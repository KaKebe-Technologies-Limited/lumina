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
    /** Active learners whose account is approved (can sign in); $login narrows them to those who have / have never signed in. @return list<array<string,mixed>> */
    public static function approvedLearners(string $login = ''): array
    {
        try {
            $lc = LearnerRegistry::loginSql(LearnerRegistry::loginMode($login), 'last_login_at');
            return Database::all('SELECT id, name, email, reg_no, google_sub FROM academy_learners WHERE is_active = 1 AND email_verified_at IS NOT NULL' . ($lc !== null ? ' AND ' . $lc : '') . ' ORDER BY id');
        } catch (\Throwable) {
            return [];
        }
    }

    /** The portal message that records "this learner was sent the sign-in guide" (also what they read in the portal). It starts with the fixed words below, which is how a later run knows who is done. */
    public const SIGNIN_MARK = 'How to sign in to your LAF Academy account';

    /** The short portal copy of the sign-in guide. */
    public static function signInGuideText(array $l): string
    {
        $reg = trim((string) ($l['reg_no'] ?? ''));
        return self::SIGNIN_MARK . ': open ' . abs_url('academy/login') . ', type your e-mail address' . ($reg !== '' ? ' or your registration number (' . $reg . ')' : '') . ' and your password, then press Sign in. Forgot your password? Use the link on that page. We have also e-mailed you these steps.';
    }

    /** Approved learners who have NOT been sent the sign-in guide yet (or all when $again). @return list<array<string,mixed>> */
    public static function signInPending(string $login = '', bool $again = false): array
    {
        $all = self::approvedLearners($login);
        if ($again || !self::ready()) {
            return $all;
        }
        $done = [];
        foreach (Database::all("SELECT DISTINCT learner_id FROM academy_messages WHERE sender = 'admin' AND body LIKE ?", [self::SIGNIN_MARK . '%']) as $r) {
            $done[(int) $r['learner_id']] = true;
        }
        return array_values(array_filter($all, static fn(array $l): bool => !isset($done[(int) $l['id']])));
    }

    /**
     * E-mail the numbered sign-in guide to approved learners, a limited number per run (the mail account has a daily limit and a request has a time limit). A learner counts as done
     * once the e-mail went out AND the portal copy was saved, so pressing the button again continues with those still waiting and never writes to anybody twice (unless $again).
     * Stops at once when the mail server keeps refusing (for example the daily limit of the account).
     * @return array{sent:int,failed:int,remaining:int,total:int,stopped:?string}
     */
    public static function sendSignInGuides(?int $adminId, string $login, bool $again, int $maxEmails, int $maxSeconds = 40): array
    {
        $todo = self::signInPending($login, $again);
        $total = count($todo);
        $sent = 0;
        $failed = 0;
        $streak = 0;
        $stopped = null;
        $t0 = microtime(true);
        foreach ($todo as $l) {
            if ($sent >= $maxEmails) {
                break;
            }
            if (microtime(true) - $t0 > $maxSeconds) {
                $stopped = 'time';
                break;
            }
            if (AcademyMail::signInGuide($l)) {
                self::send((int) $l['id'], 'admin', self::signInGuideText($l), $adminId);
                $sent++;
                $streak = 0;
                continue;
            }
            $failed++;
            if (++$streak >= 3) {
                $stopped = AcademyMail::lastError() ?: 'The mail server did not accept the messages.';
                break;
            }
        }
        return ['sent' => $sent, 'failed' => $failed, 'remaining' => max(0, $total - $sent), 'total' => $total, 'stopped' => $stopped];
    }

    /** The default words of the reminder to approved learners; {name} becomes the learner's first name. */
    public static function defaultReminder(): string
    {
        return "Hello {name}, this is the LAF Academy team. A friendly reminder to keep going with your learning: sign in, open your courses and continue from where you stopped. Each lesson you finish brings you closer to your certificate. Sign in here: " . abs_url('academy/login');
    }

    /** Portal message (and e-mail when asked) to every approved learner. @return int how many learners were written to */
    public static function remindApproved(?int $adminId, string $text, bool $email, string $login = ''): int
    {
        $sent = 0;
        foreach (self::approvedLearners($login) as $l) {
            $first = trim((string) strtok(trim((string) $l['name']), ' ')) ?: 'there';
            $body = str_replace('{name}', $first, $text);
            if (!self::send((int) $l['id'], 'admin', $body, $adminId)['ok']) {
                continue;
            }
            if ($email) {
                \App\Services\Academy\AcademyMail::teamMessage($l, $body);
            }
            $sent++;
        }
        return $sent;
    }

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
    public static function inquire(string $name, string $email, string $phone, string $message, string $regNo = ''): array
    {
        $regInput = trim($regNo);
        $regNo = LearnerRegistry::normaliseNumber($regInput);
        if ($regInput !== '' && $regNo === '') {
            return ['ok' => false, 'error' => 'That does not look like a registration number. It looks like LAF-AC-2026-000123 (or leave the box empty).', 'field' => 'reg_no'];
        }
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
            $head = '[Sent from the contact form' . ($regNo !== '' ? ', registration number ' . $regNo : '') . ($phone !== '' ? ', phone ' . $phone : '') . "]\n";
            $r = self::send((int) $learner['id'], 'learner', $head . $text);
            return ['ok' => $r['ok'], 'error' => $r['ok'] ? null : $r['error']];
        }
        if (!self::inquiriesReady()) {
            return ['ok' => false, 'error' => 'Messages are not available yet. Please use the WhatsApp line.'];
        }
        // the registration number travels as the first line of the message (no extra column): the team sees it and the admin page links it to the learner
        Database::insert('academy_inquiries', ['name' => mb_substr($name, 0, 120), 'email' => $email, 'phone' => $phone !== '' ? mb_substr($phone, 0, 40) : null, 'message' => ($regNo !== '' ? self::REG_LINE . $regNo . "\n\n" : '') . $text]);
        return ['ok' => true, 'error' => null];
    }

    /** First words of the line that carries the registration number in an inquiry. */
    public const REG_LINE = 'Registration number: ';

    /**
     * The registration number written at the top of an inquiry, the message without that line, and the learner it belongs to (or null).
     * @return array{reg:string,text:string,learner:?array{id:int,name:string}}
     */
    public static function splitInquiry(string $message): array
    {
        if (preg_match('/^' . preg_quote(self::REG_LINE, '/') . '(LAF-AC-\d{4}-\d{6})\R\R?/', $message, $m) !== 1) {
            return ['reg' => '', 'text' => $message, 'learner' => null];
        }
        $l = LearnerRegistry::ready() ? Database::one('SELECT id, name FROM academy_learners WHERE reg_no = ? LIMIT 1', [$m[1]]) : null;
        return ['reg' => $m[1], 'text' => (string) substr($message, strlen($m[0])), 'learner' => $l !== null ? ['id' => (int) $l['id'], 'name' => (string) $l['name']] : null];
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
    public static function inbox(int $limit = 100, ?string $q = null, string $login = ''): array
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
        if (($lc = LearnerRegistry::loginSql($login)) !== null) {
            $where .= ($where === '' ? ' WHERE ' : ' AND ') . $lc;
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
