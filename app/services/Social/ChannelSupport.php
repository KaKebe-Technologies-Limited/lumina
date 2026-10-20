<?php
declare(strict_types=1);

namespace App\Services\Social;

/**
 * Helpers shared by the four API adapters written by the CHANNELS agent (Facebook, Instagram, X, LinkedIn): the "never throws" guards, the Graph API version, the
 * platform-specific refinement of an error answer on top of SocialHttp::toResult(), and LinkedIn's text escaping.
 *
 * Error policy (docs/SOCIAL.md sections 4 and 5), for every adapter:
 *   auth          the token was rejected / expired / revoked            -> SocialResult::auth   (the channel is marked "needs a new token")
 *   rate limit    HTTP 429 or the platform's own rate-limit code         -> transient with the platform's Retry-After when it sent one
 *   permission    the token lacks a permission / the app may not post    -> failed 'permission'  (retrying cannot help; the text says what to change)
 *   content       the text or picture was refused                        -> failed 'content_rejected' / 'duplicate' / 'image_rejected'
 *   network       refused / DNS / TLS BEFORE the request was sent        -> transient
 *   ambiguous     timeout or drop AFTER the request was sent, 502 / 504  -> review (no idempotency key exists: a person looks at the real account)
 * Nothing here ever puts a token, header, URL or full response in a message: texts come from SocialHttp::apiMessage() / redact() or are fixed words.
 */
final class ChannelSupport
{
    /** Graph error codes that mean "slow down". */
    private const GRAPH_RATE = [4, 17, 32, 341, 613, 80001, 80002, 80003, 80004, 80005, 80006, 80008, 80009, 80014];
    /** Graph error codes for a post Facebook / Instagram refused on content grounds. */
    private const GRAPH_CONTENT = [368, 1404006, 1404078, 1404102, 1404120, 2207051, 2207050];

    /** Run an adapter's post() body so that it NEVER throws: whatever goes wrong becomes a redacted "failed" result. */
    public static function guardPost(string $label, callable $fn): SocialResult
    {
        try {
            $res = $fn();
            return $res instanceof SocialResult ? $res : SocialResult::failed($label . ' returned no usable result.', 'internal');
        } catch (\Throwable $e) {
            \App\Core\Log::error('social.adapter.error', ['adapter' => $label, 'exception' => get_class($e)]);
            // the message of an exception is NOT shown: it could hold anything. The class name is enough for a person to start from.
            return SocialResult::failed($label . ' stopped because of an internal error (' . self::shortClass($e) . '). Nothing was reported as posted.', 'internal');
        }
    }

    /** Same for test(): returns the array shape of ChannelInterface::test(). @return array{ok:bool,state:string,message:string,detail?:string} */
    public static function guardTest(string $label, callable $fn): array
    {
        try {
            $res = $fn();
            if (is_array($res) && isset($res['ok'], $res['state'], $res['message'])) {
                return $res;
            }
            return ['ok' => false, 'state' => 'error', 'message' => $label . ' test returned no usable result.'];
        } catch (\Throwable $e) {
            \App\Core\Log::error('social.adapter.error', ['adapter' => $label . ' test', 'exception' => get_class($e)]);
            return ['ok' => false, 'state' => 'error', 'message' => $label . ' test stopped because of an internal error (' . self::shortClass($e) . ').'];
        }
    }

    /** Turn a failed SocialResult into the [state, message] pair of test(). @return array{ok:bool,state:string,message:string} */
    public static function testFailure(SocialResult $res): array
    {
        if ($res->state === SocialResult::AUTH) {
            return ['ok' => false, 'state' => 'auth', 'message' => SocialHttp::redact('The platform rejected the access token' . ($res->error !== '' ? ' (' . $res->error . ')' : '') . '. Create a new token and replace it here.')];
        }
        return ['ok' => false, 'state' => 'error', 'message' => $res->error !== '' ? $res->error : 'The service could not be reached from this server.'];
    }

    private static function shortClass(\Throwable $e): string
    {
        $c = get_class($e);
        $p = strrpos($c, '\\');
        return $p === false ? $c : substr($c, $p + 1);
    }

    // ------------------------------------------------------------------------------------------------ Graph API (Facebook, Instagram)

    /** "v23.0" from the setting, validated (a typo must not become a path). */
    public static function graphVersion(): string
    {
        $v = trim(SocialConfig::str('social_graph_version'));
        return preg_match('/^v\d{1,2}\.\d{1,2}$/', $v) === 1 ? $v : 'v23.0';
    }

