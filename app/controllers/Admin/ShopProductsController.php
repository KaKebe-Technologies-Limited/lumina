<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Pagination;
use App\Core\Request;
use App\Core\Response;
use App\Core\Sanitizer;
use App\Core\Slug;
use App\Core\UploadException;
use App\Models\ShopCategory;
use App\Models\ShopProduct;
use App\Services\Shop\Money;
use App\Services\Shop\ProductImages;
use App\Services\Shop\ProductService;
use App\Services\Shop\ShopSettings;

/**
 * ADMIN SHOP: products (list with search / filters / quick toggles, create, edit, duplicate, delete). Ability shop.manage (+ shop.delete for delete).
 * OWNER: ADMIN agent. Contract: docs/SHOP.md section 8. Every method authorizes its ability (defence in depth next to the route middleware).
 *
 * Rules: every price, stock and option rule is read from / validated through App\Services\Shop (Money, ProductService); the form never decides what the shop
 * shows. Pictures are handled by ShopImagesController (AJAX) and, as a fallback without JavaScript, by the "pictures" file field of this form.
 * Audit entries carry the product ID and the NAMES of changed fields, never prices typed by hand or customer data.
 */
final class ShopProductsController extends AdminController
{
    private const SORTS = [
        'order'   => ['p.sort_order', 'p.id'],
        'name'    => ['p.name'],
        'price'   => ['p.price'],
        'stock'   => ['p.track_stock', 'p.stock_qty'],
        'updated' => ['p.updated_at'],
    ];

