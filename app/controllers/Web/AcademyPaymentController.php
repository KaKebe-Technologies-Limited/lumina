<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\Academy\CertificatePayments;
use App\Services\Academy\CertificateService;
use App\Services\Academy\CourseService;

/**
 * LAF Academy > the certificate fee, paid through Pesapal (docs/PESAPAL.md). The logic lives in CertificatePayments (tested without a browser); this class only turns
 * results into responses.
 *
 *   checkout  GET  /academy/courses/{slug}/checkout         what the learner pays for and the "Pay with Pesapal" button (signed-in learners who finished the course)
 *   start     POST /academy/courses/{slug}/checkout         new Pesapal order -> a short "taking you to Pesapal" page with a meta refresh to the hosted page
 *   return    GET  /academy/pay/return/{rid}                the learner is back from Pesapal; {rid} = CertificatePayments::returnKey(reference); the query is NOT trusted, the order is
 *                                                           verified with Pesapal; then a redirect to the course page with a note (or a neutral page when the learner is not signed in here)
 *   ipn       GET  /academy/pay/ipn                         Pesapal's notification (also POST): CSRF-exempt, authenticated by asking Pesapal back, answers the JSON Pesapal expects
 */
final class AcademyPaymentController extends AcademyBaseController
{
    public function checkout(Request $request, string $slug): Response
    {
        $this->gate();
        $l = $this->learner();
        if ($l === null) {
            return $this->loginRedirect('Please sign in to pay for your certificate.');
        }
        $course = CourseService::bySlug($slug, true) ?? throw new HttpException(404, 'Not found');
        $lid = (int) $l['id'];
        $cid = (int) $course['id'];
        if (CertificatePayments::paid($lid, $cid)) {
            Flash::info('This certificate is already paid for.');
            return $this->redirect('academy/courses/' . $course['slug']);
        }
        if (!CertificatePayments::required($lid, $course) || !CertificateService::eligible($lid, $cid)) {
            Flash::info(CertificatePayments::fee($course) <= 0 ? 'The certificate of this course is free.' : 'Finish every lesson and pass the final exam first, then you can pay for your certificate.');
            return $this->redirect('academy/courses/' . $course['slug']);
        }
        $last = CertificatePayments::latest($lid, $cid);
        return $this->renderAcademy('web/academy/checkout', [
            'course'    => $course,
            'fee'       => CertificatePayments::fee($course),
            'currency'  => CertificatePayments::CURRENCY,
            'available' => CertificatePayments::available(),
            'last'      => $last,
            'approval'  => CertificateService::approvalRequired(),
        ], [
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], [(string) $course['title'], url('academy/courses/' . $course['slug'])], ['Certificate payment', null]],
            'meta'        => ['title' => 'Pay for your certificate', 'robots' => 'noindex,nofollow'],
        ], true)->header('Cache-Control', 'private, no-store');
    }

    public function start(Request $request, string $slug): Response
    {
        $this->gate();
        $l = $this->learner();
        if ($l === null) {
            return $this->loginRedirect('Please sign in to pay for your certificate.');
        }
        $course = CourseService::bySlug($slug, true) ?? throw new HttpException(404, 'Not found');
        $r = CertificatePayments::begin($l, $course);
        if ($r['ok']) {
            // an interstitial, not a 302: the CSP form-action would block a redirect to another site after a form post (Chrome, Edge)
            return $this->renderAcademy('web/academy/pay-redirect', ['link' => (string) $r['link']], [
                'bodyClass' => 'is-academy is-shop-redirecting',
                'meta'      => ['title' => 'Taking you to the payment page', 'robots' => 'noindex,nofollow'],
            ], true)->header('Cache-Control', 'private, no-store');
        }
        switch ((string) ($r['error'] ?? '')) {
            case 'already_paid':
                Flash::success('Your payment is already confirmed. Thank you!');
                break;
            case 'not_required':
                Flash::info('The certificate of this course is free.');
                break;
            case 'not_eligible':
                Flash::info('Finish every lesson and pass the final exam first, then you can pay for your certificate.');
                break;
            case 'too_many':
                Flash::error('You started several payments in a short time. Please wait a little and try again.');
                break;
            case 'no_key':
            case 'unavailable':
                Flash::error('Online payment is not available at the moment. Please write to the Academy team and we will help you.');
                break;
            default:
                Flash::error('The payment page could not be opened. Nothing was charged. Please try again in a minute.');
        }
        return $this->redirect('academy/courses/' . $course['slug'] . (in_array($r['error'] ?? '', ['gateway', 'too_many', 'no_key', 'unavailable'], true) ? '/checkout' : ''));
    }

    public function return(Request $request, string $rid = ''): Response
    {
        $res = CertificatePayments::handleReturn($request->query(), $rid);
        $p = $res['payment'];
        $l = $this->learner();
        if ($p === null || $l === null || (int) ($p['learner_id'] ?? 0) !== (int) $l['id']) {
            // not tied to a payment of the signed-in learner (an old link, another browser, a guess): the same neutral page, nothing about anyone
            return $this->renderAcademy('web/academy/pay-return', ['state' => $p === null ? 'unknown' : (string) $res['state']], [
                'meta' => ['title' => 'Payment', 'robots' => 'noindex,nofollow', 'canonical' => '/academy'],        // never the address with the key in it
            ], true)->header('Cache-Control', 'private, no-store');
        }
        $slug = (string) (CourseService::find((int) $p['course_id'])['slug'] ?? '');
        $where = $slug !== '' ? 'academy/courses/' . $slug : 'academy/portal/certificates';
        switch ($res['state']) {
            case 'paid':
                Flash::success(CertificateService::approvalRequired()
                    ? 'Payment received, thank you! Your certificate request is now with the Academy team. You will get an e-mail as soon as it is ready.'
                    : 'Payment received, thank you! Your certificate is ready in your portal.');
                $where = 'academy/portal/certificates';
                break;
            case 'review':
                Flash::info('Your payment arrived but needs a quick check by the Academy team. We will write to you shortly.');
                break;
            case 'failed':
                Flash::error('The payment did not go through. Nothing was charged to you. You can try again.');
                $where .= '/checkout';
                break;
            default:
                Flash::info('We have not received the confirmation yet. Mobile money can take a minute: this page updates when you open it again. If money left your account, do not pay twice.');
                $where .= '/checkout';
        }
        return $this->redirect($where)->header('Cache-Control', 'private, no-store');
    }

    public function ipn(Request $request): Response
    {
        $res = CertificatePayments::handleIpn($request->query() + (array) $request->post());
        return Response::json($res['body'], $res['http'])->header('Cache-Control', 'no-store');
    }
}
