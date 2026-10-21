<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;

/**
 * Quizzes and the final exam of the LAF Academy: storing questions (from the QuizScript text), starting an attempt (question and answer order shuffled and
 * remembered), grading on the SERVER (correct answers never leave the server until the attempt is submitted), attempt limits and the optional time limit.
 * A "pick one" question is right when exactly the correct answer is chosen; a "pick all that apply" question needs exactly the right set (no partial credit).
 */
final class QuizService
{
    /** Extra seconds allowed after the time limit (slow connections). Later answers count as blank. */
    private const GRACE_SECONDS = 90;

    // ------------------------------------------------------------------------------------------------------------- authoring

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM academy_quizzes WHERE id = ? LIMIT 1', [$id]);
    }

    public static function forModule(int $moduleId): ?array
    {
        return Database::one('SELECT * FROM academy_quizzes WHERE module_id = ? LIMIT 1', [$moduleId]);
    }

    public static function exam(int $courseId): ?array
    {
        return Database::one('SELECT * FROM academy_quizzes WHERE course_id = ? AND module_id IS NULL LIMIT 1', [$courseId]);
    }

    /** Create the quiz row of a module (or the final exam with $moduleId = null) when missing. @return array<string,mixed> */
    public static function ensure(int $courseId, ?int $moduleId, string $title, int $passMark = 70, ?int $timeLimit = null, int $maxAttempts = 0): array
    {
        $q = $moduleId === null ? self::exam($courseId) : self::forModule($moduleId);
        if ($q !== null) {
            return $q;
        }
        $id = Database::insert('academy_quizzes', [
            'course_id' => $courseId, 'module_id' => $moduleId, 'title' => mb_substr($title, 0, 190),
            'pass_mark' => max(1, min(100, $passMark)), 'time_limit_min' => $timeLimit, 'max_attempts' => max(0, $maxAttempts),
        ]);
        return (array) self::find($id);
    }

    /** @return list<array<string,mixed>> questions in order, each with 'options' (id, label, is_correct) in order */
    public static function questions(int $quizId): array
    {
        $qs = Database::all('SELECT * FROM academy_questions WHERE quiz_id = ? ORDER BY sort_order ASC, id ASC', [$quizId]);
        if ($qs === []) {
            return [];
        }
        $ids = array_map(static fn(array $q): int => (int) $q['id'], $qs);
        $opts = Database::all(
            'SELECT * FROM academy_options WHERE question_id IN (' . implode(',', $ids) . ') ORDER BY sort_order ASC, id ASC'
        );
        $by = [];
        foreach ($opts as $o) {
            $by[(int) $o['question_id']][] = $o;
        }
        foreach ($qs as &$q) {
            $q['options'] = $by[(int) $q['id']] ?? [];
        }
        unset($q);
        return $qs;
    }

    public static function questionCount(int $quizId): int
    {
        return (int) Database::value('SELECT COUNT(*) FROM academy_questions WHERE quiz_id = ?', [$quizId], 0);
    }

    /** The text shown in the quiz editor. */
    public static function script(int $quizId): string
    {
        return QuizScript::export(self::questions($quizId));
    }

    /**
     * Replace ALL questions of a quiz with parsed QuizScript questions (the editor saves the whole text). Past attempts keep their stored answers,
     * the questions they pointed at are gone, so past attempts still show their score but not their review.
     * @param list<array{prompt:string,type:string,explanation:string,options:list<array{label:string,correct:bool}>}> $questions
     */
    public static function replaceQuestions(int $quizId, array $questions): void
    {
        Database::transaction(function () use ($quizId, $questions): void {
            Database::execute('DELETE FROM academy_questions WHERE quiz_id = ?', [$quizId]);
            $n = 10;
            foreach ($questions as $q) {
                $qid = Database::insert('academy_questions', [
                    'quiz_id' => $quizId, 'prompt' => mb_substr($q['prompt'], 0, 600), 'type' => $q['type'],
                    'explanation' => $q['explanation'] !== '' ? $q['explanation'] : null, 'sort_order' => $n,
                ]);
                $n += 10;
                $o = 10;
                foreach ($q['options'] as $opt) {
                    Database::insert('academy_options', ['question_id' => $qid, 'label' => mb_substr($opt['label'], 0, 255), 'is_correct' => $opt['correct'] ? 1 : 0, 'sort_order' => $o]);
                    $o += 10;
                }
            }
        });
    }

    // ------------------------------------------------------------------------------------------------------------- attempts

    public static function attemptsUsed(int $learnerId, int $quizId): int
    {
        return (int) Database::value('SELECT COUNT(*) FROM academy_attempts WHERE learner_id = ? AND quiz_id = ? AND submitted_at IS NOT NULL', [$learnerId, $quizId], 0);
    }

    /** @return array<string,mixed>|null the best submitted attempt */
    public static function best(int $learnerId, int $quizId): ?array
    {
        return Database::one('SELECT * FROM academy_attempts WHERE learner_id = ? AND quiz_id = ? AND submitted_at IS NOT NULL ORDER BY score_pct DESC, id DESC LIMIT 1', [$learnerId, $quizId]);
    }

    public static function passedBest(int $learnerId, int $quizId): ?array
    {
        return Database::one('SELECT * FROM academy_attempts WHERE learner_id = ? AND quiz_id = ? AND passed = 1 ORDER BY score_pct DESC, id DESC LIMIT 1', [$learnerId, $quizId]);
    }

    /** @return list<array<string,mixed>> */
    public static function history(int $learnerId, int $quizId): array
    {
        return Database::all('SELECT * FROM academy_attempts WHERE learner_id = ? AND quiz_id = ? AND submitted_at IS NOT NULL ORDER BY id DESC LIMIT 20', [$learnerId, $quizId]);
    }

    /** @return array{ok:bool,reason:?string,left:?int} left = attempts left (null = unlimited) */
    public static function canStart(int $learnerId, array $quiz): array
    {
        if (self::questionCount((int) $quiz['id']) === 0) {
            return ['ok' => false, 'reason' => 'This quiz has no questions yet.', 'left' => null];
        }
        $max = (int) $quiz['max_attempts'];
        if ($max > 0) {
            $used = self::attemptsUsed($learnerId, (int) $quiz['id']);
            if ($used >= $max && self::passedBest($learnerId, (int) $quiz['id']) === null) {
                return ['ok' => false, 'reason' => 'You have used all ' . $max . ' attempts. Please ask your facilitator for help.', 'left' => 0];
            }
            return ['ok' => true, 'reason' => null, 'left' => max(0, $max - $used)];
        }
        return ['ok' => true, 'reason' => null, 'left' => null];
    }

    private static function shuffled(array $a): array
    {
        for ($i = count($a) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$a[$i], $a[$j]] = [$a[$j], $a[$i]];
        }
        return $a;
    }

    /** Start (or resume an unfinished, unexpired) attempt. @return array<string,mixed> the attempt row */
    public static function start(int $learnerId, array $quiz): array
    {
        $open = Database::one('SELECT * FROM academy_attempts WHERE learner_id = ? AND quiz_id = ? AND submitted_at IS NULL ORDER BY id DESC LIMIT 1', [$learnerId, (int) $quiz['id']]);
        if ($open !== null) {
            $left = self::secondsLeft($open, $quiz);
            if ($left === null || $left > -self::GRACE_SECONDS) {
                return $open;
            }
            Database::execute('DELETE FROM academy_attempts WHERE id = ?', [(int) $open['id']]);     // abandoned and out of time: start fresh
        }
        $qs = self::questions((int) $quiz['id']);
        $order = self::shuffled(array_map(static fn(array $q): int => (int) $q['id'], $qs));
        $opts = [];
        foreach ($qs as $q) {
            $opts[(int) $q['id']] = self::shuffled(array_map(static fn(array $o): int => (int) $o['id'], $q['options']));
        }
        $id = Database::insert('academy_attempts', [
            'learner_id' => $learnerId, 'quiz_id' => (int) $quiz['id'], 'course_id' => (int) $quiz['course_id'],
            'layout' => json_encode(['q' => $order, 'o' => $opts]),
        ]);
        return (array) Database::one('SELECT * FROM academy_attempts WHERE id = ?', [$id]);
    }

    public static function attempt(int $id, int $learnerId): ?array
    {
        return Database::one('SELECT * FROM academy_attempts WHERE id = ? AND learner_id = ? LIMIT 1', [$id, $learnerId]);
    }

    /** Seconds left to answer (negative once over), or null without a time limit. */
    public static function secondsLeft(array $attempt, array $quiz): ?int
    {
        $limit = (int) ($quiz['time_limit_min'] ?? 0);
        if ($limit <= 0) {
            return null;
        }
        return (int) (strtotime((string) $attempt['started_at']) + $limit * 60 - time());
    }

    /**
     * The questions of an attempt in the order shown, WITHOUT which answers are right.
     * @return list<array{id:int,prompt:string,type:string,options:list<array{id:int,label:string}>}>
     */
    public static function present(array $attempt, int $quizId): array
    {
        $layout = json_decode((string) $attempt['layout'], true);
        $by = [];
        foreach (self::questions($quizId) as $q) {
            $by[(int) $q['id']] = $q;
        }
        $out = [];
        foreach ((array) ($layout['q'] ?? []) as $qid) {
            if (!isset($by[(int) $qid])) {
                continue;
            }
            $q = $by[(int) $qid];
            $om = [];
            foreach ($q['options'] as $o) {
                $om[(int) $o['id']] = $o;
            }
            $opts = [];
            foreach ((array) ($layout['o'][$qid] ?? array_keys($om)) as $oid) {
                if (isset($om[(int) $oid])) {
                    $opts[] = ['id' => (int) $oid, 'label' => (string) $om[(int) $oid]['label']];
                }
            }
            $out[] = ['id' => (int) $qid, 'prompt' => (string) $q['prompt'], 'type' => (string) $q['type'], 'options' => $opts];
        }
        return $out;
    }

    /**
     * Grade and close an attempt. $posted = question id => list of chosen option ids (anything else is ignored).
     * @param array<int|string,mixed> $posted
     * @return array<string,mixed> the updated attempt row
     */
    public static function submit(array $attempt, array $quiz, array $posted): array
    {
        if ($attempt['submitted_at'] !== null) {
            return $attempt;
        }
        $left = self::secondsLeft($attempt, $quiz);
        $late = $left !== null && $left < -self::GRACE_SECONDS;
        $qs = [];
        foreach (self::questions((int) $quiz['id']) as $q) {
            $qs[(int) $q['id']] = $q;
        }
        $answers = [];
        $correct = 0;
        foreach ($qs as $qid => $q) {
            $valid = array_map(static fn(array $o): int => (int) $o['id'], $q['options']);
            $raw = $posted[$qid] ?? $posted[(string) $qid] ?? [];
            $chosen = array_values(array_unique(array_filter(array_map('intval', (array) $raw), static fn(int $v): bool => in_array($v, $valid, true))));
            if ($q['type'] === 'single' && count($chosen) > 1) {
                $chosen = [$chosen[0]];
            }
            if ($late) {
                $chosen = [];
            }
            sort($chosen);
            $answers[$qid] = $chosen;
            $right = array_values(array_map(static fn(array $o): int => (int) $o['id'], array_filter($q['options'], static fn(array $o): bool => (int) $o['is_correct'] === 1)));
            sort($right);
            if ($chosen !== [] && $chosen === $right) {
                $correct++;
            }
        }
        $total = count($qs);
        $pct = $total > 0 ? round($correct * 100 / $total, 2) : 0.0;
        $passed = $total > 0 && $pct >= (float) $quiz['pass_mark'];
        Database::update('academy_attempts', [
            'submitted_at' => date('Y-m-d H:i:s'), 'answers' => json_encode($answers), 'correct' => $correct, 'total' => $total,
            'score_pct' => $pct, 'passed' => $passed ? 1 : 0,
        ], 'id = ?', [(int) $attempt['id']]);
        return (array) Database::one('SELECT * FROM academy_attempts WHERE id = ?', [(int) $attempt['id']]);
    }

    /**
     * The review of a submitted attempt: every question with the learner choice, the right answers and the explanation.
     * @return list<array<string,mixed>>
     */
    public static function review(array $attempt, int $quizId): array
    {
        $answers = json_decode((string) $attempt['answers'], true);
        $layout = json_decode((string) $attempt['layout'], true);
        $by = [];
        foreach (self::questions($quizId) as $q) {
            $by[(int) $q['id']] = $q;
        }
        $out = [];
        foreach ((array) ($layout['q'] ?? []) as $qid) {
            $q = $by[(int) $qid] ?? null;
            if ($q === null) {
                continue;
            }
            $chosen = array_map('intval', (array) ($answers[$qid] ?? []));
            $om = [];
            foreach ($q['options'] as $o) {
                $om[(int) $o['id']] = $o;
            }
            $opts = [];
            $right = [];
            foreach ((array) ($layout['o'][$qid] ?? array_keys($om)) as $oid) {
                if (!isset($om[(int) $oid])) {
                    continue;
                }
                $o = $om[(int) $oid];
                $isRight = (int) $o['is_correct'] === 1;
                if ($isRight) {
                    $right[] = (int) $oid;
                }
                $opts[] = ['id' => (int) $oid, 'label' => (string) $o['label'], 'correct' => $isRight, 'chosen' => in_array((int) $oid, $chosen, true)];
            }
            sort($right);
            $c2 = $chosen;
            sort($c2);
            $out[] = ['id' => (int) $qid, 'prompt' => (string) $q['prompt'], 'type' => (string) $q['type'], 'explanation' => (string) ($q['explanation'] ?? ''),
                'options' => $opts, 'right' => $c2 !== [] && $c2 === $right, 'answered' => $chosen !== []];
        }
        return $out;
    }
}
