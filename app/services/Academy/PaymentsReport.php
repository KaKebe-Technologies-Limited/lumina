<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;
use App\Services\Shop\Money;

/**
 * Admin > Academy > Payments: every sign-up fee and certificate fee as it comes in, from two places that are put together here:
 *   - Pesapal payments (table pesapal_payments, purposes academy_signup / academy_cert): real amounts, method and confirmation code;
 *   - payments an administrator confirmed by hand (mobile money, bank transfer, Daraza: learners / certificates with fee_status 'paid' and no Pesapal payment).
 *     The amount of those is the CURRENT fee (the site does not record the amount typed by hand), so it is an estimate and is marked as such;
 *   - "reported": a learner said they paid (fee_reported_at) and the fee is still due: it waits for an administrator to check the money and mark it paid.
 * Filters: period (today | 7 | 30 | 90 | all), type (all | signup | cert), status (all | received | pending | reported | failed).
 */
final class PaymentsReport
{
    public const PERIODS = ['today' => 'Today', '7' => 'Last 7 days', '30' => 'Last 30 days', '90' => 'Last 90 days', 'all' => 'All time'];
    public const TYPES = ['all' => 'Sign-up and certificate fees', 'signup' => 'Sign-up fees', 'cert' => 'Certificate fees'];
    public const STATUSES = ['all' => 'Every status', 'received' => 'Received', 'pending' => 'Started, not finished', 'reported' => 'Reported, waiting for you', 'failed' => 'Failed'];

    /** @return array{period:string,type:string,status:string,login:string} */
    public static function filters(string $period, string $type, string $status, string $login = ''): array
    {
        return [
            'login' => LearnerRegistry::loginMode($login),
            'period' => isset(self::PERIODS[$period]) ? $period : '30',
            'type' => isset(self::TYPES[$type]) ? $type : 'all',
            'status' => isset(self::STATUSES[$status]) ? $status : 'all',
        ];
    }

    private static function since(string $period): ?string
    {
        return match ($period) {
            'today' => date('Y-m-d 00:00:00'),
            'all' => null,
            default => date('Y-m-d H:i:s', strtotime('-' . (int) $period . ' day')),
        };
    }

