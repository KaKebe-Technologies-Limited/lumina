<?php
declare(strict_types=1);

namespace App\Services\Letters;

use App\Core\Database;
use App\Core\Mailer;

/**
 * OFFICIAL LETTERS (Admin > Official letters): an administrator writes a letter (reference, date, addressee, "RE:", body, sender, office, signature: all but the subject, the body and the
 * sender's name optional), previews it as a PDF, then e-mails the PDF to any number of addresses typed or chosen from the contact book: one e-mail for each person (each may be addressed by
 * name) or ONE e-mail to the whole group (recipients visible, or hidden). Every send is logged per address, and a letter is never sent twice to the same address unless asked.
 * Tables are created by install() on first use (CREATE TABLE IF NOT EXISTS; the same statements are in database/updates/2026-11-official-letters.sql).
 */
final class LetterService
{
    public const MAX_RECIPIENTS = 500;
    public const SIG_MODES = ['image' => 'The signatory\'s signature picture', 'typed' => 'The sender\'s name typed in italics', 'blank' => 'A blank space to sign by hand'];

    private const SCHEMA = [
        "CREATE TABLE IF NOT EXISTS `letters` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ref_no` VARCHAR(60) NULL,
  `letter_date` DATE NOT NULL,
  `use_letterhead` TINYINT(1) NOT NULL DEFAULT 1,
  `to_name` VARCHAR(190) NULL,
  `to_title` VARCHAR(190) NULL,
  `to_org` VARCHAR(190) NULL,
  `to_address` TEXT NULL,
  `salutation` VARCHAR(190) NULL,
  `subject` VARCHAR(300) NOT NULL,
  `body` MEDIUMTEXT NOT NULL,
  `closing` VARCHAR(120) NULL,
  `sig_mode` VARCHAR(10) NOT NULL DEFAULT 'blank',
  `signatory_id` INT UNSIGNED NULL,
  `from_name` VARCHAR(190) NULL,
  `from_title` VARCHAR(190) NULL,
  `from_office` VARCHAR(190) NULL,
  `cc` TEXT NULL,
  `enclosures` TEXT NULL,
  `status` VARCHAR(10) NOT NULL DEFAULT 'draft',
  `created_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_letters_status` (`status`, `updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS `letter_contacts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(190) NOT NULL DEFAULT '',
  `email` VARCHAR(190) NOT NULL,
  `organisation` VARCHAR(190) NULL,
  `phone` VARCHAR(60) NULL,
  `grp` VARCHAR(80) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_letter_contacts_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS `letter_sends` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `letter_id` INT UNSIGNED NOT NULL,
  `mode` VARCHAR(10) NOT NULL DEFAULT 'each',
  `subject` VARCHAR(300) NOT NULL,
  `message` TEXT NULL,
  `total` INT UNSIGNED NOT NULL DEFAULT 0,
  `sent_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `failed_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_letter_sends_letter` (`letter_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS `letter_recipients` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `send_id` INT UNSIGNED NOT NULL,
  `letter_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(190) NOT NULL DEFAULT '',
  `email` VARCHAR(190) NOT NULL,
  `status` VARCHAR(10) NOT NULL DEFAULT 'sent',
  `error` VARCHAR(255) NULL,
  `sent_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_letter_recipients_send` (`send_id`),
  KEY `ix_letter_recipients_letter` (`letter_id`, `email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ];

    private static ?bool $ready = null;

    public static function ready(): bool
    {
        if (self::$ready === null) {
            try {
                self::$ready = (int) Database::value("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name IN ('letters','letter_contacts','letter_sends','letter_recipients')") === 4;
            } catch (\Throwable) {
                self::$ready = false;
            }
        }
        return self::$ready;
    }

    /** Create the tables when they are missing (safe to repeat). @return bool ready afterwards */
    public static function install(): bool
    {
        if (self::ready()) {
            return true;
        }
        try {
            foreach (self::SCHEMA as $sql) {
                Database::execute($sql);
            }
        } catch (\Throwable) {
            // the database user may not be allowed to create tables: the page then tells the administrator to import database/updates/2026-11-official-letters.sql
        }
        self::$ready = null;
        return self::ready();
    }

    // ------------------------------------------------------------------------------------------------------------- letters

    /** @return array<string,mixed> the form of a new letter */
    public static function blank(): array
    {
        return [
            'id' => 0, 'ref_no' => '', 'letter_date' => date('Y-m-d'), 'use_letterhead' => 1, 'to_name' => '', 'to_title' => '', 'to_org' => '', 'to_address' => '', 'salutation' => 'Dear Sir/Madam,',
            'subject' => '', 'body' => '', 'closing' => 'Yours faithfully,', 'sig_mode' => 'blank', 'signatory_id' => 0, 'from_name' => '', 'from_title' => '', 'from_office' => '', 'cc' => '', 'enclosures' => '', 'status' => 'draft',
        ];
    }

    /**
     * Check the form. @param array<string,mixed> $in
     * @return array{errors:array<string,string>,data:array<string,mixed>}
     */
    public static function validate(array $in): array
    {
        $t = static fn(string $k, int $max): string => mb_substr(trim((string) ($in[$k] ?? '')), 0, $max);
        $errors = [];
        $d = [
            'ref_no' => $t('ref_no', 60), 'use_letterhead' => !empty($in['use_letterhead']) ? 1 : 0, 'to_name' => $t('to_name', 190), 'to_title' => $t('to_title', 190), 'to_org' => $t('to_org', 190),
            'to_address' => mb_substr(trim((string) ($in['to_address'] ?? '')), 0, 600), 'salutation' => $t('salutation', 190), 'subject' => $t('subject', 300), 'body' => mb_substr(trim((string) ($in['body'] ?? '')), 0, 20000),
            'closing' => $t('closing', 120), 'sig_mode' => array_key_exists((string) ($in['sig_mode'] ?? ''), self::SIG_MODES) ? (string) $in['sig_mode'] : 'blank', 'signatory_id' => max(0, (int) ($in['signatory_id'] ?? 0)),
            'from_name' => $t('from_name', 190), 'from_title' => $t('from_title', 190), 'from_office' => $t('from_office', 190),
            'cc' => mb_substr(trim((string) ($in['cc'] ?? '')), 0, 600), 'enclosures' => mb_substr(trim((string) ($in['enclosures'] ?? '')), 0, 600),
        ];
        $date = (string) ($in['letter_date'] ?? '');
        $ts = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 ? strtotime($date) : false;
        $d['letter_date'] = $ts !== false ? date('Y-m-d', $ts) : '';
        if ($d['letter_date'] === '') {
            $errors['letter_date'] = 'Choose the date of the letter.';
        }
        if ($d['subject'] === '') {
            $errors['subject'] = 'Write the subject (what comes after "RE:").';
        }
        if (mb_strlen($d['body']) < 10) {
            $errors['body'] = 'Write the body of the letter.';
        }
        if ($d['sig_mode'] === 'image') {
            $sig = $d['signatory_id'] > 0 ? Database::one('SELECT id, name, title, signature_path FROM document_signatories WHERE id = ? AND is_active = 1', [$d['signatory_id']]) : null;
            if ($sig === null || trim((string) ($sig['signature_path'] ?? '')) === '') {
                $errors['signatory_id'] = 'Choose a signatory who has a signature picture (Documents > Signatories), or pick another way to sign.';
            } else {
                $d['from_name'] = $d['from_name'] !== '' ? $d['from_name'] : (string) $sig['name'];
                $d['from_title'] = $d['from_title'] !== '' ? $d['from_title'] : (string) ($sig['title'] ?? '');
            }
        } else {
            $d['signatory_id'] = 0;
        }
        if ($d['from_name'] === '') {
            $errors['from_name'] = 'Write the name of the person sending the letter.';
        }
        return ['errors' => $errors, 'data' => $d];
    }

    /** @param array<string,mixed> $data validated @return int the letter id */
    public static function save(?int $id, array $data, ?int $userId): int
    {
        if (trim((string) $data['ref_no']) === '' && !empty($data['auto_ref'])) {
            $data['ref_no'] = self::nextReference();
        }
        unset($data['auto_ref']);
        if ($id !== null && $id > 0) {
            Database::update('letters', $data, 'id = ?', [$id]);
            return $id;
        }
        return (int) Database::insert('letters', $data + ['created_by' => $userId]);
    }

    /** "LAF/2026/007": the next number of this year. */
    public static function nextReference(): string
    {
        $year = date('Y');
        $n = (int) Database::value("SELECT COUNT(*) FROM letters WHERE ref_no LIKE ?", ['%/' . $year . '/%'], 0) + 1;
        $prefix = strtoupper(preg_replace('/[^A-Za-z]/', '', (string) setting('org_short', 'LAF')) ?: 'LAF');
        return sprintf('%s/%s/%03d', $prefix, $year, $n);
    }

    /** @return array<string,mixed>|null */
    public static function find(int $id): ?array
    {
        return self::ready() ? Database::one('SELECT * FROM letters WHERE id = ?', [$id]) : null;
    }

    /** @return list<array<string,mixed>> newest first, with how many addresses it was sent to */
    public static function all(): array
    {
        if (!self::ready()) {
            return [];
        }
        return Database::all("SELECT l.*, (SELECT COUNT(*) FROM letter_recipients r WHERE r.letter_id = l.id AND r.status = 'sent') AS sent_to FROM letters l ORDER BY l.updated_at DESC, l.id DESC LIMIT 300");
    }

    public static function duplicate(int $id, ?int $userId): int
    {
        $l = self::find($id);
        if ($l === null) {
            return 0;
        }
        unset($l['id'], $l['created_at'], $l['updated_at']);
        $l['ref_no'] = '';
        $l['letter_date'] = date('Y-m-d');
        $l['status'] = 'draft';
        $l['subject'] = mb_substr('Copy: ' . (string) $l['subject'], 0, 300);
        $l['created_by'] = $userId;
        return (int) Database::insert('letters', $l);
    }

    public static function delete(int $id): void
    {
        Database::execute('DELETE FROM letter_recipients WHERE letter_id = ?', [$id]);
        Database::execute('DELETE FROM letter_sends WHERE letter_id = ?', [$id]);
        Database::execute('DELETE FROM letters WHERE id = ?', [$id]);
    }

    /** @return list<array<string,mixed>> people who can sign (Documents > Signatories); has_picture says whether a signature picture is stored */
    public static function signatories(): array
    {
        try {
            return Database::all("SELECT id, name, title, (signature_path IS NOT NULL AND signature_path <> '') AS has_picture FROM document_signatories WHERE is_active = 1 ORDER BY name");
        } catch (\Throwable) {
            return [];
        }
    }

    /** @param array<string,mixed> $letter @param array{to_name?:string,salutation?:string,compress?:bool} $opts */
    public static function pdf(array $letter, array $opts = []): string
    {
        return LetterPdf::build($letter, $opts);
    }

    public static function pdfName(array $letter): string
    {
        $base = trim((string) ($letter['ref_no'] ?? '')) !== '' ? (string) $letter['ref_no'] : 'Letter-' . (int) ($letter['id'] ?? 0);
        $base = preg_replace('/[^A-Za-z0-9._-]+/', '-', $base) ?: 'Letter';
        return trim($base, '-') . '.pdf';
    }

    // ------------------------------------------------------------------------------------------------------------- contacts and recipients

    /** @return list<array<string,mixed>> */
    public static function contacts(): array
    {
        return self::ready() ? Database::all('SELECT * FROM letter_contacts ORDER BY COALESCE(NULLIF(grp, \'\'), \'~\'), name, email') : [];
    }

    /** @return array{ok:bool,error:string} */
    public static function addContact(string $name, string $email, string $org = '', string $phone = '', string $grp = ''): array
    {
        $email = strtolower(trim($email));
        if (!Mailer::validEmail($email)) {
            return ['ok' => false, 'error' => 'That is not a valid e-mail address.'];
        }
        $vals = ['name' => mb_substr(trim($name), 0, 190), 'organisation' => mb_substr(trim($org), 0, 190), 'phone' => mb_substr(trim($phone), 0, 60), 'grp' => mb_substr(trim($grp), 0, 80)];
        if (Database::value('SELECT 1 FROM letter_contacts WHERE email = ?', [$email]) !== null) {
            Database::update('letter_contacts', $vals, 'email = ?', [$email]);
            return ['ok' => true, 'error' => ''];
        }
        Database::insert('letter_contacts', $vals + ['email' => $email]);
        return ['ok' => true, 'error' => ''];
    }

    public static function deleteContact(int $id): void
    {
        Database::execute('DELETE FROM letter_contacts WHERE id = ?', [$id]);
    }

    /**
     * Read a pasted list: one contact per line or separated by commas / semicolons; "Name <a@b.org>", "a@b.org", "Name, a@b.org, Organisation, Phone" (a line with an address and other words).
     * @return array{rows:list<array{name:string,email:string,org:string,phone:string}>,bad:list<string>}
     */
    public static function parseList(string $text): array
    {
        $rows = [];
        $bad = [];
        $seen = [];
        foreach (preg_split('/[\r\n;]+/', $text) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            // "Name <email>"
            $pieces = [];
            if (preg_match_all('/([^<>,]*?)\s*<\s*([^<>\s,]+@[^<>\s,]+)\s*>/', $line, $m, PREG_SET_ORDER) > 0) {
                foreach ($m as $x) {
                    $pieces[] = ['name' => trim($x[1], " \t\"'"), 'email' => $x[2], 'org' => '', 'phone' => ''];
                }
            } elseif (substr_count($line, '@') > 1 && !str_contains($line, ' ')) {
                foreach (explode(',', $line) as $e) {
                    $pieces[] = ['name' => '', 'email' => trim($e), 'org' => '', 'phone' => ''];
                }
            } else {
                $cells = array_map('trim', explode(',', $line));
                $ei = null;
                foreach ($cells as $i => $c) {
                    if (str_contains($c, '@')) {
                        $ei = $i;
                        break;
                    }
                }
                if ($ei === null) {
                    $bad[] = $line;
                    continue;
                }
                $others = array_values(array_filter($cells, static fn(string $c, int $i): bool => $i !== $ei && $c !== '', ARRAY_FILTER_USE_BOTH));
                $pieces[] = ['name' => $others[0] ?? '', 'email' => $cells[$ei], 'org' => $others[1] ?? '', 'phone' => $others[2] ?? ''];
            }
            foreach ($pieces as $p) {
                $e = strtolower(trim($p['email']));
                if (!Mailer::validEmail($e)) {
                    $bad[] = $p['email'];
                    continue;
                }
                if (isset($seen[$e])) {
                    continue;
                }
                $seen[$e] = true;
                $rows[] = ['name' => mb_substr($p['name'], 0, 190), 'email' => $e, 'org' => mb_substr($p['org'], 0, 190), 'phone' => mb_substr($p['phone'], 0, 60)];
            }
        }
        return ['rows' => $rows, 'bad' => array_values(array_unique($bad))];
    }

    /**
     * The addresses of one send: the typed ones plus the contacts ticked (ids), without repeats.
     * @param list<int> $contactIds
     * @return array{rows:list<array{name:string,email:string}>,bad:list<string>,over:bool}
     */
    public static function recipients(string $typed, array $contactIds): array
    {
        $parsed = self::parseList($typed);
        $rows = [];
        $seen = [];
        foreach ($parsed['rows'] as $r) {
            $seen[$r['email']] = true;
            $rows[] = ['name' => $r['name'], 'email' => $r['email']];
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $contactIds), static fn(int $i): bool => $i > 0)));
        if ($ids !== [] && self::ready()) {
            foreach (Database::all('SELECT name, email FROM letter_contacts WHERE id IN (' . implode(',', $ids) . ')') as $c) {
                $e = strtolower((string) $c['email']);
                if (!isset($seen[$e]) && Mailer::validEmail($e)) {
                    $seen[$e] = true;
                    $rows[] = ['name' => (string) $c['name'], 'email' => $e];
                }
            }
        }
        $over = count($rows) > self::MAX_RECIPIENTS;
        return ['rows' => array_slice($rows, 0, self::MAX_RECIPIENTS), 'bad' => $parsed['bad'], 'over' => $over];
    }

    /** @return array<string,bool> the addresses this letter was already sent to (status sent) */
    public static function alreadySent(int $letterId): array
    {
        $out = [];
        foreach (Database::all("SELECT DISTINCT email FROM letter_recipients WHERE letter_id = ? AND status = 'sent'", [$letterId]) as $r) {
            $out[strtolower((string) $r['email'])] = true;
        }
        return $out;
    }

    /** @return list<array<string,mixed>> the sends of a letter, newest first */
    public static function sends(int $letterId): array
    {
        return self::ready() ? Database::all('SELECT s.*, u.name AS by_name FROM letter_sends s LEFT JOIN users u ON u.id = s.created_by WHERE s.letter_id = ? ORDER BY s.id DESC LIMIT 50', [$letterId]) : [];
    }

    /** @return list<array<string,mixed>> */
    public static function sendRecipients(int $sendId): array
    {
        return Database::all('SELECT name, email, status, error, sent_at FROM letter_recipients WHERE send_id = ? ORDER BY id', [$sendId]);
    }

    // ------------------------------------------------------------------------------------------------------------- sending

    /**
     * Send the letter as a PDF attachment.
     * $opts: mode 'each' (one e-mail per person) | 'one' (ONE e-mail to everybody), hide (true = recipients in Bcc, nobody sees the others; only for 'one'), personalise (address each letter to the
     * person by name; only for 'each' and only people with a name), subject, message (the words of the e-mail), reply_to, again (also send to addresses that already got this letter), cap (most e-mails in one run).
     * @param array<string,mixed> $letter
     * @param list<array{name:string,email:string}> $to
     * @param array<string,mixed> $opts
     * @return array{send_id:int,sent:int,failed:int,skipped:int,remaining:int,stopped:?string}
     */
    public static function send(array $letter, array $to, array $opts, ?int $userId): array
    {
        $mode = ($opts['mode'] ?? 'each') === 'one' ? 'one' : 'each';
        $again = !empty($opts['again']);
        $cap = max(1, min(self::MAX_RECIPIENTS, (int) ($opts['cap'] ?? 100)));
        $done = $again ? [] : self::alreadySent((int) $letter['id']);
        $todo = [];
        $skipped = 0;
        foreach ($to as $r) {
            if (isset($done[strtolower($r['email'])])) {
                $skipped++;
                continue;
            }
            $todo[] = $r;
        }
        $subject = trim((string) ($opts['subject'] ?? '')) ?: ('Official letter: ' . (string) $letter['subject']);
        $message = trim((string) ($opts['message'] ?? '')) ?: self::defaultMessage($letter);
        $sendId = (int) Database::insert('letter_sends', ['letter_id' => (int) $letter['id'], 'mode' => $mode, 'subject' => mb_substr($subject, 0, 300), 'message' => mb_substr($message, 0, 4000), 'total' => count($todo), 'created_by' => $userId]);
        $sent = 0;
        $failed = 0;
        $stopped = null;
        $replyTo = (string) ($opts['reply_to'] ?? '');
        $base = ['reply_to' => Mailer::validEmail($replyTo) ? $replyTo : null];
        $log = static function (array $r, bool $ok, ?string $err) use ($sendId, $letter): void {
            Database::insert('letter_recipients', ['send_id' => $sendId, 'letter_id' => (int) $letter['id'], 'name' => mb_substr($r['name'], 0, 190), 'email' => mb_substr($r['email'], 0, 190), 'status' => $ok ? 'sent' : 'failed', 'error' => $ok ? null : mb_substr((string) $err, 0, 250)]);
        };

        if ($mode === 'one') {
            $pdf = self::pdf($letter);
            $emails = array_column($todo, 'email');
            if ($emails !== []) {
                $hide = !empty($opts['hide']);
                $own = (string) \App\Core\Config::get('mail.from', '');
                $mail = self::mailParts($letter, $message, null);
                $r = Mailer::attempt($hide ? [$own] : $emails, $subject, $mail['text'], array_filter($base + [
                    'html' => $mail['html'], 'bcc' => $hide ? $emails : [], 'attachments' => [['filename' => self::pdfName($letter), 'content' => $pdf, 'type' => 'application/pdf']],
                ], static fn($v) => $v !== null));
                foreach ($todo as $row) {
                    $log($row, $r['ok'], $r['error']);
                }
                if ($r['ok']) {
                    $sent = count($todo);
                } else {
                    $failed = count($todo);
                    $stopped = (string) $r['error'];
                }
            }
            $remaining = 0;
        } else {
            $same = null;
            $streak = 0;
            $count = 0;
            foreach ($todo as $row) {
                if ($count >= $cap) {
                    break;
                }
                $named = !empty($opts['personalise']) && trim($row['name']) !== '';
                if ($named) {
                    $pdf = self::pdf($letter, ['to_name' => $row['name'], 'salutation' => 'Dear ' . $row['name'] . ',']);
                } else {
                    $same ??= self::pdf($letter);
                    $pdf = $same;
                }
                $mail = self::mailParts($letter, $message, $named ? $row['name'] : null);
                $r = Mailer::attempt([$row['email']], $subject, $mail['text'], array_filter($base + [
                    'html' => $mail['html'], 'to_name' => $named ? $row['name'] : null, 'attachments' => [['filename' => self::pdfName($letter), 'content' => $pdf, 'type' => 'application/pdf']],
                ], static fn($v) => $v !== null));
                $count++;
                $log($row, $r['ok'], $r['error']);
                if ($r['ok']) {
                    $sent++;
                    $streak = 0;
                    continue;
                }
                $failed++;
                // a refused login (or three in a row) is the same for everyone: stop, so the account is not locked for longer
                if (++$streak >= 3 || str_contains((string) $r['error'], 'SMTP login')) {
                    $stopped = (string) $r['error'];
                    break;
                }
            }
            $remaining = max(0, count($todo) - $sent - $failed);
        }
        Database::update('letter_sends', ['sent_count' => $sent, 'failed_count' => $failed, 'total' => count($todo)], 'id = ?', [$sendId]);
        if ($sent > 0 && (string) $letter['status'] !== 'sent') {
            Database::update('letters', ['status' => 'sent'], 'id = ?', [(int) $letter['id']]);
        }
        return ['send_id' => $sendId, 'sent' => $sent, 'failed' => $failed, 'skipped' => $skipped, 'remaining' => $remaining, 'stopped' => $stopped];
    }

    /** The default words of the e-mail that carries the letter. */
    public static function defaultMessage(array $letter): string
    {
        $site = trim((string) setting('site_name', 'Lumina Africa Foundation'));
        $ref = trim((string) ($letter['ref_no'] ?? ''));
        return "Please find attached an official letter from " . $site . ($ref !== '' ? ' (reference ' . $ref . ')' : '') . ".\n\nIt is attached to this e-mail as a PDF. If you have any questions, simply reply to this message.";
    }

    /** @return array{text:string,html:string} */
    private static function mailParts(array $letter, string $message, ?string $name): array
    {
        $site = trim((string) setting('site_name', 'Lumina Africa Foundation'));
        $hello = $name !== null && trim($name) !== '' ? 'Dear ' . trim($name) . ',' : '';
        $text = ($hello !== '' ? $hello . "\n\n" : '') . $message . "\n\n" . $site . "\nIgniting Light. Inspiring Change.";
        $html = '<!doctype html><html><body style="margin:0;padding:24px;background:#f4f1ea;font-family:Arial,Helvetica,sans-serif;color:#242A32"><div style="max-width:560px;margin:0 auto;background:#fff;border-radius:14px;padding:28px">'
            . '<p style="margin:0 0 18px;font-size:13px;letter-spacing:.12em;text-transform:uppercase;color:#A5521A;font-weight:700">' . htmlspecialchars($site, ENT_QUOTES) . '</p>'
            . ($hello !== '' ? '<p style="margin:0 0 14px;font-size:16px">' . htmlspecialchars($hello, ENT_QUOTES) . '</p>' : '')
            . '<p style="margin:0 0 16px;line-height:1.6;font-size:16px">' . nl2br(htmlspecialchars($message, ENT_QUOTES), false) . '</p>'
            . '<p style="margin:18px 0 0;font-size:14px;color:#5B6478">' . htmlspecialchars($site, ENT_QUOTES) . '<br>Igniting Light. Inspiring Change.</p></div></body></html>';
        return ['text' => $text, 'html' => $html];
    }
}
