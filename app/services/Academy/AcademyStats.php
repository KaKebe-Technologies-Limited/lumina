<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;

/**
 * LAF ACADEMY STATISTICS for the dashboards: the public page /academy/impact and the block on /academy, and Admin > LAF Academy > Statistics.
 * Everything here is an AGGREGATE (counts and percentages): no names, e-mails or other personal details ever leave this class. A "topic" is the category of a course
 * (CourseService::CATEGORIES); the country is the one the learner gave at sign-up (free text, matched without regard to capitals or spaces).
 */
final class AcademyStats
{
    /** Colours of the pie / legend, in order (brand navy, gold, orange, then calmer tones). */
    public const PALETTE = ['#142B4A', '#D9A441', '#C96A22', '#138A45', '#7A2FC0', '#1F5FA8', '#B3151C', '#6B7A90', '#8FA3BF'];

    private static function n(string $sql, array $p = []): int
    {
        return (int) Database::value($sql, $p, 0);
    }

    /** @return array<string,int|float> the headline numbers */
    public static function overview(): array
    {
        $enroll = self::n('SELECT COUNT(*) FROM academy_enrollments');
        $done = self::n('SELECT COUNT(*) FROM academy_enrollments WHERE completed_at IS NOT NULL');
        return [
            'courses'       => self::n("SELECT COUNT(*) FROM academy_courses WHERE status = 'published'"),
            'lessons'       => self::n("SELECT COUNT(*) FROM academy_lessons l JOIN academy_courses c ON c.id = l.course_id WHERE c.status = 'published' AND l.is_published = 1"),
            'learners'      => self::n('SELECT COUNT(*) FROM academy_learners WHERE is_active = 1'),
            'enrollments'   => $enroll,
            'completed'     => $done,
            'completionPct' => $enroll > 0 ? round($done * 100 / $enroll, 1) : 0.0,
            'certificates'  => self::n('SELECT COUNT(*) FROM academy_certificates WHERE revoked_at IS NULL'),
            'countries'     => self::n("SELECT COUNT(DISTINCT LOWER(TRIM(country))) FROM academy_learners WHERE is_active = 1 AND TRIM(country) <> ''"),
            'topics'        => self::n("SELECT COUNT(DISTINCT category) FROM academy_courses WHERE status = 'published'"),
            'new30'         => self::n('SELECT COUNT(*) FROM academy_learners WHERE is_active = 1 AND created_at >= (NOW() - INTERVAL 30 DAY)'),
        ];
    }

    /**
     * Learners by TOPIC (course category). A learner counts once per topic however many of its courses they joined.
     * @return list<array{key:string,label:string,icon:string,courses:int,learners:int,enrollments:int,completed:int,completionPct:float}>
     */
    public static function byTopic(): array
    {
        $rows = Database::all(
            "SELECT c.category AS k, COUNT(DISTINCT c.id) AS courses, COUNT(DISTINCT e.learner_id) AS learners, COUNT(e.id) AS enrollments, COALESCE(SUM(e.completed_at IS NOT NULL), 0) AS completed
             FROM academy_courses c LEFT JOIN academy_enrollments e ON e.course_id = c.id
             WHERE c.status = 'published' GROUP BY c.category"
        );
        $out = [];
        foreach ($rows as $r) {
            $key = (string) $r['k'];
            $cat = CourseService::CATEGORIES[$key] ?? [ucfirst($key), 'book'];
            $enr = (int) $r['enrollments'];
            $out[] = ['key' => $key, 'label' => $cat[0], 'icon' => $cat[1], 'courses' => (int) $r['courses'], 'learners' => (int) $r['learners'], 'enrollments' => $enr,
                'completed' => (int) $r['completed'], 'completionPct' => $enr > 0 ? round((int) $r['completed'] * 100 / $enr, 1) : 0.0];
        }
        usort($out, static fn(array $a, array $b): int => [$b['learners'], $b['enrollments'], $b['courses']] <=> [$a['learners'], $a['enrollments'], $a['courses']]);
        return $out;
    }

    /**
     * Learners by COUNTRY: the biggest ones, then "Other countries". $notGiven = also list learners who gave no country (admin).
     * @return array{rows:list<array{country:string,n:int,pct:float}>,total:int,countries:int}
     */
    public static function byCountry(int $limit = 8, bool $notGiven = false): array
    {
        $rows = Database::all(
            "SELECT MIN(TRIM(country)) AS c, COUNT(*) AS n FROM academy_learners WHERE is_active = 1 AND TRIM(country) <> '' GROUP BY LOWER(TRIM(country)) ORDER BY n DESC, c ASC"
        );
        $missing = $notGiven ? self::n("SELECT COUNT(*) FROM academy_learners WHERE is_active = 1 AND TRIM(COALESCE(country, '')) = ''") : 0;
        $total = array_sum(array_map(static fn(array $r): int => (int) $r['n'], $rows)) + $missing;
        $countries = count($rows);
        $head = array_slice($rows, 0, $limit);
        $tail = array_slice($rows, $limit);
        $out = [];
        foreach ($head as $r) {
            $out[] = ['country' => self::pretty((string) $r['c']), 'n' => (int) $r['n'], 'pct' => $total > 0 ? round((int) $r['n'] * 100 / $total, 1) : 0.0];
        }
        $rest = array_sum(array_map(static fn(array $r): int => (int) $r['n'], $tail));
        if ($rest > 0) {
            $out[] = ['country' => 'Other countries (' . count($tail) . ')', 'n' => $rest, 'pct' => $total > 0 ? round($rest * 100 / $total, 1) : 0.0];
        }
        if ($missing > 0) {
            $out[] = ['country' => 'Not given', 'n' => $missing, 'pct' => $total > 0 ? round($missing * 100 / $total, 1) : 0.0];
        }
        return ['rows' => $out, 'total' => $total, 'countries' => $countries];
    }

