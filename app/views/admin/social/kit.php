<?php
/**
 * Admin > Social media > Share kit of one issue. OWNER: IMAGES agent. Works without JavaScript (the Copy buttons and the live counters are added by assets/js/share-kit.js).
 * Vars: $issue (ShareHook::issueForShare), $row, $live, $captions (channel|tiktok => label, image, text, length, limit, over ...), $images (variant => preview, download, public, width, height, use),
 *       $tracked (platform => label, url), $url, $hashtags, $variants, $canRender, $countUrl, $designUrl.
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/share-kit.js')) ?>" defer></script>
<?php $this->end();
$names = ['square' => 'Square', 'portrait' => 'Portrait', 'landscape' => 'Landscape'];
$tagText = implode(' ', $hashtags);
$capIcon = static function (string $ch): string {
    return match ($ch) {
        'tiktok' => icon('tiktok', 'icon--sm'),
        'x' => icon('twitter', 'icon--sm'),
        'facebook', 'instagram', 'linkedin', 'telegram' => icon($ch, 'icon--sm'),
        default => icon('send', 'icon--sm'),
    };
};
?>
<div class="skit" data-skit data-count-url="<?= e($countUrl) ?>">
    <?= partial('partials/admin/page-header', ['title' => 'Share kit', 'subtitle' => $issue['title'], 'actions' => [
        ['label' => 'Back to the issue', 'url' => url('admin/newsletters/' . (int) $issue['id'] . '/edit'), 'class' => 'btn--outline', 'icon' => 'arrow-left'],
        ['label' => 'Share log', 'url' => url('admin/social/log?issue=' . (int) $issue['id']), 'class' => 'btn--outline', 'icon' => 'list'],
    ]]) ?>

    <p class="skit__intro">Post this issue by hand on any platform in a minute: copy the caption, download the picture, paste the link. This is how you post on <strong>TikTok</strong>: TikTok has no automatic channel (it only accepts video, or photo posts from apps it has audited, and it cannot carry a text and link newsletter).<?= $canRender ? ' Use the portrait picture there.' : ' Use the cover photo there.' ?></p>
    <?php if (!$live): ?>
        <div class="alert alert--warning" role="note"><?= icon('alert-triangle') ?><div class="alert__body">This issue is not public yet. The pictures below are a preview, and the links only work once the issue is published.</div></div>
    <?php endif; ?>
    <?php if (!$canRender): ?>
        <div class="alert alert--info" role="note"><?= icon('info') ?><div class="alert__body">This server cannot draw the branded pictures (the image library, GD with FreeType, is not switched on), so there are NO square, portrait or landscape versions: only the original cover photo of the issue (or the logo) is offered, exactly as it is, not resized or cropped. Ask your host to enable the PHP "gd" extension to get the branded pictures.</div></div>
    <?php endif; ?>

    <p class="visually-hidden" role="status" aria-live="polite" data-skit-status></p>

    <section class="card skit__sec" aria-labelledby="skit-pics">
        <div class="card__header"><h2 class="card__title" id="skit-pics"><?= icon('image', 'icon--sm') ?> Pictures</h2></div>
        <div class="card__body">
            <ul class="skit-pics<?= $canRender ? '' : ' skit-pics--single' ?>">
                <?php foreach ($canRender ? $images : array_slice($images, 0, 1, true) as $v => $im): ?>
                    <li class="skit-pic skit-pic--<?= $canRender ? e($v) : 'original' ?>">
                        <a class="skit-pic__frame" href="<?= e($im['preview']) ?>" target="_blank" rel="noopener" aria-label="Open the <?= $canRender ? e(strtolower($names[$v])) . ' picture' : 'cover photo' ?> in a new tab">
                            <img src="<?= e($im['preview']) ?>"<?= $canRender ? ' width="' . (int) $im['width'] . '" height="' . (int) $im['height'] . '"' : '' ?> loading="lazy" decoding="async" alt="<?= $canRender ? e($names[$v]) . ' share picture' : 'Cover photo' ?> for <?= e($issue['title']) ?>">
                        </a>
                        <div class="skit-pic__meta">
                            <?php if ($canRender): ?>
                            <h3 class="skit-pic__title"><?= e($names[$v]) ?> <span><?= (int) $im['width'] ?> &times; <?= (int) $im['height'] ?></span></h3>
                            <p class="skit-pic__use">Best for <?= e($im['use']) ?>.</p>
                            <?php else: ?>
                            <h3 class="skit-pic__title">Cover photo <span>original, not resized</span></h3>
                            <p class="skit-pic__use">Offered exactly as it is. Platforms may crop it or refuse it if its shape does not suit them.</p>
                            <?php endif; ?>
                            <p class="skit-pic__acts">
                                <a class="btn btn--primary btn--sm" href="<?= e($im['download']) ?>" download><?= icon('download', 'icon--sm') ?> Download</a>
                                <?php if ($im['public'] !== ''): ?>
                                    <button class="btn btn--outline btn--sm" type="button" data-skit-copy-value="<?= e($im['public']) ?>" data-skit-label="<?= e($names[$v]) ?> picture address"><?= icon('copy', 'icon--sm') ?> Copy picture address</button>
                                <?php endif; ?>
                            </p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p class="skit__hint"><?= $canRender ? 'The pictures are drawn from the issue: cover photo or brand background, title, summary, issue number and date.' : 'Pictures are offered as they are.' ?><?= $live && $canRender ? ' The picture address is public (an unguessable link that works only while the issue is published): paste it into Buffer, Zapier or any tool that asks for an image address.' : '' ?></p>
        </div>
    </section>

    <section class="card skit__sec" aria-labelledby="skit-link">
        <div class="card__header"><h2 class="card__title" id="skit-link"><?= icon('link', 'icon--sm') ?> Link</h2></div>
        <div class="card__body">
            <div class="skit-row">
                <label class="visually-hidden" for="skit-url">Link to the issue</label>
                <input class="input" id="skit-url" type="text" readonly value="<?= e($url) ?>">
                <button class="btn btn--primary btn--sm" type="button" data-skit-copy="skit-url" data-skit-label="Issue link"><?= icon('copy', 'icon--sm') ?> Copy link</button>
            </div>
            <details class="skit__tracked">
                <summary>Links with tracking tags (to see which platform sends visitors)</summary>
                <ul class="skit-links">
                    <?php foreach ($tracked as $k => $t): ?>
                        <li>
                            <label for="skit-t-<?= e($k) ?>"><?= e($t['label']) ?></label>
                            <input class="input" id="skit-t-<?= e($k) ?>" type="text" readonly value="<?= e($t['url']) ?>">
                            <button class="btn btn--outline btn--sm" type="button" data-skit-copy="skit-t-<?= e($k) ?>" data-skit-label="<?= e($t['label']) ?> link"><?= icon('copy', 'icon--sm') ?> Copy</button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </details>
        </div>
    </section>

    <section class="card skit__sec" aria-labelledby="skit-caps">
        <div class="card__header"><h2 class="card__title" id="skit-caps"><?= icon('message-circle', 'icon--sm') ?> Captions</h2></div>
        <div class="card__body">
            <p class="skit__hint">Edit a caption here if you like (nothing is saved: this page is only for copying). The counter follows each platform's own rule.</p>
            <?php foreach ($captions as $ch => $c): ?>
                <article class="skit-cap" aria-labelledby="skit-cap-h-<?= e($ch) ?>">
                    <header class="skit-cap__head">
                        <h3 class="skit-cap__title" id="skit-cap-h-<?= e($ch) ?>"><?= $capIcon($ch) ?> <?= e($c['label']) ?></h3>
                        <span class="char-count<?= !empty($c['over']) ? ' is-over' : '' ?>" id="skit-count-<?= e($ch) ?>" data-skit-count-for="skit-cap-<?= e($ch) ?>"><?= (int) $c['length'] ?> / <?= (int) $c['limit'] ?></span>
                    </header>
                    <label class="visually-hidden" for="skit-cap-<?= e($ch) ?>">Caption for <?= e($c['label']) ?></label>
                    <textarea class="textarea" id="skit-cap-<?= e($ch) ?>" rows="<?= $ch === 'x' ? 5 : 8 ?>" data-skit-channel="<?= e($ch) ?>" data-skit-limit="<?= (int) $c['limit'] ?>" data-skit-name="<?= e($c['label']) ?>" aria-describedby="skit-count-<?= e($ch) ?>"<?= !empty($c['over']) ? ' aria-invalid="true"' : '' ?>><?= e($c['text']) ?></textarea>
                    <div class="skit-cap__acts">
                        <button class="btn btn--primary btn--sm" type="button" data-skit-copy="skit-cap-<?= e($ch) ?>" data-skit-label="<?= e($c['label']) ?> caption"><?= icon('copy', 'icon--sm') ?> Copy caption</button>
                        <a class="btn btn--outline btn--sm" href="<?= e($images[$c['image']]['download']) ?>" download><?= icon('download', 'icon--sm') ?> <?= $canRender ? e($names[$c['image']]) . ' picture' : 'Cover photo' ?></a>
                    </div>
                    <?php $hint = $ch === 'tiktok' ? 'Open TikTok, make a photo post or a video with ' . ($canRender ? 'the portrait picture' : 'the cover photo') . ', paste the caption, and put the link in your profile.' : ($ch === 'instagram' ? 'Links in captions are not clickable on Instagram: put the link in your bio.' : ($ch === 'x' ? 'Every link counts as 23 characters on X.' : '')); ?>
                    <?php if ($hint !== ''): ?><p class="skit-cap__hint"><?= e($hint) ?></p><?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="card skit__sec" aria-labelledby="skit-tags">
        <div class="card__header"><h2 class="card__title" id="skit-tags"><?= icon('tag', 'icon--sm') ?> Hashtags</h2></div>
        <div class="card__body">
            <?php if ($hashtags !== []): ?>
                <ul class="skit-tags"><?php foreach ($hashtags as $tag): ?><li><?= e($tag) ?></li><?php endforeach; ?></ul>
                <label class="visually-hidden" for="skit-hashtags">All hashtags</label>
                <input class="input" id="skit-hashtags" type="text" readonly value="<?= e($tagText) ?>">
                <p><button class="btn btn--outline btn--sm" type="button" data-skit-copy="skit-hashtags" data-skit-label="Hashtags"><?= icon('copy', 'icon--sm') ?> Copy hashtags</button></p>
            <?php else: ?>
                <p>No default hashtags are set.<?php if ($designUrl !== ''): ?> <a href="<?= e(url('admin/social/channels')) ?>">Set them in the channel settings.</a><?php endif; ?></p>
            <?php endif; ?>
        </div>
    </section>
</div>
