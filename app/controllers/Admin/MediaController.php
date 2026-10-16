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
