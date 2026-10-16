<?php
/**
 * Privacy notice. Vars: $items (rows: title, body). Everything is editable: content('privacy', ...).
 * Organisation tokens ({site_name}, {email_link}, {phone_link}, {address}) are filled from Settings by TextTokens.
 */

use App\Services\Web\TextTokens;

$updated = trim((string) content('privacy', 'notice.updated'));
$heroImg = content_img('privacy', 'hero.image');
?>
<?= partial('partials/page-hero', [
    'title'   => content('privacy', 'hero.title'),
    'eyebrow' => content('privacy', 'hero.eyebrow'),
    'lead'    => TextTokens::text((string) content('privacy', 'hero.lead')),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 40%'] : null,
]) ?>

<section class="section legal">
    <div class="container">
        <div class="legal__layout">
            <?php if (count($items) > 1): ?>
                <nav class="legal__toc" aria-labelledby="toc-title">
                    <h2 class="legal__toc-title" id="toc-title"><?= e(content('privacy', 'notice.contents_title')) ?></h2>
                    <ol>
                        <?php foreach ($items as $i => $item): if (trim((string) $item['title']) === '') { continue; } ?>
                            <li><a href="#section-<?= (int) $i + 1 ?>"><?= e(TextTokens::text((string) $item['title'])) ?></a></li>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            <?php endif; ?>

            <div class="legal__body">
                <?php if ($updated !== ''): ?>
                    <p class="legal__updated"><?= icon('clock', 'icon--sm') ?> <?= e(content('privacy', 'notice.updated_label')) ?>: <time><?= e($updated) ?></time></p>
                <?php endif; ?>
                <?php foreach ($items as $i => $item): ?>
                    <section class="legal__section" id="section-<?= (int) $i + 1 ?>"<?= trim((string) $item['title']) !== '' ? ' aria-labelledby="section-' . ((int) $i + 1) . '-title"' : '' ?>>
                        <?php if (trim((string) $item['title']) !== ''): ?>
                            <h2 id="section-<?= (int) $i + 1 ?>-title"><span class="legal__num"><?= (int) $i + 1 ?></span> <?= e(TextTokens::text((string) $item['title'])) ?></h2>
                        <?php endif; ?>
                        <div class="prose"><?= TextTokens::html(rich((string) $item['body'])) ?></div>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
