<?php
/**
 * Checks the starter CHALLENGE files of the LAF Academy (app/config/academy/challenges/*.php). See docs/ACADEMY-CONTENT.md ("Challenge files").
 *
 *   /c/xampp/php/php.exe bin/academy-validate-challenges.php [file ...]        no argument = every file
 *
 * Prints one line per challenge (kind, questions) and every ERROR / WARNING. Exit code 1 when any ERROR exists.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\Academy\CourseService;
use App\Services\Academy\QuizScript;

$files = array_slice($argv, 1);
if ($files === []) {
    $files = glob(APP_PATH . '/config/academy/challenges/*.php') ?: [];
    sort($files);
}
$errors = 0;
$slugs = [];
foreach ($files as $file) {
    $name = basename($file);
    $err = static function (string $m) use ($name, &$errors): void {
        $errors++;
        echo "  ERROR   {$name}: {$m}\n";
    };
    $warn = static function (string $m) use ($name): void {
        echo "  WARNING {$name}: {$m}\n";
    };
    try {
        $c = include $file;
    } catch (\Throwable $e) {
        $err('the file does not load: ' . $e->getMessage());
        continue;
    }
    if (!is_array($c)) {
        $err('the file must return an array.');
        continue;
    }
    $slug = (string) ($c['slug'] ?? '');
    if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) !== 1 || strlen($slug) > 120) {
        $err('slug "' . $slug . '" is not valid.');
    }
    if (isset($slugs[$slug])) {
        $err('slug "' . $slug . '" is already used by ' . $slugs[$slug] . '.');
    }
    $slugs[$slug] = $name;
    if (!in_array($c['kind'] ?? '', ['quiz', 'innovation', 'hackathon'], true)) {
        $err("'kind' must be quiz, innovation or hackathon.");
    }
    if (!isset(CourseService::CATEGORIES[(string) ($c['category'] ?? '')])) {
        $err("'category' is not a known topic.");
    }
    foreach (['title' => 190, 'summary' => 320, 'description' => 4000, 'rules' => 3000, 'prize' => 400] as $k => $max) {
        $v = trim((string) ($c[$k] ?? ''));
        if ($v === '') {
            $err("'{$k}' is empty.");
        } elseif (mb_strlen($v) > $max) {
            $err("'{$k}' is longer than {$max} characters.");
        } elseif ($v !== strip_tags($v)) {
            $err("'{$k}' must be plain text (no HTML).");
        }
    }
    $w = (int) ($c['winners_count'] ?? 0);
    if ($w < 1 || $w > 10) {
        $err("'winners_count' must be 1-10.");
    }
    $n = 0;
    if (($c['kind'] ?? '') === 'quiz') {
        $m = (int) ($c['quiz_minutes'] ?? 0);
        if ($m < 5 || $m > 180) {
            $err("'quiz_minutes' must be 5-180.");
        }
        $p = QuizScript::parse((string) ($c['quiz'] ?? ''));
        foreach ($p['errors'] as $e) {
            $err('quiz: ' . $e);
        }
        $n = count($p['questions']);
        if ($n < 25) {
            $err("a quiz challenge needs at least 25 questions (has {$n}).");
        }
        $prompts = array_map(static fn(array $q): string => mb_strtolower($q['prompt']), $p['questions']);
        if (count($prompts) !== count(array_unique($prompts))) {
            $err('the same question appears twice.');
        }
        $rights = [0, 0, 0, 0, 0, 0, 0, 0];
        foreach ($p['questions'] as $q) {
            foreach ($q['options'] as $i => $o) {
                if ($o['correct']) {
                    $rights[$i]++;
                }
            }
        }
        if ($n > 0 && max($rights) > 0.55 * $n) {
            $warn('the correct answer sits in the same position in more than half of the questions: vary it.');
        }
    }
    printf("%-44s %-10s %d questions\n", $name, (string) ($c['kind'] ?? '?'), $n);
}
echo ($files === [] ? 'No challenge files.' : count($files) . ' challenge file(s).') . ($errors > 0 ? " {$errors} ERROR(S)." : ' No errors.') . "\n";
exit($errors > 0 ? 1 : 0);
