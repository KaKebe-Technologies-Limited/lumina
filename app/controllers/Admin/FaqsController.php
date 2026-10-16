<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Models\Faq;

/**
 * Frequently asked questions (table `faqs`, grouped by group_key: 'general', 'donate', ...). OWNER: B3 admin-core.
 * Ability content.manage (delete: content.delete). Answers are stored as PLAIN TEXT (blank line = new paragraph); the public pages
 * render them with nl2p(). Routes mirror StatsController.
 */
final class FaqsController extends AdminController
{
    /** group => where it appears (help text). */
    private const GROUP_HINTS = [
        'donate'  => 'These questions appear on the Donate page.',
        'general' => 'Not shown anywhere on the website: only the "donate" group has a place on a page. (Programs, Involvement and Careers keep their questions in Pages.)',
    ];

    public function index(Request $request): Response
    {
        $this->authorize('content.manage');
        $groups = $this->groups();
        $group = $request->string('group');
        if ($group === '' || !in_array($group, $groups, true)) {
            $group = $groups[0];
        }
        return $this->render('admin/faqs/index', [
            'groups'    => $groups,
            'group'     => $group,
            'counts'    => $this->counts(),
            'rows'      => Faq::where(['group_key' => $group], 'sort_order ASC, id ASC'),
            'hint'      => self::GROUP_HINTS[$group] ?? 'Questions in this group.',
            'canDelete' => can('content.delete'),
        ], ['title' => 'FAQs', 'nav' => 'faqs', 'breadcrumbs' => [['FAQs', null]]]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('content.manage');
        $group = $request->string('group', 'donate');
        $group = preg_match('/^[a-z0-9_-]{1,50}$/', $group) ? $group : 'donate';
        $max = (int) Database::value('SELECT COALESCE(MAX(sort_order), 0) FROM faqs WHERE group_key = ?', [$group], 0);
        return $this->form('create', ['group_key' => $group, 'question' => '', 'answer' => '', 'sort_order' => $max + 10, 'is_active' => 1]);
    }

    public function store(Request $request): Response
    {
        $this->authorize('content.manage');
        $data = $this->validated('admin/faqs/create');
        $id = Faq::create($data);
        $this->audit('faq.create', 'faq', $id, 'Added FAQ "' . mb_substr($data['question'], 0, 80) . '"');
        Flash::success('Question added.');
        return $this->redirect('admin/faqs?group=' . rawurlencode($data['group_key']));
    }

    public function edit(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        return $this->form('edit', Faq::findOrFail($id));
    }

    public function update(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        Faq::findOrFail($id);
        $data = $this->validated('admin/faqs/' . $id . '/edit');
        Faq::update($id, $data);
        $this->audit('faq.update', 'faq', $id, 'Edited FAQ "' . mb_substr($data['question'], 0, 80) . '"');
        Flash::success('Question saved.');
        return $this->redirect('admin/faqs?group=' . rawurlencode($data['group_key']));
    }

    public function delete(Request $request, int $id): Response
    {
        $this->authorize('content.delete');
        $row = Faq::findOrFail($id);
        Faq::delete($id);
        $this->audit('faq.delete', 'faq', $id, 'Deleted FAQ "' . mb_substr((string) $row['question'], 0, 80) . '"');
        Flash::success('Question deleted.');
        return $this->redirect('admin/faqs?group=' . rawurlencode((string) $row['group_key']));
    }

    public function toggle(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = Faq::findOrFail($id);
        $on = (int) $row['is_active'] === 1 ? 0 : 1;
        Faq::update($id, ['is_active' => $on]);
        $this->audit('faq.update', 'faq', $id, ($on ? 'Showed' : 'Hid') . ' FAQ "' . mb_substr((string) $row['question'], 0, 80) . '"');
        Flash::success('The question is now ' . ($on ? 'shown.' : 'hidden.'));
        return $this->redirect('admin/faqs?group=' . rawurlencode((string) $row['group_key']));
    }

    public function reorder(Request $request): Response
    {
        $this->authorize('content.manage');
        $ids = $request->post('ids', []);
        $group = $request->string('group');
        $ids = is_array($ids) ? array_values(array_filter(array_map('intval', $ids))) : [];
        Database::transaction(static function () use ($ids, $group): void {
            $pos = 10;
            foreach ($ids as $id) {
                Database::execute('UPDATE faqs SET sort_order = ? WHERE id = ? AND group_key = ?', [$pos, $id, $group]);
                $pos += 10;
            }
        });
        $this->audit('faq.reorder', 'faq', null, 'Re-ordered the FAQs of group "' . $group . '"');
        Flash::success('The new order is saved.');
        return $this->redirect('admin/faqs?group=' . rawurlencode($group));
    }

    // ------------------------------------------------------------------ internals

    /** @return array<string,int> group => number of questions */
    private function counts(): array
    {
        $out = [];
        foreach (Database::all('SELECT group_key, COUNT(*) AS n FROM faqs GROUP BY group_key') as $r) {
            $out[(string) $r['group_key']] = (int) $r['n'];
        }
        return $out;
    }

    /** @return list<string> */
    private function groups(): array
    {
        $groups = array_keys($this->counts());
        if (!in_array('donate', $groups, true)) {
            $groups[] = 'donate';
        }
        usort($groups, static fn(string $a, string $b): int => [$a === 'donate' ? 0 : 9, $a] <=> [$b === 'donate' ? 0 : 9, $b]);
        return $groups;
    }

    private function form(string $mode, array $row): Response
    {
        return $this->render('admin/faqs/form', [
            'mode'   => $mode,
            'row'    => $row,
            'groups' => $this->groups(),
            'hints'  => self::GROUP_HINTS,
        ], [
            'title'       => $mode === 'create' ? 'Add a question' : 'Edit question',
            'nav'         => 'faqs',
            'breadcrumbs' => [['FAQs', url('admin/faqs')], [$mode === 'create' ? 'Add' : 'Edit', null]],
        ]);
    }

    /** @return array<string,mixed> */
    private function validated(string $redirect): array
    {
        $d = $this->validate([
            'group_key'  => 'required|regex:/^[a-z0-9_-]{1,50}$/',
            'question'   => 'required|max:300',
            'answer'     => 'required|max:8000',
            'sort_order' => 'nullable|integer|between:0,100000',
        ], [
            'group_key.regex' => 'Use lowercase letters, numbers, hyphens or underscores (for example "donate").',
        ], ['group_key' => 'Group', 'question' => 'Question', 'answer' => 'Answer'], $redirect);
        $answer = trim(str_replace(["\r\n", "\r"], "\n", strip_tags((string) $d['answer'])));
        return [
            'group_key'  => (string) $d['group_key'],
            'question'   => trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $d['question']))),
            'answer'     => $answer,
            'sort_order' => ($d['sort_order'] ?? '') !== '' ? (int) $d['sort_order'] : 0,
            'is_active'  => $this->request->string('is_active') === '1' ? 1 : 0,
        ];
    }
}
