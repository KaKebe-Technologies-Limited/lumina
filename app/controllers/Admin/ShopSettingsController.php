<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\Settings;
use App\Core\ValidationException;
use App\Services\Shop\FlutterwaveClient;
use App\Services\Shop\Money;
use App\Services\Shop\PaymentMethods;
use App\Services\Shop\PaymentService;
use App\Services\Shop\SecretStore;
use App\Services\Shop\ShopSettings;

/**
 * ADMIN SHOP: shop / delivery settings and the PAYMENT panel (methods, labels, numbers, bank details, Flutterwave keys, mode, test connection, setup checklist,
 * last 20 gateway events). Ability shop.settings (holds the gateway secrets, admin only by default). OWNER: PAYMENTS agent. Contract: docs/SHOP.md.
 *
 *   GET/POST admin/shop/settings               open / closed, e-mail recipients, pickup + delivery, stock holds, policy texts
 *   GET/POST admin/shop/payments               methods (on/off, name, instructions, position), mobile-money numbers, bank details, Flutterwave, currency
 *   POST     admin/shop/payments/test-connection   JSON (or flash + redirect without JavaScript): FlutterwaveClient::testConnection()
 *   POST     admin/shop/payments/clear-secret      forget a stored secret (name = flw_secret_key | flw_secret_hash)
 *
 * SECRETS: the two secret fields are write-only. They are never rendered back, never put in the "old input" the framework flashes after a validation error
 * (failWith() strips them), never in the audit log (only the NAME of the secret and the word "replaced" / "removed") and never in a JSON answer.
 * Every method authorizes its ability (defence in depth next to the route middleware).
 */
final class ShopSettingsController extends AdminController
{
    /** Input names of the two secrets. Deliberately not the setting keys. */
    private const SECRET_INPUTS = ['flw_secret_key' => 'flw_secret_key_new', 'flw_secret_hash' => 'flw_secret_hash_new'];

    // ================================================================================================ shop settings

    public function settings(Request $request): Response
    {
        $this->authorize('shop.settings');
        return $this->render('admin/shop/settings/settings', [
            'defs'      => ShopSettings::DEFS,
            'readiness' => $this->readiness(),
            'defaults'  => [
                'terms'    => ShopSettings::registryText('policy.terms'),
                'returns'  => ShopSettings::registryText('policy.returns'),
                'delivery' => ShopSettings::registryText('policy.delivery'),
            ],
            'currency'  => ShopSettings::currency(),
            'orgAddress' => trim((string) Settings::get('address', '')),
        ], ['title' => 'Shop settings', 'nav' => 'shop_settings', 'breadcrumbs' => [['Shop', url('admin/shop')], ['Shop settings', null]]]);
    }

