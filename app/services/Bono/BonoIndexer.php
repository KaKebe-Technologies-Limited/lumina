<?php
declare(strict_types=1);

namespace App\Services\Bono;

use App\Core\Database;
use App\Core\Log;
use App\Services\Export\Letterhead;
use App\Services\Web\SitemapBuilder;

/**
 * Bono AI: how it learns the website. It reads every public page listed in the sitemap exactly as a visitor sees it (never a private or admin page), cuts the text into
 * small passages under their headings and stores them with their word stems. A page whose text did not change costs nothing; a changed page is learned again; a page that
 * left the sitemap is forgotten. It also learns the organisation's contact details and the Academy rules straight from the settings.
 *
 * Run it every day with the cron job bin/bono-learn.php, or press "Learn the website now" in Admin > Bono AI. Pages are taken oldest-first, so a big site is finished over a
 * few runs when the time budget of one run is short.
 */
final class BonoIndexer
{
    public const FACTS_URL = 'bono://facts';

    /** Tests replace the fetch: fn(string $url): ?array{status:int,body:string}. */
    public static ?\Closure $fetch = null;

    /** Tests may give the list of addresses instead of reading the sitemap. @var list<string>|null */
    public static ?array $urls = null;

    public static function ready(): bool
    {
        static $ok = null;
        if ($ok === null) {
            try {
                $ok = (int) Database::value("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name IN ('bono_pages', 'bono_chunks', 'bono_taught', 'bono_log')", [], 0) === 4;
            } catch (\Throwable) {
                $ok = false;
            }
        }
        return $ok;
    }

    // ------------------------------------------------------------------------------------------------------------- what to read

    /** Public addresses of this site that Bono may read (from the sitemap). @return list<string> */
    public static function addresses(): array
    {
        $list = self::$urls;
        if ($list === null) {
            $xml = SitemapBuilder::xml();
            preg_match_all('~<loc>([^<]+)</loc>~', $xml, $m);
            $list = array_map(static fn(string $u): string => html_entity_decode(trim($u), ENT_QUOTES | ENT_XML1, 'UTF-8'), $m[1]);
        }
        $site = rtrim(abs_url(''), '/');
        $host = strtolower((string) parse_url($site, PHP_URL_HOST));
        // BONO_BASE_URL (optional .env line) is where the learner reads the pages from when that differs from the public address, for example a local copy in a sub-folder
        $read = rtrim(trim((string) (\App\Core\Env::get('BONO_BASE_URL', '') ?? '')), '/');
        $out = [];
        foreach ($list as $u) {
            $p = parse_url($u);
            if (!is_array($p) || strtolower((string) ($p['host'] ?? '')) !== $host || self::private((string) ($p['path'] ?? '/'))) {
                continue;
            }
            $u = strtok($u, '#');
            if ($read !== '' && str_starts_with($u, $site)) {
                $u = $read . substr($u, strlen($site));
            }
            $out[$u] = true;
        }
        return array_slice(array_keys($out), 0, 800);
    }

    /** Pages that belong to one person or to the administrators: never learned, whatever the sitemap says. */
    public static function private(string $path): bool
    {
        return preg_match('~/(admin|bono|unsubscribe|meeting|shop/(cart|checkout|order|pay|webhook)|membership/(me|verify|confirmation)|academy/(learn|dashboard|account|verify|reset|check-email|pending|certificate|google|login|register))(/|$)~', $path) === 1;
    }

    // ------------------------------------------------------------------------------------------------------------- reading one page

