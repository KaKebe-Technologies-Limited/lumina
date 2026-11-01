<?php
return [
    'slug'        => 'adobe-photoshop-photo-editing-design',
    'title'       => 'Adobe Photoshop: Photo Editing and Digital Design',
    'category'    => 'design',
    'level'       => 'intermediate',
    'icon'        => 'star',
    'sort'        => 510,
    'summary'     => 'Edit and retouch photos, build layered designs, make posters, social posts and thumbnails, and export clean files for web and print, working non-destructively in Photoshop (with free alternatives for practice).',
    'description' => '<p>Photoshop is the best-known tool for editing photos and building digital designs. Printers, newspapers, churches, schools, small businesses and NGOs all rely on images that have been cropped, colour-corrected, cleaned up and laid out well. This course teaches you to do that work properly, the way professionals do, so you can fix your own photos, design posters and social media posts, and take on paid jobs.</p><p>You will learn the workspace, layers, selections and masks, adjustment layers, retouching, removing backgrounds, type and shapes, smart objects, compositing, and AI-assisted features and their limits. Then you will learn to prepare files correctly for the web, for social media and for the printer, to organise your work, and to finish with a capstone poster campaign or photo composite for your portfolio.</p><p>Photoshop is a paid subscription application with a free trial. Every idea in this course also works in free tools such as Photopea (in the browser), GIMP and Krita, and we point out where their menus differ, so you can practise without paying.</p>',
    'outcomes'    => [
        'Set up a Photoshop document with the right size, resolution and colour mode for its purpose',
        'Work non-destructively with layers, masks, adjustment layers, smart objects and smart filters',
        'Make clean selections and masks and cut a subject out of its background',
        'Crop, straighten, resize and correct the colour and tone of a photo',
        'Retouch photos honestly with healing, clone and dodge and burn tools',
        'Design posters, flyers, social media posts, banners and YouTube thumbnails with good type and layout',
        'Automate repeat work with actions, batch processing and Export As',
        'Prepare and export files for web, social media and print, including CMYK, bleed and resolution',
        'Use AI-assisted features responsibly and follow copyright and ethical rules for retouching',
    ],
    'requirements' => ['Comfortable with a computer and with saving and finding files', 'Graphic Design Fundamentals (or equivalent knowledge of layout, colour and type) is helpful', 'A computer; Photoshop (free trial or subscription) or a free alternative such as Photopea, GIMP or Krita. No Photoshop experience needed'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Adobe Photoshop user guide and tutorials', 'url' => 'https://helpx.adobe.com/photoshop/'],
        ['title' => 'Photopea (free image editor in the browser)', 'url' => 'https://www.photopea.com/'],
        ['title' => 'GIMP (free image editor)', 'url' => 'https://www.gimp.org/'],
        ['title' => 'Krita (free painting and image editing program)', 'url' => 'https://krita.org/'],
        ['title' => 'Creative Commons', 'url' => 'https://creativecommons.org/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: Getting started with Photoshop',
            'summary' => 'Install or choose your tool, learn the workspace, set up documents correctly and understand undo and file formats.',
            'lessons' => [
                [
                    'title'   => 'What Photoshop is, trial, system needs and free alternatives',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what Photoshop does, decide how to get it or a free alternative, and check whether your computer can run it. This matters because many learners lose weeks installing the wrong thing or buying a subscription before they know they need it.</p>

<h3>What Photoshop is for</h3>
<p>Photoshop is a <strong>raster</strong> image editor. A raster image is a grid of tiny coloured squares called <strong>pixels</strong>. Photoshop is built for photos: correcting colour, removing blemishes, cutting out people, combining pictures and adding text and shapes. Drawing logos that must stay sharp at any size is better done in a <strong>vector</strong> program (see Graphic Design Fundamentals), though Photoshop can make simple vector shapes too.</p>

<h3>Getting Photoshop honestly</h3>
<ul>
<li>Photoshop is sold as a <strong>monthly or yearly subscription</strong> through Adobe's Creative Cloud service, and there is usually a <strong>free trial</strong> period. Prices and plans change, so read the current plan page before you pay.</li>
<li>Download only from the official Adobe website through the Creative Cloud app. Cracked or free "full versions" on random websites often carry viruses and are illegal.</li>
<li>Students and schools sometimes get discounts. Ask your institution.</li>
</ul>

<h3>Free tools to practise the same ideas</h3>
<table>
<thead><tr><th>Tool</th><th>How you use it</th><th>How close to Photoshop</th></tr></thead>
<tbody>
<tr><td>Photopea</td><td>In your browser, no install; opens PSD files</td><td>Very close: layers, masks, adjustments and menus look similar</td></tr>
<tr><td>GIMP</td><td>Free download for Windows, Mac, Linux</td><td>Good for photo editing; menus and shortcuts differ</td></tr>
<tr><td>Krita</td><td>Free download</td><td>Best for painting and drawing; has layers and masks</td></tr>
</tbody>
</table>
<p>Throughout the course we describe Photoshop menus. In Photopea, look in the same top menu names (File, Edit, Image, Layer, Select, Filter). In GIMP, similar tools sit in the Layer, Colors and Select menus.</p>

<h3>System needs</h3>
<p>Photoshop is demanding. Exact requirements change with each release, so check the system requirements page on Adobe's help site before installing. In general, you want:</p>
<ul>
<li>A 64-bit Windows or macOS computer with a recent operating system.</li>
<li><strong>RAM (memory):</strong> 8 GB is a bare minimum; 16 GB makes work smoother with large files.</li>
<li><strong>Storage:</strong> several GB for the program and room for big files; an SSD helps speed.</li>
<li>A graphics card (GPU) that is supported, and a screen with reasonable colour.</li>
<li>A mouse is easier than a touchpad; a graphics tablet is a bonus, not a need.</li>
</ul>
<p>If your computer is old, start with Photopea in a modern browser such as Chrome or Edge.</p>

<blockquote><p><strong>Quick check:</strong> A friend offers you a free "full Photoshop" download from an unknown website. What should you do?</p><p>Answer: Decline and use the official free trial or a free alternative, because unofficial copies are illegal and often contain malware.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Your laptop has 4 GB of RAM and is slow. Which option is the most sensible way to practise?</p><p>Answer: Use Photopea in the browser, since it needs no install and runs lighter than full Photoshop on weak computers.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open the Adobe Photoshop page on Adobe's official site and find the system requirements page. Write down the RAM and storage needs.</li>
<li>Check your own computer: on Windows go to <strong>Settings > System > About</strong>; on Mac choose <strong>Apple menu > About This Mac</strong>. Note your RAM.</li>
<li>Decide: trial, subscription or free alternative. Write one sentence explaining why.</li>
<li>Open Photopea.com in your browser and open any photo from your computer (<strong>File > Open</strong>). Take a screenshot of it open to keep as your first portfolio note.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Photoshop edits raster images made of pixels and is paid software with a free trial.</li>
<li>Download only from Adobe's official site; avoid cracked copies.</li>
<li>Photopea, GIMP and Krita let you practise the same skills for free.</li>
<li>Check RAM and storage before installing; use Photopea on weak computers.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The workspace: tools, options bar, panels and preferences',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to find your way around the Photoshop window, switch workspaces and set sensible preferences. This matters because you will spend hours in this window, and knowing where things live is the first step to working fast.</p>

<h3>The parts of the window</h3>
<table>
<thead><tr><th>Area</th><th>Where</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Menu bar</td><td>Top</td><td>File, Edit, Image, Layer, Type, Select, Filter, View, Window, Help</td></tr>
<tr><td>Options bar</td><td>Under the menu bar</td><td>Settings that change with the tool you picked</td></tr>
<tr><td>Toolbar</td><td>Left edge</td><td>Move, selection, crop, brush, healing, type, shape tools and more</td></tr>
<tr><td>Document window</td><td>Centre</td><td>Your image, with a tab showing its name and zoom</td></tr>
<tr><td>Panels</td><td>Right side</td><td>Layers, Properties, Adjustments, Colour, History and others</td></tr>
</tbody>
</table>
<p>Some tools are grouped under one icon. A small triangle in the corner means more tools are hidden: <strong>click and hold</strong> (or right-click) the icon to see them. Hover over a tool to see its name and shortcut letter.</p>

<h3>Essential tools and shortcuts</h3>
<table>
<thead><tr><th>Tool</th><th>Shortcut</th></tr></thead>
<tbody>
<tr><td>Move</td><td>V</td></tr>
<tr><td>Rectangular Marquee</td><td>M</td></tr>
<tr><td>Lasso</td><td>L</td></tr>
<tr><td>Quick Selection / Object Selection</td><td>W</td></tr>
<tr><td>Crop</td><td>C</td></tr>
<tr><td>Brush</td><td>B</td></tr>
<tr><td>Healing tools</td><td>J</td></tr>
<tr><td>Type</td><td>T</td></tr>
<tr><td>Hand (move the view)</td><td>H, or hold Space</td></tr>
<tr><td>Zoom</td><td>Z, or Ctrl and + or - (Cmd and + or -)</td></tr>
</tbody>
</table>
<p>The toolbar has changed a little between versions, so if a tool is not where you expect, look for it by name or use the search icon at the top right of the window.</p>

<h3>Workspaces</h3>
<p>A <strong>workspace</strong> is a saved arrangement of panels. Open <strong>Window > Workspace</strong> and try <strong>Essentials</strong>, <strong>Photography</strong> and <strong>Graphic and Web</strong>. If you mess up your layout, choose <strong>Window > Workspace > Reset Essentials</strong>. To open any missing panel, use the <strong>Window</strong> menu and tick it. You can drag a panel by its tab to a new place, and save your layout with <strong>New Workspace</strong>.</p>

<h3>Preferences worth setting</h3>
<ol>
<li>Open <strong>Edit > Preferences > General</strong> on Windows (<strong>Photoshop menu > Settings</strong> on Mac).</li>
<li>Under <strong>Performance</strong>, make sure Photoshop may use most of your memory (around 70 percent is a common setting) and check the scratch disk is on a drive with free space.</li>
<li>Under <strong>Units and Rulers</strong>, choose pixels for web work or centimetres or millimetres for print.</li>
<li>Under <strong>File Handling</strong>, keep automatic recovery saving on, so a power cut costs you less. This matters in Uganda where power can drop suddenly.</li>
</ol>

<h3>Moving around your image</h3>
<ul>
<li>Zoom in and out with Ctrl and + or - (Cmd on Mac), or hold Alt (Option) and scroll the mouse wheel.</li>
<li>Press Ctrl+0 (Cmd+0) to fit the image to the screen and Ctrl+1 (Cmd+1) to see 100 percent.</li>
<li>Hold Space and drag to pan.</li>
</ul>

<blockquote><p><strong>Quick check:</strong> You closed the Layers panel by accident. How do you get it back?</p><p>Answer: Choose Window > Layers (or press F7), because every panel can be reopened from the Window menu.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> The options bar shows different settings after you pick the Type tool. Why?</p><p>Answer: The options bar always shows the settings of the active tool, so it changes when you switch tools.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open any photo. Switch to <strong>Window > Workspace > Photography</strong>, then back to <strong>Essentials</strong>.</li>
<li>Hover over six toolbar icons and write the name and shortcut of each.</li>
<li>Open <strong>Preferences</strong> and note your scratch disk and memory setting.</li>
<li>Drag the Layers panel to a new place, then use <strong>Reset Essentials</strong> to restore it. Take a screenshot of your tidy workspace.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>The options bar shows settings for the tool you are using.</li>
<li>Hidden tools sit under grouped icons; click and hold to reveal them.</li>
<li>Window > Workspace saves and resets layouts; the Window menu reopens panels.</li>
<li>Set units and recovery in Preferences before you start serious work.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'New documents: size, resolution, units and colour mode',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to create a new document with the right pixel size, resolution and colour mode for the job. This matters because a wrong start cannot be fully repaired later: a tiny image stretched for a banner will look blurry, and a design saved in the wrong colour mode can look dull.</p>

<h3>Creating a document</h3>
<ol>
<li>Choose <strong>File > New</strong> (Ctrl+N, Cmd+N).</li>
<li>Pick a preset tab such as <strong>Photo</strong>, <strong>Print</strong>, <strong>Web</strong> or <strong>Mobile</strong>, or type your own numbers in the panel on the right.</li>
<li>Set <strong>Width</strong>, <strong>Height</strong> and <strong>Units</strong>, then <strong>Resolution</strong>, <strong>Colour Mode</strong> and <strong>Background Contents</strong>.</li>
<li>Click <strong>Create</strong>.</li>
</ol>

<h3>Pixels, resolution and PPI</h3>
<p><strong>Resolution</strong> means pixels per inch (PPI): how many pixels are packed into one inch of a printed image. On screen, only the <em>total pixel size</em> matters; the PPI number is ignored. In print, more pixels per inch means sharper results.</p>
<table>
<thead><tr><th>Use</th><th>Typical size</th><th>Resolution</th><th>Colour mode</th></tr></thead>
<tbody>
<tr><td>Facebook or Instagram square post</td><td>1080 x 1080 px</td><td>72 PPI (not important)</td><td>RGB</td></tr>
<tr><td>YouTube thumbnail</td><td>1280 x 720 px</td><td>72 PPI</td><td>RGB</td></tr>
<tr><td>A4 flyer for printing</td><td>210 x 297 mm</td><td>300 PPI</td><td>CMYK if the printer asks</td></tr>
<tr><td>A3 poster</td><td>297 x 420 mm</td><td>150 to 300 PPI</td><td>CMYK if the printer asks</td></tr>
</tbody>
</table>
<p>Worked example: an A4 flyer at 300 PPI needs about 2480 x 3508 pixels (8.27 x 11.69 inches times 300). A phone photo of 4000 x 3000 pixels is plenty for a flyer. A 600 x 400 pixel image saved from WhatsApp is not, which is why printed WhatsApp photos look soft.</p>
<p>Platform sizes change from time to time, so check the platform's current help page before you design.</p>

<h3>Colour modes</h3>
<ul>
<li><strong>RGB</strong> (red, green, blue): light-based colour for screens. Use it for everything shown on phones, TVs and the web, and for most editing, because all filters work and colours are brightest.</li>
<li><strong>CMYK</strong> (cyan, magenta, yellow, black): ink-based colour for printing presses. Colours are less bright. Convert at the end, and only if your printer asks for it. Many digital print shops accept RGB and convert themselves, so ask.</li>
<li><strong>Grayscale</strong>: black and white images only.</li>
</ul>
<p>Change mode with <strong>Image > Mode</strong>. Check the current size with <strong>Image > Image Size</strong> (Alt+Ctrl+I, Option+Cmd+I) and the working area with <strong>Image > Canvas Size</strong> (Alt+Ctrl+C, Option+Cmd+C).</p>

<h3>Colour profiles in one minute</h3>
<p>A <strong>colour profile</strong> tells software how to interpret the numbers for a colour. For screen work, <strong>sRGB</strong> is the safe standard, because phones, browsers and social media assume it. Keep sRGB unless you have a reason to change it. Printers may give you a specific CMYK profile; ask for it. Under <strong>Edit > Color Settings</strong> you can see the working spaces; for beginners, leave the defaults.</p>

<h3>Canvas Size versus Image Size</h3>
<table>
<thead><tr><th>Command</th><th>What changes</th></tr></thead>
<tbody>
<tr><td>Image Size</td><td>The whole picture is scaled bigger or smaller (resampled)</td></tr>
<tr><td>Canvas Size</td><td>The working area grows or shrinks; the picture stays the same size, adding blank space or cropping</td></tr>
</tbody>
</table>

<blockquote><p><strong>Quick check:</strong> You need a poster to print at A4. Which is better: 1080 x 1080 pixels at 300 PPI, or 2480 x 3508 pixels at 300 PPI?</p><p>Answer: The second, because it has enough pixels to fill A4 at 300 PPI, while the first would be stretched and look soft.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which colour mode do you choose for an Instagram post?</p><p>Answer: RGB, because social media is viewed on screens, which make colour from light.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a new document: 1080 x 1080 px, 72 PPI, RGB, white background. Save it as <code>test-square.psd</code>.</li>
<li>Create a second document: A4 (210 x 297 mm), 300 PPI, RGB. Open <strong>Image > Image Size</strong> and write down the pixel size.</li>
<li>Open one of your own photos and check its size with <strong>Image > Image Size</strong>. Work out its largest print size at 300 PPI.</li>
<li>Write a three-line cheat sheet of sizes for your most common jobs and keep it for later lessons.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Set size, resolution and mode at the start of a job, based on where it will be used.</li>
<li>Screens care about pixel size; print cares about pixels per inch (about 300 for sharp print).</li>
<li>Use RGB and sRGB for screens; convert to CMYK only when the printer asks.</li>
<li>Image Size scales the picture; Canvas Size changes the working area.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Undo, History and saving: PSD, JPG, PNG, TIFF, WebP and PDF',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to undo mistakes safely, save a working file you can edit again, and choose the right file format for each purpose. This matters because the biggest beginner disaster is saving over the only copy as a flat JPG and losing all the layers.</p>

<h3>Undoing mistakes</h3>
<ul>
<li><strong>Ctrl+Z (Cmd+Z)</strong> undoes the last change. Use <strong>Alt+Ctrl+Z (Option+Cmd+Z)</strong> to step back further and <strong>Shift+Ctrl+Z (Shift+Cmd+Z)</strong> to step forward. (In some versions or settings Ctrl+Z toggles between undo and redo, so look at the Edit menu to see which you have.)</li>
<li>The <strong>History</strong> panel (<strong>Window > History</strong>) lists your recent actions. Click an earlier step to return to it. By default it keeps about 50 steps; change this in <strong>Edit > Preferences > Performance</strong>.</li>
<li>Take a <strong>snapshot</strong> (the camera icon at the bottom of the History panel) before a risky edit. You can return to it any time during the session.</li>
<li><strong>File > Revert</strong> goes back to the last saved version.</li>
</ul>
<p>History is cleared when you close the file. Real safety comes from non-destructive editing, which you will learn in the next modules.</p>

<h3>Saving the working file</h3>
<p>Use <strong>File > Save As</strong> (Shift+Ctrl+S, Shift+Cmd+S) and choose <strong>Photoshop (*.PSD)</strong>. A PSD keeps all layers, masks, text and adjustments, so you can edit it again. It is your master file. Never delete it. If you use <strong>Save a Copy</strong>, you create another file and leave your working document unchanged.</p>

<h3>Choosing a file format</h3>
<table>
<thead><tr><th>Format</th><th>Best for</th><th>Notes</th></tr></thead>
<tbody>
<tr><td>PSD</td><td>Your editable master file</td><td>Keeps layers; large; opens in Photoshop and some other programs</td></tr>
<tr><td>JPG</td><td>Photos for web, email, WhatsApp</td><td>Small, lossy: quality drops each time you save again; no transparency</td></tr>
<tr><td>PNG</td><td>Logos, screenshots, graphics needing transparency</td><td>Lossless, larger for photos</td></tr>
<tr><td>WebP</td><td>Web images where small size matters</td><td>Small files with transparency; some old software cannot open it</td></tr>
<tr><td>TIFF</td><td>High-quality print and archives</td><td>Very large; can keep layers or be flat</td></tr>
<tr><td>PDF</td><td>Print-ready documents, sharing a design</td><td>Can hold images and text; printers often ask for it</td></tr>
</tbody>
</table>
<p><strong>Lossy</strong> means some detail is thrown away to make the file smaller. <strong>Lossless</strong> keeps every pixel.</p>

<h3>A safe naming habit</h3>
<p>Name files clearly with the job and version, for example <code>youth-camp-poster-v1.psd</code> and <code>youth-camp-poster-v2.psd</code>. Do not call files "final", "final2", "FINAL-real". Save a new version before big changes.</p>

<h3>Mini-case</h3>
<p>Peter edited a wedding photo, saved it as a JPG over the original PSD, and the couple later asked him to remove the guest in the corner. He had lost his layers and had to start again. After that he always saved <code>name-master.psd</code> first and exported JPG copies into a separate folder.</p>

<blockquote><p><strong>Quick check:</strong> A client wants changes next week. Which file must you keep?</p><p>Answer: The PSD, because it still has all the layers, text and adjustments that a flat JPG has thrown away.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You have a logo with a transparent background to place on a website. Which format?</p><p>Answer: PNG (or WebP), because JPG cannot store transparency.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open a photo and make three edits, such as a crop, a brush stroke on a new layer and a text layer.</li>
<li>Open the <strong>History</strong> panel, click an earlier step, then click the last step to restore it.</li>
<li>Save the file as <code>practice-v1.psd</code>.</li>
<li>Use <strong>File > Save a Copy</strong> to make a JPG and a PNG. Compare their file sizes in File Explorer (Finder on Mac) and note them.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Undo steps, the History panel and snapshots help you recover during a session only.</li>
<li>Save your master as a PSD and keep it; export copies for sharing.</li>
<li>JPG for photos, PNG or WebP for transparency, TIFF or PDF for print.</li>
<li>Use clear names with version numbers.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: You are designing a flyer that will be printed at A4 and you want it sharp. Which setup is the best choice?
- 1080 x 1080 pixels at 72 PPI
* About 2480 x 3508 pixels at 300 PPI
- 600 x 400 pixels at 300 PPI
- 3508 x 2480 pixels at 10 PPI in a square document
= A4 at 300 PPI needs about 2480 x 3508 pixels. The other sizes do not have enough pixels for a sharp print.

Q: Which colour mode should you normally use for a social media post?
- CMYK
- Grayscale
* RGB
- Bitmap
= Screens use light, which is RGB. CMYK is for ink on paper.

Q: What is the difference between Image Size and Canvas Size?
* Image Size scales the picture; Canvas Size changes the working area around it or crops it
- They are two names for the same command
- Image Size changes colours; Canvas Size changes file format
- Canvas Size always makes the picture sharper
= Image Size resamples the whole picture. Canvas Size adds or removes space without scaling the picture.

Q: You will need to edit a design again next week. Which file do you keep above all?
- A JPG saved at low quality
- A PNG of the final result
* A PSD with layers
- A screenshot of the design
= A PSD keeps layers, masks and text. Flat copies cannot be edited easily.

Q: You see a small triangle in the corner of a toolbar icon. What does it mean?
- The tool is broken
- The tool is switched off
* More tools are grouped under that icon
- The tool needs a subscription upgrade
= Click and hold, or right-click, to reveal the grouped tools.

Q: Which file formats are lossless or can keep transparency and are good for a logo on a website? (choose all that apply)
* PNG
- JPG
* WebP
- A low-quality JPG
= PNG and WebP can store transparency. JPG is lossy and has no transparency.

Q: Why is it risky to rely only on the History panel to protect your work?
- History deletes your layers
* It is limited to a number of steps and is cleared when you close the file
- It slows the computer down permanently
- It only works for text
= History is a short-term safety net. Keep versioned PSD files and edit non-destructively.

Q: A friend offers a free copy of Photoshop from an unknown website. What is the best response?
- Install it and disable the antivirus first
- Install it only on a school computer
* Decline; use the official free trial or a free tool such as Photopea instead
- Share it with the whole class
= Unofficial copies are illegal and often hide malware. Free legal options exist.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Layers, selections and masks',
            'summary' => 'Build and organise layers, make accurate selections and use masks to hide and reveal without destroying anything.',
            'lessons' => [
                [
                    'title'   => 'The Layers panel in depth: types, blend modes, opacity, groups and styles',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a design from separate layers, change how layers mix with blend modes and opacity, organise them into groups and add simple layer styles. This matters because layers are the heart of Photoshop: they let you change one part of a design without touching the rest.</p>

<h3>What layers are</h3>
<p>Imagine a stack of clear plastic sheets. Each sheet carries part of the picture, and you see them all stacked. Open the <strong>Layers</strong> panel with <strong>Window > Layers</strong> (F7). The top layer in the list sits in front. Drag layers up or down to change the order. Click the <strong>eye icon</strong> to hide or show one.</p>

<h3>Types of layers</h3>
<table>
<thead><tr><th>Layer type</th><th>Look in the panel</th><th>Use</th></tr></thead>
<tbody>
<tr><td>Pixel layer</td><td>A thumbnail of the picture</td><td>Photos and painted pixels</td></tr>
<tr><td>Type layer</td><td>A capital T</td><td>Editable text</td></tr>
<tr><td>Shape layer</td><td>A shape thumbnail</td><td>Sharp vector shapes</td></tr>
<tr><td>Adjustment layer</td><td>A half-black circle icon</td><td>Colour and tone changes you can edit later</td></tr>
<tr><td>Smart object</td><td>A small page badge</td><td>Protects the original; allows smart filters</td></tr>
<tr><td>Group</td><td>A folder</td><td>Keeps related layers together</td></tr>
</tbody>
</table>
<p>Create a new empty layer with the button at the bottom of the panel or <strong>Shift+Ctrl+N (Shift+Cmd+N)</strong>. Duplicate the selected layer with <strong>Ctrl+J (Cmd+J)</strong>. A layer named "Background" is locked; double-click it and click OK to turn it into a normal layer.</p>

<h3>Opacity and fill</h3>
<p><strong>Opacity</strong> (top of the panel) makes a layer more see-through, from 100 percent (solid) to 0 (invisible). <strong>Fill</strong> works similarly but leaves layer styles like shadows at full strength. With the Move tool active, pressing a number key sets opacity quickly: 5 for 50 percent.</p>

<h3>Blend modes</h3>
<p>A <strong>blend mode</strong> decides how a layer mixes with the layers below. Use the drop-down menu that says "Normal" at the top of the panel.</p>
<table>
<thead><tr><th>Mode</th><th>Effect</th><th>Typical use</th></tr></thead>
<tbody>
<tr><td>Multiply</td><td>Darkens; white disappears</td><td>Shadows, paper textures, dark overlays</td></tr>
<tr><td>Screen</td><td>Lightens; black disappears</td><td>Light glows, smoke, sparks</td></tr>
<tr><td>Overlay</td><td>Boosts contrast and colour</td><td>Texture, colour tint, dodge and burn</td></tr>
<tr><td>Soft Light</td><td>A gentler overlay</td><td>Subtle colour and light</td></tr>
<tr><td>Color</td><td>Applies colour but keeps lightness</td><td>Recolouring clothes or hair</td></tr>
</tbody>
</table>
<p>Hover over the modes in the list and watch your image preview change. You can also cycle through modes by holding Shift and pressing + or - with the Move tool selected.</p>

<h3>Groups and names</h3>
<p>Select several layers (Ctrl or Cmd click, or Shift click for a range) and press <strong>Ctrl+G (Cmd+G)</strong> to put them in a group. Double-click a layer name to rename it. A file with 30 layers called "Layer 14 copy 3" is a trap. Name layers as you go: "Logo", "Title", "Photo-student".</p>

<h3>Layer styles</h3>
<p>Click the <strong>fx</strong> button at the bottom of the Layers panel to add <strong>Drop Shadow</strong>, <strong>Stroke</strong>, <strong>Outer Glow</strong> and more. Styles stay editable: double-click the effect name to change it. A good shadow is subtle: 30 to 50 percent opacity, a small distance and a soft blur, not a heavy black blob.</p>

<h3>Mini-case</h3>
<p>Sarah is designing a poster for a church youth night. She puts the photo on one layer, a dark purple rectangle above it set to <strong>Multiply</strong> at 60 percent, and white text on top with a soft shadow. Because everything is on its own layer, she can change the colour of the rectangle later in a second.</p>

<blockquote><p><strong>Quick check:</strong> You want white parts of a texture layer to disappear and the dark grain to darken the photo underneath. Which blend mode?</p><p>Answer: Multiply, because it darkens and makes white transparent.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> How do you copy the selected layer quickly?</p><p>Answer: Press Ctrl+J (Cmd+J) to duplicate the layer onto a new layer above it.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open a photo. Add a new layer and fill it with a colour (<strong>Edit > Fill</strong>, or the Paint Bucket).</li>
<li>Change its blend mode to Multiply, then Overlay, then Soft Light, and note how each looks.</li>
<li>Add a text layer, then use <strong>fx > Drop Shadow</strong> with soft settings.</li>
<li>Rename every layer and group the colour and text layers into a group called "Overlay". Save as <code>layers-practice.psd</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Layers stack like transparent sheets; the top layer is in front.</li>
<li>Opacity controls see-through; blend modes control how layers mix.</li>
<li>Group and name layers to stay organised.</li>
<li>Layer styles (fx) stay editable; keep them subtle.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Making selections: marquee, lasso, Quick Selection and Select Subject',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to pick the right selection tool and select a shape, an object or a person quickly. This matters because almost every edit, from brightening one face to changing a shirt colour, begins with choosing which pixels to affect.</p>

<h3>What a selection is</h3>
<p>A <strong>selection</strong> is a moving dotted line (nicknamed "marching ants") around an area. Edits you make then affect only that area. Remove it with <strong>Select > Deselect</strong> (Ctrl+D, Cmd+D). Bring it back with <strong>Select > Reselect</strong> (Shift+Ctrl+D, Shift+Cmd+D).</p>

<h3>Choosing the right tool</h3>
<table>
<thead><tr><th>Tool</th><th>Best for</th><th>How</th></tr></thead>
<tbody>
<tr><td>Rectangular and Elliptical Marquee (M)</td><td>Boxes and circles</td><td>Drag; hold Shift for a perfect square or circle; hold Alt (Option) to draw from the centre</td></tr>
<tr><td>Lasso (L)</td><td>Rough freehand shapes</td><td>Drag around the shape</td></tr>
<tr><td>Polygonal Lasso</td><td>Straight-edged objects like buildings and signs</td><td>Click corner to corner, double-click to finish</td></tr>
<tr><td>Magnetic Lasso</td><td>Objects with a clear edge</td><td>Move along the edge; it snaps</td></tr>
<tr><td>Quick Selection (W)</td><td>Objects you paint over</td><td>Brush over the object; it finds edges</td></tr>
<tr><td>Object Selection (W)</td><td>Drag a box around an object</td><td>Photoshop finds the object inside</td></tr>
<tr><td>Select Subject</td><td>The main person or object, in one click</td><td>Select > Subject, or the button in the options bar</td></tr>
</tbody>
</table>
<p>Look for the Quick Selection and Object Selection tools by name, because in current versions they share one toolbar slot and the exact order may differ.</p>

<h3>Adding, subtracting and refining</h3>
<ul>
<li>Hold <strong>Shift</strong> while selecting to <em>add</em> to the selection; hold <strong>Alt (Option)</strong> to <em>subtract</em>. The options bar also has buttons for these.</li>
<li>Use <strong>Select > Modify > Expand, Contract, Feather and Smooth</strong> to grow, shrink, soften or smooth the edge. A feather of 1 or 2 pixels removes harsh edges; a big feather makes a soft glow shape.</li>
<li>Use <strong>Select > Inverse</strong> (Shift+Ctrl+I, Shift+Cmd+I) to select everything except what is selected. A common trick: select the plain sky, then invert to get the buildings.</li>
</ul>

<h3>Worked example: brighten only a face</h3>
<ol>
<li>Select the Lasso and draw loosely around the face.</li>
<li>Choose <strong>Select > Modify > Feather</strong> and enter 20 pixels, so the edit blends.</li>
<li>Click the half-black circle at the bottom of the Layers panel and choose <strong>Curves</strong> or <strong>Brightness/Contrast</strong>. The selection automatically becomes a mask for that adjustment layer.</li>
<li>Lift the curve slightly. Only the face brightens, smoothly.</li>
</ol>

<h3>Mini-case</h3>
<p>Brian runs a small shop and takes a phone photo of a pair of shoes. He clicks <strong>Select Subject</strong>, sees the shoes outlined, then holds Alt with Quick Selection to remove a bit of floor that was included. In a minute he has a clean selection, ready to move onto a plain background.</p>

<blockquote><p><strong>Quick check:</strong> You selected a plain blue sky and want the buildings instead. What do you do?</p><p>Answer: Choose Select > Inverse (Shift+Ctrl+I), since the buildings are everything that is not sky.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A selection edge looks harsh and jagged when you brighten it. Which setting helps?</p><p>Answer: Feather the selection (Select > Modify > Feather) by a few pixels so the edit blends into its surroundings.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open a photo with a clear main subject.</li>
<li>Try three tools on it: Quick Selection, Object Selection and <strong>Select > Subject</strong>. Note which gave the best result.</li>
<li>Fix any mistakes using Shift to add and Alt (Option) to subtract.</li>
<li>With the selection active, add a <strong>Hue/Saturation</strong> adjustment layer and move the saturation slider. Take a screenshot of the result.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Choose the tool to fit the shape: marquee for boxes, lasso for rough shapes, Quick Selection, Object Selection or Select Subject for objects.</li>
<li>Shift adds and Alt (Option) subtracts; Select > Inverse flips the selection.</li>
<li>Feather and Modify tools soften and adjust edges.</li>
<li>Always remember to Deselect (Ctrl+D) when finished.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Select and Mask, Color Range and refining edges',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to select difficult things such as hair, trees and sky using Select and Mask and Color Range. This matters because rough cut-outs with jagged, white-edged outlines are the clearest sign of an amateur edit.</p>

<h3>Color Range: select by colour</h3>
<p>Choose <strong>Select > Color Range</strong>. In the dialog, click with the eyedropper on the colour you want in your image, such as a blue sky. Use the <strong>Fuzziness</strong> slider to widen or narrow the range. Hold Shift and click to add more shades. Click OK to turn it into a selection.</p>
<p>The same dialog has quick options, and in current versions you can choose <strong>Sky</strong>, <strong>Skin Tones</strong> and <strong>Highlights</strong> from the menu at the top. Look for them by name.</p>
<p>Color Range is great for pulling out a blue sky, a green background or all the red items in a product photo.</p>

<h3>Select and Mask workspace</h3>
<p>With a selection active, click <strong>Select and Mask</strong> in the options bar, or choose it from <strong>Select > Select and Mask</strong> (Alt+Ctrl+R, Option+Cmd+R). A special workspace opens.</p>
<ol>
<li>Choose a <strong>View Mode</strong> (for example, on black or overlay) to see the edges against a contrasting background.</li>
<li>Use the <strong>Refine Edge Brush</strong> and paint over hair and fuzzy edges. Photoshop works out which strands belong to the subject.</li>
<li>Adjust <strong>Smooth</strong> (reduces jaggies), <strong>Feather</strong> (softens), <strong>Contrast</strong> (sharpens) and <strong>Shift Edge</strong> (shrinks or grows the selection a little).</li>
<li>Tick <strong>Decontaminate Colors</strong> if you see a coloured fringe from the old background.</li>
<li>Set <strong>Output To</strong> to <strong>Layer Mask</strong> (best, because it is non-destructive) and click OK.</li>
</ol>
<p>Output choices differ in safety:</p>
<table>
<thead><tr><th>Output To</th><th>Result</th><th>Safe to change later?</th></tr></thead>
<tbody>
<tr><td>Selection</td><td>Only a selection</td><td>Yes</td></tr>
<tr><td>Layer Mask</td><td>Adds a mask to the layer</td><td>Yes, repaint the mask</td></tr>
<tr><td>New Layer with Layer Mask</td><td>Copy with mask; original stays</td><td>Yes, safest</td></tr>
<tr><td>New Layer (no mask)</td><td>Pixels deleted outside</td><td>No, destructive</td></tr>
</tbody>
</table>

<h3>Free alternatives</h3>
<p>Photopea has <strong>Select > Color Range</strong> and a refine option for selections. GIMP has <strong>Select > By Color</strong>, the Fuzzy Select tool and a Foreground Select tool for cut-outs. Results with hair may need more manual work in free tools.</p>

<h3>Tips for better edges</h3>
<ul>
<li>Zoom to 100 percent or more when judging edges.</li>
<li>Check the cut-out against a dark and a light background, because halos show on one or the other.</li>
<li>Photographs taken against a plain, evenly lit background are far easier to cut out. Teach your photographer friends to do that.</li>
</ul>

<h3>Mini-case</h3>
<p>Grace has a studio-style portrait taken in front of a green cloth. She uses Color Range to pick the green, inverts the selection to keep the person, then opens Select and Mask, paints over hair with the Refine Edge Brush, ticks Decontaminate Colors and outputs to a layer mask. The cut-out lays cleanly on a new yellow background.</p>

<blockquote><p><strong>Quick check:</strong> Why choose Output To: Layer Mask instead of New Layer?</p><p>Answer: A layer mask lets you repaint and fix the edge later, while deleting pixels cannot be undone after you save.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Your cut-out has a light fringe from the old sky. Which Select and Mask option helps?</p><p>Answer: Decontaminate Colors, which replaces fringe colours with colours from the subject.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open a photo with a person or object and a fairly plain background.</li>
<li>Make a first selection with Select Subject or Color Range.</li>
<li>Open <strong>Select and Mask</strong>, try the Refine Edge Brush on any hair, and output to a New Layer with Layer Mask.</li>
<li>Put a solid colour layer beneath it and view the cut-out. Save as <code>cutout-practice.psd</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Color Range selects by colour; widen or narrow it with Fuzziness.</li>
<li>Select and Mask refines hard edges such as hair.</li>
<li>Output to a layer mask to stay non-destructive.</li>
<li>Test your edge against light and dark backgrounds.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Layer masks and clipping masks: hide without deleting',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to hide and reveal parts of a layer using layer masks, and fit an image inside a shape using clipping masks. This matters because masks let you change your mind: the picture is never erased, only hidden.</p>

<h3>Layer masks</h3>
<p>A <strong>layer mask</strong> is a black, white and grey picture attached to a layer. The golden rule: <strong>white reveals, black hides, grey is partly see-through</strong>. A good memory trick is "white shows, black goes".</p>
<ol>
<li>Select the layer.</li>
<li>Click the <strong>Add Layer Mask</strong> button at the bottom of the Layers panel (a rectangle with a circle). A white thumbnail appears next to the layer.</li>
<li>Pick the <strong>Brush</strong> (B). Press <strong>D</strong> to reset the colours to black and white and <strong>X</strong> to swap them.</li>
<li>Make sure the <em>mask thumbnail</em> (not the picture) is selected, then paint with black to hide and with white to bring back.</li>
</ol>
<p>Use a soft-edged brush (set <strong>Hardness</strong> to 0 percent in the brush settings) for smooth blends and a hard brush for crisp edges. Lower the brush <strong>Opacity</strong> to build up a gentle fade.</p>

<h3>Useful mask actions</h3>
<table>
<thead><tr><th>Action</th><th>How</th></tr></thead>
<tbody>
<tr><td>Hide the mask temporarily</td><td>Hold Shift and click the mask thumbnail</td></tr>
<tr><td>Look at the mask itself</td><td>Alt (Option) click the mask thumbnail</td></tr>
<tr><td>Invert black and white</td><td>Select the mask, then press Ctrl+I (Cmd+I)</td></tr>
<tr><td>Unlink picture and mask</td><td>Click the chain icon between them</td></tr>
<tr><td>Make a mask from a selection</td><td>Make the selection, then click Add Layer Mask</td></tr>
<tr><td>Make a smooth fade</td><td>Use the Gradient tool (G) set from black to white on the mask</td></tr>
</tbody>
</table>

<h3>Worked example: blend two photos</h3>
<ol>
<li>Place a photo of a crowd on top of a photo of a stage in one document, both on separate layers.</li>
<li>Add a layer mask to the top layer.</li>
<li>Choose the Gradient tool, set it to black-to-white, and drag across the mask from the edge where you want the top picture to disappear.</li>
<li>The photos now melt into each other. Painting with white or black fixes any spot.</li>
</ol>

<h3>Clipping masks</h3>
<p>A <strong>clipping mask</strong> makes one layer show only inside the shape of the layer below it. Place a photo layer directly above a shape or text layer, then right-click the photo layer and choose <strong>Create Clipping Mask</strong> (or press <strong>Alt+Ctrl+G, Option+Cmd+G</strong>). The photo appears only inside the shape. Photos inside big letters and photos in circles for team profiles are made this way.</p>
<p>Clipping masks are also how you apply an adjustment layer to a <em>single</em> layer rather than everything beneath it. With the adjustment layer selected, click the small clip icon at the bottom of the Properties panel.</p>

<h3>Free alternatives</h3>
<p>Photopea works like Photoshop here: the mask button is at the bottom of the Layers panel. GIMP uses layer masks too: right-click a layer and choose <strong>Add Layer Mask</strong>.</p>

<h3>Mini-case</h3>
<p>A youth group wants a team page with round profile photos. The designer draws a circle with the Ellipse tool, places each member's photo above it, and clips it. When one photo needs moving, she drags the photo inside the circle; the circle stays still.</p>

<blockquote><p><strong>Quick check:</strong> You painted too much away on a layer mask. How do you bring it back?</p><p>Answer: Switch the brush colour to white (press X) and paint over the area, because white reveals the layer.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You want a photo to show only inside a heart shape. Which feature?</p><p>Answer: A clipping mask, placing the photo directly above the heart shape layer and clipping it.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open two photos in one document on separate layers.</li>
<li>Add a layer mask to the top one and use a black-to-white gradient to blend them.</li>
<li>Add an Ellipse shape on a new layer, place a third photo above it, and create a clipping mask.</li>
<li>Save as <code>masks-practice.psd</code> and take a screenshot of the Layers panel showing masks.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>On a layer mask, white reveals, black hides and grey is partly visible.</li>
<li>Paint on the mask thumbnail, not the picture; D and X reset and swap colours.</li>
<li>Clipping masks fit a layer into the shape of the layer below.</li>
<li>Masks hide pixels; they do not delete them, so you can always repaint.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: On a layer mask, which colour hides the layer?
- White
* Black
- Red
- Transparent
= Black hides, white reveals, and grey makes the layer partly see-through.

Q: You want a dark paper texture to darken a photo while its white areas disappear. Which blend mode fits?
- Screen
- Color
* Multiply
- Normal at 100 percent
= Multiply darkens and makes white vanish. Screen does the opposite.

Q: You have a sky selected and want to edit only the buildings. What is the quickest correct step?
- Select > Deselect
* Select > Inverse
- Select > Feather with 0 pixels
- Delete the sky layer
= Inverse flips the selection to everything that was not selected.

Q: You are cutting out a person with fine hair. Which workspace is built for this?
- Image > Canvas Size
* Select and Mask
- Filter > Blur
- File > Revert
= Select and Mask has the Refine Edge Brush and Decontaminate Colors for hair and fuzzy edges.

Q: Which Output To setting keeps your original pixels safe and lets you fix the edge later?
- New Layer, with no mask
- Selection, then press Delete
* New Layer with Layer Mask
- Flatten Image
= A layer with a mask hides rather than deletes pixels. Deleting pixels is permanent after saving.

Q: What does a clipping mask do?
- Deletes everything outside a selection
- Makes the layer smaller in file size
* Shows a layer only within the shape of the layer below
- Converts a layer to CMYK
= A clipping mask fits a photo inside a shape or text without destroying either.

Q: Why is it a good habit to rename layers and put them in groups?
- Photoshop will not save without names
* A complex file stays easy to understand and edit later
- It makes the file smaller by half
- Groups remove blend modes
= Good organisation saves time, especially when a client asks for changes.

Q: While holding Shift, you drag a selection tool over an image that already has a selection. What happens?
- The selection is subtracted
* The new area is added to the existing selection
- The selection is feathered
- The layer is deleted
= Shift adds to a selection; Alt (Option) subtracts.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Correcting and retouching photos',
            'summary' => 'Crop and resize properly, fix colour and tone with adjustment layers, and retouch honestly with healing, clone and dodge and burn.',
            'lessons' => [
                [
                    'title'   => 'Cropping, straightening, resizing and resampling correctly',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to crop a photo for a purpose, straighten a crooked horizon and resize an image without ruining its quality. This matters because a good crop is often the quickest improvement you can make, and a badly enlarged image is a common reason that prints look blurry.</p>

<h3>Cropping</h3>
<ol>
<li>Select the <strong>Crop</strong> tool (C).</li>
<li>In the options bar, choose a ratio such as <strong>1:1</strong> (square), <strong>4:5</strong> (portrait for Instagram), <strong>16:9</strong> (thumbnails and video) or <strong>W x H x Resolution</strong> to type your own.</li>
<li>Drag the corner handles; drag inside the box to move the picture. Hold Shift to keep the ratio if it is not already locked.</li>
<li>Press <strong>Enter</strong> to apply or <strong>Esc</strong> to cancel.</li>
</ol>
<p>Leave <strong>Delete Cropped Pixels</strong> <em>unticked</em> in the options bar. Then the cropped-away parts are only hidden, and you can click the Crop tool again and drag the edges back out. This is non-destructive cropping.</p>
<p>Crop with composition in mind: put the subject off-centre along the thirds, remove distracting objects at the edges and leave space for text if the photo will carry a headline.</p>

<h3>Straightening</h3>
<p>With the Crop tool active, click <strong>Straighten</strong> in the options bar and drag a line along something that should be level, such as the horizon or a door frame. Photoshop rotates the image to match. You can also move the cursor just outside a corner handle and drag to rotate by hand.</p>

<h3>Resizing and resampling</h3>
<p>Choose <strong>Image > Image Size</strong> (Alt+Ctrl+I, Option+Cmd+I). The key choice is the <strong>Resample</strong> tick box.</p>
<table>
<thead><tr><th>Resample setting</th><th>What happens</th><th>Use when</th></tr></thead>
<tbody>
<tr><td>Off</td><td>Pixel count stays; only the PPI and print size change</td><td>You want to know how big it can print</td></tr>
<tr><td>On</td><td>Pixels are added or removed (resampled)</td><td>You need an exact pixel size for web</td></tr>
</tbody>
</table>
<p>Making an image <strong>smaller</strong> (downsampling) keeps quality well. Making it <strong>larger</strong> (upsampling) invents pixels, so the picture gets soft. In the Resample drop-down, current versions offer methods suited to enlarging and reducing; choose the one marked for reduction when shrinking and one marked for enlargement (such as Preserve Details) when you must grow an image. A few percent bigger is fine; doubling it will usually look soft. Some versions include an AI-based enlargement option; judge the result with your own eyes.</p>

<h3>Worked example: web size</h3>
<p>A phone photo is 4000 x 3000 pixels and 5 MB. A school website wants a banner 1200 pixels wide. Open Image Size, switch Resample on, set the width to 1200 px with the chain link on so the height follows (900 px), and click OK. Export it later as JPG. The file may drop to under 300 KB, which loads quickly on mobile data.</p>

<h3>Free alternatives</h3>
<p>Photopea has the same <strong>Image > Image Size</strong> and a Crop tool with ratios. In GIMP use <strong>Image > Scale Image</strong> and the Crop tool; the interpolation menu offers options like Cubic and NoHalo.</p>

<h3>Common mistakes</h3>
<ul>
<li>Cropping with Delete Cropped Pixels ticked, then needing the edge back.</li>
<li>Enlarging a tiny image and expecting a sharp print.</li>
<li>Squashing a photo by unlinking width and height. Keep the chain linked.</li>
</ul>

<blockquote><p><strong>Quick check:</strong> You want a square Instagram crop but may change your mind later. Which crop setting protects you?</p><p>Answer: Leave Delete Cropped Pixels off, so the outside parts stay hidden and can be restored by re-opening the crop.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A 600 pixel wide picture must be printed on A3. Is enlarging in Image Size a good fix?</p><p>Answer: No, because resampling up only invents pixels and the print will look soft; find a larger original instead.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Take a phone photo with a slightly tilted horizon, or find one of your own.</li>
<li>Use the Crop tool with the Straighten option to level it, and crop it to 4:5.</li>
<li>Duplicate the file with <strong>Image > Duplicate</strong>, then resize the copy to 1200 pixels wide with Resample on.</li>
<li>Export it as a JPG and compare its size with the original. Keep the before and after.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Crop with Delete Cropped Pixels off so the crop stays reversible.</li>
<li>Use Straighten to level the horizon.</li>
<li>Shrinking an image is safe; enlarging softens it.</li>
<li>Keep width and height linked to avoid squashed pictures.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Adjustment layers: levels, curves, hue and saturation, balance and black and white',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to fix a dull or too-dark photo using adjustment layers and know which adjustment to choose. This matters because phone photos taken in harsh sun or dim rooms can often be rescued in a minute, and doing it with adjustment layers means you can change your mind at any time.</p>

<h3>Why adjustment layers</h3>
<p>The menu <strong>Image > Adjustments</strong> changes pixels permanently. An <strong>adjustment layer</strong> does the same job but sits above your photo as a separate layer, with its own mask. You can turn it off, change its strength or paint on its mask to limit it. Create one with the half-black circle at the bottom of the Layers panel, or open <strong>Window > Adjustments</strong> and click an icon. The settings appear in the <strong>Properties</strong> panel.</p>

<h3>Which adjustment for which problem</h3>
<table>
<thead><tr><th>Adjustment</th><th>Fixes</th><th>How to use it</th></tr></thead>
<tbody>
<tr><td>Brightness/Contrast</td><td>Quick overall light and contrast</td><td>Two sliders; simple but blunt</td></tr>
<tr><td>Levels</td><td>Flat, hazy photos</td><td>Drag the black and white triangles under the histogram to the edges of the data; move the grey middle triangle to adjust mid-tones</td></tr>
<tr><td>Curves</td><td>Fine control of tone and contrast</td><td>Click on the diagonal line to add points; lift to brighten, drop to darken; an S-shape adds contrast</td></tr>
<tr><td>Exposure</td><td>Under or over-exposed photos</td><td>Exposure, Offset and Gamma sliders</td></tr>
<tr><td>Vibrance</td><td>Dull colour without over-doing skin</td><td>Boosts weaker colours more than strong ones</td></tr>
<tr><td>Hue/Saturation</td><td>Colour strength or a single colour range</td><td>Use the drop-down to target Reds, Blues and so on</td></tr>
<tr><td>Color Balance</td><td>Colour cast, such as a yellow indoor photo</td><td>Shift Shadows, Midtones and Highlights toward the opposite colour</td></tr>
<tr><td>Black and White</td><td>Monochrome with control</td><td>Sliders decide how bright each colour becomes in grey</td></tr>
</tbody>
</table>
<p>The look and number of adjustment icons can change between versions; search for each adjustment by name in the Adjustments panel.</p>

<h3>The histogram in one minute</h3>
<p>The histogram is a mountain graph showing how many pixels are dark (left), mid (middle) and bright (right). Data bunched in the middle means a flat photo. Data cut off at the left or right means lost shadow or highlight detail. Levels and Curves use it as a guide.</p>

<h3>A reliable correction order</h3>
<ol>
<li>Fix tone first with <strong>Levels</strong> or <strong>Curves</strong>.</li>
<li>Remove any colour cast with <strong>Color Balance</strong> or by setting the grey point in Curves with the middle eyedropper on something neutral.</li>
<li>Add colour with <strong>Vibrance</strong>.</li>
<li>Fine-tune single colours with <strong>Hue/Saturation</strong>.</li>
<li>Check by toggling the eye icons to compare before and after.</li>
</ol>

<h3>Limit an adjustment with a mask</h3>
<p>Each adjustment layer carries a white mask. Paint on it with black to remove the effect from a region. For example, boost a sky with Curves, then paint black on the mask over the people so they are not darkened. Clip the adjustment to one layer by clicking the clip icon in the Properties panel.</p>

<h3>Free alternatives</h3>
<p>Photopea has adjustment layers in the same place at the bottom of the Layers panel. GIMP uses <strong>Colors > Levels</strong> and <strong>Colors > Curves</strong>, which are applied directly; duplicate the layer first to keep a safe copy.</p>

<h3>Mini-case</h3>
<p>A school photographer's indoor assembly photos come out yellow and dim. She adds a Curves layer to lift the mid-tones, a Color Balance layer to add a little blue, and a Vibrance layer. The same three layers are copied to the other photos in the series, so the whole set matches.</p>

<blockquote><p><strong>Quick check:</strong> Why use a Curves adjustment layer instead of Image > Adjustments > Curves?</p><p>Answer: The adjustment layer is editable and has a mask, so you can change or remove the effect later without damaging the photo.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A photo taken under yellow bulbs looks orange. Which adjustment first?</p><p>Answer: Color Balance (or Curves with a neutral grey point), because it corrects a colour cast by shifting toward the opposite colour.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open a dull photo.</li>
<li>Add a <strong>Levels</strong> layer and set the black and white sliders to the edges of the histogram.</li>
<li>Add a <strong>Color Balance</strong> layer to remove any colour cast, then a <strong>Vibrance</strong> layer.</li>
<li>Add a <strong>Black and White</strong> layer, then switch it off with the eye icon. Save as <code>correct-v1.psd</code> and export a before and after pair.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Adjustment layers are editable, maskable and non-destructive.</li>
<li>Fix tone first, then colour cast, then colour strength.</li>
<li>Levels and Curves use the histogram as a guide.</li>
<li>Paint black on an adjustment mask to limit where it applies.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Retouching tools: Spot Healing, Healing, Clone Stamp, Content-Aware and Patch',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to remove spots, scratches, dust and unwanted objects from a photo cleanly. This matters because a distracting bin in the background or a pimple on the day of a portrait can spoil an otherwise good picture, and retouching tools fix it in minutes.</p>

<h3>The golden rule: retouch on its own layer</h3>
<p>Click the <strong>New Layer</strong> button, name it "Retouch", and in the options bar of each tool tick <strong>Sample All Layers</strong> (or <strong>Current and Below</strong>, depending on version). You paint the repair on the empty layer and the photo underneath stays untouched. If you spoil it, delete or erase the retouch layer.</p>

<h3>The tools compared</h3>
<table>
<thead><tr><th>Tool</th><th>Shortcut</th><th>How it works</th><th>Best for</th></tr></thead>
<tbody>
<tr><td>Spot Healing Brush</td><td>J</td><td>Click or paint over a blemish; it samples nearby pixels automatically</td><td>Small spots, dust, pimples</td></tr>
<tr><td>Healing Brush</td><td>J (cycle)</td><td>Alt (Option) click to choose a source, then paint; texture is copied, colour and light blend with the destination</td><td>Skin and textures</td></tr>
<tr><td>Clone Stamp</td><td>S</td><td>Alt (Option) click a source; copies pixels exactly</td><td>Hard edges, patterns, fixing a repeat of a texture</td></tr>
<tr><td>Patch Tool</td><td>J (cycle)</td><td>Draw a loop around the problem, then drag it to a clean area</td><td>Larger blemishes and objects</td></tr>
<tr><td>Remove tool</td><td>J (cycle)</td><td>Brush over an unwanted object; Photoshop fills it in</td><td>Quick removal of distractions; may need clean-up</td></tr>
<tr><td>Content-Aware Fill</td><td>Edit menu</td><td>Select an area, then use <strong>Edit > Content-Aware Fill</strong> and preview</td><td>Removing objects from backgrounds</td></tr>
</tbody>
</table>
<p>Tool names and their order inside the Healing group change between versions. Hover over the icons and look for the tool by name.</p>

<h3>Removing a small object step by step</h3>
<ol>
<li>Zoom to 100 percent around the object and add a "Retouch" layer.</li>
<li>Choose the <strong>Spot Healing Brush</strong> and set its size a little larger than the object. Tick Sample All Layers.</li>
<li>Click once or paint over the object. If the result looks smudged, press Ctrl+Z (Cmd+Z) and try the Clone Stamp or Healing Brush.</li>
<li>For a bigger item, make a loose selection with the Lasso, choose <strong>Edit > Content-Aware Fill</strong>, check the green sample area preview and click OK.</li>
</ol>

<h3>Tips for clean results</h3>
<ul>
<li>Use a <strong>soft</strong> brush for skin and a <strong>hard</strong> brush for edges and patterns.</li>
<li>Clone from a similar area with the same light and direction.</li>
<li>Avoid sampling from the same spot over and over; it creates visible repeating patterns.</li>
<li>Work at 100 percent, then zoom out and check the whole picture.</li>
</ul>

<h3>Free alternatives</h3>
<p>Photopea has Spot Healing, Healing, Patch and Clone Stamp tools, similar to Photoshop. GIMP has a Healing tool, a Clone tool and Heal Selection (in newer versions). The Photoshop AI removal tools do not exist in all of them.</p>

<h3>Mini-case</h3>
<p>Ruth photographs her brother's market stall for a flyer. A stranger's shopping bag sits at the edge of the shot. She makes a Retouch layer, selects the bag loosely with the Lasso, opens Content-Aware Fill and accepts the result. A small corner needs touching up with the Clone Stamp, which she does on the same layer.</p>

<blockquote><p><strong>Quick check:</strong> Why retouch on a separate empty layer with Sample All Layers on?</p><p>Answer: The repair stays separate from the photo, so you can erase or hide it without damaging the original pixels.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You need to repair a brick wall pattern behind a removed sign. Which tool suits a sharp pattern?</p><p>Answer: The Clone Stamp, because it copies pixels exactly and keeps hard lines and patterns aligned.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a photo with a few small distractions, such as litter, wires or spots.</li>
<li>Create a layer named "Retouch" and remove three items with three different tools.</li>
<li>Toggle the Retouch layer's eye icon to compare before and after.</li>
<li>Save the PSD and export a JPG of the before and after. Add both to your portfolio folder.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Retouch on its own layer with Sample All Layers on.</li>
<li>Spot Healing for small spots; Healing for textures; Clone Stamp for patterns; Patch and Content-Aware for larger areas.</li>
<li>Choose clean source areas and avoid repeating patterns.</li>
<li>Look for tools by name because menus change between versions.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Dodge and burn, frequency separation and honest portrait retouching',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to shape light on a face or product using dodge and burn, explain what frequency separation is, and set sensible ethical limits for retouching people. This matters because heavy, fake-looking skin smoothing is easy to spot, and altering people's bodies and faces can harm real people's self-image and trust.</p>

<h3>Dodge and burn: painting light</h3>
<p><strong>Dodging</strong> lightens; <strong>burning</strong> darkens. Photographers used the terms in the darkroom. In Photoshop you can use the Dodge and Burn tools, but they change pixels directly. A safer way:</p>
<ol>
<li>Create a new layer with <strong>Layer > New > Layer</strong> and set <strong>Mode</strong> to <strong>Soft Light</strong> (or Overlay) and tick <strong>Fill with Soft-Light-neutral color (50% gray)</strong>.</li>
<li>Choose the <strong>Brush</strong> with a very soft edge and set <strong>Opacity</strong> or <strong>Flow</strong> low, around 5 to 10 percent.</li>
<li>Paint with <strong>white</strong> to lighten (dodge) and <strong>black</strong> to darken (burn). Build up gently by painting again and again.</li>
<li>Use it to lift the eyes, shape cheekbones, even out dark patches and add depth to products.</li>
</ol>
<p>Because the grey layer is separate, you can erase, lower its opacity or delete it any time. Some versions label the Soft Light option slightly differently, so look for the fill-with-neutral-colour tick box.</p>

<h3>Frequency separation: the idea</h3>
<p>Every image contains <strong>fine detail</strong> (skin texture, pores, hair) and <strong>soft colour and tone</strong> (blotchy colour, shadows). Frequency separation splits the picture into two layers: a <em>low-frequency</em> layer of smooth colour and tone, and a <em>high-frequency</em> layer of texture. You can then fix uneven colour on the low layer without wrecking the pores on the high layer. It is a professional technique for skin and for objects like fabric or wood.</p>
<p>At beginner level, you need only the concept. It is easy to overdo: painting too much on the low layer gives a plastic look. Many retouchers now prefer a lighter approach of dodge and burn plus spot healing. You can look up a step-by-step tutorial after the course if you wish.</p>

<h3>A simple honest-retouching comparison</h3>
<table>
<thead><tr><th>Fine to do</th><th>Think carefully</th><th>Do not do</th></tr></thead>
<tbody>
<tr><td>Remove a temporary pimple, dust, a stray hair</td><td>Smooth skin so much that texture vanishes</td><td>Change body shape, skin colour or age to look "better" without consent</td></tr>
<tr><td>Correct colour and light</td><td>Remove permanent features like scars or birthmarks (ask the person)</td><td>Edit news or documentary photos in ways that change the facts</td></tr>
<tr><td>Brighten tired eyes lightly</td><td>Whiten teeth and eyes (keep it natural)</td><td>Remove people or add people into a record of an event to mislead</td></tr>
</tbody>
</table>

<h3>Ethics and consent</h3>
<ul>
<li>Ask the person what they want changed. Never alter a person's image in an embarrassing or sexual way, which is harmful and may be illegal.</li>
<li>For news, evidence, ID photos and school or church records, do only basic corrections like colour and crop.</li>
<li>If a photo of a person has been edited a lot for an advert, be honest in your marketing and avoid unrealistic body images that mislead young viewers.</li>
<li>Keep the original file so the unedited photo can always be shown.</li>
</ul>

<h3>Mini-case</h3>
<p>Doreen is retouching graduation portraits. She removes a spot, lightly balances shine on the forehead with a 50 percent grey dodge and burn layer at 8 percent flow, and keeps the skin texture visible. When one student asks her to slim his face, she explains she can adjust light and colour but prefers not to change his real shape; he agrees the natural result is better.</p>

<blockquote><p><strong>Quick check:</strong> Why paint dodge and burn on a grey Soft Light layer rather than use the Dodge tool directly?</p><p>Answer: The grey layer is separate, so you can lower, erase or delete it without damaging the photo.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A newspaper photographer wants to remove a protester from a crowd photo to make the picture look neater. Is that acceptable?</p><p>Answer: No, because removing people from news photos changes the facts and breaks trust; only crop and tone corrections are acceptable there.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open a portrait or product photo.</li>
<li>Make a Soft Light layer filled with 50 percent grey, named "Dodge and Burn".</li>
<li>Paint white on highlights and black in shadows with a soft brush at 8 percent flow, building up slowly.</li>
<li>Toggle the layer to compare. Write two sentences on what you changed and what you chose not to change. Keep the screenshot.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Dodge and burn on a grey Soft Light layer shapes light non-destructively.</li>
<li>Frequency separation splits texture from colour and tone; use it lightly.</li>
<li>Keep skin texture; avoid the plastic look.</li>
<li>Respect consent and honesty: no misleading edits to news or records, and never harmful edits of people.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: You crop a photo to a square but might want the sides back later. What should you do?
- Tick Delete Cropped Pixels
* Leave Delete Cropped Pixels unticked
- Flatten the image first
- Convert to CMYK
= Without deleting, the cropped parts are only hidden and can be restored by re-opening the crop.

Q: You must make a 4000-pixel-wide photo into a 1200-pixel web image. What is the right approach?
- Use Canvas Size with a width of 1200
* Image Size with Resample on, width 1200, chain linked
- Switch to CMYK mode
- Save as PSD
= Image Size with resampling reduces the pixel count while keeping the proportions.

Q: Why is an adjustment layer better than Image > Adjustments for most work?
- It makes the file smaller
* It is editable, maskable and removable later
- It works only on text
- It converts the photo to a smart object
= Adjustment layers do not change the original pixels.

Q: A photo looks flat and hazy; the histogram is bunched in the middle. Which adjustment fits first?
- Black and White
* Levels or Curves
- Vibrance
- Hue/Saturation on Reds
= Levels and Curves fix tone by spreading the data across the range.

Q: You want to remove a spot on skin without leaving a smudge. Which tool is the quickest first try?
- Clone Stamp set to 100 percent hardness
* Spot Healing Brush
- Gradient tool
- Move tool
= The Spot Healing Brush samples nearby texture and blends tone automatically.

Q: Which tool is best for repairing a regular brick pattern after removing a sign?
- Spot Healing Brush with a very large soft brush
- Blur tool
* Clone Stamp, copying from a matching area of bricks
- Fill with white
= The Clone Stamp copies pixels exactly, keeping lines and patterns aligned.

Q: Which are good retouching practices? (choose all that apply)
* Retouch on a separate layer
* Keep the original unedited file
- Smooth skin until all texture is gone
- Change a person's body shape without asking
= Honest, reversible retouching keeps texture and respects the person.

Q: What is the benefit of doing dodge and burn on a 50 percent grey Soft Light layer?
- It converts the photo to black and white
* You can lower, erase or delete the effect without harming the photo
- It reduces the file to a JPG
- It removes colour casts
= The effect lives on its own layer, so it is fully reversible.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Backgrounds, type, shapes and compositing',
            'summary' => 'Remove and replace backgrounds, add text and shapes, use smart objects and filters, and combine photos convincingly.',
            'lessons' => [
                [
                    'title'   => 'Removing and replacing backgrounds',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to cut a person or product out of a photo and place them on a new background that looks natural. This matters because clean product shots, ID-style photos and event posters often need a plain or branded background, and a poor cut-out is easy to spot.</p>

<h3>A reliable workflow</h3>
<ol>
<li>Open the photo. Duplicate the Background layer (<strong>Ctrl+J, Cmd+J</strong>) so the original stays below.</li>
<li>Select the subject: click <strong>Select > Subject</strong>, or use the Object Selection tool. In current versions the options bar may also show a <strong>Remove Background</strong> button when the Layers panel shows a single layer. Look for it by name; it adds a layer mask for you.</li>
<li>Click <strong>Select and Mask</strong> to refine edges, paint over hair, tick Decontaminate Colors and set Output To <strong>New Layer with Layer Mask</strong>.</li>
<li>Check the edge against a dark and a light colour layer put underneath.</li>
<li>Place your new background layer below the subject.</li>
</ol>

<h3>Matching the new background</h3>
<p>A cut-out only looks real if the subject matches the new scene. Check these four things:</p>
<table>
<thead><tr><th>Match</th><th>What to check</th><th>How to fix</th></tr></thead>
<tbody>
<tr><td>Light direction</td><td>Is the light on the person from the same side as in the scene?</td><td>Choose a different photo, or flip with Edit > Transform > Flip Horizontal</td></tr>
<tr><td>Colour and warmth</td><td>Does the subject look more yellow or blue than the scene?</td><td>Add a clipped Color Balance or Photo Filter layer to the subject</td></tr>
<tr><td>Contrast and sharpness</td><td>Is the background blurrier or sharper?</td><td>Apply a small Gaussian Blur to the background (as a smart filter)</td></tr>
<tr><td>Shadow</td><td>Does the person touch the ground without a shadow?</td><td>Paint a soft dark shadow on a Multiply layer under the feet</td></tr>
</tbody>
</table>

<h3>Making a simple shadow</h3>
<ol>
<li>Add a new layer below the subject and set it to <strong>Multiply</strong>.</li>
<li>With a very soft brush and a dark grey colour (not pure black), paint an oval under the feet.</li>
<li>Lower the opacity to 30 to 50 percent. Use <strong>Edit > Free Transform</strong> to flatten or stretch the shadow to match the light direction.</li>
</ol>

<h3>Transparent background for products</h3>
<p>If you need a cut-out with no background, hide any colour layers beneath your subject and export as <strong>PNG</strong> (transparency is kept). For a white-background product photo, add a white solid colour layer below instead, and export as JPG.</p>

<h3>Free alternatives</h3>
<p>Photopea has <strong>Select > Subject</strong> in newer versions, plus Magic Wand and Quick Selection. GIMP has the Foreground Select tool and Fuzzy Select. Many free web tools remove backgrounds in one click; check their privacy and licence terms before uploading client photos.</p>

<h3>Mini-case</h3>
<p>Jonathan sells handmade bags on WhatsApp. He photographs each bag against a plain wall, uses Select Subject and Select and Mask, then adds a soft grey-to-white gradient background and a faint Multiply shadow. His catalogue photos now look consistent, and he keeps the PSD for each so he can change the background colour for different seasons.</p>

<blockquote><p><strong>Quick check:</strong> The person you cut out looks too warm against a cool, blue scene. What do you do?</p><p>Answer: Add a Color Balance or Photo Filter adjustment layer clipped to the person to cool the colour so they match the scene.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You need a logo-ready cut-out with no background. Which format keeps transparency?</p><p>Answer: PNG, because JPG fills transparent areas with a solid colour.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a photo of a person or object and a different scene photo of your choice (with permission or a free licence).</li>
<li>Cut the subject out with Select Subject and Select and Mask, outputting to a layer mask.</li>
<li>Drag the scene below it, match colour with a clipped adjustment layer, and add a Multiply shadow.</li>
<li>Save the PSD and export a JPG. Add it to your portfolio.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Cut out with a layer mask, not by erasing.</li>
<li>Match light direction, colour, sharpness and shadow to the new background.</li>
<li>Test your edges on dark and light underlays.</li>
<li>Export PNG for transparency and JPG for solid backgrounds.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Type and shapes: text effects, vector shapes and paths',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to add readable text, apply simple text effects and draw sharp shapes in Photoshop. This matters because most designs, from posters to thumbnails, depend on clear words and tidy shapes, and poor type is the quickest way to make a design look amateur.</p>

<h3>Adding text</h3>
<ol>
<li>Choose the <strong>Type tool</strong> (T).</li>
<li>Click once for <strong>point text</strong> (for short headlines), or click and drag a box for <strong>paragraph text</strong> (for longer text that wraps).</li>
<li>Type your words. Confirm with the tick in the options bar, or press Ctrl+Enter (Cmd+Return).</li>
<li>Set the font, size, colour and alignment in the options bar or in <strong>Window > Character</strong> and <strong>Window > Paragraph</strong>.</li>
</ol>
<p>Text stays editable as a text layer, which shows a "T" icon. Do not convert it to pixels until you must.</p>

<h3>Settings that make text look professional</h3>
<table>
<thead><tr><th>Setting</th><th>Where</th><th>Good practice</th></tr></thead>
<tbody>
<tr><td>Font size</td><td>Options bar or Character panel</td><td>Headline very large, body text readable at the final size (for a poster, at least 24 pt)</td></tr>
<tr><td>Leading (line spacing)</td><td>Character panel</td><td>About 120 percent of font size for body text; tighter for big headlines</td></tr>
<tr><td>Tracking (letter spacing)</td><td>Character panel</td><td>Add a little for ALL CAPS and small text</td></tr>
<tr><td>Alignment</td><td>Paragraph panel</td><td>Left-align body text; centre only short lines</td></tr>
<tr><td>Contrast</td><td>Colour choice</td><td>Dark on light or light on dark; add a dark overlay behind text over busy photos</td></tr>
</tbody>
</table>
<p>Use no more than two font families. Choose fonts that are licensed for your use; Google Fonts are free for commercial use, and Adobe's own font library comes with a subscription, with licence terms that apply. Check any font you download for your own business or client work.</p>

<h3>Text effects through layer styles</h3>
<p>Click <strong>fx</strong> on a text layer and try:</p>
<ul>
<li><strong>Stroke</strong>: an outline, useful for text over busy images (white text with a dark stroke).</li>
<li><strong>Drop Shadow</strong>: lifts the text slightly off the page.</li>
<li><strong>Gradient Overlay</strong>: colour fading across the letters.</li>
<li><strong>Bevel and Emboss</strong>: use sparingly; it can look dated.</li>
</ul>
<p>Another effect is putting a photo inside text using a clipping mask (see Module 2): place the photo above a bold text layer and clip it.</p>

<h3>Shapes and vector tools</h3>
<p>Choose a shape tool, for example the <strong>Rectangle</strong>, <strong>Ellipse</strong>, <strong>Line</strong> or <strong>Custom Shape</strong> (shapes share one toolbar slot; right-click to switch). Click and drag on the canvas. Shape layers are <strong>vector</strong>, so they stay crisp when scaled. Change <strong>Fill</strong> and <strong>Stroke</strong> in the options bar or Properties panel, and round the corners using the corner radius setting in the Properties panel. Hold <strong>Shift</strong> while dragging for a perfect square or circle.</p>
<p>The <strong>Pen tool</strong> (P) draws custom paths by clicking points and dragging handles to make curves. It takes practice, but it is the best way to make a clean outline. Use <strong>Align</strong> options (in the options bar with the Move tool) to line up several layers neatly.</p>

<h3>Free alternatives</h3>
<p>Photopea has type and shape tools with similar panels. GIMP's text tool is more basic, and its paths tool works like the Pen. For vector-only design, Inkscape is free and made for it.</p>

<h3>Mini-case</h3>
<p>Esther is making a concert poster. The photo is busy, so she places a black rectangle over it at 50 percent opacity, then types the event name in bold white capitals with generous letter spacing and a small yellow line shape under it. The text is clear from across a room.</p>

<blockquote><p><strong>Quick check:</strong> Your white headline gets lost over a bright photo. What are two good fixes?</p><p>Answer: Add a dark semi-transparent shape behind the text, or add a subtle dark drop shadow or stroke to the text.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why are shape layers better than painted pixel shapes for a logo mark?</p><p>Answer: Shape layers are vector, so they scale without becoming blurry and stay editable.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a 1080 x 1080 px document and place a photo.</li>
<li>Add a rectangle at 50 percent opacity behind a headline in one bold font.</li>
<li>Add a subtitle in a lighter weight of the same font, a line shape, and a drop shadow on the headline.</li>
<li>Use the clipping mask trick to put a texture inside a word. Save the PSD and export a PNG.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use point text for headlines and paragraph text for longer copy; keep text as a layer.</li>
<li>Control size, leading, tracking, alignment and contrast.</li>
<li>Layer styles (stroke, shadow, gradient) make text effects that stay editable.</li>
<li>Shape layers are vector and stay sharp at any size.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Smart objects, smart filters and creative filters',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to convert layers into smart objects and apply filters that you can change later. This matters because a normal filter changes pixels forever, while a smart filter can be switched off or edited at any time, and smart objects protect image quality when you resize.</p>

<h3>Smart objects</h3>
<p>A <strong>smart object</strong> is a container that keeps the original image data safe inside your document. Convert a layer: right-click it and choose <strong>Convert to Smart Object</strong>, or use <strong>Layer > Smart Objects > Convert to Smart Object</strong>. A small page icon appears on the thumbnail.</p>
<p>Benefits:</p>
<ul>
<li><strong>Safe resizing.</strong> If you shrink a normal pixel layer then enlarge it, it goes soft because pixels were thrown away. A smart object remembers the original, so you can scale up and down without extra damage (up to its original size).</li>
<li><strong>Smart filters</strong> (see below).</li>
<li><strong>Linked or embedded files.</strong> Double-click the thumbnail to open the contents in a separate window; edit, save and close, and your main document updates. Placing an image with <strong>File > Place Embedded</strong> makes a smart object automatically.</li>
<li><strong>Repeated use.</strong> Duplicate a smart object many times; editing the contents once can update all copies if they are duplicates of the same object.</li>
</ul>
<p>Limit: you cannot paint or use the healing tools on a smart object directly. Add a new layer above, or choose <strong>Layer > Smart Objects > Rasterize</strong> only when you are sure.</p>

<h3>Smart filters</h3>
<p>With the layer converted to a smart object, apply any filter from the <strong>Filter</strong> menu (for example <strong>Filter > Blur > Gaussian Blur</strong>). The filter appears under the layer in the Layers panel as a <strong>smart filter</strong> with its own mask. You can:</p>
<ul>
<li>Double-click its name to change the settings.</li>
<li>Click its eye icon to turn it off and on.</li>
<li>Paint on its white mask with black to keep the filter off part of the picture.</li>
<li>Change its blend mode and opacity with the small slider icon beside it.</li>
</ul>

<h3>Useful filters</h3>
<table>
<thead><tr><th>Filter</th><th>Where</th><th>Use</th></tr></thead>
<tbody>
<tr><td>Gaussian Blur</td><td>Filter > Blur</td><td>Soft backgrounds, depth of field effects</td></tr>
<tr><td>Camera Raw Filter</td><td>Filter menu</td><td>Photo-style tone and colour controls on any layer</td></tr>
<tr><td>Unsharp Mask or Smart Sharpen</td><td>Filter > Sharpen</td><td>Final sharpening for web or print; keep subtle</td></tr>
<tr><td>Add Noise</td><td>Filter > Noise</td><td>Adds grain to match photos in a composite</td></tr>
<tr><td>Liquify</td><td>Filter menu</td><td>Small shape adjustments (use ethically; see Module 3)</td></tr>
<tr><td>Neural filters</td><td>Filter menu</td><td>AI-based effects; features and availability change, some are cloud-based</td></tr>
</tbody>
</table>
<p>Filters and their locations change between versions; look for them by name in the Filter menu or the Filter Gallery.</p>

<h3>Sharpening wisely</h3>
<p>Sharpen last, after resizing, because resizing changes the sharpness. Over-sharpening creates bright outlines (halos) and noisy grain. Sharpen for the final output size.</p>

<h3>Free alternatives</h3>
<p>Photopea supports smart objects and smart filters in a similar way. GIMP has non-destructive filter layers in newer versions (the filters can be applied as effects that stay editable), but it works differently from Photoshop, so duplicate your layer for safety.</p>

<h3>Mini-case</h3>
<p>Kevin makes a poster using a small photo of a crowd. He converts it to a smart object, scales it down to fit, then later the client wants it bigger. Because the original is kept, he resizes it up without a loss of detail up to its first size. He adds a Gaussian Blur smart filter to the background and paints black on its mask over the speaker so the speaker stays sharp.</p>

<blockquote><p><strong>Quick check:</strong> What is the advantage of a smart filter over a normal filter?</p><p>Answer: A smart filter can be edited, turned off or masked later, while a normal filter changes the pixels permanently.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You shrank a normal layer to 20 percent and then enlarged it back. Why does it look soft?</p><p>Answer: Shrinking threw away pixels permanently; converting to a smart object first would have kept the original data.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Place a photo into a document with <strong>File > Place Embedded</strong>.</li>
<li>Scale it to 25 percent, then back to 100 percent, and note it stays sharp.</li>
<li>Apply a Gaussian Blur and edit the smart filter mask to keep one area sharp.</li>
<li>Double-click the filter name to change the amount, then save the PSD.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Smart objects keep the original pixels safe when scaling and filtering.</li>
<li>Smart filters stay editable and maskable.</li>
<li>Sharpen last, for the final size, and gently.</li>
<li>You cannot paint directly on a smart object; use a layer above it.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Compositing and photo manipulation',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to combine several images into one believable picture using layers, masks and matching techniques. This matters because adverts, book covers, event posters and campaign images are often composites, and the difference between a convincing and a fake-looking one is in the details.</p>

<h3>What compositing is</h3>
<p><strong>Compositing</strong> means putting parts of different images together into one. A <strong>photo manipulation</strong> may change reality for creative effect, such as a person standing on a floating island. It is fine for art and advertising, as long as you are not trying to deceive viewers about real events.</p>

<h3>Five steps to a believable composite</h3>
<ol>
<li><strong>Plan and collect.</strong> Sketch the idea. Choose images with a similar camera angle, light direction and quality. Use your own photos or ones with a free licence.</li>
<li><strong>Cut out and place.</strong> Use selections and layer masks. Convert pieces to smart objects so you can scale freely.</li>
<li><strong>Scale and perspective.</strong> Use <strong>Edit > Free Transform</strong> (Ctrl+T, Cmd+T) to resize; hold Shift to keep proportion. Check relative size: a person cannot be larger than a house unless that is your point. Use <strong>Edit > Transform > Perspective</strong> or <strong>Warp</strong> to fit angles.</li>
<li><strong>Match light and colour.</strong> Add clipped adjustment layers for each piece (Curves, Color Balance, Hue/Saturation). Add shadows on Multiply layers and highlights on Screen or Soft Light layers.</li>
<li><strong>Unify.</strong> Place a final adjustment layer or Camera Raw filter above everything for a common colour grade, add a little shared grain with Add Noise and a gentle vignette.</li>
</ol>

<h3>Match checklist</h3>
<table>
<thead><tr><th>Check</th><th>Sign of a mismatch</th><th>Fix</th></tr></thead>
<tbody>
<tr><td>Light</td><td>Shadows fall in different directions</td><td>Flip or choose another image; repaint shadows</td></tr>
<tr><td>Colour</td><td>One piece looks pasted on in the wrong colour</td><td>Clipped Color Balance or Curves</td></tr>
<tr><td>Edges</td><td>Light halos, jagged outlines</td><td>Refine the mask; Decontaminate Colors; feather 0.5 to 1 px</td></tr>
<tr><td>Focus</td><td>Subject sharp, background also sharp, or the reverse</td><td>Gaussian Blur smart filter</td></tr>
<tr><td>Grain</td><td>Clean piece against grainy photo</td><td>Add Noise to match</td></tr>
<tr><td>Scale and perspective</td><td>Objects look too big or at the wrong angle</td><td>Free Transform, Perspective, Warp</td></tr>
</tbody>
</table>

<h3>Ethics of compositing</h3>
<ul>
<li>Label art as art. If an image is altered, do not present it as a real news event or evidence.</li>
<li>Do not place a real person in a false, embarrassing or harmful situation. Fake images of real people can cause serious harm and may break the law.</li>
<li>Have permission for every photo you use and credit where required. See the copyright lesson later.</li>
</ul>

<h3>Free alternatives</h3>
<p>Photopea, GIMP and Krita all support layers, masks and transform tools, so you can build the same composite. Names differ: Photopea uses <strong>Edit > Free Transform</strong>, GIMP uses the Unified Transform and Perspective tools.</p>

<h3>Mini-case</h3>
<p>An NGO needs a poster for a clean water campaign: a child drinking from a tap, with a green hillside behind. Mary cuts the child out, places the hillside, adds a warm Color Balance to the child to match the sunset behind, paints a soft shadow on the ground, adds grain to the whole picture, and puts the campaign title in the free sky area. The photo is of a real child, so she confirms the parent's written consent before publishing.</p>

<blockquote><p><strong>Quick check:</strong> The cut-out person's shadow falls to the left, but the sun in the background photo is on the left too. What is wrong?</p><p>Answer: If the sun is on the left, shadows should fall to the right; fix by repainting the shadow or flipping the image so the light direction matches.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You add a person to a photo of a real event and share it as news. Is that fine?</p><p>Answer: No, because it misleads viewers about what happened; composites are for clearly creative use only.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick a background photo and two cut-out subjects (your own photos or free-licence ones).</li>
<li>Place them, scale with Free Transform, and add masks for clean edges.</li>
<li>Add a clipped adjustment to each subject, a Multiply shadow, and a final colour grade above everything.</li>
<li>Save the PSD with organised, named layers, and export a JPG for your portfolio.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A good composite matches light, colour, edges, focus, grain and perspective.</li>
<li>Use masks and smart objects so every piece can still be changed.</li>
<li>Finish with a unifying colour grade.</li>
<li>Be honest: label creative work and never mislead or harm real people.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: You cut out a person and place them on a new beach scene. They look pasted on. Which is the most useful first check?
- Convert to CMYK
* Match light direction, colour and add a contact shadow
- Increase the document resolution
- Rename the layer
= Believable composites depend on matching light, colour and shadow.

Q: What is the safest output for Select and Mask when you may need to refine the edges later?
- Output to a new flattened layer
* Output to a layer mask
- Press Delete on the background
- Save as JPG
= A layer mask is non-destructive and can be repainted.

Q: A white headline is hard to read on a busy photo. Which fix works?
- Make the text smaller
* Put a semi-transparent dark shape behind it
- Change the file to PNG
- Use a thin pale yellow font
= Adding a dark overlay behind the text raises contrast.

Q: Why convert a layer to a smart object before applying a blur?
- Smart objects are always CMYK
- It makes the blur permanent
* The blur becomes an editable smart filter that you can mask or switch off
- It reduces file size by half
= Smart filters stay editable and non-destructive.

Q: Which statement about shape layers is correct?
- They turn blurry when enlarged
* They are vector and stay sharp when scaled
- They cannot be coloured
- They only exist in CMYK
= Vector shapes scale without losing sharpness.

Q: When should you sharpen an image for the web?
- Before resizing, with a strong amount
* After resizing to the final size, gently
- Never; sharpening is not allowed
- Only in Grayscale mode
= Resizing changes sharpness, so sharpen last at the final size.

Q: A cut-out has a light halo around the hair. What helps? (choose all that apply)
* Decontaminate Colors in Select and Mask
* Painting the mask edge with a small soft brush
- Increasing the canvas size
- Adding more saturation to the whole image
= These fix edge fringes. Canvas size and saturation do not.

Q: You want to put a heavily edited photo of a protest in a news report, with one person removed. What is the correct decision?
- Do it if nobody will notice
* Do not; news images must not be altered in ways that change the facts
- Do it, but only in CMYK
- Do it if the file is saved as TIFF
= Removing people from news photos misleads readers and breaks trust.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Designing real projects and automating work',
            'summary' => 'Design posters, flyers, social posts, banners, thumbnails and mockups, then speed up repeat work with actions and batch processing.',
            'lessons' => [
                [
                    'title'   => 'Designing posters and flyers',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to design a clear poster or flyer in Photoshop with a strong focal point, readable text and the right print settings. This matters because a notice for a church event, school fundraiser or shop opening only works if people can read it from a distance in a few seconds.</p>

<h3>Start with the brief</h3>
<p>Before opening Photoshop, write answers to these questions on paper: What is the event? Who is it for? What is the one thing people must remember? What action do you want (come, call, pay, register)? Where will it be seen (wall, WhatsApp, handout)? A poster for a wall needs big words; a flyer handed out can carry more detail.</p>

<h3>Set up the document</h3>
<table>
<thead><tr><th>Item</th><th>Poster for the wall</th><th>Flyer to hand out</th></tr></thead>
<tbody>
<tr><td>Size</td><td>A3 (297 x 420 mm) or A2</td><td>A5 (148 x 210 mm) or A4</td></tr>
<tr><td>Resolution</td><td>150 to 300 PPI</td><td>300 PPI</td></tr>
<tr><td>Colour mode</td><td>RGB while designing; CMYK if the printer asks</td><td>Same</td></tr>
<tr><td>Bleed</td><td>3 mm each side if printing to the edge</td><td>3 mm each side</td></tr>
</tbody>
</table>
<p>Bleed means extra image beyond the trim edge so no white strip appears when the paper is cut. Add it in <strong>File > New</strong> by making the document 6 mm bigger in width and height, and keep text at least 5 to 10 mm inside the final trim line. Draw guides with <strong>View > New Guide</strong> to mark the trim and safe area.</p>

<h3>Build the layout in layers</h3>
<ol>
<li><strong>Background.</strong> A photo, a colour or a gradient on its own layer or smart object.</li>
<li><strong>Focal point.</strong> One strong image or one huge headline. Make it the biggest element.</li>
<li><strong>Overlay for contrast.</strong> A semi-transparent shape or gradient behind text.</li>
<li><strong>Text hierarchy.</strong> Headline, then date and place, then details, then contact. Use size and weight to rank them; use no more than two fonts.</li>
<li><strong>Call to action.</strong> A button-like shape with "Register by 30 June" or a phone number or QR code.</li>
<li><strong>Logo and credits</strong> small in a corner or at the bottom.</li>
</ol>

<h3>Design rules that work</h3>
<ul>
<li>Squint your eyes: you should still see the main shape and headline.</li>
<li>Keep generous margins, and align elements to a left edge or centre line.</li>
<li>Limit colours to three: a main, an accent and a neutral.</li>
<li>Use one clear photo, not four small ones.</li>
<li>Write dates in full, such as "Saturday 14 June, 2 pm", and place the venue with a landmark.</li>
</ul>

<h3>Worked example: fundraiser flyer</h3>
<p>A school wants an A5 flyer for a fundraising day. Canvas: 154 x 216 mm with 3 mm bleed, 300 PPI. Background: a cheerful photo of pupils, covered by a dark blue gradient from the bottom. Headline: "SCHOOL FUN DAY" in 60 pt bold white. Details: "Saturday 12 July, 10 am, School grounds, Entry UGX 5,000" at 20 pt. Call to action: a yellow rounded rectangle saying "Call 07xx xxx xxx". Logo at the bottom right. Everything sits on named layers in groups: Background, Overlay, Text, Logo.</p>

<h3>Free alternatives</h3>
<p>The same layout can be made in Photopea, or in GIMP and Inkscape. For heavy text layout, a free tool like Scribus is built for print pages. Canva is easier for beginners, but Photoshop gives you finer control over photos.</p>

<blockquote><p><strong>Quick check:</strong> What is bleed and why add it?</p><p>Answer: Bleed is extra image past the trim line, added so that when the paper is cut there is no white edge.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A poster has five fonts, six colours and four photos. What is the main fix?</p><p>Answer: Simplify: use two fonts, three colours and one strong focal image so the message is clear.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a real or imaginary event (a youth camp, a market day or a clinic outreach).</li>
<li>Write the brief: message, audience, action.</li>
<li>Create an A4 document at 300 PPI with 3 mm bleed guides and design the flyer with a focal image, overlay, text hierarchy and call to action.</li>
<li>Name and group your layers, save the PSD, export a JPG for WhatsApp and a PDF for printing. Add to your portfolio.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Write the brief before designing.</li>
<li>Set correct size, 300 PPI for small prints, and bleed for edge-to-edge designs.</li>
<li>Use one focal point, a clear text hierarchy, limited fonts and colours.</li>
<li>Keep layers named and grouped for easy changes.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Social media posts, banners and YouTube thumbnails',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to design square, vertical and wide graphics that work on a phone screen: social posts, web banners and YouTube thumbnails. This matters because most people in Uganda see your work on a small phone screen, scrolling fast, so your design has about one second to be understood.</p>

<h3>Common sizes</h3>
<table>
<thead><tr><th>Design</th><th>Size in pixels</th><th>Ratio</th><th>Note</th></tr></thead>
<tbody>
<tr><td>Square post</td><td>1080 x 1080</td><td>1:1</td><td>Works on many platforms</td></tr>
<tr><td>Portrait post</td><td>1080 x 1350</td><td>4:5</td><td>Takes more screen space in feeds</td></tr>
<tr><td>Story or Status</td><td>1080 x 1920</td><td>9:16</td><td>Keep text away from top and bottom edges where buttons appear</td></tr>
<tr><td>YouTube thumbnail</td><td>1280 x 720</td><td>16:9</td><td>Usually must be under a file size limit; check the platform</td></tr>
<tr><td>Website banner</td><td>1200 x 400 or similar</td><td>3:1</td><td>Depends on the website theme</td></tr>
</tbody>
</table>
<p>Platforms change sizes and rules often, so always check the platform's current help page. Design in RGB at 72 PPI (or any; only pixels matter on screen).</p>

<h3>Set up with artboards</h3>
<p>You can design several sizes in one document using <strong>Artboards</strong>: choose the Artboard option in <strong>File > New</strong> or use the Artboard tool, which sits in the same group as the Move tool. Each artboard is like its own page. Look for the Artboard tool by name. If your version lacks it, use separate documents and copy layers across.</p>

<h3>A thumbnail recipe that works</h3>
<ol>
<li>Create a 1280 x 720 px document.</li>
<li>Place a clear photo of a face or the main subject, cut out with Select Subject, and put it on one side, large.</li>
<li>Add a bright, simple background: a gradient or a blurred photo.</li>
<li>Add 2 to 4 big words in a heavy font, for example "FREE WIFI TRICK". White or yellow text with a dark stroke and a drop shadow reads well.</li>
<li>Add one arrow, circle or shape to direct the eye.</li>
<li>Zoom out to 25 percent: if you cannot read the words, make them bigger.</li>
</ol>
<p>Be honest: the thumbnail must match the video. Misleading "clickbait" harms trust and can break platform rules.</p>

<h3>Design rules for small screens</h3>
<ul>
<li>One idea per image, with at most about 7 words.</li>
<li>Text size: for a 1080-wide post, headlines are often 80 to 150 px, details at least 40 px.</li>
<li>Strong contrast, with a safe margin of about 60 px so edges are not cut off.</li>
<li>Put the logo small and consistent in the same corner each time.</li>
<li>Preview on your phone before posting.</li>
</ul>

<h3>Worked example: salon offer post</h3>
<p>A salon wants a 1080 x 1350 post. Background: a warm photo of a client, darkened with a Multiply layer. Text: "Braids Special" at 130 px, "UGX 50,000 this week" at 60 px, "Book on WhatsApp 07xx xxx xxx" at 44 px in a gold button shape. Logo top left. Saved as <code>salon-offer-v1.psd</code> and exported as a JPG at around 85 percent quality.</p>

<h3>Free alternatives</h3>
<p>Photopea works well for this, and has artboards-like canvases through multiple documents. Canva has built-in templates for every platform size, which is faster for simple posts.</p>

<blockquote><p><strong>Quick check:</strong> Your thumbnail text is unreadable when viewed small. What do you do?</p><p>Answer: Make the words fewer and larger, increase contrast, and test by viewing the design at about 25 percent size.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why keep important text away from the top and bottom of a 9:16 story?</p><p>Answer: The platform's buttons and captions cover those edges, so text there can be hidden or cut off.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Design a 1080 x 1080 px post for a small business (real or imaginary) with a photo, a short headline, an offer and contact details.</li>
<li>Design a 1280 x 720 px YouTube thumbnail for a tutorial topic with a cut-out face or object, large text and one shape.</li>
<li>Export both as JPG, send them to your phone and check readability at actual size.</li>
<li>Save both PSD files in a folder named <code>social-portfolio</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use the correct pixel size and check platform guides for current sizes.</li>
<li>One idea, big text, strong contrast, safe margins.</li>
<li>Thumbnails need bold, honest, short text.</li>
<li>Always preview on a phone before publishing.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Mockups with smart objects and Warp',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to show a design on a T-shirt, mug, signboard or phone screen using a mockup. This matters because clients and customers understand a design better when they see it on a real object, and a good mockup helps you win jobs and present work professionally.</p>

<h3>What is a mockup</h3>
<p>A <strong>mockup</strong> is a picture of an object, such as a shirt or billboard, with an empty area where your design is placed. Photoshop mockup files (PSDs) usually contain a <strong>smart object</strong> layer. You replace its contents with your design, and the design appears on the object, with the right shape, light and shadow.</p>

<h3>Using a ready-made mockup PSD</h3>
<ol>
<li>Download a mockup PSD from a source with a licence that allows your use. Read the licence: some are free for personal use only.</li>
<li>Open the PSD in Photoshop. In the Layers panel, find a layer with a smart object icon, often named "Your design here" or "Replace me".</li>
<li>Double-click the smart object thumbnail. A new tab opens.</li>
<li>Paste or place your design (<strong>File > Place Embedded</strong>), scale it to the guide, and save with <strong>Ctrl+S (Cmd+S)</strong>.</li>
<li>Close that tab and return to the main file. The mockup updates automatically.</li>
<li>Export the final with <strong>File > Export > Export As</strong> as JPG or PNG.</li>
</ol>

<h3>Making your own simple mockup</h3>
<p>Suppose you have a flat photo of a plain T-shirt or a wall sign.</p>
<ol>
<li>Place your logo or artwork above the photo and convert it to a smart object.</li>
<li>Press <strong>Ctrl+T (Cmd+T)</strong> to Free Transform, scale it and place it. For a curved or wrinkled surface, right-click inside the box and choose <strong>Warp</strong>, or use <strong>Edit > Transform > Perspective</strong> for a flat sign at an angle.</li>
<li>Set the artwork layer's blend mode to <strong>Multiply</strong> (for light fabric) so the shirt's folds and shadows show through.</li>
<li>Lower the opacity slightly (about 90 percent) so the print looks like ink, not a sticker.</li>
<li>Add a <strong>Displace</strong> filter or a Warp to follow wrinkles if you want extra realism; it is optional and advanced.</li>
</ol>

<h3>Key settings table</h3>
<table>
<thead><tr><th>Surface</th><th>Blend mode</th><th>Transform</th></tr></thead>
<tbody>
<tr><td>Light T-shirt</td><td>Multiply</td><td>Warp for curves of the chest</td></tr>
<tr><td>Dark T-shirt</td><td>Normal, or Screen for light print</td><td>Warp</td></tr>
<tr><td>Wall sign or billboard</td><td>Normal</td><td>Perspective or Distort</td></tr>
<tr><td>Phone screen</td><td>Normal</td><td>Perspective; match the corners</td></tr>
<tr><td>Mug or bottle</td><td>Multiply or Normal</td><td>Warp (Arc or Cylinder-like bend)</td></tr>
</tbody>
</table>

<h3>Honesty with mockups</h3>
<ul>
<li>Say it is a mockup; do not pretend the product already exists or has been made.</li>
<li>Do not use trademarked logos, school or church crests, or celebrity pictures without permission.</li>
<li>Keep the flat artwork file as well, because printers need the flat design, not the mockup picture.</li>
</ul>

<h3>Free alternatives</h3>
<p>Photopea opens PSD mockups and also supports smart objects, so you can use most mockup files for free. GIMP will open a PSD but may flatten smart objects; test first.</p>

<h3>Mini-case</h3>
<p>Isaac designs a logo for a youth football club. He downloads a free-licence jersey mockup, replaces the smart object with his logo, then exports three versions in different club colours. The committee approves the design on the mockup the same day, before any printing cost.</p>

<blockquote><p><strong>Quick check:</strong> How do you put your design into a ready-made mockup PSD?</p><p>Answer: Double-click the smart object layer, place your design in the window that opens, save and close it, and the mockup updates.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which blend mode lets a shirt's folds show through a printed logo on a light shirt?</p><p>Answer: Multiply, because it darkens the artwork with the shirt's shadows and makes white areas vanish.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Find or download a mockup PSD with a clear licence, or photograph a plain mug or shirt yourself.</li>
<li>Place your own logo or a text design as a smart object.</li>
<li>Use Free Transform, Warp and Multiply to fit it to the object.</li>
<li>Save a PSD and export a JPG. Keep the flat design in a separate file named <code>logo-flat.png</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Mockups use smart objects so you can swap designs quickly.</li>
<li>Warp and Perspective fit art to curved or angled surfaces.</li>
<li>Multiply blends art with fabric folds.</li>
<li>Check the mockup's licence and be honest that it is a mockup.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Automation: actions, batch processing, Export As and generating assets',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to record an action, run it on many photos and export images quickly. This matters because resizing, watermarking or colour-fixing 200 photos from a school event one by one could take a whole day, while automation can do it in minutes.</p>

<h3>Actions: record once, play many times</h3>
<p>An <strong>action</strong> records a series of steps so you can replay them with a click. Open the panel through <strong>Window > Actions</strong> (Alt+F9, Option+F9).</p>
<ol>
<li>Click the <strong>Create New Action</strong> button at the bottom of the panel. Name it, for example "Web 1200".</li>
<li>Click <strong>Record</strong>. Everything you do is now recorded.</li>
<li>Do the job once: for example <strong>Image > Image Size</strong> to 1200 px wide, a light <strong>Filter > Sharpen > Smart Sharpen</strong> step, then <strong>File > Save a Copy</strong> as JPG.</li>
<li>Click <strong>Stop</strong> (the square). The action is saved in the list.</li>
<li>Open another photo and press <strong>Play</strong> (the triangle) with your action selected.</li>
</ol>
<p>Some steps do not record well, such as freehand brush strokes. Record menu commands and dialog settings. If your tool is in a different order in your version, record again.</p>

<h3>Batch: run an action on a folder</h3>
<p>Use <strong>File > Automate > Batch</strong>. Choose your Action as the <strong>Play</strong> option, set <strong>Source</strong> to Folder and select the folder with your photos, and set <strong>Destination</strong> to another folder so the originals are not overwritten. Tick <strong>Override Action "Save As" Commands</strong> if you want to choose the file naming in the batch window. Click OK and wait.</p>
<p>Always test on three photos first. Always send results to a new folder.</p>

<h3>Other time-savers</h3>
<table>
<thead><tr><th>Feature</th><th>Where</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Image Processor</td><td>File > Scripts > Image Processor</td><td>Converts a folder to JPG, PSD or TIFF and resizes, optionally running an action</td></tr>
<tr><td>Export As</td><td>File > Export > Export As (Alt+Shift+Ctrl+W, Option+Shift+Cmd+W)</td><td>Choose format, quality and size with a preview and a file size readout</td></tr>
<tr><td>Quick Export as PNG</td><td>File > Export</td><td>One-click export using preset settings</td></tr>
<tr><td>Generate Image Assets</td><td>File > Generate > Image Assets</td><td>Name layers like <code>logo.png</code> or <code>banner.jpg</code> and Photoshop saves them in a folder next to the PSD when you edit; look for it in your version</td></tr>
<tr><td>Artboards to Files</td><td>File > Export > Artboards to Files</td><td>Exports every artboard as a separate file</td></tr>
<tr><td>Variables and Data Sets</td><td>Image > Variables</td><td>Swaps text or pictures from a data file to make many versions, such as name tags</td></tr>
</tbody>
</table>
<p>Menu names change between versions; if you cannot find a feature, use the search icon and type its name.</p>

<h3>Worked example: watermark and resize 100 photos</h3>
<p>A photographer needs 100 event photos at 1600 px wide with a small logo. She records an action that resizes to 1600 px, places her logo file (<strong>File > Place Embedded</strong>), moves it to the bottom right, sets 60 percent opacity and saves a copy. Then she runs <strong>File > Automate > Batch</strong> on the folder. A tedious day becomes a coffee break.</p>

<h3>Free alternatives</h3>
<p>Photopea has no actions, but you can save time with templates. GIMP supports batch work with scripts (for example using Script-Fu), which is more technical. For simple resizing of folders, many free desktop tools exist; read their privacy and safety terms.</p>

<blockquote><p><strong>Quick check:</strong> Why send batch results to a different folder?</p><p>Answer: So the original photos are not overwritten if the action has a mistake.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which feature lets you preview quality and file size before exporting a web image?</p><p>Answer: File > Export > Export As, which shows a preview and the resulting file size.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Put five photos in a folder named <code>originals</code> and create an empty folder named <code>web-ready</code>.</li>
<li>Record an action that resizes to 1200 px wide and saves a copy as a JPG.</li>
<li>Run it through <strong>File > Automate > Batch</strong> from <code>originals</code> to <code>web-ready</code>.</li>
<li>Compare file sizes and quality, and note the time saved.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Actions record steps; Batch plays them on a whole folder.</li>
<li>Always test on a few files and send output to a new folder.</li>
<li>Export As gives preview and size control for web files.</li>
<li>Generate Assets and Artboards to Files save time on multi-size jobs.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: You are designing a flyer that prints edge to edge. What should you add?
- A watermark
* 3 mm bleed beyond the trim line
- A CMYK profile in the title
- A second layer called bleed
= Bleed prevents white edges when the paper is trimmed.

Q: What is the best size for a standard YouTube thumbnail?
- 1080 x 1920 pixels
- 210 x 297 mm at 300 PPI
* 1280 x 720 pixels
- 400 x 400 pixels
= 16:9 at 1280 x 720 px is the common size; check the platform for any change.

Q: Your social media text is too small to read on a phone. Which fix is best?
- Add more text so people can pick what they like
* Use fewer words, larger type and stronger contrast
- Change the colour mode to CMYK
- Lower the resolution
= Phone viewers need short, big, high-contrast messages.

Q: How do you swap your design into a PSD mockup?
- Delete the mockup layers
* Double-click the smart object layer, place your design, save and close
- Flatten the image and paint over it
- Change the colour mode
= The smart object updates the mockup automatically.

Q: Which blend mode lets a T-shirt's folds show through a design on a light shirt?
- Screen
* Multiply
- Dissolve
- Lighten at 0 percent
= Multiply darkens the artwork by the shirt's tones and removes white.

Q: What is a safe way to run a batch action on 200 photos?
- Run it on the originals folder and overwrite
* Test on a few, then output to a different destination folder
- Run it on one photo only
- Turn off the action first
= Testing and separate output prevent losing the originals.

Q: A thumbnail promises something that the video does not contain. What is the problem?
- It needs a higher resolution
* It is misleading and damages trust, and may break platform rules
- Nothing, it is normal marketing
- It should be in CMYK
= Thumbnails should honestly represent the content.

Q: Which are good habits for poster design? (choose all that apply)
* One clear focal point
* A text hierarchy with limited fonts
- As many fonts as possible
- Text placed on busy areas with low contrast
= Clarity, hierarchy and contrast make posters readable from far.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 6
        [
            'title'   => 'Module 6: AI features, delivery, workflow and your capstone',
            'summary' => 'Use AI features wisely, export correctly for web and print, organise your files, respect copyright and finish a portfolio project.',
            'lessons' => [
                [
                    'title'   => 'AI-assisted features: Generative Fill, Remove and their limits',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what AI-assisted features in Photoshop can do, use them carefully, and decide when not to. This matters because these tools can save hours, but they can also produce wrong, odd or misleading results, and using them without thought can break trust or rules.</p>

<h3>What AI features exist</h3>
<p>Current versions of Photoshop include AI-powered tools. Names, places and availability change often, so look for them by name and read the current help page.</p>
<table>
<thead><tr><th>Feature (look for the name)</th><th>What it does</th><th>Where to find it</th></tr></thead>
<tbody>
<tr><td>Generative Fill</td><td>Fills a selected area with new content from a text description, or extends a scene</td><td>Contextual task bar or Edit menu</td></tr>
<tr><td>Generative Expand</td><td>Extends the canvas with new generated background</td><td>Crop tool options</td></tr>
<tr><td>Remove tool</td><td>Brushes away an unwanted object or person and fills the space</td><td>Healing group in the toolbar</td></tr>
<tr><td>Select Subject, Sky and Object Selection</td><td>AI-based selection</td><td>Select menu and options bar</td></tr>
<tr><td>Neural Filters</td><td>AI effects such as colourise, smoothing or style changes</td><td>Filter menu</td></tr>
</tbody>
</table>
<p>Generative features usually need an internet connection and an account, and often use <strong>credits</strong> from a plan. Credit rules change, so read the current terms instead of assuming.</p>

<h3>Using Generative Fill step by step</h3>
<ol>
<li>Make a selection with the Lasso or Marquee around the area to change.</li>
<li>Click <strong>Generative Fill</strong> in the floating bar, type a short description (or leave blank to remove an object), and click Generate.</li>
<li>Photoshop makes a new layer with several variations. Click through them in the Properties panel.</li>
<li>Choose the best, or generate again. Check edges, hands, faces and text; AI often gets these wrong.</li>
<li>Use a layer mask to blend or hide parts of the result.</li>
</ol>
<p>Because results are placed on their own layer, you stay non-destructive.</p>

<h3>Limits and risks</h3>
<ul>
<li><strong>Errors.</strong> Extra fingers, strange text, wrong shadows and repeating patterns are common.</li>
<li><strong>Bias and stereotypes.</strong> Generated people or places may reflect biases in training data. Check results, especially for images of Africa and Africans, which may be unrealistic or stereotyped.</li>
<li><strong>Quality.</strong> Generated areas can be softer than your photo. Match grain and sharpness.</li>
<li><strong>Honesty.</strong> Do not use generated content to fake events, evidence, medical results, or people's actions.</li>
<li><strong>Privacy.</strong> Your image data may be processed in the cloud. Do not upload other people's private photos or confidential client work without permission and without reading the terms.</li>
<li><strong>Copyright and rules.</strong> Read Adobe's terms for commercial use, and check your client's, school's or employer's rules on AI-assisted work. Some competitions, publications and exams ban or require disclosure.</li>
</ul>

<h3>Be open about it</h3>
<p>If AI helped, say so when it matters: in a school project, a news context, or if a client asks. Many tools can embed "content credentials" information; look for the setting in Preferences or export options if you want your image history recorded.</p>

<h3>Free alternatives</h3>
<p>Photopea has its own AI tools that change over time. GIMP and Krita have add-ons for AI, but quality and safety vary and some need strong computers. The classic tools in this course (Content-Aware Fill, Clone Stamp, masks) still work without AI and are good to know.</p>

<h3>Mini-case</h3>
<p>A hotel in Jinja has a good photo of its garden but the sky is grey and a parked car spoils the corner. The designer removes the car with the Remove tool, checks the grass for odd patches, and fixes a smudge with the Clone Stamp. She leaves the sky real, adding warm light with Curves rather than a fake sunset, because the hotel does not want guests to feel misled.</p>

<blockquote><p><strong>Quick check:</strong> Generative Fill gives a person with six fingers on a poster. What should you do?</p><p>Answer: Generate again or fix it by hand, because AI often makes mistakes with hands and you must check all results before use.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A client sends private photos of their customers. May you upload them to an AI feature freely?</p><p>Answer: No, not without permission and a check of the privacy terms, because the images may be processed by an online service.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>If you have Photoshop with these features, remove a small object using the Remove tool and look closely at the result; otherwise use Content-Aware Fill or the Clone Stamp in Photopea or GIMP.</li>
<li>Try three variations of a fill (or three retouch methods) and list good and bad points of each.</li>
<li>Write a two-line rule for yourself on when you will and will not use AI in client work.</li>
<li>Save your before and after with a short note on what was changed.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>AI features add speed but not guaranteed quality or truth.</li>
<li>Check edges, hands, faces, text, bias and match to the surrounding photo.</li>
<li>Protect other people's privacy and follow terms, credits and local rules.</li>
<li>Be honest about AI use and never fake real events.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Exporting for web and social media',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to export images that look good and load fast on websites, WhatsApp and social media. This matters because in Uganda data is expensive and connections vary, so a 6 MB image can cost viewers money and make pages slow to open.</p>

<h3>Choosing format and size</h3>
<table>
<thead><tr><th>Image type</th><th>Format</th><th>Typical target</th></tr></thead>
<tbody>
<tr><td>Photo for a website</td><td>JPG or WebP</td><td>Under 200 to 300 KB, 1200 to 1600 px wide</td></tr>
<tr><td>Logo, icon, graphics with flat colour</td><td>PNG or WebP</td><td>As small as possible while clean</td></tr>
<tr><td>Transparent cut-out</td><td>PNG or WebP</td><td>Keep alpha transparency</td></tr>
<tr><td>Social post</td><td>JPG (or PNG for text-heavy graphics)</td><td>Exact platform size, e.g. 1080 x 1350</td></tr>
<tr><td>Image for sharing on WhatsApp</td><td>JPG</td><td>1080 to 1600 px wide; WhatsApp compresses images further</td></tr>
</tbody>
</table>

<h3>Using Export As</h3>
<ol>
<li>Choose <strong>File > Export > Export As</strong> (Alt+Shift+Ctrl+W, Option+Shift+Cmd+W).</li>
<li>Choose the <strong>Format</strong> on the right: JPG, PNG, WebP or other formats listed in your version.</li>
<li>For JPG, move the <strong>Quality</strong> slider. Watch the preview and the file size shown. Try 70 to 85 percent. The picture should still look clean when zoomed.</li>
<li>Under <strong>Image Size</strong>, set the width in pixels. Make sure the resample method is suited to reducing.</li>
<li>Under <strong>Colour Space</strong>, tick <strong>Convert to sRGB</strong>, so colours look right in browsers. Tick Embed Color Profile only if needed.</li>
<li>Click Export and choose a folder.</li>
</ol>
<p>The older <strong>File > Export > Save for Web (Legacy)</strong> option may still exist in your version. It does the same job with side-by-side previews; look for it by name.</p>

<h3>Worked example: shrinking a heavy photo</h3>
<p>A shop owner's product photo is 4000 x 3000 px and 4.8 MB. For her website the theme shows images at most 1000 px wide. Steps: resize to 1200 px wide (to allow sharp screens), export as JPG quality 80, which gives around 180 KB; or WebP at a similar quality, which is often smaller again. The page now loads in a fraction of the time and uses far less data.</p>

<h3>Naming and metadata</h3>
<ul>
<li>Use clear lowercase names with hyphens: <code>blue-handbag-front.jpg</code>, not <code>IMG_4482 final(2).JPG</code>. Good names help search engines and your own filing.</li>
<li>In the Export options, you can usually choose which <strong>metadata</strong> (camera data, copyright, location) is included. Remove location data from photos of people and children before publishing.</li>
<li>Add a short, descriptive <strong>alt text</strong> on the website or post, for people who use screen readers.</li>
</ul>

<h3>Check before publishing</h3>
<ol>
<li>Open the exported file again and check it at 100 percent.</li>
<li>Send it to your phone and look at it at actual size.</li>
<li>Check file size in File Explorer (Finder).</li>
<li>Keep your PSD safe in a separate folder.</li>
</ol>

<h3>Free alternatives</h3>
<p>Photopea has <strong>File > Export As</strong> with JPG, PNG and WebP quality sliders. GIMP has <strong>File > Export As</strong> with a quality slider and a preview option. Free online compressors exist, but check privacy before uploading client photos.</p>

<blockquote><p><strong>Quick check:</strong> A website photo is 5 MB. What two things do you change in Export As?</p><p>Answer: Reduce the pixel width to the size actually shown, and lower JPG quality (about 70 to 85 percent) until the file is small but still clean.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why tick Convert to sRGB when exporting for the web?</p><p>Answer: Browsers and phones assume sRGB, so converting stops colours looking dull or shifted.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Take a large phone photo and open it.</li>
<li>Export it three times: JPG at quality 90, JPG at quality 60 and WebP (if available) at about 80, all at 1200 px wide.</li>
<li>Record the file sizes in a small table and compare the look at 100 percent.</li>
<li>Choose your best choice and write a rule such as "web photos: 1200 px, JPG 80 percent, under 250 KB".</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Resize to the displayed size, then compress; do not upload huge originals.</li>
<li>JPG or WebP for photos; PNG or WebP for transparency and flat graphics.</li>
<li>Convert to sRGB and remove private metadata.</li>
<li>Always preview the export and keep the PSD master.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Preparing files for print: CMYK, bleed, resolution and proofs',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to prepare a Photoshop file so a print shop can print it correctly the first time. This matters because a wrong size, dull colour or white edge means reprinting, which costs your client money and costs you your reputation.</p>

<h3>Ask the printer first</h3>
<p>Before you finish, ask your printer these questions, because each shop is different:</p>
<ul>
<li>Which file format do you want (PDF, TIFF, high-quality JPG)?</li>
<li>Do you want RGB or CMYK? Which colour profile?</li>
<li>How much bleed do you need, and are there crop marks?</li>
<li>What is the final size and resolution?</li>
<li>Do you want flattened files or layered files?</li>
</ul>
<p>Write the answers in your job notes.</p>

<h3>Resolution for print</h3>
<table>
<thead><tr><th>Print job</th><th>Suggested resolution at final size</th></tr></thead>
<tbody>
<tr><td>Flyers, brochures, business cards</td><td>300 PPI</td></tr>
<tr><td>Posters viewed at arm's length</td><td>200 to 300 PPI</td></tr>
<tr><td>Large banners, billboards seen from far away</td><td>50 to 150 PPI or as the printer advises</td></tr>
</tbody>
</table>
<p>Check with <strong>Image > Image Size</strong>: untick <strong>Resample</strong>, type 300 in Resolution, and read the width and height that appear. If the result is smaller than the final size, the photo is too small for good quality. A 3000 x 2000 pixel photo gives 10 x 6.7 inches at 300 PPI.</p>

<h3>Converting to CMYK</h3>
<p>Screens can show brighter colours than ink. When you convert, vivid blues, greens and neon colours become duller. To prepare:</p>
<ol>
<li>Finish your design in RGB and save the PSD master.</li>
<li>Preview the CMYK look first: <strong>View > Proof Setup</strong> and <strong>View > Proof Colors</strong> (Ctrl+Y, Cmd+Y) show an approximation. Colours that look dull can be adjusted.</li>
<li>If the printer wants CMYK, use <strong>Image > Mode > CMYK Color</strong> on a <em>copy</em>, using the profile they recommend (you can use <strong>Edit > Convert to Profile</strong>).</li>
<li>Check the gamut warning with <strong>View > Gamut Warning</strong>; grey areas show colours that cannot be printed exactly.</li>
<li>Keep black text as pure black (a printer might want "rich black" for large black areas; ask).</li>
</ol>
<p>Many online and digital print shops accept RGB and convert. Do not convert twice, and do not convert back and forth, as it damages colour.</p>

<h3>Bleed, trim and safe area</h3>
<ul>
<li>Extend backgrounds and photos 3 mm beyond the final trim on all sides.</li>
<li>Keep important text and logos at least 5 mm (preferably 8 to 10 mm) inside the trim.</li>
<li>If the printer wants crop marks, they may be added when you export to PDF; check options.</li>
</ul>

<h3>Saving the print file</h3>
<p>Use <strong>File > Save a Copy</strong> and choose <strong>Photoshop PDF</strong> (look for the preset named something like High Quality Print, or one the printer gives you), or <strong>TIFF</strong> if requested. A flattened copy often avoids font problems; keep your layered PSD as the master. If you keep live text in a PDF, embed fonts or convert the text to shapes (check your licence).</p>

<h3>Soft proofs and test prints</h3>
<p>For large jobs, ask for a small <strong>proof</strong> (a test print) before the full run. Colours on your screen depend on its brightness and quality, so a proof is the only trusted guide. Always check spelling, phone numbers and dates on the proof.</p>

<h3>Free alternatives</h3>
<p>GIMP can convert to CMYK-like profiles through <strong>Image > Color Management</strong> and export a PDF through other tools, but its CMYK support is limited. Photopea supports CMYK mode and PDF export, which is enough for many jobs. For serious print layout, Scribus is a free tool built for CMYK and PDF output.</p>

<h3>Mini-case</h3>
<p>Faith designs 500 wedding programmes in bright turquoise. She previews with Proof Colors and sees the turquoise turns greyer. She tells the couple, adjusts the colour slightly, asks the printer for one test copy, and adds bleed. The final prints match what the couple approved.</p>

<blockquote><p><strong>Quick check:</strong> Why keep important text 5 mm or more inside the trim?</p><p>Answer: Paper cutting is not perfectly exact, so text near the edge could be trimmed off.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Your bright neon green looks dull after converting to CMYK. Why?</p><p>Answer: CMYK inks cannot reproduce some bright screen colours, so they are shifted to the closest printable colour.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Take your flyer from Lesson 1 of this module (or any A5 design).</li>
<li>Check size, 300 PPI and 3 mm bleed using Image Size and guides.</li>
<li>Turn on <strong>View > Proof Colors</strong> and note any colours that shift; adjust if needed.</li>
<li>Save a copy as PDF and a copy as high-quality JPG. Create a checklist of printer questions in a text file called <code>print-checklist.txt</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Ask the printer for format, colour mode, bleed and size before finishing.</li>
<li>300 PPI at final size for most prints; less is fine for large banners viewed from far.</li>
<li>Design in RGB, preview with Proof Colors, convert a copy to CMYK only if asked.</li>
<li>Include 3 mm bleed and keep text inside a safe area; ask for a proof.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Workflow, file organisation, copyright and working with clients',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to organise your Photoshop projects, protect your files, respect copyright and work professionally with clients. This matters because skilled editors can still lose jobs through lost files, copied images and unclear agreements, while organised designers earn repeat work.</p>

<h3>A folder system that works</h3>
<p>Create one folder per job with the same structure each time:</p>
<pre>2025-06_school-fun-day/
  01_brief/          (notes, client messages, sizes)
  02_assets/         (photos, logos, fonts, licences)
  03_working/        (your PSD files)
  04_exports/        (JPG, PNG, PDF for the client)
  05_archive/        (older versions, final approved files)</pre>
<p>Use dates and clear names: <code>fun-day-flyer_v3.psd</code>. Keep the original photos untouched in <code>02_assets</code> and place them in your PSD as smart objects or linked files.</p>

<h3>Inside the PSD</h3>
<ul>
<li>Name every layer; group by purpose (Background, Photo, Text, Logo, Adjustments).</li>
<li>Colour-code groups with right-click > <strong>Color</strong> options.</li>
<li>Use smart objects for photos and logos, adjustment layers for colour, and layer masks for hiding.</li>
<li>Delete empty or unused layers before sending to a colleague, and keep a note of the fonts used.</li>
</ul>

<h3>Backups</h3>
<p>Follow the 3-2-1 idea: keep <strong>3</strong> copies of important files, on <strong>2</strong> different kinds of storage (for example computer and external drive), with <strong>1</strong> copy away from the computer (cloud storage or a drive at another location). Power cuts, viruses and stolen laptops cause real losses.</p>

<h3>Copyright, licences and permission</h3>
<table>
<thead><tr><th>Source</th><th>Can you use it?</th></tr></thead>
<tbody>
<tr><td>A photo you took yourself</td><td>Yes, but get a signed consent for recognisable people, especially children</td></tr>
<tr><td>An image found on a search engine</td><td>Not automatically; it is usually copyrighted</td></tr>
<tr><td>Free-licence photos from stock sites</td><td>Often yes, but read each licence for commercial use, credit and edits</td></tr>
<tr><td>Creative Commons images</td><td>Depends on the specific licence; some need credit, some forbid commercial use</td></tr>
<tr><td>Fonts</td><td>Check the font licence, especially for logos and commercial print</td></tr>
<tr><td>Logos and brands of other companies</td><td>Only with permission</td></tr>
</tbody>
</table>
<p>Keep a text file of image sources and licences in the <code>02_assets</code> folder.</p>

<h3>Ethics reminders</h3>
<ul>
<li>Respect people's consent and dignity, and do not edit people in harmful or misleading ways.</li>
<li>Be honest about edited and AI-generated images where it matters.</li>
<li>Protect clients' private files and unpublished work.</li>
<li>Credit sources and collaborators.</li>
</ul>

<h3>Working with clients</h3>
<ol>
<li><strong>Brief:</strong> agree the purpose, size, deadline and price in writing (WhatsApp or email is fine).</li>
<li><strong>Draft:</strong> send a low-resolution preview with a clear watermark if payment is outstanding.</li>
<li><strong>Revisions:</strong> agree how many rounds are included, such as two.</li>
<li><strong>Delivery:</strong> send the final files in the formats agreed, after payment or deposit as agreed.</li>
<li><strong>Archive:</strong> keep the project folder for at least a year in case of reprints.</li>
</ol>

<h3>Free alternatives</h3>
<p>Folder habits, backups, licences and client rules work the same whatever tool you use. Free cloud storage plans exist from several providers; choose one you trust and read its privacy terms.</p>

<blockquote><p><strong>Quick check:</strong> A client wants a change six months after delivery. Which folders hold what you need?</p><p>Answer: The working and archive folders, which hold your PSD with layers; without them you would have to rebuild from a flat export.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You find a perfect photo through an image search. Can you use it on a client's flyer?</p><p>Answer: Not without checking its licence or permission, because most online images are copyrighted.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create the five-folder structure on your computer for your capstone project.</li>
<li>Write a short licence log (a text file) for each image you plan to use.</li>
<li>Rename and group the layers in one of your earlier PSD files.</li>
<li>Back up the folder to a second location, such as a flash disk or cloud storage.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>One folder structure per job; clear names and versions.</li>
<li>Organised layers make editing and handover easy.</li>
<li>Keep backups in more than one place.</li>
<li>Check licences and consent; agree scope and revisions with clients.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Capstone project: a poster campaign or photo composite',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will have produced a finished, portfolio-ready piece that shows the skills of the whole course. This matters because employers and clients judge designers by finished work, and one polished project is worth more than ten half-done exercises.</p>

<h3>Choose one option</h3>
<table>
<thead><tr><th>Option</th><th>What you produce</th></tr></thead>
<tbody>
<tr><td>A: Poster campaign</td><td>An A3 poster (print-ready PDF), a 1080 x 1350 social post, a 1080 x 1920 story, and a 1280 x 720 banner for one event or cause, with a consistent look</td></tr>
<tr><td>B: Photo composite</td><td>One creative composite image (at least 3000 px on the long side) combining at least three photos, with a short "making of" note</td></tr>
</tbody>
</table>
<p>Pick a real cause you care about: a school open day, a clean-up in your town, a clinic outreach, a small business launch or a community football tournament. Get permission for anything real you use.</p>

<h3>Project steps</h3>
<ol>
<li><strong>Brief (30 minutes).</strong> Write the message, audience, call to action, sizes and deadline. Choose a palette of three colours and two fonts.</li>
<li><strong>Collect (30 minutes).</strong> Gather your own photos or free-licence ones. Log licences. Set up the folder structure.</li>
<li><strong>Build the master (1 to 2 hours).</strong> Create the main layout. Use smart objects for photos, masks for cut-outs, adjustment layers for colour, and clear layer names.</li>
<li><strong>Adapt (1 hour).</strong> For option A, resize the layout for each format, rearrange text for each ratio; do not just stretch. For option B, add shadows, colour grade and grain.</li>
<li><strong>Review.</strong> Squint test, phone test, spelling and number check, contrast check. Ask a friend or family member: "What is this about?" If they cannot say in five seconds, simplify.</li>
<li><strong>Export.</strong> JPG or PNG for screen; PDF with bleed for print; save the layered PSD master.</li>
<li><strong>Present.</strong> Write a short case study: the brief, what you did, tools used, a before-and-after or process screenshot, and what you would improve.</li>
</ol>

<h3>Self-assessment checklist</h3>
<table>
<thead><tr><th>Area</th><th>Question to ask yourself</th></tr></thead>
<tbody>
<tr><td>Message</td><td>Is the main point clear in five seconds?</td></tr>
<tr><td>Layout</td><td>Is there one focal point, good hierarchy and aligned elements?</td></tr>
<tr><td>Type</td><td>Two fonts or fewer, readable size, strong contrast?</td></tr>
<tr><td>Image quality</td><td>Sharp, clean edges, matched light and colour?</td></tr>
<tr><td>Technical</td><td>Correct sizes, sRGB for web, bleed and 300 PPI for print?</td></tr>
<tr><td>Non-destructive</td><td>Layers named, masks, smart objects, adjustment layers used?</td></tr>
<tr><td>Ethics</td><td>Licences logged, consent obtained, no misleading edits?</td></tr>
</tbody>
</table>

<h3>Building your portfolio</h3>
<ul>
<li>Choose your best 5 to 8 pieces, not everything.</li>
<li>Show process images (before and after) next to the final design.</li>
<li>Present them on a free portfolio site, a social page for your work, or a PDF you can send on WhatsApp or email.</li>
<li>Include your name, services and a clear contact method.</li>
<li>Practise explaining your choices aloud; clients like to hear your reasoning.</li>
</ul>

<h3>Free alternatives</h3>
<p>You can complete the entire capstone in Photopea, or with GIMP, Krita and Inkscape together. The technique matters more than the brand. If you use a free tool, note in your case study which tools you used.</p>

<h3>Mini-case</h3>
<p>Mercy, a university student in Mbarara, chose Option A for a student-led tree planting day. She made one dark green and gold style, shot her own photos, cut out a student holding a seedling, and adapted the layout for four sizes. Her case study with before-and-after images helped her win her first paid job, designing posters for a local NGO.</p>

<blockquote><p><strong>Quick check:</strong> When adapting a poster into a story size, should you just stretch it to 9:16?</p><p>Answer: No, rearrange the layout for the new ratio, because stretching distorts images and text.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What proves a student finished the project professionally, apart from the final image?</p><p>Answer: A layered, named PSD master, a licence log and a short case study showing the brief and process.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick Option A or B and write your brief in a text file.</li>
<li>Complete the project steps above over several sessions.</li>
<li>Run the self-assessment checklist and fix at least three issues.</li>
<li>Export the final files, write your case study, and place everything in your portfolio folder. Share it with a friend or teacher for feedback.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A finished project proves your skill better than lists of exercises.</li>
<li>Follow brief, collect, build, adapt, review, export, present.</li>
<li>Adapt layouts for each format instead of stretching.</li>
<li>Show your process in a case study and keep your PSD master.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Generative Fill returns an image area with distorted text and an odd hand. What is the correct response?
- Publish it; AI results are always right
* Check it closely, regenerate or fix by hand, and use a mask to blend
- Convert to CMYK
- Flatten the image immediately
= AI often makes errors in text and hands, so you must check and correct.

Q: You are exporting a product photo for a website. Which is the best set of choices?
- 4000 px wide, JPG quality 100
* About 1200 px wide, JPG or WebP at roughly 80 percent, converted to sRGB
- 300 px wide PNG
- PSD file
= Resize to the displayed size, compress sensibly and use sRGB for the web.

Q: Which colour mode and settings should you check with the printer before finishing a print job?
- Only the font name
* File format, RGB or CMYK profile, bleed and final size
- Only the layer names
- The number of layers
= Each print shop has its own requirements.

Q: A flyer should be printed at A5 and look sharp. Which setting is the usual target?
- 72 PPI at final size
* 300 PPI at final size
- 10 PPI at final size
- Resolution does not matter in print
= Around 300 PPI gives sharp small prints.

Q: A neon green looks duller after you convert to CMYK. What does this tell you?
- The file is corrupted
* CMYK ink cannot reproduce some bright screen colours
- You need a bigger canvas
- Photoshop has a bug
= Screens show brighter colours than ink on paper.

Q: Which practice best protects you against losing client files?
- Keep one copy on the desktop
* Keep copies in more than one place, including one away from the computer
- Email files only to yourself once
- Delete the PSD after exporting the JPG
= Backups in several locations protect against power cuts, viruses and theft.

Q: You want to use a photo found on a search engine on a client's poster. What should you do?
- Use it because it is public
* Check the licence or ask permission, or use your own or a properly licensed image
- Use it but reduce the opacity
- Use it if it is smaller than 500 px
= Most online images are copyrighted; you need permission or a suitable licence.

Q: Which statements about adapting a poster for several sizes are correct? (choose all that apply)
* Rearrange the layout for each ratio
* Check that important text is inside safe margins
- Stretch the artwork to fit
- Use one tiny font size for every format
= Each format needs its own composition; stretching distorts the design.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Photoshop is best described as which kind of program?
- A vector-only drawing program
* A raster image editor built mainly for photos and digital design
- A spreadsheet program
- A video player
= Photoshop works with pixels, though it also has simple vector shapes.

Q: A school asks you to make a web banner. Which colour mode and profile are normal?
- CMYK with a print profile
- Grayscale
* RGB with sRGB
- Bitmap
= Screens use RGB and browsers assume sRGB.

Q: What does Resample do in the Image Size dialog?
- Changes the file format
* Adds or removes pixels when the size changes
- Converts to CMYK
- Locks the layers
= With Resample off, the pixel count stays the same and only the print size changes.

Q: You need to edit a design again next month. Which file must you keep?
- The JPG you posted
- A screenshot
* The layered PSD
- A WhatsApp copy
= The PSD preserves layers, text and adjustments.

Q: On a layer mask, you paint with black. What happens?
- The area becomes brighter
* The painted area of the layer is hidden
- The layer is deleted permanently
- The mask is inverted
= Black hides on a layer mask and can be repainted with white.

Q: You want a photo to appear only inside a circle shape. Which feature is the best fit?
- Flatten image
* Clipping mask
- Canvas Size
- Gaussian Blur
= A clipping mask shows a layer only within the shape of the layer below.

Q: A cut-out of a person has a thin light fringe from the old background. What helps most?
- Convert to Grayscale
* Decontaminate Colors in Select and Mask and a refined mask edge
- Increase the resolution
- Flip the image
= Fringes come from the old background colours; decontaminating and refining the edge removes them.

Q: Which blend mode darkens and makes white vanish?
- Screen
- Color
* Multiply
- Lighten
= Multiply is commonly used for shadows and textures.

Q: You want to brighten only a face and keep the rest unchanged without damaging the photo. What is the best approach?
- Use Image > Adjustments > Brightness/Contrast on the whole image
* Select the face with a feather, then add a Curves adjustment layer so the selection becomes its mask
- Delete the background
- Convert the image to CMYK first
= An adjustment layer with a feathered selection mask is local and non-destructive.

Q: A photo looks hazy and the histogram is bunched in the middle. Which adjustment fits first?
- Black and White
* Levels or Curves
- Add Noise
- Gaussian Blur
= Levels or Curves spreads the tones to restore contrast.

Q: An indoor photo looks orange under yellow bulbs. Which is a suitable fix?
- Add a dark Drop Shadow
* Correct the colour cast with Color Balance or a neutral grey point in Curves
- Crop it
- Export as PNG
= A colour cast is corrected by shifting toward the opposite colour.

Q: Why should you retouch on a separate empty layer?
- Because Photoshop cannot heal the Background layer
* The repair stays separate, so it can be erased or hidden without harming the original pixels
- It reduces the file size
- It converts the layer to a smart object
= A retouch layer with Sample All Layers keeps the original photo intact.

Q: A sign has been removed from a brick wall and you must restore the pattern. Which tool is best?
- Blur tool
* Clone Stamp from matching bricks
- Gradient tool
- Paint Bucket
= The Clone Stamp copies pixels exactly, so patterns line up.

Q: Which is an honest and acceptable retouch of a portrait?
* Removing a temporary spot and keeping natural skin texture
- Changing the person's body shape without asking
- Removing a person from a news photograph
- Making a person look much younger for an ID photo
= Light, consented corrections are fine; misleading or harmful changes are not.

Q: What is the benefit of a smart object when you scale an image down and later back up?
- It changes the image to vector
* It keeps the original pixel data so there is no extra loss
- It makes the file smaller than a JPG
- It adds a drop shadow
= Smart objects preserve original data, up to its first size.

Q: Which statement about smart filters is true?
- They cannot be undone
* They stay editable and have a mask you can paint on
- They only work on text
- They convert the file to PNG
= Smart filters are applied to smart objects non-destructively.

Q: When is the best time to sharpen an image for web use?
- Before any resizing
* After resizing to the final size, gently
- Never
- Only when saving as PSD
= Resizing changes sharpness, so sharpen last.

Q: You set a white headline over a bright photo and cannot read it. What is the best fix?
- Make the headline thinner
* Add a semi-transparent dark shape behind it or a subtle shadow
- Make the photo larger
- Change to CMYK
= Increasing contrast makes text readable.

Q: A printed A4 flyer will be printed edge to edge. Which setting is most important to add?
- A drop shadow
* 3 mm bleed on all sides
- A transparent background
- A watermark across the page
= Bleed prevents white edges after trimming.

Q: You have a 600 pixel wide photo and the client wants it printed on A3. What is the honest advice?
- Enlarge it with Resample and it will be fine
* Ask for a larger original, because enlarging a small image will look soft
- Convert it to CMYK
- Save it as TIFF
= Upsampling invents pixels and cannot create real detail.

Q: Which pixel size is standard for a YouTube thumbnail?
- 1080 x 1920
- 600 x 600
* 1280 x 720
- 3508 x 2480
= It uses 16:9 and 1280 x 720; always confirm on the platform's help page.

Q: A product photo for a website is 4.8 MB. Which change helps visitors with limited data?
- Upload the original so it looks the best
* Resize to the displayed width and export as JPG or WebP at a sensible quality
- Convert to CMYK
- Save as PSD
= Smaller, right-sized images load quickly and save data.

Q: Which should be ticked when exporting a photo for the web so colours look correct in browsers?
- Convert to CMYK
* Convert to sRGB
- Convert to Bitmap
- Flatten transparency
= Browsers and phones assume sRGB.

Q: You will run an action on 200 photos. What is the safest practice?
- Overwrite the originals so the folder stays tidy
* Test on a few files and send the results to a different folder
- Run it on one photo only
- Close all other programs
= Testing and a separate destination protect the originals.

Q: Which are sensible ways to use AI features in Photoshop? (choose all that apply)
* Check the result carefully for errors, bias and mismatched light
* Follow the terms, privacy rules and your school or client rules
- Fake a photo of a real event to publish as news
- Upload clients' private photos without permission
= AI helps with speed, but you remain responsible for truth, privacy and rules.

Q: Which of these usually need checking before you use them in a client's work? (choose all that apply)
* Photos from stock sites
* Fonts
- A photo you took yourself of an empty field
* Images found through a search engine
= Licences differ and most online images are copyrighted. Your own photo of an empty field needs no permission.

Q: A mockup PSD has a smart object named "Your design here". How do you use it?
- Delete it and paint over it
* Double-click it, place your design, save and close it
- Flatten the image and re-save as JPG
- Convert it to CMYK
= The smart object updates the whole mockup after you save.

Q: Your printer asks for CMYK. What is the best workflow?
- Design everything in CMYK from the start and never preview
* Design in RGB, preview with Proof Colors, then convert a copy to CMYK using the printer's profile
- Convert back and forth several times
- Ignore the request
= Working in RGB and converting a copy at the end keeps your master safe and avoids repeated conversion.

Q: Which habit most helps you edit a project again six months later?
- Name layers "Layer 1", "Layer 2" and so on
* Keep named, grouped layers in a PSD inside an organised job folder with backups
- Keep only a flattened JPG
- Keep it in one folder with other jobs
= Organised files, named layers and backups make changes easy and safe.
QUIZ,
];
