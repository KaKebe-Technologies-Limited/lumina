<?php
/**
 * ONE QUESTION of a public form (markup CONTRACT for assets/js/forms.js; docs/FORMS.md section 8.3). Keeps: the input names (f_<id>, f_<id>[] , f_<id>_other),
 * the ids (q-<id> input / group, h-<id> help, e-<id> error), data-lf-field / data-lf-type / data-lf-required / data-lf-logic on the wrapper and the
 * aria-describedby / aria-invalid wiring. Labels sit ABOVE the control (always visible, never a placeholder substitute); choices are large tiles; a file
 * question is a drop zone around the real file input.
 *
 * Vars: $f (field array from the normalised definition), $input (posted values to refill the form: f_<id> => string|list), $errors (field id => message),
 *       $readonly (bool, print / preview).
 */
use App\Services\Forms\FormDefinition;
use App\Services\Forms\FormText;

$id = (string) $f['id'];
$type = (string) $f['type'];
$name = 'f_' . $id;
$err = $errors[$id] ?? null;
$required = !empty($f['required']);
$logic = $f['logic'] ?? null;
$wrapAttrs = ' data-lf-field="' . e($id) . '" data-lf-type="' . e($type) . '"' . ($required ? ' data-lf-required="1"' : '')
    . ($logic !== null ? ' data-lf-logic="' . e((string) json_encode($logic)) . '"' : '');
$cls = 'lf__field lf__field--' . e((string) ($f['width'] ?? 'full')) . ($err ? ' has-error' : '');

// ---- layout blocks -------------------------------------------------------------------------------------------------
if ($type === 'heading') {
    echo '<div class="lf__block lf__block--heading"' . $wrapAttrs . '><h3 class="lf__heading">' . e((string) $f['label']) . '</h3>'
        . (trim((string) ($f['help'] ?? '')) !== '' ? '<p class="lf__help">' . e((string) $f['help']) . '</p>' : '') . '</div>';
    return;
}
if ($type === 'paragraph') {
    echo '<div class="lf__block lf__block--text"' . $wrapAttrs . '>' . nl2p((string) ($f['text'] ?? '')) . '</div>';
    return;
}
if ($type === 'divider') {
    echo '<div class="lf__block lf__block--divider"' . $wrapAttrs . '><hr></div>';
    return;
}

$posted = $input[$name] ?? null;
if (is_array($posted) && $type !== 'checkboxes') {
    $posted = '';                   // a hand-made request can post a list for any name: only multiple choice takes one
}
$otherVal = is_string($input[$name . '_other'] ?? null) ? (string) $input[$name . '_other'] : '';
$value = $posted !== null ? $posted : ($f['default'] ?? '');
$helpId = 'h-' . $id;
$errId = 'e-' . $id;
$hasHelp = trim((string) ($f['help'] ?? '')) !== '';
$describedBy = trim(($hasHelp ? $helpId . ' ' : '') . ($err ? $errId : ''));
$aria = ($describedBy !== '' ? ' aria-describedby="' . e($describedBy) . '"' : '') . ($err ? ' aria-invalid="true"' : '');
$ariaReq = $aria . ($required ? ' required' : '');
$dis = !empty($readonly) ? ' disabled' : '';
$reqMark = $required
    ? ' <span class="lf__req" aria-hidden="true">*</span><span class="visually-hidden"> (' . e(FormText::get('ui.required_mark')) . ')</span>'
    : '';
$label = '<label class="lf__label" for="q-' . e($id) . '">' . e((string) $f['label']) . $reqMark . '</label>';
$legend = static fn(string $l): string => '<legend class="lf__label">' . e($l) . $reqMark . '</legend>';
$help = $hasHelp ? '<p class="lf__help" id="' . e($helpId) . '">' . e((string) $f['help']) . '</p>' : '';
$error = '<p class="lf__error" id="' . e($errId) . '" data-lf-error' . ($err ? ' role="alert"' : ' hidden') . '>' . ($err ? e((string) $err) : '') . '</p>';
$val = (array) ($f['validation'] ?? []);
$maxlen = in_array($type, ['text', 'textarea', 'email'], true) ? FormDefinition::maxLength($f) : 0;
$attrData = '';
foreach (['min_length' => 'minlength', 'max_length' => 'maxlength'] as $k => $a) {
    if (in_array($type, ['text', 'textarea'], true) && isset($val[$k]) && $val[$k] !== null) {
        $attrData .= ' ' . $a . '="' . (int) $val[$k] . '"';
    }
}
if (in_array($type, ['text', 'textarea'], true) && !isset($val['max_length'])) {
    $attrData .= ' maxlength="' . $maxlen . '"';
}
if (!empty($val['pattern'])) {
    $attrData .= ' data-lf-pattern="' . e((string) $val['pattern']) . '"';
}
// browser autofill hints from the question's key (a hint only: the server never trusts it)
$autoMap = ['full_name' => 'name', 'name' => 'name', 'your_name' => 'name', 'first_name' => 'given-name', 'last_name' => 'family-name', 'surname' => 'family-name',
    'organisation' => 'organization', 'organization' => 'organization', 'company' => 'organization', 'job_title' => 'organization-title', 'address' => 'street-address',
    'city' => 'address-level2', 'town' => 'address-level2', 'country' => 'country-name', 'postal_code' => 'postal-code', 'location' => 'address-level2'];