    /** One Graph call. Token in the Authorization header (never in the URL). @param array<string,scalar> $query @param array<string,scalar> $form @return array<string,mixed> */
    public static function graph(string $channel, string $method, string $suffix, string $token, array $query = [], array $form = [], ?float $deadline = null): array
    {
        $opts = ['headers' => ['Authorization' => 'Bearer ' . $token], 'deadline' => $deadline];
        if ($query !== []) {
            $opts['query'] = $query;
        }
        if ($form !== []) {
            $opts['form'] = $form;
        }
        return SocialHttp::api($channel, $method, '/' . self::graphVersion() . '/' . ltrim($suffix, '/'), $opts);
    }

    /** The most useful redacted text of a Graph error: error_user_msg, else message. */
    public static function graphMessage(array $r): string
    {
        $e = is_array($r['json']['error'] ?? null) ? $r['json']['error'] : [];
        foreach (['error_user_msg', 'message'] as $k) {
            if (isset($e[$k]) && is_string($e[$k]) && trim($e[$k]) !== '') {
                return mb_substr(SocialHttp::redact(trim((string) preg_replace('/\s+/u', ' ', $e[$k]))), 0, 200);
            }
        }
        return SocialHttp::apiMessage($r);
    }

    /** A FAILED Graph answer -> the SocialResult (see the class comment). */
    public static function graphResult(string $channel, array $r): SocialResult
    {
        $label = $channel === 'instagram' ? 'Instagram' : 'Facebook';
        if ((string) ($r['kind'] ?? '') !== '' || !empty($r['ambiguous']) || SocialHttp::isAuthFailure($channel, $r)) {
            return SocialHttp::toResult($channel, $r);
        }
        $status = (int) ($r['status'] ?? 0);
        $e = is_array($r['json']['error'] ?? null) ? $r['json']['error'] : [];
        $code = (int) ($e['code'] ?? 0);
        $sub = (int) ($e['error_subcode'] ?? 0);
        $msg = self::graphMessage($r);
        $hint = $msg !== '' ? ' (' . $msg . ')' : '';
        if ($status === 429 || in_array($code, self::GRAPH_RATE, true)) {
            return SocialResult::transient($label . ' is limiting how fast we post (rate limit)' . $hint . '.', $r['retry_after'] ?? 900, 'rate_limited');
        }
        if ($code === 9007 || $sub === 2207027) {
            return SocialResult::transient('Instagram is still preparing the picture; it will be tried again shortly.', 30, 'not_ready');
        }
        if ($code === 2207003 || $sub === 2207003) {
            return SocialResult::transient('Instagram could not download the picture in time; it will be tried again.', 60, 'image_timeout');
        }
        if (in_array($code, [1, 2], true)) {
            return SocialResult::transient($label . ' reported a temporary problem' . $hint . '.', $r['retry_after'] ?? null, 'server_error');
        }
        if ($code === 506) {
            return SocialResult::failed('Facebook refused it as a duplicate of a recent post.', 'duplicate');
        }
        if ($code === 3 || $code === 10 || ($code >= 200 && $code <= 299)) {
            return SocialResult::failed($label . ' says this token or app may not post here' . $hint . '. Check that the token has ' . ($channel === 'instagram' ? 'instagram_content_publish and pages_show_list' : 'pages_manage_posts') . ', that you are an admin of the account, and that the Meta app is allowed to post publicly (Live mode / app review).', 'permission');
        }
        if (in_array($code, self::GRAPH_CONTENT, true) || in_array($sub, self::GRAPH_CONTENT, true)) {
            return SocialResult::failed($label . ' blocked this post' . $hint . '.', 'content_rejected');
        }
        if ($channel === 'instagram' && ($code === 9004 || in_array($sub, [2207052, 2207026, 2207004, 2207005, 2207006, 2207001, 2207008, 2207009, 2207010, 2207020, 2207023, 2207024], true))) {
            return SocialResult::failed('Instagram refused the picture' . $hint . '. It must be a public JPEG between 4:5 and 1.91:1 and reachable without a login.', 'image_rejected');
        }
        if ($code === 100 || $status === 400) {
            return SocialResult::failed($label . ' refused the post' . $hint . '.', 'rejected');
        }
        return SocialHttp::toResult($channel, $r);
    }

    // ------------------------------------------------------------------------------------------------ X

