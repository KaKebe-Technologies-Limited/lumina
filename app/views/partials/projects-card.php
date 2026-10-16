<?php
/**
 * Card of ONE planned intervention (projects row) for /projects and the related list of /projects/{slug}.
 * Text-only on purpose (the 27 interventions have no photographs). The title link is stretched over the whole card, so every card is
 * one tab stop; the "Read more" text is a visual cue only.
 * Vars: $project* (projects row), $pillar (programs row of its pillar: slug, title, icon, number; null = no pillar),
 *       $showPillar (default true: print the pillar chip; the detail page's related cards all share one pillar and turn it off),
 *       $statuses (status => editable label), $cta (link cue text, default content('projects','list.card_cta')), $tag (title tag, default h4).
 * All copy comes from the database or content('projects', ...).
 */
$pcHref = url('projects/' . $project['slug']);
$pcCta = trim((string) ($cta ?? content('projects', 'list.card_cta')));
$pcTag = in_array($tag ?? 'h4', ['h3', 'h4', 'h5'], true) ? ($tag ?? 'h4') : 'h4';
$pcStatus = (string) ($project['status'] ?? '');
$pcStatusText = trim((string) (($statuses ?? [])[$pcStatus] ?? ''));
$pcTone = ['planned' => 'info', 'ongoing' => 'success', 'completed' => 'neutral'][$pcStatus] ?? 'neutral';
$pcPillar = is_array($pillar ?? null) ? $pillar : null;
$pcIcon = $pcPillar !== null && !empty($pcPillar['icon']) ? safe_icon((string) $pcPillar['icon'], 'layers') : 'layers';
$pcSummary = trim((string) ($project['summary'] ?? ''));
$pcWhere = trim((string) ($project['location'] ?? ''));
$pcShowPillar = ($showPillar ?? true) && $pcPillar !== null;
?>
<article class="pi-card">
    <div class="pi-card__top">
        <span class="pi-card__icon" aria-hidden="true"><?= icon($pcIcon) ?></span>
        <?php if ($pcStatusText !== ''): ?><span class="badge badge--<?= e($pcTone) ?> pi-card__status"><?= e($pcStatusText) ?></span><?php endif; ?>
    </div>
    <<?= $pcTag ?> class="pi-card__title"><a class="pi-card__link" href="<?= e($pcHref) ?>"><?= e($project['title']) ?></a></<?= $pcTag ?>>
    <?php if ($pcSummary !== ''): ?><p class="pi-card__text"><?= e($pcSummary) ?></p><?php endif; ?>
    <?php if ($pcWhere !== ''): ?><p class="pi-card__where"><?= icon('map-pin', 'icon--sm') ?> <?= e($pcWhere) ?></p><?php endif; ?>
    <div class="pi-card__foot">
        <?php if ($pcShowPillar): ?><span class="pi-card__pillar"><?= e($pcPillar['title']) ?></span><?php endif; ?>
        <?php if ($pcCta !== ''): ?><span class="pi-card__more" aria-hidden="true"><?= e($pcCta) ?> <?= icon('arrow-right', 'icon--sm') ?></span><?php endif; ?>
    </div>
</article>
