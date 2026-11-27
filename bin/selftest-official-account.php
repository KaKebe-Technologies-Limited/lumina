<?php
declare(strict_types=1);

/**
 * THE FOUNDATION'S OFFICIAL BANK ACCOUNT (App\Services\Payments\OfficialAccount, partials/bank-card.php, assets/js/bank-card.js): built-in values that work with no database rows,
 * an empty saved value never blanks them, an edited value wins, the site-wide "Bank and manual mobile-money details" switch hides the card, the card escapes and offers copy buttons.
 * Needs MySQL (settings table); no web server. Settings are put back.
 * Run alone: /c/xampp/php/php.exe bin/selftest-official-account.php
 */

use App\Core\Database;
use App\Core\Settings;
use App\Core\View;
use App\Services\Payments\OfficialAccount as OA;
use App\Services\Payments\PaymentOptions;

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

echo "-- official bank account --\n";
$keys = ['official_bank_account_name', 'official_bank_account_number', 'official_bank_name', 'official_bank_branch', 'official_bank_swift', 'official_bank_note', 'donate_bank_details', 'pay_opt_manual'];
$in = implode(',', array_fill(0, count($keys), '?'));
$snapshot = Database::all('SELECT * FROM settings WHERE setting_key IN (' . $in . ')', $keys);
$restore = static function () use ($keys, $in, $snapshot): void {
    Database::execute('DELETE FROM settings WHERE setting_key IN (' . $in . ')', $keys);
    foreach ($snapshot as $row) {
        Database::insert('settings', $row);
    }
    Settings::flush();
};
$clear = static function () use ($keys, $in): void {
    Database::execute('DELETE FROM settings WHERE setting_key IN (' . $in . ')', $keys);
    Settings::flush();
};
$set = static function (string $key, string $value): void {
    Settings::set($key, $value);
    Settings::flush();
};

