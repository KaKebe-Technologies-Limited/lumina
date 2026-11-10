<?php
declare(strict_types=1);

namespace App\Services\Recruitment;

use App\Core\Database;

/**
 * Criteria the SYSTEM scores by itself from what the applicant wrote on the form (0 to 5 each). They are a starting point: the judge sees the points and the reason, may change any of them,
 * switch a criterion off for this applicant, add criteria of their own and write feedback. Criteria that need a person (reading the CV, the interview) stay manual.
 * Each key belongs to one role: a_* ambassador, j_* job, v_* volunteer.
 */
final class AutoScore
{
    /** key => [label shown to the judge, default weight, scope] */
    public const KEYS = [
        'a_motivation' => ['Motivation: why they want to be an ambassador (from the form)', 3, 'ambassador'],
        'a_reach'      => ['Reach: who they can influence (from the form)', 3, 'ambassador'],
        'a_pledge'     => ['Yearly pledge: money, items or other (from the form)', 3, 'ambassador'],
        'a_ready'      => ['Completeness: CV, portrait and profile link', 2, 'ambassador'],
        'a_time'       => ['Time they can give (from the form)', 1, 'ambassador'],
        'j_cover'      => ['Cover letter: how well it is written (from the form)', 2, 'job'],
        'j_match'      => ['Match: the cover letter against the vacancy text', 3, 'job'],
        'j_ready'      => ['Completeness: CV, portrait, phone and LinkedIn', 1, 'job'],
        'v_motivation' => ['Motivation to serve (from the form)', 3, 'volunteer'],
        'v_ready'      => ['Completeness: CV, portrait, phone and organisation', 2, 'volunteer'],
    ];

    /** Old default labels that are replaced by the automatic criterion of the same meaning (so nothing is listed twice). */
    public const OLD_LABELS = [
        'Motivation and understanding of our mission' => 'a_motivation',
        'Reach: the people and organisations they can influence' => 'a_reach',
        'Fundraising or in-kind pledge' => 'a_pledge',
        'Communication and presence (profile, CV)' => 'a_ready',
        'Time they can give' => 'a_time',
        'Quality of the cover letter' => 'j_cover',
        'Motivation to serve' => 'v_motivation',
    ];

    /** @return list<string> the automatic keys of a role */
    public static function keysFor(string $scope): array
    {
        return array_keys(array_filter(self::KEYS, static fn(array $k): bool => $k[2] === $scope));
    }

    /** Points 0-5 and the reason, or null when the form does not give enough to judge this criterion. @return array{0:int,1:string}|null */
    public static function points(string $key, array $row): ?array
    {
        return match ($key) {
            'a_motivation' => self::byLength((string) ($row['motivation'] ?? ''), 'Motivation', [60, 160, 350, 700]),
            'a_reach'      => self::byLength((string) ($row['network'] ?? ''), 'Network described', [1, 80, 200, 450]),
            'a_pledge'     => self::pledge($row),
            'a_ready'      => self::ready($row, ['cv_path' => 'CV', 'photo_path' => 'portrait', 'profile_url' => 'profile link'], [3, 1, 1]),
            'a_time'       => self::time((string) ($row['availability'] ?? '')),
            'j_cover'      => self::byLength((string) ($row['cover_letter'] ?? ''), 'Cover letter', [150, 400, 900, 1600]),
            'j_match'      => self::match($row),
            'j_ready'      => self::ready($row, ['cv_path' => 'CV', 'photo_path' => 'portrait', 'phone' => 'phone', 'linkedin_url' => 'LinkedIn'], [2, 1, 1, 1]),
            'v_motivation' => self::byLength((string) ($row['message'] ?? ''), 'Message', [30, 100, 250, 500]),
            'v_ready'      => self::ready($row, ['cv_path' => 'CV', 'photo_path' => 'portrait', 'phone' => 'phone', 'organisation' => 'organisation'], [2, 1, 1, 1]),
            default        => null,
        };
    }

