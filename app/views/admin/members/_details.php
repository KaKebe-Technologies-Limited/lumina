<?php
/**
 * Read-only view of everything the person submitted, grouped like the registration form. Var: $member.
 * PRIVACY: personal data. Only reached through members.manage.
 */
use App\Services\Inbox\Support;
use App\Services\Membership\MembershipService as MS;

$m = $member;
$id = (int) $m['id'];
$none = '<span class="text-muted">Not given</span>';
$val = static fn(mixed $v): string => trim((string) $v) === '' ? $none : e((string) $v);
$long = static fn(mixed $v): string => trim((string) $v) === '' ? $none : '<div class="members-prose">' . nl2p((string) $v) . '</div>';
$fact = static fn(string $label, string $html, bool $wide = false): string =>
    '<div class="members-fact' . ($wide ? ' members-fact--wide' : '') . '"><dt>' . e($label) . '</dt><dd>' . $html . '</dd></div>';
$phone = static function (mixed $v) use ($none): string {
    $v = trim((string) $v);
    return $v === '' ? $none : (phone_href($v) !== '' ? '<a href="' . e(phone_href($v)) . '">' . e($v) . '</a>' : e($v));
};
$other = static fn(string $list, string $key, ?string $free): string => e(MS::optionLabel($list, $key)) . ($key === MS::OTHER_KEY && trim((string) $free) !== '' ? ': ' . e((string) $free) : '');

$age = MS::ageOn((string) $m['date_of_birth']);
$minor = MS::isMinor((string) $m['date_of_birth']);
$mail = Support::mailto((string) $m['email']);
$wa = trim((string) ($m['whatsapp'] ?? ''));
$waDigits = preg_replace('/[^0-9]/', '', $wa) ?? '';
$interests = MS::interestLabels($m['interests'] ?? null);
$joining = MS::joiningFeeLabel($m);
$erased = !empty($m['anonymised_at']);
?>
<section class="card members-details" id="details" aria-labelledby="details-title">
    <div class="card__header">
        <h2 class="card__title" id="details-title">Application</h2>
        <?php if (!$erased): ?><a class="btn btn--outline btn--sm" href="<?= e(url('admin/members/' . $id, ['edit' => 1])) ?>"><?= icon('edit', 'icon--sm') ?> Edit details</a><?php endif; ?>
    </div>
    <div class="card__body">
        <h3 class="members-group">Membership</h3>
        <dl class="members-facts">
            <?= $fact('Type', e($m['type_name'])) ?>
            <?= $fact('Fee agreed at application', e(MS::memberFeeLabel($m)) . ($joining !== '' ? '<span class="cell-sub">' . e($joining) . '</span>' : '')) ?>
            <?= $fact('Applied on', e(format_datetime($m['created_at']))) ?>
            <?php if (!empty($m['approved_at'])): ?><?= $fact('Approved on', e(format_datetime($m['approved_at']))) ?><?php endif; ?>
        </dl>

        <?php if ($erased): ?>
            <p class="members-note members-note--warn"><?= icon('lock', 'icon--sm') ?> The personal data of this record was erased on <?= e(format_date($m['anonymised_at'], 'j F Y')) ?>. The application reference, membership number, fee and payments were kept.</p>
        <?php else: ?>
        <h3 class="members-group">Personal</h3>
        <dl class="members-facts">
            <?= $fact('Full name', e($m['full_name'])) ?>
            <?= $fact('Gender', e(MS::optionLabel('gender', (string) $m['gender']))) ?>
            <?= $fact('Date of birth', e(format_date($m['date_of_birth'])) . ($age >= 0 ? ' <span class="text-muted">(age ' . (int) $age . ')</span>' : '') . ($minor ? ' <span class="badge badge--outline">Under 18</span>' : '')) ?>
            <?= $fact('Nationality', $val($m['nationality'])) ?>
            <?php if (!empty($m['id_number'])): ?><?= $fact('National ID / passport number', e($m['id_number'])) ?><?php endif; ?>
        </dl>

        <h3 class="members-group">Contact</h3>
        <dl class="members-facts">
            <?= $fact('E-mail', $mail !== '' ? '<a href="' . e($mail) . '">' . e($m['email']) . '</a>' : e($m['email'])) ?>
            <?= $fact('Phone', $phone($m['phone'])) ?>
            <?= $fact('WhatsApp', $waDigits !== '' ? '<a href="' . e('https://wa.me/' . $waDigits) . '" target="_blank" rel="noopener noreferrer">' . e($wa) . '</a>' : $none) ?>
            <?= $fact('Country', $val($m['country'])) ?>
            <?= $fact('District / region', $val($m['region'])) ?>
            <?= $fact('Sub-county / city', $val($m['city'])) ?>
            <?= $fact('Village / address', $val($m['address']), true) ?>
        </dl>

        <h3 class="members-group">Livelihood</h3>
        <dl class="members-facts">
            <?= $fact('Occupation', $other('occupation', (string) $m['occupation_status'], $m['occupation_other'] ?? null)) ?>
            <?= $fact('Highest education', e(MS::optionLabel('education', (string) $m['education_level']))) ?>
            <?= $fact('Organisation / school', $val($m['organisation'])) ?>
        </dl>

        <h3 class="members-group">Participation</h3>
        <dl class="members-facts">
            <?= $fact('Areas of interest', $interests === [] ? $none : '<span class="members-tags">' . implode('', array_map(static fn(string $l): string => '<span class="badge badge--gold">' . e($l) . '</span>', $interests)) . '</span>', true) ?>
            <?= $fact('Availability', e(MS::optionLabel('availability', (string) $m['availability']))) ?>
            <?= $fact('Heard about us', $other('heard', (string) $m['heard_about'], $m['heard_about_other'] ?? null)) ?>
            <?= $fact('Skills and experience', $long($m['skills']), true) ?>
            <?= $fact('Why they want to join', $long($m['motivation']), true) ?>
        </dl>

        <?php if ($minor || !empty($m['guardian_name'])): ?>
            <h3 class="members-group">Parent or guardian</h3>
            <dl class="members-facts">
                <?= $fact('Name', $val($m['guardian_name'])) ?>
                <?= $fact('Phone', $phone($m['guardian_phone'])) ?>
                <?= $fact('Relationship', $val($m['guardian_relationship'])) ?>
                <?= $fact('Consent recorded', !empty($m['consent_guardian_at']) ? e(format_datetime($m['consent_guardian_at'])) : '<span class="text-muted">Not recorded</span>') ?>
            </dl>
        <?php endif; ?>

        <h3 class="members-group">Emergency contact</h3>
        <dl class="members-facts">
            <?= $fact('Name', $val($m['emergency_name'])) ?>
            <?= $fact('Phone', $phone($m['emergency_phone'])) ?>
            <?= $fact('Relationship', $val($m['emergency_relationship'])) ?>
        </dl>

        <h3 class="members-group">Payment preference</h3>
        <dl class="members-facts">
            <?= $fact('Preferred method', e(MS::optionLabel('payment_methods', (string) $m['payment_method']))) ?>
            <?= $fact('Reference given', $val($m['payment_reference'])) ?>
        </dl>
        <?php endif; ?>
    </div>
</section>
