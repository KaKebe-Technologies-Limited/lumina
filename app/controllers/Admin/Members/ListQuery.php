<?php
declare(strict_types=1);

namespace App\Controllers\Admin\Members;

use App\Core\Database;
use App\Core\Pagination;
use App\Core\Request;
use App\Models\Member;
use App\Services\Inbox\Support;
use App\Services\Inbox\Where;
use App\Services\Membership\MembershipService;

/**
 * The Members list query: search + filters + whitelisted sort + pagination, shared by the list, the CSV export and the bulk actions so
 * they can never disagree about "which members". OWNER: ADMIN agent (membership). Contract: docs/MEMBERSHIP.md.
 *
 * SAFETY: every SQL fragment here is developer-written; user input only ever travels as a bound parameter, and the sort comes from a
 * whitelist. The payment-status / balance expressions come from MembershipService::paymentSql() (the SQL twin of paymentSummary()).
 * PRIVACY: the search term can be a name or e-mail address, so it is never written to the audit log.
 */
final class ListQuery
{
    public const EXPORT_LIMIT = 20000;

    /** sort key => [label, ORDER BY]. `pay_balance` is a column alias of the list SELECT. */
    public const SORTS = [
        'newest'    => ['Newest applications first', 'm.created_at DESC, m.id DESC'],
        'oldest'    => ['Oldest applications first', 'm.created_at ASC, m.id ASC'],
        'name_asc'  => ['Name, A to Z', 'm.full_name ASC, m.id ASC'],
        'name_desc' => ['Name, Z to A', 'm.full_name DESC, m.id DESC'],
        'expiry'    => ['Expiry date, soonest first', '(m.expiry_date IS NULL) ASC, m.expiry_date ASC, m.id ASC'],
        'balance'   => ['Largest balance owing', 'pay_balance DESC, m.id DESC'],
    ];

    /** term filter key => [label, SQL condition] */
    public const TERMS = [
        'expiring'  => ['Expiring in the next 30 days', "m.status = 'approved' AND m.expiry_date IS NOT NULL AND m.expiry_date > CURDATE() AND m.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)"],
        'no_expiry' => ['Approved, no expiry date', "m.status = 'approved' AND m.expiry_date IS NULL"],
    ];

    /** @var array{q:string,status:string,type:int,payment:string,term:string,from:string,to:string,sort:string} */
    public array $f;
    private Where $where;
    private Where $withoutStatus;

    /** @param array{q:string,status:string,type:int,payment:string,term:string,from:string,to:string,sort:string} $f */
    private function __construct(array $f)
    {
        $this->f = $f;
        $w = new Where();
        $w->addLike(['m.full_name', 'm.email', 'm.phone', 'm.whatsapp', 'm.application_ref', 'm.membership_number'], Support::like($f['q']));
        if ($f['type'] > 0) {
            $w->add('m.type_id = ?', [$f['type']]);
        }
        if ($f['payment'] !== '') {
            $w->add('(' . MembershipService::paymentSql('m')['status'] . ') = ?', [$f['payment']]);
        }
        if ($f['term'] !== '') {
            $w->add(self::TERMS[$f['term']][1]);
        }
        if ($f['from'] !== '') {
            $w->add('m.created_at >= ?', [$f['from'] . ' 00:00:00']);
        }
        if ($f['to'] !== '') {
            $w->add('m.created_at <= ?', [$f['to'] . ' 23:59:59']);
        }
        $this->withoutStatus = $w;
        $this->where = $f['status'] !== 'all' ? $w->with('m.status = ?', [$f['status']]) : $w;
    }

    public static function fromRequest(Request $r): self
    {
        $status = $r->string('status', 'all');
        $payment = $r->string('payment');
        $term = $r->string('term');
        $sort = $r->string('sort', 'newest');
        $from = Support::date($r->string('from')) ?? '';
        $to = Support::date($r->string('to')) ?? '';
        if ($from !== '' && $to !== '' && $to < $from) {
            [$from, $to] = [$to, $from];                  // a reversed range still finds something
        }
        return new self([
            'q'       => mb_substr($r->string('q'), 0, 100),
            'status'  => in_array($status, Member::STATUSES, true) ? $status : 'all',
            'type'    => max(0, $r->int('type')),
            'payment' => isset(MembershipService::PAYMENT_SUMMARY_LABELS[$payment]) ? $payment : '',
            'term'    => isset(self::TERMS[$term]) ? $term : '',
            'from'    => $from,
            'to'      => $to,
            'sort'    => isset(self::SORTS[$sort]) ? $sort : 'newest',
        ]);
    }

    /** True when something narrows the list down (search, type, payment, term, dates or a status tab). */
    public function isFiltered(): bool
    {
        return $this->f['q'] !== '' || $this->f['status'] !== 'all' || $this->f['type'] > 0 || $this->f['payment'] !== ''
            || $this->f['term'] !== '' || $this->f['from'] !== '' || $this->f['to'] !== '';
    }

