<?php
/**
 * FAQ accordion shared by the home/programs/involvement/donate views (B1). Vars: $faqs (list of ['question','answer']), $prefix (unique id prefix).
 * Answers are plain text or rich text; both are rendered safely by HomeSupport::answerHtml(). Behaviour: main.js [data-accordion].
 */

use App\Controllers\Web\HomeSupport;

if (empty($faqs)) {
    return;
}
$prefix = preg_replace('/[^a-z0-9_-]/i', '', (string) ($prefix ?? 'faq'));
?>
<div class="accordion" data-accordion="single">
    <?php foreach ($faqs as $i => $f): $pid = $prefix . '-' . ($i + 1); ?>
        <div class="accordion__item">
            <h3 class="accordion__heading">
                <button class="accordion__trigger" type="button" id="<?= e($pid) ?>-q" aria-expanded="false" aria-controls="<?= e($pid) ?>-a" data-accordion-trigger>
                    <span><?= e($f['question']) ?></span><?= icon('chevron-down') ?>
                </button>
            </h3>
            <div class="accordion__panel" id="<?= e($pid) ?>-a" role="region" aria-labelledby="<?= e($pid) ?>-q" data-accordion-panel hidden>
                <?= HomeSupport::answerHtml((string) $f['answer']) ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
