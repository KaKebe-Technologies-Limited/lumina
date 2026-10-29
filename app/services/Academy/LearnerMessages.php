<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;

/**
 * LAF Academy: the messages between the Academy team and each learner (one conversation per learner). The team writes from Admin > LAF Academy > Messages (or the Message
 * button on a learner), the learner reads and answers in the portal (My portal > Messages). "read_at" is set when the OTHER side has opened the conversation, which gives both
 * sides their unread counts. Messages are plain text.
 */
final class LearnerMessages
{
    public const MAX = 2000;

    public static function ready(): bool
    {
        static $ok = null;
        if ($ok === null) {
            try {
                $ok = (int) Database::value("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'academy_messages'", [], 0) === 1;
            } catch (\Throwable) {
                $ok = false;
            }
        }
        return $ok;
    }

    /** Plain text, tags removed, line breaks kept (at most two in a row), at most MAX characters. */
    public static function clean(string $text): string
    {
        $t = str_replace(["\r\n", "\r"], "\n", mb_scrub($text, 'UTF-8'));
        $t = trim((string) preg_replace("/\n{3,}/", "\n\n", strip_tags($t)));
        return mb_substr($t, 0, self::MAX);
    }

    /** @return array{ok:bool,error:?string,id:int} */
    public static function send(int $learnerId, string $sender, string $body, ?int $adminId = null): array
    {
        $body = self::clean($body);
        if (!self::ready() || !in_array($sender, ['admin', 'learner'], true)) {
            return ['ok' => false, 'error' => 'Messages are not available yet.', 'id' => 0];
        }
        if (mb_strlen($body) < 2) {
            return ['ok' => false, 'error' => 'Please write your message.', 'id' => 0];
        }
        if (Database::value('SELECT 1 FROM academy_learners WHERE id = ?', [$learnerId]) === null) {
            return ['ok' => false, 'error' => 'This learner does not exist.', 'id' => 0];
        }
        $id = (int) Database::insert('academy_messages', ['learner_id' => $learnerId, 'sender' => $sender, 'admin_id' => $sender === 'admin' ? $adminId : null, 'body' => $body]);
        return ['ok' => true, 'error' => null, 'id' => $id];
    }

    /** The conversation, oldest first (the last $limit messages). @return list<array<string,mixed>> */
    public static function thread(int $learnerId, int $limit = 200): array
    {
        if (!self::ready()) {
            return [];
        }
        $rows = Database::all('SELECT m.*, u.name AS admin_name FROM academy_messages m LEFT JOIN users u ON u.id = m.admin_id WHERE m.learner_id = ? ORDER BY m.id DESC LIMIT ' . max(1, min(500, $limit)), [$learnerId]);
        return array_reverse($rows);
    }

    /** The reader opened the conversation: what the other side wrote is now read. $reader = 'learner' (reads the team's messages) or 'admin'. */
    public static function markRead(int $learnerId, string $reader): void
    {
        if (self::ready()) {
            Database::execute('UPDATE academy_messages SET read_at = ? WHERE learner_id = ? AND sender = ? AND read_at IS NULL', [date('Y-m-d H:i:s'), $learnerId, $reader === 'learner' ? 'admin' : 'learner']);
        }
    }

    /** Messages from the team the learner has not read yet. */
    public static function unreadForLearner(int $learnerId): int
    {
        return self::ready() ? (int) Database::value("SELECT COUNT(*) FROM academy_messages WHERE learner_id = ? AND sender = 'admin' AND read_at IS NULL", [$learnerId], 0) : 0;
    }

    /** What waits for the team (the yellow number in the admin): learners' unread messages + open inquiries from visitors. */
    public static function unreadForAdmin(): int
    {
        return self::unreadMessages() + self::openInquiries();
    }

    /** Messages from learners the team has not read yet. */
    public static function unreadMessages(): int
    {
        return self::ready() ? (int) Database::value("SELECT COUNT(*) FROM academy_messages WHERE sender = 'learner' AND read_at IS NULL", [], 0) : 0;
    }

    public static function inquiriesReady(): bool
    {
        static $ok = null;
        if ($ok === null) {
            try {
                $ok = (int) Database::value("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'academy_inquiries'", [], 0) === 1;
            } catch (\Throwable) {
                $ok = false;
            }
        }
        return $ok;
    }

    public static function openInquiries(): int
    {
        return self::inquiriesReady() ? (int) Database::value('SELECT COUNT(*) FROM academy_inquiries WHERE handled_at IS NULL', [], 0) : 0;
    }

