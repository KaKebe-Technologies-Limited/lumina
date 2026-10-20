<?php
/**
 * Admin > Shop > Product form (create + edit). OWNER: ADMIN. Vars: $product (row or defaults), $images (ProductImages::view rows), $isEdit, $categories, $groups (option groups),
 * $currency, $decimals, $maxPics, $maxGroups, $maxValues, $gd, $canDelete, $orderLines. Field names match ShopProductsController::collect().
 * Pictures: with JavaScript each file is uploaded at once (progress, drag to re-order, cover, alt text); without it the "pictures" file field is posted with the form.
 */
use App\Services\Shop\Money;

$val = static fn(string $key, mixed $default = '') => old($key, $product[$key] ?? $default);
$cur = (string) $currency;
$money = static function (string $key) use ($product, $cur): string {
    $posted = old($key, null);
    if ($posted !== null) { return (string) $posted; }
    $v = $product[$key] ?? '';
    return $v === '' || $v === null ? '' : Money::format(Money::fromDb($v), $cur, false);
};
$dt = static function (string $key) use ($product): string {
    $posted = old($key, null);
    if ($posted !== null) { return (string) $posted; }
    $v = $product[$key] ?? '';
    return $v ? date('Y-m-d\TH:i', (int) strtotime((string) $v)) : '';
};
$id = $isEdit ? (int) $product['id'] : 0;
$action = $isEdit ? url('admin/shop/products/' . $id) : url('admin/shop/products');
$track = (int) old('track_stock', $product['track_stock'] ?? 0) === 1;
$status = (string) old('status', $product['status'] ?? 'draft');
$isSample = $isEdit && (int) ($product['is_sample'] ?? 0) === 1;
$groupsShown = $groups;
// the stock numbers as they are NOW: the server applies only the change made on this form on top of the live numbers (orders may take units while the form is open)
$stockSnap = ['qty' => $isEdit && (int) ($product['track_stock'] ?? 0) === 1 ? (int) ($product['stock_qty'] ?? 0) : null, 'opts' => []];
if ($isEdit) {
    foreach (\App\Services\Shop\ProductService::parseOptions($product['options'] ?? null) as $sg) {
        foreach ($sg['values'] as $sv) {
            if ($sv['stock'] !== null) {
                $stockSnap['opts'][$sg['key'] . '|' . $sv['value']] = (int) $sv['stock'];
            }
        }
    }
}
if (count($groupsShown) < $maxGroups) { $groupsShown[] = ['key' => '', 'label' => '', 'required' => true, 'values' => []]; }
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-editor.js')) ?>" defer></script>
<script src="<?= e(asset('js/admin-shop.js')) ?>" defer></script>
<?php $this->end();