$auto = $autoMap[(string) ($f['key'] ?? '')] ?? '';
?>
<div class="<?= $cls ?>"<?= $wrapAttrs ?>>
<?php
switch ($type) {
    case 'text':
    case 'email':
    case 'phone':
    case 'number':
    case 'date':
    case 'time':
        $htmlType = ['text' => 'text', 'email' => 'email', 'phone' => 'tel', 'number' => 'text', 'date' => 'date', 'time' => 'time'][$type];
        $extra = '';
        if ($type === 'number') {
            $extra .= ' inputmode="decimal"';
            foreach (['min', 'max'] as $b) {
                if (isset($val[$b]) && $val[$b] !== null) {
                    $extra .= ' data-lf-' . $b . '="' . e((string) $val[$b]) . '"';
                }
            }
        }
        if ($type === 'date' || $type === 'time') {
            foreach (['min', 'max'] as $b) {
                if (!empty($val[$b])) {
                    $v = ($val[$b] === 'today') ? \App\Services\Forms\FormService::now()->format('Y-m-d') : (string) $val[$b];
                    $extra .= ' ' . $b . '="' . e($v) . '"';
                }
            }
        }
        if ($type === 'phone') {
            $extra .= ' autocomplete="tel" inputmode="tel"';
        }
        if ($type === 'email') {
            $extra .= ' autocomplete="email" inputmode="email" autocapitalize="none" spellcheck="false" maxlength="190"';
        }
        if ($type === 'text' && $auto !== '') {
            $extra .= ' autocomplete="' . e($auto) . '"';
        }
        echo $label . $help;
        echo '<input class="input lf__input" type="' . $htmlType . '" id="q-' . e($id) . '" name="' . e($name) . '" value="' . e(is_array($value) ? '' : (string) $value) . '"'
            . (($f['placeholder'] ?? '') !== '' ? ' placeholder="' . e((string) $f['placeholder']) . '"' : '') . $extra . $attrData . $ariaReq . $dis . '>';
        echo $error;
        break;

    case 'textarea':
        echo $label . $help;
        echo '<textarea class="textarea lf__input" id="q-' . e($id) . '" name="' . e($name) . '" rows="5"'
            . (($f['placeholder'] ?? '') !== '' ? ' placeholder="' . e((string) $f['placeholder']) . '"' : '') . $attrData . $ariaReq . $dis . '>' . e(is_array($value) ? '' : (string) $value) . '</textarea>';
        echo $error;
        break;

    case 'select':
        echo $label . $help;
        echo '<select class="select lf__input" id="q-' . e($id) . '" name="' . e($name) . '"' . $ariaReq . $dis . '>';
        echo '<option value="">' . e((string) (($f['placeholder'] ?? '') !== '' ? $f['placeholder'] : FormText::get('ui.select_prompt'))) . '</option>';
        foreach ((array) $f['options'] as $o) {
            echo '<option value="' . e((string) $o['value']) . '"' . ((string) $value === (string) $o['value'] ? ' selected' : '') . '>' . e((string) $o['label']) . '</option>';
        }
        if (!empty($f['allow_other'])) {
            echo '<option value="' . e(FormDefinition::OTHER) . '"' . ((string) $value === FormDefinition::OTHER ? ' selected' : '') . '>' . e(($f['other_label'] ?? '') !== '' ? (string) $f['other_label'] : FormText::get('ui.other_label')) . '</option>';
        }
        echo '</select>';
        if (!empty($f['allow_other'])) {
            echo '<input class="input lf__input lf__other" type="text" name="' . e($name . '_other') . '" value="' . e($otherVal) . '" maxlength="' . FormDefinition::MAX_OTHER_TEXT . '" placeholder="' . e(FormText::get('ui.other_placeholder')) . '" aria-label="' . e(FormText::get('ui.other_placeholder')) . '" data-lf-other' . $dis . '>';
        }
        echo $error;
        break;

    case 'radio':
    case 'checkboxes':
    case 'yes_no':
    case 'rating':
        $multi = $type === 'checkboxes';
        $chosen = $multi ? array_map('strval', (array) $value) : [(string) (is_array($value) ? '' : $value)];
        $role = $multi ? '' : ' role="radiogroup"' . ($required ? ' aria-required="true"' : '');
        echo '<fieldset class="lf__group" id="q-' . e($id) . '"' . $aria . $role . ' tabindex="-1">' . $legend((string) $f['label']);
        echo $help;
        $opts = [];
        if ($type === 'yes_no') {
            $opts = [['value' => 'yes', 'label' => FormText::get('ui.yes')], ['value' => 'no', 'label' => FormText::get('ui.no')]];
        } elseif ($type === 'rating') {
            for ($i = 1; $i <= 5; $i++) {
                $opts[] = ['value' => (string) $i, 'label' => (string) $i];
            }
        } else {
            $opts = (array) $f['options'];
            if (!empty($f['allow_other'])) {
                $opts[] = ['value' => FormDefinition::OTHER, 'label' => ($f['other_label'] ?? '') !== '' ? (string) $f['other_label'] : FormText::get('ui.other_label')];
            }
        }
        echo '<div class="lf__options lf__options--' . e($type) . '">';
        foreach ($opts as $n => $o) {
            $oid = 'q-' . $id . '-' . $n;
            $isOther = (string) $o['value'] === FormDefinition::OTHER;
            $on = in_array((string) $o['value'], $chosen, true);
            echo '<div class="lf__option' . ($on ? ' is-checked' : '') . '"><input class="lf__check" type="' . ($multi ? 'checkbox' : 'radio') . '" id="' . e($oid) . '" name="' . e($name . ($multi ? '[]' : '')) . '" value="' . e((string) $o['value']) . '"'
                . ($on ? ' checked' : '') . ($isOther ? ' data-lf-other-toggle' : '')
                . ($type === 'rating' ? ' aria-label="' . e(str_replace('{n}', (string) $o['label'], FormText::get('ui.rating_label'))) . '"' : '') . $dis . '>'
                . '<label for="' . e($oid) . '"' . ($type === 'rating' ? ' aria-hidden="true"' : '') . '>' . e((string) $o['label']) . '</label>';
            if ($isOther) {
                echo '<input class="input lf__input lf__other" type="text" name="' . e($name . '_other') . '" value="' . e($otherVal) . '" maxlength="' . FormDefinition::MAX_OTHER_TEXT . '" placeholder="' . e(FormText::get('ui.other_placeholder')) . '" aria-label="' . e(FormText::get('ui.other_placeholder')) . '" data-lf-other' . $dis . '>';
            }
            echo '</div>';
        }
        echo '</div>';
        if ($type === 'rating') {
            echo '<p class="lf__scale"><span>' . e(FormText::get('ui.rating_low')) . '</span><span>' . e(FormText::get('ui.rating_high')) . '</span></p>';
        }
        echo $error . '</fieldset>';
        break;

    case 'consent':
        $checked = in_array(is_array($value) ? '' : (string) $value, ['1', 'on', 'yes', 'true'], true);
        echo '<div class="lf__consent' . ($checked ? ' is-checked' : '') . '"><input class="lf__check" type="checkbox" id="q-' . e($id) . '" name="' . e($name) . '" value="1"' . ($checked ? ' checked' : '') . $ariaReq . $dis . '>'
            . '<label for="q-' . e($id) . '">' . e((string) $f['label']) . $reqMark . '</label></div>' . $help . $error;
        break;

    case 'file':
        $rules = (array) ($f['file'] ?? []);
        $many = (int) ($rules['max_files'] ?? 1) > 1;
        $types = array_map('strtolower', (array) ($rules['types'] ?? ['pdf']));
        $accept = '.' . implode(',.', array_merge($types, in_array('jpg', $types, true) ? ['jpeg'] : []));
        $hint = FormText::get($many ? 'ui.file_hint_many' : 'ui.file_hint', ['types' => strtoupper(implode(', ', $types)), 'max' => (int) ($rules['max_mb'] ?? 5), 'count' => (int) ($rules['max_files'] ?? 1)]);
        echo $label . $help;
        echo '<div class="lf__drop" data-lf-drop><span class="lf__drop-icon" aria-hidden="true">' . icon('upload') . '</span>'
            . '<span class="lf__drop-text"><span class="lf__drop-title" data-lf-drop-title>' . e(FormText::get($many ? 'ui.file_choose_many' : 'ui.file_choose')) . '</span>'
            . '<span class="lf__drop-hint">' . e($hint) . '</span></span>';
        echo '<input class="lf__file" type="file" id="q-' . e($id) . '" name="' . e($name . ($many ? '[]' : '')) . '"' . ($many ? ' multiple' : '') . ' accept="' . e($accept) . '"'
            . ' data-lf-max-mb="' . (int) ($rules['max_mb'] ?? 5) . '" data-lf-max-files="' . (int) ($rules['max_files'] ?? 1) . '" data-lf-types="' . e(implode(',', $types)) . '"' . $ariaReq . $dis . '></div>';
        echo '<ul class="lf__files" data-lf-files></ul>';
        echo $error;
        break;
}
?>
</div>
