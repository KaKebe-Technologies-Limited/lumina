<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Settings;
use App\Services\Recruitment\Recruitment;

/**
 * Admin > Recruitment: judge an application with a click (KPI scores), the criteria of each role, the portrait and CV of an applicant. Ability inbox.manage.
 * Routes: POST admin/recruitment/{scope}/{id}/judge, GET .../portrait, GET .../cv, GET/POST admin/recruitment/criteria (+ {id}/delete, threshold).
 */
final class RecruitmentController extends AdminController
{
    private function scope(string $scope): string
    {
        return isset(Recruitment::SCOPES[$scope]) ? $scope : throw new HttpException(404, 'Not found');
    }

    public function judge(Request $request, int|string $scope, int|string $id): Response
    {
        $this->authorize('inbox.manage');
        $scope = $this->scope((string) $scope);
        $id = (int) $id;
        $row = Recruitment::row($scope, $id) ?? throw new HttpException(404, 'Not found');
        Recruitment::install();
        $points = (array) $request->post('points', []);
        $r = Recruitment::judge($scope, $id, $points, $this->userId());
        $this->audit('recruitment.judge', 'recruitment', $id, 'Scored the ' . Recruitment::SCOPES[$scope]['noun'] . ' of "' . $row['name'] . '"', ['pct' => $r['pct']]);
        if ($r['pct'] === null) {
            Flash::info('Click a score for at least one criterion.');
        } elseif ($r['invited']) {
            Flash::success('Score ' . rtrim(rtrim(number_format((float) $r['pct'], 1), '0'), '.') . '%: this reached the interview level, so the applicant was invited for an interview' . ($r['mailed'] === true ? ' and e-mailed.' : ($r['mailed'] === false ? ' (the e-mail could not be sent).' : '.')));
        } elseif (!$r['complete']) {
            Flash::success('Scores saved: ' . rtrim(rtrim(number_format((float) $r['pct'], 1), '0'), '.') . '% so far. Score every criterion to finish.');
        } else {
            Flash::success('Score ' . rtrim(rtrim(number_format((float) $r['pct'], 1), '0'), '.') . '%: below the interview level of ' . Recruitment::threshold() . '%. You can still move the applicant forward yourself.');
        }
        return $this->redirect(Recruitment::SCOPES[$scope]['admin'] . $id);
    }

    /** POST admin/recruitment/{scope}/{id}/status: the one-click stage buttons (the applicant is e-mailed; the optional note is added to the e-mail). */
    public function status(Request $request, int|string $scope, int|string $id): Response
    {
        $this->authorize('inbox.manage');
        $scope = $this->scope((string) $scope);
        $id = (int) $id;
        $row = Recruitment::row($scope, $id) ?? throw new HttpException(404, 'Not found');
        Recruitment::install();
        $to = $request->string('status');
        $res = Recruitment::setStatus($scope, $id, $to, '', $this->userId(), mb_substr(trim($request->string('applicant_note')), 0, 1000));
        if (!$res['ok']) {
            Flash::error('That stage is not available.');
        } elseif (!$res['changed']) {
            Flash::info('The application is already at that stage.');
        } else {
            $this->audit('recruitment.status', 'recruitment', $id, 'Moved the ' . Recruitment::SCOPES[$scope]['noun'] . ' of "' . $row['name'] . '" to ' . $to, ['status' => $to]);
            Flash::success('Moved to "' . (Recruitment::statuses($scope)[$to] ?? $to) . '".' . ($res['mailed'] === true ? ' The applicant was e-mailed.' : ($res['mailed'] === false ? ' The e-mail to the applicant could not be sent.' : '')));
        }
        return $this->redirect(Recruitment::SCOPES[$scope]['admin'] . $id);
    }

    public function file(Request $request, int|string $scope, int|string $id, string $part): Response
    {
        $this->authorize('inbox.manage');
        $scope = $this->scope((string) $scope);
        $part = $part === 'portrait' ? 'portrait' : 'cv';
        $res = Recruitment::file($scope, (int) $id, $part);
        if ($res === null) {
            throw new HttpException(404, 'Not found');
        }
        if ($part === 'cv') {
            $this->audit('recruitment.cv_download', 'recruitment', (int) $id, 'Downloaded the CV of an applicant');
        }
        return $res;
    }

    public function criteria(Request $request): Response
    {
        $this->authorize('inbox.manage');
        $ready = Recruitment::install();
        $scope = $this->scope($request->string('scope', 'ambassador'));
        return $this->render('admin/recruitment/criteria', ['ready' => $ready, 'scope' => $scope, 'scopes' => Recruitment::SCOPES, 'kpis' => Recruitment::kpis($scope, false), 'threshold' => Recruitment::threshold()],
            ['title' => 'Recruitment criteria', 'nav' => 'ambassadors', 'breadcrumbs' => [['Recruitment criteria', null]]]);
    }

    public function saveCriterion(Request $request): Response
    {
        $this->authorize('inbox.manage');
        Recruitment::install();
        $scope = $this->scope($request->string('scope'));
        Recruitment::saveKpi($scope, $request->int('id'), $request->string('label'), $request->int('weight', 1), $request->int('sort_order', 100), $request->bool('is_active') || $request->int('id') === 0);
        Flash::success('The criterion was saved.');
        return $this->redirect('admin/recruitment/criteria?scope=' . $scope);
    }

    public function deleteCriterion(Request $request, int|string $id): Response
    {
        $this->authorize('inbox.manage');
        Recruitment::deleteKpi((int) $id);
        Flash::success('The criterion was deleted, with the scores given for it.');
        return $this->redirect('admin/recruitment/criteria?scope=' . $this->scope($request->string('scope', 'ambassador')));
    }

    public function saveThreshold(Request $request): Response
    {
        $this->authorize('inbox.manage');
        Settings::set('recruit_interview_pct', (string) max(1, min(100, $request->int('pct', 70))));
        Settings::flush();
        Flash::success('The interview level was saved.');
        return $this->redirect('admin/recruitment/criteria?scope=' . $this->scope($request->string('scope', 'ambassador')));
    }
}
