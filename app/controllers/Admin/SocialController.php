<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Services\Social\SocialProfiles;

/**
 * ADMIN > Social media > Profile links (docs/SOCIAL.md section 2). Ability social.manage. OWNER: ADMIN agent builds the final screen (icon preview, arrows / drag order,
 * live address check); the architect delivered the working controller and a plain view. All rules live in SocialProfiles.
 *
 *   GET  admin/social   the form: one address per platform (url_<platform>), a position per platform (order_<platform>, 1 = first), the four "show in" switches
 *                       (show_footer, show_contact, show_newsletter, show_maintenance)
 *   POST admin/social   save: every address is validated (https only, the host should be the platform's own: otherwise accepted WITH a warning, a bare @name becomes an
 *                       address); nothing is saved when one address is invalid. The addresses are the existing social_* settings, so the footer, the Contact page, the
 *                       JSON-LD sameAs, the maintenance page and the newsletter e-mails read them through social_links($context).
 */
final class SocialController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('social.manage');
        return $this->render('admin/social/index', [
            'platforms' => SocialProfiles::platforms(),
            'values'    => SocialProfiles::values(),
            'order'     => SocialProfiles::order(),
            'shows'     => ['footer' => SocialProfiles::showIn('footer'), 'contact' => SocialProfiles::showIn('contact'), 'newsletter' => SocialProfiles::showIn('newsletter'), 'maintenance' => SocialProfiles::showIn('maintenance')],
        ], ['title' => 'Social media profile links', 'nav' => 'social_profiles', 'breadcrumbs' => [['Social media', null]]]);
    }

    public function save(Request $request): Response
    {
        $this->authorize('social.manage');
        $urls = [];
        $pos = [];
        foreach (array_keys(SocialProfiles::PLATFORMS) as $key) {
            if ($request->has('url_' . $key)) {
                $urls[$key] = $request->string('url_' . $key);
            }
            $pos[$key] = $request->int('order_' . $key, 999);
        }
        $order = array_keys(SocialProfiles::PLATFORMS);
        usort($order, static fn(string $a, string $b): int => [$pos[$a], array_search($a, array_keys(SocialProfiles::PLATFORMS), true)] <=> [$pos[$b], array_search($b, array_keys(SocialProfiles::PLATFORMS), true)]);
        $res = SocialProfiles::save([
            'urls' => $urls, 'order' => $order,
            'show_footer' => $request->bool('show_footer'), 'show_contact' => $request->bool('show_contact'),
            'show_newsletter' => $request->bool('show_newsletter'), 'show_maintenance' => $request->bool('show_maintenance'),
        ], $this->userId());
        if (!$res['ok']) {
            $errors = [];
            foreach ($res['errors'] as $key => $msg) {
                $errors['url_' . $key] = $msg;
            }
            $this->fail($errors, 'admin/social');
        }
        foreach ($res['warnings'] as $w) {
            Flash::warning($w);
        }
        Flash::success('Social media links saved.');
        return $this->redirect('admin/social');
    }
}
