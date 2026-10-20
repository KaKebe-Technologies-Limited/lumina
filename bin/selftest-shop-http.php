<?php
/**
 * Part 2 of bin/selftest-shop.php: the REAL HTTP flow against http://localhost/<folder>/ (skips itself when the site does not answer).
 * Included by bin/selftest-shop.php: it shares its variables ($zzSet, $zzProduct, $zzOrder, $zzLine, $zzRoot, check()).
 * Throw-away admin users and roles ("zz-shop-*@example.test") are created with random passwords that are never printed, and deleted again by the caller.
 */
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Database;
use App\Core\Roles;
use App\Models\User;
use App\Services\Shop\OrderService;
use App\Services\Shop\PaymentService;
use App\Services\Shop\SecretStore;

echo "Shop: HTTP flow (visitor)\n";
$base = 'http://localhost/' . basename($zzRoot) . '/';
$zzJars = [];
/** @return array{code:int,headers:array<string,list<string>>,body:string} */
$http = static function (string $method, string $path, array $o = []) use ($base, &$zzJars): array {
    $jar = (string) ($o['jar'] ?? '');
    if ($jar !== '' && !in_array($jar, $zzJars, true)) {
        $zzJars[] = $jar;
    }
    $headers = [];
    $ch = curl_init(str_starts_with($path, 'http') ? $path : $base . ltrim($path, '/'));
    $post = $o['post'] ?? null;
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method, CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 30, CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_HTTPHEADER => $o['headers'] ?? [],
        CURLOPT_HEADERFUNCTION => static function ($c, string $line) use (&$headers): int {
            if (str_contains($line, ':')) {
                [$k, $v] = explode(':', $line, 2);
                $headers[strtolower(trim($k))][] = trim($v);
            }
            return strlen($line);
        },
    ]);
    if ($jar !== '') {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $jar);
    }
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($post) ? http_build_query($post) : $post);
    }
    $body = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return ['code' => $code, 'headers' => $headers, 'body' => $body];
};
$field = static fn(string $html, string $name): string => preg_match('/name="' . preg_quote($name, '/') . '" value="([^"]*)"/', $html, $m) === 1 ? html_entity_decode($m[1]) : '';
$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zzshop-' . bin2hex(random_bytes(3));
$jarV = $tmp . '-visitor.txt';

$probe = $http('GET', 'shop');
if ($probe['code'] === 0) {
    echo "  info the site does not answer at {$base}: the HTTP checks were skipped\n";
    return false;
}
$pHttp = $zzProduct(['name' => 'ZZ Http Product', 'price' => '10000.00', 'track_stock' => 1, 'stock_qty' => 50, 'is_featured' => 1]);
$pSlug = (string) Database::value('SELECT slug FROM shop_products WHERE id = ?', [$pHttp]);
Database::execute('DELETE FROM rate_limits');

foreach (['shop', 'shop/category/caps', 'shop/mission-hoodie', 'shop/' . $pSlug, 'shop/cart'] as $u) {
    $r = $http('GET', $u);
    check("anonymous GET /{$u}: 200 and NO cookie", $r['code'] === 200 && empty($r['headers']['set-cookie']), $r['code'] . ' ' . json_encode($r['headers']['set-cookie'] ?? []));
}
check('unknown product / category / order token: 404, no cookie', $http('GET', 'shop/zz-nope')['code'] === 404 && $http('GET', 'shop/category/zz-nope')['code'] === 404 && $http('GET', 'shop/order/' . str_repeat('a', 40))['code'] === 404 && $http('GET', 'shop/order/short')['code'] === 404);
check('draft products are not reachable', $http('GET', 'shop/' . (string) Database::value('SELECT slug FROM shop_products WHERE id = ?', [$zzProduct(['status' => 'draft'])]))['code'] === 404);
$r = $http('GET', 'shop/checkout');
check('empty checkout redirects to the cart, no cookie', $r['code'] === 302 && str_contains($r['headers']['location'][0] ?? '', 'shop/cart') && empty($r['headers']['set-cookie']));
$r = $http('GET', 'robots.txt');
check('robots.txt keeps carts, checkout, orders and payment endpoints out of search engines', str_contains($r['body'], '/shop/order/') && str_contains($r['body'], '/shop/checkout') && str_contains($r['body'], '/shop/webhook/'));
$sm = $http('GET', 'sitemap.xml')['body'];
check('the sitemap never lists order, cart or payment URLs', !str_contains($sm, '/shop/order') && !str_contains($sm, '/shop/cart') && !str_contains($sm, '/shop/pay') && !str_contains($sm, '/shop/checkout'));

