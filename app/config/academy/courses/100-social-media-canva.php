<?php
return [
    'slug'        => 'social-media-canva',
    'title'       => 'Social Media Management and Design with Canva',
    'category'    => 'design',
    'level'       => 'beginner',
    'icon'        => 'star',
    'sort'        => 100,
    'summary'     => 'Learn to run social media pages and design posters, posts, videos and flyers with Canva, even if you have never designed anything before.',
    'description' => '<p>Almost every business, school and organisation now needs someone who can manage its social media and make good-looking posts. This course teaches both skills from the beginning: how to plan and run a page, and how to design simple, clear graphics with the free design tool Canva.</p><p>You will learn the basic rules of design that make any layout look better, then practise step by step in Canva. You will create posts, stories, flyers, a CV, a logo idea, a presentation and a short video, and finish with a four-week content calendar.</p><p>You will also learn how to behave responsibly online, including copyright, consent for photos and the protection of children.</p>',
    'outcomes'    => [
        'Describe the work of a social media manager and set up a professional profile',
        'Plan posts with content pillars, a schedule and a content calendar',
        'Apply layout, contrast, hierarchy, colour and typography rules to any design',
        'Use the Canva editor to create, edit, resize, export and share designs',
        'Design social posts, stories, flyers, a simple logo concept, a CV and a presentation',
        'Make a short video or reel and a chart in Canva',
        'Write captions and hashtags, and respond to comments and criticism well',
        'Follow copyright, consent and child safeguarding rules in everything you publish',
    ],
    'requirements' => [
        'A smartphone, laptop or computer with internet access',
        'No experience needed',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Canva Design School', 'url' => 'https://www.canva.com/designschool/'],
        ['title' => 'Canva Help Centre', 'url' => 'https://www.canva.com/help/'],
        ['title' => 'Meta Business Help Centre', 'url' => 'https://www.facebook.com/business/help'],
        ['title' => 'Google Skillshop', 'url' => 'https://skillshop.withgoogle.com'],
    ],
    'modules' => [
        // ------------------------------------------------------------------ MODULE 1
        [
            'title'   => 'Module 1: Social media management basics',
            'summary' => 'The role of a social media manager, choosing platforms, setting up profiles, scheduling and content pillars.',
            'lessons' => [
                [
                    'title'   => 'What a social media manager does',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe the daily work of a social media manager and decide whether you could do this job for a business, school or organisation. It is one of the most common digital jobs for young people, and you can begin practising with your own phone.</p>
<h3>The role in simple words</h3>
<p>A <strong>social media manager</strong> looks after the public voice of an organisation on platforms such as Facebook, Instagram, WhatsApp, TikTok, LinkedIn and X. The job is not only posting. It is a mix of planning, creating, talking with people and checking results.</p>
<h3>Main tasks</h3>
<ul>
<li><strong>Plan:</strong> decide what to post, when and why, with a content calendar.</li>
<li><strong>Create:</strong> take photos, shoot short videos, design graphics and write captions.</li>
<li><strong>Publish:</strong> post at the right times, directly or with a scheduling tool.</li>
<li><strong>Engage:</strong> reply to comments and messages, thank supporters, deal with complaints.</li>
<li><strong>Watch:</strong> notice what people say about the organisation online.</li>
<li><strong>Measure and report:</strong> check numbers and tell the team what is working.</li>
<li><strong>Protect:</strong> keep passwords safe and avoid mistakes that harm people or the brand.</li>
</ul>
<h3>Skills that matter</h3>
<table>
<thead><tr><th>Skill</th><th>Why it helps</th></tr></thead>
<tbody>
<tr><td>Clear writing</td><td>Captions and replies are your main tools</td></tr>
<tr><td>Basic design</td><td>Good visuals get noticed in a busy feed</td></tr>
<tr><td>Photography on a phone</td><td>Most content is made with a phone</td></tr>
<tr><td>Organisation</td><td>You must keep to a schedule</td></tr>
<tr><td>Patience and calm</td><td>You will meet rude comments and mistakes</td></tr>
<tr><td>Honesty and judgement</td><td>You speak for others; one careless post can cause harm</td></tr>
</tbody>
</table>
<h3>Working with the organisation</h3>
<p>Agree with your manager or board on who approves posts, which topics are allowed, and who answers sensitive questions. Keep a simple document called a <strong>social media policy</strong> with the answers. This protects you and the organisation.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose a local organisation you know, such as a school, church group or shop.</li>
<li>Visit its social media pages and count how many posts it made in the last month.</li>
<li>Write down three things it does well and three things you would improve.</li>
<li>List which of the tasks above you already feel comfortable with and which you must learn.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A social media manager plans, creates, publishes, engages, watches, measures and protects.</li>
<li>Writing, basic design, organisation and calm judgement are core skills.</li>
<li>A simple social media policy tells everyone who approves what.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Choosing platforms and setting up strong profiles',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose the right two or three platforms for an organisation and set up each profile so that it looks professional and is easy to find. A weak profile makes people doubt you before they read a single post.</p>
<h3>Choosing platforms</h3>
<p>Do not open accounts everywhere. Ask: where does our audience spend time, what do we want to achieve, and what can we create regularly?</p>
<table>
<thead><tr><th>Platform</th><th>Typical use</th></tr></thead>
<tbody>
<tr><td>Facebook</td><td>Community pages, events, local news, groups, all ages</td></tr>
<tr><td>Instagram</td><td>Photos, reels and stories; strong for visual work</td></tr>
<tr><td>WhatsApp</td><td>Direct talk with customers, updates, orders</td></tr>
<tr><td>TikTok</td><td>Short creative videos for a young audience</td></tr>
<tr><td>LinkedIn</td><td>Professional network, jobs, partners, donors</td></tr>
<tr><td>X</td><td>News and quick public conversation</td></tr>
</tbody>
</table>
<h3>Profile checklist</h3>
<ol>
<li><strong>Name.</strong> Use the real name of the organisation so people can search for it.</li>
<li><strong>Handle (username).</strong> Choose a short, simple handle and try to use the same one on every platform, for example @luminaafrica. Avoid confusing numbers and underscores.</li>
<li><strong>Profile picture.</strong> Use the logo or a clear photo. It appears very small, so keep it simple.</li>
<li><strong>Cover or banner.</strong> Show your work, with the right size for that platform. Check the current recommended size in the platform help.</li>
<li><strong>Bio.</strong> Say in one or two lines who you are, who you help and what to do next, for example "Free computer skills for young people in Gulu. Message us to register."</li>
<li><strong>Contact details.</strong> Add a phone number, email, location and opening hours where possible.</li>
<li><strong>Link.</strong> Put your most important link in the bio, such as a website, registration form or WhatsApp chat link. Some tools can make one page of several links.</li>
<li><strong>Category and business account.</strong> Switch to a professional or business account to see insights and use extra tools.</li>
</ol>
<h3>Account security</h3>
<ul>
<li>Use a strong, unique password for every account.</li>
<li>Turn on two-step verification.</li>
<li>Give team members the proper role rather than sharing your password.</li>
<li>Keep a record of who has access, and remove people who leave.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Write a bio of under 150 characters for an organisation you know.</li>
<li>Check that a handle you like is not already used by searching for it on the platform.</li>
<li>List the contact details you would add.</li>
<li>Check your own phone: do you use two-step verification on your main accounts?</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Choose platforms by audience, goal and your capacity.</li>
<li>Use the same name and handle everywhere if possible.</li>
<li>A strong bio says who you are, who you help and what to do next.</li>
<li>Protect accounts with unique passwords and two-step verification.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Content pillars and posting schedules',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose three to five themes for your content and decide how often to post. Without themes, you will run out of ideas by the second week; with themes, ideas come easily.</p>
<h3>What are content pillars?</h3>
<p><strong>Content pillars</strong> are the main topics your page will talk about again and again. They keep your page focused and help followers know what to expect. Choose three to five.</p>
<p>Example for a youth computer training centre:</p>
<ol>
<li><strong>Learn:</strong> quick computer tips and short lessons.</li>
<li><strong>Stories:</strong> graduates and their progress.</li>
<li><strong>Behind the scenes:</strong> classes, team, events.</li>
<li><strong>Opportunities:</strong> jobs, internships, announcements.</li>
<li><strong>Join us:</strong> registration dates and how to apply.</li>
</ol>
<h3>Ideas for pillars</h3>
<ul>
<li>Education and advice</li>
<li>Community stories and testimonials</li>
<li>Products or services</li>
<li>Events and news</li>
<li>Team and values</li>
</ul>
<h3>How often to post</h3>
<p>There is no magic number. Consistency matters more than volume. A small team might post three times a week on Facebook, two or three reels or videos a week on Instagram or TikTok, and a few stories. Start with a number you can keep for three months and increase it only if you can.</p>
<h3>When to post</h3>
<p>Look at your insights: they show the days and hours when your followers are online. In general, people check phones in the early morning, at lunchtime and in the evening, but your own audience may differ. Test different times and compare.</p>
<h3>Scheduling tools</h3>
<p>Scheduling means preparing a post now and setting it to publish later. Meta Business Suite lets you schedule posts for Facebook and Instagram pages. Other tools offer similar features, and some have free plans with limits. Scheduling saves time and keeps you consistent, but check posts before they go out, and stay ready to reply when they are live. Be ready to pause scheduled posts during an emergency or a sad event, so you do not look careless.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose an organisation and write three to five content pillars for it.</li>
<li>For each pillar, write three post ideas.</li>
<li>Decide how many posts a week you can truly keep.</li>
<li>Make a weekly rhythm, for example Monday tip, Wednesday story, Friday opportunity.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Content pillars are three to five main themes that keep your page focused.</li>
<li>Consistency matters more than posting very often.</li>
<li>Use insights to find good posting times, and test different times.</li>
<li>Scheduling tools help, but check every post and be ready to pause.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which task is part of a social media manager's job?
- Repairing computers only
* Planning, creating, publishing, engaging with people and reporting results
- Only pressing the like button
- Only taking photos
= The job combines planning, creating, publishing, talking with people and measuring.

Q: Which is the best way to decide which platforms to use?
- Open an account on every platform
- Use whichever your friend likes
* Choose based on where your audience is, your goal and what you can keep up
- Use only the newest one
= Fewer, well-chosen platforms are better than many neglected ones.

Q: A good bio should
- Be empty
* Say who you are, who you help and what to do next
- Contain only emojis
- List every product you sell
= A short clear bio helps people understand and act.

Q: Which TWO actions protect a social media account? (choose all that apply)
* Using a strong, unique password
- Sharing your password with the whole team on WhatsApp
* Turning on two-step verification
- Using the same password everywhere
= Unique passwords and two-step verification are basic protection.

Q: What are content pillars?
- Pictures on a building
* Three to five main themes that your page returns to regularly
- The number of followers
- A scheduling tool
= Pillars keep your content focused and your ideas flowing.

Q: What should you check before relying on a scheduling tool?
- Nothing, it does everything
- Only the logo
* That each post is correct and that you can pause posts if something sad or urgent happens
- The weather
= Scheduled posts still need review and can look careless during an emergency.

Q: How often should a small team post?
- At least ten times a day
- Once a year
* Whatever number you can keep up consistently
- Only when asked
= Consistency matters more than volume. The best frequency is one you can sustain.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 2
        [
            'title'   => 'Module 2: Design principles for non-designers',
            'summary' => 'Layout, hierarchy, colour, typography, white space and image choices that make any design look good.',
            'lessons' => [
                [
                    'title'   => 'Layout, alignment, contrast and hierarchy',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will know four simple rules that instantly improve any poster, post or flyer, and you will be able to look at a design and say what is wrong with it. You do not need to be an artist; you need a few good habits.</p>
<h3>1. Layout and balance</h3>
<p><strong>Layout</strong> is how things are arranged on the page. Decide the one most important item and give it the most space. Group related items together, and keep a clear path for the eye, usually from top to bottom and left to right. Do not scatter small items everywhere.</p>
<h3>2. Alignment</h3>
<p><strong>Alignment</strong> means lining things up along an invisible line, such as the left edge. When items share a line, a design looks tidy and professional. Mixing centre, left and right alignment on one page looks messy. In most designs, left alignment is easiest to read.</p>
<h3>3. Contrast</h3>
<p><strong>Contrast</strong> is the difference between things, such as dark text on a light background. Poor contrast makes text hard to read, especially on a phone in sunlight. Avoid light grey on white, yellow on white, and text over a busy picture. If you must put text on a photo, add a plain shape behind it or darken the photo.</p>
<h3>4. Hierarchy</h3>
<p><strong>Hierarchy</strong> means showing what is most important first. You create it using size, weight (bold or regular), colour and position.</p>
<table>
<thead><tr><th>Level</th><th>Example on an event poster</th><th>How to show it</th></tr></thead>
<tbody>
<tr><td>First</td><td>Free Computer Class</td><td>Very large, bold</td></tr>
<tr><td>Second</td><td>Starts Monday, 10 June, 6 pm</td><td>Medium, clear colour</td></tr>
<tr><td>Third</td><td>Where to register, phone number</td><td>Smaller, but easy to find</td></tr>
<tr><td>Fourth</td><td>Small notes, logo</td><td>Smallest</td></tr>
</tbody>
</table>
<h3>Repetition and proximity</h3>
<p>Repeat the same colours, fonts and shapes across a set of designs so they look like they belong together. Place related items close together (proximity) so viewers see them as a group.</p>
<h3>A quick check</h3>
<ol>
<li>Squint at the design. Can you still tell what the main message is?</li>
<li>Is the most important thing the biggest or boldest?</li>
<li>Do items line up?</li>
<li>Can everything be read from a metre away?</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Find a poster or flyer in your town, or take a photo of one.</li>
<li>Check it against the four rules: layout, alignment, contrast, hierarchy.</li>
<li>Write one thing that works and one thing to improve under each rule.</li>
<li>Sketch a better version on paper.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Give the most important item the most space and size.</li>
<li>Align items to common lines.</li>
<li>Use strong contrast between text and background.</li>
<li>Create hierarchy with size, weight and colour.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Colour, fonts and white space',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose a simple colour palette and a pair of fonts, and use empty space to make your designs easier to read. These choices make your page recognisable and trustworthy.</p>
<h3>Colour basics</h3>
<p>Colours carry feeling. Blue often suggests trust and calm, green suggests growth and nature, red and orange suggest energy and urgency, yellow suggests warmth and optimism, and dark colours feel serious. Meanings differ between cultures, so think about your own audience.</p>
<p>The colour wheel helps you combine colours:</p>
<ul>
<li><strong>Complementary:</strong> colours opposite each other, such as blue and orange. Strong contrast.</li>
<li><strong>Analogous:</strong> colours next to each other, such as green, teal and blue. Calm and harmonious.</li>
<li><strong>Monochrome:</strong> shades of one colour. Safe and elegant.</li>
</ul>
<h3>Build a palette</h3>
<ol>
<li>Choose one <strong>main colour</strong>, often from your logo.</li>
<li>Add one <strong>accent colour</strong> for buttons and highlights.</li>
<li>Add <strong>neutrals</strong>: white or cream, and a dark grey or near-black for text.</li>
<li>Use only these colours in your posts. Three to five colours are enough.</li>
</ol>
<p>Colours are saved with a code, for example a hex code such as #1F6F5C. Writing your codes down lets anyone use the exact same shade.</p>
<h3>Typography</h3>
<p><strong>Typography</strong> is the choice and use of letters. Fonts fall into two big groups: <strong>serif</strong> (with small feet on letters, feels traditional) and <strong>sans-serif</strong> (clean, modern). Rules to follow:</p>
<ul>
<li>Use no more than two fonts: one for headings and one for body text.</li>
<li>Pair fonts that are clearly different, such as a bold heading with a plain body font.</li>
<li>Avoid decorative script fonts for long text, because they are hard to read.</li>
<li>Keep text large enough for a phone. As a guide, avoid very small text in a post.</li>
<li>Do not write long text in capital letters.</li>
</ul>
<h3>White space</h3>
<p><strong>White space</strong> (also called negative space) is the empty area around items. It is not wasted. It gives the eye a place to rest and makes the important parts stand out. Resist the urge to fill every corner. Leave a comfortable margin around the edge of the design.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose a logo or a photo you like and pick its main colour.</li>
<li>Write a palette of one main colour, one accent and two neutrals.</li>
<li>Pick two fonts you can find on your device or in Canva: one for headings, one for body.</li>
<li>Write a short announcement and decide where there will be empty space.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Colours send messages; choose a small palette and use it every time.</li>
<li>Use two fonts at most, with clear difference between them.</li>
<li>White space makes designs clearer and calmer.</li>
</ul>
<h3>Common mistakes</h3>
<p>Using five colours and four fonts on one poster is the most common beginner mistake. Fewer choices look more professional.</p>
HTML,
                ],
                [
                    'title'   => 'Choosing images, licensing and brand consistency',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose good images, check whether you are allowed to use them, and keep every design consistent with your brand. Using a picture without permission can lead to a complaint or a legal claim.</p>
<h3>Choosing good images</h3>
<ul>
<li><strong>Real is better.</strong> Photos of your own people, work and place build trust more than generic pictures.</li>
<li><strong>Sharp and well lit.</strong> Take photos in daylight near a window or outdoors, and wipe your phone camera lens first.</li>
<li><strong>Simple background.</strong> A clear subject is easier to read than a busy scene.</li>
<li><strong>Fit the message.</strong> The picture should support the words, not fight them.</li>
<li><strong>Represent people fairly.</strong> Show real, respectful images of the communities you serve and avoid stereotypes.</li>
</ul>
<h3>Image quality</h3>
<p>Small, blurry images look poor when enlarged. Use the original photo, not a copy that was forwarded many times through a chat app, which compresses the picture. Portrait (tall) images fit stories and reels; square or slightly tall images work well for feeds.</p>
<h3>Licensing: can I use this picture?</h3>
<p>Photos, music, fonts and designs are the property of their creators. Searching an image in a search engine and saving it does <strong>not</strong> give you the right to use it.</p>
<table>
<thead><tr><th>Source</th><th>What to check</th></tr></thead>
<tbody>
<tr><td>Your own photos and videos</td><td>You own them, but you still need permission from people who appear</td></tr>
<tr><td>Free stock sites and Canva's library</td><td>Read the licence. Some images or elements are only for paying users</td></tr>
<tr><td>Creative Commons images</td><td>Different licences have different rules, such as crediting the creator</td></tr>
<tr><td>Images from the internet</td><td>Assume you cannot use them unless the owner permits</td></tr>
<tr><td>Music</td><td>Use music allowed by the platform or your design tool</td></tr>
</tbody>
</table>
<h3>Brand consistency</h3>
<p>Create a short <strong>brand guide</strong> with the logo, colours with their codes, two fonts, the style of photos and the voice of the text. Apply it to every post. When followers see the same colours, fonts and style, they recognise you instantly, even before they read the name. Canva has a Brand Kit feature for storing logos, colours and fonts; some brand features may need a paid plan, but you can note the same information in a simple document and apply it by hand.</p>
<h3>Try it yourself</h3>
<ol>
<li>Take three photos of a subject near a window or outdoors, from different angles.</li>
<li>Choose the best and say why.</li>
<li>Write a mini brand guide with colours, fonts and a rule for photos.</li>
<li>Look at an image you have used before and decide whether you had the right to use it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Use real, clear, well-lit photos where possible.</li>
<li>Do not use images, music or fonts without the right to do so.</li>
<li>A brand guide keeps every design consistent.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is hierarchy in design?
- Making everything the same size
* Showing what is most important first, using size, weight and colour
- Choosing the most expensive font
- Putting a border around everything
= Hierarchy guides the eye from the most to the least important.

Q: Which gives the best contrast for reading on a phone?
- Light grey text on a white background
- Yellow text on a white background
* Dark text on a light plain background
- Red text on a pink background
= Strong contrast between text and background makes reading easy.

Q: How many fonts should a simple design normally use?
* Two at most
- Five
- As many as possible
- None
= One for headings and one for body text is enough.

Q: What is white space?
- A white background only
* Empty area that gives the design room to breathe
- A mistake in the design
- Space that must be filled with logos
= White space helps important items stand out.

Q: Which TWO are good colour palette habits? (choose all that apply)
* Choosing one main colour and one accent colour
- Using ten bright colours in one post
* Writing down colour codes so everyone uses the same shades
- Changing colours every day
= A small, consistent palette makes your brand recognisable.

Q: You found a nice photo through an internet search. What is the correct action?
- Save it and use it because it is online
- Remove the watermark
* Use it only if you have the right to, for example from a source whose licence allows it
- Copy and paste it into every post
= Images belong to their creators; check the licence first.

Q: Why keep a brand guide?
- To increase the price of your products
* To make designs consistent and easy to recognise
- To stop other people posting
- To avoid taking photos
= Consistent colours, fonts and style build recognition.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 3
        [
            'title'   => 'Module 3: Canva step by step',
            'summary' => 'Create an account, learn the editor, work with text, elements, uploads and layers, then resize, export and share.',
            'lessons' => [
                [
                    'title'   => 'Getting started: account, editor and templates',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will have a Canva account, understand the main parts of the editor, and be able to start a design from a template. Canva is a free online design tool that works in a web browser and in a phone app, so you can design without expensive software.</p>
<h3>Create an account</h3>
<ol>
<li>On a computer, open a browser and go to canva.com. On a phone, install the Canva app from your phone's official app store.</li>
<li>Choose to sign up. You can use an email address, or an existing account from another provider if you prefer.</li>
<li>Choose a strong password and keep it private.</li>
<li>Canva may ask what you will use it for. Pick the closest option, such as personal use, small business or nonprofit.</li>
</ol>
<p>The free plan is enough for this course. Some templates, images and tools are marked as paid; you can simply choose the free ones. Eligible registered nonprofit organisations may be able to apply for extra features; check Canva's help pages for the current rules.</p>
<h3>Start a design</h3>
<p>On the home page you will see a search box and categories such as Social media, Presentations, Documents and Video. You can search for a type, for example "Instagram post", or choose a custom size.</p>
<h3>The editor</h3>
<ul>
<li><strong>The canvas:</strong> the central page where you design.</li>
<li><strong>The side panel (on a computer):</strong> tabs for Design (templates), Elements, Text, Brand, Uploads, Tools and more. On a phone the tools appear along the bottom.</li>
<li><strong>The toolbar above the canvas:</strong> changes depending on what is selected, showing colour, font, size, alignment and effects.</li>
<li><strong>Page controls:</strong> add, duplicate, reorder or delete pages.</li>
<li><strong>Share and download buttons:</strong> at the top of the screen.</li>
</ul>
<h3>Using a template</h3>
<ol>
<li>Open the template or Design tab and look through the examples.</li>
<li>Select one with a layout you like. Choose one with simple text and plenty of space; it is easier to edit.</li>
<li>Click each text box and type your own words.</li>
<li>Click a picture and replace it with yours.</li>
<li>Change the colours to your palette.</li>
</ol>
<p>Templates are a starting point. Always change them so they look like you, and do not leave sample text behind.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create your Canva account.</li>
<li>Search for "Instagram post" and open a template.</li>
<li>Replace all the text with a short announcement for a real or imagined event.</li>
<li>Change one colour and one picture. Find the Share and Download buttons but do not use them yet.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Canva works in a browser or phone app; the free plan is enough to begin.</li>
<li>The editor has a canvas, a side panel or bottom tools, and a toolbar that changes with your selection.</li>
<li>Edit templates properly: change the text, picture and colours.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Text, elements, uploads and backgrounds',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to add and format text, insert shapes and icons, upload your own photos, and change backgrounds. These are the building blocks of almost every design you will make.</p>
<h3>Working with text</h3>
<ol>
<li>Open the Text tab and choose Add a heading, a subheading or a little bit of body text. You can also choose a pre-made text combination.</li>
<li>Double-click the box to type your own words.</li>
<li>With the box selected, use the toolbar to change the font, size, colour, bold, italic, alignment and spacing.</li>
<li>Drag the corners to resize the box, or use the handles to change its width.</li>
</ol>
<p>Keep headings short, use the same fonts throughout the design, and avoid putting too many words on one page.</p>
<h3>Elements</h3>
<p>The Elements tab holds shapes, lines, icons, stickers, frames, charts and graphics. Use a shape as a coloured strip behind text, a line to separate sections, or a frame to place a photo in a circle. Choose a few simple elements in matching colours rather than many decorations. Check whether an item is marked as paid before using it.</p>
<h3>Uploads</h3>
<ol>
<li>Open the Uploads tab and choose to upload files.</li>
<li>Select photos, logos or short videos from your device. On a phone you can choose from your gallery.</li>
<li>Click an upload to place it on the page, or drag it in.</li>
<li>To fit a photo into a frame or background, drag it onto the frame. Double-click to adjust which part is shown.</li>
</ol>
<p>Upload the original, not a heavily compressed copy. A logo with a transparent background (PNG format) looks best over colours.</p>
<h3>Backgrounds</h3>
<p>Click an empty part of the page, then use the colour square in the toolbar to choose a background colour. You can also drag a photo onto the page to use it as a background. Keep backgrounds simple behind text; if a photo is busy, lay a plain semi-transparent shape over it. Use the transparency control in the toolbar to make an item see-through.</p>
<h3>Selecting, copying and undoing</h3>
<table>
<thead><tr><th>Action</th><th>Windows</th><th>Mac</th></tr></thead>
<tbody>
<tr><td>Undo</td><td>Ctrl + Z</td><td>Cmd + Z</td></tr>
<tr><td>Copy</td><td>Ctrl + C</td><td>Cmd + C</td></tr>
<tr><td>Paste</td><td>Ctrl + V</td><td>Cmd + V</td></tr>
<tr><td>Delete selected item</td><td>Delete</td><td>Delete</td></tr>
</tbody>
</table>
<h3>Try it yourself</h3>
<ol>
<li>Open a blank square design.</li>
<li>Choose a plain background colour from your palette.</li>
<li>Add a heading, a subheading and one line of body text.</li>
<li>Upload a photo or logo and place it on the page.</li>
<li>Add a shape behind the text so it is easy to read.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Text boxes are formatted from the toolbar; keep text short and consistent.</li>
<li>Elements decorate the page; use few and keep colours matching.</li>
<li>Upload original, good-quality files.</li>
<li>Keep backgrounds simple behind text.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Layers, alignment and the brand kit',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to control which item sits in front of which, line items up neatly, and keep your colours, fonts and logos ready for quick use. These skills turn a rough design into a clean one.</p>
<h3>Layers</h3>
<p>Every item on the page sits on its own <strong>layer</strong>, like sheets of paper stacked on a table. An item on a higher layer appears in front. If a text box disappears behind a shape, bring it forward.</p>
<ol>
<li>Click the item.</li>
<li>Look for a Position option in the toolbar, or right-click (on a phone, press and hold the item) to find options such as Bring forward, Send backward, To front and To back.</li>
<li>Choose the order you need.</li>
</ol>
<p>Many versions also have a layers view that lists items so you can select hidden ones. Lock an item (for example a background) to prevent it from moving by accident.</p>
<h3>Alignment and spacing</h3>
<ul>
<li>Drag an item slowly; pink or purple guide lines appear when it lines up with the page edge, the centre or another item.</li>
<li>Select several items (hold Shift and click, or drag a box around them) and use the Position or alignment tools to align left, centre or right, or to space them evenly.</li>
<li>Keep the same margin on all sides. Do not place text right at the edge of the page.</li>
<li>Group items that belong together so they move as one.</li>
</ul>
<h3>Copying styles</h3>
<p>The format painter, or similar style-copy tool, copies the look of one item to another. Duplicate a page that you like, then change only the words and photo, to keep a set of designs matching.</p>
<h3>Brand Kit</h3>
<p>A Brand Kit stores your logos, brand colours and brand fonts so you can apply them in one click. Open the Brand section in the side panel or from the home page. Some parts of this feature may require a paid plan. If you use the free plan, you can still do the same by saving your colour codes in the colour picker (in the toolbar choose to add a new colour and enter the code), uploading your logo to the Uploads tab, and writing your fonts in a note.</p>
<p>Teams with a Brand Kit can also use shared templates, so volunteers produce designs that look consistent.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a design with a background shape, a heading and a photo.</li>
<li>Send the shape to the back and bring the text forward.</li>
<li>Select the heading and the subheading and align them to the left.</li>
<li>Add your brand colour using its code, and save your logo in Uploads.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Items stack in layers; use bring forward and send backward to control them.</li>
<li>Use guide lines and alignment tools to make designs tidy.</li>
<li>Store your logo, colours and fonts so every design matches your brand.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Resizing, exporting and sharing',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to change the size of a design for another platform, choose the right file format to save it, and share your work or collaborate with other people. A beautiful design is only useful when it is delivered in the right form.</p>
<h3>Common sizes</h3>
<p>Each platform prefers different shapes. These are typical sizes in pixels; platforms change their recommendations from time to time, so check the current guidance when in doubt.</p>
<table>
<thead><tr><th>Use</th><th>Typical size</th></tr></thead>
<tbody>
<tr><td>Square feed post</td><td>1080 x 1080</td></tr>
<tr><td>Portrait feed post</td><td>1080 x 1350</td></tr>
<tr><td>Story or reel (vertical)</td><td>1080 x 1920</td></tr>
<tr><td>Facebook cover photo</td><td>about 820 x 312 (varies)</td></tr>
<tr><td>A4 flyer</td><td>210 x 297 mm</td></tr>
</tbody>
</table>
<h3>Resizing</h3>
<p>Canva has a Resize feature in the toolbar or menu at the top of the editor, which lets you turn one design into another size. Some versions of this feature are for paid users. On the free plan, you can create a new design in the size you need and copy the elements across. After resizing, always check the layout: text may need to move or become larger.</p>
<h3>Exporting</h3>
<ol>
<li>Click Share or Download at the top of the editor.</li>
<li>Choose the file type.</li>
<li>If your design has several pages, choose which pages to include.</li>
<li>Click Download and save the file to your device.</li>
</ol>
<table>
<thead><tr><th>Format</th><th>Best for</th></tr></thead>
<tbody>
<tr><td>PNG</td><td>Sharp graphics and logos; can have transparent background (a paid option in some plans)</td></tr>
<tr><td>JPG</td><td>Photos and posts; smaller files, good for slow connections</td></tr>
<tr><td>PDF (standard)</td><td>Documents like CVs and flyers to read on screen</td></tr>
<tr><td>PDF (print)</td><td>Printing at a print shop</td></tr>
<tr><td>MP4 video</td><td>Reels, stories and videos</td></tr>
<tr><td>GIF</td><td>Short looping animations</td></tr>
</tbody>
</table>
<p>If you will send a design on WhatsApp, remember it may be compressed. Send it as a document if you need to keep the quality.</p>
<h3>Sharing and collaborating</h3>
<ul>
<li>Use the Share button to invite people by email or to create a link.</li>
<li>Choose their permission: view only, comment or edit. Give edit rights only to those who need them.</li>
<li>Use comments to give feedback on a design.</li>
<li>Name your designs clearly, for example "Poster - Open Day - June".</li>
<li>Keep designs in folders so the team can find them.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Take a design you made earlier and download it as a JPG and a PDF.</li>
<li>Check the file sizes on your device and compare them.</li>
<li>Create a new design in story size and rebuild the main elements.</li>
<li>Share the design with a friend as view-only, then as comment-only.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Choose the size that fits the platform, and check layouts after resizing.</li>
<li>Use PNG or JPG for images, PDF for documents and MP4 for videos.</li>
<li>Share carefully and give each person the lowest permission they need.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is Canva?
- A phone network
* An online design tool that works in a browser and phone app
- An antivirus program
- A type of camera
= Canva lets you design graphics, documents and videos without special software.

Q: You replace a template's text but leave its colours and photo exactly as they were. What should you do instead?
- Nothing, templates should never change
* Change the colours and pictures too so the design reflects your own brand
- Delete the template
- Use more templates on the same page
= Templates are starting points to be customised.

Q: A text box has disappeared behind a coloured shape. What fixes it?
- Delete the shape
- Switch off the phone
* Use Bring forward, or Send backward on the shape
- Change the font
= Items sit on layers; change the order to bring the text in front.

Q: Which TWO help you line up items neatly? (choose all that apply)
* The guide lines that appear as you drag
- Placing items by eye only, with no checking
* Align tools applied to several selected items
- Using a different font
= Guide lines and align tools produce tidy layouts.

Q: Which export format is best for a CV that people will read on a screen?
- MP4
* PDF
- GIF
- A screenshot
= PDF keeps the layout and fonts stable on every device.

Q: Which size is typical for a vertical story or reel?
- 1080 x 1080
- 210 x 297 mm
- 820 x 312
* 1080 x 1920
= 1080 x 1920 is the usual vertical size.

Q: When sharing a design with a volunteer who only needs to see it, which permission is best?
- Edit
* View only
- Owner
- None of these
= Give the lowest permission needed to protect your work.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 4
        [
            'title'   => 'Module 4: Creating real assets',
            'summary' => 'Make social posts, stories, flyers, a logo idea, a CV, a presentation, a video and a chart.',
            'lessons' => [
                [
                    'title'   => 'Social posts and stories',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to design a clear feed post and a story that people notice and understand in seconds. In a busy feed you have less than two seconds to catch someone, so simplicity wins.</p>
<h3>A feed post: quote, tip or announcement</h3>
<ol>
<li>In Canva, search for "Instagram post" and create a blank square design, or choose a simple template.</li>
<li>Set the background to a colour from your palette, or use a clear photo.</li>
<li>Add the main message as a heading, with no more than 10 words. Make it the largest item.</li>
<li>Add one supporting line in a smaller size.</li>
<li>Add your logo in a corner, small but visible.</li>
<li>Leave a margin on all sides and check the contrast.</li>
<li>Download as PNG or JPG.</li>
</ol>
<h3>Ideas that work</h3>
<ul>
<li>A tip: "3 ways to keep your phone safe".</li>
<li>An announcement: date, time, place and how to join.</li>
<li>A quote from a student or a beneficiary, with permission.</li>
<li>Before and after, or a short checklist.</li>
<li>A carousel (several pages in one post) that tells a step-by-step story. Design every page in the same style and number them.</li>
</ul>
<h3>Stories</h3>
<p>Stories are tall (portrait) and last for a short time. Create a design in story size.</p>
<ul>
<li>Keep important text and the logo away from the very top and bottom, because the platform places buttons and a profile name there.</li>
<li>Use large text; people view stories quickly.</li>
<li>Use one idea per story page, and a clear action such as "Send us a message".</li>
<li>Use interactive stickers inside the platform, such as polls and question boxes, to start conversation.</li>
</ul>
<h3>A reusable template</h3>
<p>Make one post design that you like, then duplicate it each time and change only the words and picture. This saves time and keeps your page looking consistent. Save it in a folder named with the month.</p>
<h3>Accessibility</h3>
<p>Use large, readable text and high contrast. Write the key message in your caption as well as on the image, so screen readers can read it. Add alt text to images when the platform allows.</p>
<h3>Try it yourself</h3>
<ol>
<li>Design a square post announcing a real or imagined community event.</li>
<li>Include the event name, date, time, place and one contact method.</li>
<li>Duplicate the design and resize or rebuild it as a story.</li>
<li>Download both and view them on your phone. Can you read everything at arm's length?</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A good post has one main message, big text, strong contrast and a small logo.</li>
<li>Stories are vertical; keep key items away from the top and bottom edges.</li>
<li>A reusable template saves time and builds a consistent look.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Flyers, posters and event materials',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to design a flyer or poster for an event, for example a community workshop, with every key detail clear. Printed and shared posters are still very effective in towns and trading centres, and the same design can be shared online.</p>
<h3>What every event flyer needs</h3>
<ul>
<li><strong>What:</strong> the event name, as the biggest text.</li>
<li><strong>When:</strong> date and time.</li>
<li><strong>Where:</strong> place with a landmark if needed.</li>
<li><strong>Who it is for:</strong> for example "Free for youth aged 16 to 30".</li>
<li><strong>How to join:</strong> phone number, WhatsApp or a short link.</li>
<li><strong>Organiser:</strong> logo and name.</li>
</ul>
<h3>Step by step</h3>
<ol>
<li>Choose the format: A4 poster, A5 flyer or a social media size. For print, set the size at the start.</li>
<li>Start with a clean background in a brand colour or a strong photo with a darker overlay.</li>
<li>Place the event title at the top in large bold type.</li>
<li>Group the date, time and place together below, with small icons if you like.</li>
<li>Add the call to action and the contact details in a clear box near the bottom.</li>
<li>Put the logo and organiser name at the bottom or in a corner.</li>
<li>Check every spelling, date and phone number. Ask someone else to read it too.</li>
</ol>
<h3>Printing tips</h3>
<ul>
<li>Download as PDF (print) and ask the print shop about the paper size they use.</li>
<li>Test the colours: screens and printers can show them differently.</li>
<li>Leave a small margin so nothing is cut off at the edge.</li>
<li>Print one sample before you print many.</li>
<li>Use high-resolution images; blurry images look worse on paper.</li>
</ul>
<h3>Making a poster for an NGO event</h3>
<p>Include a short line about the cause in plain language. If you use photos of people, you must have their permission, and for children you need permission from a parent or guardian. Avoid images that show people as helpless; show dignity and strength. Mention accessibility: for example if the venue is wheelchair-friendly or if sign language will be available.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose a real or imagined event: a school open day, a clean-up or a free class.</li>
<li>Write all six items from the checklist.</li>
<li>Design an A4 poster in Canva using the steps above.</li>
<li>Show it to someone for five seconds and ask them to tell you what the event is and when it takes place.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Every event flyer needs what, when, where, who, how to join and organiser.</li>
<li>Make the title the largest item and group details together.</li>
<li>Check all details, and print a sample first.</li>
<li>Get consent for photos and portray people with dignity.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Logo ideas, CVs and presentations',
                    'minutes' => 17,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to sketch a simple logo concept, design a clear CV and make a short presentation in Canva. These are practical skills you can use for yourself and for clients.</p>
<h3>A simple logo concept</h3>
<p>A logo is a small mark that identifies an organisation. A good logo is simple, readable at small sizes, and works in one colour. Canva can help you test ideas, but a professional logo for a long-term brand may need a trained designer. Do not copy another logo or use ready-made logo elements that you do not have the right to use as a trademark.</p>
<ol>
<li>Write down the organisation's name and three words that describe it.</li>
<li>Sketch five rough ideas on paper: a simple shape, initials, or a name with a symbol.</li>
<li>Choose one idea and build it in Canva with shapes and text.</li>
<li>Test it small, in black and white, and on a coloured background.</li>
<li>Download as PNG and save the Canva file.</li>
</ol>
<h3>A simple CV</h3>
<p>A CV (curriculum vitae) tells an employer who you are and what you can do. In Canva, search for "resume" or "CV" and choose a simple one-page template with clear sections. Prefer templates that are easy to read over very decorative ones.</p>
<ol>
<li>Put your name, phone number and email at the top.</li>
<li>Add a two-line profile about your strengths.</li>
<li>List education and training, newest first.</li>
<li>List work, volunteering or project experience with what you achieved.</li>
<li>Add skills, languages and referees (with their permission).</li>
<li>Use one font family and no more than two colours.</li>
<li>Download as PDF and name it clearly, for example "Amina-Akello-CV.pdf".</li>
</ol>
<p>Avoid photos unless the employer asks, and avoid decorative bars that make text hard to read by screening software.</p>
<h3>A short presentation</h3>
<ol>
<li>Choose the Presentation type and a clean template.</li>
<li>Plan one idea per slide: title, problem, solution, activities, results, next step.</li>
<li>Use short phrases, not paragraphs. One picture per slide is enough.</li>
<li>Keep fonts large and the style the same on every slide.</li>
<li>Use the Present option to rehearse, and download as PDF as a backup.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Sketch five logo ideas for an imaginary community group and build the best in Canva.</li>
<li>Make a one-page CV for yourself using a simple template, and export it as a PDF.</li>
<li>Create a five-slide presentation introducing a project.</li>
<li>Ask a friend which one is clearest and why.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A good logo is simple, original and works small and in one colour.</li>
<li>A CV should be one page, readable and honest, saved as PDF.</li>
<li>Presentations need one idea per slide and little text.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Short videos, reels and charts',
                    'minutes' => 17,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to make a short vertical video in Canva and turn numbers into a clear chart. Video and simple data graphics are two of the best ways to hold attention and to explain your impact to supporters.</p>
<h3>Making a short video or reel</h3>
<ol>
<li>Start a new design and choose Video or a vertical format such as Instagram reel or story.</li>
<li>Pick a simple template, or start from blank.</li>
<li>Add your own clips and photos from Uploads. Trim a clip by selecting it and dragging its ends on the timeline.</li>
<li>Keep each scene short, usually two to five seconds, so the video moves along.</li>
<li>Add large text that appears with simple animation. Two or three words per scene is enough.</li>
<li>Add music from the library if the plan allows, or use sound that you have the right to use. Keep it quiet enough for speech.</li>
<li>Preview the video, then choose Download and select MP4.</li>
</ol>
<h3>Tips for better videos</h3>
<ul>
<li>Hold the phone upright, and film in good light.</li>
<li>Make the first three seconds interesting.</li>
<li>Add captions or text, since many people watch with the sound off.</li>
<li>Keep it short: 15 to 30 seconds for a first project.</li>
<li>End with one call to action.</li>
</ul>
<h3>Charts in Canva</h3>
<p>Charts help people understand numbers quickly. In the Elements tab search for "chart" and choose a type: bar chart for comparing amounts, line chart for changes over time, or pie chart for parts of a whole (use it only for a few slices).</p>
<ol>
<li>Add the chart to your page.</li>
<li>Click it, then choose to edit the data, which opens a small table.</li>
<li>Type your labels and values, or import a spreadsheet file if the option is offered.</li>
<li>Change colours to your palette, and use one highlight colour for the most important bar.</li>
<li>Add a clear title that states the message, for example "Enrolments doubled in 2024".</li>
</ol>
<p>Make sure numbers are accurate and the scale is honest. Do not cut the axis in a way that makes a small difference look big.</p>
<h3>Try it yourself</h3>
<ol>
<li>Film or collect five short clips or photos about a topic.</li>
<li>Assemble them in a 15-second vertical video with text and a closing call to action.</li>
<li>Create a bar chart with three or four true numbers, such as attendance at monthly classes.</li>
<li>Export the video as MP4 and the chart as PNG.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Short scenes, large text and captions make good vertical videos.</li>
<li>Choose music and clips you have the right to use.</li>
<li>Pick the right chart type and give it a title that states its message.</li>
<li>Keep data honest and accurate.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the best length for the main message on a feed post?
- A full paragraph
* A short line, ideally about ten words or fewer
- Three paragraphs
- No words at all
= Short messages are easier to read quickly.

Q: Why keep important text away from the top and bottom of a story?
- Because stories must be square
- Because it looks bold
* Because platforms place buttons and the profile name there
- Because colours do not print
= The edges of stories are partly covered by the app.

Q: Which TWO items must appear on an event flyer? (choose all that apply)
* The date and time
- The designer's favourite song
* How to join or contact the organisers
- A long history of the organisation
= People need to know when the event is and how to take part.

Q: What file type is best to send to a print shop?
- A low-quality screenshot
- GIF
* PDF made for print
- A voice note
= A print PDF keeps the correct quality and size.

Q: What should a one-page CV avoid?
- Clear sections
* Very decorative designs that make it hard to read
- Contact details
- A PDF export
= Employers value clear, readable CVs.

Q: For a vertical video that many people will watch with the sound off, what helps most?
- Very long scenes
- No text
* Short scenes with large captions or text
- Very quiet music
= Text lets viewers follow the message without sound.

Q: You want to show how attendance changed each month this year. Which chart is the best choice?
- A pie chart with twelve slices
* A line chart or bar chart
- A photo
- A logo
= Line or bar charts show change over time clearly.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 5
        [
            'title'   => 'Module 5: Engagement, growth and responsible practice',
            'summary' => 'Captions and hashtags, replying to people, analytics, copyright, consent and safeguarding, and your final 4-week calendar.',
            'lessons' => [
                [
                    'title'   => 'Captions, hashtags and talking with your audience',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write captions that add value to a picture, use hashtags sensibly, and respond to comments, messages and criticism. Engagement means people interacting with you, and it grows when they feel heard.</p>
<h3>Writing captions</h3>
<p>A caption is the text that goes with your image or video. A good caption works in three parts:</p>
<ol>
<li><strong>Hook:</strong> a first line that stands out, because the rest is often cut off. Ask a question or give a short promise.</li>
<li><strong>Body:</strong> the useful or interesting information, in short lines.</li>
<li><strong>Call to action:</strong> one clear next step: comment, share, register, message us.</li>
</ol>
<pre><code>Which computer skill would help you most this year?
Typing, Excel or the internet? Tell us below and we will share a free guide for the most popular answer.
Register for our next class: send "JOIN" on WhatsApp.</code></pre>
<p>Match your tone to your brand. Use correct spelling, plain language, and a few emojis if they suit your voice. Avoid putting all important information only in the image.</p>
<h3>Hashtags</h3>
<ul>
<li>A hashtag is a clickable word or phrase starting with #, used to group posts about a topic.</li>
<li>Use a small number of relevant tags. Quality is better than a long list. Check each platform's current advice.</li>
<li>Create one branded hashtag for your campaigns, such as #LAFAcademy.</li>
<li>Add local tags such as place names where useful.</li>
<li>Read what a hashtag shows before using it, in case it has a different meaning.</li>
<li>Write multi-word hashtags with capital letters, for example #DigitalSkillsGulu, to make them easier to read.</li>
</ul>
<h3>Replying and building relationships</h3>
<ul>
<li>Reply to comments within a day when possible, using the person's name.</li>
<li>Ask follow-up questions.</li>
<li>Thank people for support and share their content with permission.</li>
<li>Keep ready-made answers for common questions, but personalise them.</li>
<li>Use direct messages for private details and never ask for passwords or card numbers.</li>
</ul>
<h3>Handling criticism</h3>
<ol>
<li>Read it twice. Is it a fair point, a misunderstanding or abuse?</li>
<li>For a fair point, thank the person, apologise if needed, and say what you will do.</li>
<li>For a misunderstanding, politely clarify the facts.</li>
<li>For abuse, hate or spam, hide or remove it according to your policy, and report it if serious.</li>
<li>Do not argue in public. Take long conversations to private messages.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Write a caption for a post about a free class, using a hook, a body and a call to action.</li>
<li>Choose five relevant hashtags and one branded hashtag.</li>
<li>Write a reply to the comment: "Is this really free? I think it is a scam."</li>
<li>Read your answers aloud and check the tone is respectful.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Captions need a hook, a helpful body and a clear call to action.</li>
<li>Use a few relevant hashtags and one branded hashtag.</li>
<li>Respond to people promptly and respectfully.</li>
<li>Thank fair critics, correct misunderstandings and remove only abuse or spam.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Insights, calendars and teamwork',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to read the basic numbers on a social media page, improve your plan using them, and organise a team to publish together. Checking results regularly turns guesswork into learning.</p>
<h3>Reading insights</h3>
<p>Switch to a professional or business account to see insights. Look for sections called Insights, Professional dashboard or Analytics on each platform. Meta Business Suite shows data for Facebook and Instagram together. The main numbers are:</p>
<table>
<thead><tr><th>Number</th><th>What it tells you</th></tr></thead>
<tbody>
<tr><td>Reach</td><td>How many different people saw your content</td></tr>
<tr><td>Engagement</td><td>How many reacted, commented, shared, saved or clicked</td></tr>
<tr><td>Followers and growth</td><td>How your audience is changing</td></tr>
<tr><td>Audience details</td><td>Locations, age groups and times when followers are online</td></tr>
<tr><td>Best posts</td><td>Which topics and formats work best</td></tr>
<tr><td>Link clicks and messages</td><td>Whether people took the action you asked for</td></tr>
</tbody>
</table>
<h3>A monthly review in five steps</h3>
<ol>
<li>Look at the last 28 or 30 days and compare with the month before.</li>
<li>Find your three best and three weakest posts.</li>
<li>Ask what they have in common: topic, format, time or caption style.</li>
<li>Choose one thing to do more of and one thing to stop or test.</li>
<li>Write a short note and file it with your calendar.</li>
</ol>
<h3>The content calendar</h3>
<p>A calendar lists each post with date, platform, pillar, idea, design file, caption, approval status and who is responsible. Plan around fixed dates such as public holidays, school terms and your own events. Prepare content in batches: one session for photos, one for designing, one for writing captions.</p>
<h3>Working in a team</h3>
<ul>
<li>Agree on roles: who designs, who writes, who approves, who replies.</li>
<li>Use shared folders and clear file names.</li>
<li>Give people the right level of access on the platform and in Canva.</li>
<li>Hold a short weekly meeting to review the plan.</li>
<li>Write down your brand guide and social media policy for new volunteers.</li>
<li>Have a plan for emergencies, including who can pause posts.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Open the insights for a page you manage, or read the help article that explains them.</li>
<li>Write the reach and engagement for the last month.</li>
<li>Name your best post and a reason for its success.</li>
<li>Make a table of roles for a three-person team.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Insights show reach, engagement, audience and best posts.</li>
<li>A monthly review helps you do more of what works.</li>
<li>Clear roles and shared files make a team work smoothly.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Copyright, consent, safeguarding and your 4-week project',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to publish in a way that protects other people and yourself, and you will have an outline for your final project: a four-week content calendar with designs. Responsible practice is part of your professional reputation.</p>
<h3>Copyright</h3>
<p>The creator of a photo, video, song, design or text usually owns the copyright, which means others need permission to use it. To stay safe:</p>
<ul>
<li>Use your own work, or work you have permission or a licence to use.</li>
<li>Read the licence for stock images, fonts and music, including Canva's terms for its library.</li>
<li>Give credit when the licence requires it.</li>
<li>Do not remove watermarks or copy another organisation's designs.</li>
<li>Do not post other people's songs in videos unless the platform's tools allow it.</li>
</ul>
<h3>Consent for photos and stories</h3>
<ul>
<li>Ask before photographing or filming a person, and explain where the image will be used.</li>
<li>Get written permission where possible; a simple consent form helps.</li>
<li>People can change their mind; remove content when they ask.</li>
<li>Do not tag or name people without permission.</li>
<li>Be careful with private information such as addresses, phone numbers and health details.</li>
</ul>
<h3>Safeguarding children and vulnerable people</h3>
<p>Children cannot give full consent themselves. Always get permission from a parent or guardian before taking or sharing their image or story. Never post a child's full name together with their school, home location or other details that could help someone find them. Avoid photos in which a child is undressed, distressed or in a situation that could embarrass them later. Follow your organisation's child protection policy and the law. If you suspect a child is at risk, do not post; tell the person responsible for safeguarding straight away.</p>
<h3>Honesty and respect</h3>
<ul>
<li>Do not share rumours or unverified news.</li>
<li>Do not use someone's suffering to gain attention.</li>
<li>Tell the truth about your results and impact.</li>
<li>Show people with dignity and let them tell their own stories.</li>
</ul>
<h3>Final project: a 4-week content calendar</h3>
<ol>
<li>Choose a real or imagined organisation and write its goal for the month.</li>
<li>Write three to five content pillars and a brand guide with colours and fonts.</li>
<li>Create a calendar for four weeks with at least 12 posts and two stories.</li>
<li>Design at least four of them in Canva: a feed post, a story, a poster and a short video.</li>
<li>Write the captions with hooks, calls to action and hashtags.</li>
<li>Add a safety checklist: copyright, consent and child safeguarding for each post.</li>
<li>Describe the three numbers you will check at the end of the month.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Write a short consent form text for photos, in two or three sentences.</li>
<li>Check five images you have used: do you own them or have permission?</li>
<li>Start the final project by writing the goal and pillars.</li>
<li>Show your plan to someone and ask for feedback.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Respect copyright: use only what you own or have the right to use.</li>
<li>Always get consent for photos and stories, and parent or guardian consent for children.</li>
<li>Protect children's identity and follow safeguarding policies.</li>
<li>Your project combines pillars, a brand guide, a calendar, designs, captions and safety checks.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does a good caption include?
- Only emojis
- Only hashtags
* A hook, helpful content and a call to action
- The longest text possible
= These three parts keep readers interested and show them what to do next.

Q: What is the best way to use hashtags?
- Use a very long list of unrelated tags
- Use none ever
* Use a few relevant tags and a branded hashtag
- Copy another page's tags exactly
= Relevant tags help people find content; spam tags do not.

Q: A follower gives a fair complaint. What do you do?
- Delete it and block them
* Thank them, apologise if appropriate and say what you will do
- Argue in public
- Ignore it
= Fair criticism handled well builds trust.

Q: Which number tells you how many different people saw your post?
- Followers
- Hashtags
- Bio length
* Reach
= Reach counts different people who saw the content.

Q: You want to publish a photo of a child at your event. What must you do first?
- Nothing, if the event was public
* Get permission from a parent or guardian and follow your safeguarding policy
- Post it and wait for complaints
- Add the child's school name
= Children need extra protection, and a parent or guardian must consent.

Q: Which TWO actions are acceptable about images and music? (choose all that apply)
* Using photos you took yourself, with consent from people shown
- Saving any picture from an internet search and using it
* Using stock images under a licence that allows your use
- Removing a watermark
= Use only content you own or are licensed to use.

Q: Which is the best content for a final 4-week project?
- A single post with no plan
* A calendar with pillars, designs, captions, a safety checklist and measures
- A list of followers
- A copy of someone else's page
= A full project shows planning, creation and responsible practice together.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which task belongs to a social media manager?
- Only designing logos
* Planning, creating, publishing, engaging and measuring
- Only taking photos
- Only deleting comments
= The role covers the whole cycle from planning to reporting.

Q: What is the best basis for choosing platforms?
- The newest app
* Where your audience is, your goal and what you can keep up
- What a competitor uses
- How many emojis it has
= Choose platforms for your audience and capacity.

Q: Which are good habits for account security? (choose all that apply)
* Unique strong passwords
* Two-step verification
- Sharing one password with every volunteer
- Writing passwords on the page's bio
= Protect accounts with strong passwords and extra verification, and give roles instead of sharing passwords.

Q: What are content pillars?
- Pillars in a building
- Hashtags only
* The main themes of your content
- Paid adverts
= Three to five pillars keep your content focused.

Q: Which design rule makes the most important item stand out?
- Equal size for everything
* Hierarchy created with size, weight and colour
- Using many fonts
- Filling all white space
= Hierarchy guides the eye to what matters first.

Q: Which colour combination is the hardest to read on a phone in sunlight?
- Black on white
* Light grey on white
- Dark blue on cream
- Dark green on white
= Low contrast is hard to read.

Q: How many fonts should a simple design usually use?
- Six
- Ten
- As many as possible
* Two or fewer
= Too many fonts make a design messy.

Q: Why is white space useful?
- It wastes paper
* It lets the eye rest and makes important items stand out
- It hides mistakes
- It makes text smaller
= Empty space improves clarity.

Q: You found a photo online and want to use it on a poster. What is the right step?
- Use it, because everything online is free
- Crop out the watermark
* Check the licence or ask the owner for permission
- Change its colour so nobody notices
= Pictures belong to their creators.

Q: Which is a good approach when using a Canva template?
- Leave the sample text as it is
* Replace text, pictures and colours so it fits your brand
- Use five templates in one design
- Never edit it
= Templates are starting points to customise.

Q: Which part of the Canva editor is the page where you design?
- The browser tab
- The password box
- The download folder
* The canvas
= The canvas is the central page.

Q: A text box is hidden behind a shape. What should you do?
- Delete the whole design
- Change the font size
* Bring the text forward or send the shape backward
- Print it
= Layer order controls which item appears in front.

Q: Which export format suits a CV to read on screen?
- MP4
- GIF
- JPG of low quality
* PDF
= PDF keeps layout and fonts stable.

Q: Which is a typical vertical story or reel size?
* 1080 x 1920
- 1080 x 1080
- 210 x 297 mm
- 820 x 312
= 1080 x 1920 is the usual vertical size.

Q: Which file type is good for a photo post and keeps file size small?
- PDF print
- MP4
* JPG
- GIF
= JPG compresses photos well.

Q: Which sharing permission is the safest for someone who only needs to look at a design?
- Edit
- Owner
- Admin
* View only
= Give the lowest permission needed.

Q: Which TWO belong on every event flyer? (choose all that apply)
* Date and time
* How to join or contact the organisers
- The designer's private photos
- Ten paragraphs of history
= The key facts and a contact method let people take part.

Q: For a story, where should key text and the logo be placed?
* Within the safe area, away from the top and bottom edges
- At the very top edge
- At the very bottom edge
- Outside the picture
= Buttons and names cover the edges.

Q: What is the main purpose of a first line, or hook, in a caption?
- To fill space
* To catch attention before the rest of the text is hidden
- To confuse readers
- To list hashtags
= The hook makes people want to read on.

Q: Which is the best hashtag habit?
- Use thirty unrelated tags
- Never check what a tag means
- Copy a competitor's list
* Use a few relevant tags and one branded hashtag
= Relevance matters more than quantity.

Q: Someone comments "This is a scam." The offer is real and free. What is the best reply?
- Delete it at once
* Reply politely, explain the facts, give a way to check and invite them to message you
- Insult the person
- Report them as a criminal
= A calm, factual reply reassures other readers too.

Q: Which should you remove from your page?
- Fair criticism
- Questions
* Abusive, hateful or spam comments
- Thank-you messages
= Remove only abuse and spam, not honest criticism.

Q: Which number counts the different people who saw your post?
- Hashtags
* Reach
- Followers' names
- Fonts used
= Reach counts different people.

Q: What should you do in a monthly review?
* Find best and weakest posts, learn why, and decide what to do more of or change
- Delete all posts
- Change the logo
- Stop posting
= Reviews turn numbers into improvements.

Q: You want to feature a child from a community project in a post. What is required?
- Nothing, because it supports a good cause
- A like from the child
- A stock photo instead of consent
* Parent or guardian consent, and following the child protection policy
= Children need extra protection and a guardian's consent.

Q: Which details should never be posted together with a child's image?
* Full name with school and home location
- A friendly caption
- The event name
- A thank-you to volunteers
= Details that could help someone find a child are unsafe.

Q: Which are parts of the final 4-week project? (choose all that apply)
* A calendar with content pillars
* Canva designs and captions
* A copyright, consent and safeguarding checklist
- A copy of another page
= The project combines planning, creation and responsible practice.

Q: Which colour set gives a simple, consistent brand palette?
- Eight bright colours chosen at random
- A new colour every post
* One main colour, one accent and two neutrals
- Only black
= A small palette is easy to apply and recognise.
QUIZ,
];
