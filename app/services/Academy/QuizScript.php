<?php
declare(strict_types=1);

namespace App\Services\Academy;

/**
 * The plain-text quiz format of the LAF Academy (typed in Admin > Academy > a quiz, and used by the starter course files).
 *
 *     Q: What does CPU stand for?
 *     * Central Processing Unit
 *     - Central Program Utility
 *     - Computer Personal Unit
 *     = The CPU is the "brain" of the computer: it carries out instructions.
 *
 *     Q: Which of these are input devices? (choose all that apply)
 *     * Keyboard
 *     * Mouse
 *     - Printer
 *
 * One question per block, blocks separated by a blank line. `Q:` the question, `*` a correct answer, `-` a wrong answer, `=` an optional
 * explanation shown after the quiz (it may continue on the following lines). One correct answer = "pick one", several = "pick all that apply".
 * Limits: 2-8 answers per question, at least one correct, question up to 500 and answer up to 200 characters. Plain text only (tags are removed).
 */
final class QuizScript
{
    public const MAX_OPTIONS = 8;
    public const MAX_QUESTIONS = 100;

    /**
     * @return array{ok:bool,questions:list<array{prompt:string,type:string,explanation:string,options:list<array{label:string,correct:bool}>}>,errors:list<string>}
     */
    public static function parse(string $script): array
    {
        $script = str_replace(["\r\n", "\r"], "\n", trim($script));
        $questions = [];
        $errors = [];
        if ($script === '') {
            return ['ok' => true, 'questions' => [], 'errors' => []];
        }
        $blocks = preg_split('/\n\s*\n/', $script) ?: [];
        $n = 0;
        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }
            $n++;
            $q = ['prompt' => '', 'type' => 'single', 'explanation' => '', 'options' => []];
            $mode = '';
            foreach (explode("\n", $block) as $line) {
                $line = rtrim($line);
                if (preg_match('/^\s*Q\s*[:.)]\s*(.*)$/i', $line, $m) === 1) {
                    $q['prompt'] = trim($m[1]);
                    $mode = 'q';
                } elseif (preg_match('/^\s*\*\s+(.*)$/', $line, $m) === 1) {
                    $q['options'][] = ['label' => trim($m[1]), 'correct' => true];
                    $mode = 'o';
                } elseif (preg_match('/^\s*-\s+(.*)$/', $line, $m) === 1) {
                    $q['options'][] = ['label' => trim($m[1]), 'correct' => false];
                    $mode = 'o';
                } elseif (preg_match('/^\s*=\s*(.*)$/', $line, $m) === 1) {
                    $q['explanation'] = trim($m[1]);
                    $mode = 'e';
                } elseif (trim($line) !== '') {
                    if ($mode === 'e') {
                        $q['explanation'] .= ' ' . trim($line);
                    } elseif ($mode === 'q') {
                        $q['prompt'] .= ' ' . trim($line);
                    } else {
                        $errors[] = 'Question ' . $n . ': the line "' . mb_substr(trim($line), 0, 60) . '" does not start with Q:, *, - or =.';
                    }
                }
            }
            $q['prompt'] = self::plain($q['prompt']);
            $q['explanation'] = self::plain($q['explanation']);
            foreach ($q['options'] as &$o) {
                $o['label'] = self::plain($o['label']);
            }
            unset($o);
            $label = 'Question ' . $n . ($q['prompt'] !== '' ? ' ("' . mb_substr($q['prompt'], 0, 40) . '")' : '');
            if ($q['prompt'] === '') {
                $errors[] = 'Question ' . $n . ' has no text: start it with "Q: ".';
            } elseif (mb_strlen($q['prompt']) > 500) {
                $errors[] = $label . ' is too long (500 characters at most).';
            }
            $count = count($q['options']);
            $correct = count(array_filter($q['options'], static fn(array $o): bool => $o['correct']));
            if ($count < 2) {
                $errors[] = $label . ' needs at least 2 answers.';
            } elseif ($count > self::MAX_OPTIONS) {
                $errors[] = $label . ' has more than ' . self::MAX_OPTIONS . ' answers.';
            }
            if ($count >= 2 && $correct === 0) {
                $errors[] = $label . ' has no correct answer: put * in front of the right one.';
            }
            if ($count >= 2 && $correct === $count) {
                $errors[] = $label . ' has only correct answers: put - in front of the wrong ones.';
            }
            foreach ($q['options'] as $o) {
                if ($o['label'] === '') {
                    $errors[] = $label . ' has an empty answer.';
                    break;
                }
                if (mb_strlen($o['label']) > 200) {
                    $errors[] = $label . ' has an answer longer than 200 characters.';
                    break;
                }
            }
            $labels = array_map(static fn(array $o): string => mb_strtolower($o['label']), $q['options']);
            if (count($labels) !== count(array_unique($labels))) {
                $errors[] = $label . ' repeats an answer.';
            }
            $q['type'] = $correct > 1 ? 'multiple' : 'single';
            $questions[] = $q;
        }
        if (count($questions) > self::MAX_QUESTIONS) {
            $errors[] = 'A quiz can have at most ' . self::MAX_QUESTIONS . ' questions.';
        }
        return ['ok' => $errors === [], 'questions' => $questions, 'errors' => $errors];
    }

    /**
     * The script of questions already stored (the text shown in the quiz editor).
     * @param list<array{prompt:string,explanation?:?string,options:list<array{label:string,is_correct?:mixed,correct?:mixed}>}> $questions
     */
    public static function export(array $questions): string
    {
        $out = [];
        foreach ($questions as $q) {
            $lines = ['Q: ' . $q['prompt']];
            foreach ($q['options'] as $o) {
                $ok = !empty($o['is_correct']) || !empty($o['correct']);
                $lines[] = ($ok ? '* ' : '- ') . $o['label'];
            }
            if (trim((string) ($q['explanation'] ?? '')) !== '') {
                $lines[] = '= ' . trim((string) $q['explanation']);
            }
            $out[] = implode("\n", $lines);
        }
        return implode("\n\n", $out);
    }

    private static function plain(string $s): string
    {
        $s = html_entity_decode(strip_tags($s), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim((string) preg_replace('/\s+/u', ' ', $s));
    }
}
