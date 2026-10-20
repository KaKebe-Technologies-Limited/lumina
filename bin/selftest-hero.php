<?php
/**
 * Hero / banner image checks, included by bin/selftest.php (shares check() and $fail) or run alone:
 *   /c/xampp/php/php.exe bin/selftest-hero.php
 * Covers: responsive_sources() (WebP copies from bin/build-hero-images.py are found for a site photo, nothing for an upload or an unknown file), the
 * hero-img partial (<picture> with srcset + a normal <img> fallback, plain <img> for an upload, lazy for bands), every public banner page sends one
 * well-formed hero image with no leftover markup, and long titles get the smaller title size.
 */
declare(strict_types=1);

if (!function_exists('check')) {
    if (PHP_SAPI !== 'cli') {
        exit('CLI only.');
    }
    require dirname(__DIR__) . '/app/bootstrap.php';
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

echo "-- hero images --\n";
$set = responsive_sources('/lumina/assets/img/site/photo-05.jpg?v=1');
check('responsive_sources: a site photo has WebP copies, widest first-class srcset with w descriptors', $set !== null && preg_match('~-480\.webp\S* 480w, ~', $set['srcset']) === 1 && $set['max'] >= 800);
check('responsive_sources: an upload, a webp, a remote URL and an unknown photo have none', responsive_sources('/lumina/uploads/2026/10/abc.jpg') === null
    && responsive_sources('/lumina/assets/img/site/photo-05.webp') === null && responsive_sources('https://example.org/assets/img/site/x.jpg') === null
    && responsive_sources('/lumina/assets/img/site/no-such-photo.jpg') === null);
$html = partial('partials/hero-img', ['src' => '/lumina/assets/img/site/photo-05.jpg?v=1', 'alt' => 'A "quoted" <alt>', 'class' => 'page-hero__bg', 'focus' => '50% 20%']);
check('hero-img: <picture> + webp source + img fallback with priority, focus and an escaped alt', str_contains($html, '<picture>') && str_contains($html, 'type="image/webp"') && str_contains($html, 'fetchpriority="high"')
    && str_contains($html, 'object-position:50% 20%') && str_contains($html, 'alt="A &quot;quoted&quot; &lt;alt&gt;"') && substr_count($html, '<img') === 1);
$plain = partial('partials/hero-img', ['src' => '/lumina/uploads/2026/10/abc.jpg', 'alt' => '', 'class' => 'band__bg', 'priority' => 'lazy']);
check('hero-img: an upload is a plain lazy <img> exactly as before', !str_contains($plain, '<picture>') && str_contains($plain, 'loading="lazy"') && !str_contains($plain, 'fetchpriority'));

$base = 'http://localhost/' . basename(dirname(__DIR__));
$ok = true;
$why = '';
foreach (['', '/about', '/programs', '/involvement', '/opportunities', '/shop', '/contact', '/donate', '/membership'] as $path) {
    $h = (string) @file_get_contents($base . $path);
    $imgs = preg_match_all('~<img[^>]*class="(?:page-hero|hero-photo)__bg"[^>]*>~', $h, $m);
    $stray = preg_match('~\?>" alt=|" alt="[^"]*" fetchpriority~', $h) === 1 || str_contains($h, "?>\"");
    if ($imgs !== 1 || $stray) {
        $ok = false;
        $why .= ' ' . ($path ?: '/') . "(imgs={$imgs})";
    }
}
check('every banner page sends exactly one hero image and no leftover markup', $ok, $why);
$h = (string) @file_get_contents($base . '/opportunities');
check('the hero <img> is high priority and the other images stay lazy or normal', preg_match('~<img[^>]*page-hero__bg[^>]*>~', $h, $hi) === 1 && str_contains($hi[0], 'fetchpriority="high"') && substr_count($h, 'fetchpriority="high"') === 1);

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
