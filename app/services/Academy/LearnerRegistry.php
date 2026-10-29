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
