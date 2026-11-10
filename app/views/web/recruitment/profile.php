<?php
/** Ambassador profile. Vars: $r (the application), $stage, $stageLabel, $stageText, $steps, $timeline, $pledge, $roles, $tone. */
use App\Models\AmbassadorApplication as Amb;

$first = explode(' ', trim((string) $r['name']))[0] ?: 'there';
?>
<?= partial('partials/page-hero', ['title' => 'Hello, ' . $first, 'eyebrow' => 'Your ambassador profile', 'lead' => 'Follow your application here. We also e-mail you at every stage.']) ?>
<section class="section ap">
    <div class="container container--narrow">
        <article class="card ap-status ap-status--<?= e($tone) ?>"><div class="card__body">
            <p class="ap-ref"><span><?= e((string) $r['ref_no']) ?></span> &middot; applied on <?= e(format_date($r['created_at'], 'j F Y')) ?></p>
            <h2 class="ap-title"><?= icon($tone === 'good' ? 'check-circle' : ($tone === 'bad' ? 'alert-circle' : 'clock'), 'icon--sm') ?> <?= e($stageLabel) ?></h2>
            <?php if ($stageText !== ''): ?><p><?= e($stageText) ?></p><?php endif; ?>
            <?php if ($steps): ?>
                <ol class="ap-steps">
                    <?php foreach ($steps as $i => $s): ?>
                        <li class="ap-step ap-step--<?= e($s['state']) ?>"><span class="ap-step__dot"><?= $s['state'] === 'done' ? icon('check', 'icon--sm') : $i + 1 ?></span><span><?= e($s['label']) ?></span></li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div></article>

        <?php if ($timeline): ?>
        <section class="card"><div class="card__body">
            <h2 class="ap-h">What has happened so far</h2>
            <ol class="ap-timeline">
                <?php foreach ($timeline as $t): ?><li><strong><?= e($t['label']) ?></strong><span class="text-muted"><?= e(format_datetime($t['when'], 'j F Y, H:i')) ?></span></li><?php endforeach; ?>
            </ol>
        </div></section>
        <?php endif; ?>

        <section class="card"><div class="card__body">
            <h2 class="ap-h">Your application</h2>
            <dl class="ap-dl">
                <dt>Name</dt><dd><?= e($r['name']) ?></dd>
                <dt>E-mail</dt><dd><?= e($r['email']) ?></dd>
                <dt>Where</dt><dd><?= e(($r['city'] ? $r['city'] . ', ' : '') . $r['country']) ?></dd>
                <?php if (!empty($r['availability'])): ?><dt>Time you can give</dt><dd><?= e(Amb::AVAILABILITY[$r['availability']] ?? $r['availability']) ?></dd><?php endif; ?>
            </dl>
            <?php if ($roles): ?><h3 class="ap-h3">You offered to help by</h3><ul class="ap-chips"><?php foreach ($roles as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
            <?php if ($pledge): ?><h3 class="ap-h3">Your yearly pledge</h3><ul class="ap-list"><?php foreach ($pledge as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul>
                <p class="text-muted">This is a good-faith intention that helps us plan. Nothing is agreed until we have spoken with you.</p><?php endif; ?>
        </div></section>

        <p class="text-muted ap-note">Keep this page's address to yourself: anyone who has it can see your application. Questions? <a href="<?= e(url('contact')) ?>">Write to us</a> and quote <strong><?= e((string) $r['ref_no']) ?></strong>.</p>
    </div>
</section>
