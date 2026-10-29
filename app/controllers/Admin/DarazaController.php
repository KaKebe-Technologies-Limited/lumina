<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\AuditLog;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\Settings;
use App\Services\Daraza;

/**
 * Admin > Pay with Daraza: paste the payment link(s) made on the Daraza dashboard and tick where the "Pay with Daraza" button appears (Donate page and buttons, shop
 * checkout, Academy sign-up fee, Academy certificate fee). See App\Services\Daraza. Nothing here talks to Daraza.
 */
final class DarazaController extends AdminController
{
    private const URL_FIELDS = ['url' => 'daraza_url', 'donate_url' => 'daraza_donate_url', 'shop_url' => 'daraza_shop_url', 'signup_url' => 'daraza_signup_url', 'cert_url' => 'daraza_cert_url'];

    public function index(Request $request): Response
    {
        $this->authorize('inbox.manage');
        $values = ['label' => Daraza::label()];
        foreach (self::URL_FIELDS as $field => $key) {
            $values[$field] = trim((string) Settings::get($key, ''));
        }
        $places = [];
        foreach (Daraza::PLACES as $place => $name) {
            $places[$place] = ['name' => $name, 'on' => Daraza::on($place), 'link' => Daraza::url($place) !== '', 'can' => $this->canSwitch($place)];
        }
        return $this->render('admin/daraza/index', ['v' => $values, 'places' => $places], ['title' => 'Pay with Daraza', 'nav' => 'daraza', 'breadcrumbs' => [['Pay with Daraza', null]]]);
    }

    public function save(Request $request): Response
    {
        $this->authorize('inbox.manage');
        $save = [];
        $label = trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $request->post('label', ''))));
        $save['daraza_label'] = $label === '' ? 'Pay with Daraza' : mb_substr($label, 0, 40);
        foreach (self::URL_FIELDS as $field => $key) {
            $u = trim((string) $request->post($field, ''));
            if ($u !== '' && !Daraza::validUrl($u)) {
                Flash::error('One of the links is not accepted. Paste the payment link exactly as Daraza shows it: it starts with https:// and is on daraza.net (for example https://paylink.daraza.net/...).');
                return $this->redirect('admin/daraza');
            }
            $save[$key] = $u;
        }
        foreach (['donate', 'signup', 'cert'] as $place) {
            if ($this->canSwitch($place)) {
                $save['daraza_' . $place . '_on'] = $request->bool('on_' . $place) ? '1' : '0';
            }
        }
        if ($this->canSwitch('shop')) {
            $save['shop_pay_daraza_enabled'] = $request->bool('on_shop') ? '1' : '0';
        }
        Settings::setMany($save);
        AuditLog::record('daraza.settings', 'settings', null, 'Pay with Daraza settings saved (on: ' . implode(', ', array_keys(array_filter(array_map(static fn(string $p): bool => Daraza::on($p), array_keys(Daraza::PLACES))))) . ')');
        Flash::success('Saved. The "' . Daraza::label() . '" button now appears where you ticked, once it has a link.');
        return $this->redirect('admin/daraza');
    }

    /** The shop switch needs the shop settings ability, the Academy ones the Academy ability; the Donate page one anyone with this page. */
    private function canSwitch(string $place): bool
    {
        return match ($place) {
            'shop'           => can('shop.settings'),
            'signup', 'cert' => can('academy.manage'),
            default          => true,
        };
    }
}
