<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\Social\SocialCard;

/**
 * PUBLIC share image: GET /social/image/{token}.jpg (docs/SOCIAL.md section 8). OWNER: IMAGES agent.
 *
 * Instagram and Facebook must fetch the picture from a public address, so this is the one public door to the private store: only a 64-hex token (the route enforces the
 * pattern; SocialCard::resolve() checks it again), only for an issue that is public right now. Everything else - unknown token, unpublished or deleted issue, missing file -
 * is the same plain 404 (no detail, no way to tell the cases apart, nothing to enumerate: 256 random bits). The file name, the folder and the issue are never revealed.
 * No cookie and no session are touched (nothing in this class writes to the session). Cacheable for an hour; ETag / Last-Modified answer 304; HEAD gets the headers only.
 */
final class SocialImageController extends Controller
{
    public function show(Request $request, string $token): Response
    {
        $file = SocialCard::resolve($token);
        if ($file === null) {
            throw new HttpException(404, 'Not found');
        }
        $size = (int) @filesize($file['file']);
        $headers = [
            'Content-Type'                 => 'image/jpeg',
            'X-Content-Type-Options'       => 'nosniff',
            'Cache-Control'                => 'public, max-age=3600',
            'ETag'                         => $file['etag'],
            'Last-Modified'                => gmdate('D, d M Y H:i:s', $file['mtime']) . ' GMT',
            'Content-Disposition'          => 'inline; filename="newsletter-share.jpg"',
            'Cross-Origin-Resource-Policy' => 'cross-origin',
            'X-Robots-Tag'                 => 'noindex',
        ];
        // conditional request: If-None-Match wins over If-Modified-Since (RFC 9110)
        $inm = trim((string) $request->header('If-None-Match', ''));
        $notModified = false;
        if ($inm !== '') {
            foreach (explode(',', $inm) as $candidate) {
                $candidate = trim($candidate);
                if ($candidate === '*' || $candidate === $file['etag'] || $candidate === 'W/' . $file['etag']) {
                    $notModified = true;
                    break;
                }
            }
        } else {
            $ims = trim((string) $request->header('If-Modified-Since', ''));
            if ($ims !== '') {
                $t = strtotime($ims);
                $notModified = $t !== false && $t >= $file['mtime'];
            }
        }
        if ($notModified) {
            unset($headers['Content-Disposition']);
            return new Response('', 304, $headers);
        }
        $headers['Content-Length'] = (string) $size;
        if ($request->isMethod('HEAD')) {
            return new Response('', 200, $headers);
        }
        $path = $file['file'];
        return new Response('', 200, $headers, static function () use ($path): void {
            readfile($path);
        });
    }
}
