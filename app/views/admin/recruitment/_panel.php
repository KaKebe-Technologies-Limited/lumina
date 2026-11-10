<?php
/**
 * Recruitment panel on the admin page of one application (ambassador, job or volunteer). Vars: $scope, $row (the application row), $parts (files | judge | history; default all).
 * Judging: the system scores what it can from the form (marked "from the form"), the judge clicks the rest, can switch a criterion off for this applicant, add a criterion,
 * write a comment per criterion and an overall feedback (optionally e-mailed to the applicant).
 */
use App\Services\Recruitment\AutoScore;
use App\Services\Recruitment\Recruitment;

$parts = $parts ?? ['files', 'judge', 'history'];
$cfg = Recruitment::SCOPES[$scope];
$id = (int) $row['id'];
Recruitment::ensureAuto($scope, $row);
$kpis = Recruitment::kpis($scope);
$rows = Recruitment::scoreRows($scope, $id);
$eval = Recruitment::evaluate($scope, $id);
$pct = $eval['pct'];
$level = Recruitment::threshold();
$history = Recruitment::history($scope, $id);
$feedback = Recruitment::feedback($scope, $id);
$hasPortrait = trim((string) ($row['photo_path'] ?? '')) !== '';
$hasCv = trim((string) ($row['cv_path'] ?? '')) !== '';
$back = $cfg['admin'] . $id;
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
            <?= csrf_field() ?><input type="hidden" name="use_posted" value="1">
            <div class="card__body">
                <p class="text-muted">The system has already scored what it can from the form (<span class="rc-tag">from the form</span>). Check those points, click the others, and untick a criterion to leave it out for this applicant. At <strong><?= (int) $level ?>%</strong> or more, with every criterion in use scored, the applicant is invited for an interview automatically; you can always move them forward yourself.</p>
                <?php foreach ($kpis as $k): $kid = (int) $k['id']; $sr = $rows[$kid] ?? null; $on = empty($sr['skipped']); $auto = !empty($k['auto_key']);
                    $reason = $auto ? (AutoScore::points((string) $k['auto_key'], $row)[1] ?? '') : ''; ?>
                    <fieldset class="rc-kpi<?= $on ? '' : ' is-off' ?>" data-rc-kpi>
                        <legend class="rc-kpi__label">
                            <label class="rc-use"><input type="checkbox" name="use[<?= $kid ?>]" value="1" <?= $on ? 'checked' : '' ?> data-rc-use> <span><?= e($k['label']) ?></span></label>
                            <span class="rc-kpi__w">x<?= (int) $k['weight'] ?></span>
                            <?php if ($auto && !empty($sr['auto'])): ?><span class="rc-tag">from the form</span><?php elseif ($auto): ?><span class="rc-tag rc-tag--edited">changed by you</span><?php endif; ?>
                        </legend>
                        <?php if ($reason !== ''): ?><p class="rc-kpi__why"><?= e($reason) ?></p><?php endif; ?>
                        <div class="rc-kpi__scale" role="radiogroup" aria-label="<?= e($k['label']) ?>">
                            <?php for ($i = 0; $i <= Recruitment::MAX_POINTS; $i++): ?>
                                <label class="rc-pt"><input type="radio" name="points[<?= $kid ?>]" value="<?= $i ?>" data-w="<?= (int) $k['weight'] ?>" <?= $sr !== null && (int) $sr['points'] === $i ? 'checked' : '' ?>><span><?= $i ?></span></label>
                            <?php endfor; ?>
                        </div>
                        <input class="input rc-kpi__comment" type="text" name="comments[<?= $kid ?>]" value="<?= e((string) ($sr['comment'] ?? '')) ?>" maxlength="300" placeholder="Comment on this criterion (optional, private)" aria-label="Comment on <?= e($k['label']) ?>">
                    </fieldset>
                <?php endforeach; ?>
                <p class="rc-live" data-rc-live aria-live="polite"></p>

                <div class="field"><label class="field__label" for="rc-feedback">Feedback on this application</label>
                    <textarea class="textarea" id="rc-feedback" name="feedback" rows="4" maxlength="4000" placeholder="Strengths, gaps, what to ask at the interview..."><?= e($feedback) ?></textarea></div>
                <label class="check"><input type="checkbox" name="send_feedback" value="1"> E-mail this feedback to the applicant</label>
            </div>
            <div class="card__footer"><button class="btn btn--primary" type="submit"><?= icon('check', 'icon--sm') ?> Save the scores and feedback</button>
                <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/recruitment/criteria', ['scope' => $scope])) ?>">All criteria</a></div>
        </form>
    <?php endif; ?>
    <details class="rc-add">
        <summary>Add a criterion for every <?= e(strtolower($cfg['title'] === 'Job applications' ? 'job applicant' : rtrim($cfg['title'], 's'))) ?></summary>
        <form method="post" action="<?= e(url('admin/recruitment/criteria')) ?>" class="rc-kpi-row"><?= csrf_field() ?>
            <input type="hidden" name="scope" value="<?= e($scope) ?>"><input type="hidden" name="id" value="0"><input type="hidden" name="return" value="<?= e($back) ?>"><input type="hidden" name="sort_order" value="900">
            <input class="input" name="label" maxlength="190" placeholder="For example: Knowledge of our programmes" aria-label="New criterion" required>
            <label class="rc-kpi-row__w">Weight <input class="input" type="number" name="weight" min="1" max="10" value="2"></label>
            <button class="btn btn--primary btn--sm" type="submit">Add</button>
        </form>
    </details>
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
