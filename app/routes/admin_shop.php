<?php
/**
 * ADMIN SHOP routes. Contract: docs/SHOP.md section 8. Everything is inside the `auth` group; every route also names its ability and every controller
 * method ALSO calls $this->authorize() (defence in depth). CSRF is automatic on POST; JSON clients send X-CSRF-TOKEN and "Accept: application/json".
 *
 * Abilities: shop.manage (products, categories, pictures) | shop.orders (orders, payments, notes, packing slips) | shop.settings (shop, delivery and PAYMENT
 * settings: holds the gateway secrets, admin only by default) | shop.export (CSV) | shop.delete (delete products and orders, always IN ADDITION to the group's ability).
 * Owners: ADMIN agent = ShopStatsController, ShopProductsController, ShopImagesController, ShopCategoriesController, ShopOrdersController;
 *         PAYMENTS agent = ShopSettingsController.
 *
 * Overview (shop.manage OR shop.orders: the controller checks)
 *   GET  /admin/shop                                    sales summary cards, low-stock alerts, newest orders
 *   GET  /admin/api/shop/stats                          JSON: App\Services\Shop\ShopStats::summary() (shop.orders)
 * Products (shop.manage)
 *   GET  /admin/shop/products                           list (search, category, status, stock filters, quick toggles)
 *   GET  /admin/shop/products/create     POST /admin/shop/products          create
 *   GET  /admin/shop/products/{id}/edit  POST /admin/shop/products/{id}    update
 *   POST /admin/shop/products/{id}/duplicate | /toggle (field = featured|status|booking|stock) | /delete (+ shop.delete)
 * Pictures (shop.manage; JSON)
 *   POST /admin/shop/products/{id}/images               multipart file + crop + alt -> {"ok":true,"image":{...}} | 422 {"ok":false,"error":"..."}
 *   POST /admin/shop/products/{id}/images/reorder       {"ids":[3,1,2]}
 *   POST /admin/shop/products/{id}/images/{iid}         {"alt":"...","cover":true}      POST .../{iid}/delete
 * Categories (shop.manage)
 *   GET  /admin/shop/categories   POST /admin/shop/categories   POST /admin/shop/categories/{id}   POST /admin/shop/categories/{id}/delete
 * Orders (shop.orders)
 *   GET  /admin/shop/orders                             list (status / payment / method / date filters, search by number, name, e-mail, phone)
 *   GET  /admin/shop/orders/export                      CSV (+ shop.export)   ?kind=orders|items and the list filters
 *   GET  /admin/shop/orders/{id}                        detail: items, totals, customer, delivery, payment ledger, notes, timeline, actions
 *   POST /admin/shop/orders/{id}/status                 status (OrderService::setStatus)
 *   POST /admin/shop/orders/{id}/confirm                confirm a booking
 *   POST /admin/shop/orders/{id}/payment                mark paid: method, reference, amount (PaymentService::markPaid, source staff)
 *   POST /admin/shop/orders/{id}/payment-status         unpaid | failed | pending (PaymentService::staffSetStatus)
 *   POST /admin/shop/orders/{id}/recheck                ask Flutterwave again about the latest online payment (OnlinePayment::recheck)
 *   POST /admin/shop/orders/{id}/note                   add an internal note       POST /admin/shop/orders/{id}/resend   resend the customer e-mail
 *   GET  /admin/shop/orders/{id}/packing-slip | /receipt   printable pages
 *   POST /admin/shop/orders/{id}/delete                 (+ shop.delete)
 * Settings (shop.settings)
 *   GET/POST /admin/shop/settings                       open / closed, currency, delivery, e-mail recipients, policy texts, holds, order prefix
 *   GET/POST /admin/shop/payments                       payment methods, labels, instructions, numbers, bank details, Flutterwave keys, mode, options
 *   POST /admin/shop/payments/test-connection           JSON: FlutterwaveClient::testConnection()
 *   POST /admin/shop/payments/clear-secret              forget a stored secret (name = flw_secret_key | flw_secret_hash)
 */

use App\Controllers\Admin\ShopCategoriesController;
use App\Controllers\Admin\ShopImagesController;
use App\Controllers\Admin\ShopOrdersController;
use App\Controllers\Admin\ShopProductsController;
use App\Controllers\Admin\ShopSettingsController;
use App\Controllers\Admin\ShopStatsController;

