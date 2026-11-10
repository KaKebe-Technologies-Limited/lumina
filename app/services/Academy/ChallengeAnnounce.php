<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;

/**
 * "Tell the learners about this challenge": e-mail (and portal message) to every ACTIVE learner (approved, e-mail confirmed) about an OPEN challenge, a limited number per run so the mail
 * account's daily limit is never emptied in one go. A learner counts as told once the e-mail went out AND the portal copy (which starts with a fixed line naming the challenge) was saved,
 * so pressing the button again continues with those still waiting and never writes twice (unless "again"). Scope: 'all' active learners, or 'new' = only those who have not entered yet.
 */
final class ChallengeAnnounce
{
    /** The phases in which telling people still makes sense. */
    public const OPEN_PHASES = ['upcoming', 'applications', 'live'];

    public static function isOpen(array $c): bool
    {
        return $c['status'] === 'published' && empty($c['finalized_at']) && in_array(ChallengeService::phase($c), self::OPEN_PHASES, true);
    }

    /** The first line of the portal copy: how a later run knows who was told. */
    public static function mark(array $c): string
    {
        return 'New challenge: ' . $c['title'];
    }

    public static function portalText(array $c, string $note = ''): string
    {
        $parts = [self::mark($c) . '.'];
        if (trim((string) ($c['summary'] ?? '')) !== '') {
            $parts[] = trim((string) $c['summary']);
        }
        if (trim($note) !== '') {
            $parts[] = trim($note);
        }
        $parts[] = 'Open the challenge and join: ' . abs_url('academy/challenges/' . $c['slug']) . ' . We have also e-mailed you these details.';
        return implode(' ', $parts);
    }

