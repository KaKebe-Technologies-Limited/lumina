<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\Settings;
use App\Services\Academy\AcademySocial;

/**
 * Admin > LAF Academy > Social follow check: the usernames new learners gave on our social media pages and the pages they opened. The team opens each username, looks it up in
 * the followers list of the page and marks the learner VERIFIED. Needs academy.manage. Routes: GET admin/academy/social, POST admin/academy/social/{id}/verify | unverify.
 */
final class AcademySocialController extends AcademyAdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('academy.manage');
        $status = in_array($request->string('status'), ['unverified', 'verified'], true) ? $request->string('status') : '';
        $q = mb_substr(trim($request->string('q')), 0, 80);
        return $this->render('admin/academy/social', [
            'followOn' => \App\Services\Academy\LearnerAuth::followRequired(), 'followSwitch' => trim((string) setting('academy_register_follow_required', '1')) !== '0', 'hasPages' => social_links('all') !== [], 'ready' => AcademySocial::ready(), 'rows' => AcademySocial::list($status, $q), 'counts' => AcademySocial::counts(), 'status' => $status, 'q' => $q, 'pages' => AcademySocial::pages(),
        ], ['title' => 'Social follow check', 'nav' => 'academy_social', 'breadcrumbs' => [['LAF Academy', url('admin/academy')], ['Social follow check', null]]]);
    }

    /** The switch: new learners must follow and subscribe to our pages (on) or sign up without that step (off). Can be flipped at any time; usernames already given are kept. */
    public function switch(Request $request): Response
    {
        $this->authorize('academy.manage');
        $on = (string) $request->post('state', '') === 'on';
        Settings::setMany(['academy_register_follow_required' => $on ? '1' : '0']);
        $this->audit('academy.social.switch', 'settings', null, 'Follow and subscribe at sign-up switched ' . ($on ? 'ON (required)' : 'OFF (not asked)'));
        Flash::success($on ? 'Follow and subscribe is ON: new learners must follow our pages when they sign up.' : 'Follow and subscribe is OFF: new learners sign up without that step. Switch it on again whenever you like.');
        return $this->redirect('admin/academy/social');
    }

    public function verify(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        AcademySocial::setVerified($id, true, Auth::id());
        $this->audit('academy.social.verify', 'academy_learner', $id, 'Marked the social media follow of a learner as verified');
        Flash::success('Marked as verified.');
        return $this->redirect('admin/academy/social' . ($request->string('back') !== '' ? '?status=' . rawurlencode($request->string('back')) : ''));
    }

    public function unverify(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        AcademySocial::setVerified($id, false, null);
        $this->audit('academy.social.unverify', 'academy_learner', $id, 'Removed the verified mark of a learner\'s social media follow');
        Flash::success('The verified mark was removed.');
        return $this->redirect('admin/academy/social' . ($request->string('back') !== '' ? '?status=' . rawurlencode($request->string('back')) : ''));
    }
}
