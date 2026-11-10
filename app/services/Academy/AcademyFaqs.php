<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;
use App\Core\Settings;

/**
 * The FAQs of the LAF Academy (page /academy/faq and the short list on /academy). They live in the `faqs` table under the group "academy", so the team edits, re-orders and
 * adds questions in Admin > FAQs. The first time the page is opened (and only once) the questions below are added, so the page is never empty.
 */
final class AcademyFaqs
{
    public const GROUP = 'academy';
    private const SEEDED_KEY = 'academy_faqs_seeded';

    public const ABOUT = [
        'LAF Academy is an initiative of Lumina Africa Foundation, committed to expanding access to education, digital skills and opportunities for individuals and communities.',
        'Our goal is to empower people with relevant knowledge and practical skills to participate meaningfully in the digital economy.',
    ];

    /** @return list<array{0:string,1:string}> question, answer */
    public static function defaults(): array
    {
        return [
            ['What is LAF Academy?', 'LAF Academy is an online learning initiative by Lumina Africa Foundation designed to make digital education and practical skills development accessible to learners across Africa and beyond.'],
            ['Who can enrol at LAF Academy?', 'Our courses are open to anyone interested in developing new skills, improving their knowledge and expanding their professional opportunities. Learners from different countries, educational backgrounds and professions are welcome.'],
            ['How do I register for a course?', 'Visit the LAF Academy platform, create your learner account, browse the available courses and enrol in the course that matches your interests.'],
            ['Are LAF Academy courses free?', 'Our learning model is designed to make education accessible and affordable. Course learning content is provided without traditional high training costs. A certificate processing and issuance fee of USD 3 applies when requesting a certificate after successfully completing a course. Please check individual course information for any additional requirements.'],
            ['Do I receive a certificate after completing a course?', 'Yes. Learners who successfully complete a course can request a certificate of completion issued directly by Lumina Africa Foundation through LAF Academy.'],
            ['How much does a certificate cost?', 'The certificate processing and issuance fee is USD 3 per course certificate.'],
            ['Are LAF Academy certificates authentic?', 'Yes. Our certificates are issued directly by Lumina Africa Foundation and represent successful completion of training offered through LAF Academy. We are continuously working to strengthen our certificate verification and quality assurance systems.'],
            ['Is LAF Academy accredited?', 'LAF Academy currently issues institutional certificates of course completion. These are not represented as government-accredited academic qualifications or internationally accredited certifications.'],
            ['Are LAF Academy certificates internationally recognised?', 'Our certificates provide evidence of additional training, learning and skills development. However, formal recognition depends on the requirements of individual employers, institutions and professional regulatory bodies. We cannot guarantee automatic recognition across countries.'],
            ['Can I use my certificate for employment or professional development?', 'Yes. You may present your certificate to employers and institutions as evidence of additional training and skills development. However, acceptance for formal qualifications, promotions, CPD credits or professional licensing is determined by the receiving institution.'],
            ['Are the certificates recognised by education authorities in Uganda or Rwanda?', 'Our certificates are issued by Lumina Africa Foundation. They are not currently presented as officially accredited qualifications by government education authorities in Uganda, Rwanda or other countries. Learners should confirm specific recognition requirements with the relevant authorities.'],
            ['What courses are available at LAF Academy?', 'Our learning programmes focus on digital literacy, technology and practical skills development. Course availability and new learning opportunities are published on the LAF Academy platform.'],
            ['Does LAF Academy offer advanced digital skills training?', "Our digital skills development agenda includes areas such as:\n\n<ul><li>Graphic Design and Digital Content Creation.</li><li>Web Design and Website Development.</li><li>Artificial Intelligence and AI Productivity Tools.</li><li>Educational Video Production.</li><li>Digital Marketing and Branding.</li><li>Computer Literacy and Digital Productivity.</li></ul>\n\nPlease check the academy's course catalogue for programmes currently available for enrolment."],
            ['Are the courses practical or purely theoretical?', 'We recognise the importance of practical learning. Our long-term objective is to expand project-based learning opportunities that allow learners to apply their knowledge, develop practical skills and build professional portfolios.'],
            ['Can teachers enrol at LAF Academy?', 'Absolutely! Teachers and education professionals are welcome. We particularly encourage educators to explore digital literacy, AI tools, graphic design and digital content creation to improve teaching methods and develop engaging learning materials.'],
            ['Can learners enrol from outside Uganda?', 'Yes. LAF Academy welcomes learners from across Africa and other parts of the world. Our vision is to expand access to digital education beyond geographical boundaries.'],
            ['Can I enrol in multiple courses?', 'Yes. Learners can explore and enrol in multiple courses based on their interests and learning objectives, subject to the enrolment requirements of each course.'],
            ['How can I contact LAF Academy for support?', 'For assistance with registration, enrolment, courses, certificates or other enquiries, contact the Lumina Africa Foundation team through the official communication channels published on our website.'],
        ];
    }

    /** Add the questions once, when the group is empty. Never throws. */
    public static function seed(): void
    {
        try {
            if (!Database::tableExists('faqs') || (string) Settings::get(self::SEEDED_KEY, '') === '1') {
                return;
            }
            if ((int) Database::value('SELECT COUNT(*) FROM faqs WHERE group_key = ?', [self::GROUP], 0) === 0) {
                foreach (self::defaults() as $i => [$q, $a]) {
                    Database::insert('faqs', ['group_key' => self::GROUP, 'question' => $q, 'answer' => $a, 'sort_order' => ($i + 1) * 10, 'is_active' => 1]);
                }
            }
            Settings::set(self::SEEDED_KEY, '1');
            Settings::flush();
        } catch (\Throwable) {
            // the page then simply shows no questions
        }
    }

    /** @return list<array{question:string,answer:string}> */
    public static function all(?int $limit = null): array
    {
        self::seed();
        try {
            $rows = Database::all('SELECT question, answer FROM faqs WHERE group_key = ? AND is_active = 1 ORDER BY sort_order ASC, id ASC' . ($limit !== null ? ' LIMIT ' . max(1, $limit) : ''), [self::GROUP]);
        } catch (\Throwable) {
            return [];
        }
        return array_map(static fn(array $r): array => ['question' => (string) $r['question'], 'answer' => (string) $r['answer']], $rows);
    }
}