    public function saveSettings(Request $request): Response
    {
        $this->authorize('shop.settings');
        $back = 'admin/shop/settings';
        $errors = [];
        $new = [];

        foreach (['shop_open', 'shop_pickup_enabled', 'shop_delivery_enabled', 'shop_restock_on_cancel', 'shop_restock_on_refund', 'shop_auto_confirm_paid'] as $k) {
            $new[$k] = $request->post($k) ? '1' : '0';
        }
        $new['shop_closed_message'] = $this->text((string) $request->post('shop_closed_message', ''), 500, true);
        $new['shop_pickup_address'] = $this->text((string) $request->post('shop_pickup_address', ''), 300, true);
        $new['shop_pickup_hours'] = $this->text((string) $request->post('shop_pickup_hours', ''), 200, true);
        $new['shop_delivery_areas'] = $this->text((string) $request->post('shop_delivery_areas', ''), 500, true);
        foreach (['shop_terms_text' => 'terms', 'shop_returns_text' => 'returns', 'shop_delivery_text' => 'delivery'] as $k => $_) {
            $new[$k] = $this->text((string) $request->post($k, ''), 8000, true);
        }

        // order number prefix: 2-8 letters or digits
        $prefix = strtoupper(trim((string) $request->post('shop_order_prefix', '')));
        if (preg_match('/^[A-Z0-9]{2,8}$/', $prefix) !== 1) {
            $errors['shop_order_prefix'] = 'Use 2 to 8 letters or digits, for example LAF.';
        }
        $new['shop_order_prefix'] = $prefix;

        // e-mail recipients: every address must be valid, at most 10
        $rawEmails = trim((string) $request->post('shop_notify_emails', ''));
        $emails = [];
        foreach (preg_split('/[\s,;]+/', $rawEmails) ?: [] as $e) {
            $e = strtolower(trim($e));
            if ($e === '') {
                continue;
            }
            if (filter_var($e, FILTER_VALIDATE_EMAIL) === false || strlen($e) > 160) {
                $errors['shop_notify_emails'] = 'This does not look like an e-mail address: "' . mb_substr($this->text($e, 60), 0, 60) . '". Separate addresses with commas.';
                break;
            }
            if (!in_array($e, $emails, true)) {
                $emails[] = $e;
            }
        }
        if (count($emails) > 10) {
            $errors['shop_notify_emails'] = 'At most 10 addresses.';
        }
        $new['shop_notify_emails'] = implode(', ', $emails);

        // whole numbers with limits
        foreach ([
            'shop_max_qty' => [1, 99, 'a number from 1 to 99'], 'shop_hold_days' => [1, 60, 'a number of days from 1 to 60'],
            'shop_manual_hold_days' => [1, 60, 'a number of days from 1 to 60'], 'shop_gateway_hold_hours' => [1, 168, 'a number of hours from 1 to 168 (one week)'],
        ] as $k => [$min, $max, $hint]) {
            $raw = trim((string) $request->post($k, ''));
            if (preg_match('/^\d{1,4}$/', $raw) !== 1 || (int) $raw < $min || (int) $raw > $max) {
                $errors[$k] = 'Enter ' . $hint . '.';
                $new[$k] = $raw;
            } else {
                $new[$k] = (string) (int) $raw;
            }
        }

        // money (stored as a plain decimal; the shop currency decides the decimals)
        foreach (['shop_delivery_fee' => 'delivery fee', 'shop_free_delivery_over' => 'amount'] as $k => $what) {
            $raw = trim((string) $request->post($k, ''));
            $minor = $raw === '' ? 0 : Money::parse($raw);
            if ($minor === null || $minor < 0 || $minor > 99999999999) {
                $errors[$k] = 'Enter the ' . $what . ' as a number, for example 5000 (0 for none).';
                $new[$k] = $raw;
            } else {
                $new[$k] = Money::toDb(Money::roundTo($minor, ShopSettings::currency()));
            }
        }

        if ($new['shop_pickup_enabled'] === '0' && $new['shop_delivery_enabled'] === '0') {
            $errors['shop_pickup_enabled'] = 'Keep at least one way to get the goods switched on: collection or delivery.';
        }

        if ($errors !== []) {
            $this->failWith($errors, $back);
        }

        $changed = $this->changedKeys($new);
        Settings::setMany($new);
        if ($changed !== []) {
            $this->audit('shop.settings.update', 'setting', 'shop', 'Shop settings updated', ['fields' => $changed]);
        }
        if (in_array('shop_open', $changed, true)) {
            $this->audit($new['shop_open'] === '1' ? 'shop.open' : 'shop.close', 'setting', 'shop_open', $new['shop_open'] === '1' ? 'Shop opened for orders' : 'Shop closed');
        }
        Flash::success($changed === [] ? 'Nothing had changed.' : 'Shop settings saved.');
        if ($new['shop_open'] === '1' && PaymentMethods::available(true) === []) {
            Flash::warning('The shop is open, but no payment method is ready yet, so nobody can check out. Set one up in Payments.');
        }
        if ($new['shop_open'] === '1' && $this->sampleCount() > 0) {
            Flash::warning('The shop is open and still shows sample products (their names, prices and pictures are placeholders). Edit or delete them in Products.');
        }
        return $this->redirect($back);
    }

    // ================================================================================================ payments panel

