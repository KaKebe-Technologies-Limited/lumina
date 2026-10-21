<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;
use App\Core\Sanitizer;
use App\Core\Slug;

/**
 * Installs the STARTER COURSES (app/config/academy/courses/*.php, format in docs/ACADEMY-CONTENT.md) into the database: modules, lessons, module quizzes and the
 * final exam. A course whose slug already exists is never touched (nothing an administrator changed is overwritten); "reinstall" is allowed only while nobody
 * is enrolled. Run from Admin > LAF Academy (Starter courses) or `php bin/academy-install-content.php`.
 */
final class ContentInstaller
{
    public static function dir(): string
    {
        return APP_PATH . '/config/academy/courses';
    }

    /** @return list<string> absolute file paths in course order */
    public static function files(): array
    {
        $f = glob(self::dir() . '/*.php') ?: [];
        sort($f, SORT_NATURAL);
        return $f;
    }

    /** @return array<string,mixed>|null */
    public static function load(string $file): ?array
    {
        try {
            $c = include $file;
        } catch (\Throwable) {
            return null;
        }
        return is_array($c) && !empty($c['slug']) ? $c : null;
    }

    /** One row per starter course file: slug, title, size and whether it is installed. @return list<array<string,mixed>> */
    public static function status(): array
    {
        $out = [];
        $installed = [];
        foreach (Database::all('SELECT c.id, c.slug, c.status, (SELECT COUNT(*) FROM academy_enrollments e WHERE e.course_id = c.id) AS learners FROM academy_courses c') as $r) {
            $installed[(string) $r['slug']] = $r;
        }
        foreach (self::files() as $file) {
            $c = self::load($file);
            if ($c === null) {
                continue;
            }
            $lessons = 0;
            foreach ((array) ($c['modules'] ?? []) as $m) {
                $lessons += count((array) ($m['lessons'] ?? []));
            }
            $row = $installed[(string) $c['slug']] ?? null;
            $out[] = [
                'slug' => (string) $c['slug'], 'title' => (string) $c['title'], 'category' => (string) ($c['category'] ?? ''), 'level' => (string) ($c['level'] ?? ''),
                'modules' => count((array) ($c['modules'] ?? [])), 'lessons' => $lessons,
                'installed' => $row !== null, 'course_id' => $row !== null ? (int) $row['id'] : null, 'learners' => $row !== null ? (int) $row['learners'] : 0,
                'status' => $row !== null ? (string) $row['status'] : '',
            ];
        }
        return $out;
    }

