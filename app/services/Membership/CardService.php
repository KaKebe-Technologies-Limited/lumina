<?php
declare(strict_types=1);

namespace App\Services\Membership;

use App\Core\AuditLog;
use App\Core\Database;
use App\Core\Log;
use App\Core\RateLimit;
use App\Core\Uploader;
use App\Models\Member;
use App\Services\Membership\Card\CardData;
use App\Services\Membership\Card\CardRenderer;
use App\Services\Membership\Card\CardSettings;

/**
 * The MEMBERSHIP CARD LIFECYCLE: issue, re-issue, revoke, e-mail, the automatic trigger rules, and the verification lookup behind the QR code.
 * Contract, state machine and examples: docs/MEMBERSHIP_CARD.md. OWNER: architect (frozen). Rendering is Card/CardRenderer; e-mails are MembershipMail.
 *
 * STATES (members.card_status): none -> issued -> revoked (and revoked -> issued again by a new issue). The card is a SNAPSHOT: issuing stores what it
 * says (members.card_snapshot, a private copy of the photo) and a fresh random verification token; the PDF / PNG are rendered from that snapshot with the
 * LIVE status (active / expired / suspended / revoked), so a download never differs from the card that was e-mailed, except for the status pill.
 * A re-issue (version + 1) takes the member's current data and a NEW token, so the verification link printed on the old card stops working.
 *
 * AUTOMATIC ISSUE (setting membership_card_trigger), evaluated by event() whenever something happens to a member:
 *   paid_approved (default)  issue when the member is APPROVED and the balance is cleared (paid, free or waived), whichever happens last
 *   approved                 issue as soon as the member is approved
 *   manual                   never automatically
 * A photo is required before issuing only when the setting membership_card_require_photo is on; otherwise the card shows the initials block like the sample.
 * Renewal: when a card exists and the member's expiry moved past the card's valid-until date, the card is re-issued (setting membership_card_reissue_renewal)
 * once the same trigger condition holds, and e-mailed.
 *
 * IDEMPOTENCE: issue() locks the member row (SELECT ... FOR UPDATE) and re-checks inside the lock, so two requests at the same moment produce ONE card; the card
 * e-mail is claimed with a compare-and-set on card_emailed_version, so one version is e-mailed once even if the issue is triggered twice; a failed send gives the
 * claim back so "Re-send" or the next event can try again. Everything is audited with ids and references only.
 */
final class CardService
{
    public const EVENTS = ['approved', 'payment', 'renewal', 'photo'];
    /** Why a card is not issued yet (codes of eligibility()['reasons']) and what staff read. */
    public const WAITING = [
        'erased'        => 'The personal data of this member was erased.',
        'not_approved'  => 'Waiting for the application to be approved.',
        'unpaid'        => 'Waiting for the payment (the balance must be paid or waived).',
        'photo_missing' => 'Waiting for the photo (the card settings require one before a card is issued).',
        'no_number'     => 'The member has no membership number yet.',
    ];

    // ------------------------------------------------------------------------------------------------ rules

    /** paid_approved | approved | manual */
    public static function trigger(): string
    {
        $t = CardSettings::get('membership_card_trigger');
        return in_array($t, ['paid_approved', 'approved', 'manual'], true) ? $t : 'paid_approved';
    }

    /**
     * May a card be issued to this member now? $force = a member of staff presses "Issue card now": the payment rule is skipped (they may issue before the money
     * arrives) but the member must still be approved and, when the setting says so, have a photo.
     * @param array<string,mixed> $member
     * @return array{eligible:bool,reasons:list<string>,cleared:bool}
     */
    public static function eligibility(array $member, bool $force = false): array
    {
        $reasons = [];
        if (!empty($member['anonymised_at'])) {
            $reasons[] = 'erased';
        }
        if ((string) ($member['status'] ?? '') !== 'approved') {
            $reasons[] = 'not_approved';
        }
        if (empty($member['membership_number']) && $reasons === []) {
            $reasons[] = 'no_number';
        }
        $summary = MembershipService::paymentSummary($member);
        $cleared = in_array($summary['status'], ['paid', 'free'], true);
        if (!$force && self::trigger() === 'paid_approved' && !$cleared) {
            $reasons[] = 'unpaid';
        }
        if (CardSettings::bool('membership_card_require_photo') && !MemberFiles::hasPhoto($member)) {
            $reasons[] = 'photo_missing';
        }
        return ['eligible' => $reasons === [], 'reasons' => $reasons, 'cleared' => $cleared];
    }

