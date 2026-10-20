<?php
/**
 * Admin > Shop > Products (list). OWNER: ADMIN. Vars: $pager (items: shop_products + category_name, cover_path, picture_count), $q, $status, $stock, $category, $featured, $sort, $dir,
 * $filtered, $counts, $categories, $samples, $currency, $shopOpen. Quick changes (feature, publish, stock) post to the toggle endpoint; admin-shop.js sends them in the background.
 */
use App\Services\Shop\Money;
use App\Services\Shop\ProductService;

$back = url('admin/shop/products') . (($_SERVER['QUERY_STRING'] ?? '') !== '' ? '?' . $_SERVER['QUERY_STRING'] : '');
$backPath = (string) parse_url($back, PHP_URL_PATH) . (($qs = (string) parse_url($back, PHP_URL_QUERY)) !== '' ? '?' . $qs : '');
$sortLink = static function (string $key, string $label) use ($sort, $dir): string {
    $next = ($sort === $key && $dir === 'asc') ? 'desc' : 'asc';
    $params = array_filter(['q' => $_GET['q'] ?? '', 'status' => $_GET['status'] ?? '', 'category' => $_GET['category'] ?? '', 'stock' => $_GET['stock'] ?? '', 'featured' => $_GET['featured'] ?? '', 'sort' => $key, 'dir' => $next], static fn($v): bool => $v !== '');
    $arrow = $sort === $key ? ($dir === 'asc' ? ' &uarr;' : ' &darr;') : '';
    return '<a class="ash-sort" href="' . e(url('admin/shop/products', $params)) . '">' . e($label) . $arrow . '</a>';
};
$tabUrl = static fn(string $s): string => url('admin/shop/products', array_filter(['status' => $s, 'q' => $_GET['q'] ?? '', 'category' => $_GET['category'] ?? '', 'stock' => $_GET['stock'] ?? '']));
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-shop.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="ash">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Shop products',
        'subtitle' => 'The jumpers, caps, T-shirts and stickers visitors can buy. Only products that are Published appear in the shop.',
        'actions'  => [
            ['label' => 'Add a product', 'url' => url('admin/shop/products/create'), 'icon' => 'plus'],
            ['label' => 'Categories', 'url' => url('admin/shop/categories'), 'class' => 'btn--outline', 'icon' => 'tag'],
            ['label' => 'View the shop', 'url' => url('shop'), 'class' => 'btn--ghost', 'icon' => 'external-link', 'target' => '_blank'],
        ],
    ]) ?>

    <?php if ($samples > 0): ?>
        <div class="alert alert--info" role="status"><?= icon('info') ?><div class="alert__body"><strong class="alert__title">Sample products are placeholders.</strong>
            <?= (int) $samples ?> product<?= $samples === 1 ? ' is a sample' : 's are samples' ?>: the names, prices and pictures were made up to show how the shop looks. Open each one, replace the name, price and photos, and the "Sample" label goes away. Delete the ones you do not need.</div></div>
    <?php endif; ?>

    <nav class="tabs" aria-label="Product status">
        <a class="tabs__item<?= $status === '' ? ' is-active' : '' ?>" href="<?= e($tabUrl('')) ?>"<?= $status === '' ? ' aria-current="page"' : '' ?>>All <span class="pe-group__count"><?= (int) $counts['all'] ?></span></a>
        <a class="tabs__item<?= $status === 'published' ? ' is-active' : '' ?>" href="<?= e($tabUrl('published')) ?>"<?= $status === 'published' ? ' aria-current="page"' : '' ?>>Published <span class="pe-group__count"><?= (int) $counts['published'] ?></span></a>
        <a class="tabs__item<?= $status === 'draft' ? ' is-active' : '' ?>" href="<?= e($tabUrl('draft')) ?>"<?= $status === 'draft' ? ' aria-current="page"' : '' ?>>Drafts <span class="pe-group__count"><?= (int) $counts['draft'] ?></span></a>
        <a class="tabs__item<?= $status === 'archived' ? ' is-active' : '' ?>" href="<?= e($tabUrl('archived')) ?>"<?= $status === 'archived' ? ' aria-current="page"' : '' ?>>Archived <span class="pe-group__count"><?= (int) $counts['archived'] ?></span></a>
    </nav>

    <form class="filters" method="get" action="<?= e(url('admin/shop/products')) ?>" role="search">
        <?php if ($status !== ''): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
        <div class="field field--grow"><label class="field__label" for="pf-q">Search</label><input class="input" id="pf-q" type="search" name="q" value="<?= e($q) ?>" placeholder="Name, code or web address" maxlength="100"></div>
        <div class="field">
            <label class="field__label" for="pf-cat">Category</label>
            <select class="select" id="pf-cat" name="category" data-autosubmit>
                <option value="">Any</option>
                <?php foreach ($categories as $c): ?><option value="<?= (int) $c['id'] ?>"<?= (string) $category === (string) $c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
                <option value="none"<?= $category === 'none' ? ' selected' : '' ?>>No category</option>
            </select>
        </div>
        <div class="field">
            <label class="field__label" for="pf-stock">Stock</label>
            <select class="select" id="pf-stock" name="stock" data-autosubmit>
                <option value="">Any</option>
                <option value="low"<?= $stock === 'low' ? ' selected' : '' ?>>Running low</option>
                <option value="out"<?= $stock === 'out' ? ' selected' : '' ?>>Sold out</option>
                <option value="unlimited"<?= $stock === 'unlimited' ? ' selected' : '' ?>>Not counted</option>
            </select>
        </div>
        <div class="field"><label class="check ash-check"><input type="checkbox" name="featured" value="1"<?= $featured ? ' checked' : '' ?> data-autosubmit-check> <span>Featured only</span></label></div>
        <div class="filters__actions"><button class="btn btn--primary" type="submit">Search</button><?php if ($filtered): ?><a class="btn btn--ghost" href="<?= e(url('admin/shop/products')) ?>">Clear</a><?php endif; ?></div>
    </form>
    <p class="result-count"><?= (int) $pager->total ?> product<?= $pager->total === 1 ? '' : 's' ?><?= $filtered ? ' match your filters' : '' ?>.</p>

    <?php if ($pager->items === []): ?>
        <?= partial('partials/empty-state', ['title' => $filtered ? 'No products match' : 'No products yet', 'icon' => 'package',
            'text' => $filtered ? 'Try other filters.' : 'Add the first product: a name, a price and a photo are enough to start.',
            'actions' => $filtered ? [] : [['label' => 'Add a product', 'url' => url('admin/shop/products/create')]]]) ?>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table table--stack ash-products">
                <thead><tr>
                    <th><span class="visually-hidden">Picture</span></th>
                    <th><?= $sortLink('name', 'Product') ?></th>
                    <th>Category</th>
                    <th class="col-num"><?= $sortLink('price', 'Price') ?></th>
                    <th><?= $sortLink('stock', 'Stock') ?></th>
                    <th>Status</th>
                    <th class="col-actions"><span class="visually-hidden">Actions</span></th>
                </tr></thead>
                <tbody>
                <?php foreach ($pager->items as $p):
                    $pid = (int) $p['id'];
                    $price = ProductService::priceInfo($p);
                    $stockInfo = ProductService::stockInfo($p);
                    $edit = url('admin/shop/products/' . $pid . '/edit');
                    $tog = url('admin/shop/products/' . $pid . '/toggle');
                ?>
                    <tr data-product="<?= $pid ?>">
                        <td data-label="Picture"><?php if (!empty($p['cover_path'])): ?><img class="ash-thumb" src="<?= e(img_url((string) $p['cover_path'])) ?>" alt="" width="52" height="52" loading="lazy"><?php else: ?><span class="ash-thumb ash-thumb--empty" title="No picture yet"><?= icon('image', 'icon--sm') ?></span><?php endif; ?></td>
                        <td data-label="Product">
                            <a class="cell-title" href="<?= e($edit) ?>"><?= e($p['name']) ?></a>
                            <span class="cell-sub">
                                <?php if ((int) $p['is_sample'] === 1): ?><span class="ash-sample-tag">Sample product - edit or delete</span><?php endif; ?>
                                <?php if ((string) ($p['sku'] ?? '') !== ''): ?>Code <?= e($p['sku']) ?> &middot; <?php endif; ?><?= (int) $p['picture_count'] ?> picture<?= (int) $p['picture_count'] === 1 ? '' : 's' ?>
                                <?php if ((int) $p['allow_booking'] === 1): ?> &middot; can be booked<?php endif; ?>
                            </span>
                        </td>
                        <td data-label="Category"><?= e((string) ($p['category_name'] ?? '')) !== '' ? e($p['category_name']) : '<span class="cell-sub">None</span>' ?></td>
                        <td class="col-num" data-label="Price">
                            <?php if ($price['on_sale']): ?><strong><?= e(Money::format($price['unit'], $currency)) ?></strong><span class="cell-sub"><s><?= e(Money::format($price['regular'], $currency)) ?></s> on sale</span>
                            <?php else: ?><?= e(Money::format($price['regular'], $currency)) ?><?php if ($price['sale'] !== null): ?><span class="cell-sub">sale set, not running</span><?php endif; ?><?php endif; ?>
                        </td>
                        <td data-label="Stock">
                            <form class="ash-stockform" method="post" action="<?= e($tog) ?>" data-quick>
                                <?= csrf_field() ?><input type="hidden" name="field" value="stock"><input type="hidden" name="_back" value="<?= e($backPath) ?>">
                                <label class="visually-hidden" for="stk-<?= $pid ?>">Stock of <?= e($p['name']) ?></label>
                                <?php if ($stockInfo['tracked']): ?>
                                    <input class="input ash-stock" id="stk-<?= $pid ?>" name="qty" type="number" min="0" max="1000000" inputmode="numeric" value="<?= (int) $stockInfo['qty'] ?>">
                                    <button class="btn btn--ghost btn--sm" type="submit">Save</button>
                                    <?php if ($stockInfo['state'] === 'out'): ?><span class="badge badge--danger">Sold out</span><?php elseif ($stockInfo['state'] === 'low'): ?><span class="badge badge--warning">Few left</span><?php endif; ?>
                                <?php else: ?>
                                    <span class="cell-sub">Not counted</span>
                                    <input id="stk-<?= $pid ?>" name="qty" type="hidden" value="">
                                <?php endif; ?>
                            </form>
                        </td>
                        <td data-label="Status">
                            <?= partial('partials/admin/status-badge', ['status' => $p['status'], 'label' => $p['status'] === 'published' ? 'Published' : ($p['status'] === 'draft' ? 'Draft' : 'Archived')]) ?>
                            <?php if ((int) $p['is_featured'] === 1): ?><span class="badge badge--gold">Featured</span><?php endif; ?>
                        </td>
                        <td class="col-actions">
                            <div class="table__actions ash-rowtools">
                                <a class="btn btn--outline btn--sm" href="<?= e($edit) ?>"><?= icon('edit', 'icon--sm') ?> Edit<span class="visually-hidden"> <?= e($p['name']) ?></span></a>
                                <form method="post" action="<?= e($tog) ?>" data-quick><?= csrf_field() ?><input type="hidden" name="field" value="status"><input type="hidden" name="value" value="<?= $p['status'] === 'published' ? 'draft' : 'published' ?>"><input type="hidden" name="_back" value="<?= e($backPath) ?>">
                                    <button class="btn btn--ghost btn--sm" type="submit"><?= $p['status'] === 'published' ? 'Hide' : 'Publish' ?><span class="visually-hidden"> <?= e($p['name']) ?></span></button></form>
                                <form method="post" action="<?= e($tog) ?>" data-quick><?= csrf_field() ?><input type="hidden" name="field" value="featured"><input type="hidden" name="_back" value="<?= e($backPath) ?>">
                                    <button class="btn btn--ghost btn--sm btn--icon" type="submit" aria-pressed="<?= (int) $p['is_featured'] === 1 ? 'true' : 'false' ?>" title="<?= (int) $p['is_featured'] === 1 ? 'Featured: click to stop featuring' : 'Feature this product' ?>"><?= icon('star', 'icon--sm' . ((int) $p['is_featured'] === 1 ? ' is-on' : '')) ?><span class="visually-hidden"><?= (int) $p['is_featured'] === 1 ? 'Stop featuring ' : 'Feature ' ?><?= e($p['name']) ?></span></button></form>
                                <form method="post" action="<?= e(url('admin/shop/products/' . $pid . '/duplicate')) ?>"><?= csrf_field() ?>
                                    <button class="btn btn--ghost btn--sm btn--icon" type="submit" title="Make a copy"><?= icon('copy', 'icon--sm') ?><span class="visually-hidden">Copy <?= e($p['name']) ?></span></button></form>
                                <?php if (can('shop.delete')): ?>
                                    <form method="post" action="<?= e(url('admin/shop/products/' . $pid . '/delete')) ?>" data-confirm="Delete &quot;<?= e($p['name']) ?>&quot; for good? Past orders keep their own copy of the item. You can also just hide it."><?= csrf_field() ?>
                                        <button class="btn btn--ghost btn--sm btn--icon ash-danger" type="submit" title="Delete"><?= icon('trash', 'icon--sm') ?><span class="visually-hidden">Delete <?= e($p['name']) ?></span></button></form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= partial('partials/pagination', ['pagination' => $pager]) ?>
    <?php endif; ?>
    <div class="visually-hidden" role="status" aria-live="polite" data-ash-live></div>
</div>
