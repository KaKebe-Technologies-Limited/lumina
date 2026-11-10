<?php
declare(strict_types=1);

namespace App\Services\Payments;

use App\Core\Database;
use App\Services\DarazaPay\DarazaPayments;

/**
 * ONE LIST of every payment on the website, for Admin > Payments: money in and money out, from every way of paying.
 *   mobile  - MTN / Airtel mobile money through the Daraza API (collections and payouts)
 *   online  - Pesapal and Flutterwave (cards, wallets)
 *   manual  - bank, cash and manual mobile-money payments recorded by staff (shop, donations, membership)
 * A payment is counted ONCE: the gateway ledgers (daraza_payments, pesapal_payments) are the source for gateway money; the shop, donation and membership records are used only for what
 * did not come through those gateways. Amounts are in minor units (x100), like Money.
 */
final class PaymentsDashboard
{
    public const CHANNELS = ['mobile' => 'MTN / Airtel mobile money', 'online' => 'Card and online (Pesapal, Flutterwave)', 'manual' => 'Bank, cash and manual'];
    public const RANGES = ['today' => 'Today', '7' => 'Last 7 days', '30' => 'Last 30 days', '90' => 'Last 90 days', 'all' => 'All time'];
    public const STATUSES = ['paid' => 'Paid', 'pending' => 'Waiting', 'failed' => 'Failed'];
    public const MAIN_CURRENCY = 'UGX';

    /** Every payment as one row set. Columns: src, id, at, channel, dir (in|out), what, who, detail, amt (minor), cur, status (paid|pending|failed), ref */
    private static function union(): string
    {
        $labels = [];
        foreach (DarazaPayments::PURPOSES as $k => $name) {
            $labels[$k] = $name;
        }
        $what = static function (string $col) use ($labels): string {
            $case = 'CASE ' . $col;
            foreach ($labels as $k => $name) {
                $case .= " WHEN '" . $k . "' THEN '" . str_replace("'", "''", $name) . "'";
            }
            return $case . ' ELSE ' . $col . ' END';
        };
        $parts = [
            "SELECT 'daraza' AS src, d.id, COALESCE(d.paid_at, d.created_at) AS at, 'mobile' AS channel, d.direction AS dir,
                    CASE WHEN d.direction = 'out' THEN 'Payout (money sent)' ELSE " . $what('d.purpose') . " END AS what,
                    CASE WHEN d.direction = 'out' THEN COALESCE(d.phone, '') WHEN d.payer_name <> '' THEN d.payer_name ELSE COALESCE(d.payer_email, '') END AS who,
                    CONCAT(CASE d.method WHEN 1 THEN 'MTN ' WHEN 2 THEN 'Airtel ' ELSE '' END, CASE WHEN d.direction = 'in' THEN COALESCE(d.phone, '') ELSE COALESCE(d.note, '') END) AS detail,
                    d.amount_ugx * 100 AS amt, 'UGX' AS cur,
                    CASE d.status WHEN 'paid' THEN 'paid' WHEN 'failed' THEN 'failed' ELSE 'pending' END AS status, d.reference AS ref
             FROM daraza_payments d",
            "SELECT 'pesapal', p.id, COALESCE(p.paid_at, p.created_at), 'online', 'in', " . $what('p.purpose') . ",
                    CASE WHEN p.payer_name <> '' THEN p.payer_name ELSE COALESCE(p.payer_email, '') END, COALESCE(p.pay_method, 'Pesapal'),
                    p.amount_minor, p.currency, CASE p.status WHEN 'paid' THEN 'paid' WHEN 'failed' THEN 'failed' WHEN 'invalid' THEN 'failed' WHEN 'reversed' THEN 'failed' ELSE 'pending' END, p.merchant_reference
             FROM pesapal_payments p",
            "SELECT 'shop', s.id, s.created_at, CASE WHEN s.method = 'flutterwave' THEN 'online' ELSE 'manual' END, 'in', 'Shop orders',
                    COALESCE(o.customer_name, ''), CONCAT('Order ', COALESCE(o.order_number, ''), ' ', s.method),
                    s.amount, s.currency, 'paid', COALESCE(s.reference, CONCAT('SHOP-', s.id))
             FROM shop_payments s LEFT JOIN shop_orders o ON o.id = s.order_id
             WHERE s.status = 'success' AND s.method NOT IN ('mobile', 'pesapal')",
            "SELECT 'donation', n.id, COALESCE(n.received_at, n.created_at), 'manual', 'in', 'Donations',
                    CASE WHEN n.is_anonymous = 1 THEN 'Anonymous' ELSE n.donor_name END, COALESCE(n.method, ''),
                    ROUND(n.amount * 100), n.currency, 'paid', COALESCE(n.receipt_no, CONCAT('DON-', n.id))
             FROM donations n WHERE n.status = 'received' AND n.method NOT IN ('mobile_money', 'pesapal')",
            "SELECT 'member', m.id, COALESCE(m.paid_at, m.created_at), 'manual', 'in', 'Membership payments',
                    COALESCE(b.full_name, ''), COALESCE(m.method, ''),
                    ROUND(m.amount * 100), m.currency, 'paid', COALESCE(m.receipt_number, CONCAT('MEM-', m.id))
             FROM member_payments m LEFT JOIN members b ON b.id = m.member_id
             WHERE m.status = 'received' AND m.method NOT IN ('mobile_money', 'pesapal')",
        ];
        return '(' . implode(' UNION ALL ', $parts) . ')';
    }

