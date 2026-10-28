<?php
declare(strict_types=1);

namespace App\Services\Export;

/**
 * The standard document header of the organisation, taken from Settings (Contact tab): name, tagline, address, e-mail, phones, website, registration number and the logo.
 * The same block heads every PDF, Excel and Word export.
 */
final class Letterhead
{
    /**
     * @return array{org:string,tagline:string,address:string,email:string,phones:string,website:string,reg:string,logo:?array{bytes:string,ext:string},contact_line:string}
     */
    public static function get(): array
    {
        $org = trim((string) setting('site_name', 'Lumina Africa Foundation')) ?: 'Lumina Africa Foundation';
        $address = trim(implode(', ', array_filter([
            trim((string) preg_replace('/\s+/', ' ', (string) setting('address', ''))),
            trim((string) setting('city_country', '')),
        ])));
        $email = trim((string) setting('email_primary', '')) ?: trim((string) setting('email_info', ''));
        $info = trim((string) setting('email_info', ''));
        if ($info !== '' && $info !== $email) {
            $email .= ', ' . $info;
        }
        $phones = implode(', ', array_filter([trim((string) setting('phone_1', '')), trim((string) setting('phone_2', ''))]));
        $host = (string) parse_url(abs_url(''), PHP_URL_HOST);
        $reg = trim((string) setting('reg_number', ''));
        $parts = [];
        if ($email !== '') {
            $parts[] = 'Email: ' . $email;
        }
        if ($phones !== '') {
            $parts[] = 'Tel: ' . $phones;
        }
        if ($host !== '') {
            $parts[] = $host;
        }
        return [
            'org' => $org,
            'tagline' => trim((string) setting('tagline', '')),
            'address' => $address,
            'email' => $email,
            'phones' => $phones,
            'website' => $host,
            'reg' => $reg !== '' ? 'Registration no. ' . $reg : '',
            'logo' => self::logo(),
            'contact_line' => implode('   |   ', $parts),
        ];
    }

    /** The logo file as bytes + extension (png / jpg), small enough to embed; null when none can be read. @return array{bytes:string,ext:string}|null */
    private static function logo(): ?array
    {
        $path = trim((string) setting('logo_path', ''));
        $file = null;
        if ($path === '' || str_starts_with($path, 'assets/img/brand/logo-')) {
            $file = ASSET_PATH . '/img/brand/logo-192.png';
        } elseif (str_starts_with($path, 'assets/')) {
            $file = BASE_PATH . '/' . $path;
        } elseif (!str_contains($path, '..')) {
            $file = UPLOAD_PATH . '/' . ltrim($path, '/');
        }
        if ($file === null || !is_file($file) || filesize($file) > 1500000) {
            return null;
        }
        $bytes = (string) file_get_contents($file);
        if (str_starts_with($bytes, "\x89PNG")) {
            return ['bytes' => $bytes, 'ext' => 'png'];
        }
        if (str_starts_with($bytes, "\xFF\xD8")) {
            return ['bytes' => $bytes, 'ext' => 'jpg'];
        }
        return null;
    }
}
