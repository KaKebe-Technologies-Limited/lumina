<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Request;
use App\Core\Response;

/**
 * Admin > Administration > File check (read only): compares every file of the site on THIS server with the list in app/config/manifest.php (made by bin/build-manifest.php
 * from the project) and shows what is MISSING and what is DIFFERENT (an older version), so an upload that was forgotten or went to the wrong folder is found at once.
 * Needs settings.manage. Route: GET admin/file-check.
 */
final class FileCheckController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('settings.manage');
        $mf = is_file(APP_PATH . '/config/manifest.php') ? require APP_PATH . '/config/manifest.php' : null;
        $missing = [];
        $different = [];
        $ok = 0;
        $generated = '';
        if (is_array($mf) && isset($mf['files'])) {
            $generated = (string) ($mf['generated'] ?? '');
            $root = dirname(APP_PATH);
            foreach ($mf['files'] as $rel => $sha) {
                $abs = $root . '/' . $rel;
                if (!is_file($abs)) {
                    $missing[] = (string) $rel;
                } elseif (sha1(str_replace("\r\n", "\n", (string) file_get_contents($abs))) !== $sha) {
                    $different[] = (string) $rel;
                } else {
                    $ok++;
                }
            }
        }
        return $this->render('admin/file-check', [
            'hasManifest' => $generated !== '', 'generated' => $generated, 'missing' => $missing, 'different' => $different, 'ok' => $ok,
        ], ['title' => 'File check', 'nav' => 'filecheck', 'breadcrumbs' => [['File check', null]]]);
    }
}