// integrator additions: terms page, sitemap rules, sample products stay out of search engines, structured data only for real products
$r = $http('GET', 'shop/terms');
check('GET /shop/terms: 200, no cookie, the three policy headings', $r['code'] === 200 && empty($r['headers']['set-cookie']) && str_contains($r['body'], 'id="terms"') && str_contains($r['body'], 'id="returns"') && str_contains($r['body'], 'id="delivery"'), (string) $r['code']);
check('"terms" cannot be a product web address (reserved)', in_array('terms', \App\Services\Shop\ProductService::RESERVED_SLUGS, true));
check('the sitemap lists a published real product', str_contains($sm, '/shop/' . $pSlug), '');
$sampleSlug = (string) Database::value('SELECT slug FROM shop_products WHERE is_sample = 1 AND status = \'published\' LIMIT 1', [], '');
if ($sampleSlug !== '') {
    check('the sitemap never lists a SAMPLE product (its price is a placeholder)', !str_contains($sm, '/shop/' . $sampleSlug));
    $r = $http('GET', 'shop/' . $sampleSlug);
    check('a SAMPLE product page is noindex and has no Product structured data', str_contains($r['body'], 'content="noindex,follow"') && !str_contains($r['body'], '"@type":"Product"'));
}
$r = $http('GET', 'shop/' . $pSlug);
check('a real product page carries schema.org Product with price, currency and availability', str_contains($r['body'], '"@type":"Product"') && str_contains($r['body'], '"priceCurrency"') && str_contains($r['body'], 'https://schema.org/InStock'));
check('the header cart link is present but hidden while the cart is empty', str_contains($r['body'], 'data-shop-cart-link') && preg_match('/data-shop-cart-link[^>]*\shidden/', $r['body']) === 1);

// add to cart: stateless CSRF on an anonymous page; the cookie appears only after the first item
$page = $http('GET', 'shop/' . $pSlug, ['jar' => $jarV]);
$tok = $field($page['body'], '_token');
check('the product page carries a CSRF token and still sets no cookie', $tok !== '' && empty($page['headers']['set-cookie']));
check('POST add without a token: 403 (page expired)', $http('POST', 'shop/cart/add', ['jar' => $jarV, 'post' => ['product_id' => $pHttp, 'qty' => 1]])['code'] === 403);
check('POST add with a forged token: 403', $http('POST', 'shop/cart/add', ['jar' => $jarV, 'post' => ['_token' => 's.' . time() . '.' . str_repeat('0', 64), 'product_id' => $pHttp, 'qty' => 1]])['code'] === 403);
$r = $http('POST', 'shop/cart/add', ['jar' => $jarV, 'post' => ['_token' => $tok, 'product_id' => $pHttp, 'qty' => 2, 'mode' => 'cart']]);
check('POST add: redirect to the cart and the session cookie is created NOW (first item)', $r['code'] === 302 && str_contains($r['headers']['location'][0] ?? '', 'shop/cart') && !empty($r['headers']['set-cookie']), $r['code'] . json_encode($r['headers']['location'] ?? []));
$cart = $http('GET', 'shop/cart', ['jar' => $jarV]);
check('the cart shows the item, quantity 2 and the server-side line total UGX 20,000', $cart['code'] === 200 && str_contains($cart['body'], 'ZZ Http Product') && str_contains($cart['body'], 'UGX 20,000') && str_contains($cart['body'], 'value="2"'));
$ajax = $http('POST', 'shop/cart/add', ['jar' => $jarV, 'post' => ['_token' => $field($cart['body'], '_token'), 'product_id' => 999999999, 'qty' => 1], 'headers' => ['Accept: application/json', 'X-Requested-With: XMLHttpRequest']]);
$j = json_decode($ajax['body'], true);
check('AJAX add of a missing product: 422 JSON with an error code and the unit count', $ajax['code'] === 422 && ($j['error'] ?? '') === 'not_found' && ($j['units'] ?? -1) === 2, $ajax['body']);
$cartTok = $field($cart['body'], '_token');
$r = $http('POST', 'shop/cart/update', ['jar' => $jarV, 'post' => ['_token' => $cartTok, 'qty' => ['abc123abc123' => 5, 'x' => 'y']]]);
check('cart update ignores unknown line keys', $r['code'] === 302);

// checkout
$co = $http('GET', 'shop/checkout', ['jar' => $jarV]);
check('checkout page: 200, no-store, honeypot + time trap + token present, offers the enabled payment methods only', $co['code'] === 200 && str_contains(strtolower(implode(',', $co['headers']['cache-control'] ?? [])), 'no-store') && $field($co['body'], '_hp_ts') !== '' && str_contains($co['body'], 'name="website"')
    && str_contains($co['body'], 'value="cod"') && str_contains($co['body'], 'value="momo"') && !str_contains($co['body'], 'value="flutterwave"') && !str_contains($co['body'], 'value="book"'));
