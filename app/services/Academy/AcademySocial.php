<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;
use App\Core\Session;

/**
 * The "follow and subscribe" check of the LAF Academy sign-up. A website cannot ask Facebook, Instagram or TikTok whether a person follows a page, so the check has three parts:
 *   1. the learner OPENS each of our pages from the sign-up form through /academy/go/{page} (the click is recorded in the session and kept);
 *   2. the learner gives their USERNAME on Instagram, TikTok and X (and, if they wish, Facebook, YouTube, LinkedIn);
 *   3. the Academy team looks the username up in the followers list and marks the learner VERIFIED (Admin > LAF Academy > Social follow check).
 * Needs database/updates/2026-11-academy-social-follow.sql: without the table the usernames are not asked and only the clicks are required.
 */
final class AcademySocial
{
    /** platform key => [label, required?, profile URL pattern]. Only the platforms that have a page address in Admin > Social media are asked. */
    public const HANDLES = [
        'instagram' => ['Instagram', true,  'https://www.instagram.com/%s/'],
        'tiktok'    => ['TikTok',    true,  'https://www.tiktok.com/@%s'],
        'x'         => ['X',         true,  'https://x.com/%s'],
        'facebook'  => ['Facebook',  false, ''],
        'youtube'   => ['YouTube',   false, ''],
        'linkedin'  => ['LinkedIn',  false, ''],
    ];

    private const SESSION = 'academy_follow_opened';

    public static function ready(): bool
    {
        static $ok = null;
        if ($ok === null) {
            try {
                $ok = Database::tableExists('academy_social');
            } catch (\Throwable) {
                $ok = false;
            }
        }
        return $ok;
    }

    /** The social pages that are set, by key. @return array<string,array{key:string,label:string,url:string,icon:string}> */
    public static function pages(): array
    {
        $out = [];
        foreach (social_links('all') as $l) {
            $out[(string) $l['key']] = $l;
        }
        return $out;
    }

    /** Remember that this browser opened a page (called by /academy/go/{key}). */
    public static function markOpened(string $key): void
    {
        $o = (array) Session::get(self::SESSION, []);
        $o[$key] = time();
        Session::put(self::SESSION, $o);
    }

    /** @return list<string> the keys opened in this browser session */
    public static function opened(): array
    {
        return array_keys((array) Session::get(self::SESSION, []));
    }

    /** Did this browser open EVERY page that is set? */
    public static function allOpened(): bool
    {
        $pages = array_keys(self::pages());
        return $pages !== [] && array_diff($pages, self::opened()) === [];
    }

    /** The username fields to show: platform keys that have a page address. @return list<string> */
    public static function fields(): array
    {
        if (!self::ready()) {
            return [];
        }
        return array_values(array_filter(array_keys(self::HANDLES), static fn(string $k): bool => isset(self::pages()[$k])));
    }

    /** "@name", a profile link or a name typed in any way -> the bare username, or '' when it cannot be one. */
    public static function normaliseHandle(string $v): string
    {
        $v = trim($v);
        if ($v === '') {
            return '';
        }
        if (preg_match('~^(?:https?://)?(?:www\.|m\.|mobile\.)?[a-z0-9.-]+\.[a-z]{2,}/(.+)$~i', $v, $m) === 1) {
            $parts = array_values(array_filter(explode('/', (string) strtok($m[1], '?#'))));
            $v = (string) ($parts[0] ?? '');
            if (strtolower($v) === 'in' || strtolower($v) === 'company' || strtolower($v) === 'user') {
                $v = (string) ($parts[1] ?? '');
            }
        }
        $v = ltrim($v, '@');
        return preg_match('/^[A-Za-z0-9._-]{2,60}$/', $v) === 1 ? $v : '';
    }

    /**
     * Read and check the username fields of a form. @param callable(string):string $input reads a form field
     * @return array{handles:array<string,string>,errors:array<string,string>}
     */
    public static function collect(callable $input): array
    {
        $handles = [];
        $errors = [];
        foreach (self::fields() as $k) {
            [$label, $required] = self::HANDLES[$k];
            $raw = (string) $input('handle_' . $k);
            $h = self::normaliseHandle($raw);
            if ($h === '') {
                if ($required || trim($raw) !== '') {
                    $errors['handle_' . $k] = trim($raw) === '' ? 'Please type your ' . $label . ' username.' : 'That does not look like a ' . $label . ' username. Type it like @yourname.';
                }
                continue;
            }
            $handles[$k] = $h;
        }
        return ['handles' => $handles, 'errors' => $errors];
    }

    /** Keep the usernames and the pages opened. @param array<string,string> $handles */
    public static function save(int $learnerId, array $handles): void
    {
        if (!self::ready()) {
            return;
        }
        Database::execute(
            'INSERT INTO academy_social (learner_id, handles, opened) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE handles = VALUES(handles), opened = VALUES(opened), verified_at = NULL, verified_by = NULL',
            [$learnerId, json_encode($handles, JSON_UNESCAPED_SLASHES), json_encode(self::opened())]
        );
    }

    public static function setVerified(int $learnerId, bool $on, ?int $adminId): void
    {
        if (self::ready()) {
            Database::execute('UPDATE academy_social SET verified_at = ?, verified_by = ? WHERE learner_id = ?', [$on ? date('Y-m-d H:i:s') : null, $on ? $adminId : null, $learnerId]);
        }
    }

    /** The link an administrator opens to look the learner up. */
    public static function profileUrl(string $platform, string $handle): string
    {
        $pat = self::HANDLES[$platform][2] ?? '';
        return $pat !== '' && $handle !== '' ? sprintf($pat, rawurlencode($handle)) : '';
    }

    /**
     * The check list: learners with what they gave. $status: '' all | 'unverified' | 'verified'.
     * @return list<array<string,mixed>>
     */
    public static function list(string $status = '', string $q = '', int $limit = 300): array
    {
        if (!self::ready()) {
            return [];
        }
        $where = ['1 = 1'];
        $params = [];
        if ($status === 'verified') {
            $where[] = 's.verified_at IS NOT NULL';
        } elseif ($status === 'unverified') {
            $where[] = 's.verified_at IS NULL';
        }
        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $where[] = '(l.name LIKE ? OR l.email LIKE ? OR l.reg_no LIKE ? OR s.handles LIKE ?)';
            array_push($params, $like, $like, $like, $like);
        }
        $rows = Database::all(
            'SELECT s.learner_id, s.handles, s.opened, s.verified_at, s.created_at, l.name, l.email, l.reg_no, l.country FROM academy_social s JOIN academy_learners l ON l.id = s.learner_id WHERE '
            . implode(' AND ', $where) . ' ORDER BY (s.verified_at IS NULL) DESC, s.created_at DESC LIMIT ' . max(1, min(1000, $limit)),
            $params
        );
        foreach ($rows as &$r) {
            $h = json_decode((string) $r['handles'], true);
            $o = json_decode((string) $r['opened'], true);
            $r['handles'] = is_array($h) ? $h : [];
            $r['opened'] = is_array($o) ? $o : [];
        }
        unset($r);
        return $rows;
    }

    /** @return array{total:int,verified:int,unverified:int} */
    public static function counts(): array
    {
        if (!self::ready()) {
            return ['total' => 0, 'verified' => 0, 'unverified' => 0];
        }
        $r = Database::one('SELECT COUNT(*) AS t, SUM(verified_at IS NOT NULL) AS v FROM academy_social') ?? [];
        $t = (int) ($r['t'] ?? 0);
        $v = (int) ($r['v'] ?? 0);
        return ['total' => $t, 'verified' => $v, 'unverified' => $t - $v];
    }
}
