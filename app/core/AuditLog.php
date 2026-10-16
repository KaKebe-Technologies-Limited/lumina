<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Admin audit trail (table audit_log). EVERY admin create/update/delete/publish/export/login SHOULD be recorded.
 *
 *   AuditLog::record('post.create', 'post', $id, 'Created post "Hello"');
 *   AuditLog::record('page.update', 'page', 'about', 'Edited About page', ['fields' => ['hero.title']]);
 *   AuditLog::record('auth.login');                       // action naming: <entity>.<verb>
 *   AuditLog::recent(50)                                  // newest first
 *
 * Never throws (a logging failure must not break the admin action).
 */
final class AuditLog
{
    public static function record(string $action, ?string $entityType = null, int|string|null $entityId = null, string $summary = '', array $details = []): void
    {
        try {
            $user = Auth::user();
            $req = Request::current();
            // "Name <email>": display names are editable and not unique, the e-mail pins down who really acted
            $mail = $user !== null ? (string) $user['email'] : '';
            $who = $user !== null ? mb_substr((string) $user['name'], 0, max(20, 117 - mb_strlen($mail))) . ' <' . $mail . '>' : null;
            Database::insert('audit_log', [
                'user_id'     => $user['id'] ?? null,
                'user_name'   => $who !== null ? mb_substr($who, 0, 120) : ($action === 'auth.login_failed' ? 'anonymous' : 'system'),
                'action'      => mb_substr($action, 0, 60),
                'entity_type' => $entityType !== null ? mb_substr($entityType, 0, 60) : null,
                'entity_id'   => $entityId !== null ? mb_substr((string) $entityId, 0, 64) : null,
                'summary'     => mb_substr(mb_scrub($summary, 'UTF-8'), 0, 255),
                'details'     => $details === [] ? null : json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE),
                'ip'          => $req ? $req->ip() : null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('AuditLog failed: ' . $e->getMessage());
        }
    }

    /** @return array<int,array<string,mixed>> */
    public static function recent(int $limit = 50, int $offset = 0): array
    {
        return Database::all('SELECT * FROM audit_log ORDER BY id DESC LIMIT ? OFFSET ?', [$limit, $offset]);
    }
}
