<?php
/**
 * Admin sidebar fragment: SHOP (overview, orders, products, categories, shop settings, payments). Group order 39 (core=10, content=20, org=30, membership=35,
 * calendar=36, newsletter=37, forms=38, inbox=40). The badge on "Orders" is the number of orders that need a person (new, or a payment to review): one cheap
 * COUNT(*), memoised per request (OrderService::newCount; a missing table gives 0). Items follow the abilities: Orders / Overview need shop.orders,
 * Products and Categories shop.manage, Settings and Payments shop.settings (admin only by default). Contract: docs/SHOP.md.
 */

use App\Services\Shop\OrderService;

return [
    'key'   => 'shop',
    'label' => 'Shop',
    'order' => 39,
    'items' => [
        ['key' => 'shop_overview', 'label' => 'Shop overview', 'url' => 'admin/shop', 'icon' => 'bar-chart', 'can' => 'shop.orders', 'order' => 10, 'badge' => null],
        ['key' => 'shop_orders', 'label' => 'Orders', 'url' => 'admin/shop/orders', 'icon' => 'shopping-bag', 'can' => 'shop.orders', 'order' => 20,
         'badge' => static fn(): int => OrderService::newCount()],
        ['key' => 'shop_products', 'label' => 'Products', 'url' => 'admin/shop/products', 'icon' => 'package', 'can' => 'shop.manage', 'order' => 30, 'badge' => null],
        ['key' => 'shop_categories', 'label' => 'Categories', 'url' => 'admin/shop/categories', 'icon' => 'tag', 'can' => 'shop.manage', 'order' => 40, 'badge' => null],
        ['key' => 'shop_settings', 'label' => 'Shop settings', 'url' => 'admin/shop/settings', 'icon' => 'settings', 'can' => 'shop.settings', 'order' => 50, 'badge' => null],
        ['key' => 'shop_payments', 'label' => 'Payments', 'url' => 'admin/shop/payments', 'icon' => 'banknote', 'can' => 'shop.settings', 'order' => 60, 'badge' => null],
    ],
];
