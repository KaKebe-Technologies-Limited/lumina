<?php
/**
 * Field helpers of the card designer (required by index.php; the returned closures share its $values, $defs). OWNER: CARD agent.
 *
 *   $f['text']($key, $extra)     one-line or multi-line text field with the maximum length of its setting and a character counter
 *   $f['check']($key, $help)     on/off switch that always posts a value
 *   $f['select']($key, $options, $extra)
 *   $f['chips']($scopeIds, $placeholders)   buttons that insert a {placeholder} at the cursor of the field that was used last inside the scope
 *   $f['label']($key)            the friendly label of a setting
 *
 * Labels here are the ones the owner reads; CardSettings::DEFS has the technical ones.
 */

$labels = [
    'membership_card_logo' => 'Logo', 'membership_card_navy' => 'Side panel colour', 'membership_card_accent' => 'Accent colour', 'membership_card_gold' => 'Tagline colour',
    'membership_card_green' => 'Active status colour', 'membership_card_tile' => 'Information tile colour', 'membership_card_label_color' => 'Small label colour', 'membership_card_muted' => 'Side panel small text colour',
    'membership_card_org_name' => 'Organisation name', 'membership_card_tagline' => 'Tagline', 'membership_card_label' => 'Card label (top right)',
    'membership_card_label_member' => 'Label above the member\'s name', 'membership_card_label_from' => 'Label above the location',
    'membership_card_location_format' => 'Location line', 'membership_card_label_number' => 'Label above the member number',
    'membership_card_label_valid_until' => 'Label above the expiry date', 'membership_card_scan_text' => 'Text under the QR code',
    'membership_card_renews_text' => 'Text under the expiry date', 'membership_card_no_expiry_text' => 'Wording when a membership never expires',
    'membership_card_status_active' => 'Active card', 'membership_card_status_expired' => 'Expired card', 'membership_card_status_suspended' => 'Suspended card',
    'membership_card_status_revoked' => 'Revoked card (not valid)', 'membership_card_panel_text' => 'Text at the bottom of the side panel',
    'membership_card_tile_1' => 'Tile 1 (left)', 'membership_card_tile_2' => 'Tile 2 (middle)', 'membership_card_tile_3' => 'Tile 3 (right)',
    'membership_card_label_member_since' => 'Label: member since', 'membership_card_label_payment' => 'Label: payment', 'membership_card_label_fee' => 'Label: fee and period',
    'membership_card_label_type' => 'Label: membership type', 'membership_card_label_district' => 'Label: district',
    'membership_card_pay_paid' => 'Payment cleared', 'membership_card_pay_waived' => 'Fee waived', 'membership_card_pay_pending' => 'Still to pay',
    'membership_card_pay_free' => 'Free membership (no fee)', 'membership_card_pay_partial' => 'Part paid', 'membership_card_pay_tbd' => 'Fee not set yet',
    'membership_card_fee_free' => 'Free membership', 'membership_card_fee_tbd' => 'Fee not set yet', 'membership_card_fee_per_year' => 'After a yearly fee', 'membership_card_fee_per_month' => 'After a monthly fee',
    'membership_card_fee_per_quarter' => 'After a quarterly fee', 'membership_card_fee_for_life' => 'After a lifetime fee', 'membership_card_fee_one_off' => 'After a one-time fee',
    'membership_card_period_annual' => 'Annual', 'membership_card_period_monthly' => 'Monthly', 'membership_card_period_quarterly' => 'Quarterly', 'membership_card_period_lifetime' => 'Lifetime', 'membership_card_period_one_time' => 'One-time',
    'membership_card_adverb_annual' => 'Yearly memberships', 'membership_card_adverb_monthly' => 'Monthly memberships', 'membership_card_adverb_quarterly' => 'Quarterly memberships',
    'membership_card_joining_paid' => 'Joining fee paid', 'membership_card_joining_due' => 'Joining fee still to pay', 'membership_card_joining_waived' => 'Joining fee waived',
    'membership_card_footer_1' => 'Line 1', 'membership_card_footer_2' => 'Line 2', 'membership_card_footer_3' => 'Line 3 (optional)',
    'membership_card_support_phone' => 'Support phone', 'membership_card_support_email' => 'Support e-mail',
    'membership_card_number_format' => 'Member number format',
    'membership_card_trigger' => 'When is a card issued and e-mailed automatically?', 'membership_card_reissue_renewal' => 'Issue a new card when a renewal is paid',
    'membership_card_require_photo' => 'Do not issue a card until the member has a photo', 'membership_photo_required' => 'Ask for a photo on the registration form',
    'membership_proof_retention_days' => 'Keep photos and proofs after a rejection or cancellation (days)',
    'membership_card_email_subject' => 'Subject', 'membership_card_email_body' => 'Message',
    'membership_card_show_photo' => 'Show the photo block', 'membership_card_show_qr' => 'Show the QR code',
];

$label = static fn(string $k): string => (string) ($labels[$k] ?? ($defs[$k][2] ?? $k));

$text = static function (string $k, array $extra = []) use ($defs, $values, $label): string {
    $type = (string) ($defs[$k][1] ?? 'str:100');
    [$kind, $n] = array_pad(explode(':', $type, 3), 3, '');
    $attrs = (array) ($extra['attrs'] ?? []);
    unset($extra['attrs']);
    if (in_array($kind, ['str', 'text'], true) && (int) $n > 0) {
        $attrs['data-maxlength-counter'] = true;
        $extra += ['maxlength' => (int) $n];
    }
    $attrs += ['data-mc-field' => true, 'autocomplete' => 'off'];
    return partial('partials/form-field', array_merge([
        'name' => $k, 'label' => $label($k), 'value' => (string) ($values[$k] ?? ''),
        'type' => $kind === 'text' ? 'textarea' : ($kind === 'email' ? 'email' : 'text'),
        'attrs' => $attrs,
    ], $extra));
};

$check = static function (string $k, string $help = '') use ($values, $label): string {
    return partial('admin/_content/switch', ['name' => $k, 'label' => $label($k), 'checked' => (string) ($values[$k] ?? '') === '1', 'help' => $help !== '' ? $help : null]);
};

$select = static function (string $k, array $options, array $extra = []) use ($values, $label): string {
    $attrs = (array) ($extra['attrs'] ?? []);
    unset($extra['attrs']);
    return partial('partials/form-field', array_merge([
        'name' => $k, 'label' => $label($k), 'type' => 'select', 'options' => $options, 'value' => (string) ($values[$k] ?? ''),
        'attrs' => $attrs + ['data-mc-field' => true],
    ], $extra));
};

/**
 * Placeholder buttons. $scope = CSS selector of the fields they type into (the last one used, else the first); $items = [placeholder => short description].
 * The buttons are plain buttons (type=button): without JavaScript they do nothing, and the owner types the placeholder, which the help text also lists.
 */
$chips = static function (string $scope, array $items, string $caption = 'Click to put one into the text') use ($label): string {
    $out = '<div class="mc-chips" role="group" aria-label="' . e($caption) . '" data-mc-chips data-mc-scope="' . e($scope) . '">';
    foreach ($items as $ph => $desc) {
        $out .= '<button type="button" class="mc-chip" data-mc-insert="' . e((string) $ph) . '" title="' . e((string) $desc) . '"><code>' . e((string) $ph) . '</code><span class="mc-chip__desc">' . e((string) $desc) . '</span></button>';
    }
    return $out . '</div>';
};

return ['text' => $text, 'check' => $check, 'select' => $select, 'chips' => $chips, 'label' => $label];
