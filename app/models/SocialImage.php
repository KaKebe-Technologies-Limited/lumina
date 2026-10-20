<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Table `social_images` = the rendered share images and the unguessable token of each one (PRIMARY KEY `token`, not `id`: use App\Core\Database for lookups by
 * token). One row per (issue, variant). Written by App\Services\Social\SocialCard only.
 */
final class SocialImage extends Model
{
    protected static string $table = 'social_images';
    protected static string $order = 'created_at DESC';

    public const VARIANTS = ['square', 'portrait', 'landscape'];
    public const SIZES = ['square' => [1080, 1080], 'portrait' => [1080, 1920], 'landscape' => [1200, 630]];
}
