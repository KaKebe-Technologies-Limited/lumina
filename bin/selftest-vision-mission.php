<?php
declare(strict_types=1);

/**
 * VISION & MISSION EXPLORER (home page, partials/vision-mission.php): both statements and both photos are in the page for everybody (they work without JavaScript), the tabs and the key points are there for
 * the script, the wording and photos are editable, and nothing is inline (strict CSP). Run alone: /c/xampp/php/php.exe bin/selftest-vision-mission.php   Needs Apache.
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
$vm = preg_match('/<div class="vm[ "][^>]*data-vm>.*?<p class="vm__actions">.*?<\/p>/s', $html, $m) === 1 ? $m[0] : '';
check('home: the explorer is on the page and loads its script', $code === 200 && $vm !== '' && str_contains($html, 'js/vision-mission.js'));
check('both statements are in the HTML with their headings (everybody can read them, with or without scripts)', str_contains($vm, 'id="vm-panel-vision"') && str_contains($vm, 'id="vm-panel-mission"') && str_contains($vm, e((string) content('home', 'vision_mission.vision_text'))) && str_contains($vm, e((string) content('home', 'vision_mission.mission_text'))));
check('the tab bar is hidden until the script turns it on, and follows the tabs pattern (tablist, tab, tabpanel, aria-selected, aria-controls)', preg_match('/role="tablist"[^>]*hidden/', $vm) === 1 && substr_count($vm, 'role="tab"') === 2 && substr_count($vm, 'role="tabpanel"') === 2 && str_contains($vm, 'aria-selected="true"') && str_contains($vm, 'aria-controls="vm-panel-vision"') && str_contains($vm, 'aria-labelledby="vm-tab-mission"'));
check('only the first tab can be reached with Tab (roving tabindex), the other is -1', str_contains($vm, 'tabindex="-1"') && substr_count($vm, 'role="tab"') === 2 && preg_match('/role="tab"[^>]*tabindex="0"/', $vm) === 1);
check('both photos are there (one per statement), with alt text, a WebP source set where one exists, and the caption chip', substr_count($vm, 'data-vm-photo') === 2 && preg_match_all('/<img src="[^"]+" alt="[^"]+"/', $vm) === 2 && substr_count($vm, 'class="vm__chipcap"') === 2);
check('the key points are in the page for everybody (no-JS), the emblem is hidden from screen readers', substr_count($vm, 'class="vm__points"') === 2 && substr_count($vm, '<li style="--i:') >= 6 && substr_count($vm, 'class="vm__emblem" aria-hidden="true"') === 2);
check('the two buttons from the registry are under the card', str_contains($vm, (string) content('home', 'vision_mission.cta_label')) && str_contains($vm, 'btn--cta'));
check('nothing inline except the chip order variable: no onclick, no inline script (strict CSP)', preg_match('/\son[a-z]+=|<script(?![^>]*src=)/i', $vm) !== 1);
check('the old tilted photo collage and plain cards are gone from the section', !str_contains($html, 'home-mission__cards') && !preg_match('/home-mission__grid[^"]*"[^>]*>\s*<div class="photo-stack/', $html));

// registry: the photos and the optional one-line explanations are editable, the explanations default to empty (no copy invented)
$keys = [];
foreach ((array) (require dirname(__DIR__) . '/app/config/pages/home.php')['sections'] as $sec) {
    if (($sec['key'] ?? '') === 'vision_mission') {
        foreach ($sec['fields'] as $fl) {
            $keys[$fl['key']] = $fl;
        }
    }
}
check('registry: both photos are editable image fields with alt text', ($keys['image1']['type'] ?? '') === 'image' && ($keys['image2']['type'] ?? '') === 'image' && ($keys['image1']['alt'] ?? '') !== '' && ($keys['image2']['alt'] ?? '') !== '');
check('registry: optional explanation fields exist for both and default to empty', ($keys['vision_explain']['default'] ?? 'x') === '' && ($keys['mission_explain']['default'] ?? 'x') === '' && ($keys['vision_explain']['type'] ?? '') === 'textarea');

// the partial with explanations: they are in the page without JavaScript (under their chip) and carry the text the editors wrote
$one = partial('partials/vision-mission', ['items' => [
    ['key' => 'vision', 'icon' => 'eye', 'title' => 'V', 'text' => 'Vision text', 'points' => ['A', 'B'], 'explain' => ['About A', ''], 'image' => ['src' => 'assets/img/site/photo-06.jpg', 'alt' => 'x', 'has' => true]],
    ['key' => 'mission', 'icon' => 'target', 'title' => 'M', 'text' => 'Mission text', 'points' => ['C'], 'explain' => [], 'image' => ['src' => '', 'alt' => '', 'has' => false]],
]]);
check('partial: a one-line explanation shows under its chip, a point without one has no note', substr_count($one, 'class="vm__note"') === 1 && str_contains($one, 'About A'));
check('partial: a statement without a photo borrows the other so the stage has no hole', substr_count($one, 'data-vm-photo') === 2);
check('partial: with no photo at all the explorer is a single column', !str_contains(partial('partials/vision-mission', ['items' => [['key' => 'vision', 'icon' => 'eye', 'title' => 'V', 'text' => 'T', 'points' => []], ['key' => 'mission', 'icon' => 'target', 'title' => 'M', 'text' => 'T2', 'points' => []]]]), 'data-vm-photo'));

// behaviour file and styles
$js = (string) file_get_contents(dirname(__DIR__) . '/assets/js/vision-mission.js');
check('script: arrow keys, Home / End, swipe, pause on hover and focus, reduced-motion check, visibility check', str_contains($js, 'ArrowRight') && str_contains($js, "'Home'") && str_contains($js, 'touchend') && str_contains($js, 'focusin') && str_contains($js, 'prefers-reduced-motion') && str_contains($js, 'visibilitychange'));
$css = (string) file_get_contents(dirname(__DIR__) . '/assets/css/main.css');
check('styles: compiled, with the reduced-motion switch-off', str_contains($css, '.vm__thumb') && preg_match('/prefers-reduced-motion:\s*reduce\)\s*\{[^}]*\.vm__frame/s', $css) === 1);
if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL PASSED' : $fail . ' FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
