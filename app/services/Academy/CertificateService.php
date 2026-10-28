<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;
use App\Core\Mailer;
use App\Core\RateLimit;
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

    /** True when the approval columns exist (the code can be uploaded before the database update: then every certificate counts as approved, as before). */
    public static function approvalReady(): bool
    {
        static $ok = null;
        if ($ok === null) {
            try {
                $ok = (int) Database::value("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_certificates' AND column_name IN ('approved_at', 'approved_by', 'emailed_at')", [], 0) === 3;
            } catch (\Throwable) {
                $ok = false;
            }
        }
        return $ok;
    }

    /** Setting: certificates earned by passing an exam wait until an administrator approves them (default). */
    public static function approvalRequired(): bool
    {
        return self::approvalReady() && (string) setting('academy_cert_approval', '1') === '1';
    }

    /** Approved = available to the learner and checkable by the public. */
    public static function isApproved(array $cert): bool
    {
        return !self::approvalReady() || ($cert['approved_at'] ?? null) !== null;
    }

    public static function find(string $code): ?array
    {
        return preg_match('/^LAF-\d{2}-[A-Z2-9]{8}$/', $code) === 1
            ? Database::one('SELECT * FROM academy_certificates WHERE code = ? LIMIT 1', [$code])
            : null;
    }

    private static function approvedSql(string $alias = ''): string
    {
        return self::approvalReady() ? ' AND ' . $alias . 'approved_at IS NOT NULL' : '';
    }

    /** The learner's certificate for the course that is approved and not withdrawn (what the learner may open and download). */
    public static function activeFor(int $learnerId, int $courseId): ?array
    {
        return Database::one('SELECT * FROM academy_certificates WHERE learner_id = ? AND course_id = ? AND revoked_at IS NULL' . self::approvedSql() . ' ORDER BY id DESC LIMIT 1', [$learnerId, $courseId]);
    }

    /** A certificate that exists for the course but still waits for approval. */
    public static function pendingFor(int $learnerId, int $courseId): ?array
    {
        return self::approvalReady()
            ? Database::one('SELECT * FROM academy_certificates WHERE learner_id = ? AND course_id = ? AND revoked_at IS NULL AND approved_at IS NULL ORDER BY id DESC LIMIT 1', [$learnerId, $courseId])
            : null;
    }

    /** Any certificate (approved or waiting) that is not withdrawn: used so the same one is never issued twice. */
    public static function existingFor(int $learnerId, int $courseId): ?array
    {
        return Database::one('SELECT * FROM academy_certificates WHERE learner_id = ? AND course_id = ? AND revoked_at IS NULL ORDER BY id DESC LIMIT 1', [$learnerId, $courseId]);
    }

    /** @return list<array<string,mixed>> approved ones by default (the learner's view); all of them for administrators */
    public static function forLearner(int $learnerId, bool $approvedOnly = true): array
    {
        return Database::all('SELECT * FROM academy_certificates WHERE learner_id = ?' . ($approvedOnly ? self::approvedSql() : '') . ' ORDER BY issued_at DESC', [$learnerId]);
    }

    /** @return list<array<string,mixed>> the learner's certificates that wait for approval */
    public static function pendingForLearner(int $learnerId): array
    {
        return self::approvalReady() ? Database::all('SELECT * FROM academy_certificates WHERE learner_id = ? AND approved_at IS NULL AND revoked_at IS NULL ORDER BY issued_at DESC', [$learnerId]) : [];
    }

    public static function pendingCount(): int
    {
        return self::approvalReady() ? (int) Database::value('SELECT COUNT(*) FROM academy_certificates WHERE approved_at IS NULL AND revoked_at IS NULL', [], 0) : 0;
    }

    /**
     * Approve a waiting certificate: from now on the learner can open and download it and the public check page accepts it. $email also sends the learner the e-mail
     * with the link to the certificate and the PDF. @return array{ok:bool,mailed:bool}
     */
    public static function approve(int $id, ?int $adminId, bool $email): array
    {
        $c = Database::one('SELECT * FROM academy_certificates WHERE id = ?', [$id]);
        if ($c === null || !self::approvalReady()) {
            return ['ok' => false, 'mailed' => false];
        }
        if ($c['approved_at'] === null) {
            Database::update('academy_certificates', ['approved_at' => date('Y-m-d H:i:s'), 'approved_by' => $adminId], 'id = ?', [$id]);
            $c = (array) Database::one('SELECT * FROM academy_certificates WHERE id = ?', [$id]);
        }
        return ['ok' => true, 'mailed' => $email ? self::email($c) : false];
    }

    /** May this learner request a certificate for the course now? Finished (lessons + exam passed), certificates on for the course, and none exists yet. */
    public static function eligible(int $learnerId, int $courseId): bool
    {
        $course = CourseService::find($courseId);
        if ($course === null || (int) $course['certificate_enabled'] !== 1 || self::existingFor($learnerId, $courseId) !== null) {
            return false;
        }
        $enr = CourseService::enrollment($learnerId, $courseId);
        return $enr !== null && $enr['completed_at'] !== null;
    }

    /**
     * The learner asks for the certificate after passing. With approval on it waits for the Academy team (who are told by e-mail); with approval off it is issued and
     * e-mailed at once. @return array{ok:bool,error:?string,cert:?array<string,mixed>,waiting:bool}
     */
    public static function request(array $learner, array $course): array
    {
        $lid = (int) $learner['id'];
        $cid = (int) $course['id'];
        if (!RateLimit::attempt('academy-cert-request:' . $lid, 10, 3600)) {
            return ['ok' => false, 'error' => 'You asked several times already. Please wait a little.', 'cert' => null, 'waiting' => false];
        }
        $existing = self::existingFor($lid, $cid);
        if ($existing !== null) {
            return ['ok' => false, 'error' => self::isApproved($existing) ? 'You already have this certificate.' : 'Your request is already waiting for approval.', 'cert' => $existing, 'waiting' => !self::isApproved($existing)];
        }
        if ((int) $course['certificate_enabled'] !== 1) {
            return ['ok' => false, 'error' => 'This course does not give a certificate.', 'cert' => null, 'waiting' => false];
        }
        $enr = CourseService::enrollment($lid, $cid);
        // finished = recorded as completed (lessons done + exam passed, noted at the time), or the conditions hold right now (this also records it)
        if ($enr === null || ($enr['completed_at'] === null && CourseService::completion($lid, $cid) === null)) {
            return ['ok' => false, 'error' => 'Finish every lesson and pass the final exam first, then you can request your certificate.', 'cert' => null, 'waiting' => false];
        }
        $tree = CourseService::tree($cid);
        $best = $tree['exam'] !== null ? QuizService::passedBest($lid, (int) $tree['exam']['id']) : null;
        $cert = self::issue($learner, $course, $best !== null ? (float) $best['score_pct'] : null, (int) $tree['minutes']);
        if (self::isApproved($cert)) {
            self::email($cert);
            return ['ok' => true, 'error' => null, 'cert' => $cert, 'waiting' => false];
        }
        try {
            Mailer::notify('Certificate request: ' . mb_substr((string) $learner['name'], 0, 60) . ' - ' . mb_substr((string) $course['title'], 0, 80),
                $learner['name'] . ' (' . $learner['email'] . ") passed \"" . $course['title'] . "\" and asked for the certificate.\n\nApprove it here: " . abs_url('admin/academy/certificates?status=waiting'), (string) $learner['email']);
        } catch (\Throwable) {
            // the request is saved: the team still sees it on the Certificates page
        }
        return ['ok' => true, 'error' => null, 'cert' => $cert, 'waiting' => true];
    }

    /** Approve every certificate of one learner that is waiting. @return int how many were approved */
    public static function approveForLearner(int $learnerId, ?int $adminId, bool $email): int
    {
        $n = 0;
        foreach (self::pendingForLearner($learnerId) as $c) {
            $n += self::approve((int) $c['id'], $adminId, $email)['ok'] ? 1 : 0;
        }
        return $n;
    }

    /** Decline a waiting request: it is removed (the learner can ask again later) and the learner is told why. @return bool true when a waiting certificate was declined */
    public static function decline(int $id, string $reason): bool
    {
        $c = Database::one('SELECT * FROM academy_certificates WHERE id = ?', [$id]);
        if ($c === null || !self::approvalReady() || $c['approved_at'] !== null) {
            return false;
        }
        Database::execute('DELETE FROM academy_certificates WHERE id = ? AND approved_at IS NULL', [$id]);
        $l = $c['learner_id'] !== null ? Database::one('SELECT * FROM academy_learners WHERE id = ?', [(int) $c['learner_id']]) : null;
        if ($l !== null) {
            AcademyMail::certificateDeclined($l, $c, mb_substr(trim($reason), 0, 300));
        }
        return true;
    }

    /** E-mail an approved certificate to its learner (link + PDF link). */
    public static function email(array $cert): bool
    {
        if (!self::isApproved($cert) || $cert['revoked_at'] !== null || $cert['learner_id'] === null) {
            return false;
        }
        $l = Database::one('SELECT * FROM academy_learners WHERE id = ?', [(int) $cert['learner_id']]);
        if ($l === null) {
            return false;
        }
        $ok = AcademyMail::certificate($cert, $l);
        if ($ok && self::approvalReady()) {
            Database::update('academy_certificates', ['emailed_at' => date('Y-m-d H:i:s')], 'id = ?', [(int) $cert['id']]);
        }
        return $ok;
    }

    /**
     * @param bool $approved true when an administrator issues it by hand (that is the approval); false for a certificate earned by passing the exam, which waits for
     *                       approval while the setting "certificates need approval" is on.
     * @return array<string,mixed> the new (or the existing) certificate
     */
    public static function issue(array $learner, array $course, ?float $score, int $minutes, bool $approved = false): array
    {
        $existing = self::existingFor((int) $learner['id'], (int) $course['id']);
        if ($existing !== null) {
            return $existing;
        }
        $row = [
            'code' => self::generateCode(),
            'learner_id' => (int) $learner['id'],
            'course_id' => (int) $course['id'],
            'learner_name' => mb_substr((string) $learner['name'], 0, 120),
            'course_title' => mb_substr((string) $course['title'], 0, 190),
            'score_pct' => $score,
            'hours' => $minutes > 0 ? round($minutes / 60 * 2) / 2 : null,
        ];
        if (self::approvalReady() && ($approved || !self::approvalRequired())) {
            $row['approved_at'] = date('Y-m-d H:i:s');
        }
        $id = Database::insert('academy_certificates', $row);
        return (array) Database::one('SELECT * FROM academy_certificates WHERE id = ?', [$id]);
    }

    /**
     * Certificate for a challenge: $award is "Winner, 1st place" or "Participant". One active certificate per learner and challenge.
     * @return array<string,mixed>
     */
    public static function issueForChallenge(array $learner, array $challenge, string $award, ?float $score): array
    {
        $existing = Database::one('SELECT * FROM academy_certificates WHERE learner_id = ? AND challenge_id = ? AND revoked_at IS NULL ORDER BY id DESC LIMIT 1', [(int) $learner['id'], (int) $challenge['id']]);
        if ($existing !== null) {
            return $existing;
        }
        $id = Database::insert('academy_certificates', [
            'code' => self::generateCode(), 'kind' => 'challenge', 'learner_id' => (int) $learner['id'], 'challenge_id' => (int) $challenge['id'],
            'learner_name' => mb_substr((string) $learner['name'], 0, 120), 'course_title' => mb_substr((string) $challenge['title'], 0, 190),
            'award' => mb_substr($award, 0, 80), 'score_pct' => $score,
        ] + (self::approvalReady() ? ['approved_at' => date('Y-m-d H:i:s')] : []));
        return (array) Database::one('SELECT * FROM academy_certificates WHERE id = ?', [$id]);
    }

    /** @return array{heading:string,intro:string,statement:string,scoreLabel:string} the wording of a certificate by its kind */
    public static function texts(array $cert): array
    {
        if (($cert['kind'] ?? 'course') !== 'challenge') {
            return ['heading' => 'Certificate of completion', 'intro' => 'This is to certify that', 'statement' => self::wording()['statement'], 'scoreLabel' => 'Final score'];
        }
        $award = (string) ($cert['award'] ?? '');
        $winner = str_starts_with($award, 'Winner');
        return [
            'heading' => $winner ? 'Certificate of achievement' : 'Certificate of participation',
            'intro' => $winner ? 'This certificate of achievement is proudly awarded to' : 'This is to certify that',
            'statement' => $winner ? 'has been recognised as ' . strtolower(trim((string) preg_replace('/^Winner,\s*/', '', $award))) . ' winner of the LAF Academy challenge:' : 'took part in the LAF Academy challenge:',
            'scoreLabel' => 'Score',
        ];
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

    /** "Certificate of completion" as printed: Title Case with "of" in lower case. */
    private static function headline(string $heading): string
    {
        return str_replace([' Of ', ' And '], [' of ', ' and '], mb_convert_case($heading, MB_CASE_TITLE, 'UTF-8'));
    }

    /** Centred text with extra space between the letters. */
    private static function tracked(PdfWriter $pdf, float $cx, float $y, string $text, string $font, float $size, string $color, float $track): void
    {
        $chars = mb_str_split($text);
        $total = 0.0;
        foreach ($chars as $ch) {
            $total += PdfWriter::textWidth($ch, $font, $size) + $track;
        }
        $x = $cx - ($total - $track) / 2;
        foreach ($chars as $ch) {
            $pdf->text($x, $y, $ch, $font, $size, $color, 'l');
            $x += PdfWriter::textWidth($ch, $font, $size) + $track;
        }
    }

    /**
     * Text along a circle (the words round the gold seal). $top: letters stand on the circle and read clockwise over the top;
     * otherwise they hang inside the bottom of the circle and read left to right. Coordinates in PDF space (origin bottom-left).
     */
    private static function arcText(PdfWriter $pdf, float $cx, float $cy, string $text, float $radius, float $size, bool $top, float $track): void
    {
        $chars = mb_str_split($text);
        $adv = [];
        $total = 0.0;
        foreach ($chars as $ch) {
            $w = PdfWriter::textWidth($ch, 'helvetica-bold', $size);
            $adv[] = $w;
            $total += $w + $track;
        }
        $total -= $track;
        $pos = 0.0;
        $ops = [];
        foreach ($chars as $i => $ch) {
            $mid = $pos + $adv[$i] / 2;
            $theta = $top ? ($total / 2 - $mid) / $radius : (-$total / 2 + $mid) / $radius;
            $pos += $adv[$i] + $track;
            if ($ch === ' ') {
                continue;
            }
            $c = cos($theta);
            $s = sin($theta);
            $ops[] = sprintf('q %.5F %.5F %.5F %.5F %.4F %.4F cm BT /F2 %.2F Tf %.4F %.4F Td (%s) Tj ET Q', $c, $s, -$s, $c, $cx, $cy, $size, -$adv[$i] / 2, $top ? $radius : -$radius, PdfWriter::escape(PdfWriter::toWinAnsi($ch)));
        }
        $pdf->rawOps("0.070588 0.12549 0.247059 rg\n" . implode("\n", $ops));
    }

    /** @param array<string,mixed> $cert a certificate row; returns the PDF bytes (the design of docs: LAF_Certificate template, A4 landscape) */
    public static function pdf(array $cert): string
    {
        $w = self::wording();
        $navy = '#122040';
        $gold = '#C9973F';
        $goldText = '#9A6F1C';
        $orange = '#E0642C';
        $grey = '#5B6478';
        $body = '#1B2438';
        $soft = '#EADFC6';
        $art = require dirname(__DIR__, 2) . '/config/academy/certificate-art.php';
        $tx = self::texts($cert);
        $pdf = new PdfWriter(['title' => 'Certificate ' . $cert['code'], 'author' => $w['org'], 'subject' => 'LAF Academy ' . strtolower($tx['heading'])]);
        $pdf->addPage(841.89, 595.28);
        $W = $pdf->pageWidth();
        $H = $pdf->pageHeight();
        $cx = $W / 2;

        // paper, navy border, gold double frame
        $pdf->fill('#FCFAF4');
        $pdf->rect(0, 0, $W, $H, 'f');
        $pdf->fill($navy);
        $pdf->rect(18, 18, $W - 36, $H - 36, 'f');
        $pdf->fill('#FCFAF4');
        $pdf->rect(24, 24, $W - 48, $H - 48, 'f');
        // the logo, very faint, behind the text (5 % in the template: stored already blended with the paper colour)
        try {
            $wm = ASSET_PATH . '/img/brand/watermark-logo.jpg';
            if (is_file($wm)) {
                $pdf->image($pdf->addJpeg((string) file_get_contents($wm)), 270.9449, $H - 141.6378 - 300, 300, 300);
            }
        } catch (\Throwable) {
        }
        $pdf->save();
        $pdf->stroke($gold);
        $pdf->lineWidth(2.2);
        $pdf->rect(32, 32, $W - 64, $H - 64, 's');
        $pdf->lineWidth(0.7);
        $pdf->rect(38, 38, $W - 76, $H - 76, 's');
        // corner ornaments: three quarter circles and an orange dot
        $pdf->lineWidth(1);
        foreach ([[38.0, 38.0, 1, 1], [$W - 38, 38.0, -1, 1], [38.0, $H - 38, 1, -1], [$W - 38, $H - 38, -1, -1]] as [$ox, $oy, $sx, $sy]) {
            foreach ([14, 22, 30] as $r) {
                $path = 'n';
                for ($k = 0; $k <= 16; $k++) {
                    $a = M_PI / 2 * $k / 16;
                    $path .= sprintf(' %.3F %.3F %s', $ox + $sx * $r * cos($a), $H - ($oy + $sy * $r * sin($a)), $k === 0 ? 'm' : 'l');
                }
                $pdf->rawOps($path . ' S');
            }
        }
        $pdf->restore();
        $pdf->fill($orange);
        foreach ([[44.0, 44.0], [$W - 44, 44.0], [44.0, $H - 44], [$W - 44, $H - 44]] as [$px, $py]) {
            $pdf->roundRect($px - 2.4, $py - 2.4, 4.8, 4.8, 2.4, 'f');
        }

        // logo and organisation
        try {
            $file = ASSET_PATH . '/img/brand/logo-512.png';
            if (is_file($file)) {
                $pdf->image($pdf->addPng((string) file_get_contents($file)), $cx - 34, 54, 68, 68);
            }
        } catch (\Throwable) {
            // no logo: the name below still identifies the issuer
        }
        self::tracked($pdf, $cx, 140, mb_strtoupper($w['org']), 'helvetica-bold', 11, $navy, 2.1);
        self::tracked($pdf, $cx, 154, 'LAF ACADEMY', 'helvetica-bold', 8.5, $goldText, 3.0);

        // heading with the gold rule and diamond
        $pdf->text($cx, 205, self::headline($tx['heading']), 'times-bold', 40, $navy, 'c');
        $pdf->save();
        $pdf->stroke($gold);
        $pdf->lineWidth(1);
        $pdf->line($cx - 110, 216, $cx - 10, 216);
        $pdf->line($cx + 10, 216, $cx + 110, 216);
        $pdf->restore();
        $pdf->fill($orange);
        $pdf->rawOps(sprintf('n %.3F %.3F m %.3F %.3F l %.3F %.3F l %.3F %.3F l h f', $cx, $H - 216 + 4.95, $cx + 4.95, $H - 216, $cx, $H - 216 - 4.95, $cx - 4.95, $H - 216));
        $pdf->text($cx, 240, $tx['intro'], 'times-italic', 13.5, $grey, 'c');

        // the learner
        $name = mb_strtoupper((string) $cert['learner_name']);
        $nsize = self::fit($name, 'times-bold', 43, 640, 12);
        $pdf->text($cx, 290, $name, 'times-bold', $nsize, $navy, 'c');
        $pdf->save();
        $pdf->stroke($gold);
        $pdf->lineWidth(0.9);
        $pdf->line(120, 302, $W - 120, 302);
        $pdf->restore();

        // statement and course (or challenge)
        $ss = 12.0;
        $statement = rtrim($tx['statement'], " .");
        $lines = self::wrap($statement, 'helvetica', $ss, 600);
        if (count($lines) > 1) {
            $ss = 10.5;
            $lines = array_slice(self::wrap($statement, 'helvetica', $ss, 640), 0, 2);
        }
        $sy = count($lines) > 1 ? 319.0 : 324.0;
        foreach ($lines as $line) {
            $pdf->text($cx, $sy, $line, 'helvetica', $ss, $body, 'c');
            $sy += 14;
        }
        $title = (string) $cert['course_title'];
        $tsize = self::fit($title, 'times-bold', 25, 640, 17);
        if (PdfWriter::textWidth($title, 'times-bold', $tsize) > 640) {
            $tl = self::wrap($title, 'times-bold', 17, 640);
            $pdf->text($cx, 351, $tl[0], 'times-bold', 17, $orange, 'c');
            if (isset($tl[1])) {
                $pdf->text($cx, 367, $tl[1], 'times-bold', 17, $orange, 'c');
            }
        } else {
            $pdf->text($cx, 358, $title, 'times-bold', $tsize, $orange, 'c');
        }

        // facts band
        $pdf->save();
        $pdf->stroke($soft);
        $pdf->lineWidth(0.6);
        $pdf->line(100, 372, 741.89, 372);
        $pdf->line(100, 412, 741.89, 412);
        foreach ([260.47, 420.94, 581.42] as $vx) {
            $pdf->line($vx, 378, $vx, 406);
        }
        $pdf->restore();
        $num = static fn(float $v): string => rtrim(rtrim(number_format($v, 1), '0'), '.');
        $isChallenge = ($cert['kind'] ?? 'course') === 'challenge';
        $col1 = $isChallenge
            ? ['AWARD', trim((string) preg_replace('/^Winner,\s*/', '', (string) ($cert['award'] ?? ''))) ?: '-']
            : [mb_strtoupper($tx['scoreLabel']), $cert['score_pct'] !== null ? $num((float) $cert['score_pct']) . '%' : '-'];
        $col2 = $isChallenge
            ? ['SCORE', $cert['score_pct'] !== null ? $num((float) $cert['score_pct']) . '%' : '-']
            : ['LEARNING HOURS', $cert['hours'] !== null ? $num((float) $cert['hours']) . ' hours' : '-'];
        $facts = [
            [180.2, $col1[0], $col1[1], 'times-bold', 15],
            [340.7, $col2[0], $col2[1], 'times-bold', 15],
            [501.2, 'DATE OF ISSUE', date('j F Y', (int) strtotime((string) ($cert['approved_at'] ?? $cert['issued_at']))), 'times-bold', 15],
            [661.7, 'CERTIFICATE NO.', (string) $cert['code'], 'helvetica-bold', 13],
        ];
        foreach ($facts as [$fx, $label, $value, $vf, $vs]) {
            self::tracked($pdf, $fx, 386, $label, 'helvetica-bold', 7.6, $grey, 1.6);
            $pdf->text($fx, 404, $value, $vf, self::fit($value, $vf, $vs, 150, 9), $navy, 'c');
        }

        // signature (left): the founder's real signature while the signatory is the founder
        $founder = $w['signatory'] === '';
        $sigFile = ASSET_PATH . '/img/brand/signature-moses.png';
        if ($founder && is_file($sigFile)) {
            try {
                $pdf->image($pdf->addPng((string) file_get_contents($sigFile)), 165.938, $H - 106 - 59.4, 143.124, 59.4);
            } catch (\Throwable) {
            }
        }
        $pdf->save();
        $pdf->stroke($navy);
        $pdf->lineWidth(0.8);
        $pdf->line(130, $H - 102, 350, $H - 102);
        $pdf->restore();
        $signer = $w['signatory'] !== '' ? $w['signatory'] : 'Moses Komakech';
        $signerTitle = $w['signatory'] !== '' ? ($w['title'] !== '' ? $w['title'] : 'Director') : 'Founder, ' . $w['org'];
        $pdf->text(240, $H - 87, $signer, 'times-bold', 13.5, $navy, 'c');
        $pdf->text(240, $H - 74, $signerTitle, 'helvetica', 9.5, $grey, 'c');

        // gold seal (centre)
        $pdf->rawOps("q\n" . $art['seal'] . "\nQ");
        try {
            $file = ASSET_PATH . '/img/brand/logo-512.png';
            if (is_file($file)) {
                $pdf->image($pdf->addPng((string) file_get_contents($file)), 398.3849, $H - 156.56, 45.12, 45.12);
            }
        } catch (\Throwable) {
        }
        self::arcText($pdf, 420.9449, 134.0, 'LAF ACADEMY', 29.76, 6.6, true, 0.6);
        self::arcText($pdf, 420.9449, 134.0, 'VERIFIED ' . "\u{00B7}" . ' AUTHENTIC', 33.68, 5.6, false, 0.5);

        // QR code to the public check page (right)
        $url = self::verifyUrl((string) $cert['code']);
        $pdf->save();
        $pdf->fill('#FFFFFF');
        $pdf->stroke($gold);
        $pdf->lineWidth(0.7);
        $pdf->roundRect(622.5, 432.5, 78.0, 78.0, 7, 'b');
        $pdf->restore();
        try {
            $qr = QrEncoder::encode($url, 'M');
            $n = count($qr['modules']);
            $mod = 64.0 / $n;
            $pdf->fill('#000000');
            foreach (QrEncoder::runs($qr['modules']) as [$r, $c, $len]) {
                $pdf->rect(629.89 + $c * $mod - 0.01, 439.3 + $r * $mod - 0.01, $len * $mod + 0.02, $mod + 0.02, 'f');
            }
        } catch (\Throwable) {
            // no QR: the printed number and address still verify the certificate
        }
        self::tracked($pdf, 661.7, 520, 'SCAN TO VERIFY', 'helvetica-bold', 6.6, $navy, 1.3);
        $pdf->text(661.7, 530, (string) $cert['code'], 'helvetica', 6, $grey, 'c');

        // footer
        $host = (string) parse_url(abs_url(''), PHP_URL_HOST);
        $pdf->text($cx, 545, $w['org'] . ' ' . "\u{00B7}" . ' Gulu City, Northern Uganda ' . "\u{00B7}" . ' ' . $host . ' ' . "\u{00B7}" . ' Verify this certificate at ' . preg_replace('~^https?://~', '', $url), 'helvetica', 6.6, $grey, 'c');
        return $pdf->output();
    }

    /** PDF drawing operators (the seal and the signature of the template) as SVG shapes; $H is the page height used to turn PDF y (up) into SVG y (down). */
    private static function opsToSvg(string $ops, float $H): string
    {
        $out = '';
        $d = '';
        $fill = '#000000';
        $stroke = '#000000';
        $lw = 1.0;
        $st = [];
        $hex = static function (array $rgb): string {
            return sprintf('#%02x%02x%02x', (int) round($rgb[0] * 255), (int) round($rgb[1] * 255), (int) round($rgb[2] * 255));
        };
        foreach (preg_split('/\s+/', trim($ops)) ?: [] as $t) {
            if ($t === '') {
                continue;
            }
            if (is_numeric($t)) {
                $st[] = (float) $t;
                continue;
            }
            switch ($t) {
                case 'rg':
                    $fill = $hex(array_slice($st, -3));
                    break;
                case 'RG':
                    $stroke = $hex(array_slice($st, -3));
                    break;
                case 'w':
                    $lw = (float) end($st);
                    break;
                case 'm':
                case 'l':
                    $p = array_slice($st, -2);
                    $d .= ($t === 'm' ? 'M' : 'L') . sprintf('%.3F %.3F ', $p[0], $H - $p[1]);
                    break;
                case 'c':
                    $p = array_slice($st, -6);
                    $d .= sprintf('C%.3F %.3F %.3F %.3F %.3F %.3F ', $p[0], $H - $p[1], $p[2], $H - $p[3], $p[4], $H - $p[5]);
                    break;
                case 'h':
                    $d .= 'Z ';
                    break;
                case 'f*':
                case 'f':
                    if ($d !== '') {
                        $out .= '<path d="' . trim($d) . '" fill="' . $fill . '" fill-rule="evenodd"/>';
                    }
                    $d = '';
                    break;
                case 'S':
                    if ($d !== '') {
                        $out .= '<path d="' . trim($d) . '" fill="none" stroke="' . $stroke . '" stroke-width="' . $lw . '" stroke-linecap="round" stroke-linejoin="round"/>';
                    }
                    $d = '';
                    break;
                case 'n':
                    $d = '';
                    break;
                default:
                    break;          // J, j and any other state operator: not needed for the drawing
            }
            $st = [];
        }
        return $out;
    }

    /**
     * The certificate as an SVG picture (same design and positions as the PDF), for the check page on screen: sharp at any size, prints well, and opens on every
     * phone (a PDF does not show inside a page on many of them). Every learner-supplied text is escaped.
     * @param array<string,mixed> $cert a certificate row
     */
    public static function svg(array $cert): string
    {
        $w = self::wording();
        $navy = '#122040';
        $gold = '#C9973F';
        $goldText = '#9A6F1C';
        $orange = '#E0642C';
        $grey = '#5B6478';
        $body = '#1B2438';
        $soft = '#EADFC6';
        $art = require dirname(__DIR__, 2) . '/config/academy/certificate-art.php';
        $tx = self::texts($cert);
        $W = 841.89;
        $H = 595.28;
        $cx = $W / 2;
        $e = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $fam = [
            'times-bold' => "font-family=\"'Times New Roman',Times,'Liberation Serif',serif\" font-weight=\"700\"",
            'times-italic' => "font-family=\"'Times New Roman',Times,'Liberation Serif',serif\" font-style=\"italic\"",
            'helvetica' => "font-family=\"Helvetica,Arial,'Liberation Sans',sans-serif\"",
            'helvetica-bold' => "font-family=\"Helvetica,Arial,'Liberation Sans',sans-serif\" font-weight=\"700\"",
        ];
        $o = [];
        $text = static function (float $x, float $y, string $t, string $font, float $size, string $fill, float $track = 0.0) use (&$o, $e, $fam): void {
            $o[] = sprintf('<text x="%.2F" y="%.2F" text-anchor="middle" %s font-size="%.2F" fill="%s"%s>%s</text>', $x + $track / 2, $y, $fam[$font], $size, $fill, $track > 0 ? ' letter-spacing="' . $track . '"' : '', $e($t));
        };
        $line = static function (float $x1, float $y1, float $x2, float $y2, string $stroke, float $lw) use (&$o): void {
            $o[] = sprintf('<line x1="%.2F" y1="%.2F" x2="%.2F" y2="%.2F" stroke="%s" stroke-width="%s"/>', $x1, $y1, $x2, $y2, $stroke, $lw);
        };

        // paper, navy border, gold double frame, corner ornaments
        $o[] = '<rect width="' . $W . '" height="' . $H . '" fill="#FCFAF4"/>';
        $o[] = '<rect x="18" y="18" width="' . ($W - 36) . '" height="' . ($H - 36) . '" fill="' . $navy . '"/>';
        $o[] = '<rect x="24" y="24" width="' . ($W - 48) . '" height="' . ($H - 48) . '" fill="#FCFAF4"/>';
        $o[] = '<image href="' . $e(asset('img/brand/watermark-logo.jpg')) . '" x="270.9449" y="' . ($H - 141.6378 - 300) . '" width="300" height="300"/>';
        $o[] = '<rect x="32" y="32" width="' . ($W - 64) . '" height="' . ($H - 64) . '" fill="none" stroke="' . $gold . '" stroke-width="2.2"/>';
        $o[] = '<rect x="38" y="38" width="' . ($W - 76) . '" height="' . ($H - 76) . '" fill="none" stroke="' . $gold . '" stroke-width="0.7"/>';
        foreach ([[38.0, 38.0, 1, 1], [$W - 38, 38.0, -1, 1], [38.0, $H - 38, 1, -1], [$W - 38, $H - 38, -1, -1]] as [$ox, $oy, $sx, $sy]) {
            foreach ([14, 22, 30] as $r) {
                $o[] = sprintf('<path d="M%.2F %.2F A%d %d 0 0 %d %.2F %.2F" fill="none" stroke="%s" stroke-width="1"/>', $ox + $sx * $r, $oy, $r, $r, $sx * $sy > 0 ? 1 : 0, $ox, $oy + $sy * $r, $gold);
            }
            $o[] = sprintf('<circle cx="%.2F" cy="%.2F" r="2.4" fill="%s"/>', $ox + $sx * 6, $oy + $sy * 6, $orange);
        }

        // logo and organisation
        $logo = asset('img/brand/logo-512.png');
        $o[] = '<image href="' . $e($logo) . '" x="' . ($cx - 34) . '" y="54" width="68" height="68"/>';
        $text($cx, 140, mb_strtoupper($w['org']), 'helvetica-bold', 11, $navy, 2.1);
        $text($cx, 154, 'LAF ACADEMY', 'helvetica-bold', 8.5, $goldText, 3.0);

        // heading, gold rule and diamond
        $text($cx, 205, self::headline($tx['heading']), 'times-bold', 40, $navy);
        $line($cx - 110, 216, $cx - 10, 216, $gold, 1);
        $line($cx + 10, 216, $cx + 110, 216, $gold, 1);
        $o[] = sprintf('<path d="M%.2F %.2F L%.2F %.2F L%.2F %.2F L%.2F %.2F Z" fill="%s"/>', $cx, 216 - 4.95, $cx + 4.95, 216, $cx, 216 + 4.95, $cx - 4.95, 216, $orange);
        $text($cx, 240, $tx['intro'], 'times-italic', 13.5, $grey);

        // the learner
        $name = mb_strtoupper((string) $cert['learner_name']);
        $text($cx, 290, $name, 'times-bold', self::fit($name, 'times-bold', 43, 640, 12), $navy);
        $line(120, 302, $W - 120, 302, $gold, 0.9);

        // statement and course
        $ss = 12.0;
        $statement = rtrim($tx['statement'], ' .');
        $lines = self::wrap($statement, 'helvetica', $ss, 600);
        if (count($lines) > 1) {
            $ss = 10.5;
            $lines = array_slice(self::wrap($statement, 'helvetica', $ss, 640), 0, 2);
        }
        $sy = count($lines) > 1 ? 319.0 : 324.0;
        foreach ($lines as $ln) {
            $text($cx, $sy, $ln, 'helvetica', $ss, $body);
            $sy += 14;
        }
        $title = (string) $cert['course_title'];
        $tsize = self::fit($title, 'times-bold', 25, 640, 17);
        if (PdfWriter::textWidth($title, 'times-bold', $tsize) > 640) {
            $tl = self::wrap($title, 'times-bold', 17, 640);
            $text($cx, 351, $tl[0], 'times-bold', 17, $orange);
            if (isset($tl[1])) {
                $text($cx, 367, $tl[1], 'times-bold', 17, $orange);
            }
        } else {
            $text($cx, 358, $title, 'times-bold', $tsize, $orange);
        }

        // facts band
        $line(100, 372, 741.89, 372, $soft, 0.6);
        $line(100, 412, 741.89, 412, $soft, 0.6);
        foreach ([260.47, 420.94, 581.42] as $vx) {
            $line($vx, 378, $vx, 406, $soft, 0.6);
        }
        $num = static fn(float $v): string => rtrim(rtrim(number_format($v, 1), '0'), '.');
        $isChallenge = ($cert['kind'] ?? 'course') === 'challenge';
        $col1 = $isChallenge
            ? ['AWARD', trim((string) preg_replace('/^Winner,\s*/', '', (string) ($cert['award'] ?? ''))) ?: '-']
            : [mb_strtoupper($tx['scoreLabel']), $cert['score_pct'] !== null ? $num((float) $cert['score_pct']) . '%' : '-'];
        $col2 = $isChallenge
            ? ['SCORE', $cert['score_pct'] !== null ? $num((float) $cert['score_pct']) . '%' : '-']
            : ['LEARNING HOURS', $cert['hours'] !== null ? $num((float) $cert['hours']) . ' hours' : '-'];
        $facts = [
            [180.2, $col1[0], $col1[1], 'times-bold', 15],
            [340.7, $col2[0], $col2[1], 'times-bold', 15],
            [501.2, 'DATE OF ISSUE', date('j F Y', (int) strtotime((string) ($cert['approved_at'] ?? $cert['issued_at']))), 'times-bold', 15],
            [661.7, 'CERTIFICATE NO.', (string) $cert['code'], 'helvetica-bold', 13],
        ];
        foreach ($facts as [$fx, $label, $value, $vf, $vs]) {
            $text($fx, 386, $label, 'helvetica-bold', 7.6, $grey, 1.6);
            $text($fx, 404, $value, $vf, self::fit($value, $vf, $vs, 150, 9), $navy);
        }

        // signature (left): the founder's real signature while the signatory is the founder
        if ($w['signatory'] === '') {
            $o[] = '<image href="' . $e(asset('img/brand/signature-moses.png')) . '" x="165.938" y="' . ($H - 106 - 59.4) . '" width="143.124" height="59.4"/>';
        }
        $line(130, $H - 102, 350, $H - 102, $navy, 0.8);
        $signer = $w['signatory'] !== '' ? $w['signatory'] : 'Moses Komakech';
        $signerTitle = $w['signatory'] !== '' ? ($w['title'] !== '' ? $w['title'] : 'Director') : 'Founder, ' . $w['org'];
        $text(240, $H - 87, $signer, 'times-bold', 13.5, $navy);
        $text(240, $H - 74, $signerTitle, 'helvetica', 9.5, $grey);

        // gold seal (centre) with the words round it
        $o[] = '<g>' . self::opsToSvg($art['seal'], $H) . '</g>';
        $o[] = '<image href="' . $e($logo) . '" x="398.3849" y="' . ($H - 156.56) . '" width="45.12" height="45.12"/>';
        $scy = $H - 134.0;
        foreach ([['LAF ACADEMY', 29.76, 6.6, true, 0.6], ["VERIFIED \u{00B7} AUTHENTIC", 33.68, 5.6, false, 0.5]] as [$arc, $radius, $asize, $top, $atrack]) {
            $chars = mb_str_split($arc);
            $adv = [];
            $total = 0.0;
            foreach ($chars as $ch) {
                $adv[] = PdfWriter::textWidth($ch, 'helvetica-bold', $asize);
                $total += end($adv) + $atrack;
            }
            $total -= $atrack;
            $pos = 0.0;
            foreach ($chars as $i => $ch) {
                $mid = $pos + $adv[$i] / 2;
                $theta = $top ? ($total / 2 - $mid) / $radius : (-$total / 2 + $mid) / $radius;
                $pos += $adv[$i] + $atrack;
                if ($ch === ' ') {
                    continue;
                }
                $o[] = sprintf('<text transform="translate(420.9449 %.4F) rotate(%.3F)" x="%.3F" y="%.2F" %s font-size="%.2F" fill="%s">%s</text>', $scy, -$theta * 180 / M_PI, -$adv[$i] / 2, $top ? -$radius : $radius, $fam['helvetica-bold'], $asize, $navy, $e($ch));
            }
        }

        // QR code to this check page (right)
        $url = self::verifyUrl((string) $cert['code']);
        $o[] = '<rect x="622.5" y="432.5" width="78" height="78" rx="7" fill="#FFFFFF" stroke="' . $gold . '" stroke-width="0.7"/>';
        try {
            $qr = QrEncoder::encode($url, 'M');
            $n = count($qr['modules']);
            $mod = 64.0 / $n;
            $q = '';
            foreach (QrEncoder::runs($qr['modules']) as [$r, $c, $len]) {
                $q .= sprintf('M%.3F %.3Fh%.3Fv%.3Fh-%.3FZ', 629.89 + $c * $mod, 439.3 + $r * $mod, $len * $mod + 0.02, $mod + 0.02, $len * $mod + 0.02);
            }
            $o[] = '<path d="' . $q . '" fill="#000000"/>';
        } catch (\Throwable) {
            // no QR: the printed number and address still verify the certificate
        }
        $text(661.7, 520, 'SCAN TO VERIFY', 'helvetica-bold', 6.6, $navy, 1.3);
        $text(661.7, 530, (string) $cert['code'], 'helvetica', 6, $grey);

        // footer
        $host = (string) parse_url(abs_url(''), PHP_URL_HOST);
        $text($cx, 545, $w['org'] . ' ' . "\u{00B7}" . ' Gulu City, Northern Uganda ' . "\u{00B7}" . ' ' . $host . ' ' . "\u{00B7}" . ' Verify this certificate at ' . preg_replace('~^https?://~', '', $url), 'helvetica', 6.6, $grey);

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 841.89 595.28" role="img" aria-label="' . $e($tx['heading'] . ': ' . $cert['learner_name'] . ', ' . $cert['course_title']) . '">' . implode('', $o) . '</svg>';
    }
}
