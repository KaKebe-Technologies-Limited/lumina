<?php
declare(strict_types=1);

namespace App\Services\Letters;

use App\Services\Membership\Card\PdfWriter;

/**
 * The official LETTER as a PDF (A4): the letterhead (logo, name, address, contact line, registration number; optional), the reference and the date, the addressee (all optional),
 * the salutation, "RE: SUBJECT", the body (paragraphs, bullet and numbered lists, "## headings"), the closing, the signature (a picture of the signatory's signature, the typed name in
 * italics, or a blank space to sign by hand), the sender's name, position and office, then "cc" and "Enclosures" (optional). It runs over as many pages as the body needs, each with
 * "Page n of N" and the registration number. Standard PDF fonts only (Latin letters). Layout is top-down: y grows downward (see PdfWriter).
 */
final class LetterPdf
{
    private const W = 595.28;
    private const H = 841.89;
    private const ML = 64.0;
    private const MR = 64.0;
    private const MT = 54.0;
    private const MB = 66.0;
    private const NAVY = '#0B1B33';
    private const GOLD = '#C9973F';
    private const GREY = '#5B6478';
    private const INK = '#1B2438';

    /** @var list<list<array<string,mixed>>> the drawing instructions of each page */
    private array $pages = [];
    /** @var array<string,mixed> */
    private array $L = [];
    private float $y = 0.0;

    /**
     * @param array<string,mixed> $letter a letters row
     * @param array{to_name?:string,salutation?:string,compress?:bool} $opts to_name / salutation replace the addressee for one recipient; compress=false is for tests (readable text in the PDF)
     */
    public static function build(array $letter, array $opts = []): string
    {
        return (new self())->render($letter, $opts);
    }

    private static function site(string $key, string $default = ''): string
    {
        return trim((string) setting($key, $default));
    }

