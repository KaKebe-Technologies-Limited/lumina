<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Request;
use App\Core\Response;
use App\Core\UploadException;
use App\Core\Uploader;

/**
 * POST admin/media/upload - shared AJAX image upload used by partials/admin/image-field.php, repeater image fields and the
 * rich-text editor (admin-editor.js). Needs the CSRF token (header X-CSRF-TOKEN or field _token). OWNED BY B3 (frozen).
 * Request: multipart field "file". Response: 201 {"path":"2026/10/abc.jpg","url":"/lumina/uploads/2026/10/abc.jpg","name":"photo.jpg"}
 * or 422 {"error":"..."}.
 */
final class MediaController extends AdminController
{
    /**
     * GET admin/media/gallery?q=&page= - the pictures of the website gallery, for the "Choose from the gallery" dialog of every picture field.
     * Response: {"items":[{"path","url","alt","caption","album"}],"more":bool}.
     */
    public function gallery(Request $request): Response
    {
        if (!can('pages.edit') && !can('content.manage')) {
            return Response::json(['error' => 'You are not allowed to choose pictures.'], 403);
        }
        $q = trim($request->string('q'));
        $page = max(1, $request->int('page', 1));
        $per = 36;
        $where = 'is_active = 1';
        $params = [];
        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $where .= ' AND (alt_text LIKE ? OR caption LIKE ? OR album LIKE ?)';
            $params = [$like, $like, $like];
        }
        try {
            $rows = \App\Core\Database::all('SELECT image, alt_text, caption, album FROM gallery_images WHERE ' . $where . ' ORDER BY sort_order ASC, id DESC LIMIT ' . ($per + 1) . ' OFFSET ' . (($page - 1) * $per), $params);
        } catch (\Throwable) {
            $rows = [];
        }
        $more = count($rows) > $per;
        $items = [];
        foreach (array_slice($rows, 0, $per) as $r) {
            $path = trim((string) $r['image']);
            if ($path === '' || !\App\Services\Newsletter\NewsletterContent::validImagePath($path)) {
                continue;
            }
            $items[] = ['path' => $path, 'url' => img_url($path), 'alt' => (string) $r['alt_text'], 'caption' => (string) ($r['caption'] ?? ''), 'album' => (string) ($r['album'] ?? '')];
        }
        return Response::json(['items' => $items, 'more' => $more]);
    }

    public function upload(Request $request): Response
    {
        if (!can('pages.edit') && !can('content.manage')) {
            return Response::json(['error' => 'You are not allowed to upload images.'], 403);
        }
        $file = $request->file('file');
        try {
            $path = Uploader::image($file);
        } catch (UploadException $e) {
            return Response::json(['error' => $e->getMessage()], 422);
        }
        $this->audit('media.upload', 'upload', $path, 'Uploaded an image');
        return Response::json(['path' => $path, 'url' => upload_url($path), 'name' => basename((string) ($file['name'] ?? ''))], 201);
    }
}
