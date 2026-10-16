<?php
/**
 * Section wrapper, rendered by section_end(). Vars: $key, $opts (class, container, id, bleed, attrs, labelledby), $inner (HTML).
 * Do not include directly - use section_begin()/section_end() so section_on() visibility is honoured.
 */
$id = (string) ($opts['id'] ?? str_replace('_', '-', $key));
$class = trim('section ' . ($opts['class'] ?? ''));
$attrs = ['id' => $id !== '' ? $id : null, 'class' => $class, 'aria-labelledby' => $opts['labelledby'] ?? null] + (array) ($opts['attrs'] ?? []);
?>
<section<?= attrs($attrs) ?>>
    <?php if (!empty($opts['bleed'])): ?>
        <?= $inner ?>
    <?php else: ?>
        <div class="<?= e(trim('container ' . ($opts['container'] ?? ''))) ?>">
            <?= $inner ?>
        </div>
    <?php endif; ?>
</section>
