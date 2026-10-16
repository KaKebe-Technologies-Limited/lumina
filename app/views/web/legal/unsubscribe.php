<?php
/** Unsubscribe confirmation. Vars: $state ('ask' | 'done'), $token. Copy: content('site_newsletter', 'unsubscribe.*'). */
$ask = $state === 'ask';
?>
<section class="notice-hero notice-hero--unsubscribe" aria-labelledby="notice-title">
    <div class="notice-hero__top">
        <div class="container container--narrow">
            <span class="notice-hero__icon"><?= icon($ask ? 'mail' : 'check-circle', 'icon--xl') ?></span>
            <h1 class="notice-hero__title" id="notice-title"><?= e(content('site_newsletter', $ask ? 'unsubscribe.title' : 'unsubscribe.done_title')) ?></h1>
            <p class="notice-hero__text"><?= e(content('site_newsletter', $ask ? 'unsubscribe.text' : 'unsubscribe.done_text')) ?></p>
        </div>
    </div>
    <div class="notice-hero__body">
        <div class="container container--narrow">
            <form class="notice-hero__actions" method="post" action="<?= e(url('unsubscribe/' . $token)) ?>" novalidate>
                <?= csrf_field() ?>
                <?php if ($ask): ?>
                    <input type="hidden" name="action" value="unsubscribe">
                    <button class="btn btn--primary btn--caps btn--lg" type="submit"><?= e(content('site_newsletter', 'unsubscribe.button')) ?></button>
                <?php else: ?>
                    <input type="hidden" name="action" value="resubscribe">
                    <button class="btn btn--outline btn--caps btn--lg" type="submit"><?= e(content('site_newsletter', 'unsubscribe.resubscribe_button')) ?></button>
                <?php endif; ?>
                <a class="btn btn--ghost btn--caps btn--lg" href="<?= e(url('')) ?>"><?= icon('arrow-left', 'icon--sm') ?> <?= e(content('site_newsletter', 'unsubscribe.home_label')) ?></a>
            </form>
        </div>
    </div>
</section>
