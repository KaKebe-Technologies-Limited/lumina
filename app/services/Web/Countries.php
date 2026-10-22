<?php
declare(strict_types=1);

namespace App\Services\Web;

/**
 * Regions and countries of the world (and the currencies people most often give in), used by the Ambassadors sign-up form: the visitor picks a region and then
 * a country of that region. About 250 countries and territories (UN members and observers, plus the territories people live in) grouped into eight regions.
 * Plain data, no database. A country appears in exactly one region.
 */
final class Countries
{
    /** region key => label */
    public const REGIONS = [
        'africa'     => 'Africa',
        'asia'       => 'Asia',
        'middle_east' => 'Middle East',
        'europe'     => 'Europe',
        'north_america' => 'North America',
        'central_america_caribbean' => 'Central America and the Caribbean',
        'south_america' => 'South America',
        'oceania'    => 'Oceania',
    ];

    /** @return array<string,list<string>> region key => countries in alphabetical order */
    public static function byRegion(): array
    {
        $r = [
            'africa' => ['Algeria', 'Angola', 'Benin', 'Botswana', 'Burkina Faso', 'Burundi', 'Cabo Verde', 'Cameroon', 'Central African Republic', 'Chad', 'Comoros',
                'Congo (Republic of the)', 'Congo (Democratic Republic of the)', "Côte d'Ivoire", 'Djibouti', 'Egypt', 'Equatorial Guinea', 'Eritrea', 'Eswatini', 'Ethiopia',
                'Gabon', 'Gambia', 'Ghana', 'Guinea', 'Guinea-Bissau', 'Kenya', 'Lesotho', 'Liberia', 'Libya', 'Madagascar', 'Malawi', 'Mali', 'Mauritania', 'Mauritius',
                'Mayotte', 'Morocco', 'Mozambique', 'Namibia', 'Niger', 'Nigeria', 'Réunion', 'Rwanda', 'Saint Helena', 'São Tomé and Príncipe', 'Senegal', 'Seychelles',
                'Sierra Leone', 'Somalia', 'South Africa', 'South Sudan', 'Sudan', 'Tanzania', 'Togo', 'Tunisia', 'Uganda', 'Western Sahara', 'Zambia', 'Zimbabwe'],
            'asia' => ['Afghanistan', 'Armenia', 'Azerbaijan', 'Bangladesh', 'Bhutan', 'Brunei', 'Cambodia', 'China', 'Georgia', 'Hong Kong', 'India', 'Indonesia', 'Japan',
                'Kazakhstan', 'Kyrgyzstan', 'Laos', 'Macao', 'Malaysia', 'Maldives', 'Mongolia', 'Myanmar', 'Nepal', 'North Korea', 'Pakistan', 'Philippines', 'Singapore',
                'South Korea', 'Sri Lanka', 'Taiwan', 'Tajikistan', 'Thailand', 'Timor-Leste', 'Turkmenistan', 'Uzbekistan', 'Vietnam'],
            'middle_east' => ['Bahrain', 'Cyprus', 'Iran', 'Iraq', 'Israel', 'Jordan', 'Kuwait', 'Lebanon', 'Oman', 'Palestine', 'Qatar', 'Saudi Arabia', 'Syria', 'Türkiye',
                'United Arab Emirates', 'Yemen'],
            'europe' => ['Åland Islands', 'Albania', 'Andorra', 'Austria', 'Belarus', 'Belgium', 'Bosnia and Herzegovina', 'Bulgaria', 'Croatia', 'Czechia', 'Denmark', 'Estonia',
                'Faroe Islands', 'Finland', 'France', 'Germany', 'Gibraltar', 'Greece', 'Guernsey', 'Hungary', 'Iceland', 'Ireland', 'Isle of Man', 'Italy', 'Jersey', 'Kosovo',
                'Latvia', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Malta', 'Moldova', 'Monaco', 'Montenegro', 'Netherlands', 'North Macedonia', 'Norway', 'Poland',
                'Portugal', 'Romania', 'Russia', 'San Marino', 'Serbia', 'Slovakia', 'Slovenia', 'Spain', 'Sweden', 'Switzerland', 'Ukraine', 'United Kingdom', 'Vatican City'],
            'north_america' => ['Bermuda', 'Canada', 'Greenland', 'Mexico', 'Saint Pierre and Miquelon', 'United States'],
            'central_america_caribbean' => ['Anguilla', 'Antigua and Barbuda', 'Aruba', 'Bahamas', 'Barbados', 'Belize', 'British Virgin Islands', 'Cayman Islands', 'Costa Rica',
                'Cuba', 'Curaçao', 'Dominica', 'Dominican Republic', 'El Salvador', 'Grenada', 'Guadeloupe', 'Guatemala', 'Haiti', 'Honduras', 'Jamaica', 'Martinique', 'Montserrat',
                'Nicaragua', 'Panama', 'Puerto Rico', 'Saint Barthélemy', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Martin', 'Saint Vincent and the Grenadines', 'Sint Maarten',
                'Trinidad and Tobago', 'Turks and Caicos Islands', 'US Virgin Islands'],
            'south_america' => ['Argentina', 'Bolivia', 'Brazil', 'Chile', 'Colombia', 'Ecuador', 'Falkland Islands', 'French Guiana', 'Guyana', 'Paraguay', 'Peru', 'Suriname',
                'Uruguay', 'Venezuela'],
            'oceania' => ['American Samoa', 'Australia', 'Cook Islands', 'Fiji', 'French Polynesia', 'Guam', 'Kiribati', 'Marshall Islands', 'Micronesia', 'Nauru', 'New Caledonia',
                'New Zealand', 'Niue', 'Norfolk Island', 'Northern Mariana Islands', 'Palau', 'Papua New Guinea', 'Pitcairn Islands', 'Samoa', 'Solomon Islands', 'Tokelau', 'Tonga',
                'Tuvalu', 'Vanuatu', 'Wallis and Futuna'],
        ];
        foreach ($r as &$list) {
            usort($list, static fn(string $a, string $b): int => strcoll(self::fold($a), self::fold($b)));
        }
        unset($list);
        return $r;
    }