    public function payments(Request $request): Response
    {
        $this->authorize('shop.settings');
        $secretInfo = [];
        foreach (SecretStore::NAMES as $n) {
            $secretInfo[$n] = SecretStore::status($n) + ['text' => SecretStore::describe($n)];
        }
        $keyMode = 'unknown';
        $secret = SecretStore::get('flw_secret_key');
        if ($secret !== null) {
            $keyMode = FlutterwaveClient::keyMode($secret);
        }
        $webhook = abs_url('shop/webhook/flutterwave');
        $host = strtolower((string) parse_url($webhook, PHP_URL_HOST));
        return $this->render('admin/shop/settings/payments', [
            'methods'     => PaymentMethods::all(),
            'secrets'     => $secretInfo,
            'secretInputs' => self::SECRET_INPUTS,
            'canEncrypt'  => SecretStore::available(),
            'mode'        => PaymentMethods::flwMode(),
            'modeFromEnv' => in_array(strtolower(ShopSettings::env('FLW_MODE')), ['test', 'live'], true),
            'publicKey'   => PaymentMethods::flwPublicKey(),
            'publicFromEnv' => ShopSettings::env('FLW_PUBLIC_KEY') !== '',
            'keyMode'     => $keyMode,
            'options'     => PaymentMethods::flwOptions(),
            'allOptions'  => ShopSettings::FLW_OPTIONS,
            'title'       => ShopSettings::str('shop_flw_title'),
            'siteName'    => (string) Settings::get('site_name', ''),
            'currency'    => ShopSettings::currency(),
            'webhookUrl'  => $webhook,
            'webhookLocal' => in_array($host, ['localhost', '127.0.0.1', '::1', '[::1]'], true) || str_ends_with($host, '.localhost') || str_ends_with($host, '.test'),
            'webhookInsecure' => !str_starts_with($webhook, 'https://'),
            'mock'        => FlutterwaveClient::usingDevBase(),
            'events'      => PaymentService::events(20),
            'shopOpen'    => ShopSettings::isOpen(),
        ], ['title' => 'Payments', 'nav' => 'shop_payments', 'breadcrumbs' => [['Shop', url('admin/shop')], ['Payments', null]]]);
    }

