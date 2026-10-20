<?php
/**
 * Admin > Shop > Categories. OWNER: ADMIN. Vars: $rows (shop_categories + products, live counts), $uncategorised (products with no category).
 * Each row is its own small form (name, web address, order, shown). Deleting a category never deletes products: they become "no category".
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-shop.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="ash">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Shop categories',
        'subtitle' => 'Groups such as Caps or Stickers. Visitors can filter the shop by category. A category with no published product is hidden in the shop.',
        'actions'  => [['label' => 'All products', 'url' => url('admin/shop/products'), 'class' => 'btn--outline', 'icon' => 'package']],
    ]) ?>

    <section class="card" aria-labelledby="cat-new">
        <div class="card__header"><h2 class="card__title" id="cat-new">Add a category</h2></div>
        <div class="card__body">
            <form method="post" action="<?= e(url('admin/shop/categories')) ?>" class="ash-catnew" novalidate>
                <?= csrf_field() ?>
                <div class="field"><label class="field__label" for="cat-name">Name</label><input class="input" id="cat-name" name="name" maxlength="120" required placeholder="For example: Bags" autocomplete="off"></div>
                <input type="hidden" name="is_active" value="1">
                <button class="btn btn--primary" type="submit"><?= icon('plus', 'icon--sm') ?> Add category</button>
            </form>
        </div>
    </section>

    <?php if ($rows === []): ?>
        <?= partial('partials/empty-state', ['title' => 'No categories yet', 'icon' => 'tag', 'text' => 'Add one above, then choose it when you edit a product.']) ?>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table table--stack ash-cats">
                <thead><tr><th>Name</th><th>Web address</th><th class="col-num">Order</th><th>Shown</th><th class="col-num">Products</th><th class="col-actions"><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                <?php foreach ($rows as $c): $cid = (int) $c['id']; $fid = 'catf-' . $cid; ?>
                    <tr>
                        <td data-label="Name"><label class="visually-hidden" for="cn-<?= $cid ?>">Name of <?= e($c['name']) ?></label><input class="input" id="cn-<?= $cid ?>" name="name" form="<?= e($fid) ?>" value="<?= e($c['name']) ?>" maxlength="120" required></td>
                        <td data-label="Web address"><label class="visually-hidden" for="cs-<?= $cid ?>">Web address of <?= e($c['name']) ?></label><input class="input input--code" id="cs-<?= $cid ?>" name="slug" form="<?= e($fid) ?>" value="<?= e($c['slug']) ?>" maxlength="120"></td>
                        <td class="col-num" data-label="Order"><label class="visually-hidden" for="co-<?= $cid ?>">Position of <?= e($c['name']) ?></label><input class="input ash-num" id="co-<?= $cid ?>" name="sort_order" form="<?= e($fid) ?>" value="<?= (int) $c['sort_order'] ?>" inputmode="numeric"></td>
                        <td data-label="Shown">
                            <input type="hidden" name="is_active" value="0" form="<?= e($fid) ?>">
                            <label class="switch"><input type="checkbox" name="is_active" value="1" form="<?= e($fid) ?>"<?= (int) $c['is_active'] === 1 ? ' checked' : '' ?>><span class="switch__track" aria-hidden="true"></span><span class="switch__text visually-hidden">Show <?= e($c['name']) ?> in the shop</span></label>
                        </td>
                        <td class="col-num" data-label="Products"><?= (int) $c['live'] ?> live<span class="cell-sub"><?= (int) $c['products'] ?> in total</span></td>
                        <td class="col-actions">
                            <form id="<?= e($fid) ?>" method="post" action="<?= e(url('admin/shop/categories/' . $cid)) ?>" class="table__actions"><?= csrf_field() ?>
                                <button class="btn btn--outline btn--sm" type="submit">Save<span class="visually-hidden"> <?= e($c['name']) ?></span></button>
                                <button class="btn btn--ghost btn--sm btn--icon ash-danger" type="submit" formaction="<?= e(url('admin/shop/categories/' . $cid . '/delete')) ?>" data-confirm="Delete the category &quot;<?= e($c['name']) ?>&quot;? Its <?= (int) $c['products'] ?> product(s) are kept and will have no category." title="Delete"><?= icon('trash', 'icon--sm') ?><span class="visually-hidden">Delete <?= e($c['name']) ?></span></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="ash-hint">Smaller numbers come first. <?= (int) $uncategorised > 0 ? (int) $uncategorised . ' product' . ($uncategorised === 1 ? ' has' : 's have') . ' no category.' : '' ?></p>
    <?php endif; ?>
</div>
