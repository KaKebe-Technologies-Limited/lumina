<?php
declare(strict_types=1);

namespace App\Services\Shop;

use App\Core\Log;
use App\Core\Mailer;
use App\Core\RateLimit;
use App\Core\Settings;

/**
 * E-MAILS of the shop (customer and staff). All wording comes from the page registry (Admin > Pages > Shop > "E-mail wording"); the code only fills in
 * placeholders. Sending goes through Mailer (header-injection safe, never throws). Nothing here is logged with a name, address or amount.
 *
 * Placeholders usable in the e-mail wording:
 *   {name} {first_name} {order_number} {order_url} {total} {subtotal} {delivery_fee} {items} {payment_method} {payment_instructions}
 *   {delivery_method} {delivery_line} {pickup_address} {pickup_hours} {delivery_address} {status} {status_line} {site} {admin_url} {reference}
 * Values are single-line cleaned (no CR/LF/control characters, tags removed); each placeholder is replaced exactly once (a value that itself
 * contains "{name}" stays as typed). {status_line} and {delivery_line} are wording written by the owner in the registry: the tokens INSIDE them
 * ({pickup_address} {pickup_hours} {delivery_address}) are expanded first, so "Collect it from: {pickup_address}" works. {name} / {first_name} are the
 * customer's typed name reduced to letters (see mailName()): the mail goes to an address nobody has proven to own. {items} and {payment_instructions} are multi-line text blocks.
 *
 *   kinds: order (customer: we received your order) | pay (customer: online order started - the link to complete the payment) | paid (customer: payment received) | status (customer: ready / shipped / cancelled ...)
 *          staff_new (staff: new order; for an online order it goes out when the payment is confirmed) | staff_reference (staff: a customer typed a transaction ID)
 */
final class ShopMailer
{
    /**
     * The customer's "we received your order" mail + the staff notice. An online (Flutterwave) order sends only a short "complete your payment" mail with the
     * order link (kind `pay`): a customer who closes the payment page without coming back still has the way to "Pay again". The staff notice and the receipt
     * go out when the payment is confirmed.
     */
    public static function orderPlaced(array $order, array $items): void
    {
        if ($order['payment_method'] === 'flutterwave') {
            self::sendCustomer('pay', $order, $items);
            return;
        }
        self::sendCustomer('order', $order, $items);
        self::sendStaff('staff_new', $order, $items);
    }

    /** Payment confirmed: receipt to the customer; staff are told when the money came from the gateway (they did not record it themselves). */
    public static function paymentReceived(array $order, string $source = 'staff'): void
    {
        $items = OrderService::items((int) $order['id']);
        self::sendCustomer('paid', $order, $items);
        if ($source !== 'staff') {
            self::sendStaff('staff_new', $order, $items);                // only an online payment arrives from the gateway, and its staff notice is the "new order" mail
        }
    }

    public static function statusChanged(array $order, string $to): void
    {
        self::sendCustomer('status', $order, OrderService::items((int) $order['id']), $to);
    }

    public static function referenceSubmitted(array $order): void
    {
        self::sendStaff('staff_reference', $order, OrderService::items((int) $order['id']));
    }

    /** Admin "resend customer e-mail": the order mail while it is new, otherwise the mail for the current status. @return bool sent */
    public static function resendCustomer(array $order): bool
    {
        $items = OrderService::items((int) $order['id']);
        if ($order['status'] === 'pending') {
            return self::sendCustomer($order['payment_status'] === 'paid' ? 'paid' : ($order['payment_method'] === 'flutterwave' ? 'pay' : 'order'), $order, $items, null, false);
        }
        return self::sendCustomer('status', $order, $items, (string) $order['status'], false);
    }