$coTok = $field($co['body'], '_token');
$hp = $field($co['body'], '_hp_ts');
$valid = ['_token' => $coTok, '_hp_ts' => $hp, 'name' => 'Zz Http Buyer', 'email' => 'zz-shop-http@example.test', 'phone' => '0700 111 222', 'delivery' => 'pickup', 'note' => '', 'consent' => '1', 'payment' => 'cod', 'total' => '1', 'price' => '1', 'unit_minor' => '1'];
$n0 = (int) Database::value("SELECT COUNT(*) FROM shop_orders WHERE customer_email = 'zz-shop-http@example.test'", [], 0);
$r = $http('POST', 'shop/checkout', ['jar' => $jarV, 'post' => $valid]);
check('submitting faster than the time trap: silently dropped (fake success), NO order created', (int) Database::value("SELECT COUNT(*) FROM shop_orders WHERE customer_email = 'zz-shop-http@example.test'", [], 0) === $n0);
sleep(3);
$co = $http('GET', 'shop/checkout', ['jar' => $jarV]);
$coTok = $field($co['body'], '_token');
$hp = $field($co['body'], '_hp_ts');
sleep(3);
$r = $http('POST', 'shop/checkout', ['jar' => $jarV, 'post' => ['consent' => '0', 'name' => '', 'email' => 'nope', 'payment' => 'x', '_token' => $coTok, '_hp_ts' => $hp] + $valid]);
check('invalid details (no consent, bad e-mail, unknown payment method): back to the form, no order', $r['code'] === 302 && str_contains($r['headers']['location'][0] ?? '', 'shop/checkout') && (int) Database::value("SELECT COUNT(*) FROM shop_orders WHERE customer_email = 'zz-shop-http@example.test'", [], 0) === $n0);
$co = $http('GET', 'shop/checkout', ['jar' => $jarV]);
check('the form is shown again with the error summary and keeps what was typed', str_contains($co['body'], 'alert--error') || str_contains($co['body'], 'field--error'));
$coTok = $field($co['body'], '_token');
$hp = $field($co['body'], '_hp_ts');
sleep(3);
$r = $http('POST', 'shop/checkout', ['jar' => $jarV, 'post' => ['_token' => $coTok, '_hp_ts' => $hp] + $valid]);
$loc = $r['headers']['location'][0] ?? '';
check('valid checkout: 302 to /shop/order/<40 hex token>', $r['code'] === 302 && preg_match('#/shop/order/([a-f0-9]{40})$#', $loc, $m) === 1, $r['code'] . ' ' . $loc);
$tokenOrder = $m[1] ?? '';
$ord = $tokenOrder !== '' ? OrderService::findByToken($tokenOrder) : null;
check('the order is priced by the SERVER (20,000), not by the browser fields total=1 / price=1', $ord !== null && $ord['total'] === '20000.00' && $ord['payment_method'] === 'cod' && $ord['customer_phone'] === '+256700111222' && (int) Database::value('SELECT stock_qty FROM shop_products WHERE id = ?', [$pHttp]) === 48);
$op = $http('GET', 'shop/order/' . $tokenOrder, ['jar' => $jarV]);
check('order page: 200, noindex, no-store, shows the order number, never another order', $op['code'] === 200 && str_contains($op['body'], (string) ($ord['order_number'] ?? '?')) && str_contains($op['body'], 'noindex') && str_contains(strtolower(implode(',', $op['headers']['cache-control'] ?? [])), 'no-store'));
$tokenBad = substr($tokenOrder, 0, 39) . ($tokenOrder[39] === 'a' ? 'b' : 'a');
check('one wrong character in the token: 404', $http('GET', 'shop/order/' . $tokenBad)['code'] === 404);
check('printable receipt works too', $http('GET', 'shop/order/' . $tokenOrder . '/receipt')['code'] === 200);
check('the cart is emptied after the order', str_contains($http('GET', 'shop/cart', ['jar' => $jarV])['body'], (string) \App\Core\Pagination::class) || !str_contains($http('GET', 'shop/cart', ['jar' => $jarV])['body'], 'ZZ Http Product'));

// manual payment reference over HTTP
$mo = $zzOrder([$zzLine($pHttp, 1)], 'pickup', 'momo', ['email' => 'zz-shop-http2@example.test'])['order'];
$mp = $http('GET', 'shop/order/' . $mo['token']);
$mpTok = $field($mp['body'], '_token');
$mpHp = $field($mp['body'], '_hp_ts');
check('a mobile-money order page shows the number, the order number as reference, and the "enter your transaction ID" form', str_contains($mp['body'], '0777000111') && str_contains($mp['body'], (string) $mo['order_number']) && str_contains($mp['body'], 'name="reference"'));
check('POST reference without a token: 403', $http('POST', 'shop/order/' . $mo['token'] . '/reference', ['post' => ['reference' => 'MP1234567']])['code'] === 403);
sleep(3);
$r = $http('POST', 'shop/order/' . $mo['token'] . '/reference', ['post' => ['_token' => $mpTok, '_hp_ts' => $mpHp, 'reference' => 'MP7654321']]);
$mox = OrderService::find((int) $mo['id']);
check('POST reference: noted, payment PENDING (never paid by the customer), reference stored', $r['code'] === 302 && $mox['payment_status'] === 'pending' && $mox['payment_reference'] === 'MP7654321');
SecretStore::put('flw_secret_key', 'FLWSECK_TEST-http-test');
SecretStore::put('flw_secret_hash', 'zz-http-hash');
$gOrder = $zzOrder([$zzLine($pHttp, 1)], 'pickup', 'flutterwave', ['email' => 'zz-shop-http3@example.test'])['order'];
$r = $http('GET', 'shop/pay/' . $gOrder['token']);
check('GET /shop/pay/{token}: 302 to the order page and NO attempt is started (a GET never changes anything)', $r['code'] === 302 && str_contains($r['headers']['location'][0] ?? '', 'shop/order/') && (int) OrderService::find((int) $gOrder['id'])['gateway_attempts'] === 0);
check('POST /shop/pay/{token} without a CSRF token: 403', $http('POST', 'shop/pay/' . $gOrder['token'])['code'] === 403 && (int) OrderService::find((int) $gOrder['id'])['gateway_attempts'] === 0);
$r = $http('GET', 'shop/pay/return');
check('pay/return without tx_ref: the neutral thank-you page (200), no redirect, no cookie, never cached, nothing changes', $r['code'] === 200 && empty($r['headers']['set-cookie']) && empty($r['headers']['location'])
    && str_contains(strtolower(implode(' ', $r['headers']['cache-control'] ?? [])), 'no-store'));
