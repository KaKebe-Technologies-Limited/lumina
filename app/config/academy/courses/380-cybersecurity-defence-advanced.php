<?php
return [
    'slug'        => 'cybersecurity-defence-advanced',
    'title'       => 'Defending Systems: Security Operations and Incident Response',
    'category'    => 'security',
    'level'       => 'advanced',
    'icon'        => 'shield',
    'sort'        => 380,
    'summary'     => 'Move from security basics to security operations: harden Windows and Linux, manage patches and vulnerabilities, segment networks, monitor logs, respond to incidents and handle evidence and data protection duties.',
    'description' => <<<'HTML'
<p>Every organisation that uses computers needs someone who can keep them safe day after day, not only when something goes wrong. This course teaches the work of a <strong>defender</strong>: the person or small team who reduces risk, watches for trouble, and leads the response when an incident happens. It suits IT officers in schools, NGOs, clinics and small businesses, and anyone preparing for a career in security operations.</p>
<p>The course is <strong>strictly defensive</strong>. You will learn risk assessment, hardening, patching, least privilege, multi-factor authentication, network segmentation, logging, monitoring, backups, incident response, basic forensics and data protection duties. You will not be taught how to break into systems. Never test or scan any system that you do not own or do not have written permission to test, because doing so can be a crime.</p>
<p>Each lesson ends with an activity you can do on your own equipment or a practice machine. By the end you will have a hardening checklist, a logging plan, a backup and recovery plan and an incident response playbook for a realistic organisation.</p>
HTML,
    'outcomes'    => [
        'Explain defence in depth and describe the daily work of a security operations team',
        'Carry out a structured risk assessment using an asset inventory and a recognised framework',
        'Harden Windows and Linux computers using safe, documented baseline settings',
        'Run a patch and vulnerability management cycle and prioritise fixes by risk',
        'Apply least privilege, role-based access and multi-factor authentication',
        'Design network segmentation and explain how firewalls and intrusion detection fit in',
        'Plan logging and monitoring, and explain how a SIEM turns events into alerts',
        'Build and test a backup and recovery plan with clear recovery targets',
        'Lead an incident through its phases and handle digital evidence correctly',
        'Describe data protection and breach duties under Ugandan law in plain terms',
    ],
    'requirements'=> [
        'Completion of Cybersecurity Essentials for Organisations (or equal knowledge)',
        'Completion of Networking Fundamentals: IP Addressing, Routers and Wi-Fi',
        'Comfortable use of Windows and willingness to use a Linux practice machine or virtual machine',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'NIST Cybersecurity Framework', 'url' => 'https://www.nist.gov/cyberframework'],
        ['title' => 'CISA: Cybersecurity resources and guidance', 'url' => 'https://www.cisa.gov'],
        ['title' => 'UK National Cyber Security Centre: Guidance', 'url' => 'https://www.ncsc.gov.uk'],
        ['title' => 'Center for Internet Security: Benchmarks', 'url' => 'https://www.cisecurity.org'],
        ['title' => 'Personal Data Protection Office, Uganda', 'url' => 'https://www.pdpo.go.ug'],
    ],
    'modules' => [
        [
            'title'   => 'Module 1: The defender\'s foundations',
            'summary' => 'Understand how security operations work, how to assess risk with a framework and how policy and governance guide the technical work.',
            'lessons' => [
                [
                    'title'   => 'Defence in depth and the work of security operations',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain why one security tool is never enough and describe what a security operations team does each day. This matters because most breaches succeed when a single protection fails and nothing else is there to catch the problem.</p>
<h3>Defence in depth</h3>
<p><strong>Defence in depth</strong> means placing several independent layers of protection between an attacker and your valuable assets, so that if one layer fails the next one still helps. Think of a school compound: a fence, a gate with a guard, locked classrooms, and a locked cupboard for exam papers. Nobody relies on the fence alone.</p>
<table>
<thead><tr><th>Layer</th><th>Purpose</th><th>Example controls</th></tr></thead>
<tbody>
<tr><td>People and policy</td><td>Make safe behaviour normal</td><td>Training, clear rules, reporting channel</td></tr>
<tr><td>Identity and access</td><td>Make sure only the right person gets in</td><td>Strong passwords, MFA, least privilege</td></tr>
<tr><td>Device (endpoint)</td><td>Protect each computer and phone</td><td>Patching, hardening, disk encryption, anti-malware</td></tr>
<tr><td>Network</td><td>Limit how far trouble can travel</td><td>Firewall, segmentation, secure Wi-Fi</td></tr>
<tr><td>Application and data</td><td>Protect the information itself</td><td>Permissions, encryption, backups</td></tr>
<tr><td>Monitoring and response</td><td>Notice and react quickly</td><td>Logs, alerts, incident playbooks</td></tr>
</tbody>
</table>
<h3>Prevent, detect, respond, recover</h3>
<p>Good security does four jobs. <strong>Prevent</strong> what you can, <strong>detect</strong> what slips through, <strong>respond</strong> to contain the damage, and <strong>recover</strong> so normal work resumes. Beginners often spend everything on prevention. Experienced defenders accept that some attacks will get through, so they invest equally in detection and recovery.</p>
<h3>What security operations does</h3>
<p>A large company has a <strong>Security Operations Centre (SOC)</strong>, a team that watches systems around the clock. A small NGO may have one IT officer doing the same tasks part-time. The tasks are the same:</p>
<ul>
<li>Keeping an up-to-date list of devices, accounts and software.</li>
<li>Applying updates and fixing weaknesses.</li>
<li>Watching logs and alerts for unusual activity.</li>
<li>Investigating alerts to decide whether they are real problems or false alarms.</li>
<li>Leading the response when an incident is confirmed.</li>
<li>Reporting to management and improving after each lesson learned.</li>
</ul>
<h3>The defender's mindset</h3>
<p>Defenders think in terms of <em>assumptions that can fail</em>. Ask: "If this password is stolen, what happens next? Who would notice? How quickly could we recover?" Write your assumptions down and test them. Stay calm and factual, share information with colleagues, and never blame the person who reports a mistake, because blame teaches people to hide problems.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose a small organisation you know, such as a school or a shop with a computer.</li>
<li>Draw the six layers from the table and write at least one real control the organisation has for each.</li>
<li>Mark the layers where you can find no control. These are your gaps.</li>
<li>For the weakest layer, write one prevention step and one detection step.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Defence in depth uses several independent layers so one failure is not fatal.</li>
<li>Security has four jobs: prevent, detect, respond and recover.</li>
<li>Security operations is a daily routine of inventory, patching, monitoring, investigation and response.</li>
<li>A blame-free culture makes problems visible early.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Risk assessment with an asset inventory and a framework',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to run a structured risk assessment that starts from an inventory and uses a recognised framework as a checklist. This matters because guessing what to protect leads to money spent in the wrong places.</p>
<h3>Start with an inventory</h3>
<p>You cannot defend what you do not know exists. Build an <strong>asset inventory</strong> with at least these lists:</p>
<ul>
<li><strong>Hardware:</strong> laptops, phones, servers, routers, printers, with owner and location.</li>
<li><strong>Software and cloud services:</strong> operating systems, accounting tools, e-mail, file storage, with version and who administers them.</li>
<li><strong>Data:</strong> what information you hold (learner records, donor lists, payroll), where it is stored and how sensitive it is.</li>
<li><strong>Accounts:</strong> who has administrator rights, and which accounts belong to people who have left.</li>
</ul>
<p>Forgotten devices and old accounts are among the most common weak points, so the inventory itself often reveals problems.</p>
<h3>Using a framework</h3>
<p>A <strong>framework</strong> is a well-tested checklist of good practice. The NIST Cybersecurity Framework organises security into functions that match the way defenders work:</p>
<table>
<thead><tr><th>Function</th><th>Question it answers</th><th>Example activity</th></tr></thead>
<tbody>
<tr><td>Govern</td><td>Who decides, and what is our risk appetite?</td><td>Approve policies, assign owners</td></tr>
<tr><td>Identify</td><td>What do we have and what are the risks?</td><td>Inventory, risk register</td></tr>
<tr><td>Protect</td><td>How do we reduce the chance of harm?</td><td>MFA, patching, training</td></tr>
<tr><td>Detect</td><td>How do we notice problems?</td><td>Logging, monitoring</td></tr>
<tr><td>Respond</td><td>What do we do when it happens?</td><td>Incident playbook</td></tr>
<tr><td>Recover</td><td>How do we get back to normal?</td><td>Tested backups</td></tr>
</tbody>
</table>
<p>Frameworks are guides, not laws. A five-person NGO does not need everything a bank needs. Choose the parts that fit your size and risk.</p>
<h3>The assessment steps</h3>
<ol>
<li><strong>Scope:</strong> decide what you are assessing (the whole organisation, or one system such as the fee-payment system).</li>
<li><strong>Inventory:</strong> list the assets in scope and rate how valuable each one is.</li>
<li><strong>Identify threats and weaknesses:</strong> use staff interviews, a walk around the office, and the framework checklist.</li>
<li><strong>Rate each risk</strong> by likelihood and impact, and note the controls that exist.</li>
<li><strong>Decide treatment:</strong> reduce, avoid, transfer or accept, with an owner and date.</li>
<li><strong>Report</strong> in plain language to the people who can approve budgets.</li>
</ol>
<h3>Residual risk</h3>
<p>After you apply a control, some risk remains. This is called <strong>residual risk</strong>. For example, multi-factor authentication greatly reduces the danger of stolen passwords, but a user can still be tricked into approving a prompt. Leaders must knowingly accept the residual risk, and it should be written down.</p>
<h3>Try it yourself</h3>
<ol>
<li>Make a spreadsheet with tabs for Hardware, Software, Data and Accounts.</li>
<li>Fill at least five rows in each for a real or imagined organisation.</li>
<li>Pick three items and write a risk for each in the form cause, event, effect.</li>
<li>Score them 1 to 3 for likelihood and impact and record one existing control for each.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>An up-to-date inventory is the base of every assessment.</li>
<li>A framework such as NIST CSF gives a complete checklist across govern, identify, protect, detect, respond and recover.</li>
<li>Assessment ends with decisions, owners and dates, not only a list of worries.</li>
<li>Residual risk is what remains after controls, and leaders must accept it knowingly.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Security policies, standards and governance',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain how policies, standards and procedures work together and write a clear baseline standard for an organisation. This matters because technical controls only last when management has agreed to them and staff know what is expected.</p>
<h3>Policy, standard, procedure, guideline</h3>
<table>
<thead><tr><th>Document</th><th>What it says</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Policy</td><td>The rule and the reason, approved by leaders</td><td>"All laptops holding personal data must be encrypted."</td></tr>
<tr><td>Standard</td><td>The specific, measurable requirement</td><td>"Use full-disk encryption with a recovery key stored by the IT officer."</td></tr>
<tr><td>Procedure</td><td>Step-by-step instructions</td><td>"How to turn on and back up the recovery key."</td></tr>
<tr><td>Guideline</td><td>Advice that is recommended, not compulsory</td><td>"Tips for working safely on public Wi-Fi."</td></tr>
</tbody>
</table>
<p>Keep policies short and stable. Put the details that change, such as software versions, in standards and procedures so you can update them without asking the board every time.</p>
<h3>Policies a small organisation needs first</h3>
<ul>
<li><strong>Acceptable use:</strong> how staff may use organisation devices, internet and e-mail.</li>
<li><strong>Access control:</strong> who gets which accounts, how they are approved and removed.</li>
<li><strong>Password and authentication:</strong> length, password managers, multi-factor authentication.</li>
<li><strong>Data classification and handling:</strong> what is public, internal, confidential, and how each is stored and shared.</li>
<li><strong>Backup and retention:</strong> what is copied, how often, and how long records are kept.</li>
<li><strong>Incident reporting:</strong> who to call, and how fast.</li>
<li><strong>Bring your own device:</strong> what personal phones may and may not do.</li>
</ul>
<h3>Roles and governance</h3>
<p><strong>Governance</strong> is how decisions about security are made and checked. Name clear roles: a senior <strong>owner</strong> accountable for risk (often the director), an <strong>IT or security lead</strong> who runs the controls, <strong>data owners</strong> who decide who may see each type of data, and <strong>every staff member</strong> who follows the rules. Without a named owner, a security task is owned by nobody.</p>
<h3>Exceptions and enforcement</h3>
<p>Sometimes a rule cannot be followed, for example an old machine that cannot be updated. Use a written <strong>exception</strong>: what the exception is, the extra protection used instead, who approved it and when it ends. Rules that are never enforced teach people to ignore all rules, so apply consequences fairly and consistently, starting with coaching.</p>
<h3>Measuring</h3>
<p>Choose a few simple measures that show progress, such as the percentage of laptops encrypted, the number of accounts without MFA, or the days taken to apply critical updates. Report them to leaders every quarter.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write a two-line policy on laptop encryption.</li>
<li>Write the matching standard in three or four measurable sentences.</li>
<li>Write a five-step procedure for a staff member to follow.</li>
<li>Name the owner for each document and set a review date.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Policies state the rule, standards make it measurable, procedures give the steps.</li>
<li>Start with a handful of essential policies and keep them short.</li>
<li>Clear ownership and leadership approval make governance work.</li>
<li>Use written, time-limited exceptions and a few simple measures.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Understanding attacker behaviour to defend better',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe, at a conceptual level, the stages most attacks follow and match each stage to a defensive control. This matters because when you know the usual path of an attack, you can place protection and alarms along it. We study behaviour here only to defend; no attack instructions are given.</p>
<h3>The typical stages of an intrusion</h3>
<p>Security teams use models to describe how attacks progress. A simple version has six stages:</p>
<ol>
<li><strong>Preparation:</strong> the attacker collects public information about the organisation, such as staff names and e-mail formats from websites and social media.</li>
<li><strong>Initial access:</strong> a way in, most often a stolen password, a convincing phishing message, a weak or exposed remote access service, or an unpatched system.</li>
<li><strong>Foothold and escalation:</strong> the attacker tries to stay in and gain more rights, for example by finding an account with administrator power.</li>
<li><strong>Movement:</strong> they explore the network to find valuable data and other computers to reach.</li>
<li><strong>Action on goals:</strong> stealing data, locking files for ransom, committing payment fraud or damaging systems.</li>
<li><strong>Cover-up:</strong> removing traces, such as deleting logs.</li>
</ol>
<h3>Matching defences to stages</h3>
<table>
<thead><tr><th>Stage</th><th>Defensive idea</th></tr></thead>
<tbody>
<tr><td>Preparation</td><td>Limit what you publish about staff; train people about targeted messages</td></tr>
<tr><td>Initial access</td><td>MFA, patching, e-mail filtering, close unused services, awareness training</td></tr>
<tr><td>Foothold and escalation</td><td>Least privilege, separate admin accounts, endpoint protection</td></tr>
<tr><td>Movement</td><td>Network segmentation, restricted file shares, internal monitoring</td></tr>
<tr><td>Action on goals</td><td>Backups kept offline, data loss controls, rapid response</td></tr>
<tr><td>Cover-up</td><td>Send logs to a separate, protected system so they cannot be erased</td></tr>
</tbody>
</table>
<p>The key lesson: an attacker must succeed at <em>many</em> stages, but a defender only needs to stop or notice <em>one</em> of them. That is why layers matter.</p>
<h3>Common motives and who attacks small organisations</h3>
<p>Most attackers who target NGOs, schools and small firms are criminals who want money: they steal payment details, redirect invoices, or demand ransom to unlock files. Others want personal data to sell, or want to use your e-mail account to scam your contacts. Few are targeting you personally; they use automated tools to find easy weaknesses anywhere. Being slightly harder to attack than the next organisation removes many risks.</p>
<h3>Insider and accidental threats</h3>
<p>Not every threat comes from outside. A staff member may misuse access out of anger or greed, but far more common are honest mistakes: sending an e-mail to the wrong person, or clicking a bad link. Controls like least privilege, logging and a calm reporting culture handle both.</p>
<h3>Try it yourself</h3>
<ol>
<li>Pick a recent scam you heard about in the news or from friends, such as a fake payment message or a fake job offer.</li>
<li>Place its steps on the six stages above.</li>
<li>For each stage write one control that would have stopped or detected it.</li>
<li>Mark which of your controls are prevention and which are detection.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Attacks usually move through stages from preparation to action on goals.</li>
<li>Each stage can be matched with a protective or detective control.</li>
<li>Defenders only need to break one link in the chain.</li>
<li>Most attackers seek money and use easy, automated opportunities.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A school protects its network only with a firewall. A phishing e-mail tricks a teacher into giving her password, and the attacker walks in. What principle was missing?
- Risk transfer
* Defence in depth
- Data classification
- Residual risk
= Defence in depth uses several independent layers, such as MFA, endpoint protection and monitoring, so one failure does not lead to total compromise.

Q: Which of these are the four jobs of good security? (choose all that apply)
* Prevent
* Detect
* Respond
* Recover
- Ignore
= Prevention alone is never perfect, so defenders also detect, respond and recover.

Q: Before running a risk assessment, which step gives you the base to work from?
- Buying a SIEM
* Building an inventory of assets, data and accounts
- Writing a press release
- Disabling all internet access
= You cannot protect what you do not know you have, so the inventory comes first.

Q: Which NIST CSF function asks "How do we notice problems?"
- Govern
- Protect
* Detect
- Recover
= Detect covers logging, monitoring and alerting that reveal something is wrong.

Q: An IT officer writes "Use full-disk encryption with the recovery key stored by the IT officer." What kind of document is this?
- A policy
* A standard
- A guideline
- An incident report
= A standard gives the specific, measurable requirement behind a policy.

Q: A critical old machine cannot be updated. What is the best way to handle it under good governance?
- Hide it from the inventory
- Ignore the problem because the rule cannot be followed
* Record a written, time-limited exception with extra protection and an approver
- Delete the policy
= An exception is documented, compensated by another control, approved and given an end date.

Q: Why should logs be sent to a separate protected system?
- To make them look tidier
* So an attacker who gains access cannot easily erase the evidence of their actions
- To save disk space on the user's laptop
- Because logs are never useful
= Attackers often try to delete logs. A separate copy preserves the evidence.

Q: An organisation says its main attackers are criminals seeking money. Which is the most useful conclusion?
- Only large banks need to protect themselves
* Small organisations are targeted through easy weaknesses, so basic controls remove many risks
- Antivirus alone is enough
- Attackers never use phishing
= Attacks are often automated and opportunistic, so removing easy weaknesses makes a real difference.
QUIZ,
        ],
        [
            'title'   => 'Module 2: Hardening systems and controlling access',
            'summary' => 'Reduce the attack surface of Windows and Linux computers, manage patches and vulnerabilities, and apply least privilege with multi-factor authentication.',
            'lessons' => [
                [
                    'title'   => 'Hardening Windows computers',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to apply a safe baseline of settings to a Windows 10 or 11 computer and record what you did. This matters because a default installation is built for convenience, and every unused feature and weak setting is a possible way in.</p>
<h3>What hardening means</h3>
<p><strong>Hardening</strong> means reducing the <strong>attack surface</strong>: removing or switching off what you do not need and tightening what you keep. A <strong>baseline</strong> is a documented set of settings every computer of that type must have, so that all machines are the same and any difference stands out. Respected organisations such as the Center for Internet Security publish free benchmark guidance you can compare with.</p>
<h3>A practical baseline</h3>
<ol>
<li><strong>Use a supported version.</strong> Check <strong>Settings</strong>, then <strong>System</strong>, then <strong>About</strong>. A version that no longer receives security updates cannot be made safe.</li>
<li><strong>Turn on automatic updates.</strong> In <strong>Settings</strong>, open <strong>Windows Update</strong> and make sure updates are not paused.</li>
<li><strong>Encrypt the disk.</strong> On Pro editions, open <strong>Settings</strong>, <strong>Privacy and security</strong>, <strong>Device encryption</strong> or <strong>BitLocker</strong>. Save the recovery key in a safe place that is not on the same computer.</li>
<li><strong>Use standard accounts for daily work.</strong> Create a separate administrator account that is used only for installing software or changing settings.</li>
<li><strong>Keep the built-in protection on.</strong> In <strong>Windows Security</strong>, check that virus and threat protection, the firewall and reputation-based protection are on.</li>
<li><strong>Lock the screen.</strong> Set a short inactivity time and press <strong>Windows key + L</strong> whenever you leave the desk.</li>
<li><strong>Remove unused software and accounts.</strong> In <strong>Settings</strong>, <strong>Apps</strong>, uninstall programs nobody uses. Disable the guest account and old staff accounts.</li>
<li><strong>Control removable drives.</strong> Unknown USB flash disks are a common way for malware to enter, so scan them or restrict their use where possible.</li>
<li><strong>Turn off services you do not use,</strong> such as file sharing on a laptop that never shares files.</li>
</ol>
<h3>Managing many computers</h3>
<p>In an organisation with a Windows domain or a device management service, settings can be pushed to every computer through <strong>group policy</strong> or a similar tool. This is much more reliable than visiting each laptop. In a small office without these tools, use a one-page checklist and tick it for each machine.</p>
<table>
<thead><tr><th>Setting</th><th>Why</th></tr></thead>
<tbody>
<tr><td>Disk encryption</td><td>A lost or stolen laptop does not expose the data</td></tr>
<tr><td>Standard user for daily work</td><td>Malware cannot easily make deep system changes</td></tr>
<tr><td>Automatic updates</td><td>Known weaknesses are closed quickly</td></tr>
<tr><td>Firewall on</td><td>Blocks unwanted incoming connections</td></tr>
<tr><td>Screen lock</td><td>Protects an unattended device</td></tr>
</tbody>
</table>
<h3>Test before you roll out</h3>
<p>Apply new settings to one test computer first. Some hardening can stop a program the office depends on, so test the accounting software and the printer before changing every machine. Keep a record of each change and how to undo it.</p>
<h3>Try it yourself</h3>
<ol>
<li>On your own Windows computer, open <strong>Settings</strong> and note the version and update status.</li>
<li>Check that the firewall and virus protection are on in <strong>Windows Security</strong>.</li>
<li>Confirm that your daily account is a standard user, or plan to create a separate admin account.</li>
<li>Write a one-page checklist of the nine steps and tick what you already have.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Hardening reduces the attack surface by removing and tightening.</li>
<li>A documented baseline makes every machine consistent.</li>
<li>Updates, encryption, standard accounts, firewall and screen lock are the core settings.</li>
<li>Always test changes on one machine and keep a record.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Hardening Linux servers and desktops',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to apply a basic security baseline to a Linux machine, such as a small file or web server. This matters because Linux runs much of the internet and many organisation servers, and a freshly installed server that is exposed to the internet is found by automatic scanners within hours.</p>
<h3>The same ideas, different tools</h3>
<p>Hardening principles are the same on every system: keep it updated, remove what you do not need, restrict access and log what happens. The commands below are for a Debian or Ubuntu style system; other distributions use different package tools. Practise only on your own machine or a virtual machine.</p>
<h3>A baseline step by step</h3>
<ol>
<li><strong>Update the system.</strong> Refresh the package list and apply updates:
<pre><code>sudo apt update
sudo apt upgrade</code></pre></li>
<li><strong>Enable automatic security updates</strong> using the distribution's unattended update package, and check that it is running.</li>
<li><strong>Create named users</strong> and avoid using the root account directly. Give administrative rights through <code>sudo</code> only to people who need them.</li>
<li><strong>Secure remote login (SSH).</strong> Use key pairs instead of passwords, switch off direct root login, and allow only named users. In the SSH configuration file the relevant options are <code>PermitRootLogin no</code> and <code>PasswordAuthentication no</code>. Test a second login from another window before closing the first, so a typing mistake does not lock you out.</li>
<li><strong>Turn on a host firewall</strong> and allow only the services you really offer:
<pre><code>sudo ufw default deny incoming
sudo ufw allow ssh
sudo ufw enable</code></pre></li>
<li><strong>List and remove unneeded services.</strong> The command <code>systemctl list-units --type=service</code> shows what runs. Stop and disable what the machine does not need.</li>
<li><strong>Check open network ports</strong> on your own machine with <code>ss -tuln</code>. Every listening port should have a reason.</li>
<li><strong>Protect files.</strong> Review permissions with <code>ls -l</code>. Configuration files with secrets should be readable only by the account that needs them.</li>
<li><strong>Keep logs and watch them.</strong> Check authentication messages in the system log, and consider extra protection that blocks repeated failed logins.</li>
</ol>
<h3>Permissions in one minute</h3>
<p>Each file has permissions for the owner, the group and everyone else, written as read (r), write (w) and execute (x). A value such as <code>600</code> means only the owner can read and write. A value such as <code>777</code> lets everyone change the file and is almost always a mistake. Give the least permission that still lets the job work.</p>
<h3>Mandatory access and containers</h3>
<p>Many Linux systems include extra confinement tools, such as AppArmor or SELinux, which limit what each program may touch even if it is compromised. Do not disable them just because they caused an error; read the message and allow only what is needed. Programs run inside containers should also be updated and given limited rights.</p>
<h3>Try it yourself</h3>
<ol>
<li>Start a Linux virtual machine or use a practice machine you own.</li>
<li>Run the update commands and note how many packages changed.</li>
<li>Run <code>ss -tuln</code> and write down each listening port and why it exists.</li>
<li>Enable the firewall with a default deny rule and allow only SSH, then confirm you can still log in.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The baseline is: update, minimise services, restrict users, use keys for SSH, enable a firewall and log.</li>
<li>Avoid direct root login and give sudo rights sparingly.</li>
<li>Every open port must have a reason.</li>
<li>Test remote-access changes carefully so you do not lock yourself out.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Patch and vulnerability management',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to run a repeatable cycle that finds weaknesses and fixes the most dangerous ones first. This matters because most successful attacks use weaknesses for which a fix already existed; the organisation simply had not applied it.</p>
<h3>Vulnerabilities and patches</h3>
<p>A <strong>vulnerability</strong> is a flaw in software or settings that could be misused. A <strong>patch</strong> is an update from the maker that fixes it. Publicly known flaws are given identifiers such as CVE numbers, and each one is rated with a severity score from 0 to 10 (the CVSS scale). The score helps, but it is only one input.</p>
<h3>The management cycle</h3>
<ol>
<li><strong>Inventory.</strong> Know every system and its software version. Unknown systems cannot be patched.</li>
<li><strong>Discover.</strong> Learn about flaws from vendor notices, national security alerts and, on systems you own and are authorised to check, a vulnerability scanner that reports missing updates and weak settings.</li>
<li><strong>Prioritise.</strong> Rank by real risk, not only by score.</li>
<li><strong>Remediate.</strong> Apply the patch, change the setting, or use a temporary protection if a patch is not yet available.</li>
<li><strong>Verify.</strong> Check again that the problem is gone.</li>
<li><strong>Report and repeat.</strong> Track time to fix, and run the cycle every week or month.</li>
</ol>
<h3>How to prioritise</h3>
<table>
<thead><tr><th>Question</th><th>Higher priority when</th></tr></thead>
<tbody>
<tr><td>Is it exposed?</td><td>The system faces the internet, such as a web server or remote access gateway</td></tr>
<tr><td>Is it being used by criminals?</td><td>Security agencies report that the flaw is actively exploited</td></tr>
<tr><td>How valuable is the asset?</td><td>It holds payroll, learner records or payment data</td></tr>
<tr><td>Is there another protection?</td><td>No firewall rule, segment or other control reduces the danger</td></tr>
</tbody>
</table>
<p>A medium-scored flaw on an internet-facing system that criminals actively use may need fixing before a high-scored flaw on an isolated test machine.</p>
<h3>Patching safely</h3>
<ul>
<li>Test updates on a few machines first, especially for servers.</li>
<li>Make a backup or snapshot before major updates.</li>
<li>Agree <strong>time targets</strong>: for example critical flaws within a few days, others within a month.</li>
<li>Schedule maintenance windows and tell users in advance. Where electricity or bandwidth is unreliable, allow updates to download in the background and install when power is stable.</li>
<li>Include everything: operating systems, browsers, office programs, router and access point firmware, phones, and plug-ins. Routers are often forgotten.</li>
<li>Retire software that no longer gets updates.</li>
</ul>
<h3>When you cannot patch</h3>
<p>Sometimes a patch would break an essential system. Use <strong>compensating controls</strong>: isolate the system on its own network segment, restrict who can reach it, add extra monitoring, and record a time-limited exception with an owner.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose five devices or programs you use and write down the version of each.</li>
<li>Check the maker's site or the program's update feature for the latest version and mark which are behind.</li>
<li>Rank the out-of-date items using the four questions in the table.</li>
<li>Write a target date for each fix and who will do it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Vulnerability management is a repeating cycle: inventory, discover, prioritise, fix, verify, report.</li>
<li>Prioritise by exposure, active use by criminals and asset value, not by score alone.</li>
<li>Test and back up before patching.</li>
<li>Use compensating controls and written exceptions when patching is impossible.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Least privilege, role-based access and multi-factor authentication',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to design access so each person has only the rights they need, and protect accounts with multi-factor authentication (MFA). This matters because stolen or misused accounts are involved in a large share of breaches, and limited rights keep a single mistake from becoming a disaster.</p>
<h3>Least privilege</h3>
<p>The <strong>principle of least privilege</strong> says every person, program and account should have only the minimum access needed to do its job, for only as long as needed. A teacher does not need to see payroll. An intern does not need to delete the donor database. An accountant does not need administrator rights on the computer.</p>
<h3>Role-based access control</h3>
<p>Instead of giving rights person by person, define <strong>roles</strong> and give each role a set of permissions. Then place people into roles.</p>
<table>
<thead><tr><th>Role</th><th>Can do</th><th>Cannot do</th></tr></thead>
<tbody>
<tr><td>Teacher</td><td>Enter and view marks for own classes</td><td>Change fees or see other classes' private notes</td></tr>
<tr><td>Bursar</td><td>View and record fee payments</td><td>Change exam marks</td></tr>
<tr><td>IT officer</td><td>Manage accounts and systems</td><td>Approve payments (separation of duties)</td></tr>
<tr><td>Volunteer</td><td>Use shared forms</td><td>Download complete beneficiary lists</td></tr>
</tbody>
</table>
<p><strong>Separation of duties</strong> means no single person controls a whole sensitive process. The person who requests a payment should not be the one who approves it.</p>
<h3>Account lifecycle</h3>
<ol>
<li><strong>Joiner:</strong> create the account with the role's rights after written approval.</li>
<li><strong>Mover:</strong> when someone changes job, remove old rights, not only add new ones.</li>
<li><strong>Leaver:</strong> disable accounts the same day the person leaves, and collect devices.</li>
<li><strong>Review:</strong> every quarter, ask data owners to confirm each person's access is still right.</li>
</ol>
<h3>Privileged accounts</h3>
<p>Administrator accounts are the most valuable to criminals. Keep few of them, use a separate admin account for admin tasks only, never browse the web or open e-mail with it, protect it with the strongest MFA, and log what it does. Never share one admin password among several people.</p>
<h3>Multi-factor authentication</h3>
<p>MFA requires two or more kinds of proof: something you <strong>know</strong> (password), something you <strong>have</strong> (phone app, security key) and something you <strong>are</strong> (fingerprint). Even if a password is stolen, the thief still lacks the second factor.</p>
<table>
<thead><tr><th>Method</th><th>Strength</th><th>Note</th></tr></thead>
<tbody>
<tr><td>Security key or passkey</td><td>Very strong</td><td>Resists fake log-in pages</td></tr>
<tr><td>Authenticator app code</td><td>Strong</td><td>Works without network signal</td></tr>
<tr><td>SMS code</td><td>Better than none</td><td>Can be intercepted if a SIM card is swapped</td></tr>
<tr><td>Approval prompt on phone</td><td>Good</td><td>Staff must refuse prompts they did not start</td></tr>
</tbody>
</table>
<p>Switch MFA on first for e-mail, cloud storage, finance systems, social media pages and every admin account. Keep backup codes in a safe place.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write the roles for a small organisation you know and list what each can and cannot do.</li>
<li>Choose one process, such as paying a supplier, and split it across two people.</li>
<li>Turn on MFA for your own e-mail account if it is not already on, and store the backup codes safely.</li>
<li>Write a four-step leaver checklist.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Least privilege gives only the access needed, for as long as needed.</li>
<li>Roles and separation of duties keep access manageable and safe.</li>
<li>Manage joiners, movers and leavers, and review access regularly.</li>
<li>MFA adds a second proof; protect admin accounts most of all.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which of these is the best description of "hardening"?
- Making a computer physically heavier
- Installing as many security programs as possible
* Reducing the attack surface by removing what is unneeded and tightening settings
- Deleting all user accounts
= Hardening shrinks the number of ways in by removing, disabling and restricting.

Q: A finance officer uses her administrator account to read e-mail and browse the web. What is the main problem?
- Administrator accounts cannot open e-mail
* Malware run under that account would have powerful rights
- It makes the internet slower
- Nothing, it is recommended
= Daily work should use a standard account; admin rights only for admin tasks.

Q: When hardening SSH on a Linux server, which two options are widely recommended? (choose all that apply)
* Use key pairs instead of passwords
* Disable direct root login
- Allow any user to log in as root with a short password
- Open SSH to every service on the machine
= Keys resist password guessing, and avoiding direct root login adds accountability and limits damage.

Q: A flaw with a medium score is on your internet-facing web server and agencies report criminals are actively using it. A high-scored flaw is on an isolated test computer. Which do you usually fix first?
- The test computer, because the score is higher
* The web server, because exposure and active use raise the real risk
- Neither, wait for the next year
- Whichever has the longer name
= Prioritise by exposure, active exploitation and asset value, not by score alone.

Q: A system cannot be patched without breaking an essential program. What should you do?
- Leave it as it is and tell nobody
- Remove it from the inventory
* Isolate and monitor it, and record a time-limited exception with an owner
- Switch off the firewall
= Compensating controls and a documented exception manage the remaining risk.

Q: The bursar can both request and approve payments. Which principle is broken?
- Defence in depth
- Patch management
* Separation of duties
- Data classification
= One person should not control a whole sensitive process.

Q: A staff member moves from the accounts office to the library. What should happen to her access?
- Keep the accounts rights in case she is needed
* Remove the old rights and add only those needed in the library
- Give her administrator rights
- Nothing, access is for life
= Movers must lose rights they no longer need, otherwise rights pile up over time.

Q: Which second factor is generally the most resistant to fake log-in pages?
- A text message code
- A security question
* A hardware security key or passkey
- A longer password
= Security keys and passkeys check the real website, so a fake page cannot use them.
QUIZ,
        ],
        [
            'title'   => 'Module 3: Network and endpoint defence',
            'summary' => 'Divide networks into safe zones, use firewalls and intrusion detection wisely, and defend against phishing and malware at the e-mail and endpoint layers.',
            'lessons' => [
                [
                    'title'   => 'Network segmentation and firewalls',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to sketch a segmented network for a small organisation and write sensible firewall rules for it. This matters because on a flat network, one infected laptop can reach every other device, including servers and finance computers.</p>
<h3>Why segment?</h3>
<p><strong>Network segmentation</strong> divides a network into separate zones, with controlled gates between them. If one zone is compromised, the damage is contained. It also protects weaker devices, such as printers, cameras and visitors' phones, from touching sensitive systems.</p>
<h3>A simple design for a school or NGO</h3>
<table>
<thead><tr><th>Zone</th><th>Contains</th><th>Rule of thumb</th></tr></thead>
<tbody>
<tr><td>Staff</td><td>Office laptops and desktops</td><td>Can reach servers they need and the internet</td></tr>
<tr><td>Servers</td><td>File server, accounting, learner database</td><td>Reachable only from staff on needed ports; strict control outbound</td></tr>
<tr><td>Guest and learner Wi-Fi</td><td>Visitors, student devices</td><td>Internet only, cannot reach staff or servers</td></tr>
<tr><td>Devices (IoT)</td><td>CCTV, smart TVs, printers</td><td>Own zone, no route to servers</td></tr>
<tr><td>Management</td><td>Router, switch and access point admin pages</td><td>Reachable only from the IT officer's computer</td></tr>
</tbody>
</table>
<p>Segments can be made with separate physical switches, or with <strong>VLANs</strong> (virtual LANs) on managed switches, plus a router or firewall that decides what may cross between them. Even an inexpensive router with a guest network feature is a useful start.</p>
<h3>How firewalls decide</h3>
<p>A <strong>firewall</strong> compares each connection with an ordered list of rules and allows or blocks it. Rules look at the source, destination, port and protocol. A <strong>stateful</strong> firewall remembers which connections began from inside, so it allows the replies back in but blocks unrequested incoming traffic.</p>
<ol>
<li><strong>Default deny:</strong> block everything not explicitly allowed.</li>
<li><strong>Allow only what is needed:</strong> for example, staff to the file server on its file-sharing port, and everyone to the internet on web ports.</li>
<li><strong>Control outbound traffic too,</strong> because malware needs to contact its controller. Blocking unusual outgoing connections from servers is valuable.</li>
<li><strong>Document each rule:</strong> why it exists, who asked, and when to review it.</li>
<li><strong>Review regularly</strong> and delete rules nobody can explain.</li>
</ol>
<h3>Other firewall types</h3>
<ul>
<li><strong>Host firewall:</strong> runs on each computer, a second layer inside the network.</li>
<li><strong>Next-generation firewall:</strong> adds awareness of applications, content filtering and sometimes intrusion prevention.</li>
<li><strong>Web application firewall:</strong> sits in front of a website to filter malicious web requests.</li>
</ul>
<h3>Common mistakes</h3>
<p>Do not forward ports from the internet to internal machines without strong reason, because each forwarded service is exposed to the whole world. Do not leave the router's default admin password unchanged. Do not make a rule "allow all" to solve a problem temporarily and forget to remove it.</p>
<h3>Try it yourself</h3>
<ol>
<li>Draw your home or office network on paper with the five zones above.</li>
<li>Write three firewall rules in plain English, such as "Guest Wi-Fi to staff network: block".</li>
<li>If your router has a guest network, switch it on and connect a phone to it.</li>
<li>From the guest network, confirm that you cannot open the router's admin page, then change that default admin password if it was not changed.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Segmentation limits how far an incident can spread.</li>
<li>Separate staff, servers, guests, devices and management.</li>
<li>Firewalls use ordered rules; default deny and documented rules are best practice.</li>
<li>Control outbound traffic and avoid unnecessary port forwarding.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Intrusion detection, secure remote access and Wi-Fi',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what intrusion detection and prevention systems do, and secure remote access and wireless networks. This matters because firewalls only block what they are told to block; detection shows you the activity that slips through, and remote access and Wi-Fi are among the most exposed entrances.</p>
<h3>IDS and IPS</h3>
<p>An <strong>intrusion detection system (IDS)</strong> watches network traffic or computer activity and raises an alert when something looks suspicious. An <strong>intrusion prevention system (IPS)</strong> does the same but can also block the traffic automatically. Both use two main methods:</p>
<table>
<thead><tr><th>Method</th><th>How it works</th><th>Strength</th><th>Weakness</th></tr></thead>
<tbody>
<tr><td>Signature-based</td><td>Matches known patterns of bad activity</td><td>Accurate for known problems</td><td>Misses new, unknown methods</td></tr>
<tr><td>Anomaly-based</td><td>Compares with a normal baseline and flags differences</td><td>Can reveal new problems</td><td>More false alarms</td></tr>
</tbody>
</table>
<p>A <strong>network IDS</strong> watches traffic crossing a point in the network. A <strong>host IDS</strong> runs on one computer and watches files, processes and logins. Free open-source tools exist, but they need tuning: an alert system that produces hundreds of false alarms a day will soon be ignored. Start with a few high-value rules and improve them.</p>
<h3>False positives and false negatives</h3>
<p>A <strong>false positive</strong> is an alert for harmless activity. A <strong>false negative</strong> is a real attack that raised no alert. Tuning reduces the first without increasing the second. Always investigate before blocking, so you do not cut off a real user by mistake.</p>
<h3>Secure remote access</h3>
<p>Staff increasingly work from home or travel. Exposed remote desktop or admin services are a favourite target for criminals who scan the internet for them.</p>
<ol>
<li>Do not expose remote desktop or admin pages directly to the internet.</li>
<li>Use a <strong>VPN</strong> or a trusted secure access service, and require MFA.</li>
<li>Give remote users only the access they need, not the entire network.</li>
<li>Log all remote sessions and review failed attempts.</li>
<li>Require updated, encrypted devices before they connect.</li>
</ol>
<h3>Securing Wi-Fi</h3>
<ul>
<li>Use <strong>WPA3</strong> if all devices support it, otherwise <strong>WPA2 with AES</strong> and a long passphrase. Never use open networks or the old WEP.</li>
<li>Change the router's default administrator name and password and turn off remote administration from the internet.</li>
<li>Keep router firmware updated.</li>
<li>Run a separate guest network and change its password regularly.</li>
<li>For staff, a business network with individual log-ins is better than one shared passphrase that everyone knows.</li>
<li>Walk around the premises looking for unknown access points that someone may have plugged in.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Log in to your own router's admin page from inside your network.</li>
<li>Note the Wi-Fi security mode, and change it to WPA2 or WPA3 if it is lower.</li>
<li>Check that remote administration from the internet is off.</li>
<li>Write down the firmware version and search the maker's site for the latest one.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>IDS detects and alerts; IPS can also block automatically.</li>
<li>Signature methods find known problems; anomaly methods find unusual behaviour but give more false alarms.</li>
<li>Protect remote access with VPN or secure services plus MFA, never with exposed admin ports.</li>
<li>Strong Wi-Fi encryption, updated firmware and a guest network are the basics.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Defending against phishing and malware',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe how phishing and malware reach organisations and set up layered defences against them. This matters because e-mail and downloaded files are still the most common way for trouble to enter. We explain recognition and defence only.</p>
<h3>How phishing works</h3>
<p><strong>Phishing</strong> is a message that pretends to be from a trusted sender to trick the reader into giving a password, paying money or opening a harmful file. Variants include:</p>
<ul>
<li><strong>Spear phishing:</strong> carefully written for one person, using real names and facts.</li>
<li><strong>Business e-mail compromise:</strong> a fake "director" asks the finance officer to pay an urgent invoice or change bank details.</li>
<li><strong>Smishing and vishing:</strong> the same trick by text message or phone call, including fake mobile money messages.</li>
</ul>
<h3>Warning signs a trained person can spot</h3>
<ul>
<li>Urgency or threats ("your account closes today").</li>
<li>A sender address that is slightly different from the real one.</li>
<li>Links whose real destination, seen by hovering, does not match the text.</li>
<li>Unexpected attachments, or a request to enable macros.</li>
<li>Requests for secrecy, new payment details, or gift cards.</li>
</ul>
<h3>What malware is</h3>
<p><strong>Malware</strong> means harmful software. <strong>Ransomware</strong> locks or encrypts files and demands payment. <strong>Spyware</strong> and <strong>information stealers</strong> collect passwords and files. A <strong>trojan</strong> pretends to be useful. Malware may come from attachments, fake downloads, infected USB drives or unpatched systems.</p>
<h3>Layered defences</h3>
<table>
<thead><tr><th>Layer</th><th>Control</th></tr></thead>
<tbody>
<tr><td>E-mail</td><td>Spam and malware filtering; mark messages from outside the organisation; set SPF, DKIM and DMARC records on your domain so others can detect forged mail</td></tr>
<tr><td>Users</td><td>Regular short training and safe test messages; one-click "report phishing" button</td></tr>
<tr><td>Browser and web</td><td>Safe-browsing protection, DNS filtering, block known bad sites</td></tr>
<tr><td>Endpoint</td><td>Updated anti-malware or endpoint detection tools, application allow-lists, macros disabled by default</td></tr>
<tr><td>Accounts</td><td>MFA, so a stolen password alone is not enough</td></tr>
<tr><td>Process</td><td>Verify payment changes by a phone call to a known number</td></tr>
<tr><td>Recovery</td><td>Offline backups so ransomware does not hold you hostage</td></tr>
</tbody>
</table>
<h3>Detecting malware conceptually</h3>
<p>Anti-malware tools use signatures (known samples), behaviour monitoring (a program suddenly encrypting hundreds of files) and reputation (a file nobody has seen before). Defenders also look for signs such as a computer that suddenly slows, unexpected new programs, or accounts logging in from strange places. When you suspect a problem, <strong>disconnect the device from the network</strong>, do not switch it off, and report it. The incident lessons later in this course explain why.</p>
<h3>Never pay and never open</h3>
<p>Do not open suspicious files "just to see". If a message seems important, contact the sender by a separate, trusted route.</p>
<h3>Try it yourself</h3>
<ol>
<li>Find a phishing example described by a security agency, or write your own imaginary one.</li>
<li>Mark at least four warning signs in it.</li>
<li>Write the exact steps a staff member should follow on receiving it, including who to report it to.</li>
<li>Draft a rule that bank detail changes must be confirmed by phone with a known number.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Phishing tricks people; business e-mail compromise targets payments.</li>
<li>Malware includes ransomware, spyware and trojans.</li>
<li>Defend in layers: filtering, training, endpoint tools, MFA, process checks and backups.</li>
<li>On suspicion, disconnect from the network and report quickly.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the main benefit of network segmentation?
- It makes the internet faster
- It removes the need for passwords
* It limits how far an incident can spread
- It replaces backups
= Separate zones with controlled gates contain damage when one device is compromised.

Q: Which firewall approach is recommended as a starting point?
- Allow everything and block problems as they appear
* Default deny, then allow only what is needed and document each rule
- Forward all ports to the server for convenience
- Turn the firewall off during working hours
= Default deny with documented exceptions is the safest and most manageable approach.

Q: Guest Wi-Fi should normally be able to reach:
- The finance server
- The router admin page
* The internet only
- The staff network
= Visitors' devices are untrusted, so they get internet access without a route to internal systems.

Q: A system raises hundreds of harmless alerts a day. What is this problem called, and what is the fix?
- False negatives; delete the system
* False positives; tune the rules and start with high-value ones
- Signature failure; buy a bigger firewall
- Segmentation failure; remove all alerts
= Too many false positives cause alert fatigue. Tuning keeps alerts meaningful.

Q: Which are good practices for secure remote access? (choose all that apply)
* Use a VPN or secure access service with MFA
* Give remote users only the access they need
- Expose the remote desktop port directly to the internet
- Share one remote login among the team
= Exposed services are heavily targeted, so access should be through protected, individual, logged sessions.

Q: A "director" e-mails the finance officer asking her to urgently pay a new bank account and keep it secret. What is the best response?
- Pay at once to avoid trouble
* Confirm by phone using a known number before doing anything
- Reply asking for the bank PIN
- Forward it to all staff
= Urgency and secrecy are warning signs. Verify through a separate trusted route.

Q: A computer is suspected of having ransomware. What is the right first reaction?
- Switch it off and throw it away
- Pay the ransom quickly
* Disconnect it from the network and report to the IT officer
- Plug in a USB drive to copy the files
= Disconnecting stops the spread while keeping evidence in memory for investigation.
QUIZ,
        ],
        [
            'title'   => 'Module 4: Logging, monitoring and recovery',
            'summary' => 'Collect and protect the right logs, understand how a SIEM produces alerts, and build backups that you have actually tested.',
            'lessons' => [
                [
                    'title'   => 'Logging: what to collect and how to protect it',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to decide which logs an organisation needs, keep them reliably and protect them from tampering. This matters because without logs, you cannot tell what happened during an incident, and you may not even know that one occurred.</p>
<h3>What a log is</h3>
<p>A <strong>log</strong> is a time-stamped record of an event: a person signed in, a file was opened, a setting changed, a connection was blocked. Logs are the memory of your systems. They help with three jobs: <strong>detecting</strong> problems, <strong>investigating</strong> what happened, and <strong>proving</strong> facts afterwards.</p>
<h3>Which logs matter most</h3>
<table>
<thead><tr><th>Source</th><th>Useful events</th></tr></thead>
<tbody>
<tr><td>Identity and e-mail</td><td>Successful and failed sign-ins, MFA prompts, password resets, new mailbox rules, sign-ins from new countries</td></tr>
<tr><td>Windows and Linux systems</td><td>Log-ins, new accounts, rights changes, service installs, scheduled tasks, system errors</td></tr>
<tr><td>Firewall and router</td><td>Blocked and allowed connections, configuration changes, VPN sessions</td></tr>
<tr><td>Endpoint protection</td><td>Malware found, protection switched off, quarantined files</td></tr>
<tr><td>Servers and applications</td><td>Administrator actions, errors, access to sensitive records</td></tr>
<tr><td>Cloud storage</td><td>Sharing changes, mass downloads or deletions</td></tr>
</tbody>
</table>
<p>Do not try to log everything. Start with the events that answer the questions "Who did what, from where, and when?" for your most valuable assets, then expand.</p>
<h3>Getting logs right</h3>
<ol>
<li><strong>Synchronise the clocks.</strong> Every system should use the same time source, otherwise you cannot put events in order. Record the time zone (East Africa Time is UTC+3).</li>
<li><strong>Turn logging on.</strong> Many systems log little by default. Enable auditing for log-ins and account changes.</li>
<li><strong>Centralise.</strong> Send copies of logs to one place so you can search across systems.</li>
<li><strong>Protect.</strong> Store the central copy where ordinary users and attackers who control one machine cannot delete or edit it. Limit who can read it, because logs may contain personal data.</li>
<li><strong>Retain.</strong> Keep logs long enough to investigate; many incidents are discovered weeks or months later. Decide retention periods in policy, balancing storage cost and legal duties.</li>
<li><strong>Review.</strong> A log nobody reads protects nobody. Schedule regular checks and set alerts for key events.</li>
</ol>
<h3>Privacy and good practice</h3>
<p>Log what you need, avoid recording passwords or full card numbers, and tell staff that monitoring happens, usually in the acceptable use policy. Monitoring should be proportionate and for a stated security purpose, which is also consistent with data protection principles.</p>
<h3>Common mistakes</h3>
<ul>
<li>Logs that overwrite themselves after a few hours because the file size is too small.</li>
<li>Different clocks on different machines.</li>
<li>Logs stored only on the computer that was attacked.</li>
<li>Collecting huge volumes with no plan to read them.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>On a Windows computer, press <strong>Windows key + R</strong>, type <code>eventvwr</code> and press Enter to open Event Viewer.</li>
<li>Open <strong>Windows Logs</strong>, then <strong>Security</strong>, and find a log-on event. Note the time, account and result.</li>
<li>On Linux, view recent authentication messages in the system log or with the journal tool.</li>
<li>Write a list of the ten events you would alert on in a small organisation.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Logs record who did what, when and from where.</li>
<li>Start with identity, systems, firewall, endpoint and cloud logs.</li>
<li>Synchronise clocks, centralise, protect, retain and review.</li>
<li>Monitor proportionately and tell staff.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'SIEM concepts: from events to alerts',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what a SIEM does, how detection rules work and how to handle an alert in a calm, repeatable way. This matters because even a small organisation can generate more events in a day than any person could read.</p>
<h3>What is a SIEM?</h3>
<p>A <strong>Security Information and Event Management</strong> system (SIEM) collects logs from many sources, <strong>normalises</strong> them into a common format, stores them for search, <strong>correlates</strong> events across systems, and raises <strong>alerts</strong> when rules or models match. Cloud services, commercial products and free open-source tools all work on this idea. You do not need an expensive product to start; a well-organised central log store with a few alerts is already valuable.</p>
<h3>How it works</h3>
<ol>
<li><strong>Collect:</strong> agents or log forwarders send events to the SIEM.</li>
<li><strong>Parse and normalise:</strong> fields such as time, user, source address and result are extracted.</li>
<li><strong>Enrich:</strong> add context, for example whether an address is on a list of known bad sources, or which department a user belongs to.</li>
<li><strong>Detect:</strong> rules compare events with patterns of concern.</li>
<li><strong>Alert and triage:</strong> an analyst reviews alerts and decides what is real.</li>
<li><strong>Respond and learn:</strong> confirmed problems become incidents, and the lessons tune the rules.</li>
</ol>
<h3>Examples of detection ideas</h3>
<table>
<thead><tr><th>Idea</th><th>What it may mean</th></tr></thead>
<tbody>
<tr><td>Many failed sign-ins for one account, then a success</td><td>Password guessing that worked</td></tr>
<tr><td>Sign-in from Kampala and, ten minutes later, from another continent</td><td>Impossible travel: stolen credentials</td></tr>
<tr><td>New administrator account created at night</td><td>Possible persistence by an intruder</td></tr>
<tr><td>Antivirus switched off on one computer</td><td>Attempt to avoid detection</td></tr>
<tr><td>A user downloads thousands of files within minutes</td><td>Data theft or a compromised account</td></tr>
<tr><td>New e-mail forwarding rule to an outside address</td><td>Mailbox takeover</td></tr>
</tbody>
</table>
<h3>Triage: handling an alert</h3>
<ol>
<li><strong>Read</strong> the alert and what rule fired.</li>
<li><strong>Gather context:</strong> who is the user, which device, what time, what else happened around it.</li>
<li><strong>Decide:</strong> false positive, benign but unusual, or a real incident.</li>
<li><strong>Act:</strong> close with a note, ask the user a question by a trusted route, or escalate and start incident response.</li>
<li><strong>Record</strong> your reasoning so others and your future self can learn.</li>
</ol>
<h3>Alert quality</h3>
<p>Too many weak alerts cause <strong>alert fatigue</strong>, where genuine warnings are missed. For every rule write a short description, a severity, who handles it, and what to do. Measure how many alerts were useful and retire or tune the rest. Also test your detection: with permission, make a harmless planned change, such as creating a test account, and check that the alert fires.</p>
<h3>Try it yourself</h3>
<ol>
<li>List five detection ideas that fit your own organisation, using the table as a guide.</li>
<li>For each, write the log source you would need.</li>
<li>Pick one and write a four-line triage guide: what to check, who to contact, when to escalate.</li>
<li>Note one reason it could be a false positive, such as a staff member travelling.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A SIEM collects, normalises, correlates and alerts on logs.</li>
<li>Simple, well-chosen rules beat many noisy ones.</li>
<li>Triage means gathering context before deciding.</li>
<li>Tune rules and test detections regularly to avoid alert fatigue.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Backups and recovery that actually work',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to design a backup plan with clear recovery targets and test that it works. This matters because backups are your last defence against ransomware, accidental deletion, fire, theft and failed disks, and a backup that has never been restored is only a hope.</p>
<h3>The 3-2-1 rule and beyond</h3>
<p>Keep <strong>three</strong> copies of important data, on <strong>two</strong> different kinds of storage, with <strong>one</strong> copy off-site. Modern advice adds that at least one copy should be <strong>offline or immutable</strong>, meaning it cannot be changed or deleted for a set time. Ransomware tries to find and encrypt backups connected to the network.</p>
<h3>Two numbers every plan needs</h3>
<table>
<thead><tr><th>Term</th><th>Meaning</th><th>Example</th></tr></thead>
<tbody>
<tr><td>RPO, recovery point objective</td><td>How much recent data you can afford to lose</td><td>One day: back up every night</td></tr>
<tr><td>RTO, recovery time objective</td><td>How long you can be without the system</td><td>Four hours for the fee system</td></tr>
</tbody>
</table>
<p>Ask each data owner these questions, then choose methods and budget to meet the answers. Mobile money records may need hourly backups, while a photo archive needs weekly ones.</p>
<h3>Types of backup</h3>
<ul>
<li><strong>Full:</strong> a complete copy. Simple to restore but slow and large.</li>
<li><strong>Incremental:</strong> only changes since the last backup. Fast and small, but restore needs the full copy and every increment.</li>
<li><strong>Differential:</strong> changes since the last full backup. A middle way.</li>
<li><strong>Snapshot and replication:</strong> point-in-time images of servers or cloud disks; useful, but keep a separate protected copy as well.</li>
</ul>
<h3>Building the plan</h3>
<ol>
<li>List what must be backed up: files, databases, e-mail, system settings, the configuration of routers and firewalls.</li>
<li>Set RPO and RTO for each.</li>
<li>Choose the method: external drives rotated and stored in another building, a cloud backup service, or both. Encrypt backups and store the keys separately.</li>
<li>Automate. Manual backups are forgotten.</li>
<li>Protect backup accounts with MFA and separate credentials from everyday administrator accounts.</li>
<li>Monitor: get a message when a backup fails.</li>
<li><strong>Test restores</strong> regularly, at least every quarter, including one complete restoration of a whole system onto spare hardware.</li>
</ol>
<h3>Disaster recovery in a place with power cuts</h3>
<p>Plan for the real environment: a UPS to allow safe shutdown, a generator or solar backup for key equipment, mobile data as a fallback for cloud backups, and a printed recovery guide in a folder, because the instructions stored on the failed server are no help. Write who does what, in which order, and the phone numbers of suppliers.</p>
<h3>Try it yourself</h3>
<ol>
<li>List five types of data in your organisation and set an RPO and RTO for each.</li>
<li>Check whether you currently have three copies, two media, one off-site.</li>
<li>Choose one folder and restore a single file from your backup to a different location.</li>
<li>Write down how long it took and what surprised you.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Follow 3-2-1 and keep at least one copy offline or immutable.</li>
<li>RPO is acceptable data loss; RTO is acceptable downtime.</li>
<li>Automate, encrypt, monitor and test restores.</li>
<li>Recovery plans must work during power and internet failures.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Why do all systems need synchronised clocks for logging?
- To save electricity
* So events from different systems can be put in the correct order
- To make logs smaller
- Because clocks are required by law for passwords
= Without a shared time source, an investigation cannot build a reliable timeline.

Q: Where should the central copy of logs be kept?
- Only on the computer being monitored
* Where ordinary users and a compromised machine cannot delete or edit it
- On a public website
- In each user's Downloads folder
= A protected central copy keeps evidence even if an attacker controls one system.

Q: In a SIEM, what does "correlation" mean?
- Deleting old logs
* Linking events from different sources to reveal a pattern
- Converting logs to printed paper
- Blocking every user
= Correlation connects events, such as a failed-then-successful sign-in plus a new forwarding rule.

Q: A user signs in from Kampala and ten minutes later from another continent. Which detection idea fits?
- Backup failure
* Impossible travel, a sign that credentials may be stolen
- Normal behaviour with no follow-up needed
- A firewall update
= The distance cannot be travelled in ten minutes, so the sign-in deserves investigation (though a VPN can cause false alarms).

Q: Your SIEM generates hundreds of unimportant alerts and analysts begin to ignore them. What is the best remedy?
- Turn off the SIEM
* Tune or retire weak rules and focus on high-value detections
- Hire more people to ignore alerts
- Make all alerts critical
= Alert fatigue is solved by quality rules with owners and clear actions.

Q: The fee system can be offline for four hours at most, and you can lose at most one day of data. What are these numbers called?
* RTO four hours, RPO one day
- RPO four hours, RTO one day
- Both are RPO
- Both are RTO
= RTO is acceptable downtime, and RPO is acceptable data loss.

Q: Which is the best protection of backups against ransomware?
- Keeping them on a drive permanently plugged into the main server
* Keeping at least one copy offline or immutable and protected with separate credentials
- Naming the folder "Backup"
- Backing up only once a year
= Ransomware targets connected backups, so offline or immutable copies survive.

Q: How can you be sure a backup is useful?
- The backup software says "success"
* Test restores regularly, including a full system restore
- Count the number of files
- Ask the vendor
= Only a successful restore proves the backup is complete and usable.
QUIZ,
        ],
        [
            'title'   => 'Module 5: Incident response, forensics and the law',
            'summary' => 'Respond to incidents in clear phases, handle evidence correctly, and meet legal and awareness responsibilities including data protection duties.',
            'lessons' => [
                [
                    'title'   => 'The incident response lifecycle and playbooks',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to lead an incident through its phases and write a short playbook your team can follow under pressure. This matters because the first hours of an incident are stressful, and people who have rehearsed make far fewer mistakes than people who improvise.</p>
<h3>Event, alert, incident</h3>
<p>An <strong>event</strong> is anything that happens on a system. An <strong>alert</strong> is an event that deserves attention. An <strong>incident</strong> is a confirmed event that harms, or threatens to harm, confidentiality, integrity or availability. Many alerts are closed without becoming incidents.</p>
<h3>The phases</h3>
<ol>
<li><strong>Preparation.</strong> Do this before anything happens: write the plan, name the team, keep contact lists (including a printed copy), set up logging and backups, and run practice exercises.</li>
<li><strong>Detection and analysis.</strong> Confirm it is real, decide what is affected, judge how serious it is, and start a written log of actions with times.</li>
<li><strong>Containment.</strong> Stop the harm from spreading. Short-term actions include disconnecting a device from the network, disabling a compromised account or blocking a malicious address. Long-term containment may mean isolating a whole segment.</li>
<li><strong>Eradication.</strong> Remove the cause: delete malware, close the weakness that was used, reset passwords, rebuild a system from a trusted image if its integrity cannot be trusted.</li>
<li><strong>Recovery.</strong> Restore from clean backups, return systems to service in a planned order, and watch closely for signs that the problem returns.</li>
<li><strong>Lessons learned.</strong> Within a couple of weeks, hold a blame-free review: what happened, what worked, what did not, and which changes will be made, with owners and dates.</li>
</ol>
<h3>Roles</h3>
<table>
<thead><tr><th>Role</th><th>Responsibility</th></tr></thead>
<tbody>
<tr><td>Incident lead</td><td>Makes decisions and keeps the timeline</td></tr>
<tr><td>Technical responders</td><td>Investigate and contain</td></tr>
<tr><td>Communications</td><td>Informs staff, partners, donors, the public and authorities</td></tr>
<tr><td>Legal or data protection contact</td><td>Judges notification duties</td></tr>
<tr><td>Management sponsor</td><td>Approves costly decisions, such as shutting down a system</td></tr>
</tbody>
</table>
<h3>Severity levels</h3>
<p>Define levels in advance, for example <strong>low</strong> (one device, no data), <strong>medium</strong> (several devices or limited data), <strong>high</strong> (confidential data or major service loss), <strong>critical</strong> (widespread or life-affecting). Each level says who must be called and how fast.</p>
<h3>A short playbook for ransomware</h3>
<ol>
<li>Disconnect affected devices from the network but leave them powered on.</li>
<li>Tell the incident lead and start the log.</li>
<li>Identify what is encrypted and check that backups are untouched.</li>
<li>Disable suspected accounts and reset administrator passwords from a clean device.</li>
<li>Do not pay without legal and management advice; payment does not guarantee recovery.</li>
<li>Rebuild and restore from clean backups, then verify.</li>
<li>Assess whether personal data was exposed and decide on notifications.</li>
</ol>
<h3>Practise</h3>
<p>A <strong>tabletop exercise</strong> is a meeting where the team talks through an imagined incident step by step. It costs nothing and reveals missing phone numbers, unclear authority and untested backups.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write a one-page incident plan for an organisation you know: team members, phone numbers, severity levels.</li>
<li>Write a seven-step playbook for stolen laptops, using the phases above.</li>
<li>Run a ten-minute tabletop exercise with a friend: "The finance laptop is missing."</li>
<li>List three gaps you discovered.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The phases are preparation, detection and analysis, containment, eradication, recovery and lessons learned.</li>
<li>Roles, severity levels and a printed contact list are prepared in advance.</li>
<li>Containment limits damage; eradication removes the cause; recovery restores service safely.</li>
<li>Practice with tabletop exercises and review without blame.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Forensics basics and evidence handling',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to preserve digital evidence correctly so it can support an investigation, a disciplinary hearing or a police report. This matters because well-meant actions, such as restarting a computer or opening files, can destroy the very evidence you need.</p>
<h3>What digital forensics is</h3>
<p><strong>Digital forensics</strong> is the careful collection and study of digital evidence to find out what happened, when, how and by whom. A small organisation will seldom do deep forensic analysis itself, but every IT officer should know how to <strong>preserve</strong> evidence until specialists or the police take over.</p>
<h3>Principles</h3>
<ul>
<li><strong>Preserve first.</strong> Avoid changing the original. Work on copies wherever possible.</li>
<li><strong>Document everything.</strong> Who did what, when, with which tools, and why.</li>
<li><strong>Maintain the chain of custody.</strong> Record every person who held the evidence, from collection to storage to hand-over.</li>
<li><strong>Be accurate and impartial.</strong> Report facts, not guesses.</li>
<li><strong>Stay within authority.</strong> Act only with permission, under policy and law.</li>
</ul>
<h3>Order of volatility</h3>
<p>Some evidence disappears faster than other evidence. Collect the most fragile first.</p>
<table>
<thead><tr><th>Order</th><th>Evidence</th><th>Lasts</th></tr></thead>
<tbody>
<tr><td>1</td><td>Memory (RAM) and running processes</td><td>Lost when power is cut</td></tr>
<tr><td>2</td><td>Network connections and temporary state</td><td>Seconds to minutes</td></tr>
<tr><td>3</td><td>Disk contents and system files</td><td>Until overwritten</td></tr>
<tr><td>4</td><td>Logs, including remote and cloud logs</td><td>Until they roll over or are deleted</td></tr>
<tr><td>5</td><td>Backups and archives</td><td>Long term</td></tr>
</tbody>
</table>
<p>This is why a suspected computer is usually <strong>disconnected, not switched off</strong>: shutting down loses memory evidence, while disconnecting cuts the attacker's access. If you must power off, record that decision and why.</p>
<h3>A careful first response</h3>
<ol>
<li>Photograph or write down what is on the screen and the state of the device.</li>
<li>Disconnect from the network (unplug the cable or switch off Wi-Fi).</li>
<li>Do not browse, install tools or open files on the affected machine.</li>
<li>Label the device with a tag showing date, time, your name and the case number.</li>
<li>Store it in a locked place and record who has the key.</li>
<li>Copy relevant logs to protected storage and note the time.</li>
<li>Call your incident lead and, where the law requires, the police.</li>
</ol>
<h3>Hashes and copies</h3>
<p>Specialists make a <strong>forensic image</strong>, an exact copy of a disk, and compute a <strong>hash</strong>, a short fingerprint produced by a mathematical function. If the hash of the copy equals the hash of the original, and the same hash is shown later, nobody has changed the data. A write blocker device stops a computer from altering a drive being copied.</p>
<h3>The chain of custody form</h3>
<p>For each item write: description and serial number, where and when collected, by whom, each transfer (from, to, date, time, reason), and storage location. Gaps in this record can make evidence unreliable.</p>
<h3>Legal care</h3>
<p>Search of personal devices and private messages raises privacy rights. Rely on your acceptable use policy and take advice before examining personal accounts. Keep evidence confidential, share on a need-to-know basis and follow the instructions of investigating authorities.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a chain of custody template with the fields above.</li>
<li>Practise on a harmless item, such as an old flash disk: label it and fill in the form for a pretend hand-over.</li>
<li>On your own computer, compute a hash of a small file (on Windows PowerShell, <code>Get-FileHash filename</code>), copy the file, and compare the two hashes.</li>
<li>Change one letter in the copy and compute the hash again to see how it changes.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Preserve evidence, work on copies and document every action.</li>
<li>Collect the most volatile evidence first; disconnect rather than power off.</li>
<li>Chain of custody records who held evidence and when.</li>
<li>Hashes prove a copy matches the original; respect privacy and legal authority.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Law, data protection and a security awareness programme',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe, in plain terms, the legal duties around personal data and computer misuse in Uganda, and plan an awareness programme that changes behaviour. This matters because a security failure can also be a legal failure, and because people are the layer that technology cannot fully replace. This lesson is general education, not legal advice; consult a qualified lawyer for your own situation.</p>
<h3>Personal data and the Data Protection and Privacy Act, 2019</h3>
<p>Uganda's Data Protection and Privacy Act of 2019 protects <strong>personal data</strong>, which is information about an identifiable living person: names, phone numbers, photos, health or school records, mobile money history. A <strong>data subject</strong> is the person the data is about. Organisations that collect or process the data take on duties. In plain terms the law expects you to:</p>
<ul>
<li>Collect personal data <strong>lawfully, fairly and for a clear purpose</strong>, usually with the person's consent or another proper reason.</li>
<li>Collect <strong>only what you need</strong> and keep it accurate and up to date.</li>
<li>Keep it <strong>no longer than necessary</strong>.</li>
<li>Be <strong>open</strong> with people about what you collect and why.</li>
<li>Protect it with <strong>reasonable security safeguards</strong>, which is where this course comes in.</li>
<li>Respect people's rights, such as asking to see their data, correct it, or object to some uses.</li>
<li>Take extra care with sensitive data, and with data about children.</li>
<li>Follow the rules for registering with the Personal Data Protection Office and for sending personal data to another country.</li>
</ul>
<p>The Act also expects an organisation to tell the authority and the people affected when a security breach exposes personal data. Check the current text and the Personal Data Protection Office guidance for exact timing and registration steps, because details can change.</p>
<h3>Computer misuse</h3>
<p>Ugandan law, including the Computer Misuse Act, makes it an offence to gain unauthorised access to computers or data, to interfere with systems, or to misuse data. For defenders the lesson is simple: <strong>get written permission</strong> before testing or scanning anything, test only what you own or are authorised to test, and stay within the agreed scope.</p>
<h3>Ethics for defenders</h3>
<ul>
<li>Respect confidentiality of what you see while doing your job.</li>
<li>Use access only for the purpose granted.</li>
<li>Report weaknesses responsibly to the owner instead of showing them off.</li>
<li>Be honest in reports, even when the mistake is yours.</li>
</ul>
<h3>Breach response and the law</h3>
<p>In every incident ask: <strong>was personal data involved?</strong> If yes, involve the data protection contact early, record what data and how many people, decide on notifying the authority and the affected people without delay, and tell people what to do to protect themselves, for example to change passwords.</p>
<h3>Security awareness that works</h3>
<ol>
<li><strong>Start with real risks:</strong> phishing, passwords, mobile money fraud, lost devices.</li>
<li><strong>Keep it short and regular:</strong> fifteen minutes monthly beats a long yearly lecture.</li>
<li><strong>Use local stories</strong> and simple language, in the languages staff use.</li>
<li><strong>Practise:</strong> safe simulated phishing messages followed by friendly coaching, not punishment.</li>
<li><strong>Make reporting easy</strong> and thank people who report.</li>
<li><strong>Measure:</strong> report rates, click rates, and incidents over time.</li>
<li><strong>Train special groups:</strong> finance staff, administrators and managers face different risks.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>List the personal data your organisation holds and where each type is stored.</li>
<li>For each, write why you need it and when you will delete it.</li>
<li>Draft a one-page breach checklist with the question about personal data at the top.</li>
<li>Plan three monthly fifteen-minute awareness sessions with a topic and activity for each.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The Data Protection and Privacy Act, 2019 expects lawful, fair, limited and secure handling of personal data and respect for people's rights.</li>
<li>Breaches that expose personal data have notification duties; check current guidance.</li>
<li>Never test systems without written permission.</li>
<li>Awareness programmes should be short, regular, practical and blame-free.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which phase of incident response is mainly about stopping the harm from spreading?
- Preparation
- Recovery
* Containment
- Lessons learned
= Containment limits damage, for example by isolating a device or disabling an account.

Q: A team discovers it has no contact list and nobody knows who may shut down the server. In which phase should this have been fixed?
* Preparation
- Eradication
- Recovery
- Containment
= Plans, roles and contact lists belong to preparation, before incidents happen.

Q: Which is the correct order for the main phases?
- Recovery, containment, preparation, analysis
* Preparation, detection and analysis, containment, eradication, recovery, lessons learned
- Eradication, preparation, recovery, containment
- Lessons learned, preparation, recovery
= The lifecycle begins with preparation and ends with learning that improves preparation.

Q: Why is a suspected computer usually disconnected from the network rather than switched off?
- Switching off is illegal
* Shutting down loses volatile evidence such as memory, while disconnecting cuts off the intruder
- Disconnecting repairs the malware
- The battery would be damaged
= Memory and running state are lost on power-off, so preserving them helps analysis.

Q: What does a chain of custody record? (choose all that apply)
* Who collected the evidence and when
* Each transfer of the evidence between people
* Where the evidence was stored
- The brand of the lunch the investigator ate
= Complete custody records show evidence was not altered or swapped.

Q: Two hash values of an original file and its copy are identical. What does this show?
- The file is safe from malware
- The file is encrypted
* The copy has exactly the same content as the original
- The file was deleted
= A matching hash is a fingerprint match, so the data are identical.

Q: In plain terms, which of these does Uganda's Data Protection and Privacy Act, 2019 expect from organisations holding personal data? (choose all that apply)
* Collect data lawfully and for a clear purpose
* Protect it with reasonable security safeguards
* Respect rights such as access and correction
- Keep everything forever in case it is useful
= Data should be limited, secure and kept no longer than necessary, with people's rights respected.

Q: A volunteer offers to "test" a neighbouring school's website for weaknesses to show the head teacher. What should defenders do?
- Go ahead, it is helpful
* Do not test any system without the owner's written permission
- Test only at night
- Publish the results first
= Unauthorised access can be a crime even with good intentions. Report concerns to the owner and wait for permission.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What is the idea behind defence in depth?
- One very strong firewall is enough
* Several independent layers so that one failure is not fatal
- Hiding the network name
- Using only expensive tools
= Layers of people, process and technology protect even when one control fails.

Q: Which function of the NIST Cybersecurity Framework covers tested backups and restoring service?
- Identify
- Detect
* Recover
- Govern
= Recover is about restoring capabilities and services after an incident.

Q: What is residual risk?
- Risk that was never found
* Risk that remains after controls are applied
- Risk that is transferred to insurance only
- A type of malware
= No control removes all risk, and leaders should accept what remains knowingly.

Q: A written document says "All laptops holding personal data must be encrypted." This is best described as a:
* Policy
- Procedure
- Log
- Backup
= A policy states the rule and the reason; standards and procedures give the detail.

Q: At which stage of a typical intrusion does network segmentation help most?
- Preparation
- Initial access
* Movement across the network
- Planning a holiday
= Segmentation makes it harder for an intruder to reach other systems from the first compromised one.

Q: Which are good Windows hardening steps? (choose all that apply)
* Keep automatic updates on
* Encrypt the disk and store the recovery key safely
* Use a standard account for daily work
- Disable the firewall to make programs run faster
= Updates, encryption and limited accounts reduce risk; disabling the firewall removes a layer.

Q: On a Linux server, why should password logins for SSH often be replaced with keys?
- Keys are shorter to type
* Keys resist password-guessing attacks
- Keys make the server faster
- Keys remove the need for updates
= Key pairs cannot be guessed like short passwords, especially when combined with disabled root login.

Q: A file has permissions 777 on a server. What is the concern?
- It is the most secure setting
* Everyone can change it, which is rarely appropriate
- It is invisible
- It is encrypted
= Give the least permission that lets the work be done.

Q: Which factors should you combine when prioritising patches?
- Colour of the vendor logo
* Exposure to the internet, active use by criminals and asset value
- Age of the employee
- Alphabetical order of systems
= Real-world risk depends on exposure, threat activity and what the system holds.

Q: What is a compensating control?
- A punishment for staff
* An extra protection used when the normal fix cannot be applied
- A refund from the vendor
- A kind of password
= Isolation, restriction and monitoring can lower risk when patching is not possible.

Q: Which request breaks separation of duties?
- The bursar requests a payment and the director approves it
* One person both requests and approves the same payment
- Two people review a bank change
- A payment is logged
= No single person should control an entire sensitive process.

Q: A staff member has left the organisation. When should her accounts be disabled?
- After a year
- When her contract papers are filed
* On the day she leaves
- Only if she asks
= Leaver accounts are a common weakness and should be removed promptly.

Q: Which second factor is considered the strongest of these?
- SMS code
- Security question
* Hardware security key or passkey
- Birth date
= Keys and passkeys resist fake sites and cannot be easily intercepted.

Q: What should a stateful firewall do with incoming connections that nobody on the inside asked for?
- Allow them all
* Block them by default
- Ask the user to type a password
- Convert them to e-mail
= It lets replies to inside requests through but blocks unrequested incoming traffic.

Q: What is the difference between an IDS and an IPS?
- An IDS blocks, an IPS only watches
* An IDS detects and alerts, an IPS can also block automatically
- They are the same
- An IDS is only for Wi-Fi
= Prevention adds automatic blocking, which needs careful tuning to avoid cutting off real users.

Q: Which Wi-Fi setup is best for a small office?
- Open network, no password
- WEP with a short key
* WPA2 or WPA3 with a long passphrase and a separate guest network
- Hidden name only
= Strong encryption and separation of guests protect the staff network.

Q: Which is a warning sign of a phishing message? (choose all that apply)
* Urgent threat of closing an account
* A link that points somewhere different from its text
* A request to change bank details secretly
- A message from a colleague you expected, using the usual channel and no strange request
= Urgency, mismatched links and secrecy are classic signs; expected, ordinary messages are less suspicious.

Q: Why should a company make sure its logs have synchronised time?
- To change the clock display
* To order events from different systems correctly during an investigation
- To save storage
- To hide errors
= A common time source allows a trustworthy timeline.

Q: What is a SIEM mainly used for?
- Editing documents
* Collecting and correlating logs from many sources and raising alerts
- Making backups of photos
- Printing reports
= It centralises event data so patterns across systems can be detected.

Q: What does "alert fatigue" lead to?
- Better protection
* Real warnings being ignored among many unimportant alerts
- Faster computers
- Fewer logs
= Tuning and focused rules keep alerts useful.

Q: The 3-2-1 backup rule means:
- Three people, two passwords, one server
* Three copies, on two kinds of storage, one off-site
- Three days, two weeks, one month
- Three backups per year
= Spread copies across media and places, and keep one offline or immutable as well.

Q: Which statement about backups is true?
- A backup that has never been restored is proven to work
* Only a tested restore proves a backup is usable
- Backups need no protection
- RPO is how long you can be offline
= Test restores regularly, and RPO is acceptable data loss.

Q: A ransomware outbreak is confirmed on three laptops. Which action belongs to containment?
- Writing the lessons learned report
* Disconnecting the laptops from the network and disabling affected accounts
- Buying new backup drives
- Updating the policy document
= Containment limits the spread of the harm right away.

Q: Why is chain of custody important?
- It makes evidence heavier
* It shows who held the evidence and that it was not altered
- It makes the investigation faster
- It replaces hashes
= Documented custody supports the reliability of evidence in disciplinary or legal processes.

Q: Under Uganda's Data Protection and Privacy Act, 2019, what should a defender ask first when an incident occurs?
* Was any personal data involved?
- Which brand of laptop was it?
- Who is the youngest staff member?
- Is the office painted blue?
= If personal data is affected, duties such as notifying the authority and the people affected may apply, so the data protection contact must be involved early.
QUIZ,
];
