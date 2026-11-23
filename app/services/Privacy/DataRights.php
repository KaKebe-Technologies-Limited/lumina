<?php
declare(strict_types=1);

namespace App\Services\Privacy;

use App\Core\Database;
use App\Core\Mailer;

/**
 * Data-subject requests (rights under the Data Protection and Privacy Act, 2019): access, correction, deletion, objection, withdrawing consent, complaint.
 * A request is stored as a contact message of kind 'dsr' (Admin > Messages) with a reference number and a response target date, and the person gets
 * an acknowledgement e-mail. The target (default 30 days) is a TARGET, never a legal promise. Editable in Admin > Pages > Data rights request.
 */
final class DataRights
{
    public const TYPES = [
        'access'     => 'See the personal data you hold about me',
        'correction' => 'Correct something that is wrong',
        'deletion'   => 'Delete my personal data',
        'objection'  => 'Stop using my data for a purpose (object)',
        'withdraw'   => 'Withdraw my consent (for example the newsletter or a photo)',
        'complaint'  => 'Make a complaint about how my data is handled',
        'other'      => 'Something else about my personal data',
    ];

    public const DEFAULT_DAYS = 30;

    /** Days we aim to answer within (registry field, bounded 1..90). */
    public static function targetDays(): int
    {
        $d = (int) content('data-rights', 'form.response_days', self::DEFAULT_DAYS);
        return $d >= 1 && $d <= 90 ? $d : self::DEFAULT_DAYS;
    }

    public static function newReference(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        for ($try = 0; $try < 5; $try++) {
            $suffix = '';
            for ($i = 0; $i < 5; $i++) {
                $suffix .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $ref = 'DPR-' . date('ymd') . '-' . $suffix;
            if ((int) Database::value('SELECT COUNT(*) FROM contact_messages WHERE reference = ?', [$ref]) === 0) {
                return $ref;
            }
        }
        return 'DPR-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }

    /**
     * Records a request and sends the acknowledgement. Returns ['id'=>int,'reference'=>string,'due'=>'Y-m-d','mailed'=>bool].
     * $info: name, email, phone, type (key of TYPES), details, ip, user_agent.
     */
    public static function record(array $info): array
    {
        $type = isset(self::TYPES[$info['type'] ?? '']) ? (string) $info['type'] : 'other';
        $ref = self::newReference();
        $due = date('Y-m-d', strtotime('+' . self::targetDays() . ' days'));
        $details = trim((string) ($info['details'] ?? ''));
        $id = (int) Database::insert('contact_messages', [
            'name'         => (string) $info['name'],
            'email'        => (string) $info['email'],
            'phone'        => ($info['phone'] ?? '') !== '' ? (string) $info['phone'] : null,
            'subject'      => 'Data protection request ' . $ref . ': ' . self::TYPES[$type],
            'message'      => $details !== '' ? $details : '(no further details given)',
            'status'       => 'new',
            'ip'           => $info['ip'] ?? null,
            'user_agent'   => $info['user_agent'] ?? null,
            'kind'         => 'dsr',
            'reference'    => $ref,
            'request_type' => $type,
            'due_at'       => $due,
        ]);
        $site = (string) setting('site_name', 'Lumina Africa Foundation');
        $days = self::targetDays();
        Mailer::notify('Data protection request ' . $ref . ' (' . self::TYPES[$type] . ')', implode("\n", [
            'A data-protection rights request arrived. Please answer by ' . date('j F Y', strtotime($due)) . ' (our ' . $days . '-day target).',
            'Reference: ' . $ref,
            'Type: ' . self::TYPES[$type],
            'Name: ' . $info['name'],
            'Email: ' . $info['email'],
            '',
            $details,
            '',
            'Open in the admin: ' . abs_url('admin/messages/' . $id),
        ]), (string) $info['email']);
        $mailed = Mailer::send((string) $info['email'], 'We received your data request ' . $ref, implode("\n", [
            'Hello ' . $info['name'] . ',',
            '',
            'Thank you. ' . $site . ' has received your data protection request.',
            '',
            'Your reference: ' . $ref,
            'Request: ' . self::TYPES[$type],
            '',
            'We aim to answer within ' . $days . ' days (by ' . date('j F Y', strtotime($due)) . '). This is our target, not a legal promise: if we need more time or need to check who you are first, we will tell you.',
            'Please keep this reference if you write to us again about the request.',
            '',
            'We will never ask you for a password or payment details to deal with this request.',
            '',
            $site,
        ]));
        return ['id' => $id, 'reference' => $ref, 'due' => $due, 'mailed' => $mailed];
    }

    /** Open requests (not yet replied / archived), soonest due first, for the admin page. */
    public static function open(int $limit = 50): array
    {
        return Database::all("SELECT id, reference, request_type, name, email, status, due_at, created_at FROM contact_messages WHERE kind = 'dsr' AND status IN ('new','read') ORDER BY due_at ASC, id ASC LIMIT " . (int) $limit);
    }

    public static function recent(int $limit = 30): array
    {
        return Database::all("SELECT id, reference, request_type, name, email, status, due_at, replied_at, created_at FROM contact_messages WHERE kind = 'dsr' ORDER BY id DESC LIMIT " . (int) $limit);
    }

    /** @return array{open:int,overdue:int,due_soon:int,done:int} */
    public static function counts(): array
    {
        $r = Database::one("SELECT SUM(status IN ('new','read')) AS open_n, SUM(status IN ('new','read') AND due_at < CURDATE()) AS overdue_n,
            SUM(status IN ('new','read') AND due_at >= CURDATE() AND due_at <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)) AS soon_n, SUM(status IN ('replied','archived')) AS done_n
            FROM contact_messages WHERE kind = 'dsr'") ?: [];
        return ['open' => (int) ($r['open_n'] ?? 0), 'overdue' => (int) ($r['overdue_n'] ?? 0), 'due_soon' => (int) ($r['soon_n'] ?? 0), 'done' => (int) ($r['done_n'] ?? 0)];
    }
}
