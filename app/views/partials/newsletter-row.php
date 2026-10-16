<?php
/**
 * Newsletter sign-up row: heading + text on the left, e-mail field + Subscribe button on the right. Posts to POST /subscribe
 * (csrf + honeypot + throttle are handled by the route). Vars (all optional, default to the registry "Site-wide content" > Footer):
 * $title, $text, $placeholder, $button, $source (stored with the subscriber, default 'footer'), $idp (unique id prefix).
 */
$nlTitle = $title ?? (string) content('global', 'footer.newsletter_title');
$nlText = $text ?? (string) content('global', 'footer.newsletter_text');
$nlPlaceholder = $placeholder ?? (string) content('global', 'footer.newsletter_placeholder');
$nlButton = $button ?? (string) content('global', 'footer.newsletter_button');
$nlSource = preg_replace('/[^a-z0-9_-]/i', '', (string) ($source ?? 'footer')) ?: 'footer';
$nlId = preg_replace('/[^a-z0-9_-]/i', '', (string) ($idp ?? 'newsletter')) . '-email';
?>
<div class="newsletter-row">
    <div>
        <h2 class="newsletter-row__title"><?= e($nlTitle) ?></h2>
        <?php if (trim((string) $nlText) !== ''): ?><p class="newsletter-row__text"><?= e($nlText) ?></p><?php endif; ?>
    </div>
    <form class="newsletter-row__form" method="post" action="<?= e(url('subscribe')) ?>">
        <?= csrf_field() ?>
        <?= honeypot_field() ?>
        <input type="hidden" name="source" value="<?= e($nlSource) ?>">
        <label class="visually-hidden" for="<?= e($nlId) ?>"><?= e($nlPlaceholder) ?></label>
        <input class="input" id="<?= e($nlId) ?>" type="email" name="email" autocomplete="email" required maxlength="190" placeholder="<?= e($nlPlaceholder) ?>">
        <button class="btn btn--gold btn--caps" type="submit"><?= e($nlButton) ?></button>
    </form>
</div>
