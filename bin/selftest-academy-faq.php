<?php
declare(strict_types=1);

/** LAF Academy FAQs: the seeded questions, the page /academy/faq, the short list on /academy and the sitemap. Run alone: /c/xampp/php/php.exe bin/selftest-academy-faq.php (needs the local web server). */

use App\Core\Database;

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

echo "-- academy FAQs --\n";
$base = 'http://localhost/' . basename(dirname(__DIR__));
$get = static function (string $p) use ($base): array {
    $ch = curl_init($base . $p);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 60]);
    $b = (string) curl_exec($ch);
    $c = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return ['code' => $c, 'body' => $b];
};
$faq = $get('/academy/faq');
check('the FAQ page opens with all 18 questions', $faq['code'] === 200 && substr_count($faq['body'], 'accordion__item') >= 18, (string) $faq['code']);
check('it holds the certificate fee answer and the accreditation answer', str_contains($faq['body'], 'USD 3') && str_contains($faq['body'], 'not represented as government-accredited'));
check('the list in "advanced digital skills" is a real list', str_contains($faq['body'], '<li>Graphic Design and Digital Content Creation.</li>'));
check('it carries FAQPage data for search engines', str_contains($faq['body'], '"FAQPage"'));
check('the About LAF Academy block is shown', str_contains($faq['body'], 'participate meaningfully in the digital economy'));
check('the questions are stored in Admin > FAQs (group academy), once', (int) Database::value("SELECT COUNT(*) FROM faqs WHERE group_key = 'academy'", [], 0) >= 18);
$home = $get('/academy');
check('/academy shows a short list with a link to all the questions', str_contains($home['body'], 'academy/faq') && str_contains($home['body'], 'What is LAF Academy?'));
check('the sitemap lists the page', str_contains($get('/sitemap.xml')['body'], 'academy/faq'));
if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