    public function savePayments(Request $request): Response
    {
        $this->authorize('shop.settings');
        $back = 'admin/shop/payments';
        $errors = [];
        $new = [];
        $warnings = [];

        // ---- the five methods: on/off, name, instructions, position
        $positions = [];
        foreach (PaymentMethods::KEYS as $i => $m) {
            $new["shop_pay_{$m}_enabled"] = $request->post("shop_pay_{$m}_enabled") ? '1' : '0';
            $new["shop_pay_{$m}_label"] = $this->text((string) $request->post("shop_pay_{$m}_label", ''), 60);
            $new["shop_pay_{$m}_instructions"] = $this->text((string) $request->post("shop_pay_{$m}_instructions", ''), 1500, true);
            $rawSort = trim((string) $request->post("shop_pay_{$m}_sort", ''));
            if ($rawSort === '' || preg_match('/^\d{1,3}$/', $rawSort) !== 1 || (int) $rawSort < 1) {
                $errors["shop_pay_{$m}_sort"] = 'Enter a position from 1 to 999 (1 is shown first).';
                $positions[$m] = [999, $i];
            } else {
                $positions[$m] = [(int) $rawSort, $i];
            }
        }
        if ($errors === []) {
            uasort($positions, static fn(array $a, array $b): int => $a <=> $b);
            $n = 10;
            foreach (array_keys($positions) as $m) {          // renumber 10, 20, 30 ... so the order is always clean
                $new["shop_pay_{$m}_sort"] = (string) $n;
                $n += 10;
            }
        }

        // ---- mobile money (manual)
        foreach (['mtn' => 'MTN', 'airtel' => 'Airtel'] as $net => $nm) {
            $new["shop_momo_{$net}_name"] = $this->text((string) $request->post("shop_momo_{$net}_name", ''), 80);
            $num = $this->text((string) $request->post("shop_momo_{$net}_number", ''), 30);
            if ($num !== '' && preg_match('/^\+?[0-9][0-9 ()\-]{6,24}$/', $num) !== 1) {
                $errors["shop_momo_{$net}_number"] = $nm . ' number: use digits only, for example 0771 234 567 or +256 771 234 567.';
            }
            $new["shop_momo_{$net}_number"] = $num;
            if ($num !== '' && $new["shop_momo_{$net}_name"] === '') {
                $warnings[] = $nm . ' number has no account name. Customers feel safer when they see the name the money goes to.';
            }
        }

        // ---- bank (manual)
        $new['shop_bank_name'] = $this->text((string) $request->post('shop_bank_name', ''), 80);
        $new['shop_bank_account_name'] = $this->text((string) $request->post('shop_bank_account_name', ''), 80);
        $new['shop_bank_branch'] = $this->text((string) $request->post('shop_bank_branch', ''), 80);
        $acct = $this->text((string) $request->post('shop_bank_account_number', ''), 40);
        if ($acct !== '' && preg_match('/^[A-Za-z0-9][A-Za-z0-9 \-\/]{2,39}$/', $acct) !== 1) {
            $errors['shop_bank_account_number'] = 'Use letters, digits, spaces and dashes only.';
        }
        $new['shop_bank_account_number'] = $acct;
        $swift = strtoupper(str_replace(' ', '', $this->text((string) $request->post('shop_bank_swift', ''), 20)));
        if ($swift !== '' && preg_match('/^[A-Z0-9]{8}([A-Z0-9]{3})?$/', $swift) !== 1) {
            $errors['shop_bank_swift'] = 'A SWIFT / BIC code has 8 or 11 letters and digits, for example SBICUGKX.';
        }
        $new['shop_bank_swift'] = $swift;

        // ---- Flutterwave (non secret part)
        $currency = strtoupper(trim((string) $request->post('shop_currency', ShopSettings::currency())));
        if (!Money::validCurrency($currency)) {
            $errors['shop_currency'] = 'Use a three-letter currency code, for example UGX.';
        }
        $new['shop_currency'] = $currency;

        $pub = trim((string) $request->post('shop_flw_public_key', ''));
        if (ShopSettings::env('FLW_PUBLIC_KEY') === '') {
            if ($pub !== '' && preg_match('/^[A-Za-z0-9_\-]{8,200}$/', $pub) !== 1) {
                $errors['shop_flw_public_key'] = 'The public key has only letters, digits, dashes and underscores. Copy it again from Flutterwave.';
            }
            $new['shop_flw_public_key'] = $pub;
        }
        $modeIn = strtolower(trim((string) $request->post('shop_flw_mode', 'test')));
        if (!in_array(strtolower(ShopSettings::env('FLW_MODE')), ['test', 'live'], true)) {
            if (!in_array($modeIn, ['test', 'live'], true)) {
                $errors['shop_flw_mode'] = 'Choose test or live.';
            }
            $new['shop_flw_mode'] = $modeIn === 'live' ? 'live' : 'test';
        }
        $opts = $request->post('shop_flw_options', []);
        $opts = is_array($opts) ? array_values(array_filter(array_keys(ShopSettings::FLW_OPTIONS), static fn(string $o): bool => in_array($o, array_map('strval', $opts), true))) : [];
        $new['shop_flw_options'] = implode(',', $opts);
        if ($opts === [] && $new['shop_pay_flutterwave_enabled'] === '1') {
            $errors['shop_flw_options'] = 'Choose at least one way to pay on the Flutterwave page: card or mobile money.';
        }
        $new['shop_flw_title'] = $this->text((string) $request->post('shop_flw_title', ''), 60);

        // ---- the two secrets (write-only): empty = keep what is stored
        $secretsIn = [];
        $cantEncrypt = false;
        foreach (self::SECRET_INPUTS as $name => $input) {
            $val = trim((string) $request->post($input, ''));
            if ($val === '') {
                continue;
            }
            if (SecretStore::fromEnv($name) !== '') {
                $warnings[] = 'The ' . ($name === 'flw_secret_key' ? 'secret key' : 'secret hash') . ' is set in the .env file, which wins. What you typed was not saved.';
                continue;
            }
            if (!SecretStore::available()) {
                $cantEncrypt = true;
                $errors[$input] = 'This server cannot encrypt secrets. Put it in the .env file instead (' . ($name === 'flw_secret_key' ? 'FLW_SECRET_KEY' : 'FLW_SECRET_HASH') . ').';
                continue;
            }
            if ($name === 'flw_secret_key') {
                if (preg_match('/^[A-Za-z0-9_\-]{12,200}$/', $val) !== 1) {
                    $errors[$input] = 'This does not look like a Flutterwave secret key. Copy it again from Flutterwave (Settings > API keys), without spaces.';
                    continue;
                }
            } else {
                if (preg_match('/^[\x21-\x7E]{8,120}$/', $val) !== 1) {
                    $errors[$input] = 'Use 8 to 120 letters, digits or symbols, with no spaces. A long random value is best.';
                    continue;
                }
                if (strlen($val) < 16) {
                    $warnings[] = 'The secret hash is short. A random value of 24 characters or more is safer (the "Make a random hash" button creates one).';
                }
            }
            $secretsIn[$name] = $val;
        }

        if ($errors !== []) {
            $this->failWith($errors, $back);
        }

        // ---- save
        $changed = $this->changedKeys($new);
        Settings::setMany($new);
        $secretChanges = [];
        foreach ($secretsIn as $name => $val) {
            if (SecretStore::put($name, $val)) {
                $secretChanges[] = $name . ' (replaced)';
            }
        }
        $details = ['fields' => array_merge($changed, $secretChanges)];
        if ($details['fields'] !== []) {
            $this->audit('shop.payments.update', 'setting', 'shop_payments', 'Shop payment settings updated', $details);
        }

        // ---- plain-language feedback
        Flash::success($details['fields'] === [] ? 'Nothing had changed.' : 'Payment settings saved.');
        foreach (array_unique($warnings) as $w) {
            Flash::warning($w);
        }
        foreach (PaymentMethods::all() as $m) {
            if ($m['enabled'] && !$m['ready']) {
                Flash::warning($m['label'] . ' is switched on but customers will NOT see it yet. ' . implode(' ', $m['problems']));
            }
        }
        $secret = SecretStore::get('flw_secret_key');
        $mode = PaymentMethods::flwMode();
        if ($secret !== null) {
            $km = FlutterwaveClient::keyMode($secret);
            if ($km !== 'unknown' && $km !== $mode) {
                Flash::warning('The saved secret key is a ' . strtoupper($km) . ' key but the mode is ' . strtoupper($mode) . '. Use a key of the same mode, or change the mode.');
            }
        }
        if (in_array('shop_flw_mode', $changed, true) && $mode === 'live') {
            Flash::warning('LIVE mode is on: real money will move. Make sure you have first placed a test order in TEST mode with Flutterwave\'s test cards, and that the live keys and the live webhook are saved here.');
        }
        if (in_array('shop_currency', $changed, true)) {
            Flash::warning('The currency changed to ' . $currency . '. Product prices are NOT converted: check every price in Products. Existing orders keep their own currency.');
        }
        if ($cantEncrypt) {
            Flash::error('Some secrets could not be saved because this server cannot encrypt them.');
        }
        return $this->redirect($back);
    }

