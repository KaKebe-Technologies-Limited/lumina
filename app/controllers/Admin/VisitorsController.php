<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\CsvExport;
use App\Core\Request;
use App\Core\Response;
use App\Services\Analytics\VisitLogger;
use App\Services\Analytics\VisitReport;

/**
 * Admin > Visitors: who opens the public website. Filter by period, country and page; most visited pages, countries, devices, where visitors came from and the latest visits.
 * Routes: GET admin/visitors   GET admin/visitors/export (CSV of the filtered visits). Needs settings.manage.
 */
final class VisitorsController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('settings.manage');
        VisitLogger::ensureTables();
        $f = VisitReport::filters($request->string('period'), $request->string('country'), $request->string('page'));
        $countries = VisitReport::byCountry($f, 60);
        return $this->render('admin/visitors/index', [
            'f' => $f,
            'periods' => VisitReport::PERIODS,
            'summary' => VisitReport::summary($f),
            'pages' => VisitReport::topPages($f, 20),
            'countries' => $countries,
            'daily' => VisitReport::daily($f),
            'recent' => VisitReport::recent($f, 30),
            'referrers' => VisitReport::referrers($f, 8),
            'devices' => VisitReport::devices($f),
        ], ['title' => 'Visitors', 'nav' => 'visitors', 'breadcrumbs' => [['Visitors', null]]]);
    }

    public function export(Request $request): Response
    {
        $this->authorize('settings.manage');
        VisitLogger::ensureTables();
        $f = VisitReport::filters($request->string('period'), $request->string('country'), $request->string('page'));
        $rows = VisitReport::export($f);
        $this->audit('visitors.export', 'site_visits', null, 'Downloaded the visitor list', ['rows' => count($rows)]);
        return CsvExport::response('visitors-' . date('Y-m-d') . '.csv', ['Time', 'Page', 'Country', 'Device', 'From website'], array_map('array_values', $rows));
    }
}
