<?php
/**
 * Member screen: PHOTO (headshot) panel. OWNER: STAFF agent. Contract: docs/MEMBERSHIP_CARD.md (sections 4, 10, 11).
 * Vars: $member (row), $card (CardService::summary), $gd (bool: the server can crop and rotate pictures).
 * The picture is streamed by the controller (authenticated, never a file path). Staff can replace it, remove it, or frame it again with the cropper
 * (assets/js/admin-members-card.js: zoom, move and rotate in the browser, then the framed square is uploaded like a new file).
 */
use App\Services\Membership\Card\CardData;
use App\Services\Membership\Card\CardSettings;
use App\Services\Membership\MemberFiles;

$id = (int) $member['id'];
$erased = !empty($member['anonymised_at']);
$hasPhoto = MemberFiles::hasPhoto($member);
$when = static fn(?string $d): string => $d ? format_datetime($d, 'j M Y, H:i') : '';
$photoUrl = url('admin/members/' . $id . '/photo', ['v' => substr(md5((string) ($member['photo_updated_at'] ?? '') . (string) ($member['photo_path'] ?? '')), 0, 8)]);
$required = CardSettings::bool('membership_card_require_photo');
$stale = $card['state'] === 'issued' && $card['needs_reissue'] && in_array((string) $card['reissue_reason'], ['photo', 'photo_staff'], true);
?>
<section class="card mphoto" id="photo" aria-labelledby="photo-title">
    <div class="card__header">
        <h2 class="card__title" id="photo-title">Photo for the card</h2>
        <span class="badge badge--<?= $hasPhoto ? 'success' : ($required ? 'warning' : 'neutral') ?> badge--dot"><?= $hasPhoto ? 'Photo added' : ($required ? 'Photo needed' : 'No photo') ?></span>
    </div>
    <div class="card__body mfiles">
        <?php if ($erased): ?>
            <p class="text-muted">The personal data of this member was erased, together with the photo.</p>
        <?php else: ?>
            <div class="mphoto__grid">
                <div class="mphoto__frame">
                    <?php if ($hasPhoto): ?>
                        <img class="mphoto__img" src="<?= e($photoUrl) ?>" alt="Photo of <?= e($member['full_name']) ?>" width="160" height="160" data-photo-current>
                    <?php else: ?>
                        <div class="mphoto__initials" role="img" aria-label="No photo: the card shows the initials <?= e(CardData::initials((string) $member['full_name'])) ?>"><?= e(CardData::initials((string) $member['full_name'])) ?></div>
                    <?php endif; ?>
                </div>
                <div class="mphoto__side">
                    <?php if ($hasPhoto): ?>
                        <dl class="mfiles__meta">
                            <div><dt>Added or changed</dt><dd><?= e($when($member['photo_updated_at'] ?? null)) ?></dd></div>
                            <div><dt>Agreed to print it on the card</dt><dd><?= !empty($member['photo_consent_at']) ? 'Yes, ' . e($when($member['photo_consent_at'])) : 'Not recorded' ?></dd></div>
                        </dl>
                        <?php if (!empty($member['photo_original'])): ?>
                            <p class="mfiles__next"><?= icon('alert-triangle', 'icon--sm') ?> Stored as uploaded: this server could not crop it. Use "Frame the photo" to make it a square before the card is issued.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted"><?= $required ? 'The card settings require a photo before a card is issued.' : 'Without a photo the card shows the member\'s initials in the orange block, like the sample card.' ?> The member can add one from their private page, or you can upload it here.</p>
                    <?php endif; ?>
                    <?php if ($stale): ?>
                        <p class="mfiles__next mfiles__next--warn"><?= icon('alert-triangle', 'icon--sm') ?> This photo is newer than the one on the issued card. <a href="#card">Re-issue the card</a> (new version) to update it.</p>
                    <?php endif; ?>
                </div>
            </div>

            <form method="post" action="<?= e(url('admin/members/' . $id . '/photo')) ?>" enctype="multipart/form-data" class="mfiles__upload" data-photo-form>
                <?= csrf_field() ?>
                <div class="mfiles__row">
                    <div class="field">
                        <label class="field__label" for="staff-photo"><?= $hasPhoto ? 'Replace the photo' : 'Upload a photo' ?></label>
                        <input class="input" id="staff-photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" required aria-describedby="staff-photo-help" data-photo-input>
                        <div class="field__help" id="staff-photo-help">JPG, PNG or WebP, at least 300 x 300 pixels, 5 MB at most. It is cropped to a square (the face stays near the top)<?= $gd ? '' : '; this server has no image library, so crop it first or use "Frame the photo"' ?>. No consent tick is needed when staff upload it.</div>
                    </div>
                    <button class="btn btn--outline btn--sm" type="submit"><?= icon('upload', 'icon--sm') ?> <?= $hasPhoto ? 'Replace' : 'Upload' ?></button>
                </div>
                <div class="mphoto__preview" data-photo-preview hidden>
                    <img src="" alt="Preview of the chosen photo" width="96" height="96">
                    <span class="small text-muted" data-photo-preview-name></span>
                </div>
            </form>

            <div class="mfiles__upload mphoto__tools">
                <button class="btn btn--outline btn--sm" type="button" data-photo-frame hidden><?= icon('image', 'icon--sm') ?> Frame the photo</button>
                <?php if ($hasPhoto): ?>
                    <form method="post" action="<?= e(url('admin/members/' . $id . '/photo/remove')) ?>" data-confirm="Remove this photo? The card will show the initials."><?= csrf_field() ?>
                        <button class="btn btn--ghost btn--sm" type="submit"><?= icon('trash', 'icon--sm') ?> Remove the photo</button></form>
                <?php endif; ?>
            </div>

            <dialog class="mcrop" data-photo-dialog aria-labelledby="mcrop-title">
                <div class="mcrop__form">
                    <h3 id="mcrop-title">Frame the photo</h3>
                    <p class="small text-muted">Move the picture with the mouse or the arrow keys, zoom with the slider, turn it with the buttons. The square is what the card shows.</p>
                    <div class="mcrop__stage" data-crop-stage tabindex="0" role="application" aria-label="Photo framing area. Arrow keys move the picture, plus and minus zoom, R and L rotate.">
                        <canvas width="360" height="360" data-crop-canvas></canvas>
                    </div>
                    <div class="mcrop__controls">
                        <label class="mcrop__zoom"><span>Zoom</span> <input type="range" min="1" max="3" step="0.05" value="1" data-crop-zoom></label>
                        <button class="btn btn--outline btn--sm" type="button" data-crop-rotate="-1" aria-label="Rotate left"><span aria-hidden="true">&#8634;</span> Left</button>
                        <button class="btn btn--outline btn--sm" type="button" data-crop-rotate="1" aria-label="Rotate right"><span aria-hidden="true">&#8635;</span> Right</button>
                    </div>
                    <p class="small mcrop__error" role="alert" data-crop-error hidden></p>
                    <div class="mcrop__actions">
                        <button class="btn btn--primary btn--sm" type="button" data-crop-save>Use this framing</button>
                        <button class="btn btn--ghost btn--sm" type="button" data-crop-cancel>Cancel</button>
                    </div>
                </div>
            </dialog>
        <?php endif; ?>
    </div>
</section>
