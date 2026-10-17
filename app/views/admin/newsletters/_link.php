<?php
/**
 * Newsletter editor: a link made of a text and an address (featured article, article blocks, buttons).
 * Behaviour: assets/js/admin-newsletter.js ([data-link]). Vars: $idp, $textLabel, $urlLabel, $help, $textHelp, $max (label length, default 80).
 */
$idp = (string) ($idp ?? 'link');
?>
<div class="nl-link" data-link>
    <div class="nl-link__grid">
        <div class="field">
            <label class="field__label" for="<?= e($idp) ?>-label"><?= e($textLabel ?? 'Link text') ?></label>
            <input class="input" type="text" id="<?= e($idp) ?>-label" maxlength="<?= (int) ($max ?? 80) ?>" autocomplete="off" data-link-label placeholder="<?= e($textPlaceholder ?? 'Read more') ?>">
        </div>
        <div class="field">
            <label class="field__label" for="<?= e($idp) ?>-url"><?= e($urlLabel ?? 'Link address') ?></label>
            <input class="input" type="text" inputmode="url" id="<?= e($idp) ?>-url" maxlength="2000" autocomplete="off" spellcheck="false" list="nl-pages" data-link-url placeholder="/programs  or  https://..." aria-describedby="<?= e($idp) ?>-help">
        </div>
    </div>
    <p class="field__help" id="<?= e($idp) ?>-help"><?= e($help ?? 'Start typing a page name (like /programs) or paste a full web address (https://...). Leave both empty for no link.') ?></p>
</div>
