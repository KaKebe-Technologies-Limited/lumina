<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Web\DonateController;
use App\Core\AuditLog;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\Settings;
use App\Services\Academy\AcademyFeeMessages;
use App\Services\Academy\AcademyFees;
use App\Services\Payments\PaymentOptions;
use App\Services\Pesapal\PesapalPayments;

/**
 * Admin > LAF Academy > Fees & payments: ONE page for everything the Academy charges and says about money, writing the same settings the website and the learner portal read:
 *   fees (sign-up and certificate amounts, on/off, automatic approval), payment details (mobile money and bank, shown on the portal), and the wording of the messages learners get.
 * Routes: GET admin/academy/fees, POST admin/academy/fees/{section} (fees | details | messages). Needs academy.manage.
 */
final class AcademyFeesController extends AcademyAdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('academy.manage');
        $ways = [
            ['Mobile money online (Daraza)', AcademyFees::mobileAvailable('signup'), AcademyFees::mobileAvailable('cert'), 'admin/daraza/api', 'Learners pay from their phone and the payment is confirmed by itself.'],
            ['Card and online (Pesapal)', PesapalPayments::ready() && PaymentOptions::online(), PesapalPayments::ready() && PaymentOptions::online(), 'admin/pesapal', 'Cards and other online methods.'],
            ['Payment link (Daraza page)', \App\Services\Daraza::enabled('signup'), \App\Services\Daraza::enabled('cert'), 'admin/daraza', 'A button to your Daraza payment page; you confirm the money by hand.'],
            ['Bank and mobile-money details', AcademyFees::manual() !== null, AcademyFees::manual() !== null, null, 'The details you type below; learners pay by hand and report the transaction.'],
        ];
        $messages = [];
        foreach (AcademyFeeMessages::all() as $k => $m) {
            $messages[$k] = $m + ['text' => AcademyFeeMessages::isCustom($k) ? AcademyFeeMessages::template($k) : '', 'custom' => AcademyFeeMessages::isCustom($k), 'placeholders_help' => AcademyFeeMessages::placeholders($k)];
        }
        return $this->render('admin/academy/fees', [
            'ready' => AcademyFees::ready(),
            'signupOn' => AcademyFees::signupEnabled(),
            'signup' => ['amount' => $this->amountText('academy_signup_fee'), 'currency' => $this->currency('academy_signup_fee_currency'), 'display' => (AcademyFees::configured('signup')['display'] ?? '')],
            'cert' => ['amount' => $this->amountText('academy_cert_fee'), 'currency' => $this->currency('academy_cert_fee_currency'), 'display' => (AcademyFees::configured('cert')['display'] ?? '')],
            'autoApprove' => (string) Settings::get('academy_fee_auto_approve', '1') !== '0',
            'autoRelease' => (string) Settings::get('academy_cert_auto_release', '1') !== '0',
            'currencies' => AcademyFees::CURRENCIES,
            'mobile' => (string) Settings::get('academy_pay_mobile_money', ''),
            'bank' => (string) Settings::get('academy_pay_bank_details', ''),
            'manualOn' => PaymentOptions::manual(),
            'ways' => $ways,
            'messages' => $messages,
            'sample' => ['cert_pay' => AcademyFeeMessages::sample(), 'signup_pay' => ['how_to_pay' => AcademyFeeMessages::howToPay('signup', false) ?: 'You can pay by mobile money: 0772 000 000, or online on the waiting page.'] + AcademyFeeMessages::sample(), 'cert_portal' => AcademyFeeMessages::sample()],
            'waitingCerts' => \App\Services\Academy\CertificatePayReminders::count(),
            'unpaidLearners' => count(AcademyFees::unpaidLearners()),
        ], ['title' => 'Fees & payments', 'nav' => 'academy_fees', 'breadcrumbs' => [['LAF Academy', url('admin/academy')], ['Fees & payments', null]]]);
    }

    public function save(Request $request, string $section): Response
    {
        $this->authorize('academy.manage');
        $back = $this->redirect('admin/academy/fees#' . $section);
        if ($section === 'fees') {
            $save = [];
            foreach (['signup' => 'academy_signup_fee', 'cert' => 'academy_cert_fee'] as $kind => $key) {
                $raw = DonateController::parseAmount((string) $request->post($kind . '_amount', '0'));
                $cur = strtoupper(trim((string) $request->post($kind . '_currency', 'UGX')));
                if ($raw === null || ($raw !== '' && ((float) $raw < 0 || (float) $raw > 100000000))) {
                    Flash::error('Type the ' . ($kind === 'signup' ? 'sign-up' : 'certificate') . ' fee with digits only (for example 20000), or 0 for free.');
                    return $back;
                }
                if (!in_array($cur, AcademyFees::CURRENCIES, true)) {
                    Flash::error('Choose a currency from the list.');
                    return $back;
                }
                $save[$key] = $raw === '' ? '0' : $raw;
                $save[$key . '_currency'] = $cur;
            }
            $save['academy_signup_fee_enabled'] = $request->bool('signup_on') ? '1' : '0';
            $save['academy_fee_auto_approve'] = $request->bool('auto_approve') ? '1' : '0';
            $save['academy_cert_auto_release'] = $request->bool('auto_release') ? '1' : '0';
            Settings::setMany($save);
            AuditLog::record('academy.fees', 'settings', null, 'Academy fees set: sign-up ' . $save['academy_signup_fee'] . ' ' . $save['academy_signup_fee_currency'] . ($save['academy_signup_fee_enabled'] === '1' ? '' : ' (switched off)') . ', certificate ' . $save['academy_cert_fee'] . ' ' . $save['academy_cert_fee_currency']);
            Flash::success('The fees were saved. The website and the learner portal use them from now on.');
            return $back;
        }
        if ($section === 'details') {
            $mobile = trim((string) $request->post('mobile', ''));
            $bank = trim((string) $request->post('bank', ''));
            if (mb_strlen($mobile) > 1000 || mb_strlen($bank) > 1000) {
                Flash::error('Each box can hold at most 1000 characters.');
                return $back;
            }
            Settings::setMany(['academy_pay_mobile_money' => $mobile, 'academy_pay_bank_details' => $bank, 'pay_opt_manual' => $request->bool('manual_on') ? '1' : '0']);
            AuditLog::record('academy.fees', 'settings', null, 'Academy payment details saved (mobile money ' . ($mobile !== '' ? 'set' : 'empty') . ', bank ' . ($bank !== '' ? 'set' : 'empty') . ', shown to learners: ' . ($request->bool('manual_on') ? 'yes' : 'no') . ')');
            Flash::success(($mobile === '' && $bank === '') ? 'Saved. No details are set, so learners see none.' : ($request->bool('manual_on') ? 'The payment details were saved and learners see them when they pay.' : 'The details were saved, but they are switched off: learners do not see them until you tick "Show these details to learners".'));
            return $back;
        }
        if ($section === 'messages') {
            $save = [];
            foreach (AcademyFeeMessages::all() as $k => $m) {
                $text = $request->bool('reset_' . $k) ? '' : trim((string) $request->post('msg_' . $k, ''));
                if (($err = AcademyFeeMessages::check($text)) !== null) {
                    Flash::error($m['title'] . ': ' . $err);
                    return $back;
                }
                // the same as the built-in wording is saved as "not customised", so a later improvement of the default reaches this site
                $save[$m['setting']] = $text === trim($m['default']) ? '' : $text;
            }
            Settings::setMany($save);
            AuditLog::record('academy.fees', 'settings', null, 'Academy fee messages saved (' . implode(', ', array_keys(array_filter($save, static fn(string $v): bool => $v !== ''))) . ')');
            Flash::success('The messages were saved. Learners get this wording from now on.');
            return $back;
        }
        Flash::error('Unknown section.');
        return $this->redirect('admin/academy/fees');
    }

    private function amountText(string $key): string
    {
        $v = trim((string) Settings::get($key, '0'));
        return $v === '' ? '0' : (str_contains($v, '.') ? rtrim(rtrim($v, '0'), '.') : $v);
    }

    private function currency(string $key): string
    {
        $c = strtoupper((string) Settings::get($key, 'UGX'));
        return in_array($c, AcademyFees::CURRENCIES, true) ? $c : 'UGX';
    }
}
