<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Slug;
use App\Core\Uploader;
use App\Services\Academy\AcademyAccess;
use App\Services\Academy\AcademyMail;
use App\Services\Academy\ChallengeInstaller;
use App\Services\Academy\ChallengeService;
use App\Services\Academy\CourseService;
use App\Services\Academy\FacilitatorApplications;
use App\Services\Academy\QuizScript;

/**
 * Admin > LAF Academy > Challenges: create quiz challenges, innovation challenges and hackathons, set their dates, approve entries, let judges score projects,
 * and watch the system mark the winners. Administrators (academy.manage) do everything; facilitators (academy.teach) can open the list and score the projects of
 * a judged challenge (in their approved topic, when they have one). Docs: docs/ACADEMY.md.
 */
final class AcademyChallengesAdminController extends AcademyAdminController
{
    /** A redirect with an explanation when the database update has not been imported yet. */
    private function notReady(): ?Response
    {
        if (ChallengeService::ready()) {
            return null;
        }
        Flash::warning('The database update has not been imported yet. In phpMyAdmin, import lumina-live-import.sql into your database (it is safe to run again), then open this page again.');
        return $this->redirect('admin/academy');
    }

    private function load(int $id, string $ability = 'academy.manage'): array
    {
        $this->authorize($ability);
        if (!ChallengeService::ready()) {
            throw new HttpException(404, 'Not found');
        }
        return ChallengeService::find($id) ?? throw new HttpException(404, 'Not found');
    }

    public function index(Request $request): Response
    {
        $this->authorize('academy.teach');
        if ($r = $this->notReady()) {
            return $r;
        }
        ChallengeService::settle();
        $rows = Database::all(
            "SELECT c.*, (SELECT COUNT(*) FROM academy_challenge_entries e WHERE e.challenge_id = c.id AND e.status IN ('pending','approved')) AS entries,
                    (SELECT COUNT(*) FROM academy_challenge_entries e2 WHERE e2.challenge_id = c.id AND e2.status = 'pending') AS waiting
             FROM academy_challenges c ORDER BY (c.status = 'draft') DESC, (c.finalized_at IS NULL) DESC, COALESCE(c.starts_at, c.created_at) DESC LIMIT 200"
        );
        foreach ($rows as &$r) {
            $r['phase'] = ChallengeService::phase($r);
        }
        unset($r);
        $manager = AcademyAccess::manager();
        return $this->render('admin/academy/challenges/index', ['rows' => $rows, 'manager' => $manager, 'starterPending' => $manager ? ChallengeInstaller::pending() : 0],
            ['title' => 'Challenges', 'nav' => 'academy_challenges', 'breadcrumbs' => $this->crumbs('Challenges')]);
    }

    // ------------------------------------------------------------------------------------------------------------- create and edit