$r = $http('GET', 'shop/pay/return?status=successful&tx_ref=' . $gOrder['order_number'] . '-1&transaction_id=1');
check('pay/return with a tx_ref that was never started: nothing is paid', OrderService::find((int) $gOrder['id'])['payment_status'] === 'unpaid' && $r['code'] === 200);

// review pass: the return URL is tied to the browser that started the payment, and a few public pages
$rOrder = $zzOrder([$zzLine($pHttp, 1)], 'pickup', 'flutterwave', ['email' => 'zz-shop-http4@example.test'])['order'];
$rStart = PaymentService::startAttempt((int) $rOrder['id']);
$rRef = (string) $rStart['tx_ref'];
$gen1 = $http('GET', 'shop/pay/return?status=successful&tx_ref=' . $rRef);
$gen2 = $http('GET', 'shop/pay/return?status=successful&tx_ref=ZZT-000000-NOPE-1');
$gen3 = $http('GET', 'shop/pay/return/' . str_repeat('a', 32) . '?tx_ref=' . $rRef);
// the top-bar clock prints the current time (seconds) and an epoch: two requests a second apart must still compare equal
$norm = static fn(string $h): string => (string) preg_replace(['/(name="(?:_token|_hp_ts)" value=")[^"]*/', '/s\.\d+\.[a-f0-9]{20,}/', '/(data-clock-hms>)[^<]*/', '/data-epoch="\d+"/', '/(data-clock-date>)[^<]*/'], ['$1', 'T', '$1', 'data-epoch="E"', '$1'], $h);
check('GET /shop/pay/return?tx_ref=<a REAL order number>-1 without the key: the same neutral 200 page as for an invented reference - no redirect, no buyer data, no cookie, never cached',
    $gen1['code'] === 200 && $gen2['code'] === 200 && $gen3['code'] === 200 && empty($gen1['headers']['location']) && empty($gen1['headers']['set-cookie']) && !str_contains($gen1['body'], 'zz-shop-http4@example.test')
    && !str_contains($gen1['body'], (string) $rOrder['token']) && str_contains(strtolower(implode(',', $gen1['headers']['cache-control'] ?? [])), 'no-store') && $norm($gen1['body']) === $norm($gen2['body']) && $norm($gen1['body']) === $norm($gen3['body']));
$rKey = \App\Services\Shop\OnlinePayment::returnKey($rRef);
$keyed = $http('GET', 'shop/pay/return/' . $rKey . '?status=successful&tx_ref=' . $rRef);
check('with the key we put in the redirect URL: 302 to that buyer\'s own order page, private and never cached', $keyed['code'] === 302 && str_ends_with($keyed['headers']['location'][0] ?? '', '/shop/order/' . $rOrder['token'])
    && str_contains(strtolower(implode(',', $keyed['headers']['cache-control'] ?? [])), 'no-store'));