    /**
     * All events newest first. Each: key, at, type (signup|cert), status (received|pending|reported|failed), source (pesapal|manual|reported), learner_id, learner,
     * item (course title for a certificate), minor, currency, estimated (amount is the current fee), method, reference, mark_url_id (learner id when "mark paid" applies).
     * @param array{period:string,type:string,status:string} $f @return list<array<string,mixed>>
     */
    public static function events(array $f, int $limit = 200): array
    {
        if (!AcademyFees::ready()) {
            return [];
        }
        $since = self::since($f['period']);
        $out = [];
        $signupFee = AcademyFees::configured('signup');
        $certFee = AcademyFees::configured('cert');

        if (Database::tableExists('pesapal_payments')) {
            $w = "p.purpose IN ('academy_signup','academy_cert')";
            $p = [];
            if ($since !== null) {
                $w .= ' AND COALESCE(p.paid_at, p.created_at) >= ?';
                $p[] = $since;
            }
            $rows = Database::all(
                "SELECT p.id, p.purpose, p.ref_id, p.amount_minor, p.currency, p.status, p.pay_method, p.confirmation, p.payer_name, p.merchant_reference, COALESCE(p.paid_at, p.created_at) AS at,
                        l.id AS s_learner_id, l.name AS s_name, c.learner_id AS c_learner_id, c.learner_name AS c_name, c.course_title
                 FROM pesapal_payments p
                 LEFT JOIN academy_learners l ON p.purpose = 'academy_signup' AND l.id = p.ref_id
                 LEFT JOIN academy_certificates c ON p.purpose = 'academy_cert' AND c.id = p.ref_id
                 WHERE $w ORDER BY at DESC LIMIT 500",
                $p
            );
            foreach ($rows as $r) {
                $isCert = $r['purpose'] === 'academy_cert';
                $status = match ((string) $r['status']) {
                    'paid' => 'received',
                    'pending' => 'pending',
                    default => 'failed',
                };
                $out[] = [
                    'key' => 'p' . $r['id'], 'at' => (string) $r['at'], 'type' => $isCert ? 'cert' : 'signup', 'status' => $status, 'source' => 'pesapal',
                    'learner_id' => (int) ($isCert ? ($r['c_learner_id'] ?? 0) : ($r['s_learner_id'] ?? 0)), 'learner' => (string) (($isCert ? $r['c_name'] : $r['s_name']) ?? $r['payer_name']),
                    'item' => $isCert ? (string) ($r['course_title'] ?? '') : '', 'minor' => (int) $r['amount_minor'], 'currency' => (string) $r['currency'], 'estimated' => false,
                    'method' => 'Pesapal' . (trim((string) $r['pay_method']) !== '' ? ' (' . $r['pay_method'] . ')' : ''), 'reference' => (string) ($r['confirmation'] ?: $r['merchant_reference']), 'mark' => 0,
                ];
            }
        }

        // confirmed by hand: sign-up fees
        $w = "l.fee_status = 'paid' AND l.fee_paid_at IS NOT NULL AND NOT EXISTS (SELECT 1 FROM pesapal_payments pp WHERE pp.purpose = 'academy_signup' AND pp.ref_id = l.id AND pp.status = 'paid')";
        $p = [];
        if ($since !== null) {
            $w .= ' AND l.fee_paid_at >= ?';
            $p[] = $since;
        }
        $hasPesapal = Database::tableExists('pesapal_payments');
        if (!$hasPesapal) {
            $w = "l.fee_status = 'paid' AND l.fee_paid_at IS NOT NULL" . ($since !== null ? ' AND l.fee_paid_at >= ?' : '');
        }
        foreach (Database::all("SELECT l.id, l.name, l.fee_paid_at AS at, l.fee_reference FROM academy_learners l WHERE $w ORDER BY l.fee_paid_at DESC LIMIT 500", $p) as $r) {
            $out[] = ['key' => 'ms' . $r['id'], 'at' => (string) $r['at'], 'type' => 'signup', 'status' => 'received', 'source' => 'manual', 'learner_id' => (int) $r['id'], 'learner' => (string) $r['name'], 'item' => '',
                'minor' => (int) ($signupFee['minor'] ?? 0), 'currency' => (string) ($signupFee['currency'] ?? 'UGX'), 'estimated' => true, 'method' => 'Confirmed by the Academy team', 'reference' => (string) ($r['fee_reference'] ?? ''), 'mark' => 0];
        }

        // reported by the learner, waiting for the team to check the money
        $w = "l.fee_status = 'due' AND l.fee_reported_at IS NOT NULL";
        $p = [];
        if ($since !== null) {
            $w .= ' AND l.fee_reported_at >= ?';
            $p[] = $since;
        }
        foreach (Database::all("SELECT l.id, l.name, l.fee_reported_at AS at, l.fee_reference FROM academy_learners l WHERE $w ORDER BY l.fee_reported_at DESC LIMIT 200", $p) as $r) {
            $out[] = ['key' => 'rp' . $r['id'], 'at' => (string) $r['at'], 'type' => 'signup', 'status' => 'reported', 'source' => 'reported', 'learner_id' => (int) $r['id'], 'learner' => (string) $r['name'], 'item' => '',
                'minor' => (int) ($signupFee['minor'] ?? 0), 'currency' => (string) ($signupFee['currency'] ?? 'UGX'), 'estimated' => true, 'method' => 'Reported by the learner (mobile money or bank)', 'reference' => (string) ($r['fee_reference'] ?? ''), 'mark' => (int) $r['id']];
        }

        // confirmed by hand: certificate fees
        $w = "c.fee_status = 'paid' AND c.fee_paid_at IS NOT NULL" . ($hasPesapal ? " AND NOT EXISTS (SELECT 1 FROM pesapal_payments pp WHERE pp.purpose = 'academy_cert' AND pp.ref_id = c.id AND pp.status = 'paid')" : '');
        $p = [];
        if ($since !== null) {
            $w .= ' AND c.fee_paid_at >= ?';
            $p[] = $since;
        }
        foreach (Database::all("SELECT c.id, c.learner_id, c.learner_name, c.course_title, c.fee_paid_at AS at FROM academy_certificates c WHERE $w ORDER BY c.fee_paid_at DESC LIMIT 500", $p) as $r) {
            $out[] = ['key' => 'mc' . $r['id'], 'at' => (string) $r['at'], 'type' => 'cert', 'status' => 'received', 'source' => 'manual', 'learner_id' => (int) $r['learner_id'], 'learner' => (string) $r['learner_name'], 'item' => (string) $r['course_title'],
                'minor' => (int) ($certFee['minor'] ?? 0), 'currency' => (string) ($certFee['currency'] ?? 'UGX'), 'estimated' => true, 'method' => 'Confirmed by the Academy team', 'reference' => '', 'mark' => 0];
        }

        $out = array_values(array_filter($out, static fn(array $e): bool => ($f['type'] === 'all' || $e['type'] === $f['type']) && ($f['status'] === 'all' || $e['status'] === $f['status'])));
        if (($lc = LearnerRegistry::loginSql((string) ($f['login'] ?? ''), 'last_login_at')) !== null) {          // only the learners who have (not) signed in
            $ids = array_flip(array_map('intval', array_column(Database::all('SELECT id FROM academy_learners WHERE ' . $lc), 'id')));
            $out = array_values(array_filter($out, static fn(array $e): bool => isset($ids[(int) $e['learner_id']])));
        }
        usort($out, static fn(array $a, array $b): int => strcmp($b['at'], $a['at']));
        return array_slice($out, 0, max(1, $limit));
    }

