<?php
/**
 * Admin > Forms > answers: the inbox of ONE form. Vars: $form, $pagination, $f (filters), $back (query string of the position), $counts, $columns, $labels,
 * $tabs, $contexts, $retention, $canExport, $canDelete, $canManage, $perPage.
 * Contract: docs/FORMS.md sections 7 and 16. PERSONAL DATA: nothing here is printed unescaped; the audit log never sees answers.
 * The bulk form POSTs to admin/forms/{id}/submissions/bulk with action + ids[] (+ back); the row checkboxes use form="fs-bulk".
 */
use App\Services\Forms\FormService;
use App\Services\Forms\SubmissionService;

$fid = (int) $form['id'];
$rows = $pagination->items;
$base = 'admin/forms/' . $fid . '/submissions';
$total = (int) ($counts['all'] ?? 0) + (int) ($counts['archived'] ?? 0) + (int) ($counts['spam'] ?? 0);      // every row, whatever its status (the figure in forms.submissions_count leaves spam out)
$filtered = $f['q'] !== '' || $f['from'] !== '' || $f['to'] !== '' || $f['ref'] !== '';
$keep = array_filter(['q' => $f['q'], 'from' => $f['from'], 'to' => $f['to'], 'ref' => $f['ref'], 'per_page' => $f['per'], 'sort' => $f['sort'] === 'created_at' ? '' : $f['sort'], 'dir' => $f['dir'] === 'desc' ? '' : $f['dir']], static fn($v): bool => $v !== '');
$statusKeep = $f['status'] !== 'all' ? ['status' => $f['status']] : [];
$tabLabel = static fn(string $t): string => $t === 'all' ? 'All' : ($labels[$t] ?? ucfirst($t));
$isLive = in_array($form['status'], ['published', 'closed'], true);
$hasContext = $contexts !== [];
$first = $columns[0] ?? null;
$rest = array_slice($columns, 1);
$sortUrl = static function (string $key) use ($f, $keep, $statusKeep, $base): string {
    $dir = $f['sort'] === $key && $f['dir'] === 'desc' ? 'asc' : 'desc';
    return url($base, array_merge($keep, $statusKeep, ['sort' => $key, 'dir' => $dir]));
};
$ariaSort = static fn(string $key): string => $f['sort'] === $key ? ($f['dir'] === 'asc' ? 'ascending' : 'descending') : 'none';
$exportQuery = array_merge($keep, $statusKeep);
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-form-submissions.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-inbox admin-forms">
    <?= partial('partials/admin/page-header', [
        'title' => (string) $form['title'],
        'subtitle' => $total === 0 ? 'No answers yet.' : ($total === 1 ? '1 answer in total.' : $total . ' answers in total.'),
        'actions' => array_values(array_filter([
            ['label' => 'Summary', 'url' => url($base . '/stats'), 'class' => 'btn--outline', 'icon' => 'bar-chart'],
            $canExport ? ['label' => 'Export CSV', 'url' => url($base . '/export', $exportQuery), 'class' => 'btn--outline', 'icon' => 'download'] : null,
            $canManage ? ['label' => 'Edit form', 'url' => url('admin/forms/' . $fid . '/edit'), 'class' => 'btn--outline', 'icon' => 'edit'] : null,
        ])),
    ]) ?>

    <?php if ($retention > 0): ?>
        <p class="inbox-notice fs-notice" role="note"><?= icon('clock', 'icon--sm') ?> Answers to this form are deleted automatically <?= (int) $retention ?> day<?= $retention === 1 ? '' : 's' ?> after they arrive.</p>
    <?php endif; ?>

    <nav class="tabs inbox-tabs" aria-label="Filter by status">
        <?php foreach ($tabs as $t): $n = (int) ($counts[$t] ?? 0); $on = $f['status'] === $t; ?>
            <a class="tabs__item<?= $on ? ' is-active' : '' ?>" href="<?= e(url($base, $keep + ($t === 'all' ? [] : ['status' => $t]))) ?>"<?= $on ? ' aria-current="page"' : '' ?>><?= e($tabLabel($t)) ?> <span class="inbox-count"><?= $n ?><span class="visually-hidden"> answers</span></span></a>
        <?php endforeach; ?>
    </nav>

    <form class="filters" method="get" action="<?= e(url($base)) ?>" role="search" aria-label="Search and filter the answers">
        <?php if ($f['status'] !== 'all'): ?><input type="hidden" name="status" value="<?= e($f['status']) ?>"><?php endif; ?>
        <div class="field field--grow"><label class="field__label" for="fs-q">Search the answers</label>
            <div class="search-input"><?= icon('search', 'icon--sm') ?><input class="input" id="fs-q" type="search" name="q" value="<?= e($f['q']) ?>" maxlength="200" placeholder="A name, e-mail, word in an answer or note"></div></div>
        <?php if ($hasContext): ?>
            <div class="field"><label class="field__label" for="fs-ref">Applying for</label>
                <select class="select" id="fs-ref" name="ref" data-autosubmit>
                    <option value="">Everything</option>
                    <?php foreach ($contexts as $c): ?><option value="<?= e($c['ref']) ?>"<?= $f['ref'] === $c['ref'] ? ' selected' : '' ?>><?= e($c['label']) ?> (<?= (int) $c['n'] ?>)</option><?php endforeach; ?>
                </select></div>
        <?php endif; ?>
        <div class="field fs-date"><label class="field__label" for="fs-from">Received from</label><input class="input" id="fs-from" type="date" name="from" value="<?= e($f['from']) ?>"></div>
        <div class="field fs-date"><label class="field__label" for="fs-to">Received until</label><input class="input" id="fs-to" type="date" name="to" value="<?= e($f['to']) ?>"></div>
        <div class="filters__actions"><button class="btn btn--primary" type="submit"><?= icon('filter', 'icon--sm') ?> Filter</button><?php if ($filtered || $f['sort'] !== 'created_at' || $f['dir'] !== 'desc'): ?> <a class="btn btn--ghost" href="<?= e(url($base, $statusKeep)) ?>">Reset</a><?php endif; ?></div>
        <details class="fs-more"<?= ($f['sort'] !== 'created_at' || $f['dir'] !== 'desc' || (int) $perPage !== 20) ? ' open' : '' ?>>
            <summary>Order and page size</summary>
            <div class="fs-more__body">
                <div class="field"><label class="field__label" for="fs-sort">Order by</label>
                    <select class="select" id="fs-sort" name="sort" data-autosubmit><?php foreach (['created_at' => 'Date received', 'status' => 'Status', 'submitter_email' => 'E-mail address'] as $k => $l): ?><option value="<?= e($k) ?>"<?= $f['sort'] === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
                <div class="field"><label class="field__label" for="fs-dir">Direction</label>
                    <select class="select" id="fs-dir" name="dir" data-autosubmit><option value="desc"<?= $f['dir'] === 'desc' ? ' selected' : '' ?>>Newest / Z-A first</option><option value="asc"<?= $f['dir'] === 'asc' ? ' selected' : '' ?>>Oldest / A-Z first</option></select></div>
                <div class="field"><label class="field__label" for="fs-per">Per page</label>
                    <select class="select" id="fs-per" name="per_page" data-autosubmit><?php foreach ([10, 20, 50, 100] as $n): ?><option value="<?= $n ?>"<?= (int) $perPage === $n ? ' selected' : '' ?>><?= $n ?></option><?php endforeach; ?></select></div>
            </div>
        </details>
    </form>

    <?php if ($rows === []): ?>
        <?php if ($total === 0): ?>
            <?= partial('partials/empty-state', [
                'icon' => 'inbox', 'title' => 'No answers yet',
                'text' => $isLive ? 'When someone sends this form, the answer appears here. Share the link to start collecting.' : 'This form is not live yet. Publish it to start collecting answers.',
                'actions' => $isLive ? [['label' => 'Open the public form', 'url' => FormService::embedCode($form)['link'], 'class' => 'btn--outline']] : ($canManage ? [['label' => 'Open the builder', 'url' => url('admin/forms/' . $fid . '/edit'), 'class' => 'btn--outline']] : []),
            ]) ?>
        <?php else: ?>
            <?= partial('partials/empty-state', [
                'icon' => 'inbox', 'title' => $filtered ? 'No answers match these filters' : 'Nothing in this tab',
                'text' => $filtered ? 'Try a different search, or clear the filters.' : 'Answers with this status appear here.',
                'actions' => [['label' => $filtered ? 'Clear the filters' : 'Show all answers', 'url' => url($base), 'class' => 'btn--outline']],
            ]) ?>
        <?php endif; ?>
    <?php else: ?>
        <form method="post" action="<?= e(url($base . '/bulk')) ?>" id="fs-bulk" class="inbox-bulk toolbar" data-fs-bulk data-fs-noun="answers" data-fs-danger="delete,status:spam">
            <?= csrf_field() ?>
            <input type="hidden" name="back" value="<?= e($back) ?>">
            <label class="visually-hidden" for="fs-action">Action for the selected answers</label>
            <select class="select" id="fs-action" name="action">
                <option value="">With the selected answers ...</option>
                <optgroup label="Mark">
                    <option value="read">Mark as read</option>
                    <option value="unread">Mark as unread</option>
                </optgroup>
                <optgroup label="Set the status to">
                    <?php foreach (['starred', 'shortlisted', 'accepted', 'rejected', 'archived', 'spam'] as $s): ?><option value="status:<?= e($s) ?>"><?= e($labels[$s] ?? ucfirst($s)) ?></option><?php endforeach; ?>
                </optgroup>
                <?php if ($canDelete): ?><optgroup label="Danger"><option value="delete">Delete permanently</option></optgroup><?php endif; ?>
            </select>
            <button class="btn btn--outline btn--sm" type="submit">Apply</button>
            <span class="inbox-bulk__count" data-fs-count role="status" aria-live="polite"></span>
            <?php if (count($rest) > 2): ?><button class="btn btn--ghost btn--sm" type="button" data-fs-cols="#fs-table" aria-pressed="false" hidden><?= icon('layout', 'icon--sm') ?> <span>Show all columns</span></button><?php endif; ?>
            <p class="result-count">Showing <?= (int) $pagination->from() ?>-<?= (int) $pagination->to() ?> of <?= (int) $pagination->total ?></p>
        </form>
        <div class="table-wrap">
            <table class="table table--stack fs-table" id="fs-table">
                <caption class="visually-hidden">Answers to <?= e((string) $form['title']) ?></caption>
                <thead><tr>
                    <th class="col-narrow" scope="col"><input type="checkbox" data-fs-select-all aria-label="Select all answers on this page"></th>
                    <th scope="col"><?= e($first['label'] ?? 'Answer') ?></th>
                    <?php foreach ($rest as $c): ?><th scope="col" class="fs-col-extra"><?= e($c['label']) ?></th><?php endforeach; ?>
                    <th scope="col" aria-sort="<?= e($ariaSort('status')) ?>"><a class="fs-sort" href="<?= e($sortUrl('status')) ?>">Status<?= $f['sort'] === 'status' ? ' ' . icon($f['dir'] === 'asc' ? 'chevron-up' : 'chevron-down', 'icon--sm') : '' ?></a></th>
                    <th scope="col" aria-sort="<?= e($ariaSort('created_at')) ?>"><a class="fs-sort" href="<?= e($sortUrl('created_at')) ?>">Received<?= $f['sort'] === 'created_at' ? ' ' . icon($f['dir'] === 'asc' ? 'chevron-up' : 'chevron-down', 'icon--sm') : '' ?></a></th>
                    <th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
                </tr></thead>
                <tbody>
                <?php foreach ($rows as $s):
                    $sid = (int) $s['id'];
                    $ref = SubmissionService::reference($fid, $sid);
                    $href = url($base . '/' . $sid, $keep + $statusKeep + ($pagination->page > 1 ? ['page' => $pagination->page] : []));
                    $title = $first !== null ? trim(SubmissionService::answerText($s, $first['id'])) : '';
                    $title = $title !== '' ? mb_substr($title, 0, 80) : $ref; ?>
                    <tr class="<?= $s['status'] === 'new' ? 'is-unread' : '' ?>" data-fs-row-href="<?= e($href) ?>">
                        <td class="col-narrow" data-label="Select"><input type="checkbox" name="ids[]" value="<?= $sid ?>" form="fs-bulk" data-fs-row aria-label="Select answer <?= e($ref) ?>"></td>
                        <td data-label="<?= e($first['label'] ?? 'Answer') ?>">
                            <span class="cell-title"><a href="<?= e($href) ?>"><?= e($title) ?></a></span>
                            <span class="cell-sub"><?= e($ref) ?><?php if (!empty($s['context_label'])): ?> &middot; Applying for: <?= e($s['context_label']) ?><?php endif; ?><?php $nFiles = 0; foreach ($s['entries'] as $en) { if (($en['type'] ?? '') === 'file' && is_array($en['value'] ?? null)) { $nFiles += count($en['value']); } } if ($nFiles > 0): ?> &middot; <?= icon('file', 'icon--sm') ?> <?= $nFiles ?> file<?= $nFiles === 1 ? '' : 's' ?><?php endif; ?></span>
                        </td>
                        <?php foreach ($rest as $c): $t = trim(SubmissionService::answerText($s, $c['id'])); ?>
                            <td class="fs-col-extra fs-col--<?= e($c['type']) ?>" data-label="<?= e($c['label']) ?>"><?= $t !== '' ? '<span class="fs-clamp">' . e(mb_substr($t, 0, 160)) . '</span>' : '<span class="text-muted" aria-label="No answer">-</span>' ?></td>
                        <?php endforeach; ?>
                        <td data-label="Status"><?= partial('partials/admin/status-badge', ['status' => $s['status'], 'label' => $labels[$s['status']] ?? $s['status'], 'tone' => ['new' => 'warning', 'read' => 'neutral', 'starred' => 'gold', 'shortlisted' => 'info', 'accepted' => 'success', 'rejected' => 'danger', 'archived' => 'neutral', 'spam' => 'danger'][$s['status']] ?? 'neutral']) ?></td>
                        <td data-label="Received" class="nowrap"><?= e(format_date($s['created_at'], 'j M Y')) ?><span class="cell-sub"><?= e(format_date($s['created_at'], 'H:i')) ?></span></td>
                        <td data-label="" class="col-actions"><a class="btn btn--outline btn--sm" href="<?= e($href) ?>"><?= icon('eye', 'icon--sm') ?> Open<span class="visually-hidden"> answer <?= e($ref) ?></span></a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= partial('partials/pagination', ['pagination' => $pagination]) ?>
    <?php endif; ?>
</div>
