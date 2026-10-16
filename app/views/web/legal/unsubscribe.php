<?php
/** Unsubscribe confirmation. Vars: $state ('ask' | 'done'), $token. Copy: content('site_newsletter', 'unsubscribe.*'). */
$ask = $state === 'ask';
?>
<section class="section legal-notice">
    <div class="container container--narrow">
        <div class="notice-card text-center">
            <span class="notice-card__icon"><?= icon($ask ? 'mail' : 'check-circle', 'icon--xl') ?></span>
            <h1 class="notice-card__title"><?= e(content('site_newsletter', $ask ? 'unsubscribe.title' : 'unsubscribe.done_title')) ?></h1>
            <p class="lead mt-4"><?= e(content('site_newsletter', $ask ? 'unsubscribe.text' : 'unsubscribe.done_text')) ?></p>

            <form class="notice-card__actions" method="post" action="<?= e(url('unsubscribe/' . $token)) ?>" novalidate>
                <?= csrf_field() ?>
                <?php if ($ask): ?>
                    <input type="hidden" name="action" value="unsubscribe">
                    <button class="btn btn--primary btn--lg" type="submit"><?= e(content('site_newsletter', 'unsubscribe.button')) ?></button>
                <?php else: ?>
                    <input type="hidden" name="action" value="resubscribe">
                    <button class="btn btn--outline" type="submit"><?= e(content('site_newsletter', 'unsubscribe.resubscribe_button')) ?></button>
                <?php endif; ?>
            </form>
            <p class="mt-5"><a class="link-arrow" href="<?= e(url('')) ?>"><?= icon('arrow-left', 'icon--arrow') ?> <?= e(content('site_newsletter', 'unsubscribe.home_label')) ?></a></p>
        </div>
    </div>
</section>
