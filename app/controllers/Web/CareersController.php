<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Response;
use App\Core\SitePages;
use App\Core\UploadException;
use App\Core\Uploader;
use App\Models\Application;
use App\Models\Job;
use App\Services\Web\FormMessages;

/**
 * GET  /careers                  open vacancies (admin: Content > Jobs) + the editable careers copy + a general application form
 * GET  /careers/{slug}           vacancy page with its own application form
 * POST /careers/{slug}/apply     application for a vacancy      -> applications (job_id set)
 * POST /careers/apply            general expression of interest -> applications (job_id NULL)
 * The optional CV (pdf/doc/docx, 5 MB) goes through Uploader::privateFile(..., 'cv', ...) into storage/private/cv (never web accessible);
 * admins download it from /admin/applications/{id}/cv. Routes carry honeypot + throttle + CSRF.
 */
final class CareersController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->renderPage('careers', 'web/careers/index', [
            'jobs' => Job::open(),
        ], [
            'bodyClass'   => 'is-careers-index',
            'breadcrumbs' => [[$this->navLabel(), null]],
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $this->requirePublished();
        $job = $this->findJob($slug);
        $description = trim((string) ($job['seo_description'] ?? ''));
        if ($description === '') {
            $description = trim((string) ($job['summary'] ?? ''));
        }
        if ($description === '') {
            $description = excerpt((string) ($job['description'] ?? ''), 175);
        }
        $title = trim((string) ($job['seo_title'] ?? '')) !== '' ? (string) $job['seo_title'] : (string) $job['title'];

        return $this->renderPage('careers', 'web/careers/show', [
            'job'    => $job,
            'closed' => $this->isClosed($job),
        ], [
            'bodyClass'   => 'is-job',
            'meta'        => ['title' => $title, 'description' => $description, 'canonical' => '/careers/' . $job['slug']],
            'breadcrumbs' => [[$this->navLabel(), url('careers')], [(string) $job['title'], null]],
        ]);
    }

    public function apply(Request $request, string $slug): Response
    {
        $this->requirePublished();
        $job = $this->findJob($slug);
        if ($this->isClosed($job)) {
            Flash::error((string) content('careers', 'form.closed_error'));
            return $this->redirect('careers/' . $job['slug']);
        }
        return $this->submit($request, $job);
    }

    public function applyGeneral(Request $request): Response
    {
        $this->requirePublished();
        return $this->submit($request, null);
    }

    // -------------------------------------------------------------------------------------------------------------

    private function submit(Request $request, ?array $job): Response
    {
        $back = $job !== null ? 'careers/' . $job['slug'] : 'careers';
        $rules = [
            'name'         => 'required|max:150',
            'email'        => 'required|email|max:190',
            'phone'        => 'nullable|phone|max:40',
            'cover_letter' => 'nullable|max:5000',
            'linkedin_url' => 'nullable|url|max:255',
            'consent'      => 'required|accepted',
        ];
        $messages = FormMessages::for('careers', array_keys($rules), $rules);
        $consent = trim((string) content('careers', 'form.err_consent'));
        if ($consent !== '') {
            $messages['consent.required'] = $consent;
            $messages['consent.accepted'] = $consent;
        }
        $data = $this->validate($rules, $messages, [], $back . '#apply');
        foreach (['name', 'email', 'phone', 'cover_letter', 'linkedin_url'] as $k) {
            $data[$k] = trim((string) ($data[$k] ?? ''));
        }

        // Optional CV: stored PRIVATELY (outside the web root). Failure -> field error, nothing is saved.
        $cv = null;
        $file = $request->file('cv');
        if ($file !== null) {
            try {
                $cv = Uploader::privateFile($file, 'cv', (array) config('upload.cv_exts'));
            } catch (UploadException $e) {
                $this->fail(['cv' => $e->getMessage()], $back . '#apply');
            }
        }

        try {
            $id = Application::create([
                'job_id'           => $job['id'] ?? null,
                'name'             => $data['name'],
                'email'            => $data['email'],
                'phone'            => $data['phone'] !== '' ? $data['phone'] : null,
                'cover_letter'     => $data['cover_letter'] !== '' ? $data['cover_letter'] : null,
                'cv_path'          => $cv['path'] ?? null,
                'cv_original_name' => $cv['name'] ?? null,
                'cv_mime'          => $cv['mime'] ?? null,
                'cv_size'          => $cv['size'] ?? null,
                'linkedin_url'     => $data['linkedin_url'] !== '' ? $data['linkedin_url'] : null,
                'status'           => 'new',
                'ip'               => $request->ip(),
                'user_agent'       => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            if ($cv !== null) {
                Uploader::deletePrivate($cv['path']);       // never leave an orphaned private file behind
            }
            throw $e;
        }

        $subject = trim((string) content('careers', $job !== null ? 'form.notify_subject' : 'form.notify_subject_general'));
        $subject = ($subject !== '' ? $subject : 'Application') . ($job !== null ? ': ' . $job['title'] : '');
        $lines = [
            $job !== null ? 'Vacancy: ' . $job['title'] : 'General application (talent network)',
            'Name: ' . $data['name'],
            'Email: ' . $data['email'],
            'Phone: ' . ($data['phone'] !== '' ? $data['phone'] : '-'),
            'LinkedIn: ' . ($data['linkedin_url'] !== '' ? $data['linkedin_url'] : '-'),
            'CV: ' . ($cv !== null ? $cv['name'] . ' (' . human_filesize($cv['size']) . ', download it from the admin)' : 'not attached'),
            '',
            'Message:',
            $data['cover_letter'] !== '' ? $data['cover_letter'] : '-',
            '',
            'Open in the admin: ' . abs_url('admin/applications/' . $id),
        ];
        Mailer::notify($subject, implode("\n", $lines), $data['email']);

        Flash::success((string) content('careers', $job !== null ? 'form.success_job' : 'form.success'));
        return $this->redirect($back);
    }

    private function findJob(string $slug): array
    {
        $job = Job::first(['slug' => $slug, 'is_active' => 1]);
        if ($job === null) {
            throw new HttpException(404, 'Not found');
        }
        return $job;
    }

    /** A vacancy is closed once its closing date (inclusive) has passed. */
    private function isClosed(array $job): bool
    {
        $d = (string) ($job['closing_date'] ?? '');
        return $d !== '' && $d !== '0000-00-00' && $d < date('Y-m-d');
    }

    private function requirePublished(): void
    {
        if (!SitePages::isPublished('careers')) {
            throw new HttpException(404, 'Not found');
        }
    }

    private function navLabel(): string
    {
        return (string) (SitePages::get('careers')['nav_label'] ?? 'Careers');
    }
}
