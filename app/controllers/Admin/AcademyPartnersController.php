<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\Academy\CourseService;
use App\Services\Academy\PartnerCourses;

/**
 * Admin > LAF Academy > Partner courses (administrators): the catalogue of free courses on other learning platforms. Add, edit, hide and delete entries, load the
 * starter list, and check that the links still work (an address that answers 404 or 410 is hidden from visitors until it is fixed). Docs: docs/ACADEMY.md.
 */
final class AcademyPartnersController extends AcademyAdminController
{
    private function load(int $id): array
    {
        $this->authorize('academy.manage');
        return PartnerCourses::find($id) ?? throw new HttpException(404, 'Not found');
    }

    public function index(Request $request): Response
    {
        $this->authorize('academy.manage');
        $q = trim($request->string('q'));
        $state = $request->string('state');
        $where = ['1 = 1'];
        $params = [];
        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_substr($q, 0, 80)) . '%';
            $where[] = '(title LIKE ? OR provider LIKE ? OR summary LIKE ?)';
            array_push($params, $like, $like, $like);
        }
        if ($state === 'hidden') {
            $where[] = 'is_active = 0';
        } elseif ($state === 'broken') {
            $where[] = '(link_status IN (0, 404, 410) OR link_status >= 500)';
        } elseif ($state === 'unchecked') {
            $where[] = 'link_status IS NULL';
        }
        $rows = Database::all('SELECT * FROM academy_partner_courses WHERE ' . implode(' AND ', $where) . ' ORDER BY provider ASC, sort_order ASC, title ASC LIMIT 500', $params);
        $starterPending = 0;
        $have = array_flip(array_column(Database::all('SELECT url_hash FROM academy_partner_courses'), 'url_hash'));
        foreach (PartnerCourses::starter() as $s) {
            $starterPending += isset($have[PartnerCourses::hash((string) ($s['url'] ?? ''))]) ? 0 : 1;
        }
        return $this->render('admin/academy/partners/index', [
            'rows' => $rows, 'q' => $q, 'state' => $state, 'total' => PartnerCourses::count(), 'starterPending' => $starterPending,
            'broken' => (int) Database::value('SELECT COUNT(*) FROM academy_partner_courses WHERE link_status IN (0, 404, 410) OR link_status >= 500', [], 0),
        ], ['title' => 'Partner courses', 'nav' => 'academy_partners', 'breadcrumbs' => $this->crumbs('Partner courses')]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('academy.manage');
        return $this->form(null, ['category' => 'basics', 'level' => 'beginner', 'cost' => 'free', 'language' => 'English', 'is_active' => 1, 'sort_order' => 100]);
    }

    public function edit(Request $request, int $id): Response
    {
        $row = $this->load($id);
        return $this->form($row, $row);
    }

    private function form(?array $course, array $row): Response
    {
        $isNew = $course === null;
        return $this->render('admin/academy/partners/form', [
            'row' => $row, 'course' => $course, 'isNew' => $isNew, 'action' => url($isNew ? 'admin/academy/partner-courses' : 'admin/academy/partner-courses/' . $course['id']),
            'categories' => CourseService::categoryLabels(), 'levels' => CourseService::LEVELS, 'costs' => array_map(static fn(array $c): string => $c[0], PartnerCourses::COSTS),
        ], ['title' => $isNew ? 'New partner course' : 'Edit partner course', 'nav' => 'academy_partners', 'breadcrumbs' => $this->crumbs('Partner courses', [$isNew ? 'New' : (string) $course['title'], null])]);
    }

    /** @return array<string,mixed> */
    private function collect(?array $course): array
    {
        $back = $course === null ? 'admin/academy/partner-courses/create' : 'admin/academy/partner-courses/' . $course['id'] . '/edit';
        $valid = $this->validate([
            'title' => 'required|max:190', 'provider' => 'required|max:80', 'url' => 'required|max:480', 'category' => 'required|in:' . implode(',', array_keys(CourseService::CATEGORIES)),
            'level' => 'required|in:' . implode(',', array_keys(CourseService::LEVELS)), 'duration' => 'nullable|max:60', 'summary' => 'nullable|max:320',
            'language' => 'nullable|max:40', 'cost' => 'required|in:' . implode(',', array_keys(PartnerCourses::COSTS)), 'certificate' => 'nullable|max:80', 'sort_order' => 'nullable|integer|between:0,9999',
        ], [], ['title' => 'Title', 'provider' => 'Platform', 'url' => 'Course address'], $back);
        $url = trim((string) $valid['url']);
        if (preg_match('~^https://[^\s"<>]{4,470}$~', $url) !== 1) {
            $this->fail(['url' => 'Enter the full course address starting with https://'], $back);
        }
        $hash = PartnerCourses::hash($url);
        $dup = Database::one('SELECT id FROM academy_partner_courses WHERE url_hash = ?' . ($course !== null ? ' AND id <> ' . (int) $course['id'] : ''), [$hash]);
        if ($dup !== null) {
            $this->fail(['url' => 'This address is already in the list.'], $back);
        }
        $changed = $course === null || PartnerCourses::hash((string) $course['url']) !== $hash;
        $data = [
            'title' => trim(strip_tags((string) $valid['title'])), 'provider' => trim(strip_tags((string) $valid['provider'])), 'url' => $url, 'url_hash' => $hash,
            'category' => (string) $valid['category'], 'level' => (string) $valid['level'], 'duration' => trim(strip_tags((string) $valid['duration'])) ?: null,
            'summary' => trim(strip_tags((string) $valid['summary'])) ?: null, 'language' => trim(strip_tags((string) $valid['language'])) ?: 'English', 'cost' => (string) $valid['cost'],
            'certificate' => trim(strip_tags((string) $valid['certificate'])) ?: null, 'sort_order' => (int) ($valid['sort_order'] ?? 100),
            'is_active' => $this->request->bool('is_active') ? 1 : 0, 'is_featured' => $this->request->bool('is_featured') ? 1 : 0,
        ];
        if ($changed) {
            $data['link_status'] = null;
            $data['link_checked_at'] = null;
        }
        return $data;
    }

    public function store(Request $request): Response
    {
        $this->authorize('academy.manage');
        $data = $this->collect(null) + ['source' => 'manual'];
        $id = Database::insert('academy_partner_courses', $data);
        $this->audit('academy.partner.create', 'academy_partner_course', $id, 'Added partner course "' . $data['title'] . '"');
        Flash::success('The partner course was added.');
        return $this->redirect('admin/academy/partner-courses');
    }

    public function update(Request $request, int $id): Response
    {
        $c = $this->load($id);
        $data = $this->collect($c);
        Database::update('academy_partner_courses', $data, 'id = ?', [$id]);
        $this->audit('academy.partner.update', 'academy_partner_course', $id, 'Edited partner course "' . $data['title'] . '"');
        Flash::success('The partner course was saved.');
        return $this->redirect('admin/academy/partner-courses');
    }

    public function toggle(Request $request, int $id): Response
    {
        $c = $this->load($id);
        $to = (int) $c['is_active'] === 1 ? 0 : 1;
        Database::update('academy_partner_courses', ['is_active' => $to], 'id = ?', [$id]);
        $this->audit('academy.partner.' . ($to ? 'show' : 'hide'), 'academy_partner_course', $id, ($to ? 'Showed' : 'Hid') . ' partner course "' . $c['title'] . '"');
        Flash::success($to ? 'The course is visible again.' : 'The course is hidden from visitors.');
        return $this->redirect('admin/academy/partner-courses');
    }

    public function delete(Request $request, int $id): Response
    {
        $c = $this->load($id);
        Database::execute('DELETE FROM academy_partner_courses WHERE id = ?', [$id]);
        $this->audit('academy.partner.delete', 'academy_partner_course', $id, 'Deleted partner course "' . $c['title'] . '"');
        Flash::success('The partner course was deleted.');
        return $this->redirect('admin/academy/partner-courses');
    }

    public function check(Request $request, int $id): Response
    {
        $c = $this->load($id);
        $s = PartnerCourses::check($c);
        in_array($s, [0, 404, 410], true) || $s >= 500 ? Flash::warning('"' . $c['title'] . '": ' . PartnerCourses::statusLabel($s) . '.') : Flash::success('"' . $c['title'] . '": ' . PartnerCourses::statusLabel($s) . '.');
        return $this->redirect('admin/academy/partner-courses');
    }

    public function checkAll(Request $request): Response
    {
        $this->authorize('academy.manage');
        $r = PartnerCourses::checkSome(25, 45);
        $this->audit('academy.partner.check', 'academy_partner_course', null, 'Checked ' . $r['checked'] . ' partner course links', $r);
        $r['checked'] === 0 ? Flash::info('There is nothing to check.') : Flash::success($r['checked'] . ' link' . ($r['checked'] === 1 ? '' : 's') . ' checked' . ($r['broken'] > 0 ? ', ' . $r['broken'] . ' need attention.' : ', all fine.') . ' Run it again to check the next ones.');
        return $this->redirect('admin/academy/partner-courses');
    }

    public function loadStarter(Request $request): Response
    {
        $this->authorize('academy.manage');
        $n = PartnerCourses::installStarter();
        $this->audit('academy.partner.starter', 'academy_partner_course', null, 'Loaded ' . $n . ' starter partner courses');
        $n === 0 ? Flash::info('Every course of the starter list is already there.') : Flash::success($n . ' partner course' . ($n === 1 ? '' : 's') . ' added.');
        return $this->redirect('admin/academy/partner-courses');
    }
}
