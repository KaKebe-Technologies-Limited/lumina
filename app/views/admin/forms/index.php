<?php
/**
 * Admin > Forms: the list. Vars: $pagination, $counts, $status, $q, $tabs, $canDelete, $canAnswers, $jobUse (form id => vacancies using it).
 * Status tabs, search, answers + new badge, copy link / shortcode / button code, duplicate, publish / close / archive, delete with a confirm.
 * Every action is a plain POST form (works without JavaScript); assets/js/admin-form-builder.js only adds the copy buttons.
 */
use App\Services\Forms\FormService;

$rows = $pagination->items;
$tabLabels = ['all' => 'All', 'draft' => 'Drafts', 'published' => 'Live', 'closed' => 'Closed', 'archived' => 'Archived'];
$stateText = ['open' => 'Live', 'draft' => 'Draft', 'archived' => 'Archived', 'not_open' => 'Opens later', 'closed' => 'Closed', 'full' => 'Full'];
$stateTone = ['open' => 'success', 'draft' => 'warning', 'archived' => 'neutral', 'not_open' => 'info', 'closed' => 'neutral', 'full' => 'gold'];
$kindText = ['job' => 'Job application', 'intake' => 'Private intake', 'survey' => 'Survey'];
$menus = [
    'draft'     => [['publish', 'Publish']],
    'published' => [['close', 'Close (stop taking answers)'], ['unpublish', 'Unpublish (back to draft)']],
    'closed'    => [['reopen', 'Reopen'], ['unpublish', 'Unpublish (back to draft)']],
    'archived'  => [['restore', 'Restore as draft']],
];
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-form-builder.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-forms">
    <?= partial('partials/admin/page-header', [
        'title' => 'Forms', 'subtitle' => 'Build forms to collect applications, registrations, feedback or any information, and handle the answers.',
        'actions' => [['label' => 'New form', 'url' => url('admin/forms/new'), 'icon' => 'plus']],
    ]) ?>

    <nav class="tabs" aria-label="Form status">
        <?php foreach ($tabs as $t): ?>
            <a class="tabs__item<?= $status === $t ? ' is-active' : '' ?>" href="<?= e(url('admin/forms', ['status' => $t === 'all' ? null : $t, 'q' => $q !== '' ? $q : null])) ?>"<?= $status === $t ? ' aria-current="page"' : '' ?>><?= e($tabLabels[$t]) ?> <span class="badge badge--neutral"><?= (int) ($counts[$t] ?? 0) ?></span></a>
        <?php endforeach; ?>
    </nav>

    <form class="filters" method="get" action="<?= e(url('admin/forms')) ?>" role="search">
        <?php if ($status !== 'all'): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
        <div class="field field--grow">
            <label class="field__label" for="forms-q">Search</label>
            <div class="search-input"><?= icon('search', 'icon--sm') ?><input class="input" id="forms-q" type="search" name="q" value="<?= e($q) ?>" placeholder="Form name or web address" maxlength="100"></div>
        </div>
        <div class="filters__actions">
            <button class="btn btn--primary" type="submit"><?= icon('filter', 'icon--sm') ?> Search</button>
            <?php if ($q !== ''): ?><a class="btn btn--ghost" href="<?= e(url('admin/forms', $status !== 'all' ? ['status' => $status] : [])) ?>">Reset</a><?php endif; ?>
        </div>
    </form>

    <?php if ($rows === []): ?>
        <?= partial('partials/empty-state', [
            'icon' => 'file-text',
            'title' => $q !== '' ? 'No forms match your search' : ($status !== 'all' ? 'No forms here' : 'No forms yet'),
            'text' => $q !== '' ? 'Try a different search.' : ($status !== 'all' ? 'There are no forms with this status.' : 'Start from a template (job application, volunteer sign-up, event RSVP ...) or from a blank form.'),
            'actions' => [['label' => 'Create a form', 'url' => url('admin/forms/new')]],
        ]) ?>
    <?php else: ?>
        <div class="table-wrap fl-wrap">
            <table class="table table--stack fl-table">
                <thead><tr>
                    <th scope="col">Form</th><th scope="col">Status</th><th scope="col">Answers</th><th scope="col">Last changed</th><th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
                </tr></thead>
                <tbody>
                <?php foreach ($rows as $f):
                    $a = $f['availability'];
                    $embed = FormService::embedCode($f);
                    $fid = (int) $f['id'];
                    $kind = (string) ($f['cfg']['kind'] ?? 'general');
                    $jobs = (int) ($jobUse[$fid] ?? 0);
                    $answers = (int) $f['submissions_count'];
                    $warnDelete = 'Delete "' . $f['title'] . '"?' . ($answers > 0 ? ' Its ' . $answers . ' answer' . ($answers === 1 ? '' : 's') . ' and uploaded files will be deleted for good.' : '')
                        . ($jobs > 0 ? ' ' . $jobs . ' vacanc' . ($jobs === 1 ? 'y' : 'ies') . ' will go back to the built-in application form.' : '') . ' This cannot be undone.';
                    $warnArchive = 'Archive "' . $f['title'] . '"? It stops being public' . ($jobs > 0 ? ' and ' . $jobs . ' vacanc' . ($jobs === 1 ? 'y' : 'ies') . ' will show the built-in application form' : '') . '. Its answers are kept and you can restore it later.';
                ?>
                    <tr>
                        <td data-label="Form">
                            <span class="cell-title"><a href="<?= e(url('admin/forms/' . $fid . '/edit')) ?>"><?= e($f['title']) ?></a></span>
                            <span class="cell-sub">/forms/<?= e($f['slug']) ?></span>
                            <span class="fl-tags">
                                <?php if (isset($kindText[$kind])): ?><span class="fl-tag"><?= e($kindText[$kind]) ?></span><?php endif; ?>
                                <?php if ($f['listed']): ?><span class="fl-tag">In the public list</span><?php endif; ?>
                                <?php if ($jobs > 0): ?><span class="fl-tag"><?= $jobs ?> vacanc<?= $jobs === 1 ? 'y' : 'ies' ?></span><?php endif; ?>
                            </span>
                        </td>
                        <td data-label="Status">
                            <?= partial('partials/admin/status-badge', ['status' => $f['status'], 'label' => $stateText[$a['state']] ?? $f['status'], 'tone' => $stateTone[$a['state']] ?? 'neutral']) ?>
                            <?php if ($a['state'] === 'full'): ?><span class="cell-sub">All places taken</span>
                            <?php elseif ($a['state'] === 'open' && $a['remaining'] !== null): ?><span class="cell-sub"><?= (int) $a['remaining'] ?> place<?= (int) $a['remaining'] === 1 ? '' : 's' ?> left</span>
                            <?php elseif ($a['state'] === 'not_open' && $a['opens_at']): ?><span class="cell-sub">Opens <?= e(format_datetime($a['opens_at'])) ?></span>
                            <?php elseif ($a['state'] === 'open' && $a['closes_at']): ?><span class="cell-sub">Closes <?= e(format_datetime($a['closes_at'])) ?></span><?php endif; ?>
                        </td>
                        <td data-label="Answers">
                            <?php if ($canAnswers): ?><a href="<?= e(url('admin/forms/' . $fid . '/submissions')) ?>"><?= $answers ?></a><?php else: ?><?= $answers ?><?php endif; ?>
                            <?php if ((int) $f['unread'] > 0): ?><span class="badge badge--warning"><?= (int) $f['unread'] ?> new</span><?php endif; ?>
                        </td>
                        <td data-label="Last changed"><?= e(format_datetime($f['updated_at'])) ?></td>
                        <td data-label="Actions" class="col-actions">
                            <div class="table__actions fl-actions">
                                <a class="btn btn--sm btn--outline" href="<?= e(url('admin/forms/' . $fid . '/edit')) ?>"><?= icon('edit', 'icon--sm') ?> Edit</a>
                                <?php if ($canAnswers): ?><a class="btn btn--sm btn--ghost" href="<?= e(url('admin/forms/' . $fid . '/submissions')) ?>"><?= icon('inbox', 'icon--sm') ?> Answers</a><?php endif; ?>
                                <details class="fl-menu" data-menu>
                                    <summary class="btn btn--sm btn--ghost" aria-label="More actions for <?= e($f['title']) ?>"><?= icon('more', 'icon--sm') ?> <span class="fl-menu__text">More</span></summary>
                                    <div class="fl-menu__panel">
                                        <?php if ($f['status'] !== 'archived'): ?>
                                            <a class="fl-menu__item" href="<?= e(url('forms/' . $f['slug'])) ?>" target="_blank" rel="noopener"><?= icon('external-link', 'icon--sm') ?> <?= $f['status'] === 'published' ? 'View the live form' : 'Preview (staff only)' ?></a>
                                            <button class="fl-menu__item" type="button" data-copy="<?= e($embed['link']) ?>" data-copy-label="Link copied"><?= icon('link', 'icon--sm') ?> Copy the link</button>
                                            <button class="fl-menu__item" type="button" data-copy="<?= e($embed['shortcode']) ?>" data-copy-label="Shortcode copied"><?= icon('code', 'icon--sm') ?> Copy the page shortcode</button>
                                            <button class="fl-menu__item" type="button" data-copy="<?= e($embed['button_html']) ?>" data-copy-label="Button code copied"><?= icon('copy', 'icon--sm') ?> Copy the button code</button>
                                        <?php endif; ?>
                                        <form method="post" action="<?= e(url('admin/forms/' . $fid . '/duplicate')) ?>"><?= csrf_field() ?><button class="fl-menu__item" type="submit"><?= icon('copy', 'icon--sm') ?> Duplicate</button></form>
                                        <?php foreach ($menus[$f['status']] ?? [] as [$act, $lbl]): ?>
                                            <form method="post" action="<?= e(url('admin/forms/' . $fid . '/status')) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="<?= e($act) ?>"><button class="fl-menu__item" type="submit"><?= icon($act === 'publish' || $act === 'reopen' || $act === 'restore' ? 'check-circle' : 'eye-off', 'icon--sm') ?> <?= e($lbl) ?></button></form>
                                        <?php endforeach; ?>
                                        <?php if ($f['status'] !== 'archived'): ?>
                                            <form method="post" action="<?= e(url('admin/forms/' . $fid . '/status')) ?>" data-confirm="<?= e($warnArchive) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="archive"><button class="fl-menu__item" type="submit"><?= icon('folder', 'icon--sm') ?> Archive</button></form>
                                        <?php endif; ?>
                                        <?php if ($canDelete): ?>
                                            <form method="post" action="<?= e(url('admin/forms/' . $fid . '/delete')) ?>" data-confirm="<?= e($warnDelete) ?>"><?= csrf_field() ?><button class="fl-menu__item fl-menu__item--danger" type="submit"><?= icon('trash', 'icon--sm') ?> Delete...</button></form>
                                        <?php endif; ?>
                                    </div>
                                </details>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= partial('partials/pagination', ['pagination' => $pagination]) ?>
    <?php endif; ?>
</div>
