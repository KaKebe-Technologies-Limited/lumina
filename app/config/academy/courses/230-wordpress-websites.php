<?php
return [
    'slug'        => 'wordpress-websites',
    'title'       => 'Build a Website with WordPress',
    'category'    => 'web',
    'level'       => 'intermediate',
    'icon'        => 'globe',
    'sort'        => 230,
    'summary'     => 'Plan, build, launch and look after a real website with WordPress, with no coding: pages, posts, design, plugins, security and a capstone site for a small NGO or business.',
    'description' => '<p>WordPress is free software that runs a very large share of the websites you visit. With it you can build a site for a business, school, church, youth group or NGO using menus and a visual editor instead of code.</p><p>This course walks you through the whole journey: planning what the site is for, setting it up, adding content, choosing a design and a few safe plugins, and then launching and looking after it. Every lesson ends with a short practice task, and the last lesson gives you a capstone brief.</p><p>You should already be comfortable using a computer and the internet. Menus and screens change between WordPress versions, so the lessons describe where to look in general terms rather than relying on exact screenshots.</p>',
    'outcomes'    => [
        'Plan a website by defining its purpose, audience, pages and content',
        'Explain domain names and hosting, and compare WordPress.com with self-hosted WordPress.org',
        'Set up a WordPress site and configure its main settings, users and roles',
        'Create pages and posts with the block editor, add images with alt text and build menus',
        'Choose and customise a theme and use plugins carefully and safely',
        'Add a contact form and apply basic search engine and speed improvements',
        'Launch a site with a checklist, legal pages and analytics',
        'Keep a site secure and healthy with updates, backups and a maintenance routine',
    ],
    'requirements' => [
        'Completed The Internet, Email and Online Communication and Computer Basics, or equivalent skills',
        'A laptop or computer with internet access and a modern web browser',
        'An email address you can access',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'WordPress.org Documentation', 'url' => 'https://wordpress.org/documentation/'],
        ['title' => 'WordPress.com Support', 'url' => 'https://wordpress.com/support/'],
        ['title' => 'Learn WordPress (free tutorials)', 'url' => 'https://learn.wordpress.org'],
        ['title' => 'Web Accessibility Initiative (W3C)', 'url' => 'https://www.w3.org/WAI/'],
        ['title' => 'Personal Data Protection Office, Uganda', 'url' => 'https://www.pdpo.go.ug'],
    ],
    'modules' => [
        // ------------------------------------------------------------------ MODULE 1
        [
            'title'   => 'Module 1: Planning a website',
            'summary' => 'Decide what the site is for, who it serves, which pages it needs, and how domains, hosting and WordPress options fit together.',
            'lessons' => [
                [
                    'title'   => 'Purpose, audience and pages',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a clear one-sentence purpose for a website, describe its main visitors, and list the pages it needs. This matters because most weak websites are not weak in design; they simply never decided what they were for.</p>
<h3>Start with the purpose</h3>
<p>A website is a tool. Before you touch any software, finish this sentence: <em>"This website exists so that [who] can [do what]."</em> For example: "This website exists so that parents in Gulu can see our tutoring timetable and contact us to enrol their children." A good purpose is specific and has one main action.</p>
<p>Typical purposes are: sell products, bring in enquiries, share news and reports, collect donations or volunteers, or simply prove that your organisation is real and trustworthy.</p>
<h3>Know your audience</h3>
<p>Your <strong>audience</strong> is the group of people you want to serve. Describe one or two typical visitors in a few lines: age, what they want, what device they use, and how fast their internet is. In Northern Uganda many visitors use a smartphone with limited data, so your site should load quickly and read well on a small screen.</p>
<p>Ask yourself three questions about each visitor:</p>
<ul>
<li>What are they looking for when they arrive?</li>
<li>What worries do they have (price, trust, distance, language)?</li>
<li>What do I want them to do before they leave?</li>
</ul>
<h3>Choose the pages</h3>
<p>A <strong>page</strong> is one screen of a website, such as the "About" page. Small sites need very few. A good starting set is:</p>
<table>
<thead><tr><th>Page</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Home</td><td>Says who you are and what visitors can do, within a few seconds</td></tr>
<tr><td>About</td><td>Your story, mission, team and why people should trust you</td></tr>
<tr><td>Services or Programmes</td><td>What you offer, in plain words</td></tr>
<tr><td>Contact</td><td>Phone, email, location, opening hours, a form</td></tr>
<tr><td>News or Blog (optional)</td><td>Fresh updates that show you are active</td></tr>
<tr><td>Privacy policy</td><td>Explains how you use visitors' data (see Module 5)</td></tr>
</tbody>
</table>
<h3>Plan your content before you build</h3>
<p>Content is the text, photos, logo and documents that fill the pages. Collect it first in a folder: your logo, 10 to 20 clear photos, a short description of each service, contact details, and any prices. Trying to write content while building the site slows you down and often leads to empty "coming soon" pages.</p>
<p>Draw a simple <strong>site map</strong> on paper: Home at the top, then boxes below it for each main page. Keep the main menu to five or six items so visitors never feel lost.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose a real or imaginary organisation (a tailoring shop, a youth club, a clinic).</li>
<li>Write its purpose sentence using the "so that [who] can [do what]" shape.</li>
<li>Describe one typical visitor in three lines.</li>
<li>List the five pages you would build and write one sentence about what each page must achieve.</li>
<li>List the content items you would need to collect (logo, photos, text).</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Write a one-sentence purpose with one main action before choosing any tools.</li>
<li>Design for real visitors, including people on phones with limited data.</li>
<li>Start with a small set of clear pages and a short menu.</li>
<li>Gather your content first, then build.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Domain names and hosting explained',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will understand what a domain name and web hosting are, how they work together, and how to choose and buy them wisely. These two items are the "address" and the "plot of land" of your website, and mistakes here are expensive to fix later.</p>
<h3>The house and the address</h3>
<p>Imagine your website as a house. The <strong>hosting</strong> is the plot of land and the building: a computer, called a server, that is always switched on and connected to the internet, storing your files. The <strong>domain name</strong> is the street address people type, such as <code>example.org</code>. Without hosting there is nothing to visit; without a domain people cannot find it easily.</p>
<h3>Parts of a domain name</h3>
<p>Take <code>www.luminaafrica.org</code>. The last part, <code>.org</code>, is the <strong>top-level domain</strong> (TLD). The middle part is the name you choose. Common choices are <code>.com</code> for business, <code>.org</code> for organisations, and country endings such as <code>.ug</code>, <code>.co.ug</code> or <code>.or.ug</code> for Uganda. Country domains can show local identity, but some have extra registration rules, so ask the registrar what documents they need.</p>
<h3>Choosing a good name</h3>
<ul>
<li>Keep it short and easy to say on the phone and spell from memory.</li>
<li>Avoid hyphens, numbers and unusual spellings.</li>
<li>Use your real organisation or business name where possible.</li>
<li>Check that the matching social media names are free, so your brand looks consistent.</li>
</ul>
<h3>Buying a domain</h3>
<p>You register a domain through a <strong>registrar</strong>, a company authorised to sell domains. You pay yearly, and the price varies by ending. Be careful with these points:</p>
<ol>
<li>Register the domain in <strong>your organisation's name and email</strong>, not a freelancer's or friend's. Otherwise you may lose control of your own address.</li>
<li>Turn on <strong>auto-renew</strong> or set a calendar reminder, because an expired domain can be taken by someone else.</li>
<li>Ask about <strong>WHOIS privacy</strong>, which hides your personal contact details from public lookups.</li>
<li>Compare the renewal price, not only the first-year offer, since renewals are often higher.</li>
</ol>
<h3>What hosting does</h3>
<p>Hosting plans differ mainly in who looks after the technical side. <strong>Shared hosting</strong> puts many small sites on one server and is cheap, which suits a small NGO or business. <strong>Managed WordPress hosting</strong> costs more but handles updates, backups and security for you. Larger plans give more speed and storage. When comparing, check: yearly cost after the offer ends, backup service, support hours, whether a free security certificate is included, and where the servers are located.</p>
<h3>HTTPS and the padlock</h3>
<p>A security certificate (SSL/TLS) encrypts the connection and makes your address start with <code>https://</code>. Browsers warn visitors about sites without it, so choose a host that provides it, which many do at no extra charge.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write down three possible domain names for your practice organisation.</li>
<li>Visit a registrar's search page and check whether each name is available (do not buy yet).</li>
<li>Note the first-year and renewal prices for <code>.org</code> and <code>.com</code>.</li>
<li>Compare two hosting plans and list their yearly price, backups and support options.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Hosting stores your site; the domain is its address.</li>
<li>Register the domain under your organisation's own details and renew it on time.</li>
<li>Compare renewal prices, backups, support and HTTPS when choosing hosting.</li>
<li>A short, simple, easy-to-spell name is best.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'WordPress.com or WordPress.org',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain the difference between WordPress.com and self-hosted WordPress.org, and choose the one that fits your project, skills and budget. People often mix them up, and choosing the wrong one can lead to a painful move later.</p>
<h3>One name, two ways to use it</h3>
<p><strong>WordPress</strong> is free, open-source software for building websites. "Open source" means its code is public and anyone may use and improve it. You can use it in two main ways:</p>
<ul>
<li><strong>WordPress.com</strong> is a service run by a company. You create an account and the site lives on their servers. They look after hosting, updates and security.</li>
<li><strong>WordPress.org</strong> is the home of the free software itself. You download it (or your host installs it for you) and run it on hosting that you pay for. This is called <strong>self-hosted</strong> WordPress.</li>
</ul>
<h3>Comparing the two</h3>
<table>
<thead><tr><th>Question</th><th>WordPress.com</th><th>Self-hosted WordPress.org</th></tr></thead>
<tbody>
<tr><td>Setup effort</td><td>Very low: sign up and start</td><td>Moderate: buy hosting and domain, install</td></tr>
<tr><td>Maintenance</td><td>Mostly done for you</td><td>You or your host must update and back up</td></tr>
<tr><td>Cost</td><td>Free plan with limits; paid plans add features</td><td>Software is free; you pay for hosting and domain</td></tr>
<tr><td>Plugins and custom themes</td><td>Limited on lower plans; wider on higher plans</td><td>Full freedom to install any</td></tr>
<tr><td>Control and ownership</td><td>The platform sets the rules</td><td>You control files and data</td></tr>
<tr><td>Best for</td><td>Personal blogs, simple sites, quick start</td><td>Business and NGO sites that need to grow</td></tr>
</tbody>
</table>
<p>Plans and features change over time, so always read the current plan descriptions before deciding.</p>
<h3>Security and costs in plain words</h3>
<p>With a hosted service, security is mainly the platform's job and your job is a strong password. With self-hosted WordPress, <em>you</em> are responsible for keeping WordPress, themes and plugins updated, using strong logins, and keeping backups. This is not hard, and Module 5 teaches the routine, but it is real work. Typical yearly costs for a small site are the domain, hosting and, sometimes, a paid theme or plugin. Avoid pirated "free premium" themes: they often hide malicious code that can take over your site.</p>
<h3>Making the decision</h3>
<ol>
<li>Is this a quick, small site where you do not mind some limits? Consider WordPress.com.</li>
<li>Will the site be a long-term home for a business or organisation, possibly with a shop, forms or special features? Consider self-hosted WordPress.org.</li>
<li>Do you have someone who will do monthly updates and backups? If not, pick a managed option or a hosted plan.</li>
</ol>
<p>In this course the skills apply to both. The dashboard, editor, pages, posts and menus work in a very similar way. Where a feature exists only in the self-hosted version, the lesson says so.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open the websites WordPress.com and WordPress.org in two browser tabs.</li>
<li>On each, find a page that describes pricing or getting started.</li>
<li>Write three differences you notice between them.</li>
<li>Decide which you would use for your practice organisation and write two sentences explaining why.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>WordPress.com is a hosted service; WordPress.org is the free software you run on your own hosting.</li>
<li>Self-hosted gives more freedom but more responsibility for updates, security and backups.</li>
<li>Never use pirated themes or plugins.</li>
<li>Check current plans and prices before you decide.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which sentence is the best purpose statement for a website?
- We want a modern, beautiful website.
* This site exists so that parents can see our tutoring timetable and contact us to enrol their children.
- This site is about our organisation and everything we do.
- This site will have many pages and lots of photos.
= A good purpose names who the visitor is and what single main action they can take. The other options describe looks or size, not a goal.

Q: What is the role of web hosting?
- It is the name people type to find your site.
- It protects your computer from viruses.
* It stores your site's files on a server that is always connected to the internet.
- It chooses the colours of your site.
= Hosting is the server space where your site lives. The domain name is only the address that points to it.

Q: Which are good practices when registering a domain? (choose all that apply)
* Register it under your organisation's own name and email
* Turn on auto-renew or set a reminder
- Let a freelancer register it in their own name to save time
- Compare only the first-year price
= You must control your own domain and renew it on time. Renewal prices are often higher than the first-year offer.

Q: In the address www.example.org, what is .org?
- The hosting provider
* The top-level domain
- The server name
- The password
= The ending after the last dot is the top-level domain, such as .com, .org or .ug.

Q: Which statement about self-hosted WordPress.org is true?
- The platform does all updates and security for you.
- You cannot install plugins.
* You are responsible for updates, backups and security, but have full freedom to install themes and plugins.
- It always costs a monthly fee for the software.
= The software is free, but you pay for hosting and domain, and you must keep the site updated and backed up.

Q: Why should you collect content before building the site?
* Building is faster and you avoid empty "coming soon" pages
- WordPress cannot start without photos
- It is a legal requirement
- It makes the domain cheaper
= Having text, photos and contact details ready lets you build and publish real pages quickly.

Q: Why is it risky to use pirated "free premium" themes?
- They are slower to download.
- They need more hosting space.
* They often hide malicious code that can take over your site.
= Illegal copies are a common way for attackers to get into websites. Use themes from trusted sources.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 2
        [
            'title'   => 'Module 2: Getting set up',
            'summary' => 'Create the site, find your way around the dashboard, set the main settings, manage users and keep updates and backups under control.',
            'lessons' => [
                [
                    'title'   => 'Creating your site and meeting the dashboard',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will have a WordPress site running, be able to log in to its dashboard, and know what each main area is for. The dashboard is the control room of your website, so getting comfortable here makes everything else easier.</p>
<h3>Ways to get a site running</h3>
<p>There are three common routes:</p>
<ul>
<li><strong>A hosted account</strong> on WordPress.com: sign up with your email, choose a site address, and follow the on-screen setup.</li>
<li><strong>One-click install</strong> on self-hosted hosting: most hosts have a control panel with an option such as "Install WordPress". You pick the domain, a site title, an admin username and a strong password.</li>
<li><strong>A local practice site</strong> on your own computer, using free tools that run WordPress offline. This is perfect for learning because nothing is public and nothing can break.</li>
</ul>
<p>Whichever you choose, write down your login address, username and password in a safe place. A self-hosted login page is usually your site address followed by <code>/wp-admin</code>.</p>
<h3>Choosing a good admin account</h3>
<ol>
<li>Do not use "admin" as the username, because attackers try it first.</li>
<li>Use a long password of three or four unrelated words plus numbers, or let a password manager create one.</li>
<li>Use an email address that you check regularly, because password resets go there.</li>
</ol>
<h3>A tour of the dashboard</h3>
<p>After logging in you see the <strong>Dashboard</strong>. The menu runs down the left side (on a phone it hides behind a button). Menu names can differ slightly between versions, but you will find these areas:</p>
<table>
<thead><tr><th>Menu area</th><th>What it is for</th></tr></thead>
<tbody>
<tr><td>Posts</td><td>News, blog entries and other dated content</td></tr>
<tr><td>Media</td><td>Your library of images, documents and videos</td></tr>
<tr><td>Pages</td><td>Permanent pages such as About and Contact</td></tr>
<tr><td>Comments</td><td>Visitor comments waiting for your approval</td></tr>
<tr><td>Appearance</td><td>Themes, menus, widgets and design tools</td></tr>
<tr><td>Plugins</td><td>Extra features you add (self-hosted)</td></tr>
<tr><td>Users</td><td>The people who can log in</td></tr>
<tr><td>Settings</td><td>Site title, reading, discussion, permalinks and more</td></tr>
</tbody>
</table>
<p>At the top there is a toolbar with a link to view your site and a button to add new content. Visit your site in another tab and compare it with the dashboard: the site is what the public sees, the dashboard is what only you see.</p>
<h3>Remove the demo content</h3>
<p>New sites usually contain a sample page, a "Hello world" post and a sample comment. Move these to the trash so visitors never see them.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a practice site by any route above (a local or free hosted site is fine).</li>
<li>Log in and click each main menu item once, noting what it shows.</li>
<li>Open your public site in a new tab using the "Visit site" link.</li>
<li>Delete the sample post, sample page and sample comment.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Choose a hosted, one-click or local route to start, and keep your login details safe.</li>
<li>Avoid "admin" as a username and use a long, unique password.</li>
<li>The dashboard is private; your site is the public view.</li>
<li>Remove the demo content before you add your own.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Essential settings',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to set your site title and tagline, choose clean permalinks, decide what the home page shows, and control comments. A few minutes on settings at the start prevents embarrassing mistakes and later rework.</p>
<h3>General settings</h3>
<p>Open <strong>Settings</strong> and choose <strong>General</strong> (the wording may vary). Here you set:</p>
<ul>
<li><strong>Site title:</strong> your organisation's name, as it should appear in browser tabs and search results.</li>
<li><strong>Tagline:</strong> a short phrase that explains what you do, for example "Free digital skills for young people in Gulu".</li>
<li><strong>Time zone:</strong> choose a city in East Africa so that scheduled posts publish at the right time.</li>
<li><strong>Admin email:</strong> where WordPress sends notices. Use an address you read.</li>
</ul>
<p>Save the changes at the bottom of the screen. Many settings pages do nothing until you press the save button.</p>
<h3>Permalinks</h3>
<p>A <strong>permalink</strong> is the permanent web address of a page or post. Under <strong>Settings</strong>, find <strong>Permalinks</strong>. The default can produce addresses like <code>?p=123</code>, which are ugly and say nothing. Choose an option that shows the post name, such as <code>/my-first-news-story/</code>. Descriptive addresses are easier to read, share and understand by search engines.</p>
<p>Set this <strong>before you publish</strong>. Changing the format later changes every address and can break links that other people already saved.</p>
<h3>Reading settings</h3>
<p>Under <strong>Reading</strong> you decide what visitors see first. Two choices exist:</p>
<ol>
<li><strong>Your latest posts:</strong> the home page is a stream of news. Good for a blog.</li>
<li><strong>A static page:</strong> you pick a page to be the home page, and optionally another page to hold your posts (for example "News"). Most organisation sites use this.</li>
</ol>
<p>On this screen there is also a search engine visibility box. While you are building, you may ask search engines not to index the site; <strong>remember to switch this off at launch</strong>, otherwise your site will not appear in search results. Treat the request as a polite hint rather than a lock: it does not make the site private.</p>
<h3>Discussion settings</h3>
<p>Comments invite conversation, but they also invite spam. Under <strong>Discussion</strong> you can:</p>
<ul>
<li>Turn comments off for new posts if you cannot moderate them.</li>
<li>Require approval for the first comment from each person.</li>
<li>Require commenters to give a name and email.</li>
<li>Hold comments that contain several links, which is a classic spam sign.</li>
</ul>
<p>For most small organisation sites, comments off on pages and moderated on posts is a sensible default.</p>
<h3>Try it yourself</h3>
<ol>
<li>Set a site title and tagline for your practice site.</li>
<li>Pick the East African time zone that suits you.</li>
<li>Change permalinks to a post-name format and save.</li>
<li>Create two empty pages named "Home" and "News", then set Home as the static home page and News as the posts page in Reading settings.</li>
<li>Switch on comment approval in Discussion settings.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Set title, tagline, time zone and admin email first, and press save.</li>
<li>Use post-name permalinks and set them before publishing.</li>
<li>A static home page suits most organisations.</li>
<li>Moderate comments or turn them off to avoid spam.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Users and roles',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to add a person to your site with the right level of access, and explain why giving everyone full control is a mistake. This matters when several staff or volunteers share a website, because every extra powerful account is another way in for attackers or accidents.</p>
<h3>What is a user role?</h3>
<p>A <strong>user</strong> is anyone with a login. A <strong>role</strong> decides what that user is allowed to do. WordPress comes with these standard roles:</p>
<table>
<thead><tr><th>Role</th><th>What they can do</th></tr></thead>
<tbody>
<tr><td>Administrator</td><td>Everything: settings, themes, plugins, other users</td></tr>
<tr><td>Editor</td><td>Publish and manage all posts and pages, including other people's</td></tr>
<tr><td>Author</td><td>Write, publish and manage only their own posts</td></tr>
<tr><td>Contributor</td><td>Write their own posts but not publish them; someone must review</td></tr>
<tr><td>Subscriber</td><td>Only manage their own profile</td></tr>
</tbody>
</table>
<p>Self-hosted sites may also add extra roles through plugins, for example for shop managers.</p>
<h3>The rule of least privilege</h3>
<p>Give each person the <strong>lowest role that lets them do their job</strong>. A volunteer who writes news stories needs Contributor or Author, not Administrator. If a volunteer's password is stolen, the damage is limited to what that role can do.</p>
<h3>Adding a user</h3>
<ol>
<li>Open <strong>Users</strong> in the dashboard and choose <strong>Add New</strong>.</li>
<li>Enter a username, the person's own email address and their name.</li>
<li>Choose the role carefully from the list.</li>
<li>Send the new user a link so they set their own password. Never share one login between several people, because then you cannot tell who changed what.</li>
<li>Save with the add-user button.</li>
</ol>
<h3>Looking after your accounts</h3>
<ul>
<li>Keep at least one trusted second administrator so you are not locked out if one person leaves.</li>
<li>Review the Users list every few months and delete accounts for people who have left.</li>
<li>Use your own profile page to set your display name, so posts show a friendly name rather than your login name.</li>
<li>When someone leaves, change any shared passwords they knew and reassign their content before deleting their account.</li>
</ul>
<h3>Your own profile</h3>
<p>Under <strong>Users</strong>, choose <strong>Profile</strong>. You can set a display name, add a short biography, choose the admin colour scheme and change your password. Use a strong password that you do not reuse anywhere else.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open the Users list on your practice site and find your own account.</li>
<li>Set a display name that is not your login name.</li>
<li>Add a second user with the Contributor role, using another email you control.</li>
<li>Write one sentence stating which role you would give a volunteer who only writes news stories, and why.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Roles control what each person can do; choose the lowest role that works.</li>
<li>Never share a single login among several people.</li>
<li>Keep two trusted administrators and remove accounts that are no longer needed.</li>
<li>Use strong, unique passwords for every account.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Updates and backups',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will know why updates matter, how to apply them safely, and how to make and test backups. These two habits protect you from the most common website disasters: hacked sites, broken sites and lost work.</p>
<h3>Why updates matter</h3>
<p>WordPress, themes and plugins are software, and software has bugs. When someone finds a security weakness, the makers release an update that fixes it. Attackers then search the internet for sites that have not updated yet. An outdated plugin is one of the most common ways websites get hacked. Updates also bring new features and fixes.</p>
<p>The dashboard shows a number badge on <strong>Dashboard</strong> or <strong>Updates</strong> when something is waiting. Self-hosted sites update three things: the <strong>WordPress core</strong>, <strong>themes</strong> and <strong>plugins</strong>. On hosted plans the platform updates the core for you.</p>
<h3>A safe update routine</h3>
<ol>
<li>Take a fresh backup first.</li>
<li>Update one thing at a time, or the small batch of plugins together.</li>
<li>Visit the front of your site and test the main pages, the menu and the contact form.</li>
<li>If something breaks, switch off the last plugin you updated, restore the backup if needed, and look for help in the plugin's support area.</li>
</ol>
<p>Choose a regular time, for example every Friday morning, and keep to it. For minor security updates many sites allow automatic updates; check your host's options.</p>
<h3>What a backup is</h3>
<p>A <strong>backup</strong> is a saved copy of your site that you can restore. A complete backup has two parts: the <strong>files</strong> (themes, plugins, images) and the <strong>database</strong> (your pages, posts and settings). If you keep only one of these you cannot fully restore the site.</p>
<h3>The 3-2-1 idea</h3>
<ul>
<li>Keep <strong>3</strong> copies of anything important.</li>
<li>Store them on <strong>2</strong> different kinds of place (for example host storage and your own computer or a cloud drive).</li>
<li>Keep <strong>1</strong> copy away from the host, in case the host itself fails.</li>
</ul>
<p>Many hosts offer daily backups, and trusted backup plugins can send copies to cloud storage on a schedule. Backups kept only on the same server as the site are not enough.</p>
<h3>Test your backup</h3>
<p>A backup you have never tried to restore is only a hope. At least once, practise restoring on a practice site or ask your host how restoration works and how long it takes. Write the steps down.</p>
<h3>How often?</h3>
<p>A site that changes every day, such as a shop, needs daily backups. A site that changes monthly can use weekly ones. Also make a manual backup right before big changes such as switching themes.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open the Updates area on your practice site and note what is waiting.</li>
<li>Find out what backup options your host or chosen plugin offers.</li>
<li>Write a three-line update routine and decide which weekday you will do it.</li>
<li>If possible, make a backup and download it to your computer, then note where you stored it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Update core, themes and plugins regularly because updates fix security holes.</li>
<li>Back up before updating, and test the main pages afterwards.</li>
<li>A full backup contains both files and the database.</li>
<li>Keep at least one backup away from your host and practise restoring it.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which username choice is safest for the main administrator?
- admin
- The name of your organisation
* A less obvious name that is not "admin"
- Your phone number
= Attackers try "admin" first, so a less predictable name makes guessing harder. Always pair it with a long, unique password.

Q: Where can you set what the home page shows?
- Appearance, then Users
* Settings, then Reading
- Posts, then Categories
- Media, then Library
= The Reading settings let you choose between a stream of latest posts and a static page as the home page.

Q: Why should permalinks be set before you publish?
- They stop spam.
- They speed up the site.
* Changing them later changes every address and can break saved links.
- They are needed to log in.
= Permalinks are your pages' permanent addresses. Choose a clean post-name format at the start.

Q: A volunteer will only write news stories that you review before they go live. Which role fits best?
- Administrator
- Editor
* Contributor
- Subscriber
= A Contributor can write but cannot publish, which matches review-first work. This follows the rule of least privilege.

Q: A complete WordPress backup includes (choose all that apply):
* The files such as themes, plugins and images
* The database with pages, posts and settings
- Only the site title
- Only the homepage text
= Restoring needs both files and the database. Missing either one means the site cannot be fully rebuilt.

Q: What should you do before applying updates?
- Switch off the internet.
- Delete all plugins.
* Take a fresh backup.
= A backup lets you return to a working site if an update causes problems.

Q: What does it mean to keep one backup away from your host?
- Store it in a different folder on the same server.
* Keep a copy on another place such as your own computer or cloud drive.
- Email it to all your users.
= If the host server fails or is hacked, backups stored only there would be lost with the site.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 3
        [
            'title'   => 'Module 3: Content',
            'summary' => 'Create pages and posts with the block editor, handle images and media properly, build menus and link your content together.',
            'lessons' => [
                [
                    'title'   => 'Pages, posts and the block editor',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain when to use a page and when to use a post, and create both using the block editor. This is the skill you will use most as a website owner.</p>
<h3>Pages versus posts</h3>
<table>
<thead><tr><th></th><th>Page</th><th>Post</th></tr></thead>
<tbody>
<tr><td>Purpose</td><td>Lasting information</td><td>News, updates, stories</td></tr>
<tr><td>Date shown</td><td>Usually not</td><td>Yes, newest first</td></tr>
<tr><td>Organised by</td><td>Menus and parent pages</td><td>Categories and tags</td></tr>
<tr><td>Examples</td><td>About, Contact, Services</td><td>"Our new training cohort starts in March"</td></tr>
</tbody>
</table>
<p>A simple test: if the content would still be correct in two years, it is probably a page. If it is tied to a date, it is probably a post.</p>
<h3>The block editor</h3>
<p>WordPress's modern editor works with <strong>blocks</strong>. Each paragraph, heading, image, list, button or table is a separate block that you can move, style or delete. To start, choose <strong>Pages</strong>, then <strong>Add New</strong> (names may vary slightly).</p>
<ol>
<li>Click the title area at the top and type the page title.</li>
<li>Click in the body and begin typing: this creates a paragraph block.</li>
<li>Press <strong>Enter</strong> to start a new block. Type <code>/</code> followed by a word, such as <code>/heading</code> or <code>/image</code>, to insert a block quickly.</li>
<li>Click the plus icon (usually top left or between blocks) to browse all blocks.</li>
<li>Use the small toolbar above the selected block to change alignment, bold, italic and links, and the side panel for more settings.</li>
<li>Use the move arrows or drag handle to reorder blocks, and the three-dot menu to duplicate or delete.</li>
</ol>
<p>Useful shortcuts: <code>Ctrl+Z</code> (Mac: Cmd+Z) to undo, <code>Ctrl+K</code> (Cmd+K) to add a link, and <code>Ctrl+S</code> (Cmd+S) to save a draft.</p>
<h3>Draft, preview and publish</h3>
<p>Content starts as a <strong>draft</strong>, visible only to you. Use <strong>Preview</strong> to see it as visitors will. When happy, press <strong>Publish</strong>. You can also schedule for a later date and time, or set a page to private. After publishing, the button changes to <strong>Update</strong>, and you must press it to save later edits. WordPress also keeps <strong>revisions</strong>, earlier versions you can restore if you make a mistake.</p>
<h3>Write for the web</h3>
<ul>
<li>Put the most important point first.</li>
<li>Use short paragraphs of two or three sentences.</li>
<li>Break long text with subheadings and bullet lists.</li>
<li>Write link text that says where the link goes, such as "read our child-protection policy", not "click here".</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Create an "About" page with a title, a paragraph, a heading and a bulleted list of three facts.</li>
<li>Insert a button block that says "Contact us" and leave its link empty for now.</li>
<li>Use Preview, then publish the page.</li>
<li>Create a post titled "Welcome" with two short paragraphs and save it as a draft without publishing.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Pages hold lasting information; posts hold dated news.</li>
<li>Every element in the block editor is a block that you can add, move and edit.</li>
<li>Use drafts, preview and revisions to work safely.</li>
<li>Write short paragraphs with clear headings and descriptive link text.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Headings, images and the media library',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to structure text with proper headings, prepare images at a sensible size, upload them to the media library and write helpful alt text. Heavy photos are the number one reason small websites load slowly, especially on mobile data.</p>
<h3>Headings give structure</h3>
<p>Headings are levels of titles: Heading 1 is the main title (the page title usually does this), Heading 2 marks main sections, and Heading 3 marks sub-sections. In the block editor, add a heading block and use the toolbar or side panel to choose the level. Follow these rules:</p>
<ul>
<li>Do not choose a heading level because of how big it looks; choose it for the structure. Change sizes with the theme, not by misusing levels.</li>
<li>Do not skip levels (from H2 straight to H4).</li>
<li>Make headings meaningful: a person scanning only headings should understand the page.</li>
</ul>
<p>Screen readers used by blind visitors, and search engines, rely on headings to understand your page.</p>
<h3>Prepare images before upload</h3>
<ol>
<li><strong>Crop</strong> the photo to the part that matters.</li>
<li><strong>Resize</strong> it to about the largest size it will be shown. For a full-width banner, 1600 to 2000 pixels wide is usually plenty. For a photo in the text column, 800 to 1200 pixels is enough.</li>
<li><strong>Compress</strong> it. Photos straight from a phone camera can be several megabytes; aim for under 200 to 300 KB for most images. Free tools on your computer, phone or in the browser can resize and compress.</li>
<li>Use <strong>JPEG</strong> for photos, <strong>PNG</strong> for logos and graphics needing transparency, and <strong>WebP</strong> where supported for smaller files.</li>
<li>Give the file a descriptive name such as <code>girls-coding-class-gulu.jpg</code> rather than <code>IMG_4521.jpg</code>.</li>
</ol>
<h3>The media library</h3>
<p>Open <strong>Media</strong> to see every file you have uploaded. Click <strong>Add New</strong> and drag files in, or upload directly from the image block in the editor. Clicking a file shows its details: title, caption, description and <strong>alt text</strong>. You can also upload PDFs, such as an annual report. Keep an eye on file size, because most hosts limit uploads and your storage is not unlimited.</p>
<h3>Alt text</h3>
<p><strong>Alt text</strong> is a short written description of an image. It is read aloud by screen readers, shown if the image fails to load, and helps search engines. Write what the image shows and why it matters, in one short sentence: "Learners at laptops during a coding class in Gulu". Do not start with "image of". If an image is purely decorative, leave the alt text empty. If the image contains words, such as a flyer, repeat the important words in the alt text or the page text.</p>
<h3>Using images in a page</h3>
<p>Add an image block, upload or choose from the library, then set the alt text and alignment in the side panel. Use captions for credits or context. Make sure you have permission to use every image; do not copy photos from other websites. When photographing people, especially children, get consent first and follow your organisation's child-protection rules.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose a photo from your phone and resize it to about 1200 pixels wide and under 300 KB.</li>
<li>Rename it with a descriptive name and upload it in the Media library.</li>
<li>Add it to your About page and write alt text.</li>
<li>Check that your page uses one H2 or more in a sensible order.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Choose heading levels for structure, not size, and do not skip levels.</li>
<li>Crop, resize and compress images before upload; aim for a few hundred KB or less.</li>
<li>Write short, meaningful alt text for every informative image.</li>
<li>Only use images you have the right to use, and get consent for photos of people.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Menus, categories and internal links',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a clear navigation menu, organise posts into categories, and link your own pages together. Good navigation lets a visitor find what they came for in two or three taps, which keeps them on your site.</p>
<h3>The navigation menu</h3>
<p>A <strong>menu</strong> is the list of links that visitors use to move around, usually at the top of the page. How you edit it depends on your theme type. In many modern themes you edit it inside the Site Editor or in the navigation block; in older themes you use <strong>Appearance</strong> and then <strong>Menus</strong>. In either case:</p>
<ol>
<li>Create or open the main menu.</li>
<li>Add the pages you want: Home, About, Programmes, News, Contact.</li>
<li>Drag them into the order that makes sense. Put your most important action, such as Contact or Donate, where it is easy to see.</li>
<li>Indent an item beneath another to create a drop-down for related pages, but use these sparingly because they are awkward on a phone.</li>
<li>Choose where the menu appears (header, footer) and save.</li>
</ol>
<p>Keep labels short and use words visitors expect. "Contact" is better than "Reach out to our wonderful team".</p>
<h3>Categories and tags</h3>
<p>Posts can be sorted with <strong>categories</strong> and <strong>tags</strong>. Think of categories as the chapters of a book and tags as the index.</p>
<ul>
<li><strong>Categories</strong> are a few broad topics, for example "News", "Stories", "Events". Every post should have at least one.</li>
<li><strong>Tags</strong> are specific keywords, for example "robotics" or "Gulu". Use them sparingly.</li>
</ul>
<p>Create categories under <strong>Posts</strong> and then <strong>Categories</strong>, or while writing a post in the side panel. Avoid ending up with 40 categories that each hold one post; a handful is easier for visitors. Rename the default "Uncategorized" category to something useful.</p>
<h3>Internal links</h3>
<p>An <strong>internal link</strong> points from one page of your site to another. They help visitors discover related information and help search engines understand your site. To add one, select some words, press <code>Ctrl+K</code> (Cmd+K on Mac), and begin typing the title of the page you want. Choose it from the suggestions. Good practice:</p>
<ul>
<li>Link from news posts to the page about the related programme.</li>
<li>Use descriptive words as the link text.</li>
<li>Check links after changing a page address, because old links may break.</li>
<li>Open external websites in a new tab if you want visitors to stay; leave internal links in the same tab.</li>
</ul>
<h3>Check your navigation</h3>
<p>Ask a friend to find your phone number and your latest news without help. If they hesitate, simplify the menu. Test on a phone as well as a computer.</p>
<h3>Try it yourself</h3>
<ol>
<li>Build a main menu with Home, About, News and Contact.</li>
<li>Create two categories for your posts and rename "Uncategorized".</li>
<li>Edit your Welcome post, assign a category and publish it.</li>
<li>Add an internal link from the post to your About page, then view your site on a phone.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Keep menus short with clear, expected labels and the key action easy to find.</li>
<li>Categories are broad topics; tags are specific keywords.</li>
<li>Use descriptive internal links to connect related content.</li>
<li>Test navigation on a phone with a real person.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which content is best created as a post rather than a page?
- Our contact details
- The mission of the organisation
* News about a training cohort starting next month
- The privacy policy
= Posts are for dated, time-related news. Pages hold information that stays correct for a long time.

Q: In the block editor, what does typing / followed by a word do?
* Opens a quick search to insert a block
- Deletes the block
- Publishes the page
- Switches to the dashboard
= Typing a slash opens a list so you can quickly add a heading, image or other block.

Q: What is the best reason to write alt text for an image?
- It makes the image bigger.
- It protects the image from copying.
* Screen readers and search engines use it, and it shows if the image fails to load.
= Alt text makes images understandable to people who cannot see them and helps search engines.

Q: Which are good image preparation steps? (choose all that apply)
* Resize to about the largest size shown
* Compress the file
- Upload the original phone photo of several megabytes
- Name the file IMG_4521.jpg
= Large uncompressed photos slow the site. Descriptive file names also help.

Q: What is the difference between a category and a tag?
- Categories are for pages; tags are for menus.
- There is no difference.
* Categories are broad topics; tags are more specific keywords.
= Think of categories as book chapters and tags as the index.

Q: Which is the best link text?
- Click here
* Read our child-protection policy
- Link
- http://
= Descriptive link text tells visitors and screen readers where the link leads.

Q: Why should you use drop-down menus sparingly?
- They are not allowed in WordPress.
* They can be awkward to use on phones.
- They cost money.
= Many visitors use phones, and nested menus are harder to tap.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 4
        [
            'title'   => 'Module 4: Design and features',
            'summary' => 'Choose and customise a theme, add blocks and widgets, use plugins safely, build a contact form and apply SEO and speed basics.',
            'lessons' => [
                [
                    'title'   => 'Choosing and customising a theme',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose a suitable theme, install it and customise the logo, colours and fonts so that the site looks like your organisation. A theme sets the look and layout of the entire site, so a careful choice saves a great deal of work.</p>
<h3>What a theme is</h3>
<p>A <strong>theme</strong> is a package that controls the design: layout, colours, fonts, header and footer. Changing the theme changes the look without changing your content. You manage themes under <strong>Appearance</strong> and then <strong>Themes</strong>. Modern <strong>block themes</strong> let you edit the whole site, including header and footer, in the Site Editor. Older <strong>classic themes</strong> use a Customizer panel and widget areas. Both work; check which type you have.</p>
<h3>How to choose</h3>
<ol>
<li><strong>Start from your purpose and pages</strong>, not from a pretty demo. Does the theme suit an organisation, a shop, or a blog?</li>
<li><strong>Prefer simple and fast.</strong> A lightweight theme loads quickly on mobile data. Demo sites full of sliders and animations are often heavy.</li>
<li><strong>Check it is mobile-friendly.</strong> Use the preview buttons for phone and tablet.</li>
<li><strong>Look at updates and support.</strong> Choose themes that were updated recently and have many active installs and good reviews.</li>
<li><strong>Use official or reputable sources.</strong> The free themes in the WordPress theme directory are reviewed. Buy paid themes only from the maker, never from "nulled" copies.</li>
<li><strong>Check accessibility and language support</strong> where listed, including readable text size and strong colour contrast.</li>
</ol>
<h3>Installing a theme</h3>
<p>Go to <strong>Appearance</strong>, then <strong>Themes</strong>, choose <strong>Add New</strong>, search or browse, and use <strong>Preview</strong> to see how it looks with your content. When ready, press <strong>Install</strong> and then <strong>Activate</strong>. Take a backup before switching on an existing site, because layouts may change.</p>
<h3>Customising</h3>
<p>Open the Site Editor or Customizer, usually through <strong>Appearance</strong> and then <strong>Editor</strong> or <strong>Customize</strong>. Common settings:</p>
<ul>
<li><strong>Site logo and icon:</strong> upload your logo and a small square "site icon" shown in browser tabs.</li>
<li><strong>Colours:</strong> pick two or three brand colours and one for buttons. Check contrast so text remains readable.</li>
<li><strong>Fonts:</strong> use one or two fonts. More looks messy.</li>
<li><strong>Header and footer:</strong> add your menu, contact details and social media links.</li>
</ul>
<p>Keep the design consistent: the same colours, spacing and style on every page build trust. Resist the urge to change everything; white space helps readability.</p>
<h3>Child themes</h3>
<p>If you plan to edit a classic theme's code, use a <strong>child theme</strong>, which keeps your changes when the parent theme updates. Most beginners never need this.</p>
<h3>Try it yourself</h3>
<ol>
<li>Preview three themes with your content and write one strength and one weakness for each.</li>
<li>Activate the one you prefer.</li>
<li>Upload a logo (or make a simple one) and set a site icon.</li>
<li>Set a main colour and check that white text on it is easy to read.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A theme controls look and layout; switching it does not delete your content.</li>
<li>Choose light, mobile-friendly, regularly updated themes from reputable sources.</li>
<li>Customise logo, colours and fonts, and keep them consistent.</li>
<li>Back up before changing the theme on a live site.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Plugins: what they are and how to choose safely',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will understand what plugins are, check them before installing, and know a small set worth having. Plugins add power, but each one is also extra code that can slow down or endanger your site, so the rule is: fewer, better plugins.</p>
<h3>What is a plugin?</h3>
<p>A <strong>plugin</strong> is an add-on that gives WordPress a new feature, such as a contact form, backups, security or search engine tools. Self-hosted sites can install any plugin. Some hosted plans limit plugins, so check your plan. Manage them under <strong>Plugins</strong> in the dashboard.</p>
<h3>Check before you install</h3>
<ol>
<li><strong>Do you really need it?</strong> Sometimes a built-in block or theme feature already does the job.</li>
<li><strong>Source:</strong> install from the official plugin directory or from the developer's own site. Never install a plugin from a random file you were emailed or from a "free download of a paid plugin".</li>
<li><strong>Last updated:</strong> avoid plugins not updated in a year or more.</li>
<li><strong>Active installations and reviews:</strong> many installs and mostly good, recent reviews are a good sign.</li>
<li><strong>Compatibility:</strong> check it works with your WordPress version.</li>
<li><strong>Support:</strong> look at whether the developer answers questions in the support forum.</li>
<li><strong>Privacy:</strong> read what data it collects or sends elsewhere.</li>
</ol>
<h3>Install, activate, update</h3>
<p>Choose <strong>Plugins</strong>, then <strong>Add New</strong>, search for the name, press <strong>Install Now</strong>, then <strong>Activate</strong>. Plugins do nothing until activated. Keep them updated. Deactivate and <strong>delete</strong> plugins you no longer use, since inactive plugins can still contain security holes.</p>
<h3>A small set by purpose</h3>
<table>
<thead><tr><th>Purpose</th><th>What to look for</th></tr></thead>
<tbody>
<tr><td>Contact forms</td><td>A well-known form plugin with spam protection</td></tr>
<tr><td>Backups</td><td>A plugin that makes scheduled backups to cloud storage</td></tr>
<tr><td>Security</td><td>One security plugin with login protection and file scanning</td></tr>
<tr><td>Search engine optimisation</td><td>One plugin that manages titles, descriptions and sitemaps</td></tr>
<tr><td>Speed</td><td>One caching or image-optimisation plugin, if your host does not provide it</td></tr>
<tr><td>Spam</td><td>An anti-spam tool if comments are enabled</td></tr>
</tbody>
</table>
<p>Use <strong>one</strong> plugin per purpose. Two security plugins, or two caching plugins, usually conflict. Compare several options in the directory by their reviews and update dates before choosing, rather than following a recommendation blindly.</p>
<h3>Signs of a problem</h3>
<ul>
<li>The site suddenly slows down or shows errors after activating a plugin.</li>
<li>A plugin asks for a licence key you never bought.</li>
<li>A plugin hides its code or asks for administrator login details outside the dashboard.</li>
</ul>
<p>If the dashboard breaks, you can often fix it by renaming the plugin's folder through your host's file manager, which switches it off. Ask your host or a technical friend if unsure.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open Plugins, then Add New, and search for "contact form".</li>
<li>Compare two results by last update, installs and rating, and write down which you would pick and why.</li>
<li>Install and activate one plugin you need on a practice site.</li>
<li>List which installed plugins you can delete.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Plugins add features but also risk and weight; install only what you need.</li>
<li>Check source, update date, installs, reviews and support before installing.</li>
<li>Use one plugin per purpose and delete unused plugins.</li>
<li>Never use pirated or emailed plugin files.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Widgets, blocks and contact forms',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to add extra content areas such as a footer and sidebar, use design blocks to build attractive pages, and create a working contact form. A contact form is often the main reason a website exists, so it must work and be tested.</p>
<h3>Widgets and block areas</h3>
<p>A <strong>widget</strong> is a small block of content in a side area of the page, for example a sidebar or the footer. Classic themes manage them under <strong>Appearance</strong> and then <strong>Widgets</strong>. Block themes let you edit the same areas inside the Site Editor. Typical items to place in a footer:</p>
<ul>
<li>Your organisation name and short description.</li>
<li>Phone, email, physical address and opening hours.</li>
<li>Links to important pages and social media.</li>
<li>Links to your privacy policy.</li>
</ul>
<p>Keep side areas simple. Too many widgets distract from the main message and slow the page.</p>
<h3>Design blocks that help</h3>
<p>Beyond paragraphs and images, the editor has blocks that make pages look professional without code:</p>
<table>
<thead><tr><th>Block</th><th>Use it for</th></tr></thead>
<tbody>
<tr><td>Columns</td><td>Put text beside an image or show three services side by side</td></tr>
<tr><td>Group or Row</td><td>Wrap blocks together to give a coloured background or spacing</td></tr>
<tr><td>Cover</td><td>A large image with text on top, good for the top of the home page</td></tr>
<tr><td>Buttons</td><td>Clear calls to action such as "Donate" or "Enrol now"</td></tr>
<tr><td>Gallery</td><td>A neat grid of photos</td></tr>
<tr><td>Table</td><td>Timetables and price lists</td></tr>
<tr><td>Embed (video, map)</td><td>Show a video or a map location</td></tr>
</tbody>
</table>
<p>Check how columns look on a phone, where they stack. Keep text short and use one clear button per section.</p>
<h3>Building a contact form</h3>
<p>Basic WordPress has no full contact form, so use a trusted form plugin (see the previous lesson). General steps, which vary by plugin:</p>
<ol>
<li>Install and activate the plugin.</li>
<li>Create a new form. Add only what you need: name, email, message. Every extra field lowers the number of people who finish.</li>
<li>Set where messages go: the email of someone who will answer within a day or two.</li>
<li>Set a friendly confirmation message, for example "Thank you. We will reply within two working days."</li>
<li>Turn on spam protection, such as a hidden trap field or a simple challenge.</li>
<li>Insert the form on the Contact page using a block or shortcode provided by the plugin.</li>
<li><strong>Test it</strong> by sending a message yourself, and check it arrives and is not in the spam folder.</li>
</ol>
<p>Also show a phone number and a WhatsApp link where appropriate, because many people prefer to call or chat. Say clearly what you will do with the data (see the privacy lesson in Module 5).</p>
<h3>Try it yourself</h3>
<ol>
<li>Build a Contact page with a Columns block: contact details on one side, a map or photo on the other.</li>
<li>Install a form plugin and create a form with name, email and message.</li>
<li>Place it on the Contact page and send yourself a test message.</li>
<li>Add contact details and a link to the privacy policy in the footer.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Use footers and side areas for contact details and key links, and keep them uncluttered.</li>
<li>Columns, Cover, Buttons and Gallery blocks give a professional look without code.</li>
<li>Ask only for the fields you need in a form and turn on spam protection.</li>
<li>Always test the form and confirm that messages arrive.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'SEO and speed basics',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to apply basic search engine optimisation (SEO) to your pages and make simple improvements that speed up your site. Both help real people: those searching for you, and those waiting for your page to load on a slow connection.</p>
<h3>What SEO is</h3>
<p><strong>SEO</strong> means helping search engines understand your pages so that they show them to people searching for what you offer. No one can guarantee a top position, and anyone who promises it should be doubted. Good basics, repeated steadily, work well over months.</p>
<h3>On-page basics</h3>
<ol>
<li><strong>Know the question.</strong> For each page, think of the words a visitor would type, such as "computer training Gulu".</li>
<li><strong>Page title.</strong> Write a clear title containing those words naturally, about 50 to 60 characters.</li>
<li><strong>Meta description.</strong> This is the short summary that may appear under your title in search results. Write 120 to 160 characters that invite a click. An SEO plugin gives you a box for it.</li>
<li><strong>Headings.</strong> Use one main heading and meaningful sub-headings.</li>
<li><strong>Helpful content.</strong> Answer the visitor's question fully and honestly. Do not stuff keywords; write for people first.</li>
<li><strong>Images.</strong> Add alt text and descriptive file names.</li>
<li><strong>Clean links.</strong> Use readable permalinks and internal links.</li>
<li><strong>Local details.</strong> Show your town, address and phone number consistently. Create a free business listing on the main map and search services to make local searches easier.</li>
</ol>
<h3>Sitemaps and search visibility</h3>
<p>A <strong>sitemap</strong> is a list of your pages that search engines read. Most SEO plugins and WordPress itself create one automatically. Make sure the setting that discourages search engines is switched off once you launch. You can add your site to the free search console tools of the major search engines to see how it appears and spot problems.</p>
<h3>Speed basics</h3>
<p>Visitors leave slow sites. Try these steps in order of impact:</p>
<ul>
<li><strong>Optimise images</strong> (the biggest gain for most sites).</li>
<li><strong>Choose a lightweight theme</strong> and avoid heavy sliders and auto-playing video.</li>
<li><strong>Delete unused plugins</strong> and themes.</li>
<li><strong>Use caching.</strong> A cache stores ready-made copies of your pages so they load faster. Many hosts include it; otherwise use one caching plugin.</li>
<li><strong>Keep PHP, WordPress and plugins updated</strong> and choose hosting with servers reasonably close to your visitors.</li>
<li><strong>Limit outside scripts</strong> such as many tracking tools and web fonts.</li>
</ul>
<h3>Measure first</h3>
<p>Use a free page-speed testing tool to test your home page on mobile. Note the score and the suggestions, make one change at a time, and test again. A fast phone experience matters most in places where mobile data is expensive or slow.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose one page and decide the phrase a visitor might search for.</li>
<li>Write a page title and a meta description for it, counting characters.</li>
<li>Test your home page with a free speed tool and note the three biggest suggestions.</li>
<li>Fix one suggestion, for example by compressing the largest image, and test again.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>SEO is about clear titles, helpful content and good structure, with no guaranteed ranking.</li>
<li>Write a title and a meta description for each important page.</li>
<li>Optimise images first when you want a faster site.</li>
<li>Measure, change one thing and measure again.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which is a good rule for choosing a theme?
- Pick the one with the most animations.
* Choose a light, mobile-friendly theme that is updated regularly and comes from a reputable source.
- Download a free copy of a paid theme.
- Choose the one with the most colours.
= Simple, fast, supported themes work best for small organisations, especially on mobile data.

Q: What happens to your content when you switch to a new theme?
* It stays, but the layout and look change.
- It is deleted.
- It is moved to a backup folder.
- It becomes private.
= Themes control design, not content. Still, take a backup before changing the theme on a live site.

Q: Which are good checks before installing a plugin? (choose all that apply)
* When it was last updated
* The number of active installs and reviews
- Whether it came as an email attachment
- Whether it has the longest name
= Choose maintained, well-reviewed plugins from official sources, never from unknown files.

Q: Why delete unused plugins instead of only deactivating them?
- They use your internet data.
* Even inactive plugins can contain security holes.
- WordPress forbids inactive plugins.
= Unused code still sits on your server. Removing it reduces risk.

Q: What is the best practice for contact form fields?
- Ask for as much information as possible.
- Ask only for a phone number.
* Ask only for what you need, such as name, email and message.
= Every extra field makes people less likely to finish the form.

Q: What must you do after building your contact form?
- Nothing, forms always work.
* Send a test message and confirm it arrives.
- Turn off the email address.
= Testing proves the messages reach a person and are not blocked or lost in spam.

Q: Which step usually gives the biggest speed improvement for a small site?
- Adding more plugins
- Adding a large slider
* Compressing and resizing images
- Using more fonts
= Large photos are the usual cause of slow pages, particularly on mobile data.

Q: What is a meta description?
* A short summary that may appear under your title in search results
- A hidden password
- A type of image
= Writing a clear, inviting description can encourage more people to click your result.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 5
        [
            'title'   => 'Module 5: Launch and look after it',
            'summary' => 'Check the site before launch, add legal and privacy pages, measure visits, build safe security habits and plan a capstone site.',
            'lessons' => [
                [
                    'title'   => 'The pre-launch checklist',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to test your site thoroughly and launch it with confidence. A careful checklist catches the embarrassing mistakes, such as a broken link on the home page or a "Lorem ipsum" paragraph, before the public sees them.</p>
<h3>Content checks</h3>
<ul>
<li>Read every page aloud once. You will catch spelling mistakes and awkward sentences.</li>
<li>Check names, phone numbers, email addresses and prices against your records.</li>
<li>Remove placeholder text, sample pages and test posts.</li>
<li>Make sure every page has a purpose and a clear next step, such as a button or contact link.</li>
<li>Confirm you have permission for every photo, logo and quotation.</li>
</ul>
<h3>Function checks</h3>
<ol>
<li>Click every menu and footer link, and every button.</li>
<li>Submit your contact form and confirm the email arrives. Try a mistake in the form to see the error message.</li>
<li>Test the search box if you have one.</li>
<li>Check the 404 page, which is what appears when a page is not found. Make sure it offers a way back home.</li>
<li>Check that downloads such as PDFs open.</li>
</ol>
<h3>Device and browser checks</h3>
<p>View the site on at least one phone, a laptop and, if you can, a tablet. Try two different browsers. Turn the phone sideways. Are text and buttons large enough to read and tap? Do images fit the screen? Test on a slow connection if possible.</p>
<h3>Technical checks</h3>
<ul>
<li>The address starts with <code>https://</code> and the padlock shows, with no warnings.</li>
<li>Permalinks are the clean format you chose.</li>
<li>The setting that discourages search engines is <strong>switched off</strong>.</li>
<li>A site icon and logo appear.</li>
<li>Page titles and meta descriptions exist for your main pages.</li>
<li>A backup system and updates are working.</li>
<li>Your domain is set to renew.</li>
</ul>
<h3>Choose a launch day</h3>
<p>Launch when someone can respond to messages, not just before a weekend or holiday. Announce it where your audience already is: WhatsApp groups, social media, email, printed material, business cards and signs. Add the web address to your email signature and flyers.</p>
<h3>After launch</h3>
<p>Ask five people to use the site and tell you what confused them. Watch them without helping. Collect their comments, fix the top three issues, and repeat in a month. A website is never finished; it improves in small steps.</p>
<h3>Try it yourself</h3>
<ol>
<li>Copy the checklist above into a document and add any items specific to your site.</li>
<li>Run through it on your practice site and tick each item.</li>
<li>Ask one friend to test the site on their phone and list anything confusing.</li>
<li>Fix at least two items from the list.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Check content, function, devices and technical settings before launch.</li>
<li>Switch off the "discourage search engines" setting when you go live.</li>
<li>Test on real phones and with real people.</li>
<li>Announce the launch and improve the site in small steps afterwards.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Legal pages, privacy and analytics',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain which legal and privacy pages a site should carry, handle visitor data respectfully and measure visits with analytics. This lesson is general education and not legal advice; ask a qualified person to review your final texts.</p>
<h3>Why privacy matters</h3>
<p>Whenever your site collects personal data, such as names and emails from a contact form, or records visitors with analytics tools, you handle other people's information. Uganda has a data protection law, the Data Protection and Privacy Act, and an office that oversees it. Good practice is to collect little, tell people clearly why, keep it safe and delete it when no longer needed. Check the current rules with the official authority.</p>
<h3>Pages to prepare</h3>
<table>
<thead><tr><th>Page</th><th>What it contains</th></tr></thead>
<tbody>
<tr><td>Privacy policy</td><td>What data you collect, why, who sees it, how long you keep it, how people can ask to see or remove it, and who to contact</td></tr>
<tr><td>Terms of use</td><td>Rules for using the site, ownership of content, limits of responsibility (useful for shops and larger sites)</td></tr>
<tr><td>Cookie notice</td><td>Explains small files stored in the browser by analytics, ads or embedded tools, and lets visitors choose</td></tr>
<tr><td>Contact page</td><td>Real organisation name, address and contact details, which builds trust</td></tr>
</tbody>
</table>
<p>WordPress has a privacy page helper under <strong>Settings</strong> and then <strong>Privacy</strong> that creates a draft you must edit. Do not copy another organisation's policy word for word, because your situation differs. Write in simple language.</p>
<h3>Cookies and consent</h3>
<p>A <strong>cookie</strong> is a small file a website stores in the visitor's browser. Essential ones keep the site working. Others record behaviour for statistics or advertising. If you use non-essential cookies, tell visitors and let them accept or refuse; a cookie-notice plugin can do this. If you use no tracking or advertising cookies, say so.</p>
<h3>Forms and data handling</h3>
<ul>
<li>Place a short sentence near each form: "We use your details only to reply to your message. See our privacy policy."</li>
<li>For newsletters, ask for clear consent with an unticked box, and never add people without asking.</li>
<li>Do not publish photos of identifiable people, especially children, without proper permission.</li>
<li>Delete old messages you no longer need.</li>
</ul>
<h3>Analytics</h3>
<p><strong>Analytics</strong> tools show how many people visit, which pages they read, what device they use and how they found you. Choose a tool, set it up through its official plugin or settings, and add its notice to your privacy page. Start with a few simple questions:</p>
<ol>
<li>How many visitors came this month?</li>
<li>Which pages were most popular?</li>
<li>Where did they come from (search, social media, direct)?</li>
<li>How many contacted us or completed our main action?</li>
</ol>
<p>Review once a month and act on what you learn: improve popular pages, fix pages people leave quickly, and share more on the channels that bring visitors.</p>
<h3>Try it yourself</h3>
<ol>
<li>Use the WordPress privacy page helper to create a draft policy.</li>
<li>Edit it to list your actual forms and what you do with the data.</li>
<li>Add the privacy page link to your footer and next to your contact form.</li>
<li>Write the four monthly questions you will ask of analytics for your site.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Collect only the personal data you need, explain why and protect it.</li>
<li>Prepare a privacy policy, a cookie notice if you use tracking, and a clear contact page.</li>
<li>Get clear consent for newsletters and for photos of people.</li>
<li>Use analytics for a few useful questions and review it monthly. This is general information, not legal advice.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Security habits and a maintenance routine',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to put practical protections around your site and follow a simple weekly, monthly and yearly maintenance routine. A hacked site can lose your visitors' trust and cost you days of work, but most attacks are stopped by a handful of habits.</p>
<h3>Why sites get hacked</h3>
<p>Most attacks are automatic. Programs scan the internet for sites with weak passwords, out-of-date software or unsafe plugins. Small sites are targets precisely because they are often neglected. The usual causes are:</p>
<ul>
<li>Easy or reused passwords.</li>
<li>Outdated WordPress, themes or plugins.</li>
<li>Pirated themes and plugins with hidden malware.</li>
<li>Too many administrator accounts.</li>
<li>No backups, so recovery is impossible.</li>
</ul>
<h3>Core security habits</h3>
<ol>
<li><strong>Strong, unique passwords</strong> for WordPress, hosting, email and domain accounts. Use a password manager or a long passphrase.</li>
<li><strong>Two-step verification (2FA)</strong> on your login, using an authenticator app or text code, and on your hosting and email. Even if someone learns your password, they still cannot log in.</li>
<li><strong>Update</strong> core, themes and plugins promptly.</li>
<li><strong>Limit login attempts</strong> and consider a protection feature from your host or a security plugin.</li>
<li><strong>Use least-privilege roles</strong> and delete old accounts.</li>
<li><strong>Back up</strong> automatically and keep a copy away from the host.</li>
<li><strong>Use HTTPS</strong> on every page.</li>
<li><strong>Choose reputable hosting</strong> that offers security monitoring.</li>
<li><strong>Be wary of phishing.</strong> Never click links in emails claiming to be from your host or WordPress asking for your password. Type the address yourself.</li>
</ol>
<h3>Warning signs</h3>
<p>Pages you did not create, strange links or advertising, redirects to other sites, a warning from your browser or search engine, your host suspending the account, or sudden floods of spam all suggest a problem. If this happens, stay calm: change passwords, notify your host, restore a clean backup if you have one, update everything and remove unknown users and plugins.</p>
<h3>A maintenance routine</h3>
<table>
<thead><tr><th>How often</th><th>Task</th></tr></thead>
<tbody>
<tr><td>Weekly</td><td>Apply updates after a backup; check comments and form messages; look at the site on your phone</td></tr>
<tr><td>Monthly</td><td>Review analytics; test the contact form; check broken links and speed; review users</td></tr>
<tr><td>Quarterly</td><td>Test restoring a backup; remove unused plugins and themes; refresh old content</td></tr>
<tr><td>Yearly</td><td>Renew domain and hosting; review the privacy policy; check prices and staff contacts</td></tr>
</tbody>
</table>
<p>Write the routine on a checklist with a named person responsible, so it does not depend on one person's memory. Keep a short log of what you changed and when.</p>
<h3>Try it yourself</h3>
<ol>
<li>Change your WordPress password to a long unique passphrase and turn on 2FA if available.</li>
<li>List all user accounts and remove or downgrade any that are not needed.</li>
<li>Write your own weekly, monthly, quarterly and yearly checklist.</li>
<li>Put the first task for each period in your phone calendar as a reminder.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Most attacks are automatic and exploit weak passwords and old software.</li>
<li>Use strong passwords, 2FA, updates, least privilege and backups.</li>
<li>Know the warning signs and have a recovery plan.</li>
<li>A written routine with a named person keeps the site safe.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Accessibility and the capstone project',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to check your site for basic accessibility and plan a complete capstone website for a small NGO or business. Accessibility means that people with different abilities, devices and connections can use your site. It is the right thing to do and it improves the site for everyone.</p>
<h3>Why accessibility matters</h3>
<p>Some visitors are blind, have low vision, are colour-blind, are deaf, have difficulty using a mouse, or have a learning difficulty. Others have a broken screen, bright sunlight or old devices. Designing for them helps everyone. International guidelines called WCAG describe how to do this.</p>
<h3>Practical checks</h3>
<ul>
<li><strong>Alt text</strong> on every informative image.</li>
<li><strong>Headings</strong> in a logical order.</li>
<li><strong>Colour contrast:</strong> text must stand out from its background. Pale grey text on white is hard to read. Free contrast checkers exist.</li>
<li><strong>Do not use colour alone</strong> to explain something, such as "click the red button".</li>
<li><strong>Readable text:</strong> at least 16 pixels for body text, clear spacing and left-aligned paragraphs.</li>
<li><strong>Descriptive links:</strong> "Download the 2025 annual report (PDF)" not "click here".</li>
<li><strong>Keyboard use:</strong> press <code>Tab</code> to move through links and buttons; you should see where you are and be able to reach everything.</li>
<li><strong>Videos:</strong> provide captions or a transcript.</li>
<li><strong>Forms:</strong> each field has a visible label and clear error messages.</li>
</ul>
<h3>Capstone brief</h3>
<p>Plan and build a five-page website for a small organisation of your choice, for example a youth skills centre, a farmers' group, a tailoring business or a clinic. Your final work should include:</p>
<ol>
<li><strong>A planning sheet:</strong> purpose sentence, audience description, site map and content list.</li>
<li><strong>The site:</strong> Home, About, Services or Programmes, News (with two posts), and Contact with a working form.</li>
<li><strong>Settings:</strong> site title and tagline, clean permalinks, static home page, comments moderated.</li>
<li><strong>Design:</strong> a chosen theme, logo, two brand colours, one or two fonts and consistent spacing.</li>
<li><strong>Media:</strong> at least five optimised images with alt text.</li>
<li><strong>Navigation:</strong> a short menu and footer with contact details.</li>
<li><strong>Legal:</strong> privacy policy and a cookie notice if you use tracking.</li>
<li><strong>Quality:</strong> a completed pre-launch checklist, a speed test result and an accessibility check.</li>
<li><strong>Care plan:</strong> a written backup, update and security routine with responsible people.</li>
</ol>
<p>Judge your work by asking: could a visitor on a basic phone quickly understand who we are, trust us and contact us?</p>
<h3>Try it yourself</h3>
<ol>
<li>Run the accessibility checks on your practice site and fix three problems.</li>
<li>Choose your capstone organisation and write its planning sheet.</li>
<li>Build the five pages and the contact form.</li>
<li>Complete the pre-launch checklist and show the site to a friend for feedback.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Accessible sites use alt text, good headings, strong contrast and clear links.</li>
<li>Test with the keyboard and on small screens.</li>
<li>The capstone brings together planning, building, privacy, security and care.</li>
<li>Ask real users for feedback and keep improving.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What should you do with the "discourage search engines" setting at launch?
- Leave it on so visitors cannot find mistakes.
* Switch it off so search engines can show your site.
- Rename it.
- Move it to the footer.
= While building you may discourage indexing, but forgetting to turn it off means your launched site will not appear in search results.

Q: Which is true about the privacy policy of your site?
- You can copy another organisation's word for word.
- It is only needed for shops.
* It should describe your own data practices in simple language.
= Your policy must reflect what you actually collect and do, such as forms and analytics. Have it reviewed by a qualified person.

Q: What does two-step verification (2FA) do?
* Requires a second proof, such as a code, even if someone has your password
- Makes your password shorter
- Encrypts your photos
- Blocks all visitors
= A stolen password alone is not enough to log in when 2FA is on.

Q: Which are common reasons small WordPress sites get hacked? (choose all that apply)
* Weak or reused passwords
* Outdated plugins and themes
* Pirated themes with hidden malware
- Having too few images
= Most attacks are automatic and exploit neglected basics.

Q: Which task belongs in a quarterly maintenance routine?
- Change your site title
* Test restoring a backup
- Delete all posts
= Practising restoration proves your backups work. It is a good regular check.

Q: Which of these is good accessibility practice?
- Using pale grey text on a white background
* Providing captions or a transcript for videos
- Explaining a choice using colour alone
= Captions help deaf visitors and those in noisy places. Strong contrast and non-colour cues are also needed.

Q: Why should you ask real people to test the site after launch?
- To make the site slower
- Because it is legally required
* They will find confusing parts you no longer notice
= Fresh eyes spot problems quickly. Fix the top issues and repeat.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What is the best first step when planning a website?
- Choose a theme.
- Buy a domain.
* Write a clear purpose: who the site serves and what they can do.
- Install plugins.
= Purpose and audience guide every later decision, including pages, design and content.

Q: What does a domain name provide?
- Storage for your files
* The address people type to reach your site
- A security scan
- A contact form
= Hosting stores the site; the domain is the address that points to it.

Q: Which is a sensible way to register a domain?
- Under a freelancer's personal details
- Without renewal reminders
* Under your organisation's own details with auto-renew or a reminder
- Only for one year with no plan for renewal
= Losing control of your domain means losing your web address and email.

Q: A key difference of self-hosted WordPress.org compared to a hosted service is that:
- Updates and security are done for you.
- You cannot change the theme.
* You control the site and plugins but are responsible for updates, backups and security.
= Freedom brings responsibility. Module 5 teaches the routines.

Q: Where do you find the settings for site title, time zone and tagline?
- Appearance
* Settings, then General
- Media
- Comments
= General settings hold basic identity information. Save changes after editing.

Q: When should permalinks be chosen?
- After a year
* Before publishing content
- Only for posts
- Never
= Changing them later alters all addresses and can break saved links.

Q: Which role is best for a volunteer who writes drafts that must be reviewed before publishing?
- Administrator
- Editor
- Subscriber
* Contributor
= The Contributor role cannot publish. It follows the rule of least privilege.

Q: Why shouldn't several staff share one login?
- It is slower.
* You cannot tell who made which changes, and one leak affects everyone.
- WordPress forbids it technically.
= Individual accounts with the right roles improve security and accountability.

Q: What does a full WordPress backup include? (choose all that apply)
* Files such as themes, plugins and uploads
- Only the logo
* The database containing pages, posts and settings
- Only the menu
= Without both parts you cannot fully restore the site.

Q: What is the best order for applying updates safely?
- Update, then back up
* Back up, update, then test the main pages
- Delete plugins, then update
- Update everything at midnight without testing
= A backup gives you a way back if something breaks.

Q: A page differs from a post because:
- Pages are always private.
* Pages hold lasting information while posts are dated news items
- Posts cannot have images.
- Pages have categories and posts do not.
= Use pages for About and Contact, posts for news and updates.

Q: What is the best choice for a heading level?
* Choose the level that matches the structure of the content
- Choose H4 because it looks small
- Skip from H2 to H5
- Use bold text instead of headings
= Headings give structure to people using screen readers and to search engines.

Q: A good image for a web page is:
- A 6 MB photo straight from the camera
- A file called IMG_001.jpg with no alt text
* A cropped, resized and compressed image with descriptive alt text
- A screenshot of the text
= Optimised images load faster and are more accessible.

Q: Which are good practices for navigation menus? (choose all that apply)
* Keep the menu short with clear labels
- Add every page to the main menu
* Test on a phone
- Hide the contact page
= Short, expected labels and mobile testing help visitors find things quickly.

Q: What is the difference between categories and tags?
- Tags are always required.
- Categories are for images.
* Categories are broad topics; tags are specific keywords.
= Use a few categories and tag sparingly.

Q: What does a theme control?
- Your domain name
* The look and layout of the site
- Your backup schedule
- Your passwords
= Changing the theme changes appearance but keeps your content.

Q: Before installing a plugin you should:
- Install it from a file that was emailed to you
- Install it first and read the reviews later
* Check the last update date, installs and reviews, and whether you really need it
- Install five similar plugins to compare
= Each plugin adds risk and weight, so be selective.

Q: Why should you use one plugin per purpose?
- Plugins cannot be uninstalled.
* Two plugins doing the same job often conflict and slow the site
- It is a legal rule.
= Keep the set small: one for security, one for caching, and so on.

Q: What should you do after creating a contact form?
- Publish and forget.
- Collect as many fields as possible.
* Send a test message and confirm it arrives
= Many forms silently fail, so testing is essential.

Q: Which is the best way to speed up a slow site?
* Compress large images and remove unused plugins
- Add a video background
- Add more sliders
- Install several caching plugins
= Images and unused code are the usual causes. Measure before and after.

Q: Which statement about SEO is true?
- Anyone can guarantee you the top place in search results.
* Clear titles, helpful content and good structure improve your chances over time
- Repeating a keyword 50 times works best
= No one controls search rankings. Writing for people and keeping the basics tidy works best.

Q: Which item belongs in a pre-launch checklist? (choose all that apply)
* Test all links and the contact form
* Check the site on a phone
- Leave the sample "Hello world" post
* Make sure search engines are not blocked
= A careful check prevents public mistakes and lost visibility.

Q: What is a cookie notice for?
- It speeds up the site.
* It tells visitors about cookies used for statistics or advertising and lets them choose
- It replaces the privacy policy completely
- It hides your content
= Transparency about tracking is part of respecting visitor privacy.

Q: Which is a good practice for collecting emails for a newsletter?
- Add everyone from your phone contacts.
- Pre-tick the subscribe box.
* Ask for clear consent with an unticked box
= People must choose to join. This protects them and your reputation.

Q: What is the benefit of two-step verification?
- It makes pages load faster.
* Even if your password is stolen, the attacker needs a second proof
- It removes the need for backups.
= It adds a strong layer to your accounts.

Q: Which alt text is best for a photo of learners using laptops?
- Image
- IMG_4521
* Learners at laptops during a coding class in Gulu
- Click here
= Alt text should describe what the image shows and why it matters in one short sentence.

Q: Which maintenance task should be weekly?
* Apply updates after a backup and review messages
- Rewrite the whole site
- Change the domain
- Buy new hosting
= Regular small updates keep the site safe and healthy.
QUIZ,
];