    /** Start of a range as a datetime, or null for all time. */
    public static function since(string $range): ?string
    {
        return match ($range) {
            'today' => date('Y-m-d 00:00:00'),
            '7' => date('Y-m-d 00:00:00', strtotime('-6 days')),
            '30' => date('Y-m-d 00:00:00', strtotime('-29 days')),
            '90' => date('Y-m-d 00:00:00', strtotime('-89 days')),
            default => null,
        };
    }

    /** @return array{0:string,1:list<mixed>} WHERE clause and its values */
    private static function where(array $f): array
    {
        $w = [];
        $v = [];
        $since = self::since((string) ($f['range'] ?? '30'));
        if ($since !== null) {
            $w[] = 'u.at >= ?';
            $v[] = $since;
        }
        if (isset(self::CHANNELS[(string) ($f['channel'] ?? '')])) {
            $w[] = 'u.channel = ?';
            $v[] = $f['channel'];
        }
        if (in_array($f['dir'] ?? '', ['in', 'out'], true)) {
            $w[] = 'u.dir = ?';
            $v[] = $f['dir'];
        }
        if (isset(self::STATUSES[(string) ($f['status'] ?? '')])) {
            $w[] = 'u.status = ?';
            $v[] = $f['status'];
        }
        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_substr($q, 0, 60)) . '%';
            $w[] = '(u.who LIKE ? OR u.ref LIKE ? OR u.detail LIKE ? OR u.what LIKE ?)';
            array_push($v, $like, $like, $like, $like);
        }
        return [$w === [] ? '' : ' WHERE ' . implode(' AND ', $w), $v];
    }

    /** @return array{rows:list<array<string,mixed>>,total:int} */
    public static function rows(array $f, int $limit, int $offset): array
    {
        DarazaPayments::install();
        [$where, $v] = self::where($f);
        $u = self::union();
        $total = (int) Database::value("SELECT COUNT(*) FROM {$u} u" . $where, $v, 0);
        $rows = Database::all("SELECT u.* FROM {$u} u" . $where . ' ORDER BY u.at DESC, u.id DESC LIMIT ' . max(1, min(500, $limit)) . ' OFFSET ' . max(0, $offset), $v);
        return ['rows' => $rows, 'total' => $total];
    }

    /**
     * The numbers at the top of the dashboard for a range (the filters other than the range do not change them).
     * @return array{in:int,out:int,net:int,count_in:int,count_out:int,waiting:int,failed:int,by_channel:array<string,int>,by_what:list<array{what:string,amt:int,n:int}>,days:list<array{day:string,amt:int}>,other:array<string,int>}
     */
    public static function summary(string $range): array
    {
        DarazaPayments::install();
        $u = self::union();
        $since = self::since($range);
        $w = $since === null ? '' : ' AND u.at >= ?';
        $v = $since === null ? [] : [$since];
        $cur = self::MAIN_CURRENCY;
        $one = static fn(string $sql, array $args = []): int => (int) Database::value($sql, $args, 0);
        $in = $one("SELECT COALESCE(SUM(u.amt),0) FROM {$u} u WHERE u.dir = 'in' AND u.status = 'paid' AND u.cur = ?" . $w, array_merge([$cur], $v));
        $out = $one("SELECT COALESCE(SUM(u.amt),0) FROM {$u} u WHERE u.dir = 'out' AND u.status = 'paid' AND u.cur = ?" . $w, array_merge([$cur], $v));
        $byChannel = array_fill_keys(array_keys(self::CHANNELS), 0);
        foreach (Database::all("SELECT u.channel, COALESCE(SUM(u.amt),0) s FROM {$u} u WHERE u.dir = 'in' AND u.status = 'paid' AND u.cur = ?" . $w . ' GROUP BY u.channel', array_merge([$cur], $v)) as $r) {
            $byChannel[(string) $r['channel']] = (int) $r['s'];
        }
        $byWhat = [];
        foreach (Database::all("SELECT u.what, COALESCE(SUM(u.amt),0) s, COUNT(*) n FROM {$u} u WHERE u.dir = 'in' AND u.status = 'paid' AND u.cur = ?" . $w . ' GROUP BY u.what ORDER BY s DESC LIMIT 8', array_merge([$cur], $v)) as $r) {
            $byWhat[] = ['what' => (string) $r['what'], 'amt' => (int) $r['s'], 'n' => (int) $r['n']];
        }
        $other = [];
        foreach (Database::all("SELECT u.cur, COALESCE(SUM(u.amt),0) s FROM {$u} u WHERE u.dir = 'in' AND u.status = 'paid' AND u.cur <> ?" . $w . ' GROUP BY u.cur', array_merge([$cur], $v)) as $r) {
            $other[(string) $r['cur']] = (int) $r['s'];
        }
        // the last 14 days of money in, for the bars (always 14, so the picture does not change with the range)
        $from = date('Y-m-d 00:00:00', strtotime('-13 days'));
        $byDay = [];
        foreach (Database::all("SELECT DATE(u.at) d, COALESCE(SUM(u.amt),0) s FROM {$u} u WHERE u.dir = 'in' AND u.status = 'paid' AND u.cur = ? AND u.at >= ? GROUP BY DATE(u.at)", [$cur, $from]) as $r) {
            $byDay[(string) $r['d']] = (int) $r['s'];
        }
        $days = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $days[] = ['day' => $d, 'amt' => $byDay[$d] ?? 0];
        }
        return [
            'in' => $in, 'out' => $out, 'net' => $in - $out,
            'count_in' => $one("SELECT COUNT(*) FROM {$u} u WHERE u.dir = 'in' AND u.status = 'paid'" . $w, $v),
            'count_out' => $one("SELECT COUNT(*) FROM {$u} u WHERE u.dir = 'out' AND u.status = 'paid'" . $w, $v),
            'waiting' => $one("SELECT COUNT(*) FROM {$u} u WHERE u.status = 'pending' AND u.at >= ?", [date('Y-m-d H:i:s', strtotime('-3 days'))]),
            'failed' => $one("SELECT COUNT(*) FROM {$u} u WHERE u.status = 'failed'" . $w, $v),
            'by_channel' => $byChannel, 'by_what' => $byWhat, 'days' => $days, 'other' => $other,
        ];
    }

    /** Recipients used before for payouts, newest first (a quick "send again" list). @return list<array{phone:string,method:int,n:int}> */
    public static function recentRecipients(int $limit = 6): array
    {
        if (!DarazaPayments::install()) {
            return [];
        }
        $rows = Database::all("SELECT phone, MAX(method) method, COUNT(*) n FROM daraza_payments WHERE direction = 'out' AND status = 'paid' AND phone IS NOT NULL GROUP BY phone ORDER BY MAX(id) DESC LIMIT " . max(1, min(20, $limit)));
        return array_map(static fn(array $r): array => ['phone' => (string) $r['phone'], 'method' => (int) $r['method'], 'n' => (int) $r['n']], $rows);
    }
}
