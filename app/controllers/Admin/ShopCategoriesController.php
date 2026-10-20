<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\Slug;
use App\Models\ShopCategory;
use App\Services\Shop\ProductService;

/**
 * ADMIN SHOP: categories (name, web address, order, active). Ability shop.manage. OWNER: ADMIN agent. Contract: docs/SHOP.md.
 * Deleting a category never deletes products: they become "no category" (the foreign key sets the column to NULL).
 */
final class ShopCategoriesController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('shop.manage');
        $rows = Database::all(
            'SELECT c.*, (SELECT COUNT(*) FROM shop_products p WHERE p.category_id = c.id) AS products,
                    (SELECT COUNT(*) FROM shop_products p2 WHERE p2.category_id = c.id AND p2.status = \'published\') AS live
             FROM shop_categories c ORDER BY c.sort_order ASC, c.id ASC'
        );
        $uncategorised = (int) Database::value('SELECT COUNT(*) FROM shop_products WHERE category_id IS NULL', [], 0);
        return $this->render('admin/shop/categories/index', ['rows' => $rows, 'uncategorised' => $uncategorised],
            ['title' => 'Categories', 'nav' => 'shop_categories', 'breadcrumbs' => [['Shop', url('admin/shop')], ['Categories', null]]]);
    }

    public function store(Request $request): Response
    {
        $this->authorize('shop.manage');
        $data = $this->collect(null);
        if ($data === null) {
            return $this->redirect('admin/shop/categories');
        }
        $data['sort_order'] = (int) Database::value('SELECT COALESCE(MAX(sort_order), 0) FROM shop_categories', [], 0) + 10;
        $id = ShopCategory::create($data);
        $this->audit('shop.category.create', 'shop_category', $id, 'Created category #' . $id . ' "' . $data['name'] . '"');
        Flash::success('Category "' . $data['name'] . '" was added. It shows in the shop as soon as a published product uses it.');
        return $this->redirect('admin/shop/categories');
    }

    public function update(Request $request, int $id): Response
    {
        $this->authorize('shop.manage');
        $row = ShopCategory::findOrFail($id);
        $data = $this->collect($row);
        if ($data === null) {
            return $this->redirect('admin/shop/categories');
        }
        ShopCategory::update($id, $data);
        $this->audit('shop.category.update', 'shop_category', $id, 'Edited category #' . $id, ['fields' => array_keys(array_filter($data, static fn($v, $k): bool => (string) $v !== (string) ($row[$k] ?? ''), ARRAY_FILTER_USE_BOTH))]);
        Flash::success('Category "' . $data['name'] . '" was saved.');
        return $this->redirect('admin/shop/categories');
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('shop.manage');
        $row = ShopCategory::findOrFail($id);
        $n = (int) Database::value('SELECT COUNT(*) FROM shop_products WHERE category_id = ?', [$id], 0);
        ShopCategory::delete($id);
        $this->audit('shop.category.delete', 'shop_category', $id, 'Deleted category #' . $id, ['products_uncategorised' => $n]);
        Flash::success('Category "' . $row['name'] . '" was deleted.' . ($n > 0 ? ' Its ' . $n . ' product(s) are kept and now have no category.' : ''));
        return $this->redirect('admin/shop/categories');
    }

    /**
     * @param array<string,mixed>|null $row the category being edited
     * @return array{name:string,slug:string,sort_order?:int,is_active:int}|null null = an error was flashed
     */
    private function collect(?array $row): ?array
    {
        $r = $this->request;
        $id = $row !== null ? (int) $row['id'] : null;
        $name = trim((string) preg_replace('/\p{C}+/u', '', mb_convert_encoding($r->string('name'), 'UTF-8', 'UTF-8')));
        if ($name === '' || mb_strlen($name) > 120) {
            Flash::error('Give the category a name of up to 120 characters, for example "Caps".');
            return null;
        }
        $typed = $r->string('slug');
        $slug = Slug::make($typed);
        if ($typed !== '' && $slug === '') {
            Flash::error('The web address may only contain letters, numbers and hyphens.');
            return null;
        }
        if ($slug === '') {
            $slug = $row !== null ? (string) $row['slug'] : Slug::unique('shop_categories', $name, $id, 'slug', [], 'category');
        } elseif ((int) Database::value('SELECT COUNT(*) FROM shop_categories WHERE slug = ?' . ($id !== null ? ' AND id <> ?' : ''), $id !== null ? [$slug, $id] : [$slug], 0) > 0) {
            Flash::error('Another category already uses the web address "' . $slug . '".');
            return null;
        }
        if (in_array($slug, ProductService::RESERVED_SLUGS, true)) {
            Flash::error('"' . $slug . '" is reserved by the shop. Choose another web address.');
            return null;
        }
        $out = ['name' => $name, 'slug' => $slug, 'is_active' => $r->bool('is_active') ? 1 : 0];
        if ($row !== null) {
            $order = trim($r->string('sort_order'));
            $out['sort_order'] = $order !== '' && preg_match('/^-?\d{1,6}$/', $order) === 1 ? (int) $order : (int) $row['sort_order'];
        }
        return $out;
    }
}
