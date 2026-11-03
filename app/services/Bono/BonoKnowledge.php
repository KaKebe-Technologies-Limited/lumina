<?php
declare(strict_types=1);

namespace App\Services\Bono;

use App\Core\Database;

/**
 * Bono AI: the BUILT-IN KNOWLEDGE about Lumina Africa Foundation, kept in app/config/bono/knowledge.txt (one "## heading" block per topic, "Q:" lines = the ways a visitor asks,
 * "A:" = the answer). Two uses:
 *   - install(): every Q: line becomes a TAUGHT answer (Admin > Bono AI lists them; an administrator can switch one off or delete it). Only questions that are not taught yet are
 *     added, so nothing an administrator wrote or changed is ever overwritten.
 *   - sections(): every block is also a searchable passage (BonoIndexer::facts adds them to the "facts" page that is re-learned every night), so a question phrased in a way
 *     nobody foresaw still finds the right answer.
 */
final class BonoKnowledge
{
    private const FILE = APP_PATH . '/config/bono/knowledge.txt';

    /** @return list<array{heading:string,questions:list<string>,answer:string}> */
    public static function load(?string $file = null): array
    {
        $raw = @file_get_contents($file ?? self::FILE);
        if ($raw === false) {
            return [];
        }
        $out = [];
        $cur = null;
        $inAnswer = false;
        foreach (preg_split('/\R/', $raw) ?: [] as $line) {
            if (str_starts_with($line, '#') && !str_starts_with($line, '##')) {
                continue;                                       // comment
            }
            if (str_starts_with($line, '## ')) {
                if ($cur !== null) {
                    $out[] = $cur;
                }
                $cur = ['heading' => trim(substr($line, 3)), 'questions' => [], 'answer' => ''];
                $inAnswer = false;
            } elseif ($cur !== null && str_starts_with($line, 'Q:')) {
                $q = trim(substr($line, 2));
                if ($q !== '') {
                    $cur['questions'][] = $q;
                }
            } elseif ($cur !== null && str_starts_with($line, 'A:')) {
                $cur['answer'] = trim(substr($line, 2));
                $inAnswer = true;
            } elseif ($cur !== null && $inAnswer && trim($line) !== '') {
                $cur['answer'] .= ' ' . trim($line);
            }
        }
        if ($cur !== null) {
            $out[] = $cur;
        }
        return array_values(array_filter($out, static fn(array $b): bool => $b['heading'] !== '' && $b['answer'] !== ''));
    }

    /** How many taught questions the knowledge file holds. */
    public static function count(): int
    {
        $n = 0;
        foreach (self::load() as $b) {
            $n += count($b['questions']);
        }
        return $n;
    }

    /**
     * Add the questions that are not taught yet. @return array{added:int,existing:int,blocks:int}
     */
    public static function install(?string $file = null): array
    {
        $res = ['added' => 0, 'existing' => 0, 'blocks' => 0];
        if (!BonoIndexer::ready()) {
            return $res;
        }
        $have = [];
        foreach (Database::all('SELECT question FROM bono_taught') as $r) {
            $have[mb_strtolower(trim((string) $r['question']))] = true;
        }
        foreach (self::load($file) as $b) {
            $res['blocks']++;
            $answer = mb_substr($b['answer'], 0, 1500);
            foreach ($b['questions'] as $q) {
                $q = BonoEngine::clean($q);
                $key = mb_strtolower($q);
                if (isset($have[$key])) {
                    $res['existing']++;
                    continue;
                }
                $stems = array_values(array_unique(BonoText::stems($q)));
                if ($stems === []) {
                    continue;
                }
                Database::insert('bono_taught', ['question' => mb_substr($q, 0, 300), 'answer' => $answer, 'stems' => ' ' . implode(' ', $stems) . ' ']);
                $have[$key] = true;
                $res['added']++;
            }
        }
        return $res;
    }

    /** The blocks as passages for the nightly learner. @return list<array{heading:string,anchor:string,text:string}> */
    public static function sections(): array
    {
        $s = [];
        foreach (self::load() as $b) {
            $s[] = ['heading' => $b['heading'] . ' ' . implode(' ', array_slice($b['questions'], 0, 3)), 'anchor' => '', 'text' => $b['answer']];
        }
        return $s;
    }
}
