<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;

/**
 * Exam integrity for the LAF Academy (docs/ACADEMY.md, "Exam integrity"). Everything that can be decided on the SERVER is decided here:
 *  - the time limit (default by number of questions, or the limit set on the exam) and the deadline stored when the attempt starts;
 *  - "one open window only": the attempt page takes a lock (a random token + a heartbeat every few seconds); a second tab or window is refused until the first one is gone;
 *  - the log of leave events (tab switch, window blur, full-screen exit ...) and the policy: warning, final warning, then automatic submit and a "flagged" mark;
 *  - autosaved answers, so a dropped connection or an auto-submit never loses work.
 * The browser side (assets/js/academy-exam.js) blocks copy/paste and reports leaving the page, but a browser cannot be made tamper-proof: the server state is what counts.
 * Until database/updates/2026-11-exam-security.sql is imported, ready() is false and quizzes behave exactly as before.
 */
final class ExamSecurity
{
    /** Seconds after the deadline in which a last submit (slow connection) is still accepted. */
    public const GRACE_SECONDS = 30;
    /** A lock whose heartbeat is older than this is dead (the other tab was closed or lost its connection). */
    public const LOCK_STALE_SECONDS = 45;
    /** Two leave events closer than this are one leave (tab switch fires visibilitychange, blur and more at once). */
    public const LEAVE_DEDUPE_SECONDS = 3;
    public const MIN_MINUTES = 10;
    public const MAX_MINUTES = 120;
    private const MAX_EVENT_ROWS = 200;

    public static function ready(): bool
    {
        static $ok = null;
        if ($ok === null) {
            try {
                $ok = (int) Database::value("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_attempts' AND column_name IN ('deadline_at', 'token', 'flagged', 'saved_answers')", [], 0) === 4
                    && (int) Database::value("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name IN ('academy_exam_events', 'academy_exam_grants')", [], 0) === 2;
            } catch (\Throwable) {
                $ok = false;
            }
        }
        return $ok;
    }

    // ------------------------------------------------------------------------------------------------------------- settings

    public static function minutesPerQuestion(): int
    {
        return max(1, min(10, (int) setting('academy_exam_min_per_question', '1')));
    }

    public static function maxLeaves(): int
    {
        return max(1, min(10, (int) setting('academy_exam_max_leaves', '3')));
    }

    public static function lessonQuizzes(): bool
    {
        return (string) setting('academy_exam_lesson_quizzes', '0') === '1';
    }

    public static function fullscreenOffered(): bool
    {
        return (string) setting('academy_exam_fullscreen', '1') === '1';
    }

    /** Do the exam rules (time limit, rules screen, copy/paste block, leave watch, one window) apply to this quiz? Final exams always, lesson quizzes when switched on. */
    public static function applies(array $quiz): bool
    {
        return self::ready() && ($quiz['module_id'] === null || self::lessonQuizzes());
    }

    /** Default time for a quiz of $questions questions: minutes per question, kept between 10 and 120. */
    public static function defaultMinutes(int $questions): int
    {
        return max(self::MIN_MINUTES, min(self::MAX_MINUTES, $questions * self::minutesPerQuestion()));
    }

    /** The time limit in minutes of a quiz: the one set on the quiz, else (when the rules apply) the default for its number of questions, else null (no limit). */
    public static function limitMinutes(array $quiz): ?int
    {
        $set = (int) ($quiz['time_limit_min'] ?? 0);
        if ($set > 0) {
            return $set;
        }
        return self::applies($quiz) ? self::defaultMinutes(QuizService::questionCount((int) $quiz['id'])) : null;
    }

    // ------------------------------------------------------------------------------------------------------------- one window only

    private static function staleBefore(): string
    {
        return date('Y-m-d H:i:s', time() - self::LOCK_STALE_SECONDS);
    }

