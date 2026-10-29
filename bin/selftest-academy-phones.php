<?php
declare(strict_types=1);

/**
 * Academy: the phone code follows the learner's country, older accounts are repaired, and the WhatsApp group buttons. Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-academy-phones.php
 * Needs the local web server (the sign-up page and the Academy page are read over HTTP). Removes its own rows (zz-ph-*).
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
use App\Services\Academy\LearnerAuth;
use App\Services\Academy\LearnerPhones;
use App\Services\Web\Countries;

echo "-- academy phone codes and WhatsApp group --\n";

// ---- country -> dialling code -----------------------------------------------------------------------------------------------
$all = [];
foreach (Countries::byRegion() as $list) {
    foreach ($list as $c) {
        $all[] = $c;
    }
}
check('every one of the ' . count($all) . ' countries in the list has a dialling code', count(array_filter($all, static fn(string $c): bool => Countries::dial($c) === null)) === 0);
check('codes: Uganda 256, Nigeria 234, Kenya 254, United States 1, United Kingdom 44, South Africa 27, India 91', Countries::dial('Uganda') === '256' && Countries::dial('Nigeria') === '234' && Countries::dial('Kenya') === '254'
    && Countries::dial('United States') === '1' && Countries::dial('United Kingdom') === '44' && Countries::dial('South Africa') === '27' && Countries::dial('India') === '91');
check('codes: spelling and old free text are understood (case, spaces, USA, UK, DR Congo, Ivory Coast, Turkey)', Countries::dial(' uganda ') === '256' && Countries::dial('USA') === '1' && Countries::dial('uk') === '44'
    && Countries::dial('DR Congo') === '243' && Countries::dial('Ivory Coast') === '225' && Countries::dial('Turkey') === '90');
check('codes: an unknown country gives null, not a guess', Countries::dial('Atlantis') === null && Countries::dial('') === null);

// ---- the number rules ---------------------------------------------------------------------------------------------------------
$i = static fn(string $in, ?string $dial = null): ?string => LearnerPhones::international($in, $dial);
check('numbers: a local number with 0 takes the code of the country (Uganda, Nigeria)', $i('0772 123 456', '256') === '256772123456' && $i('0803 123 4567', '234') === '2348031234567');
check('numbers: +code and 00code numbers are kept as they are, whatever the country', $i('+254 711 222 333', '256') === '254711222333' && $i('00254711222333', '234') === '254711222333');
check('numbers: digits without the 0 get the code; digits that already start with it are kept', $i('803 123 4567', '234') === '2348031234567' && $i('2348031234567', '234') === '2348031234567');
check('numbers: North America (+1): ten digits get 1, eleven starting with 1 are kept', $i('202 555 0123', '1') === '12025550123' && $i('12025550123', '1') === '12025550123');
check('numbers: nonsense and too short or too long are refused', $i('abc', '256') === null && $i('12', '256') === null && $i('1234567890123456789', '256') === null && $i('', '256') === null);
check('numbers: with no country the site default (256) is used, never a wrong guess for a known country', $i('0772123456', null) === (LearnerPhones::fallbackDial() . '772123456'));

// ---- the sign-up check (LearnerAuth::contact) -----------------------------------------------------------------------------------
$c = LearnerAuth::contact('0803 123 4567', 'Nigeria', '0803 123 4567');
check('sign-up: a Nigerian choosing Nigeria is saved as +234..., not +256', $c['errors'] === [] && $c['phone'] === '+2348031234567' && $c['whatsapp'] === '2348031234567', json_encode($c));
$c = LearnerAuth::contact('+254 711 222 333', 'Kenya', '0711222333');
check('sign-up: a Kenyan: phone with + kept, WhatsApp local number gets +254', $c['errors'] === [] && $c['phone'] === '+254711222333' && $c['whatsapp'] === '254711222333', json_encode($c));
$c = LearnerAuth::contact('0772 123 456', 'Uganda', '+256 772 123 456');
check('sign-up: a Ugandan still works as before', $c['errors'] === [] && $c['phone'] === '+256772123456' && $c['whatsapp'] === '256772123456', json_encode($c));
$c = LearnerAuth::contact('', '', '');
check('sign-up: empty phone, country and WhatsApp are all asked for', isset($c['errors']['phone'], $c['errors']['country'], $c['errors']['whatsapp']));

// ---- older accounts ---------------------------------------------------------------------------------------------------------------
$clean = static function (): void {
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-ph-%@example.test'");
};
$clean();
$hash = \App\Core\Auth::hashPassword('Phones test pass 5');
$mk = static function (string $key, string $country, string $phone, string $wa) use ($hash): int {
    return Database::insert('academy_learners', ['name' => 'ZZ Ph ' . $key, 'email' => 'zz-ph-' . $key . '@example.test', 'password_hash' => $hash, 'email_verified_at' => date('Y-m-d H:i:s'),
        'phone' => $phone, 'country' => $country, 'whatsapp' => $wa]);
};
$ng = $mk('ng', 'Nigeria', '0803 111 2222', '2568031112222');          // what the old sign-up saved: the local WhatsApp number given +256
$ug = $mk('ug', 'Uganda', '0772 000 111', '256772000111');
$kdiaspora = $mk('ke', 'Kenya', '+254 711 000 222', '256772999888');      // a Kenyan resident with a real Ugandan WhatsApp number: must not be touched
$gh = $mk('gh', 'ghana', '24 123 4567', '233241234567');                 // lower-case country, digits without 0
$unk = $mk('unk', 'Atlantis', '0700 000 000', '256700000000');
$row = static fn(int $id): array => (array) Database::one('SELECT phone, whatsapp FROM academy_learners WHERE id = ?', [$id]);
$dry = LearnerPhones::repair(false, 'zz-ph-%@example.test');
check('repair: a preview counts the changes and changes nothing', $dry['phones'] >= 3 && $dry['whatsapp'] >= 1 && $row($ng)['phone'] === '0803 111 2222' && $row($ng)['whatsapp'] === '2568031112222');
check('repair: an unknown country is reported and left alone', isset($dry['unknown_country']['Atlantis']) && $row($unk)['phone'] === '0700 000 000');
$done = LearnerPhones::repair(true, 'zz-ph-%@example.test');
check('repair: the Nigerian phone and the wrongly prefixed WhatsApp number now carry +234', $row($ng)['phone'] === '+2348031112222' && $row($ng)['whatsapp'] === '2348031112222', json_encode($row($ng)));
check('repair: the Ugandan keeps +256 (phone gets its +, WhatsApp unchanged)', $row($ug)['phone'] === '+256772000111' && $row($ug)['whatsapp'] === '256772000111');
check('repair: a Kenyan with a real +256 WhatsApp number is NOT changed (phone already had +)', $row($kdiaspora)['phone'] === '+254 711 000 222' && $row($kdiaspora)['whatsapp'] === '256772999888');
check('repair: "ghana" and digits without 0 get +233', $row($gh)['phone'] === '+233241234567');
$again = LearnerPhones::repair(true, 'zz-ph-%@example.test');
check('repair: running it again changes nothing (idempotent)', $again['phones'] === 0 && $again['whatsapp'] === 0 && $row($ng)['phone'] === '+2348031112222');
$clean();

// ---- the sign-up page and the WhatsApp group ----------------------------------------------------------------------------------------
$base = 'http://localhost/' . basename(dirname(__DIR__));
$get = static function (string $path) use ($base): string {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20, CURLOPT_FOLLOWLOCATION => false]);
    $b = (string) curl_exec($ch);
    curl_close($ch);
    return $b;
};
$reg = $get('/academy/register');
check('sign-up page: the country is a list with the dialling codes, and the phone and WhatsApp boxes are marked for the code', str_contains($reg, '<select') && str_contains($reg, 'data-country-dial=') && str_contains($reg, '&quot;Nigeria&quot;:&quot;234&quot;')
    && substr_count($reg, 'data-dial-input') === 2 && str_contains($reg, 'Choose your country'));
$js = (string) file_get_contents(dirname(__DIR__) . '/assets/js/academy.js');
check('sign-up script: choosing a country fills the code into the marked boxes', str_contains($js, 'data-country-dial') && str_contains($js, 'data-dial-input'));

$groupBefore = Settings::get('academy_whatsapp_group_url', null);
$link = 'https://chat.whatsapp.com/I96TlewmJ3J0PpEecpwv8U?s=cl&p=a&iam=2';
Settings::set('academy_whatsapp_group_url', $link);
$home = $get('/academy');
$reg = $get('/academy/register');
check('group: the link is on the public Academy page and on the sign-up page, opening in a new tab safely', str_contains($home, 'chat.whatsapp.com/I96TlewmJ3J0PpEecpwv8U') && str_contains($reg, 'chat.whatsapp.com/I96TlewmJ3J0PpEecpwv8U')
    && str_contains($home, 'rel="noopener noreferrer"') && str_contains($home, 'Join our Academy WhatsApp group'));
check('group: the helper returns the link', LearnerAuth::whatsappGroup() === $link);
Settings::set('academy_whatsapp_group_url', 'https://evil.example.com/chat.whatsapp.com/abcdefghijk');
check('group: a link that is not a WhatsApp group invitation is never shown', LearnerAuth::whatsappGroup() === null && !str_contains($get('/academy'), 'evil.example.com'));
Settings::set('academy_whatsapp_group_url', 'http://chat.whatsapp.com/I96TlewmJ3J0PpEecpwv8U');
check('group: a plain http link is refused too', LearnerAuth::whatsappGroup() === null);
Settings::set('academy_whatsapp_group_url', '');
check('group: with the link emptied the buttons disappear', LearnerAuth::whatsappGroup() === null && !str_contains($get('/academy'), 'Join our Academy WhatsApp group'));
if ($groupBefore === null) {
    Settings::reset('academy_whatsapp_group_url');
} else {
    Settings::set('academy_whatsapp_group_url', (string) $groupBefore);
}

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL OK\n" : "\n{$fail} FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
