<?php
return [
    'slug'        => 'canva-graphic-design-masterclass',
    'title'       => 'Graphic Design with Canva: From Beginner to Professional',
    'category'    => 'design',
    'level'       => 'beginner',
    'icon'        => 'layers',
    'sort'        => 500,
    'summary'     => 'Go beyond templates: learn layout, type, colour, brand kits, print and screen exports, and finish real projects (posts, posters, CVs, logos, menus, videos) plus a brand-identity portfolio piece.',
    'description' => '<p>Canva makes it easy to open a template and change the words. Real design skill is knowing why a layout works, which size to start with, how to export for print or WhatsApp, and how to keep a whole brand looking like one family. This course teaches that craft, step by step, inside Canva.</p><p>You will learn the workspace and what the free and paid plans really include, then layout, spacing, hierarchy, typography and colour with worked examples. You will edit photos, build a Brand Kit, and complete projects you can show to clients: social media posts and carousels, print posters with bleed, CVs and certificates, logos, business cards, menus, presentations, invitations, infographics and short videos. You will also learn teamwork, AI tools and their limits, correct exporting, copyright, portfolio building and finding design work in East Africa.</p><p>No experience is needed. Completing Graphic Design Fundamentals first helps, but it is not required. The course ends with a capstone: a complete brand identity for a real or imagined small business, church group, school club or NGO.</p>',
    'outcomes'    => [
        'Find your way around the Canva workspace and explain honestly what the free and paid plans include',
        'Choose the correct size and orientation for screen and print before you start designing',
        'Build clear layouts using grids, alignment, spacing and visual hierarchy',
        'Pair fonts and set readable text sizes and line spacing',
        'Choose colour palettes that match a message and pass contrast checks for accessibility',
        'Edit photos, use frames and mockups, and create a consistent brand with the Brand Kit',
        'Design and finish complete projects such as social posts, print posters, CVs, logos, menus, presentations, infographics and short videos',
        'Export in the right format (PNG, JPG, PDF, SVG, MP4) with the right settings, including bleed for print',
        'Work with a team, use AI design tools carefully, respect copyright, and present a portfolio to win design work',
    ],
    'requirements' => ['No experience needed (completing Graphic Design Fundamentals helps but is not required)', 'A computer, laptop or smartphone with internet access and a free Canva account', 'Some photos or text from a real or imagined project to practise with'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Canva Design School', 'url' => 'https://www.canva.com/designschool/'],
        ['title' => 'Canva Help Centre', 'url' => 'https://www.canva.com/help/'],
        ['title' => 'Google Fonts', 'url' => 'https://fonts.google.com/'],
        ['title' => 'WebAIM: web accessibility in mind (contrast and accessibility guides)', 'url' => 'https://webaim.org/'],
        ['title' => 'Creative Commons', 'url' => 'https://creativecommons.org/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: The Canva workspace and starting a design',
            'summary' => 'Know the workspace, the plans, the right size for each job and how to use templates wisely.',
            'lessons' => [
                [
                    'title'   => 'The Canva workspace and account types',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to move around the Canva home page and editor, and say clearly which features you get for free and which need a paid plan. This matters because a freelancer who promises a client a transparent logo or an instant background removal must first know whether their plan allows it.</p>

<h3>Opening Canva</h3>
<p>Canva works in a web browser (on a computer, go to the Canva website and sign in) and as an app for Android and iPhone. A computer with a mouse is easier for detailed work, but a phone is fine for quick posts. Your work saves automatically to your account, so you can start on a phone and finish on a laptop.</p>

<h3>The home page</h3>
<p>In current versions the home page has a left-hand menu and a search bar. Names change from time to time, so look for these items by name:</p>
<ul>
<li><strong>Home:</strong> search for templates and design types, and see recent designs.</li>
<li><strong>Projects:</strong> your designs and folders. Use folders from day one.</li>
<li><strong>Templates:</strong> ready-made starting points, searchable by topic, size and style.</li>
<li><strong>Brand:</strong> the place for your logo, colours and fonts (Module 3).</li>
<li><strong>Apps:</strong> extra tools that plug into the editor.</li>
</ul>

<h3>The editor</h3>
<ol>
<li>The <strong>side panel</strong> on the left holds tabs such as Design (templates), Elements, Text, Brand, Uploads, Tools, Projects and Apps.</li>
<li>The <strong>toolbar</strong> above the canvas changes with what you select: font, size and colour for text; crop and effects for photos.</li>
<li>The <strong>canvas</strong> is the page you design on. Zoom with the slider at the bottom right, or Ctrl and the scroll wheel.</li>
<li>The <strong>Share</strong> button (top right) gives you download, share link and present options.</li>
</ol>

<h3>Free and paid plans, honestly</h3>
<p>Plans and prices change, so always check the Canva pricing page before you promise a client something. In general, at the time of writing:</p>
<table>
<thead><tr><th>Feature</th><th>Free plan</th><th>Paid plans (Pro or Teams)</th></tr></thead>
<tbody>
<tr><td>Editor, templates, many photos and graphics</td><td>Yes (a part of the library)</td><td>Full library, including premium content</td></tr>
<tr><td>Custom design sizes</td><td>Yes, in current versions</td><td>Yes</td></tr>
<tr><td>Download PNG, JPG, PDF, MP4, GIF</td><td>Yes</td><td>Yes</td></tr>
<tr><td>Transparent background PNG</td><td>No, paid</td><td>Yes</td></tr>
<tr><td>Background remover, Magic Resize</td><td>Usually paid</td><td>Yes</td></tr>
<tr><td>Full Brand Kit with uploaded fonts</td><td>Limited or paid</td><td>Yes</td></tr>
<tr><td>SVG download</td><td>Usually paid</td><td>Yes</td></tr>
<tr><td>Team brand controls and approvals</td><td>No</td><td>Teams plans</td></tr>
</tbody>
</table>
<p>Some students and teachers can get Canva for Education free, and registered non-profit organisations can apply for free nonprofit access. Check the Canva Help Centre for the rules and how to apply. Premium items in a free design show a crown mark, and using them needs a paid plan or a one-time purchase before you download.</p>

<h3>A free-first strategy</h3>
<p>You can learn all the design thinking in this course on the free plan. Where a feature is paid, this course gives a free alternative, such as saving brand colours in a text note, or removing a background with a free tool. A free trial can be useful when you have a paid client job to finish, but note the date it ends so you are not charged by surprise.</p>

<blockquote><p><strong>Quick check:</strong> Your client wants a logo with a transparent background on the free plan. What should you tell them?</p><p>Answer: Transparent PNG export is normally a paid feature, so you either use a paid plan or trial for the export or deliver a version on a plain background, because promising a feature your plan lacks causes problems later.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> Where do your designs and folders live in Canva?</p><p>Answer: Under Projects, because it stores all your designs and folders and keeps work organised across phone and computer.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a free Canva account (or sign in) on a computer or phone.</li>
<li>Open Projects and create a folder named "Masterclass practice".</li>
<li>Open the Canva pricing page and write down in a note three features you may need that are paid.</li>
<li>Create any blank design, find the side-panel tabs listed above, and write down the name of each tab you can see.</li>
<li>Save your notes in a file named canva-plan-notes.txt in a new folder on your computer.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Canva saves automatically to your account and works on browser and phone.</li>
<li>The side panel, toolbar and canvas are the three main areas of the editor.</li>
<li>Check current plan details before promising paid features to a client.</li>
<li>Use folders from the start so you can find work later.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Starting a design: sizes, dimensions and templates',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to start every project in the right size, set custom dimensions, and decide when to use a template and when to start blank. This matters because a design made in the wrong size is blurry, cropped or wasted, and fixing it later costs more time than starting correctly.</p>

<h3>Pixels, millimetres and why units matter</h3>
<p>Screens use <strong>pixels</strong> (px), the tiny dots of light. Printers use physical units: millimetres, centimetres or inches. Canva lets you choose a unit when you create a custom size. A simple rule: if the design will be seen on a phone or computer, think in pixels; if it will come out of a printer, think in millimetres and plan for 300 DPI (dots per inch), the usual quality for print.</p>

<h3>Common sizes</h3>
<table>
<thead><tr><th>Use</th><th>Size</th><th>Note</th></tr></thead>
<tbody>
<tr><td>Instagram or Facebook square post</td><td>1080 x 1080 px</td><td>Safe for any feed</td></tr>
<tr><td>Portrait post</td><td>1080 x 1350 px</td><td>Takes more space on a phone feed</td></tr>
<tr><td>Story, Reel cover, WhatsApp Status</td><td>1080 x 1920 px</td><td>Keep text away from the top and bottom edges</td></tr>
<tr><td>YouTube thumbnail</td><td>1280 x 720 px</td><td>16:9 ratio</td></tr>
<tr><td>Presentation</td><td>1920 x 1080 px</td><td>16:9 ratio</td></tr>
<tr><td>A4 flyer or CV</td><td>210 x 297 mm</td><td>Standard office paper</td></tr>
<tr><td>A3 poster</td><td>297 x 420 mm</td><td>Double A4</td></tr>
<tr><td>Business card</td><td>90 x 55 mm (common)</td><td>Ask your printer for their size</td></tr>
</tbody>
</table>
<p>Platform sizes change, so confirm the current recommendation on the platform before a client job.</p>

<h3>Starting a design</h3>
<ol>
<li>On the home page select <strong>Create a design</strong> (in current versions, a button near the top).</li>
<li>Pick a ready type such as Instagram Post, A4 Document or Poster, or select <strong>Custom size</strong>.</li>
<li>For a custom size, choose the unit (px, mm, cm or in), type the width and height, and select <strong>Create new design</strong>.</li>
<li>Rename the design by clicking the title at the top. Use clear names such as "stjohns-poster-A3-v1".</li>
</ol>
<p>Ready-made types already set the right size, so use them when one fits. You can resize later, but the free plan may not include the one-click resize tool, so choosing correctly at the start saves time.</p>

<h3>Templates: help or trap?</h3>
<p>A template is a ready-made layout. Templates are excellent for learning, for tight deadlines and for seeing professional spacing. They also have risks: many people use the same ones, they may not fit your message, and premium items inside them need payment.</p>
<table>
<thead><tr><th>Use a template when...</th><th>Start blank when...</th></tr></thead>
<tbody>
<tr><td>Time is short and the message is simple</td><td>You are designing a logo or brand identity</td></tr>
<tr><td>You want to study a layout</td><td>You need a unique look for a client</td></tr>
<tr><td>The template matches your content length</td><td>Your content (long text or many photos) does not fit the template</td></tr>
<tr><td>The client agrees to a standard look</td><td>The template licence does not allow your use (read its terms)</td></tr>
</tbody>
</table>

<h3>Mini-case</h3>
<p>Grace runs a tailoring shop in Mbale. She needs a Facebook promo and a printed A5 flyer. She starts both from templates but changes the colours, replaces every font with two chosen ones, and uses her own photos. The result looks like her shop, not like a stock sample. The rule is: <strong>use the template for structure, never for identity.</strong></p>

<blockquote><p><strong>Quick check:</strong> Which size would you pick for a WhatsApp Status promo?</p><p>Answer: 1080 x 1920 px, because Status is a tall, full-screen format on a phone.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> Why is it risky to design a printed poster at 800 x 600 px?</p><p>Answer: It has too few pixels for print at 300 DPI, so it will look soft or pixelated when enlarged.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a custom design of 148 x 210 mm (A5) and name it "a5-practice".</li>
<li>Create a second design called "post-practice" at 1080 x 1350 px.</li>
<li>Open a template for a business flyer, then change its colours and fonts so it no longer looks like the original.</li>
<li>Write in a note two sentences: what you kept from the template and what you changed.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use pixels for screens and millimetres with 300 DPI thinking for print.</li>
<li>Choose the size before you design, not after.</li>
<li>Templates give structure; your fonts, colours and photos give identity.</li>
<li>Name designs clearly and keep them in folders.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Elements, layers and an efficient workflow',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to add and arrange elements, control layers, use shortcuts, and keep your files organised. This matters because professionals are not faster because they click harder; they are faster because they use shortcuts and a tidy workflow.</p>

<h3>Elements and uploads</h3>
<p>In the <strong>Elements</strong> tab you can find shapes, lines, icons, photos, graphics, frames, charts and more. Use the search box with simple words such as "arrow" or "market". Items with a crown need a paid plan. In the <strong>Uploads</strong> tab, select <strong>Upload files</strong> to bring in your own photos and logos (JPG, PNG, SVG and others are accepted; SVG is a vector file that stays sharp at any size).</p>

<h3>Layers: what is on top</h3>
<p>Every item sits on a layer. Items higher in the stack cover items below. To change order, select an item and use <strong>Position</strong> in the toolbar, then <strong>Arrange</strong>: Forward, Backward, To front or To back. Shortcuts are Ctrl + ] (Mac: Cmd + ]) to move forward and Ctrl + [ to move backward. In current versions Position also has an Advanced tab and a Layers view; look for the name Layers.</p>

<h3>Shortcuts worth learning</h3>
<table>
<thead><tr><th>Action</th><th>Windows (Mac)</th></tr></thead>
<tbody>
<tr><td>Undo / Redo</td><td>Ctrl + Z / Ctrl + Y (Cmd + Z / Cmd + Shift + Z)</td></tr>
<tr><td>Copy, paste</td><td>Ctrl + C, Ctrl + V (Cmd + C, Cmd + V)</td></tr>
<tr><td>Duplicate selected item</td><td>Ctrl + D (Cmd + D)</td></tr>
<tr><td>Group / Ungroup</td><td>Ctrl + G / Ctrl + Shift + G (Cmd + G / Cmd + Shift + G)</td></tr>
<tr><td>Select all on the page</td><td>Ctrl + A (Cmd + A)</td></tr>
<tr><td>Add text box</td><td>T</td></tr>
<tr><td>Add rectangle, circle, line</td><td>R, C, L</td></tr>
<tr><td>Copy style, paste style</td><td>Ctrl + Alt + C, Ctrl + Alt + V (Cmd + Option + C, V)</td></tr>
<tr><td>Show rulers and guides</td><td>Shift + R</td></tr>
<tr><td>Nudge selected item</td><td>Arrow keys (hold Shift for bigger steps)</td></tr>
</tbody>
</table>
<p>Shortcuts can change; hold your pointer over a toolbar button to see its current shortcut, or check the Canva Help Centre.</p>

<h3>Working neatly</h3>
<ol>
<li><strong>Group</strong> items that belong together (a logo and its name) so they move as one.</li>
<li><strong>Lock</strong> items you do not want to move by accident, such as a background. Select the item and choose the padlock icon in the toolbar.</li>
<li><strong>Name your pages and designs</strong> and keep drafts in a folder.</li>
<li><strong>Use Duplicate page</strong> before trying a risky change, so the old version stays safe.</li>
<li><strong>Zoom in</strong> to check edges and small text before you finish.</li>
</ol>

<h3>Organising files on your own computer</h3>
<p>Design is not only inside Canva. A tidy folder structure on your computer or phone protects you if you lose access. A suggested structure for each client:</p>
<pre>Clients/
  Mama-Rose-Bakery/
    1-brief/
    2-assets/      (logo, photos from the client)
    3-drafts/
    4-final-exports/</pre>
<p>Put the date or a version in the name, for example <code>menu-v2-2026-05.pdf</code>, and never name a file "final-final2".</p>

<h3>Mini-case</h3>
<p>Brian is making a poster. He accidentally drags the background photo while moving text, again and again. He locks the background layer, groups the three date lines, and duplicates the page before changing colours. Each habit takes seconds and removes one frustration.</p>

<blockquote><p><strong>Quick check:</strong> A text box is hidden behind a photo. What do you do?</p><p>Answer: Select the text box and bring it forward (Position, then Arrange, or Ctrl + ]), because items higher in the layer order appear on top.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> Why group a logo and its name before moving them?</p><p>Answer: Grouping keeps them together as one item, so spacing between them stays exactly as designed.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open a new square design. Add a circle (press C), a rectangle (press R) and a text box (press T).</li>
<li>Use Position and Arrange to place the text on top of the rectangle, then the circle behind both.</li>
<li>Select the rectangle and text, group them with Ctrl + G, and duplicate the group with Ctrl + D.</li>
<li>Lock the circle and try to move it; then unlock it.</li>
<li>Create the client folder structure above on your computer and save a download of your page in 3-drafts.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Layers decide which item is on top; Position and Arrange control them.</li>
<li>Learn five shortcuts first: undo, copy/paste, duplicate, group and text.</li>
<li>Group, lock and duplicate pages to work safely.</li>
<li>Keep a tidy folder and file naming system outside Canva too.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A client wants a transparent-background logo and you are on the free plan. What is the most honest next step?
* Tell them transparent export is normally paid, and use a paid plan or trial for that export, or offer another solution
- Download a normal PNG and say it is transparent
- Tell them Canva cannot make logos
- Tell them to buy a different laptop
= Transparent PNG export is normally a paid feature, so be open about it and plan for it instead of promising something your plan does not include.

Q: Which unit should you think in when designing a poster for the printer?
- Pixels only
* Millimetres or centimetres, with 300 DPI quality in mind
- Percentages
- Fonts
= Print uses physical sizes. Pixels alone do not tell you how large the printed item will be.

Q: Which size fits a WhatsApp Status or Instagram Story best?
- 1080 x 1080 px
- 1280 x 720 px
- 210 x 297 mm
* 1080 x 1920 px
= Stories and Status are tall, full-screen phone formats, so 1080 x 1920 px is the standard.

Q: You use a template for a client's flyer. What is the best practice?
- Leave it exactly as it is
* Keep the structure but change colours, fonts and photos to match the client's identity
- Delete all the text and use no fonts
- Use as many crown items as possible
= A template should supply the layout. Identity comes from the brand's own colours, fonts and images.

Q: Which is the best moment to choose the size of a design?
- After finishing the artwork
- Only when exporting
* Before you start designing
- It does not matter
= A wrong size can mean a crop, blur or a total redo, so decide early.

Q: A text box is hidden behind a rectangle. What fixes it?
- Make the text bigger
* Bring the text forward in the layer order
- Change the font
- Delete the rectangle
= Layer order decides what is on top, so move the text forward.

Q: What does locking an item do?
- Deletes it
* Stops it being moved or edited by accident until you unlock it
- Makes it transparent
- Uploads it to the cloud
= Locking is useful for backgrounds and guides you do not want to disturb.

Q: Which file name is the most professional?
- final final2 new
- poster
* mama-rose-menu-v2-2026-05.pdf
- IMG0031
= A clear client name, item, version and date makes files easy to find months later.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Layout, hierarchy, type and colour',
            'summary' => 'The craft that makes designs look professional: grids, spacing, hierarchy, font pairing, readable text and colour.',
            'lessons' => [
                [
                    'title'   => 'Grids, alignment and spacing',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to align items precisely, use rulers and guides, and set consistent margins and spacing. This matters because most "amateur" looking designs have the right ingredients but items placed by eye, a few millimetres off.</p>

<h3>What a grid is</h3>
<p>A <strong>grid</strong> is an invisible set of columns and margins that every item snaps to. A simple grid has a margin around the edge and two to four columns. Because everything lines up with the same lines, the design looks calm and organised even when it is full of content.</p>

<h3>Setting up guides and rulers</h3>
<ol>
<li>Press <strong>Shift + R</strong> (Mac: Shift + R) to show the rulers, or in current versions open the File menu, then View settings, then Show rulers and guides.</li>
<li>Drag from the top or left ruler onto the page to create a guide line.</li>
<li>Create guides for your margins, such as 10 percent of the page width on each side.</li>
<li>Items now snap to the guides as you drag them. Guides do not appear in the downloaded file.</li>
</ol>
<p>Canva also shows pink or purple smart guides while you drag, to show when edges or centres line up with other items. Use them as feedback.</p>

<h3>Align and distribute</h3>
<ul>
<li>Select two or more items (hold Shift and click), then select <strong>Position</strong> in the toolbar. Choose Align left, Align centre, Align right, Align top, middle or bottom.</li>
<li>With three or more items, look for Space evenly (or Distribute) to make equal gaps.</li>
<li>To centre one item on the page, select it and use Position, then align to the page (look for "Align to page" in the Advanced options in current versions).</li>
<li>Use the arrow keys for tiny nudges, and Shift plus an arrow key for larger ones.</li>
</ul>

<h3>Spacing rules of thumb</h3>
<table>
<thead><tr><th>Rule</th><th>Example on a 1080 px post</th></tr></thead>
<tbody>
<tr><td>Outer margin of 5-10 percent of width</td><td>60-100 px on every side</td></tr>
<tr><td>Space between a heading and its text: small</td><td>20 px</td></tr>
<tr><td>Space between groups: larger than inside a group</td><td>60 px</td></tr>
<tr><td>Same gap repeated between similar items</td><td>40 px between three icons</td></tr>
</tbody>
</table>
<p>This follows the idea of <strong>proximity</strong>: things that belong together sit close, and different groups have clear space between them. Empty space (white space) is not wasted; it gives the eye a place to rest.</p>

<h3>Worked example: three-column price list</h3>
<p>For an A4 page (210 x 297 mm), set margins of 15 mm and make three columns of equal width with 10 mm gaps. Each column holds a heading, a price and a short line. Select the three headings and use Align top, then Space evenly. The result looks like a designer arranged it, even though no item is fancy.</p>

<h3>Mini-case</h3>
<p>A church youth group makes a Sunday notice. Text begins at three different left edges, and logos sit near the page edge. After setting margins, aligning everything to the left margin and giving the logo the same margin as the text, the notice suddenly looks official, with no new content added.</p>

<blockquote><p><strong>Quick check:</strong> You have four icons that should have equal gaps between them. Which tool do you use?</p><p>Answer: Select all four and use Position's spacing (space evenly or distribute) option, because it measures and equalises the gaps for you.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> Do guide lines print or show in the exported file?</p><p>Answer: No, they only help while editing, so you can use as many as you need.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open a 1080 x 1350 px design and show the rulers (Shift + R).</li>
<li>Drag four guides to make an 80 px margin on each side.</li>
<li>Add a heading, three short lines and a logo or shape, and snap each to the left margin.</li>
<li>Add three equal circles and use align and spacing options to space them evenly.</li>
<li>Download the result as PNG and save it as grid-practice.png in your portfolio folder.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A grid and margins make everything line up and feel organised.</li>
<li>Use guides, smart guides and the Position tool instead of placing by eye.</li>
<li>Keep gaps inside a group smaller than gaps between groups.</li>
<li>White space is a design tool, not wasted space.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Visual hierarchy and contrast in practice',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to decide what the viewer should read first, second and third, and build that order with size, weight, colour and position. This matters because people glance at a poster or post for about two seconds. If nothing stands out, they scroll or walk past.</p>

<h3>The three levels</h3>
<p>Every design needs a clear order of importance:</p>
<ol>
<li><strong>Level 1: the hook.</strong> One headline or image, the biggest item. Example: "Free Computer Training".</li>
<li><strong>Level 2: the key details.</strong> Date, place, price, medium sized.</li>
<li><strong>Level 3: the small print.</strong> Contacts, terms, social media handles, small but still readable.</li>
</ol>
<p>If everything is big, nothing is. If everything is bold, bold loses its power.</p>

<h3>The tools of hierarchy</h3>
<table>
<thead><tr><th>Tool</th><th>How to use it</th><th>Canva control</th></tr></thead>
<tbody>
<tr><td>Size</td><td>Headline two to three times bigger than body text</td><td>Font size box in the toolbar</td></tr>
<tr><td>Weight</td><td>Bold for headings, regular for body</td><td>Font dropdown or Bold button (Ctrl + B)</td></tr>
<tr><td>Colour</td><td>One accent colour for the most important words</td><td>Text colour button</td></tr>
<tr><td>Position</td><td>Top or centre draws the eye first</td><td>Drag or Position tab</td></tr>
<tr><td>Space</td><td>Space around an item gives it importance</td><td>Margins and gaps</td></tr>
<tr><td>Contrast</td><td>Dark on light, or light on dark</td><td>Colour and backgrounds</td></tr>
</tbody>
</table>

<h3>The squint test</h3>
<p>Zoom out in Canva (or step back from your phone) and squint until the text blurs. What still stands out? That is your real hierarchy. If it is a decoration instead of your headline, fix it. This test takes ten seconds and catches most mistakes.</p>

<h3>Worked example: a fundraising poster</h3>
<p>A school wants a poster for a charity football match. Weak version: all text 24 pt, same colour, centred in a block. Better version using the three levels:</p>
<pre>Level 1 (96 pt bold, white on dark green):  CHARITY MATCH
Level 2 (40 pt, bold gold):                 Saturday 14 June, 3 pm, Kyambogo Grounds
Level 2 (32 pt regular white):              Entry UGX 5,000. All money builds our library.
Level 3 (22 pt white):                      Call 07xx xxx xxx   |   @ourschool</pre>
<p>The viewer reads the event, then the time and place, then how to join, in that order. Sizes are examples for an A3 poster; test them on your own page.</p>

<h3>Contrast also means difference</h3>
<p>Contrast is not only dark versus light. It can be big versus small, thick versus thin, or colour versus neutral. A bright button on a calm page works because it is different. When a design feels flat, add contrast in one place, not everywhere.</p>

<h3>Common mistakes</h3>
<ul>
<li>Three headlines fighting for attention.</li>
<li>Putting the phone number bigger than the event name.</li>
<li>Using many different font sizes with no system. Pick three sizes and repeat them.</li>
</ul>

<blockquote><p><strong>Quick check:</strong> You squint at your poster and the decorative border stands out most, not the headline. What do you change?</p><p>Answer: Reduce the border's contrast and increase the headline's size or contrast, because the headline must be the first thing the eye sees.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> Name two ways other than size to make a word stand out.</p><p>Answer: Bold weight and a different (accent) colour, because both create contrast without changing the layout.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a real event (a school event, a shop opening, a church program).</li>
<li>Write down three levels of information on paper first.</li>
<li>Build an A4 poster in Canva using only three font sizes and one accent colour.</li>
<li>Do the squint test, adjust once, and note what you changed.</li>
<li>Export as PDF and keep the file for your portfolio.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Plan three levels: hook, key details, small print.</li>
<li>Use size, weight, colour, position and space deliberately.</li>
<li>The squint test shows your true hierarchy quickly.</li>
<li>Add contrast in one place at a time; too much weakens the effect.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Typography: pairing, sizes and line spacing',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose two fonts that work together, set text sizes that people can read, and adjust line and letter spacing. This matters because most design is text, and unreadable text means a message that fails.</p>

<h3>Font families, in plain words</h3>
<ul>
<li><strong>Serif</strong> fonts have small feet on the letters (like Times). They feel traditional, formal and good for long printed text.</li>
<li><strong>Sans-serif</strong> fonts have clean ends (like Arial). They feel modern and read well on screens.</li>
<li><strong>Script</strong> fonts imitate handwriting. They are pretty for a name or a wedding invitation but hard to read in long text.</li>
<li><strong>Display</strong> fonts are bold and decorative, for large headlines only.</li>
</ul>

<h3>Pairing rules</h3>
<ol>
<li><strong>Use two fonts, three at most:</strong> one for headings, one for body text.</li>
<li><strong>Contrast them:</strong> a bold sans heading with a calm serif body works, or the reverse. Two similar fonts look like a mistake.</li>
<li><strong>Use one family in different weights</strong> when unsure. Fonts with Light, Regular and Bold weights give variety safely.</li>
<li><strong>Avoid two decorative fonts</strong> in the same design.</li>
</ol>
<table>
<thead><tr><th>Project</th><th>Heading</th><th>Body</th></tr></thead>
<tbody>
<tr><td>Law firm or bank</td><td>Serif, bold</td><td>Clean sans-serif</td></tr>
<tr><td>Youth event</td><td>Bold display or heavy sans</td><td>Simple sans-serif</td></tr>
<tr><td>Wedding invitation</td><td>Elegant script (large, names only)</td><td>Light serif or sans</td></tr>
<tr><td>NGO report</td><td>Sans-serif, bold</td><td>Serif, readable</td></tr>
</tbody>
</table>
<p>Many good free fonts exist at Google Fonts; some of them also appear in Canva's font list. On Canva, uploading your own font file needs a paid plan, so on the free plan choose from the built-in list. Check licence terms before using any font commercially.</p>

<h3>Readable sizes</h3>
<table>
<thead><tr><th>Item</th><th>Suggested minimum</th></tr></thead>
<tbody>
<tr><td>Instagram post body text (1080 px wide)</td><td>36-40 px</td></tr>
<tr><td>Presentation body text (1920 x 1080)</td><td>28-32 pt, never below 24</td></tr>
<tr><td>A4 flyer body text</td><td>10-12 pt</td></tr>
<tr><td>A3 poster details</td><td>24-36 pt</td></tr>
<tr><td>Poster headline</td><td>72 pt or more</td></tr>
</tbody>
</table>
<p>Canva shows sizes in a unit that does not exactly equal printed points on every design, so judge by testing: look at your design at the real size on screen or print a test page.</p>

<h3>Line spacing, letter spacing and alignment</h3>
<ol>
<li>Select a text box and choose <strong>Spacing</strong> in the toolbar (in current versions it opens Letter spacing and Line spacing sliders).</li>
<li><strong>Line spacing</strong> of about 1.2 to 1.5 for body text keeps lines from crowding. Tight spacing (about 1.0) suits big headlines.</li>
<li><strong>Letter spacing:</strong> add a little to ALL CAPS and small text; do not squeeze letters.</li>
<li><strong>Alignment:</strong> left-align paragraphs. Centre only short lines such as titles. Avoid justified text in narrow boxes because it creates ugly gaps.</li>
<li>Keep lines to about 40-60 characters. Very long lines tire the eye.</li>
</ol>

<h3>Mini-case</h3>
<p>Aisha designs a menu for a juice bar. She first uses four fonts and 14 sizes. She changes to one bold rounded heading font and one clean body font, three sizes, and left-aligns the descriptions. Customers say the menu is easier to read, and prices are found faster.</p>

<blockquote><p><strong>Quick check:</strong> A poster uses a script font for three full paragraphs. What is wrong?</p><p>Answer: Script fonts are hard to read in long text, so use a plain serif or sans-serif for paragraphs and keep script for a short title.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> Which line spacing suits body text best: 0.8, 1.0 or 1.4?</p><p>Answer: About 1.4, because comfortable gaps between lines make reading easier.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create an A5 flyer for a tutoring service.</li>
<li>Choose one heading font and one body font, with a clear contrast between them.</li>
<li>Write a 40-word paragraph and set line spacing to about 1.4 and left alignment.</li>
<li>Make a second version with a different pair and compare which is more readable.</li>
<li>Download the better one as PNG and write one line saying why you chose it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use two fonts that contrast, or one family in several weights.</li>
<li>Choose readable minimum sizes and test them at real size.</li>
<li>Give body text breathing space with line spacing of about 1.2 to 1.5.</li>
<li>Left-align paragraphs and keep scripts for short names and titles.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Colour: palettes, psychology and accessibility',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a simple palette, explain what colours may suggest to an audience, and check that text is readable for everyone, including people with low vision or colour blindness. This matters because colour sets the mood in a second, and poor contrast makes your message invisible.</p>

<h3>A palette you can manage</h3>
<p>Use a small palette: usually 3 to 5 colours.</p>
<ul>
<li><strong>One main colour</strong> (about 60 percent of the design).</li>
<li><strong>One support colour</strong> (about 30 percent).</li>
<li><strong>One accent colour</strong> (about 10 percent) for buttons and key words.</li>
<li><strong>Neutrals:</strong> white, black or grey for text and backgrounds.</li>
</ul>
<p>This is called the 60-30-10 guide. Canva helps in several places: the colour button on any item shows <strong>Photo colours</strong> picked from your image, <strong>Document colours</strong> already in the design, and <strong>Default colours</strong>. To type an exact colour code, select the plus sign (or the colour picker) in the colour panel and enter a HEX code, a six-character code like #1B5E20.</p>

<h3>What colours may suggest</h3>
<p>These meanings are tendencies, not laws, and change between cultures and contexts. Always consider your own audience.</p>
<table>
<thead><tr><th>Colour</th><th>Often suggests</th><th>Example use</th></tr></thead>
<tbody>
<tr><td>Blue</td><td>Trust, calm, professionalism</td><td>Banks, clinics, schools</td></tr>
<tr><td>Green</td><td>Growth, health, nature, money</td><td>Farming, environment, finance</td></tr>
<tr><td>Red</td><td>Energy, urgency, passion (also warning)</td><td>Sales, food, emergencies</td></tr>
<tr><td>Yellow and orange</td><td>Warmth, optimism, attention</td><td>Youth events, food, discounts</td></tr>
<tr><td>Purple</td><td>Creativity, luxury, spirituality</td><td>Beauty, churches, arts</td></tr>
<tr><td>Black, white, grey</td><td>Elegance, simplicity, neutrality</td><td>Fashion, technology</td></tr>
</tbody>
</table>

<h3>Contrast and accessibility</h3>
<p>The most common colour problem is low contrast, such as light grey text on white or yellow on white. Accessibility guidelines (WCAG) suggest a contrast ratio of at least <strong>4.5 to 1</strong> for normal text and <strong>3 to 1</strong> for large text. Canva does not do every accessibility check for you, so use a free online contrast checker (WebAIM has one) by entering the HEX codes of your text and background.</p>
<ul>
<li>Never rely on colour alone. If red means "closed" and green means "open", add the words as well, because some people cannot tell red and green apart.</li>
<li>Avoid placing text directly on a busy photo. Add a dark or light overlay (a rectangle with transparency) or a solid band behind the text.</li>
<li>Test your design in greyscale: if you cannot read it in black and white, the contrast is too weak.</li>
</ul>

<h3>Screen colour and print colour</h3>
<p>Screens mix light (RGB); printers mix ink (CMYK). Some bright screen colours, such as vivid neon green or electric blue, look duller when printed. Canva designs in RGB, but when you download a PDF Print you can choose a CMYK option in current versions (it may need a paid plan). If you do not have it, keep print colours slightly deeper and print a test page.</p>

<h3>Mini-case</h3>
<p>Peter designs a poster for a vaccination awareness day. He uses pale yellow text on a white background. A teacher tells him she cannot read it from the back of the hall. He changes to dark blue text on white with a yellow band for the date. Same colours, readable result.</p>

<blockquote><p><strong>Quick check:</strong> Which pair has better contrast: light grey text on white, or dark navy text on white?</p><p>Answer: Dark navy on white, because the large difference in lightness makes it easy to read.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> A map uses only red and green dots to show open and closed shops. What should you add?</p><p>Answer: Labels or shapes such as "Open" and "Closed", because some people cannot tell red from green.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a business type and pick one main, one support and one accent colour. Write the HEX codes.</li>
<li>Create a 1080 x 1080 px post with a headline in the main colour on a neutral background.</li>
<li>Check the contrast of your text and background with a free online contrast checker.</li>
<li>Download the design, convert a copy to greyscale in any photo viewer, and see whether it is still readable.</li>
<li>Save the palette in a text file named palette.txt next to your artwork.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use 3 to 5 colours in a 60-30-10 pattern.</li>
<li>Colour meanings are tendencies; think about your own audience.</li>
<li>Aim for contrast of about 4.5 to 1 for normal text and never use colour alone to carry meaning.</li>
<li>Print colours can look duller than screen colours, so test print.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Your poster has all text at the same size and colour. What should you do first?
- Add a border
- Add more fonts
* Decide the order of importance and make the headline clearly the largest and most contrasting
- Make everything bold
= Hierarchy comes from a deliberate order: hook, key details, small print.

Q: What does the squint test show you?
* What stands out most in your design, which is your real hierarchy
- Whether the file is the right size
- Whether the fonts are installed
- How much the design will cost to print
= Blurring detail reveals which shapes and words dominate, so you can fix the wrong ones.

Q: Which is the best font pairing approach for a beginner?
- Four decorative fonts for variety
- Two similar sans-serif fonts at the same weight
* One heading font and one calm body font that contrast, or one family in several weights
- A script font for all paragraphs
= Contrast between two fonts (or weights) looks deliberate. Too many decorative fonts looks messy.

Q: Which line spacing is best for a paragraph of body text?
- 0.7
- 1.0
* About 1.3 to 1.5
- 3.0
= Slightly open line spacing makes it easier for the eye to follow from line to line.

Q: A group of three icons should have equal gaps. What is the fastest correct method?
- Move each by eye
* Select them and use the Position tool's space-evenly option
- Make the icons bigger
- Delete one icon
= The tool measures for you and gives exact, equal gaps.

Q: Light yellow text on a white background is hard to read. What is the problem?
- Wrong font family
- Wrong file format
* Low colour contrast
- Too much white space
= Text needs strong contrast with the background; a ratio of about 4.5 to 1 is recommended for normal text.

Q: Which statement about colour meaning is most accurate?
- Blue always means danger
* Colour meanings are tendencies that depend on culture and context
- Red cannot be used in a school design
- Meanings are the same for every audience
= Think about your audience; the same colour can suggest different ideas in different places.

Q: Why should a poster with green "Open" and red "Closed" dots also have words?
- Words look more modern
* Some people cannot distinguish red from green, so colour alone is not enough
- It is required by printers
- Canva refuses designs without words
= Accessible design never relies on colour alone to carry meaning.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Images, brand kit and logos',
            'summary' => 'Work with photos, frames and mockups, build a consistent brand with the Brand Kit, and design a simple logo.',
            'lessons' => [
                [
                    'title'   => 'Photos, frames, cropping and mockups',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose and prepare photos, crop and straighten them, place them in frames, remove a background where your plan allows, and show your design on a mockup. This matters because a strong photo does half the work of a poster, and a poor one ruins even a good layout.</p>

<h3>Choosing a good photo</h3>
<ul>
<li><strong>Sharp and well lit.</strong> Blurry or dark photos look worse when enlarged.</li>
<li><strong>Enough pixels.</strong> A photo for an A4 print should be at least about 2500 x 3500 px. A small phone screenshot will look soft.</li>
<li><strong>Space for text.</strong> Pick photos with a calm area (sky, wall, floor) where text can sit.</li>
<li><strong>Real people and places</strong> from your community usually feel more honest than generic stock photos. Always ask permission before using someone's face in an advert.</li>
</ul>

<h3>Cropping, straightening and adjusting</h3>
<ol>
<li>Click the photo, then select <strong>Crop</strong> in the toolbar (or double-click the photo). Drag the handles to cut away what you do not need. Keep the main subject off the exact centre; the rule of thirds places it a third from one edge.</li>
<li>Use the <strong>Straighten</strong> option or rotate handle if the horizon tilts.</li>
<li>Select <strong>Edit image</strong> (in current versions, in the toolbar) to find Adjust (brightness, contrast, saturation) and Filters. Small changes are best: raise brightness a little rather than flooding the photo.</li>
<li>Use <strong>Flip</strong> if a face looks the wrong way, but never flip a photo containing text, such as a signboard.</li>
</ol>

<h3>Frames and grids</h3>
<p>In <strong>Elements</strong>, search "Frames" or "Grids". Drag a photo onto a frame and it fills the shape (circle, rectangle, blob). Double-click inside the frame to move or zoom the photo. Frames create neat team pages, profile pictures and collage layouts, and make different photo shapes look like one set.</p>

<h3>Removing a background</h3>
<p>Select a photo and look for <strong>Background remover</strong> (in current versions under Edit image). It cuts out the subject, which is useful for product photos, such as a sneaker on a plain colour. This tool is generally a paid feature. Free alternatives: use a free online background removal service or an offline photo editor, then upload the cut-out PNG. Check the tool's privacy rules before uploading client photos.</p>

<h3>Mockups</h3>
<p>A <strong>mockup</strong> shows your design on a real-world object: a T-shirt, mug, phone screen or poster on a wall. Open the Apps tab and search for "Mockups" (in current versions). Choose a mockup, pick your design, and download the image. Clients love seeing a logo on a shirt before they print 100 of them. Some mockups need a paid plan.</p>

<h3>Photo size comparison</h3>
<table>
<thead><tr><th>Use</th><th>Minimum photo size</th><th>Why</th></tr></thead>
<tbody>
<tr><td>Instagram post (1080 px)</td><td>1080 px on the long side</td><td>Screen only</td></tr>
<tr><td>Presentation full-slide photo</td><td>1920 x 1080 px</td><td>Fills the slide</td></tr>
<tr><td>A4 poster photo</td><td>About 2500 x 3500 px</td><td>300 DPI print</td></tr>
<tr><td>Business card photo</td><td>About 1000 x 650 px</td><td>Small print size</td></tr>
</tbody>
</table>

<h3>Mini-case</h3>
<p>Doreen sells handmade bags. Her phone photos have messy backgrounds. She takes new photos near a window with the bags on a plain white cloth, crops them in squares, adds a little brightness, and puts them in matching circular frames. Her shop page looks professional, and she spent nothing on equipment.</p>

<blockquote><p><strong>Quick check:</strong> A photo looks pixelated on your A4 print. What is the likely cause?</p><p>Answer: The photo has too few pixels for the print size, so use a larger original.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> Why should you ask permission before using a customer's photo in an advert?</p><p>Answer: A person's image belongs to them in many situations, and using it without consent can break privacy and trust.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Take one product or place photo with your phone near a window.</li>
<li>Upload it to Canva and crop it to a 4:5 portrait.</li>
<li>Adjust brightness slightly, then place it in a circular or rounded frame.</li>
<li>If your plan allows, remove the background and place the product on a solid colour.</li>
<li>Download as PNG and save in your portfolio folder with a clear name.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Sharp photos with enough pixels and space for text work best.</li>
<li>Crop, straighten and adjust a little rather than a lot.</li>
<li>Frames make mixed photos look like one set.</li>
<li>Background removal is usually paid; free outside tools exist.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The Brand Kit and a consistent brand',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what a brand identity is, build a Brand Kit with logo, colours and fonts, and apply it consistently. This matters because customers remember brands that always look the same, from a signboard to a WhatsApp post.</p>

<h3>What is a brand identity?</h3>
<p>A brand is not only a logo. It is the whole feeling people get about a business, school, church or NGO. The visible part, the <strong>brand identity</strong>, includes:</p>
<ul>
<li>A <strong>logo</strong> (and sometimes a shorter version, such as an icon).</li>
<li>A <strong>colour palette</strong> with exact codes.</li>
<li>Two <strong>fonts</strong> for headings and body.</li>
<li><strong>Image style</strong>: real photos or illustrations, bright or calm.</li>
<li><strong>Voice</strong>: friendly, formal, or playful words.</li>
</ul>
<p>Consistency means all these appear the same everywhere. A tailor whose Facebook page is purple, whose flyer is orange and whose signboard is green looks like three different shops.</p>

<h3>The Brand Kit in Canva</h3>
<p>In current versions, open <strong>Brand</strong> from the home page menu or the side panel of the editor (sometimes called Brand Hub or Brand Kit). A full Brand Kit with logos, colours and fonts is generally a paid feature, and the free plan may offer a limited version. On the free plan you can still be consistent: keep your HEX codes and font names in a brand sheet and set them by hand. The steps for a paid plan:</p>
<ol>
<li>Open Brand and select your Brand Kit (or <strong>Add new</strong>).</li>
<li>Under <strong>Logos</strong>, upload your logo files (a coloured one and a white one).</li>
<li>Under <strong>Brand colours</strong>, add each colour by its HEX code and name it, for example "Main green #1B5E20".</li>
<li>Under <strong>Brand fonts</strong>, set your heading and body fonts, and your style for headline, subheading and body.</li>
<li>In the editor, open the <strong>Brand</strong> tab to click your logo, colours and fonts into a design.</li>
</ol>

<h3>A one-page brand sheet (free and useful on any plan)</h3>
<table>
<thead><tr><th>Item</th><th>Example entry</th></tr></thead>
<tbody>
<tr><td>Name and tagline</td><td>Mama Rose Bakery: Fresh every morning</td></tr>
<tr><td>Main colour</td><td>Warm brown #6D4C41</td></tr>
<tr><td>Accent colour</td><td>Golden yellow #FFB300</td></tr>
<tr><td>Neutrals</td><td>Cream #FFF8E1 and dark brown #3E2723</td></tr>
<tr><td>Heading font</td><td>A bold rounded sans-serif</td></tr>
<tr><td>Body font</td><td>A clean sans-serif</td></tr>
<tr><td>Logo rules</td><td>Keep clear space equal to the height of the letter R; never stretch</td></tr>
<tr><td>Photo style</td><td>Warm light, real customers, close-ups of bread</td></tr>
</tbody>
</table>
<p>You can design this sheet as a single A4 Canva page and export it as a PDF to give your client.</p>

<h3>Logo rules that protect the brand</h3>
<ul>
<li>Do not stretch, squeeze or rotate the logo. Hold Shift while resizing to keep the proportion.</li>
<li>Leave clear space around it.</li>
<li>Use a light version on dark backgrounds.</li>
<li>Do not put it on a busy photo where it becomes hard to see.</li>
</ul>

<h3>Mini-case</h3>
<p>A youth NGO in Gulu used different colours and fonts in every poster, designed by whoever had time. After building one brand sheet with two colours, two fonts and a logo rule, volunteers made posters that looked like they came from one organisation. Donors recognised them at once.</p>

<blockquote><p><strong>Quick check:</strong> Which items belong in a brand identity apart from the logo?</p><p>Answer: Colours, fonts, image style and tone of voice, because together they make the brand recognisable everywhere.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> You are on the free plan without a full Brand Kit. How can you stay consistent?</p><p>Answer: Keep a brand sheet with HEX codes and font names and apply them by hand every time, because the discipline matters more than the tool.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a small business or group you know.</li>
<li>Pick two colours plus neutrals, and two fonts.</li>
<li>Create an A4 design called "brand-sheet" with these items laid out in a table style.</li>
<li>If your plan has a Brand Kit, add the same items to it.</li>
<li>Download the sheet as a PDF named brandname-brand-sheet.pdf.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A brand is logo, colours, fonts, image style and voice working together.</li>
<li>Consistency builds recognition and trust.</li>
<li>The full Brand Kit is usually paid, but a brand sheet works on any plan.</li>
<li>Protect the logo: never stretch it and always leave clear space.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Designing a simple logo',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to design a simple, clear logo in Canva, test it at different sizes, and export the right files for the client. This matters because a logo appears everywhere, from a tiny profile picture to a large signboard, so it must work at every size.</p>

<h3>What makes a logo work</h3>
<ul>
<li><strong>Simple:</strong> few shapes and colours. Strong logos can be drawn from memory.</li>
<li><strong>Memorable:</strong> one clear idea, such as a leaf, a bowl, a book or a letter.</li>
<li><strong>Versatile:</strong> good in colour, in one colour and on dark or light backgrounds.</li>
<li><strong>Appropriate:</strong> fits the business, audience and values.</li>
<li><strong>Readable small:</strong> still clear as a 48 px profile picture.</li>
</ul>

<h3>Types of logo</h3>
<table>
<thead><tr><th>Type</th><th>What it is</th><th>Best for</th></tr></thead>
<tbody>
<tr><td>Wordmark</td><td>The name in a styled font</td><td>Short, unique names</td></tr>
<tr><td>Lettermark</td><td>Initials in a shape</td><td>Long names (an NGO or school)</td></tr>
<tr><td>Icon plus name</td><td>A symbol next to the name</td><td>Most small businesses</td></tr>
<tr><td>Emblem</td><td>Text inside a badge</td><td>Schools, clubs, churches</td></tr>
</tbody>
</table>

<h3>Building a logo step by step</h3>
<ol>
<li><strong>Write a mini brief:</strong> name, what it does, who it serves, three words for the feeling (for example "fresh, friendly, local").</li>
<li><strong>Sketch</strong> five quick ideas on paper. Choose one or two.</li>
<li>In Canva, start a blank <strong>1000 x 1000 px</strong> design (a square gives room; use Custom size).</li>
<li>Add shapes from Elements (circles, lines) and combine them. Use simple shapes you can recolour. Be careful with ready-made icons or clip art: many are licensed for use in designs but not as a trademarked logo, so read the content licence in the Canva help pages before using one as the main mark.</li>
<li>Add the name in your chosen font. Adjust letter spacing, then align the symbol and text. Group everything.</li>
<li>Test it: make it small (zoom out to a thumbnail), make a black version, and put it on a coloured background.</li>
</ol>

<h3>Worked example</h3>
<p>For "Mama Rose Bakery", build a circle in warm brown, place a simple wheat stalk shape in cream inside it, and write "Mama Rose" in a bold rounded font beside it with "BAKERY" in smaller spaced capitals underneath. Then make three copies: full colour, all-white for dark backgrounds, and just the circle for a social media profile picture.</p>

<h3>Which files to deliver</h3>
<table>
<thead><tr><th>File</th><th>Use</th></tr></thead>
<tbody>
<tr><td>PNG with transparent background (paid export in Canva)</td><td>Placing on photos, slides and websites</td></tr>
<tr><td>PNG on a white background</td><td>When transparent export is not available</td></tr>
<tr><td>SVG (usually paid in Canva)</td><td>Scaling for large signs or printers</td></tr>
<tr><td>PDF</td><td>Sending to printers</td></tr>
</tbody>
</table>
<p>Honest note: for professional vector logos, free tools like Inkscape are stronger than Canva. For small business branding, Canva is usually enough.</p>

<blockquote><p><strong>Quick check:</strong> You shrink your logo to 48 px and the small details disappear. What should you do?</p><p>Answer: Simplify the logo by removing fine details, because it must stay clear at very small sizes.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> Why make a white version of the logo?</p><p>Answer: It is needed for dark backgrounds, where a dark logo would disappear.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a three-line brief for a business of your choice.</li>
<li>Sketch three ideas on paper and photograph the best.</li>
<li>Build the logo in Canva in a 1000 x 1000 px design.</li>
<li>Create colour, white and icon-only versions on separate pages.</li>
<li>Download each as PNG and keep them together with the brief in one folder.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Simple, memorable, versatile logos work best.</li>
<li>Sketch first, then build, then test at small size.</li>
<li>Prepare colour, white and icon-only versions.</li>
<li>Check licences before using ready-made graphics as a logo.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A photo must be printed on an A4 poster. Which original is the better choice?
- A small screenshot from a chat app
* A sharp photo of about 2500 x 3500 px
- A 400 x 300 px picture saved from a website
- A tiny profile picture
= More pixels are needed for print quality at about 300 DPI.

Q: Where does a calm area in a photo help most?
- It makes the file smaller
* It gives a clear place for text to sit
- It changes the colours
- It makes the photo legal to use
= Text needs a plain area to stay readable.

Q: A shop owner wants a quick way to be consistent on the free plan without a full Brand Kit. What is best?
- Pick random colours each time
* Keep a brand sheet with HEX codes and font names and use it every time
- Use a different font in each design
- Copy a competitor's design
= Consistency comes from discipline and a written reference, with or without the paid Brand Kit.

Q: What is a mockup used for?
- Printing a poster
* Showing a design on a realistic object such as a T-shirt or phone screen
- Fixing spelling
- Removing guide lines
= Mockups help clients see the design in use before it is made.

Q: Which is NOT part of a brand identity?
- Colour palette
- Fonts
* Your Wi-Fi password
- Tone of voice
= A brand identity is the set of visible and verbal elements that make the brand recognisable.

Q: Your logo has fine details that vanish at 48 px. What do you do?
- Make the profile picture bigger on every app
* Simplify the logo
- Add more details
- Use five more colours
= A good logo stays readable at very small sizes.

Q: Why prepare a white version of the logo?
- White logos are cheaper
* It stays visible on dark backgrounds
- Canva requires it
- It makes the logo bigger
= A dark logo disappears on dark backgrounds, so provide a light version.

Q: What should you do before using a customer's photo in an advert?
- Nothing, it is public
* Ask for their permission
- Change the colours
- Add a filter
= Consent protects the person and your reputation.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Complete projects for screen and print',
            'summary' => 'Build finished, professional items step by step: social posts and carousels, print posters, CVs, certificates, cards, menus, presentations and invitations.',
            'lessons' => [
                [
                    'title'   => 'Social media posts and carousels',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to design a single post and a multi-slide carousel that are clear on a phone, consistent with a brand, and ready to publish. This matters because social media is where most small businesses in East Africa advertise, and viewers decide in a moment whether to stop or scroll.</p>

<h3>A post is read on a small screen</h3>
<p>Most people see your design on a phone held at arm's length. That means: big text, few words, strong contrast, and one message per post. A good rule is no more than about 20 to 30 words on a single image. Put the full details in the caption.</p>

<h3>Design a single post</h3>
<ol>
<li>Create a 1080 x 1350 px design (portrait takes more screen space) or 1080 x 1080 px.</li>
<li>Set guides with at least 80 px margins. Keep important content away from the edges because apps may crop previews.</li>
<li>Add a background: a solid brand colour, a calm photo with an overlay, or a pattern at low contrast.</li>
<li>Write the headline (about 80 to 120 px) and one supporting line (about 40 to 50 px).</li>
<li>Add the logo small in a corner, same position in every post.</li>
<li>Add one call to action, such as "Order on WhatsApp 07xx xxx xxx".</li>
</ol>

<h3>A carousel: a story in 5 to 8 slides</h3>
<p>A carousel is a set of swipeable images in one post. Use the <strong>Add page</strong> button under the canvas to add slides, all the same size.</p>
<table>
<thead><tr><th>Slide</th><th>Job</th><th>Example for a bakery</th></tr></thead>
<tbody>
<tr><td>1</td><td>Hook (a question or bold claim)</td><td>"3 mistakes when ordering a cake"</td></tr>
<tr><td>2-5</td><td>One point per slide</td><td>Mistake 1: ordering too late...</td></tr>
<tr><td>6</td><td>Summary or tip</td><td>Order 3 days before</td></tr>
<tr><td>7</td><td>Call to action and contact</td><td>"Order today, UGX 80,000 from"</td></tr>
</tbody>
</table>
<p>Keep the same background, fonts and margins on every slide so they feel like one design. Use an arrow or a "swipe" cue at the first slides. To make a seamless carousel, you can design one wide page and split it, but beginners should start with separate slides.</p>

<h3>Tips that save time</h3>
<ul>
<li>Duplicate page, then change only the text.</li>
<li>Use Copy style (Ctrl + Alt + C, then Ctrl + Alt + V) to repeat styling.</li>
<li>Plan a week of posts in one folder with names such as post-01-promo.png.</li>
<li>Check each post on your own phone before publishing.</li>
</ul>

<h3>Mini-case</h3>
<p>Kevin runs a phone repair stall. His first post has a blurry picture, six lines of text and a tiny phone number. He redesigns it: one clear photo of a repaired screen, headline "Cracked screen? Fixed in 1 hour", price from UGX 30,000, and the WhatsApp number in large type with a contrasting button shape. Enquiries rise because the message is visible in two seconds.</p>

<blockquote><p><strong>Quick check:</strong> A post has 80 words in small text. What do you do?</p><p>Answer: Cut it to the key message in 20 to 30 words and move the rest into the caption, because small text is unreadable on a phone.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> What should the first slide of a carousel do?</p><p>Answer: Hook the viewer with a clear promise or question, because most people decide on the first slide whether to swipe.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick a business and write one message in under 25 words.</li>
<li>Design one 1080 x 1350 px post using your brand sheet colours and fonts.</li>
<li>Then create a five-slide carousel on the same topic with a hook, three points and a call to action.</li>
<li>Check readability on your own phone.</li>
<li>Download as PNG (all pages) and save them in a folder named social-practice.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>One message per post and large, high-contrast text.</li>
<li>Use margins and consistent logo placement.</li>
<li>Carousels tell a story: hook, points, call to action.</li>
<li>Always check on a real phone before posting.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Posters and flyers for print: bleed, margins and export',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to prepare a poster or flyer so a printer can print it without white edges, cut-off text or blurry images. This matters because a print job costs real money in UGX, and a mistake means paying twice.</p>

<h3>Three print words you must know</h3>
<ul>
<li><strong>Bleed:</strong> extra background that extends beyond the final page edge, usually about 3 mm on each side. Printers cut paper by machine, and the cut is never perfect, so bleed prevents thin white lines at the edge.</li>
<li><strong>Trim:</strong> the final size after cutting (for example A4: 210 x 297 mm).</li>
<li><strong>Safe margin:</strong> keep text and logos at least 5 to 10 mm inside the trim line.</li>
</ul>
<table>
<thead><tr><th>Area</th><th>Rule</th></tr></thead>
<tbody>
<tr><td>Background colour or photo</td><td>Extend to the bleed edge (3 mm beyond trim)</td></tr>
<tr><td>Text and logos</td><td>At least 5-10 mm inside the trim edge</td></tr>
<tr><td>Resolution of photos</td><td>About 300 DPI at print size</td></tr>
<tr><td>Colour mode</td><td>CMYK if the printer asks for it; otherwise ask what they prefer</td></tr>
</tbody>
</table>

<h3>Step by step: an A4 flyer</h3>
<ol>
<li>Ask the printer first: paper size, bleed needed, file type (usually PDF), and colour mode. Printers in Kampala, Nairobi and Dar es Salaam often accept PDF files and some also accept JPG.</li>
<li>Create a custom design of 210 x 297 mm.</li>
<li>Show rulers and guides (Shift + R) and set safe margins of 8 to 10 mm.</li>
<li>Design with your three-level hierarchy. Extend the background to cover the whole page edge to edge.</li>
<li>Use high-quality photos and avoid stretching them.</li>
<li>Review at 100 percent zoom and proofread every number, date and phone number.</li>
</ol>

<h3>Exporting for print</h3>
<ol>
<li>Select <strong>Share</strong>, then <strong>Download</strong>.</li>
<li>Choose <strong>PDF Print</strong> as the file type.</li>
<li>Tick <strong>Crop marks and bleed</strong> if your design and plan offer it (in current versions this appears for print documents). If it is not available, ask the printer how they prefer files, or design the background slightly larger than the page yourself.</li>
<li>Select the colour profile if offered (RGB or CMYK; CMYK may need a paid plan) and choose all pages.</li>
<li>Download and open the PDF to check it before sending.</li>
</ol>
<p>PDF Standard is smaller and good for email and screen. PDF Print is heavier and better for printers.</p>

<h3>Cost and honesty</h3>
<p>The free plan can export a PDF Print in current versions, but the CMYK colour mode and some bleed features may require a paid plan. When this is a problem, tell the printer, and be ready to accept slightly different colours. Always do a test print of one copy before printing 500.</p>

<h3>Mini-case</h3>
<p>Ruth ordered 200 church event flyers. Her blue background stopped at the page edge, and 3 of 10 sheets came with thin white lines. The printer explained bleed. She reprinted with the background extended, paid again, and now always checks the bleed first.</p>

<blockquote><p><strong>Quick check:</strong> What is bleed and why is it needed?</p><p>Answer: It is extra background beyond the trim edge, needed because cutting is not perfectly exact and bleed prevents white lines at the edge.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> You place a phone number 2 mm from the edge. What is the risk?</p><p>Answer: It may be cut off or look cramped when trimmed, so keep important content at least 5 to 10 mm inside.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create an A4 flyer for a real or imagined event.</li>
<li>Set margins with guides and extend the background to all edges.</li>
<li>Download PDF Print and tick crop marks and bleed if offered.</li>
<li>Open the PDF and check that all text sits inside the safe area.</li>
<li>Print one copy on A4 paper and note anything to improve.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Bleed is about 3 mm of extra background beyond the trim edge.</li>
<li>Keep text 5 to 10 mm inside the edge.</li>
<li>Use PDF Print and ask your printer about colour and file needs.</li>
<li>Proofread and test print before a large run.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'CVs and certificates',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to design a clean, readable CV and a professional certificate, and export them in the right format. This matters because these documents are judged in seconds: employers, schools and event organisers want clarity, and a design that looks organised suggests a person who is organised.</p>

<h3>A CV that gets read</h3>
<p>Many companies read CVs on screen, and some use software that reads text. That has design consequences.</p>
<ul>
<li><strong>One or two pages</strong> on A4 (210 x 297 mm).</li>
<li><strong>One column or a simple two-column layout</strong> with a clear order: name, contact, profile, experience, education, skills, referees.</li>
<li><strong>Body text 10 to 11 pt,</strong> headings 12 to 14 pt, name 22 to 28 pt.</li>
<li><strong>Two fonts at most,</strong> and one accent colour.</li>
<li><strong>No heavy photos or graphics.</strong> A small, professional photo is optional; many employers do not need it.</li>
<li><strong>Do not use icons instead of words,</strong> and avoid skill rating bars, which are vague and do not read well in software.</li>
</ul>
<p>Download as a <strong>PDF Standard</strong>. A Canva PDF keeps the text selectable in current versions, but some applicant tracking software handles simple layouts best. If a job advert asks for a Word file, use a word processor instead of Canva.</p>

<h3>Build a CV in Canva</h3>
<ol>
<li>Create an A4 document and set 15 mm margins with guides.</li>
<li>Put your name at the top and contact details beneath: phone, email, town. Use a professional email address.</li>
<li>Add a 3-line profile that names your job target and top strengths.</li>
<li>Write experience with the newest first: role, place, dates, and two or three bullet points that start with an action word and show a result ("Increased weekly sales by 20 percent").</li>
<li>Add education and skills, then two referees with permission.</li>
<li>Align everything, check spelling, and export as PDF.</li>
</ol>

<h3>Certificates</h3>
<table>
<thead><tr><th>Part</th><th>What to include</th></tr></thead>
<tbody>
<tr><td>Title</td><td>"Certificate of Completion" or "Certificate of Achievement"</td></tr>
<tr><td>Recipient name</td><td>The largest text after the title</td></tr>
<tr><td>Reason</td><td>One line: what the person did</td></tr>
<tr><td>Date and place</td><td>Clear and correct</td></tr>
<tr><td>Signatures and names</td><td>Two signature lines for authority</td></tr>
<tr><td>Logo and seal</td><td>Top centre; optional seal</td></tr>
<tr><td>Certificate number</td><td>Helps verification</td></tr>
</tbody>
</table>
<p>Use A4 landscape (297 x 210 mm), generous margins, a border at least 10 mm inside the trim, and a serif or elegant font for the name. For many certificates, design one master and use <strong>Bulk create</strong> (in current versions, found through Apps) to fill names from a spreadsheet. Check its current availability and plan limits. Without it, duplicate the page and change the name.</p>
<p>Honesty rule: never design a fake certificate or use a real organisation's name or seal without permission. Certificates must be issued by the organisation that stands behind them.</p>

<h3>Mini-case</h3>
<p>Sarah's CV used two coloured sidebars, five fonts and star ratings. A career adviser suggested a single-column layout, black text with one blue heading colour and a short results-based experience list. She was invited to two interviews the next month.</p>

<blockquote><p><strong>Quick check:</strong> Should a CV use skill rating bars?</p><p>Answer: Better not, since bars are vague and may not read well in screening software; describe your skills with words and examples.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> A job advert asks for a Word file. What do you do?</p><p>Answer: Provide a Word file as requested, because following instructions matters more than design tool preference.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Design your own one-page CV on A4 using a simple layout with one accent colour.</li>
<li>Export it as PDF Standard and check that you can select the text with your mouse.</li>
<li>Design a landscape certificate for a made-up training event.</li>
<li>Duplicate the certificate page three times, each with a different name.</li>
<li>Save both files in your portfolio folder.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A CV needs clear order, readable sizes and results-based bullets.</li>
<li>Avoid decoration that hides content; use PDF Standard unless told otherwise.</li>
<li>A certificate needs title, name, reason, date, signature and a number.</li>
<li>Never forge or imitate an organisation's certificate.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Business cards and menus',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to design a business card and a menu that are readable, correctly sized and ready for a printer. This matters because both are small, everyday pieces where tiny details like text size and spacing decide whether people can use them.</p>

<h3>Business card</h3>
<p>A business card is small, so it has one job: let people contact you quickly. Typical size is 90 x 55 mm (some printers use 85 x 55 mm or 3.5 x 2 inches); always ask the printer. Add 3 mm bleed if they need it, and keep text at least 4 to 5 mm inside the trim edge.</p>
<ol>
<li>Create a custom design of 90 x 55 mm (landscape).</li>
<li>Front: logo, name of business and a short tagline.</li>
<li>Back: your name, job title, phone and WhatsApp number, email, location and one social media handle. Use a clear hierarchy: name largest (about 11 to 14 pt), then details (7 to 9 pt).</li>
<li>Use a minimum of 7 pt text. Smaller than that is unreadable when printed.</li>
<li>Add a QR code if useful. In current versions, search Elements or Apps for "QR code", and test it with a phone before printing. Make it at least about 20 mm wide.</li>
<li>Export as PDF Print with crop marks and bleed if available.</li>
</ol>
<table>
<thead><tr><th>Do</th><th>Avoid</th></tr></thead>
<tbody>
<tr><td>Leave white space</td><td>Cramming every service on one card</td></tr>
<tr><td>Use matt or simple paper to keep costs low</td><td>Heavy decoration that costs more and hides text</td></tr>
<tr><td>Check the phone number twice</td><td>Light grey tiny text</td></tr>
</tbody>
</table>

<h3>Menu design</h3>
<p>A menu is a hierarchy exercise: customers must find a dish and its price in seconds.</p>
<ol>
<li>Choose the size: A4 for tables, A5 for small cafes, or a tall 1080 x 1920 px for a phone or WhatsApp menu.</li>
<li>Group dishes into sections (Breakfast, Mains, Drinks) with a clear heading style.</li>
<li>Write each item as: <strong>name in bold</strong>, short description in regular, price aligned to the same edge. Example: <strong>Rolex Special</strong>, two eggs, chapati, tomato, UGX 6,000.</li>
<li>Use consistent price format: either "6,000" or "UGX 6,000" everywhere.</li>
<li>Limit fonts to two, line spacing 1.3 and enough space between sections.</li>
<li>Add a small photo only where it helps; do not fill the page.</li>
<li>Print on a thicker paper or laminate if customers handle it often. Use a pale background with dark text for the best readability in poor light.</li>
</ol>
<p>Prices change, so keep the master Canva file and update it, and number versions: menu-2026-05-v3.</p>

<h3>Mini-case</h3>
<p>A small restaurant in Jinja had a menu with five fonts and prices in different places. Customers asked the waiter for prices all the time. After aligning prices in one right-hand column, adding dotted lines or spacing and sorting dishes by section, orders became faster, and the owner saved waiter time.</p>

<blockquote><p><strong>Quick check:</strong> What is the smallest text size you should use on a printed business card?</p><p>Answer: About 7 pt, because anything smaller is hard to read on paper.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> Why align all prices to one edge on a menu?</p><p>Answer: Customers can scan down one column to compare prices quickly.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Design a front and back business card for yourself or a small business at 90 x 55 mm.</li>
<li>Create a QR code for a WhatsApp or website link and test it on a phone.</li>
<li>Design an A4 menu with three sections and at least eight items with prices in UGX.</li>
<li>Export the card as PDF Print and the menu as PDF Standard.</li>
<li>Print both at actual size on plain paper and check readability.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A business card is 90 x 55 mm in many places; confirm with the printer.</li>
<li>Keep text at 7 pt or more and important text inside the safe margin.</li>
<li>Menus need sections, aligned prices and consistent formatting.</li>
<li>Test QR codes before printing.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Presentations and event invitations',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a clear presentation and a stylish invitation in Canva and share or print them correctly. This matters because many of the first design jobs people receive are school, church, wedding and company presentations and invitations.</p>

<h3>Presentations: design for a room</h3>
<p>Slides support a speaker; they are not the speaker's script. The audience is often at the back of a room, so type must be large.</p>
<table>
<thead><tr><th>Rule</th><th>Detail</th></tr></thead>
<tbody>
<tr><td>Size</td><td>Presentation 16:9 (1920 x 1080 px)</td></tr>
<tr><td>One idea per slide</td><td>A headline of 5 to 8 words</td></tr>
<tr><td>Body text</td><td>28 pt or more, and no more than about 5 short lines</td></tr>
<tr><td>Contrast</td><td>Dark text on light, or white text on a dark colour, not mid-tones on mid-tones</td></tr>
<tr><td>Visuals</td><td>One strong photo, chart or icon set per slide</td></tr>
<tr><td>Consistency</td><td>Same fonts, colours and logo place on all slides</td></tr>
</tbody>
</table>
<ol>
<li>Create a Presentation (16:9) from Create a design.</li>
<li>Write the story on paper first: opening, three main points, close.</li>
<li>Set the title slide, then duplicate and edit pages to keep layout the same.</li>
<li>Use <strong>Present</strong> (under Share or top right in current versions) to test full screen. Press the arrow keys to move through slides.</li>
<li>Export as PDF for emailing, PPTX if you need to edit it in PowerPoint (formatting may shift, so check), or share a view-only link.</li>
</ol>
<p>Always have a backup: a PDF on a flash drive in case the internet fails.</p>

<h3>Event invitations</h3>
<p>An invitation sets the mood: wedding, graduation, church anniversary, birthday, or a fundraising dinner. It must answer what, who, when, where and how to respond.</p>
<ol>
<li>Choose the size: A5 (148 x 210 mm) for a printed card, or 1080 x 1920 px for a digital WhatsApp invitation.</li>
<li>Write the wording first: event title, host names, date, time, venue with a landmark, dress code if any, and RSVP phone and date.</li>
<li>Use a script or display font for the title only, and a plain readable font for details.</li>
<li>Pick two or three colours that match the occasion. Add one subtle border or pattern.</li>
<li>Check the date and weekday match. Ask someone else to read every detail.</li>
<li>For print, add 3 mm bleed and export PDF Print. For WhatsApp, export PNG or JPG at about 1080 px wide so it opens quickly on a phone.</li>
</ol>
<p>For many guests, a short link to a map location in the caption saves confusion.</p>

<h3>Mini-case</h3>
<p>Joseph's graduation party invitation looked beautiful, but the venue was written as "Near the main road". Half of the guests got lost. He added the exact name, a landmark and a map link in the WhatsApp message, and reissued the design. Information completeness matters as much as beauty.</p>

<blockquote><p><strong>Quick check:</strong> How many ideas should a slide carry?</p><p>Answer: One, because the audience must absorb it in seconds while the speaker explains.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> Which export suits a WhatsApp invitation: PDF Print or PNG at about 1080 px wide?</p><p>Answer: PNG (or JPG) at about 1080 px wide, because it opens quickly on a phone while PDF Print is heavy and meant for printers.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a five-slide presentation about a topic you know with title, three points and a closing slide.</li>
<li>Rehearse it with the Present button and time yourself.</li>
<li>Design an A5 invitation and a WhatsApp version of the same event.</li>
<li>Check each date, time and number with someone else.</li>
<li>Export the slides as PDF and the invitations as PDF Print and PNG.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Slides: one idea, large text, strong contrast, consistent style.</li>
<li>Invitations must answer what, who, when, where and how to reply.</li>
<li>Use PDF Print for printers and PNG/JPG for WhatsApp.</li>
<li>Keep a PDF backup of a presentation.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the best amount of text on a single social media image?
- A full paragraph of 100 words
* A short message of about 20 to 30 words
- No text at all, ever
- As many words as will fit
= Short text is readable on a small phone screen, and details can go in the caption.

Q: What is the main job of slide 1 in a carousel?
- List the prices
* Hook the viewer so they want to swipe
- Show the logo only
- Say goodbye
= The first slide must earn the swipe with a clear promise or question.

Q: What is bleed?
- Red ink in the printer
* Extra background beyond the trim edge to avoid white lines after cutting
- A text margin inside the page
- A type of font
= Bleed protects against small cutting errors.

Q: Where should important text sit on a printed flyer?
- Right on the trim edge
* At least 5 to 10 mm inside the trim edge
- Outside the bleed
- Anywhere, since printers adjust
= A safe margin avoids text being cut off or crowded.

Q: Which export suits a flyer you will take to a printer?
- A small GIF
* PDF Print, with crop marks and bleed if offered
- An MP4
- A transparent PNG of 200 px
= PDF keeps layout and quality for the printer.

Q: Which is the best choice on a CV?
- Skill rating bars with five stars
* Clear headings and results-based bullet points
- A different font for each section
- Light grey text to look elegant
= Clear structure and evidence are what employers look for.

Q: What is a sensible minimum text size on a printed business card?
- 3 pt
- 4 pt
* About 7 pt
- 2 mm tall
= Text smaller than about 7 pt becomes difficult to read on paper.

Q: Your WhatsApp invitation PDF Print file is 25 MB and opens slowly. What is the better choice?
- Send it anyway
* Export a PNG or JPG at about 1080 px wide
- Convert to an MP4
- Remove the date
= Screen sharing needs a lighter image file, not a print-quality PDF.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Infographics, video, teamwork and AI tools',
            'summary' => 'Explain data visually, make simple videos, work with a team and use AI design tools with care.',
            'lessons' => [
                [
                    'title'   => 'Infographics and charts',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to turn facts and numbers into a clear infographic with a chart, icons and a logical reading order. This matters because NGOs, schools and businesses must explain results to donors, parents and customers who will not read a long report.</p>

<h3>What makes an infographic work</h3>
<ul>
<li><strong>One main message</strong> such as "Our clean water project reached 4,200 people".</li>
<li><strong>A clear path:</strong> top to bottom (long vertical format) or left to right, with numbered steps if there is a sequence.</li>
<li><strong>Few numbers, big:</strong> make the key figure huge and the label small.</li>
<li><strong>Honest data:</strong> show where it comes from and the date. Never stretch a chart to exaggerate.</li>
<li><strong>Simple visuals:</strong> one icon style throughout.</li>
</ul>

<h3>Choosing the right chart</h3>
<table>
<thead><tr><th>You want to show</th><th>Use</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Compare amounts</td><td>Bar chart</td><td>Students per class</td></tr>
<tr><td>Change over time</td><td>Line chart</td><td>Monthly sales for a year</td></tr>
<tr><td>Parts of a whole (few parts)</td><td>Pie or doughnut chart</td><td>How donations are spent (4 parts)</td></tr>
<tr><td>One big number</td><td>Large text with a short label</td><td>"85%" of children vaccinated</td></tr>
<tr><td>A process</td><td>Numbered steps with arrows</td><td>How to apply for a loan</td></tr>
</tbody>
</table>

<h3>Making a chart in Canva</h3>
<ol>
<li>In <strong>Elements</strong>, search for "Charts" (in current versions, under Charts) and choose bar, line or pie.</li>
<li>The chart is placed on the page. Select it and open <strong>Data</strong> or double-click it to edit the table of values.</li>
<li>Type labels and numbers or paste them from a spreadsheet. In current versions you can also import data from a CSV file.</li>
<li>Change chart colours with the colour tool; use your brand palette and make the most important bar the accent colour.</li>
<li>Remove clutter: delete heavy gridlines, shorten labels and place values directly on the bars.</li>
</ol>

<h3>Worked example: a one-page NGO impact infographic</h3>
<p>Size: 1080 x 2400 px (long vertical, for WhatsApp and Facebook) or A4 for print. Structure:</p>
<pre>1. Title: "One year of clean water in Kasese"     (large, top)
2. Big number: 4,200 people served                (110 px, accent colour)
3. Bar chart: wells built per quarter (3, 5, 6, 9)
4. Three icons: wells built / trainings held / schools reached
5. Short "How you can help" line with contact
6. Footer: source "Project records, 2025" + logo</pre>
<p>Use a column of margins (80 px), the same icon style, and a limit of three colours plus neutrals. Keep text above 36 px so it stays readable on a phone.</p>

<h3>Common mistakes</h3>
<ul>
<li>A pie chart with ten slices, too many to compare.</li>
<li>3D effects that distort the sizes.</li>
<li>Bars that do not start from zero, which exaggerates differences.</li>
<li>Using photos of real people in need without their consent. Respect dignity.</li>
</ul>

<blockquote><p><strong>Quick check:</strong> You want to show monthly sales over a year. Which chart?</p><p>Answer: A line chart, because it shows how a value changes over time.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> Why should a bar chart start at zero?</p><p>Answer: If it does not, small differences look huge, which is misleading.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Collect four real numbers about a school, shop or community project.</li>
<li>Create a 1080 x 2400 px design with a title, one big number and a bar chart.</li>
<li>Edit the chart data in Canva and colour the key bar with your accent colour.</li>
<li>Add three matching icons and a source line.</li>
<li>Download as PNG and test on your phone.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Start with one message and one reading path.</li>
<li>Match the chart to the question: bars compare, lines show time, pies show a few parts.</li>
<li>Make key numbers large and cite your source.</li>
<li>Keep data honest: start bars at zero and avoid 3D effects.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Simple video and animation',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to create a short vertical video with text animation, music and transitions in Canva and export it as MP4. This matters because short videos are favoured by many platforms, and a 15-second ad often reaches more people than a still image.</p>

<h3>Plan before you build</h3>
<p>Short videos work when they are planned. A 15 to 30 second advert can use this structure:</p>
<table>
<thead><tr><th>Time</th><th>Content</th></tr></thead>
<tbody>
<tr><td>0-3 seconds</td><td>Hook: a question or striking image</td></tr>
<tr><td>3-12 seconds</td><td>Three quick points or a product demonstration</td></tr>
<tr><td>12-15 seconds</td><td>Call to action with contact or location</td></tr>
</tbody>
</table>
<p>Most people watch without sound, so use readable on-screen text. Keep each text on screen for long enough to read, about one second for every three words.</p>

<h3>Create the video</h3>
<ol>
<li>Create a design of 1080 x 1920 px (Story, Reel, Status), or choose Video from the design types.</li>
<li>Add pages as scenes. At the bottom, the timeline (in current versions, shown with a Timeline button) lets you set each page's length. Select the page duration (a clock icon) and set 2 to 4 seconds.</li>
<li>Add your video clips, photos and text. Upload your phone clips under Uploads.</li>
<li>Trim a clip: select it and use the trim handles on the timeline.</li>
<li>Apply <strong>Animate</strong> to text and graphics. Choose a gentle entrance such as Fade or Rise, and use the same animation style on all scenes.</li>
<li>Add <strong>Transitions</strong> between pages (small icon between pages), for example a simple fade or slide. Using one style looks professional; ten styles look chaotic.</li>
<li>Add music from the Audio section. Check that the track is available for your intended use (Canva shows its own licensed audio; do not upload popular songs, which can lead to muting or a copyright claim).</li>
<li>Select <strong>Share</strong>, <strong>Download</strong>, choose <strong>MP4 Video</strong>, and select the pages.</li>
</ol>
<p>You can also use <strong>GIF</strong> for a short looping animation. Free plans can export MP4 and GIF; some premium videos and elements need a paid plan.</p>

<h3>Animation guidelines</h3>
<ul>
<li>Animate to guide attention, not to decorate. A moving price tag draws the eye; moving everything tires it.</li>
<li>Keep animations short: 0.5 to 1 second.</li>
<li>Avoid flashing effects that can harm people sensitive to flashing light.</li>
<li>Add captions or text for sound content, so deaf or hard-of-hearing people and people on mute can follow.</li>
</ul>

<h3>File size and data costs</h3>
<p>In many places mobile data is expensive. A 60 MB video can frustrate viewers. Keep videos short, and if the file is too large for WhatsApp, export a shorter version or lower quality (some download options offer a quality choice such as 720p or 1080p in current versions). Test on a mid-range phone, not only your own.</p>

<h3>Mini-case</h3>
<p>Mercy sells hair products. Her image ads get few views. She creates a 15-second video: three scenes (before, product, after), on-screen text "Soft hair in 2 weeks, UGX 25,000", a soft fade transition and licensed upbeat music. She posts it to Status and Instagram Reels, and several people ask where to buy.</p>

<blockquote><p><strong>Quick check:</strong> Why include on-screen text in a video ad?</p><p>Answer: Many viewers watch without sound, so text carries the message even on mute.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> Can you freely use a famous pop song as background music?</p><p>Answer: No, because it is copyrighted, so use licensed audio from the tool or music you have rights to.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a 3-scene storyboard on paper for a 15-second advert.</li>
<li>Create the design at 1080 x 1920 px with one scene per page.</li>
<li>Add text animation and one transition type to all scenes.</li>
<li>Add licensed music at a low volume and preview.</li>
<li>Download as MP4 and check the file size and playback on your phone.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Plan: hook, points, call to action in 15 to 30 seconds.</li>
<li>Use one animation style and one transition style.</li>
<li>Always add text so the video works without sound.</li>
<li>Use licensed music and watch file size.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Teamwork: sharing, comments and team templates',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to share a design safely, give and receive comments, and set up reusable templates for a team. This matters because design is rarely done alone: a school club, church media team or small agency needs everyone to use the same files and brand without breaking them.</p>

<h3>Sharing a design</h3>
<ol>
<li>Open the design and select <strong>Share</strong> (top right).</li>
<li>To invite someone, type their email address and choose a permission: <strong>Can edit</strong>, <strong>Can comment</strong> or <strong>Can view</strong>.</li>
<li>To share by link, choose the link setting (in current versions, a link-access option) and set the permission carefully. Choose "Only you and people you share with" if the design is private.</li>
<li>Send the link by email or WhatsApp.</li>
</ol>
<table>
<thead><tr><th>Permission</th><th>Meaning</th><th>Best for</th></tr></thead>
<tbody>
<tr><td>Can view</td><td>See only</td><td>Final approval, public presentation</td></tr>
<tr><td>Can comment</td><td>See and leave comments</td><td>Clients, supervisors</td></tr>
<tr><td>Can edit</td><td>Change the design</td><td>Teammates only</td></tr>
</tbody>
</table>
<p>Never give edit access to a client unless you are ready for changes you did not plan. For "view only" final delivery, they can download the file you export.</p>

<h3>Comments and feedback</h3>
<p>Open the Comments tool (a speech-bubble icon on the toolbar or in the menu in current versions), click the place on the design, and type. You can mention a colleague with @ and their name so they are notified. Resolve a comment once fixed to keep the list tidy.</p>
<ul>
<li>Good feedback is specific: "Make the date larger" is better than "I don't like it".</li>
<li>Ask clients to put all feedback in one round, not scattered across WhatsApp and calls.</li>
<li>Use version names and the page history (in current versions, a version history under File) to go back if needed.</li>
</ul>

<h3>Team templates and brand control</h3>
<p>On Canva Teams or education/nonprofit plans (check your plan), you can create a team, shared folders and templates. A common workflow:</p>
<ol>
<li>One designer builds a master template (for example "Weekly event poster") with brand colours, fonts and the logo.</li>
<li>The master is saved as a template or a shared design in a team folder. Look for the option to "Share as template" or brand templates (the latter on certain paid plans).</li>
<li>Team members open the template, and Canva creates their own copy to edit.</li>
<li>Admins can set permissions or approvals on bigger plans.</li>
</ol>
<p>On free accounts you can imitate this: keep a master design in a folder, and always "Make a copy" (File menu, then Make a copy) before editing.</p>

<h3>Good team habits</h3>
<ul>
<li>Agree a naming system and folder structure.</li>
<li>Decide who has final approval.</li>
<li>Share only with the people who need access; remove access after the project.</li>
<li>Do not put private information (national ID numbers, phone numbers of minors) in shared designs.</li>
</ul>

<h3>Mini-case</h3>
<p>A university media club had five people editing one poster. Text kept getting moved and nobody knew which version was final. They created a master, gave edit rights to two designers only, gave everyone else comment rights, and named exported files with the date. Arguments stopped.</p>

<blockquote><p><strong>Quick check:</strong> A client should approve your design but not change it. Which permission do you give?</p><p>Answer: Can comment (or Can view), because they can give feedback without altering the design.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> You have a master poster and a teammate wants to use it. What is the safest step?</p><p>Answer: Ask them to make a copy first, so the master stays unchanged.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a master poster design with your brand colours and fonts.</li>
<li>Share it with a friend with "Can comment" permission.</li>
<li>Ask your friend to leave two specific comments on it.</li>
<li>Reply, fix the issues and resolve the comments.</li>
<li>Make a copy named "poster-week-2" and change only the text.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Match permission to the person: view, comment or edit.</li>
<li>Use comments and mentions for clear, specific feedback.</li>
<li>Work from a master and make copies instead of changing the original.</li>
<li>Team templates and brand controls depend on your plan.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'AI design tools in Canva: uses and limits',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what AI design tools can and cannot do, use them as a helper rather than a replacement for your judgement, and avoid legal and quality problems. This matters because clients may ask "can't AI do this for free?", and you need an honest, confident answer.</p>

<h3>What AI tools in Canva do, conceptually</h3>
<p>Canva includes a set of AI-powered features, grouped in current versions under names such as <strong>Magic Studio</strong> or a Canva AI assistant. Names, limits and prices change often, so look for the tool by name in your editor or the Canva Help Centre. Typical abilities:</p>
<table>
<thead><tr><th>Idea</th><th>What it does</th><th>Limit</th></tr></thead>
<tbody>
<tr><td>Text to image</td><td>Creates an image from a written description</td><td>Hands, text and cultural details are often wrong; style may not match your brand</td></tr>
<tr><td>Magic Write (text help)</td><td>Drafts or rewrites copy</td><td>May be generic or inaccurate; you must fact-check</td></tr>
<tr><td>Magic Edit / Eraser</td><td>Changes or removes parts of a photo</td><td>Edges can look fake; check at full size</td></tr>
<tr><td>Magic Resize / Switch</td><td>Adapts a design to other sizes or formats</td><td>Layout may need manual fixing</td></tr>
<tr><td>Background remover</td><td>Cuts out a subject</td><td>Hair and thin parts can be rough</td></tr>
</tbody>
</table>
<p>Free plans often have a small number of AI uses, with more on paid plans. Check the current limits.</p>

<h3>How to use AI well</h3>
<ol>
<li><strong>Know what you want first.</strong> AI amplifies a clear brief; it cannot invent your client's brand for you.</li>
<li><strong>Write specific prompts:</strong> subject, style, colours, mood and what to avoid. Example: "A warm flat illustration of a bakery counter with bread, brown and yellow colours, no text".</li>
<li><strong>Generate several options</strong> and choose the best, then edit it.</li>
<li><strong>Check details:</strong> faces, hands, spelling, and cultural accuracy. AI often struggles to show African settings and clothing accurately, so review for stereotypes.</li>
<li><strong>Edit for brand:</strong> change colours, crop and add your own typography.</li>
</ol>

<h3>Limits and responsibilities</h3>
<ul>
<li><strong>Quality:</strong> AI output can look generic. Your craft, from layout to type to colour, still decides if a design works.</li>
<li><strong>Accuracy:</strong> do not use AI-generated pictures of real people, real places or products to mislead customers. A picture of a "real" dish that does not exist can be false advertising.</li>
<li><strong>Copyright and licence:</strong> the rules for AI-generated images differ between countries and platforms, and are still developing. Read the current Canva terms for what you may do with generated content, and avoid using AI to copy a living artist's style or a competitor's logo.</li>
<li><strong>Privacy:</strong> do not upload client documents, private photos or children's pictures into an AI tool without permission.</li>
<li><strong>Honesty:</strong> if a client or a school requires disclosure of AI use, tell them. It builds trust.</li>
<li><strong>Brand safety:</strong> a logo that comes from AI may not be unique, so it can be hard to trademark. For important identities, design your own.</li>
</ul>

<h3>Mini-case</h3>
<p>Ivan uses text-to-image to make a banner for a Kampala bakery, but the AI bread has six fingers of dough and gibberish on the sign. He generates five versions, chooses one, crops out the sign, adds the bakery's real logo and text in the brand font, and checks everything at full size. AI saved time; his judgement saved the job.</p>

<blockquote><p><strong>Quick check:</strong> An AI image shows a sign with strange letters. What do you do?</p><p>Answer: Crop or cover it and add real text yourself, because AI often produces unreadable lettering.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> Is it wise to upload a client's private documents into an AI tool?</p><p>Answer: No, not without permission, since it may expose confidential information.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open the AI tools available in your Canva (look for Magic Studio or an AI assistant) and note which are free for you.</li>
<li>Write a detailed prompt for a background image for a poster and generate options.</li>
<li>Choose one, edit it (crop, recolour) and add your own text and logo.</li>
<li>Write down three flaws in the AI image that you had to fix.</li>
<li>Save the poster as a PNG with a note saying how AI was used.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>AI is a fast helper for ideas, images and resizing, not a replacement for design judgement.</li>
<li>Always inspect AI output for errors, stereotypes and fake text.</li>
<li>Check the current licence, limits and privacy rules.</li>
<li>Be honest with clients about when AI was used.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: You need to show how sales changed month by month over a year. Which chart suits best?
- Pie chart
* Line chart
- Table of icons
- Mockup
= A line chart shows change over time clearly.

Q: Why should a bar chart axis start at zero?
- It prints cheaper
* Otherwise small differences look larger than they are
- Canva requires it
- It makes the bars colourful
= Honest data design avoids exaggerating differences.

Q: Why add on-screen text to a short video advert?
- It makes the file smaller
* Many people watch with the sound off
- It replaces the need for a hook
- Platforms demand it
= Text keeps the message clear on mute.

Q: Which music choice is safest for a business video?
- A popular song you downloaded
* Licensed audio provided in the tool or music you have the right to use
- Any song from a friend's phone
- A song with the artist name in the caption
= Copyrighted songs can lead to muted videos or claims, even if you credit the artist.

Q: A client should review your design but not change it. Which permission?
- Can edit
* Can comment
- Owner
- Admin
= Comment access lets them give feedback without altering the design.

Q: Why make a copy of a master design before editing?
- To make the file bigger
* To keep the master unchanged for future use
- Because Canva requires copies
- To remove the brand colours
= A protected master saves time and keeps the brand consistent.

Q: AI generated a banner with garbled text on the sign. What is the best response?
- Publish it, nobody will notice
* Remove or replace the text with your own typography
- Add more AI text
- Blame the client
= AI often produces unreadable lettering, so real text must be added by the designer.

Q: Which is an honest limit of AI design tools?
- They are always correct
* They can produce generic, inaccurate or stereotyped results that need a human check
- They need no brief
- They remove all copyright questions
= Human review remains essential.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 6
        [
            'title'   => 'Module 6: Exporting, rights, portfolio and capstone',
            'summary' => 'Export files correctly, respect copyright, present your work and find clients, then complete a full brand identity project.',
            'lessons' => [
                [
                    'title'   => 'Exporting correctly: PNG, JPG, PDF, SVG and MP4',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose the correct file format and settings for any delivery: website, WhatsApp, email, printer or video platform. This matters because a perfect design exported in the wrong format looks poor, loads slowly or prints badly, and the client will blame you.</p>

<h3>How to download</h3>
<ol>
<li>Select <strong>Share</strong> in the top right, then <strong>Download</strong>.</li>
<li>Choose the <strong>File type</strong> from the list.</li>
<li>Choose <strong>Select pages</strong> (all pages or specific ones).</li>
<li>Adjust options such as <strong>Size</strong> (a slider that scales the output), <strong>Compress file</strong>, <strong>Transparent background</strong> (paid) or <strong>Crop marks and bleed</strong> (print PDF).</li>
<li>Select <strong>Download</strong>. Check the file in a viewer before sending.</li>
</ol>

<h3>Choosing a format</h3>
<table>
<thead><tr><th>Format</th><th>Best for</th><th>Notes</th></tr></thead>
<tbody>
<tr><td>PNG</td><td>Logos, graphics with text, screenshots, transparent backgrounds</td><td>Sharp edges, bigger files; transparency is a paid option in Canva</td></tr>
<tr><td>JPG</td><td>Photos, WhatsApp, email, websites</td><td>Smaller files; quality loss if compressed too much; no transparency</td></tr>
<tr><td>PDF Standard</td><td>Documents to read or email: CVs, reports, menus</td><td>Keeps text sharp; smaller</td></tr>
<tr><td>PDF Print</td><td>Printer-ready flyers, posters, cards</td><td>Higher quality; bleed and crop marks available</td></tr>
<tr><td>SVG</td><td>Logos and icons that must scale to any size</td><td>Vector format; usually a paid export in Canva</td></tr>
<tr><td>MP4</td><td>Video</td><td>Plays on most devices; choose resolution to control size</td></tr>
<tr><td>GIF</td><td>Short looping animation</td><td>Low colour quality, can be large</td></tr>
</tbody>
</table>

<h3>Quality and size</h3>
<p>Screen images are measured in pixels. Printing needs more pixels per inch. If your poster is A3 (297 x 420 mm) at 300 DPI, it needs about 3508 x 4961 px. Canva sets the output size from the design and plan, and Pro users can raise the size slider; on free plans a large print poster may be limited, so print PDF is the better route as it keeps vector text sharp.</p>
<table>
<thead><tr><th>Delivery</th><th>Format and setting</th></tr></thead>
<tbody>
<tr><td>Instagram or Facebook post</td><td>PNG or JPG at design size (1080 px)</td></tr>
<tr><td>WhatsApp image</td><td>JPG, under about 1 MB if possible</td></tr>
<tr><td>Email attachment CV</td><td>PDF Standard</td></tr>
<tr><td>Printer, flyer</td><td>PDF Print, bleed 3 mm, crop marks</td></tr>
<tr><td>Website banner</td><td>JPG or PNG; compress file</td></tr>
<tr><td>Logo handover</td><td>PNG (transparent if possible), PDF, SVG if available</td></tr>
<tr><td>Video advert</td><td>MP4 at 1080 x 1920 px for vertical</td></tr>
</tbody>
</table>

<h3>File names and checks</h3>
<ul>
<li>Use names such as <code>rose-bakery-logo-white.png</code> instead of "Design (3).png".</li>
<li>Open every exported file and zoom in. Check fonts, spelling and edges.</li>
<li>Keep an editable copy in Canva and a folder of final exports.</li>
<li>Compress large images for web and chat to save mobile data.</li>
</ul>

<h3>Mini-case</h3>
<p>Esther sent a 18 MB PNG poster to her client on WhatsApp, and it took forever to download. She also sent a small JPG version for preview and a PDF Print version to the printer. The client replied that the delivery felt professional because each file had a purpose.</p>

<blockquote><p><strong>Quick check:</strong> Which format keeps a logo sharp at any size?</p><p>Answer: SVG, because it is a vector format that scales without losing quality.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> Which export is best for a photo-heavy post sent over WhatsApp?</p><p>Answer: JPG, since it keeps photos looking good at a smaller file size.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open one finished design from this course.</li>
<li>Export it as PNG, JPG and PDF Standard.</li>
<li>Compare the three file sizes in File Explorer or your phone Files app.</li>
<li>Write a short note naming the best use of each file.</li>
<li>Rename each file with a clear name and put them in a final-exports folder.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Pick the format for the purpose: PNG, JPG, PDF Standard, PDF Print, SVG, MP4.</li>
<li>Print needs PDF Print with bleed; web and chat need smaller JPG or PNG.</li>
<li>Transparent PNG and SVG exports are usually paid.</li>
<li>Name files clearly and check every export.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Copyright, licences and fair practice',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use images, fonts, music and templates legally, credit creators properly and protect your own work. This matters because one careless copied image can lead to a takedown, a fine, a lost client or damage to your reputation.</p>

<h3>The basic idea</h3>
<p><strong>Copyright</strong> means that the creator of a photo, illustration, font, song or design owns the right to decide how it is copied and used. Finding something on Google or Pinterest does not make it free. In Uganda, Kenya, Tanzania and most countries, copyright protects creative work automatically, without registration, although the exact rules differ by country. This lesson is general education, not legal advice. For serious disputes, speak to a qualified lawyer.</p>

<h3>Types of licence</h3>
<table>
<thead><tr><th>Type</th><th>What it usually means</th><th>Example use</th></tr></thead>
<tbody>
<tr><td>Public domain / CC0</td><td>No restrictions</td><td>Very old works; items marked as dedicated to the public domain</td></tr>
<tr><td>Creative Commons (CC BY)</td><td>Free to use with credit to the creator; other CC types may restrict commercial use or changes</td><td>Blog images with credit</td></tr>
<tr><td>Free-to-use stock libraries</td><td>Free for most uses under the site's own terms; read what is not allowed</td><td>Photos for posts and posters</td></tr>
<tr><td>Paid / premium licence</td><td>Allowed for the uses stated, often for one project or with limits on copies</td><td>Canva premium elements, paid stock</td></tr>
<tr><td>All rights reserved</td><td>You need permission</td><td>A photographer's portfolio, a pop song</td></tr>
</tbody>
</table>

<h3>Using Canva content</h3>
<ul>
<li>Canva's photos, graphics, audio and templates come with a content licence that allows use in your designs, but with restrictions. Typically you may not sell the raw element alone, use it as a trademark or logo, or build a competing library. Read the current Content License Agreement in the Canva Help Centre.</li>
<li>If you use premium items, you need the paid plan or one-time purchase before you publish.</li>
<li>Selling a template you made from Canva elements is allowed only under particular rules, so check the template creator terms.</li>
</ul>

<h3>Fonts, music and photos</h3>
<ul>
<li><strong>Fonts:</strong> check the licence. Google Fonts are open source and generally free for commercial use; fonts downloaded from random websites may not be.</li>
<li><strong>Music:</strong> use licensed audio. Credit does not replace permission.</li>
<li><strong>People's photos:</strong> get permission for adverts. For children, get consent from a parent or guardian, and think about safety before showing faces, school names and locations.</li>
<li><strong>Logos and brands:</strong> do not copy another brand's logo. Using a known brand name in a way that suggests partnership can mislead people.</li>
</ul>

<h3>Credits</h3>
<p>When a licence requires attribution, write it near the work or in the caption: "Photo: Name, via source, licence". Keep a simple record of where every asset came from.</p>

<h3>Protecting your own work</h3>
<ul>
<li>Keep your source files and dated drafts as proof you made the work.</li>
<li>Add a small watermark or use a low-resolution preview when sharing with prospects who have not paid.</li>
<li>Write a short contract or agreement saying who owns the final design and how it can be used. Many freelancers transfer the final design to the client after full payment but keep the right to show it in their portfolio. Agree this in writing.</li>
</ul>

<h3>Mini-case</h3>
<p>Daniel used a photo he found online for a client's billboard. The photographer wrote to the client demanding payment. Daniel had to pay to resolve the problem and lost the client's trust. After that, he used only his own photos, free-licence libraries, or paid stock, and kept a sheet listing each source.</p>

<blockquote><p><strong>Quick check:</strong> Is a photo from a Google image search free to use?</p><p>Answer: No, because being easy to find does not mean it is free; check the licence.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> A licence says "CC BY". What must you do?</p><p>Answer: Give credit to the creator as the licence requires, because attribution is the main condition.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a simple spreadsheet or text file named asset-log with columns: item, where from, licence, credit needed.</li>
<li>Find three images from a free-licence library and record their licence terms.</li>
<li>Design a post that uses one and credit it correctly in the caption.</li>
<li>Read the section about content use in the Canva Help Centre and write two rules in your own words.</li>
<li>Draft a two-line statement about who owns your final designs.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Copyright is automatic: findable does not mean free.</li>
<li>Know licence types and follow their conditions, including credit.</li>
<li>Get consent for people's images, especially children.</li>
<li>Keep an asset log and put ownership terms in writing.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Building a portfolio and finding design work in East Africa',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a portfolio of your best work, present it professionally, price your services and find your first paying clients. This matters because clients do not hire a certificate, they hire proof that you can solve their problem.</p>

<h3>What goes in a portfolio</h3>
<p>Show 6 to 10 strong pieces, not 40 average ones. Aim for variety that proves range, but a clear style that proves taste:</p>
<ul>
<li>One brand identity (logo, colours, fonts, card, social post).</li>
<li>Two social media posts or carousels.</li>
<li>One print piece (poster or flyer) with a mockup.</li>
<li>One document (CV, menu or certificate).</li>
<li>One video or animated post.</li>
</ul>
<p>For each project, write a short case study: <strong>the problem</strong>, <strong>what you did</strong>, <strong>the result</strong>. For example: "Mama Rose Bakery needed a simple brand for social media. I designed a logo and template set. Orders by WhatsApp increased in the first month." Use real numbers only if they are true. Practice projects are fine, but label them honestly as concept work.</p>

<h3>Where to present it</h3>
<table>
<thead><tr><th>Option</th><th>Cost</th><th>Notes</th></tr></thead>
<tbody>
<tr><td>Canva presentation or website exported or shared by a view link</td><td>Free (check plan features)</td><td>Quick and easy; a PDF portfolio also works</td></tr>
<tr><td>Social media page (Instagram, Facebook, LinkedIn)</td><td>Free</td><td>Post consistently; clients find you here</td></tr>
<tr><td>Behance or similar portfolio platform</td><td>Free tiers exist</td><td>Good for visibility to wider employers</td></tr>
<tr><td>WhatsApp Business catalogue</td><td>Free</td><td>Good for local clients who prefer WhatsApp</td></tr>
</tbody>
</table>
<p>Make a clean cover, a short "About me" slide, your project pages, and a "Contact" page with email and WhatsApp. Keep the file small enough to send over WhatsApp, under about 10 MB.</p>

<h3>Finding work</h3>
<ol>
<li><strong>Start local:</strong> shops, salons, restaurants, schools, churches, SACCOs, clinics, and NGOs near you often need posts, posters and logos but have no designer.</li>
<li><strong>Offer a small first job</strong> at a clear price, such as "10 social posts for UGX 150,000" (an example; set your own price).</li>
<li><strong>Online platforms:</strong> freelance marketplaces and social media let you reach clients in Kenya, Uganda, Tanzania and abroad. Read each platform's fees and payment rules, and beware of upfront-payment scams or offers that ask you to pay first.</li>
<li><strong>Ask for referrals and testimonials</strong> after each job, and add them to your portfolio.</li>
<li><strong>Post your process,</strong> not just final images, to show how you think.</li>
</ol>

<h3>Pricing and working safely</h3>
<ul>
<li>Price by value and time: estimate hours, add revision rounds, and quote a package. Do not work for "exposure" alone.</li>
<li>Ask for a deposit (for example half) before starting; deliver final files after payment.</li>
<li>Use a short written agreement: what is included, number of revisions, deadline, payment method (mobile money or bank), and who owns the design.</li>
<li>Keep records of income. In your country, tax rules may apply once you earn regularly; ask the tax authority or an adviser.</li>
<li>Communicate politely and set response times.</li>
</ul>

<h3>Mini-case</h3>
<p>Patience, a graduate in Mbarara, had no clients. She designed a free sample brand kit for three local shops (clearly labelled as practice), showed them on mockups, and posted the results. Two shop owners offered paid work for monthly posts, and a third referred her to a school.</p>

<blockquote><p><strong>Quick check:</strong> How many pieces should a beginner portfolio contain?</p><p>Answer: About 6 to 10 strong ones, because quality shows skill better than quantity.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> A new client wants all files before paying. What do you do?</p><p>Answer: Ask for a deposit before starting and deliver final files after full payment, as set in your agreement.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Select your best six designs from this course.</li>
<li>Create a Canva presentation with a cover, an about page, six project pages and a contact page.</li>
<li>Write a three-line case study under each project.</li>
<li>Export as PDF and check the size for WhatsApp.</li>
<li>Write a price list for three services in UGX (or your currency) and a simple agreement of five lines.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A small set of strong, explained projects beats a large mixed gallery.</li>
<li>Start with local businesses and ask for referrals.</li>
<li>Use deposits, written agreements and clear revisions.</li>
<li>Label practice work honestly and avoid advance-payment scams.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Capstone: a complete brand identity project',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will have designed a complete brand identity for one real or imagined organisation, using everything from the course. This matters because a finished project, with a brief, a logo, a system and examples in use, is the strongest thing you can show a client or employer.</p>

<h3>Choose your client</h3>
<p>Pick a small organisation you know or can imagine: a juice bar, tailoring shop, youth football club, church choir, school science club, farm cooperative or a local NGO. If it is a real organisation, get permission to use its name and say clearly if it is practice work.</p>

<h3>The deliverables</h3>
<table>
<thead><tr><th>#</th><th>Item</th><th>Size and format</th></tr></thead>
<tbody>
<tr><td>1</td><td>Brief and research (one page)</td><td>A4 PDF</td></tr>
<tr><td>2</td><td>Logo: colour, white and icon-only versions</td><td>PNG (transparent if possible), PDF</td></tr>
<tr><td>3</td><td>Brand sheet: colours with HEX codes, two fonts, logo rules, photo style</td><td>A4 PDF</td></tr>
<tr><td>4</td><td>Social media set: one post, one carousel of 5 slides, one story</td><td>1080 x 1350, 1080 x 1920 px PNG</td></tr>
<tr><td>5</td><td>Print piece: A4 flyer or A3 poster with bleed</td><td>PDF Print</td></tr>
<tr><td>6</td><td>Business card (front and back)</td><td>90 x 55 mm, PDF Print</td></tr>
<tr><td>7</td><td>Mockups: logo on a shirt or signboard, poster on a wall</td><td>PNG</td></tr>
<tr><td>8</td><td>Presentation of the project</td><td>PDF, 8 to 12 slides</td></tr>
</tbody>
</table>

<h3>Step-by-step plan</h3>
<ol>
<li><strong>Brief (day 1).</strong> Answer: who is the audience, what do they offer, what three words describe the feeling, what problems do they have, and who are their competitors? Find three competitor logos and note what you will do differently.</li>
<li><strong>Sketch (day 1-2).</strong> Draw ten logo ideas. Choose two and ask three people which is clearer.</li>
<li><strong>Logo (day 2-3).</strong> Build it in Canva, test at 48 px and in black and white, and make the three versions.</li>
<li><strong>Brand sheet (day 3).</strong> Choose palette and fonts, checking contrast. Add them to a Brand Kit if your plan allows.</li>
<li><strong>Applications (day 4-6).</strong> Create the social set, the print piece and the card using the brand sheet. Check hierarchy, alignment, margins and bleed.</li>
<li><strong>Mockups and presentation (day 7).</strong> Show your work in use, with a short story for each page.</li>
<li><strong>Review and export (day 8).</strong> Use the checklist below, then export every file correctly and arrange them in folders.</li>
</ol>

<h3>Final checklist</h3>
<ul>
<li>Is there a clear hierarchy in each piece (squint test)?</li>
<li>Is text readable: size, line spacing and contrast of about 4.5 to 1?</li>
<li>Are margins and alignment consistent?</li>
<li>Do all pieces use the same logo, colours and fonts?</li>
<li>Is all content licensed, with an asset log?</li>
<li>Are print files at the correct size with bleed and safe margins?</li>
<li>Are file names clear and exports in the right formats?</li>
<li>Has someone else proofread every word and number?</li>
</ul>

<h3>Mini-case</h3>
<p>Joan chose "Kampala Youth Chess Club". Her brief showed that rivals used dull colours and long names. She designed a bold knight icon in navy and gold, wrote a short brand sheet, and made a flyer, a story and a membership card. Her presentation told the story in eight slides. A local school asked her to design its sports club identity after seeing it.</p>

<blockquote><p><strong>Quick check:</strong> What should you do before building the logo?</p><p>Answer: Write the brief and sketch ideas on paper, because planning prevents wasted time and weak concepts.</p></blockquote>
<blockquote><p><strong>Quick check:</strong> Why test the logo in black and white and at 48 px?</p><p>Answer: It shows whether the logo stays clear without colour and in tiny uses like profile pictures.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose your organisation and write the one-page brief.</li>
<li>Sketch ten logo ideas, choose one, and build it in Canva with all three versions.</li>
<li>Create the brand sheet and the application pieces from the deliverables table.</li>
<li>Run the final checklist, fix issues and export every file in the right format.</li>
<li>Build the final presentation, save the whole project in one folder, and add it to your portfolio.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A brand identity project connects brief, logo, system and real applications.</li>
<li>Testing and checking are as important as designing.</li>
<li>Consistent colours, fonts and logo rules make pieces feel like one family.</li>
<li>A finished capstone is your best portfolio proof.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: You must email a CV to an employer. Which export is the best choice?
- GIF
* PDF Standard
- SVG
- MP4
= A PDF keeps layout and sharp text and opens on almost any device.

Q: Which format is best for a logo that must stay sharp on a huge signboard?
- JPG at 72 DPI
- GIF
* SVG or a vector PDF
- A screenshot
= Vector formats scale without losing quality.

Q: You are sending a poster to a printer and the printer asks for bleed. Which download option helps?
- JPG with compression
* PDF Print with crop marks and bleed, if available
- GIF
- MP4
= PDF Print supports bleed and crop marks for professional printing.

Q: A photo is easy to find in an image search. What does this tell you about its licence?
- It is free to use
- It is in the public domain
* Nothing; you must still check the licence
- It was uploaded by Canva
= Availability does not equal permission.

Q: A Creative Commons CC BY photo requires...
- Nothing at all
* Credit to the creator
- A payment of UGX 50,000
- Permission to remove the name
= Attribution is the main condition of that licence.

Q: Why keep an asset log?
- It makes the design heavier
* It records where every image, font and sound came from and its licence
- Printers ask for it
- It replaces a contract
= A log proves your use is legal and helps when a client asks.

Q: How many pieces make a good beginner portfolio?
- 50 mixed pieces
- One piece only
* About 6 to 10 strong pieces with short case studies
- Only screenshots without explanation
= Quality and clear explanation show skill better than quantity.

Q: A new client asks for the final files before paying anything. What is the safest professional step?
- Send everything and trust them
* Agree a deposit and deliver final files after payment, as set in a written agreement
- Work for free for exposure
- Delete the project
= Deposits and written terms protect both parties.

QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which of these is normally a paid feature in Canva, so you should not promise it to a client on the free plan without checking?
- Downloading a PNG
- Creating a custom size
* Transparent background PNG export
- Adding a text box
= Transparent PNG export and some other features need a paid plan, so check before promising.

Q: Which size fits a printed A4 flyer?
- 1080 x 1080 px
* 210 x 297 mm
- 1280 x 720 px
- 55 x 90 mm
= A4 paper is 210 x 297 mm.

Q: What is the best use of a template?
- Leave it exactly as it is for every client
* Use it for structure and then change colours, fonts and photos to fit the brand
- Avoid ever changing the text
- Use only crown elements
= Templates give structure; identity comes from your own choices.

Q: Which shortcut duplicates a selected element in Canva on Windows?
- Ctrl + Z
* Ctrl + D
- Ctrl + P
- Ctrl + S
= Ctrl + D duplicates the selected item (Cmd + D on a Mac).

Q: What is a guide in Canva?
- A tutorial video
* A line that helps you align and place items but does not show in the exported file
- A paid font
- A printed crop mark
= Guides help while editing and are not part of the final design.

Q: You see four icons with uneven gaps. What is the efficient fix?
- Nudge each icon by eye
* Select them and use the Position tool's space-evenly option
- Make the icons bigger
- Group them without aligning
= The tool gives exact, equal gaps quickly.

Q: Which design has the clearest visual hierarchy?
- All text 24 pt in the same colour
* A large headline, medium details and small contact text
- Five equally large headlines
- Many decorative fonts
= Hierarchy needs different levels of importance.

Q: What does the squint test reveal?
- The print size
* What stands out first in the design
- The font names
- The licence of images
= Blurring the view shows the real order of attention.

Q: Which pairing is the safest for a beginner?
- Two script fonts
- Three decorative fonts
* One bold sans-serif heading and one readable body font that contrast, or one family in different weights
- Five fonts for variety
= Contrast with restraint looks deliberate and readable.

Q: What line spacing is usually comfortable for body text?
- About 0.8
- 1.0 exactly in every case
* About 1.2 to 1.5
- 3.0
= Slightly open spacing helps eyes follow lines.

Q: What contrast ratio is generally recommended for normal text under accessibility guidelines?
- 1.5 to 1
- 2 to 1
* 4.5 to 1
- 20 to 1
= About 4.5 to 1 is the common minimum for normal-size text.

Q: A map shows shops in red (closed) and green (open) only. What should you add?
- Brighter colours
* Words or shapes to mark open and closed
- A drop shadow
- A script font
= Colour alone excludes people who cannot distinguish the colours.

Q: A photo for an A4 print looks pixelated. What is the most likely cause?
- The wrong font
* The photo does not have enough pixels for the print size
- The page margins
- A missing logo
= Low-resolution images blur when enlarged.

Q: Which is NOT usually part of a brand identity?
- Logo
- Colour palette
* A random new font in every post
- Tone of voice
= Brand identity depends on consistency.

Q: You do not have a full Brand Kit on the free plan. What helps keep your design consistent?
- Guessing colours each time
* A brand sheet with HEX codes and font names used for every design
- Using whichever template appears first
- Changing the logo regularly
= Written standards work on any plan.

Q: What is a good test for a logo?
- Make it as detailed as possible
* Check it at 48 px and in one colour
- Add gradients and shadows to everything
- Use five fonts
= Strong logos stay clear when small and without colour.

Q: What is bleed?
- Margin inside the page for text
* Extra background that extends beyond the trim edge to avoid white lines
- A printing error
- A type of font
= Bleed protects against small cutting differences.

Q: Where should text sit on a print flyer?
- Exactly on the edge
* At least 5 to 10 mm inside the trim edge
- Within the bleed
- Outside the page
= Safe margins stop text being cut off.

Q: Which is the best export for an A3 poster going to a printer?
- GIF
* PDF Print with bleed and crop marks if available
- A low-quality JPG
- MP4
= PDF Print is made for printers.

Q: Which CV choice is best for readability?
- A rating bar for each skill
* Clear headings and results-based bullet points
- Light grey body text
- Five fonts
= Clarity and evidence matter most.

Q: What should you include on a certificate?
- Only a logo
* Title, recipient, reason, date, signatures and ideally a number
- A random quote
- Only a colourful border
= These details make the certificate meaningful and verifiable.

Q: What is a suitable chart for monthly sales across a year?
- Pie chart
* Line chart
- Business card
- Flowchart
= A line chart shows change over time.

Q: Why should most bar charts start at zero?
- To use fewer colours
* To avoid exaggerating differences
- To reduce the file size
- Canva requires it
= Honest charts do not mislead.

Q: Why add on-screen text to a short video?
- To remove the need for music
* Because many viewers watch with sound off
- To hide mistakes
- Because videos cannot have audio
= Text keeps the message clear on mute.

Q: A client should see your design and give feedback but not edit it. Which permission?
- Can edit
* Can comment
- Owner
- Delete
= Comment access lets them respond without changing the file.

Q: A teammate wants to use your master poster for a new event. What should they do?
- Edit the master directly
* Make a copy and edit the copy
- Delete the master
- Rename it only
= Copies protect the original master.

Q: What is the best approach to an AI-generated image in a client design?
- Use it without checking
* Inspect for errors and stereotypes, edit it to fit the brand and follow current licence and privacy rules
- Hide that AI was used from everyone
- Upload client secrets as part of the prompt
= AI can speed up work but needs human judgement and responsibility.

Q: Which format scales a logo to any size without losing quality?
- JPG
- GIF
* SVG
- Screenshot
= SVG is a vector format.

Q: You find a photo through an image search. What must you do before using it commercially?
- Nothing, since it is visible
* Check its licence and get permission or use a licensed source
- Add a filter
- Credit Google
= Finding an image does not give you the right to use it.

Q: A freelance client asks for final files before paying a deposit. What is the best practice?
- Send everything immediately
* Use a written agreement with a deposit and deliver final files after payment
- Work for exposure
- Stop answering messages
= Clear terms protect both sides.
QUIZ,
];
