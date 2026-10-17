<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\AuditLog;
use App\Core\Controller;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Log;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Response;
use App\Models\Member;
use App\Models\MembershipType;
use App\Services\Membership\MembershipApplication;
use App\Services\Membership\MembershipMail;
use App\Services\Membership\MembershipService;

/**
 * PUBLIC membership: /membership, /membership/register (+POST), /membership/confirmation/{ref}, /membership/terms.
 * OWNER: WEB agent (file owner of this controller and app/views/web/membership/*). Contract: docs/MEMBERSHIP.md.
 *
 * The rules, messages, duplicate guard, fee snapshot, reference and subscriber opt-in are NOT re-implemented here:
 * MembershipApplication::process() does them, MembershipMail sends the e-mails. This class only prepares the data the views need and
 * turns the result into a response (error summary, generic duplicate message, signed confirmation redirect).
 * PRIVACY: nothing here logs submitted values; the audit entry carries the member id, the reference and the type id only.
 */
final class MembershipController extends Controller
{
    /** The Membership tab: hero, benefits, eligibility, types with their CURRENT fees, steps, payment details, FAQ, call to action. */
    public function index(Request $request): Response
    {
        $types = array_map(fn(array $t): array => $this->typeView($t), MembershipType::active());
        return $this->renderPage('membership', 'web/membership/index', [
            'types'        => $types,
            'allFull'      => $types !== [] && count(array_filter($types, static fn(array $t): bool => !$t['full'])) === 0,
            'open'         => MembershipService::isOpen(),
            'instructions' => MembershipService::paymentInstructions(),
            'faqs'         => HomeSupport::faqs('membership', 'membership', 'faq.items'),
        ], [
            'breadcrumbs' => [[HomeSupport::navLabel('membership', 'Membership'), null]],
        ]);
    }

    /** The registration form (or the "closed" notice). ?type=<id> preselects a membership type. */
    public function register(Request $request): Response
    {
        $open = MembershipService::isOpen();
        $types = [];
        $activeCount = 0;
        foreach (MembershipType::active() as $t) {
            $activeCount++;
            if (!MembershipService::capacity($t)['full']) {
                $types[] = $this->typeView($t);
            }
        }
        $ids = array_map(static fn(array $t): int => (int) $t['id'], $types);
        $oldType = old('type_id', $request->int('type'));
        $selected = is_scalar($oldType) ? (int) $oldType : 0;
        if (!in_array($selected, $ids, true)) {
            $selected = count($ids) === 1 ? $ids[0] : 0;
        }

        // age of the applicant as typed before a failed submission: decides whether the guardian block is open without JavaScript
        $oldDob = old('date_of_birth', '');
        $dob = is_scalar($oldDob) ? trim((string) $oldDob) : '';
        $age = MembershipService::ageOn($dob);
        $interests = old('interests', []);

        return $this->renderPage('membership', 'web/membership/register', [
            'open'         => $open,
            'types'        => $types,
            'allFull'      => $types === [] && $activeCount > 0,
            'selected'     => $selected,
            'collectId'    => MembershipService::collectsId(),
            'instructions' => MembershipService::paymentInstructions(),
            'age'          => $age,
            'minor'        => $age >= 0 && $age < MembershipService::ADULT_AGE,
            'interestsOld' => is_array($interests) ? array_values(array_map('strval', array_filter($interests, 'is_scalar'))) : [],
            'dobMin'       => date('Y-m-d', strtotime('-120 years') ?: time()),
            'dobMax'       => date('Y-m-d'),
            'options'      => [
                'gender' => MembershipService::options('gender'), 'occupation' => MembershipService::options('occupation'),
                'education' => MembershipService::options('education'), 'interests' => MembershipService::options('interests'),
                'availability' => MembershipService::options('availability'), 'heard' => MembershipService::options('heard'),
                'payment_methods' => MembershipService::options('payment_methods'),
            ],
        ], [
            'bodyClass'   => 'is-membership-register',
            'meta'        => ['robots' => 'noindex,follow', 'canonical' => '/membership/register', 'title' => strip_highlight((string) content('membership', 'register.title'))],
            'breadcrumbs' => [[HomeSupport::navLabel('membership', 'Membership'), url('membership')], [(string) content('membership', 'register.breadcrumb'), null]],
        ]);
    }

