<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Roles;

/**
 * Who may do what in the Academy admin. academy.manage (administrators) = everything. academy.teach (the Facilitator role) = only the courses
 * they own (courses.facilitator_id = their user id), the classes of their courses (or ones they run), and the learners enrolled in their courses.
 */
final class AcademyAccess
{
    public static function manager(): bool
    {
        return can('academy.manage');
    }

    public static function teacher(): bool
    {
        return can('academy.teach') || can('academy.manage');
    }

    public static function userId(): int
    {
        return (int) (Auth::id() ?? 0);
    }

    /** May the signed-in staff member edit this course (details, lessons, quizzes, publish)? */
    public static function canEdit(?array $course): bool
    {
        if ($course === null) {
            return false;
        }
        if (self::manager()) {
            return true;
        }
        return can('academy.teach') && (int) ($course['facilitator_id'] ?? 0) === self::userId() && self::userId() > 0;
    }

    /** SQL fragment (alias c) limiting a course list to what this staff member may see. @return array{0:string,1:list<mixed>} */
    public static function courseScope(string $alias = 'c'): array
    {
        if (self::manager()) {
            return ['1 = 1', []];
        }
        return [$alias . '.facilitator_id = ?', [self::userId()]];
    }

    /** Ids of the courses the staff member may manage (null = all). @return list<int>|null */
    public static function courseIds(): ?array
    {
        if (self::manager()) {
            return null;
        }
        return array_map('intval', array_column(Database::all('SELECT id FROM academy_courses WHERE facilitator_id = ?', [self::userId()]), 'id'));
    }

    /** May the staff member see/manage this class? (managers: all; facilitators: classes they run or of their courses) */
    public static function canClass(array $class): bool
    {
        if (self::manager()) {
            return true;
        }
        if (!can('academy.teach')) {
            return false;
        }
        if ((int) ($class['facilitator_id'] ?? 0) === self::userId()) {
            return true;
        }
        $c = $class['course_id'] !== null ? CourseService::find((int) $class['course_id']) : null;
        return $c !== null && (int) $c['facilitator_id'] === self::userId();
    }

    /** Active staff accounts whose role may teach. @return list<array{id:int,name:string,email:string,role:string}> */
    public static function facilitators(): array
    {
        $roles = [];
        foreach (Roles::slugs() as $slug) {
            if (Roles::can($slug, 'academy.teach')) {
                $roles[] = $slug;
            }
        }
        if ($roles === []) {
            return [];
        }
        $in = implode(',', array_fill(0, count($roles), '?'));
        return array_map(static fn(array $r): array => ['id' => (int) $r['id'], 'name' => (string) $r['name'], 'email' => (string) $r['email'], 'role' => (string) $r['role']],
            Database::all('SELECT id, name, email, role FROM users WHERE is_active = 1 AND role IN (' . $in . ') ORDER BY name', $roles));
    }
}
