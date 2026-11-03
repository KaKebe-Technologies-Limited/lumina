<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\AuditLog;
use App\Core\Database;
use App\Core\Log;
use App\Core\Mailer;

/**
 * LAF Academy: registration numbers, the administrator's approval and the notice to the Academy team about every new account.
 *
 * - Every learner gets a registration number (LAF-AC-2026-000123: year of sign-up + the account number) the moment the account exists, however it was made
 *   (the sign-up form, "Continue with Google", or an administrator). Older learners got theirs from the schema migration.
 * - A learner can start when the e-mail address is confirmed (link, 6-digit code, Google) OR an administrator approves the account: acceptance never depends on
 *   an e-mail arriving. "Approve" sets the same "confirmed" mark the sign-in already checks, and records who approved and when.
 * - Every new account is written to the activity log (it shows on the dashboard) and e-mailed to the organisation (best effort: a failed e-mail changes nothing).
 *
 * All of it degrades gracefully while the columns are missing (ready() is false until database/schema.sql has been applied).
 */
final class LearnerRegistry
{
    /** The "Sign-in" filter of the Academy reports: a learner has SIGNED IN when last_login_at is set (it is written only by a successful sign-in). */
    public const LOGIN_FILTER = ['' => 'Any', 'yes' => 'Has signed in', 'no' => 'Never signed in'];

    public static function loginMode(string $v): string
    {
        return isset(self::LOGIN_FILTER[$v]) ? $v : '';
    }

    /** SQL for the sign-in filter on a column (default l.last_login_at), or null for "any". */
    public static function loginSql(string $mode, string $col = 'l.last_login_at'): ?string
    {
        return match ($mode) {
            'yes' => $col . ' IS NOT NULL',
            'no' => $col . ' IS NULL',
            default => null,
        };
    }

    public static function ready(): bool
    {
        static $ok = null;
        if ($ok === null) {
            try {
                $ok = (int) Database::value("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name IN ('reg_no', 'approved_at', 'approved_by')", [], 0) === 3;
            } catch (\Throwable) {
                $ok = false;
            }
        }
        return $ok;
    }

    /** True when the rejection columns exist (the code can be uploaded before the database update: Reject is then hidden). */
    public static function rejectReady(): bool
    {
        static $ok = null;
        if ($ok === null) {
            try {
                $ok = (int) Database::value("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name IN ('rejected_at', 'rejected_by', 'reject_reason')", [], 0) === 3;
            } catch (\Throwable) {
                $ok = false;
            }
        }
        return $ok;
    }

    /**
     * Reject an application: the account is switched off, marked as rejected (when, by whom, why) and cannot sign in; at the next sign-in attempt the applicant is told, with
     * the reason, and can write to the Academy team to appeal. Only a learner who is not approved yet can be rejected.
     */
    public static function reject(int $id, ?int $by, string $reason): bool
    {
        if (!self::rejectReady()) {
            return false;
        }
        $l = Database::one('SELECT id, email_verified_at, rejected_at FROM academy_learners WHERE id = ?', [$id]);
        if ($l === null || $l['rejected_at'] !== null || $l['email_verified_at'] !== null) {
            return false;
        }
        $reason = trim((string) preg_replace('/\s+/u', ' ', strip_tags($reason)));
        Database::update('academy_learners', ['is_active' => 0, 'rejected_at' => date('Y-m-d H:i:s'), 'rejected_by' => $by, 'reject_reason' => $reason !== '' ? mb_substr($reason, 0, 300) : null, 'token_hash' => null, 'token_kind' => null, 'token_expires_at' => null], 'id = ?', [$id]);
        return true;
    }

    /** A registration number typed or pasted by a person ("laf-ac-2026-000123", with spaces, without dashes ...) in its canonical form, or '' when it is not one. */
    public static function normaliseNumber(string $v): string
    {
        $v = strtoupper((string) preg_replace('/[\s\x{00A0}]+/u', '', trim($v)));
        return preg_match('/^LAF-?AC-?(\d{4})-?(\d{6})$/', $v, $m) === 1 ? 'LAF-AC-' . $m[1] . '-' . $m[2] : '';
    }

