<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Flash;
use App\Core\Log;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Response;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Models\Donation;
use App\Services\Pesapal\PesapalPayments;
use App\Services\Shop\Money;

/**
 * GET /donate and POST /donate/pledge  - OWNER: B1 "web-core".
 * The page shows the online link (setting donate_url) and the bank / mobile-money / PayPal details from Settings > Donations
 * (each hidden while empty), the FAQ (table faqs, group "donate"; registry fallback) and an optional PLEDGE form.
 * A pledge is a record of intent (donations.status = "pledged", source = "pledge_form"): it never takes a payment.
 */
final class DonateController extends Controller
{
    use GatewayRedirects;

    private const CURRENCIES = ['UGX', 'USD', 'EUR', 'GBP'];
    private const METHODS = ['bank', 'mobile_money', 'paypal', 'other'];

    public function index(Request $request): Response
    {
        $hasMethod = trim((string) setting('donate_url', '')) !== ''
            || trim((string) setting('donate_bank_details', '')) !== ''
            || trim((string) setting('donate_mobile_money', '')) !== ''
            || trim((string) setting('donate_paypal_email', '')) !== '';
        $selectedCurrency = (string) old('currency', 'UGX');
        return $this->renderPage('donate', 'web/donate/index', [
            'faqs'             => HomeSupport::faqs('donate', 'donate', 'faq.items'),
            'hasMethod'        => $hasMethod,
            'currencies'       => array_combine(self::CURRENCIES, self::CURRENCIES),
            'selectedCurrency' => in_array($selectedCurrency, self::CURRENCIES, true) ? $selectedCurrency : 'UGX',
            'methods'          => $this->methodLabels(),
            'giveOnline'       => PesapalPayments::donationsEnabled(),
        ], [
            'breadcrumbs' => [[HomeSupport::navLabel('donate', 'Donate'), null]],
        ]);
    }

    public function pledge(Request $request): Response
    {
        $input = $request->all();
        // "1,500,000", "1 500 000" and "1500.50" are fine; mixed or ambiguous forms ("1.000,50", "50,00") are refused, never guessed
        $rawAmount = is_scalar($input['amount'] ?? null) ? (string) $input['amount'] : '';
        $parsed = self::parseAmount($rawAmount);
        $input['amount'] = $parsed ?? '';

        $rules = [
            'donor_name'   => 'required|max:150',
            'donor_email'  => 'required|email|max:190',
            'donor_phone'  => 'nullable|phone|max:40',
            'amount'       => 'required|numeric|between:1,1000000000',
            'currency'     => 'required|in:' . implode(',', self::CURRENCIES),
            'method'       => 'required|in:' . implode(',', self::METHODS),
            'message'      => 'nullable|max:2000',
        ];
        $messages = HomeSupport::formMessages('donate', array_keys($rules), 'pledge');
        $range = trim((string) content('donate', 'pledge.err_amount_range'));
        if ($range !== '') {
            $messages['amount.between'] = $range;
        }
        $validator = Validator::make($input, $rules, $messages);
        $errors = $validator->errors();
        if ($parsed === null && trim($rawAmount) !== '') {
            $errors['amount'] = trim((string) content('donate', 'pledge.err_amount_format')) ?: 'Please type the amount with digits only, using a point for cents.';
        }
        if ($errors !== []) {
            throw new ValidationException($errors, self::scrubOld($request->all()), 'donate#pledge');
        }
        $data = $validator->validated();

        $record = [
            'donor_name'   => (string) $data['donor_name'],
            'donor_email'  => (string) $data['donor_email'],
            'donor_phone'  => $this->nullable($data['donor_phone'] ?? null),
            'amount'       => round((float) $data['amount'], 2),
            'currency'     => (string) $data['currency'],
            'method'       => (string) $data['method'],
            'status'       => 'pledged',
            'source'       => 'pledge_form',
            'message'      => $this->nullable($data['message'] ?? null),
            'is_anonymous' => $request->bool('is_anonymous') ? 1 : 0,
            'ip'           => $request->ip(),
        ];
        Donation::create($record);

        $body = "New donation PLEDGE (a record of intent - no payment was taken)\n\n"
            . "Name: {$record['donor_name']}" . ($record['is_anonymous'] ? " (asked to stay anonymous)\n" : "\n")
            . "Email: {$record['donor_email']}\n"
            . 'Phone: ' . ($record['donor_phone'] ?? '-') . "\n"
            . 'Amount: ' . number_format((float) $record['amount'], 2) . " {$record['currency']}\n"
            . "Method: {$record['method']}\n\n"
            . "Message:\n" . ($record['message'] ?? '-') . "\n\n"
            . 'Manage it in the admin area: ' . abs_url('admin/donations') . "\n";
        if (!Mailer::notify('New donation pledge', $body, $record['donor_email'])) {
            Log::warning('Pledge notification e-mail could not be sent', ['email' => $record['donor_email']]);
        }

        Flash::success((string) content('donate', 'pledge.success'));
        return $this->redirect('donate');
    }

