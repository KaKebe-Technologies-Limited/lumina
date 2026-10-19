<?php
/**
 * A form embedded in a custom page by the [form slug="..."] shortcode (FormShortcode). Vars: $form (hydrated, published), $availability.
 * Open forms render inline (posting to /forms/{slug}; on success the visitor lands on the thank-you page); a closed, full or not-yet-open form shows
 * its friendly notice instead. forms.js is added once per page, however many forms are embedded.
 */
use App\Services\Forms\FormText;

$cfg = (array) $form['cfg'];
$state = (string) $availability['state'];
$accent = in_array((string) ($cfg['theme']['accent'] ?? 'orange'), ['orange', 'navy', 'teal', 'gold'], true) ? (string) $cfg['theme']['accent'] : 'orange';
$vars = ['title' => (string) $form['title'], 'opens' => !empty($availability['opens_at']) ? format_datetime($availability['opens_at']) : '', 'closes' => !empty($availability['closes_at']) ? format_datetime($availability['closes_at']) : ''];
?>
<div class="lf-embed theme-<?= e($accent) ?>" data-lf-embed="<?= e((string) $form['slug']) ?>" data-no-justify>
    <?php if ($state === 'open'): ?>
        <h2 class="lf-embed__title"><?= e((string) $form['title']) ?></h2>
        <?php if (trim((string) ($cfg['intro'] ?? '')) !== ''): ?><div class="lf-intro prose"><?= rich((string) $cfg['intro']) ?></div><?php endif; ?>
        <?= partial('web/forms/_form', ['form' => $form, 'input' => [], 'errors' => [], 'context' => null, 'mode' => 'embed']) ?>
        <?php if (empty($GLOBALS['lf_script_done'])): $GLOBALS['lf_script_done'] = true; ?><script src="<?= e(asset('js/forms.js')) ?>" defer></script><?php endif; ?>
    <?php else:
        $key = $state === 'full' ? ['full_title', 'full_text', 'full_message', 'users'] : ($state === 'not_open' ? ['not_open_title', 'not_open_text', 'not_open_message', 'clock'] : ['closed_title', 'closed_text', 'closed_message', 'lock']);
        $own = trim((string) ($cfg[$key[2]] ?? ''));
        ?>
        <div class="lf-card--notice" role="status">
            <span class="lf-mark" aria-hidden="true"><?= icon($key[3]) ?></span>
            <h2 class="lf-notice__title"><?= e(FormText::get('state.' . $key[0])) ?></h2>
            <p class="lf-notice__text"><?= nl2br(e($own !== '' ? FormText::fill($own, $vars) : FormText::get('state.' . $key[1], $vars))) ?></p>
        </div>
    <?php endif; ?>
</div>
