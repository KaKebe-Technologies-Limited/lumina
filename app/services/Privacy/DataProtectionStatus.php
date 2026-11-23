<?php
declare(strict_types=1);

namespace App\Services\Privacy;

use App\Core\Database;

/**
 * What Admin > Data protection shows: the retention reminders (counts of records older than the targets stated in the privacy policy; nothing is deleted
 * automatically) and the owner checklist. The checklist is the ONLY place where "to be confirmed" appears: the public pages never show it.
 */
final class DataProtectionStatus
{
    /** @return list<array{label:string,count:int,target:string,where:string}> */
    public static function retention(): array
    {
        $q = static function (string $sql): int {
            try {
                return (int) Database::value($sql);
            } catch (\Throwable) {
                return 0;
            }
        };
        return [
            ['label' => 'Contact messages older than 2 years', 'target' => 'About 2 years after the last message', 'where' => 'admin/messages',
                'count' => $q("SELECT COUNT(*) FROM contact_messages WHERE kind = 'message' AND created_at < DATE_SUB(NOW(), INTERVAL 2 YEAR)")],
            ['label' => 'Rejected job and volunteer applications older than 12 months', 'target' => '12 months (policy LAF-DP-001)', 'where' => 'admin/applications',
                'count' => $q("SELECT COUNT(*) FROM applications WHERE status = 'rejected' AND created_at < DATE_SUB(NOW(), INTERVAL 12 MONTH)")],
            ['label' => 'Involvement requests older than 2 years', 'target' => 'About 2 years after the last contact', 'where' => 'admin/involvement',
                'count' => $q("SELECT COUNT(*) FROM involvement_requests WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 YEAR)")],
            ['label' => 'Unsubscribed newsletter addresses', 'target' => 'Kept only as an opt-out note', 'where' => 'admin/subscribers',
                'count' => $q("SELECT COUNT(*) FROM subscribers WHERE status = 'unsubscribed'")],
        ];
    }

    /** @return list<array{title:string,detail:string,state:string}> state: ok | todo | warn */
    public static function checklist(): array
    {
        $analytics = trim((string) setting('analytics_id', '')) !== '';
        $reg = trim((string) setting('reg_number', ''));
        return [
            ['title' => 'Wording confirmed by a lawyer', 'state' => 'todo', 'detail' => 'The privacy policy, the rights page and the notes beside forms are a draft framework, not legal advice. To be confirmed by counsel under the Data Protection and Privacy Act, 2019.'],
            ['title' => 'Registration with the Personal Data Protection Office (PDPO)', 'state' => 'todo', 'detail' => 'To be confirmed: whether the foundation is registered as a data controller, and the registration number. Our policy LAF-DP-001 says the number is shown on the privacy notice: add it to the "Registration line" in Pages > Privacy Policy once known. No number has been invented.'],
            ['title' => 'Data Protection Focal Person named', 'state' => 'todo', 'detail' => 'To be confirmed: who holds the role (policy LAF-DP-001, section 7), and that requests reach them. Requests currently go to the e-mail address in Settings.'],
            ['title' => 'Policy LAF-DP-001 approved and effective date set', 'state' => 'todo', 'detail' => 'The PDF says "For Board approval" and "effective date to be confirmed". The website follows it, but the Board must adopt it.'],
            ['title' => 'Retention periods confirmed', 'state' => 'todo', 'detail' => 'Member records (membership + 3 years), unsuccessful candidates (12 months), volunteers (3 years) and financial records (7 years) come from the policy proposals. Others (contact messages, events, Academy, 2 years) are our suggested aims: confirm them. Nothing is deleted automatically yet: use the reminders above.'],
            ['title' => 'Registration number is consistent', 'state' => $reg === '' ? 'warn' : 'todo', 'detail' => 'Settings show "' . ($reg !== '' ? $reg : 'nothing') . '" while the policy PDF prints "GSD/R 1234". To be confirmed: which is right.'],
            ['title' => 'Google Analytics and cookies', 'state' => $analytics ? 'warn' : 'ok', 'detail' => $analytics
                ? 'A Google Analytics ID is set, so analytics cookies are used without asking visitors. Either clear the ID in Settings, or decide how to ask visitors first. The privacy page already mentions it.'
                : 'No Google Analytics ID is set, so the site sets only strictly necessary cookies (plus a language cookie if a visitor picks a language).'],
            ['title' => 'Third-party services (processors) checked', 'state' => 'todo', 'detail' => 'To be confirmed: agreements and where data is stored for Hostinger, the e-mail provider, Pesapal, Flutterwave, PayPal, Daraza, WhatsApp, Google (sign-in, fonts, maps, translate), the AI provider and ipwho.is (policy Annex G, section 12.3).'],
            ['title' => 'Deleting a person removes their files', 'state' => 'warn', 'detail' => 'Deleting a learner, volunteer or ambassador record may leave a portrait, CV or proof file on the server. Check private storage when you fulfil a deletion request (see docs/DATA-INVENTORY.md, gaps).'],
            ['title' => 'Breach plan known to staff', 'state' => 'todo', 'detail' => 'To be confirmed: who reports a breach to the PDPO and within what time (the policy says "within the time set by law").'],
        ];
    }
}