    /**
     * POST /donate/online: a donor who pays NOW through Pesapal (card, mobile money). The donation is recorded first (pledged, source "online"); it only becomes
     * "received" when PesapalPayments::settle() has confirmed the money with Pesapal. Nothing the browser says about the result is trusted.
     */
    public function online(Request $request): Response
    {
        if (!PesapalPayments::donationsEnabled()) {
            Flash::error('Online giving is not switched on yet. Please use one of the other ways to give.');
            return $this->redirect('donate');
        }
        $input = $request->all();
        $rawAmount = is_scalar($input['give_amount'] ?? null) ? (string) $input['give_amount'] : '';
        $parsed = self::parseAmount($rawAmount);
        $currency = is_scalar($input['give_currency'] ?? null) ? strtoupper((string) $input['give_currency']) : '';
        $min = $currency === 'UGX' ? 1000 : 1;
        $errors = [];
        $name = trim((string) ($input['give_name'] ?? ''));
        $email = trim((string) ($input['give_email'] ?? ''));
        $phone = trim((string) ($input['give_phone'] ?? ''));
        if ($name === '' || mb_strlen($name) > 150) {
            $errors['give_name'] = 'Please type your name.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
            $errors['give_email'] = 'Please type a valid e-mail address (your receipt goes there).';
        }
        if ($phone !== '' && preg_match('/^[0-9+()\s.-]{6,40}$/', $phone) !== 1) {
            $errors['give_phone'] = 'Please type a valid phone number or leave it empty.';
        }
        if (!in_array($currency, self::CURRENCIES, true)) {
            $errors['give_currency'] = 'Please choose a currency.';
        }
        if ($parsed === null || $parsed === '' || (float) $parsed < $min || (float) $parsed > 100000000) {
            $errors['give_amount'] = $parsed === null || $parsed === '' ? 'Please type the amount with digits only.' : 'The smallest online gift is ' . number_format($min) . ' ' . ($currency ?: 'UGX') . '.';
        }
        $minor = $errors === [] ? Money::parse($parsed) : null;
        if ($errors === [] && ($minor === null || $minor < 1)) {
            $errors['give_amount'] = 'Please type the amount with digits only.';
        }
        if ($errors !== []) {
            throw new ValidationException($errors, self::scrubOld($request->all()), 'give-online');
        }
        $donationId = Donation::create([
            'donor_name'   => $name,
            'donor_email'  => $email,
            'donor_phone'  => $phone === '' ? null : $phone,
            'amount'       => round((float) $parsed, 2),
            'currency'     => $currency,
            'method'       => 'pesapal',
            'status'       => 'pledged',
            'source'       => 'online',
            'is_anonymous' => $request->bool('give_anonymous') ? 1 : 0,
            'ip'           => $request->ip(),
        ]);
        $r = PesapalPayments::begin('donation', $donationId, (int) $minor, $currency, 'Donation to Lumina Africa Foundation', ['name' => $name, 'email' => $email, 'phone' => $phone]);
        if (!$r['ok']) {
            Flash::error('We could not open the secure payment page just now. Nothing was charged. Please try again in a minute, or use one of the other ways to give.');
            return $this->redirect('donate');
        }
        return $this->sendToGateway((string) $r['link']);
    }

    /**
     * Turn what a visitor typed into a plain decimal string, or null when it is unclear. Accepts "1500", "1500.50", "1,500,000",
     * "1 500 000" and "1,500.50"; refuses "1.000,50", "50,00", "2,5", "1.000" (thousands point or three decimals?) and more than two decimals.
     */
    public static function parseAmount(string $raw): ?string
    {
        $s = trim(str_replace(["\u{00A0}", "\u{202F}", ' '], '', $raw));
        if ($s === '') {
            return '';
        }
        if (str_contains($s, ',') && str_contains($s, '.')) {
            return preg_match('/^\d{1,3}(,\d{3})+\.\d{1,2}$/', $s) ? str_replace(',', '', $s) : null;
        }
        if (str_contains($s, ',')) {
            return preg_match('/^\d{1,3}(,\d{3})+$/', $s) ? str_replace(',', '', $s) : null;
        }
        if (str_contains($s, '.')) {
            return preg_match('/^\d+\.\d{1,2}$/', $s) ? $s : null;
        }
        return preg_match('/^\d+$/', $s) ? $s : null;
    }

    /** @return array<string,string> method => editable label */
    private function methodLabels(): array
    {
        return [
            'bank'         => (string) content('donate', 'pledge.method_bank'),
            'mobile_money' => (string) content('donate', 'pledge.method_mobile_money'),
            'paypal'       => (string) content('donate', 'pledge.method_paypal'),
            'other'        => (string) content('donate', 'pledge.method_other'),
        ];
    }

    private function nullable(mixed $v): ?string
    {
        $s = trim((string) $v);
        return $s === '' ? null : $s;
    }
}
