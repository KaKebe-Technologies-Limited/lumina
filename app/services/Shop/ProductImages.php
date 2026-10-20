<?php
declare(strict_types=1);

namespace App\Services\Shop;

use App\Core\Database;
use App\Core\ImageOptimizer;
use App\Core\UploadException;
use App\Core\Uploader;
use App\Services\ImageCleanup;

/**
 * PRODUCT PICTURES: upload + optimise, order, cover, alt text, delete, and the URLs a template needs.
 *
 *   ProductImages::upload($productId, $request->file('image'), ['crop' => 'square', 'alt' => 'Navy hoodie'])   // -> the new row (see view())
 *   ProductImages::view($row)   // ['src','srcset','webp','webp_srcset','width','height','alt','is_cover',...] ready for <picture>
 *
 * With GD: the picture is validated (real PNG/JPEG/WebP/GIF only, <= 6000 px, <= 5 MB, no hidden code), turned upright, stripped of all
 * metadata by re-encoding, CROPPED ('square' 1:1 | 'portrait' 4:5 | 'none' keeps the shape), flattened on white, and written to
 * uploads/shop/YYYY/MM as   <name>.jpg (<= 600 px)   <name>-2x.jpg (<= 1200 px)   <name>.webp   <name>-2x.webp   - never upscaled: a small
 * source gets no 2x. Without GD the validated original is stored with its metadata removed (Uploader::image) and `notice` says so.
 * Bundled sample pictures live in assets/img/shop/ and are never deleted.
 */
final class ProductImages
{
    public const MAX_PER_PRODUCT = 12;
    public const CROPS = ['square', 'portrait', 'none'];
    private const BOX_1X = 600;
    private const BOX_2X = 1200;
    private const JPEG_Q = 82;
    private const WEBP_Q = 82;

    /** The picture rows of a product, cover first. @return list<array<string,mixed>> raw rows */
    public static function forProduct(int $productId): array
    {
        return Database::all('SELECT * FROM shop_product_images WHERE product_id = ? ORDER BY is_cover DESC, sort_order ASC, id ASC', [$productId]);
    }

    /**
     * Row -> template data. srcset strings are ready to print (already URL-safe; escape with e()).
     * @param array<string,mixed> $row
     * @return array<string,mixed> row + src, src2x, webp, webp2x, srcset, webp_srcset, width, height, alt, is_cover (bool)
     */
    public static function view(array $row): array
    {
        $w = (int) ($row['width'] ?? 0);
        $src = img_url((string) $row['path']);
        $src2 = (string) ($row['path_2x'] ?? '') !== '' ? img_url((string) $row['path_2x']) : '';
        $webp = (string) ($row['path_webp'] ?? '') !== '' ? img_url((string) $row['path_webp']) : '';
        $webp2 = (string) ($row['path_webp_2x'] ?? '') !== '' ? img_url((string) $row['path_webp_2x']) : '';
        return array_merge($row, [
            'src'         => $src,
            'src2x'       => $src2,
            'webp'        => $webp,
            'webp2x'      => $webp2,
            'srcset'      => $src2 !== '' && $w > 0 ? $src . ' ' . $w . 'w, ' . $src2 . ' ' . ($w * 2) . 'w' : '',
            'webp_srcset' => $webp !== '' && $webp2 !== '' && $w > 0 ? $webp . ' ' . $w . 'w, ' . $webp2 . ' ' . ($w * 2) . 'w' : '',
            'width'       => $w > 0 ? $w : null,
            'height'      => (int) ($row['height'] ?? 0) > 0 ? (int) $row['height'] : null,
            'alt'         => (string) ($row['alt'] ?? ''),
            'is_cover'    => (int) ($row['is_cover'] ?? 0) === 1,
        ]);
    }

