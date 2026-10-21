<?php
return [
    'slug'        => 'graphic-design-fundamentals',
    'title'       => 'Graphic Design Fundamentals',
    'category'    => 'design',
    'level'       => 'beginner',
    'icon'        => 'layers',
    'sort'        => 330,
    'summary'     => 'Learn the principles behind good design (contrast, hierarchy, colour, type and layout), then make logos, posters and social graphics with free tools and export them correctly.',
    'description' => '<p>Every shop sign, school poster, WhatsApp status and church programme is a piece of graphic design. Some are easy to read and make you want to act; others are crowded and confusing. The difference is rarely talent. It is a handful of principles that anyone can learn and practise.</p><p>This beginner course teaches those principles first: contrast, hierarchy, alignment, spacing, colour, typography and layout. Then you apply them to real jobs such as a logo, a brand kit, an event poster and a social media graphic. We use free tools such as Canva, Inkscape and GIMP, so you do not need an expensive computer or software. The last module covers exporting for screen and print, copyright and licensing, and how to build a portfolio.</p><p>No experience is needed. If you can use a computer or a phone and are curious, you can start today.</p>',
    'outcomes'    => [
        'Explain how contrast, hierarchy, alignment, proximity, repetition and white space make a design clear',
        'Choose a colour palette, and understand RGB, CMYK and readable colour combinations',
        'Choose and pair fonts, and set text that is easy to read',
        'Build balanced layouts using a grid and visual flow',
        'Tell raster images from vector graphics and pick the right free tool for each job',
        'Design a simple logo and a small brand kit',
        'Create posters, flyers and social media graphics for a real purpose',
        'Export files correctly for web and print, and use images, fonts and logos legally',
    ],
    'requirements' => ['No design experience needed', 'A computer, laptop or smartphone with internet access', 'A free Canva account (or another free design tool) for the practice activities'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Canva Design School', 'url' => 'https://www.canva.com/designschool/'],
        ['title' => 'Inkscape (free vector graphics editor)', 'url' => 'https://inkscape.org/'],
        ['title' => 'GIMP (free image editor)', 'url' => 'https://www.gimp.org/'],
        ['title' => 'Google Fonts', 'url' => 'https://fonts.google.com/'],
        ['title' => 'Creative Commons', 'url' => 'https://creativecommons.org/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: How design works',
            'summary' => 'See what graphic design is for and learn the core principles: contrast, hierarchy, alignment, proximity, repetition and space.',
            'lessons' => [
                [
                    'title'   => 'What graphic design is and how a design project flows',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what graphic design is for and follow a simple five-step process for any project. This matters because good design is not decoration; it helps a message reach the right person quickly, which is useful for a business, a charity or a school club.</p>

<h3>Design is communication</h3>
<p><strong>Graphic design</strong> is the skill of arranging words, pictures, colours and shapes so that a message is clear and attractive. A bus-stop sign tells you where to wait. A mobile money poster tells you the price and the phone number. A good design answers three questions in a few seconds: <em>What is this? Is it for me? What should I do next?</em></p>
<p>Art and design are related but different. An artist often expresses a personal idea. A designer solves a problem for someone else, so the first question is never "What looks nice?" but "What is this design supposed to achieve?"</p>

<h3>Where you see graphic design</h3>
<ul>
<li>Logos and shop signs, packaging and labels.</li>
<li>Posters, flyers, banners and billboards.</li>
<li>Social media posts, WhatsApp statuses and YouTube thumbnails.</li>
<li>Certificates, programmes, business cards and CVs.</li>
<li>Websites and mobile app screens.</li>
</ul>

<h3>A five-step design process</h3>
<ol>
<li><strong>Brief.</strong> Write down the goal, the audience and the practical facts. Example: "A poster for a free computer class for girls aged 14 to 18, on Saturday at 10 am at the community hall; call 0700 000 000 to register."</li>
<li><strong>Research and ideas.</strong> Look at similar designs and collect examples you like. Sketch three small, rough layouts on paper. Sketching is faster than clicking and lets you throw ideas away cheaply.</li>
<li><strong>Design.</strong> Choose the best sketch and build it in your tool, using the principles from this course.</li>
<li><strong>Feedback.</strong> Show it to two or three people from your audience. Ask what they notice first and what the design wants them to do. Do not argue; listen.</li>
<li><strong>Deliver.</strong> Fix the problems, export in the right format, and save your working file in case changes are needed.</li>
</ol>

<h3>Collect the content first</h3>
<p>Many designs fail because the text is missing or keeps changing. Before you open any software, gather the exact words, dates, prices, phone numbers, logos and photos, and check that the spelling of names is correct. A beautiful poster with the wrong date is a failed poster.</p>

<h3>Common mistakes</h3>
<ul>
<li>Starting in the software with no plan.</li>
<li>Putting everything you know on one page.</li>
<li>Designing for yourself rather than for the audience.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Choose a poster or flyer near you, or one on your phone.</li>
<li>Write what its goal is and who its audience is.</li>
<li>Decide what you noticed first, second and third.</li>
<li>Write one thing that works and one thing that is confusing.</li>
<li>Write a short brief (goal, audience, key facts) for a poster you would like to make for a school or community event.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Graphic design communicates a message clearly to a particular audience.</li>
<li>A design should answer: what is this, is it for me, what do I do next?</li>
<li>Follow brief, ideas, design, feedback, deliver.</li>
<li>Collect and check all the content before designing.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Contrast and visual hierarchy',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use contrast to make important things stand out and arrange information so the reader knows what to read first. This matters because people look at a poster or screen for only a moment; if everything shouts equally, nothing is heard.</p>

<h3>What is contrast?</h3>
<p><strong>Contrast</strong> means a clear difference between two elements. Contrast helps the eye notice something and also makes text readable. You can create contrast with:</p>
<table>
<thead><tr><th>Tool</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Size</td><td>A very large heading above small details</td></tr>
<tr><td>Colour</td><td>Dark text on a pale background, or one bright button on a calm page</td></tr>
<tr><td>Weight</td><td>Bold text next to light text</td></tr>
<tr><td>Shape</td><td>A round badge among rectangles</td></tr>
<tr><td>Space</td><td>One item with plenty of empty space around it</td></tr>
</tbody>
</table>
<p>The weakest design is the one with <em>almost</em> the same: two fonts that are nearly alike, or grey text on a slightly lighter grey background. Be bold. If things are different, make them clearly different.</p>

<h3>Contrast for readability</h3>
<p>Text must stand out from its background. Black on white, dark blue on cream and white on deep green are strong. Yellow on white, light grey on white and red on blue are weak and tiring to read. A quick test: take a photo of your design, turn the picture to black and white, or squint your eyes. If you cannot read the words, increase the contrast. On a busy photograph, add a plain coloured box or a darker overlay behind the text.</p>

<h3>Visual hierarchy</h3>
<p><strong>Hierarchy</strong> is the order of importance. It tells the reader where to look first, second and third. Most useful designs have three levels:</p>
<ol>
<li><strong>Level 1: the headline.</strong> The biggest or strongest item, such as "Free Computer Class for Girls".</li>
<li><strong>Level 2: the key details.</strong> The date, time, place, price. Medium size.</li>
<li><strong>Level 3: the extras.</strong> Small print, social media handles, a logo.</li>
</ol>
<p>Only one item should be the star. If you have two headlines of equal size, the eye does not know where to go.</p>

<h3>Test your hierarchy</h3>
<p>Show your design to a friend for three seconds, then take it away and ask what they remember. They should recall the headline and the main action. If they remember a decoration or a logo instead, rearrange the sizes and weights.</p>

<h3>Common mistakes</h3>
<ul>
<li>Making everything bold, large or in capital letters.</li>
<li>Using many colours to "add excitement", which removes focus.</li>
<li>Placing text on a photograph without checking contrast.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Open Canva or a similar free tool and make a square design.</li>
<li>Type three lines: an event name, a date and place, and a phone number.</li>
<li>Make the event name large and bold, the date and place medium, and the phone number small but clear.</li>
<li>Check that dark text sits on a light background (or the reverse).</li>
<li>Show a friend for three seconds and ask what they remember.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Contrast is a clear difference in size, colour, weight, shape or space.</li>
<li>Text must contrast strongly with its background.</li>
<li>Hierarchy sets the reading order: headline, key details, extras.</li>
<li>Give only one element the strongest emphasis.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Alignment, proximity, repetition and white space',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to make a design look neat and organised using alignment, proximity, repetition and white space. This matters because people judge quality in seconds: a tidy design feels trustworthy, while a messy one feels careless, even if the information is the same.</p>

<h3>Alignment</h3>
<p><strong>Alignment</strong> means lining things up along a shared edge or centre. Every element should have a visible connection to something else on the page. The common choices are:</p>
<ul>
<li><strong>Left aligned.</strong> The most readable for paragraphs, because the eye returns to the same starting line each time.</li>
<li><strong>Centred.</strong> Good for short, formal things such as invitations, certificates and titles. Long centred paragraphs are tiring to read.</li>
<li><strong>Right aligned.</strong> Use rarely, for small items such as a date in the corner.</li>
</ul>
<p>Pick one main alignment and keep to it. Mixing left, centre and right on the same page makes it look accidental. Use the guide lines that Canva and other tools show when you drag an object; they tell you when something is lined up.</p>

<h3>Proximity</h3>
<p><strong>Proximity</strong> means that things which belong together should be placed close together, and unrelated things further apart. Readers see grouped items as one idea. A poster might have three groups: the headline, the "when and where" details, and the "how to join" details. If the date is far from the place, people have to hunt for the connection. Use space, not lines, to make groups wherever possible.</p>

<h3>Repetition</h3>
<p><strong>Repetition</strong> means reusing the same style for the same kind of thing: the same colour for all headings, the same font size for all captions, the same shape for all buttons. Repetition makes a design feel consistent and builds recognition. In a set of posts for a business, repeating the logo position, colours and fonts means that people know the posts are yours before reading a word.</p>

<h3>White space</h3>
<p><strong>White space</strong> (also called negative space) is the empty area around and between elements. It does not have to be white. Beginners often fear empty areas and fill them with more pictures and text. But space gives the eye a place to rest and makes the important parts stand out. A crowded poster says "do not read me".</p>
<table>
<thead><tr><th>Problem</th><th>Fix</th></tr></thead>
<tbody>
<tr><td>Text touches the edge of the page</td><td>Leave a margin on every side, at least 5 percent of the width</td></tr>
<tr><td>Lines of text are squeezed together</td><td>Increase line spacing a little</td></tr>
<tr><td>Everything is the same distance apart</td><td>Put more space between groups than inside groups</td></tr>
<tr><td>Too many items</td><td>Remove the least important one</td></tr>
</tbody>
</table>

<h3>Try it yourself</h3>
<ol>
<li>Make a design with a heading, three lines of details, a button shape with "Join us", and a logo or small picture.</li>
<li>Use the same left edge for all text.</li>
<li>Move related lines closer together and unrelated groups further apart.</li>
<li>Remove one element and add margin all round.</li>
<li>Compare with your first version and note what looks calmer.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Align elements to shared edges; choose one main alignment.</li>
<li>Group related items close together using proximity.</li>
<li>Repeat colours, fonts and shapes for a consistent look.</li>
<li>White space is a tool; do not fill every gap.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which best describes the main purpose of graphic design?
- To make a page as colourful as possible
* To communicate a message clearly to a particular audience
- To show off the designer's favourite effects
- To use as many fonts as possible
= Design solves a communication problem for the audience.

Q: What should you collect before opening the design software?
- Only a logo
* The exact text, dates, contacts, logos and photos, checked for mistakes
- Nothing; you can add the details later
- Many different fonts
= Missing or changing content causes errors, such as a wrong date on a printed poster.

Q: Which of these gives the strongest text contrast?
- Light grey text on a white background
- Yellow text on a white background
* Dark blue text on a cream background
- Red text on a blue background
= Dark on light (or light on dark) is readable; similar brightness is tiring and hard to read.

Q: What is visual hierarchy?
- Using the most expensive fonts
* The order of importance that tells the reader what to look at first, second and third
- Putting everything in the centre
- Using only one colour
= Hierarchy guides the eye through the design.

Q: A poster has two headlines of exactly the same size and weight. What is the likely problem?
- Too much white space
* The eye does not know where to start because nothing is clearly the main point
- The text is aligned left
- The poster is repeating a style
= Only one item should have the strongest emphasis.

Q: Which are examples of proximity working well? (choose all that apply)
* The date and place are placed close together
* Contact details form a group separate from the headline
- Every item is spaced the same distance apart
- The phone number is at the opposite corner from "call us"
= Related items sit together; unrelated items have more space between them.

Q: Why is white space useful?
- It wastes paper and should be removed
- It makes text unreadable
* It gives the eye rest and makes important elements stand out
- It is only needed on websites
= Empty space helps focus and makes designs feel calm and professional.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Colour and typography',
            'summary' => 'Choose colours that work together, understand RGB and CMYK, and set type that is easy to read.',
            'lessons' => [
                [
                    'title'   => 'Colour basics: the wheel, palettes and meaning',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a simple colour palette that looks good together and suits your message. This matters because colour is the first thing people notice, and a clear palette makes even simple designs look professional.</p>

<h3>The colour wheel</h3>
<p>The colour wheel arranges colours in a circle. The three <strong>primary colours</strong> in traditional art are red, yellow and blue. Mixing two gives the <strong>secondary colours</strong>: orange, green and purple. Colours directly opposite each other on the wheel are <strong>complementary</strong>, such as blue and orange. Together they create strong contrast.</p>

<h3>Three words to describe any colour</h3>
<table>
<thead><tr><th>Word</th><th>Meaning</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Hue</td><td>The basic colour family</td><td>Blue, green, red</td></tr>
<tr><td>Saturation</td><td>How strong or dull the colour is</td><td>Bright blue versus grey-blue</td></tr>
<tr><td>Brightness (or lightness)</td><td>How light or dark it is</td><td>Navy versus sky blue</td></tr>
</tbody>
</table>
<p>You can make a harmonious palette from one hue by changing only saturation and brightness.</p>

<h3>Simple palette recipes</h3>
<ul>
<li><strong>Monochromatic:</strong> one hue in light, medium and dark versions. Calm and safe.</li>
<li><strong>Analogous:</strong> colours next to each other, such as yellow, orange and red. Warm and friendly.</li>
<li><strong>Complementary:</strong> two opposites, such as blue and orange. Energetic; use one as the main colour and the other as a small accent.</li>
<li><strong>Neutral plus accent:</strong> white, grey or black with one bright colour. Very popular and easy to use.</li>
</ul>

<h3>The 60-30-10 rule</h3>
<p>A reliable way to balance a palette: use about <strong>60 percent</strong> of a main colour (often a neutral background), <strong>30 percent</strong> of a second colour, and <strong>10 percent</strong> of an accent for buttons and key words. It stops designs from becoming a rainbow.</p>

<h3>What colours can suggest</h3>
<p>Colours carry feelings, but meanings differ between cultures and situations, so use these as hints and not as rules.</p>
<table>
<thead><tr><th>Colour</th><th>Often suggests</th></tr></thead>
<tbody>
<tr><td>Blue</td><td>Trust, calm, technology</td></tr>
<tr><td>Green</td><td>Nature, growth, health, money</td></tr>
<tr><td>Red</td><td>Energy, urgency, danger, love</td></tr>
<tr><td>Yellow</td><td>Optimism, warmth, caution</td></tr>
<tr><td>Black</td><td>Power, elegance, seriousness</td></tr>
</tbody>
</table>
<p>Think about your audience: what colours does the community associate with its school, church, football team or party? Avoid accidental messages such as using the colours of a political party on a neutral community notice.</p>

<h3>Where to find palettes</h3>
<p>Take a colour from a photograph in your design; most tools have an eyedropper that picks it. Free palette tools, including Canva's colour palette generator, can suggest matching colours. Pick no more than three or four colours for a project and write down their codes.</p>

<h3>Try it yourself</h3>
<ol>
<li>Choose a pretend small business, for example a bakery or a phone-repair shop.</li>
<li>Pick a main colour that suits it and decide on a neutral background colour.</li>
<li>Add one accent colour from the opposite side of the wheel.</li>
<li>Make a small design that uses them in roughly 60-30-10 proportions.</li>
<li>Write down the three colour codes next to your design.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Hue, saturation and brightness describe every colour.</li>
<li>Monochromatic, analogous, complementary and neutral-plus-accent palettes are easy to use.</li>
<li>The 60-30-10 rule keeps colour balanced.</li>
<li>Colour meanings vary; think about your audience and keep to three or four colours.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Screen and print colour: RGB, CMYK and accessibility',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose the correct colour mode for screen or print and make sure your colours can be read by everyone. This matters because a colour that glows on your phone can look dull on paper, and because many people have difficulty seeing certain colour combinations.</p>

<h3>RGB: colour made of light</h3>
<p>Screens such as phones, computers and TVs make colour by mixing <strong>R</strong>ed, <strong>G</strong>reen and <strong>B</strong>lue light. When all three are at full strength you see white; with none you see black. Designs for websites, social media, presentations and videos use RGB. Colours are often written as a <strong>hex code</strong>, a hash sign followed by six characters, such as <code>#1F6F43</code> for a deep green. Your design tool shows the hex code of any colour, so you can reuse it exactly.</p>

<h3>CMYK: colour made of ink</h3>
<p>Printers mix <strong>C</strong>yan, <strong>M</strong>agenta, <strong>Y</strong>ellow and black (called <strong>K</strong>ey) ink on paper. Printed colours cannot be as bright as screen colours because ink absorbs light, while screens give out light. Very bright greens, blues and oranges often look duller when printed. For professional printing, many print shops prefer files prepared for CMYK, but always ask what your print shop wants. Small local shops that print from a normal PDF or JPG usually convert colours themselves.</p>
<table>
<thead><tr><th></th><th>RGB</th><th>CMYK</th></tr></thead>
<tbody>
<tr><td>Made by</td><td>Mixing light</td><td>Mixing ink</td></tr>
<tr><td>Used for</td><td>Screens, web, social media</td><td>Printing on paper</td></tr>
<tr><td>Brightness</td><td>Very vivid</td><td>Slightly duller</td></tr>
<tr><td>Basic mix</td><td>Red, green, blue</td><td>Cyan, magenta, yellow, black</td></tr>
</tbody>
</table>

<h3>Check before printing</h3>
<ol>
<li>Print one test page on the same kind of printer and paper if possible.</li>
<li>Compare it with the screen, remembering that screens are brighter.</li>
<li>Adjust colours that look too dull, then print the final copies.</li>
</ol>
<p>Cheap paper soaks up ink and makes colours darker and softer. Plan for this when you choose text colours.</p>

<h3>Designing for everyone</h3>
<p>Around one in twelve men and a smaller number of women have some form of colour vision difference, commonly difficulty with red and green. Others use phones in bright sunshine or have poor eyesight. Follow these rules:</p>
<ul>
<li>Never use colour alone to carry meaning. Add a word, an icon or a pattern, for example "Closed" written on a red sign.</li>
<li>Keep strong contrast between text and background. Web accessibility guidelines recommend a contrast ratio of at least 4.5 to 1 for normal text, and free contrast checker tools can measure it.</li>
<li>Avoid red text on green, and light text on yellow.</li>
<li>Test by viewing your design in greyscale; if it still works, it is accessible.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Pick a colour in your design tool and find its hex code.</li>
<li>Write down the hex codes of your palette from the last lesson.</li>
<li>Make a small design with dark text on a coloured background and check it in a free contrast checker.</li>
<li>Take a screenshot, switch it to black and white in your phone's photo editor, and see whether the text is still clear.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>RGB (light) is for screens; CMYK (ink) is for print.</li>
<li>Printed colours look duller than screen colours, so test print.</li>
<li>Hex codes let you reuse colours exactly.</li>
<li>Do not rely on colour alone; keep strong contrast for readability.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Typography: choosing and pairing fonts',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose fonts that suit a message and combine two of them without a clash. This matters because most designs are mainly words, and the font changes how those words feel before anyone reads them.</p>

<h3>Font families and types</h3>
<p><strong>Typography</strong> is the art of arranging type. A <strong>typeface</strong> (or font family) such as Arial has several <strong>styles</strong>: regular, italic, bold and so on. The main groups are:</p>
<table>
<thead><tr><th>Group</th><th>Looks like</th><th>Feels</th><th>Good for</th></tr></thead>
<tbody>
<tr><td>Serif</td><td>Small strokes on the ends of letters, like Times New Roman</td><td>Traditional, serious, bookish</td><td>Printed text, headings with character</td></tr>
<tr><td>Sans serif</td><td>Clean letters with no strokes, like Arial or Calibri</td><td>Modern, simple, friendly</td><td>Screens, signs, small text</td></tr>
<tr><td>Script</td><td>Handwriting style</td><td>Personal, elegant</td><td>Invitations, a single word only</td></tr>
<tr><td>Display</td><td>Decorative and bold</td><td>Fun, loud</td><td>One short headline</td></tr>
</tbody>
</table>

<h3>Pairing fonts</h3>
<p>You almost never need more than <strong>two</strong> typefaces in one design. Three or more look messy. Reliable ways to pair:</p>
<ul>
<li><strong>Serif plus sans serif.</strong> A serif headline with a sans serif body is a classic match.</li>
<li><strong>One family in different weights.</strong> Bold for the heading, regular for the text. This is safest for beginners.</li>
<li><strong>A decorative font for the title only,</strong> with a plain font for everything else.</li>
</ul>
<p>Avoid pairing two fonts that are <em>similar but not the same</em>, for example two different rounded sans serifs; they look like a mistake. Make the difference clear or use one family.</p>

<h3>Free fonts</h3>
<p>Google Fonts offers a large library of free fonts that can be used in personal and commercial projects under open licences. Canva includes many fonts, and some are only for paid users. Do not download "free" fonts from random websites; they may have unclear licences or hide harmful files. Read the licence for any font used in a logo or a product for sale.</p>

<h3>Choose by purpose</h3>
<ul>
<li>A bank or school notice: a calm serif or neat sans serif.</li>
<li>A children's party poster: a rounded, playful display font for the title.</li>
<li>A road safety sign: a strong, plain sans serif in capital letters.</li>
</ul>
<p>Use capital letters sparingly. A few words in capitals work for headings, but whole paragraphs in capitals are hard to read because the word shapes disappear.</p>

<h3>Try it yourself</h3>
<ol>
<li>Write the title "Community Clean-Up Day" three times in three different font families and compare how each feels.</li>
<li>Pick the one that best matches a friendly community event.</li>
<li>Pair it with a plain sans serif for the date and place.</li>
<li>Explore Google Fonts, filter by style, and note two fonts you would like to try.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Serif, sans serif, script and display fonts have different moods.</li>
<li>Use no more than two typefaces; a single family with several weights is safest.</li>
<li>Choose fonts from trusted sources and check their licences.</li>
<li>Avoid long text in capitals or script.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Setting readable text',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to set text that is comfortable to read by controlling size, line spacing, line length and alignment. This matters because a lovely layout is useless if the words are too small, too tight or too long to follow, especially on a phone screen or from a distance.</p>

<h3>Size</h3>
<p>Size depends on how far away the reader is. On a phone, body text is usually 16 pixels or more. On a printed A4 page, 10 to 12 points is common for reading text. On a poster read from two or three metres, the headline may need to be 60 points or larger and the details at least 24 points. A useful test is to look at your design from the real viewing distance, or shrink it to the size of a phone screen, and see if you can still read the key details.</p>

<h3>Line spacing</h3>
<p><strong>Line spacing</strong> (also called leading) is the space between lines. Lines that are too close are hard to follow, and lines too far apart look broken. For paragraphs, set it to around 1.4 to 1.6 times the text size. In Canva this is the <strong>Spacing</strong> button on the text toolbar. For large headlines, tighter spacing of about 1.0 to 1.2 often looks better.</p>

<h3>Line length</h3>
<p>Very long lines make the eye lose its place, and very short lines make reading choppy. A good length for paragraphs is about 45 to 75 characters per line, roughly 8 to 12 words. If your lines are longer, make the text box narrower or use two columns.</p>

<h3>Letter spacing</h3>
<p><strong>Letter spacing</strong> (tracking) is the gap between letters. Capital letters in small headings often look better with slightly increased spacing. Never squeeze letters so tightly that they touch. Do not stretch or squash a font by dragging its corner handles unevenly; it distorts the letter shapes. Change the size instead.</p>

<h3>Alignment and rag</h3>
<p>Left-align paragraphs. Avoid <strong>justified</strong> text (even edges on both sides) in narrow columns, because it creates uneven gaps between words. Watch the ragged right edge; if one line is very short or one word sits alone on the last line, rewrite or resize to fix it.</p>

<h3>A quick checklist for text</h3>
<table>
<thead><tr><th>Check</th><th>Aim</th></tr></thead>
<tbody>
<tr><td>Size</td><td>Readable from the real viewing distance</td></tr>
<tr><td>Contrast</td><td>Strong against the background</td></tr>
<tr><td>Line spacing</td><td>About 1.4 to 1.6 for paragraphs</td></tr>
<tr><td>Line length</td><td>45 to 75 characters</td></tr>
<tr><td>Fonts</td><td>No more than two typefaces</td></tr>
<tr><td>Words</td><td>Short and spelled correctly; read them aloud</td></tr>
</tbody>
</table>

<h3>Try it yourself</h3>
<ol>
<li>Write a paragraph of about 40 words for a school event notice.</li>
<li>Set it first with tight line spacing and a very wide text box, and look at it.</li>
<li>Now use line spacing near 1.5 and narrow the box until it has about 10 words per line.</li>
<li>Compare the two versions and notice which is easier to read.</li>
<li>View the design at the size of a phone screen to check the size.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Choose text size for the real viewing distance.</li>
<li>Paragraphs need roomy line spacing and a comfortable line length.</li>
<li>Do not stretch fonts or squeeze letters together.</li>
<li>Left-align most text and proofread every word.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which colour pair is complementary (opposite on the colour wheel)?
- Blue and green
- Red and orange
* Blue and orange
- Yellow and orange
= Complementary colours sit opposite each other and create strong contrast.

Q: What does the 60-30-10 rule suggest?
- Use ten different colours
* Use about 60 percent a main colour, 30 percent a second colour and 10 percent an accent
- Make text 60 points, headings 30 and notes 10
- Print 60 copies
= It keeps a palette balanced, with the accent used sparingly.

Q: Which colour mode should you normally use for a social media graphic?
* RGB
- CMYK
- Greyscale only
- Pantone only
= Screens create colour with red, green and blue light.

Q: Why can printed colours look duller than on screen?
- Printers are always broken
* Ink absorbs light, while screens give out light, so very bright colours cannot be matched
- Paper is always yellow
- CMYK has too many colours
= The CMYK print range is smaller than the RGB screen range, so a test print is wise.

Q: Which are good practices for accessible colour use? (choose all that apply)
* Do not use colour alone to give meaning
* Keep strong contrast between text and background
- Use red text on a green background
- Use light yellow text on white
= People with colour vision differences, or using a phone in sun, need contrast and extra cues.

Q: How many typefaces should a beginner usually use in one design?
- As many as possible
- Five
- Four
* One or two
= More than two usually looks messy.

Q: Which settings help paragraph text be readable?
- Very tight line spacing and long lines
* Line spacing around 1.4 to 1.6 and lines of roughly 45 to 75 characters
- All capital letters
- Stretching the font to fit the box
= Comfortable spacing and line length help the eye follow the text.

Q: Where should you get fonts for a logo or a product you will sell?
- Any website that says free
* A trusted source such as Google Fonts, after reading the licence
- Copy them from a friend's computer
- Use fonts from a competitor's brand without asking
= The licence decides what you may do with a font.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Layout, tools and images',
            'summary' => 'Arrange elements with grids and balance, meet the free design tools, and handle images properly.',
            'lessons' => [
                [
                    'title'   => 'Layout: grids, balance and visual flow',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to plan a layout using a grid, balance the elements on a page, and guide the reader's eye from start to finish. This matters because a clear layout is the skeleton of every poster, flyer, slide and web page; with a good skeleton, even simple content looks organised.</p>

<h3>Start with the format</h3>
<p>Choose the page shape for where the design will be used. Common shapes are a square (1080 by 1080 pixels) or a portrait rectangle (1080 by 1350 pixels) for social feeds, a tall story shape (1080 by 1920 pixels) for statuses and stories, a wide rectangle (1920 by 1080 pixels) for slides and video thumbnails, and A4 or A3 paper for printed posters. Check the exact size recommended by the platform before you start, because platforms change.</p>

<h3>Using a grid</h3>
<p>A <strong>grid</strong> is an invisible set of columns and rows that you line your elements up to. It gives your design order without effort.</p>
<ul>
<li><strong>Margins:</strong> the empty border around the page. Keep text and logos inside it.</li>
<li><strong>Columns:</strong> vertical strips. A poster may use one wide column; a flyer may use two or three.</li>
<li><strong>Rule of thirds:</strong> divide the page into three equal parts across and down. Placing the main subject near where the lines cross often looks more natural than putting it dead centre.</li>
</ul>
<p>In Canva, turn on rulers and guides from the <strong>File</strong> menu (View settings) and drag guides from the ruler to mark your margins. Many tools also have a grid view.</p>

<h3>Balance</h3>
<p><strong>Balance</strong> means that the visual weight of the page feels steady and not tipped to one side. Large, dark, bright and busy things look heavy; small, light and plain things look light.</p>
<table>
<thead><tr><th>Type</th><th>How it works</th><th>Feeling</th></tr></thead>
<tbody>
<tr><td>Symmetrical</td><td>Both sides mirror each other</td><td>Formal, stable (certificates, invitations)</td></tr>
<tr><td>Asymmetrical</td><td>Different items but equal weight, such as a large photo balanced by a block of text</td><td>Modern, dynamic (posters, social posts)</td></tr>
</tbody>
</table>

<h3>Visual flow</h3>
<p>People in Uganda read from left to right and top to bottom, so the eye often follows a Z shape across a page or an F shape down a text-heavy page. Put the headline at the start of that path, the key details in the middle and the action ("Call now", "Register at the hall") at the end. Faces and arrows also direct attention; a person in a photo looking toward your text leads the reader there.</p>

<h3>Check your layout</h3>
<ol>
<li>Squint at the design so that details blur. You should see clear shapes: where is the heaviest part?</li>
<li>Check the margins on all four sides.</li>
<li>Trace the path your eye takes. Does it end at the action?</li>
</ol>

<h3>Try it yourself</h3>
<ol>
<li>Create a portrait design (for example 1080 by 1350 pixels).</li>
<li>Add guides for a margin on all sides and put a headline, a photo or shape, and a short block of text inside them.</li>
<li>Place the photo using the rule of thirds rather than the exact centre.</li>
<li>Balance it with the text block and a contact line at the bottom.</li>
<li>Squint at it and note whether it feels steady.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Choose the right size and shape for where the design will be shown.</li>
<li>Margins, columns and the rule of thirds give structure.</li>
<li>Balance can be symmetrical or asymmetrical; heavy items need counterweights.</li>
<li>Plan the eye's path so it ends at the call to action.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Free design tools: Canva, Inkscape and GIMP',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose the right free tool for a design job and find your way around the basics. This matters because you do not need expensive software to produce professional work; you need to understand what each tool is good at.</p>

<h3>Three free tools and their jobs</h3>
<table>
<thead><tr><th>Tool</th><th>Type</th><th>Best for</th><th>Notes</th></tr></thead>
<tbody>
<tr><td>Canva</td><td>Online design app with templates</td><td>Posters, social posts, presentations, simple logos</td><td>Works in a browser or phone app; needs internet; a free plan exists and some items need a paid plan</td></tr>
<tr><td>Inkscape</td><td>Vector graphics editor</td><td>Logos, icons, illustrations, anything that must scale</td><td>Free and open source for Windows, Mac and Linux</td></tr>
<tr><td>GIMP</td><td>Raster image editor</td><td>Photo editing, retouching, cutting out and combining pictures</td><td>Free and open source for Windows, Mac and Linux</td></tr>
</tbody>
</table>
<p>There are other free editors that run in a web browser and look similar to professional photo software, and free phone apps for quick edits. The principles in this course apply to all of them; pick one that runs well on your device.</p>

<h3>Canva basics</h3>
<ol>
<li>Sign in and choose <strong>Create a design</strong>, then a size such as <strong>Instagram Post</strong>, <strong>Poster</strong> or <strong>A4</strong> (names may change over time), or <strong>Custom size</strong> to type your own.</li>
<li>Open <strong>Elements</strong> for shapes and lines, <strong>Text</strong> to add text, and <strong>Uploads</strong> for your own pictures.</li>
<li>Click an object to move it, drag the corner handles to resize it while keeping proportions, and use the <strong>Position</strong> menu to align objects or send them forward and back.</li>
<li>Press <strong>Ctrl + Z</strong> to undo and <strong>Ctrl + D</strong> to duplicate a selected object.</li>
</ol>
<p>Templates are a starting point, not a finished design. Change the text, colours and photos so the result looks like yours and not like everyone else's.</p>

<h3>Inkscape in a few lines</h3>
<p>Inkscape draws with <strong>paths</strong> (mathematical lines), so shapes stay sharp at any size. Use the rectangle and ellipse tools to make shapes, the text tool to type, and the <strong>Fill and Stroke</strong> panel to colour them. Press <strong>Ctrl + Shift + F</strong> to open that panel. To save for the web, use <strong>File</strong> then <strong>Export</strong> and choose PNG; to keep editing, save as an Inkscape SVG.</p>

<h3>GIMP in a few lines</h3>
<p>GIMP edits pictures made of pixels. Open a photo with <strong>File</strong>, <strong>Open</strong>. Use <strong>Image</strong>, <strong>Scale Image</strong> to resize, <strong>Image</strong>, <strong>Crop to Selection</strong> to crop, and <strong>Colors</strong>, <strong>Brightness-Contrast</strong> to improve a dull photo. Use <strong>File</strong>, <strong>Export As</strong> to make a JPG or PNG; saving with <strong>File</strong>, <strong>Save</strong> makes a GIMP project file (XCF).</p>

<h3>Choosing quickly</h3>
<ul>
<li>Need a poster by tonight, using templates? Canva.</li>
<li>Need a logo that can go on a billboard and a business card? Inkscape.</li>
<li>Need to fix or combine photographs? GIMP.</li>
</ul>
<p>Whatever you choose, keep your working files safe, for example in a folder on your computer and a copy on a flash disk or cloud drive.</p>

<h3>Try it yourself</h3>
<ol>
<li>Open Canva and create a custom design of 1080 by 1080 pixels.</li>
<li>Add a shape, a heading and a smaller line of text.</li>
<li>Use the Position menu to align the text to the left edge of your shape.</li>
<li>Duplicate the shape, change its colour, and undo the change.</li>
<li>Download the design as a PNG, then open it from your folder.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Canva is quick and template based; Inkscape is for vectors; GIMP is for photos.</li>
<li>Keep an editable working file as well as the exported file.</li>
<li>Templates must be changed to make a design your own.</li>
<li>Pick the tool by the job, not by habit.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Working with images: raster, vector and resolution',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to tell raster images from vector graphics, understand resolution, and avoid blurry pictures. This matters because a photograph that looks fine on your phone can look terrible on a printed poster, and a logo that cannot scale will cost you time and money later.</p>

<h3>Raster images</h3>
<p>A <strong>raster</strong> image is made of tiny coloured squares called <strong>pixels</strong>. Photographs and screenshots are raster. Common file types are <strong>JPG</strong> (small file, good for photos), <strong>PNG</strong> (supports transparent backgrounds, good for logos on screen and screenshots) and <strong>WebP</strong> (a modern web format). Enlarging a raster image forces the software to invent new pixels, so it becomes blurry or blocky.</p>

<h3>Vector graphics</h3>
<p>A <strong>vector</strong> graphic is built from mathematical shapes and lines. It can be scaled to any size, from a stamp to a billboard, without losing sharpness. Logos, icons and simple illustrations are best made as vectors. Common file types are <strong>SVG</strong> and <strong>PDF</strong>. Free tools such as Inkscape make vectors.</p>
<table>
<thead><tr><th></th><th>Raster</th><th>Vector</th></tr></thead>
<tbody>
<tr><td>Made of</td><td>Pixels</td><td>Lines and shapes defined by maths</td></tr>
<tr><td>Scaling</td><td>Gets blurry when enlarged</td><td>Stays sharp at any size</td></tr>
<tr><td>Best for</td><td>Photographs, realistic images</td><td>Logos, icons, diagrams</td></tr>
<tr><td>Typical files</td><td>JPG, PNG, WebP</td><td>SVG, PDF</td></tr>
</tbody>
</table>

<h3>Resolution and size</h3>
<p>Resolution describes how many pixels an image has. For screens, what matters is the pixel size. A 1080 by 1080 pixel image fills a social post well. For print, what matters is pixels per inch (<strong>PPI</strong>, often called DPI). A common target for printed material is about 300 pixels per inch at the final printed size.</p>
<p>Here is how to check. A4 paper is about 8.3 by 11.7 inches. At 300 pixels per inch that needs around 2480 by 3508 pixels. A small image of 800 by 600 pixels downloaded from WhatsApp will look soft on an A4 poster. Always start with the largest original image available, and avoid pictures that have been forwarded many times, because each forward compresses them further.</p>

<h3>Cropping, not stretching</h3>
<ul>
<li>Crop to remove unwanted areas and improve the composition.</li>
<li>Never pull a picture's side handle to fit a space; faces will look squashed or stretched. Resize using the corner handles, which keep the proportions.</li>
<li>Keep the main subject away from the edges that may be cut during printing.</li>
</ul>

<h3>Transparent backgrounds</h3>
<p>A PNG logo with a transparent background can be placed over a coloured background without a white box around it. A JPG cannot be transparent. If a logo comes with a white box, ask for a transparent version or remove the background in an editor.</p>

<h3>Try it yourself</h3>
<ol>
<li>Take a photograph with your phone and find another small, forwarded copy of a picture.</li>
<li>Place both into the same 1080 by 1080 design and enlarge them to fill the page.</li>
<li>Compare how sharp they are at full size.</li>
<li>Crop one photo to make the subject sit on a rule-of-thirds line.</li>
<li>Write down which of your pictures could be printed on A4 and which could not.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Raster images use pixels and blur when enlarged; vector graphics scale without loss.</li>
<li>Use JPG for photos, PNG for transparency, and SVG or PDF for vector logos.</li>
<li>Aim for about 300 PPI at the final printed size for print work.</li>
<li>Crop instead of stretching, and start with original, high-quality images.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is a grid in layout design?
- A type of font
* An invisible set of columns, rows and margins used to line up elements
- A colour mode
- A kind of file format
= A grid gives structure so the design feels organised.

Q: Which layout is asymmetrical but balanced?
- Two identical boxes on each side of the page
* A large photo on one side balanced by a block of text and a small logo on the other
- Everything piled in one corner
- A completely empty page
= Asymmetrical balance uses different items with similar visual weight.

Q: Which tool is the best choice for creating a logo that must be printed at any size?
- A tool that only makes JPG files
* A vector editor such as Inkscape
- A phone camera
- A spreadsheet
= Vector artwork stays sharp when scaled.

Q: Which free tool is mainly designed for editing and retouching photographs?
- Inkscape
* GIMP
- Google Fonts
- Creative Commons
= GIMP is a raster image editor.

Q: What happens when you enlarge a small raster image a lot?
- It gets sharper
* It becomes blurry or blocky because pixels are stretched
- It turns into a vector
- It becomes CMYK
= Raster images have a fixed number of pixels.

Q: Which file type supports a transparent background? (choose all that apply)
* PNG
* SVG
- JPG
- Printed paper
= JPG cannot store transparency; PNG and SVG can.

Q: Approximately how many pixels wide is needed to print A4 portrait (about 8.3 inches wide) at 300 pixels per inch?
- 800
- 1080
* About 2480
- 300
= 8.3 inches multiplied by 300 gives about 2480 pixels.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Logos, brand and everyday projects',
            'summary' => 'Design a simple logo, build a small brand kit, and make posters and social media graphics that work.',
            'lessons' => [
                [
                    'title'   => 'Logo basics: designing a simple, strong mark',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what makes a good logo and sketch and build a simple one. This matters because a logo is the face of a business, school or group, and a good one is remembered, works everywhere and costs nothing to reuse.</p>

<h3>What a logo is</h3>
<p>A <strong>logo</strong> is a symbol, a word or a combination that identifies an organisation. It is not the whole brand. The brand is the total impression people have of you. The logo is the signature on it.</p>

<h3>Types of logo</h3>
<table>
<thead><tr><th>Type</th><th>Description</th><th>Example idea</th></tr></thead>
<tbody>
<tr><td>Wordmark</td><td>The name written in a styled font</td><td>A bakery name in a warm, rounded font</td></tr>
<tr><td>Symbol (icon)</td><td>A picture or shape alone</td><td>A simple bird shape</td></tr>
<tr><td>Combination</td><td>Symbol with name</td><td>A leaf icon beside the name of a farm shop</td></tr>
<tr><td>Lettermark</td><td>Initials only</td><td>Two letters inside a circle</td></tr>
</tbody>
</table>

<h3>Qualities of a good logo</h3>
<ul>
<li><strong>Simple.</strong> People should be able to draw it from memory. Avoid tiny details.</li>
<li><strong>Memorable.</strong> One clear idea beats five ideas combined.</li>
<li><strong>Suitable.</strong> It fits the business, the audience and the culture. A funeral service and a toy shop need different logos.</li>
<li><strong>Flexible.</strong> It works small (a phone profile picture) and large (a signboard), in colour and in black and white.</li>
<li><strong>Original.</strong> It does not copy another logo. Never trace or imitate existing brands.</li>
</ul>

<h3>Design steps</h3>
<ol>
<li><strong>Write the brief.</strong> What does the organisation do, who is the audience, and what feeling should it give (trustworthy, friendly, strong)? Write three keywords.</li>
<li><strong>Sketch.</strong> Draw at least twenty tiny thumbnails on paper. The first ten are obvious; the later ones are often better.</li>
<li><strong>Choose two or three</strong> and redraw them cleanly.</li>
<li><strong>Build the best one</strong> as a vector in Inkscape, or with simple shapes and a font in Canva. Use no more than two colours at first.</li>
<li><strong>Test it.</strong> Shrink it to the size of a thumbnail, turn it black and white, put it on a dark background, and show it to others.</li>
<li><strong>Save versions:</strong> full colour, black, white and a version with a transparent background.</li>
</ol>

<h3>Mistakes to avoid</h3>
<ul>
<li>Using stock clip art or a template mark that thousands of other people use; it cannot be protected, and may break licence terms.</li>
<li>Too many fonts, effects, shadows or gradients.</li>
<li>A logo that only works in colour.</li>
<li>Choosing a fashionable trick that will look old in two years.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Choose a real or imaginary local business, such as a tailor, a poultry farm or a youth football club.</li>
<li>Write three keywords describing the feeling it should give.</li>
<li>Sketch twenty small logo ideas on paper in ten minutes.</li>
<li>Pick the best and build it in Canva or Inkscape using simple shapes and one font.</li>
<li>Shrink it to the size of your thumb on the screen and check it in black and white.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A logo identifies an organisation; the brand is the whole impression.</li>
<li>Simple, memorable, suitable, flexible and original are the five tests.</li>
<li>Sketch many ideas on paper before touching the computer.</li>
<li>Keep colour, black, white and transparent versions.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Brand identity: colours, fonts and a brand kit',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to create a one-page brand kit that keeps all your designs consistent. This matters because customers recognise and trust organisations that look and sound the same everywhere: on the signboard, the invoice, the WhatsApp status and the t-shirt.</p>

<h3>What brand identity means</h3>
<p><strong>Brand identity</strong> is the set of visible and written choices that make an organisation recognisable: logo, colours, fonts, picture style and the way it speaks. A <strong>brand kit</strong> (or style guide) is a short document that records these choices so that everyone, including a volunteer who joins next year, follows the same rules.</p>

<h3>What goes into a simple brand kit</h3>
<table>
<thead><tr><th>Part</th><th>What to record</th></tr></thead>
<tbody>
<tr><td>Logo</td><td>All versions (colour, black, white, transparent) and the minimum size</td></tr>
<tr><td>Clear space</td><td>The empty area to keep around the logo, for example the height of one letter</td></tr>
<tr><td>Colours</td><td>One main colour, one or two support colours, neutrals, with hex codes (and CMYK values if you print)</td></tr>
<tr><td>Fonts</td><td>One font for headings, one for text, with the weights to use</td></tr>
<tr><td>Pictures</td><td>The style of photos, for example real local people, natural light, no heavy filters</td></tr>
<tr><td>Voice</td><td>How you write: friendly, simple, respectful; a few example sentences</td></tr>
<tr><td>Do and do not</td><td>Examples: do not stretch the logo, do not put it on a busy photo, do not change the colours</td></tr>
</tbody>
</table>

<h3>Build a brand kit in four steps</h3>
<ol>
<li>Make a new design (for example A4 landscape) in Canva or another tool.</li>
<li>Place your logo, then add small squares of each brand colour with the hex code written under each.</li>
<li>Write your two fonts in their real styles with a sample line, such as the name of your organisation.</li>
<li>Add three sentences about your voice, and two "do not" rules. Export it as a PDF and keep it in a shared folder.</li>
</ol>
<p>Canva's paid plans include a feature called Brand Kit that stores colours, fonts and logos for quick reuse. On the free plan you can simply copy and paste elements from your own kit page or reuse a design you saved as a template.</p>

<h3>Consistency in action</h3>
<ul>
<li>Use the same two fonts and the same colour for headings on every poster.</li>
<li>Put the logo in the same corner each time.</li>
<li>Use the same tone in captions: if you are friendly on one post and stiff on the next, people feel they are dealing with different organisations.</li>
<li>Name your files sensibly, for example <code>laf-logo-colour.png</code>, so others can find the right version.</li>
</ul>

<h3>Common mistakes</h3>
<ul>
<li>Changing colours and fonts for every new design because you are bored. Consistency feels dull to the designer but builds trust with the audience.</li>
<li>Using too many colours; stay with the palette.</li>
<li>Giving printers and volunteers a low-quality logo copied from a chat message.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Use the logo you designed in the last lesson, or make a simple one.</li>
<li>Choose a palette of three colours and write down their hex codes.</li>
<li>Choose one heading font and one body font.</li>
<li>Build a one-page brand kit with the logo, colours, fonts, voice and two rules.</li>
<li>Export it as a PDF and name it clearly.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A brand kit records the logo, colours, fonts, pictures and voice in one place.</li>
<li>Consistency makes an organisation recognisable and trusted.</li>
<li>Store every logo version and use clear file names.</li>
<li>Rules such as clear space and "do not stretch" protect the brand.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Posters, flyers and social media graphics',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to plan and design a poster, a flyer and a social media graphic using everything you have learned. This matters because these are the designs people most often need, for events, businesses, campaigns and community news.</p>

<h3>Know the difference</h3>
<table>
<thead><tr><th>Piece</th><th>Seen from</th><th>Amount of text</th><th>Main aim</th></tr></thead>
<tbody>
<tr><td>Poster</td><td>A few metres away, in seconds</td><td>Very little</td><td>Catch attention and give the key facts</td></tr>
<tr><td>Flyer</td><td>In the hand, with time to read</td><td>More, in clear groups</td><td>Explain the offer and how to respond</td></tr>
<tr><td>Social graphic</td><td>On a phone while scrolling</td><td>Very little</td><td>Stop the thumb and share one idea</td></tr>
</tbody>
</table>

<h3>A reliable poster recipe</h3>
<ol>
<li><strong>Headline:</strong> five to eight words, the largest item, such as "Free Computer Class for Girls".</li>
<li><strong>One strong picture or shape</strong> that supports the headline.</li>
<li><strong>Key details:</strong> date, time, place, price, in a group of their own, in medium size.</li>
<li><strong>Call to action:</strong> what to do and how, such as "Register at the community hall or call 0700 000 000".</li>
<li><strong>Logo and small print</strong> in a corner or at the bottom.</li>
</ol>
<p>Check every date, name and number twice, and ask someone else to check them as well.</p>

<h3>Social media graphics</h3>
<ul>
<li>Use the size recommended by the platform (for example 1080 by 1350 pixels for a portrait feed post or 1080 by 1920 for stories).</li>
<li>Keep one message per graphic. Large, bold text of a few words beats a paragraph.</li>
<li>Leave a safe margin, because apps cover parts of the screen with buttons, names and captions.</li>
<li>Make a series with the same template so your feed looks organised.</li>
<li>Write useful text in the caption, not only on the picture, and add a short description of the picture (alt text) where the platform allows it, so that people using screen readers can understand it.</li>
</ul>

<h3>Flyers</h3>
<p>Divide the page into clear sections with headings, use short sentences and bullet points, and keep space between groups. Put the contact information in a place where people can find it quickly, and do not forget a map or landmark if people must travel to a place. For print, leave about 5 mm of margin so nothing is cut off.</p>

<h3>Check your design</h3>
<ol>
<li>Show it to someone for three seconds. What did they notice first?</li>
<li>Does it answer: what, when, where, how?</li>
<li>Is any text too small for the real viewing distance?</li>
<li>Is it your brand: colours, fonts, logo?</li>
</ol>

<h3>Try it yourself</h3>
<ol>
<li>Choose a real or imaginary event, such as a school open day.</li>
<li>Write the brief: audience, goal, key facts.</li>
<li>Design an A4 poster using the recipe above.</li>
<li>Resize or duplicate it into a 1080 by 1350 pixel social graphic with fewer words.</li>
<li>Ask two people to describe what the design is asking them to do.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Posters need very few words; flyers can carry more; social graphics need one idea.</li>
<li>Use headline, picture, key details, call to action, logo.</li>
<li>Respect platform sizes and safe margins.</li>
<li>Proofread dates, names and numbers before publishing.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the difference between a logo and a brand?
- There is no difference
* A logo is a symbol or name that identifies you; the brand is the whole impression people have of you
- A brand is only the colours
- A logo is the same as an advertisement
= The logo is one part of the brand.

Q: Which of these is a quality of a good logo? (choose all that apply)
* Simple
* Flexible: it works small and large, in colour and black and white
- Full of tiny details and many effects
- Copied from a famous brand
= A good mark is simple, memorable, suitable, flexible and original.

Q: What should you do first when designing a logo?
- Pick a font at random
* Write a short brief and sketch many ideas on paper
- Download a template and change one word
- Choose ten colours
= Sketching many ideas helps you find a stronger one than the first idea.

Q: Which logo versions should you keep? (choose all that apply)
* Full colour
* Black only
* White (for dark backgrounds)
- A low-quality copy forwarded through chat
= A set of clean versions makes the logo usable everywhere.

Q: What is the purpose of a brand kit?
- To make designs more expensive
* To record logo, colours, fonts, pictures and voice so all designs stay consistent
- To list prices
- To replace the logo
= It lets anyone create on-brand work.

Q: Which is the best structure for a poster?
- Equal-sized text in every corner
* A large headline, one strong picture, grouped key details, a call to action and a small logo
- A long paragraph about the history of the group
- Ten different fonts
= Posters are read quickly, so they need clear levels and little text.

Q: Why should a social media graphic keep important text away from the edges?
- Platforms like empty edges
* Apps may cover parts of the screen with buttons, names and captions
- Text at the edge cannot be read
- It makes the file smaller
= A safe margin prevents key words from being hidden or cut.

Q: What is the most important check before publishing a poster?
- Add more decoration
* Proofread dates, names, places and phone numbers, ideally with a second person
- Make the logo larger than the headline
- Use a script font for all text
= A wrong date or number makes a design fail, however good it looks.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Delivering your work responsibly',
            'summary' => 'Export for web and print, respect copyright and licences, and gather feedback to build a portfolio.',
            'lessons' => [
                [
                    'title'   => 'Exporting for web and for print',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to export a finished design in the right file type, size and quality for a screen or a printer. This matters because many good designs are spoiled at the last step: a heavy file that will not send over WhatsApp, or a low-quality file that prints blurry.</p>

<h3>Choose the file type</h3>
<table>
<thead><tr><th>File</th><th>Best for</th><th>Notes</th></tr></thead>
<tbody>
<tr><td>JPG</td><td>Photos and designs with photos on the web or social media</td><td>Small file; quality is lost each time it is re-saved; no transparency</td></tr>
<tr><td>PNG</td><td>Logos, graphics with sharp text, transparent backgrounds, screenshots</td><td>Sharp; can be larger than JPG for photos</td></tr>
<tr><td>SVG</td><td>Logos and icons on websites</td><td>Vector; scales perfectly</td></tr>
<tr><td>PDF</td><td>Documents and print-ready designs</td><td>Keeps fonts and layout; accepted by print shops</td></tr>
<tr><td>Original working file</td><td>Future editing (Canva design, Inkscape SVG, GIMP XCF)</td><td>Always keep it</td></tr>
</tbody>
</table>

<h3>Exporting for web and social media</h3>
<ol>
<li>Make sure the design has the correct pixel size (for example 1080 by 1350 pixels).</li>
<li>In Canva choose <strong>Share</strong>, then <strong>Download</strong>, and pick <strong>PNG</strong> for sharp graphics or <strong>JPG</strong> for photo-heavy designs.</li>
<li>Check the file size. For web pages and slow connections aim for under about 500 KB per image where possible; large files slow down phones and use up data bundles.</li>
<li>Name the file clearly, using lower-case letters and hyphens: <code>clean-up-day-poster.png</code>.</li>
</ol>
<p>Remember that WhatsApp and some other apps compress pictures. To send a design in full quality, share it as a document or file, or send the PDF, not as a normal photograph.</p>

<h3>Exporting for print</h3>
<ol>
<li>Ask the print shop for their requirements: size, file type, colour mode, bleed and the number of copies.</li>
<li>Design at the true final size, such as A4 or A3, with images of about 300 pixels per inch at that size.</li>
<li>Add <strong>bleed</strong>: if a background colour or picture runs to the edge of the paper, extend it about 3 mm beyond the trim edge. Printers cut paper by machine and the cut is never perfect, so without bleed you may see a thin white edge. Canva offers <strong>Show print bleed</strong> in the File menu (View settings) for supported print sizes.</li>
<li>Keep important text and logos at least 5 mm inside the trim edge, the <strong>safe area</strong>.</li>
<li>Export as <strong>PDF Print</strong> in Canva (it offers crop marks and bleed options and a CMYK colour option for some plans), or as a PDF from another tool.</li>
<li>Check the PDF on screen at 100 percent, read every word, and print one test copy on plain paper.</li>
</ol>

<h3>Using a local print shop</h3>
<p>Many small print shops work from a PDF or a high-quality JPG and handle the settings themselves. Take your file on a flash disk or send it as a document. Bring the original working file too, in case a change is needed. Ask for a proof or a single test copy before printing hundreds of flyers. Compare the prices of paper types, because thicker, glossy paper costs more but may not be needed.</p>

<h3>Common mistakes</h3>
<ul>
<li>Exporting a low-resolution preview, or a picture screenshot, for print.</li>
<li>Putting text too near the edge, which gets cut.</li>
<li>Sending the only copy of your working file to the printer and losing it.</li>
<li>Sending a photo of the screen instead of the real file.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Take the poster you designed and export it as a PNG and a JPG.</li>
<li>Compare the two file sizes in the folder, and open them to see which looks sharper.</li>
<li>Export it again as a PDF for print, with bleed if your tool offers it.</li>
<li>Print one test copy at A4 if you can, and check that no text is near the edge.</li>
<li>Save the original working file in a folder named after the project.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>PNG and JPG are for screens, SVG for vector logos on the web, PDF for print.</li>
<li>Keep web files small, and share full-quality files as documents when needed.</li>
<li>For print, use the real size, about 300 PPI, bleed and a safe area.</li>
<li>Always test print once and keep the original working file.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Copyright, licences and using other people\'s work',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use pictures, fonts, music and templates legally, and to protect your own work. This matters because taking images from the internet without permission can lead to takedown notices, lost clients and even legal claims, while following simple rules keeps you safe and respected.</p>

<h3>What copyright is</h3>
<p><strong>Copyright</strong> is the legal right of the creator over their original work, such as a photograph, drawing, song, article or design. It begins automatically when the work is created; no registration or symbol is needed in most countries. The creator decides who may copy, change, publish or sell it. Uganda has its own copyright law, and many countries follow international agreements, so the basic idea is similar around the world. If you are doing paid work, check the law and the licence terms for your situation.</p>
<p>Finding an image on a search engine or receiving it on WhatsApp does <strong>not</strong> mean you may use it. "It was on the internet" is not permission.</p>

<h3>Ways to use images legally</h3>
<table>
<thead><tr><th>Source</th><th>What to know</th></tr></thead>
<tbody>
<tr><td>Your own photos and drawings</td><td>You own them, but if a person is clearly shown, ask for their permission, especially for children</td></tr>
<tr><td>Free stock sites with their own licences</td><td>Sites such as Unsplash, Pexels and Pixabay publish licence terms; read them, because rules differ and change</td></tr>
<tr><td>Creative Commons works</td><td>The creator allows reuse under stated conditions, such as giving credit or not using it commercially</td></tr>
<tr><td>Paid stock or a licence bought from the creator</td><td>You may use it within the terms you bought</td></tr>
<tr><td>Public domain</td><td>The copyright has ended or been given up, so anyone can use it</td></tr>
<tr><td>Tool libraries (Canva elements, templates)</td><td>Follow the tool's content licence; for example, you usually cannot sell a template unchanged or register an element alone as a logo</td></tr>
</tbody>
</table>

<h3>Understand Creative Commons</h3>
<p>Creative Commons licences use short names. <strong>CC BY</strong> means you may use it if you credit the creator. <strong>NC</strong> means non-commercial only; <strong>ND</strong> means no changes; <strong>SA</strong> means share alike, so your new work must use the same licence. <strong>CC0</strong> means the creator gives up their rights. Always read the actual licence page and give credit in the way requested, for example: "Photo: Name, source, licence".</p>

<h3>Fonts, logos and music</h3>
<ul>
<li><strong>Fonts</strong> have licences too. Open-licence fonts such as those on Google Fonts allow commercial use under their terms; some "free" fonts allow personal use only.</li>
<li><strong>Logos of other brands</strong> are trademarks. You may not use a company's logo in a way that suggests it supports your event unless they agree.</li>
<li><strong>Music and video clips</strong> are also copyrighted; use music from libraries that clearly allow your use.</li>
</ul>

<h3>Protect your own work</h3>
<ol>
<li>Keep your original working files and dates; they are proof that you created the work.</li>
<li>Put your name and year on portfolio pieces (for example in the file name or a small credit).</li>
<li>For client work, agree in writing who owns the final design, whether the client can edit it, and whether you may show it in your portfolio.</li>
<li>Share low-resolution previews until you are paid, if that is your agreement.</li>
</ol>

<h3>If in doubt</h3>
<p>Choose another picture, make your own, or ask the owner in writing. Keep a short note of where every image came from and what licence it has; it saves you if anyone asks later.</p>

<h3>Try it yourself</h3>
<ol>
<li>Choose a photo for a poster from a free stock site, and open its licence page.</li>
<li>Write what the licence allows and whether you must give credit.</li>
<li>Make a small "image sources" list for one of your designs, with the source, the creator and the licence.</li>
<li>Visit creativecommons.org and find the meaning of one licence you did not know.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Copyright protects creators automatically; being online does not make work free to use.</li>
<li>Use your own work, licensed stock, public domain or Creative Commons within their terms.</li>
<li>Fonts, brand logos and music have rules too.</li>
<li>Keep records and agree ownership in writing for client work.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Feedback, improving and building a portfolio',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to gather and use feedback, improve a design in rounds, and build a small portfolio that helps you find work. This matters because designers grow by repeating the cycle of making, showing and improving, and because a portfolio of real examples convinces people far more than words on a CV.</p>

<h3>Ask for feedback the right way</h3>
<p>"Do you like it?" gets polite answers. Ask questions that reveal how the design works.</p>
<ul>
<li>"What is the first thing you notice?"</li>
<li>"What do you think this is asking you to do?"</li>
<li>"Is there anything you could not read or understand?"</li>
<li>"What would you change?"</li>
</ul>
<p>Ask people from the audience, not only friends who want to be kind. Listen without defending. If three people misread the date, the date is the problem, not the readers. You do not have to accept every opinion; look for repeated comments.</p>

<h3>Improve in rounds</h3>
<ol>
<li>Save the first version and make a copy before changing it.</li>
<li>Fix the biggest issue first, usually hierarchy, contrast or too much text.</li>
<li>Then adjust alignment, spacing and details.</li>
<li>Check again with the same people.</li>
</ol>
<p>Use a quick personal checklist before you share any design:</p>
<table>
<thead><tr><th>Question</th><th>Principle</th></tr></thead>
<tbody>
<tr><td>Is there one clear starting point?</td><td>Hierarchy</td></tr>
<tr><td>Can I read everything from the right distance?</td><td>Contrast and size</td></tr>
<tr><td>Are things lined up and grouped logically?</td><td>Alignment and proximity</td></tr>
<tr><td>Is there room to breathe?</td><td>White space</td></tr>
<tr><td>Do colours and fonts match the brand?</td><td>Repetition and consistency</td></tr>
<tr><td>Are all facts correct?</td><td>Proofreading</td></tr>
</tbody>
</table>

<h3>Building a portfolio</h3>
<p>A <strong>portfolio</strong> is a collection of your best work. You do not need clients to start.</p>
<ol>
<li>Create four to six pieces: a logo with a brand kit, a poster, a flyer, and two social media graphics. Use real or imaginary organisations, and say which is which.</li>
<li>For each piece write two or three lines: the goal, the audience, what you did and what you learned.</li>
<li>Show only your best. Five strong pieces are better than fifteen weak ones.</li>
<li>Place them where people can see them: a free portfolio page, a Behance or similar profile, a public folder with a link, or a short PDF you can send by WhatsApp or email.</li>
<li>Add your name, a way to contact you, and your skills.</li>
</ol>

<h3>Finding practice work</h3>
<ul>
<li>Offer to design for a school club, church group, sports team or a small local shop, with their permission and a written note about how the work may be used.</li>
<li>Join community challenges and redesign existing posters as a practice exercise (and label them as practice).</li>
<li>Keep learning with free resources such as Canva Design School.</li>
</ul>
<p>Be honest. Do not claim someone else's work as yours, and respect any confidentiality agreements.</p>

<h3>Try it yourself</h3>
<ol>
<li>Choose your best design from this course and show it to three people with the questions above.</li>
<li>Write down their answers and find any repeated comment.</li>
<li>Make a second version that fixes the biggest problem.</li>
<li>Write a three-line description of the project for your portfolio.</li>
<li>List the five pieces you will create to complete a starter portfolio.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Ask specific questions and listen for repeated feedback.</li>
<li>Improve in rounds, biggest problems first.</li>
<li>A small portfolio of strong, explained work opens doors.</li>
<li>Work honestly and get permission to show client or community work.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: You need a sharp logo with a transparent background to place on a coloured poster on screen. Which file is a good choice?
- JPG
* PNG
- A screenshot of a WhatsApp chat
- A Word file
= PNG supports transparency; JPG does not.

Q: Which file type do print shops commonly prefer for finished artwork?
- A low-resolution screenshot
- A WhatsApp-compressed photo
* PDF
- A slide show
= A PDF keeps fonts and layout and is widely accepted for print.

Q: What is bleed?
- An ink colour
* Background extended slightly beyond the trim edge so no white border appears after cutting
- A font style
- The margin for text
= Printers cut by machine, so backgrounds must run past the edge.

Q: Why should important text stay inside a safe area when printing?
- To make the file smaller
* So it is not cut off when the paper is trimmed
- Because printers cannot print text
- To increase the contrast
= Text close to the edge may be trimmed away.

Q: Which statements about copyright are correct? (choose all that apply)
* It protects a creator's original work automatically
* Finding an image online does not mean you may use it
- You may use anything that is on WhatsApp
- Only registered works are protected anywhere
= Online availability does not equal permission.

Q: What does a Creative Commons CC BY licence usually require?
- That you pay a fee
* That you give credit to the creator
- That you never change anything
- That you delete the image after a week
= Always read the exact licence, and credit in the way requested.

Q: You want to use a picture of a well-known company's logo on your event poster to suggest it supports you. What is the right approach?
- Use it; logos are free
* Do not use it unless the company agrees, because logos are trademarks
- Change its colours and use it
- Place it in the corner so it is small
= Using a brand's logo can suggest endorsement and may break trademark rules.

Q: Which question gives the most useful design feedback?
- "Do you like it?"
* "What is the first thing you notice, and what do you think it asks you to do?"
- "Is it perfect?"
- "Can you say something nice?"
= Specific questions show whether the hierarchy and message work.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What is the first question a designer should ask before starting a project?
- Which font is trendy?
* What is this design supposed to achieve, and for whom?
- How many colours can I use?
- Which effect looks best?
= Design solves a communication problem for an audience.

Q: What is the best use of the first minutes of a poster project?
- Open the software and experiment
* Write a brief, collect and check the content, and sketch rough layouts
- Choose ten fonts
- Print immediately
= Planning and correct content prevent mistakes and wasted time.

Q: What is contrast in design?
- Using many similar elements
* A clear difference between elements, such as in size, colour, weight or space
- The amount of white space
- A type of grid
= Contrast makes things stand out and keeps text readable.

Q: A heading in light yellow on a white background is hard to read. What is the best fix?
- Make it italic
* Use a much darker colour or a dark background behind it
- Make it smaller
- Add a shadow in light grey
= Text needs strong contrast against its background.

Q: What does visual hierarchy do?
- It decides the file type
* It shows the reader what to look at first, second and third
- It sets the print size
- It chooses the font
= Hierarchy organises information by importance.

Q: Which principle says that related items should sit close together?
- Repetition
- Alignment
* Proximity
- Resolution
= Proximity groups related items so readers see them as one idea.

Q: Why is white space important?
- It saves ink only
* It gives the eye room to rest and makes important things stand out
- It is required by law
- It makes images sharper
= Space is an active design tool.

Q: Which alignment is usually best for paragraphs of text?
- Centred
- Right aligned
* Left aligned
- Mixed alignments
= Left-aligned text gives the eye a steady starting point.

Q: Which pair of colours is complementary?
- Red and orange
- Blue and green
* Blue and orange
- Yellow and green
= Complementary colours are opposite on the colour wheel.

Q: In the 60-30-10 rule, what is the 10 percent usually used for?
- The background
* An accent colour for key items such as buttons or key words
- The body text
- The border
= The accent should be used sparingly so it stands out.

Q: Which colour mode is used for designs shown on phone screens?
* RGB
- CMYK
- Both are equally correct for phones
- Pantone
= Screens mix red, green and blue light.

Q: Which statements about colour accessibility are true? (choose all that apply)
* Colour should not be the only way to carry meaning
* Text needs strong contrast with its background
- Red text on a green background is always best
- Accessibility only matters for websites
= Many people have colour vision differences or read in bright sunlight.

Q: Which is the safest and neatest font choice for a beginner?
- Five different decorative fonts
* One family used in two weights, or two well-contrasted fonts
- Two similar fonts that are slightly different
- All capital script text
= Limiting fonts keeps designs clean and consistent.

Q: Which settings improve paragraph readability?
- Very tight line spacing
* Line spacing of about 1.4 to 1.6 and lines of about 45 to 75 characters
- Stretching text to fill the box
- Justified text in a narrow column
= Comfortable spacing and line length help reading.

Q: What is the rule of thirds?
- Use three fonts
- Print three copies
* Divide the page into thirds and place key elements near the lines or where they cross
- Use three colours only
= It creates a more natural composition than placing everything in the centre.

Q: A poster has a heavy dark photo on the left and a small light text box on the right. What can be done to balance it?
- Nothing; it is perfect
* Increase the weight of the right side, for example with larger text or a coloured shape
- Add more photos on the left
- Remove all white space
= Visual weight on both sides should feel steady.

Q: What is the main difference between raster and vector graphics?
* Raster uses pixels and blurs when enlarged; vector uses shapes defined by maths and stays sharp
- Raster is for logos; vector is for photos
- Vector files are always smaller
- There is no difference
= Logos should be vectors so they scale to any size.

Q: Which tool is mainly a free vector editor?
* Inkscape
- GIMP
- A phone camera
- WhatsApp
= Inkscape draws vector shapes; GIMP edits pixels.

Q: What is a good approach to creating a logo?
- Trace a famous brand and change the colours
* Write a brief, sketch many ideas on paper, then build the strongest one simply
- Use a very detailed illustration
- Use six fonts
= Original, simple, flexible logos work best.

Q: What goes into a brand kit? (choose all that apply)
* Logo versions and clear-space rules
* Colours with codes and fonts
- The owner's passwords
- Competitor logos
= A brand kit keeps designs consistent across people and time.

Q: Which is the best layout for a poster?
- A long paragraph with a small headline
* A large headline, one strong picture, grouped key details and a clear call to action
- Equal emphasis for every item
- Everything in capitals
= Posters are read in seconds, so keep them clear and short.

Q: You will print an A4 flyer with a background colour that runs to the paper edge. What should you add?
- A thicker font
* Bleed of about 3 mm beyond the trim line, and keep text inside a safe area
- A lower resolution
- A white border only
= Bleed prevents white edges after cutting.

Q: Which resolution is a common target for printed images at the final printed size?
- 72 pixels per inch
- 50 pixels per inch
* About 300 pixels per inch
- 10 pixels per inch
= Screens need less; print needs more detail.

Q: Which statement about using images from the internet is correct?
- Anything found by a search engine is free to use
* You need the right to use it, for example through its licence, permission, or because it is your own
- Pictures sent on WhatsApp are always free to use
- Credit is never needed
= Copyright protects creators even when their work is easy to find.

Q: What is a smart way to get design feedback?
- Ask only your best friend if they like it
* Ask people from the audience what they notice first and what they think it asks them to do
- Never show it until it is printed
- Change it every time someone speaks
= Specific questions from real readers reveal problems that kind comments hide.
QUIZ,
];
