<?php
/**
 * Child process of bin/selftest-shop.php (the "last units" race): tries to buy N units of one product through OrderService::create() and prints
 * one line per try: "OK <order number>" or "FAIL <code>". Usage: php bin/selftest-shop-child.php <productId> <tries> <email>
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\Shop\OrderService;

if (\App\Core\Config::get('app.env') !== 'local') {
    exit("Refusing to run outside APP_ENV=local.\n");
}
$pid = (int) ($argv[1] ?? 0);
$tries = max(1, (int) ($argv[2] ?? 1));
$email = (string) ($argv[3] ?? 'zz-shop-race@example.test');
for ($i = 0; $i < $tries; $i++) {
    $r = OrderService::create(
        ['name' => 'Race Runner', 'email' => $email, 'phone' => '0700123456', 'consent' => true],
        [['key' => 'k' . $i, 'product_id' => $pid, 'selection' => [], 'qty' => 1]],
        'pickup',
        'cod',
        ['notify' => false]
    );
    echo $r['ok'] ? 'OK ' . $r['order']['order_number'] : 'FAIL ' . ($r['error'] ?? '?'), PHP_EOL;
}
