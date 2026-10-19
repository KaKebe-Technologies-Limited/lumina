<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ImageOptimizer;
use App\Core\Log;
use App\Core\UploadException;
use App\Core\Uploader;

/**
 * Everything the partner screens and views need to know about a partner's LOGO SET (fallback picture, 2x, WebP, WebP 2x, size).
 * Contract: docs/PARTNERS_NEWSLETTER.md. Static; never touches anything but the `partners` logo columns and the files below uploads/.
 *
 *   // saving (admin controller)
 *   $up = PartnerLogos::store($request->file('logo'));              // ['columns' => [...DB columns...], 'result' => ImageOptimizer result]  (UploadException on a bad file)
 *   Partner::create(['name' => ...] + $up['columns']);              // or Partner::update($id, $up['columns'])
 *   PartnerLogos::release($oldRow);                                  // AFTER the row was saved: deletes the old files nothing else uses
 *   PartnerLogos::discard($up['result']);                            // if the save failed after store(): removes the new files again
 *
 *   // showing (views)
 *   $v = PartnerLogos::view($row);   // has, alt, src, srcset, webp_srcset, width, height, optimised   -> partials/partner-logo-img does the markup
 *
 *   // existing logos uploaded before the pipeline existed
 *   PartnerLogos::pendingCount();    PartnerLogos::optimiseExisting(10);
 */
final class PartnerLogos
{
    /** The columns that hold one picture path each. */
    public const PATH_COLUMNS = ['logo', 'logo_2x', 'logo_webp', 'logo_webp_2x'];

    /** DB columns for a row WITHOUT a logo. @return array<string,mixed> */
    public static function emptyColumns(): array
    {
        return ['logo' => null, 'logo_2x' => null, 'logo_webp' => null, 'logo_webp_2x' => null, 'logo_width' => null, 'logo_height' => null, 'logo_optimised' => 0];
    }

    /**
     * DB columns for an ImageOptimizer result.
     * @param array<string,mixed> $result
     * @return array<string,mixed>
     */
    public static function columns(array $result): array
    {
        return [
            'logo'           => (string) $result['logo'],
            'logo_2x'        => $result['logo_2x'] ?? null,
            'logo_webp'      => $result['logo_webp'] ?? null,
            'logo_webp_2x'   => $result['logo_webp_2x'] ?? null,
            'logo_width'     => (int) $result['logo_width'] > 0 ? (int) $result['logo_width'] : null,
            'logo_height'    => (int) $result['logo_height'] > 0 ? (int) $result['logo_height'] : null,
            'logo_optimised' => !empty($result['optimised']) ? 1 : 0,
        ];
    }

    /**
     * Run the pipeline on an uploaded file (Request::file()). The new files exist when this returns: save the row, or call discard().
     * @return array{columns:array<string,mixed>,result:array<string,mixed>}
     * @throws UploadException
     */
    public static function store(?array $file): array
    {
        $result = Uploader::logo($file);
        return ['columns' => self::columns($result), 'result' => $result];
    }

    /** Remove the files of a result whose database save failed. @param array<string,mixed> $result */
    public static function discard(array $result): void
    {
        foreach ((array) ($result['files'] ?? []) as $rel) {
            Uploader::delete((string) $rel);
        }
    }

    /**
     * Every upload path a partner row uses for its logo (unique, non-empty).
     * @param array<string,mixed> $row
     * @return list<string>
     */
    public static function paths(array $row): array
    {
        $out = [];
        foreach (self::PATH_COLUMNS as $c) {
            $p = trim((string) ($row[$c] ?? ''));
            if ($p !== '') {
                $out[$p] = true;
            }
        }
        return array_keys($out);
    }

    /**
     * Delete the files of a logo set that the row no longer uses (replaced or removed logo, deleted partner). Call it AFTER the row was saved or
     * deleted. A file that another row (or a page, a setting, rich text) still references is kept: ImageCleanup::release() checks every place.
     * Bundled "assets/..." pictures and external URLs are never deleted. Returns the number of files removed.
     *
     * @param array<string,mixed> $oldRow the row as it was BEFORE the change
     */
    public static function release(array $oldRow): int
    {
        $n = 0;
        foreach (self::paths($oldRow) as $p) {
            try {
                if (ImageCleanup::release($p)) {
                    $n++;
                }
            } catch (\Throwable $e) {
                Log::warning('PartnerLogos::release kept ' . $p . ': ' . $e->getMessage());
            }
        }
        return $n;
    }

    /**
     * View model of a logo for templates. `has` is false for a partner without a picture (show the name). URLs are ready to print (escape them).
     * width / height are the 1x size: print them as attributes so the page does not jump while the picture loads (null for old unoptimised logos).
     *
     * @param array<string,mixed> $row a partners row
     * @return array{has:bool,alt:string,src:string,srcset:string,webp:string,webp_srcset:string,width:?int,height:?int,optimised:bool}
     */
    public static function view(array $row): array
    {
        $alt = trim((string) ($row['name'] ?? ''));
        $src = img_url(trim((string) ($row['logo'] ?? '')));
        if ($src === '') {
            return ['has' => false, 'missing' => false, 'alt' => $alt, 'src' => '', 'srcset' => '', 'webp' => '', 'webp_srcset' => '', 'width' => null, 'height' => null, 'optimised' => false];
        }
        $x2 = img_url(trim((string) ($row['logo_2x'] ?? '')));
        $w = img_url(trim((string) ($row['logo_webp'] ?? '')));
        $w2 = img_url(trim((string) ($row['logo_webp_2x'] ?? '')));
        $width = (int) ($row['logo_width'] ?? 0);
        $height = (int) ($row['logo_height'] ?? 0);
        return [
            'has'         => true,
            'missing'     => self::fileMissing($row),          // the row points at a file that is gone: templates show the name instead of a broken picture
            'alt'         => $alt,
            'src'         => $src,
            'srcset'      => $x2 !== '' ? $src . ' 1x, ' . $x2 . ' 2x' : '',
            'webp'        => $w,
            'webp_srcset' => $w !== '' ? ($w2 !== '' ? $w . ' 1x, ' . $w2 . ' 2x' : $w) : '',
            'width'       => $width > 0 ? $width : null,
            'height'      => $height > 0 ? $height : null,
            'optimised'   => !empty($row['logo_optimised']),
        ];
    }

