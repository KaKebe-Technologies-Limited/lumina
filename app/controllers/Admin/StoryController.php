<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\Sanitizer;
use App\Core\Slug;
use App\Core\UploadException;
use App\Core\Uploader;
use App\Core\Validator;
use App\Models\Story;

/**
 * Admin > Impact stories (/admin/stories). OWNER: B8 "org-admin".
 *
 * CONSENT RULE (privacy): a story is published when is_active = 1, and it can only be active while consent_confirmed = 1.
 *   - the form saves a story as a DRAFT (is_active = 0, with a warning) whenever the consent box is not ticked, so withdrawing
 *     consent takes a live story offline immediately;
 *   - bulk "Publish" and the quick toggle skip stories without consent (consent is NEVER confirmed in bulk, only per story);
 *   - the public side additionally filters on consent_confirmed = 1 (Story::published()).
 * Every consent change is written to the audit log (story.consent) with who/when.
 */
final class StoryController extends OrgAdminController
{
    protected function area(): string
    {
        return 'admin/stories';
    }

    /** @return array<string,string[]> */
    private function sortable(): array
    {
        return [
            'added'     => ['created_at'],
            'title'     => ['title'],
            'person'    => ['person_name', 'title'],
            'published' => ['published_at'],
            'status'    => ['is_active', 'title'],
        ];
    }

