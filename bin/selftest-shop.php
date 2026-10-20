<?php
/**
 * SHOP module checks (docs/SHOP.md). Run alone:   /c/xampp/php/php.exe -d extension=gd bin/selftest-shop.php     (GD only adds the picture-pipeline checks)
 * or include it from bin/selftest.php (it shares check() and $fail when they exist).
 *
 * Part 1 (no web server needed): money maths (rounding, big totals, overflow), seed / schema / abilities / role upgrade contract, SecretStore (round trip,
 * tamper, key change, .env override, nothing readable in the database), product rules (options, selection fuzz, sale windows, stock), pricing (delivery fee,
 * free threshold), the cart (hostile ids / options / quantities), order creation (server-side prices, stock reservation, hold expiry, the "last units" race with
 * parallel PHP processes), the order status machine (every pair), the payment machine (partial, replay, duplicate, mismatch -> review, closed order,
 * refund), FlutterwaveClient + OnlinePayment against the local mock bin/dev/mock-flutterwave.php (starts and stops it), CSV formula safety, e-mail
 * placeholders / header injection, picture pipeline (when GD is loaded), bin/shop-maintenance.php.
 * Part 2 (needs the site at http://localhost/<folder>/): the real HTTP flow (no cookie on anonymous views, cookie only after the first item, CSRF + honeypot,
 * checkout, order page noindex / no-store, token handling, webhook authentication) and the admin abilities with throw-away users and roles. It skips itself
 * when the site does not answer.
 *
 * Everything it creates is removed again (products "zz-shop-*", orders of zz-shop-*@example.test, users, roles, mail files, uploaded files); the shop settings
 * it changes are put back exactly as they were. Real payment gateways are NEVER contacted: the only server it talks to is the local mock.
 */
declare(strict_types=1);