    /** @return list<array<string,mixed>> the active learners to tell (not yet told, unless $again; without those who already entered when $scope is "new") */
    public static function pending(array $c, string $scope = 'all', bool $again = false): array
    {
        $all = LearnerMessages::approvedLearners();
        $skip = [];
        if ($scope === 'new') {
            foreach (Database::all("SELECT DISTINCT learner_id FROM academy_challenge_entries WHERE challenge_id = ? AND status <> 'withdrawn'", [(int) $c['id']]) as $r) {
                $skip[(int) $r['learner_id']] = true;
            }
        }
        if (!$again && LearnerMessages::ready()) {
            $like = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], self::mark($c)) . '%';
            foreach (Database::all("SELECT DISTINCT learner_id FROM academy_messages WHERE sender = 'admin' AND body LIKE ?", [$like]) as $r) {
                $skip[(int) $r['learner_id']] = true;
            }
        }
        return array_values(array_filter($all, static fn(array $l): bool => !isset($skip[(int) $l['id']])));
    }

    /** How many learners the next run would write to, and how many active learners there are in all. @return array{todo:int,active:int,told:int} */
    public static function counts(array $c, string $scope = 'all'): array
    {
        $active = count(LearnerMessages::approvedLearners());
        $todo = count(self::pending($c, $scope));
        return ['todo' => $todo, 'active' => $active, 'told' => max(0, count(self::pending($c, $scope, true)) - $todo)];
    }

    // ------------------------------------------------------------------------------------------------------------- one e-mail about ALL the open challenges

    /** @return list<array<string,mixed>> the published challenges that are still open (applications or running), soonest first */
    public static function openList(): array
    {
        if (!ChallengeService::ready()) {
            return [];
        }
        $out = [];
        foreach (Database::all("SELECT * FROM academy_challenges WHERE status = 'published' AND finalized_at IS NULL ORDER BY COALESCE(starts_at, created_at) ASC LIMIT 30") as $c) {
            if (self::isOpen($c)) {
                $out[] = $c;
            }
        }
        return $out;
    }

    /** The first words of the portal copy of a digest: the same set of challenges is never sent twice to a learner. */
    public static function digestMark(array $list): string
    {
        $ids = array_map(static fn(array $c): int => (int) $c['id'], $list);
        sort($ids);
        return 'Open challenges [' . implode(',', $ids) . ']';
    }

    public static function digestPortalText(array $list, string $note = ''): string
    {
        $names = implode('; ', array_map(static fn(array $c): string => (string) $c['title'], $list));
        return self::digestMark($list) . ': ' . $names . '. ' . (trim($note) !== '' ? trim($note) . ' ' : '') . 'Read about each one, its benefits and how to join: ' . abs_url('academy/challenges') . ' . We have also e-mailed you the details.';
    }

    /** @return list<array<string,mixed>> the active learners not yet told about exactly this set of challenges (unless $again) */
    public static function digestPending(array $list, bool $again = false): array
    {
        $all = LearnerMessages::approvedLearners();
        if ($again || $list === [] || !LearnerMessages::ready()) {
            return $all;
        }
        $like = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], self::digestMark($list)) . '%';
        $done = [];
        foreach (Database::all("SELECT DISTINCT learner_id FROM academy_messages WHERE sender = 'admin' AND body LIKE ?", [$like]) as $r) {
            $done[(int) $r['learner_id']] = true;
        }
        return array_values(array_filter($all, static fn(array $l): bool => !isset($done[(int) $l['id']])));
    }

    /** @return array{sent:int,failed:int,remaining:int,total:int,stopped:?string} */
    public static function sendDigest(array $list, string $note, bool $again, ?int $adminId, int $maxEmails, int $maxSeconds = 40): array
    {
        $todo = $list === [] ? [] : self::digestPending($list, $again);
        $total = count($todo);
        $sent = 0;
        $failed = 0;
        $streak = 0;
        $stopped = null;
        $t0 = microtime(true);
        foreach ($todo as $l) {
            if ($sent >= $maxEmails) {
                break;
            }
            if (microtime(true) - $t0 > $maxSeconds) {
                $stopped = 'time';
                break;
            }
            if (AcademyMail::challengesDigest($list, $l, $note)) {
                LearnerMessages::send((int) $l['id'], 'admin', self::digestPortalText($list, $note), $adminId);
                $sent++;
                $streak = 0;
                continue;
            }
            $failed++;
            if (++$streak >= 3 || str_contains((string) AcademyMail::lastError(), 'SMTP login')) {
                $stopped = AcademyMail::lastError() ?: 'The mail server did not accept the messages.';
                break;
            }
        }
        return ['sent' => $sent, 'failed' => $failed, 'remaining' => max(0, $total - $sent), 'total' => $total, 'stopped' => $stopped];
    }

    /** @return array{sent:int,failed:int,remaining:int,total:int,stopped:?string} */
    public static function send(array $c, string $scope, string $note, bool $again, ?int $adminId, int $maxEmails, int $maxSeconds = 40): array
    {
        $todo = self::pending($c, $scope, $again);
        $total = count($todo);
        $sent = 0;
        $failed = 0;
        $streak = 0;
        $stopped = null;
        $t0 = microtime(true);
        foreach ($todo as $l) {
            if ($sent >= $maxEmails) {
                break;
            }
            if (microtime(true) - $t0 > $maxSeconds) {
                $stopped = 'time';
                break;
            }
            if (AcademyMail::challengeOpen($c, $l, $note)) {
                LearnerMessages::send((int) $l['id'], 'admin', self::portalText($c, $note), $adminId);
                $sent++;
                $streak = 0;
                continue;
            }
            $failed++;
            if (++$streak >= 3 || str_contains((string) AcademyMail::lastError(), 'SMTP login')) {
                $stopped = AcademyMail::lastError() ?: 'The mail server did not accept the messages.';
                break;
            }
        }
        return ['sent' => $sent, 'failed' => $failed, 'remaining' => max(0, $total - $sent), 'total' => $total, 'stopped' => $stopped];
    }
}
