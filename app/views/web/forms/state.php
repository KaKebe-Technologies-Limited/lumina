<?php
/**
 * The friendly notice shown INSTEAD of a form. Vars: $form, $state (closed | full | not_open | draft | archived | unavailable | job), $availability.
 * Wording: the form's own messages (Settings > Messages) or the editable defaults in Admin > Pages > Forms > "Form: closed, full and not-live notices".
 */
use App\Services\Forms\FormText;

$cfg = (array) $form['cfg'];
$map = [
    'closed'      => ['closed_title', 'closed_text', 'closed_message', 'lock'],
    'full'        => ['full_title', 'full_text', 'full_message', 'users'],
    'not_open'    => ['not_open_title', 'not_open_text', 'not_open_message', 'clock'],
    'draft'       => ['unavailable_title', 'unavailable_text', 'unavailable_message', 'info'],
    'archived'    => ['unavailable_title', 'unavailable_text', 'unavailable_message', 'info'],
    'unavailable' => ['unavailable_title', 'unavailable_text', 'unavailable_message', 'info'],
];
$vars = ['title' => (string) $form['title'], 'opens' => !empty($availability['opens_at']) ? format_datetime($availability['opens_at']) : '', 'closes' => !empty($availability['closes_at']) ? format_datetime($availability['closes_at']) : ''];
if ($state === 'job') {
    $title = FormText::get('state.unavailable_title');
    $text = FormText::get('job.closed_text');
    $icon = 'briefcase';
    $buttons = [['label' => FormText::get('job.careers_button'), 'url' => url('careers'), 'class' => 'btn--cta btn--caps']];
} else {
    [$tk, $xk, $ck, $icon] = $map[$state] ?? $map['unavailable'];
    $title = FormText::get('state.' . $tk);
    $own = trim((string) ($cfg[$ck] ?? ''));
    $text = $own !== '' ? FormText::fill($own, $vars) : FormText::get('state.' . $xk, $vars);
    $buttons = [
        ['label' => FormText::get('state.back_button'), 'url' => url(''), 'class' => 'btn--cta btn--caps'],
        ['label' => FormText::get('state.contact_button'), 'url' => url('contact'), 'class' => 'btn--outline btn--caps'],
    ];
}
?>
<?= partial('partials/page-hero', ['title' => (string) $form['title'], 'sheet' => 'tint']) ?>
<section class="section section--tint lf-section">
    <div class="container container--narrow">
        <div class="lf-card lf-card--notice" role="status">
            <span class="lf-mark" aria-hidden="true"><?= icon($icon) ?></span>
            <h2 class="lf-notice__title"><?= e($title) ?></h2>
            <p class="lf-notice__text"><?= nl2br(e($text)) ?></p>
            <p class="lf-notice__actions"><?php foreach ($buttons as $b): ?><a class="btn <?= e($b['class']) ?>" href="<?= e($b['url']) ?>"><?= e($b['label']) ?></a><?php endforeach; ?></p>
        </div>
    </div>
</section>