    public function submit(Request $request): Response
    {
        $res = MembershipApplication::process($request->all(), ['ip' => $request->ip(), 'user_agent' => $request->userAgent()]);

        if (!empty($res['closed'])) {
            Flash::error((string) content('membership', 'form.err_closed'));
            return $this->redirect('membership/register');
        }
        if (!$res['ok']) {
            // the messages are the editable form.err_* copy; the form re-renders with the error summary and the old input
            $this->fail((array) $res['errors'], 'membership/register');
        }
        if (!empty($res['duplicate'])) {
            // an open application with this e-mail exists: say nothing about it (no status, no reference, no e-mail is sent)
            Flash::success((string) content('membership', 'form.duplicate'));
            return $this->redirect('membership');
        }

        $member = (array) $res['member'];
        AuditLog::record('member.apply', 'member', (int) $member['id'], 'Membership application ' . $member['application_ref'], [
            'ref' => (string) $member['application_ref'], 'type_id' => (int) $member['type_id'],
        ]);
        // the application is already stored: a mail problem must never turn the confirmation into an error page (nothing personal is logged)
        try {
            MembershipMail::staffNotice($member);
            MembershipMail::applicationReceived($member);
        } catch (\Throwable $e) {
            Log::error('Membership e-mail failed', ['ref' => (string) $member['application_ref'], 'error' => $e::class]);
        }
        return Response::redirect(MembershipService::confirmationPath((string) $member['application_ref']));
    }

    /** Signed confirmation page: first name, reference, type, fee snapshot, payment instructions, next steps. Nothing else from the record. */
    public function confirmation(Request $request, string $ref): Response
    {
        if (!RateLimit::attempt('member-confirm:' . $request->ip(), 60, 600)) {
            throw new HttpException(429, 'Too many requests');
        }
        $member = MembershipService::verifyConfirmationToken($ref, $request->string('t')) ? Member::findBy('application_ref', $ref) : null;
        $opts = ['meta' => ['robots' => 'noindex,nofollow', 'canonical' => '/membership'], 'bodyClass' => 'is-membership-confirmation'];
        if ($member === null) {
            $opts['meta']['title'] = strip_highlight((string) content('membership', 'confirmation.invalid_title'));
            return $this->renderPage('membership', 'web/membership/confirmation', ['member' => null], $opts + ['status' => 404])
                ->header('Cache-Control', 'private, no-store');
        }
        $opts['meta']['title'] = strip_highlight((string) content('membership', 'confirmation.title'));
        return $this->renderPage('membership', 'web/membership/confirmation', [
            'member'       => $member,
            'fee'          => MembershipService::memberFeeLabel($member),
            'joining'      => MembershipService::joiningFeeLabel($member),
            'instructions' => MembershipService::paymentInstructions(),
        ], $opts)->header('Cache-Control', 'private, no-store');
    }

    public function terms(Request $request): Response
    {
        return $this->renderPage('membership-terms', 'web/membership/terms', [], [
            'bodyClass'   => 'is-legal',
            'breadcrumbs' => [[HomeSupport::navLabel('membership', 'Membership'), url('membership')], [HomeSupport::navLabel('membership-terms', 'Membership terms'), null]],
        ]);
    }

    /**
     * A type row plus what the views print: the fee split into currency, number and period (the words come from Pages > Membership),
     * the joining-fee line and whether the type has no places left. fee_state: tba (no fee set), free (0) or amount.
     * @param array<string,mixed> $t
     * @return array<string,mixed>
     */
    private function typeView(array $t): array
    {
        $cur = strtoupper((string) ($t['currency'] ?? MembershipService::defaultCurrency()));
        $period = (string) ($t['period'] ?? 'annual');
        $raw = $t['fee_amount'] ?? null;
        $state = $raw === null || $raw === '' ? 'tba' : ((float) $raw <= 0 ? 'free' : 'amount');
        $t['fee_state'] = $state;
        $t['fee_cur'] = $cur;
        $t['fee_num'] = $state === 'amount' ? MembershipService::formatAmount($raw, $cur) : '';
        $t['fee_text'] = $state === 'amount' ? '' : MembershipService::feeLabel($raw, $cur, $period, false);
        $t['fee_period'] = $state === 'amount' ? MembershipService::periodLabel($period) : '';
        $t['fee_label'] = MembershipService::typeFeeLabel($t);
        $t['joining_label'] = MembershipService::joiningFeeLabel($t, true);
        $t['full'] = MembershipService::capacity($t)['full'];
        return $t;
    }
}