    /**
     * WHERE IS MY APPLICATION: what a learner may learn from the registration number alone. Only the STATE is returned (never a name, e-mail or phone).
     * @return array{found:bool,number:string,registered_on:?string,steps:list<array{label:string,state:string,note:string}>,headline:string,tone:string,can_sign_in:bool}
     */
    public static function track(string $input): array
    {
        $no = self::normaliseNumber($input);
        $none = ['found' => false, 'number' => $no, 'registered_on' => null, 'steps' => [], 'headline' => '', 'tone' => 'neutral', 'can_sign_in' => false];
        if ($no === '' || !self::ready()) {
            return $none;
        }
        $fee = AcademyFees::ready() ? ', fee_status' : ", 'none' AS fee_status";
        $rej = self::rejectReady() ? ', rejected_at' : ', NULL AS rejected_at';
        $l = Database::one('SELECT created_at, is_active, email_verified_at, approved_at' . $fee . $rej . ' FROM academy_learners WHERE reg_no = ? LIMIT 1', [$no]);
        if ($l === null) {
            return $none;
        }
        $active = (int) $l['is_active'] === 1;
        $rejected = $l['rejected_at'] !== null;
        $approved = $l['email_verified_at'] !== null;
        $feeStatus = (string) ($l['fee_status'] ?? 'none');
        $steps = [['label' => 'Account created', 'state' => 'done', 'note' => 'Your registration number exists in the LAF Academy.']];
        if ($feeStatus === 'due') {
            $steps[] = ['label' => 'Sign-up fee', 'state' => 'todo', 'note' => 'The sign-up fee is not paid yet. Pay it, or message the Academy team if you already did.'];
        } elseif ($feeStatus === 'paid') {
            $steps[] = ['label' => 'Sign-up fee', 'state' => 'done', 'note' => 'The sign-up fee is paid.'];
        } elseif ($feeStatus === 'waived') {
            $steps[] = ['label' => 'Sign-up fee', 'state' => 'done', 'note' => 'The sign-up fee was waived.'];
        }
        if ($rejected) {
            $steps[] = ['label' => 'Approval', 'state' => 'stop', 'note' => 'The application was not approved. Message the Academy team and we will look at it again.'];
        } elseif ($approved) {
            $steps[] = ['label' => 'Approval', 'state' => 'done', 'note' => 'Approved' . ($l['approved_at'] !== null ? ' on ' . date('j M Y', strtotime((string) $l['approved_at'])) : '') . '.'];
        } else {
            $steps[] = ['label' => 'Approval', 'state' => 'todo', 'note' => 'Waiting for the Academy team to approve the account.'];
        }
        $steps[] = ['label' => 'Account active', 'state' => $active && !$rejected ? ($approved ? 'done' : 'todo') : 'stop', 'note' => $rejected ? 'The account cannot be used.' : ($active ? ($approved ? 'The account is active: you can sign in.' : 'It becomes active when it is approved.') : 'The account is switched off. Message the Academy team.')];
        if ($rejected) {
            [$headline, $tone] = ['Not approved', 'bad'];
        } elseif (!$active) {
            [$headline, $tone] = ['Switched off', 'bad'];
        } elseif ($feeStatus === 'due') {
            [$headline, $tone] = ['Waiting for the sign-up fee', 'warn'];
        } elseif (!$approved) {
            [$headline, $tone] = ['Waiting for approval', 'warn'];
        } else {
            [$headline, $tone] = ['Approved and active', 'good'];
        }
        return ['found' => true, 'number' => $no, 'registered_on' => date('j M Y', strtotime((string) $l['created_at'])), 'steps' => $steps, 'headline' => $headline, 'tone' => $tone, 'can_sign_in' => $tone === 'good'];
    }

