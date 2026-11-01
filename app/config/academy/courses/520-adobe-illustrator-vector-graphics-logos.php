<?php
return [
    'slug'        => 'adobe-illustrator-vector-graphics-logos',
    'title'       => 'Adobe Illustrator: Vector Graphics, Logos and Illustration',
    'category'    => 'design',
    'level'       => 'intermediate',
    'icon'        => 'target',
    'sort'        => 520,
    'summary'     => 'Learn vector design with Adobe Illustrator (and free alternatives): the Pen tool, shapes, colour, type, logos, icons, patterns, export, print preparation and delivering a logo package to a client.',
    'description' => '<p>Logos, icons, maps, posters and many illustrations are made as vector graphics: pictures built from clean paths that stay sharp at any size. Adobe Illustrator is the professional program for this work. It is a paid subscription application with a free trial, so this course also shows the free alternatives, such as Inkscape and browser-based vector editors, and where their menus differ. The ideas you learn are the same in every program.</p><p>You will start with the difference between vector and raster graphics, then set up documents and artboards and learn to select and transform objects. Next come paths, anchor points and handles, the Pen tool, shape building with Pathfinder and Shape Builder, strokes and brushes. You will then work with colour, type and layers, follow a professional logo process from sketch to vector, trace images, design icons on a grid, and test a logo at small size and in one colour.</p><p>The final modules cover flat illustration, patterns, symbols and mockups, then exporting to SVG, PDF, PNG and other formats, preparing files for print, packaging a logo with a one-page brand sheet for a client, and the basics of copyright and simple client agreements. Every lesson ends with a hands-on task, so you finish with a portfolio of real work.</p>',
    'outcomes'    => [
        'Explain the difference between vector and raster images and choose the right one for a job',
        'Set up Illustrator documents, artboards and colour modes correctly for screen or print',
        'Draw accurate shapes and curves with the Pen tool, paths, anchor points and handles',
        'Build logos and icons with Pathfinder, Shape Builder, strokes and grids',
        'Use swatches, global colours, gradients, type and outlines, and organise files with layers',
        'Follow a logo design process and test a logo at small size, in one colour and on backgrounds',
        'Trace images with Image Trace and create flat illustrations, patterns and simple mockups',
        'Export for web and print, and deliver a logo package with a brand sheet while respecting copyright',
    ],
    'requirements' => ['Comfortable using a computer, folders and files', 'Graphic Design Fundamentals (graphic-design-fundamentals) or equivalent knowledge is helpful', 'No Illustrator experience needed; a free trial of Illustrator, or the free Inkscape editor, is enough to practise'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Adobe Illustrator: user guide and tutorials', 'url' => 'https://helpx.adobe.com/illustrator/user-guide.html'],
        ['title' => 'Inkscape (free vector graphics editor)', 'url' => 'https://inkscape.org/'],
        ['title' => 'Google Fonts', 'url' => 'https://fonts.google.com/'],
        ['title' => 'Creative Commons', 'url' => 'https://creativecommons.org/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: Vector foundations and the workspace',
            'summary' => 'Understand vector graphics, set up a document and artboards, and learn to select and transform objects, in Illustrator or a free alternative.',
            'lessons' => [
                [
                    'title'   => 'Vector versus raster, and why logos must be vector',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to tell a vector file from a raster file, choose the right one for a job, and explain why a logo should always exist as a vector. This matters because a business that only has a small, blurry logo picture cannot print a signboard, a T-shirt and a business card from the same file.</p>

<h3>Two ways to store a picture</h3>
<p>A <strong>raster</strong> image (also called a bitmap) is a grid of tiny coloured squares called pixels. Photos from your phone are raster. When you enlarge a raster image, the computer must invent more pixels, so the edges turn soft and blocky. A <strong>vector</strong> image is stored as instructions: "draw a curve from this point to that point, 6 points thick, in this colour". Because it is maths, the shape can be made as small as a stamp or as large as a billboard and the edges stay perfectly sharp.</p>

<table>
<thead><tr><th>Feature</th><th>Raster (pixels)</th><th>Vector (paths)</th></tr></thead>
<tbody>
<tr><td>Best for</td><td>Photos, scanned drawings, paintings</td><td>Logos, icons, maps, charts, simple illustrations</td></tr>
<tr><td>Enlarging</td><td>Gets blurry or blocky</td><td>Stays sharp at any size</td></tr>
<tr><td>File size</td><td>Grows with image size</td><td>Usually small for simple artwork</td></tr>
<tr><td>Common formats</td><td>JPG, PNG, WebP, TIFF</td><td>SVG, AI, EPS, PDF</td></tr>
<tr><td>Editing</td><td>Change pixels (Photoshop, GIMP)</td><td>Change shapes and points (Illustrator, Inkscape)</td></tr>
</tbody>
</table>

<h3>A worked example</h3>
<p>Imagine a small shop in Jinja has a logo saved as a 300 pixel wide PNG. It looks fine on a phone. The owner now orders a signboard 2 metres wide. At that size each pixel would be about 6 or 7 millimetres wide, so the logo looks like a mosaic. A designer who redraws the logo as a vector gives the shop one file that works on the signboard, the receipt book and a social media profile picture.</p>

<h3>Where Illustrator fits</h3>
<p>Adobe Illustrator is a professional vector drawing application for Windows and Mac. It is sold as a paid subscription, with a free trial you can use to learn. Free tools teach the same ideas: <strong>Inkscape</strong> is a free desktop vector editor, and several free editors run in a web browser. Everything in this course is explained for Illustrator, with notes on where the free tools differ, so you can practise even without a subscription.</p>

<h3>Vector does not mean "always better"</h3>
<p>Vectors are poor at photographs, because a photo has millions of subtle colour changes. Many real projects mix both: a poster might use a raster photo with a vector logo and vector text on top. Knowing which part is which helps you export correctly later.</p>

<blockquote><p><strong>Quick check:</strong> A church wants one logo file for a small hymn-book cover and a large banner. Should you supply it as a JPG photo of the logo or as a vector file?</p><p>Answer: A vector file, because it stays sharp at any size while a JPG would look blurry on the banner.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Is a photograph of a crowd at a school event best kept as raster or vector?</p><p>Answer: Raster, because a photograph has millions of tiny colour changes that pixels store well and simple vector paths cannot.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Find any small logo picture on your computer or phone (a PNG or JPG).</li>
<li>Open it and zoom in to 400 percent. Take a screenshot of the blurry or blocky edges.</li>
<li>Download the free Inkscape editor or open a browser vector editor, then draw a circle and a star and zoom in to 400 percent. Take a second screenshot.</li>
<li>Put both screenshots side by side in a document and write two sentences saying what you see. Keep it as the first item in your portfolio notes.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Raster images are pixels; vector images are mathematical paths.</li>
<li>Vectors scale to any size without losing sharpness, so logos and icons should be vectors.</li>
<li>Photos stay raster; many projects combine both.</li>
<li>Illustrator is paid software with a free trial; Inkscape and browser editors teach the same ideas for free.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The Illustrator workspace, documents and artboards',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to create a new document with the right size and colour mode, find the main tools and panels, and add several artboards. This matters because a wrong setup at the start, such as the wrong colour mode, causes problems that are painful to fix at the end of a project.</p>

<h3>Creating a document</h3>
<ol>
<li>Choose <strong>File &gt; New</strong> (Ctrl+N, Cmd+N on Mac).</li>
<li>Pick a preset tab at the top. In current versions you will see tabs such as Mobile, Web, Print and Art &amp; Illustration. Pick <strong>Web</strong> for screen work or <strong>Print</strong> for printed work.</li>
<li>On the right, set the <strong>Width</strong> and <strong>Height</strong>, the unit (pixels, millimetres, points), the number of <strong>Artboards</strong>, and the <strong>Color Mode</strong> (RGB for screens, CMYK for print).</li>
<li>Under <strong>Bleed</strong>, leave 0 for screen work. For print you will add 3 mm (see the print lesson).</li>
<li>Click <strong>Create</strong>.</li>
</ol>

<h3>Understanding the screen</h3>
<table>
<thead><tr><th>Area</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Toolbar (usually on the left)</td><td>Holds tools such as Selection (V), Direct Selection (A), Pen (P), Type (T), Rectangle (M), Ellipse (L)</td></tr>
<tr><td>Control bar (top)</td><td>Shows quick options for whatever you have selected</td></tr>
<tr><td>Properties panel (right)</td><td>Choose <strong>Window &gt; Properties</strong>; it shows the settings that matter for the current selection</td></tr>
<tr><td>Layers panel</td><td>Choose <strong>Window &gt; Layers</strong> (F7); organises your objects</td></tr>
<tr><td>Artboard</td><td>The white page area. Only what is on an artboard is exported by default</td></tr>
<tr><td>Canvas</td><td>The grey area around artboards; a handy parking place for spare parts</td></tr>
</tbody>
</table>
<p>If a panel is missing, open the <strong>Window</strong> menu and tick it. To restore a messy layout, choose <strong>Window &gt; Workspace &gt; Reset</strong> followed by the workspace name, such as Essentials.</p>

<h3>Moving around</h3>
<ul>
<li>Zoom in and out with Ctrl and + or - (Cmd on Mac), or hold Alt (Option) and scroll the mouse wheel.</li>
<li>Hold the <strong>Space bar</strong> to turn the pointer into a hand and drag the view.</li>
<li>Fit the artboard on screen with Ctrl+0 (Cmd+0).</li>
<li>Turn on rulers with Ctrl+R (Cmd+R) and Smart Guides with Ctrl+U (Cmd+U). Smart Guides show helpful pink or green hints as you align things.</li>
</ul>

<h3>Working with artboards</h3>
<p>An artboard is a page. One file can hold many, which is perfect for a logo project: one artboard for the main logo, one for the one-colour version, one for the icon. Press <strong>Shift+O</strong> for the <strong>Artboard</strong> tool, then drag on the canvas to draw a new artboard, or click the New Artboard button in the Properties panel. Rename artboards in the Artboards panel (<strong>Window &gt; Artboards</strong>) so exported files get helpful names.</p>

<h3>Saving</h3>
<p>Use <strong>File &gt; Save</strong> (Ctrl+S, Cmd+S) and keep the native .ai file as your editable master. Save early and often. Illustrator can offer to save a cloud copy; for learning, saving on your own computer and a USB drive or cloud folder as backup is fine.</p>

<h3>Mini-case</h3>
<p>Amina is making a logo for a school debating club. She creates a Web-preset document at 1000 by 1000 pixels in RGB, adds three artboards and names them Main, One colour and Icon. Because she set RGB and pixels at the start, her social media exports will be correct without conversion.</p>

<blockquote><p><strong>Quick check:</strong> You are designing a leaflet that a printing press will print. Which colour mode should the document usually start in?</p><p>Answer: CMYK, because print uses cyan, magenta, yellow and black inks and CMYK shows you colours closer to the printed result.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A panel such as Layers has disappeared. Where do you bring it back?</p><p>Answer: From the Window menu, by ticking the panel name (Layers is also F7), because every panel is listed there.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a new Web document, 800 by 800 pixels, RGB, with 3 artboards.</li>
<li>Open the Artboards panel and rename them Main, One colour and Icon.</li>
<li>Draw a rectangle (M) on the first artboard, then use Ctrl+0 (Cmd+0) to fit the view.</li>
<li>Save the file as <code>my-first-logo-project.ai</code> and note its location. If you use Inkscape instead, create a new document and use Save As to keep an SVG.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Set size, units and colour mode (RGB or CMYK) when you create the document.</li>
<li>Panels live in the Window menu; Properties shows what matters for your selection.</li>
<li>Use several named artboards for the different versions of one project.</li>
<li>Keep the editable .ai master and save often.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Free alternatives: Inkscape and browser vector editors',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to practise every vector skill in this course without a paid subscription, and translate an Illustrator instruction into its free equivalent. This matters because many learners do not have a powerful computer or a subscription, yet the thinking behind vector design is the same everywhere.</p>

<h3>Your free options</h3>
<ul>
<li><strong>Inkscape</strong> is a free, open-source desktop editor for Windows, Mac and Linux. It is closest to Illustrator in power. It saves SVG natively and can export PNG and PDF.</li>
<li><strong>Browser-based editors</strong> such as Vectr and the free tier of some design websites let you draw vector shapes with nothing to install. They are good for logos and icons on a slow computer, but they have fewer advanced features and free plans can change, so check the current limits.</li>
<li><strong>Illustrator free trial</strong> gives full features for a limited period. Use it when you can get steady access, for example at a lab or training centre.</li>
</ul>

<h3>Tool comparison</h3>
<table>
<thead><tr><th>Idea</th><th>Illustrator</th><th>Inkscape</th></tr></thead>
<tbody>
<tr><td>Select whole object</td><td>Selection tool (V)</td><td>Select tool (S)</td></tr>
<tr><td>Edit points</td><td>Direct Selection (A)</td><td>Node tool (N)</td></tr>
<tr><td>Draw curves</td><td>Pen tool (P)</td><td>Pen / Bezier tool (B)</td></tr>
<tr><td>Combine shapes</td><td>Pathfinder panel, Shape Builder (Shift+M)</td><td>Path menu: Union, Difference, Intersection</td></tr>
<tr><td>Change colour</td><td>Swatches, Color, Properties panels</td><td>Fill and Stroke dialog (Ctrl+Shift+F)</td></tr>
<tr><td>Text to shapes</td><td>Type &gt; Create Outlines</td><td>Path &gt; Object to Path</td></tr>
<tr><td>Trace a picture</td><td>Image Trace</td><td>Path &gt; Trace Bitmap</td></tr>
<tr><td>Export</td><td>File &gt; Export</td><td>File &gt; Export (PNG) or Save As (SVG, PDF)</td></tr>
</tbody>
</table>
<p>Menus and shortcuts change between versions, so if you cannot find something, look for the tool by name in the menu or the search box of the help menu. The names above are a guide, not a promise.</p>

<h3>How to practise when you have no Illustrator</h3>
<ol>
<li>Read each lesson and find the free tool's equivalent from the table.</li>
<li>Do the "Try it yourself" steps in Inkscape or a browser editor. The result is the same: clean vector shapes.</li>
<li>Save as SVG. SVG opens in Illustrator later, so your portfolio work transfers.</li>
</ol>

<h3>What you will miss</h3>
<p>Illustrator has strong CMYK and print features, many brushes and effects, and tight links with other Adobe apps. Inkscape works mainly in RGB, so print work may need a final conversion by a print shop or another tool. For logos, icons and web illustrations, free tools are entirely adequate to learn on and to make professional work.</p>

<h3>Mini-case</h3>
<p>Brian shares a ten-year-old laptop at a youth centre in Mbale. Illustrator would run slowly, so he uses Inkscape for every exercise. When the centre gets a trial licence, he opens his SVG files in Illustrator and finishes the print versions there. Nothing he practised was wasted.</p>

<blockquote><p><strong>Quick check:</strong> Which Inkscape tool is the match for Illustrator's Direct Selection tool?</p><p>Answer: The Node tool, because both let you select and move the individual points of a path.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You practise in a free editor and want your work to open later in Illustrator. Which format do you save?</p><p>Answer: SVG, because it is an open vector format that both programs read.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Install Inkscape from its official website, or open a browser vector editor.</li>
<li>Draw a rectangle, a circle and a star. Give each a different fill colour.</li>
<li>Use Path &gt; Union (or the editor's equivalent) to join the circle and rectangle into one shape.</li>
<li>Save as SVG with the name <code>practice-shapes.svg</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Inkscape and browser editors are free ways to learn vector skills.</li>
<li>Tool names differ but the ideas match: select, node edit, pen, combine, export.</li>
<li>Save as SVG so work moves between programs.</li>
<li>Illustrator adds strong print and colour features for professional delivery.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Selecting, moving and transforming objects',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to select the right thing, move it exactly, scale, rotate and reflect it, and align objects neatly. This matters because most of your time in a vector program is spent adjusting objects, and precise handling is what makes artwork look professional and not sloppy.</p>

<h3>Two selection tools</h3>
<ul>
<li><strong>Selection tool (V, black arrow):</strong> selects and moves a whole object or group.</li>
<li><strong>Direct Selection tool (A, white arrow):</strong> selects single anchor points or segments inside an object.</li>
<li><strong>Group Selection tool:</strong> in current versions it sits under the Direct Selection tool in the toolbar and selects one object inside a group. Double-clicking a group with the Selection tool enters <strong>Isolation mode</strong>, where you can edit its parts; press Esc to leave.</li>
</ul>
<p>Hold <strong>Shift</strong> and click to add more objects to the selection. Drag a box around objects to select all inside. Use <strong>Select &gt; All</strong> (Ctrl+A, Cmd+A) and <strong>Select &gt; Deselect</strong> (Ctrl+Shift+A, Cmd+Shift+A).</p>

<h3>Transforming</h3>
<table>
<thead><tr><th>Action</th><th>Tool and shortcut</th><th>Tip</th></tr></thead>
<tbody>
<tr><td>Scale</td><td>Drag a corner of the selection box, or Scale tool (S)</td><td>Hold Shift to keep proportions; Alt (Option) scales from the centre</td></tr>
<tr><td>Rotate</td><td>Move just outside a corner, or Rotate tool (R)</td><td>Hold Shift to snap to 45 degree steps</td></tr>
<tr><td>Reflect (mirror)</td><td>Reflect tool (O)</td><td>Press Enter with the tool active to type an exact axis</td></tr>
<tr><td>Move exactly</td><td>Select, then Enter (Return) to open Move</td><td>Type horizontal and vertical distances</td></tr>
<tr><td>Copy while moving</td><td>Hold Alt (Option) and drag</td><td>Add Shift to keep the line straight</td></tr>
<tr><td>Repeat last action</td><td>Ctrl+D (Cmd+D)</td><td>Makes evenly spaced copies quickly</td></tr>
</tbody>
</table>

<h3>Worked example: five equal circles</h3>
<ol>
<li>Draw one circle with the Ellipse tool (L); hold Shift to keep it round.</li>
<li>Select it, press Enter, set Horizontal to 60 px, Vertical 0, then click <strong>Copy</strong>.</li>
<li>Press Ctrl+D (Cmd+D) three more times. You now have five circles spaced exactly 60 px apart.</li>
</ol>

<h3>Aligning and distributing</h3>
<p>Select several objects and use the <strong>Align</strong> buttons in the Properties panel (or <strong>Window &gt; Align</strong>). Choices include Align Left, Horizontal Align Center, Align Top and Distribute Horizontal Centers. By default objects align to the selection; you can choose <strong>Align to Artboard</strong> from the options to centre a logo on the page. The Transform panel (<strong>Window &gt; Transform</strong>) shows exact X, Y, width and height; the small chain icon locks proportions.</p>

<h3>Stacking order</h3>
<p>Objects stack like paper. Use <strong>Object &gt; Arrange</strong> with Bring to Front (Ctrl+Shift+] ), Bring Forward (Ctrl+]), Send Backward (Ctrl+[) and Send to Back (Ctrl+Shift+[). On Mac use Cmd in place of Ctrl.</p>

<h3>Undo is your friend</h3>
<p>Ctrl+Z (Cmd+Z) steps back, and Ctrl+Shift+Z (Cmd+Shift+Z) steps forward. Illustrator keeps many steps, so experiment boldly.</p>

<blockquote><p><strong>Quick check:</strong> You want to move one corner point of a triangle only, not the whole triangle. Which tool?</p><p>Answer: The Direct Selection tool (A), because it selects individual anchor points rather than the whole object.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You must centre a logo on the artboard exactly. What is the quick way?</p><p>Answer: Select it and use Align to Artboard with Horizontal and Vertical Align Center, because it uses measured positions rather than your eye.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Draw one circle and make five evenly spaced copies using Enter and Ctrl+D (Cmd+D) as above.</li>
<li>Give them five different colours.</li>
<li>Select all five, then use Align and Distribute so they sit on one straight line, centred on the artboard.</li>
<li>Rotate the whole group 15 degrees. Save it as <code>circles-practice</code> to keep in your portfolio folder.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>V moves whole objects; A edits single points.</li>
<li>Shift keeps proportions or snaps angles; Alt (Option) copies or scales from the centre.</li>
<li>Ctrl+D repeats your last transformation for even spacing.</li>
<li>Use Align and the Transform panel for exact, professional positioning.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A shop owner needs one logo that works on a signboard and on a small receipt. Which file type suits this best?
- A 500 pixel JPG
* A vector file such as SVG or AI
- A screenshot of the logo
- A low-resolution GIF
= A vector keeps sharp edges at any size, so one file serves both uses.

Q: Which type of image is best for a detailed photograph of a wedding?
* Raster
- Vector
- Either one gives identical quality
- Neither; photographs cannot be stored on computers
= Photographs contain millions of tiny colour changes, which pixels store well; vectors are for simple shapes.

Q: You are designing a flyer to be printed on an offset press. Which colour mode do you normally choose when creating the document?
- RGB
- Grayscale only
* CMYK
- Indexed colour
= Printing uses cyan, magenta, yellow and black inks, so CMYK gives a closer preview of the result.

Q: Which tool edits a single anchor point inside a shape?
- Selection tool (V)
- Artboard tool
* Direct Selection tool (A)
- Zoom tool
= The Direct Selection tool selects points and segments; the Selection tool moves the whole object.

Q: You have no Illustrator. Which statement is true about practising?
- You cannot learn vector skills without a subscription
* Free editors such as Inkscape teach the same ideas; save as SVG so work can open elsewhere later
- You must buy a new laptop first
- Only paid programs can make logos
= The thinking (paths, points, shapes, colour) is the same in free tools, and SVG is an open format.

Q: You need five circles exactly 60 px apart in a row. What is the fastest accurate method?
- Drag each circle by eye
- Draw five circles and guess the spacing
* Copy one circle with Move (Enter) set to 60 px, then press Ctrl+D (Cmd+D) to repeat
- Redraw the circle five times with the Pen tool
= Repeating a transformation gives identical spacing with almost no effort.

Q: What is an artboard used for?
- It stores your fonts
* It is a page area; each artboard can be exported as its own file
- It is a type of brush
- It keeps a history of your colours
= Several named artboards let one file hold the logo, one-colour version and icon separately.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Paths, the Pen tool and shapes',
            'summary' => 'Learn how paths, anchor points and handles work, draw with the Pen tool properly, and build shapes with Pathfinder and Shape Builder.',
            'lessons' => [
                [
                    'title'   => 'Paths, anchor points and handles',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to name the parts of a vector path, tell a smooth point from a corner point, and edit a curve by moving its handles. This matters because every shape in Illustrator, from a letter to a leaf, is a path, and if you understand paths you can fix any curve instead of redrawing it.</p>

<h3>The parts of a path</h3>
<ul>
<li><strong>Anchor point:</strong> a dot that marks a place the path passes through.</li>
<li><strong>Segment:</strong> the line or curve between two anchor points.</li>
<li><strong>Handles (direction handles):</strong> the thin lines with round ends that appear on a selected smooth point. They control how the curve leaves and arrives at the point.</li>
<li><strong>Open path:</strong> has a start and an end, like a piece of string.</li>
<li><strong>Closed path:</strong> the end joins the start, like a ring. Only closed paths can hold a fill colour neatly.</li>
</ul>

<h3>Corner points and smooth points</h3>
<table>
<thead><tr><th>Point type</th><th>Handles</th><th>Looks like</th><th>Use for</th></tr></thead>
<tbody>
<tr><td>Corner</td><td>None, or handles that point in different directions</td><td>A sharp turn</td><td>Corners of a triangle, the tip of a leaf, an arrow head</td></tr>
<tr><td>Smooth</td><td>Two handles in one straight line</td><td>A flowing curve</td><td>Circles, hills, letters like O and S</td></tr>
</tbody>
</table>

<h3>Editing a curve</h3>
<ol>
<li>Draw an ellipse with the Ellipse tool (L). It has four smooth anchor points.</li>
<li>Choose the <strong>Direct Selection</strong> tool (A) and click one anchor point. Two handles appear.</li>
<li>Drag the end of a handle. A longer handle makes a rounder, bigger curve; a shorter handle makes a tighter one. Rotating a handle changes the direction of the curve.</li>
<li>Drag the point itself to move where the curve passes.</li>
</ol>
<p>To convert a point, select it and use the <strong>Convert selected anchor points to corner</strong> or <strong>smooth</strong> buttons that appear in the control bar. You can also hold Alt (Option) while dragging a handle to break the pair so the two sides move separately. In current versions the Anchor Point tool (Shift+C) also converts a point when you click or drag it.</p>

<h3>Live shapes and corners</h3>
<p>Basic shapes like rectangles keep small round widgets inside the corners in current versions. Drag a widget to round the corners. Remember this is a live effect: the shape remains a rectangle you can set exact corner radii for in the Properties panel.</p>

<h3>Good habits for clean paths</h3>
<ul>
<li>Use as few anchor points as you can. Fewer points make smoother, easier-to-edit curves.</li>
<li>Place points at the extremes of a curve: the top, bottom, left and right ends of a bend, not in the middle of a bulge.</li>
<li>Delete unnecessary points with the Direct Selection tool then press Delete. Illustrator reshapes the line through the remaining points.</li>
</ul>

<h3>Mini-case</h3>
<p>Grace draws a leaf for a farming cooperative logo. Her first attempt has twelve points and looks lumpy. She deletes eight, leaving a corner point at each tip and a smooth point on each side. The leaf becomes clean, and she finishes it by adjusting four handles.</p>

<blockquote><p><strong>Quick check:</strong> You select a smooth point and drag one handle longer. What happens to the curve?</p><p>Answer: The curve becomes rounder and wider on that side, because handle length controls how far the curve travels before bending.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A drawn line looks lumpy and has many points. What is the best first fix?</p><p>Answer: Delete unneeded points and adjust handles on the rest, because fewer well-placed points create smoother curves.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Draw an ellipse. With the Direct Selection tool, move its top point upward and drag the side handles until it looks like an egg.</li>
<li>Convert the bottom point to a corner so it becomes a teardrop.</li>
<li>Fill it green and duplicate it to make three leaves in a fan.</li>
<li>Save the result as <code>leaf-practice</code>. Notice you made it from one ellipse with only four points.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A path is anchor points joined by segments; handles control curves.</li>
<li>Smooth points have straight-line handle pairs; corner points make sharp turns.</li>
<li>Use the Direct Selection tool (A) to edit points and handles.</li>
<li>Fewer points means cleaner, smoother artwork.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The Pen tool done properly',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to draw straight lines, smooth curves and closed shapes with the Pen tool, and trace a simple picture accurately. This matters because the Pen tool is the most important drawing tool in Illustrator, and a designer who can use it well can draw anything.</p>

<h3>Getting ready</h3>
<p>Select the <strong>Pen</strong> tool (P). In the control bar or Properties panel set <strong>Fill</strong> to none and a visible <strong>Stroke</strong> such as black, 2 pt. Drawing with a fill makes the path hard to see while you learn. Turn on Smart Guides (Ctrl+U, Cmd+U) so you get helpful snapping hints.</p>

<h3>Three clicks you must know</h3>
<table>
<thead><tr><th>What you do</th><th>What you get</th></tr></thead>
<tbody>
<tr><td>Click, release, click elsewhere</td><td>A straight segment with corner points</td></tr>
<tr><td>Click and drag, then release</td><td>A smooth point with handles; dragging sets the curve direction</td></tr>
<tr><td>Click the first point again</td><td>Closes the path into a shape</td></tr>
</tbody>
</table>

<h3>Drawing a smooth curve</h3>
<ol>
<li>Click and drag from the left in a gentle rightward direction, then release. This is your first point and handle.</li>
<li>Move to the right. Click and drag again, this time in the direction the curve is travelling. A curved segment appears.</li>
<li>Repeat. Each time you drag in the direction of travel, the curve flows on smoothly.</li>
</ol>
<p>The golden rules are: put points at the ends of curves, drag handles about one third of the way to the next point, and use few points. Do not try to place a point on every bump.</p>

<h3>Changing direction smoothly: the corner trick</h3>
<p>If you want a curve then a sharp corner, click the last point again with <strong>Alt (Option)</strong> held. The outgoing handle disappears so the next segment can leave in a new direction. Then continue with a click or drag.</p>

<h3>Ending and joining</h3>
<ul>
<li>To finish an open path, press Enter or click another tool. You can also Ctrl-click (Cmd-click) on empty space with the Pen tool selected to deselect.</li>
<li>To close a shape, hover over the first point until a small circle appears on the Pen cursor, then click.</li>
<li>To join two open paths, select both end points with Direct Selection and use <strong>Object &gt; Path &gt; Join</strong> (Ctrl+J, Cmd+J).</li>
</ul>

<h3>Practising with a template</h3>
<p>The best practice is tracing. Place a simple picture, like a leaf, a heart or a letter, using <strong>File &gt; Place</strong>. Lower its opacity in the Properties panel, then lock it with Ctrl+2 (Cmd+2) so you cannot move it by accident. Draw over it on a new layer with the Pen tool. When you finish, delete or hide the picture. This builds control quickly.</p>

<h3>The Curvature tool and Pencil</h3>
<p>The <strong>Curvature</strong> tool (Shift+~) is a friendlier alternative: click points and Illustrator makes smooth curves between them, and double-click to make a corner. The <strong>Pencil</strong> tool (N) lets you draw freely, which is useful for hand-drawn looks, but results usually need clean-up. Inkscape's Pen tool (B) works in the same way as the Pen here, so you can practise anywhere.</p>

<h3>Common mistakes</h3>
<ul>
<li>Placing too many points. Redraw using fewer.</li>
<li>Dragging handles in the wrong direction, which creates loops. Undo and drag the other way.</li>
<li>Forgetting to deselect, so the next click continues the old path.</li>
</ul>

<blockquote><p><strong>Quick check:</strong> You click and drag a point with the Pen tool. What have you created?</p><p>Answer: A smooth anchor point with handles, because dragging pulls out the direction handles that shape the curve.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> How do you close a path you are drawing?</p><p>Answer: Click the first anchor point again, because that joins the end to the start and forms a closed shape.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Draw a simple heart picture, or place any simple graphic, on its own layer. Lower its opacity to 30 percent and lock it.</li>
<li>On a new layer, trace it with the Pen tool using as few points as you can.</li>
<li>Close the path, then set a red fill and no stroke.</li>
<li>Delete the template, count your points with the Direct Selection tool, and aim for fewer than ten. Save the heart for your portfolio.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Click makes corners; click-drag makes smooth curves; click the first point to close.</li>
<li>Place points at curve ends and keep the count low.</li>
<li>Alt (Option) on the last point changes direction with a corner.</li>
<li>Practise by tracing a template on a locked layer.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Shapes, Shape Builder and Pathfinder',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build complex artwork by combining simple shapes, using the Shape Builder tool and the Pathfinder panel. This matters because most professional logos and icons are made from circles, rectangles and triangles that are added and cut, which is faster and cleaner than drawing every curve by hand.</p>

<h3>Basic shapes</h3>
<p>The shape tools are grouped in the toolbar. Look for the Rectangle (M), Ellipse (L), Polygon, Star and Line Segment tools by name. Click and drag to draw, or click once on the artboard to type exact dimensions. Hold <strong>Shift</strong> for a perfect square or circle and <strong>Alt (Option)</strong> to draw from the centre. For polygons and stars, press the Up or Down arrow key while dragging to add or remove sides or points.</p>

<h3>The Pathfinder panel</h3>
<p>Open it with <strong>Window &gt; Pathfinder</strong> (Shift+Ctrl+F9; on Mac use Shift+Cmd+F9). Select two or more overlapping shapes, then click one of the Shape Modes or Pathfinders.</p>
<table>
<thead><tr><th>Button</th><th>Result</th><th>Example use</th></tr></thead>
<tbody>
<tr><td>Unite</td><td>Merges shapes into one outline</td><td>Cloud made from circles</td></tr>
<tr><td>Minus Front</td><td>Top shape cuts a hole from the shapes beneath</td><td>A ring from two circles</td></tr>
<tr><td>Intersect</td><td>Keeps only the overlapping area</td><td>A leaf shape from two overlapping circles</td></tr>
<tr><td>Exclude</td><td>Keeps all but the overlap</td><td>Interlocking pattern effects</td></tr>
<tr><td>Divide</td><td>Cuts shapes into separate pieces where they overlap</td><td>Cutting a design into coloured parts</td></tr>
</tbody>
</table>
<p>Hold Alt (Option) while clicking a Shape Mode to keep it as an editable, live compound shape. You can release it later from the Pathfinder menu.</p>

<h3>The Shape Builder tool</h3>
<p>Shift+M selects <strong>Shape Builder</strong>. Select all your overlapping shapes, then:</p>
<ol>
<li>Drag across several regions to merge them into one shape.</li>
<li>Hold Alt (Option) and click a region or segment to delete it.</li>
<li>Click once on a region to turn it into its own separate shape.</li>
</ol>
<p>This feels like cutting and gluing paper, and many designers prefer it to Pathfinder because you can see exactly what is happening.</p>

<h3>Worked example: a simple house icon for a real estate agent</h3>
<ol>
<li>Draw a square 100 by 100 px for the walls.</li>
<li>Draw a triangle (a Polygon with 3 sides) 130 px wide and place it on top for the roof; align them centred horizontally.</li>
<li>Draw a small rectangle 24 by 44 px for the door, aligned to the bottom centre of the walls.</li>
<li>Select all the shapes, Unite the walls and roof, then use Minus Front with the door on top to cut the door out.</li>
</ol>

<h3>Free alternative</h3>
<p>In Inkscape use the <strong>Path</strong> menu: Union, Difference, Intersection, Exclusion and Division. The Shape Builder tool also exists in current Inkscape versions, with the same drag-to-merge idea; look for it by name in the toolbox.</p>

<h3>Common mistakes</h3>
<ul>
<li>Forgetting to select all the shapes first.</li>
<li>Using Minus Front when the cutting shape is not at the front. Check the stacking order.</li>
<li>Unite on shapes that do not overlap, which leaves them as a compound shape and not a single outline.</li>
</ul>

<blockquote><p><strong>Quick check:</strong> You want a ring (doughnut) from two circles. Which Pathfinder button?</p><p>Answer: Minus Front, with the smaller circle in front, because the top shape cuts a hole from the one beneath it.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which tool lets you merge or delete overlapping regions by dragging or Alt-clicking on them?</p><p>Answer: The Shape Builder tool (Shift+M), because it works directly on the regions you can see.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Build the house icon exactly as in the worked example.</li>
<li>Fill the house dark blue and keep the door as an empty hole.</li>
<li>Create a second icon, a cloud, by overlapping four circles and a rectangle and using Unite (or Shape Builder).</li>
<li>Save both on separate artboards named House and Cloud.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Build complex shapes from simple ones.</li>
<li>Pathfinder gives exact operations: Unite, Minus Front, Intersect, Exclude, Divide.</li>
<li>Shape Builder (Shift+M) merges or removes regions visually.</li>
<li>Check stacking order before you cut.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Strokes, brushes and the Width tool',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to control the look of lines using the Stroke panel, apply brushes, make lines vary in thickness, and turn a stroke into a filled shape. This matters because line quality gives artwork its personality, and a stroke that cannot be converted properly can break when a logo is resized or sent to a printer.</p>

<h3>Fill and stroke</h3>
<p>Every path can have a <strong>fill</strong> (the colour inside) and a <strong>stroke</strong> (the line along the edge). In the toolbar near the bottom you will see two overlapping squares for fill and stroke; press X to swap which one is active and Shift+X to swap their colours. The small diagonal-line square sets either to none.</p>

<h3>The Stroke panel</h3>
<p>Open <strong>Window &gt; Stroke</strong> (Ctrl+F10, Cmd+F10). Key options:</p>
<table>
<thead><tr><th>Option</th><th>What it does</th><th>When to use it</th></tr></thead>
<tbody>
<tr><td>Weight</td><td>Thickness of the line</td><td>Thin 1 pt for fine detail; thick 8 pt for bold icons</td></tr>
<tr><td>Cap (Butt, Round, Projecting)</td><td>Shape of an open line's ends</td><td>Round caps for a friendly look</td></tr>
<tr><td>Corner (Miter, Round, Bevel)</td><td>Shape of corners</td><td>Round corners for friendly icons, miter for sharp ones</td></tr>
<tr><td>Align Stroke (centre, inside, outside)</td><td>Where the line sits against the edge of a closed path</td><td>Inside or outside keeps overall size predictable</td></tr>
<tr><td>Dashed Line</td><td>Makes dashes and gaps</td><td>Cutting guides, maps, stitching effects</td></tr>
<tr><td>Arrowheads</td><td>Adds ends such as arrows</td><td>Diagrams and maps</td></tr>
</tbody>
</table>

<h3>Brushes</h3>
<p>Open <strong>Window &gt; Brushes</strong> (F5). Select a path, then click a brush in the panel to apply it. Brush types include Calligraphic, Art, Scatter, Pattern and Bristle brushes; look for the menu at the bottom or the top right of the panel to open brush libraries. Draw freehand with the <strong>Paintbrush</strong> tool (B). In current versions you can double-click the Paintbrush tool to adjust its smoothness, which controls how many points it makes. A tablet pen helps, but a mouse works.</p>

<h3>The Width tool</h3>
<p>Press <strong>Shift+W</strong> for the <strong>Width</strong> tool. Hover over a stroke, then drag outwards from a point to make the line fatter or thinner there. This turns a flat line into a flowing, hand-lettered stroke. Save a favourite profile from the Stroke panel for later use.</p>

<h3>Outline Stroke: when a stroke becomes a shape</h3>
<p>Strokes scale with the Scale tool only if <em>Scale Strokes &amp; Effects</em> is on in the Preferences or Transform panel menu. To avoid surprises and to make shapes you can combine with Pathfinder, convert strokes to filled shapes using <strong>Object &gt; Path &gt; Outline Stroke</strong>. Do this once the line weight is final, because after that you can no longer change the weight.</p>

<h3>Mini-case</h3>
<p>Peter draws a swirl for a hair salon logo with the Paintbrush. He uses the Width tool to thicken the middle and taper both ends, then outlines the stroke. The printer can now enlarge the logo to a shop sign without the line weight changing in odd ways.</p>

<blockquote><p><strong>Quick check:</strong> You want a line to taper to a point at both ends. Which tool helps?</p><p>Answer: The Width tool (Shift+W), because it lets you change the thickness at points along the stroke.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why use Outline Stroke on a finished logo line?</p><p>Answer: It turns the line into a filled shape that scales exactly with the logo and can be combined in Pathfinder, so the look cannot change when resized.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Draw a wavy line with the Paintbrush tool (B), 6 pt, in teal.</li>
<li>Use the Width tool to make it thick in the middle and thin at the ends.</li>
<li>Apply Object &gt; Path &gt; Outline Stroke, then give it a gradient or a different fill colour.</li>
<li>Duplicate it three times with different colours to make a wave pattern and save as <code>wave-lines</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Fill is the inside, stroke is the line; manage stroke options in the Stroke panel.</li>
<li>Brushes and the Width tool give lines character.</li>
<li>Use Outline Stroke when line widths are final.</li>
<li>Round caps and corners give a friendly look; miter corners give a sharp look.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What do the handles on a smooth anchor point control?
- The colour of the segment
* The direction and tightness of the curve leaving and arriving at the point
- The layer the path sits on
- The thickness of the stroke
= Handles shape curves: their angle sets direction and their length sets how far the curve travels before bending.

Q: You draw a path with 20 anchor points and it looks lumpy. What is the best improvement?
- Add more anchor points
- Apply a thicker stroke to hide it
* Remove unnecessary points and adjust the handles on those that remain
- Convert every point to a corner point
= Fewer, well-placed points give smoother and easier-to-edit curves.

Q: With the Pen tool, you click and drag at a spot. What do you get?
- A corner point with no handles
- A text box
* A smooth point with handles that set the curve
- A closed path
= Dragging pulls out the handles; a simple click makes a corner point.

Q: How do you close a Pen tool path?
- Press the Delete key
* Click the first anchor point again
- Choose Object > Hide
- Double-click the last point twice only
= Clicking the starting point joins the end to the start so you can fill the shape.

Q: You want to build a doughnut from two circles. Which Pathfinder action with the smaller circle in front?
- Unite
- Intersect
* Minus Front
- Exclude never works with circles
= Minus Front lets the front shape cut a hole in the shape behind it.

Q: What does the Shape Builder tool let you do?
- Convert text to outlines
- Choose CMYK colours
* Merge or delete overlapping regions by dragging or Alt-clicking
- Export for screens
= Shape Builder works visually on the regions made by overlapping shapes.

Q: A designer finishes a logo with a 4 pt stroked swirl and wants it to scale perfectly on any sign. What should they do?
- Delete the stroke and leave the fill empty
- Rasterise it at a low resolution
* Use Outline Stroke to convert the line into a filled shape
- Change the stroke cap to Butt
= An outlined stroke becomes a shape, so its thickness scales with the logo and can be used with Pathfinder.

Q: Which tool makes a line fatter in the middle and thin at the ends?
* Width tool
- Zoom tool
- Artboard tool
- Eraser tool
= The Width tool adjusts stroke thickness at points along a path.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Colour, type and organisation',
            'summary' => 'Use swatches, global colours and gradients, understand RGB, CMYK and Pantone, work with type and outlines, and keep your files tidy with layers.',
            'lessons' => [
                [
                    'title'   => 'Colour: swatches, global colours and gradients',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a small colour palette in the Swatches panel, use global colours so one change updates the whole design, and apply gradients. This matters because brand colours must be exactly the same everywhere, and changing colours one object at a time is slow and risky.</p>

<h3>Applying colour</h3>
<p>Select an object, then click the <strong>Fill</strong> or <strong>Stroke</strong> box in the toolbar or Properties panel. You can pick a colour from the <strong>Swatches</strong> panel (<strong>Window &gt; Swatches</strong>), mix it in the <strong>Color</strong> panel (<strong>Window &gt; Color</strong>, F6), or copy one from another object using the <strong>Eyedropper</strong> tool (I). Press / (forward slash) to set the fill to none quickly.</p>

<h3>Creating swatches</h3>
<ol>
<li>Mix a colour in the Color panel, for example a deep green.</li>
<li>Click the <strong>New Swatch</strong> button at the bottom of the Swatches panel, or drag the fill box into the panel.</li>
<li>In the New Swatch dialog, give it a clear name such as <code>Brand green</code>.</li>
<li>Tick <strong>Global</strong> and click OK.</li>
</ol>

<h3>Why global colours matter</h3>
<p>A normal swatch is just a stored colour: changing it later does not change objects already coloured. A <strong>global swatch</strong> stays linked to everything coloured with it. Double-click the swatch, change its values, and every object using it updates at once. Global swatches also allow <em>tints</em> (a lighter percentage of the same colour) which stay tied to the main colour. In the Swatches panel, global swatches show a small white triangle in the corner.</p>

<table>
<thead><tr><th>Swatch type</th><th>Updates all objects when edited?</th><th>Good for</th></tr></thead>
<tbody>
<tr><td>Normal swatch</td><td>No</td><td>Quick one-off colours</td></tr>
<tr><td>Global swatch</td><td>Yes</td><td>Brand colours, any design that may change</td></tr>
<tr><td>Spot colour swatch</td><td>Yes (also prints as a special ink)</td><td>Professional printing with fixed brand inks</td></tr>
</tbody>
</table>

<h3>Gradients</h3>
<p>A gradient is a smooth blend between two or more colours. Select an object, then click the <strong>Gradient</strong> button in the Properties panel, or open <strong>Window &gt; Gradient</strong> (Ctrl+F9, Cmd+F9). Use the <strong>Gradient tool</strong> (G) to drag across the object and set the direction. In the panel:</p>
<ul>
<li>Choose <strong>Linear</strong>, <strong>Radial</strong> or <strong>Freeform</strong> type. Look for the three type buttons; names may vary slightly between versions.</li>
<li>Double-click a <strong>colour stop</strong> (the small square under the bar) to change its colour; drag stops to move them; click under the bar to add a stop.</li>
<li>Change the angle to rotate the blend.</li>
</ul>
<p>Use gradients with restraint. A subtle two-colour blend can make a button or sun look rich, but a gradient is harder to print in one colour and may not reproduce on a rubber stamp or embroidery. Always keep a flat-colour version of a logo.</p>

<h3>Recolour in bulk</h3>
<p>Choose <strong>Edit &gt; Edit Colors &gt; Recolor Artwork</strong>. In current versions this opens a panel where you can drag colours or choose a harmony, and every matching colour in the selection changes together. Use it to test alternative colour schemes in minutes.</p>

<h3>Worked example: a three-colour palette</h3>
<p>For a youth football club: Brand green (R 0, G 110, B 70), Gold (R 245, G 190, B 40) and Ink (R 25, G 25, B 30). Save all three as global swatches. Colour the shirt badge with them. When the committee decides on a lighter green, you edit the one swatch and the whole badge updates.</p>

<blockquote><p><strong>Quick check:</strong> Why make brand colours global swatches?</p><p>Answer: Editing the swatch once updates every object that uses it, so the brand stays consistent and changes are fast.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why keep a flat-colour version of a logo that uses a gradient?</p><p>Answer: Some uses, like stamps, embroidery and one-colour printing, cannot reproduce gradients, so a flat version is needed.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create three global swatches for an imaginary brand and name them clearly.</li>
<li>Draw a circle, a square and a triangle and colour each with one swatch.</li>
<li>Double-click one swatch and change its colour; watch the shape update.</li>
<li>Apply a two-stop gradient to the circle and screenshot your palette and shapes for your portfolio.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Store palette colours in the Swatches panel with clear names.</li>
<li>Global swatches update every object that uses them.</li>
<li>Use gradients carefully and keep a flat version for limited-colour uses.</li>
<li>Recolor Artwork tests alternatives quickly.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'RGB, CMYK and Pantone at concept level',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose between RGB, CMYK and spot colour for a job, explain why on-screen colours change in print, and convert a document properly. This matters because a bright green logo on screen can look dull on a printed T-shirt, and clients blame the designer if they were not warned.</p>

<h3>Two ways of making colour</h3>
<p><strong>RGB</strong> (red, green, blue) mixes light. Screens, phones and TVs glow, so they can show very bright, vivid colours. <strong>CMYK</strong> (cyan, magenta, yellow and key, which means black) mixes ink on paper. Paper reflects light, so it cannot match a glowing screen, and very vivid greens, blues and oranges look duller. This is why the same colour looks different in the two modes.</p>

<table>
<thead><tr><th>Mode</th><th>Used for</th><th>Colour range</th><th>Typical values</th></tr></thead>
<tbody>
<tr><td>RGB</td><td>Websites, social media, apps, video</td><td>Wider, brighter</td><td>0 to 255 per channel, or hex such as #00A651</td></tr>
<tr><td>CMYK</td><td>Offset and digital printing on paper</td><td>Narrower</td><td>0 to 100 percent per ink</td></tr>
<tr><td>Spot (for example Pantone)</td><td>Brand-critical printing, special inks</td><td>Fixed mixed ink</td><td>A named colour code from a swatch book</td></tr>
</tbody>
</table>

<h3>Spot colours and Pantone, in concept</h3>
<p>Instead of mixing four inks, a printer can use one pre-mixed ink matched to a standard. Pantone is the best-known system; each colour has a code and the printer uses a physical swatch book to check the match. Spot colours give consistent brand colours and are used for things like packaging and company stationery. They cost extra because each ink needs its own print run. As a beginner you only need to know they exist, how to pick one from <strong>Window &gt; Swatch Libraries &gt; Color Books</strong>, and when to ask the client or printer.</p>

<h3>Setting and converting the mode</h3>
<ol>
<li>Check the current mode in the title bar of the document window; it shows RGB or CMYK in brackets.</li>
<li>To change it, choose <strong>File &gt; Document Color Mode</strong> and then CMYK Color or RGB Color.</li>
<li>Check colours afterwards. Bright RGB colours will shift; re-adjust them in the Color panel until you are happy.</li>
</ol>
<p>If a colour swatch in the panel shows a small warning (such as an out-of-gamut sign in current versions) it cannot be printed exactly. Clicking the warning picks the nearest printable colour.</p>

<h3>Black</h3>
<p>For large black areas, printers sometimes want a "rich black" with some other inks added, but for small text and thin lines pure black (K 100 only) is best because it avoids tiny colour shifts. Ask the printer for their preference before final files.</p>

<h3>Common mistakes</h3>
<ul>
<li>Designing a print job in RGB and converting at the last minute.</li>
<li>Showing a client a vivid screen colour and promising that print will match exactly.</li>
<li>Using many spot colours when the budget allows only two inks.</li>
</ul>

<h3>Mini-case</h3>
<p>A café in Gulu orders printed cups. The designer chooses a bright lime RGB green for the logo. In the CMYK preview it turns olive. Rather than hide the problem, she tests the CMYK version, shows the client both, picks the brighter of the printable greens and asks the printer for a proof. The cups arrive as promised.</p>

<blockquote><p><strong>Quick check:</strong> Why does a bright screen green often look duller on paper?</p><p>Answer: Screens glow with RGB light and can show a wider range than CMYK inks on paper can reproduce.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A company needs its brand colour to match exactly on packaging, every time. What kind of colour do you suggest asking the printer about?</p><p>Answer: A spot colour such as a Pantone ink, because it is a fixed pre-mixed ink matched to a standard.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create an RGB document and make a rectangle in the brightest green you can.</li>
<li>Duplicate the document with <strong>File &gt; Save As</strong>, then choose File &gt; Document Color Mode &gt; CMYK Color.</li>
<li>Compare the two screens and note how much the green changes.</li>
<li>Write a three-line note you could send a client explaining the difference. Keep it for your portfolio.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>RGB is for screens; CMYK is for ink on paper; spot colours are special ink.</li>
<li>CMYK cannot match the brightest RGB colours.</li>
<li>Choose the mode at the start, and test the conversion early.</li>
<li>Be honest with clients about screen versus print colour.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Type in Illustrator, and outlining text',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to add and format text, set text on a path, and convert lettering to outlines before sending a logo to a printer or client. This matters because if you send a file with live text and the other person does not have your font, the letters change and a carefully designed logo falls apart.</p>

<h3>Adding text</h3>
<ul>
<li><strong>Point type:</strong> choose the <strong>Type tool</strong> (T), click once on the artboard and type. Best for logos and short headings.</li>
<li><strong>Area type:</strong> with the Type tool, drag a box and type inside it. Text wraps within the box, which suits paragraphs.</li>
<li><strong>Type on a path:</strong> choose the <strong>Type on a Path</strong> tool (it sits under the Type tool), click on a curved line and type. The text follows the curve, like words around a badge.</li>
</ul>
<p>Press Esc to leave text editing and select the whole text object.</p>

<h3>Formatting text</h3>
<p>Use <strong>Window &gt; Type &gt; Character</strong> (Ctrl+T, Cmd+T) for font, size, <em>kerning</em> (the space between two letters) and <em>tracking</em> (space across a group of letters), and <strong>Window &gt; Type &gt; Paragraph</strong> for alignment. Kerning matters most in logos. Place the cursor between two letters and press Alt+Left or Right arrow (Option on Mac) to adjust the gap in small steps.</p>

<table>
<thead><tr><th>Setting</th><th>What it changes</th><th>Logo tip</th></tr></thead>
<tbody>
<tr><td>Font and style</td><td>Shape of letters</td><td>Choose one clear font; avoid very thin ones that vanish when small</td></tr>
<tr><td>Size</td><td>Height of text</td><td>Judge by testing small, not just on a large screen</td></tr>
<tr><td>Kerning</td><td>Gap between a pair of letters</td><td>Fix odd gaps such as around A, V, T</td></tr>
<tr><td>Tracking</td><td>Overall letter spacing</td><td>Slightly open spacing helps small capital text</td></tr>
<tr><td>Leading</td><td>Space between lines</td><td>Keep tight but never touching</td></tr>
</tbody>
</table>

<h3>Choosing fonts safely</h3>
<p>Fonts have licences. Many free fonts from reputable libraries, such as Google Fonts, allow commercial use, but you must check the licence of any font you use in a client's logo. Fonts installed with Adobe subscriptions follow Adobe's terms; ask what is allowed for logos. If you cannot confirm a licence, do not use the font for paid work.</p>

<h3>Create Outlines</h3>
<ol>
<li>Select the text with the Selection tool (V).</li>
<li>Choose <strong>Type &gt; Create Outlines</strong> (Shift+Ctrl+O, Shift+Cmd+O).</li>
<li>The text becomes ordinary vector shapes grouped together. It is no longer editable as text, and it no longer needs the font.</li>
<li>Ungroup (Shift+Ctrl+G) and use the Direct Selection tool to customise letters, for example joining two letters or cutting a notch.</li>
</ol>
<p>Always <strong>keep a copy</strong> of the live text version on a hidden layer or in a separate file, so that you can change the spelling later. Outline only the delivery copy. In Inkscape the same step is <strong>Path &gt; Object to Path</strong>.</p>

<h3>Mini-case</h3>
<p>Ruth designs the name of a tailoring shop. The letters R and T have a wide gap, so she kerns them closer. She then creates outlines, cuts a small thread-like gap into the T with Shape Builder, and saves one file called <code>shop-logo-outlined.ai</code> and another called <code>shop-logo-live-text.ai</code>.</p>

<blockquote><p><strong>Quick check:</strong> Why outline text before sending a logo to a printer?</p><p>Answer: It turns letters into shapes so the printer's computer shows the correct design even without your font installed.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You outlined your text and now notice a spelling error. What should you have kept?</p><p>Answer: A copy with live text, because outlined letters cannot be edited as text.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Type a business name in point type, using a clear font in capitals, 72 pt.</li>
<li>Adjust the kerning on any uneven pair with Alt (Option) and arrow keys.</li>
<li>Save a copy with live text, then use Create Outlines and ungroup.</li>
<li>Customise one letter, for example add a small circle to replace a dot, then save both versions.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use point type for logos, area type for paragraphs, and type on a path for curved lettering.</li>
<li>Kerning corrects uneven gaps, especially in logos.</li>
<li>Check font licences before using a font in client work.</li>
<li>Create Outlines for delivery, and keep a live-text copy.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Layers, groups and keeping files organised',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use layers, sublayers and groups to keep artwork neat, lock and hide parts of a design, and use clipping masks. This matters because an organised file saves you from hunting through hundreds of loose shapes, and a client or colleague who opens your file later will thank you.</p>

<h3>The Layers panel</h3>
<p>Open <strong>Window &gt; Layers</strong> (F7). Each layer is like a clear sheet: things on higher layers appear in front. Useful buttons and icons:</p>
<ul>
<li><strong>Eye icon:</strong> shows or hides a layer.</li>
<li><strong>Lock icon (box beside the eye):</strong> stops you changing the layer by accident.</li>
<li><strong>Create New Layer</strong> button at the bottom, and <strong>Create New Sublayer</strong> next to it.</li>
<li><strong>Coloured square at the right:</strong> shows what is selected; drag it to move an object to another layer.</li>
<li>Double-click the layer name to rename it.</li>
</ul>

<h3>A sensible layer structure for a logo project</h3>
<table>
<thead><tr><th>Layer name</th><th>What it holds</th><th>State</th></tr></thead>
<tbody>
<tr><td>Guides</td><td>Grids, circles and lines used for construction</td><td>Locked, can be hidden</td></tr>
<tr><td>Text</td><td>Business name</td><td>Unlocked</td></tr>
<tr><td>Symbol</td><td>The icon part of the logo</td><td>Unlocked</td></tr>
<tr><td>Sketch</td><td>Scanned or placed hand sketch</td><td>Locked, 30 percent opacity</td></tr>
<tr><td>Background</td><td>Only when needed for tests</td><td>Hidden for export</td></tr>
</tbody>
</table>

<h3>Groups</h3>
<p>Select objects that belong together, then choose <strong>Object &gt; Group</strong> (Ctrl+G, Cmd+G). Move and scale the group as one. Ungroup with Shift+Ctrl+G (Shift+Cmd+G). Double-click a group to enter Isolation mode, edit inside it, then press Esc. Name important groups in the Layers panel so you can find them.</p>

<h3>Useful commands</h3>
<ul>
<li><strong>Lock</strong> selected objects: Ctrl+2 (Cmd+2). Unlock all: Alt+Ctrl+2 (Option+Cmd+2).</li>
<li><strong>Hide</strong> selected objects: Ctrl+3 (Cmd+3). Show all: Alt+Ctrl+3 (Option+Cmd+3).</li>
<li><strong>Paste in Front</strong>: Ctrl+F (Cmd+F) pastes a copy exactly on top of the original; <strong>Paste in Back</strong>: Ctrl+B (Cmd+B).</li>
</ul>

<h3>Clipping masks</h3>
<p>A clipping mask hides everything outside a shape. Draw a shape on top of the artwork, select both, and choose <strong>Object &gt; Clipping Mask &gt; Make</strong> (Ctrl+7, Cmd+7). The top shape works as a window. Release with Alt+Ctrl+7 (Option+Cmd+7). It is perfect for putting a pattern inside a circle or cropping a stripe that sticks out of a badge.</p>

<h3>Clean-up habits</h3>
<ul>
<li>Delete stray points and empty layers. Use <strong>Object &gt; Path &gt; Clean Up</strong> to remove stray points, unpainted objects and empty text paths.</li>
<li>Name layers, artboards and key swatches in plain words.</li>
<li>Keep unused experiments on a hidden layer or in another file, not scattered around the artboard.</li>
</ul>

<h3>Free alternative</h3>
<p>Inkscape has a Layers and Objects dialog and groups with Ctrl+G; clip with <strong>Object &gt; Clip &gt; Set</strong>.</p>

<blockquote><p><strong>Quick check:</strong> You traced a sketch and do not want to move it by accident. What do you do to its layer?</p><p>Answer: Lock the layer using the lock box beside the eye icon, because locked layers cannot be selected or moved.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You want to put a striped pattern inside a circle without any stripes outside. Which feature?</p><p>Answer: A clipping mask, with the circle on top, because it hides everything outside the top shape.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create layers named Guides, Text, Symbol and Sketch in a new document.</li>
<li>Draw a circle on Symbol and a word on Text, then lock Guides and Sketch.</li>
<li>Draw five coloured stripes, place a circle on top and make a clipping mask.</li>
<li>Take a screenshot of the Layers panel as proof of a well-organised file.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Layers stack, hide and lock parts of your design; groups keep related shapes together.</li>
<li>Use clear names for layers and artboards.</li>
<li>Clipping masks crop artwork to a shape.</li>
<li>Clean files are easier to edit, hand over and print.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A brand uses the same green on 40 objects. How can you update them all in one action later?
- Select each object and recolour it manually
* Make the green a global swatch and edit that swatch
- Group all the objects and change the stroke
- Save the file as a PDF
= Global swatches stay linked to the objects using them, so one edit updates everything.

Q: Why should you keep a flat-colour version of a logo that normally uses a gradient?
- Gradients are illegal in print
* Some uses, such as stamps, embroidery and one-colour printing, cannot reproduce gradients
- Flat colours take less time to load in Illustrator only
- Gradients cannot be exported
= The logo must work wherever it is used, including simple limited-colour reproduction.

Q: Which statement about RGB and CMYK is correct?
- RGB is for print and CMYK is for screens
* RGB mixes light for screens; CMYK mixes ink for printing and cannot match the brightest RGB colours
- They always look identical
- CMYK has a wider colour range than RGB
= Screens glow and can show brighter colours than ink on paper.

Q: A company needs its exact brand colour reproduced reliably on packaging. What is a suitable idea to discuss with the printer?
- Use any RGB colour from the screen
- Convert everything to grayscale
* A spot colour such as a Pantone ink
- Use a gradient to approximate it
= Spot colours are pre-mixed inks that match a standard, giving consistent results.

Q: Why create outlines of the text in a logo before sending it to a printer?
- To make the text editable
* To turn letters into shapes so the correct design shows even if the printer lacks your font
- To make the file smaller than a PNG
- To change the text to CMYK automatically
= Outlined text no longer depends on installed fonts.

Q: After outlining text you notice a spelling mistake. What should you have kept?
- Nothing; you can retype it as text
- A raster copy
* A copy of the file with live, editable text
- A second artboard with a gradient
= Outlined letters are shapes, so you need the live-text version to fix spelling.

Q: What is the best tool for putting a pattern inside a circle so it does not spill outside?
- Direct Selection
* A clipping mask with the circle on top
- The Width tool
- Align to Artboard
= A clipping mask hides everything outside the top shape.

Q: What is kerning?
- The thickness of a stroke
- The space between lines of text
* The space between a specific pair of letters
- A font licence type
= Kerning corrects uneven gaps between letter pairs, which is especially important in logos.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Logos and icons',
            'summary' => 'Follow a professional logo process from sketch to vector, trace and clean up images, design icons on a grid, and test a logo before delivery.',
            'lessons' => [
                [
                    'title'   => 'The logo design process: sketch, concept, vectorise, refine',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to follow a clear process to design a logo, from a client brief to a vector design, instead of opening the computer and hoping for inspiration. This matters because a process saves time, produces better ideas and gives you something to explain to the client when they ask why you chose a design.</p>

<h3>Step 1: The brief</h3>
<p>Ask the client short questions and write the answers down.</p>
<ul>
<li>What does the business do, and for whom?</li>
<li>Three words that describe the personality (for example trusted, young, friendly).</li>
<li>Where will the logo appear: signboard, receipts, social media, uniforms, stamps?</li>
<li>Colours they like or must avoid, and competitors they do not want to look like.</li>
<li>Budget, deadline and how many changes are included.</li>
</ul>

<h3>Step 2: Research and sketch</h3>
<p>Look at how similar businesses in your area present themselves, and note what is common so that you can be different. Then <strong>sketch on paper first</strong>. Draw at least twenty small thumbnails in ten minutes, without erasing. Ideas appear faster by hand, and bad ideas cost nothing. Circle your best three.</p>

<h3>Step 3: Choose a concept</h3>
<p>Types of logo to consider:</p>
<table>
<thead><tr><th>Type</th><th>What it is</th><th>Works well when</th></tr></thead>
<tbody>
<tr><td>Wordmark</td><td>The name in custom lettering</td><td>The name is short and distinctive</td></tr>
<tr><td>Symbol or icon</td><td>A picture mark with the name beside it</td><td>You want a memorable shape for stamps and avatars</td></tr>
<tr><td>Monogram</td><td>Initials combined</td><td>The name is long</td></tr>
<tr><td>Emblem or badge</td><td>Text inside a shape</td><td>Schools, clubs, craft products</td></tr>
</tbody>
</table>

<h3>Step 4: Vectorise</h3>
<ol>
<li>Scan or photograph the sketch and place it with <strong>File &gt; Place</strong>, then lock it on its own layer at about 30 percent opacity.</li>
<li>Rebuild the idea with simple shapes (circles, rectangles) and the Pen tool, using the least points that work.</li>
<li>Use the Align and Transform panels to keep shapes precise.</li>
<li>Start in black only. Colour comes later, so that the shape works first.</li>
</ol>

<h3>Step 5: Refine</h3>
<ul>
<li>Check curves are smooth and joins meet cleanly.</li>
<li>Fix spacing between letters (kerning) and between the symbol and the text.</li>
<li>Remove anything that does not help. Ask: if I delete this, is the logo worse?</li>
<li>Add colour from your global swatches, then test the logo (next lessons).</li>
</ul>

<h3>Mini-case: Kato Fresh Foods</h3>
<p>The brief says: a vegetable seller in Masaka, friendly and fresh, to appear on bags and a signboard. The designer sketches a leaf, a sun and a basket. She picks the leaf and combines it with a letter K: the stem of the K becomes a leaf stalk. She rebuilds it from two circles and a Pathfinder cut, sets the name in a rounded font, kerns it and presents it in black first, then in two greens.</p>

<h3>Avoid common traps</h3>
<ul>
<li>Do not copy other logos or use clip art that others use. Check copyright (covered later).</li>
<li>Do not use trendy effects such as shadows that will disappear on a stamp.</li>
<li>Do not show ten options. Show two or three strong ones with reasons.</li>
</ul>

<h3>Free alternative</h3>
<p>Every step works in Inkscape too: place the sketch with File &gt; Import, use the Pen and Node tools, and Path menu operations.</p>

<blockquote><p><strong>Quick check:</strong> Why sketch on paper before opening the computer?</p><p>Answer: Hand sketching produces many ideas quickly and cheaply, and avoids getting stuck polishing a weak idea.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why start the vector version in black only?</p><p>Answer: If the shape works in one colour it will work in many; colour can hide a weak design.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Invent a small business, such as a boda boda repair shop, and write a six-line brief for it.</li>
<li>Sketch twenty thumbnail ideas on paper and photograph the best one.</li>
<li>Place it in Illustrator on a locked layer and rebuild it with simple shapes in black.</li>
<li>Export the black logo as a PNG and keep the sketch photo with it as a before and after set for your portfolio.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Follow the order: brief, research, sketch, concept, vectorise, refine, test.</li>
<li>Start with paper sketches and black shapes.</li>
<li>Choose a logo type that suits the name and where it will be used.</li>
<li>Present two or three strong options, with reasons.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Image Trace and cleaning up traced artwork',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to turn a scanned drawing or a low-quality logo picture into vector shapes with Image Trace, and clean the result so it is usable. This matters because many clients arrive with only a photo of a hand-painted sign or a blurry logo, and tracing can be a fast first step to rebuild it.</p>

<h3>What tracing does</h3>
<p>Image Trace looks at a raster picture and builds vector paths around the shapes and colours it finds. It works best on pictures with strong contrast: black ink drawings, clean scans, simple logos. It works poorly on photographs and blurry images, which produce messy, heavy files.</p>

<h3>The basic method</h3>
<ol>
<li>Place the image with <strong>File &gt; Place</strong>, then click <strong>Place</strong>.</li>
<li>Select the image, then open <strong>Window &gt; Image Trace</strong>. In current versions the control bar also shows an Image Trace button with presets.</li>
<li>Tick <strong>Preview</strong>, and choose a preset such as <strong>Black and White Logo</strong>, <strong>Sketched Art</strong> or <strong>Silhouettes</strong>. Look at the result before you commit.</li>
<li>Adjust the <strong>Threshold</strong> slider. A higher threshold makes more of the image black, a lower one makes less. Pick the value where details such as thin letters are still readable.</li>
<li>Open the advanced options, and try <strong>Paths</strong>, <strong>Corners</strong> and <strong>Noise</strong>. Fewer paths and higher noise values give simpler shapes; enough corners keep sharp edges.</li>
<li>Click <strong>Expand</strong> in the control bar (or <strong>Object &gt; Image Trace &gt; Expand</strong>) to turn the trace into real, editable paths.</li>
</ol>

<h3>Settings guide</h3>
<table>
<thead><tr><th>Source picture</th><th>Good preset</th><th>Note</th></tr></thead>
<tbody>
<tr><td>Black ink sketch on white paper</td><td>Sketched Art or Black and White Logo</td><td>Adjust threshold until lines are clean</td></tr>
<tr><td>Simple logo with 2 or 3 flat colours</td><td>3 Colors or a limited colour setting</td><td>Keeps colours separate</td></tr>
<tr><td>Photograph</td><td>Not recommended; use High Fidelity Photo only for experiments</td><td>Produces huge, messy files</td></tr>
<tr><td>Blurry or tiny logo</td><td>Black and White Logo, then rebuild by hand</td><td>Use trace as a guide, not final</td></tr>
</tbody>
</table>
<p>Preset names can change in different versions, so choose the nearest match and test.</p>

<h3>Cleaning up</h3>
<ul>
<li><strong>Remove the white box.</strong> After expanding, the white background is a shape. Use the Direct Selection tool to click it and press Delete.</li>
<li><strong>Ungroup</strong> (Shift+Ctrl+G) and delete tiny specks.</li>
<li><strong>Simplify</strong> with <strong>Object &gt; Path &gt; Simplify</strong>. It reduces anchor points while keeping the shape. Check carefully that it has not rounded your corners.</li>
<li><strong>Join broken lines</strong> with <strong>Object &gt; Path &gt; Join</strong> (Ctrl+J, Cmd+J).</li>
<li>Compare with the original by toggling visibility, and redraw bad parts with the Pen tool.</li>
</ul>

<h3>Hand-drawn lettering</h3>
<p>For a hand-lettered sign, trace and then adjust curves with the Direct Selection tool. Keep the charm of the hand, but straighten wobbly edges that look like mistakes instead of style.</p>

<h3>Mini-case</h3>
<p>Joseph has a photograph of a hand-painted mural logo on a shop wall. He crops and straightens it, increases contrast, traces with a Black and White Logo preset, expands, deletes the white box and cleans the edges. He then rebuilds the lettering by hand because the trace was too rough, using the traced version as a guide layer.</p>

<h3>Free alternative</h3>
<p>Inkscape has <strong>Path &gt; Trace Bitmap</strong> with similar controls, including a threshold, which gives you the same practice for free.</p>

<blockquote><p><strong>Quick check:</strong> Which kind of image traces best?</p><p>Answer: A high-contrast, clean image such as a black ink drawing, because the software can find clear edges.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> After Image Trace, why click Expand?</p><p>Answer: Expand turns the trace result into real vector paths you can edit with the Direct Selection tool and Pathfinder.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Draw a simple shape or a word in black pen on white paper and photograph or scan it in good light.</li>
<li>Place it in Illustrator and trace it with a logo or sketch preset; adjust the threshold.</li>
<li>Expand, remove the white background, and use Simplify.</li>
<li>Save the clean traced artwork as <code>traced-sketch.svg</code> and note how many points it has before and after Simplify.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Image Trace works best with clean, high-contrast sources.</li>
<li>Use Preview, a preset and the Threshold slider, then Expand.</li>
<li>Clean up: delete the background, remove specks, simplify and join.</li>
<li>For poor sources, rebuild by hand using the trace as a guide.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Icon design on a grid',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a consistent set of simple icons using a pixel grid, keylines and shared stroke rules. This matters because an app or website with icons that differ in thickness, size and style looks careless, while a matching set looks professional and is easy to understand.</p>

<h3>What a good icon is</h3>
<p>An icon is a very small picture that must be understood in a second. It uses simple shapes, a few details, and the same visual rules as its siblings. Common sizes are 16, 24, 32 and 48 pixels.</p>

<h3>Setting up a grid</h3>
<ol>
<li>Create a new Web document and one artboard of 48 by 48 px.</li>
<li>Open <strong>Edit &gt; Preferences &gt; Guides &amp; Grid</strong> (on Mac, <strong>Illustrator &gt; Settings &gt; Guides &amp; Grid</strong> in current versions) and set <strong>Gridline every</strong> to 4 px and <strong>Subdivisions</strong> to 4.</li>
<li>Show the grid with <strong>View &gt; Show Grid</strong> (Ctrl+") and snap to it with <strong>View &gt; Snap to Grid</strong> (Shift+Ctrl+").</li>
<li>Turn on <strong>View &gt; Pixel Preview</strong> to see how pixels will look when exported.</li>
</ol>
<p>Leave a margin of about 4 px on every side of the artboard as a safe area. Keep all the main shapes inside it.</p>

<h3>Keylines: a shared set of shapes</h3>
<table>
<thead><tr><th>Keyline shape</th><th>Size on a 48 px artboard</th><th>Use for</th></tr></thead>
<tbody>
<tr><td>Circle</td><td>40 px diameter</td><td>Round objects such as a clock or a coin</td></tr>
<tr><td>Square</td><td>36 by 36 px</td><td>Boxes, documents</td></tr>
<tr><td>Portrait rectangle</td><td>32 by 40 px</td><td>Phones, doors, pages</td></tr>
<tr><td>Landscape rectangle</td><td>40 by 32 px</td><td>Screens, envelopes, photos</td></tr>
</tbody>
</table>
<p>These sizes are an example scheme. Choose your own and use it the same way across the whole set. A circle looks smaller than a square of the same width, so it is made slightly larger.</p>

<h3>Rules for a consistent set</h3>
<ul>
<li><strong>One stroke weight</strong> (for example 4 px) for every outline icon, with the same cap and corner styles.</li>
<li><strong>One corner style</strong>: all round or all sharp.</li>
<li><strong>Same level of detail</strong>: do not draw one icon with five details and another with one.</li>
<li><strong>Align to whole pixels</strong> so edges are crisp when exported at 1x. Pixel Preview shows blurry half pixels.</li>
</ul>

<h3>Worked example: a mobile money icon</h3>
<ol>
<li>Draw a rounded rectangle 32 by 40 px for the phone and centre it on the artboard.</li>
<li>Give it a 4 px stroke, no fill, round corners.</li>
<li>Draw a 12 px circle in the upper part of the screen with a 4 px stroke for a coin.</li>
<li>Add a short line near the bottom for a button. Use the Align panel to centre everything horizontally.</li>
<li>Select all and choose Object &gt; Path &gt; Outline Stroke if the set will be delivered as filled shapes.</li>
</ol>

<h3>Test at the real size</h3>
<p>Zoom to 100 percent (Ctrl+1, Cmd+1) and look at the icon at the size it will really appear. If a detail disappears, remove it. If two icons look like they come from different families, adjust the stroke or the corner style.</p>

<h3>Free alternative</h3>
<p>Inkscape lets you set a grid in <strong>Document Properties</strong> and snap to it. Browser vector editors often offer grid and snap options too; look in their View menu.</p>

<blockquote><p><strong>Quick check:</strong> What is the main reason all icons in a set should use the same stroke weight?</p><p>Answer: It makes them look like one family; mixed thicknesses look careless and are harder to read together.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why turn on Pixel Preview while designing a 24 px icon?</p><p>Answer: It shows how the edges will look on pixels, so you can align shapes to whole pixels and avoid blur.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a 48 by 48 px artboard with a 4 px grid and snapping on.</li>
<li>Design three icons in the same style, such as a phone, a house and a heart, using a 4 px stroke and round corners.</li>
<li>Copy them onto one wider artboard and check spacing with Distribute.</li>
<li>Export the set as SVG and save the three icons in your portfolio.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Work on a pixel grid with snapping and Pixel Preview.</li>
<li>Use keyline shapes and a safe margin.</li>
<li>Keep stroke weight, corners and detail consistent across the set.</li>
<li>Always judge icons at their real size.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Testing a logo: small, one colour and on backgrounds',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to test a logo against the real conditions it will meet and prepare the variations clients need. This matters because a logo that looks wonderful large on your screen may fall apart on a 2 cm stamp, a dark T-shirt or a social media profile circle.</p>

<h3>The standard tests</h3>
<table>
<thead><tr><th>Test</th><th>How to do it</th><th>What to look for</th></tr></thead>
<tbody>
<tr><td>Small size</td><td>Scale a copy to about 2 cm (about 75 px) wide</td><td>Details should still be readable; thin lines should not disappear</td></tr>
<tr><td>One colour</td><td>Make a copy and set every fill to black</td><td>The shape alone should still be recognisable</td></tr>
<tr><td>Reversed</td><td>Place a white version on a dark background</td><td>Contrast stays high; no stray dark edges</td></tr>
<tr><td>Busy background</td><td>Place it on a photo</td><td>Is there a clear space or a background shape for safety?</td></tr>
<tr><td>Squint test</td><td>Look from a distance or squint at it</td><td>The overall shape should still stand out</td></tr>
<tr><td>Circle crop</td><td>Place inside a circle 400 px wide</td><td>Nothing important is cut off in a profile picture</td></tr>
</tbody>
</table>

<h3>Versions to create</h3>
<ol>
<li><strong>Primary:</strong> full colour with symbol and name.</li>
<li><strong>Horizontal and stacked:</strong> two layouts for wide and tall spaces.</li>
<li><strong>Icon only:</strong> the symbol alone for avatars and favicons.</li>
<li><strong>One colour, black:</strong> for stamps, newspapers and forms.</li>
<li><strong>Reversed, white:</strong> for dark photos and backgrounds.</li>
</ol>
<p>Place each on its own artboard in one file, and name the artboards clearly. Creating a one-colour version: select all, then set fill to black. If the design uses overlap or transparency, use Pathfinder to unite shapes so that no parts hide each other.</p>

<h3>Clear space</h3>
<p>Clear space is an empty margin around the logo that nothing else may enter. A simple rule is to use the height of a letter or a key shape from the logo as the margin all around. Draw it on a layer and mention it on the brand sheet.</p>

<h3>Minimum size</h3>
<p>Test and record the smallest size at which the logo remains legible, for example 20 mm wide in print or 80 px on screen. Tell the client not to go below it.</p>

<h3>Mini-case</h3>
<p>Sarah designs a bakery logo with a thin wheat ear and a script name. At 2 cm the thin lines vanish and the script fills in. She thickens the wheat strokes, switches to a bolder rounded font for the name and creates a stacked version with a larger icon. The simplified logo then works on bread bags, receipts and the shop's WhatsApp profile.</p>

<h3>Free alternative</h3>
<p>In Inkscape or a browser editor, do the same tests by duplicating the logo, scaling copies and setting fills to black and white; the tests need no special feature.</p>

<blockquote><p><strong>Quick check:</strong> A logo has delicate hairline details that vanish when printed small. What do you do?</p><p>Answer: Thicken or remove the thin details and test again, because the logo must stay readable at its smallest size.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why make a black one-colour version?</p><p>Answer: Stamps, forms, newspapers and engraving may allow only one colour, and the mark must still work there.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Take your logo from the earlier lesson and create artboards for Primary, Icon, One colour and Reversed.</li>
<li>Scale a copy to 75 px wide and take a screenshot at 100 percent zoom.</li>
<li>Place the reversed white version on a dark navy rectangle and check contrast.</li>
<li>Write the minimum size you recommend and add it to a note for your portfolio.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Test small, one-colour, reversed, on busy backgrounds and cropped in a circle.</li>
<li>Deliver primary, horizontal, stacked, icon, black and reversed versions.</li>
<li>Define clear space and a minimum size.</li>
<li>Simplify the design where a test fails.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which step comes first when a new client asks for a logo?
- Open the computer and start drawing shapes
* Gather a brief: what the business does, its personality, where the logo will appear, budget and deadline
- Choose the colours
- Export the final files
= A brief gives you direction and avoids wasted work.

Q: Why does the lesson recommend starting the vector logo in black only?
- Black prints faster
- Clients dislike colour
* If the shape works without colour, it will work in many colours; colour can hide a weak shape
- Illustrator cannot use colour at the start
= The structure of the design should stand on its own.

Q: A client brings a clear black ink drawing on white paper. Which tool is a good first step to vectorise it?
- Gradient tool
* Image Trace, then Expand and clean up
- Warp effect
- Convert to CMYK
= High-contrast drawings trace well; expanding gives editable paths.

Q: You traced a scan and it contains a white rectangle behind the artwork. What do you do?
- Leave it; white is invisible everywhere
* Select the white shape with the Direct Selection tool and delete it
- Increase the stroke weight
- Apply a gradient
= The white background becomes a shape on a white page and causes problems on coloured backgrounds.

Q: A tracing of a photograph produces a heavy, messy file. What is the best advice?
- Always use High Fidelity settings for logos
- Add more anchor points
* Do not trace photographs for logos; trace clean artwork or rebuild by hand
- Convert to RGB
= Image Trace is built for high-contrast artwork; photographs make messy results.

Q: You are designing a set of 24 px icons. What helps most to keep them consistent?
- Different stroke weights to add variety
* One stroke weight, one corner style, same level of detail and a shared grid
- Using a different canvas size for each icon
- Gradients on every icon
= Consistent rules make icons look like a family.

Q: Why use Pixel Preview for small icons?
* It shows how edges will look on pixels so you can align to whole pixels and avoid blur
- It converts the file to CMYK
- It adds bleed
- It outlines text
= Half-pixel edges look blurry when exported at small sizes.

Q: A logo's thin lines vanish when it is printed 2 cm wide. What do you do?
- Tell the client to always print it large
- Hide the thin lines
* Thicken or simplify the details and test again at small size
- Add a drop shadow
= The logo must stay legible at its smallest planned size.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Illustration, patterns and mockups',
            'summary' => 'Build flat illustrations from shapes, repeat artwork with symbols and patterns, and present designs on simple mockups.',
            'lessons' => [
                [
                    'title'   => 'Flat illustration from simple shapes',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to plan and draw a simple flat illustration using basic shapes, a limited palette and a clear structure. This matters because flat vector illustration is popular for websites, posters and NGO reports, and you can make it without being a natural artist.</p>

<h3>What flat illustration is</h3>
<p>Flat illustration uses solid colours, simple shapes and little or no realistic shading. Think of a friendly village scene, a market stall or a person using a phone. It reads quickly and reproduces well at any size, which is why organisations use it for health campaigns, training manuals and mobile apps.</p>

<h3>Plan before you draw</h3>
<ol>
<li><strong>Choose one idea</strong>, for example "a girl studying with a solar lamp".</li>
<li><strong>Thumbnail sketch</strong> the layout on paper: foreground, middle ground, background.</li>
<li><strong>Pick a palette</strong> of 4 to 6 colours and save them as global swatches, including a light, a dark and one accent.</li>
<li><strong>Break the picture into shapes</strong>: a head is a circle, a body is a rounded rectangle, a table is a rectangle.</li>
</ol>

<h3>Build in layers</h3>
<table>
<thead><tr><th>Layer (back to front)</th><th>Contents</th><th>Tip</th></tr></thead>
<tbody>
<tr><td>Background</td><td>Sky, wall or ground shapes</td><td>Use the lightest colours</td></tr>
<tr><td>Middle</td><td>Furniture, trees, buildings</td><td>Slightly stronger colours</td></tr>
<tr><td>Characters</td><td>People and animals</td><td>Highest contrast against the background</td></tr>
<tr><td>Details</td><td>Faces, folds, small objects</td><td>Add last and keep simple</td></tr>
</tbody>
</table>

<h3>Techniques that save time</h3>
<ul>
<li><strong>Shape Builder and Pathfinder</strong> to cut hair, hills and clouds out of circles.</li>
<li><strong>Copy and reuse</strong> with Alt (Option) drag, and Ctrl+D to repeat.</li>
<li><strong>Reflect</strong> (O) to make a symmetrical object from one half, then Unite.</li>
<li><strong>Clipping masks</strong> (Ctrl+7, Cmd+7) to keep patterns on a shirt inside the body shape.</li>
<li><strong>Shading with a darker shape</strong>: duplicate a shape with Ctrl+C then Ctrl+F (Paste in Front), set a darker colour and use Minus Front to leave only a crescent shadow.</li>
<li><strong>Eyedropper</strong> (I) to reuse colours, though sampling from a swatch is neater.</li>
</ul>

<h3>Good style rules</h3>
<ul>
<li>Use one level of detail throughout the picture.</li>
<li>Keep corner treatment consistent: all rounded, or all sharp.</li>
<li>Leave breathing space. Do not fill every corner.</li>
<li>Represent people respectfully and with variety; avoid stereotypes.</li>
</ul>

<h3>Mini-case</h3>
<p>A health NGO in Kasese needs a poster picture on handwashing. The designer uses a palette of two blues, a warm yellow and a deep green. She draws a tap from rectangles, water drops as teardrop shapes, and a child with a circle head and a rounded body. Because it is vector, the same picture prints on an A2 poster and appears on a phone screen for the NGO's WhatsApp.</p>

<h3>Free alternative</h3>
<p>Inkscape's shape tools, Path menu and layers allow exactly the same method. Many browser editors also have shape and boolean tools.</p>

<blockquote><p><strong>Quick check:</strong> You want a crescent-shaped shadow on a circle. How can you make it from shapes?</p><p>Answer: Copy the circle, shift the copy, and use Minus Front, because cutting one circle from another leaves a crescent.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why choose a palette of only a few colours before drawing?</p><p>Answer: A limited palette keeps the picture harmonious, speeds up decisions and makes later changes easy through global swatches.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a simple scene, such as a market stall with an umbrella, and sketch it on paper.</li>
<li>Create a 5-colour global palette and set up four layers: Background, Middle, Characters, Details.</li>
<li>Build the scene using only circles, rectangles and triangles plus Pathfinder cuts.</li>
<li>Export it as SVG and PNG and save it as portfolio piece number one.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Plan: idea, thumbnail, palette, shapes.</li>
<li>Build from back to front on named layers.</li>
<li>Use Pathfinder, reflect, clipping masks and paste in front for efficient detail.</li>
<li>Keep style, detail and colours consistent.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Symbols, patterns and repeating artwork',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to reuse artwork with symbols, build a seamless repeating pattern, and apply it to shapes. This matters because patterns decorate packaging, fabric designs, backgrounds and wrapping paper, and symbols let you update many copies of an object at once.</p>

<h3>Symbols</h3>
<p>A symbol is a stored piece of artwork you can place many times. Each copy is called an instance. Edit the symbol and every instance updates. Open <strong>Window &gt; Symbols</strong> (Shift+Ctrl+F11; Shift+Cmd+F11 on Mac).</p>
<ol>
<li>Select your artwork, for example a simple tree.</li>
<li>Drag it into the Symbols panel, or click the <strong>New Symbol</strong> button. Name it and choose Dynamic Symbol if you want to edit instances separately; in current versions a dynamic symbol lets you change the colour of one instance.</li>
<li>Place copies by dragging from the panel, or with the Symbol Sprayer tool in the toolbar group, to scatter many instances quickly.</li>
<li>To change all of them, double-click the symbol in the panel, edit it, then press Esc.</li>
</ol>
<p>Use symbols for repeated items such as trees on a map, seats on a plan, or flags in a graph.</p>

<h3>Seamless patterns</h3>
<p>A seamless pattern repeats in all directions without visible joins. Illustrator has a dedicated mode.</p>
<ol>
<li>Draw your motif, for example small circles and leaves, and select it.</li>
<li>Choose <strong>Object &gt; Pattern &gt; Make</strong>. Illustrator opens Pattern Editing mode and the <strong>Pattern Options</strong> panel.</li>
<li>Name the pattern. Choose <strong>Tile Type</strong>: Grid, Brick by Row, Brick by Column or Hex by Column. Try them and watch the preview.</li>
<li>Set <strong>Width</strong> and <strong>Height</strong> of the tile and increase <strong>Copies</strong> (for example 5 by 5) to preview the repetition.</li>
<li>Drag the tile edges so that shapes crossing the edge join smoothly. Adjust <strong>Overlap</strong> if parts cover each other.</li>
<li>Click <strong>Done</strong> at the top of the window. The pattern appears in the Swatches panel.</li>
</ol>
<p>Now draw a rectangle and click the pattern swatch to fill it. To scale the pattern without scaling the shape, use the Scale tool (double-click it), untick <strong>Transform Objects</strong> and tick <strong>Transform Patterns</strong>.</p>

<h3>Pattern design tips</h3>
<table>
<thead><tr><th>Tip</th><th>Why it helps</th></tr></thead>
<tbody>
<tr><td>Vary the size of motifs</td><td>Avoids a flat, mechanical look</td></tr>
<tr><td>Rotate some motifs</td><td>Looks natural, like a printed fabric</td></tr>
<tr><td>Leave even space between motifs</td><td>Avoids bare patches and clumps</td></tr>
<tr><td>Use 3 to 5 colours</td><td>Easier to print and easier on the eye</td></tr>
<tr><td>Test the repeat on a large rectangle</td><td>Reveals lines and patches that show up when tiled</td></tr>
</tbody>
</table>

<h3>African print inspiration, used respectfully</h3>
<p>Many learners enjoy making patterns inspired by local textiles such as kitenge or bark cloth. Create your own original motifs from simple shapes and do not copy a specific fabric designer's pattern, because that may break copyright. Where you draw on a traditional design, learn its meaning and credit the culture.</p>

<h3>Mini-case</h3>
<p>Esther makes a pattern for a school tote bag. Her motif is a stylised pencil and book repeated in a brick layout in the school's colours. She tests the repeat on a large rectangle, sees a gap at the top-right, nudges the tile, and then saves the pattern as a swatch to use on the notebook cover too.</p>

<h3>Free alternative</h3>
<p>Inkscape has <strong>Object &gt; Pattern &gt; Objects to Pattern</strong> and the Clones dialog for repeats. The tools differ, but the idea of a repeating tile is the same.</p>

<blockquote><p><strong>Quick check:</strong> You edited a symbol and all its copies changed. Why?</p><p>Answer: Each copy is an instance linked to the symbol, so editing the symbol updates all instances.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Where does a finished pattern appear so you can fill shapes with it?</p><p>Answer: In the Swatches panel, because Done saves the pattern as a swatch.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Draw three simple motifs, for example a circle, a leaf and a diamond, in three colours.</li>
<li>Choose Object &gt; Pattern &gt; Make, set a Brick by Row tile and press Done.</li>
<li>Fill a large rectangle with the pattern, then use Transform Patterns to make the tiles smaller.</li>
<li>Export the rectangle as PNG and keep it as a portfolio background.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Symbols reuse artwork; editing the symbol updates every instance.</li>
<li>Object &gt; Pattern &gt; Make creates seamless repeats saved as swatches.</li>
<li>Test the repeat on a large area before delivering.</li>
<li>Make original motifs and respect other designers' work.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Effects, transparency and simple mockups',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to add soft effects like shadows, use opacity and blending, and present a logo on a simple mockup of a T-shirt, mug or business card. This matters because clients decide faster when they see a logo in context, and a mockup helps them imagine the finished product.</p>

<h3>The Appearance panel</h3>
<p>Open <strong>Window &gt; Appearance</strong> (Shift+F6). It lists everything that affects how a selected object looks: fills, strokes, opacity and effects. You can add several fills or strokes to one object, which is useful for outlined text without extra shapes. Click an item to edit it, drag to reorder, and use the <strong>Add New Effect</strong> button (fx) at the bottom.</p>

<h3>Effects</h3>
<p>Effects change the look without changing the underlying path. Open the <strong>Effect</strong> menu and look for these by name:</p>
<table>
<thead><tr><th>Effect</th><th>Where to find it</th><th>Use</th></tr></thead>
<tbody>
<tr><td>Drop Shadow</td><td>Effect &gt; Stylize &gt; Drop Shadow</td><td>Soft lift under a button or card; keep it subtle</td></tr>
<tr><td>Round Corners</td><td>Effect &gt; Stylize &gt; Round Corners</td><td>Live rounded corners on any shape</td></tr>
<tr><td>Offset Path</td><td>Effect &gt; Path &gt; Offset Path</td><td>Outline around a shape for stickers</td></tr>
<tr><td>Warp</td><td>Effect &gt; Warp</td><td>Bend text or shapes, as on a flag or arch</td></tr>
</tbody>
</table>
<p>Effects are live, so you can edit them later in the Appearance panel. Choose <strong>Object &gt; Expand Appearance</strong> to make an effect into real paths. Important: raster effects, such as shadows, depend on a resolution setting in <strong>Effect &gt; Document Raster Effects Settings</strong>. Use 300 ppi for print and 72 or 150 for screen.</p>

<h3>Opacity and blending</h3>
<p>The Properties panel and <strong>Window &gt; Transparency</strong> (Shift+Ctrl+F10) let you change <strong>Opacity</strong> and choose a <strong>Blending Mode</strong>. <strong>Multiply</strong> darkens, so a shadow shape set to Multiply over a fabric colour looks natural. Lower opacity (for example 30 percent) is good for watermarks and guide images.</p>

<h3>Building a simple T-shirt mockup</h3>
<ol>
<li>Draw the T-shirt as a shape with the Pen tool or by combining rectangles and ellipses with Pathfinder. Fill it with a fabric colour.</li>
<li>Place your logo on the chest. Use <strong>Align</strong> to centre it horizontally and scale it so it is about a third of the shirt width.</li>
<li>Duplicate the shirt shape with Ctrl+C and Ctrl+F (Paste in Front), fill it with a dark grey, set the blending mode to <strong>Multiply</strong> and opacity to 15 percent. Use a clipping mask or a gradient to make the shading appear only along one side.</li>
<li>Add a subtle Drop Shadow beneath the shirt.</li>
<li>Export the mockup as PNG or JPG for the client presentation, and keep the vector logo file separate.</li>
</ol>
<p>Other simple mockups: a business card (a rounded rectangle with a shadow), a signboard (rectangle with a wooden frame), a mug (rounded rectangle with a handle and the logo on the body). Keep it clearly labelled as a presentation, not a final artwork.</p>

<h3>Honesty with mockups</h3>
<p>Mockups should show a realistic result. Do not use a mockup that makes the logo look better than it will be in print, such as hiding the fact that a gradient cannot be embroidered.</p>

<h3>Mini-case</h3>
<p>Isaac shows a tailor three logo options on one slide: each on a drawn shirt, a business card and a signboard. The tailor picks the second because it is clear on the shirt and card. The mockups took 30 minutes and avoided a long meeting.</p>

<blockquote><p><strong>Quick check:</strong> Which blending mode helps make a natural-looking shadow over a coloured fabric?</p><p>Answer: Multiply, because it darkens the colour underneath instead of covering it with a grey patch.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You added a Drop Shadow effect and now want to edit it. Where do you go?</p><p>Answer: The Appearance panel, because live effects are listed there and can be edited or removed.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Draw a T-shirt shape and a mug shape on one artboard.</li>
<li>Place your logo on both, align it and scale it sensibly.</li>
<li>Add a Multiply shading shape and a soft Drop Shadow to each.</li>
<li>Export as PNG and save with the logo in your portfolio.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>The Appearance panel shows and edits fills, strokes, effects and opacity.</li>
<li>Effects are live and editable; Expand Appearance makes them permanent.</li>
<li>Multiply gives realistic shading.</li>
<li>Mockups help clients decide, but must be honest.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which is the best first step when starting a flat illustration?
- Add shadows to every shape
* Choose one idea, sketch the layout and pick a small palette
- Export the file
- Convert everything to outlines
= Planning idea, layout and colours makes drawing faster and the result more consistent.

Q: How can you make a crescent shadow shape from a circle?
- Draw it freehand with the Pencil only
- Use Unite with a rectangle
* Place a shifted copy on top and use Minus Front
- Apply Image Trace
= Cutting one circle from another leaves a crescent.

Q: You changed the colour of one tree symbol and all the trees changed. What does this show?
- The file is corrupted
* Instances stay linked to the symbol, so editing the symbol updates all copies
- You applied a clipping mask
- The trees were grouped
= Symbols update every instance.

Q: After pressing Done in Pattern Editing mode, where is the pattern found?
- In the Layers panel only
- In the Brushes panel only
* In the Swatches panel, ready to fill shapes
- It disappears until exported
= Finished patterns are stored as swatches.

Q: You want a pattern's tiles smaller but the rectangle size unchanged. What setting?
- Scale Strokes and Effects only
* Transform Patterns ticked and Transform Objects unticked in the Scale options
- Move the pattern to another layer
- Lock the layer
= This scales the pattern fill and leaves the object alone.

Q: Where do you edit a Drop Shadow you applied earlier?
* The Appearance panel
- The Pathfinder panel
- The Swatches panel
- The Artboards panel
= Live effects are listed in the Appearance panel.

Q: For print, which setting controls the resolution of raster effects such as shadows?
- Stroke panel
- Align panel
- Artboard Options
* Effect > Document Raster Effects Settings
= Set 300 ppi for print so shadows are not blurry.

Q: A designer shows a client a mockup in which the gradient looks great, even though the client plans embroidery on uniforms. What is the honest approach?
- Say nothing; the mockup looks good
- Remove the logo from the mockup
* Warn the client that embroidery cannot reproduce gradients and show a flat version too
- Increase the gradient brightness
= Mockups must give a realistic picture of the final product.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 6
        [
            'title'   => 'Module 6: Exporting, printing and working with clients',
            'summary' => 'Export in the right formats, prepare files for print, package a logo for a client with a brand sheet, and understand copyright and basic contracts.',
            'lessons' => [
                [
                    'title'   => 'Exporting: SVG, PDF, PNG, EPS, AI and Export for Screens',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose the right file format for each use and export artboards and assets correctly. This matters because sending the wrong format, such as a JPG logo with a white box to a printer, wastes time and makes you look unprofessional.</p>

<h3>Formats and when to use them</h3>
<table>
<thead><tr><th>Format</th><th>Type</th><th>Best for</th><th>Note</th></tr></thead>
<tbody>
<tr><td>AI</td><td>Vector, native</td><td>Your editable master file</td><td>Needs Illustrator (or some other programs) to edit</td></tr>
<tr><td>SVG</td><td>Vector, open</td><td>Websites, apps, Inkscape and browser use</td><td>Tiny file and sharp at any size; check text is outlined</td></tr>
<tr><td>PDF</td><td>Vector (can hold raster)</td><td>Sending to printers and clients</td><td>Opens almost anywhere; keeps vectors</td></tr>
<tr><td>EPS</td><td>Vector, older</td><td>Some printers and old software</td><td>No transparency support; ask whether it is needed</td></tr>
<tr><td>PNG</td><td>Raster, transparent background</td><td>Logos on websites, slides, social media</td><td>Choose the pixel size you need</td></tr>
<tr><td>JPG</td><td>Raster, no transparency</td><td>Photos and mockups</td><td>Compressed; avoid for logos</td></tr>
</tbody>
</table>

<h3>Save a master first</h3>
<p>Always keep the .ai file. Choose <strong>File &gt; Save</strong>. For a copy in another format use <strong>File &gt; Save a Copy</strong> or <strong>File &gt; Export</strong>.</p>

<h3>Exporting a PNG or JPG</h3>
<ol>
<li>Choose <strong>File &gt; Export &gt; Export As</strong>.</li>
<li>Pick the folder and choose PNG or JPEG as the format.</li>
<li>Tick <strong>Use Artboards</strong> to export each artboard, or enter a range such as 1-3.</li>
<li>In the options, set <strong>Resolution</strong> (72 ppi for typical screens, 150 or 300 ppi for larger or print use) and <strong>Background Color</strong> to Transparent for PNG.</li>
<li>Click Export, then OK.</li>
</ol>

<h3>Export for Screens</h3>
<p>This is the quickest way to make many sizes at once. Choose <strong>File &gt; Export &gt; Export for Screens</strong> (Alt+Ctrl+E; Option+Cmd+E on Mac).</p>
<ol>
<li>On the <strong>Artboards</strong> tab pick the artboards you want.</li>
<li>In the right-hand side, add scale options such as 1x, 2x and 3x, and a suffix for each (for example <code>@2x</code>).</li>
<li>Choose the format: PNG, JPG, SVG or PDF.</li>
<li>Pick a folder and click <strong>Export Artboard</strong>.</li>
</ol>
<p>The Assets tab lets you collect single objects (drag them in) to export as separate files, handy for icons.</p>

<h3>Exporting an SVG</h3>
<p>Use <strong>File &gt; Save a Copy</strong> or <strong>File &gt; Export</strong> and choose SVG. In the options, a sensible setup is: Styling set to a Presentation Attributes or Internal CSS option, Fonts set to convert to outlines (or outline text yourself first), and Responsive ticked if the file will resize in a web page. Open the result in a browser to check how it looks.</p>

<h3>Exporting a PDF</h3>
<p><strong>File &gt; Save a Copy</strong>, choose Adobe PDF. Choose a preset such as <strong>High Quality Print</strong> or <strong>Press Quality</strong> for printers. Untick <em>Preserve Illustrator Editing Capabilities</em> if you want a small file for reading only.</p>

<h3>Naming and checks</h3>
<ul>
<li>Use clear names such as <code>kato-fresh-logo-full-colour.png</code>, not <code>final2-new.png</code>.</li>
<li>Check transparent PNGs on a dark and a light background.</li>
<li>Open each exported file once before sending it.</li>
</ul>

<h3>Free alternative</h3>
<p>In Inkscape, <strong>File &gt; Save As</strong> gives SVG or PDF and <strong>File &gt; Export</strong> gives PNG at a chosen resolution; look for Export PNG Image in current versions.</p>

<blockquote><p><strong>Quick check:</strong> You need your logo on a website with a transparent background and sharp edges on all screens. Which formats suit?</p><p>Answer: SVG, or PNG with a transparent background, because SVG scales perfectly and PNG keeps transparency.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What does Export for Screens save you from doing manually?</p><p>Answer: Exporting every artboard at several sizes and formats one by one, because it creates them all in one step.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open your logo file with Primary, Icon, One colour and Reversed artboards.</li>
<li>Use Export for Screens to export PNG at 1x and 2x and SVG for all artboards.</li>
<li>Open the SVG in a browser and test the PNGs on dark and light backgrounds.</li>
<li>Put the exports in a folder named <code>logo-exports</code> with clear file names.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Keep AI as the master; use SVG, PDF, PNG and others as deliverables.</li>
<li>PNG for transparent raster, JPG for photos, SVG and PDF for vectors.</li>
<li>Export for Screens produces many sizes at once.</li>
<li>Name files clearly and check each export.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Preparing for print: bleed, outlines, CMYK and overprint basics',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to set up a print-ready file with bleed, correct colour mode, outlined text and sensible black, and to talk to a printer clearly. This matters because mistakes found at the print shop cost money and time, and a small business owner often cannot afford a reprint.</p>

<h3>Ask the printer first</h3>
<p>Print shops differ. Before you start, ask: what size, which paper, what colour process (CMYK or spot), how much bleed, what file type (usually a PDF), and whether they want crop marks. Write the answers on your job sheet.</p>

<h3>Bleed, trim and safe area</h3>
<table>
<thead><tr><th>Term</th><th>Meaning</th><th>Typical value</th></tr></thead>
<tbody>
<tr><td>Trim size</td><td>Final size after cutting</td><td>A5 is 148 by 210 mm</td></tr>
<tr><td>Bleed</td><td>Extra artwork beyond the trim so no white edge appears after cutting</td><td>3 mm on every side</td></tr>
<tr><td>Safe area</td><td>Margin inside the trim where important text stays</td><td>5 mm or more</td></tr>
</tbody>
</table>
<p>Cutting machines move slightly, so backgrounds must extend past the trim. Set bleed at creation in <strong>File &gt; New</strong>, or later in <strong>File &gt; Document Setup</strong> under Bleed (3 mm for each side). A red line around the artboard shows the bleed edge. Drag background shapes out to the red line.</p>

<h3>The print-ready checklist</h3>
<ol>
<li><strong>Colour mode CMYK.</strong> Choose File &gt; Document Color Mode &gt; CMYK Color. Replace any leftover RGB swatches.</li>
<li><strong>Outline text</strong> (Type &gt; Create Outlines) in a copy, or embed fonts in the PDF if the printer allows it.</li>
<li><strong>Embed or package images.</strong> Linked pictures can go missing. Use <strong>Window &gt; Links</strong> and the Embed option, or use <strong>File &gt; Package</strong> to gather files.</li>
<li><strong>Image resolution</strong>: aim for about 300 ppi at the printed size for photos.</li>
<li><strong>Black.</strong> Use K 100 for body text and thin lines. Ask the printer about rich black for large areas.</li>
<li><strong>Bleed and safe area</strong> checked, with crop marks if requested (in the PDF Save options, under Marks and Bleeds).</li>
<li><strong>Proof</strong> read all text for spelling and phone numbers.</li>
</ol>

<h3>Overprint, at basic level</h3>
<p>Normally, when a coloured shape sits on another, the top colour knocks out (removes) the one below. Overprint makes the top colour print over the one beneath, so inks mix. It is used on purpose, for example for black text over a colour, to avoid thin white gaps if the printing plates are not perfectly aligned. Find it in <strong>Window &gt; Attributes</strong> with <strong>Overprint Fill</strong> or <strong>Overprint Stroke</strong>. Preview it with <strong>View &gt; Overprint Preview</strong>. Beginners should not guess: use overprint only on the printer's advice, and never apply it to white objects, because white that overprints simply disappears.</p>

<h3>Saving the print PDF</h3>
<ol>
<li>Choose <strong>File &gt; Save a Copy</strong> and select Adobe PDF.</li>
<li>Pick a preset the printer recommends, such as High Quality Print, or one they supplied.</li>
<li>In the <strong>Marks and Bleeds</strong> section tick <strong>Use Document Bleed Settings</strong> and, if asked, Trim Marks.</li>
<li>Save with a name such as <code>flyer-a5-print.pdf</code>.</li>
</ol>

<h3>Mini-case</h3>
<p>David designs 500 A5 flyers for a church fundraising event. His blue background stopped at the trim line, so the first sample had thin white edges. He extends the background to the 3 mm bleed, sets text 6 mm inside the trim and exports a PDF with trim marks. The reprint cost him only a sample, not the whole job.</p>

<h3>Free alternative</h3>
<p>Inkscape works mostly in RGB, so ask whether the printer can convert and proof it, or finish print-critical files in a program with CMYK support. Always request a printed or digital proof.</p>

<blockquote><p><strong>Quick check:</strong> Why must a blue background extend 3 mm past the trim line?</p><p>Answer: Cutting is never perfect, so the extra bleed prevents thin white edges appearing.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why do printers often ask you to outline text or embed fonts?</p><p>Answer: Their computers may not have your fonts, and missing fonts change the lettering.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create an A5 CMYK document (148 by 210 mm) with a 3 mm bleed.</li>
<li>Add a coloured background that reaches the red bleed line and a heading inside a 6 mm safe margin.</li>
<li>Outline the heading on a copy, then save a PDF with trim marks.</li>
<li>Write a short job sheet listing size, bleed, mode and fonts, and save it with your portfolio.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Ask the printer for size, bleed, colour and file needs before you design.</li>
<li>Use CMYK, 3 mm bleed, safe margins and outlined text or embedded fonts.</li>
<li>Package or embed images and check resolution.</li>
<li>Use overprint only when advised, and never on white.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Packaging a logo for a client: file set and a one-page brand sheet',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to deliver a complete, organised logo package and a one-page brand sheet that tells the client how to use their logo. This matters because a client who receives a messy folder will call you for months asking where a file is, while a neat package makes you look reliable and earns recommendations.</p>

<h3>A professional file set</h3>
<p>Create one main folder named after the client and project, then subfolders.</p>
<table>
<thead><tr><th>Folder</th><th>Files inside</th><th>Used for</th></tr></thead>
<tbody>
<tr><td>01-Master</td><td>The editable .ai file with all artboards</td><td>Future changes by a designer</td></tr>
<tr><td>02-Vector</td><td>SVG, PDF and EPS in full colour, black and white versions</td><td>Print and professional use</td></tr>
<tr><td>03-PNG</td><td>Transparent PNGs of each version, large and small</td><td>Office documents, social media, websites</td></tr>
<tr><td>04-Social</td><td>Square profile picture and cover images</td><td>Facebook, WhatsApp Business, Instagram, X</td></tr>
<tr><td>05-Brand-sheet</td><td>One-page PDF guide</td><td>Anyone who uses the logo</td></tr>
</tbody>
</table>
<p>Use consistent names such as <code>kato-fresh_logo_horizontal_full-colour.svg</code>. Do not use spaces or special symbols, which can break on some systems. Include a plain text file listing fonts used and their licence source.</p>

<h3>The one-page brand sheet</h3>
<p>Make a new artboard (A4 landscape or portrait) in the same Illustrator file, then export as a PDF. It should show:</p>
<ol>
<li><strong>The primary logo</strong> in the middle with its name.</li>
<li><strong>The versions</strong>: horizontal, stacked, icon, black, white on a dark block.</li>
<li><strong>Colours</strong> as swatches with names and values: RGB hex codes for screens and CMYK values for print, plus a Pantone reference only if one was chosen.</li>
<li><strong>Fonts</strong> for headings and text, with a sample line each and where to download them.</li>
<li><strong>Clear space and minimum size</strong>, drawn as a diagram.</li>
<li><strong>Do not</strong> examples: stretching, changing colours, adding effects, placing on a busy photo.</li>
<li>Your contact details and the date.</li>
</ol>

<h3>Worked example of a colour block</h3>
<table>
<thead><tr><th>Name</th><th>HEX</th><th>CMYK</th></tr></thead>
<tbody>
<tr><td>Kato Green</td><td>#1B8A4B</td><td>C 85 M 20 Y 95 K 5</td></tr>
<tr><td>Sun Gold</td><td>#F5BE28</td><td>C 3 M 25 Y 90 K 0</td></tr>
<tr><td>Ink</td><td>#19191E</td><td>C 70 M 65 Y 60 K 75</td></tr>
</tbody>
</table>
<p>These values are examples; give your client the exact values from your file, and say that printed colour may vary slightly.</p>

<h3>Delivery</h3>
<ul>
<li>Compress the main folder into a ZIP file (right-click the folder, Send to, Compressed (zipped) folder on Windows).</li>
<li>Share it by email, a cloud folder or a USB flash drive.</li>
<li>Send a short message listing what is inside and which file to use where.</li>
<li>Keep your own backup of everything for at least a year.</li>
</ul>

<h3>Mini-case</h3>
<p>Lillian delivers a logo to a women's savings group. She includes a sheet that shows the primary logo, a black stamp version and the exact colours, plus a note: "Use the black version on the rubber stamp." Six months later a printer calls the group, reads the brand sheet and prints everything correctly without calling Lillian once.</p>

<h3>Free alternative</h3>
<p>A brand sheet can be built in Inkscape and exported as a PDF, or in a layout tool such as Canva. The important part is the content, not the program.</p>

<blockquote><p><strong>Quick check:</strong> Why include both HEX and CMYK values on the brand sheet?</p><p>Answer: HEX is for screens and websites while CMYK is for print, so users of either medium can match the colour.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which file in the package lets a future designer change the logo?</p><p>Answer: The editable .ai master (or SVG if an AI file is unavailable), because exported PNGs cannot be edited easily.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose your best logo project and create the folder structure above.</li>
<li>Export the vector and PNG versions with clear names.</li>
<li>Design a one-page brand sheet with versions, colours, fonts, clear space and three do-nots, and export it as a PDF.</li>
<li>Zip the whole package. This is a complete portfolio case study.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Deliver a tidy folder: master, vector, PNG, social, brand sheet.</li>
<li>A brand sheet shows versions, colours, fonts, clear space and what not to do.</li>
<li>Use clear file names without spaces.</li>
<li>Keep your own backup after delivery.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Copyright, licences and client contracts',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to avoid copyright mistakes in your designs, explain who owns the artwork, and agree simple terms with a client in writing. This matters because a stolen image or a verbal agreement that goes wrong can end a client relationship or lead to a legal claim. This lesson gives basic guidance, not legal advice; for serious disputes consult a lawyer or the relevant national copyright office.</p>

<h3>Copyright in simple terms</h3>
<p>Copyright is the legal right of the creator over an original work such as a drawing, a photograph or a logo design. Without the owner's permission, others may not copy or sell it, except in limited cases set by law. Copyright usually exists automatically when something original is created. Different countries have their own laws, so check the rules where you and your client work.</p>

<h3>What you may and may not use</h3>
<table>
<thead><tr><th>Source</th><th>Safe to use in client work?</th></tr></thead>
<tbody>
<tr><td>Your own drawings and photos</td><td>Yes</td></tr>
<tr><td>An image found through a search engine</td><td>No, unless the licence clearly allows it</td></tr>
<tr><td>Another company's logo, or a close copy</td><td>No; it may also confuse customers</td></tr>
<tr><td>Free font or icon with a licence allowing commercial use</td><td>Yes, if you follow the licence terms such as credit</td></tr>
<tr><td>Public domain work</td><td>Yes, but check it truly is public domain</td></tr>
<tr><td>Stock vectors you have paid for</td><td>Check whether the licence allows use in a logo; many do not</td></tr>
</tbody>
</table>
<p>Never build a logo from a stock illustration that thousands of others can also use: the client cannot own or protect it. Draw your own, or use stock only for non-logo uses.</p>

<h3>Understanding licences</h3>
<p>A licence says what you are allowed to do. Look for: commercial use allowed or not, attribution (credit) required or not, modification allowed or not, and any limits such as number of copies. Creative Commons licences are one common system, with labels that show whether credit, non-commercial use or no changes apply. Always read the specific licence.</p>

<h3>Who owns the finished logo?</h3>
<p>This is decided by the agreement. A common arrangement for logos is that the client owns the final design after full payment, and the designer keeps the right to show it in a portfolio. Say this clearly in writing. Unused concepts usually stay with the designer unless the agreement says otherwise.</p>

<h3>A simple written agreement</h3>
<ol>
<li><strong>Scope:</strong> what you will deliver, for example a logo with three versions and a brand sheet.</li>
<li><strong>Revisions:</strong> how many rounds of changes are included, and the cost of extra ones.</li>
<li><strong>Price and payment:</strong> total in UGX, a deposit (for example half) before you start, and the balance before you hand over the final files.</li>
<li><strong>Timeline:</strong> dates for concepts, feedback and delivery, and what happens if the client is late with feedback.</li>
<li><strong>Ownership:</strong> transfers on full payment; the portfolio right stays with you.</li>
<li><strong>Client responsibilities:</strong> supply correct text, names and any material they own.</li>
<li><strong>Signatures and date:</strong> an email "I agree" can be enough in many places, but a signed page is clearer.</li>
</ol>

<h3>Good conduct</h3>
<ul>
<li>Be honest about your own work and say when something is a stock image.</li>
<li>Do not accept jobs to copy another business's identity.</li>
<li>Keep client information private until they publish it.</li>
</ul>

<h3>Mini-case</h3>
<p>Moses makes a logo for a friend's hair studio and uses a clip-art scissors image from a search result. A year later the original artist asks for payment. Moses has to redesign the logo for free and the studio must reprint signs. If he had drawn the scissors himself, or used a properly licensed image, the problem would never have happened.</p>

<blockquote><p><strong>Quick check:</strong> You find a nice icon on a website with no licence information. Can you put it in a client's logo?</p><p>Answer: No, because without a licence you do not know that you have permission, and the safe choice is to draw your own.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why take a deposit before starting a project?</p><p>Answer: It shows the client is serious and covers some of your time if the project stops.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a one-page agreement for an imaginary logo job using the seven points above, with prices in UGX.</li>
<li>Find one free icon or font and write down its licence terms: commercial use, credit, modification.</li>
<li>Make a short checklist titled "Before I publish a design" with five questions about the images, fonts and permission.</li>
<li>Save these three documents with your portfolio files.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use only your own work or properly licensed material.</li>
<li>Read each licence for commercial use, credit and changes.</li>
<li>Put ownership, price, revisions and timeline in writing.</li>
<li>Take a deposit and give final files after full payment.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which file should always be kept as your editable master?
- The JPG
* The native AI file
- The PNG
- The PDF with fonts removed
= The AI file keeps layers, artboards and editable paths for future changes.

Q: A designer needs a logo with a transparent background for a website. Which export suits?
- JPG
- A screenshot
* PNG with a transparent background, or SVG
- Word document
= JPG has no transparency; PNG and SVG keep it.

Q: What does Export for Screens help you do?
- Convert RGB to CMYK
* Export several artboards at several sizes and formats in one step
- Outline all fonts
- Check spelling
= It saves exporting each version by hand.

Q: Why should a print background extend 3 mm beyond the trim?
- It makes the file smaller
- It is a rule for web files
* Cutting is not perfectly exact, and bleed prevents white edges
- Printers require RGB
= Bleed gives the cutter a safe margin.

Q: Which colour mode should a file for an offset print shop normally use?
- RGB
* CMYK
- Both at once
- Indexed
= CMYK matches the inks used on the press. (Check with the printer, as some digital printers accept RGB.)

Q: What is a risk of sending a print file with live text in a font the printer does not have?
- Nothing; the printer will find the font
* The font may be replaced, changing the layout and look
- The text turns to CMYK
- The file cannot be opened at all
= Missing fonts are substituted; outlining or embedding avoids this.

Q: What should a good brand sheet include?
- Only the logo in full colour
* Logo versions, colours with HEX and CMYK values, fonts, clear space, minimum size and do-nots
- The designer's invoice
- A photo of the designer
= The sheet tells everyone how to use the logo correctly.

Q: A client wants to use a stock illustration found online as the main part of their logo. What is the best advice?
- Go ahead; stock images are free for any use
* Check the licence and avoid it for a logo, because others can use the same image; draw an original mark
- Change its colour to make it unique
- Convert it to outlines and then it is yours
= Logos should be original and protectable; changing colours does not change ownership.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What is the main advantage of a vector logo over a small PNG logo?
- It always has more colours
* It stays sharp at any size because it is made of paths, not pixels
- It cannot be edited
- It is always smaller than 1 KB
= Vector shapes are stored as mathematics and scale without losing quality.

Q: Which kind of image is usually best kept as raster?
- A simple icon
- A company logo
* A photograph
- A map symbol
= Photographs have millions of subtle colour changes that pixels store well.

Q: You are making a social media post and a leaflet from the same project. Which colour modes will you most likely use?
- RGB for the leaflet and CMYK for the post
* RGB for the post and CMYK for the leaflet
- CMYK for both
- Grayscale for both
= Screens use RGB light; print uses CMYK inks.

Q: Which tool do you use to move a single anchor point of a shape?
- Selection tool
- Rotate tool
- Zoom tool
* Direct Selection tool
= The Direct Selection tool works on individual points and segments.

Q: You need six circles in a row, exactly 80 px apart. What is the quickest accurate approach?
* Copy one with the Move dialog set to 80 px, then press Ctrl+D (Cmd+D) repeatedly
- Drag each circle by eye with Smart Guides off
- Draw each with the Pen tool
- Use Image Trace
= Repeating a transformation gives exact spacing.

Q: What does dragging while you click with the Pen tool create?
- A corner point
* A smooth point with handles
- A closed path
- A text object
= The drag pulls out the handles that set the curve.

Q: A path made with 30 points looks lumpy. What is the best fix?
- Add a stroke
- Convert to RGB
* Remove unnecessary points and adjust handles on the remaining ones
- Apply a drop shadow
= Fewer well-placed points produce smoother curves.

Q: How do you cut a round hole out of a rectangle using Pathfinder?
- Place the circle on top of the rectangle and choose Unite
* Place the circle on top and choose Minus Front
- Place the circle underneath and choose Exclude
- Group them and choose Divide
= Minus Front lets the front shape cut away from the shapes behind it.

Q: Which tool lets you merge or delete overlapping regions by dragging or Alt-clicking?
- Width tool
- Eyedropper
* Shape Builder tool
- Blend tool
= Shape Builder works directly on regions created by overlapping shapes.

Q: You want a line to taper at both ends. Which tool?
- Pencil tool
- Rotate tool
- Artboard tool
* Width tool
= The Width tool changes stroke thickness at chosen points.

Q: Why use Outline Stroke on a finished logo?
* The line becomes a filled shape that scales exactly and can be used with Pathfinder
- It changes the file to CMYK
- It makes the stroke dashed
- It adds a drop shadow
= Outlined strokes cannot change weight when the logo is resized.

Q: What is the benefit of a global swatch?
- It makes the file smaller
* Editing it updates every object coloured with it
- It converts the document to CMYK
- It blocks other colours
= Global swatches keep brand colours consistent and easy to change.

Q: A client's printed green looks duller than on screen. What is the most likely reason?
- The printer made an error
* CMYK ink on paper cannot reproduce the brightest RGB screen colours
- The file was saved as PNG
- The paper is too thick
= Screen light has a wider colour range than ink.

Q: A company wants the exact same brand colour on cartons from two different printers. What do you suggest?
- Use a different RGB colour for each printer
- Use a gradient
* Ask about a spot colour such as a Pantone ink
- Use black only
= Spot colours are pre-mixed inks matched to a standard.

Q: Why create outlines for the text in a logo before sending it for printing?
- To make the text editable
- To compress the file
* To convert letters into shapes so they do not depend on installed fonts
- To convert the text to RGB
= Outlined text looks the same on any computer.

Q: Which feature crops a pattern so it appears only inside a circle?
- Pathfinder Unite
- Overprint
* Clipping mask
- Pixel Preview
= A clipping mask hides everything outside the top shape.

Q: What is the correct first step of a logo project?
- Choose the final colours
* Write down the client's brief: business, personality, uses, budget and deadline
- Export PNGs
- Draw in Illustrator without any plan
= The brief guides all later decisions.

Q: Which method best generates many logo ideas quickly?
- Copying a competitor
- Browsing templates only
* Making many small sketches on paper first
- Applying gradients
= Hand sketching is fast and cheap, and helps you find strong concepts.

Q: Image Trace gives the best results with which source?
- A dark, blurry phone photo
* A clean black-ink drawing scanned at good contrast
- A group photograph
- A screenshot of a video
= Clean high-contrast sources give clean paths.

Q: You expanded an Image Trace result and see a white rectangle behind the artwork. What is the right action?
- Leave it, it is harmless
* Select the white shape with the Direct Selection tool and delete it
- Change the colour mode
- Add a stroke
= The white box would block coloured backgrounds.

Q: What makes an icon set look consistent?
- Different stroke weights on each icon
* Shared grid, stroke weight, corner style and level of detail
- Different artboard sizes
- Random gradients
= Matching rules make icons feel like one family.

Q: A logo has hairline details that disappear when printed 2 cm wide. What should you do?
- Ignore it; clients rarely print small
- Add a drop shadow
* Thicken or simplify the details and test again at a small size
- Convert to JPG
= A logo must stay legible at its smallest planned size.

Q: You edit a symbol in the Symbols panel. What happens to the copies on the artboard?
- Nothing
* All instances update
- Only the selected one updates
- They are deleted
= Instances stay linked to the symbol.

Q: Where does a finished custom pattern appear for you to use?
- Layers panel
* Swatches panel
- Brushes panel only
- Align panel
= Choosing Done saves the pattern as a swatch.

Q: Which blending mode usually gives a natural shadow over coloured fabric in a mockup?
- Screen
* Multiply
- Difference
- Dissolve
= Multiply darkens the colours below instead of covering them.

Q: You need to deliver many sizes of every artboard as PNG and SVG quickly. Which command suits?
- Save As Template
- Document Setup
* File > Export > Export for Screens
- Object > Expand
= Export for Screens batches artboards, formats and scales.

Q: A printed A5 flyer must have a coloured background right to the edge. What do you set up?
- Nothing; printers fix edges
* A 3 mm bleed with the background extended to the bleed line
- A smaller artboard
- A thick white stroke
= Bleed prevents white edges after cutting.

Q: A printer asks for overprint on small black text. How should a beginner respond?
- Apply overprint to every object, including white
* Apply it only as advised, check View > Overprint Preview, and never apply it to white objects
- Refuse to use black
- Convert text to JPG
= White that overprints disappears, so use it carefully.

Q: Which items belong in a logo package for a client?
* An editable master file, vector and PNG versions, social images and a brand sheet
- Only a low-resolution JPG
- A screenshot
- The invoice only
= A complete set lets the client use the logo anywhere without calling you.

Q: A client wants to use a free image found online as the centre of their logo. What is the best advice?
- Use it; free images are free for any use
* Check the licence, and prefer an original drawing because a logo should be unique and protectable
- Change the colour and claim it as yours
- Trace it and it is no longer copyrighted
= A logo should be original, and tracing or recolouring does not remove another person's rights.
QUIZ,
];
