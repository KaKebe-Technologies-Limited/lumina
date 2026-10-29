<?php
declare(strict_types=1);

/**
 * Shop: the LAF branded products (import from the pictures shipped with the site, starting prices that stay editable) and the removal of the placeholder samples.
 * Included by bin/selftest.php or run alone:   /c/xampp/php/php.exe bin/selftest-shop-laf.php
 * Needs the local web server. Removes what it creates (products with sku LAF-%, zz-laf rows); the seeded sample products are never touched.
 */

if (!function_exists('check')) {
    if (PHP_SAPI !== 'cli') {
        exit('CLI only.');
    }
    require dirname(__DIR__) . '/app/bootstrap.php';
    if (\App\Core\Config::get('app.env') !== 'local') {
        exit('Refusing to run outside APP_ENV=local.' . PHP_EOL);
    }
    $fail = 0;
    function check(string $name, bool $ok, string $extra = ''): void
    {
        global $fail;
        echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($ok || $extra === '' ? '' : "  -> {$extra}") . PHP_EOL;
        if (!$ok) {
            $fail++;
        }
    }
    $standalone = true;
}

use App\Core\Auth;
use App\Core\Database;
use App\Models\User;
use App\Services\Shop\LafProducts;
use App\Services\Shop\ProductImages;

echo "-- shop: LAF products --\n";

$wipe = static function (): void {
    foreach (Database::all("SELECT id FROM shop_products WHERE sku LIKE 'LAF-%' OR name LIKE 'ZZ Sample%'") as $r) {
        $images = ProductImages::forProduct((int) $r['id']);
        Database::execute('DELETE FROM shop_products WHERE id = ?', [(int) $r['id']]);
        foreach ($images as $img) {
            ProductImages::releaseFiles($img);
        }
    }
    Database::execute("DELETE FROM users WHERE email = 'zz-laf-admin@example.test'");
    Database::execute('DELETE FROM rate_limits');
};
$catsBefore = array_column(Database::all('SELECT slug FROM shop_categories'), 'slug');
$samplesBefore = (int) Database::value('SELECT COUNT(*) FROM shop_products WHERE is_sample = 1 AND name NOT LIKE ?', ['ZZ Sample%'], 0);
$wipe();

$cat = LafProducts::catalogue();
check('the catalogue lists the 12 LAF products with a price above 0 and a unique address and SKU', count($cat) === 12 && count(array_unique(array_column($cat, 'slug'))) === 12 && count(array_unique(array_column($cat, 'sku'))) === 12
    && count(array_filter($cat, static fn(array $p): bool => $p['price'] > 0)) === 12);
check('every product picture ships with the site (assets/img/shop/laf)', count(array_filter($cat, static fn(array $p): bool => is_file(LafProducts::pictureDir() . '/' . $p['file']))) === 12);

$r = LafProducts::import();
check('import: 12 products created with a picture each, no problems', $r['created'] === 12 && $r['pictures'] === 12 && $r['problems'] === [], json_encode($r));
$rows = Database::all("SELECT p.*, (SELECT COUNT(*) FROM shop_product_images i WHERE i.product_id = p.id) AS pics FROM shop_products p WHERE p.sku LIKE 'LAF-%' ORDER BY p.sort_order");
check('import: all published, real products (not samples), one cover picture each, prices as listed', count($rows) === 12 && count(array_filter($rows, static fn(array $x): bool => $x['status'] === 'published' && (int) $x['is_sample'] === 0 && (int) $x['pics'] === 1)) === 12
    && (float) Database::value("SELECT price FROM shop_products WHERE slug = 'laf-red-polo'") === 45000.0 && (float) Database::value("SELECT price FROM shop_products WHERE slug = 'laf-black-bomber-jacket'") === 120000.0);
check('import: clothes get a required size choice (S to XXL), the cap and the backpack none', str_contains((string) Database::value("SELECT options FROM shop_products WHERE slug = 'laf-red-polo'"), '"xxl"')
    && Database::value("SELECT options FROM shop_products WHERE slug = 'laf-white-cap'") === null && Database::value("SELECT options FROM shop_products WHERE slug = 'laf-white-backpack'") === null);
check('import: the two new categories exist and the products are in the right ones', (int) Database::value("SELECT COUNT(*) FROM shop_products p JOIN shop_categories c ON c.id = p.category_id WHERE c.slug = 'shirts-polos' AND p.sku IN ('LAF-POLO-RED','LAF-POLO-WHITE','LAF-SHIRT-CREAM')", [], 0) === 3
    && (int) Database::value("SELECT COUNT(*) FROM shop_products p JOIN shop_categories c ON c.id = p.category_id WHERE c.slug = 'bags' AND p.sku = 'LAF-BAG-WHITE'", [], 0) === 1);