    /** JSON for the "Test connection" button; a plain POST (no JavaScript) gets a flash message and a redirect. Never contains a key. */
    public function testConnection(Request $request): Response
    {
        $this->authorize('shop.settings');
        $r = FlutterwaveClient::testConnection();
        $out = [
            'ok'         => (bool) $r['ok'],
            'state'      => (string) $r['state'],
            'message'    => (string) $r['message'],
            'mode'       => (string) $r['mode'],
            'using_mock' => (bool) $r['using_mock'],
            'warning'    => '',
        ];
        $want = PaymentMethods::flwMode();
        if ($out['ok'] && in_array($out['mode'], ['test', 'live'], true) && $out['mode'] !== $want) {
            $out['warning'] = 'The saved key is a ' . strtoupper($out['mode']) . ' key but the mode is ' . strtoupper($want) . '.';
        }
        if ($out['ok'] && $out['using_mock']) {
            $out['warning'] = trim($out['warning'] . ' This answer came from the local development mock, not from Flutterwave.');
        }
        $this->audit('shop.payments.test_connection', 'setting', 'shop_payments', 'Payment connection tested (' . ($out['ok'] ? 'ok' : (string) $out['state']) . ')');
        if ($request->expectsJson()) {
            return $this->json($out, 200);
        }
        $text = $out['message'] . ($out['warning'] !== '' ? ' ' . $out['warning'] : '');
        $out['ok'] ? Flash::success($text) : Flash::error($text);
        return $this->redirect('admin/shop/payments');
    }

