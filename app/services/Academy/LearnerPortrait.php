<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;
use App\Core\ImageOptimizer;
use App\Core\Response;
use App\Core\UploadException;
use App\Core\Uploader;

/**
 * The portrait a learner adds to their own account (docs/ACADEMY.md). Stored privately (storage/private/academy/learners/<random>.jpg), cropped square by
 * ImageOptimizer::portrait (metadata removed). It is shown to the learner, to administrators and facilitators, and on the public Academy page ONLY when the learner
 * ticked the consent box (photo_consent_at). Everything degrades to "no picture" when the columns are missing (code uploaded before the database update).
 */
final class LearnerPortrait
{
    public const DIR = 'academy/learners';

    private static ?bool $ready = null;

    public static function ready(): bool
    {
        if (self::$ready === null) {
            try {
                self::$ready = (int) Database::value("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name IN ('photo_path', 'photo_consent_at')", [], 0) === 2;
            } catch (\Throwable) {
                self::$ready = false;
            }
        }
        return self::$ready;
    }

    /** @return array{photo_path:?string,photo_consent_at:?string} */
    public static function of(int $learnerId): array
    {
        if (!self::ready()) {
            return ['photo_path' => null, 'photo_consent_at' => null];
        }
        $r = Database::one('SELECT photo_path, photo_consent_at FROM academy_learners WHERE id = ?', [$learnerId]);
        return ['photo_path' => $r['photo_path'] ?? null, 'photo_consent_at' => $r['photo_consent_at'] ?? null];
    }

    /** @return array{ok:bool,error:string} */
    public static function save(int $learnerId, array $file, bool $consent): array
    {
        if (!self::ready()) {
            return ['ok' => false, 'error' => 'Pictures are not available yet. Please try again later.'];
        }
        $rel = self::DIR . '/' . bin2hex(random_bytes(16)) . '.jpg';
        $abs = STORAGE_PATH . '/private/' . $rel;
        try {
            $res = ImageOptimizer::portrait((string) $file['tmp_name'], $abs, ['min_side' => 300, 'max_bytes' => 5 * 1024 * 1024, 'side' => 600]);
        } catch (UploadException $x) {
            return ['ok' => false, 'error' => $x->getMessage()];
        }
        if ($res['ext'] !== 'jpg') {
            $rel2 = self::DIR . '/' . bin2hex(random_bytes(16)) . '.' . $res['ext'];
            if (!@rename($abs, STORAGE_PATH . '/private/' . $rel2)) {
                @unlink($abs);
                return ['ok' => false, 'error' => 'The server could not save the picture.'];
            }
            $rel = $rel2;
        }
        $old = (string) (self::of($learnerId)['photo_path'] ?? '');
        Database::update('academy_learners', ['photo_path' => $rel, 'photo_consent_at' => $consent ? date('Y-m-d H:i:s') : null], 'id = ?', [$learnerId]);
        if ($old !== '') {
            Uploader::deletePrivate($old);
        }
        return ['ok' => true, 'error' => ''];
    }

    public static function setConsent(int $learnerId, bool $consent): void
    {
        if (self::ready()) {
            $cur = self::of($learnerId);
            Database::update('academy_learners', ['photo_consent_at' => $consent && $cur['photo_path'] ? ($cur['photo_consent_at'] ?: date('Y-m-d H:i:s')) : null], 'id = ?', [$learnerId]);
        }
    }

    public static function remove(int $learnerId): void
    {
        if (!self::ready()) {
            return;
        }
        $old = (string) (self::of($learnerId)['photo_path'] ?? '');
        Database::update('academy_learners', ['photo_path' => null, 'photo_consent_at' => null], 'id = ?', [$learnerId]);
        if ($old !== '') {
            Uploader::deletePrivate($old);
        }
    }

    /** Send the picture file (no caching beyond the browser: the owner may replace it). */
    public static function download(int $learnerId): ?Response
    {
        $p = (string) (self::of($learnerId)['photo_path'] ?? '');
        if ($p === '') {
            return null;
        }
        try {
            $mime = ['png' => 'image/png', 'webp' => 'image/webp'][strtolower(pathinfo($p, PATHINFO_EXTENSION))] ?? 'image/jpeg';
            return Uploader::download($p, 'learner-' . $learnerId . '.jpg', true)->header('Content-Type', $mime)->header('Cache-Control', 'private, max-age=300');
        } catch (\Throwable) {
            return null;
        }
    }

    /** Learners who agreed to be shown on the public Academy page. @return list<array{id:int,first:string,country:string}> */
    public static function showcase(int $limit = 12): array
    {
        if (!self::ready()) {
            return [];
        }
        $rows = Database::all(
            'SELECT id, name, country FROM academy_learners WHERE is_active = 1 AND photo_path IS NOT NULL AND photo_consent_at IS NOT NULL ORDER BY photo_consent_at DESC LIMIT ' . max(1, min(48, $limit))
        );
        return array_map(static function (array $r): array {
            $first = trim(explode(' ', trim((string) $r['name']))[0] ?? '');
            return ['id' => (int) $r['id'], 'first' => $first !== '' ? mb_substr($first, 0, 40) : 'Learner', 'country' => (string) ($r['country'] ?? '')];
        }, $rows);
    }

    public static function isPublic(int $learnerId): bool
    {
        if (!self::ready()) {
            return false;
        }
        return Database::value('SELECT 1 FROM academy_learners WHERE id = ? AND is_active = 1 AND photo_path IS NOT NULL AND photo_consent_at IS NOT NULL', [$learnerId]) !== null;
    }
}