    public function index(Request $request): Response
    {
        $this->authorize('shop.manage');
        $q = mb_substr($request->string('q'), 0, 100);
        $status = in_array($request->string('status'), ShopProduct::STATUSES, true) ? $request->string('status') : '';
        $stock = in_array($request->string('stock'), ['low', 'out', 'unlimited'], true) ? $request->string('stock') : '';
        $category = $request->string('category');
        $featured = $request->bool('featured');
        $sort = isset(self::SORTS[$request->string('sort')]) ? $request->string('sort') : 'order';
        $dir = strtolower($request->string('dir')) === 'desc' ? 'DESC' : 'ASC';
        if (!isset(self::SORTS[$request->string('sort')])) {
            $dir = $sort === 'order' ? 'ASC' : $dir;
        }

        $where = ['1 = 1'];
        $params = [];
        if ($q !== '') {
            $like = '%' . addcslashes($q, '\\%_') . '%';
            $where[] = '(p.name LIKE ? OR p.sku LIKE ? OR p.slug LIKE ?)';
            array_push($params, $like, $like, $like);
        }
        if ($status !== '') {
            $where[] = 'p.status = ?';
            $params[] = $status;
        }
        if ($category === 'none') {
            $where[] = 'p.category_id IS NULL';
        } elseif (ctype_digit($category) && (int) $category > 0) {
            $where[] = 'p.category_id = ?';
            $params[] = (int) $category;
        } else {
            $category = '';
        }
        if ($featured) {
            $where[] = 'p.is_featured = 1';
        }
        if ($stock === 'unlimited') {
            $where[] = 'p.track_stock = 0';
        } elseif ($stock === 'out') {
            $where[] = 'p.track_stock = 1 AND p.stock_qty <= 0';
        } elseif ($stock === 'low') {
            $where[] = 'p.track_stock = 1 AND p.stock_qty > 0 AND p.stock_qty <= p.low_stock';
        }
        $whereSql = implode(' AND ', $where);

        $total = (int) Database::value('SELECT COUNT(*) FROM shop_products p WHERE ' . $whereSql, $params, 0);
        $pager = Pagination::make($total, $request->page(), $this->perPage());
        $order = implode(' ' . $dir . ', ', self::SORTS[$sort]) . ' ' . $dir . ', p.id DESC';
        $pager->items = Database::all(
            'SELECT p.*, c.name AS category_name,
                (SELECT i.path FROM shop_product_images i WHERE i.product_id = p.id ORDER BY i.is_cover DESC, i.sort_order ASC, i.id ASC LIMIT 1) AS cover_path,
                (SELECT COUNT(*) FROM shop_product_images i2 WHERE i2.product_id = p.id) AS picture_count
             FROM shop_products p LEFT JOIN shop_categories c ON c.id = p.category_id WHERE ' . $whereSql . ' ORDER BY ' . $order . ' LIMIT ? OFFSET ?',
            array_merge($params, [$pager->perPage, $pager->offset])
        );

        $counts = ['all' => 0, 'published' => 0, 'draft' => 0, 'archived' => 0];
        foreach (Database::all('SELECT status, COUNT(*) AS n FROM shop_products GROUP BY status') as $r) {
            $counts[(string) $r['status']] = (int) $r['n'];
            $counts['all'] += (int) $r['n'];
        }

        return $this->render('admin/shop/products/index', [
            'pager'      => $pager,
            'q'          => $q,
            'status'     => $status,
            'stock'      => $stock,
            'category'   => $category,
            'featured'   => $featured,
            'sort'       => $sort,
            'dir'        => strtolower($dir),
            'filtered'   => $q !== '' || $status !== '' || $stock !== '' || $category !== '' || $featured,
            'counts'     => $counts,
            'categories' => ShopCategory::all(),
            'samples'    => (int) Database::value('SELECT COUNT(*) FROM shop_products WHERE is_sample = 1', [], 0),
            'currency'   => ShopSettings::currency(),
            'shopOpen'   => ShopSettings::isOpen(),
        ], ['title' => 'Products', 'nav' => 'shop_products', 'breadcrumbs' => [['Shop', url('admin/shop')], ['Products', null]]]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('shop.manage');
        $product = [
            'status' => 'draft', 'track_stock' => 0, 'stock_qty' => '', 'low_stock' => 5, 'allow_booking' => 0, 'is_featured' => 0, 'is_sample' => 0,
            'price' => '', 'sale_price' => '', 'sort_order' => 0, 'category_id' => '', 'options' => null,
        ];
        return $this->render('admin/shop/products/form', $this->formData($product, [], false),
            ['title' => 'Add product', 'nav' => 'shop_products', 'breadcrumbs' => [['Shop', url('admin/shop')], ['Products', url('admin/shop/products')], ['Add', null]]]);
    }

    public function store(Request $request): Response
    {
        $this->authorize('shop.manage');
        $to = 'admin/shop/products/create';
        $data = $this->collect(null, $to);
        $data['sort_order'] = (int) Database::value('SELECT COALESCE(MAX(sort_order), 0) FROM shop_products', [], 0) + 10;
        $id = ShopProduct::create($data);
        $this->audit('shop.product.create', 'shop_product', $id, 'Created product #' . $id . ' "' . $data['name'] . '"', ['status' => $data['status']]);
        $added = $this->savePictures($request, $id, (string) $data['name']);
        Flash::success('Product "' . $data['name'] . '" was created.' . ($data['status'] === 'published' ? '' : ' It is not visible in the shop until its status is Published.'));
        $this->afterSaveWarnings($data, $added['count']);
        return $this->redirect('admin/shop/products/' . $id . '/edit');
    }

    public function edit(Request $request, int $id): Response
    {
        $this->authorize('shop.manage');
        $product = ShopProduct::findOrFail($id);
        $images = ProductImages::forProduct($id);
        return $this->render('admin/shop/products/form', $this->formData($product, $images, true),
            ['title' => 'Edit product', 'nav' => 'shop_products', 'breadcrumbs' => [['Shop', url('admin/shop')], ['Products', url('admin/shop/products')], [(string) $product['name'], null]]]);
    }

    public function update(Request $request, int $id): Response
    {
        $this->authorize('shop.manage');
        $row = ShopProduct::findOrFail($id);
        $to = 'admin/shop/products/' . $id . '/edit';
        $data = $this->collect($row, $to);
        // The stock numbers on the form are the numbers from when it was OPENED; orders may have taken units since. Under a row lock (the same lock checkout takes) the
        // saved stock becomes "what is there now" plus the change the person made, so a typo fix never puts reserved units back on the shelf.
        Database::transaction(function () use ($id, &$data, $row): void {
            $now = Database::one('SELECT * FROM shop_products WHERE id = ? FOR UPDATE', [$id]);
            $data = $this->mergeLiveStock($data, $now ?? $row);
            ShopProduct::update($id, $data);
        });

        // pictures: alt texts, order / cover (fallback when JavaScript is off), then new files
        $own = array_map('intval', array_column(Database::all('SELECT id FROM shop_product_images WHERE product_id = ?', [$id]), 'id'));
        $alts = $request->post('image_alt', []);
        if (is_array($alts)) {
            foreach ($alts as $iid => $alt) {
                if (is_scalar($alt) && in_array((int) $iid, $own, true)) {
                    ProductImages::updateAlt((int) $iid, (string) $alt);
                }
            }
        }
        $order = $request->post('image_order', []);
        $cover = $request->int('cover_id');
        if (is_array($order) && $order !== []) {
            $ids = array_values(array_filter(array_map('intval', $order), static fn(int $i): bool => in_array($i, $own, true)));
            if ($cover > 0 && in_array($cover, $ids, true)) {
                $ids = array_merge([$cover], array_values(array_diff($ids, [$cover])));
            }
            if ($ids !== []) {
                ProductImages::reorder($id, $ids);
            }
        } elseif ($cover > 0 && in_array($cover, $own, true)) {
            ProductImages::setCover($id, $cover);
        }
        $added = $this->savePictures($request, $id, (string) $data['name']);

        $changed = [];
        foreach (['name', 'slug', 'category_id', 'summary', 'description', 'details', 'price', 'sale_price', 'sale_starts', 'sale_ends', 'sku', 'track_stock', 'stock_qty', 'low_stock', 'options',
                  'allow_booking', 'is_featured', 'is_sample', 'status', 'seo_title', 'seo_description'] as $f) {
            if ((string) ($row[$f] ?? '') !== (string) ($data[$f] ?? '')) {
                $changed[] = $f;
            }
        }
        $this->audit('shop.product.update', 'shop_product', $id, 'Edited product #' . $id, ['fields' => $changed]);
        Flash::success('Product "' . $data['name'] . '" was saved.');
        $this->afterSaveWarnings($data, $added['count'] + (int) Database::value('SELECT COUNT(*) FROM shop_product_images WHERE product_id = ?', [$id], 0));
        return $this->redirect('admin/shop/products/' . $id . '/edit');
    }

    public function duplicate(Request $request, int $id): Response
    {
        $this->authorize('shop.manage');
        $row = ShopProduct::findOrFail($id);
        $name = mb_substr('Copy of ' . $row['name'], 0, 190);
        $copy = $row;
        unset($copy['id'], $copy['created_at'], $copy['updated_at']);
        $copy['name'] = $name;
        $copy['slug'] = ProductService::uniqueSlug($name);
        $copy['sku'] = null;
        $copy['status'] = 'draft';
        $copy['is_sample'] = 0;
        $copy['is_featured'] = 0;
        $copy['sort_order'] = (int) Database::value('SELECT COALESCE(MAX(sort_order), 0) FROM shop_products', [], 0) + 10;
        $newId = ShopProduct::create($copy);
        // the copy points at the same picture files (ImageCleanup only deletes a file when no row uses it any more)
        foreach (ProductImages::forProduct($id) as $img) {
            unset($img['id'], $img['created_at'], $img['updated_at']);
            $img['product_id'] = $newId;
            Database::insert('shop_product_images', $img);
        }
        $this->audit('shop.product.create', 'shop_product', $newId, 'Duplicated product #' . $id . ' as #' . $newId, ['copy_of' => $id]);
        Flash::success('Copied. The copy "' . $name . '" is a draft: change its name, price and pictures, then publish it.');
        return $this->redirect('admin/shop/products/' . $newId . '/edit');
    }

    /** Quick changes from the list: field = featured | status | booking | stock. */
    public function toggle(Request $request, int $id): Response
    {
        $this->authorize('shop.manage');
        $row = ShopProduct::findOrFail($id);
        $field = $request->string('field');
        $msg = '';
        $update = [];
        switch ($field) {
            case 'featured':
                $update['is_featured'] = (int) $row['is_featured'] === 1 ? 0 : 1;
                $msg = '"' . $row['name'] . '" is ' . ($update['is_featured'] ? 'now featured.' : 'no longer featured.');
                break;
            case 'booking':
                $update['allow_booking'] = (int) $row['allow_booking'] === 1 ? 0 : 1;
                $msg = 'Booking is ' . ($update['allow_booking'] ? 'on' : 'off') . ' for "' . $row['name'] . '".';
                break;
            case 'status':
                $to = $request->string('value');
                if (!in_array($to, ShopProduct::STATUSES, true)) {
                    $to = $row['status'] === 'published' ? 'draft' : 'published';
                }
                $update['status'] = $to;
                $msg = '"' . $row['name'] . '" is now ' . ($to === 'published' ? 'published (visible in the shop).' : ($to === 'draft' ? 'a draft (hidden).' : 'archived (hidden).'));
                break;
            case 'stock':
                $qty = trim($request->string('qty'));
                if ($qty === '' || !ctype_digit($qty) || (int) $qty > 1000000) {
                    return $this->quickFail($request, 'Enter the number of items in stock as a whole number, for example 12.');
                }
                $update['track_stock'] = 1;
                $update['stock_qty'] = (int) $qty;
                $msg = 'Stock of "' . $row['name'] . '" is now ' . (int) $qty . '.';
                break;
            default:
                return $this->quickFail($request, 'Unknown change.');
        }
        ShopProduct::update($id, $update);
        $this->audit('shop.product.update', 'shop_product', $id, 'Quick change on product #' . $id, ['fields' => array_keys($update)]);
        if ($request->expectsJson()) {
            return $this->json(['ok' => true, 'message' => $msg]);
        }
        Flash::success($msg);
        return $this->returnTo($request);
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('shop.manage', 'shop.delete');
        $row = ShopProduct::findOrFail($id);
        $images = ProductImages::forProduct($id);
        $orders = (int) Database::value('SELECT COUNT(*) FROM shop_order_items WHERE product_id = ?', [$id], 0);
        ShopProduct::delete($id);                               // picture rows go with it (ON DELETE CASCADE); order history keeps its own snapshot
        foreach ($images as $img) {
            ProductImages::releaseFiles($img);
        }
        $this->audit('shop.product.delete', 'shop_product', $id, 'Deleted product #' . $id, ['pictures' => count($images), 'order_lines_kept' => $orders]);
        Flash::success('Product "' . $row['name'] . '" was deleted.' . ($orders > 0 ? ' Past orders keep their own copy of the item.' : ''));
        return $this->redirect('admin/shop/products');
    }

    // ------------------------------------------------------------------------------------------------ helpers

    /** @param array<string,mixed> $product @param list<array<string,mixed>> $images @return array<string,mixed> */
    private function formData(array $product, array $images, bool $isEdit): array
    {
        $cur = ShopSettings::currency();
        return [
            'product'    => $product,
            'images'     => array_map([ProductImages::class, 'view'], $images),
            'isEdit'     => $isEdit,
            'categories' => ShopCategory::all(),
            'groups'     => $this->postedGroups() ?? ProductService::parseOptions($product['options'] ?? null),
            'currency'   => $cur,
            'decimals'   => \App\Services\Shop\Money::decimals($cur),
            'maxPics'    => ProductImages::MAX_PER_PRODUCT,
            'maxGroups'  => ProductService::MAX_GROUPS,
            'maxValues'  => ProductService::MAX_VALUES,
            'gd'         => \App\Core\ImageOptimizer::capabilities()['gd'],
            'canDelete'  => can('shop.delete'),
            'orderLines' => $isEdit ? (int) Database::value('SELECT COUNT(*) FROM shop_order_items WHERE product_id = ?', [(int) $product['id']], 0) : 0,
        ];
    }

    /** Option groups typed into the form that failed validation (so they are not lost), or null. @return list<array<string,mixed>>|null */
    private function postedGroups(): ?array
    {
        $raw = $this->request->post('options', null);
        if (!is_array($raw)) {
            return null;
        }
        return $this->groupsFromPost($raw, false);
    }

    /**
     * POSTed option groups -> the shape ProductService::normaliseOptions expects. With $clean = false empty rows are kept for redisplay.
     * @param array<mixed> $raw
     * @return list<array<string,mixed>>
     */
    private function groupsFromPost(array $raw, bool $clean = true): array
    {
        $groups = [];
        foreach ($raw as $g) {
            if (!is_array($g)) {
                continue;
            }
            $values = [];
            foreach ((array) ($g['values'] ?? []) as $v) {
                if (!is_array($v)) {
                    continue;
                }
                $label = trim((string) ($v['label'] ?? ''));
                if ($label === '' && $clean) {
                    continue;
                }
                $values[] = ['label' => $label, 'stock' => trim((string) ($v['stock'] ?? '')) === '' ? null : trim((string) $v['stock']), 'swatch' => trim((string) ($v['swatch'] ?? '')) ?: null,
                    'value' => preg_match('/^[a-z0-9\-]{1,40}$/', (string) ($v['value'] ?? '')) === 1 ? (string) $v['value'] : ''];
            }
            $label = trim((string) ($g['label'] ?? ''));
            if ($clean && $label === '' && $values === []) {
                continue;
            }
            $groups[] = ['label' => $label, 'key' => preg_match('/^[a-z0-9_]{1,30}$/', (string) ($g['key'] ?? '')) === 1 ? (string) $g['key'] : '',
                'required' => !empty($g['required']), 'values' => $values];
        }
        return $groups;
    }

    /**
     * Validate the form and return the columns to save. Errors go back to the form (old input is kept).
     * @param array<string,mixed>|null $row the product being edited
     * @return array<string,mixed>
     */
    private function collect(?array $row, string $redirectTo): array
    {
        $r = $this->request;
        $id = $row !== null ? (int) $row['id'] : null;
        $errors = [];
        $cur = ShopSettings::currency();

        $name = $this->clean($r->string('name'));
        if ($name === '') {
            $errors['name'] = 'Give the product a name, for example "Lumina Cap".';
        } elseif (mb_strlen($name) > 190) {
            $errors['name'] = 'Use at most 190 characters for the name.';
        }

        $typedSlug = $r->string('slug');
        $slug = Slug::make($typedSlug);
        if ($typedSlug !== '' && $slug === '') {
            $errors['slug'] = 'The web address may only contain letters, numbers and hyphens.';
        } elseif ($slug !== '') {
            if (in_array($slug, ProductService::RESERVED_SLUGS, true)) {
                $errors['slug'] = 'That web address is reserved by the shop (cart, checkout and so on). Choose another.';
            } elseif ((int) Database::value('SELECT COUNT(*) FROM shop_products WHERE slug = ?' . ($id !== null ? ' AND id <> ?' : ''), $id !== null ? [$slug, $id] : [$slug], 0) > 0) {
                $errors['slug'] = 'Another product already uses that web address.';
            }
        }

        $categoryId = null;
        $cat = $r->string('category_id');
        if ($cat !== '') {
            if (ctype_digit($cat) && ShopCategory::find((int) $cat) !== null) {
                $categoryId = (int) $cat;
            } else {
                $errors['category_id'] = 'Choose one of the listed categories.';
            }
        }

        $summary = $this->clean($r->string('summary'), true);
        if (mb_strlen($summary) > 400) {
            $errors['summary'] = 'The short description may have at most 400 characters.';
        }
        $details = $this->clean($r->string('details'), true);
        if (mb_strlen($details) > 3000) {
            $errors['details'] = 'The details list is too long (3000 characters at most).';
        }
        $descRaw = $r->post('description', '');
        $description = Sanitizer::html(is_string($descRaw) ? $descRaw : '');

        // ---- price
        // round FIRST (a price of 0.4 in a currency without cents is 0, not a free product), then check the range the money columns and the order totals can hold
        $maxPrice = Money::format(Money::MAX_PRICE, $cur, false);
        $price = Money::parse($r->string('price'));
        if ($price !== null) {
            $price = Money::roundTo($price, $cur);
        }
        if ($r->string('price') === '' || $price === null || $price <= 0) {
            $errors['price'] = 'Enter the price as a number above 0, for example 35000.';
        } elseif ($price > Money::MAX_PRICE) {
            $errors['price'] = 'The price may be at most ' . $maxPrice . '.';
        }
        $salePrice = null;
        if ($r->string('sale_price') !== '') {
            $salePrice = Money::parse($r->string('sale_price'));
            if ($salePrice !== null) {
                $salePrice = Money::roundTo($salePrice, $cur);
            }
            if ($salePrice === null || $salePrice <= 0) {
                $errors['sale_price'] = 'Enter the sale price as a number above 0, or leave it empty for no sale.';
                $salePrice = null;
            } elseif ($salePrice > Money::MAX_PRICE) {
                $errors['sale_price'] = 'The sale price may be at most ' . $maxPrice . '.';
            } elseif (is_int($price) && $salePrice >= $price) {
                $errors['sale_price'] = 'The sale price must be lower than the regular price.';
            }
        }
        $starts = $this->dbDateTime($r->string('sale_starts'), 'sale_starts', $errors);
        $ends = $this->dbDateTime($r->string('sale_ends'), 'sale_ends', $errors);
        if ($starts !== null && $ends !== null && $ends <= $starts && !isset($errors['sale_ends'])) {
            $errors['sale_ends'] = 'The sale must end after it starts.';
        }
        if ($salePrice === null) {
            $starts = null;
            $ends = null;
        }

        // ---- stock
        $sku = $this->clean($r->string('sku'));
        if (mb_strlen($sku) > 60) {
            $errors['sku'] = 'The SKU may have at most 60 characters.';
        }
        $track = $r->bool('track_stock') ? 1 : 0;
        $qty = null;
        if ($track === 1) {
            $q = trim($r->string('stock_qty'));
            if ($q === '' || !ctype_digit($q) || (int) $q > 1000000) {
                $errors['stock_qty'] = 'Enter how many are in stock as a whole number (0 means sold out).';
            } else {
                $qty = (int) $q;
            }
        }
        $low = trim($r->string('low_stock'));
        if ($low === '') {
            $low = '5';
        }
        if (!ctype_digit($low) || (int) $low > 100000) {
            $errors['low_stock'] = 'Enter the "few left" level as a whole number, for example 5.';
            $low = '5';
        }

        // ---- option groups
        $groupsPosted = $this->groupsFromPost((array) $r->post('options', []));
        if (count($groupsPosted) > ProductService::MAX_GROUPS) {
            $errors['options'] = 'A product can have at most ' . ProductService::MAX_GROUPS . ' option groups (for example Size and Colour).';
        }
        foreach ($groupsPosted as $g) {
            if ($g['label'] === '') {
                $errors['options'] = 'Give every option group a name (for example Size) or remove it.';
                break;
            }
            if ($g['values'] === []) {
                $errors['options'] = 'Add at least one choice to the "' . $g['label'] . '" option, or remove the group.';
                break;
            }
            if (count($g['values']) > ProductService::MAX_VALUES) {
                $errors['options'] = 'An option group can have at most ' . ProductService::MAX_VALUES . ' choices.';
                break;
            }
            foreach ($g['values'] as $v) {
                if ($v['stock'] !== null && (!ctype_digit((string) $v['stock']) || (int) $v['stock'] > 1000000)) {
                    $errors['options'] = 'The stock of "' . $g['label'] . ': ' . $v['label'] . '" must be a whole number, or empty.';
                    break 2;
                }
                if ($v['swatch'] !== null && preg_match('/^#[0-9a-fA-F]{6}$/', (string) $v['swatch']) !== 1) {
                    $errors['options'] = 'A colour swatch must look like #2C3950, or be empty.';
                    break 2;
                }
            }
        }
        $groups = ProductService::normaliseOptions($groupsPosted);

        $status = $r->string('status');
        if (!in_array($status, ShopProduct::STATUSES, true)) {
            $errors['status'] = 'Choose Draft, Published or Archived.';
        }
        $seoTitle = $this->clean($r->string('seo_title'));
        $seoDesc = $this->clean($r->string('seo_description'), true);
        if (mb_strlen($seoTitle) > 190) {
            $errors['seo_title'] = 'The search title may have at most 190 characters.';
        }
        if (mb_strlen($seoDesc) > 300) {
            $errors['seo_description'] = 'The search description may have at most 300 characters.';
        }
        foreach ($this->pictureErrors() as $msg) {
            $errors['pictures'] = $msg;
        }

        if ($errors !== []) {
            $this->fail($errors, $redirectTo);
        }
        if ($slug === '') {
            $slug = $row !== null && trim($typedSlug) === '' ? (string) $row['slug'] : ProductService::uniqueSlug($name, $id);
        }

        return [
            'slug'            => $slug,
            'category_id'     => $categoryId,
            'name'            => $name,
            'summary'         => $summary !== '' ? $summary : null,
            'description'     => $description !== '' ? $description : null,
            'details'         => $details !== '' ? $details : null,
            'price'           => Money::toDb((int) $price),
            'sale_price'      => $salePrice !== null ? Money::toDb($salePrice) : null,
            'sale_starts'     => $starts,
            'sale_ends'       => $ends,
            'sku'             => $sku !== '' ? $sku : null,
            'track_stock'     => $track,
            'stock_qty'       => $qty,
            'low_stock'       => (int) $low,
            'options'         => ProductService::encodeOptions($groups),
            'allow_booking'   => $r->bool('allow_booking') ? 1 : 0,
            'is_featured'     => $r->bool('is_featured') ? 1 : 0,
            'is_sample'       => $row !== null && (int) $row['is_sample'] === 1 && !$r->bool('not_sample') ? 1 : 0,
            'status'          => $status,
            'seo_title'       => $seoTitle !== '' ? $seoTitle : null,
            'seo_description' => $seoDesc !== '' ? $seoDesc : null,
        ];
    }

    /**
     * Apply the CHANGE the person made to the stock numbers (posted minus the snapshot of when the form was opened) on top of the current database values.
     * Untouched numbers keep the live value; a number the person did change moves by the difference (never below 0); a new or cleared limit is taken as typed.
     * Without a snapshot (an old form, a new product) the posted numbers are used as typed.
     * @param array<string,mixed> $data the columns to save  @param array<string,mixed> $current the row as it is now
     * @return array<string,mixed>
     */
    private function mergeLiveStock(array $data, array $current): array
    {
        $raw = $this->request->string('stock_snapshot');
        $snap = $raw !== '' ? json_decode($raw, true) : null;
        if (!is_array($snap)) {
            return $data;
        }
        if ((int) $data['track_stock'] === 1 && (int) $current['track_stock'] === 1 && isset($snap['qty']) && is_numeric($snap['qty']) && $data['stock_qty'] !== null) {
            $loaded = (int) $snap['qty'];
            $posted = (int) $data['stock_qty'];
            $data['stock_qty'] = $posted === $loaded ? max(0, (int) $current['stock_qty']) : max(0, (int) $current['stock_qty'] + ($posted - $loaded));
        }
        $loadedOpts = is_array($snap['opts'] ?? null) ? $snap['opts'] : [];
        $live = [];
        foreach (ProductService::parseOptions($current['options'] ?? null) as $g) {
            foreach ($g['values'] as $v) {
                if ($v['stock'] !== null) {
                    $live[$g['key'] . '|' . $v['value']] = (int) $v['stock'];
                }
            }
        }
        $groups = ProductService::parseOptions($data['options'] ?? null);
        foreach ($groups as &$g) {
            foreach ($g['values'] as &$v) {
                $k = $g['key'] . '|' . $v['value'];
                if ($v['stock'] !== null && isset($loadedOpts[$k], $live[$k]) && is_numeric($loadedOpts[$k])) {
                    $posted = (int) $v['stock'];
                    $v['stock'] = $posted === (int) $loadedOpts[$k] ? $live[$k] : max(0, $live[$k] + ($posted - (int) $loadedOpts[$k]));
                }
            }
            unset($v);
        }
        unset($g);
        $data['options'] = ProductService::encodeOptions($groups);
        return $data;
    }

    /** Messages for the files chosen in the "pictures" field that cannot be accepted (checked BEFORE the product is saved). @return list<string> */
    private function pictureErrors(): array
    {
        $out = [];
        foreach ($this->request->files('pictures') as $f) {
            if ((int) $f['error'] !== UPLOAD_ERR_OK) {
                $out[] = 'A picture could not be uploaded (it may be larger than the server allows). Try a smaller file.';
                break;
            }
            $ext = strtolower(pathinfo((string) $f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                $out[] = 'Only JPG, PNG, WebP or GIF pictures are allowed.';
                break;
            }
        }
        return $out;
    }

    /** Upload the files of the "pictures" field. @return array{count:int} */
    private function savePictures(Request $request, int $productId, string $name): array
    {
        $crop = in_array($request->string('crop'), ProductImages::CROPS, true) ? $request->string('crop') : 'square';
        $alt = $this->clean($request->string('new_alt'));
        $count = 0;
        $notices = [];
        foreach ($request->files('pictures') as $file) {
            try {
                $saved = ProductImages::upload($productId, $file, ['crop' => $crop, 'alt' => $alt !== '' ? $alt : $name]);
                $count++;
                if (!empty($saved['notice'])) {
                    $notices[(string) $saved['notice']] = true;
                }
            } catch (UploadException $e) {
                Flash::warning('A picture was not added: ' . $e->getMessage());
            }
        }
        foreach (array_keys($notices) as $n) {
            Flash::info($n);
        }
        if ($count > 0) {
            $this->audit('shop.product.images', 'shop_product', $productId, 'Added ' . $count . ' picture(s) to product #' . $productId);
        }
        return ['count' => $count];
    }

    /** @param array<string,mixed> $data */
    private function afterSaveWarnings(array $data, int $pictures): void
    {
        if ($data['status'] === 'published' && $pictures === 0) {
            Flash::warning('This product is published but has no picture yet. Add at least one so the shop looks good.');
        }
        if ((int) $data['track_stock'] === 1 && (int) $data['stock_qty'] === 0 && $data['status'] === 'published') {
            Flash::info('Stock is 0, so the shop shows this product as "Sold out".');
        }
    }

    private function quickFail(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return $this->json(['ok' => false, 'message' => $message], 422);
        }
        Flash::error($message);
        return $this->returnTo($request);
    }

    /** Back to the list the admin came from (the "_back" field), else the list. */
    private function returnTo(Request $request): Response
    {
        $back = $request->string('_back');
        if ($back !== '' && $back[0] === '/' && !str_contains($back, '..') && !preg_match('~[\x00-\x1F]~', $back)) {
            $path = (string) parse_url($back, PHP_URL_PATH);
            $qs = (string) parse_url($back, PHP_URL_QUERY);
            if (preg_match('~/admin/shop/products(/\d+/edit)?$~', $path)) {
                return Response::redirect(url(trim($path, '/')) . ($qs !== '' ? '?' . $qs : ''));
            }
        }
        return $this->redirect('admin/shop/products');
    }

    /** Plain text: control characters removed, line breaks normalised (kept when $multiline). Output is escaped with e(). */
    private function clean(string $value, bool $multiline = false): string
    {
        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = (string) preg_replace($multiline ? '/[^\P{C}\n\t]+/u' : '/\p{C}+/u', '', $value);
        return trim($value);
    }

    /** "2026-11-03T14:30" -> "2026-11-03 14:30:00"; '' -> null; anything else adds an error. @param array<string,string> $errors */
    private function dbDateTime(string $value, string $field, array &$errors): ?string
    {
        $v = str_replace('T', ' ', trim($value));
        if ($v === '') {
            return null;
        }
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})(:\d{2})?$/', $v, $m) !== 1 || !checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            $errors[$field] = 'Choose a valid date and time, or leave it empty.';
            return null;
        }
        return substr($v, 0, 16) . ':00';
    }
}