    /**
     * Take the "this window is taking the exam" lock of an open secured attempt. $token is the token this window already holds (empty for a new page load).
     * @return array{ok:bool,token:string} ok=false: another window holds the lock (its heartbeat is recent)
     */
    public static function claim(array $attempt, string $token = ''): array
    {
        $id = (int) $attempt['id'];
        $mine = bin2hex(random_bytes(16));
        Database::execute(
            'UPDATE academy_attempts SET token = ?, heartbeat_at = ? WHERE id = ? AND submitted_at IS NULL AND (heartbeat_at IS NULL OR heartbeat_at < ? OR token IS NULL OR (? <> \'\' AND token = ?))',
            [$mine, date('Y-m-d H:i:s'), $id, self::staleBefore(), $token, $token]
        );
        $now = (string) Database::value('SELECT token FROM academy_attempts WHERE id = ?', [$id], '');
        if ($now === $mine) {
            return ['ok' => true, 'token' => $mine];
        }
        if ($token !== '' && hash_equals($now, $token)) {
            return ['ok' => true, 'token' => $token];
        }
        self::record($id, 'second_tab', false, null, 'Opened in a second window or tab');
        return ['ok' => false, 'token' => ''];
    }

    /** Is the attempt currently held by a window other than $token? (A submit from a window that does not hold the lock is refused.) */
    public static function heldByOther(array $attempt, string $token): bool
    {
        $hb = $attempt['heartbeat_at'] ?? null;
        if ($hb === null || strtotime((string) $hb) < time() - self::LOCK_STALE_SECONDS) {
            return false;
        }
        return $token === '' || !hash_equals((string) ($attempt['token'] ?? ''), $token);
    }

    // ------------------------------------------------------------------------------------------------------------- the events log

