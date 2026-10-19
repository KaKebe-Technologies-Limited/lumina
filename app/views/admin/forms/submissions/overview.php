<?php
/**
 * Admin > Form answers: every form with its counts (for people who only have forms.submissions).
 * Vars: $forms (rows: id, title, slug, status, submissions_count, unread, last_at), $canManage.
 */
?>
<div class="admin-inbox admin-forms">
    <?= partial('partials/admin/page-header', ['title' => 'Form answers', 'subtitle' => 'Choose a form to read and handle what people sent.']) ?>
    <?php if ($forms === []): ?>
        <?= partial('partials/empty-state', ['icon' => 'inbox', 'title' => 'No forms yet', 'text' => 'When a form exists and people send it, the answers are listed here.']) ?>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table table--stack">
                <caption class="visually-hidden">Forms and their answers</caption>
                <thead><tr><th scope="col">Form</th><th scope="col">Form status</th><th scope="col" class="col-num">Answers</th><th scope="col" class="col-num">New</th><th scope="col">Latest answer</th><th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                <?php foreach ($forms as $f): $href = url('admin/forms/' . (int) $f['id'] . '/submissions'); ?>
                    <tr<?= (int) $f['unread'] > 0 ? ' class="is-unread"' : '' ?>>
                        <td data-label="Form"><span class="cell-title"><a href="<?= e($href) ?>"><?= e($f['title']) ?></a></span></td>
                        <td data-label="Form status"><?= partial('partials/admin/status-badge', ['status' => $f['status']]) ?></td>
                        <td class="col-num" data-label="Answers"><?= (int) $f['submissions_count'] ?></td>
                        <td class="col-num" data-label="New"><?= (int) $f['unread'] > 0 ? '<span class="badge badge--warning">' . (int) $f['unread'] . '</span>' : '0' ?></td>
                        <td data-label="Latest answer" class="nowrap"><?= $f['last_at'] !== null ? e(format_datetime($f['last_at'], 'j M Y, H:i')) : '<span class="text-muted">None yet</span>' ?></td>
                        <td data-label="" class="col-actions"><a class="btn btn--outline btn--sm" href="<?= e($href) ?>"><?= icon('inbox', 'icon--sm') ?> Open<span class="visually-hidden"> the answers to <?= e($f['title']) ?></span></a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
