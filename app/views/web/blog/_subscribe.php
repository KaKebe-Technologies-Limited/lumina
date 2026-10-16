<?php
/**
 * Newsletter sign-up box (posts to /subscribe). Copy is passed in from the page's registry fields - nothing is hardcoded here.
 * Vars: $id (unique html id prefix), $title, $text, $placeholder, $button, $source (where the visitor signed up), $class (optional wrapper class).
 */
$id = (string) ($id ?? 'subscribe');
?>
<div class="subscribe-box<?= !empty($class) ? ' ' . e($class) : '' ?>" data-reveal>
    <div class="subscribe-box__copy">
        <?php if (trim((string) $title) !== ''): ?><h2 class="subscribe-box__title" id="<?= e($id) ?>-title"><?= e($title) ?></h2><?php endif; ?>
        <?php if (trim((string) $text) !== ''): ?><p class="subscribe-box__text"><?= e($text) ?></p><?php endif; ?>
    </div>
    <form class="subscribe-box__form" method="post" action="<?= e(url('subscribe')) ?>" novalidate<?= trim((string) $title) !== '' ? ' aria-labelledby="' . e($id) . '-title"' : '' ?>>
        <?= csrf_field() ?>
        <?= honeypot_field() ?>
        <input type="hidden" name="source" value="<?= e($source ?? 'website') ?>">
        <label class="visually-hidden" for="<?= e($id) ?>-email"><?= e($placeholder) ?></label>
        <input class="input" id="<?= e($id) ?>-email" type="email" name="email" autocomplete="email" required maxlength="190" placeholder="<?= e($placeholder) ?>">
        <button class="btn btn--accent" type="submit"><?= e($button) ?></button>
    </form>
</div>