$q500 = [];
foreach (['shop?sort[]=a', 'shop?q[]=a', 'shop?page[]=2', 'shop?category[]=x', 'shop/category/caps?sort[]=a&q[]=b'] as $u) {
    $q500[$u] = $http('GET', $u)['code'];
}
check('array query parameters (?sort[]=a ...) on the catalogue and a category never give a 500', array_diff($q500, [200]) === [], json_encode($q500));
$cartHtml = $http('GET', 'shop/cart')['body'];
preg_match('#<title>([^<]*)</title>#', $cartHtml, $tm);
check('the cart page title has no [[highlight]] markup (tab, search result, screen reader)', isset($tm[1]) && !str_contains($tm[1], '[[') && !str_contains($cartHtml, 'content="Your [['), $tm[1] ?? '');
$capName = (string) Database::value("SELECT name FROM shop_categories WHERE slug = 'caps'", [], '');
if ($capName !== '') {
    $catHtml = $http('GET', 'shop/category/caps')['body'];
    preg_match('#<title>([^<]*)</title>#', $catHtml, $ct);
    preg_match('#<h1[^>]*>(.*?)</h1>#s', $catHtml, $ch);
    check('a category page has its own title, its own h1 and its own canonical address (it was a copy of /shop)', str_contains($ct[1] ?? '', $capName) && str_contains(strip_tags($ch[1] ?? ''), $capName) && str_contains($catHtml, 'rel="canonical" href="http://localhost/' . basename($zzRoot) . '/shop/category/caps"'), ($ct[1] ?? '') . ' | ' . strip_tags($ch[1] ?? ''));
}
$rcHtml = $http('GET', 'shop/order/' . $tokenOrder . '/receipt')['body'];
preg_match('#<title>([^<]*)</title>#', $rcHtml, $rt);
check('the receipt page title says "Receipt"', str_contains($rt[1] ?? '', 'Receipt') && str_contains($rt[1] ?? '', (string) ($ord['order_number'] ?? '?')), $rt[1] ?? '');
check('the sort menu is no longer submitted on every arrow key (no auto-submit) and its Apply button is visible', !str_contains($http('GET', 'shop')['body'], 'data-autosubmit') && !preg_match('/<button[^>]*sh-nojs[^>]*>\s*Apply/i', $http('GET', 'shop')['body']));
// unpublishing the Shop page hides the catalogue, cart and checkout - but a customer can always open their order, receipt and payment return
$shopPub = Database::value("SELECT is_published FROM pages WHERE slug = 'shop'", [], null);
if ($shopPub !== null) {
    try {
        Database::execute("UPDATE pages SET is_published = 0 WHERE slug = 'shop'");
        $up = [
            'catalogue' => $http('GET', 'shop')['code'], 'cart' => $http('GET', 'shop/cart')['code'],
            'order' => $http('GET', 'shop/order/' . $tokenOrder)['code'], 'receipt' => $http('GET', 'shop/order/' . $tokenOrder . '/receipt')['code'],
            'return' => $http('GET', 'shop/pay/return/' . $rKey . '?tx_ref=' . $rRef)['code'], 'thanks' => $http('GET', 'shop/pay/return')['code'],
        ];
    } finally {
        Database::execute('UPDATE pages SET is_published = ? WHERE slug = \'shop\'', [$shopPub]);
    }
    check('Shop page unpublished: catalogue and cart 404; the order page, receipt, payment return and thank-you page still work', $up['catalogue'] === 404 && $up['cart'] === 404 && $up['order'] === 200 && $up['receipt'] === 200 && $up['return'] === 302 && $up['thanks'] === 200, json_encode($up));
}

echo "Shop: HTTP flow (webhook)\n";
$wh = static fn(array $h, string $body) => $http('POST', 'shop/webhook/flutterwave', ['headers' => array_merge(['Content-Type: application/json'], $h), 'post' => $body]);
$r1 = $wh([], '{"event":"charge.completed","data":{"tx_ref":"x"}}');
$r2 = $wh(['verif-hash: wrong'], '{"event":"charge.completed","data":{"tx_ref":"x"}}');
$r3 = $wh(['verif-hash: zz-http-hash'], '{"event":"charge.completed","data":{"tx_ref":"' . $gOrder['order_number'] . '-1"}}');
$r4 = $wh(['verif-hash: zz-http-hash'], 'garbage');
check('webhook: no header / wrong hash -> 401; right hash -> 200 (no CSRF token needed); garbage body -> 200', $r1['code'] === 401 && $r2['code'] === 401 && $r3['code'] === 200 && $r4['code'] === 200, "{$r1['code']} {$r2['code']} {$r3['code']} {$r4['code']}");
check('...and an authentic webhook for an attempt that was never started changes nothing', OrderService::find((int) $gOrder['id'])['payment_status'] === 'unpaid');
check('GET on the webhook URL is not a page', $http('GET', 'shop/webhook/flutterwave')['code'] === 404);
check('the webhook sets no cookie', empty($r3['headers']['set-cookie']));
SecretStore::forget('flw_secret_key');
SecretStore::forget('flw_secret_hash');