    /**
     * Upload, optimise and attach a picture. Throws UploadException (message safe to show). Files written are removed again when the insert fails.
     * @param array<string,mixed>|null $file one entry of $_FILES / Request::file()
     * @param array{crop?:string,alt?:string,allow_nonupload?:bool} $opts allow_nonupload only for tests / CLI
     * @return array<string,mixed> the saved row (raw) plus 'notice' (?string)
     */
    public static function upload(int $productId, ?array $file, array $opts = []): array
    {
        if ($file === null || (int) ($file['error'] ?? 0) === UPLOAD_ERR_NO_FILE) {
            throw new UploadException('Choose a picture to upload.');
        }
        if ((int) ($file['error'] ?? 0) !== UPLOAD_ERR_OK) {
            throw new UploadException('The picture could not be uploaded (it may be larger than the server allows). Try a smaller file.');
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || (!is_uploaded_file($tmp) && empty($opts['allow_nonupload']))) {
            throw new UploadException('The picture could not be uploaded.');
        }
        if (!Database::one('SELECT id FROM shop_products WHERE id = ?', [$productId])) {
            throw new UploadException('That product no longer exists.');
        }
        if ((int) Database::value('SELECT COUNT(*) FROM shop_product_images WHERE product_id = ?', [$productId], 0) >= self::MAX_PER_PRODUCT) {
            throw new UploadException('A product can have at most ' . self::MAX_PER_PRODUCT . ' pictures. Delete one first.');
        }
        $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            throw new UploadException('Only JPG, PNG, WebP or GIF pictures are allowed.');
        }
        $info = ImageOptimizer::inspect($tmp, ['max_bytes' => 8 * 1024 * 1024, 'noun' => 'picture']);
        $crop = in_array($opts['crop'] ?? 'square', self::CROPS, true) ? (string) ($opts['crop'] ?? 'square') : 'square';

