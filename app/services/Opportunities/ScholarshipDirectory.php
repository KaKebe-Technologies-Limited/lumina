<?php
declare(strict_types=1);

namespace App\Services\Opportunities;

use App\Core\Database;
use App\Core\Settings;
use App\Core\Slug;

/**
 * The SCHOLARSHIP AND FELLOWSHIP DIRECTORY: well-known, long-running programmes (Mastercard Foundation Scholars, Swedish Institute, Chevening, India's ICCR, DAAD, Fulbright, beVisioneers by
 * Mercedes-Benz and many more), each with the official page to apply on. They are added as ordinary opportunities (Admin > Opportunities), so the team can edit, hide or delete any of them.
 * The calls change every year, so no closing date is written here: each entry says to read the current call on the official page. Added once automatically, and again with the admin button
 * (programmes that are already there, or that the team deleted on purpose after the first run, are not added twice: they are matched by their address).
 */
final class ScholarshipDirectory
{
    private const NOTE = 'Calls, dates, the number of awards and the rules change every year: open the official page for the current call before you apply.';

    /**
     * title, organisation (offered by), provider (organisation|government|international), levels (u = undergraduate, g = graduate, p = PhD), type (scholarship|fellowship), where, summary,
     * who can apply, what you get, official link, featured (1/0)
     * @return list<array{0:string,1:string,2:string,3:string,4:string,5:string,6:string,7:string,8:string,9:string,10:int}>
     */
    public static function entries(): array
    {
        return [
            // ---------------------------------------------------------------------------------------------------- organisations
            ['Mastercard Foundation Scholars Program', 'Mastercard Foundation and partner universities', 'organisation', 'ug', 'scholarship', 'Africa and partner universities worldwide',
                'Scholarships for talented young people from Africa, especially those facing financial barriers, to study at partner universities in Africa and abroad and then lead change in their communities.',
                "Young African citizens with strong academic results and leadership potential\nFinancial need is a key condition\nApply through the partner university you want to attend: each runs its own selection",
                'Tuition and other study costs, support such as mentoring and leadership development, and a network of fellow Scholars', 'https://mastercardfdn.org/all/scholars/', 1],
            ['Aga Khan Foundation International Scholarship Programme', 'Aga Khan Foundation', 'organisation', 'gp', 'scholarship', 'Open to selected developing countries',
                'Partial funding (a grant and a loan) for postgraduate study for outstanding students from selected developing countries who have no other means of funding.',
                "Postgraduate study (Master's, and PhD in some cases)\nStrong academic record and no other source of funding\nCitizens of the eligible countries listed on the official page",
                '50% grant and 50% loan towards the cost of study', 'https://www.akdn.org/our-agencies/aga-khan-foundation/international-scholarship-programme', 0],
            ['Rhodes Scholarship', 'Rhodes Trust, University of Oxford', 'organisation', 'gp', 'scholarship', 'University of Oxford, United Kingdom',
                'The world\'s oldest international scholarship: fully funded postgraduate study at the University of Oxford for young people with academic excellence and a commitment to service.',
                "Postgraduate study at Oxford\nApplications are made through the constituency (country or region) you belong to, listed on the official page",
                'Full university and college fees, a living stipend and travel', 'https://www.rhodeshouse.ox.ac.uk/scholarships/the-rhodes-scholarship/', 0],
            ['Mandela Rhodes Scholarship', 'Mandela Rhodes Foundation', 'organisation', 'g', 'scholarship', 'South Africa',
                'Postgraduate scholarships and a leadership development programme for young Africans who study at a South African university.',
                "Young African leaders with a strong academic record\nPostgraduate study (Honours, Master's) in South Africa",
                'Tuition, accommodation and living costs, plus the Mandela Rhodes leadership programme', 'https://www.mandelarhodes.org/', 0],
            ['Gates Cambridge Scholarship', 'Gates Cambridge Trust', 'organisation', 'gp', 'scholarship', 'University of Cambridge, United Kingdom',
                'Fully funded full-time postgraduate study at the University of Cambridge for outstanding applicants from countries outside the UK.',
                "Applicants from outside the United Kingdom\nA place at Cambridge for a Master's or PhD, and the Scholarship application made with it",
                'The full cost of study at Cambridge, a maintenance allowance, airfare and an inaugural allowance', 'https://www.gatescambridge.org/', 0],
            ['Clarendon Scholarships', 'University of Oxford', 'organisation', 'gp', 'scholarship', 'University of Oxford, United Kingdom',
                'Graduate scholarships at Oxford awarded on academic merit to applicants for a Master\'s or doctoral course, with no separate application in most cases.',
                "Graduate applicants to Oxford, considered automatically with the course application where the rules say so",
                'Tuition fees and a living allowance', 'https://www.ox.ac.uk/clarendon', 0],
            ['Schwarzman Scholars', 'Schwarzman Scholars', 'organisation', 'g', 'scholarship', 'Tsinghua University, Beijing, China',
                'A one-year, fully funded Master\'s in Global Affairs at Tsinghua University for future leaders from around the world.',
                "Recent graduates and young professionals, usually aged 18 to 28\nLeadership potential and strong academics",
                'Tuition, room and board, travel and a stipend', 'https://www.schwarzmanscholars.org/', 0],
            ['Knight-Hennessy Scholars', 'Stanford University', 'organisation', 'gp', 'scholarship', 'Stanford University, United States',
                'Fully funded graduate study at Stanford (any field) together with a leadership programme for a cohort of future leaders.',
                "Applicants to a graduate programme at Stanford\nIndependence of thought, purposeful leadership and civic mindset",
                'Full funding for up to three years of graduate study, plus leadership development', 'https://knight-hennessy.stanford.edu/', 0],
            ['Yenching Academy Scholarship', 'Peking University', 'organisation', 'g', 'scholarship', 'Peking University, Beijing, China',
                'A fully funded two-year Master\'s in China Studies at Peking University for students from around the world.',
                "Strong undergraduate degree and an interest in China and the world",
                'Tuition, accommodation and a living stipend', 'https://yenchingacademy.pku.edu.cn/', 0],
            ['Rotary Peace Fellowship', 'Rotary Foundation', 'organisation', 'g', 'fellowship', 'Rotary Peace Centers worldwide',
                'Fully funded Master\'s degree or professional development certificate in peace and conflict studies for emerging leaders working for peace.',
                "Professionals with experience in peace-building, development or related fields\nGood academic record and leadership",
                'Tuition, living costs, travel and a field study period', 'https://www.rotary.org/en/our-programs/peace-fellowships', 0],
            ['beVisioneers: The Mercedes-Benz Fellowship', 'Mercedes-Benz and The DO School', 'organisation', '', 'fellowship', 'Online and in person, open worldwide',
                'A free 12-month fellowship for young people aged 16 to 28 who want to start environmental projects. Mercedes-Benz funds it; The DO School runs it. Fellows get online learning, mentoring, venture coaching and a chance to pitch for seed funding at a global summit.',
                "Young people aged 16 to 28\nAn idea or project for the environment and the drive to put it into practice",
                'No cost to take part; learning, 1:1 mentoring, venture coaching and a place at the Global Summit with a chance of seed funding', 'https://group.mercedes-benz.com/bevisioneers', 1],
            ['Mandela Washington Fellowship for Young African Leaders', 'U.S. Department of State (YALI)', 'government', '', 'fellowship', 'United States and Africa',
                'The flagship programme of the Young African Leaders Initiative: a leadership fellowship that brings young leaders from sub-Saharan Africa to the United States for academic and leadership training.',
                "Young African leaders (usually aged 25 to 35) with a record of leadership and public service\nCitizens of sub-Saharan African countries",
                'Six weeks of academic coursework and leadership training in the United States, networking and follow-on support', 'https://yali.state.gov/mwf/', 1],
            ['Obama Foundation Leaders: Africa', 'Obama Foundation', 'organisation', '', 'fellowship', 'Africa',
                'A leadership programme for emerging leaders across Africa who are working to create change in their communities.',
                "Emerging leaders with a project or initiative that serves their community",
                'Training, a peer network and support from the Obama Foundation', 'https://www.obama.org/', 0],
            ['Echoing Green Fellowship', 'Echoing Green', 'organisation', '', 'fellowship', 'Worldwide',
                'A seed-funding and support fellowship for social entrepreneurs who start new organisations to tackle inequality and injustice.',
                "Early-stage social entrepreneurs with a new organisation or idea",
                'Seed funding, a fellowship community and support over time', 'https://echoinggreen.org/fellowship/', 0],
            ['Open Society Fellowship', 'Open Society Foundations', 'organisation', '', 'fellowship', 'Worldwide',
                'A fellowship that supports practitioners, researchers and writers to develop bold ideas on the challenges that open societies face.',
                "Professionals and thinkers with a proposal that fits the themes on the official page",
                'A stipend and support to carry out the project', 'https://www.opensocietyfoundations.org/grants/open-society-fellowship', 0],
            ['Atlas Corps Fellowship', 'Atlas Corps', 'organisation', '', 'fellowship', 'United States and worldwide',
                'A leadership development fellowship that places nonprofit and public-sector professionals in organisations abroad for one year.',
                "Nonprofit or public-sector professionals with some years of experience and good English",
                'A placement with a host organisation, a stipend, health insurance and leadership training', 'https://atlascorps.org/', 0],
            ['Eisenhower Fellowships', 'Eisenhower Fellowships', 'organisation', '', 'fellowship', 'Worldwide',
                'A fellowship for mid-career leaders to build global networks and exchange ideas through a tailored programme in another country.',
                "Mid-career professionals with proven leadership, nominated or applying through the official process",
                'A customised multi-week programme abroad and membership of a global network', 'https://www.efi.org/', 0],
            ['Tony Elumelu Foundation Entrepreneurship Programme', 'Tony Elumelu Foundation', 'organisation', '', 'fellowship', 'Africa',
                'A programme for African entrepreneurs: business training, mentoring and seed funding for early-stage start-ups.',
                "African entrepreneurs with a business idea or an early-stage business\nAdults, usually aged 18 or over, from any African country",
                'Training, mentoring, a network and seed capital', 'https://tefconnect.com/', 0],
            // ---------------------------------------------------------------------------------------------------- governments
            ['Swedish Institute Scholarships for Global Professionals', 'Swedish Institute (Sweden)', 'government', 'g', 'scholarship', 'Sweden',
                'Scholarships from the Swedish Institute for master\'s studies in Sweden for professionals with leadership experience from selected countries, including many in Africa.',
                "Citizens of the eligible countries listed on the official page\nAt least 3,000 hours of work, volunteer or leadership experience\nAdmission to a master's programme at a Swedish university",
                'Tuition fees, a monthly living allowance, travel grant and insurance', 'https://si.se/en/apply/scholarships/swedish-institute-scholarships-for-global-professionals/', 1],
            ['Chevening Scholarships', 'UK Government (FCDO)', 'government', 'g', 'scholarship', 'United Kingdom',
                'The UK government\'s international scholarship for future leaders: a fully funded one-year Master\'s degree at a UK university.',
                "Citizens of Chevening-eligible countries, including Uganda\nAn undergraduate degree and at least two years of work experience\nReturn home for two years after the scholarship",
                'University fees, a living allowance, travel and visa costs', 'https://www.chevening.org/scholarships/', 1],
            ['Commonwealth Scholarships', 'Commonwealth Scholarship Commission (UK)', 'government', 'gp', 'scholarship', 'United Kingdom',
                'Scholarships for students from low and middle income Commonwealth countries to study Master\'s and PhD degrees in the UK and to return home to make a difference.',
                "Citizens of eligible Commonwealth countries\nApplications are made through the nominating agencies listed on the official page, or directly where the rules allow",
                'Tuition fees, a living allowance, airfares and other allowances', 'https://cscuk.fcdo.gov.uk/scholarships/', 0],
            ['ICCR Scholarships (Africa Scholarship Scheme)', 'Indian Council for Cultural Relations (Government of India)', 'government', 'ugp', 'scholarship', 'India',
                'Scholarships from the Government of India, run by the Indian Council for Cultural Relations, for students from African and other countries to study undergraduate, postgraduate and doctoral courses in India.',
                "Foreign nationals from countries covered by the scheme\nApply on the ICCR scholarship portal during the yearly call, usually through the Indian mission in your country",
                'Tuition, a monthly stipend, a contingency grant and accommodation support (see the current call)', 'https://iccr.gov.in/', 1],
            ['Study in India (SII) Scholarships', 'Ministry of Education (Government of India)', 'government', 'ugp', 'scholarship', 'India',
                'The Government of India\'s Study in India programme brings international students to Indian universities, with tuition fee waivers and scholarships for meritorious students.',
                "International students applying to the participating Indian institutions through the Study in India portal",
                'Fee waivers and scholarships at participating institutions (see the portal)', 'https://studyinindia.gov.in/', 0],
            ['ITEC: Indian Technical and Economic Cooperation Programme', 'Ministry of External Affairs (Government of India)', 'government', 'g', 'scholarship', 'India',
                'Fully funded short and medium-term training courses in India for professionals from partner countries, in fields such as ICT, management, engineering, health and agriculture.',
                "Professionals nominated through the partner government, apply on the ITEC portal\nCountries covered include many in Africa",
                'Course fees, accommodation, a living allowance and return airfare (see the current call)', 'https://www.itecgoi.in/', 0],
            ['DAAD Scholarships', 'German Academic Exchange Service (DAAD, Germany)', 'government', 'gp', 'scholarship', 'Germany',
                'The largest German funder of international study: scholarships for Master\'s and PhD study and research in Germany, including development-related programmes for African graduates.',
                "Graduates with a good first degree and, for many schemes, work experience\nEach scheme has its own conditions: choose yours in the DAAD scholarship database",
                'A monthly scholarship, health insurance, travel allowance and, for many schemes, a German language course', 'https://www.daad.de/en/study-and-research-in-germany/scholarships/', 1],
            ['Fulbright Foreign Student Program', 'U.S. Department of State', 'government', 'gp', 'scholarship', 'United States',
                'Fulbright scholarships for graduate students, young professionals and artists from abroad to study and conduct research in the United States.',
                "Apply through the Fulbright Commission or U.S. Embassy in your country\nA bachelor's degree and strong English",
                'Tuition, a living stipend, travel and health insurance (see the current call)', 'https://foreign.fulbrightonline.org/', 0],
            ['Australia Awards Scholarships', 'Australian Government (DFAT)', 'government', 'g', 'scholarship', 'Australia',
                'Long-term development scholarships from the Australian Government for people from developing countries, including Africa, to study at Australian universities and return to contribute to development.',
                "Citizens of the eligible countries, who meet the entry rules of the university\nApply during the yearly call on the Australia Awards website",
                'Full tuition, return air travel, a contribution to living costs and health cover', 'https://www.dfat.gov.au/people-to-people/australia-awards', 0],
            ['Chinese Government Scholarship (CSC)', 'China Scholarship Council (Government of China)', 'government', 'ugp', 'scholarship', 'China',
                'Full and partial scholarships from the Chinese government for international students at bachelor\'s, master\'s and doctoral level at Chinese universities.',
                "International students who are not Chinese citizens, apply to the university or through the Chinese Embassy in your country",
                'Tuition, accommodation and a monthly living allowance (see the current call)', 'https://www.campuschina.org/', 0],
            ['Türkiye Scholarships (Türkiye Bursları)', 'Presidency for Turks Abroad and Related Communities (Turkey)', 'government', 'ugp', 'scholarship', 'Türkiye',
                'A fully funded scholarship from the Turkish government for international students at undergraduate, master\'s and doctoral level.',
                "International students applying in the yearly call on the official portal\nAge limits apply by level",
                'Tuition, accommodation, a monthly stipend, health insurance, a Turkish language course and flight ticket', 'https://turkiyeburslari.gov.tr/', 0],
            ['Global Korea Scholarship (GKS)', 'National Institute for International Education (South Korea)', 'government', 'ugp', 'scholarship', 'South Korea',
                'The Korean Government Scholarship Program for international students to study undergraduate or graduate degrees at Korean universities.',
                "International students applying through the Korean Embassy in their country or through the invited universities",
                'Tuition, a monthly allowance, settlement allowance, a Korean language year and airfare', 'https://www.studyinkorea.go.kr/', 0],
            ['MEXT Japanese Government Scholarship', 'Ministry of Education, Culture, Sports, Science and Technology (Japan)', 'government', 'ugp', 'scholarship', 'Japan',
                'Scholarships from the Japanese government for international students for undergraduate, master\'s and doctoral study at Japanese universities.',
                "Apply through the Embassy of Japan in your country (Embassy recommendation) or through a Japanese university (University recommendation)",
                'Tuition, a monthly allowance and travel (see the current call)', 'https://www.studyinjapan.go.jp/en/', 0],
            ['Stipendium Hungaricum Scholarship', 'Tempus Public Foundation (Hungary)', 'government', 'ugp', 'scholarship', 'Hungary',
                'Hungary\'s government scholarship for students from partner countries to study bachelor\'s, master\'s and doctoral programmes at Hungarian universities.',
                "Students from partner countries, nominated or applying through the sending country's process on the official page",
                'Tuition, a monthly stipend, accommodation support and health insurance', 'https://stipendiumhungaricum.hu/', 0],
            ['Swiss Government Excellence Scholarships', 'Swiss Confederation (State Secretariat for Education, Research and Innovation)', 'government', 'p', 'scholarship', 'Switzerland',
                'Scholarships for foreign researchers and artists to pursue doctoral and postdoctoral research or arts study in Switzerland.',
                "Foreign nationals with a Master's degree, applying through the Swiss embassy or the nominating body of their country",
                'A monthly stipend, tuition fee exemption, health insurance and housing allowance', 'https://www.sbfi.admin.ch/sbfi/en/home/education/scholarships-and-grants/swiss-government-excellence-scholarships.html', 0],
            ['Eiffel Excellence Scholarship Programme', 'Campus France (French Government)', 'government', 'gp', 'scholarship', 'France',
                'The French government\'s excellence scholarship for international students in Master\'s and PhD programmes at French higher education institutions.',
                "The French institution applies on your behalf: you apply to the institution first",
                'A monthly allowance, international travel, health insurance and cultural activities', 'https://www.campusfrance.org/en/eiffel-scholarship-program-of-excellence', 0],
            ['Russian Government Scholarships', 'Ministry of Science and Higher Education (Russia)', 'government', 'ugp', 'scholarship', 'Russia',
                'Government-funded places for international students at Russian universities at bachelor\'s, specialist, master\'s and postgraduate level.',
                "Apply on the Education in Russia portal in the yearly quota call",
                'Free tuition and, for most students, a monthly stipend and a place in student housing', 'https://education-in-russia.com/', 0],
            ['Vanier Canada Graduate Scholarships', 'Government of Canada', 'government', 'p', 'scholarship', 'Canada',
                'Doctoral scholarships for international and Canadian students who show leadership skills and high standards of scholarly achievement in graduate studies.',
                "Nominated by a Canadian university: apply to a Canadian institution for a doctoral programme first",
                'CAD 50,000 a year for three years', 'https://vanier.gc.ca/', 0],
            ['Netherlands Fellowship Programmes (NFP)', 'Nuffic (Government of the Netherlands)', 'government', 'g', 'scholarship', 'The Netherlands',
                'Fellowships from the Dutch government for mid-career professionals from selected countries to study short courses or a Master\'s at Dutch institutions.',
                "Professionals from the eligible countries, with work experience and employer support\nApply to the course and then for the fellowship",
                'Tuition, a living allowance, travel and insurance', 'https://www.studyinnl.org/', 0],
            // ---------------------------------------------------------------------------------------------------- international organisations
            ['Erasmus Mundus Joint Masters Scholarships', 'European Union (Erasmus+)', 'international', 'g', 'scholarship', 'Europe (two or more countries)',
                'Fully funded joint Master\'s degrees taught by a consortium of universities in several European countries, open to students from around the world.',
                "Graduates with a first degree who apply directly to the joint master's programme you choose in the Erasmus+ catalogue",
                'Tuition, travel, installation costs and a monthly allowance', 'https://erasmus-plus.ec.europa.eu/opportunities/opportunities-for-individuals/students/erasmus-mundus-joint-masters', 1],
            ['Joint Japan/World Bank Graduate Scholarship Program', 'World Bank Group and the Government of Japan', 'international', 'g', 'scholarship', 'Worldwide',
                'Scholarships for development professionals from World Bank member countries to study development-related master\'s programmes at partner universities.',
                "At least three years of recent professional experience in a development-related field\nCitizens of World Bank member countries",
                'Tuition, a living allowance, airfare and health insurance', 'https://www.worldbank.org/en/programs/scholarships', 0],
            ['Islamic Development Bank Scholarship Programme', 'Islamic Development Bank', 'international', 'gp', 'scholarship', 'Member countries and partner universities',
                'Merit scholarships for master\'s and PhD study in science and technology for students from IsDB member countries and communities.',
                "Students from IsDB member countries, with the grades required on the official page",
                'Tuition, a living allowance and other study costs', 'https://www.isdb.org/scholarships', 0],
            ['Pan African University Scholarships', 'African Union Commission', 'international', 'gp', 'scholarship', 'Africa (Pan African University institutes)',
                'Scholarships from the African Union for African graduates to study Master\'s and PhD programmes at the Pan African University institutes across the continent.',
                "African citizens with a good first degree, applying in the yearly call on the PAU website",
                'Tuition, a monthly stipend, accommodation support and a research grant', 'https://pau-au.africa/', 0],
        ];
    }

