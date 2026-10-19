<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Content;
use App\Core\Database;
use App\Core\Log;
use App\Core\Request;
use App\Core\UploadException;
use App\Core\Uploader;

/**
 * Safe handling of single-image form fields for the admin content areas (B4).
 *
 * Why not Uploader::resolveImageField()? That helper deletes the replaced file immediately - even when the save later
 * fails, and even when the same upload is still used somewhere else (another row, a rich-text body, a page block).
 * ImageCleanup splits the job in two steps:
 *
 *   1. $img = ImageCleanup::resolve($request, 'image', $row['image'] ?? null);     // validates / stores a NEW upload, deletes nothing
 *   2. ...save the row with $img['value']...
 *   3. ImageCleanup::release($old)                                                   // deletes $old ONLY when nothing references it any more
 *
 * release() looks in every table/column that can hold an image path (and in rich text / JSON / settings) before it unlinks
 * anything, and keeps the file whenever it is unsure (a failing query counts as "still referenced").
 */
final class ImageCleanup
{
    /** Columns that hold exactly one stored image path. @var list<array{0:string,1:string}> */
    private const IMAGE_COLUMNS = [
        ['programs', 'image'], ['projects', 'image'], ['posts', 'cover_image'], ['activities', 'image'], ['gallery_images', 'image'],
        ['events', 'image'], ['stories', 'image'], ['team_members', 'photo'], ['partners', 'logo'], ['partners', 'logo_2x'], ['partners', 'logo_webp'], ['partners', 'logo_webp_2x'], ['pages', 'hero_image'], ['pages', 'og_image'], ['newsletters', 'cover_image'],
    ];

    /** Text columns that may embed an image path (rich bodies, JSON repeaters, settings). @var list<array{0:string,1:string}> */
    private const TEXT_COLUMNS = [
        ['programs', 'body'], ['projects', 'body'], ['posts', 'body'], ['activities', 'body'], ['jobs', 'description'], ['jobs', 'requirements'],
        ['pages', 'body'], ['events', 'body'], ['stories', 'body'], ['content_blocks', 'value'], ['settings', 'setting_value'], ['faqs', 'answer'],
        ['team_members', 'bio'], ['partners', 'description'], ['newsletters', 'content'], ['newsletter_revisions', 'content'],
    ];

    /**
     * Work out the value an image field should have after this request. Never deletes the previous file.
     *
     * Understands the same inputs as partials/admin/image-field: a multipart file "<name>_file", an AJAX-uploaded path "<name>"
     * and the removal flag "<name>_remove". Only site uploads ("YYYY/MM/x.jpg") and bundled "assets/..." images are accepted.
     *
     * @return array{value:?string, uploaded:?string, changed:bool}  value = what to store; uploaded = a file stored by THIS call
     *         (delete it with discardUpload() when the save fails); changed = value differs from $current
     * @throws UploadException on a bad file / unknown path
     */
    public static function resolve(Request $request, string $name, ?string $current): array
    {
        $current = ($current === null || $current === '') ? null : $current;

        $file = $request->file($name . '_file');
        if ($file !== null) {
            $new = Uploader::image($file);
            return ['value' => $new, 'uploaded' => $new, 'changed' => $new !== $current];
        }
        if ($request->string($name . '_remove') === '1') {
            return ['value' => null, 'uploaded' => null, 'changed' => $current !== null];
        }
        if ($request->has($name)) {
            $posted = Content::cleanImagePath($request->string($name));
            if ($posted === '') {
                return ['value' => null, 'uploaded' => null, 'changed' => $current !== null];
            }
            if ($posted !== $current) {
                if (!self::isStoredImage($posted)) {
                    throw new UploadException('That image could not be found. Please upload it again.');
                }
                return ['value' => $posted, 'uploaded' => null, 'changed' => true];
            }
        }
        return ['value' => $current, 'uploaded' => null, 'changed' => false];
    }

    /** Remove a file that resolve() stored when the save that should have used it failed. */
    public static function discardUpload(?string $uploaded): void
    {
        if ($uploaded !== null && $uploaded !== '') {
            self::release($uploaded);
        }
    }

    /**
     * Delete an upload that is no longer used. Call AFTER the row has been saved/deleted so the row itself no longer counts.
     * Bundled assets, external URLs and files still referenced anywhere are left alone. Returns true when a file was removed.
     */
    public static function release(?string $path): bool
    {
        $path = trim((string) $path);
        if ($path === '' || str_starts_with($path, 'assets/') || preg_match('~^https?://~i', $path)) {
            return false;
        }
        if (self::isReferenced($path)) {
            return false;
        }
        return Uploader::delete($path);
    }

    /** Release several paths (e.g. after a bulk delete). */
    public static function releaseMany(array $paths): int
    {
        $n = 0;
        foreach (array_unique(array_filter(array_map('strval', $paths))) as $p) {
            if (self::release($p)) {
                $n++;
            }
        }
        return $n;
    }

    /** Is the stored path still used by any row, rich-text body, content block or setting? Unsure => true (keep the file). */
    public static function isReferenced(string $path): bool
    {
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $path) . '%';
        try {
            foreach (self::IMAGE_COLUMNS as [$table, $col]) {
                if (!self::hasTable($table)) {
                    continue;
                }
                if ((int) Database::value('SELECT COUNT(*) FROM ' . Database::ident($table) . ' WHERE ' . Database::ident($col) . ' = ?', [$path], 0) > 0) {
                    return true;
                }
            }
            foreach (self::TEXT_COLUMNS as [$table, $col]) {
                if (!self::hasTable($table)) {
                    continue;
                }
                if ((int) Database::value('SELECT COUNT(*) FROM ' . Database::ident($table) . ' WHERE ' . Database::ident($col) . ' LIKE ?', [$like], 0) > 0) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('ImageCleanup::isReferenced failed, keeping file: ' . $e->getMessage());
            return true;
        }
        return false;
    }

    /** @var array<string,bool> */
    private static array $tables = [];

    private static function hasTable(string $table): bool
    {
        return self::$tables[$table] ??= Database::tableExists($table);
    }

    /** Does a stored image path point at a file that really exists (bundled asset or site upload)? */
    public static function isStoredImage(string $path): bool
    {
        if ($path === '' || str_contains($path, '..')) {
            return false;
        }
        if (str_starts_with($path, 'assets/')) {
            return is_file(BASE_PATH . '/' . $path);
        }
        if (!preg_match('~^[A-Za-z0-9_\-]+(/[A-Za-z0-9_\-]+)*/[A-Za-z0-9_\-]+\.(jpe?g|png|gif|webp)$~i', $path)) {
            return false;
        }
        return is_file(UPLOAD_PATH . '/' . $path);
    }
}
