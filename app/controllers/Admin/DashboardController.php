<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Support\DashboardData;
use App\Core\AdminController;
use App\Core\Auth;
use App\Core\Config;
use App\Core\Content;
use App\Core\Request;
use App\Core\Response;
use App\Core\Settings;

/**
 * Admin dashboard: what needs attention (inbox counters), recent items, a 30-day activity chart, quick actions and a
 * "before you launch" checklist. OWNER: B3 admin-core. Ability dashboard.view. Tiles only show what the user may open.
 */
final class DashboardController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('dashboard.view');
        $counts = DashboardData::counts();
        $inbox = can('inbox.manage');
        $content = can('content.manage');

        $tiles = [];
        if ($inbox) {
            $tiles[] = ['icon' => 'mail', 'value' => number_format($counts['messages_new']), 'label' => 'Unread messages', 'url' => 'admin/messages', 'alert' => $counts['messages_new'] > 0];
            $tiles[] = ['icon' => 'user-plus', 'value' => number_format($counts['subscribers_new']), 'label' => 'New subscribers this month', 'sub' => number_format($counts['subscribers']) . ' in total', 'url' => 'admin/subscribers'];
            $tiles[] = ['icon' => 'briefcase', 'value' => number_format($counts['applications']), 'label' => 'Applications to review', 'url' => 'admin/applications', 'alert' => $counts['applications'] > 0];
            $tiles[] = ['icon' => 'heart', 'value' => $this->moneyTile($counts['money']), 'label' => 'Donations received', 'sub' => $this->pledgedLine($counts['money']), 'url' => 'admin/donations', 'small' => strlen($this->moneyTile($counts['money'])) > 8];
        }
        // Membership (docs/MEMBERSHIP.md): pending applications; null when the user may not manage members
        if ($tile = \App\Services\Membership\MembershipService::dashboardTile()) {
            $tiles[] = $tile;
        }
        if ($content) {
            $p = $counts['posts'];
            $tiles[] = ['icon' => 'newspaper', 'value' => number_format($counts['posts_total']), 'label' => 'Published posts', 'sub' => $p['blog'] . ' blog, ' . $p['news'] . ' news, ' . $p['press'] . ' press', 'url' => 'admin/posts'];
            $tiles[] = ['icon' => 'calendar', 'value' => number_format($counts['events_upcoming']), 'label' => 'Upcoming events', 'url' => 'admin/events'];
        }
        if ($inbox) {
            $tiles[] = ['icon' => 'users', 'value' => number_format($counts['registrations']), 'label' => 'Event sign-ups, last 7 days', 'url' => 'admin/events'];
            $tiles[] = ['icon' => 'inbox', 'value' => number_format($counts['involvement_new']), 'label' => 'New "get involved" requests', 'url' => 'admin/involvement', 'alert' => $counts['involvement_new'] > 0];
        }

        $recent = $inbox ? DashboardData::recent() : ['messages' => [], 'applications' => [], 'donations' => []];
        $actions = array_values(array_filter([
            can('pages.edit') ? ['icon' => 'edit', 'label' => 'Edit the home page', 'url' => 'admin/pages/home/edit'] : null,
            can('pages.edit') ? ['icon' => 'plus', 'label' => 'Create a new page', 'url' => 'admin/pages/create'] : null,
            $content ? ['icon' => 'file-text', 'label' => 'Write a blog post', 'url' => 'admin/posts/create'] : null,
            $content ? ['icon' => 'calendar', 'label' => 'Add an event', 'url' => 'admin/events/create'] : null,
            $content ? ['icon' => 'image', 'label' => 'Add photos to the gallery', 'url' => 'admin/gallery'] : null,
            can('menus.manage') ? ['icon' => 'list', 'label' => 'Change the menus', 'url' => 'admin/menus'] : null,
            can('settings.manage') ? ['icon' => 'settings', 'label' => 'Contact details and settings', 'url' => 'admin/settings'] : null,
        ]));

        return $this->render('admin/dashboard/index', [
            'user'      => $this->user(),
            'tiles'     => $tiles,
            'recent'    => $recent,
            'chart'     => $inbox ? DashboardData::chart(30) : null,
            'activity'  => DashboardData::activity(can('audit.view') ? null : (int) Auth::id()),
            'allActivity' => can('audit.view'),
            'actions'   => $actions,
            'checklist' => $content ? DashboardData::contentChecklist() : [],
            'launch'    => $this->launchItems(),
            'canInbox'  => $inbox,
        ], ['title' => 'Dashboard', 'nav' => 'dashboard']);
    }

    /** Things the owner should still do before the site goes live, derived from real state (never a fixed to-do list). @return list<array<string,string>> */
    private function launchItems(): array
    {
        $items = [];
        if (can('settings.manage') && !Settings::isCustomised('reg_number')) {
            $items[] = [
                'title' => 'Confirm the registration number',
                'text'  => 'The old website showed two different numbers (GSD/R 1545 and GSD/R 1234). Check the certificate and save the correct one.',
                'url'   => 'admin/settings#tab-organisation', 'cta' => 'Open settings',
            ];
        }
        if (can('pages.edit') && !Content::isOverridden('home', 'hero.image') && Content::get('home', 'hero.image', '') === 'assets/img/site/photo-hero-portrait.jpg') {
            $items[] = [
                'title' => 'Replace the placeholder home-page photo',
                'text'  => 'The current hero picture carries another website\'s watermark along its bottom edge. Upload one of the foundation\'s own photos.',
                'url'   => 'admin/pages/home/edit#sec-hero', 'cta' => 'Change the photo',
            ];
        }
        if (can('settings.manage') && (bool) Config::get('app.debug', false) && (string) Config::get('app.env', 'production') !== 'local') {
            $items[] = [
                'title' => 'Switch debug mode off',
                'text'  => 'Technical error details are visible to visitors. Set APP_DEBUG=0 in the .env file on the live server.',
                'url'   => 'admin/settings', 'cta' => 'See system check',
            ];
        }
        return $items;
    }

    /** @param array<string,array<string,float|int>> $money */
    private function moneyTile(array $money): string
    {
        if ($money === []) {
            return '0';
        }
        $lines = [];
        foreach ($money as $cur => $m) {
            $lines[] = $cur . ' ' . $this->compact((float) $m['received']);
        }
        return count($lines) > 2 ? $lines[0] . ' +' . (count($lines) - 1) : implode(' / ', $lines);
    }

    /** @param array<string,array<string,float|int>> $money */
    private function pledgedLine(array $money): string
    {
        $parts = [];
        foreach ($money as $cur => $m) {
            if ($m['pledged'] > 0) {
                $parts[] = $cur . ' ' . number_format((float) $m['pledged']) . ' pledged';
            }
        }
        return $parts === [] ? 'Nothing pledged right now' : implode(', ', array_slice($parts, 0, 2));
    }

    private function compact(float $n): string
    {
        if ($n >= 1000000) {
            return rtrim(rtrim(number_format($n / 1000000, 1), '0'), '.') . 'M';
        }
        if ($n >= 10000) {
            return rtrim(rtrim(number_format($n / 1000, 1), '0'), '.') . 'K';
        }
        return number_format($n);
    }
}
