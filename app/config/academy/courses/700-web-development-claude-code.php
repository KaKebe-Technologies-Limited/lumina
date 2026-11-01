<?php
return [
    'slug'        => 'web-development-claude-code',
    'title'       => 'Web Development with Claude Code: From First Page to Live Application',
    'category'    => 'web',
    'level'       => 'beginner',
    'icon'        => 'code',
    'sort'        => 700,
    'summary'     => 'Build real websites and web applications from scratch with HTML, CSS, JavaScript, PHP and MySQL, using Claude Code as an AI assistant you direct, review and test, then secure and deploy your work.',
    'description' => '<p>This course teaches you to build websites and web applications from your first page to a live, secure application, and to use an AI coding assistant, Claude Code, as a helper rather than a replacement for understanding. It is for students, beginners, aspiring developers and entrepreneurs, and every stage is project-driven: you build, test and explain real work.</p><p>You will set up your tools, learn HTML, CSS and JavaScript, and then learn how Claude Code works in a project: writing clear prompts, planning first, reviewing every change, and using Git to recover from mistakes. You will add a backend with PHP and a MySQL database, build secure registration and login with roles, and bring everything together in complete applications with dashboards and reports. Later stages cover APIs and payments concepts, website security, testing and debugging, and deploying to a real hosting account with a public address.</p><p>The emphasis is on understanding and verifying the work an AI produces: you will learn to spot invented code, security weaknesses and wrong assumptions before they reach your users. Tools, accounts and features change quickly, so the course teaches lasting skills and tells you where to check the current official documentation. If you cannot use Claude Code, you can follow every exercise with another AI assistant or by writing the code yourself. The course ends with a final project chosen from eight options, from a portfolio website to an e-commerce or loan management system, which you demonstrate with evidence of testing and deployment.</p>',
    'outcomes'    => [
        'Explain how websites work and the roles of frontend, backend and full-stack development',
        'Set up a professional local development environment with an editor, Git, a terminal and XAMPP',
        'Build responsive pages with HTML, CSS and JavaScript',
        'Direct Claude Code with clear prompts, plan first, review every change and recover with Git',
        'Build dynamic PHP and MySQL applications with secure forms, sessions and prepared statements',
        'Implement registration, login, roles and protected pages that are enforced on the server',
        'Integrate APIs, find and fix common security weaknesses, and test an application thoroughly',
        'Deploy an application to a public URL and present a documented final project',
    ],
    'requirements' => ['A computer (Windows, Mac or Linux) that can install an editor and XAMPP, and an internet connection', 'No programming experience needed', 'Optional: access to Claude Code or another AI assistant (the exercises also work by hand)', 'A free GitHub account (the lessons show how to create one)'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'MDN Web Docs', 'url' => 'https://developer.mozilla.org/'],
        ['title' => 'Git documentation', 'url' => 'https://git-scm.com/doc'],
        ['title' => 'PHP manual', 'url' => 'https://www.php.net/manual/en/'],
        ['title' => 'OWASP', 'url' => 'https://owasp.org/'],
        ['title' => 'Visual Studio Code', 'url' => 'https://code.visualstudio.com/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- STAGE 1
        [
            'title'   => 'Stage 1: Foundations and Setup',
            'summary' => 'Understand how websites work, what AI coding assistants such as Claude Code can and cannot do, and set up a working local development environment.',
            'lessons' => [
                [
                    'title'   => 'How websites work and where AI coding fits in',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to explain how a website reaches your screen, tell websites, web applications and mobile apps apart, describe frontend, backend and full-stack work, and say what an AI coding assistant such as Claude Code is. This matters because you cannot direct an assistant, or check its work, if you do not know what you are asking it to build.</p>

<h3>How a website reaches your screen</h3>
<p>When you type an address into a browser, the <strong>browser</strong> (the client) sends a request across the internet to a <strong>server</strong>, a computer that stores the site. The server replies with files: HTML for structure, CSS for appearance and JavaScript for behaviour. The browser reads them and draws the page. A <strong>domain name</strong> is the human-friendly address of that server.</p>
<ol>
<li>You enter an address or tap a link.</li>
<li>The browser asks the server for the page.</li>
<li>The server sends back files (and sometimes runs code first to build them).</li>
<li>The browser displays the result and runs any JavaScript.</li>
</ol>

<h3>Websites, web applications and mobile apps</h3>
<table>
<thead><tr><th>Type</th><th>What it does</th><th>East African example</th></tr></thead>
<tbody>
<tr><td>Website</td><td>Mostly shows information; the same for every visitor</td><td>A school page with fees, news and contacts</td></tr>
<tr><td>Web application</td><td>Lets users log in, enter data and get personal results</td><td>A clinic booking system, a SACCO member portal</td></tr>
<tr><td>Mobile app</td><td>Installed on a phone, may work offline and use the camera or sensors</td><td>A mobile money app</td></tr>
</tbody>
</table>
<p>The lines blur: a web application opened on a phone is still a web application.</p>

<h3>Frontend, backend and full-stack</h3>
<p>The <strong>frontend</strong> is everything the visitor sees and touches: layout, buttons, forms. The <strong>backend</strong> is the hidden part on the server: it checks logins, saves records in a <strong>database</strong> and decides what each user may see. A <strong>full-stack</strong> developer works on both. In a farmers' group site, the frontend shows the price list; the backend stores prices that the group's secretary updates.</p>

<h3>AI-assisted software development</h3>
<p>An AI coding assistant is a tool that reads your request in plain language and proposes code, explanations or fixes. <strong>Claude</strong> is Anthropic's AI model family. <strong>Claude Code</strong> is Anthropic's AI coding assistant that runs in the terminal inside your project folder: it can read project files, propose edits and run commands, and it asks your permission before it does. Features, plans and access rules change often, so always check the current official documentation. If you cannot use Claude Code because of cost, device or region, you can use another AI assistant or do the exercises by hand; the skills in this course transfer.</p>

<h3>Worked example: asking for a small page</h3>
<p>A tailor in Masaka wants a page showing her prices. A weak request is "make me a website". A clear one is below.</p>
<pre><code>Create one HTML page for "Mariam Tailoring, Masaka".
Show a heading, three services with prices in UGX,
and a phone number. Keep it simple. First tell me
the file name and the structure you plan to use.</code></pre>
<p>It names the content and limits and asks for a plan first.</p>

<h3>Project brief template</h3>
<pre><code>Project name:
Who will use it:
Problem it solves:
Pages or features (3 to 5):
Information it must show:
What it must NOT do:
Who checks the result:</code></pre>

<h3>Case scenario</h3>
<p>Peter, a youth SACCO treasurer in Mbale, asked an assistant to "build the full SACCO system" and received a huge pile of code he could not read. When members' balances showed wrongly, he had no idea where to look. He restarted with a one-page brief: only a public page with meeting dates and a contact form. He read every file before using it. The page worked, and later features were added one at a time. Lesson: start small and understand each piece.</p>

<blockquote><p><strong>Quick check:</strong> Which part of a website saves members' records in a database?</p><p>Answer: The backend, because it runs on the server and handles stored data; the frontend only displays and collects it.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why ask an assistant for a plan before it creates files?</p><p>Answer: So you can review and correct its approach first, which is cheaper than undoing wrong changes afterwards.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open any website you use and write down one thing it shows (frontend) and one thing it must store (backend).</li>
<li>Decide whether it is a website, a web application or a mobile app, and give one reason.</li>
<li>Fill in the project brief template for a page your family or school could use.</li>
<li>Rewrite "make me a website" as a clear request of five lines.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A browser requests files from a server and displays them.</li>
<li>Websites inform; web applications let users log in and work with data.</li>
<li>Frontend is what users see; backend is the server logic and database.</li>
<li>Claude Code works in your project folder and asks permission; you stay in charge.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Working with AI responsibly: skills, careers and real problems',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to describe how AI assistants help write and maintain code, explain the difference between generating code and understanding it, name career paths, choose a real community problem for a website, and follow basic rules for responsible AI-assisted coding. This matters because the learners who grow fastest are those who use AI to learn, not those who use it to skip learning.</p>

<h3>How assistants help write and maintain code</h3>
<ul>
<li><strong>Drafting:</strong> a first version of a page, form or function from a description.</li>
<li><strong>Explaining:</strong> turning unfamiliar code into plain sentences.</li>
<li><strong>Debugging:</strong> suggesting why an error appears and how to fix it.</li>
<li><strong>Maintaining:</strong> renaming things, tidying repeated code, updating text across many files.</li>
<li><strong>Documenting:</strong> writing notes so the next person understands the project.</li>
</ul>
<p>An assistant can be wrong with confidence. It may invent a function that does not exist or assume a setting you do not have. So every suggestion is a proposal, never a fact.</p>

<h3>Generating code versus understanding code</h3>
<p>Generated code that you cannot explain is a debt. Imagine a clinic booking page that works today. In a month a patient reports a wrong date. If you never understood how the date is handled, you must ask again and hope. If you read it, tested it and asked the assistant to explain each part, you can fix it in minutes. A simple habit: after any generated change, say in your own words what it does. If you cannot, ask for an explanation or a simpler version.</p>

<h3>Career opportunities</h3>
<table>
<thead><tr><th>Path</th><th>What the work looks like</th></tr></thead>
<tbody>
<tr><td>Frontend developer</td><td>Builds pages and interfaces for businesses and agencies</td></tr>
<tr><td>Backend developer</td><td>Builds server logic, databases and integrations</td></tr>
<tr><td>Full-stack developer</td><td>Delivers a whole small system for a client</td></tr>
<tr><td>Freelancer</td><td>Builds sites for shops, schools and clinics, often remotely</td></tr>
<tr><td>Own business owner</td><td>Uses coding to run or grow a venture</td></tr>
</tbody>
</table>
<p>Employers and clients still need people who can plan, test, secure and explain software. AI tools change how the work is done; they do not remove the need for judgement.</p>

<h3>Real problems a website can solve</h3>
<p>Good project ideas start with a person and a pain, not a technology. A farmers' group loses sales because buyers cannot see current maize prices. A school answers the same fee questions by phone fifty times a week. A small shop owner wants customers to see stock without calling. A clinic wants appointment requests collected before patients queue. Each one has a clear user, a clear need and a small first feature.</p>

<h3>Responsible AI-assisted coding</h3>
<ol>
<li>Never paste passwords, API keys, personal data or confidential client data into any AI tool.</li>
<li>Never accept generated changes without reading and testing them.</li>
<li>Use version history so you can undo changes (you will learn this in Stage 2).</li>
<li>Check licences and facts; do not claim code as your own understanding if it is not.</li>
<li>Tell clients honestly when AI helped, if they ask.</li>
</ol>

<h3>Problem statement template</h3>
<pre><code>Who has the problem:
What happens now (the pain):
How a website would help:
The smallest useful first version:
How I will know it works:</code></pre>

<h3>Case scenario</h3>
<p>Aisha built a shop website in Jinja and pasted her real supplier price list, including the supplier's phone numbers and a login password from her notes, into an AI chat to format it. Later she realised she had shared private information she had no right to share. She decided to remove the data from her notes, change the password and use made-up sample data from then on. The site was not harmed, but the trust risk was real. Lesson: use fake sample data in AI tools, always.</p>

<blockquote><p><strong>Quick check:</strong> A generated page works but you cannot explain one block of code. What should you do?</p><p>Answer: Ask for an explanation or a simpler version and test it, because code you cannot explain is hard to fix later.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Is it safe to paste a customer list into an AI chat to sort it?</p><p>Answer: No, because it exposes personal data; use invented sample rows instead.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List three problems in your community, school or business that a website could help with.</li>
<li>Pick the one with the clearest user and fill in the problem statement template.</li>
<li>Write the smallest useful first version in one sentence (for example, a single page with contacts and prices).</li>
<li>Write your own three personal rules for using AI safely and keep them where you code.</li>
<li>Assignment: submit your chosen problem, who it helps and your first version in half a page.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Assistants draft, explain, debug and document, but can be confidently wrong.</li>
<li>Understanding your code matters more than generating it.</li>
<li>Many career paths exist, and employers value judgement and testing.</li>
<li>Start from a real person's problem and build the smallest useful version.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Your toolbox: VS Code, Git, GitHub, the terminal and Claude Code',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to name the tools a web developer uses and what each is for, install and open a code editor, understand the terminal and basic commands, create a GitHub account safely, and describe what Claude Code needs to run. This matters because a well-prepared toolbox saves hours of confusion later.</p>

<h3>Tools and their jobs</h3>
<table>
<thead><tr><th>Tool</th><th>Job</th></tr></thead>
<tbody>
<tr><td>Visual Studio Code (VS Code)</td><td>A free code editor where you read and write files</td></tr>
<tr><td>Git</td><td>Records the history of your project so you can undo mistakes</td></tr>
<tr><td>GitHub</td><td>A website that stores Git projects online and helps people share work</td></tr>
<tr><td>Terminal</td><td>A text window where you type commands</td></tr>
<tr><td>Claude Code</td><td>An AI coding assistant that runs in the terminal in your project folder</td></tr>
<tr><td>Browser</td><td>Where you view and test your pages</td></tr>
</tbody>
</table>
<p>Download every tool from its official website only, and follow the current official installation guide. Versions and steps change, so this course does not give fixed commands or version numbers. Look for the download page for your operating system, such as Windows.</p>

<h3>Visual Studio Code</h3>
<p>VS Code shows your project folder on the left and the open file on the right. Look for the <strong>Explorer</strong> panel, the search tool, the built-in terminal and the extensions area. Useful habits: open a whole folder (not single files), save often, and turn on automatic saving if you keep forgetting. Add only a few well-known extensions; unknown ones can be a risk.</p>

<h3>Git and GitHub</h3>
<p><strong>Git</strong> lives on your computer. <strong>GitHub</strong> is an online home for Git projects. Create a GitHub account on the official site with a strong, unique password and turn on two-step verification if offered. Choose a professional username, because employers and clients will see it. Never share your password or access tokens with anyone, or with an AI tool.</p>

<h3>The terminal</h3>
<p>The terminal is a way to give written instructions to the computer. Four commands cover most early needs.</p>
<pre><code>pwd            show the current folder
ls             list files here (on Windows PowerShell, dir also works)
cd shop-site   move into the folder named shop-site
cd ..          go up one folder</code></pre>
<p>Always check which folder you are in before running anything. A command runs where you are standing.</p>

<h3>Claude Code: installing and access</h3>
<p>To use Claude Code, look for the official setup page in Anthropic's documentation and follow it for your system. Expect to need an internet connection, a supported computer and an account or access method that Anthropic currently offers; requirements, plans and costs change, so check the official pages rather than trusting a friend's older advice. When you start it inside your project folder, it asks permission before it reads, changes or runs things. Read each request and decline anything you do not understand. If you cannot use it, a different AI assistant in your browser works for the exercises, as long as you paste only safe sample content.</p>

<h3>Setup checklist template</h3>
<pre><code>[ ] Code editor installed and opens a folder
[ ] Git installed (check it from the terminal)
[ ] GitHub account created, strong password, two-step on
[ ] Terminal opens in my project folder
[ ] Claude Code (or another AI assistant) available
[ ] Modern browser ready</code></pre>

<h3>Case scenario</h3>
<p>Brian, a student in Gulu, followed a video that was two years old and installed an editor from a download site that was not the official one. His computer showed pop-ups and he lost a day. He decided to wipe the unofficial program, download from the official page and compare the steps with the current instructions. The setup then worked. Lesson: official sources and current documentation first, videos second.</p>

<blockquote><p><strong>Quick check:</strong> Which tool keeps the history of your project, and which one stores it online?</p><p>Answer: Git keeps the history on your computer; GitHub stores Git projects online.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why should you check your folder before running a terminal command?</p><p>Answer: Commands run in the folder you are in, so a wrong folder can change the wrong files.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Install your editor from its official site and open any empty folder in it.</li>
<li>Open the terminal and run pwd, ls and cd to move into and out of a folder you create.</li>
<li>Create your GitHub account with a strong password and two-step verification.</li>
<li>Find the official Claude Code setup page and note which requirements apply to you; if you cannot meet them, choose your alternative assistant.</li>
<li>Complete the setup checklist and tick what is done.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Each tool has one job: edit, record history, share, command, assist, view.</li>
<li>Always use official downloads and current documentation.</li>
<li>Protect your account details and never give them to an AI tool.</li>
<li>Know your current folder before you run commands.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Local servers, project structure and fixing setup problems',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to explain Node.js and package managers, use XAMPP to run a local PHP and MySQL environment, create and organise a first project folder, view a page on a local server, and troubleshoot common installation problems. This matters because you will test everything locally first, safely and for free, before any real visitor sees it.</p>

<h3>Local and live environments</h3>
<p>A <strong>local development environment</strong> is a server that runs on your own computer, so only you can see your work. A <strong>production</strong> (live) server is the public one. Working locally lets you make mistakes in private.</p>

<h3>Node.js and package managers</h3>
<p><strong>Node.js</strong> lets JavaScript run outside the browser and powers many developer tools. A <strong>package manager</strong> installs and updates ready-made code libraries, called packages, and records which ones your project needs. Some tools you meet, including some AI tools, may need Node.js; check the official documentation of the tool you use. Install it from the official site and follow its current guidance. Install only packages you recognise.</p>

<h3>XAMPP for PHP and MySQL</h3>
<p><strong>XAMPP</strong> bundles a web server (Apache), PHP and a MySQL-compatible database on your computer. After installing from the official site, open its control panel and start Apache. Website files go in a folder that XAMPP serves, often named htdocs inside the XAMPP folder. Look for it in the XAMPP location on your computer. Start MySQL only when a lesson needs a database.</p>

<h3>Worked example: first project folder</h3>
<p>For a farmers' group in Masaka, make this structure:</p>
<pre><code>farmers-site/
  index.html
  css/
    style.css
  js/
    app.js
  images/
  README.md</code></pre>
<p>Put the home page in index.html, the styles in css, scripts in js and pictures in images. Use lowercase names without spaces, because servers treat uppercase and lowercase as different on many systems.</p>
<p>A first index.html might be:</p>
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
  &lt;meta charset="utf-8"&gt;
  &lt;title&gt;Masaka Farmers Group&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
  &lt;h1&gt;Masaka Farmers Group&lt;/h1&gt;
  &lt;p&gt;Maize prices updated every Monday.&lt;/p&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>

<h3>Demonstration: view it on a local server</h3>
<ol>
<li>Create the folder farmers-site inside the folder XAMPP serves.</li>
<li>Save the code above as index.html inside it.</li>
<li>Open the XAMPP control panel and start Apache.</li>
<li>In your browser, type localhost followed by a slash and the folder name.</li>
<li>Edit the heading in your editor, save, and refresh the browser to see it change.</li>
</ol>

<h3>Troubleshooting guide</h3>
<table>
<thead><tr><th>Symptom</th><th>Likely cause and first step</th></tr></thead>
<tbody>
<tr><td>Apache will not start</td><td>Another program may use the same port; close it or check the control panel log</td></tr>
<tr><td>Page not found</td><td>Wrong folder or file name; check spelling and where the folder is</td></tr>
<tr><td>Command not recognised</td><td>The tool is not installed or the terminal needs reopening</td></tr>
<tr><td>Changes do not show</td><td>File not saved or the browser cached the page; save and refresh</td></tr>
</tbody>
</table>
<p>Read the exact error message, search the official documentation, and only then ask an assistant, pasting the message without any secrets.</p>

<h3>Case scenario</h3>
<p>Esther, in Kampala, put her clinic page on her desktop and double-clicked it. Her contact form, which needs a server, never worked. She decided to move the folder into the XAMPP web folder, start Apache and open it through localhost. The form then reached its PHP file. A week later Apache refused to start because a video-call program held the port; she closed it and tried again. Lesson: use a server for server features, and read the log.</p>

<blockquote><p><strong>Quick check:</strong> Why should project folder names avoid spaces and capital letters?</p><p>Answer: Servers may treat names with different capitals as different files, and spaces cause path errors.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A page will not appear at localhost. What is the first thing to check?</p><p>Answer: That Apache is running and the folder name is spelt correctly, because those are the commonest causes.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Install XAMPP from its official site and start Apache from the control panel.</li>
<li>Create a project folder for your own idea with the structure shown above.</li>
<li>Write a small index.html with a heading and one paragraph about your project.</li>
<li>View it through localhost, change the text and refresh to prove it is live locally.</li>
<li>Assignment: with VS Code, Git, your GitHub account, Claude Code or another assistant and a modern browser ready, submit a screenshot of your page on localhost and your completed setup checklist.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Local servers let you build and test privately before going live.</li>
<li>Node.js and package managers install tools; trust only packages you recognise.</li>
<li>XAMPP serves PHP and MySQL locally from its web folder.</li>
<li>Organised, lowercase folders prevent many errors; read error messages carefully.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: When you open a school website, what does the server send back to your browser?
- A finished picture of the page that cannot change
* Files such as HTML, CSS and JavaScript that the browser turns into the page
- Only the school's domain name
- A copy of the school's whole database
= The browser requests files and builds the page from HTML, CSS and JavaScript. Databases stay on the server.

Q: A clinic wants patients to log in and see their own appointments. What are they building?
- A static website
- A printed brochure
* A web application
- A browser extension only
= Logging in and showing personal data makes it a web application, not just an information website.

Q: In a booking system, which job belongs to the backend?
- Choosing the button colours
- Making the menu collapse on phones
* Checking the login and saving the booking in the database
- Choosing the heading font
= The backend runs on the server, handles logic and data. Colours and menus are frontend work.

Q: A youth group leader asks an assistant for "the whole SACCO system" and receives thousands of lines she cannot read. What is the best next step?
- Upload it live at once because it came from AI
* Restart with a small brief for one feature, review the plan, then read and test each piece
- Ask the assistant to hide the errors
- Delete the AI tool and never use it again
= Small steps and a reviewed plan keep you in control. Unread code is a risk, and the problem was scope, not the tool.

Q: What is Claude Code, according to this stage?
- A web browser
* An AI coding assistant that runs in the terminal inside your project folder and asks permission before acting
- A free web hosting service
- A replacement for learning to code
= It reads files, proposes edits and runs commands with your permission. It supports your learning; it does not replace it.

Q: Which are safe habits when using any AI tool for coding? (choose all that apply)
* Use invented sample data instead of real customer records
- Paste your passwords so it can log in for you
* Read and test generated changes before accepting them
* Check current official documentation for installation steps
= Passwords, keys and personal data must never be pasted. Reading, testing and using current documentation are good practice.

Q: Esther's contact form works nowhere when she double-clicks the HTML file, but should send data to a PHP script. What is the likely fix?
- Make the page colours brighter
- Rename the file to a different extension
* Put the project in the XAMPP web folder, start Apache and open it through localhost
- Install more browser extensions
= Server features such as PHP need a running server. Opening the file directly does not run PHP.

Q: Why are lowercase folder names without spaces recommended?
* They avoid path and capital-letter mismatches on servers
- They make the website load twice as fast
- They are required by every browser
- They stop other people from copying your files
= Some servers treat capital and small letters differently, and spaces cause path errors. It is about reliability, not speed.
QUIZ,
        ],
        // ---------------------------------------------------------------- STAGE 2
        [
            'title'   => 'Stage 2: Frontend Foundations',
            'summary' => 'Build the visible part of a website: structure pages with semantic HTML, style and make them responsive with CSS, and add interactivity with JavaScript, using Claude Code as a reviewed assistant.',
            'lessons' => [
                [
                    'title'   => 'HTML foundations: page structure, text, links, images and tables',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to write a complete HTML page, use elements and attributes correctly, add text, links, lists, images and tables, and ask Claude Code to explain markup you do not understand. This matters because HTML is the skeleton of every website: search engines, screen readers and browsers all depend on it being right.</p>

<h3>What HTML is</h3>
<p><strong>HTML</strong> (HyperText Markup Language) describes the meaning of content; CSS decides how it looks.</p>

<h3>The structure of a document</h3>
<p>An <strong>element</strong> is an opening tag, the content, and a closing tag. An <strong>attribute</strong> adds extra information inside the opening tag, such as <code>lang="en"</code>.</p>
<pre>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
  &lt;meta charset="utf-8"&gt;
  &lt;meta name="viewport" content="width=device-width, initial-scale=1"&gt;
  &lt;title&gt;Green Hills Farmers Group&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
  &lt;h1&gt;Green Hills Farmers Group&lt;/h1&gt;
&lt;/body&gt;
&lt;/html&gt;</pre>
<p>The <code>head</code> holds page information; the <code>body</code> holds what visitors see. Use one <code>h1</code> per page, then <code>h2</code> and <code>h3</code> in order.</p>

<h3>Text, links and lists</h3>
<pre>&lt;h2&gt;Our crops&lt;/h2&gt;
&lt;ul&gt;&lt;li&gt;Maize&lt;/li&gt;&lt;li&gt;Beans&lt;/li&gt;&lt;/ul&gt;
&lt;p&gt;Read our &lt;a href="prices.html"&gt;price list&lt;/a&gt;.&lt;/p&gt;</pre>
<p>Use <code>ul</code> for lists and <code>ol</code> for steps. Link text should make sense alone: write "price list", never "click here".</p>

<h3>Images and multimedia</h3>
<pre>&lt;img src="images/maize.jpg" alt="Farmer holding a basket of dry maize" width="600" height="400"&gt;
&lt;video controls width="600" src="media/tips.mp4"&gt;&lt;/video&gt;</pre>
<p>The <code>alt</code> text describes the picture for people who cannot see it and appears if the file fails to load. Giving <code>width</code> and <code>height</code> stops the page jumping. Compress large photos: visitors pay for data.</p>

<h3>Tables for real data</h3>
<p>Use a table only for data with rows and columns, never for layout.</p>
<pre>&lt;table&gt;
  &lt;caption&gt;Prices per kilogram&lt;/caption&gt;
  &lt;thead&gt;&lt;tr&gt;&lt;th scope="col"&gt;Crop&lt;/th&gt;&lt;th scope="col"&gt;UGX&lt;/th&gt;&lt;/tr&gt;&lt;/thead&gt;
  &lt;tbody&gt;&lt;tr&gt;&lt;td&gt;Maize&lt;/td&gt;&lt;td&gt;1,200&lt;/td&gt;&lt;/tr&gt;&lt;/tbody&gt;
&lt;/table&gt;</pre>

<h3>Using Claude Code to generate and explain HTML</h3>
<p>Claude Code is an AI coding assistant that runs in the terminal inside your project folder. It can read your files, propose edits and ask permission before changing anything. Never paste passwords, keys or personal data into any AI tool.</p>
<pre>Read index.html. Explain each element in plain English for a beginner.
Then list any accessibility problems (missing alt text, skipped heading levels).
Do not change any file yet; propose the fixes first.</pre>
<p>Review the answer: check every claimed problem really exists. AI output can be wrong.</p>

<h3>Debugging HTML with AI and validation</h3>
<p>Run your page through a validator (look for the W3C Markup Validation Service) to find unclosed tags. If you ask Claude Code to fix an error, give it the exact message, then read the change before accepting it.</p>

<h3>Case scenario</h3>
<p>Peter built a clinic page in Jinja with tables for layout and 6 MB phone photos. On rural 3G it took a minute to load, and screen-reader users heard "image, image". An assistant asked to "fix everything" deleted the opening-hours table, and he accepted it unread. He restored his saved copy, resized the photos, wrote alt text and kept one table for hours. The lesson: read every change.</p>

<blockquote><p><strong>Quick check:</strong> Why should an informative image always have alt text?</p><p>Answer: Because screen readers read it aloud and browsers show it when the image fails to load.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Is it good practice to use a table to arrange a page into columns?</p><p>Answer: No, because tables are for data; layout belongs to CSS and tables confuse assistive technology when misused.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a folder <code>farmers</code> with <code>index.html</code> using the structure above.</li>
<li>Add an <code>h2</code>, a list of three crops, one link to a second file and one image with real alt text.</li>
<li>Add a price table with a caption and header cells.</li>
<li>Validate it and fix every error yourself.</li>
<li>Ask an AI assistant to list problems; verify each claim before changing anything.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>HTML gives content meaning; CSS gives it appearance.</li>
<li>Every page needs doctype, language, charset, viewport, title and one h1.</li>
<li>Write useful link text and honest alt text; use tables only for data.</li>
<li>Validate your markup and verify anything an AI assistant suggests.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'HTML forms, semantic structure, navigation and your profile website',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to build an accessible form, structure a page with semantic elements, create a navigation menu that links several pages, and complete a personal profile website with the help of Claude Code. This matters because forms are how visitors contact you, and semantic structure is what makes your site usable for everyone.</p>

<h3>Forms and input fields</h3>
<p>A form collects information. Each field needs a <code>label</code> connected to it, so that tapping the label focuses the field and screen readers announce it.</p>
<pre>&lt;form action="contact.php" method="post"&gt;
  &lt;label for="name"&gt;Your name&lt;/label&gt;
  &lt;input id="name" name="name" type="text" required&gt;

  &lt;label for="phone"&gt;Phone&lt;/label&gt;
  &lt;input id="phone" name="phone" type="tel"&gt;

  &lt;label for="email"&gt;Email&lt;/label&gt;
  &lt;input id="email" name="email" type="email" required&gt;

  &lt;label for="msg"&gt;Message&lt;/label&gt;
  &lt;textarea id="msg" name="msg" rows="4" required&gt;&lt;/textarea&gt;

  &lt;button type="submit"&gt;Send&lt;/button&gt;
&lt;/form&gt;</pre>
<p>The attribute <code>required</code> gives basic browser checking, but it can be bypassed, so the server must always validate again (you will build that later in the course).</p>

<h3>Semantic HTML and accessible structure</h3>
<p><strong>Semantic</strong> elements say what a region is for, instead of using anonymous <code>div</code> boxes everywhere.</p>
<table>
<thead><tr><th>Element</th><th>Use</th></tr></thead>
<tbody>
<tr><td><code>header</code></td><td>Top of the page or a section: logo, title</td></tr>
<tr><td><code>nav</code></td><td>A group of navigation links</td></tr>
<tr><td><code>main</code></td><td>The unique main content, once per page</td></tr>
<tr><td><code>section</code>, <code>article</code></td><td>A themed block; a self-contained item such as a news post</td></tr>
<tr><td><code>footer</code></td><td>Contacts, copyright, small links</td></tr>
</tbody>
</table>

<h3>Navigation menus and linking pages</h3>
<pre>&lt;header&gt;
  &lt;nav aria-label="Main"&gt;
    &lt;ul&gt;
      &lt;li&gt;&lt;a href="index.html" aria-current="page"&gt;Home&lt;/a&gt;&lt;/li&gt;
      &lt;li&gt;&lt;a href="about.html"&gt;About&lt;/a&gt;&lt;/li&gt;
      &lt;li&gt;&lt;a href="contact.html"&gt;Contact&lt;/a&gt;&lt;/li&gt;
    &lt;/ul&gt;
  &lt;/nav&gt;
&lt;/header&gt;</pre>
<p>A menu is a list of links. Paths matter: <code>about.html</code> means the same folder and <code>../index.html</code> means one folder up. Test every link after moving a file.</p>

<h3>Asking Claude Code to generate and debug HTML</h3>
<p>A clear request names the files and rules. Copy this template.</p>
<pre>I am building a profile website for a tailor in Jinja, Uganda.
Create index.html, about.html, portfolio.html and contact.html.
Rules: semantic HTML only, one h1 per page, a shared nav on every page,
labels on all form fields, alt text on every image, no CSS yet.
First list the files you will create and wait for my approval.</pre>
<p>When the files appear, review them like a teacher marking homework: does each page have its own title? Do the links point to files that exist? Is placeholder text left? To debug, give the symptom, such as "the About link gives a 404", and ask for the cause before any edit.</p>

<h3>Case scenario</h3>
<p>Agnes, who runs a youth SACCO in Lira, asked an assistant for a membership form. It looked fine but had no labels and used <code>type="text"</code> for phone and email. Members got the wrong keyboard, and a screen-reader user could not tell the boxes apart. Agnes asked for labels and correct types, then tested on two phones. The lesson: a form that looks right can still fail people, so test on real devices.</p>

<blockquote><p><strong>Quick check:</strong> What does <code>for="email"</code> on a label connect to?</p><p>Answer: The input whose <code>id</code> is "email", so clicking the label focuses that field and assistive tools announce it.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> If Claude Code produces a page, what is the first thing to do before accepting it?</p><p>Answer: Read the code and open the page to test it, because generated code can contain broken links or wrong assumptions.</p></blockquote>

<h3>Assignment: personal profile website</h3>
<p>Build a profile site with a homepage, biography, skills, portfolio and contact page, using shared navigation, semantic structure and one accessible form.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create four files: <code>index.html</code>, <code>about.html</code>, <code>portfolio.html</code>, <code>contact.html</code>.</li>
<li>Put the same <code>nav</code> in each and mark the current page with <code>aria-current</code>.</li>
<li>Write your biography and a skills list using your own honest facts.</li>
<li>Add the contact form with labels, correct input types and a submit button.</li>
<li>Validate all four pages and click every link.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Every form field needs a connected label and the right input type.</li>
<li>Browser checking is a convenience; the server must validate again.</li>
<li>Semantic elements give pages meaning and help accessibility.</li>
<li>Give Claude Code clear rules, ask for a plan first, then review and test.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'CSS foundations: selectors, colour, the box model, Flexbox and Grid',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to write CSS rules, control colour, text and spacing, explain the box model, and lay out a page with Flexbox and Grid. This matters because good styling makes a site look trustworthy, and trust decides whether a customer calls your shop or leaves.</p>

<h3>Introduction to CSS</h3>
<p><strong>CSS</strong> (Cascading Style Sheets) controls how HTML looks. Keep styles in a separate file and link it inside the <code>head</code>.</p>
<pre>&lt;link rel="stylesheet" href="css/style.css"&gt;</pre>

<h3>Selectors, properties and values</h3>
<p>A rule has a <strong>selector</strong> (what to style) and declarations (<strong>property</strong>: <strong>value</strong>).</p>
<pre>p { line-height: 1.6; }
.card { background: #fff; }
#contact { margin-top: 2rem; }
nav a:hover { text-decoration: underline; }</pre>
<p>Element selectors target tags, class selectors (dot) target reusable groups, and an id selector (hash) targets one element. Prefer classes. When rules conflict, the more specific selector wins, and for equal specificity the later one wins; this is the "cascade".</p>

<h3>Colours, typography and backgrounds</h3>
<p>Store design choices once as <strong>custom properties</strong> (CSS variables) and reuse them.</p>
<pre>:root {
  --green: #1b6b3a;
  --ink: #1f2933;
  --paper: #f7f5ef;
  --space: 1rem;
}
body {
  font-family: system-ui, sans-serif;
  color: var(--ink);
  background: var(--paper);
  font-size: 1.125rem;
}</pre>
<p>Check contrast: pale grey text on white is hard to read in bright sunlight. Use <code>rem</code> units for text so users can enlarge it.</p>

<h3>Margin, padding, borders and the box model</h3>
<p>Every element is a box: content, then <strong>padding</strong> (space inside), then <strong>border</strong>, then <strong>margin</strong> (space outside). Without help, width excludes padding and border, which surprises beginners. This reset fixes it.</p>
<pre>*, *::before, *::after { box-sizing: border-box; }
.card {
  padding: var(--space);
  border: 1px solid #d5d0c4;
  border-radius: 8px;
  margin-bottom: calc(var(--space) * 2);
}</pre>

<h3>Styling navigation and buttons</h3>
<pre>nav ul { list-style: none; display: flex; gap: 1rem; padding: 0; }
nav a { text-decoration: none; color: var(--green); }
.button {
  display: inline-block;
  padding: 0.75rem 1.25rem;
  background: var(--green);
  color: #fff;
  border-radius: 6px;
}
.button:focus-visible { outline: 3px solid #f2b705; outline-offset: 2px; }</pre>
<p>Always keep a visible focus style so keyboard users know where they are.</p>

<h3>Flexbox and CSS Grid</h3>
<p><strong>Flexbox</strong> arranges items in one direction (a row or column). <strong>Grid</strong> arranges items in rows and columns together.</p>
<pre>.bar { display: flex; justify-content: space-between; align-items: center; }

.products {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
}</pre>
<p>The grid above automatically fits as many 220px-minimum columns as the screen allows, with no media query needed. Use Flexbox for a menu or a row of buttons, and Grid for a catalogue of cards.</p>

<h3>Worked example with Claude Code</h3>
<p>A tailor wants her portfolio cards in a neat grid. Ask for a plan and a small change, not a redesign.</p>
<pre>Open css/style.css and portfolio.html. Make the .card items a responsive
grid with a 1rem gap and a minimum card width of 220px.
Use only the existing colour variables. Show me the exact diff first.
Do not rename any classes.</pre>
<p>Then check the diff: did it add a new colour you did not choose? Did it touch other rules? Open the page and resize the browser. If something looks wrong, use the developer tools (press F12 in most browsers) to inspect the element and see which rule applies.</p>

<h3>Case scenario</h3>
<p>Mercy styled her shop page by giving every element a fixed width of 960px. On a phone, visitors had to scroll sideways, and orders from phones dropped. She had also copied colours without checking contrast, so prices in light yellow were unreadable. She replaced fixed widths with Grid, used <code>border-box</code> and darkened the text. Orders from phones recovered the next week. The lesson: design for the smallest screen first and test contrast.</p>

<blockquote><p><strong>Quick check:</strong> Which layout tool suits a catalogue of product cards in rows and columns?</p><p>Answer: CSS Grid, because it controls rows and columns together.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why set <code>box-sizing: border-box</code>?</p><p>Answer: So padding and border are counted inside the width, making sizes predictable.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create <code>css/style.css</code>, link it and define four colour variables in <code>:root</code>.</li>
<li>Style your navigation as a row with hover and focus styles.</li>
<li>Build three cards in a Grid that wraps on narrow screens.</li>
<li>Right-click one card, choose Inspect, and note its margin, padding and border.</li>
<li>Ask an AI assistant to suggest one spacing improvement; apply it only if you can explain why it helps.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>CSS rules combine a selector with property-value pairs.</li>
<li>Custom properties keep colours and spacing consistent.</li>
<li>The box model is content, padding, border, margin; use border-box.</li>
<li>Flexbox is one-dimensional, Grid is two-dimensional.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Responsive design, components, animation and your landing page',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to make a layout work on phones, tablets and desktops, build cards, forms and sections, add gentle animation, organise reusable styles, debug visual problems with browser tools and use Claude Code to improve layouts safely. This matters because most visitors in East Africa arrive on a phone, often with limited data.</p>

<h3>Responsive design principles</h3>
<p>A <strong>responsive</strong> site adapts to the screen. Three habits do most of the work: include the viewport meta tag in the HTML, use flexible units (<code>rem</code>, <code>%</code>, <code>fr</code>) instead of fixed pixel widths, and design <strong>mobile-first</strong>, meaning you write the small-screen styles first and add rules for larger screens afterwards.</p>

<h3>Media queries</h3>
<pre>.layout { display: grid; gap: 1rem; }

@media (min-width: 700px) {
  .layout { grid-template-columns: 1fr 1fr; }
}
@media (min-width: 1000px) {
  .layout { grid-template-columns: 2fr 1fr; }
  .wrap { max-width: 70rem; margin-inline: auto; }
}</pre>
<p>Choose breakpoints where your content starts to look stretched, not because of a particular device. Also make images flexible with <code>img { max-width: 100%; height: auto; }</code>.</p>

<h3>Modern cards, forms and sections</h3>
<pre>.card {
  background: #fff;
  border-radius: 10px;
  padding: 1.25rem;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12);
}
input, textarea {
  width: 100%;
  padding: 0.7rem;
  border: 1px solid #8a8f98;
  border-radius: 6px;
  font: inherit;
}
.section { padding-block: clamp(2rem, 6vw, 4rem); }</pre>
<p>The <code>clamp()</code> function sets a minimum, a flexible value and a maximum in one line. Touch targets such as buttons should be at least about 44 pixels tall so thumbs can hit them.</p>

<h3>Transitions and animations</h3>
<pre>.button { transition: background-color 0.2s ease, transform 0.2s ease; }
.button:hover { transform: translateY(-2px); }

@keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
.hero { animation: fade-in 0.6s ease both; }

@media (prefers-reduced-motion: reduce) {
  * { animation: none !important; transition: none !important; }
}</pre>
<p>Keep motion subtle and short. The last rule respects visitors who ask their device to reduce motion.</p>

<h3>Design systems and reusable styles</h3>
<p>A small <strong>design system</strong> is a set of agreed choices: colour variables, a spacing scale, two font sizes for headings, and named components such as <code>.card</code> and <code>.button</code>. Write them once, in one file, and reuse them on every page. Then a change of brand colour means editing one line.</p>

<h3>Using Claude Code to improve layouts</h3>
<pre>The cards on index.html overflow on a 360px-wide phone.
First explain the likely cause by reading css/style.css.
Then propose the smallest fix. Keep the existing variables and class names.
Do not edit HTML. Wait for my approval before changing files.</pre>
<p>Review: does the fix address the cause or just hide it (for example with <code>overflow: hidden</code>)? Test at several widths, and check that other pages did not change.</p>

<h3>Debugging visual problems</h3>
<ol>
<li>Open the page and press F12 (or right-click and choose Inspect).</li>
<li>Select the broken element and read the Styles panel to see which rules apply and which are crossed out.</li>
<li>Use the device toolbar to test a phone width such as 360px.</li>
<li>Change a value live to test your idea, then copy the working value into your CSS file.</li>
</ol>

<h3>Case scenario</h3>
<p>Brian, a young web designer in Kampala, asked an assistant to "make the landing page mobile friendly" for a boda boda insurance agent. It added a dozen media queries and several <code>!important</code> flags, and the desktop layout broke. Brian could not tell which rule did what. He reverted to his saved version, asked instead for a plan, and applied one change at a time, testing each. The page worked on all widths. The lesson: small reviewed steps beat one large unexplained change, and keep a saved copy to return to.</p>

<blockquote><p><strong>Quick check:</strong> What does mobile-first mean?</p><p>Answer: Writing the small-screen styles first and adding media queries for larger screens, which keeps phone pages light and simple.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why include a <code>prefers-reduced-motion</code> rule?</p><p>Answer: Some people feel unwell with moving effects, so the rule turns animation off when their device asks.</p></blockquote>

<h3>Assignment: responsive landing page</h3>
<p>Design a landing page for a business you know, such as a tailor, a clinic or a farmers' group. It must have a hero, services cards, a contact form and a footer, and must work well on phones, tablets and desktops.</p>

<h3>Try it yourself</h3>
<ol>
<li>Sketch the page on paper for a 360px phone first, then for a wide screen.</li>
<li>Write the HTML sections and a shared <code>style.css</code> with variables, cards and a button.</li>
<li>Add two media queries at your own breakpoints and test at 360px, 768px and 1200px.</li>
<li>Add one gentle hover transition and the reduced-motion rule.</li>
<li>Ask an AI assistant for one improvement, review the exact changes, test them and note what you accepted or rejected.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Responsive design needs the viewport tag, flexible units and mobile-first CSS.</li>
<li>Media queries adapt layout where content needs it.</li>
<li>Reusable variables and components form a simple design system.</li>
<li>Inspect with developer tools, change one thing at a time and verify AI fixes.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'JavaScript foundations: variables, logic, functions, data and the DOM',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to store values, make decisions, repeat actions, write functions, organise data in arrays and objects, change a page through the DOM and respond to clicks. This matters because JavaScript is the language that turns a static page into something visitors can interact with, such as a fee calculator or a product filter.</p>

<h3>Introduction to JavaScript</h3>
<p>JavaScript runs in the browser. Link it at the end of the body, or use <code>defer</code> in the head, so the HTML exists before the script runs.</p>
<pre>&lt;script src="js/app.js" defer&gt;&lt;/script&gt;</pre>
<p>Open the browser console (F12, then Console) to see messages from <code>console.log()</code>.</p>

<h3>Variables, data types and operators</h3>
<pre>const shop = "Nakato Hardware";   // text (string)
let stock = 40;                    // number, can change
const open = true;                 // boolean
let price = 12500;
let total = price * 3;             // arithmetic
console.log(`${shop} total: UGX ${total}`);</pre>
<p>Use <code>const</code> by default and <code>let</code> only when the value must change. Comparisons use <code>===</code> (strictly equal) and <code>!==</code>; avoid the looser <code>==</code>. Combine tests with <code>&amp;&amp;</code> (and), <code>||</code> (or) and <code>!</code> (not).</p>

<h3>Conditionals and loops</h3>
<pre>function feeBand(age) {
  if (age &lt; 13) {
    return "Junior";
  } else if (age &lt;= 18) {
    return "Teen";
  }
  return "Adult";
}

for (let week = 1; week &lt;= 4; week++) {
  console.log(`Savings week ${week}`);
}</pre>

<h3>Functions, arrays and objects</h3>
<p>A <strong>function</strong> packages a task so you can reuse it. An <strong>array</strong> holds an ordered list; an <strong>object</strong> groups named properties.</p>
<pre>const members = [
  { name: "Amina", savings: 120000 },
  { name: "Joel", savings: 45000 },
  { name: "Ruth", savings: 80000 },
];

const addUp = (list) =&gt; list.reduce((sum, m) =&gt; sum + m.savings, 0);
const rich = members.filter((m) =&gt; m.savings &gt;= 80000);
const names = members.map((m) =&gt; m.name);

console.log(addUp(members), rich.length, names.join(", "));</pre>
<p>The methods <code>map</code>, <code>filter</code> and <code>reduce</code> each take a function and return a new result; they are the everyday tools for working with lists.</p>

<h3>The DOM and events</h3>
<p>The <strong>DOM</strong> (Document Object Model) is the page as JavaScript sees it: a tree of elements you can read and change.</p>
<pre>&lt;p&gt;Total savings: &lt;strong id="total"&gt;0&lt;/strong&gt;&lt;/p&gt;
&lt;button id="calc" type="button"&gt;Add up&lt;/button&gt;</pre>
<pre>const totalEl = document.querySelector("#total");
const button = document.querySelector("#calc");

button.addEventListener("click", () =&gt; {
  totalEl.textContent = addUp(members).toLocaleString();
});</pre>
<p>Use <code>textContent</code> to insert text. Avoid <code>innerHTML</code> with any text a visitor typed, because it can run malicious code (an attack called XSS, covered later in the course).</p>

<h3>Worked example with Claude Code</h3>
<pre>Read index.html and js/app.js. Write a function that adds up the
numbers typed in the "savings" inputs and shows the total in #total.
Use const/let, no libraries, and no innerHTML.
Explain each line in plain English, then write two example test inputs
with the expected answers so I can check it by hand.</pre>
<p>Check the result by running it with your own test numbers, including awkward ones: an empty box, a negative number, text. Ask yourself what the code does in each case. If you cannot explain a line, ask the assistant to explain it, then check the explanation against the browser's behaviour.</p>

<h3>Case scenario</h3>
<p>Samuel built a fee calculator for a school in Jinja. He tested with 50000 and it worked. After launch, a parent typed "50,000" with a comma and the total showed NaN, which means "not a number", for hundreds of visitors. He had accepted generated code without testing odd input. He added a check that rejects non-numbers with a clear message and tested empty, negative and comma cases. The lesson: test the strange inputs real people will type, not only the happy path.</p>

<blockquote><p><strong>Quick check:</strong> Which keyword should you use for a value that never changes?</p><p>Answer: <code>const</code>, because it prevents accidental reassignment and shows your intention.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What does <code>members.filter(...)</code> return?</p><p>Answer: A new array holding only the items for which the test function returns true; the original array stays unchanged.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create <code>js/app.js</code>, link it with <code>defer</code> and print a message with <code>console.log</code>.</li>
<li>Make an array of four objects about your own shop, class or group.</li>
<li>Write one function that totals a number property and one that uses <code>filter</code>.</li>
<li>Add a button and show the result in the page with <code>textContent</code>.</li>
<li>Test with an empty value and a negative number and note what happens.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use <code>const</code> and <code>let</code>, and strict comparison with <code>===</code>.</li>
<li>Functions, arrays and objects organise logic and data.</li>
<li>The DOM lets scripts read and change the page; events trigger the code.</li>
<li>Always test unusual input, including with AI-written code.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Interactive features: validation, search, APIs, debugging and Claude Code',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to validate a form, build a dropdown menu, add live search, fetch JSON from an API with async and await, debug scripts with browser tools, and use Claude Code to write, explain and test JavaScript. This matters because these features make a site feel like an application, and they are also where most beginner bugs hide.</p>

<h3>Form validation</h3>
<p>Browser validation gives quick feedback but can be bypassed; the server validates again.</p>
<pre>const form = document.querySelector("#join");
const phone = document.querySelector("#phone");
const error = document.querySelector("#phone-error");

form.addEventListener("submit", (event) =&gt; {
  const ok = /^(\+256|0)7\d{8}$/.test(phone.value.trim());
  if (!ok) {
    event.preventDefault();
    error.textContent = "Enter a valid phone number, for example 0772123456.";
    phone.setAttribute("aria-invalid", "true");
    phone.focus();
  }
});</pre>
<p>Keep the message next to the field and say how to fix it.</p>

<h3>Interactive menus and dropdowns</h3>
<pre>const toggle = document.querySelector("#menu-toggle");
const menu = document.querySelector("#menu");

toggle.addEventListener("click", () =&gt; {
  const open = menu.hidden;
  menu.hidden = !open;
  toggle.setAttribute("aria-expanded", String(open));
});</pre>
<p>Use a real <code>button</code> so the keyboard works, and update <code>aria-expanded</code> for screen readers.</p>

<h3>Search and filtering</h3>
<pre>const box = document.querySelector("#search");
const items = document.querySelectorAll(".product");

box.addEventListener("input", () =&gt; {
  const term = box.value.trim().toLowerCase();
  items.forEach((item) =&gt; {
    item.hidden = !item.textContent.toLowerCase().includes(term);
  });
});</pre>

<h3>Asynchronous JavaScript, APIs and JSON</h3>
<p>Some tasks take time, such as loading data from a server. <strong>Asynchronous</strong> code lets the page stay usable while it waits. An <strong>API</strong> is a service that returns data, often as <strong>JSON</strong>, a text format of objects and arrays.</p>
<pre>async function loadPrices() {
  const list = document.querySelector("#prices");
  try {
    const response = await fetch("data/prices.json");
    if (!response.ok) {
      throw new Error(`Server answered ${response.status}`);
    }
    const prices = await response.json();
    list.replaceChildren(...prices.map((p) =&gt; {
      const li = document.createElement("li");
      li.textContent = `${p.crop}: UGX ${p.price}`;
      return li;
    }));
  } catch (err) {
    list.textContent = "Prices could not be loaded. Please try again later.";
    console.error(err);
  }
}
loadPrices();</pre>
<p>Notice the three habits: check <code>response.ok</code>, handle failure with <code>try</code> and <code>catch</code>, and show a friendly message. Mobile networks fail often. Opening a page straight from a file may block <code>fetch</code>; use a local server such as XAMPP.</p>

<h3>Debugging JavaScript</h3>
<ol>
<li>Read the red Console message: it names the file and line.</li>
<li>Print values with <code>console.log()</code>.</li>
<li>Open the Sources panel, click a line number to set a breakpoint, and step through the code.</li>
<li>Check the Network panel for failed requests.</li>
</ol>

<h3>Using Claude Code to write, explain and test JavaScript</h3>
<pre>Read js/app.js. The search box should hide products that do not match,
but typing a capital letter finds nothing. Explain the cause first.
Then propose a fix and show me the diff. Do not add any library.
Afterwards list five test inputs I should try by hand, including
an empty box and a very long word.</pre>
<p>When the change arrives, do not trust it: run your five tests, then open the console to check for errors. Never paste secrets or real customer data into an AI tool.</p>

<h3>Case scenario</h3>
<p>Dorothy, who coordinates a farmers' group in Masaka, added a price list loaded with <code>fetch</code>. In villages with weak signal the list stayed blank because there was no error handling, and members thought the site was broken. A first generated fix just returned an empty list. After reviewing, Dorothy asked for a visible message and a retry button, and tested with offline mode in the Network panel. The lesson: simulate failure before your users meet it.</p>

<blockquote><p><strong>Quick check:</strong> Why must a server still validate a form that JavaScript already validated?</p><p>Answer: Because browser code can be bypassed or switched off, so only server checks can be trusted.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What two things should you do with the result of <code>fetch</code> before using the data?</p><p>Answer: Check <code>response.ok</code> and wrap the work in <code>try</code> and <code>catch</code>, because requests can fail.</p></blockquote>

<h3>Assignment: interactive website</h3>
<p>Extend your site with form validation, a navigation menu and search.</p>

<h3>Try it yourself</h3>
<ol>
<li>Add validation to your contact form with one clear message per field.</li>
<li>Add a menu button that opens and closes your navigation and updates <code>aria-expanded</code>.</li>
<li>Add a search box that filters a list of at least six items.</li>
<li>Create <code>data/prices.json</code>, load it with <code>fetch</code> and test it with the network turned off.</li>
<li>Ask an AI assistant to review your script, then test each suggestion before keeping it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Client-side validation helps users; the server must validate again.</li>
<li>Use real buttons and ARIA state for accessible menus.</li>
<li>With <code>fetch</code>, check <code>response.ok</code>, catch errors and show friendly messages.</li>
<li>Debug with Console, breakpoints and the Network panel; verify every AI change.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which HTML element should hold the unique main content of a page?
- header
- section
* main
- nav
= The main element marks the single main content area of the page, which helps assistive technology skip repeated menus.

Q: A learner's contact form has no labels, so a screen reader user cannot tell which box is which. What is the best fix?
- Add placeholder text only
* Add a label connected to each input with matching for and id
- Make the input boxes larger
- Remove the form and show an email address
= A label linked by for and id is announced by screen readers and enlarges the tap target. Placeholder text disappears while typing.

Q: In the CSS box model, which order lists the layers from the content outward?
- Content, margin, border, padding
- Border, content, padding, margin
* Content, padding, border, margin
- Padding, content, margin, border
= Content sits in the middle, padding surrounds it, then the border, and margin is the space outside.

Q: A shop owner's page has fixed 960px widths and visitors on phones must scroll sideways. What is the best approach?
- Tell visitors to rotate their phones
- Add overflow: hidden to the body
- Shrink the font size on the whole page
* Use flexible layout (Grid or Flexbox with relative units), the viewport meta tag and media queries, written mobile-first
= Responsive layout fixes the cause. Hiding overflow only hides content, and a smaller font does not remove the fixed width.

Q: Claude Code proposes a change that adds many media queries and several !important flags to "fix" a mobile layout. What should you do?
- Accept it because it came from an AI assistant
* Ask for a plan, read the diff, apply small changes one at a time and test at several widths
- Delete your CSS file and start again
- Accept it and test only on desktop
= AI output is a proposal. Reviewing the diff and testing small changes lets you understand what each rule does and revert safely.

Q: Which are good practices for fetching JSON in JavaScript? (choose all that apply)
* Check response.ok before reading the data
* Use try and catch and show a friendly message on failure
- Assume the request always succeeds because it worked in the office
* Test with the network switched off
= Mobile connections fail often. Checking the response, catching errors and simulating failure prepare the page for real users.

Q: A fee calculator shows NaN when a parent types "50,000". What lesson does this teach?
- JavaScript cannot handle numbers
- The parent should not use commas
* Test unusual input such as commas, empty boxes and negatives, and validate it with clear messages
- Switch to a different browser
= Real users type unpredictable values. Testing the edge cases and giving clear errors prevents silent failures.

Q: Why must a server validate form data even when JavaScript validated it in the browser?
- Browsers cannot run JavaScript
- JavaScript validation is always slower
- Servers are faster at checking phone numbers
* Browser checks can be bypassed or disabled, so only server-side checks can be trusted
= Anyone can switch off or edit client-side code, so the server is the final guard of your data.
QUIZ,
        ],
        // ---------------------------------------------------------------- STAGE 3
        [
            'title'   => 'Stage 3: Building with Claude Code and Git',
            'summary' => 'Learn to direct Claude Code with clear prompts, a plan-first workflow and careful review, and to protect every step with Git checkpoints, branches, GitHub and safe handling of secrets.',
            'lessons' => [
                [
                    'title'   => 'Working with Claude Code: context, prompts and requirements',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to explain how Claude Code works inside a project, start it in the right folder, and write prompts with a goal, context, constraints and acceptance checks. This matters because the quality of what an AI assistant builds depends heavily on how clearly you describe the job, and a vague request wastes your time and your learners' or clients' money.</p>

<h3>How Claude Code operates inside a project</h3>
<p><strong>Claude Code</strong> is Anthropic's AI coding assistant that runs in the terminal, inside your project folder. It can read project files, propose edits and run commands, and it asks your permission before doing so. Think of it as a fast junior colleague sitting beside you: quick, tireless, often right, but sometimes wrong with total confidence. You remain the developer. Installation methods, accounts and plans change often, so follow the current official documentation. Without access, another AI assistant or doing the steps by hand works for every exercise.</p>

<h3>Starting it in the right place</h3>
<p>Open the terminal, move into your project folder, then start the tool by typing <code>claude</code>.</p>
<pre>cd C:\xampp\htdocs\school-site
claude</pre>
<p>The folder you start in is the tool's workspace. Start inside the project, never in a folder such as your whole Documents or Desktop, because it may read everything it can reach. Never paste passwords, API keys, personal data or confidential client data into any AI tool.</p>

<h3>Project context and file access</h3>
<p>Claude Code knows only what it can read in the folder and what you tell it. If your pages use a folder called <code>css</code>, it can open those files, but it cannot know your client's opening hours unless you say so. Give context in words: who the site is for, which files matter, which style you already use.</p>

<h3>The anatomy of a good prompt</h3>
<p>Use four parts: <strong>goal</strong> (what you want), <strong>context</strong> (what exists and who it is for), <strong>constraints</strong> (what must not change) and <strong>acceptance checks</strong> (how you will know it works).</p>
<pre>Goal: Add a contact section to index.html for Mwangaza Clinic.
Context: The site is plain HTML and CSS. Opening hours are
Mon-Sat 8am-5pm. Phone: 0700 000 000 (sample number).
Constraints: Do not change style.css colours. No JavaScript.
Do not touch other pages.
Acceptance: The section shows on a phone-width screen without
sideways scrolling, and the phone number is a tap-to-call link.</pre>

<table>
<thead><tr><th>Weak prompt</th><th>Strong prompt</th></tr></thead>
<tbody>
<tr><td>Make my site nice</td><td>Improve spacing and font sizes on the homepage only; keep the green and white colours</td></tr>
</tbody>
</table>

<h3>Giving clear project requirements</h3>
<p>For a whole site, write a short brief first and keep it as a file in the project. Template:</p>
<pre>Project: (name and one-line purpose)
Audience: (who visits, on what devices)
Pages: (list, with the main content of each)
Style: (colours, fonts, tone)
Must have / must not: (contact form; no external libraries)
Done when: (checks you will do)</pre>

<h3>Generating layouts and components</h3>
<p>Ask for one piece at a time: a header with navigation, then a services grid, then a footer. Tell it to reuse your existing CSS.</p>

<h3>Worked example: a tailor's price page</h3>
<p>Mama Rehema, a tailor in Jinja, needs a page listing five services with prices in UGX. Her prompt names the file, lists the services and prices, points to the existing stylesheet and asks for a table readable on a phone. Every prompt part is present, so the result needs only a small tweak.</p>

<h3>Case scenario</h3>
<p>A student in Mbarara typed "build a website for my shop" and approved everything. The tool created eight files, replaced her homepage and added bright purple styling she never asked for. She had no backup and lost her original text. She rebuilt from a friend's copy, wrote a brief with constraints and asked for one page at a time. The lesson: vague prompts invite large guesses, so save a checkpoint before any big request.</p>

<blockquote><p><strong>Quick check:</strong> Which four parts make a strong coding prompt?</p><p>Answer: Goal, context, constraints and acceptance checks, because together they tell the assistant what to build, what exists, what to leave alone and how success is judged.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why start Claude Code inside the project folder rather than a big parent folder?</p><p>Answer: The starting folder is its workspace, so a narrow folder limits what it can read and change.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a folder for a small business you know and add a simple index.html.</li>
<li>Write a project brief using the template above and save it as brief.txt in that folder.</li>
<li>Write one prompt with goal, context, constraints and acceptance checks for a single new section.</li>
<li>Run it in your assistant (or another AI assistant) and compare the result with your acceptance checks.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Claude Code works in your project folder, reads files, proposes edits and runs commands only with your permission.</li>
<li>Start it in the narrowest sensible folder and never share secrets with it.</li>
<li>Strong prompts state goal, context, constraints and acceptance checks.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Plan first, review every change, and understand the code',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to use the plan-first workflow, read proposed changes before approving them, ask for explanations, fix bugs with AI help and request a safe refactor. This matters because AI output is a draft from a fast but fallible assistant, and the person who clicks approve owns the result.</p>

<h3>Creating and modifying project files</h3>
<p>Claude Code can create new files and edit existing ones. It usually shows each proposed change and waits for your approval. Ask yourself three things: which file is it touching, is that the file I expected, and is the change the size I expected?</p>

<h3>The plan-first workflow</h3>
<p>Before any code is written, ask for a plan. A plan is cheap to correct; wrong code spread across ten files is not.</p>
<pre>Do not write or change any files yet. Read index.html and
style.css, then propose a plan to add a fee table for
St. Agnes Primary School. List the files you would change,
the steps in order, and any risks. Wait for my approval.</pre>
<p>Read the plan. Remove steps you did not want ("no new folders"), reorder them, then say "go ahead with steps 1 and 2 only". The tool may offer a planning mode; check the current documentation.</p>

<h3>Reviewing proposed changes before accepting</h3>
<p>Use this checklist on every proposal:</p>
<ul>
<li>Does it touch only the files I named?</li>
<li>Are lines being deleted that I did not expect?</li>
<li>Does it add a library, a link to an outside website or a command I do not understand?</li>
<li>Does it contain keys, passwords or real phone numbers that should not be there?</li>
<li>Can I explain, in my own words, what each block does?</li>
</ul>
<p>If the answer to the last question is no, ask for an explanation or reject the change.</p>

<h3>Asking Claude Code to explain generated code</h3>
<pre>Explain the JavaScript you just added to contact.html as if
I am a beginner. What does each function do, and what would
break if I deleted the last four lines?</pre>
<p>If you cannot explain your own page, you cannot maintain it.</p>

<h3>Finding and fixing bugs with AI</h3>
<p>Describe the symptom, the expected result and what you already tried. Include the exact error message from the browser console, but remove any private data first.</p>
<pre>Bug: On menu.html the "Order" button does nothing.
Expected: It should open the order form section.
Seen: Browser console says "orderForm is null".
Task: Find the cause, explain it, and propose the smallest fix.
Do not rewrite the page.</pre>
<p>"Smallest fix" is the key phrase. Without it, assistants sometimes rewrite whole files, and new bugs hide inside the rewrite.</p>

<h3>Refactoring for readability and maintainability</h3>
<p>Refactoring means improving how code is organised without changing what the page does: clearer names, no repeated blocks, shared styles. Ask for it separately from new features, and always add the check "the page must look and behave exactly the same".</p>

<h3>Worked example: a youth SACCO's loan page</h3>
<p>A youth SACCO in Mukono wants a loan calculator section. The developer asks for a plan, spots that step 3 would replace the whole stylesheet, edits the plan to "add new rules at the end of style.css only", then approves one step at a time. After each step she opens the page in the browser. It takes longer than blind approval, yet the page works at the first demonstration.</p>

<h3>Case scenario</h3>
<p>A developer in Gulu asked an assistant to "tidy the CSS". He pressed approve repeatedly. The tool merged two files and removed rules it judged unused, but those rules styled the mobile menu. The site looked fine on his laptop and was unusable on phones, which is how most customers visited. He found it only after complaints. He rolled back to his last commit and redid the task with a plan and a mobile check. The lesson: approve nothing unread, and test on a phone-sized screen.</p>

<blockquote><p><strong>Quick check:</strong> Why ask for a plan before code is written?</p><p>Answer: A plan lets you correct the approach cheaply, whereas wrong code across many files is slow to untangle.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What phrase in a bug prompt helps stop the assistant rewriting a whole file?</p><p>Answer: "Propose the smallest fix", because it limits the size of the change you must review.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick a small page and ask for a plan only, with the words "do not change any files yet".</li>
<li>Edit the plan: delete one step and narrow another.</li>
<li>Approve one step, then read the proposed change line by line using the checklist.</li>
<li>Ask for an explanation of one block you do not understand and write it down in your own words.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Ask for a plan first, edit it, then approve steps one at a time.</li>
<li>Review every proposed change: files touched, lines deleted, new libraries, secrets.</li>
<li>Ask for explanations until you can describe the code yourself.</li>
<li>Request the smallest fix for bugs and keep refactors separate from features.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Safe practice: testing, context, recovery and permissions',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to build responsive pages with AI help, work in an existing codebase, test what was produced, manage long conversations, recover from unwanted changes with Git and approve permissions safely. This matters because the real skill with Claude Code is not generating code, it is staying in control of it.</p>

<h3>Implementing responsive designs</h3>
<p>Most users in East Africa browse on phones, so say so in the prompt: "Design for a 360 pixel wide screen first, then widen for tablets and desktops. No horizontal scrolling." Then check it yourself by narrowing the browser window or using the browser's device view.</p>

<h3>Working with existing codebases</h3>
<p>Before changing someone else's project, ask for orientation, not edits.</p>
<pre>Read this project and describe its structure in plain English:
which files make the pages, where styles live, and where the
contact form is handled. Do not change anything.</pre>
<p>Then ask for changes that match the existing style and naming.</p>

<h3>Human review and testing</h3>
<p>Never assume that code works because it looks neat. Run it: open the page, click every button, submit the form with good and bad input, try a phone-size window, and read the console for errors. Use your acceptance checks as a test list. If the assistant ran its own checks, still repeat the important ones.</p>

<h3>Managing context and small tasks</h3>
<p>A long conversation can become confused: the assistant may forget early rules or repeat old mistakes. Keep tasks small, one clear outcome each. When replies start contradicting earlier decisions, start fresh with a short summary. A file such as <code>CLAUDE.md</code> in the project can hold standing rules that the tool reads at the start of a session, so you do not repeat them; look in the current documentation for how it is used.</p>
<pre>Project rules (CLAUDE.md)
- Plain HTML, CSS and small JavaScript only; no frameworks.
- Mobile first. Reuse classes in style.css.
- Never edit .env or anything in the private folder.
- Ask before installing anything or deleting files.
- After each change, tell me what to test in the browser.</pre>

<h3>Using Git to review and recover</h3>
<p>Commit before each AI task so you can see and undo what changed.</p>
<ol>
<li><code>git status</code> lists changed files.</li>
<li><code>git diff</code> shows exactly which lines changed.</li>
<li><code>git restore style.css</code> throws away uncommitted edits in that file, and this cannot be undone, so read the diff first.</li>
<li><code>git revert HEAD</code> adds a new commit that cancels the latest commit while keeping history.</li>
</ol>

<h3>Permissions and tool approvals</h3>
<ul>
<li>Approve narrowly: allow one action once, rather than a blanket permission for everything.</li>
<li>Be extra careful with commands that delete files or install software.</li>
<li>Never run a command you do not understand; ask what it does and why.</li>
<li>Be wary of commands that download and run scripts, or that touch folders outside the project.</li>
</ul>

<h3>The owner's workflow for a business website</h3>
<p>Instruct Claude Code in eight steps: analyse the requirements, propose the structure, create pages and components, implement the visual design, add functionality, test and identify errors, fix issues and review changes, prepare for deployment. The emphasis throughout is understanding and verifying the work, not accepting every generated change automatically.</p>

<h3>Case scenario</h3>
<p>A volunteer building a farmers' group site in Masindi approved a command without reading it. It deleted a folder of member photos it considered duplicates. The folder had no Git history and no copy elsewhere. After that she turned on careful approvals, committed before every task, and kept photos backed up on a flash disk. The lesson: deleting commands deserve the closest reading, and files outside Git need their own backup.</p>

<blockquote><p><strong>Quick check:</strong> What does <code>git revert</code> do?</p><p>Answer: It creates a new commit that cancels an earlier commit, so history stays intact.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A command you do not recognise is proposed. What should you do?</p><p>Answer: Decline it and ask for an explanation, because running unknown commands blindly can delete files or install harmful software.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a CLAUDE.md with five rules for your own project.</li>
<li>Commit your project, ask for one small change, then run git status and git diff.</li>
<li>Test the result at phone width and list two things to check.</li>
<li>Undo the change with git restore (after reading the diff), or keep it and commit.</li>
<li>Assignment: write a brief for a business website and build it with Claude Code, following the eight steps above.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Design mobile first and test at phone width yourself.</li>
<li>Ask for orientation before editing an unfamiliar codebase.</li>
<li>Keep tasks small, restart with a summary when the chat gets confused, and store rules in CLAUDE.md.</li>
<li>Commit before AI changes, review with git diff, undo with restore or revert.</li>
<li>Approve narrowly and never run unknown commands.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Git and GitHub basics: commits, branches and merging',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to explain version control, create and configure a repository, make well-described commits, track changes, work on branches, merge them and connect a project to GitHub. This matters because Git is your safety net: when an AI tool or a tired human breaks something, you can see what changed and go back.</p>

<h3>Version control, Git and GitHub</h3>
<p><strong>Version control</strong> records the history of your files so you can compare versions and return to an older one. <strong>Git</strong> is the program on your computer that does this. <strong>GitHub</strong> is a website that stores a copy of a Git project online for backup and teamwork. They are different things: you can use Git without GitHub.</p>

<h3>Creating and configuring a repository</h3>
<p>A <strong>repository</strong> (repo) is a project folder that Git tracks. Tell Git who you are once, then start tracking.</p>
<pre>git config --global user.name "Your Name"
git config --global user.email "you@example.com"
cd school-site
git init</pre>

<h3>Tracking changes and committing</h3>
<p>A <strong>commit</strong> is a saved snapshot with a message. First stage the files you want, then commit.</p>
<pre>git status
git add index.html style.css
git commit -m "Add homepage and base styles"
git diff
git log --oneline</pre>
<p><code>git status</code> shows what changed, <code>git diff</code> shows the exact lines changed but not yet staged, and <code>git log --oneline</code> lists past commits. Commit message template:</p>
<pre>Short summary in the present tense (under 60 characters)

Optional detail: what changed and why.</pre>
<p>Good: "Add fee table to admissions page". Poor: "stuff" or "final final 2".</p>

<h3>Branches</h3>
<p>A <strong>branch</strong> is a separate line of work. The main branch holds the version you trust; you try new things on another branch, so a mistake never spoils main.</p>
<pre>git branch
git switch -c add-contact-form
git add contact.html
git commit -m "Add contact form"
git switch main</pre>
<p>Older guides use <code>git checkout</code> for switching branches; it still works, but <code>git switch</code> is clearer. Your first branch might be called main or master depending on your setup.</p>

<h3>Merging</h3>
<p>When the work on a branch is tested and good, bring it into main.</p>
<pre>git switch main
git merge add-contact-form</pre>

<h3>Connecting a local project to GitHub</h3>
<p>Create an empty repository on GitHub through the website, then connect it. Copy the address GitHub shows you.</p>
<pre>git remote add origin https://github.com/YOUR-USERNAME/school-site.git
git branch -M main
git push -u origin main</pre>
<p>The word <code>origin</code> is the usual nickname for the online copy. Sign-in methods change, so follow GitHub's current instructions.</p>

<h3>Worked example: a clinic's website</h3>
<p>Mwangaza Clinic in Soroti has a working site. The developer runs git init, adds the files, and commits "Add clinic homepage". She then creates a branch called add-opening-hours, edits one page, checks it in the browser, commits and merges. Two weeks later the owner asks, "What did the page look like in July?" Because every step is a commit, she can find it with git log in a minute.</p>

<h3>Case scenario</h3>
<p>A student in Kabale worked straight on one folder, copying it as "site-old", "site-new" and "site-final-REAL". When a form stopped working he could not remember which copy had changed. After learning Git he committed small changes with clear messages. The next time a feature broke, git log and git diff showed him the exact line within minutes. The lesson: history replaces folder copies, and small commits make problems easy to find.</p>

<blockquote><p><strong>Quick check:</strong> What is the difference between Git and GitHub?</p><p>Answer: Git is the version-control tool on your computer, while GitHub is an online service that stores copies of Git projects, so Git works without GitHub.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why work on a branch instead of main?</p><p>Answer: A branch isolates unfinished work, so a mistake cannot spoil the version you trust.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Make a folder with one HTML file and run git init.</li>
<li>Run git add and git commit with a clear message, then run git log --oneline.</li>
<li>Create a branch, change the file, commit, and view the change with git diff before committing.</li>
<li>Switch to main and merge the branch.</li>
<li>Create an empty repository on GitHub and push your project with the three remote commands above.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Git records history locally; GitHub stores a copy online.</li>
<li>Stage with git add, save with git commit, and write clear messages.</li>
<li>Use git status, git diff and git log to see what is happening.</li>
<li>Do new work on branches and merge when it is tested.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Collaborating safely: push, pull, conflicts, secrets and checkpoints',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to push, pull and clone, read project history, resolve a basic merge conflict, collaborate on GitHub, write a README, keep secrets out of your repository and use Git checkpoints with Claude Code. This matters because one leaked key or one overwritten teammate's work can cost real money and trust.</p>

<h3>Push, pull and clone</h3>
<pre>git push
git pull
git clone https://github.com/YOUR-USERNAME/school-site.git</pre>
<p><code>git push</code> sends your commits to GitHub. <code>git pull</code> brings down commits made by others (or from another computer). <code>git clone</code> copies a whole project from GitHub to a new folder. Pull before you start work each day.</p>

<h3>Project history</h3>
<pre>git log --oneline
git diff abc1234 def5678</pre>
<p>Those short codes are commit identifiers from git log (yours will differ). GitHub shows the same history in the browser.</p>

<h3>Resolving a basic merge conflict</h3>
<p>A conflict happens when two branches change the same lines. Git cannot guess which is right, so it stops and marks the file:</p>
<pre>&lt;&lt;&lt;&lt;&lt;&lt;&lt; HEAD
&lt;p&gt;Open Monday to Saturday, 8am to 5pm&lt;/p&gt;
=======
&lt;p&gt;Open Monday to Friday, 8am to 6pm&lt;/p&gt;
&gt;&gt;&gt;&gt;&gt;&gt;&gt; update-hours</pre>
<p>The part above the equals line is your current branch; below is the incoming branch. To resolve it:</p>
<ol>
<li>Run <code>git status</code> to see which files conflict.</li>
<li>Open the file and decide the correct text, perhaps after asking the teammate.</li>
<li>Delete all three marker lines and keep only the correct text.</li>
<li>Save, run <code>git add</code> on the file, then <code>git commit</code>.</li>
<li>Open the page and test it.</li>
</ol>
<p>If you ask an AI assistant to resolve a conflict, read its result as carefully as any other change.</p>

<h3>Collaborating on GitHub</h3>
<p>Teams commonly work on a branch, push it, and open a <strong>pull request</strong>: a page where teammates read the changes, comment and approve before merging. Never push straight over a teammate's work, and review AI-written changes in a pull request like human ones.</p>

<h3>README files and documentation</h3>
<p>A README tells newcomers what the project is and how to run it.</p>
<pre>Project name: one line on what it is for
How to run: steps to open it locally
Folders: what each main folder holds
Settings: which settings the owner must create (never real values)
Status: what works, what is planned</pre>

<h3>Environment variables and secrets</h3>
<p>Passwords, API keys and database logins are <strong>secrets</strong>. Keep them in a file such as <code>.env</code> that is never committed, and list it in <code>.gitignore</code>.</p>
<pre>.env
node_modules/
uploads/private/</pre>
<p>Commit a safe example file such as <code>.env.example</code> with fake values so others know what to create. If a secret was committed, deleting it in a later commit is not enough because the history still holds it. Rotate it: make the service issue a new key, switch to it, and disable the old one.</p>

<h3>Git checkpoints with Claude Code</h3>
<p>Commit before each task. After the task, review with git diff, test, then commit again with a message that says what was done. If the result is bad, <code>git restore</code> removes uncommitted edits, or <code>git revert</code> cancels a committed change. Commit messages as checkpoints: "Before: add booking form" and "Add booking form, tested on phone".</p>

<h3>Case scenario</h3>
<p>A developer in Kampala built a delivery site for a small shop and pushed it to a public repository. His .env file held a payment key and was not in .gitignore. Two days later the account showed odd activity. He had to rotate the key and apologise to the owner. Afterwards he created .gitignore at the start of every project. The lesson: ignore secrets first, commit second, and rotate immediately after any leak.</p>

<blockquote><p><strong>Quick check:</strong> A secret was pushed to GitHub. Is deleting the file in a new commit enough?</p><p>Answer: No, because old commits still contain it; you must rotate the secret so the leaked value stops working.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What do the lines starting with seven less-than signs mean in a conflicted file?</p><p>Answer: They mark where your branch's version begins, so you must choose the right text and remove all markers.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Add a .gitignore containing .env, create a fake .env file, and confirm git status does not list it.</li>
<li>Write a README using the template above and commit it.</li>
<li>Create two branches that edit the same line, merge them, and resolve the conflict.</li>
<li>Clone your GitHub repository into a new folder to prove the backup works.</li>
<li>Assignment: upload a website to GitHub, document it with a README and keep a history of at least five clear commits.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Pull before you work, push when your changes are committed and tested.</li>
<li>Conflicts need a human decision: keep the correct text, remove markers, test.</li>
<li>Never commit .env; list it in .gitignore and rotate any secret that leaks.</li>
<li>Commit before and after every AI task so each step can be reviewed or undone.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which set of parts makes a strong prompt for Claude Code?
- Greeting, apology, deadline and thanks
* Goal, context, constraints and acceptance checks
- Language, colour, font and file size
- Password, project name, folder and date
= A strong prompt says what you want, what exists, what must not change and how you will judge success.

Q: A school asks you to add a fee table. Before any file is changed, what is the best first request?
* Ask for a plan that lists files, steps and risks, and wait for approval
- Ask it to build the whole site and approve everything
- Ask it to delete the old homepage first
- Ask it to install every popular library
= Plans are cheap to correct, while wrong code across many files is slow to repair.

Q: Claude Code proposes a command you do not recognise that deletes a folder. What do you do?
- Approve it, because the tool must know best
- Approve it only if the folder looks old
* Decline, ask what it does and why, and check that the folder is backed up
- Approve and fix problems later
= Unknown or deleting commands deserve the closest reading, and you remain responsible for what runs.

Q: After an AI task, your clinic page looks wrong on phones. You committed before the task. Which approach is best?
- Delete the project and start again
- Ask the tool for a bigger rewrite without looking
* Use git diff to see what changed, then restore or revert, and redo the task with a mobile check
- Leave it, because desktop looks fine
= Commits before AI work let you see exactly what changed and return to a good state.

Q: Which command shows the exact lines you have changed since the last commit?
- git log
- git init
* git diff
- git clone
= git diff compares your files with the last commit; git log lists the commits themselves.

Q: A developer pushed a payment key to a public GitHub repository, then deleted the file in a new commit. What is the right response?
- Nothing, because the file is gone
- Rename the repository
* Rotate the key so the old one stops working, and add .env to .gitignore
- Ask friends not to look
= History still contains the key, so only issuing a new key and disabling the old one removes the danger.

Q: Two teammates changed the same line and a merge shows markers like seven less-than signs, equals signs and seven greater-than signs. What should you do?
- Commit the file with the markers left in
* Choose the correct text, delete all marker lines, save, add, commit and test
- Delete the whole repository
- Push again until it works
= Git cannot decide for you; a person must pick the right text and remove the markers.

Q: Which statement about AI output is the most sensible?
- It is always correct if it runs
* It is a draft from a fast but fallible assistant that must be reviewed and tested
- It never needs explanation
- It replaces the need to learn
= The developer owns the result, so reading, testing and understanding the code is part of the job.
QUIZ,
        ],
        // ---------------------------------------------------------------- STAGE 4
        [
            'title'   => 'Stage 4: Backend and Data',
            'summary' => 'Build the server side of a dynamic website with PHP and store its data safely in MySQL, finishing with a student registration system.',
            'lessons' => [
                [
                    'title'   => 'PHP basics: forms, GET and POST, sessions',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to explain how a browser and a PHP server talk to each other, write simple PHP, and handle a submitted form with GET, POST and a session. This matters because every login, booking and registration on the web depends on code that runs on the server, where visitors cannot change it.</p>

<h3>Frontend and backend communication</h3>
<p>The <strong>frontend</strong> is what runs in the browser (HTML, CSS, JavaScript). The <strong>backend</strong> runs on the server. The browser sends an HTTP <em>request</em>, PHP runs, and the server sends back a <em>response</em>, usually HTML. The visitor never sees your PHP code, only its output. Never trust anything arriving from the browser, because anyone can send any data to your server.</p>

<h3>PHP essentials</h3>
<p><strong>PHP</strong> is a server-side language. Files end in <code>.php</code> and live in <code>C:\xampp\htdocs</code>, then you open them through <code>http://localhost</code>, not by double-clicking.</p>
<pre>&lt;?php
declare(strict_types=1);

$school = 'Gulu Hill Secondary';      // string
$students = 42;                       // integer
$fee = 150000.50;                     // float
$open = true;                         // boolean
$subjects = ['Maths', 'Biology'];     // array

if ($students &gt; 40) {
    echo 'Large class';
} elseif ($students &gt; 20) {
    echo 'Medium class';
} else {
    echo 'Small class';
}

foreach ($subjects as $subject) {
    echo htmlspecialchars($subject) . '&lt;br&gt;';
}

function formatFee(float $amount): string
{
    return 'UGX ' . number_format($amount);
}
echo formatFee($fee);</pre>
<p>A <strong>function</strong> packs a task so you reuse it. Put such functions in a file such as <code>functions.php</code> and load it with <code>require</code>.</p>

<h3>Processing a form: GET and POST</h3>
<p><strong>GET</strong> puts data in the address (<code>?q=maths</code>) and suits searching and filtering, because the page can be bookmarked. <strong>POST</strong> sends data in the request body and suits anything that changes data or carries private details. Read them from <code>$_GET</code> and <code>$_POST</code>.</p>
<pre>&lt;?php
$name = trim($_POST['name'] ?? '');
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if ($errors === []) {
        echo 'Thank you, ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    }
}
?&gt;
&lt;form method="post"&gt;
  &lt;input name="name" value="&lt;?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?&gt;"&gt;
  &lt;button&gt;Send&lt;/button&gt;
&lt;/form&gt;</pre>
<p>The <code>??</code> operator gives a default when a key is missing. <code>htmlspecialchars</code> turns characters like &lt; into harmless text, which blocks cross-site scripting (XSS).</p>

<h3>Sessions and cookies</h3>
<p>HTTP forgets you between requests. A <strong>cookie</strong> is a small value the browser stores and sends back. A <strong>session</strong> keeps the real data on the server and gives the browser only a random ID cookie. Call <code>session_start()</code> before any output.</p>
<pre>&lt;?php
session_start();
$_SESSION['visits'] = ($_SESSION['visits'] ?? 0) + 1;
echo 'Visit number ' . $_SESSION['visits'];</pre>

<h3>Case scenario</h3>
<p>A clinic in Mbale built an appointment page that printed the patient's typed name straight back with <code>echo $_GET['name']</code>. A prankster sent staff a link whose name contained a script, and the script ran in the receptionist's browser. Nothing was stolen, but the clinic took the page offline for a day. The decision was to escape every output with <code>htmlspecialchars</code> and switch the form to POST. The lesson: treat all input as hostile and escape on output.</p>

<h3>Template: asking Claude Code for a form</h3>
<pre>Create contact.php in this project (PHP 8, no framework).
It shows a form (name, email, message), reads POST,
validates on the server, escapes all output with htmlspecialchars,
and keeps the typed values after an error.
Explain each part in plain English before writing files.
Use only fake sample data.</pre>

<blockquote><p><strong>Quick check:</strong> A search box filters a shop stock list. Should the form use GET or POST?</p><p>Answer: GET, because searching does not change data and a GET address can be bookmarked and shared.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why must <code>session_start()</code> come before any output?</p><p>Answer: It sends a cookie header, and headers cannot be sent after the page has started printing.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Start Apache in the XAMPP Control Panel and create <code>C:\xampp\htdocs\school\hello.php</code>.</li>
<li>Type the form example above, open it at <code>http://localhost/school/hello.php</code> and submit an empty name.</li>
<li>Add the visit counter and refresh five times to watch the session count.</li>
<li>Type <code>&lt;b&gt;test&lt;/b&gt;</code> as the name and confirm it appears as text, not bold.</li>
<li>Ask Claude Code or another AI assistant to explain each line, and write one sentence per line in your notes.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>PHP runs on the server; the browser receives only the output.</li>
<li>Use GET to read and filter, POST to change data.</li>
<li>Escape output with htmlspecialchars; never trust input.</li>
<li>Sessions keep data on the server and need session_start() first.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'PHP in practice: includes, uploads, validation, errors',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to build reusable page parts, validate input on the server, accept a file upload safely, handle errors with exceptions and use Claude Code to write and debug PHP. This matters because a real contact form or registration page must still behave safely when users make mistakes or attackers send bad data.</p>

<h3>Reusable headers, footers and dynamic pages</h3>
<p>Do not copy the menu into every page. Put it in one file and include it. A <strong>dynamic page</strong> is one whose content changes with data, such as the visitor's choice or a database row.</p>
<pre>&lt;?php
// about.php
$pageTitle = 'About us';
require __DIR__ . '/includes/header.php';
?&gt;
&lt;h1&gt;&lt;?= htmlspecialchars($pageTitle) ?&gt;&lt;/h1&gt;
&lt;?php require __DIR__ . '/includes/footer.php'; ?&gt;</pre>
<p><code>require</code> stops the script if the file is missing, which is what you want for essential parts.</p>

<h3>Server-side validation</h3>
<p>JavaScript checks in the browser help users, but an attacker can skip them, so the server must always check again.</p>
<pre>&lt;?php
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');
$errors  = [];
if ($name === '' || mb_strlen($name) &gt; 80) {
    $errors['name'] = 'Enter a name (up to 80 characters).';
}
if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    $errors['email'] = 'Enter a valid email address.';
}
if (mb_strlen($message) &lt; 10) {
    $errors['message'] = 'Message is too short.';
}</pre>

<h3>Uploading files securely</h3>
<p>An upload can carry a harmful script disguised as a photo. Check size, extension and the real type, then store it under a new random name outside the web root, which is the public folder (<code>htdocs</code>).</p>
<pre>&lt;?php
$file = $_FILES['photo'] ?? null;
if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    exit('Upload failed.');
}
if ($file['size'] &gt; 2 * 1024 * 1024) {
    exit('Maximum 2 MB.');
}
$allowed = ['jpg' =&gt; 'image/jpeg', 'png' =&gt; 'image/png'];
$ext   = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo-&gt;file($file['tmp_name']);
if (!isset($allowed[$ext]) || $allowed[$ext] !== $mime) {
    exit('Only JPG or PNG images.');
}
$newName = bin2hex(random_bytes(16)) . '.' . $ext;
move_uploaded_file($file['tmp_name'], 'C:/xampp/uploads/' . $newName);</pre>
<p>The form needs <code>enctype="multipart/form-data"</code>. If files must be public, serve them through a PHP script, or add a rule in the upload folder that stops PHP from running.</p>

<h3>Errors and exceptions</h3>
<p>Show detailed errors on your own computer, never to the public. Wrap risky work in <code>try</code> and <code>catch</code>, log the detail and show a friendly message.</p>
<pre>&lt;?php
try {
    $data = riskyTask();
} catch (Throwable $e) {
    error_log($e-&gt;getMessage());
    http_response_code(500);
    echo 'Something went wrong. Please try again.';
}</pre>

<h3>Case scenario</h3>
<p>A school in Gulu let parents upload passport photos and checked only that the name ended in <code>.jpg</code>. A student renamed a small PHP file to <code>photo.jpg.php</code>, and an early version accepted it. The script could list the school's files. The school found it from strange traffic, deleted the file and changed passwords. The decision was to check the real MIME type, rename every file randomly and store uploads outside <code>htdocs</code>. The lesson: a file name proves nothing.</p>

<h3>Template: asking Claude Code to debug</h3>
<pre>contact.php shows a blank page after I submit the form.
Look at the file, list the three most likely causes,
and ask me before running any command or editing files.
Then explain the fix so I can repeat it myself.</pre>
<p>Always read the proposed change before approving. Follow the current official documentation for installing and using Claude Code; if you cannot use it, another AI assistant works for the same exercise.</p>

<h3>Connecting PHP to MySQL</h3>
<p>You will connect with PDO in the next lessons. For now remember that a form is only half of a feature: it must save its data. The next two lessons build that database layer.</p>

<blockquote><p><strong>Quick check:</strong> A form already checks the email in JavaScript. Is server-side validation still needed?</p><p>Answer: Yes, because attackers can bypass browser code and send requests directly to the server.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why rename uploaded files with random names?</p><p>Answer: It stops attackers guessing file names and stops dangerous names or double extensions from being used.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create <code>includes/header.php</code> and <code>footer.php</code>, then include them in <code>index.php</code> and <code>contact.php</code>.</li>
<li>Build the contact form with the validation above, showing one error message under each wrong field.</li>
<li>Add an optional JPG or PNG upload with the checks above and test a renamed text file to see it rejected.</li>
<li>For the assignment, save each valid message to a text file for now (fake data only); the database comes next.</li>
<li>Ask Claude Code to review your file for missing validation, then judge each suggestion yourself.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Include shared header and footer files instead of copying them.</li>
<li>Validate every input on the server, whatever the browser did.</li>
<li>Check upload size, extension and finfo type, then rename and store safely.</li>
<li>Log errors privately and show users a plain message.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Databases and SQL: tables, keys and CRUD',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to explain what a relational database is, create tables with keys and constraints in phpMyAdmin, and run insert, select, update and delete queries. This matters because websites that forget their data are useless: registrations, appointments and stock levels must survive a restart.</p>

<h3>Databases, MySQL and MariaDB</h3>
<p>A <strong>relational database</strong> stores data in <strong>tables</strong> (like spreadsheets). Each <strong>row</strong> is one record and each <strong>column</strong> is one fact. Tables connect through shared values. <strong>MySQL</strong> and <strong>MariaDB</strong> are two closely related database servers; XAMPP includes MariaDB, and almost all the SQL here works on both. <strong>SQL</strong> is the language you use to talk to them. <strong>phpMyAdmin</strong> is the web screen for managing them.</p>

<h3>Creating the database and a table</h3>
<p>We design the student registration for a school in Gulu. Each column needs a <strong>data type</strong>, and the <strong>primary key</strong> is the unique ID of each row.</p>
<pre>CREATE DATABASE school_reg
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE school_reg;

CREATE TABLE classes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(40) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE students (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  birth_date DATE NULL,
  class_id INT UNSIGNED NOT NULL,
  fee_paid DECIMAL(10,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_students_class FOREIGN KEY (class_id)
    REFERENCES classes (id)
) ENGINE=InnoDB;</pre>
<table>
<thead><tr><th>Type</th><th>Use</th></tr></thead>
<tbody>
<tr><td>INT</td><td>Whole numbers, IDs</td></tr>
<tr><td>VARCHAR(n)</td><td>Short text such as names</td></tr>
<tr><td>TEXT</td><td>Long text</td></tr>
<tr><td>DATE, TIMESTAMP</td><td>Dates and times</td></tr>
<tr><td>DECIMAL(10,2)</td><td>Money; never use floating point for fees</td></tr>
</tbody>
</table>

<h3>Insert, retrieve, update, delete</h3>
<pre>INSERT INTO classes (name) VALUES ('Senior 1'), ('Senior 2');

INSERT INTO students (full_name, email, class_id)
VALUES ('Akello Grace', 'grace@example.com', 1);

SELECT id, full_name, email FROM students
WHERE class_id = 1 AND fee_paid &gt; 0
ORDER BY full_name ASC
LIMIT 20;

SELECT * FROM students WHERE full_name LIKE 'Ak%';

UPDATE students SET fee_paid = 150000 WHERE id = 1;

DELETE FROM students WHERE id = 1;</pre>
<p>Always write <code>WHERE</code> on an UPDATE or DELETE. Without it, every row changes. List the columns you need rather than <code>SELECT *</code>.</p>

<h3>Relationships</h3>
<p>One class has many students, so each student stores the <code>class_id</code> of its class. That column is a <strong>foreign key</strong>: the database refuses a student whose class does not exist. This avoids typing the class name hundreds of times and spelling it differently each time.</p>

<h3>Worked example: clinic list</h3>
<p>A clinic in Mbale tracks appointments. Tables: <code>patients</code> (id, name, phone) and <code>appointments</code> (id, patient_id, visit_date, reason). The patient is stored once, and each appointment points to that patient with <code>patient_id</code>.</p>

<h3>Case scenario</h3>
<p>A shop owner in Lira ran <code>DELETE FROM stock</code> in phpMyAdmin to remove one item and forgot the WHERE. All 600 products vanished, and no backup existed. She spent a week retyping from paper receipts. The decision afterwards was to export a backup every evening and to test risky queries with SELECT first. The lesson: write the SELECT, check the rows, only then change it to DELETE.</p>

<h3>Template: asking Claude Code for a schema</h3>
<pre>Design a MySQL schema for a school in Gulu: students, classes, fees.
Use utf8mb4, InnoDB, primary keys, foreign keys, NOT NULL and UNIQUE
where sensible. Show it as CREATE TABLE statements and explain each
choice. Do not run anything yet.</pre>

<blockquote><p><strong>Quick check:</strong> What does a primary key guarantee?</p><p>Answer: Every row has a unique, non-empty identifier, so one record can be found and changed precisely.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why use DECIMAL for school fees instead of a float?</p><p>Answer: DECIMAL stores exact values, while floats can produce tiny rounding errors in money.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Start Apache and MySQL in XAMPP, then open <code>http://localhost/phpmyadmin</code>.</li>
<li>Look for the <strong>SQL</strong> tab, paste the CREATE statements and run them.</li>
<li>Insert two classes and three students using fake names.</li>
<li>Try inserting a student with <code>class_id</code> 99 and read the error.</li>
<li>Practise SELECT with WHERE and ORDER BY, then change one fee with UPDATE.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Tables hold records; keys identify and link them.</li>
<li>Choose exact data types and use utf8mb4.</li>
<li>CRUD means INSERT, SELECT, UPDATE, DELETE.</li>
<li>Test with SELECT before UPDATE or DELETE.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'PHP and MySQL together: joins, security, backups',
                    'minutes' => 26,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to join tables, connect PHP to MySQL safely with PDO and prepared statements, use constraints, export, import and back up a database, and review AI-written SQL. This matters because the most common website break-ins come from careless database code, and the most painful losses come from having no backup.</p>

<h3>Joins and schema design</h3>
<p>A <strong>join</strong> combines rows from related tables in one result.</p>
<pre>SELECT s.id, s.full_name, c.name AS class_name
FROM students AS s
INNER JOIN classes AS c ON c.id = s.class_id
WHERE c.name = 'Senior 1'
ORDER BY s.full_name;</pre>
<p><code>INNER JOIN</code> keeps only matching rows; <code>LEFT JOIN</code> keeps all students even when the class is empty. Good design means one fact stored once, a primary key everywhere, foreign keys for links, and constraints (NOT NULL, UNIQUE, CHECK, foreign keys) so the database rejects bad data even if PHP forgets.</p>

<h3>Connecting PHP with PDO</h3>
<p>Never write passwords inside code that goes to GitHub. Put settings in a <code>.env</code> or config file, add it to <code>.gitignore</code>, and read it in one connection file.</p>
<pre>&lt;?php
// config/.env  (never commit this file)
// DB_HOST=localhost
// DB_NAME=school_reg
// DB_USER=school_app
// DB_PASS=use-a-strong-password

// db.php
$env = parse_ini_file(__DIR__ . '/config/.env');
$dsn = "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS'], [
        PDO::ATTR_ERRMODE =&gt; PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE =&gt; PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES =&gt; false,
    ]);
} catch (PDOException $e) {
    error_log($e-&gt;getMessage());
    exit('Database unavailable.');
}</pre>

<h3>Prepared statements against SQL injection</h3>
<p><strong>SQL injection</strong> happens when user text becomes part of the SQL command. A <strong>prepared statement</strong> sends the command and the values separately, so values can never act as commands.</p>
<pre>&lt;?php
// WRONG: "SELECT * FROM students WHERE email = '$email'"

$stmt = $pdo-&gt;prepare('INSERT INTO students (full_name, email, class_id)
                       VALUES (:name, :email, :class)');
$stmt-&gt;execute(['name' =&gt; $name, 'email' =&gt; $email, 'class' =&gt; $classId]);

$stmt = $pdo-&gt;prepare('SELECT * FROM students WHERE id = :id');
$stmt-&gt;execute(['id' =&gt; $id]);
$student = $stmt-&gt;fetch();

$stmt = $pdo-&gt;prepare('UPDATE students SET full_name = :n WHERE id = :id');
$stmt-&gt;execute(['n' =&gt; $name, 'id' =&gt; $id]);

$stmt = $pdo-&gt;prepare('DELETE FROM students WHERE id = :id');
$stmt-&gt;execute(['id' =&gt; $id]);</pre>

<h3>Export, import and backup</h3>
<p>In phpMyAdmin, select your database and look for the <strong>Export</strong> tab (choose Quick, SQL) to download a <code>.sql</code> file. To restore, create an empty database, look for <strong>Import</strong> and choose the file. From the command line:</p>
<pre>C:\xampp\mysql\bin\mysqldump -u root -p school_reg &gt; school_reg_backup.sql
C:\xampp\mysql\bin\mysql -u root -p school_reg &lt; school_reg_backup.sql</pre>
<p>A backup is only real once you have restored it somewhere. Keep dated copies in two places, such as a flash disk and cloud storage, and protect them, because they contain personal data.</p>

<h3>Review checklist for AI-written PHP and SQL</h3>
<ul>
<li>Any SQL built by joining strings or variables (SQL injection)?</li>
<li>Any output printed without htmlspecialchars (XSS)?</li>
<li>Is every input validated on the server, including lengths and email?</li>
<li>Are passwords hashed and settings kept out of Git?</li>
<li>Do UPDATE and DELETE have a WHERE and a permission check?</li>
<li>Do errors show database details to visitors?</li>
</ul>

<h3>Case scenario</h3>
<p>A training centre in Kampala used AI-generated code for its search page without reviewing it. The code joined the typed text into the query, and a visitor entered a quote and an extra command, which exposed the whole list of student emails. The centre notified the affected students, rebuilt the page with prepared statements and began monthly reviews. The lesson: an AI assistant can write code that works and is still unsafe, so you review it.</p>

<h3>Template: asking Claude Code to review</h3>
<pre>Review students.php and db.php for SQL injection, XSS, missing
validation and exposed secrets. List each problem with the line
and a fix. Do not change files until I approve.</pre>

<blockquote><p><strong>Quick check:</strong> Why can a prepared statement not be hijacked by a typed quote?</p><p>Answer: The values travel separately from the SQL command, so they are treated as data, never as code.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> When is a backup trustworthy?</p><p>Answer: After you have restored it on another database and checked the data, because an untested file may be broken.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a limited MySQL user for the app and a <code>config/.env</code> with its settings; add the file to <code>.gitignore</code>.</li>
<li>Build the student registration system: a list page (with the join), an add form, an edit page and a delete button that asks for confirmation. Use PDO prepared statements, validation and htmlspecialchars everywhere.</li>
<li>Export the database, create <code>school_reg_test</code> and import the file there.</li>
<li>Ask Claude Code to review your pages with the checklist and fix one real issue.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Joins combine related tables; constraints guard your data.</li>
<li>Use PDO, prepared statements and a config file kept out of Git.</li>
<li>Escape output, validate input, store hashed passwords.</li>
<li>Export regularly and test your restores.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A visitor types a script tag into the name box of your appointment page and it runs when the receptionist opens the list. What was missing?
- Server-side session handling
- A faster database
* Escaping of the output with htmlspecialchars
- A larger upload limit
= Output that is not escaped lets typed HTML or script run in other people's browsers (XSS). htmlspecialchars turns the characters into plain text.

Q: Which request method suits a stock-list search box that changes no data?
* GET
- POST
- Neither, it needs a cookie
= GET suits reading and filtering, and the address can be bookmarked. POST is for changes and private data.

Q: Your form validates the email in JavaScript only. A friend sends the form fields directly to your server with a tool and bad data gets saved. What is the fix?
- Make the JavaScript longer
- Hide the form
- Block the friend's address
* Validate again on the server with filter_var and length checks
= The browser is outside your control, so the server must check every input itself.

Q: Which checks make a file upload safer? (choose all that apply)
* Limit the file size
- Trust the name the user gave the file
* Check the real MIME type with finfo
* Store it under a new random name
= Size, real type and random names reduce the risk. A user-given name proves nothing about the content.

Q: A shop owner ran DELETE FROM stock without a WHERE clause and lost every product. Which habits would have prevented the disaster?
- Using TEXT columns for every field
* Testing with SELECT first and keeping a recent export
- Turning off the foreign keys
- Running the query twice
= Previewing the rows with SELECT catches a missing WHERE, and a recent backup lets you recover when a mistake still happens.

Q: Why is DECIMAL(10,2) preferred for fees?
- It uses less disk space than any other type
- It accepts text such as "free"
* It stores exact money values without rounding errors
= Floats can drift by small fractions, which is unacceptable for money.

Q: A page builds its SQL with "WHERE email = '" . $email . "'" and a visitor enters a quote and extra commands. What should replace the code?
* A prepared statement with a named parameter
- A longer password for the database
- Hiding the table names
- Converting the page to GET
= Prepared statements keep values separate from the SQL command, so typed text cannot change the query.

Q: Your backup file exists but you have never tried restoring it. A server fails. What is the risk?
- None, a file always works
* The file may be incomplete or broken and you only find out too late
- The restore will always delete the backup
= A backup is trusted only after a test restore on another database.
QUIZ,
        ],
        // ---------------------------------------------------------------- STAGE 5
        [
            'title'   => 'Stage 5: Accounts and Complete Applications',
            'summary' => 'Build secure registration, login, roles and Google Sign-In, then combine frontend, backend, database and authentication into a complete, tested management system.',
            'lessons' => [
                [
                    'title'   => 'Authentication basics: passwords, sessions, reset and roles',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to explain authentication and authorization, build registration and login with safely hashed passwords, manage sessions securely, design a password reset flow and plan role-based access. This matters because one weak login can expose every pupil record, patient booking or member balance in your system.</p>

<h3>Authentication versus authorization</h3>
<p><strong>Authentication</strong> asks "who are you?" (login). <strong>Authorization</strong> asks "what are you allowed to do?" (roles and permissions). A school portal needs both: a parent proves who they are, then may see only their own child's results.</p>

<h3>Registration and password hashing</h3>
<p>Never store a password, never email one back and never log one. Store a one-way <strong>hash</strong>. PHP does the hard work for you.</p>
<pre><code>$hash = password_hash($password, PASSWORD_DEFAULT);
$st = $pdo-&gt;prepare('INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)');
$st-&gt;execute([$email, $hash, 'member']);

// at login
if ($user &amp;&amp; password_verify($password, $user['password_hash'])) { /* success */ }</code></pre>
<p>Use a column long enough for future hash formats (255 characters). Validate on the server: a valid email, a minimum password length (12 characters is a good start), and a unique email enforced by a UNIQUE index.</p>

<h3>Login, logout and secure sessions</h3>
<pre><code>session_set_cookie_params([
    'httponly' =&gt; true,
    'secure'   =&gt; true,
    'samesite' =&gt; 'Lax',
]);
session_start();

// after password_verify succeeds:
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];

// logout
$_SESSION = [];
session_destroy();</code></pre>
<p>HttpOnly hides the cookie from JavaScript, Secure sends it only over HTTPS, SameSite limits cross-site sending. Regenerating the session id on login stops <em>session fixation</em>. Always use one <strong>generic error</strong>: "Email or password is incorrect". Saying "no such email" helps attackers list your members.</p>

<h3>Email validation and confirmation</h3>
<p>Check the format with <code>filter_var($email, FILTER_VALIDATE_EMAIL)</code>, but a valid format is not a real owner. Store the account as unconfirmed, email a single-use link and activate only when it is clicked.</p>

<h3>Password reset, step by step</h3>
<ol>
<li>The user enters an email. Always answer: "If that address is registered, we have sent a link." This does not leak whether the email exists.</li>
<li>Create a random token: <code>$token = bin2hex(random_bytes(32));</code></li>
<li>Store only <code>hash('sha256', $token)</code> with an expiry (for example 30 minutes) and send the raw token by email.</li>
<li>On the link, hash the incoming token, find an unexpired, unused row, let the user set a new password, then mark the token used and end the user's other sessions.</li>
</ol>

<h3>Role-based access control</h3>
<table>
<thead><tr><th>Role</th><th>Can do</th></tr></thead>
<tbody>
<tr><td>member</td><td>View and edit own profile</td></tr>
<tr><td>officer</td><td>Record contributions, view member list</td></tr>
<tr><td>admin</td><td>Everything, including managing users</td></tr>
</tbody>
</table>
<p>Store a role per user, and always check it on the server.</p>

<h3>Case scenario</h3>
<p>A SACCO in Mbarara kept members' passwords as plain text in the database so staff could "help when someone forgets". A developer exporting the table for a backup lost the file on a shared flash disk. Hundreds of members reused those passwords on mobile money apps. The SACCO had to reset every account, apologise publicly and lost trust. The fix was password_hash, a reset email flow and no human ever seeing a password. Lesson: if you can read a password, so can a thief.</p>

<blockquote><p><strong>Quick check:</strong> Why should a login error say "Email or password is incorrect" instead of "Email not found"?</p><p>Answer: A specific message tells attackers which emails are registered, so a generic message protects members' privacy.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why store a hash of the reset token instead of the token itself?</p><p>Answer: If the database leaks, stolen hashes cannot be used to reset accounts, because only the emailed raw token works.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a <code>users</code> table with email (UNIQUE), password_hash, role and confirmed columns.</li>
<li>Build a registration form that validates the email and password length on the server and uses password_hash.</li>
<li>Build login with password_verify, a generic error and session_regenerate_id.</li>
<li>Build logout that clears and destroys the session.</li>
<li>Write the four reset steps in plain words for your project, then ask an AI assistant or a classmate to find holes. Never paste real passwords or keys into any AI tool.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Authentication proves identity; authorization decides permission.</li>
<li>Use password_hash and password_verify; never store or email plain passwords.</li>
<li>Regenerate the session id on login and set HttpOnly, Secure and SameSite cookie options.</li>
<li>Reset tokens must be random, hashed, expiring and single-use, and the page must not reveal whether an email exists.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Protecting pages, Google Sign-In, rate limits and testing',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to protect every page and action on the server, explain Google Sign-In with OAuth 2.0 and OpenID Connect, limit login attempts, build user profiles and test authentication with a checklist. This matters because attackers do not click your buttons: they type URLs and send requests directly.</p>

<h3>Protect pages and actions on the server</h3>
<p>Authentication must be enforced on the server. Hiding a menu link or button in the browser does not stop anyone from visiting the address or sending the request. Create one helper and call it at the top of every protected file.</p>
<pre><code>function require_role(string $role): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: /login.php'); exit;
    }
    if (($_SESSION['role'] ?? '') !== $role) {
        http_response_code(403); exit('Not allowed');
    }
}

// admin/delete-user.php
require_role('admin');
verify_csrf($_POST['csrf'] ?? '');</code></pre>
<p>Check both the <em>page</em> and each <em>action</em> (delete, approve, export). Also check ownership: a parent opening <code>result.php?id=15</code> must be refused if pupil 15 is not their child.</p>

<h3>CSRF tokens on forms</h3>
<p>Cross-site request forgery tricks a logged-in browser into submitting a request. Put a random token in the session, place it in a hidden field and compare it when the form is posted.</p>
<pre><code>$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
function verify_csrf(string $t): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(400); exit('Bad request');
    }
}</code></pre>

<h3>Google Sign-In: the idea</h3>
<p><strong>OAuth 2.0</strong> lets a user grant your app limited access; <strong>OpenID Connect</strong> adds a signed <em>ID token</em> that says who the user is. The authorization code flow works like this:</p>
<ol>
<li>You register the app in Google's developer console and receive a <strong>client ID</strong> and <strong>client secret</strong>. You also register the exact <strong>redirect (callback) URL</strong>. Console menus change, so follow Google's current documentation.</li>
<li>Your site sends the browser to Google with the client ID, callback URL, a random <strong>state</strong> and a <strong>nonce</strong>.</li>
<li>The user signs in at Google and is sent back to your callback with a short-lived code and the state.</li>
<li>Your <em>server</em> checks the state matches, then exchanges the code for tokens using the client secret.</li>
<li>Your server verifies the ID token (signature, issuer, audience equals your client ID, expiry, nonce) and then finds or creates the local user.</li>
</ol>
<p>Never trust identity data sent by the browser, and keep the client secret on the server only, never in JavaScript. Use a maintained library for verification rather than writing it yourself.</p>

<h3>User profiles</h3>
<p>Let users edit only their own name, phone and photo. Load the profile by the session user id, never by an id in the URL, so nobody edits another person's record.</p>

<h3>Rate limiting and lockout</h3>
<p>Record failed attempts per account and per IP address. After five failures in fifteen minutes, wait before allowing another try, and keep the message generic. This slows password guessing without revealing which accounts exist.</p>

<h3>Auth test checklist (template)</h3>
<ul>
<li>Visit every protected URL while logged out: expect a redirect to login.</li>
<li>Visit admin URLs as a normal member: expect 403.</li>
<li>Change an id in the URL to another user's: expect refusal.</li>
<li>Submit a form without the CSRF token: expect rejection.</li>
<li>Try six wrong passwords: expect a lockout delay.</li>
<li>Use a reset link twice and after expiry: expect failure.</li>
<li>Check the database: no plain passwords, only hashes.</li>
</ul>

<h3>Case scenario</h3>
<p>A clinic booking system in Kisumu hid the Admin menu from receptionists. A curious receptionist guessed <code>/admin/patients.php</code> and found every patient record, because the page itself never checked the role. The clinic reported the incident, added require_role to every admin file and wrote the test checklist above. Lesson: a hidden link is not a lock.</p>

<blockquote><p><strong>Quick check:</strong> Where must the Google client secret be kept?</p><p>Answer: On the server only, because anything in browser code can be read by every visitor.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is the purpose of the state value in OAuth?</p><p>Answer: It ties the callback to the login you started, which blocks forged callbacks.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Add the role column and the require_role helper, then call it on an admin page and an admin action.</li>
<li>Add CSRF tokens to every form that changes data.</li>
<li>Build a profile page that loads data by session user id and updates name and phone.</li>
<li>Add a simple failed-login counter with a short lockout.</li>
<li>Assignment: finish a secure user management system with registration, login, logout, profiles and separate administrator access. Run the checklist, then ask Claude Code or another assistant to review your code for security gaps, reading every suggestion before applying it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Check login, role and ownership on the server for every page and every action.</li>
<li>CSRF tokens protect forms; rate limits slow guessing.</li>
<li>Google Sign-In uses the authorization code flow, with state, nonce, ID token verification and a server-side secret.</li>
<li>Test authentication with a written checklist, including URL guessing.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'From requirements to a plan: SRS, workflows, wireframes and data',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to turn a vague idea into written requirements, a short software requirements specification, workflows, wireframes, a database plan and reusable components. This matters because most failed projects are not badly coded; they were never clearly understood.</p>

<h3>Analyse user needs</h3>
<p>Talk to the people who will use the system. A shop owner in Jinja may say "I want to know what is running out". That is a need; "a low-stock report with a red warning under 10 items" is a feature. Ask: who uses it, what do they do today, what goes wrong, what would success look like? Separate <strong>must have</strong> features from <strong>nice to have</strong>, and build the first group first.</p>

<h3>A short SRS template</h3>
<p>A <strong>software requirements specification</strong> (SRS) is a written agreement of what will be built. Keep it to two pages.</p>
<pre><code>1. Purpose: one sentence on the problem solved
2. Users and roles: who logs in, what each may do
3. Functional requirements: FR1, FR2 ... (testable sentences)
4. Data: main things stored (students, items, visits)
5. Non-functional: speed, security, backups, phone use
6. Out of scope: what will NOT be built now
7. Acceptance tests: how we prove each FR works</code></pre>
<p>A good requirement is testable: "FR3: A teacher can add a pupil with name, class and guardian phone; saving without a name shows an error." A bad one is "the system must be user friendly".</p>

<h3>Workflows</h3>
<p>A workflow is the path through the system. Clinic booking: patient registers, picks a date, receptionist confirms, patient receives a message, doctor marks the visit complete. Write each as numbered steps and note who acts and what can fail (date already full, phone missing).</p>

<h3>Wireframes</h3>
<p>A wireframe is a rough layout without colour. Draw it before coding so mistakes cost minutes, not days.</p>
<pre><code>+--------------------------------------+
| Shop Stock      [Search____] [Add]   |
+--------------------------------------+
| Item      | Qty | Price | Actions    |
| Sugar 1kg |  42 | 4500  | Edit Del   |
| Soap bar  |   6 | 1500  | Edit Del   |
+--------------------------------------+
| &lt; Prev   Page 1 of 5   Next &gt;        |</code></pre>

<h3>Database relationships</h3>
<p>List the things (entities), then how they connect. One class has many pupils; one pupil belongs to one class; one pupil has many fee payments. The "many" side holds the foreign key.</p>
<table>
<thead><tr><th>Relationship</th><th>How to store it</th></tr></thead>
<tbody>
<tr><td>One to many (class to pupils)</td><td>pupils.class_id references classes.id</td></tr>
<tr><td>Many to many (pupils to clubs)</td><td>a link table with pupil_id and club_id</td></tr>
<tr><td>One to one (user to profile)</td><td>profiles.user_id UNIQUE</td></tr>
</tbody>
</table>

<h3>Reusable components and forms</h3>
<p>Do not copy the header, menu, table and form fields into every page. Put them in include files or small functions: <code>header.php</code>, <code>footer.php</code>, a function that prints an input with its error message. Fix a bug once and every page is fixed. Forms post to a backend script that validates on the server, uses prepared statements, saves and redirects with a message.</p>

<h3>Case scenario</h3>
<p>A youth group in Gulu asked a freelancer for "a members system". He started coding at once. After three weeks the group revealed they also needed monthly savings, and the treasurer wanted to see only totals, not personal details. Much had to be rebuilt. The next project began with a one-page SRS and wireframes, and the client signed each requirement. Lesson: an hour of questions saves a week of rework.</p>

<blockquote><p><strong>Quick check:</strong> Is "the system must be fast and nice" a good requirement?</p><p>Answer: No, because it cannot be tested; a good requirement states something you can check, such as "the member list loads in under 3 seconds".</p></blockquote>

<blockquote><p><strong>Quick check:</strong> In a one-to-many relationship between classes and pupils, which table holds the foreign key?</p><p>Answer: The pupils table, because each pupil row points to its one class.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose one project: student information, inventory, appointment booking or community organisation management.</li>
<li>Interview one possible user and write five needs in their own words.</li>
<li>Fill in the SRS template with at least six testable requirements and an out-of-scope list.</li>
<li>Sketch the list page and the add form as ASCII or on paper, and write the main workflow as numbered steps.</li>
<li>List your tables and mark each relationship as one to many, many to many or one to one.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Start from user needs, then write testable requirements.</li>
<li>A short SRS, workflows and wireframes prevent expensive rework.</li>
<li>Plan tables and relationships before writing queries.</li>
<li>Reusable components and server-side form handling keep code small and safe.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Building the application: CRUD, dashboards, reports and delivery',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to build CRUD screens, search with pagination, protect them with roles, produce summary reports, test the workflow end to end and develop features step by step with Claude Code. This matters because a complete application is many small features that must work together and stay safe.</p>

<h3>CRUD, dashboards and admin panels</h3>
<p><strong>CRUD</strong> means Create, Read, Update, Delete: the four things most screens do. For each table build a list page, an add form, an edit form and a delete action that accepts POST only, with a CSRF token and a role check. A <strong>dashboard</strong> shows a few key numbers at a glance (pupils enrolled, items low on stock, today's appointments). An <strong>admin panel</strong> is the protected area for managing users and settings.</p>

<h3>Search, filter and pagination</h3>
<p>Never load thousands of rows at once. Use LIMIT and OFFSET with prepared statements.</p>
<pre><code>$perPage = 20;
$page    = max(1, (int) ($_GET['page'] ?? 1));
$total   = (int) $pdo-&gt;query('SELECT COUNT(*) FROM items')-&gt;fetchColumn();
$pages   = max(1, (int) ceil($total / $perPage));
$page    = min($page, $pages);
$offset  = ($page - 1) * $perPage;

$st = $pdo-&gt;prepare('SELECT * FROM items WHERE name LIKE ? ORDER BY name LIMIT ? OFFSET ?');
$st-&gt;bindValue(1, '%' . $q . '%');
$st-&gt;bindValue(2, $perPage, PDO::PARAM_INT);
$st-&gt;bindValue(3, $offset, PDO::PARAM_INT);
$st-&gt;execute();</code></pre>
<p>The arithmetic: 103 items at 20 per page gives ceil(103 / 20) = 6 pages. Page 3 starts at offset (3 - 1) x 20 = 40. Filters (class, status, date) become extra WHERE conditions; keep the search text in the page links.</p>

<h3>Roles and permissions matrix (template)</h3>
<table>
<thead><tr><th>Action</th><th>Admin</th><th>Officer</th><th>Member</th></tr></thead>
<tbody>
<tr><td>View own record</td><td>Yes</td><td>Yes</td><td>Yes</td></tr>
<tr><td>Add or edit records</td><td>Yes</td><td>Yes</td><td>No</td></tr>
<tr><td>Delete records</td><td>Yes</td><td>No</td><td>No</td></tr>
<tr><td>Manage users</td><td>Yes</td><td>No</td><td>No</td></tr>
<tr><td>View reports</td><td>Yes</td><td>Yes</td><td>No</td></tr>
</tbody>
</table>
<p>Turn every cell into a server-side check and a test.</p>

<h3>Reports and summaries</h3>
<pre><code>SELECT class_id, COUNT(*) AS pupils, SUM(fee_paid) AS collected
FROM pupils
GROUP BY class_id
ORDER BY class_id;</code></pre>
<p>COUNT counts rows, SUM adds a column, GROUP BY makes one result row per group. Always sum in SQL, not in a loop, and show the date range on the report.</p>

<h3>End-to-end testing and documentation</h3>
<p>Walk the full workflow as each role: register, log in, add a record, search for it, edit, delete, view the report, log out. Also try bad input: empty fields, very long text, a letter where a number belongs, and a stranger's id in the URL. Document the project in a README: purpose, setup steps, database import, roles and test accounts (fake ones), and how to back up.</p>

<h3>Building with Claude Code, one small step at a time</h3>
<p>Claude Code is Anthropic's AI coding assistant that runs in the terminal inside your project folder; it can read files, propose edits and run commands, and asks permission first. Follow the current official documentation for setup; without it, use another assistant or write the code by hand. Work like this:</p>
<ol>
<li>Write a small prompt: "Add a delete action for items, POST only, with a CSRF check and admin role."</li>
<li>Read the proposed diff line by line. Reject anything you do not understand.</li>
<li>Run the feature and your tests.</li>
<li>Commit with a clear message, then start the next feature.</li>
</ol>
<p>Never paste passwords, API keys or real personal data into any AI tool.</p>

<h3>Case scenario</h3>
<p>A school in Mbale asked an assistant for "the whole portal" in one prompt and pasted back 2,000 lines nobody understood. Deleting a pupil worked through a plain link, so a browser prefetch erased records. The team restored a backup, then rebuilt feature by feature with small prompts, reviewed diffs and a commit each time. Lesson: small, reviewed steps are faster than big mysteries.</p>

<blockquote><p><strong>Quick check:</strong> With 45 records and 10 per page, how many pages are there and what is the offset of page 4?</p><p>Answer: 5 pages (ceil of 4.5) and offset 30, because (4 - 1) x 10 = 30.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why commit after each working feature?</p><p>Answer: You can return to a known good version if the next change breaks something.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Build list, add, edit and delete screens for your main table, with delete as POST and role-checked.</li>
<li>Add search and pagination with the arithmetic above.</li>
<li>Add a dashboard with two counts and one report using GROUP BY.</li>
<li>Complete the permissions matrix and test every cell with a different account.</li>
<li>Assignment: finish your small management system (student information, inventory, appointment booking or community organisation), write its README and commit after each feature.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>CRUD plus a dashboard and admin panel form the core of most systems.</li>
<li>Paginate with LIMIT and OFFSET; pages = ceil(total / per page).</li>
<li>Enforce the permissions matrix on the server and test every role end to end.</li>
<li>Build incrementally: small prompts, reviewed diffs, a commit per working feature.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A login page returns "No account with that email" for unknown emails and "Wrong password" otherwise. What is the problem?
- It is too slow
- It makes the page longer than necessary
* It reveals which emails are registered, helping attackers
- It breaks session handling
= A single generic message such as "Email or password is incorrect" does not leak who has an account.

Q: Which is the correct way to store a user's password?
- Encrypt it with a key kept in the same database
- Save it in plain text so staff can help
* Store the result of password_hash and check with password_verify
- Store an MD5 hash of the password
= password_hash with PASSWORD_DEFAULT uses a slow salted algorithm and password_verify checks it; plain text, reversible encryption and fast hashes are unsafe.

Q: A clinic hides the Admin link from receptionists, but /admin/patients.php has no role check. A receptionist types the address. What happens, and what is the fix?
- Nothing, because the link is hidden
* They see all patient data; add a server-side role check to the page and its actions
- The browser blocks the page automatically
- Only the menu needs a new colour
= Hiding elements is not security. The server must check login, role and ownership on every protected page and action.

Q: Which settings make a session cookie safer? (choose all that apply)
* HttpOnly
* Secure
* SameSite
- Storing the password inside the cookie
= HttpOnly hides the cookie from scripts, Secure limits it to HTTPS and SameSite limits cross-site sending. A password must never be put in a cookie.

Q: A password reset token is emailed to the user. How should the server store it?
- As plain text so it can be compared quickly
- Forever, so users can reuse the link
* As a hash with an expiry time, marked used after one use
- In the URL of the home page
= Hashed, expiring, single-use tokens mean a leaked database or an old email cannot be used to take over an account.

Q: In Google Sign-In, a developer puts the client secret in the JavaScript file so the browser can call Google. What is wrong?
- Nothing; secrets are meant to be public
- Google does not use secrets
* Anyone can read the secret in the browser; the code exchange and ID token verification belong on the server
- The redirect URL becomes invalid
= The client secret must stay on the server, which also verifies the state, the nonce and the ID token. The browser is never trusted.

Q: A school has 87 pupils and shows 25 per page. How many pages are there and what OFFSET starts page 3?
- 3 pages, offset 50
- 4 pages, offset 75
* 4 pages, offset 50
- 3 pages, offset 75
= ceil(87 / 25) = 4 pages. Page 3 starts at (3 - 1) x 25 = 50.

Q: You ask an AI assistant to build a whole school portal in one prompt and it returns thousands of lines. Which approach is best?
- Paste it all in and deploy immediately
* Ask for one small feature at a time, review each diff, test and commit
- Stop testing since the AI wrote it
- Give it real pupil data so it can test
= Small, reviewed, committed steps keep you in control and give you a safe version to return to; real personal data should never be pasted into AI tools.
QUIZ,
        ],
        // ---------------------------------------------------------------- STAGE 6
        [
            'title'   => 'Stage 6: APIs and Security',
            'summary' => 'Connect your application to outside services such as payments, email and maps, then protect it against the most common attacks and review AI-written code with a security checklist.',
            'lessons' => [
                [
                    'title'   => 'APIs, REST and JSON: talking to other systems',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to explain what an API is, read an HTTP request and response, and call an external API from JavaScript and from PHP with sensible error handling. This matters because almost every real application borrows something from another service, such as payments, SMS, weather or maps, and a careless connection is where most bugs and outages begin.</p>

<h3>What an API is</h3>
<p>An <strong>API</strong> (application programming interface) is a set of rules that lets one program ask another program for data or for an action. A restaurant menu is a good picture: you do not enter the kitchen, you order from the menu and the kitchen answers. A <strong>REST API</strong> is the common web style: every thing has an address (a URL), and you act on it using standard HTTP methods.</p>

<h3>HTTP methods and status codes</h3>
<table>
<thead><tr><th>Method</th><th>Usual meaning</th></tr></thead>
<tbody>
<tr><td>GET</td><td>Read data; should not change anything</td></tr>
<tr><td>POST</td><td>Create something or start an action</td></tr>
<tr><td>PUT or PATCH</td><td>Replace or partly update something</td></tr>
<tr><td>DELETE</td><td>Remove something</td></tr>
</tbody>
</table>
<table>
<thead><tr><th>Status</th><th>Meaning</th><th>What your code should do</th></tr></thead>
<tbody>
<tr><td>200, 201</td><td>Success, created</td><td>Use the data</td></tr>
<tr><td>400, 422</td><td>Your request was wrong</td><td>Fix the input; do not retry blindly</td></tr>
<tr><td>401, 403</td><td>Not logged in, or not allowed</td><td>Check the key or permissions</td></tr>
<tr><td>404</td><td>Not found</td><td>Show a friendly message</td></tr>
<tr><td>429</td><td>Too many requests</td><td>Wait, then try later</td></tr>
<tr><td>500, 503</td><td>The other server failed</td><td>Fail gracefully, maybe retry once</td></tr>
</tbody>
</table>

<h3>JSON</h3>
<p><strong>JSON</strong> is plain text that holds data as named values, and it is the usual language of APIs. A farmers' market app might receive this for one price listing:</p>
<pre>{"crop": "maize", "unit": "kg", "price_ugx": 1800, "market": "Masaka"}</pre>
<p>In JavaScript you turn it into an object with <code>response.json()</code>; in PHP you use <code>json_decode($text, true)</code>.</p>

<h3>Calling an API from JavaScript</h3>
<p>The browser function <code>fetch</code> sends the request. Remember that <code>fetch</code> only fails on network trouble; a 404 or 500 still counts as an answer, so check <code>response.ok</code> yourself.</p>
<pre>async function loadPrices() {
  try {
    const response = await fetch('/api/prices.php');
    if (!response.ok) {
      throw new Error('Server answered ' + response.status);
    }
    const data = await response.json();
    showPrices(data);
  } catch (error) {
    showMessage('Prices are not available now. Please try again soon.');
  }
}</pre>

<h3>Calling an API from PHP</h3>
<p>PHP code runs on your server, so it can call an API without exposing secrets to visitors. cURL gives the most control. Always set a timeout, otherwise a slow partner can freeze your own page.</p>
<pre>&lt;?php
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER =&gt; true,
    CURLOPT_CONNECTTIMEOUT =&gt; 3,
    CURLOPT_TIMEOUT        =&gt; 8,
]);
$body   = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error  = curl_error($ch);
curl_close($ch);

if ($body === false || $status !== 200) {
    error_log('Rates API failed: ' . $status . ' ' . $error);
    $data = null;               // use a fallback
} else {
    $data = json_decode($body, true);
}</pre>
<p>The simpler <code>file_get_contents</code> also works for small jobs when you pass a stream context with a timeout, but cURL reports status codes more clearly.</p>

<h3>Handling responses and errors</h3>
<ol>
<li>Check that the request itself worked (network).</li>
<li>Check the status code.</li>
<li>Check that the JSON decoded and holds the fields you need.</li>
<li>Only then show the data; otherwise log the technical detail privately and show a short human message.</li>
</ol>

<h3>Worked example: fees portal exchange rate</h3>
<p>A school fees portal shows fees in UGX and, for parents abroad, an approximate US dollar value taken from a rates API. The PHP page calls the API with an 8 second timeout, saves the last good rate in the database with the time, and shows that saved rate if the call fails, labelled "rate as at yesterday". Parents still see something useful, and the school page never hangs.</p>

<h3>Case scenario</h3>
<p>A clinic booking site loaded a weather widget in the middle of its booking page, with no timeout. One afternoon the weather service went slow, and every patient who opened the page waited a full minute, so many gave up. The developer moved the call to a background step, added a 3 second timeout and hid the widget when it failed. Bookings recovered the same day. The lesson: a non-essential service must never be able to block an essential page.</p>

<blockquote><p><strong>Quick check:</strong> A <code>fetch</code> call returns status 500. Does the <code>catch</code> block run automatically?</p><p>Answer: No, because fetch only rejects on network failure; you must test response.ok and throw yourself.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why set a timeout on a PHP call to an outside API?</p><p>Answer: Without one, a slow partner can hold your page open indefinitely and block your own visitors.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a small JSON object for one product from your own project, then read it in a JavaScript page with JSON.parse.</li>
<li>Write the meaning of each status code in the table from memory, then check yourself.</li>
<li>Write a PHP function that fetches a URL with cURL, timeouts and a null return on failure.</li>
<li>Test it with a wrong address and confirm your page shows a friendly message, not an error dump.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>REST APIs use URLs plus methods; status codes tell you what happened.</li>
<li>JSON is the common data format on both browser and server.</li>
<li>Check fetch's ok flag and give cURL timeouts.</li>
<li>Log the detail privately; show the visitor a short, kind message.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Keys, payments, webhooks and third-party failures',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to keep API keys safe, describe how email and payment services connect to a site, handle webhooks securely, test an API with a tool and design for the day a partner goes down. This matters because money, messages and customer trust all travel through these connections, and mistakes here are costly.</p>

<h3>Authentication and secrets</h3>
<p>Most APIs identify you with an <strong>API key</strong> or token sent in a header. Treat it like a password. Rules to follow:</p>
<ul>
<li>Keep keys on the server only. Anything in browser JavaScript can be read by any visitor.</li>
<li>Store them in a config file or environment variable that is listed in <code>.gitignore</code> so it is never committed.</li>
<li>Commit a sample file such as <code>config.example.php</code> with fake values.</li>
<li>Use separate test and live keys, and rotate (replace) a key the moment you suspect it leaked.</li>
</ul>
<pre>&lt;?php
// config.local.php  (listed in .gitignore, never committed)
return [
    'sms_api_key'  =&gt; 'put-the-real-key-here',
    'pay_api_key'  =&gt; 'put-the-real-key-here',
];</pre>
<p>Never paste real keys into an AI assistant. Give it the file structure and fake values instead.</p>

<h3>Email notification services</h3>
<p>Sending mail straight from a basic web server often lands in spam. A dedicated email service sends through a trusted route, and you call it with an API or SMTP settings. Send the mail after the main action succeeds, log failures, and never make the visitor wait for the email to finish before their page loads.</p>

<h3>Payment gateways: the concepts</h3>
<p>Most small sites use a <strong>hosted checkout</strong>: your server creates an order, redirects the customer to the provider's payment page, and the provider handles the card or mobile-money step. The customer then returns to your site. Key ideas:</p>
<ol>
<li>Never collect or store raw card numbers yourself; the provider's page does that.</li>
<li>The return redirect is not proof of payment, because a user can type that address by hand.</li>
<li>Confirm server to server: your PHP asks the provider, using your secret key, whether the order is really paid, or receives a webhook.</li>
<li>Build and test in the provider's <strong>test mode</strong> first, and follow the provider's official documentation for exact fields and addresses.</li>
</ol>

<h3>Webhooks</h3>
<p>A <strong>webhook</strong> is the reverse of an API call: the provider sends a message to a URL on your site when something happens, such as a payment completing. Handle it carefully:</p>
<ul>
<li><strong>Verify</strong> it came from the provider, either by checking the signature the provider documents or by re-querying the provider for the real status.</li>
<li>Be <strong>idempotent</strong>: providers may send the same event twice, so mark the order paid only once.</li>
<li>Reply quickly with a success code, and do slow work afterwards.</li>
</ul>

<h3>Maps, other services and testing</h3>
<p>Maps, weather, SMS and translation services follow the same pattern: read the documentation, get a key, respect limits, give credit where required. Before writing code, try the API with a testing tool such as a desktop API client or cURL in the terminal: send a request, read the status and body, and confirm the key works.</p>

<h3>When a third party is down</h3>
<p>Assume every partner will fail sometimes. Use timeouts, a fallback such as the last saved value, a clear message, and a retry later for non-urgent jobs. Keep a "pending" status so nothing is lost.</p>

<h3>Case scenario</h3>
<p>A developer building a farmers' market app pushed the whole project to a public repository, including the file holding the SMS key. Within hours the account balance was drained by strangers sending messages. The developer revoked the key, issued a new one, moved it to an ignored config file and added a spending limit. A second failure at another site: its payment webhook trusted any POST, so a tester forged one and an unpaid order was marked paid. Verifying the signature fixed it. The lesson: keys stay private, and incoming messages are never trusted without proof.</p>

<blockquote><p><strong>Quick check:</strong> A customer returns to your thank-you page after hosted checkout. Is the order paid?</p><p>Answer: Not yet proven, because anyone can visit that page; confirm with the provider server to server or by a verified webhook.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why must a webhook handler be idempotent?</p><p>Answer: Providers can resend an event, and handling it twice could double-credit or double-ship an order.</p></blockquote>

<h3>Try it yourself</h3>
<p>Assignment: integrate a public API into a website and display the data in a user-friendly interface.</p>
<ol>
<li>Choose a free public API that needs no payment (for example weather, public holidays or country information) and read its own documentation for the address, limits and sample response.</li>
<li>Try one request with an API testing tool and study the JSON.</li>
<li>Write a small PHP page that calls it with timeouts and error handling, keeping any key in an ignored config file.</li>
<li>Display the data as cards or a table with a loading message and a friendly failure message.</li>
<li>Switch your internet off and confirm the page still looks professional.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Keys live on the server, outside version control.</li>
<li>Use hosted checkout, test mode and server-to-server confirmation; never touch raw card data.</li>
<li>Verify webhooks, make them idempotent and answer fast.</li>
<li>Plan for partner failure with timeouts, fallbacks and clear messages.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Common attacks: injection, XSS, CSRF, passwords and sessions',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to recognise the most common web vulnerabilities and write the code that blocks them: prepared statements, output escaping, CSRF tokens, safe password storage and protected sessions. This matters because attackers scan the internet automatically, and a small school or clinic site is attacked just as readily as a bank.</p>

<h3>Security fundamentals</h3>
<p>Three ideas guide everything. <strong>Never trust input</strong>: anything from a form, URL, cookie or file can be hostile. <strong>Least privilege</strong>: give each user and each database account only what it needs. <strong>Defence in depth</strong>: use several layers, so one mistake is not a disaster. Common weaknesses include injection, cross-site scripting, forged requests, weak passwords, broken access control and exposed secrets.</p>

<h3>SQL injection</h3>
<p>Injection happens when user text is glued into a database command, so the attacker's text becomes part of the command. On a school fees portal, searching a student by number could be written like this:</p>
<pre>// VULNERABLE
$sql = "SELECT * FROM students WHERE adm_no = '" . $_GET['no'] . "'";
$rows = $pdo-&gt;query($sql);

// SAFE: prepared statement
$stmt = $pdo-&gt;prepare('SELECT * FROM students WHERE adm_no = :no');
$stmt-&gt;execute([':no' =&gt; $_GET['no']]);
$rows = $stmt-&gt;fetchAll();</pre>
<p>In the safe version the command and the data travel separately, so the data can never change the command.</p>

<h3>Cross-site scripting (XSS)</h3>
<p>XSS means an attacker gets their script to run in other people's browsers, for example through a comment field. The cure is to <strong>escape output</strong> every time you print user data into HTML:</p>
<pre>&lt;p&gt;Comment: &lt;?= htmlspecialchars($comment, ENT_QUOTES, 'UTF-8') ?&gt;&lt;/p&gt;</pre>
<p>A <strong>Content-Security-Policy</strong> header adds a second layer by telling the browser which script sources are allowed.</p>

<h3>CSRF</h3>
<p>Cross-site request forgery tricks a logged-in user's browser into sending a request they never intended, such as changing a clinic booking. Defend with a secret <strong>CSRF token</strong>: store a random value in the session, put it in a hidden form field, and reject any POST where it does not match.</p>
<pre>$_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(32));
// on submit:
if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
    http_response_code(403);
    exit('Invalid request');
}</pre>

<h3>Validating versus sanitising</h3>
<p><strong>Validation</strong> checks that input is acceptable and rejects it if not (a phone number has only digits, an age is between 0 and 120). <strong>Sanitising</strong> changes input to make it safe or tidy. Validate on the server first, always, because browser checks are easy to bypass. Escaping for the right place (SQL by prepared statements, HTML by htmlspecialchars) is what truly protects you.</p>

<h3>Password storage</h3>
<p>Never store passwords as plain text or with a fast hash such as MD5. Use PHP's built-in functions, which add a salt and are deliberately slow:</p>
<pre>$hash = password_hash($password, PASSWORD_DEFAULT);
// at login:
if (password_verify($password, $hash)) { /* success */ }</pre>

<h3>Protecting sessions</h3>
<ul>
<li>Call <code>session_regenerate_id(true)</code> right after login.</li>
<li>Set a sensible expiry and a real logout that destroys the session.</li>
<li>Limit login attempts to slow guessing.</li>
<li>Serve the whole site over HTTPS, which encrypts traffic between browser and server. A certificate proves your site's identity; many hosts offer free ones, so check your host.</li>
</ul>

<h3>Case scenario</h3>
<p>A small shop's order page built its search query by joining text. A curious visitor typed a quote mark and the page printed a database error showing table names. The owner, advised by a freelancer, rewrote all queries as prepared statements, turned off error display on the live site and logged errors privately. Nothing was stolen because it was caught early. The lesson: an error message that reveals your database is already a warning, and prepared statements remove the cause.</p>

<blockquote><p><strong>Quick check:</strong> Does <code>htmlspecialchars</code> protect against SQL injection?</p><p>Answer: No, because it escapes text for HTML output only; SQL needs prepared statements.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why is browser-side form validation not enough?</p><p>Answer: An attacker can skip the browser and send requests directly, so the server must validate too.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Find one place in your project where a database query joins text, and rewrite it as a prepared statement.</li>
<li>Find one place where user text is printed, and wrap it in htmlspecialchars.</li>
<li>Add a CSRF token to one form and test that a missing token is rejected.</li>
<li>Replace any plain password storage with password_hash and password_verify, using only test accounts.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Never trust input; validate on the server.</li>
<li>Prepared statements stop SQL injection; escaping output stops XSS.</li>
<li>CSRF tokens prove a form came from your own page.</li>
<li>Hash passwords with the built-in functions and regenerate session IDs at login.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Hardening, AI code review and the security report',
                    'minutes' => 26,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to manage secrets, secure file uploads, enforce access control, set security headers and cookie flags, review AI-generated code with a checklist, and write a security review report. This matters because a site is only as safe as its weakest forgotten corner, and AI tools can write code that works yet is unsafe.</p>

<h3>Secrets and environment variables</h3>
<p>Passwords, keys and database logins belong in environment variables or a config file kept outside the public web folder and outside Git. Keep different values for your computer and the live server. If a secret ever reaches a public place, treat it as stolen and replace it.</p>

<h3>Secure file uploads</h3>
<p>A school portal that accepts photos or result slips must assume files can be hostile.</p>
<ul>
<li>Allow only the types you need and check the real content type, not just the file name.</li>
<li>Set a size limit.</li>
<li>Rename the file to a random name and never use the visitor's name.</li>
<li>Store it outside the public folder, or where scripts cannot run, and serve it through a PHP page that checks permission.</li>
</ul>

<h3>Access control and permissions</h3>
<p>Hiding a button is not security. On every page and every action, the server must ask: is this person logged in, and are they allowed to touch this record? A parent must see only their own child's results. A common flaw is changing <code>?id=15</code> to <code>?id=16</code> and seeing someone else's data, so always compare the record's owner with the current user.</p>

<h3>Dependencies and backups</h3>
<p>Libraries and frameworks receive security fixes, so update them regularly and remove those you no longer use. Backups protect against attack, mistakes and hardware failure: keep database and uploads backed up on a schedule, store a copy away from the server, and practise a restore, because an untested backup is only a hope.</p>

<h3>Security headers and secure cookies</h3>
<table>
<thead><tr><th>Setting</th><th>Purpose</th></tr></thead>
<tbody>
<tr><td>X-Content-Type-Options: nosniff</td><td>Stops browsers guessing file types</td></tr>
<tr><td>Referrer-Policy</td><td>Limits what address information is shared with other sites</td></tr>
<tr><td>Content-Security-Policy</td><td>Restricts where scripts and styles may load from</td></tr>
<tr><td>Cookie flags HttpOnly, Secure, SameSite</td><td>Hide cookies from scripts, send only over HTTPS, limit cross-site sending</td></tr>
</tbody>
</table>
<pre>header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
session_set_cookie_params(['httponly' =&gt; true, 'secure' =&gt; true, 'samesite' =&gt; 'Lax']);</pre>

<h3>Reviewing AI-generated code</h3>
<p>Claude Code or another assistant can write fast, but it may skip protections unless you ask. Read what it proposes before approving, and ask it to explain risky parts. Use this checklist:</p>
<pre>[ ] Every database query uses prepared statements
[ ] Every printed user value is escaped
[ ] Forms that change data have CSRF tokens
[ ] Passwords use password_hash, never plain or MD5
[ ] No keys or passwords written in the code
[ ] Uploads checked for type, size and renamed
[ ] Each action checks login AND ownership
[ ] Errors are logged, not shown to visitors
[ ] No unexplained new packages
[ ] I can explain what each changed file does</pre>

<h3>Testing before public release</h3>
<p>Before launch, run your checklist on a staging copy, try wrong inputs, test as a user without permissions, confirm HTTPS works, error display is off and backups run. Only test applications you own or have written permission to test. Probing someone else's site without permission is illegal and unethical.</p>

<h3>Security review report template</h3>
<pre>SECURITY REVIEW REPORT
Application:        ___________   Date: ______   Reviewer: ______
Scope and permission: ____________________________
Finding 1: ______________________________
  Where:       page / file / form
  Risk level:  High / Medium / Low
  Evidence:    what you saw (no real data)
  Fix:         recommended change
  Status:      Open / Fixed / Retested
Summary: what is most urgent, and what to do first</pre>

<h3>Case scenario</h3>
<p>A clinic site let patients download their lab result through a link ending in a number. A staff member noticed that changing the number showed another patient's result. The cause was code, partly AI-written, that checked login but not ownership. The team added an owner check, logged access attempts and informed management as policy required. Nobody had exploited it, but it could have been serious. The lesson: login is not permission, so always check the owner.</p>

<blockquote><p><strong>Quick check:</strong> Why rename uploaded files to random names?</p><p>Answer: It prevents overwriting, hides hostile file names and stops attackers from guessing file addresses.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You may test a friend's website for weaknesses if it is only for learning. True or false?</p><p>Answer: False, because you need the owner's written permission; testing without it can be illegal.</p></blockquote>

<h3>Try it yourself</h3>
<p>Assignment: perform a security review of a sample web application and write a report of weaknesses and fixes.</p>
<ol>
<li>Choose an app you own, such as your own practice project, or a deliberately vulnerable sample made for learning.</li>
<li>Walk through the AI code checklist against its files and forms.</li>
<li>Record at least five findings in the report template, each with risk level and fix.</li>
<li>Fix the top two findings and retest them.</li>
<li>Finish with a one-paragraph summary of what you would do first.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Keep secrets out of code and out of Git.</li>
<li>Uploads and access control need server-side checks every time.</li>
<li>Headers and secure cookie flags are cheap extra layers.</li>
<li>Review AI code with a checklist and report findings in writing.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A developer's JavaScript file for a farmers' market page contains the SMS service key so the browser can send messages directly. What is the main problem?
* Anyone who opens the page can read and misuse the key
- JavaScript cannot send messages
- The key will expire faster
- The page will load more slowly
= Browser code is visible to every visitor, so secrets must stay in server-side code and be called from there.

Q: Which status code family means the other server failed and your code should fail gracefully?
- 200 to 299
- 400 to 499
* 500 to 599
- 100 to 199
= 5xx codes mean a server-side failure; your page should show a fallback message and log the detail.

Q: A customer lands on your shop's thank-you page after hosted checkout. A teacher asks what proves the order is paid. What is the best answer?
- The customer arriving on the thank-you page
- A message in the customer's browser saying "paid"
* A server-to-server confirmation or a verified webhook from the provider
- The customer saying they paid
= The return address can be typed by anyone; only a confirmation from the provider's side is reliable.

Q: Your webhook receives the same "payment complete" event twice and the order is credited twice. Which design idea was missing?
- Hosted checkout
* Idempotency
- Output escaping
- A longer timeout
= An idempotent handler recognises an event it has already processed and does nothing the second time.

Q: A school portal builds a search query by joining the visitor's text into the SQL string. What is the correct fix?
- Wrap the text in htmlspecialchars
- Add a JavaScript check to the form
* Use a prepared statement with bound parameters
- Hide the search box from strangers
= Prepared statements keep data separate from the command, so input cannot change the query.

Q: A parent changes a number in the address bar and sees another child's results. Which weakness is this, and what fixes it?
- XSS, fixed by a Content-Security-Policy
* Broken access control, fixed by checking record ownership on the server
- CSRF, fixed by a token
- Weak hashing, fixed by password_hash
= Being logged in is not enough; the server must confirm the record belongs to the current user.

Q: Which are good practices for storing user passwords? (choose all that apply)
* Use password_hash
- Store them encrypted with MD5
* Verify with password_verify
- Keep a plain copy for support staff
= The built-in functions use salted, slow hashing; plain copies and fast hashes make a breach far worse.

Q: A freelancer offers to "test the security" of a neighbour's shop website to practise, without asking the owner. What should you advise?
- It is fine if no data is changed
* Get the owner's written permission first, or practise on your own project
- It is fine if done at night
- It is fine if the results are not shared
= Testing systems you do not own or have permission to test can be illegal, whatever your intention.
QUIZ,
        ],
        // ---------------------------------------------------------------- STAGE 7
        [
            'title'   => 'Stage 7: Testing and Deployment',
            'summary' => 'Test and debug a web application with a plan, browser tools and careful use of Claude Code, then deploy it to a working public URL and keep it healthy.',
            'lessons' => [
                [
                    'title'   => 'Testing and debugging: plans, developer tools and errors',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to write a manual test plan, use browser developer tools to find problems, and trace PHP and database errors to their cause. This matters because a bug found by you costs minutes, while a bug found by a parent paying school fees online costs trust.</p>

<h3>Software testing and manual testing</h3>
<p><strong>Software testing</strong> means checking that a program does what it should, and does not do what it should not. <strong>Manual testing</strong> means a person uses the site like a real visitor and records what happens. Start with a <strong>test plan</strong>: a list of test cases, each with steps, an expected result and an actual result.</p>
<pre>ID  | Feature       | Steps                          | Expected              | Actual | Pass?
T01 | Booking form  | Submit with empty phone        | Message: phone needed |        |
T02 | Booking form  | Submit valid details           | Saved, thank-you page |        |
T03 | Login         | Wrong password                 | Error, no login       |        |</pre>

<h3>Browser developer tools</h3>
<p>Open them with F12 (or right-click and choose Inspect). Look for these tabs, whose names can differ slightly between browsers:</p>
<ul>
<li><strong>Elements:</strong> look for the live HTML and the CSS rules applied to a chosen element; crossed-out rules were overridden.</li>
<li><strong>Console:</strong> look for red error lines with a file name and line number.</li>
<li><strong>Network:</strong> look for requests shown in red or with status 404 (not found) or 500 (server error); click one to see the response.</li>
<li><strong>Responsive mode:</strong> look for a phone or tablet icon that lets you pick a screen size.</li>
</ul>

<h3>Debugging HTML, CSS and JavaScript</h3>
<p>For HTML, an unclosed tag often shifts the whole layout, so inspect the element and check its parent. For CSS, select the element in Elements and read which rule wins. For JavaScript, read the Console message first: "Cannot read properties of null" usually means the script looked for an element that does not exist yet or has a different id.</p>

<h3>Debugging PHP errors</h3>
<p>On your computer, show errors so you can fix them. On the live server, <strong>display_errors must be off</strong>, because error text can reveal file paths and database details to strangers. Log the errors instead and read the log.</p>
<pre>// local development only
ini_set('display_errors', '1');
error_reporting(E_ALL);

// production: hide from visitors, keep a record
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_log('Booking save failed for clinic form');</pre>
<p>In XAMPP, look for the Apache and PHP error logs through the Logs button of the control panel. A blank page often means a fatal error that was hidden.</p>

<h3>Database connection failures</h3>
<p>The usual causes, in the order to check them: MySQL is not running in the XAMPP control panel; the wrong host (often localhost); the wrong port; a wrong username or password; or the database name does not exist. Read the exact message, since "Access denied" points at credentials while "Connection refused" points at the server or port.</p>

<h3>Testing forms, registration and login</h3>
<p>Test every form with valid data, empty data, very long text, text with quotes such as O'Brien, and a wrong email format. For registration, try a duplicate email. For login, try a wrong password, a correct one, logging out and pressing the Back button. Confirm that the server rejects bad data even when browser checks are bypassed.</p>

<h3>Worked example: a clinic booking site</h3>
<p>A clinic in Jinja tests its booking form. Case T01 passes. Case T04, a patient named "D'Souza", gives a blank page. The Console is clean, so the developer checks the PHP error log and finds an SQL syntax error caused by the apostrophe, which shows the query was built by joining strings instead of using a prepared statement. Fixing it and rerunning T01 to T04 proves the repair.</p>

<h3>Case scenario</h3>
<p>A school launched its admissions page with debug mode on. A parent sent a form with an odd character and saw a long error that exposed the database username and the server folder path. The developer switched display_errors off, changed the database password, and moved errors to a log file. The lesson: errors are for developers, never for visitors.</p>

<blockquote><p><strong>Quick check:</strong> Which developer tools tab shows a failed request with status 404?</p><p>Answer: The Network tab, because it lists every request the page makes with its status code.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why must display_errors be off on a live site?</p><p>Answer: Error messages can reveal file paths and database details, which helps attackers.</p></blockquote>

<h3>Defect log template</h3>
<pre>Defect ID: D01
Page: booking.php
Found by / date: Amina / 12 March
Steps: 1) open booking page 2) enter name D'Souza 3) submit
Expected: saved message     Actual: blank page
Severity: High              Status: Open / Fixed / Retested</pre>

<h3>Try it yourself</h3>
<ol>
<li>Pick one form from a site you built and write six test cases in a table like the one above.</li>
<li>Run each case by hand and fill in the Actual column.</li>
<li>Open the Console and Network tabs while you test and note any red lines.</li>
<li>Stop MySQL in XAMPP, load a page that uses the database, and read the error you get; then start it again.</li>
<li>Record one defect in the defect log format.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A test plan lists steps, expected results and actual results.</li>
<li>Elements, Console and Network each answer different questions.</li>
<li>Show errors locally, hide and log them in production.</li>
<li>Test forms with bad, empty and unusual data, not only good data.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Quality assurance: roles, devices, automation and the release checklist',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to test roles and permissions, check a site across browsers and screens, write a tiny automated test, use Claude Code to investigate an error, and finish with a release checklist and test report. This matters because the last checks before launch are the ones that catch embarrassing, expensive mistakes.</p>

<h3>Testing roles and permissions</h3>
<p>If your system has a visitor, a member and an administrator, test each role against each page. Do not trust hidden buttons: type the admin address directly while logged out, or while logged in as an ordinary member. The server must refuse, usually with a redirect to login or an access-denied page.</p>
<table>
<thead><tr><th>Page</th><th>Visitor</th><th>Member</th><th>Admin</th></tr></thead>
<tbody>
<tr><td>/dashboard.php</td><td>Redirect to login</td><td>Allowed</td><td>Allowed</td></tr>
<tr><td>/admin/users.php</td><td>Redirect to login</td><td>Access denied</td><td>Allowed</td></tr>
</tbody>
</table>

<h3>Cross-browser and responsive testing</h3>
<p>Test at least two browsers on a computer (for example Chrome and Firefox) and one real phone, since many visitors in Uganda use phones with smaller screens and slower data. In responsive mode check widths around 360, 768 and 1200 pixels. Look for text that is cut off, buttons too small to tap, menus that do not open and sideways scrolling.</p>

<h3>Unit and integration testing, and automated tests</h3>
<p>A <strong>unit test</strong> checks one small function. An <strong>integration test</strong> checks that parts work together, for example a form saving a row in the database. Tools such as PHPUnit exist for PHP; follow their current documentation to set them up. The idea works even with plain assertions:</p>
<pre>function totalFee(int $tuition, int $transport): int
{
    return $tuition + $transport;
}

function assertSame($expected, $actual, string $name): void
{
    echo ($expected === $actual ? 'PASS ' : 'FAIL ') . $name . "\n";
}

assertSame(750000, totalFee(600000, 150000), 'fee with transport');
assertSame(600000, totalFee(600000, 0), 'fee without transport');</pre>
<p>Automate tests where the logic is important and stable, such as fee calculations or validation rules. Layout and wording are usually quicker to check by eye.</p>

<h3>Performance and accessibility checks</h3>
<p>For performance, use the Network tab to look for very large images and many requests, and load the site on a slow connection setting. For accessibility, check that every image has useful alt text, every form field has a label, text has strong contrast, and the whole page can be used with the Tab key only.</p>

<h3>Using Claude Code to investigate errors</h3>
<p>Claude Code is Anthropic's AI coding assistant that runs in the terminal inside your project folder. It can read project files, propose edits and run commands, and it asks permission first. Follow the current official documentation to install it. Without it, use another AI assistant or do these steps by hand. Never paste passwords, keys or real personal data into any AI tool.</p>
<ol>
<li>Give it the exact error message, copied in full.</li>
<li>Name the relevant file and line.</li>
<li>Say what you expected to happen.</li>
<li>Ask for the <strong>cause before the fix</strong>, and judge whether the explanation makes sense.</li>
<li>Review the proposed change, apply it, and run the same test again.</li>
</ol>

<h3>Release checklist template</h3>
<pre>[ ] All test cases pass; open defects are Low only
[ ] Tested in two browsers and on one phone
[ ] Roles tested: visitor, member, admin
[ ] display_errors off; test accounts and sample data removed
[ ] Database backup taken and stored safely
[ ] Config values set for production; no secrets in the code
[ ] Rollback plan written down</pre>

<h3>Test report template</h3>
<pre>Project: Shop orders system        Tester: Moses      Date: 20 April
Cases run: 42   Passed: 39   Failed: 3   Fixed and retested: 3
Open defects: none High; one Low (button wording)
Browsers/devices: Chrome, Firefox, Android phone
Recommendation: Ready to release / Not ready, because ...</pre>

<h3>Case scenario</h3>
<p>A shop owner in Mbarara asked for last-minute changes and the developer skipped testing. After launch, members could open the admin orders page by typing its address. A customer noticed and warned the owner. The developer added a server-side role check, tested each role against each page, and wrote the table above into the release checklist. The lesson: test permissions from the outside, as an unwelcome visitor would.</p>

<blockquote><p><strong>Quick check:</strong> What should you give Claude Code first when a page shows an error?</p><p>Answer: The exact error message plus the relevant file and what you expected, because specific context leads to an accurate diagnosis.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Is it enough to hide the Admin menu from members?</p><p>Answer: No, because they can still type the address; the server must check the role.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a web application you built (a registration system or booking site).</li>
<li>Write at least 15 test cases covering forms, login, roles and a phone-sized screen.</li>
<li>Run them, record every failure in a defect log, and fix at least three defects.</li>
<li>Retest the fixed cases and complete the test report template with real numbers.</li>
<li>Add one plain assertion test for a calculation or validation function.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Test every role against every protected page, using direct addresses.</li>
<li>Check more than one browser and a real phone.</li>
<li>Unit tests check small functions; integration tests check parts together.</li>
<li>With AI help, share the error, file and expectation, ask for the cause first, then rerun the test.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'From localhost to the internet: hosting, domains and preparing your project',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to explain the difference between local and production, choose a hosting type, understand domains and DNS, and prepare a project for deployment. This matters because many beginners finish a site that runs well on their laptop and then lose days when it fails on a real server.</p>

<h3>Local development versus production</h3>
<p><strong>Local</strong> means your own computer, with XAMPP, debug messages and test data. <strong>Production</strong> means the live server the public uses, where errors must be hidden, data is real and the site must stay online. They differ in operating system (often Windows locally, Linux online), PHP settings, paths, database credentials and security.</p>

<h3>Hosting and server environments</h3>
<table>
<thead><tr><th>Type</th><th>What it is</th><th>Good for</th></tr></thead>
<tbody>
<tr><td>Static host</td><td>Serves HTML, CSS, JavaScript and images only</td><td>Portfolios, simple business pages</td></tr>
<tr><td>Shared hosting</td><td>Many sites share one server; PHP and MySQL included</td><td>School, clinic and shop sites with moderate traffic</td></tr>
<tr><td>VPS</td><td>Your own virtual server that you set up and secure yourself</td><td>Larger apps and developers with server skills</td></tr>
</tbody>
</table>
<blockquote><p><strong>Important:</strong> GitHub Pages is for static sites. It does not run PHP and it does not host a MySQL database. A PHP and MySQL application needs PHP-compatible hosting, such as Hostinger, which is one example named by the course owner.</p></blockquote>

<h3>Domain names and DNS</h3>
<p>A <strong>domain name</strong> such as examplehospital.org is the friendly address people type. <strong>DNS</strong> is the phone book that turns that name into the server's address. Two records matter most. An <strong>A record</strong> points a name to a server's numeric IP address. A <strong>CNAME</strong> points one name to another name, for example www to the main domain. After you change a record, <strong>propagation</strong> is the waiting time, from minutes to a day or more, while DNS servers around the world refresh their memory. If your site seems unchanged, wait before panicking.</p>

<h3>Choosing a hosting provider</h3>
<p>Compare on criteria, not on advertising: does it support the PHP version and MySQL your project needs; is there a control panel with File Manager, database tools and SSL; are backups offered; what are the storage and traffic limits; how reachable is support; how fast is the site for visitors in East Africa; and is renewal clear? Read the terms before you pay.</p>

<h3>Preparing a project for deployment</h3>
<ol>
<li>Move database host, name, user and password into one config file or environment settings, never scattered through pages.</li>
<li>Turn debug mode off and log errors instead.</li>
<li>Delete test accounts, sample data and leftover files such as old copies.</li>
<li>Check every file path and link; make them relative or built from a base setting.</li>
<li>Add a basic .htaccess only when you understand each line.</li>
</ol>
<pre>// config.php (kept out of Git; a config.example.php holds fake values)
return [
    'db_host' =&gt; 'localhost',
    'db_name' =&gt; 'school_site',
    'db_user' =&gt; 'school_user',
    'db_pass' =&gt; 'SET-ON-SERVER',
    'debug'   =&gt; false,
];</pre>
<p>A simple .htaccess can block directory listings and set a friendlier error page. Hosts differ, so test after each change.</p>

<h3>Publishing static sites</h3>
<p>For a pure HTML, CSS and JavaScript portfolio, a static host such as GitHub Pages is enough: push the files to a repository and enable Pages in the repository settings (look for a Pages section). Follow its current documentation for the exact steps.</p>

<h3>Deploying PHP and MySQL, and migrating the database</h3>
<p>On shared hosting, the usual order is: create the database and a database user in the host panel (look for MySQL Databases or similar), give that user access to the database, export your local database as an SQL file from phpMyAdmin, then import it into the new database. Host names, prefixes and usernames are often different from local, so update your config afterwards.</p>

<h3>Case scenario</h3>
<p>A developer built a school website locally with XAMPP. After upload, the site showed broken images and a missing stylesheet. Her file was named Logo.png but the page asked for logo.png. Windows ignores case, and the Linux server does not. She also found the database import had failed silently because the SQL file was too big for one upload. She fixed the names, split the import, and now checks name case before every upload. The lesson: a local success is not a production success.</p>

<blockquote><p><strong>Quick check:</strong> Can GitHub Pages host a PHP and MySQL application?</p><p>Answer: No, because it serves static files only and runs no PHP or database.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What does a CNAME record do?</p><p>Answer: It points one domain name to another name, such as www to the main domain.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List five differences between your local setup and a live server.</li>
<li>Decide which hosting type fits a school website with a booking form, and write two reasons.</li>
<li>Move your database settings into one config file and add a config.example.php with fake values.</li>
<li>Export your local database to an SQL file and open it in a text editor to see the tables.</li>
<li>Search your files for names that differ only by capital letters from the links that use them.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Production is real: debug off, real data, Linux rules.</li>
<li>Choose hosting by what your project needs, not by promotions.</li>
<li>DNS changes take time to propagate.</li>
<li>GitHub Pages is static only; PHP and MySQL need PHP-capable hosting.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Going live: upload, HTTPS, post-launch checks and maintenance',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to upload a project, secure it with HTTPS, test it after launch, fix common deployment errors and keep it backed up and updated. This matters because going live is the start of responsibility, not the end of the work.</p>

<h3>Uploading files and deployment workflows</h3>
<p>There are three common routes. <strong>File Manager</strong> in the host panel lets you upload a zip file and extract it in the browser. <strong>FTP</strong> uses a program such as FileZilla with the details your host gives you; prefer the secure variant if the host offers it. <strong>Git</strong> lets you pull the latest code from your repository on the server when the host supports it, which leaves a clear history and an easy way back. Upload into the folder your host serves for the domain (look for names like public_html), and keep secrets out of the repository.</p>

<h3>Configuring the database connection securely</h3>
<p>Create the config file on the server with the production values. Where possible keep it <strong>outside the web root</strong>, the folder the public can reach, so that a visitor cannot request it by address. If your host lets you set environment variables, use them; otherwise a config file outside public_html that your code includes is a good choice. Folder permissions should be as tight as the site allows: files readable by the server, only upload folders writable, and nothing set to full access for everyone. Check your host's guidance for exact numbers.</p>

<h3>SSL and HTTPS</h3>
<p>HTTPS encrypts data between the visitor and the server, so passwords and forms cannot be read on the way. Many hosts offer a free or included certificate; look for an SSL or Security section. After it is active, make the site use https links and redirect the old http address, then check that no images or scripts are still loaded over http.</p>

<h3>Testing after deployment</h3>
<pre>[ ] Home page loads over https, no browser warning
[ ] Every menu link and image works (check capital letters in names)
[ ] Contact and booking forms save data in the live database
[ ] Registration, login and logout work; admin pages refuse ordinary users
[ ] Errors are hidden from visitors; the log is being written
[ ] Phone test on mobile data
[ ] Test records removed afterwards</pre>

<h3>Troubleshooting deployment errors</h3>
<table>
<thead><tr><th>Symptom</th><th>Likely causes to check</th></tr></thead>
<tbody>
<tr><td>500 Internal Server Error</td><td>Bad .htaccess line, PHP error, wrong permissions; read the server error log</td></tr>
<tr><td>Blank white page</td><td>A hidden fatal error; check the log, then the PHP version</td></tr>
<tr><td>Missing images, CSS or pages</td><td>Wrong paths or file names with different capital letters (Linux is case-sensitive)</td></tr>
<tr><td>Call to undefined function</td><td>A PHP extension is not enabled; look for PHP settings in the panel</td></tr>
<tr><td>Database connection error</td><td>Wrong host, name, user or password; user not linked to the database</td></tr>
</tbody>
</table>
<p>Change one thing at a time and retest, otherwise you will not know what fixed it.</p>

<h3>Backups and ongoing maintenance</h3>
<p>Back up both the files and the database, on a schedule, and store a copy away from the server. Test a restore at least once; a backup you have never restored is only a hope. Keep PHP, libraries and any CMS updated, remove unused accounts, review logs, renew the domain and certificate before they expire, and retest after every update.</p>

<h3>Deployment checklist template</h3>
<pre>Site: ______  Host: ______  Date: ______
[ ] Backup of local files and SQL export made
[ ] Database and user created; SQL imported
[ ] Files uploaded to the correct folder
[ ] Config set; kept outside web root where possible
[ ] Debug off; test data removed
[ ] HTTPS active and forced
[ ] Post-launch tests done; defects logged
[ ] Backup schedule set; next maintenance date: ______</pre>

<h3>Case scenario</h3>
<p>A developer in Kampala launched a shop site on shared hosting and left debug mode on "to help fix small things". Two days later the first visitor who mistyped a product link saw a warning with the full server path and the database name. The owner noticed it on a customer's phone screenshot. The developer turned debug off, rotated the database password, and added a post-launch test step to check an invalid link. The lesson: test what happens when things go wrong, not only when they go right.</p>

<blockquote><p><strong>Quick check:</strong> A page works locally but images are missing on the live Linux server. What do you check first?</p><p>Answer: File name capitalisation, because Linux treats Logo.png and logo.png as different files.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why keep the config file outside the web root?</p><p>Answer: Visitors cannot request it by address, so the database password stays private.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a finished website; decide whether it is static or needs PHP and MySQL.</li>
<li>Deploy it to a suitable host, such as GitHub Pages for a static site or PHP-compatible hosting for PHP and MySQL, following the host's current documentation.</li>
<li>Confirm the public URL loads over https and run the post-deployment checklist.</li>
<li>Take a backup of files and database, then write down how you would restore it.</li>
<li>Write a short deployment note: host, date, URL, steps used and one problem you solved.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Upload by File Manager, FTP or Git, into the correct folder.</li>
<li>Keep configuration and secrets outside the web root and out of Git.</li>
<li>Turn on HTTPS, then test the live site, including error cases.</li>
<li>Back up regularly, test restores and keep everything updated.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A clinic booking form shows a blank page only when a patient's name contains an apostrophe. The Console is clean. Where should you look first?
- The Elements tab for a missing CSS rule
* The PHP error log, because a server-side error is likely hidden
- The domain's DNS records
- The phone's screen size settings
= A blank page with a clean browser Console usually means a hidden PHP error, such as an SQL syntax error, so read the server log.

Q: Which line best describes a good test case?
- A general wish such as "make the form work"
- The final pass or fail mark only
* Steps to follow, an expected result and a recorded actual result
- A screenshot of the finished page
= A test case must be repeatable: steps, expected outcome and what really happened.

Q: Your PHP page cannot reach the database on XAMPP and the message says "Connection refused". What is the most likely first thing to check?
- The CSS file path
- The browser version
* Whether MySQL is started in the XAMPP control panel
- The HTML doctype
= Connection refused points to the server not running or a wrong port, so start with the control panel.

Q: A member types the address of an admin page and sees the full admin dashboard, although the menu hides the link. What does this show?
- The browser cache is old
* The server does not check the user's role, so hiding the link is not protection
- The page needs a bigger font
- Responsive mode is switched on
= Access control must be enforced on the server. A hidden link only hides the door.

Q: A learner's site works on XAMPP on Windows but images are missing on a Linux host. The file is Logo.png and the page asks for logo.png. What is the explanation?
- Linux hosts block all images
- The image is too large
* Linux file names are case-sensitive, so the names must match exactly
- DNS has not propagated yet
= Windows ignores capitals in file names while most Linux servers do not.

Q: Which statement about GitHub Pages is correct?
- It runs PHP and also provides MySQL
* It serves static sites and does not run PHP or host MySQL
- It is a domain registrar
- It replaces the need for any testing
= The owner's note stands: PHP and MySQL applications need PHP-compatible hosting.

Q: After deploying, the site shows a 500 error. Which action is the sensible first step?
- Delete all files and upload again at random
- Wait a month for DNS
* Read the server error log and check recent changes such as .htaccess and permissions
- Turn display_errors on for all visitors
= The log usually names the cause. Showing errors to the public is unsafe.

Q: You ask an AI assistant to help with a PHP error. Which request gives the best result? (choose all that apply)
* Paste the exact error message and name the file
* Say what you expected to happen
- Include the real database password so it can test
* Ask for the cause before the fix and then rerun the test
= Give precise context, never secrets, ask why before how, and verify with the same test.
QUIZ,
        ],
        // ---------------------------------------------------------------- STAGE 8
        [
            'title'   => 'Stage 8: Professional Practice and Final Projects',
            'summary' => 'Learn to plan, review, document and hand over AI-assisted software like a professional, then complete, demonstrate and present a final web application.',
            'lessons' => [
                [
                    'title'   => 'Working with AI professionally: requirements, review and Git',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to write clear requirements, break a project into small tasks, plan before you generate code, review what an AI assistant produces, catch invented functions and packages, and keep every change safe in Git. This matters because an AI assistant writes code very fast, so your value as a developer is in deciding what to build and checking that it is right.</p>

<h3>Requirements and task breakdown</h3>
<p>A <strong>requirement</strong> is a clear statement of what the software must do. "Make a nice shop site" is a wish; "A customer can add an item to a cart and see the total in UGX" is a requirement you can test. Write each as who, what and how you will know it works. Then split the project into tasks you can finish and check in one sitting, such as one table or one form.</p>

<h3>Plan before you generate</h3>
<p>Claude Code is Anthropic's AI coding assistant that runs in the terminal inside your project folder. It can read project files, propose edits and run commands, and it asks permission before doing so. Before it changes anything, ask it for a plan: which files it will touch, in what order, and what could go wrong. Read the plan, correct it, then approve one step at a time. Follow the current official documentation, because features change.</p>

<h3>Reviewing AI-generated code</h3>
<p>Treat AI code like a pull request from a new colleague: useful, but unproven. Read every change before accepting it. Ask: does it meet the requirement, handle wrong input, use prepared statements and keep secrets out? Then run it and test.</p>

<h3>Hallucinations: invented functions, packages and options</h3>
<p>A <strong>hallucination</strong> is a confident answer that is false. In code it appears as a function that does not exist, a library option that was never offered, or a package name that sounds real but is not. How to catch them:</p>
<ul>
<li>Run the code; an invented function fails at once.</li>
<li>Look up every unfamiliar function or option in the official documentation.</li>
<li>Check any package name on the official registry before installing it.</li>
</ul>

<h3>Dependencies, refactoring, documentation and Git</h3>
<p>A <strong>dependency</strong> is code written by someone else that your project uses. Before adding one, check that it exists, is spelled exactly right, is still maintained, and does a job you cannot do simply yourself. Attackers publish look-alike package names; this is a supply-chain risk. <strong>Refactoring</strong> means improving the structure of code without changing what it does; do it in small steps and test after each. Useful documentation says what the project is, how to run it and why. In Git, commit before asking the AI to change anything, so you can undo it.</p>

<h3>Template: a requirement card</h3>
<pre>
Requirement: R-04
As a: shop owner (logged in)
I want to: mark an order as paid
So that: I know which deliveries to make
Done when: the status changes, the date is saved,
           and a customer cannot do this action
Test: log in as owner and customer, try both
</pre>

<h3>Worked example: a safe change loop</h3>
<ol>
<li>Commit unfinished work, then create a branch: <code>git switch -c add-order-status</code>.</li>
<li>Ask Claude Code for a plan only; correct it.</li>
<li>Approve one task; read the changes it proposes.</li>
<li>Run the page, test the requirement, then <code>git add</code> and <code>git commit</code> with a clear message.</li>
<li>Repeat for the next task.</li>
</ol>

<h3>Case scenario</h3>
<p>Brian, a student in Jinja, asked an AI assistant for a package to send SMS from his PHP project. It named a package and he installed it without checking. The name was one letter different from the real library; it did not work, and his security tool warned that it was suspicious. Brian checked the official registry and installed the right package. The lesson: verify a package's exact name, maintainer and activity before installing anything an AI suggests.</p>

<blockquote><p><strong>Quick check:</strong> An AI suggests a function you have never seen. What should you do before using it?</p><p>Answer: Check the official documentation, because the function may be invented and fail or behave differently.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why commit to Git before asking the AI for a big change?</p><p>Answer: So you have a clean checkpoint to return to if the change breaks something.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick a small project and write three requirement cards using the template.</li>
<li>Plan task one (with an AI assistant or by hand) and list the files it changes.</li>
<li>Find one unfamiliar function or package in any code you use and verify it in the official documentation or registry.</li>
<li>Complete task one on a Git branch, test it and commit.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Testable requirements and small tasks keep AI work under your control.</li>
<li>Ask for a plan first, then approve one step at a time.</li>
<li>Review and test all generated code.</li>
<li>Verify every package's name, maintenance and purpose before installing.</li>
<li>Commit before and after each change so you can undo mistakes.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Professional practice: codebases, confidentiality, estimates and handover',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to explore an unfamiliar codebase, handle licences and confidential data carefully, work with clients, estimate time and cost in UGX, build a portfolio and hand over a project. This matters because clients pay for reliable, honest professionals, and one careless paste of private data or one wrong estimate can end a working relationship.</p>

<h3>Unfamiliar codebases</h3>
<p>Most real jobs begin with code someone else wrote. Change nothing first. Read the README, find the entry point, run the project, and follow one feature from page to database. An AI assistant can summarise folders and explain functions, but confirm what it says by running the code and reading the files yourself.</p>

<h3>Licensing and intellectual property</h3>
<p>This is general education, not legal advice. Open-source code comes with a <strong>licence</strong> that sets conditions for using, changing and sharing it; some are permissive, others require you to share your changes. Always check the licence of a library before using it in a client project. Who owns AI-assisted code depends on the law of your country, your contract with the client and the terms of the tool you use, so read all three, and write ownership of the final work into your agreement.</p>

<h3>Protecting confidential data</h3>
<p>Never paste passwords, API keys, real customer records or confidential client files into any AI tool. Use fake sample data that has the same shape. Keep secrets in environment files that are listed in <code>.gitignore</code>. Check your tool's data settings and your client's rules. If a secret is exposed, change it immediately and tell the client.</p>

<h3>Working with developers and clients</h3>
<p>Agree what will be built in writing, show progress often, and ask when something is unclear. With developers, use branches, short pull requests and review. With clients, avoid jargon, report problems early and confirm changes in writing.</p>

<h3>Estimating time and cost: a worked UGX example</h3>
<p>A small business website with a contact form and a simple admin page:</p>
<table>
<thead><tr><th>Task</th><th>Hours</th></tr></thead>
<tbody>
<tr><td>Requirements and meetings</td><td>6</td></tr>
<tr><td>Design</td><td>10</td></tr>
<tr><td>Build</td><td>28</td></tr>
<tr><td>Testing</td><td>8</td></tr>
<tr><td>Deployment</td><td>4</td></tr>
<tr><td>Handover and training</td><td>4</td></tr>
<tr><td>Total</td><td>60</td></tr>
</tbody>
</table>
<p>Add a 25% buffer for changes and surprises: 60 × 1.25 = 75 hours. At UGX 25,000 per hour the labour cost is 75 × 25,000 = UGX 1,875,000. Domain and hosting for the first year, estimated at UGX 300,000, are listed separately, so the client sees a total of UGX 2,175,000. AI may speed the build, but reviewing and testing still take time.</p>

<h3>Portfolio, handover and maintenance</h3>
<p>A portfolio shows two or three finished projects, each with a screenshot, a short problem statement, the tools used, a link to the GitHub repository and a live URL where possible. Remove client secrets and private data first, and ask permission before showing client work. At handover give the client the code, the documentation, the list of accounts and how to back up. Agree maintenance in writing: who fixes bugs, who updates dependencies, and what it costs.</p>

<h3>AI is an assistant, not a substitute</h3>
<p>If you cannot explain a piece of code, you cannot maintain it or defend it. Use AI to speed up, to explain and to suggest, but keep learning the fundamentals so you can judge its answers.</p>

<h3>Template: handover checklist</h3>
<pre>
[ ] Source code in the client's own repository
[ ] README: how to install and run
[ ] Database export and how to restore it
[ ] List of accounts (owned by the client)
[ ] Test report and known issues
[ ] Maintenance terms signed
</pre>

<h3>Case scenario</h3>
<p>Agnes, a freelancer in Mbarara, quoted UGX 800,000 for a school fee portal without a buffer or testing time. Midway the school added three new reports. To finish faster she pasted a spreadsheet of real pupils' names and phone numbers into an AI tool for sample data. The school found out and she had to apologise and work unpaid for weeks. The lesson: estimate with a buffer and written scope, and use invented sample data, never real client records.</p>

<blockquote><p><strong>Quick check:</strong> Your estimate is 40 hours and you add a 25% buffer. How many hours do you quote?</p><p>Answer: 50 hours, because 40 × 1.25 = 50.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> May you paste a client's customer list into an AI tool to get help?</p><p>Answer: No, because it is confidential data; use invented sample data of the same shape instead.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open any project you did not write and write a five-line summary: purpose, entry point, folders, database, how to run it.</li>
<li>Find the licence of one library you use and write what it requires in one sentence.</li>
<li>Estimate a small project with the table above, add a 25% buffer and calculate the UGX cost with your own hourly rate.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Explore unfamiliar code by reading and running it before changing it.</li>
<li>Check licences and ownership terms; they are not the same everywhere.</li>
<li>Keep confidential data and secrets out of AI tools.</li>
<li>Estimate by tasks, add a buffer and write the scope down.</li>
<li>Plan handover and maintenance; understand what AI writes for you.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Final projects part 1: choosing, planning and collecting evidence',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to choose a final project that matches your skill, write a project plan with weekly milestones, collect the evidence assessors need, and write a requirements and architecture document. This matters because you must complete at least one working project before earning a certificate, and a good plan is what turns an ambitious idea into a finished one.</p>

<h3>The eight project options</h3>
<table>
<thead><tr><th>Project</th><th>Main skills</th><th>Difficulty</th></tr></thead>
<tbody>
<tr><td>Personal Portfolio Website</td><td>HTML, CSS, JavaScript, responsive design</td><td>Beginner</td></tr>
<tr><td>Business Website</td><td>Web design, contact forms, content management</td><td>Beginner</td></tr>
<tr><td>Blog Platform</td><td>PHP, MySQL, CRUD</td><td>Intermediate</td></tr>
<tr><td>Student Management System</td><td>Database design, authentication, dashboards</td><td>Intermediate</td></tr>
<tr><td>Inventory Management System</td><td>PHP, MySQL, reports, access control</td><td>Intermediate</td></tr>
<tr><td>Online Academy</td><td>Accounts, course management, progress tracking</td><td>Intermediate to advanced</td></tr>
<tr><td>E-Commerce Website</td><td>Catalogues, carts, orders, payment integration</td><td>Advanced</td></tr>
<tr><td>Loan Management System</td><td>Financial records, roles, transactions, reports, audit trails</td><td>Advanced</td></tr>
</tbody>
</table>

<h3>How to choose</h3>
<p>Choose the project you can finish well, not the most impressive one. If you are comfortable with HTML and CSS but new to databases, pick a beginner option. If you have built forms that save to MySQL, pick an intermediate one. Choose an advanced project only if you already handle login, permissions and money-related data safely. A finished, tested blog beats an unfinished loan system. You may add one extra feature later, but only after the core works.</p>

<h3>Project plan template</h3>
<pre>
Project name:
Who will use it:
Problem it solves (2 sentences):
Core features (maximum 5):
Out of scope (what I will NOT build):
Tools: (language, database, hosting)
Risks and how I will reduce them:
Milestones: (see below)
Evidence to collect:
</pre>

<h3>Milestones over six weeks</h3>
<table>
<thead><tr><th>Week</th><th>Milestone</th></tr></thead>
<tbody>
<tr><td>1</td><td>Requirements, architecture document, empty repository, README</td></tr>
<tr><td>2</td><td>Database and the first working page</td></tr>
<tr><td>3</td><td>Accounts, login and roles</td></tr>
<tr><td>4</td><td>Remaining core features</td></tr>
<tr><td>5</td><td>Testing, security checks and fixes</td></tr>
<tr><td>6</td><td>Deployment, documentation, rehearsal of the demonstration</td></tr>
</tbody>
</table>
<p>Commit at the end of every working session. If you fall behind, cut features, not testing.</p>

<h3>Evidence to collect</h3>
<ul>
<li>Screenshots of the main pages for each role.</li>
<li>A GitHub repository with a history of meaningful commits.</li>
<li>Documentation: README, requirements and architecture document, AI-use log.</li>
<li>A test report listing what you tested, what happened and what you fixed.</li>
<li>A live URL, or screenshots of the deployment if hosting is not possible.</li>
</ul>

<h3>Template: requirements and architecture document</h3>
<pre>
1. Purpose and users
2. Functional requirements (R-01, R-02 ...)
3. Non-functional requirements (speed, security, mobile use)
4. Roles and permissions
5. Database tables (name: key columns, relationships)
6. Pages and routes
7. Folder structure (for example: public/, app/, views/)
8. Security measures (password hashing, prepared statements,
   input checks, secrets kept out of Git)
9. Testing plan
10. Deployment plan and backups
</pre>

<h3>Case scenario</h3>
<p>Peter, a learner in Lira, chose the Loan Management System because it sounded impressive. He planned interest calculations, repayment schedules, SMS reminders and reports, all in six weeks, with no plan and no scope limit. By week five he had half-working pages and nothing tested. He restarted with a smaller plan: borrowers, loans, repayments and one report, with an audit log. He finished, tested and demonstrated it. The lesson: a narrow, finished project is worth more than a broad, broken one.</p>

<blockquote><p><strong>Quick check:</strong> You are new to databases. Which project is a sensible first choice, a Blog Platform or an E-Commerce Website?</p><p>Answer: The Blog Platform, because it practises basic CRUD at intermediate level, while e-commerce adds carts, orders and payments.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why list what is out of scope in the plan?</p><p>Answer: It stops the project growing beyond what you can finish and test in the time.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Mark your current skills honestly and pick one project from the table.</li>
<li>Fill in the project plan template, with no more than five core features.</li>
<li>Write six weekly milestones with dates.</li>
<li>Create a repository with a README and make your first commit.</li>
<li>Fill in sections 1 to 4 of the requirements and architecture document.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Pick the project that matches your skill and can be finished.</li>
<li>A plan names features, limits and weekly milestones.</li>
<li>Collect evidence as you go: screenshots, repository, documents, test report, live URL.</li>
<li>Write the architecture before building, and update it as you learn.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Final projects part 2: outlines, presentation and assessment',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to outline each project option, explain your architecture, record honestly how you used Claude Code, present in seven minutes, and calculate your weighted assessment mark. This matters because learners who meet the academy's assessment requirements earn a certificate, and the demonstration is where you prove the work is yours.</p>

<h3>Outlines of the eight options</h3>
<table>
<thead><tr><th>Project</th><th>Core features, tables, roles and key risks</th></tr></thead>
<tbody>
<tr><td>Portfolio</td><td>About, projects, contact; no database; one visitor role; risk: poor mobile layout</td></tr>
<tr><td>Business website</td><td>Services, contact form, editable content; tables: messages, pages; roles: visitor, admin; risk: spam and unprotected admin</td></tr>
<tr><td>Blog</td><td>Posts, categories, comments; tables: users, posts, comments; roles: author, admin; risk: script injection in posts</td></tr>
<tr><td>Student management</td><td>Students, classes, marks, dashboards; tables: students, classes, marks, users; roles: admin, teacher; risk: wrong marks or private records exposed</td></tr>
<tr><td>Inventory</td><td>Items, stock in and out, reports; tables: items, movements, users; roles: manager, clerk; risk: negative stock and missing access control</td></tr>
<tr><td>Online academy</td><td>Courses, lessons, enrolments, progress; tables: users, courses, lessons, enrolments, progress; roles: learner, instructor, admin; risk: learners reading others' data</td></tr>
<tr><td>E-commerce</td><td>Catalogue, cart, orders, payment integration; tables: products, carts, orders, order_items, payments; roles: customer, admin; risk: price tampering and payment mistakes; use a provider's test mode</td></tr>
<tr><td>Loan management</td><td>Borrowers, loans, repayments, reports, audit trail; tables: borrowers, loans, repayments, audit_log, users; roles: officer, manager, admin; risk: calculation errors and unauthorised changes</td></tr>
</tbody>
</table>

<h3>Explaining architecture and AI use</h3>
<p>Explain your architecture in plain steps: a visitor opens a page, the router chooses a controller, the controller asks the database, and a view shows the result. Name where login, permissions and validation happen. Then be honest about Claude Code. Keep a log as you work, so you can show which parts the AI drafted, which you wrote, and what you changed after review.</p>
<pre>
AI-use log
Date | Task | What I asked | What the AI produced
Problem found in review | What I changed | How I tested
</pre>

<h3>Seven-minute demonstration</h3>
<ol>
<li>Minute 1: the problem and who the users are.</li>
<li>Minutes 2 to 4: live demonstration of the main flow for each role.</li>
<li>Minute 5: architecture and database in one diagram.</li>
<li>Minute 6: how you used Claude Code, one mistake it made and how you caught it, plus your test evidence.</li>
<li>Minute 7: deployment, limits and what you would improve.</li>
</ol>

<h3>Assessment structure</h3>
<table>
<thead><tr><th>Component</th><th>Weight</th></tr></thead>
<tbody>
<tr><td>Module quizzes</td><td>10%</td></tr>
<tr><td>Practical coding exercises</td><td>25%</td></tr>
<tr><td>GitHub portfolio and project documentation</td><td>15%</td></tr>
<tr><td>Final web application</td><td>40%</td></tr>
<tr><td>Project demonstration and presentation</td><td>10%</td></tr>
</tbody>
</table>
<p>Worked example: quizzes 80, practical exercises 70, portfolio 60, final application 75, presentation 80 (each out of 100). Weighted: 80 × 0.10 = 8; 70 × 0.25 = 17.5; 60 × 0.15 = 9; 75 × 0.40 = 30; 80 × 0.10 = 8. Total = 8 + 17.5 + 9 + 30 + 8 = 72.5%. Learners complete at least one working project before a certificate, and those who meet the academy's assessment requirements earn a certificate.</p>

<h3>Keep growing</h3>
<p>After the course, improve your project, read other people's code, write tests, learn one new tool at a time, and keep your repository and portfolio current. Follow official documentation as tools change.</p>

<h3>Case scenario</h3>
<p>Mercy built an Online Academy and let Claude Code write most of it without a log. At the demonstration she could not explain how enrolment checked permissions, and a panel member found that one learner could open another's progress by changing a number in the address. She lost marks for both. She then added the permission check, tested it, wrote her AI-use log and re-presented. The lesson: understand and test what you submit, and be honest about how it was made.</p>

<blockquote><p><strong>Quick check:</strong> What is the final application's weight in the assessment?</p><p>Answer: 40%, the largest component, because it shows your whole skill set.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Marks of 60 in a component worth 15% add how many points to the total?</p><p>Answer: 9 points, because 60 × 0.15 = 9.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write the outline for your chosen project: features, tables, roles and two risks.</li>
<li>Start an AI-use log and record your next three tasks.</li>
<li>Draft a seven-minute outline and time yourself.</li>
<li>Calculate your own weighted mark using estimated scores.</li>
<li>Write three goals for the month after the course.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Every project needs features, tables, roles and a risk plan.</li>
<li>Honest AI-use logs show understanding, not weakness.</li>
<li>A clear seven-minute structure makes demonstrations calm.</li>
<li>Weighted marks add each score times its percentage.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which requirement is clear and testable?
- The shop site should look modern.
- The system must be fast and good.
* A customer can add an item to a cart and see the total in UGX.
- Users should enjoy the project.
= A clear requirement says who does what and how you can check it. The others are wishes.

Q: An AI assistant tells you to install a package. Its name is one letter different from the well-known library. What is the best action?
* Check the official registry for the exact name, maintainer and recent updates before installing.
- Install it, because the AI is usually correct.
- Install it and delete it later if it fails.
- Ask the AI to confirm that the name is right.
= Look-alike names can be invented or malicious. Verify on the official registry, not by asking the same AI.

Q: You estimate a job at 80 hours and add a 25% buffer. At UGX 20,000 per hour, what is the labour cost?
- UGX 1,600,000
- UGX 1,800,000
* UGX 2,000,000
- UGX 2,500,000
= 80 × 1.25 = 100 hours, and 100 × 20,000 = UGX 2,000,000.

Q: Which are good habits when using Claude Code or another AI coding assistant? (choose all that apply)
* Ask for a plan before it changes files.
- Accept every change to save time.
* Commit to Git before large changes.
* Read and test the generated code.
= Plan, checkpoint, review and test. Accepting everything removes your control.

Q: A client asks you to fix a bug using their customer list. What should you do with the AI tool?
- Paste the list so the AI sees the real problem.
- Paste only the phone numbers.
- Upload the file but delete it afterwards.
* Create invented sample data with the same shape and use that.
= Real customer data is confidential. Sample data lets you get help without exposing anyone.

Q: A learner is new to databases and has four weeks. Which final project is the most sensible choice?
* Personal Portfolio Website or Business Website
- Loan Management System
- E-Commerce Website with payments
- Online Academy with full progress tracking
= Beginner projects can be finished and tested. Advanced projects need strong skills in login, roles and sensitive data.

Q: A learner scores: quizzes 90, practical 60, portfolio 80, final application 70, presentation 50 (each out of 100). What is the weighted total?
- 72.5%
- 65.0%
* 69.0%
- 70.0%
= 90 x 0.10 = 9; 60 x 0.25 = 15; 80 x 0.15 = 12; 70 x 0.40 = 28; 50 x 0.10 = 5. The total is 9 + 15 + 12 + 28 + 5 = 69%.

Q: Why is a clear list of what is out of scope useful in a project plan?
- It makes the document longer.
- It lets you add features during the demonstration.
* It protects the schedule by stopping the project growing beyond what can be finished and tested.
= Out-of-scope notes keep the project finishable, and any extra feature waits until the core works.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which statement about websites and web applications is most accurate?
- They are identical in every way
* Web applications let users log in and work with stored data, while many websites mainly display information
- Websites need a server but web applications do not
- Web applications cannot be opened on a phone
= Both use browsers and servers. The difference is the interaction with personal or stored data.

Q: A farmers' group wants buyers to see current maize prices that the secretary updates weekly. Which pairing is right?
- Frontend stores the prices; backend draws the table
* Frontend shows the prices; backend stores and serves the updated prices
- Only a mobile app can do this
- No database or server logic is ever involved
= The visitor-facing page is the frontend, while saving and supplying the data is the backend.

Q: Before using Claude Code, what is the best way to learn the current installation steps and account requirements?
- Trust a friend's old message
* Follow the current official documentation, because methods and requirements change
- Guess from the tool's name
- Download it from any site that has a link
= Installation details change often, and only official documentation stays current and safe.

Q: Aisha pasted a supplier list with phone numbers into an AI chat. What should she have done?
- Nothing, because chats are private by definition
* Use invented sample data and keep real personal or confidential data out of AI tools
- Paste her passwords too so the data was complete
- Share the chat link publicly
= Personal and confidential data should never be pasted into AI tools. Fake sample data is enough for formatting tasks.

Q: What is the correct purpose of alt text on an informative image?
- To make the image load faster
- To set the image size
- To add a tooltip for search engines only
* To describe the image for people who cannot see it and to show if it fails to load
= Alt text gives screen reader users the meaning of the image and appears when the file cannot be displayed.

Q: Which CSS is the best choice for a product catalogue that fits as many 220px-minimum cards per row as the screen allows?
* display: grid with grid-template-columns: repeat(auto-fit, minmax(220px, 1fr))
- A table with fixed widths
- display: flex with width: 960px
- float: left on every card
= Grid with auto-fit and minmax adapts to any width without separate media queries.

Q: You are given a bug: clicking the menu button does nothing and the Console shows "Cannot read properties of null". What is the most likely cause?
- The CSS file is missing
* querySelector found no element, for example because the id in JavaScript does not match the HTML or the script runs before the page loads
- The browser is too old
- The menu has too many links
= Null from querySelector means the element was not found. Check the selector spelling and use defer so the HTML exists first.

Q: Which statement about the DOM is correct?
- It is a CSS file that styles the page
- It is a database that stores form data
* It is the page represented as a tree of elements that JavaScript can read and change
- It is a server that sends HTML
= The DOM lets scripts select, change and create elements, and respond to events.

Q: A long Claude Code conversation starts to contradict earlier decisions. What is a sensible step?
* Start a fresh session with a short summary of the project and standing rules
- Keep going and ignore the errors
- Paste your passwords so it understands better
- Delete the project
= Long chats can lose track of earlier rules, and a clear restart keeps tasks small and focused.

Q: What is the purpose of CLAUDE.md in a project?
- It stores passwords for the tool
* It holds standing project rules for the assistant to read
- It replaces Git
- It deploys the site
= A rules file saves you repeating the same instructions each session, but it must never hold secrets.

Q: Which sequence correctly saves your work in Git?
- git push, git init, git add
- git commit, git add, git status
* git add the files, then git commit with a message
- git clone, then git revert
= Staging with git add chooses what goes into the snapshot and git commit saves it.

Q: Which command creates and moves to a new branch?
- git branch -d
* git switch -c add-contact-form
- git merge add-contact-form
- git pull add-contact-form
= The -c option creates the branch and switches to it in one step.

Q: Where should a PHP app store uploaded files if they should not be run as code?
- In the same folder as index.php with the original name
* Outside the web root, under random names
- In the database password file
= Storing outside the public folder, renamed, stops an uploaded script from being requested and executed.

Q: Which line correctly protects a database connection file when the project goes to GitHub?
- Commit config/.env so the team can see it
* List config/.env in .gitignore and commit only an example file
- Paste the password into the README
- Rename the file to secret.txt
= Secrets must never be committed; an example file with fake values shows the structure safely.

Q: A student table has class_id with a foreign key to classes. You try to insert class_id 99, which does not exist. What happens?
- It is saved and the class is created
- The class_id becomes NULL
* The database rejects the row with a constraint error
- The first class is used instead
= A foreign key forces every class_id to match an existing class, protecting data quality.

Q: An AI assistant gives you a PHP page that works. Which is the best next step?
- Upload it at once because it works
* Review it for SQL injection, XSS and missing validation, and test with bad input
- Delete the checks to speed it up
- Ask it to approve its own code and stop
= Working code may still be unsafe, so you check it and test with hostile input.

Q: What is the difference between authentication and authorization?
- They mean the same thing
- Authentication sets roles; authorization checks passwords
* Authentication proves who you are; authorization decides what you may do
- Authorization is only needed for admins
= Login proves identity; permissions then limit actions. A parent can be authenticated yet not authorized to see another child's results.

Q: After a successful password check, why call session_regenerate_id(true)?
- To make the page load faster
* To prevent session fixation by issuing a fresh session id at login
- To hash the password again
- To log the user out of other sites
= An id known before login must not stay valid after login, so a new one is issued.

Q: Which requirement is well written for an SRS?
- The system must be good
- The system should be modern
* A teacher can add a pupil with name, class and guardian phone; saving without a name shows an error
- The portal will make everyone happy
= A requirement must be testable, so someone can check it against the finished system.

Q: A SACCO register must show members with their savings totals per branch. Which SQL idea fits?
- INSERT with a loop
- DROP TABLE
- SELECT * with no conditions
* GROUP BY branch with SUM and COUNT
= GROUP BY creates one row per branch, while SUM and COUNT calculate the totals inside the database.

Q: What does a CSRF token protect against?
- Attackers reading your database
* A logged-in user's browser being tricked into sending a request they did not intend
- Slow page loading
- Weak passwords
= The token is a secret only your own form contains, so forged requests from other sites fail the check.

Q: Your app calls a maps service and the service is down. Which response is best?
- Show a raw error message with the technical detail
- Let the page keep loading until it answers
* Use a timeout, hide or replace the map with a clear message and carry on
- Delete the map section permanently
= Non-essential services should fail quietly and kindly so the rest of the page still works.

Q: A developer accidentally commits a config file with a live payment key to a public repository, then deletes the file in the next commit. What should happen next?
- Nothing, since the file is gone
* Revoke and replace the key, because it stays in the repository history
- Make the repository private only
- Ask the AI assistant to forget it
= Git keeps history, and bots scan public repositories, so the key must be treated as stolen and replaced.

Q: Which header value tells browsers not to guess a file's type?
- Referrer-Policy: no-referrer
* X-Content-Type-Options: nosniff
- Cache-Control: private
- Content-Length: 0
= nosniff makes the browser trust the declared type, which reduces some upload and script-injection tricks.

Q: A school site launched with debug mode on. A visitor saw the database name in an error message. What should be done?
- Leave it, since only developers use the site
* Turn display_errors off, log errors instead and change the exposed credentials
- Rename the website
- Remove HTTPS
= Visible errors leak details. Hide and log them, and change anything that was exposed.

Q: You changed the A record for your domain and the old site still appears on your phone. What is the likely cause?
- The domain is stolen
- PHP is broken
* DNS propagation has not finished
- The database is full
= DNS changes take time to spread, so wait and retest before changing more.

Q: Which is a sound reason to keep config.php outside the web root?
- It makes pages load faster
* The public cannot request it directly, so the database password stays private
- It removes the need for a database
- It turns on HTTPS
= Files outside the web root cannot be reached by address.

Q: Which statement about ownership of AI-assisted code is most accurate?
- It always belongs to the AI company.
- It always belongs to the developer.
* It depends on the law, your contract and the tool's terms, so check all three.
- It belongs to nobody, everywhere.
= Rules differ between countries and tools. Read the terms and write ownership into the client agreement.

Q: Why should you read the licence of an open-source library before using it in a client project?
- Licences are only decoration.
* It sets conditions for use, changes and sharing that may affect the client.
- It guarantees the library has no bugs.
- It lets you remove the author's name.
= Licences differ, and some require you to share changes or keep notices.

Q: What must a learner complete before earning a certificate?
- Only the module quizzes
* At least one working project, plus meeting the academy's assessment requirements
- Any project, even if it does not run
- Only the presentation
= A working, tested project is required, along with the assessment requirements.
QUIZ,
];