    /**
     * Headline numbers for the filtered events plus what is still owed.
     * @param list<array<string,mixed>> $events @return array{received:int,amounts:array<string,int>,estimated:bool,reported:int,pending:int,failed:int,owed_signup:int,owed_signup_display:string,owed_cert:int,owed_cert_display:string,waived:int}
     */
    public static function summary(array $events): array
    {
        $s = ['received' => 0, 'amounts' => [], 'estimated' => false, 'reported' => 0, 'pending' => 0, 'failed' => 0];
        foreach ($events as $e) {
            if ($e['status'] === 'received') {
                $s['received']++;
                $s['amounts'][$e['currency']] = ($s['amounts'][$e['currency']] ?? 0) + (int) $e['minor'];
                $s['estimated'] = $s['estimated'] || $e['estimated'];
            } elseif (isset($s[$e['status']])) {
                $s[$e['status']]++;
            }
        }
        $signupFee = AcademyFees::configured('signup');
        $certFee = AcademyFees::configured('cert');
        $owedS = count(AcademyFees::unpaidLearners());
        $owedC = count(AcademyFees::unpaidCerts());
        $s['owed_signup'] = $owedS;
        $s['owed_signup_display'] = $signupFee !== null ? Money::format($owedS * $signupFee['minor'], $signupFee['currency']) : '';
        $s['owed_cert'] = $owedC;
        $s['owed_cert_display'] = $certFee !== null ? Money::format($owedC * $certFee['minor'], $certFee['currency']) : '';
        $s['waived'] = AcademyFees::ready() ? (int) Database::value("SELECT COUNT(*) FROM academy_learners WHERE fee_status = 'waived'", [], 0) : 0;
        return $s;
    }

    /** Received payments per day for the small chart. @param list<array<string,mixed>> $events @param array{period:string,type:string,status:string} $f @return list<array{day:string,n:int}> */
    public static function daily(array $events, array $f): array
    {
        $days = $f['period'] === 'today' ? 1 : ($f['period'] === 'all' ? 60 : (int) $f['period']);
        $by = [];
        foreach ($events as $e) {
            if ($e['status'] === 'received') {
                $d = substr((string) $e['at'], 0, 10);
                $by[$d] = ($by[$d] ?? 0) + 1;
            }
        }
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime('-' . $i . ' day'));
            $out[] = ['day' => $d, 'n' => $by[$d] ?? 0];
        }
        return $out;
    }

    public static function money(int $minor, string $currency): string
    {
        return Money::format($minor, $currency);
    }
}
