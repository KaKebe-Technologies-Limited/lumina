<?php
return [
    'slug'        => 'ms-word',
    'title'       => 'Microsoft Word: Documents from Zero',
    'category'    => 'office',
    'level'       => 'beginner',
    'icon'        => 'file-text',
    'sort'        => 30,
    'summary'     => 'Learn to type, format and print professional documents in Microsoft Word: letters, CVs, reports and more. No experience needed.',
    'description' => '<p>Microsoft Word is the most widely used program for writing documents. Schools, offices, NGOs and small businesses use it every day for letters, CVs, reports, proposals, notices and forms. This course takes you from opening Word for the first time to producing clean, professional documents.</p><p>Each lesson is short and practical. You read a little, then you try it on your own computer. By the end you will have made a CV, a formal letter and a short report, and you will know how to review, share, protect and print your work.</p><p>The course is for anyone new to Word: students, job seekers, volunteers and small business owners. The steps match Microsoft 365 and Word 2019 or later on Windows 10 and 11, and most steps work the same on a Mac.</p>',
    'outcomes'    => [
        'Create, save, open and close Word documents in the right file format',
        'Type and format text with fonts, sizes, colours, alignment and spacing',
        'Use bullets, numbering, styles and headings to organise a document',
        'Set up pages with margins, orientation, headers, footers and page numbers',
        'Build a CV, a formal letter and a simple report from scratch',
        'Insert and format tables, pictures, shapes and charts',
        'Review documents with comments and Track Changes, and protect them',
        'Print, save as PDF and share a document with other people',
    ],
    'requirements' => ['A computer with Microsoft Word installed (or Word online), or access to one at a centre', 'No experience needed'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Microsoft Support: Word help', 'url' => 'https://support.microsoft.com/word'],
    ],
    'modules' => [
        [
            'title'   => 'Module 1: Getting started with Word',
            'summary' => 'Meet the Word window, then learn to create, save, open and view documents.',
            'lessons' => [
                [
                    'title'   => 'What Word is and how the window looks',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to open Word and find your way around its main parts. This matters because every task in Word starts with knowing where the buttons are, and a confident start saves hours of searching later.</p>
<h3>What is Word?</h3>
<p>Microsoft Word is a <strong>word processor</strong>: a program for writing, changing and printing documents. Compared with a typewriter, you can fix mistakes before printing, change how the text looks, add pictures and tables, and save the work to use again. Word is part of Microsoft 365 (also called Office). A Word file is called a <strong>document</strong>.</p>
<h3>Opening Word</h3>
<ol>
<li>Click the <strong>Start</strong> button (the Windows logo) at the bottom of the screen.</li>
<li>Type <code>Word</code> and press <strong>Enter</strong>, or click the Word icon in the list.</li>
<li>On the first screen, click <strong>Blank document</strong>.</li>
</ol>
<h3>The parts of the window</h3>
<table>
<thead><tr><th>Part</th><th>Where</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Title bar</td><td>Very top</td><td>Shows the file name and the buttons to minimise, maximise and close.</td></tr>
<tr><td>Quick Access Toolbar</td><td>Top left</td><td>Small shortcuts such as Save, Undo and Redo. You can add your own.</td></tr>
<tr><td>Ribbon</td><td>Under the title bar</td><td>The wide strip of buttons. It is split into <strong>tabs</strong>.</td></tr>
<tr><td>Tabs</td><td>Top of the ribbon</td><td>File, Home, Insert, Draw, Design, Layout, References, Mailings, Review, View.</td></tr>
<tr><td>Document area</td><td>Centre</td><td>The white page where you type.</td></tr>
<tr><td>Ruler</td><td>Above and left of the page</td><td>Shows margins and indents. Turn it on from the View tab.</td></tr>
<tr><td>Status bar</td><td>Very bottom</td><td>Shows the page number, word count, language and the zoom slider.</td></tr>
</tbody>
</table>
<h3>Understanding the ribbon</h3>
<p>Each tab holds <strong>groups</strong> of related buttons. For example, the <strong>Home</strong> tab has the groups Clipboard, Font, Paragraph, Styles and Editing. The name of each group is written at its bottom. A small arrow in the corner of a group opens a <strong>dialog box</strong> with more choices. If you hover your mouse over any button, a small label tells you its name and shortcut.</p>
<p>Some tabs appear only when needed. When you click a picture or a table, extra tabs such as <strong>Picture Format</strong> or <strong>Table Design</strong> show up. If you cannot find a button, look for one of these.</p>
<h3>The blinking line</h3>
<p>The blinking vertical line in the page is the <strong>cursor</strong> (or insertion point). Whatever you type appears at the cursor. Click anywhere in the text to move it.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open Word and create a blank document.</li>
<li>Click each tab (Home, Insert, Layout, Review, View) and look at the groups.</li>
<li>Hover over three buttons and read their labels.</li>
<li>Type your name and your town, then look at the word count in the status bar.</li>
<li>Click the <strong>View</strong> tab and tick <strong>Ruler</strong> to show it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Word is a word processor for creating, editing and printing documents.</li>
<li>The ribbon is divided into tabs, and each tab into groups of buttons.</li>
<li>The Quick Access Toolbar keeps your favourite shortcuts, such as Save and Undo.</li>
<li>The status bar shows the page, the word count and the zoom slider.</li>
<li>Extra tabs appear when you select a picture, table or shape.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Creating, saving and opening documents',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn to create a new document, save it where you can find it again, and open it later. Losing an hour of work because it was never saved is a very common beginner problem, so we will build good habits from the start.</p>
<h3>Creating a new document</h3>
<p>Click <strong>File</strong>, then <strong>New</strong>, then <strong>Blank document</strong>. The faster way is the shortcut <strong>Ctrl+N</strong> (Mac: Cmd+N).</p>
<h3>Saving for the first time</h3>
<ol>
<li>Press <strong>Ctrl+S</strong> (Mac: Cmd+S), or click <strong>File</strong> then <strong>Save</strong>.</li>
<li>Choose a place: <strong>This PC</strong> then <strong>Documents</strong>, or OneDrive if you have an account.</li>
<li>In <strong>File name</strong>, type a clear name such as <code>Letter to Head Teacher</code>.</li>
<li>Click <strong>Save</strong>.</li>
</ol>
<p>Use names that tell you what the file is. A name like <code>Document1</code> will confuse you in a month.</p>
<h3>Save and Save As</h3>
<table>
<thead><tr><th>Command</th><th>What it does</th><th>Use it when</th></tr></thead>
<tbody>
<tr><td>Save (Ctrl+S)</td><td>Updates the same file with your latest changes.</td><td>You are working on a file that already has a name. Do this often.</td></tr>
<tr><td>Save As (F12)</td><td>Makes a new copy with a new name, place or format.</td><td>You want to keep the original, or need a PDF or another format.</td></tr>
</tbody>
</table>
<h3>AutoSave</h3>
<p>If your files are stored in OneDrive or SharePoint, Word has an <strong>AutoSave</strong> switch at the top left. When it is on, Word saves your changes every few seconds. If you save on your own computer, AutoSave is not available, so press Ctrl+S every few minutes. Word also keeps recovery copies after a power cut (very common during load shedding), but never depend on them.</p>
<h3>File formats</h3>
<ul>
<li><strong>.docx</strong>: the normal Word format. Use it while you are still editing.</li>
<li><strong>.pdf</strong>: a fixed copy that looks the same on every device. Use it to send a finished CV or letter.</li>
<li><strong>.doc</strong>: the old Word format from before 2007. Only use it if someone has a very old computer.</li>
<li><strong>.txt</strong>: plain text with no formatting.</li>
</ul>
<p>To choose a format, use <strong>File</strong> then <strong>Save As</strong> and change <strong>Save as type</strong>.</p>
<h3>Opening and closing</h3>
<p>Press <strong>Ctrl+O</strong> (Mac: Cmd+O), or click <strong>File</strong> then <strong>Open</strong>. Pick from <strong>Recent</strong> or click <strong>Browse</strong>. To close the document, click <strong>File</strong> then <strong>Close</strong>. If you made changes since the last save, Word asks whether to save: choose <strong>Save</strong>.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a blank document and type three sentences about your day.</li>
<li>Save it in Documents with the name <code>My First Document</code>.</li>
<li>Type one more sentence and press Ctrl+S.</li>
<li>Use Save As to make a PDF copy named <code>My First Document PDF</code>.</li>
<li>Close Word, then open the .docx file again from the Recent list.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Save early, and save often with Ctrl+S.</li>
<li>Save As makes a new copy; Save updates the current file.</li>
<li>AutoSave works only for files stored in OneDrive or SharePoint.</li>
<li>Use .docx to edit and .pdf to send a finished document.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Views, zoom and moving around',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>This lesson shows you how to see your document in different ways and move quickly through long pages. Being able to zoom in on small text, or zoom out to see a whole page, makes working on a small laptop screen much easier.</p>
<h3>Document views</h3>
<p>A <strong>view</strong> is a way of displaying your document on the screen. Click the <strong>View</strong> tab to see the choices.</p>
<table>
<thead><tr><th>View</th><th>What you see</th><th>Best for</th></tr></thead>
<tbody>
<tr><td>Print Layout</td><td>Pages exactly as they will print, with margins.</td><td>Most work. This is the normal view.</td></tr>
<tr><td>Read Mode</td><td>Clean pages for reading, with no ribbon.</td><td>Reading a document on screen. Press Esc to leave.</td></tr>
<tr><td>Web Layout</td><td>Text as it would look on a web page.</td><td>Rarely needed.</td></tr>
<tr><td>Outline</td><td>Only headings and their levels.</td><td>Planning and reorganising a long document.</td></tr>
<tr><td>Draft</td><td>Text only, without pictures and margins.</td><td>Fast typing.</td></tr>
</tbody>
</table>
<p>The three small view buttons at the bottom right of the window (next to the zoom slider) switch quickly between Read Mode, Print Layout and Web Layout.</p>
<h3>Zoom</h3>
<p>Zoom changes how large the page looks, not how large it prints.</p>
<ul>
<li>Drag the <strong>zoom slider</strong> at the bottom right.</li>
<li>Click the <strong>+</strong> and <strong>-</strong> buttons beside it.</li>
<li>Hold <strong>Ctrl</strong> and roll the mouse wheel.</li>
<li>On the <strong>View</strong> tab choose <strong>One Page</strong>, <strong>Multiple Pages</strong> or <strong>Page Width</strong>.</li>
</ul>
<h3>Moving around quickly</h3>
<table>
<thead><tr><th>Keys</th><th>Result</th></tr></thead>
<tbody>
<tr><td>Ctrl+Home</td><td>Go to the start of the document (Mac: Cmd+Fn+Left)</td></tr>
<tr><td>Ctrl+End</td><td>Go to the end of the document (Mac: Cmd+Fn+Right)</td></tr>
<tr><td>Home / End</td><td>Start / end of the line</td></tr>
<tr><td>Page Up / Page Down</td><td>One screen up or down</td></tr>
<tr><td>Ctrl+Left / Right arrow</td><td>One word at a time (Mac: Option+arrow)</td></tr>
<tr><td>Ctrl+G</td><td>Go to a page number (Mac: Option+Cmd+G)</td></tr>
</tbody>
</table>
<h3>The Navigation Pane</h3>
<p>Tick <strong>Navigation Pane</strong> on the View tab. A panel opens on the left. It lists your headings (once you use them) and lets you search the document. Click a heading to jump there.</p>
<h3>Show or hide formatting marks</h3>
<p>On the <strong>Home</strong> tab, click the <strong>paragraph mark</strong> button (it looks like a backward P) or press <strong>Ctrl+Shift+8</strong>. Word shows dots for spaces and arrows for tabs. Beginners often use many spaces to line things up; the marks help you spot this mistake.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open any document with more than one page, or paste the same paragraph ten times.</li>
<li>Switch to Read Mode, then press Esc to return to Print Layout.</li>
<li>Zoom to 50 percent, then to Page Width.</li>
<li>Use Ctrl+End and Ctrl+Home to jump to the end and the start.</li>
<li>Turn the formatting marks on and off.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Print Layout shows how the pages will look on paper.</li>
<li>Zoom changes only the screen size of the page.</li>
<li>Keyboard shortcuts move you around faster than scrolling.</li>
<li>Formatting marks reveal hidden spaces, tabs and paragraph breaks.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Templates and getting help',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>You do not always have to start from an empty page. In this lesson you will learn to begin from a ready-made template and to find answers inside Word when you are stuck. These two skills save a lot of time and make you independent.</p>
<h3>What is a template?</h3>
<p>A <strong>template</strong> is a document that already has the layout, fonts and colours set. You only replace the sample words with your own. Word includes templates for CVs, letters, reports, flyers, invoices, calendars and newsletters.</p>
<h3>Using a template</h3>
<ol>
<li>Click <strong>File</strong> then <strong>New</strong>.</li>
<li>Look through the pictures, or type a word such as <code>resume</code> or <code>invoice</code> in the search box and press Enter. You need an internet connection to search online templates.</li>
<li>Click a template to see a preview, then click <strong>Create</strong>.</li>
<li>Click on the sample text and type your own words over it.</li>
<li>Save the file with a new name.</li>
</ol>
<p>Choose simple templates. A template with too many colours, boxes and pictures looks busy and is hard to edit. For a job application, a plain black-and-white design is usually best.</p>
<h3>Making your own template</h3>
<p>If you write the same kind of document often, such as an invoice for your shop, prepare it once. Then choose <strong>File</strong>, <strong>Save As</strong>, and set <strong>Save as type</strong> to <strong>Word Template (*.dotx)</strong>. When you open a template file, Word creates a new copy each time, so the original stays clean.</p>
<h3>Getting help</h3>
<ul>
<li><strong>Tell me what you want to do</strong>: the search box at the top of the window (in some versions it is labelled <strong>Search</strong>). Type what you want, for example <code>page numbers</code>, and Word shows the button or opens the right tool. Shortcut: Alt+Q on Windows.</li>
<li><strong>F1</strong>: opens the Help pane.</li>
<li><strong>Tell-me-more tips</strong>: hover over a button and wait for the tooltip.</li>
<li><strong>Online</strong>: the Microsoft Support website has step-by-step articles. Search for exactly what you want to do.</li>
</ul>
<h3>Solving problems</h3>
<p>If Word stops responding, wait a minute before closing it. If you must close it, reopen Word: a <strong>Document Recovery</strong> pane on the left offers the saved versions. If a button is grey, it usually means it cannot be used on what you have selected, so select the right thing first.</p>
<h3>Try it yourself</h3>
<ol>
<li>Click File, New, and search for <code>letter</code>.</li>
<li>Create a document from a simple letter template and replace the sample text with your own name and address.</li>
<li>Save it as <code>My letter template test</code>.</li>
<li>Use the search box at the top to look up <code>word count</code> and read what Word suggests.</li>
<li>Press F1 and search for a topic that you want to learn next.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A template is a ready-made starting point; replace the sample text with yours.</li>
<li>Keep layouts simple, especially for CVs and letters.</li>
<li>Save repeated documents as templates (.dotx).</li>
<li>Use the search box and F1 whenever you cannot find a button.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Where do you find the buttons for Bold, Italic and font size?
- On the View tab
- In the status bar
* In the Font group on the Home tab
- On the File tab
= Text formatting buttons are in the Font group of the Home tab.

Q: You have a saved file called "CV.docx" and want to keep it unchanged while making a different version. What should you use?
- Save
* Save As, with a new name
- Close without saving
- Print Preview
= Save As creates a new copy under a new name, leaving the original untouched.

Q: Which file format is best for sending a finished CV so that it looks the same on every phone and computer?
- .docx
- .txt
* .pdf
- .dotx
= PDF keeps the layout fixed on every device. Use .docx while you are still editing.

Q: When does the AutoSave switch work?
- Always, on every computer
- Only when the document is printed
* When the file is stored in OneDrive or SharePoint
- Only in Read Mode
= AutoSave needs the file to be stored online in OneDrive or SharePoint. Otherwise press Ctrl+S yourself.

Q: Which view shows the pages exactly as they will print?
- Outline
* Print Layout
- Draft
- Web Layout
= Print Layout shows margins, headers and page edges as they will appear on paper.

Q: Which of these keyboard shortcuts take you to the start of the document? (choose one)
- Ctrl+End
* Ctrl+Home
- Ctrl+G
- Page Down
= Ctrl+Home goes to the beginning; Ctrl+End goes to the end.

Q: What is a template?
- A tool for checking spelling
* A ready-made document with layout and styles already set
- A kind of printer
- A backup copy
= A template gives you a ready layout so you only add your own content.

Q: You cannot find the button for page numbers. What is a quick way to find it?
- Reinstall Word
* Type "page numbers" in the search box at the top of the window
- Delete the document and start again
- Restart the computer
= The search box finds commands by name and can open them for you.
QUIZ,
        ],
        [
            'title'   => 'Module 2: Typing and formatting text',
            'summary' => 'Type, select, edit and format text, paragraphs and lists, then check your spelling.',
            'lessons' => [
                [
                    'title'   => 'Typing, selecting and editing text',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to type correctly, select any amount of text, move or copy it, and undo mistakes. These are the basic skills behind everything else you do in Word, and they make you fast.</p>
<h3>Typing rules that save time</h3>
<ul>
<li>Do <strong>not</strong> press Enter at the end of every line. Word moves to the next line by itself (this is called <strong>word wrap</strong>). Press Enter only to start a new paragraph.</li>
<li>Use <strong>one</strong> space after each word and after a full stop.</li>
<li>Do not use the space bar to move text across the page. Use indents, tabs or tables (later lessons).</li>
<li>Press <strong>Backspace</strong> to delete the letter before the cursor, and <strong>Delete</strong> to remove the letter after it.</li>
<li>Press <strong>Caps Lock</strong> for capitals; press it again to turn it off. Use <strong>Shift</strong> for single capital letters.</li>
</ul>
<h3>Selecting text</h3>
<p>You must <strong>select</strong> text before you can change it. Selected text is shown with a coloured background.</p>
<table>
<thead><tr><th>To select</th><th>Do this</th></tr></thead>
<tbody>
<tr><td>Any amount</td><td>Click and drag, or click at the start, hold Shift, and click at the end</td></tr>
<tr><td>One word</td><td>Double-click it</td></tr>
<tr><td>A sentence</td><td>Hold Ctrl and click in the sentence (Mac: Cmd+click)</td></tr>
<tr><td>A paragraph</td><td>Triple-click inside it</td></tr>
<tr><td>A whole line</td><td>Click in the left margin beside the line</td></tr>
<tr><td>Everything</td><td>Ctrl+A (Mac: Cmd+A)</td></tr>
</tbody>
</table>
<h3>Cut, copy and paste</h3>
<ol>
<li>Select the text.</li>
<li><strong>Cut</strong> (Ctrl+X, Mac Cmd+X) removes it and holds it in the clipboard. <strong>Copy</strong> (Ctrl+C, Mac Cmd+C) keeps the original and holds a copy.</li>
<li>Click where you want the text.</li>
<li><strong>Paste</strong> with Ctrl+V (Mac: Cmd+V).</li>
</ol>
<p>After pasting, a small <strong>Paste Options</strong> button appears. Choose <strong>Keep Text Only</strong> when you copy from a web page, so the web styles do not come with it. You can also drag selected text with the mouse to move it.</p>
<h3>Undo and Redo</h3>
<p><strong>Ctrl+Z</strong> (Mac: Cmd+Z) undoes the last action; you can press it many times. <strong>Ctrl+Y</strong> (Mac: Cmd+Y or Cmd+Shift+Z) redoes what you undid. These buttons are also on the Quick Access Toolbar. Do not be afraid to experiment: you can always undo.</p>
<h3>Common mistakes</h3>
<ul>
<li>Typing over selected text by accident: if text vanishes, press Ctrl+Z straight away.</li>
<li>The <strong>Insert</strong> key switches to overwrite mode, where new letters replace old ones. Press Insert again to switch it off.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Type a short paragraph (4 sentences) about your favourite food, without pressing Enter inside it.</li>
<li>Select the first sentence with Ctrl+click and cut it. Paste it at the end of the paragraph.</li>
<li>Double-click a word and replace it by typing another word.</li>
<li>Copy the whole paragraph and paste it twice below.</li>
<li>Use Ctrl+Z until you are back to one paragraph.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Let word wrap work; press Enter only at the end of a paragraph.</li>
<li>Select first, then format or edit.</li>
<li>Ctrl+X, Ctrl+C and Ctrl+V cut, copy and paste.</li>
<li>Ctrl+Z is your safety net.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Fonts, size, colour and alignment',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>In this lesson you will change how text looks: the font, its size, bold or italic, its colour and where it sits on the line. Good formatting makes a document easy to read and shows care, which matters when you apply for a job or send a letter to an office.</p>
<h3>The Font group</h3>
<p>Select your text first, then use the <strong>Font</strong> group on the <strong>Home</strong> tab.</p>
<ul>
<li><strong>Font</strong> (typeface): the style of the letters. For serious documents use a simple, clear font such as Calibri, Arial, Times New Roman or Cambria. Avoid decorative fonts.</li>
<li><strong>Font size</strong>: measured in points. Normal text is usually 11 or 12. Headings are bigger, such as 14 to 18.</li>
<li><strong>Grow</strong> and <strong>Shrink</strong> buttons: Ctrl+Shift+&gt; and Ctrl+Shift+&lt; make text bigger or smaller step by step.</li>
</ul>
<h3>Bold, italic and underline</h3>
<table>
<thead><tr><th>Style</th><th>Shortcut</th><th>Use it for</th></tr></thead>
<tbody>
<tr><td><strong>Bold</strong></td><td>Ctrl+B (Mac: Cmd+B)</td><td>Headings and important words</td></tr>
<tr><td><em>Italic</em></td><td>Ctrl+I (Mac: Cmd+I)</td><td>Titles of books, foreign words, light emphasis</td></tr>
<tr><td>Underline</td><td>Ctrl+U (Mac: Cmd+U)</td><td>Rarely; it can look like a web link</td></tr>
</tbody>
</table>
<p>Do not use all three at once. Too much emphasis means nothing stands out.</p>
<h3>Colour and highlight</h3>
<ol>
<li>Select the text.</li>
<li>Click the small arrow beside <strong>Font Color</strong> (the letter A with a coloured bar) and choose a colour.</li>
<li>For a marker-pen effect, click the arrow beside <strong>Text Highlight Color</strong> (the pen icon).</li>
</ol>
<p>Choose dark text on a light background. Pale yellow text on white is impossible to read, especially when printed in black and white.</p>
<h3>Clear formatting</h3>
<p>If a piece of text has become a mess, select it and click <strong>Clear All Formatting</strong> (the A with a small eraser) in the Font group. It returns to the plain style.</p>
<h3>Alignment</h3>
<p>Alignment decides how a paragraph sits between the margins. Click anywhere inside a paragraph (no need to select all of it) and use these buttons in the Paragraph group.</p>
<table>
<thead><tr><th>Alignment</th><th>Shortcut</th><th>Best for</th></tr></thead>
<tbody>
<tr><td>Align Left</td><td>Ctrl+L (Mac: Cmd+L)</td><td>Most body text, easiest to read</td></tr>
<tr><td>Center</td><td>Ctrl+E (Mac: Cmd+E)</td><td>Titles and certificates</td></tr>
<tr><td>Align Right</td><td>Ctrl+R (Mac: Cmd+R)</td><td>Dates and addresses in letters</td></tr>
<tr><td>Justify</td><td>Ctrl+J (Mac: Cmd+J)</td><td>Neat edges in newspapers; can leave odd gaps between words</td></tr>
</tbody>
</table>
<h3>Try it yourself</h3>
<ol>
<li>Type your name on the first line, a job title on the second, and a three-line paragraph about yourself.</li>
<li>Make your name 20 points, bold, in a dark blue colour, and centre it.</li>
<li>Make the job title 14 points, italic, centred.</li>
<li>Make the paragraph 12 points and left-aligned.</li>
<li>Select the whole paragraph and use Clear All Formatting; then press Ctrl+Z to bring the formatting back.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Select text first, then apply a font, size or style.</li>
<li>Keep to one or two fonts and plain colours.</li>
<li>Bold is for headings and key words; do not overuse emphasis.</li>
<li>Left alignment is the easiest to read for long text.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Spacing, indents, lists and Format Painter',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>You will learn to control the space between lines and paragraphs, indent text, make bullet and numbered lists, and copy a format quickly. These tools turn a block of crowded text into something clean and easy to scan.</p>
<h3>Line and paragraph spacing</h3>
<p><strong>Line spacing</strong> is the gap between lines inside a paragraph. <strong>Paragraph spacing</strong> is the gap before and after a paragraph. Using these is much better than pressing Enter twice to make a gap.</p>
<ol>
<li>Click in the paragraph (or select several).</li>
<li>On the <strong>Home</strong> tab, click <strong>Line and Paragraph Spacing</strong> in the Paragraph group.</li>
<li>Choose 1.0, 1.15, 1.5 or 2.0.</li>
<li>Choose <strong>Add Space Before Paragraph</strong> or <strong>Add Space After Paragraph</strong>.</li>
</ol>
<p>School work is often set at 1.5 line spacing. For a CV or letter, 1.0 or 1.15 with a little space after each paragraph looks neat. For finer control, open <strong>Line Spacing Options</strong> and type values in the <strong>Before</strong> and <strong>After</strong> boxes (for example 6 pt).</p>
<h3>Indents</h3>
<p>An <strong>indent</strong> moves a paragraph in from the margin. Click <strong>Increase Indent</strong> or <strong>Decrease Indent</strong> in the Paragraph group. For a first-line indent, open the Paragraph dialog box (the small arrow in the group corner), find <strong>Special</strong> and choose <strong>First line</strong>. Never use the space bar to indent.</p>
<h3>Bullets and numbering</h3>
<ul>
<li>Use <strong>bullets</strong> when the order does not matter, such as a list of skills.</li>
<li>Use <strong>numbering</strong> when order matters, such as steps in a process.</li>
</ul>
<p>Select the lines and click <strong>Bullets</strong> or <strong>Numbering</strong>, or type <code>1.</code> then a space, or <code>*</code> then a space, and press Enter: Word starts the list for you. To finish a list, press Enter twice. Press <strong>Tab</strong> to push an item into a sub-list, and <strong>Shift+Tab</strong> to bring it back. Use the arrow beside the button to pick a different bullet or number style.</p>
<h3>Format Painter</h3>
<p>Format Painter copies the look of text, not the words.</p>
<ol>
<li>Click on text that has the format you like.</li>
<li>Click <strong>Format Painter</strong> (the paintbrush) in the Clipboard group.</li>
<li>Drag across the text that should look the same.</li>
</ol>
<p>Double-click the paintbrush to use it several times; press Esc to stop. Shortcuts: Ctrl+Shift+C to copy the format and Ctrl+Shift+V to paste it (Mac: Cmd+Option+C and Cmd+Option+V).</p>
<h3>Common mistakes</h3>
<ul>
<li>Pressing Enter several times to make space. Use paragraph spacing instead.</li>
<li>Typing bullet symbols by hand. Use the Bullets button so the list stays tidy.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Type five skills you have, one per line, and turn them into a bulleted list.</li>
<li>Type four steps to make tea or cook rice and make them a numbered list.</li>
<li>Set a paragraph of text to 1.5 line spacing with 6 pt space after.</li>
<li>Give one heading a bold, blue format, then use Format Painter to copy that look to another heading.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Use line and paragraph spacing instead of extra Enter presses.</li>
<li>Use indents, not spaces, to move text in.</li>
<li>Bullets are for unordered lists and numbers for steps.</li>
<li>Format Painter copies a format to other text in one click.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Find and Replace, spelling and grammar',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>In this lesson you will find words quickly, replace them everywhere in one go, and check your spelling and grammar. A single typing mistake in a CV or a letter to an employer can cost you an opportunity, so checking is part of every document you write.</p>
<h3>Find</h3>
<ol>
<li>Press <strong>Ctrl+F</strong> (Mac: Cmd+F). The Navigation Pane opens on the left.</li>
<li>Type the word you want. Word highlights every place it appears in yellow and lists the results.</li>
<li>Click a result to jump to it.</li>
</ol>
<h3>Replace</h3>
<p>Imagine you wrote a report for a village group and later learned the group name changed. You can fix every mention at once.</p>
<ol>
<li>Press <strong>Ctrl+H</strong> (Mac: Ctrl+H or Cmd+Shift+H).</li>
<li>In <strong>Find what</strong>, type the old word.</li>
<li>In <strong>Replace with</strong>, type the new word.</li>
<li>Click <strong>Replace</strong> to change one at a time, or <strong>Replace All</strong> to change every one.</li>
</ol>
<p>Be careful with Replace All. If you replace <code>he</code> you will also change the inside of words like <code>the</code> and <code>when</code>. Click <strong>More</strong> and tick <strong>Find whole words only</strong> to avoid this. Tick <strong>Match case</strong> if capital letters matter. If you make a mistake, press Ctrl+Z.</p>
<h3>Spelling and grammar</h3>
<p>Word checks as you type:</p>
<ul>
<li>A <strong>red wavy line</strong> under a word means a possible spelling mistake.</li>
<li>A <strong>blue double line</strong> (or a blue wavy line in some versions) means a possible grammar or style problem.</li>
</ul>
<p>Right-click the underlined word and choose a suggestion, or choose <strong>Ignore All</strong> if it is a name or local word that is correct. Choose <strong>Add to Dictionary</strong> to teach Word words such as <em>Acholi</em> or <em>Gulu</em> so they are not flagged again.</p>
<h3>Running the full check</h3>
<p>Press <strong>F7</strong>, or click <strong>Review</strong> then <strong>Editor</strong> (older versions: <strong>Spelling and Grammar</strong>). The pane lists the problems one by one. Read each suggestion. Word is a helper, not a teacher who is always right.</p>
<h3>Language setting</h3>
<p>If correct words are marked as wrong, check the language. Select the text, click <strong>Review</strong>, then <strong>Language</strong>, then <strong>Set Proofing Language</strong>, and choose <strong>English (United Kingdom)</strong>. Uganda uses British spelling, such as <em>organise</em> and <em>colour</em>.</p>
<h3>What Word cannot catch</h3>
<p>Word will not notice that <em>their</em> should be <em>there</em> every time, or that a phone number has a wrong digit. Read the document slowly, or read it aloud, and ask a friend to check it. In Word you can also use <strong>Review</strong> then <strong>Read Aloud</strong> to hear it.</p>
<h3>Try it yourself</h3>
<ol>
<li>Type a paragraph of five sentences with at least three deliberate spelling mistakes.</li>
<li>Right-click each red-underlined word and fix it.</li>
<li>Use Ctrl+H to replace one word, such as <code>school</code>, with <code>college</code> using Replace All.</li>
<li>Press F7 and go through the check.</li>
<li>Set the proofing language to English (United Kingdom).</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Ctrl+F finds text and Ctrl+H replaces it.</li>
<li>Use Find whole words only to avoid replacing parts of other words.</li>
<li>Red lines mean spelling and blue lines mean grammar or style.</li>
<li>Always proofread yourself, because Word cannot catch every mistake.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: You are typing a paragraph and reach the right edge of the page. What should you do?
- Press Enter to start a new line
- Press the space bar many times
* Keep typing, because Word wraps the line automatically
- Press Tab
= Word wrap moves text to the next line. Press Enter only to start a new paragraph.

Q: What does Ctrl+Z do?
- Cuts the selected text
* Undoes the last action
- Zooms the page
- Closes the document
= Ctrl+Z is Undo. It is your safety net.

Q: Which method selects an entire paragraph quickly?
- Double-click a word
- Press Ctrl+A
* Triple-click inside the paragraph
- Press Home
= Triple-click selects the paragraph; double-click selects one word; Ctrl+A selects everything.

Q: Which alignment is easiest to read for long body text?
* Left
- Center
- Right
- None of them
= Left-aligned text keeps an even start to every line, which is easiest for the eye.

Q: How should you make a gap between two paragraphs in a neat document?
- Press Enter three times
- Type spaces until it moves
* Use paragraph spacing (Before or After)
- Press Tab five times
= Paragraph spacing keeps the layout consistent and easy to change later.

Q: When should you use a numbered list instead of bullets?
- When you want to look fancy
* When the order of the items matters, such as steps
- When there is only one item
- Never
= Numbers show a sequence. Bullets suit lists where the order does not matter.

Q: What does Format Painter copy?
- The words only
- A whole page
* The look (format) of the text
- The spelling language
= Format Painter copies fonts, size, colour and similar formatting to other text.

Q: You use Replace All to change "he" to "she" and the word "the" turns into "tshe". How could you have avoided this?
- By using a larger font
* By ticking "Find whole words only"
- By saving first
- By turning off AutoSave
= Whole-word matching stops Word from changing letters inside other words.
QUIZ,
        ],
        [
            'title'   => 'Module 3: Page layout and professional documents',
            'summary' => 'Set up pages, use styles and headings, and build a CV, a letter and a report.',
            'lessons' => [
                [
                    'title'   => 'Page setup: margins, orientation, size and columns',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>This lesson teaches you to set up the page itself before or after you type: the margins, the direction of the paper, the paper size and the columns. Correct page setup makes documents look professional and prevents surprises when you print.</p>
<h3>Where to find the settings</h3>
<p>Click the <strong>Layout</strong> tab (in some versions it is called <strong>Page Layout</strong>). The <strong>Page Setup</strong> group contains Margins, Orientation, Size, Columns, Breaks and more.</p>
<h3>Margins</h3>
<p><strong>Margins</strong> are the empty spaces between the text and the edge of the paper. Click <strong>Margins</strong> and choose:</p>
<ul>
<li><strong>Normal</strong>: 2.54 cm (1 inch) on every side. A good default.</li>
<li><strong>Narrow</strong>: 1.27 cm. Fits more text, but looks crowded.</li>
<li><strong>Custom Margins</strong>: type your own values. Many schools ask for 2.5 cm or a wider left margin if pages will be bound.</li>
</ul>
<h3>Orientation</h3>
<p>Click <strong>Orientation</strong> and choose <strong>Portrait</strong> (tall page, the normal choice for letters and reports) or <strong>Landscape</strong> (wide page, good for wide tables, timetables and certificates).</p>
<h3>Paper size</h3>
<p>Click <strong>Size</strong>. In Uganda, <strong>A4</strong> (21 x 29.7 cm) is the standard. Word may start in <strong>Letter</strong> size, which is American and slightly different. Always check that A4 is selected before you print, or the edges may be cut off.</p>
<h3>Columns</h3>
<p>Newsletters and notices often use two or three columns. Select the text, click <strong>Columns</strong>, and choose <strong>Two</strong> or <strong>Three</strong>. To add a thin line between columns, choose <strong>More Columns</strong> and tick <strong>Line between</strong>. Use columns only for short pieces; long reports are easier to read in a single column.</p>
<h3>Page and section breaks</h3>
<ul>
<li><strong>Page break</strong> (Ctrl+Enter): starts a new page exactly where you want, such as the start of a new chapter. Do not press Enter again and again to reach the next page.</li>
<li><strong>Section break</strong> (Layout, then Breaks, then Next Page): lets different parts of a document have their own margins, orientation or page numbers. Use it, for example, to make one landscape page for a wide table inside a portrait report.</li>
</ul>
<h3>Page borders and watermark</h3>
<p>On the <strong>Design</strong> tab you will find <strong>Page Borders</strong> (a frame around each page, nice for certificates) and <strong>Watermark</strong> (faint words such as DRAFT behind the text).</p>
<h3>Common mistakes</h3>
<ul>
<li>Making margins very small to squeeze text onto one page. If the text does not fit, shorten it.</li>
<li>Leaving the paper size on Letter when you print on A4.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Open a new document and type or paste about 10 lines of text.</li>
<li>Set the margins to Custom with 2.5 cm all round.</li>
<li>Check that the paper size is A4.</li>
<li>Turn the text into two columns, then change it back to one.</li>
<li>Insert a page break with Ctrl+Enter and type the word <code>Page 2</code>.</li>
<li>Change the orientation to Landscape and then back to Portrait.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Page Setup is on the Layout tab.</li>
<li>Use A4 paper size in Uganda, and Normal margins unless told otherwise.</li>
<li>Use a page break (Ctrl+Enter), not repeated Enter, to start a new page.</li>
<li>Use section breaks when parts of a document need different layouts.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Headers, footers, page numbers and the table of contents',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>You will learn to add running text at the top and bottom of every page, number your pages, and build an automatic table of contents. These features make a long report or school project look organised and easy to navigate.</p>
<h3>Headers and footers</h3>
<p>A <strong>header</strong> is the area at the top of each page, and a <strong>footer</strong> is the area at the bottom. They repeat on every page, so they suit titles, names, dates and page numbers.</p>
<ol>
<li>Click the <strong>Insert</strong> tab.</li>
<li>In the <strong>Header &amp; Footer</strong> group, click <strong>Header</strong> and choose <strong>Blank</strong> (or a built-in style).</li>
<li>Type your text, for example the report title.</li>
<li>Click <strong>Close Header and Footer</strong>, or double-click the main page.</li>
</ol>
<p>To edit again, double-click the header or footer area. The main text becomes pale while you work there. A <strong>Header &amp; Footer</strong> tab appears with extra options, such as <strong>Different First Page</strong>, which lets the cover page have no header.</p>
<h3>Page numbers</h3>
<ol>
<li>Click <strong>Insert</strong>, then <strong>Page Number</strong>.</li>
<li>Choose where: <strong>Top of Page</strong>, <strong>Bottom of Page</strong>, and so on.</li>
<li>Pick a style, for example <strong>Plain Number 3</strong> for the bottom right.</li>
</ol>
<p>To start the numbering from a different number, or to remove the number from a cover page, tick <strong>Different First Page</strong> and use <strong>Format Page Numbers</strong>. You can also insert <strong>Page X of Y</strong> from the Page Number menu.</p>
<h3>Headings and styles first</h3>
<p>A <strong>style</strong> is a saved set of formatting (font, size, colour, spacing). Instead of formatting each heading by hand, you apply a style:</p>
<ol>
<li>Click in the heading line.</li>
<li>On the <strong>Home</strong> tab, in the <strong>Styles</strong> group, click <strong>Heading 1</strong> for main headings or <strong>Heading 2</strong> for sub-headings.</li>
</ol>
<p>Shortcuts: Ctrl+Alt+1 for Heading 1 and Ctrl+Alt+2 for Heading 2 (Mac: Cmd+Option+1 and 2). To change how every Heading 1 looks, right-click <strong>Heading 1</strong> in the gallery, choose <strong>Modify</strong>, and change the font once. All headings update at the same time. Use <strong>Title</strong> for the document title and <strong>Normal</strong> for body text.</p>
<h3>Table of contents</h3>
<p>When your headings use styles, Word can list them automatically.</p>
<ol>
<li>Click where the contents page should go (after the cover page).</li>
<li>Click <strong>References</strong>, then <strong>Table of Contents</strong>.</li>
<li>Choose <strong>Automatic Table</strong> 1 or 2.</li>
</ol>
<p>If you add, remove or rename headings later, click the table and choose <strong>Update Table</strong> (or press F9). Choose <strong>Update entire table</strong> so both the titles and the page numbers refresh.</p>
<h3>Try it yourself</h3>
<ol>
<li>Type three headings with a few lines under each, for example <em>Introduction</em>, <em>Findings</em>, <em>Conclusion</em>.</li>
<li>Apply Heading 1 to each heading.</li>
<li>Add a header with your name and a page number in the footer.</li>
<li>Insert an automatic table of contents at the top.</li>
<li>Rename one heading and update the table to see the change.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Headers and footers repeat on every page; double-click them to edit.</li>
<li>Insert page numbers from the Insert tab, not by typing them.</li>
<li>Use heading styles instead of formatting each heading by hand.</li>
<li>An automatic table of contents depends on heading styles; update it after changes.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Writing a CV and a formal letter',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>In this lesson you will build two documents that almost everyone needs: a one-to-two page CV (curriculum vitae, or resume) and a formal letter. You will use what you have already learned about fonts, alignment, spacing and lists. A tidy CV and letter can make a big difference when you apply for work, a scholarship or a place at a college.</p>
<h3>A simple CV layout</h3>
<p>Keep it plain: one font (Calibri or Arial, 11 points), dark text on white, and clear headings. Use these sections in this order:</p>
<ol>
<li><strong>Name</strong> at the top, large and bold, with phone number, email address and town below. Use a sensible email address based on your name.</li>
<li><strong>Personal profile</strong>: two or three sentences about who you are and what you want.</li>
<li><strong>Education</strong>: the most recent school or course first, with years.</li>
<li><strong>Work experience or volunteering</strong>: job title, place, dates, and bullet points of what you did.</li>
<li><strong>Skills</strong>: bullet list, for example computer skills, languages, driving.</li>
<li><strong>Referees</strong>: two people with their contacts, after asking their permission.</li>
</ol>
<h3>Building it in Word</h3>
<ol>
<li>Set margins to 2 cm and paper size to A4.</li>
<li>Type your name, select it, and set it to 20 points bold.</li>
<li>Type each section title (such as <code>EDUCATION</code>) and apply <strong>Heading 1</strong>; then modify Heading 1 to 13 points, bold, dark blue, with 6 pt space before.</li>
<li>Use bullets for duties and skills.</li>
<li>To align a date on the right of a line, use a <strong>tab</strong> (press Tab) and set a right tab stop on the ruler, rather than many spaces.</li>
<li>Do not put a photograph or ID number unless the employer asks.</li>
<li>Save as .docx, then also save a PDF to send.</li>
</ol>
<h3>The formal letter</h3>
<p>A formal letter has a standard order. In a block style, every part starts at the left margin.</p>
<pre>Your name
Your address / phone / email

Date: 21 October 2026

The Head Teacher
Name of the School
Gulu

Dear Sir or Madam,

SUBJECT: APPLICATION FOR A TEACHING ASSISTANT POST

First paragraph: why you are writing.
Second paragraph: your main qualities and experience.
Closing paragraph: thanks and what happens next.

Yours faithfully,

(space for signature)
Your name</pre>
<p>Use <strong>Yours sincerely</strong> when you know the person's name (Dear Mr Okello), and <strong>Yours faithfully</strong> when you start with Dear Sir or Madam. Write in short, polite paragraphs and keep it to one page. Put a blank line between paragraphs using paragraph spacing, not extra Enter presses.</p>
<h3>Common mistakes</h3>
<ul>
<li>Using many colours, pictures or fancy fonts on a CV.</li>
<li>Spelling mistakes in the employer's name. Check it twice.</li>
<li>Sending the same letter without changing the name of the organisation.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Create a new document and build your CV with the six sections above, using made-up details if you prefer.</li>
<li>Check that it fits on one page, or on two pages with no half-empty last page.</li>
<li>Create a second document and write a letter applying for a job or a training place.</li>
<li>Run the spelling check with F7.</li>
<li>Save both as .docx and as PDF.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A CV should be plain, accurate and easy to scan.</li>
<li>Use heading styles and bullet lists for consistent sections.</li>
<li>Use Yours faithfully with Dear Sir or Madam, and Yours sincerely with a name.</li>
<li>Proofread, then send a PDF.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Reports and proposals with a clear layout',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>This lesson puts layout, styles and headings together to produce a report or a project proposal. NGOs, schools and community groups often need these documents to apply for support. After the lesson you will be able to set up a report that is organised, consistent and easy to navigate.</p>
<h3>Planning the structure</h3>
<p>Before you type, decide the sections. A simple report can have:</p>
<ol>
<li><strong>Title page</strong>: the title, your name or group, the date.</li>
<li><strong>Table of contents</strong>.</li>
<li><strong>Introduction</strong>: what the report is about and why it matters.</li>
<li><strong>Main sections</strong>: each with a heading, for example Background, Activities, Results.</li>
<li><strong>Conclusion and recommendations</strong>.</li>
<li><strong>Appendices</strong>: extra material such as a budget table.</li>
</ol>
<p>A project proposal usually adds <em>Objectives</em>, <em>Activities and timeline</em>, <em>Budget</em> and <em>Expected results</em>.</p>
<h3>Setting up styles once</h3>
<p>Consistency is the sign of a professional document. Set up your styles at the start.</p>
<ol>
<li>Right-click <strong>Normal</strong> in the Styles gallery and choose <strong>Modify</strong>. Set the font to Calibri or Times New Roman, 12 points, and line spacing to 1.15 or 1.5. Click <strong>OK</strong>.</li>
<li>Modify <strong>Heading 1</strong>: 16 points, bold, dark colour.</li>
<li>Modify <strong>Heading 2</strong>: 13 points, bold.</li>
<li>Modify <strong>Title</strong> for the title page.</li>
</ol>
<p>Now apply styles as you type: Heading 1 for main sections, Heading 2 for sub-sections, Normal for paragraphs. Never make a heading by simply making text bigger, because Word will not recognise it for the table of contents or the Navigation Pane.</p>
<h3>The title page</h3>
<p>Type the title, apply the <strong>Title</strong> style, centre it, and add your name and the date below it. Click <strong>Insert</strong>, <strong>Page Break</strong> (or press Ctrl+Enter) so the next part starts on a new page. Alternatively use <strong>Insert</strong>, <strong>Cover Page</strong> for a ready design.</p>
<h3>Finishing touches</h3>
<ul>
<li>Insert page numbers in the footer, with <strong>Different First Page</strong> so the title page has none.</li>
<li>Add an automatic table of contents on page 2.</li>
<li>Use bullet lists for short points and tables for figures (Module 4).</li>
<li>Leave headings together with their text: right-click a heading style, choose Modify, Format, Paragraph, then <strong>Keep with next</strong>. This stops a heading from sitting alone at the bottom of a page.</li>
<li>Read the whole document in Read Mode before you print.</li>
</ul>
<h3>Common mistakes</h3>
<ul>
<li>Using different fonts or sizes in different sections by accident. Apply styles instead.</li>
<li>Putting a page number on the title page.</li>
<li>Forgetting to update the table of contents after editing.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Start a new document and modify the Normal, Heading 1 and Heading 2 styles as above.</li>
<li>Write a title page for a short report called <code>Report on a Community Clean-up</code>.</li>
<li>Add three Heading 1 sections with two or three sentences each, and one Heading 2 inside one section.</li>
<li>Insert an automatic table of contents and page numbers.</li>
<li>Switch to Read Mode and check that every page looks neat.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Plan the sections before you start writing.</li>
<li>Set the styles first and apply them as you type.</li>
<li>Only heading styles appear in the table of contents and Navigation Pane.</li>
<li>Check the title page, numbering and contents before printing.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which paper size is standard in Uganda?
- Letter
- Legal
* A4
- A0
= A4 (21 x 29.7 cm) is the normal size. Letter is American.

Q: Which is the best way to start a new page at an exact place?
- Press Enter until the cursor reaches the next page
* Press Ctrl+Enter to insert a page break
- Press Tab
- Use a very large font
= A page break stays in the right place even if you change the text above it.

Q: Where do you click to add page numbers?
- Home, then Font
- View, then Zoom
* Insert, then Page Number
- Review, then Comments
= Page numbers are inserted from the Insert tab, in the Header and Footer group.

Q: What must you do before Word can build an automatic table of contents?
- Use many colours
- Type the page numbers by hand
* Apply heading styles such as Heading 1 to your headings
- Use landscape orientation
= The table of contents is built from text that uses heading styles.

Q: You change a heading in a finished report. How do you refresh the table of contents?
- Delete it and retype it
* Click the table and choose Update Table
- Nothing: it changes itself instantly
- Change the margins
= Click the table and choose Update Table (or press F9) and update the entire table.

Q: What is the benefit of changing the Heading 1 style instead of formatting each heading by hand?
- It uses less ink
* Every Heading 1 in the document changes together and stays consistent
- It deletes the table of contents
- It makes the file smaller
= Styles allow one change to update all matching headings.

Q: A letter starts with "Dear Sir or Madam". How should it end?
- Yours sincerely
* Yours faithfully
- Love from
- Best wishes always
= Yours faithfully goes with Dear Sir or Madam; Yours sincerely goes with a named person.

Q: Which of these belong in a good CV? (choose all that apply)
* A clear heading and contact details
* Education and experience listed with dates
- Five different fonts and bright colours
* A short list of skills
= A CV should be plain and easy to scan. Too many fonts and colours make it harder to read.
QUIZ,
        ],
        [
            'title'   => 'Module 4: Tables, pictures and shapes',
            'summary' => 'Add tables, pictures, shapes, charts and links to make documents clear and attractive.',
            'lessons' => [
                [
                    'title'   => 'Inserting and formatting tables',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>You will learn to create a table, type in it, change its size, merge cells and add borders and shading. Tables keep information in neat rows and columns, such as a timetable, a price list or a budget, and they are much better than lining things up with spaces.</p>
<h3>What is in a table?</h3>
<p>A table is a grid. A <strong>row</strong> goes across, a <strong>column</strong> goes down, and the box where they meet is a <strong>cell</strong>.</p>
<h3>Inserting a table</h3>
<ol>
<li>Click where the table should appear.</li>
<li>Click the <strong>Insert</strong> tab, then <strong>Table</strong>.</li>
<li>Move your mouse over the grid to choose the number of columns and rows, then click. Or choose <strong>Insert Table</strong> and type the numbers.</li>
</ol>
<p>Type in a cell and press <strong>Tab</strong> to move to the next one. Tab in the last cell adds a new row. <strong>Shift+Tab</strong> goes back.</p>
<h3>Selecting and changing</h3>
<ul>
<li>Move over the left edge of a row until the arrow points right, then click to select the row.</li>
<li>Move over the top edge of a column until the arrow points down, then click to select the column.</li>
<li>Click the small square at the top left of the table to select all.</li>
<li>To add rows or columns, right-click a cell, choose <strong>Insert</strong> and pick where. To remove, right-click and choose <strong>Delete Cells</strong> (or Delete Rows / Columns).</li>
<li>Drag the lines between columns to change their widths, or use <strong>Layout</strong> then <strong>AutoFit</strong>.</li>
</ul>
<h3>The two table tabs</h3>
<p>When you click inside a table, two tabs appear: <strong>Table Design</strong> and <strong>Layout</strong>.</p>
<table>
<thead><tr><th>Tab</th><th>Use it to</th></tr></thead>
<tbody>
<tr><td>Table Design</td><td>Choose a Table Style, add shading (colour in cells), and change borders (lines).</td></tr>
<tr><td>Layout</td><td>Insert or delete rows and columns, <strong>Merge Cells</strong>, <strong>Split Cells</strong>, change alignment and cell size.</td></tr>
</tbody>
</table>
<h3>Merge and split</h3>
<p>To make a title that spans the whole table, select the cells in the top row, then choose <strong>Layout</strong>, <strong>Merge Cells</strong>. To divide one cell into several, choose <strong>Split Cells</strong> and type how many columns and rows you want.</p>
<h3>Borders and shading</h3>
<ol>
<li>Select the cells.</li>
<li>On <strong>Table Design</strong>, click <strong>Borders</strong> and choose All Borders, No Border, Outside Borders, and so on.</li>
<li>Click <strong>Shading</strong> and pick a light colour for the header row.</li>
</ol>
<p>Use light shading and dark text. Make the header row <strong>bold</strong>. For a school timetable, centre the text in the cells using the alignment buttons in the Layout tab.</p>
<h3>Common mistakes</h3>
<ul>
<li>Using Tab characters or spaces to build a table.</li>
<li>Making a table that is wider than the page. Use <strong>AutoFit to Window</strong>.</li>
<li>Choosing dark shading with dark text.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Insert a table with 4 columns and 5 rows.</li>
<li>Type the headings <code>Item</code>, <code>Quantity</code>, <code>Price (UGX)</code>, <code>Total (UGX)</code> in the first row and fill four rows of shop items.</li>
<li>Make the first row bold with light grey shading.</li>
<li>Insert a new row at the bottom and type <code>Grand total</code>.</li>
<li>Merge the first three cells of that last row.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Use Insert, Table to create a grid of rows and columns.</li>
<li>Tab moves from cell to cell.</li>
<li>Table Design controls style, borders and shading; Layout controls rows, columns and merging.</li>
<li>Keep tables simple, readable and within the margins.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Pictures, wrapping and captions',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>In this lesson you will add pictures to a document, place them where you want, and give them captions. A good picture can make a flyer, newsletter or report much clearer, but only when it is placed and sized properly.</p>
<h3>Inserting a picture</h3>
<ol>
<li>Click where the picture should go.</li>
<li>Click <strong>Insert</strong>, then <strong>Pictures</strong>, then <strong>This Device</strong>.</li>
<li>Find the picture (for example in your Pictures folder), select it and click <strong>Insert</strong>.</li>
</ol>
<p>You can also drag a picture from a folder onto the page, or copy and paste. Choose <strong>Online Pictures</strong> or <strong>Stock Images</strong> only if you are sure you may use them. Pictures you find on the internet belong to someone, so use your own photos or ones that are clearly free to use.</p>
<h3>Resizing and cropping</h3>
<ul>
<li>Click the picture. Round handles appear at the corners and sides.</li>
<li>Drag a <strong>corner</strong> handle to resize. This keeps the proportions, so the picture does not look stretched.</li>
<li>Do not drag the side handles unless you want to squash or stretch it.</li>
<li>To remove edges, click <strong>Picture Format</strong>, then <strong>Crop</strong>, and drag the black edges.</li>
</ul>
<p>Very large photos from a phone can make the file huge. Resize them in Word, and when you email the file, choose <strong>Picture Format</strong>, <strong>Compress Pictures</strong> to make it smaller.</p>
<h3>Wrap text</h3>
<p>By default a picture sits <strong>in line with text</strong>, like a big letter. To place it beside text, click the picture, then the small <strong>Layout Options</strong> icon at its top right (or <strong>Picture Format</strong>, then <strong>Wrap Text</strong>).</p>
<table>
<thead><tr><th>Option</th><th>Result</th></tr></thead>
<tbody>
<tr><td>In Line with Text</td><td>The picture behaves like a character in a line.</td></tr>
<tr><td>Square</td><td>Text wraps around the picture in a square shape.</td></tr>
<tr><td>Tight</td><td>Text follows the outline of the picture.</td></tr>
<tr><td>Top and Bottom</td><td>Text stays above and below only.</td></tr>
<tr><td>Behind Text / In Front of Text</td><td>The picture overlaps the text, useful for logos and watermarks.</td></tr>
</tbody>
</table>
<p>After you choose Square, you can drag the picture to any place on the page.</p>
<h3>Styles, effects and alt text</h3>
<p>On the <strong>Picture Format</strong> tab you can apply a border, rounded corners or a shadow in <strong>Picture Styles</strong>, and fix brightness and contrast in <strong>Corrections</strong>. Keep it modest. Also right-click the picture, choose <strong>View Alt Text</strong>, and type a short description. Screen readers say this to blind users.</p>
<h3>Captions</h3>
<p>A caption is a short label under a picture. Right-click the picture and choose <strong>Insert Caption</strong>, or go to <strong>References</strong>, then <strong>Insert Caption</strong>. Word adds the label <em>Figure 1</em> and numbers it automatically. Type your description after it, for example <code>Figure 1: Volunteers planting trees in Gulu</code>.</p>
<h3>Try it yourself</h3>
<ol>
<li>Type a short paragraph of about six lines.</li>
<li>Insert a photo from your computer and resize it by dragging a corner.</li>
<li>Set Wrap Text to Square and drag the picture to the right side of the paragraph.</li>
<li>Crop one side of the picture.</li>
<li>Add a caption and a short alt text.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Insert pictures from the Insert tab, and resize with a corner handle.</li>
<li>Choose a Wrap Text option to place a picture beside text.</li>
<li>Use only pictures that you have the right to use.</li>
<li>Add captions and alt text to make documents clearer and more accessible.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Shapes, text boxes and SmartArt',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>This lesson shows you how to draw shapes, add text boxes and make simple diagrams with SmartArt. They help you build posters, notices, certificates, flow charts and organisation charts without any other software.</p>
<h3>Shapes</h3>
<ol>
<li>Click <strong>Insert</strong>, then <strong>Shapes</strong>.</li>
<li>Pick a shape such as a rectangle, circle, arrow or star.</li>
<li>Click and drag on the page to draw it. Hold <strong>Shift</strong> while dragging to make a perfect square or circle.</li>
</ol>
<p>When the shape is selected, the <strong>Shape Format</strong> tab appears. There you can change <strong>Shape Fill</strong> (inside colour), <strong>Shape Outline</strong> (the line), and <strong>Shape Effects</strong> (shadow and glow). You can type text in a shape by clicking it and typing. Drag the round handle at the top to rotate it. Choose <strong>Wrap Text</strong> to control how it sits among the text, just as with pictures.</p>
<h3>Text boxes</h3>
<p>A <strong>text box</strong> is a box you can place anywhere on the page, independent of the main text. It is useful for a quote, a notice or a sidebar.</p>
<ol>
<li>Click <strong>Insert</strong>, then <strong>Text Box</strong>.</li>
<li>Choose a built-in design or <strong>Draw Text Box</strong> and drag on the page.</li>
<li>Type your words and format them as usual.</li>
<li>Use <strong>Shape Fill</strong> and <strong>Shape Outline</strong> to change the look. Choose <strong>No Outline</strong> for a clean look.</li>
</ol>
<h3>Arranging objects</h3>
<ul>
<li>Hold <strong>Shift</strong> and click several shapes to select them, then use <strong>Align</strong> (Shape Format tab) to line them up neatly.</li>
<li><strong>Group</strong> (right-click, then Group) joins shapes so they move together.</li>
<li><strong>Bring Forward</strong> and <strong>Send Backward</strong> change which one is on top.</li>
</ul>
<h3>SmartArt</h3>
<p><strong>SmartArt</strong> is a ready-made diagram where you only type the text. Click <strong>Insert</strong>, then <strong>SmartArt</strong>. The categories include <strong>List</strong>, <strong>Process</strong> (steps), <strong>Cycle</strong>, <strong>Hierarchy</strong> (organisation chart) and <strong>Relationship</strong>.</p>
<ol>
<li>Choose a diagram, for example <strong>Basic Process</strong>, and click <strong>OK</strong>.</li>
<li>Click <strong>[Text]</strong> in each shape and type, or use the text pane on the left.</li>
<li>Press Enter in the text pane to add a new shape, or Tab to make a sub-item.</li>
<li>Use the <strong>SmartArt Design</strong> tab to change colours and styles.</li>
</ol>
<p>Use SmartArt for simple ideas with a few words per shape, such as the steps of registering a business, or the structure of a youth club (chairperson, secretary, treasurer). Too much text makes it unreadable.</p>
<h3>Common mistakes</h3>
<ul>
<li>Filling the page with shapes of many bright colours.</li>
<li>Typing long paragraphs into SmartArt.</li>
<li>Forgetting to group the pieces of a diagram, so they scatter when you move one.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Draw a rectangle, fill it with light blue, and type <code>Welcome to the Youth Club</code> inside.</li>
<li>Insert a text box with the opening hours and remove its outline.</li>
<li>Insert a Basic Process SmartArt with three steps: <em>Register</em>, <em>Learn</em>, <em>Certificate</em>.</li>
<li>Change the SmartArt colours from the SmartArt Design tab.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Shapes and text boxes come from the Insert tab; edit them in Shape Format.</li>
<li>Hold Shift to draw perfect squares and circles.</li>
<li>Align and group objects to keep them neat.</li>
<li>SmartArt turns a short list into a diagram quickly; keep text short.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Charts, hyperlinks and cross-references',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>In this lesson you will add a chart that shows numbers at a glance, create links that open web pages or email addresses, and use bookmarks and captions to guide readers. After it you can present figures and information in a way that readers can understand quickly.</p>
<h3>Charts</h3>
<p>A <strong>chart</strong> shows numbers as a picture. A column chart compares amounts, a line chart shows change over time, and a pie chart shows parts of a whole.</p>
<ol>
<li>Click <strong>Insert</strong>, then <strong>Chart</strong>.</li>
<li>Choose a type, for example <strong>Column</strong>, and click <strong>OK</strong>.</li>
<li>A small spreadsheet window opens. Replace the sample text and numbers with yours. For example: Q1, Q2, Q3 and the amount of maize sold.</li>
<li>Close the spreadsheet window. The chart updates.</li>
</ol>
<p>Click the chart and use the <strong>Chart Design</strong> tab to change the style and colours, or the small <strong>+</strong> button beside it to add or remove the title, axis labels and legend. To change the numbers later, click <strong>Chart Design</strong>, then <strong>Edit Data</strong>.</p>
<p>Make charts easy to read: give them a clear title, label the axes with units such as UGX or number of people, avoid 3D effects, and use few colours. Use a pie chart only for a small number of parts that add up to a whole.</p>
<h3>Hyperlinks</h3>
<p>A <strong>hyperlink</strong> is text or a picture that opens something when clicked (Ctrl+click in Word).</p>
<ol>
<li>Select the text that will be the link.</li>
<li>Press <strong>Ctrl+K</strong> (Mac: Cmd+K), or choose <strong>Insert</strong>, then <strong>Link</strong>.</li>
<li>For a web page, type or paste the address in <strong>Address</strong>.</li>
<li>For an email, choose <strong>E-mail Address</strong> on the left and type the address.</li>
<li>Click <strong>OK</strong>.</li>
</ol>
<p>Word often turns web and email addresses into links as you type. To remove one, right-click and choose <strong>Remove Hyperlink</strong>. Use meaningful link text such as <em>Read the registration guide</em> instead of a long address. A printed document cannot be clicked, so print the full address if readers need it.</p>
<h3>Bookmarks and internal links</h3>
<p>You can also link to another part of the same document. Select the heading, choose <strong>Insert</strong>, then <strong>Bookmark</strong>, and give it a name. Then select other text, press Ctrl+K, choose <strong>Place in This Document</strong> and pick the heading or bookmark.</p>
<h3>Cross-references</h3>
<p>If your report says <em>see Figure 2</em>, you can let Word insert that number so it updates when figures move. Choose <strong>References</strong>, then <strong>Cross-reference</strong>, pick <strong>Figure</strong> as the type, and choose the caption. Tables and charts can also have captions through <strong>Insert Caption</strong>.</p>
<h3>Try it yourself</h3>
<ol>
<li>Insert a column chart showing the number of learners in three classes of your choice.</li>
<li>Edit the data so the title reads <code>Learners per class</code>.</li>
<li>Add a caption <code>Figure 1</code> under the chart.</li>
<li>Type the sentence <code>Visit the Microsoft Support website</code>, select the last three words and link them to <code>https://support.microsoft.com</code>.</li>
<li>Press Ctrl+click on the link to test it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Insert, then Chart creates a chart; edit its numbers with Edit Data.</li>
<li>Choose the chart type that suits your message and label it clearly.</li>
<li>Ctrl+K creates a hyperlink to a web page, email or another place in the document.</li>
<li>Captions and cross-references keep figure numbers correct.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: In a Word table, what is a cell?
- A whole row
- A whole column
* The box where a row and a column meet
- The border around the table
= A cell is a single box in the grid.

Q: What does the Tab key do inside a table?
- Inserts a picture
* Moves to the next cell
- Deletes the row
- Closes the table
= Tab moves to the next cell. In the very last cell it adds a new row.

Q: You want one heading to stretch across the top of four columns. What should you use?
- Split Cells
* Merge Cells
- AutoFit
- Wrap Text
= Merge Cells joins several cells into one.

Q: When you resize a picture, which handles keep it from looking stretched?
- The side handles
* The corner handles
- The top handle only
- No handles
= Dragging a corner keeps the proportions.

Q: Which Wrap Text option lets words flow around a picture on the page?
- In Line with Text
* Square
- Delete
- Crop
= Square (or Tight) wraps text around the picture, which can then be dragged.

Q: Which feature makes a ready-made process diagram from a short list?
- Format Painter
- Mail Merge
* SmartArt
- Spelling check
= SmartArt provides ready diagrams such as process, cycle and hierarchy.

Q: Which chart type is best for comparing the amount of maize sold in three quarters?
* Column chart
- Pie chart with 20 slices
- 3D bubble chart
- No chart is possible
= A simple column chart makes comparing amounts easy.

Q: What is the shortcut for inserting a hyperlink?
- Ctrl+H
* Ctrl+K
- Ctrl+L
- Ctrl+P
= Ctrl+K opens the Insert Hyperlink box (Cmd+K on a Mac).
QUIZ,
        ],
        [
            'title'   => 'Module 5: Review, share and print',
            'summary' => 'Review work with comments and Track Changes, protect, merge, print, export and share.',
            'lessons' => [
                [
                    'title'   => 'Comments, Track Changes and comparing documents',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>You will learn how to give and receive feedback in Word: leaving comments, tracking edits so that every change can be accepted or rejected, and comparing two versions. This is how teams, supervisors and teachers improve a document without losing the original text.</p>
<h3>Comments</h3>
<p>A <strong>comment</strong> is a note attached to a piece of text. It does not change the document.</p>
<ol>
<li>Select the word or sentence.</li>
<li>Click <strong>Review</strong>, then <strong>New Comment</strong> (shortcut Ctrl+Alt+M; Mac: Cmd+Option+A).</li>
<li>Type your note in the box that appears at the side.</li>
</ol>
<p>Click <strong>Reply</strong> inside a comment to answer it, and <strong>Resolve</strong> when the matter is finished. To delete a comment, right-click it and choose <strong>Delete Comment</strong>.</p>
<h3>Track Changes</h3>
<p>When <strong>Track Changes</strong> is on, Word records every addition, deletion and format change, shows it in colour, and keeps the original available.</p>
<ol>
<li>Click <strong>Review</strong>, then <strong>Track Changes</strong> (Ctrl+Shift+E; Mac: Cmd+Shift+E).</li>
<li>Edit the document as normal. Deleted words appear crossed out, new words underlined, and a line in the margin marks each change.</li>
<li>Click <strong>Track Changes</strong> again to switch off.</li>
</ol>
<h3>Accepting and rejecting</h3>
<p>The author goes through the changes one by one.</p>
<ol>
<li>Click at the start of the document and click <strong>Review</strong>, then <strong>Next</strong> under Changes.</li>
<li>Click <strong>Accept</strong> to keep the change, or <strong>Reject</strong> to throw it away.</li>
<li>Continue until Word says there are no more changes. Or use <strong>Accept All</strong> or <strong>Reject All</strong> when sure.</li>
</ol>
<p>The drop-down box in the Tracking group changes what you see: <strong>Simple Markup</strong> (clean, with a red line), <strong>All Markup</strong> (every change shown), <strong>No Markup</strong> and <strong>Original</strong>.</p>
<table>
<thead><tr><th>Display setting</th><th>Shows</th></tr></thead>
<tbody>
<tr><td>Simple Markup</td><td>Final text with a line for each change</td></tr>
<tr><td>All Markup</td><td>Every insertion, deletion and comment</td></tr>
<tr><td>No Markup</td><td>The final text as if all changes were accepted</td></tr>
<tr><td>Original</td><td>The text before any changes</td></tr>
</tbody>
</table>
<h3>Compare documents</h3>
<p>If someone sent you a copy without Track Changes, you can find the differences. Click <strong>Review</strong>, then <strong>Compare</strong>, then <strong>Compare...</strong>. Choose the <strong>Original document</strong> and the <strong>Revised document</strong> and click <strong>OK</strong>. Word creates a new document that shows what changed.</p>
<h3>Good habits</h3>
<ul>
<li>Save a copy of the original before review starts.</li>
<li>Use your real name in Word options, so changes show who made them (File, Options, General).</li>
<li>Remove all comments and accept or reject all changes before sending a final document to the public. Hidden changes can reveal things you did not mean to share.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Open a document with a paragraph of text and save it as <code>Review practice</code>.</li>
<li>Turn on Track Changes and change two words and delete a sentence.</li>
<li>Add a comment on one word asking a question.</li>
<li>Go through the changes: accept one and reject one.</li>
<li>Reply to your comment and resolve it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Comments are notes; Track Changes records every edit.</li>
<li>Accept keeps a change; Reject removes it.</li>
<li>Compare shows the differences between two versions.</li>
<li>Clear all markup before you send a final version.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Protecting documents and mail merge basics',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn to protect a document with a password or restrictions, and to use Mail Merge to send the same letter to many people with a different name on each. Both save time and help you protect important information.</p>
<h3>Protecting a document</h3>
<p>You can control who can open or change a file.</p>
<ol>
<li>Click <strong>File</strong>, then <strong>Info</strong>.</li>
<li>Click <strong>Protect Document</strong>.</li>
<li>Choose an option:</li>
</ol>
<table>
<thead><tr><th>Option</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Mark as Final</td><td>Tells readers the document is finished. It is only a warning; anyone can still edit.</td></tr>
<tr><td>Encrypt with Password</td><td>Asks for a password to open the file. If you forget it, Microsoft cannot recover it for you.</td></tr>
<tr><td>Restrict Editing</td><td>Allows only certain changes, such as comments or form filling.</td></tr>
</tbody>
</table>
<p>Write the password in a safe place, and make it strong and not the same as other passwords. For a document that people only need to read, save a PDF instead and share that.</p>
<h3>Mail merge: the idea</h3>
<p>Suppose you are the secretary of a savings group and must invite 40 members to a meeting. You do not want to type 40 letters. With <strong>Mail Merge</strong> you write the letter once and Word fills in each member's name from a list. You need two things: the <strong>main document</strong> (the letter) and the <strong>data source</strong> (a list of names, often in Excel or a Word table).</p>
<h3>Preparing the list</h3>
<p>Make a table with one header row and one row per person. The header names are the fields: <code>Title</code>, <code>FirstName</code>, <code>LastName</code>, <code>Village</code>. Save the file and close it.</p>
<h3>Running the merge</h3>
<ol>
<li>Open a new document and type the letter. Leave the personal parts for Word to fill.</li>
<li>Click the <strong>Mailings</strong> tab, then <strong>Start Mail Merge</strong>, then <strong>Letters</strong>.</li>
<li>Click <strong>Select Recipients</strong>, then <strong>Use an Existing List</strong>, and choose your file.</li>
<li>Click where the name should go. Click <strong>Insert Merge Field</strong> and pick <code>FirstName</code>. It appears as &laquo;FirstName&raquo;.</li>
<li>Insert other fields in the same way, for example the greeting: <code>Dear</code> then <code>Title</code> <code>LastName</code>.</li>
<li>Click <strong>Preview Results</strong> and use the arrows to see each letter.</li>
<li>Click <strong>Finish &amp; Merge</strong>, then <strong>Edit Individual Documents</strong>, and choose All. Word makes a new document with one letter per person, ready to print.</li>
</ol>
<h3>Common mistakes</h3>
<ul>
<li>Typing names by hand instead of inserting fields.</li>
<li>Leaving blank lines or typing errors in the list.</li>
<li>Forgetting to preview before printing 40 pages.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Make a table with columns <code>FirstName</code> and <code>Village</code> and four rows of made-up names; save it as <code>Members</code>.</li>
<li>In a new document write a short invitation beginning <code>Dear</code> and add the FirstName field.</li>
<li>Add a line <code>We will meet at the village hall in </code> followed by the Village field.</li>
<li>Preview the four letters and finish the merge with Edit Individual Documents.</li>
<li>Use Protect Document to encrypt a different practice file with a password you can remember.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Protect Document options are in File, Info.</li>
<li>Mark as Final is only a warning; a password encrypts the file.</li>
<li>Mail Merge combines one letter with a list of recipients.</li>
<li>Always use Preview Results before finishing the merge.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Print preview, printing and saving as PDF',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to preview and print a document with the correct settings, and save it as a PDF. Many people waste paper and printing money because they print without checking first. A preview shows problems before they cost you.</p>
<h3>Print preview</h3>
<ol>
<li>Click <strong>File</strong>, then <strong>Print</strong> (shortcut <strong>Ctrl+P</strong>; Mac: Cmd+P).</li>
<li>The page on the right shows exactly how the document will print.</li>
<li>Use the arrows at the bottom to look through each page, and the zoom slider to look closer.</li>
</ol>
<p>Check that no heading is alone at the bottom of a page, that nothing is cut off, that the last page is not nearly empty, and that the page numbers are correct. Fix problems, then return to Print.</p>
<h3>Print settings</h3>
<table>
<thead><tr><th>Setting</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>Copies</td><td>How many sets to print</td></tr>
<tr><td>Printer</td><td>Which printer to use. Check the right one is selected.</td></tr>
<tr><td>Print All Pages</td><td>Change to the current page or a custom range such as <code>1-3, 5</code></td></tr>
<tr><td>Print One Sided / Both Sides</td><td>Two-sided printing saves paper if your printer supports it</td></tr>
<tr><td>Collated</td><td>Prints each full copy in order (1,2,3 then 1,2,3)</td></tr>
<tr><td>Portrait / Landscape</td><td>The paper direction</td></tr>
<tr><td>A4</td><td>The paper size. It must match the paper in the printer.</td></tr>
<tr><td>Margins</td><td>Normal, Narrow and so on</td></tr>
</tbody>
</table>
<p>When ready, click the large <strong>Print</strong> button. If nothing prints, check that the printer is on, connected and has paper and ink, and that the correct printer is chosen.</p>
<h3>Saving as PDF</h3>
<p>A PDF is the best way to share a final document, because it looks the same on any device and cannot easily be changed.</p>
<ol>
<li>Click <strong>File</strong>, then <strong>Save As</strong> (or <strong>Export</strong>).</li>
<li>Choose <strong>PDF (*.pdf)</strong> in <strong>Save as type</strong>, or choose <strong>Create PDF/XPS Document</strong> under Export.</li>
<li>Click <strong>Options</strong> if you want only some pages.</li>
<li>Choose a name and click <strong>Save</strong> (or <strong>Publish</strong>).</li>
</ol>
<p>A PDF can also be read on a phone without Word. Choose <strong>Minimum size</strong> instead of Standard when you plan to send it by email or WhatsApp on limited data.</p>
<p>You can also choose <strong>Microsoft Print to PDF</strong> as the printer. This is useful when you need a PDF from any program.</p>
<h3>Saving paper and ink</h3>
<ul>
<li>Preview first, always.</li>
<li>Print only the pages you need.</li>
<li>Use two-sided printing and draft quality for drafts.</li>
<li>Avoid solid dark backgrounds.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Open your report or CV from earlier lessons.</li>
<li>Press Ctrl+P and look through every page in the preview.</li>
<li>Change the print range to page 1 only.</li>
<li>Choose Microsoft Print to PDF as the printer (or use Save As PDF) and save the result.</li>
<li>Open the PDF and check it looks the same.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Ctrl+P opens print settings and a live preview.</li>
<li>Check the printer, paper size, page range and copies before pressing Print.</li>
<li>Send finished documents as PDF.</li>
<li>Previewing saves paper, ink and money.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Sharing, shortcuts and a final document checklist',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>To finish the course, you will learn how to share a Word document and work on it with others, use the most helpful keyboard shortcuts, and run a checklist before you send any document. Working together online is now common in schools, offices and organisations of all sizes.</p>
<h3>Sharing from Word</h3>
<p>If a document is saved in <strong>OneDrive</strong> or SharePoint, you can share it directly.</p>
<ol>
<li>Click <strong>Share</strong> at the top right of the window.</li>
<li>Type the email address of the person.</li>
<li>Choose <strong>Can edit</strong> (they can change the document) or <strong>Can view</strong> (they can only read).</li>
<li>Click <strong>Send</strong>. The person gets an email with a link.</li>
</ol>
<p>You can also choose <strong>Copy link</strong> and send it by WhatsApp or email. Be careful about who has the link: choose people you trust, and avoid giving edit access to anyone with the link.</p>
<p>Other options are to attach the file to an email, or send a PDF copy. Choose the PDF when you do not want anyone to change the content.</p>
<h3>Co-authoring</h3>
<p>When two or more people open the same shared document at the same time, you can edit together. Coloured flags show where others are typing and the AutoSave switch keeps everyone's work saved. Use comments to discuss, and avoid editing the same sentence at the same time. This works in Word on a computer and in Word for the web.</p>
<h3>Useful shortcuts</h3>
<table>
<thead><tr><th>Task</th><th>Windows</th><th>Mac</th></tr></thead>
<tbody>
<tr><td>Save</td><td>Ctrl+S</td><td>Cmd+S</td></tr>
<tr><td>Open / New</td><td>Ctrl+O / Ctrl+N</td><td>Cmd+O / Cmd+N</td></tr>
<tr><td>Print</td><td>Ctrl+P</td><td>Cmd+P</td></tr>
<tr><td>Cut / Copy / Paste</td><td>Ctrl+X / C / V</td><td>Cmd+X / C / V</td></tr>
<tr><td>Undo / Redo</td><td>Ctrl+Z / Ctrl+Y</td><td>Cmd+Z / Cmd+Y</td></tr>
<tr><td>Select all</td><td>Ctrl+A</td><td>Cmd+A</td></tr>
<tr><td>Bold / Italic / Underline</td><td>Ctrl+B / I / U</td><td>Cmd+B / I / U</td></tr>
<tr><td>Find / Replace</td><td>Ctrl+F / Ctrl+H</td><td>Cmd+F / Ctrl+H</td></tr>
<tr><td>Page break</td><td>Ctrl+Enter</td><td>Cmd+Enter</td></tr>
<tr><td>Spelling check</td><td>F7</td><td>Option+F7</td></tr>
</tbody>
</table>
<h3>Final document checklist</h3>
<ol>
<li>Is the file name clear and the document saved?</li>
<li>Is the spelling checked (F7) and have you read it slowly yourself?</li>
<li>Are the fonts, sizes and colours consistent, using styles?</li>
<li>Are the margins, A4 size and orientation correct?</li>
<li>Do headings, page numbers, headers and the table of contents look right?</li>
<li>Are pictures, tables and charts neat, labelled and inside the margins?</li>
<li>Have all comments and tracked changes been dealt with?</li>
<li>Have you checked names, dates, amounts and phone numbers?</li>
<li>Have you looked at the print preview?</li>
<li>Are you sending the right format (PDF for final, docx for editing)?</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Take your best document from this course and run the checklist above, fixing anything you find.</li>
<li>Save a PDF copy.</li>
<li>If you have a OneDrive account, upload the file and use Share to send it to a friend with Can view permission.</li>
<li>Write down five shortcuts from the table that you did not use before and practise them for a week.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Share a document from OneDrive with Can edit or Can view permission.</li>
<li>Co-authoring lets several people work in the same file at once.</li>
<li>Shortcuts make you faster; learn a few at a time.</li>
<li>Always use a checklist before sending a final document.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does Track Changes do?
- Prints the document faster
* Records every edit so each one can be accepted or rejected
- Locks the document with a password
- Checks the spelling
= Track Changes marks insertions, deletions and format changes without losing the original text.

Q: A reviewer's change is wrong. What should you do?
- Accept it
* Reject it
- Resolve the comment
- Delete the whole document
= Rejecting removes the change and restores the original text.

Q: Which Protect Document option actually requires a password to open the file?
- Mark as Final
* Encrypt with Password
- Restrict Editing
- Compare
= Encrypting with a password locks the file. Mark as Final is only a warning.

Q: In Mail Merge, what is the data source?
- The letter itself
* The list of recipients with their details
- The printer
- The table of contents
= The data source is the list (for example names and villages) whose details are inserted into the letter.

Q: What should you do before clicking Finish & Merge?
- Close Word
* Use Preview Results to check several letters
- Delete the list
- Change the paper size to Letter
= Previewing catches errors before you print many copies.

Q: You want to print only pages 2 to 4. What do you do?
- Print the whole document and tear out the pages
* In the print settings, type 2-4 as the page range
- Change the font size
- Use Compare
= The custom page range box lets you print only the pages you need.

Q: Which is the best format to send a finished CV by email so nobody can change it accidentally?
- .docx
* .pdf
- .dotx
- .txt
= PDF keeps the layout fixed and is harder to change.

Q: When sharing a document from OneDrive with a colleague who should only read it, which permission is correct?
- Can edit
* Can view
- Delete
- Full control for anyone
= Can view lets the person read without changing the content.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What is the name of the wide strip of buttons at the top of the Word window?
- Status bar
- Navigation Pane
* Ribbon
- Ruler
= The ribbon holds tabs and groups of commands.

Q: Which command creates a new copy of a file under a different name or format?
- Save
* Save As
- Print
- Undo
= Save As makes a new file and leaves the original untouched.

Q: When does AutoSave work?
- On any document saved on the desktop
* When the file is stored in OneDrive or SharePoint
- Only after printing
- Only when Track Changes is on
= AutoSave needs a cloud location.

Q: Which view shows pages as they will print?
- Outline
- Draft
* Print Layout
- Read Mode
= Print Layout shows margins and page edges.

Q: What is the shortcut to select all the text?
- Ctrl+S
* Ctrl+A
- Ctrl+F
- Ctrl+Z
= Ctrl+A selects everything (Cmd+A on Mac).

Q: Where can you find a ready-made starting layout for a CV?
- Review, Language
* File, New, templates
- View, Zoom
- Insert, Header
= New documents can start from templates such as CVs and letters.

Q: What does a red wavy line under a word usually mean?
- A grammar problem
* A possible spelling mistake
- A comment
- A hyperlink
= Red lines mark spelling; blue lines mark grammar or style.

Q: Why should you not press Enter at the end of each line in a paragraph?
- It uses more ink
* Word wraps the line automatically and extra paragraph breaks cause formatting problems
- It deletes the text
- It switches off spelling check
= Press Enter only to start a new paragraph.

Q: How do you make a new paragraph look different from the last without leaving blank lines?
- Press Enter twice
* Use paragraph spacing before or after
- Use many spaces
- Change the paper size
= Paragraph spacing is cleaner than empty lines.

Q: Which are good choices for the body text of a formal document? (choose all that apply)
* A clear font such as Calibri or Arial
* A size of about 11 or 12 points
- Several decorative fonts
* Dark text on a light background
= Plain fonts and strong contrast keep documents readable.

Q: Which is the best way to indent the first line of a paragraph?
- Press the space bar five times
* Use the paragraph indent settings or Tab
- Change the paper to landscape
- Insert a table
= Spaces do not align reliably. Use indents or tabs.

Q: What does Format Painter do?
- Prints in colour
* Copies formatting from one piece of text to another
- Finds spelling mistakes
- Inserts pictures
= Format Painter copies the look, not the words.

Q: You want to change every mention of "Primary 5" to "Primary 6". Which tool is the quickest?
- Spell check
* Find and Replace (Ctrl+H)
- Format Painter
- Zoom
= Replace All changes every match at once; check the results afterwards.

Q: Which paper size should you normally use in Uganda?
- Legal
- Letter
* A4
- A3 only
= A4 is the standard office paper size.

Q: Which feature should you use to start a new chapter on a new page?
- Several Enter presses
* Page break (Ctrl+Enter)
- Space bar
- A larger font
= A page break keeps the structure stable when you edit.

Q: What must be true before an automatic table of contents can be created?
- The document must have pictures
* Headings must use heading styles
- The document must be in landscape
- It must be saved as PDF
= The table of contents reads the heading styles.

Q: How can you stop the page number appearing on a cover page?
- Delete the whole footer from every page
* Tick Different First Page in the header and footer options
- Use a smaller font
- Change the language
= The first page can have its own header and footer.

Q: A formal letter begins "Dear Mrs Akello". How should it end?
* Yours sincerely
- Yours faithfully
- Cheers
- With love
= Use Yours sincerely with a named person.

Q: In a Word table, which tab contains Merge Cells and Split Cells?
- Home
- Insert
* Layout (under Table Tools)
- References
= The Layout tab for tables holds merge and split.

Q: Which Wrap Text option lets text flow around a picture?
- In Line with Text
* Square
- Compress
- Caption
= Square or Tight wraps words around the picture.

Q: What is SmartArt used for?
- Checking grammar
* Creating ready-made diagrams such as processes and organisation charts
- Printing double-sided
- Merging letters
= SmartArt turns short text into diagrams.

Q: How do you insert a hyperlink quickly?
- Ctrl+P
- Ctrl+W
* Ctrl+K
- F7
= Ctrl+K opens the Insert Hyperlink box. (Cmd+K on Mac.)

Q: What does Reject do in a Track Changes review?
- Accepts the edit
* Removes the change and restores the original text
- Adds a comment
- Saves the document
= Reject undoes the tracked edit.

Q: Which protection actually stops people opening a file without a password?
- Mark as Final
* Encrypt with Password
- Page borders
- Compare
= Only encryption requires a password to open the file.

Q: What do you need to run a Mail Merge for 40 invitations? (choose all that apply)
* A main letter document
* A list of recipients with fields such as names
- A watermark
* The Mailings tab
= Mail merge uses a letter and a data list, controlled from the Mailings tab.

Q: What is the shortcut that opens the print settings and preview?
- Ctrl+O
- Ctrl+N
- Ctrl+B
* Ctrl+P
= Ctrl+P opens Print with a preview (Cmd+P on Mac).

Q: When sending a document by email and you want to be sure the layout will not change on the other person's phone, what should you send?
- A .docx
* A PDF
- A .dotx template
- A .txt file
= A PDF looks the same on any device.

Q: Why should you check names, dates and amounts yourself even after the spelling check?
- Word never checks anything
* Word cannot tell when a correct word or number is wrong for your meaning
- Word deletes them
- It is a rule of Microsoft
= Spell check cannot catch wrong numbers or the wrong correct word.
QUIZ,
];
