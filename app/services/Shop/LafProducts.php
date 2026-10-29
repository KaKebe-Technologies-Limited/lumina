<?php
declare(strict_types=1);

namespace App\Services\Shop;

use App\Core\Database;

/**
 * The LAF branded products (hoodies, jackets, shirts, polos, cap, backpack) and the pictures that ship with the site (assets/img/shop/laf/).
 *
 *   import()          Admin > Shop > Products > "Add the LAF products": creates every product that is not in the shop yet (matched by its address/slug, so a second
 *                     press adds nothing and a product you edited or renamed is never touched), with its picture, a size choice for clothes and a price.
 *   removeSamples()   deletes the placeholder SAMPLE products the shop started with (and their picture files), the same way the Delete button does. Past orders keep
 *                     their own copy of the item.
 *
 * PRICES here are only the starting values (UGX). Every price is edited afterwards in Admin > Shop > Products > Edit (price, sale price, stock...).
 */
final class LafProducts
{
    private const SIZES = ['s' => 'S', 'm' => 'M', 'l' => 'L', 'xl' => 'XL', 'xxl' => 'XXL'];

    /** @return list<array{slug:string,name:string,category:string,price:int,sizes:bool,file:string,summary:string,description:string,sku:string}> */
    public static function catalogue(): array
    {
        $badge = 'It carries the embroidered Lumina Africa Foundation badge with our line "Igniting light, inspiring change".';
        $buy = 'Every purchase supports the foundation\'s work. Choose your size when you order.';
        $mk = static fn(string $slug, string $name, string $cat, int $price, bool $sizes, string $file, string $summary, string $what, string $sku): array => [
            'slug' => $slug, 'name' => $name, 'category' => $cat, 'price' => $price, 'sizes' => $sizes, 'file' => $file, 'summary' => $summary,
            'description' => $what . ' ' . $badge . ($sizes ? ' ' . $buy : ' Every purchase supports the foundation\'s work.'), 'sku' => $sku,
        ];
        return [
            $mk('laf-beige-hoodie', 'LAF Beige Hoodie', 'jumpers-hoodies', 85000, true, 'laf-beige-hoodie.jpg', 'Beige pullover hoodie with the LAF badge.', 'A beige pullover hoodie with a front pocket and a drawstring hood.', 'LAF-HOOD-BEIGE'),
            $mk('laf-tan-hoodie', 'LAF Tan Hoodie', 'jumpers-hoodies', 85000, true, 'laf-tan-hoodie.jpg', 'Tan pullover hoodie with the LAF badge.', 'A tan pullover hoodie with a hood.', 'LAF-HOOD-TAN'),
            $mk('laf-white-hoodie', 'LAF White Hoodie', 'jumpers-hoodies', 85000, true, 'laf-white-hoodie.jpg', 'White pullover hoodie with the LAF badge.', 'A white pullover hoodie with a hood.', 'LAF-HOOD-WHITE'),
            $mk('laf-white-sleeveless-hoodie', 'LAF White Sleeveless Hoodie', 'jumpers-hoodies', 65000, true, 'laf-white-sleeveless-hoodie.jpg', 'White sleeveless hoodie with the LAF badge.', 'A white sleeveless hoodie with a hood and a front pocket.', 'LAF-HOOD-WHITE-SL'),
            $mk('laf-white-sweatshirt', 'LAF White Sweatshirt', 'jumpers-hoodies', 70000, true, 'laf-white-sweatshirt.jpg', 'White crew-neck sweatshirt with the LAF badge.', 'A white sweatshirt.', 'LAF-SWEAT-WHITE'),
            $mk('laf-black-bomber-jacket', 'LAF Black Bomber Jacket', 'jumpers-hoodies', 120000, true, 'laf-black-bomber-jacket.jpg', 'Black zip-up bomber jacket with the LAF badge.', 'A black bomber jacket with a zip front, ribbed collar and cuffs, and a sleeve pocket.', 'LAF-BOMBER-BLACK'),
            $mk('laf-beige-tshirt', 'LAF Beige T-shirt', 't-shirts', 35000, true, 'laf-beige-tshirt.jpg', 'Beige T-shirt with the LAF badge.', 'A beige T-shirt.', 'LAF-TEE-BEIGE'),
            $mk('laf-cream-shirt', 'LAF Cream Shirt', 'shirts-polos', 60000, true, 'laf-cream-shirt.jpg', 'Cream button-up shirt with the LAF badge.', 'A cream long-sleeve button-up shirt.', 'LAF-SHIRT-CREAM'),
            $mk('laf-red-polo', 'LAF Red Polo', 'shirts-polos', 45000, true, 'laf-red-polo.jpg', 'Red polo shirt with the LAF badge.', 'A red short-sleeve polo shirt.', 'LAF-POLO-RED'),
            $mk('laf-white-polo', 'LAF White Polo', 'shirts-polos', 45000, true, 'laf-white-polo.jpg', 'White polo shirt with the LAF badge.', 'A white short-sleeve polo shirt.', 'LAF-POLO-WHITE'),
            $mk('laf-white-cap', 'LAF White Cap', 'caps', 25000, false, 'laf-white-cap.jpg', 'White mesh-back cap with the LAF badge.', 'A white cap with a mesh back.', 'LAF-CAP-WHITE'),
            $mk('laf-white-backpack', 'LAF White Backpack', 'bags', 90000, false, 'laf-white-backpack.jpg', 'White and black backpack with the LAF badge.', 'A white backpack with a black base, a front zip pocket and a carry handle.', 'LAF-BAG-WHITE'),
        ];
    }

