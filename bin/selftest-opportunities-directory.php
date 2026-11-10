<?php
declare(strict_types=1);

/**
 * The scholarship and fellowship directory, and the "Offered by" / "Level of study" filters of /opportunities. Run alone: /c/xampp/php/php.exe bin/selftest-opportunities-directory.php
 * (needs the local web server). It adds nothing permanent: the directory entries are the real ones; the test only reads them and removes its own test row.
 */

use App\Core\Database;
use App\Services\Opportunities\OpportunityMeta;
use App\Services\Opportunities\ScholarshipDirectory;

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

echo "-- opportunities directory and filters --\n";
check('the provider and level columns exist (added on first use)', OpportunityMeta::install());

// ---- the guesser
$g = OpportunityMeta::guess('Fully funded Master\'s and PhD scholarships from the Government of India for foreign students');
check('guess: government, graduate and PhD from the words', $g['provider'] === 'government' && in_array('graduate', $g['levels'], true) && in_array('phd', $g['levels'], true), json_encode($g));
$g2 = OpportunityMeta::guess('World Bank scholarship for undergraduate students', 'World Bank');
check('guess: international organisation and undergraduate ("undergraduate" is not "graduate")', $g2['provider'] === 'international' && $g2['levels'] === ['undergraduate'], json_encode($g2));
check('guess: a foundation is an organisation, and no level is invented', OpportunityMeta::guess('Leadership programme by a foundation for young founders')['provider'] === 'organisation' && OpportunityMeta::guess('A young founders programme')['levels'] === []);

// ---- the directory
$entries = ScholarshipDirectory::entries();
$titles = array_column($entries, 0);
check('the directory covers Mastercard Foundation, the Swedish Institute, India (ICCR, ITEC, Study in India) and beVisioneers by Mercedes-Benz', in_array('Mastercard Foundation Scholars Program', $titles, true)
    && in_array('Swedish Institute Scholarships for Global Professionals', $titles, true) && in_array('ICCR Scholarships (Africa Scholarship Scheme)', $titles, true)
    && in_array('ITEC: Indian Technical and Economic Cooperation Programme', $titles, true) && in_array('Study in India (SII) Scholarships', $titles, true) && in_array('beVisioneers: The Mercedes-Benz Fellowship', $titles, true));
check('the directory has 35 or more programmes, each with an official https link and a level or fellowship type', count($entries) >= 35 && count(array_filter($entries, static fn($e) => str_starts_with($e[9], 'https://'))) === count($entries));
check('every programme has one of the three provider types', count(array_filter($entries, static fn($e) => isset(OpportunityMeta::PROVIDERS[$e[2]]))) === count($entries));
$r = ScholarshipDirectory::install();
check('adding the directory again adds nothing twice', $r['added'] === 0 && $r['skipped'] >= 35, json_encode($r));
$row = Database::one("SELECT * FROM opportunities WHERE apply_url = 'https://mastercardfdn.org/all/scholars/'");
check('the Mastercard Foundation entry is an organisation, undergraduate + graduate, shown now, with no invented deadline', $row !== null && $row['provider_type'] === 'organisation' && $row['study_levels'] === 'undergraduate,graduate' && $row['deadline'] === null && (int) $row['is_active'] === 1 && (int) $row['needs_review'] === 0, json_encode($row ? [$row['provider_type'], $row['study_levels']] : null));
$si = Database::one("SELECT * FROM opportunities WHERE apply_url LIKE 'https://si.se/%'");
check('the Swedish Institute entry is a government scholarship for graduate study', $si !== null && $si['provider_type'] === 'government' && $si['study_levels'] === 'graduate' && $si['category'] === 'scholarship');
$bv = Database::one("SELECT * FROM opportunities WHERE apply_url = 'https://group.mercedes-benz.com/bevisioneers'");
check('beVisioneers is a fellowship', $bv !== null && $bv['category'] === 'fellowship');

// ---- the public page
$base = 'http://localhost/' . basename(dirname(__DIR__));
$get = static function (string $p) use ($base): array {
    $ch = curl_init($base . $p);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 60]);
    $b = (string) curl_exec($ch);
    $c = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return ['code' => $c, 'body' => $b];
};
$all = $get('/opportunities');
check('the page has the "Offered by" and "Level" filters', $all['code'] === 200 && str_contains($all['body'], 'name="provider"') && str_contains($all['body'], 'name="level"') && str_contains($all['body'], 'International organisations'));
$phd = $get('/opportunities?type=scholarship&level=phd');
check('level filter: PhD shows Vanier (PhD) and not the Swedish Institute (master\'s only)', str_contains($phd['body'], 'Vanier Canada Graduate Scholarships') && !str_contains($phd['body'], 'Swedish Institute Scholarships for Global Professionals'));
$gov = $get('/opportunities?type=scholarship&provider=government&q=India');
check('provider filter + search: Governments and "India" shows the ICCR scholarship', str_contains($gov['body'], 'ICCR Scholarships') && !str_contains($gov['body'], 'Mastercard Foundation Scholars Program'));
$intl = $get('/opportunities?provider=international&level=graduate');
check('provider filter: International organisations shows Erasmus Mundus (EU) and the World Bank scholarship', str_contains($intl['body'], 'Erasmus Mundus') && str_contains($intl['body'], 'Joint Japan/World Bank'));
$fel = $get('/opportunities?type=fellowship&q=Mercedes');
check('the fellowship type shows beVisioneers', str_contains($fel['body'], 'beVisioneers'));
check('a bad filter value is ignored (no error)', $get('/opportunities?provider=zzz&level=zzz')['code'] === 200);
$detail = $get('/opportunities/' . (string) Database::value("SELECT slug FROM opportunities WHERE apply_url = 'https://mastercardfdn.org/all/scholars/'"));
check('the detail page shows the level of study and the official link', $detail['code'] === 200 && str_contains($detail['body'], 'Level of study') && str_contains($detail['body'], 'mastercardfdn.org/all/scholars'));

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
