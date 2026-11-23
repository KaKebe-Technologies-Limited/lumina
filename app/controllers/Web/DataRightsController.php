<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\SitePages;
use App\Services\Privacy\DataRights;
use App\Services\Web\FormMessages;

/**
 * GET  /privacy/request  the data rights request form (shows the reference number after sending).
 * POST /privacy/request  validated -> contact_messages (kind 'dsr', reference, due date; Admin > Messages and Admin > Data protection) + acknowledgement e-mail.
 * Middleware: page.published:data-rights, honeypot, throttle (+ automatic CSRF).
 */
final class DataRightsController extends Controller
{
    public function index(Request $request): Response
    {
        $done = Session::pull('dsr_done');
        $type = (string) $request->query('type', '');
        return $this->renderPage('data-rights', 'web/legal/data-rights', [
            'done'  => is_array($done) ? $done : null,
            'types' => DataRights::TYPES,
            'preselect' => isset(DataRights::TYPES[$type]) ? $type : '',
            'days'  => DataRights::targetDays(),
        ], [
            'bodyClass'   => 'is-legal',
            'breadcrumbs' => [[(string) (SitePages::get('privacy')['nav_label'] ?? 'Privacy Policy'), url('privacy')], [(string) (SitePages::get('data-rights')['nav_label'] ?? 'Your data rights'), null]],
        ]);
    }

    public function send(Request $request): Response
    {
        $rules = [
            'name'    => 'required|max:150',
            'email'   => 'required|email|max:190',
            'phone'   => 'nullable|phone|max:40',
            'type'    => 'required|max:24',
            'details' => 'nullable|max:3000',
            'confirm' => 'required',
        ];
        $messages = FormMessages::for('data-rights', ['name', 'email', 'phone', 'type', 'details'], $rules)
            + ['confirm.required' => 'Please tick the box so we know the request is yours.'];
        $data = $this->validate($rules, $messages, [], 'privacy/request#request-form');
        $type = isset(DataRights::TYPES[(string) $data['type']]) ? (string) $data['type'] : '';
        if ($type === '') {
            $this->fail(['type' => 'Please choose what you would like to do.'], 'privacy/request#request-form');
        }
        $r = DataRights::record([
            'name' => trim((string) $data['name']), 'email' => trim((string) $data['email']), 'phone' => trim((string) ($data['phone'] ?? '')),
            'type' => $type, 'details' => trim((string) ($data['details'] ?? '')),
            'ip' => $request->ip(), 'user_agent' => $request->userAgent(),
        ]);
        Session::put('dsr_done', ['reference' => $r['reference'], 'due' => $r['due'], 'mailed' => $r['mailed']]);
        return $this->redirect('privacy/request#received');
    }
}