    /** Folder of the pictures that ship with the site. */
    public static function pictureDir(): string
    {
        return dirname(__DIR__, 3) . '/assets/img/shop/laf';
    }

    /** Create the two extra categories when they are missing. @return array<string,int> slug => id */
    private static function categories(): array
    {
        $want = ['shirts-polos' => ['Shirts & polos', 35], 'bags' => ['Bags', 50]];
        foreach ($want as $slug => [$name, $sort]) {
            if (Database::one('SELECT id FROM shop_categories WHERE slug = ?', [$slug]) === null) {
                Database::insert('shop_categories', ['slug' => $slug, 'name' => $name, 'sort_order' => $sort, 'is_active' => 1]);
            }
        }
        $out = [];
        foreach (Database::all('SELECT id, slug FROM shop_categories') as $c) {
            $out[(string) $c['slug']] = (int) $c['id'];
        }
        return $out;
    }

    /** The size choice of clothes, in the shop's option format. */
    private static function sizeOptions(): string
    {
        $values = [];
        foreach (self::SIZES as $v => $l) {
            $values[] = ['value' => $v, 'label' => $l, 'stock' => null, 'swatch' => null];
        }
        return (string) ProductService::encodeOptions(ProductService::normaliseOptions([['key' => 'size', 'label' => 'Size', 'required' => true, 'values' => $values]]));
    }

    /**
     * Add the products that are missing.
     * @return array{created:int,skipped:int,pictures:int,problems:list<string>}
     */
    public static function import(): array
    {
        $out = ['created' => 0, 'skipped' => 0, 'pictures' => 0, 'problems' => []];
        $cats = self::categories();
        $sort = 10;
        foreach (self::catalogue() as $p) {
            $sort += 10;
            if (Database::one('SELECT id FROM shop_products WHERE slug = ? OR sku = ?', [$p['slug'], $p['sku']]) !== null) {
                $out['skipped']++;
                continue;
            }
            $file = self::pictureDir() . '/' . $p['file'];
            if (!is_file($file)) {
                $out['problems'][] = $p['name'] . ': the picture file ' . $p['file'] . ' is missing on the server (upload the folder assets/img/shop/laf).';
                continue;
            }
            $id = Database::insert('shop_products', [
                'slug'        => $p['slug'],
                'category_id' => $cats[$p['category']] ?? null,
                'name'        => $p['name'],
                'summary'     => $p['summary'],
                'description' => $p['description'],
                'price'       => Money::toDb($p['price'] * Money::SCALE),
                'sku'         => $p['sku'],
                'track_stock' => 0,
                'stock_qty'   => null,
                'options'     => $p['sizes'] ? self::sizeOptions() : null,
                'is_featured' => 0,
                'is_sample'   => 0,
                'status'      => 'published',
                'sort_order'  => $sort,
            ]);
            $out['created']++;
            // the picture is copied first: the optimiser may move or remove the file it is given, and the shipped original must stay
            $tmp = tempnam(sys_get_temp_dir(), 'laf');
            if ($tmp === false || !@copy($file, $tmp)) {
                $out['problems'][] = $p['name'] . ': the picture could not be read.';
                continue;
            }
            try {
                ProductImages::upload($id, ['name' => $p['file'], 'tmp_name' => $tmp, 'error' => UPLOAD_ERR_OK, 'size' => (int) filesize($tmp)], ['crop' => 'portrait', 'alt' => $p['name'], 'allow_nonupload' => true]);
                $out['pictures']++;
            } catch (\Throwable $e) {
                $out['problems'][] = $p['name'] . ': ' . $e->getMessage();
            } finally {
                @unlink($tmp);
            }
        }
        OrderService::flushCounts();
        return $out;
    }

    /**
     * Delete the placeholder sample products and their picture files (the files bundled with the site are never deleted).
     * @param ?string $nameLike limits it to some names (the tests use it); null = every sample product
     * @return int how many were deleted
     */
    public static function removeSamples(?string $nameLike = null): int
    {
        $rows = Database::all('SELECT id FROM shop_products WHERE is_sample = 1' . ($nameLike !== null ? ' AND name LIKE ?' : ''), $nameLike !== null ? [$nameLike] : []);
        foreach ($rows as $r) {
            $id = (int) $r['id'];
            $images = ProductImages::forProduct($id);
            Database::execute('DELETE FROM shop_products WHERE id = ?', [$id]);          // picture rows go with it (ON DELETE CASCADE)
            foreach ($images as $img) {
                ProductImages::releaseFiles($img);
            }
        }
        return count($rows);
    }
}
