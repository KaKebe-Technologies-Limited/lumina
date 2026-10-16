<?php
declare(strict_types=1);

namespace App\Controllers\Admin\Inbox;

use App\Core\AdminController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Pagination;
use App\Core\Response;
use App\Services\Inbox\Where;

/**
 * Shared plumbing for the five admin inbox controllers (messages, subscribers, involvement, applications, donations).
 * OWNER: B5 "admin-inbox". Everything here is generic list / bulk / export support; the area-specific rules live in the
 * concrete controllers. Every SQL fragment passed in is developer-written; user input only ever travels as bound params.
 */
abstract class InboxController extends AdminController
{
    /** Max rows in one CSV export and max ids in one bulk action (protects memory / the request). */
    protected const EXPORT_LIMIT = 50000;
    protected const BULK_LIMIT = 500;

    /**
     * Counted + paginated list query.
     * @param string $from   e.g. 'contact_messages m' (constant, never user input)
     * @param string $select e.g. 'm.*'
     * @param string $order  an ORDER BY expression picked from a whitelist
     */
    protected function paged(string $from, string $select, Where $where, string $order): Pagination
    {
        $total = (int) Database::value('SELECT COUNT(*) FROM ' . $from . $where->sql(), $where->params(), 0);
        $p = Pagination::make($total, $this->request->page(), $this->perPage());
        $p->items = Database::all(
            'SELECT ' . $select . ' FROM ' . $from . $where->sql() . ' ORDER BY ' . $order . ' LIMIT ' . (int) $p->perPage . ' OFFSET ' . (int) $p->offset,
            $where->params()
        );
        return $p;
    }

    /**
     * All rows for an export (capped).
     * @return array<int,array<string,mixed>>
     */
    protected function exportRows(string $from, string $select, Where $where, string $order): array
    {
        return Database::all(
            'SELECT ' . $select . ' FROM ' . $from . $where->sql() . ' ORDER BY ' . $order . ' LIMIT ' . self::EXPORT_LIMIT,
            $where->params()
        );
    }

    /**
     * Row counts per status of a table: ['new' => 3, 'read' => 10, ...] (every listed status is present; 'all' = total).
     * @param string[] $statuses
     * @return array<string,int>
     */
    protected function statusCounts(string $table, array $statuses): array
    {
        $out = array_fill_keys($statuses, 0);
        $total = 0;
        foreach (Database::all('SELECT status, COUNT(*) AS c FROM ' . Database::ident($table) . ' GROUP BY status') as $r) {
            $out[(string) $r['status']] = (int) $r['c'];
            $total += (int) $r['c'];
        }
        $out['all'] = $total;
        return $out;
    }

    /** The ticked row ids of a bulk form (unique, positive, capped). @return int[] */
    protected function bulkIds(): array
    {
        $raw = $this->request->post('ids', []);
        if (!is_array($raw)) {
            return [];
        }
        $ids = [];
        foreach ($raw as $v) {
            if (is_scalar($v) && ctype_digit((string) $v) && (int) $v > 0) {
                $ids[(int) $v] = true;
            }
        }
        return array_slice(array_keys($ids), 0, self::BULK_LIMIT);
    }

    /** "?,?,?" for an IN() list. @param array<int,mixed> $ids */
    protected function marks(array $ids): string
    {
        return implode(',', array_fill(0, count($ids), '?'));
    }

    /**
     * Validate a bulk request: at least one id and an action from the whitelist. Redirects back with a flash message otherwise.
     * @param string[] $allowed
     * @return array{0:int[],1:string}|Response
     */
    protected function bulkInput(array $allowed, string $fallback): array|Response
    {
        $action = $this->request->string('action');
        $ids = $this->bulkIds();
        if ($ids === []) {
            Flash::warning('Select at least one item first.');
            return $this->back($fallback);
        }
        if (!in_array($action, $allowed, true)) {
            Flash::error('Choose an action to apply to the selected items.');
            return $this->back($fallback);
        }
        return [$ids, $action];
    }

    /** "3 items" / "1 item". */
    protected function items(int $n, string $singular = 'item'): string
    {
        return $n . ' ' . $singular . ($n === 1 ? '' : 's');
    }

    /** Status tabs for an index view: [['key','label','count'], ...] starting with "All". @param array<string,int> $counts */
    protected function tabs(array $counts, array $statuses, array $labels = []): array
    {
        $tabs = [['key' => 'all', 'label' => 'All', 'count' => (int) ($counts['all'] ?? 0)]];
        foreach ($statuses as $s) {
            $tabs[] = ['key' => $s, 'label' => $labels[$s] ?? label_from($s), 'count' => (int) ($counts[$s] ?? 0)];
        }
        return $tabs;
    }
}
