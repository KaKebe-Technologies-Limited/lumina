<?php
declare(strict_types=1);

namespace App\Services\Bono;

use App\Core\Database;

/**
 * Bono AI: answers a visitor's question from what the website says. In this order:
 *   1. small talk (hello, thanks, who are you);
 *   2. an answer an administrator TAUGHT for the same question (Admin > Bono AI), which always wins;
 *   3. the best passages of the website (BM25 over the passages BonoIndexer stored) - phrased by Claude when ANTHROPIC_API_KEY is set (BonoAi), otherwise the best sentences
 *      of those passages, with links to the pages;
 *   4. when nothing in the website covers the question: it says so, points to the WhatsApp line and the contact page, and keeps the question for the administrators.
 * Every question is logged (text only, no name, address or IP) so the team can see what people ask and teach Bono the missing answers.
 */
final class BonoEngine
{
    public const OUT_NONE = 0;
    public const OUT_SITE = 1;
    public const OUT_TAUGHT = 2;
    public const OUT_AI = 3;
    public const OUT_TALK = 4;

    public static function enabled(): bool
    {
        return (string) setting('bono_enabled', '1') === '1' && BonoIndexer::ready();
    }

    public static function greeting(): string
    {
        $g = trim((string) setting('bono_greeting', ''));
        return $g !== '' ? $g : "Hello! I'm Bono AI, the support assistant of " . (trim((string) setting('site_name', '')) ?: 'Lumina Africa Foundation') . '. Ask me about our programmes, the LAF Academy, how to donate, volunteer or join, events, or how to reach us.';
    }

    /** @return list<string> */
    public static function suggestions(): array
    {
        return ['What programmes do you run?', 'How do I join the LAF Academy?', 'How can I donate?', 'How can I volunteer?', 'How do I contact you?'];
    }

    public static function clean(string $q): string
    {
        $q = trim((string) preg_replace('/\s+/u', ' ', strip_tags(mb_scrub($q, 'UTF-8'))));
        return mb_substr($q, 0, 500);
    }

    // ------------------------------------------------------------------------------------------------------------- retrieval

