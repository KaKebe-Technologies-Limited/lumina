<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `opportunities` (scholarships, fellowships, internships, grants ...). Generic API: see App\Core\Model. */
final class Opportunity extends Model
{
    protected static string $table = 'opportunities';
    protected static string $order = 'is_featured DESC, posted_at DESC, id DESC';

    /** key => [label, icon] */
    public const CATEGORIES = [
        'scholarship' => ['Scholarship', 'award'],
        'fellowship'  => ['Fellowship', 'users'],
        'internship'  => ['Internship', 'briefcase'],
        'grant'       => ['Grant and funding', 'heart'],
        'job'         => ['Job', 'briefcase'],
        'training'    => ['Training and course', 'book'],
        'competition' => ['Competition and award', 'award'],
        'conference'  => ['Conference and event', 'calendar'],
        'volunteer'   => ['Volunteering', 'heart'],
        'other'       => ['Other opportunity', 'star'],
    ];

    /** SQL (alias t): shown on the website and not waiting for approval. Closed ones are separate (see open()). */
    public const SHOWN = 't.is_active = 1 AND t.needs_review = 0';
    /** SQL (alias t): not past its deadline (no deadline = rolling, counts as open). */
    public const OPEN = '(t.deadline IS NULL OR t.deadline >= CURDATE())';

    /** @return array<string,string> key => label */
    public static function categoryLabels(): array
    {
        return array_map(static fn(array $c): string => $c[0], self::CATEGORIES);
    }

    public static function categoryLabel(?string $key): string
    {
        return self::CATEGORIES[(string) $key][0] ?? self::CATEGORIES['other'][0];
    }

    public static function categoryIcon(?string $key): string
    {
        return self::CATEGORIES[(string) $key][1] ?? 'star';
    }
}