    /** @return array{added:int,skipped:int} */
    public static function install(): array
    {
        if (!OpportunityMeta::install()) {
            return ['added' => 0, 'skipped' => 0];
        }
        $added = 0;
        $skipped = 0;
        foreach (self::entries() as [$title, $org, $provider, $lv, $type, $where, $summary, $elig, $benefits, $url, $featured]) {
            $hash = sha1('lafdir:' . $url);
            if (Database::value('SELECT 1 FROM opportunities WHERE source_hash = ? OR apply_url = ? LIMIT 1', [$hash, $url]) !== null) {
                $skipped++;
                continue;
            }
            $levels = [];
            foreach (['u' => 'undergraduate', 'g' => 'graduate', 'p' => 'phd'] as $k => $name) {
                if (str_contains($lv, $k)) {
                    $levels[] = $name;
                }
            }
            Database::insert('opportunities', [
                'title' => $title, 'slug' => Slug::unique('opportunities', $title, null, 'slug', [], 'opportunity'), 'category' => $type, 'organisation' => $org, 'location' => $where,
                'summary' => $summary, 'eligibility' => $elig, 'benefits' => $benefits,
                'how_to_apply' => "Apply on the official website: " . $url . "\n" . self::NOTE,
                'description' => '<p>' . htmlspecialchars($summary, ENT_QUOTES) . '</p><p>' . htmlspecialchars(self::NOTE, ENT_QUOTES) . '</p>',
                'deadline' => null, 'apply_url' => $url, 'source' => 'manual', 'source_hash' => $hash, 'source_name' => null,
                'provider_type' => $provider, 'study_levels' => $levels !== [] ? implode(',', $levels) : null,
                'is_featured' => $featured, 'is_active' => 1, 'needs_review' => 0, 'posted_at' => date('Y-m-d H:i:s'),
            ]);
            $added++;
        }
        return ['added' => $added, 'skipped' => $skipped];
    }