    /** Install the course with this slug. @return array{ok:bool,message:string,id?:int} */
    public static function install(string $slug, ?int $userId = null, bool $publish = true): array
    {
        $def = null;
        foreach (self::files() as $file) {
            $c = self::load($file);
            if ($c !== null && $c['slug'] === $slug) {
                $def = $c;
                break;
            }
        }
        if ($def === null) {
            return ['ok' => false, 'message' => 'There is no starter course "' . $slug . '".'];
        }
        if (Database::value('SELECT 1 FROM academy_courses WHERE slug = ? LIMIT 1', [$slug]) !== null) {
            return ['ok' => false, 'message' => '"' . $def['title'] . '" is already installed.'];
        }
        $id = Database::transaction(function () use ($def, $userId, $publish): int {
            $pass = max(40, min(100, (int) ($def['pass_mark'] ?? 70)));
            $courseId = Database::insert('academy_courses', [
                'slug' => Slug::make((string) $def['slug']),
                'title' => mb_substr((string) $def['title'], 0, 190),
                'category' => (string) ($def['category'] ?? 'basics'),
                'level' => (string) ($def['level'] ?? 'beginner'),
                'icon' => (string) ($def['icon'] ?? 'book'),
                'summary' => mb_substr((string) ($def['summary'] ?? ''), 0, 255),
                'description' => Sanitizer::html((string) ($def['description'] ?? '')),
                'outcomes' => json_encode(array_values((array) ($def['outcomes'] ?? [])), JSON_UNESCAPED_UNICODE),
                'requirements' => json_encode(array_values((array) ($def['requirements'] ?? [])), JSON_UNESCAPED_UNICODE),
                'resources' => json_encode(array_values((array) ($def['resources'] ?? [])), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'pass_mark' => $pass,
                'certificate_enabled' => 1,
                'status' => $publish ? 'published' : 'draft',
                'is_featured' => 0,
                'sort_order' => (int) ($def['sort'] ?? 100),
                'source' => 'starter',
                'created_by' => $userId,
            ]);
            $mOrder = 10;
            foreach ((array) $def['modules'] as $m) {
                $moduleId = Database::insert('academy_modules', ['course_id' => $courseId, 'title' => mb_substr((string) $m['title'], 0, 190), 'summary' => mb_substr((string) ($m['summary'] ?? ''), 0, 255), 'sort_order' => $mOrder]);
                $mOrder += 10;
                $lOrder = 10;
                foreach ((array) ($m['lessons'] ?? []) as $l) {
                    Database::insert('academy_lessons', [
                        'course_id' => $courseId, 'module_id' => $moduleId, 'title' => mb_substr((string) $l['title'], 0, 190), 'kind' => 'text',
                        'body' => Sanitizer::html((string) ($l['body'] ?? '')), 'minutes' => max(1, min(240, (int) ($l['minutes'] ?? 10))), 'sort_order' => $lOrder, 'is_published' => 1,
                    ]);
                    $lOrder += 10;
                }
                $parsed = QuizScript::parse((string) ($m['quiz'] ?? ''));
                if ($parsed['questions'] !== []) {
                    $quiz = QuizService::ensure($courseId, $moduleId, 'Quiz: ' . mb_substr((string) $m['title'], 0, 150), 60, null, 0);
                    QuizService::replaceQuestions((int) $quiz['id'], $parsed['questions']);
                }
            }
            $final = QuizScript::parse((string) ($def['final_exam'] ?? ''));
            if ($final['questions'] !== []) {
                $quiz = QuizService::ensure($courseId, null, 'Final exam: ' . mb_substr((string) $def['title'], 0, 150), $pass, null, 5);
                QuizService::replaceQuestions((int) $quiz['id'], $final['questions']);
            }
            return $courseId;
        });
        return ['ok' => true, 'message' => '"' . $def['title'] . '" was installed.', 'id' => (int) $id];
    }

    /** @return array{installed:int,skipped:int,messages:list<string>} */
    public static function installAll(?int $userId = null, bool $publish = true): array
    {
        $n = 0;
        $skipped = 0;
        $msgs = [];
        foreach (self::status() as $s) {
            if ($s['installed']) {
                $skipped++;
                continue;
            }
            $r = self::install((string) $s['slug'], $userId, $publish);
            if ($r['ok']) {
                $n++;
            } else {
                $msgs[] = $r['message'];
            }
        }
        return ['installed' => $n, 'skipped' => $skipped, 'messages' => $msgs];
    }

    /**
     * FIRST RUN: when the Academy has no course at all (a fresh install, or the starter courses were never installed) the starter courses and the partner course list are
     * installed once, so visitors never meet an empty catalogue. Done after the response was sent. A marker (setting academy_starter_done) stops it for good, so courses an
     * administrator deletes later do not come back by themselves. Returns true when it installed something.
     */
    public static function autoInstall(): bool
    {
        if ((string) setting('academy_starter_done', '0') === '1') {
            return false;
        }
        try {
            if (!Database::tableExists('academy_courses') || !\App\Core\RateLimit::attempt('academy-auto-install', 1, 600)) {
                return false;
            }
            @set_time_limit(180);
            $did = false;
            if ((int) Database::value('SELECT COUNT(*) FROM academy_courses', [], 0) === 0) {
                $did = self::installAll(null, true)['installed'] > 0;
            }
            if (Database::tableExists('academy_partner_courses') && PartnerCourses::count() === 0) {
                $did = PartnerCourses::installStarter() > 0 || $did;
            }
            \App\Core\Settings::set('academy_starter_done', '1');
            return $did;
        } catch (\Throwable $e) {
            \App\Core\Log::error('Academy first-run install failed: ' . $e->getMessage());
            return false;
        }
    }

    /** Remove and install again, ONLY while nobody is enrolled. @return array{ok:bool,message:string} */
    public static function reinstall(string $slug, ?int $userId = null): array
    {
        $c = Database::one('SELECT id, status, (SELECT COUNT(*) FROM academy_enrollments e WHERE e.course_id = academy_courses.id) AS learners FROM academy_courses WHERE slug = ?', [$slug]);
        if ($c !== null) {
            if ((int) $c['learners'] > 0) {
                return ['ok' => false, 'message' => 'Learners are enrolled in this course, so it cannot be reinstalled. Edit it instead.'];
            }
            Database::execute('DELETE FROM academy_courses WHERE id = ?', [(int) $c['id']]);
        }
        return self::install($slug, $userId, ($c['status'] ?? 'published') === 'published');
    }
}
