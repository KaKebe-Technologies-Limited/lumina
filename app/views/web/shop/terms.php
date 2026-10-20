<?php
/**
 * Terms of sale, delivery and returns. OWNER: STOREFRONT. Vars: $docs = [key => [title, text]]. The text is plain (typed in Admin > Shop > Settings or the Shop page
 * wording); organisation tokens such as {site_name} are filled in here, then it is escaped and shown as paragraphs.
 */
use App\Services\Web\TextTokens;

?>
<?= partial('partials/page-hero', ['title' => (string) content('shop', 'policy.terms_title'), 'eyebrow' => (string) content('shop', 'hero.eyebrow'), 'sheet' => 'white']) ?>

<section class="section sh-terms">
    <div class="container container--narrow">
        <nav class="sh-terms__nav" aria-label="<?= e(content('shop', 'policy.terms_title')) ?>">
            <ul class="pills">
                <?php foreach ($docs as $key => $d): if (trim($d['text']) === '') { continue; } ?>
                    <li><a class="pills__item" href="#<?= e($key) ?>"><?= e($d['title']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <?php foreach ($docs as $key => $d): if (trim($d['text']) === '') { continue; } ?>
            <article class="sh-terms__doc" id="<?= e($key) ?>">
                <h2><?= e($d['title']) ?></h2>
                <div class="prose"><?= nl2p(TextTokens::text($d['text'])) ?></div>
            </article>
        <?php endforeach; ?>
        <p><a class="link-arrow" href="<?= e(url('shop')) ?>"><?= icon('arrow-left', 'icon--sm') ?> <?= e(content('shop', 'product.back_label')) ?></a></p>
    </div>
</section>
