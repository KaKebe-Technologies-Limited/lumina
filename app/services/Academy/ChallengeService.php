<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;
use App\Core\ImageOptimizer;
use App\Core\Log;
use App\Core\UploadException;
use App\Core\Uploader;

/**
 * CHALLENGES of the LAF Academy: quiz challenges, innovation challenges and hackathons that an administrator opens for a time window.
 *
 *   draft -> published; then, by the clock: upcoming -> applications (apply, entry approved automatically or by staff) -> live (quiz can be taken / projects
 *   submitted) -> judging (judged challenges: staff score the projects) -> finished (results final).
 *
 * The SYSTEM marks the winners: when a quiz challenge ends, or when the judging deadline of a judged challenge passes (and at least one project has been scored),
 * settle() ranks the eligible entries by score (quiz: percentage, then the fastest finishing time; judged: the average of the judges' weighted totals, then the
 * earliest submission), marks the top entries as winners, and issues certificates (winners: achievement, other qualifying entrants: participation).
 * Winners appear on the "Winners of the month" page with their photo when they agreed to publication. Docs: docs/ACADEMY.md.
 */
final class ChallengeService
{
    public const KINDS = ['quiz' => 'Quiz challenge', 'innovation' => 'Innovation challenge', 'hackathon' => 'Hackathon'];
    public const PHASES = [
        'draft' => 'Draft', 'cancelled' => 'Cancelled', 'upcoming' => 'Opening soon', 'applications' => 'Applications open', 'live' => 'Running now',
        'judging' => 'Results pending', 'finished' => 'Finished',
    ];
    public const ENTRY_STATUS = ['pending' => 'Waiting for approval', 'approved' => 'Approved', 'rejected' => 'Not accepted', 'withdrawn' => 'Withdrawn'];
    /** Seconds allowed after a quiz time limit or the end of the challenge (slow connections). */
    private const GRACE = 60;
    public const PHOTO_DIR = 'academy/winners';
    public const FILE_EXTS = ['pdf', 'doc', 'docx', 'ppt', 'pptx'];
    public const FILE_MAX = 10 * 1024 * 1024;

    /** @return list<array{name:string,weight:int}> */
    public static function defaultCriteria(): array
    {
        return [
            ['name' => 'Innovation and creativity', 'weight' => 30],
            ['name' => 'Impact and usefulness', 'weight' => 30],
            ['name' => 'Feasibility and quality of the work', 'weight' => 25],
            ['name' => 'Presentation and clarity', 'weight' => 15],
        ];
    }

