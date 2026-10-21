<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\Academy\AcademyMail;
use App\Services\Academy\CourseService;
use App\Services\Academy\FacilitatorApplications;

/**
 * Public: "Become a volunteer facilitator" at the LAF Academy. A visitor (no account needed) applies for one topic with a CV and supporting papers; the files are kept
 * privately and reviewed by administrators (Admin > LAF Academy > Facilitator applications). The role is voluntary and unpaid. Docs: docs/ACADEMY.md.
 */
final class AcademyFacilitateController extends AcademyBaseController
{
    public function form(Request $request): Response
    {
        $this->gate();
        if (!FacilitatorApplications::ready()) {
            throw new \App\Core\HttpException(404, 'Not found');
        }
        return $this->renderAcademy('web/academy/facilitate', ['topics' => CourseService::categoryLabels()], [
            'bodyClass' => 'is-academy is-academy-auth',
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], ['Become a facilitator', null]],
            'meta' => ['title' => 'Become a volunteer facilitator', 'description' => 'Volunteer your expertise: apply to facilitate an ICT topic at the LAF Academy by sending your CV and papers for approval.'],
        ]);
    }

    public function submit(Request $request): Response
    {
        $this->gate();
        if (!FacilitatorApplications::ready()) {
            throw new \App\Core\HttpException(404, 'Not found');
        }
        $res = FacilitatorApplications::submit($request->all(), $request->file('cv'), $request->files('papers'), $request->ip());
        if (!$res['ok']) {
            $this->fail($res['errors'], 'academy/facilitate');
        }
        $app = (array) FacilitatorApplications::find((int) $res['id']);
        AcademyMail::facilitatorReceived($app);
        AcademyMail::staffNotice('New facilitator application: ' . mb_substr((string) $app['topic_detail'], 0, 80), [
            (string) $app['name'] . ' applied to facilitate "' . $app['topic_detail'] . '" (' . CourseService::categoryLabel((string) $app['topic']) . ').',
            'Open the application to read the CV and the papers and to approve, decline or ask for more information.',
        ], 'admin/academy/facilitator-applications/' . $app['id']);
        Session::put('academy_facilitate_done', 1);
        return $this->redirect('academy/facilitate/thanks');
    }

    public function thanks(Request $request): Response
    {
        if (!Session::pull('academy_facilitate_done')) {
            return $this->redirect('academy/facilitate');
        }
        return $this->renderAcademy('web/academy/facilitate-thanks', [], [
            'bodyClass' => 'is-academy is-academy-auth',
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], ['Application received', null]],
            'meta' => ['title' => 'Application received'],
        ], true);
    }
}
