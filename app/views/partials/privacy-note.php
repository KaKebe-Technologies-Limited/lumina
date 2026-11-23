<?php
/**
 * "Why we ask for this" note shown beside a form that collects personal data (see App\Services\Privacy\Notices).
 * Vars: $key (activity key, e.g. 'contact'), $class (optional extra classes), $text (optional replacement text).
 */
use App\Services\Privacy\Notices;

$key = (string) ($key ?? 'forms');
$text = isset($text) && trim((string) $text) !== '' ? (string) $text : Notices::text($key);
?>
<aside class="privacy-note<?= !empty($class) ? ' ' . e($class) : '' ?>" aria-label="<?= e((string) content('privacy', 'notes.heading', 'Why we ask for this')) ?>">
    <strong class="privacy-note__title"><?= icon('shield', 'icon--sm') ?> <?= e((string) content('privacy', 'notes.heading', 'Why we ask for this')) ?></strong>
    <span class="privacy-note__text"><?= e($text) ?></span>
    <a class="privacy-note__link" href="<?= e(Notices::url($key)) ?>"><?= e((string) content('privacy', 'notes.link_label', 'How we use your information')) ?></a>
</aside>
