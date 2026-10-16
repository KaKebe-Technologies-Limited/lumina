<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Content\CrudController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\Sanitizer;
use App\Core\UploadException;
use App\Core\Uploader;
use App\Services\ImageCleanup;

/**
 * Admin for `gallery_images` (/admin/gallery): multi-file drag-and-drop upload with progress, albums, captions and alt text,
 * drag / keyboard re-ordering, bulk actions (show, hide, move to album, delete) and per-picture editing. OWNER: B4.
 * The list, edit, update, delete and toggle come from Content\CrudController; this class adds upload / reorder / bulk / quick.
 */
final class GalleryController extends CrudController
{
    private const PER_PAGE = [24, 48, 96, 192];

    protected function config(): array
    {
        return [
            'entity' => 'gallery', 'table' => 'gallery_images', 'singular' => 'picture', 'plural' => 'pictures', 'title' => 'Gallery',
            'path' => 'admin/gallery', 'views' => 'admin/gallery', 'nav' => 'gallery', 'title_col' => 'alt_text',
            'search' => ['alt_text', 'caption', 'album'],
            'sorts' => [
                'order'  => 't.sort_order {dir}, t.id DESC',
                'newest' => 't.id {dir}',
                'album'  => 't.album {dir}, t.sort_order ASC, t.id DESC',
            ],
            'default_sort' => ['order', 'asc'],
            'order_sql' => 'sort_order ASC, id DESC',
            'reorderable' => true,
            'reorder_filtered' => true,          // the reorder endpoint merges any subset of pictures into the global order
            'public' => static fn(array $r): ?string => null,
        ];
    }

    /** Grids want more tiles per page than tables. */
    protected function perPage(): int
    {
        $n = $this->request->int('per_page', 48);
        return in_array($n, self::PER_PAGE, true) ? $n : 48;
    }

    protected function imageFields(): array
    {
        return ['image' => ''];
    }

    protected function requiredImages(): array
    {
        return ['image'];
    }

    protected function rowLabel(array $row): string
    {
        $label = trim((string) ($row['caption'] ?? '')) ?: trim((string) ($row['alt_text'] ?? ''));
        return $label !== '' ? mb_strimwidth($label, 0, 60, '…') : 'Picture #' . (int) ($row['id'] ?? 0);
    }

    protected function defaults(): array
    {
        return ['is_active' => 1, 'sort_order' => $this->nextSortOrder(), 'album' => $this->request->string('album')];
    }

    protected function tabs(): array
    {
        return [
            'all'      => ['label' => 'All', 'sql' => null, 'params' => []],
            'shown'    => ['label' => 'Shown', 'sql' => 't.is_active = 1', 'params' => []],
            'hidden'   => ['label' => 'Hidden', 'sql' => 't.is_active = 0', 'params' => []],
            'noalt'    => ['label' => 'Needs a description', 'sql' => "t.alt_text = ''", 'params' => []],
        ];
    }

    protected function applyFilters(Request $request, array &$where, array &$params): array
    {
        $album = mb_substr($request->string('album'), 0, 100);
        if ($album === '__none') {
            $where[] = "(t.album IS NULL OR t.album = '')";
            return ['album' => '__none'];
        }
        if ($album !== '') {
            $where[] = 't.album = ?';
            $params[] = $album;
            return ['album' => $album];
        }
        return [];
    }

    protected function listData(): array
    {
        return ['albums' => $this->albums(), 'perPageOptions' => self::PER_PAGE, 'maxBytes' => (int) config('upload.max_image_bytes', 5 * 1024 * 1024)];
    }

    protected function formData(?array $row): array
    {
        return ['albums' => $this->albums()];
    }

    /** @return array<string,int> album name => number of pictures */
    private function albums(): array
    {
        $out = [];
        foreach (Database::all("SELECT album, COUNT(*) AS n FROM gallery_images WHERE album IS NOT NULL AND album <> '' GROUP BY album ORDER BY album ASC") as $r) {
            $out[(string) $r['album']] = (int) $r['n'];
        }
        return $out;
    }

    // ------------------------------------------------------------------ create = upload page

    public function create(Request $request): Response
    {
        $this->authorize('content.manage');
        return Response::redirect(url('admin/gallery') . '#upload');
    }

