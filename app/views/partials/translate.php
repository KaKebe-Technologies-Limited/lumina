<?php
/**
 * Site-wide language switcher (floating button, bottom right above the back-to-top button). English is the written language of the site; the other languages are
 * produced in the visitor's browser by Google's website translator, which assets/js/translate.js loads only after a language is chosen.
 * The button is marked translate="no" so its own labels stay as written. Switch off with the setting translate_enabled = 0. No variables.
 */
if ((string) setting('translate_enabled', '1') !== '1') {
    return;
}
$trLangs = [
    'en'    => 'English',
    'sv'    => 'Svenska',
    'es'    => 'Español',
    'zh-CN' => '中文',
    'fr'    => 'Français',
    'ru'    => 'Русский',
];
?>
<div class="translate notranslate" translate="no" data-translate>
    <button class="translate__btn" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="translate-menu" data-translate-btn>
        <?= icon('globe') ?><span class="translate__current" data-translate-current>English</span>
    </button>
    <ul class="translate__menu" id="translate-menu" role="menu" hidden data-translate-menu>
<?php foreach ($trLangs as $code => $label): ?>
        <li role="none"><button type="button" role="menuitemradio" aria-checked="false" lang="<?= e($code) ?>" data-lang="<?= e($code) ?>"><?= e($label) ?></button></li>
<?php endforeach; ?>
    </ul>
</div>
<?php /* outside the notranslate wrapper: the translator does not start inside a notranslate element */ ?>
<div id="google_translate_element" class="translate__host" aria-hidden="true"></div>