    /** @return array{status:int,body:string}|null */
    private static function get(string $url): ?array
    {
        if (self::$fetch !== null) {
            return (self::$fetch)($url);
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_CONNECTTIMEOUT => 8, CURLOPT_TIMEOUT => 20, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'BonoAI-Learner/1.0', CURLOPT_HTTPHEADER => ['Accept: text/html'], CURLOPT_MAXFILESIZE => 3 * 1024 * 1024,
        ]);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $type = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        if ($body === false || !str_contains(strtolower($type), 'text/html')) {
            return null;
        }
        return ['status' => $status, 'body' => (string) $body];
    }

    /**
     * The readable part of a page: its title and the passages under each heading. Menus, header, footer, forms, scripts and anything hidden are left out.
     * @return array{title:string,noindex:bool,sections:list<array{heading:string,anchor:string,text:string}>}
     */
    public static function extract(string $html): array
    {
        $dom = new \DOMDocument();
        $prev = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_COMPACT);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        $xp = new \DOMXPath($dom);
        $noindex = false;
        foreach ($xp->query('//meta[@name="robots"]') ?: [] as $m) {
            $noindex = $noindex || str_contains(strtolower((string) $m->getAttribute('content')), 'noindex');
        }
        $title = '';
        $h1 = $xp->query('//main//h1')->item(0) ?? $xp->query('//h1')->item(0);
        if ($h1 !== null) {
            $title = trim((string) preg_replace('/\s+/u', ' ', $h1->textContent));
        }
        if ($title === '') {
            $t = $xp->query('//title')->item(0);
            $title = $t !== null ? trim((string) preg_replace('/\s*[|\-].*$/u', '', $t->textContent)) : '';
        }
        $root = $xp->query('//main')->item(0) ?? $xp->query('//body')->item(0);
        if ($root === null) {
            return ['title' => $title, 'noindex' => $noindex, 'sections' => []];
        }
        foreach ($xp->query('.//script|.//style|.//noscript|.//nav|.//form|.//footer|.//header|.//button|.//svg|.//select|.//textarea|.//*[@hidden]|.//*[@aria-hidden="true"]|.//*[contains(@class,"visually-hidden")]', $root) ?: [] as $n) {
            $n->parentNode?->removeChild($n);
        }
        $meta = $xp->query('//meta[@name="description"]')->item(0);
        $sections = [];
        $cur = ['heading' => $title, 'anchor' => '', 'text' => ''];
        if ($meta !== null && trim((string) $meta->getAttribute('content')) !== '') {
            $cur['text'] = trim((string) $meta->getAttribute('content')) . "\n";
        }
        $walk = static function (\DOMNode $node) use (&$walk, &$cur, &$sections): void {
            foreach ($node->childNodes as $c) {
                if ($c instanceof \DOMText) {
                    $t = trim((string) preg_replace('/\s+/u', ' ', $c->textContent));
                    if ($t !== '') {
                        $cur['text'] .= $t . ' ';
                    }
                    continue;
                }
                if (!$c instanceof \DOMElement) {
                    continue;
                }
                $tag = strtolower($c->tagName);
                if (in_array($tag, ['h1', 'h2', 'h3', 'h4'], true)) {
                    if (trim($cur['text']) !== '') {
                        $sections[] = $cur;
                    }
                    $h = trim((string) preg_replace('/\s+/u', ' ', $c->textContent));
                    $cur = ['heading' => $h, 'anchor' => (string) $c->getAttribute('id'), 'text' => ''];
                    continue;
                }
                $walk($c);
                if (in_array($tag, ['p', 'li', 'div', 'section', 'article', 'tr', 'dd', 'dt', 'blockquote', 'br'], true)) {
                    $cur['text'] = rtrim($cur['text']) . "\n";
                }
            }
        };
        $walk($root);
        if (trim($cur['text']) !== '') {
            $sections[] = $cur;
        }
        // the page description and the passage under the first heading are one section
        if (count($sections) > 1 && $sections[0]['heading'] === $sections[1]['heading'] && $sections[0]['anchor'] === '') {
            $sections[1]['text'] = $sections[0]['text'] . $sections[1]['text'];
            array_shift($sections);
        }
        $out = [];
        foreach ($sections as $s) {
            $text = trim((string) preg_replace("/[ \t]+/", ' ', (string) preg_replace("/\n\s*\n+/", "\n", $s['text'])));
            if (mb_strlen($text) >= 25) {
                $out[] = ['heading' => mb_substr($s['heading'], 0, 250), 'anchor' => mb_substr($s['anchor'], 0, 100), 'text' => $text];
            }
        }
        return ['title' => mb_substr($title, 0, 250), 'noindex' => $noindex, 'sections' => $out];
    }

    /** Cut a section into passages of about 900 characters (whole sentences). @return list<string> */
    public static function passages(string $text, int $max = 900): array
    {
        $text = trim($text);
        if (mb_strlen($text) <= $max) {
            return [$text];
        }
        $out = [];
        $cur = '';
        foreach (preg_split("/\n+/", $text) ?: [] as $line) {
            foreach (mb_strlen($line) > $max ? BonoText::sentences($line) : [$line] as $piece) {
                if ($cur !== '' && mb_strlen($cur) + mb_strlen($piece) + 1 > $max) {
                    $out[] = trim($cur);
                    $cur = '';
                }
                $cur .= ($cur === '' ? '' : "\n") . $piece;
            }
        }
        if (trim($cur) !== '') {
            $out[] = trim($cur);
        }
        return $out;
    }

    // ------------------------------------------------------------------------------------------------------------- storing

    /** Replace everything Bono knows about one address. Returns the number of passages stored (0 when nothing changed or nothing readable). */
    private static function store(string $url, string $title, array $sections, bool $force): int
    {
        $hash = sha1(json_encode($sections, JSON_UNESCAPED_UNICODE) . '|' . $title);
        $uh = sha1($url);
        $row = Database::one('SELECT id, content_hash FROM bono_pages WHERE url_hash = ?', [$uh]);
        $now = date('Y-m-d H:i:s');
        if ($row !== null && $row['content_hash'] === $hash && !$force) {
            Database::update('bono_pages', ['indexed_at' => $now], 'id = ?', [(int) $row['id']]);
            return 0;
        }
        if ($row === null) {
            $pid = (int) Database::insert('bono_pages', ['url_hash' => $uh, 'url' => mb_substr($url, 0, 500), 'title' => $title, 'content_hash' => $hash, 'indexed_at' => $now, 'changed_at' => $now]);
        } else {
            $pid = (int) $row['id'];
            Database::update('bono_pages', ['title' => $title, 'content_hash' => $hash, 'indexed_at' => $now, 'changed_at' => $now], 'id = ?', [$pid]);
            Database::execute('DELETE FROM bono_chunks WHERE page_id = ?', [$pid]);
        }
        $n = 0;
        foreach ($sections as $s) {
            foreach (self::passages((string) $s['text']) as $body) {
                $tf = BonoText::termFrequency($s['heading'] . ' ' . $s['heading'] . ' ' . $body);       // the heading counts double
                if ($tf === []) {
                    continue;
                }
                Database::insert('bono_chunks', [
                    'page_id' => $pid, 'heading' => $s['heading'], 'anchor' => $s['anchor'], 'body' => $body,
                    'stems' => ' ' . implode(' ', array_keys($tf)) . ' ', 'tf' => json_encode($tf), 'len' => min(65000, array_sum($tf)),
                ]);
                $n++;
            }
        }
        return $n;
    }

    /** What the settings say about the organisation and the Academy, as a page of its own. @return array{title:string,sections:list<array{heading:string,anchor:string,text:string}>} */
    public static function facts(): array
    {
        $lh = Letterhead::get();
        $s = [];
        $contact = array_filter([
            $lh['org'] . ($lh['tagline'] !== '' ? ': ' . $lh['tagline'] : ''),
            $lh['address'] !== '' ? 'Address: ' . $lh['address'] . '.' : '',
            $lh['email'] !== '' ? 'E-mail: ' . $lh['email'] . '.' : '',
            $lh['phones'] !== '' ? 'Phone: ' . $lh['phones'] . '.' : '',
            ($wa = trim((string) setting('whatsapp', ''))) !== '' ? 'WhatsApp: ' . $wa . '.' : '',
            $lh['reg'] !== '' ? $lh['reg'] . '.' : '',
        ]);
        $s[] = ['heading' => 'Where we are, our office address, phone number, e-mail and WhatsApp contact', 'anchor' => '', 'text' => implode("\n", $contact)];
        $support = \App\Services\Academy\LearnerAuth::supportWhatsapp();
        $s[] = ['heading' => 'What is the LAF Academy and how to join, register or sign up', 'anchor' => '', 'text' => 'The LAF Academy is the free ICT learning portal of ' . $lh['org'] . ': free courses, virtual classes, quizzes and certificates. To join, create a free account on the Academy sign-up page with your name, e-mail address, password, phone number, country and WhatsApp number. A picture or selfie is optional.'];
        $mode = \App\Services\Academy\LearnerAuth::signupMode();
        $appr = $mode === 'approval'
            ? 'New Academy accounts are approved by the Academy team, which takes about ' . \App\Services\Academy\LearnerAuth::approvalText() . '. Please sign in after that. You do not need to confirm your e-mail address.'
            : ($mode === 'email' ? 'After you sign up, confirm your e-mail address with the link or the 6-digit code that is sent to you, or wait for the Academy team to approve your account.' : 'After you sign up you can sign in at once.');
        if ($support !== null) {
            $appr .= "\nFor quick inquiries about your approval, contact " . $support['display'] . ' on WhatsApp.';
        }
        $s[] = ['heading' => 'Academy account approval: how long it takes and how to sign in', 'anchor' => '', 'text' => $appr];
        $s[] = ['heading' => 'Academy certificates: when you receive your certificate', 'anchor' => '', 'text' => 'When you pass the final exam your certificate is prepared: it waits for approval and signing by the Academy administrator and is shared with you only after that. Please check your dashboard again after ' . \App\Services\Academy\CertificateService::waitHours() . ' hours. You can download it and anyone can check it with its number on the certificate check page.'];
        return ['title' => 'About ' . $lh['org'] . ' and how to reach us', 'sections' => $s];
    }

    // ------------------------------------------------------------------------------------------------------------- the daily run

    /**
     * Learn the website. @return array{pages:int,updated:int,passages:int,removed:int,errors:int,left:int,done:bool,seconds:float}
     */
    public static function run(int $budgetSeconds = 50, bool $force = false): array
    {
        $t0 = microtime(true);
        $res = ['pages' => 0, 'updated' => 0, 'passages' => 0, 'removed' => 0, 'errors' => 0, 'left' => 0, 'done' => true, 'seconds' => 0.0];
        if (!self::ready()) {
            return $res;
        }
        @set_time_limit($budgetSeconds + 30);
        $f = self::facts();
        $res['passages'] += self::store(self::FACTS_URL, $f['title'], $f['sections'], true);
        $res['updated']++;

        $urls = self::addresses();
        // oldest first: addresses never read, then the least recently read
        $seen = [];
        foreach (Database::all('SELECT url, indexed_at FROM bono_pages') as $r) {
            $seen[(string) $r['url']] = (string) $r['indexed_at'];
        }
        usort($urls, static fn(string $a, string $b): int => strcmp($seen[$a] ?? '', $seen[$b] ?? ''));
        // a full "force" run re-reads everything this run; otherwise a page read in the last 20 hours is left for the next run
        $fresh = date('Y-m-d H:i:s', time() - 20 * 3600);
        $todo = $force ? $urls : array_values(array_filter($urls, static fn(string $u): bool => !isset($seen[$u]) || $seen[$u] < $fresh));
        foreach ($todo as $i => $url) {
            if (microtime(true) - $t0 > $budgetSeconds) {
                $res['left'] = count($todo) - $i;
                $res['done'] = false;
                break;
            }
            $r = self::get($url);
            if ($r === null || $r['status'] >= 500) {
                $res['errors']++;
                continue;
            }
            $res['pages']++;
            if ($r['status'] === 404 || $r['status'] === 410) {
                self::forget($url);
                $res['removed']++;
                continue;
            }
            if ($r['status'] !== 200) {
                continue;
            }
            $x = self::extract($r['body']);
            if ($x['noindex'] || $x['sections'] === []) {
                self::forget($url);
                continue;
            }
            $n = self::store($url, $x['title'], $x['sections'], $force);
            if ($n > 0) {
                $res['updated']++;
                $res['passages'] += $n;
            }
        }
        if ($res['done']) {
            // forget what is no longer in the sitemap (a deleted page)
            $keep = array_fill_keys($urls, true) + [self::FACTS_URL => true];
            foreach (Database::all('SELECT url FROM bono_pages') as $r) {
                if (!isset($keep[(string) $r['url']])) {
                    self::forget((string) $r['url']);
                    $res['removed']++;
                }
            }
        }
        Database::execute("DELETE FROM bono_log WHERE created_at < ?", [date('Y-m-d H:i:s', time() - 180 * 86400)]);
        $res['seconds'] = round(microtime(true) - $t0, 1);
        try {
            \App\Core\Settings::set('bono_last_learned', date('Y-m-d H:i:s'));
            \App\Core\Settings::flush();
        } catch (\Throwable $e) {
            Log::warning('Bono: could not save the learning time: ' . $e->getMessage());
        }
        return $res;
    }

    private static function forget(string $url): void
    {
        Database::execute('DELETE FROM bono_pages WHERE url_hash = ?', [sha1($url)]);
    }

    /** @return array{pages:int,passages:int,taught:int,asked:int,unanswered:int,last:?string} */
    public static function stats(): array
    {
        if (!self::ready()) {
            return ['pages' => 0, 'passages' => 0, 'taught' => 0, 'asked' => 0, 'unanswered' => 0, 'last' => null];
        }
        $last = trim((string) setting('bono_last_learned', ''));
        return [
            'pages' => (int) Database::value('SELECT COUNT(*) FROM bono_pages', [], 0),
            'passages' => (int) Database::value('SELECT COUNT(*) FROM bono_chunks', [], 0),
            'taught' => (int) Database::value('SELECT COUNT(*) FROM bono_taught WHERE is_active = 1', [], 0),
            'asked' => (int) Database::value('SELECT COUNT(*) FROM bono_log', [], 0),
            'unanswered' => (int) Database::value('SELECT COUNT(*) FROM bono_log WHERE outcome = 0 AND handled = 0', [], 0),
            'last' => $last !== '' ? $last : null,
        ];
    }
}