    /** @param array<string,mixed> $L @param array<string,mixed> $opts */
    private function render(array $L, array $opts): string
    {
        $toName = array_key_exists('to_name', $opts) ? (string) $opts['to_name'] : (string) ($L['to_name'] ?? '');
        $salutation = array_key_exists('salutation', $opts) ? (string) $opts['salutation'] : (string) ($L['salutation'] ?? '');
        $maxW = self::W - self::ML - self::MR;
        $this->L = $L;

        $this->newPage(true, $L);

        // reference (left) and date (right)
        $ref = trim((string) ($L['ref_no'] ?? ''));
        $date = $this->longDate((string) ($L['letter_date'] ?? ''));
        if ($ref !== '') {
            $this->text(self::ML, $this->y, 'Our ref: ' . $ref, 'helvetica', 9.5, self::GREY);
        }
        $this->text(self::W - self::MR, $this->y, $date, 'helvetica', 10.5, self::INK, 'r');
        $this->y += 30;

        // addressee (every line optional)
        $to = array_values(array_filter([$toName, trim((string) ($L['to_title'] ?? '')), trim((string) ($L['to_org'] ?? ''))], static fn(string $s): bool => $s !== ''));
        foreach (preg_split('/\R/', trim((string) ($L['to_address'] ?? ''))) ?: [] as $line) {
            if (trim($line) !== '') {
                $to[] = trim($line);
            }
        }
        foreach ($to as $i => $line) {
            foreach ($this->wrap($line, $i === 0 && $toName !== '' ? 'helvetica-bold' : 'helvetica', 10.5, $maxW) as $w) {
                $this->line($w, $i === 0 && $toName !== '' ? 'helvetica-bold' : 'helvetica', 10.5, self::INK, 14.5);
            }
        }
        if ($to !== []) {
            $this->y += 14;
        }

        if (trim($salutation) !== '') {
            $this->line(trim($salutation), 'helvetica', 11, self::INK, 15);
            $this->y += 8;
        }

        // RE: subject
        $subject = mb_strtoupper(trim((string) ($L['subject'] ?? '')));
        foreach ($this->wrap('RE: ' . $subject, 'helvetica-bold', 11, $maxW) as $i => $w) {
            $this->ensure(18);
            $width = PdfWriter::textWidth($w, 'helvetica-bold', 11);
            $this->text(self::ML, $this->y, $w, 'helvetica-bold', 11, self::NAVY);
            $this->ops[] = ['t' => 'line', 'x1' => self::ML, 'y1' => $this->y + 2.5, 'x2' => self::ML + $width, 'y2' => $this->y + 2.5, 'c' => self::NAVY, 'w' => 0.8];
            $this->y += 16;
        }
        $this->y += 8;

        // body
        foreach ($this->blocks((string) ($L['body'] ?? '')) as $b) {
            if ($b['k'] === 'h') {
                $this->y += 4;
                foreach ($this->wrap($b['text'], 'helvetica-bold', 11, $maxW) as $w) {
                    $this->line($w, 'helvetica-bold', 11, self::NAVY, 15);
                }
                $this->y += 3;
            } elseif ($b['k'] === 'li') {
                $indent = 18.0;
                $lines = $this->wrap($b['text'], 'helvetica', 10.5, $maxW - $indent);
                foreach ($lines as $i => $w) {
                    $this->ensure(15);
                    if ($i === 0) {
                        $this->text(self::ML + 4, $this->y, $b['mark'], 'helvetica', 10.5, self::INK);
                    }
                    $this->text(self::ML + $indent, $this->y, $w, 'helvetica', 10.5, self::INK);
                    $this->y += 14.5;
                }
                $this->y += 3;
            } else {
                foreach ($this->wrap($b['text'], 'helvetica', 10.5, $maxW) as $w) {
                    $this->line($w, 'helvetica', 10.5, self::INK, 14.5);
                }
                $this->y += 8;
            }
        }

        // closing and signature (kept together on one page)
        $mode = (string) ($L['sig_mode'] ?? 'blank');
        $sigImg = $mode === 'image' ? $this->signatureImage($L) : null;
        $need = 150.0;
        $this->y += 6;
        if ($this->y + $need > self::H - self::MB) {
            $this->newPage(false, $L);
        }
        $closing = trim((string) ($L['closing'] ?? ''));
        if ($closing !== '') {
            $this->line($closing, 'helvetica', 11, self::INK, 15);
        }
        $this->y += 6;
        $sigH = 52.0;
        if ($sigImg !== null) {
            $this->ops[] = ['t' => 'image', 'bytes' => $sigImg['bytes'], 'png' => $sigImg['png'], 'x' => self::ML, 'y' => $this->y, 'w' => $sigImg['w'], 'h' => $sigImg['h']];
            $this->y += $sigImg['h'] + 4;
        } elseif ($mode === 'typed' && trim((string) ($L['from_name'] ?? '')) !== '') {
            $this->text(self::ML, $this->y + 30, trim((string) $L['from_name']), 'times-italic', 22, self::NAVY);
            $this->y += $sigH;
        } else {
            $this->y += $sigH;                          // a blank space to sign by hand
        }
        $name = trim((string) ($L['from_name'] ?? ''));
        if ($name !== '') {
            $this->line($name, 'helvetica-bold', 11, self::INK, 15);
        }
        foreach ([trim((string) ($L['from_title'] ?? '')), trim((string) ($L['from_office'] ?? ''))] as $line) {
            if ($line !== '') {
                foreach ($this->wrap($line, 'helvetica', 10.5, $maxW) as $w) {
                    $this->line($w, 'helvetica', 10.5, self::GREY, 14);
                }
            }
        }

        // cc and enclosures (optional)
        $cc = trim((string) ($L['cc'] ?? ''));
        $enc = trim((string) ($L['enclosures'] ?? ''));
        if ($cc !== '' || $enc !== '') {
            $this->y += 16;
            if ($enc !== '') {
                $this->line('Enclosures:', 'helvetica-bold', 9.5, self::INK, 13);
                foreach (preg_split('/\R/', $enc) ?: [] as $e) {
                    if (trim($e) !== '') {
                        foreach ($this->wrap('- ' . trim($e), 'helvetica', 9.5, $maxW) as $w) {
                            $this->line($w, 'helvetica', 9.5, self::INK, 13);
                        }
                    }
                }
                $this->y += 4;
            }
            if ($cc !== '') {
                $this->line('cc:', 'helvetica-bold', 9.5, self::INK, 13);
                foreach (preg_split('/\R/', $cc) ?: [] as $e) {
                    if (trim($e) !== '') {
                        foreach ($this->wrap(trim($e), 'helvetica', 9.5, $maxW) as $w) {
                            $this->line($w, 'helvetica', 9.5, self::INK, 13);
                        }
                    }
                }
            }
        }
        $this->flushPage();

        return $this->draw($L, $opts);
    }

    // ------------------------------------------------------------------------------------------------------------- layout helpers

    /** @var list<array<string,mixed>> the instructions of the page being laid out */
    private array $ops = [];

