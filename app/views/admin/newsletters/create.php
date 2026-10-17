<?php
/**
 * Admin > Newsletter > Write an issue: the small "new draft" form (EDITOR agent). Vars: $nextNumber.
 * Only a title is needed; the editor opens right after, and nothing is public until the issue is published.
 */
?>
<div class="nl-admin">
    <?= partial('partials/admin/page-header', ['title' => 'Write an issue', 'subtitle' => 'Give it a title to start. You can change everything later; nothing is public until you publish it.']) ?>
    <form class="card nl-create" method="post" action="<?= e(url('admin/newsletters')) ?>" novalidate>
        <?= csrf_field() ?>
        <div class="card__body">
            <?= partial('partials/form-field', ['name' => 'title', 'label' => 'Title', 'required' => true, 'maxlength' => 190, 'help' => 'The headline of this issue. It appears on the website, in the archive and in e-mails.', 'attrs' => ['autofocus' => true]]) ?>
            <div class="nl-grid-2">
                <?= partial('partials/form-field', ['name' => 'issue_number', 'label' => 'Issue number', 'type' => 'number', 'value' => old('issue_number', (string) $nextNumber), 'min' => 1, 'help' => 'Suggested: the next free number. Each issue needs its own.']) ?>
                <?= partial('partials/form-field', ['name' => 'issue_date', 'label' => 'Issue date', 'type' => 'date', 'value' => old('issue_date', date('Y-m-d')), 'help' => 'The date printed on the issue.']) ?>
            </div>
            <p class="nl-create__next"><?= icon('info', 'icon--sm') ?> Next you will write the featured article, add sections (articles, pictures, quotes, buttons) and see a live preview as you go.</p>
        </div>
        <div class="card__footer"><button class="btn btn--primary" type="submit"><?= icon('plus', 'icon--sm') ?> Create the draft</button> <a class="btn btn--ghost" href="<?= e(url('admin/newsletters')) ?>">Cancel</a></div>
    </form>
</div>
