<?php
declare(strict_types=1);

/**
 * CORE UNITS: the small pure building blocks every flow relies on. No database writes, no web server, no mail.
 *   Money (every amount the site charges), Sanitizer (the only way HTML/links reach the page), Slug, Validator (every form).
 * Run alone: /c/xampp/php/php.exe bin/selftest-core-units.php        (or: php bin/test.php core-units)
 */

use App\Core\Sanitizer;
use App\Core\Slug;
use App\Core\Validator;
use App\Services\Shop\Money;

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

echo "-- core units: money --\n";
check('Money::parse reads plain and comma-grouped amounts into minor units (x100)', Money::parse('35000') === 3500000 && Money::parse('35,000') === 3500000 && Money::parse('12.5') === 1250);
check('Money::parse rounds the third decimal half-up', Money::parse('1.005') === 101 && Money::parse('1.004') === 100);
check('Money::parse refuses ambiguous or hostile input (spaces, exponent, words, empty, huge)', Money::parse('35 000') === null && Money::parse('1e3') === null && Money::parse('abc') === null && Money::parse('') === null && Money::parse('99999999999999999') === null);
check('Money::parse never accepts an array, bool or null', Money::parse([1]) === null && Money::parse(null) === null && Money::parse(true) === null);
check('Money::format: UGX has no decimals, USD has two, negatives carry a minus', Money::format(3500000, 'UGX') === 'UGX 35,000' && Money::format(1250, 'USD') === 'USD 12.50' && Money::format(-1250, 'USD') === 'USD -12.50' && Money::format(3500000, 'UGX', false) === '35,000');
check('Money::roundTo rounds UGX to whole shillings half-up and leaves USD alone', Money::roundTo(150, 'UGX') === 200 && Money::roundTo(149, 'UGX') === 100 && Money::roundTo(1234, 'USD') === 1234);
check('Money::toDb / fromDb round-trip', Money::toDb(1250) === '12.50' && Money::toDb(5) === '0.05' && Money::fromDb('12.50') === 1250 && Money::fromDb('junk') === 0);
check('Money::mul and add refuse overflow and negative quantities', (static function (): bool {
    $a = $b = false;
    try {
        Money::mul(Money::MAX, 2);
    } catch (\OverflowException) {
        $a = true;
    }
    try {
        Money::mul(100, -1);
    } catch (\InvalidArgumentException) {
        $b = true;
    }
    return $a && $b && Money::mul(250, 3) === 750 && Money::add(100, 250, 50) === 400;
})());
check('Money::gateway sends whole numbers for UGX and 2-decimals for USD; fromGateway reads them back', Money::gateway(3500000, 'UGX') === 35000 && Money::gateway(1250, 'USD') === 12.5 && Money::fromGateway(35000) === 3500000 && Money::fromGateway('12.50') === 1250 && Money::fromGateway(null) === null && Money::fromGateway(true) === null);
check('Money::validCurrency wants three capital letters', Money::validCurrency('UGX') && !Money::validCurrency('ugx') && !Money::validCurrency('UGXX'));

echo "-- core units: sanitizer --\n";
$dirty = '<p onclick="x()">Hi <script>alert(1)</script><a href="javascript:alert(1)">bad</a> <a href="https://example.org/">ok</a></p>';
$clean = Sanitizer::html($dirty);
check('Sanitizer::html drops scripts, event handlers and javascript: links but keeps safe markup', !str_contains($clean, '<script') && !str_contains($clean, 'onclick') && !str_contains($clean, 'javascript:') && str_contains($clean, 'https://example.org/') && str_contains($clean, 'Hi'), $clean);
check('Sanitizer::text strips tags, decodes entities and collapses whitespace', Sanitizer::text("  <b>Hello</b>\n  &amp;   world ") === 'Hello & world');
check('Sanitizer::url accepts https, mailto, tel and anchors', Sanitizer::url('https://example.org/a?b=1') === 'https://example.org/a?b=1' && Sanitizer::url('mailto:a@example.org') === 'mailto:a@example.org' && Sanitizer::url('tel:+256700000000') === 'tel:+256700000000' && Sanitizer::url('#top') === '#top');
check('Sanitizer::url refuses javascript:, data:, protocol-relative and control-character tricks', Sanitizer::url('javascript:alert(1)') === '' && Sanitizer::url('data:text/html,x') === '' && Sanitizer::url('//evil.example') === '' && Sanitizer::url("java\tscript:alert(1)") === '' && Sanitizer::url('') === '');
check('Sanitizer::localPath refuses path traversal and backslashes', Sanitizer::localPath('/a/../etc/passwd') === null && Sanitizer::localPath('\\\\server\\share') === null);

echo "-- core units: slug --\n";
check('Slug::make lowercases, hyphenates and trims', Slug::make('  Hello, World!  ') === 'hello-world' && Slug::make('Our   Team -- 2026') === 'our-team-2026');
check('Slug::make returns empty for empty input and respects the length cap', Slug::make('   ') === '' && strlen(Slug::make(str_repeat('a', 300), 50)) <= 50);

echo "-- core units: validator --\n";
$ok = Validator::make(['name' => '  Amina  ', 'email' => 'amina@example.org', 'age' => '30', 'kind' => 'partner'], ['name' => 'required|max:20', 'email' => 'required|email', 'age' => 'required|integer|between:18,99', 'kind' => 'required|in:volunteer,partner']);
check('Validator passes good input and trims strings', $ok->passes() && $ok->validated()['name'] === 'Amina');
$bad = Validator::make(['name' => '', 'email' => 'nope', 'age' => '5', 'kind' => 'hacker'], ['name' => 'required', 'email' => 'required|email', 'age' => 'required|integer|between:18,99', 'kind' => 'required|in:volunteer,partner']);
check('Validator reports one message per bad field', $bad->fails() && count($bad->errors()) === 4, json_encode($bad->errors()));
check('Validator uses custom messages', Validator::make([], ['email' => 'required'], ['email.required' => 'Please enter your email.'])->errors()['email'] === 'Please enter your email.');
check('Validator rejects a list posted into a scalar field (name[]=x)', Validator::make(['name' => ['x']], ['name' => 'required|max:20'])->fails());
check('Validator: nullable fields may be empty, but must be valid when filled', Validator::make(['site' => ''], ['site' => 'nullable|url'])->passes() && Validator::make(['site' => 'javascript:alert(1)'], ['site' => 'nullable|url'])->fails());
check('Validator: required|accepted (always paired with required, as the forms do), and confirmed', Validator::make(['agree' => '1'], ['agree' => 'required|accepted'])->passes() && Validator::make(['agree' => ''], ['agree' => 'required|accepted'])->fails() && Validator::make(['agree' => 'no'], ['agree' => 'required|accepted'])->fails() && Validator::make(['p' => 'abc', 'p_confirmation' => 'abc'], ['p' => 'required|confirmed'])->passes() && Validator::make(['p' => 'abc', 'p_confirmation' => 'abd'], ['p' => 'required|confirmed'])->fails());

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL PASSED' : $fail . ' FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
