<?php
/**
 * /newsletter while nothing is published yet: friendly empty state + "when the first issue will appear" (editable) + the subscribe box.
 * noindex (set by the controller). All wording: content('newsletter', ...). OWNER: WEB / NEWSLETTER agent.
 */
$when = trim((string) content('newsletter', 'empty.when'));
?>
<?= partial('partials/page-hero', [
    'title'   => content('newsletter', 'hero.title'),
    'eyebrow' => content('newsletter', 'hero.eyebrow'),
    'lead'    => content('newsletter', 'hero.lead'),
]) ?>

<section class="section nl-empty">
    <div class="container container--narrow">
        <?= partial('partials/empty-state', [
            'title' => content('newsletter', 'empty.title'),
            'text'  => content('newsletter', 'empty.text'),
            'icon'  => 'newspaper',
        ]) ?>
        <?php if ($when !== ''): ?><p class="nl-empty__when" role="note"><?= icon('clock', 'icon--sm') ?> <span><?= e($when) ?></span></p><?php endif; ?>
    </div>
</section>

<?php if (section_on('newsletter', 'subscribe')): ?>
    <?= partial('web/newsletter/_subscribe') ?>
<?php endif; ?>
