<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `programs`. Generic API: see App\Core\Model. */
final class Program extends Model
{
    protected static string $table = 'programs';
    protected static string $order = 'sort_order ASC, id ASC';

    /** Allowed values of programs.status (new programs are 'planned'). */
    public const STATUSES = ['planned', 'ongoing', 'completed'];

    /** Visitor-facing status labels (status => text), editable in Admin > Pages > Programs > "Labels". */
    public static function statusLabels(): array
    {
        return [
            'planned'   => (string) content('programs', 'labels.status_planned'),
            'ongoing'   => (string) content('programs', 'labels.status_ongoing'),
            'completed' => (string) content('programs', 'labels.status_completed'),
        ];
    }

    public static function active(?int $limit = null): array
    {
        return self::where(['is_active' => 1], 'sort_order ASC, id ASC', $limit);
    }
}
