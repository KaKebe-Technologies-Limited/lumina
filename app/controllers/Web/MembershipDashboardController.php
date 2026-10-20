<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Response;
use App\Services\Membership\Card\CardData;
use App\Services\Membership\Card\CardPng;
use App\Services\Membership\Card\CardSettings;
use App\Services\Membership\CardService;
use App\Services\Membership\MemberFiles;
use App\Services\Membership\MembershipService;

/**
 * The member's PRIVATE PAGE: /membership/me/{token}. OWNER: DASHBOARD agent. Contract: docs/MEMBERSHIP_CARD.md.
 *
 * RULES (do not weaken): no login; the token (64 hex, 256 bits) IS the credential. Every response carries "Cache-Control: private, no-store", "Referrer-Policy:
 * no-referrer" and "X-Robots-Tag: noindex, nofollow", and the HTML has meta robots noindex,nofollow. A token that does not match a member counts as a failure per IP:
 * after 15 failures in 10 minutes the visitor gets 429. The page shows only the member's OWN data, minimal (name, e-mail, phone, location); other members' data and file
 * paths never appear; files are streamed through this controller (photo, proof, card). Uploads are validated and rate limited by MemberFiles (8 per hour per member,
 * 30 per hour per address), and logged by id only. Uploads are allowed only while MemberFiles::canUpload() says so (status pending or approved; the proof until verified).
 * A photo replaced after the card was issued does NOT change the card: it flags a re-issue for staff (card_needs_reissue).
 *
 * Messages after an upload: the layout prints flash messages above <main>, but the view prints them first (inside the page, in the live region #mb-status) and
 * every POST redirects to that anchor, so the person lands on the answer to what they just did.
 */
final class MembershipDashboardController extends Controller
{
    /** Anchor of the live region that holds the answer to the last action. */
    private const STATUS_ANCHOR = '#mb-status';

    /**
     * The member behind the token, or an HttpException. The lookup comes FIRST and a valid token always works (many people share one address: an office, a campus,
     * a mobile carrier; a few bad links from one of them must never lock the others out of their own page, same rule as the meeting links). Only lookups that FAIL are
     * counted per address, and a visitor who failed 15 times in 10 minutes gets 429 instead of another 404.
     */
    private function member(string $token): array
    {
        $m = MembershipService::memberByDashboardToken($token);
        if ($m !== null) {
            return $m;
        }
        $fail = 'member-me-bad:' . $this->request->ip();
        if (RateLimit::tooMany($fail, 15)) {
            throw new HttpException(429, 'Too many requests', ['Retry-After' => (string) RateLimit::retryAfter($fail)]);
        }
        RateLimit::hit($fail, 600);
        throw new HttpException(404, 'Not found');
    }

    /** Headers every response of this controller must carry. */
    private function noStore(Response $r): Response
    {
        return $r->header('Cache-Control', 'private, no-store, max-age=0')->header('Referrer-Policy', 'no-referrer')->header('X-Robots-Tag', 'noindex, nofollow');
    }

    private function path(string $token, string $suffix = ''): string
    {
        return 'membership/me/' . $token . $suffix;
    }

    /** First name for the greeting, reduced to letters, digits and ' . - (the name was typed by the visitor; same rule as the e-mails), else the editable fallback word. */
    private function firstName(array $m): string
    {
        $first = (string) preg_replace('~(?:https?://|www\.)\S*~iu', ' ', (string) $m['full_name']);
        $first = trim((string) preg_replace('/[^\p{L}\p{M}\p{N} \'.-]+/u', ' ', $first));
        $first = mb_substr(explode(' ', (string) preg_replace('/\s+/u', ' ', $first))[0] ?? '', 0, 40);
        return preg_match('/\p{L}/u', $first) === 1 ? $first : (string) content('membership', 'emails.name_fallback');
    }

