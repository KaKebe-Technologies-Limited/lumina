<?php
declare(strict_types=1);

namespace App\Controllers\Admin\Members;

use App\Services\Membership\MembershipService;

/**
 * Column layout of the members CSV export (list export and the "export selected" bulk action). The file is written by
 * App\Core\CsvExport (formula-injection safe, UTF-8 with BOM). OWNER: ADMIN agent (membership).
 *
 * PRIVACY: the file contains personal data. Left out on purpose: IP address, browser, internal staff notes. The national ID / passport
 * number is only a column while the setting "Ask for a national ID or passport number" is on.
 */
final class MemberCsv
{
    /** @return list<string> */
    public static function header(): array
    {
        $h = ['Application reference', 'Membership number', 'Status', 'Status reason', 'Membership type', 'Applied on', 'Approved on', 'Start date', 'Expiry date',
            'Fee', 'Joining fee', 'Currency', 'Billing period', 'Payment status', 'Paid', 'Balance',
            'Full name', 'Gender', 'Date of birth', 'Age', 'Nationality'];
        if (MembershipService::collectsId()) {
            $h[] = 'ID / passport number';
        }
        return array_merge($h, [
            'Email', 'Phone', 'WhatsApp', 'Country', 'District / region', 'Sub-county / city', 'Village / address',
            'Occupation', 'Education', 'Organisation', 'Areas of interest', 'Skills and experience', 'Availability', 'How they heard about us', 'Why they want to join',
            'Guardian name', 'Guardian phone', 'Guardian relationship',
            'Emergency contact', 'Emergency phone', 'Emergency relationship',
            'Preferred payment method', 'Payment reference',
            'Newsletter consent', 'Terms accepted on', 'Privacy notice accepted on',
        ]);
    }

    /**
     * One CSV row for a member row selected with ListQuery (pay_balance, pay_paid and pay_status are present).
     * @param array<string,mixed> $m
     * @return list<mixed>
     */
    public static function row(array $m): array
    {
        $MS = MembershipService::class;
        $money = static fn(mixed $v): string => $v === null || $v === '' ? '' : number_format((float) $v, 2, '.', '');
        $occupation = $MS::optionLabel('occupation', (string) $m['occupation_status']);
        if ((string) $m['occupation_status'] === $MS::OTHER_KEY && !empty($m['occupation_other'])) {
            $occupation .= ': ' . $m['occupation_other'];
        }
        $heard = $MS::optionLabel('heard', (string) $m['heard_about']);
        if ((string) $m['heard_about'] === $MS::OTHER_KEY && !empty($m['heard_about_other'])) {
            $heard .= ': ' . $m['heard_about_other'];
        }
        $row = [
            $m['application_ref'], $m['membership_number'], $MS::STATUS_LABELS[$m['status']] ?? $m['status'], $m['status_reason'], $m['type_name'],
            $m['created_at'], $m['approved_at'], $m['start_date'], $m['expiry_date'],
            $money($m['fee_amount']), $money($m['joining_fee']), $m['fee_currency'], $MS::PERIOD_LABELS_ADMIN[$m['fee_period']] ?? $m['fee_period'],
            $MS::PAYMENT_SUMMARY_LABELS[$m['pay_status'] ?? ''] ?? '', $money($m['pay_paid'] ?? null), $money($m['pay_balance'] ?? null),
            $m['full_name'], $MS::optionLabel('gender', (string) $m['gender']), $m['date_of_birth'], $MS::ageOn((string) $m['date_of_birth']), $m['nationality'],
        ];
        if ($MS::collectsId()) {
            $row[] = $m['id_number'];
        }
        return array_merge($row, [
            $m['email'], $m['phone'], $m['whatsapp'], $m['country'], $m['region'], $m['city'], $m['address'],
            $occupation, $MS::optionLabel('education', (string) $m['education_level']), $m['organisation'],
            implode('; ', $MS::interestLabels($m['interests'] ?? null)), $m['skills'], $MS::optionLabel('availability', (string) $m['availability']), $heard, $m['motivation'],
            $m['guardian_name'], $m['guardian_phone'], $m['guardian_relationship'],
            $m['emergency_name'], $m['emergency_phone'], $m['emergency_relationship'],
            $MS::optionLabel('payment_methods', (string) $m['payment_method']), $m['payment_reference'],
            !empty($m['consent_updates']) ? 'Yes' : 'No', $m['consent_terms_at'], $m['consent_privacy_at'],
        ]);
    }
}
