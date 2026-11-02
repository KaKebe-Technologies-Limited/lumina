<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;

/**
 * FEATURED STUDENTS of the LAF Academy. An administrator features a learner (Admin > Academy > Learners > the learner > "Feature on the website") after the learner agreed;
 * the public Academy page then shows a card with the first name and the first letter of the surname, the country, the picture (only when the learner also agreed to show
 * the picture), a short highlight written by the administrator and the courses completed. Nothing else about the learner is ever shown (no e-mail, phone, reg. number).
 * Needs database/updates/2026-11-academy-featured-learners.sql; without it nothing is featured and the section stays hidden.
 */
final class FeaturedLearners
{
    public const NOTE_MAX = 400;

    public static function ready(): bool
    {
        static $ok = null;
        if ($ok === null) {
            try {
                $ok = (int) Database::value("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name IN ('featured_at', 'featured_note', 'featured_by')", [], 0) === 3;
            } catch (\Throwable) {
                $ok = false;
            }
        }
        return $ok;
    }

    public static function clean(string $note): string
    {
        $note = trim((string) preg_replace('/\s+/u', ' ', strip_tags($note)));
        return mb_substr($note, 0, self::NOTE_MAX);
    }

    /** Feature an approved, active learner. @return bool false when the learner cannot be featured (not approved / switched off / columns missing) */
    public static function feature(int $learnerId, string $note, ?int $adminId): bool
    {
        if (!self::ready()) {
            return false;
        }
        $n = Database::execute(
            'UPDATE academy_learners SET featured_at = ?, featured_note = ?, featured_by = ? WHERE id = ? AND is_active = 1 AND email_verified_at IS NOT NULL',
            [date('Y-m-d H:i:s'), self::clean($note) ?: null, $adminId, $learnerId]
        );
        return (int) $n > 0 || Database::value('SELECT featured_at FROM academy_learners WHERE id = ?', [$learnerId]) !== null;
    }

    public static function unfeature(int $learnerId): void
    {
        if (self::ready()) {
            Database::execute('UPDATE academy_learners SET featured_at = NULL, featured_note = NULL, featured_by = NULL WHERE id = ?', [$learnerId]);
        }
    }

    public static function isFeatured(array $learner): bool
    {
        return self::ready() && !empty($learner['featured_at']);
    }

    /**
     * The cards for the public page, newest featured first.
     * @return list<array{id:int,name:string,country:string,photo:bool,note:string,courses:list<string>,certificates:int}>
     */
    public static function public(int $limit = 6): array
    {
        if (!self::ready()) {
            return [];
        }
        try {
            $rows = Database::all(
                'SELECT l.id, l.name, l.country, l.featured_note, (l.photo_path IS NOT NULL AND l.photo_consent_at IS NOT NULL) AS photo, '
                . '(SELECT COUNT(*) FROM academy_certificates c WHERE c.learner_id = l.id AND c.revoked_at IS NULL AND c.approved_at IS NOT NULL) AS certificates '
                . 'FROM academy_learners l WHERE l.featured_at IS NOT NULL AND l.is_active = 1 AND l.email_verified_at IS NOT NULL ORDER BY l.featured_at DESC LIMIT ' . max(1, min(24, $limit))
            );
        } catch (\Throwable) {
            return [];
        }
        $out = [];
        foreach ($rows as $r) {
            $parts = preg_split('/\s+/u', trim((string) $r['name'])) ?: [];
            $first = mb_substr((string) ($parts[0] ?? ''), 0, 40);
            $last = count($parts) > 1 ? mb_strtoupper(mb_substr((string) end($parts), 0, 1)) . '.' : '';
            $courses = array_map(static fn(array $c): string => (string) $c['title'], Database::all(
                'SELECT c.title FROM academy_enrollments e JOIN academy_courses c ON c.id = e.course_id WHERE e.learner_id = ? AND e.completed_at IS NOT NULL ORDER BY e.completed_at DESC LIMIT 3',
                [(int) $r['id']]
            ));
            $out[] = [
                'id' => (int) $r['id'], 'name' => trim($first . ' ' . $last) ?: 'Learner', 'country' => (string) ($r['country'] ?? ''),
                'photo' => (int) $r['photo'] === 1, 'note' => (string) ($r['featured_note'] ?? ''), 'courses' => $courses, 'certificates' => (int) $r['certificates'],
            ];
        }
        return $out;
    }
}
