<?php
declare(strict_types=1);

/**
 * Home page: the four focus areas (numbered colour ribbons, the foundation's programme template). Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-home-focus.php
 * Needs the local web server.
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

echo "-- home focus areas --\n";
$reg = require dirname(__DIR__) . '/app/config/pages/home.php';
$sec = null;
$order = [];
foreach ($reg['sections'] ?? [] as $s) {
    $order[] = $s['key'];
    if ($s['key'] === 'focus') {
        $sec = $s;
    }
}
check('registry: the home page has an editable "focus" section with an on/off switch, placed before the program highlights', $sec !== null && !empty($sec['toggle']) && array_search('focus', $order, true) < array_search('programs', $order, true));
$items = [];
foreach ($sec['fields'] ?? [] as $f) {
    if ($f['key'] === 'items') {
        $items = $f['default'];
    }
}
check('registry: the four areas come with the foundation\'s wording (Health, Education, Technology & Innovation, Empowered Communities)', array_column($items, 'title') === ['Health', 'Education', 'Technology & Innovation', 'Empowered Communities']
    && $items[0]['text'] === 'Healthier people, stronger communities.' && $items[3]['text'] === 'Inclusive, resilient and sustainable Africa.');

$ch = curl_init('http://localhost/' . basename(dirname(__DIR__)) . '/');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 60]);
$html = (string) curl_exec($ch);
$code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
curl_close($ch);
check('page: the home page opens', $code === 200);
preg_match('~<section[^>]*home-focus.*?</section>~s', $html, $m);
$block = $m[0] ?? '';
check('page: the section shows its heading, an ordered list of four numbered rows and the tagline', $block !== '' && str_contains($block, 'focus areas') && substr_count($block, 'class="focus-row focus-row--') === 4 && str_contains($block, '<ol class="focus-list">') && str_contains($block, 'Igniting Light. Inspiring Change.'));
check('page: the rows are numbered 01 to 04 and coloured red, green, gold, purple in that order', preg_match_all('~focus-row focus-row--(\w+)~', $block, $t) === 4 && $t[1] === ['red', 'green', 'gold', 'purple'] && preg_match_all('~focus-row__num">(\d\d)<~', $block, $n) === 4 && $n[1] === ['01', '02', '03', '04']);
check('page: each row has its title, one line and an icon (decorative, hidden from screen readers)', preg_match_all('~focus-row__title">([^<]+)<~', $block, $tt) === 4 && $tt[1][2] === 'Technology &amp; Innovation' && substr_count($block, 'focus-row__icon" aria-hidden="true"') === 4 && str_contains($block, 'Digital skills for a brighter tomorrow.'));
check('page: a row with a link is clickable as a whole and keeps an accessible name', str_contains($block, 'class="focus-row__link"') && preg_match('~focus-row__link"[^>]*><span class="visually-hidden">Health</span>~', $block) === 1);
check('page: the section comes after the hero and before the program highlights', ($p1 = strpos($html, 'home-focus')) !== false && ($p2 = strpos($html, 'home-programs')) !== false ? $p1 < $p2 : true);
$css = (string) file_get_contents(dirname(__DIR__) . '/assets/css/main.min.css');
check('style: the ribbons are in the stylesheet (and the old clashing number-tile class is gone)', str_contains($css, '.focus-row--purple') && str_contains($css, '.focus-tagline') && !str_contains($css, '.ac-stats{list-style'));

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL CHECKS PASSED' : "{$fail} check(s) FAILED.") . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
