<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;

/**
 * STARTER CHALLENGES of the LAF Academy: ready-made quiz challenges, an innovation challenge and a hackathon (app/config/academy/challenges/*.php,
 * format in docs/ACADEMY-CONTENT.md). They are added as DRAFTS without dates: an administrator sets the dates and publishes. Added once, by web address
 * (slug); a challenge that already exists is never changed.
 */
final class ChallengeInstaller
{
    /** @return list<array<string,mixed>> */
    public static function starter(): array
    {
        $out = [];
        $files = glob(APP_PATH . '/config/academy/challenges/*.php') ?: [];
        sort($files);
        foreach ($files as $f) {
            try {
                $c = include $f;
            } catch (\Throwable) {
                continue;
            }
            if (is_array($c) && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', (string) ($c['slug'] ?? '')) === 1) {
                $out[] = $c;
            }
        }
        return $out;
    }

    public static function pending(): int
    {
        if (!ChallengeService::ready()) {
            return 0;
        }
        $have = array_flip(array_column(Database::all('SELECT slug FROM academy_challenges'), 'slug'));
        $n = 0;
        foreach (self::starter() as $c) {
            $n += isset($have[$c['slug']]) ? 0 : 1;
        }
        return $n;
    }

    /** @return int challenges added */
    public static function installAll(?int $userId = null): int
    {
        $n = 0;
        foreach (self::starter() as $c) {
            if (Database::value('SELECT 1 FROM academy_challenges WHERE slug = ? LIMIT 1', [$c['slug']]) !== null) {
                continue;
            }
            $kind = isset(ChallengeService::KINDS[(string) $c['kind']]) ? (string) $c['kind'] : 'quiz';
            $quiz = $kind === 'quiz';
            Database::insert('academy_challenges', [
                'slug' => (string) $c['slug'], 'kind' => $kind, 'scoring' => $quiz ? 'quiz' : 'judged', 'title' => mb_substr((string) $c['title'], 0, 190),
                'category' => isset(CourseService::CATEGORIES[(string) $c['category']]) ? (string) $c['category'] : 'basics', 'summary' => mb_substr((string) ($c['summary'] ?? ''), 0, 320) ?: null,
                'description' => (string) ($c['description'] ?? '') ?: null, 'rules' => (string) ($c['rules'] ?? '') ?: null, 'prize' => mb_substr((string) ($c['prize'] ?? ''), 0, 400) ?: null,
                'status' => 'draft', 'team_max' => max(1, min(10, (int) ($c['team_max'] ?? ($kind === 'hackathon' ? 4 : 1)))), 'approval' => (string) ($c['approval'] ?? 'auto') === 'manual' ? 'manual' : 'auto',
                'quiz_script' => $quiz ? (string) ($c['quiz'] ?? '') : null, 'quiz_minutes' => $quiz ? (int) ($c['quiz_minutes'] ?? 30) : null,
                'criteria' => !$quiz ? json_encode(is_array($c['criteria'] ?? null) ? $c['criteria'] : ChallengeService::defaultCriteria()) : null,
                'winners_count' => max(1, min(10, (int) ($c['winners_count'] ?? 3))), 'pass_score' => max(0, min(100, (int) ($c['pass_score'] ?? 0))), 'source' => 'starter', 'created_by' => $userId,
            ]);
            $n++;
        }
        return $n;
    }
}
