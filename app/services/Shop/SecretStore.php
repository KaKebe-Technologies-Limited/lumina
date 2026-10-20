<?php
declare(strict_types=1);

namespace App\Services\Shop;

use App\Core\Config;
use App\Core\Database;
use App\Core\Env;
use App\Core\Settings;

/**
 * Encrypted storage of the payment-gateway secrets (Flutterwave secret key, webhook secret hash).
 *
 *   - Stored in the settings table under `shop_secret_<name>` as   enc:v1:<base64url(iv 12 | tag 16 | ciphertext)>   (AES-256-GCM, openssl).
 *   - The key is derived from APP_KEY (Config::appKey()) with HKDF-SHA256 (info "lumina-shop-secrets-v1"). If APP_KEY changes, the stored
 *     secrets can no longer be read: get() returns null and status() says "unreadable" - the admin simply types them again.
 *   - A tampered or truncated value fails the GCM tag check and also reads as null (never as garbage).
 *   - The .env alternative wins over the database: FLW_SECRET_KEY, FLW_SECRET_HASH (and the non-secret FLW_PUBLIC_KEY, FLW_MODE).
 *   - A secret is NEVER shown again after saving (the admin sees "set" / "replace"), never logged, never put in the audit log, a JSON
 *     response or an e-mail. Nothing in this class returns or logs plaintext except get().
 *
 * Names: flw_secret_key, flw_secret_hash.
 */
final class SecretStore
{
    public const PREFIX = 'enc:v1:';
    public const NAMES = ['flw_secret_key', 'flw_secret_hash'];
    private const ENV = ['flw_secret_key' => 'FLW_SECRET_KEY', 'flw_secret_hash' => 'FLW_SECRET_HASH'];
    private const INFO = 'lumina-shop-secrets-v1';

    /** The 32-byte AES key. $appKey is overridable for tests. */
    private static function key(?string $appKey = null): string
    {
        $ikm = $appKey ?? Config::appKey();
        return hash_hmac('sha256', self::INFO . "\x01", hash_hmac('sha256', $ikm, 'lumina-shop-salt-v1', true), true);   // HKDF extract + expand (one block)
    }

    public static function available(): bool
    {
        return function_exists('openssl_encrypt') && in_array('aes-256-gcm', array_map('strtolower', openssl_get_cipher_methods()), true);
    }

    /** Encrypt text -> "enc:v1:..." (null when openssl is missing). */
    public static function encrypt(string $plain, ?string $appKey = null): ?string
    {
        if (!self::available()) {
            return null;
        }
        $iv = random_bytes(12);
        $tag = '';
        $ct = openssl_encrypt($plain, 'aes-256-gcm', self::key($appKey), OPENSSL_RAW_DATA, $iv, $tag, '', 16);
        if ($ct === false) {
            return null;
        }
        return self::PREFIX . rtrim(strtr(base64_encode($iv . $tag . $ct), '+/', '-_'), '=');
    }

    /** "enc:v1:..." -> plaintext, or null when the value is not ours, was tampered with, or the application key changed. */
    public static function decrypt(string $stored, ?string $appKey = null): ?string
    {
        if (!self::available() || !str_starts_with($stored, self::PREFIX)) {
            return null;
        }
        $raw = base64_decode(strtr(substr($stored, strlen(self::PREFIX)), '-_', '+/'), true);
        if ($raw === false || strlen($raw) < 12 + 16 + 1) {
            return null;
        }
        $plain = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', self::key($appKey), OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
        return $plain === false ? null : $plain;
    }

    private static function settingKey(string $name): string
    {
        if (!in_array($name, self::NAMES, true)) {
            throw new \InvalidArgumentException('Unknown secret.');
        }
        return 'shop_secret_' . $name;
    }

    /** Value from .env (trimmed) or ''. */
    public static function fromEnv(string $name): string
    {
        return trim((string) Env::get(self::ENV[$name] ?? '', ''));
    }

    /** The plaintext secret: .env first, then the decrypted database value. null when unset or unreadable. */
    public static function get(string $name): ?string
    {
        $env = self::fromEnv($name);
        if ($env !== '') {
            return $env;
        }
        $stored = trim((string) Settings::get(self::settingKey($name), ''));
        if ($stored === '') {
            return null;
        }
        $plain = self::decrypt($stored);
        return $plain !== null && $plain !== '' ? $plain : null;
    }

    /** Save (or, with '', forget) a secret. Returns false when encryption is unavailable. */
    public static function put(string $name, string $plain): bool
    {
        $key = self::settingKey($name);
        $plain = trim($plain);
        if ($plain === '') {
            self::forget($name);
            return true;
        }
        $enc = self::encrypt($plain);
        if ($enc === null) {
            return false;
        }
        Settings::set($key, $enc);
        return true;
    }

    public static function forget(string $name): void
    {
        Database::execute('DELETE FROM settings WHERE setting_key = ?', [self::settingKey($name)]);
        Settings::flush();
    }

    /**
     * What the admin may know about a secret: never the value.
     * @return array{set:bool,source:string,readable:bool}  source: env | database | none ; readable false = stored but cannot be decrypted (key changed or damaged)
     */
    public static function status(string $name): array
    {
        if (self::fromEnv($name) !== '') {
            return ['set' => true, 'source' => 'env', 'readable' => true];
        }
        $stored = trim((string) Settings::get(self::settingKey($name), ''));
        if ($stored === '') {
            return ['set' => false, 'source' => 'none', 'readable' => true];
        }
        return ['set' => true, 'source' => 'database', 'readable' => self::decrypt($stored) !== null];
    }

    /** Short text for the admin: "Set in .env", "Set", "Stored but unreadable - enter it again", "Not set". */
    public static function describe(string $name): string
    {
        $s = self::status($name);
        return match (true) {
            $s['source'] === 'env'  => 'Set in the .env file',
            !$s['set']              => 'Not set',
            !$s['readable']         => 'Stored but cannot be read (the application key changed): enter it again',
            default                 => 'Set (hidden)',
        };
    }

    /** Constant-time comparison of two secrets (used for the webhook hash). */
    public static function equals(string $known, string $given): bool
    {
        return $known !== '' && $given !== '' && hash_equals($known, $given);
    }
}
