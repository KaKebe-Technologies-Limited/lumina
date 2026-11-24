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
// the wheel holds one <section> per area, so the block runs from the focus section to the program highlights
$p0 = strpos($html, 'home-focus');
$p1 = strpos($html, 'home-programs', (int) $p0);
$block = $p0 !== false ? substr($html, $p0, ($p1 !== false ? $p1 : $p0 + 60000) - $p0) : '';
check('page: the section shows its heading and the wheel with four area nodes', $block !== '' && str_contains($block, 'focus areas') && substr_count($block, 'data-fw-node=') === 4 && str_contains($block, 'class="fw fw--n4"'), 'nodes ' . substr_count($block, 'data-fw-node='));
check('page: Technology & Innovation is the lead node at the top, the others are coloured red, green, gold, purple in the editable order', preg_match('~<a class="fw__node fw__node--(\w+) is-lead"[^>]*data-fw-node="0"~', $block, $lead) === 1 && preg_match_all('~fw__node fw__node--(\w+)~', $block, $t) === 4 && count(array_unique($t[1])) === 4 && str_contains($block, 'fw__node-label">Technology'), json_encode($t[1] ?? []));
check('page: every area has a panel with its title and an icon, and the panels are labelled', preg_match_all('~<section class="fw-panel[^"]*"[^>]*aria-label="([^"]+)"~', $block, $pp) === 4 && in_array('Technology &amp; Innovation', $pp[1], true) && in_array('Health', $pp[1], true) && substr_count($block, 'fw-panel__icon') === 4, json_encode($pp[1] ?? []));
check('page: each node is a real link (works without JavaScript) and the script is loaded', substr_count($block, '<a class="fw__node') === 4 && str_contains($html, 'focus-wheel.js'));
check('page: the section comes after the hero and before the program highlights', ($p1 = strpos($html, 'home-focus')) !== false && ($p2 = strpos($html, 'home-programs')) !== false ? $p1 < $p2 : true);
$css = (string) file_get_contents(dirname(__DIR__) . '/assets/css/main.min.css');
check('style: the wheel is in the stylesheet (and the old clashing number-tile class is gone)', str_contains($css, '.fw__node') && str_contains($css, '.fw-panel') && !str_contains($css, '.ac-stats{list-style'));

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL CHECKS PASSED' : "{$fail} check(s) FAILED.") . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