    /** @return list<array<string,mixed>> inquiries from visitors, open ones first */
    public static function inquiries(int $limit = 100): array
    {
        return self::inquiriesReady() ? Database::all('SELECT * FROM academy_inquiries ORDER BY (handled_at IS NULL) DESC, id DESC LIMIT ' . max(1, min(300, $limit))) : [];
    }

    /**
     * A message from the public "Message the Academy team" form (a new or waiting learner, or somebody with a question). The sender's e-mail address finds the account when there is
     * one: the message then goes into that learner's conversation (the registration number shows in the admin), otherwise it is kept as an inquiry. The answer is always the same
     * to the visitor, so the form never tells whether an address has an account. @return array{ok:bool,error:?string}
     */
    public static function inquire(string $name, string $email, string $phone, string $message): array
    {
        $name = trim((string) preg_replace('/\s+/u', ' ', strip_tags($name)));
        $email = LearnerAuth::normaliseEmail($email);
        $phone = trim((string) preg_replace('/[^0-9+()\- ]/', '', $phone));
        $text = self::clean($message);
        if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
            return ['ok' => false, 'error' => 'Please enter your name.'];
        }
        if (!\App\Core\Mailer::validEmail($email) || strlen($email) > 190) {
            return ['ok' => false, 'error' => 'Please enter a valid e-mail address so the team can answer you.'];
        }
        if (mb_strlen($text) < 5) {
            return ['ok' => false, 'error' => 'Please write your message.'];
        }
        $learner = self::ready() ? Database::one('SELECT id FROM academy_learners WHERE email = ? LIMIT 1', [$email]) : null;
        if ($learner !== null) {
            $head = $phone !== '' ? '[Sent from the contact form, phone ' . $phone . ']' . "\n" : "[Sent from the contact form]\n";
            $r = self::send((int) $learner['id'], 'learner', $head . $text);
            return ['ok' => $r['ok'], 'error' => $r['ok'] ? null : $r['error']];
        }
        if (!self::inquiriesReady()) {
            return ['ok' => false, 'error' => 'Messages are not available yet. Please use the WhatsApp line.'];
        }
        Database::insert('academy_inquiries', ['name' => mb_substr($name, 0, 120), 'email' => $email, 'phone' => $phone !== '' ? mb_substr($phone, 0, 40) : null, 'message' => $text]);
        return ['ok' => true, 'error' => null];
    }

    /** Unread messages from one learner. */
    public static function unreadFrom(int $learnerId): int
    {
        return self::ready() ? (int) Database::value("SELECT COUNT(*) FROM academy_messages WHERE learner_id = ? AND sender = 'learner' AND read_at IS NULL", [$learnerId], 0) : 0;
    }

    /** The newest message of the conversation, or null. @return array<string,mixed>|null */
    public static function latest(int $learnerId): ?array
    {
        return self::ready() ? Database::one('SELECT * FROM academy_messages WHERE learner_id = ? ORDER BY id DESC LIMIT 1', [$learnerId]) : null;
    }

    /** The inbox of the team: one row per learner who has a conversation, unread first, then newest. @return list<array<string,mixed>> */
    public static function inbox(int $limit = 100, ?string $q = null): array
    {
        if (!self::ready()) {
            return [];
        }
        $where = '';
        $params = [];
        if ($q !== null && $q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $where = ' WHERE (l.name LIKE ? OR l.email LIKE ? OR l.reg_no LIKE ?)';
            array_push($params, $like, $like, $like);
        }
        $reg = LearnerRegistry::ready() ? 'l.reg_no' : 'NULL AS reg_no';
        return Database::all(
            'SELECT l.id, l.name, l.email, l.email_verified_at, l.is_active, ' . $reg . ', t.last_id, t.total, t.unread, m.sender AS last_sender, m.body AS last_body, m.created_at AS last_at FROM academy_learners l '
            . 'JOIN (SELECT learner_id, MAX(id) AS last_id, COUNT(*) AS total, SUM(sender = \'learner\' AND read_at IS NULL) AS unread FROM academy_messages GROUP BY learner_id) t ON t.learner_id = l.id '
            . 'JOIN academy_messages m ON m.id = t.last_id' . $where . ' ORDER BY (t.unread > 0) DESC, t.last_id DESC LIMIT ' . max(1, min(300, $limit)),
            $params
        );
    }
}