    /**
     * Build a message without sending it.
     * @param array<string,mixed> $order
     * @param list<array<string,mixed>> $items
     * @return array{subject:string,text:string,html:string}
     */
    public static function render(string $kind, array $order, array $items, ?string $status = null): array
    {
        $vars = self::vars($order, $items, $status ?? (string) $order['status']);
        $get = static fn(string $key): string => ShopSettings::registryText('email.' . $key);
        $subjectKey = $kind === 'status' ? 'status_subject' : $kind . '_subject';
        $bodyKey = $kind === 'status' ? 'status_body' : $kind . '_body';
        $subject = self::fill($get($subjectKey), $vars, true);
        $body = self::fill($get($bodyKey), $vars, false);
        $footer = self::fill($get('footer'), $vars, false);
        $text = rtrim($body) . ($footer !== '' ? "\n\n-- \n" . $footer : '') . "\n";
        $paras = array_filter(array_map('trim', preg_split('/\n{2,}/', $body) ?: []), static fn(string $p): bool => $p !== '');
        $html = '<div style="font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.55;color:#1D1E20;max-width:620px;margin:0 auto;padding:16px">';
        foreach ($paras as $p) {
            $html .= '<p style="margin:0 0 14px">' . nl2br(htmlspecialchars($p, ENT_QUOTES, 'UTF-8')) . '</p>';
        }
        if ($footer !== '') {
            $html .= '<hr style="border:0;border-top:1px solid #ddd;margin:20px 0"><p style="margin:0;color:#5A6470;font-size:13px">' . nl2br(htmlspecialchars($footer, ENT_QUOTES, 'UTF-8')) . '</p>';
        }
        $html .= '</div>';
        return ['subject' => $subject, 'text' => $text, 'html' => $html];
    }

    // ------------------------------------------------------------------------------------------------ internals

    private static function sendCustomer(string $kind, array $order, array $items, ?string $status = null, bool $capped = true): bool
    {
        $to = (string) $order['customer_email'];
        if (!Mailer::validEmail($to)) {
            return false;
        }
        // "we received your order" goes to an address nobody has proven to own (the checkout does not verify it): cap what one address and the whole site can be sent,
        // the same way the event confirmations do. The order itself is placed normally; the later mails follow real actions (a payment, staff changing the status).
        if ($capped && in_array($kind, ['order', 'pay'], true) && (!RateLimit::attempt('shop-mail:' . sha1(strtolower($to)), 3, 86400) || !RateLimit::attempt('shop-mail-all', 120, 3600))) {
            Log::warning('Shop order e-mail skipped (send cap reached)', ['order' => $order['order_number'] ?? '']);
            OrderService::addLog((int) $order['id'], 'email', 'The order e-mail to the customer was not sent (too many e-mails to this address today).');
            return false;
        }
        $m = self::render($kind, $order, $items, $status);
        if (trim($m['subject']) === '') {
            return false;
        }
        $opts = ['html' => $m['html']];
        $reply = trim((string) Settings::get('email_primary', ''));
        if ($reply !== '' && Mailer::validEmail($reply)) {
            $opts['reply_to'] = $reply;
        }
        $ok = Mailer::send($to, $m['subject'], $m['text'], $opts);
        OrderService::addLog((int) $order['id'], 'email', $ok ? 'E-mail sent to the customer (' . $kind . ').' : 'E-mail to the customer could not be sent (' . $kind . ').');
        return $ok;
    }

    private static function sendStaff(string $kind, array $order, array $items): bool
    {
        $m = self::render($kind, $order, $items);
        if (trim($m['subject']) === '') {
            return false;
        }
        $reply = Mailer::validEmail((string) $order['customer_email']) ? (string) $order['customer_email'] : null;
        $to = ShopSettings::notifyEmails();
        if ($to === []) {
            return Mailer::notify($m['subject'], $m['text'], $reply, $m['html']);
        }
        $opts = ['html' => $m['html']];
        if ($reply !== null) {
            $opts['reply_to'] = $reply;
        }
        return Mailer::send($to, $m['subject'], $m['text'], $opts);
    }

