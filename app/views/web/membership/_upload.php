<?php
/**
 * One FILE FIELD of the membership forms: the headshot or the proof of payment. Used by the registration form (/membership/register) and by the
 * member's private page (/membership/me/{token}). OWNER: DASHBOARD agent. Behaviour: assets/js/membership-dashboard.js (preview, checks, camera hint);
 * WITHOUT JavaScript the native file input is shown and the server checks everything (MemberFiles).
 *
 * Vars: $kind* ('photo' | 'proof'), $name* (input name = photo | proof), $label*, $pick* (text of the choose button), $accept*, $help,
 *       $required (bool), $id (default f-<name>), $msgs (['none','size','type','small','invalid'] => editable error texts, printed as data attributes for the script),
 *       $maxBytes, $minSide (photo), $current (url of the file already stored, or ''), $currentKind ('image' | 'pdf'), $currentAlt, $previewAlt, $initials
 *       (photo placeholder: letters drawn on the orange block, like the card), $initialsFrom (css selector of the name field: the letters follow it while typing),
 *       $hideLabel (the label stays for screen readers only: the private page has a heading and a button that say the same), $camera (text of the camera button; '' = none), $remove (text of the remove button), $chosen (text before the file name), $notice (SAFE small note under the help).
 * Everything printed is escaped here.
 */
$id = (string) ($id ?? 'f-' . preg_replace('/[^a-z0-9_-]/i', '-', (string) $name));
$err = error((string) $name);
$help = trim((string) ($help ?? ''));
$current = (string) ($current ?? '');
$isPhoto = $kind === 'photo';
$ids = [];
if ($err !== null) {
    $ids[] = $id . '-error';
}
if ($help !== '') {
    $ids[] = $id . '-help';
}
$ids[] = $id . '-status';
$msgs = (array) ($msgs ?? []);
$initials = trim((string) ($initials ?? ''));
$camera = trim((string) ($camera ?? ''));
$opt = static fn(mixed $v): ?string => is_scalar($v) && trim((string) $v) !== '' ? trim((string) $v) : null;       // an empty text is not printed
$data = [
    'data-upload'      => $kind,
    'data-max-bytes'   => (int) ($maxBytes ?? 5242880),
    'data-min-side'    => $isPhoto ? (int) ($minSide ?? 300) : null,
    'data-msg-none'    => $opt($msgs['none'] ?? null),
    'data-msg-size'    => $opt($msgs['size'] ?? null),
    'data-msg-type'    => $opt($msgs['type'] ?? null),
    'data-msg-small'   => $opt(isset($msgs['small']) ? str_replace('{min}', (string) (int) ($minSide ?? 300), (string) $msgs['small']) : null),
    'data-msg-invalid' => $opt($msgs['invalid'] ?? null),
    'data-msg-missing' => $opt($msgs['missing'] ?? null),
    'data-chosen'      => $opt($chosen ?? null),
    'data-alt-current' => $opt($currentAlt ?? null),
    'data-alt-preview' => $opt($previewAlt ?? null),
    'data-initials-from' => $opt($initialsFrom ?? null),
];
?>
<div class="field mb-upload mb-upload--<?= e($kind) ?><?= $err !== null ? ' field--error' : '' ?><?= !empty($class) ? ' ' . e($class) : '' ?>"<?= attrs($data) ?>>
    <label class="field__label<?= !empty($hideLabel) ? ' visually-hidden' : '' ?>" for="<?= e($id) ?>"><?= e($label) ?><?php if (!empty($required) && empty($hideLabel)): ?> <span class="field__req" aria-hidden="true" data-req-mark>*</span><?php endif; ?></label>
    <div class="mb-upload__box">
        <div class="mb-upload__thumb<?= $current !== '' || $initials !== '' || $isPhoto ? '' : ' mb-upload__thumb--doc' ?>" data-upload-thumb>
            <?php if ($current !== '' && ($currentKind ?? 'image') === 'image'): ?>
                <img class="mb-upload__img" src="<?= e($current) ?>" alt="<?= e((string) ($currentAlt ?? '')) ?>" width="160" height="160" decoding="async" data-upload-img>
            <?php else: ?>
                <img class="mb-upload__img" src="" alt="" width="160" height="160" hidden data-upload-img>
            <?php endif; ?>
            <span class="mb-upload__ph<?= $current !== '' && ($currentKind ?? 'image') === 'image' ? ' is-covered' : '' ?>" aria-hidden="true" data-upload-ph>
                <?php if ($isPhoto): ?>
                    <span class="mb-upload__initials" data-upload-initials<?= $initials === '' ? ' hidden' : '' ?>><?= e($initials) ?></span>
                    <span class="mb-upload__icon" data-upload-icon<?= $initials !== '' ? ' hidden' : '' ?>><?= icon('user', 'icon--xl') ?></span>
                <?php else: ?>
                    <span class="mb-upload__icon"><?= icon('file-text', 'icon--xl') ?></span>
                <?php endif; ?>
            </span>
        </div>
        <div class="mb-upload__ctl">
            <input class="mb-upload__input" type="file" id="<?= e($id) ?>" name="<?= e((string) $name) ?>" accept="<?= e((string) $accept) ?>"
                   <?= !empty($required) ? 'required ' : '' ?><?= $err !== null ? 'aria-invalid="true" ' : '' ?>aria-describedby="<?= e(implode(' ', $ids)) ?>" data-upload-input>
            <label class="btn btn--outline mb-upload__pick" for="<?= e($id) ?>"><?= icon($isPhoto ? 'camera' : 'upload', 'icon--sm') ?> <span><?= e((string) $pick) ?></span></label>
            <?php if ($isPhoto && $camera !== ''): ?>
                <button class="mb-upload__camera" type="button" data-upload-camera hidden><?= icon('smartphone', 'icon--sm') ?> <span><?= e($camera) ?></span></button>
            <?php endif; ?>
            <p class="mb-upload__file" id="<?= e($id) ?>-status" data-upload-status role="status" aria-live="polite"></p>
            <?php if (!empty($remove)): ?><button class="mb-upload__remove" type="button" data-upload-clear hidden><?= icon('x', 'icon--sm') ?> <span><?= e((string) $remove) ?></span></button><?php endif; ?>
        </div>
    </div>
    <?php if ($help !== ''): ?><div class="field__help mb-upload__help" id="<?= e($id) ?>-help"><?= e($help) ?></div><?php endif; ?>
    <?= (string) ($notice ?? '') ?>
    <?php if ($err !== null): ?><div class="field__error" id="<?= e($id) ?>-error"><?= e($err) ?></div><?php endif; ?>
</div>