    /** "uganda" -> "Uganda", "SOUTH SUDAN" -> "South Sudan"; names that already have capitals are kept. */
    private static function pretty(string $c): string
    {
        $c = trim($c);
        return $c === mb_strtolower($c) || $c === mb_strtoupper($c) ? mb_convert_case($c, MB_CASE_TITLE, 'UTF-8') : $c;
    }

    /**
     * New learners and certificates per month, the last $months months (oldest first, empty months included).
     * @return list<array{ym:string,label:string,signups:int,certificates:int}>
     */
    public static function byMonth(int $months = 12): array
    {
        $from = (new \DateTimeImmutable('first day of this month 00:00:00'))->modify('-' . ($months - 1) . ' months');
        $sign = Database::all("SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS n FROM academy_learners WHERE created_at >= ? GROUP BY ym", [$from->format('Y-m-d H:i:s')]);
        $cert = Database::all("SELECT DATE_FORMAT(issued_at, '%Y-%m') AS ym, COUNT(*) AS n FROM academy_certificates WHERE revoked_at IS NULL AND issued_at >= ? GROUP BY ym", [$from->format('Y-m-d H:i:s')]);
        $s = array_column($sign, 'n', 'ym');
        $c = array_column($cert, 'n', 'ym');
        $out = [];
        for ($i = 0; $i < $months; $i++) {
            $d = $from->modify('+' . $i . ' months');
            $ym = $d->format('Y-m');
            $out[] = ['ym' => $ym, 'label' => $d->format('M'), 'signups' => (int) ($s[$ym] ?? 0), 'certificates' => (int) ($c[$ym] ?? 0)];
        }
        return $out;
    }

    /**
     * From sign-up to certificate: how many learners reached each step (each learner counted once).
     * @return list<array{label:string,n:int,pct:float}>
     */
    public static function funnel(): array
    {
        $steps = [
            ['Signed up', self::n('SELECT COUNT(*) FROM academy_learners WHERE is_active = 1')],
            ['Confirmed their e-mail', self::n('SELECT COUNT(*) FROM academy_learners WHERE is_active = 1 AND email_verified_at IS NOT NULL')],
            ['Approved', self::n('SELECT COUNT(*) FROM academy_learners WHERE is_active = 1 AND approved_at IS NOT NULL')],
            ['Joined a course', self::n('SELECT COUNT(DISTINCT e.learner_id) FROM academy_enrollments e JOIN academy_learners l ON l.id = e.learner_id WHERE l.is_active = 1')],
            ['Finished a course', self::n('SELECT COUNT(DISTINCT e.learner_id) FROM academy_enrollments e JOIN academy_learners l ON l.id = e.learner_id WHERE l.is_active = 1 AND e.completed_at IS NOT NULL')],
            ['Earned a certificate', self::n('SELECT COUNT(DISTINCT c.learner_id) FROM academy_certificates c JOIN academy_learners l ON l.id = c.learner_id WHERE l.is_active = 1 AND c.revoked_at IS NULL')],
        ];
        $base = max(1, (int) $steps[0][1]);
        return array_map(static fn(array $s): array => ['label' => $s[0], 'n' => (int) $s[1], 'pct' => round((int) $s[1] * 100 / $base, 1)], $steps);
    }

    /**
     * Topics x countries: how many learners of each of the biggest countries joined each topic (admin).
     * @param list<string> $countries display names from byCountry(), "Other" / "Not given" rows are skipped
     * @return array{countries:list<string>,rows:list<array{label:string,cells:list<int>}>}
     */
    public static function topicByCountry(int $countryLimit = 6): array
    {
        $top = array_values(array_filter(array_map(static fn(array $r): string => (string) $r['country'], self::byCountry($countryLimit)['rows']),
            static fn(string $c): bool => !str_starts_with($c, 'Other countries') && $c !== 'Not given'));
        if ($top === []) {
            return ['countries' => [], 'rows' => []];
        }
        $rows = Database::all(
            "SELECT c.category AS k, LOWER(TRIM(l.country)) AS lc, COUNT(DISTINCT e.learner_id) AS n
             FROM academy_enrollments e JOIN academy_courses c ON c.id = e.course_id JOIN academy_learners l ON l.id = e.learner_id
             WHERE l.is_active = 1 AND TRIM(l.country) <> '' GROUP BY c.category, LOWER(TRIM(l.country))"
        );
        $grid = [];
        foreach ($rows as $r) {
            $grid[(string) $r['k']][(string) $r['lc']] = (int) $r['n'];
        }
        $out = [];
        foreach (self::byTopic() as $t) {
            $cells = [];
            foreach ($top as $name) {
                $cells[] = (int) ($grid[$t['key']][mb_strtolower($name)] ?? 0);
            }
            if (array_sum($cells) > 0) {
                $out[] = ['label' => $t['label'], 'cells' => $cells];
            }
        }
        return ['countries' => $top, 'rows' => $out];
    }

    /** Everything the public dashboard shows. @return array<string,mixed> */
    public static function publicData(): array
    {
        return ['overview' => self::overview(), 'topics' => self::byTopic(), 'countries' => self::byCountry(8), 'months' => self::byMonth(12), 'funnel' => self::funnel()];
    }
}