    /** Has the membership been extended beyond what the issued card shows? */
    public static function renewalDue(array $member): bool
    {
        $exp = (string) ($member['expiry_date'] ?? '');
        $until = (string) ($member['card_valid_until'] ?? '');
        return $exp !== '' && ($until === '' || $exp > $until);
    }

    // ------------------------------------------------------------------------------------------------ the trigger

    /**
     * Something happened to a member: 'approved' (status changed to approved), 'payment' (a payment row was recorded or changed), 'renewal' (a new term was
     * started), 'photo' (a photo was added). Issues or re-issues the card when the rules say so and e-mails it. NEVER throws (a card problem must not break
     * an approval or a payment); returns the issue result when something was issued, else null.
     * @param array{user_id?:?int} $opts @return array<string,mixed>|null
     */
    public static function event(int $memberId, string $event, array $opts = []): ?array
    {
        try {
            if (!in_array($event, self::EVENTS, true)) {
                return null;
            }
            $m = Member::find($memberId);
            if ($m === null || !empty($m['anonymised_at']) || (string) $m['status'] !== 'approved') {
                return null;
            }
            $userId = isset($opts['user_id']) ? (int) $opts['user_id'] : null;
            if ($m['card_status'] === 'issued') {
                if ($event !== 'photo' && self::trigger() !== 'manual' && CardSettings::bool('membership_card_reissue_renewal') && self::renewalDue($m)
                    && self::eligibility($m)['eligible']) {
                    // 'only_if_renewal_due': re-checked on the LOCKED row, so parallel events (a double-clicked payment form) make one new version, not one each
                    return self::reissue($memberId, ['user_id' => $userId, 'source' => 'renewal', 'reason' => 'renewal', 'only_if_renewal_due' => true]);
                }
                // the card was issued before the balance was cleared (trigger "approved" or a staff override) and still says "Pending" / "due": nothing automatic, staff are told
                if ($event === 'payment' && (int) $m['card_needs_reissue'] === 0) {
                    $snap = json_decode((string) ($m['card_snapshot'] ?? ''), true);
                    $then = is_array($snap) ? (string) ($snap['pay_state'] ?? '') : '';
                    $now = (string) (CardData::raw($m, ['verify_token' => '', 'valid_until' => $m['expiry_date'], 'issued' => date('Y-m-d'), 'status' => 'active'])['pay_state'] ?? '');
                    if ($then !== '' && $now !== '' && $then !== $now && in_array($now, ['paid', 'waived', 'free'], true)) {
                        Database::update('members', ['card_needs_reissue' => 1, 'card_reissue_reason' => 'payment'], 'id = ?', [$memberId]);
                        MembershipService::flush();
                    }
                }
                return null;
            }
            if ($m['card_status'] === 'revoked' || self::trigger() === 'manual' || !self::eligibility($m)['eligible']) {
                return null;
            }
            return self::issue($memberId, ['user_id' => $userId, 'source' => 'auto:' . $event]);
        } catch (\Throwable $e) {
            Log::error('Membership card trigger failed', ['member' => $memberId, 'event' => $event, 'error' => $e::class . ': ' . $e->getMessage()]);
            return null;
        }
    }

    // ------------------------------------------------------------------------------------------------ issue / re-issue / revoke

