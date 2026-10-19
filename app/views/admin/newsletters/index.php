<?php
/**
 * Admin > Newsletter > Issues (EDITOR / NEWSLETTER agent). Vars: $pagination (items = list rows, each with `live` = NewsletterService::liveStatus),
 * $filters (status, q, email_status), $counts (status => n), $pageVisible (the Newsletter page is switched on), $inMenu (it is in the header menu),
 * $publicCount (issues visitors can open right now), $missingPdf (id => true: its PDF file is gone from the server), $canSend, $canDeletePublished. Status tabs, search, one row per issue with its cover, format
 * (Written online / PDF), number, date, status (and when a scheduled issue goes live), e-mail status and the row actions. A row that visitors cannot
 * see says WHY under its title (draft, scheduled for later, archived, missing the PDF, Newsletter page switched off). Delete of an issue that is or
 * was public, and every e-mail link, are for administrators only.
 */
use App\Services\Newsletter\NewsletterService as S;

$rows = $pagination->items;
$tabs = ['' => 'All', 'draft' => 'Drafts', 'scheduled' => 'Scheduled', 'published' => 'Published', 'archived' => 'Archived'];
$filtered = $filters['status'] !== '' || $filters['q'] !== '' || $filters['email_status'] !== '';
?>
<div class="nl-admin">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Newsletter',
        'subtitle' => 'Write an issue, check it in the preview, and publish it when it is ready. The newest published issue is shown on the Newsletter page of the website.',
        'actions'  => array_values(array_filter([
            ($counts['published'] ?? 0) > 0 || ($counts['scheduled'] ?? 0) > 0 ? ['label' => 'View on the website', 'url' => url('newsletter'), 'class' => 'btn--outline', 'icon' => 'external-link', 'target' => '_blank'] : null,
            ['label' => 'Upload a PDF issue', 'url' => url('admin/newsletters/create', ['kind' => 'pdf']), 'class' => 'btn--outline', 'icon' => 'upload'],
            ['label' => 'Write an issue', 'url' => url('admin/newsletters/create'), 'icon' => 'plus'],
        ])),
    ]) ?>

    <?php if (!$pageVisible): ?>
        <div class="alert alert--warning nl-site-alert" role="alert"><?= icon('alert-triangle') ?><div class="alert__body"><strong>The Newsletter page is switched off.</strong> Visitors get “page not found” for every issue, even published ones, until you switch the page on again (Admin &gt; Pages &gt; Newsletter).</div></div>
    <?php elseif ((int) $publicCount === 0 && ($counts['all'] ?? 0) > 0): ?>
        <div class="alert alert--info nl-site-alert"><?= icon('info') ?><div class="alert__body"><strong>No issue is visible on the website yet.</strong> Visitors see “No issue has been published yet” on the Newsletter page. Open a draft and press “Publish now” to put it on the website.</div></div>
    <?php elseif (!$inMenu): ?>
        <div class="alert alert--info nl-site-alert"><?= icon('info') ?><div class="alert__body">The Newsletter page is not in the website menu, so visitors only find it through the footer, the Home page and the Blog page. You can add it back in Admin &gt; Menus.</div></div>
    <?php endif; ?>

    <nav class="tabs" aria-label="Show issues by status">
        <?php foreach ($tabs as $key => $label): $n = $counts[$key === '' ? 'all' : $key] ?? 0; $on = $filters['status'] === $key; ?>
            <a class="tabs__item<?= $on ? ' is-active' : '' ?>" href="<?= e(url('admin/newsletters', array_filter(['status' => $key, 'q' => $filters['q'], 'email_status' => $filters['email_status']], static fn($v) => $v !== ''))) ?>"<?= $on ? ' aria-current="page"' : '' ?>><?= e($label) ?> (<?= (int) $n ?>)</a>
        <?php endforeach; ?>
    </nav>

    <form class="filters" method="get" action="<?= e(url('admin/newsletters')) ?>" role="search">
        <?php if ($filters['status'] !== ''): ?><input type="hidden" name="status" value="<?= e($filters['status']) ?>"><?php endif; ?>
        <div class="field field--grow">
            <label class="field__label" for="nl-q">Search</label>
            <div class="search-input"><?= icon('search', 'icon--sm') ?><input class="input" id="nl-q" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Title, summary or issue number" maxlength="100"></div>
        </div>
        <?php if ($canSend): ?>
            <div class="field">
                <label class="field__label" for="nl-email-status">E-mail</label>
                <select class="select" id="nl-email-status" name="email_status" data-autosubmit>
                    <option value="">Any</option>
                    <?php foreach (S::EMAIL_LABELS as $k => $label): ?><option value="<?= e($k) ?>"<?= $filters['email_status'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        <div class="filters__actions">
            <button class="btn btn--primary" type="submit">Search</button>
            <?php if ($filtered): ?><a class="btn btn--ghost" href="<?= e(url('admin/newsletters')) ?>">Clear</a><?php endif; ?>
        </div>
    </form>

    <?php if (!$rows): ?>
        <?= partial('partials/empty-state', [
            'title' => $filtered ? 'No issues match' : 'No issues yet',
            'text'  => $filtered ? 'Try another search, or choose All.' : 'Write your first issue with the button above. Nothing is public until you publish it.',
            'icon'  => 'newspaper',
        ]) ?>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table table--stack">
                <thead><tr><th>Issue</th><th class="col-format">Format</th><th class="col-num">No.</th><th class="col-date">Date</th><th>Status</th><th>E-mail</th><th class="col-changed">Last change</th><th class="col-actions">Actions</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $r):
                    $id = (int) $r['id'];
                    $live = S::isLive($r);
                    $state = $r['live'] ?? null;                     // liveStatus(): visible, label, reasons ...
                    $isPdf = (string) ($r['kind'] ?? 'online') === 'pdf';
                    $everPublic = $r['status'] === 'published' || !empty($r['published_at']);
                    $mayDelete = !$everPublic || $canDeletePublished;
                    $cover = (string) ($r['cover_image'] ?? '');
                    ?>
                    <tr>
                        <td data-label="Issue" class="cell-title">
                            <div class="nl-issue-cell">
                                <?php if ($cover !== ''): ?><img class="nl-thumb" src="<?= e(img_url($cover)) ?>" alt="" width="64" height="44" loading="lazy"><?php else: ?><span class="nl-thumb nl-thumb--empty" aria-hidden="true"><?= icon('image', 'icon--sm') ?></span><?php endif; ?>
                                <span class="nl-issue-cell__text">
                                    <a class="nl-issue-cell__title" href="<?= e(url('admin/newsletters/' . $id . '/edit')) ?>"><?= e($r['title']) ?></a>
                                    <?php if (trim((string) $r['summary']) !== ''): ?><span class="cell-sub"><?= e(excerpt($r['summary'], 90)) ?></span><?php endif; ?>
                                    <span class="cell-sub nl-issue-cell__date"><?= e(format_date($r['issue_date'], 'j M Y')) ?></span>
                                    <span class="cell-sub nl-issue-cell__format"><?= $isPdf ? 'PDF issue' : 'Written online' ?></span>
                                    <?php if ($state && $state['visible']): ?>
                                        <a class="nl-live-dot" href="<?= e(S::url($r)) ?>" target="_blank" rel="noopener">Live on the website<span class="visually-hidden"> (opens in a new tab)</span></a>
                                    <?php elseif ($state && $state['reasons']): ?>
                                        <span class="nl-why"><?= icon('eye-off', 'icon--sm') ?><span><span class="nl-why__lead">Not visible to visitors:</span> <?= e(implode(' ', array_slice($state['reasons'], 0, 2))) ?><?= count($state['reasons']) > 2 ? ' (and ' . (count($state['reasons']) - 2) . ' more: open the issue)' : '' ?></span></span>
                                    <?php endif; ?>
                                    <?php if (!empty($missingPdf[$id])): ?>
                                        <span class="nl-why"><?= icon('alert-triangle', 'icon--sm') ?><span><span class="nl-why__lead">The PDF file is missing from the server.</span> Open the issue and upload the PDF again<?= $live ? ': visitors cannot download it until then' : '' ?>.</span></span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </td>
                        <td data-label="Format" class="col-format"><?= $isPdf ? '<span class="badge badge--info" title="An uploaded PDF">' . icon('file-text', 'icon--sm') . ' PDF</span>' : '<span class="badge badge--neutral">Online</span>' ?><?php if ($isPdf && (int) ($r['pdf_downloads'] ?? 0) > 0): ?><span class="cell-sub"><?= (int) $r['pdf_downloads'] ?> download<?= (int) $r['pdf_downloads'] === 1 ? '' : 's' ?></span><?php endif; ?></td>
                        <td data-label="No." class="col-num"><?= $r['issue_number'] !== null ? (int) $r['issue_number'] : '-' ?></td>
                        <td data-label="Date" class="col-date"><?= e(format_date($r['issue_date'], 'j M Y')) ?></td>
                        <td data-label="Status">
                            <?= partial('partials/admin/status-badge', ['status' => $r['status'], 'label' => S::STATUS_LABELS[$r['status']] ?? $r['status'], 'tone' => S::STATUS_TONES[$r['status']] ?? 'neutral']) ?>
                            <?php if ($r['status'] === 'scheduled' && $r['publish_at']): ?><span class="cell-sub">Goes live <?= e(format_datetime($r['publish_at'], 'j M Y, H:i')) ?></span><?php endif; ?>
                        </td>
                        <td data-label="E-mail">
                            <?= partial('partials/admin/status-badge', ['status' => $r['email_status'], 'label' => S::EMAIL_LABELS[$r['email_status']] ?? $r['email_status'], 'tone' => S::EMAIL_TONES[$r['email_status']] ?? 'neutral']) ?>
                            <?php if ((int) $r['email_total'] > 0): ?><span class="cell-sub"><?= (int) $r['email_sent'] ?> of <?= (int) $r['email_total'] ?> sent</span><?php endif; ?>
                        </td>
                        <td data-label="Last change" class="col-changed"><span class="cell-sub"><?= e(format_datetime($r['updated_at'], 'j M Y, H:i')) ?></span></td>
                        <td data-label="Actions" class="col-actions">
                            <div class="nl-row-actions">
                                <a class="btn btn--sm btn--outline" href="<?= e(url('admin/newsletters/' . $id . '/edit')) ?>"><?= icon('edit', 'icon--sm') ?> Edit</a>
                                <a class="btn btn--sm btn--ghost" href="<?= e(url('admin/newsletters/' . $id . '/preview')) ?>" target="_blank" rel="noopener" aria-label="Preview <?= e($r['title']) ?> in a new tab"><?= icon('eye', 'icon--sm') ?> Preview</a>
                                <?php if ($canSend): ?><a class="btn btn--sm btn--ghost" href="<?= e(url('admin/newsletters/' . $id . '/email')) ?>" aria-label="E-mail <?= e($r['title']) ?>"><?= icon('mail', 'icon--sm') ?> E-mail</a><?php endif; ?>
                                <form method="post" action="<?= e(url('admin/newsletters/' . $id . '/duplicate')) ?>"><?= csrf_field() ?><button class="btn btn--sm btn--ghost" type="submit" aria-label="Make a copy of <?= e($r['title']) ?>"><?= icon('copy', 'icon--sm') ?> Duplicate</button></form>
                                <?php if ($mayDelete): ?>
                                    <form method="post" action="<?= e(url('admin/newsletters/' . $id . '/delete')) ?>" data-confirm="<?= e('Delete "' . $r['title'] . '" for good? Its saved versions go with it and this cannot be undone.' . ($live ? ' It is live, so it will disappear from the website.' : '')) ?>"><?= csrf_field() ?><button class="btn btn--sm btn--ghost nl-btn-danger" type="submit" aria-label="Delete <?= e($r['title']) ?>"><?= icon('trash', 'icon--sm') ?> Delete</button></form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= partial('partials/pagination', ['pagination' => $pagination, 'base' => 'admin/newsletters']) ?>
    <?php endif; ?>
</div>