    /** Sort key without accents ("Åland" sorts with A, "Türkiye" with T). */
    private static function fold(string $s): string
    {
        return strtolower(strtr($s, ['Å' => 'A', 'å' => 'a', 'Ç' => 'C', 'ç' => 'c', 'ô' => 'o', 'é' => 'e', 'í' => 'i', 'ü' => 'u', 'Ü' => 'U', 'Ô' => 'O', 'É' => 'E', 'ã' => 'a']));
    }

    /** True when $country belongs to $region. */
    public static function valid(string $region, string $country): bool
    {
        return isset(self::REGIONS[$region]) && in_array($country, self::byRegion()[$region] ?? [], true);
    }

    public static function count(): int
    {
        return array_sum(array_map('count', self::byRegion()));
    }

    /** Currencies offered for a yearly amount: code => label. */
    public const CURRENCIES = [
        'USD' => 'US dollar (USD)', 'EUR' => 'Euro (EUR)', 'GBP' => 'Pound sterling (GBP)', 'UGX' => 'Ugandan shilling (UGX)', 'KES' => 'Kenyan shilling (KES)',
        'TZS' => 'Tanzanian shilling (TZS)', 'RWF' => 'Rwandan franc (RWF)', 'ZAR' => 'South African rand (ZAR)', 'NGN' => 'Nigerian naira (NGN)', 'GHS' => 'Ghanaian cedi (GHS)',
        'CAD' => 'Canadian dollar (CAD)', 'AUD' => 'Australian dollar (AUD)', 'NZD' => 'New Zealand dollar (NZD)', 'CHF' => 'Swiss franc (CHF)', 'SEK' => 'Swedish krona (SEK)',
        'NOK' => 'Norwegian krone (NOK)', 'DKK' => 'Danish krone (DKK)', 'JPY' => 'Japanese yen (JPY)', 'CNY' => 'Chinese yuan (CNY)', 'INR' => 'Indian rupee (INR)',
        'AED' => 'UAE dirham (AED)', 'SAR' => 'Saudi riyal (SAR)', 'SGD' => 'Singapore dollar (SGD)', 'BRL' => 'Brazilian real (BRL)', 'MXN' => 'Mexican peso (MXN)',
    ];
}
