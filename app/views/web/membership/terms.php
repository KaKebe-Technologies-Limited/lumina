<?php
/**
 * MEMBERSHIP TERMS (/membership/terms). OWNER: WEB agent. Copy: content('membership-terms', ...) (Admin > Pages > Membership terms).
 * The terms are the editable rich-text block terms.body (sanitised by content_html). The registry default is a PLACEHOLDER for the owner to replace.
 */
$updated = trim((string) content('membership-terms', 'terms.updated'));
$body = trim(content_html('membership-terms', 'terms.body'));
$open = \App\Services\Membership\MembershipService::isOpen();
?>
<?= partial('partials/page-hero', [
    'title'   => (string) content('membership-terms', 'hero.title'),
    'eyebrow' => (string) content('membership-terms', 'hero.eyebrow'),
    'lead'    => (string) content('membership-terms', 'hero.lead'),
]) ?>

<section class="section legal mb-terms">
    <div class="container container--narrow">
        <?php if ($updated !== ''): ?>
            <p class="mb-terms__updated"><?= icon('clock', 'icon--sm') ?> <?= e(content('membership-terms', 'terms.updated_label')) ?>: <time><?= e($updated) ?></time></p>
        <?php endif; ?>
        <div class="prose mb-terms__body"><?= $body ?></div>
        <div class="mb-terms__actions">
            <a class="link-arrow" href="<?= e(url('privacy')) ?>"><?= e(content('membership-terms', 'terms.privacy_label')) ?></a>
            <?php if ($open): ?><a class="btn btn--cta btn--caps" href="<?= e(url('membership/register')) ?>"><?= e(content('membership-terms', 'terms.back_label')) ?></a><?php endif; ?>
        </div>
    </div>
</section>
