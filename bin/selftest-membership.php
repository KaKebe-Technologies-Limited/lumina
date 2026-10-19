<?php
/**
 * Membership checks, included by bin/selftest.php (shares its check() helper and $fail counter).
 * Exercises App\Services\Membership\*: option lists, expiry math per billing period, age / minor rule, status transitions, fee snapshot,
 * reference + membership number + receipt uniqueness, application validation (minor, duplicate, closed, capacity, ID data minimisation),
 * payments + balance (PHP and SQL agree), renewal, expiry sync, type CRUD guards, confirmation tokens, e-mail templates, navigation.
 * Everything it creates is removed again (members e-mail @selftest.invalid, types zz-selftest-*).
 */
declare(strict_types=1);

use App\Core\Database;
use App\Core\Nav;
use App\Core\PageRegistry;
use App\Core\Settings;
use App\Core\SitePages;
use App\Models\Member;
use App\Models\MemberPayment;
use App\Models\MembershipType;
use App\Services\Membership\MembershipApplication;
use App\Services\Membership\MembershipMail;
use App\Services\Membership\MembershipService as MS;

echo "Membership\n";

$auditBefore = (int) Database::value('SELECT COALESCE(MAX(id), 0) FROM audit_log', [], 0);
$saved = [];
foreach (['membership_open', 'membership_collect_id'] as $k) {
    $saved[$k] = [Settings::isCustomised($k), (string) Settings::get($k, '')];
}
$erasedIds = [];     // ids of selftest members whose e-mail was erased (they no longer match @selftest.invalid)
$cleanup = static function () use (&$saved, &$erasedIds, $auditBefore): void {
    $extra = $erasedIds === [] ? '0' : implode(',', array_map('intval', $erasedIds));
    Database::execute("DELETE FROM member_payments WHERE member_id IN (SELECT id FROM members WHERE email LIKE '%@selftest.invalid' OR id IN ($extra))");
    Database::execute("DELETE FROM members WHERE email LIKE '%@selftest.invalid' OR id IN ($extra)");
    Database::execute("DELETE FROM subscribers WHERE email LIKE '%@selftest.invalid'");
    Database::execute("DELETE FROM membership_types WHERE slug LIKE 'zz-selftest-%' OR name LIKE 'Selftest %'");
    Database::execute("DELETE FROM audit_log WHERE id > ? AND user_name = 'system' AND (action LIKE 'member.%' OR action LIKE 'membership_type.%')", [$auditBefore]);
    foreach ($saved as $k => [$custom, $val]) {
        $custom ? Settings::set($k, $val) : Settings::reset($k);
    }
};
$cleanup();      // leftovers of an aborted earlier run
Settings::set('membership_open', '1');      // registration is OFF by default; the checks below need it open (restored by $cleanup)

