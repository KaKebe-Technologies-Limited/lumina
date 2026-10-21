<?php /** Admin > Academy > Facilitator applications. Vars: $rows, $status, $q, $counts. */
use App\Services\Academy\CourseService;
use App\Services\Academy\FacilitatorApplications;

$tabs = ['' => 'All'] + FacilitatorApplications::STATUSES; ?>
<?= partial('partials/admin/page-header', ['title' => 'Facilitator applications', 'subtitle' => 'People who volunteer to facilitate a topic. Read the CV and papers, then approve, ask for more information or decline. The role is unpaid.']) ?>
<nav class="tabs content-tabs" aria-label="Status">
    <?php foreach ($tabs as $k => $label): $n = $k === '' ? $counts['all'] : ($counts[$k] ?? 0); ?>
        <a class="tabs__item<?= $status === $k ? ' is-active' : '' ?>" href="<?= e(url('admin/academy/facilitator-applications', $k === '' ? [] : ['status' => $k])) ?>"<?= $status === $k ? ' aria-current="page"' : '' ?>><?= e($label) ?> <span class="content-tabs__count"><?= (int) $n ?></span></a>
    <?php endforeach; ?>
</nav>
<form class="filter-bar" method="get" action="<?= e(url('admin/academy/facilitator-applications')) ?>" role="search">
    <input type="hidden" name="status" value="<?= e($status) ?>">
    <?= partial('partials/form-field', ['name' => 'q', 'id' => 'fq', 'label' => 'Search', 'value' => $q, 'maxlength' => 80]) ?>
    <button class="btn btn--primary" type="submit">Search</button>
</form>
<?php if ($rows === []): ?>
    <?= partial('partials/empty-state', ['title' => 'No applications', 'icon' => 'users', 'text' => 'Applications arrive from the public page: ' . abs_url('academy/facilitate')]) ?>
<?php else: ?>
    <div class="table-wrap"><table class="table table--stack">
        <caption class="visually-hidden">Facilitator applications</caption>
        <thead><tr><th scope="col">Applicant</th><th scope="col">Topic</th><th scope="col">Sent</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): $id = (int) $r['id']; ?>
            <tr>
                <td class="cell-main" data-label="Applicant"><span class="cell-title"><a href="<?= e(url('admin/academy/facilitator-applications/' . $id)) ?>"><?= e($r['name']) ?></a></span><span class="cell-sub"><?= e($r['email']) ?></span></td>
                <td data-label="Topic"><?= e(CourseService::categoryLabel((string) $r['topic'])) ?><span class="cell-sub"><?= e((string) $r['topic_detail']) ?></span></td>
                <td data-label="Sent"><span class="nowrap"><?= e(format_date($r['created_at'], 'j M Y')) ?></span></td>
                <td data-label="Status"><span class="badge"><?= e(FacilitatorApplications::STATUSES[$r['status']] ?? $r['status']) ?></span></td>
                <td class="col-actions"><div class="table__actions"><a class="btn btn--outline btn--sm" href="<?= e(url('admin/academy/facilitator-applications/' . $id)) ?>">Review</a></div></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
<?php endif; ?>