    public function clearSecret(Request $request): Response
    {
        $this->authorize('shop.settings');
        $name = (string) $request->post('name', '');
        if (!in_array($name, SecretStore::NAMES, true)) {
            Flash::error('Unknown secret.');
            return $this->redirect('admin/shop/payments');
        }
        $pretty = $name === 'flw_secret_key' ? 'secret key' : 'webhook secret hash';
        if (SecretStore::fromEnv($name) !== '') {
            Flash::warning('The ' . $pretty . ' comes from the .env file. Remove it there; it cannot be removed from this screen.');
            return $this->redirect('admin/shop/payments');
        }
        if (!SecretStore::status($name)['set']) {
            Flash::info('The ' . $pretty . ' was not set.');
            return $this->redirect('admin/shop/payments');
        }
        SecretStore::forget($name);
        $this->audit('shop.payments.clear_secret', 'setting', 'shop_payments', 'Payment secret removed', ['fields' => [$name . ' (removed)']]);
        Flash::success('The ' . $pretty . ' was removed.' . ($name === 'flw_secret_key' ? ' Online payments are now switched off until you add a key.' : ' Webhooks are refused until you set a hash.'));
        return $this->redirect('admin/shop/payments');
    }

    // ================================================================================================ helpers

    /** Plain text from a form: tags removed, control characters removed, trimmed, limited. $multiline keeps line breaks (CRLF becomes LF). */
    private function text(string $s, int $max, bool $multiline = false): string
    {
        $s = strip_tags($s);
        $s = str_replace(["\r\n", "\r"], "\n", $s);
        $s = (string) preg_replace($multiline ? '/[^\P{C}\n]+/u' : '/\p{C}+/u', ' ', $s);
        if ($multiline) {
            $s = (string) preg_replace("/[ \t]+\n/", "\n", $s);
            $s = (string) preg_replace("/\n{3,}/", "\n\n", $s);
        } else {
            $s = (string) preg_replace('/\s+/u', ' ', $s);
        }
        return mb_substr(trim($s), 0, $max);
    }

    /** Setting keys whose stored value differs from the new one (NAMES only: values never go to the audit log). @param array<string,string> $new @return list<string> */
    private function changedKeys(array $new): array
    {
        $out = [];
        foreach ($new as $k => $v) {
            $default = ShopSettings::DEFS[$k][0] ?? '';
            $cur = trim((string) Settings::get($k, $default));
            if ($cur !== trim($v)) {
                $out[] = $k;
            }
        }
        return $out;
    }

    /** Redirect back with errors and the typed values, WITHOUT the two secret inputs. */
    private function failWith(array $errors, string $redirectTo): never
    {
        $old = self::scrubOld($this->request->all());
        foreach (self::SECRET_INPUTS as $input) {
            unset($old[$input]);
        }
        throw new ValidationException($errors, $old, $redirectTo);
    }

    private function sampleCount(): int
    {
        try {
            return (int) Database::value("SELECT COUNT(*) FROM shop_products WHERE is_sample = 1 AND status = 'published'", [], 0);
        } catch (\Throwable) {
            return 0;
        }
    }

    /** "Before you open the shop": plain-language checks. @return list<array{ok:bool,text:string,link:?string}> */
    private function readiness(): array
    {
        $samples = $this->sampleCount();
        $methods = PaymentMethods::available(true);
        $pickup = ShopSettings::pickup();
        return [
            ['ok' => $samples === 0, 'text' => $samples === 0 ? 'No sample products are published.' : $samples . ' sample product' . ($samples === 1 ? ' is' : 's are') . ' still published. Their names, prices and pictures are placeholders: edit or delete them.', 'link' => 'admin/shop/products'],
            ['ok' => $methods !== [], 'text' => $methods === [] ? 'No payment method is ready: customers cannot check out.' : count($methods) . ' payment method' . (count($methods) === 1 ? ' is' : 's are') . ' ready (' . implode(', ', array_map(static fn(array $m): string => (string) $m['label'], $methods)) . ').', 'link' => 'admin/shop/payments'],
            ['ok' => !ShopSettings::pickupEnabled() || $pickup['address'] !== '', 'text' => 'Collection address ' . ($pickup['address'] !== '' ? 'is set.' : 'is empty: add it below, or in Site settings.'), 'link' => null],
            ['ok' => ShopSettings::notifyEmails() !== [], 'text' => ShopSettings::notifyEmails() !== [] ? 'Order e-mails go to ' . count(ShopSettings::notifyEmails()) . ' address' . (count(ShopSettings::notifyEmails()) === 1 ? '' : 'es') . '.' : 'No order e-mail recipient is set: new orders are e-mailed to the organisation\'s default notification address.', 'link' => null],
        ];
    }
}
