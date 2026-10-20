<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\Shop\Money;
use App\Services\Shop\OnlinePayment;
use App\Services\Shop\OrderService;
use App\Services\Shop\PaymentMethods;
use App\Services\Shop\PaymentService;
use App\Services\Shop\ShopExport;
use App\Services\Shop\ShopMailer;

/**
 * ADMIN SHOP: orders (list, detail, status, confirm a booking, record / verify payments, notes, resend e-mail, packing slip, receipt, CSV, delete).
 * OWNER: ADMIN agent (views in app/views/admin/shop/orders/). Contract: docs/SHOP.md. Ability shop.orders (+ shop.export for the CSV, + shop.delete for delete).
 *
 * The actions are thin wrappers over OrderService / PaymentService with audit entries that carry the order NUMBER only. Rules the screens respect: a "Mark paid" form needs via (the method key), reference (optional)
 * and amount (default = balance); statuses offered come from OrderService::allowedNext(); money texts through Money::format; customer data only here, never in the audit log.
 */
final class ShopOrdersController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('shop.orders');
        $filters = $this->filters($request);
        $page = OrderService::search($filters, $request->page(), $this->perPage());
        return $this->render('admin/shop/orders/index', ['page' => $page, 'filters' => $filters], ['title' => 'Orders', 'nav' => 'shop_orders', 'breadcrumbs' => [['Shop', url('admin/shop')], ['Orders', null]]]);
    }

    public function show(Request $request, int $id): Response
    {
        $this->authorize('shop.orders');
        $order = $this->order($id);
        return $this->render('admin/shop/orders/show', [
            'order' => $order, 'items' => OrderService::items($id), 'ledger' => PaymentService::ledger($id), 'log' => OrderService::log($id),
            'next' => OrderService::allowedNext((string) $order['status']), 'balance' => PaymentService::balanceMinor($order), 'methods' => PaymentMethods::all(),
            'refundable' => PaymentService::refundableMinor($order), 'open_attempt' => OnlinePayment::hasOpenAttempt($id),
        ], ['title' => 'Order ' . $order['order_number'], 'nav' => 'shop_orders', 'breadcrumbs' => [['Shop', url('admin/shop')], ['Orders', url('admin/shop/orders')], [(string) $order['order_number'], null]]]);
    }

    public function status(Request $request, int $id): Response
    {
        $this->authorize('shop.orders');
        $order = $this->order($id);
        $r = OrderService::setStatus($id, $request->string('status'), ['user_id' => $this->userId(), 'note' => $request->string('note'), 'allow_unpaid' => $request->bool('allow_unpaid')]);
        return $this->done($request, $r['ok'], $order, 'shop.order.status', 'Status changed.', $this->error($r['error'] ?? ''), ['to' => $request->string('status')]);
    }

    public function confirm(Request $request, int $id): Response
    {
        $this->authorize('shop.orders');
        $order = $this->order($id);
        $r = OrderService::confirmBooking($id, $this->userId());
        return $this->done($request, $r['ok'], $order, 'shop.order.confirm', 'Booking confirmed.', $this->error($r['error'] ?? ''));
    }

    public function payment(Request $request, int $id): Response
    {
        $this->authorize('shop.orders');
        $order = $this->order($id);
        // the form field is called "via": a field named "method" would shadow form.method in the admin scripts
        $picked = $request->string('via') !== '' ? $request->string('via') : $request->string('method');
        $method = PaymentMethods::isKey($picked) ? $picked : (string) $order['payment_method'];
        $amountText = $request->string('amount');
        $amount = $amountText === '' ? PaymentService::balanceMinor($order) : Money::parse($amountText);
        if ($amount === null || $amount <= 0) {
            Flash::error('Enter the amount received as a number, for example 35000.');
            return $this->back('admin/shop/orders/' . $id);
        }
        $r = PaymentService::markPaid($id, ['method' => $method, 'reference' => $request->string('reference'), 'amount' => Money::roundTo($amount, (string) $order['currency']),
            'currency' => (string) $order['currency'], 'source' => 'staff', 'user_id' => $this->userId(), 'meta' => 'Recorded by staff']);
        $ok = !empty($r['ok']);
        return $this->done($request, $ok, $order, 'shop.order.payment', !empty($r['duplicate']) ? 'That payment reference was already recorded.' : (!empty($r['partial']) ? 'Part payment recorded.' : 'Payment recorded.'), $this->error($r['error'] ?? 'review'));
    }

    public function paymentStatus(Request $request, int $id): Response
    {
        $this->authorize('shop.orders');
        $order = $this->order($id);
        $r = PaymentService::staffSetStatus($id, $request->string('payment_status'), $this->userId(), $request->string('note'));
        return $this->done($request, $r['ok'], $order, 'shop.order.payment_status', 'Payment status changed.', $this->error($r['error'] ?? ''), ['to' => $request->string('payment_status')]);
    }

    public function recheck(Request $request, int $id): Response
    {
        $this->authorize('shop.orders');
        $order = $this->order($id);
        $r = OnlinePayment::recheck($id);
        $ok = !in_array($r['state'], ['verify_failed', 'no_attempt', 'unknown_order'], true);
        return $this->done($request, $ok, $order, 'shop.order.recheck', 'Checked with the payment gateway: ' . $r['state'] . '.', 'The gateway could not be checked (' . $r['state'] . ').');
    }

    public function note(Request $request, int $id): Response
    {
        $this->authorize('shop.orders');
        $order = $this->order($id);
        $ok = OrderService::addNote($id, $request->string('note'), $this->userId(), (string) ($this->user()['name'] ?? ''));
        return $this->done($request, $ok, $order, 'shop.order.note', 'Note added.', 'Type a note first.');
    }

    public function resend(Request $request, int $id): Response
    {
        $this->authorize('shop.orders');
        $order = $this->order($id);
        $ok = ShopMailer::resendCustomer($order);
        return $this->done($request, $ok, $order, 'shop.order.resend', 'E-mail sent to the customer.', 'The e-mail could not be sent.');
    }

    public function packingSlip(Request $request, int $id): Response
    {
        $this->authorize('shop.orders');
        $order = $this->order($id);
        return $this->render('admin/shop/orders/packing-slip', ['order' => $order, 'items' => OrderService::items($id)], ['title' => 'Packing slip ' . $order['order_number'], 'nav' => 'shop_orders']);
    }

    public function receipt(Request $request, int $id): Response
    {
        $this->authorize('shop.orders');
        $order = $this->order($id);
        return $this->render('admin/shop/orders/receipt', ['order' => $order, 'items' => OrderService::items($id), 'ledger' => PaymentService::ledger($id)], ['title' => 'Receipt ' . $order['order_number'], 'nav' => 'shop_orders']);
    }

    public function export(Request $request): Response
    {
        $this->authorize('shop.orders', 'shop.export');
        $filters = $this->filters($request);
        $kind = $request->string('kind') === 'items' ? 'items' : 'orders';
        // names of the filters that were used, never their values: the search box takes a customer's name, e-mail or phone
        $used = array_keys(array_filter($filters, static fn($v): bool => $v !== '' && $v !== null && $v !== false));
        $this->audit('shop.export', 'shop', $kind, 'Exported shop ' . $kind . ' as CSV', ['filters' => $used]);
        return ShopExport::response($kind, $filters);
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('shop.orders', 'shop.delete');
        $order = $this->order($id);
        OrderService::delete($id);
        $this->audit('shop.order.delete', 'order', $order['order_number'], 'Deleted order ' . $order['order_number']);
        Flash::success('Order ' . $order['order_number'] . ' deleted.');
        return $this->redirect('admin/shop/orders');
    }

    // ------------------------------------------------------------------------------------------------ helpers

    /** @return array<string,mixed> */
    private function order(int $id): array
    {
        return OrderService::find($id) ?? throw new HttpException(404, 'Not found');
    }

    /** @return array<string,mixed> */
    private function filters(Request $request): array
    {
        return ['status' => $request->string('status'), 'payment_status' => $request->string('payment_status'), 'method' => $request->string('paid_by') !== '' ? $request->string('paid_by') : $request->string('method'),     // the list form calls it paid_by: a field named "method" shadows form.method in the admin scripts
            'from' => $request->string('from'), 'to' => $request->string('to'), 'q' => $request->string('q'), 'needs_action' => $request->bool('needs_action')];
    }

    /** One place for audit + flash + redirect. Audit details: the order NUMBER and the action only (never customer data). @param array<string,mixed> $order */
    private function done(Request $request, bool $ok, array $order, string $action, string $success, string $failure, array $details = []): Response
    {
        if ($ok) {
            $this->audit($action, 'order', $order['order_number'], $success . ' (' . $order['order_number'] . ')', $details);
            Flash::success($success);
        } else {
            Flash::error($failure);
        }
        if ($request->expectsJson()) {
            return $this->json(['ok' => $ok, 'message' => $ok ? $success : $failure], $ok ? 200 : 422);
        }
        return $this->redirect('admin/shop/orders/' . $order['id']);
    }

    private function error(string $code): string
    {
        return match ($code) {
            'transition'        => 'That change is not allowed from the current status.',
            'not_paid'          => 'Record the payment first (the order must be paid).',
            'same'              => 'Nothing to change.',
            'closed'            => 'This order is cancelled or refunded: payments cannot be recorded.',
            'already_refunded'  => 'This order was refunded.',
            'nothing_to_refund' => 'No money is recorded for this order, so there is nothing to refund. If a payment is still missing, check it with the gateway first.',
            'invalid_amount'    => 'Enter the amount received.',
            'reference_used'    => 'That reference was already used to pay another order, so nothing was recorded. Check it in your statement. If one transfer really paid for two orders, add a letter to the reference, for example MP-123-b.',
            'currency'          => 'The currency does not match the order.',
            'review'            => 'The payment was flagged for review instead of being recorded.',
            'not_found'         => 'That order no longer exists.',
            default             => 'The change could not be made.',
        };
    }
}
