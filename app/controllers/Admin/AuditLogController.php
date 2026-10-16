<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\CsvExport;
use App\Core\Database;
use App\Core\Pagination;
use App\Core\Request;
use App\Core\Response;

/**
 * ACTIVITY LOG viewer (ability audit.view = admins). OWNER: B3 admin-core.
 *   GET admin/audit-log[?q=&user=&entity=&from=&to=&page=&per_page=]        GET admin/audit-log/export  (same filters, CSV)
 * Read-only: the log can never be edited or deleted from the admin.
 */
final class AuditLogController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('audit.view');
        [$where, $params] = $this->filters($request);
        $total = (int) Database::value('SELECT COUNT(*) FROM audit_log' . $where, $params, 0);
        $p = Pagination::make($total, $request->page(), $this->perPage());
        $p->items = Database::all(
            'SELECT id, user_id, user_name, action, entity_type, entity_id, summary, details, ip, created_at FROM audit_log' . $where
            . ' ORDER BY id DESC LIMIT ' . (int) $p->perPage . ' OFFSET ' . (int) $p->offset,
            $params
        );
        return $this->render('admin/audit/index', [
            'pagination' => $p,
            'users'      => Database::all('SELECT DISTINCT user_name FROM audit_log ORDER BY user_name LIMIT 100'),
            'entities'   => array_column(Database::all('SELECT DISTINCT entity_type FROM audit_log WHERE entity_type IS NOT NULL ORDER BY entity_type'), 'entity_type'),
            'q'          => $request->string('q'),
            'user'       => $request->string('user'),
            'entity'     => $request->string('entity'),
            'from'       => $request->string('from'),
            'to'         => $request->string('to'),
            'exportQuery' => http_build_query(array_filter($request->only(['q', 'user', 'entity', 'from', 'to']), static fn($v) => $v !== '')),
        ], ['title' => 'Activity log', 'nav' => 'audit', 'breadcrumbs' => [['Activity log', null]]]);
    }

    public function export(Request $request): Response
    {
        $this->authorize('audit.view', 'export.csv');
        [$where, $params] = $this->filters($request);
        $rows = Database::all('SELECT created_at, user_name, action, entity_type, entity_id, summary, ip FROM audit_log' . $where . ' ORDER BY id DESC LIMIT 20000', $params);
        $this->audit('audit.export', 'audit', null, 'Exported the activity log (' . count($rows) . ' rows)');
        return CsvExport::response('activity-log-' . date('Y-m-d') . '.csv', ['When', 'User', 'Action', 'Item type', 'Item', 'Summary', 'IP address'], array_map(
            static fn(array $r): array => array_values($r),
            $rows
        ));
    }

    /** @return array{0:string,1:array<int,mixed>} */
    private function filters(Request $request): array
    {
        $w = [];
        $params = [];
        if (($q = $request->string('q')) !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_substr($q, 0, 100)) . '%';
            $w[] = '(summary LIKE ? OR action LIKE ? OR entity_id LIKE ?)';
            array_push($params, $like, $like, $like);
        }
        if (($u = $request->string('user')) !== '') {
            $w[] = 'user_name = ?';
            $params[] = $u;
        }
        if (($e = $request->string('entity')) !== '') {
            $w[] = 'entity_type = ?';
            $params[] = $e;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->string('from'))) {
            $w[] = 'created_at >= ?';
            $params[] = $request->string('from') . ' 00:00:00';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->string('to'))) {
            $w[] = 'created_at <= ?';
            $params[] = $request->string('to') . ' 23:59:59';
        }
        return [$w === [] ? '' : ' WHERE ' . implode(' AND ', $w), $params];
    }
}
