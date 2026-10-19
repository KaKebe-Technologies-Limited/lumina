<?php
/**
 * Admin > Partners: the fast visual screen. OWNER: PARTNERS agent (docs/PARTNERS_NEWSLETTER.md section 6).
 * Quick-add bar (name, logo by drag-and-drop or click with an instant preview, website, type) -> one click adds a partner; a card grid that shows each
 * logo exactly as the website does (same box class); show / hide switch, inline edit, replace / remove logo, delete with confirmation, bulk actions,
 * drag-and-drop + Move earlier / later buttons with automatic saving, "Optimise existing logos". All behaviour: assets/js/admin-partners.js, talking to
 * Admin\PartnerApiController (JSON). The classic full form stays at /admin/partners/create and /admin/partners/{id}/edit.
 * Vars: $partners (rows, in website order), $types, $canDelete, $total, $active, $pending, $notice, $hasGd, $aboutLive.
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-partners.js')) ?>" defer></script>
<?php $this->end();
$hidden = $total - $active;
?>
<div class="admin-org partners-admin" data-partners-admin
     data-api="<?= e(url('admin/api/partners')) ?>"
     data-about-url="<?= e(url('about') . '#partners') ?>"
     data-partners-url="<?= e(url('partners')) ?>"
     data-can-delete="<?= $canDelete ? '1' : '0' ?>">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Partners',
        'subtitle' => 'The organisations that appear in the "Our partners" section of the About page, on the Partners page and in the website footer. Changes show on the website straight away.',
        'actions'  => [
            ['label' => 'See it on the website', 'url' => url('about') . '#partners', 'class' => 'btn--outline', 'icon' => 'external-link', 'target' => '_blank'],
        ],
    ]) ?>

    <noscript><div class="alert alert--warning"><?= icon('alert-triangle') ?><div class="alert__body">Adding, hiding and arranging partners from this screen needs JavaScript. You can still add a partner with the form below, or open a partner (click its name) to edit or delete it.</div></div></noscript>

    <div class="pa-notices" data-pa-notices>
        <?php if ($notice !== null): ?>
            <div class="alert alert--warning" role="status" data-pa-capability><?= icon('alert-triangle') ?><div class="alert__body"><strong class="alert__title">About logo quality on this server</strong><?= e($notice) ?></div></div>
        <?php endif; ?>
        <div class="alert alert--info pa-optimise" data-pa-optimise<?= $pending > 0 ? '' : ' hidden' ?>>
            <?= icon('info') ?>
            <div class="alert__body">
                <strong class="alert__title" data-pa-optimise-title><?= (int) $pending ?> logo<?= $pending === 1 ? '' : 's' ?> not optimised yet</strong>
                <span data-pa-optimise-text><?= $hasGd
                    ? 'These were added before logos were optimised automatically (or while this server could not do it). They still work, but they are not trimmed, resized or compressed. One click makes them as sharp and tidy as new uploads.'
                    : 'They still work. Once the image library (GD) is available on this server, one click here will trim, resize and compress them.' ?></span>
                <?php if ($hasGd): ?>
                    <span class="pa-optimise__row">
                        <button class="btn btn--primary btn--sm" type="button" data-pa-optimise-run><?= icon('refresh', 'icon--sm') ?> Optimise existing logos</button>
                        <span class="pa-optimise__progress" data-pa-optimise-progress role="status" aria-live="polite"></span>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ------------------------------------------------------------ quick add -->
    <section class="card pa-quick" aria-labelledby="pa-quick-title">
        <div class="card__header">
            <h2 class="card__title" id="pa-quick-title">Add a partner</h2>
            <span class="pa-quick__tip">Type the name, drop in the logo, click Add. That is all.</span>
        </div>
        <form class="card__body pa-quick__form" method="post" action="<?= e(url('admin/partners')) ?>" enctype="multipart/form-data" data-pa-quick novalidate>
            <?= csrf_field() ?>
            <?= partial('admin/partners/_drop', ['id' => 'pa-quick-logo']) ?>
            <div class="pa-quick__fields">
                <div class="field">
                    <label class="field__label" for="pa-quick-name">Organisation name <span class="field__req" aria-hidden="true">*</span></label>
                    <input class="input" id="pa-quick-name" name="name" maxlength="190" required autocomplete="off" aria-describedby="pa-quick-name-help pa-quick-name-error">
                    <div class="field__help" id="pa-quick-name-help">Also used as the logo's alternative text for screen readers.</div>
                    <div class="field__error" id="pa-quick-name-error" role="alert" data-pa-error="name" hidden></div>
                </div>
                <div class="field">
                    <label class="field__label" for="pa-quick-site">Website <span class="pa-optional">(optional)</span></label>
                    <input class="input" id="pa-quick-site" name="website" maxlength="255" inputmode="url" placeholder="https://www.example.org" autocomplete="off" aria-describedby="pa-quick-site-help pa-quick-site-error">
                    <div class="field__help" id="pa-quick-site-help">If set, the logo links to this address.</div>
                    <div class="field__error" id="pa-quick-site-error" role="alert" data-pa-error="website" hidden></div>
                </div>
                <div class="field pa-quick__type">
                    <label class="field__label" for="pa-quick-type">Type</label>
                    <select class="select" id="pa-quick-type" name="partner_type">
                        <?php foreach ($types as $tv => $tl): ?><option value="<?= e($tv) ?>"><?= e($tl) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="pa-quick__go">
                    <input type="hidden" name="is_active" value="0">
                    <label class="switch">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span class="switch__track" aria-hidden="true"></span>
                        <span class="switch__text">Show on the website</span>
                    </label>
                    <button class="btn btn--primary pa-quick__add" type="submit" data-pa-add><?= icon('plus', 'icon--sm') ?> <span data-pa-add-label>Add partner</span></button>
                </div>
            </div>
        </form>
    </section>

    <!-- ------------------------------------------------------------ the partners -->
    <section class="pa-list" aria-labelledby="pa-list-title">
        <div class="pa-toolbar" data-pa-toolbar<?= $total === 0 ? ' hidden' : '' ?>>
            <div class="pa-toolbar__title">
                <h2 id="pa-list-title">Your partners</h2>
                <p class="pa-counts" role="status" aria-live="polite" data-pa-counts><?= (int) $total ?> partner<?= $total === 1 ? '' : 's' ?>: <?= (int) $active ?> on the website<?= $hidden > 0 ? ', ' . (int) $hidden . ' hidden' : '' ?></p>
            </div>
            <div class="pa-toolbar__tools">
                <label class="pa-selectall"><input type="checkbox" data-pa-select-all> <span>Select all</span></label>
                <div class="field pa-search">
                    <label class="visually-hidden" for="pa-search">Find a partner</label>
                    <input class="input" id="pa-search" type="search" placeholder="Find a partner&hellip;" autocomplete="off" data-pa-search>
                </div>
                <div class="pa-filter" role="group" aria-label="Show">
                    <button type="button" class="pa-chip is-on" data-pa-filter="all" aria-pressed="true">All</button>
                    <button type="button" class="pa-chip" data-pa-filter="on" aria-pressed="false">On the website</button>
                    <button type="button" class="pa-chip" data-pa-filter="off" aria-pressed="false">Hidden</button>
                </div>
            </div>
        </div>

        <div class="pa-bulk" data-pa-bulk hidden role="region" aria-label="Actions for the selected partners">
            <strong class="pa-bulk__count" data-pa-bulk-count>0 selected</strong>
            <span class="pa-bulk__buttons">
                <button class="btn btn--outline btn--sm" type="button" data-pa-bulk-action="show"><?= icon('eye', 'icon--sm') ?> Show on website</button>
                <button class="btn btn--outline btn--sm" type="button" data-pa-bulk-action="hide"><?= icon('eye-off', 'icon--sm') ?> Hide</button>
                <?php if ($canDelete): ?><button class="btn btn--outline btn--sm pa-bulk__delete" type="button" data-pa-bulk-action="delete"><?= icon('trash', 'icon--sm') ?> Delete</button><?php endif; ?>
                <button class="btn btn--ghost btn--sm" type="button" data-pa-bulk-clear>Clear selection</button>
            </span>
        </div>

        <ul class="pa-grid" role="list" data-pa-grid aria-label="Partners in the order they appear on the website">
            <?php foreach ($partners as $row): ?>
                <?= partial('admin/partners/_card', ['p' => $row, 'types' => $types, 'canDelete' => $canDelete]) ?>
            <?php endforeach; ?>
        </ul>

        <div class="pa-empty" data-pa-empty<?= $total === 0 ? '' : ' hidden' ?>>
            <?= partial('partials/empty-state', [
                'title' => 'No partners yet',
                'text'  => 'Add the organisations that support or work with the foundation using the form above. Until at least one partner is on the website, the "Our partners" section of the About page and the footer strip stay hidden.',
                'icon'  => 'handshake',
                'tag'   => 'h3',
            ]) ?>
        </div>
        <p class="pa-nomatch" data-pa-nomatch hidden>No partner matches your search. <button class="btn btn--link" type="button" data-pa-filter-reset>Clear the search</button></p>

        <p class="pa-order-hint" data-pa-order-hint<?= $total < 2 ? ' hidden' : '' ?>>
            <?= icon('info', 'icon--sm') ?>
            <span>The order here is the order on the website. <span class="pa-hint-drag">Drag a card by its handle (<span aria-hidden="true">&#8942;&#8942;</span>), or </span>move it with the arrow buttons. It saves by itself.
                <span class="pa-order-status" role="status" aria-live="polite" data-pa-order-status></span></span>
        </p>
    </section>

    <!-- ------------------------------------------------------------ shared bits (no JavaScript needed until used) -->
    <input class="pa-hidden-file" type="file" accept="image/png,image/jpeg,image/webp,image/gif,.png,.jpg,.jpeg,.webp,.gif" tabindex="-1" aria-hidden="true" data-pa-replace-input>

    <dialog class="pa-dialog" data-pa-dialog aria-labelledby="pa-dialog-title" aria-describedby="pa-dialog-text">
        <form method="dialog" class="pa-dialog__box">
            <h2 class="pa-dialog__title" id="pa-dialog-title" data-pa-dialog-title></h2>
            <div class="pa-dialog__preview plogo" data-pa-dialog-preview hidden></div>
            <p class="pa-dialog__text" id="pa-dialog-text" data-pa-dialog-text></p>
            <div class="pa-dialog__actions">
                <button class="btn btn--outline" value="cancel" data-pa-dialog-cancel autofocus>Cancel</button>
                <button class="btn btn--outline" value="alt" data-pa-dialog-alt hidden></button>
                <button class="btn btn--danger" value="ok" data-pa-dialog-ok></button>
            </div>
        </form>
    </dialog>

    <div class="pa-toasts" data-pa-toasts></div>
    <div class="visually-hidden" aria-live="polite" role="status" data-pa-announce></div>
</div>
