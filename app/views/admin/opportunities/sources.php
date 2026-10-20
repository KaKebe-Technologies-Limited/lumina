<?php
/** Opportunity sources (RSS/Atom feeds). Vars: $rows, $categories, $enabled, $hours, $waiting. */
?>
<?= partial('partials/admin/page-header', [
    'title' => 'Opportunity sources',
    'subtitle' => 'The websites the Opportunities page reads from. Only a short excerpt and a link back to the original are shown, and the source is credited.',
    'actions' => [['label' => 'All opportunities', 'url' => url('admin/opportunities'), 'icon' => 'award', 'class' => 'btn--outline']],
]) ?>

<?php if (!$enabled): ?>
    <div class="alert alert--warning" role="status">Reading from other websites is switched off. Nothing new is fetched automatically (the "Fetch now" buttons still work).
        <?php if (can('settings.manage')): ?><a href="<?= e(url('admin/settings')) ?>">Open Settings &gt; Opportunities</a><?php endif; ?></div>
<?php else: ?>
    <p class="form-note">New opportunities are looked for about every <?= (int) $hours ?> hours, when someone visits the website<?= can('settings.manage') ? ' (change this in Settings &gt; Opportunities)' : '' ?>.</p>
<?php endif; ?>

<section class="card" aria-labelledby="src-list">
    <div class="card__header">
        <h2 class="card__title" id="src-list">Sources</h2>
        <form method="post" action="<?= e(url('admin/opportunity-sources/fetch-all')) ?>" class="inline-form">
            <?= csrf_field() ?>
            <button class="btn btn--outline btn--sm" type="submit"><?= icon('refresh', 'icon--sm') ?> Fetch all now</button>
        </form>
    </div>
    <div class="card__body">
        <?php if ($rows === []): ?>
            <p class="text-muted">No source yet. Add a website below.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table table--stack">
                    <caption class="visually-hidden">Opportunity sources</caption>
                    <thead><tr><th scope="col">Source</th><th scope="col">Last check</th><th scope="col">Items</th><th scope="col">On</th><th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td class="cell-main" data-label="Source">
                                <span class="cell-title"><?= e($r['name']) ?></span>
                                <span class="cell-sub"><?= e($r['url']) ?></span>
                            </td>
                            <td data-label="Last check">
                                <?php if ($r['last_fetched_at']): ?>
                                    <span class="nowrap"><?= e(format_datetime($r['last_fetched_at'], 'j M, H:i')) ?></span>
                                    <span class="cell-sub badge-line"><span class="badge badge--<?= $r['last_status'] === 'ok' ? 'success' : 'danger' ?>"><?= $r['last_status'] === 'ok' ? 'OK' : 'Problem' ?></span> <?= e((string) $r['last_message']) ?></span>
                                <?php else: ?><span class="text-muted">Not checked yet</span><?php endif; ?>
                            </td>
                            <td data-label="Items"><?= (int) $r['items'] ?></td>
                            <td data-label="On">
                                <form method="post" action="<?= e(url('admin/opportunity-sources/' . (int) $r['id'] . '/toggle')) ?>" class="inline-form">
                                    <?= csrf_field() ?>
                                    <button class="status-toggle<?= $r['is_active'] ? ' is-on' : '' ?>" type="submit" aria-pressed="<?= $r['is_active'] ? 'true' : 'false' ?>">
                                        <span class="badge badge--<?= $r['is_active'] ? 'success' : 'neutral' ?> badge--dot"><?= $r['is_active'] ? 'On' : 'Off' ?></span>
                                        <span class="visually-hidden"> - <?= e($r['name']) ?></span>
                                    </button>
                                </form>
                            </td>
                            <td class="col-actions">
                                <div class="table__actions">
                                    <form method="post" action="<?= e(url('admin/opportunity-sources/' . (int) $r['id'] . '/fetch')) ?>" class="inline-form">
                                        <?= csrf_field() ?>
                                        <button class="btn btn--ghost btn--icon" type="submit" title="Fetch now" aria-label="<?= e('Fetch now: ' . $r['name']) ?>"><?= icon('refresh', 'icon--sm') ?></button>
                                    </form>
                                    <details class="inline-details">
                                        <summary class="btn btn--ghost btn--icon" title="Edit" aria-label="<?= e('Edit: ' . $r['name']) ?>"><?= icon('edit', 'icon--sm') ?></summary>
                                        <form class="inline-edit" method="post" action="<?= e(url('admin/opportunity-sources/' . (int) $r['id'])) ?>">
                                            <?= csrf_field() ?>
                                            <?= partial('partials/form-field', ['name' => 'name', 'id' => 'name-' . (int) $r['id'], 'label' => 'Name', 'value' => $r['name'], 'maxlength' => 120, 'required' => true]) ?>
                                            <?= partial('partials/form-field', ['name' => 'url', 'id' => 'url-' . (int) $r['id'], 'label' => 'Feed address', 'type' => 'url', 'value' => $r['url'], 'maxlength' => 500, 'required' => true]) ?>
                                            <?= partial('partials/form-field', ['name' => 'default_category', 'id' => 'cat-' . (int) $r['id'], 'label' => 'Type when it cannot be told', 'type' => 'select', 'options' => $categories, 'value' => $r['default_category']]) ?>
                                            <label class="check"><input type="checkbox" name="is_active" value="1"<?= $r['is_active'] ? ' checked' : '' ?>><span>Read this source</span></label>
                                            <button class="btn btn--primary btn--sm" type="submit">Save</button>
                                        </form>
                                    </details>
                                    <?php if (can('content.delete')): ?>
                                        <form method="post" action="<?= e(url('admin/opportunity-sources/' . (int) $r['id'] . '/delete')) ?>" class="inline-form"
                                              data-confirm="<?= e('Delete the source "' . $r['name'] . '"? Its ' . (int) $r['items'] . ' opportunities stay on the website until they close.') ?>">
                                            <?= csrf_field() ?>
                                            <button class="btn btn--ghost btn--icon btn-danger-hover" type="submit" title="Delete" aria-label="<?= e('Delete: ' . $r['name']) ?>"><?= icon('trash', 'icon--sm') ?></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="card" aria-labelledby="src-add">
    <div class="card__header"><h2 class="card__title" id="src-add">Add a source</h2></div>
    <form class="card__body" method="post" action="<?= e(url('admin/opportunity-sources')) ?>" novalidate>
        <?= csrf_field() ?>
        <div class="form-grid">
            <?= partial('partials/form-field', ['name' => 'name', 'id' => 'new-name', 'label' => 'Name', 'required' => true, 'maxlength' => 120, 'value' => '', 'help' => 'Shown as the credit on each opportunity.']) ?>
            <?= partial('partials/form-field', ['name' => 'url', 'id' => 'new-url', 'label' => 'Feed address', 'type' => 'url', 'required' => true, 'maxlength' => 500, 'value' => '',
                'help' => 'The RSS feed of a website, often the site address followed by /feed/ , for example https://example.org/feed/']) ?>
            <?= partial('partials/form-field', ['name' => 'default_category', 'id' => 'new-cat', 'label' => 'Type when it cannot be told', 'type' => 'select', 'options' => $categories, 'value' => 'other']) ?>
        </div>
        <p class="form-note">Only public websites can be added. Please read a website only if it allows sharing its headlines; every item links back to the original.</p>
        <button class="btn btn--primary" type="submit"><?= icon('plus', 'icon--sm') ?> Add and fetch</button>
    </form>
</section>
