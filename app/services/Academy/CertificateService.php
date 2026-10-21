<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;
use App\Services\Membership\Card\PdfWriter;
use App\Services\Membership\Card\QrEncoder;

/**
 * Certificates of the LAF Academy: issued once per learner and course when the course is completed (see CourseService::checkCompletion), with a public,
 * unguessable-enough number (LAF-YY-XXXXXXXX) that anyone can check at /academy/certificate/{number}. The learner name and the course title are copied into the
 * certificate row, so it stays valid and printable if the account or the course is edited or deleted. An administrator can revoke a certificate (the check page
 * then says so) and reinstate it. The PDF is drawn with the dependency-free PdfWriter of the membership card (A4 landscape, a QR code to the check page).
 */
final class CertificateService
{
    private const CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public static function generateCode(): string
    {
        for ($try = 0; $try < 20; $try++) {
            $s = '';
            for ($i = 0; $i < 8; $i++) {
                $s .= self::CODE_ALPHABET[random_int(0, strlen(self::CODE_ALPHABET) - 1)];
            }
            $code = 'LAF-' . date('y') . '-' . $s;
            if (Database::value('SELECT 1 FROM academy_certificates WHERE code = ? LIMIT 1', [$code]) === null) {
                return $code;
            }
        }
        throw new \RuntimeException('Could not make a unique certificate number.');
    }

    public static function find(string $code): ?array
    {
        return preg_match('/^LAF-\d{2}-[A-Z2-9]{8}$/', $code) === 1
            ? Database::one('SELECT * FROM academy_certificates WHERE code = ? LIMIT 1', [$code])
            : null;
    }

    public static function activeFor(int $learnerId, int $courseId): ?array
    {
        return Database::one('SELECT * FROM academy_certificates WHERE learner_id = ? AND course_id = ? AND revoked_at IS NULL ORDER BY id DESC LIMIT 1', [$learnerId, $courseId]);
    }

    /** @return list<array<string,mixed>> */
    public static function forLearner(int $learnerId): array
    {
        return Database::all('SELECT * FROM academy_certificates WHERE learner_id = ? ORDER BY issued_at DESC', [$learnerId]);
    }

    /** @return array<string,mixed> the new (or the existing active) certificate */
    public static function issue(array $learner, array $course, ?float $score, int $minutes): array
    {
        $existing = self::activeFor((int) $learner['id'], (int) $course['id']);
        if ($existing !== null) {
            return $existing;
        }
        $id = Database::insert('academy_certificates', [
            'code' => self::generateCode(),
            'learner_id' => (int) $learner['id'],
            'course_id' => (int) $course['id'],
            'learner_name' => mb_substr((string) $learner['name'], 0, 120),
            'course_title' => mb_substr((string) $course['title'], 0, 190),
            'score_pct' => $score,
            'hours' => $minutes > 0 ? round($minutes / 60 * 2) / 2 : null,
        ]);
        return (array) Database::one('SELECT * FROM academy_certificates WHERE id = ?', [$id]);
    }

    public static function revoke(int $id, string $reason): void
    {
        Database::update('academy_certificates', ['revoked_at' => date('Y-m-d H:i:s'), 'revoked_reason' => mb_substr(trim($reason), 0, 255) ?: null], 'id = ?', [$id]);
    }

    public static function reinstate(int $id): void
    {
        Database::update('academy_certificates', ['revoked_at' => null, 'revoked_reason' => null], 'id = ?', [$id]);
    }

    public static function verifyUrl(string $code): string
    {
        return abs_url('academy/certificate/' . $code);
    }

    // ------------------------------------------------------------------------------------------------------------- PDF

    /** The wording printed on certificates (settings), with the placeholders the system fills. @return array{org:string,statement:string,signatory:string,title:string} */
    public static function wording(): array
    {
        return [
            'org' => trim((string) setting('academy_org_name', 'Lumina Africa Foundation')) ?: 'Lumina Africa Foundation',
            'statement' => trim((string) setting('academy_cert_statement', 'has successfully completed the course and passed the final assessment of the LAF Academy.')),
            'signatory' => trim((string) setting('academy_cert_signatory', '')),
            'title' => trim((string) setting('academy_cert_signatory_title', 'Director')),
        ];
    }

    /** @return list<string> words wrapped to $max points */
    private static function wrap(string $text, string $font, float $size, float $max): array
    {
        $lines = [];
        $line = '';
        foreach (preg_split('/\s+/u', trim($text)) ?: [] as $w) {
            $try = $line === '' ? $w : $line . ' ' . $w;
            if ($line !== '' && PdfWriter::textWidth($try, $font, $size) > $max) {
                $lines[] = $line;
                $line = $w;
            } else {
                $line = $try;
            }
        }
        if ($line !== '') {
            $lines[] = $line;
        }
        return $lines;
    }

    /** Largest size <= $size at which $text fits in $max points. */
    private static function fit(string $text, string $font, float $size, float $max, float $min = 14.0): float
    {
        while ($size > $min && PdfWriter::textWidth($text, $font, $size) > $max) {
            $size -= 1.0;
        }
        return $size;
    }

