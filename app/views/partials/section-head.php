<?php
/**
 * Section heading block: small-caps eyebrow with accent underline + serif heading (+ lead). Vars (all text from content()):
 *  $title* (supports [[highlight]] markers), $eyebrow, $lead, $align ('left' default | 'center'), $tag ('h2' default | 'h1' | 'h3'),
 *  $id (id of the heading, for aria-labelledby), $class (extra classes, e.g. 'mb-0'), $actions (ready-made SAFE html shown on the right).
 *   <?= partial('partials/section-head', ['eyebrow' => content('home','help.eyebrow'), 'title' => content('home','help.title'), 'id' => 'help-title']) ?>
 */
$shTitle = trim((string) ($title ?? ''));
$shEyebrow = trim((string) ($eyebrow ?? ''));
$shLead = trim((string) ($lead ?? ''));
if ($shTitle === '' && $shEyebrow === '' && $shLead === '') {
    return;
}
$shTag = in_array($tag ?? 'h2', ['h1', 'h2', 'h3'], true) ? ($tag ?? 'h2') : 'h2';
$shCenter = ($align ?? 'left') === 'center';
$shActions = (string) ($actions ?? '');
$shClass = trim('section-head' . ($shCenter ? ' section-head--center' : '') . ($shActions !== '' ? ' section-head--split' : '') . ' ' . preg_replace('/[^a-z0-9_\- ]/i', '', (string) ($class ?? '')));
$shId = preg_replace('/[^a-z0-9_-]/i', '', (string) ($id ?? ''));
?>
<div class="<?= e($shClass) ?>">
    <div class="section-head__text">
        <?php if ($shEyebrow !== ''): ?><span class="eyebrow<?= $shCenter ? ' eyebrow--center' : '' ?>"><?= e($shEyebrow) ?></span><?php endif; ?>
        <?php if ($shTitle !== ''): ?><<?= $shTag ?> class="section-head__title"<?= $shId !== '' ? ' id="' . e($shId) . '"' : '' ?>><?= highlight($shTitle) ?></<?= $shTag ?>><?php endif; ?>
        <?php if ($shLead !== ''): ?><p class="section-head__lead"><?= e($shLead) ?></p><?php endif; ?>
    </div>
    <?php if ($shActions !== ''): ?><div class="section-head__actions"><?= $shActions ?></div><?php endif; ?>
</div>
