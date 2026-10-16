<?php
/**
 * Outlined feature card: round icon + serif title + one line. Vars: $title*, $text, $icon (icon name, see icon_options(); default 'check-circle'),
 * $href (makes the whole card a link), $tag ('h3' default), $class (extra modifiers: 'feature-card--row' icon left, 'feature-card--tint').
 * All text from content() / DB rows.
 *   <?php foreach (content_list('home', 'help.items') as $f): ?><?= partial('partials/feature-card', ['icon' => $f['icon'], 'title' => $f['title'], 'text' => $f['text']]) ?><?php endforeach; ?>
 */
$fcTitle = trim((string) ($title ?? ''));
if ($fcTitle === '') {
    return;
}
$fcTag = in_array($tag ?? 'h3', ['h2', 'h3', 'h4'], true) ? ($tag ?? 'h3') : 'h3';
$fcHref = trim((string) ($href ?? ''));
$fcClass = trim('feature-card ' . preg_replace('/[^a-z0-9_\- ]/i', '', (string) ($class ?? '')));
?>
<article class="<?= e($fcClass) ?>" data-reveal>
    <span class="feature-card__icon"><?= icon(safe_icon($icon ?? '')) ?></span>
    <div class="feature-card__body">
        <<?= $fcTag ?> class="feature-card__title"><?php if ($fcHref !== ''): ?><a href="<?= e($fcHref) ?>"><?= e($fcTitle) ?></a><?php else: ?><?= e($fcTitle) ?><?php endif; ?></<?= $fcTag ?>>
        <?php if (trim((string) ($text ?? '')) !== ''): ?><p class="feature-card__text"><?= e($text) ?></p><?php endif; ?>
    </div>
</article>
