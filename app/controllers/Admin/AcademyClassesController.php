<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\Academy\AcademyAccess;
use App\Services\Academy\ClassService;

/**
 * Admin > LAF Academy > Virtual classes: schedule a live class (Google Meet, Zoom, Microsoft Teams, free Jitsi room or any https link) for the learners of a course
 * or for everyone, e-mail the learners, cancel, add the recording afterwards. A facilitator manages the classes they run or that belong to their courses;
 * administrators manage all. Times are East Africa Time.
 */
final class AcademyClassesController extends AcademyAdminController
{
    private function scope(): array
    {
        if (AcademyAccess::manager()) {
            return ['1 = 1', []];
        }
        $ids = AcademyAccess::courseIds() ?? [];
        return ['(cl.facilitator_id = ? OR cl.course_id IN (' . ($ids === [] ? '0' : implode(',', $ids)) . '))', [AcademyAccess::userId()]];
    }

    private function load(int $id): array
    {
        $this->authorize('academy.teach');
        $c = ClassService::find($id) ?? throw new HttpException(404, 'Not found');
        if (!AcademyAccess::canClass($c)) {
            throw new HttpException(403, 'Forbidden');
        }
        return $c;
    }

    public function index(Request $request): Response
    {
        $this->authorize('academy.teach');
        ClassService::settle();
        [$sc, $sp] = $this->scope();
        $tab = $request->string('when');
        $tab = in_array($tab, ['past', 'cancelled'], true) ? $tab : 'upcoming';
        $cond = ['upcoming' => "cl.status = 'scheduled'", 'past' => "cl.status = 'completed'", 'cancelled' => "cl.status = 'cancelled'"][$tab];
        $order = $tab === 'upcoming' ? 'cl.start_at ASC' : 'cl.start_at DESC';
        $rows = Database::all(
            'SELECT cl.*, c.title AS course_title, u.name AS facilitator_name FROM academy_classes cl LEFT JOIN academy_courses c ON c.id = cl.course_id LEFT JOIN users u ON u.id = cl.facilitator_id WHERE ' . $cond . ' AND ' . $sc . ' ORDER BY ' . $order . ' LIMIT 200',
            $sp
        );
        $counts = [];
        foreach (['upcoming' => "'scheduled'", 'past' => "'completed'", 'cancelled' => "'cancelled'"] as $k => $st) {
            $counts[$k] = (int) Database::value('SELECT COUNT(*) FROM academy_classes cl WHERE cl.status = ' . $st . ' AND ' . $sc, $sp, 0);
        }
        return $this->render('admin/academy/classes/index', ['rows' => $rows, 'tab' => $tab, 'counts' => $counts, 'platforms' => ClassService::PLATFORMS],
            ['title' => 'Virtual classes', 'nav' => 'academy_classes', 'breadcrumbs' => $this->crumbs('Virtual classes')]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('academy.teach');
        $start = strtotime('tomorrow 10:00');
        return $this->form(null, [
            'platform' => 'meet', 'audience' => 'course', 'status' => 'scheduled', 'date' => date('Y-m-d', $start), 'time' => '10:00', 'duration' => 60,
            'facilitator_id' => AcademyAccess::userId(), 'course_id' => $request->int('course') ?: null,
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $c = $this->load($id);
        $mins = (int) round((strtotime((string) $c['end_at']) - strtotime((string) $c['start_at'])) / 60);
        return $this->form($c, $c + ['date' => date('Y-m-d', strtotime((string) $c['start_at'])), 'time' => date('H:i', strtotime((string) $c['start_at'])), 'duration' => max(15, $mins)]);
    }

    private function form(?array $class, array $row): Response
    {
        $isNew = $class === null;
        [$sc, $sp] = AcademyAccess::courseScope('c');
        return $this->render('admin/academy/classes/form', [
            'row' => $row, 'class' => $class, 'isNew' => $isNew, 'action' => url($isNew ? 'admin/academy/classes' : 'admin/academy/classes/' . $class['id']),
            'platforms' => ClassService::PLATFORMS, 'manager' => AcademyAccess::manager(), 'facilitators' => AcademyAccess::facilitators(),
            'courses' => Database::all("SELECT c.id, c.title FROM academy_courses c WHERE c.status <> 'archived' AND " . $sc . ' ORDER BY c.title', $sp),
            'learnersCount' => $isNew ? 0 : count(ClassService::audience($class)),
        ], ['title' => $isNew ? 'New virtual class' : 'Edit virtual class', 'nav' => 'academy_classes', 'breadcrumbs' => $this->crumbs('Virtual classes', [$isNew ? 'New class' : (string) $class['title'], null])]);
    }

    /** @return array<string,mixed> */
    private function collect(?array $class): array
    {
        $back = $class === null ? 'admin/academy/classes/create' : 'admin/academy/classes/' . $class['id'] . '/edit';
        $r = $this->request;
        $valid = $this->validate([
            'title' => 'required|max:190', 'description' => 'nullable|max:1500', 'platform' => 'required|in:' . implode(',', array_keys(ClassService::PLATFORMS)),
            'join_url' => 'nullable|max:500', 'passcode' => 'nullable|max:60', 'date' => 'required|date', 'time' => 'required', 'duration' => 'required|integer|between:15,600',
            'audience' => 'required|in:course,all', 'status' => 'required|in:scheduled,cancelled,completed', 'recording_url' => 'nullable|max:500',
        ], [], ['title' => 'Title', 'date' => 'Date', 'time' => 'Start time', 'duration' => 'Length'], $back);
        if (preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', (string) $valid['time']) !== 1) {
            $this->fail(['time' => 'Enter the start time as hours and minutes, for example 14:30.'], $back);
        }
        $url = ClassService::checkUrl((string) $valid['platform'], (string) $valid['join_url']);
        if (!$url['ok']) {
            $this->fail(['join_url' => (string) $url['error']], $back);
        }
        if ($valid['status'] === 'scheduled' && $url['url'] === null) {
            $this->fail(['join_url' => 'Add the meeting link. Use "Make a free Jitsi link" if you have no Meet, Zoom or Teams room yet.'], $back);
        }
        $rec = ClassService::checkUrl('other', (string) $valid['recording_url']);
        if (!$rec['ok']) {
            $this->fail(['recording_url' => (string) $rec['error']], $back);
        }
        $startTs = strtotime($valid['date'] . ' ' . $valid['time']);
        if ($startTs === false) {
            $this->fail(['date' => 'That date and time are not valid.'], $back);
        }
        if ($class === null && $valid['status'] === 'scheduled' && $startTs < time() - 3600) {
            $this->fail(['date' => 'The class starts in the past. Choose a later date or time.'], $back);
        }
        $courseId = $r->int('course_id');
        $course = $courseId > 0 ? \App\Services\Academy\CourseService::find($courseId) : null;
        if ($courseId > 0 && ($course === null || !AcademyAccess::canEdit($course))) {
            $this->fail(['course_id' => 'Choose one of your courses.'], $back);
        }
        if ($valid['audience'] === 'course' && $course === null) {
            $this->fail(['course_id' => 'Choose the course whose learners may attend, or open the class to all learners.'], $back);
        }
        $fid = AcademyAccess::manager() ? $r->int('facilitator_id') : AcademyAccess::userId();
        return [
            'title' => trim(strip_tags((string) $valid['title'])), 'description' => trim(strip_tags((string) $valid['description'])) ?: null, 'course_id' => $course !== null ? (int) $course['id'] : null,
            'facilitator_id' => $fid > 0 ? $fid : null, 'platform' => (string) $valid['platform'], 'join_url' => $url['url'], 'passcode' => trim(strip_tags((string) $valid['passcode'])) ?: null,
            'start_at' => date('Y-m-d H:i:s', $startTs), 'end_at' => date('Y-m-d H:i:s', $startTs + (int) $valid['duration'] * 60), 'status' => (string) $valid['status'],
            'audience' => (string) $valid['audience'], 'recording_url' => $rec['url'],
        ];
    }

    public function store(Request $request): Response
    {
        $this->authorize('academy.teach');
        $data = $this->collect(null) + ['created_by' => $this->userId()];
        $id = Database::insert('academy_classes', $data);
        $this->audit('academy.class.create', 'academy_class', $id, 'Scheduled class "' . $data['title'] . '"');
        Flash::success('The class was scheduled. Use "E-mail the learners" to invite them now.');
        return $this->redirect('admin/academy/classes/' . $id . '/edit');
    }

    public function update(Request $request, int $id): Response
    {
        $c = $this->load($id);
        $data = $this->collect($c);
        if ($data['start_at'] !== $c['start_at']) {
            $data['reminder_sent_at'] = null;               // a new time: the reminder is due again
        }
        Database::update('academy_classes', $data, 'id = ?', [$id]);
        $this->audit('academy.class.update', 'academy_class', $id, 'Edited class "' . $data['title'] . '"');
        Flash::success('The class was saved.');
        return $this->redirect('admin/academy/classes/' . $id . '/edit');
    }

    public function notify(Request $request, int $id): Response
    {
        $c = $this->load($id);
        if ($c['status'] !== 'scheduled') {
            Flash::warning('Only a scheduled class can be announced.');
            return $this->redirect('admin/academy/classes/' . $id . '/edit');
        }
        [$sent, $failed] = ClassService::notify($c, $request->bool('reminder'));
        $this->audit('academy.class.notify', 'academy_class', $id, 'E-mailed learners about class "' . $c['title'] . '"', ['sent' => $sent, 'failed' => $failed]);
        $sent === 0 && $failed === 0 ? Flash::info('Nobody needed an e-mail (each learner is e-mailed at most once an hour about the same class).') : Flash::success($sent . ' learner' . ($sent === 1 ? '' : 's') . ' e-mailed' . ($failed > 0 ? ', ' . $failed . ' could not be reached.' : '.'));
        return $this->redirect('admin/academy/classes/' . $id . '/edit');
    }

    public function status(Request $request, int $id): Response
    {
        $c = $this->load($id);
        $to = $request->string('to');
        if (!in_array($to, ['scheduled', 'cancelled', 'completed'], true)) {
            throw new HttpException(422, 'Unknown status');
        }
        Database::update('academy_classes', ['status' => $to], 'id = ?', [$id]);
        $this->audit('academy.class.' . $to, 'academy_class', $id, ucfirst($to) . ' class "' . $c['title'] . '"');
        Flash::success('The class is now ' . $to . '.');
        return $this->redirect('admin/academy/classes');
    }

    public function delete(Request $request, int $id): Response
    {
        $c = $this->load($id);
        Database::execute('DELETE FROM academy_classes WHERE id = ?', [$id]);
        $this->audit('academy.class.delete', 'academy_class', $id, 'Deleted class "' . $c['title'] . '"');
        Flash::success('The class was deleted.');
        return $this->redirect('admin/academy/classes');
    }
}
