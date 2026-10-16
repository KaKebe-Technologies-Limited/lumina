<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `documents`. Generic API: see App\Core\Model. */
final class Document extends Model
{
    protected static string $table = 'documents';
    protected static string $order = 'year DESC, id DESC';

    public const CATEGORIES = [
        'annual_report' => 'Annual reports', 'financial' => 'Financial statements', 'policy' => 'Policies',
        'brief' => 'Briefs', 'newsletter' => 'Newsletters', 'other' => 'Other',
    ];

    /** Documents visitors may see/download. */
    public static function visible(array $extra = []): array
    {
        return self::where(['is_active' => 1, 'is_public' => 1] + $extra, 'year DESC, published_at DESC, id DESC');
    }
}
