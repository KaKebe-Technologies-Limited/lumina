<?php
declare(strict_types=1);

namespace App\Core;

/**
 * PRE-LAUNCH GATE. Until the launch moment (app/config/launch.php, "launch_at") the public website is closed: every visitor gets one full-screen page, the launch pop-up with its big
 * countdown (views/launch/gate.php), and nothing else. At the launch moment the page reloads itself and the site opens. Signed-in staff and /admin keep working (so the team can
 * prepare), and the payment callbacks and robots.txt stay reachable. Set "gate" => false in the config (or "enabled" => false) to switch it off. On a local APP_ENV the gate only
 * applies when the address carries ?gate=1, so development is not locked out.
 */
final class PreLaunch
{
    /** Paths that must keep answering while the site is closed (payment callbacks, crawlers' robots file). */
    private const OPEN = ['/admin', '/pay/pesapal/ipn', '/shop/webhook', '/robots.txt'];

    /** @return array<string,mixed> */
    private static function config(): array
    {
        static $c = null;
        return $c ??= (array) require APP_PATH . '/config/launch.php';
    }

    public static function launchAt(): ?int
    {
        $t = strtotime((string) (self::config()['launch_at'] ?? ''));
        return $t === false ? null : $t;
    }

    public static function isLocked(Request $request): bool
    {
        $c = self::config();
        if (empty($c['enabled']) || empty($c['gate'])) {
            return false;
        }
        $at = self::launchAt();
        if ($at === null || time() >= $at) {
            return false;
        }
        if ((string) Config::get('app.env', 'production') === 'local' && (string) ($_GET['gate'] ?? '') !== '1') {
            return false;
        }
        foreach (self::OPEN as $p) {
            if ($request->pathIs($p)) {
                return false;
            }
        }
        return !Auth::check();
    }

    /** The closed-site page: HTTP 503 with Retry-After (search engines try again later), never cached, never indexed. */
    public static function response(): Response
    {
        $at = self::launchAt() ?? time();
        $html = View::render('launch/gate', []);
        return Response::html($html, 503)
            ->header('Cache-Control', 'no-store, max-age=0')
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Retry-After', (string) min(86400, max(30, $at - time())));
    }
}
