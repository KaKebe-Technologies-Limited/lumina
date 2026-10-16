<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Database;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Slug;
use App\Core\Uploader;
use App\Models\Document;

/**
 * GET /reports (filter ?category=&year=&page=) and GET /reports/download/{id} (alias /reports/{id}/download).
 * OWNED BY B7 "org-public". Copy comes from the registry page "reports" (app/config/pages/reports.php).
 *
 * Only documents with is_active = 1 AND is_public = 1 are ever listed or served. The file lives under storage/private and is
 * streamed by Uploader::download() (which refuses any path that is not a well-formed file inside storage/private).
 */
final class ReportsController extends OrgPublicController
{
    private const PER_PAGE = 12;

    public function index(Request $request): Response
    {
        $visible = ['is_active' => 1, 'is_public' => 1];

        $category = $request->string('category');
        if (!array_key_exists($category, Document::CATEGORIES)) {
            $category = '';
        }
        $year = $request->int('year');
        if ($year < 1900 || $year > 2200) {
            $year = 0;
        }

        $conds = $visible;
        if ($category !== '') {
            $conds['category'] = $category;
        }
        if ($year > 0) {
            $conds['year'] = $year;
        }

        $everything = Document::count($visible);
        $pagination = Document::paginate($request->page(), self::PER_PAGE, $conds, 'year DESC, published_at DESC, id DESC');

        $categories = [];
        $years = [];
        if ($everything > 0) {
            foreach (Database::all('SELECT DISTINCT category FROM documents WHERE is_active = 1 AND is_public = 1 ORDER BY category ASC') as $row) {
                $categories[] = (string) $row['category'];
            }
            foreach (Database::all('SELECT DISTINCT year FROM documents WHERE is_active = 1 AND is_public = 1 AND year IS NOT NULL ORDER BY year DESC') as $row) {
                $years[] = (int) $row['year'];
            }
        }

        return $this->renderPage('reports', 'web/reports/index', [
            'documents'  => $pagination->items,
            'pagination' => $pagination,
            'everything' => $everything,
            'categories' => $categories,
            'years'      => $years,
            'category'   => $category,
            'year'       => $year,
            'filtered'   => $category !== '' || $year > 0,
        ], [
            'breadcrumbs' => $this->crumbs('reports'),
        ]);
    }

    /**
     * Counted download. Public documents only; anything else (private, inactive, unknown id, missing or malformed file path) is a plain 404.
     * HEAD requests and link prefetchers are not counted (only GET).
     */
    public function download(Request $request, int $id): Response
    {
        $doc = Document::find($id);
        if ($doc === null || (int) $doc['is_active'] !== 1 || (int) $doc['is_public'] !== 1) {
            throw new HttpException(404);
        }
        $path = (string) $doc['file_path'];
        Uploader::privatePath($path);                       // throws 404 when the stored path is malformed or the file is missing

        if ($request->isMethod('GET')) {
            Database::execute('UPDATE documents SET download_count = download_count + 1 WHERE id = ?', [$id]);
        }
        return Uploader::download($path, $this->downloadName($doc));
    }

    /** A clean file name for the browser's save dialog: the original name when known, else the title; always with the real extension. */
    private function downloadName(array $doc): string
    {
        $ext = strtolower(pathinfo((string) $doc['file_path'], PATHINFO_EXTENSION));
        $name = trim((string) ($doc['file_name'] ?? ''));
        $name = trim((string) preg_replace('/[\x00-\x1f\x7f\/\\\\]+/', '', $name));
        if ($name === '') {
            $name = Slug::make((string) $doc['title'], 80);
        }
        if ($name === '') {
            $name = 'document-' . (int) $doc['id'];
        }
        if ($ext !== '' && strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== $ext) {
            $name .= '.' . $ext;
        }
        return $name;
    }
}
