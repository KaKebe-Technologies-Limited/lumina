<?php
declare(strict_types=1);

namespace App\Controllers\Admin\Support;

use App\Core\Database;
use App\Core\Log;

/**
 * Numbers and lists for the admin dashboard (OWNER: B3 admin-core). Every query is defensive: a table that does not exist yet
 * (other areas are built independently) simply yields 0 / an empty list instead of an error page.
 */
final class DashboardData
{
    private static function value(string $sql, array $params = [], mixed $default = 0): mixed
    {
        try {
            return Database::value($sql, $params, $default);
        } catch (\Throwable $e) {
            Log::warning('dashboard query failed: ' . $e->getMessage());
            return $default;
        }
    }

    /** @return array<int,array<string,mixed>> */
    private static function rows(string $sql, array $params = []): array
    {
        try {
            return Database::all($sql, $params);
        } catch (\Throwable $e) {
            Log::warning('dashboard query failed: ' . $e->getMessage());
            return [];
        }
    }

    /** @return array<string,mixed> */
    public static function counts(): array
    {
        $monthStart = date('Y-m-01 00:00:00');
        $week = date('Y-m-d H:i:s', strtotime('-7 days'));

        $posts = ['blog' => 0, 'news' => 0, 'press' => 0];
        foreach (self::rows("SELECT post_type, COUNT(*) AS n FROM posts WHERE status = 'published' AND (published_at IS NULL OR published_at <= NOW()) GROUP BY post_type") as $r) {
            $posts[(string) $r['post_type']] = (int) $r['n'];
        }

        $money = [];
        foreach (self::rows("SELECT currency, status, COUNT(*) AS n, COALESCE(SUM(amount), 0) AS total FROM donations WHERE status IN ('pledged','received') GROUP BY currency, status") as $r) {
            $cur = (string) $r['currency'];
            $money[$cur] ??= ['received' => 0.0, 'pledged' => 0.0, 'received_n' => 0, 'pledged_n' => 0];
            $money[$cur][(string) $r['status']] = (float) $r['total'];
            $money[$cur][(string) $r['status'] . '_n'] = (int) $r['n'];
        }
        uasort($money, static fn(array $a, array $b): int => ($b['received'] + $b['pledged']) <=> ($a['received'] + $a['pledged']));

        return [
            'messages_new'    => (int) self::value("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'"),
            'subscribers_new' => (int) self::value("SELECT COUNT(*) FROM subscribers WHERE status = 'subscribed' AND subscribed_at >= ?", [$monthStart]),
            'subscribers'     => (int) self::value("SELECT COUNT(*) FROM subscribers WHERE status = 'subscribed'"),
            'applications'    => (int) self::value("SELECT COUNT(*) FROM applications WHERE status IN ('new','reviewing')"),
            'involvement_new' => (int) self::value("SELECT COUNT(*) FROM involvement_requests WHERE status = 'new'"),
            'posts'           => $posts,
            'posts_total'     => array_sum($posts),
            'events_upcoming' => (int) self::value('SELECT COUNT(*) FROM events WHERE is_active = 1 AND start_at >= NOW()'),
            'registrations'   => (int) self::value("SELECT COUNT(*) FROM event_registrations WHERE status <> 'cancelled' AND created_at >= ?", [$week]),
            'money'           => $money,
        ];
    }

    /** @return array<string,array<int,array<string,mixed>>> */
    public static function recent(): array
    {
        return [
            'messages'     => self::rows('SELECT id, name, subject, status, created_at FROM contact_messages ORDER BY id DESC LIMIT 5'),
            'applications' => self::rows('SELECT a.id, a.name, a.status, a.created_at, j.title AS job_title FROM applications a LEFT JOIN jobs j ON j.id = a.job_id ORDER BY a.id DESC LIMIT 5'),
            'donations'    => self::rows('SELECT id, donor_name, is_anonymous, amount, currency, status, created_at FROM donations ORDER BY id DESC LIMIT 5'),
        ];
    }

    /** Audit entries: everything for admins, only the user's own for everybody else. @return array<int,array<string,mixed>> */
    public static function activity(?int $onlyUserId): array
    {
        if ($onlyUserId !== null) {
            return self::rows('SELECT user_name, action, summary, created_at FROM audit_log WHERE user_id = ? ORDER BY id DESC LIMIT 8', [$onlyUserId]);
        }
        return self::rows("SELECT user_name, action, summary, created_at FROM audit_log WHERE action NOT IN ('auth.login_failed') ORDER BY id DESC LIMIT 8");
    }

