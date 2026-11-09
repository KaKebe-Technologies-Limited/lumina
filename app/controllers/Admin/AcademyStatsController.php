<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\CsvExport;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Services\Academy\AcademyStats;

/**
 * Admin > LAF Academy > Statistics: the Academy dashboard for the team: the headline numbers, learners by topic and by country, topics x countries, growth by month and
 * the road from sign-up to certificate. Aggregates only. Needs academy.manage. Routes: GET admin/academy/statistics, GET admin/academy/statistics/export?what=topics|countries|months.
 */
final class AcademyStatsController extends AcademyAdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('academy.manage');
        $ready = Database::tableExists('academy_learners') && Database::tableExists('academy_enrollments');
        $d = ['overview' => [], 'topics' => [], 'countries' => ['rows' => [], 'total' => 0, 'countries' => 0], 'months' => [], 'funnel' => [], 'matrix' => ['countries' => [], 'rows' => []]];
        if ($ready) {
            $d = [
                'overview'  => AcademyStats::overview(),
                'topics'    => AcademyStats::byTopic(),
                'countries' => AcademyStats::byCountry(10, true),
                'months'    => AcademyStats::byMonth(12),
                'funnel'    => AcademyStats::funnel(),
                'matrix'    => AcademyStats::topicByCountry(6),
            ];
        }
        return $this->render('admin/academy/statistics', ['d' => $d, 'ready' => $ready], ['title' => 'Academy statistics', 'nav' => 'academy_statistics', 'breadcrumbs' => $this->crumbs('Statistics')]);
    }

    public function export(Request $request): Response
    {
        $this->authorize('academy.manage');
        $what = $request->string('what');
        if ($what === 'countries') {
            $rows = array_map(static fn(array $r): array => [$r['country'], $r['n'], $r['pct'] . '%'], AcademyStats::byCountry(500, true)['rows']);
            return CsvExport::response('academy-learners-by-country-' . date('Y-m-d') . '.csv', ['Country', 'Learners', 'Share'], $rows);
        }
        if ($what === 'months') {
            $rows = array_map(static fn(array $r): array => [$r['ym'], $r['signups'], $r['certificates']], AcademyStats::byMonth(24));
            return CsvExport::response('academy-by-month-' . date('Y-m-d') . '.csv', ['Month', 'New learners', 'Certificates issued'], $rows);
        }
        $rows = array_map(static fn(array $r): array => [$r['label'], $r['courses'], $r['learners'], $r['enrollments'], $r['completed'], $r['completionPct'] . '%'], AcademyStats::byTopic());
        return CsvExport::response('academy-learners-by-topic-' . date('Y-m-d') . '.csv', ['Topic', 'Courses', 'Learners', 'Enrolments', 'Completed', 'Completion rate'], $rows);
    }
}