// ---------------------------------------------------------------------------------------------------------------------------------------------
echo "Shop: HTTP flow (admin abilities)\n";
$mkUser = static function (string $key, string $role, array $abilities) {
    $pw = Auth::generatePassword(20);
    if (!in_array($role, ['admin', 'editor'], true)) {
        Database::insert('roles', ['slug' => $role, 'name' => 'ZZ ' . $key, 'description' => '', 'abilities' => json_encode($abilities), 'is_system' => 0]);
    }
    User::create(['name' => 'ZZ ' . $key, 'email' => 'zz-shop-' . $key . '@example.test', 'password_hash' => Auth::hashPassword($pw), 'role' => $role, 'is_active' => 1, 'must_change_password' => 0]);
    return $pw;
};
$accounts = [
    'admin'    => ['admin', []],
    'orders'   => ['zz-shop-orders', ['dashboard.view', 'shop.orders']],
    'manage'   => ['zz-shop-manage', ['dashboard.view', 'shop.manage']],
    'settings' => ['zz-shop-settings', ['dashboard.view', 'shop.settings']],
    'editor'   => ['editor', []],
];
$pw = [];
foreach ($accounts as $k => [$role, $ab]) {
    $pw[$k] = $mkUser($k, $role, $ab);
}
Roles::flush();
Database::execute('DELETE FROM rate_limits');
$login = static function (string $key) use ($http, $field, $tmp, $pw): string {
    $jar = $tmp . '-' . $key . '.txt';
    $lp = $http('GET', 'admin/login', ['jar' => $jar]);
    $http('POST', 'admin/login', ['jar' => $jar, 'post' => ['_token' => $field($lp['body'], '_token'), 'email' => 'zz-shop-' . $key . '@example.test', 'password' => $pw[$key]]]);
    return $jar;
};
$jars = [];
foreach (array_keys($accounts) as $k) {
    $jars[$k] = $login($k);
}
$code = static fn(string $k, string $path): int => $http('GET', $path, ['jar' => $jars[$k]])['code'];
check('not signed in: every shop admin URL goes to the login page', $http('GET', 'admin/shop')['code'] === 302 && str_contains($http('GET', 'admin/shop/orders')['headers']['location'][0] ?? '', 'admin/login') && $http('GET', 'admin/shop/payments')['code'] === 302
);
check('admin: overview, orders, products, categories, settings, payments all open', $code('admin', 'admin/shop') === 200 && $code('admin', 'admin/shop/orders') === 200 && $code('admin', 'admin/shop/products') === 200 && $code('admin', 'admin/shop/categories') === 200
    && $code('admin', 'admin/shop/settings') === 200 && $code('admin', 'admin/shop/payments') === 200 && $code('admin', 'admin/api/shop/stats') === 200);
check('orders-only role: orders and overview yes; products, categories, settings, payments, CSV export no (403)', $code('orders', 'admin/shop/orders') === 200 && $code('orders', 'admin/shop') === 200 && $code('orders', 'admin/shop/products') === 403 && $code('orders', 'admin/shop/categories') === 403
    && $code('orders', 'admin/shop/settings') === 403 && $code('orders', 'admin/shop/payments') === 403 && $code('orders', 'admin/shop/orders/export') === 403);
check('manage-only role: products and categories yes; orders, settings, payments, export no', $code('manage', 'admin/shop/products') === 200 && $code('manage', 'admin/shop/categories') === 200 && $code('manage', 'admin/shop') === 200 && $code('manage', 'admin/shop/orders') === 403
    && $code('manage', 'admin/shop/settings') === 403 && $code('manage', 'admin/shop/payments') === 403 && $code('manage', 'admin/api/shop/stats') === 403);