    protected function rules(?array $row): array
    {
        return [
            'rules' => [
                'alt_text'   => 'required|max:250',
                'caption'    => 'nullable|max:250',
                'album'      => 'nullable|max:100',
                'is_active'  => 'nullable|boolean',
                'sort_order' => 'nullable|integer|between:-100000,100000',
            ],
            'labels' => ['alt_text' => 'Picture description', 'caption' => 'Caption', 'album' => 'Album', 'sort_order' => 'Display order'],
            'messages' => ['alt_text.required' => 'Describe the picture in a few words so people who cannot see it understand it.'],
        ];
    }

    protected function payload(Request $request, ?array $row, array $valid): array
    {
        return [
            'alt_text'   => mb_substr(Sanitizer::text((string) $valid['alt_text']), 0, 250),
            'caption'    => $this->text($valid['caption']),
            'album'      => $this->text($valid['album']),
            'is_active'  => $request->bool('is_active') ? 1 : 0,
            'sort_order' => $request->int('sort_order', 0),
        ];
    }

    /** POST /admin/gallery (no-JS fallback of the upload form). */
    public function store(Request $request): Response
    {
        return $this->upload($request);
    }

    // ------------------------------------------------------------------ upload (AJAX one file at a time, or a plain multi-file form)

    public function upload(Request $request): Response
    {
        $this->authorize('content.manage');
        $album = $this->text(mb_substr($request->string('album'), 0, 100));
        $files = array_merge($request->files('file'), $request->files('images'));
        $json = $request->expectsJson();

        if ($files === []) {
            if ($json) {
                return $this->json(['ok' => false, 'items' => [], 'errors' => [['name' => '', 'error' => 'Please choose at least one picture.']]], 422);
            }
            Flash::error('Please choose at least one picture to upload.');
            return Response::redirect(url('admin/gallery') . '#upload');
        }

        $items = [];
        $errors = [];
        foreach ($files as $file) {
            $name = basename((string) ($file['name'] ?? ''));
            try {
                $path = Uploader::image($file);
            } catch (UploadException $e) {
                $errors[] = ['name' => $name, 'error' => $e->getMessage()];
                continue;
            }
            try {
                $id = Database::insert('gallery_images', [
                    'image' => $path, 'alt_text' => '', 'caption' => null, 'album' => $album, 'is_active' => 1, 'sort_order' => $this->nextSortOrder(),
                ]);
            } catch (\Throwable $e) {
                ImageCleanup::release($path);
                throw $e;
            }
            $this->audit('gallery.upload', 'gallery', $id, 'Uploaded picture "' . $name . '"' . ($album ? ' to album "' . $album . '"' : ''));
            $items[] = ['id' => $id, 'name' => $name, 'url' => upload_url($path), 'album' => $album ?? ''];
        }

        if ($json) {
            return $this->json(['ok' => $items !== [], 'items' => $items, 'errors' => $errors], $items !== [] ? 201 : 422);
        }
        if ($items !== []) {
            Flash::success(count($items) . ' picture' . (count($items) === 1 ? ' was' : 's were') . ' uploaded. Add a short description to each one (it helps people who use screen readers).');
        }
        foreach ($errors as $err) {
            Flash::error($err['name'] . ': ' . $err['error']);
        }
        return Response::redirect(url('admin/gallery', ['status' => 'noalt']));
    }

    // ------------------------------------------------------------------ inline quick edit (alt text / caption / album) from the upload queue

    public function quick(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = $this->find($id);
        $set = [];
        $errors = [];
        if ($request->has('alt_text')) {
            $v = mb_substr(Sanitizer::text($request->string('alt_text')), 0, 250);
            $set['alt_text'] = $v;
        }
        if ($request->has('caption')) {
            $v = $this->text($request->string('caption'));
            if ($v !== null && mb_strlen($v) > 250) {
                $errors['caption'] = 'The caption can be up to 250 characters.';
            }
            $set['caption'] = $v;
        }
        if ($request->has('album')) {
            $v = $this->text($request->string('album'));
            if ($v !== null && mb_strlen($v) > 100) {
                $errors['album'] = 'The album name can be up to 100 characters.';
            }
            $set['album'] = $v;
        }
        if ($errors !== []) {
            return $this->json(['ok' => false, 'errors' => $errors], 422);
        }
        if ($set !== []) {
            Database::update('gallery_images', $set, 'id = ?', [$id]);
            $this->audit('gallery.update', 'gallery', $id, 'Edited picture details', ['fields' => array_keys($set)]);
        }
        return $this->json(['ok' => true, 'id' => $id, 'missing_alt' => (($set['alt_text'] ?? $row['alt_text']) === '')]);
    }