try {
    // ---- pages, navigation, settings, option lists
    check('registry pages membership + membership-terms', PageRegistry::has('membership') && PageRegistry::has('membership-terms'));
    SitePages::flush();
    Nav::flush();
    $top = array_column(Nav::header(), 'label');
    $inv = null;
    foreach (Nav::header() as $item) {
        if (($item['label'] ?? '') === 'Involvement') {
            $inv = $item;
        }
    }
    $kids = $inv ? array_column((array) ($inv['children'] ?? []), 'label') : [];
    check('Membership sits inside the Involvement dropdown in the header', in_array('Membership', $kids, true) && !in_array('Membership', $top, true), json_encode([$top, $kids]));
    check('Membership is in the footer menu', in_array('Membership', array_column(Nav::footer(), 'label'), true));
    $schemaGroups = require dirname(__DIR__) . '/app/config/settings_schema.php';
    check('settings defaults (registration is closed until the owner opens it)', MS::prefix() === 'LAF' && MS::defaultCurrency() === 'UGX' && !MS::collectsId()
        && (string) ($schemaGroups['groups']['membership']['fields']['membership_open']['default'] ?? '') === '0');
    check('option lists have keys and labels', count(MS::options('occupation')) >= 5 && isset(MS::options('payment_methods')['mobile_money'])
        && isset(MS::options('gender')['female']) && isset(MS::options('interests')['volunteering']));
    check('interest areas come from the program pillars', count(array_filter(array_keys(MS::options('interests')), static fn(string $k): bool => str_starts_with($k, 'program:'))) >= 3);

    // ---- money, periods, dates, age
    check('money formatting', MS::formatMoney(50000, 'UGX') === 'UGX 50,000' && MS::formatMoney('1500.5', 'USD') === 'USD 1,500.50');
    check('fee label: NULL = to be announced, 0 = free, amount + period', MS::feeLabel(null, 'UGX') === 'Fee to be announced' && MS::feeLabel('0.00', 'UGX') === 'Free'
        && MS::feeLabel('50000.00', 'UGX', 'annual') === 'UGX 50,000 per year');
    check('parseMoney', MS::parseMoney('50,000') === '50000.00' && MS::parseMoney('1 500.5') === '1500.50' && MS::parseMoney('abc') === null && MS::parseMoney('-5') === null);
    check('expiry: annual +1 year', MS::computeExpiry('2026-03-15', 'annual') === '2027-03-15');
    check('expiry: monthly +1 month, month ends clamped', MS::computeExpiry('2026-01-31', 'monthly') === '2026-02-28' && MS::computeExpiry('2026-04-30', 'monthly') === '2026-05-30');
    check('expiry: quarterly +3 months', MS::computeExpiry('2026-11-30', 'quarterly') === '2027-02-28');
    check('expiry: leap day + 1 year', MS::computeExpiry('2024-02-29', 'annual') === '2025-02-28');
    check('expiry: one_time and lifetime never expire', MS::computeExpiry('2026-03-15', 'one_time') === null && MS::computeExpiry('2026-03-15', 'lifetime') === null);
    check('age + minor rule (18th birthday)', MS::ageOn('2008-06-15', '2026-06-14') === 17 && MS::ageOn('2008-06-15', '2026-06-15') === 18
        && MS::isMinor('2008-06-15', '2026-06-14') && !MS::isMinor('2008-06-15', '2026-06-15') && MS::ageOn('2999-01-01') === -1 && MS::ageOn('nonsense') === -1);

    // ---- status workflow map
    check('transitions: pending -> approved / rejected / cancelled only', MS::canTransition('pending', 'approved') && MS::canTransition('pending', 'rejected')
        && !MS::canTransition('pending', 'suspended') && !MS::canTransition('pending', 'expired'));
    check('transitions: expired only returns through renew()', !MS::canTransition('expired', 'approved') && MS::canTransition('suspended', 'approved')
        && MS::canTransition('rejected', 'pending') && !MS::canTransition('approved', 'pending'));

    // ---- membership types (the fee configuration)
    $bad = MS::saveType(null, ['name' => '', 'period' => 'yearly', 'fee_amount' => 'abc']);
    check('saveType rejects bad input', !$bad['ok'] && isset($bad['errors']['name'], $bad['errors']['period'], $bad['errors']['fee_amount']), json_encode($bad['errors']));
    $r1 = MS::saveType(null, ['name' => 'Selftest Annual', 'slug' => 'zz-selftest-annual', 'fee_amount' => '50,000', 'joining_fee' => '10000', 'currency' => 'ugx', 'period' => 'annual', 'is_active' => '1', 'sort_order' => 9001]);
    $r2 = MS::saveType(null, ['name' => 'Selftest Free', 'slug' => 'zz-selftest-free', 'fee_amount' => '0', 'period' => 'lifetime', 'is_active' => '1', 'sort_order' => 9002]);
    $r3 = MS::saveType(null, ['name' => 'Selftest Capped', 'slug' => 'zz-selftest-capped', 'fee_amount' => '', 'period' => 'monthly', 'is_active' => '1', 'max_members' => '1', 'sort_order' => 9003]);
    check('saveType stores fee 50000.00, joining 10000.00, currency upper-case', $r1['ok'] && $r1['type']['fee_amount'] === '50000.00' && $r1['type']['joining_fee'] === '10000.00' && $r1['type']['currency'] === 'UGX');
    check('saveType: 0 = free, empty = to be announced', $r2['ok'] && (float) $r2['type']['fee_amount'] === 0.0 && $r3['ok'] && $r3['type']['fee_amount'] === null);
    $dup = MS::saveType(null, ['name' => 'Selftest Annual', 'slug' => 'zz-selftest-annual', 'period' => 'annual']);
    check('saveType keeps slugs unique', $dup['ok'] && $dup['type']['slug'] === 'zz-selftest-annual-2');
    MS::deleteType((int) $dup['id']);
    [$t1, $t2, $t3] = [(int) $r1['id'], (int) $r2['id'], (int) $r3['id']];

    // ---- applications
    $base = static fn(int $type, string $email, array $over = []): array => $over + [
        'type_id' => $type, 'full_name' => 'Selftest Person', 'gender' => 'female', 'date_of_birth' => '1990-05-20', 'nationality' => 'Ugandan',
        'email' => $email, 'phone' => '+256700000001', 'country' => 'Uganda', 'region' => 'Gulu', 'city' => 'Laroo',
        'occupation_status' => 'employed', 'education_level' => 'diploma', 'availability' => 'weekends', 'heard_about' => 'website',
        'motivation' => 'I would like to volunteer and support young people.', 'emergency_name' => 'Em Contact', 'emergency_phone' => '+256700000002',
        'emergency_relationship' => 'Sister', 'payment_method' => 'mobile_money', 'consent_terms' => '1', 'consent_privacy' => '1', 'declaration' => '1',
    ];
    $meta = ['ip' => '203.0.113.9', 'user_agent' => 'selftest'];
    $empty = MembershipApplication::process([], $meta);
    check('empty application: errors on every required field', !$empty['ok'] && isset($empty['errors']['full_name'], $empty['errors']['email'], $empty['errors']['type_id'],
        $empty['errors']['date_of_birth'], $empty['errors']['consent_terms'], $empty['errors']['declaration'], $empty['errors']['motivation']), json_encode(array_keys($empty['errors'] ?? [])));
    $shortWhy = MembershipApplication::process($base($t1, 'short@selftest.invalid', ['motivation' => 'too short']), $meta);
    check('motivation has a minimum length', !$shortWhy['ok'] && isset($shortWhy['errors']['motivation']));
    $badOpt = MembershipApplication::process($base($t1, 'opt@selftest.invalid', ['occupation_status' => 'astronaut', 'interests' => ['nonsense'], 'date_of_birth' => '2999-01-01']), $meta);
    check('unknown option keys / future date of birth are rejected', !$badOpt['ok'] && isset($badOpt['errors']['occupation_status'], $badOpt['errors']['interests'], $badOpt['errors']['date_of_birth']));
    $other = MembershipApplication::process($base($t1, 'other@selftest.invalid', ['occupation_status' => 'other', 'heard_about' => 'other']), $meta);
    check('"other" answers need their free text', !$other['ok'] && isset($other['errors']['occupation_other'], $other['errors']['heard_about_other']));

    $ok = MembershipApplication::process($base($t1, 'Adult@Selftest.invalid', ['consent_updates' => '1', 'interests' => ['volunteering', 'volunteering'], 'id_number' => 'CM1234567', 'skills' => "Teaching\r\nFirst aid"]), $meta);
    check('valid application is created as pending', $ok['ok'] && empty($ok['duplicate']) && $ok['member']['status'] === 'pending', json_encode($ok['errors'] ?? []));
    $m1 = (array) ($ok['member'] ?? []);
    check('application reference format + e-mail lower-cased', preg_match('/^LAF-APP-\d{4}-\d{5}$/', (string) ($m1['application_ref'] ?? '')) === 1 && ($m1['email'] ?? '') === 'adult@selftest.invalid');
    check('fee snapshot copied from the type', ($m1['type_name'] ?? '') === 'Selftest Annual' && $m1['fee_amount'] === '50000.00' && $m1['joining_fee'] === '10000.00'
        && $m1['fee_currency'] === 'UGX' && $m1['fee_period'] === 'annual');
    check('consents recorded with a time and the IP', !empty($m1['consent_terms_at']) && !empty($m1['consent_privacy_at']) && !empty($m1['declaration_at']) && $m1['consent_ip'] === '203.0.113.9');
    check('ID number is NOT stored while "collect ID" is off (data minimisation)', $m1['id_number'] === null);
    check('interests stored once each as JSON', MS::decodeInterests($m1['interests'] ?? '') === ['volunteering']);
    check('newsletter opt-in creates a subscriber only when ticked', Database::value("SELECT COUNT(*) FROM subscribers WHERE email = 'adult@selftest.invalid'", [], 0) == 1);

    $again = MembershipApplication::process($base($t1, 'ADULT@selftest.invalid'), $meta);
    check('same e-mail with an open application: generic duplicate, nothing stored', $again['ok'] && !empty($again['duplicate']) && Member::count(['email' => 'adult@selftest.invalid']) === 1);

    $minorNo = MembershipApplication::process($base($t1, 'minor@selftest.invalid', ['date_of_birth' => date('Y-m-d', strtotime('-15 years'))]), $meta);
    check('under 18 needs guardian name, phone, relationship and consent', !$minorNo['ok'] && isset($minorNo['errors']['guardian_name'], $minorNo['errors']['guardian_phone'], $minorNo['errors']['guardian_relationship'], $minorNo['errors']['consent_guardian']));
    $minorYes = MembershipApplication::process($base($t2, 'minor@selftest.invalid', ['date_of_birth' => date('Y-m-d', strtotime('-15 years')), 'guardian_name' => 'Parent One',
        'guardian_phone' => '+256700000003', 'guardian_relationship' => 'Mother', 'consent_guardian' => '1']), $meta);
    $m2 = (array) ($minorYes['member'] ?? []);
    check('minor with guardian details is accepted', $minorYes['ok'] && ($m2['guardian_name'] ?? '') === 'Parent One' && !empty($m2['consent_guardian_at']), json_encode($minorYes['errors'] ?? []));

    $capA = MembershipApplication::process($base($t3, 'cap1@selftest.invalid'), $meta);
    $capB = MembershipApplication::process($base($t3, 'cap2@selftest.invalid'), $meta);
    check('type with max_members: the place is taken, the next applicant is refused', $capA['ok'] && !$capB['ok'] && isset($capB['errors']['type_id']));
    $m3 = (array) ($capA['member'] ?? []);
    check('fee "to be announced" is snapshotted as NULL', $m3['fee_amount'] === null && $m3['joining_fee'] === null);

    Settings::set('membership_open', '0');
    $closed = MembershipApplication::process($base($t1, 'closed@selftest.invalid'), $meta);
    check('closed membership refuses applications', !$closed['ok'] && !empty($closed['closed']) && Member::count(['email' => 'closed@selftest.invalid']) === 0);
    Settings::set('membership_open', '1');

    Settings::set('membership_collect_id', '1');
    $withId = MembershipApplication::process($base($t2, 'withid@selftest.invalid', ['id_number' => 'CM7654321']), $meta);
    check('ID number is stored when the admin turns "collect ID" on', $withId['ok'] && ($withId['member']['id_number'] ?? '') === 'CM7654321');
    Settings::set('membership_collect_id', '0');

    // ---- references and numbers
    $refs = [];
    for ($i = 0; $i < 300; $i++) {
        $refs[MS::generateApplicationRef()] = true;
    }
    check('application references are well-formed and (practically) unique', count($refs) >= 295 && !isset($refs[$m1['application_ref']]));

    // ---- status workflow on real rows
    $noReason = MS::changeStatus((int) $m1['id'], 'rejected');
    check('rejecting needs a reason', !$noReason['ok'] && $noReason['error'] === 'reason_required');
    $illegal = MS::changeStatus((int) $m1['id'], 'suspended', ['reason' => 'x']);
    check('illegal transition is refused', !$illegal['ok'] && $illegal['error'] === 'not_allowed');
    $appr = MS::changeStatus((int) $m1['id'], 'approved', ['user_id' => null]);
    $a1 = (array) ($appr['member'] ?? []);
    check('approval assigns the membership number LAF-YYYY-NNNN', $appr['ok'] && preg_match('/^LAF-' . date('Y') . '-\d{4}$/', (string) ($a1['membership_number'] ?? '')) === 1, json_encode($appr));
    check('approval sets start date today and expiry +1 year (annual)', ($a1['start_date'] ?? '') === date('Y-m-d') && ($a1['expiry_date'] ?? '') === MS::computeExpiry(date('Y-m-d'), 'annual'));
    $appr2 = MS::changeStatus((int) $m2['id'], 'approved');
    $a2 = (array) ($appr2['member'] ?? []);
    check('membership numbers are unique and increase', $appr2['ok'] && $a2['membership_number'] !== $a1['membership_number'] && strcmp($a2['membership_number'], $a1['membership_number']) > 0);
    check('lifetime type: approved with no expiry date', $a2['expiry_date'] === null);
    check('assigning a number twice returns the same number', MS::assignMembershipNumber((int) $m1['id']) === $a1['membership_number']);
    $full = MS::changeStatus((int) $m3['id'], 'approved');
    check('a type at capacity can still approve its own pending member', $full['ok']);
    $m4row = MembershipApplication::process($base($t3, 'cap3@selftest.invalid'), $meta);
    check('...but admits nobody else while full', !$m4row['ok']);
    $rej = MS::changeStatus((int) Database::value("SELECT id FROM members WHERE email = 'withid@selftest.invalid'", [], 0), 'rejected', ['reason' => 'Incomplete']);
    check('rejected with a reason; reopening is allowed', $rej['ok'] && MS::changeStatus((int) $rej['member']['id'], 'pending')['ok']);

    // ---- payments and balance
    $sum = MS::paymentSummary((int) $m1['id']);
    check('summary: charges = joining + first fee, nothing paid yet', $sum['charges'] === 60000.0 && $sum['paid'] === 0.0 && $sum['balance'] === 60000.0 && $sum['status'] === 'unpaid');
    $badPay = MS::recordPayment((int) $m1['id'], ['amount' => 'x', 'status' => 'received']);
    check('recordPayment validates the amount', !$badPay['ok'] && isset($badPay['errors']['amount']));
    $p1 = MS::recordPayment((int) $m1['id'], ['amount' => '20,000', 'status' => 'received', 'method' => 'mobile_money', 'reference' => 'TX1', 'purpose' => 'joining']);
    check('received payment gets a receipt number LAF-RCT-YYYY-NNNN', $p1['ok'] && preg_match('/^LAF-RCT-' . date('Y') . '-\d{4}$/', (string) ($p1['payment']['receipt_number'] ?? '')) === 1, json_encode($p1['errors'] ?? []));
    $p2 = MS::recordPayment((int) $m1['id'], ['amount' => '30000', 'status' => 'received', 'method' => 'bank_transfer']);
    check('receipt numbers are unique', $p2['ok'] && $p2['payment']['receipt_number'] !== $p1['payment']['receipt_number']);
    $sum = MS::paymentSummary((int) $m1['id']);
    check('partial payment: balance 10000, status partial', $sum['paid'] === 50000.0 && $sum['balance'] === 10000.0 && $sum['status'] === 'partial');
    $sql = MS::paymentSql('m');
    $row = Database::one('SELECT ' . $sql['status'] . ' AS s, ' . $sql['balance'] . ' AS b, ' . $sql['paid'] . ' AS p FROM members m WHERE m.id = ?', [(int) $m1['id']]);
    check('SQL balance/status agree with PHP', $row !== null && $row['s'] === 'partial' && (float) $row['b'] === 10000.0 && (float) $row['p'] === 50000.0, json_encode($row));
    $p3 = MS::recordPayment((int) $m1['id'], ['amount' => '10000', 'status' => 'waived', 'notes' => 'Hardship']);
    $sum = MS::paymentSummary((int) $m1['id']);
    check('waived amount clears the balance', $p3['ok'] && $sum['balance'] === 0.0 && $sum['status'] === 'paid' && empty($p3['payment']['receipt_number']));
    $del = MS::deletePayment((int) $p1['payment']['id']);
    check('a payment with a receipt cannot be deleted', !$del['ok'] && MemberPayment::exists(['id' => $p1['payment']['id']]));
    check('receipt data only for received payments', MS::receiptData((int) $p1['payment']['id']) !== null && MS::receiptData((int) $p3['payment']['id']) === null);
    $free = MS::paymentSummary((int) $m2['id']);
    $tbd = MS::paymentSummary((int) $m3['id']);
    check('free type = "free", unset fee = "tbd"', $free['status'] === 'free' && $tbd['status'] === 'tbd');
    $upd = MS::updatePayment((int) $p2['payment']['id'], ['amount' => '30000', 'status' => 'pending']);
    check('payment can go back to pending and keeps its receipt number', $upd['ok'] && $upd['payment']['receipt_number'] === $p2['payment']['receipt_number']);

    // ---- staff edit of a member
    $edit = MembershipApplication::update((int) $m1['id'], $base($t1, 'adult@selftest.invalid', ['phone' => '+256700000099', 'internal_notes' => 'Called on Monday']));
    $after = (array) Member::find((int) $m1['id']);
    check('staff edit changes details + notes, keeps the fee snapshot and the stored ID number', $edit['ok'] && in_array('phone', $edit['changed'], true) && $after['phone'] === '+256700000099'
        && $after['internal_notes'] === 'Called on Monday' && $after['fee_amount'] === '50000.00', json_encode($edit['errors'] ?? $edit['changed']));
    $editBad = MembershipApplication::update((int) $m1['id'], $base($t1, 'not-an-email', ['phone' => '']));
    check('staff edit validates like the public form', !$editBad['ok'] && isset($editBad['errors']['email'], $editBad['errors']['phone']));
    $editFee = MembershipApplication::update((int) $m3['id'], $base($t3, 'cap1@selftest.invalid', ['fee_amount' => '20,000', 'fee_currency' => 'usd']), null, true);
    $editNoFee = MembershipApplication::update((int) $m3['id'], $base($t3, 'cap1@selftest.invalid'), null, true);
    $m3b = (array) Member::find((int) $m3['id']);
    check('a fee "to be announced" can be set on a member; saving without the field leaves it alone', $editFee['ok'] && $editNoFee['ok'] && $m3b['fee_amount'] === '20000.00' && $m3b['fee_currency'] === 'USD');

    // ---- renewal and expiry
    $ren = MS::renew((int) $m1['id'], ['user_id' => null]);
    $sum = MS::paymentSummary((int) $m1['id']);
    check('renewal extends the term by a year from the current expiry and adds a pending renewal charge',
        $ren['ok'] && $ren['member']['expiry_date'] === MS::computeExpiry((string) $a1['expiry_date'], 'annual') && ($ren['payment']['purpose'] ?? '') === 'renewal' && $sum['renewal_due'] === 50000.0 && $sum['balance'] > 0.0, json_encode($ren));
    $row = Database::one('SELECT ' . $sql['status'] . ' AS s, ' . $sql['balance'] . ' AS b FROM members m WHERE m.id = ?', [(int) $m1['id']]);
    check('SQL still agrees after the renewal', $row !== null && (float) $row['b'] === $sum['balance'] && $row['s'] === $sum['status'], json_encode($row));
    check('lifetime membership cannot be renewed', !MS::renew((int) $m2['id'])['ok']);
    Database::execute("UPDATE members SET expiry_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY) WHERE id = ?", [(int) $m1['id']]);
    check('syncExpired marks lapsed approved members as expired', MS::syncExpired() >= 1 && Member::find((int) $m1['id'])['status'] === 'expired');
    check('expired members come back only through renew()', !MS::changeStatus((int) $m1['id'], 'approved')['ok'] && MS::renew((int) $m1['id'])['ok'] && Member::find((int) $m1['id'])['status'] === 'approved');

    // ---- type guards
    $delT = MS::deleteType($t1);
    check('a type with members cannot be deleted', !$delT['ok'] && MembershipType::exists(['id' => $t1]));
    $toggle = MS::saveType($t1, ['name' => 'Selftest Annual', 'fee_amount' => '75000', 'period' => 'annual', 'is_active' => '1']);
    $mFee = Member::find((int) $m1['id']);
    check('changing a type fee does not rewrite existing members', $toggle['ok'] && $toggle['type']['fee_amount'] === '75000.00' && $mFee['fee_amount'] === '50000.00');
    check('editing a type without a slug keeps its slug', $toggle['ok'] && $toggle['type']['slug'] === 'zz-selftest-annual');

    // ---- confirmation link, e-mail templates
    $path = MS::confirmationPath($m1['application_ref']);
    parse_str((string) parse_url($path, PHP_URL_QUERY), $q);
    check('confirmation token verifies, a tampered one does not', MS::verifyConfirmationToken($m1['application_ref'], (string) ($q['t'] ?? ''))
        && !MS::verifyConfirmationToken($m1['application_ref'], preg_replace('/.$/', 'x', (string) ($q['t'] ?? '')))
        && !MS::verifyConfirmationToken('LAF-APP-2026-00001', (string) ($q['t'] ?? '')) && !MS::verifyConfirmationToken($m1['application_ref'], ''));
    $sign = new ReflectionMethod(MS::class, 'sign');
    $sign->setAccessible(true);
    $past = time() - 10;
    check('an expired confirmation token is refused', !MS::verifyConfirmationToken($m1['application_ref'], $past . '.' . $sign->invoke(null, $m1['application_ref'], $past)));
    $evil = ['id' => 1, 'full_name' => "Evil http://phish.example/x\r\nBcc: a@b.c", 'application_ref' => 'LAF-APP-2026-12345', 'type_name' => 'T', 'fee_amount' => null,
        'joining_fee' => null, 'fee_currency' => 'UGX', 'fee_period' => 'annual', 'email' => 'e@selftest.invalid'];
    [$subj, $body] = MembershipMail::render('ack', $evil);
    check('e-mail template: placeholders filled, link/line break in the name neutralised', !preg_match('/\{[a-z_]+\}/', $subj . $body) && !str_contains($body, 'phish') && !str_contains($subj, "\n")
        && str_contains($body, 'LAF-APP-2026-12345'), $body);
    [$subj, $body] = MembershipMail::render('approved', (array) Member::find((int) $m1['id']));
    check('approved e-mail carries the membership number', str_contains($subj . $body, (string) $a1['membership_number']));

    // ================================================================ review fixes (docs/MEMBERSHIP.md section 13)

    // ---- editable choice lists never drop a row silently
    $rto = new ReflectionMethod(MS::class, 'rowsToOptions');
    $rto->setAccessible(true);
    $opts = $rto->invoke(null, [['key' => 'weekdays', 'label' => 'Weekdays'], ['key' => '', 'label' => 'Part time'], ['key' => 'Part Time', 'label' => 'Spaced'],
        ['key' => 'weekdays', 'label' => 'Again'], ['key' => 'zz', 'label' => '']]);
    check('choice list: a row without a code gets one from its label; clashes get a suffix; a row without label is ignored',
        count($opts) === 4 && ($opts['weekdays'] ?? '') === 'Weekdays' && ($opts['part_time'] ?? '') === 'Part time' && ($opts['part_time_2'] ?? '') === 'Spaced'
        && ($opts['weekdays_2'] ?? '') === 'Again', json_encode($opts));

    // ---- cleaned before validated; arrays and strange characters
    $ctl = MembershipApplication::process($base($t2, 'ctl@selftest.invalid', ['full_name' => "\x01\x01", 'country' => "\xC2\xA0\xC2\xA0", 'nationality' => "\xE2\x80\x8B\xE2\x80\x8B"]), $meta);
    check('required text made of control, non-breaking or zero-width characters counts as empty', !$ctl['ok'] && isset($ctl['errors']['full_name'], $ctl['errors']['country'], $ctl['errors']['nationality'])
        && Member::count(['email' => 'ctl@selftest.invalid']) === 0, json_encode($ctl['errors'] ?? []));
    $arr = MembershipApplication::process($base($t2, 'arr@selftest.invalid', ['date_of_birth' => ['x'], 'occupation_status' => ['x'], 'heard_about' => ['x'], 'type_id' => ['1']]), $meta);
    check('arrays posted for single-value fields are refused cleanly (no PHP warning)', !$arr['ok'] && isset($arr['errors']['date_of_birth'], $arr['errors']['occupation_status'], $arr['errors']['heard_about']));
    $evilMail = MembershipApplication::process($base($t2, 'a?bcc=evil@selftest.invalid'), $meta);
    check('an e-mail address that could carry mailto parameters is refused', !$evilMail['ok'] && isset($evilMail['errors']['email']));
    $mt = \App\Services\Inbox\Support::mailto('a?bcc=evil@selftest.invalid');
    check('mailto: percent-encodes ? & = in the address', $mt === '' || (!str_contains($mt, '?bcc') && str_contains($mt, '%3Fbcc')), $mt);
    check('consent versions (fingerprints of the texts) are stored with the application', preg_match('/^[a-f0-9]{12}$/', (string) ($m1['consent_terms_ver'] ?? '')) === 1
        && preg_match('/^[a-f0-9]{12}$/', (string) ($m1['consent_privacy_ver'] ?? '')) === 1 && $m1['consent_terms_ver'] === MS::consentVersions()['terms']);

    // ---- a staff edit never creates a guardian consent
    $aid = (int) $m1['id'];
    $asMinor = MembershipApplication::update($aid, $base($t1, 'adult@selftest.invalid', ['date_of_birth' => date('Y-m-d', strtotime('-10 years')), 'guardian_name' => 'Guard',
        'guardian_phone' => '+256700000007', 'guardian_relationship' => 'Aunt']));
    $row = (array) Member::find($aid);
    check('staff edit to a minor date of birth leaves the guardian consent time empty', $asMinor['ok'] && $row['consent_guardian_at'] === null && $row['guardian_name'] === 'Guard', json_encode($asMinor['errors'] ?? []));
    Database::execute('UPDATE members SET consent_guardian_at = NOW() WHERE id = ?', [$aid]);
    MembershipApplication::update($aid, $base($t1, 'adult@selftest.invalid', ['date_of_birth' => '1990-05-20']));
    $row = (array) Member::find($aid);
    check('...and back to an adult clears the guardian and the stale consent time', $row['consent_guardian_at'] === null && $row['guardian_name'] === null);

    // ---- the audit trail keeps money before and after
    $feeAudit = (string) Database::value("SELECT details FROM audit_log WHERE entity_type = 'member' AND entity_id = ? AND action = 'member.update' ORDER BY id DESC LIMIT 1", [(int) $m3['id']], '');
    check('staff fee edit: old and new fee values are in the audit entry', str_contains($feeAudit, 'was_fee_amount') && str_contains($feeAudit, 'now_fee_amount'), $feeAudit);
    $upd = MS::updatePayment((int) $p1['payment']['id'], ['amount' => '19000', 'status' => 'received', 'purpose' => 'joining', 'method' => 'mobile_money']);
    $payAudit = (string) Database::value("SELECT details FROM audit_log WHERE entity_type = 'member' AND entity_id = ? AND action = 'member.payment' ORDER BY id DESC LIMIT 1", [$aid], '');
    check('changing an issued receipt: amount before and after are in the audit entry',
        $upd['ok'] && str_contains($payAudit, '"amount_was":"20000.00"') && str_contains($payAudit, '"amount_now":"19000.00"'), $payAudit);

    // ---- payment rules
    $badRenew = MS::recordPayment($aid, ['amount' => '1000', 'status' => 'received', 'purpose' => 'renewal']);
    check('a payment cannot be recorded with the purpose "renewal" (that would count as a second charge)', !$badRenew['ok'] && isset($badRenew['errors']['purpose']));
    $zero = MS::recordPayment($aid, ['amount' => '0', 'status' => 'received']);
    check('a received payment must be more than zero', !$zero['ok'] && isset($zero['errors']['amount']));
    $roll = MS::recordPayment($aid, ['amount' => '1000', 'status' => 'received', 'paid_at' => '2026-13-01']);
    $roll2 = MS::recordPayment($aid, ['amount' => '1000', 'status' => 'pending', 'paid_at' => '2026-02-30']);
    check('impossible payment dates are refused, not rolled over', !$roll['ok'] && isset($roll['errors']['paid_at']) && !$roll2['ok'] && isset($roll2['errors']['paid_at']));
    $future = MS::recordPayment($aid, ['amount' => '1000', 'status' => 'received', 'paid_at' => date('Y-m-d', strtotime('+10 days'))]);
    check('a received payment cannot be dated in the future', !$future['ok'] && isset($future['errors']['paid_at']));
    $order = MS::recordPayment($aid, ['amount' => '1000', 'status' => 'pending', 'term_start' => '2026-05-01', 'term_end' => '2026-01-01']);
    check('the end of a covered period cannot be before its start', !$order['ok'] && isset($order['errors']['term_end']));
    Database::insert('member_payments', ['member_id' => $aid, 'purpose' => 'membership', 'amount' => '500.00', 'currency' => 'UGX', 'method' => 'legacy_method', 'status' => 'pending']);
    $legacyId = (int) Database::value('SELECT MAX(id) FROM member_payments WHERE member_id = ?', [$aid], 0);
    $legacy = MS::updatePayment($legacyId, ['amount' => '500', 'status' => 'received', 'method' => 'legacy_method', 'purpose' => 'membership']);
    check('a payment keeps working after the owner removed its payment method from the list', $legacy['ok'] && !empty($legacy['payment']['receipt_number']), json_encode($legacy['errors'] ?? []));
    $newLegacy = MS::recordPayment($aid, ['amount' => '500', 'status' => 'pending', 'method' => 'another_unknown']);
    check('...but a new payment still needs a listed method', !$newLegacy['ok'] && isset($newLegacy['errors']['method']));
    check('parseMoney: thousands separators only as proper groups', MS::parseMoney('1,0,0') === null && MS::parseMoney('1,234.5') === '1234.50' && MS::parseMoney('50,00') === null);
    $renewalRow = Database::one("SELECT id FROM member_payments WHERE member_id = ? AND purpose = 'renewal' LIMIT 1", [$aid]);
    if ($renewalRow !== null) {
        $chg = MS::updatePayment((int) $renewalRow['id'], ['amount' => '1000', 'status' => 'pending', 'purpose' => 'membership']);
        check('a renewal charge row keeps its purpose', !$chg['ok'] && isset($chg['errors']['purpose']));
    }
    // received money with purpose "membership" against a pending renewal does not double count
    $before = MS::paymentSummary($aid);
    $pay = MS::recordPayment($aid, ['amount' => '1000', 'status' => 'received', 'purpose' => 'membership', 'method' => 'mobile_money']);
    $afterSum = MS::paymentSummary($aid);
    check('recording money received for a renewal as a membership payment lowers the balance by exactly that amount', $pay['ok'] && abs(($before['balance'] - $afterSum['balance']) - 1000.0) < 0.001
        && abs($before['charges'] - $afterSum['charges']) < 0.001);

    // ---- term dates: no overflow, no shortened terms
    $edge = MS::changeStatus((int) Database::value("SELECT id FROM members WHERE email = 'withid@selftest.invalid'", [], 0), 'approved', ['start_date' => '9999-12-31']);
    check('an absurd start date is refused with a message (no database error)', !$edge['ok'] && $edge['error'] === 'bad_start');
    $edge2 = MS::changeStatus((int) Database::value("SELECT id FROM members WHERE email = 'withid@selftest.invalid'", [], 0), 'approved', ['start_date' => '2026-01-10', 'expiry_date' => '2026-01-05']);
    check('an expiry date before the start date is refused', !$edge2['ok'] && $edge2['error'] === 'bad_expiry');
    $over = MS::renew($aid, ['start_date' => date('Y-m-d', strtotime('-2 months'))]);
    $far = MS::renew($aid, ['start_date' => '9999-12-31']);
    check('renewing while the term runs cannot start before it ends; absurd dates are refused', !$over['ok'] && $over['error'] === 'term_overlap' && !$far['ok'] && $far['error'] === 'bad_start', json_encode([$over['error'], $far['error']]));
    $expiryNow = (string) Member::find($aid)['expiry_date'];
    Database::execute("UPDATE members SET status = 'expired', expiry_date = DATE_SUB(CURDATE(), INTERVAL 3 YEAR) WHERE id = ?", [$aid]);
    $past = MS::renew($aid, ['start_date' => date('Y-m-d', strtotime('-3 years'))]);
    check('a renewal that would already be over is refused', !$past['ok'] && $past['error'] === 'term_over', json_encode($past));
    $okRen = MS::renew($aid);
    check('...while the default renewal of an expired member starts today', $okRen['ok'] && $okRen['member']['expiry_date'] === MS::computeExpiry(date('Y-m-d'), 'annual'));

    // ---- capacity: reinstating, reopening and renewing need a free place too
    $rc = MS::saveType(null, ['name' => 'Selftest Cap2', 'slug' => 'zz-selftest-cap2', 'fee_amount' => '1000', 'period' => 'annual', 'is_active' => '1', 'max_members' => '1', 'sort_order' => 9004]);
    $t4 = (int) $rc['id'];
    $ca = MembershipApplication::process($base($t4, 'capa@selftest.invalid'), $meta);
    $caId = (int) ($ca['member']['id'] ?? 0);
    check('capacity: first member of the capped type approved', $ca['ok'] && MS::changeStatus($caId, 'approved')['ok']);
    MS::changeStatus($caId, 'suspended', ['reason' => 'test']);
    $cb = MembershipApplication::process($base($t4, 'capb@selftest.invalid'), $meta);
    $cbId = (int) ($cb['member']['id'] ?? 0);
    check('capacity: a suspended member frees the place', $cb['ok'] && MS::changeStatus($cbId, 'approved')['ok']);
    $rein = MS::changeStatus($caId, 'approved');
    check('capacity: reinstating a suspended member needs a free place', !$rein['ok'] && $rein['error'] === 'type_full', json_encode($rein));
    MS::changeStatus($caId, 'cancelled', ['reason' => 'test']);
    $reopen = MS::changeStatus($caId, 'pending');
    check('capacity: reopening a cancelled application needs a free place', !$reopen['ok'] && $reopen['error'] === 'type_full');
    MS::changeStatus($cbId, 'expired');
    $cc = MembershipApplication::process($base($t4, 'capc@selftest.invalid'), $meta);
    $ccId = (int) ($cc['member']['id'] ?? 0);
    check('capacity: an expired member frees the place', $cc['ok'] && MS::changeStatus($ccId, 'approved')['ok']);
    $renCap = MS::renew($cbId);
    check('capacity: renewing an expired member needs a free place', !$renCap['ok'] && $renCap['error'] === 'type_full', json_encode($renCap));

    // ---- one-time form tokens
    $tok = \App\Services\Membership\OnceToken::issue('selftest');
    check('one-time token: accepted once, then refused; garbage refused', \App\Services\Membership\OnceToken::consume('selftest', $tok) && !\App\Services\Membership\OnceToken::consume('selftest', $tok)
        && !\App\Services\Membership\OnceToken::consume('selftest', 'nope') && !\App\Services\Membership\OnceToken::consume('other', $tok));

    // ---- e-mail wording for a membership without expiry
    [$s2, $b2] = MembershipMail::render('approved', (array) Member::find((int) $m2['id']));
    check('approval e-mail of a lifetime member says it does not expire (no dash)', str_contains($b2, 'does not expire') && !str_contains($b2, 'Valid until'), $b2);

    // ---- erasing the personal data of a member who cannot be deleted
    $er = MembershipApplication::process($base($t2, 'erase@selftest.invalid', ['consent_updates' => '1', 'skills' => 'Secret skills', 'payment_reference' => 'TXN-PRIVATE']), $meta);
    $erId = (int) ($er['member']['id'] ?? 0);
    $erasedIds[] = $erId;
    check('erase: a pending member cannot be erased', $er['ok'] && !MembershipApplication::anonymise($erId)['ok']);
    MS::changeStatus($erId, 'approved');
    MS::recordPayment($erId, ['amount' => '1000', 'status' => 'received', 'method' => 'cash_office', 'notes' => 'Paid by Jane at the office']);
    check('erase: an approved member cannot be erased', !MembershipApplication::anonymise($erId)['ok']);
    MS::changeStatus($erId, 'cancelled', ['reason' => 'Asked to leave']);
    $number = (string) Member::find($erId)['membership_number'];
    $done = MembershipApplication::anonymise($erId, null);
    $gone = (array) Member::find($erId);
    check('erase: the personal data is blanked, the number, fee and receipt stay', $done['ok'] && $gone['full_name'] === 'Erased on request' && str_starts_with($gone['email'], 'erased-') && $gone['phone'] === ''
        && $gone['skills'] === null && $gone['motivation'] === null && $gone['payment_reference'] === null && $gone['internal_notes'] === null && !empty($gone['anonymised_at'])
        && $gone['membership_number'] === $number && $gone['fee_amount'] === '0.00' && MemberPayment::exists(['member_id' => $erId])
        && Database::value('SELECT notes FROM member_payments WHERE member_id = ?', [$erId]) === null, json_encode($done));
    check('erase: the newsletter row of this form is removed too', (int) Database::value("SELECT COUNT(*) FROM subscribers WHERE email = 'erase@selftest.invalid'", [], 0) === 0);
    check('erase: it cannot be done twice, edited or reopened afterwards', !MembershipApplication::anonymise($erId)['ok'] && !MembershipApplication::update($erId, $base($t2, 'x@selftest.invalid'))['ok']
        && !MS::changeStatus($erId, 'pending')['ok']);
    $eraseAudit = (string) Database::value("SELECT summary FROM audit_log WHERE entity_type = 'member' AND entity_id = ? AND action = 'member.anonymise' ORDER BY id DESC LIMIT 1", [$erId], '');
    check('erase: the audit entry names the reference only', str_contains($eraseAudit, (string) $gone['application_ref']) && !str_contains($eraseAudit, 'Selftest Person'));

    // ---- approvals at the same instant (separate processes = separate database connections)
    $par = [];
    for ($i = 1; $i <= 6; $i++) {
        $r = MembershipApplication::process($base($t2, 'par' . $i . '@selftest.invalid'), $meta);
        $par[] = (int) ($r['member']['id'] ?? 0);
    }
    $startAt = microtime(true) + 3.0;
    $procs = [];
    foreach ($par as $pid) {
        $cmd = [PHP_BINARY, __DIR__ . '/selftest-membership-child.php', (string) $pid, sprintf('%.4f', $startAt)];
        $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__));
        $procs[] = [$proc, $pipes];
    }
    $results = [];
    foreach ($procs as [$proc, $pipes]) {
        $out = trim((string) stream_get_contents($pipes[1]));
        stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);
        $results[] = json_decode($out, true) ?: ['ok' => false, 'error' => 'no output: ' . $out];
    }
    $numbers = array_filter(array_column($results, 'number'));
    check('6 approvals at the same instant: all succeed with 6 different membership numbers', count(array_filter(array_column($results, 'ok'))) === 6 && count(array_unique($numbers)) === 6,
        json_encode($results));

    // ---- counters
    MS::flush();
    check('pending counter and dashboard tile', MS::pendingCount() === (int) Database::value("SELECT COUNT(*) FROM members WHERE status = 'pending'", [], 0));
} finally {
    $cleanup();
}
