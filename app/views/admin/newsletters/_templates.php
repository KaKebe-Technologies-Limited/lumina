<?php
/**
 * Newsletter editor: the HTML of one block card per block type, as <template> elements. assets/js/admin-newsletter.js clones a template,
 * replaces __ID__ with the block id and fills the fields from the issue's JSON. All user-visible words of the block editor live here.
 * Field hooks: data-f="heading|body_html|cite|button.label|button.url|button.style", [data-image], [data-link] (see _image.php, _link.php, _rich.php).
 */
$cardOpen = static function (string $type, string $label, string $icon, string $about): string {
    ob_start(); ?>
<template data-block-template="<?= e($type) ?>">
<article class="nl-bc is-open" data-block data-type="<?= e($type) ?>" data-block-id="__ID__" id="nl-b-__ID__" tabindex="-1" role="group" aria-labelledby="nl-b-__ID__-name">
    <header class="nl-bc__head">
        <span class="nl-grip" data-grip draggable="true" aria-hidden="true" title="Drag to move"><?= icon('grip') ?></span>
        <button type="button" class="nl-bc__toggle" data-collapse-toggle aria-expanded="true" aria-controls="nl-b-__ID__-body">
            <span class="nl-bc__badge nl-bc__badge--<?= e($type) ?>"><?= icon($icon, 'icon--sm') ?><span class="nl-bc__type"><?= e($label) ?></span></span>
            <span class="nl-bc__summary" id="nl-b-__ID__-name" data-summary><?= e($label) ?></span>
            <?= icon('chevron-down', 'icon--sm nl-bc__chev') ?>
        </button>
        <span class="nl-bc__tools" role="group" aria-label="Block actions">
            <button type="button" class="nl-tool" data-act="block-up" aria-label="Move this block up" title="Move up"><?= icon('arrow-up', 'icon--sm') ?></button>
            <button type="button" class="nl-tool" data-act="block-down" aria-label="Move this block down" title="Move down"><?= icon('arrow-down', 'icon--sm') ?></button>
            <button type="button" class="nl-tool" data-act="block-dup" aria-label="Duplicate this block" title="Duplicate"><?= icon('copy', 'icon--sm') ?></button>
            <button type="button" class="nl-tool nl-tool--danger" data-act="block-remove" aria-label="Remove this block" title="Remove"><?= icon('trash', 'icon--sm') ?></button>
        </span>
    </header>
    <div class="nl-bc__body" id="nl-b-__ID__-body">
        <p class="nl-bc__about"><?= e($about) ?></p>
<?php return (string) ob_get_clean();
};
$cardClose = '    </div>' . "\n" . '</article>' . "\n" . '</template>' . "\n";
?>

<?= $cardOpen('article', 'Article', 'file-text', 'A heading with text, and if you like a picture and a link.') ?>
        <div class="field">
            <label class="field__label" for="nl-b-__ID__-heading">Heading</label>
            <input class="input" type="text" id="nl-b-__ID__-heading" maxlength="200" autocomplete="off" data-f="heading" data-count="200" placeholder="A short, clear headline">
        </div>
        <?= partial('admin/newsletters/_rich', ['idp' => 'nl-b-__ID__-body-text', 'label' => 'Text', 'f' => 'body_html', 'placeholder' => 'Write the article here...']) ?>
        <div class="nl-optional">
            <h3 class="nl-optional__title">Picture <span>(optional)</span></h3>
            <?= partial('admin/newsletters/_image', ['idp' => 'nl-b-__ID__-img', 'label' => 'Picture', 'caption' => true]) ?>
        </div>
        <div class="nl-optional">
            <h3 class="nl-optional__title">Link at the end <span>(optional)</span></h3>
            <?= partial('admin/newsletters/_link', ['idp' => 'nl-b-__ID__-link', 'textLabel' => 'Link text', 'urlLabel' => 'Link address']) ?>
        </div>
<?= $cardClose ?>

<?= $cardOpen('image', 'Image', 'image', 'A picture on its own, with an optional caption.') ?>
        <?= partial('admin/newsletters/_image', ['idp' => 'nl-b-__ID__-img', 'label' => 'Picture', 'caption' => true]) ?>
<?= $cardClose ?>

<?= $cardOpen('quote', 'Quote', 'quote', 'A highlighted quotation. The quotation marks are added for you.') ?>
        <?= partial('admin/newsletters/_rich', ['idp' => 'nl-b-__ID__-quote', 'label' => 'The quote', 'f' => 'body_html', 'placeholder' => 'Type or paste the quote...', 'help' => 'Only the words that were said. Keep it short.']) ?>
        <div class="field">
            <label class="field__label" for="nl-b-__ID__-cite">Who said it (optional)</label>
            <input class="input" type="text" id="nl-b-__ID__-cite" maxlength="160" autocomplete="off" data-f="cite" placeholder="Name and role">
        </div>
<?= $cardClose ?>

<?= $cardOpen('button', 'Button', 'arrow-up-right', 'A large button that sends readers to a page or a web address.') ?>
        <div class="nl-link__grid">
            <div class="field">
                <label class="field__label" for="nl-b-__ID__-btn-label">Button text</label>
                <input class="input" type="text" id="nl-b-__ID__-btn-label" maxlength="80" autocomplete="off" data-f="button.label" placeholder="What the button says">
            </div>
            <div class="field">
                <label class="field__label" for="nl-b-__ID__-btn-url">Link address</label>
                <input class="input" type="text" inputmode="url" id="nl-b-__ID__-btn-url" maxlength="2000" autocomplete="off" spellcheck="false" list="nl-pages" data-f="button.url" data-url placeholder="/involvement  or  https://..." aria-describedby="nl-b-__ID__-btn-help">
            </div>
        </div>
        <p class="field__help" id="nl-b-__ID__-btn-help">Start typing a page name (like /programs) or paste a full web address (https://...). A button needs both its text and its address.</p>
        <div class="field nl-field-narrow">
            <label class="field__label" for="nl-b-__ID__-btn-style">Look</label>
            <select class="select" id="nl-b-__ID__-btn-style" data-f="button.style">
                <option value="primary">Solid (stands out most)</option>
                <option value="outline">Outline (quieter)</option>
            </select>
        </div>
<?= $cardClose ?>

<?= $cardOpen('divider', 'Divider', 'minus', 'A thin line that separates two parts of the newsletter.') ?>
        <p class="field__help">Nothing to fill in. Move it where you want the line to appear.</p>
<?= $cardClose ?>
