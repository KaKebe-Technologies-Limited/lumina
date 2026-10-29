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

    /**
     * International dialling codes (digits only, no plus) of the countries listed above. Territories that share a code with a larger country use it
     * (Åland +358, Jersey +44, every North American plan member +1, ...). Used by the Academy sign-up to pre-fill phone numbers.
     * @var array<string,string>
     */
    private const DIAL = [
        'Algeria' => '213', 'Angola' => '244', 'Benin' => '229', 'Botswana' => '267', 'Burkina Faso' => '226', 'Burundi' => '257', 'Cabo Verde' => '238', 'Cameroon' => '237',
        'Central African Republic' => '236', 'Chad' => '235', 'Comoros' => '269', 'Congo (Democratic Republic of the)' => '243', 'Congo (Republic of the)' => '242',
        "Côte d'Ivoire" => '225', 'Djibouti' => '253', 'Egypt' => '20', 'Equatorial Guinea' => '240', 'Eritrea' => '291', 'Eswatini' => '268', 'Ethiopia' => '251', 'Gabon' => '241',
        'Gambia' => '220', 'Ghana' => '233', 'Guinea' => '224', 'Guinea-Bissau' => '245', 'Kenya' => '254', 'Lesotho' => '266', 'Liberia' => '231', 'Libya' => '218',
        'Madagascar' => '261', 'Malawi' => '265', 'Mali' => '223', 'Mauritania' => '222', 'Mauritius' => '230', 'Mayotte' => '262', 'Morocco' => '212', 'Mozambique' => '258',
        'Namibia' => '264', 'Niger' => '227', 'Nigeria' => '234', 'Réunion' => '262', 'Rwanda' => '250', 'Saint Helena' => '290', 'São Tomé and Príncipe' => '239', 'Senegal' => '221',
        'Seychelles' => '248', 'Sierra Leone' => '232', 'Somalia' => '252', 'South Africa' => '27', 'South Sudan' => '211', 'Sudan' => '249', 'Tanzania' => '255', 'Togo' => '228',
        'Tunisia' => '216', 'Uganda' => '256', 'Western Sahara' => '212', 'Zambia' => '260', 'Zimbabwe' => '263',
        'Afghanistan' => '93', 'Armenia' => '374', 'Azerbaijan' => '994', 'Bangladesh' => '880', 'Bhutan' => '975', 'Brunei' => '673', 'Cambodia' => '855', 'China' => '86',
        'Georgia' => '995', 'Hong Kong' => '852', 'India' => '91', 'Indonesia' => '62', 'Japan' => '81', 'Kazakhstan' => '7', 'Kyrgyzstan' => '996', 'Laos' => '856', 'Macao' => '853',
        'Malaysia' => '60', 'Maldives' => '960', 'Mongolia' => '976', 'Myanmar' => '95', 'Nepal' => '977', 'North Korea' => '850', 'Pakistan' => '92', 'Philippines' => '63',
        'Singapore' => '65', 'South Korea' => '82', 'Sri Lanka' => '94', 'Taiwan' => '886', 'Tajikistan' => '992', 'Thailand' => '66', 'Timor-Leste' => '670', 'Turkmenistan' => '993',
        'Uzbekistan' => '998', 'Vietnam' => '84',
        'Bahrain' => '973', 'Cyprus' => '357', 'Iran' => '98', 'Iraq' => '964', 'Israel' => '972', 'Jordan' => '962', 'Kuwait' => '965', 'Lebanon' => '961', 'Oman' => '968',
        'Palestine' => '970', 'Qatar' => '974', 'Saudi Arabia' => '966', 'Syria' => '963', 'Türkiye' => '90', 'United Arab Emirates' => '971', 'Yemen' => '967',
        'Åland Islands' => '358', 'Albania' => '355', 'Andorra' => '376', 'Austria' => '43', 'Belarus' => '375', 'Belgium' => '32', 'Bosnia and Herzegovina' => '387', 'Bulgaria' => '359',
        'Croatia' => '385', 'Czechia' => '420', 'Denmark' => '45', 'Estonia' => '372', 'Faroe Islands' => '298', 'Finland' => '358', 'France' => '33', 'Germany' => '49', 'Gibraltar' => '350',
        'Greece' => '30', 'Guernsey' => '44', 'Hungary' => '36', 'Iceland' => '354', 'Ireland' => '353', 'Isle of Man' => '44', 'Italy' => '39', 'Jersey' => '44', 'Kosovo' => '383',
        'Latvia' => '371', 'Liechtenstein' => '423', 'Lithuania' => '370', 'Luxembourg' => '352', 'Malta' => '356', 'Moldova' => '373', 'Monaco' => '377', 'Montenegro' => '382',
        'Netherlands' => '31', 'North Macedonia' => '389', 'Norway' => '47', 'Poland' => '48', 'Portugal' => '351', 'Romania' => '40', 'Russia' => '7', 'San Marino' => '378',
        'Serbia' => '381', 'Slovakia' => '421', 'Slovenia' => '386', 'Spain' => '34', 'Sweden' => '46', 'Switzerland' => '41', 'Ukraine' => '380', 'United Kingdom' => '44', 'Vatican City' => '39',
        'Bermuda' => '1', 'Canada' => '1', 'Greenland' => '299', 'Mexico' => '52', 'Saint Pierre and Miquelon' => '508', 'United States' => '1',
        'Anguilla' => '1', 'Antigua and Barbuda' => '1', 'Aruba' => '297', 'Bahamas' => '1', 'Barbados' => '1', 'Belize' => '501', 'British Virgin Islands' => '1', 'Cayman Islands' => '1',
        'Costa Rica' => '506', 'Cuba' => '53', 'Curaçao' => '599', 'Dominica' => '1', 'Dominican Republic' => '1', 'El Salvador' => '503', 'Grenada' => '1', 'Guadeloupe' => '590',
        'Guatemala' => '502', 'Haiti' => '509', 'Honduras' => '504', 'Jamaica' => '1', 'Martinique' => '596', 'Montserrat' => '1', 'Nicaragua' => '505', 'Panama' => '507',
        'Puerto Rico' => '1', 'Saint Barthélemy' => '590', 'Saint Kitts and Nevis' => '1', 'Saint Lucia' => '1', 'Saint Martin' => '590', 'Saint Vincent and the Grenadines' => '1',
        'Sint Maarten' => '1', 'Trinidad and Tobago' => '1', 'Turks and Caicos Islands' => '1', 'US Virgin Islands' => '1',
        'Argentina' => '54', 'Bolivia' => '591', 'Brazil' => '55', 'Chile' => '56', 'Colombia' => '57', 'Ecuador' => '593', 'Falkland Islands' => '500', 'French Guiana' => '594',
        'Guyana' => '592', 'Paraguay' => '595', 'Peru' => '51', 'Suriname' => '597', 'Uruguay' => '598', 'Venezuela' => '58',
        'American Samoa' => '1', 'Australia' => '61', 'Cook Islands' => '682', 'Fiji' => '679', 'French Polynesia' => '689', 'Guam' => '1', 'Kiribati' => '686', 'Marshall Islands' => '692',
        'Micronesia' => '691', 'Nauru' => '674', 'New Caledonia' => '687', 'New Zealand' => '64', 'Niue' => '683', 'Norfolk Island' => '672', 'Northern Mariana Islands' => '1',
        'Palau' => '680', 'Papua New Guinea' => '675', 'Pitcairn Islands' => '64', 'Samoa' => '685', 'Solomon Islands' => '677', 'Tokelau' => '690', 'Tonga' => '676', 'Tuvalu' => '688',
        'Vanuatu' => '678', 'Wallis and Futuna' => '681',
    ];

    /** Other ways people write a country (typed by hand before the sign-up had a list). Folded spelling => the name used in the list. @var array<string,string> */
    private const ALIASES = [
        'usa' => 'United States', 'u.s.a.' => 'United States', 'us' => 'United States', 'america' => 'United States', 'united states of america' => 'United States',
        'uk' => 'United Kingdom', 'u.k.' => 'United Kingdom', 'england' => 'United Kingdom', 'scotland' => 'United Kingdom', 'wales' => 'United Kingdom', 'great britain' => 'United Kingdom',
        'drc' => 'Congo (Democratic Republic of the)', 'dr congo' => 'Congo (Democratic Republic of the)', 'dr. congo' => 'Congo (Democratic Republic of the)',
        'congo (drc)' => 'Congo (Democratic Republic of the)', 'congo-kinshasa' => 'Congo (Democratic Republic of the)', 'democratic republic of the congo' => 'Congo (Democratic Republic of the)',
        'democratic republic of congo' => 'Congo (Democratic Republic of the)', 'congo-brazzaville' => 'Congo (Republic of the)', 'republic of the congo' => 'Congo (Republic of the)',
        'ivory coast' => "Côte d'Ivoire", "cote d'ivoire" => "Côte d'Ivoire", 'cote divoire' => "Côte d'Ivoire", 'united republic of tanzania' => 'Tanzania',
        'turkey' => 'Türkiye', 'turkiye' => 'Türkiye', 'czech republic' => 'Czechia', 'swaziland' => 'Eswatini', 'cape verde' => 'Cabo Verde', 'uae' => 'United Arab Emirates',
        'burma' => 'Myanmar', 'macedonia' => 'North Macedonia', 'russian federation' => 'Russia', 'republic of korea' => 'South Korea', 'korea' => 'South Korea',
        'the gambia' => 'Gambia', 'the netherlands' => 'Netherlands', 'holland' => 'Netherlands', 'vatican' => 'Vatican City', 'east timor' => 'Timor-Leste',
        'palestinian territories' => 'Palestine', 'south-sudan' => 'South Sudan', 'sao tome and principe' => 'São Tomé and Príncipe', 'reunion' => 'Réunion', 'curacao' => 'Curaçao',
    ];

    /** The dialling code of a country, as typed or chosen ("Uganda", "uganda ", "UK", "DR Congo"), digits without plus; null when we do not know it. */
    public static function dial(string $country): ?string
    {
        $c = trim((string) preg_replace('/\s+/u', ' ', $country));
        if ($c === '') {
            return null;
        }
        if (isset(self::DIAL[$c])) {
            return self::DIAL[$c];
        }
        $f = self::fold($c);
        $alias = self::ALIASES[$f] ?? null;
        if ($alias !== null) {
            return self::DIAL[$alias];
        }
        foreach (self::DIAL as $name => $code) {
            if (self::fold($name) === $f) {
                return $code;
            }
        }
        return null;
    }

    /** The countries of the list with their dialling codes, sorted by name: name => code (for the sign-up country list). @return array<string,string> */
    public static function dialList(): array
    {
        $list = self::DIAL;
        uksort($list, static fn(string $a, string $b): int => strcmp(self::fold($a), self::fold($b)));
        return $list;
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
