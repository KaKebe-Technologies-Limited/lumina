<?php
declare(strict_types=1);

namespace App\Services\Fund;

use App\Services\Letters\LetterPdf;

/**
 * THE FULL PROPOSAL of a project (Fund a project > "Request the full proposal"): a multi-page PDF on the foundation's letterhead, built from the same text the project page shows
 * (summary, the need, the response, what it delivers, why it is a sound choice, the budget, the ways to give, the contributions to sponsor, the questions funders ask), so the document
 * and the page can never disagree. It is e-mailed to the visitor straight away by FundController::proposal(); nobody has to write or attach anything.
 * Uses the letter PDF engine (standard fonts only, so typographic punctuation is turned into plain letters first).
 */
final class ProposalDocument
{
    /** Plain Latin text for the standard PDF fonts. */
    public static function ascii(string $s): string
    {
        $s = strtr($s, [
            "\u{2018}" => "'", "\u{2019}" => "'", "\u{201C}" => '"', "\u{201D}" => '"', "\u{2013}" => '-', "\u{2014}" => ' - ', "\u{2026}" => '...', "\u{00A0}" => ' ', "\u{2022}" => '-', "\u{00D7}" => 'x',
        ]);
        return (string) (@iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s);
    }

    /** @param array<string,mixed> $p a project of app/config/fundraising/projects.php */
    public static function filename(array $p): string
    {
        return 'Proposal-' . preg_replace('/[^A-Za-z0-9]+/', '-', (string) ($p['short'] ?? $p['slug'] ?? 'project')) . '.pdf';
    }

    /** The proposal text (the "##" / "-" / numbered format of the letter engine). @param array<string,mixed> $p */
    public static function body(array $p): string
    {
        $ugx = static fn(int|float $v): string => 'UGX ' . number_format((float) $v);
        $usd = static fn(int|float $v): string => 'USD ' . number_format((float) $v);
        $o = [];
        $o[] = '## About this project';
        $o[] = (string) ($p['summary'] ?? '');
        $facts = array_filter(['Programme area: ' . ($p['pillar'] ?? ''), 'Duration: ' . ($p['period'] ?? ''), 'Where: ' . ($p['where'] ?? '')], static fn(string $l): bool => !str_ends_with($l, ': '));
        $o[] = implode("\n", array_map(static fn(string $l): string => '- ' . $l, $facts));
        if (!empty($p['need'])) {
            $o[] = "## The need\n" . $p['need'];
        }
        if (!empty($p['response'])) {
            $o[] = "## Our response\n" . $p['response'];
        }
        if (!empty($p['deliver'])) {
            $o[] = "## What the project delivers\n" . implode("\n", array_map(static fn(string $d): string => '- ' . $d, (array) $p['deliver']));
        }
        if (!empty($p['figures'])) {
            $o[] = "## The project in numbers\n" . implode("\n", array_map(static fn(array $f): string => '- ' . $f[0] . ' ' . $f[1], (array) $p['figures']));
        }
        if (!empty($p['why'])) {
            $o[] = "## Why this project\n" . implode("\n", array_map(static fn(array $w): string => '- ' . $w[0] . '. ' . $w[1], (array) $p['why']));
        }
        if (!empty($p['budget'])) {
            $b = (array) $p['budget'];
            $line = 'Total budget: ' . $ugx((float) ($b['ugx'] ?? 0)) . ' (about ' . $usd((float) ($b['usd'] ?? 0)) . ')' . (!empty($b['note']) ? ', ' . $b['note'] : '') . '.';
            $bars = array_map(static fn(array $r): string => '- ' . $r[0] . ': ' . rtrim(rtrim(number_format((float) $r[1], 1), '0'), '.') . '%', (array) ($b['bars'] ?? []));
            $o[] = "## The budget\n" . $line . ($bars !== [] ? "\n" . implode("\n", $bars) : '');
        }
        if (!empty($p['packages'])) {
            $rows = [];
            foreach ((array) $p['packages'] as $k) {
                $price = isset($k['ugx']) ? $ugx($k['ugx']) . ' (about ' . $usd((float) ($k['usd'] ?? 0)) . ')' : 'from ' . $usd((float) ($k['min_usd'] ?? 0));
                $rows[] = '- ' . $k['title'] . ': ' . $price . '. ' . ($k['text'] ?? '');
            }
            $o[] = "## Ways to give\n" . implode("\n", $rows);
        }
        if (!empty($p['items'])) {
            $rows = array_map(static fn(array $i): string => '- ' . $i['title'] . ': ' . $ugx($i['ugx']) . ' (about ' . $usd((float) ($i['usd'] ?? 0)) . '). ' . ($i['impact'] ?? ''), (array) $p['items']);
            $o[] = "## Contributions you can sponsor\nEvery gift below is real and priced. You can sponsor one, several or any number of each.\n" . implode("\n", $rows);
        }
        if (!empty($p['faq'])) {
            $rows = [];
            foreach ((array) $p['faq'] as $n => [$q, $a]) {
                $rows[] = ($n + 1) . '. ' . $q . ' ' . $a;
            }
            $o[] = "## Questions funders often ask\n" . implode("\n", $rows);
        }
        $site = (string) setting('site_name', 'Lumina Africa Foundation');
        $contact = array_filter([(string) setting('email_info', setting('email_primary', '')), (string) setting('phone_1', '')]);
        $o[] = "## How to give\nGive online or pledge at " . abs_url('fund-a-project/' . $p['slug']) . ' and choose a package or a contribution.'
            . ($contact !== [] ? ' For anything else, including the detailed budget and a call, write or call ' . $site . ' on ' . implode(' / ', $contact) . '.' : '');
        return self::ascii(implode("\n\n", array_filter($o, static fn(string $s): bool => trim($s) !== '')));
    }

    /**
     * The PDF bytes. @param array<string,mixed> $p
     */
    public static function pdf(array $p, string $toName = '', string $toOrg = '', array $opts = []): string
    {
        $first = trim((string) strtok(trim($toName), ' '));
        $site = (string) setting('site_name', 'Lumina Africa Foundation');
        return LetterPdf::build([
            'ref_no' => 'PROP-' . strtoupper(substr(preg_replace('/[^a-z]/i', '', (string) $p['slug']), 0, 3)) . '-' . date('Y'),
            'letter_date' => date('Y-m-d'),
            'to_name' => self::ascii($toName), 'to_org' => self::ascii($toOrg),
            'salutation' => $first !== '' ? 'Dear ' . self::ascii($first) . ',' : 'Dear friend,',
            'subject' => self::ascii('Project proposal: ' . (string) $p['title']),
            'body' => self::body($p),
            'closing' => 'With thanks and best wishes,',
            'sig_mode' => 'typed', 'from_name' => self::ascii($site), 'from_title' => 'Partnerships and Fundraising', 'from_office' => '',
            'use_letterhead' => 1,
        ], $opts);
    }
}