    private function newPage(bool $first, array $L): void
    {
        if ($this->ops !== [] || $this->pages !== []) {
            $this->flushPage();
        }
        $this->ops = [];
        $this->y = self::MT;
        if ($first && (int) ($L['use_letterhead'] ?? 1) === 1) {
            $this->letterhead();
        } elseif (!$first) {
            $ref = trim((string) ($L['ref_no'] ?? ''));
            $this->text(self::ML, $this->y + 6, ($ref !== '' ? $ref . '  |  ' : '') . mb_strimwidth((string) ($L['subject'] ?? ''), 0, 70, '...'), 'helvetica', 8.5, self::GREY);
            $this->y += 34;
        }
    }

    private function flushPage(): void
    {
        $this->pages[] = $this->ops;
        $this->ops = [];
    }

    private function ensure(float $h): void
    {
        if ($this->y + $h > self::H - self::MB) {
            $this->newPage(false, $this->L);
        }
    }

    private function text(float $x, float $y, string $t, string $font, float $size, string $color, string $align = 'l'): void
    {
        $this->ops[] = ['t' => 'text', 'x' => $x, 'y' => $y, 'txt' => $t, 'f' => $font, 's' => $size, 'c' => $color, 'a' => $align];
    }

    /** One line of text, then move down. */
    private function line(string $t, string $font, float $size, string $color, float $lead): void
    {
        $this->ensure($lead);
        $this->text(self::ML, $this->y, $t, $font, $size, $color);
        $this->y += $lead;
    }

