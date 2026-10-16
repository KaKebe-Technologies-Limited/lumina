<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\UploadException;
use App\Core\Uploader;
use App\Core\Validator;
use App\Models\Document;

/**
 * Admin > Documents / reports (/admin/documents). OWNER: B8 "org-admin".
 *
 * Files are stored PRIVATELY under storage/private/documents/YYYY/MM/<random>.<ext> (never web accessible) and the relative
 * path goes into documents.file_path. Visitors get them through B7's /reports/download/{id} (is_active + is_public checked,
 * counter incremented); admins preview through GET /admin/documents/{id}/download (no counter).
 *
 * Accepted: pdf doc docx xls xlsx ppt pptx zip, up to 20 MB. Uploader::privateFile() handles everything except zip (it has
 * no zip signature), so .zip archives go through storeZip() below with the same safety rules (extension allow-list,
 * double-extension rejection, magic bytes, random server-side name, private folder).
 */
final class DocumentController extends OrgAdminController
{
    public const MAX_BYTES = 20 * 1024 * 1024;
    public const EXTS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip'];

    private const BAD_PARTS = ['php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'pht', 'phps', 'phar', 'html', 'htm', 'xhtml', 'shtml', 'svg',
        'svgz', 'js', 'mjs', 'exe', 'dll', 'bat', 'cmd', 'com', 'sh', 'cgi', 'pl', 'py', 'asp', 'aspx', 'jsp', 'htaccess', 'htpasswd', 'ini', 'swf', 'jar', 'vbs', 'ps1'];

    protected function area(): string
    {
        return 'admin/documents';
    }

    /** @return array<string,string[]> */
    private function sortable(): array
    {
        return [
            'added'     => ['created_at'],
            'title'     => ['title'],
            'category'  => ['category', 'title'],
            'year'      => ['year', 'published_at'],
            'downloads' => ['download_count'],
            'size'      => ['file_size'],
            'status'    => ['is_active', 'title'],
        ];
    }