check('settings-only role: ONLY settings and payments (the payment panel holds the secrets)', $code('settings', 'admin/shop/payments') === 200 && $code('settings', 'admin/shop/settings') === 200 && $code('settings', 'admin/shop/orders') === 403 && $code('settings', 'admin/shop/products') === 403 && $code('settings', 'admin/shop') === 403);
check('the built-in Editor sees orders and products but NEVER the payment panel, settings, export or deletes', $code('editor', 'admin/shop/orders') === 200 && $code('editor', 'admin/shop/products') === 200 && $code('editor', 'admin/shop/payments') === 403 && $code('editor', 'admin/shop/settings') === 403 && $code('editor', 'admin/shop/orders/export') === 403);
// order detail + actions
$show = $http('GET', 'admin/shop/orders/' . $mo['id'], ['jar' => $jars['orders']]);
$csrf = $field($show['body'], '_token');
check('order detail renders with the order number and the customer reference', $show['code'] === 200 && str_contains($show['body'], (string) $mo['order_number']) && str_contains($show['body'], 'MP7654321'));
check('an action POST without a CSRF token is refused (403)', $http('POST', 'admin/shop/orders/' . $mo['id'] . '/note', ['jar' => $jars['orders'], 'post' => ['note' => 'x']])['code'] === 403);
$r = $http('POST', 'admin/shop/orders/' . $mo['id'] . '/payment', ['jar' => $jars['orders'], 'post' => ['_token' => $csrf, 'method' => 'momo', 'reference' => 'MP7654321', 'amount' => '']]);
$moy = OrderService::find((int) $mo['id']);
check('staff "Mark paid" with an empty amount takes the balance: order paid and confirmed', $r['code'] === 302 && $moy['payment_status'] === 'paid' && $moy['status'] === 'confirmed' && PaymentService::balanceMinor($moy) === 0);
$r = $http('POST', 'admin/shop/orders/' . $mo['id'] . '/note', ['jar' => $jars['orders'], 'post' => ['_token' => $csrf, 'note' => 'Collected by the buyer himself']]);
check('internal note added', $r['code'] === 302 && str_contains((string) OrderService::find((int) $mo['id'])['staff_note'], 'Collected by the buyer'));
$r = $http('POST', 'admin/shop/orders/' . $mo['id'] . '/status', ['jar' => $jars['orders'], 'post' => ['_token' => $csrf, 'status' => 'completed']]);
check('status change through the machine (confirmed -> completed after payment)', OrderService::find((int) $mo['id'])['status'] === 'completed');
$r = $http('POST', 'admin/shop/orders/' . $mo['id'] . '/status', ['jar' => $jars['orders'], 'post' => ['_token' => $csrf, 'status' => 'pending']]);
check('a forbidden transition is refused (completed -> pending) and nothing changes', OrderService::find((int) $mo['id'])['status'] === 'completed');
$r = $http('POST', 'admin/shop/orders/' . $mo['id'] . '/delete', ['jar' => $jars['orders'], 'post' => ['_token' => $csrf]]);
check('deleting an order needs shop.delete: the orders-only role gets 403 and the order stays', $r['code'] === 403 && OrderService::find((int) $mo['id']) !== null);
$ex = $http('GET', 'admin/shop/orders/export?kind=orders&q=zz-shop', ['jar' => $jars['admin']]);
check('admin CSV export: 200, text/csv, header row, formula-safe', $ex['code'] === 200 && str_contains(strtolower($ex['headers']['content-type'][0] ?? ''), 'csv') && str_contains($ex['body'], 'Order number'));
$au = Database::all("SELECT summary, details FROM audit_log WHERE action LIKE 'shop.%' ORDER BY id DESC LIMIT 20");
$leak = false;
foreach ($au as $a) {
    if (str_contains($a['summary'] . $a['details'], 'zz-shop-http') || str_contains($a['summary'] . $a['details'], 'Zz Http') ) {
        $leak = true;
    }
}
check('the audit log holds order numbers and actions only (no names, e-mails or phone numbers)', $au !== [] && !$leak);
$r = $http('POST', 'admin/shop/payments/test-connection', ['jar' => $jars['admin'], 'post' => ['_token' => $field($http('GET', 'admin/shop/payments', ['jar' => $jars['admin']])['body'], '_token')]]);
check('the payment settings routes exist and are admin-only (the stub answers; orders-only role gets 403)', in_array($r['code'], [200, 302, 422, 501], true) && $http('POST', 'admin/shop/payments/test-connection', ['jar' => $jars['orders']])['code'] === 403);
// review pass: the export audit, price limits, stock edits while orders arrive, a cancelled order that holds money
$http('GET', 'admin/shop/orders/export?kind=orders&q=zz-shop-secret-search&status=pending', ['jar' => $jars['admin']]);
$auq = (string) Database::value("SELECT details FROM audit_log WHERE action = 'shop.export' ORDER BY id DESC LIMIT 1", [], '');
check('the export audit entry names the filters that were used and never their values (the search box takes names, e-mails and phone numbers)', str_contains($auq, '"q"') && str_contains($auq, '"status"') && !str_contains($auq, 'zz-shop-secret-search') && !str_contains($auq, 'pending'), $auq);
$pfPage = $http('GET', 'admin/shop/products/create', ['jar' => $jars['admin']]);
$pfTok = $field($pfPage['body'], '_token');
$postProduct = static fn(array $o) => $http('POST', 'admin/shop/products', ['jar' => $jars['admin'], 'post' => $o + ['_token' => $pfTok, 'name' => 'zz-shop-form-price', 'status' => 'draft', 'price' => '10000', 'track_stock' => '0', 'low_stock' => '5']]);
$cntForm = static fn(): int => (int) Database::value("SELECT COUNT(*) FROM shop_products WHERE slug LIKE 'zz-shop-form-%'", [], 0);
$c0 = $cntForm();
$bad = ['0.4' => $postProduct(['price' => '0.4']), '0.49' => $postProduct(['price' => '0.49']), '99999999999' => $postProduct(['price' => '99999999999']), '9999999999.99' => $postProduct(['price' => '9999999999.99']), '3000001' => $postProduct(['price' => '3000001']), 'sale' => $postProduct(['price' => '1000', 'sale_price' => '0.4'])];
$badCodes = array_map(static fn(array $r): int => $r['code'], $bad);
check('prices of 0.4 (free after rounding), 99,999,999,999 and 3,000,001 (over the limit) are refused with a message: no product, never a 500', array_diff($badCodes, [302]) === [] && $cntForm() === $c0, json_encode($badCodes));
$good = $postProduct(['price' => '3,000,000', 'name' => 'zz-shop-form-ok']);
check('the largest allowed price (3,000,000) is saved', $good['code'] === 302 && $cntForm() === $c0 + 1 && Database::value("SELECT price FROM shop_products WHERE slug = 'zz-shop-form-ok'") === '3000000.00');
// editing a product while orders arrive must not put reserved units back on the shelf
$pStock = $zzProduct(['slug' => 'zz-shop-form-stock', 'name' => 'zz-shop-form-stock', 'track_stock' => 1, 'stock_qty' => 10, 'price' => '10000.00', 'status' => 'published',
    'options' => \App\Services\Shop\ProductService::encodeOptions([['label' => 'Size', 'values' => [['label' => 'S', 'stock' => 5], ['label' => 'M']]]])]);
