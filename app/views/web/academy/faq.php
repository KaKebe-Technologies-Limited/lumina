<?php
/** LAF Academy FAQs. Vars: $faqs (question, answer), $about (paragraphs), $learner. Questions are edited in Admin > FAQs (group "academy"). */
$this->start('head');
if ($faqs) {
    $ld = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(static fn(array $f): array => ['@type' => 'Question', 'name' => $f['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => trim(strip_tags($f['answer']))]], $faqs)];
    echo '<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) . '</script>';
}
$this->end();
?>
<?= partial('partials/page-hero', ['title' => 'Frequently asked questions', 'eyebrow' => 'LAF Academy', 'lead' => 'Everything you need to know about learning, certificates and support at the LAF Academy.']) ?>
<section class="section">
    <div class="container container--narrow">
        <?php if ($faqs): ?>
            <?= partial('partials/faq-split', ['faqs' => $faqs, 'prefix' => 'acfaq', 'first_open' => false]) ?>
        <?php else: ?>
            <p class="text-muted">The questions will appear here soon.</p>
        <?php endif; ?>
    </div>
</section>
<section class="section section--tint">
    <div class="container container--narrow">
        <div class="cta" data-reveal>
            <h2 class="cta__title">About LAF Academy</h2>
            <?php foreach ($about as $p): ?><p class="cta__text"><?= e($p) ?></p><?php endforeach; ?>
            <p class="cta__actions">
                <?php if ($learner === null): ?><a class="btn btn--cta btn--caps" href="<?= e(url('academy/register')) ?>">Create my learner account</a><?php endif; ?>
                <a class="btn btn--outline btn--caps" href="<?= e(url('academy')) ?>">Browse the courses</a>
                <a class="btn btn--ghost btn--caps" href="<?= e(url('academy/contact')) ?>">Ask the Academy team</a>
            </p>
        </div>
    </div>
</section>
