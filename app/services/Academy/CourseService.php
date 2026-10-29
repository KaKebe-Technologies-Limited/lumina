<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;

/**
 * Read and write the LAF Academy catalogue and a learner progress through it: courses, the curriculum tree (modules, lessons, quizzes), enrolment,
 * lesson completion, and the moment a course is completed (every lesson done + final exam passed -> certificate). Admin screens use the same tree
 * with $publishedOnly = false.
 */
final class CourseService
{
    /** key => [label, icon] */
    public const CATEGORIES = [
        'basics'    => ['Computer basics', 'laptop'],
        'office'    => ['Office and productivity', 'file-text'],
        'internet'  => ['Internet and email', 'mail'],
        'marketing' => ['Digital marketing', 'megaphone'],
        'design'    => ['Design and social media', 'star'],
        'security'  => ['Online safety', 'shield'],
        'careers'   => ['Digital careers', 'briefcase'],
        'web'       => ['Web development', 'code'],
        'coding'    => ['Programming', 'code'],
        'ai'        => ['Artificial intelligence', 'star'],
        'hardware'  => ['Maintenance and networks', 'settings'],
        'data'      => ['Data and databases', 'layers'],
        'cloud'     => ['Cloud and systems', 'globe'],
        'business'  => ['Business and e-commerce', 'briefcase'],
    ];
    public const LEVELS = ['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'];

    /** @return array<string,string> */
    public static function categoryLabels(): array
    {
        return array_map(static fn(array $c): string => $c[0], self::CATEGORIES);
    }

    public static function categoryLabel(?string $key): string
    {
        return self::CATEGORIES[(string) $key][0] ?? 'Course';
    }

    public static function icon(array $course): string
    {
        $icon = (string) ($course['icon'] ?? '');
        if ($icon !== '' && function_exists('safe_icon')) {
            return safe_icon($icon, 'book');
        }
        return $icon !== '' ? $icon : (self::CATEGORIES[(string) ($course['category'] ?? '')][1] ?? 'book');
    }

    // ------------------------------------------------------------------------------------------------------------- catalogue

