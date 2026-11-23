<?php
/**
 * Privacy policy. Vars: $items (rows: title, anchor, body - already token-filled and sanitised), $cols (5 column headings), $rows (the "what we collect" table), $points ("in brief" lines).
 * Everything is editable: content('privacy', ...). The table is placed after the section whose anchor is "collect" (or at the end when there is none).
 */

use App\Services\Web\TextTokens;

$updated = trim((string) content('privacy', 'notice.updated'));
$heroImg = content_img('privacy', 'hero.image');
$hasCollect = false;
foreach ($items as $it) {
    if ($it['anchor'] === 'collect') {
        $hasCollect = true;
    }
}
$table = static function () use ($rows, $cols): void {
    if (!$rows) {
        return;
    } ?>
    <div class="privacy-table-wrap" role="region" aria-label="<?= e((string) content('privacy', 'collect.table_label', 'What we collect, activity by activity')) ?>" tabindex="0">
        <table class="privacy-table">
            <caption class="visually-hidden">What we collect, why, our legal reason, who receives it, and how long we aim to keep it, activity by activity</caption>
            <thead><tr><th scope="col">Activity</th><?php foreach (array_slice($cols, 0, 5) as $c): ?><th scope="col"><?= e($c) ?></th><?php endforeach; ?></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr<?= $r['anchor'] !== '' ? ' id="' . e($r['anchor']) . '"' : '' ?>>
                    <th scope="row"><?= e((string) $r['activity']) ?></th>
                    <td data-label="<?= e($cols[0]) ?>"><?= e((string) ($r['data'] ?? '')) ?></td>
                    <td data-label="<?= e($cols[1]) ?>"><?= e((string) ($r['purpose'] ?? '')) ?></td>
                    <td data-label="<?= e($cols[2]) ?>"><?= e((string) ($r['basis'] ?? '')) ?></td>
                    <td data-label="<?= e($cols[3]) ?>"><?= e((string) ($r['who'] ?? '')) ?></td>
                    <td data-label="<?= e($cols[4]) ?>"><?= e((string) ($r['keep'] ?? '')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php };
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
                        <?php foreach ($items as $item): if (trim((string) $item['title']) === '') { continue; } ?>
                            <li><a href="#<?= e($item['anchor']) ?>"><?= e($item['title']) ?></a></li>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            <?php endif; ?>

            <div class="legal__body">
                <?php if ($updated !== ''): ?>
                    <p class="legal__updated"><?= icon('clock', 'icon--sm') ?> <?= e(content('privacy', 'notice.updated_label')) ?>: <time><?= e($updated) ?></time></p>
                <?php endif; ?>

                <?php if ($points): ?>
                    <aside class="privacy-brief" aria-labelledby="brief-title">
                        <h2 id="brief-title"><?= e(content('privacy', 'brief.title')) ?></h2>
                        <ul><?php foreach ($points as $p): ?><li><?= e(TextTokens::text($p)) ?></li><?php endforeach; ?></ul>
                        <?php if (trim((string) content('privacy', 'brief.cta_label')) !== ''): ?>
                            <p class="mt-4"><a class="btn btn--cta btn--caps" href="<?= e(url('privacy/request')) ?>"><?= e(content('privacy', 'brief.cta_label')) ?></a></p>
                        <?php endif; ?>
                    </aside>
                <?php endif; ?>

                <?php foreach ($items as $i => $item): ?>
                    <section class="legal__section" id="<?= e($item['anchor']) ?>"<?= trim((string) $item['title']) !== '' ? ' aria-labelledby="' . e($item['anchor']) . '-title"' : '' ?>>
                        <?php if (trim((string) $item['title']) !== ''): ?>
                            <h2 id="<?= e($item['anchor']) ?>-title"><span class="legal__num"><?= (int) $i + 1 ?></span> <?= e($item['title']) ?></h2>
                        <?php endif; ?>
                        <div class="prose"><?= $item['body'] ?></div>
                        <?php if ($item['anchor'] === 'collect') { $table(); } ?>
                    </section>
                <?php endforeach; ?>
                <?php if (!$hasCollect && $rows): ?>
                    <section class="legal__section" id="collect-table"><?php $table(); ?></section>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