    /** Add the directory once, the first time the Opportunities page is opened (never again by itself: a programme the team deletes stays deleted). */
    public static function installOnce(): void
    {
        try {
            if ((string) Settings::get('opp_directory_seeded', '') === '1' || !OpportunityMeta::install()) {
                return;
            }
            self::install();
            self::addSources();
            Settings::set('opp_directory_seeded', '1');
            Settings::flush();
        } catch (\Throwable) {
            // the page simply shows what it has
        }
    }

    /** More websites that list scholarships, read like the existing sources (their headlines, with the source credited). @return int added */
    public static function addSources(): int
    {
        $n = 0;
        foreach ([['Scholars4Dev', 'https://www.scholars4dev.com/feed/'], ['After School Africa', 'https://www.afterschoolafrica.com/feed/']] as [$name, $url]) {
            $h = sha1(\App\Services\Opportunities\OpportunityImporter::normalizeLink($url));
            try {
                if (Database::value('SELECT 1 FROM opportunity_feeds WHERE url_hash = ? LIMIT 1', [$h]) === null) {
                    Database::insert('opportunity_feeds', ['name' => $name, 'url' => $url, 'url_hash' => $h, 'default_category' => 'scholarship', 'is_active' => 1]);
                    $n++;
                }
            } catch (\Throwable) {
                // the feed table is missing: nothing to add
            }
        }
        return $n;
    }
}
