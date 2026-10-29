<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AuditLog;
use App\Core\AdminController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Services\Pesapal\PesapalClient;
use App\Services\Pesapal\PesapalPayments;

/**
 * Admin > Pesapal payments: is the gateway connected, test the keys, and the list of every Pesapal payment (donations and shop orders) with a
 * "Check with Pesapal" button for one that is still waiting. The keys themselves are never shown or stored here: they live in the server .env.
 */
final class PesapalController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('inbox.manage');
        $tables = Database::tableExists('pesapal_payments') && Database::tableExists('pesapal_ipns');
        return $this->render('admin/pesapal/index', [
            'tables'    => $tables,
            'donationsOn' => PesapalPayments::donationsSwitch(),
            'donateMethod' => PesapalPayments::donateMethod(),
            'shopOn'    => \App\Services\Shop\ShopSettings::bool('shop_pay_pesapal_enabled'),
            'shopOpen'  => \App\Services\Shop\ShopSettings::bool('shop_open'),
            'canShop'   => can('shop.settings'),
            'fees'      => $this->feeValues(),
            'feeCurrencies' => \App\Services\Academy\AcademyFees::CURRENCIES,
            'feesReady' => \App\Services\Academy\AcademyFees::ready(),
            'canFees'   => can('academy.manage'),
            'hasKeys'   => PesapalClient::hasKeys(),
            'mode'      => PesapalClient::mode(),
            'payments'  => $tables ? PesapalPayments::recent(100) : [],
            'ipnUrl'    => abs_url('pay/pesapal/ipn'),
            'returnUrl' => abs_url('pay/pesapal/return'),
        ], ['title' => 'Pesapal payments', 'nav' => 'pesapal', 'breadcrumbs' => [['Pesapal payments', null]]]);
    }

    /** Where on the website Pesapal is offered: tick the places. Shop needs the shop.settings ability (it is also set in Shop > Payments). */
    public function options(Request $request): Response
    {
        $this->authorize('inbox.manage');
        // the tick box and Settings > Donations > "How online donations are taken" are the same choice: ticked = Pesapal is offered (kept as "only Pesapal" if that was chosen), unticked = the link only
        $method = PesapalPayments::donateMethod();
        $changes = ['donate_method' => $request->bool('donations') ? ($method === 'link' ? 'both' : $method) : 'link'];
        if (can('shop.settings')) {
            $changes['shop_pay_pesapal_enabled'] = $request->bool('shop') ? '1' : '0';
        }
        \App\Core\Settings::setMany($changes);
        AuditLog::record('pesapal.options', 'settings', null, 'Pesapal offered on: ' . implode(', ', array_keys(array_filter(['donations' => $changes['donate_method'] !== 'link', 'shop' => ($changes['shop_pay_pesapal_enabled'] ?? '0') === '1']))) ?: 'nowhere');
        Flash::success('Saved. Pesapal is now offered on the places you ticked.');
        return $this->redirect('admin/pesapal');
    }

    /** @return array<string,array{amount:string,currency:string,on:bool,display:string}> */
    private function feeValues(): array
    {
        $out = [];
        foreach (['signup' => 'academy_signup_fee', 'cert' => 'academy_cert_fee'] as $kind => $key) {
            $conf = \App\Services\Academy\AcademyFees::configured($kind);
            $raw = trim((string) \App\Core\Settings::get($key, '0'));
            $out[$kind] = ['amount' => $raw === '' ? '0' : $raw, 'currency' => strtoupper((string) \App\Core\Settings::get($key . '_currency', 'UGX')), 'on' => $conf !== null, 'display' => $conf['display'] ?? ''];
        }
        return $out;
    }

    /** Set the Academy sign-up fee and certificate fee (0 = free). */
    public function fees(Request $request): Response
    {
        $this->authorize('inbox.manage', 'academy.manage');
        $save = [];
        foreach (['signup' => 'academy_signup_fee', 'cert' => 'academy_cert_fee'] as $kind => $key) {
            $raw = \App\Controllers\Web\DonateController::parseAmount((string) $request->post($kind . '_amount', '0'));
            $cur = strtoupper(trim((string) $request->post($kind . '_currency', 'UGX')));
            if ($raw === null || ($raw !== '' && ((float) $raw < 0 || (float) $raw > 100000000))) {
                Flash::error('Type the amount with digits only (for example 20000), or 0 for free.');
                return $this->redirect('admin/pesapal');
            }
            if (!in_array($cur, \App\Services\Academy\AcademyFees::CURRENCIES, true)) {
                Flash::error('Choose a currency from the list.');
                return $this->redirect('admin/pesapal');
            }
            $save[$key] = $raw === '' ? '0' : $raw;
            $save[$key . '_currency'] = $cur;
        }
        \App\Core\Settings::setMany($save);
        AuditLog::record('academy.fees', 'settings', null, 'Academy fees set: sign-up ' . $save['academy_signup_fee'] . ' ' . $save['academy_signup_fee_currency'] . ', certificate ' . $save['academy_cert_fee'] . ' ' . $save['academy_cert_fee_currency']);
        Flash::success('The Academy fees were saved.');
        return $this->redirect('admin/pesapal');
    }

    public function test(Request $request): Response
    {
        $this->authorize('inbox.manage');
        if (!Database::tableExists('pesapal_ipns')) {
            Flash::error('The Pesapal database tables are missing. Import database/updates/2026-10-pesapal.sql in phpMyAdmin first.');
            return $this->redirect('admin/pesapal');
        }
        $r = PesapalClient::testConnection();
        if ($r['ok']) {
            Flash::success($r['message']);
        } else {
            Flash::error($r['message']);
        }
        return $this->redirect('admin/pesapal');
    }

    public function check(Request $request, int|string $id): Response
    {
        $this->authorize('inbox.manage');
        if (!Database::tableExists('pesapal_payments')) {
            Flash::error('The Pesapal database tables are missing. Import database/updates/2026-10-pesapal.sql in phpMyAdmin first.');
            return $this->redirect('admin/pesapal');
        }
        $row = Database::one('SELECT * FROM pesapal_payments WHERE id = ?', [(int) $id]);
        if ($row === null || (string) ($row['order_tracking_id'] ?? '') === '') {
            Flash::error('That payment never reached the Pesapal page, so there is nothing to check.');
            return $this->redirect('admin/pesapal');
        }
        $res = PesapalPayments::settle((string) $row['order_tracking_id']);
        AuditLog::record('pesapal.check', 'pesapal_payment', (string) $row['id'], 'Checked ' . $row['merchant_reference'] . ' with Pesapal: ' . $res['state']);
        $msg = [
            'paid' => 'Pesapal confirms this payment: it is recorded as paid.', 'pending' => 'Pesapal says it is still waiting for the customer to pay.',
            'failed' => 'Pesapal says the payment failed.', 'reversed' => 'Pesapal says the payment was reversed.', 'review' => 'The payment needs a manual look: its amount or currency does not match.',
            'verify_failed' => 'Pesapal could not be reached. Try again in a minute.', 'unknown' => 'Pesapal does not know this payment.',
        ];
        Flash::info($msg[$res['state']] ?? 'Checked.');
        return $this->redirect('admin/pesapal');
    }
}
