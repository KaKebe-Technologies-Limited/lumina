<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\CsvExport;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\Reports\ReportCatalog;

/**
 * Admin > Reports: one hub of every report of the website (donations, people, membership, shop, Academy, newsletter, traffic, content, meetings, security).
 * Each report has a date range and a CSV download; a person sees (and can open) only the reports of the areas their role can already use.
 * Routes: GET admin/reports   GET admin/reports/{key}   GET admin/reports/{key}/export. The reports themselves: App\Services\Reports\ReportCatalog.
 */
final class ReportsController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('dashboard.view');
        $groups = [];
        foreach (ReportCatalog::visible() as $r) {
            $groups[$r['group']][] = $r;
        }
        return $this->render('admin/reports/index', [
            'groups' => $groups,
            'meta' => ReportCatalog::GROUPS,
            'total' => array_sum(array_map('count', $groups)),
        ], ['title' => 'Reports', 'nav' => 'reports', 'breadcrumbs' => [['Reports', null]]]);
    }

    public function show(Request $request, string $key): Response
    {
        $report = $this->report($key);
        $r = ReportCatalog::range($request->string('period'), $request->string('from'), $request->string('to'));
        $res = ReportCatalog::run($report, $r, 500);
        return $this->render('admin/reports/show', [
            'report' => $report,
            'r' => $r,
            'periods' => ReportCatalog::PERIODS,
            'res' => $res,
            'canExport' => can('export.csv'),
            'groupLabel' => ReportCatalog::GROUPS[$report['group']][0] ?? '',
            'siblings' => array_filter(ReportCatalog::visible(), static fn(array $x): bool => $x['group'] === $report['group'] && $x['key'] !== $report['key']),
        ], ['title' => (string) $report['title'], 'nav' => 'reports', 'breadcrumbs' => [['Reports', url('admin/reports')], [(string) $report['title'], null]]]);
    }

    public function export(Request $request, string $key): Response
    {
        $this->authorize('export.csv');
        $report = $this->report($key);
        $r = ReportCatalog::range($request->string('period'), $request->string('from'), $request->string('to'));
        $res = ReportCatalog::run($report, $r, 50000);
        $cols = array_keys($report['cols']);
        $this->audit('reports.export', 'report', null, 'Downloaded the report "' . $report['title'] . '"', ['rows' => count($res['rows'])]);
        return CsvExport::response('report-' . $report['key'] . '-' . date('Y-m-d') . '.csv', array_values($report['cols']),
            array_map(static fn(array $row): array => array_map(static fn(string $c) => $row[$c] ?? '', $cols), $res['rows']));
    }

    /** @return array<string,mixed> */
    private function report(string $key): array
    {
        $report = ReportCatalog::all()[$key] ?? throw new HttpException(404, 'Not found');
        $this->authorize('dashboard.view', (string) $report['ability']);
        return $report;
    }
}
