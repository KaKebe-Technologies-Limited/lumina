<?php
/**
 * Admin > Forms > one answer. Vars: $form, $sub (hydrated: entries = snapshot, files), $ref, $labels, $workflow, $neighbours (prev = newer, next = older),
 * $position {index,total}, $f (inbox filters), $back (query string of the inbox position), $same (other answers by the same e-mail), $history, $deleteOn,
 * $canDelete, $canManage. Contract: docs/FORMS.md section 7. PERSONAL DATA: everything is escaped; only ids reach the audit log.
 */
$fid = (int) $form['id'];
$sid = (int) $sub['id'];
$base = 'admin/forms/' . $fid . '/submissions';
$keep = array_filter(['q' => $f['q'], 'from' => $f['from'], 'to' => $f['to'], 'ref' => $f['ref'], 'per_page' => $f['per'], 'status' => $f['status'] === 'all' ? '' : $f['status'], 'sort' => $f['sort'] === 'created_at' ? '' : $f['sort'], 'dir' => $f['dir'] === 'desc' ? '' : $f['dir']], static fn($v): bool => $v !== '');
$filesBy = [];
foreach ($sub['files'] as $file) {
    $filesBy[(string) $file['field_id']][] = $file;
}
$shown = [];
$groups = [];                                                  // page title => entries (kept in form order)
foreach ($sub['entries'] as $entry) {
    $shown[(string) ($entry['id'] ?? '')] = true;
    $groups[(string) ($entry['page'] ?? '')][] = $entry;
}
$hasConsentEntry = false;
foreach ($sub['entries'] as $entry) {
    if (($entry['type'] ?? '') === 'consent' && !empty($entry['at'])) {
        $hasConsentEntry = true;
    }
}
$orphans = array_values(array_filter($sub['files'], static fn(array $x): bool => !isset($shown[(string) $x['field_id']])));
$tone = ['new' => 'warning', 'read' => 'neutral', 'starred' => 'gold', 'shortlisted' => 'info', 'accepted' => 'success', 'rejected' => 'danger', 'archived' => 'neutral', 'spam' => 'danger'];
$statusLabel = $labels[$sub['status']] ?? $sub['status'];
$current = (int) ($form['version'] ?? 0);
$email = (string) ($sub['submitter_email'] ?? '');
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-form-submissions.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-inbox admin-forms fs-detail">
    <?= partial('partials/admin/page-header', [
        'title' => $ref,
        'subtitle' => 'Received ' . format_datetime($sub['created_at'], 'j F Y \a\t H:i') . ' (East Africa Time)',
        'actions' => [
            ['label' => 'Print', 'url' => url($base . '/' . $sid . '/print', $keep), 'class' => 'btn--outline', 'icon' => 'file-text', 'target' => '_blank'],
            ['label' => 'All answers', 'url' => url($base) . ($back !== '' ? '?' . $back : ''), 'class' => 'btn--outline', 'icon' => 'inbox'],
        ],
    ]) ?>

    <nav class="inbox-siblings fs-siblings" aria-label="Move between answers">
        <?php if ($neighbours['prev'] !== null): ?><a class="btn btn--outline btn--sm" href="<?= e(url($base . '/' . (int) $neighbours['prev'], $keep)) ?>" rel="prev"><?= icon('arrow-left', 'icon--sm') ?> Newer</a><?php else: ?><span class="btn btn--outline btn--sm is-disabled" aria-disabled="true"><?= icon('arrow-left', 'icon--sm') ?> Newer</span><?php endif; ?>
        <span class="fs-siblings__pos" aria-live="polite"><?= (int) $position['index'] ?> of <?= (int) $position['total'] ?></span>
        <?php if ($neighbours['next'] !== null): ?><a class="btn btn--outline btn--sm" href="<?= e(url($base . '/' . (int) $neighbours['next'], $keep)) ?>" rel="next">Older <?= icon('arrow-right', 'icon--sm') ?></a><?php else: ?><span class="btn btn--outline btn--sm is-disabled" aria-disabled="true">Older <?= icon('arrow-right', 'icon--sm') ?></span><?php endif; ?>
    </nav>

    <div class="card-grid card-grid--main-aside">
        <div class="inbox-main">
            <section class="card" aria-labelledby="fs-answers-title">
                <div class="card__header">
                    <h2 class="card__title" id="fs-answers-title">Answers</h2>
                    <?= partial('partials/admin/status-badge', ['status' => $sub['status'], 'label' => $statusLabel, 'tone' => $tone[$sub['status']] ?? 'neutral']) ?>
                </div>
                <div class="card__body">
                    <?php if (!empty($sub['context_label'])): ?>
                        <p class="fs-context"><?= icon('briefcase', 'icon--sm') ?> <span><strong>Applying for:</strong> <?= e($sub['context_label']) ?></span>
                            <?php if (!empty($sub['context_ref'])): ?> <a class="small" href="<?= e(url($base, ['ref' => (string) $sub['context_ref']])) ?>">All answers for this</a><?php endif; ?></p>
                    <?php endif; ?>
                    <?php if ($sub['entries'] === []): ?>
                        <p class="text-muted">This submission has no stored answers.</p>
                    <?php endif; ?>
                    <?php foreach ($groups as $pageTitle => $entries): ?>
                        <?php if (count($groups) > 1 && $pageTitle !== ''): ?><h3 class="fs-group"><?= e($pageTitle) ?></h3><?php endif; ?>
                        <dl class="definition-list fs-answers">
                            <?php foreach ($entries as $entry): ?>
                                <div class="fs-answers__row">
                                    <dt><?= e($entry['label'] ?? '') ?></dt>
                                    <dd><?= partial('admin/forms/submissions/_answer', ['e' => $entry, 'files' => $filesBy[(string) ($entry['id'] ?? '')] ?? [], 'base' => $base, 'sid' => $sid, 'print' => false]) ?></dd>
                                </div>
                            <?php endforeach; ?>
                        </dl>
                    <?php endforeach; ?>
                    <?php if (!empty($sub['consent_at']) && !$hasConsentEntry): ?>
                        <dl class="definition-list fs-answers"><div class="fs-answers__row"><dt>Consent given</dt><dd><?= icon('check-circle', 'icon--sm') ?> <?= e(format_datetime($sub['consent_at'])) ?></dd></div></dl>
                    <?php endif; ?>
                </div>
            </section>

            <?php if ($orphans !== []): ?>
                <section class="card" aria-labelledby="fs-other-files">
                    <div class="card__header"><h2 class="card__title" id="fs-other-files">Other files</h2></div>
                    <div class="card__body">
                        <p class="small text-muted">These files belong to a question that is no longer in the form.</p>
                        <ul class="fs-files">
                            <?php foreach ($orphans as $file): ?>
                                <li><a class="fs-file" href="<?= e(url($base . '/' . $sid . '/files/' . (int) $file['id'])) ?>" download><?= icon('download', 'icon--sm') ?> <span class="fs-file__name"><?= e($file['original_name']) ?></span></a>
                                    <span class="small text-muted">(<?= e(human_filesize((int) $file['size'])) ?>)</span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>
            <?php endif; ?>
        </div>

        <aside class="inbox-aside" aria-label="Handle this answer">
            <form class="card" method="post" action="<?= e(url($base . '/' . $sid . '/status')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="back" value="<?= e($back) ?>">
                <div class="card__header"><h2 class="card__title">Status</h2></div>
                <div class="card__body">
                    <p class="fs-current">Now: <strong><?= e($statusLabel) ?></strong></p>
                    <div class="fs-status" role="group" aria-label="Set the status">
                        <?php foreach ($workflow as $s): $on = $sub['status'] === $s; ?>
                            <button class="btn btn--sm <?= $on ? 'btn--primary' : 'btn--outline' ?>" type="submit" name="status" value="<?= e($s) ?>"<?= $on ? ' aria-pressed="true"' : ' aria-pressed="false"' ?>><?= e($s === 'new' ? 'Mark as unread' : ($labels[$s] ?? ucfirst($s))) ?></button>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($email !== '' && \App\Services\Forms\FormValidator::plainEmail($email)): ?>
                        <p class="fs-reply"><a class="btn btn--accent btn--sm" href="mailto:<?= e(rawurlencode($email)) ?>"><?= icon('mail', 'icon--sm') ?> E-mail <?= e($email) ?></a></p>
                    <?php endif; ?>
                </div>
            </form>

            <form class="card" method="post" action="<?= e(url($base . '/' . $sid . '/notes')) ?>" data-unsaved-guard>
                <?= csrf_field() ?>
                <input type="hidden" name="back" value="<?= e($back) ?>">
                <div class="card__header"><h2 class="card__title">Internal notes</h2></div>
                <div class="card__body">
                    <div class="field"><label class="field__label" for="sub-notes">Only staff can see these notes</label>
                        <textarea class="textarea" id="sub-notes" name="notes" rows="5" maxlength="5000" data-maxlength-counter><?= e((string) ($sub['notes'] ?? '')) ?></textarea></div>
                </div>
                <div class="card__footer"><button class="btn btn--primary btn--sm" type="submit">Save notes</button></div>
            </form>

            <section class="card" aria-labelledby="fs-details">
                <div class="card__header"><h2 class="card__title" id="fs-details">Details</h2></div>
                <div class="card__body">
                    <dl class="definition-list fs-meta">
                        <div><dt>Reference</dt><dd><?= e($ref) ?></dd></div>
                        <div><dt>Received</dt><dd><?= e(format_datetime($sub['created_at'], 'j M Y, H:i')) ?> EAT</dd></div>
                        <?php if ($email !== ''): ?><div><dt>E-mail typed</dt><dd><?= e($email) ?></dd></div><?php endif; ?>
                        <div><dt>Form version</dt><dd>Sent on version <?= (int) $sub['form_version'] ?><?= $current > (int) $sub['form_version'] ? ' (the form has been edited since)' : '' ?></dd></div>
                        <?php if ($same > 0): ?><div><dt>Same e-mail</dt><dd><?= (int) $same ?> other answer<?= $same === 1 ? '' : 's' ?> &middot; <a href="<?= e(url($base, ['q' => $email])) ?>">Show</a></dd></div><?php endif; ?>
                        <?php if ($deleteOn !== null): ?><div><dt>Deleted automatically</dt><dd><?= e(format_date($deleteOn, 'j M Y')) ?></dd></div><?php endif; ?>
                    </dl>
                </div>
            </section>

            <?php if ($history !== []): ?>
                <section class="card" aria-labelledby="fs-history">
                    <div class="card__header"><h2 class="card__title" id="fs-history">History</h2></div>
                    <ul class="fs-history">
                        <?php foreach ($history as $h): ?>
                            <li>
                                <span><?= e(match ($h['action']) {
                                    'form.submission.status' => 'Status set to ' . ($h['status'] !== null ? ($labels[$h['status']] ?? $h['status']) : 'a new value'),
                                    'form.submission.notes' => 'Notes saved',
                                    'form.submission.file' => 'File downloaded',
                                    'form.submission.print' => 'Print view opened',
                                    default => 'Changed',
                                }) ?></span>
                                <span class="small text-muted"><?= e($h['who']) ?> &middot; <time datetime="<?= e($h['at']) ?>"><?= e(format_datetime($h['at'], 'j M, H:i')) ?></time></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <?php if ($canDelete): ?>
                <form class="card inbox-danger" method="post" action="<?= e(url($base . '/' . $sid . '/delete')) ?>" data-confirm="Delete this answer and its files permanently? This cannot be undone.">
                    <?= csrf_field() ?>
                    <input type="hidden" name="back" value="<?= e($back) ?>">
                    <div class="card__body">
                        <h2 class="card__title">Delete</h2>
                        <p class="small text-muted">Removes the answer and its uploaded files for good, for example when a person asks for their data to be erased.</p>
                        <button class="btn btn--danger btn--sm" type="submit"><?= icon('trash', 'icon--sm') ?> Delete this answer</button>
                    </div>
                </form>
            <?php endif; ?>
        </aside>
    </div>
</div>
