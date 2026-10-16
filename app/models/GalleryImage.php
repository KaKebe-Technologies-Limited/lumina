<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `gallery_images`. Generic API: see App\Core\Model. */
final class GalleryImage extends Model
{
    protected static string $table = 'gallery_images';
    protected static string $order = 'sort_order ASC, id DESC';

    public static function active(?string $album = null): array
    {
        return self::where($album !== null ? ['is_active' => 1, 'album' => $album] : ['is_active' => 1], 'sort_order ASC, id DESC');
    }
}