if (!function_exists('check')) {
    if (PHP_SAPI !== 'cli') {
        exit('CLI only.');
    }
    require dirname(__DIR__) . '/app/bootstrap.php';
    if (\App\Core\Config::get('app.env') !== 'local') {
        exit("Refusing to run outside APP_ENV=local.\n");
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
use App\Core\Config;
use App\Core\CsvExport;
use App\Core\Database;
use App\Core\Env;
use App\Core\Roles;
use App\Core\Settings;
use App\Models\ShopOrder;
use App\Services\Shop\CartService;
use App\Services\Shop\FlutterwaveClient;
use App\Services\Shop\Money;
use App\Services\Shop\OnlinePayment;
use App\Services\Shop\OrderService;
use App\Services\Shop\PaymentMethods;
use App\Services\Shop\PaymentService;
use App\Services\Shop\PricingService;
use App\Services\Shop\ProductImages;
use App\Services\Shop\ProductService;
use App\Services\Shop\SecretStore;
use App\Services\Shop\ShopExport;
use App\Services\Shop\ShopMailer;
use App\Services\Shop\ShopSettings;
use App\Services\Shop\ShopStats;

const ZZ_SHOP_EMAIL = 'zz-shop-a@example.test';

$zzRoot = dirname(__DIR__);
$zzSettingsTouched = [];
$zzSettingRows = [];                       // key => original row (or null)
$zzMailBefore = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
$zzUploads = [];

/** change a shop setting for the test and remember the original row so it can be put back exactly */
$zzSet = static function (string $key, string $value) use (&$zzSettingRows): void {
    if (!array_key_exists($key, $zzSettingRows)) {
        $zzSettingRows[$key] = Database::one('SELECT * FROM settings WHERE setting_key = ?', [$key]);
    }
    Settings::set($key, $value);
};
$zzRestoreSettings = static function () use (&$zzSettingRows): void {
    foreach ($zzSettingRows as $key => $row) {
        Database::execute('DELETE FROM settings WHERE setting_key = ?', [$key]);
        if ($row !== null) {
            Database::insert('settings', ['setting_key' => $row['setting_key'], 'setting_value' => $row['setting_value'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']]);
        }
    }
    $zzSettingRows = [];
    Settings::flush();
};

$zzCleanup = static function () use (&$zzUploads, $zzRoot): void {
    try {
        Database::execute("DELETE FROM shop_orders WHERE customer_email LIKE 'zz-shop-%@example.test'");
        Database::execute("DELETE FROM shop_events WHERE ref LIKE 'ZZT-%' OR ref LIKE 'zz-%' OR ref LIKE 'ZZ-%'");
        $imgs = Database::all("SELECT i.* FROM shop_product_images i JOIN shop_products p ON p.id = i.product_id WHERE p.slug LIKE 'zz-shop-%'");
        Database::execute("DELETE FROM shop_products WHERE slug LIKE 'zz-shop-%'");
        foreach ($imgs as $row) {
            ProductImages::releaseFiles($row);
        }
        Database::execute("DELETE FROM shop_categories WHERE slug LIKE 'zz-shop-%'");
        foreach ($zzUploads as $f) {
            @unlink($f);
        }
        Database::execute("DELETE FROM audit_log WHERE user_name LIKE '%zz-shop-%@example.test%' OR summary LIKE '%ZZT-%'");
        Database::execute("DELETE FROM login_attempts WHERE email LIKE 'zz-shop-%@example.test'");
        Database::execute("DELETE FROM users WHERE email LIKE 'zz-shop-%@example.test'");
        Database::execute("DELETE FROM roles WHERE slug LIKE 'zz-shop-%'");
        Roles::flush();
    } catch (\Throwable) {
    }
};
$zzCleanup();
$secretRows = [];
foreach (SecretStore::NAMES as $n) {
    $secretRows[$n] = Database::one('SELECT * FROM settings WHERE setting_key = ?', ['shop_secret_' . $n]);
}
$mockProc = null;
$mockPort = 0;
$zzDone = false;
/** put everything back (also runs when the script dies half way, so a crash never leaves test settings or test data behind) */
$zzFinish = static function () use (&$zzDone, $zzCleanup, &$secretRows, $zzRestoreSettings, &$mockProc, &$mockPort, $zzMailBefore): void {
    if ($zzDone) {
        return;
    }
    $zzDone = true;
    $zzCleanup();
    foreach (array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $zzMailBefore) as $f) {
        @unlink($f);
    }
    foreach ($secretRows as $n => $row) {
        Database::execute('DELETE FROM settings WHERE setting_key = ?', ['shop_secret_' . $n]);
        if ($row !== null) {
            Database::insert('settings', ['setting_key' => $row['setting_key'], 'setting_value' => $row['setting_value'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']]);
        }
    }
    $zzRestoreSettings();
    if (is_resource($mockProc)) {
        proc_terminate($mockProc);
        proc_close($mockProc);
    }
    @unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lumina-mock-flutterwave-' . $mockPort . '.json');
    OrderService::flushCounts();
    Settings::flush();
};
register_shutdown_function($zzFinish);

$zzProduct = static function (array $o = []): int {
    $row = $o + [
        'slug' => 'zz-shop-' . bin2hex(random_bytes(4)), 'name' => 'ZZ Test Product', 'price' => '10000.00', 'status' => 'published', 'track_stock' => 0, 'stock_qty' => null,
        'low_stock' => 2, 'allow_booking' => 0, 'is_sample' => 0,
    ];
    return Database::insert('shop_products', $row);
};
$zzLine = static fn(int $pid, int $qty = 1, array $sel = []): array => ['key' => substr(sha1($pid . json_encode($sel)), 0, 12), 'product_id' => $pid, 'selection' => $sel, 'qty' => $qty];
$zzCustomer = static fn(array $o = []): array => $o + ['name' => 'Zz Shop Tester', 'email' => ZZ_SHOP_EMAIL, 'phone' => '0700123456', 'address' => '', 'note' => '', 'consent' => true];
$zzOrder = static function (array $lines, string $delivery = 'pickup', string $method = 'cod', array $customer = []) use ($zzCustomer): array {
    return OrderService::create($zzCustomer($customer), $lines, $delivery, $method, ['notify' => false, 'ip_hash' => 'x']);
};
$zzStock = static fn(int $pid): int => (int) Database::value('SELECT stock_qty FROM shop_products WHERE id = ?', [$pid], -1);

// =====================================================================================================================================================
echo "Shop: money\n";
check('parse: plain, thousands separator, decimals, spaces', Money::parse('35000') === 3500000 && Money::parse('35,000') === 3500000 && Money::parse('35000.5') === 3500050 && Money::parse(' 12.34 ') === 1234);
check('parse: rounds half up at the third decimal', Money::parse('1.005') === 101 && Money::parse('1.004') === 100 && Money::parse('0.995') === 100);
check('parse: refuses garbage (letters, exponent, two separators, empty, array, bool, NaN)', Money::parse('abc') === null && Money::parse('1e3') === null && Money::parse('1.2.3') === null && Money::parse('') === null
    && Money::parse([]) === null && Money::parse(true) === null && Money::parse(NAN) === null && Money::parse('12,34') === null && Money::parse('35 000') === null);
check('parse: ints, floats, negatives', Money::parse(5) === 500 && Money::parse(2.5) === 250 && Money::parse('-3.20') === -320 && Money::parse(35000.0) === 3500000);
check('parse: amounts beyond what the DECIMAL(12,2) columns hold are refused', Money::parse('99999999999999') === null && Money::parse('9999999999.99') === 999999999999 && Money::parse('10000000000') === null && Money::parse('9999999999999') === null);
check('toDb / fromDb round trip', Money::toDb(3500000) === '35000.00' && Money::toDb(5) === '0.05' && Money::toDb(-320) === '-3.20' && Money::fromDb('35000.00') === 3500000 && Money::fromDb(null) === 0);
check('format: UGX has no decimals, grouped, code first', Money::format(3500000, 'UGX') === 'UGX 35,000' && Money::format(3500000, 'UGX', false) === '35,000' && Money::format(123456789000, 'UGX') === 'UGX 1,234,567,890');
check('format: two-decimal currencies keep cents', Money::format(1250, 'USD') === 'USD 12.50' && Money::format(5, 'USD') === 'USD 0.05' && Money::format(-1250, 'USD') === 'USD -12.50');
check('roundTo: zero-decimal currencies round half up to whole units, others untouched', Money::roundTo(3500049, 'UGX') === 3500000 && Money::roundTo(3500050, 'UGX') === 3500100 && Money::roundTo(1234, 'USD') === 1234 && Money::roundTo(-3500050, 'UGX') === -3500100);
check('mul / add: exact, big totals, overflow refused', Money::mul(8500000, 12) === 102000000 && Money::add(1, 2, 3) === 6 && Money::mul(0, 5) === 0);
$over = false;
try {
    Money::mul(Money::MAX, 2);
} catch (\OverflowException) {
    $over = true;
}
$over2 = false;
try {
    Money::add(Money::MAX, 1);
} catch (\OverflowException) {
    $over2 = true;
}
check('overflow throws instead of wrapping', $over && $over2);
check('no float drift: 0.1 + 0.2 in minor units is exact', Money::add(Money::parse('0.1'), Money::parse('0.2')) === Money::parse('0.3'));
check('gateway amount: int for UGX, float for USD; fromGateway reads int / float / string', Money::gateway(3500000, 'UGX') === 35000 && Money::gateway(1250, 'USD') === 12.5 && Money::fromGateway(35000) === 3500000 && Money::fromGateway(35000.0) === 3500000
    && Money::fromGateway('35000.00') === 3500000 && Money::fromGateway(12.5) === 1250 && Money::fromGateway(null) === null && Money::fromGateway(['x']) === null);

// =====================================================================================================================================================
echo "Shop: seed, schema, abilities, roles, menu\n";
foreach (['shop_categories', 'shop_products', 'shop_product_images', 'shop_orders', 'shop_order_items', 'shop_payments', 'shop_order_log', 'shop_events'] as $t) {
    check("table {$t} exists", Database::tableExists($t));
}
$seedSql = (string) file_get_contents($zzRoot . '/database/seed.sql');
$missing = [];
foreach (ShopSettings::DEFS as $k => $d) {
    if (!str_contains($seedSql, "('{$k}', '" . str_replace("'", "''", (string) $d[0]) . "')")) {
        $missing[] = $k;
    }
}
check('database/seed.sql seeds every ShopSettings key with its default', $missing === [], implode(',', $missing));
check('no secret is seeded or defined as a setting default', !str_contains($seedSql, 'shop_secret_') && !isset(ShopSettings::DEFS['shop_secret_flw_secret_key']) && !str_contains($seedSql, 'FLWSECK'));
check('settings_schema does not list the shop keys (the generic settings screen never shows them)', Settings::definition('shop_open') === null && Settings::definition('shop_flw_public_key') === null);
$cfgAb = (array) Config::get('abilities', []);
$cat = (array) require $zzRoot . '/app/config/ability_catalog.php';
$catItems = (array) ($cat['shop']['items'] ?? []);
foreach (['shop.manage', 'shop.orders', 'shop.settings', 'shop.export', 'shop.delete'] as $ab) {
    check("ability {$ab} is defined and in the catalogue with a plain-language label", isset($cfgAb[$ab]) && isset($catItems[$ab]) && is_string($catItems[$ab][0]) && $catItems[$ab][0] !== '');
}
check('shop.settings is flagged as holding payment secrets and is admin-only by default', !empty($catItems['shop.settings']['secrets']) && $cfgAb['shop.settings'] === ['admin']);
Roles::flush();
check('editor holds shop.manage and shop.orders but never settings / export / delete', Roles::can('editor', 'shop.manage') && Roles::can('editor', 'shop.orders') && !Roles::can('editor', 'shop.settings') && !Roles::can('editor', 'shop.export') && !Roles::can('editor', 'shop.delete'));
check('administrator holds every shop ability', Roles::can('admin', 'shop.settings') && Roles::can('admin', 'shop.export') && Roles::can('admin', 'shop.delete'));
check('"shop" is a reserved page slug', \App\Core\Slug::isReserved('shop'));
$page = Database::one("SELECT * FROM pages WHERE slug = 'shop'");
check('pages row: Shop is a header + footer child of Donate', $page !== null && $page['parent_slug'] === 'donate' && (int) $page['show_in_nav'] === 1 && (int) $page['show_in_footer'] === 1);
check('the one-time migrations ran once (markers present)', (int) Database::value("SELECT COUNT(*) FROM settings WHERE setting_key IN ('migration_shop_nav_v1','migration_shop_editor_role_v1','migration_shop_samples_v1')", [], 0) === 3);
$sam = Database::all("SELECT * FROM shop_products WHERE is_sample = 1 ORDER BY id");
check('six sample products are seeded, flagged as samples, UGX placeholder prices', count($sam) >= 6 && count(array_filter($sam, static fn(array $r): bool => (float) $r['price'] > 0 && (int) $r['is_sample'] === 1)) >= 6);
check('five categories are seeded', (int) Database::value('SELECT COUNT(*) FROM shop_categories WHERE slug IN (?,?,?,?,?)', ['jumpers-hoodies', 'caps', 't-shirts', 'stickers', 'other'], 0) === 5);
$imgBad = [];
foreach (Database::all('SELECT i.* FROM shop_product_images i JOIN shop_products p ON p.id = i.product_id WHERE p.is_sample = 1') as $i) {
    foreach (['path', 'path_webp', 'path_2x', 'path_webp_2x'] as $c) {
        $f = $zzRoot . '/' . (string) $i[$c];
        if (!is_file($f) || filesize($f) > 150 * 1024) {
            $imgBad[] = $i[$c];
        }
    }
}
check('every sample picture file exists and is under 150 KB (jpg, webp, 2x)', $imgBad === [] && (int) Database::value('SELECT COUNT(*) FROM shop_product_images i JOIN shop_products p ON p.id = i.product_id WHERE p.is_sample = 1', [], 0) >= 12, implode(',', $imgBad));
check('the shop starts CLOSED and the gateway is off by default', !ShopSettings::bool('shop_open') || Database::value("SELECT updated_at <> created_at FROM settings WHERE setting_key = 'shop_open'") == 1);
check('registry page "shop" has the wording the code reads', content('shop', 'hero.title') !== '' && content('shop', 'status.pending') === 'Received' && content('shop', 'email.order_subject') !== '' && content('shop', 'payment.momo_instructions') !== '');

// settings for the rest of the run
$zzSet('shop_open', '1');
$zzSet('shop_order_prefix', 'ZZT');
$zzSet('shop_currency', 'UGX');
$zzSet('shop_max_qty', '10');
$zzSet('shop_pickup_enabled', '1');
$zzSet('shop_delivery_enabled', '1');
$zzSet('shop_delivery_fee', '5000');
$zzSet('shop_free_delivery_over', '100000');
$zzSet('shop_pay_book_enabled', '1');
$zzSet('shop_pay_cod_enabled', '1');
$zzSet('shop_pay_momo_enabled', '1');
$zzSet('shop_momo_mtn_number', '0777000111');
$zzSet('shop_momo_mtn_name', 'Zz Test');
$zzSet('shop_pay_bank_enabled', '1');
$zzSet('shop_bank_account_number', '0012345');
$zzSet('shop_bank_name', 'Zz Bank');
$zzSet('shop_notify_emails', 'zz-shop-staff@example.test');
$zzSet('shop_pay_flutterwave_enabled', '1');
$zzSet('shop_flw_options', 'card,mobilemoneyuganda');
$zzSet('shop_restock_on_cancel', '1');
$zzSet('shop_restock_on_refund', '0');
$zzSet('shop_auto_confirm_paid', '1');
$zzSet('shop_hold_days', '7');

// =====================================================================================================================================================
echo "Shop: SecretStore\n";
putenv('FLW_SECRET_KEY');
putenv('FLW_SECRET_HASH');
$plain = 'FLWSECK_TEST-' . bin2hex(random_bytes(16)) . '-X';
$enc = SecretStore::encrypt($plain);
check('encrypt: enc:v1: prefix, plaintext not visible', $enc !== null && str_starts_with($enc, 'enc:v1:') && !str_contains($enc, 'FLWSECK') && !str_contains($enc, $plain));
check('round trip', $enc !== null && SecretStore::decrypt($enc) === $plain);
check('a fresh IV every time (two encryptions differ)', SecretStore::encrypt($plain) !== $enc);
$tampered = substr($enc, 0, -3) . (substr($enc, -3) === 'AAA' ? 'BBB' : 'AAA');
check('tampered ciphertext reads as null (never garbage)', SecretStore::decrypt($tampered) === null);
check('truncated / foreign / empty values read as null', SecretStore::decrypt('enc:v1:abc') === null && SecretStore::decrypt('plain text') === null && SecretStore::decrypt('') === null && SecretStore::decrypt('enc:v1:') === null);
check('a different application key cannot read it (key change)', SecretStore::decrypt($enc, 'another-app-key') === null && SecretStore::decrypt((string) SecretStore::encrypt($plain, 'k1'), 'k1') === $plain);
SecretStore::put('flw_secret_key', $plain);
$raw = (string) Database::value("SELECT setting_value FROM settings WHERE setting_key = 'shop_secret_flw_secret_key'");
check('stored encrypted in the settings table (no plaintext in the database)', str_starts_with($raw, 'enc:v1:') && !str_contains($raw, $plain));
check('get() returns the plaintext; status() never does', SecretStore::get('flw_secret_key') === $plain && json_encode(SecretStore::status('flw_secret_key')) === '{"set":true,"source":"database","readable":true}' && !str_contains(SecretStore::describe('flw_secret_key'), $plain));
Database::execute("UPDATE settings SET setting_value = ? WHERE setting_key = 'shop_secret_flw_secret_key'", [$tampered]);
Settings::flush();
check('a damaged stored value is reported as unreadable and get() is null', SecretStore::get('flw_secret_key') === null && SecretStore::status('flw_secret_key')['readable'] === false);
putenv('FLW_SECRET_KEY=FLWSECK_TEST-from-env');
check('.env wins over the database and is shown as "set in .env"', SecretStore::get('flw_secret_key') === 'FLWSECK_TEST-from-env' && SecretStore::status('flw_secret_key')['source'] === 'env' && str_contains(SecretStore::describe('flw_secret_key'), '.env'));
putenv('FLW_SECRET_KEY');
SecretStore::put('flw_secret_key', '');
check('put("") forgets the secret', SecretStore::get('flw_secret_key') === null && SecretStore::status('flw_secret_key')['set'] === false);
$bad = false;
try {
    SecretStore::get('not_a_secret');
} catch (\InvalidArgumentException) {
    $bad = true;
}
check('unknown secret names are refused', $bad);
check('constant-time compare helper', SecretStore::equals('abc', 'abc') && !SecretStore::equals('abc', 'abd') && !SecretStore::equals('', '') && !SecretStore::equals('abc', ''));
check('key mode from the documented prefixes', FlutterwaveClient::keyMode('FLWSECK_TEST-x') === 'test' && FlutterwaveClient::keyMode('FLWSECK-x') === 'live' && FlutterwaveClient::keyMode('x') === 'unknown');

// =====================================================================================================================================================
echo "Shop: products, options, prices, stock\n";
$groups = ProductService::normaliseOptions([
    ['label' => '<b>Size</b>', 'values' => 'S, M, L, M, , XL'],
    ['label' => 'Colour', 'required' => false, 'values' => [['label' => 'Navy', 'stock' => '3', 'swatch' => '#2C3950'], ['label' => 'Gold', 'stock' => -4, 'swatch' => 'javascript:x'], 'Red']],
    ['label' => '', 'values' => 'a'], ['label' => 'Empty', 'values' => []], 'junk', ['label' => 'Size', 'values' => 'one'],
    ['label' => 'A', 'values' => 'x'], ['label' => 'B', 'values' => 'x'], ['label' => 'C', 'values' => 'x'],
]);
check('options: tags removed, duplicates dropped, keys and values made unique, at most 4 groups', count($groups) === 4 && $groups[0]['label'] === 'Size' && array_column($groups[0]['values'], 'value') === ['s', 'm', 'l', 'xl']
    && $groups[3]['key'] !== $groups[0]['key'] && count(array_unique(array_column($groups, 'key'))) === 4);
check('options: negative stock becomes 0, bad swatch dropped, good swatch kept', $groups[1]['values'][0]['stock'] === 3 && $groups[1]['values'][1]['stock'] === 0 && $groups[1]['values'][1]['swatch'] === null && $groups[1]['values'][0]['swatch'] === '#2c3950' && $groups[1]['required'] === false);
check('options: idempotent (normalising twice changes nothing) and JSON round trip', ProductService::normaliseOptions($groups) === $groups && ProductService::parseOptions(ProductService::encodeOptions($groups)) === $groups && ProductService::encodeOptions([]) === null);
check('options: garbage input never throws', ProductService::parseOptions('{not json') === [] && ProductService::parseOptions(null) === [] && ProductService::parseOptions('null') === [] && ProductService::parseOptions(42) === []);
$many = ProductService::normaliseOptions([['label' => 'X', 'values' => implode(',', range(1, 80))]]);
check('options: at most 30 values per group', count($many[0]['values']) === 30);

$pOpt = $zzProduct(['track_stock' => 1, 'stock_qty' => 20, 'options' => ProductService::encodeOptions([['label' => 'Size', 'values' => [['label' => 'S', 'stock' => 2], ['label' => 'M'], ['label' => 'L', 'stock' => 0]]], ['label' => 'Colour', 'required' => false, 'values' => 'Navy, Gold']])]);
$row = Database::one('SELECT * FROM shop_products WHERE id = ?', [$pOpt]);
$v = ProductService::validateSelection($row, ['size' => 'm']);
check('selection: valid choice -> text "Size: M", optional group may be empty', $v['ok'] && $v['text'] === 'Size: M' && $v['selection'] === ['size' => 'm']);
check('selection: missing required, invalid value, sold-out value, unknown key', !ProductService::validateSelection($row, [])['ok'] && ProductService::validateSelection($row, [])['errors'] === ['size' => 'required']
    && ProductService::validateSelection($row, ['size' => 'XXL'])['errors'] === ['size' => 'invalid'] && ProductService::validateSelection($row, ['size' => 'l'])['errors'] === ['size' => 'out_of_stock']
    && isset(ProductService::validateSelection($row, ['size' => 'm', 'hack' => '1'])['errors']['_']));
$hostile = [null, 5, 'x', [], [[]], ['size' => []], ['size' => ['m']], ['size' => 'm', 'colour' => ['gold']], ['size' => str_repeat('a', 5000)], [0 => 'm'], ['size' => "m\0"], ['__proto__' => 'x', 'size' => 'm'], ['size' => 'M']];
$ok = true;
foreach ($hostile as $h) {
    try {
        $r = ProductService::validateSelection($row, $h);
        if ($r['ok'] && $r['selection'] !== ['size' => 'm']) {
            $ok = false;
        }
    } catch (\Throwable) {
        $ok = false;
    }
}
check('selection fuzz: hostile shapes never throw and never pass as something else', $ok);
check('max orderable: product stock and option stock cap each other', ProductService::maxOrderable($row, ['size' => 's']) === 2 && ProductService::maxOrderable($row, ['size' => 'm']) === 20 && ProductService::maxOrderable($row, []) === 20
    && ProductService::maxOrderable(['track_stock' => 0], []) === null);

$sale = ['price' => '10000.00', 'sale_price' => '7500.00', 'sale_starts' => '2026-06-01 00:00:00', 'sale_ends' => '2026-06-30 23:59:59'];
$at = static fn(string $d): \DateTimeImmutable => new \DateTimeImmutable($d);
check('sale window: before / inside / after (boundaries included)', !ProductService::priceInfo($sale, $at('2026-05-31 23:59:59'))['on_sale'] && ProductService::priceInfo($sale, $at('2026-06-01 00:00:00'))['unit'] === 750000
    && ProductService::priceInfo($sale, $at('2026-06-30 23:59:59'))['on_sale'] && !ProductService::priceInfo($sale, $at('2026-07-01 00:00:00'))['on_sale']);
check('sale: open ended, 25% off text, not above the regular price, 0 or equal price ignored', ProductService::priceInfo(['price' => '100.00', 'sale_price' => '75.00'], $at('2030-01-01'))['percent_off'] === 25
    && !ProductService::priceInfo(['price' => '100.00', 'sale_price' => '120.00'])['on_sale'] && !ProductService::priceInfo(['price' => '100.00', 'sale_price' => '0.00'])['on_sale'] && !ProductService::priceInfo(['price' => '100.00', 'sale_price' => '100.00'])['on_sale']
    && !ProductService::priceInfo(['price' => '100.00', 'sale_price' => null])['on_sale']);
check('stock states: unlimited / in stock / low / sold out (also when every size of a required group is gone)', ProductService::stockInfo(['track_stock' => 0])['state'] === 'unlimited'
    && ProductService::stockInfo(['track_stock' => 1, 'stock_qty' => 9, 'low_stock' => 2])['state'] === 'in_stock' && ProductService::stockInfo(['track_stock' => 1, 'stock_qty' => 2, 'low_stock' => 2])['state'] === 'low'
    && ProductService::stockInfo(['track_stock' => 1, 'stock_qty' => 0, 'low_stock' => 2])['state'] === 'out'
    && ProductService::stockInfo(['track_stock' => 1, 'stock_qty' => 9, 'low_stock' => 2, 'options' => ProductService::encodeOptions([['label' => 'Size', 'values' => [['label' => 'S', 'stock' => 0], ['label' => 'M', 'stock' => 0]]]])])['state'] === 'out');

$catId = Database::insert('shop_categories', ['slug' => 'zz-shop-cat', 'name' => 'ZZ Cat', 'is_active' => 1]);
$pDraft = $zzProduct(['status' => 'draft']);
$pArch = $zzProduct(['status' => 'archived']);
$pCat = $zzProduct(['category_id' => $catId, 'price' => '1000.00', 'name' => 'ZZ Cheap']);
$pCat2 = $zzProduct(['category_id' => $catId, 'price' => '9000.00', 'name' => 'ZZ Dear', 'sale_price' => '500.00']);
$vis = static fn(int $id): bool => ProductService::find($id, true) !== null;
check('visibility: published shows, draft and archived never do', $vis($pCat) && !$vis($pDraft) && !$vis($pArch) && ProductService::find($pDraft, false) !== null);
Database::execute('UPDATE shop_categories SET is_active = 0 WHERE id = ?', [$catId]);
check('visibility: a product of an inactive category is hidden', !$vis($pCat));
Database::execute('UPDATE shop_categories SET is_active = 1 WHERE id = ?', [$catId]);
$cs = ProductService::catalogue(['category' => 'zz-shop-cat', 'sort' => 'price_asc']);
check('catalogue: category filter + price sort uses the SALE price (500 < 1000)', array_column($cs['items'], 'id') === [$pCat2, $pCat] && $cs['total'] === 2);
$cs = ProductService::catalogue(['category' => 'zz-shop-cat', 'sort' => 'price_desc']);
check('catalogue: price descending', array_column($cs['items'], 'id') === [$pCat, $pCat2]);
check('catalogue: search is a literal match (wildcards are escaped), unknown sort falls back', ProductService::catalogue(['q' => '%'])['total'] === 0 && ProductService::catalogue(['q' => 'ZZ Cheap'])['total'] === 1 && ProductService::catalogue(['sort' => 'drop table'])['sort'] === 'featured');
check('catalogue: page numbers are clamped', ProductService::catalogue([], 999)['page'] >= 1 && ProductService::catalogue([], -5)['page'] === 1);
check('hydrate adds prices, badges, urls (sale badge, sold-out badge)', in_array('sale', ProductService::find($pCat2)['badges'], true) && ProductService::find($pCat2)['unit_text'] === 'UGX 500' && ProductService::find($pCat2)['price_text'] === 'UGX 9,000');
check('reserved product slugs never collide with shop URLs', ProductService::uniqueSlug('Cart') !== 'cart' && ProductService::uniqueSlug('Checkout') !== 'checkout' && ProductService::uniqueSlug('') !== '');

// =====================================================================================================================================================
echo "Shop: pricing\n";
$lines = [['unit_minor' => 2500000, 'qty' => 2], ['unit_minor' => 1000000, 'qty' => 1]];
$q = PricingService::quote($lines, 'pickup');
check('pickup: subtotal 60,000, no fee, total 60,000', $q['subtotal'] === 6000000 && $q['delivery_fee'] === 0 && $q['total'] === 6000000 && $q['units'] === 3);
$q = PricingService::quote($lines, 'delivery');
check('delivery below the free threshold: fee 5,000 added, gap to free delivery shown', $q['delivery_fee'] === 500000 && $q['total'] === 6500000 && $q['free_gap'] === 4000000 && !$q['free_delivery']);
$q = PricingService::quote([['unit_minor' => 5000000, 'qty' => 2]], 'delivery');
check('delivery at exactly the threshold is free', $q['free_delivery'] && $q['delivery_fee'] === 0 && $q['total'] === 10000000);
$zzSet('shop_free_delivery_over', '0');
check('threshold 0 = never free', PricingService::quote([['unit_minor' => 99900000, 'qty' => 1]], 'delivery')['delivery_fee'] === 500000);
$zzSet('shop_free_delivery_over', '100000');
$zzSet('shop_delivery_enabled', '0');
check('delivery switched off: requests for delivery fall back to pickup', PricingService::quote($lines, 'delivery')['delivery_method'] === 'pickup');
$zzSet('shop_delivery_enabled', '1');
check('zero quote for an empty cart; big totals are exact', PricingService::quote([], 'pickup')['total'] === 0 && PricingService::quote([['unit_minor' => 9999900, 'qty' => 99]], 'pickup')['total'] === 989990100);
$o = false;
try {
    PricingService::quote([['unit_minor' => Money::MAX, 'qty' => 99]], 'pickup');
} catch (\OverflowException) {
    $o = true;
}
check('an absurd total throws instead of wrapping', $o);
check('negative / junk line values count as zero', PricingService::quote([['unit_minor' => -500, 'qty' => 3], ['qty' => 'x'], []], 'pickup')['total'] === 0);

// =====================================================================================================================================================
echo "Shop: cart\n";
$_SESSION = [];
$pCart = $zzProduct(['track_stock' => 1, 'stock_qty' => 5, 'allow_booking' => 1, 'options' => ProductService::encodeOptions([['label' => 'Size', 'values' => 'S, M']])]);
check('an empty cart is empty and has no units', CartService::isEmpty() && CartService::units() === 0);
$r = CartService::add($pCart, ['size' => 'm'], 2);
check('add: ok, line key, quantity', $r['ok'] && $r['qty'] === 2 && CartService::units() === 2);
$r = CartService::add($pCart, ['size' => 'm'], 2);
check('adding the same choice merges the line', $r['ok'] && $r['qty'] === 4 && count(CartService::raw()['lines']) === 1);
$r = CartService::add($pCart, ['size' => 'm'], 9);
check('quantity is capped at the stock (5) and the cap is reported', $r['ok'] && $r['qty'] === 5 && $r['clamped']);
check('missing option -> options error', CartService::add($pCart, [], 1)['error'] === 'options' && CartService::add($pCart, ['size' => 'zz'], 1)['error'] === 'options');
check('hostile product ids', CartService::add(0, [], 1)['error'] === 'not_found' && CartService::add(-5, [], 1)['error'] === 'not_found' && CartService::add(PHP_INT_MAX, [], 1)['error'] === 'not_found' && CartService::add($pDraft, [], 1)['error'] === 'not_found');
$hostileQty = [-3, 0, PHP_INT_MAX, PHP_INT_MIN];
$ok = true;
foreach ($hostileQty as $hq) {
    $res = CartService::add($pCat, [], $hq);
    if ($res['ok'] && ($res['qty'] < 1 || $res['qty'] > 10)) {
        $ok = false;
    }
}
check('hostile quantities are clamped into 1..max, never negative, never huge', $ok);
CartService::clear();
CartService::clear();
check('book mode needs a product that allows booking', CartService::add($pCat, [], 1, 'book')['error'] === 'not_bookable' && CartService::add($pCart, ['size' => 's'], 1, 'book')['ok'] && CartService::prefer() === 'book');
CartService::add($pCart, ['size' => 'm'], 3);
$lines = CartService::lines();
check('lines() resolve prices from the database (not from the session)', $lines !== [] && $lines[0]['unit_minor'] === 1000000 && !str_contains(json_encode($_SESSION[CartService::SESSION_KEY]), 'price'));
Database::execute('UPDATE shop_products SET price = ? WHERE id = ?', ['12000.00', $pCart]);
check('a price change shows up in the cart at once', CartService::lines()[0]['unit_minor'] === 1200000);
Database::execute('UPDATE shop_products SET stock_qty = 2 WHERE id = ?', [$pCart]);
$probs = array_column(CartService::lines(), 'problem');
check('stock dropped below the quantity: line reduced, still purchasable', in_array('reduced', $probs, true));
Database::execute('UPDATE shop_products SET stock_qty = 0 WHERE id = ?', [$pCart]);
check('sold out: the line is out_of_stock and not part of the quote', in_array('out_of_stock', array_column(CartService::lines(), 'problem'), true) && CartService::summary()['quote']['total'] === 0);
Database::execute("UPDATE shop_products SET status = 'draft', stock_qty = 5 WHERE id = ?", [$pCart]);
check('unpublished product: line is "removed" and left out', in_array('removed', array_column(CartService::lines(), 'problem'), true));
Database::execute("UPDATE shop_products SET status = 'published' WHERE id = ?", [$pCart]);
check('setQty clamps, 0 removes, unknown line is not_found; remove works', CartService::setQty('nope', 3)['error'] === 'not_found');
CartService::clear();
check('clear empties the cart', CartService::isEmpty());
CartService::add($pCat, [], 3);
$key = array_key_first(CartService::raw()['lines']);
check('setQty / remove', CartService::setQty($key, 99)['qty'] === 10 && CartService::setQty($key, 0)['qty'] === 0 && CartService::isEmpty());
$zzSet('shop_open', '0');
check('closed shop refuses add', CartService::add($pCat, [], 1)['error'] === 'closed');
$zzSet('shop_open', '1');
CartService::add($pCat, [], 1);
CartService::setDelivery('delivery');
check('delivery method is kept, junk methods are ignored', CartService::delivery() === 'delivery' && (function () { CartService::setDelivery('teleport'); return CartService::delivery() === 'delivery'; })());
$_SESSION['shop_cart'] = ['lines' => ['x' => 'junk', 'abc' => ['p' => 'a', 'q' => -4, 'o' => 'nope'], 5 => ['p' => 1, 'q' => 1]], 'delivery' => ['hack'], 'prefer' => 'x'];
check('a damaged session cart is repaired, not trusted or fatal', is_array(CartService::raw()) && CartService::delivery() === 'pickup' && CartService::lines() !== null);
$_SESSION = [];

// =====================================================================================================================================================
echo "Shop: orders (creation, prices, stock, numbers, tokens)\n";
$pA = $zzProduct(['track_stock' => 1, 'stock_qty' => 10, 'price' => '20000.00', 'name' => 'ZZ Order A', 'options' => ProductService::encodeOptions([['label' => 'Size', 'values' => [['label' => 'S', 'stock' => 3], ['label' => 'M', 'stock' => 5]]]])]);
$pB = $zzProduct(['price' => '15000.00', 'name' => 'ZZ Order B', 'allow_booking' => 1]);
$lineA = $zzLine($pA, 2, ['size' => 's']) + [];
$lineA['unit_minor'] = 1;          // a tampered price from the browser must be ignored
$lineA['line_minor'] = 1;
$res = $zzOrder([$lineA, $zzLine($pB, 1)], 'delivery', 'cod', ['address' => 'Plot 5, Gulu']);
$o = $res['order'] ?? [];
check('create: ok', !empty($res['ok']) && $o !== [], json_encode($res));
check('server prices: subtotal 55,000 (2 x 20,000 + 15,000), delivery 5,000, total 60,000 - the tampered browser price was ignored', ($o['subtotal'] ?? '') === '55000.00' && ($o['delivery_fee'] ?? '') === '5000.00' && ($o['total'] ?? '') === '60000.00');
check('items are snapshots (name, option text, unit price, qty)', count($res['items']) === 2 && $res['items'][0]['unit_price'] === '20000.00' && $res['items'][0]['options_text'] === 'Size: S' && (int) $res['items'][0]['qty'] === 2);
check('stock reserved (10 -> 8) and the option stock too (S: 3 -> 1)', $zzStock($pA) === 8 && ProductService::parseOptions(Database::value('SELECT options FROM shop_products WHERE id = ?', [$pA]))[0]['values'][0]['stock'] === 1);
check('order number format PREFIX-YYMMDD-XXXX (no look-alike characters)', preg_match('/^ZZT-\d{6}-[ABCDEFGHJKMNPQRSTUVWXYZ23456789]{4}$/', (string) $o['order_number']) === 1);
check('token: 40 hex = 160 bits, unique across orders', preg_match('/^[a-f0-9]{40}$/', (string) $o['token']) === 1);
$o2 = $zzOrder([$zzLine($pB, 1)])['order'];
check('two orders never share a token or a number', $o2['token'] !== $o['token'] && $o2['order_number'] !== $o['order_number']);
check('new order: pending + unpaid, hold until about 7 days, consent time stored, ip only as hash', $o['status'] === 'pending' && $o['payment_status'] === 'unpaid' && abs(strtotime((string) $o['hold_until']) - strtotime('+7 days')) < 120 && $o['consent_at'] !== null && $o['ip_hash'] === 'x');
check('findByToken: exact token only; malformed / unknown / SQL-ish tokens are null', OrderService::findByToken($o['token'])['id'] === $o['id'] && OrderService::findByToken(strtoupper($o['token'])) === null && OrderService::findByToken("' OR 1=1 --") === null
    && OrderService::findByToken(str_repeat('0', 40)) === null && OrderService::findByToken('') === null);
$short = $zzOrder([$zzLine($pA, 9, ['size' => 'm'])]);
check('not enough stock (option M has 5): refused, nothing reserved', empty($short['ok']) && $short['error'] === 'stock' && $zzStock($pA) === 8);
check('unknown product / bad option / zero qty are refused with the right code', ($zzOrder([$zzLine(999999999, 1)])['error'] ?? '') === 'unavailable' && ($zzOrder([$zzLine($pA, 1, [])])['error'] ?? '') === 'options' && ($zzOrder([$zzLine($pB, 0)])['error'] ?? '') === 'qty'
    && ($zzOrder([$zzLine($pB, 999)])['error'] ?? '') === 'qty' && ($zzOrder([])['error'] ?? '') === 'empty');
check('a draft product cannot be ordered', ($zzOrder([$zzLine($pDraft, 1)])['error'] ?? '') === 'unavailable');
$bad = $zzOrder([$zzLine($pB, 1)], 'pickup', 'cod', ['email' => "a@b.test\r\nBcc: x@y.test"]);
check('customer details are validated (header-injection e-mail refused)', empty($bad['ok']) && $bad['error'] === 'customer' && isset($bad['fields']['email']));
$bad = $zzOrder([$zzLine($pB, 1)], 'delivery', 'cod', ['address' => '']);
check('delivery needs an address; consent is required', ($bad['fields']['address'] ?? '') === 'required' && ($zzOrder([$zzLine($pB, 1)], 'pickup', 'cod', ['consent' => false])['fields']['consent'] ?? '') === 'consent');
check('phone numbers: Uganda formats normalised, junk refused', OrderService::normalisePhone('0700 123 456') === '+256700123456' && OrderService::normalisePhone('+256 700-123456') === '+256700123456' && OrderService::normalisePhone('256700123456') === '+256700123456'
    && OrderService::normalisePhone('(0414) 123456') === '+256414123456' && OrderService::normalisePhone('0100 123456') === null && OrderService::normalisePhone('+44 20 7946 0958') === '+442079460958' && OrderService::normalisePhone('abc') === null && OrderService::normalisePhone('12345') === null && OrderService::normalisePhone('00256700123456') === '+256700123456');
check('payment method must be offered: unknown / disabled / book for a non-bookable cart', ($zzOrder([$zzLine($pB, 1)], 'pickup', 'paypal')['error'] ?? '') === 'payment' && ($zzOrder([$zzLine($pA, 1, ['size' => 's'])], 'pickup', 'book')['error'] ?? '') === 'payment'
    && !empty($zzOrder([$zzLine($pB, 1)], 'pickup', 'book')['ok']));
$zzSet('shop_pay_cod_enabled', '0');
check('a disabled method cannot be used even if the browser posts it', ($zzOrder([$zzLine($pB, 1)], 'pickup', 'cod')['error'] ?? '') === 'payment');
$zzSet('shop_pay_cod_enabled', '1');
$zzSet('shop_open', '0');
check('closed shop refuses orders', ($zzOrder([$zzLine($pB, 1)])['error'] ?? '') === 'closed');
$zzSet('shop_open', '1');
$pSale = $zzProduct(['price' => '10000.00', 'sale_price' => '8000.00', 'name' => 'ZZ Sale']);
$so = $zzOrder([$zzLine($pSale, 2)]);
check('an order uses the sale price in force now', ($so['order']['total'] ?? '') === '16000.00');
Database::execute('UPDATE shop_products SET price = ? WHERE id = ?', ['99000.00', $pSale]);
check('history is a snapshot: a later price change does not alter the order', Database::value('SELECT unit_price FROM shop_order_items WHERE order_id = ?', [$so['order']['id']]) === '8000.00');
$pFloat = $zzProduct(['price' => '33333.33', 'name' => 'ZZ Odd']);
check('prices with cents are rounded to whole UGX per unit (UGX has no cents)', ($zzOrder([$zzLine($pFloat, 3)])['order']['total'] ?? '') === '99999.00');

// ---- status machine
echo "Shop: order status machine\n";
$allowed = 0;
$wrong = [];
foreach (ShopOrder::STATUSES as $from) {
    foreach (ShopOrder::STATUSES as $to) {
        $exp = in_array($to, OrderService::FLOW[$from], true);
        if (OrderService::canTransition($from, $to) !== $exp) {
            $wrong[] = "{$from}->{$to}";
        }
        $allowed += $exp ? 1 : 0;
    }
}
check('every status pair is decided by the table (none left implicit)', $wrong === [] && $allowed > 15);
check('final states have no way out', OrderService::allowedNext('cancelled') === [] && OrderService::allowedNext('refunded') === [] && !OrderService::canTransition('completed', 'pending') && !OrderService::canTransition('pending', 'completed') && !OrderService::canTransition('pending', 'shipped'));
$oc = $zzOrder([$zzLine($pA, 3, ['size' => 'm'])])['order'];
$stockAfter = $zzStock($pA);
$r = OrderService::setStatus((int) $oc['id'], 'completed');
check('completed is refused while unpaid (and from pending at all)', !$r['ok'] && $r['error'] === 'transition');
$r = OrderService::setStatus((int) $oc['id'], 'ready', ['notify' => false]);
check('pending -> ready is allowed', $r['ok'] && $r['order']['status'] === 'ready');
$r = OrderService::setStatus((int) $oc['id'], 'completed', ['notify' => false]);
check('ready -> completed needs payment first', !$r['ok'] && $r['error'] === 'not_paid');
$r = OrderService::setStatus((int) $oc['id'], 'ready', ['notify' => false]);
check('same status twice is "same", not a second timeline line', !$r['ok'] && $r['error'] === 'same');
$r = OrderService::setStatus((int) $oc['id'], 'cancelled', ['notify' => false]);
check('cancel: stock goes back (once)', $r['ok'] && $zzStock($pA) === $stockAfter + 3 && (int) $r['order']['stock_released'] === 1);
check('releasing again never double-restocks', OrderService::releaseStock((int) $oc['id']) === false && $zzStock($pA) === $stockAfter + 3);
check('a cancelled order cannot be reopened or changed', !OrderService::setStatus((int) $oc['id'], 'pending')['ok'] && !OrderService::setStatus((int) $oc['id'], 'confirmed')['ok']);
check('option stock came back too (M: 5)', ProductService::parseOptions(Database::value('SELECT options FROM shop_products WHERE id = ?', [$pA]))[0]['values'][1]['stock'] === 5);
check('the timeline records the changes without customer data', (function () use ($oc) {
    foreach (OrderService::log((int) $oc['id']) as $l) {
        if (str_contains($l['message'], ZZ_SHOP_EMAIL) || str_contains($l['message'], 'Tester')) {
            return false;
        }
    }
    return count(OrderService::log((int) $oc['id'])) >= 4;
})());
check('unknown status values are refused', !OrderService::setStatus((int) $oc['id'], 'hacked')['ok'] && !OrderService::setStatus(999999999, 'confirmed')['ok']);

// ---- holds / expiry
echo "Shop: holds and expiry\n";
$ph = $zzProduct(['track_stock' => 1, 'stock_qty' => 5, 'allow_booking' => 1]);
$h1 = $zzOrder([$zzLine($ph, 2)], 'pickup', 'book')['order'];
$h2 = $zzOrder([$zzLine($ph, 1)], 'pickup', 'cod')['order'];
check('booking reserves stock (5 -> 2)', $zzStock($ph) === 2);
Database::execute('UPDATE shop_orders SET hold_until = ? WHERE id = ?', [date('Y-m-d H:i:s', time() - 60), $h1['id']]);
Database::execute('UPDATE shop_orders SET hold_until = ?, payment_status = ? WHERE id = ?', [date('Y-m-d H:i:s', time() - 60), 'review', $h2['id']]);
check('releaseExpiredHolds cancels the expired unpaid order and frees its stock; a "review" order never expires', OrderService::releaseExpiredHolds() >= 1 && OrderService::find((int) $h1['id'])['status'] === 'cancelled' && $zzStock($ph) === 4 && OrderService::find((int) $h2['id'])['status'] === 'pending');
$h3 = $zzOrder([$zzLine($ph, 1)], 'pickup', 'book')['order'];
OrderService::confirmBooking((int) $h3['id'], null);
Database::execute('UPDATE shop_orders SET hold_until = ? WHERE id = ?', [date('Y-m-d H:i:s', time() - 60), $h3['id']]);
check('a confirmed booking keeps its stock (confirming clears the hold)', OrderService::releaseExpiredHolds() === 0 || OrderService::find((int) $h3['id'])['status'] === 'confirmed');
$ph2 = $zzOrder([$zzLine($ph, 1)], 'pickup', 'book')['order'];
Database::execute('UPDATE shop_orders SET hold_until = ? WHERE id = ?', [date('Y-m-d H:i:s', time() - 60), $ph2['id']]);
$exit = null;
exec('"' . PHP_BINARY . '" ' . escapeshellarg($zzRoot . '/bin/shop-maintenance.php') . ' --no-recheck 2>&1', $outLines, $exit);
check('bin/shop-maintenance.php releases expired holds from the command line', $exit === 0 && OrderService::find((int) $ph2['id'])['status'] === 'cancelled' && str_contains(implode(' ', $outLines), 'released'), implode(' | ', $outLines));
$ph3 = $zzProduct(['track_stock' => 1, 'stock_qty' => 3]);
$race = [];
$procs = [];
for ($i = 0; $i < 6; $i++) {
    $pipes = [];
    $p = proc_open([PHP_BINARY, $zzRoot . '/bin/selftest-shop-child.php', (string) $ph3, '2', 'zz-shop-race' . $i . '@example.test'], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    $procs[] = [$p, $pipes];
}
foreach ($procs as [$p, $pipes]) {
    $race[] = stream_get_contents($pipes[1]);
    fclose($pipes[0]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($p);
}
$okCount = substr_count(implode("\n", $race), 'OK ');
$failCount = substr_count(implode("\n", $race), 'FAIL stock');
check('the last 3 units, 12 parallel buyers in 6 processes: exactly 3 orders, 9 refused, stock exactly 0 (never negative)', $okCount === 3 && $failCount === 9 && $zzStock($ph3) === 0, "ok={$okCount} fail={$failCount} stock=" . $zzStock($ph3) . ' ' . substr(implode('', $race), 0, 200));
check('every race order has its own number', (int) Database::value("SELECT COUNT(DISTINCT order_number) FROM shop_orders WHERE customer_email LIKE 'zz-shop-race%@example.test'", [], 0) === 3);

// =====================================================================================================================================================
echo "Shop: payments (ledger, idempotency, review)\n";
SecretStore::put('flw_secret_key', 'FLWSECK_TEST-selftest');       // so that "flutterwave" is an offered method for the orders below (forgotten again further down)
$pp = $zzProduct(['price' => '50000.00', 'name' => 'ZZ Pay']);
$mk = static fn(string $m = 'momo'): array => $zzOrder([$zzLine($pp, 1)], 'pickup', $m)['order'];
$oP = $mk('momo');
check('PAY machine table: every pair decided, paid -> only refunded', PaymentService::canTransition('unpaid', 'paid') && !PaymentService::canTransition('paid', 'failed') && PaymentService::canTransition('paid', 'refunded') && !PaymentService::canTransition('refunded', 'paid') && PaymentService::canTransition('review', 'paid'));
$r = PaymentService::recordCustomerReference((int) $oP['id'], 'MP123456789');
$oPx = OrderService::find((int) $oP['id']);
check('customer reference: payment becomes pending (not paid), reference stored, ledger row pending/customer, hold kept', $r['ok'] && $oPx['payment_status'] === 'pending' && $oPx['payment_reference'] === 'MP123456789'
    && Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ? AND status = 'pending' AND source = 'customer'", [$oP['id']]) == 1);
check('customer reference: junk refused, the same one twice is a no-op, only momo / bank orders accept it', !PaymentService::recordCustomerReference((int) $oP['id'], '<script>')['ok'] && !PaymentService::recordCustomerReference((int) $oP['id'], 'ab')['ok']
    && PaymentService::recordCustomerReference((int) $oP['id'], 'MP123456789')['ok'] && Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ? AND source = 'customer'", [$oP['id']]) == 1
    && PaymentService::recordCustomerReference((int) $mk('cod')['id'], 'MP123456789')['error'] === 'not_allowed');
$r = PaymentService::markPaid((int) $oP['id'], ['method' => 'momo', 'reference' => 'MP123456789', 'amount' => 2000000, 'currency' => 'UGX', 'source' => 'staff', 'notify' => false]);
$oPx = OrderService::find((int) $oP['id']);
check('staff part payment: order stays pending-payment, balance shown, hold released', $r['ok'] && !empty($r['partial']) && $oPx['payment_status'] === 'pending' && PaymentService::balanceMinor($oPx) === 3000000 && $oPx['hold_until'] === null);
check('the pending customer row became the success row (one row per attempt)', Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ?", [$oP['id']]) == 1 && Database::value("SELECT status FROM shop_payments WHERE order_id = ?", [$oP['id']]) === 'success');
$r = PaymentService::markPaid((int) $oP['id'], ['method' => 'momo', 'reference' => 'MP999', 'amount' => 3000000, 'currency' => 'UGX', 'source' => 'staff', 'notify' => false]);
$oPx = OrderService::find((int) $oP['id']);
check('second part payment completes it: paid, paid_at set, auto-confirmed, balance 0', !empty($r['paid']) && $oPx['payment_status'] === 'paid' && $oPx['paid_at'] !== null && $oPx['status'] === 'confirmed' && PaymentService::balanceMinor($oPx) === 0);
$before = Database::value('SELECT COUNT(*) FROM shop_payments WHERE order_id = ?', [$oP['id']]);
$r = PaymentService::markPaid((int) $oP['id'], ['method' => 'momo', 'reference' => 'MP999', 'amount' => 3000000, 'currency' => 'UGX', 'source' => 'staff', 'notify' => false]);
check('REPLAY of the same reference: duplicate, nothing written, nothing changed', !empty($r['duplicate']) && Database::value('SELECT COUNT(*) FROM shop_payments WHERE order_id = ?', [$oP['id']]) === $before);
$oQ = $mk('momo');
$r = PaymentService::markPaid((int) $oQ['id'], ['method' => 'momo', 'reference' => 'MP999', 'amount' => 5000000, 'currency' => 'UGX', 'source' => 'staff', 'notify' => false]);
check('a transaction ID that already paid ANOTHER order is refused for staff: nothing recorded, the order stays unpaid', $r['ok'] === false && ($r['error'] ?? '') === 'reference_used' && OrderService::find((int) $oQ['id'])['payment_status'] === 'unpaid'
    && Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ?", [$oQ['id']]) == 0);
$r = PaymentService::markPaid((int) $oP['id'], ['method' => 'bank', 'reference' => 'OTHER1', 'amount' => 5000000, 'currency' => 'UGX', 'source' => 'staff', 'notify' => false]);
check('a different payment for an order that is already paid is kept as "duplicate" for staff to refund; order stays paid', !empty($r['duplicate']) && OrderService::find((int) $oP['id'])['payment_status'] === 'paid'
    && Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ? AND status = 'duplicate'", [$oP['id']]) == 1);
$r = PaymentService::markPaid((int) $oP['id'], ['method' => 'momo', 'reference' => 'ZZ1', 'amount' => 0, 'currency' => 'UGX', 'source' => 'staff', 'notify' => false]);
check('staff cannot record a zero / negative amount or a foreign currency', $r['ok'] === false && PaymentService::markPaid((int) $mk()['id'], ['method' => 'momo', 'amount' => 100, 'currency' => 'USD', 'source' => 'staff', 'notify' => false])['error'] === 'currency'
    && PaymentService::markPaid((int) $mk()['id'], ['method' => 'momo', 'amount' => -100, 'source' => 'staff', 'notify' => false])['error'] === 'invalid_amount');
// gateway-sourced mismatches
$g1 = $mk('flutterwave');
$r = PaymentService::markPaid((int) $g1['id'], ['method' => 'flutterwave', 'reference' => $g1['order_number'] . '-1', 'amount' => 4999900, 'currency' => 'UGX', 'source' => 'webhook', 'notify' => false]);
check('gateway amount LOWER than the total: flagged for review, NOT paid', !$r['ok'] && !empty($r['review']) && OrderService::find((int) $g1['id'])['payment_status'] === 'review');
$r2 = PaymentService::markPaid((int) $g1['id'], ['method' => 'flutterwave', 'reference' => $g1['order_number'] . '-1', 'amount' => 4999900, 'currency' => 'UGX', 'source' => 'webhook', 'notify' => false]);
check('the same mismatch delivered twice writes one review row only', Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ? AND status = 'review'", [$g1['id']]) == 1 && !empty($r2['review']));
$g2 = $mk('flutterwave');
$r = PaymentService::markPaid((int) $g2['id'], ['method' => 'flutterwave', 'reference' => $g2['order_number'] . '-1', 'amount' => 5000000, 'currency' => 'USD', 'source' => 'gateway', 'notify' => false]);
check('gateway currency mismatch: review, not paid', !$r['ok'] && !empty($r['review']) && OrderService::find((int) $g2['id'])['payment_status'] === 'review');
$g3 = $mk('flutterwave');
$r = PaymentService::markPaid((int) $g3['id'], ['method' => 'flutterwave', 'reference' => $g3['order_number'] . '-1', 'amount' => null, 'currency' => 'UGX', 'source' => 'gateway', 'notify' => false]);
check('gateway without a usable amount: review', !$r['ok'] && !empty($r['review']));
$g4 = $mk('flutterwave');
$r = PaymentService::markPaid((int) $g4['id'], ['method' => 'flutterwave', 'reference' => $g4['order_number'] . '-1', 'amount' => 6000000, 'currency' => 'UGX', 'source' => 'webhook', 'notify' => false]);
check('gateway amount HIGHER than the total is accepted (>=)', !empty($r['paid']) && OrderService::find((int) $g4['id'])['payment_status'] === 'paid');
$g5 = $mk('flutterwave');
OrderService::setStatus((int) $g5['id'], 'cancelled', ['notify' => false]);
$r = PaymentService::markPaid((int) $g5['id'], ['method' => 'flutterwave', 'reference' => $g5['order_number'] . '-1', 'amount' => 5000000, 'currency' => 'UGX', 'source' => 'webhook', 'notify' => false]);
check('money arriving for a CANCELLED order is flagged, never marked paid, never hidden', !$r['ok'] && !empty($r['review']) && OrderService::find((int) $g5['id'])['payment_status'] === 'review' && OrderService::find((int) $g5['id'])['status'] === 'cancelled');
check('staff cannot record a payment on a cancelled order', PaymentService::markPaid((int) $g5['id'], ['method' => 'cod', 'amount' => 100, 'currency' => 'UGX', 'source' => 'staff', 'notify' => false])['error'] === 'closed');
$f1 = $mk('momo');
PaymentService::markFailed((int) $f1['id'], 'REFX1', 'No money received', 'staff', null);
PaymentService::markFailed((int) $f1['id'], 'REFX1', 'No money received', 'staff', null);
check('markFailed: failed once (idempotent per reference); a paid order is never marked failed', OrderService::find((int) $f1['id'])['payment_status'] === 'failed' && Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ? AND status = 'failed'", [$f1['id']]) == 1
    && !empty(PaymentService::markFailed((int) $oP['id'], 'LATE', 'x', 'staff')['duplicate']) && OrderService::find((int) $oP['id'])['payment_status'] === 'paid');
check('staffSetStatus: failed -> unpaid allowed; paid / refunded cannot be set by hand', PaymentService::staffSetStatus((int) $f1['id'], 'unpaid')['ok'] && !PaymentService::staffSetStatus((int) $oP['id'], 'unpaid')['ok'] && !PaymentService::staffSetStatus((int) $f1['id'], 'paid')['ok']);
// refund
$rf = $mk('cod');
PaymentService::markPaid((int) $rf['id'], ['method' => 'cod', 'reference' => 'RCPT1', 'amount' => 5000000, 'currency' => 'UGX', 'source' => 'staff', 'notify' => false]);
$r = OrderService::setStatus((int) $rf['id'], 'refunded', ['notify' => false]);
check('refund: status refunded + payment refunded + ledger row; stock stays out unless the setting says so', $r['ok'] && OrderService::find((int) $rf['id'])['payment_status'] === 'refunded' && Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ? AND status = 'refunded'", [$rf['id']]) == 1);
check('refunding an unpaid order is refused; a refunded order accepts no payment', !OrderService::setStatus((int) $mk('cod')['id'], 'refunded')['ok'] && PaymentService::markPaid((int) $rf['id'], ['method' => 'cod', 'amount' => 100, 'currency' => 'UGX', 'source' => 'staff'])['error'] === 'already_refunded');
$cp = $mk('cod');
PaymentService::markPaid((int) $cp['id'], ['method' => 'cod', 'reference' => 'RCPT2', 'amount' => 5000000, 'currency' => 'UGX', 'source' => 'staff', 'notify' => false]);
OrderService::setStatus((int) $cp['id'], 'ready', ['notify' => false]);
check('completed is allowed once paid', OrderService::setStatus((int) $cp['id'], 'completed', ['notify' => false])['ok']);
$cx = $mk('cod');
OrderService::setStatus((int) $cx['id'], 'confirmed', ['notify' => false]);
check('a free (waived) order can be completed with allow_unpaid', OrderService::setStatus((int) $cx['id'], 'completed', ['notify' => false, 'allow_unpaid' => true])['ok']);

// =====================================================================================================================================================
echo "Shop: Flutterwave client and online payment against the LOCAL MOCK\n";
foreach ([8099, 8097, 8095, 18099] as $try) {
    $sock = @fsockopen('127.0.0.1', $try, $en, $es, 0.3);
    if ($sock === false) {
        $mockPort = $try;
        break;
    }
    fclose($sock);
}
SecretStore::forget('flw_secret_key');
Settings::flush();
if ($mockPort !== 0) {
    $null = DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null';
    $mockProc = proc_open([PHP_BINARY, '-S', '127.0.0.1:' . $mockPort, $zzRoot . '/bin/dev/mock-flutterwave.php'], [0 => ['pipe', 'r'], 1 => ['file', $null, 'w'], 2 => ['file', $null, 'w']], $mockPipes);
    for ($i = 0; $i < 40; $i++) {
        $sock = @fsockopen('127.0.0.1', $mockPort, $en, $es, 0.2);
        if ($sock !== false) {
            fclose($sock);
            break;
        }
        usleep(150000);
    }
}
$mockCall = static function (string $path, array $data = [], string $method = 'POST') use (&$mockPort): array {
    $ch = curl_init('http://127.0.0.1:' . $mockPort . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_POSTFIELDS => $method === 'POST' ? json_encode($data) : null, CURLOPT_HTTPHEADER => ['Content-Type: application/json']]);
    $body = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return ['code' => $code, 'json' => json_decode($body, true), 'body' => $body];
};
$mockUp = $mockPort !== 0 && @$mockCall('/__mock/state', [], 'GET')['code'] === 200;
if (!$mockUp) {
    echo "  info the mock server could not be started: the gateway checks were skipped\n";
} else {
    $mockCall('/__mock/reset');
    putenv('FLW_DEV_BASE_URL=http://127.0.0.1:' . $mockPort);
    putenv('FLW_SECRET_KEY=FLWSECK_TEST-mock-secret');
    putenv('FLW_SECRET_HASH=zz-mock-hash');
    Settings::flush();
    check('dev base URL honoured only on localhost while APP_ENV=local', FlutterwaveClient::baseUrl() === 'http://127.0.0.1:' . $mockPort && FlutterwaveClient::usingDevBase());
    Config::set('app.env', 'production');
    $prodBase = FlutterwaveClient::baseUrl();
    Config::set('app.env', 'local');
    check('the override is IGNORED outside APP_ENV=local (production always talks to api.flutterwave.com over https)', $prodBase === 'https://api.flutterwave.com');
    foreach (['http://evil.example', 'http://127.0.0.1.evil.example', 'http://user:pw@127.0.0.1:1', 'http://127.0.0.1:99/path', 'ftp://127.0.0.1', 'file:///etc/passwd', 'http://localhost.evil.com'] as $badUrl) {
        putenv('FLW_DEV_BASE_URL=' . $badUrl);
        if (FlutterwaveClient::devBase() !== null) {
            check('a non-local dev base URL is refused: ' . $badUrl, false);
        }
    }
    putenv('FLW_DEV_BASE_URL=http://127.0.0.1:' . $mockPort);
    check('hostile dev base URLs are all refused (other hosts, userinfo, paths, other schemes)', true);
    $safe = new \ReflectionMethod(FlutterwaveClient::class, 'safeLink');
    $safe->setAccessible(true);
    Config::set('app.env', 'production');
    check('redirect link must be https on flutterwave.com (production)', $safe->invoke(null, 'https://checkout.flutterwave.com/v3/hosted/pay/x') === true && $safe->invoke(null, 'http://checkout.flutterwave.com/x') === false
        && $safe->invoke(null, 'https://flutterwave.com.evil.com/x') === false && $safe->invoke(null, 'https://evil.com/?a=flutterwave.com') === false && $safe->invoke(null, 'javascript:alert(1)') === false && $safe->invoke(null, 'https://u:p@checkout.flutterwave.com/') === false);
    Config::set('app.env', 'local');

    $t = FlutterwaveClient::testConnection();
    check('test connection with the right key: ok, mode test, flagged as using the mock', $t['ok'] && $t['state'] === 'ok' && $t['mode'] === 'test' && $t['using_mock'] && !str_contains(json_encode($t), 'FLWSECK'));
    putenv('FLW_SECRET_KEY=FLWSECK_TEST-WRONG');
    $t = FlutterwaveClient::testConnection();
    check('test connection with a wrong key: invalid_key and a plain-language message; the key is never echoed', !$t['ok'] && $t['state'] === 'invalid_key' && !str_contains(json_encode($t), 'WRONG'));
    putenv('FLW_SECRET_KEY=FLWSECK_TEST-mock-secret');
    putenv('FLW_DEV_BASE_URL=http://127.0.0.1:1');
    $t = FlutterwaveClient::testConnection();
    check('test connection when the server cannot be reached: "unavailable", no exception', !$t['ok'] && $t['state'] === 'unavailable');
    putenv('FLW_DEV_BASE_URL=http://127.0.0.1:' . $mockPort);

    // a full successful payment
    $mkG = static fn(): array => $zzOrder([$zzLine($pp, 1)], 'pickup', 'flutterwave')['order'];
    // the customer comes back through the URL WE gave the gateway: it carries the keyed proof (OnlinePayment::returnKey) that this browser started the payment
    $rtn = static fn(array $q): array => OnlinePayment::handleReturn($q, OnlinePayment::returnKey((string) ($q['tx_ref'] ?? '')));
    $og = $mkG();
    $mailBefore = count(glob(STORAGE_PATH . '/mail/*.eml') ?: []);
    check('creating an online-payment order sends NO e-mail yet (mails go out when paid)', true);
    $b = OnlinePayment::begin($og);
    $txRef = (string) ($b['tx_ref'] ?? '');
    check('begin: tx_ref = <order number>-1, link to the hosted page, order payment pending, ledger row pending/gateway', !empty($b['ok']) && $txRef === $og['order_number'] . '-1' && str_contains((string) $b['link'], '/hosted/pay/' . $txRef)
        && OrderService::find((int) $og['id'])['payment_status'] === 'pending' && Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ? AND status = 'pending' AND source = 'gateway'", [$og['id']]) == 1);
    $mst = $mockCall('/__mock/state', [], 'GET')['json']['tx'][$txRef] ?? [];
    check('the request sent to Flutterwave carries the exact amount, currency, redirect URL, options and order number in meta', (int) ($mst['amount'] ?? 0) === 50000 && ($mst['currency'] ?? '') === 'UGX' && str_ends_with((string) ($mst['redirect_url'] ?? ''), '/shop/pay/return/' . OnlinePayment::returnKey($txRef))
        && ($mst['payment_options'] ?? '') === 'card,mobilemoneyuganda' && (($mst['meta']['order_number'] ?? '') === $og['order_number']), json_encode($mst));
    // the query string is NEVER trusted
    $ret = $rtn(['status' => 'successful', 'tx_ref' => $txRef, 'transaction_id' => '1']);
    check('return page with status=successful in the URL but the gateway says pending: the order is NOT paid', $ret['state'] === 'pending' && OrderService::find((int) $og['id'])['payment_status'] === 'pending');
    $mockCall('/__mock/outcome', ['tx_ref' => $txRef, 'status' => 'successful']);
    $ret = $rtn(['status' => 'cancelled', 'tx_ref' => $txRef]);
    check('the gateway says successful: paid, even if the URL claimed "cancelled" (verified server-side only)', $ret['state'] === 'paid' && OrderService::find((int) $og['id'])['payment_status'] === 'paid' && OrderService::find((int) $og['id'])['status'] === 'confirmed');
    $ret2 = $rtn(['tx_ref' => $txRef]);
    check('reloading the return page does nothing twice', $ret2['state'] === 'duplicate' && Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ? AND status = 'success'", [$og['id']]) == 1);
    $w = OnlinePayment::handleWebhook('zz-mock-hash', json_encode(['event' => 'charge.completed', 'data' => ['tx_ref' => $txRef, 'amount' => 1, 'status' => 'failed']]));
    check('webhook replay after payment: 200, idempotent, and the (false) amount / status in its body are ignored', $w['http'] === 200 && $w['state'] === 'duplicate' && OrderService::find((int) $og['id'])['payment_status'] === 'paid');
    // webhook authentication
    $w = OnlinePayment::handleWebhook('wrong-hash', json_encode(['data' => ['tx_ref' => $txRef]]));
    $w2 = OnlinePayment::handleWebhook(null, '{}');
    $w3 = OnlinePayment::handleWebhook('', '{}');
    check('webhook: a wrong / missing verif-hash is rejected with 401 and processes nothing', $w['http'] === 401 && $w2['http'] === 401 && $w3['http'] === 401);
    $w = OnlinePayment::handleWebhook('zz-mock-hash', 'not json');
    $w2 = OnlinePayment::handleWebhook('zz-mock-hash', json_encode(['data' => ['tx_ref' => 'ZZT-000000-NOPE-1']]));
    $w3 = OnlinePayment::handleWebhook('zz-mock-hash', str_repeat('x', 300000));
    check('authentic webhook with a garbage body / unknown tx_ref / huge body: always 200, nothing happens', $w['http'] === 200 && $w2['http'] === 200 && $w2['state'] === 'unknown_order' && $w3['http'] === 200);
    putenv('FLW_SECRET_HASH');
    check('without a configured secret hash NO webhook is accepted', OnlinePayment::handleWebhook('', '{}')['http'] === 401 && OnlinePayment::handleWebhook('zz-mock-hash', '{}')['http'] === 401);
    putenv('FLW_SECRET_HASH=zz-mock-hash');
    // webhook as the first signal (customer never came back)
    $o2g = $mkG();
    $b2 = OnlinePayment::begin($o2g);
    $mockCall('/__mock/outcome', ['tx_ref' => $b2['tx_ref'], 'status' => 'successful']);
    $w = OnlinePayment::handleWebhook('zz-mock-hash', json_encode(['event' => 'charge.completed', 'data' => ['tx_ref' => $b2['tx_ref']]]));
    $w2 = OnlinePayment::handleWebhook('zz-mock-hash', json_encode(['event' => 'charge.completed', 'data' => ['tx_ref' => $b2['tx_ref']]]));
    check('webhook before the return page pays the order; the second delivery is a harmless duplicate', $w['state'] === 'paid' && $w2['state'] === 'duplicate' && OrderService::find((int) $o2g['id'])['payment_status'] === 'paid'
        && Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ? AND status = 'success'", [$o2g['id']]) == 1);
    // the webhook route over real HTTP is exercised in part 2
    // mismatch: amount lower, currency other, tx_ref in the answer for another reference
    $o3 = $mkG();
    $b3 = OnlinePayment::begin($o3);
    $mockCall('/__mock/outcome', ['tx_ref' => $b3['tx_ref'], 'status' => 'successful', 'amount' => 100]);
    $ret = $rtn(['tx_ref' => $b3['tx_ref']]);
    check('gateway says successful but for 100 instead of 50,000: order flagged for REVIEW, not paid', $ret['state'] === 'review' && OrderService::find((int) $o3['id'])['payment_status'] === 'review');
    $o4 = $mkG();
    $b4 = OnlinePayment::begin($o4);
    $mockCall('/__mock/outcome', ['tx_ref' => $b4['tx_ref'], 'status' => 'successful', 'currency' => 'USD', 'amount' => 50000]);
    check('gateway says successful in the wrong currency: REVIEW', $rtn(['tx_ref' => $b4['tx_ref']])['state'] === 'review');
    $o5 = $mkG();
    $b5 = OnlinePayment::begin($o5);
    $mockCall('/__mock/outcome', ['tx_ref' => $b5['tx_ref'], 'status' => 'successful', 'amount' => 50000, 'tx_ref_in_response' => $o3['order_number'] . '-1']);
    $ret = $rtn(['tx_ref' => $b5['tx_ref']]);
    check('gateway answers with ANOTHER tx_ref than the one asked: REVIEW, the other order is untouched', $ret['state'] === 'review' && OrderService::find((int) $o3['id'])['payment_status'] === 'review' && OrderService::find((int) $o5['id'])['payment_status'] === 'review');
    $fake = $rtn(['tx_ref' => $o5['order_number'] . '-7']);
    $fake2 = $rtn(['tx_ref' => $og['order_number'] . '-2']);
    $fake3 = $rtn(['tx_ref' => "x'; DROP TABLE shop_orders;--"]);
    check('a tx_ref that was never started for the order (attempt 7, attempt 2) or is hostile never pays or flags anything', $fake['state'] === 'unknown_order' && $fake2['state'] === 'unknown_order' && $fake3['state'] === 'unknown_order' && Database::tableExists('shop_orders'));
    check('orderForTxRef: belongs only to the order whose number it carries', PaymentService::orderForTxRef($txRef)['id'] === $og['id'] && PaymentService::orderForTxRef('ZZT-000000-AAAA-1') === null && PaymentService::orderForTxRef('') === null);
    // failure, pay again
    $o6 = $mkG();
    $b6 = OnlinePayment::begin($o6);
    $mockCall('/__mock/outcome', ['tx_ref' => $b6['tx_ref'], 'status' => 'failed']);
    $ret = $rtn(['tx_ref' => $b6['tx_ref'], 'status' => 'failed']);
    $o6x = OrderService::find((int) $o6['id']);
    check('failed payment: order unpaid-failed, still open, stock still reserved', $ret['state'] === 'failed' && $o6x['payment_status'] === 'failed' && $o6x['status'] === 'pending' && OrderService::paymentInfo($o6x)['can_pay_online']);
    $b6b = OnlinePayment::begin($o6x);
    check('"Pay again": a NEW tx_ref (attempt 2) and a new ledger row', !empty($b6b['ok']) && $b6b['tx_ref'] === $o6['order_number'] . '-2' && OrderService::find((int) $o6['id'])['payment_status'] === 'pending');
    $mockCall('/__mock/outcome', ['tx_ref' => $b6b['tx_ref'], 'status' => 'successful']);
    check('...which can then succeed', $rtn(['tx_ref' => $b6b['tx_ref']])['state'] === 'paid');
    $pending = $rtn(['tx_ref' => $o6['order_number'] . '-1']);
    check('the old failed attempt cannot overwrite a paid order', OrderService::find((int) $o6['id'])['payment_status'] === 'paid');
    // cancelled in the hosted page
    $o7 = $mkG();
    $b7 = OnlinePayment::begin($o7);
    $ret = $rtn(['tx_ref' => $b7['tx_ref'], 'status' => 'cancelled']);
    check('customer cancelled on the hosted page: wording "cancelled", the order stays unpaid for a later try', $ret['state'] === 'cancelled' && OrderService::find((int) $o7['id'])['payment_status'] === 'pending');
    // gateway errors
    $mockCall('/__mock/config', ['fail' => '500']);
    $ret = $rtn(['tx_ref' => $b7['tx_ref']]);
    check('gateway 500 on verify: "verify_failed", nothing is marked paid', $ret['state'] === 'verify_failed' && OrderService::find((int) $o7['id'])['payment_status'] === 'pending');
    $mockCall('/__mock/config', ['fail' => 'bad_json']);
    check('gateway answers with HTML instead of JSON: verify_failed', $rtn(['tx_ref' => $b7['tx_ref']])['state'] === 'verify_failed');
    $mockCall('/__mock/config', ['fail' => '']);
    $attempts = $mkG();
    for ($i = 0; $i < PaymentService::MAX_GATEWAY_ATTEMPTS; $i++) {
        OnlinePayment::begin(OrderService::find((int) $attempts['id']));
    }
    check('attempts are capped (a customer cannot spawn endless tx_refs)', (OnlinePayment::begin(OrderService::find((int) $attempts['id']))['error'] ?? '') === 'too_many_attempts');
    $mockCall('/__mock/config', ['secret' => 'something-else']);
    $ob = $mkG();
    $b = OnlinePayment::begin($ob);
    check('a rejected key: begin fails with invalid_key, the attempt is closed as failed, no link', empty($b['ok']) && $b['error'] === 'invalid_key' && OrderService::find((int) $ob['id'])['payment_status'] === 'failed');
    $mockCall('/__mock/config', ['secret' => 'FLWSECK_TEST-mock-secret']);
    $noKey = $mkG();
    putenv('FLW_SECRET_KEY');
    check('no key at all: the method is not available and begin() refuses', !PaymentMethods::ready('flutterwave') && (OnlinePayment::begin($noKey)['error'] ?? '') === 'unavailable' && ($zzOrder([$zzLine($pp, 1)], 'pickup', 'flutterwave')['error'] ?? '') === 'payment');
    putenv('FLW_SECRET_KEY=FLWSECK_TEST-mock-secret');
    check('a paid order cannot start a new payment', (OnlinePayment::begin(OrderService::find((int) $og['id']))['error'] ?? '') === 'not_payable');
    $rc = $mkG();
    $rb = OnlinePayment::begin($rc);
    $mockCall('/__mock/outcome', ['tx_ref' => $rb['tx_ref'], 'status' => 'successful']);
    check('staff "re-check with the gateway" settles an order whose webhook never came', OnlinePayment::recheck((int) $rc['id'])['state'] === 'paid' && OnlinePayment::recheck((int) $rc['id'])['state'] === 'paid' && Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ? AND status = 'success'", [$rc['id']]) == 1 && OnlinePayment::recheck((int) $zzOrder([$zzLine($pp, 1)])['order']['id'])['state'] === 'no_attempt');
    check('the secret key never appears in the event log or order log', (function () {
        foreach (Database::all('SELECT kind, ref, status FROM shop_events ORDER BY id DESC LIMIT 200') as $e) {
            if (str_contains(json_encode($e), 'FLWSECK') || str_contains(json_encode($e), 'zz-mock-hash')) {
                return false;
            }
        }
        return (int) Database::value("SELECT COUNT(*) FROM shop_order_log WHERE message LIKE '%FLWSECK%' OR message LIKE '%mock-hash%'", [], 0) === 0
            && (int) Database::value("SELECT COUNT(*) FROM audit_log WHERE details LIKE '%FLWSECK%' OR summary LIKE '%FLWSECK%'", [], 0) === 0;
    })());
    check('events record kind / reference / status only', Database::value("SELECT COUNT(*) FROM shop_events WHERE kind IN ('payment_started','return_verified','webhook_verified','webhook_rejected','mismatch')", [], 0) >= 5 && count(PaymentService::events(20)) === 20);
    // ---- review pass: payment money paths ---------------------------------------------------------------------------------------------------------------
    // the return URL is tied to the browser that started the payment: an order number alone (printed on receipts, e-mails, statements) is never a key
    $rb = $mkG();
    $rbb = OnlinePayment::begin($rb);
    $mockCall('/__mock/outcome', ['tx_ref' => $rbb['tx_ref'], 'status' => 'successful']);
    $u1 = OnlinePayment::handleReturn(['tx_ref' => $rbb['tx_ref']]);
    $u2 = OnlinePayment::handleReturn(['tx_ref' => $rbb['tx_ref']], str_repeat('a', 32));
    $u3 = OnlinePayment::handleReturn(['tx_ref' => $rb['order_number'] . '-9'], str_repeat('a', 32));
    $u4 = OnlinePayment::handleReturn(['tx_ref' => $rbb['tx_ref']], OnlinePayment::returnKey($rb['order_number'] . '-2'));
    check('the order number alone (no key, a wrong key, the key of another try) reaches nothing: no order, no gateway call, the same answer for real and invented references',
        $u1['order'] === null && $u2['order'] === null && $u3['order'] === null && $u4['order'] === null && $u1['state'] === 'unbound' && $u2['state'] === $u3['state'] && $u3['state'] === $u4['state']
        && OrderService::find((int) $rb['id'])['payment_status'] === 'pending');
    $okRet = $rtn(['tx_ref' => $rbb['tx_ref']]);
    check('with the key we gave the gateway, the same return verifies and pays the order and hands back the order', $okRet['state'] === 'paid' && (int) ($okRet['order']['id'] ?? 0) === (int) $rb['id']);
    check('the return URL given to the gateway carries the key as a path segment (the gateway appends its own query)', preg_match('#/shop/pay/return/[a-f0-9]{32}$#', OnlinePayment::returnUrl('X-1')) === 1 && OnlinePayment::returnKey('A-1') !== OnlinePayment::returnKey('A-2') && strlen(OnlinePayment::returnKey('A-1')) === 32);

    // only "successful" is money (the documented value); "completed" is not
    $cm = $mkG();
    $cmb = OnlinePayment::begin($cm);
    $mockCall('/__mock/outcome', ['tx_ref' => $cmb['tx_ref'], 'status' => 'completed']);
    check('a verify answer of "completed" is NOT proof of payment', $rtn(['tx_ref' => $cmb['tx_ref']])['state'] === 'pending' && OrderService::find((int) $cm['id'])['payment_status'] === 'pending');

    // two tries, the customer paid on the FIRST and pressed "Pay again" (no webhook): the re-check looks at every open try
    $m1 = $mkG();
    $m1a = OnlinePayment::begin($m1);
    $m1b = OnlinePayment::begin(OrderService::find((int) $m1['id']));
    $mockCall('/__mock/outcome', ['tx_ref' => $m1a['tx_ref'], 'status' => 'successful']);
    $rcAll = OnlinePayment::recheck((int) $m1['id']);
    check('two tries, money on the earlier one, no webhook: the re-check verifies EVERY open try and records the payment', ($m1b['tx_ref'] ?? '') === $m1['order_number'] . '-2' && $rcAll['state'] === 'paid'
        && OrderService::find((int) $m1['id'])['payment_status'] === 'paid' && Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ? AND status = 'pending'", [$m1['id']]) == 1);

    // an earlier try that failed must not fail the order while a later try is still running
    $m2 = $mkG();
    $m2a = OnlinePayment::begin($m2);
    $m2b = OnlinePayment::begin(OrderService::find((int) $m2['id']));
    $mockCall('/__mock/outcome', ['tx_ref' => $m2a['tx_ref'], 'status' => 'failed']);
    $oldFail = $rtn(['tx_ref' => $m2a['tx_ref']]);
    check('a late "failed" for an earlier try closes only that try: the order stays payable through the newer one', $oldFail['state'] === 'superseded' && OrderService::find((int) $m2['id'])['payment_status'] === 'pending'
        && Database::value("SELECT status FROM shop_payments WHERE reference = ?", [$m2a['tx_ref']]) === 'failed' && Database::value("SELECT status FROM shop_payments WHERE reference = ?", [$m2b['tx_ref']]) === 'pending');

    // "Pay again" asks the gateway first: a customer who has already paid is never invited to pay twice
    $m3 = $mkG();
    $m3a = OnlinePayment::begin($m3);
    $mockCall('/__mock/outcome', ['tx_ref' => $m3a['tx_ref'], 'status' => 'successful']);
    $again = OnlinePayment::begin(OrderService::find((int) $m3['id']));
    check('"Pay again" on an order the gateway already marks paid: refused (already_paid), the order is settled, no second payable link', ($again['error'] ?? '') === 'already_paid' && OrderService::find((int) $m3['id'])['payment_status'] === 'paid'
        && (int) OrderService::find((int) $m3['id'])['gateway_attempts'] === 1);

    // two links both paid: the second one is a flagged duplicate and leaves NO orphan "pending" row
    $m4 = $mkG();
    $m4a = OnlinePayment::begin($m4);
    $m4b = OnlinePayment::begin(OrderService::find((int) $m4['id']));
    $mockCall('/__mock/outcome', ['tx_ref' => $m4a['tx_ref'], 'status' => 'successful']);
    $mockCall('/__mock/outcome', ['tx_ref' => $m4b['tx_ref'], 'status' => 'successful']);
    $d1 = $rtn(['tx_ref' => $m4a['tx_ref']]);
    $d2 = $rtn(['tx_ref' => $m4b['tx_ref']]);
    check('a second payment on another link: state duplicate, the pending row of that try becomes the duplicate row (nothing stays "pending")', $d1['state'] === 'paid' && $d2['state'] === 'duplicate'
        && Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ? AND status = 'pending'", [$m4['id']]) == 0 && Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ? AND status = 'duplicate'", [$m4['id']]) == 1);

    // the hold ran out but the webhook was lost: the lazy release asks the gateway BEFORE cancelling
    $x1 = $mkG();
    $x1a = OnlinePayment::begin($x1);
    $mockCall('/__mock/outcome', ['tx_ref' => $x1a['tx_ref'], 'status' => 'successful']);
    Database::execute('UPDATE shop_orders SET hold_until = ? WHERE id = ?', [date('Y-m-d H:i:s', time() - 60), $x1['id']]);
    OrderService::releaseExpiredHolds(25);
    $x1x = OrderService::find((int) $x1['id']);
    check('expired hold + lost webhook: the payment is recorded instead of the order being cancelled', $x1x['status'] === 'confirmed' && $x1x['payment_status'] === 'paid');

    // ...and when the gateway cannot be reached at that moment the order is cancelled, but the cron keeps looking and flags the money for staff
    $x2 = $mkG();
    $x2a = OnlinePayment::begin($x2);
    Database::execute('UPDATE shop_orders SET hold_until = ? WHERE id = ?', [date('Y-m-d H:i:s', time() - 60), $x2['id']]);
    Database::execute('UPDATE shop_payments SET created_at = ? WHERE order_id = ?', [date('Y-m-d H:i:s', time() - 900), $x2['id']]);
    $mockCall('/__mock/config', ['fail' => '500']);
    OrderService::releaseExpiredHolds(25);
    $x2x = OrderService::find((int) $x2['id']);
    check('gateway down at expiry: the order is cancelled (stock returns) and its payment stays "pending"', $x2x['status'] === 'cancelled' && $x2x['payment_status'] === 'pending');
    $mockCall('/__mock/config', ['fail' => '']);
    $mockCall('/__mock/outcome', ['tx_ref' => $x2a['tx_ref'], 'status' => 'successful']);
    $cronOut = [];
    exec('"' . PHP_BINARY . '" ' . escapeshellarg($zzRoot . '/bin/shop-maintenance.php') . ' 2>&1', $cronOut, $cronExit);
    $x2y = OrderService::find((int) $x2['id']);
    check('the cron re-checks CANCELLED orders too: money that turns up is flagged for review (never hidden, never silently paid)', $cronExit === 0 && $x2y['status'] === 'cancelled' && $x2y['payment_status'] === 'review' && str_contains(implode(' ', $cronOut), 'flagged for review'), implode(' | ', $cronOut));
    check('...staff can see it (it counts in the needs-action number) and it can be recorded as refunded', PaymentService::refundableMinor($x2y) === 5000000 && (function () use ($x2) {
        OrderService::flushCounts();
        $r = PaymentService::staffSetStatus((int) $x2['id'], 'refunded', null, 'sent back');
        $o = OrderService::find((int) $x2['id']);
        return $r['ok'] && $o['payment_status'] === 'refunded' && Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ? AND status = 'refunded'", [$x2['id']]) == 1;
    })());

    // a paid order that is cancelled keeps its money until the refund is recorded
    $f1 = $mkG();
    $f1a = OnlinePayment::begin($f1);
    $mockCall('/__mock/outcome', ['tx_ref' => $f1a['tx_ref'], 'status' => 'successful']);
    $rtn(['tx_ref' => $f1a['tx_ref']]);
    OrderService::setStatus((int) $f1['id'], 'cancelled', ['notify' => false]);
    $f1x = OrderService::find((int) $f1['id']);
    check('paid then cancelled: the money is "refundable" (UGX 50,000) and the order does not count as revenue', $f1x['payment_status'] === 'paid' && PaymentService::refundableMinor($f1x) === 5000000);
    $rf1 = PaymentService::staffSetStatus((int) $f1['id'], 'refunded', null, 'returned by mobile money');
    $rf2 = PaymentService::staffSetStatus((int) $f1['id'], 'refunded');
    check('staff record the refund once; a second try says "same"; the ledger has one refunded row of the full amount', $rf1['ok'] && ($rf2['error'] ?? '') === 'same' && OrderService::find((int) $f1['id'])['payment_status'] === 'refunded'
        && Database::value("SELECT amount FROM shop_payments WHERE order_id = ? AND status = 'refunded'", [$f1['id']]) === '50000.00');

    // the mock's hosted page redirects to our return URL
    $hp = $mockCall('/hosted/pay/' . $b7['tx_ref'], [], 'GET');
    check('the mock hosted page exists (browser demo)', $hp['code'] === 200 && str_contains($hp['body'], 'LOCAL MOCK'));
}
putenv('FLW_DEV_BASE_URL');
putenv('FLW_SECRET_KEY');
putenv('FLW_SECRET_HASH');

// =====================================================================================================================================================
echo "Shop: payment methods\n";
SecretStore::forget('flw_secret_key');
SecretStore::forget('flw_secret_hash');
Settings::flush();
check('flutterwave is not offered until a secret key exists', !PaymentMethods::ready('flutterwave') && !PaymentMethods::isAvailable('flutterwave', false) && count(PaymentMethods::problems('flutterwave')) >= 1);
SecretStore::put('flw_secret_key', 'FLWSECK_TEST-x');
check('flutterwave is offered once the key is set (the webhook hash is advised, not required)', PaymentMethods::ready('flutterwave') && PaymentMethods::isAvailable('flutterwave', false));
check('booking is only offered for carts where every item allows it', !PaymentMethods::isAvailable('book', false) && PaymentMethods::isAvailable('book', true));
$zzSet('shop_pay_momo_sort', '1');
check('method order follows the sort setting', PaymentMethods::all()[0]['key'] === 'momo');
$zzSet('shop_momo_mtn_number', '');
check('momo without a number is not offered', !PaymentMethods::isAvailable('momo', false) && PaymentMethods::problems('momo') !== []);
$zzSet('shop_momo_mtn_number', '0777000111');
check('labels and instructions: the settings text wins, empty falls back to the registry wording', PaymentMethods::label('momo') === content('shop', 'payment.momo_label') && (function () use ($zzSet) { $zzSet('shop_pay_momo_label', 'MoMo Test'); return PaymentMethods::label('momo') === 'MoMo Test'; })());
check('payment options sent to Flutterwave are filtered to the known list', (function () use ($zzSet) { $zzSet('shop_flw_options', 'card,evil,mobilemoneyuganda,ussd'); return PaymentMethods::flwOptions() === ['card', 'mobilemoneyuganda']; })());
SecretStore::forget('flw_secret_key');

// =====================================================================================================================================================
echo "Shop: CSV export and e-mails\n";
$ev = $zzOrder([$zzLine($pB, 1)], 'delivery', 'cod', ['name' => '=HYPERLINK("http://evil","x")', 'address' => '@SUM(1+1) street one', 'note' => '+cmd|calc'])['order'];
$csv = tempnam(sys_get_temp_dir(), 'zzshop');
CsvExport::write($csv, ShopExport::ORDER_HEADER, (function () {
    foreach (ShopExport::rows('orders', ['q' => 'HYPERLINK']) as $r) {
        yield $r;
    }
})());
$raw = (string) file_get_contents($csv);
@unlink($csv);
check('CSV: a name / address / note starting with = @ + is neutralised (no live formula in the file)', str_contains($raw, "\"'=HYPERLINK") && str_contains($raw, "'@SUM") && str_contains($raw, "'+cmd") && !preg_match('/(^|,)"?=HYPERLINK/m', $raw));
check('CSV: header + one row per order, items summarised, money as plain decimals', str_starts_with($raw, "\xEF\xBB\xBF") && str_contains($raw, 'Order number') && str_contains($raw, '15000.00'));
$items = iterator_to_array(ShopExport::rows('items', ['q' => 'HYPERLINK']), false);
check('CSV items: one row per ordered item', count($items) === 1 && $items[0][7] === '1');
$mail = ShopMailer::render('order', $ev, OrderService::items((int) $ev['id']));
check('e-mail placeholders are filled (order number, total, link, items)', str_contains($mail['subject'], $ev['order_number']) && str_contains($mail['text'], 'UGX 15,000') && str_contains($mail['text'], '/shop/order/' . $ev['token']) && !preg_match('/\{[a-z_]+\}/', $mail['text'] . $mail['subject']));
$evil = $zzOrder([$zzLine($pB, 1)], 'pickup', 'cod', ['name' => "Eve\r\nBcc: spy@example.test {order_url} {total}"])['order'];
$m2 = ShopMailer::render('order', $evil, OrderService::items((int) $evil['id']));
check('e-mail: CR/LF in a customer name cannot add a header line to the SUBJECT, and a placeholder typed by the customer is not expanded', !str_contains($m2['subject'], "\n") && !str_contains($m2['subject'], "\r") && !str_contains($m2['text'], 'UGX 15,000 UGX') && substr_count($m2['text'], '/shop/order/') === 1);
$files0 = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
ShopMailer::resendCustomer($evil);
$files1 = array_values(array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $files0));
$eml = $files1 !== [] ? (string) file_get_contents($files1[0]) : '';
check('sent .eml: no injected Bcc header, HTML part escaped, link present', $files1 !== [] && !preg_match('/^Bcc:/mi', $eml) && str_contains($eml, 'multipart/alternative'));
$html = ShopMailer::render('order', array_merge($evil, ['customer_name' => '<script>alert(1)</script>']), [])['html'];
check('e-mail HTML never carries markup typed by the customer', !str_contains($html, '<script>'));
SecretStore::put('flw_secret_key', 'FLWSECK_TEST-selftest');
// the "order received" mails go to an unverified address and are capped per address and per hour: the selftest sends many to the same test address, so reset the counters
$zzMailReset = static function (): void {
    foreach ([ZZ_SHOP_EMAIL, 'zz-shop-http3@example.test'] as $addr) {
        \App\Core\RateLimit::clear('shop-mail:' . sha1(strtolower($addr)));
    }
    \App\Core\RateLimit::clear('shop-mail-all');
};
$zzMailReset();
$files0 = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
$gm = $zzOrder([$zzLine($pB, 1)], 'pickup', 'flutterwave', [])['order'];
ShopMailer::orderPlaced($gm, OrderService::items((int) $gm['id']));
$gmNew = array_values(array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $files0));
/** the plain-text part of a stored .eml (the file mail driver writes it base64-encoded) */
$zzMailText = static function (string $file): string {
    $raw = (string) file_get_contents($file);
    return preg_match('/Content-Type: text\/plain[^
]*
(?:[^
]*
)*??
([A-Za-z0-9+\/=
]+)/', $raw, $m) === 1 ? (string) base64_decode(preg_replace('/\s+/', '', $m[1]) ?? '') : $raw;
};
check('orderPlaced sends only the short "complete your payment" mail to the customer for an online order (no staff notice until it is paid)', count($gmNew) === 1
    && str_contains($zzMailText($gmNew[0]), $gm['order_number']) && str_contains($zzMailText($gmNew[0]), '/shop/order/' . $gm['token']), count($gmNew) . ' new file(s)');
SecretStore::forget('flw_secret_key');
$zzMailReset();
$files0 = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
$cod = OrderService::create($zzCustomer(), [$zzLine($pB, 1)], 'pickup', 'cod', ['notify' => true])['order'];
$new = array_values(array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $files0));
check('a pay-on-pickup order e-mails the customer and the staff (2 messages)', count($new) === 2);
$to = array_map(static fn(string $f): string => (preg_match('/^To:\s*(.+)$/mi', (string) file_get_contents($f), $m) === 1 ? trim($m[1]) : ''), $new);
check('...to the customer and to the configured staff address only', in_array(ZZ_SHOP_EMAIL, $to, true) && in_array('zz-shop-staff@example.test', $to, true));

// =====================================================================================================================================================
echo "Shop: statistics, dashboard numbers\n";
$st = ShopStats::summary();
check('summary: totals are consistent (paid revenue counts only paid orders; cancelled orders excluded from units)', $st['orders'] > 5 && $st['revenue'] > 0 && $st['by_status']['cancelled'] >= 1 && is_array($st['revenue_by_method']) && $st['currency'] === 'UGX');
check('new-order badge counts pending + review orders', OrderService::newCount() > 0 && (function () { OrderService::flushCounts(); return OrderService::newCount() >= 1; })());
check('low stock alerts list the sold-out race product', in_array($ph3, array_column(ShopStats::lowStock(100), 'id'), true));
check('byDay returns one row per day', count(ShopStats::byDay(7)) === 7);
check('search: by status / number / e-mail / phone and the LIKE wildcard is literal', OrderService::search(['q' => $cod['order_number']])['total'] === 1 && OrderService::search(['q' => ZZ_SHOP_EMAIL])['total'] >= 3 && OrderService::search(['q' => '%'])['total'] === 0
    && OrderService::search(['status' => 'cancelled'])['total'] >= 1 && OrderService::search(['method' => 'nope'])['total'] === OrderService::search([])['total']);

echo "Shop: route parameters (integrator)\n";
// The router casts ONLY a parameter called {id} (and the globally declared \d+ patterns) to int; any other parameter, such as {iid}, arrives as a STRING and a
// strict-typed "int $iid" in the controller is a TypeError (HTTP 500) - found the hard way on the picture buttons. Every shop route is checked here.
$zzRouter = new \App\Core\Router();
foreach (['web_shop', 'admin_shop'] as $zzRouteFile) {
    $router = $zzRouter;
    require APP_PATH . '/routes/' . $zzRouteFile . '.php';
}
$zzBadParam = [];
foreach ($zzRouter->routes() as $zzRoute) {
    if (!is_array($zzRoute->handler) || !preg_match_all('/\{([a-z_]+)\}/i', $zzRoute->path, $zzM)) {
        continue;
    }
    $zzRef = new \ReflectionMethod($zzRoute->handler[0], $zzRoute->handler[1]);
    $zzArgs = array_slice($zzRef->getParameters(), 1);          // after Request
    foreach ($zzM[1] as $zzI => $zzName) {
        $zzType = isset($zzArgs[$zzI]) ? (string) $zzArgs[$zzI]->getType() : '';
        $zzCast = $zzName === 'id' || (isset(\App\Core\Route::PATTERNS[$zzName]) && \App\Core\Route::PATTERNS[$zzName] === '\d+');
        if ($zzType === 'int' && !$zzCast) {
            $zzBadParam[] = $zzRoute->path . ' {' . $zzName . '} -> ' . $zzRoute->handler[1] . '(' . $zzType . ')';
        }
    }
}
check('no shop route has a strict-int controller parameter that the router does not cast ({iid} etc.)', $zzBadParam === [], implode('; ', $zzBadParam));

// =====================================================================================================================================================
// =====================================================================================================================================================
echo "Shop: pictures\n";
if (!extension_loaded('gd')) {
    echo "  info GD is not loaded: run with  php -d extension=gd bin/selftest-shop.php  to test the picture pipeline\n";
} else {
    $pi = $zzProduct(['name' => 'ZZ Pictures']);
    $mkImg = static function (int $w, int $h, string $fmt = 'png') use (&$zzUploads): string {
        $im = imagecreatetruecolor($w, $h);
        imagefill($im, 0, 0, imagecolorallocate($im, 200, 40, 40));
        imagefilledrectangle($im, (int) ($w * 0.1), (int) ($h * 0.1), (int) ($w * 0.9), (int) ($h * 0.5), imagecolorallocate($im, 20, 20, 120));
        $f = tempnam(sys_get_temp_dir(), 'zzimg') . '.' . $fmt;
        $fmt === 'png' ? imagepng($im, $f) : imagejpeg($im, $f);
        $zzUploads[] = $f;
        return $f;
    };
    $f1 = $mkImg(2400, 1600);
    $r = ProductImages::upload($pi, ['name' => 'a.png', 'tmp_name' => $f1, 'error' => 0, 'size' => filesize($f1)], ['crop' => 'square', 'alt' => '<b>Red</b> hoodie', 'allow_nonupload' => true]);
    $abs = static fn(?string $p): string => UPLOAD_PATH . '/' . (string) $p;
    $dim = @getimagesize($abs($r['path']));
    $dim2 = @getimagesize($abs($r['path_2x']));
    check('upload: square crop, 1x 600 and exactly-2x 1200 JPEG + WebP, width / height stored, first picture is the cover, alt text stripped of tags',
        $dim && $dim[0] === 600 && $dim[1] === 600 && $dim2 && $dim2[0] === 1200 && is_file($abs($r['path_webp'])) && is_file($abs($r['path_webp_2x'])) && (int) $r['width'] === 600 && (int) $r['is_cover'] === 1 && $r['alt'] === 'Red hoodie' && str_starts_with((string) $r['path'], 'shop/'));
    $f2 = $mkImg(500, 300, 'jpg');
    $r2 = ProductImages::upload($pi, ['name' => 'b.jpg', 'tmp_name' => $f2, 'error' => 0, 'size' => filesize($f2)], ['crop' => 'portrait', 'allow_nonupload' => true]);
    $d = @getimagesize($abs($r2['path']));
    check('upload: a small picture is never upscaled and gets no 2x; portrait crop is 4:5', $r2['path_2x'] === null && $d && $d[0] < 500 && abs($d[0] / $d[1] - 0.8) < 0.02 && (int) $r2['is_cover'] === 0);
    $v = ProductImages::view($r);
    check('view(): src, srcset with width descriptors, webp srcset, dimensions', $v['src'] !== '' && str_contains($v['srcset'], '600w') && str_contains($v['srcset'], '1200w') && str_contains($v['webp_srcset'], '.webp') && $v['width'] === 600);
    $php = tempnam(sys_get_temp_dir(), 'zzimg') . '.png';
    file_put_contents($php, file_get_contents($f1) . '<?php echo 1; ?>');
    $zzUploads[] = $php;
    $bad = '';
    try {
        ProductImages::upload($pi, ['name' => 'c.png', 'tmp_name' => $php, 'error' => 0, 'size' => filesize($php)], ['allow_nonupload' => true]);
    } catch (\App\Core\UploadException $e) {
        $bad = $e->getMessage();
    }
    check('a PNG that hides PHP code is refused', $bad !== '');
    $txt = tempnam(sys_get_temp_dir(), 'zzimg') . '.png';
    file_put_contents($txt, 'just text');
    $zzUploads[] = $txt;
    $bad2 = '';
    try {
        ProductImages::upload($pi, ['name' => 'd.png', 'tmp_name' => $txt, 'error' => 0, 'size' => 9], ['allow_nonupload' => true]);
    } catch (\App\Core\UploadException $e) {
        $bad2 = $e->getMessage();
    }
    $bad3 = '';
    try {
        ProductImages::upload($pi, ['name' => 'e.php', 'tmp_name' => $f1, 'error' => 0, 'size' => 1], ['allow_nonupload' => true]);
    } catch (\App\Core\UploadException $e) {
        $bad3 = $e->getMessage();
    }
    $bad4 = '';
    try {
        ProductImages::upload($pi, ['name' => 'f.png', 'tmp_name' => $f1, 'error' => 0, 'size' => 1]);
    } catch (\App\Core\UploadException $e) {
        $bad4 = $e->getMessage();
    }
    check('text renamed .png, a .php name and a file that was not really uploaded are refused', $bad2 !== '' && $bad3 !== '' && $bad4 !== '');
    ProductImages::reorder($pi, [(int) $r2['id'], (int) $r['id'], 999999]);
    $order = array_column(ProductImages::forProduct($pi), 'id');
    check('reorder: the first of the list becomes the cover; foreign ids are ignored', array_map('intval', $order) === [(int) $r2['id'], (int) $r['id']] && (int) Database::value('SELECT is_cover FROM shop_product_images WHERE id = ?', [$r2['id']]) === 1 && (int) Database::value('SELECT is_cover FROM shop_product_images WHERE id = ?', [$r['id']]) === 0);
    $files = [$abs($r['path']), $abs($r['path_2x']), $abs($r['path_webp']), $abs($r['path_webp_2x'])];
    ProductImages::remove((int) $r['id']);
    check('remove: the row and all four files go; a cover is always kept', !is_file($files[0]) && !is_file($files[1]) && !is_file($files[2]) && !is_file($files[3]) && (int) Database::value('SELECT COUNT(*) FROM shop_product_images WHERE product_id = ? AND is_cover = 1', [$pi], 0) === 1);
    ProductImages::remove((int) $r2['id']);
    $bid = Database::insert('shop_product_images', ['product_id' => $pi, 'path' => 'assets/img/shop/cap-navy.jpg', 'path_webp' => 'assets/img/shop/cap-navy.webp', 'path_2x' => 'assets/img/shop/cap-navy-2x.jpg', 'path_webp_2x' => 'assets/img/shop/cap-navy-2x.webp', 'width' => 600, 'height' => 600, 'alt' => 'x', 'sort_order' => 10, 'is_cover' => 1]);
    ProductImages::remove($bid);
    check('bundled sample pictures are never deleted when a picture row is removed', is_file($zzRoot . '/assets/img/shop/cap-navy.jpg') && is_file($zzRoot . '/assets/img/shop/cap-navy-2x.webp'));
}

// =====================================================================================================================================================
// =====================================================================================================================================================
echo "Shop: review-pass fixes (no gateway)\n";
// -- one shelf for every size / colour line of a product
CartService::clear();
$_SESSION = [];
$pSh = $zzProduct(['track_stock' => 1, 'stock_qty' => 10, 'name' => 'ZZ Shared Shelf', 'options' => ProductService::encodeOptions([['label' => 'Size', 'values' => [['label' => 'S', 'stock' => 2], ['label' => 'M']]]])]);
$sa1 = CartService::add($pSh, ['size' => 's'], 2);
$sa2 = CartService::add($pSh, ['size' => 'm'], 99);
check('two lines of one product share the shelf: S 2 + M capped at 8 (not 10), and the cart shows no problem', $sa1['ok'] && $sa2['ok'] && $sa2['qty'] === 8 && $sa2['clamped'] && CartService::units() === 10 && !CartService::summary()['has_problems']);
Database::execute('UPDATE shop_products SET stock_qty = 6 WHERE id = ?', [$pSh]);
$shLines = CartService::lines();
check('the shelf shrank to 6: S keeps 2, M is lowered to 4 and flagged "reduced" (the cart says which line), checkout is held back', $shLines[0]['qty'] === 2 && $shLines[1]['qty'] === 4 && $shLines[1]['problem'] === 'reduced' && CartService::summary()['has_problems']);
$shOrder = $zzOrder(CartService::purchasable($shLines), 'pickup', 'cod');
check('...and the lowered cart can be ordered (nothing is over-sold)', !empty($shOrder['ok']) && $zzStock($pSh) === 0);
CartService::clear();

// -- revenue, refunds of cancelled orders
$pRv = $zzProduct(['price' => '12000.00', 'name' => 'ZZ Revenue']);
$rev0 = ShopStats::summary()['revenue'];
$day0 = array_sum(array_column(ShopStats::byDay(1), 'revenue'));
$rv = $zzOrder([$zzLine($pRv, 1)], 'pickup', 'cod')['order'];
PaymentService::markPaid((int) $rv['id'], ['method' => 'cod', 'amount' => 1200000, 'currency' => 'UGX', 'source' => 'staff']);
$rev1 = ShopStats::summary()['revenue'];
$day1 = array_sum(array_column(ShopStats::byDay(1), 'revenue'));
OrderService::setStatus((int) $rv['id'], 'cancelled', ['notify' => false]);
check('revenue counts a paid order and drops it again when the order is cancelled (the money waits for a refund) - the summary AND the daily series', $rev1 - $rev0 === 1200000 && ShopStats::summary()['revenue'] === $rev0 && $day1 - $day0 === 1200000 && array_sum(array_column(ShopStats::byDay(1), 'revenue')) === $day0);
check('cancelled + paid: 12,000 is refundable and staff can record the refund (and only once)', PaymentService::refundableMinor(OrderService::find((int) $rv['id'])) === 1200000 && PaymentService::staffSetStatus((int) $rv['id'], 'refunded')['ok']
    && (PaymentService::staffSetStatus((int) $rv['id'], 'refunded')['error'] ?? '') === 'same' && OrderService::find((int) $rv['id'])['payment_status'] === 'refunded');
$nr = $zzOrder([$zzLine($pRv, 1)], 'pickup', 'cod')['order'];
OrderService::setStatus((int) $nr['id'], 'cancelled', ['notify' => false]);
check('a cancelled order with no money recorded has nothing to refund (and says so)', (PaymentService::staffSetStatus((int) $nr['id'], 'refunded')['error'] ?? '') === 'nothing_to_refund' && PaymentService::refundableMinor(OrderService::find((int) $nr['id'])) === 0);
$pp2 = $zzOrder([$zzLine($pRv, 1)], 'pickup', 'momo')['order'];
PaymentService::markPaid((int) $pp2['id'], ['method' => 'momo', 'reference' => 'ZZ-PART-1', 'amount' => 400000, 'currency' => 'UGX', 'source' => 'staff']);
OrderService::setStatus((int) $pp2['id'], 'cancelled', ['notify' => false]);
check('part payment then cancel: the part is refundable, and the refund can be recorded', PaymentService::refundableMinor(OrderService::find((int) $pp2['id'])) === 400000 && PaymentService::staffSetStatus((int) $pp2['id'], 'refunded')['ok']);
check('stats header and the revenue footnote agree: Money::MAX is the DECIMAL(12,2) maximum', Money::MAX === 999999999999 && Money::MAX_PRICE * 2970 + 99999999999 < Money::MAX);

// -- the reservation clock ends when the order moves on
$hc = $zzOrder([$zzLine($pRv, 1)], 'pickup', 'cod')['order'];
OrderService::setStatus((int) $hc['id'], 'confirmed', ['notify' => false]);
check('confirming an order through the status form clears its hold (no "stock held until <past date>")', Database::value('SELECT hold_until FROM shop_orders WHERE id = ?', [$hc['id']]) === null);

// -- admin search finds a phone the way people write it
$phOrder = $zzOrder([$zzLine($pRv, 1)], 'pickup', 'cod', ['phone' => '0772 123 456'])['order'];
$found = static fn(string $q): bool => in_array((int) $phOrder['id'], array_map('intval', array_column(OrderService::search(['q' => $q])['items'], 'id')), true);
check('order search: 0772123456, 0772 123 456, 077-212-3456, +256772123456 and 772123456 all find the customer', $found('0772123456') && $found('0772 123 456') && $found('077-212-3456') && $found('+256772123456') && $found('772123456') && !$found('0772999999'));

// -- references the customer typed, and the staff e-mail
$zzSet('shop_notify_emails', 'zz-shop-staff@example.test');
$mm = $zzOrder([$zzLine($pRv, 1)], 'pickup', 'momo')['order'];
$fm0 = count(glob(STORAGE_PATH . '/mail/*.eml') ?: []);
PaymentService::recordCustomerReference((int) $mm['id'], 'MP5550001');
$fm1 = count(glob(STORAGE_PATH . '/mail/*.eml') ?: []);
PaymentService::recordCustomerReference((int) $mm['id'], 'MP5550001');
$fm2 = count(glob(STORAGE_PATH . '/mail/*.eml') ?: []);
check('the same transaction ID sent twice e-mails staff ONCE', $fm1 === $fm0 + 1 && $fm2 === $fm1);
PaymentService::markPaid((int) $mm['id'], ['method' => 'momo', 'reference' => 'STAFF-REF-77', 'amount' => PaymentService::balanceMinor(OrderService::find((int) $mm['id'])), 'currency' => 'UGX', 'source' => 'staff']);
check('paid under another reference: the customer-typed row is closed ("superseded"), nothing stays "pending"', Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ? AND status = 'pending'", [$mm['id']]) == 0
    && Database::value("SELECT status FROM shop_payments WHERE order_id = ? AND reference = 'MP5550001'", [$mm['id']]) === 'superseded' && OrderService::find((int) $mm['id'])['payment_status'] === 'paid');

// -- e-mail wording
$zzSet('shop_pickup_address', 'Zz Office, Gulu');
$zzSet('shop_pickup_hours', 'Mon-Fri 9-5');
$kp = $zzOrder([$zzLine($pRv, 1)], 'pickup', 'cod')['order'];
$ready = ShopMailer::render('status', $kp, [], 'ready');
check('the "ready for pickup" mail names the address and the hours (the tokens inside the status line are expanded)', str_contains($ready['text'], 'Collect it from: Zz Office, Gulu Mon-Fri 9-5') && !str_contains($ready['text'], '{pickup'));
$dl = $zzOrder([$zzLine($pRv, 1)], 'delivery', 'cod', ['address' => 'Plot 9, Zz Road, Gulu'])['order'];
$dm = ShopMailer::render('order', $dl, OrderService::items((int) $dl['id']));
check('an order to be DELIVERED says where it will be delivered, not "Collection"', str_contains($dm['text'], 'Plot 9, Zz Road, Gulu') && !str_contains($dm['text'], 'Collection:'));
$evilName = array_merge($dl, ['customer_name' => 'URGENT: claim your prize at http://evil.example/claim now']);
$em = ShopMailer::render('order', $evilName, [])['text'];
$onlyUrl = ShopMailer::render('order', array_merge($dl, ['customer_name' => 'https://evil.example/x']), [])['text'];
check('a name typed into the checkout never puts a link into the e-mail (letters only; no usable name -> "there")', !str_contains($em, 'evil.example') && !str_contains($em, 'claim your prize') && str_contains($onlyUrl, 'Hello there') && !str_contains($onlyUrl, 'evil.example'));
$zzMailReset();
\App\Core\RateLimit::clear('shop-mail:' . sha1('zz-shop-cap@example.test'));
$capFiles0 = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
for ($i = 0; $i < 5; $i++) {
    $cp = $zzOrder([$zzLine($pRv, 1)], 'pickup', 'cod', ['email' => 'zz-shop-cap@example.test'])['order'];
    ShopMailer::orderPlaced($cp, OrderService::items((int) $cp['id']));
}
$toCap = 0;
foreach (array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $capFiles0) as $f) {
    if (preg_match('/^To:\s*(.+)$/mi', (string) file_get_contents($f), $mt) === 1 && str_contains($mt[1], 'zz-shop-cap@example.test')) {
        $toCap++;
    }
}
check('"order received" mails to one unverified address are capped (3 a day), the orders themselves are all placed', $toCap === 3 && (int) Database::value("SELECT COUNT(*) FROM shop_orders WHERE customer_email = 'zz-shop-cap@example.test'", [], 0) === 5);
\App\Core\RateLimit::clear('shop-mail:' . sha1('zz-shop-cap@example.test'));
\App\Core\RateLimit::clear('shop-mail-all');

// -- the log never keeps a secret link
\App\Core\Log::info('zz-shop log test', ['path' => '/shop/order/' . str_repeat('ab12', 10) . '/reference', 'uri' => '/shop/pay/return/' . str_repeat('cd34', 8) . '?x=1']);
$logTail = substr((string) @file_get_contents(STORAGE_PATH . '/logs/app-' . date('Y-m-d') . '.log'), -3000);
check('private order links and payment-return keys are redacted in the application log', str_contains($logTail, '/shop/order/[link]/reference') && str_contains($logTail, '/shop/pay/return/[link]') && !str_contains($logTail, str_repeat('ab12', 10)) && !str_contains($logTail, str_repeat('cd34', 8)));

// -- the editable wording that was dead is gone, and what remains is used
$regDead = ['catalogue.badge_featured', 'catalogue.book_label', 'product.close_label', 'product.choose_label', 'product.not_found_title', 'product.not_found_text', 'order.not_found_title', 'order.not_found_text',
    'cart.th_price', 'cart.err_qty', 'checkout.err_throttle', 'email.staff_paid_subject', 'email.staff_paid_body'];
$regKeys = [];
foreach (require APP_PATH . '/config/pages/shop.php' as $k => $v) {
    if ($k === 'sections') {
        foreach ($v as $sec) {
            foreach ($sec['fields'] as $fld) {
                $regKeys[] = $sec['key'] . '.' . $fld['key'];
            }
        }
    }
}
check('the 13 registry fields nothing ever read were removed from Admin > Pages > Shop', array_intersect($regDead, $regKeys) === []);
$srcAll = '';
foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(APP_PATH, \FilesystemIterator::SKIP_DOTS)) as $fi) {
    if ($fi->isFile() && $fi->getExtension() === 'php' && !str_ends_with(str_replace('\\', '/', $fi->getPathname()), 'config/pages/shop.php')) {
        $srcAll .= (string) file_get_contents($fi->getPathname()) . "\n";
    }
}
foreach (['assets/js/shop.js'] as $jsf) {
    $srcAll .= (string) file_get_contents($zzRoot . '/' . $jsf);
}
// fields read through a computed key ('status.' . $s, 'cart.err_' . $code, 'email.' . $kind ...) are matched by their section prefix; everything else must be named somewhere in the code
$dynamic = ['product.in_stock', 'product.low_stock', 'product.out_of_stock', 'product.made_to_order', 'status.', 'pay_status.', 'payment.', 'email.', 'cart.err_', 'catalogue.sort_', 'order.ref_label_', 'order.return_', 'order.label_', 'checkout.delivery_', 'policy.', 'order.title', 'checkout.err_', 'trust.', 'faq.', 'fundraising.', 'hero.', 'closed.'];
$unused = [];
foreach ($regKeys as $rk) {
    $isDyn = false;
    foreach ($dynamic as $d) {
        if (str_starts_with($rk, $d)) {
            $isDyn = true;
        }
    }
    if (!$isDyn && !str_contains($srcAll, "'" . $rk . "'") && !str_contains($srcAll, '"' . $rk . '"')) {
        $unused[] = $rk;
    }
}
check('every editable shop wording field is read somewhere in the code (no dead fields)', $unused === [], implode(', ', $unused));

$zzHttp = require __DIR__ . '/selftest-shop-http.php';

// ---- clean up
$zzFinish();
check('clean-up: no test product, order or user is left behind and the shop settings are back as they were',
    (int) Database::value("SELECT COUNT(*) FROM shop_products WHERE slug LIKE 'zz-shop-%'", [], 0) === 0 && (int) Database::value("SELECT COUNT(*) FROM shop_orders WHERE customer_email LIKE 'zz-shop-%@example.test'", [], 0) === 0
    && (int) Database::value("SELECT COUNT(*) FROM users WHERE email LIKE 'zz-shop-%@example.test'", [], 0) === 0 && (int) Database::value("SELECT COUNT(*) FROM settings WHERE setting_key = 'shop_order_prefix' AND setting_value = 'ZZT'", [], 0) === 0);

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL SHOP CHECKS PASSED\n" : "\n{$fail} SHOP CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