try {
    // ---- the built-in account: no rows at all
    $clear();
    check('defaults: with no rows the account is the one on the foundation\'s bank card', OA::get() === ['name' => 'Lumina Africa Foundation', 'number' => '4090008000229', 'bank' => 'Pearl Bank', 'branch' => 'Gulu', 'swift' => ''], json_encode(OA::get()));
    check('defaults: the note asks to confirm the account name and share the proof of payment', str_contains(OA::note(), 'confirm the account name') && str_contains(OA::note(), 'proof of payment'));
    check('defaults: every field is the built-in value', OA::isBuiltIn());
    check('defaults: the schema holds the same values (the one place they are written)', Settings::definition('official_bank_account_number')['default'] === '4090008000229' && Settings::definition('official_bank_name')['default'] === 'Pearl Bank' && Settings::definition('official_bank_branch')['default'] === 'Gulu' && Settings::definition('official_bank_account_name')['default'] === 'Lumina Africa Foundation');

    // ---- an empty saved value (what a settings form with blank boxes stores) never blanks it
    foreach (['official_bank_account_name', 'official_bank_account_number', 'official_bank_name', 'official_bank_branch', 'official_bank_note'] as $k) {
        Settings::set($k, '');
    }
    Settings::set('official_bank_swift', '   ');
    Settings::flush();
    check('empty: every field explicitly saved as empty (or spaces) still gives the built-in account', OA::value('number') === '4090008000229' && OA::value('name') === 'Lumina Africa Foundation' && OA::value('bank') === 'Pearl Bank' && OA::value('branch') === 'Gulu' && OA::note() !== '');

    // ---- an edited value wins, field by field
    $set('official_bank_account_number', '1112 2233 3444');
    check('edit: a saved account number replaces only that field', OA::value('number') === '1112 2233 3444' && OA::value('bank') === 'Pearl Bank' && !OA::isBuiltIn());
    $rows = OA::rows();
    check('rows: Account Name, Account Number, Bank, Branch in that order (the order on the bank card); no SWIFT row while it is empty', array_column($rows, 'field') === ['name', 'number', 'bank', 'branch']);
    check('rows: the number is copied without spaces', $rows[1]['copy'] === '111222333444' && $rows[1]['value'] === '1112 2233 3444');
    $set('official_bank_swift', 'PRBLUGKA');
    $withSwift = OA::rows();
    check('rows: a SWIFT row appears only when one is set', end($withSwift)['field'] === 'swift');
    $clear();

    // ---- the switch in Admin > Payment options
    check('switch: visible while "Bank and manual mobile-money details" is on (the default)', OA::visible());
    PaymentOptions::save(['manual' => false]);
    check('switch: hidden everywhere while it is off', !OA::visible());
    PaymentOptions::save(['manual' => true]);
    check('switch: visible again when it is switched on', OA::visible());

    // ---- extra instructions (the older free-text box) are an addition, never a second copy of the account
    $set('donate_bank_details', 'Use your full name as the reference.');
    check('extra: free text is printed under the account', OA::extra() === 'Use your full name as the reference.');
    $set('donate_bank_details', "Bank: Pearl Bank\nAccount Name: Lumina Africa Foundation\nAccount Number: 4090 0080 00229");
    check('extra: a text that already contains the official account number is not repeated', OA::extra() === '');
    $set('donate_bank_details', 'Pearl Bank Gulu, a/c 409-0008-000229');
    check('extra: the same account written with dashes or dots is recognised too (compared by digits)', OA::extra() === '');
    $set('donate_bank_details', 'Old account 123-456-7890 (closed)');
    check('extra: a DIFFERENT account number typed there is still shown (it is the owner\'s text)', OA::extra() === 'Old account 123-456-7890 (closed)');
    $set('donate_bank_details', '');

    // ---- text for e-mails
    $t = OA::text('DON-7');
    check('text: the lines, the reference, the note and where to send the proof', str_contains($t, 'Account Name: Lumina Africa Foundation') && str_contains($t, 'Account Number: 4090008000229') && str_contains($t, 'Bank: Pearl Bank') && str_contains($t, 'Branch: Gulu') && str_contains($t, 'Payment reference: DON-7') && str_contains($t, 'confirm the account name') && str_contains($t, 'Send your proof of payment to'), $t);
    PaymentOptions::save(['manual' => false]);
    check('text: an e-mail a person at the foundation sends is not blocked by the on-screen switch', str_contains(OA::text(), '4090008000229'));
    PaymentOptions::save(['manual' => true]);
    check('reference: a donation is quoted as DON-<id> (the reference on receipts and Admin > Payments)', OA::donationReference(42) === 'DON-42');

    // ---- the card (partials/bank-card.php)
    $html = View::partial('partials/bank-card', ['reference' => 'Digital Inclusion DON-9', 'amount' => 'UGX 6,300,000.00', 'id' => 't1']);
    check('card: shows the four details of the bank card', str_contains($html, 'Lumina Africa Foundation') && str_contains($html, '4090008000229') && str_contains($html, 'Pearl Bank') && str_contains($html, 'Gulu'));
    check('card: a reference row and an amount row', str_contains($html, 'Payment reference') && str_contains($html, 'Digital Inclusion DON-9') && str_contains($html, 'Amount to send') && str_contains($html, 'UGX 6,300,000.00'));
    check('card: the note and the way to send the proof of payment', str_contains($html, 'confirm the account name before sending') && str_contains($html, 'Send the proof of payment to'));
    check('card: copy buttons are hidden until the script runs, have a name for screen readers, and the numbers are not translated by browsers', str_contains($html, 'data-bankcard-copy="4090008000229"') && preg_match('~<button class="bankcard__copy"[^>]*aria-label="Copy account number"[^>]* hidden>~', $html) === 1 && str_contains($html, 'translate="no"') && str_contains($html, 'data-bankcard-live'));
    check('card: a labelled region with its own unique ids', str_contains($html, 'id="bankcard-t1"') && str_contains($html, 'aria-labelledby="bankcard-t1-title"'));
    $own = View::partial('partials/bank-card', ['rows' => [['label' => 'Bank', 'value' => 'Zz <b>Bank</b>'], ['label' => 'Account number', 'value' => '99 88 77']], 'note' => 'Shop note', 'id' => 't2']);
    check('card: it can show a different account (the shop\'s own), escaped, and not the official one', str_contains($own, 'Zz &lt;b&gt;Bank&lt;/b&gt;') && !str_contains($own, '<b>Bank</b>') && !str_contains($own, '4090008000229') && str_contains($own, 'data-bankcard-copy="998877"') && str_contains($own, 'Shop note'));
    PaymentOptions::save(['manual' => false]);
    check('card: prints nothing while the switch is off (also for a different account)', trim(View::partial('partials/bank-card', [])) === '' && trim(View::partial('partials/bank-card', ['rows' => [['label' => 'Bank', 'value' => 'X']]])) === '');
    PaymentOptions::save(['manual' => true]);

    // ---- the script holds no account detail
    $js = (string) file_get_contents(dirname(__DIR__) . '/assets/js/bank-card.js');
    check('script: assets/js/bank-card.js carries no account detail (it copies what the button holds)', !str_contains($js, '4090008000229') && !str_contains($js, 'Pearl'));
} finally {
    $restore();
}

if (!empty($standalone)) {
    echo $fail === 0 ? "\nAll official-account checks passed.\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
