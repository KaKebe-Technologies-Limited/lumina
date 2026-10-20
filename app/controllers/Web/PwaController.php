<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

/**
 * GET /manifest.webmanifest  the web app manifest (name, colours, icons) built from Admin > Settings > Install as an app.
 * GET /sw.js                 the service worker: keeps only public CSS/JS/images/fonts and shows /offline when a page cannot be reached.
 * GET /offline               the page shown when the visitor has no connection.
 * No session is started. Private pages (admin, member pages, orders, meeting links) are never stored by the worker.
 */
final class PwaController extends Controller
{
    private function on(): bool
    {
        return (string) setting('pwa_enabled', '1') === '1';
    }

    private function colour(string $key, string $default): string
    {
        $v = trim((string) setting($key, $default));
        return preg_match('/^#[0-9a-fA-F]{6}$/', $v) === 1 ? $v : $default;
    }

    public function manifest(Request $request): Response
    {
        if (!$this->on()) {
            return Response::text('', 404);
        }
        $display = (string) setting('pwa_display', 'standalone');
        $display = in_array($display, ['standalone', 'minimal-ui', 'browser'], true) ? $display : 'standalone';
        $scope = base_path() . '/';
        $name = trim((string) setting('pwa_name', 'Lumina Africa Foundation')) ?: 'Lumina Africa Foundation';
        $short = trim((string) setting('pwa_short_name', 'Lumina')) ?: 'Lumina';
        $icon = static fn(string $f, string $size, string $purpose = 'any'): array => [
            'src' => url('assets/img/pwa/' . $f), 'sizes' => $size, 'type' => 'image/png', 'purpose' => $purpose,
        ];
        $data = [
            'id' => $scope,
            'name' => $name,
            'short_name' => $short,
            'description' => trim((string) setting('pwa_description', '')),
            'start_url' => $scope . '?source=pwa',
            'scope' => $scope,
            'display' => $display,
            'orientation' => 'any',
            'lang' => 'en',
            'theme_color' => $this->colour('pwa_theme_color', '#2C3950'),
            'background_color' => $this->colour('pwa_background_color', '#1F2A44'),
            'icons' => [
                $icon('icon-192.png', '192x192'), $icon('icon-512.png', '512x512'),
                $icon('maskable-192.png', '192x192', 'maskable'), $icon('maskable-512.png', '512x512', 'maskable'),
            ],
        ];
        return Response::json($data)
            ->header('Content-Type', 'application/manifest+json; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function worker(Request $request): Response
    {
        $js = "'use strict';\n";
        if (!$this->on()) {
            // switched off: unregister itself and clear everything it stored
            $js .= "self.addEventListener('install',function(){self.skipWaiting();});\n"
                . "self.addEventListener('activate',function(e){e.waitUntil(caches.keys().then(function(k){return Promise.all(k.filter(function(n){return n.indexOf('lumina-')===0;}).map(function(n){return caches.delete(n);}));}).then(function(){return self.registration.unregister();}));});\n";
        } else {
            $cfg = [
                'version' => 'lumina-v' . (int) setting('pwa_cache_version', 1),
                'base' => base_path(),
                'offline' => url('offline'),
                'precache' => [url('offline'), url('assets/img/pwa/icon-192.png'), url('assets/img/pwa/icon-512.png')],
                // never kept on the device (prefixes after the base path)
                'private' => ['/admin', '/membership/me', '/membership/verify', '/membership/confirmation', '/shop/cart', '/shop/checkout', '/shop/order', '/shop/pay', '/shop/webhook', '/meeting', '/unsubscribe', '/api', '/newsletter/'],
            ];
            $js .= 'const CFG=' . json_encode($cfg, JSON_UNESCAPED_SLASHES) . ";\n" . (string) file_get_contents(APP_PATH . '/views/pwa/sw-body.js');
        }
        return Response::text($js, 200, 'application/javascript; charset=utf-8')
            ->header('Cache-Control', 'no-cache')
            ->header('Service-Worker-Allowed', base_path() . '/');
    }

    public function offline(Request $request): Response
    {
        return Response::html(partial('pwa/offline', [
            'siteName' => (string) setting('pwa_name', 'Lumina Africa Foundation'),
            'title' => (string) setting('pwa_offline_title', 'You are offline'),
            'text' => (string) setting('pwa_offline_text', ''),
            'theme' => $this->colour('pwa_theme_color', '#2C3950'),
            'bg' => $this->colour('pwa_background_color', '#1F2A44'),
            'home' => url(''),
        ]))->header('Cache-Control', 'public, max-age=3600');
    }
}
