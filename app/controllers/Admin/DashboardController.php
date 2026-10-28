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
        // Calendar (docs/CALENDAR.md): Eid dates and other flagged dates that still need an administrator's confirmation within 60 days
        if ($tile = \App\Services\Calendar\CalendarService::dashboardTile()) {
            $tiles[] = $tile;
        }
        // LAF Academy (docs/ACADEMY.md): new learners waiting for approval (null unless the user manages the Academy)
        if ($tile = \App\Services\Academy\LearnerRegistry::dashboardTile()) {
            $tiles[] = $tile;
        }
        // Meetings (docs/MEETINGS.md): "Meetings this week" and "New meeting requests"; empty when the user may not manage meetings
        foreach (\App\Services\Meetings\MeetingService::dashboardTiles() as $tile) {
            $tiles[] = $tile;
        }
        // Forms (docs/FORMS.md): "New form submissions" (counts only) for people who may read the answers; hidden until a form exists
        if (can('forms.submissions') && \App\Services\Forms\FormService::available()) {
            $fs = \App\Services\Forms\FormService::summary();
            if ($fs['forms'] > 0 || $fs['new'] > 0) {
                $tiles[] = ['icon' => 'inbox', 'value' => number_format($fs['new']), 'label' => 'New form submissions', 'sub' => number_format($fs['week']) . ' in the last 7 days', 'url' => 'admin/form-answers', 'alert' => $fs['new'] > 0];
            }
        }
        // Shop (docs/SHOP.md): orders that need a person (new, or a payment to check). Counts only, no customer data; shown once the shop is open or has had an order.
        if (can('shop.orders')) {
            try {
                $shopTodo = \App\Services\Shop\OrderService::newCount();
                $shopAll = (int) \App\Core\Database::value('SELECT COUNT(*) FROM shop_orders', [], 0);
                if ($shopAll > 0 || \App\Services\Shop\ShopSettings::isOpen()) {
                    $tiles[] = ['icon' => 'shopping-bag', 'value' => number_format($shopTodo), 'label' => 'Shop orders to handle', 'sub' => number_format($shopAll) . ' order' . ($shopAll === 1 ? '' : 's') . ' in total',
                        'url' => 'admin/shop/orders?needs_action=1', 'alert' => $shopTodo > 0];
                }
            } catch (\Throwable) {
                // the shop tables do not exist yet (schema not applied): no tile
            }
        }
        // Social media (docs/SOCIAL.md): posts that failed or need a decision, a channel that needs a new token, a token about to expire. Counts only; shown only while something needs a person.
        if (can('social.share') || can('social.manage')) {
            try {
                $soc = \App\Services\Social\SocialConfig::attention();
                if ($soc['total'] > 0) {
                    $bits = array_filter([
                        $soc['failed'] > 0 ? $soc['failed'] . ' failed post' . ($soc['failed'] === 1 ? '' : 's') : '',
                        $soc['review'] > 0 ? $soc['review'] . ' to check' : '',
                        $soc['needs_token'] !== [] ? 'a channel needs a new token' : '',
                        $soc['expiring'] !== [] ? 'a token expires soon' : '',
                    ]);
                    $tiles[] = ['icon' => 'send', 'value' => number_format($soc['total']), 'label' => 'Social media needs attention', 'sub' => implode(', ', $bits),
                        // each person gets a link they may open: the log needs social.share, the channels screen social.manage
                        'url' => can('social.manage') && (!can('social.share') || $soc['needs_token'] !== [] || $soc['expiring'] !== []) ? 'admin/social/channels' : 'admin/social/log', 'alert' => true];
                }
            } catch (\Throwable) {
                // the social tables do not exist yet (schema not applied): no tile
            }
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
            can('meetings.manage') ? ['icon' => 'users', 'label' => 'Schedule a meeting', 'url' => 'admin/meetings/create'] : null,
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