    /**
     * Daily counts for the last $days days (oldest first), one series per inbound stream.
     * @return array{days:list<array{date:string,label:string,short:string,v:array<string,int>,total:int}>,series:array<string,array{label:string,total:int}>,max:int}
     */
    public static function chart(int $days = 30): array
    {
        $streams = [
            'messages'     => ['label' => 'Messages', 'sql' => 'SELECT DATE(created_at) AS d, COUNT(*) AS n FROM contact_messages WHERE created_at >= ? GROUP BY DATE(created_at)'],
            'subscribers'  => ['label' => 'New subscribers', 'sql' => "SELECT DATE(subscribed_at) AS d, COUNT(*) AS n FROM subscribers WHERE status = 'subscribed' AND subscribed_at >= ? GROUP BY DATE(subscribed_at)"],
            'applications' => ['label' => 'Applications', 'sql' => 'SELECT DATE(created_at) AS d, COUNT(*) AS n FROM applications WHERE created_at >= ? GROUP BY DATE(created_at)'],
        ];
        $start = new \DateTimeImmutable('today -' . ($days - 1) . ' days');
        $since = $start->format('Y-m-d 00:00:00');
        $series = [];
        $byDay = [];
        foreach ($streams as $key => $s) {
            $total = 0;
            foreach (self::rows($s['sql'], [$since]) as $r) {
                $byDay[(string) $r['d']][$key] = (int) $r['n'];
                $total += (int) $r['n'];
            }
            $series[$key] = ['label' => $s['label'], 'total' => $total];
        }
        $out = [];
        $max = 0;
        for ($i = 0; $i < $days; $i++) {
            $d = $start->modify('+' . $i . ' days');
            $key = $d->format('Y-m-d');
            $v = [];
            $total = 0;
            foreach (array_keys($streams) as $sk) {
                $v[$sk] = (int) ($byDay[$key][$sk] ?? 0);
                $total += $v[$sk];
            }
            $max = max($max, $total);
            $out[] = ['date' => $key, 'label' => $d->format('D j M'), 'short' => $d->format('j'), 'v' => $v, 'total' => $total];
        }
        return ['days' => $out, 'series' => $series, 'max' => $max];
    }

    /**
     * "Organisation pages" card: these pages are always live and in the menu (the owner can hide any of them under Menus); the card
     * shows how much content each has, with links to edit its wording and to add items.
     * @return list<array{slug:string,title:string,count:int,url:string,add:string}>
     */
    public static function contentChecklist(): array
    {
        $map = [
            'team'     => ['team_members', 'admin/team', 'admin/team/create', 'Add a team member'],
            'partners' => ['partners', 'admin/partners', 'admin/partners/create', 'Add a partner'],
            'reports'  => ['documents', 'admin/documents', 'admin/documents/create', 'Upload a report'],
            'events'   => ['events', 'admin/events', 'admin/events/create', 'Add an event'],
            'gallery'  => ['gallery_images', 'admin/gallery', 'admin/gallery', 'Upload photos'],
            'news'     => ['posts', 'admin/posts?type=news', 'admin/posts/create?type=news', 'Publish a news item'],
            'stories'  => ['stories', 'admin/stories', 'admin/stories/create', 'Add an impact story'],
        ];
        $where = [
            'team'     => 'is_active = 1',
            'partners' => 'is_active = 1',
            'reports'  => 'is_active = 1 AND is_public = 1',
            'events'   => 'is_active = 1',
            'gallery'  => 'is_active = 1',
            'news'     => "post_type = 'news' AND status = 'published'",
            'stories'  => 'is_active = 1 AND consent_confirmed = 1',
        ];
        $out = [];
        foreach (\App\Core\PageRegistry::all() as $slug => $reg) {
            if (!isset($map[$slug])) {
                continue;
            }
            $n = (int) self::value('SELECT COUNT(*) FROM ' . Database::ident($map[$slug][0]) . ' WHERE ' . $where[$slug]);
            $out[] = ['slug' => $slug, 'title' => $reg['title'], 'count' => $n, 'url' => $map[$slug][1], 'add' => $map[$slug][2], 'add_label' => $map[$slug][3],
                'edit' => 'admin/pages/' . $slug . '/edit'];
        }
        return $out;
    }
}