    public function create(Request $request): Response
    {
        $this->authorize('academy.manage');
        if ($r = $this->notReady()) {
            return $r;
        }
        $kind = in_array($request->string('kind'), array_keys(ChallengeService::KINDS), true) ? $request->string('kind') : 'quiz';
        return $this->form(null, [
            'kind' => $kind, 'category' => 'basics', 'status' => 'draft', 'team_max' => $kind === 'hackathon' ? 4 : 1, 'approval' => 'auto', 'scoring' => $kind === 'quiz' ? 'quiz' : 'judged',
            'winners_count' => 3, 'pass_score' => 0, 'certify_participants' => 1, 'quiz_minutes' => 30, 'criteria' => '',
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $c = $this->load($id);
        return $this->form($c, $c + ['criteria_text' => ChallengeService::criteriaText($c)]);
    }

    private function dt(?string $v): string
    {
        return $v ? date('Y-m-d\TH:i', (int) strtotime($v)) : '';
    }

    private function form(?array $c, array $row): Response
    {
        $isNew = $c === null;
        foreach (['apply_opens_at', 'apply_closes_at', 'starts_at', 'ends_at', 'judging_ends_at'] as $k) {
            $row[$k] = isset($row[$k]) ? (str_contains((string) $row[$k], 'T') ? $row[$k] : $this->dt((string) $row[$k])) : '';
        }
        $started = !$isNew && (int) Database::value('SELECT COUNT(*) FROM academy_challenge_entries WHERE challenge_id = ? AND quiz_started_at IS NOT NULL', [(int) $c['id']], 0) > 0;
        return $this->render('admin/academy/challenges/form', [
            'row' => $row, 'c' => $c, 'isNew' => $isNew, 'action' => url($isNew ? 'admin/academy/challenges' : 'admin/academy/challenges/' . $c['id']),
            'kinds' => ChallengeService::KINDS, 'categories' => CourseService::categoryLabels(), 'quizLocked' => $started, 'criteriaDefault' => ChallengeService::criteriaText(['criteria' => null]),
            'questionCount' => $c !== null ? count(ChallengeService::questions($c)) : 0,
        ], ['title' => $isNew ? 'New challenge' : 'Edit challenge', 'nav' => 'academy_challenges', 'breadcrumbs' => $this->crumbs('Challenges', [$isNew ? 'New' : (string) $c['title'], null])]);
    }

    /** @return array<string,mixed> */
    private function collect(?array $c): array
    {
        $back = $c === null ? 'admin/academy/challenges/create' : 'admin/academy/challenges/' . $c['id'] . '/edit';
        $r = $this->request;
        $valid = $this->validate([
            'title' => 'required|max:190', 'slug' => 'nullable|max:120', 'kind' => 'required|in:' . implode(',', array_keys(ChallengeService::KINDS)),
            'category' => 'required|in:' . implode(',', array_keys(CourseService::CATEGORIES)), 'summary' => 'nullable|max:320', 'description' => 'nullable|max:4000',
            'rules' => 'nullable|max:3000', 'prize' => 'nullable|max:400', 'apply_opens_at' => 'nullable|datetime', 'apply_closes_at' => 'nullable|datetime',
            'starts_at' => 'required|datetime', 'ends_at' => 'required|datetime', 'judging_ends_at' => 'nullable|datetime', 'team_max' => 'required|integer|between:1,10',
            'max_entries' => 'nullable|integer|between:1,5000', 'approval' => 'required|in:auto,manual', 'winners_count' => 'required|integer|between:1,10',
            'pass_score' => 'required|integer|between:0,100', 'quiz_minutes' => 'nullable|integer|between:5,180',
        ], [], ['title' => 'Title', 'starts_at' => 'Start', 'ends_at' => 'End'], $back);
        $ts = static fn(string $k): ?int => ($valid[$k] ?? '') !== '' ? (int) strtotime(str_replace('T', ' ', (string) $valid[$k])) : null;
        $opens = $ts('apply_opens_at');
        $closes = $ts('apply_closes_at');
        $start = $ts('starts_at');
        $end = $ts('ends_at');
        $judging = $ts('judging_ends_at');
        if ($start === null || $end === null || $end <= $start) {
            $this->fail(['ends_at' => 'The end must be after the start.'], $back);
        }
        if ($opens !== null && $opens > $end) {
            $this->fail(['apply_opens_at' => 'Applications must open before the challenge ends.'], $back);
        }
        if ($closes !== null && ($closes > $end || ($opens !== null && $closes <= $opens))) {
            $this->fail(['apply_closes_at' => 'Applications must close after they open and no later than the end.'], $back);
        }
        if ($judging !== null && $judging < $end) {
            $this->fail(['judging_ends_at' => 'Judging cannot end before the challenge ends.'], $back);
        }
        $kind = (string) $valid['kind'];
        $scoring = $kind === 'quiz' ? 'quiz' : 'judged';
        $data = [
            'kind' => $kind, 'scoring' => $scoring, 'title' => trim(strip_tags((string) $valid['title'])), 'category' => (string) $valid['category'],
            'summary' => trim(strip_tags((string) $valid['summary'])) ?: null, 'description' => trim(strip_tags((string) $valid['description'])) ?: null,
            'rules' => trim(strip_tags((string) $valid['rules'])) ?: null, 'prize' => trim(strip_tags((string) $valid['prize'])) ?: null,
            'apply_opens_at' => $opens ? date('Y-m-d H:i:s', $opens) : null, 'apply_closes_at' => $closes ? date('Y-m-d H:i:s', $closes) : null,
            'starts_at' => date('Y-m-d H:i:s', $start), 'ends_at' => date('Y-m-d H:i:s', $end), 'judging_ends_at' => $judging ? date('Y-m-d H:i:s', $judging) : null,
            'team_max' => $kind === 'quiz' ? 1 : (int) $valid['team_max'], 'max_entries' => ($valid['max_entries'] ?? '') !== '' ? (int) $valid['max_entries'] : null,
            'approval' => (string) $valid['approval'], 'winners_count' => (int) $valid['winners_count'], 'pass_score' => (int) $valid['pass_score'],
            'certify_participants' => $r->bool('certify_participants') ? 1 : 0, 'quiz_minutes' => ($valid['quiz_minutes'] ?? '') !== '' ? (int) $valid['quiz_minutes'] : null,
        ];
        if ($scoring === 'quiz') {
            $locked = $c !== null && (int) Database::value('SELECT COUNT(*) FROM academy_challenge_entries WHERE challenge_id = ? AND quiz_started_at IS NOT NULL', [(int) $c['id']], 0) > 0;
            if (!$locked) {
                $script = trim(str_replace("\r\n", "\n", $r->string('quiz_script')));
                $p = QuizScript::parse($script);
                if (!$p['ok']) {
                    $this->fail(['quiz_script' => implode(' ', array_slice($p['errors'], 0, 3))], $back);
                }
                if (count($p['questions']) < 5) {
                    $this->fail(['quiz_script' => 'A quiz challenge needs at least 5 questions (now ' . count($p['questions']) . ').'], $back);
                }
                $data['quiz_script'] = $script;
            }
            if ($data['quiz_minutes'] === null) {
                $this->fail(['quiz_minutes' => 'Set the time allowed for the quiz.'], $back);
            }
        } else {
            $cr = ChallengeService::parseCriteria($r->string('criteria_text') !== '' ? $r->string('criteria_text') : ChallengeService::criteriaText(['criteria' => null]));
            if (!$cr['ok']) {
                $this->fail(['criteria_text' => $cr['error']], $back);
            }
            $data['criteria'] = json_encode($cr['criteria']);
        }
        $typed = Slug::make($r->string('slug'));
        $ignore = $c !== null ? (int) $c['id'] : null;
        if ($typed === '') {
            $data['slug'] = $c !== null ? (string) $c['slug'] : Slug::unique('academy_challenges', $data['title'], null, 'slug', [], 'challenge');
        } else {
            $dup = Database::value('SELECT COUNT(*) FROM academy_challenges WHERE slug = ?' . ($ignore !== null ? ' AND id <> ?' : ''), $ignore !== null ? [$typed, $ignore] : [$typed], 0);
            if ($dup > 0) {
                $this->fail(['slug' => 'Another challenge already uses this web address.'], $back);
            }
            $data['slug'] = $typed;
        }
        return $data;
    }

    public function store(Request $request): Response
    {
        $this->authorize('academy.manage');
        $data = $this->collect(null) + ['created_by' => $this->userId(), 'status' => 'draft'];
        $id = Database::insert('academy_challenges', $data);
        $this->audit('academy.challenge.create', 'academy_challenge', $id, 'Created challenge "' . $data['title'] . '"');
        Flash::success('The challenge was saved as a draft. Publish it when you are ready for applications.');
        return $this->redirect('admin/academy/challenges/' . $id . '/edit');
    }

    public function update(Request $request, int $id): Response
    {
        $c = $this->load($id);
        $data = $this->collect($c);
        if (!empty($c['finalized_at'])) {
            unset($data['starts_at'], $data['ends_at'], $data['judging_ends_at'], $data['winners_count'], $data['pass_score'], $data['criteria'], $data['quiz_script']);
        }
        Database::update('academy_challenges', $data, 'id = ?', [$id]);
        $this->audit('academy.challenge.update', 'academy_challenge', $id, 'Edited challenge "' . $data['title'] . '"');
        Flash::success('The challenge was saved.' . (!empty($c['finalized_at']) ? ' Dates, scoring and results are locked once the results are final: use "Reopen the results" to change them.' : ''));
        return $this->redirect('admin/academy/challenges/' . $id . '/edit');
    }

    public function status(Request $request, int $id): Response
    {
        $c = $this->load($id);
        $to = $request->string('to');
        if (!in_array($to, ['draft', 'published', 'cancelled'], true)) {
            throw new HttpException(422, 'Unknown status');
        }
        if ($to === 'published') {
            if ($c['starts_at'] === null || $c['ends_at'] === null) {
                Flash::warning('Set the start and end before publishing.');
                return $this->redirect('admin/academy/challenges/' . $id . '/edit');
            }
            if ($c['scoring'] === 'quiz' && count(ChallengeService::questions($c)) < 5) {
                Flash::warning('Add at least 5 questions before publishing.');
                return $this->redirect('admin/academy/challenges/' . $id . '/edit');
            }
        }
        if ($to === 'draft' && Database::value('SELECT COUNT(*) FROM academy_challenge_entries WHERE challenge_id = ?', [$id], 0) > 0) {
            Flash::warning('A challenge with entries cannot go back to draft. Cancel it instead.');
            return $this->redirect('admin/academy/challenges');
        }
        Database::update('academy_challenges', ['status' => $to], 'id = ?', [$id]);
        $this->audit('academy.challenge.' . $to, 'academy_challenge', $id, ucfirst($to) . ' challenge "' . $c['title'] . '"');
        Flash::success($to === 'published' ? 'The challenge is published.' : ($to === 'cancelled' ? 'The challenge is cancelled.' : 'The challenge is a draft again.'));
        return $this->redirect('admin/academy/challenges');
    }

    public function delete(Request $request, int $id): Response
    {
        $c = $this->load($id);
        $n = (int) Database::value('SELECT COUNT(*) FROM academy_challenge_entries WHERE challenge_id = ?', [$id], 0);
        if ($n > 0 && $c['status'] !== 'cancelled') {
            Flash::warning('This challenge has entries. Cancel it first if you really want to remove it.');
            return $this->redirect('admin/academy/challenges');
        }
        foreach (Database::all('SELECT photo_path, sub_file_path FROM academy_challenge_entries WHERE challenge_id = ?', [$id]) as $e) {
            Uploader::deletePrivate((string) ($e['photo_path'] ?? ''));
            Uploader::deletePrivate((string) ($e['sub_file_path'] ?? ''));
        }
        Database::execute('DELETE FROM academy_challenges WHERE id = ?', [$id]);
        $this->audit('academy.challenge.delete', 'academy_challenge', $id, 'Deleted challenge "' . $c['title'] . '"');
        Flash::success('The challenge was deleted. Certificates already issued stay valid.');
        return $this->redirect('admin/academy/challenges');
    }

    public function loadStarter(Request $request): Response
    {
        $this->authorize('academy.manage');
        $n = ChallengeInstaller::installAll($this->userId());
        $this->audit('academy.challenge.starter', 'academy_challenge', null, 'Added ' . $n . ' starter challenges');
        $n === 0 ? Flash::info('Every starter challenge is already there.') : Flash::success($n . ' starter challenge' . ($n === 1 ? '' : 's') . ' added as drafts. Open one, set its dates and publish it.');
        return $this->redirect('admin/academy/challenges');
    }

    // ------------------------------------------------------------------------------------------------------------- entries

    public function entries(Request $request, int $id): Response
    {
        $c = $this->load($id);
        $status = $request->string('status');
        $where = 'e.challenge_id = ?';
        $params = [$id];
        if (isset(ChallengeService::ENTRY_STATUS[$status])) {
            $where .= ' AND e.status = ?';
            $params[] = $status;
        }
        $rows = Database::all(
            "SELECT e.*, l.name AS learner_name, l.email AS learner_email,
                    (SELECT COUNT(*) FROM academy_challenge_judgments j WHERE j.entry_id = e.id) AS judgments, (SELECT AVG(j2.total) FROM academy_challenge_judgments j2 WHERE j2.entry_id = e.id) AS average
             FROM academy_challenge_entries e JOIN academy_learners l ON l.id = e.learner_id WHERE {$where} ORDER BY (e.rank_no IS NULL), e.rank_no ASC, e.created_at ASC LIMIT 500",
            $params
        );
        return $this->render('admin/academy/challenges/entries', ['c' => $c, 'rows' => $rows, 'status' => $status, 'phase' => ChallengeService::phase($c)],
            ['title' => 'Entries: ' . $c['title'], 'nav' => 'academy_challenges', 'breadcrumbs' => $this->crumbs('Challenges', [(string) $c['title'], url('admin/academy/challenges/' . $id . '/edit')], ['Entries', null])]);
    }

    private function entryOf(int $eid): array
    {
        $this->authorize('academy.manage');
        $e = ChallengeService::entry($eid) ?? throw new HttpException(404, 'Not found');
        return $e;
    }

    public function entryDecision(Request $request, int $eid): Response
    {
        $e = $this->entryOf($eid);
        $c = ChallengeService::find((int) $e['challenge_id']) ?? throw new HttpException(404, 'Not found');
        $to = $request->string('to');
        if (!in_array($to, ['approved', 'rejected'], true)) {
            throw new HttpException(422, 'Unknown decision');
        }
        $note = trim(strip_tags($request->string('note')));
        ChallengeService::setEntryStatus($e, $to, $note);
        $learner = Database::one('SELECT * FROM academy_learners WHERE id = ?', [(int) $e['learner_id']]);
        $mailed = $learner !== null && AcademyMail::challengeEntry($c, $learner, $to, $note);
        $this->audit('academy.challenge.entry.' . $to, 'academy_challenge_entry', $eid, ucfirst($to) . ' an entry to "' . $c['title'] . '"');
        Flash::success('The entry is ' . ($to === 'approved' ? 'approved' : 'declined') . '.' . ($mailed ? ' The learner was e-mailed.' : ''));
        return $this->redirect('admin/academy/challenges/' . $c['id'] . '/entries');
    }

    public function entryPhoto(Request $request, int $eid): Response
    {
        $e = $this->entryOf($eid);
        $to = $request->string('to');
        if ($to === 'toggle') {
            Database::update('academy_challenge_entries', ['publish_photo' => (int) $e['publish_photo'] === 1 ? 0 : 1], 'id = ?', [$eid]);
            Flash::success((int) $e['publish_photo'] === 1 ? 'The photo is hidden from the winners page.' : 'The photo may be shown on the winners page.');
        } elseif ($to === 'remove') {
            Uploader::deletePrivate((string) ($e['photo_path'] ?? ''));
            Database::update('academy_challenge_entries', ['photo_path' => null], 'id = ?', [$eid]);
            Flash::success('The photo was removed.');
        }
        return $this->redirect('admin/academy/challenges/' . $e['challenge_id'] . '/entries');
    }

    /** View an entrant's photo or project file (staff only). */
    public function entryFile(Request $request, int $eid, string $part): Response
    {
        $this->authorize('academy.teach');
        $e = ChallengeService::entry($eid) ?? throw new HttpException(404, 'Not found');
        $path = $part === 'photo' ? (string) ($e['photo_path'] ?? '') : (string) ($e['sub_file_path'] ?? '');
        if ($path === '' || !in_array($part, ['photo', 'project'], true)) {
            throw new HttpException(404, 'Not found');
        }
        return Uploader::download($path, $part === 'photo' ? 'photo-' . $eid . '.jpg' : (string) ($e['sub_file_name'] ?: 'project'), $part === 'photo');
    }

    // ------------------------------------------------------------------------------------------------------------- judging

    private function mayJudge(array $c): void
    {
        $this->authorize('academy.teach');
        if ($c['scoring'] !== 'judged') {
            throw new HttpException(404, 'Not found');
        }
        if (!AcademyAccess::manager()) {
            $topic = FacilitatorApplications::topicOfUser($this->userId());
            if ($topic !== null && $topic !== $c['category']) {
                throw new HttpException(403, 'Forbidden');
            }
        }
    }

    public function judge(Request $request, int $id): Response
    {
        $c = $this->load($id, 'academy.teach');
        $this->mayJudge($c);
        return $this->render('admin/academy/challenges/judge', [
            'c' => $c, 'criteria' => ChallengeService::criteria($c), 'entries' => ChallengeService::judgeable($c, $this->userId()), 'phase' => ChallengeService::phase($c), 'manager' => AcademyAccess::manager(),
        ], ['title' => 'Score projects: ' . $c['title'], 'nav' => 'academy_challenges', 'breadcrumbs' => $this->crumbs('Challenges', [(string) $c['title'], null], ['Score projects', null])]);
    }

    public function saveJudgment(Request $request, int $id, int $eid): Response
    {
        $c = $this->load($id, 'academy.teach');
        $this->mayJudge($c);
        $e = ChallengeService::entry($eid);
        if ($e === null || (int) $e['challenge_id'] !== $id) {
            throw new HttpException(404, 'Not found');
        }
        $points = $request->input('points', []);
        $ok = ChallengeService::judge($c, $e, $this->userId(), is_array($points) ? array_values($points) : [], $request->string('comment'));
        $ok ? Flash::success('Your scores were saved.') : Flash::warning('These scores cannot be saved: the results are final or the project is not submitted.');
        return $this->redirect('admin/academy/challenges/' . $id . '/judge#e' . $eid);
    }

    // ------------------------------------------------------------------------------------------------------------- results

    public function finalize(Request $request, int $id): Response
    {
        $c = $this->load($id);
        $r = ChallengeService::finalize($c, $request->bool('force'));
        if ($r['ok']) {
            $this->audit('academy.challenge.finalize', 'academy_challenge', $id, 'Results made for "' . $c['title'] . '": ' . $r['message']);
            Flash::success('Results are final. ' . $r['message'] . ' ' . $r['certificates'] . ' certificate' . ($r['certificates'] === 1 ? '' : 's') . ' issued.');
        } else {
            Flash::warning($r['message']);
        }
        return $this->redirect('admin/academy/challenges/' . $id . '/entries');
    }

    public function reopen(Request $request, int $id): Response
    {
        $c = $this->load($id);
        if (empty($c['finalized_at'])) {
            return $this->redirect('admin/academy/challenges/' . $id . '/entries');
        }
        ChallengeService::reopen($c);
        $this->audit('academy.challenge.reopen', 'academy_challenge', $id, 'Reopened the results of "' . $c['title'] . '"');
        Flash::success('The results were reopened and the certificates of this challenge were withdrawn. Correct what is needed, then make the results again.');
        return $this->redirect('admin/academy/challenges/' . $id . '/entries');
    }
}
