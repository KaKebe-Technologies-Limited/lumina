<?php
declare(strict_types=1);

namespace App\Services\Opportunities;

use App\Core\Database;
use App\Core\Settings;

/**
 * Who offers an opportunity (organisation / government / international organisation) and the level of study it is for (undergraduate / graduate / PhD): the two filters of the
 * Opportunities page. Stored in the columns provider_type and study_levels (comma list), created on first use. The values are written by the team (Admin > Opportunities), set by the
 * scholarship and fellowship directory, or worked out from the text of items read from other websites (guess()).
 */
final class OpportunityMeta
{
    public const PROVIDERS = [
        'organisation' => 'Organisations',
        'government'   => 'Governments',
        'international' => 'International organisations',
    ];
    /** What each provider means, for the help text. */
    public const PROVIDER_HELP = [
        'organisation' => 'Foundations, universities, companies and charities',
        'government'   => 'A country\'s government or its agencies',
        'international' => 'The UN, the World Bank, the African Union, the European Union and similar',
    ];
    public const LEVELS = ['undergraduate' => 'Undergraduate', 'graduate' => 'Graduate (Master\'s)', 'phd' => 'PhD'];

    private static ?bool $ready = null;

    /** Add the columns when they are missing. Never throws. @return bool the columns exist */
    public static function install(): bool
    {
        if (self::$ready !== null) {
            return self::$ready;
        }
        try {
            if (!Database::tableExists('opportunities')) {
                return self::$ready = false;
            }
            foreach (['provider_type' => 'VARCHAR(20) NULL', 'study_levels' => 'VARCHAR(60) NULL'] as $col => $def) {
                if ((int) Database::value("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'opportunities' AND column_name = ?", [$col], 0) === 0) {
                    Database::execute("ALTER TABLE `opportunities` ADD COLUMN `{$col}` {$def}");
                }
            }
            self::backfill();
            return self::$ready = true;
        } catch (\Throwable) {
            return self::$ready = false;
        }
    }

    /** Once: guess the provider and the levels of the scholarships and fellowships that were added before these filters existed. */
    private static function backfill(): void
    {
        if ((string) Settings::get('opp_meta_backfilled', '') === '1') {
            return;
        }
        foreach (Database::all("SELECT id, title, organisation, summary, eligibility, description FROM opportunities WHERE category IN ('scholarship','fellowship') AND provider_type IS NULL AND study_levels IS NULL LIMIT 2000") as $r) {
            $g = self::guess((string) $r['title'] . ' ' . (string) $r['summary'] . ' ' . (string) $r['eligibility'] . ' ' . mb_substr(strip_tags((string) $r['description']), 0, 1500), (string) $r['organisation']);
            Database::update('opportunities', ['provider_type' => $g['provider'], 'study_levels' => $g['levels'] !== [] ? implode(',', $g['levels']) : null], 'id = ?', [(int) $r['id']]);
        }
        Settings::set('opp_meta_backfilled', '1');
        Settings::flush();
    }

    /**
     * Work out the provider and the levels from the words of an item. A guess: the team can correct it in the admin.
     * @return array{provider:string,levels:list<string>}
     */
    public static function guess(string $text, string $organisation = ''): array
    {
        $t = mb_strtolower($text);
        $o = mb_strtolower($organisation . ' ' . $text);
        $levels = [];
        if (preg_match('/\b(?:undergraduate|bachelor\'?s?|first degree|b\.?sc|b\.?a\.? degree|diploma)\b/u', $t) === 1) {
            $levels[] = 'undergraduate';
        }
        if (preg_match('/\b(?:master\'?s?|postgraduate|post-graduate|graduate|m\.?sc|mba|msc)\b/u', $t) === 1) {
            $levels[] = 'graduate';
        }
        if (preg_match('/\b(?:ph\.?d|doctoral|doctorate|dphil)\b/u', $t) === 1) {
            $levels[] = 'phd';
        }
        $provider = 'organisation';
        if (preg_match('/\b(?:united nations|unesco|unicef|undp|world bank|european union|european commission|erasmus mundus|african union|pan african university|african development bank|islamic development bank|commonwealth secretariat|iaea|opec fund|world health organi[sz]ation|fao|imf)\b/u', $o) === 1) {
            $provider = 'international';
        } elseif (preg_match('/\b(?:government|ministry|embassy|high commission|department of state|state department|chevening|fulbright|daad|swedish institute|iccr|itec|csc\b|mext|gks|australia awards|commonwealth scholarship|stipendium hungaricum|campus france|eiffel|national scholarship)\b/u', $o) === 1) {
            $provider = 'government';
        }
        return ['provider' => $provider, 'levels' => $levels];
    }

    /** @return list<string> the valid levels of a stored value */
    public static function levelsOf(?string $stored): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $stored)), static fn(string $l): bool => isset(self::LEVELS[$l])));
    }

    public static function providerLabel(?string $key): string
    {
        return self::PROVIDERS[(string) $key] ?? '';
    }
}
