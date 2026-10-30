<?php
return [
    'slug'        => 'responsible-ai-data-protection',
    'title'       => 'Responsible AI, Ethics and Data Protection in Africa',
    'category'    => 'ai',
    'level'       => 'intermediate',
    'icon'        => 'shield',
    'sort'        => 440,
    'summary'     => 'Use and buy AI responsibly: spot bias and harm, protect personal data, respect creators, handle deepfakes, and write an AI-use policy and risk assessment for a real organisation in Africa.',
    'description' => '<p>AI tools are spreading fast across Africa: in schools, clinics, farms, banks and mobile money. Used well they save time and widen access. Used carelessly they treat people unfairly, leak personal data, copy the work of creators and spread false information. Organisations now need people who can ask the right questions before and after an AI tool is introduced.</p><p>This intermediate course is organisational. You will look at benefits and harms in African settings, learn how bias shows up in hiring, lending, face recognition and language tools, and understand transparency and human oversight. You will learn personal data basics and the common principles in data protection laws such as the Uganda Data Protection and Privacy Act 2019, with a general comparison to Kenya, Nigeria, South Africa and the GDPR. Then you will cover training data, copyright, deepfakes, sector examples, policy writing, risk assessment, governance frameworks and questions for AI vendors.</p><p>This course gives general education, not legal advice. For decisions with legal effect, speak to a qualified lawyer or your national data protection office.</p>',
    'outcomes'    => [
        'Describe real benefits and harms of AI in African schools, health, farming, finance and public services',
        'Identify bias in hiring, lending, face recognition and language tools, and suggest fair practice',
        'Explain transparency, explainability, accountability and meaningful human oversight',
        'Recognise personal data and apply the common principles of data protection laws, including consent, minimisation and retention',
        'Respond correctly to a personal data breach in the first hours',
        'Explain how training data, copyright and creator rights affect everyday AI use',
        'Detect and respond to deepfakes and AI-driven misinformation',
        'Write a practical AI-use policy and complete a simple AI risk assessment for a small organisation',
        'Ask AI vendors the right procurement questions and use frameworks such as UNESCO, the African Union and NIST',
    ],
    'requirements' => ['Completed Practical AI Tools: Using AI Assistants Wisely (ai-tools-literacy), or equivalent experience using an AI chat assistant', 'A computer or smartphone with internet access', 'Comfortable reading and writing in English at work level'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'UNESCO: Artificial intelligence', 'url' => 'https://www.unesco.org/en/artificial-intelligence'],
        ['title' => 'NIST AI Risk Management Framework', 'url' => 'https://www.nist.gov/itl/ai-risk-management-framework'],
        ['title' => 'Personal Data Protection Office, Uganda', 'url' => 'https://pdpo.go.ug/'],
        ['title' => 'African Union', 'url' => 'https://au.int/'],
        ['title' => 'Masakhane: African language NLP community', 'url' => 'https://www.masakhane.io/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: Benefits, harms and fairness',
            'summary' => 'See where AI helps and hurts in African settings, how bias appears, and what transparency and human oversight mean.',
            'lessons' => [
                [
                    'title'   => 'AI benefits and harms in African contexts',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to weigh the benefits and harms of an AI tool for a specific community and decide whether it deserves a careful trial. This matters because AI built elsewhere is often brought to Africa with big promises, and the people who pay for mistakes are usually the ones with the least power to complain.</p>

<h3>Where AI can help</h3>
<ul>
<li><strong>Access to services:</strong> chatbots and voice tools can answer common questions in the evening, when offices are closed.</li>
<li><strong>Speed:</strong> a clinic can sort scans faster, or a SACCO can review loan files faster.</li>
<li><strong>Reach:</strong> a farm advice tool on a basic phone can reach people far from an extension officer.</li>
<li><strong>Skills:</strong> a student with no tutor nearby can get explanations at any hour.</li>
</ul>

<h3>Where AI can harm</h3>
<ul>
<li><strong>Unfair decisions:</strong> a model trained on data from other regions can score African applicants poorly.</li>
<li><strong>Privacy loss:</strong> phone and mobile money data can be used for purposes people never agreed to.</li>
<li><strong>Exclusion:</strong> tools that work well in English and French but poorly in Luganda, Kiswahili or Hausa leave many people out.</li>
<li><strong>Wrong information:</strong> confident but false answers in health or farming can cost money or lives.</li>
<li><strong>Job and income effects:</strong> some tasks, such as basic translation or data entry, may change quickly.</li>
<li><strong>Dependence:</strong> if an imported tool is withdrawn or becomes expensive, the service stops.</li>
</ul>

<h3>Four questions to ask about any AI tool</h3>
<table>
<thead><tr><th>Question</th><th>Why it matters</th></tr></thead>
<tbody>
<tr><td>Who benefits and who carries the risk?</td><td>Benefit may go to the company while risk falls on users</td></tr>
<tr><td>Was it tested on people like ours?</td><td>Performance in another country does not guarantee performance here</td></tr>
<tr><td>What happens when it is wrong?</td><td>A wrong film recommendation and a wrong loan refusal are very different</td></tr>
<tr><td>Can a person step in and can people complain?</td><td>Without a route to challenge, errors become permanent</td></tr>
</tbody>
</table>

<h3>Mini-case: the school fees chatbot</h3>
<p>A secondary school installs a chatbot to answer parents about fees. It works in English only. Many parents write in Luganda or mix languages, and the bot misreads their messages and tells some parents that they owe more than they do. The benefit (fewer queues) is real, but the harm (wrong balances, anxious parents) falls on those least able to check. A better design adds local languages, a clear "talk to the bursar" button and a weekly check of a sample of answers.</p>

<blockquote><p><strong>Quick check:</strong> A bank pilots an AI tool that was tested only in another continent. Which question matters first?</p><p>Answer: "Was it tested on people like ours?", because a model's accuracy can drop sharply on data from different languages, incomes and habits.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why is a wrong answer from a farming advice tool treated as higher risk than a wrong music suggestion?</p><p>Answer: Because the cost of the mistake is higher, such as a lost harvest, so more testing and human checking are needed.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick one AI tool you have used or heard of (a chatbot, translation tool or lending app).</li>
<li>Write two benefits and two possible harms for people in your own community.</li>
<li>Answer the four questions in the table for that tool.</li>
<li>Decide: use freely, use with checks, or do not use for important decisions. Write one sentence explaining why.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>AI brings real benefits, but harms often fall on people with less power.</li>
<li>Always ask who benefits, who carries risk, whether it was tested locally, and what happens when it errs.</li>
<li>The higher the cost of a mistake, the more checking you need.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Bias and fairness with worked examples',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain where AI bias comes from and test a simple system for unfair results. This matters because a biased tool used on hundreds of applicants can repeat the same unfairness hundreds of times, quietly and quickly.</p>

<h3>What bias means</h3>
<p><strong>Bias</strong> in AI means results that are systematically worse for some groups of people. It usually does not come from bad intentions. It comes from the data, the design or the way the tool is used. The main sources are:</p>
<ul>
<li><strong>Unrepresentative data:</strong> some groups, regions or languages are barely present.</li>
<li><strong>Historical bias:</strong> the data records past unfair decisions, and the model learns to copy them.</li>
<li><strong>Poor proxies:</strong> the model uses a stand-in for the thing you care about, such as postcode for reliability.</li>
<li><strong>Use in the wrong setting:</strong> a tool built for one country is used in another.</li>
</ul>

<h3>Worked example 1: hiring</h3>
<p>A company trains a CV-screening tool on ten years of past hires. Most past hires were men from a few universities, so the tool learns that "men from those universities" look like good candidates. It ranks equally qualified women or graduates of other universities lower. The fix is not to trust the tool: remove features that act as proxies, test results by group, and keep a person making the final choice.</p>

<h3>Worked example 2: lending</h3>
<p>A digital lender scores applicants using phone data: top-up patterns, contacts and location. A farmer with irregular seasonal income and few contacts looks risky, even though she repays reliably. People who share a phone also look strange to the model. The system may be accurate on average and still unfair to a whole group.</p>

<h3>Worked example 3: face recognition</h3>
<p>Well-known research (the Gender Shades study, 2018) found that some commercial face analysis systems made far more mistakes on darker-skinned women than on lighter-skinned men. A wrong match by a police or access-control system can mean a wrongful stop or a locked-out worker. Face recognition used on people needs very high accuracy tests on local faces, strict limits and human review.</p>

<h3>Worked example 4: language coverage</h3>
<p>Africa has many hundreds of languages, but most AI tools are strongest in English and a few global languages. A voice assistant that misunderstands Kiswahili accents, or a translator that handles formal Kiswahili but not Sheng or village variants, serves some users much better than others. Community projects such as Masakhane work to build tools and data for African languages.</p>

<h3>A simple fairness check</h3>
<pre><code>Group      Applicants   Accepted   Rate
Women      200          40         20%
Men        200          80         40%

Rate difference: 40% - 20% = 20 points  ->  investigate</code></pre>
<p>A big gap does not prove discrimination, but it is a signal to look for the cause. Compare error rates and outcomes for each group, not only the overall average.</p>

<blockquote><p><strong>Quick check:</strong> A tool is 95 percent accurate overall. Does that prove it is fair?</p><p>Answer: No, because it may be 99 percent accurate for one group and 80 percent for another, and the overall figure hides the gap.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why can removing the "gender" column still leave a hiring tool biased?</p><p>Answer: Other columns, such as a past job title or a school, can act as proxies for gender and let the model rebuild the same pattern.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Imagine a bursary scoring tool for 100 students: 60 from town and 40 from villages.</li>
<li>Suppose it approves 30 town students and 8 village students. Work out each approval rate.</li>
<li>Write what extra information you would want before deciding whether it is unfair.</li>
<li>List two changes you would ask the supplier to make.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Bias comes from data, proxies, history and wrong settings, not usually from bad intentions.</li>
<li>Test results by group; an overall average can hide unfairness.</li>
<li>Keep a person responsible for final decisions that affect people's lives.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Transparency, accountability and human oversight',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to say what people are owed when AI affects them, and design a sensible human-oversight step. This matters because when a system refuses a loan or flags a student, someone must be able to explain it, correct it and answer for it.</p>

<h3>Three ideas, three questions</h3>
<table>
<thead><tr><th>Idea</th><th>Plain meaning</th><th>Question it answers</th></tr></thead>
<tbody>
<tr><td>Transparency</td><td>Being open that AI is used and how</td><td>Do people know AI is involved?</td></tr>
<tr><td>Explainability</td><td>Giving reasons a person can understand</td><td>Why was this decision made?</td></tr>
<tr><td>Accountability</td><td>A named person or office is responsible</td><td>Who answers if it goes wrong?</td></tr>
</tbody>
</table>

<h3>Transparency in practice</h3>
<p>Tell people when they are talking to a bot, when a decision is automated, and when content is AI-generated. Put a short, plain notice at the point of use, not only in a long document. In Uganda, as in many countries, data protection law also gives people rights about decisions made only by automated means; check the exact wording with a qualified person.</p>

<h3>Explainability in practice</h3>
<p>Some models are hard to explain even for their makers. For serious decisions, prefer tools that can give the main reasons ("income records missing; two late payments"), so a person can check them and correct wrong data. If a vendor says "the algorithm decides, we cannot say why", treat that as a warning for any high-stakes use.</p>

<h3>Accountability: a person is always responsible</h3>
<p>An AI tool cannot be blamed. The organisation using it, and a named role inside it, remains responsible. Write down an owner for each AI system, such as "Head of Admissions owns the application-screening tool".</p>

<h3>Meaningful human oversight</h3>
<p>Having a human "in the loop" only helps if that person has the time, training and power to disagree. If a clerk must approve 300 AI recommendations an hour, they will click "yes" without reading: this is called <strong>automation bias</strong>, trusting the machine because it is a machine.</p>
<ul>
<li>Give reviewers enough time and the underlying facts, not just the AI's score.</li>
<li>Allow them to override, and record the reason.</li>
<li>Check a sample of approved cases as well as refused ones.</li>
<li>Offer a simple way for people to appeal.</li>
</ul>

<h3>Mini-case: the clinic triage app</h3>
<p>A clinic uses an app that ranks patients by urgency. The nurse must accept or change the ranking. After a month the log shows the nurse never changes it. The manager discovers the nurse has 40 seconds per patient and the screen shows only a colour. The oversight exists on paper but not in practice. The fix is a short summary of why the app ranked the patient that way and enough time to check borderline cases.</p>

<blockquote><p><strong>Quick check:</strong> A staff member approves every AI recommendation within seconds. Is this good oversight?</p><p>Answer: No, it suggests automation bias; the person needs time, information and permission to disagree.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A loan is refused by an automated tool. What should the applicant be able to receive?</p><p>Answer: A plain explanation of the main reasons and a way to ask a person to review the decision, so mistakes in the data can be corrected.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose an AI system in a school, shop or office you know.</li>
<li>Write one sentence for each: how people are told, how reasons are given, who is accountable.</li>
<li>Mark any that are missing.</li>
<li>Draft one improvement for each missing item, in one sentence.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Be open about AI use, give understandable reasons and name an accountable owner.</li>
<li>Human oversight must be real: time, information and power to override.</li>
<li>Offer people a way to appeal automated decisions.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A lending app is accurate 94 percent of the time overall, but refuses far more seasonal farmers who repay well. What is the best next step?
- Accept it, because 94 percent is high
* Compare error rates for each group, find the cause, and add human review for the affected group
- Remove all human reviewers to avoid mistakes
- Tell farmers to change their phone habits
= Overall accuracy can hide unfair results for a group, so check by group and fix the cause.

Q: A CV-screening tool trained on past hires ranks women lower. Which is the most likely cause?
- The tool dislikes women
* The past hiring data reflected unfair decisions and the model copied them
- The CVs were too long
- AI tools cannot read CVs
= Models learn patterns in historical data, including unfair ones.

Q: Which is the clearest example of automation bias?
- A manager double-checks a tool's result with the original records
- A nurse overrides a tool after reading the patient's notes
* A clerk approves hundreds of recommendations a day without reading them because the system probably knows best
- A school tests a chatbot before launching it
= Automation bias is trusting the machine without checking, often because of time pressure.

Q: Why is "tested only in another continent" a concern for an AI tool used in Uganda?
- Foreign tools are always illegal
- It makes the tool slower
- It changes the tool's name
* Accuracy can fall for local languages, accents, incomes and habits that were missing from the test data
= A tool must be tested on people like those who will use it.

Q: Who is accountable when an AI system used by a school makes an unfair decision?
* The school, through a named responsible role
- The AI tool itself
- The student affected
- Nobody, because the decision was automated
= Responsibility stays with the organisation using the tool; a tool cannot be held to account.

Q: Which notice best supports transparency in a customer chatbot?
- A long terms document that nobody sees
* A short message at the start saying "You are talking to an automated assistant; type HUMAN to reach a staff member"
- No message, so customers are not confused
- A message in a language the customers do not speak
= Transparency means telling people at the moment they need to know, in plain language, with a route to a person.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Personal data and data protection law',
            'summary' => 'Learn what personal data is, the shared principles in data protection laws, and how to handle consent, retention and breaches.',
            'lessons' => [
                [
                    'title'   => 'Personal data basics',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to recognise personal data and sensitive personal data, and name the roles in a data protection law. This matters because you cannot protect information you do not realise is personal, and AI tools make it very easy to paste personal data where it should not go.</p>

<h3>What is personal data?</h3>
<p><strong>Personal data</strong> is any information that identifies a person or could identify them when combined with other information. A name is obvious, but so are a phone number, an email address, a national ID number, a photo, a voice recording, a mobile money account, a student number and a location history.</p>
<p><strong>Sensitive</strong> (or special category) personal data needs extra care. Laws differ in their exact lists, but they commonly include health, religion, political opinions, ethnic origin, sexual life, criminal records, biometric data (such as fingerprints and face templates) and children's information.</p>

<h3>Is it personal data?</h3>
<table>
<thead><tr><th>Item</th><th>Personal?</th><th>Note</th></tr></thead>
<tbody>
<tr><td>"Grace Nakato, Form 3, phone 0772 000 000"</td><td>Yes</td><td>Name plus phone</td></tr>
<tr><td>"Student 4417 failed mathematics"</td><td>Yes</td><td>The school can link 4417 to a person</td></tr>
<tr><td>"The shop sold 120 units in March"</td><td>No</td><td>No individual</td></tr>
<tr><td>A list of patients' diagnoses with no names but small village and age</td><td>Possibly</td><td>Could be re-identified</td></tr>
<tr><td>A photo of a crowd where faces are clear</td><td>Yes</td><td>Faces identify people</td></tr>
</tbody>
</table>

<h3>The roles</h3>
<ul>
<li><strong>Data subject:</strong> the person the data is about.</li>
<li><strong>Data controller</strong> (in some laws, the one who decides why and how data is used) and <strong>data processor</strong> (who handles data on the controller's behalf). Uganda's Act uses the terms data controller, data processor and data collector. If you use an AI vendor to handle your customers' data, the vendor often acts as a processor and you remain responsible.</li>
<li><strong>Regulator:</strong> the national authority. In Uganda this is the Personal Data Protection Office (PDPO), which sits within NITA-U.</li>
</ul>

<h3>Pseudonymised and anonymised</h3>
<p>Replacing names with codes is <strong>pseudonymisation</strong>; it reduces risk but data can often be linked back, so it is usually still personal data. <strong>Anonymisation</strong> means no reasonable way to identify anyone, which is harder to achieve than people expect.</p>

<h3>Mini-case: the pasted spreadsheet</h3>
<p>A school secretary pastes a class list with names, parents' phone numbers and a "special needs" column into a free AI chat to ask for help formatting it. She has shared sensitive children's data with an outside service. A safer way is to replace names with codes, remove the phone and special needs columns, or use an approved tool under a written agreement.</p>

<blockquote><p><strong>Quick check:</strong> Is "Customer 0092 bought a bicycle on credit" personal data if your shop keeps a list linking 0092 to a name?</p><p>Answer: Yes, because you can identify the person by linking the code to your list.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why does the sensitive category deserve extra care?</p><p>Answer: Misuse can cause serious harm such as discrimination, stigma or danger, and it is hard to undo.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List ten fields you might find in a school or small business customer file.</li>
<li>Mark each as personal, sensitive, or not personal.</li>
<li>Choose three you could remove before asking an AI tool for help.</li>
<li>Write the cleaned version of one example row.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Personal data is anything that identifies a person, directly or by combination.</li>
<li>Health, children's, biometric and similar data are sensitive.</li>
<li>Replacing names with codes helps but is not the same as anonymising.</li>
<li>Know who the controller, processor and regulator are.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Principles of data protection laws',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to name the shared principles behind data protection laws in Africa and Europe and apply them to an AI project. This matters because the details differ between countries, but the principles are very similar, so learning them once helps you everywhere. This is general education and not legal advice.</p>

<h3>The laws at a glance</h3>
<table>
<thead><tr><th>Place</th><th>Main law</th><th>Regulator</th></tr></thead>
<tbody>
<tr><td>Uganda</td><td>Data Protection and Privacy Act, 2019</td><td>Personal Data Protection Office (PDPO)</td></tr>
<tr><td>Kenya</td><td>Data Protection Act, 2019</td><td>Office of the Data Protection Commissioner</td></tr>
<tr><td>Nigeria</td><td>Nigeria Data Protection Act, 2023</td><td>Nigeria Data Protection Commission</td></tr>
<tr><td>South Africa</td><td>Protection of Personal Information Act (POPIA)</td><td>Information Regulator</td></tr>
<tr><td>European Union</td><td>General Data Protection Regulation (GDPR)</td><td>National authorities in each member state</td></tr>
</tbody>
</table>
<p>Many African laws were influenced by the GDPR, and the GDPR can also apply to an African business that offers goods or services to people in the EU. Laws change, so always check the current text and guidance.</p>

<h3>The shared principles</h3>
<ol>
<li><strong>Lawful, fair and transparent:</strong> have a valid reason (such as consent, a contract or a legal duty) and tell people what you do.</li>
<li><strong>Purpose limitation:</strong> collect data for a clear purpose and do not use it for something unrelated.</li>
<li><strong>Minimisation:</strong> collect only what you need.</li>
<li><strong>Accuracy:</strong> keep data correct and fix mistakes.</li>
<li><strong>Storage limitation:</strong> keep it only as long as needed.</li>
<li><strong>Security:</strong> protect it with sensible technical and organisational measures.</li>
<li><strong>Accountability:</strong> be able to show how you comply.</li>
</ol>

<h3>Rights of data subjects</h3>
<p>Laws in this family usually give people rights to be informed, to access their data, to correct it, to object, and to ask for deletion in some cases. Many also give rights about decisions made only by automated means. Wording and limits vary by country.</p>

<h3>Applying the principles to an AI project</h3>
<pre><code>Project: AI tool to predict which students may drop out

Purpose?        Support students early (clear, fair)
Minimisation?   Use attendance and marks; do NOT use family health notes
Transparency?   Tell parents and students it is used and why
Accuracy?       Check records are correct before scoring
Retention?      Delete scores at the end of the school year
Security?       Limit access to class teachers and the head
Accountability? Deputy Head owns it; records decisions</code></pre>

<h3>Registration and cross-border data</h3>
<p>In Uganda, organisations that collect or process personal data may need to register with the PDPO, and rules apply to sending personal data outside the country, which matters when an AI service stores data abroad. Other countries have their own registration and transfer rules. Ask the regulator or a lawyer what applies to you.</p>

<blockquote><p><strong>Quick check:</strong> A school collected phone numbers to send fee reminders, and now wants to use them for a business advertising. Which principle is at risk?</p><p>Answer: Purpose limitation, because the data was collected for one purpose and is being used for an unrelated one.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A shop asks customers for their religion "just in case". Which principle does it break?</p><p>Answer: Minimisation, because the shop does not need that data, and it is sensitive.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a small project that uses AI and personal data (for example, an AI assistant that answers customer messages).</li>
<li>Copy the seven-line project table above and fill it in for your project.</li>
<li>Circle any line you cannot answer.</li>
<li>Find the official page of your national regulator and note its contact details.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>The laws of Uganda, Kenya, Nigeria, South Africa and the EU share core principles.</li>
<li>Purpose, minimisation, accuracy, retention, security and accountability apply to every AI project.</li>
<li>People have rights, including in many cases rights about automated decisions.</li>
<li>Check your regulator for registration and cross-border rules.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Consent, minimisation and retention',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to judge whether consent is valid, shrink the data you collect, and set a retention rule. This matters because most data protection problems start with collecting too much, for too long, on a weak basis.</p>

<h3>Consent: what makes it valid</h3>
<p>Consent is one lawful basis for using data, not the only one. Where you rely on it, it should generally be:</p>
<ul>
<li><strong>Informed:</strong> the person knows who you are, what you will do with the data and why.</li>
<li><strong>Specific:</strong> for a stated purpose, not "anything we like".</li>
<li><strong>Freely given:</strong> not forced. If a clinic says "no photo, no treatment", the consent is not free.</li>
<li><strong>Clear:</strong> a deliberate action such as ticking an unticked box, not silence.</li>
<li><strong>Withdrawable:</strong> as easy to take back as to give.</li>
</ul>
<p>For children, laws usually require a parent or guardian to agree. Other lawful bases, such as a contract or a legal duty, may fit better in some cases.</p>

<h3>Consent for AI use</h3>
<p>If you plan to send customer messages to an AI vendor, to train a model, or to make automated decisions, say so plainly. Many people agree to a shop keeping their phone number for delivery, but would not expect it to be used to train a chatbot. A short notice often works:</p>
<pre><code>We use your name and phone number to deliver your order.
We do not share them with advertisers.
Our assistant uses an AI service to draft replies; we remove your
phone number before sending your message to it.
To ask a question or withdraw, call 0700 000 000.</code></pre>

<h3>Minimisation: ask only what you need</h3>
<table>
<thead><tr><th>Collected</th><th>Needed?</th><th>Better</th></tr></thead>
<tbody>
<tr><td>Full date of birth</td><td>Rarely</td><td>Ask "Are you over 18?"</td></tr>
<tr><td>National ID copy to join a loyalty club</td><td>No</td><td>Phone number only</td></tr>
<tr><td>Exact home GPS for deliveries</td><td>Sometimes</td><td>Landmark and area</td></tr>
<tr><td>Free-text notes</td><td>Risky</td><td>Use fixed options to avoid sensitive remarks</td></tr>
</tbody>
</table>

<h3>Retention: delete on schedule</h3>
<p>Data kept forever is data that can leak forever. Set a retention period for each type, give someone the job to delete it, and include copies, backups and chat logs from AI tools.</p>
<pre><code>Data                       Keep for              Then
Job applications (unsuccessful)  6 months        Delete
Customer chat logs               12 months       Delete or anonymise
CCTV                             30 days         Overwrite
Tax records                      As the law requires  Archive, then delete</code></pre>
<p>Keep the periods that your laws require for taxes and records, and ask a professional when unsure.</p>

<h3>Mini-case: the tailor's customers</h3>
<p>A tailor collects customers' measurements and phone numbers and starts using an AI assistant to write order messages. She stops pasting numbers into the tool, uses first names only, and deletes old chats every three months. She has applied minimisation and retention in ten minutes.</p>

<blockquote><p><strong>Quick check:</strong> A job portal says "tick to accept, otherwise you cannot apply" for marketing emails. Is that consent valid?</p><p>Answer: No, because consent to marketing is not freely given if it is forced as a condition of applying.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why include AI tool chat logs in a retention rule?</p><p>Answer: Because they may contain personal data, and data you no longer need should not be kept.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick a form your school or business uses.</li>
<li>Cross out every field you do not truly need.</li>
<li>Write a two-line plain notice explaining the purpose.</li>
<li>Write a retention period for the form and who deletes it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Valid consent is informed, specific, freely given, clear and withdrawable.</li>
<li>Collect the minimum, and say if AI vendors are involved.</li>
<li>Set a retention period and delete on schedule.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Breach response and security habits',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to recognise a personal data breach and follow a calm, ordered response in the first hours. This matters because quick and honest action can limit harm to people, and laws in Uganda, Kenya, Nigeria, South Africa and the EU generally expect organisations to report serious breaches.</p>

<h3>What is a breach?</h3>
<p>A <strong>personal data breach</strong> is a loss, leak, theft, damage or unauthorised access to personal data. It is not only hacking. Common AI-era examples:</p>
<ul>
<li>A staff member pastes a customer list into a public AI tool.</li>
<li>A laptop with student records is stolen.</li>
<li>A spreadsheet is emailed to the wrong person.</li>
<li>A shared chatbot link exposes saved conversations.</li>
<li>A weak password lets a stranger into a mobile money agent's records.</li>
</ul>

<h3>A response plan in six steps</h3>
<ol>
<li><strong>Contain:</strong> stop the leak. Disconnect, change passwords, remove public links, recall the email.</li>
<li><strong>Tell the right person inside:</strong> report at once to your data protection lead or manager. Do not hide it.</li>
<li><strong>Assess:</strong> what data, whose, how many people, how sensitive, and what harm could follow (fraud, embarrassment, danger)?</li>
<li><strong>Notify:</strong> laws set deadlines for telling the regulator and sometimes the people affected, often within days. Check your national rule or ask the regulator; for example, the GDPR uses 72 hours for regulator notification where required.</li>
<li><strong>Help those affected:</strong> tell them what happened, what you did and what they should do (change passwords, watch for scam calls).</li>
<li><strong>Learn:</strong> write what went wrong and fix the cause.</li>
</ol>

<h3>A simple incident record</h3>
<pre><code>Date found:        12 March, 9:40
Reported by:       Front desk
What happened:     Fee list emailed to wrong parent group
Data involved:     Names, class, balances (no phone numbers)
People affected:   About 80 students
Actions taken:     Email recalled; recipients asked to delete
Regulator told:    Decision recorded with reason
Follow-up:         Two-person check before bulk emails</code></pre>

<h3>Everyday security habits that prevent breaches</h3>
<ul>
<li>Use strong, different passwords and two-step verification on email and mobile money accounts.</li>
<li>Keep software updated and lock screens.</li>
<li>Share files with named people, not "anyone with the link".</li>
<li>Back up data and test restoring it.</li>
<li>Train every staff member on what must never go into an AI tool.</li>
</ul>

<h3>Mini-case: the weekend mistake</h3>
<p>On Saturday a youth group coordinator realises that a public AI chat link containing sign-up details of 120 young people is still shared on WhatsApp. She deletes the link, informs the group chair at once, notes what data was in it, and the group decides with advice whether the regulator and the young people must be told. She acted in the right order: contain, report, assess, decide.</p>

<blockquote><p><strong>Quick check:</strong> You realise you pasted customer phone numbers into a public AI tool. What do you do first?</p><p>Answer: Report it straight away to your responsible person and contain it (delete the chat or shared link if you can), because early action reduces harm and meets reporting duties.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why keep an incident record even for small breaches?</p><p>Answer: It shows accountability, helps you decide whether to notify, and lets you fix the cause.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write the name of the person in your organisation who should receive breach reports.</li>
<li>List the top three places where personal data is stored.</li>
<li>For each, write one way it could leak.</li>
<li>Copy the incident record above and fill it in for an imagined stolen laptop.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A breach includes accidents and misuse, not only hacking.</li>
<li>Contain, report, assess, notify, support and learn.</li>
<li>Laws expect timely reporting; check your regulator's rules.</li>
<li>Good everyday habits prevent most breaches.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which of these is most likely to count as personal data?
- Total sales for the month
* A list of customer numbers with each customer's mobile money name
- The weather forecast for Kampala
- The name of a product
= Data that identifies or can be linked to a person is personal data; totals about a business are not.

Q: A school collected parents' phone numbers for fee reminders and now wants to sell them to an advertiser. Which principle is most clearly broken?
- Accuracy
- Security
* Purpose limitation
- Storage limitation
= Data collected for one purpose should not be used for an unrelated one without a proper basis.

Q: Which is an example of valid consent?
- A pre-ticked box on a long form
- A clinic refusing treatment unless a patient allows photo sharing
- Silence after an email notice
* A clear, unticked box next to a plain sentence explaining the purpose, with an easy way to withdraw
= Consent must be informed, specific, freely given, clear and withdrawable.

Q: Replacing students' names with ID codes but keeping a separate list that links codes to names is best described as
- Anonymisation, so the data is no longer personal
* Pseudonymisation, which reduces risk but the data can still be linked to a person
- Deletion
- Encryption
= If the data can be linked back, it is generally still personal data.

Q: A staff member emails a fee list to the wrong parent group. What is the best first response?
- Wait to see if anyone notices
* Try to recall the email, report it internally at once, and assess what data was involved
- Delete the sent items so the evidence disappears
- Blame the parent group
= Contain, report and assess promptly and honestly.

Q: Why set a retention period for chat logs from an AI assistant?
- Chat logs are always illegal
* They may contain personal data, and data you no longer need should be deleted
- It makes the assistant faster
- It is only needed for financial data
= Storage limitation applies to any personal data, including logs and backups.

Q: A loyalty club asks for a copy of the national ID to give 5 percent discounts. Which principle suggests this is a problem?
* Minimisation
- Accuracy
- Transparency only
- Portability
= The ID is more data than the purpose needs; a phone number would be enough.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Training data, creators and truth',
            'summary' => 'Understand where model training data comes from, how copyright affects AI use, and how to handle deepfakes and misinformation.',
            'lessons' => [
                [
                    'title'   => 'Data used to train models',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain how personal and public data can end up in AI models, and decide what your organisation should keep out. This matters because once data is used to train a model, it can be hard to remove, and the people it came from rarely know.</p>

<h3>Where training data comes from</h3>
<ul>
<li><strong>Public web pages, books and articles</strong> collected in large amounts.</li>
<li><strong>User conversations</strong>, if the provider's settings or terms allow using them to improve the service.</li>
<li><strong>Licensed or purchased datasets.</strong></li>
<li><strong>Your own records</strong>, when you build or fine-tune a model for your organisation.</li>
</ul>
<p>"Public" does not mean "free to use for any purpose". A photo on social media is visible to everyone, but the person in it may never have expected it to train a face recognition system.</p>

<h3>Three risks to know</h3>
<table>
<thead><tr><th>Risk</th><th>What it means</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Memorisation</td><td>A model sometimes repeats parts of its training data</td><td>A model outputs a real phone number it saw in training</td></tr>
<tr><td>Re-identification</td><td>Removing names may not hide someone in a small dataset</td><td>"Female teacher, 41, village X" is one person</td></tr>
<tr><td>Loss of control</td><td>Hard to withdraw data once it is inside a trained model</td><td>A person asks for deletion but the model has already learned from it</td></tr>
</tbody>
</table>

<h3>Check the provider's data settings</h3>
<p>Before using an AI tool for work, find out in its settings or terms:</p>
<ol>
<li>Is my content used to train or improve the model? Can I switch this off?</li>
<li>How long is my content stored and where?</li>
<li>Who can see it, including the provider's staff?</li>
<li>Is there a business or education version with stronger data terms?</li>
<li>Can I delete my history, and does that delete it everywhere?</li>
</ol>
<p>Terms differ by product and plan, and they change. Read the current terms for your version and do not rely on memory or hearsay.</p>

<h3>Building your own model with local data</h3>
<p>Local data is valuable. An African-language speech tool needs recordings from African speakers. Doing it responsibly means:</p>
<ul>
<li>Getting clear, informed permission from speakers, including how recordings will be used.</li>
<li>Paying or otherwise rewarding contributors fairly.</li>
<li>Removing identifying details where possible.</li>
<li>Writing down what the data covers and does not cover, so others do not misuse it.</li>
</ul>

<h3>Mini-case: the clinic notes</h3>
<p>A clinic wants to fine-tune a model on past patient notes to draft summaries. Notes contain names, diagnoses and HIV status. The manager decides to wait: first remove identifiers, ask legal advice about consent and purpose, keep the work on a secure system under the clinic's control, and test whether the model repeats real notes. Speed matters less than trust here.</p>

<blockquote><p><strong>Quick check:</strong> A photo is public on social media. Can an organisation freely use it to train a face recognition tool?</p><p>Answer: Not safely; being visible is not the same as permission, and data protection and other laws may still apply.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why is removing names alone not always enough to anonymise a small dataset?</p><p>Answer: Other details such as age, role and village can still point to one person.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open the settings or privacy page of an AI tool you use.</li>
<li>Find out whether your conversations may be used to improve the service and whether you can turn it off.</li>
<li>Find how to delete your history.</li>
<li>Write a two-line rule for your team about what may be pasted into this tool.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Training data comes from the web, user conversations, licensed sets and your own records.</li>
<li>Public does not mean free to use for any purpose.</li>
<li>Check each tool's data settings and keep sensitive data out unless terms and law allow it.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Copyright, creators and AI',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use AI in ways that respect creators, and avoid the most common copyright mistakes. This matters because Ugandan musicians, photographers, writers and designers depend on their work for income, and your organisation can face complaints or loss of trust if it copies them.</p>

<h3>Copyright in simple terms</h3>
<p><strong>Copyright</strong> gives the creator of an original work, such as a song, photo, article, drawing or software, control over how it is copied and used. In Uganda the main law is the Copyright and Neighbouring Rights Act, 2006. Rules on exceptions, such as limited use for study or criticism, differ by country. This lesson is general education, not legal advice.</p>

<h3>The open legal questions</h3>
<p>Courts and governments around the world are still deciding several questions, and answers differ between countries:</p>
<ul>
<li>Is it lawful to train a model on copyrighted works without permission?</li>
<li>Who owns AI-generated output, and is it protected at all if no human creative contribution is involved?</li>
<li>When does an output copy a particular work too closely?</li>
</ul>
<p>Because these questions are unsettled, a careful organisation avoids depending on a risky assumption.</p>

<h3>Safe habits</h3>
<table>
<thead><tr><th>Do</th><th>Avoid</th></tr></thead>
<tbody>
<tr><td>Use AI for ideas, drafts and structure, then add your own original work</td><td>Publishing raw AI output as if it were your original creation</td></tr>
<tr><td>Check the tool's terms for commercial use of output</td><td>Assuming everything made by a tool is free for any use</td></tr>
<tr><td>Use images and music with a clear licence, or your own</td><td>Asking an AI to imitate a living artist's style for a commercial logo or song</td></tr>
<tr><td>Credit sources and quote briefly with attribution</td><td>Pasting a whole article or book chapter into a tool and calling the output yours</td></tr>
<tr><td>Keep a note of how AI was used for important work</td><td>Hiding AI use where rules require disclosure</td></tr>
</tbody>
</table>

<h3>Respect for creators and communities</h3>
<ul>
<li>Folk stories, traditional designs and community songs belong to communities, and using them commercially without involving them can be disrespectful even where the law is unclear.</li>
<li>Voice and likeness matter. Do not clone a singer's or presenter's voice without permission.</li>
<li>If your business hires creators, agree in writing whether and how their work may be used with AI.</li>
</ul>

<h3>Mini-case: the poster</h3>
<p>A small hotel owner asks an image tool for a poster "in the style of" a well-known local painter and prints 500 copies. The painter's followers notice and complain. The owner could have commissioned the painter, hired a local designer to create an original look with AI-assisted drafts, or used licensed images. The money saved was small compared with the loss of goodwill.</p>

<blockquote><p><strong>Quick check:</strong> A student pastes a full newspaper article into an AI tool and asks it to rewrite it slightly, then submits it as their own. What are the problems?</p><p>Answer: It is plagiarism and may infringe copyright, because the work is not original and the source is not credited.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why should a business check a tool's terms before using its images in an advertising campaign?</p><p>Answer: The terms say whether commercial use of the output is allowed and who is responsible for any claims.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose an AI image or text tool.</li>
<li>Find its terms about use of output for commercial purposes and note the sentence.</li>
<li>Draft a rule for your team: when must we disclose that AI was used in published material?</li>
<li>Write one sentence on how you would credit or reward a local creator for work you want to use.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Copyright protects creators, and the law on AI training and output is still developing.</li>
<li>Use AI as help, add your own original work, and respect licences and terms.</li>
<li>Do not imitate living creators commercially or clone voices without permission.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Deepfakes and misinformation',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to spot likely fake media, verify a suspicious message, and respond if your organisation is targeted. This matters because a fake voice note or video can move money, damage a reputation or stir up conflict within minutes, especially around elections and emergencies.</p>

<h3>Key words</h3>
<ul>
<li><strong>Misinformation:</strong> false information shared without meaning to deceive.</li>
<li><strong>Disinformation:</strong> false information spread on purpose.</li>
<li><strong>Deepfake:</strong> a photo, audio or video made or altered with AI to show someone saying or doing something they did not.</li>
<li><strong>Voice cloning:</strong> copying a person's voice from a short recording.</li>
</ul>

<h3>How fakes are used</h3>
<table>
<thead><tr><th>Use</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Fraud</td><td>A cloned voice of a "boss" asks the accountant for an urgent transfer</td></tr>
<tr><td>Family scams</td><td>A call sounds like your child asking for mobile money after "an accident"</td></tr>
<tr><td>Political manipulation</td><td>A fake clip of a candidate "admitting" something before an election</td></tr>
<tr><td>Harassment</td><td>Fake images of women or girls used to shame them</td></tr>
<tr><td>Fake news pages</td><td>AI-written stories with invented quotes</td></tr>
</tbody>
</table>

<h3>Signs that media may be fake</h3>
<p>No single sign is proof, and good fakes may show none of them.</p>
<ul>
<li>Odd details: hands, teeth, shadows, jewellery or text that look wrong.</li>
<li>Lip movement that does not match speech, or an unnatural voice rhythm.</li>
<li>Strong emotion and urgency ("send now, tell no one").</li>
<li>No original source, or an unknown account sharing it first.</li>
</ul>

<h3>A verification routine</h3>
<ol>
<li><strong>Pause</strong> before sharing or paying.</li>
<li><strong>Check the source:</strong> who posted it first? Is it a known, accountable outlet?</li>
<li><strong>Look for other reports</strong> from trusted organisations or fact-checking groups.</li>
<li><strong>Search the image</strong> with a reverse image search to see where else it appeared.</li>
<li><strong>Call back</strong> on a number you already know, never on the number in the message.</li>
</ol>
<p>Detection tools exist but are not perfectly reliable. Use them as one input, never as the only proof.</p>

<h3>Protecting your organisation</h3>
<ul>
<li>Agree a rule: no payment or password change on voice or video alone; confirm by a second channel or a code word.</li>
<li>Limit how much staff voice and video is posted publicly.</li>
<li>Have a plan to publish a correction quickly and report impersonation to the platform and, where needed, the police.</li>
<li>Do not create deepfakes of real people without permission, even as a joke. Laws on harassment, false information and image rights may apply.</li>
</ul>

<h3>Mini-case: the late-night voice note</h3>
<p>A finance officer receives a voice note that sounds like the director: "Send UGX 8,000,000 to this number now, I am in a meeting." The officer remembers the two-channel rule, phones the director's saved number and learns the voice note was fake. The rule cost two minutes and saved eight million shillings.</p>

<blockquote><p><strong>Quick check:</strong> A video of a leader shocks you and ends with "share before it is deleted". What should you do?</p><p>Answer: Pause and verify the source and other reports first, because urgency to share is a common sign of manipulation.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You receive a voice message from your "manager" asking for money. Why is calling the number in the message a poor check?</p><p>Answer: A scammer controls that number; use a contact you already have instead.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Find a viral image or claim you saw recently.</li>
<li>Run the five-step verification routine and write what you found at each step.</li>
<li>Decide: true, false or unclear.</li>
<li>Write a two-sentence rule your family or team could adopt for money requests by voice or video.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Deepfakes and voice cloning make fraud and disinformation cheaper.</li>
<li>Pause, check the source, find other reports and confirm by a second channel.</li>
<li>Set payment rules that do not depend on a voice or video alone.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: An organisation wants to train a model on photos collected from public social media pages. Which statement is most accurate?
- Public photos can always be used for any purpose
* Being visible publicly does not equal permission, so data protection and other rules may still apply
- Only photos of adults are a problem
- The law has no interest in training data
= Public does not mean free to use for any purpose; assess purpose, consent and law.

Q: Before using an AI tool for work, which question is most important about data?
- Does the logo look professional?
* Is my content used to train the model, where is it stored, and can I switch that off or delete it?
- How many users does it have?
- Does it work on a phone?
= Data use and retention settings decide whether it is safe to put work information in the tool.

Q: A restaurant owner asks an AI to create a menu poster "in the style of" a living local painter and prints it for sale. What is the best advice?
- Go ahead; style cannot be a problem
* Avoid imitating a living artist commercially; commission the painter or create an original design
- Print more copies
- Remove the painter's name only
= Imitating a living creator for commercial gain risks complaints and loss of trust, even where the law is unclear.

Q: Which practice best protects against voice-clone fraud?
- Trusting any voice you recognise
* A rule that payments need a second-channel confirmation to a known number
- Asking the caller to repeat the request
- Replying to the voice note immediately
= Voices can be copied; a second channel to a known contact defeats the trick.

Q: A clinic wants to fine-tune a model on patient notes. What should come first?
- Upload all notes to the cheapest tool
* Check the legal basis and purpose, remove identifiers, and use a secure system under the clinic's control
- Ask staff to guess whether it is allowed
- Share the notes with the vendor's marketing team
= Health data is sensitive, so basis, minimisation and security come before experimentation.

Q: A video of a candidate arrives with the message "share before it is deleted". The best first step is to
- Share it to warn others
* Pause and check who first posted it and whether trusted sources report it
- Delete your phone
- Assume it is false because it is a video
= Urgency to share is a manipulation sign; verify before spreading.

Q: A student pastes a magazine article into an AI tool, asks for a rewrite, and submits it as original work. This is mainly a problem of
- Speed
* Plagiarism and possible copyright infringement
- Poor spelling
- Data minimisation
= The work is not original and the source is not credited.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: AI in key sectors',
            'summary' => 'Apply responsible-AI thinking to schools, health, agriculture and mobile money.',
            'lessons' => [
                [
                    'title'   => 'AI in schools and health',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to set sensible limits for AI in education and health settings. This matters because these sectors deal with children and with life-and-death information, so mistakes carry high cost and the people affected often cannot say no.</p>

<h3>AI in schools</h3>
<p><strong>Helpful uses:</strong> explaining concepts in simple words, practice questions, drafting lesson plans, feedback on drafts, translation of materials, and support for learners with reading difficulties.</p>
<p><strong>Risks to manage:</strong></p>
<ul>
<li><strong>Cheating and weak learning:</strong> a student who lets AI do the thinking learns less.</li>
<li><strong>Unreliable "AI detectors":</strong> tools that claim to detect AI writing make mistakes and can accuse honest students, especially those writing in a second language. Do not use them as the only evidence.</li>
<li><strong>Children's data:</strong> names, marks, behaviour notes and photos are sensitive. Prefer tools approved by the school.</li>
<li><strong>Unequal access:</strong> students without a phone, data or electricity fall behind if lessons assume AI access.</li>
<li><strong>Wrong or foreign content:</strong> explanations may not match the national syllabus.</li>
</ul>
<p>Good practice: a clear school rule on which uses are allowed (for example, "explain a topic": yes; "write my coursework": no), teaching students to check answers, and assessment that includes oral explanation or in-class work.</p>

<h3>AI in health</h3>
<p><strong>Helpful uses:</strong> reading scans to help prioritise cases, reminding patients about appointments, summarising guidelines, drafting patient information leaflets, and supporting community health workers with decision aids.</p>
<p><strong>Risks to manage:</strong></p>
<ul>
<li>Health data is among the most sensitive personal data.</li>
<li>A tool tested on patients abroad may be less accurate for local populations or diseases.</li>
<li>A chatbot giving medical advice can sound sure while being wrong.</li>
<li>Patients may not know AI was involved in their care.</li>
</ul>

<h3>Comparing the two sectors</h3>
<table>
<thead><tr><th>Question</th><th>School</th><th>Health</th></tr></thead>
<tbody>
<tr><td>Main data at risk</td><td>Children's records, marks, behaviour</td><td>Diagnoses, test results, HIV status</td></tr>
<tr><td>Worst realistic harm</td><td>Unfair accusation, lost learning</td><td>Wrong treatment, stigma</td></tr>
<tr><td>Minimum safeguard</td><td>Approved tools, clear rules</td><td>Clinician decides; tool is advice only</td></tr>
<tr><td>Who is accountable</td><td>Head teacher</td><td>Licensed clinician and facility manager</td></tr>
</tbody>
</table>

<h3>Mini-case: the community health worker</h3>
<p>A health worker uses an app that suggests whether a child's cough might be pneumonia. The app is a helpful prompt, but the worker is trained to follow national guidelines and refer any child with danger signs, whatever the app says. The app supports the worker; it does not replace the referral rule.</p>

<blockquote><p><strong>Quick check:</strong> A teacher wants to use an "AI detector" score as proof that a student cheated. Is this safe?</p><p>Answer: No, because detectors make errors and can accuse honest students; use conversation, drafts and in-class work alongside any tool.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A clinic's chatbot answers "Can I stop my medicine?" confidently. What control should exist?</p><p>Answer: The chatbot should not give treatment advice and should direct the patient to a clinician, because wrong advice can cause serious harm.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose either a school or a clinic.</li>
<li>Write three AI uses that are allowed and three that are not.</li>
<li>For each allowed use, name who checks the result.</li>
<li>Write a one-line notice for students or patients telling them when AI is used.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Schools and health services handle sensitive data and vulnerable people, so use approved tools and clear rules.</li>
<li>AI detectors are unreliable as sole evidence.</li>
<li>In health, AI supports a trained person; it does not replace clinical judgement or national guidelines.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'AI in agriculture and mobile money',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to evaluate AI tools offered to farmers and mobile money users, and recognise AI-driven scams. This matters because farming and mobile money touch most households in East Africa, and both involve money, data and trust.</p>

<h3>AI in agriculture</h3>
<p><strong>Useful applications:</strong></p>
<ul>
<li>Phone apps that suggest likely crop diseases from a leaf photo.</li>
<li>Weather and planting advice sent by SMS or voice message in local languages.</li>
<li>Satellite and sensor data used to estimate yields or detect drought.</li>
<li>Price information that helps farmers choose when and where to sell.</li>
<li>Chatbots that answer farming questions for extension workers.</li>
</ul>
<p><strong>Risks to check:</strong></p>
<ul>
<li><strong>Local fit:</strong> advice trained on other soils, seeds and climates can be wrong for your district.</li>
<li><strong>Who owns the farm data?</strong> Yields, plot locations and income are valuable. Farmers should know who can use or sell them.</li>
<li><strong>Power imbalance:</strong> if buyers or lenders use farm data to push prices down or refuse credit, farmers may lose out.</li>
<li><strong>Exclusion:</strong> women, older farmers and people without smartphones may be left out.</li>
<li><strong>Wrong advice at planting time</strong> can cost a whole season.</li>
</ul>

<h3>AI in mobile money and digital finance</h3>
<p><strong>Useful applications:</strong> fraud detection that blocks suspicious transfers, customer-service chatbots, credit scoring for people without bank histories, and agent management.</p>
<p><strong>Risks to check:</strong></p>
<ul>
<li><strong>Unfair or unexplained credit decisions</strong> using phone and transaction data (see the lending example in Module 1).</li>
<li><strong>Wrongly blocked accounts</strong> when a fraud model flags an honest user, perhaps cutting off their only income.</li>
<li><strong>Surveillance:</strong> payment data reveals health, religion and relationships.</li>
<li><strong>AI-powered scams:</strong> cloned voices, convincing messages and fake customer-care numbers.</li>
</ul>

<h3>Questions to ask, side by side</h3>
<table>
<thead><tr><th>Question</th><th>Farm advice app</th><th>Digital lender</th></tr></thead>
<tbody>
<tr><td>Tested locally?</td><td>On crops and soils in our area?</td><td>On borrowers like ours?</td></tr>
<tr><td>Data use?</td><td>Who can use plot and yield data?</td><td>Which phone data is used and with what consent?</td></tr>
<tr><td>Explanation?</td><td>Why this advice?</td><td>Why this limit or refusal?</td></tr>
<tr><td>Human route?</td><td>Extension officer</td><td>Customer service that can review</td></tr>
<tr><td>Language?</td><td>Local language and voice</td><td>Plain terms in local language</td></tr>
</tbody>
</table>

<h3>Mini-case: the blocked trader</h3>
<p>A market trader's mobile money account is frozen on a busy Friday after a fraud model flags several large receipts. She cannot restock. The provider's customer care takes three days. A responsible design would hold only the suspicious transaction, give a quick human review path for small traders and explain what happened. When choosing or regulating such tools, ask how long a wrongly flagged customer waits.</p>

<blockquote><p><strong>Quick check:</strong> A farm app offers free advice if farmers share plot locations and yield data. What should the farmer ask?</p><p>Answer: Who will use or sell this data and for what, because farm data has value and may be used against the farmer.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You get a call from "mobile money support" in a familiar voice asking for your PIN to fix a problem. What do you do?</p><p>Answer: Never share your PIN; hang up and call the official number, because AI makes scam voices more convincing but genuine providers do not ask for your PIN.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick a farming, mobile money or savings app that uses AI features.</li>
<li>Copy the table headings above and answer the five questions for it from its help pages or privacy notice.</li>
<li>Mark answers you could not find.</li>
<li>Write one sentence advising a relative whether to use the service.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Check that agricultural AI is tested for local crops, soils and languages.</li>
<li>Ask who owns and can use farm and payment data.</li>
<li>Fraud and credit models need explanations and quick human review.</li>
<li>Never share a PIN, even with a familiar-sounding voice.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A teacher uses an AI-detector percentage to fail a student for cheating. What is the best advice?
- Accept the score, since software is objective
* Use the score only as a prompt to talk with the student and review drafts, because detectors make errors
- Ban all computers
- Ask the student to take the same detector test
= AI detectors are unreliable as sole evidence and can wrongly accuse honest students.

Q: A clinic chatbot tells a patient to stop taking prescribed medicine. Which design flaw is this?
- The chatbot was too slow
* It gives treatment advice instead of sending the patient to a clinician
- It used simple language
- It had a friendly name
= Treatment decisions belong to trained clinicians; wrong advice can cause serious harm.

Q: A farm advice app trained on another region recommends a planting date that fails in your district. What was the main gap?
- The app was free
* It was not tested on local soils, climate and crops
- Farmers did not read the terms
- The phone was old
= Advice must be tested where it will be used.

Q: Which question best protects farmers who share plot and yield data with an app?
- What colour is the logo?
* Who can use or sell this data and for what purposes?
- Which phone brand do you recommend?
- How many photos can I upload?
= Farm data has commercial value and may be used against farmers, so ownership and use matter.

Q: A fraud model freezes a trader's mobile money account for three days after a flag. What improvement is best?
- Block more accounts to be safe
* Hold only the suspicious transaction and offer a fast human review with a clear explanation
- Remove the fraud model completely
- Ask the trader to open a new account
= Fraud tools must balance safety with fair, quick routes to correct mistakes.

Q: A caller with a familiar voice says they are from mobile money support and need your PIN. What do you do?
- Give it, because the voice is familiar
- Give half the PIN
* End the call and use the provider's official number, never sharing the PIN
- Ask them to send an SMS
= Voices can be cloned and genuine providers do not ask for PINs.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Governing AI in your organisation',
            'summary' => 'Write an AI-use policy, assess risk, use governance frameworks, buy AI wisely and complete a capstone.',
            'lessons' => [
                [
                    'title'   => 'Writing an AI-use policy',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a clear one-to-two page AI-use policy for a small organisation such as a school, clinic, shop, SACCO or NGO. This matters because without a policy each staff member invents their own rules, and the first serious mistake becomes the organisation's problem.</p>

<h3>What a policy is for</h3>
<p>A policy is a short written agreement about what is allowed, what is not, and who decides. A good one is simple enough that every staff member reads and remembers it, and specific enough that a manager can say "that breaks rule 3".</p>

<h3>The parts of a small policy</h3>
<table>
<thead><tr><th>Part</th><th>What to write</th></tr></thead>
<tbody>
<tr><td>1. Purpose and scope</td><td>Who and which tools it covers (staff, volunteers, learners)</td></tr>
<tr><td>2. Approved tools</td><td>The list of tools you allow, and the plan or account type</td></tr>
<tr><td>3. Allowed uses</td><td>Examples: drafting, summaries, brainstorming, translation drafts</td></tr>
<tr><td>4. Not allowed</td><td>Examples: pasting personal data, final decisions about people, fake content of real people</td></tr>
<tr><td>5. Data rules</td><td>What may never be entered; how to anonymise when needed</td></tr>
<tr><td>6. Human checking</td><td>Who checks facts and quality before anything is sent or published</td></tr>
<tr><td>7. Disclosure</td><td>When we tell customers, learners or readers that AI helped</td></tr>
<tr><td>8. Incidents</td><td>Who to tell if something goes wrong (link to your breach plan)</td></tr>
<tr><td>9. Owner and review</td><td>Named owner; review every six months</td></tr>
</tbody>
</table>

<h3>A sample clause set</h3>
<pre><code>Mwangaza Youth SACCO: AI Use Policy (draft)

1. We may use approved AI tools to draft and summarise
   documents that contain no member information.
2. Staff must not enter names, phone numbers, ID numbers,
   account balances or loan details into any AI tool.
3. AI must not decide a loan, a discipline case or an
   employment matter. A named officer decides.
4. Every AI-assisted document is checked by the author for
   facts and tone before it is sent.
5. We do not create images, audio or video of real people
   with AI without their written permission.
6. Members are told when a chatbot, not a person, replies.
7. Report mistakes to the Data Protection Officer within
   24 hours.
Owner: General Manager.   Review: every 6 months.</code></pre>

<h3>Making the policy work</h3>
<ul>
<li><strong>Keep it short and plain.</strong> Use the languages your staff use daily.</li>
<li><strong>Train people</strong> with real examples, not only a document.</li>
<li><strong>Give an easy route to ask</strong> "may I use AI for this?" so staff do not hide use.</li>
<li><strong>Do not ban everything.</strong> A total ban usually pushes use into private phones, where nobody can see it.</li>
<li><strong>Check and update.</strong> Tools, laws and your needs change.</li>
</ul>

<h3>Mini-case: the school rule that failed</h3>
<p>A school policy said only "AI is not allowed". Teachers kept using free tools on personal phones and pasted learner lists into them, because nobody had told them what was safe. The next version listed approved uses, banned pasting learner names, and named the deputy head as contact. Use rose, but risk fell.</p>

<blockquote><p><strong>Quick check:</strong> Why is a total AI ban often a weak policy?</p><p>Answer: People still use AI privately, so risks continue unseen, whereas clear allowed and banned uses guide safer behaviour.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A policy says "use AI responsibly". What is missing?</p><p>Answer: Specific, checkable rules, such as which data is never entered and who checks the output.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a real or imagined organisation of 5 to 30 people.</li>
<li>Using the table of nine parts, write one or two sentences for each.</li>
<li>Add at least two rules about personal data and one about human checking.</li>
<li>Give your draft to a friend and ask which rule is unclear or hard to follow, then improve it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A small policy covers tools, allowed and banned uses, data, checking, disclosure, incidents and ownership.</li>
<li>Specific rules beat general slogans.</li>
<li>Pair the policy with training and an easy way to ask questions.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'A simple AI risk assessment',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to complete a one-page risk assessment (sometimes called an impact assessment) before starting an AI project. This matters because thinking about harm on paper is cheap; fixing harm after it reaches real people is expensive and painful. Some laws require formal data protection impact assessments for high-risk processing, so this habit also prepares you for that.</p>

<h3>The idea: likelihood and impact</h3>
<p>For each possible problem, ask two things: how likely is it, and how bad would it be? Rate each as Low, Medium or High and combine them.</p>
<table>
<thead><tr><th></th><th>Impact low</th><th>Impact medium</th><th>Impact high</th></tr></thead>
<tbody>
<tr><td><strong>Likely: high</strong></td><td>Medium</td><td>High</td><td>Very high</td></tr>
<tr><td><strong>Likely: medium</strong></td><td>Low</td><td>Medium</td><td>High</td></tr>
<tr><td><strong>Likely: low</strong></td><td>Low</td><td>Low</td><td>Medium</td></tr>
</tbody>
</table>

<h3>The template</h3>
<ol>
<li><strong>Describe the project:</strong> what the AI does, for whom, and who decides.</li>
<li><strong>List the data:</strong> what personal data goes in, where it is stored, who sees it, how long it is kept.</li>
<li><strong>List the people affected:</strong> customers, learners, staff, and any vulnerable group (children, patients, people with low literacy).</li>
<li><strong>Identify risks:</strong> unfairness, privacy, wrong output, security, over-reliance, copyright, reputational harm.</li>
<li><strong>Rate each risk:</strong> likelihood and impact, then the combined level.</li>
<li><strong>Choose controls:</strong> what you will do to reduce each risk.</li>
<li><strong>Decide:</strong> go ahead, go ahead with conditions, or stop. Record who decided and the date.</li>
<li><strong>Plan the review:</strong> when you will check how it is actually working.</li>
</ol>

<h3>A worked example</h3>
<pre><code>Project: Chatbot answering parents' fee questions (primary school)

Risk                         Likely  Impact  Level      Control
Wrong balance given          Medium  High    High       Bot shows balance only from the
                                                         system; "talk to bursar" button
Parents' phone data leaks    Low     High    Medium     Vendor contract; no data used
                                                         to train; access limited
Luganda messages misread     High    Medium  High       Test with 50 real messages;
                                                         fall back to staff
Staff stop checking          Medium  Medium  Medium     Weekly sample of 20 chats

Decision: Go ahead with conditions (pilot for one term).
Decided by: Head Teacher, 5 May.   Review: end of term.</code></pre>

<h3>Good habits for assessments</h3>
<ul>
<li>Involve the people who will use the tool and, where possible, those affected by it.</li>
<li>Be honest. A risk list that finds nothing is probably not looking hard enough.</li>
<li>Rewrite the assessment when the project changes.</li>
<li>Keep it with the project records; it shows accountability.</li>
</ul>

<h3>When to be extra careful</h3>
<p>Raise the level of checking when the AI affects access to money, work, education, health or safety, when it uses data about children or sensitive data, when decisions are automated, or when the system watches people (cameras, tracking). For these, consider asking a lawyer or the national regulator.</p>

<blockquote><p><strong>Quick check:</strong> A risk is unlikely but would harm a patient seriously if it happened. How should it be rated?</p><p>Answer: Medium or higher, because high impact means it needs a control even if it is unlikely.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A risk assessment lists risks but no controls or decision. Why is it incomplete?</p><p>Answer: The point is to reduce risk and decide; without controls and a recorded decision nothing changes.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a project: for example, a WhatsApp assistant for a small shop.</li>
<li>Write the project and data lines of the template.</li>
<li>List at least four risks and rate each using the table.</li>
<li>Write one control for each risk rated Medium or higher.</li>
<li>Write the decision and a review date.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Rate each risk by likelihood and impact, then choose controls and decide.</li>
<li>Extra care is needed for money, health, education, children and automated decisions.</li>
<li>Review the assessment after launch.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Governance frameworks: UNESCO, African Union and NIST',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe the main international AI frameworks and borrow the useful parts for your own organisation. This matters because you do not need to invent responsible AI from nothing, and showing that your policy follows a recognised framework builds trust with funders, partners and customers.</p>

<h3>Why frameworks exist</h3>
<p>Frameworks are shared guidance written by governments, international bodies and expert groups. Most are voluntary guidance, not law, but they influence national policy and what partners expect. Some places, such as the European Union, also have binding laws on AI that take a risk-based approach. Check which rules actually apply to you.</p>

<h3>Three frameworks to know</h3>
<table>
<thead><tr><th>Framework</th><th>Who</th><th>Core idea</th></tr></thead>
<tbody>
<tr><td>UNESCO Recommendation on the Ethics of Artificial Intelligence (2021)</td><td>UNESCO, adopted by its member states</td><td>Human rights and dignity, fairness, transparency, human oversight, sustainability and attention to inclusion and gender</td></tr>
<tr><td>African Union Continental AI Strategy (endorsed 2024)</td><td>African Union</td><td>An Africa-centred approach to AI that supports development, skills, local innovation and safeguards against harm</td></tr>
<tr><td>NIST AI Risk Management Framework (2023)</td><td>US National Institute of Standards and Technology</td><td>A practical method to manage AI risk: Govern, Map, Measure, Manage</td></tr>
</tbody>
</table>
<p>Details of these documents are updated and new national AI strategies appear regularly. Read the originals through official sites for current wording.</p>

<h3>The NIST four functions in plain words</h3>
<ol>
<li><strong>Govern:</strong> set the culture, roles and policies. Who is responsible? What are our rules?</li>
<li><strong>Map:</strong> understand the context. What is the AI for, who is affected and what could go wrong?</li>
<li><strong>Measure:</strong> test and monitor. Is it accurate, fair and secure for our users?</li>
<li><strong>Manage:</strong> act on the findings. Reduce risks, respond to incidents and improve.</li>
</ol>
<p>Notice that your policy is Govern, your risk assessment is Map, your testing and sampling is Measure, and your controls and incident plan are Manage.</p>

<h3>Common themes across all frameworks</h3>
<ul>
<li>Respect for human rights and dignity.</li>
<li>Fairness and non-discrimination.</li>
<li>Transparency and explainability.</li>
<li>Human oversight and accountability.</li>
<li>Privacy and data protection.</li>
<li>Safety, security and reliability.</li>
<li>Inclusion, including language and gender.</li>
</ul>

<h3>Mini-case: the NGO and its donor</h3>
<p>A Kampala NGO applies for funding to use AI for case management. The donor asks how the NGO will manage AI risk. The NGO shows its short policy, its completed risk assessment and a note mapping them to the four NIST functions and the UNESCO values of fairness and human oversight. The donor sees a serious, organised partner.</p>

<blockquote><p><strong>Quick check:</strong> Your organisation has a risk assessment and a policy. Which NIST functions do they mostly cover?</p><p>Answer: The policy mainly covers Govern and the risk assessment mainly covers Map, with Measure and Manage covered by testing and incident plans.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Is a framework such as the UNESCO Recommendation the same as a national law?</p><p>Answer: No, it is guidance that influences policy; only national or regional laws are binding where they apply.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open the UNESCO or NIST page from the resources list of this course.</li>
<li>Find and copy the names of its main principles or functions.</li>
<li>Match each to a rule in the sample policy from the previous lessons.</li>
<li>Write which framework element you are missing and one action to add it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>UNESCO, the African Union and NIST offer shared guidance on responsible AI.</li>
<li>NIST: Govern, Map, Measure, Manage.</li>
<li>Frameworks are mostly voluntary, so also check the laws that bind you.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Questions to ask AI vendors',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to question an AI supplier before you buy, sign or pilot, and recognise weak answers. This matters because many organisations in Africa buy AI services from abroad, and once you sign, your data and your users are in someone else's hands.</p>

<h3>Why ask?</h3>
<p>A confident sales presentation tells you what the product can do on its best day. It tells you little about what happens to your data, your users or your budget over time. Vendors who take responsibility welcome questions; vendors who avoid them tell you something too.</p>

<h3>Ten questions to ask</h3>
<table>
<thead><tr><th>Area</th><th>Question</th><th>A good answer sounds like</th></tr></thead>
<tbody>
<tr><td>Data use</td><td>Do you use our data to train your models?</td><td>"No, unless you opt in, and it is in the contract"</td></tr>
<tr><td>Location</td><td>Where is our data stored and processed?</td><td>Named countries; transfer terms explained</td></tr>
<tr><td>Access</td><td>Who at your company can see it?</td><td>Limited staff, logged, under confidentiality</td></tr>
<tr><td>Deletion</td><td>How do we get our data out or deleted when we leave?</td><td>Clear process and timeline</td></tr>
<tr><td>Testing</td><td>Was it tested on users like ours, in our languages?</td><td>Evidence, numbers and limitations</td></tr>
<tr><td>Fairness</td><td>Can you show results by group?</td><td>Yes, or a plan to let you test it</td></tr>
<tr><td>Explanation</td><td>Can it give reasons for a decision?</td><td>Yes, with examples</td></tr>
<tr><td>Security</td><td>How do you protect data and handle breaches?</td><td>Named practices; notification promise in the contract</td></tr>
<tr><td>Legal</td><td>Do you comply with data protection law here?</td><td>Clear statement; registration where required</td></tr>
<tr><td>Support and cost</td><td>What happens if you raise prices, change the product or stop?</td><td>Notice period, export options, price terms</td></tr>
</tbody>
</table>

<h3>Warning signs</h3>
<ul>
<li>"Our algorithm is a secret, we cannot tell you anything."</li>
<li>"It is 99 percent accurate" with no information on who it was tested on.</li>
<li>Pressure to sign today for a special price.</li>
<li>The contract gives the vendor wide rights to reuse your data.</li>
<li>No way to take your data out.</li>
</ul>

<h3>Do a small pilot first</h3>
<ol>
<li>Start with a short, limited pilot using little or no sensitive data.</li>
<li>Define what success and failure look like before you begin.</li>
<li>Test with real local examples, including difficult cases.</li>
<li>Decide only after reviewing results and risks.</li>
</ol>

<h3>Mini-case: the SACCO software</h3>
<p>A SACCO is offered a credit-scoring tool. When the manager asks for results by gender and by region, the vendor says it does not collect them. The manager asks for a trial on 200 anonymised past loans, compares the tool's scores against actual repayment, and finds it rejects many good borrowers in rural areas. The SACCO negotiates changes, or walks away. A question or two saved the members from an unfair system.</p>

<blockquote><p><strong>Quick check:</strong> A vendor says "we cannot reveal anything about how the model works or who it was tested on". How should you treat this for a loan decision?</p><p>Answer: As a serious warning sign, because high-stakes decisions need evidence of testing and some explanation.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why ask about exit and deletion before signing?</p><p>Answer: Once you rely on the service, leaving or recovering your data can be hard unless the contract sets it out.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick an AI product page, such as a chatbot, scoring or transcription service.</li>
<li>Use the ten-question table and note which answers you can find on its website or in its terms.</li>
<li>Mark the questions that remain unanswered.</li>
<li>Write an email of five questions you would send to the vendor.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Ask about data use, location, access, deletion, testing, fairness, security, law and exit.</li>
<li>Be wary of secrecy, vague accuracy claims and pressure.</li>
<li>Pilot small with local data before committing.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Capstone: policy and risk assessment for a real organisation',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>In this capstone you will produce two practical documents for a real organisation, such as your school, a local business, a church group, a SACCO, an NGO or a clinic: an AI-use policy and a risk assessment for one AI project. This matters because a finished, usable document shows employers and communities that you can turn principles into action.</p>

<h3>Step 1: Choose and understand your organisation</h3>
<p>Pick an organisation you can talk to or know well. Write five lines: what it does, how many people work there, what personal data it holds, which AI tools staff already use (or might), and who would own AI decisions. If you cannot talk to anyone, use a realistic imagined organisation, and do not use real people's private data.</p>

<h3>Step 2: Find out what is really happening</h3>
<p>Ask three or four staff members, kindly and without blame: "Do you use AI tools? For what? Have you ever pasted customer or learner information into one?" This shows the gap between what is written and what happens.</p>

<h3>Step 3: Draft the policy</h3>
<p>Use the nine-part template from the policy lesson. Keep it to two pages. Check that it includes at least:</p>
<ul>
<li>Approved tools and allowed uses.</li>
<li>A list of data never to be entered, using words your staff understand.</li>
<li>Human checking before anything goes to customers.</li>
<li>Disclosure when a chatbot or AI-made content is used.</li>
<li>Whom to tell about incidents.</li>
</ul>

<h3>Step 4: Choose one AI project and assess it</h3>
<p>Choose a realistic project, such as an AI assistant for customer messages, a tool to summarise meeting notes, an AI-assisted CV filter or a chatbot for fee questions. Complete the eight-part risk assessment, with at least five risks, ratings, controls and a decision.</p>

<h3>Step 5: Check against principles and frameworks</h3>
<table>
<thead><tr><th>Check</th><th>Yes / No</th><th>Where in your documents</th></tr></thead>
<tbody>
<tr><td>Purpose clear and limited</td><td></td><td></td></tr>
<tr><td>Only necessary data used</td><td></td><td></td></tr>
<tr><td>Retention period set</td><td></td><td></td></tr>
<tr><td>Fairness tested by group</td><td></td><td></td></tr>
<tr><td>Human oversight with power to override</td><td></td><td></td></tr>
<tr><td>Vendor questions asked</td><td></td><td></td></tr>
<tr><td>Incident and breach route named</td><td></td><td></td></tr>
<tr><td>Owner and review date named</td><td></td><td></td></tr>
</tbody>
</table>

<h3>Step 6: Present and improve</h3>
<p>Share a summary with the organisation's leader or a friend who can challenge you. Ask them: "What is unclear? What would staff ignore? What is missing?" Revise. Note that this course is general education: for legal obligations, the organisation should consult a qualified lawyer or its national data protection office.</p>

<h3>What a strong submission looks like</h3>
<ul>
<li>Plain, short and specific rules, not slogans.</li>
<li>Risks drawn from real local situations: languages, mobile money, shared phones, intermittent power.</li>
<li>Clear owners, review dates and decisions.</li>
<li>Honest limits: what you could not find out.</li>
</ul>

<blockquote><p><strong>Quick check:</strong> Why interview staff before writing the policy?</p><p>Answer: To learn what is really happening, so the rules fit actual behaviour and close real gaps.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Your risk assessment finds a High risk with no practical control. What should the decision be?</p><p>Answer: Do not proceed as planned; redesign the project, reduce the data or impact, or stop.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Complete steps 1 and 2 and write your five-line organisation profile.</li>
<li>Draft the two-page policy.</li>
<li>Complete the risk assessment for one project.</li>
<li>Fill in the Step 5 checklist and fix any "No".</li>
<li>Present to a reviewer and write down two improvements you made.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Responsible AI becomes real when it is written, owned, checked and reviewed.</li>
<li>Start with what people actually do, then write rules that fit.</li>
<li>Pair every policy with a risk assessment for the projects that matter.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A school bans AI entirely, but teachers keep pasting learner lists into free tools on their phones. What is the best improvement?
- Increase the penalty for use
* Write a policy listing approved tools and uses, banning pasting learner names, and naming a contact for questions
- Ignore it
- Ask the vendor to ban the school
= Clear allowed and banned uses guide safer behaviour better than a ban that drives use out of sight.

Q: In a risk assessment, which risk needs a control even though it is unlikely?
- Low impact and low likelihood
- A typing mistake in a newsletter
* A data leak that would expose patients' HIV status
- A slow website on one afternoon
= High impact risks need controls even when they are unlikely.

Q: Which of these best matches the NIST function "Map"?
- Responding to a breach
* Understanding the purpose, context, people affected and what could go wrong
- Writing the company logo
- Buying the tool
= Map is about understanding context and identifying risks.

Q: A vendor says its model is "99 percent accurate" but will not say who it was tested on. What should you do?
- Sign today to get the discount
- Assume it is accurate in your district too
* Ask for evidence of testing on users like yours, results by group, and run a small pilot first
- Tell staff to trust the tool
= Accuracy claims mean little without information on the test population and a pilot on your own cases.

Q: Which contract term is most important to check about data when you stop using an AI service?
- The colour of the invoice
* Whether you can export and delete your data, and how quickly
- The number of staff in the vendor's office
- The vendor's social media pages
= Exit and deletion rights decide whether you keep control of your data.

Q: Which statement about the UNESCO Recommendation on the Ethics of AI is correct?
* It is international guidance adopted by member states, not a binding national law
- It is the data protection law of Uganda
- It bans all face recognition
- It applies only to universities
= Frameworks like UNESCO's guide policy, while national laws bind organisations.

Q: Before writing a policy for a small NGO, what is the most useful first step?
- Copy a large company's 40-page policy
* Ask staff how they actually use AI now and what data they have pasted into tools
- Ban staff from talking about AI
- Wait for a breach
= Knowing real behaviour lets you write rules that fit and close real gaps.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: A hospital app was trained on patients from another continent. Before using it locally, what should the hospital do first?
- Roll it out to all wards
* Test its accuracy on local patients and keep clinicians in charge of decisions
- Remove clinicians to save money
- Hide that AI is used
= Tools must be tested on the population they will serve, with human clinical oversight.

Q: An employer's CV tool ranks men higher because past hires were mostly men. This is an example of
- Random error
* Historical bias copied from the training data
- A language problem only
- Good performance
= Models can learn and repeat unfair patterns from past decisions.

Q: A face recognition system has far more false matches for some groups. What is the most appropriate response for a security company?
- Use it anyway because average accuracy is high
* Test on local faces by group, limit its use, and require human review before any action
- Use it only on women
- Publish the matches online
= Unequal error rates can cause wrongful stops, so strict testing and human review are essential.

Q: A voice assistant works well for formal Kiswahili but not for rural accents. This is mainly a problem of
- Battery life
- Screen size
* Language coverage and unrepresentative training data
- Internet price
= Training data that lacks some varieties makes tools worse for those speakers.

Q: A manager approves 400 AI-ranked applications in an hour. What is the likely problem?
- None; speed is good
* Automation bias, because the reviewer cannot genuinely check the decisions
- The AI is too slow
- The applications are too short
= Oversight needs time, information and power to disagree.

Q: Which is the best example of explainability?
- "The system decided."
* "Your application was declined because income records were missing and two repayments were late; you can ask for review."
- "It is confidential."
- "The algorithm is advanced."
= Explanations should give understandable reasons and a route to correction.

Q: A shop owner keeps customers' phone numbers to deliver goods and then uses them to promote a friend's business. Which principle is most clearly broken?
- Accuracy
* Purpose limitation
- Security
- Backups
= Data collected for delivery should not be used for unrelated marketing without a proper basis.

Q: Which item is most likely to be sensitive personal data?
- A shop's opening hours
* A learner's disability or health status
- The price of maize
- A product name
= Health and similar categories need extra care.

Q: A website says "By continuing you agree to everything, including sharing your data with partners." Why is this weak consent?
- It is too short
* It is not specific or freely given, and nobody is clearly informed
- It uses English
- It has a button
= Valid consent needs clear information, a specific purpose and real choice.

Q: A clinic's retention schedule says to keep unsuccessful job applications for 6 months, then delete. Which principle does this apply?
* Storage limitation
- Purpose limitation
- Transparency
- Accuracy
= Personal data should be kept only as long as needed.

Q: You discover that a spreadsheet of customer details was pasted into a public AI tool by a colleague. What is the correct order of response?
- Wait, then investigate later
* Contain it, report to the responsible person, assess what data and who is affected, then decide on notification
- Delete the records and say nothing
- Post an apology online first
= Prompt containment, internal reporting and assessment come first, and notification decisions follow.

Q: Which statement about Uganda's Data Protection and Privacy Act, 2019 is the safest to make in a training session?
- It has no connection to AI projects
* It sets rules for how personal data is collected and used, which can apply to AI projects handling personal data, and a lawyer or the PDPO should be consulted for specifics
- It bans all use of AI
- It only covers government data
= The Act applies to personal data wherever it is processed, including in AI uses, but detailed obligations need proper advice.

Q: A school wants to use a US-based AI service to store pupils' records. Which question matters most?
- What is the app's colour?
* Where will the data be stored, who can access it, and are transfer rules and the contract suitable?
- How many stars does it have?
- Is it popular?
= Cross-border data and processor terms are core data protection issues.

Q: A model is trained on a dataset where names were removed but age, job and village remain for a small community. The main risk is
- Spelling errors
* Re-identification of individuals from remaining details
- File size
- Training speed
= Combinations of details can point to one person.

Q: A student pastes a whole article into an AI tool to rephrase it and submits it as original. This is mainly
* Plagiarism and possible copyright infringement
- Good citation
- Data minimisation
- An accessibility feature
= The work is not original and the source is not credited.

Q: A business wants to use AI output for advertising. What should it check first?
- The tool's logo
* The tool's terms on commercial use of output and any licence limits
- The weather
- The number of followers
= Terms decide who can use output and who bears risk.

Q: The finance officer gets a voice message from the "director" demanding an urgent transfer. What is the best control?
- Reply with the account number
* Confirm through a second channel using a known number or code word before paying
- Forward the voice note
- Pay half
= Cloned voices are convincing, so independent confirmation is needed.

Q: Which is the best description of a deepfake?
- A slow internet connection
* Media created or altered with AI to show someone doing or saying something they did not
- A type of spreadsheet formula
- An online ad
= Deepfakes imitate real people's faces, voices or actions.

Q: A health chatbot confidently tells a patient to stop medication. The best design fix is
- Make the chatbot sound less sure
* Restrict it from treatment advice and direct patients to a clinician
- Add emojis
- Remove the patient's name
= Medical decisions should rest with qualified clinicians.

Q: A mobile money fraud model freezes an honest trader's account for days. Which improvement matters most?
- Block more accounts
* Narrow the action to the suspicious transaction and add a fast human review and explanation
- Stop all fraud detection
- Charge the trader a fee
= Safety measures must include fair, quick correction of errors.

Q: A farm advice app asks farmers to share their plot locations and yields in exchange for free tips. What should the organisation recommending it ask?
- Is it free forever?
* Who can use or sell the data, and for what purposes?
- Does it have a logo?
- Is it available in English only?
= Farm data is valuable and could be used against farmers.

Q: In a risk assessment, a high-impact risk has no practical control. The sensible decision is
- Proceed anyway
* Redesign, reduce data or scope, or stop the project
- Hide the risk
- Wait for complaints
= High risks without controls should block or change the project.

Q: Which is the best structure for a small organisation's AI-use policy?
- A general statement to use AI wisely
* Specific rules on approved tools, banned data, human checks, disclosure, incident reporting, an owner and a review date
- A list of every AI product on the market
- A ban on talking about AI
= Specific, checkable rules work better than slogans.

Q: Which NIST function is mainly about setting roles, culture and policies?
- Map
- Measure
- Manage
* Govern
= Govern covers responsibility, culture and policy.

Q: A vendor says "we use your data to improve our models, and you cannot opt out." What should you do for a project using customer data?
- Accept, since all vendors do this
* Treat it as a major risk; look for another vendor or plan, or avoid putting personal data in
- Ask customers to sign
- Publish the data yourself
= Unwanted training use of personal data can breach purpose and consent expectations.

Q: Before signing for an AI service, which pilot approach is best?
* Run a limited pilot with little or no sensitive data, local test cases and clear success criteria
- Roll it out to every office immediately
- Use only the vendor's examples
- Pilot with all of the live customer data
= A small, realistic pilot gives evidence while limiting harm.

Q: How do UNESCO and African Union AI frameworks differ from national data protection laws?
- They are identical
* They are mostly guidance that shapes policy, while data protection laws are binding rules where they apply
- They cancel national laws
- They apply only to governments
= Frameworks guide good practice, and binding obligations come from laws and regulations.
QUIZ,
];
