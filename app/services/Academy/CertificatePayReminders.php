<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;
use App\Core\Mailer;

/**
 * Admin > Academy > Certificates: "ask the learners to pay BEFORE the certificate is approved".
 * It works on the certificates that wait for approval and whose certificate fee is not paid (fee_status none or due):
 *   1. each is put on "fee due", so the learner can pay it in the portal (a certificate with no fee due cannot be paid for);
 *   2. the learner gets ONE portal message and ONE e-mail that name every certificate waiting for payment, the fee and the link to pay.
 * A learner reminded in the last 24 hours is skipped (no spam). The wording adapts: pay online when a fee can be paid on the portal, otherwise reply / contact the team (and the manual payment details, if set).
 */
final class CertificatePayReminders
{
    /** @return list<array<string,mixed>> waiting, unpaid certificates with the learner's contact details (one row per certificate) */
    public static function waiting(?int $certId = null): array
    {
        if (!AcademyFees::ready() || !CertificateService::approvalReady()) {
            return [];
        }
        return Database::all(
            "SELECT c.id, c.learner_id, c.learner_name AS name, c.course_title, c.fee_status, l.email FROM academy_certificates c JOIN academy_learners l ON l.id = c.learner_id
             WHERE c.approved_at IS NULL AND c.revoked_at IS NULL AND c.fee_status IN ('none','due') AND l.is_active = 1" . ($certId !== null ? ' AND c.id = ' . (int) $certId : '') . ' ORDER BY c.learner_id, c.id'
        );
    }

    public static function count(): int
    {
        return count(self::waiting());
    }

    /** The wording sent to a learner: the certificate is ready, pay the fee to receive it. @param list<string> $courses */
    public static function text(string $name, array $courses): string
    {
        $fee = AcademyFees::configured('cert');
        $first = trim((string) strtok(trim($name), ' ')) ?: 'there';
        $many = count($courses) > 1;
        $list = $many ? count($courses) . ' courses (' . implode(', ', array_map(static fn(string $c): string => '"' . $c . '"', $courses)) . ')' : '"' . $courses[0] . '"';
        $how = AcademyFees::cert() !== null
            ? 'Sign in at ' . abs_url('academy/login') . ', open My certificates and pay online: ' . ($many ? 'each certificate is' : 'it is') . ' released as soon as the payment is confirmed.'
            : 'Reply to this message or contact the LAF Academy team to pay, and ' . ($many ? 'they are' : 'it is') . ' released as soon as the payment is confirmed.';
        $manual = AcademyFees::manual();
        $details = $manual !== null ? ' ' . trim(($manual['mobile'] !== '' ? 'Mobile money: ' . $manual['mobile'] . '. ' : '') . ($manual['bank'] !== '' ? 'Bank: ' . $manual['bank'] . '.' : '')) : '';
        return 'Hello ' . $first . ', this is the LAF Academy team. Good news: your certificate' . ($many ? 's' : '') . ' for ' . $list . ($many ? ' are' : ' is') . ' ready. '
            . 'Please pay the certificate fee' . ($fee !== null ? ' of ' . $fee['display'] . ($many ? ' for each certificate' : '') : '') . ' so that we can approve and release ' . ($many ? 'them' : 'it') . '. ' . $how . $details;
    }

    private static function recentlyReminded(int $learnerId): bool
    {
        return Database::value("SELECT 1 FROM academy_messages WHERE learner_id = ? AND sender = 'admin' AND body LIKE ? AND created_at > (NOW() - INTERVAL 1 DAY) LIMIT 1", [$learnerId, '%Please pay the certificate fee%'], null) !== null;
    }

    /**
     * Remind one certificate (its learner), or every waiting one when $certId is null.
     * @return array{ok:bool,message:string,learners:int,emailed:int,skipped:int,held:int}
     */
    public static function remind(?int $certId, ?int $adminId): array
    {
        $out = ['ok' => false, 'message' => '', 'learners' => 0, 'emailed' => 0, 'skipped' => 0, 'held' => 0];
        $byLearner = [];
        foreach (self::waiting($certId) as $r) {
            $byLearner[(int) $r['learner_id']][] = $r;
        }
        if ($byLearner === []) {
            $out['message'] = $certId !== null ? 'This certificate is not waiting for payment (it is approved, paid, waived or withdrawn).' : 'No certificate is waiting for approval with an unpaid fee, so nothing was sent.';
            return $out;
        }
        foreach ($byLearner as $learnerId => $certs) {
            if (self::recentlyReminded($learnerId)) {
                $out['skipped']++;
                continue;
            }
            foreach ($certs as $c) {
                if ($c['fee_status'] === 'none' && AcademyFees::cert() !== null) {      // on hold only when the learner can really pay online
                    $out['held'] += Database::execute("UPDATE academy_certificates SET fee_status = 'due', fee_paid_at = NULL WHERE id = ? AND fee_status = 'none' AND approved_at IS NULL", [(int) $c['id']]);
                }
            }
            $text = self::text((string) $certs[0]['name'], array_map(static fn(array $c): string => (string) $c['course_title'], $certs));
            if (!LearnerMessages::send($learnerId, 'admin', $text, $adminId)['ok']) {
                continue;
            }
            $out['learners']++;
            try {
                if (Mailer::validEmail((string) $certs[0]['email']) && AcademyMail::teamMessage($certs[0], $text)) {
                    $out['emailed']++;
                }
            } catch (\Throwable) {
            }
        }
        Mailer::closeConnection();
        $out['ok'] = $out['learners'] > 0;
        $out['message'] = $out['learners'] === 0
            ? ($out['skipped'] > 0 ? 'Everyone was already reminded in the last 24 hours.' : 'No reminder could be sent.')
            : 'Payment reminder sent to ' . $out['learners'] . ' learner' . ($out['learners'] === 1 ? '' : 's') . ' (portal message' . ($out['emailed'] > 0 ? ' and ' . $out['emailed'] . ' e-mail' . ($out['emailed'] === 1 ? '' : 's') : ', no e-mail address worked') . ').'
                . ($out['held'] > 0 ? ' ' . $out['held'] . ' certificate' . ($out['held'] === 1 ? ' is' : 's are') . ' now on hold until the fee is paid.' : '')
                . ($out['skipped'] > 0 ? ' ' . $out['skipped'] . ' skipped (reminded in the last 24 hours).' : '');
        return $out;
    }
}
