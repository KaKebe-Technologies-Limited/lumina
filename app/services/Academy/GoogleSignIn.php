<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Env;
use App\Core\Log;

/**
 * "Continue with Google" for LAF Academy learners (OpenID Connect, authorization-code flow with PKCE, state and nonce). A learner who signs in with Google needs no
 * confirmation e-mail: Google has already verified the address, so the account is created (or the existing one confirmed) and signed in at once.
 *
 * Switched on only when the .env file holds the Google credentials (https://console.cloud.google.com > APIs & Services > Credentials > OAuth client ID, type "Web
 * application", authorised redirect URI = abs_url('academy/google/callback')):
 *
 *   GOOGLE_CLIENT_ID=....apps.googleusercontent.com
 *   GOOGLE_CLIENT_SECRET=....
 *
 * Without them the button is hidden everywhere. The ID token comes straight from Google's token endpoint over TLS in answer to our own request (with our secret and the
 * PKCE verifier), so it is trusted per the OpenID Connect rules; its issuer, audience, expiry, nonce and "email_verified" are still checked.
 */
final class GoogleSignIn
{
    private const AUTH = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN = 'https://oauth2.googleapis.com/token';

    /** Tests replace the call to Google: fn(string $url, array $form): array{status:int,body:string}. */
    public static ?\Closure $transport = null;

    /** @return array{id:string,secret:string} */
    public static function cfg(): array
    {
        return ['id' => trim((string) (Env::get('GOOGLE_CLIENT_ID', '') ?? '')), 'secret' => trim((string) (Env::get('GOOGLE_CLIENT_SECRET', '') ?? ''))];
    }

    public static function enabled(): bool
    {
        $c = self::cfg();
        return $c['id'] !== '' && $c['secret'] !== '';
    }

    /** The address Google sends the learner back to (must be listed in the Google console exactly like this). */
    public static function redirectUri(): string
    {
        return abs_url('academy/google/callback');
    }