    public function show(Request $request, string $token): Response
    {
        try {
            $m = $this->member($token);
        } catch (HttpException $e) {
            if ($e->status !== 404) {
                throw $e;
            }
            return $this->noStore($this->renderCredentialPage('web/membership/me/show', ['member' => null], [
                'status' => 404, 'bodyClass' => 'is-membership-me',
                'meta' => ['robots' => 'noindex,nofollow', 'canonical' => '/membership', 'title' => strip_highlight((string) content('membership', 'dashboard.invalid_title'))],
            ]));
        }
        $card = CardService::summary($m);
        if ($card['state'] === 'issued' && in_array((string) $m['status'], ['rejected', 'cancelled'], true)) {
            $card['state'] = 'revoked';                  // a rejected or cancelled membership keeps no downloadable card on the private page
        }
        $summary = MembershipService::paymentSummary($m);
        $expiry = (string) ($m['expiry_date'] ?? '');
        $renewal = null;
        if ($m['status'] === 'expired' && $expiry !== '') {
            $renewal = ['kind' => 'expired', 'date' => MembershipService::lastValidDay($expiry) ?? $expiry];
        } elseif ($m['status'] === 'approved' && $expiry !== '' && $expiry <= date('Y-m-d', strtotime('+30 days'))) {
            $renewal = ['kind' => $expiry <= date('Y-m-d') ? 'expired' : 'soon', 'date' => MembershipService::lastValidDay($expiry) ?? $expiry];
        }
        $hasProof = MemberFiles::hasProof($m);
        return $this->noStore($this->renderCredentialPage('web/membership/me/show', [
            'member'       => $m,
            'token'        => $token,
            'first'        => $this->firstName($m),
            'initials'     => CardData::initials((string) $m['full_name'], false),
            'card'         => $card,
            'summary'      => $summary,
            'fee'          => MembershipService::memberFeeLabel($m),
            'joining'      => MembershipService::joiningFeeLabel($m),
            'instructions' => MembershipService::paymentInstructions(),
            'contact'      => MembershipService::contactEmail(),
            'timeline'     => CardService::timeline($m),
            'state'        => CardService::dashboardState($m),
            'renewal'      => $renewal,
            'hasPhoto'     => MemberFiles::hasPhoto($m),
            'hasProof'     => $hasProof,
            'proofIsPdf'   => $hasProof && (string) ($m['proof_mime'] ?? '') === 'application/pdf',
            'canPhoto'     => MemberFiles::canUpload($m, 'photo'),
            'canProof'     => MemberFiles::canUpload($m, 'proof'),
            'photoRequired' => MemberFiles::photoRequired(),
            'hasPng'       => $card['state'] === 'issued' && CardPng::available(),
            // the live status pill as the card itself words it (Admin > Membership card): ACTIVE MEMBER, EXPIRED, SUSPENDED, NOT VALID
            'liveLabel'    => CardSettings::get('membership_card_status_' . (in_array($card['live'], ['active', 'expired', 'suspended', 'revoked'], true) ? $card['live'] : 'revoked')),
            'urls'         => [
                'page' => url($this->path($token)), 'photo' => url($this->path($token, '/photo')), 'proof' => url($this->path($token, '/proof')),
                'pdf' => url($this->path($token, '/card.pdf')), 'png' => url($this->path($token, '/card.png')), 'resend' => url($this->path($token, '/resend')),
                'photoPost' => url($this->path($token, '/photo')), 'proofPost' => url($this->path($token, '/proof')),
            ],
        ], ['bodyClass' => 'is-membership-me', 'meta' => ['robots' => 'noindex,nofollow', 'canonical' => '/membership', 'title' => strip_highlight((string) content('membership', 'dashboard.meta_title'))],
            'breadcrumbs' => [[HomeSupport::navLabel('membership', 'Membership'), url('membership')], [(string) content('membership', 'dashboard.breadcrumb'), null]]]));
    }

    public function uploadPhoto(Request $request, string $token): Response
    {
        $m = $this->member($token);
        $res = MemberFiles::storePhoto((int) $m['id'], $request->file('photo'), ['consent' => $request->string('photo_consent'), 'source' => 'dashboard', 'ip' => $request->ip()]);
        $res['ok'] ? Flash::success($res['message'] . ($m['card_status'] === 'issued' ? ' ' . (string) content('membership', 'dashboard.photo_reissue_note') : '')) : Flash::error($res['message']);
        return $this->noStore($this->redirect($this->path($token) . self::STATUS_ANCHOR));
    }

    public function uploadProof(Request $request, string $token): Response
    {
        $m = $this->member($token);
        $res = MemberFiles::storeProof((int) $m['id'], $request->file('proof'), ['source' => 'dashboard', 'ip' => $request->ip()]);
        $res['ok'] ? Flash::success($res['message']) : Flash::error($res['message']);
        return $this->noStore($this->redirect($this->path($token) . self::STATUS_ANCHOR));
    }

    /** The member's own photo (image). */
    public function photo(Request $request, string $token): Response
    {
        return $this->noStore(MemberFiles::stream($this->member($token), 'photo'));
    }

    /** The member's own proof of payment (pictures inline, PDFs as a download). */
    public function proof(Request $request, string $token): Response
    {
        return $this->noStore(MemberFiles::stream($this->member($token), 'proof', $request->string('download') === '1'));
    }

    public function cardPdf(Request $request, string $token): Response
    {
        $m = $this->member($token);
        $pdf = CardService::pdf($m);
        if ($pdf === null) {
            throw new HttpException(404, 'Not found');
        }
        $r = new Response($pdf, 200, [
            'Content-Type' => 'application/pdf', 'Content-Length' => (string) strlen($pdf), 'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition' => 'attachment; filename="' . CardService::filename($m) . '"',
        ]);
        return $this->noStore($r);
    }

    public function cardPng(Request $request, string $token): Response
    {
        $m = $this->member($token);
        $png = CardService::png($m);
        if ($png === null) {
            throw new HttpException(404, 'Not found');
        }
        return $this->noStore(new Response($png, 200, ['Content-Type' => 'image/png', 'Content-Length' => (string) strlen($png), 'X-Content-Type-Options' => 'nosniff']));
    }

    public function resend(Request $request, string $token): Response
    {
        $m = $this->member($token);
        $res = CardService::resend((int) $m['id'], ['by' => 'member']);
        if ($res['ok']) {
            Flash::success((string) content('membership', 'dashboard.card_resent'));
        } else {
            Flash::error($res['code'] === 'rate' ? (string) content('membership', 'dashboard.card_resend_limit') : (string) content('membership', 'dashboard.card_resend_failed'));
        }
        return $this->noStore($this->redirect($this->path($token) . self::STATUS_ANCHOR));
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
