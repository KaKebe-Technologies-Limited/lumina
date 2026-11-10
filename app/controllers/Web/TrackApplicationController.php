<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\Recruitment\Recruitment;

/** GET/POST /track-application: an applicant enters the tracking number (AMB-/JOB-/VOL-...) and the e-mail address used, and sees the stage only (no personal details). */
final class TrackApplicationController extends Controller
{
    public function show(Request $request): Response
    {
        $result = null;
        $ref = trim($request->string('ref'));
        $email = trim($request->string('email'));
        if ($request->isPost() || $ref !== '') {
            $result = $ref !== '' && $email !== '' ? Recruitment::track($ref, $email) : ['found' => false, 'ref' => strtoupper($ref), 'label' => '', 'tone' => '', 'updated' => '', 'steps' => []];
        }
        return $this->render('web/recruitment/track', ['result' => $result, 'ref' => $ref, 'email' => $email], ['title' => 'Track my application']);
    }
}