    // ------------------------------------------------------------------ logos uploaded before the pipeline existed

    /**
     * Does the row point at an uploaded logo whose file is gone (deleted over FTP, a database restored without the uploads folder)? Logos shipped
     * with the theme (assets/...) and web addresses are not checked.
     */
    public static function fileMissing(array $row): bool
    {
        $logo = trim((string) ($row['logo'] ?? ''));
        if ($logo === '' || preg_match('~^(assets/|https?://)~i', $logo) === 1) {
            return false;
        }
        return str_contains($logo, '..') || !is_file(UPLOAD_PATH . '/' . ltrim($logo, '/'));
    }

    /** @return list<array<string,mixed>> partners whose uploaded logo was not made by the pipeline (id, name, logo) */
    private static function unoptimisedRows(): array
    {
        try {
            return Database::all("SELECT id, name, logo FROM partners WHERE logo IS NOT NULL AND logo <> '' AND logo_optimised = 0 AND logo NOT LIKE 'assets/%' AND logo NOT LIKE 'http%'");
        } catch (\Throwable) {
            return [];    // old schema (columns not imported yet)
        }
    }

    /**
     * How many partners still have a logo that was not run through the pipeline (uploaded before it existed, or without GD) AND whose file is
     * there. A partner whose file is missing cannot be optimised: it is counted by missingCount() instead, so the "not optimised yet" banner can clear.
     */
    public static function pendingCount(): int
    {
        return count(array_filter(self::unoptimisedRows(), static fn(array $r): bool => !self::fileMissing($r)));
    }

    /** How many partners point at a logo file that no longer exists (they show their name on the website until a new logo is uploaded). */
    public static function missingCount(): int
    {
        try {
            $rows = Database::all("SELECT id, logo FROM partners WHERE logo IS NOT NULL AND logo <> '' AND logo NOT LIKE 'assets/%' AND logo NOT LIKE 'http%'");
        } catch (\Throwable) {
            return 0;
        }
        return count(array_filter($rows, static fn(array $r): bool => self::fileMissing($r)));
    }

    /**
     * Run the pipeline over existing logos, `$limit` rows per call, in id order, starting after `$afterId`. Call again with the returned
     * `last_id` until `remaining` is 0 (a logo that fails is skipped, so the loop always ends). Safe to repeat. A logo that cannot be processed
     * is left exactly as it was and listed in `errors` (partner id => message). Without GD nothing is touched and `notice` says why.
     *
     * @return array{done:int,failed:int,remaining:int,last_id:int,notice:?string,errors:array<int,string>}
     */
    public static function optimiseExisting(int $limit = 10, int $afterId = 0, ?bool $trim = null): array
    {
        $out = ['done' => 0, 'failed' => 0, 'remaining' => 0, 'last_id' => $afterId, 'notice' => null, 'errors' => []];
        if (!ImageOptimizer::capabilities()['gd']) {
            $out['remaining'] = self::pendingCount();
            $out['notice'] = ImageOptimizer::capabilityNotice();
            return $out;
        }
        if ($trim === null) {
            $trim = (string) setting('partners_logo_trim', '1') !== '0';
        }
        $rows = Database::all(
            "SELECT * FROM partners WHERE id > ? AND logo IS NOT NULL AND logo <> '' AND logo_optimised = 0 AND logo NOT LIKE 'assets/%' AND logo NOT LIKE 'http%' ORDER BY id ASC LIMIT ?",
            [max(0, $afterId), max(1, min(100, $limit))]
        );
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $out['last_id'] = $id;
            $abs = UPLOAD_PATH . '/' . ltrim((string) $row['logo'], '/');
            if (str_contains((string) $row['logo'], '..') || !is_file($abs)) {
                $out['failed']++;
                $out['errors'][$id] = 'The logo file of "' . $row['name'] . '" is missing. Upload it again.';
                continue;
            }
            try {
                $r = ImageOptimizer::logo($abs, ['trim' => $trim]);
            } catch (UploadException $e) {
                $out['failed']++;
                $out['errors'][$id] = '"' . $row['name'] . '": ' . $e->getMessage();
                continue;
            }
            if (empty($r['optimised'])) {
                self::discard($r);
                $out['failed']++;
                $out['errors'][$id] = '"' . $row['name'] . '": ' . (string) ($r['notice'] ?? 'not optimised');
                continue;
            }
            try {
                Database::update('partners', self::columns($r), 'id = ?', [$id]);
            } catch (\Throwable $e) {
                self::discard($r);
                $out['failed']++;
                $out['errors'][$id] = '"' . $row['name'] . '" could not be saved.';
                continue;
            }
            self::release($row);                     // the old original goes when nothing else uses it
            $out['done']++;
        }
        $out['remaining'] = (int) Database::value(
            "SELECT COUNT(*) FROM partners WHERE id > ? AND logo IS NOT NULL AND logo <> '' AND logo_optimised = 0 AND logo NOT LIKE 'assets/%' AND logo NOT LIKE 'http%'",
            [$out['last_id']],
            0
        );
        return $out;
    }
}