    /** @param array<string,mixed> $cert a certificate row; returns the PDF bytes */
    public static function pdf(array $cert): string
    {
        $w = self::wording();
        $navy = '#1F2A44';
        $gold = '#B7852F';
        $ink = '#2B2F36';
        $pdf = new PdfWriter(['title' => 'Certificate ' . $cert['code'], 'author' => $w['org'], 'subject' => 'LAF Academy certificate of completion']);
        $pdf->addPage(841.89, 595.28);
        $W = $pdf->pageWidth();
        $H = $pdf->pageHeight();
        $cx = $W / 2;

        $pdf->fill('#FFFFFF');
        $pdf->rect(0, 0, $W, $H, 'f');
        $pdf->fill('#FBF7EE');
        $pdf->rect(22, 22, $W - 44, $H - 44, 'f');
        $pdf->save();
        $pdf->stroke($navy);
        $pdf->lineWidth(4);
        $pdf->rect(18, 18, $W - 36, $H - 36, 's');
        $pdf->stroke($gold);
        $pdf->lineWidth(1.2);
        $pdf->rect(30, 30, $W - 60, $H - 60, 's');
        $pdf->restore();
        // corner marks
        $pdf->fill($gold);
        foreach ([[30, 30], [$W - 30, 30], [30, $H - 30], [$W - 30, $H - 30]] as [$px, $py]) {
            $pdf->roundRect($px - 5, $py - 5, 10, 10, 5, 'f');
        }

        // logo
        $y = 66.0;
        try {
            $file = ASSET_PATH . '/img/brand/logo-192.png';
            if (is_file($file)) {
                $img = $pdf->addPng((string) file_get_contents($file));
                $pdf->image($img, $cx - 27, 48, 54, 54);
                $y = 122.0;
            }
        } catch (\Throwable) {
            $y = 66.0;
        }
        $pdf->text($cx, $y + 4, mb_strtoupper($w['org']) . '  -  LAF ACADEMY', 'helvetica-bold', 11, $gold, 'c');
        $pdf->text($cx, $y + 44, 'CERTIFICATE OF COMPLETION', 'times-bold', 33, $navy, 'c');
        $pdf->save();
        $pdf->stroke($gold);
        $pdf->lineWidth(1);
        $pdf->line($cx - 90, $y + 58, $cx + 90, $y + 58);
        $pdf->restore();
        $pdf->text($cx, $y + 86, 'This is to certify that', 'times-italic', 15, $ink, 'c');

        $name = (string) $cert['learner_name'];
        $size = self::fit($name, 'times-bold', 40, $W - 200, 20);
        $pdf->text($cx, $y + 132, $name, 'times-bold', $size, $navy, 'c');
        $pdf->save();
        $pdf->stroke($ink);
        $pdf->lineWidth(0.6);
        $nameW = min($W - 200, max(260, PdfWriter::textWidth($name, 'times-bold', $size) + 40));
        $pdf->line($cx - $nameW / 2, $y + 142, $cx + $nameW / 2, $y + 142);
        $pdf->restore();

        $yy = $y + 172;
        foreach (self::wrap($w['statement'], 'times-roman', 15, $W - 260) as $line) {
            $pdf->text($cx, $yy, $line, 'times-roman', 15, $ink, 'c');
            $yy += 20;
        }
        $title = (string) $cert['course_title'];
        $tsize = 25.0;
        $tlines = self::wrap($title, 'helvetica-bold', $tsize, $W - 220);
        while (count($tlines) > 2 && $tsize > 16) {
            $tsize -= 1.5;
            $tlines = self::wrap($title, 'helvetica-bold', $tsize, $W - 220);
        }
        $yy += 14;
        foreach ($tlines as $line) {
            $pdf->text($cx, $yy, $line, 'helvetica-bold', $tsize, $navy, 'c');
            $yy += $tsize + 6;
        }
        $bits = [];
        if ($cert['score_pct'] !== null) {
            $bits[] = 'Final score ' . rtrim(rtrim(number_format((float) $cert['score_pct'], 1), '0'), '.') . '%';
        }
        if ($cert['hours'] !== null) {
            $bits[] = rtrim(rtrim(number_format((float) $cert['hours'], 1), '0'), '.') . ' learning hours';
        }
        $bits[] = 'Issued ' . date('j F Y', (int) strtotime((string) $cert['issued_at']));
        $pdf->text($cx, $yy + 14, implode('   |   ', $bits), 'helvetica', 11.5, '#4A5360', 'c');

        // signature (left) and QR (right)
        $by = $H - 92;
        $pdf->save();
        $pdf->stroke($ink);
        $pdf->lineWidth(0.7);
        $pdf->line(70, $by, 260, $by);
        $pdf->restore();
        if ($w['signatory'] !== '') {
            $pdf->text(70, $by + 14, $w['signatory'], 'helvetica-bold', 11, $ink, 'l');
            $pdf->text(70, $by + 27, $w['title'], 'helvetica', 10, '#4A5360', 'l');
        } else {
            $pdf->text(70, $by + 14, $w['title'], 'helvetica-bold', 11, $ink, 'l');
        }

        $url = self::verifyUrl((string) $cert['code']);
        try {
            $qr = QrEncoder::encode($url, 'M');
            $n = count($qr['modules']);
            $mod = 60.0 / $n;
            $qx = $W - 70 - 60.0;
            $qy = $H - 150.0;
            $pdf->fill('#FFFFFF');
            $pdf->rect($qx - 4, $qy - 4, 68, 68, 'f');
            $pdf->fill('#000000');
            foreach (QrEncoder::runs($qr['modules']) as [$r, $c, $len]) {
                $pdf->rect($qx + $c * $mod - 0.01, $qy + $r * $mod - 0.01, $len * $mod + 0.02, $mod + 0.02, 'f');
            }
        } catch (\Throwable) {
            // no QR: the printed number and address still verify the certificate
        }
        $pdf->text($W - 70 - 30, $H - 76, 'Scan to verify', 'helvetica', 8.5, '#4A5360', 'c');
        $pdf->text($cx, $H - 50, 'Certificate no. ' . $cert['code'], 'helvetica-bold', 10, $navy, 'c');
        $pdf->text($cx, $H - 38, 'Verify at ' . preg_replace('~^https?://~', '', $url), 'helvetica', 8.5, '#4A5360', 'c');
        return $pdf->output();
    }
}
