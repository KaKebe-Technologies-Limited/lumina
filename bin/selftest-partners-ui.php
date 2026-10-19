<?php
/**
 * Partners screens + public logo rendering checks (PARTNERS agent). Included by bin/selftest.php (shares check() and $fail) or run alone:
 *   /c/xampp/php/php.exe -d extension=gd bin/selftest-partners-ui.php
 * Covers the markup that must never regress: partials/partner-tile (link rules, escaping, name fallback), partner-grid and partner-strip (nothing
 * when empty), the admin card (hooks, escaping), and the About "Our partners" section (hidden when nothing is active, grouped only when more than one
 * type is shown, inactive partners left out). The About checks run inside a database TRANSACTION that is rolled back, so real partners are never
 * touched. The JSON API and the drag-and-drop screen are exercised over HTTP with a throwaway admin (see docs/PARTNERS_NEWSLETTER.md section 6).
 */
declare(strict_types=1);

if (!function_exists('check')) {
    if (PHP_SAPI !== 'cli') {
        exit('CLI only.');
    }
    require dirname(__DIR__) . '/app/bootstrap.php';
    if (\App\Core\Config::get('app.env') !== 'local') {
        exit("Refusing to run outside APP_ENV=local.\n");
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
use App\Core\View;
use App\Models\Partner;

echo "Partners screens and public logo rendering\n";

$puiTile = static fn(array $row, string $variant = 'wall'): string => View::partial('partials/partner-tile', ['partner' => $row, 'variant' => $variant]);
$puiRow = static fn(array $over = []): array => $over + ['id' => 9001, 'name' => 'Zz Test Org', 'partner_type' => 'partner', 'website' => '', 'logo' => null, 'is_active' => 1, 'sort_order' => 1];

// ------------------------------------------------------------------ the tile
$html = $puiTile($puiRow(['website' => 'https://ok.example.org/x?a=1&b=2']));
check('tile: a website makes the box a link that opens in a new tab with rel noopener noreferrer', str_contains($html, '<a class="plogo plogo--wall" href="https://ok.example.org/x?a=1&amp;b=2" target="_blank" rel="noopener noreferrer"'), $html);
foreach (['javascript:alert(1)', 'data:text/html,<script>x</script>', 'ftp://example.org', 'https://a b.example.org', '//evil.example', 'https://x.org/"onmouseover="x'] as $bad) {
    $h = $puiTile($puiRow(['website' => $bad]));
    check('tile: unsafe website is never printed as a link (' . substr($bad, 0, 24) . ')', !str_contains($h, '<a ') && !str_contains($h, 'href='), $h);
}
$html = $puiTile($puiRow(['name' => 'A & <b>Co</b> "Q"']));
check('tile: without a logo the NAME is shown as escaped text', str_contains($html, 'plogo__name">A &amp; &lt;b&gt;Co&lt;/b&gt; &quot;Q&quot;</span>') && !str_contains($html, '<b>'), $html);
@mkdir(UPLOAD_PATH . '/zz-selftest-ui', 0777, true);
file_put_contents(UPLOAD_PATH . '/zz-selftest-ui/zz-x.png', 'x');                  // the file must exist, otherwise the tile shows the name (see the "file is missing" check below)
$html = $puiTile($puiRow(['logo' => 'zz-selftest-ui/zz-x.png', 'logo_width' => 480, 'logo_height' => 120, 'logo_2x' => 'zz-selftest-ui/zz-x-2x.png', 'logo_webp' => 'zz-selftest-ui/zz-x.webp', 'logo_webp_2x' => 'zz-selftest-ui/zz-x-2x.webp', 'name' => 'Zz "Alt" & Co']));
check('tile: logo = <picture> with WebP source, 1x/2x srcset, width/height, lazy, alt = escaped name',
    str_contains($html, '<picture class="partner-logo-pic">') && str_contains($html, 'type="image/webp"') && str_contains($html, ' 2x') && str_contains($html, 'width="480" height="120"')
    && str_contains($html, 'loading="lazy"') && str_contains($html, 'alt="Zz &quot;Alt&quot; &amp; Co"'), $html);
@unlink(UPLOAD_PATH . '/zz-selftest-ui/zz-x.png');
@rmdir(UPLOAD_PATH . '/zz-selftest-ui');
check('tile: an empty name prints nothing', trim($puiTile($puiRow(['name' => '  ']))) === '');
check('tile: the strip variant uses its own class', str_contains($puiTile($puiRow(), 'strip'), 'plogo plogo--strip'));
check('tile: an unknown variant falls back to the wall', str_contains($puiTile($puiRow(), '"><script>'), 'plogo plogo--wall') && !str_contains($puiTile($puiRow(), '"><script>'), '<script>'));

// ------------------------------------------------------------------ grid, strip, admin card
check('partner-grid prints nothing for an empty list', trim(View::partial('partials/partner-grid', ['partners' => []])) === '');
check('partner-strip prints nothing for an empty list', trim(View::partial('partials/partner-strip', ['partners' => []])) === '');
$html = View::partial('partials/partner-grid', ['partners' => [$puiRow(), $puiRow(['id' => 9002, 'name' => 'Second'])]]);
check('partner-grid: a list with one item per partner and the grey switch class from the setting', substr_count($html, 'partner-wall__item') === 2 && str_contains($html, 'role="list"'));
$html = View::partial('partials/partner-strip', ['partners' => [$puiRow()], 'title' => 'With <thanks>']);
check('partner-strip: escaped heading, labelled list', str_contains($html, 'With &lt;thanks&gt;') && str_contains($html, 'aria-labelledby="partner-strip-title"'));
$many = [];
for ($puiI = 1; $puiI <= 31; $puiI++) {
    $many[] = $puiRow(['id' => 9100 + $puiI, 'name' => 'Many ' . $puiI]);
}
$html = View::partial('partials/partner-strip', ['partners' => $many, 'title' => 'Thanks', 'limit' => 12, 'all_label' => 'See all <partners>']);
check('partner-strip: only the first `limit` partners are printed (31 partners, limit 12) and a link to /partners follows', substr_count($html, 'partner-strip__cell') === 12 && str_contains($html, 'Many 12') && !str_contains($html, 'Many 13') && str_contains($html, 'partner-strip__more') && str_contains($html, 'See all &lt;partners&gt;') && str_contains($html, '/partners"'), (string) substr_count($html, 'partner-strip__cell'));
$html = View::partial('partials/partner-strip', ['partners' => array_slice($many, 0, 5), 'title' => 'Thanks', 'limit' => 12, 'all_label' => 'See all']);
check('partner-strip: no "see all" link when every partner is already shown', substr_count($html, 'partner-strip__cell') === 5 && !str_contains($html, 'partner-strip__more'));
check('partner-strip: a labelled <section> landmark (named by the heading, or "Partners" without one)', str_starts_with(trim($html), '<section class="partner-strip" aria-labelledby="partner-strip-title">') && str_contains(View::partial('partials/partner-strip', ['partners' => [$puiRow()], 'title' => '']), '<section class="partner-strip" aria-label="Partners">'));
$html = View::partial('partials/newsletter-row', ['idp' => 'zz-nl']);
check('newsletter row: a <section> labelled by its heading', str_contains($html, '<section class="newsletter-row" aria-labelledby="zz-nl-email-title">') && str_contains($html, 'id="zz-nl-email-title"'));
$puiGone = $puiRow(['logo' => 'zz-selftest/gone/' . bin2hex(random_bytes(4)) . '.png', 'name' => 'Gone Logo Org']);
check('a logo whose file is missing: tile shows the NAME (no broken picture), admin card says the file is missing', str_contains($puiTile($puiGone), 'plogo__name">Gone Logo Org</span>') && !str_contains($puiTile($puiGone), '<img')
    && str_contains(View::partial('admin/partners/_card', ['p' => $puiGone, 'types' => ['partner' => 'Partner'], 'canDelete' => true]), 'Logo file missing') && !str_contains(View::partial('admin/partners/_card', ['p' => $puiGone, 'types' => ['partner' => 'Partner'], 'canDelete' => true]), 'Not optimised yet'));
$html = View::partial('admin/partners/_card', ['p' => $puiRow(['name' => 'Evil "><img src=x onerror=1>']), 'types' => ['partner' => 'Partner'], 'canDelete' => true]);
check('admin card: escaped name everywhere, JS hooks present', !str_contains($html, '<img src=x') && str_contains($html, 'data-pa-card') && str_contains($html, 'data-pa-toggle') && str_contains($html, 'data-pa-delete') && str_contains($html, 'data-pa-move="earlier"'), $html);
$html = View::partial('admin/partners/_card', ['p' => $puiRow(['is_active' => 0]), 'types' => ['partner' => 'Partner'], 'canDelete' => false]);
check('admin card: hidden partner is dimmed, switch off; no delete button without the ability', str_contains($html, 'is-off') && !str_contains($html, ' checked') && !str_contains($html, 'data-pa-delete'), $html);

// ------------------------------------------------------------------ About section (inside a transaction that is rolled back)
$pdo = Database::pdo();
$pdo->beginTransaction();
try {
    Database::execute('UPDATE partners SET is_active = 0');
    $about = static fn(): string => trim(View::partial('web/about/_partners'));
    check('About: nothing at all while no partner is active', $about() === '', $about());
    Partner::create(['name' => 'Zz One', 'partner_type' => 'partner', 'website' => 'https://one.example.org', 'is_active' => 1, 'sort_order' => 1]);
    Partner::create(['name' => 'Zz Two', 'partner_type' => 'partner', 'is_active' => 1, 'sort_order' => 2]);
    Partner::create(['name' => 'Zz Hidden', 'partner_type' => 'partner', 'is_active' => 0, 'sort_order' => 3]);
    $h = $about();
    check('About: one type -> a single wall without group headings, inactive partners left out', str_contains($h, 'id="partners"') && substr_count($h, 'partner-wall__item') === 2 && !str_contains($h, 'partner-group__title') && !str_contains($h, 'Zz Hidden'), $h);
    check('About: a partner with a website links out (new tab), one without does not', str_contains($h, 'href="https://one.example.org" target="_blank" rel="noopener noreferrer"') && substr_count($h, 'target="_blank"') === 1);
    check('About: link to the Partners page ("See all partners")', str_contains($h, 'href="' . url('partners') . '"') && str_contains($h, 'See all partners'));
    Partner::create(['name' => 'Zz Funder', 'partner_type' => 'funder', 'is_active' => 1, 'sort_order' => 4]);
    $h = $about();
    check('About: two types -> group headings (Partners, Funders)', substr_count($h, 'partner-group__title') === 2 && substr_count($h, 'partner-wall__item') === 3, $h);
} finally {
    $pdo->rollBack();
}
check('About checks left the database untouched (transaction rolled back)', (int) Database::value("SELECT COUNT(*) FROM partners WHERE name LIKE 'Zz %'", [], 0) === 0);

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
