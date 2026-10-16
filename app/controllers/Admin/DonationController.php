<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Inbox\InboxController;
use App\Core\CsvExport;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Donation;
use App\Services\Inbox\Support;
use App\Services\Inbox\Where;

/**
 * Admin > Donations. OWNER: B5 "admin-inbox".
 * Pledges from the public pledge form arrive with source "pledge_form"; the team adds money that arrived by other routes manually
 * (bank, mobile money, cash...). Workflow: pledged -> received (| cancelled). Marking a donation received stamps the received date and
 * issues a unique receipt number (RCT-YYYY-NNNN); only received donations have a printable receipt.
 * Totals are always kept per currency (amounts in different currencies are never added together).
 */
final class DonationController extends InboxController
{
    private const FROM = 'donations d';
    /** The date a donation "counts" on: when it was received, otherwise when it was recorded. */
    private const DATE_EXPR = "(CASE WHEN d.status = 'received' AND d.received_at IS NOT NULL THEN d.received_at ELSE d.created_at END)";
    private const SORTS = [
        'newest'      => self::DATE_EXPR . ' DESC, d.id DESC',
        'oldest'      => self::DATE_EXPR . ' ASC, d.id ASC',
        'amount_high' => 'd.amount DESC, d.id DESC',
        'amount_low'  => 'd.amount ASC, d.id ASC',
    ];
    public const METHOD_LABELS = [
        'gofundme' => 'GoFundMe', 'bank' => 'Bank transfer', 'mobile_money' => 'Mobile money', 'paypal' => 'PayPal', 'cash' => 'Cash', 'other' => 'Other',
    ];
    public const CURRENCIES = ['UGX', 'USD', 'EUR', 'GBP', 'KES', 'TZS', 'RWF'];