    /** @return array<string,string> */
    private static function vars(array $order, array $items, string $status): array
    {
        $cur = (string) $order['currency'];
        $lines = [];
        foreach ($items as $it) {
            $lines[] = '- ' . (int) $it['qty'] . ' x ' . $it['name'] . ((string) ($it['options_text'] ?? '') !== '' ? ' (' . $it['options_text'] . ')' : '') . ' - ' . Money::format(Money::fromDb($it['line_total']), $cur);
        }
        $name = self::mailName((string) $order['customer_name']);
        $pickup = ShopSettings::pickup();
        $addr = trim((string) preg_replace('/\s+/u', ' ', (string) ($order['delivery_address'] ?? '')));
        $tokens = ['{pickup_address}' => $pickup['address'], '{pickup_hours}' => $pickup['hours'], '{delivery_address}' => $addr];
        $deliveryLine = self::expand(ShopSettings::registryText($order['delivery_method'] === 'delivery' ? 'email.line_delivery' : 'email.line_pickup'), $tokens);
        $info = OrderService::paymentInfo($order);
        $payLines = [];
        if ($info['instructions'] !== '') {
            $payLines[] = $info['instructions'];
        }
        foreach ($info['details'] as $d) {
            $payLines[] = isset($d['number']) ? $d['label'] . ($d['name'] !== '' ? ' (' . $d['name'] . ')' : '') . ': ' . $d['number'] : $d['label'] . ': ' . $d['value'];
        }
        if (in_array($order['payment_method'], ['momo', 'bank'], true)) {
            $payLines[] = ShopSettings::registryText('payment.reference_line') !== '' ? str_replace('{order_number}', (string) $order['order_number'], ShopSettings::registryText('payment.reference_line')) : (string) $order['order_number'];
        }
        return [
            '{name}'                 => $name,
            '{first_name}'           => (string) (preg_split('/\s+/', $name)[0] ?? ''),
            '{order_number}'         => (string) $order['order_number'],
            '{order_url}'            => OrderService::absUrl($order),
            '{total}'                => Money::format(Money::fromDb($order['total']), $cur),
            '{subtotal}'             => Money::format(Money::fromDb($order['subtotal']), $cur),
            '{delivery_fee}'         => Money::format(Money::fromDb($order['delivery_fee']), $cur),
            '{items}'                => implode("\n", $lines),
            '{payment_method}'       => PaymentMethods::label((string) $order['payment_method']),
            '{payment_instructions}' => implode("\n", $payLines),
            '{delivery_method}'      => ShopSettings::registryText('checkout.delivery_' . $order['delivery_method']) ?: ucfirst((string) $order['delivery_method']),
            '{delivery_line}'        => $deliveryLine,
            '{pickup_address}'       => $pickup['address'],
            '{pickup_hours}'         => $pickup['hours'],
            '{delivery_address}'     => $addr,
            '{status}'               => OrderService::statusLabel($status),
            '{status_line}'          => self::expand(ShopSettings::registryText('email.status_line_' . $status), $tokens),
            '{site}'                 => (string) Settings::get('site_name', 'Lumina Africa Foundation'),
            '{admin_url}'            => abs_url('admin/shop/orders/' . (int) $order['id']),
            '{reference}'            => (string) ($order['payment_reference'] ?? ''),
        ];
    }

    /**
     * The customer's name as it may appear in a message sent to an address nobody has verified: no links, no line breaks, only letters, digits and a few
     * punctuation marks, shortened. Without any letter it falls back to the editable word (registry email.name_fallback).
     */
    private static function mailName(string $name): string
    {
        $n = (string) preg_replace('~(?:https?://|www\.)\S*~iu', ' ', $name);
        $n = (string) preg_replace("/[^\p{L}\p{M}\p{N} '.-]+/u", ' ', $n);
        $n = trim((string) preg_replace('/\s+/u', ' ', $n));
        $n = mb_substr($n, 0, 40);
        if (preg_match('/\p{L}/u', $n) !== 1) {
            $fallback = ShopSettings::registryText('email.name_fallback');
            return $fallback !== '' ? $fallback : 'there';
        }
        return $n;
    }

    /** Expand the tokens inside an owner-written line (values cleaned like everywhere else); unknown tokens vanish. @param array<string,string> $tokens */
    private static function expand(string $line, array $tokens): string
    {
        if ($line === '') {
            return '';
        }
        $clean = [];
        foreach ($tokens as $k => $v) {
            $clean[$k] = trim((string) preg_replace('/\s+/u', ' ', strip_tags($v)));
        }
        return trim((string) preg_replace('/\s+/u', ' ', (string) preg_replace('/\{[a-z_]+\}/', '', strtr($line, $clean))));
    }

    /**
     * Replace placeholders in one pass. Values lose control characters and tags; $singleLine also removes line breaks (subjects).
     * @param array<string,string> $vars
     */
    private static function fill(string $template, array $vars, bool $singleLine): string
    {
        if ($template === '') {
            return '';
        }
        $clean = [];
        foreach ($vars as $k => $v) {
            $v = str_replace(["\r\n", "\r"], "\n", strip_tags((string) $v));
            $v = (string) preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $v);
            $clean[$k] = $singleLine ? trim((string) preg_replace('/\s+/u', ' ', $v)) : $v;
        }
        $out = strtr($template, $clean);
        $out = (string) preg_replace('/\{[a-z_]+\}/', '', $out);                    // unknown placeholders vanish
        return $singleLine ? trim((string) preg_replace('/\s+/u', ' ', $out)) : trim((string) preg_replace("/\n{3,}/", "\n\n", str_replace("\r", '', $out)));
    }
}