    /** Query parameters to carry over in links and forms (defaults and empty values left out). @return array<string,string|int> */
    public function keep(bool $withStatus = true): array
    {
        $out = [
            'q' => $this->f['q'], 'type' => $this->f['type'] > 0 ? $this->f['type'] : '', 'payment' => $this->f['payment'],
            'term' => $this->f['term'], 'from' => $this->f['from'], 'to' => $this->f['to'],
            'sort' => $this->f['sort'] === 'newest' ? '' : $this->f['sort'],
        ];
        if ($withStatus) {
            $out['status'] = $this->f['status'] === 'all' ? '' : $this->f['status'];
        }
        return array_filter($out, static fn($v): bool => $v !== '');
    }

    /** The shared SELECT list: the member row plus balance, paid amount and payment status computed in SQL. */
    private static function select(): string
    {
        $pay = MembershipService::paymentSql('m');
        return 'm.*, ' . $pay['balance'] . ' AS pay_balance, ' . $pay['paid'] . ' AS pay_paid, ' . $pay['status'] . ' AS pay_status';
    }

    public function page(int $page, int $perPage): Pagination
    {
        $total = (int) Database::value('SELECT COUNT(*) FROM members m' . $this->where->sql(), $this->where->params(), 0);
        $p = Pagination::make($total, $page, $perPage);
        $p->items = Database::all(
            'SELECT ' . self::select() . ' FROM members m' . $this->where->sql() . ' ORDER BY ' . self::SORTS[$this->f['sort']][1]
            . ' LIMIT ' . (int) $p->perPage . ' OFFSET ' . (int) $p->offset,
            $this->where->params()
        );
        return $p;
    }

    /** Every matching row for an export (capped). @return array<int,array<string,mixed>> */
    public function rows(): array
    {
        return Database::all(
            'SELECT ' . self::select() . ' FROM members m' . $this->where->sql() . ' ORDER BY ' . self::SORTS[$this->f['sort']][1] . ' LIMIT ' . self::EXPORT_LIMIT,
            $this->where->params()
        );
    }

    /**
     * Rows for the ticked ids of a bulk export (newest first).
     * @param int[] $ids
     * @return array<int,array<string,mixed>>
     */
    public static function byIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        return Database::all(
            'SELECT ' . self::select() . ' FROM members m WHERE m.id IN (' . implode(',', array_fill(0, count($ids), '?')) . ') ORDER BY m.created_at DESC, m.id DESC',
            array_values($ids)
        );
    }

    /** Number of matching members per status for the tabs. They follow every filter except the status tab itself. @return array<string,int> */
    public function counts(): array
    {
        $out = array_fill_keys(Member::STATUSES, 0);
        $total = 0;
        foreach (Database::all('SELECT m.status, COUNT(*) AS c FROM members m' . $this->withoutStatus->sql() . ' GROUP BY m.status', $this->withoutStatus->params()) as $r) {
            $out[(string) $r['status']] = (int) $r['c'];
            $total += (int) $r['c'];
        }
        $out['all'] = $total;
        return $out;
    }

    /**
     * Figures for the tiles above the list (whole database, not the current filter).
     * @return array{pending:int,approved:int,expiring:int,total:int,owing_count:int,owing:array<string,float>}
     */
    public static function summary(): array
    {
        $c = Database::one(
            "SELECT COUNT(*) AS total, COALESCE(SUM(m.status = 'pending'), 0) AS pending, COALESCE(SUM(m.status = 'approved'), 0) AS approved,
                    COALESCE(SUM(m.status = 'approved' AND m.expiry_date IS NOT NULL AND m.expiry_date > CURDATE() AND m.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)), 0) AS expiring
             FROM members m"
        ) ?? [];
        $owing = [];
        $owingCount = 0;
        $balance = MembershipService::paymentSql('m')['balance'];
        foreach (Database::all(
            'SELECT t.cur, COUNT(*) AS n, SUM(t.bal) AS total FROM (SELECT m.fee_currency AS cur, ' . $balance . " AS bal FROM members m WHERE m.status = 'approved') t
             WHERE t.bal > 0 GROUP BY t.cur ORDER BY t.cur"
        ) as $r) {
            $owing[(string) $r['cur']] = (float) $r['total'];
            $owingCount += (int) $r['n'];
        }
        return [
            'pending' => (int) ($c['pending'] ?? 0), 'approved' => (int) ($c['approved'] ?? 0), 'expiring' => (int) ($c['expiring'] ?? 0),
            'total' => (int) ($c['total'] ?? 0), 'owing_count' => $owingCount, 'owing' => $owing,
        ];
    }
}
