<?php
/**
 * A public form page. Vars: $form, $availability, $values?, $errors, $context, $preview (staff looking at a form that is not live).
 * The hero carries the form title + description; the card holds the optional intro, the "closes soon" / "few places left" notes and the form itself.
 * Wording of the fixed parts: Admin > Pages > Forms (registry `forms`); what the form asks and its intro are written in Admin > Forms.
 */
use App\Services\Forms\FormText;

$cfg = (array) $form['cfg'];
$errors = $errors ?? [];
$input = $values ?? [];
$preview = !empty($preview);
$this->start('scripts'); ?>
<script src="<?= e(asset('js/forms.js')) ?>" defer></script>
<?php $this->end(); ?>

<?= partial('partials/page-hero', [
    'title'   => (string) $form['title'],
    'lead'    => (string) ($form['description'] ?? ''),
    'eyebrow' => $context !== null && !empty($context['label']) ? FormText::get('job.applying_for', ['title' => (string) $context['label']]) : '',
    'sheet'   => 'tint',
]) ?>

<section class="section section--tint lf-section" aria-label="<?= e((string) $form['title']) ?>">
    <div class="container container--narrow">
        <div class="lf-card">
            <?php if ($preview): ?>
                <p class="lf-banner" role="note"><?= e(FormText::get('ui.draft_banner')) ?></p>
            <?php endif; ?>
            <?php if (trim((string) ($cfg['intro'] ?? '')) !== ''): ?>
                <div class="lf-intro prose"><?= rich((string) $cfg['intro']) ?></div>
            <?php endif; ?>
            <?php
            $notes = [];
            if (!empty($availability['closes_at']) && ($availability['state'] ?? '') === 'open') {
                $notes[] = ['icon' => 'clock', 'text' => FormText::get('state.closes_note', ['closes' => format_datetime($availability['closes_at'])]), 'warn' => false];
            }
            if (isset($availability['remaining']) && $availability['remaining'] !== null && $availability['remaining'] > 0 && $availability['remaining'] <= 10) {
                $notes[] = ['icon' => 'users', 'text' => FormText::get('state.remaining_note', ['count' => (int) $availability['remaining']]), 'warn' => true];
            }
            ?>
            <?php if ($notes !== []): ?>
                <ul class="lf-notes">
                    <?php foreach ($notes as $n): ?><li class="lf-note<?= $n['warn'] ? ' lf-note--warn' : '' ?>"><?= icon($n['icon'], 'icon--sm') ?> <?= e($n['text']) ?></li><?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?= partial('web/forms/_form', [
                'form'    => $form,
                'input'   => $input,
                'errors'  => $errors,
                'context' => $context,
                'hpTs'    => $hpTs ?? null,
                'mode'    => $preview ? 'preview' : 'live',
            ]) ?>
        </div>
    </div>
</section>
