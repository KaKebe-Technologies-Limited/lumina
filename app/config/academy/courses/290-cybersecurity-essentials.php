<?php
return [
    'slug'        => 'cybersecurity-essentials',
    'title'       => 'Cybersecurity Essentials for Organisations',
    'category'    => 'security',
    'level'       => 'intermediate',
    'icon'        => 'shield',
    'sort'        => 290,
    'summary'     => 'Learn defensive security for schools, NGOs and small businesses: assess risk, protect accounts, devices and networks, respond to incidents and build a 90-day improvement plan.',
    'description' => <<<'HTML'
<p>Schools, NGOs, clinics and small businesses hold valuable information: learner records, donor lists, beneficiary details, mobile money records and financial files. Attackers know that small organisations often have weak protection, so they are targeted more than most people expect. One stolen password or one infected laptop can stop the work of a whole team.</p>
<p>This course is about <strong>defence only</strong>. You will learn how to understand risk, protect accounts and devices, secure networks, e-mail and cloud storage, train people, respond to incidents and plan for improvement. You will not be taught how to attack real systems, and you must never test the security of any system you do not own or have written permission to test.</p>
<p>The course is practical and low cost. Each lesson ends with an activity you can do for your own organisation, school or community group. By the end you will have a risk register, a security checklist and a 90-day plan.</p>
HTML,
    'outcomes'    => [
        'Explain assets, threats, vulnerabilities and risk in plain words',
        'Build a simple risk register and rank risks by likelihood and impact',
        'Apply the CIA triad (confidentiality, integrity, availability) to everyday decisions',
        'Set up password policies, password managers and multi-factor authentication for a team',
        'Apply least privilege, patching, encryption and basic endpoint protection to devices',
        'Secure Wi-Fi, e-mail, cloud storage and backups using the 3-2-1 rule',
        'Run security awareness training and follow the six steps of incident response',
        'Describe breach duties under data protection law in general terms',
        'Draft a 90-day security improvement plan for a small organisation',
    ],
    'requirements'=> [
        'Completion of the Online Safety and Cybersecurity Basics course (or equal knowledge)',
        'Comfortable use of a computer, e-mail and the internet',
        'An organisation, school or group to use as your practice case (real or imagined)',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'CISA: Cybersecurity resources for small organisations', 'url' => 'https://www.cisa.gov'],
        ['title' => 'UK National Cyber Security Centre: Guidance for small organisations', 'url' => 'https://www.ncsc.gov.uk'],
        ['title' => 'NIST Cybersecurity Framework', 'url' => 'https://www.nist.gov/cyberframework'],
        ['title' => 'Personal Data Protection Office, Uganda', 'url' => 'https://www.pdpo.go.ug'],
        ['title' => 'Have I Been Pwned', 'url' => 'https://haveibeenpwned.com'],
    ],
    'modules' => [
        // ------------------------------------------------------------------ MODULE 1
        [
            'title'   => 'Module 1: Risk and the basics',
            'summary' => 'Learn the language of security and how to decide what to protect first.',
            'lessons' => [
                [
                    'title'   => 'Assets, threats and vulnerabilities',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to list what your organisation needs to protect and describe what could go wrong. This matters because you cannot protect everything equally, and you cannot protect what you have not named.</p>
<h3>Three words to know</h3>
<ul>
<li><strong>Asset:</strong> anything of value to the organisation. Assets are not only computers. They include information, people, reputation and services.</li>
<li><strong>Threat:</strong> something that could cause harm to an asset, such as a thief, a virus, a power surge, a flood or a careless mistake.</li>
<li><strong>Vulnerability:</strong> a weakness that lets a threat succeed, such as a laptop with no password, software that was never updated, or a staff member who has never been trained.</li>
</ul>
<h3>An example from a school</h3>
<table>
<thead><tr><th>Asset</th><th>Threat</th><th>Vulnerability</th></tr></thead>
<tbody>
<tr><td>Learner results spreadsheet</td><td>Accidental deletion, ransomware</td><td>One copy only, on one office computer</td></tr>
<tr><td>School Facebook page</td><td>Account takeover</td><td>Shared password, no two-step verification</td></tr>
<tr><td>Fee payment records</td><td>Theft of the laptop</td><td>Laptop is not locked and not encrypted</td></tr>
<tr><td>Head teacher e-mail</td><td>Phishing</td><td>No training on spotting fake messages</td></tr>
</tbody>
</table>
<h3>Types of assets</h3>
<p>Think in groups so you do not forget anything: <strong>information</strong> (records, photos, contracts), <strong>devices</strong> (laptops, phones, routers), <strong>software and accounts</strong> (e-mail, cloud storage, accounting system), <strong>people</strong> (staff, volunteers, who hold knowledge and access) and <strong>reputation</strong> (the trust donors, parents and communities place in you).</p>
<h3>Threats are not only hackers</h3>
<p>Many incidents are not attacks at all. A power cut can corrupt a file. A volunteer may send a list to the wrong person. A laptop may be left on a bus. A good security plan covers <strong>accidents, failures and attacks</strong>, not just criminals.</p>
<h3>Try it yourself</h3>
<ol>
<li>Take a sheet of paper or open a spreadsheet and make three columns: Asset, Threat, Vulnerability.</li>
<li>List at least eight assets of your organisation, using the five groups above.</li>
<li>For each asset, write one realistic threat and one weakness that exists today.</li>
<li>Circle the three assets that would hurt most if lost. These will start your risk register in the next lessons.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>An asset is anything valuable: information, devices, accounts, people and reputation.</li>
<li>A threat can cause harm; a vulnerability is the weakness that lets it happen.</li>
<li>Accidents and failures are threats just as much as criminals are.</li>
<li>Naming your assets is the first step of every security plan.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Understanding risk in plain words',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to judge how serious a risk is and decide what to deal with first. This matters because a small organisation has limited money and time, so it must spend them where they reduce the most harm.</p>
<h3>What is risk?</h3>
<p><strong>Risk</strong> is the chance that a threat uses a vulnerability to harm an asset, combined with how bad the harm would be. Two questions measure it:</p>
<ol>
<li><strong>Likelihood:</strong> how likely is it to happen in the next year? (Low, medium or high)</li>
<li><strong>Impact:</strong> how bad would it be if it happened? (Low, medium or high)</li>
</ol>
<p>A simple way to score is to give each answer a number (low 1, medium 2, high 3) and multiply. A risk scoring 9 is urgent. A risk scoring 1 or 2 can wait.</p>
<h3>A simple risk assessment, step by step</h3>
<ol>
<li>List your assets (from the last lesson).</li>
<li>For each asset write the main things that could go wrong.</li>
<li>Score likelihood and impact.</li>
<li>Multiply and sort from highest to lowest.</li>
<li>Decide what to do about each of the top risks.</li>
</ol>
<h3>Four ways to treat a risk</h3>
<table>
<thead><tr><th>Choice</th><th>Meaning</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Reduce</td><td>Take action so it is less likely or less harmful</td><td>Switch on two-step verification</td></tr>
<tr><td>Avoid</td><td>Stop the risky activity</td><td>Stop keeping learner ID photos on personal phones</td></tr>
<tr><td>Transfer</td><td>Share the risk with someone else</td><td>Buy insurance for equipment, or use a trusted cloud provider</td></tr>
<tr><td>Accept</td><td>Decide the risk is small and live with it</td><td>An old printer with no sensitive data may fail</td></tr>
</tbody>
</table>
<h3>Be honest about uncertainty</h3>
<p>Scores are judgements, not science. Ask two or three colleagues to score the same risk and discuss differences. The talk is often more valuable than the number. Review the scores each year, because new tools, staff and threats change them.</p>
<h3>Try it yourself</h3>
<ol>
<li>Pick the five most valuable assets from your list.</li>
<li>For each, write the most worrying thing that could happen.</li>
<li>Score likelihood and impact from 1 to 3 and multiply.</li>
<li>Choose reduce, avoid, transfer or accept for the top two, and write one practical action for each.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Risk combines how likely something is with how bad it would be.</li>
<li>Scoring and sorting shows what to fix first.</li>
<li>You can reduce, avoid, transfer or accept a risk.</li>
<li>Review risks regularly, because things change.</li>
</ul>
<h3>Common mistakes</h3>
<p>Do not spend all your effort on exciting but unlikely threats while ignoring boring likely ones, such as weak passwords and missing backups.</p>
HTML,
                ],
                [
                    'title'   => 'The CIA triad and security policies',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use the CIA triad to describe what you are protecting and write a short security policy people can follow. This matters because clear goals and clear rules turn good intentions into daily habits.</p>
<h3>The CIA triad</h3>
<p>The letters stand for three goals of information security. (This CIA has nothing to do with any spy agency.)</p>
<table>
<thead><tr><th>Goal</th><th>Meaning</th><th>Example of failure</th></tr></thead>
<tbody>
<tr><td>Confidentiality</td><td>Only the right people can see the information</td><td>A list of beneficiaries is sent to the wrong group chat</td></tr>
<tr><td>Integrity</td><td>The information is accurate and has not been changed by mistake or by an attacker</td><td>Someone alters bank details on an invoice</td></tr>
<tr><td>Availability</td><td>The information and systems work when needed</td><td>Ransomware locks the files on the day reports are due</td></tr>
</tbody>
</table>
<p>For each asset ask three questions: who may see it, who may change it, and how long can we manage without it? The answers show which goal matters most. A donor list needs strong confidentiality. A payroll file needs integrity. An online classroom needs availability.</p>
<h3>What is a security policy?</h3>
<p>A <strong>policy</strong> is a short written set of rules that says what people must and must not do. It is not a legal book. For a small organisation, one to three pages is enough. People follow rules they can understand and remember.</p>
<h3>What a small policy contains</h3>
<ul>
<li><strong>Purpose and scope:</strong> why it exists and who it covers (staff, volunteers, board members).</li>
<li><strong>Accounts:</strong> unique passwords, a password manager, multi-factor authentication, no sharing of logins.</li>
<li><strong>Devices:</strong> screen locks, updates, encryption, rules for personal phones.</li>
<li><strong>Data:</strong> what is confidential, where it may be stored, how it is shared.</li>
<li><strong>Reporting:</strong> who to tell and how quickly if something looks wrong, with no blame for honest reports.</li>
<li><strong>Consequences and review:</strong> what happens if rules are ignored and when the policy will be reviewed.</li>
</ul>
<h3>Making it work</h3>
<p>Write in plain language, use "you must" for essentials, explain the reason for each rule, and have the director or board approve it so it carries weight. Introduce it in a short meeting and ask each person to sign that they have read it.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose one asset and write which of confidentiality, integrity or availability matters most, and why.</li>
<li>Draft a one-page policy using the six headings above.</li>
<li>Keep each rule to one sentence.</li>
<li>Ask a colleague to read it and tell you which rule is unclear.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The CIA triad covers confidentiality, integrity and availability.</li>
<li>Different assets need different emphasis on the three goals.</li>
<li>A good policy is short, clear and explains the reasons.</li>
<li>Rules only work when leaders approve them and staff understand them.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Building a simple risk register',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will have a working risk register: a single table that tracks your main risks and what you are doing about them. This matters because it keeps decisions visible, helps you report to your board or donors, and stops risks from being forgotten.</p>
<h3>What goes in a risk register</h3>
<p>A risk register can be a spreadsheet with one row per risk. Useful columns are:</p>
<table>
<thead><tr><th>Column</th><th>What to write</th></tr></thead>
<tbody>
<tr><td>ID</td><td>R1, R2, R3 and so on</td></tr>
<tr><td>Asset</td><td>What is at risk</td></tr>
<tr><td>Risk description</td><td>One sentence: what could happen and why</td></tr>
<tr><td>Likelihood and impact</td><td>Scores from 1 to 3</td></tr>
<tr><td>Score</td><td>Likelihood multiplied by impact</td></tr>
<tr><td>Action</td><td>What you will do to reduce it</td></tr>
<tr><td>Owner</td><td>One named person responsible</td></tr>
<tr><td>Due date and status</td><td>When, and whether it is open or done</td></tr>
</tbody>
</table>
<h3>A sample entry</h3>
<pre><code>R1 | Finance laptop | Laptop stolen, bank details exposed | L2 x I3 = 6
Action: encrypt disk, set strong sign-in, keep in locked cupboard
Owner: Office manager | Due: end of next month | Status: Open</code></pre>
<h3>Writing good risk descriptions</h3>
<p>A good description says the cause, the event and the effect. "Staff reuse one password for all accounts, so one breach could expose our e-mail and cloud files" is clearer than "weak passwords". Specific descriptions lead to specific actions.</p>
<h3>Keeping it alive</h3>
<ol>
<li>Sort by score so the highest risks are on top.</li>
<li>Give every action one owner, because a task owned by everyone is owned by no one.</li>
<li>Review the register every three months in a short meeting.</li>
<li>Add new risks when you buy a new system, change a process or hear of an incident elsewhere.</li>
<li>Re-score after an action is finished, to show the improvement.</li>
</ol>
<h3>Keep the register itself safe</h3>
<p>The register describes your weak points, so treat it as confidential. Store it where only managers can open it.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open a spreadsheet and create the columns above.</li>
<li>Enter your top five risks from the previous lesson.</li>
<li>Give each one an owner, an action and a due date.</li>
<li>Save it with a clear name, add a password to the file, and set a calendar reminder to review it in three months.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A risk register is a table of risks, scores, actions, owners and dates.</li>
<li>Specific descriptions lead to specific actions.</li>
<li>Review regularly and update as things change.</li>
<li>The register is sensitive, so protect it.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A school keeps all learner results on one office computer with no copy. In security terms, "only one copy" is an example of a:
- Threat
* Vulnerability
- Asset
- Policy
= A vulnerability is a weakness. The results are the asset, and something like a failed disk or ransomware is the threat.

Q: Which of these are threats and not only attacks by criminals? (choose all that apply)
* A power surge that damages a computer
* A volunteer sending a file to the wrong person
* A flood in the office
- A strong, unique password
= Accidents and failures count as threats. A strong password is a protection, not a threat.

Q: A risk has likelihood 2 and impact 3 on a scale of 1 to 3. What is its score, and what does it suggest?
- 5, it can be ignored
- 3, it is low priority
* 6, it deserves action soon
- 9, it must be accepted
= Multiplying gives 6, which is fairly high. Higher scores should be treated before lower ones.

Q: An NGO stops keeping scanned ID cards on personal phones. Which way of treating the risk is this?
- Accept
- Transfer
* Avoid
- Reduce
= They stopped the risky activity completely. That is avoiding the risk.

Q: Someone changes the bank details on a supplier invoice before it is paid. Which part of the CIA triad failed?
- Confidentiality
* Integrity
- Availability
- Authorisation
= Integrity means information stays accurate and unaltered. Here the data was changed.

Q: Which is the best risk description?
- Bad passwords
* Staff reuse one password everywhere, so one breach could expose e-mail and cloud files
- Hackers
- The internet is dangerous
= A good description says the cause, the event and the effect, which makes the action clear.

Q: Why should each action in a risk register have one named owner?
- It looks more professional
* A task owned by everyone is often done by no one
- The owner is blamed if anything happens
- It is a legal requirement everywhere
= A single owner is accountable and will follow up the action.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 2
        [
            'title'   => 'Module 2: Protecting accounts and devices',
            'summary' => 'Control who can log in, what they can reach, and how laptops and phones are kept safe.',
            'lessons' => [
                [
                    'title'   => 'Identity, access and least privilege',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to decide who in your organisation should have access to what, and set that up safely. This matters because many serious incidents happen when someone has access they never needed.</p>
<h3>Identity and access</h3>
<p><strong>Identity</strong> means proving who you are (a username with a password and a second check). <strong>Access</strong> means what that identity is allowed to do. Good security needs both: knowing who is logging in, and limiting what they can reach.</p>
<h3>The principle of least privilege</h3>
<p><strong>Least privilege</strong> means every person gets only the access they need to do their job, and no more. An accounts assistant needs the finance folder but not the learners' health records. A volunteer who posts on social media needs to post, not to delete the page or see its settings.</p>
<h3>Practical rules</h3>
<ul>
<li><strong>One person, one account.</strong> Do not share logins. Shared accounts make it impossible to know who did what, and impossible to remove one person safely.</li>
<li><strong>Separate admin accounts.</strong> The person who manages systems should use a normal account for daily work and an admin account only when needed.</li>
<li><strong>Use groups and roles.</strong> Give access to folders by role (for example Finance, Programmes) instead of person by person.</li>
<li><strong>Use roles on social pages.</strong> Many platforms let you add people as editors without giving them full ownership. Keep at least two trusted owners so you are never locked out.</li>
</ul>
<h3>Joiners, movers and leavers</h3>
<table>
<thead><tr><th>Event</th><th>What to do</th></tr></thead>
<tbody>
<tr><td>Joiner (new person)</td><td>Create their own accounts with only the access needed, and give a short induction</td></tr>
<tr><td>Mover (new role)</td><td>Add the new access and remove the old access that is no longer needed</td></tr>
<tr><td>Leaver (leaves the organisation)</td><td>Disable accounts on the last day, change shared passwords, recover devices and remove them from groups and chats</td></tr>
</tbody>
</table>
<h3>Review access regularly</h3>
<p>Every six months, print or export a list of who can open each important folder, system and page. Ask the owner of each: "Does everyone on this list still need it?" Remove anyone who does not.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose your most sensitive folder or system (for example finance or beneficiary data).</li>
<li>Write down every person who can open it.</li>
<li>Beside each name write the reason they need access.</li>
<li>Remove or reduce access for anyone without a reason, and write a short leaver checklist for your organisation.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Identity proves who you are; access decides what you can do.</li>
<li>Least privilege gives each person only what the job needs.</li>
<li>Avoid shared accounts and keep admin rights separate.</li>
<li>Handle joiners, movers and leavers carefully and review access twice a year.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Passwords, password managers and MFA',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to set a sensible password rule for a team and switch on multi-factor authentication. This matters because stolen or guessed passwords are one of the most common ways attackers get in.</p>
<h3>Modern password advice</h3>
<p>Older rules demanded odd symbols and changes every month. This made people choose weak, predictable passwords. Current advice from security bodies is simpler:</p>
<ul>
<li>Use <strong>long</strong> passwords or passphrases of at least 12 to 16 characters, for example four or five unrelated words.</li>
<li>Use a <strong>different</strong> password for every account, so one breach does not open the others.</li>
<li>Do not use personal facts such as names, birthdays or phone numbers.</li>
<li>Change a password when you suspect it was exposed, not on a fixed timetable.</li>
<li>Block very common passwords such as "password123" if the system allows it.</li>
</ul>
<h3>Password managers</h3>
<p>Nobody can remember dozens of long, unique passwords. A <strong>password manager</strong> is an app that creates, stores and fills them in for you. You remember one strong master passphrase, and the manager protects the rest in an encrypted vault. Many reputable managers have free personal plans, and browsers also have built-in managers. Choose one from a well-known provider, protect the vault with a long master passphrase and multi-factor authentication, and keep the recovery information safe.</p>
<h3>Multi-factor authentication (MFA)</h3>
<p><strong>MFA</strong> asks for two or more proofs: something you <em>know</em> (password), something you <em>have</em> (phone or key) and something you <em>are</em> (fingerprint). Even if a thief learns your password, they cannot log in without the second proof.</p>
<table>
<thead><tr><th>Method</th><th>Strength</th><th>Note</th></tr></thead>
<tbody>
<tr><td>Authenticator app codes</td><td>Good</td><td>Works without network; better than SMS</td></tr>
<tr><td>Security key or passkey</td><td>Very good</td><td>Hardest to phish</td></tr>
<tr><td>SMS codes</td><td>Fair</td><td>Better than nothing, but SIM swap fraud is possible</td></tr>
</tbody>
</table>
<h3>Where to start</h3>
<p>Turn on MFA first for e-mail (it can reset every other account), then cloud storage, finance and banking tools, social media pages and admin accounts. Keep backup codes printed and locked away.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open the security settings of your main e-mail account.</li>
<li>Switch on two-step verification using an authenticator app if available.</li>
<li>Save the backup codes in a safe place that is not on the same phone.</li>
<li>Write a three-line password rule for your organisation using the advice above.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Long, unique passwords or passphrases beat complex short ones.</li>
<li>A password manager makes unique passwords practical.</li>
<li>MFA stops most stolen-password attacks; start with e-mail.</li>
<li>Do not force frequent changes without a reason.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Securing laptops: updates, protection and encryption',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to harden a laptop or desktop for office use. This matters because the computer is where documents, passwords and e-mail meet, so one weak computer can expose everything.</p>
<h3>Endpoint protection</h3>
<p>An <strong>endpoint</strong> is any device that connects to your network: laptops, desktops, tablets and phones. Protect each with:</p>
<ul>
<li><strong>Built-in security tools.</strong> Modern Windows includes Microsoft Defender, which is turned on by default and is suitable for many small organisations. Check that it is active and not turned off. macOS and Linux have their own protections.</li>
<li><strong>A screen lock</strong> that starts after a short idle time. On Windows press the Windows key and L (on a Mac, Control, Command and Q) when stepping away.</li>
<li><strong>A standard user account</strong> for daily work, not an administrator account.</li>
</ul>
<h3>Patching</h3>
<p>A <strong>patch</strong> or update fixes security holes that attackers already know about. Delaying updates leaves doors open. Turn on automatic updates for the operating system, browser and office software. Retire machines that no longer receive updates, such as unsupported old Windows versions, because they cannot be made safe.</p>
<h3>Encryption of disks</h3>
<p><strong>Encryption</strong> scrambles data so that it is unreadable without the key. If an encrypted laptop is stolen, the thief gets a useless device rather than your files. On Windows, look for <strong>Device encryption</strong> or <strong>BitLocker</strong> in Settings (availability depends on the edition). On a Mac, turn on <strong>FileVault</strong>. Store the recovery key safely, away from the laptop, or you may lock yourself out.</p>
<h3>A simple hardening checklist</h3>
<table>
<thead><tr><th>Item</th><th>Done?</th></tr></thead>
<tbody>
<tr><td>Automatic updates on</td><td></td></tr>
<tr><td>Security tool active and up to date</td><td></td></tr>
<tr><td>Disk encryption on, recovery key stored</td><td></td></tr>
<tr><td>Screen lock after 5 minutes or less</td><td></td></tr>
<tr><td>Standard user for daily work</td><td></td></tr>
<tr><td>Unneeded programs removed</td><td></td></tr>
<tr><td>Backup working</td><td></td></tr>
</tbody>
</table>
<h3>Software and downloads</h3>
<p>Install software only from official sources. Pirated programs and "cracks" often carry malware. Limit who can install new programs, and keep a short list of approved tools.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open Settings and find the update page. Check that automatic updates are on and that no important update is waiting.</li>
<li>Open the Windows Security app (or your computer's equivalent) and confirm protection is active.</li>
<li>Check whether disk encryption is on. If yes, note where the recovery key is saved.</li>
<li>Complete the checklist for one device and fix the items missing.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Keep security tools active and the screen locked.</li>
<li>Patch quickly and retire unsupported machines.</li>
<li>Encrypt disks and store the recovery key safely.</li>
<li>Use standard accounts and install only trusted software.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Mobile devices and BYOD',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to set sensible rules for staff phones and for personal devices used at work. This matters because in many organisations, e-mail, WhatsApp, mobile money and documents live on phones that can easily be lost or stolen.</p>
<h3>What is BYOD?</h3>
<p><strong>BYOD</strong> means "bring your own device": staff or volunteers use their personal phones or laptops for work. It saves money and is common, but it mixes personal and organisational data on a device you do not control.</p>
<h3>Basic rules for every phone that touches work data</h3>
<ol>
<li><strong>Screen lock</strong> with a PIN of six digits or more, a pattern that is hard to guess, or a fingerprint or face unlock. Do not use 1234 or a birth year.</li>
<li><strong>Encryption.</strong> Modern iPhones encrypt data when a passcode is set. Most recent Android phones encrypt by default, and you can check in the Security settings.</li>
<li><strong>Updates.</strong> Install system and app updates promptly. An old phone with no updates should not hold sensitive data.</li>
<li><strong>Find my device and remote wipe.</strong> Turn on the built-in tools (Find My on iPhone, Find Hub or Find My Device on Android) so a lost phone can be located or erased.</li>
<li><strong>Trusted apps only.</strong> Install from the official app store and check permissions. A torch app should not need your contacts.</li>
<li><strong>Beware of public charging and open Wi-Fi</strong> for sensitive work. Use your own charger and mobile data when possible.</li>
</ol>
<h3>Separating work and personal</h3>
<p>Where possible use separate work profiles or work apps. Keep organisation files in the official cloud storage app rather than saving copies in the phone gallery or chat apps. Never store lists of beneficiaries, especially children, in personal chat groups.</p>
<h3>A BYOD agreement</h3>
<p>A short written agreement should say what is allowed, what protections are required, what happens if the phone is lost (report within one day), and what happens to work data when the person leaves. Be fair: remote wipe on a personal phone should be limited to work data when the tools allow it, and staff should know this beforehand.</p>
<h3>SIM and mobile money</h3>
<p>Protect SIM cards with a PIN, register them correctly in your own name, and report a lost SIM immediately to your network. Never share mobile money PINs or one-time codes with anyone, including people claiming to be from the company.</p>
<h3>Try it yourself</h3>
<ol>
<li>On your phone, check the screen lock, the last update date and whether Find My Device is on.</li>
<li>Look at the permissions of three apps and remove any that are not needed.</li>
<li>Check whether any work files are saved only on the phone, and move them to official cloud storage.</li>
<li>Draft five rules for a mobile device policy for your organisation.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>BYOD saves money but needs clear rules.</li>
<li>Lock, encrypt, update and enable remote tracking on every phone.</li>
<li>Keep work data in approved apps, not in personal chats.</li>
<li>Report a lost device straight away.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does the principle of least privilege mean?
- Everyone shares one account to save money
* Each person gets only the access needed for their job
- Only the director may use computers
- Access is given to everyone and removed later
= Limiting access reduces the harm from mistakes, stolen accounts and misuse.

Q: When a staff member leaves, what should happen? (choose all that apply)
* Disable their accounts on the last day
* Recover devices and change any shared passwords
* Remove them from groups and chat channels
- Leave the accounts open in case they return
= Quick removal of access prevents misuse. Leaving accounts open creates a risk.

Q: Which password is the best choice for a staff member who uses a password manager?
- Lumina2024!
- The name of a child plus a birth year
* A long random passphrase unique to that account
- The same strong password used on all accounts
= Length and uniqueness matter most. A reused password exposes every account if one is breached.

Q: Which method of MFA is generally strongest against phishing?
- A code sent by SMS
- A secret question
- A longer password
* A security key or passkey
= Keys and passkeys are bound to the real site so they are hard to trick. SMS is better than nothing but weaker.

Q: A laptop with disk encryption is stolen. What is the main benefit?
- The thief cannot turn on the laptop
* The files stay unreadable without the key
- The laptop reports the thief to the police
- The files are automatically backed up
= Encryption protects the data itself. The laptop may still be powered on, but the content is scrambled.

Q: Why are software updates important for security?
- They change the design of the screen
- They always make the computer faster
* They fix known weaknesses that attackers use
- They remove the need for passwords
= Patches close known holes, so delaying them leaves doors open.

Q: A volunteer uses a personal phone for organisation e-mail. Which is the best rule?
- No rules, it is their phone
- Share the e-mail password in a group so others can help
* Require a screen lock, updates and the ability to wipe work data if lost
- Forward all e-mails to a free personal account
= BYOD needs basic protections and a plan for loss. The other choices increase risk.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 3
        [
            'title'   => 'Module 3: Network and cloud security',
            'summary' => 'Secure the network, e-mail and cloud storage, and make sure you can recover from loss.',
            'lessons' => [
                [
                    'title'   => 'Firewalls, Wi-Fi and VPNs',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to check the basic security of your office or school network. This matters because anyone who joins your network may be able to see or reach your computers.</p>
<h3>Firewalls</h3>
<p>A <strong>firewall</strong> is a filter between your network and the internet. It allows wanted traffic and blocks unwanted traffic. Most routers include a basic firewall, and computers have a software firewall (Windows Defender Firewall is on by default). Keep both turned on. Do not open ports or forward traffic to office computers unless a trained person has a clear need.</p>
<h3>Securing Wi-Fi</h3>
<ol>
<li><strong>Change the router admin password</strong> from the default printed on the sticker. Attackers know default passwords for popular models.</li>
<li><strong>Use WPA2 or WPA3</strong> encryption. Never use "open" Wi-Fi or the old WEP for your organisation.</li>
<li><strong>Use a long Wi-Fi passphrase</strong> and change it when someone leaves or if it was shared widely.</li>
<li><strong>Update the router.</strong> Check the maker's page for firmware updates, because routers are computers too.</li>
<li><strong>Turn off WPS</strong> if you do not need it, as it can weaken protection.</li>
<li><strong>Create a guest network</strong> for visitors, learners and personal phones, separate from the staff network.</li>
</ol>
<h3>The idea of segmentation</h3>
<p><strong>Segmentation</strong> means splitting the network into parts so that a problem in one part does not spread to others. A school might keep the admin computers, the learners' computer lab and the guest Wi-Fi on separate networks. If an infected student laptop joins the guest Wi-Fi, it cannot reach the finance computer. Many modern routers support guest networks and some support separate VLANs, which is a more advanced form of the same idea.</p>
<h3>VPNs</h3>
<p>A <strong>VPN</strong> (virtual private network) creates an encrypted tunnel between your device and another network. Organisations use a VPN so staff working remotely can safely reach internal systems. A consumer VPN service hides your traffic from the local Wi-Fi owner but shifts trust to the VPN company, so choose a reputable provider and do not assume it makes you anonymous or protects against phishing or malware.</p>
<h3>Public Wi-Fi</h3>
<p>Treat cafe and airport Wi-Fi as untrusted. Use websites that start with https, avoid banking on them, and prefer mobile data or a VPN for sensitive work.</p>
<h3>Try it yourself</h3>
<ol>
<li>Sign in to your router's settings page (with permission, on your own or your organisation's router).</li>
<li>Check that the admin password is not the default, and that Wi-Fi uses WPA2 or WPA3.</li>
<li>Check whether a guest network is available and switch it on.</li>
<li>Write down the router model and the date of its last update.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Keep the router and computer firewalls turned on.</li>
<li>Change default passwords, use WPA2 or WPA3 and a guest network.</li>
<li>Segmentation limits the spread of a problem.</li>
<li>A VPN protects traffic in transit but is not a complete shield.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Safe e-mail and cloud storage',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to configure e-mail and cloud storage so that confidential files are shared safely. This matters because most organisations now keep their work in e-mail and online folders, and mistakes in sharing settings are a leading cause of data leaks.</p>
<h3>Safer e-mail</h3>
<ul>
<li>Use an organisation domain and a business-grade provider (for example a school or NGO plan from a major provider), not a mix of personal accounts.</li>
<li>Turn on MFA for every mailbox.</li>
<li>Switch on spam and phishing filters, and report suspicious e-mails rather than just deleting them.</li>
<li>Check before sending: right recipient? right attachment? Use "Bcc" for large lists so addresses stay private.</li>
<li>Be careful with auto-forwarding rules. Attackers who break in often add a hidden rule that copies every e-mail to them. Review the rules in settings from time to time.</li>
<li>Do not send passwords by e-mail. Share them in person or through a password manager's sharing feature.</li>
</ul>
<h3>E-mail authentication in simple words</h3>
<p>Three DNS settings called <strong>SPF, DKIM and DMARC</strong> help receivers confirm that e-mail claiming to come from your domain really does. Your e-mail provider or web host can usually guide you through them. They make it harder for criminals to pretend to be you.</p>
<h3>Cloud storage settings</h3>
<p>Cloud storage (for example shared drives from major providers) lets teams work together. Review these settings:</p>
<table>
<thead><tr><th>Setting</th><th>Safer choice</th></tr></thead>
<tbody>
<tr><td>Link sharing</td><td>"Restricted" to named people, not "anyone with the link", for sensitive files</td></tr>
<tr><td>Permission level</td><td>Viewer or commenter by default; editor only when needed</td></tr>
<tr><td>Expiry</td><td>Set an end date for external sharing when available</td></tr>
<tr><td>Ownership</td><td>Keep important files in a shared organisation space, not a single person's personal drive</td></tr>
<tr><td>Sign-in</td><td>MFA on every account</td></tr>
</tbody>
</table>
<h3>Sensitive data</h3>
<p>Do not put highly sensitive items, such as scans of children's documents or health records, into general folders. Create a restricted folder with a short list of authorised people, and consider whether you need to keep the data at all.</p>
<h3>Choosing a provider</h3>
<p>Prefer well-known providers that publish clear privacy and security information, support MFA and let you export your data. Read the terms about where data is stored and who owns it.</p>
<h3>Try it yourself</h3>
<ol>
<li>In your cloud storage, find a file shared with "anyone with the link" and change it to restricted if it is sensitive.</li>
<li>Review the sharing list of your most important folder and remove people who no longer need it.</li>
<li>In your e-mail settings, check forwarding and filter rules for anything you do not recognise.</li>
<li>Write a rule for your organisation about how passwords are shared.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Protect mailboxes with MFA and filters, and check hidden forwarding rules.</li>
<li>Prefer restricted sharing and the lowest permission that works.</li>
<li>Keep sensitive data in restricted folders, and keep less of it.</li>
<li>Choose providers with clear security and privacy information.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Backups and recovery that really work',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to design a backup plan for a small organisation and test it. This matters because backups are your last defence against ransomware, theft, fire, floods and plain accidents. A backup you have never tested is only a hope.</p>
<h3>The 3-2-1 rule</h3>
<p>A trusted way to remember a good backup plan is <strong>3-2-1</strong>:</p>
<ul>
<li><strong>3</strong> copies of your important data (the original plus two backups),</li>
<li><strong>2</strong> different kinds of storage (for example an external drive and cloud storage),</li>
<li><strong>1</strong> copy kept somewhere else (off-site, or in the cloud), so a fire or theft at the office cannot take all copies.</li>
</ul>
<p>A modern addition is to keep one copy <strong>offline or unchangeable</strong>, because ransomware can encrypt backup drives that stay connected to the computer.</p>
<h3>Syncing is not backing up</h3>
<p>Folder syncing tools copy changes immediately, including deletions and encrypted files. If a file is deleted or locked by ransomware, the synced copy is damaged too. A true backup keeps older versions you can go back to. Check whether your cloud service offers version history and how long it keeps old versions.</p>
<h3>Planning your backups</h3>
<ol>
<li><strong>List what must be backed up:</strong> finance files, records, databases, website, key documents and photos.</li>
<li><strong>Decide how often.</strong> Ask: how much work could we afford to lose? If the answer is one day, back up daily.</li>
<li><strong>Automate.</strong> Use scheduled backups. Manual backups get forgotten.</li>
<li><strong>Encrypt backups</strong> that leave the building, and store the key safely.</li>
<li><strong>Assign an owner</strong> and check the backup report weekly.</li>
</ol>
<h3>Two numbers to agree</h3>
<table>
<thead><tr><th>Term</th><th>Meaning</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Recovery point objective (RPO)</td><td>How much recent data you can afford to lose</td><td>One day of work</td></tr>
<tr><td>Recovery time objective (RTO)</td><td>How long you can be without the system</td><td>Back in two days</td></tr>
</tbody>
</table>
<h3>Test your recovery</h3>
<p>At least twice a year, restore a few files and one full folder to a different computer. Check that files open and are complete, and note how long it took. Write the restore steps on paper, because if the computer fails you cannot read them from it.</p>
<h3>Try it yourself</h3>
<ol>
<li>List your five most important sets of data and where each is stored today.</li>
<li>Check whether each follows the 3-2-1 rule. Mark the gaps.</li>
<li>Set up one automatic backup, such as an external drive or cloud backup.</li>
<li>Restore one file from the backup to prove it works, and put a reminder in your calendar for the next test.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Follow 3-2-1: three copies, two storage types, one off-site.</li>
<li>Keep one copy offline or unchangeable against ransomware.</li>
<li>Syncing is not a backup because it copies mistakes.</li>
<li>Test restores and write down the steps.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the first thing to do with a new Wi-Fi router?
- Turn off the firewall to improve speed
- Share the default password with everyone
* Change the default admin password and use WPA2 or WPA3
- Use open Wi-Fi so visitors can join easily
= Default passwords are widely known. WPA2 or WPA3 encrypts the connection.

Q: Why have a separate guest Wi-Fi network?
* A problem on a visitor's device cannot easily reach your office computers
- It makes the internet faster for staff
- It stops phishing e-mails
- It removes the need for passwords
= Segmentation keeps untrusted devices apart from sensitive systems.

Q: Which statements about VPNs are true? (choose all that apply)
* A VPN encrypts traffic between your device and the VPN endpoint
* A consumer VPN moves trust to the VPN company
- A VPN stops you from ever being phished
- A VPN makes you completely anonymous
= A VPN is useful but not magic. It does not stop malware or phishing.

Q: An attacker who breaks into a mailbox often adds a hidden rule that does what?
- Deletes the spam folder
- Updates the password
* Forwards copies of e-mails to the attacker
- Turns on MFA
= Checking forwarding rules can reveal a hidden compromise.

Q: For a confidential file in cloud storage, which sharing setting is best?
- Anyone with the link can edit
- Anyone on the internet can view
- Public listing in search
* Restricted to named people with the lowest permission needed
= Restricted access with least privilege reduces leaks.

Q: What does the 3-2-1 backup rule say?
- Three passwords, two locations, one device
* Three copies, two kinds of storage, one copy off-site
- Three backups a day, two a week, one a month
- Three people, two keys, one safe
= The rule helps ensure no single event destroys every copy.

Q: Why is folder syncing not the same as a backup?
- Syncing is always slower
* Syncing copies deletions and damage immediately, so you may lose the good version
- Syncing cannot use the cloud
- Syncing needs a special licence
= A backup keeps older versions so you can go back before the damage.

Q: What is the best proof that your backup works?
- The backup software shows a green tick
- The drive is new
* You restore files to another computer and check they open
- The backup is encrypted
= Only a test restore proves you can recover.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 4
        [
            'title'   => 'Module 4: People and incidents',
            'summary' => 'Train people, understand social engineering, handle incidents calmly and know breach duties.',
            'lessons' => [
                [
                    'title'   => 'Security awareness and social engineering',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to plan simple staff training and explain how social engineering works. This matters because technology cannot stop a tired employee who trusts a convincing stranger.</p>
<h3>What is social engineering?</h3>
<p><strong>Social engineering</strong> means tricking people, not machines, into giving up information or access. The attacker uses trust, authority, fear, kindness or urgency. Common forms are:</p>
<table>
<thead><tr><th>Type</th><th>How it works</th></tr></thead>
<tbody>
<tr><td>Phishing</td><td>A fake e-mail or SMS asks you to click or log in</td></tr>
<tr><td>Vishing</td><td>A phone call from "the bank" or "IT" asking for codes or passwords</td></tr>
<tr><td>Pretexting</td><td>The attacker invents a believable story, such as an auditor or donor visit</td></tr>
<tr><td>Business e-mail compromise</td><td>A message that looks like it is from the director asks finance to change bank details or pay an urgent invoice</td></tr>
<tr><td>Physical tailgating</td><td>Someone follows a staff member through a door without a pass</td></tr>
</tbody>
</table>
<h3>Warning signs</h3>
<ul>
<li>Pressure to act quickly or keep it secret.</li>
<li>A change in payment details or an unusual request from a senior person.</li>
<li>A link or attachment you did not expect.</li>
<li>Someone asking for a password, PIN or code. A real IT team never needs your password.</li>
</ul>
<h3>Simple defences for the team</h3>
<ol>
<li><strong>Verify through a second channel.</strong> If the director asks for an urgent payment by e-mail, call them on a known number.</li>
<li><strong>Use a two-person rule</strong> for payments and bank detail changes.</li>
<li><strong>Slow down.</strong> Urgency is the attacker's best tool.</li>
<li><strong>Report quickly.</strong> A message like "I clicked something strange" within minutes can save the organisation.</li>
</ol>
<h3>Running good awareness training</h3>
<p>Keep sessions short (20 to 30 minutes), regular (every quarter) and based on real examples from your community. Use stories, short quizzes and role play instead of long lectures. Teach staff what to <em>do</em>, not just what to fear. Welcome people who report mistakes and never shame them, or they will hide the next one. Include volunteers, board members and new joiners within their first week.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write three realistic scam scenarios aimed at your organisation (for example a fake invoice or a fake donor).</li>
<li>For each one, write the warning signs and the correct response.</li>
<li>Draft a 20-minute training outline using your scenarios.</li>
<li>Agree on a single e-mail address or phone number for reporting suspicious messages.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Social engineering targets people through trust, urgency and authority.</li>
<li>Verify unusual requests through a second channel.</li>
<li>Use a two-person rule for money changes.</li>
<li>Training should be short, regular, practical and blame-free.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Ethical phishing simulations',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to plan a fair phishing exercise for your own staff, and explain when not to run one. This matters because practice builds habits, but a badly run test can damage trust and even break the law.</p>
<h3>What is a phishing simulation?</h3>
<p>A <strong>phishing simulation</strong> is an organised exercise in which an organisation sends harmless, realistic test messages to its own staff to see who reports them, so that training can be improved. The purpose is learning, never punishment or entertainment.</p>
<h3>Rules for doing it ethically</h3>
<ol>
<li><strong>Get written approval</strong> from the director or board before you begin. Never run one on your own initiative.</li>
<li><strong>Limit it to your own staff and systems.</strong> Never send test messages to the public, to partners or to anyone outside the approved group.</li>
<li><strong>No harm.</strong> Use a link that goes to a training page, not a real login form. Never collect real passwords. Do not attach anything risky.</li>
<li><strong>Be fair in content.</strong> Do not use fear topics such as fake salary cuts, medical results, bereavement, or threats of dismissal. These cause real distress.</li>
<li><strong>Protect privacy.</strong> Report results as groups and percentages. Do not publish names, and do not use results in disciplinary action.</li>
<li><strong>Teach straight away.</strong> Anyone who clicks sees a friendly message explaining the clues they missed.</li>
<li><strong>Inform people in general.</strong> Staff should know that exercises may happen, even if they do not know when.</li>
</ol>
<h3>A simple plan</h3>
<table>
<thead><tr><th>Step</th><th>Action</th></tr></thead>
<tbody>
<tr><td>Prepare</td><td>Choose a mild scenario, such as a fake parcel notice or a fake shared document</td></tr>
<tr><td>Brief leaders</td><td>Tell the director and the e-mail administrator to avoid false alarms</td></tr>
<tr><td>Send</td><td>Send to a small group first, then widen</td></tr>
<tr><td>Measure</td><td>Count clicks and, more importantly, reports</td></tr>
<tr><td>Teach</td><td>Share the clues and one new habit</td></tr>
<tr><td>Repeat</td><td>Run again after a few months and compare</td></tr>
</tbody>
</table>
<h3>What to measure</h3>
<p>The most useful figure is the <strong>report rate</strong>, the share of people who told the security contact. A rising report rate means people are alert, which is better than a low click rate alone.</p>
<h3>Limits</h3>
<p>Simulations do not replace technical filters or good processes. If staff are very busy or stressed, delay the exercise. Never try to trick the public or other organisations, even for "awareness", because that is illegal and harmful.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write a one-page approval request for a simulation, with purpose, scope, content rules and how results will be used.</li>
<li>Draft a friendly "this was a test" learning page of five lines.</li>
<li>List three topics you will never use in a test message.</li>
<li>Decide who will receive reports and how quickly they will respond.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Simulations teach staff; they are not traps for punishment.</li>
<li>Get written approval and stay inside your own organisation.</li>
<li>Avoid distressing topics and never collect real passwords.</li>
<li>Track the report rate and teach straight afterwards.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Responding to an incident',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to follow six clear steps when something goes wrong. This matters because in a real incident people panic, and a calm plan limits the damage and the cost.</p>
<h3>What is an incident?</h3>
<p>A <strong>security incident</strong> is any event that harms, or could harm, your information or systems. Examples: a hacked e-mail account, ransomware on a computer, a lost phone with work data, or a file shared with the wrong people.</p>
<h3>The six steps</h3>
<ol>
<li><strong>Prepare.</strong> Before anything happens, write an incident plan: who leads, who to call, where the contact list is kept (on paper too), and how to reach your e-mail, hosting and bank contacts. Make sure backups work.</li>
<li><strong>Detect.</strong> Notice that something is wrong: strange sign-in alerts, files that will not open, a message demanding payment, or a staff report. Encourage quick reporting.</li>
<li><strong>Contain.</strong> Stop the spread. Disconnect the affected computer from the network and Wi-Fi (but do not switch it off unless advised, as it may help investigation). Change passwords of affected accounts from a clean device. Disable stolen accounts.</li>
<li><strong>Eradicate.</strong> Remove the cause: clean or rebuild infected machines, remove hidden forwarding rules, close the weakness that allowed entry.</li>
<li><strong>Recover.</strong> Restore from clean backups, bring systems back one at a time, and watch closely for repeat problems.</li>
<li><strong>Learn.</strong> Within two weeks hold a short, blame-free review: what happened, what worked, what must change. Update the plan and risk register.</li>
</ol>
<h3>Keep notes</h3>
<p>From the first minute, write down times, what you saw, who was told and what you did. These notes help with recovery, insurers, authorities and later learning.</p>
<h3>Roles</h3>
<table>
<thead><tr><th>Role</th><th>Job</th></tr></thead>
<tbody>
<tr><td>Incident lead</td><td>Makes decisions and coordinates</td></tr>
<tr><td>Technical helper</td><td>Contains and fixes the problem</td></tr>
<tr><td>Communications person</td><td>Informs staff, partners and the public accurately</td></tr>
<tr><td>Note-taker</td><td>Records the timeline</td></tr>
</tbody>
</table>
<p>In a small organisation one person may hold several roles, but name them in advance.</p>
<h3>Ransomware reminders</h3>
<p>If files are locked and a payment is demanded, isolate the device, report to the lead, and contact trusted technical help and the police cyber unit. Paying does not guarantee your files back and encourages more attacks. This is why tested backups matter.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write a one-page incident plan with the six steps and your own names and phone numbers.</li>
<li>Choose a scenario, such as a hacked Facebook page, and walk through each step on paper with a colleague.</li>
<li>Note where you were unsure, such as who has the page owner login.</li>
<li>Fix those gaps and store a printed copy somewhere safe.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The steps are prepare, detect, contain, eradicate, recover and learn.</li>
<li>Contain first, then fix the cause, then restore.</li>
<li>Keep notes with times from the start.</li>
<li>A practised plan and tested backups make incidents far less harmful.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Reporting, communication and breach duties',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain who to tell after an incident and why data protection law matters. This matters because many organisations hold personal data about children, patients, donors and clients, and have legal and ethical duties to protect it. This lesson gives general information, not legal advice, so ask a qualified lawyer or the data protection authority for your exact case.</p>
<h3>Internal reporting</h3>
<p>Make reporting easy. Tell staff to report anything strange straight away, to one named person or a single address, at any hour. Say clearly that honest reports are never punished. Delays in reporting turn small problems into big ones.</p>
<h3>Personal data and the law in Uganda</h3>
<p>Uganda has a law called the <strong>Data Protection and Privacy Act, 2019</strong>, with regulations, and a body called the <strong>Personal Data Protection Office (PDPO)</strong> under the National Information Technology Authority. In general terms the law says that organisations which collect and use personal data should:</p>
<ul>
<li>collect only the data they need and for a clear purpose,</li>
<li>keep it accurate and not longer than necessary,</li>
<li>take appropriate security measures to protect it,</li>
<li>respect the rights of people (to know what is held about them and to ask for corrections),</li>
<li>take extra care with sensitive data and with the data of children.</li>
</ul>
<p>Some organisations must register with the authority. Check the PDPO website for current rules.</p>
<h3>Breach notification in general terms</h3>
<p>A <strong>personal data breach</strong> is when personal data is lost, stolen, accessed or shared by people who should not have it. In general terms, the law expects an organisation to tell the authority about a breach within a short, set time and, where people may be harmed, to tell those people too. Check the exact deadlines and the content required with the authority or a legal adviser, and write them into your incident plan.</p>
<h3>Communicating well</h3>
<table>
<thead><tr><th>Audience</th><th>What they need</th></tr></thead>
<tbody>
<tr><td>Staff</td><td>What to do now, such as changing passwords, and what not to say publicly</td></tr>
<tr><td>Affected people</td><td>What happened, what data, what risks, what you are doing and what they should do</td></tr>
<tr><td>Authorities and partners</td><td>Facts, dates and steps taken, as the law and agreements require</td></tr>
<tr><td>Public or media</td><td>A short, honest statement from one spokesperson</td></tr>
</tbody>
</table>
<p>Be honest, calm and specific. Do not guess, blame individuals or hide facts. Offer practical help, such as advice on changing passwords or watching for scams.</p>
<h3>Try it yourself</h3>
<ol>
<li>List the types of personal data your organisation holds and where each is stored.</li>
<li>Mark any that are sensitive or about children.</li>
<li>Look up the PDPO website and note one rule that applies to you.</li>
<li>Draft a 5-line message to affected people for a pretend breach, using calm and clear language.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Report quickly and without blame inside the organisation.</li>
<li>Uganda's data protection law expects security, purpose limits and care with sensitive data.</li>
<li>Know your breach notification duties and write them into the plan.</li>
<li>Communicate honestly and with one voice.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: The finance officer receives an e-mail that looks like it is from the director, asking for an urgent change of bank details. What is the best response?
- Make the change quickly because the director is busy
- Reply to the e-mail asking if it is real
* Call the director on a known number to confirm before doing anything
- Forward it to all staff
= Verifying through a second channel defeats business e-mail compromise.

Q: Which are good principles for an ethical phishing simulation? (choose all that apply)
* Get written approval first
* Use only your own staff and systems
* Teach people straight after the test
- Use fake threats of dismissal to make it realistic
= Fear topics can cause real distress. Tests must be fair and educational.

Q: What is the most valuable measure after a phishing simulation?
- The name of each person who clicked
- How angry people were
* The report rate, meaning how many told the security contact
- How many e-mails were sent
= A good report rate shows people are alert and willing to speak up.

Q: Which is the correct order of incident response?
- Detect, eradicate, prepare, learn, contain, recover
* Prepare, detect, contain, eradicate, recover, learn
- Contain, learn, prepare, detect, recover, eradicate
- Recover, contain, detect, prepare, learn, eradicate
= Preparation comes first, and the learning step closes the cycle.

Q: A staff computer shows a ransomware message. What should you do first?
- Pay immediately to save time
- Delete all files
* Disconnect it from the network and tell the incident lead
- Restart it ten times
= Isolating the device limits the spread. Paying does not guarantee recovery.

Q: Why keep time-stamped notes during an incident?
- To blame someone later
* They support recovery, reporting and learning afterwards
- They are only needed for the media
- Notes are never useful
= A clear timeline helps with decisions, authorities and later improvement.

Q: In general terms, what does Uganda's data protection law expect when personal data is breached?
- Nothing, if the data is old
* That the authority, and where harm is likely the affected people, are told promptly
- That the breach is kept secret
- That all data is deleted
= Exact deadlines should be checked with the PDPO or a lawyer, but prompt notification is the general principle.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 5
        [
            'title'   => 'Module 5: Governance and improvement',
            'summary' => 'Keep track of what you have, manage suppliers, monitor, plan for continuity and build a 90-day plan.',
            'lessons' => [
                [
                    'title'   => 'Asset inventory and vendor risk',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build an inventory of your devices, accounts and data, and check the security of the suppliers you rely on. This matters because you cannot secure what you do not know you have, and your security is only as strong as your weakest supplier.</p>
<h3>Why an inventory?</h3>
<p>An <strong>asset inventory</strong> is a list of everything that holds or handles your information. Forgotten devices and old accounts are a common way in for attackers, because nobody updates or watches them.</p>
<h3>What to record</h3>
<table>
<thead><tr><th>Type</th><th>Details to record</th></tr></thead>
<tbody>
<tr><td>Devices</td><td>Type, model, serial number, who uses it, operating system, date bought, encrypted yes or no</td></tr>
<tr><td>Software and subscriptions</td><td>Name, purpose, owner, renewal date, who has admin rights</td></tr>
<tr><td>Online accounts</td><td>E-mail, social media, website hosting, domain name, payment systems and who owns each</td></tr>
<tr><td>Data</td><td>What it is, where it is kept, how sensitive, who can open it, how long it is kept</td></tr>
</tbody>
</table>
<p>A shared spreadsheet is enough to begin. Keep it up to date when you buy, lend, repair or dispose of anything.</p>
<h3>Safe disposal</h3>
<p>Before selling or giving away a computer or phone, back up what you need, sign out of accounts and erase it properly using the factory reset function after encryption is on. For devices that held very sensitive data, ask a trusted technician about secure erasure or physical destruction.</p>
<h3>Vendor (supplier) risk</h3>
<p>Your vendors include cloud providers, web developers, accountants, payment services and the IT freelancer who has your passwords. If they are hacked or careless, your data may be exposed. Simple checks:</p>
<ol>
<li><strong>List vendors</strong> who can access your data or systems.</li>
<li><strong>Ask basic questions:</strong> Do you use MFA? Where is data stored? How do you handle breaches? Can we export or delete our data?</li>
<li><strong>Give least privilege.</strong> A web developer does not need access to your finance records.</li>
<li><strong>Use a written agreement</strong> that covers confidentiality, security and what happens at the end of the contract.</li>
<li><strong>Review yearly</strong> and remove access when a contract ends.</li>
</ol>
<h3>Domain and website basics</h3>
<p>Make sure your domain name is registered in the organisation's name, not a freelancer's, with MFA on the registrar account and auto-renew on. Losing a domain can mean losing your e-mail and website.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open a spreadsheet and record at least ten devices and accounts.</li>
<li>Add a column for the owner and a column for "last checked".</li>
<li>List every vendor who can access your data and mark the three most important.</li>
<li>Write three questions you will ask each of them.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>You cannot protect what you do not know about, so keep an inventory.</li>
<li>Dispose of old devices safely.</li>
<li>Check vendors and give them only the access they need.</li>
<li>Keep domains and key accounts in the organisation's name.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Logging, monitoring and business continuity',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use simple logs and alerts to notice problems, and write a basic business continuity plan. This matters because an organisation that notices trouble early and knows how to keep working suffers far less harm.</p>
<h3>Logs in plain words</h3>
<p>A <strong>log</strong> is an automatic record of events: who signed in, from where, what was changed and what failed. Logs let you see what happened after an incident, and sometimes warn you while it is happening.</p>
<h3>Easy monitoring for small organisations</h3>
<ul>
<li><strong>Sign-in alerts.</strong> Turn on notifications from e-mail and cloud services for new sign-ins and changes to security settings.</li>
<li><strong>Account activity pages.</strong> Most e-mail and social media services show recent activity and the devices that are signed in. Review them monthly and sign out unknown devices.</li>
<li><strong>Admin audit logs.</strong> Cloud workspaces often include logs of file sharing and admin actions. Check who shared files externally.</li>
<li><strong>Security tool reports.</strong> Look at your endpoint protection dashboard for devices that are out of date or have detected threats.</li>
<li><strong>Router and website logs.</strong> Know where they are and who can read them.</li>
</ul>
<p>Keep logs for long enough to be useful, often three to twelve months, and protect them from being edited or deleted. Logs may contain personal data, so limit who can see them.</p>
<h3>Business continuity</h3>
<p><strong>Business continuity</strong> means keeping essential services running, or restoring them quickly, when something goes wrong, whether a cyber attack, a flood, a power cut or the loss of a key person.</p>
<ol>
<li><strong>Find the essential services.</strong> For a school this may be teaching records, fee collection and communication with parents.</li>
<li><strong>Find what each depends on:</strong> power, internet, people, systems and data.</li>
<li><strong>Plan workarounds.</strong> Paper registers, mobile data as backup internet, a spare laptop, a power bank or generator.</li>
<li><strong>Write a contact sheet</strong> with staff, suppliers, bank, internet provider and authorities. Keep a printed copy in a safe place.</li>
<li><strong>Know who can decide and who can sign</strong> when the director is away.</li>
<li><strong>Practise</strong> once a year with a short walk-through of a scenario.</li>
</ol>
<h3>Key people</h3>
<p>Avoid depending on one person who alone knows the passwords or the setup. Document key procedures, store emergency access safely (for example, sealed in a safe with two people who know where) and train a deputy.</p>
<h3>Try it yourself</h3>
<ol>
<li>Check the recent sign-in activity for your main e-mail account and sign out any device you do not recognise.</li>
<li>Switch on new sign-in alerts for at least two services.</li>
<li>Choose one essential service and write what you would do if the power and internet were off for a full day.</li>
<li>Start a one-page contact sheet and print it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Logs and alerts help you notice problems and investigate them.</li>
<li>Review account activity monthly and protect log files.</li>
<li>Business continuity plans keep essential services running.</li>
<li>Avoid single points of failure, including a single knowledgeable person.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Your 90-day plan, checklists and careers',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to turn everything in this course into a 90-day plan for a small organisation, and know the main career paths in cybersecurity. This matters because security improves through steady, small steps that someone owns, not through one big project that never ends.</p>
<h3>A starter security checklist</h3>
<table>
<thead><tr><th>Area</th><th>Question</th></tr></thead>
<tbody>
<tr><td>Accounts</td><td>Is MFA on for e-mail, cloud, finance and social pages?</td></tr>
<tr><td>Passwords</td><td>Does the team use a password manager and unique passwords?</td></tr>
<tr><td>Devices</td><td>Are updates automatic, disks encrypted and screens locked?</td></tr>
<tr><td>Network</td><td>Is the router secured and a guest Wi-Fi in use?</td></tr>
<tr><td>Backups</td><td>Do we follow 3-2-1 and have we tested a restore?</td></tr>
<tr><td>People</td><td>Has everyone been trained and do they know how to report?</td></tr>
<tr><td>Incidents</td><td>Is there a written plan with contacts?</td></tr>
<tr><td>Vendors and data</td><td>Do we have an inventory and checked our suppliers?</td></tr>
</tbody>
</table>
<h3>The 90-day improvement plan</h3>
<p><strong>Days 1 to 30: the foundations.</strong></p>
<ul>
<li>Choose a security lead and get leadership approval.</li>
<li>Build the asset inventory and risk register.</li>
<li>Turn on MFA for e-mail, cloud storage and finance accounts.</li>
<li>Start automatic backups and complete one test restore.</li>
</ul>
<p><strong>Days 31 to 60: devices and people.</strong></p>
<ul>
<li>Update and encrypt all laptops and phones, and retire unsupported devices.</li>
<li>Introduce a password manager and sign the short security policy.</li>
<li>Secure the router, set a guest network and review cloud sharing.</li>
<li>Run the first 30-minute awareness session.</li>
</ul>
<p><strong>Days 61 to 90: response and review.</strong></p>
<ul>
<li>Write the incident plan and practise a scenario.</li>
<li>Review vendor access and remove what is not needed.</li>
<li>Write a simple continuity plan and print the contact sheet.</li>
<li>Re-score the risk register and present progress to leadership.</li>
</ul>
<h3>Keep it going</h3>
<p>Set a quarterly review in the calendar. Celebrate progress, however small, and fund improvements in the annual budget. Many security steps cost nothing but time.</p>
<h3>Careers and certifications in general terms</h3>
<p>Security careers include security analyst, IT support with security duties, network administrator, incident responder, risk and compliance officer, data protection officer, and awareness trainer. Employers often value practical skill, curiosity and honesty. Learners often begin with IT support and networking foundations, then add certifications. Entry-level credentials from respected bodies, such as vendor-neutral security fundamentals certificates and the introductory certificates offered by well-known training organisations, can help, and some have free learning paths. Check each programme's current content, cost and recognition before you pay, and practise only in safe, legal labs that you own or that are provided for learning.</p>
<h3>Try it yourself</h3>
<ol>
<li>Copy the 90-day plan into a spreadsheet with columns for task, owner and date.</li>
<li>Adjust it to your organisation, removing or adding tasks.</li>
<li>Give every task one owner and a date.</li>
<li>Present the first 30 days to a leader and ask for their written support.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A checklist turns advice into questions you can answer.</li>
<li>Do foundations first, then devices and people, then response and review.</li>
<li>Assign owners, dates and a quarterly review.</li>
<li>Security careers reward steady learning, ethics and hands-on practice.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Why keep an asset inventory?
- To impress visitors
* You cannot protect or update what you do not know you have
- It replaces the need for backups
- It is only needed by large banks
= Forgotten devices and accounts are a common entry point.

Q: Before giving away an old laptop, what should you do? (choose all that apply)
* Back up what you need
* Sign out of accounts
* Erase it properly with a factory reset after encryption
- Leave your files on it for the new owner
= Safe disposal prevents your data from reaching the next owner.

Q: A freelance web developer needs to update the website. What should you do about their access?
- Give them admin access to all systems
* Give only the access needed for the website, and remove it when the work ends
- Share your personal e-mail password
- Give them the finance folder to be safe
= Least privilege applies to suppliers too.

Q: Which is a simple monitoring step for a small organisation?
- Ignoring sign-in alerts
- Turning off account activity pages
* Turning on new sign-in alerts and reviewing devices signed in
- Deleting all logs every day
= Alerts and reviews of account activity help notice problems early.

Q: What does a business continuity plan aim to do?
- Prevent every incident
* Keep essential services running or restore them quickly
- Replace the security policy
- Remove the need for staff training
= Continuity focuses on resilience when something goes wrong.

Q: Where should the first 30 days of a security improvement plan focus?
- Buying the most expensive tools
- Waiting for an incident
* Foundations such as inventory, risk register, MFA and backups
- Only a long policy document
= Quick, low-cost foundations reduce the most risk early.

Q: What is a sensible way to start a cybersecurity career?
- Practise on any system you can find online
* Learn IT and networking basics, practise in legal labs and consider entry-level certifications
- Skip learning and just buy tools
- Use leaked data to practise
= Ethical and legal practice is essential, and strong foundations help.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What is an asset in security terms?
- Only a computer or phone
- Only money
* Anything of value, including information, people and reputation
- Only software
= Assets are broad. Information and trust are often the most valuable.

Q: A learner results file exists only on one computer. This is best described as:
- A threat
- An asset
- A policy
* A vulnerability
= A single copy is a weakness. A failure or ransomware would be the threat.

Q: Which scoring approach does a simple risk assessment use?
- Colour of the device
* Likelihood multiplied by impact
- Number of staff
- Age of the organisation
= Multiplying likelihood and impact gives a priority score.

Q: Which treatment means taking action to make a risk smaller?
- Accept
- Ignore
* Reduce
- Hide
= Reducing means lowering the likelihood or the impact.

Q: In the CIA triad, which goal is damaged when ransomware locks files on a deadline day?
- Confidentiality
- Integrity
* Availability
- Accountability
= Availability means systems and data are usable when needed.

Q: What makes a security policy effective for a small organisation? (choose all that apply)
* It is short and in plain language
* It is approved by leaders
* It explains the reasons for rules
- It is long and full of legal terms
= People follow rules they understand and see leaders support.

Q: Why is each risk register action given one owner?
- To make blame easy
- To make the register longer
* So someone is accountable for completing it
- Owners are not needed
= Clear ownership gets actions finished.

Q: Which best describes least privilege?
- Everyone has admin rights for convenience
* Each person has only the access needed for the job
- Access is given only to the director
- Access is never removed
= Limiting access limits the harm from mistakes and stolen accounts.

Q: What should be done to accounts when someone leaves the organisation?
- Nothing until they return the laptop next year
- Share their password with their replacement
* Disable accounts on their last day and recover devices
- Rename the account
= Prompt removal of access prevents misuse.

Q: Which password approach is recommended today?
- Short passwords with many symbols changed every week
* Long unique passphrases kept in a password manager
- The same password on every account
- Writing the password on the monitor
= Length and uniqueness protect best, and a manager makes this practical.

Q: Which account should usually get MFA first?
- A game account
* The main e-mail account, because it can reset other accounts
- The Wi-Fi guest page
- The printer
= Control of e-mail often means control of everything else.

Q: Why encrypt a laptop's disk?
- To make it run faster
* So the files are unreadable if the laptop is stolen
- To remove the need for updates
- To block phishing
= Encryption protects data when the device is lost, as long as the key is not stored with it.

Q: Which are good rules for phones used for work? (choose all that apply)
* A strong screen lock
* Updates installed promptly
* Find My Device or equivalent turned on
- Saving beneficiary lists in a personal chat group
= Protect the device and keep sensitive lists out of personal chats.

Q: What is the safest approach to router Wi-Fi settings?
- Keep the default admin password
- Use open Wi-Fi
* Change the admin password and use WPA2 or WPA3
- Turn off all updates
= Defaults are widely known and encryption protects traffic.

Q: What is segmentation?
- Deleting old files
* Splitting a network into parts so problems do not spread easily
- Using one password everywhere
- Backing up at night
= Segmentation, such as a guest Wi-Fi, limits movement of threats.

Q: Which statement about VPNs is correct?
- A VPN stops all phishing
* A VPN encrypts traffic in transit but does not remove every risk
- A VPN makes you invisible
- A VPN replaces antivirus
= VPNs protect the connection, not your behaviour or your device.

Q: For a confidential cloud file, which sharing choice is best?
- Anyone with the link can edit
* Restricted to named people with the lowest permission needed
- Public on the internet
- Shared in a group chat
= Restricted sharing with least privilege reduces leaks.

Q: The 3-2-1 rule means:
- Three people, two keys, one door
* Three copies, two kinds of storage, one copy off-site
- Three drives, two cables, one computer
- Three backups yearly
= It protects against loss of any single copy or location.

Q: Why is syncing not the same as a backup?
- It uses more electricity
* Syncing copies mistakes and deletions at once
- It never uses the cloud
- It is illegal
= A backup keeps older, recoverable versions.

Q: What proves that a backup can be restored?
- The software says "complete"
* A test restore of files to another computer
- The drive is labelled
- The drive is expensive
= Only restoring proves recovery works.

Q: A request to change supplier bank details arrives by e-mail from "the director". The best action is to:
- Pay immediately
* Verify by calling a known number and follow a two-person rule
- Reply with your password
- Ignore all e-mails forever
= Second-channel verification defeats business e-mail compromise.

Q: Which is correct for an ethical phishing simulation? (choose all that apply)
* Written approval from leadership first
* Only your own staff and systems
* No collection of real passwords
- Sending tests to partners without telling them
= Tests must be authorised, limited, harmless and fair.

Q: The correct order of incident response is:
- Contain, prepare, learn, recover, detect, eradicate
* Prepare, detect, contain, eradicate, recover, learn
- Recover, detect, learn, prepare, eradicate, contain
- Detect, learn, prepare, contain, recover, eradicate
= The cycle starts with preparation and ends with learning.

Q: What is the first action for a computer showing a ransomware demand?
- Pay at once
* Disconnect it from the network and tell the incident lead
- Share the message publicly
- Delete the backups
= Isolating the device limits spread and keeps backups safe.

Q: Which statement matches Uganda's data protection law in general terms?
- Organisations may keep any data forever
* Organisations should collect only what they need, secure it and respect people's rights
- Children's data needs no extra care
- Breaches never need reporting
= Security, purpose limits and rights are core ideas. Check details with the PDPO.

Q: What is the purpose of an asset inventory?
* To know what devices, accounts and data you must protect
- To list staff birthdays
- To replace passwords
- To make backups unnecessary
= You cannot protect what you cannot see.

Q: How should you treat a vendor who needs to update your website?
- Give them all passwords
* Give limited access and remove it when finished
- Give no contract
- Never check them
= Least privilege and review apply to suppliers.

Q: Which is a simple monitoring habit?
- Turning off alerts
* Reviewing signed-in devices and enabling sign-in alerts
- Deleting logs daily
- Sharing admin rights widely
= Alerts and reviews help detect unusual access early.

Q: A good 90-day plan places which work first?
- Advanced tools before basics
* Foundations such as inventory, MFA and backups
- Only awareness posters
- Waiting for a breach
= Foundations give the biggest early reduction in risk.

Q: A good first step into a cybersecurity career is to:
- Test other people's systems without permission
* Build IT and networking basics and practise in legal labs
- Buy leaked databases
- Avoid learning
= Ethical practice and good foundations are essential.
QUIZ,
];
