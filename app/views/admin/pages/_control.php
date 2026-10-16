<?php
/**
 * ONE input control for a registry field type (used by top-level fields AND repeater sub-fields). OWNER: B3.
 * Vars: $field (normalised registry field), $name (input name, also used as id unless $id is given), $value, $alt (image alt text),
 *       $altName (image alt input name), $id, $rowSource (bool: this input names the repeater row), $describedBy, $invalid (bool),
 *       $compact (bool: repeater sub-field, no character counters).
 * Names such as blocks[hero.title] are valid PHP input names: PHP keeps the dot when it is inside the brackets.
 */
$type = $field['type'];
$id = $id ?? $name;
$invalid = !empty($invalid);
$compact = !empty($compact);
$common = [
    'id'               => $id,
    'name'             => $name,
    'aria-invalid'     => $invalid ? 'true' : null,
    'aria-describedby' => !empty($describedBy) ? $describedBy : null,
    'data-pe-row-source' => !empty($rowSource) ? '1' : null,
];
$counter = !$compact && !empty($field['maxlength']) ? ['maxlength' => (int) $field['maxlength'], 'data-maxlength-counter' => true] : [];
switch ($type):
    case 'textarea': ?>
        <textarea class="textarea" rows="<?= (int) ($field['rows'] ?? 4) ?>"<?= attrs($common + $counter + ['placeholder' => $field['placeholder'] ?? null, 'maxlength' => $counter['maxlength'] ?? null]) ?>><?= e($value) ?></textarea>
    <?php break;
    case 'richtext': ?>
        <textarea class="textarea textarea--tall" rows="<?= (int) ($field['rows'] ?? 10) ?>" data-richtext<?= attrs($common) ?>><?= e($value) ?></textarea>
    <?php break;
    case 'url': ?>
        <input class="input" type="text" inputmode="url" autocomplete="off" spellcheck="false" list="pe-links" placeholder="<?= e($field['placeholder'] ?? '/page, https://example.org, mailto: or tel:') ?>" value="<?= e($value) ?>"<?= attrs($common) ?>>
    <?php break;
    case 'email': ?>
        <input class="input" type="email" autocomplete="off" value="<?= e($value) ?>"<?= attrs($common + ['placeholder' => $field['placeholder'] ?? null]) ?>>
    <?php break;
    case 'number': ?>
        <input class="input" type="number" value="<?= e($value) ?>"<?= attrs($common + ['min' => $field['min'] ?? null, 'max' => $field['max'] ?? null, 'step' => $field['step'] ?? 'any', 'placeholder' => $field['placeholder'] ?? null]) ?>>
    <?php break;
    case 'boolean': ?>
        <input type="hidden" name="<?= e($name) ?>" value="0">
        <label class="switch">
            <input type="checkbox" value="1" id="<?= e($id) ?>" name="<?= e($name) ?>"<?= $value ? ' checked' : '' ?>>
            <span class="switch__track" aria-hidden="true"></span>
            <span class="switch__text"><?= e($field['label']) ?></span>
        </label>
    <?php break;
    case 'select': ?>
        <select class="select"<?= attrs($common) ?>>
            <?php foreach ((array) ($field['options'] ?? []) as $ov => $ol): ?>
                <option value="<?= e($ov) ?>"<?= (string) $ov === (string) $value ? ' selected' : '' ?>><?= e($ol) ?></option>
            <?php endforeach; ?>
        </select>
    <?php break;
    case 'image': ?>
        <?= partial('admin/pages/_image', [
            'name' => $name, 'altName' => $altName ?? ($name . '.alt'), 'value' => (string) $value, 'alt' => (string) ($alt ?? ''), 'id' => $id,
            'label' => $field['label'],
        ]) ?>
    <?php break;
    default: ?>
        <input class="input" type="text"<?= attrs($common + $counter + ['value' => (string) $value, 'placeholder' => $field['placeholder'] ?? null, 'maxlength' => (int) ($field['maxlength'] ?? 500)]) ?>>
<?php endswitch;
