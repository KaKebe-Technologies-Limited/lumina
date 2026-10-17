<?php
/**
 * The subscribe box of the newsletter pages (posts to the existing POST /subscribe; source "newsletter"). Wording: content('newsletter', 'subscribe.*').
 * Reuses the blog's subscribe panel markup. No vars.
 */
?>
<section class="section nl-subscribe" aria-label="<?= e(strip_highlight((string) content('newsletter', 'subscribe.title'))) ?>">
    <div class="container">
        <?= partial('web/blog/_subscribe', [
            'id'          => 'nl-subscribe',
            'eyebrow'     => content('newsletter', 'subscribe.eyebrow'),
            'title'       => content('newsletter', 'subscribe.title'),
            'text'        => content('newsletter', 'subscribe.text'),
            'placeholder' => content('newsletter', 'subscribe.placeholder'),
            'button'      => content('newsletter', 'subscribe.button'),
            'source'      => 'newsletter',
        ]) ?>
        <?php if (trim((string) content('newsletter', 'subscribe.privacy_note')) !== ''): ?>
            <p class="nl-subscribe__note small text-muted"><?= e(content('newsletter', 'subscribe.privacy_note')) ?></p>
        <?php endif; ?>
    </div>
</section>
