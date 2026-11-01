<?php
return [
    'slug'        => 'career-readiness-cvs-applications',
    'title'       => 'Career Readiness: CVs, Cover Letters and Job Applications',
    'category'    => 'careers',
    'level'       => 'beginner',
    'icon'        => 'briefcase',
    'sort'        => 640,
    'summary'     => 'A full application toolkit: understand the East African job market, avoid scams, write a strong CV, cover letter and application email, prepare for tests, follow up, handle offers and start well.',
    'description' => '<p>Getting a job is a skill that can be learned. This course gives you a complete toolkit for finding real opportunities in Uganda and East Africa and applying for them well. It is written for fresh graduates, diploma holders, school leavers and anyone returning to the job market, including people whose experience so far is informal or unpaid.</p><p>You will learn how jobs are really found in the formal, NGO, government and private sectors, how to read an advert and match it honestly, and how to spot fake offers and pay-to-apply scams. You will write a CV from scratch, with full worked examples for a graduate, a diploma holder and a school leaver, then a cover letter and an application email, and you will practise online forms and tailoring each application.</p><p>The later modules cover proof of your work, a professional online presence, tests and assessments, a simple tracker for your search, following up, learning from rejection, and what to do when an offer arrives: contracts, probation, polite salary talks and your first 90 days. This course goes deeper than the short CV introduction in Job-Ready Digital Skills, so you can take both.</p>',
    'outcomes'    => [
        'Describe the main kinds of work and entry routes in the East African job market',
        'Find genuine vacancies and recognise pay-to-apply and fake-offer scams',
        'Read a job advert and match your honest experience to its requirements',
        'Write a clear CV with achievements, in a suitable format and length',
        'Write a tailored cover letter and a professional application email',
        'Complete online forms and portals accurately, with keywords that fit',
        'Prepare references, certificates and a simple portfolio of proof',
        'Prepare for aptitude tests and assessments and keep a job-search tracker',
        'Follow up politely, learn from rejection, and handle an offer, probation and your first 90 days',
    ],
    'requirements' => ['A smartphone or computer with internet access', 'No experience needed; basic use of Word or Google Docs is helpful for the practice tasks'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'LinkedIn Help Center', 'url' => 'https://www.linkedin.com/help/linkedin'],
        ['title' => 'Google Docs help', 'url' => 'https://support.google.com/docs'],
        ['title' => 'Microsoft Support: Word help', 'url' => 'https://support.microsoft.com/word'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: The job market and finding real jobs',
            'summary' => 'Understand where work exists in East Africa, find genuine vacancies, avoid scams, and read adverts carefully.',
            'lessons' => [
                [
                    'title'   => 'The East African job market: where the work is',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to name the main routes into work in East Africa and choose the ones that fit your situation. This matters because many young people apply only for a few famous employers and miss the many other doors that are open.</p>

<h3>The main kinds of opportunity</h3>
<table>
<thead><tr><th>Route</th><th>What it is</th><th>Good to know</th></tr></thead>
<tbody>
<tr><td>Private sector</td><td>Banks, telecoms, schools, shops, factories, hotels, farms, software firms</td><td>Largest number of jobs; many small employers never advertise widely</td></tr>
<tr><td>Government and public service</td><td>Ministries, districts, hospitals, schools, agencies</td><td>Posts are usually advertised formally with set requirements and deadlines</td></tr>
<tr><td>NGOs and development organisations</td><td>Local and international organisations running projects</td><td>Often project-based contracts; reports and communication skills count</td></tr>
<tr><td>Self-employment</td><td>Your own shop, salon, farm, workshop, online service</td><td>Needs savings, discipline and customers; a CV may still help for contracts</td></tr>
<tr><td>Internships</td><td>Short placements, paid or unpaid, to learn on the job</td><td>A common first step; ask what you will learn and whether it is paid</td></tr>
<tr><td>Graduate trainee programmes</td><td>Structured entry programmes run by large employers for new graduates</td><td>Competitive; usually include tests and several interviews</td></tr>
<tr><td>Volunteering</td><td>Unpaid service for a community group, school or charity</td><td>Builds skills and references; it should never replace a paid job forever</td></tr>
</tbody>
</table>

<h3>Be honest about your starting point</h3>
<p>A university graduate may aim for a trainee programme and an internship at the same time. A diploma holder may find strong openings in technical and supervisory roles. A school leaver may start in a shop, workshop or farm, add a short course and move up. None of these is shameful; each is a step. The goal is to pick two or three routes and work on them together.</p>

<h3>Worked example: three people, three plans</h3>
<ul>
<li><strong>Brenda, graduate in business studies (Kampala):</strong> applies to two trainee programmes, one bank internship and volunteers for a youth NGO on weekends to build project experience.</li>
<li><strong>Samuel, diploma in electrical installation (Mbarara):</strong> contacts four electrical contractors by visit, applies to a district water office post, and offers wiring services for small shops.</li>
<li><strong>Aisha, S6 leaver who helps in her mother's clothing stall (Mombasa):</strong> asks shops for sales assistant roles, takes a short customer-service course and keeps a record of sales she handled.</li>
</ul>

<h3>Case scenario</h3>
<p>Peter, a graduate in Jinja, had sent 60 applications to big companies in Kampala and heard nothing. A mentor asked him to widen the search. Peter decided to apply also to schools, a local sugar-related supplier and two NGOs working in his district, and to ask a cousin about internship openings. Within five weeks he had two interview invitations, one from an NGO that never advertised online. The lesson: spread your effort across several routes, and look close to home as well as in the capital.</p>

<blockquote><p><strong>Quick check:</strong> Which is usually more competitive and structured: a graduate trainee programme or a small shop vacancy?</p><p>Answer: The trainee programme, because large employers run tests and several interview rounds for many applicants.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why should an internship be questioned before you accept?</p><p>Answer: You should ask what you will learn and whether it is paid, so the placement truly builds skills and is not unpaid labour.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Copy the table headings (Route, Fits me?, First step) and fill them for all seven routes.</li>
<li>Circle the two or three routes that fit you best this year.</li>
<li>For each circled route write one concrete first step, such as "visit three workshops on Saturday".</li>
<li>Set a date within seven days for the first step.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>There are many routes: private, government, NGO, self-employment, internships, trainee programmes and volunteering.</li>
<li>Choose two or three routes that fit your real situation and work on them together.</li>
<li>Small and local employers often hire without wide advertising.</li>
<li>Every honest first step is valid and can lead upward.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Finding genuine vacancies and spotting scams',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to search for jobs in the right places and recognise a fake offer before you lose money or personal information. This matters because job seekers are a favourite target of fraudsters who know that people are hopeful and sometimes desperate.</p>

<h3>Where genuine jobs are advertised</h3>
<ul>
<li><strong>Job boards:</strong> websites that list vacancies from many employers. Check that the employer name is real.</li>
<li><strong>Company and organisation websites:</strong> look for a Careers or Vacancies page. This is the safest source.</li>
<li><strong>LinkedIn:</strong> vacancies, company pages and people who work there.</li>
<li><strong>Newspapers and notice boards:</strong> government and many institutions still print adverts; schools and universities pin them on notice boards.</li>
<li><strong>Networks:</strong> teachers, former classmates, church and community members, alumni groups, and people you did an internship with. A large share of jobs are filled through a trusted introduction.</li>
</ul>

<h3>Warning signs of a scam</h3>
<table>
<thead><tr><th>Warning sign</th><th>What it usually means</th></tr></thead>
<tbody>
<tr><td>You must pay an application, "registration", medical, uniform or visa fee</td><td>Real employers do not charge you to be considered; this is the biggest sign of fraud</td></tr>
<tr><td>Offer arrives without any interview, for a job you never applied for</td><td>The "offer" is bait to get money or documents</td></tr>
<tr><td>Contact only through a free email address pretending to be a big company, or only through WhatsApp</td><td>A real company normally uses its own email domain</td></tr>
<tr><td>Very high pay for very easy work, or "work abroad guaranteed"</td><td>Too good to be true; check with a trusted official source</td></tr>
<tr><td>Pressure to decide today and send money by mobile money</td><td>Pressure is used to stop you thinking</td></tr>
<tr><td>Asks for your PIN, password or bank details before you have a signed contract</td><td>Never share these; no recruiter needs your PIN</td></tr>
</tbody>
</table>

<h3>How to verify an employer</h3>
<ol>
<li>Search the organisation name with the word "scam" or "fake" and read what appears.</li>
<li>Find its official website yourself, not through a link in the message, and compare the contact details.</li>
<li>Call the office number from the official site and ask if the vacancy exists.</li>
<li>Ask a teacher, relative or career office for a second opinion before paying or travelling.</li>
</ol>

<h3>Worked example: a fake offer</h3>
<p>Message received: "Congratulations! You are selected as Customer Care Officer, salary UGX 1,800,000. Pay UGX 150,000 for registration and uniform to confirm your slot today." Red flags: no interview, a payment demand, urgency, and a free email address. A genuine employer would invite you to an interview and would never charge registration.</p>

<h3>Case scenario</h3>
<p>Mercy, a graduate in Nairobi, received an email saying a well-known company had chosen her from her CV and she must send KES 5,000 for "background checks". She was tempted because she had been unemployed for a year. She decided first to call the company's published number; the receptionist said they never charge applicants and had not sent the email. Mercy reported the message to the police cyber unit and warned her class group. She lost nothing. The lesson: pause, verify with an official channel, and never pay to be hired.</p>

<blockquote><p><strong>Quick check:</strong> A recruiter asks you to pay a "processing fee" before the interview. What should you do?</p><p>Answer: Refuse and verify the employer independently, because genuine employers do not charge job seekers.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why is the company's own website the safest source of a vacancy?</p><p>Answer: Only the organisation controls that page, so the advert and application route are less likely to be faked.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Find the Careers or Vacancies page of two organisations in your area and note one advert from each.</li>
<li>Write down three people who could introduce you to an employer.</li>
<li>Write the six warning signs in your own words on a card to keep near your phone.</li>
<li>Tell one friend about the "never pay to get hired" rule.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use job boards, company sites, LinkedIn, notice boards and networks together.</li>
<li>Never pay to apply, and never share a PIN or password with a recruiter.</li>
<li>Verify through the official website and phone number you find yourself.</li>
<li>Urgency, secrecy and too-good pay are classic scam signals.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Reading a job advert and matching the requirements',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to break a job advert into its parts, decide whether to apply and show how your experience matches. This matters because many applications fail simply because the applicant did not read what the employer asked for.</p>

<h3>The parts of an advert</h3>
<ul>
<li><strong>Job title and location:</strong> where you would work and whether travel is needed.</li>
<li><strong>Purpose and duties:</strong> what you will do each day.</li>
<li><strong>Requirements:</strong> qualifications, experience and skills. Words like "must" or "required" are essential; "desirable" or "an added advantage" are optional.</li>
<li><strong>How to apply:</strong> email address, portal or physical address, documents to attach, and the <strong>deadline</strong>.</li>
</ul>

<h3>Sort the requirements into three groups</h3>
<table>
<thead><tr><th>Group</th><th>Meaning</th><th>What to do</th></tr></thead>
<tbody>
<tr><td>Match</td><td>You clearly have it</td><td>Give a proof point (a task, a number)</td></tr>
<tr><td>Partial</td><td>You have something close</td><td>Describe the closest experience honestly</td></tr>
<tr><td>Gap</td><td>You do not have it yet</td><td>Be honest; mention you are learning it, if true</td></tr>
</tbody>
</table>
<p>You do not need every point to apply, but you should match the essential ones. If you miss several "must" items, spend your time on a better-fitting advert.</p>

<h3>Worked example</h3>
<p>Advert: <em>Sales and Customer Service Assistant, Kampala. Required: Diploma or equivalent; 1 year customer experience; good Luganda and English; basic computer skills (Word, Excel). Desirable: mobile money experience. Apply by email with CV and cover letter by 15 November. Subject line: Sales Assistant Application.</em></p>
<p>Fatuma, a diploma holder, builds her match list:</p>
<ul>
<li>Diploma in Business Administration: <strong>match</strong>.</li>
<li>Customer experience: <strong>match</strong>, 14 months serving about 40 customers a day in her uncle's shop.</li>
<li>Languages: <strong>match</strong>, Luganda and English.</li>
<li>Excel: <strong>partial</strong>, she keeps the shop stock sheet but has no certificate.</li>
<li>Mobile money: <strong>match</strong>, handled deposits and withdrawals as an agent assistant.</li>
</ul>
<p>She notes the deadline, the exact subject line and the two documents to attach. She applies knowing exactly which points to prove.</p>

<h3>Common reading mistakes</h3>
<ul>
<li>Missing the deadline or applying to the wrong email address.</li>
<li>Ignoring the subject line instructions; some employers filter on them.</li>
<li>Sending a general letter that does not answer what the advert asks.</li>
<li>Applying for a job that clearly needs a licence or degree you do not have.</li>
</ul>

<h3>Case scenario</h3>
<p>Joseph, a graduate in Kisumu, saw an advert for an "Accounts Assistant" that required a professional accounting certificate in progress. He had a general business degree and no accounting certificate. He first wanted to apply anyway, but after sorting the requirements he saw two essential gaps. He decided to apply instead to a "Business Development Assistant" post that matched his degree and his student enterprise experience, and enrolled in an accounting course for the next year. He was shortlisted for the second post. The lesson: match honestly, apply where you fit, and plan to close gaps.</p>

<blockquote><p><strong>Quick check:</strong> What does the word "desirable" in a requirements list usually mean?</p><p>Answer: It is optional; the employer would like it but will consider people without it.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Name two details in an advert you must copy exactly when applying.</p><p>Answer: The subject line or reference number and the deadline and address, because applications that ignore them can be rejected.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a real advert that interests you.</li>
<li>Draw a three-column table: Requirement, Match/Partial/Gap, My proof.</li>
<li>Fill every row honestly with a task or number as proof.</li>
<li>Write the deadline, address, subject line and attachments at the top of the page.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Read every part of an advert, especially "must" items, the deadline and how to apply.</li>
<li>Sort requirements into match, partial and gap.</li>
<li>Prove each match with a task or number.</li>
<li>Apply where you fit and plan to close gaps.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which of these is a genuine route into work in East Africa? (choose all that apply)
* Graduate trainee programme
* Internship
- Paying a fee to a recruiter to guarantee a job
* Volunteering to build skills and references
= Trainee programmes, internships and volunteering are real routes. A fee to guarantee a job is a scam sign.

Q: Where is the safest place to find a vacancy?
- A message from an unknown number on WhatsApp
* The Careers page of the organisation's own website
- A poster with no contact name
- A forwarded message with no source
= Only the organisation controls its own site, so the advert there is far more likely to be real.

Q: A message says you won a job without any interview and must pay UGX 100,000 today to confirm. What is the best action?
- Pay quickly so you do not lose the slot
- Pay half now and half later
* Do not pay; verify through the organisation's official website and phone number
- Send your ID and PIN so they can check you
= No interview plus a payment demand and urgency are classic scam signs. Verify independently and never pay to be hired.

Q: In an advert, "must have" and "an added advantage" differ because:
- Both are compulsory
* "Must have" is essential and "an added advantage" is optional
- "An added advantage" is more important
- Neither matters to the employer
= Essential items are required. Added-advantage items help but are not compulsory.

Q: Fatuma matches four of five requirements and has a partial match for Excel. What is the best approach?
- Do not apply until she has a certificate
* Apply and honestly describe her closest experience, such as keeping the shop stock sheet
- Claim she is an Excel expert
- Leave Excel out and hope nobody asks
= Honest partial matches are fine. Exaggerating can be exposed at an interview or test.

Q: Brenda applies only to five famous Kampala companies and gets no reply. Based on this module, what should she do?
- Apply to the same five again every day
- Stop applying
* Widen her routes: NGOs, schools, internships, local employers and her network
- Pay an agent to find a job
= Spreading effort over several honest routes, including local and smaller employers, improves the chance of an interview.

Q: Which detail should you copy exactly from an advert?
- The salary only
- The company logo
* The subject line or reference, the deadline and the email address
- The first sentence
= Employers often sort and filter applications by subject line and reject late or wrongly addressed ones.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Building a strong CV',
            'summary' => 'Choose a format, write achievements with numbers, handle no-experience situations, and format the CV neatly for people and systems.',
            'lessons' => [
                [
                    'title'   => 'CV structure and choosing the right format',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to lay out a CV with the right sections and choose between a chronological and a skills-based format. This matters because a recruiter often spends less than a minute on a first look, so the layout must let them find the key facts quickly.</p>

<h3>The standard sections</h3>
<ol>
<li><strong>Name and contact details:</strong> phone, professional email, town, and a LinkedIn link if you have one.</li>
<li><strong>Profile (summary):</strong> two or three lines on who you are, what you offer and what you are seeking.</li>
<li><strong>Work experience or projects:</strong> the main section for most people.</li>
<li><strong>Education and training:</strong> qualifications, school or college and years.</li>
<li><strong>Skills:</strong> relevant skills, languages and software.</li>
<li><strong>References:</strong> names or "available on request" (see Module 3).</li>
</ol>
<p>Put the most relevant section first. A new graduate can put Education first; someone with solid work history puts Experience first.</p>

<h3>Chronological or skills-based?</h3>
<table>
<thead><tr><th>Format</th><th>How it works</th><th>Best for</th></tr></thead>
<tbody>
<tr><td>Chronological</td><td>Lists jobs from the most recent backward, with dates</td><td>People with steady, relevant work history</td></tr>
<tr><td>Skills-based (functional)</td><td>Groups achievements under skill headings such as Customer Service or Leadership</td><td>School leavers, career changers, people with gaps or informal work</td></tr>
<tr><td>Combined</td><td>A short skills summary, then a brief timeline</td><td>Many beginners; clear and honest</td></tr>
</tbody>
</table>

<h3>Worked example: profile statements</h3>
<p><strong>Weak:</strong> <em>"I am a hardworking and honest person looking for any job where I can grow."</em></p>
<p><strong>Strong (fresh graduate):</strong> <em>"Bachelor of Business Administration graduate (Makerere University, 2025) with experience managing a student enterprise of 12 members and UGX 2.4 million in sales. Skilled in Excel, customer service and report writing. Seeking a graduate trainee role in marketing or sales."</em></p>
<p><strong>Strong (diploma holder):</strong> <em>"Diploma in Electrical Installation holder with 8 months of supervised wiring on residential and shop sites in Mbarara. Safety-conscious, reads building plans and completes jobs on schedule. Seeking an assistant electrician post."</em></p>

<h3>Worked example: a chronological experience section</h3>
<pre><code>WORK EXPERIENCE
Sales Assistant (Intern), Mukwano Hardware, Kampala        Jun 2025 - Dec 2025
- Served an average of 35 customers a day and recorded sales in the daily book
- Reduced stock-count errors by arranging shelves by item code
- Trained 2 new interns on the till and receipts</code></pre>

<h3>Case scenario</h3>
<p>Doreen, a graduate in Entebbe, used a long, plain CV that started with a page about her primary school. Employers rarely reached the useful part. A mentor advised her to cut it to the six standard sections and place Education and one internship near the top. She decided to remove school details before secondary level and move her skills higher. After the redesign she got a reply from two of her next five applications. The lesson: put the most relevant facts first and remove what does not help.</p>

<blockquote><p><strong>Quick check:</strong> Which format suits a school leaver with informal work only?</p><p>Answer: A skills-based or combined format, because it highlights what they can do rather than a short job list.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What belongs in a CV profile?</p><p>Answer: Two or three lines on who you are, what you offer and the role you want, so the reader sees your fit at once.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write the six section headings on a blank page in a sensible order for you.</li>
<li>Choose chronological, skills-based or combined, and write one line explaining why.</li>
<li>Write a three-line profile with your qualification, one proof point and the role you want.</li>
<li>Read it aloud to check it sounds like you.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use clear standard sections with the most relevant first.</li>
<li>Chronological suits steady history; skills-based or combined suits beginners.</li>
<li>A good profile is short, specific and states the target role.</li>
<li>Remove anything that does not help this employer decide.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Achievements, numbers and CVs with little or no experience',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to turn ordinary duties into achievements with numbers and write a convincing CV when you have little paid experience. This matters because employers hire people who can show results, and every person, even a school leaver, has some results to show.</p>

<h3>Duties versus achievements</h3>
<p>A <strong>duty</strong> says what you were supposed to do. An <strong>achievement</strong> says what happened because of you. Use a simple pattern: <strong>action word + what you did + result or number</strong>.</p>
<table>
<thead><tr><th>Weak (duty)</th><th>Strong (achievement)</th></tr></thead>
<tbody>
<tr><td>Responsible for sales</td><td>Sold an average of UGX 400,000 of goods a week and kept a written record of every sale</td></tr>
<tr><td>Helped customers</td><td>Served about 40 customers daily and handled complaints calmly, keeping regular customers</td></tr>
<tr><td>Did typing and filing</td><td>Typed and filed over 200 documents a month with no lost records</td></tr>
<tr><td>Was class representative</td><td>Represented 60 students and organised a meeting with the dean that restored library hours</td></tr>
</tbody>
</table>
<p>Useful action words: organised, handled, reduced, increased, trained, designed, repaired, recorded, led, saved, delivered.</p>
<p>Never invent numbers. If you do not know exactly, estimate honestly with words like "about" or "an average of", and be ready to explain how you counted.</p>

<h3>Where to find experience when you have no job</h3>
<ul>
<li>Helping in a family shop, farm or workshop</li>
<li>School or campus leadership, clubs, sports and events</li>
<li>Volunteering and church or community projects</li>
<li>Class or final-year projects</li>
<li>Short courses and self-taught skills</li>
<li>Small side work such as tutoring, phone repair or social media posting</li>
</ul>

<h3>Worked example: a school leaver with informal work</h3>
<p>Brian, S6 leaver in Gulu, has never held a formal job. His CV (skills-based):</p>
<pre><code>PROFILE
S6 leaver with two years of hands-on experience in motorcycle repair and customer
handling. Honest, punctual and quick to learn. Seeking an apprentice mechanic or
sales assistant post.

KEY SKILLS AND ACHIEVEMENTS
Mechanical work
- Repaired and serviced about 10 boda boda motorcycles a week at my uncle's garage
- Diagnosed common faults (brakes, chain, plugs) and reduced repeat visits by checking each job twice
Customer handling
- Greeted customers, gave honest cost estimates and kept a daily job book
Learning
- Completed a 3-week basic mobile money and customer care course (2025)</code></pre>

<h3>Worked example: a fresh graduate</h3>
<p>Weak: <em>"Member of the debating club."</em> Strong: <em>"Chaired the university debating club (45 members), organised 3 inter-university contests and raised UGX 900,000 in sponsorship."</em></p>

<h3>Case scenario</h3>
<p>Agnes, a diploma holder in Mbale, listed "cashier" at a pharmacy with no details and, wanting to look stronger, added "managed a team of 20 staff". In the interview the panel asked about the team; she had only worked with two colleagues and became confused. She decided to correct the CV with true facts: "Served about 60 customers a day, balanced the till daily with no shortages for six months". Her next interview went well. The lesson: truthful numbers are safer and stronger than inflated claims.</p>

<blockquote><p><strong>Quick check:</strong> What is the difference between a duty and an achievement?</p><p>Answer: A duty is what you were supposed to do; an achievement is the result you produced, ideally with a number.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Is it acceptable to estimate a number when you are unsure?</p><p>Answer: Yes, if you say "about" or "an average of" and can explain it, because honesty matters more than exact precision.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List five things you have done (work, school, family, community, projects).</li>
<li>Rewrite each as: action word + what you did + a number or result.</li>
<li>Remove any claim you could not explain in an interview.</li>
<li>Choose the three strongest lines for your CV.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Show results, not only duties.</li>
<li>Use numbers where you honestly can, and estimate honestly if you must.</li>
<li>Informal work, volunteering and projects count as experience.</li>
<li>Never exaggerate; interviews test your claims.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Formatting, length, photos, personal details and ATS keywords',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to format a clean CV in Word or Google Docs, decide on length and personal details, and use keywords that help automated systems and human readers. This matters because a good CV can still be rejected if it is messy, too long or cannot be read by the software some employers use.</p>

<h3>Formatting in Word or Google Docs</h3>
<ol>
<li>Use a plain, readable font such as Calibri, Arial or Times New Roman at size 10 to 12.</li>
<li>Set margins to about 2 centimetres (in Word: <strong>Layout</strong> tab, then <strong>Margins</strong>).</li>
<li>Make section headings <strong>bold</strong> and slightly larger; keep one style throughout.</li>
<li>Use bullet points for achievements, not long paragraphs.</li>
<li>Align dates on the right using a tab or a simple table, and keep spacing even.</li>
<li>Save as <strong>PDF</strong> when the employer allows it, so the layout does not change. Name the file clearly, for example <code>Grace-Nakato-CV.pdf</code>.</li>
</ol>
<p>Avoid colourful backgrounds, many fonts, text boxes and pictures for decoration. They look busy and may confuse automated readers.</p>

<h3>One page or two?</h3>
<table>
<thead><tr><th>Situation</th><th>Suggested length</th></tr></thead>
<tbody>
<tr><td>Student, school leaver, new graduate</td><td>One page</td></tr>
<tr><td>Three to ten years of relevant work</td><td>Two pages</td></tr>
<tr><td>Long careers, academic posts</td><td>Two pages or more, only if every line helps</td></tr>
</tbody>
</table>

<h3>Photos and personal details</h3>
<p>Norms differ by country and employer, so follow the advert. Many employers in East Africa still expect a photo, while others prefer none to reduce bias. If you include a photo, use a recent, neat, passport-style picture. Do not include your ID number, tribe, religion, parents' details or bank details. Date of birth, marital status and nationality are sometimes expected on forms but are not needed on most CVs. Your phone number, professional email and town are enough.</p>

<h3>ATS keyword basics</h3>
<p>An <strong>ATS</strong> (applicant tracking system) is software some larger employers use to store and sort CVs, often by searching for keywords. To help:</p>
<ul>
<li>Use words from the advert that are true for you, such as "customer service", "bookkeeping" or "Microsoft Excel".</li>
<li>Use standard headings: Experience, Education, Skills.</li>
<li>Write abbreviations in full once, for example "Certified Public Accountant (CPA)".</li>
<li>Do not hide keywords or paste huge lists; a human must still read it.</li>
</ul>

<h3>Worked example: before and after</h3>
<p><strong>Before:</strong> a two-column CV with a photo as a background, six fonts and the file named "cv final final2.docx". <strong>After:</strong> a one-page, single-column PDF named "Moses-Okello-CV.pdf" with clear headings and keywords from the advert such as "stock control" and "customer service".</p>

<h3>Case scenario</h3>
<p>Moses, a graduate in Lira, sent a CV designed with text boxes and icons. A company using a tracking system could not read his experience, and his application showed up with blank fields. A friend in HR explained the problem. Moses rebuilt the CV in a simple single-column layout with standard headings and sent it again; this time his details appeared correctly and he was invited for an interview. The lesson: plain beats fancy, because both software and busy readers prefer clarity.</p>

<blockquote><p><strong>Quick check:</strong> Why save a CV as a PDF?</p><p>Answer: The layout stays the same on any device, unless the employer asks for a Word file.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which personal details should usually be left off a CV?</p><p>Answer: ID number, religion, tribe, parents' details and bank details, because they are unnecessary and a privacy risk.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open Word or Google Docs and set a simple font, margins of about 2 cm and clear headings.</li>
<li>Type your contact block with a professional email.</li>
<li>Check the advert you chose earlier and add three true keywords to your skills or experience.</li>
<li>Export to PDF and name it with your full name and the word CV.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use a simple single-column layout and a readable font.</li>
<li>Aim for one page as a beginner and two pages at most unless more is truly needed.</li>
<li>Keep personal details minimal and safe; follow the employer's photo norm.</li>
<li>Use true keywords from the advert and standard headings.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which section should come first on a new graduate's CV?
- References
* Education, or a short profile followed by Education
- Hobbies
- Primary school history
= A new graduate's strongest facts are usually the degree and training, so these go near the top.

Q: Which format is usually best for a school leaver with informal work?
- Chronological with long dates
* Skills-based or combined, grouping achievements under skills
- A one-line CV
- A CV with a decorative background
= A skills-based format shows what the person can do when the formal job list is short.

Q: Which is an achievement rather than a duty?
- Responsible for the shop
- Helped customers
* Served about 40 customers a day and kept a daily record of sales
- Did many tasks
= It names an action, a number and a result that can be checked.

Q: Agnes has only worked with two colleagues but wants to write "managed 20 staff". What is the best choice?
- Write it; employers rarely check
- Write "managed a big team"
* Write only the true facts, such as serving 60 customers a day and balancing the till daily
- Remove her work experience
= Interviews test claims. True, specific facts are safer and still impressive.

Q: Which are good formatting choices for a CV? (choose all that apply)
* A plain readable font and even spacing
* Clear section headings
- Six different fonts and bright backgrounds
* Saving as a PDF when allowed
= Simple, consistent formatting is easy to read and survives on any device.

Q: Moses's designed CV with text boxes appears blank in an employer's system. What should he do?
- Add more colours
* Rebuild it as a simple single-column CV with standard headings and resend it
- Stop applying to companies
- Send it as a photo
= Plain layouts with standard headings can be read by tracking systems and by people.

Q: What is an ATS?
- A type of reference letter
* Software some employers use to store and sort applications, often by keywords
- A government licence
- A CV template
= Applicant tracking systems search for keywords, so true keywords from the advert help.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Cover letters, emails and application forms',
            'summary' => 'Write tailored cover letters and application emails, complete online forms carefully, and prepare references.',
            'lessons' => [
                [
                    'title'   => 'The cover letter: weak and strong samples',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a one-page cover letter that is specific to the job and the employer. This matters because the letter is your chance to explain, in your own words, why you fit, which a CV alone cannot do.</p>

<h3>The structure</h3>
<ol>
<li><strong>Heading:</strong> your name, phone, email, the date, then the employer's name and address.</li>
<li><strong>Greeting:</strong> use a name if the advert gives one ("Dear Ms Atim"); otherwise "Dear Hiring Manager".</li>
<li><strong>Opening paragraph:</strong> the job you are applying for and one strong reason you fit.</li>
<li><strong>Middle paragraph:</strong> two or three proof points linked to the advert's requirements.</li>
<li><strong>Closing paragraph:</strong> your availability, thanks, and a polite request for an interview.</li>
<li><strong>Sign-off:</strong> "Yours sincerely" when you used a name, "Yours faithfully" when you used "Dear Hiring Manager", then your full name.</li>
</ol>
<p>Keep it to about 250 to 350 words. Do not repeat the whole CV; choose the best three facts.</p>

<h3>Weak sample</h3>
<pre><code>Dear Sir/Madam,
I am writing to apply for any job in your company. I am hardworking, honest and
a fast learner. I have attached my CV. Please consider me. I will do any work.
Yours, Allan</code></pre>
<p>Problems: no job named, only adjectives, no proof, no reason for this employer.</p>

<h3>Strong sample (fresh graduate)</h3>
<pre><code>Winnie Achieng
0700 000 000 | winnie.achieng@example.com | Kampala
12 November 2026

The Human Resource Manager
Bright Foods Uganda Ltd, Kampala

Dear Ms Namukasa,

Re: Application for Sales Trainee (Ref: ST-26)

I am a Bachelor of Business Administration graduate (Makerere University, 2025)
and I would like to join your sales trainee programme. Your focus on bringing
affordable packaged foods to local shops matches the work I enjoy most.

During my final year I ran a campus snack enterprise with 12 members. We
recorded every sale, reached UGX 2.4 million in sales over 8 months and kept
a customer list of over 150 buyers. I also completed a 3-month internship at a
supermarket where I served about 35 customers a day and helped cut stock-count
errors by arranging shelves by item code. I use Excel for sales records and
speak English, Luganda and Luo.

I am available to start immediately and would welcome an interview to discuss
how I can support your sales team. Thank you for your time.

Yours sincerely,
Winnie Achieng</code></pre>

<h3>Strong sample (school leaver, shorter)</h3>
<pre><code>Dear Hiring Manager,

I am applying for the Sales Assistant post advertised on your notice board. For
two years I have helped in my mother's clothing stall in Mombasa, serving about
30 customers a day, recording sales in a book and handling mobile money payments
accurately. I am honest, punctual and keen to learn your products quickly.
I would be glad to meet you for an interview.

Yours faithfully,
Aisha Mwinyi</code></pre>

<h3>Case scenario</h3>
<p>Ivan, a graduate in Mbarara, sent the same letter to thirty employers, once even leaving in the wrong company name. He received no replies. After learning about tailoring, he decided to write fewer letters and spend ten minutes researching each employer: one fact about its work and one requirement from the advert. He sent eight letters and got three interviews. The lesson: a letter must show that you chose this employer on purpose, and a wrong company name shows the opposite.</p>

<blockquote><p><strong>Quick check:</strong> When do you end with "Yours faithfully"?</p><p>Answer: When you began with "Dear Sir/Madam" or "Dear Hiring Manager" and do not know the person's name.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What should the middle paragraph contain?</p><p>Answer: Two or three proof points linked to the advert, because proof persuades more than adjectives.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Take the advert you analysed earlier.</li>
<li>Write the opening paragraph naming the job and one real reason you fit.</li>
<li>Write the middle paragraph with two proof points that include numbers.</li>
<li>Write a polite closing and check the company name three times.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use a clear structure: heading, greeting, opening, proof, closing, sign-off.</li>
<li>Keep it to one page, about 250 to 350 words.</li>
<li>Name the job and show why this employer and this role.</li>
<li>Proofread, especially names and dates.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Application emails, online forms and portals',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to send a professional application email and complete online forms and portals accurately. This matters because many applications are lost or rejected on small errors: a missing attachment, a vague subject line or a wrong answer on a form.</p>

<h3>The application email</h3>
<p>When the advert says "apply by email", your email itself is the first thing the employer reads. Follow this order:</p>
<ol>
<li><strong>Subject line:</strong> copy the one requested. If none, write the job title and your name.</li>
<li><strong>Greeting and short body:</strong> three or four sentences, or paste your short cover letter into the email.</li>
<li><strong>Attachments:</strong> a CV and a letter or documents listed in the advert, as PDFs with clear names.</li>
<li><strong>Signature:</strong> full name, phone number and town.</li>
</ol>

<h3>Weak and strong examples</h3>
<p><strong>Weak</strong></p>
<pre><code>Subject: Job
Body: Hello. See attached. Thanks.</code></pre>
<p><strong>Strong</strong></p>
<pre><code>Subject: Sales Assistant Application - Fatuma Nalubega

Dear Hiring Manager,

I am applying for the Sales and Customer Service Assistant post in Kampala. I
have a Diploma in Business Administration and 14 months of experience serving
about 40 customers a day, including mobile money transactions. My CV and cover
letter are attached as PDF files.

I would be glad to attend an interview at your convenience.

Kind regards,
Fatuma Nalubega
0700 000 000 | Kampala</code></pre>
<p>Before sending, attach the files, then re-open the draft to confirm they are there. Send the email to yourself first if you are unsure how it looks.</p>

<h3>Online forms and portals</h3>
<ul>
<li><strong>Read all instructions</strong> before starting; note the closing date and any word limits.</li>
<li><strong>Create the account carefully</strong> with your professional email, and save the login in a safe place. Never share the password.</li>
<li><strong>Prepare answers in a document first</strong> and paste them in. Portals sometimes time out and lose your work.</li>
<li><strong>Enter details exactly as on your certificates</strong>: names, dates, grades and institution names.</li>
<li><strong>Upload files in the requested format and size</strong> and check that each one opens afterwards.</li>
<li><strong>Review before you submit</strong> and keep a screenshot or confirmation email.</li>
</ul>
<p>Be careful with personal data. A genuine portal asks for documents and contact details, but never for payment to apply, PINs or banking passwords.</p>

<h3>Worked example: answering a form question</h3>
<p>Question: <em>"Describe a time you solved a problem (max 100 words)."</em> Weak: "I am good at solving problems." Strong: "At my uncle's shop, stock regularly ran out before delivery. I started a simple weekly count and a reorder list. Within two months we stopped running out of the five fastest-selling items, and daily sales rose by about UGX 50,000."</p>

<h3>Case scenario</h3>
<p>Hassan, a graduate in Dar es Salaam, spent two hours filling a long portal form, which then timed out before he pressed submit. He lost everything. Next time he decided to write answers in a document, paste them in and submit early, not on the deadline day. He also saved the confirmation email. The lesson: prepare offline, submit early and keep proof of submission.</p>
<p>Another candidate, Lilian, was sent a "portal link" by SMS that asked for a UGX 30,000 "processing fee" and her mobile money PIN. She recognised the scam signs, closed the page and reported the number.</p>

<blockquote><p><strong>Quick check:</strong> Why prepare form answers in a document first?</p><p>Answer: Portals can time out or crash, and a saved copy lets you paste your answers again without losing work.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What should you do after attaching files to an email?</p><p>Answer: Confirm the attachments are really there and open correctly before sending, because a missing file often means automatic rejection.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Draft an application email for your chosen advert using the strong example as a model.</li>
<li>Name your files clearly, such as <code>Your-Name-CV.pdf</code>.</li>
<li>Send the email to yourself and check the subject, attachments and spelling.</li>
<li>Write one 100-word achievement story you can reuse in forms.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Follow the subject line and attachment instructions exactly.</li>
<li>Keep emails short, polite and signed with your contact details.</li>
<li>Prepare portal answers offline, enter details exactly and keep confirmation.</li>
<li>No genuine portal asks you for payment or a PIN.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Tailoring each application, references and recommendation letters',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to adapt your CV and letter to each job quickly, choose and brief your referees, and request a recommendation letter. This matters because employers can tell when an application is copied, and a good referee can decide between two similar candidates.</p>

<h3>Tailoring in 20 minutes</h3>
<p>You do not need to rewrite everything. Keep a <strong>master CV</strong> that holds all your experience, then make a short copy for each job.</p>
<ol>
<li>Read the advert and highlight the five most important requirements.</li>
<li>Adjust your profile to name the job title and one or two of those requirements.</li>
<li>Move the most relevant experience or achievements to the top of each section.</li>
<li>Use the advert's wording for skills that are true for you.</li>
<li>Rewrite the cover letter's opening and middle paragraphs for this employer.</li>
<li>Save with a clear name, such as <code>Winnie-CV-Bright-Foods.pdf</code>.</li>
</ol>

<h3>Worked example: same candidate, two jobs</h3>
<table>
<thead><tr><th>Part</th><th>For a sales trainee job</th><th>For a data entry job</th></tr></thead>
<tbody>
<tr><td>Profile</td><td>Graduate with a track record of selling and serving customers</td><td>Graduate who is accurate and fast with Excel and records</td></tr>
<tr><td>Top achievement</td><td>Reached UGX 2.4 million in student enterprise sales</td><td>Maintained a 150-customer list and sales sheet with zero lost records</td></tr>
<tr><td>Skills first</td><td>Customer service, negotiation, Luganda</td><td>Excel, typing at 40 words a minute, attention to detail</td></tr>
</tbody>
</table>

<h3>References</h3>
<p>A <strong>referee</strong> is a person who can confirm your character and work. Good referees are a former boss or supervisor, a lecturer or teacher, a community or church leader who knows your work, or a client. Avoid relatives and close friends.</p>
<ul>
<li><strong>Always ask permission first</strong>, and tell them about the job.</li>
<li>Send them your CV and the advert so they can speak to the right points.</li>
<li>Give their full name, job title, organisation, phone and email on your CV or list.</li>
<li>Thank them and tell them the result.</li>
</ul>

<h3>Recommendation letters</h3>
<p>A recommendation letter is written by a referee. To make it easy for them, send a short request:</p>
<pre><code>Dear Mr Okello,

I hope you are well. I am applying for a trainee post at a logistics company and
would be grateful if you could write a short recommendation letter about my work
during my internship at your store (June to December 2025). I have attached my CV
and the advert. The deadline is 20 November. Please tell me if you need any other
information. Thank you very much.

Sincerely,
Brian Mugisha</code></pre>
<p>Give at least two weeks' notice and offer to remind them politely once.</p>

<h3>Case scenario</h3>
<p>Naomi, a graduate in Kigali, listed her lecturer as a referee without asking. When the employer called, the lecturer did not remember her and was surprised. Naomi lost the chance. For her next application she decided to ask first, sent her lecturer her CV and the project they had worked on, and received a strong reference. She was offered a trainee post. The lesson: always ask permission and prepare your referee.</p>

<blockquote><p><strong>Quick check:</strong> What is a master CV for?</p><p>Answer: It stores all your experience so you can quickly copy and adapt a short, tailored version for each job.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why avoid using a relative as a referee?</p><p>Answer: Employers want an independent view of your work, and a relative is not seen as neutral.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a master CV with all your experience and achievements.</li>
<li>Make a tailored copy for one real advert using the six steps.</li>
<li>Choose two referees, ask permission and note their contact details.</li>
<li>Write the recommendation request email to one of them.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Keep a master CV and tailor a copy for each application.</li>
<li>Reorder and re-word, but only with true information.</li>
<li>Always ask permission and brief your referees.</li>
<li>Give referees plenty of notice and say thank you.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What should the opening paragraph of a cover letter do?
- Tell your life story
* Name the job you are applying for and give one strong reason you fit
- Ask for a high salary
- Apologise for having little experience
= The opening should show at once which job you want and why you fit.

Q: You wrote "Dear Hiring Manager". How should you end the letter?
- Yours sincerely
* Yours faithfully, followed by your full name
- Love from
- Bye
= When the person's name is unknown, "Yours faithfully" is the traditional sign-off, followed by your full name.

Q: Which email subject is best when the advert gives no instruction?
- Job
- Please help me
* Sales Assistant Application - Fatuma Nalubega
- Hello
= A clear subject names the job and you, so the employer can sort the message quickly.

Q: Hassan's long portal form timed out and he lost all his answers. What is the best habit?
- Complete forms only on the closing day
* Write answers in a document first, paste them in, and submit early
- Use someone else's account
- Skip long questions
= A saved copy protects your work, and early submission avoids last-minute failures.

Q: A text message links to a "job portal" that asks for a UGX 30,000 fee and your mobile money PIN. What is the right response?
- Pay the fee and send the PIN
- Pay the fee but not the PIN
* Close the page, never share the PIN, and report the number
- Forward it to all friends so they can apply
= No genuine employer asks for a payment or PIN to apply. This is a scam.

Q: Naomi named her lecturer as a referee without asking, and the lecturer did not recognise her. What should she do in the future?
- Name more lecturers without asking
* Ask permission, share her CV and the advert, and remind the referee of the work done together
- Use a friend instead
- Skip references
= Referees need to agree and be prepared so they can speak confidently about you.

Q: Which are good referees? (choose all that apply)
* A former supervisor
* A lecturer who taught and graded your project
- Your sibling
* A community leader who knows your volunteer work
= Independent people who have seen your work are credible. Relatives are not seen as neutral.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Proof, online presence and assessments',
            'summary' => 'Gather certificates and proof of work, present yourself professionally online, and prepare for tests and assessments.',
            'lessons' => [
                [
                    'title'   => 'Certificates, portfolios and proof of work',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to organise your documents and build a small portfolio that proves your skills. This matters because employers believe evidence more than claims, and many jobs ask for certified copies at short notice.</p>

<h3>Your document file</h3>
<p>Keep both a paper folder and a digital folder. Include:</p>
<ul>
<li>National ID or passport (for you only; send copies only when asked)</li>
<li>Academic certificates, transcripts and result slips</li>
<li>Training and short-course certificates</li>
<li>Reference or recommendation letters</li>
<li>Your CV and a few cover letter drafts</li>
<li>Proof of work such as photos, reports, links or work samples</li>
</ul>
<p>Scan documents at a clear resolution and name them clearly: <code>Degree-Certificate.pdf</code>, <code>Transcript.pdf</code>. Back up the folder on a second device or cloud storage with a strong password. Only send certified copies when requested; send photocopies or scans first if the advert allows it, and never give away original certificates.</p>

<h3>What is a portfolio?</h3>
<p>A <strong>portfolio</strong> is a small collection of your best work. It is not only for designers. It can be for anyone:</p>
<table>
<thead><tr><th>Person</th><th>Portfolio might include</th></tr></thead>
<tbody>
<tr><td>Accounting graduate</td><td>A sample budget sheet, a bookkeeping template, a project report</td></tr>
<tr><td>Teacher</td><td>A lesson plan, a marked sample (names hidden), a learner progress chart</td></tr>
<tr><td>Electrician</td><td>Photos of finished installations with short notes on what was done</td></tr>
<tr><td>Tailor or salon worker</td><td>Photos of finished work with permission from clients</td></tr>
<tr><td>Writer or social media assistant</td><td>Three posts or articles and their results</td></tr>
</tbody>
</table>

<h3>Worked example: a three-item portfolio</h3>
<p>Samuel, the electrician, makes one page with three projects. Each has a photo, the place and date, what he did, and the result:</p>
<pre><code>Project 1: Shop wiring, Mbarara (March 2026)
Task: Full wiring of a 4-room shop, with safe earthing
Result: Passed inspection by the owner's engineer; no faults after 4 months
(Photo attached)</code></pre>
<p>He saves it as a single PDF and adds a link when he applies. He asks clients for permission before using photos and hides private details.</p>

<h3>Rules for proof</h3>
<ul>
<li>Show only work you really did; if it was a team project, say your part.</li>
<li>Get permission before sharing client or school information.</li>
<li>Keep it short: three to six strong items beat twenty weak ones.</li>
<li>Never forge or alter a certificate. This ends careers and can be a crime.</li>
</ul>

<h3>Case scenario</h3>
<p>Esther, a diploma holder in Arusha, was asked to produce certified copies at two days' notice but her certificates were in a village home and she had no scans. She rushed, nearly missing the deadline. Afterwards she decided to scan everything, store it in a folder on her phone and email, and keep one certified set ready. When the next call came she replied within an hour and kept her place.</p>
<p>In another case, a man offered to "improve" a candidate's grades on a transcript for UGX 200,000. She refused; forged results would have been discovered and would have ended her application.</p>

<blockquote><p><strong>Quick check:</strong> Name two kinds of proof that a non-designer could put in a portfolio.</p><p>Answer: For example photos of finished work, a sample report or a lesson plan, because they show real results.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Should you ever alter a certificate to look better?</p><p>Answer: No, because forgery is dishonest, usually discovered, and can end your career or lead to prosecution.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Gather your certificates and scan them with your phone into clear PDFs.</li>
<li>Create a folder with clear file names and back it up securely.</li>
<li>Choose three pieces of proof of your work.</li>
<li>Write two lines for each: what you did and the result.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Keep your documents organised on paper and digitally.</li>
<li>A portfolio is a small set of your best, true work.</li>
<li>Ask permission before showing others' information.</li>
<li>Never forge or alter documents.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'A professional email address and online presence',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to set up a professional email address and check that what employers find online supports your application. This matters because many recruiters search your name before they invite you, and a silly email address or an angry public post can cost an interview.</p>

<h3>Your professional email address</h3>
<p>Use an address built from your real name, for example <code>grace.nakato@gmail.com</code>. Avoid nicknames and jokes such as <code>sweetboy2000@</code> or <code>queenofparty@</code>. If your name is taken, add a number or middle initial in a neat way, such as <code>grace.n.nakato</code>. Use this address only for job matters, keep the password strong and different from other accounts, and turn on two-step verification. Check your inbox and spam folder daily. Also set up a clear voicemail or greeting on your phone.</p>

<h3>A simple online audit</h3>
<ol>
<li>Search your full name in quotation marks and look at the first two pages of results.</li>
<li>Check each social media account, as a stranger would see it.</li>
<li>Delete or hide posts that show anger, insults, drunkenness, nudity, tribal or religious hatred or complaints about employers.</li>
<li>Check privacy settings and limit who can see personal photos and your phone number.</li>
<li>Update profile photos: a clear, smiling, neat photo is enough for a professional page.</li>
</ol>

<h3>LinkedIn basics for job seekers</h3>
<ul>
<li>Use a clear photo, a headline with your target role, for example "Business graduate seeking sales trainee role".</li>
<li>Copy the facts from your CV so the two match.</li>
<li>Add skills and ask former supervisors for short recommendations.</li>
<li>Do not claim skills or jobs you did not hold.</li>
</ul>

<h3>Worked example: before and after</h3>
<table>
<thead><tr><th>Before</th><th>After</th></tr></thead>
<tbody>
<tr><td>Email: <code>kingdavo_bad@</code></td><td>Email: <code>david.ssemakula@</code></td></tr>
<tr><td>Public Facebook post insulting a former boss</td><td>Post deleted; privacy settings tightened</td></tr>
<tr><td>LinkedIn headline: "Looking"</td><td>"Accounting graduate with bookkeeping and Excel skills, seeking an assistant accountant role"</td></tr>
<tr><td>Profile picture: nightclub selfie</td><td>Clear, neat portrait against a plain wall</td></tr>
</tbody>
</table>

<h3>Case scenario</h3>
<p>David reached the final round for a bank job. The manager searched his name and found a public post mocking a former employer. David was not chosen and was told his judgement was a concern. He decided to clean his accounts, delete old posts, tighten privacy settings and publish two professional posts about what he was learning. Months later he was offered an accounts post at another firm.</p>
<p>Scam case: a "recruiter" on social media offered Esther a remote data-entry job if she shared a one-time code sent to her phone. That code would have let the fraudster take over her account. She refused and reported the profile. Never share a code sent to your phone.</p>

<blockquote><p><strong>Quick check:</strong> Which is better, <code>sweetgirl_22@</code> or <code>rose.akello@</code>?</p><p>Answer: <code>rose.akello@</code>, because an address using your real name looks professional and is easy to remember.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A recruiter asks for a code you received by SMS. What should you do?</p><p>Answer: Refuse, because that code can give access to your accounts and no genuine recruiter needs it.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create or rename a professional email address based on your name.</li>
<li>Search your name and write down what appears.</li>
<li>Remove or hide two posts or photos that could harm an application.</li>
<li>Write a one-line LinkedIn headline with your target role.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use an email address built from your real name.</li>
<li>Search yourself and clean up your online footprint.</li>
<li>Keep your online profiles consistent with your CV and honest.</li>
<li>Never share codes, PINs or passwords with anyone claiming to recruit you.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Assessments and aptitude tests: what to expect',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe the common types of tests employers use and prepare for them calmly. This matters because many trainee programmes and large employers use tests to shortlist hundreds of applicants, and prepared candidates perform much better than unprepared ones.</p>

<h3>Common types of tests</h3>
<table>
<thead><tr><th>Test</th><th>What it checks</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Numerical reasoning</td><td>Reading tables, percentages and simple calculations</td><td>A shop sells 120 items at UGX 2,500; discount of 10%. What is the total?</td></tr>
<tr><td>Verbal reasoning</td><td>Understanding written passages and drawing correct conclusions</td><td>Read a paragraph and decide if a statement is true, false or cannot say</td></tr>
<tr><td>Logical or abstract reasoning</td><td>Spotting patterns in shapes or sequences</td><td>Find the next number in 3, 6, 12, 24, ...</td></tr>
<tr><td>Situational judgement</td><td>Choosing sensible actions in work situations</td><td>A colleague takes credit for your work. What do you do?</td></tr>
<tr><td>Skills tests</td><td>Practical ability</td><td>Typing speed, Excel task, writing a short email or report</td></tr>
<tr><td>Written case or group exercise</td><td>Teamwork and communication</td><td>Group discussion on a problem; each person is observed</td></tr>
</tbody>
</table>

<h3>How to prepare</h3>
<ol>
<li>Ask the employer what the assessment includes, if the advert does not say.</li>
<li>Practise basic arithmetic, percentages and reading tables without a calculator unless allowed.</li>
<li>Do timed practice with past or sample papers from the library, school or free practice sites.</li>
<li>Practise typing and common Excel tasks such as sum, average and sorting.</li>
<li>Read the news and one business article daily to improve verbal skills.</li>
<li>On the day: arrive early, bring ID and pens, read instructions and manage time.</li>
</ol>

<h3>Worked example: numerical question</h3>
<pre><code>A shop sold 80 phone chargers at UGX 12,000 each. It then gave a 15% discount
on a bulk order of 20 chargers. How much did the bulk order cost?
Step 1: normal price of 20 chargers = 20 x 12,000 = UGX 240,000
Step 2: discount = 15% of 240,000 = 36,000
Step 3: cost = 240,000 - 36,000 = UGX 204,000</code></pre>

<h3>Group exercise tips</h3>
<ul>
<li>Listen, then speak clearly; do not interrupt.</li>
<li>Build on other people's ideas and invite quiet members to speak.</li>
<li>Keep an eye on time and help the group reach a decision.</li>
</ul>

<h3>Case scenario</h3>
<p>Robert, a graduate in Nairobi, was invited to a numerical test and had not practised percentages since school. He answered half the questions and failed. Afterwards he decided to practise twenty minutes a day for three weeks with sample papers. At his next test he finished on time and passed the stage.</p>
<p>Scam case: an "agent" told candidates he could give them the test answers in advance for UGX 100,000. Robert refused. Employers detect cheating, and buying answers is dishonest and also a common way for fraudsters to take your money.</p>

<blockquote><p><strong>Quick check:</strong> Which type of test asks you to choose sensible actions in work situations?</p><p>Answer: A situational judgement test, because it checks your decisions and values.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Name one habit that improves performance in a timed test.</p><p>Answer: Practising with timed sample papers, so you learn to manage time and recognise question styles.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Find a sample numerical or verbal reasoning test from a school, library or free site.</li>
<li>Do ten questions with a timer and mark them.</li>
<li>Write down the topics you got wrong and revise them.</li>
<li>Practise a typing test and one Excel task using SUM.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Expect numerical, verbal, logical, situational and practical tests, and group exercises.</li>
<li>Prepare with timed practice and by learning basic percentages.</li>
<li>Manage time and read instructions carefully.</li>
<li>Never pay for "leaked answers"; it is cheating and often a scam.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Why scan and organise your certificates before you apply?
- Employers do not need them
* You can respond quickly when asked, and the files are safe if originals are lost
- To post them publicly
- So you can edit the grades
= A neat document file saves time and protects you. Documents must never be altered.

Q: Which is the best item for a portfolio of an electrician?
- A list of adjectives
* A project page with a photo, what he did and the result
- A picture of someone else's work
- A long essay about electricity
= Real work with a result is the strongest proof of skill.

Q: Someone offers to "improve" your transcript grades for a fee. What should you do?
- Accept, since many people do it
- Accept only for small changes
* Refuse; forged results are dishonest and are usually discovered
- Ask for a discount
= Forgery can end your career and lead to prosecution.

Q: Which email address looks most professional?
- cool_boy_254@
* john.mwangi@
- bigboss2000@
- iamthebest@
= An address built on your real name is simple, professional and easy to recall.

Q: David lost a job chance because of a public post insulting a former boss. What is the best next step?
- Ignore it
* Delete or hide old posts, tighten privacy settings and publish professional content
- Delete his CV
- Argue with the recruiter online
= Recruiters search your name. Cleaning up and showing positive work rebuilds your image.

Q: A "recruiter" asks you to send the one-time code you just received by SMS. What is right?
- Send it quickly to get the job
* Refuse; that code can give access to your account
- Send half of the code
- Ask a friend to send it
= One-time codes are private. Sharing them lets fraudsters take over your accounts.

Q: Robert failed a numerical test. Which action will best help him next time?
- Pay an agent for the answers
- Stop applying to companies with tests
* Practise basic percentages and timed sample papers for a few weeks
- Hope the next test is easier
= Regular timed practice improves speed and accuracy. Buying answers is cheating and often a scam.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Organising your search and handling results',
            'summary' => 'Track applications, follow up politely, build your network and learn from rejection.',
            'lessons' => [
                [
                    'title'   => 'Organising your job search with a tracker',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a simple tracker and a weekly routine for your job search. This matters because applying without records leads to missed deadlines, forgotten follow-ups and confused interview dates.</p>

<h3>The tracker table</h3>
<p>Use a spreadsheet in Excel or Google Sheets, or even an exercise book. One row per application. Suggested columns:</p>
<table>
<thead><tr><th>Date</th><th>Employer</th><th>Job</th><th>Where found</th><th>Deadline</th><th>Sent?</th><th>Follow-up date</th><th>Status</th></tr></thead>
<tbody>
<tr><td>3 Nov</td><td>Bright Foods Uganda</td><td>Sales Trainee</td><td>Company website</td><td>15 Nov</td><td>Yes (4 Nov)</td><td>18 Nov</td><td>Waiting</td></tr>
<tr><td>5 Nov</td><td>Kampala Youth Network</td><td>Project Assistant</td><td>Notice board</td><td>20 Nov</td><td>Yes (6 Nov)</td><td>22 Nov</td><td>Interview 25 Nov</td></tr>
<tr><td>8 Nov</td><td>City Hardware</td><td>Sales Assistant</td><td>Referral (Mr Okello)</td><td>None</td><td>No</td><td>-</td><td>Visit on Monday</td></tr>
</tbody>
</table>
<p>Add a notes column for contact names and phone numbers, and which version of your CV you sent. Colour the status cells: green for interviews, yellow for waiting, grey for closed.</p>

<h3>A weekly routine</h3>
<ol>
<li><strong>Monday:</strong> search for new vacancies and update the tracker.</li>
<li><strong>Tuesday to Thursday:</strong> write and send two or three tailored applications.</li>
<li><strong>Friday:</strong> follow up on applications sent seven to ten days ago.</li>
<li><strong>Weekend:</strong> network, practise a test, learn one skill and rest.</li>
</ol>
<p>Treat the search like a job: set hours, such as 9 am to 1 pm on weekdays, and set a weekly target such as five quality applications, not fifty careless ones.</p>

<h3>Worked example</h3>
<p>Joan, a graduate in Kampala, set a target of five tailored applications and two networking messages a week. After four weeks her tracker showed 20 applications, four replies and two interviews. She saw that applications found through referrals brought replies faster than those from general job boards, so she spent more time on networking.</p>

<h3>What the tracker teaches you</h3>
<ul>
<li>Which sources give the best replies.</li>
<li>Which CV versions work better.</li>
<li>When to follow up, so you do not forget.</li>
<li>That you are making progress, which protects your motivation.</li>
</ul>

<h3>Case scenario</h3>
<p>Ronald, a graduate in Entebbe, got a call from an unknown number inviting him to an interview "tomorrow" for a job he could not remember applying for. Without a tracker he did not know whether he had applied. He decided to say politely that he would confirm the details by email, and after checking found he had never applied. The "interview" email later asked him to pay UGX 70,000 as a "booking fee". He did not respond and blocked the sender. From then on he logged every application in a tracker. The lesson: records help you recognise both real opportunities and fake ones.</p>

<blockquote><p><strong>Quick check:</strong> Why record the follow-up date in your tracker?</p><p>Answer: So you remember to contact the employer at the right time instead of forgetting or following up too early.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Is it better to send 50 careless applications or 5 tailored ones a week?</p><p>Answer: Five tailored ones, because tailored applications get more replies and are less likely to contain errors.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a tracker with the eight columns suggested above.</li>
<li>Enter at least three real vacancies with their deadlines.</li>
<li>Choose your weekly routine and write it in a calendar.</li>
<li>Set a target for tailored applications and networking messages.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>One row per application keeps dates, contacts and status in one place.</li>
<li>A weekly routine turns a vague search into steady progress.</li>
<li>Quality and tailoring beat quantity.</li>
<li>Review the tracker to see what works and adjust.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Following up and networking',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to follow up on an application, thank an interviewer and ask for introductions politely. This matters because employers receive many applications, and a short, courteous message can put your name in front of the right person again.</p>

<h3>When to follow up</h3>
<ul>
<li>If the advert gives a closing date, wait until about a week after it unless told otherwise.</li>
<li>If there is no date, follow up after seven to ten days.</li>
<li>After an interview, send a thank-you note within 24 hours.</li>
<li>Follow up once or twice, then move on. Do not call every day.</li>
</ul>

<h3>Weak and strong follow-up</h3>
<p><strong>Weak:</strong></p>
<pre><code>Hello, did you get my application? Why have you not replied? I need this job.</code></pre>
<p><strong>Strong:</strong></p>
<pre><code>Subject: Follow-up: Sales Trainee Application - Winnie Achieng

Dear Ms Namukasa,

I hope you are well. I applied on 4 November for the Sales Trainee post and
wanted to confirm that my application was received. I remain very interested
in the role and would be happy to provide any further information.

Thank you for your time.

Kind regards,
Winnie Achieng | 0700 000 000</code></pre>

<h3>Thank-you note after an interview</h3>
<pre><code>Dear Mr Kato,

Thank you for meeting me today about the Project Assistant role. I enjoyed
learning about your youth livelihood programme. Our discussion about
preparing monthly reports reminded me of the 12 reports I wrote during my
internship, and I would be glad to support your team in the same way.

Yours sincerely,
Daniel Opio</code></pre>

<h3>Networking, honestly</h3>
<p><strong>Networking</strong> means building honest relationships with people who may share information or introduce you. It is not begging.</p>
<ol>
<li>Tell family, friends, teachers and former colleagues clearly what kind of work you seek.</li>
<li>Attend career fairs, alumni meetings, community events and online groups.</li>
<li>Ask for advice, not a job: "Could you spare 15 minutes to tell me how you entered this field?"</li>
<li>Offer something in return: share useful information or help with a task.</li>
<li>Thank people and tell them what happens next.</li>
</ol>

<h3>Case scenario</h3>
<p>Kevin, a graduate in Jinja, applied for a company vacancy and heard nothing for three weeks. He decided to send one polite follow-up and, at the same time, messaged a former classmate who now worked there, asking for advice rather than a favour. The classmate passed his CV to the manager, who invited him for an interview. Another candidate, Mary, was contacted by a "manager" on WhatsApp who offered to push her application to the top for UGX 50,000. Mary refused: genuine insiders do not sell jobs, and paying is a scam sign.</p>

<blockquote><p><strong>Quick check:</strong> About how long after applying should you follow up if no date is given?</p><p>Answer: Around seven to ten days, which is polite and gives the employer time to review applications.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is the better networking request: "Give me a job" or "Could you give me 15 minutes of advice"?</p><p>Answer: The second, because it is respectful, easy to say yes to and builds a relationship.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a follow-up email for one of your pending applications.</li>
<li>Write a thank-you note you could send after an interview.</li>
<li>List five people to tell about your job search, with one request for each.</li>
<li>Send one short message asking someone for 15 minutes of advice.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Follow up once or twice, politely, with a clear subject line.</li>
<li>Thank interviewers within 24 hours.</li>
<li>Network by asking for advice and offering help.</li>
<li>Never pay anyone to "push" your application.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Rejection, feedback and learning from the search',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to respond to rejection calmly, ask for feedback and improve your next application. This matters because almost every successful person has been rejected many times, and the people who keep learning are usually the ones who succeed.</p>

<h3>Why rejection happens</h3>
<p>Rejection is rarely a statement about your worth. Common reasons include: many strong candidates for one post, another candidate with slightly more experience, an internal candidate already chosen, a mismatch in one requirement, an error in the application, or a weak interview answer. Some you can change and some you cannot. Focus on what you can control.</p>

<h3>Respond professionally</h3>
<pre><code>Dear Ms Namukasa,

Thank you for informing me of your decision. I appreciated the chance to
interview for the Sales Trainee post. If possible, I would be grateful for
any short feedback that could help me improve. I would also like to be
considered for future vacancies.

Kind regards,
Winnie Achieng</code></pre>
<p>Not everyone replies, but some do, and a polite reply keeps the door open.</p>

<h3>Learn from the pattern</h3>
<table>
<thead><tr><th>What you see in your tracker</th><th>Likely problem</th><th>What to improve</th></tr></thead>
<tbody>
<tr><td>Many applications, no replies</td><td>CV or letter not matching adverts</td><td>Tailor more; add keywords and numbers</td></tr>
<tr><td>Replies but no interviews</td><td>Missing "must" requirements</td><td>Apply for better-fitting roles or gain the missing skill</td></tr>
<tr><td>Interviews but no offers</td><td>Interview preparation</td><td>Practise answers; ask for feedback</td></tr>
<tr><td>Tests failed</td><td>Test skills</td><td>Do timed practice</td></tr>
</tbody>
</table>

<h3>Worked example</h3>
<p>Rose, a diploma holder in Kisumu, sent 25 applications and had one interview. Her tracker showed that all her replies came from jobs where she had used the advert's keywords. She rebuilt her CV for each job and added numbers to her achievements. Her next 10 applications brought four replies. She also joined a short interview-practice group at her college and asked a friend to give feedback on her answers.</p>

<h3>Protect your wellbeing</h3>
<ul>
<li>Keep a daily routine, sleep and exercise.</li>
<li>Talk with family, friends or a mentor; do not carry it alone.</li>
<li>Celebrate small wins: a reply, an interview, a new skill.</li>
<li>Learn something new meanwhile through volunteering or a short course.</li>
<li>If you feel hopeless for a long time, speak to a trusted person or a counsellor.</li>
</ul>

<h3>Case scenario</h3>
<p>Peter, a graduate in Mbarara, received a rejection email and felt so discouraged that he stopped applying for two months. A cousin noticed and suggested he review his tracker. Peter decided to request feedback from the employer. The reply said his interview answers were general. He rehearsed with a friend, using the story method, and later won a post at another company. In contrast, Hellen was told she had "won" a scholarship-and-job package after being rejected and asked to pay a UGX 120,000 "release fee"; remembering the lessons on scams, she refused. The lesson: rejection should lead to learning, not to desperation.</p>

<blockquote><p><strong>Quick check:</strong> What is a good response to a rejection email?</p><p>Answer: Thank them politely, ask for brief feedback and say you want to be considered for future roles.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> If you get many applications sent but no replies, what should you check first?</p><p>Answer: Whether your CV and letter match the adverts, because tailoring and keywords are the usual problem.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Review your tracker or list of applications and note any pattern.</li>
<li>Write one change you will make for your next three applications.</li>
<li>Draft a thank-you-and-feedback reply for a rejection.</li>
<li>Write a list of three small wins you have had this month.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Rejection is common and not a judgement of your worth.</li>
<li>Reply politely and ask for feedback.</li>
<li>Use your tracker to find patterns and fix them.</li>
<li>Look after your health and keep learning while you search.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What should a job-search tracker contain? (choose all that apply)
* Employer and job title
* Deadline and date sent
- Your passwords for each portal
* Follow-up date and status
= A tracker holds application facts. Passwords should be stored separately and securely, not in a shared table.

Q: Why is a weekly routine useful?
- It guarantees a job in one week
* It turns the search into steady, planned progress
- It means you never need to network
- It replaces tailoring
= A routine builds habits and helps you meet deadlines and targets.

Q: You did not receive any date in the advert. When is it polite to send a follow-up?
- The next hour
* About seven to ten days after applying
- Every day
- Never
= That time gives the employer a chance to review applications without being too long.

Q: A "manager" says he can move your application to the top for UGX 50,000. What do you do?
- Pay at once
* Refuse; genuine staff do not sell jobs, and it is a scam sign
- Pay half
- Send your ID to him
= Paying for favours is both a scam sign and dishonest.

Q: Your tracker shows many applications with no replies. What is the most likely fix?
- Apply to more jobs without changing anything
* Tailor your CV and letter more, use advert keywords and add numbers
- Stop using a CV
- Send the same message again
= No replies usually means your documents do not match the adverts well enough.

Q: After a rejection, Peter stopped applying for two months. What would have been better?
- Waiting longer
* Asking for feedback, reviewing his tracker, practising answers and applying again
- Complaining online
- Changing his name
= Learning from the result and continuing is more effective than stopping.

Q: What is a good reason to send a thank-you note after an interview?
- To demand the job
* It shows courtesy and reminds the interviewer of your fit
- To ask about other candidates
- To ask for a loan
= A short thank-you within 24 hours leaves a positive impression.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 6
        [
            'title'   => 'Module 6: Offers, contracts and your first 90 days',
            'summary' => 'Understand job offers and contracts, probation and polite salary discussion, and plan a strong start at work.',
            'lessons' => [
                [
                    'title'   => 'Job offers and contract basics',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to read a job offer carefully, recognise the main parts of an employment contract and spot a fake offer. This lesson gives general guidance only and is not legal advice; for a legal question about your own contract, ask a qualified lawyer or the labour office of your country. This matters because a signed contract shapes your pay, duties and rights, and mistakes are costly.</p>

<h3>Offer first, contract next</h3>
<p>An <strong>offer</strong> is the employer's statement that they want to hire you, usually by phone or letter. A <strong>contract</strong> is the written agreement setting out the terms. Never resign from a current job or travel to a new town until you have a written offer or contract.</p>

<h3>Parts of a typical contract</h3>
<table>
<thead><tr><th>Part</th><th>What to look for</th></tr></thead>
<tbody>
<tr><td>Parties and job title</td><td>Your name, the employer's legal name, and your role</td></tr>
<tr><td>Start date and type</td><td>Permanent, fixed-term (end date) or probation</td></tr>
<tr><td>Duties and reporting line</td><td>What you will do and who supervises you</td></tr>
<tr><td>Pay</td><td>Gross or net amount, currency, payment date and method</td></tr>
<tr><td>Hours and leave</td><td>Working hours, overtime, annual leave and sick leave</td></tr>
<tr><td>Benefits and deductions</td><td>Allowances, insurance, pension or social security contributions, tax</td></tr>
<tr><td>Probation and notice</td><td>Length of probation and the notice either side must give</td></tr>
<tr><td>Termination and conduct</td><td>How the contract can end; rules you must follow</td></tr>
</tbody>
</table>
<p>Ask questions about anything unclear, in writing and politely. Keep a signed copy.</p>

<h3>Worked example: a sample offer</h3>
<pre><code>Dear Ms Achieng,

We are pleased to offer you the position of Sales Trainee at Bright Foods Uganda Ltd,
starting 5 January 2027, based in Kampala. The contract is for 12 months with a
3-month probation. Gross monthly salary is UGX 1,200,000 paid by the 28th of each
month, plus lunch allowance. Working hours are 8 am to 5 pm, Monday to Friday.
Please sign and return one copy by 20 December 2026.</code></pre>
<p>Winnie checks: job title, start date, contract type, gross pay, hours, probation and the date to reply. She writes down two questions on leave and transport allowance before replying.</p>

<h3>Signs of a fake offer or contract</h3>
<ul>
<li>You are asked to pay for tools, visas, medicals or "training" before starting.</li>
<li>No interview happened, or the interview was only on chat.</li>
<li>The employer's name, address or phone number cannot be verified.</li>
<li>The pay is far above normal for the job.</li>
<li>You are asked to sign immediately, without time to read.</li>
</ul>

<h3>Case scenario</h3>
<p>Vincent, a graduate in Kampala, received an offer as a "hotel supervisor abroad" with a huge salary. The agent asked for UGX 2,000,000 for a visa and ticket to be sent by mobile money the same day. Vincent decided to wait and verify. The company named in the letter had no such vacancy, and the agent's phone was switched off the next day. A friend who had paid lost her savings. Vincent reported the case to the police. The lesson: a real offer can wait for checking, and fraudsters rely on speed.</p>

<blockquote><p><strong>Quick check:</strong> What should you have before resigning from your current job?</p><p>Answer: A written offer or signed contract, because verbal promises can change or be fake.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Name two parts you must check in an offer letter.</p><p>Answer: For example pay (gross or net), probation length, hours and start date, because these affect your income and security.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Read the sample offer above and list each item against the contract table.</li>
<li>Write three questions you would ask the employer in writing.</li>
<li>Write the five fake-offer signs on a small card.</li>
<li>Find your country's labour office name through its official website and note it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Get the offer and the contract in writing before you act.</li>
<li>Check pay, duties, probation, hours, leave and notice.</li>
<li>Ask questions in writing and keep a signed copy.</li>
<li>Real employers do not ask you to pay to start work.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Probation and negotiating salary politely',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what probation is and ask politely about pay without damaging the relationship. This lesson gives general guidance only and not legal advice. It matters because many young people either accept anything out of fear or demand too much without evidence; a calm, informed approach works better.</p>

<h3>What is probation?</h3>
<p><strong>Probation</strong> is an initial period, often three to six months, when the employer and the new employee check whether the job suits both. During it you may be reviewed more often, and the notice terms may differ. Ask what is expected, how you will be assessed and when the review takes place. A good employer gives clear goals.</p>

<h3>Negotiating salary: prepare first</h3>
<ol>
<li><strong>Research the usual pay</strong> for this job, level and place by asking people in the field and checking reliable sources.</li>
<li><strong>Know your value:</strong> two or three achievements that show what you bring.</li>
<li><strong>Know your minimum:</strong> the lowest amount you can honestly accept, considering rent, transport and food.</li>
<li><strong>Consider the whole package:</strong> allowances, transport, health cover, leave, training and growth.</li>
<li><strong>Choose your moment:</strong> after you receive an offer, not in the first interview unless asked.</li>
</ol>

<h3>Weak and strong approaches</h3>
<table>
<thead><tr><th>Weak</th><th>Strong</th></tr></thead>
<tbody>
<tr><td>"The pay is too small. I want more or I will not come."</td><td>"Thank you for the offer. I am excited about the role. Based on my research and my experience managing a UGX 2.4 million enterprise, I was hoping for a salary closer to UGX 1,400,000. Is there flexibility?"</td></tr>
<tr><td>Accepting at once from fear</td><td>Thanking them and asking for 2 days to consider</td></tr>
<tr><td>Comparing yourself to friends</td><td>Using facts about role and market</td></tr>
</tbody>
</table>

<h3>A sample email</h3>
<pre><code>Dear Ms Namukasa,

Thank you for offering me the Sales Trainee position. I am delighted and
would be glad to join Bright Foods. Having considered the role and my
experience in running a student enterprise, I would like to ask whether the
monthly salary could be UGX 1,400,000. If this is not possible, I would
appreciate knowing whether a transport allowance can be considered.

Thank you for your understanding.

Kind regards,
Winnie Achieng</code></pre>
<p>If the employer says no, you can still decide: accept for the experience, ask for a salary review after probation in writing, or politely decline.</p>

<h3>Case scenario</h3>
<p>Isaac, a diploma holder in Nakuru, was offered a position at a salary he felt was low. He had never negotiated and was afraid to lose the offer. After some research he decided to ask politely for a transport allowance and a review after six months. The employer agreed to both. Later a "recruiter" contacted a friend, Joy, offering a job and demanding a "salary guarantee deposit" before the first pay; Joy recognised the scam. Real employers pay you; you do not pay them. The lesson: polite, prepared requests often succeed, and a fee for pay is never normal.</p>

<blockquote><p><strong>Quick check:</strong> When is the best time to raise salary?</p><p>Answer: After you receive an offer, because you then know they want you and you can discuss pay with confidence.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why research typical pay before negotiating?</p><p>Answer: Facts about the role and market make your request reasonable and credible.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a job you want and research its typical pay range from at least two people or sources.</li>
<li>Write your minimum acceptable amount and your target amount.</li>
<li>List two achievements that justify your target.</li>
<li>Draft a polite email asking about salary or benefits.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Probation is a trial period; ask how you will be assessed.</li>
<li>Research and know your value before you negotiate.</li>
<li>Be polite, specific and ready to hear no.</li>
<li>Look at the whole package, not salary alone.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Your first 90 days at work',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to plan your first three months so that you learn quickly, build trust and pass probation with confidence. This matters because the early impression you make often lasts for years, and the habits you set now become your reputation.</p>

<h3>The first week</h3>
<ul>
<li>Arrive early, dress as others do or slightly smarter, and carry a notebook.</li>
<li>Learn names and roles: write them down with a small note to remember each person.</li>
<li>Ask for your job description, goals, working hours, and who to ask for help.</li>
<li>Learn the rules: leave, reporting, safety, dress code and communication channels.</li>
<li>Be friendly to everyone, from the guard to the director.</li>
</ul>

<h3>Days 1 to 30: learn</h3>
<p>Listen more than you talk. Ask smart questions, and write the answers so you do not ask the same thing twice. Learn how the organisation makes money or delivers its service, who its customers are and how your work fits. Finish small tasks well and on time.</p>

<h3>Days 31 to 60: contribute</h3>
<p>Take on tasks with growing independence. Report progress before you are asked. Offer one useful idea based on what you observed. Ask your supervisor for feedback: "What should I do more, or differently?"</p>

<h3>Days 61 to 90: show results</h3>
<p>Write a short summary of what you achieved, with numbers, and prepare for your probation review. Agree goals for the next quarter. Say thank you to the colleagues who helped you.</p>

<h3>Worked example: a 90-day plan</h3>
<pre><code>Winnie Achieng, Sales Trainee, Bright Foods Uganda

Days 1-30: Learn all product codes and prices; shadow 3 sales visits; meet each
          team member; learn the reporting template.
Days 31-60: Handle 10 customer accounts alone; submit weekly reports on time;
           ask supervisor for feedback twice.
Days 61-90: Add 5 new shop customers; reduce order errors to under 2 per month;
           prepare a one-page summary of results for the probation review.</code></pre>

<h3>Habits that build trust</h3>
<ul>
<li>Be punctual and meet deadlines; tell people early if you will be late.</li>
<li>Do what you promise and own your mistakes quickly.</li>
<li>Protect confidential information.</li>
<li>Avoid gossip and keep professional behaviour on social media.</li>
<li>Learn something new each week.</li>
</ul>

<h3>Case scenario</h3>
<p>Samuel started as an assistant technician in Mbarara. In week three he made a wiring error on a small job. He first thought of hiding it. Instead he told his supervisor at once, explained how it happened and showed how he fixed it. The supervisor thanked him for being honest, and at the probation review Samuel was confirmed. Another new employee, Charity, received an email from "the HR office" asking her to pay UGX 40,000 to "activate her staff ID" before collecting her salary. She asked her supervisor, who confirmed it was a fraud. The lesson: honesty and checking with real colleagues protect your job and your money.</p>

<blockquote><p><strong>Quick check:</strong> What is a good thing to do in your first month at work?</p><p>Answer: Listen, learn names and processes, and ask smart questions, because understanding the workplace comes before trying to change it.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You made a mistake at work. What is the best action?</p><p>Answer: Tell your supervisor quickly, explain it and say how you will fix it, because honesty builds trust.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a 90-day plan with three goals for each month, using the example as a model.</li>
<li>Write five questions to ask your supervisor in week one.</li>
<li>Choose one habit from the list to practise for the next 30 days.</li>
<li>Prepare a short note format for tracking your results with numbers.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use days 1-30 to learn, 31-60 to contribute and 61-90 to show results.</li>
<li>Ask for feedback and write down what you learn.</li>
<li>Be punctual, honest and careful with confidential information.</li>
<li>Check any request for payment with your supervisor; real employers do not charge staff to be paid.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What should you have before you resign from your current job?
- A phone call promising a job
* A written offer or signed contract
- A friend's guarantee
- A social media message
= A written agreement protects you. Verbal promises can change or be fake.

Q: What does a probation period usually allow?
- The employer to stop paying you
* Both sides to test whether the job suits them, with defined review points
- The employee to work without supervision
- The employer to skip the contract
= Probation is a trial period. Ask what is expected and when the review will take place.

Q: Which items should you check in an offer letter? (choose all that apply)
* Gross or net salary
* Probation length and notice terms
- The colour of the letterhead
* Working hours and leave
= These items affect your income, time and security. The colour of the letterhead does not.

Q: You are offered a "hotel supervisor abroad" job and must pay UGX 2,000,000 today for a visa. What is the best approach?
- Pay quickly to secure the job
* Wait, verify the employer and agent independently, and never pay under pressure
- Pay half
- Ask your friends to pay too
= Fraudsters rely on speed and pressure. A genuine offer can be checked.

Q: When is it best to raise salary?
- In the first minute of the interview
* After receiving a job offer, politely and with facts
- Never
- After you have resigned
= After an offer, the employer has decided they want you and you can discuss the package from a position of confidence.

Q: Isaac fears losing his offer if he asks for more. What is a good step?
- Accept with no questions
* Research typical pay, then politely ask for a modest improvement or a review after six months
- Threaten to refuse
- Post online about the offer
= A prepared, polite request often works and keeps the relationship positive.

Q: Which is the best approach to your first 30 days?
- Change how everything is done
* Listen, learn names and processes, ask good questions and finish small tasks well
- Avoid asking questions
- Stay silent and unseen
= Early on, learning and reliability build trust.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which of these is a genuine route into employment? (choose all that apply)
* Graduate trainee programme
* Internship
- Paying a recruiter for a guaranteed job
* Volunteering to build experience
= Real routes build skills and experience. A fee for a guaranteed job is a scam sign.

Q: Where is the safest place to find out whether a vacancy is real?
- A forwarded message
* The organisation's official website or its published phone number
- A stranger's social media message
- A poster without a contact name
= Official channels belong to the organisation and are harder to fake.

Q: A message says you have been hired with no interview and must pay UGX 150,000 today. What do you do?
- Pay to keep the slot
- Pay half
* Do not pay; verify through the official website and phone number and report the message
- Send your PIN as proof
= No interview, a payment demand and urgency are classic scam signs.

Q: Which words in an advert usually mean optional?
- Must have
- Required
* Desirable, or an added advantage
- Essential
= Optional items help but are not compulsory.

Q: Which details should you copy exactly from an advert?
- Only the salary
* The subject line or reference, deadline and address
- The company slogan
- Nothing
= Employers often filter by subject line and reject late or misaddressed applications.

Q: Which format is best for a school leaver with informal experience?
- Chronological only
* Skills-based or combined, grouping achievements under skills
- A one-line CV
- A designed poster
= A skills-based layout highlights what the person can do.

Q: Which is an achievement rather than a duty?
- Responsible for sales
* Sold an average of UGX 400,000 a week and recorded every sale
- Helped customers
- Worked hard
= It gives an action, a number and a result.

Q: Agnes has only worked with two colleagues but wants to write that she managed 20 staff. What should she do?
- Write it anyway
* Write only true facts, such as serving 60 customers a day and balancing the till daily
- Remove her work history
- Say she managed "many people"
= Interviews test claims; true numbers are safer and still persuasive.

Q: What is the best length for a new graduate's CV?
* One page
- Four pages
- Ten pages
- Half a page with no detail
= A beginner has limited experience, so one clear page is best.

Q: Which personal details should usually be left off a CV?
- Phone number
- Professional email
* ID number, religion, tribe and bank details
- Town
= Unnecessary sensitive details are a privacy risk and can invite bias.

Q: Moses's decorated CV with text boxes shows blank in an employer's system. What should he do?
- Add more icons
* Rebuild it as a simple single-column layout with standard headings
- Send it by photo
- Stop applying
= Plain layouts are readable by both software and people.

Q: What is an ATS?
- A type of reference
* Software some employers use to store and sort applications, often by keywords
- A government licence
- A test centre
= Applicant tracking systems search for keywords, so true advert wording helps.

Q: How should you end a letter that begins "Dear Hiring Manager"?
- Yours sincerely
* Yours faithfully
- Cheers
- Regards, from your friend
= When you do not know the person's name, "Yours faithfully" is the traditional closing.

Q: What should the middle of a cover letter contain?
- Your life story
* Two or three proof points linked to the advert
- Complaints about unemployment
- A request for a loan
= Proof linked to the job persuades more than general adjectives.

Q: Hassan's long online form timed out. What is the best habit?
- Fill it only at the last minute
* Prepare answers in a document first and submit early
- Use a friend's account
- Skip hard questions
= A saved copy protects your work, and early submission avoids failures.

Q: A "portal" asks for a UGX 30,000 fee and your mobile money PIN. What is right?
- Pay it
- Share the PIN but not pay
* Close the page, never share the PIN, and report it
- Forward it to friends
= A genuine employer never asks for payment or PINs from applicants.

Q: Which of these is the best referee? (choose all that apply)
* A former supervisor who knows your work
* A lecturer who supervised your project
- Your brother
* A community leader who saw your volunteering
= Independent people who have seen your work are credible.

Q: Naomi named a lecturer as referee without asking, and the lecturer did not remember her. What should she do next time?
- Name more people without asking
* Ask permission, share her CV and the advert, and remind the referee of the work done
- Use a family member
- Leave references out
= Referees must agree and be prepared.

Q: What is the best use of a portfolio?
- To list adjectives
* To show three to six pieces of your real work with what you did and the result
- To show other people's work as yours
- To replace a CV
= A small set of true, strong examples proves your skills.

Q: Someone offers to improve your transcript grades for UGX 200,000. What should you do?
- Accept
* Refuse; forgery is dishonest and usually discovered
- Ask for a discount
- Ask to change only one grade
= Forged documents can end a career and be prosecuted.

Q: A recruiter wants the one-time code you just received by SMS. What is right?
- Send it so they can verify you
* Refuse; the code can give access to your account
- Send half
- Ask a friend to send it
= One-time codes are private. Sharing them lets fraudsters take over your accounts.

Q: Which type of test checks how you would act in a realistic work situation?
- Numerical reasoning
* Situational judgement
- Typing test
- Abstract reasoning
= Situational judgement tests present work scenarios and ask what you would do.

Q: Robert failed a numerical test. What helps most next time?
- Paying for leaked answers
- Skipping tests
* Timed practice with basic percentages and sample papers
- Relying on luck
= Practice improves speed and accuracy; buying answers is cheating and often a scam.

Q: Which of these should a tracker include? (choose all that apply)
* Employer and job title
* Deadline and date sent
* Follow-up date and status
- All your passwords in plain view
= Passwords should never be stored in a tracker that others might see.

Q: About how long after applying should you follow up if the advert gives no date?
- After one hour
* After seven to ten days
- Every day
- After one year
= This gives the employer time to review applications without making you seem forgetful.

Q: A "manager" offers to put your CV on top for UGX 50,000. What is right?
- Pay
- Pay half
* Refuse; genuine staff do not sell jobs
- Send your ID
= Paying to be pushed forward is a scam sign.

Q: Your tracker shows many applications and no replies. What is the most likely fix?
- Send the same CV to more employers
* Tailor your CV and letter, use the advert's keywords and add numbers
- Remove your name
- Stop applying
= Matching each advert more closely usually increases replies.

Q: Before resigning from your current job for a new one, you should have:
- A verbal promise
* A written offer or signed contract
- A friend's message
- A rumour
= Written terms protect you; promises can change or be fake.

Q: Winnie wants a higher salary after receiving an offer. What is the best approach?
- Demand more or refuse the job
* Thank them, explain her value with facts, and politely ask if there is flexibility
- Accept in silence and complain later
- Compare herself with a friend
= A polite, evidence-based request keeps goodwill and often works.

Q: In your first 90 days, what is the best way to handle a mistake?
- Hide it
* Tell your supervisor quickly, explain it and say how you will fix it
- Blame a colleague
- Quit
= Honesty builds trust and mistakes handled well rarely hurt a career.
QUIZ,
];