    /** A FAILED X answer -> the SocialResult. */
    public static function xResult(array $r): SocialResult
    {
        if ((string) ($r['kind'] ?? '') !== '' || !empty($r['ambiguous']) || SocialHttp::isAuthFailure('x', $r)) {
            return SocialHttp::toResult('x', $r);
        }
        $status = (int) ($r['status'] ?? 0);
        $msg = SocialHttp::apiMessage($r);
        $hint = $msg !== '' ? ' (' . $msg . ')' : '';
        $raw = strtolower(mb_substr((string) ($r['raw'] ?? ''), 0, 2000));
        if ($status === 402 || str_contains($raw, 'client-not-enrolled') || str_contains($raw, 'creditsdepleted') || str_contains($raw, 'credits')) {
            return SocialResult::failed('X says this app\'s API plan does not allow posting (or its credits are used up)' . $hint . '. Check your plan in the X developer portal.', 'plan');
        }
        if ($status === 403) {
            if (preg_match('/duplicate/i', $msg . ' ' . $raw) === 1) {
                return SocialResult::failed('X refused it as a duplicate of a recent post.', 'duplicate');
            }
            if (preg_match('/oauth1 app permissions|not configured with the appropriate|read-only|not permitted|does not have permission|permission/i', $msg . ' ' . $raw) === 1) {
                return SocialResult::failed('X says the app may not post' . $hint . '. Set the app permission to "Read and write", then generate NEW access tokens and paste them here.', 'permission');
            }
            return SocialResult::failed('X refused the request' . $hint . '. The account may be restricted or the app may lack permission.', 'permission');
        }
        if ($status === 400 || $status === 422) {
            return SocialResult::failed('X refused the text' . $hint . '.', 'content_rejected');
        }
        return SocialHttp::toResult('x', $r);
    }

    // ------------------------------------------------------------------------------------------------ LinkedIn

    /** A FAILED LinkedIn answer -> the SocialResult. */
    public static function linkedinResult(array $r): SocialResult
    {
        if ((string) ($r['kind'] ?? '') !== '' || !empty($r['ambiguous'])) {
            return SocialHttp::toResult('linkedin', $r);
        }
        $status = (int) ($r['status'] ?? 0);
        $code = is_array($r['json'] ?? null) ? (string) ($r['json']['code'] ?? '') : '';
        if (in_array($code, ['REVOKED_ACCESS_TOKEN', 'EXPIRED_ACCESS_TOKEN', 'INVALID_ACCESS_TOKEN'], true) || $status === 401) {
            $m = SocialHttp::apiMessage($r);
            return SocialResult::auth($m !== '' ? $m : 'The LinkedIn access token was rejected or has expired.', 'auth');
        }
        $msg = SocialHttp::apiMessage($r);
        $hint = $msg !== '' ? ' (' . $msg . ')' : '';
        if ($status === 426 || $code === 'NONEXISTENT_VERSION') {
            return SocialResult::failed('LinkedIn no longer accepts the API version "' . SocialConfig::str('social_li_version') . '"' . $hint . '. Enter a current version (YYYYMM) under Channels > LinkedIn API version.', 'version');
        }
        if ($status === 403) {
            return SocialResult::failed('LinkedIn says this token may not post as the organisation' . $hint . '. It needs w_organization_social (Community Management API) and the token\'s member must be an administrator of the Page.', 'permission');
        }
        if ($status === 422 || $status === 400) {
            if (preg_match('/duplicate/i', $msg) === 1) {
                return SocialResult::failed('LinkedIn refused it as a duplicate of a recent post.', 'duplicate');
            }
            return SocialResult::failed('LinkedIn refused the post' . $hint . '.', 'content_rejected');
        }
        return SocialHttp::toResult('linkedin', $r);
    }

    /**
     * LinkedIn's "little text" format reserves ( ) [ ] { } < > @ | ~ _ * # and the backslash: each is escaped with a backslash so the text is shown as typed.
     */
    public static function linkedinEscape(string $text): string
    {
        return (string) preg_replace('/([\\\\()\[\]{}<>@|~_*#])/u', '\\\\$1', $text);
    }

    /** True when a "YYYY-MM-DD" date lies before today (application time zone). */
    public static function datePassed(string $ymd): bool
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $ymd) !== 1) {
            return false;
        }
        return $ymd < (new \DateTimeImmutable('today'))->format('Y-m-d');
    }
}
