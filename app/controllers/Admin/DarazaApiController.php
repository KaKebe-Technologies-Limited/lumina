<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\Settings;
use App\Services\DarazaPay\DarazaApi;
use App\Services\DarazaPay\DarazaPayments;
use App\Services\Payments\PaymentOptions;

/**
 * Admin > Mobile money (Daraza API): the connection (the key is in the server .env, never here), where "Pay with MTN or Airtel mobile money" is offered, the rate for USD fees, the
 * payments received, "check again" for a payment whose answer never came back, and PAYOUTS (send money to a phone with the remittance call). Needs settings.manage (administrators).
 */
final class DarazaApiController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('settings.manage');
        $ok = DarazaPayments::install();
        $places = [];
        foreach (DarazaPayments::PURPOSES as $k => $name) {
            $places[$k] = ['name' => $name, 'on' => DarazaPayments::on($k)];
        }
        return $this->render('admin/daraza/api', [
            'ready' => $ok, 'hasKey' => DarazaApi::hasKey(), 'mode' => DarazaApi::mode(), 'places' => $places, 'rate' => DarazaPayments::rate(),
            'options' => array_map(static fn(string $k): array => ['label' => PaymentOptions::OPTIONS[$k][0], 'help' => PaymentOptions::OPTIONS[$k][1], 'on' => PaymentOptions::on($k)], array_combine(array_keys(PaymentOptions::OPTIONS), array_keys(PaymentOptions::OPTIONS))),
            'ways' => array_map(static fn(string $k): array => ['key' => $k, 'label' => PaymentOptions::WAYS[$k][0], 'help' => PaymentOptions::WAYS[$k][1], 'rank' => PaymentOptions::rank($k)], PaymentOptions::ranked()),
            'rows' => $ok ? DarazaPayments::recent(40) : [], 'balance' => null,
        ], ['title' => 'Payment options', 'nav' => 'daraza_api', 'breadcrumbs' => [['Payment options', null]]]);
    }

    public function save(Request $request): Response
    {
        $this->authorize('settings.manage');
        $preset = $request->string('preset');
        $save = [];
        $opts = [];
        if ($preset === 'mobile_only') {
            foreach (array_keys(DarazaPayments::PURPOSES) as $k) {
                $save['daraza_api_' . $k . '_on'] = '1';
            }
            $opts = ['online' => false, 'link' => false, 'manual' => false];
        } elseif ($preset === 'restore') {
            $opts = ['online' => true, 'link' => true, 'manual' => true];
        } else {
            foreach (array_keys(DarazaPayments::PURPOSES) as $k) {
                $save['daraza_api_' . $k . '_on'] = $request->bool('on_' . $k) ? '1' : '0';
            }
            foreach (array_keys(PaymentOptions::OPTIONS) as $k) {
                $opts[$k] = $request->bool('opt_' . $k);
            }
            PaymentOptions::saveRanks(array_combine(array_keys(PaymentOptions::WAYS), array_map(static fn(string $k): int => $request->int('rank_' . $k), array_keys(PaymentOptions::WAYS))));
        }
        if (!in_array($preset, ['mobile_only', 'restore'], true)) {
            $save['daraza_api_usd_rate'] = (string) max(0, min(100000, $request->int('usd_rate')));
        }
        Settings::setMany($save);
        PaymentOptions::save($opts);
        Settings::flush();
        $this->audit('daraza.api.settings', 'settings', null, 'Payment options saved' . ($preset !== '' ? ' (' . $preset . ')' : ''));
        Flash::success($preset === 'mobile_only' ? 'Done: mobile money is on everywhere and the earlier ways to pay are switched off. You can switch them back on here at any time.' : ($preset === 'restore' ? 'The earlier ways to pay are switched back on.' : 'Saved.')
            . (!DarazaApi::hasKey() ? ' The mobile-money buttons appear only once DARAZA_API_KEY is in the server .env file.' : ''));
        return $this->redirect('admin/daraza/api');
    }

    public function test(Request $request): Response
    {
        $this->authorize('settings.manage');
        $b = DarazaApi::balance();
        $b['ok'] ? Flash::success('Connected to Daraza (' . DarazaApi::mode() . '). App wallet balance: ' . $b['balance'] . '.') : Flash::error('Could not connect to Daraza: ' . $b['error']);
        return $this->redirect('admin/daraza/api');
    }

    public function check(Request $request, int|string $id): Response
    {
        $this->authorize('settings.manage');
        $r = DarazaPayments::check((int) $id);
        $r['state'] === 'paid' ? Flash::success($r['message']) : ($r['state'] === 'failed' ? Flash::error($r['message']) : Flash::info($r['message']));
        return $this->redirect('admin/daraza/api');
    }

    public function payout(Request $request): Response
    {
        $this->authorize('settings.manage');
        if ($request->string('confirm') !== '1') {
            Flash::error('Tick the box to confirm that you want to send this money.');
            return $this->redirect('admin/daraza/api');
        }
        $r = DarazaPayments::payout($this->userId(), $request->string('phone'), $request->int('method'), $request->int('amount'), $request->string('note'));
        $r['ok'] ? Flash::success($r['message']) : Flash::error($r['message']);
        return $this->redirect('admin/daraza/api');
    }
}