check('import: the shipped pictures stay in place (the importer copies them)', count(array_filter($cat, static fn(array $p): bool => is_file(LafProducts::pictureDir() . '/' . $p['file']))) === 12);
$again = LafProducts::import();
check('import twice: nothing is added the second time', $again['created'] === 0 && $again['skipped'] === 12 && (int) Database::value("SELECT COUNT(*) FROM shop_products WHERE sku LIKE 'LAF-%'", [], 0) === 12);

// the pictures can be stored again WHOLE (the first import cropped tall photos)
$oldPath = (string) Database::value("SELECT i.path FROM shop_product_images i JOIN shop_products p ON p.id = i.product_id WHERE p.slug = 'laf-red-polo'");
$capId = (int) Database::value("SELECT id FROM shop_products WHERE slug = 'laf-white-cap'");
Database::insert('shop_product_images', ['product_id' => $capId, 'path' => 'zz-laf-extra.jpg', 'alt' => 'extra', 'sort_order' => 90, 'is_cover' => 0]);
$ref = LafProducts::refreshPictures();
$newPath = (string) Database::value("SELECT i.path FROM shop_product_images i JOIN shop_products p ON p.id = i.product_id WHERE p.slug = 'laf-red-polo'");
check('refresh: every LAF product with one picture gets it stored again (11 of 12), the one with an extra picture is left alone', $ref['refreshed'] === 11 && $ref['skipped'] === ['LAF White Cap'] && $ref['problems'] === [], json_encode($ref));
check('refresh: the picture is replaced (new file), still exactly one, and it is the cover', $newPath !== '' && $newPath !== $oldPath && (int) Database::value("SELECT COUNT(*) FROM shop_product_images i JOIN shop_products p ON p.id = i.product_id WHERE p.slug = 'laf-red-polo'", [], 0) === 1
    && (int) Database::value("SELECT i.is_cover FROM shop_product_images i JOIN shop_products p ON p.id = i.product_id WHERE p.slug = 'laf-red-polo'", [], 0) === 1);
check('refresh: the old picture file is deleted and the new one exists', !is_file(UPLOAD_PATH . '/' . $oldPath) && is_file(UPLOAD_PATH . '/' . $newPath));
check('refresh: the product with extra pictures keeps both', (int) Database::value('SELECT COUNT(*) FROM shop_product_images WHERE product_id = ?', [$capId], 0) === 2);
Database::execute("DELETE FROM shop_product_images WHERE path = 'zz-laf-extra.jpg'");
$css = (string) file_get_contents(dirname(__DIR__) . '/assets/css/main.css');
check('shop cards: a 4:5 frame with the picture fitted inside (whole product), not cropped', preg_match('/\.sh-card__media\s*\{[^}]*aspect-ratio:\s*4\s*\/\s*5/', $css) === 1 && preg_match('/\.sh-card__img\s*\{[^}]*object-fit:\s*contain/', $css) === 1);
// the owner edits a price and renames a product: an import never undoes it
Database::execute("UPDATE shop_products SET price = 99000, name = 'LAF Red Polo (club)' WHERE slug = 'laf-red-polo'");
LafProducts::import();
check('prices and names edited by the owner are never overwritten by a later import', (float) Database::value("SELECT price FROM shop_products WHERE slug = 'laf-red-polo'") === 99000.0 && Database::value("SELECT name FROM shop_products WHERE slug = 'laf-red-polo'") === 'LAF Red Polo (club)');
Database::execute("UPDATE shop_products SET price = 45000, name = 'LAF Red Polo' WHERE slug = 'laf-red-polo'");

// the placeholder samples can be removed (only the test ones here)
$s1 = Database::insert('shop_products', ['slug' => 'zz-sample-one', 'name' => 'ZZ Sample One', 'price' => '1000', 'status' => 'published', 'is_sample' => 1]);
$s2 = Database::insert('shop_products', ['slug' => 'zz-sample-two', 'name' => 'ZZ Sample Two', 'price' => '2000', 'status' => 'published', 'is_sample' => 1]);
Database::insert('shop_product_images', ['product_id' => $s1, 'path' => 'zz-laf-none.jpg', 'sort_order' => 10, 'is_cover' => 1]);
$gone = LafProducts::removeSamples('ZZ Sample%');
check('removing samples deletes them with their picture rows, and nothing else', $gone === 2 && Database::one('SELECT id FROM shop_products WHERE id IN (?, ?)', [$s1, $s2]) === null
    && (int) Database::value('SELECT COUNT(*) FROM shop_product_images WHERE product_id = ?', [$s1], 0) === 0
    && (int) Database::value("SELECT COUNT(*) FROM shop_products WHERE is_sample = 1 AND name NOT LIKE 'ZZ Sample%'", [], 0) === $samplesBefore && (int) Database::value("SELECT COUNT(*) FROM shop_products WHERE sku LIKE 'LAF-%'", [], 0) === 12);

