<?php
/** The optional public list of forms (/forms). Vars: $forms (hydrated forms with `availability`). Cards pick up each form's accent colour. */
use App\Services\Forms\FormText;

$iconFor = ['job' => 'briefcase', 'intake' => 'hand-heart', 'survey' => 'message-circle', 'general' => 'file-text'];
?>
<?= partial('partials/page-hero', ['title' => content('forms', 'hero.title'), 'eyebrow' => content('forms', 'hero.eyebrow'), 'lead' => content('forms', 'hero.lead'), 'sheet' => 'tint']) ?>
<section class="section section--tint lf-section">
    <div class="container">
        <?php if ($forms === []): ?>
            <?= partial('partials/empty-state', [
                'icon'    => 'file-text',
                'title'   => content('forms', 'directory.empty_title'),
                'text'    => content('forms', 'directory.empty_text'),
                'actions' => [['label' => (string) content('forms', 'directory.empty_button'), 'url' => url('contact')]],
            ]) ?>
        <?php else: ?>
            <div class="grid grid--3 lf-directory">
                <?php foreach ($forms as $f): $st = $f['availability']['state']; $kind = (string) ($f['cfg']['kind'] ?? 'general'); ?>
                    <article class="card lf-directory__card is-form theme-<?= e((string) ($f['cfg']['theme']['accent'] ?? 'orange')) ?>">
                        <div class="card__body">
                            <span class="lf-directory__icon" aria-hidden="true"><?= icon($iconFor[$kind] ?? 'file-text') ?></span>
                            <h2 class="card__title"><?= e($f['title']) ?></h2>
                            <?php if (trim((string) ($f['description'] ?? '')) !== ''): ?><p class="card__text"><?= e(excerpt((string) $f['description'], 160)) ?></p><?php endif; ?>
                            <?php if ($st !== 'open'): ?><p><span class="badge badge--neutral"><?= e(content('forms', 'directory.' . ($st === 'full' ? 'full_badge' : ($st === 'not_open' ? 'soon_badge' : 'closed_badge')))) ?></span></p><?php endif; ?>
                        </div>
                        <div class="card__footer"><a class="btn btn--primary" href="<?= e(url('forms/' . $f['slug'])) ?>"><?= e(content('forms', 'directory.open_button')) ?> <?= icon('arrow-right', 'icon--sm') ?></a></div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