/** one option value row (also used for the <template>) */
$valueRow = static function (string $g, string $v, array $val = []): string {
    return '<div class="ash-opt__row" data-opt-value>'
        . '<input type="hidden" name="options[' . e($g) . '][values][' . e($v) . '][value]" value="' . e((string) ($val['value'] ?? '')) . '">'
        . '<div class="field"><label class="visually-hidden" for="ov-' . e($g) . '-' . e($v) . '">Choice</label><input class="input" id="ov-' . e($g) . '-' . e($v) . '" name="options[' . e($g) . '][values][' . e($v) . '][label]" value="' . e((string) ($val['label'] ?? '')) . '" maxlength="40" placeholder="For example: M" autocomplete="off"></div>'
        . '<div class="field"><label class="visually-hidden" for="os-' . e($g) . '-' . e($v) . '">How many in stock (optional)</label><input class="input" id="os-' . e($g) . '-' . e($v) . '" name="options[' . e($g) . '][values][' . e($v) . '][stock]" value="' . e((string) ($val['stock'] ?? '')) . '" inputmode="numeric" placeholder="Stock" autocomplete="off"></div>'
        . '<div class="field"><label class="visually-hidden" for="oc-' . e($g) . '-' . e($v) . '">Colour swatch (optional)</label><input class="input ash-swatch" id="oc-' . e($g) . '-' . e($v) . '" type="color" value="' . e((string) (($val['swatch'] ?? '') !== '' ? $val['swatch'] : '#2C3950')) . '" data-swatch-picker><input type="hidden" name="options[' . e($g) . '][values][' . e($v) . '][swatch]" value="' . e((string) ($val['swatch'] ?? '')) . '" data-swatch-value></div>'
        . '<button class="btn btn--ghost btn--sm btn--icon" type="button" data-remove-value title="Remove this choice"><span class="visually-hidden">Remove this choice</span>' . icon('x', 'icon--sm') . '</button>'
        . '</div>';
};
$groupBlock = static function (string $g, array $grp, int $maxValues) use ($valueRow): string {
    $values = $grp['values'];
    $html = '<fieldset class="ash-opt" data-opt-group data-index="' . e($g) . '"><legend class="visually-hidden">Option group' . ((string) $grp['label'] !== '' ? ': ' . e((string) $grp['label']) : '') . '</legend>'
        . '<div class="ash-opt__head">'
        . '<input type="hidden" name="options[' . e($g) . '][key]" value="' . e((string) $grp['key']) . '">'
        . '<div class="field"><label class="field__label" for="og-' . e($g) . '">Name of the option</label><input class="input" id="og-' . e($g) . '" name="options[' . e($g) . '][label]" value="' . e((string) $grp['label']) . '" maxlength="40" placeholder="For example: Size or Colour" autocomplete="off"></div>'
        . '<label class="check ash-check"><input type="checkbox" name="options[' . e($g) . '][required]" value="1"' . (!empty($grp['required']) ? ' checked' : '') . '> <span>The customer must choose</span></label>'
        . '<button class="btn btn--ghost btn--sm" type="button" data-remove-group>' . icon('trash', 'icon--sm') . ' Remove this option</button>'
        . '</div>'
        . '<div class="ash-opt__values" data-opt-values>'
        . '<div class="ash-opt__cols" aria-hidden="true"><span>Choice</span><span>Stock (optional)</span><span>Swatch</span><span></span></div>';
    $i = 0;
    foreach ($values as $val) { $html .= $valueRow($g, (string) $i, $val); $i++; }
    for ($k = 0; $k < 2 || $i < 1; $k++) { $html .= $valueRow($g, (string) $i, []); $i++; }
    $html .= '</div><button class="btn btn--outline btn--sm" type="button" data-add-value data-next="' . (int) $i . '" data-max="' . (int) $maxValues . '">' . icon('plus', 'icon--sm') . ' Add a choice</button></fieldset>';
    return $html;
};
?>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" novalidate data-unsaved-guard data-ash-product<?= $isEdit ? ' data-product-id="' . $id . '" data-images-url="' . e(url('admin/shop/products/' . $id . '/images')) . '"' : '' ?>>
    <?= csrf_field() ?>
    <?php /* the FIRST submit button of a form is what Enter in a text field presses: it must be Save, never a picture's Delete button further down */ ?>
    <button class="visually-hidden" type="submit" tabindex="-1" aria-hidden="true">Save</button>
    <?= partial('partials/admin/page-header', [
        'title'    => $isEdit ? 'Edit product' : 'Add a product',
        'subtitle' => $isEdit ? (string) $product['name'] : 'A name, a price and a photo are enough to start. You can add details later.',
        'actions'  => array_values(array_filter([
            $isEdit && $status === 'published' ? ['label' => 'View in the shop', 'url' => url('shop/' . $product['slug']), 'class' => 'btn--outline', 'icon' => 'external-link', 'target' => '_blank'] : null,
            ['label' => 'All products', 'url' => url('admin/shop/products'), 'class' => 'btn--ghost', 'icon' => 'arrow-left'],
        ])),
    ]) ?>
    <?php if ($isSample): ?>
        <div class="alert alert--info" role="status"><?= icon('info') ?><div class="alert__body"><strong class="alert__title">This is a sample product.</strong> The name, price, texts and pictures are placeholders. Replace them with your real item, then tick "This is now my real product" below to remove the Sample label.</div></div>
    <?php endif; ?>

    <div class="card-grid card-grid--main-aside">
        <div>
            <section class="card" aria-labelledby="pf-basics">
                <div class="card__header"><h2 class="card__title" id="pf-basics">The product</h2><span class="required-note"><span aria-hidden="true">*</span> required</span></div>
                <div class="card__body">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'name', 'label' => 'Product name', 'required' => true, 'value' => $val('name'), 'maxlength' => 190, 'class' => 'field--full', 'autocomplete' => 'off', 'placeholder' => 'For example: Lumina Cap']) ?>
                        <?= partial('partials/form-field', ['name' => 'slug', 'label' => 'Web address (optional)', 'value' => $val('slug'), 'maxlength' => 190, 'autocomplete' => 'off', 'attrs' => ['data-slug-source' => '#f-name'], 'help' => 'Made from the name when empty. It is the end of the product link: ' . url('shop') . '/your-address']) ?>
                        <?= partial('partials/form-field', ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'value' => (string) $val('category_id'), 'empty' => 'No category', 'options' => array_column($categories, 'name', 'id'), 'help' => 'Customers can browse the shop by category.']) ?>
                        <?= partial('partials/form-field', ['name' => 'summary', 'label' => 'Short description', 'type' => 'textarea', 'rows' => 3, 'value' => $val('summary'), 'maxlength' => 400, 'class' => 'field--full', 'help' => 'One or two sentences shown under the name (up to 400 characters).']) ?>
                        <div class="field--full">
                            <?= partial('partials/admin/richtext-field', ['name' => 'description', 'label' => 'Full description (optional)', 'value' => (string) $val('description'), 'rows' => 10, 'help' => 'Material, fit, care, anything a buyer wants to know.']) ?>
                        </div>
                        <?= partial('partials/form-field', ['name' => 'details', 'label' => 'Key details (optional)', 'type' => 'textarea', 'rows' => 4, 'value' => $val('details'), 'class' => 'field--full', 'help' => 'One short point per line, shown as a tick list. For example: 100% cotton.']) ?>
                    </div>
                </div>
            </section>

            <section class="card" aria-labelledby="pf-pics">
                <div class="card__header"><h2 class="card__title" id="pf-pics">Pictures</h2><span class="required-note">Up to <?= (int) $maxPics ?></span></div>
                <div class="card__body">
                    <?php if (error('pictures')): ?><div class="field__error" role="alert"><?= e(error('pictures')) ?></div><?php endif; ?>
                    <?php if ($isEdit): ?>
                        <ul class="ash-pics" data-ash-pics data-reorder-url="<?= e(url('admin/shop/products/' . $id . '/images/reorder')) ?>" aria-label="Pictures of this product">
                            <?php foreach ($images as $i => $img): $iid = (int) $img['id']; ?>
                                <li class="ash-pic" data-image-id="<?= $iid ?>" data-update-url="<?= e(url('admin/shop/products/' . $id . '/images/' . $iid)) ?>" data-delete-url="<?= e(url('admin/shop/products/' . $id . '/images/' . $iid . '/delete')) ?>" draggable="true">
                                    <div class="ash-pic__media"><img src="<?= e($img['src']) ?>" alt="" width="120" height="120" loading="lazy"><span class="ash-pic__cover<?= $img['is_cover'] ? '' : ' is-hidden' ?>" data-cover-badge>Main picture</span></div>
                                    <input type="hidden" name="image_order[]" value="<?= $iid ?>">
                                    <div class="field"><label class="field__label" for="alt-<?= $iid ?>">Describe the picture</label><input class="input" id="alt-<?= $iid ?>" name="image_alt[<?= $iid ?>]" value="<?= e($img['alt']) ?>" maxlength="200" data-pic-alt><div class="field__help">Read out for people who cannot see it.</div></div>
                                    <div class="ash-pic__tools">
                                        <label class="check ash-check"><input type="radio" name="cover_id" value="<?= $iid ?>"<?= $img['is_cover'] ? ' checked' : '' ?> data-pic-cover> <span>Main picture</span></label>
                                        <span class="ash-pic__move">
                                            <button class="repeater__tool" type="button" data-pic-up aria-label="Move picture <?= $i + 1 ?> earlier" hidden><?= icon('arrow-up', 'icon--sm') ?></button>
                                            <button class="repeater__tool" type="button" data-pic-down aria-label="Move picture <?= $i + 1 ?> later" hidden><?= icon('arrow-down', 'icon--sm') ?></button>
                                        </span>
                                        <button class="btn btn--ghost btn--sm ash-danger" type="submit" formaction="<?= e(url('admin/shop/products/' . $id . '/images/' . $iid . '/delete')) ?>" data-pic-delete data-confirm="Delete this picture?"><?= icon('trash', 'icon--sm') ?> Delete</button>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if ($images === []): ?><p class="ash-hint" data-pics-empty>No pictures yet. The shop shows a grey box until you add one.</p><?php endif; ?>
                    <?php endif; ?>
                    <div class="ash-upload" data-ash-upload>
                        <div class="form-grid">
                            <div class="field">
                                <label class="field__label" for="pf-files"><?= $isEdit ? 'Add pictures' : 'Pictures' ?></label>
                                <input class="input" id="pf-files" type="file" name="pictures[]" multiple accept="image/jpeg,image/png,image/webp,image/gif" data-pic-files>
                                <div class="field__help">JPG, PNG, WebP or GIF. <?= $isEdit ? 'They upload as soon as you choose them.' : 'They are added when you save the product.' ?><?= $gd ? ' Photos are resized and made light for fast pages.' : ' This server cannot resize pictures, so keep them under 1 MB.' ?></div>
                            </div>
                            <div class="field">
                                <label class="field__label" for="pf-crop">Shape in the shop</label>
                                <select class="select" id="pf-crop" name="crop" data-pic-crop>
                                    <option value="square">Square (best for the shop grid)</option>
                                    <option value="portrait">Portrait (4 to 5)</option>
                                    <option value="none">Keep the original shape</option>
                                </select>
                            </div>
                            <?= partial('partials/form-field', ['name' => 'new_alt', 'label' => 'Describe new pictures (optional)', 'value' => '', 'maxlength' => 200, 'class' => 'field--full', 'help' => 'Leave empty to use the product name.', 'attrs' => ['data-pic-newalt' => true]]) ?>
                        </div>
                        <ul class="ash-upload__list" data-upload-list aria-live="polite"></ul>
                    </div>
                </div>
            </section>

            <section class="card" aria-labelledby="pf-price">
                <div class="card__header"><h2 class="card__title" id="pf-price">Price</h2></div>
                <div class="card__body">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'price', 'label' => 'Price (' . $cur . ')', 'required' => true, 'value' => $money('price'), 'attrs' => ['inputmode' => 'decimal'], 'autocomplete' => 'off', 'placeholder' => $decimals === 0 ? 'For example: 35000' : 'For example: 35.00', 'help' => 'What one item costs. The shop currency is ' . $cur . ' (change it in Payments).']) ?>
                        <?= partial('partials/form-field', ['name' => 'sale_price', 'label' => 'Sale price (optional)', 'value' => $money('sale_price'), 'attrs' => ['inputmode' => 'decimal'], 'autocomplete' => 'off', 'help' => 'Lower than the price. The old price is shown crossed out.']) ?>
                        <?= partial('partials/form-field', ['name' => 'sale_starts', 'label' => 'Sale starts (optional)', 'type' => 'datetime-local', 'value' => $dt('sale_starts'), 'help' => 'Empty = starts now.']) ?>
                        <?= partial('partials/form-field', ['name' => 'sale_ends', 'label' => 'Sale ends (optional)', 'type' => 'datetime-local', 'value' => $dt('sale_ends'), 'help' => 'Empty = until you remove the sale price.']) ?>
                    </div>
                </div>
            </section>

            <section class="card" aria-labelledby="pf-stock">
                <div class="card__header"><h2 class="card__title" id="pf-stock">Stock</h2></div>
                <div class="card__body">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'sku', 'label' => 'Product code (optional)', 'value' => $val('sku'), 'maxlength' => 60, 'autocomplete' => 'off', 'help' => 'Your own code, for example CAP-NAVY. Shown on packing slips.']) ?>
                        <div class="field">
                            <span class="field__label">Counting</span>
                            <input type="hidden" name="track_stock" value="0">
                            <label class="switch"><input type="checkbox" name="track_stock" value="1"<?= $track ? ' checked' : '' ?> data-track-stock><span class="switch__track" aria-hidden="true"></span><span class="switch__text">Count how many are in stock</span></label>
                            <div class="field__help">When on, the shop stops selling at 0 and shows "Few left" when it runs low. When off, the product is always available.</div>
                        </div>
                    </div>
                    <?php if ($isEdit): ?><input type="hidden" name="stock_snapshot" value="<?= e((string) old('stock_snapshot', (string) json_encode($stockSnap))) ?>"><?php endif; ?>
                    <div class="form-grid" data-stock-fields>
                        <?= partial('partials/form-field', ['name' => 'stock_qty', 'label' => 'How many in stock', 'type' => 'number', 'value' => $val('stock_qty'), 'min' => 0, 'step' => 1, 'help' => '0 means sold out.']) ?>
                        <?= partial('partials/form-field', ['name' => 'low_stock', 'label' => 'Show "Few left" at or below', 'type' => 'number', 'value' => $val('low_stock', 5), 'min' => 0, 'step' => 1]) ?>
                    </div>
                </div>
            </section>

            <section class="card" aria-labelledby="pf-options">
                <div class="card__header"><h2 class="card__title" id="pf-options">Sizes, colours and other choices</h2><span class="required-note">Optional</span></div>
                <div class="card__body">
                    <p class="ash-hint">Add an option such as Size (S, M, L) or Colour. The customer picks one choice per option. You can give a choice its own stock (for example 5 of size M); leave it empty to share the product stock. A colour swatch shows a small coloured dot.</p>
                    <?php if (error('options')): ?><div class="field__error" role="alert"><?= e(error('options')) ?></div><?php endif; ?>
                    <div class="ash-opts" data-opts data-max-groups="<?= (int) $maxGroups ?>" data-max-values="<?= (int) $maxValues ?>">
                        <?php foreach ($groupsShown as $gi => $grp): ?><?= $groupBlock((string) $gi, $grp, $maxValues) ?><?php endforeach; ?>
                    </div>
                    <button class="btn btn--outline" type="button" data-add-group data-next="<?= count($groupsShown) ?>"><?= icon('plus', 'icon--sm') ?> Add another option</button>
                    <template data-tpl-group><?= $groupBlock('__G__', ['key' => '', 'label' => '', 'required' => true, 'values' => []], $maxValues) ?></template>
                    <template data-tpl-value><?= $valueRow('__G__', '__V__', []) ?></template>
                </div>
            </section>
        </div>

        <div class="ash-aside">
            <section class="card" aria-labelledby="pf-publish">
                <div class="card__header"><h2 class="card__title" id="pf-publish">Visibility</h2></div>
                <div class="card__body">
                    <?= partial('partials/form-field', ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'value' => $status, 'options' => ['draft' => 'Draft (hidden)', 'published' => 'Published (visible in the shop)', 'archived' => 'Archived (hidden, kept)']]) ?>
                    <div>
                        <input type="hidden" name="is_featured" value="0">
                        <label class="switch"><input type="checkbox" name="is_featured" value="1"<?= (int) old('is_featured', $product['is_featured'] ?? 0) === 1 ? ' checked' : '' ?>><span class="switch__track" aria-hidden="true"></span><span class="switch__text">Featured</span></label>
                        <p class="form-note">Featured products are shown first.</p>
                    </div>
                    <div>
                        <input type="hidden" name="allow_booking" value="0">
                        <label class="switch"><input type="checkbox" name="allow_booking" value="1"<?= (int) old('allow_booking', $product['allow_booking'] ?? 0) === 1 ? ' checked' : '' ?>><span class="switch__track" aria-hidden="true"></span><span class="switch__text">Can be booked</span></label>
                        <p class="form-note">Adds "Book / reserve this item": the customer reserves it and pays later. The "Book now, pay later" method must be on in Payments.</p>
                    </div>
                    <?php if ($isSample): ?>
                        <label class="check ash-check"><input type="checkbox" name="not_sample" value="1"<?= old('not_sample') ? ' checked' : '' ?>> <span>This is now my real product (remove the Sample label)</span></label>
                    <?php endif; ?>
                </div>
            </section>

            <section class="card" aria-labelledby="pf-seo">
                <div class="card__header"><h2 class="card__title" id="pf-seo">Search engines</h2></div>
                <div class="card__body">
                    <?= partial('partials/form-field', ['name' => 'seo_title', 'label' => 'Title in search results (optional)', 'value' => $val('seo_title'), 'maxlength' => 190, 'help' => 'Empty = the product name.']) ?>
                    <?= partial('partials/form-field', ['name' => 'seo_description', 'label' => 'Description in search results (optional)', 'type' => 'textarea', 'rows' => 3, 'value' => $val('seo_description'), 'maxlength' => 300, 'help' => 'Empty = the short description.']) ?>
                </div>
            </section>

            <?php if ($isEdit): ?>
                <section class="card" aria-labelledby="pf-more">
                    <div class="card__header"><h2 class="card__title" id="pf-more">More</h2></div>
                    <div class="card__body ash-actions">
                        <button class="btn btn--outline btn--block" type="submit" formaction="<?= e(url('admin/shop/products/' . $id . '/duplicate')) ?>" data-confirm="Make a copy of this product? Unsaved changes on this page are not copied."><?= icon('copy', 'icon--sm') ?> Make a copy</button>
                        <?php if ($orderLines > 0): ?><p class="form-note">Ordered <?= (int) $orderLines ?> time<?= $orderLines === 1 ? '' : 's' ?>. Past orders keep their own copy of the item.</p><?php endif; ?>
                        <?php if ($canDelete): ?><button class="btn btn--ghost btn--block ash-danger" type="submit" formaction="<?= e(url('admin/shop/products/' . $id . '/delete')) ?>" data-confirm="Delete this product for good? Its pictures are removed too. You can also set it to Archived instead."><?= icon('trash', 'icon--sm') ?> Delete this product</button><?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-actions">
        <button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> <?= $isEdit ? 'Save changes' : 'Create product' ?></button>
        <a class="btn btn--ghost" href="<?= e(url('admin/shop/products')) ?>">Cancel</a>
    </div>
</form>
