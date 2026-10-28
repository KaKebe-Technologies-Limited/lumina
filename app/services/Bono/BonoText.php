<?php
declare(strict_types=1);

namespace App\Services\Bono;

/**
 * Bono AI: the small text toolkit shared by the learner (BonoIndexer) and the answerer (BonoEngine): words, a light English stemmer, stop words and sentences.
 * Pure functions, no database.
 */
final class BonoText
{
    private const STOP = 'a about above after again all also am an and any are as at be because been before being below between both but by can could did do does doing down during each '
        . 'few for from further had has have having he her here hers him his how i if in into is it its just me more most my no nor not now of off on once only or other our out over own '
        . 'same she should so some such than that the their them then there these they this those through to too under until up us very was we were what when where which while who whom '
        . 'why will with would you your yours please tell want need know say get give let lets hi hello hey thanks thank ok okay yes also could might may shall '
        . 'long take takes taken many much find looking look make makes number possible able info run runs running offer offers anyone someone every everything';

    /** @return array<string,true> */
    private static function stopWords(): array
    {
        static $set = null;
        if ($set === null) {
            $set = array_fill_keys(explode(' ', self::STOP), true);
        }
        return $set;
    }

    /** Lower-case words (letters and digits, 2+ characters), accents folded, without the stop words. @return list<string> */
    public static function words(string $text): array
    {
        $t = mb_strtolower(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'), 'UTF-8');
        $t = str_replace(["\u{2019}", "'"], '', $t);
        if (function_exists('iconv')) {
            $f = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $t);
            $t = $f !== false && $f !== '' ? strtolower($f) : $t;
        }
        preg_match_all('/[a-z0-9]{2,}/', $t, $m);
        $stop = self::stopWords();
        return array_values(array_filter($m[0], static fn(string $w): bool => !isset($stop[$w])));
    }

    /** A light stemmer: donating, donated, donates, donations -> donat/donation. Good enough to match a question with the page wording. */
    public static function stem(string $w): string
    {
        $n = strlen($w);
        if ($n > 4 && str_ends_with($w, 'ies')) {
            return substr($w, 0, -3) . 'y';
        }
        if ($n > 5 && str_ends_with($w, 'sses')) {
            return substr($w, 0, -2);
        }
        foreach (['ations' => 'at', 'ation' => 'at', 'ments' => 'ment', 'ingly' => '', 'ing' => '', 'ed' => '', 'es' => '', 'ly' => '', 's' => ''] as $suf => $rep) {
            if (str_ends_with($w, $suf) && $n - strlen($suf) >= 3) {
                if ($suf === 's' && (str_ends_with($w, 'ss') || str_ends_with($w, 'us') || str_ends_with($w, 'is'))) {
                    continue;
                }
                $w = substr($w, 0, $n - strlen($suf)) . $rep;
                break;
            }
        }
        // donat(e) / donat(ing): drop a trailing silent e so the forms meet
        if (strlen($w) > 4 && str_ends_with($w, 'e')) {
            $w = substr($w, 0, -1);
        }
        return $w;
    }

    /**
     * Words that mean (nearly) the same thing for a visitor's question, so "where is your office" finds the page that says "address". Both sides are stemmed.
     * @return array<string,list<string>> stem => other stems
     */
    public static function synonyms(): array
    {
        static $map = null;
        if ($map === null) {
            $pairs = [
                'donate' => ['contribution', 'pledge', 'giving'], 'phone' => ['telephone', 'call', 'mobile', 'whatsapp', 'contact'], 'email' => ['mail', 'contact'],
                'located' => ['address', 'office', 'headquarters', 'gulu', 'visit'], 'location' => ['address', 'office', 'headquarters', 'gulu'], 'office' => ['address', 'located', 'headquarters', 'gulu'],
                'address' => ['location', 'office', 'gulu'], 'register' => ['signup', 'account', 'enrol', 'join'], 'join' => ['register', 'account', 'signup', 'enrol', 'member'],
                'signup' => ['register', 'account', 'join'], 'cost' => ['fee', 'price', 'free', 'pay'], 'price' => ['cost', 'fee', 'free'], 'fee' => ['cost', 'price', 'free'],
                'free' => ['cost', 'fee'], 'certificate' => ['award', 'diploma'], 'approval' => ['approved', 'approve', 'pending', 'waiting'], 'approved' => ['approval', 'waiting'],
                'volunteer' => ['volunteering', 'volunteers'], 'job' => ['career', 'vacancy', 'employment'], 'jobs' => ['career', 'vacancy', 'employment'], 'event' => ['events', 'calendar'],
                'programmes' => ['programs', 'projects', 'activities'], 'programme' => ['programs', 'projects', 'activities'], 'programs' => ['programmes', 'projects', 'activities'],
                'program' => ['programmes', 'projects', 'activities'], 'help' => ['support'], 'mission' => ['vision', 'about'], 'about' => ['mission', 'vision'],
            ];
            $map = [];
            foreach ($pairs as $k => $alts) {
                $sk = self::stem($k);
                foreach ($alts as $a) {
                    $sa = self::stem($a);
                    if ($sa !== $sk) {
                        $map[$sk][$sa] = true;
                    }
                }
            }
            foreach ($map as $k => $set) {
                $map[$k] = array_keys($set);
            }
        }
        return $map;
    }

    /** Stems of the meaningful words, in order (duplicates kept). @return list<string> */
    public static function stems(string $text): array
    {
        return array_map([self::class, 'stem'], self::words($text));
    }

    /** @return array<string,int> stem => how often */
    public static function termFrequency(string $text): array
    {
        $tf = [];
        foreach (self::stems($text) as $s) {
            $tf[$s] = ($tf[$s] ?? 0) + 1;
        }
        return $tf;
    }

    /** Split into sentences (keeps abbreviations like "e.g." together well enough for display). @return list<string> */
    public static function sentences(string $text): array
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));
        if ($text === '') {
            return [];
        }
        $parts = preg_split('/(?<=[.!?])\s+(?=[A-Z0-9"\x{201C}(])/u', $text) ?: [$text];
        return array_values(array_filter(array_map('trim', $parts), static fn(string $s): bool => $s !== ''));
    }

    /** Cut at a word boundary. */
    public static function clip(string $text, int $max): string
    {
        $text = trim($text);
        if (mb_strlen($text) <= $max) {
            return $text;
        }
        $cut = mb_substr($text, 0, $max);
        $sp = mb_strrpos($cut, ' ');
        return rtrim($sp !== false && $sp > $max * 0.6 ? mb_substr($cut, 0, $sp) : $cut, " ,;:-") . '...';
    }
}
