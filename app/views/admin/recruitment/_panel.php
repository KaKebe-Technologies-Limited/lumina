<?php
/**
 * Recruitment panel on the admin page of one application (ambassador, job or volunteer). Vars: $scope, $row (the application row).
 * Shows the tracking number, the portrait and CV, the KPI scoring (a click on 0-5 for each criterion) and the history of the stages.
 */
use App\Services\Recruitment\Recruitment;

$parts = $parts ?? ['files', 'judge', 'history'];
$cfg = Recruitment::SCOPES[$scope];
$id = (int) $row['id'];
$kpis = Recruitment::kpis($scope);
$points = Recruitment::scores($scope, $id);
$pct = $row['score_pct'] ?? null;
$level = Recruitment::threshold();
$history = Recruitment::history($scope, $id);
$hasPortrait = trim((string) ($row['photo_path'] ?? '')) !== '';
$hasCv = trim((string) ($row['cv_path'] ?? '')) !== '';
$this->start('scripts'); ?><script src="<?= e(asset('js/admin-recruitment.js')) ?>" defer></script><?php $this->end();
?>
<?php if (in_array('files', $parts, true)): ?>
<section class="card rc-panel">
    <div class="card__header"><h2 class="card__title">Application number</h2><span class="badge badge--outline rc-ref"><?= e((string) ($row['ref_no'] ?? '-')) ?></span></div>
    <div class="card__body">
        <div class="rc-files">
            <?php if ($hasPortrait): ?>
                <a href="<?= e(url('admin/recruitment/' . $scope . '/' . $id . '/portrait')) ?>" target="_blank" rel="noopener"><img class="rc-portrait" src="<?= e(url('admin/recruitment/' . $scope . '/' . $id . '/portrait')) ?>" alt="Portrait of <?= e($row['name']) ?>" width="96" height="96"></a>
            <?php else: ?><span class="rc-portrait rc-portrait--none"><?= icon('user', 'icon--sm') ?></span><?php endif; ?>
            <div>
                <p class="rc-files__line"><?= $hasCv ? '<a class="btn btn--outline btn--sm" href="' . e(url('admin/recruitment/' . $scope . '/' . $id . '/cv')) . '">' . icon('download', 'icon--sm') . ' Download the CV</a>' : '<span class="text-muted">No CV on file</span>' ?></p>
                <p class="text-muted rc-files__line">The applicant is e-mailed at every stage and can follow it at <a href="<?= e(url('track-application')) ?>" target="_blank" rel="noopener">/track-application</a> with this number.</p>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (in_array('judge', $parts, true)): ?>
<section class="card rc-panel" id="judge">
    <div class="card__header">
        <h2 class="card__title">Judge this application</h2>
        <?php if ($pct !== null): $p = (float) $pct; ?><span class="badge <?= $p >= $level ? 'badge--success' : 'badge--outline' ?> rc-score"><?= e(rtrim(rtrim(number_format($p, 1), '0'), '.')) ?>%</span><?php endif; ?>
    </div>
    <?php if ($kpis === []): ?>
        <div class="card__body"><p class="text-muted">No criteria are set for this role yet. <a href="<?= e(url('admin/recruitment/criteria', ['scope' => $scope])) ?>">Set the criteria</a>.</p></div>
    <?php else: ?>
        <form method="post" action="<?= e(url('admin/recruitment/' . $scope . '/' . $id . '/judge')) ?>" data-rc-judge data-rc-level="<?= (int) $level ?>">
            <?= csrf_field() ?>
            <div class="card__body">
                <p class="text-muted">Click a score from 0 (none) to <?= (int) Recruitment::MAX_POINTS ?> (excellent) for each criterion. At <strong><?= (int) $level ?>%</strong> or more, with every criterion scored, the applicant is invited for an interview automatically. You can always move an applicant forward yourself, whatever the score.</p>
                <?php foreach ($kpis as $k): $kid = (int) $k['id']; ?>
                    <fieldset class="rc-kpi">
                        <legend class="rc-kpi__label"><?= e($k['label']) ?> <span class="rc-kpi__w">x<?= (int) $k['weight'] ?></span></legend>
                        <div class="rc-kpi__scale" role="radiogroup" aria-label="<?= e($k['label']) ?>">
                            <?php for ($i = 0; $i <= Recruitment::MAX_POINTS; $i++): ?>
                                <label class="rc-pt"><input type="radio" name="points[<?= $kid ?>]" value="<?= $i ?>" data-w="<?= (int) $k['weight'] ?>" <?= isset($points[$kid]) && $points[$kid] === $i ? 'checked' : '' ?>><span><?= $i ?></span></label>
                            <?php endfor; ?>
                        </div>
                    </fieldset>
                <?php endforeach; ?>
                <p class="rc-live" data-rc-live aria-live="polite"></p>
            </div>
            <div class="card__footer"><button class="btn btn--primary" type="submit"><?= icon('check', 'icon--sm') ?> Save the scores</button>
                <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/recruitment/criteria', ['scope' => $scope])) ?>">Edit the criteria</a></div>
        </form>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php if ($history && in_array('history', $parts, true)): ?>
<section class="card rc-panel">
    <div class="card__header"><h2 class="card__title">Stages and e-mails</h2></div>
    <div class="card__body">
        <ol class="rc-history">
            <?php foreach ($history as $h): ?>
                <li><strong><?= e(Recruitment::statuses($scope)[$h['status']] ?? $h['status']) ?></strong>
                    <span class="text-muted"><?= e(format_datetime($h['created_at'], 'j M Y, H:i')) ?><?= $h['by_name'] ? ' by ' . e($h['by_name']) : '' ?><?= $h['mailed'] ? ' &middot; applicant e-mailed' : '' ?></span>
                    <?php if ($h['note']): ?><span class="rc-history__note"><?= e($h['note']) ?></span><?php endif; ?></li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>
<?php endif; ?>
