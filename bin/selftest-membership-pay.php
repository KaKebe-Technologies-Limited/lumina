<?php
declare(strict_types=1);

/**
 * Membership payments: apply first, then the payment details (Daraza button, mobile money, bank) are shown on the confirmation page, on the member's private page and in
 * the e-mail, and the applicant attaches the proof of payment. Included by bin/selftest.php or run alone:   /c/xampp/php/php.exe bin/selftest-membership-pay.php
 * Needs the local web server. Removes its rows (@selftest-pay.invalid, zz-pay-*) and puts the settings back.
 */

if (!function_exists('check')) {
    if (PHP_SAPI !== 'cli') {
        exit('CLI only.');
    }
    require dirname(__DIR__) . '/app/bootstrap.php';
    if (\App\Core\Config::get('app.env') !== 'local') {
        exit('Refusing to run outside APP_ENV=local.' . PHP_EOL);
    }
    $fail = 0;
    function check(string $name, bool $ok, string $extra = ''): void
    {
        global $fail;
        echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($ok || $extra === '' ? '' : "  -> {$extra}") . PHP_EOL;
        if (!$ok) {
            $fail++;
        }
    }
    $standalone = true;
}

use App\Core\Database;
use App\Core\Settings;
use App\Services\Daraza;
use App\Services\Membership\MembershipApplication;
use App\Services\Membership\MembershipMail;
use App\Services\Membership\MembershipService as MS;

echo "-- membership payments --\n";

$keys = ['membership_open', 'membership_payment_instructions', 'membership_mobile_money', 'membership_bank_details', 'donate_mobile_money', 'donate_bank_details',
    'daraza_url', 'daraza_membership_url', 'daraza_membership_on', 'daraza_label'];
$before = [];
foreach ($keys as $k) {
    $before[$k] = Settings::isCustomised($k) ? Settings::get($k) : null;
}
$clean = static function (): void {
    Database::execute("DELETE FROM member_payments WHERE member_id IN (SELECT id FROM members WHERE email LIKE '%@selftest-pay.invalid')");
    Database::execute("DELETE FROM members WHERE email LIKE '%@selftest-pay.invalid'");
    Database::execute("DELETE FROM membership_types WHERE slug LIKE 'zz-pay-%'");
};
$clean();
Settings::setMany(['membership_open' => '1', 'membership_payment_instructions' => '', 'membership_mobile_money' => '', 'membership_bank_details' => '', 'donate_mobile_money' => '', 'donate_bank_details' => '',
    'daraza_url' => '', 'daraza_membership_url' => '', 'daraza_membership_on' => '0', 'daraza_label' => '']);

$good = 'https://paylink.daraza.net/auths/pay/membership-link-1';
check('nothing set: no payment instructions and no Daraza option', MS::paymentInstructions() === '' && MS::darazaOption() === null);
Settings::setMany(['donate_mobile_money' => 'MTN 0771 000 111 (donations)', 'donate_bank_details' => 'Old Bank 123 (donations)']);
check('nothing set for membership: the Donations tab details are used, as before (no headings)', MS::paymentInstructions() === "MTN 0771 000 111 (donations)\n\nOld Bank 123 (donations)" && MS::paymentParts()['fallback'] === true);
Settings::setMany(['membership_mobile_money' => "MTN: 0772 222 333\nAirtel: 0752 444 555", 'membership_bank_details' => "Stanbic Bank\nLumina Africa Foundation\n9030000000001"]);
$txt = MS::paymentInstructions();
check('membership details set: mobile money and bank each under a heading, the donation details no longer used', str_contains($txt, "Mobile money:\nMTN: 0772 222 333") && str_contains($txt, "Bank transfer:\nStanbic Bank") && !str_contains($txt, 'donations') && MS::paymentParts()['fallback'] === false);
Settings::setMany(['daraza_url' => $good, 'daraza_membership_on' => '1']);
check('Daraza switched on: a button for the pages and a link line in the plain text (e-mail)', MS::darazaOption()['url'] === $good && str_contains(MS::paymentInstructions(), 'Pay with Daraza (pay online): ' . $good) && !str_contains(MS::paymentInstructions(false), $good));
check('Daraza is a payment method the applicant can say they will use', isset(MS::options('payment_methods')['daraza']) && MS::options('payment_methods')['daraza'] === 'Pay with Daraza' && isset(MS::options('payment_methods')['mobile_money']));
Settings::set('daraza_membership_on', '0');
check('Daraza switched off: no button, no extra method', MS::darazaOption() === null && !isset(MS::options('payment_methods')['daraza']) && !str_contains(MS::paymentInstructions(), 'daraza'));
Settings::set('daraza_membership_on', '1');

