<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Flash;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Response;
use App\Models\AmbassadorApplication as Amb;
use App\Services\Recruitment\Recruitment;

/**
 * The AMBASSADOR PROFILE: created when the application is submitted. The applicant follows the application there (stage, history of the stages, what they applied with) through a
 * private link that is e-mailed to them. GET /ambassador/profile/{token}; GET/POST /ambassador/profile (ask for the link again with the application number and e-mail address).
 */
final class AmbassadorProfileController extends Controller
{
    public function show(Request $request, string $token): Response
    {
        $r = Recruitment::rowByToken($token);
        if ($r === null) {
            return $this->render('web/recruitment/profile-missing', [], ['title' => 'Ambassador profile'])->status(404);
        }
        $id = (int) $r['id'];
        $status = (string) $r['status'];
        $stage = Recruitment::stage('ambassador', $status) ?? 'reviewing';
        $msg = Recruitment::message('ambassador', ['name' => '', 'ref_no' => ''], $stage);
        $order = ['received' => 'Received', 'reviewing' => 'Under review', 'interview' => 'Interview', 'accepted' => 'Approved'];
        $pos = array_search($stage, array_keys($order), true);
        $steps = [];
        $i = 0;
        foreach ($order as $k => $label) {
            $steps[] = ['label' => $label, 'state' => $pos === false ? 'todo' : ($i < $pos ? 'done' : ($i === $pos ? 'now' : 'todo'))];
            $i++;
        }
        $timeline = [];
        foreach (array_reverse(Recruitment::history('ambassador', $id)) as $h) {
            $st = Recruitment::stage('ambassador', (string) $h['status']);
            $m = $st !== null ? Recruitment::message('ambassador', ['name' => '', 'ref_no' => ''], $st) : null;
            if ($m !== null) {
                $timeline[] = ['label' => $m['label'], 'when' => (string) $h['created_at']];
            }
        }
        return $this->render('web/recruitment/profile', [
            'r' => $r, 'stage' => $stage, 'stageLabel' => (string) ($msg['label'] ?? 'Under review'), 'stageText' => (string) (($msg['lines'][0] ?? '')),
            'steps' => $stage === 'declined' || $stage === 'on_hold' ? [] : $steps, 'timeline' => array_reverse($timeline),
            'pledge' => Amb::pledgeLines($r), 'roles' => array_map(static fn(string $k): string => Amb::ROLES[$k] ?? $k, Amb::jsonList($r['roles'])),
            'tone' => $stage === 'declined' ? 'bad' : ($stage === 'accepted' ? 'good' : 'wait'),
        ], ['title' => 'My ambassador profile', 'meta' => ['robots' => 'noindex, nofollow']]);
    }

    /** The form "send me my profile link". */
    public function request(Request $request): Response
    {
        return $this->render('web/recruitment/profile-request', ['sent' => false], ['title' => 'My ambassador profile']);
    }

    /** POST: the link is e-mailed when the number and the address match; the answer is the same either way (nothing leaks). */
    public function sendLink(Request $request): Response
    {
        Recruitment::install();
        $ref = strtoupper(trim($request->string('ref')));
        $email = strtolower(trim($request->string('email')));
        if (Recruitment::ready() && preg_match('/^AMB-\d{4}-\d{3,}$/', $ref) === 1 && Mailer::validEmail($email)) {
            $row = \App\Core\Database::one('SELECT * FROM ambassador_applications WHERE ref_no = ? AND LOWER(email) = ?', [$ref, $email]);
            if ($row !== null && Recruitment::profileUrl($row) !== '') {
                $site = trim((string) setting('site_name', 'Lumina Africa Foundation')) ?: 'Lumina Africa Foundation';
                $first = explode(' ', trim((string) $row['name']))[0] ?: 'there';
                try {
                    Mailer::send($email, 'Your ambassador profile link (' . $ref . ')', "Hello {$first},\n\nHere is the private link to your ambassador profile, where you can follow your application:\n\n" . Recruitment::profileUrl($row) . "\n\nKeep this link to yourself: anyone who has it can see your application.\n\nWith thanks,\n{$site}\n");
                } catch (\Throwable) {
                    // the answer below does not reveal whether it worked
                }
            }
        }
        return $this->render('web/recruitment/profile-request', ['sent' => true], ['title' => 'My ambassador profile']);
    }
}