    /** @param list<int> $tiers the character counts for 2, 3, 4 and 5 points (below the first: 1; empty: 0) */
    private static function byLength(string $text, string $what, array $tiers): array
    {
        $n = mb_strlen(trim($text));
        if ($n === 0) {
            return [0, $what . ' is empty'];
        }
        $p = 1;
        foreach ($tiers as $i => $t) {
            if ($n >= $t) {
                $p = $i + 2;
            }
        }
        return [min(5, $p), $what . ': ' . $n . ' characters'];
    }

    private static function pledge(array $row): array
    {
        $types = array_filter(explode(',', (string) ($row['pledge_types'] ?? '')));
        if ($types === []) {
            return [0, 'No pledge'];
        }
        $p = min(3, count($types)) + 1;
        $detail = (float) ($row['annual_amount'] ?? 0) > 0 || count(json_decode((string) ($row['inkind_items'] ?? '[]'), true) ?: []) >= 2 || mb_strlen((string) ($row['other_text'] ?? '')) >= 80;
        if ($detail) {
            $p++;
        }
        return [min(5, $p), count($types) . ' kind(s) of pledge' . ($detail ? ', with detail' : '')];
    }

    /** @param array<string,string> $fields column => label; @param list<int> $weights points given for each (they add up to 5 at most) */
    private static function ready(array $row, array $fields, array $weights): array
    {
        $p = 0;
        $have = [];
        $i = 0;
        foreach ($fields as $col => $label) {
            $v = trim((string) ($row[$col] ?? ''));
            if ($v !== '') {
                $p += $weights[$i];
                $have[] = $label;
            }
            $i++;
        }
        return [min(5, $p), $have ? 'Given: ' . implode(', ', $have) : 'Nothing given'];
    }

    private static function time(string $availability): ?array
    {
        $map = ['few_hours' => 1, 'campaigns' => 2, 'weekly' => 4, 'year_round' => 5];
        return isset($map[$availability]) ? [$map[$availability], 'Time: ' . str_replace('_', ' ', $availability)] : [0, 'Time not given'];
    }

    /** Words of the vacancy that the cover letter (and LinkedIn text) also uses. */
    private static function match(array $row): ?array
    {
        $jobId = (int) ($row['job_id'] ?? 0);
        if ($jobId <= 0) {
            return null;
        }
        $job = Database::one('SELECT title, summary, description, requirements FROM jobs WHERE id = ?', [$jobId]);
        if ($job === null) {
            return null;
        }
        $words = static function (string $t): array {
            $t = mb_strtolower(strip_tags($t));
            preg_match_all('/[\p{L}][\p{L}\-]{3,}/u', $t, $m);
            $stop = ['with', 'that', 'this', 'from', 'have', 'will', 'your', 'their', 'they', 'must', 'able', 'such', 'also', 'work', 'team', 'role', 'including', 'which', 'other', 'into', 'than', 'more', 'been', 'them', 'where', 'when', 'what', 'about', 'years', 'year'];
            return array_values(array_unique(array_diff($m[0], $stop)));
        };
        $jobWords = $words(trim((string) $job['title'] . ' ' . (string) $job['requirements'] . ' ' . (string) $job['description']));
        if (count($jobWords) < 6) {
            return null;
        }
        $mine = $words((string) ($row['cover_letter'] ?? '') . ' ' . (string) ($row['linkedin_url'] ?? ''));
        if ($mine === []) {
            return [0, 'No cover letter to compare'];
        }
        $hit = count(array_intersect($jobWords, $mine));
        $ratio = $hit / min(30, count($jobWords));
        $p = $ratio >= 0.5 ? 5 : ($ratio >= 0.35 ? 4 : ($ratio >= 0.2 ? 3 : ($ratio >= 0.1 ? 2 : ($hit > 0 ? 1 : 0))));
        return [$p, $hit . ' words of the vacancy used'];
    }
}
