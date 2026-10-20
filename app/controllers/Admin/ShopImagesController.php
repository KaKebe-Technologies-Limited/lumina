<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\UploadException;
use App\Models\ShopProduct;
use App\Services\Shop\ProductImages;

/**
 * ADMIN SHOP: product pictures (upload + optimise via ProductImages::upload, reorder, alt text, cover, delete). JSON endpoints used by assets/js/admin-shop.js;
 * a plain (non-JSON) request is answered with a redirect back to the product form so the buttons also work without JavaScript. Ability shop.manage.
 * OWNER: ADMIN agent. Contract: docs/SHOP.md section 8. Audit entries carry the product ID and counts only.
 */
final class ShopImagesController extends AdminController
{
    /** POST multipart: file, crop (square|portrait|none), alt. */
    public function upload(Request $request, int $id): Response
    {
        $this->authorize('shop.manage');
        $product = ShopProduct::find($id);
        if ($product === null) {
            return $this->reply($request, ['ok' => false, 'error' => 'That product no longer exists.'], 404, $id);
        }
        $crop = in_array($request->string('crop'), ProductImages::CROPS, true) ? $request->string('crop') : 'square';
        $alt = trim((string) preg_replace('/\p{C}+/u', '', $request->string('alt')));
        try {
            $saved = ProductImages::upload($id, $request->file('file'), ['crop' => $crop, 'alt' => $alt !== '' ? $alt : (string) $product['name']]);
        } catch (UploadException $e) {
            return $this->reply($request, ['ok' => false, 'error' => $e->getMessage()], 422, $id);
        }
        $this->audit('shop.product.images', 'shop_product', $id, 'Added a picture to product #' . $id, ['image_id' => (int) $saved['id']]);
        return $this->reply($request, ['ok' => true, 'image' => $this->payload($saved), 'notice' => $saved['notice'] ?? null, 'count' => $this->count($id)], 200, $id,
            'Picture added.' . (!empty($saved['notice']) ? ' ' . $saved['notice'] : ''));
    }

    /** POST {"ids":[3,1,2]}: the first picture becomes the cover. */
    public function reorder(Request $request, int $id): Response
    {
        $this->authorize('shop.manage');
        if (ShopProduct::find($id) === null) {
            return $this->reply($request, ['ok' => false, 'error' => 'That product no longer exists.'], 404, $id);
        }
        $ids = $request->post('ids', []);
        if (!is_array($ids)) {
            return $this->reply($request, ['ok' => false, 'error' => 'Nothing to reorder.'], 422, $id);
        }
        ProductImages::reorder($id, array_values(array_filter($ids, 'is_scalar')));
        $this->audit('shop.product.images', 'shop_product', $id, 'Reordered the pictures of product #' . $id);
        return $this->reply($request, ['ok' => true, 'cover_id' => $this->coverId($id)], 200, $id, 'Picture order saved.');
    }

    /** POST alt (text) and / or cover (true). */
    public function update(Request $request, int $id, int|string $iid): Response
    {
        $this->authorize('shop.manage');
        $iid = (int) $iid;                      // the router only casts a parameter called {id}; {iid} arrives as a string
        $img = $this->imageOf($id, $iid);
        if ($img === null) {
            return $this->reply($request, ['ok' => false, 'error' => 'That picture no longer exists.'], 404, $id);
        }
        if ($request->has('alt')) {
            ProductImages::updateAlt($iid, $this->clean($request->string('alt')));
        }
        if ($request->bool('cover')) {
            ProductImages::setCover($id, $iid);
            // keep the order consistent: the cover is always listed first
            $ids = array_map('intval', array_column(Database::all('SELECT id FROM shop_product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC', [$id]), 'id'));
            ProductImages::reorder($id, array_merge([$iid], array_values(array_diff($ids, [$iid]))));
        }
        $this->audit('shop.product.images', 'shop_product', $id, 'Edited a picture of product #' . $id, ['image_id' => $iid]);
        $fresh = Database::one('SELECT * FROM shop_product_images WHERE id = ?', [$iid]) ?? $img;
        return $this->reply($request, ['ok' => true, 'image' => $this->payload($fresh), 'cover_id' => $this->coverId($id)], 200, $id, 'Picture saved.');
    }

    public function destroy(Request $request, int $id, int|string $iid): Response
    {
        $this->authorize('shop.manage');
        $iid = (int) $iid;
        if ($this->imageOf($id, $iid) === null) {
            return $this->reply($request, ['ok' => false, 'error' => 'That picture no longer exists.'], 404, $id);
        }
        ProductImages::remove($iid);
        $this->audit('shop.product.images', 'shop_product', $id, 'Deleted a picture of product #' . $id, ['image_id' => $iid]);
        return $this->reply($request, ['ok' => true, 'cover_id' => $this->coverId($id), 'count' => $this->count($id)], 200, $id, 'Picture deleted.');
    }

    // ------------------------------------------------------------------------------------------------ helpers

    /** @return array<string,mixed>|null */
    private function imageOf(int $productId, int $imageId): ?array
    {
        return Database::one('SELECT * FROM shop_product_images WHERE id = ? AND product_id = ?', [$imageId, $productId]);
    }

    private function count(int $productId): int
    {
        return (int) Database::value('SELECT COUNT(*) FROM shop_product_images WHERE product_id = ?', [$productId], 0);
    }

    private function coverId(int $productId): ?int
    {
        $v = Database::value('SELECT id FROM shop_product_images WHERE product_id = ? ORDER BY is_cover DESC, sort_order ASC, id ASC LIMIT 1', [$productId]);
        return $v === null ? null : (int) $v;
    }

    /** What the browser needs to draw one picture. @param array<string,mixed> $row @return array<string,mixed> */
    private function payload(array $row): array
    {
        $v = ProductImages::view($row);
        return [
            'id' => (int) $v['id'], 'src' => $v['src'], 'webp' => $v['webp'], 'width' => $v['width'], 'height' => $v['height'], 'alt' => $v['alt'], 'is_cover' => $v['is_cover'],
            'url' => url('admin/shop/products/' . (int) $v['product_id'] . '/images/' . (int) $v['id']),
        ];
    }

    private function clean(string $value): string
    {
        return trim((string) preg_replace('/\p{C}+/u', '', mb_convert_encoding($value, 'UTF-8', 'UTF-8')));
    }

    /** JSON for scripts, a flash + redirect to the product form for plain requests. @param array<string,mixed> $body */
    private function reply(Request $request, array $body, int $status, int $productId, string $message = ''): Response
    {
        if ($request->expectsJson()) {
            return $this->json($body, $status);
        }
        if (!empty($body['ok'])) {
            Flash::success($message !== '' ? $message : 'Saved.');
        } else {
            Flash::error((string) ($body['error'] ?? 'The change could not be made.'));
        }
        return $status === 404 ? $this->redirect('admin/shop/products') : $this->redirect('admin/shop/products/' . $productId . '/edit');
    }
}
