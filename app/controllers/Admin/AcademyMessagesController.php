<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\Academy\AcademyMail;
use App\Services\Academy\LearnerMessages;

/**
 * Admin > LAF Academy > Messages (administrators, ability academy.manage): the inbox of every conversation with a learner, and each conversation. The team writes from here
 * or with the "Message" button on a learner; the learner reads it in the portal (Messages tab) and answers there. A yellow number on the menu counts the learners' unread messages.
 */
final class AcademyMessagesController extends AcademyAdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('academy.manage');
        $q = mb_substr(trim($request->string('q')), 0, 80);
        return $this->render('admin/academy/messages/index', [
            'rows' => LearnerMessages::inbox(100, $q), 'q' => $q, 'ready' => LearnerMessages::ready(), 'unread' => LearnerMessages::unreadForAdmin(),
        ], ['title' => 'Messages', 'nav' => 'academy_messages', 'breadcrumbs' => $this->crumbs('Messages')]);
    }

    /** One conversation: opening it marks the learner's messages as read. */
    public function thread(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = Database::one('SELECT * FROM academy_learners WHERE id = ?', [$id]) ?? throw new HttpException(404, 'Not found');
        $thread = LearnerMessages::thread($id);
        $firstNew = null;
        foreach ($thread as $m) {
            if ($m['sender'] === 'learner' && $m['read_at'] === null) {
                $firstNew = (int) $m['id'];
                break;
            }
        }
        LearnerMessages::markRead($id, 'admin');
        return $this->render('admin/academy/messages/thread', ['l' => $l, 'thread' => $thread, 'firstNew' => $firstNew, 'ready' => LearnerMessages::ready()],
            ['title' => 'Messages: ' . $l['name'], 'nav' => 'academy_messages', 'breadcrumbs' => $this->crumbs('Messages', [(string) $l['name'], null])]);
    }

    public function send(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = Database::one('SELECT id, name, email FROM academy_learners WHERE id = ?', [$id]) ?? throw new HttpException(404, 'Not found');
        $res = LearnerMessages::send($id, 'admin', $request->string('body'), Auth::id());
        if (!$res['ok']) {
            $this->fail(['body' => (string) $res['error']], 'admin/academy/learners/' . $id . '/messages');
        }
        $this->audit('academy.message.send', 'academy_learner', $id, 'Sent a message to ' . $l['email']);
        $mailed = false;
        if ($request->bool('email')) {
            $mailed = AcademyMail::teamMessage($l, LearnerMessages::clean($request->string('body')));
        }
        Flash::success('The message was sent. ' . $l['name'] . ' will see it in the learner portal' . ($request->bool('email') ? ($mailed ? ' and received an e-mail.' : '. The e-mail could not be sent.') : '.'));
        return $this->redirect($request->string('back') === 'learner' ? 'admin/academy/learners/' . $id : 'admin/academy/learners/' . $id . '/messages#end');
    }
}
