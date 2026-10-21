<?php
/**
 * Checks the starter course files of the LAF Academy (app/config/academy/courses/*.php). See docs/ACADEMY-CONTENT.md.
 *
 *   /c/xampp/php/php.exe bin/academy-validate-content.php [file ...]        no argument = every file
 *
 * Prints one summary line per course (modules, lessons, words, quiz questions) and every ERROR / WARNING. Exit code 1 when any ERROR exists.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Sanitizer;
use App\Services\Academy\QuizScript;

$files = array_slice($argv, 1);
if ($files === []) {
    $files = glob(APP_PATH . '/config/academy/courses/*.php') ?: [];
    sort($files);
}
$cats = ['basics', 'office', 'internet', 'marketing', 'design', 'security', 'careers', 'web', 'coding', 'ai', 'hardware'];
$levels = ['beginner', 'intermediate', 'advanced'];
$icons = ['laptop', 'file-text', 'mail', 'globe', 'megaphone', 'shield', 'briefcase', 'code', 'layers', 'book', 'award', 'users', 'target', 'sprout', 'star', 'settings'];
$allowed = ['h3', 'h4', 'p', 'ul', 'ol', 'li', 'strong', 'em', 'code', 'pre', 'blockquote', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'a', 'br'];
$errors = 0;
$totals = ['courses' => 0, 'lessons' => 0, 'words' => 0, 'questions' => 0];
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
    if (preg_match('/^(\d+)-(.+)\.php$/', $name, $m) === 1) {
        if ($m[2] !== $slug) {
            $warn('the file name should be ' . $m[1] . '-' . $slug . '.php');
        }
        if ((int) ($c['sort'] ?? 0) !== (int) $m[1]) {
            $warn('"sort" is ' . (int) ($c['sort'] ?? 0) . ' but the file number is ' . $m[1] . '.');
        }
    }
    foreach (['title', 'summary', 'description'] as $k) {
        if (trim((string) ($c[$k] ?? '')) === '') {
            $err("\"{$k}\" is empty.");
        }
    }
    if (mb_strlen((string) ($c['title'] ?? '')) > 190) {
        $err('title is longer than 190 characters.');
    }
    if (mb_strlen((string) ($c['summary'] ?? '')) > 240) {
        $warn('summary is ' . mb_strlen((string) $c['summary']) . ' characters (keep it under 220).');
    }
    if (!in_array($c['category'] ?? '', $cats, true)) {
        $err('category must be one of: ' . implode(', ', $cats));
    }
    if (!in_array($c['level'] ?? '', $levels, true)) {
        $err('level must be one of: ' . implode(', ', $levels));
    }
    if (!in_array($c['icon'] ?? '', $icons, true)) {
        $err('icon must be one of: ' . implode(', ', $icons));
    }
    $pm = (int) ($c['pass_mark'] ?? 0);
    if ($pm < 40 || $pm > 100) {
        $err('pass_mark must be between 40 and 100.');
    }
    foreach (['outcomes' => [5, 10], 'requirements' => [1, 5]] as $k => [$lo, $hi]) {
        $list = $c[$k] ?? null;
        if (!is_array($list) || count($list) < $lo || count($list) > $hi) {
            $err("\"{$k}\" needs {$lo}-{$hi} items.");
        } else {
            foreach ($list as $item) {
                if (!is_string($item) || trim($item) === '' || mb_strlen($item) > 240) {
                    $err("\"{$k}\" has an empty or too long item.");
                    break;
                }
            }
        }
    }
    $res = $c['resources'] ?? [];
    if (!is_array($res) || count($res) < 1 || count($res) > 6) {
        $warn('"resources" should hold 2-5 links.');
    } else {
        foreach ($res as $r) {
            if (!is_array($r) || empty($r['title']) || !isset($r['url']) || preg_match('~^https://[^\s"<>]+$~', (string) $r['url']) !== 1) {
                $err('a resource needs a title and an https:// url.');
            }
        }
    }
    $mods = $c['modules'] ?? [];
    if (!is_array($mods) || count($mods) < 3 || count($mods) > 8) {
        $err('a course needs 3-8 modules (4-6 is ideal), it has ' . (is_array($mods) ? count($mods) : 0) . '.');
        $mods = is_array($mods) ? $mods : [];
    }
    $lessons = 0;
    $words = 0;
    $questions = 0;
    $titles = [];
    foreach ($mods as $mi => $mod) {
        $mt = 'Module ' . ($mi + 1);
        if (trim((string) ($mod['title'] ?? '')) === '') {
            $err("{$mt} has no title.");
        }
        if (trim((string) ($mod['summary'] ?? '')) === '') {
            $warn("{$mt} has no summary.");
        }
        $ls = $mod['lessons'] ?? [];
        if (!is_array($ls) || count($ls) < 2 || count($ls) > 8) {
            $err("{$mt} needs 2-8 lessons (3-5 is ideal).");
            $ls = is_array($ls) ? $ls : [];
        }
        foreach ($ls as $li => $lesson) {
            $lessons++;
            $lt = $mt . ' lesson ' . ($li + 1);
            $title = trim((string) ($lesson['title'] ?? ''));
            if ($title === '' || mb_strlen($title) > 190) {
                $err("{$lt} has no title (or it is too long).");
            }
            if (isset($titles[mb_strtolower($title)])) {
                $warn("{$lt}: the title \"{$title}\" is used twice in this course.");
            }
            $titles[mb_strtolower($title)] = 1;
            $min = (int) ($lesson['minutes'] ?? 0);
            if ($min < 3 || $min > 45) {
                $err("{$lt}: \"minutes\" must be 3-45.");
            }
            $body = (string) ($lesson['body'] ?? '');
            $w = str_word_count(strip_tags(str_replace('<', ' <', $body)));
            $words += $w;
            if ($w < 300) {
                $warn("{$lt} \"{$title}\" has only {$w} words (aim for 350-700).");
            }
            if ($w > 1100) {
                $warn("{$lt} \"{$title}\" has {$w} words: split it.");
            }
            if (!str_contains($body, 'Try it yourself')) {
                $warn("{$lt} \"{$title}\" has no \"Try it yourself\" section.");
            }
            if (!str_contains($body, 'Key points')) {
                $warn("{$lt} \"{$title}\" has no \"Key points\" section.");
            }
            preg_match_all('~<\s*([a-zA-Z0-9]+)~', $body, $tm);
            $bad = array_values(array_unique(array_diff(array_map('strtolower', $tm[1]), $allowed)));
            if ($bad !== []) {
                $err("{$lt} uses tags that are not allowed: " . implode(', ', $bad));
            }
            if (preg_match('~(?<![A-Za-z])(on[a-z]+\s*=|style\s*=|class\s*=|javascript:)~i', $body) === 1) {
                $err("{$lt} contains an attribute that is not allowed (style, class, on...=, javascript:).");
            }
            $clean = Sanitizer::rich($body);
            if (strlen(strip_tags($clean)) < strlen(strip_tags($body)) * 0.97) {
                $warn("{$lt}: the sanitiser removed part of the text (check for stray < or > characters).");
            }
            if (substr_count($body, '<p>') + substr_count($body, '<p ') !== substr_count($body, '</p>')) {
                $err("{$lt}: unbalanced <p> tags.");
            }
            foreach (['ul', 'ol', 'li', 'table', 'pre', 'h3'] as $t) {
                if (substr_count($body, '<' . $t . '>') !== substr_count($body, '</' . $t . '>')) {
                    $err("{$lt}: unbalanced <{$t}> tags.");
                }
            }
        }
        $q = QuizScript::parse((string) ($mod['quiz'] ?? ''));
        $n = count($q['questions']);
        if ($n < 4 || $n > 12) {
            $err("{$mt} quiz needs 5-8 questions (it has {$n}).");
        }
        foreach ($q['errors'] as $e) {
            $err("{$mt} quiz: {$e}");
        }
        foreach ($q['questions'] as $qi => $qq) {
            if ($qq['explanation'] === '') {
                $warn("{$mt} quiz question " . ($qi + 1) . ' has no explanation (= line).');
            }
        }
        $questions += $n;
    }
    $f = QuizScript::parse((string) ($c['final_exam'] ?? ''));
    $fn = count($f['questions']);
    if ($fn < 15 || $fn > 40) {
        $err("the final exam needs 20-30 questions (it has {$fn}).");
    }
    foreach ($f['errors'] as $e) {
        $err("final exam: {$e}");
    }
    $firstCorrect = 0;
    foreach ($f['questions'] as $qi => $qq) {
        if ($qq['explanation'] === '') {
            $warn('final exam question ' . ($qi + 1) . ' has no explanation (= line).');
        }
        if ($qq['options'][0]['correct'] ?? false) {
            $firstCorrect++;
        }
    }
    if ($fn >= 10 && $firstCorrect > $fn * 0.5) {
        $warn("in the final exam the first answer is correct in {$firstCorrect} of {$fn} questions: spread the correct answers over different positions.");
    }
    $questions += $fn;
    $totals['courses']++;
    $totals['lessons'] += $lessons;
    $totals['words'] += $words;
    $totals['questions'] += $questions;
    printf("%-34s %d modules, %2d lessons, %5d words, %3d quiz questions (final exam %d)\n", $name, count($mods), $lessons, $words, $questions, $fn);
}
echo sprintf("\n%d course(s), %d lessons, %d words, %d questions. %s\n", $totals['courses'], $totals['lessons'], $totals['words'], $totals['questions'], $errors === 0 ? 'No errors.' : $errors . ' ERROR(S).');
exit($errors === 0 ? 0 : 1);
