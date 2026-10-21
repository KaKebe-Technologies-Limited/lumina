<?php
return [
    'slug'        => 'web-basics-html-css',
    'title'       => 'Web Basics: Build Your First Web Page (HTML and CSS)',
    'category'    => 'web',
    'level'       => 'intermediate',
    'icon'        => 'code',
    'sort'        => 130,
    'summary'     => 'Learn how websites work and build your own from scratch with HTML and CSS: text, links, images, forms, layout, mobile-friendly design and a finished three-page site.',
    'description' => '<p>Every website you have ever visited is built from a few simple ingredients. In this course you will learn the two most important ones: <strong>HTML</strong>, which gives a page its structure and content, and <strong>CSS</strong>, which controls how it looks. You will write real code, see the result in your browser straight away, and finish by building a small three-page website for a youth group or small business.</p><p>The course starts with how the web works, so nothing feels like magic. Then you build up step by step: text and links, images and tables, colours and fonts, layout with Flexbox and Grid, and finally a design that works on both a phone and a computer.</p><p>It is meant for learners who are comfortable with a computer, files and folders, and want to start creating things online. You do not need to have written code before, but you should be patient: coding is a skill that grows with practice.</p>',
    'outcomes'    => [
        'Explain how a browser, a server and a URL work together to show a web page.',
        'Set up a free code editor and organise a website project in folders.',
        'Write a correct HTML document with headings, lists, links, images, tables and forms.',
        'Use semantic elements so that pages are clear to people and to search engines.',
        'Style a page with CSS using selectors, colours, fonts, the box model and hover effects.',
        'Build layouts with Flexbox and Grid that adapt to phones, tablets and computers.',
        'Make a page more accessible with good contrast, labels, alt text and keyboard focus.',
        'Plan, build, test and publish a small multi-page website.',
    ],
    'requirements'=> [
        'A computer or laptop (Windows, Mac or Linux); a phone is useful for testing',
        'Comfort with files, folders and a web browser',
        'No coding experience needed',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'MDN Web Docs: Learn web development', 'url' => 'https://developer.mozilla.org/en-US/docs/Learn'],
        ['title' => 'W3C Markup Validation Service', 'url' => 'https://validator.w3.org/'],
        ['title' => 'Visual Studio Code', 'url' => 'https://code.visualstudio.com/'],
        ['title' => 'GitHub Pages', 'url' => 'https://pages.github.com/'],
    ],
    'modules' => [
        [
            'title'   => 'Module 1: How the web works',
            'summary' => 'Understand what happens when you open a website, set up your tools and create your first page.',
            'lessons' => [
                [
                    'title'   => 'What happens when you open a website',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe, in simple words, what happens between typing an address and seeing a page. This matters because every web developer, from beginner to expert, builds on exactly this journey.</p>

<h3>Browsers and servers</h3>
<p>A <strong>browser</strong> is a program that shows web pages. Chrome, Firefox, Edge and Safari are browsers. A <strong>server</strong> is a computer that is always switched on and connected to the internet, and stores websites. When you visit a site, your browser (the <em>client</em>) asks a server for the files, and the server sends them back.</p>

<h3>The journey of a request</h3>
<ol>
<li>You type an address such as <code>https://www.example.com/about.html</code> and press Enter.</li>
<li>Your browser finds the server's number (called an <strong>IP address</strong>) using the <strong>DNS</strong>, which works like a phone book for the internet.</li>
<li>The browser sends a <strong>request</strong> to that server using <strong>HTTP</strong> (HyperText Transfer Protocol), the set of rules for asking for and sending web pages. <code>HTTPS</code> is the same thing with encryption, so nobody can read the data on the way.</li>
<li>The server sends back a <strong>response</strong>: the HTML file, plus the CSS, images and other files the page needs.</li>
<li>The browser reads the HTML and CSS and draws the page on your screen.</li>
</ol>

<h3>Parts of a URL</h3>
<p>A <strong>URL</strong> (Uniform Resource Locator) is the full address of one file on the web. Take <code>https://www.example.com/blog/post1.html</code>:</p>
<table>
<thead><tr><th>Part</th><th>Example</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>Protocol</td><td>https</td><td>How to talk to the server</td></tr>
<tr><td>Domain name</td><td>www.example.com</td><td>Which server to ask</td></tr>
<tr><td>Path</td><td>/blog/post1.html</td><td>Which file on that server</td></tr>
</tbody>
</table>

<h3>Front end and back end</h3>
<p>The <strong>front end</strong> is everything the visitor sees and uses in the browser: text, colours, buttons. It is built with HTML, CSS and JavaScript. The <strong>back end</strong> is the work done on the server, such as saving a registration into a database or checking a password. In this course you will focus on the front end. HTML and CSS are not programming languages; they describe content and appearance, which is a perfect first step.</p>

<h3>Try it yourself</h3>
<ol>
<li>Open any website in your browser and look at the address bar. Write down the protocol, the domain name and the path.</li>
<li>Find the padlock symbol next to the address. Click it and read what it says about the connection.</li>
<li>Right-click an empty part of the page and choose <strong>View page source</strong> (shortcut <code>Ctrl+U</code>). You are looking at the HTML the server sent you.</li>
<li>Change the address to a page that does not exist on the same site (add random letters to the end) and see the error page.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A browser asks a server for files; the server sends them back and the browser displays them.</li>
<li>DNS turns a domain name into the server's IP address.</li>
<li>A URL has a protocol, a domain name and a path.</li>
<li>The front end is what visitors see; the back end runs on the server.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Choosing and installing your tools',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will have a working setup for writing web pages: a text editor and a browser. You need nothing expensive; everything in this course is free, and it all works on an ordinary laptop, even an older one.</p>

<h3>What you need</h3>
<p>A web page is a plain <strong>text file</strong>. So you only need two tools:</p>
<ul>
<li>A <strong>text editor</strong> to write the code.</li>
<li>A <strong>web browser</strong> to open and test the result.</li>
</ul>

<h3>Choosing a text editor</h3>
<p>Do not use Microsoft Word. Word adds hidden formatting that breaks code. Use one of these instead:</p>
<table>
<thead><tr><th>Editor</th><th>Good for</th><th>Notes</th></tr></thead>
<tbody>
<tr><td>Notepad (Windows)</td><td>Your very first try</td><td>Already installed. No colours or help, so mistakes are harder to spot.</td></tr>
<tr><td>Visual Studio Code (VS Code)</td><td>This whole course</td><td>Free. Colours the code, suggests tags, and closes brackets for you.</td></tr>
<tr><td>Other code editors</td><td>Later, if you prefer them</td><td>Many free ones exist; they work in a similar way.</td></tr>
</tbody>
</table>
<p>We recommend VS Code. Download it from its official website (code.visualstudio.com), run the installer and accept the default options. If your computer is very slow, Notepad works too; just be extra careful.</p>

<h3>Choosing a browser</h3>
<p>Any modern browser works. It is wise to have <strong>two</strong> (for example Chrome and Firefox) because pages can look slightly different between them. Every browser also includes <strong>developer tools</strong>. Press <code>F12</code> (or <code>Ctrl+Shift+I</code>) to open them. You can inspect any element, see which CSS applies to it, and test a phone-sized screen.</p>

<h3>Helpful editor settings</h3>
<ol>
<li>Show file extensions in File Explorer: open the <strong>View</strong> tab and tick <strong>File name extensions</strong>. This stops you from saving <code>index.html.txt</code> by mistake.</li>
<li>In VS Code, choose <strong>File &gt; Auto Save</strong> so your edits are saved automatically.</li>
<li>Zoom the text with <code>Ctrl</code> and <code>+</code> if the code is too small to read comfortably.</li>
</ol>

<h3>Try it yourself</h3>
<ol>
<li>Install VS Code (or confirm that you can open Notepad).</li>
<li>Turn on file name extensions in File Explorer.</li>
<li>Open your browser, press <code>F12</code>, and find the button that shows a phone-sized screen. Close the tools again.</li>
<li>Open VS Code and type your name. Notice that it is just text. Close it without saving.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A web page is a plain text file; use a text editor, never a word processor.</li>
<li>VS Code is free and helps beginners by colouring and completing code.</li>
<li>Test in more than one browser, and learn to open the developer tools with <code>F12</code>.</li>
<li>Show file extensions so you always know what your files are really called.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Your first page and your project folder',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>In this lesson you will create your first web page, open it in a browser and organise a proper project folder. Good habits with files now will save you hours of confusion later.</p>

<h3>Make a project folder</h3>
<p>All the files of one website live together in a folder. Create a folder called <code>my-first-site</code> in your Documents. Inside it, make two more folders: <code>images</code> and <code>css</code>. A typical structure looks like this:</p>
<pre><code>my-first-site/
    index.html
    css/
        styles.css
    images/
        photo.jpg</code></pre>
<p>Follow these rules for names: use only <strong>lower-case letters</strong>, numbers and hyphens; no spaces. Web servers can treat <code>Photo.JPG</code> and <code>photo.jpg</code> as different files, and spaces turn into ugly <code>%20</code> in addresses. The home page is nearly always called <code>index.html</code>, because servers show that file first when someone visits your site.</p>

<h3>Write and open your first page</h3>
<ol>
<li>Open VS Code, choose <strong>File &gt; Open Folder</strong> and select <code>my-first-site</code>.</li>
<li>Create a new file named <code>index.html</code> (use the New File button next to the folder name).</li>
<li>Type the code below exactly.</li>
<li>Save with <code>Ctrl+S</code>.</li>
<li>Double-click <code>index.html</code> in File Explorer. It opens in your browser.</li>
</ol>
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;title&gt;My First Page&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;h1&gt;Hello, Gulu!&lt;/h1&gt;
    &lt;p&gt;This is my first web page.&lt;/p&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
<p>You should see a large heading and a line of text. The tab at the top of the browser shows "My First Page". Congratulations, you are now a web page author.</p>

<h3>The edit and refresh cycle</h3>
<p>Web developers work in a loop: change the code, save, then switch to the browser and refresh with <code>F5</code>. Keep the editor and the browser side by side. If nothing changes, you probably forgot to save.</p>

<h3>Common mistakes</h3>
<ul>
<li>Saving as <code>index.html.txt</code>. The browser then shows the code as text instead of a page.</li>
<li>Forgetting to save before refreshing.</li>
<li>Using capital letters or spaces in file names.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Create the folder structure shown above on your computer.</li>
<li>Write the page and change the heading to your own name and the text to one sentence about yourself.</li>
<li>Save, refresh the browser and confirm that the change appears.</li>
<li>Deliberately delete the closing <code>&lt;/p&gt;</code>, save and refresh. Notice that browsers are forgiving, then put it back.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Keep every website in its own folder, with sub-folders for images and CSS.</li>
<li>Use lower-case names, no spaces, and call your home page <code>index.html</code>.</li>
<li>Edit, save, refresh: repeat this loop often.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does a web server do?
- It draws the page on your screen
* It stores website files and sends them to browsers that ask for them
- It converts HTML into CSS
- It connects your phone to mobile data
= A server is an always-on computer that holds the files. The browser is what draws the page.

Q: What is the job of DNS?
* To turn a domain name such as example.com into the IP address of a server
- To encrypt the connection
- To store the CSS of a website
- To make pages load faster by shrinking images
= DNS works like a phone book: you know the name, it finds the number.

Q: In the URL https://www.example.com/blog/post1.html, which part is the path?
- https
- www.example.com
* /blog/post1.html
- example
= The protocol is https, the domain is www.example.com and the path says which file to fetch.

Q: Which of these belong to the front end? (choose all that apply)
* The colours and fonts of a page
* A button the visitor clicks
- A database on the server that stores registrations
- The server checking a password
= The front end is what runs and shows in the browser. Databases and password checks are back end work.

Q: Why should you NOT write web pages in Microsoft Word?
- Word cannot print
- Word is too expensive for most learners
* Word adds hidden formatting; a web page must be plain text
- Word cannot save files in folders
= HTML files are plain text. A plain text editor keeps your code clean.

Q: Which file name is best for the home page of a website?
- Home Page.html
- INDEX.HTML.txt
* index.html
- my first page.htm
= Servers look for index.html first, and good names use lower-case letters, no spaces and the correct extension.
QUIZ,
        ],
        [
            'title'   => 'Module 2: HTML foundations',
            'summary' => 'Write well-structured HTML with text, lists, links, images, semantic sections, tables and forms.',
            'lessons' => [
                [
                    'title'   => 'The structure of an HTML document',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write the skeleton that every web page needs and use headings, paragraphs and lists correctly. This is the foundation: a clean structure helps visitors, search engines and screen readers.</p>

<h3>HTML in one sentence</h3>
<p><strong>HTML</strong> (HyperText Markup Language) uses <strong>tags</strong> to label the parts of a page. Most tags come in pairs: an opening tag <code>&lt;p&gt;</code> and a closing tag <code>&lt;/p&gt;</code>. Together with the content between them, they make an <strong>element</strong>. Some tags have extra information called <strong>attributes</strong>, written as name and value, for example <code>lang="en"</code>.</p>

<h3>The skeleton</h3>
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1"&gt;
    &lt;title&gt;Gulu Youth Club&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;!-- everything visitors see goes here --&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
<table>
<thead><tr><th>Part</th><th>Purpose</th></tr></thead>
<tbody>
<tr><td><code>&lt;!DOCTYPE html&gt;</code></td><td>Tells the browser this is modern HTML</td></tr>
<tr><td><code>&lt;html lang="en"&gt;</code></td><td>Wraps the whole page and states its language</td></tr>
<tr><td><code>&lt;head&gt;</code></td><td>Information about the page that is not shown on it</td></tr>
<tr><td><code>&lt;meta charset="UTF-8"&gt;</code></td><td>Lets the page show all letters and symbols correctly</td></tr>
<tr><td><code>&lt;title&gt;</code></td><td>The name in the browser tab and in search results</td></tr>
<tr><td><code>&lt;body&gt;</code></td><td>All visible content</td></tr>
</tbody>
</table>
<p>The text between <code>&lt;!--</code> and <code>--&gt;</code> is a <strong>comment</strong>: the browser ignores it, so you can leave notes to yourself.</p>

<h3>Headings and paragraphs</h3>
<p>Headings run from <code>&lt;h1&gt;</code> (most important) to <code>&lt;h6&gt;</code>. Use one <code>h1</code> per page for its main title, then <code>h2</code> for sections and <code>h3</code> for sub-sections. Do not pick a heading just because it looks big; CSS can change size later. Paragraphs use <code>&lt;p&gt;</code>. A line break uses <code>&lt;br&gt;</code> and has no closing tag.</p>

<h3>Lists</h3>
<pre><code>&lt;h2&gt;Club activities&lt;/h2&gt;
&lt;ul&gt;
    &lt;li&gt;Computer practice&lt;/li&gt;
    &lt;li&gt;Football&lt;/li&gt;
&lt;/ul&gt;

&lt;h2&gt;How to join&lt;/h2&gt;
&lt;ol&gt;
    &lt;li&gt;Fill in the form&lt;/li&gt;
    &lt;li&gt;Come to the first meeting&lt;/li&gt;
&lt;/ol&gt;</code></pre>
<p>Use <code>&lt;ul&gt;</code> for a bulleted list where order does not matter, and <code>&lt;ol&gt;</code> for a numbered list where it does. Emphasis uses <code>&lt;strong&gt;</code> (important, usually bold) and <code>&lt;em&gt;</code> (stress, usually italic).</p>

<h3>Try it yourself</h3>
<ol>
<li>Create a page about your favourite hobby with a title, an <code>h1</code>, two <code>h2</code> sections and at least two paragraphs.</li>
<li>Add a bulleted list of five things related to the hobby.</li>
<li>Add a numbered list of steps for how to start it.</li>
<li>Add a comment before each section and open the page to check that comments do not appear.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Every page needs the doctype, <code>html</code>, <code>head</code> and <code>body</code>.</li>
<li>Headings give structure: one <code>h1</code>, then a logical order of <code>h2</code> and <code>h3</code>.</li>
<li>Choose <code>ul</code> or <code>ol</code> depending on whether order matters.</li>
<li>Comments are for people; browsers ignore them.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Links and images',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>The web is a web because of links, and it is interesting because of images. After this lesson you will be able to link pages together, link to other websites, and add images that everyone can understand, including people who cannot see them.</p>

<h3>Links</h3>
<p>A link uses the anchor tag <code>&lt;a&gt;</code> and its <code>href</code> attribute (short for "hypertext reference"), which holds the destination:</p>
<pre><code>&lt;a href="https://www.mozilla.org"&gt;Visit Mozilla&lt;/a&gt;
&lt;a href="about.html"&gt;About us&lt;/a&gt;
&lt;a href="mailto:info@example.com"&gt;Email us&lt;/a&gt;
&lt;a href="tel:+256700000000"&gt;Call us&lt;/a&gt;</code></pre>
<p>The text between the tags is what visitors click. Write clear link text: "Read our 2025 report" is far better than "click here", because screen readers can list links on their own.</p>

<h3>Absolute and relative paths</h3>
<table>
<thead><tr><th>Type</th><th>Example</th><th>Use it for</th></tr></thead>
<tbody>
<tr><td>Absolute</td><td><code>https://www.example.com/page.html</code></td><td>Other websites</td></tr>
<tr><td>Relative, same folder</td><td><code>about.html</code></td><td>A file next to this one</td></tr>
<tr><td>Relative, sub-folder</td><td><code>images/logo.png</code></td><td>A file inside a folder below this one</td></tr>
<tr><td>Relative, up a level</td><td><code>../index.html</code></td><td>A file in the folder above (<code>..</code> means "go up one")</td></tr>
</tbody>
</table>
<p>For your own pages always use relative paths, so the site still works when you move it to a server. To open a link in a new tab add <code>target="_blank" rel="noopener"</code>, but do this sparingly because it can confuse some users.</p>

<h3>Images</h3>
<pre><code>&lt;img src="images/team.jpg" alt="Five club members standing outside the youth centre" width="600" height="400"&gt;</code></pre>
<ul>
<li><code>src</code> is the path to the image file.</li>
<li><code>alt</code> is the <strong>alternative text</strong>. Screen readers read it aloud, and it appears if the image fails to load (a real problem on slow mobile data). Describe what matters in the picture. For a purely decorative picture use an empty <code>alt=""</code>.</li>
<li><code>width</code> and <code>height</code> reserve space so the page does not jump while loading.</li>
</ul>
<p>The <code>img</code> tag has no closing tag. Choose formats wisely: <strong>JPG</strong> for photographs, <strong>PNG</strong> for logos and screenshots, <strong>SVG</strong> for simple scalable icons, and <strong>WebP</strong> for small file sizes. Large photographs straight from a phone can be several megabytes, so shrink them before you use them. Only use images you own or that have a licence allowing reuse.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create <code>index.html</code> and <code>about.html</code> in the same folder.</li>
<li>Link them to each other, using relative paths.</li>
<li>Add a link to an outside website of your choice.</li>
<li>Place a photograph in your <code>images</code> folder and show it with a helpful <code>alt</code> description.</li>
<li>Rename the image file in the folder without changing the code, then refresh. You will see the alt text appear. Rename it back.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use <code>&lt;a href="..."&gt;</code> for links, with descriptive link text.</li>
<li>Relative paths for your own files; absolute URLs for other sites.</li>
<li>Every meaningful image needs an <code>alt</code> description.</li>
<li>Resize and compress images before you add them to a page.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Semantic layout and tables',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to divide a page into meaningful sections and show data in a proper table. Choosing the right element for each job makes your site easier to navigate and easier for search engines and screen readers to understand.</p>

<h3>Semantic elements</h3>
<p>An element is <strong>semantic</strong> when its name tells you what it means. Compare <code>&lt;div&gt;</code>, which means nothing, with <code>&lt;nav&gt;</code>, which clearly means "navigation". The main page-level elements are:</p>
<table>
<thead><tr><th>Element</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td><code>&lt;header&gt;</code></td><td>Introductory area: logo, site name, sometimes the menu</td></tr>
<tr><td><code>&lt;nav&gt;</code></td><td>A block of main navigation links</td></tr>
<tr><td><code>&lt;main&gt;</code></td><td>The main content of the page (use only one)</td></tr>
<tr><td><code>&lt;section&gt;</code></td><td>A themed group of content, normally with a heading</td></tr>
<tr><td><code>&lt;article&gt;</code></td><td>A self-contained item such as a news story</td></tr>
<tr><td><code>&lt;aside&gt;</code></td><td>Side information related to the content</td></tr>
<tr><td><code>&lt;footer&gt;</code></td><td>Closing area: contacts, copyright</td></tr>
</tbody>
</table>
<pre><code>&lt;body&gt;
    &lt;header&gt;
        &lt;h1&gt;Gulu Youth Tech Club&lt;/h1&gt;
        &lt;nav&gt;
            &lt;a href="index.html"&gt;Home&lt;/a&gt;
            &lt;a href="about.html"&gt;About&lt;/a&gt;
        &lt;/nav&gt;
    &lt;/header&gt;
    &lt;main&gt;
        &lt;section&gt;
            &lt;h2&gt;What we do&lt;/h2&gt;
            &lt;p&gt;We teach computer skills every Saturday.&lt;/p&gt;
        &lt;/section&gt;
    &lt;/main&gt;
    &lt;footer&gt;
        &lt;p&gt;Contact: info@example.com&lt;/p&gt;
    &lt;/footer&gt;
&lt;/body&gt;</code></pre>
<p>Use <code>&lt;div&gt;</code> and <code>&lt;span&gt;</code> only when no meaningful element fits; they are neutral boxes (block and inline) used mainly for styling.</p>

<h3>Tables</h3>
<p>Tables are for <strong>data in rows and columns</strong>, such as a timetable. They are not for page layout.</p>
<pre><code>&lt;table&gt;
    &lt;thead&gt;
        &lt;tr&gt;&lt;th&gt;Day&lt;/th&gt;&lt;th&gt;Activity&lt;/th&gt;&lt;th&gt;Fee (UGX)&lt;/th&gt;&lt;/tr&gt;
    &lt;/thead&gt;
    &lt;tbody&gt;
        &lt;tr&gt;&lt;td&gt;Saturday&lt;/td&gt;&lt;td&gt;Typing class&lt;/td&gt;&lt;td&gt;2,000&lt;/td&gt;&lt;/tr&gt;
        &lt;tr&gt;&lt;td&gt;Sunday&lt;/td&gt;&lt;td&gt;Coding club&lt;/td&gt;&lt;td&gt;3,000&lt;/td&gt;&lt;/tr&gt;
    &lt;/tbody&gt;
&lt;/table&gt;</code></pre>
<p><code>tr</code> is a table row, <code>th</code> is a heading cell and <code>td</code> is a data cell. The optional <code>thead</code> and <code>tbody</code> group the header and the body. A table can have a <code>&lt;caption&gt;</code> as its title, which also helps accessibility.</p>

<h3>Try it yourself</h3>
<ol>
<li>Rebuild one of your earlier pages using <code>header</code>, <code>nav</code>, <code>main</code>, <code>section</code> and <code>footer</code>.</li>
<li>Add a table of at least four rows, for example a weekly class timetable or a price list.</li>
<li>Give the table a caption.</li>
<li>Check that the page has exactly one <code>h1</code> and that the headings are in a logical order.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Semantic elements describe the role of each part of a page.</li>
<li>Use one <code>main</code> per page and put navigation links inside <code>nav</code>.</li>
<li>Tables show data, never layout.</li>
<li>Use <code>div</code> only when nothing more meaningful exists.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Forms, comments and validating your HTML',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a simple form, label it correctly, and check your HTML for mistakes. Forms let visitors contact you or register for an event, and checking your code stops small errors turning into big problems.</p>

<h3>A basic form</h3>
<pre><code>&lt;form action="thanks.html" method="post"&gt;
    &lt;p&gt;
        &lt;label for="fullname"&gt;Full name&lt;/label&gt;&lt;br&gt;
        &lt;input type="text" id="fullname" name="fullname" required&gt;
    &lt;/p&gt;
    &lt;p&gt;
        &lt;label for="email"&gt;Email address&lt;/label&gt;&lt;br&gt;
        &lt;input type="email" id="email" name="email" required&gt;
    &lt;/p&gt;
    &lt;p&gt;
        &lt;label for="course"&gt;Choose a course&lt;/label&gt;&lt;br&gt;
        &lt;select id="course" name="course"&gt;
            &lt;option value="typing"&gt;Typing&lt;/option&gt;
            &lt;option value="coding"&gt;Coding&lt;/option&gt;
        &lt;/select&gt;
    &lt;/p&gt;
    &lt;p&gt;
        &lt;label for="message"&gt;Message&lt;/label&gt;&lt;br&gt;
        &lt;textarea id="message" name="message" rows="4"&gt;&lt;/textarea&gt;
    &lt;/p&gt;
    &lt;button type="submit"&gt;Send&lt;/button&gt;
&lt;/form&gt;</code></pre>

<h3>What each part does</h3>
<ul>
<li><code>&lt;form&gt;</code> wraps the fields. <code>action</code> is where the data is sent and <code>method</code> is how (<code>post</code> hides data inside the request; <code>get</code> puts it in the address).</li>
<li><code>&lt;label&gt;</code> is the visible name of a field. Its <code>for</code> value must match the field's <code>id</code>. Clicking the label then selects the field, and screen readers announce it properly.</li>
<li><code>&lt;input&gt;</code> has many types: <code>text</code>, <code>email</code>, <code>password</code>, <code>number</code>, <code>date</code>, <code>checkbox</code>, <code>radio</code>. Phones show a suitable keyboard for each type.</li>
<li><code>required</code> asks the browser to refuse an empty field.</li>
<li><code>name</code> is the label the data travels under on the server side.</li>
</ul>
<p>An important truth: HTML alone cannot <em>process</em> a form. Something on the server (back end code) or a form service must receive the data. For a static site, many free form services exist; for now we are only building the front end. Also never use a form to collect sensitive data unless the site uses HTTPS.</p>

<h3>Validating your HTML</h3>
<p>Browsers forgive many errors, which hides them. A <strong>validator</strong> reads your code and lists problems. The W3C Markup Validation Service (validator.w3.org) lets you paste code or upload a file. Common messages are a missing <code>alt</code>, an unclosed tag, or elements nested in the wrong order. Elements must close in the reverse order they opened:</p>
<pre><code>&lt;p&gt;This is &lt;strong&gt;correct&lt;/strong&gt;.&lt;/p&gt;
&lt;p&gt;This is &lt;strong&gt;wrong.&lt;/p&gt;&lt;/strong&gt;</code></pre>
<p>Your editor also helps. Consistent <strong>indentation</strong> (four spaces for each level of nesting) makes missing tags easy to see.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create <code>contact.html</code> with a form that has name, email, a drop-down and a message box.</li>
<li>Click each label to confirm that the matching field is selected.</li>
<li>Try to submit with an empty name field and read the browser's message.</li>
<li>Paste your page into the validator and fix every error it reports.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Each form field needs a visible <code>label</code> linked by <code>for</code> and <code>id</code>.</li>
<li>Choose the right <code>input</code> type; it helps phones and validation.</li>
<li>HTML forms need a server or service to receive the data.</li>
<li>Validate regularly and keep your indentation tidy.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Where does the visible content of a page go?
- Inside the head element
* Inside the body element
- Inside the title element
- Before the doctype
= The head holds information about the page. The body holds everything the visitor sees.

Q: Which element is best for a block of main navigation links?
- div
- section
- footer
* nav
= nav is the semantic element for navigation, which helps screen readers jump straight to the menu.

Q: What is the purpose of the alt attribute on an image?
- It sets the image size
* It gives a text description for screen readers and for when the image cannot load
- It links the image to another page
- It makes the image load faster
= Alt text is essential for accessibility and helps users on slow connections.

Q: You have index.html in a folder and a photo at images/team.jpg in the same folder structure. Which src value is correct?
- C:\Users\me\images\team.jpg
- /team.jpg
* images/team.jpg
- team.jpg/images
= Use a relative path from the page to the file. A path starting with C:\ only works on your own computer.

Q: How do you connect a label to its form field?
- Put them in the same line
* Give the field an id and set the label's for attribute to the same value
- Use the same name on two fields
- Add required to the label
= The for and id values must match. Then clicking the label selects the field and screen readers announce it.

Q: Which are correct uses of a table? (choose all that apply)
* A timetable of classes
* A price list with several columns
- Positioning a menu on the left of the page
- Making the page look two columns wide
= Tables are for data. Page layout is done with CSS.

Q: Which nesting is correct?
- The strong tag closes after the p tag that contains it
* The strong element is opened and closed fully inside the p element
- The strong tag opens before the p tag and closes inside it
- The p element is closed but the strong element is never closed
= Tags must close in the reverse order they were opened, and every opened tag needs closing.
QUIZ,
        ],
        [
            'title'   => 'Module 3: CSS foundations',
            'summary' => 'Style your pages with selectors, colours, fonts, the box model, backgrounds, units and hover effects.',
            'lessons' => [
                [
                    'title'   => 'What CSS is and how to link it',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to link a stylesheet to a page and write rules that change how elements look. CSS turns a plain page into something you are proud to show people.</p>

<h3>What is CSS?</h3>
<p><strong>CSS</strong> (Cascading Style Sheets) describes the appearance of HTML: colours, fonts, spacing and layout. HTML says <em>what</em> something is; CSS says <em>how it looks</em>. Keeping them separate means you can change the design of a whole website by editing one file.</p>

<h3>Three ways to add CSS</h3>
<table>
<thead><tr><th>Method</th><th>Where</th><th>Advice</th></tr></thead>
<tbody>
<tr><td>External stylesheet</td><td>A separate <code>.css</code> file linked from the head</td><td>Best. Use this almost always.</td></tr>
<tr><td>Internal</td><td>A <code>&lt;style&gt;</code> element in the head</td><td>Fine for quick tests.</td></tr>
<tr><td>Inline</td><td>A style attribute on one element</td><td>Avoid; it is hard to maintain.</td></tr>
</tbody>
</table>

<h3>Linking an external stylesheet</h3>
<p>Create <code>css/styles.css</code>, then add this line inside the <code>&lt;head&gt;</code> of your page:</p>
<pre><code>&lt;link rel="stylesheet" href="css/styles.css"&gt;</code></pre>
<p>The path is relative to the HTML page, exactly like image paths. If the styles do not appear, a wrong path is the first thing to check.</p>

<h3>Anatomy of a CSS rule</h3>
<pre><code>h1 {
    color: darkgreen;
    font-size: 2.5rem;
}</code></pre>
<ul>
<li><code>h1</code> is the <strong>selector</strong>: which elements to style.</li>
<li>The curly brackets hold the <strong>declarations</strong>.</li>
<li><code>color</code> is a <strong>property</strong> and <code>darkgreen</code> is its <strong>value</strong>. A colon separates them, and a semicolon ends each declaration.</li>
</ul>
<p>Write comments in CSS between <code>/*</code> and <code>*/</code>. A forgotten semicolon or bracket breaks the rule after it, so check punctuation first when something does not work.</p>

<h3>Inspecting with developer tools</h3>
<p>Press <code>F12</code>, click the small arrow icon, then click any element on the page. The panel shows the HTML of that element and the CSS rules that apply to it. You can edit values there and see the result instantly. Changes are temporary, so copy good ones into your file.</p>

<h3>Try it yourself</h3>
<ol>
<li>Open your project and create <code>css/styles.css</code>.</li>
<li>Link it from <code>index.html</code>.</li>
<li>Write rules to give the page a light grey background colour on the <code>body</code>, and a dark blue colour to the <code>h1</code>.</li>
<li>Open the developer tools, select the <code>h1</code>, and change its colour live to see how it works.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>HTML gives structure; CSS gives appearance.</li>
<li>Use one external stylesheet linked in the <code>head</code>.</li>
<li>A rule is a selector plus declarations of property and value.</li>
<li>Developer tools let you experiment safely.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Selectors, specificity and hover effects',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose exactly which elements a rule affects, understand what happens when two rules disagree, and add interactive effects such as a button that changes colour under the mouse.</p>

<h3>The three basic selectors</h3>
<pre><code>/* Element selector: every paragraph */
p { color: #333333; }

/* Class selector: any element that has class&#61;"note" */
.note { background-color: #fff3cd; }

/* ID selector: the one element with id="top" */
#top { border-bottom: 2px solid green; }</code></pre>
<p>In your HTML you attach them like this (use the attributes <code>class</code> and <code>id</code>):</p>
<pre><code>&lt;p class&#61;"note"&gt;Remember to bring your ID.&lt;/p&gt;
&lt;header id&#61;"top"&gt;...&lt;/header&gt;</code></pre>
<p>A <strong>class</strong> can be reused on many elements, and an element can have several classes separated by spaces. An <strong>id</strong> must be unique on the page. As a habit, prefer classes for styling.</p>

<h3>Grouping and descendants</h3>
<pre><code>h1, h2, h3 { font-family: Georgia, serif; }   /* grouping */
nav a { text-decoration: none; }              /* a links inside nav */</code></pre>
<p>A space between selectors means "inside". So <code>nav a</code> styles only links that are inside a <code>nav</code>.</p>

<h3>When rules disagree: specificity</h3>
<p>If two rules target the same thing, the browser must choose. It uses <strong>specificity</strong>, a score of how precise a selector is:</p>
<ol>
<li>Inline styles are strongest.</li>
<li>Then ID selectors.</li>
<li>Then class selectors (and pseudo-classes).</li>
<li>Then element selectors.</li>
</ol>
<p>If the score is equal, the rule written <strong>later</strong> in the file wins. That is the "cascading" in the name. Avoid the temptation to force things with <code>!important</code>; it makes later fixes very painful. Instead, write a more specific selector.</p>

<h3>Pseudo-classes: reacting to the visitor</h3>
<p>A <strong>pseudo-class</strong> selects an element in a certain state. It is added with a colon:</p>
<pre><code>a { color: #0b5ed7; }
a:hover { color: #d63384; text-decoration: underline; }
a:focus { outline: 3px solid orange; }
li:first-child { font-weight: bold; }</code></pre>
<p><code>:hover</code> applies when the mouse is over the element; <code>:focus</code> applies when the element is selected with the keyboard (important for accessibility); <code>:first-child</code> picks the first item in its parent. Touch screens do not truly hover, so never hide important information behind a hover.</p>

<h3>Try it yourself</h3>
<ol>
<li>Add a class called <code>highlight</code> to two paragraphs and style them with a yellow background.</li>
<li>Make all links dark blue, and change them to orange on hover.</li>
<li>Write two conflicting rules for <code>h2</code> colour and watch which wins. Then swap their order.</li>
<li>Press <code>Tab</code> to move between links and check that the focus style is visible.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Element, class and ID selectors target different things; classes are the everyday tool.</li>
<li>More specific selectors win; if equal, the later rule wins.</li>
<li>Pseudo-classes such as <code>:hover</code> and <code>:focus</code> style states.</li>
<li>Always give keyboard users a visible focus style.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Colours, fonts and text',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose colours and fonts, and format text so that it is easy to read. Readability matters more than decoration: the best design is the one people can read comfortably on a small phone in bright sunlight.</p>

<h3>Ways to write colours</h3>
<table>
<thead><tr><th>Format</th><th>Example</th><th>Notes</th></tr></thead>
<tbody>
<tr><td>Name</td><td><code>darkgreen</code></td><td>About 140 names exist</td></tr>
<tr><td>Hex</td><td><code>#1b5e20</code></td><td>Red, green and blue as pairs of digits</td></tr>
<tr><td>RGB</td><td><code>rgb(27, 94, 32)</code></td><td>Each number runs from 0 to 255</td></tr>
<tr><td>RGBA</td><td><code>rgba(0, 0, 0, 0.5)</code></td><td>The last number is transparency</td></tr>
</tbody>
</table>
<p>Choose a small palette: one main colour, one accent, plus dark text and a light background. Browsers' developer tools include a colour picker so you can find hex values easily.</p>

<h3>Text colour and background</h3>
<pre><code>body {
    color: #222222;
    background-color: #f7f7f2;
}</code></pre>
<p>Make sure text and background have strong <strong>contrast</strong>. Light grey text on white is hard to read for many people. Aim for dark text on a light background, or the reverse. Free online contrast checkers tell you whether a pair is acceptable.</p>

<h3>Fonts</h3>
<pre><code>body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 1.125rem;
    line-height: 1.6;
}
h1, h2 {
    font-family: Georgia, "Times New Roman", serif;
}</code></pre>
<p>A <code>font-family</code> lists fonts in order of preference. The browser uses the first one installed on the visitor's device, so always end with a generic family: <code>serif</code>, <code>sans-serif</code> or <code>monospace</code>. Fonts with names that contain spaces go in quotes. Web fonts from a font service can be added later, but they slow a page down on weak connections, so use them with care.</p>

<h3>Formatting text</h3>
<ul>
<li><code>font-weight: bold;</code> or a number such as <code>600</code></li>
<li><code>font-style: italic;</code></li>
<li><code>text-align: left;</code> (also <code>center</code>, <code>right</code>); left-aligned text is usually the easiest to read</li>
<li><code>text-decoration: underline;</code> or <code>none</code></li>
<li><code>text-transform: uppercase;</code></li>
<li><code>line-height: 1.6;</code> sets the space between lines; 1.5 to 1.7 is comfortable</li>
<li><code>letter-spacing: 0.05em;</code></li>
</ul>
<p>Keep line length reasonable. Lines of 60 to 80 characters read best. You can limit the width of a text block with <code>max-width: 40rem;</code>.</p>

<h3>Try it yourself</h3>
<ol>
<li>Pick a palette of three colours for a club or business you know.</li>
<li>Set the page text colour, background colour and a readable font size and line height.</li>
<li>Give headings a different font family from the body text.</li>
<li>Test the contrast by squinting at the screen or using a free contrast checker.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Colours can be names, hex, RGB or RGBA values.</li>
<li>Strong contrast and generous line height make text readable.</li>
<li>List several fonts and end with a generic family.</li>
<li>Limit line length for comfortable reading.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The box model, display and units',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will understand why elements take the space they do and how to control it. The <strong>box model</strong> is the most important idea in CSS: every element is a rectangular box, and learning how that box is built solves most layout puzzles.</p>

<h3>The four layers of a box</h3>
<p>From the inside out, each box has:</p>
<ol>
<li><strong>Content</strong>: the text or image itself.</li>
<li><strong>Padding</strong>: space between the content and the border (inside the box).</li>
<li><strong>Border</strong>: a line around the padding.</li>
<li><strong>Margin</strong>: space outside the border, separating the box from its neighbours.</li>
</ol>
<pre><code>.card {
    width: 300px;
    padding: 16px;
    border: 2px solid #1b5e20;
    margin: 20px auto;
    background-color: white;
}</code></pre>
<p>Shorthand: <code>padding: 10px 20px;</code> means 10px top and bottom, 20px left and right. With four values (<code>10px 20px 5px 0</code>) the order is top, right, bottom, left (clockwise). The trick <code>margin: 0 auto;</code> centres a block that has a set width.</p>

<h3>Measuring the width: box-sizing</h3>
<p>By default, <code>width</code> covers only the content, so padding and border are added on top and boxes grow bigger than you expect. Fix this once, at the top of your stylesheet:</p>
<pre><code>*, *::before, *::after {
    box-sizing: border-box;
}</code></pre>
<p>Now the width includes padding and border, which is far easier to calculate.</p>

<h3>Display types</h3>
<table>
<thead><tr><th>Value</th><th>Behaviour</th><th>Examples</th></tr></thead>
<tbody>
<tr><td><code>block</code></td><td>Starts on a new line and fills the width</td><td>p, h1, div, section</td></tr>
<tr><td><code>inline</code></td><td>Flows within a line of text; width and height do not apply</td><td>a, strong, span</td></tr>
<tr><td><code>inline-block</code></td><td>Flows in a line but accepts width, height and padding</td><td>buttons</td></tr>
<tr><td><code>none</code></td><td>The element is not shown and takes no space</td><td>hidden items</td></tr>
</tbody>
</table>

<h3>Units</h3>
<ul>
<li><code>px</code>: pixels, fixed size. Good for borders and small details.</li>
<li><code>%</code>: a percentage of the parent's size. Good for flexible widths.</li>
<li><code>rem</code>: a multiple of the root font size (usually 16px). <code>1.5rem</code> is 24px. It scales when users change their text size, so it is excellent for fonts and spacing.</li>
<li><code>em</code>: a multiple of the current element's font size.</li>
<li><code>vw</code> and <code>vh</code>: 1% of the screen width or height.</li>
</ul>

<h3>Backgrounds and borders</h3>
<pre><code>.hero {
    background-color: #e8f5e9;
    border-radius: 12px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}</code></pre>
<p>A background image uses <code>background-image: url("../images/banner.jpg");</code> with <code>background-size: cover;</code>. Because the CSS file is in the <code>css</code> folder, the path goes up one level with <code>../</code>.</p>

<h3>Try it yourself</h3>
<ol>
<li>Add the <code>box-sizing</code> rule to your stylesheet.</li>
<li>Create a <code>div</code> of class <code>card</code> with a heading and text, then style it using the rule above.</li>
<li>Use developer tools: hover over the card in the Elements panel to see the coloured margin, border and padding areas.</li>
<li>Switch units: set the card's font size to <code>1.25rem</code> and compare with <code>20px</code>. Then zoom the browser text and see which scales.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Every element is a box: content, padding, border, margin.</li>
<li>Use <code>box-sizing: border-box</code> everywhere.</li>
<li>Block elements fill the line; inline elements flow within text.</li>
<li>Prefer <code>rem</code> and <code>%</code> for flexible designs.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: In the rule  p { color: red; }  what is "color"?
- The selector
- The value
* The property
- The comment
= The selector is p, the property is color and the value is red.

Q: Which line correctly links a stylesheet stored at css/styles.css?
- A style element with a src attribute pointing to the file
* A link element with rel set to stylesheet and href set to css/styles.css
- A css element with an href attribute
- A script element with an href attribute
= The link element with rel set to stylesheet belongs in the head of the page.

Q: Which selector targets every element that has the class "note"?
- note
- #note
* .note
- &note
= A class selector starts with a full stop. A hash symbol is for IDs.

Q: Two rules set different colours on the same h2 with equal specificity. Which one wins?
- The one written first
* The one written later in the stylesheet
- The one with the shorter value
- The browser chooses randomly
= When specificity is equal, the later rule overrides the earlier one.

Q: Which order lists the box model layers from inside to outside?
- Margin, border, padding, content
- Content, margin, padding, border
* Content, padding, border, margin
- Padding, content, border, margin
= The content sits in the middle, wrapped by padding, then a border, then margin outside.

Q: Why is rem a good unit for font sizes?
- It always equals 1 pixel
* It scales with the root font size, so it respects the user's text size settings
- It is the only unit browsers understand
- It makes text bold
= rem is relative to the root font size, so layouts remain comfortable when users enlarge their text.

Q: Which pseudo-class styles an element when a keyboard user has selected it?
- :hover
- :first-child
* :focus
- :visited
= :focus shows keyboard users where they are. Never remove it without giving a clear alternative.

Q: What does margin: 0 auto; do to a block with a set width?
- Removes the border
* Centres the block horizontally
- Adds padding of auto
- Makes it inline
= Zero top and bottom margin plus automatic left and right margin centres the box.
QUIZ,
        ],
        [
            'title'   => 'Module 4: Layout and responsive design',
            'summary' => 'Arrange content with Flexbox and Grid, make pages work on phones, and build accessible navigation and cards.',
            'lessons' => [
                [
                    'title'   => 'Flexbox: rows, columns and navigation bars',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to place items side by side, space them evenly and build a navigation bar. <strong>Flexbox</strong> (flexible box layout) is the modern CSS tool for arranging items in one direction, either a row or a column.</p>

<h3>The basic idea</h3>
<p>You give a parent element <code>display: flex;</code>. Its direct children become <strong>flex items</strong> and line up in a row. The parent is the <strong>flex container</strong>.</p>
<pre><code>&lt;nav class&#61;"menu"&gt;
    &lt;a href="index.html"&gt;Home&lt;/a&gt;
    &lt;a href="about.html"&gt;About&lt;/a&gt;
    &lt;a href="contact.html"&gt;Contact&lt;/a&gt;
&lt;/nav&gt;</code></pre>
<pre><code>.menu {
    display: flex;
    gap: 1rem;
    justify-content: center;
    align-items: center;
    background-color: #1b5e20;
    padding: 0.75rem;
}
.menu a {
    color: white;
    text-decoration: none;
    padding: 0.5rem 1rem;
}
.menu a:hover {
    background-color: #2e7d32;
}</code></pre>

<h3>The most useful properties</h3>
<table>
<thead><tr><th>Property</th><th>What it controls</th><th>Common values</th></tr></thead>
<tbody>
<tr><td><code>flex-direction</code></td><td>Main direction</td><td>row (default), column</td></tr>
<tr><td><code>justify-content</code></td><td>Spacing along the main direction</td><td>flex-start, center, flex-end, space-between, space-around</td></tr>
<tr><td><code>align-items</code></td><td>Alignment across the main direction</td><td>stretch, center, flex-start, flex-end</td></tr>
<tr><td><code>gap</code></td><td>Space between items</td><td>1rem</td></tr>
<tr><td><code>flex-wrap</code></td><td>Whether items move to a new line</td><td>nowrap, wrap</td></tr>
</tbody>
</table>
<p>On an item you can use <code>flex: 1;</code> to make it share the free space equally with its siblings. Two items with <code>flex: 1</code> become two equal columns.</p>

<h3>Centring things</h3>
<p>Centring used to be a headache. With Flexbox it is three lines:</p>
<pre><code>.centre-box {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 200px;
}</code></pre>

<h3>Wrapping on small screens</h3>
<p>With <code>flex-wrap: wrap;</code> the items drop to the next line when there is no room. Combined with a minimum size, such as <code>flex: 1 1 250px;</code> (grow, shrink, start at 250px), you get a layout that rearranges itself on narrow screens without any extra code.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create the navigation bar above in your project and style the link hover.</li>
<li>Change <code>justify-content</code> to <code>space-between</code> and see the difference, then try <code>flex-end</code>.</li>
<li>Add a second container with three <code>div</code> items and give them <code>flex: 1;</code>.</li>
<li>Switch the direction to <code>column</code> and then back again.</li>
</ol>

<h3>Key points</h3>
<ul>
<li><code>display: flex</code> on a parent arranges its children in a row or column.</li>
<li><code>justify-content</code> works along the main direction; <code>align-items</code> across it.</li>
<li><code>gap</code> gives clean spacing and <code>flex-wrap</code> allows items to move down.</li>
<li>Flexbox is ideal for menus, button rows and centring.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Simple Grid and positioning basics',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to create a grid of cards and use positioning for small effects like a fixed header. Where Flexbox lines items up in one direction, <strong>CSS Grid</strong> arranges content in rows and columns at once.</p>

<h3>A basic grid</h3>
<p>Give the parent <code>display: grid;</code> and describe the columns with <code>grid-template-columns</code>.</p>
<pre><code>.grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1rem;
}</code></pre>
<p>The unit <code>fr</code> means a "fraction" of the free space. The code above creates three equal columns. Each new child goes into the next free cell, filling a row, then starting another.</p>

<h3>A grid that adapts without media queries</h3>
<p>This single line makes as many columns as will fit, each at least 220px wide:</p>
<pre><code>.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
}</code></pre>
<p>On a wide screen you may see four columns; on a phone, just one. The browser does the thinking.</p>

<h3>Flexbox or Grid?</h3>
<table>
<thead><tr><th>Use Flexbox for</th><th>Use Grid for</th></tr></thead>
<tbody>
<tr><td>A row of buttons or menu links</td><td>A gallery or a set of cards</td></tr>
<tr><td>Centring one item</td><td>The overall page layout with a sidebar</td></tr>
<tr><td>Content-driven spacing in one direction</td><td>Rows and columns that must line up</td></tr>
</tbody>
</table>
<p>They work well together: a grid for the page, Flexbox inside each card.</p>

<h3>Positioning basics</h3>
<p>Normally elements stay in the <strong>normal flow</strong>, one after another. The <code>position</code> property changes this:</p>
<ul>
<li><code>static</code>: the default; no special behaviour.</li>
<li><code>relative</code>: stays in flow, but can be nudged with <code>top</code>, <code>left</code> and so on. It also becomes a reference point for absolutely positioned children.</li>
<li><code>absolute</code>: removed from the flow and placed relative to its nearest positioned parent. Useful for a small badge on a card corner.</li>
<li><code>fixed</code>: stays in the same place on the screen while scrolling.</li>
<li><code>sticky</code>: scrolls normally, then sticks at an edge you set.</li>
</ul>
<pre><code>header {
    position: sticky;
    top: 0;
    background-color: white;
    z-index: 10;
}</code></pre>
<p><code>z-index</code> decides which overlapping element is on top; a higher number sits above. Use positioning sparingly: layouts built mostly with absolute positions break easily on different screen sizes.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create six <code>div</code> elements of class <code>box</code> inside a grid container.</li>
<li>Use the three-column grid and then switch to the <code>auto-fit</code> version.</li>
<li>Make your header sticky and scroll a long page to test it.</li>
<li>Resize the browser window to see how many columns appear at each width.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Grid places items in rows and columns; <code>fr</code> shares free space.</li>
<li><code>repeat(auto-fit, minmax(...))</code> gives a responsive grid with no extra code.</li>
<li>Use Grid for two-dimensional layouts and Flexbox for one-dimensional ones.</li>
<li>Positioning is for small adjustments, not whole layouts.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Making your page mobile friendly',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to make one page look good on a phone, a tablet and a computer. In Uganda and across Africa, most people reach the web on a phone, so a page that fails on a small screen fails most of its visitors.</p>

<h3>Step 1: the viewport meta tag</h3>
<p>Without help, a phone pretends to be a wide desktop screen and shrinks your page into tiny text. This line in the <code>head</code> fixes it, and every page must have it:</p>
<pre><code>&lt;meta name="viewport" content="width=device-width, initial-scale=1"&gt;</code></pre>

<h3>Step 2: fluid images</h3>
<p>An image wider than the screen causes sideways scrolling. Use this rule so images shrink to fit their container but never grow beyond their real size:</p>
<pre><code>img {
    max-width: 100%;
    height: auto;
}</code></pre>

<h3>Step 3: design for small screens first</h3>
<p>The <strong>mobile-first</strong> approach writes the simple phone layout as the default CSS, then adds improvements for bigger screens using media queries. This gives phones fewer rules to process and keeps the code simple.</p>

<h3>Step 4: media queries</h3>
<p>A <strong>media query</strong> applies CSS only when a condition is true, usually a minimum screen width. The widths at which the layout changes are called <strong>breakpoints</strong>.</p>
<pre><code>.columns {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

@media (min-width: 768px) {
    .columns {
        flex-direction: row;
    }
}</code></pre>
<p>Here the columns stack on phones. When the screen is at least 768px wide, they sit side by side. Choose breakpoints where your content starts to look bad, not just at famous device sizes. Common ones are about 600px and 900px.</p>

<h3>Other good habits for mobile</h3>
<ul>
<li>Make buttons and links large enough to tap with a thumb, roughly 44px high.</li>
<li>Use readable text: at least 16px (1rem) for body text.</li>
<li>Keep pages light. Many visitors pay for data, so compress images and avoid heavy files.</li>
<li>Avoid fixed widths in pixels for main containers; use <code>max-width</code> instead.</li>
</ul>
<pre><code>.container {
    width: 100%;
    max-width: 960px;
    margin: 0 auto;
    padding: 0 1rem;
}</code></pre>

<h3>Testing</h3>
<p>Open the developer tools with <code>F12</code>, click the device toggle icon, and choose a phone size. Drag the width slowly and watch for breaking points. Always finish by opening the page on a real phone.</p>

<h3>Try it yourself</h3>
<ol>
<li>Confirm every page you have made has the viewport tag.</li>
<li>Add the image rule and put a very large image on a page to check it fits.</li>
<li>Build two columns that stack on small screens and sit side by side from 768px.</li>
<li>Test with the device toggle, then open the page on your phone (copy the folder to it, or send the files, then open <code>index.html</code>).</li>
</ol>

<h3>Key points</h3>
<ul>
<li>The viewport meta tag is essential on every page.</li>
<li>Use <code>max-width: 100%</code> and <code>height: auto</code> for fluid images.</li>
<li>Start with the phone layout and add media queries with <code>min-width</code>.</li>
<li>Test on real devices and think about data costs.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Cards, navigation and accessibility',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a responsive card layout and check your pages against the basic rules of accessibility. <strong>Accessibility</strong> means that everyone can use your site, including people with poor eyesight, hearing loss, limited movement, or only a keyboard. It also helps everyone in difficult conditions, like glare on a phone screen.</p>

<h3>A responsive card layout</h3>
<pre><code>&lt;section class&#61;"cards"&gt;
    &lt;article class&#61;"card"&gt;
        &lt;img src="images/typing.jpg" alt="Learners practising typing in a classroom"&gt;
        &lt;h3&gt;Typing class&lt;/h3&gt;
        &lt;p&gt;Learn to type quickly and correctly.&lt;/p&gt;
        &lt;a href="contact.html"&gt;Join the typing class&lt;/a&gt;
    &lt;/article&gt;
    &lt;!-- add more articles of the same shape --&gt;
&lt;/section&gt;</code></pre>
<pre><code>.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.25rem;
    padding: 1rem;
}
.card {
    background-color: #ffffff;
    border: 1px solid #d0d7d0;
    border-radius: 10px;
    padding: 1rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}
.card img {
    width: 100%;
    height: auto;
    border-radius: 6px;
}</code></pre>

<h3>Accessibility checklist</h3>
<table>
<thead><tr><th>Area</th><th>What to do</th></tr></thead>
<tbody>
<tr><td>Contrast</td><td>Make text clearly different from its background. Do not rely on colour alone to show meaning (add an icon or word).</td></tr>
<tr><td>Alt text</td><td>Describe meaningful images; use an empty alt for decoration.</td></tr>
<tr><td>Labels</td><td>Link every form field to a visible label.</td></tr>
<tr><td>Keyboard</td><td>Everything must work with <code>Tab</code>, <code>Shift+Tab</code>, <code>Enter</code> and the space bar. Never remove the focus outline unless you replace it.</td></tr>
<tr><td>Headings</td><td>Use them in order so screen reader users can jump around.</td></tr>
<tr><td>Language</td><td>Set <code>lang</code> on the <code>html</code> tag.</td></tr>
<tr><td>Link text</td><td>Make it meaningful on its own.</td></tr>
<tr><td>Text size</td><td>Use <code>rem</code> so users can enlarge text.</td></tr>
</tbody>
</table>

<h3>A visible focus style</h3>
<pre><code>a:focus, button:focus, input:focus, select:focus, textarea:focus {
    outline: 3px solid #ff9800;
    outline-offset: 2px;
}</code></pre>

<h3>Test it</h3>
<p>Put your mouse away and use only the <code>Tab</code> key to move through the whole page. Can you always see where you are? Can you reach every link and button? Next, zoom the browser to 200% and check that nothing is cut off. Some browsers also have an accessibility panel in their developer tools to check contrast.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create three cards with an image, heading, short text and a link each.</li>
<li>Check that the card grid becomes a single column on a narrow window.</li>
<li>Add the focus style and navigate the whole page using only the keyboard.</li>
<li>Go through the accessibility table and note one improvement for each row on one of your pages.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A grid with <code>auto-fit</code> and <code>minmax</code> makes a simple responsive card layout.</li>
<li>Good contrast, alt text, labels and keyboard access are the basics of accessibility.</li>
<li>Never rely on colour alone and never remove focus outlines.</li>
<li>Test with the keyboard and at 200% zoom.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which CSS turns an element into a flex container?
- position: flex;
- flex: container;
* display: flex;
- layout: flex;
= display: flex on the parent makes its direct children flex items.

Q: In a row-direction flex container, which property spaces items along the row?
- align-items
* justify-content
- text-align
- float
= justify-content works along the main direction (the row), and align-items works across it.

Q: What does grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); achieve?
- Exactly three columns of 220px
- A single column that is always 220px wide
* As many columns as fit, each at least 220px wide, sharing the leftover space
- Columns that never change
= The browser fits as many columns as possible, so a phone gets one column and a wide screen gets several.

Q: Why is the viewport meta tag important?
- It adds a background colour
* It makes phones use their real screen width instead of pretending to be a wide desktop
- It loads fonts faster
- It enables JavaScript
= Without it, mobile browsers shrink the whole desktop layout and the text becomes tiny.

Q: Which CSS keeps an image from overflowing its container?
- img { width: 2000px; }
- img { overflow: hidden; }
* img { max-width: 100%; height: auto; }
- img { display: none; }
= max-width: 100% lets the image shrink to fit, and height: auto keeps the proportions.

Q: In mobile-first CSS, which media query adds styles for larger screens?
- @media (max-width: 0)
* @media (min-width: 768px)
- @media (phone)
- @media (width: 100%)
= Mobile-first starts with the small-screen styles and adds min-width queries as the screen grows.

Q: Which practices improve accessibility? (choose all that apply)
* Visible keyboard focus styles
* Good contrast between text and background
- Removing the focus outline because it looks untidy
- Using colour alone to show an error
= Keyboard users need to see where they are, and colour alone excludes people who cannot distinguish it.
QUIZ,
        ],
        [
            'title'   => 'Module 5: Your first website',
            'summary' => 'Plan and build a complete three-page website, test it, and learn how to publish it and where to go next.',
            'lessons' => [
                [
                    'title'   => 'Planning a small website',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will have a clear plan for a small website. Professionals never start with code; they start with questions. A short plan prevents wasted work and gives you a site that actually serves its visitors.</p>

<h3>Step 1: purpose and audience</h3>
<p>Answer in one sentence each: <em>Who is this for? What do they want to know or do? What do I want them to do next?</em> In this module we will use an example: <strong>Gulu Youth Tech Club</strong>, a group that teaches computer skills on Saturdays. Visitors are young people and parents. They want to know what is taught, when and how to join. The club wants them to send a message to enrol.</p>

<h3>Step 2: list the pages</h3>
<p>Keep a first site small. Our club needs three pages:</p>
<table>
<thead><tr><th>File</th><th>Purpose</th><th>Main content</th></tr></thead>
<tbody>
<tr><td><code>index.html</code></td><td>Home</td><td>Welcome, three activity cards, call to action</td></tr>
<tr><td><code>about.html</code></td><td>About us</td><td>Mission, a timetable table</td></tr>
<tr><td><code>contact.html</code></td><td>Contact</td><td>Enrolment form and contact details</td></tr>
</tbody>
</table>

<h3>Step 3: sketch the layout</h3>
<p>Draw each page on paper as boxes: header with menu on top, main content, footer at the bottom. Draw the phone version first. A sketch takes five minutes and shows problems before you write a line of code.</p>

<h3>Step 4: gather content</h3>
<p>Write the real text and prepare images before building. Placeholder text like "Lorem ipsum" hides problems. Resize photographs to a sensible width (around 800px) and save them as compressed JPG files in the <code>images</code> folder. Choose a palette and fonts now.</p>

<h3>Step 5: set up the folder</h3>
<pre><code>gulu-youth-club/
    index.html
    about.html
    contact.html
    css/
        styles.css
    images/
        typing.jpg
        coding.jpg
        football.jpg</code></pre>

<h3>Step 6: a plan for the code</h3>
<p>All three pages share the same header, menu and footer, and one stylesheet. So build the page template once, get it right, then copy it for the other pages and change only the main content. (Bigger sites avoid copying by using tools that build pages from templates; you will meet them later.)</p>

<h3>Try it yourself</h3>
<ol>
<li>Choose a real or imaginary project: a youth group, a school club, or a small shop or salon.</li>
<li>Write the purpose, audience and the one action you want visitors to take.</li>
<li>List three pages and the main content of each in a table.</li>
<li>Sketch the phone layout of the home page on paper.</li>
<li>Create the project folder and gather at least two images.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Start with purpose, audience and the action you want visitors to take.</li>
<li>Keep the first version small and focused.</li>
<li>Sketch on paper, phone version first.</li>
<li>Prepare real content and optimised images before coding.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Build the stylesheet and the home page',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>In this lesson you will write the shared stylesheet and the home page of the Gulu Youth Tech Club site. Type the code yourself rather than copying and pasting; typing builds understanding. Replace the details and image names with your own project.</p>

<h3>css/styles.css</h3>
<pre><code>*, *::before, *::after { box-sizing: border-box; }

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 1.1rem;
    line-height: 1.6;
    color: #222222;
    background-color: #f6f8f4;
}
img { max-width: 100%; height: auto; }
a { color: #0b5d1e; }
a:hover { color: #7a2e00; }
a:focus, button:focus, input:focus, select:focus, textarea:focus {
    outline: 3px solid #ff9800;
    outline-offset: 2px;
}

header { background-color: #0b5d1e; color: white; padding: 1rem; }
header h1 { margin: 0 0 0.5rem; font-size: 1.6rem; }
.menu { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.menu a { color: white; text-decoration: none; padding: 0.5rem 0.9rem; border-radius: 6px; }
.menu a:hover { background-color: #14802d; color: white; }

main { max-width: 960px; margin: 0 auto; padding: 1rem; }
.hero { background-color: #e3f1e0; border-radius: 12px; padding: 1.5rem; }
.button {
    display: inline-block;
    background-color: #0b5d1e;
    color: white;
    padding: 0.75rem 1.25rem;
    border-radius: 8px;
    text-decoration: none;
}
.button:hover { background-color: #7a2e00; color: white; }

.cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; }
.card { background-color: white; border: 1px solid #d0d7d0; border-radius: 10px; padding: 1rem; }

table { border-collapse: collapse; width: 100%; background-color: white; }
th, td { border: 1px solid #c8d0c8; padding: 0.6rem; text-align: left; }
th { background-color: #e3f1e0; }

footer { text-align: center; padding: 1rem; background-color: #dfe6dc; }

@media (min-width: 768px) {
    header h1 { font-size: 2.2rem; }
}</code></pre>

<h3>index.html</h3>
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1"&gt;
    &lt;title&gt;Gulu Youth Tech Club - Home&lt;/title&gt;
    &lt;link rel="stylesheet" href="css/styles.css"&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;header&gt;
        &lt;h1&gt;Gulu Youth Tech Club&lt;/h1&gt;
        &lt;nav class&#61;"menu"&gt;
            &lt;a href="index.html"&gt;Home&lt;/a&gt;
            &lt;a href="about.html"&gt;About&lt;/a&gt;
            &lt;a href="contact.html"&gt;Contact&lt;/a&gt;
        &lt;/nav&gt;
    &lt;/header&gt;
    &lt;main&gt;
        &lt;section class&#61;"hero"&gt;
            &lt;h2&gt;Learn computer skills every Saturday&lt;/h2&gt;
            &lt;p&gt;Free classes for young people in Gulu. No experience needed.&lt;/p&gt;
            &lt;a class&#61;"button" href="contact.html"&gt;Join the club&lt;/a&gt;
        &lt;/section&gt;
        &lt;section&gt;
            &lt;h2&gt;What we do&lt;/h2&gt;
            &lt;div class&#61;"cards"&gt;
                &lt;article class&#61;"card"&gt;
                    &lt;h3&gt;Typing&lt;/h3&gt;
                    &lt;p&gt;Learn to type fast and accurately.&lt;/p&gt;
                &lt;/article&gt;
                &lt;article class&#61;"card"&gt;
                    &lt;h3&gt;Coding&lt;/h3&gt;
                    &lt;p&gt;Build your first web pages.&lt;/p&gt;
                &lt;/article&gt;
                &lt;article class&#61;"card"&gt;
                    &lt;h3&gt;Teamwork&lt;/h3&gt;
                    &lt;p&gt;Work on projects together.&lt;/p&gt;
                &lt;/article&gt;
            &lt;/div&gt;
        &lt;/section&gt;
    &lt;/main&gt;
    &lt;footer&gt;
        &lt;p&gt;Gulu Youth Tech Club, Gulu, Uganda&lt;/p&gt;
    &lt;/footer&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>

<h3>What to notice</h3>
<p>The same header and footer will appear on every page, and the menu is built with Flexbox so it wraps on narrow phones. The cards use the adaptive grid. There are no pixel widths on the page; <code>max-width</code> keeps content to a comfortable reading size.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create the stylesheet and the home page with your own project's name and wording.</li>
<li>Open it and shrink the browser window until it looks like a phone. Does the menu stay usable?</li>
<li>Add an image with proper alt text to one card, in a way that fits its content.</li>
<li>Change the main colour in the three places it appears and see the whole look change.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>One shared stylesheet keeps all pages consistent.</li>
<li>Semantic structure plus Flexbox and Grid gives a responsive page with little code.</li>
<li>Colours repeated in a stylesheet are easy to change when you plan them early.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The About and Contact pages',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>In this lesson you will build the remaining two pages by reusing the template of the home page. Reusing a tested structure is how real developers work, and it keeps your site consistent.</p>

<h3>Step by step</h3>
<ol>
<li>In VS Code, copy <code>index.html</code> and paste it twice. Rename the copies <code>about.html</code> and <code>contact.html</code>.</li>
<li>In each file, change the <code>&lt;title&gt;</code>.</li>
<li>Replace everything inside <code>&lt;main&gt;</code> with the new content below.</li>
<li>Leave the header, the menu and the footer unchanged.</li>
</ol>

<h3>The main content of about.html</h3>
<pre><code>&lt;main&gt;
    &lt;h2&gt;About the club&lt;/h2&gt;
    &lt;p&gt;We are a group of volunteers who teach computer skills to
    young people in Gulu. Our classes are free, and we believe
    that everyone can learn to use technology.&lt;/p&gt;

    &lt;h2&gt;Our timetable&lt;/h2&gt;
    &lt;table&gt;
        &lt;caption&gt;Weekly classes&lt;/caption&gt;
        &lt;thead&gt;
            &lt;tr&gt;&lt;th&gt;Time&lt;/th&gt;&lt;th&gt;Class&lt;/th&gt;&lt;th&gt;Room&lt;/th&gt;&lt;/tr&gt;
        &lt;/thead&gt;
        &lt;tbody&gt;
            &lt;tr&gt;&lt;td&gt;9:00 am&lt;/td&gt;&lt;td&gt;Typing&lt;/td&gt;&lt;td&gt;Room 1&lt;/td&gt;&lt;/tr&gt;
            &lt;tr&gt;&lt;td&gt;11:00 am&lt;/td&gt;&lt;td&gt;Coding&lt;/td&gt;&lt;td&gt;Room 2&lt;/td&gt;&lt;/tr&gt;
            &lt;tr&gt;&lt;td&gt;2:00 pm&lt;/td&gt;&lt;td&gt;Team project&lt;/td&gt;&lt;td&gt;Room 1&lt;/td&gt;&lt;/tr&gt;
        &lt;/tbody&gt;
    &lt;/table&gt;
&lt;/main&gt;</code></pre>

<h3>The main content of contact.html</h3>
<pre><code>&lt;main&gt;
    &lt;h2&gt;Join the club&lt;/h2&gt;
    &lt;p&gt;Fill in the form and we will contact you before the next class.&lt;/p&gt;
    &lt;form action="#" method="post"&gt;
        &lt;p&gt;
            &lt;label for="fullname"&gt;Full name&lt;/label&gt;&lt;br&gt;
            &lt;input type="text" id="fullname" name="fullname" required&gt;
        &lt;/p&gt;
        &lt;p&gt;
            &lt;label for="phone"&gt;Phone number&lt;/label&gt;&lt;br&gt;
            &lt;input type="tel" id="phone" name="phone"&gt;
        &lt;/p&gt;
        &lt;p&gt;
            &lt;label for="interest"&gt;Class you like&lt;/label&gt;&lt;br&gt;
            &lt;select id="interest" name="interest"&gt;
                &lt;option value="typing"&gt;Typing&lt;/option&gt;
                &lt;option value="coding"&gt;Coding&lt;/option&gt;
            &lt;/select&gt;
        &lt;/p&gt;
        &lt;button type="submit" class&#61;"button"&gt;Send&lt;/button&gt;
    &lt;/form&gt;
    &lt;p&gt;Or email us: &lt;a href="mailto:info@example.com"&gt;info@example.com&lt;/a&gt;&lt;/p&gt;
&lt;/main&gt;</code></pre>

<h3>A note about the form</h3>
<p>The attribute <code>action="#"</code> is a placeholder: the form looks real but sends the data nowhere. To receive messages from a static site, you would connect the form to a form-handling service or to server code. Until then, give visitors an email address and phone number as another way to reach you.</p>

<h3>Style the form a little</h3>
<p>Add these lines to <code>styles.css</code> so fields are comfortable on a phone:</p>
<pre><code>input, select, textarea {
    width: 100%;
    max-width: 24rem;
    padding: 0.6rem;
    font-size: 1rem;
    border: 1px solid #8a948a;
    border-radius: 6px;
}
button.button { border: none; cursor: pointer; font-size: 1rem; }</code></pre>

<h3>Try it yourself</h3>
<ol>
<li>Build both pages with your own project's content.</li>
<li>Click through all three pages using the menu. Does every link work?</li>
<li>Add a <code>textarea</code> with a label for a message to your form.</li>
<li>Run each page through the HTML validator and fix every error.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Copy a tested page as a template, then change only the content that is different.</li>
<li>Keep the title unique on every page.</li>
<li>A static form needs a service or server to receive its data.</li>
<li>Validate every page before you call the site finished.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Testing, publishing and where to go next',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will know how to test your site thoroughly, how websites are published, and how to continue learning. A site only matters when other people can see it, and a few careful checks first will save you from embarrassing mistakes.</p>

<h3>Test before you publish</h3>
<ol>
<li><strong>Click every link</strong> on every page, including the logo and footer links.</li>
<li><strong>Check images.</strong> Do all load? Are the paths in lower case? Is each alt text meaningful?</li>
<li><strong>Test two or more browsers</strong> (for example Chrome and Firefox).</li>
<li><strong>Test on a real phone</strong> in portrait and landscape.</li>
<li><strong>Use only the keyboard</strong> to move through the page.</li>
<li><strong>Validate</strong> the HTML. Check CSS for typing errors.</li>
<li><strong>Proofread.</strong> Ask a friend to read it and to try to find the contact details.</li>
<li><strong>Check speed.</strong> Large images are the usual cause of slow pages.</li>
</ol>

<h3>Domain names and hosting</h3>
<p>To make your site public, two things are needed. <strong>Web hosting</strong> is space on a server that is always online. A <strong>domain name</strong> is the easy-to-remember address (like <code>example.org</code>) that points to that server. Domains are rented, usually per year, from companies called registrars. Names ending in <code>.ug</code> are Uganda's country domain.</p>

<h3>Free and paid options in general terms</h3>
<table>
<thead><tr><th>Option</th><th>What it does</th><th>Good for</th></tr></thead>
<tbody>
<tr><td>GitHub Pages</td><td>Serves a static site from files stored in a GitHub repository, free of charge, usually at an address provided by the service</td><td>Portfolios, practice, simple sites</td></tr>
<tr><td>Netlify and similar services</td><td>Serve static sites and can also handle simple forms; usually a free plan exists</td><td>Small business and club sites</td></tr>
<tr><td>Shared web hosting</td><td>Paid space on a server where you upload files, often with email and a domain</td><td>Sites that need more features</td></tr>
</tbody>
</table>
<p>These services change their screens and plans often, so follow their current official guides when you publish. The general steps are always the same: create an account, upload (or connect) your project folder, and check that <code>index.html</code> is at the top of it. Never put passwords or private information in your site files.</p>

<h3>Publishing checklist</h3>
<ul>
<li>The home page is called <code>index.html</code> and sits at the top of the folder.</li>
<li>All paths are relative and in lower case.</li>
<li>Every page has a title, a language and the viewport tag.</li>
<li>Contact details and spelling are correct.</li>
<li>You only used images you have permission to use.</li>
<li>You tested the live address on your phone after publishing.</li>
</ul>

<h3>Where to go next</h3>
<p>You now understand how pages are built. Good next steps are: <strong>JavaScript</strong>, which adds behaviour such as menus, calculators and form checks; <strong>Git</strong>, which tracks changes to your code; CSS topics such as animations and variables; and later, frameworks and tools that speed up big projects, plus back end languages for sites that store data. Keep building small real projects, because that is the fastest way to learn.</p>

<h3>Try it yourself</h3>
<ol>
<li>Go through the testing list above for your three-page site and write down every problem you find.</li>
<li>Fix each problem and test again.</li>
<li>Ask two people to open the site on their phones and tell you what confused them.</li>
<li>Choose one hosting option and read its current official introduction to find out whether it fits your project.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Test links, images, browsers, phones, keyboard and speed before publishing.</li>
<li>Hosting stores your site; a domain name is its address.</li>
<li>Free static hosting services exist, but follow their current official guides.</li>
<li>Next steps are JavaScript, Git and bigger projects.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What should you do first when planning a website?
- Choose the fonts
- Start writing code
* Decide who it is for and what visitors should do
- Buy a domain name
= A clear purpose and audience guide every later decision.

Q: Why build the template page once and reuse it for the other pages?
- Browsers refuse to open different pages
* It keeps the header, menu and footer consistent and saves time
- It makes the images smaller
- HTML does not allow different content
= Copying a tested structure keeps pages consistent. You change only the main content.

Q: A form in a static HTML page uses action="#". What does that mean in practice?
- It sends the data to your email automatically
- It stores the data in the browser
* It is a placeholder: the data is not sent anywhere useful
- It encrypts the form
= A form needs a service or server code to receive its data.

Q: What is the difference between hosting and a domain name?
- They are the same thing
* Hosting is the space on a server that stores the site; the domain is its address
- Hosting is the address; the domain is the server
- A domain is only for emails
= The domain points visitors to the server where your files are stored.

Q: Which checks belong in a pre-publishing test? (choose all that apply)
* Click all links
* View the site on a real phone
* Use the Tab key to navigate
- Delete the alt text to make pages smaller
= Alt text should stay. Links, devices and keyboard access are all important checks.

Q: Which file should sit at the top of your published folder as the home page?
- home.txt
- main.doc
* index.html
- website.exe
= Servers show index.html by default when someone visits the address.

Q: What is a good next step after finishing this course?
- Stop practising so you do not forget
* Learn JavaScript and keep building small projects
- Copy another website completely
- Switch to Word for design
= JavaScript adds behaviour to your pages, and projects turn knowledge into skill.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What does a browser do when you press Enter after typing a URL?
- Sends your files to a server
* Requests the page from a server and displays the files it receives
- Creates a new website for you
- Compiles the page into a program
= The browser asks the server for the files and then draws the page.

Q: HTTPS differs from HTTP because it...
- Is faster in all cases
- Allows larger images
* Encrypts the data travelling between browser and server
- Is only used for forms
= HTTPS adds encryption so others cannot read the traffic.

Q: Which of these is part of the back end?
- The colour of a button
- A heading
- A CSS file
* Code on the server that saves a registration in a database
= The back end works on the server, out of sight of the visitor.

Q: Why is it best to use lower-case names without spaces for files?
* Servers can treat upper and lower case as different files, and spaces cause problems in addresses
- Browsers cannot read capital letters
- It makes the images smaller
- It is required by Windows
= Consistent, simple names prevent broken links.

Q: What is the first line of a modern HTML page?
- The opening html tag
- The opening head tag
- The title element
* The doctype declaration
= The doctype tells the browser to use modern standards.

Q: Where do you put the page title shown in the browser tab?
- In the body as an h1
* In a title element inside the head
- In a CSS file
- In the footer
= The title element belongs in the head.

Q: How many h1 headings should a normal page have?
- None
- As many as possible
* One main h1, then h2 and h3 for sections
- Exactly six
= One h1 names the page; the others organise sections in order.

Q: What does the href attribute do?
- Sets the font
* Holds the destination of a link
- Names an image
- Starts a form
= href is the "hypertext reference" for the link target.

Q: Which is the best link text?
- Click here
- Link
* Read our 2025 annual report
- Here
= Descriptive link text makes sense when read alone, which helps screen readers and search engines.

Q: Why is alt text important? (choose all that apply)
* Screen readers read it aloud
* It appears when the image cannot load
- It makes the image larger
- It changes the colour of the image
= Alt text supports accessibility and slow connections.

Q: The element that should contain the main navigation links is...
- section
* nav
- aside
- article
= nav is the semantic element for primary navigation.

Q: What are tables for?
- Page layout
- Placing menus
* Showing data in rows and columns
- Adding colour
= Layout should be done with CSS; tables are for data.

Q: Which attribute links a label to an input?
- name on the label
- type on the label
* for on the label matching the input's id
- class on both
= The label's for value must equal the id of the field.

Q: Which is NOT a way of adding CSS?
- External stylesheet
- Internal style element
- Inline style attribute
* Putting CSS inside a comment in the body
= Comments are ignored by the browser, so the CSS would do nothing.

Q: What does this select?   .menu a { }
- Every element with id menu
- Links and menus separately
* Links that are inside an element with class menu
- Only the first link on the page
= A space means "inside", and the dot marks a class.

Q: Two rules apply to the same element with the same specificity. Which wins?
- The first in the file
* The last one in the file
- The shorter rule
- Neither
= Later rules override earlier ones when specificity is equal.

Q: What does padding do?
- Adds space outside the border
* Adds space between the content and the border
- Makes the border thicker
- Hides the content
= Padding is inside the border; margin is outside.

Q: What does box-sizing: border-box do?
- Draws a border round everything
* Includes padding and border in the stated width and height
- Removes margins
- Makes boxes square
= It makes widths predictable and layouts easier to calculate.

Q: Which unit scales with the user's chosen text size?
- px
* rem
- cm
- pt
= rem is relative to the root font size, which users can change.

Q: Which Flexbox property spaces items along the main axis?
- align-items
* justify-content
- text-align
- display
= justify-content distributes items along the main direction.

Q: Which line best creates a responsive card grid?
- grid-template-columns: 300px 300px 300px;
- display: block;
* grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
- position: absolute;
= auto-fit with minmax adds or removes columns as the space changes.

Q: Which line must be in the head of every page that should work well on phones?
- The meta tag that sets the character set to UTF-8
* The meta tag named viewport with width set to device-width
- A link tag with rel set to phone
- A title element containing the word Phone
= The viewport tag stops phones from shrinking the page as if it were a desktop screen.

Q: In mobile-first design you...
* Write the small-screen CSS first and use min-width media queries for larger screens
- Design the desktop version, then delete parts
- Avoid media queries altogether
- Use only fixed pixel widths
= Starting small keeps CSS simple and phones fast.

Q: Which are good accessibility practices? (choose all that apply)
* Using strong text contrast
* Making everything usable with the keyboard
* Giving form fields labels
- Removing focus outlines to look cleaner
= The first three include more people. Removing focus outlines excludes keyboard users.

Q: What does position: sticky; top: 0; do to a header?
- Removes it from the page
* Lets it scroll normally, then stick to the top of the screen
- Moves it to the bottom
- Hides it on phones
= Sticky elements stay visible once they reach the set edge.

Q: Hosting and a domain are different. Which statement is correct?
- A domain stores the website files
* Hosting provides the server space; the domain name is the address pointing to it
- Hosting is the same as a browser
- You cannot publish without a paid domain in any case
= Free static hosts exist, which usually give you an address provided by the service.

Q: Before publishing your three-page site you should... (choose all that apply)
* Click every link
* Test on a phone and in more than one browser
* Validate the HTML
- Remove all alt text
= Careful testing finds broken links and layout problems before visitors do.

Q: Which skill would you learn next to add behaviour such as interactive menus?
- Photoshop
* JavaScript
- Microsoft Access
- Typing
= JavaScript is the language that adds behaviour to web pages.
QUIZ,
];
