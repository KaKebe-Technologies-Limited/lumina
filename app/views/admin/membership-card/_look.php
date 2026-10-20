<?php
/**
 * Designer tab "Logo & colours". OWNER: CARD agent. Vars: see index.php ($values, $defs, $f, $themes, $contrast, $logoIsDefault, $defaults, $tab).
 * Each colour is a swatch (the browser's colour picker) AND a hex text field (the field that is posted; it also works without JavaScript and on a keyboard);
 * the contrast list below recomputes while a colour is typed (assets/js/admin-membership-card.js) and the server refuses an unreadable combination on save.
 */
$colors = [
    'navy'   => ['membership_card_navy', 'Side panel', 'The big panel on the right. It is also the colour of the member\'s name, the QR code and the values on the tiles.'],
    'accent' => ['membership_card_accent', 'Accent', 'The bar on top of the side panel, the photo block, the line under the name and the membership type.'],
    'gold'   => ['membership_card_gold', 'Tagline', 'The tagline under the organisation name.'],
    'green'  => ['membership_card_green', 'Active status', 'The "Active member" pill. An expired card is grey and a suspended or revoked card is red, whatever you choose here.'],
    'tile'   => ['membership_card_tile', 'Information tiles', 'The background of the three tiles.'],
    'label'  => ['membership_card_label_color', 'Small labels', 'The small grey labels and the footer lines.'],
    'muted'  => ['membership_card_muted', 'Side panel small text', 'Labels and small print on the side panel.'],
];
$statusOf = static function (string $short) use ($contrast): string {
    $worst = 'ok';
    foreach ($contrast as $r) {
        if (in_array($short, $r['keys'], true)) {
            $worst = $r['status'] === 'fail' || $worst === 'fail' ? 'fail' : ($r['status'] === 'warn' || $worst === 'warn' ? 'warn' : 'ok');
        }
    }
    return $worst;
};
$stateText = ['ok' => 'Easy to read', 'warn' => 'Below the advised contrast for small text', 'fail' => 'Unreadable'];
$summary = ['ok' => 0, 'warn' => 0, 'fail' => 0];
foreach ($contrast as $r) {
    $summary[$r['status']]++;
}
$hex = static function (string $v): string {
    $h = ltrim(trim($v), '#');
    if (strlen($h) === 3 && ctype_xdigit($h)) {
        $h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
    }
    return strlen($h) === 6 && ctype_xdigit($h) ? '#' . strtoupper($h) : '#000000';
};
?>
<section class="card mc-panel" id="tab-look" role="tabpanel" aria-labelledby="mc-tab-look" data-mc-panel="look">
    <?= partial('admin/membership-card/_panel_head', ['key' => 'look', 'tab' => $tab]) ?>
    <div class="card__body">

        <section class="form-section" aria-labelledby="mc-sec-logo">
            <h3 class="form-section__title" id="mc-sec-logo">Logo</h3>
            <p class="form-section__help">The picture at the top left of the card and in the e-mail. A square or round logo works best. When you save, it is trimmed, made the right size and cleaned of hidden data.</p>
            <div class="mc-logo">
                <?= partial('partials/admin/image-field', [
                    'name' => 'membership_card_logo', 'label' => 'Your logo', 'value' => (string) ($values['membership_card_logo'] ?? ''),
                    'help' => 'PNG (a transparent background is best), JPG or WebP, up to 5 MB. Remove it to go back to the bundled Lumina seal.',
                ]) ?>
                <figure class="mc-logo__default">
                    <img src="<?= e(asset('img/brand/card-logo.jpg')) ?>" alt="" width="64" height="64" loading="lazy">
                    <figcaption><?= $logoIsDefault ? 'No logo of your own yet: the card uses this bundled Lumina seal.' : 'The bundled Lumina seal, used when there is no logo of your own.' ?></figcaption>
                </figure>
            </div>
        </section>

        <section class="form-section" aria-labelledby="mc-sec-themes">
            <h3 class="form-section__title" id="mc-sec-themes">Colour themes</h3>
            <p class="form-section__help">A quick start: choose one, then adjust any colour below. The list "Is the text easy to read?" shows how each combination reads.</p>
            <div class="mc-themes" role="group" aria-label="Colour themes">
                <?php foreach ($themes as $tk => $th): ?>
                    <button type="button" class="mc-theme" data-mc-theme="<?= e((string) json_encode(array_diff_key($th, ['name' => 1]))) ?>" aria-label="Use the <?= e($th['name']) ?> colours">
                        <span class="mc-theme__swatches" aria-hidden="true">
                            <i style="background: <?= e($th['navy']) ?>"></i><i style="background: <?= e($th['accent']) ?>"></i><i style="background: <?= e($th['gold']) ?>"></i><i style="background: <?= e($th['green']) ?>"></i>
                        </span>
                        <span class="mc-theme__name"><?= e($th['name']) ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="form-section" aria-labelledby="mc-sec-colors">
            <h3 class="form-section__title" id="mc-sec-colors">Colours</h3>
            <p class="form-section__help">Click the square to pick a colour, or type it as a hex code such as <code>#0F1B33</code>.</p>
            <div class="mc-colors">
                <?php foreach ($colors as $short => [$key, $title, $use]):
                    $val = (string) ($values[$key] ?? '');
                    $status = $statusOf($short);
                    $default = (string) ($defaults[$key] ?? '');
                    $err = error($key); ?>
                    <div class="field mc-colorfield<?= $err ? ' field--error' : '' ?>" data-mc-colorfield="<?= e($short) ?>">
                        <label class="field__label" for="f-<?= e($key) ?>"><?= e($title) ?></label>
                        <div class="mc-color">
                            <input class="mc-color__swatch" type="color" value="<?= e($hex($val)) ?>" aria-label="Pick the colour: <?= e($title) ?>" data-mc-swatch>
                            <input class="input input--code mc-color__hex" type="text" id="f-<?= e($key) ?>" name="<?= e($key) ?>" value="<?= e($val) ?>" maxlength="7" size="9" autocomplete="off" spellcheck="false"
                                   placeholder="<?= e($default) ?>" data-mc-hex="<?= e($short) ?>" data-mc-default="<?= e($default) ?>" aria-describedby="f-<?= e($key) ?>-help f-<?= e($key) ?>-state<?= $err ? ' f-' . e($key) . '-error' : '' ?>"<?= $err ? ' aria-invalid="true"' : '' ?>>
                            <button class="btn btn--ghost btn--sm mc-color__reset" type="button" data-mc-color-reset title="Back to the reference colour (<?= e($default) ?>)" aria-label="Back to the reference colour for <?= e($title) ?>"><?= icon('refresh', 'icon--sm') ?></button>
                        </div>
                        <div class="field__help" id="f-<?= e($key) ?>-help"><?= e($use) ?></div>
                        <div class="mc-color__state is-<?= e($status) ?>" id="f-<?= e($key) ?>-state" data-mc-colorstate="<?= e($short) ?>"><?= icon($status === 'ok' ? 'check-circle' : 'alert-triangle', 'icon--sm') ?> <span><?= e($stateText[$status]) ?></span></div>
                        <?php if ($err): ?><div class="field__error" id="f-<?= e($key) ?>-error" role="alert"><?= e($err) ?></div><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="form-section" aria-labelledby="mc-sec-contrast">
            <h3 class="form-section__title" id="mc-sec-contrast">Is the text easy to read?</h3>
            <p class="form-section__help">Each line shows one piece of text on its background. A ratio of 4.5 or more reads well for small text. Between 3 and 4.5 the text is only comfortable when it is large or decorative; the reference design of the sample card uses a few such colours on purpose. A combination marked in red cannot be saved, because nobody could read it.</p>
            <p class="mc-checks__summary is-<?= $summary['fail'] > 0 ? 'fail' : ($summary['warn'] > 0 ? 'warn' : 'ok') ?>" data-mc-checks-summary role="status" aria-live="polite">
                <?php if ($summary['fail'] > 0): ?><?= icon('alert-circle', 'icon--sm') ?> <?= (int) $summary['fail'] ?> combination<?= $summary['fail'] === 1 ? '' : 's' ?> cannot be read at all: change a colour before saving.
                <?php elseif ($summary['warn'] > 0): ?><?= icon('alert-triangle', 'icon--sm') ?> <?= (int) $summary['warn'] ?> combination<?= $summary['warn'] === 1 ? ' is' : 's are' ?> below the advised contrast for small text (see the list).
                <?php else: ?><?= icon('check-circle', 'icon--sm') ?> Every combination meets the advised contrast.<?php endif; ?>
            </p>
            <ul class="mc-checks" data-mc-checks>
                <?php foreach ($contrast as $r): ?>
                    <li class="mc-check is-<?= e($r['status']) ?>" data-mc-check="<?= e($r['id']) ?>">
                        <span class="mc-check__sample" style="color: <?= e($r['fg']) ?>; background: <?= e($r['bg']) ?>" data-mc-sample aria-hidden="true">Aa</span>
                        <span class="mc-check__what"><?= e($r['what']) ?></span>
                        <span class="mc-check__ratio"><strong data-mc-ratio><?= e(number_format((float) $r['ratio'], 1)) ?></strong>:1 <span class="mc-check__state" data-mc-checkstate><?= $r['status'] === 'ok' ? 'good' : ($r['status'] === 'warn' ? ((float) $r['ratio'] >= 3.0 ? 'large text only' : 'hard to read') : 'unreadable') ?></span></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </div>
</section>