    /** @return list<string> */
    private function wrap(string $text, string $font, float $size, float $max): array
    {
        $out = [];
        foreach (preg_split('/\R/', trim($text)) ?: [''] as $para) {
            $words = preg_split('/\s+/u', trim($para), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $line = '';
            foreach ($words as $w) {
                $try = $line === '' ? $w : $line . ' ' . $w;
                if (PdfWriter::textWidth($try, $font, $size) <= $max) {
                    $line = $try;
                    continue;
                }
                if ($line !== '') {
                    $out[] = $line;
                }
                while (PdfWriter::textWidth($w, $font, $size) > $max && mb_strlen($w) > 1) {   // a word longer than the line (a web address): cut it
                    $n = mb_strlen($w);
                    while ($n > 1 && PdfWriter::textWidth(mb_substr($w, 0, $n), $font, $size) > $max) {
                        $n--;
                    }
                    $out[] = mb_substr($w, 0, $n);
                    $w = mb_substr($w, $n);
                }
                $line = $w;
            }
            $out[] = $line;
        }
        return $out === [] ? [''] : $out;
    }

    /** The body as blocks: paragraphs (p), bullet / numbered items (li) and "## headings" (h). @return list<array<string,string>> */
    private function blocks(string $body): array
    {
        $blocks = [];
        $para = [];
        $flush = static function () use (&$para, &$blocks): void {
            if ($para !== []) {
                $blocks[] = ['k' => 'p', 'text' => implode(' ', $para)];
                $para = [];
            }
        };
        foreach (preg_split('/\R/', str_replace("\t", '    ', $body)) ?: [] as $raw) {
            $line = rtrim($raw);
            if (trim($line) === '') {
                $flush();
            } elseif (preg_match('/^##\s+(.+)$/u', $line, $m) === 1) {
                $flush();
                $blocks[] = ['k' => 'h', 'text' => trim($m[1])];
            } elseif (preg_match('/^\s*[-*\x{2022}]\s+(.+)$/u', $line, $m) === 1) {
                $flush();
                $blocks[] = ['k' => 'li', 'mark' => '-', 'text' => trim($m[1])];
            } elseif (preg_match('/^\s*(\d{1,2}[.)])\s+(.+)$/u', $line, $m) === 1) {
                $flush();
                $blocks[] = ['k' => 'li', 'mark' => $m[1], 'text' => trim($m[2])];
            } else {
                $para[] = trim($line);
            }
        }
        $flush();
        return $blocks;
    }

    private function longDate(string $d): string
    {
        $t = strtotime($d);
        return $t !== false ? date('j F Y', $t) : trim($d);
    }

    private function letterhead(): void
    {
        $name = self::site('site_name', 'Lumina Africa Foundation');
        $addr = implode(', ', array_filter([self::site('address'), self::site('city_country')]));
        $contact = implode('   |   ', array_filter([
            self::site('phone_1') !== '' ? 'Tel: ' . self::site('phone_1') : '',
            self::site('email_primary') !== '' ? 'E-mail: ' . self::site('email_primary') : '',
            (string) parse_url((string) \App\Core\Config::get('app.url', ''), PHP_URL_HOST) !== '' ? 'Web: ' . (string) parse_url((string) \App\Core\Config::get('app.url', ''), PHP_URL_HOST) : '',
        ]));
        $logo = ASSET_PATH . '/img/brand/logo-128.png';
        $x = self::ML;
        if (is_file($logo)) {
            $this->ops[] = ['t' => 'image', 'bytes' => (string) file_get_contents($logo), 'png' => true, 'x' => self::ML, 'y' => $this->y - 4, 'w' => 58.0, 'h' => 58.0];
            $x = self::ML + 70;
        }
        $this->text($x, $this->y + 18, $name, 'times-bold', 19, self::NAVY);
        $this->text($x, $this->y + 32, 'Igniting Light. Inspiring Change.', 'times-italic', 9.5, self::GOLD);
        $line = $this->y + 45;
        if ($addr !== '') {
            $this->text($x, $line, $addr, 'helvetica', 8.2, self::GREY);
            $line += 10.5;
        }
        if ($contact !== '') {
            $this->text($x, $line, $contact, 'helvetica', 8.2, self::GREY);
        }
        $this->y += 70;
        $this->ops[] = ['t' => 'line', 'x1' => self::ML, 'y1' => $this->y, 'x2' => self::W - self::MR, 'y2' => $this->y, 'c' => self::GOLD, 'w' => 1.6];
        $this->y += 26;
    }

    /** @return array{bytes:string,png:bool,w:float,h:float}|null the signatory's signature picture, scaled to fit about 150 x 52 points */
    private function signatureImage(array $L): ?array
    {
        $id = (int) ($L['signatory_id'] ?? 0);
        $row = $id > 0 ? \App\Core\Database::one('SELECT signature_path FROM document_signatories WHERE id = ?', [$id]) : null;
        $path = $row !== null ? trim((string) ($row['signature_path'] ?? '')) : '';
        if ($path === '' || str_contains($path, '..')) {
            return null;
        }
        $file = STORAGE_PATH . '/private/' . $path;
        $bytes = is_file($file) ? (string) file_get_contents($file) : '';
        $info = $bytes !== '' ? PdfWriter::imageInfo($bytes) : null;
        if ($info === null || $info['w'] < 1 || $info['h'] < 1) {
            return null;
        }
        $scale = min(160.0 / $info['w'], 56.0 / $info['h']);
        return ['bytes' => $bytes, 'png' => $info['mime'] === 'image/png', 'w' => round($info['w'] * $scale, 2), 'h' => round($info['h'] * $scale, 2)];
    }

    // ------------------------------------------------------------------------------------------------------------- drawing

    /** @param array<string,mixed> $L @param array<string,mixed> $opts */
    private function draw(array $L, array $opts): string
    {
        $pdf = new PdfWriter([
            'title' => (string) ($L['subject'] ?? 'Letter'), 'author' => self::site('site_name', 'Lumina Africa Foundation'), 'subject' => 'Official letter',
            'creator' => 'Lumina letters', 'compress' => (bool) ($opts['compress'] ?? true),
        ]);
        $total = count($this->pages);
        $reg = self::site('reg_number');
        $imgs = [];
        foreach ($this->pages as $n => $ops) {
            $pdf->addPage(self::W, self::H);
            foreach ($ops as $o) {
                switch ($o['t']) {
                    case 'text':
                        $pdf->text($o['x'], $o['y'], $o['txt'], $o['f'], $o['s'], $o['c'], $o['a']);
                        break;
                    case 'line':
                        $pdf->save();
                        $pdf->stroke($o['c']);
                        $pdf->lineWidth($o['w']);
                        $pdf->line($o['x1'], $o['y1'], $o['x2'], $o['y2']);
                        $pdf->restore();
                        break;
                    case 'image':
                        $key = md5($o['bytes']);
                        if (!isset($imgs[$key])) {
                            $imgs[$key] = $o['png'] ? $pdf->addPng($o['bytes']) : $pdf->addJpeg($o['bytes']);
                        }
                        $pdf->image($imgs[$key], $o['x'], $o['y'], $o['w'], $o['h']);
                        break;
                }
            }
            // footer: registration number (left), page n of N (right)
            $pdf->save();
            $pdf->stroke('#D8D2C0');
            $pdf->lineWidth(0.6);
            $pdf->line(self::ML, self::H - 44, self::W - self::MR, self::H - 44);
            $pdf->restore();
            $pdf->text(self::ML, self::H - 30, self::site('site_name', 'Lumina Africa Foundation') . ($reg !== '' ? '  |  Reg. No. ' . $reg : ''), 'helvetica', 7.8, self::GREY);
            $pdf->text(self::W - self::MR, self::H - 30, 'Page ' . ($n + 1) . ' of ' . $total, 'helvetica', 7.8, self::GREY, 'r');
        }
        return $pdf->output();
    }
}
