<?php
/**
 * Video button that opens an accessible YouTube dialog (assets/js/main.js [data-video-lightbox]). Renders NOTHING when $url is empty
 * or not a YouTube address. Without JavaScript it is a normal link to YouTube; with JavaScript nothing is requested from YouTube until
 * the visitor clicks, and then only youtube-nocookie.com is used.
 * Vars: $url* (any youtube.com / youtu.be link or a bare 11-character id), $label (default content('global','labels.watch_video')),
 * $title (dialog + screen-reader title), $class (default 'btn btn--photo btn--caps'; use 'btn btn--white btn--sm btn--caps' on bands).
 *   <?= partial('partials/video-button', ['url' => content('home','hero.video_url'), 'label' => content('home','hero.video_label'), 'title' => content('home','hero.video_title')]) ?>
 */
$vbId = youtube_id((string) ($url ?? ''));
if ($vbId === '') {
    return;
}
$vbLabel = trim((string) ($label ?? '')) !== '' ? (string) $label : (string) content('global', 'labels.watch_video');
$vbTitle = trim((string) ($title ?? '')) !== '' ? (string) $title : $vbLabel;
$vbClass = trim((string) ($class ?? '')) !== '' ? (string) $class : 'btn btn--photo btn--caps';
?>
<a class="<?= e($vbClass) ?>" href="https://www.youtube.com/watch?v=<?= e($vbId) ?>" target="_blank" rel="noopener noreferrer" aria-haspopup="dialog"
   data-video-lightbox data-video-id="<?= e($vbId) ?>" data-video-title="<?= e($vbTitle) ?>"
   data-close-label="<?= e((string) content('global', 'labels.close')) ?>" data-default-title="<?= e((string) content('global', 'labels.video_fallback_title')) ?>" data-fallback-label="<?= e((string) content('global', 'labels.watch_on_youtube')) ?>"><span class="btn__play"><?= icon('play') ?></span><span><?= e($vbLabel) ?></span></a>