    // ------------------------------------------------------------------ ordering

    /** POST reorder: ids[] in the new visual order (may be a single page of the list). Re-numbers the whole table 10,20,30... */
    public function reorder(Request $request): Response
    {
        $this->authorize('content.manage');
        $ids = [];
        foreach ((array) $request->post('ids', []) as $v) {
            if (is_scalar($v) && ctype_digit((string) $v) && (int) $v > 0) {
                $ids[(int) $v] = (int) $v;
            }
        }
        $ids = array_values($ids);
        if ($ids === []) {
            return $this->json(['ok' => false, 'error' => 'Nothing to reorder.'], 422);
        }
        Database::transaction(function () use ($ids): void {
            $all = array_map('intval', array_column(Database::all('SELECT id FROM gallery_images ORDER BY sort_order ASC, id DESC'), 'id'));
            $known = array_flip($all);
            $valid = array_values(array_filter($ids, static fn(int $id): bool => isset($known[$id])));
            $slots = [];
            $inSet = array_flip($valid);
            foreach ($all as $pos => $id) {
                if (isset($inSet[$id])) {
                    $slots[] = $pos;
                }
            }
            foreach ($slots as $k => $pos) {
                $all[$pos] = $valid[$k];
            }
            $n = 10;
            foreach ($all as $id) {
                Database::execute('UPDATE gallery_images SET sort_order = ? WHERE id = ? AND sort_order <> ?', [$n, $id, $n]);
                $n += 10;
            }
        });
        $this->audit('gallery.reorder', 'gallery', null, 'Changed the order of the gallery');
        return $this->json(['ok' => true]);
    }

    // ------------------------------------------------------------------ bulk actions

    /** POST bulk: action = show | hide | album | delete, ids[] = selected pictures, album = target album for "album". */
    public function bulk(Request $request): Response
    {
        $this->authorize('content.manage');
        $ids = [];
        foreach ((array) $request->post('ids', []) as $v) {
            if (is_scalar($v) && ctype_digit((string) $v) && (int) $v > 0) {
                $ids[(int) $v] = (int) $v;
            }
        }
        $ids = array_values($ids);
        $back = $this->returnUrl($request, 'admin/gallery');
        if ($ids === []) {
            Flash::warning('Select at least one picture first.');
            return Response::redirect($back);
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        $action = $request->string('action');
        $n = count($ids);
        $what = $n . ' picture' . ($n === 1 ? '' : 's');

        switch ($action) {
            case 'show':
            case 'hide':
                $on = $action === 'show' ? 1 : 0;
                Database::execute("UPDATE gallery_images SET is_active = {$on} WHERE id IN ({$in})", $ids);
                $this->audit('gallery.' . ($on ? 'activate' : 'deactivate'), 'gallery', null, ($on ? 'Showed ' : 'Hid ') . $what, ['ids' => $ids]);
                Flash::success($what . ($on ? ' now shown on the website.' : ' now hidden from the website.'));
                break;

            case 'album':
                $album = $this->text(mb_substr($request->string('album'), 0, 100));
                Database::execute("UPDATE gallery_images SET album = ? WHERE id IN ({$in})", array_merge([$album], $ids));
                $this->audit('gallery.album', 'gallery', null, 'Moved ' . $what . ($album ? ' to album "' . $album . '"' : ' out of their album'), ['ids' => $ids]);
                Flash::success($what . ($album ? ' moved to the album "' . $album . '".' : ' removed from their album.'));
                break;

            case 'delete':
                $this->authorize('content.delete');
                $rows = Database::all("SELECT id, image FROM gallery_images WHERE id IN ({$in})", $ids);
                Database::execute("DELETE FROM gallery_images WHERE id IN ({$in})", $ids);
                ImageCleanup::releaseMany(array_column($rows, 'image'));
                $this->audit('gallery.delete', 'gallery', null, 'Deleted ' . count($rows) . ' picture(s)', ['ids' => array_column($rows, 'id')]);
                Flash::success(count($rows) . ' picture' . (count($rows) === 1 ? '' : 's') . ' deleted.');
                break;

            default:
                Flash::error('Please choose what to do with the selected pictures.');
        }
        return Response::redirect($back);
    }
}
