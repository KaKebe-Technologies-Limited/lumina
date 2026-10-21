<?php /** Admin > Academy > Score the projects of a challenge. Vars: $c, $criteria, $entries (with 'mine' judgment), $phase, $manager. */
use App\Services\Academy\ChallengeService;

$over = !empty($c['finalized_at']);
?>
<?= partial('partials/admin/page-header', ['title' => 'Score projects: ' . (string) $c['title'], 'subtitle' => 'Give each project 0 to 10 points for every criterion. The system averages the judges\' scores and marks the winners automatically.']) ?>
<?php if ($over): ?><p class="opp-notice ac-ok"><?= icon('check-circle', 'icon--sm') ?> The results are final: scores can no longer be changed.</p><?php endif; ?>
<div class="card"><div class="card__body"><strong>Criteria</strong>
    <ul class="ac-list"><?php foreach ($criteria as $x): ?><li><?= e($x['name']) ?> <span class="text-muted">(<?= (int) $x['weight'] ?>%)</span></li><?php endforeach; ?></ul>
    <p class="text-muted">Results are final <?= e(format_datetime($c['judging_ends_at'] ?: $c['ends_at'], 'j M Y, H:i')) ?> (East Africa Time). Status: <?= e(ChallengeService::PHASES[$phase] ?? '') ?>.</p></div></div>
<?php if ($entries === []): ?>
    <?= partial('partials/empty-state', ['title' => 'No submitted project yet', 'icon' => 'file-text', 'text' => 'Projects appear here as approved entrants submit them.']) ?>
<?php endif; ?>
<?php foreach ($entries as $e): $eid = (int) $e['id']; $mine = $e['mine'] ? (array) json_decode((string) $e['mine']['scores'], true) : []; ?>
    <section class="card" id="e<?= $eid ?>" aria-labelledby="t<?= $eid ?>">
        <div class="card__header"><h2 class="card__title" id="t<?= $eid ?>"><?= e($e['sub_title']) ?> <span class="text-muted">by <?= e($e['team_name'] ?: $e['learner_name']) ?></span></h2></div>
        <div class="card__body">
            <p><?= nl2br(e((string) $e['sub_summary'])) ?></p>
            <p><?php if ($e['sub_url']): ?><a href="<?= e($e['sub_url']) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= icon('external-link', 'icon--sm') ?> Open the project link</a> &nbsp;<?php endif; ?>
                <?php if ($e['sub_file_path']): ?><a href="<?= e(url('admin/academy/challenges/entries/' . $eid . '/file/project')) ?>"><?= icon('download', 'icon--sm') ?> <?= e((string) $e['sub_file_name']) ?></a><?php endif; ?></p>
            <p class="text-muted"><?= (int) $e['judgments'] ?> judge<?= (int) $e['judgments'] === 1 ? '' : 's' ?> so far<?= $e['average'] !== null ? ', average ' . e(number_format((float) $e['average'], 1)) . ' out of 100' : '' ?>.<?= $e['mine'] ? ' Your total: ' . e(number_format((float) $e['mine']['total'], 1)) . '.' : '' ?></p>
            <?php if (!$over): ?>
                <form method="post" action="<?= e(url('admin/academy/challenges/' . (int) $c['id'] . '/judge/' . $eid)) ?>" novalidate>
                    <?= csrf_field() ?>
                    <div class="form-grid">
                        <?php foreach ($criteria as $i => $x): ?>
                            <?= partial('partials/form-field', ['name' => 'points[' . $i . ']', 'id' => 'p' . $eid . '-' . $i, 'label' => $x['name'] . ' (0-10)', 'type' => 'number', 'min' => 0, 'max' => 10, 'step' => 1, 'value' => (string) ($mine[$i] ?? ''), 'required' => true]) ?>
                        <?php endforeach; ?>
                    </div>
                    <?= partial('partials/form-field', ['name' => 'comment', 'id' => 'c' . $eid, 'label' => 'Comment (optional, kept private)', 'maxlength' => 500, 'value' => (string) ($e['mine']['comment'] ?? '')]) ?>
                    <button class="btn btn--primary btn--sm" type="submit"><?= $e['mine'] ? 'Update my scores' : 'Save my scores' ?></button>
                </form>
            <?php endif; ?>
        </div>
    </section>
<?php endforeach; ?>