    public function index(Request $request): Response
    {
        $this->authorize('content.manage');
        $st = $this->listState($this->sortable(), 'added', 'desc');

        $where = [];
        $params = [];
        [$sql, $sp] = $this->searchSql(['title', 'description', 'file_name'], $st['q']);
        if ($sql !== '') {
            $where[] = $sql;
            $params = array_merge($params, $sp);
        }
        $category = $request->string('category');
        if (isset(Document::CATEGORIES[$category])) {
            $where[] = 'category = ?';
            $params[] = $category;
        } else {
            $category = '';
        }
        $year = $request->string('year');
        if ($year !== '' && ctype_digit($year)) {
            $where[] = 'year = ?';
            $params[] = (int) $year;
        } else {
            $year = '';
        }
        $visibility = $request->string('visibility');
        if ($visibility === 'public' || $visibility === 'private') {
            $where[] = 'is_public = ?';
            $params[] = $visibility === 'public' ? 1 : 0;
        } else {
            $visibility = '';
        }
        $status = $request->string('status');
        if ($status === 'active' || $status === 'inactive') {
            $where[] = 'is_active = ?';
            $params[] = $status === 'active' ? 1 : 0;
        } else {
            $status = '';
        }

        $pager = $this->paged('documents' . $this->whereSql($where), $params, '*', $this->orderSql($this->sortable(), $st['sort'], $st['dir']));
        foreach ($pager->items as &$item) {
            $item['file_ok'] = $this->fileExists((string) $item['file_path']);
        }
        unset($item);

        $years = array_map('intval', array_column(Database::all('SELECT DISTINCT year FROM documents WHERE year IS NOT NULL ORDER BY year DESC'), 'year'));

        return $this->render('admin/documents/index', [
            'pager'      => $pager,
            'q'          => $st['q'],
            'sort'       => $st['sort'],
            'dir'        => $st['dir'],
            'category'   => $category,
            'year'       => $year,
            'visibility' => $visibility,
            'status'     => $status,
            'filtered'   => $st['q'] !== '' || $category !== '' || $year !== '' || $visibility !== '' || $status !== '',
            'total'      => Document::count(),
            'years'      => $years,
            'categories' => Document::CATEGORIES,
        ], ['title' => 'Documents', 'nav' => 'documents', 'breadcrumbs' => [['Documents', null]]]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('content.manage');
        return $this->render('admin/documents/create', [
            'doc'        => ['is_active' => 1, 'is_public' => 1, 'category' => 'other', 'year' => '', 'published_at' => ''],
            'categories' => Document::CATEGORIES,
            'maxBytes'   => $this->effectiveMaxBytes(),
        ], ['title' => 'Upload document', 'nav' => 'documents', 'breadcrumbs' => [['Documents', url('admin/documents')], ['Upload', null]]]);
    }

    public function store(Request $request): Response
    {
        $this->authorize('content.manage');
        $to = 'admin/documents/create';
        $data = $this->collect($to);
        if ($request->file('document_file') === null) {
            $this->fail(['document_file' => 'Choose the file to upload.'], $to);
        }
        try {
            $file = $this->storeUpload($request->file('document_file'));
        } catch (UploadException $e) {
            $this->fail(['document_file' => $e->getMessage()], $to);
        }
        try {
            $id = Document::create($data + [
                'file_path' => $file['path'],
                'file_name' => $file['name'],
                'file_size' => $file['size'],
                'mime'      => $file['mime'],
            ]);
        } catch (\Throwable $e) {
            Uploader::deletePrivate($file['path']);
            throw $e;
        }
        $this->audit('document.create', 'document', $id, 'Uploaded document "' . $data['title'] . '"', [
            'category' => $data['category'], 'public' => (bool) $data['is_public'], 'file' => $file['name'], 'size' => $file['size'],
        ]);
        Flash::success('Document "' . $data['title'] . '" was uploaded.' . (($data['is_public'] && $data['is_active']) ? '' : ' It is not visible on the website yet.'));
        return $this->redirect('admin/documents');
    }

    public function edit(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $doc = Document::findOrFail($id);
        $doc['file_ok'] = $this->fileExists((string) $doc['file_path']);
        return $this->render('admin/documents/edit', [
            'doc'        => $doc,
            'categories' => Document::CATEGORIES,
            'maxBytes'   => $this->effectiveMaxBytes(),
        ], ['title' => 'Edit document', 'nav' => 'documents', 'breadcrumbs' => [['Documents', url('admin/documents')], [$doc['title'], null]]]);
    }

    public function update(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = Document::findOrFail($id);
        $to = 'admin/documents/' . $id . '/edit';
        $data = $this->collect($to);

        $file = null;
        if ($request->file('document_file') !== null) {
            try {
                $file = $this->storeUpload($request->file('document_file'));
            } catch (UploadException $e) {
                $this->fail(['document_file' => $e->getMessage()], $to);
            }
            $data += ['file_path' => $file['path'], 'file_name' => $file['name'], 'file_size' => $file['size'], 'mime' => $file['mime']];
        }
        try {
            Document::update($id, $data);
        } catch (\Throwable $e) {
            if ($file !== null) {
                Uploader::deletePrivate($file['path']);        // keep the old file; drop the new orphan
            }
            throw $e;
        }
        if ($file !== null) {
            Uploader::deletePrivate((string) $row['file_path']);   // only after the new file is safely recorded
        }

        $changed = [];
        foreach (['title', 'category', 'year', 'description', 'published_at', 'is_public', 'is_active'] as $f) {
            if ((string) ($row[$f] ?? '') !== (string) ($data[$f] ?? '')) {
                $changed[] = $f;
            }
        }
        if ($file !== null) {
            $changed[] = 'file';
        }
        $this->audit('document.update', 'document', $id, 'Edited document "' . $data['title'] . '"', ['fields' => $changed, 'replaced_file' => $file !== null]);
        Flash::success('Document "' . $data['title'] . '" was saved' . ($file !== null ? ' and its file was replaced.' : '.'));
        return $this->redirect('admin/documents');
    }

    /** Show / hide on the website (POST). */
    public function toggle(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = Document::findOrFail($id);
        $new = (int) $row['is_active'] === 1 ? 0 : 1;
        Document::update($id, ['is_active' => $new]);
        $this->audit('document.' . ($new ? 'activate' : 'deactivate'), 'document', $id, ($new ? 'Activated' : 'Deactivated') . ' document "' . $row['title'] . '"');
        Flash::success('"' . $row['title'] . '" is now ' . ($new ? 'active' : 'inactive') . '.');
        return Response::redirect($this->returnUrl());
    }

    /** Admin download of the stored file (auth-gated; does not touch the public download counter). */
    public function download(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = Document::findOrFail($id);
        $name = (string) ($row['file_name'] ?: $row['title']);
        $this->audit('document.download', 'document', $id, 'Downloaded document "' . $row['title'] . '" (admin)');
        return Uploader::download((string) $row['file_path'], $name);
    }

    public function bulk(Request $request): Response
    {
        $this->authorize('content.manage');
        $ids = $this->postedIds();
        $action = $request->string('bulk_action');
        $back = Response::redirect($this->returnUrl());
        if ($ids === []) {
            Flash::warning('Select at least one document first.');
            return $back;
        }
        $map = [
            'activate'   => ['is_active', 1, 'activated'],
            'deactivate' => ['is_active', 0, 'deactivated'],
            'public'     => ['is_public', 1, 'made public'],
            'private'    => ['is_public', 0, 'made private'],
        ];
        if (isset($map[$action])) {
            [$col, $val, $word] = $map[$action];
            $this->setFlag('documents', $col, $val, $ids);
            $this->audit('document.bulk', 'document', null, ucfirst($word) . ' ' . count($ids) . ' documents', ['ids' => $ids, 'action' => $action]);
            Flash::success($this->plural(count($ids), 'document', 'documents') . ' ' . $word . '.');
        } elseif ($action === 'delete') {
            $this->authorize('content.delete');
            $n = $this->deleteMany($ids);
            Flash::success($this->plural($n, 'document', 'documents') . ' deleted.');
        } else {
            Flash::error('Choose an action to apply to the selected documents.');
        }
        return $back;
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('content.delete');
        $row = Document::findOrFail($id);
        $this->deleteMany([$id]);
        Flash::success('Document "' . $row['title'] . '" was deleted.');
        return Response::redirect($this->returnUrl());
    }

    // ------------------------------------------------------------------ internals

    /** @param int[] $ids */
    private function deleteMany(array $ids): int
    {
        $n = 0;
        foreach ($ids as $id) {
            $row = Document::find($id);
            if ($row === null) {
                continue;
            }
            Document::delete($id);
            Uploader::deletePrivate((string) $row['file_path']);
            $this->audit('document.delete', 'document', $id, 'Deleted document "' . $row['title'] . '"', ['file' => $row['file_name']]);
            $n++;
        }
        return $n;
    }

    private function fileExists(string $rel): bool
    {
        try {
            return Uploader::privatePath($rel) !== '';
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Validate the text fields. Returns the columns to save (the file columns are added by the caller).
     * @return array<string,mixed>
     */
    private function collect(string $redirectTo): array
    {
        $r = $this->request;
        $maxYear = (int) date('Y') + 1;
        $v = Validator::make($r->all(), [
            'title'        => 'required|max:220',
            'category'     => 'required|in:' . implode(',', array_keys(Document::CATEGORIES)),
            'year'         => 'nullable|integer|between:1990,' . $maxYear,
            'description'  => 'nullable|max:2000',
            'published_at' => 'nullable|date',
        ], [
            'category.required' => 'Choose a category.',
            'category.in'       => 'Choose one of the listed categories.',
            'year.integer'      => 'The year must be a number such as ' . date('Y') . '.',
            'year.between'      => 'The year must be between 1990 and ' . $maxYear . '.',
            'published_at.date' => 'Enter the date as YYYY-MM-DD.',
        ], ['title' => 'Title', 'description' => 'Description']);
        $errors = $v->errors();
        $this->requireText($errors, 'title', $this->clean($r->string('title')), 'Title is required.');
        if ($errors !== []) {
            $this->fail($errors, $redirectTo);
        }
        $year = $r->string('year');
        return [
            'title'        => $this->clean($r->string('title')),
            'category'     => $r->string('category'),
            'year'         => $year === '' ? null : (int) $year,
            'description'  => $this->clean($r->string('description'), true) ?: null,
            'published_at' => $r->string('published_at') ?: null,
            'is_public'    => $this->flag('is_public'),
            'is_active'    => $this->flag('is_active'),
        ];
    }

    /**
     * Store the uploaded document privately. Throws UploadException (safe to show) on any problem.
     * @return array{path:string,name:string,size:int,mime:string}
     */
    private function storeUpload(?array $file): array
    {
        if ($file === null) {
            throw new UploadException('Choose the file to upload.');
        }
        $ext = strtolower((string) pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($ext, self::EXTS, true)) {
            throw new UploadException('That file type is not allowed. Allowed: ' . strtoupper(implode(', ', self::EXTS)) . '.');
        }
        if ($ext === 'zip') {
            return $this->storeZip($file);
        }
        return Uploader::privateFile($file, 'documents', array_values(array_diff(self::EXTS, ['zip'])), self::MAX_BYTES);
    }

    /**
     * ZIP archives (Uploader::privateFile has no zip signature). Same layout and rules as Uploader::privateFile():
     * storage/private/documents/YYYY/MM/<random>.zip, mode 0640, client name never used on disk.
     * @return array{path:string,name:string,size:int,mime:string}
     */
    private function storeZip(array $file): array
    {
        $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err !== UPLOAD_ERR_OK) {
            throw new UploadException(match ($err) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'That file is larger than the server allows.',
                UPLOAD_ERR_PARTIAL                        => 'The upload was interrupted. Please try again.',
                UPLOAD_ERR_NO_FILE                        => 'Choose the file to upload.',
                default                                   => 'The file could not be uploaded (error ' . $err . ').',
            });
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ((PHP_SAPI !== 'cli' && !is_uploaded_file($tmp)) || !is_file($tmp)) {
            throw new UploadException('Invalid upload.');
        }
        $size = (int) filesize($tmp);
        if ($size <= 0) {
            throw new UploadException('That file is empty.');
        }
        if ($size > self::MAX_BYTES) {
            throw new UploadException('That file is too large (maximum ' . human_filesize(self::MAX_BYTES) . ').');
        }
        $name = (string) ($file['name'] ?? '');
        $parts = explode('.', strtolower(basename(str_replace(chr(92), '/', $name))));
        array_pop($parts);
        foreach ($parts as $p) {
            if (in_array(trim($p), self::BAD_PARTS, true)) {
                throw new UploadException('That file name is not allowed.');
            }
        }
        $h = fopen($tmp, 'rb');
        $head = $h !== false ? (string) fread($h, 4) : '';
        if ($h !== false) {
            fclose($h);
        }
        $sniffed = '';
        if (function_exists('finfo_open')) {
            $fi = finfo_open(FILEINFO_MIME_TYPE);
            $sniffed = $fi ? strtolower((string) finfo_file($fi, $tmp)) : '';
        }
        if ($head !== "PK\x03\x04" || ($sniffed !== '' && !in_array($sniffed, ['application/zip', 'application/x-zip-compressed', 'application/octet-stream'], true))) {
            throw new UploadException('That file does not look like a valid .zip archive.');
        }

        $rel = 'documents/' . date('Y') . '/' . date('m');
        $dir = STORAGE_PATH . '/private/' . $rel;
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new UploadException('The upload folder is not writable.');
        }
        $stored = bin2hex(random_bytes(16)) . '.zip';
        $ok = PHP_SAPI === 'cli' ? @copy($tmp, $dir . '/' . $stored) : @move_uploaded_file($tmp, $dir . '/' . $stored);
        if (!$ok) {
            throw new UploadException('The file could not be saved.');
        }
        @chmod($dir . '/' . $stored, 0640);

        $clean = trim((string) preg_replace('/[^A-Za-z0-9._ \-()]+/', '_', basename($name)));
        return [
            'path' => $rel . '/' . $stored,
            'name' => mb_substr($clean !== '' ? $clean : 'archive.zip', 0, 190),
            'size' => $size,
            'mime' => 'application/zip',
        ];
    }

    /** Largest upload the form can really accept: 20 MB, or less when php.ini (upload_max_filesize / post_max_size) is stricter. */
    private function effectiveMaxBytes(): int
    {
        $limits = [self::MAX_BYTES];
        foreach (['upload_max_filesize', 'post_max_size'] as $key) {
            $bytes = $this->iniBytes((string) ini_get($key));
            if ($bytes > 0) {
                $limits[] = $bytes;
            }
        }
        return min($limits);
    }

    private function iniBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '' || !preg_match('/^(\d+)\s*([KMG]?)/i', $value, $m)) {
            return 0;
        }
        $n = (int) $m[1];
        return match (strtoupper($m[2])) {
            'G' => $n * 1024 * 1024 * 1024,
            'M' => $n * 1024 * 1024,
            'K' => $n * 1024,
            default => $n,
        };
    }
}
