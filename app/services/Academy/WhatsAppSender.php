<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Env;
use App\Core\Log;

/**
 * Sends the confirmation code through the WhatsApp Business Cloud API (Meta). Switched on only when the .env file holds the credentials:
 *
 *   WHATSAPP_TOKEN=...            permanent access token of a system user (Meta Business settings)
 *   WHATSAPP_PHONE_ID=...         "Phone number ID" of the WhatsApp Business number (NOT the phone number itself)
 *   WHATSAPP_TEMPLATE=verification_code   name of an approved template of the category "Authentication" (one {{1}} parameter = the code)
 *   WHATSAPP_LANGUAGE=en          language code the template was approved in (en, en_US ...)
 *   WHATSAPP_COUNTRY_CODE=256     added when a learner types a local number that starts with 0
 *   WHATSAPP_COPY_BUTTON=1        1 when the template has the standard "Copy code" button (authentication templates do), 0 when it has none
 *
 * Without the credentials enabled() is false and every screen simply hides the WhatsApp option. Numbers and codes are never written to the logs.
 */
final class WhatsAppSender
{
    private const API = 'https://graph.facebook.com/v21.0/';

    public static function enabled(): bool
    {
        return self::cfg('WHATSAPP_TOKEN') !== '' && self::cfg('WHATSAPP_PHONE_ID') !== '';
    }

    private static function cfg(string $key, string $default = ''): string
    {
        return trim((string) (Env::get($key, $default) ?? $default));
    }

    /** Digits only, international format without "+" (e.g. 256772123456), or null when it cannot be a WhatsApp number. */
    public static function normalise(string $input): ?string
    {
        $d = (string) preg_replace('/\D+/', '', $input);
        if ($d === '') {
            return null;
        }
        if (str_starts_with($d, '00')) {
            $d = substr($d, 2);
        } elseif (str_starts_with($d, '0')) {
            $cc = (string) preg_replace('/\D+/', '', self::cfg('WHATSAPP_COUNTRY_CODE'));
            if ($cc === '') {
                return null;                 // a local number and no default country: ask for the full international number
            }
            $d = $cc . ltrim($d, '0');
        }
        return strlen($d) >= 8 && strlen($d) <= 15 ? $d : null;
    }

    /** @return array{ok:bool,error:?string} */
    public static function sendCode(string $number, string $code): array
    {
        if (!self::enabled()) {
            return ['ok' => false, 'error' => 'WhatsApp is not set up on this site.'];
        }
        $components = [['type' => 'body', 'parameters' => [['type' => 'text', 'text' => $code]]]];
        if (self::cfg('WHATSAPP_COPY_BUTTON', '1') !== '0') {
            $components[] = ['type' => 'button', 'sub_type' => 'url', 'index' => '0', 'parameters' => [['type' => 'text', 'text' => $code]]];
        }
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $number,
            'type' => 'template',
            'template' => ['name' => self::cfg('WHATSAPP_TEMPLATE', 'verification_code'), 'language' => ['code' => self::cfg('WHATSAPP_LANGUAGE', 'en')], 'components' => $components],
        ];
        $ch = curl_init(self::API . rawurlencode(self::cfg('WHATSAPP_PHONE_ID')) . '/messages');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . self::cfg('WHATSAPP_TOKEN'), 'Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $raw = curl_exec($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $curlErr = curl_errno($ch);
        curl_close($ch);
        if ($raw === false || $curlErr !== 0) {
            Log::error('WhatsApp send failed (network error ' . $curlErr . ')');
            return ['ok' => false, 'error' => 'Could not reach WhatsApp.'];
        }
        $json = json_decode((string) $raw, true);
        if ($http >= 200 && $http < 300 && !empty($json['messages'][0]['id'])) {
            return ['ok' => true, 'error' => null];
        }
        $msg = is_array($json) ? (string) ($json['error']['message'] ?? '') : '';
        $codeNo = is_array($json) ? (int) ($json['error']['code'] ?? 0) : 0;
        Log::error('WhatsApp send refused (HTTP ' . $http . ', code ' . $codeNo . ')');
        // the reason is shown to administrators only; it never contains the number or the code
        return ['ok' => false, 'error' => mb_substr($msg !== '' ? $msg : 'WhatsApp refused the message (HTTP ' . $http . ').', 0, 190)];
    }
}
