<?php
return [
    'slug'        => 'ms-powerpoint',
    'title'       => 'Microsoft PowerPoint: Presentations that Work',
    'category'    => 'office',
    'level'       => 'beginner',
    'icon'        => 'layers',
    'sort'        => 50,
    'summary'     => 'Build clear, good-looking slide presentations in PowerPoint and deliver them with confidence, from the first slide to the final talk.',
    'description' => '<p>PowerPoint is a program for making slides: pages of words, pictures, charts and video that you show while you speak. Students use it for class projects, NGOs use it to report to donors, and small businesses use it to pitch to customers. A good presentation helps people understand and remember your message.</p><p>This course teaches you both the buttons and the thinking. You will learn how to build slides, how to design them so people can read them, how to add motion and media sensibly, and how to stand up and deliver with confidence.</p><p>You need no experience. Each lesson ends with a short practice task, and by the end you will have made a complete presentation of your own. The steps match Microsoft 365 and PowerPoint 2019 or later on Windows 10 and 11, and most steps work on a Mac too.</p>',
    'outcomes'    => [
        'Create, save and organise a presentation with slides, layouts and themes',
        'Build slides with text, pictures, shapes, tables, charts and SmartArt',
        'Use the Slide Master to keep every slide consistent',
        'Design slides that have one clear message, strong contrast and little clutter',
        'Make presentations accessible with alt text, reading order and readable fonts',
        'Add transitions, animations, audio, video and links with good judgement',
        'Use speaker notes, Presenter View and rehearsed timings',
        'Export, share and present in person or online with confidence',
    ],
    'requirements' => ['A computer with PowerPoint installed (or PowerPoint online)', 'No experience needed'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Microsoft Support: PowerPoint help', 'url' => 'https://support.microsoft.com/powerpoint'],
    ],
    'modules' => [
        [
            'title'   => 'Module 1: Getting started with PowerPoint',
            'summary' => 'Learn the window, slides and layouts, and how to start, save and view a presentation.',
            'lessons' => [
                [
                    'title'   => 'The PowerPoint window and what a slide is',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to open PowerPoint, name the main parts of the window and explain what a slide and a presentation are. This knowledge makes the following lessons easy, because every instruction in this course refers to these parts.</p>
<h3>What is PowerPoint?</h3>
<p><strong>PowerPoint</strong> is a program from Microsoft for making <strong>presentations</strong>. A presentation is a file made of many <strong>slides</strong>, like pages in a book. You show the slides on a screen or projector while you speak. A PowerPoint file ends with <code>.pptx</code>.</p>
<h3>Opening PowerPoint</h3>
<ol>
<li>Click the <strong>Start</strong> button (the Windows logo).</li>
<li>Type <code>PowerPoint</code> and press <strong>Enter</strong>.</li>
<li>Click <strong>Blank Presentation</strong> to begin.</li>
</ol>
<h3>The parts of the window</h3>
<table>
<thead><tr><th>Part</th><th>Where</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Ribbon</td><td>Top</td><td>Tabs full of buttons: File, Home, Insert, Draw, Design, Transitions, Animations, Slide Show, Review, View.</td></tr>
<tr><td>Quick Access Toolbar</td><td>Top left</td><td>Save, Undo, Redo and other shortcuts.</td></tr>
<tr><td>Thumbnail pane</td><td>Left</td><td>Small pictures of all slides. Click one to open it, drag to change the order.</td></tr>
<tr><td>Slide pane</td><td>Centre</td><td>The big slide where you work.</td></tr>
<tr><td>Notes pane</td><td>Under the slide</td><td>Where you type what you plan to say. The audience does not see it.</td></tr>
<tr><td>Status bar</td><td>Bottom</td><td>Slide number, Notes button, view buttons and the zoom slider.</td></tr>
</tbody>
</table>
<p>If you cannot see the Notes pane, click <strong>Notes</strong> in the status bar at the bottom.</p>
<h3>Slides and placeholders</h3>
<p>A new slide has dotted boxes that say things like <em>Click to add title</em>. These boxes are called <strong>placeholders</strong>. Click inside one and type. Placeholders keep text in the right place and use the correct size and font. Text can also be placed in separate <strong>text boxes</strong> (Insert, then Text Box), but placeholders are better because they appear in the Outline view and help screen readers.</p>
<h3>What makes a good presentation?</h3>
<p>A presentation is not a document projected on a wall. The slides support you, the speaker. Put only the main idea and the proof on the slide, and say the rest. A slide that is full of paragraphs makes people read instead of listen.</p>
<h3>Common mistakes</h3>
<ul>
<li>Typing a lot of text in the slide and reading it aloud.</li>
<li>Pressing the wrong button and not knowing how to get back: remember Undo (Ctrl+Z).</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Open PowerPoint and choose Blank Presentation.</li>
<li>Click the title placeholder and type <code>My Name and My Town</code>.</li>
<li>Click the subtitle placeholder and type your favourite subject.</li>
<li>Click each ribbon tab and look at the buttons.</li>
<li>Save the file as <code>My first slides</code> in your Documents folder.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A presentation is a file of slides, saved as .pptx.</li>
<li>The thumbnail pane lists slides; the slide pane is where you edit; the notes pane holds your speaker notes.</li>
<li>Placeholders are the ready-made boxes for titles and content.</li>
<li>Slides are for support. Keep the details in your speech.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Layouts, new slides and themes',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>You will learn to add slides, choose the right layout for each one, and change the look of the whole presentation with a theme. Choosing a layout and a theme early saves you from manual formatting later.</p>
<h3>Adding a new slide</h3>
<ol>
<li>Click the <strong>Home</strong> tab.</li>
<li>Click the <strong>top part</strong> of <strong>New Slide</strong> to add a slide with the same layout, or click the <strong>arrow below</strong> it to choose a layout.</li>
<li>Or click in the thumbnail pane and press <strong>Enter</strong>, or <strong>Ctrl+M</strong> (Mac: Cmd+Shift+N).</li>
</ol>
<h3>What is a layout?</h3>
<p>A <strong>layout</strong> is the arrangement of placeholders on a slide. Choose the one that matches what you want to say.</p>
<table>
<thead><tr><th>Layout</th><th>Use it for</th></tr></thead>
<tbody>
<tr><td>Title Slide</td><td>The first slide: title and your name</td></tr>
<tr><td>Title and Content</td><td>A heading with bullet points, a picture, a table or a chart</td></tr>
<tr><td>Section Header</td><td>A divider between parts of your talk</td></tr>
<tr><td>Two Content</td><td>Two lists, or text on one side and a picture on the other</td></tr>
<tr><td>Comparison</td><td>Two things side by side with headings</td></tr>
<tr><td>Title Only</td><td>A slide where you will place your own picture or diagram</td></tr>
<tr><td>Blank</td><td>A free canvas</td></tr>
</tbody>
</table>
<p>To change the layout of a slide you already made, click <strong>Home</strong>, then <strong>Layout</strong>, and pick another one. Your text moves into the new boxes. To start the slide again, click <strong>Reset</strong>.</p>
<h3>Themes</h3>
<p>A <strong>theme</strong> is a set of colours, fonts and background designs applied to all slides. Click the <strong>Design</strong> tab and click a theme in the gallery. Hover over a theme to see a preview. On the right side of the Design tab you will find <strong>Variants</strong>, which change colours and fonts within the same theme, and <strong>Slide Size</strong> (Widescreen 16:9 is the normal choice for modern screens).</p>
<p>Pick a theme before you add lots of content. Choose a simple one with a plain background and strong contrast between text and background. Avoid very busy designs: they fight with your message.</p>
<h3>Templates</h3>
<p>A <strong>template</strong> is a ready-made presentation with design and sample slides. Click <strong>File</strong>, then <strong>New</strong>, and search for words such as <code>report</code> or <code>pitch</code>. Choose one, create it, and replace the sample text. Remove any slides you do not need.</p>
<h3>Working with slides</h3>
<ul>
<li><strong>Duplicate</strong>: right-click a thumbnail and choose Duplicate Slide.</li>
<li><strong>Delete</strong>: select a thumbnail and press Delete.</li>
<li><strong>Move</strong>: drag a thumbnail up or down.</li>
<li><strong>Copy a slide</strong> from another file: select the thumbnails, press Ctrl+C, and paste with Ctrl+V.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Open your presentation from the last lesson.</li>
<li>Click the Design tab and apply a simple theme with a light background.</li>
<li>Add a Title and Content slide with the heading <code>About Me</code> and three bullet points.</li>
<li>Add a Section Header slide and a Two Content slide.</li>
<li>Change the Two Content slide to a Comparison layout, then drag one slide to a new position.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>New Slide on the Home tab adds slides; the arrow lets you pick the layout.</li>
<li>Pick a layout that matches the message of the slide.</li>
<li>A theme sets colours, fonts and the background for the whole presentation.</li>
<li>Choose plain themes with strong contrast.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Saving, views and the Slide Sorter',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>In this lesson you will save your work safely, look at your presentation in the different views, and reorganise slides in the Slide Sorter. Knowing the views lets you work faster and check your presentation before showing it.</p>
<h3>Saving</h3>
<ol>
<li>Press <strong>Ctrl+S</strong> (Mac: Cmd+S).</li>
<li>Choose a folder, type a clear file name such as <code>Youth Group Report 2026</code>, and click <strong>Save</strong>.</li>
<li>After that, press Ctrl+S every few minutes.</li>
</ol>
<p><strong>Save As</strong> (F12) makes a copy with a new name or format. If the file is on OneDrive, <strong>AutoSave</strong> at the top left saves automatically. Useful formats:</p>
<ul>
<li><strong>.pptx</strong>: normal, editable presentation.</li>
<li><strong>.ppsx</strong>: PowerPoint Show. It opens straight into the slide show.</li>
<li><strong>.pdf</strong>: a fixed copy for sharing or printing.</li>
<li><strong>.mp4</strong>: a video of the presentation.</li>
</ul>
<p>Power cuts are common, so do not wait until the end to save.</p>
<h3>The main views</h3>
<table>
<thead><tr><th>View</th><th>How to open</th><th>Best for</th></tr></thead>
<tbody>
<tr><td>Normal</td><td>View, then Normal</td><td>Building and editing slides</td></tr>
<tr><td>Outline View</td><td>View, then Outline View</td><td>Typing and reading all the text quickly</td></tr>
<tr><td>Slide Sorter</td><td>View, then Slide Sorter</td><td>Seeing many slides at once and changing their order</td></tr>
<tr><td>Notes Page</td><td>View, then Notes Page</td><td>Printing notes with each slide</td></tr>
<tr><td>Reading View</td><td>View, then Reading View</td><td>Previewing in a window</td></tr>
<tr><td>Slide Show</td><td>Slide Show tab or F5</td><td>Presenting full screen</td></tr>
</tbody>
</table>
<p>The small buttons at the bottom right of the window also switch between Normal, Slide Sorter, Reading View and Slide Show.</p>
<h3>Using the Slide Sorter</h3>
<ol>
<li>Click <strong>View</strong>, then <strong>Slide Sorter</strong>.</li>
<li>All slides appear as small pictures. Drag a slide to a new place to move it.</li>
<li>Right-click a slide to delete or duplicate it.</li>
<li>Click <strong>Normal</strong> in the View tab to go back.</li>
</ol>
<p>The Slide Sorter is also the best place to check whether your slides look alike and whether the story has a clear start, middle and end.</p>
<h3>Zoom and guides</h3>
<p>Use the slider at the bottom right to zoom, or click <strong>Fit slide to current window</strong> (the small box beside the slider). On the View tab, tick <strong>Ruler</strong>, <strong>Gridlines</strong> and <strong>Guides</strong> to help you line things up.</p>
<h3>Common mistakes</h3>
<ul>
<li>Saving only at the end of a long task.</li>
<li>Forgetting that .pptx on a USB drive may not have its fonts or videos on another computer. Test on the computer you will use.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Make a presentation with at least six slides, even with simple titles such as <code>Slide 1</code>, <code>Slide 2</code>.</li>
<li>Save it. Then use Save As to save a PDF copy.</li>
<li>Open Slide Sorter view and move the last slide to second position.</li>
<li>Open Outline View and type a new bullet point.</li>
<li>Press F5 to start the slide show, click through it, and press Esc to end.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Save early and often with Ctrl+S.</li>
<li>Normal view is for editing; Slide Sorter is for ordering slides.</li>
<li>F5 starts the slide show from the beginning; Esc ends it.</li>
<li>Choose PDF or MP4 when you need a fixed copy.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is a placeholder in PowerPoint?
- A hidden slide
* A ready-made dotted box on a slide where you type or add content
- A kind of animation
- A printer setting
= Placeholders hold titles, text, pictures and other content in the right position.

Q: Where do you type what you plan to say, so the audience does not see it?
- In the title placeholder
* In the Notes pane
- In the Slide Sorter
- In the status bar
= The notes pane holds speaker notes. They are shown only to you in Presenter View or on printed notes.

Q: Which layout is the best for a slide that compares two options side by side?
- Title Slide
- Blank
* Comparison
- Section Header
= The Comparison layout has headings and content for two things.

Q: What does a theme change?
- Only the speaker notes
* Colours, fonts and background across the whole presentation
- The file size only
- The printer
= A theme sets the overall design.

Q: Which view is best for changing the order of many slides at once?
- Notes Page
* Slide Sorter
- Slide Show
- Reading View
= Slide Sorter shows thumbnails that you drag to reorder.

Q: What does F5 do in PowerPoint?
- Saves the file
- Opens the Design tab
* Starts the slide show from the beginning
- Deletes the slide
= F5 runs the slide show. Press Esc to stop.

Q: Which file type is a fixed copy that is easy to share and cannot be changed easily?
- .pptx
* .pdf
- .potx
- .ppsx
= PDF keeps a fixed layout on every device.

Q: Why is a slide full of paragraphs a problem?
- The computer runs slower
* People read instead of listening to you
- It cannot be saved
- It uses the wrong layout
= Slides support the speaker. Keep detail in your speech.
QUIZ,
        ],
        [
            'title'   => 'Module 2: Building slides',
            'summary' => 'Add and format text, use the Slide Master, insert content and arrange it neatly.',
            'lessons' => [
                [
                    'title'   => 'Titles, bullets and text formatting',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>You will learn how to write good slide text, format it, and manage bullet lists. Text is still the most common content on slides, and learning to write it short and format it clearly is the base of every good presentation.</p>
<h3>Writing short slide text</h3>
<ul>
<li>Give every slide a <strong>title</strong> that states the message, such as <em>Mobile money reaches more villages</em> instead of just <em>Mobile money</em>.</li>
<li>Use <strong>3 to 5 short bullet points</strong> at most, and a few words in each. Aim for a rule of thumb: no more than about 30 words on a slide.</li>
<li>Write phrases, not full sentences.</li>
<li>Use the same style for all bullets: either all start with a verb or all are nouns.</li>
</ul>
<h3>Typing and the bullet list</h3>
<ol>
<li>Click the content placeholder and type the first point.</li>
<li>Press <strong>Enter</strong> for a new bullet.</li>
<li>Press <strong>Tab</strong> to make a bullet a sub-point; <strong>Shift+Tab</strong> brings it back one level.</li>
<li>To change bullet styles, select the text and click the arrow beside <strong>Bullets</strong> (Home tab); choose <strong>Numbering</strong> for a sequence.</li>
</ol>
<h3>Formatting text</h3>
<p>The <strong>Font</strong> group on the <strong>Home</strong> tab works as in Word.</p>
<table>
<thead><tr><th>Action</th><th>Windows</th><th>Mac</th></tr></thead>
<tbody>
<tr><td>Bold</td><td>Ctrl+B</td><td>Cmd+B</td></tr>
<tr><td>Italic</td><td>Ctrl+I</td><td>Cmd+I</td></tr>
<tr><td>Underline</td><td>Ctrl+U</td><td>Cmd+U</td></tr>
<tr><td>Centre / Left / Right</td><td>Ctrl+E / L / R</td><td>Cmd+E / L / R</td></tr>
<tr><td>Increase / decrease font size</td><td>Ctrl+Shift+&gt; / &lt;</td><td>Cmd+Shift+&gt; / &lt;</td></tr>
</tbody>
</table>
<p>Use sizes that the back row can read. As a guide:</p>
<ul>
<li>Titles: 32 to 44 points.</li>
<li>Main text: 24 points or larger.</li>
<li>Never go below 18 points for anything the audience must read.</li>
</ul>
<h3>AutoFit warnings</h3>
<p>PowerPoint can shrink text automatically to fit a box. If the text has shrunk so much that it looks small, it is a sign that you have written too much. Cut the words, or split the content into two slides.</p>
<h3>Line spacing and alignment</h3>
<p>Click <strong>Line Spacing</strong> in the Paragraph group to add breathing space between lines. Keep text left-aligned for lists and short sentences. Centre text only for titles or one short statement.</p>
<h3>Text boxes</h3>
<p>Use <strong>Insert</strong>, then <strong>Text Box</strong>, and drag on the slide to add text outside a placeholder, for example a quote or a caption. Format it like normal text.</p>
<h3>Common mistakes</h3>
<ul>
<li>Writing whole sentences and reading them from the slide.</li>
<li>Using ALL CAPITALS for everything. It is hard to read.</li>
<li>Mixing many fonts and sizes.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Create a Title and Content slide with the title <code>Why Learn Computer Skills</code>.</li>
<li>Write four short bullet points (for example: apply for jobs online, send email, use mobile money safely, make documents).</li>
<li>Add a sub-point under one bullet using Tab.</li>
<li>Change the bullets to numbers, then back.</li>
<li>Check that the text is at least 24 points. If it is not, shorten it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Titles should state the message of the slide.</li>
<li>Use 3 to 5 short bullets, written as phrases.</li>
<li>Keep main text large (24 points or more) and never smaller than 18.</li>
<li>If text shrinks to fit, you have written too much.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The Slide Master: one change, every slide',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn the basics of the <strong>Slide Master</strong>, the hidden slide that controls the look of all other slides. After it you will be able to add a logo to every slide, change fonts for the whole presentation and keep your design consistent, all with a few clicks.</p>
<h3>What is the Slide Master?</h3>
<p>Think of the Slide Master as the mould from which every slide is made. If you change the mould, all slides change. Without it, you might spend an hour adding a logo to 30 slides one by one.</p>
<h3>Opening it</h3>
<ol>
<li>Click the <strong>View</strong> tab.</li>
<li>Click <strong>Slide Master</strong>.</li>
</ol>
<p>The left pane now shows a large slide at the top (the <strong>Slide Master</strong>) and smaller ones below it (the <strong>layouts</strong>, such as Title Slide, Title and Content, and so on). A <strong>Slide Master</strong> tab appears on the ribbon.</p>
<h3>What you can do there</h3>
<table>
<thead><tr><th>Goal</th><th>How</th></tr></thead>
<tbody>
<tr><td>Change the font of all titles</td><td>Click the top master slide, select the title box, and choose a font and size</td></tr>
<tr><td>Change all bullets or body text</td><td>Format the text in the body placeholder on the master</td></tr>
<tr><td>Add a logo to every slide</td><td>On the top master slide, click Insert, then Pictures, and place the logo in a corner</td></tr>
<tr><td>Change the background</td><td>Click Background Styles in the Slide Master tab</td></tr>
<tr><td>Change theme fonts and colours</td><td>Use Fonts and Colors in the Slide Master tab</td></tr>
<tr><td>Show slide numbers</td><td>Insert slide number in the footer area, or use Insert, then Slide Number later</td></tr>
</tbody>
</table>
<p>Changes on the top master slide apply to all layouts. Changes on one layout (for example, Title Slide) affect only slides that use that layout. A logo on the Title Slide layout may be a good idea, but not necessarily on every layout.</p>
<h3>Closing</h3>
<p>Click <strong>Close Master View</strong> on the Slide Master tab. Your slides now show the changes. If you edit text on a single slide in Normal view, you can still override the master, but try not to: it breaks consistency.</p>
<h3>Footers and slide numbers</h3>
<p>Click <strong>Insert</strong>, then <strong>Header &amp; Footer</strong>. Tick <strong>Slide number</strong> and <strong>Footer</strong> (type text such as your organisation's name), then click <strong>Apply to All</strong>. You can tick <strong>Don't show on title slide</strong>.</p>
<h3>Common mistakes</h3>
<ul>
<li>Formatting every slide by hand when the master could do it once.</li>
<li>Putting a very large logo that covers content.</li>
<li>Deleting layouts you might need later.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Open a presentation with at least four slides.</li>
<li>Go to View, then Slide Master. Select the top master slide.</li>
<li>Change the title font to a bold, dark colour and size 36.</li>
<li>Insert a small picture or a shape as a logo in the bottom corner.</li>
<li>Close Master View and check that every slide shows the change.</li>
<li>Add slide numbers with Insert, then Header &amp; Footer.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The Slide Master controls the design of all slides.</li>
<li>Change the top master slide to affect everything, or one layout to affect only that layout.</li>
<li>Logos, footers and font changes belong in the master.</li>
<li>Click Close Master View when finished.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Pictures, icons, shapes, tables, charts and SmartArt',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>You will learn how to add visual content to your slides: pictures, icons, shapes, tables, charts and SmartArt. Pictures and diagrams explain ideas faster than words, and audiences remember them better.</p>
<h3>Pictures</h3>
<ol>
<li>Click the <strong>Insert</strong> tab, then <strong>Pictures</strong>, then <strong>This Device</strong>.</li>
<li>Select your photo and click <strong>Insert</strong>.</li>
<li>Drag a <strong>corner</strong> handle to resize it without distorting it.</li>
<li>Use <strong>Picture Format</strong>, then <strong>Crop</strong> to cut away what you do not need.</li>
</ol>
<p>You can also click the small picture icon in a content placeholder. Use your own photos where you can, for example of your project. Do not copy images from the internet unless you know you may use them.</p>
<h3>Icons and shapes</h3>
<p><strong>Insert</strong>, then <strong>Icons</strong> opens a library of simple pictures (for example, a phone, a book or a person). They can be recoloured: click one, then <strong>Graphics Format</strong>, then <strong>Graphics Fill</strong>. Shapes (<strong>Insert</strong>, then <strong>Shapes</strong>) include rectangles, circles, arrows and lines. Hold Shift while drawing for perfect squares and circles. You can type words inside a shape.</p>
<h3>Tables</h3>
<ol>
<li>Click <strong>Insert</strong>, then <strong>Table</strong>.</li>
<li>Drag over the grid to choose rows and columns.</li>
<li>Type in the cells and press Tab to move.</li>
<li>Use the <strong>Table Design</strong> tab to choose a style and the <strong>Layout</strong> tab to add or remove rows.</li>
</ol>
<p>Keep tables small: up to about four columns and five rows, with large text.</p>
<h3>Charts</h3>
<p>Click <strong>Insert</strong>, then <strong>Chart</strong>, then choose a type. A small data sheet opens. Replace the sample numbers, close it, and the chart updates. Choose:</p>
<ul>
<li><strong>Column or bar</strong> to compare amounts.</li>
<li><strong>Line</strong> for change over time.</li>
<li><strong>Pie</strong> for parts of a whole, with only a few slices.</li>
</ul>
<p>Give a chart a title that says what it shows. To change numbers later, click the chart and choose <strong>Chart Design</strong>, then <strong>Edit Data</strong>.</p>
<h3>SmartArt</h3>
<p>Click <strong>Insert</strong>, then <strong>SmartArt</strong>. Choose a category such as <strong>Process</strong> (steps), <strong>Cycle</strong> (repeating stages), <strong>Hierarchy</strong> (organisation chart) or <strong>List</strong>. Type text in the shapes or in the text pane. Press Enter to add a shape. SmartArt is good for a simple process such as <em>Apply, Interview, Training, Job</em>. Keep each shape to a few words.</p>
<h3>Screenshots</h3>
<p>Use <strong>Insert</strong>, then <strong>Screenshot</strong> or <strong>Screen Clipping</strong> to capture part of another window, handy for software training.</p>
<h3>Common mistakes</h3>
<ul>
<li>Stretching pictures so faces look wrong.</li>
<li>Choosing a 3D or pie chart with ten slices.</li>
<li>Putting a screenshot that is too small to read.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Create a Title and Content slide called <code>Our Project in Pictures</code> and insert a photo.</li>
<li>Add a new slide with a column chart of the number of learners in three classes.</li>
<li>Add another slide with a SmartArt Basic Process with 4 steps.</li>
<li>Add an icon next to a heading and change its colour.</li>
<li>Insert a small table of three rows to list prices in UGX.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Use the Insert tab for pictures, icons, shapes, tables, charts and SmartArt.</li>
<li>Resize with a corner handle so pictures do not distort.</li>
<li>Choose a chart type that matches the message.</li>
<li>Keep tables and diagrams small and readable.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Arranging objects, colour and font pairing',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>This lesson teaches you to arrange objects neatly with the alignment tools, and choose colours and fonts that go well together. Slides that look tidy and consistent feel trustworthy, even before the audience reads a word.</p>
<h3>Selecting several objects</h3>
<p>Click one object, hold <strong>Shift</strong> and click the others. Or drag a box around them. Press <strong>Ctrl+A</strong> to select all on the slide. The <strong>Selection Pane</strong> (Home, then Arrange, then Selection Pane) lists every object and lets you hide or select one that is hidden underneath another.</p>
<h3>Aligning and distributing</h3>
<ol>
<li>Select the objects.</li>
<li>Click <strong>Shape Format</strong> (or Picture Format), then <strong>Align</strong>.</li>
<li>Choose <strong>Align Left</strong>, <strong>Align Center</strong>, <strong>Align Top</strong>, and so on.</li>
<li>Choose <strong>Distribute Horizontally</strong> or <strong>Distribute Vertically</strong> to make the gaps between them equal.</li>
</ol>
<p>Turn on <strong>Smart Guides</strong>: as you drag, red dotted lines show when objects are in line with others. On the View tab you can also switch on <strong>Gridlines</strong> and <strong>Guides</strong>. To nudge an object a tiny amount, use the arrow keys; hold Alt (Windows) for even finer moves.</p>
<h3>Group, order and rotate</h3>
<ul>
<li><strong>Group</strong> (Ctrl+G): joins objects so they move and resize together; <strong>Ungroup</strong> is Ctrl+Shift+G.</li>
<li><strong>Bring Forward / Send Backward</strong>: change which object is on top.</li>
<li><strong>Rotate</strong>: drag the circular handle, or use Shape Format, then Rotate.</li>
<li><strong>Duplicate</strong>: Ctrl+D copies the selected object.</li>
</ul>
<h3>Choosing colours</h3>
<p>Colours carry mood and meaning. Use a small <strong>palette</strong>: one main colour, one accent, and neutral colours (white, black, grey). Many slides look best with 2 to 3 colours.</p>
<ol>
<li>Click <strong>Design</strong>, then <strong>Variants</strong>, then <strong>Colors</strong> to change the theme colours of the whole presentation.</li>
<li>For a single object, use <strong>Shape Fill</strong> or <strong>Font Color</strong>, and pick from the theme colour row so the object follows the theme.</li>
<li>Use the <strong>Eyedropper</strong> in the colour menu to match a colour from your logo.</li>
</ol>
<p>Use the accent colour for what matters most, like a key number. If everything is bright, nothing stands out.</p>
<h3>Font pairing</h3>
<p>A good rule: use <strong>two fonts only</strong>, one for headings and one for body text. Choose a clear sans serif font (letters without small feet), such as Calibri or Arial, for the body. You can combine it with a bolder heading font. Set this once in <strong>Design</strong>, <strong>Variants</strong>, <strong>Fonts</strong>, or in the Slide Master. Do not use decorative or handwriting fonts for important text.</p>
<h3>Common mistakes</h3>
<ul>
<li>Pictures and boxes that are slightly out of line.</li>
<li>Using red text on green, or other colours that are hard to tell apart for people with colour blindness.</li>
<li>Using five different fonts.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Draw three equal rectangles on a slide and type <code>Plan</code>, <code>Do</code>, <code>Review</code> inside.</li>
<li>Select them all, use Align Top and Distribute Horizontally.</li>
<li>Give them the same fill colour and group them.</li>
<li>Change the theme fonts under Design, Variants, Fonts.</li>
<li>Choose a different colour set under Variants, Colors and see how the slide changes.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Use Align and Distribute for neat layouts.</li>
<li>Group related objects and use Smart Guides.</li>
<li>Limit the palette to two or three colours.</li>
<li>Use two fonts at most, and set them in the theme or master.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the best way to write bullets on a slide?
- Complete paragraphs, so people can read along
* Short phrases of a few words each
- As many bullets as fit
- Only capital letters
= Short phrases keep the audience listening to you.

Q: What is the smallest font size you should use for text the audience must read?
- 8 points
- 12 points
* 18 points
- 6 points
= Never go below about 18 points. Aim for 24 or larger for body text.

Q: What is the Slide Master used for?
* Controlling the look of all slides, such as fonts and logos
- Making animations
- Recording sound
- Printing handouts
= A change in the master applies to every slide that uses it.

Q: You want your organisation's logo on every slide. Where do you put it?
- Copy it to every slide by hand
* On the top slide in Slide Master view
- In the notes pane
- In the Slide Sorter
= The master slide shows its objects on all slides.

Q: Which chart type is best to compare the amounts sold in five months?
* Column chart
- Pie chart with 10 slices
- No chart is possible
- 3D scatter
= Column or bar charts make comparison easy.

Q: Which SmartArt category would you use for the steps of applying for a job?
- Cycle
* Process
- Hierarchy
- Matrix
= Process diagrams show steps in order.

Q: Which tool makes the gaps between three boxes equal?
- Bring Forward
* Distribute Horizontally
- Group
- Crop
= Distribute spaces objects evenly.

Q: Which is a good colour approach for a presentation?
- Use every colour in the theme
* Use two or three colours and an accent for key items
- Use only neon colours
- Change the colours on every slide
= A small palette looks consistent, and an accent highlights what matters.
QUIZ,
        ],
        [
            'title'   => 'Module 3: Design that communicates',
            'summary' => 'Apply simple design principles: one message per slide, readability, images, charts, clutter and accessibility.',
            'lessons' => [
                [
                    'title'   => 'One message per slide and visual hierarchy',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to plan a presentation as a story, give each slide a single message, and arrange items so the eye knows where to look first. Good design is not decoration. It is the way you help people understand quickly.</p>
<h3>Plan before you open PowerPoint</h3>
<p>Take a sheet of paper and answer three questions:</p>
<ol>
<li><strong>Who</strong> is the audience (teachers, donors, customers, classmates)?</li>
<li><strong>What</strong> should they know or do after your talk? Write it in one sentence.</li>
<li><strong>How</strong> will you prove it: facts, a story, a picture, a figure?</li>
</ol>
<p>Then sketch a simple structure: an opening that grabs attention, 3 to 5 main points, and a clear ending with a request or conclusion. A short talk of 10 minutes usually needs about 8 to 12 slides.</p>
<h3>One message per slide</h3>
<p>Each slide should answer one question or support one point. If you find yourself saying <em>and also</em> while writing a slide, make a second slide. Slides are cheap; a confused audience is not. Write the title as a full message:</p>
<table>
<thead><tr><th>Weak title</th><th>Strong title</th></tr></thead>
<tbody>
<tr><td>Sales</td><td>Sales doubled after we started selling by phone</td></tr>
<tr><td>Water project</td><td>Clean water now reaches 600 households</td></tr>
<tr><td>Challenges</td><td>Three challenges slowed us down</td></tr>
</tbody>
</table>
<h3>Visual hierarchy</h3>
<p><strong>Visual hierarchy</strong> means that important things look important. The eye is drawn to:</p>
<ul>
<li>Larger items before smaller ones.</li>
<li>Bold or bright colours before pale ones.</li>
<li>Items at the top left before the bottom right (in languages read left to right).</li>
<li>Items with space around them before crowded ones.</li>
</ul>
<p>Use these deliberately. Make the title the largest text, the key number or picture the next most striking, and supporting text smaller. For example, a slide about funds could show <strong>UGX 12,000,000</strong> in very large letters with a short line below that says what it was spent on.</p>
<h3>White space</h3>
<p>Empty space is not wasted. It gives the eye a rest and makes the important parts stand out. Leave margins around the edges and space between groups. Do not fill every gap.</p>
<h3>The squint test</h3>
<p>Stand back from your screen and squint, or reduce the zoom. What do you see first? If it is not the main message, change the size or colour.</p>
<h3>Common mistakes</h3>
<ul>
<li>Putting an entire talk on one slide.</li>
<li>Making all items the same size so nothing leads the eye.</li>
<li>Using slides as your notes. Put notes in the Notes pane.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Choose a topic you know well, such as your school, a club or a business idea.</li>
<li>Write the one sentence you want your audience to remember.</li>
<li>List five points on paper and decide the title of one slide for each.</li>
<li>Build two slides: one with a very large key number or word, and one with a picture and a short title.</li>
<li>Use the squint test and adjust sizes until the main message stands out.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Plan the audience, the main message and the order before designing.</li>
<li>One slide, one message, with a title that says it.</li>
<li>Size, colour and position create hierarchy.</li>
<li>White space helps the important parts stand out.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Contrast, readability and using images well',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn how to make text readable from the back of a room, and how to use photographs and graphics in a way that helps your message. Many presentations fail because people cannot read the slide or because pictures are blurry, stretched or irrelevant.</p>
<h3>Contrast</h3>
<p><strong>Contrast</strong> is the difference between text and its background. High contrast is easy to read.</p>
<ul>
<li>Dark text on a light background, or white text on a dark background, are safe choices.</li>
<li>Avoid light grey on white, yellow on white, or red on blue.</li>
<li>Projectors make colours look paler than on your laptop screen, so make the contrast stronger than you think necessary.</li>
<li>Do not put text directly over a busy photograph. Place a semi-transparent rectangle behind the text, or choose a calm part of the picture.</li>
</ul>
<h3>Making text readable</h3>
<ol>
<li>Use large fonts: 24 points or more for body, 32 or more for titles.</li>
<li>Use a clear font (for example Calibri or Arial). Avoid thin, decorative or handwriting fonts.</li>
<li>Use bold for emphasis. Avoid underlining, which looks like a link.</li>
<li>Keep lines short, at most about eight words, and leave space between them.</li>
<li>Align text to the left.</li>
</ol>
<h3>Using images well</h3>
<p>A good image supports the message. It is not just there to fill space.</p>
<ul>
<li><strong>Quality:</strong> use sharp pictures. A small, blurry picture stretched to fill the slide looks poor. If you have to enlarge it a lot, choose another picture.</li>
<li><strong>Relevance:</strong> choose a picture that shows what you are talking about, for example your own project, the people and the place.</li>
<li><strong>Size:</strong> bigger is often better. A single large picture across the whole slide, with a short title, is more powerful than five small pictures.</li>
<li><strong>Crop:</strong> remove distracting edges using Picture Format, then Crop. Use <strong>Crop to Fill</strong> or <strong>Fit</strong>.</li>
<li><strong>Consistency:</strong> use a similar style across the presentation, such as all photos or all icons.</li>
<li><strong>Respect:</strong> ask permission before using photos of people, especially children, and show people with dignity.</li>
<li><strong>Copyright:</strong> do not copy pictures from websites unless you have permission. Use your own, or the images included with PowerPoint (<strong>Insert</strong>, <strong>Pictures</strong>, <strong>Stock Images</strong>) if available.</li>
</ul>
<h3>Text on a picture</h3>
<ol>
<li>Insert the picture and make it fill the slide.</li>
<li>Draw a rectangle with <strong>Insert</strong>, <strong>Shapes</strong>.</li>
<li>Set its fill to black or dark blue and the <strong>Transparency</strong> to about 40 to 60 percent (Shape Format, Shape Fill, More Fill Colors).</li>
<li>Type the title in white on the rectangle.</li>
</ol>
<h3>Check readability</h3>
<p>Look at your slides from about two metres away, or shrink your screen zoom. Can you still read them? Test on the real projector or screen if you can, before the event.</p>
<h3>Common mistakes</h3>
<ul>
<li>Too many small pictures that compete.</li>
<li>Putting white text on a light photograph.</li>
<li>Using clip art in a very different style on each slide.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Insert a photo of your own and make it fill a slide.</li>
<li>Add a transparent dark rectangle and a white title on it.</li>
<li>Create a second slide with the same title in grey text on white and compare which is easier to read.</li>
<li>Look at both from a distance of two metres and decide which one works better.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Contrast between text and background is essential, especially with projectors.</li>
<li>Use large, simple fonts and few words.</li>
<li>Choose sharp, relevant, respectful, and lawful images.</li>
<li>Put text on a plain or transparent background, not on a busy photo.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Consistency, data visualisation and avoiding clutter',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>You will learn how to keep your presentation consistent, show numbers clearly in charts, and remove unnecessary items. After the lesson you will be able to edit a crowded slide into a clean one. This is one of the most valuable skills in presentation design.</p>
<h3>Consistency</h3>
<p>Slides in a presentation should look as if they belong together.</p>
<ul>
<li>Use the same fonts and colours throughout (set them in the theme or Slide Master).</li>
<li>Put titles in the same place on each slide.</li>
<li>Use the same style of bullets and the same style of pictures.</li>
<li>Use the same transitions and animations.</li>
</ul>
<p>Use the layouts that PowerPoint provides, instead of drawing text boxes by hand. If a slide looks different from the others, click <strong>Home</strong>, then <strong>Reset</strong> to bring it back to the layout.</p>
<h3>Show numbers with charts</h3>
<p>Numbers in a table are hard to remember. A chart helps your audience see the pattern at once. Follow these rules:</p>
<ol>
<li>Choose <strong>one message</strong> for the chart and write it as the slide title, for example <em>Attendance rose every month</em>.</li>
<li>Use a <strong>column or bar chart</strong> to compare, a <strong>line chart</strong> for change over time, and a <strong>pie chart</strong> only for a few parts of a whole.</li>
<li>Remove clutter: delete heavy gridlines, extra borders, 3D effects and unnecessary legends.</li>
<li>Label the data directly if possible (<strong>Chart Design</strong>, <strong>Add Chart Element</strong>, <strong>Data Labels</strong>).</li>
<li>Use one colour for the bars, and a second accent colour for the bar you want to highlight.</li>
<li>Include units such as UGX or number of people.</li>
<li>Start column charts at zero, or the differences will look bigger than they are, which can mislead.</li>
</ol>
<h3>Avoiding clutter</h3>
<p><strong>Clutter</strong> is everything on a slide that does not help the message. Remove it. Ask of each item, <em>if I delete this, does the slide lose meaning?</em> If not, delete it.</p>
<table>
<thead><tr><th>Clutter</th><th>Fix</th></tr></thead>
<tbody>
<tr><td>Long paragraphs</td><td>Cut to key words, or split into several slides</td></tr>
<tr><td>Busy background picture</td><td>Use a plain background</td></tr>
<tr><td>Too many colours and fonts</td><td>Use the theme palette and two fonts</td></tr>
<tr><td>Decorative clip art</td><td>Remove it, or replace it with one good photo</td></tr>
<tr><td>Heavy lines and borders around everything</td><td>Use space to separate items</td></tr>
<tr><td>Logos and footers that are large</td><td>Make them small and subtle</td></tr>
</tbody>
</table>
<h3>Handling lots of detail</h3>
<p>If you must share a lot of information, such as a full budget, do not put it on the slide. Show the key figure on the slide and give the full data in a handout or an appendix at the end.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a slide with a title, eight bullets, a clip art picture and three colours.</li>
<li>Edit it: keep only the three most important bullets, remove the clip art and use only the theme colours.</li>
<li>Add a new slide with a column chart of monthly attendance. Write the message as the title.</li>
<li>Delete the gridlines, add data labels and colour the highest column with an accent.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Consistency comes from themes, layouts and the Slide Master.</li>
<li>A chart needs one clear message, units and little decoration.</li>
<li>Delete anything that does not help the message.</li>
<li>Put detailed data in a handout, not on the slide.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Making presentations accessible',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn how to make a presentation that more people can use, including people who are blind, have low vision, are colour blind or use a screen reader. Accessible design is good design for everyone: it also helps people who are far from the screen or reading in poor light.</p>
<h3>Why accessibility matters</h3>
<p>In every community there are people with visual, hearing, movement or learning difficulties. An inclusive presentation lets them take part. Many organisations and donors now expect accessible materials.</p>
<h3>Alt text for pictures</h3>
<p><strong>Alt text</strong> (alternative text) is a short description of a picture that a screen reader says aloud.</p>
<ol>
<li>Right-click the picture, chart or shape.</li>
<li>Choose <strong>View Alt Text</strong> (or <strong>Edit Alt Text</strong>).</li>
<li>Type one or two short sentences that describe what matters, for example: <code>Three students working on laptops in a classroom in Gulu</code>.</li>
<li>If a picture is purely decorative, tick <strong>Mark as decorative</strong>.</li>
</ol>
<h3>Reading order</h3>
<p>A screen reader reads objects in a set order, which may not be the order you see. To check it:</p>
<ol>
<li>Click <strong>Home</strong>, then <strong>Arrange</strong>, then <strong>Selection Pane</strong>.</li>
<li>The list shows objects from <strong>bottom to top</strong>. The screen reader reads from the <strong>bottom of the list</strong> up to the top.</li>
<li>Drag items so the order matches how the slide should be read: title first, then content.</li>
</ol>
<h3>Unique slide titles</h3>
<p>Every slide should have a title, and the titles should be different, so that people using the keyboard or a screen reader can move around. Use layouts with a title placeholder. If you want a slide with no visible title, you can still add one and hide it by moving it off the slide.</p>
<h3>Colour, contrast and font</h3>
<ul>
<li>Do not use colour alone to give meaning. For example, in a chart, label the lines directly or use patterns as well as colours.</li>
<li>Use strong contrast.</li>
<li>Use a sans serif font at a large size.</li>
<li>Write link text that makes sense, such as <em>Read the report</em>, not <em>click here</em>.</li>
</ul>
<h3>Tables, video and audio</h3>
<ul>
<li>Use simple tables with a header row (tick <strong>Header Row</strong> in Table Design). Avoid merged and split cells.</li>
<li>Add <strong>captions</strong> or a transcript for videos so deaf people can follow.</li>
<li>Describe key visuals aloud when you present.</li>
</ul>
<h3>Accessibility Checker</h3>
<p>Click <strong>Review</strong>, then <strong>Check Accessibility</strong>. A pane lists <strong>Errors</strong>, <strong>Warnings</strong> and <strong>Tips</strong>, for example missing alt text. Click an item and follow the advice to fix it. Run the checker before every important presentation.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open a presentation with pictures.</li>
<li>Add alt text to each picture, or mark decoration as decorative.</li>
<li>Open the Selection Pane and check the reading order of one slide.</li>
<li>Run Review, then Check Accessibility and fix at least two issues.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Add alt text to meaningful pictures, charts and shapes.</li>
<li>Check the reading order in the Selection Pane.</li>
<li>Give every slide a unique title and keep strong contrast.</li>
<li>Run the Accessibility Checker before presenting or sharing.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is a good title for a slide that shows sales rose after phone ordering started?
- Sales
* Sales doubled after we started taking orders by phone
- Slide 4
- Information
= A title that states the message tells the audience what to notice.

Q: What does "visual hierarchy" mean?
- The order of the slides in the file
* Important items look more important through size, colour and position
- Using many animations
- The number of pictures
= Hierarchy guides the eye to what matters first.

Q: Which colour combination is the safest for readability?
- Light grey text on white
- Yellow text on white
* Dark text on a light background
- Red text on blue
= High contrast is easiest to read, especially from a projector.

Q: You want to place a title over a busy photograph. What is a good solution?
- Use a thin decorative font
- Make the text tiny
* Add a semi-transparent rectangle behind the text
- Leave the text as it is
= A transparent shape gives contrast without hiding the picture.

Q: Which of these is clutter that you should consider removing? (choose all that apply)
* Decorative clip art that adds no meaning
* Heavy borders around every item
- The chart that supports your main point
* Five different fonts
= Remove items that do not help the message.

Q: When you make a column chart, what should the vertical axis normally start at?
* Zero
- Any number that makes the differences look big
- A negative number
- A random value
= Starting at zero keeps the comparison honest.

Q: What is alt text?
- A secret password
* A short written description of a picture for screen readers
- A kind of animation
- A speaker note
= Alt text lets people who cannot see the picture understand it.

Q: Where do you check the order in which a screen reader reads the objects on a slide?
- In the Slide Sorter
* In the Selection Pane
- In the Notes pane
- In the Slide Show
= The Selection Pane lists objects; the screen reader reads from the bottom of the list up.
QUIZ,
        ],
        [
            'title'   => 'Module 4: Motion and media',
            'summary' => 'Use transitions, animation, audio, video, screen recording, links and sections with good judgement.',
            'lessons' => [
                [
                    'title'   => 'Transitions',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>You will learn what a transition is, how to add it, and how to choose one that helps rather than distracts. A transition is the effect you see when one slide changes into the next. Used lightly, it makes a presentation feel smooth. Used too much, it makes the audience dizzy.</p>
<h3>Adding a transition</h3>
<ol>
<li>Select a slide in the thumbnail pane (or several, holding Ctrl).</li>
<li>Click the <strong>Transitions</strong> tab.</li>
<li>Click an effect in the gallery, such as <strong>Fade</strong>, <strong>Push</strong> or <strong>Wipe</strong>. You see a preview.</li>
<li>Click <strong>Effect Options</strong> to choose a direction.</li>
<li>Click <strong>Apply To All</strong> to use the same effect on every slide.</li>
</ol>
<p>Slides with a transition have a small star beside them in the thumbnail pane.</p>
<h3>Timing options</h3>
<table>
<thead><tr><th>Setting</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>Sound</td><td>A sound played at the change. Better left as <strong>No Sound</strong>.</td></tr>
<tr><td>Duration</td><td>How long the transition takes. 0.5 to 1 second is comfortable.</td></tr>
<tr><td>On Mouse Click</td><td>The slide changes when you click or press a key. This is the default and usually best for a live talk.</td></tr>
<tr><td>After</td><td>The slide advances automatically after a set time. Useful for a looping display at an event or a kiosk.</td></tr>
</tbody>
</table>
<h3>Choosing transitions wisely</h3>
<ul>
<li>Prefer <strong>Fade</strong> or <strong>Push</strong>. They are calm and professional.</li>
<li>Use the <strong>same</strong> transition on all slides, so the presentation feels consistent.</li>
<li>Use a different one only for a reason, for example a bigger effect before a new section.</li>
<li>Avoid dramatic effects like Vortex, Cube or Ripple in serious talks. They draw attention to the effect instead of your message.</li>
<li>Avoid sound effects. They are distracting and some people find them annoying.</li>
</ul>
<h3>Morph</h3>
<p>In Microsoft 365 and newer versions, <strong>Morph</strong> smoothly moves and resizes matching objects between two slides. To use it, duplicate a slide (right-click, Duplicate Slide), move or resize objects on the second slide, and apply Morph on the Transitions tab. It is a subtle way to zoom into a detail. Check that your version includes it, because older versions do not have it.</p>
<h3>Removing a transition</h3>
<p>Select the slide and click <strong>None</strong> at the start of the transitions gallery.</p>
<h3>Common mistakes</h3>
<ul>
<li>Using a different transition for every slide.</li>
<li>Setting slow durations that waste time.</li>
<li>Using an automatic advance and forgetting that the speaker cannot pause.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Open a presentation with at least five slides.</li>
<li>Apply the Fade transition to the first slide and click Apply To All.</li>
<li>Set the duration to 0.7 seconds.</li>
<li>Play the show from the start (F5) and watch how it feels.</li>
<li>Try changing one transition to Push, and decide which looks better.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Transitions are on the Transitions tab and control how one slide changes to the next.</li>
<li>Use simple effects, short durations and the same effect throughout.</li>
<li>Avoid sound effects and showy transitions.</li>
<li>Morph can move objects smoothly between slides in newer versions.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Animations and the Animation Pane',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>In this lesson you will animate objects on a slide, so that text and pictures can appear in steps. Used carefully, animation lets you reveal points one at a time, so the audience listens to you and does not read ahead. Used badly, it looks childish and wastes time.</p>
<h3>Types of animation</h3>
<table>
<thead><tr><th>Type</th><th>Colour in the gallery</th><th>What it does</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Entrance</td><td>Green</td><td>An object appears</td><td>A bullet fades in</td></tr>
<tr><td>Emphasis</td><td>Yellow</td><td>An object already on the slide draws attention</td><td>A number pulses</td></tr>
<tr><td>Exit</td><td>Red</td><td>An object leaves the slide</td><td>A picture fades away</td></tr>
<tr><td>Motion Paths</td><td>Lines</td><td>An object moves along a path</td><td>An arrow moves along a route</td></tr>
</tbody>
</table>
<h3>Adding an animation</h3>
<ol>
<li>Click the object (text box, picture, shape, chart).</li>
<li>Click the <strong>Animations</strong> tab.</li>
<li>Choose an effect from the gallery, for example <strong>Fade</strong> or <strong>Appear</strong>.</li>
<li>Click <strong>Effect Options</strong> to choose how: for text, you can show <strong>By Paragraph</strong> so each bullet appears in turn.</li>
<li>Click <strong>Preview</strong> to see it.</li>
</ol>
<p>Small numbers appear beside the animated object, showing the order.</p>
<h3>Start, duration and delay</h3>
<ul>
<li><strong>Start: On Click</strong> waits for your click. Good when you want to control the pace.</li>
<li><strong>With Previous</strong> starts at the same time as the previous effect.</li>
<li><strong>After Previous</strong> starts right after the earlier effect ends, without a click.</li>
<li><strong>Duration</strong> sets the speed. Use 0.5 seconds for most effects.</li>
<li><strong>Delay</strong> waits a set time before starting.</li>
</ul>
<h3>The Animation Pane</h3>
<p>Click <strong>Animation Pane</strong> on the Animations tab. A list of all effects on the slide opens on the right.</p>
<ol>
<li>Click an item to select it. Click the arrow beside it for <strong>Timing</strong> or <strong>Effect Options</strong>.</li>
<li>Drag items up or down, or use the <strong>Reorder</strong> arrows, to change the order.</li>
<li>Click <strong>Play From</strong> to test.</li>
<li>Select an item and press Delete to remove its animation.</li>
</ol>
<p>Use <strong>Add Animation</strong> to give one object more than one effect, for example an entrance and later an exit. Use <strong>Animation Painter</strong> to copy an animation from one object to another, like Format Painter.</p>
<h3>Rules of thumb</h3>
<ul>
<li>Animate only to explain: reveal steps, build a diagram, or point to a detail.</li>
<li>Use subtle effects: Fade, Appear, Wipe.</li>
<li>Do not animate every object on every slide.</li>
<li>Avoid spinning, bouncing and sounds.</li>
<li>Rehearse. Animations change how you speak, because you must click at the right moment.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Create a slide with the title <code>Three Steps to Start a Savings Group</code> and three bullets.</li>
<li>Select the bullets and apply the Fade animation by paragraph.</li>
<li>Open the Animation Pane and check the order.</li>
<li>Add a picture and give it an Entrance effect set to After Previous.</li>
<li>Run the slide show with F5 and click through it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Entrance, Emphasis, Exit and Motion Paths are the four animation types.</li>
<li>Start On Click gives you control of the pace.</li>
<li>The Animation Pane shows and reorders all effects on a slide.</li>
<li>Animate to explain, not to decorate.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Audio, video and screen recording',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>You will learn to add sound and video to slides, trim them, and record your screen. A short video clip can bring a story to life, and a screen recording is a great way to teach software. Media also brings risks, so we also look at how to avoid problems on the day.</p>
<h3>Adding video</h3>
<ol>
<li>Click the slide where the video should appear.</li>
<li>Click <strong>Insert</strong>, then <strong>Video</strong>, then <strong>This Device</strong>.</li>
<li>Select the video file (for example an .mp4 from your phone) and click <strong>Insert</strong>.</li>
<li>Resize it by dragging a corner. Click <strong>Play</strong> beneath it to test.</li>
</ol>
<p>The <strong>Playback</strong> tab appears. There you can <strong>Trim Video</strong> (cut the beginning or the end), set <strong>Start: Automatically</strong> or <strong>On Click</strong>, tick <strong>Full Screen</strong> or <strong>Loop until Stopped</strong>, and adjust <strong>Volume</strong>. Use <strong>Video Format</strong> to add a border, set a poster frame (the picture shown before it plays) and change the shape.</p>
<h3>Adding audio</h3>
<ol>
<li>Click <strong>Insert</strong>, then <strong>Audio</strong>.</li>
<li>Choose <strong>Audio on My PC</strong> to use a file, or <strong>Record Audio</strong> to record your voice.</li>
<li>A speaker icon appears on the slide. On the <strong>Playback</strong> tab, you can trim it, make it fade, or choose <strong>Play in Background</strong> so it plays across slides.</li>
</ol>
<p>Use background music rarely, and quietly. Never let it drown out your voice. Use only music that you have permission to use.</p>
<h3>Screen recording</h3>
<p>This records what happens on your computer screen, with optional sound, and places it on the slide.</p>
<ol>
<li>Open the slide.</li>
<li>Click <strong>Insert</strong>, then <strong>Screen Recording</strong> (on the right).</li>
<li>Click <strong>Select Area</strong> and drag a box around the part of the screen to record.</li>
<li>Click <strong>Record</strong> (the shortcut is Windows key + Shift + R) and do the steps while you explain.</li>
<li>Press <strong>Windows key + Shift + Q</strong> to stop.</li>
<li>The recording appears on the slide. Trim it on the Playback tab.</li>
</ol>
<p>It is excellent for lessons such as how to open an email account. Close personal windows and notifications first, so you do not record private information.</p>
<h3>Avoiding problems</h3>
<ul>
<li>Large videos make files big and slow. Compress them: <strong>File</strong>, <strong>Info</strong>, <strong>Compress Media</strong>.</li>
<li>If a video is linked and not embedded, it can disappear when you copy the presentation. Keep the video in the same folder as the presentation, or insert it again on the final computer.</li>
<li>Test the sound on the room's speakers before the audience arrives.</li>
<li>Keep clips short: 30 to 90 seconds. Add captions or a transcript for accessibility.</li>
<li>Always have a plan B if the video fails: a still picture and your explanation.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Record a 15-second clip on your phone and copy it to your computer.</li>
<li>Insert it on a slide, trim the first two seconds, and set it to start On Click.</li>
<li>Insert Audio, then Record Audio, and record a short sentence.</li>
<li>Use Screen Recording to record yourself opening the Start menu.</li>
<li>Check the file size in File, Info.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Insert, Video and Insert, Audio add media; the Playback tab trims and controls it.</li>
<li>Screen Recording is useful for teaching software.</li>
<li>Keep clips short, tested and compressed, and respect copyright.</li>
<li>Always prepare a plan B in case media fails.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Hyperlinks, action buttons and sections',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>In this lesson you will make slides interactive with links and buttons, and organise a long presentation into sections. Links let you jump to a website or another slide, and sections keep large files tidy and easy to rearrange.</p>
<h3>Hyperlinks</h3>
<ol>
<li>Select text, a shape or a picture.</li>
<li>Click <strong>Insert</strong>, then <strong>Link</strong> (shortcut Ctrl+K; Mac: Cmd+K).</li>
<li>Choose where to link:
<ul>
<li><strong>Existing File or Web Page</strong>: type or paste a web address, or pick a file.</li>
<li><strong>Place in This Document</strong>: pick another slide.</li>
<li><strong>E-mail Address</strong>: opens an email message.</li>
</ul>
</li>
<li>Click <strong>OK</strong>.</li>
</ol>
<p>Links only work in <strong>Slide Show</strong> view (press F5). Use clear link text such as <em>Read the full report</em>. To remove a link, right-click it and choose <strong>Remove Link</strong>. Check that you have internet access before relying on a web link. A QR code on a slide is another way to share a link, but make it large enough to scan.</p>
<h3>Action buttons</h3>
<p>An <strong>action</strong> makes an object do something when clicked, for example go to the next slide.</p>
<ol>
<li>Click <strong>Insert</strong>, then <strong>Shapes</strong>. At the bottom of the list are <strong>Action Buttons</strong> (arrows, a home symbol, and others).</li>
<li>Drag on the slide to draw one.</li>
<li>A dialog box opens. Choose <strong>Hyperlink to</strong> and pick a slide, such as <strong>First Slide</strong> or <strong>Next Slide</strong>.</li>
<li>Click <strong>OK</strong>.</li>
</ol>
<p>You can also select any shape or picture, click <strong>Insert</strong>, then <strong>Action</strong>, and set the same thing. This is how to build a simple menu: a slide with several buttons, each jumping to a topic, plus a <em>Back to menu</em> button on each topic slide. It is useful for a training session where the audience chooses what to see.</p>
<h3>Zoom for PowerPoint</h3>
<p>In Microsoft 365, <strong>Insert</strong>, then <strong>Zoom</strong> can create clickable thumbnails (Summary Zoom, Section Zoom, Slide Zoom) that jump to parts of the presentation and return. It is a modern alternative to action buttons.</p>
<h3>Sections</h3>
<p>Sections divide a long presentation into named groups of slides, like chapters.</p>
<ol>
<li>Right-click between two slides in the thumbnail pane and choose <strong>Add Section</strong> (or Home, then Section, then Add Section).</li>
<li>Type a name, such as <code>Introduction</code> or <code>Results</code>, and press Enter.</li>
<li>Click the arrow beside a section name to collapse or expand it.</li>
<li>Drag a section to move all its slides together.</li>
</ol>
<p>Sections also help when several people prepare different parts of a talk and then combine them.</p>
<h3>Common mistakes</h3>
<ul>
<li>Testing links in Normal view, where they do not work.</li>
<li>Using many links so that you lose your place and your audience's attention.</li>
<li>Linking to a file on your computer that is not on the presenting computer.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Make a slide called <code>Menu</code> with three shapes labelled <code>Plan</code>, <code>Do</code> and <code>Review</code>.</li>
<li>Create three other slides, one for each, and link each shape to its slide.</li>
<li>On each topic slide, add a Home action button that goes back to the Menu.</li>
<li>Divide the file into two sections and name them.</li>
<li>Run the show with F5 and test every link.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Ctrl+K inserts a hyperlink to a web page, file, slide or email address.</li>
<li>Action buttons and links work only in Slide Show view.</li>
<li>Sections group slides into chapters.</li>
<li>Always test links on the computer you will present on.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which transition style is the best choice for most professional presentations?
- Cube with a sound
* Fade or Push, used consistently
- A different dramatic effect on each slide
- Vortex
= Calm, consistent transitions keep attention on your message.

Q: What kind of animation makes an object appear?
* Entrance
- Emphasis
- Exit
- Motion path
= Entrance effects (green) make objects appear.

Q: You want each bullet to appear when you click. What do you choose?
- Apply the animation to the slide background
* Apply an entrance animation to the text and use the By Paragraph option
- Use Morph
- Use a loop
= By Paragraph reveals each bullet in turn.

Q: Where can you see and reorder all animations on one slide?
- In the Slide Sorter
* In the Animation Pane
- In the Notes pane
- In the Selection Pane only
= The Animation Pane lists effects in order and lets you drag them.

Q: How do you remove the first few seconds of a video you inserted?
- Delete the slide
* Use Trim Video on the Playback tab
- Use Format Painter
- Change the theme
= Trim Video lets you cut the start and end.

Q: What is an important precaution before presenting a slide with a video?
- Turn off the speakers
* Test it on the actual computer and have a plan B
- Delete the file
- Save the slide as a PDF
= Media can fail, so test it and have a backup plan.

Q: Why might a hyperlink not work when you click it in Normal view?
- It is broken forever
* Links work in Slide Show view
- You need to restart the computer
- The theme is wrong
= Press F5 to run the show before testing links.

Q: What do sections help you to do?
- Add sound to slides
* Group slides into named chapters and move them together
- Make the slides larger
- Remove animations
= Sections organise long presentations.
QUIZ,
        ],
        [
            'title'   => 'Module 5: Delivering your presentation',
            'summary' => 'Prepare notes, rehearse, present with Presenter View, export and share, and handle nerves and questions.',
            'lessons' => [
                [
                    'title'   => 'Speaker notes, Presenter View and rehearsing',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write useful speaker notes, use Presenter View to see them while the audience sees only the slides, and rehearse with timings. Preparation is the biggest factor in a confident talk, and these tools help you to prepare properly.</p>
<h3>Speaker notes</h3>
<p>Speaker notes are the text you put in the <strong>Notes pane</strong> under each slide. The audience does not see them. If the pane is hidden, click <strong>Notes</strong> in the status bar.</p>
<ul>
<li>Write key words or short sentences, not a full script. A full script makes you read and stop looking at people.</li>
<li>Note the story, the numbers, a quote, or the name of someone to thank.</li>
<li>Add reminders such as <em>pause here</em> or <em>ask the audience a question</em>.</li>
<li>Use a large enough font so you can read it quickly.</li>
</ul>
<h3>Presenter View</h3>
<p>Presenter View needs two screens: the audience sees the slides on the projector, and you see extra tools on your laptop screen.</p>
<table>
<thead><tr><th>What you see</th><th>Why it helps</th></tr></thead>
<tbody>
<tr><td>Current slide and the next slide</td><td>You know what comes next</td></tr>
<tr><td>Your speaker notes</td><td>You can read reminders</td></tr>
<tr><td>A timer</td><td>You keep to time</td></tr>
<tr><td>Slide thumbnails</td><td>You can jump to a slide</td></tr>
<tr><td>Pen, laser pointer and zoom tools</td><td>You can point at and enlarge details</td></tr>
</tbody>
</table>
<ol>
<li>Connect to the projector (press the Windows key + P and choose <strong>Extend</strong>).</li>
<li>Click the <strong>Slide Show</strong> tab and tick <strong>Use Presenter View</strong>.</li>
<li>Choose which screen shows what under <strong>Monitor</strong>, if needed.</li>
<li>Click <strong>From Beginning</strong> or press F5.</li>
</ol>
<p>If it shows on the wrong screens, click <strong>Display Settings</strong> and choose <strong>Swap Presenter View and Slide Show</strong>.</p>
<h3>Rehearse timings</h3>
<ol>
<li>Click <strong>Slide Show</strong>, then <strong>Rehearse Timings</strong>.</li>
<li>Talk through the presentation as if you were in front of the audience, clicking when you move on.</li>
<li>The recording box shows the time spent on each slide. At the end, you see the total time. Choose whether to keep the timings.</li>
</ol>
<p>If you do not want slides to advance on their own, switch off <strong>Use Timings</strong> on the Slide Show tab. The main reason to rehearse is to learn the real duration of your talk and to find slides to cut.</p>
<h3>Rehearsal habits</h3>
<ul>
<li>Practise aloud at least three times, standing up, with a friend or family member as audience.</li>
<li>Learn the opening and the closing really well.</li>
<li>Time yourself and aim to finish a little early.</li>
<li>Check the room, the screen, the clicker or the cables beforehand.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Open a five-slide presentation and add a short note to each slide.</li>
<li>If you have only one screen, still tick Use Presenter View. You can start from the Slide Show tab and look at the tools, or use Alt+F5 on Windows to start Presenter View on one screen.</li>
<li>Use Rehearse Timings and talk aloud for each slide.</li>
<li>Write down the total time. Is it close to what you were given?</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Speaker notes hold key words, not a full script.</li>
<li>Presenter View shows the next slide, your notes and a timer to you only.</li>
<li>Rehearse aloud and time yourself.</li>
<li>Check equipment and connections before the audience arrives.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Running a slide show, handouts and exporting',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>You will learn the controls used while presenting, how to make handouts for your audience, and how to export a presentation as a PDF or a video. These steps make you look in control and help the audience take something away.</p>
<h3>Starting the show</h3>
<ul>
<li><strong>F5</strong>: start from the first slide (Mac: Cmd+Return).</li>
<li><strong>Shift+F5</strong>: start from the current slide (Mac: Cmd+Shift+Return).</li>
<li><strong>Esc</strong>: stop the show.</li>
</ul>
<h3>Controls during the show</h3>
<table>
<thead><tr><th>Do this</th><th>Result</th></tr></thead>
<tbody>
<tr><td>Click, Space, Enter, Right arrow or Page Down</td><td>Next slide or next animation</td></tr>
<tr><td>Backspace, Left arrow or Page Up</td><td>Previous slide</td></tr>
<tr><td>Type a number, then Enter</td><td>Jump to that slide</td></tr>
<tr><td>B (or full stop)</td><td>Black screen: use it when you want attention on you</td></tr>
<tr><td>W (or comma)</td><td>White screen</td></tr>
<tr><td>Ctrl+P</td><td>Pen (Mac: Cmd+P). Press Esc to return to the arrow.</td></tr>
<tr><td>Ctrl+L</td><td>Laser pointer</td></tr>
<tr><td>Right-click or the toolbar</td><td>See all slides, change pointer options</td></tr>
</tbody>
</table>
<p>Black screen is a useful trick. When you talk about something not on the slide, or answer a long question, press <strong>B</strong>. The audience looks at you. Press B again to bring the slide back.</p>
<h3>Handouts</h3>
<p>A handout is a paper or PDF copy that people take home, usually with several slides per page.</p>
<ol>
<li>Click <strong>File</strong>, then <strong>Print</strong>.</li>
<li>Under <strong>Settings</strong> change <strong>Full Page Slides</strong> to <strong>Handouts</strong> and choose how many slides per page, for example 3 (with lines for notes) or 6.</li>
<li>Choose <strong>Grayscale</strong> or <strong>Black and White</strong> to save ink.</li>
<li>Click <strong>Print</strong>.</li>
</ol>
<p>Choose <strong>Notes Pages</strong> to print each slide with your notes, for yourself. Do not give out notes pages if they contain private comments.</p>
<h3>Exporting</h3>
<ol>
<li>Click <strong>File</strong>, then <strong>Export</strong>.</li>
<li>Choose <strong>Create PDF/XPS Document</strong> for a PDF, or <strong>Create a Video</strong> for an .mp4.</li>
<li>For video, pick the quality (a lower quality makes a smaller file) and whether to use recorded timings and narration.</li>
<li>Click <strong>Create</strong>, name the file and save.</li>
</ol>
<p>You can also use <strong>File</strong>, <strong>Save As</strong> and choose PDF or MP4 as the type. In the PDF options you can choose to publish only the slides, handouts, notes, or outline. A video is great for sharing a presentation on WhatsApp or YouTube, but the file may be large for limited data, so choose a lower quality.</p>
<h3>Package for another computer</h3>
<p>If you present on a different computer, save fonts with the file (File, Options, Save, <strong>Embed fonts in the file</strong>), keep videos in the same folder, and take a PDF copy and a USB flash drive as a back-up.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open your presentation and start it from the beginning with F5.</li>
<li>Practise: next, back, jump to slide 3 by typing 3 and Enter, black screen with B.</li>
<li>Print to PDF as a handout with three slides per page.</li>
<li>Export the presentation as a PDF and as a video at the lowest quality, and compare the file sizes.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>F5 starts the show; Esc ends it; B blanks the screen.</li>
<li>Handouts let the audience keep the key points.</li>
<li>Export to PDF or video from File, then Export.</li>
<li>Always bring a back-up copy in PDF.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Sharing, co-authoring and presenting online',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn to share a presentation, build one together with other people, and deliver it online using a meeting program. Teamwork and online meetings are part of study and work today, so these skills are valuable.</p>
<h3>Sharing a presentation</h3>
<p>If your file is saved in OneDrive:</p>
<ol>
<li>Click <strong>Share</strong> at the top right.</li>
<li>Enter the email addresses of the people.</li>
<li>Choose <strong>Can edit</strong> or <strong>Can view</strong>.</li>
<li>Click <strong>Send</strong> or <strong>Copy link</strong>.</li>
</ol>
<p>Share with the right level. For an audience who only needs to read, send a PDF or a view-only link. Never share a link for editing with anyone who has it.</p>
<h3>Co-authoring</h3>
<p>Several people can edit the same presentation at the same time when it is stored online. You see coloured markers where others work. Good teamwork rules:</p>
<ul>
<li>Agree who does which slides before you start, for example by sections.</li>
<li>Agree on the theme, fonts and master first, so slides match.</li>
<li>Use <strong>Review</strong>, then <strong>New Comment</strong> to give feedback instead of changing others' slides.</li>
<li>Keep a copy of the first version. In OneDrive, <strong>Version History</strong> (File, Info) lets you go back.</li>
<li>Combine the slides, then one person tidies the whole file for consistency.</li>
</ul>
<h3>Presenting online</h3>
<p>Teams, Zoom and Google Meet all allow screen sharing. In general:</p>
<ol>
<li>Join the meeting early and test your microphone and camera.</li>
<li>Close other programs and messages that could appear on screen.</li>
<li>Choose <strong>Share</strong>, then pick the PowerPoint window (or the slide show).</li>
<li>Start the slide show and present.</li>
<li>Stop sharing when finished.</li>
</ol>
<p>In PowerPoint itself, <strong>Slide Show</strong>, then <strong>Present Online</strong> (in some versions) or <strong>Share</strong> can start a session. If you share a whole screen, everyone sees everything, including notifications. Share only the window you need.</p>
<h3>Tips for online delivery</h3>
<ul>
<li>Internet can be unreliable. Download your presentation to the computer so it does not need a connection. Keep slides light, with few videos.</li>
<li>Speak a little slower and clearly. Look at the camera when you are making a point.</li>
<li>Sit in a bright place with the light in front of you, not behind.</li>
<li>Ask short questions or use the chat to keep people engaged.</li>
<li>Send the PDF to participants afterward, for those who joined on low data or missed part.</li>
<li>Record the session only with the permission of those attending.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Save a presentation to OneDrive, if you have an account.</li>
<li>Share it with a friend as view-only and ask them to open it.</li>
<li>Add a comment to one slide and reply to a comment from your friend.</li>
<li>Join a practice video call, share the PowerPoint window, and present for one minute.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Use Share with the right permissions: Can view or Can edit.</li>
<li>Co-authoring works best with a shared theme and clear division of slides.</li>
<li>Share only the PowerPoint window when presenting online.</li>
<li>Prepare for weak internet with a local copy and light files.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Nerves, questions and the final presentation checklist',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>In this last lesson you will learn how to handle nerves, answer questions well, and check your presentation before you deliver it. Nearly everyone feels nervous before speaking. The goal is not to remove the nerves, but to use them as energy and be ready.</p>
<h3>Managing nerves</h3>
<ul>
<li><strong>Prepare:</strong> the best cure for nerves is practice. Rehearse aloud several times.</li>
<li><strong>Breathe:</strong> before you start, breathe in slowly for four counts and out for six. Do this three times.</li>
<li><strong>Arrive early:</strong> check the room, the equipment and the first slide.</li>
<li><strong>Know your opening:</strong> memorise the first two sentences. Once you begin, you relax.</li>
<li><strong>Think of the audience:</strong> they want you to do well. They came to learn something.</li>
<li><strong>Speak slowly:</strong> nervous people speak fast. Pause after each key point.</li>
<li><strong>Use your hands naturally,</strong> stand with your feet apart, and avoid hiding behind the laptop.</li>
<li><strong>If you lose your place:</strong> stop, drink water, look at your notes, and carry on. No one notices a short pause as much as you do.</li>
</ul>
<h3>Connecting with the audience</h3>
<ol>
<li>Look at people, not at the screen. Do not read the slides aloud with your back to the audience.</li>
<li>Start with a story, a question or a surprising fact.</li>
<li>Use simple words and examples they know.</li>
<li>Pause after important points.</li>
<li>End with a clear message or a request: what should they remember or do?</li>
</ol>
<h3>Handling questions</h3>
<ol>
<li>Listen to the whole question without interrupting.</li>
<li>Repeat or rephrase it so everyone hears and you check you understood.</li>
<li>Answer briefly and honestly.</li>
<li>If you do not know, say so: <em>I do not know that yet, but I will find out and tell you.</em> Then do it.</li>
<li>Stay polite with difficult questions. Thank the person for asking.</li>
<li>Keep a back-up slide at the end for expected questions, such as the budget.</li>
</ol>
<h3>Final project presentation checklist</h3>
<table>
<thead><tr><th>Area</th><th>Check</th></tr></thead>
<tbody>
<tr><td>Message</td><td>One clear main message; each slide has one point and a message title</td></tr>
<tr><td>Design</td><td>Consistent theme, 24+ point text, strong contrast, no clutter</td></tr>
<tr><td>Content</td><td>Facts, names, dates and figures checked; spelling checked (F7)</td></tr>
<tr><td>Images</td><td>Sharp, relevant, with permission; alt text added</td></tr>
<tr><td>Motion</td><td>Simple transitions and animations only where they help</td></tr>
<tr><td>Accessibility</td><td>Accessibility Checker run; reading order checked</td></tr>
<tr><td>Notes and timing</td><td>Speaker notes written; rehearsed aloud; the time is right</td></tr>
<tr><td>Technology</td><td>Saved copy, PDF back-up, media tested, links tested, charger and cables ready</td></tr>
<tr><td>Delivery</td><td>Opening and closing learned; water ready; arrived early</td></tr>
</tbody>
</table>
<h3>Try it yourself</h3>
<ol>
<li>Choose a five-minute topic and finish your final project presentation of 6 to 8 slides.</li>
<li>Go through the checklist above and fix every item you find.</li>
<li>Rehearse aloud twice, then present it to a friend or family member.</li>
<li>Ask them to write down one thing that worked and one thing to improve, and write answers to two questions they ask.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Preparation, breathing and a strong opening reduce nerves.</li>
<li>Talk to the audience, not to the screen.</li>
<li>Listen, rephrase and answer honestly. It is fine to say you do not know.</li>
<li>Use the checklist before every important presentation.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What should speaker notes contain?
- A full script to read word for word
* Key words and reminders that help you speak naturally
- Nothing; they are not useful
- The same text as the slide
= Notes should prompt you, not replace talking to the audience.

Q: What does Presenter View show you but not the audience?
- Only the current slide
* The next slide, your notes and a timer
- A different presentation
- The ribbon
= Presenter View is your private control screen.

Q: What is the purpose of Rehearse Timings?
- To add sound to slides
* To learn how long your talk takes and which slides to adjust
- To delete slides
- To change the theme
= Rehearsing shows the real length of your talk.

Q: Which key makes the screen go black during a show so that attention returns to you?
- A
* B
- Q
- Z
= Press B for black, W for white. Press again to return.

Q: Which is the quickest way to start the show from the current slide?
- F5
* Shift+F5
- Ctrl+S
- Alt+F4
= Shift+F5 starts from the current slide; F5 from the beginning.

Q: You must give people a copy to take home that uses little paper. What should you print?
- Full page slides, one per page
* Handouts with several slides per page
- Only the notes
- The theme file
= Handouts with 3 or 6 slides per page save paper.

Q: Which is a good way to answer a question you cannot answer?
- Invent an answer
* Say you do not know, and promise to find out and follow up
- Ignore the person
- End the talk
= Honesty builds trust.

Q: When presenting online, how can you avoid showing private notifications to everyone?
- Share your entire desktop
* Share only the PowerPoint window and close other programs
- Turn off your microphone
- Use a larger font
= Sharing one window and closing other programs keeps private things private.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What file extension does a normal PowerPoint presentation have?
- .docx
- .xlsx
* .pptx
- .txt
= .pptx is the PowerPoint presentation format.

Q: Which pane shows small pictures of all slides on the left?
* Thumbnail pane
- Notes pane
- Status bar
- Ribbon
= The thumbnail pane lists the slides; drag to reorder.

Q: What is a layout?
- A sound effect
* The arrangement of placeholders on a slide
- A saved PDF
- A printer
= Layouts such as Title and Content set where things go.

Q: What does a theme do?
- Plays music
* Sets colours, fonts and background for all slides
- Prints handouts
- Counts words
= Themes give the presentation a consistent design.

Q: Which view lets you drag slides to change their order across the whole file?
- Notes Page
* Slide Sorter
- Reading View
- Slide Show
= Slide Sorter shows many slides at once.

Q: You want the same logo on every slide. What is the most efficient way?
- Paste it on each slide
* Put it on the Slide Master
- Make it very large
- Use a transition
= Anything on the master appears on the slides that use it.

Q: What is the recommended minimum font size for text your audience must read?
- 10 points
* 18 points
- 8 points
- 4 points
= Never go below about 18 points; 24 or more for body text is better.

Q: How should a slide title be written?
- As a single vague word
* As a clear message in a short sentence
- In capital letters only
- Left empty
= A message title tells people what to take from the slide.

Q: Which is the best way to compare five amounts?
* A column or bar chart
- A paragraph of text
- A pie chart with five similar slices
- A 3D animation
= Columns make comparison easy.

Q: Why should a column chart normally start at zero?
- It uses less ink
* Otherwise differences can look bigger than they are
- PowerPoint requires it
- It makes the file smaller
= A truncated axis can mislead the audience.

Q: Which of these helps readability? (choose all that apply)
* High contrast between text and background
* Large, simple fonts
- Text over a busy photograph
* Short lines with space around them
= Contrast, size and space make slides readable.

Q: What is the purpose of visual hierarchy?
- To use more colours
* To guide the eye to the most important things first
- To make slides longer
- To reduce the file size
= Size, colour and position decide what people look at first.

Q: Which is good practice for pictures?
- Stretch them to fill the space
* Use sharp, relevant images that you have permission to use
- Use as many as possible
- Copy any picture from the internet
= Quality, relevance and permission matter.

Q: What should you do to make pictures accessible?
- Make them smaller
* Add alt text, or mark decorative ones as decorative
- Delete the title
- Use bright colours
= Alt text lets screen readers describe the picture.

Q: Where do you check the reading order for a screen reader?
- Slide Master
* Selection Pane
- Transitions tab
- Print settings
= The Selection Pane lists objects in order.

Q: Which tool finds missing alt text and other accessibility problems?
- Spelling check
* Check Accessibility on the Review tab
- Slide Sorter
- Rehearse Timings
= The Accessibility Checker reports errors and warnings.

Q: What is a transition?
- An effect on an object inside a slide
* The effect when one slide changes to the next
- A kind of chart
- A type of font
= Animations affect objects; transitions affect slide changes.

Q: Which advice about transitions is best?
- Use a different dramatic one on every slide
* Use one simple transition style throughout
- Add a loud sound to each
- Make them last 10 seconds
= Consistency and subtlety keep attention on the message.

Q: Which kind of animation is used to make an object appear?
* Entrance
- Exit
- Emphasis
- Layout
= Entrance effects bring objects onto the slide.

Q: What does the "By Paragraph" option do for bulleted text?
- Reads the text aloud
* Shows each bullet separately in turn
- Deletes the bullets
- Prints the bullets
= By Paragraph reveals the bullets one at a time.

Q: How do you cut the first seconds off an inserted video?
- Use Crop
* Use Trim Video on the Playback tab
- Delete the slide
- Change the transition
= Trim Video shortens the clip.

Q: Which of these are good practices with media? (choose all that apply)
* Keep video clips short
* Test them on the computer you will present on
- Use copyrighted music without permission
* Have a plan B if the video fails
= Respect copyright and prepare for failure.

Q: What do action buttons and hyperlinks need in order to work?
- Normal view
* Slide Show view
- A printer
- Internet only
= Links and action buttons are active in Slide Show view.

Q: What do sections do in PowerPoint?
- Make text bold
* Group slides into named parts that you can collapse and move
- Add transitions
- Export a video
= Sections organise long files.

Q: What should speaker notes be?
- A word-for-word script
* Key words and reminders
- Blank
- A copy of the slide
= Notes should help, not tempt you to read.

Q: What does the black screen key (B) do during a show?
- Ends the show
* Blanks the screen so attention is on you
- Saves the file
- Plays a video
= Press B to blank and again to return.

Q: You are presenting online and want to avoid showing private notifications. What should you do?
- Share the whole desktop
* Share only the PowerPoint window and close other programs
- Share nothing
- Turn the sound off
= Limit what is shared.

Q: You are nervous before a talk. Which is the most helpful action?
- Skip the rehearsal
* Rehearse aloud, breathe slowly and know your opening
- Speak as fast as possible
- Read the slides word for word
= Preparation and calm breathing reduce nerves.

Q: Someone asks a question you cannot answer. What is the best reply?
- Make up an answer
* Say you do not know, and promise to find out and reply
- Say it is a silly question
- Change the topic
= Honesty and follow-up build trust.
QUIZ,
];
