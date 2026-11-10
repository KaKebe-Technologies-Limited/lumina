<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Flash;
use App\Core\Log;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Response;
use App\Core\UploadException;
use App\Core\Uploader;
use App\Models\InvolvementRequest;
use App\Services\Recruitment\Recruitment;

/**
 * GET /involvement and POST /involvement (volunteer / partner / advocate form)  - OWNER: B1 "web-core".
 * Requests are stored in `involvement_requests` (Admin > Involvement) and e-mailed to the notification address (Settings).
 * The route adds the honeypot + throttle middleware; every visible string comes from the "involvement" registry page.
 */
final class InvolvementController extends Controller
{
    private const TYPES = ['volunteer', 'partner', 'advocate'];

    public function index(Request $request): Response
    {
        $selected = (string) old('involvement_type', '');
        if (!in_array($selected, self::TYPES, true)) {
            $q = $request->string('type');
            $selected = in_array($q, self::TYPES, true) ? $q : '';
        }
        return $this->renderPage('involvement', 'web/involvement/index', [
            'types'        => $this->typeLabels(),
            'selectedType' => $selected,
            'faqs'         => HomeSupport::faqs('involvement', null, 'faq.items'),
        ], [
            'breadcrumbs' => [[HomeSupport::navLabel('involvement', 'Involvement'), null]],
        ]);
    }

    public function submit(Request $request): Response
    {
        $rules = [
            'name'             => 'required|max:150',
            'email'            => 'required|email|max:190',
            'phone'            => 'nullable|phone|max:40',
            'involvement_type' => 'required|in:' . implode(',', self::TYPES),
            'organisation'     => 'nullable|max:190',
            'message'          => 'nullable|max:3000',
        ];
        $data = $this->validate($rules, HomeSupport::formMessages('involvement', array_keys($rules)), [], 'involvement#join');

        $record = [
            'name'             => (string) $data['name'],
            'email'            => (string) $data['email'],
            'phone'            => $this->nullable($data['phone'] ?? null),
            'involvement_type' => (string) $data['involvement_type'],
            'organisation'     => $this->nullable($data['organisation'] ?? null),
            'message'          => $this->nullable($data['message'] ?? null),
            'status'           => 'new',
            'ip'               => $request->ip(),
            'user_agent'       => $request->userAgent(),
        ];
        // Volunteers must also send a CV and a portrait (partners and advocates need not)
        $cv = null;
        $pic = null;
        if ($record['involvement_type'] === 'volunteer') {
            Recruitment::install();
            $cvFile = $request->file('cv');
            $photoFile = $request->file('portrait');
            $errs = [];
            if ($cvFile === null) {
                $errs['cv'] = 'Volunteers: please attach your CV (PDF or Word).';
            }
            $problem = Recruitment::portraitProblem($photoFile);
            if ($problem !== '') {
                $errs['portrait'] = $problem;
            }
            if ($errs !== []) {
                $this->fail($errs, 'involvement#join');
            }
            try {
                $cv = Uploader::privateFile($cvFile, 'cv', (array) config('upload.cv_exts'));
            } catch (UploadException $e) {
                $this->fail(['cv' => $e->getMessage()], 'involvement#join');
            }
            $pic = Recruitment::storePortrait($photoFile);
            if (!$pic['ok']) {
                Uploader::deletePrivate($cv['path']);
                $this->fail(['portrait' => $pic['error']], 'involvement#join');
            }
            $record += ['cv_path' => $cv['path'], 'cv_original_name' => $cv['name'], 'cv_mime' => $cv['mime'], 'cv_size' => $cv['size'], 'photo_path' => $pic['path']];
        }
        try {
            $newId = (int) InvolvementRequest::create($record);
        } catch (\Throwable $e) {
            if ($cv !== null) {
                Uploader::deletePrivate($cv['path']);
                Uploader::deletePrivate((string) $pic['path']);
            }
            throw $e;
        }
        $ref = $record['involvement_type'] === 'volunteer' ? Recruitment::assignRef('volunteer', $newId) : '';

        $typeLabel = $this->typeLabels()[$record['involvement_type']] ?? $record['involvement_type'];
        $body = "New involvement request ({$typeLabel})\n\n"
            . "Name: {$record['name']}\n"
            . "Email: {$record['email']}\n"
            . 'Phone: ' . ($record['phone'] ?? '-') . "\n"
            . 'Organisation: ' . ($record['organisation'] ?? '-') . "\n\n"
            . "Message:\n" . ($record['message'] ?? '-') . "\n\n"
            . 'Manage it in the admin area: ' . abs_url('admin/involvement') . "\n";
        if (!Mailer::notify('New involvement request: ' . $typeLabel, $body, $record['email'])) {
            Log::warning('Involvement notification e-mail could not be sent', ['email' => $record['email']]);
        }

        if ($record['involvement_type'] === 'volunteer') {
            Recruitment::received('volunteer', $newId);
        }
        Flash::success((string) content('involvement', 'form.success') . ($ref !== '' ? ' Your application number is ' . $ref . ': we have e-mailed it to you.' : ''));
        return $this->redirect('involvement');
    }

    /** @return array<string,string> type => editable label */
    private function typeLabels(): array
    {
        return [
            'volunteer' => (string) content('involvement', 'form.type_volunteer'),
            'partner'   => (string) content('involvement', 'form.type_partner'),
            'advocate'  => (string) content('involvement', 'form.type_advocate'),
        ];
    }

    private function nullable(mixed $v): ?string
    {
        $s = trim((string) $v);
        return $s === '' ? null : $s;
    }
}
