<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\SitePages;
use App\Services\Privacy\DataRights;
use App\Services\Web\TextTokens;

/**
 * GET /privacy - the privacy policy. Every section is an editable repeater row (Pages > Privacy Policy) with a fixed anchor; the table of what we collect
 * (one row per activity, anchors collect-<key>) is placed after the section "collect"; the membership and shop sections are separate blocks appended after
 * the list. The registry default is a DRAFT FRAMEWORK that the foundation must have checked (see the page help and Admin > Data protection).
 * Extra tokens filled here: {request_url} {days} {reg_line} {analytics_note} (+ the organisation tokens of TextTokens).
 */
final class PrivacyController extends Controller
{
    public function index(Request $request): Response
    {
        $items = array_values(array_filter(
            content_list('privacy', 'notice.items'),
            static fn(array $i): bool => trim((string) ($i['title'] ?? '')) !== '' || trim((string) ($i['body'] ?? '')) !== ''
        ));
        // these two are their own blocks (not rows of the list above), so an edited list can never lose them
        foreach (['membership', 'shop'] as $extra) {
            if (section_on('privacy', $extra)) {
                $items[] = ['title' => (string) content('privacy', $extra . '.title'), 'anchor' => (string) content('privacy', $extra . '.anchor'), 'body' => (string) content('privacy', $extra . '.body')];
            }
        }

        $reg = trim((string) setting('reg_number', ''));
        $regLine = trim((string) content('privacy', 'who.reg_line'));
        $regLine = ($reg !== '' && $regLine !== '') ? htmlspecialchars(str_replace('{reg_number}', $reg, $regLine), ENT_QUOTES, 'UTF-8') : '';
        $analyticsOn = function_exists('analytics_id') ? analytics_id() !== '' : trim((string) setting('analytics_id', '')) !== '';
        $analytics = $analyticsOn ? (string) content_html('privacy', 'analytics.body') : '';
        $tokens = [
            '{request_url}'    => e(url('privacy/request')),
            '{days}'           => (string) DataRights::targetDays(),
            '{reg_line}'       => $regLine,
            '{analytics_note}' => $analytics,
        ];

        $seen = [];
        foreach ($items as $i => &$item) {
            $a = strtolower(preg_replace('/[^a-z0-9-]+/i', '', (string) ($item['anchor'] ?? '')) ?? '');
            if ($a === '' || isset($seen[$a])) {
                $a = 'section-' . ($i + 1);
            }
            $seen[$a] = true;
            $item['anchor'] = $a;
            $item['body'] = TextTokens::html(rich(strtr((string) $item['body'], $tokens)));
            $item['title'] = TextTokens::text((string) $item['title']);
        }
        unset($item);

        $cols = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) content('privacy', 'collect.cols')) ?: [])));
        $rows = array_values(array_filter(
            content_list('privacy', 'collect.rows'),
            static fn(array $r): bool => trim((string) ($r['activity'] ?? '')) !== ''
        ));
        foreach ($rows as &$r) {
            $r['anchor'] = strtolower(preg_replace('/[^a-z0-9-]+/i', '', (string) ($r['anchor'] ?? '')) ?? '');
        }
        unset($r);

        $points = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) content('privacy', 'brief.points')) ?: [])));

        return $this->renderPage('privacy', 'web/legal/privacy', [
            'items'  => $items,
            'cols'   => $cols + [0 => 'What we collect', 1 => 'Why we need it', 2 => 'Our legal reason', 3 => 'Who receives it', 4 => 'How long we aim to keep it'],
            'rows'   => $rows,
            'points' => section_on('privacy', 'brief') ? $points : [],
        ], [
            'bodyClass'   => 'is-legal',
            'breadcrumbs' => [[(string) (SitePages::get('privacy')['nav_label'] ?? 'Privacy Policy'), null]],
        ]);
    }
}