    /**
     * Issue the card of an approved member (idempotent: a member who already has an issued card is left alone, see reissue()). $opts: user_id, source
     * (auto:<event> | manual | renewal), force (staff override of the payment rule), send (default true: e-mail the card), reason (re-issue reason).
     * Errors (result 'error'): not_found, erased, not_approved, no_number, unpaid, photo_missing.
     * @return array{ok:bool,error:?string,message:string,member:?array,issued:bool,already:bool,emailed:?bool,version:int}
     */
    public static function issue(int $memberId, array $opts = []): array
    {
        return self::doIssue($memberId, $opts, false);
    }

    /**
     * Issue a NEW VERSION of the card from the member's current data (new verification token: the link on the previous card stops working). Same options and
     * errors as issue(). Used after a photo / name / expiry change, for a renewal, and to replace a revoked card.
     * @return array{ok:bool,error:?string,message:string,member:?array,issued:bool,already:bool,emailed:?bool,version:int}
     */
    public static function reissue(int $memberId, array $opts = []): array
    {
        return self::doIssue($memberId, $opts, true);
    }

    /** @return array{ok:bool,error:?string,message:string,member:?array,issued:bool,already:bool,emailed:?bool,version:int} */
    private static function doIssue(int $memberId, array $opts, bool $reissue): array
    {
        $fail = static fn(string $code, string $msg, ?array $m = null): array => ['ok' => false, 'error' => $code, 'message' => $msg, 'member' => $m, 'issued' => false, 'already' => false, 'emailed' => null, 'version' => (int) ($m['card_version'] ?? 0)];
        $force = !empty($opts['force']);
        $source = (string) ($opts['source'] ?? 'manual');
        $userId = isset($opts['user_id']) ? (int) $opts['user_id'] : null;
        $oldPhoto = null;
        $outcome = null;
        $err = null;

        Database::transaction(function () use ($memberId, $reissue, $force, $source, $userId, $opts, &$oldPhoto, &$outcome, &$err): void {
            $m = Database::one('SELECT * FROM members WHERE id = ? FOR UPDATE', [$memberId]);
            if ($m === null) {
                $err = ['not_found', 'Member not found.', null];
                return;
            }
            $already = $m['card_status'] === 'issued';
            if (($already && !$reissue) || ($reissue && $already && !empty($opts['only_if_renewal_due']) && !self::renewalDue($m))) {
                $outcome = ['member' => $m, 'issued' => false, 'already' => true, 'version' => (int) $m['card_version']];
                return;
            }
            $el = self::eligibility($m, $force);
            if (!$el['eligible']) {
                $code = $el['reasons'][0];
                $err = [$code, self::WAITING[$code] ?? 'The card cannot be issued yet.', $m];
                return;
            }
            $version = (int) $m['card_version'] + 1;
            $token = bin2hex(random_bytes(16));                                  // 128 bits, different from the dashboard token
            $issuedOn = date('Y-m-d');
            $raw = CardData::raw($m, ['verify_token' => $token, 'valid_until' => $m['expiry_date'], 'issued' => $issuedOn, 'status' => 'active']);
            $cardPhoto = !empty($m['photo_path']) ? Uploader::copyPrivate((string) $m['photo_path']) : null;
            $oldPhoto = !empty($m['card_photo_path']) ? (string) $m['card_photo_path'] : null;
            Database::update('members', [
                'card_status' => 'issued', 'card_version' => $version, 'card_issued_at' => date('Y-m-d H:i:s'), 'card_valid_until' => !empty($m['expiry_date']) ? $m['expiry_date'] : null,
                'card_verify_token' => $token, 'card_needs_reissue' => 0, 'card_reissue_reason' => null, 'card_revoked_at' => null,
                'card_snapshot' => json_encode(CardData::snapshotOf($raw), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'card_photo_path' => $cardPhoto,
            ], 'id = ?', [$memberId]);
            AuditLog::record($version === 1 ? 'member.card_issue' : 'member.card_reissue', 'member', $memberId, 'Membership card v' . $version . ' for ' . $m['application_ref'],
                ['ref' => $m['application_ref'], 'number' => $m['membership_number'], 'version' => $version, 'source' => $source, 'reason' => (string) ($opts['reason'] ?? ''), 'user_id' => $userId]);
            $outcome = ['member' => Member::find($memberId), 'issued' => true, 'already' => false, 'version' => $version];
        });
        if ($err !== null) {
            return $fail($err[0], $err[1], $err[2]);
        }
        if ($oldPhoto !== null) {
            Uploader::deletePrivate($oldPhoto);
        }
        MembershipService::dashboardToken($memberId);                           // the private page the e-mail links to exists from now on
        MembershipService::flush();
        $emailed = null;
        if ($outcome['issued'] && ($opts['send'] ?? true) !== false) {
            $emailed = self::deliver($memberId, (int) $outcome['version']);
        }
        $member = Member::find($memberId);
        $msg = $outcome['already'] ? 'This member already has a membership card.' : 'Membership card version ' . $outcome['version'] . ' issued'
            . ($emailed === true ? ' and e-mailed.' : ($emailed === false ? ', but the e-mail could not be sent: use "Re-send".' : '.'));
        return ['ok' => true, 'error' => null, 'message' => $msg, 'member' => $member, 'issued' => $outcome['issued'], 'already' => $outcome['already'], 'emailed' => $emailed, 'version' => (int) $outcome['version']];
    }

    /**
     * Revoke the card: the verification page then says NOT VALID for its QR code. The member keeps their membership (change the status for that); a new issue
     * replaces the card. @return array{ok:bool,message:string,member:?array}
     */
    public static function revoke(int $memberId, string $reason = '', ?int $userId = null): array
    {
        $m = Member::find($memberId);
        if ($m === null) {
            return ['ok' => false, 'message' => 'Member not found.', 'member' => null];
        }
        if ($m['card_status'] !== 'issued') {
            return ['ok' => false, 'message' => 'There is no issued card to revoke.', 'member' => $m];
        }
        Database::update('members', ['card_status' => 'revoked', 'card_revoked_at' => date('Y-m-d H:i:s'), 'card_needs_reissue' => 0, 'card_reissue_reason' => null], 'id = ?', [$memberId]);
        AuditLog::record('member.card_revoke', 'member', $memberId, 'Membership card revoked for ' . $m['application_ref'], ['ref' => $m['application_ref'], 'version' => (int) $m['card_version'], 'user_id' => $userId,
            'reason' => mb_substr(trim($reason), 0, 120)]);
        MembershipService::flush();
        return ['ok' => true, 'message' => 'The card was revoked: its QR code now shows "not valid".', 'member' => Member::find($memberId)];
    }

    // ------------------------------------------------------------------------------------------------ e-mail

    /**
     * E-mail the card of version $version once: the claim on card_emailed_version makes a second call for the same version do nothing (returns null). The PDF is
     * rendered now from the snapshot. A failed send releases the claim. @return bool|null true sent, false failed, null already e-mailed
     */
    private static function deliver(int $memberId, int $version): ?bool
    {
        $m = Member::find($memberId);
        if ($m === null || $m['card_status'] !== 'issued') {
            return false;
        }
        // Render FIRST, claim afterwards: a fatal error (memory, time) while drawing cannot be caught, and must not leave "e-mailed" recorded for a card that never left.
        $pdf = '';
        try {
            $pdf = (string) self::pdf($m);
        } catch (\Throwable $e) {
            Log::error('Membership card render failed', ['member' => $memberId, 'error' => $e::class]);
            return false;
        }
        if ($pdf === '') {
            return false;
        }
        $prevVersion = (int) $m['card_emailed_version'];
        $prevAt = $m['card_emailed_at'];
        $claimed = Database::execute(
            "UPDATE members SET card_emailed_version = ?, card_emailed_at = NOW() WHERE id = ? AND card_emailed_version < ? AND card_status = 'issued'",
            [$version, $memberId, $version]
        );
        if ($claimed !== 1) {
            return null;
        }
        $ok = false;
        try {
            $ok = MembershipMail::cardIssued(Member::find($memberId) ?? $m, $pdf);
        } catch (\Throwable $e) {
            Log::error('Membership card e-mail failed', ['member' => $memberId, 'error' => $e::class]);
        }
        if (!$ok) {
            Database::execute('UPDATE members SET card_emailed_version = ?, card_emailed_at = ? WHERE id = ? AND card_emailed_version = ?', [$prevVersion, $prevAt, $memberId, $version]);
        }
        return $ok;
    }

    /**
     * Send the issued card to the member's address again. Limits: a member (via their private page, by => 'member') 3 per day; staff 10 per hour per member.
     * @param array{by?:string,user_id?:?int} $opts @return array{ok:bool,code:?string,message:string}
     */
    public static function resend(int $memberId, array $opts = []): array
    {
        $m = Member::find($memberId);
        if ($m === null || $m['card_status'] !== 'issued' || !empty($m['anonymised_at'])) {
            return ['ok' => false, 'code' => 'none', 'message' => 'There is no issued card to send.'];
        }
        if (in_array((string) $m['status'], ['rejected', 'cancelled'], true)) {
            return ['ok' => false, 'code' => 'none', 'message' => 'There is no issued card to send.'];
        }
        $byMember = ($opts['by'] ?? 'staff') === 'member';
        $allowed = $byMember
            ? RateLimit::attempt('member-card-resend:' . $memberId, 3, 86400)
            : RateLimit::attempt('member-card-resend-staff:' . $memberId, 10, 3600);
        if (!$allowed) {
            return ['ok' => false, 'code' => 'rate', 'message' => 'The card was sent several times already. Please try again later.'];
        }
        $ok = false;
        try {
            $ok = MembershipMail::cardIssued($m, (string) self::pdf($m));
        } catch (\Throwable $e) {
            Log::error('Membership card re-send failed', ['member' => $memberId, 'error' => $e::class]);
        }
        if ($ok) {
            Database::update('members', ['card_emailed_at' => date('Y-m-d H:i:s'), 'card_emailed_version' => (int) $m['card_version']], 'id = ?', [$memberId]);
            AuditLog::record('member.card_resend', 'member', $memberId, 'Membership card e-mailed again to ' . $m['application_ref'], ['ref' => $m['application_ref'], 'version' => (int) $m['card_version'], 'by' => $byMember ? 'member' : 'staff', 'user_id' => $opts['user_id'] ?? null]);
        }
        return ['ok' => $ok, 'code' => $ok ? null : 'failed', 'message' => $ok ? 'The card was sent to the member\'s e-mail address.' : 'The e-mail could not be sent. Please try again later.'];
    }

    /**
     * The designer's "Send a test card to my e-mail": the SAMPLE card (watermarked, fictional) with the card e-mail. $override = unsaved designer values.
     * @param array<string,mixed> $override @return array{ok:bool,message:string}
     */
    public static function sendSample(string $toEmail, array $override = [], ?int $userId = null): array
    {
        if (!RateLimit::attempt('member-card-sample:' . ($userId ?? 0), 10, 3600)) {
            return ['ok' => false, 'message' => 'Too many test cards. Please try again in a few minutes.'];
        }
        $ok = MembershipMail::cardSample($toEmail, CardRenderer::samplePdf($override), $override);
        return ['ok' => $ok, 'message' => $ok ? 'A test card was sent to ' . $toEmail . '.' : 'The test e-mail could not be sent.'];
    }

    // ------------------------------------------------------------------------------------------------ rendering and files

    /** PDF bytes of the issued card (null when there is none). The status pill is live; everything else is the snapshot. */
    public static function pdf(array $member): ?string
    {
        if (!in_array($member['card_status'] ?? '', ['issued'], true) || !empty($member['anonymised_at']) || in_array((string) ($member['status'] ?? ''), ['rejected', 'cancelled'], true)) {
            return null;                                 // a rejected or cancelled membership has no card to download (the headshot on it would outlive the application)
        }
        return CardRenderer::issuedPdf($member);
    }

    /** PNG preview of the issued card (null when there is none or GD is missing). */
    public static function png(array $member): ?string
    {
        if (($member['card_status'] ?? '') !== 'issued' || !empty($member['anonymised_at']) || in_array((string) ($member['status'] ?? ''), ['rejected', 'cancelled'], true)) {
            return null;
        }
        return CardRenderer::issuedPng($member);
    }

    /** "Membership-card-LAF-IND-26-0001.pdf" */
    public static function filename(array $member, string $ext = 'pdf'): string
    {
        $n = preg_replace('/[^A-Za-z0-9-]+/', '', (string) ($member['membership_number'] ?? '')) ?: 'member';
        return 'Membership-card-' . $n . '.' . $ext;
    }

    /** Delete the card's own copy of the photo (erasure, hard delete). */
    public static function deleteArtefacts(array $member): int
    {
        return !empty($member['card_photo_path']) && Uploader::deletePrivate((string) $member['card_photo_path']) ? 1 : 0;
    }

    // ------------------------------------------------------------------------------------------------ views for the screens

    /**
     * Everything the admin member panel and the member's dashboard need to show about the card.
     * state: not_issued | issued | revoked. waiting = reasons why a not-yet-issued card is not issued (eligibility reasons).
     * @param array<string,mixed> $member
     * @return array{unprintable:bool,state:string,version:int,issued_at:?string,emailed_at:?string,emailed:bool,valid_until:?string,needs_reissue:bool,reissue_reason:?string,live:string,eligible:bool,waiting:list<string>,verify_url:string,trigger:string,filename:string,renewal_due:bool}
     */
    public static function summary(array $member): array
    {
        $cs = (string) ($member['card_status'] ?? 'none');
        $state = $cs === 'issued' ? 'issued' : ($cs === 'revoked' ? 'revoked' : 'not_issued');
        $el = self::eligibility($member);
        return [
            'state'          => $state,
            'version'        => (int) ($member['card_version'] ?? 0),
            'issued_at'      => $member['card_issued_at'] ?? null,
            'emailed_at'     => $member['card_emailed_at'] ?? null,
            'emailed'        => (int) ($member['card_emailed_version'] ?? 0) >= (int) ($member['card_version'] ?? 0) && (int) ($member['card_version'] ?? 0) > 0,
            'valid_until'    => $member['card_valid_until'] ?? null,
            'needs_reissue'  => (int) ($member['card_needs_reissue'] ?? 0) === 1,
            'reissue_reason' => $member['card_reissue_reason'] ?? null,
            'live'           => CardData::state($member, $cs),
            'eligible'       => $el['eligible'],
            'waiting'        => $state === 'not_issued' ? $el['reasons'] : [],
            'verify_url'     => $state === 'issued' && !empty($member['card_verify_token']) ? abs_url('membership/verify/' . $member['card_verify_token']) : '',
            'trigger'        => self::trigger(),
            'filename'       => self::filename($member),
            'renewal_due'    => $state === 'issued' && self::renewalDue($member),
            // letters the printed card cannot draw (it prints "?" for them); the other screens show the real name
            'unprintable'    => CardData::hasUnprintable((string) ($member['full_name'] ?? '')) || CardData::hasUnprintable((string) ($member['city'] ?? '')) || CardData::hasUnprintable((string) ($member['region'] ?? '')),
        ];
    }

    /**
     * The four-step progress of the member's private page: Received -> Payment -> Approved -> Card issued. Each step is done | current | todo; keys
     * received, payment, approved, card. A rejected or cancelled application has no current step.
     * @param array<string,mixed> $member @return array<string,string>
     */
    public static function timeline(array $member): array
    {
        $status = (string) $member['status'];
        $closed = in_array($status, ['rejected', 'cancelled'], true);
        $summary = MembershipService::paymentSummary($member);
        $paid = in_array($summary['status'], ['paid', 'free'], true);
        $approved = in_array($status, ['approved', 'suspended', 'expired'], true);
        $issued = ($member['card_status'] ?? 'none') === 'issued';
        $revoked = ($member['card_status'] ?? 'none') === 'revoked';
        $steps = ['received' => 'done', 'payment' => 'todo', 'approved' => 'todo', 'card' => 'todo'];
        if ($closed) {
            return $steps;
        }
        $steps['payment'] = $paid ? 'done' : 'current';
        $steps['approved'] = $approved ? 'done' : ($paid ? 'current' : 'todo');
        $steps['card'] = $issued ? 'done' : (($approved && $paid && !$revoked) ? 'current' : 'todo');
        if ($approved && !$paid) {
            $steps['approved'] = 'done';                        // approved first, payment still open: the payment step stays current
        }
        return $steps;
    }

    /**
     * Which status message the private page shows (key suffix of dashboard.state_*): pending, approved_unpaid, approved_photo, approved_ready, issued,
     * rejected, cancelled, suspended, expired, revoked.
     * @param array<string,mixed> $member
     */
    public static function dashboardState(array $member): string
    {
        $status = (string) $member['status'];
        if ($status !== 'approved') {
            return in_array($status, ['pending', 'rejected', 'cancelled', 'suspended', 'expired'], true) ? $status : 'pending';
        }
        if (($member['card_status'] ?? 'none') === 'issued') {
            return 'issued';
        }
        if (($member['card_status'] ?? 'none') === 'revoked') {
            return 'revoked';                           // nothing is on its way: staff must issue a new card
        }
        $el = self::eligibility($member);
        if (in_array('unpaid', $el['reasons'], true)) {
            return 'approved_unpaid';
        }
        if (in_array('photo_missing', $el['reasons'], true)) {
            return 'approved_photo';
        }
        return 'approved_ready';
    }

    // ------------------------------------------------------------------------------------------------ verification (the QR code)

    /** Token format of a verification link: 32 lower-case hex characters (128 bits). */
    public static function validToken(string $token): bool
    {
        return preg_match('/^[a-f0-9]{32}$/D', $token) === 1;
    }

    /**
     * What the public verification page may show for a token. NOTHING else ever leaves this method: no photo, e-mail, phone, address, date of birth, payment.
     * state: valid | expired | suspended | invalid. For `invalid` (unknown token, a replaced or revoked card, a member who is not approved, erased data) NO member data
     * is returned, so a stale or guessed link learns nothing. The dates are the member's LIVE term.
     * @return array{state:string,name?:string,type?:string,number?:string,since?:?string,valid_until?:?string,no_expiry?:bool}
     */
    public static function verification(string $token): array
    {
        if (!self::validToken($token)) {
            return ['state' => 'invalid'];
        }
        $m = Database::one('SELECT full_name, type_name, membership_number, start_date, expiry_date, status, card_status, anonymised_at FROM members WHERE card_verify_token = ?', [$token]);
        if ($m === null || $m['card_status'] !== 'issued' || !empty($m['anonymised_at'])) {
            return ['state' => 'invalid'];
        }
        $live = CardData::state($m, 'issued');
        if ($live === 'revoked') {
            return ['state' => 'invalid'];
        }
        return [
            'state'       => ['active' => 'valid', 'expired' => 'expired', 'suspended' => 'suspended'][$live] ?? 'invalid',
            'name'        => CardSettings::cleanLine((string) $m['full_name'], 150),          // the same cleaner as the card: no bidi override can reorder it
            'type'        => (string) $m['type_name'],
            'number'      => (string) $m['membership_number'],
            'since'       => $m['start_date'] ?: null,
            'valid_until' => MembershipService::lastValidDay($m['expiry_date'] ?: null),         // the last valid day
            'no_expiry'   => empty($m['expiry_date']),
        ];
    }
}