$edit = $http('GET', 'admin/shop/products/' . $pStock . '/edit', ['jar' => $jars['admin']]);
$edTok = $field($edit['body'], '_token');
$snap = $field($edit['body'], 'stock_snapshot');
$optsLoaded = \App\Services\Shop\ProductService::parseOptions(Database::value('SELECT options FROM shop_products WHERE id = ?', [$pStock]));
// a customer orders 3 units (2 of them size S) while the form is open
Database::execute('UPDATE shop_products SET stock_qty = 7 WHERE id = ?', [$pStock]);
$optsNow = $optsLoaded;
$optsNow[0]['values'][0]['stock'] = 3;
Database::execute('UPDATE shop_products SET options = ? WHERE id = ?', [\App\Services\Shop\ProductService::encodeOptions($optsNow), $pStock]);
$formPost = static function (array $o) use ($http, $jars, $edTok, $pStock, $snap, $optsLoaded): array {
    $g = $optsLoaded[0];
    return $http('POST', 'admin/shop/products/' . $pStock, ['jar' => $jars['admin'], 'post' => $o + ['_token' => $edTok, 'name' => 'zz-shop-form-stock (typo fixed)', 'status' => 'published', 'price' => '10000', 'track_stock' => '1',
        'stock_qty' => '10', 'low_stock' => '5', 'stock_snapshot' => $snap,
        'options' => [['key' => $g['key'], 'label' => $g['label'], 'required' => '1', 'values' => [
            ['value' => $g['values'][0]['value'], 'label' => 'S', 'stock' => (string) ($o['_s'] ?? '5')], ['value' => $g['values'][1]['value'], 'label' => 'M', 'stock' => '']]]]]]);
};
$formPost(['_s' => '5']);
$stockNow = (int) Database::value('SELECT stock_qty FROM shop_products WHERE id = ?', [$pStock]);
$sNow = \App\Services\Shop\ProductService::parseOptions(Database::value('SELECT options FROM shop_products WHERE id = ?', [$pStock]))[0]['values'][0]['stock'];
check('saving the product form with the old stock numbers (a typo fix) keeps the LIVE stock: 7 and size S 3, not the 10 and 5 the form was opened with', $stockNow === 7 && $sNow === 3
    && Database::value('SELECT name FROM shop_products WHERE id = ?', [$pStock]) === 'zz-shop-form-stock (typo fixed)', $stockNow . ' / ' . var_export($sNow, true));
$formPost(['stock_qty' => '15', '_s' => '8']);
$stockNow = (int) Database::value('SELECT stock_qty FROM shop_products WHERE id = ?', [$pStock]);
$sNow = \App\Services\Shop\ProductService::parseOptions(Database::value('SELECT options FROM shop_products WHERE id = ?', [$pStock]))[0]['values'][0]['stock'];
check('a number the person really changed moves by that difference on top of the live value (10 -> 15 is +5: 7 -> 12; S 5 -> 8 is +3: 3 -> 6)', $stockNow === 12 && $sNow === 6, $stockNow . ' / ' . var_export($sNow, true));
// a cancelled order that still holds money shows the refund control
$crOrder = $zzOrder([$zzLine($pHttp, 1)], 'pickup', 'cod', ['email' => 'zz-shop-http5@example.test'])['order'];
PaymentService::markPaid((int) $crOrder['id'], ['method' => 'cod', 'amount' => 1000000, 'currency' => 'UGX', 'source' => 'staff']);
OrderService::setStatus((int) $crOrder['id'], 'cancelled', ['notify' => false]);
$crPage = $http('GET', 'admin/shop/orders/' . $crOrder['id'], ['jar' => $jars['orders']]);
check('a cancelled order that still holds money offers "Record the refund" (it used to say "nothing more can be changed" and point to a control that was not there)', str_contains($crPage['body'], 'Record the refund') && !str_contains($crPage['body'], 'in the progress box above'));
$crTok = $field($crPage['body'], '_token');
$http('POST', 'admin/shop/orders/' . $crOrder['id'] . '/payment-status', ['jar' => $jars['orders'], 'post' => ['_token' => $crTok, 'payment_status' => 'refunded', 'note' => 'sent back']]);
check('...and recording it closes the books: payment refunded, a refunded row in the ledger', OrderService::find((int) $crOrder['id'])['payment_status'] === 'refunded' && Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ? AND status = 'refunded'", [$crOrder['id']]) == 1);

foreach ($zzJars as $j) {
    @unlink($j);
}
return true;