    /**
     * Published courses with their size: modules, lessons, minutes, learners.
     * @param array{q?:string,category?:string,level?:string} $f
     * @return list<array<string,mixed>>
     */
    public static function published(array $f = []): array
    {
        $where = ["c.status = 'published'"];
        $params = [];
        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_substr($q, 0, 80)) . '%';
            $where[] = '(c.title LIKE ? OR c.summary LIKE ? OR c.description LIKE ?)';
            array_push($params, $like, $like, $like);
        }
        if (isset(self::CATEGORIES[(string) ($f['category'] ?? '')])) {
            $where[] = 'c.category = ?';
            $params[] = $f['category'];
        }
        if (isset(self::LEVELS[(string) ($f['level'] ?? '')])) {
            $where[] = 'c.level = ?';
            $params[] = $f['level'];
        }
        $rows = Database::all(
            'SELECT c.*, '
            . '(SELECT COUNT(*) FROM academy_modules m WHERE m.course_id = c.id) AS modules, '
            . '(SELECT COUNT(*) FROM academy_lessons l WHERE l.course_id = c.id AND l.is_published = 1) AS lessons, '
            . '(SELECT COALESCE(SUM(l2.minutes), 0) FROM academy_lessons l2 WHERE l2.course_id = c.id AND l2.is_published = 1) AS minutes, '
            . '(SELECT COUNT(*) FROM academy_enrollments e WHERE e.course_id = c.id) AS learners '
            . 'FROM academy_courses c WHERE ' . implode(' AND ', $where) . ' ORDER BY c.is_featured DESC, c.sort_order ASC, c.id ASC',
            $params
        );
        return self::withHighlights($rows);
    }

    /** Add "highlights" to course rows: the titles of the first three modules (what the learner will learn), from one query. @param list<array<string,mixed>> $rows @return list<array<string,mixed>> */
    public static function withHighlights(array $rows): array
    {
        $ids = array_map(static fn(array $r): int => (int) $r['id'], $rows);
        $by = [];
        if ($ids !== []) {
            foreach (Database::all('SELECT course_id, title FROM academy_modules WHERE course_id IN (' . implode(',', $ids) . ') ORDER BY course_id, sort_order, id') as $m) {
                if (count($by[(int) $m['course_id']] ?? []) < 3) {
                    $by[(int) $m['course_id']][] = trim((string) preg_replace('/^\s*(module\s*)?\d+\s*[.:)\-]\s*/i', '', (string) $m['title']));
                }
            }
        }
        foreach ($rows as &$r) {
            $r['highlights'] = $by[(int) $r['id']] ?? [];
        }
        unset($r);
        return $rows;
    }

    /** @return array<string,int> category => number of published courses */
    public static function categoryCounts(): array
    {
        $out = [];
        foreach (Database::all("SELECT category, COUNT(*) n FROM academy_courses WHERE status = 'published' GROUP BY category") as $r) {
            $out[(string) $r['category']] = (int) $r['n'];
        }
        return $out;
    }

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM academy_courses WHERE id = ? LIMIT 1', [$id]);
    }

    public static function bySlug(string $slug, bool $includeUnpublished = false): ?array
    {
        return Database::one('SELECT * FROM academy_courses WHERE slug = ?' . ($includeUnpublished ? '' : " AND status = 'published'") . ' LIMIT 1', [$slug]);
    }

    /** @return list<string> */
    public static function jsonList(mixed $json): array
    {
        $a = json_decode((string) $json, true);
        return is_array($a) ? array_values(array_filter(array_map(static fn($v): string => is_string($v) ? trim($v) : '', $a), static fn(string $v): bool => $v !== '')) : [];
    }

    /** @return list<array{title:string,url:string}> */
    public static function resources(mixed $json): array
    {
        $a = json_decode((string) $json, true);
        $out = [];
        foreach (is_array($a) ? $a : [] as $r) {
            if (is_array($r) && !empty($r['title']) && isset($r['url']) && preg_match('~^https?://~i', (string) $r['url']) === 1) {
                $out[] = ['title' => (string) $r['title'], 'url' => (string) $r['url']];
            }
        }
        return $out;
    }

    // ------------------------------------------------------------------------------------------------------------- curriculum

    /**
     * The curriculum: modules in order, each with its lessons and (practice) quiz, plus the final exam.
     * @return array{modules:list<array<string,mixed>>,exam:?array<string,mixed>,lesson_ids:list<int>,lessons:int,minutes:int,quizzes:int}
     */
    public static function tree(int $courseId, bool $publishedOnly = true): array
    {
        $modules = Database::all('SELECT * FROM academy_modules WHERE course_id = ? ORDER BY sort_order ASC, id ASC', [$courseId]);
        $lessons = Database::all(
            'SELECT id, course_id, module_id, title, kind, minutes, sort_order, is_published, (file_path IS NOT NULL AND file_path <> \'\') AS has_file FROM academy_lessons WHERE course_id = ?'
            . ($publishedOnly ? ' AND is_published = 1' : '') . ' ORDER BY sort_order ASC, id ASC',
            [$courseId]
        );
        $quizzes = Database::all(
            'SELECT q.*, (SELECT COUNT(*) FROM academy_questions x WHERE x.quiz_id = q.id) AS questions FROM academy_quizzes q WHERE q.course_id = ?',
            [$courseId]
        );
        $byModule = [];
        foreach ($lessons as $l) {
            $byModule[(int) $l['module_id']][] = $l;
        }
        $quizByModule = [];
        $exam = null;
        foreach ($quizzes as $q) {
            if ($publishedOnly && (int) $q['questions'] === 0) {
                continue;
            }
            if ($q['module_id'] === null) {
                $exam = $q;
            } else {
                $quizByModule[(int) $q['module_id']] = $q;
            }
        }
        $ids = [];
        $minutes = 0;
        $quizCount = $exam !== null ? 1 : 0;
        foreach ($modules as &$m) {
            $m['lessons'] = $byModule[(int) $m['id']] ?? [];
            $m['quiz'] = $quizByModule[(int) $m['id']] ?? null;
            $quizCount += $m['quiz'] !== null ? 1 : 0;
            foreach ($m['lessons'] as $l) {
                $ids[] = (int) $l['id'];
                $minutes += (int) $l['minutes'];
            }
        }
        unset($m);
        return ['modules' => $modules, 'exam' => $exam, 'lesson_ids' => $ids, 'lessons' => count($ids), 'minutes' => $minutes, 'quizzes' => $quizCount];
    }

    public static function lesson(int $id): ?array
    {
        return Database::one('SELECT * FROM academy_lessons WHERE id = ? LIMIT 1', [$id]);
    }

    // ------------------------------------------------------------------------------------------------------------- learner progress

    public static function enrollment(int $learnerId, int $courseId): ?array
    {
        return Database::one('SELECT * FROM academy_enrollments WHERE learner_id = ? AND course_id = ? LIMIT 1', [$learnerId, $courseId]);
    }

    public static function enroll(int $learnerId, int $courseId): void
    {
        Database::execute('INSERT IGNORE INTO academy_enrollments (learner_id, course_id) VALUES (?, ?)', [$learnerId, $courseId]);
    }

    public static function unenroll(int $learnerId, int $courseId): void
    {
        Database::execute('DELETE FROM academy_progress WHERE learner_id = ? AND course_id = ?', [$learnerId, $courseId]);
        Database::execute('DELETE FROM academy_attempts WHERE learner_id = ? AND course_id = ?', [$learnerId, $courseId]);
        Database::execute('DELETE FROM academy_enrollments WHERE learner_id = ? AND course_id = ?', [$learnerId, $courseId]);
    }

    /** @return list<int> ids of the lessons the learner has finished (in this course) */
    public static function doneIds(int $learnerId, int $courseId): array
    {
        return array_map('intval', array_column(Database::all('SELECT lesson_id FROM academy_progress WHERE learner_id = ? AND course_id = ?', [$learnerId, $courseId]), 'lesson_id'));
    }

    /**
     * @param array{lesson_ids:list<int>} $tree
     * @return array{done:int,total:int,pct:int,done_ids:list<int>}
     */
    public static function progress(int $learnerId, int $courseId, array $tree): array
    {
        $done = array_values(array_intersect(self::doneIds($learnerId, $courseId), $tree['lesson_ids']));
        $total = count($tree['lesson_ids']);
        return ['done' => count($done), 'total' => $total, 'pct' => $total > 0 ? (int) floor(count($done) * 100 / $total) : 0, 'done_ids' => $done];
    }

    public static function completeLesson(int $learnerId, array $lesson): void
    {
        Database::execute(
            'INSERT IGNORE INTO academy_progress (learner_id, lesson_id, course_id) VALUES (?, ?, ?)',
            [$learnerId, (int) $lesson['id'], (int) $lesson['course_id']]
        );
        self::touch($learnerId, (int) $lesson['course_id'], (int) $lesson['id']);
    }

    public static function uncompleteLesson(int $learnerId, array $lesson): void
    {
        Database::execute('DELETE FROM academy_progress WHERE learner_id = ? AND lesson_id = ?', [$learnerId, (int) $lesson['id']]);
        Database::execute('UPDATE academy_enrollments SET completed_at = NULL WHERE learner_id = ? AND course_id = ? AND completed_at IS NOT NULL AND NOT EXISTS (SELECT 1 FROM academy_certificates c WHERE c.learner_id = ? AND c.course_id = ? AND c.revoked_at IS NULL)',
            [$learnerId, (int) $lesson['course_id'], $learnerId, (int) $lesson['course_id']]);
    }

    public static function touch(int $learnerId, int $courseId, int $lessonId): void
    {
        Database::execute('UPDATE academy_enrollments SET last_lesson_id = ? WHERE learner_id = ? AND course_id = ?', [$lessonId, $learnerId, $courseId]);
    }

    /** The lesson after $currentId in course order (null at the end). @param array{lesson_ids:list<int>} $tree */
    public static function nextLessonId(array $tree, int $currentId): ?int
    {
        $i = array_search($currentId, $tree['lesson_ids'], true);
        return $i === false ? null : ($tree['lesson_ids'][$i + 1] ?? null);
    }

    public static function previousLessonId(array $tree, int $currentId): ?int
    {
        $i = array_search($currentId, $tree['lesson_ids'], true);
        return ($i === false || $i === 0) ? null : $tree['lesson_ids'][$i - 1];
    }

    /** Where "Continue" leads: the last lesson opened if unfinished, else the first unfinished lesson, else the first lesson. @param array{lesson_ids:list<int>} $tree */
    public static function resumeLessonId(array $tree, array $doneIds, ?int $lastId): ?int
    {
        if ($lastId !== null && in_array($lastId, $tree['lesson_ids'], true) && !in_array($lastId, $doneIds, true)) {
            return $lastId;
        }
        foreach ($tree['lesson_ids'] as $id) {
            if (!in_array($id, $doneIds, true)) {
                return $id;
            }
        }
        return $tree['lesson_ids'][0] ?? null;
    }

    public static function examUnlocked(array $progress): bool
    {
        if ((string) setting('academy_exam_requires_lessons', '1') !== '1') {
            return true;
        }
        return $progress['total'] === 0 || $progress['done'] >= $progress['total'];
    }

    /**
     * Has the learner finished the course (every lesson done and the final exam, if there is one, passed)? Marks the enrolment as completed the first time.
     * @return array{course:array<string,mixed>,tree:array<string,mixed>,score:?float}|null null while the course is not finished
     */
    public static function completion(int $learnerId, int $courseId): ?array
    {
        $course = self::find($courseId);
        $enr = self::enrollment($learnerId, $courseId);
        if ($course === null || $enr === null) {
            return null;
        }
        $tree = self::tree($courseId);
        $p = self::progress($learnerId, $courseId, $tree);
        if ($p['total'] === 0 || $p['done'] < $p['total']) {
            return null;
        }
        $score = null;
        if ($tree['exam'] !== null) {
            $best = QuizService::passedBest($learnerId, (int) $tree['exam']['id']);
            if ($best === null) {
                return null;
            }
            $score = (float) $best['score_pct'];
        }
        if ($enr['completed_at'] === null) {
            Database::update('academy_enrollments', ['completed_at' => date('Y-m-d H:i:s')], 'id = ?', [(int) $enr['id']]);
        }
        return ['course' => $course, 'tree' => $tree, 'score' => $score];
    }

    /**
     * Called after a lesson is finished and after an exam attempt. When the course is finished it is marked completed, and then:
     *  - certificates need approval (the default): nothing is issued; the learner REQUESTS the certificate (CertificateService::request) and the Academy team approves it.
     *    This returns ['requestable' => true, 'course_id' => ...] so the page can tell the learner.
     *  - approval is switched off: the certificate is issued and e-mailed at once and its row is returned.
     * Returns null when nothing new happened (not finished yet, certificates off for the course, or one exists already).
     * @return array<string,mixed>|null
     */
    public static function checkCompletion(int $learnerId, int $courseId): ?array
    {
        $done = self::completion($learnerId, $courseId);
        if ($done === null || (int) $done['course']['certificate_enabled'] !== 1) {
            return null;
        }
        if (CertificateService::existingFor($learnerId, $courseId) !== null) {      // approved or waiting for approval: never twice
            return null;
        }
        $learner = Database::one('SELECT * FROM academy_learners WHERE id = ?', [$learnerId]);
        if ($learner === null) {
            return null;
        }
        if (CertificatePayments::required($learnerId, $done['course'])) {
            // the certificate fee comes first (docs/PESAPAL.md): nothing is filed or issued until Pesapal confirmed the payment, whatever the approval setting says
            return ['requestable' => true, 'payment_required' => true, 'course_id' => $courseId];
        }
        if (CertificateService::approvalRequired()) {
            // the certificate is filed for the Academy team straight away (they are told); the learner is told it waits for approval and signing
            $res = CertificateService::request($learner, $done['course']);
            if ($res['ok'] && $res['waiting']) {
                AcademyMail::certificateWaiting($learner, $done['course']);
                return ['requestable' => false, 'waiting' => true, 'course_id' => $courseId];
            }
            return ['requestable' => true, 'course_id' => $courseId];        // could not be filed just now: the learner can ask on the page
        }
        $cert = CertificateService::issue($learner, $done['course'], $done['score'], $done['tree']['minutes']);
        if (CertificateService::isApproved($cert)) {
            CertificateService::email($cert);        // approval is switched off: it is ready at once
        }
        return $cert;
    }

    // ------------------------------------------------------------------------------------------------------------- learner dashboard

    /** The learner courses with progress, newest activity first. @return list<array<string,mixed>> */
    public static function dashboard(int $learnerId): array
    {
        $rows = Database::all(
            'SELECT e.*, c.slug, c.title, c.icon, c.category, c.level, c.status, c.summary, c.certificate_enabled FROM academy_enrollments e JOIN academy_courses c ON c.id = e.course_id WHERE e.learner_id = ? ORDER BY e.enrolled_at DESC',
            [$learnerId]
        );
        foreach ($rows as &$r) {
            $tree = self::tree((int) $r['course_id']);
            $r['tree'] = $tree;
            $r['progress'] = self::progress($learnerId, (int) $r['course_id'], $tree);
            $r['resume_id'] = self::resumeLessonId($tree, $r['progress']['done_ids'], $r['last_lesson_id'] !== null ? (int) $r['last_lesson_id'] : null);
            $r['certificate'] = CertificateService::activeFor($learnerId, (int) $r['course_id']);
            $r['cert_pending'] = $r['certificate'] === null ? CertificateService::pendingFor($learnerId, (int) $r['course_id']) : null;
            $r['cert_requestable'] = $r['certificate'] === null && $r['cert_pending'] === null && $r['completed_at'] !== null && (int) $r['certificate_enabled'] === 1
                && CertificateService::existingFor($learnerId, (int) $r['course_id']) === null;
        }
        unset($r);
        return $rows;
    }

    // ------------------------------------------------------------------------------------------------------------- YouTube

    /** The embeddable address of a YouTube link (watch, youtu.be, shorts, embed), or null. */
    public static function youtubeEmbed(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }
        if (preg_match('~^https?://(?:www\.|m\.)?(?:youtube\.com/(?:watch\?(?:[^#]*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})(?:[?&#].*)?$~', $url, $m) === 1) {
            return 'https://www.youtube-nocookie.com/embed/' . $m[1];
        }
        return null;
    }
}