    public static function record(int $attemptId, string $kind, bool $counted, ?int $secondsAway = null, ?string $detail = null): void
    {
        if ((int) Database::value('SELECT COUNT(*) FROM academy_exam_events WHERE attempt_id = ?', [$attemptId], 0) >= self::MAX_EVENT_ROWS) {
            return;                                   // a flood of events cannot fill the table
        }
        Database::insert('academy_exam_events', [
            'attempt_id' => $attemptId, 'kind' => mb_substr($kind, 0, 20), 'counted' => $counted ? 1 : 0,
            'seconds_away' => $secondsAway !== null ? max(0, min(65000, $secondsAway)) : null, 'detail' => $detail !== null ? mb_substr($detail, 0, 190) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /** @return list<array<string,mixed>> */
    public static function events(int $attemptId): array
    {
        return self::ready() ? Database::all('SELECT * FROM academy_exam_events WHERE attempt_id = ? ORDER BY id ASC', [$attemptId]) : [];
    }

    private const EVENT_KINDS = ['leave', 'return', 'blur', 'pagehide', 'fullscreen_exit', 'copy_blocked', 'release', 'save'];

    /**
     * One call from the open exam page (every few seconds, and at once when something happens): keeps the lock alive, saves the answers, records an event.
     * @param array<int|string,mixed>|null $answers question id => chosen option ids, or null when nothing changed
     * @return array{status:string,left:?int,leaves:int,max:int,redirect:?string}  status: open | submitted | locked
     */
    public static function sync(array $attempt, array $quiz, string $token, ?array $answers, string $event = '', int $away = 0, string $detail = ''): array
    {
        $max = self::maxLeaves();
        $id = (int) $attempt['id'];
        if ($attempt['submitted_at'] !== null) {
            return ['status' => 'submitted', 'left' => null, 'leaves' => (int) $attempt['leave_count'], 'max' => $max, 'redirect' => null];
        }
        if ($token === '' || (string) ($attempt['token'] ?? '') === '' || !hash_equals((string) $attempt['token'], $token)) {
            return ['status' => 'locked', 'left' => QuizService::secondsLeft($attempt, $quiz), 'leaves' => (int) $attempt['leave_count'], 'max' => $max, 'redirect' => null];
        }
        $left = QuizService::secondsLeft($attempt, $quiz);
        if ($left !== null && $left < -self::GRACE_SECONDS) {
            $done = QuizService::submit($attempt, $quiz, null, 'time');
            return ['status' => 'submitted', 'left' => 0, 'leaves' => (int) $done['leave_count'], 'max' => $max, 'redirect' => null];
        }
        if ($answers !== null) {
            QuizService::saveAnswers($attempt, $quiz, $answers);
            $attempt = (array) Database::one('SELECT * FROM academy_attempts WHERE id = ?', [$id]);
        }
        $leaves = (int) $attempt['leave_count'];
        if ($event === 'release') {
            Database::execute('UPDATE academy_attempts SET heartbeat_at = NULL WHERE id = ? AND token = ?', [$id, $token]);
            self::record($id, 'pagehide', false, null, 'Page closed or reloaded');
            return ['status' => 'open', 'left' => $left, 'leaves' => $leaves, 'max' => $max, 'redirect' => null];
        }
        Database::execute('UPDATE academy_attempts SET heartbeat_at = ? WHERE id = ? AND token = ?', [date('Y-m-d H:i:s'), $id, $token]);
        if (in_array($event, ['leave', 'fullscreen_exit'], true)) {
            $leaves = self::countLeave($id, $event, $detail);
            if ($leaves >= $max) {
                self::record($id, 'auto_submit', false, null, 'Reached ' . $max . ' leaves: submitted automatically');
                $fresh = (array) Database::one('SELECT * FROM academy_attempts WHERE id = ?', [$id]);
                QuizService::submit($fresh, $quiz, null, 'violations', true);
                return ['status' => 'submitted', 'left' => $left, 'leaves' => $leaves, 'max' => $max, 'redirect' => null];
            }
        } elseif (in_array($event, ['return', 'blur', 'pagehide', 'copy_blocked'], true)) {
            self::record($id, $event, false, $event === 'return' ? $away : null, $detail !== '' ? $detail : null);
        }
        return ['status' => 'open', 'left' => $left, 'leaves' => $leaves, 'max' => $max, 'redirect' => null];
    }

    /** Count one leave (events within a few seconds of the last counted one are the same leave). @return int the number of leaves counted so far */
    private static function countLeave(int $attemptId, string $kind, string $detail): int
    {
        $last = Database::value("SELECT created_at FROM academy_exam_events WHERE attempt_id = ? AND counted = 1 ORDER BY id DESC LIMIT 1", [$attemptId]);
        if ($last !== null && strtotime((string) $last) > time() - self::LEAVE_DEDUPE_SECONDS) {
            self::record($attemptId, $kind, false, null, 'Same leave as the one just counted');
        } else {
            Database::execute('UPDATE academy_attempts SET leave_count = leave_count + 1 WHERE id = ?', [$attemptId]);
            self::record($attemptId, $kind, true, null, $detail !== '' ? $detail : null);
        }
        return (int) Database::value('SELECT leave_count FROM academy_attempts WHERE id = ?', [$attemptId], 0);
    }

    public static function cleanEvent(string $event): string
    {
        return in_array($event, self::EVENT_KINDS, true) ? $event : '';
    }

    // ------------------------------------------------------------------------------------------------------------- flagged attempts (review by an administrator)

    /** An attempt that was stopped for leaving too often and has not been cleared by an administrator: it does not count towards the certificate. */
    public static function blocksCertificate(array $attempt): bool
    {
        return (int) ($attempt['flagged'] ?? 0) === 1 && ($attempt['flag_review'] ?? null) !== 'cleared';
    }

    /** SQL fragment (with a leading AND) that leaves out flagged attempts that were not cleared. */
    public static function countsSql(string $alias = ''): string
    {
        return self::ready() ? " AND ({$alias}flagged = 0 OR {$alias}flag_review = 'cleared')" : '';
    }

    /**
     * An administrator decides about a flagged attempt: 'cleared' (it counts again: the normal certificate flow follows) or 'rejected' (it does not count).
     * @return bool true when a flagged attempt was updated
     */
    public static function review(int $attemptId, string $decision, ?int $adminId): bool
    {
        if (!self::ready() || !in_array($decision, ['cleared', 'rejected'], true)) {
            return false;
        }
        $n = Database::execute(
            'UPDATE academy_attempts SET flag_review = ?, flag_reviewed_by = ?, flag_reviewed_at = ? WHERE id = ? AND flagged = 1 AND submitted_at IS NOT NULL',
            [$decision, $adminId, date('Y-m-d H:i:s'), $attemptId]
        );
        if ($n > 0 && $decision === 'cleared') {
            $a = Database::one('SELECT learner_id, course_id FROM academy_attempts WHERE id = ?', [$attemptId]);
            if ($a !== null) {
                CourseService::checkCompletion((int) $a['learner_id'], (int) $a['course_id']);        // files the certificate for approval, as after any passed exam
            }
        }
        return $n > 0;
    }

    /** Flagged attempts waiting for a decision. @return list<array<string,mixed>> */
    public static function flaggedWaiting(int $limit = 50): array
    {
        if (!self::ready()) {
            return [];
        }
        return Database::all(
            'SELECT a.*, l.name AS learner_name, l.email AS learner_email, c.title AS course_title, q.title AS quiz_title FROM academy_attempts a JOIN academy_learners l ON l.id = a.learner_id JOIN academy_courses c ON c.id = a.course_id JOIN academy_quizzes q ON q.id = a.quiz_id WHERE a.flagged = 1 AND a.flag_review IS NULL AND a.submitted_at IS NOT NULL ORDER BY a.id DESC LIMIT ' . max(1, $limit)
        );
    }

    public static function flaggedCount(): int
    {
        return self::ready() ? (int) Database::value('SELECT COUNT(*) FROM academy_attempts WHERE flagged = 1 AND flag_review IS NULL AND submitted_at IS NOT NULL', [], 0) : 0;
    }

    /** "12 min 30 s" for the time between start and submit (null when not finished). */
    public static function duration(array $attempt): ?string
    {
        if ($attempt['submitted_at'] === null) {
            return null;
        }
        $s = max(0, strtotime((string) $attempt['submitted_at']) - strtotime((string) $attempt['started_at']));
        return intdiv($s, 60) . ' min ' . ($s % 60) . ' s';
    }

    // ------------------------------------------------------------------------------------------------------------- retakes and extra time (administrator)

    /**
     * Let one learner retake an exam and/or give extra minutes. Extra attempts raise the limit of the learner for this quiz; extra minutes extend an attempt that is open now,
     * or are added to the learner's next attempt.
     */
    public static function grant(int $learnerId, int $quizId, int $extraAttempts, int $extraMinutes, ?string $note, ?int $adminId): void
    {
        $extraAttempts = max(0, min(10, $extraAttempts));
        $extraMinutes = max(0, min(240, $extraMinutes));
        $open = Database::one('SELECT * FROM academy_attempts WHERE learner_id = ? AND quiz_id = ? AND submitted_at IS NULL ORDER BY id DESC LIMIT 1', [$learnerId, $quizId]);
        $usedOn = null;
        if ($open !== null && $extraMinutes > 0) {
            Database::execute('UPDATE academy_attempts SET extra_minutes = extra_minutes + ?, deadline_at = DATE_ADD(COALESCE(deadline_at, ?), INTERVAL ? MINUTE) WHERE id = ?', [$extraMinutes, date('Y-m-d H:i:s'), $extraMinutes, (int) $open['id']]);
            $usedOn = (int) $open['id'];
        }
        Database::insert('academy_exam_grants', [
            'learner_id' => $learnerId, 'quiz_id' => $quizId, 'extra_attempts' => $extraAttempts, 'extra_minutes' => $extraMinutes,
            'note' => $note !== null && $note !== '' ? mb_substr($note, 0, 190) : null, 'granted_by' => $adminId, 'used_attempt_id' => $usedOn, 'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function extraAttempts(int $learnerId, int $quizId): int
    {
        return self::ready() ? (int) Database::value('SELECT COALESCE(SUM(extra_attempts), 0) FROM academy_exam_grants WHERE learner_id = ? AND quiz_id = ?', [$learnerId, $quizId], 0) : 0;
    }

    /** Extra minutes waiting for the next attempt (marks them used by $attemptId). */
    public static function takeExtraMinutes(int $learnerId, int $quizId, int $attemptId): int
    {
        if (!self::ready()) {
            return 0;
        }
        $m = (int) Database::value('SELECT COALESCE(SUM(extra_minutes), 0) FROM academy_exam_grants WHERE learner_id = ? AND quiz_id = ? AND used_attempt_id IS NULL', [$learnerId, $quizId], 0);
        if ($m > 0) {
            Database::execute('UPDATE academy_exam_grants SET used_attempt_id = ? WHERE learner_id = ? AND quiz_id = ? AND used_attempt_id IS NULL AND extra_minutes > 0', [$attemptId, $learnerId, $quizId]);
        }
        return $m;
    }

    /** Peek at the extra minutes that will be added (not used up). */
    public static function pendingExtraMinutes(int $learnerId, int $quizId): int
    {
        return self::ready() ? (int) Database::value('SELECT COALESCE(SUM(extra_minutes), 0) FROM academy_exam_grants WHERE learner_id = ? AND quiz_id = ? AND used_attempt_id IS NULL', [$learnerId, $quizId], 0) : 0;
    }

    public static function submitReasonLabel(?string $reason): string
    {
        return match ($reason) {
            'time' => 'Time ran out', 'violations' => 'Stopped: left the page too often', 'late' => 'Late: saved answers only', default => 'Submitted by the learner',
        };
    }
}