    // ------------------------------------------------------------------------------------------------------------- reading

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM academy_challenges WHERE id = ?', [$id]);
    }

    public static function bySlug(string $slug, bool $includeDraft = false): ?array
    {
        $c = Database::one('SELECT * FROM academy_challenges WHERE slug = ?', [$slug]);
        return $c !== null && ($includeDraft || $c['status'] !== 'draft') ? $c : null;
    }

    /** @return list<array<string,mixed>> published and cancelled-visible challenges for the public list, with entry counts */
    public static function publicList(string $category = '', string $kind = ''): array
    {
        $where = ["c.status = 'published'"];
        $params = [];
        if (isset(CourseService::CATEGORIES[$category])) {
            $where[] = 'c.category = ?';
            $params[] = $category;
        }
        if (isset(self::KINDS[$kind])) {
            $where[] = 'c.kind = ?';
            $params[] = $kind;
        }
        return Database::all(
            "SELECT c.*, (SELECT COUNT(*) FROM academy_challenge_entries e WHERE e.challenge_id = c.id AND e.status IN ('pending','approved')) AS entries
             FROM academy_challenges c WHERE " . implode(' AND ', $where) . ' ORDER BY (c.finalized_at IS NULL) DESC, COALESCE(c.starts_at, c.created_at) DESC LIMIT 100',
            $params
        );
    }

    /** @return list<array{name:string,weight:int}> */
    public static function criteria(array $c): array
    {
        $d = json_decode((string) ($c['criteria'] ?? ''), true);
        $out = [];
        foreach (is_array($d) ? $d : [] as $x) {
            if (is_array($x) && trim((string) ($x['name'] ?? '')) !== '' && (int) ($x['weight'] ?? 0) > 0) {
                $out[] = ['name' => mb_substr(trim((string) $x['name']), 0, 80), 'weight' => (int) $x['weight']];
            }
        }
        return $out !== [] ? $out : self::defaultCriteria();
    }

    /** "Name | weight" lines to criteria; null + message when weights do not add up to 100. @return array{ok:bool,criteria:list<array{name:string,weight:int}>,error:string} */
    public static function parseCriteria(string $text): array
    {
        $out = [];
        foreach (preg_split('/\R/', trim($text)) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (preg_match('/^(.{2,80}?)\s*[|:]\s*(\d{1,3})\s*%?$/u', $line, $m) !== 1) {
                return ['ok' => false, 'criteria' => [], 'error' => 'Write each criterion on its own line as: Name | weight, for example "Impact | 30".'];
            }
            $out[] = ['name' => trim(strip_tags($m[1])), 'weight' => (int) $m[2]];
        }
        if (count($out) < 2 || count($out) > 8) {
            return ['ok' => false, 'criteria' => [], 'error' => 'Give between 2 and 8 criteria.'];
        }
        if (array_sum(array_column($out, 'weight')) !== 100) {
            return ['ok' => false, 'criteria' => [], 'error' => 'The weights must add up to exactly 100 (now ' . array_sum(array_column($out, 'weight')) . ').'];
        }
        return ['ok' => true, 'criteria' => $out, 'error' => ''];
    }

    public static function criteriaText(array $c): string
    {
        return implode("\n", array_map(static fn(array $x): string => $x['name'] . ' | ' . $x['weight'], self::criteria($c)));
    }

    private static function ts(?string $v): ?int
    {
        return $v !== null && $v !== '' ? (strtotime($v) ?: null) : null;
    }

    /** The moment applications close: the stated time, or the end of the challenge. */
    public static function applyDeadline(array $c): ?int
    {
        return self::ts($c['apply_closes_at'] ?? null) ?? self::ts($c['ends_at'] ?? null);
    }

    /** One of self::PHASES. */
    public static function phase(array $c, ?int $now = null): string
    {
        $now ??= time();
        if ($c['status'] === 'draft') {
            return 'draft';
        }
        if ($c['status'] === 'cancelled') {
            return 'cancelled';
        }
        if (!empty($c['finalized_at'])) {
            return 'finished';
        }
        $start = self::ts($c['starts_at'] ?? null);
        $end = self::ts($c['ends_at'] ?? null);
        $opens = self::ts($c['apply_opens_at'] ?? null);
        if ($end !== null && $now >= $end) {
            return 'judging';
        }
        if ($start !== null && $now >= $start) {
            return 'live';
        }
        if ($opens !== null && $now < $opens) {
            return 'upcoming';
        }
        return 'applications';
    }

    public static function canApply(array $c, ?int $now = null): bool
    {
        $now ??= time();
        if ($c['status'] !== 'published' || !empty($c['finalized_at'])) {
            return false;
        }
        $opens = self::ts($c['apply_opens_at'] ?? null);
        $close = self::applyDeadline($c);
        if (($opens !== null && $now < $opens) || ($close !== null && $now >= $close)) {
            return false;
        }
        if ($c['ends_at'] === null && $c['apply_closes_at'] === null && $c['starts_at'] === null) {
            return false;                                                // not scheduled yet
        }
        return $c['max_entries'] === null || self::entryCount((int) $c['id']) < (int) $c['max_entries'];
    }

    public static function entryCount(int $challengeId): int
    {
        return (int) Database::value("SELECT COUNT(*) FROM academy_challenge_entries WHERE challenge_id = ? AND status IN ('pending','approved')", [$challengeId], 0);
    }

    public static function entryOf(int $challengeId, int $learnerId): ?array
    {
        return Database::one('SELECT * FROM academy_challenge_entries WHERE challenge_id = ? AND learner_id = ?', [$challengeId, $learnerId]);
    }

    public static function entry(int $id): ?array
    {
        return Database::one('SELECT * FROM academy_challenge_entries WHERE id = ?', [$id]);
    }

    public static function ordinal(int $n): string
    {
        $s = ['th', 'st', 'nd', 'rd'];
        $v = $n % 100;
        return $n . ($s[($v - 20) % 10] ?? $s[$v] ?? $s[0]);
    }

    // ------------------------------------------------------------------------------------------------------------- applying

    /**
     * Apply. $post: team_name, members, idea_title, idea_summary, consent. $photo: an uploaded picture (optional).
     * @param array<string,mixed> $post
     * @return array{ok:bool,errors:array<string,string>,entry?:array<string,mixed>}
     */
    public static function apply(array $c, array $learner, array $post, ?array $photo): array
    {
        $e = [];
        if (!self::canApply($c)) {
            return ['ok' => false, 'errors' => ['form' => 'Applications for this challenge are not open.']];
        }
        if (self::entryOf((int) $c['id'], (int) $learner['id']) !== null) {
            return ['ok' => false, 'errors' => ['form' => 'You have already applied to this challenge.']];
        }
        $text = static fn(string $k, int $max): string => mb_substr(trim(strip_tags((string) ($post[$k] ?? ''))), 0, $max);
        $team = $text('team_name', 120);
        $members = $text('members', 600);
        $teamMax = max(1, (int) $c['team_max']);
        if ($teamMax > 1) {
            $others = $members === '' ? [] : array_values(array_filter(array_map('trim', preg_split('/[,;\n]+/', $members) ?: [])));
            if (count($others) + 1 > $teamMax) {
                $e['members'] = 'A team can have at most ' . $teamMax . ' people including you.';
            }
            if ($others !== [] && $team === '') {
                $e['team_name'] = 'Give your team a name.';
            }
        } else {
            $team = '';
            $members = '';
        }
        $ideaTitle = $text('idea_title', 190);
        $idea = $text('idea_summary', 2000);
        if ($c['kind'] !== 'quiz') {
            if ($ideaTitle === '') {
                $e['idea_title'] = 'Give your idea or project a short title.';
            }
            if (mb_strlen($idea) < 60) {
                $e['idea_summary'] = 'Describe your idea in a few sentences (at least 60 characters).';
            }
        }
        $consent = !empty($post['consent']);
        $hasPhoto = $photo !== null && (int) ($photo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
        if ($hasPhoto && !$consent) {
            $e['consent'] = 'Tick the box to agree that the picture may be published if you win, or remove the picture.';
        }
        if ($e !== []) {
            return ['ok' => false, 'errors' => $e];
        }
        $id = Database::insert('academy_challenge_entries', [
            'challenge_id' => (int) $c['id'], 'learner_id' => (int) $learner['id'], 'team_name' => $team ?: null, 'members' => $members ?: null,
            'idea_title' => $ideaTitle ?: null, 'idea_summary' => $idea ?: null, 'status' => $c['approval'] === 'auto' ? 'approved' : 'pending',
            'photo_consent_at' => $consent ? date('Y-m-d H:i:s') : null,
        ]);
        $entry = (array) self::entry($id);
        if ($hasPhoto) {
            $r = self::savePhoto($entry, $photo);
            if (!$r['ok']) {
                return ['ok' => true, 'errors' => ['photo' => $r['error']], 'entry' => (array) self::entry($id)];
            }
        }
        return ['ok' => true, 'errors' => [], 'entry' => (array) self::entry($id)];
    }

    /** @return array{ok:bool,error:string} */
    public static function savePhoto(array $entry, array $file): array
    {
        $rel = self::PHOTO_DIR . '/' . bin2hex(random_bytes(16)) . '.jpg';
        $abs = STORAGE_PATH . '/private/' . $rel;
        try {
            $res = ImageOptimizer::portrait((string) $file['tmp_name'], $abs, ['min_side' => 300, 'max_bytes' => 5 * 1024 * 1024]);
        } catch (UploadException $x) {
            return ['ok' => false, 'error' => $x->getMessage()];
        }
        if ($res['ext'] !== 'jpg') {
            $rel2 = self::PHOTO_DIR . '/' . bin2hex(random_bytes(16)) . '.' . $res['ext'];
            if (!@rename($abs, STORAGE_PATH . '/private/' . $rel2)) {
                @unlink($abs);
                return ['ok' => false, 'error' => 'The server could not save the picture.'];
            }
            $rel = $rel2;
        }
        $old = (string) ($entry['photo_path'] ?? '');
        Database::update('academy_challenge_entries', ['photo_path' => $rel], 'id = ?', [(int) $entry['id']]);
        if ($old !== '') {
            Uploader::deletePrivate($old);
        }
        return ['ok' => true, 'error' => ''];
    }

    public static function setConsent(array $entry, bool $consent): void
    {
        Database::update('academy_challenge_entries', ['photo_consent_at' => $consent ? ($entry['photo_consent_at'] ?: date('Y-m-d H:i:s')) : null], 'id = ?', [(int) $entry['id']]);
    }

    public static function withdraw(array $entry): void
    {
        Database::update('academy_challenge_entries', ['status' => 'withdrawn'], 'id = ?', [(int) $entry['id']]);
    }

    public static function setEntryStatus(array $entry, string $status, string $note): void
    {
        Database::update('academy_challenge_entries', ['status' => $status, 'admin_note' => mb_substr($note, 0, 500) ?: null], 'id = ?', [(int) $entry['id']]);
    }

    // ------------------------------------------------------------------------------------------------------------- quiz

    /** @return list<array{prompt:string,type:string,explanation:string,options:list<array{label:string,correct:bool}>}> */
    public static function questions(array $c): array
    {
        $p = QuizScript::parse((string) ($c['quiz_script'] ?? ''));
        return $p['ok'] ? $p['questions'] : [];
    }

    /** Seconds the entrant has for the quiz (the limit, capped by the end of the challenge), or null = no limit. */
    public static function quizDeadline(array $c, array $entry): ?int
    {
        $start = self::ts($entry['quiz_started_at'] ?? null);
        if ($start === null) {
            return null;
        }
        $limit = (int) ($c['quiz_minutes'] ?? 0) > 0 ? $start + (int) $c['quiz_minutes'] * 60 : null;
        $end = self::ts($c['ends_at'] ?? null);
        $d = array_filter([$limit, $end], static fn($x): bool => $x !== null);
        return $d === [] ? null : min($d);
    }

    public static function canTakeQuiz(array $c, array $entry, ?int $now = null): bool
    {
        return $c['kind'] === 'quiz' && $entry['status'] === 'approved' && self::phase($c, $now) === 'live' && $entry['quiz_submitted_at'] === null;
    }

    /** Start (or resume) the attempt: question and answer order are shuffled and remembered. @return array<string,mixed>|null the entry */
    public static function startQuiz(array $c, array $entry): ?array
    {
        if (!self::canTakeQuiz($c, $entry)) {
            return null;
        }
        if ($entry['quiz_started_at'] !== null && $entry['quiz_layout'] !== null) {
            return $entry;
        }
        $qs = self::questions($c);
        if ($qs === []) {
            return null;
        }
        $order = range(0, count($qs) - 1);
        shuffle($order);
        $layout = [];
        foreach ($order as $i) {
            $o = range(0, count($qs[$i]['options']) - 1);
            shuffle($o);
            $layout[] = ['i' => $i, 'o' => $o];
        }
        Database::update('academy_challenge_entries', ['quiz_started_at' => date('Y-m-d H:i:s'), 'quiz_layout' => json_encode($layout)], 'id = ? AND quiz_started_at IS NULL', [(int) $entry['id']]);
        return self::entry((int) $entry['id']);
    }

    /** The questions as shown to this entrant (no answers). @return list<array{n:int,prompt:string,multiple:bool,options:list<array{n:int,label:string}>}> */
    public static function shownQuestions(array $c, array $entry): array
    {
        $qs = self::questions($c);
        $layout = json_decode((string) ($entry['quiz_layout'] ?? ''), true);
        $out = [];
        foreach (is_array($layout) ? $layout : [] as $n => $l) {
            $q = $qs[(int) ($l['i'] ?? -1)] ?? null;
            if ($q === null) {
                continue;
            }
            $opts = [];
            foreach ((array) $l['o'] as $k => $oi) {
                if (isset($q['options'][(int) $oi])) {
                    $opts[] = ['n' => (int) $k, 'label' => $q['options'][(int) $oi]['label']];
                }
            }
            $out[] = ['n' => (int) $n, 'prompt' => $q['prompt'], 'multiple' => $q['type'] === 'multiple', 'options' => $opts];
        }
        return $out;
    }

    /**
     * Grade on the server. Answers after the deadline (plus a short grace) count as blank. @param array<int|string,mixed> $answers position => chosen option positions
     * @return array<string,mixed>|null the updated entry, null when the entry cannot submit
     */
    public static function submitQuiz(array $c, array $entry, array $answers): ?array
    {
        if ($entry['quiz_started_at'] === null || $entry['quiz_submitted_at'] !== null || $entry['status'] !== 'approved' || $c['status'] !== 'published') {
            return null;
        }
        $now = time();
        $deadline = self::quizDeadline($c, $entry);
        $late = $deadline !== null && $now > $deadline + self::GRACE;
        $qs = self::questions($c);
        $layout = json_decode((string) ($entry['quiz_layout'] ?? ''), true);
        $correct = 0;
        $kept = [];
        foreach (is_array($layout) ? $layout : [] as $n => $l) {
            $q = $qs[(int) $l['i']] ?? null;
            if ($q === null) {
                continue;
            }
            $chosen = [];
            if (!$late) {
                foreach ((array) ($answers[$n] ?? []) as $pos) {
                    $oi = $l['o'][(int) $pos] ?? null;
                    if ($oi !== null) {
                        $chosen[] = (int) $oi;
                    }
                }
            }
            $chosen = array_values(array_unique($chosen));
            sort($chosen);
            $right = [];
            foreach ($q['options'] as $oi => $o) {
                if ($o['correct']) {
                    $right[] = (int) $oi;
                }
            }
            if ($chosen === $right) {
                $correct++;
            }
            $kept[$n] = $chosen;
        }
        $total = is_array($layout) ? count($layout) : 0;
        $started = self::ts($entry['quiz_started_at']) ?? $now;
        $end = min($now, $deadline ?? $now);
        $seconds = max(1, $end - $started);
        Database::update('academy_challenge_entries', [
            'quiz_submitted_at' => date('Y-m-d H:i:s', $now), 'quiz_answers' => json_encode($kept), 'quiz_correct' => $correct, 'quiz_total' => $total,
            'quiz_seconds' => $seconds, 'score' => $total > 0 ? round($correct / $total * 100, 2) : 0,
        ], 'id = ? AND quiz_submitted_at IS NULL', [(int) $entry['id']]);
        return self::entry((int) $entry['id']);
    }

    // ------------------------------------------------------------------------------------------------------------- projects (innovation, hackathon)

    public static function canSubmitProject(array $c, array $entry, ?int $now = null): bool
    {
        return $c['kind'] !== 'quiz' && $entry['status'] === 'approved' && self::phase($c, $now) === 'live';
    }

    /**
     * Save (or replace) the project submission. @param array<string,mixed> $post sub_title, sub_summary, sub_url
     * @return array{ok:bool,errors:array<string,string>}
     */
    public static function submitProject(array $c, array $entry, array $post, ?array $file): array
    {
        if (!self::canSubmitProject($c, $entry)) {
            return ['ok' => false, 'errors' => ['form' => 'Submissions are not open for this challenge right now.']];
        }
        $e = [];
        $title = mb_substr(trim(strip_tags((string) ($post['sub_title'] ?? ''))), 0, 190);
        $summary = mb_substr(trim(strip_tags((string) ($post['sub_summary'] ?? ''))), 0, 4000);
        $url = trim((string) ($post['sub_url'] ?? ''));
        if ($title === '') {
            $e['sub_title'] = 'Give your project a title.';
        }
        if (mb_strlen($summary) < 100) {
            $e['sub_summary'] = 'Explain the problem, your solution and who benefits (at least 100 characters).';
        }
        if ($url !== '' && preg_match('~^https://[^\s"<>]{4,470}$~', $url) !== 1) {
            $e['sub_url'] = 'Enter the full link starting with https://';
        }
        $hasFile = $file !== null && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
        if ($url === '' && !$hasFile && empty($entry['sub_file_path']) && empty($entry['sub_url'])) {
            $e['sub_url'] = 'Add a link to your project (demo, repository, video or folder) or upload a file.';
        }
        $data = ['sub_title' => $title, 'sub_summary' => $summary, 'sub_url' => $url !== '' ? $url : null, 'submitted_at' => date('Y-m-d H:i:s')];
        if ($e === [] && $hasFile) {
            try {
                $up = Uploader::privateFile($file, 'academy', self::FILE_EXTS, self::FILE_MAX);
                if (!empty($entry['sub_file_path'])) {
                    Uploader::deletePrivate((string) $entry['sub_file_path']);
                }
                $data['sub_file_path'] = $up['path'];
                $data['sub_file_name'] = $up['name'];
            } catch (UploadException $x) {
                $e['sub_file'] = $x->getMessage();
            }
        }
        if ($e !== []) {
            return ['ok' => false, 'errors' => $e];
        }
        Database::update('academy_challenge_entries', $data, 'id = ?', [(int) $entry['id']]);
        return ['ok' => true, 'errors' => []];
    }

    // ------------------------------------------------------------------------------------------------------------- judging

    /** @return list<array<string,mixed>> the projects that can be scored, each with the judge's own scores and the number of judgments */
    public static function judgeable(array $c, int $judgeId): array
    {
        $rows = Database::all(
            "SELECT e.*, l.name AS learner_name, (SELECT COUNT(*) FROM academy_challenge_judgments j WHERE j.entry_id = e.id) AS judgments,
                    (SELECT AVG(j2.total) FROM academy_challenge_judgments j2 WHERE j2.entry_id = e.id) AS average
             FROM academy_challenge_entries e JOIN academy_learners l ON l.id = e.learner_id
             WHERE e.challenge_id = ? AND e.status = 'approved' AND e.submitted_at IS NOT NULL ORDER BY e.submitted_at ASC",
            [(int) $c['id']]
        );
        foreach ($rows as &$r) {
            $mine = Database::one('SELECT * FROM academy_challenge_judgments WHERE entry_id = ? AND judge_id = ?', [(int) $r['id'], $judgeId]);
            $r['mine'] = $mine;
        }
        unset($r);
        return $rows;
    }

    /** @param list<int|string> $points 0-10 per criterion, in order */
    public static function judge(array $c, array $entry, int $judgeId, array $points, string $comment): bool
    {
        if ($c['kind'] === 'quiz' || $entry['status'] !== 'approved' || $entry['submitted_at'] === null || !empty($c['finalized_at'])) {
            return false;
        }
        $crit = self::criteria($c);
        $scores = [];
        $total = 0.0;
        foreach ($crit as $i => $x) {
            $p = max(0, min(10, (int) ($points[$i] ?? 0)));
            $scores[] = $p;
            $total += $p / 10 * $x['weight'];
        }
        $row = ['scores' => json_encode($scores), 'total' => round($total, 2), 'comment' => mb_substr(trim(strip_tags($comment)), 0, 500) ?: null];
        $exists = Database::one('SELECT id FROM academy_challenge_judgments WHERE entry_id = ? AND judge_id = ?', [(int) $entry['id'], $judgeId]);
        if ($exists !== null) {
            Database::update('academy_challenge_judgments', $row, 'id = ?', [(int) $exists['id']]);
        } else {
            Database::insert('academy_challenge_judgments', $row + ['entry_id' => (int) $entry['id'], 'judge_id' => $judgeId]);
        }
        return true;
    }

    // ------------------------------------------------------------------------------------------------------------- results

    /** Rank the entries and mark the winners now. Safe to call twice: a finished challenge is left alone. @return array{ok:bool,message:string,winners:int,certificates:int} */
    public static function finalize(array $c, bool $force = false): array
    {
        $id = (int) $c['id'];
        $result = Database::transaction(function () use ($id, $force): array {
            $c = Database::one('SELECT * FROM academy_challenges WHERE id = ? FOR UPDATE', [$id]);
            if ($c === null || $c['status'] !== 'published') {
                return ['ok' => false, 'message' => 'Only a published challenge can be finalised.', 'winners' => 0, 'certificates' => 0, 'ids' => []];
            }
            if (!empty($c['finalized_at'])) {
                return ['ok' => false, 'message' => 'The results are already final.', 'winners' => 0, 'certificates' => 0, 'ids' => []];
            }
            $quiz = $c['scoring'] === 'quiz';
            if ($quiz) {
                $rows = Database::all("SELECT * FROM academy_challenge_entries WHERE challenge_id = ? AND status = 'approved' AND quiz_submitted_at IS NOT NULL", [$id]);
            } else {
                $rows = Database::all(
                    "SELECT e.*, (SELECT AVG(j.total) FROM academy_challenge_judgments j WHERE j.entry_id = e.id) AS avg_total FROM academy_challenge_entries e
                     WHERE e.challenge_id = ? AND e.status = 'approved' AND e.submitted_at IS NOT NULL", [$id]
                );
                $rows = array_values(array_filter($rows, static fn(array $r): bool => $r['avg_total'] !== null));
                foreach ($rows as &$r) {
                    $r['score'] = round((float) $r['avg_total'], 2);
                }
                unset($r);
            }
            if ($rows === [] && !$force) {
                return ['ok' => false, 'message' => $quiz ? 'Nobody has finished the quiz yet.' : 'No project has been scored yet.', 'winners' => 0, 'certificates' => 0, 'ids' => []];
            }
            usort($rows, static function (array $a, array $b) use ($quiz): int {
                $c = (float) $b['score'] <=> (float) $a['score'];
                if ($c !== 0) {
                    return $c;
                }
                $c = $quiz ? ((int) $a['quiz_seconds'] <=> (int) $b['quiz_seconds']) : strcmp((string) $a['submitted_at'], (string) $b['submitted_at']);
                return $c !== 0 ? $c : ((int) $a['id'] <=> (int) $b['id']);
            });
            Database::execute('UPDATE academy_challenge_entries SET rank_no = NULL, is_winner = 0, award = NULL WHERE challenge_id = ?', [$id]);
            $slots = max(1, (int) $c['winners_count']);
            $minScore = max(1, (int) $c['pass_score']);
            $winners = 0;
            $rank = 0;
            $ids = [];
            foreach ($rows as $r) {
                $rank++;
                $win = $winners < $slots && (float) $r['score'] >= $minScore;
                $winners += $win ? 1 : 0;
                Database::update('academy_challenge_entries', [
                    'score' => $r['score'], 'rank_no' => $rank, 'is_winner' => $win ? 1 : 0, 'award' => $win ? self::ordinal($rank) . ' place' : null,
                ], 'id = ?', [(int) $r['id']]);
                $ids[] = (int) $r['id'];
            }
            Database::update('academy_challenges', ['finalized_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
            return ['ok' => true, 'message' => $winners . ' winner' . ($winners === 1 ? '' : 's') . ' marked from ' . count($rows) . ' scored entr' . (count($rows) === 1 ? 'y' : 'ies') . '.', 'winners' => $winners, 'certificates' => 0, 'ids' => $ids];
        });
        if (!$result['ok']) {
            return ['ok' => false, 'message' => $result['message'], 'winners' => 0, 'certificates' => 0];
        }
        $c = (array) self::find($id);
        $certs = 0;
        foreach ((array) $result['ids'] as $eid) {
            try {
                $e = (array) self::entry((int) $eid);
                $learner = Database::one('SELECT * FROM academy_learners WHERE id = ?', [(int) $e['learner_id']]);
                if ($learner === null) {
                    continue;
                }
                $winner = (int) $e['is_winner'] === 1;
                $qualifies = $winner || ((int) $c['certify_participants'] === 1 && (float) $e['score'] >= (int) $c['pass_score']);
                $cert = null;
                if ($qualifies) {
                    $cert = CertificateService::issueForChallenge($learner, $c, $winner ? 'Winner, ' . $e['award'] : 'Participant', (float) $e['score']);
                    Database::update('academy_challenge_entries', ['certificate_id' => (int) $cert['id']], 'id = ?', [(int) $e['id']]);
                    $certs++;
                }
                if ($winner || $cert !== null) {
                    AcademyMail::challengeResult($c, $e, $learner, $cert);
                }
            } catch (\Throwable $x) {
                Log::error('Challenge result processing failed for entry ' . (int) $eid . ': ' . $x->getMessage());
            }
        }
        return ['ok' => true, 'message' => $result['message'], 'winners' => (int) $result['winners'], 'certificates' => $certs];
    }

    /** Undo the results (certificates of this challenge are withdrawn) so the scores can be corrected and the results made again. */
    public static function reopen(array $c): void
    {
        $id = (int) $c['id'];
        Database::execute("UPDATE academy_certificates SET revoked_at = NOW(), revoked_reason = 'Results reopened' WHERE challenge_id = ? AND revoked_at IS NULL", [$id]);
        Database::execute('UPDATE academy_challenge_entries SET rank_no = NULL, is_winner = 0, award = NULL, certificate_id = NULL WHERE challenge_id = ?', [$id]);
        Database::update('academy_challenges', ['finalized_at' => null], 'id = ?', [$id]);
    }

    /**
     * Finalise every challenge whose time has come (called by the public pages, the admin overview and the cron script). Cheap when nothing is due.
     * @return int challenges finalised
     */
    public static function settle(): int
    {
        try {
            $due = Database::all(
                "SELECT * FROM academy_challenges WHERE status = 'published' AND finalized_at IS NULL AND ends_at IS NOT NULL AND ends_at <= NOW()
                 AND (scoring = 'quiz' OR COALESCE(judging_ends_at, ends_at) <= NOW()) LIMIT 5"
            );
        } catch (\Throwable) {
            return 0;
        }
        $n = 0;
        foreach ($due as $c) {
            $r = self::finalize($c);
            $n += $r['ok'] ? 1 : 0;
        }
        return $n;
    }

    // ------------------------------------------------------------------------------------------------------------- public results

    /** @return list<array<string,mixed>> ranked entries of a finished challenge (or the top of an unfinished quiz is never shown) */
    public static function leaderboard(array $c, int $limit = 20): array
    {
        if (empty($c['finalized_at'])) {
            return [];
        }
        $rows = Database::all(
            'SELECT e.*, l.name AS learner_name FROM academy_challenge_entries e JOIN academy_learners l ON l.id = e.learner_id
             WHERE e.challenge_id = ? AND e.rank_no IS NOT NULL ORDER BY e.rank_no ASC LIMIT ' . (int) $limit,
            [(int) $c['id']]
        );
        foreach ($rows as &$r) {
            $r['display_name'] = self::displayName($r);
        }
        unset($r);
        return $rows;
    }

    /** The public name of an entrant: the full name when they agreed to publication, else the first name and an initial. */
    public static function displayName(array $e): string
    {
        $name = trim((string) ($e['team_name'] ?? '') ?: (string) ($e['learner_name'] ?? 'Learner'));
        if (!empty($e['photo_consent_at']) || !empty($e['team_name'])) {
            return $name;
        }
        $parts = preg_split('/\s+/u', $name) ?: [$name];
        return count($parts) > 1 ? $parts[0] . ' ' . mb_substr((string) end($parts), 0, 1) . '.' : $parts[0];
    }

    /** True when the entry's picture may be shown publicly. */
    public static function photoPublic(array $e): bool
    {
        return !empty($e['photo_path']) && !empty($e['photo_consent_at']) && (int) ($e['publish_photo'] ?? 1) === 1 && (int) ($e['is_winner'] ?? 0) === 1;
    }

    /**
     * Winners grouped by the month the results were made, newest first.
     * @return list<array{month:string,label:string,winners:list<array<string,mixed>>}>
     */
    public static function winnersByMonth(int $months = 12): array
    {
        $rows = Database::all(
            "SELECT e.*, l.name AS learner_name, c.title AS challenge_title, c.slug AS challenge_slug, c.kind AS challenge_kind, c.category AS challenge_category,
                    c.finalized_at, DATE_FORMAT(c.finalized_at, '%Y-%m') AS ym, cert.code AS cert_code
             FROM academy_challenge_entries e JOIN academy_challenges c ON c.id = e.challenge_id JOIN academy_learners l ON l.id = e.learner_id
             LEFT JOIN academy_certificates cert ON cert.id = e.certificate_id AND cert.revoked_at IS NULL
             WHERE e.is_winner = 1 AND c.status = 'published' AND c.finalized_at IS NOT NULL
             ORDER BY c.finalized_at DESC, e.rank_no ASC LIMIT 300"
        );
        $by = [];
        foreach ($rows as $r) {
            $r['display_name'] = self::displayName($r);
            $r['photo_ok'] = self::photoPublic($r);
            $by[(string) $r['ym']][] = $r;
        }
        $out = [];
        foreach (array_slice($by, 0, max(1, $months), true) as $ym => $list) {
            $out[] = ['month' => (string) $ym, 'label' => date('F Y', (int) strtotime($ym . '-01')), 'winners' => $list];
        }
        return $out;
    }

    public static function statusLabel(array $c): string
    {
        return self::PHASES[self::phase($c)] ?? 'Draft';
    }
}