    public function index(Request $request): Response
    {
        $this->authorize('inbox.manage');
        [$where, $f, $noStatus] = $this->filters();
        $page = $this->paged(self::FROM, 'd.*', $where, self::SORTS[$f['sort']]);

        return $this->render('admin/donations/index', [
            'pagination'   => $page,
            'f'            => $f,
            'tabs'         => $this->tabs($this->statusCounts('donations', Donation::STATUSES), Donation::STATUSES, $this->statusLabels()),
            'summary'      => $this->summary($noStatus),
            'methods'      => self::METHOD_LABELS,
            'statusLabels' => $this->statusLabels(),
            'canDelete'    => can('inbox.delete'),
            'canExport'    => can('export.csv'),
        ], ['title' => 'Donations', 'nav' => 'donations', 'breadcrumbs' => [['Donations', null]]]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('inbox.manage');
        return $this->form(null);
    }

    public function store(Request $request): Response
    {
        $this->authorize('inbox.manage');
        $fields = $this->input(null);
        $id = 0;
        $this->saveWithReceipt($fields, function (array $data) use (&$id): void {
            $id = Donation::create($data + ['source' => 'manual']);
        });
        $this->audit('donation.create', 'donation', $id, 'Recorded a donation' . $this->describe($fields), ['status' => $fields['status'], 'method' => $fields['method']]);
        Flash::success('Donation recorded.' . (!empty($fields['receipt_no']) ? ' Receipt number ' . $fields['receipt_no'] . '.' : ''));
        return $this->redirect('admin/donations');
    }

    public function edit(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage');
        return $this->form(Donation::findOrFail($id));
    }

    public function update(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage');
        $d = Donation::findOrFail($id);
        $fields = $this->input($d);
        $this->saveWithReceipt($fields, static function (array $data) use ($id): void {
            Donation::update($id, $data);
        });
        $details = ['status' => $fields['status'], 'was' => $d['status']];
        $moneyChanged = !empty($d['receipt_no'])
            && ((string) ($d['amount'] ?? '') !== (string) ($fields['amount'] ?? '') || strtoupper((string) $d['currency']) !== strtoupper((string) $fields['currency']));
        if ($moneyChanged) {
            // the printed receipt keeps its number, so a change of the money on it must be visible in the activity log
            $details['receipt_no'] = $d['receipt_no'];
            $details['amount_was'] = trim((string) $d['amount'] . ' ' . $d['currency']);
            $details['amount_now'] = trim((string) ($fields['amount'] ?? '') . ' ' . $fields['currency']);
        }
        $this->audit('donation.update', 'donation', $id, 'Updated donation' . $this->describe($fields)
            . ($moneyChanged ? ' (amount changed after receipt ' . $d['receipt_no'] . ' was issued)' : ''), $details);
        Flash::success('Donation updated.');
        if ($moneyChanged) {
            Flash::warning('Receipt ' . $d['receipt_no'] . ' was already issued. The printable receipt now shows the new amount: tell the donor if they already hold the old one.');
        }
        return $this->redirect('admin/donations');
    }

    /** Quick status change from the list (pledged / received / cancelled ...). */
    public function status(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage');
        $d = Donation::findOrFail($id);
        $this->validate(['status' => 'required|in:' . implode(',', Donation::STATUSES)], [], ['status' => 'Status']);
        $status = $this->request->string('status');
        if ($status === $d['status']) {
            return $this->back('admin/donations');
        }
        $fields = ['status' => $status];
        if ($status === 'received') {
            if ((float) ($d['amount'] ?? 0) <= 0) {
                Flash::error('Add the amount to this donation before marking it as received.');
                return $this->redirect('admin/donations/' . $id . '/edit');
            }
            if (empty($d['received_at'])) {
                $fields['received_at'] = date('Y-m-d H:i:s');
            }
            if (empty($d['receipt_no'])) {
                $fields['receipt_no'] = $this->nextReceiptNo();
            }
        }
        $this->saveWithReceipt($fields, static function (array $data) use ($id): void {
            Donation::update($id, $data);
        });
        $this->audit('donation.status', 'donation', $id, 'Marked donation #' . $id . ' as ' . $status, ['status' => $status, 'was' => $d['status']]);
        Flash::success('Donation marked as ' . strtolower($this->statusLabels()[$status] ?? $status) . '.' . (!empty($fields['receipt_no']) ? ' Receipt number ' . $fields['receipt_no'] . '.' : ''));
        return $this->back('admin/donations');
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage', 'inbox.delete');
        $d = Donation::findOrFail($id);
        if (!empty($d['receipt_no'])) {
            // a receipt number may already be printed or e-mailed: deleting the row would let the next donation reuse it
            Flash::error('This donation has receipt number ' . $d['receipt_no'] . ', so it cannot be deleted. Set its status to Cancelled instead; the number then stays reserved.');
            return $this->redirect('admin/donations/' . $id . '/edit');
        }
        Donation::delete($id);
        $this->audit('donation.delete', 'donation', $id, 'Deleted donation #' . $id . $this->describe($d), ['receipt_no' => $d['receipt_no'], 'status' => $d['status']]);
        Flash::success('Donation deleted.');
        return $this->redirect('admin/donations');
    }

    /** Printable receipt (received donations only). Standalone page, no admin chrome. */
    public function receipt(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage');
        $d = Donation::findOrFail($id);
        if ($d['status'] !== 'received') {
            Flash::error('A receipt can only be printed for a donation that has been received.');
            return $this->redirect('admin/donations/' . $id . '/edit');
        }
        if (empty($d['receipt_no'])) {
            Flash::warning('This donation has no receipt number yet. Save it once to issue one, then print the receipt.');
            return $this->redirect('admin/donations/' . $id . '/edit');
        }
        return $this->render('admin/donations/receipt', [
            'd'       => $d,
            'methods' => self::METHOD_LABELS,
        ], ['title' => 'Receipt ' . $d['receipt_no'], 'layout' => null]);
    }

    public function export(Request $request): Response
    {
        $this->authorize('inbox.manage', 'export.csv');
        [$where] = $this->filters();
        $rows = $this->exportRows(self::FROM, 'd.*, ' . self::DATE_EXPR . ' AS donated_on', $where, self::SORTS['newest']);
        $this->audit('donation.export', 'donation', null, 'Exported ' . $this->items(count($rows), 'donation') . ' to CSV');
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                $r['receipt_no'], $r['donated_on'], $r['donor_name'], $r['donor_email'], $r['donor_phone'],
                $r['amount'] === null ? '' : number_format((float) $r['amount'], 2, '.', ''), $r['currency'],
                self::METHOD_LABELS[$r['method']] ?? label_from((string) $r['method']), $r['status'], $r['source'], $r['reference'],
                !empty($r['is_anonymous']) ? 'Yes' : 'No', $r['message'], $r['admin_notes'], $r['created_at'],
            ];
        }
        return CsvExport::response('donations-' . date('Y-m-d') . '.csv',
            ['Receipt no', 'Date', 'Donor name', 'Donor email', 'Donor phone', 'Amount', 'Currency', 'Method', 'Status', 'Source', 'Reference', 'Anonymous', 'Message', 'Notes', 'Recorded at'], $out);
    }

    // ------------------------------------------------------------------ internals

    /** @return array<string,string> */
    private function statusLabels(): array
    {
        $out = [];
        foreach (Donation::STATUSES as $s) {
            $out[$s] = label_from($s);
        }
        return $out;
    }

    private function form(?array $d): Response
    {
        $currencies = self::CURRENCIES;
        $cur = strtoupper((string) old('currency', $d['currency'] ?? 'UGX'));
        if (!in_array($cur, $currencies, true)) {
            $currencies[] = $cur;
        }
        $receivedAt = (string) old('received_at', !empty($d['received_at']) ? date('Y-m-d\TH:i', strtotime((string) $d['received_at'])) : '');
        return $this->render('admin/donations/form', [
            'd'            => $d,
            'currencies'   => array_combine($currencies, $currencies),
            'methods'      => self::METHOD_LABELS,
            'statusLabels' => $this->statusLabels(),
            'receivedAt'   => $receivedAt,
            'canDelete'    => can('inbox.delete'),
        ], [
            'title'       => $d === null ? 'Record a donation' : 'Edit donation',
            'nav'         => 'donations',
            'breadcrumbs' => [['Donations', url('admin/donations')], [$d === null ? 'Record a donation' : 'Edit donation', null]],
        ]);
    }

    /**
     * Validate the donation form and return the columns to store (without `source`).
     * @param array<string,mixed>|null $existing the current row when editing
     * @return array<string,mixed>
     */
    private function input(?array $existing): array
    {
        $in = $this->request->all();
        $in['amount'] = preg_replace('/[\s,]+/', '', (string) ($in['amount'] ?? '')) ?? '';
        $v = Validator::make($in, [
            'donor_name'  => 'nullable|max:150',
            'donor_email' => 'nullable|email|max:190',
            'donor_phone' => 'nullable|max:40',
            'amount'      => 'nullable|numeric|between:0,999999999999',
            'currency'    => ['required', 'regex:/^[A-Za-z]{3}$/'],
            'method'      => 'required|in:' . implode(',', Donation::METHODS),
            'status'      => 'required|in:' . implode(',', Donation::STATUSES),
            'reference'   => 'nullable|max:120',
            'received_at' => 'nullable|datetime',
            'message'     => 'nullable|max:5000',
            'admin_notes' => 'nullable|max:5000',
        ], [
            'currency.regex' => 'Use a three-letter currency code such as UGX or USD.',
            'amount.numeric' => 'Enter the amount as a number, for example 150000 or 25.50.',
        ], [
            'donor_name' => 'Donor name', 'donor_email' => 'Donor email', 'donor_phone' => 'Donor phone', 'amount' => 'Amount',
            'currency' => 'Currency', 'method' => 'Method', 'status' => 'Status', 'reference' => 'Reference',
            'received_at' => 'Date received', 'message' => 'Message', 'admin_notes' => 'Notes',
        ]);
        if ($v->fails()) {
            $this->fail($v->errors());
        }
        $d = $v->validated();

        $amount = ($d['amount'] ?? '') === '' ? null : round((float) $d['amount'], 2);
        if ($d['status'] === 'received' && ($amount === null || $amount <= 0)) {
            $this->fail(['amount' => 'Enter the amount received before marking the donation as received.']);
        }

        $receivedAt = ($d['received_at'] ?? '') !== '' ? date('Y-m-d H:i:s', (int) strtotime(str_replace('T', ' ', (string) $d['received_at']))) : null;
        if ($d['status'] === 'received' && $receivedAt === null) {
            $receivedAt = date('Y-m-d H:i:s');
        }

        $fields = [
            'donor_name'   => $this->nullable($d['donor_name'] ?? ''),
            'donor_email'  => $this->nullable($d['donor_email'] ?? ''),
            'donor_phone'  => $this->nullable($d['donor_phone'] ?? ''),
            'amount'       => $amount === null ? null : number_format($amount, 2, '.', ''),
            'currency'     => strtoupper((string) $d['currency']),
            'method'       => $d['method'],
            'status'       => $d['status'],
            'reference'    => $this->nullable($d['reference'] ?? ''),
            'received_at'  => $receivedAt,
            'message'      => $this->nullable($d['message'] ?? ''),
            'admin_notes'  => $this->nullable($d['admin_notes'] ?? ''),
            'is_anonymous' => $this->request->bool('is_anonymous') ? 1 : 0,
        ];
        if ($d['status'] === 'received' && empty($existing['receipt_no'])) {
            $fields['receipt_no'] = $this->nextReceiptNo();
        }
        return $fields;
    }

    private function nullable(mixed $v): ?string
    {
        $v = trim((string) $v);
        return $v === '' ? null : $v;
    }

    /** "RCT-2026-0007": the next free number of the current year. */
    private function nextReceiptNo(): string
    {
        $prefix = 'RCT-' . date('Y') . '-';
        $max = (int) Database::value(
            'SELECT MAX(CAST(SUBSTRING(receipt_no, ?) AS UNSIGNED)) FROM donations WHERE receipt_no LIKE ?',
            [strlen($prefix) + 1, $prefix . '%'],
            0
        );
        return $prefix . str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Run $write($fields); if two people issued the same receipt number at the same moment (unique key), take the next number and retry.
     * @param array<string,mixed> $fields (receipt_no is updated in place when it had to change)
     */
    private function saveWithReceipt(array &$fields, callable $write): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                $write($fields);
                return;
            } catch (\PDOException $e) {
                if ((string) $e->getCode() !== '23000' || empty($fields['receipt_no'])) {
                    throw $e;
                }
                $fields['receipt_no'] = $this->nextReceiptNo();
            }
        }
        throw new \RuntimeException('Could not issue a unique receipt number.');
    }

    private function describe(array $d): string
    {
        $bits = [];
        if (isset($d['amount']) && $d['amount'] !== null && $d['amount'] !== '') {
            $bits[] = Support::money($d['amount'], (string) ($d['currency'] ?? ''));
        }
        if (!empty($d['donor_name'])) {
            $bits[] = 'from ' . $d['donor_name'];
        }
        return $bits ? ' (' . implode(' ', $bits) . ')' : '';
    }

    /**
     * Totals for the cards above the list. They follow the search / method / date filters (not the status tabs).
     * @return array{byStatus:array,byMethod:array,byMonth:array,received:array,pledged:array,count:int,currencies:string[]}
     */
    private function summary(Where $w): array
    {
        $byStatus = [];
        $currencies = [];
        foreach (Database::all('SELECT d.status, d.currency, COUNT(*) AS c, COALESCE(SUM(d.amount), 0) AS s FROM ' . self::FROM . $w->sql() . ' GROUP BY d.status, d.currency', $w->params()) as $r) {
            $byStatus[(string) $r['status']][(string) $r['currency']] = ['count' => (int) $r['c'], 'sum' => (float) $r['s']];
            $currencies[(string) $r['currency']] = true;
        }

        $received = $w->with("d.status = 'received'");
        $byMethod = [];
        foreach (Database::all('SELECT d.method, d.currency, COUNT(*) AS c, COALESCE(SUM(d.amount), 0) AS s FROM ' . self::FROM . $received->sql() . ' GROUP BY d.method, d.currency', $received->params()) as $r) {
            $byMethod[(string) $r['method']][(string) $r['currency']] = ['count' => (int) $r['c'], 'sum' => (float) $r['s']];
        }

        $byMonth = [];
        foreach (Database::all(
            "SELECT DATE_FORMAT(" . self::DATE_EXPR . ", '%Y-%m') AS m, d.currency, COUNT(*) AS c, COALESCE(SUM(d.amount), 0) AS s FROM " . self::FROM . $received->sql() . ' GROUP BY m, d.currency ORDER BY m DESC LIMIT 60',
            $received->params()
        ) as $r) {
            $byMonth[(string) $r['m']][(string) $r['currency']] = ['count' => (int) $r['c'], 'sum' => (float) $r['s']];
        }
        $byMonth = array_slice($byMonth, 0, 12, true);

        $count = 0;
        foreach ($byStatus as $perCurrency) {
            foreach ($perCurrency as $cell) {
                $count += $cell['count'];
            }
        }
        $cur = array_keys($currencies);
        sort($cur);
        return [
            'byStatus'   => $byStatus,
            'byMethod'   => $byMethod,
            'byMonth'    => $byMonth,
            'received'   => $byStatus['received'] ?? [],
            'pledged'    => $byStatus['pledged'] ?? [],
            'count'      => $count,
            'currencies' => $cur,
        ];
    }

    /**
     * @return array{0:Where,1:array{q:string,status:string,method:string,from:string,to:string,sort:string},2:Where} [where, filters, where WITHOUT the status]
     */
    private function filters(): array
    {
        $q = $this->request->string('q');
        $status = $this->request->string('status', 'all');
        $status = in_array($status, Donation::STATUSES, true) ? $status : 'all';
        $method = $this->request->string('method');
        $method = in_array($method, Donation::METHODS, true) ? $method : '';
        $from = Support::date($this->request->string('from')) ?? '';
        $to = Support::date($this->request->string('to')) ?? '';
        $sort = $this->request->string('sort', 'newest');
        $sort = isset(self::SORTS[$sort]) ? $sort : 'newest';

        $w = new Where();
        $w->addLike(['d.donor_name', 'd.donor_email', 'd.donor_phone', 'd.reference', 'd.receipt_no', 'd.message'], Support::like($q));
        if ($method !== '') {
            $w->add('d.method = ?', [$method]);
        }
        if ($from !== '') {
            $w->add(self::DATE_EXPR . ' >= ?', [$from . ' 00:00:00']);
        }
        if ($to !== '') {
            $w->add(self::DATE_EXPR . ' <= ?', [$to . ' 23:59:59']);
        }
        $noStatus = $w;
        $withStatus = $status !== 'all' ? $w->with('d.status = ?', [$status]) : $w;
        return [$withStatus, ['q' => $q, 'status' => $status, 'method' => $method, 'from' => $from, 'to' => $to, 'sort' => $sort], $noStatus];
    }
}
