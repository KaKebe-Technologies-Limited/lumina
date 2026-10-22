<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `ambassador_applications` (people who offer to raise funds and lobby for the foundation). Generic API: see App\Core\Model. */
final class AmbassadorApplication extends Model
{
    protected static string $table = 'ambassador_applications';
    protected static string $order = 'id DESC';

    public const STATUSES = ['new', 'reviewing', 'approved', 'on_hold', 'declined'];
    public const LABELS = ['new' => 'New', 'reviewing' => 'Under review', 'approved' => 'Approved', 'on_hold' => 'On hold', 'declined' => 'Declined'];

    /** The ways an ambassador can help: key => label (shown on the form and in the admin). */
    public const ROLES = [
        'fundraise' => 'Raise funds (appeals, events, online campaigns)',
        'lobby'     => 'Lobby companies, institutions and donors for support',
        'equipment' => 'Collect computers, books and other materials',
        'network'   => 'Introduce partners, volunteers and supporters',
        'awareness' => 'Spread the word in my community and online',
        'speak'     => 'Speak at events or in the media',
    ];

    /** Kinds of in-kind support: key => label. */
    public const INKIND = [
        'computers'  => 'Computers and laptops',
        'devices'    => 'Tablets and smartphones',
        'equipment'  => 'Projectors, printers and routers',
        'power'      => 'Solar lamps and power equipment',
        'books'      => 'Books and learning materials',
        'stationery' => 'Stationery and school supplies',
        'software'   => 'Software licences and subscriptions',
        'furniture'  => 'Furniture for training spaces',
    ];

    public const PLEDGES = ['money' => 'Money', 'inkind' => 'In-kind items', 'other' => 'Something else'];

    public const AVAILABILITY = [
        'few_hours'   => 'A few hours a month',
        'weekly'      => 'A few hours a week',
        'campaigns'   => 'During campaigns and events',
        'year_round'  => 'Regularly throughout the year',
    ];

    /** @return list<string> */
    public static function jsonList(mixed $json): array
    {
        $d = json_decode((string) $json, true);
        return is_array($d) ? array_values(array_filter(array_map('strval', $d), static fn(string $s): bool => $s !== '')) : [];
    }

    /** @return list<string> the pledge kinds of a row (money, inkind, other) */
    public static function pledgeList(array $row): array
    {
        return array_values(array_filter(explode(',', (string) ($row['pledge_types'] ?? '')), static fn(string $s): bool => isset(self::PLEDGES[$s])));
    }
    /** Human-readable lines describing the yearly pledge of a row (money, in-kind items, other). @return list<string> */
    public static function pledgeLines(array $row): array
    {
        $out = [];
        $types = self::pledgeList($row);
        if (in_array("money", $types, true) && $row["annual_amount"] !== null) {
            $out[] = "Money: " . number_format((float) $row["annual_amount"], 2) . " " . (string) $row["currency"] . " per year";
        }
        if (in_array("inkind", $types, true)) {
            $names = array_map(static fn(string $k): string => self::INKIND[$k] ?? $k, self::jsonList($row["inkind_items"] ?? ""));
            $out[] = "In kind: " . ($names ? implode(", ", $names) : "(see notes)") . (trim((string) ($row["inkind_notes"] ?? "")) !== "" ? ". " . trim((string) $row["inkind_notes"]) : "");
        }
        if (in_array("other", $types, true) && trim((string) ($row["other_text"] ?? "")) !== "") {
            $out[] = "Other: " . trim((string) $row["other_text"]);
        }
        return $out;
    }
}