    public static function format(int $id, string $createdAt): string
    {
        $year = (int) substr($createdAt, 0, 4);
        return 'LAF-AC-' . ($year >= 2000 ? $year : (int) date('Y')) . '-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    /** Give the learner a registration number if there is none yet. Returns it ('' while the column does not exist). */
    public static function assign(int $id): string
    {
        if (!self::ready()) {
            return '';
        }
        $row = Database::one('SELECT reg_no, created_at FROM academy_learners WHERE id = ?', [$id]);
        if ($row === null) {
            return '';
        }
        if ((string) ($row['reg_no'] ?? '') !== '') {
            return (string) $row['reg_no'];
        }
        $no = self::format($id, (string) $row['created_at']);
        Database::update('academy_learners', ['reg_no' => $no], 'id = ? AND reg_no IS NULL', [$id]);
        return $no;
    }

    /** A new account exists: activity log (dashboard) + e-mail to the organisation. $via: 'sign-up form', 'Google', 'administrator'. */
    public static function announce(int $id, string $via): void
    {
        $l = Database::one('SELECT name, email, phone, country, email_verified_at FROM academy_learners WHERE id = ?', [$id]);
        if ($l === null) {
            return;
        }
        $no = self::assign($id);
        $waiting = $l['email_verified_at'] === null;
        AuditLog::record('academy.learner.register', 'academy_learner', $id, 'New Academy account' . ($no !== '' ? ' ' . $no : '') . ': ' . $l['name'] . ' (' . $via . ')' . ($waiting ? ', waiting for approval' : ''), ['via' => $via, 'reg_no' => $no]);
        try {
            Mailer::notify(
                'New LAF Academy account' . ($no !== '' ? ' ' . $no : '') . ': ' . mb_substr((string) $l['name'], 0, 60),
                "A new learner has signed up to the LAF Academy.\n\n"
                . ($no !== '' ? 'Registration no.: ' . $no . "\n" : '')
                . 'Name: ' . $l['name'] . "\nE-mail: " . $l['email'] . "\n"
                . ((string) ($l['phone'] ?? '') !== '' ? 'Phone: ' . $l['phone'] . "\n" : '')
                . ((string) ($l['country'] ?? '') !== '' ? 'Country: ' . $l['country'] . "\n" : '')
                . 'Signed up with: ' . $via . "\n"
                . 'Status: ' . ($waiting ? 'waiting (e-mail not confirmed yet): approve the account if the learner cannot receive e-mail' : 'active') . "\n\n"
                . 'Open the learner: ' . abs_url('admin/academy/learners/' . $id)
            );
        } catch (\Throwable $e) {
            Log::warning('Academy sign-up notice failed: ' . $e->getMessage());
        }
    }

    /** Administrator approval: the learner can sign in now, whether or not the e-mail ever arrives. */
    public static function approve(int $id, ?int $by): bool
    {
        $l = Database::one('SELECT * FROM academy_learners WHERE id = ?', [$id]);
        if ($l === null || $l['email_verified_at'] !== null) {
            return false;
        }
        if (AcademyFees::signupDue($l)) {
            return false;                         // the sign-up fee comes first (paid, or waived by an administrator)
        }
        $upd = ['email_verified_at' => date('Y-m-d H:i:s'), 'token_hash' => null, 'token_kind' => null, 'token_expires_at' => null];
        if (self::rejectReady() && $l['rejected_at'] !== null) {
            $upd += ['is_active' => 1, 'rejected_at' => null, 'rejected_by' => null, 'reject_reason' => null];        // reconsidered: the account is on again
        }
        if (self::ready()) {
            $upd['approved_at'] = date('Y-m-d H:i:s');
            $upd['approved_by'] = $by;
        }
        Database::update('academy_learners', $upd, 'id = ?', [$id]);
        self::assign($id);
        return true;
    }

    /** Approve every switched-on learner who is still waiting. Returns how many. */
    public static function approveAll(?int $by): int
    {
        $ids = array_map('intval', array_column(Database::all('SELECT id FROM academy_learners WHERE is_active = 1 AND email_verified_at IS NULL'), 'id'));
        $n = 0;
        foreach ($ids as $id) {
            $n += self::approve($id, $by) ? 1 : 0;
        }
        return $n;
    }

    public static function waiting(): int
    {
        return (int) Database::value('SELECT COUNT(*) FROM academy_learners WHERE is_active = 1 AND email_verified_at IS NULL', [], 0);
    }

    /** Dashboard tile for people who manage the Academy; null for everybody else. @return array<string,mixed>|null */
    public static function dashboardTile(): ?array
    {
        if (!can('academy.manage')) {
            return null;
        }
        try {
            $waiting = self::waiting();
            $total = (int) Database::value('SELECT COUNT(*) FROM academy_learners', [], 0);
            $week = (int) Database::value('SELECT COUNT(*) FROM academy_learners WHERE created_at >= ?', [date('Y-m-d H:i:s', time() - 7 * 86400)], 0);
        } catch (\Throwable) {
            return null;
        }
        return ['icon' => 'users', 'value' => number_format($waiting), 'label' => 'Academy learners waiting for approval',
            'sub' => number_format($week) . ' new this week, ' . number_format($total) . ' in all',
            'url' => $waiting > 0 ? 'admin/academy/learners?status=unverified' : 'admin/academy/learners', 'alert' => $waiting > 0];
    }
}