// the storefront and the admin
$base = 'http://localhost/' . basename(dirname(__DIR__));
$jar = tempnam(sys_get_temp_dir(), 'lafj');
$req = static function (string $method, string $path, array $form = []) use ($base, $jar): array {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['code' => $code, 'body' => substr($raw, $hs)];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
$page = $req('GET', '/shop/laf-red-polo');
check('storefront: a product page opens with its name and price (the size choice shows once the shop is open for orders)', $page['code'] === 200 && str_contains($page['body'], 'LAF Red Polo') && str_contains($page['body'], '45,000'), (string) $page['code']);
$list = $req('GET', '/shop');
check('storefront: the shop lists the LAF products and the new categories', $list['code'] === 200 && str_contains($list['body'], 'LAF Beige Hoodie') && str_contains($list['body'], 'Shirts &amp; polos') && str_contains($list['body'], 'Bags'));

User::create(['name' => 'ZZ Laf Admin', 'email' => 'zz-laf-admin@example.test', 'password_hash' => Auth::hashPassword('Laf admin pass 55'), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
Database::execute('DELETE FROM rate_limits');
$pg = $req('GET', '/admin/login');
$req('POST', '/admin/login', ['_token' => $tok($pg['body']), 'email' => 'zz-laf-admin@example.test', 'password' => 'Laf admin pass 55']);
$ad = $req('GET', '/admin/shop/products');
check('admin: the "Show the whole product in the LAF pictures" button is there', str_contains($ad['body'], 'refresh-laf-pictures'));
check('admin: with every LAF product present no "Add" button; the sample button is there (the samples exist)', $ad['code'] === 200 && !str_contains($ad['body'], 'import-laf') && ($samplesBefore === 0 || str_contains($ad['body'], 'delete-samples')), (string) $ad['code']);
foreach (Database::all("SELECT id FROM shop_products WHERE sku = 'LAF-CAP-WHITE'") as $cap) {          // the picture files go with the product, as the Delete button does it
    $capImages = ProductImages::forProduct((int) $cap['id']);
    Database::execute('DELETE FROM shop_products WHERE id = ?', [(int) $cap['id']]);
    foreach ($capImages as $img) {
        ProductImages::releaseFiles($img);
    }
}
$ad = $req('GET', '/admin/shop/products');
check('admin: with one product missing the "Add the 1 LAF product" button appears', str_contains($ad['body'], 'Add the 1 LAF product') && str_contains($ad['body'], 'import-laf'));
$post = $req('POST', '/admin/shop/products/import-laf', ['_token' => $tok($ad['body'])]);
check('admin: pressing it adds the missing product (redirect) and the price is editable in the product form', in_array($post['code'], [302, 303], true) && (int) Database::value("SELECT COUNT(*) FROM shop_products WHERE sku = 'LAF-CAP-WHITE'", [], 0) === 1, (string) $post['code']);
$pid = (int) Database::value("SELECT id FROM shop_products WHERE sku = 'LAF-CAP-WHITE'", [], 0);
$edit = $req('GET', '/admin/shop/products/' . $pid . '/edit');
$priceTag = preg_match('/<input[^>]*name="price"[^>]*>/', $edit['body'], $pm) === 1 ? $pm[0] : '';
check('admin: the product Edit page has an editable price field holding the starting price', $edit['code'] === 200 && preg_match('/value="25[, ]?000/', $priceTag) === 1 && !str_contains($priceTag, 'readonly') && !str_contains($priceTag, 'disabled'), $priceTag);
$noCsrf = $req('POST', '/admin/shop/products/import-laf', []);
check('admin: the import button needs the CSRF token (403)', $noCsrf['code'] === 403, (string) $noCsrf['code']);
@unlink($jar);

// ---- clean up ------------------------------------------------------------------------------------------------------------------------
$wipe();
foreach (['shirts-polos', 'bags'] as $slug) {
    if (!in_array($slug, $catsBefore, true)) {
        Database::execute('DELETE FROM shop_categories WHERE slug = ? AND NOT EXISTS (SELECT 1 FROM shop_products WHERE category_id = shop_categories.id)', [$slug]);
    }
}
check('clean-up: the shop is back as it was (no LAF test product left, the seeded samples untouched)', (int) Database::value("SELECT COUNT(*) FROM shop_products WHERE sku LIKE 'LAF-%'", [], 0) === 0
    && (int) Database::value('SELECT COUNT(*) FROM shop_products WHERE is_sample = 1', [], 0) === $samplesBefore);

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL OK\n" : "\n{$fail} FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
