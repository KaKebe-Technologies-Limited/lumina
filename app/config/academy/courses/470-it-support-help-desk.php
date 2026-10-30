<?php
return [
    'slug'        => 'it-support-help-desk',
    'title'       => 'IT Support and Help Desk Essentials',
    'category'    => 'hardware',
    'level'       => 'beginner',
    'icon'        => 'settings',
    'sort'        => 470,
    'summary'     => 'Learn the job of an IT support officer: run a help desk with tickets, troubleshoot calmly, talk to non-technical users, fix common problems, protect data and build a career.',
    'description' => '<p>Every school, clinic, shop, bank and office depends on computers, phones and printers, and every one of them needs someone who can fix problems and keep people working. That person is the IT support officer, and the place where problems arrive is the help desk. It is one of the best first jobs in technology, because it teaches you how real systems break and how real people use them.</p><p>This beginner course focuses on the support job itself, not only on the machines. You will learn how a help desk works from the first call to the closed ticket, how to talk to people who are stressed and not technical, and a reliable troubleshooting method you can use on any problem. You will practise fixes for common Windows, Android, Office, printer and Wi-Fi problems, handle accounts, passwords and multi-factor authentication, use remote support tools with consent, protect data with backups, and respond to malware.</p><p>You will also learn to keep an asset list, write knowledge-base articles, use AI assistants safely, work in a team to service-level targets and act ethically with other people\'s private information. The course ends with a capstone in which you run a simulated help desk with ten tickets.</p>',
    'outcomes'    => [
        'Describe what an IT support officer does and the levels of a help desk',
        'Log, prioritise, escalate, resolve and close a support ticket correctly',
        'Communicate clearly and kindly with non-technical users using plain words and simple scripts',
        'Apply the troubleshooting method: ask, reproduce, isolate, fix, verify and document',
        'Fix common Windows, Android, Office, printer, Wi-Fi and peripheral problems',
        'Manage accounts safely, including password resets, multi-factor authentication, onboarding and offboarding',
        'Use remote support tools only with consent and protect users\' privacy',
        'Respond to malware, restore data from backups and keep an asset inventory',
        'Write a knowledge-base article and use AI assistants safely when troubleshooting',
        'Plan a support career and complete a simulated help desk of ten tickets',
    ],
    'requirements' => ['Completed Computer Basics and Digital Literacy (computer-basics), or equivalent experience using a computer and a smartphone', 'A computer or laptop with Windows (a phone with Android is helpful but not required)', 'Comfortable reading and writing English at a basic work level'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Microsoft Support', 'url' => 'https://support.microsoft.com/'],
        ['title' => 'Android Help (Google)', 'url' => 'https://support.google.com/android'],
        ['title' => 'CompTIA: IT certifications and career information', 'url' => 'https://www.comptia.org/'],
        ['title' => 'Cisco Networking Academy', 'url' => 'https://www.netacad.com/'],
        ['title' => 'Microsoft Learn', 'url' => 'https://learn.microsoft.com/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: The support job and the help-desk process',
            'summary' => 'Understand the role, follow a ticket from start to finish and learn how service levels shape the work.',
            'lessons' => [
                [
                    'title'   => 'What an IT support officer does',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe the work of an IT support officer and explain how it differs from other IT jobs. This matters because many beginners think support is only "fixing computers", when most of the job is helping people, so knowing the real job helps you prepare for it.</p>

<h3>The job in one sentence</h3>
<p>An <strong>IT support officer</strong> (also called a help-desk officer, IT technician or service-desk agent) helps people use technology so they can keep working. The people you help are called <strong>users</strong> or <strong>customers</strong>, even when they are your own colleagues.</p>

<h3>A normal day</h3>
<ul>
<li>Answer calls, WhatsApp messages, emails and walk-in requests.</li>
<li>Record each problem in a list called a <strong>ticket</strong> so nothing is forgotten.</li>
<li>Fix simple problems quickly: a forgotten password, a stuck printer, a slow computer.</li>
<li>Set up new computers and phones, and create accounts for new staff.</li>
<li>Install updates, check backups and keep a list of equipment.</li>
<li>Pass difficult problems to a more senior person.</li>
<li>Write short guides so the same problem is solved faster next time.</li>
</ul>

<h3>Support compared with other IT roles</h3>
<table>
<thead><tr><th>Role</th><th>Main focus</th><th>Example task</th></tr></thead>
<tbody>
<tr><td>IT support officer</td><td>Helping users with daily problems</td><td>Reset a locked account</td></tr>
<tr><td>Network technician</td><td>Cables, routers and Wi-Fi</td><td>Install a Wi-Fi access point</td></tr>
<tr><td>System administrator</td><td>Servers and company systems</td><td>Manage user accounts for the whole office</td></tr>
<tr><td>Security analyst</td><td>Watching for and stopping attacks</td><td>Investigate a suspicious login</td></tr>
<tr><td>Software developer</td><td>Building programs</td><td>Write a new feature for an app</td></tr>
</tbody>
</table>
<p>Support is the doorway into most of these roles.</p>

<h3>Skills that matter</h3>
<p>Technical knowledge is only half. Employers also look for patience, listening, clear writing, honesty about what you do not know, tidy records and a habit of learning. A person who is average at technology but excellent with people is often a better support officer than a brilliant person who is rude.</p>

<h3>Scenario</h3>
<p>Brenda works at a secondary school office in Jinja. At 8:10 a.m. the head teacher says her laptop "is not working". Brenda asks what she sees on the screen. It turns out the laptop is fine, the Wi-Fi password was changed over the weekend. Five minutes, no repair needed. Notice that the skill used was asking, not fixing.</p>

<blockquote><p><strong>Quick check:</strong> A teacher says "the computer is broken". What is the best first thing a support officer does?</p><p>Answer: Ask what the teacher sees and what they were trying to do, because the real problem is often different from the first description.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which job is closest to helping staff with daily problems such as a locked account?</p><p>Answer: IT support officer, because the role focuses on helping users keep working, while the others focus on networks, servers, security or programming.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Think of the last time you asked someone for help with a phone or computer problem.</li>
<li>Write down how that person behaved: did they listen, explain, and check that it was fixed?</li>
<li>List three things they did well and one thing you would do differently as a support officer.</li>
<li>Write a four-line "job advert" for an IT support officer at a school or small business near you.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>IT support helps people use technology so they can keep working.</li>
<li>The job is mostly listening, recording and solving, not only repairing.</li>
<li>People skills and clear writing matter as much as technical knowledge.</li>
<li>Support is a good starting point for many other IT careers.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The help-desk process: ticket, triage, escalation and closing',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to take a problem through the full help-desk process and explain what happens at each stage. This matters because a clear process means no request is lost, urgent problems are handled first and users always know what is happening.</p>

<h3>The seven stages</h3>
<ol>
<li><strong>Receive.</strong> The request arrives by phone, message, email or in person.</li>
<li><strong>Log.</strong> You create a <strong>ticket</strong>: a record with the user's name, contact, the problem, the date and time, and a ticket number.</li>
<li><strong>Triage.</strong> Triage means sorting by urgency, like a nurse in a clinic. You decide the category (for example "printer") and the priority.</li>
<li><strong>Diagnose and fix.</strong> You use the troubleshooting method (Module 2).</li>
<li><strong>Escalate if needed.</strong> If you cannot solve it, or it is beyond your permission, you hand it to someone more experienced.</li>
<li><strong>Resolve.</strong> You apply the fix and confirm with the user that it works.</li>
<li><strong>Close and follow up.</strong> You write what was done, close the ticket and, for important cases, check back later.</li>
</ol>

<h3>Priority: impact and urgency</h3>
<p>Priority depends on two questions. <strong>Impact</strong>: how many people are affected? <strong>Urgency</strong>: how quickly does it need fixing?</p>
<table>
<thead><tr><th>Priority</th><th>Typical situation</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Critical</td><td>Many people stopped, or money or safety at risk</td><td>Internet is down for the whole bank branch</td></tr>
<tr><td>High</td><td>One important person or small group stopped</td><td>The accountant cannot open the accounting program on payday</td></tr>
<tr><td>Medium</td><td>One person affected, has a workaround</td><td>One printer is down, but another works</td></tr>
<tr><td>Low</td><td>Request or small annoyance</td><td>Install a new font</td></tr>
</tbody>
</table>
<p>Note that rank in the company does not set priority by itself. A director's slow mouse is lower than a whole classroom without internet.</p>

<h3>Escalation</h3>
<p><strong>Escalating</strong> is not failure. You escalate when you lack the skill, the access or the authority, or when the time target is about to be missed. A good escalation note says what the problem is, what you already tried, what you found and what you need. Never just write "please help".</p>

<h3>A ticket example</h3>
<pre><code>Ticket: 0231
User: Agnes (Accounts), 0700 000 000
Problem: Cannot print invoices to the office printer
Priority: Medium (other printer works)
Tried: Restarted printer, checked cable, printer shows "offline"
Status: Escalated to network technician - printer lost its IP address
</code></pre>

<h3>Closing properly</h3>
<p>Before you close a ticket: confirm with the user, record the cause and the fix, and tell the user what was done in simple words. Closing a ticket the user thinks is still broken is the fastest way to lose trust.</p>

<blockquote><p><strong>Quick check:</strong> The whole branch loses internet, and one person asks for a new screen saver. Which do you handle first, and why?</p><p>Answer: The internet outage, because its impact is on many people and the work of the branch has stopped.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You tried three fixes and the problem remains. What should your escalation note include?</p><p>Answer: The problem, the steps already tried, what you found and what you need, so the next person does not repeat your work.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open a notebook or spreadsheet and make columns: Number, Date, User, Problem, Priority, Status, Fix.</li>
<li>Log these three problems: a locked account, a slow computer, and no internet for the whole office.</li>
<li>Give each a priority and one sentence explaining why.</li>
<li>Write an escalation note for the internet problem, as if you tried restarting the router.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>The stages are receive, log, triage, fix, escalate, resolve, close and follow up.</li>
<li>Priority comes from impact and urgency, not from a person's job title.</li>
<li>Escalate with a clear note of what you tried.</li>
<li>Confirm with the user before you close a ticket.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Ticketing systems and service levels',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use a ticketing system properly and explain what a service level agreement is. This matters because tickets and time targets make support fair and measurable, so users know what to expect and managers can see where help is needed.</p>

<h3>What a ticketing system does</h3>
<p>A <strong>ticketing system</strong> (also called a service-desk tool) is software that stores every request in one place. Big organisations buy paid tools, and small teams may use a free tool or even a shared spreadsheet. The idea is the same. A good system lets you:</p>
<ul>
<li>Create a ticket with a number, owner and status.</li>
<li>Assign it to a person or team.</li>
<li>Set category and priority.</li>
<li>Add notes and attachments such as screenshots.</li>
<li>Send the user updates.</li>
<li>Search old tickets to find a past solution.</li>
<li>Produce reports, for example "how many tickets this week".</li>
</ul>

<h3>Common ticket statuses</h3>
<table>
<thead><tr><th>Status</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>New</td><td>Logged, nobody has started</td></tr>
<tr><td>In progress</td><td>Someone is working on it</td></tr>
<tr><td>Waiting for user</td><td>You asked a question and are waiting</td></tr>
<tr><td>Escalated</td><td>Passed to another person or team</td></tr>
<tr><td>Resolved</td><td>Fixed, waiting for the user to confirm</td></tr>
<tr><td>Closed</td><td>Confirmed and finished</td></tr>
</tbody>
</table>

<h3>Writing good ticket notes</h3>
<p>Write notes so that a colleague who has never heard of the problem can continue. Include what the user said in their own words, exact error messages, the device name, what you tried and the result. Avoid notes such as "fixed" or "sorted"; write "Cleared the print queue and restarted the Print Spooler service; test page printed".</p>

<h3>Service level agreements</h3>
<p>A <strong>service level agreement (SLA)</strong> is a promise about how fast support will respond and fix problems. It may say: critical tickets get a response in 15 minutes and a fix target of 4 hours; low tickets get a response in one working day. The <strong>response time</strong> is how soon you first reply. The <strong>resolution time</strong> is how soon the problem is solved.</p>
<table>
<thead><tr><th>Priority</th><th>First response</th><th>Target fix</th></tr></thead>
<tbody>
<tr><td>Critical</td><td>15 minutes</td><td>4 hours</td></tr>
<tr><td>High</td><td>1 hour</td><td>8 hours</td></tr>
<tr><td>Medium</td><td>4 hours</td><td>2 working days</td></tr>
<tr><td>Low</td><td>1 working day</td><td>5 working days</td></tr>
</tbody>
</table>
<p>These numbers are only an example; each organisation sets its own.</p>

<h3>Scenario</h3>
<p>A clinic in Mbale logs a ticket at 9:00 for a medium problem. The SLA says first response in four hours. Peter replies at 9:20 with "I have your request and will visit at 11:00". He has met the response target and the user feels cared for, even before the fix.</p>

<blockquote><p><strong>Quick check:</strong> What is the difference between response time and resolution time?</p><p>Answer: Response time is how soon you first reply to the user, and resolution time is how long it takes to solve the problem.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which note is better: "fixed" or "Reset the password and confirmed the user could sign in"?</p><p>Answer: The second, because it tells a colleague exactly what was done and how it was checked.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a free spreadsheet with columns for Ticket, Status, Priority, Opened, First reply, Closed.</li>
<li>Add four sample tickets with times.</li>
<li>Decide on SLA targets for each priority and add a column that says "Met" or "Missed".</li>
<li>Write one well-detailed note for one ticket.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A ticketing system keeps requests, notes and history in one place.</li>
<li>Statuses show where each ticket is in the process.</li>
<li>An SLA is a promise about response and resolution times.</li>
<li>Detailed notes make the next fix faster.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which task is MOST typical of an IT support officer?
- Designing a new company logo
* Resetting a locked account and recording it in a ticket
- Writing a new mobile app from scratch
- Negotiating the company budget
= Support officers help users with everyday problems and record the work.

Q: A user phones in and says "Everything is broken". What is the best first step?
- Reinstall Windows
- Tell them to call back later
* Ask what they see on screen and what they were trying to do
- Escalate to the manager immediately
= Asking questions turns a vague complaint into a problem you can solve.

Q: What is triage in a help desk?
- Deleting old tickets
- Training the user
* Sorting requests by urgency and category
- Buying new equipment
= Triage means deciding what needs attention first, like a nurse in a clinic.

Q: Which problem should normally have the HIGHEST priority?
- One staff member wants a new desktop wallpaper
- A single mouse that double-clicks
* The network is down for a whole office during working hours
- A request to install a font
= Many people are stopped, so impact and urgency are both high.

Q: You cannot fix a problem and must escalate. What is the best note?
- Please help
* Printer offline, restarted printer and PC, cable checked, IP address missing; need network access to check the router
- It does not work
- Urgent!!!
= A good escalation says what you tried and what you need.

Q: When should you close a ticket?
- As soon as you finish typing the fix
* After the user confirms the problem is solved and you record the fix
- After 24 hours of silence, always
- Never, to keep records open
= Confirmation protects trust; recording the fix helps future tickets.

Q: An SLA says "first response within 1 hour for high priority". You reply in 40 minutes but the fix takes 6 hours. What was met?
- Nothing
* The response target was met; the fix target depends on the agreement
- Only the fix target
- Both, automatically
= Response and resolution are two separate targets.

Q: Why does the ticket history help the team?
- It makes the desk look busy
* Colleagues can search past solutions and repeat problems are fixed faster
- It replaces training
- It is only for managers
= A searchable history is one of the biggest time savers in support.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Working with users and solving problems',
            'summary' => 'Communicate clearly with non-technical people and follow a repeatable troubleshooting method.',
            'lessons' => [
                [
                    'title'   => 'Customer care and plain words',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to talk to users with patience, use plain words instead of jargon, and use simple scripts for calls and messages. This matters because a user who feels respected cooperates better, gives you useful information and trusts you next time.</p>

<h3>Think about how the user feels</h3>
<p>People usually contact support when they are worried: a report is due, customers are waiting, or they fear they have lost their work. They may be embarrassed to ask. <strong>Empathy</strong> means showing you understand how they feel. It costs nothing and often calms the situation in seconds.</p>

<h3>The four habits of good support talk</h3>
<ol>
<li><strong>Listen first.</strong> Let the user finish. Do not guess the problem while they are still talking.</li>
<li><strong>Acknowledge.</strong> "I understand this is urgent, let us fix it together."</li>
<li><strong>Explain simply.</strong> Use the user's words and short sentences.</li>
<li><strong>Confirm.</strong> Repeat back what you will do and when.</li>
</ol>

<h3>Jargon and plain words</h3>
<table>
<thead><tr><th>Avoid</th><th>Say instead</th></tr></thead>
<tbody>
<tr><td>Your DNS is not resolving</td><td>The computer is not finding the website address, I will fix the setting</td></tr>
<tr><td>Clear your cache</td><td>Remove the saved temporary pages, which can make a website show old information</td></tr>
<tr><td>The profile is corrupted</td><td>Your personal settings file is damaged, I will make a fresh one</td></tr>
<tr><td>Your session timed out</td><td>You were signed out for safety after being idle</td></tr>
</tbody>
</table>

<h3>Useful scripts</h3>
<p>A script is a set of sentences you can adapt. It keeps you calm when you are busy.</p>
<pre><code>Greeting:   "Good morning, IT Support, this is Sam. How can I help?"
Empathy:    "I am sorry about that. Let us sort it out."
Hold:       "May I put you on hold for two minutes while I check? Is that okay?"
Unknown:    "I am not sure yet. I will find out and call you by 3 p.m."
Closing:    "Is it working for you now? Is there anything else I can help with?"
</code></pre>

<h3>Writing messages</h3>
<p>In email or WhatsApp, put the key point first, use short steps and one request per message. Avoid capital letters, which look like shouting, and avoid blaming: say "the file was deleted" rather than "you deleted the file".</p>

<h3>Difficult situations</h3>
<ul>
<li><strong>An angry user:</strong> stay calm, let them speak, apologise for the inconvenience, then move to action.</li>
<li><strong>You do not know:</strong> admit it and promise a time to come back.</li>
<li><strong>A user who is blamed:</strong> never make anyone feel stupid.</li>
</ul>

<blockquote><p><strong>Quick check:</strong> A user shouts that their report is lost. What is the best first response?</p><p>Answer: Stay calm, acknowledge the stress ("I understand this is urgent"), and then ask what happened, because calming first makes the facts easier to collect.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Rewrite "Your DNS cache is corrupted" for a non-technical user.</p><p>Answer: "The saved web address list on your computer is damaged, I will clear it," because it uses ordinary words that describe the effect.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick a friend or family member and ask them to describe a problem on their phone.</li>
<li>Listen without interrupting, then repeat the problem back in your own words.</li>
<li>Explain the solution without any technical word, or explain each one you must use.</li>
<li>Ask them to rate how clear you were from 1 to 5, and write down their comment.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Users are often worried, so listen and show empathy first.</li>
<li>Use plain words and confirm what happens next.</li>
<li>Scripts keep calls polite and consistent.</li>
<li>Never blame or embarrass the user.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The troubleshooting method',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to follow six steps to solve almost any technical problem, even one you have never seen. This matters because guessing wastes time and can break more things, while a method lets you work calmly and prove what the cause is.</p>

<h3>The six steps</h3>
<ol>
<li><strong>Ask.</strong> What exactly happens? What did you expect? When did it start? What changed recently (an update, a new program, a power cut, a dropped phone)? Is there an error message? Write it down exactly.</li>
<li><strong>Reproduce.</strong> Try to make the problem happen yourself. A problem you can repeat is much easier to fix. If it cannot be repeated, note when it happens.</li>
<li><strong>Isolate.</strong> Narrow down where the problem lives. Is it one program or all programs? One user or everyone? One computer or the whole network? Wired or Wi-Fi?</li>
<li><strong>Fix.</strong> Make ONE change at a time, starting with the easiest and safest. Back up important data before risky changes.</li>
<li><strong>Verify.</strong> Test that the original problem is gone and that nothing else broke. Ask the user to test too.</li>
<li><strong>Document.</strong> Record the cause and the fix in the ticket, and write a guide if the problem will come again.</li>
</ol>

<h3>Isolation questions</h3>
<table>
<thead><tr><th>Question</th><th>If the answer is "yes"</th></tr></thead>
<tbody>
<tr><td>Do other users have the problem?</td><td>Likely a shared cause: network, server, service outage</td></tr>
<tr><td>Does it happen on another computer with the same account?</td><td>Likely the account or settings, not the hardware</td></tr>
<tr><td>Does it happen on mobile data as well as Wi-Fi?</td><td>Likely the website or the account, not your Wi-Fi</td></tr>
<tr><td>Did it work yesterday?</td><td>Look for what changed since then</td></tr>
</tbody>
</table>

<h3>The famous easy fixes</h3>
<p>Check the simple things first. Is it plugged in and switched on? Is the cable secure? Is Wi-Fi turned on, and is Airplane mode off? Is the volume muted? Has the screen brightness been turned down? Has the computer been restarted? Many tickets end here, and there is no shame in it. Experienced technicians check these first.</p>

<h3>Rules for good troubleshooting</h3>
<ul>
<li>Change one thing at a time, otherwise you will not know what worked.</li>
<li>Keep a list of what you tried.</li>
<li>Do not delete user files without permission.</li>
<li>Set a time limit, then escalate.</li>
<li>Undo changes that did not help.</li>
</ul>

<h3>Scenario</h3>
<p>Moses cannot open any website, but WhatsApp on his computer works. Asking shows it began after he tried a free "speed booster" program. Isolating: another browser fails too, and a colleague's computer is fine. Fixing: the booster changed the proxy setting; Moses switches it off. Verifying: sites load. Documenting: "Proxy setting enabled by third-party program; disabled; user advised not to install it."</p>

<blockquote><p><strong>Quick check:</strong> Why change only one thing at a time?</p><p>Answer: If the problem is solved you will know which change did it, and if it is not, you can undo that single change easily.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A website works on a user's phone data but not on office Wi-Fi. Where does the problem probably lie?</p><p>Answer: In the office network or its settings, because the website and the user's account are fine on another network.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a small problem, for example a program that opens slowly on your computer.</li>
<li>Write the six steps on a page and answer each one for that problem.</li>
<li>Try one fix, test, and record the result.</li>
<li>Write one sentence for the "document" step that a colleague could use later.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>The method is ask, reproduce, isolate, fix, verify and document.</li>
<li>Check the simple things first.</li>
<li>Change one thing at a time and keep notes.</li>
<li>Always verify with the user and write down the result.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Difficult cases: remote users, repeated faults and saying no',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to gather information from users you cannot see, handle repeat problems, and decline requests politely when they are unsafe or against policy. This matters because real support is not only easy calls: you will meet unclear descriptions, unhappy users and requests that you must refuse.</p>

<h3>Helping someone you cannot see</h3>
<p>On a phone call you cannot look at the screen, so you must create a clear picture with words. Useful techniques:</p>
<ul>
<li>Ask the user to read the exact words on the screen.</li>
<li>Ask for a screenshot. On Windows, press <strong>Windows key + Shift + S</strong>; on Android, press power and volume down together (this varies slightly by phone).</li>
<li>Give one step at a time and wait for confirmation. "Click the Start button at the bottom left. Tell me when you see a menu."</li>
<li>Use landmarks: "the round button at the top right", not "the icon".</li>
</ul>

<h3>Questions to gather facts</h3>
<pre><code>1. What were you trying to do?
2. What happened instead? Is there a message? Please read it to me.
3. When did it last work?
4. Has anything changed - update, new program, moved, dropped, power cut?
5. Is anyone else affected?
</code></pre>

<h3>Repeat problems</h3>
<p>If the same user or machine keeps returning with the same fault, you have treated a symptom, not the cause. Look at the ticket history, search for a pattern (time of day, one program, one room), and ask for a longer investigation. A fault that is "fixed" three times is probably not fixed.</p>

<h3>Saying no politely</h3>
<p>Sometimes the right answer is no: installing a cracked (pirated) program, giving someone else's password, opening a manager's mailbox without approval, or turning off security software. A good refusal has three parts: say what you cannot do, say why in one sentence, and offer an alternative.</p>
<table>
<thead><tr><th>Request</th><th>Good reply</th></tr></thead>
<tbody>
<tr><td>"Install this free copy of the paid software."</td><td>"I cannot install pirated software because it is illegal and often carries viruses. I can request a licence or install a free alternative."</td></tr>
<tr><td>"Tell me my boss's password."</td><td>"I cannot share anyone's password. If you need access to the files, I can ask your boss to share the folder with you."</td></tr>
<tr><td>"Switch off the antivirus, it is slow."</td><td>"I would rather not, it protects your files. Let me check why it is slow and tune it."</td></tr>
</tbody>
</table>

<h3>Keeping yourself calm</h3>
<p>Take a breath before replying. If you are frustrated, ask a colleague to take the call. Never write an angry message, because tickets and messages can be read later by managers.</p>

<blockquote><p><strong>Quick check:</strong> A user says "my screen has a weird message". How do you get the exact words?</p><p>Answer: Ask for a screenshot or ask them to read the message aloud word for word, because exact wording often points straight to the cause.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A colleague asks you for the finance manager's password "just this once". What do you do?</p><p>Answer: Politely refuse and offer another route, such as asking the manager to share the file, because passwords must never be shared.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write the five fact-gathering questions on a card.</li>
<li>Ask a friend to describe a pretend problem on their phone while you cannot see it, and use only the questions to find out what is wrong.</li>
<li>Write a polite refusal for the request: "Please install this cracked game on the office computer."</li>
<li>Read it aloud and check that it has the three parts: cannot, why, alternative.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use exact messages, screenshots, one step at a time and clear landmarks.</li>
<li>A repeat fault needs the cause found, not just another quick fix.</li>
<li>A good refusal says what you cannot do, why, and what you can do instead.</li>
<li>Stay calm and professional in writing as well as speech.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A worried user phones about a lost file. What should you do FIRST?
- Explain how the hard disk works
* Listen, acknowledge the worry, then ask what happened
- Tell them it is their fault
- Put them on hold without warning
= Listening and empathy calm people and help you gather facts.

Q: Which sentence is best for a non-technical user?
- Your DNS resolver is timing out
- The packets are being dropped
* Your computer cannot find the website address, I will change a setting to fix it
- It is a layer 3 issue
= Plain words explain the effect and what you will do.

Q: In the troubleshooting method, what comes immediately after "Reproduce"?
- Document
- Verify
* Isolate
- Close
= After you can repeat the problem, you narrow down where it lives.

Q: Only one user cannot open a shared program, and on another computer the same user account also fails. What is most likely?
- A broken network cable in the hub
* A problem with the user account or its permissions
- The program has been deleted for everyone
- A power cut
= The failure follows the account, not the computer.

Q: You changed three settings at once and the problem disappeared. What is the problem with this?
- Nothing, speed is all that matters
* You do not know which change fixed it, and the others may cause new problems
- It was impossible
- It will always return
= One change at a time lets you prove the cause and undo mistakes.

Q: A colleague wants you to install a pirated copy of a paid program. What is the best response?
- Install it quietly
* Politely decline, explain it is illegal and risky, and offer a licence or a free alternative
- Ignore the request
- Tell everyone in the office
= A good refusal gives the reason and a helpful alternative.

Q: The same printer problem has been "fixed" three times this month. What should you do?
- Keep restarting it
* Review the ticket history and look for the underlying cause
- Close all the tickets
- Tell the user to stop printing
= Repeat faults signal that the root cause has not been found.

Q: Why check "Is it plugged in and switched on?" first?
- It makes the user feel silly
* Simple causes are common, quick to check and cheap to fix
- It is a rule of the law
- Experienced technicians never do it
= Check the easy things first, before making bigger changes.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Fixing common problems',
            'summary' => 'Practical fixes for Windows, Android, Office, printers, Wi-Fi, peripherals and user accounts.',
            'lessons' => [
                [
                    'title'   => 'Common Windows problems and fixes',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to diagnose and fix the most common Windows problems: slowness, frozen programs, full disks, failed updates and startup problems. This matters because these make up a large part of everyday tickets, and a calm routine solves most of them in minutes.</p>

<h3>A slow computer</h3>
<ol>
<li>Press <strong>Ctrl + Shift + Esc</strong> to open <strong>Task Manager</strong>. Look at the Processes tab and sort by CPU, Memory and Disk to see what uses the most.</li>
<li>Close programs the user does not need. Check that the computer has been restarted recently (a restart clears many problems).</li>
<li>Open <strong>Settings</strong>, then <strong>Apps</strong>, then <strong>Startup</strong> (Windows 11) and turn off programs that do not need to start with Windows. In Windows 10 use the Startup tab in Task Manager.</li>
<li>Check free disk space. If a drive is nearly full, Windows slows down. Use <strong>Storage</strong> in Settings to remove temporary files, and move large old files only with the user's permission.</li>
<li>Run a security scan, because malware can cause slowness.</li>
<li>If the machine has an old mechanical hard drive and little memory (RAM), an upgrade to a solid-state drive and more RAM often helps most.</li>
</ol>

<h3>A program is frozen</h3>
<p>Open Task Manager, select the program marked "Not responding" and choose <strong>End task</strong>. Warn the user that unsaved work may be lost. Many programs also recover a draft when reopened.</p>

<h3>Common error situations</h3>
<table>
<thead><tr><th>Symptom</th><th>First things to try</th></tr></thead>
<tbody>
<tr><td>Update failed or stuck</td><td>Restart, check free space and internet, then run Windows Update again; use the built-in troubleshooter in Settings</td></tr>
<tr><td>No sound</td><td>Check the volume and output device, cable, then restart the audio driver or computer</td></tr>
<tr><td>Cannot open a file type</td><td>Right-click, choose <strong>Open with</strong>, and select the right program</td></tr>
<tr><td>Screen is the wrong size</td><td>Right-click the desktop, choose Display settings, and set the recommended resolution</td></tr>
<tr><td>Blue screen or repeated restarts</td><td>Note the stop code, remove newly installed hardware or software, and escalate with the code</td></tr>
<tr><td>Will not start</td><td>Check power and screen first; then try Startup Repair from the recovery options; protect the data before reinstalling</td></tr>
</tbody>
</table>

<h3>Safe Mode</h3>
<p>Safe Mode starts Windows with only basic drivers and programs. If the computer works in Safe Mode, the problem is probably a program or driver. It helps you remove a bad update or a harmful program. The Windows recovery options let you reach it from Settings under Recovery.</p>

<h3>Built-in troubleshooters</h3>
<p>Settings has a <strong>Troubleshoot</strong> area that can check network, printer, sound and update problems. They are not magic, but they are free and quick, so run them early.</p>

<h3>Scenario</h3>
<p>A secretary in Kampala says her laptop takes ten minutes to start. You open Task Manager and see the disk at 100 percent. Startup shows six programs running. You disable four of them, clear temporary files, and the start time falls to two minutes. You note the fix and advise her to restart weekly.</p>

<blockquote><p><strong>Quick check:</strong> Which tool shows which program is using the most memory and CPU?</p><p>Answer: Task Manager (Ctrl + Shift + Esc), because it lists running programs with their resource use.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A computer works in Safe Mode but not normally. What does this suggest?</p><p>Answer: A normal program or driver is causing the fault, because Safe Mode loads only the basics.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open Task Manager and write down the three programs using the most memory.</li>
<li>Open the Startup list and note which programs start with Windows.</li>
<li>Open Settings and look at Storage to see which type of file takes most space.</li>
<li>Run the Windows troubleshooter for Network or Sound and write down what it reports.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Task Manager shows what is slowing a computer.</li>
<li>Startup programs and a full disk are common causes of slowness.</li>
<li>Safe Mode helps separate Windows problems from program or driver problems.</li>
<li>Protect user data before risky repairs.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Android phones and Office problems',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to fix common problems on Android phones and in office programs such as Word, Excel and Outlook. This matters because in East Africa the phone is often the main computer, and almost every office runs on documents, spreadsheets and email.</p>

<h3>Android: slow phone, full storage</h3>
<ol>
<li>Restart the phone. This clears many temporary faults.</li>
<li>Open <strong>Settings</strong> and look for <strong>Storage</strong>. Delete downloads, duplicate photos and large videos that are no longer needed, after backing up what the owner wants to keep.</li>
<li>In <strong>Settings</strong>, then <strong>Apps</strong>, open an app that misbehaves and use <strong>Clear cache</strong> first. <strong>Clear data</strong> resets the app and may delete its information, so use it carefully.</li>
<li>Update the phone and its apps over Wi-Fi.</li>
<li>Uninstall apps the user does not use.</li>
</ol>
<p>Menu names differ between phone brands, so use the Settings search box when you cannot find an option.</p>

<h3>Android: other common problems</h3>
<table>
<thead><tr><th>Problem</th><th>Check</th></tr></thead>
<tbody>
<tr><td>No mobile data</td><td>Airplane mode, mobile data switch, airtime or data bundle, SIM seated properly, APN settings</td></tr>
<tr><td>Battery drains quickly</td><td>Battery usage list, brightness, apps running in background, an old battery</td></tr>
<tr><td>App keeps crashing</td><td>Update the app, clear cache, restart, reinstall if needed</td></tr>
<tr><td>Cannot install an app</td><td>Storage space, Google account sign-in, date and time, Play Store updates</td></tr>
<tr><td>Forgot the screen lock</td><td>Use the manufacturer's account recovery; resetting the phone erases data, so warn the owner</td></tr>
</tbody>
</table>

<h3>Office: Word, Excel and Outlook</h3>
<ul>
<li><strong>Program not responding:</strong> wait a minute, then close it. Reopen and check the <strong>Document Recovery</strong> panel for the saved draft. Word and Excel can save recovery copies automatically if AutoSave or AutoRecover is on.</li>
<li><strong>Cannot edit a file:</strong> it may be opened as Read-Only, in Protected View (click <strong>Enable Editing</strong> only if the file comes from a trusted source), or open on another person's computer.</li>
<li><strong>Printing layout is wrong:</strong> check page size (A4), margins and the chosen printer.</li>
<li><strong>Formulas show as text in Excel:</strong> the cell may be formatted as Text; change it to General and re-enter the formula.</li>
<li><strong>Email will not send:</strong> check internet, the Outbox, attachment size and the correct sender account.</li>
<li><strong>Add-in or crash problem:</strong> start the program in safe mode by holding the Ctrl key while opening it, which disables add-ins, and confirm the prompt.</li>
</ul>

<h3>Scenario</h3>
<p>A shopkeeper in Masaka reports his phone says "storage almost full" and he cannot take photos of his stock. You open Storage and find 9 GB of WhatsApp videos. With his permission, you back up photos to his Google account, he deletes old videos, and 6 GB becomes free.</p>

<blockquote><p><strong>Quick check:</strong> On Android, what is the difference between Clear cache and Clear data?</p><p>Answer: Clear cache removes temporary files and is safe, while Clear data resets the app and may delete its saved information.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A Word crash happened before the user could save. What do you check first?</p><p>Answer: The Document Recovery panel or the AutoRecover files when Word reopens, because they may contain the latest draft.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>On a phone, open Storage and write down the three biggest items.</li>
<li>Open one app's info page and find the Clear cache button (do not press Clear data).</li>
<li>In Word or Excel, find the AutoSave or AutoRecover setting in Options.</li>
<li>Write one recovery step for each: slow phone, crashed Word file.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Restart, free storage and update apps fix many phone problems.</li>
<li>Clear cache is safe; Clear data can erase app information.</li>
<li>Check Document Recovery after an office program crashes.</li>
<li>Edit Protected View files only when the source is trusted.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Printers, Wi-Fi and peripherals',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to fix common printer, Wi-Fi and peripheral problems with simple checks. This matters because printers and internet are the problems people complain about most, and they stop the whole office from working when they fail.</p>

<h3>Printers: a step-by-step check</h3>
<ol>
<li><strong>Power and paper.</strong> Is it switched on, has paper, no jam, and are the ink or toner levels fine?</li>
<li><strong>Connection.</strong> USB cable secure? For a network printer, is it connected to the same network as the computer?</li>
<li><strong>Status.</strong> Open <strong>Settings</strong>, then <strong>Bluetooth &amp; devices</strong>, then <strong>Printers &amp; scanners</strong> (Windows 11) and check that the printer is not offline or paused.</li>
<li><strong>Print queue.</strong> Open the printer and see the queue. A stuck job blocks the rest. Cancel the stuck job.</li>
<li><strong>Default printer.</strong> Make sure the correct printer is chosen when printing.</li>
<li><strong>Driver.</strong> If the printer is not detected, download the driver from the maker's official website and install it.</li>
<li><strong>Test page.</strong> Print a test page to confirm.</li>
</ol>
<p>If the Print Spooler service is stuck, restarting that service or the computer often clears it.</p>

<h3>Wi-Fi problems</h3>
<table>
<thead><tr><th>Symptom</th><th>Likely cause and action</th></tr></thead>
<tbody>
<tr><td>Wi-Fi icon shows no networks</td><td>Wi-Fi is switched off or Airplane mode is on</td></tr>
<tr><td>Connected but no internet</td><td>The router has lost its internet line or the data bundle is finished; restart the router and check with the provider</td></tr>
<tr><td>Slow Wi-Fi</td><td>Too many devices, a weak signal far from the router, or a large download in progress</td></tr>
<tr><td>Wrong password message</td><td>The password was changed; ask the administrator and forget and re-add the network</td></tr>
<tr><td>Only one device cannot connect</td><td>Forget the network on that device and reconnect</td></tr>
</tbody>
</table>
<p>A quick order of testing: can other devices connect? If yes, the problem is on one device. If none can, the router or internet line is the cause.</p>

<h3>Useful commands</h3>
<p>Open <strong>Command Prompt</strong> and try these. They are safe to run.</p>
<pre><code>ipconfig            shows the computer's network address and the gateway
ping 8.8.8.8        tests whether the internet answers
ping google.com     if this fails but the line above works, the problem is name lookup (DNS)
</code></pre>

<h3>Peripherals</h3>
<ul>
<li><strong>Mouse or keyboard not working:</strong> change the USB port, check batteries or the wireless dongle, try another device.</li>
<li><strong>Second screen not detected:</strong> check the cable, press <strong>Windows key + P</strong> and choose Extend or Duplicate.</li>
<li><strong>Flash disk not appearing:</strong> try another port, check Disk Management, and scan for viruses before opening files.</li>
<li><strong>Projector shows nothing:</strong> check the input source on the projector and the Windows key + P setting.</li>
</ul>

<h3>Scenario</h3>
<p>At a training centre, nobody can print. The queue shows one 400-page document stuck at the top, sent by mistake. You cancel it, restart the print spooler, and everything prints. You then ask the user to check the page range before printing large files.</p>

<blockquote><p><strong>Quick check:</strong> All devices are connected to Wi-Fi but none can reach the internet. Where should you look?</p><p>Answer: At the router and the internet line or data bundle, because the fault is shared by every device.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A printer shows jobs waiting but nothing prints. What is a likely quick fix?</p><p>Answer: Clear the stuck job from the print queue and check the printer is online, because one stuck job can block the queue.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open the printer queue on your computer and find the status of your printer.</li>
<li>Open Command Prompt and run ipconfig, then write down the default gateway.</li>
<li>Run ping 8.8.8.8 and note the average time.</li>
<li>Press Windows key + P and write what the four options are called.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Check power, paper, connection, queue and driver for printers.</li>
<li>Find out whether one device or all devices are affected before blaming Wi-Fi.</li>
<li>ipconfig and ping are simple, safe network tests.</li>
<li>Try another port, cable or device to isolate peripherals.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Accounts, passwords, multi-factor authentication, onboarding and offboarding',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to reset passwords safely, explain multi-factor authentication, and set up and close user accounts correctly. This matters because accounts are the keys to an organisation's data, and careless support can give a criminal the keys.</p>

<h3>Password resets: verify first</h3>
<p>Fraudsters often phone support pretending to be a staff member who is "locked out". Before you reset anything, confirm identity using a method agreed by the organisation. For example: ask a question only the user would know from company records, call back on the number held on file, or ask their manager to approve. Never reset on the word of a stranger or on the basis of a name alone.</p>
<ol>
<li>Verify the identity.</li>
<li>Reset to a temporary password or send a reset link to the user's registered address or phone.</li>
<li>Require the user to change it at next sign-in.</li>
<li>Never say the password aloud, write it in a ticket, or send it in a group chat.</li>
<li>Record the reset in the ticket (without the password).</li>
</ol>

<h3>Strong passwords</h3>
<p>Advise users to use long passphrases, for example three or four unrelated words, rather than short complicated words. Each account needs its own password. A password manager can store them. Passwords must never be shared, even with IT.</p>

<h3>Multi-factor authentication (MFA)</h3>
<p><strong>Multi-factor authentication</strong> asks for two or more proofs: something you <em>know</em> (password), something you <em>have</em> (phone or security key) and something you <em>are</em> (fingerprint). Even if a thief learns a password, they cannot sign in without the second factor.</p>
<table>
<thead><tr><th>Method</th><th>Notes</th></tr></thead>
<tbody>
<tr><td>Authenticator app code</td><td>Works without network signal; stronger than text messages</td></tr>
<tr><td>SMS code</td><td>Better than nothing, but SIM swap fraud is a risk</td></tr>
<tr><td>Approval notification</td><td>Convenient; teach users to deny prompts they did not start</td></tr>
<tr><td>Security key</td><td>Strong protection; needs the key to be kept safe</td></tr>
<tr><td>Backup codes</td><td>Printed or stored safely for when the phone is lost</td></tr>
</tbody>
</table>
<p>If a user loses a phone, verify identity strictly before removing or changing the second factor.</p>

<h3>Onboarding: new staff</h3>
<ol>
<li>Receive an approved request from the manager or HR.</li>
<li>Create the account with the correct name and role.</li>
<li>Give access only to what the role needs. This is the <strong>principle of least privilege</strong>.</li>
<li>Prepare the device, install required programs and enable MFA.</li>
<li>Hand over equipment, record it in the asset list and explain the rules for use.</li>
</ol>

<h3>Offboarding: people who leave</h3>
<ol>
<li>Get written notice from HR or the manager.</li>
<li>Disable the account on the last day (do not delete it straight away).</li>
<li>Remove access to email, systems and shared folders; sign out phones.</li>
<li>Collect laptops, phones, keys and cards, and update the asset list.</li>
<li>Pass the person's files and mailbox to the manager as the organisation decides.</li>
<li>Delete the account only after the retention period.</li>
</ol>

<h3>Scenario</h3>
<p>A caller says, "I am the finance manager and I am travelling, please reset my password and read it to me." The voice sounds right but the number is unknown. You politely say you will call back on the number in the staff list. The real manager is in a meeting and knew nothing. A fraud attempt was stopped.</p>

<blockquote><p><strong>Quick check:</strong> A caller asks for an urgent password reset but cannot be verified. What do you do?</p><p>Answer: Do not reset it; verify through an agreed method such as calling back on the number on file, because the caller may be an attacker.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why disable, rather than immediately delete, a leaver's account?</p><p>Answer: Disabling stops access at once while the organisation keeps the files and mail it may still need.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a five-step "password reset" checklist for a help desk.</li>
<li>Switch on MFA for one of your own accounts if it is available, and note the options offered.</li>
<li>Create a one-page onboarding checklist for a new receptionist.</li>
<li>Create a matching offboarding checklist.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Verify identity before any reset and never reveal or record passwords.</li>
<li>MFA adds a second proof and blocks most stolen-password attacks.</li>
<li>Give new users only the access their role needs.</li>
<li>Disable leavers quickly and delete only after the retention period.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A Windows computer is very slow. What is a good first diagnostic tool?
- The Calculator
* Task Manager to see what uses CPU, memory and disk
- The Paint app
- Reinstalling Windows immediately
= Task Manager shows what is consuming resources so you can act on evidence.

Q: A computer works normally in Safe Mode but not in normal mode. What does this suggest?
- The screen is broken
* A program or driver loaded in normal mode is probably the cause
- The hard disk has no data
- The user is wrong
= Safe Mode loads only the basics, so the fault lies with something excluded from it.

Q: On Android, which action is the SAFEST first fix for an app that keeps crashing?
* Update the app and clear its cache
- Clear data and lose its saved information
- Factory reset the phone
- Delete the Google account
= Cache clearing is safe; data clearing and resets can lose information.

Q: An Excel formula shows as text instead of a result. What is a likely cause?
- The cell is formatted as Text
- The monitor is dim
* Excel is too old to calculate
- The printer is offline
= Cells formatted as Text show formulas as typed; change the format to General and re-enter.

Q: Every device in the office has Wi-Fi bars but none can open websites. Where do you look first?
- Each device's keyboard
* The router and internet line or data bundle
- The printer
- Each user's password
= A fault shared by all devices points to the shared connection.

Q: A printer is online but nothing prints and the queue shows a huge stuck job. What do you do?
- Buy a new printer
* Cancel the stuck job and restart the print spooler or printer
- Reinstall Office
- Disable the Wi-Fi
= One stuck job can block everything behind it.

Q: Someone phones asking you to reset a manager's password and cannot be verified. What is correct?
- Reset it because the voice sounds confident
- Tell them the password over the phone
* Refuse until identity is verified by an agreed method, such as a call back on the number on file
- Send the password by group chat
= Social engineering often targets help desks, so verify first.

Q: Why is multi-factor authentication useful?
- It makes passwords unnecessary
* A stolen password alone is not enough to sign in
- It speeds up the internet
- It deletes old accounts
= A second factor blocks most attacks that use only a stolen password.

Q: What is the best treatment of a leaver's account?
- Delete it immediately and everything in it
- Leave it active for a year
* Disable it on the last day, collect equipment, and delete only after the retention period
- Give the password to the next person
= Disabling stops access but keeps the data the organisation may need.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Protecting people and data',
            'summary' => 'Support remotely with consent, protect data with backups and respond to malware.',
            'lessons' => [
                [
                    'title'   => 'Remote support tools and consent',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to help a user through a remote support session safely, with their clear consent, and protect their privacy. This matters because remote tools let you fix problems without travelling, but they also give you a view of someone's private screen, so trust and rules are essential.</p>

<h3>What remote support is</h3>
<p><strong>Remote support</strong> means connecting to a user's computer or phone over the internet, so you can see the screen and, with permission, control it. Windows includes a feature called Quick Assist, and many free and paid remote-support tools exist. Whatever tool you use, the process is similar: the user starts or approves the session and shares a short one-time code.</p>

<h3>The safe process</h3>
<ol>
<li><strong>Get a ticket first.</strong> Never connect without a recorded request.</li>
<li><strong>Verify the person.</strong> Make sure you are really speaking to the right user.</li>
<li><strong>Ask for permission.</strong> Say what you will do: "I will connect to see your screen and fix the printer. Is that okay?"</li>
<li><strong>Use the organisation's approved tool only.</strong></li>
<li><strong>Tell the user when you are about to take control,</strong> and ask them to close private windows such as banking and personal messages.</li>
<li><strong>Do only the work in the ticket.</strong> Do not open personal files or emails.</li>
<li><strong>Disconnect and confirm.</strong> End the session, ask the user to test, and record the work.</li>
</ol>

<h3>Phone and chat safety: avoid scams</h3>
<p>Fake "tech support" scammers phone people claiming their computer has a virus and ask them to install a remote tool, then steal money. As a support officer, never ask users to approve remote access for a call they did not request, and teach users this rule: <em>real support never calls you unexpectedly and asks for remote access, passwords or mobile money PINs</em>.</p>

<h3>Good and bad remote practice</h3>
<table>
<thead><tr><th>Good practice</th><th>Bad practice</th></tr></thead>
<tbody>
<tr><td>User starts the session and shares the code</td><td>Connecting while the user is not present</td></tr>
<tr><td>Explaining each action</td><td>Silent clicking and opening folders</td></tr>
<tr><td>Using the company's approved tool</td><td>Installing a random free tool</td></tr>
<tr><td>Ending the session and logging the work</td><td>Leaving the connection open</td></tr>
<tr><td>Asking the user to close private windows</td><td>Looking at personal messages "by accident"</td></tr>
</tbody>
</table>

<h3>Slow connections</h3>
<p>If the connection is poor, reduce the screen quality in the tool, close other downloads, or switch to guiding the user by voice. Remote sessions also use data, so agree before using a user's mobile bundle.</p>

<h3>Scenario</h3>
<p>A branch manager in Arua cannot open a spreadsheet. You phone, verify her, explain, and she shares the session code. You see a banking page open and ask her to close it first. You fix the file association in four minutes, disconnect, and note: "Remote session, with consent, fixed .xlsx association."</p>

<blockquote><p><strong>Quick check:</strong> What must you have before starting a remote session?</p><p>Answer: A ticket and the user's clear permission, because remote access to someone's screen must be recorded and agreed.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A stranger phones a user, says their computer has a virus, and asks them to install a remote tool. What is the right advice?</p><p>Answer: Hang up and do not install anything, because this is a common scam and real support does not make unexpected calls like this.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a short consent script that you would say before every remote session.</li>
<li>List five things you must not do while connected to someone's computer.</li>
<li>Ask a friend to try Windows Quick Assist or another approved tool with you as a practice (you both agree first).</li>
<li>Write the ticket note you would save afterwards.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Remote support needs a ticket, verification and permission.</li>
<li>Use only approved tools and only do the work in the ticket.</li>
<li>Respect privacy: ask users to close private windows and never browse personal files.</li>
<li>Teach users that real support never makes unexpected remote-access calls.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Backups and data recovery basics',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain a good backup plan, help a user restore files, and decide what to do when data seems lost. This matters because hardware can be replaced, but a school's records or a trader's sales book often cannot.</p>

<h3>Why data is lost</h3>
<ul>
<li>A user deletes or overwrites a file by mistake.</li>
<li>A hard disk, flash disk or phone fails or is lost or stolen.</li>
<li>Malware locks or destroys files.</li>
<li>Power surges, floods or fire.</li>
</ul>

<h3>The 3-2-1 idea</h3>
<p>A simple, trusted rule is <strong>3-2-1</strong>: keep <strong>3</strong> copies of important data, on <strong>2</strong> different types of storage, with <strong>1</strong> copy kept somewhere else (off site or in the cloud). One copy on the same computer is not a backup: if the computer fails, both are gone.</p>

<h3>Backup types</h3>
<table>
<thead><tr><th>Type</th><th>What it copies</th><th>Pros and cons</th></tr></thead>
<tbody>
<tr><td>Full</td><td>Everything chosen</td><td>Simple to restore, but slow and uses more space</td></tr>
<tr><td>Incremental</td><td>Only what changed since the last backup</td><td>Fast and small, but restoring needs several parts</td></tr>
<tr><td>Cloud sync or backup</td><td>Copies to online storage</td><td>Off-site and convenient; needs internet and a safe account</td></tr>
<tr><td>External drive</td><td>Copies to a disk you keep</td><td>Cheap and fast; can be lost, stolen or damaged with the computer</td></tr>
</tbody>
</table>
<p>Note that a cloud sync folder can copy a deletion or a ransomware change to every device. A true backup keeps older versions.</p>

<h3>Good backup habits</h3>
<ol>
<li>Decide what matters: documents, databases, photos, accounting files.</li>
<li>Make backups automatic and regular, daily for busy offices.</li>
<li>Keep at least one copy away from the building.</li>
<li>Protect backups with passwords or encryption if they hold private data.</li>
<li><strong>Test a restore</strong> regularly. A backup you have never restored is only a hope.</li>
</ol>

<h3>Recovering lost files</h3>
<ul>
<li><strong>Deleted recently:</strong> check the Recycle Bin first, then right-click the folder and see if <strong>Restore previous versions</strong> or File History is available.</li>
<li><strong>Office file lost before saving:</strong> check Document Recovery or the AutoRecover location.</li>
<li><strong>Cloud files:</strong> many services keep deleted items in a bin or version history for a limited time.</li>
<li><strong>Failing disk:</strong> stop using it. Every minute of use can overwrite what you want to recover. Copy files to another drive or hand it to a recovery specialist.</li>
</ul>
<p>Recovery software can sometimes bring back deleted files, but results are never guaranteed, especially on phones and solid-state drives, and you should never install it onto the same drive you are trying to recover.</p>

<h3>Scenario</h3>
<p>A nursery school in Entebbe stored term fees on one flash disk. It fell in water. There was no other copy. After this you set up a weekly copy to a second drive kept by the head teacher and an online copy of the spreadsheet. Next time, loss means a week of work at most.</p>

<blockquote><p><strong>Quick check:</strong> What does the 3-2-1 rule mean?</p><p>Answer: Three copies, on two kinds of storage, with one copy kept off site, so one disaster cannot destroy all copies.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A disk is making clicking noises and holds important files. What should you do?</p><p>Answer: Stop using it and copy the data off or seek specialist recovery, because continued use may damage it further.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List five files or folders on your computer that you could not replace.</li>
<li>Copy them to a second location using the 3-2-1 idea as far as you can.</li>
<li>Delete a test file and practise restoring it from the Recycle Bin.</li>
<li>Write the day each week you will test that your backup opens.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Follow 3-2-1: three copies, two storage types, one off site.</li>
<li>A sync folder alone is not a full backup.</li>
<li>Test your restores, not just your backups.</li>
<li>Stop using a failing disk to improve recovery chances.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Malware first response',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to recognise the signs of a malware infection and take the right first steps to limit the damage. This matters because the first few minutes decide whether one computer is cleaned or a whole office is locked.</p>

<h3>What malware is</h3>
<p><strong>Malware</strong> is harmful software. Common types are viruses, <strong>ransomware</strong> (locks files and demands payment), <strong>spyware</strong> (watches what you do), <strong>trojans</strong> (pretend to be useful programs) and adware (floods you with adverts). It usually arrives through email attachments, links, pirated software, infected flash disks and fake websites.</p>

<h3>Warning signs</h3>
<ul>
<li>Sudden slowness, pop-ups or new toolbars in the browser.</li>
<li>Files with strange new extensions that will not open, or a ransom note.</li>
<li>The antivirus is switched off and cannot be turned back on.</li>
<li>Friends receive odd messages from the user's account.</li>
<li>Unknown programs, high network use, a webcam light that turns on.</li>
</ul>

<h3>First response: isolate, report, scan, recover</h3>
<ol>
<li><strong>Isolate.</strong> Disconnect the computer from the network: unplug the cable and turn off Wi-Fi. This stops spread to other computers and shared folders. Do not switch off if ransomware is encrypting only if instructed by your policy; most guides say to disconnect first.</li>
<li><strong>Report.</strong> Tell your supervisor or security contact. Log a ticket as high priority. If other computers behave the same way, treat it as critical.</li>
<li><strong>Do not use the infected machine</strong> for banking or email until it is clean, and do not plug in flash disks.</li>
<li><strong>Scan.</strong> Use the installed security software, and run a full scan. Windows includes Microsoft Defender, which can run an offline scan.</li>
<li><strong>Change passwords</strong> from a different, clean device, because the infected machine may have captured them. Enable MFA.</li>
<li><strong>Recover.</strong> If files are damaged, restore from a clean backup. If the machine cannot be trusted, rebuild it from a clean installation.</li>
<li><strong>Find the cause and learn.</strong> How did it arrive? Update the user's awareness and your guide.</li>
</ol>

<h3>Ransomware: what not to do</h3>
<p>Paying a ransom does not guarantee you get your files back and encourages criminals. Do not pay without management and expert advice. Your best defence is regular backups kept separately.</p>

<h3>Do and do not</h3>
<table>
<thead><tr><th>Do</th><th>Do not</th></tr></thead>
<tbody>
<tr><td>Disconnect from the network quickly</td><td>Keep working "to finish the report"</td></tr>
<tr><td>Report to the right person</td><td>Hide it because you are afraid of blame</td></tr>
<tr><td>Change passwords from a clean device</td><td>Change them on the infected computer</td></tr>
<tr><td>Restore from clean backups</td><td>Copy files back without scanning them</td></tr>
<tr><td>Keep notes of what you saw and did</td><td>Delete evidence casually</td></tr>
</tbody>
</table>

<h3>Scenario</h3>
<p>On Monday, a clerk at a savings group in Mukono opens an email attachment called "Invoice.zip". Folders begin to show ".locked" files. She shouts for IT. Joseph pulls the network cable, informs the manager, checks the other computers (all fine), restores the shared folder from Friday's backup, and rebuilds her computer. The loss is two days of work, not the whole group's records.</p>

<blockquote><p><strong>Quick check:</strong> A user reports a ransom note on their screen. What is the first action?</p><p>Answer: Disconnect the computer from the network and report it, because isolation stops the infection spreading to other machines and shared files.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why change passwords from a different device after an infection?</p><p>Answer: The infected computer may be recording what is typed, so a new password entered there could be stolen.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write the seven response steps on a card, in your own words.</li>
<li>Find out how to start a full scan with the security software on your computer.</li>
<li>Draft a short message that an employee should send to IT if they suspect an infection.</li>
<li>List three habits that would help prevent infection in a small office.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Isolate first, then report, scan, change passwords on a clean device and recover.</li>
<li>Clean backups are the best protection against ransomware.</li>
<li>Do not hide incidents; fast reporting limits damage.</li>
<li>Learn from every incident and update advice for users.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What must be in place before you start a remote support session?
- Nothing, speed matters
* A ticket and the user's clear permission
- The user's password
- A change in the user's settings
= Consent and a record protect both the user and you.

Q: During a remote session you notice the user's private messages open. What do you do?
- Read them quickly for context
* Ask the user to close private windows and do only the task in the ticket
- Copy them for later
- Disconnect without telling anyone
= Respecting privacy is part of professional ethics.

Q: A stranger phones someone, says their PC has a virus, and asks them to install a remote tool. What is this most likely?
- Genuine support
* A common scam
- A routine update
- A government service
= Unexpected calls asking for remote access are a classic fraud.

Q: Which setup follows the 3-2-1 backup rule?
- One copy on the same computer
- Two copies on the same disk
* Three copies, two storage types, one kept off site
- One copy in an email draft
= 3-2-1 spreads risk across copies, media and places.

Q: Why test a restore regularly?
- It makes the backup run faster
* A backup you have never restored may not actually work
- It deletes old backups
- It is a legal duty in every country
= Only a successful restore proves that the backup is usable.

Q: A user's external hard drive makes clicking sounds and holds the only copy of the school records. What do you advise?
- Keep using it to copy files over slowly
* Stop using it and copy the data off carefully or ask a recovery specialist
- Shake it gently
- Format it
= More use may cause more damage; a careful copy comes first.

Q: Files on a computer suddenly will not open and a ransom note appears. What is the FIRST step?
- Pay the ransom
- Keep working
* Disconnect it from the network and report it
- Send the note to all staff
= Isolation stops spread; paying is risky and no guarantee.

Q: After a malware infection, from which device should the user change their passwords?
- The infected computer
* A clean, trusted device
- Any device with the infection
- They should not change them
= The infected machine may be recording typed text.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Running the service and your career',
            'summary' => 'Keep assets, write knowledge, use AI safely, work as a team ethically and finish with a simulated help desk.',
            'lessons' => [
                [
                    'title'   => 'Hardware swaps and asset inventory',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to replace common parts and devices safely, and keep an asset inventory that shows what the organisation owns and who has it. This matters because organisations lose money and time when equipment disappears or when nobody knows which laptop belongs to whom.</p>

<h3>Why keep an asset inventory</h3>
<p>An <strong>asset inventory</strong> is a list of every device and licence: computers, phones, printers, routers, screens and software. It helps you plan replacements, find stolen items, check warranties and answer "who has this laptop?" in seconds.</p>

<h3>What to record</h3>
<table>
<thead><tr><th>Field</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Asset tag</td><td>LAF-0042 (a sticker on the device)</td></tr>
<tr><td>Type and model</td><td>Laptop, 15 inch</td></tr>
<tr><td>Serial number</td><td>From the label or system info</td></tr>
<tr><td>Assigned to</td><td>Grace, Accounts</td></tr>
<tr><td>Location</td><td>Head office, room 3</td></tr>
<tr><td>Purchase date and warranty end</td><td>March 2025, March 2027</td></tr>
<tr><td>Status</td><td>In use, in repair, spare, retired</td></tr>
</tbody>
</table>
<p>A spreadsheet is a good start for a small team. Update it every time a device moves, breaks or is replaced.</p>

<h3>Safe hardware swaps</h3>
<ol>
<li><strong>Back up</strong> the user's data first, or confirm the data is on the server or cloud.</li>
<li><strong>Switch off and unplug.</strong> Remove the laptop battery if it is removable.</li>
<li><strong>Discharge static.</strong> Touch an unpainted metal part of the case before touching components. Hold parts by the edges.</li>
<li><strong>Take photos or notes</strong> of cables and positions before removing anything.</li>
<li><strong>Replace one part at a time</strong> and use the correct type (for example the right kind of RAM for that laptop).</li>
<li><strong>Test</strong> by starting the machine and checking the part is detected.</li>
<li><strong>Update the inventory and ticket.</strong></li>
</ol>

<h3>Repair or replace?</h3>
<p>Compare the cost of repair with the cost of a replacement and the age of the device. A battery or SSD upgrade on a good three-year-old laptop may be worth it. A ten-year-old computer needing a new motherboard usually is not. Also consider the lost working time.</p>

<h3>Retiring devices safely</h3>
<p>Before selling, donating or disposing of a computer, make sure data is properly erased, because deleting files or formatting quickly may leave data recoverable. Use a secure wipe or the device's reset option, and remove accounts. Electronic waste should go to a responsible collection point, not an ordinary dustbin.</p>

<h3>Scenario</h3>
<p>A school receives 20 donated laptops. Before use, Irene gives each an asset tag, records the serial number, wipes and reinstalls Windows, and assigns them to classrooms. A year later, one is reported missing; the inventory shows exactly who last signed for it.</p>

<blockquote><p><strong>Quick check:</strong> Name three details an asset inventory should hold for a laptop.</p><p>Answer: For example the asset tag, serial number and the person it is assigned to, because these identify the device and who is responsible for it.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Before replacing a laptop's disk, what must you do first?</p><p>Answer: Back up the user's data, because the replacement may erase or lose what is on the old disk.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Make a spreadsheet with the seven columns from the table.</li>
<li>Add at least five items, such as your own phone, laptop and charger.</li>
<li>Find a serial number on a device label or in Settings, and record it.</li>
<li>Write a three-line rule for how your team will update the list.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>An inventory shows what you own, where it is and who holds it.</li>
<li>Back up, power off and protect against static before opening a device.</li>
<li>Decide whether to repair or replace using cost, age and lost time.</li>
<li>Erase data properly before a device leaves the organisation.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Knowledge-base articles and using AI assistants safely',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a clear knowledge-base article and use an AI assistant to help you troubleshoot without exposing private information. This matters because a good article saves the whole team time, and careless AI use can leak passwords or customer data.</p>

<h3>What a knowledge base is</h3>
<p>A <strong>knowledge base</strong> (KB) is a collection of short articles that explain how to solve common problems. Users can search it themselves (self-service), and the help desk uses it to answer faster and more consistently. Each time you solve a problem you will meet again, you can turn your ticket notes into an article.</p>

<h3>Anatomy of a good article</h3>
<ol>
<li><strong>A clear title</strong> in the words users would search: "Printer shows offline".</li>
<li><strong>Symptoms:</strong> what the user sees.</li>
<li><strong>Cause</strong> (if known), in one line.</li>
<li><strong>Steps</strong> as a numbered list, one action per step.</li>
<li><strong>How to check it worked.</strong></li>
<li><strong>When to contact IT</strong> and what to say.</li>
<li><strong>Author, date and version</strong>, so out-of-date articles are fixed.</li>
</ol>
<pre><code>Title: Printer shows "Offline"
Applies to: Windows 10/11, office printer
Symptoms: Documents wait in the queue; printer says Offline.
Steps:
1. Check the printer is on and the cable or Wi-Fi is connected.
2. Open Settings > Printers &amp; scanners, select the printer.
3. Click Open print queue and cancel stuck jobs.
4. Print a test page.
Still not working? Log a ticket with the printer name and a photo of its screen.
Author: Joseph   Updated: 12 March
</code></pre>
<p>Write in short sentences, avoid jargon, and test the steps on a computer before you publish them.</p>

<h3>Using AI assistants to help troubleshoot</h3>
<p>An AI chat assistant can explain an error message, suggest steps and help draft articles or replies. But it can be confidently wrong, so treat it like a helpful colleague who sometimes guesses.</p>
<table>
<thead><tr><th>Safe use</th><th>Unsafe use</th></tr></thead>
<tbody>
<tr><td>Paste an error message with names and numbers removed</td><td>Paste passwords, keys, customer lists or confidential files</td></tr>
<tr><td>Ask for possible causes, then test one at a time</td><td>Run commands you do not understand</td></tr>
<tr><td>Ask it to rewrite your article more simply</td><td>Publish its text without testing</td></tr>
<tr><td>Check key facts in official documentation</td><td>Assume a made-up setting or command exists</td></tr>
</tbody>
</table>

<h3>Rules for AI in support</h3>
<ol>
<li><strong>Remove private data</strong> first: names, account numbers, IP addresses, passwords.</li>
<li><strong>Understand before you run.</strong> Ask the assistant to explain each command. Avoid anything that deletes, formats or changes security settings.</li>
<li><strong>Verify</strong> with official documentation or a colleague.</li>
<li><strong>Follow the organisation's AI policy.</strong></li>
<li><strong>You are responsible</strong> for the fix, not the tool.</li>
</ol>
<p>Example prompt: "A Windows 11 laptop shows 'Printer offline' and the printer works from other computers. Give me five possible causes in order of likelihood, with a safe test for each." Notice there is no private data and the request asks for tests, not blind commands.</p>

<blockquote><p><strong>Quick check:</strong> Which belongs in a KB article: "Try some stuff" or a numbered list of steps with a check at the end?</p><p>Answer: The numbered steps with a check, because a reader needs exact actions and a way to know they worked.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> An AI assistant suggests a command you do not recognise that "cleans the system". What should you do?</p><p>Answer: Do not run it until you understand it and have checked official documentation, because a wrong command can delete data.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick a problem you have fixed, such as connecting to Wi-Fi.</li>
<li>Write a short KB article using the seven parts.</li>
<li>Ask a friend to follow it with no help and note where they get stuck.</li>
<li>Improve the article, then (if you use an AI tool) ask it to suggest a clearer wording, with no private data pasted.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A KB article has a title, symptoms, steps, a check and when to ask for help.</li>
<li>Test every article before publishing.</li>
<li>Remove private data before using an AI assistant.</li>
<li>Understand and verify AI suggestions; you remain responsible.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Teamwork, ethics, confidentiality and your career',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to work well in a support team, follow professional ethics, protect confidential information and plan your career. This matters because IT staff are trusted with access to everything, and that trust is the most valuable thing you have.</p>

<h3>Working in a team</h3>
<ul>
<li><strong>Levels of support.</strong> Level 1 takes first contact and fixes simple problems. Level 2 handles harder technical problems. Level 3 handles specialist and system-level work. Some places have a different number of levels, but the idea is the same.</li>
<li><strong>Handover.</strong> At the end of a shift, write notes on open tickets so the next person can continue without asking you.</li>
<li><strong>Share knowledge.</strong> Write articles, teach juniors and admit mistakes early.</li>
<li><strong>Respect the SLA</strong> and tell the user in advance if you will miss a time.</li>
</ul>

<h3>Professional ethics</h3>
<p>Support staff can often read files, reset accounts and see screens. With that power come duties.</p>
<table>
<thead><tr><th>Principle</th><th>In practice</th></tr></thead>
<tbody>
<tr><td>Confidentiality</td><td>Never read or share private mail, files or messages unless the job requires it and you are authorised</td></tr>
<tr><td>Honesty</td><td>Admit mistakes and do not pretend to know</td></tr>
<tr><td>Least access</td><td>Use only the access needed for the ticket</td></tr>
<tr><td>Fairness</td><td>Treat every user with the same respect, whatever their rank</td></tr>
<tr><td>Legality</td><td>Use licensed software and follow data-protection law, such as the Uganda Data Protection and Privacy Act</td></tr>
<tr><td>Reporting</td><td>Report suspected misuse or a security incident to the right person</td></tr>
</tbody>
</table>
<p>If a manager asks you to secretly read an employee's messages, do not simply obey or refuse in anger. Ask for the request in writing through the proper process (for example HR or legal approval) and record it.</p>

<h3>Scenario</h3>
<p>While fixing a laptop, Aisha sees a spreadsheet of staff salaries on the desktop. Her curiosity is natural, but she closes it, does not mention it to anyone, and finishes her ticket. A week later she is trusted with access to the finance computer because of how she behaved.</p>

<h3>Career paths</h3>
<p>Support is a springboard. Common routes are:</p>
<ul>
<li><strong>Systems or network administration</strong>, managing servers, users and networks.</li>
<li><strong>Cybersecurity</strong>, protecting systems from attack.</li>
<li><strong>Cloud engineering</strong>, running services on cloud platforms.</li>
<li><strong>IT management or service management</strong>, running a service desk.</li>
<li><strong>Self-employment</strong>, repairing and supporting small businesses.</li>
</ul>

<h3>Entry-level certifications</h3>
<p>Certificates prove your knowledge to employers. General categories include: entry-level IT support certificates from vendor-neutral bodies (such as CompTIA's IT support and A+ style certificates), vendor programmes from Microsoft, Cisco and Google, introductory networking and security certificates, and service-management foundations such as ITIL. Certifications change over time, so check the official site before you pay for an exam. Practical experience, a home lab and a clear CV count just as much.</p>

<blockquote><p><strong>Quick check:</strong> You notice a colleague's private email while fixing their Outlook. What do you do?</p><p>Answer: Do not read or share it and continue only with the task, because confidentiality is a core duty of support staff.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why write handover notes at the end of a shift?</p><p>Answer: So the next person can continue open tickets without delay or repeated questions to the user.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a short personal "code of conduct" with five rules for yourself.</li>
<li>Choose one career path and list three skills you need for it.</li>
<li>Visit the official sites of two certification bodies and write down one entry-level certificate and what it covers.</li>
<li>Make a 6-month learning plan with one goal per month.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Teams use support levels, handover notes and shared knowledge.</li>
<li>Confidentiality, honesty, fairness and legality guide every action.</li>
<li>Support leads to administration, security, cloud, management or self-employment.</li>
<li>Check official sources for certifications; skills and experience matter too.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Capstone: run a simulated help desk with 10 tickets',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will have run a complete simulated help desk: logging, prioritising, solving, escalating and closing ten tickets with proper notes. This matters because the real skill of support is putting everything together under time pressure, and this exercise is portfolio evidence you can show an employer.</p>

<h3>How the simulation works</h3>
<p>You act as the Level 1 officer at "Kampala Community Clinic" with 15 staff. Use a spreadsheet as your ticketing system with these columns: Ticket, Time, User, Problem, Priority, Category, Action taken, Status, Note for user. Work through the ten tickets below in order of priority, not the order they arrive. You can ask a friend to play the user for some of them.</p>

<h3>The ten tickets</h3>
<table>
<thead><tr><th>No.</th><th>Time</th><th>Report</th></tr></thead>
<tbody>
<tr><td>1</td><td>8:00</td><td>Reception: "Internet is down, we cannot register patients." All devices affected.</td></tr>
<tr><td>2</td><td>8:10</td><td>Dr. Ouma: forgot his password, wants it reset by phone.</td></tr>
<tr><td>3</td><td>8:30</td><td>Accounts: printer says "Offline". The other printer works.</td></tr>
<tr><td>4</td><td>9:00</td><td>Nurse Hope: her laptop takes ten minutes to start.</td></tr>
<tr><td>5</td><td>9:15</td><td>Pharmacy: files show a ".locked" extension and a ransom note appears.</td></tr>
<tr><td>6</td><td>10:00</td><td>New nurse starts Monday and needs an account and a laptop.</td></tr>
<tr><td>7</td><td>10:30</td><td>Admin phone has "storage full" and cannot take photos.</td></tr>
<tr><td>8</td><td>11:00</td><td>A caller asks for the manager's password "for an audit".</td></tr>
<tr><td>9</td><td>11:30</td><td>A Word document crashed before saving.</td></tr>
<tr><td>10</td><td>12:00</td><td>A staff member left last Friday and still has system access.</td></tr>
</tbody>
</table>

<h3>Your task for each ticket</h3>
<ol>
<li>Assign a priority (critical, high, medium, low) and give one reason using impact and urgency.</li>
<li>Write the first three questions you would ask the user.</li>
<li>Write the steps you would take, using the troubleshooting method.</li>
<li>Decide whether to escalate, and write the escalation note if so.</li>
<li>Write the closing note for the user in plain words.</li>
</ol>

<h3>Hints to check your thinking</h3>
<ul>
<li>Which tickets involve security (5, 8, 10)? They need fast, careful action.</li>
<li>Which ticket affects the most people (1 and perhaps 5)?</li>
<li>Which ticket needs identity verification (2 and 8)?</li>
<li>Which tickets are about preparation rather than a fault (6)?</li>
</ul>

<h3>Mark yourself</h3>
<table>
<thead><tr><th>Criteria</th><th>Good evidence</th></tr></thead>
<tbody>
<tr><td>Prioritising</td><td>Critical and security tickets handled first, with reasons</td></tr>
<tr><td>Communication</td><td>Polite scripts and plain words in each note</td></tr>
<tr><td>Method</td><td>Ask, reproduce, isolate, fix, verify, document visible</td></tr>
<tr><td>Safety</td><td>Identity checks, no password sharing, disconnecting the infected PC</td></tr>
<tr><td>Records</td><td>Clear notes and an updated asset list</td></tr>
</tbody>
</table>

<blockquote><p><strong>Quick check:</strong> Ticket 5 shows a ransom note while ticket 3 is a printer fault. Which first?</p><p>Answer: Ticket 5, because ransomware can spread and destroy records, while a single printer has a workaround.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What do tickets 2 and 8 have in common?</p><p>Answer: Both involve passwords and could be social engineering, so identity must be verified before any action.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Set up the spreadsheet and enter all ten tickets.</li>
<li>Complete all five task items for every ticket.</li>
<li>Write one knowledge-base article for the ticket you found most common or interesting.</li>
<li>Ask a friend or tutor to review your work using the marking table and note two improvements.</li>
<li>Save the file as your portfolio piece and add one line about it to your CV.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Prioritise by impact and urgency, with security risks treated quickly.</li>
<li>Combine the process, the method and clear communication for every ticket.</li>
<li>Good notes and knowledge-base articles multiply your value to the team.</li>
<li>Your completed simulation is evidence of real skills for employers.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the main purpose of an asset inventory?
- To impress visitors
* To know what equipment and licences exist, where they are and who holds them
- To replace the ticket system
- To slow down staff
= An accurate inventory supports planning, warranty claims and loss tracking.

Q: What should you do before opening a laptop to replace its RAM?
- Nothing, begin immediately
* Back up data, switch off, unplug and discharge static
- Spray the parts with water
- Remove the screen
= Safe preparation protects both data and components.

Q: What is the safest way to prepare a computer for donation?
- Delete the files and empty the Recycle Bin
* Securely erase the drive or reset the device and remove accounts
- Rename the user
- Just switch it off
= Simply deleting or quick formatting can leave recoverable data.

Q: Which is the best title for a KB article?
- Problem 14
* Printer shows "Offline"
- IT notes
- Fix
= Titles should use words that users would search for.

Q: An AI assistant suggests a command you do not understand to "clean up" a server. What should you do?
- Run it, the AI is usually right
* Ask what it does, check official documentation, and test it safely or ask a colleague
- Paste it into the server twice
- Send it to the user to run
= AI can be wrong, and an unknown command can destroy data.

Q: Which information is SAFE to paste into a public AI chat while troubleshooting?
- The administrator password
- A list of customers with phone numbers
* An error message with names and numbers removed
- A copy of a client contract
= Remove all private and confidential data before using an external tool.

Q: While fixing a PC you notice a colleague's private messages. What is professional?
- Read them since you are already there
- Share a screenshot as a joke
* Ignore them, do only the task and keep what you saw confidential
- Tell the manager the contents
= Confidentiality is a core duty of support staff.

Q: Why write handover notes at the end of a shift?
- To fill the log
* So the next person can continue open tickets without asking the user again
- To hide mistakes
- Because tickets disappear overnight
= Good notes keep support smooth across shifts.

Q: In the capstone, a ransom note appears at the pharmacy and a printer is offline at accounts. What is the best order?
- Printer first because it is easy
* Pharmacy first: isolate and report, then handle the printer
- Whichever user shouts loudest
- Ignore both until the end of the day
= A possible ransomware outbreak has far greater impact and urgency.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What is the best description of an IT support officer?
- A person who writes new software for sale
* A person who helps users keep working by solving and recording their technology problems
- A person who only repairs hardware in a workshop
- A person who manages company finances
= Support is about helping users and keeping records, not only repairing machines.

Q: In the help-desk process, what does triage mean?
- Closing old tickets
* Sorting requests by category and urgency
- Replacing a computer
- Sending a survey
= Triage decides what is handled first.

Q: A teacher says the whole lab cannot print, and another teacher wants a new wallpaper. Which has higher priority?
- The wallpaper, it is easier
* The lab printing problem, because more people are affected
- Both are equal
- Neither is a ticket
= Impact and urgency set priority.

Q: A ticket note says "fixed". What is wrong with it?
- Nothing, it is short and clear
* It does not say what was done or how it was checked
- It is too long
- It should be in capital letters
= Notes should let a colleague repeat or understand the fix.

Q: You reply to a high-priority ticket in 20 minutes; the SLA says first response in one hour. What does this show?
- The ticket is closed
* The response target is met
- The resolution target is met
- The SLA does not apply
= First response and resolution are measured separately.

Q: Which phrase shows empathy to an upset user?
- That is not my problem
- You should have been more careful
* I understand this is urgent, let us fix it together
- Calm down
= Acknowledging the stress builds trust.

Q: Which replacement is best for the jargon "Your DNS cache is corrupted"?
- It is a layer 7 error
* The saved list of website addresses on your computer is damaged and I will clear it
- Reinstall everything
- Nothing is wrong
= Plain words describe the effect and the action.

Q: Which is the correct order of the troubleshooting method?
- Fix, ask, document, isolate, reproduce, verify
* Ask, reproduce, isolate, fix, verify, document
- Document, fix, verify, ask
- Verify, ask, isolate, reproduce
= The method moves from understanding the problem to proving and recording the fix.

Q: Why should you change one thing at a time while troubleshooting?
- It is faster to type
* You can tell which change solved the problem and undo a change that did not
- It prevents tickets
- It is required by Windows
= Single changes keep cause and effect clear.

Q: A caller wants a pirated copy of paid software installed. What is the best answer?
- Install it quietly
* Decline politely, explain the legal and virus risks, and offer a licence or free alternative
- Ask the caller to install it
- Tell colleagues about the caller
= A good refusal gives a reason and an alternative.

Q: Which Windows tool shows what is using the most CPU, memory and disk?
- Notepad
- Calculator
* Task Manager
- Paint
= Task Manager shows running programs and their resource use.

Q: A phone says storage is full and the owner wants to keep all photos. What is the best approach?
- Delete the photos
* Back up the photos first, then remove unneeded files and apps with permission
- Factory reset immediately
- Clear data on the camera app
= Protect the owner's data before freeing space.

Q: Which of these is true about Clear cache and Clear data on Android?
- They are the same
* Clear cache is safe; Clear data may delete the app's saved information
- Clear data is always safe
- Neither does anything
= Choose the less destructive option first.

Q: One user cannot connect to Wi-Fi but other devices connect well. What is likely?
- The router is broken for everyone
* A problem on that device or its saved network settings
- The internet provider is down
- The building has no power
= Only one device is affected, so the cause is local to it.

Q: What do ipconfig and ping help you do?
- Edit documents
* Check network addresses and test connectivity
- Install printers
- Format disks
= They are safe network diagnostic tools.

Q: Why is a reset by phone without checking identity dangerous?
- It uses mobile airtime
* An attacker could take over someone's account
- It deletes mailboxes
- It stops the router
= Social engineering targets help desks.

Q: Which MFA description is correct?
- Two passwords written in a file
* Two or more different proofs of identity, such as a password plus a code from a phone
- A shared team password
- A longer user name
= MFA combines different kinds of factors.

Q: When a staff member leaves, what is the best handling of their account?
- Delete it at once with all data
- Leave it active
* Disable it on their last day, recover equipment, and delete only after the retention period
- Share the password with the team
= Disabling stops access and keeps data the organisation may need.

Q: What must be in place before a remote support session?
* A ticket and the user's permission
- Only the user's mobile number
- A long password
- Nothing
= Remote access needs consent and a record.

Q: A person phones a user claiming to be "Microsoft" and asks them to install a remote tool. What should the user do?
- Install it to be helpful
* Hang up and report it, because real support does not make unexpected calls like this
- Share their mobile money PIN
- Pay a small fee
= This is a common scam.

Q: Which setup best follows the 3-2-1 backup rule?
- One copy on the laptop
- Two copies on the same flash disk
* Three copies, on two types of storage, with one kept off site
- Two copies in the same room
= It spreads the risk of loss.

Q: Why test restoring from a backup?
- To delete old backups
* To prove the backup can really be used
- To make the computer faster
- To save electricity
= Untested backups may fail when needed.

Q: A user's computer shows a ransom note. What is the best first step?
- Pay it quickly
- Keep working
* Disconnect from the network and report it
- Reboot repeatedly
= Isolation limits the spread.

Q: Where should passwords be changed after a malware infection?
- On the infected computer only
* From a clean, trusted device
- By email to the whole team
- Passwords never need changing
= The infected machine may capture what is typed.

Q: What is the best way to prepare a laptop for donation?
- Delete the files and empty the Recycle Bin
* Securely erase or reset the device and remove accounts
- Rename the folders
- Remove the stickers
= Simple deletion may leave data recoverable.

Q: A knowledge-base article should include which items? (choose all that apply)
* A clear title users would search for
* Numbered steps
* A way to check the fix worked
- The administrator password
= Passwords never belong in articles; the other items make them usable.

Q: Which of these is a SAFE use of an AI assistant while troubleshooting?
- Pasting the admin password for context
* Pasting an error message with private details removed and verifying its suggestions
- Running an unexplained command on the server
- Publishing its steps untested
= Remove private data, verify, and understand before acting.

Q: You see confidential salary data while repairing a PC. What is professional?
- Tell the staff
* Do only your task and keep what you saw confidential
- Print it
- Copy it to a flash disk
= Support staff must respect confidentiality.

Q: Which is the best example of a good escalation?
- Please help, urgent!
* Printer offline since 8:00; restarted PC and printer, cable checked, no IP address; need network access to the router
- It is broken again
- Forwarded
= It tells the next person what is known and needed.
QUIZ,
];
