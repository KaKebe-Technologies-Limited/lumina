-- =====================================================================================
-- Lumina Africa Foundation - seed data (REAL content only, taken from https://www.luminaafricafoundation.com/).
-- Owner of this file: B6 "content-seed". Source URL and caveats for every block: docs/CONTENT_NOTES.md.
--
-- IMPORT:   bin/setup.php (imports schema.sql, then this file, then adds any missing pages rows)
--           or   mysql -u root lumina < database/seed.sql
-- UTF-8:    the file is UTF-8 without BOM and starts with SET NAMES utf8mb4 (curly quotes and dashes are real characters).
--
-- IDEMPOTENT AND NON-DESTRUCTIVE:
--   * Re-running never duplicates anything and never TRUNCATEs or DELETEs.
--   * Tables with a unique natural key (settings, pages, programs, projects, activities, posts, content_blocks) use
--     INSERT ... ON DUPLICATE KEY UPDATE. An existing row is refreshed from this file ONLY while it is still pristine
--     (updated_at = created_at, i.e. nobody has edited it in the admin). Rows the owner edited are left alone.
--   * Tables without a natural unique key (stats, faqs, gallery_images, team_members) get their rows once (INSERT ... SELECT ...
--     WHERE NOT EXISTS), followed by the same pristine-only refresh UPDATE.
--   * To force every seeded row back to this file's values (development only; it overwrites admin edits):
--         mysql -u root lumina -e "SET @reseed = 1; SOURCE database/seed.sql"
-- Page COPY (headings, paragraphs, buttons) is NOT seeded: the registry defaults in app/config/pages/*.php carry the live wording.
-- A content_blocks row is written below only where the live wording differs from the registry default.
-- =====================================================================================

SET NAMES utf8mb4;
SET @reseed = IFNULL(@reseed, 0);

-- ------------------------------------------------------------------ settings (every key of app/config/settings_schema.php)
INSERT INTO settings (setting_key, setting_value) VALUES
  ('site_name', 'Lumina Africa Foundation'),
  ('tagline', 'Igniting Light, Inspiring Change.'),
  ('reg_number', 'GSD/R 1545'),
  ('founder_name', 'Moses Komakech'),
  ('footer_credit', 'Website managed by Lumina Africa Foundation.'),
  ('email_primary', 'luminaafricafoundation@gmail.com'),
  ('email_info', 'info@luminaafricafoundation.com'),
  ('phone_1', '+256-779-712-990'),
  ('phone_2', '+256-790-635-544'),
  ('whatsapp', 'https://wa.me/256779712990'),
  ('address', 'Laroo-Pece Division'),
  ('city_country', 'Gulu City, Northern Uganda'),
  ('map_embed_url', ''),
  ('notify_email', 'luminaafricafoundation@gmail.com'),
  ('social_facebook', 'https://www.facebook.com/profile.php?id=61578215262565'),
  ('social_instagram', 'https://www.instagram.com/luminaafricafdn/'),
  ('social_linkedin', 'https://www.linkedin.com/company/lumina-africa-foundation'),
  ('social_x', 'https://x.com/luminaafricafdn'),
  ('social_tiktok', 'https://www.tiktok.com/@lumina.africa.fou'),
  ('social_youtube', ''),
  ('donate_url', 'https://gofund.me/676b7a086'),
  ('donate_intro', ''),
  ('donate_bank_details', ''),
  ('donate_mobile_money', ''),
  ('donate_paypal_email', ''),
  ('logo_path', 'assets/img/brand/logo-512.png'),
  ('favicon_path', 'assets/img/brand/favicon-32.png'),
  ('og_image', 'assets/img/brand/logo-512.png'),
  ('meta_description', 'Lumina Africa Foundation is a registered community-based non-profit in Gulu City, Northern Uganda, bridging the digital divide for vulnerable youth through digital skills, employability, internships and entrepreneurship.'),
  ('analytics_id', '')
ON DUPLICATE KEY UPDATE
  setting_value = IF((@reseed = 1 OR updated_at = created_at), VALUES(setting_value), setting_value),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- ------------------------------------------------------------------ pages (one row per registry page; nav order mirrors the live site)
INSERT INTO pages (slug, title, type, template, is_published, show_in_nav, show_in_footer, nav_label, nav_order, parent_slug) VALUES
  ('home', 'Home', 'registry', 'default', 1, 1, 0, 'Home', 10, NULL),
  ('about', 'About Us', 'registry', 'default', 1, 1, 1, 'About', 20, NULL),
  ('programs', 'Programs', 'registry', 'default', 1, 1, 1, 'Programs', 30, NULL),
  ('projects', 'Projects', 'registry', 'default', 1, 1, 1, 'Projects', 40, NULL),
  ('involvement', 'Involvement', 'registry', 'default', 1, 1, 1, 'Involvement', 50, NULL),
  ('donate', 'Donate', 'registry', 'default', 1, 1, 1, 'Donate', 60, NULL),
  ('blog', 'Blog', 'registry', 'default', 1, 1, 1, 'Blog', 70, NULL),
  ('contact', 'Contact', 'registry', 'default', 1, 1, 1, 'Contact', 80, NULL),
  ('activities', 'Activities', 'registry', 'default', 1, 1, 1, 'Activities', 90, NULL),
  ('privacy', 'Privacy Policy', 'registry', 'default', 1, 0, 1, 'Privacy Policy', 200, NULL),
  ('global', 'Site-wide content (header, footer, shared buttons)', 'registry', 'default', 1, 0, 0, 'Site-wide content', 9000, NULL),
  ('site_errors', 'Error pages & maintenance message', 'registry', 'default', 1, 0, 0, 'Error pages', 9010, NULL),
  ('site_newsletter', 'Newsletter messages & unsubscribe page', 'registry', 'default', 1, 0, 0, 'Newsletter messages', 9020, NULL),
  ('site_custom', 'Labels for pages you create', 'registry', 'default', 1, 0, 0, 'Created-page labels', 9030, NULL),
  ('team', 'Our Team', 'registry', 'default', 1, 1, 1, 'Our Team', 21, 'about'),
  ('partners', 'Partners', 'registry', 'default', 1, 1, 1, 'Partners', 22, 'about'),
  ('reports', 'Reports & Downloads', 'registry', 'default', 1, 1, 1, 'Reports & Downloads', 23, 'about'),
  ('careers', 'Careers', 'registry', 'default', 1, 1, 1, 'Careers', 55, 'involvement'),
  ('events', 'Events', 'registry', 'default', 1, 1, 1, 'Events', 91, 'activities'),
  ('gallery', 'Gallery', 'registry', 'default', 1, 1, 1, 'Gallery', 92, 'activities'),
  ('news', 'News & Press', 'registry', 'default', 1, 1, 1, 'News', 71, 'blog'),
  ('stories', 'Impact Stories', 'registry', 'default', 1, 1, 1, 'Impact Stories', 72, 'blog')
ON DUPLICATE KEY UPDATE
  title = IF((@reseed = 1 OR updated_at = created_at), VALUES(title), title),
  type = IF((@reseed = 1 OR updated_at = created_at), VALUES(type), type),
  template = IF((@reseed = 1 OR updated_at = created_at), VALUES(template), template),
  is_published = IF((@reseed = 1 OR updated_at = created_at), VALUES(is_published), is_published),
  show_in_nav = IF((@reseed = 1 OR updated_at = created_at), VALUES(show_in_nav), show_in_nav),
  show_in_footer = IF((@reseed = 1 OR updated_at = created_at), VALUES(show_in_footer), show_in_footer),
  nav_label = IF((@reseed = 1 OR updated_at = created_at), VALUES(nav_label), nav_label),
  nav_order = IF((@reseed = 1 OR updated_at = created_at), VALUES(nav_order), nav_order),
  parent_slug = IF((@reseed = 1 OR updated_at = created_at), VALUES(parent_slug), parent_slug),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- ------------------------------------------------------------------ stats (home page counters; the live site's own labels are swapped, see CONTENT_NOTES.md)
INSERT INTO stats (group_key, label, value_number, prefix, suffix, description, icon, sort_order, is_active)
SELECT seed.group_key, seed.label, seed.value_number, seed.prefix, seed.suffix, seed.description, seed.icon, seed.sort_order, seed.is_active FROM (
  SELECT 'home' AS group_key, 'Youth Trained' AS label, 150 AS value_number, '' AS prefix, '+' AS suffix, NULL AS description, 'users' AS icon, 10 AS sort_order, 1 AS is_active
  UNION ALL SELECT 'home', 'Active Programs', 5, '', '', NULL, 'layers', 20, 1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM stats WHERE group_key = 'home');

UPDATE stats SET value_number = 150, prefix = '', suffix = '+', description = NULL, icon = 'users', sort_order = 10, is_active = 1, updated_at = created_at
  WHERE group_key = 'home' AND label = 'Youth Trained' AND (@reseed = 1 OR updated_at = created_at);
UPDATE stats SET value_number = 5, prefix = '', suffix = '', description = NULL, icon = 'layers', sort_order = 20, is_active = 1, updated_at = created_at
  WHERE group_key = 'home' AND label = 'Active Programs' AND (@reseed = 1 OR updated_at = created_at);

-- ------------------------------------------------------------------ programs (live /programs: four programs with their impact indicators)
INSERT INTO programs (slug, title, tagline, summary, body, image, image_alt, icon, is_featured, is_active, sort_order) VALUES
  ('digital-pathways-for-youth', 'Digital Pathways for Youth', 'Bridging the Digital Divide', 'Digital Pathways for Youth is our flagship program designed to equip vulnerable young people in Northern Uganda with practical digital skills and structured pathways to economic opportunity.', '<h2>Impact Indicators</h2><ul><li>120 vulnerable youth enrolled annually in structured digital skills training</li><li>Minimum 85% program completion rate</li><li>80% of participants demonstrate measurable improvement in digital competency at endline assessment</li><li>1 fully operational community digital learning hub established and maintained</li><li>Over 1,000 supervised digital learning hours delivered annually</li></ul><p>We measure digital skill progression through baseline and endline competency assessments.</p>', 'assets/img/site/photo-05.jpg', 'Young people seated in a bright room with laptops; one in the foreground types on a laptop', 'book-open', 1, 1, 10),
  ('digital-employability-internship-linkages', 'Digital Employability & Internship Linkages', 'From Skills to Work', 'Training without opportunity is incomplete. The Digital Employability & Internship Linkages Program connects trained youth to real income pathways.', '<h2>Impact Indicators</h2><ul><li>At least 40% of graduates engaged in income-generating activities within 6 months of program completion</li><li>30% of participants placed in internships or structured employment pathways</li><li>100% of participants receive structured career readiness and job preparation support</li><li>Minimum 10 active partnerships with institutions, NGOs, or local businesses</li></ul><p>We track employment outcomes through structured 3-month and 6-month follow-up assessments.</p>', 'assets/img/site/photo-06.jpg', 'Young people seated at desks in a lecture room, listening', 'briefcase', 0, 1, 20),
  ('inclusive-digital-access-for-out-of-school-youth', 'Inclusive Digital Access for Out-of-School Youth', 'No Youth Left Behind', 'Northern Uganda’s conflict history disrupted education for many young people. This program ensures that youth without formal academic certificates are not excluded from digital opportunity.', '<h2>Impact Indicators</h2><ul><li>At least 50% of program participants without formal academic certification</li><li>75% of participants demonstrate increased confidence in digital task performance</li><li>70% of participants independently perform practical documentation and typing services</li><li>Gender inclusion target maintained at minimum 50% female participation</li></ul><p>We monitor inclusion metrics and track access equity across all cohorts.</p>', 'assets/img/site/photo-04.jpg', 'Young people seated in red theatre-style seats at an event, some smiling and looking off to one side', 'users', 0, 1, 30),
  ('youth-digital-entrepreneurship-micro-income-development', 'Youth Digital Entrepreneurship & Micro-Income Development', 'Building Self-Reliance', 'This program supports youth to transition from skills acquisition to income generation through small digital enterprises.', '<h2>Impact Indicators</h2><ul><li>At least 25% of graduates initiate small digital income-generating activities</li><li>Minimum 20 youth-led micro digital service ventures launched annually</li><li>60% of supported youth report increased monthly income within 6 months</li><li>12 structured business mentorship sessions conducted annually</li></ul><p>Income progression and enterprise sustainability are tracked through periodic reporting and mentorship reviews.</p>', 'assets/img/site/photo-03.jpg', 'A man in a blue polo shirt points at a computer screen while young people wearing headphones work at computers around him', 'banknote', 0, 1, 40)
ON DUPLICATE KEY UPDATE
  title = IF((@reseed = 1 OR updated_at = created_at), VALUES(title), title),
  tagline = IF((@reseed = 1 OR updated_at = created_at), VALUES(tagline), tagline),
  summary = IF((@reseed = 1 OR updated_at = created_at), VALUES(summary), summary),
  body = IF((@reseed = 1 OR updated_at = created_at), VALUES(body), body),
  image = IF((@reseed = 1 OR updated_at = created_at), VALUES(image), image),
  image_alt = IF((@reseed = 1 OR updated_at = created_at), VALUES(image_alt), image_alt),
  icon = IF((@reseed = 1 OR updated_at = created_at), VALUES(icon), icon),
  is_featured = IF((@reseed = 1 OR updated_at = created_at), VALUES(is_featured), is_featured),
  is_active = IF((@reseed = 1 OR updated_at = created_at), VALUES(is_active), is_active),
  sort_order = IF((@reseed = 1 OR updated_at = created_at), VALUES(sort_order), sort_order),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- ------------------------------------------------------------------ projects (live /projects: the four project cards, each linked to its program)
INSERT INTO projects (program_id, slug, title, tagline, summary, body, image, image_alt, status, location, is_featured, is_active, sort_order) VALUES
  ((SELECT id FROM programs WHERE slug = 'digital-pathways-for-youth'), 'digital-pathways-for-youth', 'Digital Pathways for Youth', 'Bridging the Digital Divide', 'Digital Pathways for Youth is our flagship program designed to equip vulnerable young people in Northern Uganda with practical digital skills and structured pathways to economic opportunity.', '<p>Digital Pathways for Youth is our flagship program designed to equip vulnerable young people in Northern Uganda with practical digital skills and structured pathways to economic opportunity.</p><p>Through community-based digital learning hubs, we provide access to computers, internet connectivity, and hands-on training in:</p><ul><li>Basic and intermediate computer literacy</li><li>Typing and document preparation</li><li>Printing and office productivity tools</li><li>Data entry and digital record management</li><li>Internet navigation and digital communication</li></ul><p>The program is inclusive and does not segregate by academic attainment. Whether a young person completed UCE, UACE, or no formal certification at all, they can access foundational digital skills that open doors to income and opportunity.</p><p>We do not only train. We link skills to real pathways.</p>', 'assets/img/site/photo-05.jpg', 'Young people seated in a bright room with laptops; one in the foreground types on a laptop', 'ongoing', 'Northern Uganda', 1, 1, 10),
  ((SELECT id FROM programs WHERE slug = 'digital-employability-internship-linkages'), 'digital-employability-internship-linkages', 'Digital Employability & Internship Linkages', 'From Skills to Work', 'Training without opportunity is incomplete. The Digital Employability & Internship Linkages Program connects trained youth to real income pathways.', '<p>Training without opportunity is incomplete. The Digital Employability &amp; Internship Linkages Program connects trained youth to real income pathways.</p><p>We work with local businesses, NGOs, institutions, and digital platforms to create:</p><ul><li>Internship placements</li><li>Administrative and documentation roles</li><li>Data collection opportunities</li><li>Entry-level digital work</li><li>Job readiness coaching and career guidance</li></ul><p>Participants receive support in CV development, interview preparation, professional communication, and workplace readiness.</p><p>Our focus is measurable employment outcomes that strengthen youth economic resilience.</p>', 'assets/img/site/photo-06.jpg', 'Young people seated at desks in a lecture room, listening', 'ongoing', 'Northern Uganda', 0, 1, 20),
  ((SELECT id FROM programs WHERE slug = 'inclusive-digital-access-for-out-of-school-youth'), 'inclusive-digital-access-for-out-of-school-youth', 'Inclusive Digital Access for Out-of-School Youth', 'No Youth Left Behind', 'Northern Uganda’s conflict history disrupted education for many young people. This program ensures that youth without formal academic certificates are not excluded from digital opportunity.', '<p>Northern Uganda’s conflict history disrupted education for many young people. This program ensures that youth without formal academic certificates are not excluded from digital opportunity.</p><p>We provide structured, hands-on digital training tailored for:</p><ul><li>Youth with interrupted education</li><li>School leavers</li><li>Young women facing structural barriers</li><li>Youth affected by conflict and displacement</li></ul><p>The training focuses on practical, income-generating competencies such as typing services, documentation support, printing, and digital record keeping.</p><p>By removing academic barriers, we restore access to opportunity and dignity.</p><p>Digital inclusion must be inclusive by design.</p>', 'assets/img/site/photo-04.jpg', 'Young people seated in red theatre-style seats at an event, some smiling and looking off to one side', 'ongoing', 'Northern Uganda', 0, 1, 30),
  ((SELECT id FROM programs WHERE slug = 'youth-digital-entrepreneurship-micro-income-development'), 'youth-digital-entrepreneurship-micro-income-development', 'Youth Digital Entrepreneurship & Micro-Income Development', 'Building Self-Reliance', 'This program supports youth to transition from skills acquisition to income generation through small digital enterprises.', '<p>This program supports youth to transition from skills acquisition to income generation through small digital enterprises.</p><p>Participants receive training and mentorship in:</p><ul><li>Micro digital business development</li><li>Service-based entrepreneurship</li><li>Community documentation services</li><li>Digital printing and administrative support enterprises</li><li>Basic financial literacy and business planning</li></ul><p>By supporting youth to launch and manage small digital service ventures, we promote self-reliance and sustainable livelihoods.</p><p>Even small income streams create stability, confidence, and resilience.</p>', 'assets/img/site/photo-03.jpg', 'A man in a blue polo shirt points at a computer screen while young people wearing headphones work at computers around him', 'ongoing', 'Northern Uganda', 0, 1, 40)
ON DUPLICATE KEY UPDATE
  program_id = IF((@reseed = 1 OR updated_at = created_at), VALUES(program_id), program_id),
  title = IF((@reseed = 1 OR updated_at = created_at), VALUES(title), title),
  tagline = IF((@reseed = 1 OR updated_at = created_at), VALUES(tagline), tagline),
  summary = IF((@reseed = 1 OR updated_at = created_at), VALUES(summary), summary),
  body = IF((@reseed = 1 OR updated_at = created_at), VALUES(body), body),
  image = IF((@reseed = 1 OR updated_at = created_at), VALUES(image), image),
  image_alt = IF((@reseed = 1 OR updated_at = created_at), VALUES(image_alt), image_alt),
  status = IF((@reseed = 1 OR updated_at = created_at), VALUES(status), status),
  location = IF((@reseed = 1 OR updated_at = created_at), VALUES(location), location),
  is_featured = IF((@reseed = 1 OR updated_at = created_at), VALUES(is_featured), is_featured),
  is_active = IF((@reseed = 1 OR updated_at = created_at), VALUES(is_active), is_active),
  sort_order = IF((@reseed = 1 OR updated_at = created_at), VALUES(sort_order), sort_order),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- ------------------------------------------------------------------ activities (live /activities: the one real activity)
INSERT INTO activities (slug, title, summary, body, image, image_alt, activity_date, location, is_active, sort_order) VALUES
  ('visit-to-wii-quarry-site', 'Visit to Wii Got Quarry Site', 'Our Director joined the Delegation of the Northern Region Youth Parliament for a dynamic outreach event at the Wii Got Quarry Site in Gulu City, together with youth leaders from the EU Youth Sounding Board and representatives of the 8th Northern Regional Youth Parliament.', '<p>We are thrilled to share that our Director has joined the incredible team of the Delegation of the Northern Region Youth Parliament! He had the amazing opportunity to participate in a dynamic outreach event at the Wii Got Quarry Site in Gulu City, where he collaborated passionately with enthusiastic youth leaders from the EU Youth Sounding Board and the representatives of the 8th Northern Regional Youth Parliament. This exciting initiative aimed to empower and inspire our vibrant young participants!</p><p>We engaged the quarry youth in stimulating discussions about environmental sustainability and community development while providing them with essential safety gear to keep them safe and sound during all activities. Plus, in line with our commitment to fostering sustainability, the event was highlighted by the donation and planting of 20 tree seedlings! These seedlings symbolize hope for a brighter, greener future and deepen our understanding of the importance of environmental stewardship.</p><p>This incredible collective effort showcases our strong belief that young people can lead the charge for positive change in their communities, embracing active roles in environmental protection and social responsibility!</p><p><a href="/blog/empowering-voices-planting-hope-lumina-africa-foundations-weekend-at-wii-got-quarry">Read the full blog post</a></p>', NULL, NULL, NULL, 'Wii Got Quarry Site, Laroo, Gulu City', 1, 10)
ON DUPLICATE KEY UPDATE
  title = IF((@reseed = 1 OR updated_at = created_at), VALUES(title), title),
  summary = IF((@reseed = 1 OR updated_at = created_at), VALUES(summary), summary),
  body = IF((@reseed = 1 OR updated_at = created_at), VALUES(body), body),
  image = IF((@reseed = 1 OR updated_at = created_at), VALUES(image), image),
  image_alt = IF((@reseed = 1 OR updated_at = created_at), VALUES(image_alt), image_alt),
  activity_date = IF((@reseed = 1 OR updated_at = created_at), VALUES(activity_date), activity_date),
  location = IF((@reseed = 1 OR updated_at = created_at), VALUES(location), location),
  is_active = IF((@reseed = 1 OR updated_at = created_at), VALUES(is_active), is_active),
  sort_order = IF((@reseed = 1 OR updated_at = created_at), VALUES(sort_order), sort_order),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- ------------------------------------------------------------------ posts (live /blog: two posts; the second one is imported as a DRAFT, see CONTENT_NOTES.md)
INSERT INTO posts (post_type, slug, title, excerpt, body, cover_image, cover_alt, category, status, published_at, author_name, is_featured, seo_title, seo_description) VALUES
  ('blog', 'empowering-voices-planting-hope-lumina-africa-foundations-weekend-at-wii-got-quarry', 'Empowering Voices, Planting Hope – Lumina Africa Foundation’s Weekend at Wii Got Quarry', 'This past weekend, Lumina Africa Foundation joined hands with youth leaders from the EU Youth Sounding Board and the 8th Northern Regional Youth Parliament for an inspiring outreach at the Wii Got Quarry Site in Laroo, Gulu City.', '<h2>About the Outreach at Wii Got Quarry Site, Gulu City</h2><p>At Lumina Africa Foundation, we believe that real change begins at the grassroots, where young people live, work, and dream. Over the weekend, our Director <strong>Moses Komakech</strong>, together with youth leaders from the <strong>EU Youth Sounding Board</strong> and the <strong>8th Northern Regional Youth Parliament</strong>, led an outreach at <strong>Wii Got Quarry Site in Laroo, Gulu City</strong>.</p><p>The visit was designed to listen to quarry youth, understand their realities, and co-create solutions that enhance both their livelihoods and well-being.</p><h2>Why We Visited the Quarry Youth</h2><p>The youth at Wii Got Quarry are hardworking and resilient, yet they face daily challenges such as unsafe working conditions, lack of protective gear, and limited opportunities for growth. As a Foundation that champions <strong>youth empowerment, community development, and sustainability</strong>, we saw it as an important step to directly engage them, hear their voices, and provide immediate support while exploring long-term solutions.</p><h2>What We Did</h2><h3>1. Dialogue and Engagement</h3><ul><li>Conducted <strong>interactive discussions, interviews, and questionnaires</strong> with the quarry youth to capture their challenges and aspirations.</li><li>Created a safe space where they could freely share their experiences and suggest solutions.</li></ul><h3>2. Safety and Protection</h3><ul><li>Distributed <strong>protective gloves and reflector jackets</strong> to enhance their safety while working at the quarry.</li><li>Used the reflector jackets as a symbol to spread awareness about youth rights, safety, and leadership engagement.</li></ul><h3>3. Environmental Sustainability</h3><ul><li>Donated and planted <strong>20 tree seedlings</strong> within the quarry site and surrounding areas.</li><li>Encouraged quarry youth to embrace environmental stewardship as part of building a sustainable future.</li></ul><h2>Our Impact</h2><ul><li><strong>Direct beneficiaries:</strong> Quarry youth who received protective gear and participated in the tree planting activity.</li><li><strong>Community awareness:</strong> Broadened understanding of the role of the Regional Youth Parliament and Lumina Africa Foundation in amplifying youth voices.</li><li><strong>Environmental contribution:</strong> Set the foundation for a greener quarry site and a cleaner environment through tree planting.</li></ul><h2>Looking Ahead</h2><p>This initiative is just the beginning. Lumina Africa Foundation is committed to:</p><ul><li>Continuing similar outreach programs in other vulnerable communities.</li><li>Strengthening partnerships with youth networks, local leaders, and organizations to scale impact.</li><li>Designing programs that address both <strong>immediate needs</strong> (such as safety and protection) and <strong>long-term empowerment</strong> (such as skills development, advocacy, and environmental action).</li></ul><h2>Conclusion</h2><p>The visit to Wii Got Quarry was a <strong>powerful reminder that young people must be at the center of change</strong>. By listening to their voices, acting on their challenges, and involving them in solutions, we can build resilient, empowered, and sustainable communities.</p><p>At Lumina Africa Foundation, we remain steadfast in our mission to <strong>uplift youth, protect their rights, and create opportunities that inspire hope and transformation</strong>.</p><p><strong>Together, we are empowering voices, protecting lives, and planting hope for the future.</strong></p>', NULL, NULL, 'Community Engagement', 'published', '2025-08-22 09:34:06', 'Moses Komakech', 0, NULL, 'This past weekend, Lumina Africa Foundation joined hands with youth leaders from the EU Youth Sounding Board and the 8th Northern Regional Youth Parliament for an inspiring outreach at the Wii Got Quarry Site in Laroo, Gulu City.'),
  ('blog', 'breaking-the-stigma-discussing-mental-health-in-northern-uganda', 'Breaking the Stigma: Discussing Mental Health in Northern Uganda', 'Mental health remains one of the most overlooked issues in Northern Uganda, where stigma, cultural misconceptions, and limited access to care prevent many from seeking help. This article explores the realities faced by communities struggling with trauma, stress, and mental illness, particularly in post-conflict areas.', '<h2>Understanding the Mental Health Landscape</h2><p>Mental health remains a pressing issue worldwide, and Northern Uganda is no exception. The aftermath of prolonged conflicts and socio-economic struggles has significantly impacted the mental well-being of its populace. While the stigma surrounding mental health issues continues to persist, conversations surrounding it are becoming increasingly vital. Addressing this stigma is a crucial step towards fostering better mental health awareness and support systems.</p><h2>Confronting Cultural Taboos</h2><p>In many communities within Northern Uganda, discussing mental health is often viewed through a lens of cultural taboo. Many individuals fear judgment or isolation if they openly express their mental health struggles. This societal fear contributes to a lack of understanding and acceptance of mental health conditions. By challenging these cultural norms and encouraging open discussions, individuals can begin to share their experiences, thus helping to normalize the conversation surrounding mental health. Increased awareness can ultimately lead to a more supportive environment, reducing the stigma attached to these discussions.</p><h2>Community Initiatives and Support Systems</h2><p>One of the most effective ways to break the stigma surrounding mental health is through community initiatives. Organizations and local leaders are increasingly stepping up to provide educational resources and promote mental health awareness. Workshops, seminars, and support groups allow individuals to interact and discuss their feelings in a safe space. Moreover, it is vital for healthcare professionals to be trained in culturally sensitive techniques that address mental health without judgment, thereby improving the overall discourse around this essential subject.</p><p>In addition, involving local influencers in these initiatives can help shift perceptions. Using popular culture and trusted community members to promote mental health discussions can significantly affect how issues are perceived. By harnessing these relationships, community initiatives can successfully break down barriers and encourage individuals to seek help when needed.</p><p>In conclusion, breaking the stigma associated with mental health issues in Northern Uganda requires a collective effort. By fostering open discussions, challenging cultural biases, and implementing community initiatives, we can pave the way for healthier mental health awareness. Society as a whole benefits when individuals feel accepted and supported in addressing their mental health. The journey may be long, but with persistence, the stigma surrounding mental health can eventually dissipate.</p>', NULL, NULL, NULL, 'draft', '2025-08-25 00:00:00', 'Moses Komakech', 0, NULL, NULL)
ON DUPLICATE KEY UPDATE
  post_type = IF((@reseed = 1 OR updated_at = created_at), VALUES(post_type), post_type),
  title = IF((@reseed = 1 OR updated_at = created_at), VALUES(title), title),
  excerpt = IF((@reseed = 1 OR updated_at = created_at), VALUES(excerpt), excerpt),
  body = IF((@reseed = 1 OR updated_at = created_at), VALUES(body), body),
  cover_image = IF((@reseed = 1 OR updated_at = created_at), VALUES(cover_image), cover_image),
  cover_alt = IF((@reseed = 1 OR updated_at = created_at), VALUES(cover_alt), cover_alt),
  category = IF((@reseed = 1 OR updated_at = created_at), VALUES(category), category),
  status = IF((@reseed = 1 OR updated_at = created_at), VALUES(status), status),
  published_at = IF((@reseed = 1 OR updated_at = created_at), VALUES(published_at), published_at),
  author_name = IF((@reseed = 1 OR updated_at = created_at), VALUES(author_name), author_name),
  is_featured = IF((@reseed = 1 OR updated_at = created_at), VALUES(is_featured), is_featured),
  seo_title = IF((@reseed = 1 OR updated_at = created_at), VALUES(seo_title), seo_title),
  seo_description = IF((@reseed = 1 OR updated_at = created_at), VALUES(seo_description), seo_description),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- ------------------------------------------------------------------ jobs: none seeded on purpose (live /careers: "Currently, there are no openings."; the volunteer pathway is page copy in the careers registry)

-- ------------------------------------------------------------------ faqs (live /donate; three answers are inactive until the foundation confirms them)
INSERT INTO faqs (group_key, question, answer, sort_order, is_active)
SELECT seed.group_key, seed.question, seed.answer, seed.sort_order, seed.is_active FROM (
  SELECT 'donate' AS group_key, 'How can I donate?' AS question, 'You can donate via mobile money, bank transfer, or international donations through PayPal/Stripe.' AS answer, 10 AS sort_order, 0 AS is_active
  UNION ALL SELECT 'donate', 'Is my donation tax-deductible?', 'Please check with your local tax authority regarding the deductibility of donations made to our organization.', 20, 1
  UNION ALL SELECT 'donate', 'What programs do donations support?', 'Donations support school supplies, digital literacy training, and community health outreach programs for children and families.', 30, 0
  UNION ALL SELECT 'donate', 'Can I donate monthly?', 'Yes, you can set up a recurring donation to support our ongoing programs.', 40, 0
  UNION ALL SELECT 'donate', 'Who can I contact?', 'You can contact us through our website for any inquiries or assistance regarding donations.', 50, 1
  UNION ALL SELECT 'donate', 'What is the impact of my donation?', 'Every contribution directly helps us provide essential resources and programs to children, youth, and families in need.', 60, 1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM faqs WHERE group_key = 'donate');

UPDATE faqs SET answer = 'You can donate via mobile money, bank transfer, or international donations through PayPal/Stripe.', sort_order = 10, is_active = 0, updated_at = created_at
  WHERE group_key = 'donate' AND question = 'How can I donate?' AND (@reseed = 1 OR updated_at = created_at);
UPDATE faqs SET answer = 'Please check with your local tax authority regarding the deductibility of donations made to our organization.', sort_order = 20, is_active = 1, updated_at = created_at
  WHERE group_key = 'donate' AND question = 'Is my donation tax-deductible?' AND (@reseed = 1 OR updated_at = created_at);
UPDATE faqs SET answer = 'Donations support school supplies, digital literacy training, and community health outreach programs for children and families.', sort_order = 30, is_active = 0, updated_at = created_at
  WHERE group_key = 'donate' AND question = 'What programs do donations support?' AND (@reseed = 1 OR updated_at = created_at);
UPDATE faqs SET answer = 'Yes, you can set up a recurring donation to support our ongoing programs.', sort_order = 40, is_active = 0, updated_at = created_at
  WHERE group_key = 'donate' AND question = 'Can I donate monthly?' AND (@reseed = 1 OR updated_at = created_at);
UPDATE faqs SET answer = 'You can contact us through our website for any inquiries or assistance regarding donations.', sort_order = 50, is_active = 1, updated_at = created_at
  WHERE group_key = 'donate' AND question = 'Who can I contact?' AND (@reseed = 1 OR updated_at = created_at);
UPDATE faqs SET answer = 'Every contribution directly helps us provide essential resources and programs to children, youth, and families in need.', sort_order = 60, is_active = 1, updated_at = created_at
  WHERE group_key = 'donate' AND question = 'What is the impact of my donation?' AND (@reseed = 1 OR updated_at = created_at);

-- ------------------------------------------------------------------ gallery_images (photos bundled under assets/img/site; the two photos of children are inactive until consent is confirmed)
INSERT INTO gallery_images (image, alt_text, caption, album, is_active, sort_order)
SELECT seed.image, seed.alt_text, seed.caption, seed.album, seed.is_active, seed.sort_order FROM (
  SELECT 'assets/img/site/photo-03.jpg' AS image, 'A man in a blue polo shirt points at a computer screen while young people wearing headphones work at computers around him' AS alt_text, 'Young people working at computers' AS caption, 'Digital learning' AS album, 1 AS is_active, 10 AS sort_order
  UNION ALL SELECT 'assets/img/site/photo-05.jpg', 'Young people seated in a bright room with laptops; one in the foreground types on a laptop', 'Young people with laptops in a classroom', 'Digital learning', 1, 20
  UNION ALL SELECT 'assets/img/site/photo-04.jpg', 'Young people seated in red theatre-style seats at an event, some smiling and looking off to one side', 'Young people at an event', 'Events and gatherings', 1, 30
  UNION ALL SELECT 'assets/img/site/photo-06.jpg', 'Young people seated at desks in a lecture room, listening', 'Young people in a lecture room', 'Events and gatherings', 1, 40
  UNION ALL SELECT 'assets/img/site/photo-02.jpg', 'Black-and-white photograph of a crowd of children looking at the camera', 'Children looking at the camera', 'Community', 0, 50
  UNION ALL SELECT 'assets/img/site/photo-08.jpg', 'Black-and-white portrait of a child holding a cardboard sign that reads HOPE', 'A child holding a sign reading HOPE', 'Community', 0, 60
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM gallery_images g WHERE g.image = seed.image);

UPDATE gallery_images SET alt_text = 'A man in a blue polo shirt points at a computer screen while young people wearing headphones work at computers around him', caption = 'Young people working at computers', album = 'Digital learning', is_active = 1, sort_order = 10, updated_at = created_at
  WHERE image = 'assets/img/site/photo-03.jpg' AND (@reseed = 1 OR updated_at = created_at);
UPDATE gallery_images SET alt_text = 'Young people seated in a bright room with laptops; one in the foreground types on a laptop', caption = 'Young people with laptops in a classroom', album = 'Digital learning', is_active = 1, sort_order = 20, updated_at = created_at
  WHERE image = 'assets/img/site/photo-05.jpg' AND (@reseed = 1 OR updated_at = created_at);
UPDATE gallery_images SET alt_text = 'Young people seated in red theatre-style seats at an event, some smiling and looking off to one side', caption = 'Young people at an event', album = 'Events and gatherings', is_active = 1, sort_order = 30, updated_at = created_at
  WHERE image = 'assets/img/site/photo-04.jpg' AND (@reseed = 1 OR updated_at = created_at);
UPDATE gallery_images SET alt_text = 'Young people seated at desks in a lecture room, listening', caption = 'Young people in a lecture room', album = 'Events and gatherings', is_active = 1, sort_order = 40, updated_at = created_at
  WHERE image = 'assets/img/site/photo-06.jpg' AND (@reseed = 1 OR updated_at = created_at);
UPDATE gallery_images SET alt_text = 'Black-and-white photograph of a crowd of children looking at the camera', caption = 'Children looking at the camera', album = 'Community', is_active = 0, sort_order = 50, updated_at = created_at
  WHERE image = 'assets/img/site/photo-02.jpg' AND (@reseed = 1 OR updated_at = created_at);
UPDATE gallery_images SET alt_text = 'Black-and-white portrait of a child holding a cardboard sign that reads HOPE', caption = 'A child holding a sign reading HOPE', album = 'Community', is_active = 0, sort_order = 60, updated_at = created_at
  WHERE image = 'assets/img/site/photo-08.jpg' AND (@reseed = 1 OR updated_at = created_at);

-- ------------------------------------------------------------------ team_members (only the founder; the other board members named on the live About page are NOT seeded)
INSERT INTO team_members (name, role_title, team_group, bio, photo, email, linkedin, x, sort_order, is_active)
SELECT seed.name, seed.role_title, seed.team_group, seed.bio, seed.photo, seed.email, seed.linkedin, seed.x, seed.sort_order, seed.is_active FROM (
  SELECT 'Moses Komakech' AS name, 'Founder' AS role_title, 'board' AS team_group, 'Moses Komakech founded Lumina Africa Foundation to bridge the digital divide and create pathways for youth to learn, work, and lead in the digital age. He is listed as Chairperson of the Board of Directors.' AS bio, NULL AS photo, NULL AS email, 'https://www.linkedin.com/in/moses-komakech-27a61120b' AS linkedin, NULL AS x, 10 AS sort_order, 1 AS is_active
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM team_members t WHERE t.name = seed.name);

UPDATE team_members SET role_title = 'Founder', team_group = 'board', bio = 'Moses Komakech founded Lumina Africa Foundation to bridge the digital divide and create pathways for youth to learn, work, and lead in the digital age. He is listed as Chairperson of the Board of Directors.', photo = NULL, email = NULL, linkedin = 'https://www.linkedin.com/in/moses-komakech-27a61120b', x = NULL, sort_order = 10, is_active = 1, updated_at = created_at
  WHERE name = 'Moses Komakech' AND (@reseed = 1 OR updated_at = created_at);

-- ------------------------------------------------------------------ content_blocks (only where the live wording differs from the registry default)
-- (none: every registry default already carries the live wording)

-- Not seeded on purpose (real data the owner must supply): partners, documents, events, event_registrations, stories, subscribers,
-- contact_messages, applications, involvement_requests, donations, users. See docs/CONTENT_NOTES.md.
