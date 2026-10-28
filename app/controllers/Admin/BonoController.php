<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Services\Bono\BonoAi;
use App\Services\Bono\BonoEngine;
use App\Services\Bono\BonoIndexer;
use App\Services\Bono\BonoText;

/**
 * Admin > Bono AI (ability settings.manage; docs/BONO.md): how much Bono has learned and when, "Learn the website now", the answers an administrator taught it, and what
 * visitors asked: the questions Bono could not answer (teach them), how often the answers helped, and the latest questions.
 */
final class BonoController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('settings.manage');
        $ready = BonoIndexer::ready();
        $taught = $ready ? Database::all('SELECT * FROM bono_taught ORDER BY id DESC') : [];
        $open = $ready ? Database::all('SELECT question, MAX(id) AS id, COUNT(*) AS times, MAX(created_at) AS last_at FROM bono_log WHERE outcome = 0 AND handled = 0 GROUP BY question ORDER BY times DESC, last_at DESC LIMIT 30') : [];
        $recent = $ready ? Database::all('SELECT question, outcome, helpful, created_at FROM bono_log ORDER BY id DESC LIMIT 25') : [];
        $rate = $ready ? Database::one('SELECT SUM(helpful = 1) AS yes, SUM(helpful = 0) AS no FROM bono_log WHERE created_at >= ?', [date('Y-m-d H:i:s', time() - 30 * 86400)]) : null;
        return $this->render('admin/bono/index', [
            'ready' => $ready, 'stats' => BonoIndexer::stats(), 'taught' => $taught, 'open' => $open, 'recent' => $recent,
            'yes' => (int) ($rate['yes'] ?? 0), 'no' => (int) ($rate['no'] ?? 0),
            'ai' => BonoAi::enabled(), 'aiKey' => BonoAi::key() !== '', 'model' => BonoAi::model(), 'on' => BonoEngine::enabled(),
            'prefill' => mb_substr(BonoEngine::clean($request->string('q')), 0, 300), 'prefillLog' => $request->int('log'),
        ], ['title' => 'Bono AI', 'nav' => 'bono', 'breadcrumbs' => [['Administration', url('admin/settings')], ['Bono AI', null]]]);
    }

    /** Learn the website now (about a minute at most; the cron job does the same every night). */
    public function learn(Request $request): Response
    {
        $this->authorize('settings.manage');
        $r = BonoIndexer::run(50, $request->bool('force'));
        $this->audit('bono.learn', null, null, 'Bono AI learned the website: ' . $r['pages'] . ' pages read, ' . $r['updated'] . ' updated', $r);
        Flash::success('Bono read ' . $r['pages'] . ' page' . ($r['pages'] === 1 ? '' : 's') . ' and learned ' . $r['passages'] . ' new passages (' . $r['removed'] . ' forgotten, ' . $r['errors'] . ' errors).'
            . ($r['done'] ? '' : ' ' . $r['left'] . ' pages are left: press the button again, or the nightly run will finish them.'));
        return $this->redirect('admin/bono');
    }

    public function teach(Request $request): Response
    {
        $this->authorize('settings.manage');
        $q = BonoEngine::clean($request->string('question'));
        $a = trim(mb_substr(trim(strip_tags($request->string('answer'))), 0, 1500));
        if (mb_strlen($q) < 4 || mb_strlen($a) < 4 || BonoText::stems($q) === []) {
            $this->fail(['question' => 'Write the question as a visitor would ask it, and the answer Bono should give.'], 'admin/bono');
        }
        $id = (int) Database::insert('bono_taught', ['question' => $q, 'answer' => $a, 'stems' => ' ' . implode(' ', array_values(array_unique(BonoText::stems($q)))) . ' ']);
        if ($request->int('log') > 0) {
            Database::execute('UPDATE bono_log SET handled = 1 WHERE outcome = 0 AND question = (SELECT q FROM (SELECT question AS q FROM bono_log WHERE id = ?) x)', [$request->int('log')]);
        }
        $this->audit('bono.teach', 'bono_taught', $id, 'Taught Bono an answer: ' . mb_substr($q, 0, 80));
        Flash::success('Bono learned this answer. It will use it whenever someone asks about the same thing.');
        return $this->redirect('admin/bono');
    }

    public function toggle(Request $request, int $id): Response
    {
        $this->authorize('settings.manage');
        Database::execute('UPDATE bono_taught SET is_active = 1 - is_active, updated_at = ? WHERE id = ?', [date('Y-m-d H:i:s'), $id]);
        $this->audit('bono.toggle', 'bono_taught', $id, 'Switched a taught answer on or off');
        return $this->redirect('admin/bono');
    }

    public function delete(Request $request, int $id): Response
    {
        $this->authorize('settings.manage');
        Database::execute('DELETE FROM bono_taught WHERE id = ?', [$id]);
        $this->audit('bono.delete', 'bono_taught', $id, 'Removed a taught answer');
        Flash::success('The answer was removed.');
        return $this->redirect('admin/bono');
    }

    /** Dismiss an unanswered question (nothing to teach). */
    public function dismiss(Request $request, int $id): Response
    {
        $this->authorize('settings.manage');
        Database::execute('UPDATE bono_log SET handled = 1 WHERE outcome = 0 AND question = (SELECT q FROM (SELECT question AS q FROM bono_log WHERE id = ?) x)', [$id]);
        return $this->redirect('admin/bono');
    }
}