// ---- apply, then the details appear ---------------------------------------------------------------------------------------------------
$type = MS::saveType(null, ['name' => 'Selftest Pay Annual', 'slug' => 'zz-pay-annual', 'fee_amount' => '50,000', 'joining_fee' => '10000', 'currency' => 'ugx', 'period' => 'annual', 'is_active' => '1', 'sort_order' => 9101]);
$tid = (int) ($type['type']['id'] ?? 0);
$meta = ['ip' => '203.0.113.17', 'user_agent' => 'selftest'];
$app = MembershipApplication::process([
    'type_id' => $tid, 'full_name' => 'Selftest Payer', 'gender' => 'female', 'date_of_birth' => '1990-05-20', 'nationality' => 'Ugandan', 'email' => 'payer@selftest-pay.invalid', 'phone' => '+256700000091',
    'country' => 'Uganda', 'region' => 'Gulu', 'city' => 'Laroo', 'occupation_status' => 'employed', 'education_level' => 'diploma', 'availability' => 'weekends', 'heard_about' => 'website',
    'motivation' => 'I would like to volunteer and support young people.', 'emergency_name' => 'Em Contact', 'emergency_phone' => '+256700000092', 'emergency_relationship' => 'Sister',
    'payment_method' => 'daraza', 'consent_terms' => '1', 'consent_privacy' => '1', 'declaration' => '1',
], $meta);
check('an applicant can apply saying they will pay with Daraza', $app['ok'] === true && ($app['member']['payment_method'] ?? '') === 'daraza', json_encode($app['errors'] ?? []));
$member = (array) ($app['member'] ?? []);

$base = 'http://localhost/' . basename(dirname(__DIR__));
$get = static function (string $path) use ($base): array {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_FOLLOWLOCATION => false]);
    $b = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => $b];
};
$index = $get('/membership');
$form = $get('/membership/register');
check('before applying: the public Membership page and the application form do NOT show the payment details', $index['code'] === 200 && !str_contains($index['body'], '9030000000001') && !str_contains($index['body'], '0772 222 333') && !str_contains($index['body'], $good)
    && $form['code'] === 200 && !str_contains($form['body'], '9030000000001') && !str_contains($form['body'], $good));
$conf = $get(MS::confirmationPath((string) $member['application_ref']));
check('after applying: the confirmation page shows the mobile money, the bank details and the Pay with Daraza button (new tab)', $conf['code'] === 200 && str_contains($conf['body'], '0772 222 333') && str_contains($conf['body'], '0752 444 555') && str_contains($conf['body'], '9030000000001')
    && preg_match('/<a[^>]*href="' . preg_quote($good, '/') . '"[^>]*target="_blank"/', $conf['body']) === 1 && str_contains($conf['body'], 'Pay with Daraza'), (string) $conf['code']);
check('after applying: it says what to do after paying and links to the proof of payment', str_contains($conf['body'], 'After you pay') && str_contains($conf['body'], 'attach my proof of payment') && str_contains($conf['body'], '#proof'));
$hdr = (string) @file_get_contents($base . MS::confirmationPath((string) $member['application_ref']), false, stream_context_create(['http' => ['ignore_errors' => true]]));
$respHeaders = implode("
", $http_response_header ?? []);
check('the confirmation page is private: not cached, not indexed', stripos($respHeaders, 'no-store') !== false && str_contains($hdr, 'noindex'));
$mine = $get(MS::dashboardPath($member));
check('the private membership page shows the same payment options and has the proof of payment upload', $mine['code'] === 200 && str_contains($mine['body'], '9030000000001') && str_contains($mine['body'], $good) && str_contains($mine['body'], 'id="proof"')
    && str_contains($mine['body'], 'name="proof"') && str_contains($mine['body'], 'Attach proof of payment'), (string) $mine['code']);
[$subject, $body] = MembershipMail::render('ack', $member);
check('the confirmation e-mail carries the details and the Daraza link', str_contains($body, '0772 222 333') && str_contains($body, '9030000000001') && str_contains($body, $good), $body);

// ---- the admin side still works: staff see the proof and record the payment ----------------------------------------------------------------------
$pay = MS::recordPayment((int) $member['id'], ['purpose' => 'joining', 'amount' => '60000', 'method' => 'daraza', 'reference' => 'DZ-777', 'status' => 'received', 'paid_at' => date('Y-m-d')]);
check('staff can record the payment with the method Daraza', ($pay['ok'] ?? false) === true && ($pay['payment']['method'] ?? '') === 'daraza', json_encode($pay['errors'] ?? $pay));

// ---- clean up ---------------------------------------------------------------------------------------------------------------------------------------
$clean();
foreach ($before as $k => $v) {
    if ($v === null) {
        Settings::reset($k);
    } else {
        Settings::set($k, (string) $v);
    }
}

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL OK\n" : "\n{$fail} FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