    private static function b64url(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    /**
     * Start: the Google address to send the learner to, and the values to keep in the session until they come back.
     * @return array{url:string,session:array{state:string,nonce:string,verifier:string}}
     */
    public static function begin(?array $cfg = null): array
    {
        $c = $cfg ?? self::cfg();
        $state = bin2hex(random_bytes(20));
        $nonce = bin2hex(random_bytes(16));
        $verifier = self::b64url(random_bytes(48));
        $url = self::AUTH . '?' . http_build_query([
            'client_id' => $c['id'],
            'redirect_uri' => self::redirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'nonce' => $nonce,
            'code_challenge' => self::b64url(hash('sha256', $verifier, true)),
            'code_challenge_method' => 'S256',
            'prompt' => 'select_account',
        ], '', '&', PHP_QUERY_RFC3986);
        return ['url' => $url, 'session' => ['state' => $state, 'nonce' => $nonce, 'verifier' => $verifier]];
    }

    /**
     * The learner is back from Google: check everything and return the account to sign in.
     * @param array<string,mixed> $query    the callback's query (code, state, error)
     * @param array<string,mixed> $session  what begin() kept (state, nonce, verifier)
     * @return array{ok:bool,error:?string,learner:?array<string,mixed>,created:bool}
     */
    public static function finish(array $query, array $session, ?array $cfg = null): array
    {
        $c = $cfg ?? self::cfg();
        $fail = static fn(string $m): array => ['ok' => false, 'error' => $m, 'learner' => null, 'created' => false];
        if (($query['error'] ?? '') !== '') {
            return $fail(($query['error'] ?? '') === 'access_denied' ? 'You cancelled the Google sign-in. You can try again, or use your e-mail and password.' : 'Google could not sign you in. Please try again.');
        }
        $state = (string) ($session['state'] ?? '');
        if ($state === '' || !hash_equals($state, (string) ($query['state'] ?? '')) || (string) ($query['code'] ?? '') === '') {
            return $fail('The Google sign-in expired or was not started here. Please try again.');
        }
        $res = self::exchange((string) $query['code'], (string) ($session['verifier'] ?? ''), $c);
        if ($res === null || empty($res['id_token'])) {
            return $fail('Google did not confirm the sign-in. Please try again in a moment.');
        }
        $claims = self::claims((string) $res['id_token']);
        $problem = $claims === null ? 'invalid' : self::checkClaims($claims, (string) ($session['nonce'] ?? ''), $c['id']);
        if ($claims === null || $problem !== null) {
            return $fail($problem === 'email' ? 'Google did not share a verified e-mail address for this account. Please use your e-mail and password.' : 'The Google answer could not be checked. Please try again.');
        }
        return self::account($claims);
    }

    /** Swap the one-time code for the ID token. @return array<string,mixed>|null */
    private static function exchange(string $code, string $verifier, array $c): ?array
    {
        $form = ['code' => $code, 'client_id' => $c['id'], 'client_secret' => $c['secret'], 'redirect_uri' => self::redirectUri(), 'grant_type' => 'authorization_code', 'code_verifier' => $verifier];
        if (self::$transport !== null) {
            $r = (self::$transport)(self::TOKEN, $form);
        } else {
            $ch = curl_init(self::TOKEN);
            curl_setopt_array($ch, [
                CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query($form), CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 8, CURLOPT_TIMEOUT => 20,
                CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_HTTPHEADER => ['Accept: application/json'],
            ]);
            $body = curl_exec($ch);
            $r = ['status' => (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE), 'body' => $body === false ? '' : (string) $body];
            curl_close($ch);
        }
        $json = json_decode((string) $r['body'], true);
        if ($r['status'] !== 200 || !is_array($json)) {
            Log::error('Google token exchange failed (HTTP ' . $r['status'] . ')');
            return null;
        }
        return $json;
    }

    /** The claims inside an ID token (header.payload.signature). @return array<string,mixed>|null */
    public static function claims(string $jwt): ?array
    {
        $p = explode('.', $jwt);
        if (count($p) !== 3) {
            return null;
        }
        $json = base64_decode(strtr($p[1], '-_', '+/') . str_repeat('=', (4 - strlen($p[1]) % 4) % 4), true);
        $a = $json === false ? null : json_decode($json, true);
        return is_array($a) ? $a : null;
    }

    /** null when fine, otherwise a short reason ("email" = no verified e-mail address). */
    public static function checkClaims(array $c, string $nonce, string $clientId): ?string
    {
        if (!in_array((string) ($c['iss'] ?? ''), ['https://accounts.google.com', 'accounts.google.com'], true)) {
            return 'iss';
        }
        $aud = $c['aud'] ?? '';
        if (!(is_array($aud) ? in_array($clientId, $aud, true) : (string) $aud === $clientId)) {
            return 'aud';
        }
        if ((int) ($c['exp'] ?? 0) < time() - 60) {
            return 'exp';
        }
        if ($nonce === '' || !hash_equals($nonce, (string) ($c['nonce'] ?? ''))) {
            return 'nonce';
        }
        $verified = $c['email_verified'] ?? false;
        if (($verified !== true && $verified !== 'true') || !filter_var((string) ($c['email'] ?? ''), FILTER_VALIDATE_EMAIL) || (string) ($c['sub'] ?? '') === '') {
            return 'email';
        }
        return null;
    }

    private static function subColumn(): bool
    {
        static $ok = null;
        if ($ok === null) {
            try {
                $ok = (int) Database::value("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'google_sub'", [], 0) === 1;
            } catch (\Throwable) {
                $ok = false;
            }
        }
        return $ok;
    }

    /**
     * Match the Google identity with a learner (by the stable Google id, then by the verified e-mail address), or create the learner. The address is confirmed on the
     * way (Google verified it).
     * @param array<string,mixed> $claims checked claims
     * @return array{ok:bool,error:?string,learner:?array<string,mixed>,created:bool}
     */
    public static function account(array $claims): array
    {
        $email = LearnerAuth::normaliseEmail((string) $claims['email']);
        $sub = mb_substr((string) $claims['sub'], 0, 40);
        $created = false;
        $l = self::subColumn() ? Database::one('SELECT * FROM academy_learners WHERE google_sub = ? LIMIT 1', [$sub]) : null;
        if ($l === null) {
            $l = Database::one('SELECT * FROM academy_learners WHERE email = ? LIMIT 1', [$email]);
            if ($l !== null && self::subColumn() && (string) ($l['google_sub'] ?? '') !== '' && (string) $l['google_sub'] !== $sub) {
                return ['ok' => false, 'error' => 'This e-mail address is already linked to a different Google account. Please use your e-mail and password.', 'learner' => null, 'created' => false];
            }
        }
        if ($l === null) {
            if (!LearnerAuth::registrationOpen()) {
                return ['ok' => false, 'error' => 'New sign-ups are closed at the moment.', 'learner' => null, 'created' => false];
            }
            $name = trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) ($claims['name'] ?? ''))));
            if (mb_strlen($name) < 2) {
                $name = ucfirst(explode('@', $email)[0]);
            }
            $row = [
                'name' => mb_substr($name, 0, 120), 'email' => $email, 'password_hash' => Auth::hashPassword(Auth::generatePassword(24)),
                'email_verified_at' => date('Y-m-d H:i:s'), 'is_active' => 1,
            ];
            if (self::subColumn()) {
                $row['google_sub'] = $sub;
            }
            $id = Database::insert('academy_learners', $row);
            $l = (array) Database::one('SELECT * FROM academy_learners WHERE id = ?', [$id]);
            $created = true;
            LearnerRegistry::assign($id);
            $l = (array) Database::one('SELECT * FROM academy_learners WHERE id = ?', [$id]);
            LearnerRegistry::announce($id, 'Google');
            AcademyMail::welcome($l);
        } else {
            if ((int) $l['is_active'] !== 1) {
                return ['ok' => false, 'error' => 'This account is switched off. Please contact the Academy team.', 'learner' => null, 'created' => false];
            }
            $upd = [];
            if ($l['email_verified_at'] === null) {
                $upd += ['email_verified_at' => date('Y-m-d H:i:s'), 'token_hash' => null, 'token_kind' => null, 'token_expires_at' => null];
            }
            if (self::subColumn() && (string) ($l['google_sub'] ?? '') === '') {
                $upd['google_sub'] = $sub;
            }
            if ($upd !== []) {
                Database::update('academy_learners', $upd, 'id = ?', [(int) $l['id']]);
                $l = (array) Database::one('SELECT * FROM academy_learners WHERE id = ?', [(int) $l['id']]);
            }
        }
        return ['ok' => true, 'error' => null, 'learner' => $l, 'created' => $created];
    }
}