        $made = ['files' => [], 'notice' => null];
        if (ImageOptimizer::capabilities()['gd'] && ImageOptimizer::fitsInMemory($info['width'], $info['height'])) {
            $made = self::optimise($tmp, $info, $crop);
        }
        if ($made['files'] === []) {
            // no GD (or too big for memory): keep the validated original, metadata removed by the shared uploader
            $stored = Uploader::image($file);
            $size = @getimagesize(UPLOAD_PATH . '/' . $stored);
            $made = [
                'row'    => ['path' => $stored, 'path_webp' => null, 'path_2x' => null, 'path_webp_2x' => null, 'width' => $size ? (int) $size[0] : null, 'height' => $size ? (int) $size[1] : null],
                'files'  => [$stored],
                'notice' => 'This server cannot resize pictures (the GD extension is missing), so the picture was stored as uploaded. Use a picture about 1200 px wide for the best result.',
            ];
        }
        try {
            $sort = (int) Database::value('SELECT COALESCE(MAX(sort_order), 0) FROM shop_product_images WHERE product_id = ?', [$productId], 0) + 10;
            $first = (int) Database::value('SELECT COUNT(*) FROM shop_product_images WHERE product_id = ?', [$productId], 0) === 0;
            $row = $made['row'] + [
                'product_id' => $productId,
                'alt'        => mb_substr(trim(strip_tags((string) ($opts['alt'] ?? ''))), 0, 190),
                'sort_order' => $sort,
                'is_cover'   => $first ? 1 : 0,
            ];
            $id = Database::insert('shop_product_images', $row);
        } catch (\Throwable $e) {
            foreach ($made['files'] as $f) {
                Uploader::delete($f);
            }
            throw new UploadException('The picture could not be saved. Please try again.');
        }
        $saved = Database::one('SELECT * FROM shop_product_images WHERE id = ?', [$id]) ?? [];
        $saved['notice'] = $made['notice'];
        return $saved;
    }

    /** Delete a picture (row first, then the files when nothing else uses them). The next picture becomes the cover if needed. */
    public static function remove(int $imageId): bool
    {
        $row = Database::one('SELECT * FROM shop_product_images WHERE id = ?', [$imageId]);
        if ($row === null) {
            return false;
        }
        Database::execute('DELETE FROM shop_product_images WHERE id = ?', [$imageId]);
        if ((int) $row['is_cover'] === 1) {
            self::ensureCover((int) $row['product_id']);
        }
        self::releaseFiles($row);
        return true;
    }

    /** Delete the files of picture rows that were removed with their product (call AFTER deleting the product). @param list<array<string,mixed>> $rows */
    public static function releaseFiles(array $row): void
    {
        foreach (['path', 'path_webp', 'path_2x', 'path_webp_2x'] as $col) {
            if ((string) ($row[$col] ?? '') !== '') {
                try {
                    ImageCleanup::release((string) $row[$col]);
                } catch (\Throwable) {
                }
            }
        }
    }

    /** Make $imageId the cover (first picture) of its product. */
    public static function setCover(int $productId, int $imageId): bool
    {
        if (Database::one('SELECT id FROM shop_product_images WHERE id = ? AND product_id = ?', [$imageId, $productId]) === null) {
            return false;
        }
        Database::transaction(static function () use ($productId, $imageId): void {
            Database::execute('UPDATE shop_product_images SET is_cover = 0 WHERE product_id = ?', [$productId]);
            Database::execute('UPDATE shop_product_images SET is_cover = 1 WHERE id = ?', [$imageId]);
        });
        return true;
    }

    /** Set the order from a list of picture ids (ids of other products are ignored). The first one becomes the cover. */
    public static function reorder(int $productId, array $orderedIds): void
    {
        $own = array_map('intval', array_column(Database::all('SELECT id FROM shop_product_images WHERE product_id = ?', [$productId]), 'id'));
        $ids = array_values(array_unique(array_filter(array_map('intval', $orderedIds), static fn(int $i): bool => in_array($i, $own, true))));
        foreach ($own as $id) {
            if (!in_array($id, $ids, true)) {
                $ids[] = $id;                                       // pictures missing from the list keep their relative place at the end
            }
        }
        Database::transaction(static function () use ($ids, $productId): void {
            foreach ($ids as $i => $id) {
                Database::execute('UPDATE shop_product_images SET sort_order = ? WHERE id = ? AND product_id = ?', [($i + 1) * 10, $id, $productId]);
            }
        });
        if ($ids !== []) {
            self::setCover($productId, $ids[0]);
        }
    }

    public static function updateAlt(int $imageId, string $alt): void
    {
        Database::execute('UPDATE shop_product_images SET alt = ? WHERE id = ?', [mb_substr(trim(strip_tags($alt)), 0, 190), $imageId]);
    }

    /** Guarantee that a product with pictures has exactly one cover. */
    public static function ensureCover(int $productId): void
    {
        $has = (int) Database::value('SELECT COUNT(*) FROM shop_product_images WHERE product_id = ? AND is_cover = 1', [$productId], 0);
        if ($has === 1) {
            return;
        }
        $first = Database::value('SELECT id FROM shop_product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1', [$productId]);
        if ($first !== null) {
            self::setCover($productId, (int) $first);
        }
    }

    // ------------------------------------------------------------------------------------------------ GD pipeline

    /**
     * @param array{mime:string,ext:string,width:int,height:int,bytes:int} $info
     * @return array{row?:array<string,mixed>,files:list<string>,notice:?string}  files empty = GD failed (caller falls back)
     */
    private static function optimise(string $file, array $info, string $crop): array
    {
        $src = match ($info['mime']) {
            'image/jpeg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($file) : false,
            'image/png'  => function_exists('imagecreatefrompng') ? @imagecreatefrompng($file) : false,
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file) : false,
            'image/gif'  => function_exists('imagecreatefromgif') ? @imagecreatefromgif($file) : false,
            default      => false,
        };
        if (!$src instanceof \GdImage) {
            return ['files' => [], 'notice' => null];
        }
        // upright
        if ($info['mime'] === 'image/jpeg' && function_exists('exif_read_data') && function_exists('imagerotate')) {
            $exif = @exif_read_data($file);
            $rot = match ((int) ($exif['Orientation'] ?? 1)) { 3 => 180, 6 => -90, 8 => 90, default => 0 };
            if ($rot !== 0) {
                $r = @imagerotate($src, $rot, 0);
                if ($r instanceof \GdImage) {
                    $src = $r;
                }
            }
        }
        $sw = imagesx($src);
        $sh = imagesy($src);
        // crop box in source pixels
        $cx = 0;
        $cy = 0;
        $cw = $sw;
        $ch = $sh;
        $ratio = $crop === 'square' ? 1.0 : ($crop === 'portrait' ? 0.8 : null);      // width / height
        if ($ratio !== null) {
            if ($sw / $sh > $ratio) {
                $cw = (int) round($sh * $ratio);
                $cx = intdiv($sw - $cw, 2);
            } else {
                $ch = (int) round($sw / $ratio);
                $cy = intdiv($sh - $ch, 2);
            }
        }
        $maxSide = max($cw, $ch);
        // sizes: 2x = <= 1200, 1x = half of it; never upscaled
        if ($maxSide >= self::BOX_2X) {
            $scale2 = self::BOX_2X / $maxSide;
            $scale1 = self::BOX_1X / $maxSide;
            $has2 = true;
        } elseif ($maxSide >= 800) {
            $scale2 = 1.0;
            $scale1 = 0.5;
            $has2 = true;
        } else {
            $scale2 = 0.0;
            $scale1 = 1.0;
            $has2 = false;
        }
        $dir = 'shop/' . date('Y') . '/' . date('m');
        $abs = UPLOAD_PATH . '/' . $dir;
        if (!is_dir($abs) && !@mkdir($abs, 0775, true) && !is_dir($abs)) {
            return ['files' => [], 'notice' => null];
        }
        $base = bin2hex(random_bytes(12));
        $files = [];
        $row = ['path' => null, 'path_webp' => null, 'path_2x' => null, 'path_webp_2x' => null, 'width' => null, 'height' => null];
        $write = static function (float $scale, string $suffix, bool $primary) use ($src, $cx, $cy, $cw, $ch, $dir, $abs, $base, &$files, &$row): bool {
            $tw = max(1, (int) round($cw * $scale));
            $th = max(1, (int) round($ch * $scale));
            $dst = imagecreatetruecolor($tw, $th);
            if ($dst === false) {
                return false;
            }
            imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));                // flatten transparency on white
            imagecopyresampled($dst, $src, 0, 0, $cx, $cy, $tw, $th, $cw, $ch);
            imageinterlace($dst, true);
            $jpg = $dir . '/' . $base . $suffix . '.jpg';
            if (!@imagejpeg($dst, UPLOAD_PATH . '/' . $jpg, self::JPEG_Q)) {
                return false;
            }
            @chmod(UPLOAD_PATH . '/' . $jpg, 0644);
            $files[] = $jpg;
            $row[$suffix === '' ? 'path' : 'path_2x'] = $jpg;
            if (function_exists('imagewebp')) {
                $webp = $dir . '/' . $base . $suffix . '.webp';
                if (@imagewebp($dst, UPLOAD_PATH . '/' . $webp, self::WEBP_Q)) {
                    @chmod(UPLOAD_PATH . '/' . $webp, 0644);
                    $files[] = $webp;
                    $row[$suffix === '' ? 'path_webp' : 'path_webp_2x'] = $webp;
                }
            }
            if ($primary) {
                $row['width'] = $tw;
                $row['height'] = $th;
            }
            return true;
        };
        $ok = $write($scale1, '', true) && (!$has2 || $write($scale2, '-2x', false));
        if (!$ok) {
            foreach ($files as $f) {
                Uploader::delete($f);
            }
            return ['files' => [], 'notice' => null];
        }
        return ['row' => $row, 'files' => $files, 'notice' => null];
    }
}
