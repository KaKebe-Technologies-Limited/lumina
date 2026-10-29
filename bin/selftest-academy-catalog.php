<?php
declare(strict_types=1);

/**
 * LAF Academy: the course cards (coloured banner, Free / Certificate pills, level dots, what you will learn, facts). Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-academy-catalog.php
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

use App\Services\Academy\CourseService;

echo "-- academy course cards --\n";
$courses = CourseService::published();
check('catalogue: every published course comes with "highlights" (the first module titles, at most three, without "Module 1:" numbering)', $courses !== [] && !array_filter($courses, static fn(array $c): bool => !array_key_exists('highlights', $c) || count($c['highlights']) > 3)
    && !array_filter($courses, static fn(array $c): bool => (bool) array_filter($c['highlights'], static fn(string $t): bool => preg_match('/^\s*(module\s*)?\d+\s*[.:)\-]/i', $t) === 1)));
check('catalogue: a course with modules lists them as what the learner will learn', (bool) array_filter($courses, static fn(array $c): bool => (int) $c['modules'] > 0 && $c['highlights'] !== []));

$base = 'http://localhost/' . basename(dirname(__DIR__));
$get = static function (string $path) use ($base): array {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 60]);
    $body = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => $body];
};
$home = $get('/academy');
$b = $home['body'];
check('page: the Academy page loads its own card stylesheet (assets/css/catalog.min.css) and the file exists', $home['code'] === 200 && str_contains($b, 'css/catalog.min.css') && is_file(dirname(__DIR__) . '/assets/css/catalog.min.css'));
check('page: one card per course, each with a coloured banner of its category', substr_count($b, 'class="cc-card cc-tone-') === count($courses) && str_contains($b, 'cc-card__banner') && preg_match('/cc-tone-(basics|office|internet)/', $b) === 1);
check('page: every card says Free and shows the Certificate pill when the course has one, the category, and the level as dots', substr_count($b, 'cc-pill cc-pill--free">Free<') === count($courses) && str_contains($b, '<span class="cc-pill"') && str_contains($b, 'cc-card__cat') && str_contains($b, 'cc-level__dots') && str_contains($b, 'class="is-on"'));
check('page: the cards show "What you will learn", the number of lessons, the duration and the button', str_contains($b, 'What you will learn') && str_contains($b, 'cc-card__facts') && str_contains($b, 'class="cc-btn"') && preg_match('/\d+ lessons/', $b) === 1);
check('page: learners are counted ("N learners" or "Be the first to join")', preg_match('/(\d[\d,]* learners?|Be the first to join)/', $b) === 1);
$feat = (int) \App\Core\Database::value("SELECT COUNT(*) FROM academy_courses WHERE status = 'published' AND is_featured = 1", [], 0);
check('page: featured courses carry the "Popular" ribbon', substr_count($b, 'cc-card__ribbon') === $feat);
$slug = (string) $courses[0]['slug'];
$cp = $get('/academy/courses/' . $slug);
check('course page: the related courses use the same cards and load the stylesheet', $cp['code'] === 200 && str_contains($cp['body'], 'css/catalog.min.css') && (str_contains($cp['body'], 'class="cc-card cc-tone-') || count(CourseService::published(['category' => (string) $courses[0]['category']])) < 2));
$css = (string) file_get_contents(dirname(__DIR__) . '/assets/css/catalog.min.css');
check('style: all 14 categories have their own colours, with pills, ribbon, level dots and the learn box in the stylesheet', substr_count($css, '.cc-card__banner{background-color') >= count(CourseService::CATEGORIES) && str_contains($css, '.cc-card__ribbon') && str_contains($css, '.cc-level__dots') && str_contains($css, '.cc-card__learn'));

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL CHECKS PASSED' : "{$fail} check(s) FAILED.") . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
