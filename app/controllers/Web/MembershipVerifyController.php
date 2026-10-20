<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Response;
use App\Services\Membership\CardService;
use App\Services\Membership\MembershipService;

/**
 * The QR target: /membership/verify/{token}. OWNER: DASHBOARD agent (this file and app/views/web/membership/verify/*); the first version is the architect's reference.
 *
 * It answers ONE question: is this card genuine and current? The only data it may show is what CardService::verification() returns: the result (valid / expired /
 * suspended / not valid) and, for a card that exists, the member's NAME, membership TYPE, MEMBER NUMBER, member-since date and valid-until date. NEVER a photo, e-mail,
 * phone, address, date of birth, payment or application reference. An unknown, replaced or revoked card shows NOT VALID with no member data at all (HTTP 404),
 * so a stale or guessed link learns nothing. The token is 32 hex characters (128 bits), separate from the private-page token; lookups are rate limited per
 * address (only FAILED lookups count: 20 per 10 minutes; a genuine card is always verified); the page is noindex and never cached.
 */
final class MembershipVerifyController extends Controller
{
    public function show(Request $request, string $token): Response
    {
        // The lookup comes FIRST: a genuine card is always verified (a check-in desk or a venue shares one public address, and scans many cards). Only lookups that
        // FAIL (unknown or replaced tokens) are counted per address; 20 in 10 minutes and the visitor gets 429 instead of another NOT VALID.
        $result = CardService::verification($token);
        if ($result['state'] === 'invalid') {
            $bad = 'member-verify-bad:' . $this->request->ip();
            if (RateLimit::tooMany($bad, 20)) {
                throw new HttpException(429, 'Too many requests', ['Retry-After' => (string) RateLimit::retryAfter($bad)]);
            }
            RateLimit::hit($bad, 600);
        }
        $r = $this->renderCredentialPage('web/membership/verify/show', ['result' => $result, 'contact' => MembershipService::contactEmail()], [
            'status'    => $result['state'] === 'invalid' ? 404 : 200,
            'bodyClass' => 'is-membership-verify',
            'meta'      => ['robots' => 'noindex,nofollow', 'canonical' => '/membership', 'title' => strip_highlight((string) content('membership', 'verify.meta_title'))],
        ]);
        return $r->header('Cache-Control', 'private, no-store, max-age=0')->header('Referrer-Policy', 'no-referrer')->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * render(), not renderPage(): the QR target and the private page must work for ever, even when the owner has unpublished the marketing page "Membership"
     * (same rule as the Shop order pages, docs/SHOP.md section 8). The page record still supplies the SEO defaults and the editable wording.
     * @param array<string,mixed> $data @param array<string,mixed> $opts
     */
    private function renderCredentialPage(string $view, array $data, array $opts): Response
    {
        $opts['page'] = 'membership';
        $data += ['pageSlug' => 'membership'];
        return $this->render($view, $data, $opts);
    }
}
