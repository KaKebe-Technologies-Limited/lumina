<?php
declare(strict_types=1);

/**
 * VISION & MISSION EXPLORER (home page, partials/vision-mission.php): both statements are in the page for everybody (they work without JavaScript), the tabs and the key points are there for
 * the script, the wording is editable, and nothing is inline (strict CSP). Run alone: /c/xampp/php/php.exe bin/selftest-vision-mission.php   Needs Apache.
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

echo "-- vision and mission explorer --\n";
$ch = curl_init('http://localhost/' . basename(dirname(__DIR__)) . '/');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 60]);
$html = (string) curl_exec($ch);
$code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
curl_close($ch);
$vm = preg_match('/<div class="vm" data-vm>.*?(?=<\/div>\s*<\/div>\s*<p class="mt-5 home-mission__actions"|<p class="mt-5 home-mission__actions")/s', $html, $m) === 1 ? $m[0] : '';
check('home: the explorer is on the page', $code === 200 && $vm !== '' && str_contains($html, 'js/vision-mission.js'));
check('both statements are in the HTML with their headings (everybody can read them, with or without scripts)', str_contains($vm, 'id="vm-panel-vision"') && str_contains($vm, 'id="vm-panel-mission"') && str_contains($vm, (string) content('home', 'vision_mission.vision_text')) && str_contains($vm, (string) content('home', 'vision_mission.mission_text')));
check('the tab bar is hidden until the script turns it on, and follows the tabs pattern (tablist, tab, tabpanel, aria-selected, aria-controls)', preg_match('/role="tablist"[^>]*hidden/', $vm) === 1 && substr_count($vm, 'role="tab"') === 2 && substr_count($vm, 'role="tabpanel"') === 2 && str_contains($vm, 'aria-selected="true"') && str_contains($vm, 'aria-controls="vm-panel-vision"'));
check('only the first tab can be reached with Tab (roving tabindex), the other is -1', str_contains($vm, 'tabindex="-1"') && substr_count($vm, 'tabindex="0"') >= 1);
check('the key points show as badges and the animated emblem is hidden from screen readers', substr_count($vm, 'class="vm__points"') === 2 && substr_count($vm, '<li style="--i:') >= 6 && substr_count($vm, 'class="vm__emblem" aria-hidden="true"') === 2);
check('nothing inline (no onclick, no inline script): the strict CSP stays valid', preg_match('/\son[a-z]+=|<script(?![^>]*src=)/i', $vm) !== 1);
check('the old two plain cards are gone', !str_contains($html, 'home-mission__cards'));

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL PASSED' : $fail . ' FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
