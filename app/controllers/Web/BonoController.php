<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Database;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Response;
use App\Services\Bono\BonoEngine;

/**
 * Bono AI, the support assistant (docs/BONO.md): the two JSON endpoints behind the chat window.
 *   POST /bono/ask       {question, history:[{role,text}]}  ->  {ok, id, answer, sources:[{title,url}], suggestions:[...]}
 *   POST /bono/feedback  {id, helpful:1|0}                  ->  {ok}
 * CSRF is verified by the router (the script sends X-CSRF-TOKEN). Questions are limited per connection; nothing personal is stored with a question.
 */
final class BonoController extends Controller
{
    public function ask(Request $request): Response
    {
        if (!BonoEngine::enabled()) {
            return $this->json(['ok' => false, 'error' => 'Bono AI is switched off.'], 404);
        }
        $q = BonoEngine::clean((string) $request->post('question', ''));
        if ($q === '') {
            return $this->json(['ok' => false, 'error' => 'Please type a question.'], 422);
        }
        if (!RateLimit::attempt('bono-ask:' . $request->ip(), 24, 600) || !RateLimit::attempt('bono-ask-day:' . $request->ip(), 200, 86400)) {
            return $this->json(['ok' => false, 'error' => 'You are asking very quickly. Please wait a few minutes, or contact the team on WhatsApp.'], 429);
        }
        $history = [];
        foreach (array_slice((array) $request->post('history', []), -6) as $h) {
            if (is_array($h) && in_array($h['role'] ?? '', ['user', 'bot'], true) && is_string($h['text'] ?? null)) {
                $history[] = ['role' => (string) $h['role'], 'text' => mb_substr((string) $h['text'], 0, 600)];
            }
        }
        $r = BonoEngine::ask($q, $history);
        return $this->json(['ok' => true, 'id' => $r['id'], 'answer' => $r['answer'], 'sources' => $r['sources'], 'suggestions' => $r['suggestions']]);
    }

    public function feedback(Request $request): Response
    {
        if (!BonoEngine::enabled()) {
            return $this->json(['ok' => false], 404);
        }
        $id = (int) $request->post('id', 0);
        $helpful = $request->post('helpful', null);
        if ($id > 0 && ($helpful === 1 || $helpful === 0 || $helpful === '1' || $helpful === '0') && RateLimit::attempt('bono-fb:' . $request->ip(), 60, 600)) {
            Database::update('bono_log', ['helpful' => (int) $helpful], 'id = ? AND helpful IS NULL', [$id]);
        }
        return $this->json(['ok' => true]);
    }
}
