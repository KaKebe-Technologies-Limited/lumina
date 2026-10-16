<?php
/**
 * Accountability framework block (B1). Shown on /programs and on every /programs/{slug}. Copy: content('programs', 'accountability.*').
 * Respects the section toggle (admin can hide it everywhere at once).
 */
section_begin('programs', 'accountability', ['class' => 'section--tint programs-accountability', 'labelledby' => 'programs-acc-title']);
$items = array_values(array_filter(content_list('programs', 'accountability.items'), static fn(array $r): bool => trim((string) ($r['text'] ?? '')) !== ''));
$closing = trim((string) content('programs', 'accountability.closing'));
?>
    <div class="programs-accountability__box">
        <div data-reveal>
            <h2 id="programs-acc-title"><?= e(content('programs', 'accountability.title')) ?></h2>
            <?php if ($closing !== ''): ?><p class="lead mt-4"><?= e($closing) ?></p><?php endif; ?>
        </div>
        <div class="programs-accountability__list" data-reveal>
            <?php if (trim((string) content('programs', 'accountability.intro')) !== ''): ?><p class="programs-accountability__intro"><?= e(content('programs', 'accountability.intro')) ?></p><?php endif; ?>
            <?php if ($items): ?>
                <ul class="checklist">
                    <?php foreach ($items as $it): ?><li><?= e($it['text']) ?></li><?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
<?php section_end(); ?>