    public function index(Request $request): Response
    {
        $this->authorize('content.manage');
        $st = $this->listState($this->sortable(), 'added', 'desc');

        $where = [];
        $params = [];
        [$sql, $sp] = $this->searchSql(['title', 'summary', 'person_name', 'person_role', 'location'], $st['q']);
        if ($sql !== '') {
            $where[] = $sql;
            $params = array_merge($params, $sp);
        }
        $state = $request->string('state');
        switch ($state) {
            case 'published':
                $where[] = 'is_active = 1 AND consent_confirmed = 1';
                break;
            case 'draft':
                $where[] = 'is_active = 0 AND consent_confirmed = 1';
                break;
            case 'no_consent':
                $where[] = 'consent_confirmed = 0';
                break;
            default:
                $state = '';
        }

        $pager = $this->paged('stories' . $this->whereSql($where), $params, '*', $this->orderSql($this->sortable(), $st['sort'], $st['dir']));
        $counts = Database::one(
            'SELECT COUNT(*) AS total, COALESCE(SUM(is_active = 1 AND consent_confirmed = 1), 0) AS published, '
            . 'COALESCE(SUM(is_active = 0 AND consent_confirmed = 1), 0) AS draft, COALESCE(SUM(consent_confirmed = 0), 0) AS no_consent FROM stories'
        ) ?? ['total' => 0, 'published' => 0, 'draft' => 0, 'no_consent' => 0];

        return $this->render('admin/stories/index', [
            'pager'    => $pager,
            'q'        => $st['q'],
            'sort'     => $st['sort'],
            'dir'      => $st['dir'],
            'state'    => $state,
            'filtered' => $st['q'] !== '',
            'counts'   => array_map('intval', $counts),
            'now'      => date('Y-m-d H:i:s'),
        ], ['title' => 'Impact stories', 'nav' => 'stories', 'breadcrumbs' => [['Impact stories', null]]]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('content.manage');
        return $this->render('admin/stories/create', [
            'story' => ['is_active' => 0, 'consent_confirmed' => 0, 'published_at' => ''],
        ], ['title' => 'Add story', 'nav' => 'stories', 'breadcrumbs' => [['Impact stories', url('admin/stories')], ['Add', null]]]);
    }

    public function store(Request $request): Response
    {
        $this->authorize('content.manage');
        $to = 'admin/stories/create';
        $data = $this->collect(null, $to);
        try {
            $data['image'] = Uploader::resolveImageField($request, 'image', null);
        } catch (UploadException $e) {
            $this->fail(['image' => $e->getMessage()], $to);
        }
        if ($data['image'] === null) {
            $data['image_alt'] = null;
        }
        $id = Story::create($data);
        $this->audit('story.create', 'story', $id, 'Created story "' . $data['title'] . '"', ['published' => (bool) $data['is_active'], 'consent_confirmed' => (bool) $data['consent_confirmed']]);
        if ($data['consent_confirmed']) {
            $this->audit('story.consent', 'story', $id, 'Consent confirmed for story "' . $data['title'] . '"', ['person' => $data['person_name'], 'state' => 'confirmed']);
        }
        $this->flashSaved($data, 'created');
        return $this->redirect('admin/stories');
    }

    public function edit(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $story = Story::findOrFail($id);
        return $this->render('admin/stories/edit', [
            'story' => $story,
            'now'   => date('Y-m-d H:i:s'),
        ], ['title' => 'Edit story', 'nav' => 'stories', 'breadcrumbs' => [['Impact stories', url('admin/stories')], [$story['title'], null]]]);
    }

    public function update(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = Story::findOrFail($id);
        $to = 'admin/stories/' . $id . '/edit';
        $data = $this->collect($row, $to);
        try {
            $data['image'] = Uploader::resolveImageField($request, 'image', $row['image'] ?? null);
        } catch (UploadException $e) {
            $this->fail(['image' => $e->getMessage()], $to);
        }
        if ($data['image'] === null) {
            $data['image_alt'] = null;
        }
        Story::update($id, $data);

        $changed = [];
        foreach (['title', 'slug', 'summary', 'body', 'image', 'image_alt', 'person_name', 'person_role', 'location', 'consent_confirmed', 'is_active', 'published_at', 'seo_title', 'seo_description'] as $f) {
            if ((string) ($row[$f] ?? '') !== (string) ($data[$f] ?? '')) {
                $changed[] = $f;
            }
        }
        $this->audit('story.update', 'story', $id, 'Edited story "' . $data['title'] . '"', ['fields' => $changed, 'published' => (bool) $data['is_active']]);
        if ((int) $row['consent_confirmed'] !== (int) $data['consent_confirmed']) {
            $this->audit('story.consent', 'story', $id, ($data['consent_confirmed'] ? 'Consent confirmed' : 'Consent withdrawn') . ' for story "' . $data['title'] . '"', [
                'person' => $data['person_name'], 'state' => $data['consent_confirmed'] ? 'confirmed' : 'withdrawn',
            ]);
        }
        $this->flashSaved($data, 'saved');
        return $this->redirect('admin/stories');
    }

    /** Publish / unpublish (POST). Publishing needs confirmed consent. */
    public function toggle(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = Story::findOrFail($id);
        if ((int) $row['is_active'] === 1) {
            Story::update($id, ['is_active' => 0]);
            $this->audit('story.unpublish', 'story', $id, 'Unpublished story "' . $row['title'] . '"');
            Flash::success('"' . $row['title'] . '" is now unpublished.');
        } elseif ((int) $row['consent_confirmed'] !== 1) {
            Flash::error('"' . $row['title'] . '" cannot be published: open the story and confirm consent first.');
        } else {
            Story::update($id, ['is_active' => 1, 'published_at' => $row['published_at'] ?: date('Y-m-d H:i:s')]);
            $this->audit('story.publish', 'story', $id, 'Published story "' . $row['title'] . '"');
            Flash::success('"' . $row['title'] . '" is now published.');
        }
        return Response::redirect($this->returnUrl());
    }

    public function bulk(Request $request): Response
    {
        $this->authorize('content.manage');
        $ids = $this->postedIds();
        $action = $request->string('bulk_action');
        $back = Response::redirect($this->returnUrl());
        if ($ids === []) {
            Flash::warning('Select at least one story first.');
            return $back;
        }
        switch ($action) {
            case 'publish':
                $in = implode(',', array_fill(0, count($ids), '?'));
                $n = Database::execute(
                    'UPDATE stories SET is_active = 1, published_at = COALESCE(published_at, NOW()) WHERE consent_confirmed = 1 AND is_active = 0 AND id IN (' . $in . ')',
                    $ids
                );
                $blocked = (int) Database::value('SELECT COUNT(*) FROM stories WHERE consent_confirmed = 0 AND id IN (' . $in . ')', $ids, 0);
                $this->audit('story.bulk', 'story', null, 'Published ' . $n . ' stories', ['ids' => $ids, 'action' => 'publish', 'skipped_no_consent' => $blocked]);
                if ($n > 0) {
                    Flash::success($this->plural($n, 'story', 'stories') . ' published.');
                }
                if ($blocked > 0) {
                    Flash::warning($this->plural($blocked, 'story was', 'stories were') . ' not published because consent has not been confirmed. Open each story to confirm consent.');
                }
                if ($n === 0 && $blocked === 0) {
                    Flash::info('Nothing to publish: the selected stories are already published.');
                }
                break;
            case 'unpublish':
                $this->setFlag('stories', 'is_active', 0, $ids);
                $this->audit('story.bulk', 'story', null, 'Unpublished ' . count($ids) . ' stories', ['ids' => $ids, 'action' => 'unpublish']);
                Flash::success($this->plural(count($ids), 'story', 'stories') . ' unpublished.');
                break;
            case 'delete':
                $this->authorize('content.delete');
                $n = $this->deleteMany($ids);
                Flash::success($this->plural($n, 'story', 'stories') . ' deleted.');
                break;
            default:
                Flash::error('Choose an action to apply to the selected stories.');
        }
        return $back;
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('content.delete');
        $row = Story::findOrFail($id);
        $this->deleteMany([$id]);
        Flash::success('Story "' . $row['title'] . '" was deleted.');
        return $this->redirect('admin/stories');
    }

    // ------------------------------------------------------------------ internals

    /** @param int[] $ids */
    private function deleteMany(array $ids): int
    {
        $n = 0;
        foreach ($ids as $id) {
            $row = Story::find($id);
            if ($row === null) {
                continue;
            }
            Story::delete($id);
            Uploader::delete($row['image'] ?? null);
            $this->audit('story.delete', 'story', $id, 'Deleted story "' . $row['title'] . '"');
            $n++;
        }
        return $n;
    }

    /** @param array<string,mixed> $data */
    private function flashSaved(array $data, string $verb): void
    {
        Flash::success('Story "' . $data['title'] . '" was ' . $verb . '.');
        if (!empty($data['_forced_draft'])) {
            Flash::warning('It was saved as a draft: a story can only be published after you confirm that the person (or their parent or guardian) has given consent.');
        } elseif ((int) $data['is_active'] === 0) {
            Flash::info('This story is not published, so visitors cannot see it yet.');
        }
    }

    /**
     * Validate the form. Returns the columns to save (the image is resolved by the caller after validation).
     * The helper key "_forced_draft" is only a signal for the flash message; Model::create()/update() drop unknown columns.
     * @param array<string,mixed>|null $row current row when editing
     * @return array<string,mixed>
     */
    private function collect(?array $row, string $redirectTo): array
    {
        $r = $this->request;
        $id = $row !== null ? (int) $row['id'] : null;
        $v = Validator::make($r->all(), [
            'title'           => 'required|max:220',
            'summary'         => 'nullable|max:500',
            'person_name'     => 'nullable|max:150',
            'person_role'     => 'nullable|max:150',
            'location'        => 'nullable|max:190',
            'published_at'    => 'nullable|datetime',
            'image_alt'       => 'nullable|max:250',
            'seo_title'       => 'nullable|max:190',
            'seo_description' => 'nullable|max:300',
        ], [
            'published_at.datetime' => 'Choose a valid publish date and time, or leave it empty.',
        ], ['title' => 'Title', 'summary' => 'Summary', 'person_name' => 'Name', 'person_role' => 'Role', 'location' => 'Location', 'image_alt' => 'Image description', 'seo_title' => 'SEO title', 'seo_description' => 'SEO description']);
        $errors = $v->errors();
        $this->requireText($errors, 'title', $this->clean($r->string('title')), 'Title is required.');

        $typedSlug = $r->string('slug');
        $slug = Slug::make($typedSlug);
        if ($typedSlug !== '' && $slug === '') {
            $errors['slug'] = 'The web address may only contain letters, numbers and hyphens.';
        } elseif ($slug !== '' && (int) Database::value('SELECT COUNT(*) FROM stories WHERE slug = ?' . ($id !== null ? ' AND id <> ?' : ''), $id !== null ? [$slug, $id] : [$slug], 0) > 0) {
            $errors['slug'] = 'That web address is already used by another story.';
        }

        if (!isset($errors['image_alt']) && $this->imageWillExist('image', $row['image'] ?? null) && $this->clean($r->string('image_alt')) === '') {
            $errors['image_alt'] = 'Describe the image for people who cannot see it (alt text). Describe what is visible; do not name the person unless they agreed to it.';
        }
        if ($errors !== []) {
            $this->fail($errors, $redirectTo);
        }
        if ($slug === '') {
            $slug = Slug::unique('stories', $r->string('title'), $id, 'slug', [], 'story');
        }

        $consent = $this->flag('consent_confirmed');
        $wantsPublish = $this->flag('is_active');
        $active = ($consent === 1 && $wantsPublish === 1) ? 1 : 0;            // the consent rule: no consent, no publishing
        $publishedAt = $this->dbDateTime($r->string('published_at'));
        if ($active === 1 && $publishedAt === null) {
            $publishedAt = ($row['published_at'] ?? null) ?: date('Y-m-d H:i:s');
        }
        $body = $r->post('body', '');

        return [
            'slug'              => $slug,
            'title'             => $this->clean($r->string('title')),
            'summary'           => $this->clean($r->string('summary'), true) ?: null,
            'body'              => Sanitizer::html(is_string($body) ? $body : '') ?: null,
            'image_alt'         => $this->clean($r->string('image_alt')) ?: null,
            'person_name'       => $this->clean($r->string('person_name')) ?: null,
            'person_role'       => $this->clean($r->string('person_role')) ?: null,
            'location'          => $this->clean($r->string('location')) ?: null,
            'consent_confirmed' => $consent,
            'is_active'         => $active,
            'published_at'      => $publishedAt,
            'seo_title'         => $this->clean($r->string('seo_title')) ?: null,
            'seo_description'   => $this->clean($r->string('seo_description')) ?: null,
            '_forced_draft'     => $wantsPublish === 1 && $consent !== 1,
        ];
    }
}