    /**
     * The passages of the website that best answer the question. coverage = the share of the question's meaningful words (weighted by how rare they are) the passage has.
     * @param list<string> $qstems
     * @return list<array<string,mixed>>
     */
    public static function retrieve(array $qstems, int $limit = 5): array
    {
        $qstems = array_values(array_unique(array_filter($qstems, static fn(string $s): bool => strlen($s) >= 2)));
        $qstems = array_slice($qstems, 0, 12);
        if ($qstems === [] || !BonoIndexer::ready()) {
            return [];
        }
        $n = max(1, (int) Database::value('SELECT COUNT(*) FROM bono_chunks', [], 0));
        $avg = max(1.0, (float) Database::value('SELECT AVG(len) FROM bono_chunks', [], 1));
        $like = static fn(string $s): string => '% ' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $s) . '%';
        // every word of the question stands for itself and a few other words that mean nearly the same (a "group": any of them counts once)
        $syn = BonoText::synonyms();
        $groups = [];
        foreach ($qstems as $s) {
            $groups[$s] = array_merge([$s => 1.0], array_fill_keys($syn[$s] ?? [], 0.6));
        }
        $idf = [];
        $conds = [];
        $params = [];
        $dfCache = [];
        foreach ($groups as $g => $alts) {
            $best = 0.0;
            foreach (array_keys($alts) as $a) {
                if (!isset($dfCache[$a])) {
                    $dfCache[$a] = (int) Database::value('SELECT COUNT(*) FROM bono_chunks WHERE stems LIKE ?', [$like($a)], 0);
                    $conds[] = 'c.stems LIKE ?';
                    $params[] = $like($a);
                }
                $best = max($best, log(1 + ($n - $dfCache[$a] + 0.5) / ($dfCache[$a] + 0.5)) * $alts[$a]);
            }
            $idf[$g] = $best;
        }
        $rows = Database::all(
            'SELECT c.id, c.heading, c.anchor, c.body, c.tf, c.len, p.title, p.url FROM bono_chunks c JOIN bono_pages p ON p.id = c.page_id WHERE ' . implode(' OR ', $conds) . ' LIMIT 400',
            $params
        );
        $totalIdf = max(0.0001, array_sum($idf));
        $out = [];
        foreach ($rows as $r) {
            $tf = json_decode((string) $r['tf'], true);
            if (!is_array($tf)) {
                continue;
            }
            $score = 0.0;
            $hit = 0.0;
            $pageStems = array_flip(BonoText::stems((string) $r['title'] . ' ' . str_replace(['/', '-', '_'], ' ', (string) parse_url((string) $r['url'], PHP_URL_PATH))));
            $inTitle = false;
            foreach ($groups as $g => $alts) {
                $f = 0.0;
                foreach ($alts as $q => $w) {
                    foreach ($tf as $k => $cnt) {
                        $k = (string) $k;
                        if ($k === (string) $q || (min(strlen($k), strlen((string) $q)) >= 5 && (str_starts_with($k, (string) $q) || str_starts_with((string) $q, $k)))) {
                            $f += $w * (int) $cnt;
                        }
                    }
                    foreach ($pageStems as $ps => $_) {
                        $inTitle = $inTitle || ((string) $ps === (string) $q && $w === 1.0) || ((string) $q === $g && min(strlen((string) $ps), strlen($g)) >= 5 && (str_starts_with((string) $ps, $g) || str_starts_with($g, (string) $ps)));
                    }
                }
                if ($f > 0) {
                    $hit += $idf[$g];
                    $score += $idf[$g] * ($f * 2.4) / ($f + 1.4 * (0.25 + 0.75 * ((int) $r['len'] / $avg)));
                }
            }
            if ($score <= 0) {
                continue;
            }
            // a page named after what is asked (Our Programs, Support Us), a page near the top of the site and the foundation's own facts are better answers than a deep page
            $depth = count(array_filter(explode('/', trim((string) parse_url((string) $r['url'], PHP_URL_PATH), '/'))));
            $score *= ($inTitle ? 1.6 : 1.0) * ($depth <= 2 ? 1.15 : 1.0) * (str_starts_with((string) $r['url'], 'bono://') ? 1.4 : 1.0);
            $out[] = ['id' => (int) $r['id'], 'title' => (string) $r['title'], 'url' => (string) $r['url'], 'heading' => (string) $r['heading'], 'anchor' => (string) $r['anchor'],
                'body' => (string) $r['body'], 'score' => $score, 'coverage' => min(1.0, $hit / $totalIdf)];
        }
        usort($out, static fn(array $a, array $b): int => (round($b['coverage'], 1) <=> round($a['coverage'], 1)) ?: ($b['score'] <=> $a['score']));
        // at most two passages of one page, so the answer draws on several pages
        $perPage = [];
        $final = [];
        foreach ($out as $o) {
            $perPage[$o['url']] = ($perPage[$o['url']] ?? 0) + 1;
            if ($perPage[$o['url']] <= 2) {
                $final[] = $o;
            }
            if (count($final) >= $limit) {
                break;
            }
        }
        return $final;
    }

    /** An answer taught by an administrator for (about) the same question. @return array{id:int,answer:string}|null */
    public static function taught(array $qstems): ?array
    {
        $qs = array_values(array_unique($qstems));
        if ($qs === []) {
            return null;
        }
        $best = null;
        $bestScore = 0.0;
        foreach (Database::all('SELECT id, answer, stems FROM bono_taught WHERE is_active = 1') as $t) {
            $ts = array_values(array_filter(explode(' ', (string) $t['stems'])));
            if ($ts === []) {
                continue;
            }
            $both = 0;
            foreach ($ts as $s) {
                foreach ($qs as $q) {
                    if ($s === $q || (min(strlen($s), strlen($q)) >= 5 && (str_starts_with($s, $q) || str_starts_with($q, $s)))) {
                        $both++;
                        break;
                    }
                }
            }
            $score = $both / count($ts);                                   // how much of the taught question the visitor's words cover
            $extra = count($qs) - $both;                                   // words the taught question does not have
            if ($both >= min(2, count($ts)) && $score >= 0.66 && $extra <= max(2, count($ts)) && $score > $bestScore) {
                $bestScore = $score;
                $best = ['id' => (int) $t['id'], 'answer' => (string) $t['answer']];
            }
        }
        return $best;
    }

    // ------------------------------------------------------------------------------------------------------------- answers

    /** The best one to three sentences of the passages for the question. @param list<array<string,mixed>> $passages */
    public static function extract(array $qstems, array $passages): string
    {
        $scored = [];
        $order = 0;
        $syn = BonoText::synonyms();
        foreach (array_slice($passages, 0, 3) as $pi => $p) {
            $lines = [];
            foreach (preg_split("/\n+/", (string) $p['body']) ?: [] as $line) {
                foreach (mb_strlen($line) > 220 ? BonoText::sentences($line) : [$line] as $s) {
                    $s = trim($s);
                    if (mb_strlen($s) >= 12) {
                        $lines[] = $s;
                    }
                }
            }
            foreach ($lines as $s) {
                $st = BonoText::stems($s);
                $m = 0.0;
                foreach ($qstems as $q) {
                    $best = 0.0;
                    foreach ([$q => 1.0] + array_fill_keys($syn[$q] ?? [], 0.7) as $alt => $w) {
                        foreach ($st as $x) {
                            if ($x === (string) $alt || (min(strlen($x), strlen((string) $alt)) >= 5 && (str_starts_with($x, (string) $alt) || str_starts_with((string) $alt, $x)))) {
                                $best = max($best, $w);
                                break;
                            }
                        }
                    }
                    $m += $best;
                }
                $scored[] = ['s' => $s, 'm' => $m + ($pi === 0 && $m > 0 ? 0.8 : 0.0), 'rank' => $pi, 'order' => $order++];
            }
        }
        usort($scored, static fn(array $a, array $b): int => ($b['m'] <=> $a['m']) ?: ($a['rank'] <=> $b['rank']) ?: ($a['order'] <=> $b['order']));
        $pick = [];
        $len = 0;
        $bestM = $scored[0]['m'] ?? 0.0;
        foreach ($scored as $x) {
            if (($x['m'] <= 0.0 || $x['m'] < $bestM * 0.55) && $pick !== []) {
                break;
            }
            if ($len + mb_strlen($x['s']) > 520 && $pick !== []) {
                continue;
            }
            $pick[] = $x;
            $len += mb_strlen($x['s']);
            if (count($pick) >= 3) {
                break;
            }
        }
        usort($pick, static fn(array $a, array $b): int => $a['order'] <=> $b['order']);
        return trim(implode(' ', array_map(static fn(array $x): string => $x['s'], $pick)));
    }

    /** @return array{answer:string,sources:list<array{title:string,url:string}>,outcome:int,score:float} */
    private static function none(): array
    {
        $sw = \App\Services\Academy\LearnerAuth::supportWhatsapp();
        $sources = [['title' => 'Contact us', 'url' => abs_url('contact')]];
        if ($sw !== null) {
            $sources[] = ['title' => 'Chat on WhatsApp ' . $sw['display'], 'url' => $sw['url']];
        }
        return [
            'answer' => "I'm not sure about that yet, and I don't want to guess. Please ask the team directly" . ($sw !== null ? ': WhatsApp ' . $sw['display'] : '') . ' or use our contact page. Your question was noted so the team can teach me the answer.',
            'sources' => $sources,
            'outcome' => self::OUT_NONE, 'score' => 0.0,
        ];
    }

    /** Small talk. @return array{answer:string,suggest:bool}|null */
    private static function talk(string $q): ?array
    {
        $l = mb_strtolower(trim($q, " \t\n\r.!?,"));
        if (preg_match('/^(hi|hello|hey|hola|good (morning|afternoon|evening)|greetings|howdy)( there| bono)?$/', $l) === 1) {
            return ['answer' => self::greeting(), 'suggest' => true];
        }
        if (preg_match('/^(thanks|thank you|thanks a lot|thank you so much|great thanks|ok thanks|cool thanks|asante)( bono)?$/', $l) === 1) {
            return ['answer' => "You're welcome! Is there anything else I can help you with?", 'suggest' => false];
        }
        if (preg_match('/^(bye|goodbye|see you|see you later|that is all|that\'s all|no thanks)$/', $l) === 1) {
            return ['answer' => 'Goodbye! Come back any time. Every day I learn more about the foundation.', 'suggest' => false];
        }
        if (preg_match('/(who|what) are you|your name|are you (a )?(bot|robot|human|ai)|what is bono|what can you do|help me$/', $l) === 1) {
            return ['answer' => "I'm Bono AI, the support assistant of the website. I read the whole website every day, so I can answer questions about the foundation, its programmes, the LAF Academy, donations, volunteering, events and contacts. If I don't know something I say so and point you to the team.", 'suggest' => true];
        }
        return null;
    }

    /**
     * Answer a question. $history = the last turns of this chat (role user|bot, text), only used to understand a short follow-up.
     * @param list<array{role:string,text:string}> $history
     * @return array{id:int,answer:string,sources:list<array{title:string,url:string}>,outcome:int,suggestions:list<string>}
     */
    public static function ask(string $question, array $history = []): array
    {
        $q = self::clean($question);
        $res = null;
        $suggest = false;
        $score = 0.0;
        $pageUrl = null;
        if ($t = self::talk($q)) {
            $res = ['answer' => $t['answer'], 'sources' => [], 'outcome' => self::OUT_TALK];
            $suggest = $t['suggest'];
        }
        $qstems = BonoText::stems($q);
        // a short follow-up ("and how much?") borrows the words of the visitor's previous question
        if ($res === null && count($qstems) <= 1) {
            for ($i = count($history) - 1; $i >= 0; $i--) {
                if (($history[$i]['role'] ?? '') === 'user' && self::clean((string) $history[$i]['text']) !== $q) {
                    $qstems = array_merge($qstems, BonoText::stems(self::clean((string) $history[$i]['text'])));
                    break;
                }
            }
        }
        if ($res === null && $qstems === []) {
            $res = ['answer' => 'Could you tell me a little more? For example: "How do I join the LAF Academy?" or "How can I donate?"', 'sources' => [], 'outcome' => self::OUT_TALK];
            $suggest = true;
        }
        if ($res === null) {
            $tg = self::taught($qstems);
            if ($tg !== null) {
                Database::execute('UPDATE bono_taught SET hits = hits + 1 WHERE id = ?', [$tg['id']]);
                $res = ['answer' => $tg['answer'], 'sources' => [], 'outcome' => self::OUT_TAUGHT];
                $score = 1.0;
            }
        }
        if ($res === null) {
            $passages = self::retrieve($qstems, 5);
            $top = $passages[0] ?? null;
            $enough = $top !== null && ($top['coverage'] >= (count($qstems) <= 1 ? 1.0 : 0.5) || ($top['coverage'] >= 0.34 && count($qstems) >= 4));
            if (!$enough) {
                $res = self::none();
            } else {
                $score = (float) $top['coverage'];
                $pageUrl = (string) $top['url'];
                $answer = BonoAi::enabled() ? BonoAi::answer($q, $history, $passages) : null;
                $outcome = $answer !== null ? self::OUT_AI : self::OUT_SITE;
                $answer ??= self::extract($qstems, $passages);
                $sources = [];
                foreach ($passages as $p) {
                    if (count($sources) >= 3) {
                        break;
                    }
                    $u = str_starts_with($p['url'], 'bono://') ? abs_url('contact') : $p['url'] . ($p['anchor'] !== '' ? '#' . $p['anchor'] : '');
                    $already = false;
                    foreach ($sources as $s) {
                        $already = $already || $s['url'] === $u;
                    }
                    if (!$already) {
                        $sources[] = ['title' => $p['title'] !== '' ? $p['title'] : $p['heading'], 'url' => $u];
                    }
                }
                $res = ['answer' => $answer !== '' ? $answer : self::none()['answer'], 'sources' => $sources, 'outcome' => $answer !== '' ? $outcome : self::OUT_NONE];
            }
        }
        $id = 0;
        try {
            $id = (int) Database::insert('bono_log', ['question' => mb_substr($q, 0, 500), 'outcome' => $res['outcome'], 'score' => round($score, 3), 'page_url' => $pageUrl !== null ? mb_substr($pageUrl, 0, 500) : null]);
        } catch (\Throwable) {
            // the log is a convenience: never stop an answer for it
        }
        return ['id' => $id, 'answer' => $res['answer'], 'sources' => array_values($res['sources']), 'outcome' => $res['outcome'], 'suggestions' => $suggest ? self::suggestions() : []];
    }
}
