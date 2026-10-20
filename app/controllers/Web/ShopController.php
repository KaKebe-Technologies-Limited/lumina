<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\Shop\ProductService;
use App\Services\Shop\ShopSettings;

/**
 * PUBLIC catalogue: /shop, /shop/category/{slug}, /shop/{slug}.
 * OWNER: STOREFRONT agent (file owner of this controller and app/views/web/shop/*). Contract: docs/SHOP.md.
 *
 * Final implementation: views in app/views/web/shop, partials shop-card / shop-image. Every rule comes from ProductService / CartService / PricingService, every word from content('shop', ...),
 * prices only through the hydrated product ('unit_text', 'price_text'), no cookie for anonymous views.
 */
final class ShopController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->list($request, null);
    }

    public function category(Request $request, string $slug): Response
    {
        $cat = ProductService::category($slug);
        if ($cat === null) {
            $this->abort(404);
        }
        return $this->list($request, $cat);
    }

    /** Terms of sale, delivery and returns (the checkout consent box links here). */
    public function terms(Request $request): Response
    {
        return $this->renderPage('shop', 'web/shop/terms', [
            'docs' => [
                'terms'    => ['title' => (string) content('shop', 'policy.terms_title'), 'text' => ShopSettings::policy('terms')],
                'delivery' => ['title' => (string) content('shop', 'policy.delivery_title'), 'text' => ShopSettings::policy('delivery')],
                'returns'  => ['title' => (string) content('shop', 'policy.returns_title'), 'text' => ShopSettings::policy('returns')],
            ],
        ], [
            'bodyClass'   => 'is-shop-terms',
            'meta'        => ['title' => (string) content('shop', 'policy.terms_title')],
            'breadcrumbs' => [[(string) content('shop', 'hero.eyebrow'), url('shop')], [(string) content('shop', 'policy.terms_title'), null]],
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $product = ProductService::findBySlug($slug, true);
        if ($product === null) {
            $this->abort(404);
        }
        return $this->renderPage('shop', 'web/shop/show', [
            'product' => $product,
            'related' => ProductService::related($product, 4),
            'open'    => ShopSettings::isOpen(),
        ], [
            'bodyClass'   => 'is-shop-product',
            'meta'        => ['title' => (string) ($product['seo_title'] ?: $product['name']), 'description' => (string) ($product['seo_description'] ?: ($product['summary'] ?? '')),
                'og_image' => (string) ($product['cover']['path'] ?? '')] + (in_array('sample', $product['badges'], true) ? ['robots' => 'noindex,follow'] : []),     // a sample's price is a placeholder: keep it out of search results
            'breadcrumbs' => [[(string) content('shop', 'hero.eyebrow'), url('shop')], [(string) $product['name'], null]],
        ]);
    }

    /** @param array<string,mixed>|null $cat */
    private function list(Request $request, ?array $cat): Response
    {
        $sort = $request->string('sort', 'featured');            // string(): an array (?sort[]=a) is ignored, never cast (no 500)
        $q = $request->string('q');
        $page = ProductService::catalogue(['category' => $cat['slug'] ?? $request->string('category'), 'sort' => $sort, 'q' => $q], $request->page(), 12);
        return $this->renderPage('shop', 'web/shop/index', [
            'items'      => $page['items'],
            'catalogue'  => $page,
            'categories' => ProductService::categories(true, true),
            'category'   => $cat,
            'q'          => $q,
            'open'       => ShopSettings::isOpen(),
        ], [
            'bodyClass'   => 'is-shop',
            // a category page has its own title, address and description (it was a copy of /shop); the page after the first keeps the same title
            'meta'        => $cat === null ? [] : ['title' => $cat['name'] . ' | ' . (string) content('shop', 'hero.eyebrow'), 'canonical' => '/shop/category/' . $cat['slug'],
                'description' => str_replace('{name}', (string) $cat['name'], (string) content('shop', 'catalogue.category_description'))],
            'breadcrumbs' => $cat === null ? [[(string) content('shop', 'hero.eyebrow'), null]] : [[(string) content('shop', 'hero.eyebrow'), url('shop')], [(string) $cat['name'], null]],
        ]);
    }
}
