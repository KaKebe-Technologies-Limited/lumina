<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Flash;
use App\Core\Maintenance;
use App\Core\Request;
use App\Core\Response;
use App\Core\Settings;

/**
 * WEBSITE ON / OFF (admins only, ability settings.manage).
 *   GET  admin/maintenance           status, big on/off button, message / countdown / contact options, preview
 *   POST admin/maintenance/toggle    switch the website off (maintenance page for visitors) or back on, with one click
 *   POST admin/maintenance/save      save the message, expected-back time and display options (does not change on/off)
 *   GET  admin/maintenance/preview   the visitor page exactly as it looks (HTTP 200, for staff only)
 * Signed-in staff and /admin always see the real site, so the owner can keep working while visitors see the maintenance page.
 */
final class MaintenanceController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('settings.manage');
        $g = static fn (string $k, string $d = ''): string => (string) Settings::get($k, $d);
        return $this->render('admin/maintenance/index', [
            'on'       => Maintenance::isOn(),
            'forced'   => (bool) \App\Core\Config::get('app.maintenance', false),
            'enabled'  => $g('maintenance_enabled', '0') === '1',
            'title'    => $g('maintenance_title'),
            'message'  => $g('maintenance_message'),
            'returnAt' => $g('maintenance_return_at'),
            'autoOpen' => $g('maintenance_auto_open', '0') === '1',
            'contact'  => $g('maintenance_show_contact', '1') !== '0',
            'social'   => $g('maintenance_show_social', '1') !== '0',
        ], ['title' => 'Website on / off', 'nav' => 'maintenance', 'breadcrumbs' => [['Website on / off', null]]]);
    }

    public function toggle(Request $request): Response
    {
        $this->authorize('settings.manage');
        $turnOff = (string) $request->post('mode', '') === 'off';
        Settings::set('maintenance_enabled', $turnOff ? '1' : '0');
        $this->audit('maintenance.' . ($turnOff ? 'on' : 'off'), 'setting', 'maintenance_enabled', $turnOff ? 'Website switched OFF (maintenance page shown to visitors)' : 'Website switched back ON');
        if ($turnOff) {
            Flash::success('The website is now OFF for visitors. They see your maintenance page. You and other signed-in staff can still see the real site.');
        } elseif ((bool) \App\Core\Config::get('app.maintenance', false)) {
            Flash::warning('Saved, but the website is still closed: APP_MAINTENANCE=1 is set in the server .env file. Remove that line to open it.');
        } else {
            Flash::success('The website is back ON. Visitors can see it again.');
        }
        return $this->redirect('admin/maintenance');
    }

    public function save(Request $request): Response
    {
        $this->authorize('settings.manage');
        $d = $this->validate([
            'title'     => 'nullable|max:120',
            'message'   => 'nullable|max:800',
            'return_at' => 'nullable|max:20',
        ], [], ['title' => 'Headline', 'message' => 'Message', 'return_at' => 'Expected back'], 'admin/maintenance');

        $returnAt = trim((string) ($d['return_at'] ?? ''));
        if ($returnAt !== '') {
            $returnAt = str_replace('T', ' ', $returnAt);
            if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $returnAt) || \DateTimeImmutable::createFromFormat('Y-m-d H:i', $returnAt) === false) {
                $this->fail(['return_at' => 'Use a date and time like 2026-10-25 09:00.'], 'admin/maintenance');
            }
        }
        Settings::setMany([
            'maintenance_title'        => strip_tags((string) ($d['title'] ?? '')),
            'maintenance_message'      => strip_tags((string) ($d['message'] ?? '')),
            'maintenance_return_at'    => $returnAt,
            'maintenance_auto_open'    => $request->post('auto_open') ? '1' : '0',
            'maintenance_show_contact' => $request->post('show_contact') ? '1' : '0',
            'maintenance_show_social'  => $request->post('show_social') ? '1' : '0',
        ]);
        $this->audit('maintenance.save', 'setting', 'maintenance', 'Maintenance page options saved');
        Flash::success('Maintenance page saved.');
        return $this->redirect('admin/maintenance');
    }

    public function preview(Request $request): Response
    {
        $this->authorize('settings.manage');
        return Maintenance::response(true);
    }
}
