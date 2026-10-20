<?php
declare(strict_types=1);

namespace App\Services\Shop;

use App\Core\CsvExport;
use App\Core\Database;
use App\Core\Response;

/**
 * CSV export of shop orders (ability shop.export). The cells go through CsvExport::cell(), so a customer who types "=HYPERLINK(...)" as a name or a
 * note cannot plant a formula in the spreadsheet. The file contains PERSONAL DATA: the controller audits "shop.export" (filters and row count only).
 *
 *   ShopExport::response('orders', $filters)   one row per order
 *   ShopExport::response('items', $filters)    one row per ordered item
 * $filters are OrderService::search() filters (status, payment_status, method, from, to, q).
 */
final class ShopExport
{
    public const ORDER_HEADER = ['Order number', 'Date', 'Status', 'Payment status', 'Payment method', 'Name', 'E-mail', 'Phone', 'Delivery', 'Address',
        'Subtotal', 'Delivery fee', 'Total', 'Currency', 'Paid on', 'Payment reference', 'Items', 'Customer note'];
    public const ITEM_HEADER = ['Order number', 'Date', 'Status', 'Payment status', 'Product', 'Options', 'SKU', 'Quantity', 'Unit price', 'Line total', 'Currency'];

    public static function response(string $kind, array $filters = []): Response
    {
        $kind = $kind === 'items' ? 'items' : 'orders';
        $name = 'shop-' . $kind . '-' . date('Y-m-d') . '.csv';
        return CsvExport::response($name, $kind === 'items' ? self::ITEM_HEADER : self::ORDER_HEADER, self::rows($kind, $filters));
    }

    /** @return \Generator<int,list<string>> */
    public static function rows(string $kind, array $filters = []): \Generator
    {
        $chunk = [];
        foreach (OrderService::searchAll($filters) as $o) {
            $chunk[] = $o;
            if (count($chunk) >= 100) {
                yield from self::chunkRows($kind, $chunk);
                $chunk = [];
            }
        }
        if ($chunk !== []) {
            yield from self::chunkRows($kind, $chunk);
        }
    }

    /** @param list<array<string,mixed>> $orders @return \Generator<int,list<string>> */
    private static function chunkRows(string $kind, array $orders): \Generator
    {
        $ids = array_map(static fn(array $o): int => (int) $o['id'], $orders);
        $items = [];
        foreach (Database::all('SELECT * FROM shop_order_items WHERE order_id IN (' . implode(',', $ids) . ') ORDER BY id ASC') as $it) {
            $items[(int) $it['order_id']][] = $it;
        }
        foreach ($orders as $o) {
            $its = $items[(int) $o['id']] ?? [];
            if ($kind === 'items') {
                foreach ($its as $it) {
                    yield [(string) $o['order_number'], self::dt($o['created_at']), (string) $o['status'], (string) $o['payment_status'], (string) $it['name'], (string) ($it['options_text'] ?? ''),
                        (string) ($it['sku'] ?? ''), (string) $it['qty'], (string) $it['unit_price'], (string) $it['line_total'], (string) $o['currency']];
                }
                continue;
            }
            $text = implode('; ', array_map(static fn(array $it): string => (int) $it['qty'] . ' x ' . $it['name'] . ((string) ($it['options_text'] ?? '') !== '' ? ' (' . $it['options_text'] . ')' : ''), $its));
            yield [(string) $o['order_number'], self::dt($o['created_at']), (string) $o['status'], (string) $o['payment_status'], (string) $o['payment_method'], (string) $o['customer_name'],
                (string) $o['customer_email'], (string) $o['customer_phone'], (string) $o['delivery_method'], (string) ($o['delivery_address'] ?? ''), (string) $o['subtotal'],
                (string) $o['delivery_fee'], (string) $o['total'], (string) $o['currency'], self::dt($o['paid_at']), (string) ($o['payment_reference'] ?? ''), $text, (string) ($o['customer_note'] ?? '')];
        }
    }

    private static function dt(mixed $v): string
    {
        return $v === null || $v === '' ? '' : substr((string) $v, 0, 16);
    }
}