$router->group(['prefix' => 'admin', 'middleware' => ['auth']], function ($router) {
    // ------------------------------------------------------------------------------------------------ overview: shop.manage or shop.orders (controller checks)
    $router->get('/shop', [ShopStatsController::class, 'index'])->name('admin.shop');

    // ------------------------------------------------------------------------------------------------ products, pictures, categories: shop.manage
    $router->group(['middleware' => ['can:shop.manage']], function ($router) {
        $router->get('/shop/products', [ShopProductsController::class, 'index'])->name('admin.shop.products');
        $router->get('/shop/products/create', [ShopProductsController::class, 'create'])->name('admin.shop.products.create');
        $router->post('/shop/products', [ShopProductsController::class, 'store'])->name('admin.shop.products.store');
        $router->get('/shop/products/{id}/edit', [ShopProductsController::class, 'edit'])->name('admin.shop.products.edit');
        $router->post('/shop/products/{id}', [ShopProductsController::class, 'update'])->name('admin.shop.products.update');
        $router->post('/shop/products/{id}/duplicate', [ShopProductsController::class, 'duplicate'])->name('admin.shop.products.duplicate');
        $router->post('/shop/products/{id}/toggle', [ShopProductsController::class, 'toggle'])->name('admin.shop.products.toggle');

        $router->post('/shop/products/{id}/images', [ShopImagesController::class, 'upload'])->middleware('throttle:60,60')->name('admin.shop.images.upload');
        $router->post('/shop/products/{id}/images/reorder', [ShopImagesController::class, 'reorder'])->name('admin.shop.images.reorder');
        $router->post('/shop/products/{id}/images/{iid}', [ShopImagesController::class, 'update'])->where('iid', '\d+')->name('admin.shop.images.update');
        $router->post('/shop/products/{id}/images/{iid}/delete', [ShopImagesController::class, 'destroy'])->where('iid', '\d+')->name('admin.shop.images.delete');

        $router->get('/shop/categories', [ShopCategoriesController::class, 'index'])->name('admin.shop.categories');
        $router->post('/shop/categories', [ShopCategoriesController::class, 'store'])->name('admin.shop.categories.store');
        $router->post('/shop/categories/{id}', [ShopCategoriesController::class, 'update'])->name('admin.shop.categories.update');
        $router->post('/shop/categories/{id}/delete', [ShopCategoriesController::class, 'destroy'])->name('admin.shop.categories.delete');

        // deleting a product needs shop.delete as well
        $router->group(['middleware' => ['can:shop.delete']], function ($router) {
            $router->post('/shop/products/{id}/delete', [ShopProductsController::class, 'destroy'])->name('admin.shop.products.delete');
        });
    });

    // ------------------------------------------------------------------------------------------------ orders: shop.orders
    $router->group(['middleware' => ['can:shop.orders']], function ($router) {
        $router->get('/api/shop/stats', [ShopStatsController::class, 'statsJson'])->name('admin.api.shop.stats');
        $router->get('/shop/orders', [ShopOrdersController::class, 'index'])->name('admin.shop.orders');
        $router->get('/shop/orders/{id}', [ShopOrdersController::class, 'show'])->name('admin.shop.orders.show');
        $router->post('/shop/orders/{id}/status', [ShopOrdersController::class, 'status'])->name('admin.shop.orders.status');
        $router->post('/shop/orders/{id}/confirm', [ShopOrdersController::class, 'confirm'])->name('admin.shop.orders.confirm');
        $router->post('/shop/orders/{id}/payment', [ShopOrdersController::class, 'payment'])->name('admin.shop.orders.payment');
        $router->post('/shop/orders/{id}/payment-status', [ShopOrdersController::class, 'paymentStatus'])->name('admin.shop.orders.payment_status');
        $router->post('/shop/orders/{id}/recheck', [ShopOrdersController::class, 'recheck'])->middleware('throttle:30,60')->name('admin.shop.orders.recheck');
        $router->post('/shop/orders/{id}/note', [ShopOrdersController::class, 'note'])->name('admin.shop.orders.note');
        $router->post('/shop/orders/{id}/resend', [ShopOrdersController::class, 'resend'])->middleware('throttle:30,60')->name('admin.shop.orders.resend');
        $router->get('/shop/orders/{id}/packing-slip', [ShopOrdersController::class, 'packingSlip'])->name('admin.shop.orders.packing');
        $router->get('/shop/orders/{id}/receipt', [ShopOrdersController::class, 'receipt'])->name('admin.shop.orders.receipt');

        // CSV export: shop.export as well (static segment: registered before /shop/orders/{id} automatically)
        $router->group(['middleware' => ['can:shop.export']], function ($router) {
            $router->get('/shop/orders/export', [ShopOrdersController::class, 'export'])->middleware('throttle:20,60')->name('admin.shop.orders.export');
        });
        // deleting an order: shop.delete as well
        $router->group(['middleware' => ['can:shop.delete']], function ($router) {
            $router->post('/shop/orders/{id}/delete', [ShopOrdersController::class, 'destroy'])->name('admin.shop.orders.delete');
        });
    });

    // ------------------------------------------------------------------------------------------------ settings and payments: shop.settings (holds secrets)
    $router->group(['middleware' => ['can:shop.settings']], function ($router) {
        $router->get('/shop/settings', [ShopSettingsController::class, 'settings'])->name('admin.shop.settings');
        $router->post('/shop/settings', [ShopSettingsController::class, 'saveSettings'])->name('admin.shop.settings.save');
        $router->get('/shop/payments', [ShopSettingsController::class, 'payments'])->name('admin.shop.payments');
        $router->post('/shop/payments', [ShopSettingsController::class, 'savePayments'])->name('admin.shop.payments.save');
        $router->post('/shop/payments/test-connection', [ShopSettingsController::class, 'testConnection'])->middleware('throttle:10,60')->name('admin.shop.payments.test');
        $router->post('/shop/payments/clear-secret', [ShopSettingsController::class, 'clearSecret'])->name('admin.shop.payments.clear');
    });
});
