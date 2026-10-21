<?php
return [
    'slug'        => 'google-workspace',
    'title'       => 'Google Workspace: Docs, Sheets, Slides, Drive and Meet',
    'category'    => 'office',
    'level'       => 'beginner',
    'icon'        => 'globe',
    'sort'        => 80,
    'summary'     => 'Create, share and work together on documents, spreadsheets, presentations, forms and video meetings with Google\'s free online tools.',
    'description' => '<p>Google Workspace is a set of online tools for writing, calculating, presenting, storing files and meeting. Because everything is stored online, you can start a document on a phone, finish it on a computer, and let your team work in the same file at the same time.</p><p>This course teaches the tools most used in schools, offices, NGOs and small businesses: Drive, Docs, Sheets, Slides, Forms, Meet, Calendar, Keep and Tasks. You will learn how to share safely and how to work in a team, as well as the basics of each application.</p><p>You only need a Google account, which is free. Every lesson ends with a practical task so you learn by doing.</p>',
    'outcomes'    => [
        'Create a Google account and find your way around Google Drive',
        'Upload, organise and share files with the right permissions',
        'Write and format a document in Google Docs, using styles, tables, comments and version history',
        'Build a spreadsheet in Google Sheets with formulas, filters, charts and data validation',
        'Design a presentation in Google Slides and present it with confidence',
        'Create a survey or quiz in Google Forms and read the results in Sheets',
        'Host and join meetings in Google Meet and schedule events in Google Calendar',
        'Protect your account and work well in a team using shared files',
    ],
    'requirements' => [
        'A computer, laptop or smartphone with an internet connection',
        'No experience needed (basic use of a browser helps)',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Google Workspace Learning Center', 'url' => 'https://support.google.com/a/users'],
        ['title' => 'Google Docs Help', 'url' => 'https://support.google.com/docs'],
        ['title' => 'Google Drive Help', 'url' => 'https://support.google.com/drive'],
        ['title' => 'Google Meet Help', 'url' => 'https://support.google.com/meet'],
        ['title' => 'Google Calendar Help', 'url' => 'https://support.google.com/calendar'],
    ],
    'modules' => [
        [
            'title'   => 'Module 1: Your Google account and Drive',
            'summary' => 'Set up your account, find your way around Drive, organise files and share them safely.',
            'lessons' => [
                [
                    'title'   => 'Creating and understanding your Google account',
                    'minutes' => 10,
                    'body'    => <<<'BODY'
<p>After this lesson you will have a Google account and know how to move between Google's apps. One account opens Gmail, Drive, Docs, Sheets, Slides, Meet and Calendar, so this is the key to the whole course.</p>
<h3>What is a Google account?</h3>
<p>A <strong>Google account</strong> is your sign-in identity for Google services. It is made of an email address (often a Gmail address) and a password. Your files, emails and calendar are stored under that account. Some schools and organisations give out accounts that end in their own domain; those are managed by an administrator who may set extra rules.</p>
<h3>Creating an account</h3>
<ol>
<li>Open your browser and go to Google's sign-in page. Choose <strong>Create account</strong> and then "For my personal use".</li>
<li>Enter your first and last name.</li>
<li>Enter your date of birth and gender.</li>
<li>Choose your username. Use your real name, for example <code>grace.achan</code>, so that it looks professional.</li>
<li>Create a strong password of at least 12 characters, mixing words, numbers and symbols.</li>
<li>Add a recovery phone number or recovery email, which helps if you are locked out.</li>
<li>Read and accept the terms and finish the setup.</li>
</ol>
<p>If you already have an account, you do not need another. Many people use one for work or study, and keep a different one for personal use.</p>
<h3>The app launcher</h3>
<p>Near the top right of most Google pages is a square of nine dots, the <strong>app launcher</strong>. Select it to open a list of apps: Gmail, Drive, Docs, Sheets, Slides, Calendar, Meet, Forms and more. Next to it, your profile picture or initial opens your account menu, where you can switch accounts, sign out or manage settings.</p>
<h3>Managing your account</h3>
<p>Choose your picture and then <strong>Manage your Google Account</strong>. Here you can change your photo, name, password and recovery details. The <strong>Security</strong> section shows devices that use your account and lets you turn on 2-step verification (covered in the last module).</p>
<h3>Using several accounts</h3>
<p>You can be signed in to more than one account. Choose <strong>Add another account</strong> in the account menu. Be careful: if you create a document while the wrong account is active, it will be saved in the wrong place and colleagues may not find it. Always check the picture or initial at the top right.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a Google account, or sign in with one you already have.</li>
<li>Open the app launcher and find Drive, Docs, Sheets and Slides.</li>
<li>Open <strong>Manage your Google Account</strong> and add a profile picture or change your display name.</li>
<li>Find the Security section and note whether 2-step verification is on.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>One Google account gives access to all the Workspace apps.</li>
<li>Use a professional username and a strong, private password.</li>
<li>The nine-dot app launcher opens all apps; your picture opens the account menu.</li>
<li>Always check which account you are signed in to before creating or sharing files.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Google Drive: layout, uploading and folders',
                    'minutes' => 14,
                    'body'    => <<<'BODY'
<p>In this lesson you will learn to find your way around Google Drive, upload files and organise them in folders. Drive is your online filing cabinet. Files stored there are safe if your phone is lost, and you can open them from any device.</p>
<h3>The Drive layout</h3>
<table>
<thead><tr><th>Section</th><th>What you find there</th></tr></thead>
<tbody>
<tr><td>My Drive</td><td>Files and folders you created or uploaded</td></tr>
<tr><td>Shared with me</td><td>Files other people have shared with you</td></tr>
<tr><td>Recent</td><td>Files you opened lately</td></tr>
<tr><td>Starred</td><td>Files you marked as important</td></tr>
<tr><td>Trash (Bin)</td><td>Deleted files, kept for about 30 days before being removed</td></tr>
<tr><td>Storage</td><td>Shows how much space you have used</td></tr>
</tbody>
</table>
<p>The large search box at the top finds files by name, owner, type and even by words inside documents.</p>
<h3>Creating new files and folders</h3>
<p>Select the <strong>New</strong> button (top left). You can create a <strong>Folder</strong>, upload a <strong>File</strong> or <strong>Folder</strong> from your device, or start a Google Doc, Sheet, Slides presentation or Form directly.</p>
<h3>Uploading files</h3>
<ol>
<li>Select <strong>New</strong>, then <strong>File upload</strong> (or <strong>Folder upload</strong>).</li>
<li>Choose the file on your computer or phone and open it.</li>
<li>Wait for the upload box at the bottom right to show that the upload is complete.</li>
</ol>
<p>You can also drag a file from your computer into the Drive window. Office files such as Word or Excel can be opened in Drive and converted to Google format when you want to edit them online.</p>
<h3>Organising with folders</h3>
<ol>
<li>Select <strong>New</strong>, then <strong>New folder</strong>, give it a clear name such as <code>2026 Projects</code> and select <strong>Create</strong>.</li>
<li>Drag files into the folder, or right-click a file and choose <strong>Move to</strong>.</li>
<li>Right-click a folder to give it a colour, which makes it easier to spot.</li>
<li>Right-click an important file and choose <strong>Add to starred</strong>.</li>
</ol>
<p>Use short, consistent names and dates, for example <code>2026-03-Budget-Draft</code>. Delete files you no longer need by right-clicking and choosing <strong>Move to trash</strong>. You can restore them from the Trash within the retention period.</p>
<h3>Storage limits</h3>
<p>A free Google account normally gives 15 GB of storage, shared between Drive, Gmail and Google Photos. Files created as native Google Docs, Sheets or Slides generally do not count towards your storage in the same way as uploaded files, but rules change, so check the Storage page of your account. If you run out of space, delete big unneeded files or buy more storage.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open Drive and create a folder called "LAF Practice".</li>
<li>Upload a photo or document from your device into it.</li>
<li>Create a subfolder named "Notes" and move the file into it.</li>
<li>Star the folder and check the Storage section to see how much space you have used.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Drive stores files online so you can reach them from any device.</li>
<li>Use New to create folders and upload files; drag and drop also works.</li>
<li>Clear, consistent names and folders make files easy to find.</li>
<li>Free accounts share about 15 GB between Drive, Gmail and Photos.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Sharing files and permissions',
                    'minutes' => 14,
                    'body'    => <<<'BODY'
<p>After this lesson you will be able to share a file or folder with the right people and the right level of access. Sharing is the main strength of Google Workspace, and sharing carelessly is the main risk, so it is worth learning well.</p>
<h3>How to share a file</h3>
<ol>
<li>Right-click the file or folder in Drive (or open it and select the blue <strong>Share</strong> button at the top right).</li>
<li>In the box, type the email addresses of the people you want to share with.</li>
<li>Select the permission next to the box: <strong>Viewer</strong>, <strong>Commenter</strong> or <strong>Editor</strong>.</li>
<li>Add a short message if you wish and select <strong>Send</strong>.</li>
</ol>
<h3>The three permission levels</h3>
<table>
<thead><tr><th>Level</th><th>Can read</th><th>Can comment</th><th>Can edit</th><th>Typical use</th></tr></thead>
<tbody>
<tr><td>Viewer</td><td>Yes</td><td>No</td><td>No</td><td>Final reports, notices, learning material</td></tr>
<tr><td>Commenter</td><td>Yes</td><td>Yes</td><td>No</td><td>A draft you want feedback on</td></tr>
<tr><td>Editor</td><td>Yes</td><td>Yes</td><td>Yes</td><td>Teammates writing together</td></tr>
</tbody>
</table>
<p>The <strong>Owner</strong> is the person who created the file, and can also remove access and delete it. Always give the lowest permission that does the job.</p>
<h3>Sharing by link</h3>
<p>In the Share window, under <strong>General access</strong>, you can choose:</p>
<ul>
<li><strong>Restricted</strong>: only the people you added can open the file. This is the safest choice.</li>
<li><strong>Anyone with the link</strong>: anybody who receives the link can open it with the level you choose, without signing in. Use this for public material such as a flyer, never for personal or financial information.</li>
</ul>
<p>Select <strong>Copy link</strong> to paste it into an email or WhatsApp.</p>
<h3>Changing or removing access</h3>
<p>Open the Share window again. Next to a person's name you can change their level or choose <strong>Remove access</strong>. If someone asks for access, you will receive an email request to approve or deny. In Drive you can also open <strong>Details</strong> (the "i" icon) to see who has access and recent activity.</p>
<h3>Sharing folders</h3>
<p>Sharing a folder shares everything inside it, including files added later. This is a good method for teams: create one folder, share it with the team once, and keep all project files inside it.</p>
<h3>Common mistakes</h3>
<ul>
<li>Setting "Anyone with the link" as Editor for a private file, so anyone can change it.</li>
<li>Typing a wrong email address and sharing with a stranger.</li>
<li>Forgetting to remove access when a project ends.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Share your practice folder with a friend as Viewer. Ask them to confirm they can open it but not change it.</li>
<li>Change their permission to Commenter, then to Editor.</li>
<li>Set General access to Restricted and check that a person who is not on the list cannot open it.</li>
<li>Remove your friend's access.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Choose Viewer, Commenter or Editor depending on what the person needs to do.</li>
<li>Restricted sharing is safest; use "Anyone with the link" only for public items.</li>
<li>Sharing a folder shares everything inside it.</li>
<li>Review and remove access when it is no longer needed.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Offline access, Drive on your phone and shared drives',
                    'minutes' => 12,
                    'body'    => <<<'BODY'
<p>This lesson shows you how to keep working when the internet is weak, how to use Drive on your phone, and what shared drives are. In places with unstable connections or power cuts, these skills prevent lost time.</p>
<h3>Working offline</h3>
<p>Google's editors can work offline if you prepare in advance. On a computer using Google Chrome (or another supported browser), you usually do this once while you are online:</p>
<ol>
<li>Open Drive and select the gear icon, then <strong>Settings</strong>.</li>
<li>Find the <strong>Offline</strong> option and tick it. You may need to install a small Google extension called Docs Offline; follow the instructions on screen.</li>
<li>Right-click a file you will need and choose <strong>Available offline</strong>.</li>
</ol>
<p>While offline you can open and edit those files. When your connection returns, your changes upload automatically. Remember that other people cannot see your changes until then, and two people editing the same part offline can create conflicts.</p>
<h3>Drive on your phone</h3>
<ul>
<li>Install the <strong>Google Drive</strong> app from the official app store and sign in.</li>
<li>Tap the <strong>+</strong> button to upload, create a folder, or <strong>scan</strong> a paper document with your camera. The scan becomes a PDF in Drive.</li>
<li>Tap the three dots next to a file and choose <strong>Make available offline</strong> to keep a copy on the phone.</li>
<li>Use the same menu to share, rename, download or delete.</li>
</ul>
<p>Scanning documents is very useful for certificates and receipts, but remember to store personal documents in a private folder.</p>
<h3>Desktop apps and sync</h3>
<p>Google offers an application for computers that can keep chosen folders on your computer in step with Drive. It is helpful if you work with many files on one machine, but it uses data and disk space, so install it only if you need it and your connection allows.</p>
<h3>Shared drives</h3>
<p>A <strong>shared drive</strong> is a team storage space available in many Google Workspace business, school and nonprofit accounts (it is not usually offered on personal accounts). The difference from a normal folder is that the files belong to the team, not to one person. If a team member leaves, the files stay. Members are given roles, such as Manager, Content manager, Contributor, Commenter and Viewer. You will see "Shared drives" in the left menu if your organisation has them.</p>
<table>
<thead><tr><th>Feature</th><th>My Drive folder</th><th>Shared drive</th></tr></thead>
<tbody>
<tr><td>Owner of files</td><td>The person who made them</td><td>The team</td></tr>
<tr><td>If the creator leaves</td><td>Files may be lost or moved</td><td>Files stay in the team space</td></tr>
<tr><td>Available on</td><td>All accounts</td><td>Selected Workspace accounts</td></tr>
</tbody>
</table>
<h3>Try it yourself</h3>
<ol>
<li>Choose one important document and mark it as available offline.</li>
<li>Turn off your Wi-Fi, open it and type one line. Turn Wi-Fi back on and check that the change saved.</li>
<li>On your phone, install the Drive app and scan a page of notes to PDF.</li>
<li>Look at the left menu: do you have Shared drives? Write down why a team might use one.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Set up offline access while you are online; changes sync later.</li>
<li>The Drive phone app can scan paper documents into PDF.</li>
<li>Shared drives belong to a team, so files stay if a person leaves.</li>
<li>Offline editing carries a risk of conflicts if others edit the same part.</li>
</ul>
BODY,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does one Google account give you access to?
- Only Gmail
* Gmail, Drive, Docs, Sheets, Slides, Meet, Calendar and more
- Only Google Docs
- Only YouTube
= One account is the key to the whole Workspace family.

Q: Where do you find the list of all Google apps, such as Docs and Sheets?
- In the Trash
* In the app launcher (the nine dots at the top right)
- In the address bar
- In the Starred section
= The app launcher lists the apps you can open.

Q: A file someone else shared with you appears in which Drive section?
- Trash
- Starred
- Storage
* Shared with me
= Files shared by others are listed under Shared with me.

Q: A free Google account has about 15 GB of storage. Which products share it?
* Drive
* Gmail
* Google Photos
- Only Drive
= Storage is shared across Drive, Gmail and Photos.

Q: You want a teammate to read your draft and write feedback but not change the text. Which permission?
- Viewer
* Commenter
- Editor
- Owner
= Commenters can add comments but cannot edit the content.

Q: Which general access setting is safest for a file with names and phone numbers?
- Anyone with the link, Editor
- Anyone with the link, Viewer
* Restricted
- Public on the web
= Restricted means only people you add can open it.

Q: Why use a shared drive instead of a personal folder for a team project?
- It makes the internet faster.
* The files belong to the team and stay if a member leaves.
- It removes the need for a password.
- It deletes old files automatically.
= Shared drives are owned by the team. They are available only on certain Workspace accounts.
QUIZ,
        ],
        [
            'title'   => 'Module 2: Google Docs',
            'summary' => 'Write, format and polish documents, collaborate with comments and use history, voice typing and templates.',
            'lessons' => [
                [
                    'title'   => 'Creating a document and basic formatting',
                    'minutes' => 14,
                    'body'    => <<<'BODY'
<p>After this lesson you will be able to create a Google Doc, type and format text, and save and name it. Docs replaces many everyday uses of Word, and it saves automatically as you type, which protects you from power cuts.</p>
<h3>Creating a document</h3>
<ol>
<li>Open Google Docs from the app launcher, or in Drive select <strong>New</strong> and then <strong>Google Docs</strong>.</li>
<li>Choose <strong>Blank document</strong>.</li>
<li>Click "Untitled document" at the top left and type a name, such as <code>Youth Club Report</code>.</li>
<li>Start typing in the page.</li>
</ol>
<p>Docs saves your changes constantly. At the top you will see a message such as "Saved to Drive".</p>
<h3>The toolbar and menus</h3>
<p>Under the menu words (<strong>File, Edit, View, Insert, Format, Tools</strong>) is the toolbar with buttons for undo, zoom, text style, font, font size, bold, italic, underline, colour, alignment, lists and more. Hover over any button to read its name.</p>
<h3>Formatting text</h3>
<ol>
<li>Select the text by dragging with your mouse, or by holding Shift and pressing the arrow keys.</li>
<li>Use the toolbar to change the font and the size, for example 11 or 12 point for normal text.</li>
<li>Select <strong>Bold</strong> (Ctrl+B, Cmd+B), <strong>Italic</strong> (Ctrl+I) or <strong>Underline</strong> (Ctrl+U).</li>
<li>Use alignment buttons for left, centre, right, or justified text.</li>
<li>Choose <strong>Line &amp; paragraph spacing</strong> to make lines more open, for example 1.15 or 1.5.</li>
</ol>
<h3>Useful shortcuts</h3>
<table>
<thead><tr><th>Action</th><th>Windows</th><th>Mac</th></tr></thead>
<tbody>
<tr><td>Copy / Cut / Paste</td><td>Ctrl+C / Ctrl+X / Ctrl+V</td><td>Cmd+C / Cmd+X / Cmd+V</td></tr>
<tr><td>Undo / Redo</td><td>Ctrl+Z / Ctrl+Y</td><td>Cmd+Z / Cmd+Shift+Z</td></tr>
<tr><td>Select all</td><td>Ctrl+A</td><td>Cmd+A</td></tr>
<tr><td>Find</td><td>Ctrl+F</td><td>Cmd+F</td></tr>
</tbody>
</table>
<h3>Lists</h3>
<p>Choose <strong>Bulleted list</strong> or <strong>Numbered list</strong> from the toolbar, type each item and press Enter. Press Enter twice to end the list.</p>
<h3>Spelling and grammar</h3>
<p>Words with red wavy lines may be misspelt. Right-click them to see suggestions. You can also use <strong>Tools</strong>, then <strong>Spelling and grammar</strong>, to check the whole document. Always read it yourself too, because a spell checker will not notice a correctly spelt wrong word, such as "form" instead of "from".</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a new document called "About Me".</li>
<li>Type a title and three short paragraphs about yourself, your studies and your goals.</li>
<li>Make the title bold and centred, and set the body to size 12.</li>
<li>Add a bulleted list of three skills you want to learn.</li>
<li>Run the spelling check and fix any errors.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Docs saves automatically; give your file a clear name.</li>
<li>Select text before formatting it.</li>
<li>Use shortcuts such as Ctrl+B, Ctrl+Z and Ctrl+F to save time.</li>
<li>Spelling checks help, but you must still proofread.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Styles, headings, tables and images',
                    'minutes' => 15,
                    'body'    => <<<'BODY'
<p>When you finish this lesson you will be able to make a document look organised, using headings, a table of contents, tables and pictures. A document with a clear structure is easier to read and looks more professional, which counts in reports, proposals and assignments.</p>
<h3>Headings and styles</h3>
<p>A <strong>style</strong> is a ready-made set of formatting. Instead of making a title bigger by hand, apply the <strong>Title</strong> or <strong>Heading</strong> styles. In the toolbar, the box that normally says "Normal text" is the style menu.</p>
<ol>
<li>Click inside a line such as "Introduction".</li>
<li>Open the style menu and choose <strong>Heading 1</strong> for main sections or <strong>Heading 2</strong> for subsections.</li>
<li>Use <strong>Normal text</strong> for paragraphs.</li>
</ol>
<p>The benefit is consistency: to change how all your headings look, change the style once, and every heading updates. Headings also help screen readers and the <strong>outline</strong> of the document.</p>
<h3>Table of contents</h3>
<p>Once you have headings, place the cursor where you want the contents list, then select <strong>Insert</strong>, <strong>Table of contents</strong> and choose a style. The list links to each heading. If you add or change headings later, select the contents and click the refresh symbol.</p>
<h3>Tables</h3>
<ol>
<li>Select <strong>Insert</strong>, then <strong>Table</strong>, and move the mouse over the grid to choose the number of rows and columns.</li>
<li>Click in a cell and type. Press Tab to move to the next cell.</li>
<li>Right-click in the table to insert or delete rows and columns.</li>
<li>Use <strong>Format</strong>, then <strong>Table</strong>, then <strong>Table properties</strong> to change borders and shading.</li>
</ol>
<p>Use a table when information fits rows and columns, such as a budget, a timetable or a list of names. Do not use a table just to arrange text on the page.</p>
<h3>Images</h3>
<p>Select <strong>Insert</strong>, then <strong>Image</strong>, and choose where the picture comes from: your computer, Drive, a web search or your camera. After inserting it, click it to move or resize it using the corner handles. The toolbar that appears offers wrapping options: <strong>In line</strong> keeps the picture in the text, and <strong>Wrap text</strong> lets words flow around it. Use only pictures you have the right to use, and write a short description with <strong>Alt text</strong> (right-click the image) for people using screen readers.</p>
<h3>Page setup and numbers</h3>
<p>Use <strong>File</strong>, then <strong>Page setup</strong> to change page size (A4 is common in Uganda), orientation and margins. Add page numbers with <strong>Insert</strong>, then <strong>Page numbers</strong>. Add a header or footer with <strong>Insert</strong>, then <strong>Headers and footers</strong>.</p>
<h3>Try it yourself</h3>
<ol>
<li>Start a document called "Community Project Plan".</li>
<li>Type three section names and make each Heading 1.</li>
<li>Insert a table of contents at the top.</li>
<li>Add a small table of three rows listing tasks, people and dates.</li>
<li>Insert one image and add alt text, then add page numbers.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Use heading styles instead of manual formatting.</li>
<li>A table of contents is built from your headings and can be refreshed.</li>
<li>Tables suit data in rows and columns.</li>
<li>Add alt text to images and use page numbers in longer documents.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Comments, suggestions and version history',
                    'minutes' => 14,
                    'body'    => <<<'BODY'
<p>After this lesson you will be able to review other people's writing without ruining it, answer comments, and go back to an earlier version of your document. These tools are what make Google Docs so useful for teamwork.</p>
<h3>Working at the same time</h3>
<p>Several people can open the same document at once. You will see coloured cursors and names showing who is typing. Changes appear for everybody within moments. This removes the old problem of many emailed copies such as "Report-final-final2".</p>
<h3>Comments</h3>
<ol>
<li>Select a word or sentence.</li>
<li>Select the <strong>Add comment</strong> button (a speech bubble with a plus sign), or press Ctrl+Alt+M (Cmd+Option+M).</li>
<li>Type your comment. To get a person's attention, type <code>@</code> followed by their name or email; they will receive an email.</li>
<li>Select <strong>Comment</strong>.</li>
</ol>
<p>Others can reply. When the matter is settled, select <strong>Resolve</strong> (the tick) and it disappears from view. You can find resolved comments again from the comment history.</p>
<h3>Suggesting mode</h3>
<p>At the top right, the pencil button shows the editing mode. It has three choices:</p>
<table>
<thead><tr><th>Mode</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Editing</td><td>Your changes happen directly in the text</td></tr>
<tr><td>Suggesting</td><td>Your changes appear as suggestions that the owner can accept or reject</td></tr>
<tr><td>Viewing</td><td>You can only read</td></tr>
</tbody>
</table>
<p>Use Suggesting when you review a colleague's or a teacher's work. The owner sees each suggested change in colour and can select the tick to accept it or the cross to reject it.</p>
<h3>Version history</h3>
<p>Docs keeps earlier versions of your document. To see them, select <strong>File</strong>, then <strong>Version history</strong>, then <strong>See version history</strong>. A panel lists earlier versions with dates and the names of the editors. Select one to preview it, and choose <strong>Restore this version</strong> to bring it back. You can also name an important version, for example "Version sent to donor", so it is easy to find.</p>
<p>This protects against mistakes such as deleting a whole section, and shows who changed what.</p>
<h3>Common mistakes</h3>
<ul>
<li>Editing directly in a colleague's text when you should be suggesting.</li>
<li>Leaving comments unresolved, so that nobody knows what is finished.</li>
<li>Restoring an old version without checking what you will lose; make a copy first with <strong>File</strong>, then <strong>Make a copy</strong>.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Share a document with a friend as Editor.</li>
<li>Ask them to switch to Suggesting mode and change a sentence. Accept one suggestion and reject another.</li>
<li>Add a comment mentioning your friend with @ and ask them to answer; resolve it afterwards.</li>
<li>Open Version history and name the current version.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Many people can edit one document at once without sending copies.</li>
<li>Comments and the @ mention direct attention to specific text.</li>
<li>Suggesting mode lets the owner accept or reject changes.</li>
<li>Version history allows you to restore earlier versions.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Voice typing, templates, exporting and add-ons',
                    'minutes' => 12,
                    'body'    => <<<'BODY'
<p>By the end of this lesson you will be able to dictate text, start from a template, save your document as Word or PDF and know what add-ons are. These features save time and let you share with people who do not use Google.</p>
<h3>Voice typing</h3>
<p>Voice typing turns your speech into text. It works best in the Chrome browser with a microphone and a good connection.</p>
<ol>
<li>Open a document and select <strong>Tools</strong>, then <strong>Voice typing</strong> (or press Ctrl+Shift+S).</li>
<li>Allow microphone access if asked.</li>
<li>Select the microphone icon and speak clearly at a normal pace.</li>
<li>Say punctuation by name: "comma", "full stop" and "new paragraph".</li>
<li>Select the icon again to stop, then read and correct the text.</li>
</ol>
<p>Accents and background noise can affect accuracy, so always proofread. Voice typing is a helpful aid for people who find typing slow or difficult.</p>
<h3>Templates</h3>
<p>A <strong>template</strong> is a pre-designed document you can reuse, such as a CV, a letter, a meeting agenda or a report. On the Google Docs home page, select <strong>Template gallery</strong> (which may be shown as a link to see more templates) and choose one. A copy opens for you to edit. Replace sample text with your own, and check that the layout still fits. If you create a document you will use often, such as a monthly report, you can save a copy and reuse it each month.</p>
<h3>Downloading and exporting</h3>
<p>To send a document to someone who uses Word or needs a fixed layout:</p>
<ol>
<li>Select <strong>File</strong>, then <strong>Download</strong>.</li>
<li>Choose <strong>Microsoft Word (.docx)</strong> to allow editing in Word, or <strong>PDF document (.pdf)</strong> for a final version that cannot easily be changed. Other options include plain text and web page.</li>
<li>The file goes to your Downloads folder; attach it to an email or upload it where needed.</li>
</ol>
<p>You can also open a Word file in Docs by uploading it to Drive and opening it with Google Docs. Check the layout afterwards because complex formatting may change. In Docs you can also email the file directly with <strong>File</strong>, then <strong>Email</strong>.</p>
<h3>Printing</h3>
<p>Use Ctrl+P (Cmd+P) to open the print window. Choose a printer, pages and number of copies, or select <strong>Save as PDF</strong> as the destination.</p>
<h3>Add-ons</h3>
<p><strong>Add-ons</strong> are small extra tools, made by Google or other companies, that add features to Docs, Sheets and Slides, for example for citations, diagrams or mail merge. Find them under <strong>Extensions</strong>, then <strong>Add-ons</strong>, then <strong>Get add-ons</strong>. Install only add-ons from known developers, read the permissions they ask for, and remove those you do not use. Organisation accounts may restrict add-ons.</p>
<h3>Try it yourself</h3>
<ol>
<li>Turn on voice typing and dictate three sentences about your day, including punctuation. Correct any mistakes.</li>
<li>Open a template, such as a letter or a report, and fill in your own information.</li>
<li>Download your document as a PDF and open the PDF to see it.</li>
<li>Download it as a .docx and note where it was saved.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Voice typing is quick but must be proofread.</li>
<li>Templates save time; replace the sample text with your own.</li>
<li>Download as PDF for final versions and .docx when others need to edit in Word.</li>
<li>Install add-ons carefully and check what permissions they request.</li>
</ul>
BODY,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What happens when you type in a Google Doc?
- You must press Save every few minutes.
* It saves automatically to Drive as you type.
- It saves only when you close the browser.
- It never saves.
= Docs saves your changes continuously.

Q: Why use Heading styles instead of just making text bigger?
- Headings are always red.
* Styles keep formatting consistent and allow an automatic table of contents.
- Headings make the file smaller.
- It is the only way to print.
= Styles can be changed once for the whole document and are used to build the table of contents and outline.

Q: Which keyboard shortcut undoes your last action on Windows?
- Ctrl+Y
- Ctrl+A
* Ctrl+Z
- Ctrl+F
= Ctrl+Z (Cmd+Z on Mac) undoes the last action.

Q: You are reviewing a colleague's report and want them to approve each change. What should you use?
- Delete the text and type new text
* Suggesting mode
- Viewing mode
- Version history
= In Suggesting mode, changes appear as suggestions that the owner accepts or rejects.

Q: How can you restore the text of a section that was deleted yesterday?
- Ask Google to email you
* Open Version history and restore an earlier version
- Use voice typing
- Re-install Docs
= Version history keeps earlier versions that you can preview and restore.

Q: To get a person's attention in a comment you can:
- Type their name in capital letters
* Type @ followed by their name or email address
- Change the font colour
- Share the file again as Viewer
= The @ mention sends the person a notification.

Q: Which format is best for a final copy that you do not want others to change easily?
- .docx
* PDF
- Plain text
- Web page
= PDFs keep the layout fixed and are harder to edit.

Q: Which are good habits for voice typing? (choose all that apply)
* Speak the punctuation, for example "full stop"
* Proofread the text afterwards
- Trust the result without checking
= Voice typing can make mistakes, so always check it.
QUIZ,
        ],
        [
            'title'   => 'Module 3: Google Sheets',
            'summary' => 'Enter data, use formulas and functions, organise and chart information, and protect your work.',
            'lessons' => [
                [
                    'title'   => 'Cells, data and formatting',
                    'minutes' => 14,
                    'body'    => <<<'BODY'
<p>After this lesson you will be able to create a spreadsheet, enter data and make it clear to read. A spreadsheet helps you handle money, marks, stock and attendance with much less work than paper.</p>
<h3>What a spreadsheet is</h3>
<p>Google Sheets arranges data in a grid. The vertical lines are <strong>columns</strong>, labelled with letters (A, B, C). The horizontal lines are <strong>rows</strong>, labelled with numbers (1, 2, 3). A <strong>cell</strong> is where a column and row meet; its name is a column letter and a row number, such as <code>B3</code>. A file is called a <strong>spreadsheet</strong> and can have several <strong>sheets</strong> (tabs) at the bottom.</p>
<h3>Entering data</h3>
<ol>
<li>Open Sheets and start a blank spreadsheet. Name it at the top left, for example <code>Shop Sales</code>.</li>
<li>Click cell <code>A1</code> and type a heading such as "Item". Press Tab to move right.</li>
<li>Type headings in <code>B1</code> ("Quantity") and <code>C1</code> ("Price").</li>
<li>Press Enter, then continue typing the items below, one row per item.</li>
</ol>
<p>Use one row for each record, one column for each kind of information, and a heading at the top of every column. Avoid blank rows and merged cells inside your data, because they cause problems for sorting and formulas.</p>
<h3>Selecting and editing</h3>
<ul>
<li>Click a cell to select it; drag to select a range such as <code>A1:C6</code>.</li>
<li>Click a column letter or row number to select the whole column or row.</li>
<li>Double-click a cell, or press F2, to change what is inside it.</li>
<li>Right-click a column letter or row number to insert or delete columns or rows.</li>
<li>Drag the line between column letters to make a column wider.</li>
</ul>
<h3>Formatting</h3>
<p>Select the cells and use the toolbar. You can make headings bold, add a background colour, set the alignment and add borders. Use the number format buttons for money: choose <strong>Format as currency</strong> or open <strong>Format</strong>, then <strong>Number</strong> and select a format. Percentages, dates and plain numbers have their own formats. To freeze the heading row so that it stays visible when you scroll, open <strong>View</strong>, then <strong>Freeze</strong>, and choose 1 row.</p>
<h3>Filling series</h3>
<p>Type 1 in <code>A2</code> and 2 in <code>A3</code>, select both, and drag the small square at the corner of the selection down. Sheets continues the pattern. It also works for days, months and dates.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a spreadsheet called "My Budget".</li>
<li>Make three headings: Item, Category, Amount (UGX).</li>
<li>Enter at least six expenses, such as transport, airtime and food.</li>
<li>Make the headings bold with a colour, format the amounts as numbers with a thousands separator, and freeze the first row.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Cells are named by column letter and row number, such as B3.</li>
<li>One row per record, one column per kind of data, with headings.</li>
<li>Use number formats for currency, dates and percentages.</li>
<li>Freeze the header row to keep the headings in view.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Formulas and functions',
                    'minutes' => 18,
                    'body'    => <<<'BODY'
<p>When you finish this lesson you will be able to make Sheets calculate for you, using formulas and the most useful functions: SUM, AVERAGE, IF, COUNTIF and VLOOKUP. Formulas save time and avoid arithmetic mistakes, and they update automatically when your numbers change.</p>
<h3>Writing a formula</h3>
<p>Every formula starts with an equals sign <code>=</code>. For example, type in a cell:</p>
<pre><code>=B2*C2</code></pre>
<p>This multiplies the quantity in B2 by the price in C2. Use <code>+</code> to add, <code>-</code> to subtract, <code>*</code> to multiply and <code>/</code> to divide. Click on cells instead of typing their names to avoid mistakes. To copy the formula down, select the cell and drag the small square in the corner downwards; the cell names adjust for each row.</p>
<h3>Common functions</h3>
<p>A <strong>function</strong> is a ready-made formula with a name.</p>
<table>
<thead><tr><th>Function</th><th>Example</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>SUM</td><td><code>=SUM(D2:D10)</code></td><td>Adds up all numbers in the range</td></tr>
<tr><td>AVERAGE</td><td><code>=AVERAGE(B2:B10)</code></td><td>Finds the mean</td></tr>
<tr><td>MAX / MIN</td><td><code>=MAX(B2:B10)</code></td><td>Largest / smallest value</td></tr>
<tr><td>COUNT</td><td><code>=COUNT(B2:B10)</code></td><td>Counts cells holding numbers</td></tr>
<tr><td>IF</td><td><code>=IF(B2&gt;=50,"Pass","Fail")</code></td><td>Gives one answer if a test is true and another if not</td></tr>
<tr><td>COUNTIF</td><td><code>=COUNTIF(C2:C20,"Pass")</code></td><td>Counts cells that meet a condition</td></tr>
<tr><td>VLOOKUP</td><td><code>=VLOOKUP(A2,F2:G10,2,FALSE)</code></td><td>Looks up a value in the first column of a table and returns a value from another column</td></tr>
</tbody>
</table>
<h3>Using AutoSum</h3>
<p>Click the cell under a column of numbers, open the <strong>Functions</strong> button (the sigma symbol) in the toolbar and choose <strong>SUM</strong>. Sheets suggests a range; press Enter if it is correct.</p>
<h3>Understanding VLOOKUP</h3>
<p>Imagine a price list in F2:G10, with item names in column F and prices in column G. In cell B2 you type an item name. The formula <code>=VLOOKUP(B2,F2:G10,2,FALSE)</code> means: search for B2 in the first column of the table, then return the value in the 2nd column; FALSE means "find an exact match". If the item is not found, you will see <code>#N/A</code>.</p>
<h3>Absolute references</h3>
<p>If a formula must always point to the same cell, such as a tax rate in <code>F1</code>, write it with dollar signs: <code>$F$1</code>. Without them, the reference moves when you copy the formula.</p>
<h3>Common mistakes</h3>
<ul>
<li>Forgetting the equals sign, so the formula shows as text.</li>
<li>Typing numbers with letters or spaces, such as "5 000 UGX", which Sheets reads as text. Type 5000 and format the cell instead.</li>
<li>Pointing a SUM at the wrong range.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Add a column "Total" to your budget or shop sheet and use a formula to multiply quantity by price in each row.</li>
<li>Use SUM at the bottom to find the grand total.</li>
<li>Add marks for five students and use IF to show Pass if the mark is 50 or more.</li>
<li>Use COUNTIF to count how many students passed.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Formulas begin with = and update when the data changes.</li>
<li>SUM, AVERAGE, IF, COUNTIF and VLOOKUP cover most everyday needs.</li>
<li>Copy formulas by dragging the corner; use $ to fix a cell.</li>
<li>Store numbers as numbers, not as text.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Sorting, filters, charts and conditional formatting',
                    'minutes' => 16,
                    'body'    => <<<'BODY'
<p>After this lesson you will be able to sort and filter your data, turn it into a chart and make important values stand out with colour. These tools help you see patterns, such as which product sells best or which months were weakest.</p>
<h3>Sorting</h3>
<ol>
<li>Click any cell in your data (make sure there is a heading row).</li>
<li>Select <strong>Data</strong>, then <strong>Sort range</strong>, then <strong>Advanced range sorting options</strong>. Tick "Data has header row".</li>
<li>Choose the column to sort by and select A to Z (small to large) or Z to A (large to small), then <strong>Sort</strong>.</li>
</ol>
<p>Sorting rearranges whole rows, so a name always stays with its marks. Always select the whole table, not a single column, or you will mix up the data.</p>
<h3>Filters</h3>
<p>A filter hides rows you do not need without deleting them.</p>
<ol>
<li>Click inside the data and select <strong>Data</strong>, then <strong>Create a filter</strong>.</li>
<li>A small filter icon appears in each heading. Select it to see choices.</li>
<li>Untick the values you do not want, or use <strong>Filter by condition</strong> (for example "Greater than 50 000").</li>
<li>Select <strong>OK</strong>. To remove the filter, open <strong>Data</strong>, then <strong>Remove filter</strong>.</li>
</ol>
<h3>Charts</h3>
<ol>
<li>Select the data you want to show, including the headings.</li>
<li>Select <strong>Insert</strong>, then <strong>Chart</strong>. Sheets suggests a type.</li>
<li>In the chart editor on the right, choose the type in the <strong>Setup</strong> tab, and adjust titles, colours and labels in the <strong>Customize</strong> tab.</li>
</ol>
<table>
<thead><tr><th>Chart</th><th>Best for</th></tr></thead>
<tbody>
<tr><td>Column or bar</td><td>Comparing amounts, such as sales per product</td></tr>
<tr><td>Line</td><td>Showing change over time, such as monthly income</td></tr>
<tr><td>Pie</td><td>Parts of a whole with only a few slices</td></tr>
</tbody>
</table>
<p>Give every chart a clear title and label the axes. Do not use too many colours.</p>
<h3>Conditional formatting</h3>
<p>This changes the look of cells automatically depending on their value. Select the cells, open <strong>Format</strong>, then <strong>Conditional formatting</strong>. Under "Format cells if", choose a rule such as "Less than", type <code>50</code>, and pick a fill colour such as light red. Now every mark below 50 turns red. You can also use a colour scale to show high and low values with shades.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a table of ten sales with product, month and amount.</li>
<li>Sort it from largest to smallest amount.</li>
<li>Apply a filter to show only one product.</li>
<li>Insert a column chart of the amounts and give it a title.</li>
<li>Add conditional formatting to highlight amounts under 20 000.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Sort whole tables with headings so that rows stay together.</li>
<li>Filters hide rows temporarily and do not delete data.</li>
<li>Choose column, line or pie charts to match your message.</li>
<li>Conditional formatting highlights values automatically.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Data validation, protecting ranges and importing data',
                    'minutes' => 14,
                    'body'    => <<<'BODY'
<p>By the end of this lesson you will be able to prevent wrong entries, protect cells that must not change and bring data from other files into Sheets. These skills keep shared spreadsheets accurate when many people use them.</p>
<h3>Data validation</h3>
<p><strong>Data validation</strong> limits what can be typed into a cell. For example, a "Status" column can accept only Present, Absent or Late.</p>
<ol>
<li>Select the cells, such as <code>D2:D30</code>.</li>
<li>Open <strong>Data</strong>, then <strong>Data validation</strong>, then <strong>Add rule</strong>.</li>
<li>For criteria choose <strong>Dropdown</strong> and type the allowed items, or choose a rule such as "Number between" 0 and 100.</li>
<li>Choose to show a warning or to reject invalid input, add help text if you like, and select <strong>Done</strong>.</li>
</ol>
<p>Now users pick from a drop-down list instead of typing, which prevents spelling differences such as "Absent" and "absent." that would mislead COUNTIF.</p>
<h3>Protecting sheets and ranges</h3>
<p>Protection stops people from changing parts of the spreadsheet by accident, such as formulas.</p>
<ol>
<li>Select the range with formulas, or open the sheet tab.</li>
<li>Open <strong>Data</strong>, then <strong>Protect sheets and ranges</strong>.</li>
<li>Choose <strong>Add a sheet or range</strong>, give it a description and select <strong>Set permissions</strong>.</li>
<li>Choose to show a warning, or restrict editing to yourself or chosen people, and select <strong>Done</strong>.</li>
</ol>
<p>Protection is only useful if you share the sheet as Editor with others. People with Viewer access cannot edit anyway.</p>
<h3>Importing data</h3>
<ul>
<li>Open <strong>File</strong>, then <strong>Import</strong> and upload a CSV, Excel or other spreadsheet file. Choose whether to create a new spreadsheet or add a sheet.</li>
<li>Open an Excel file stored in Drive with Google Sheets to edit it online.</li>
<li>Copy a table from a web page or document and paste it into the cells.</li>
<li>Responses from Google Forms flow into Sheets automatically (see the Forms lesson).</li>
</ul>
<p>After importing, check the columns. Dates and numbers sometimes arrive as text, and imported data may include blank rows you should remove.</p>
<h3>Downloading</h3>
<p>Select <strong>File</strong>, then <strong>Download</strong>, and choose <strong>Microsoft Excel (.xlsx)</strong>, <strong>PDF</strong> or <strong>CSV</strong>. CSV is a plain file useful for moving data to other programs.</p>
<h3>Sharing carefully</h3>
<p>The sharing rules are the same as for Docs. Remember that anyone with Editor access can see all data in the spreadsheet, including hidden sheets. Do not share sheets that contain personal details unless the person needs them.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create an attendance sheet with names and a Status column.</li>
<li>Add a drop-down list to the Status column with Present, Absent and Late.</li>
<li>Use COUNTIF to count how many are present.</li>
<li>Protect the cell with the COUNTIF formula, then ask a friend to try to change it.</li>
<li>Download the sheet as a PDF.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Data validation uses drop-downs and rules to prevent wrong entries.</li>
<li>Protecting ranges stops accidental changes to formulas.</li>
<li>You can import CSV and Excel files, then check the imported data.</li>
<li>Share only with people who need to see the data.</li>
</ul>
BODY,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the name of the cell where column C meets row 5?
- 5C
- Cell five
* C5
- C-5-A
= Cells are named with the column letter first, then the row number.

Q: What must every formula start with?
- A dollar sign
- A quotation mark
* An equals sign
- A bracket
= Without =, Sheets treats what you type as text.

Q: Which formula adds the numbers in B2 to B10?
- =ADD(B2:B10)
- =B2+B10
* =SUM(B2:B10)
- =TOTAL B2 TO B10
= SUM adds all numbers in a range.

Q: What does =IF(B2>=50,"Pass","Fail") do?
- Always returns Pass
* Shows Pass when B2 is 50 or more and Fail otherwise
- Counts the cells in B2
- Adds 50 to B2
= IF tests a condition and gives one result if true and another if not.

Q: Which function counts how many cells in C2:C20 contain the word Present?
- =SUM(C2:C20,"Present")
* =COUNTIF(C2:C20,"Present")
- =AVERAGE(C2:C20)
- =MAX(C2:C20)
= COUNTIF counts the cells that meet a condition.

Q: Which chart type best shows change over time, like monthly income?
- Pie chart
* Line chart
- Table
- Bar chart with only one bar
= Line charts show trends over time clearly.

Q: Why use a drop-down list through data validation? (choose all that apply)
* It prevents spelling differences in entries
* It makes it easier for several people to enter consistent data
- It removes the need for column headings
= Drop-downs keep entries consistent, which makes counting and filtering accurate.

Q: You sort only column B of a table, leaving column A alone. What is the likely result?
- Nothing changes
* Names and marks no longer match
- The sheet is protected
- The formulas are deleted
= Sorting only part of a table breaks the link between related data in a row. Select the whole table.
QUIZ,
        ],
        [
            'title'   => 'Module 4: Google Slides and Forms',
            'summary' => 'Create and deliver presentations, then collect answers with surveys and quizzes.',
            'lessons' => [
                [
                    'title'   => 'Building a presentation in Google Slides',
                    'minutes' => 15,
                    'body'    => <<<'BODY'
<p>After this lesson you will be able to build a clear presentation with slides, text, pictures and a theme. Slides are used to pitch ideas, report to donors, teach a class or present a business plan.</p>
<h3>Starting a presentation</h3>
<ol>
<li>Open Google Slides from the app launcher or Drive and choose <strong>Blank presentation</strong>, or a template.</li>
<li>Name the file at the top left, for example <code>Youth Skills Pitch</code>.</li>
<li>Click the title area on the first slide and type your title and subtitle.</li>
</ol>
<h3>Adding slides and layouts</h3>
<p>Select the <strong>+</strong> button on the toolbar to add a slide, or the small arrow next to it to pick a <strong>layout</strong> such as Title and body, Section header or Two columns. You can change the layout later with the <strong>Layout</strong> button. Slides appear in the panel on the left, where you can drag them to change their order, or right-click to duplicate or delete.</p>
<h3>Themes</h3>
<p>A <strong>theme</strong> controls fonts, colours and backgrounds for all slides. Open <strong>Theme</strong> in the toolbar (or <strong>Slide</strong>, then <strong>Change theme</strong>) and choose one that is plain and easy to read. Keep the same theme throughout so the deck looks consistent.</p>
<h3>Adding content</h3>
<ul>
<li>Type in the text boxes. To add another box, select <strong>Insert</strong>, then <strong>Text box</strong>.</li>
<li>Add a picture with <strong>Insert</strong>, then <strong>Image</strong>. Resize it by dragging the corners.</li>
<li>Add shapes, lines and tables from the <strong>Insert</strong> menu.</li>
<li>Paste a chart from Sheets, or insert a video from a link.</li>
</ul>
<h3>Design rules that work</h3>
<table>
<thead><tr><th>Do</th><th>Do not</th></tr></thead>
<tbody>
<tr><td>One main idea per slide</td><td>Fill the slide with long paragraphs</td></tr>
<tr><td>Use at least 24-point text so the back row can read</td><td>Use tiny text to fit more</td></tr>
<tr><td>Use strong contrast (dark text on light background)</td><td>Place yellow text on a white background</td></tr>
<tr><td>Use clear photos and simple charts</td><td>Use cluttered pictures with text on top</td></tr>
<tr><td>Limit yourself to 3-5 short bullet points</td><td>Read the slide aloud word for word</td></tr>
</tbody>
</table>
<h3>Speaker notes</h3>
<p>Under each slide is a notes area. Type reminders there. Only you see them while presenting.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a presentation titled "My Community".</li>
<li>Make five slides: title, the problem, the solution, who can help, thank you.</li>
<li>Apply a simple theme, add one picture and keep text at 24 points or more.</li>
<li>Add speaker notes to two slides.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Choose layouts and one theme for a consistent look.</li>
<li>One idea per slide, short bullets and large readable text.</li>
<li>Use pictures and charts to support, not replace, your message.</li>
<li>Speaker notes help you remember what to say.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Transitions, presenting and collaborating on slides',
                    'minutes' => 12,
                    'body'    => <<<'BODY'
<p>After this lesson you will be able to add simple movement to slides, present in full screen and work with others on one deck. A calm, well-delivered presentation is more convincing than one with too many effects.</p>
<h3>Transitions and animations</h3>
<p>A <strong>transition</strong> is how one slide changes to the next. An <strong>animation</strong> is how an object, such as a line of text, appears on the slide.</p>
<ol>
<li>Select a slide and open <strong>Slide</strong>, then <strong>Transition</strong>. A panel opens on the right.</li>
<li>Choose an effect such as Fade, and set the speed. You can apply it to all slides.</li>
<li>To animate an object, click it and choose <strong>Insert</strong>, then <strong>Animation</strong>. Pick an effect such as Appear or Fade in, and choose whether it starts on click or after the previous action.</li>
</ol>
<p>Use effects sparingly. One simple transition is enough. Flashing, spinning or noisy effects distract the audience.</p>
<h3>Presenting</h3>
<ol>
<li>Select <strong>Slideshow</strong> at the top right (or Ctrl+F5 on Windows).</li>
<li>Move forward with the Right Arrow, Space or Enter, and back with the Left Arrow.</li>
<li>Press Esc to leave the slideshow.</li>
<li>Use <strong>Presenter view</strong> from the Slideshow menu to see your notes and a timer on your own screen while the audience sees only the slides.</li>
</ol>
<p>During a video call, share only the Slides window or use the presenting option offered by Meet.</p>
<h3>Tips for good delivery</h3>
<ul>
<li>Practise aloud at least twice and time yourself.</li>
<li>Speak to the audience, not to the screen.</li>
<li>Start with the main point, then give details, then repeat the main point at the end.</li>
<li>Have a backup: save a PDF copy, because computers and projectors sometimes fail.</li>
<li>Check that the projector is connected and the slides are visible before the audience arrives.</li>
</ul>
<h3>Collaborating</h3>
<p>Share the presentation like any other Google file. Give teammates Editor access, and use comments on slides to discuss changes. Decide who is responsible for which slides, and agree on the theme, fonts and colours before everybody starts. Use Version history to restore earlier work. To copy slides from another presentation, open both decks, select slides in the left panel, copy them, and paste them into the other deck, choosing whether to keep the source theme.</p>
<h3>Exporting</h3>
<p>Choose <strong>File</strong>, then <strong>Download</strong>, and select <strong>PDF</strong> or <strong>Microsoft PowerPoint (.pptx)</strong>. Use the PDF to email or print handouts.</p>
<h3>Try it yourself</h3>
<ol>
<li>Apply a Fade transition to all slides of your "My Community" deck.</li>
<li>Animate one bullet list so that each point appears when you click.</li>
<li>Start the slideshow and present it aloud once, using Presenter view.</li>
<li>Download the deck as a PDF.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Use one simple transition and few animations.</li>
<li>Practise with the Slideshow and Presenter view.</li>
<li>Keep a PDF copy as a backup.</li>
<li>Agree on design rules before a team builds a deck together.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Google Forms: surveys, quizzes and responses',
                    'minutes' => 16,
                    'body'    => <<<'BODY'
<p>After this lesson you will be able to create an online form for a survey, a registration or a quiz, share it, and read the results. Google Forms saves paper and time, because answers arrive in one place and are counted for you.</p>
<h3>Creating a form</h3>
<ol>
<li>Open Google Forms from the app launcher or from Drive (select <strong>New</strong>, then <strong>Google Forms</strong>).</li>
<li>Choose <strong>Blank form</strong>.</li>
<li>Click "Untitled form" and give it a name, and write a short description telling people the purpose and how long it takes.</li>
<li>Type your first question in the question box.</li>
</ol>
<h3>Question types</h3>
<table>
<thead><tr><th>Type</th><th>Use for</th></tr></thead>
<tbody>
<tr><td>Short answer</td><td>A name or a number</td></tr>
<tr><td>Paragraph</td><td>Longer comments</td></tr>
<tr><td>Multiple choice</td><td>Pick one option</td></tr>
<tr><td>Checkboxes</td><td>Pick several options</td></tr>
<tr><td>Dropdown</td><td>Long lists, such as districts</td></tr>
<tr><td>Linear scale</td><td>Rating from 1 to 5</td></tr>
<tr><td>Date / Time</td><td>Bookings and registration</td></tr>
</tbody>
</table>
<p>Use the <strong>Add question</strong> button (a plus sign on the side toolbar) for each new question. Switch on <strong>Required</strong> for the questions people must answer. Use <strong>Add section</strong> to split a long form into pages.</p>
<h3>Making a quiz</h3>
<ol>
<li>Open <strong>Settings</strong> at the top, then <strong>Quizzes</strong>, and switch on <strong>Make this a quiz</strong>.</li>
<li>For each question, select <strong>Answer key</strong>, mark the right answers and set the points.</li>
<li>Add feedback to explain the correct answer.</li>
<li>Choose in settings whether the learner sees the score immediately or after you review it.</li>
</ol>
<h3>Sharing the form</h3>
<p>Select <strong>Send</strong> at the top right. You can send by email, copy a link or embed it in a web page. Preview your form first (the eye icon) and test it by answering it yourself. In Settings you can restrict responses to people in your organisation, or limit to one response per person (which requires sign-in).</p>
<h3>Reading and using responses</h3>
<p>Open the <strong>Responses</strong> tab. <strong>Summary</strong> shows charts for each question, <strong>Question</strong> shows answers question by question, and <strong>Individual</strong> shows one respondent at a time. Select the green Sheets icon to <strong>link to Sheets</strong>: every answer becomes a row in a spreadsheet where you can use formulas, filters and charts. You can also stop collecting answers by switching off <strong>Accepting responses</strong>.</p>
<h3>Responsible data use</h3>
<p>Ask only what you need. Tell people why you are collecting information, and keep the spreadsheet private. Never ask for passwords or bank PINs in a form. Be careful with children's information and follow your organisation's rules.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a five-question feedback form for a class or event, using at least three question types.</li>
<li>Preview it and submit one test response.</li>
<li>Link the responses to a spreadsheet and look at your test row.</li>
<li>Turn it into a quiz with an answer key for two questions.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Choose question types that match the answer you need.</li>
<li>Mark essential questions as required, and test the form before sending it.</li>
<li>Quiz mode scores answers and can show feedback.</li>
<li>Link responses to Sheets for deeper analysis, and protect respondents' information.</li>
</ul>
BODY,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is a "theme" in Google Slides?
- A single picture
* A set of fonts, colours and backgrounds applied to all slides
- A video effect
- A speaker note
= A theme gives the whole presentation a consistent look.

Q: Which is a good rule for slides?
- Fill each slide with full paragraphs
* One main idea per slide with short bullets and large text
- Use as many animations as possible
- Use light yellow text on a white background
= Clear, large text and one idea at a time help the audience follow.

Q: Where can you see your speaker notes while presenting without the audience seeing them?
- In the transition panel
* In Presenter view
- In the comments
- In the file name
= Presenter view shows notes and a timer on your screen only.

Q: Which key leaves the slideshow?
- Tab
- Enter
- F1
* Esc
= Press Esc to leave the presentation mode.

Q: In Google Forms, which question type lets respondents choose more than one answer?
- Multiple choice
* Checkboxes
- Short answer
- Linear scale
= Checkboxes allow several selections; multiple choice allows only one.

Q: What can you do with a Form's responses? (choose all that apply)
* View summary charts
* Link them to a Google Sheet
* View answers one by one
- Turn them into a Slides theme
= Forms offer summary, question and individual views, and can send answers to Sheets.

Q: You want learners to see their score at the end of a test. What should you do?
- Make the form a quiz and set an answer key
- Print the form
* Switch on quiz mode in Settings, add an answer key and choose how scores are released
- Add a transition
= Quiz mode with an answer key scores the answers automatically.
QUIZ,
        ],
        [
            'title'   => 'Module 5: Meet, Calendar and teamwork',
            'summary' => 'Hold video meetings, plan your time, use Keep and Tasks, work well in a team and secure your account.',
            'lessons' => [
                [
                    'title'   => 'Google Meet: creating, joining and sharing',
                    'minutes' => 14,
                    'body'    => <<<'BODY'
<p>After this lesson you will be able to start and join video meetings in Google Meet, share your screen and understand recording. Meet is built into Gmail and Calendar, so it is easy to use for classes, interviews and team meetings.</p>
<h3>Starting a meeting</h3>
<ol>
<li>Open Google Meet from the app launcher, or visit the Meet page in your browser. On a phone, install the Meet app (or use the Meet tab in the Gmail app, if shown).</li>
<li>Select <strong>New meeting</strong>. You can choose to create a meeting for later, start an instant meeting, or schedule it in Google Calendar.</li>
<li>Copy the meeting link and send it to the participants by email or chat.</li>
<li>Select <strong>Join now</strong>.</li>
</ol>
<h3>Joining a meeting</h3>
<ol>
<li>Click the link in the invitation or enter the meeting code in Meet.</li>
<li>Check your microphone and camera in the preview. Turn off the camera or mute your microphone if you wish.</li>
<li>Select <strong>Join now</strong> or <strong>Ask to join</strong> and wait for the host to admit you.</li>
</ol>
<h3>Meeting controls</h3>
<p>The bar at the bottom includes the microphone, camera, captions, raise hand, <strong>Present now</strong>, more options and a red button to leave. The side panel shows people, chat and activities. You can switch on live captions, which are helpful in noisy places or for those who find listening difficult.</p>
<h3>Sharing your screen</h3>
<ol>
<li>Select <strong>Present now</strong>.</li>
<li>Choose <strong>Your entire screen</strong>, <strong>A window</strong> or <strong>A tab</strong>.</li>
<li>Select the one you want and confirm. A banner shows that you are sharing.</li>
<li>Select <strong>Stop presenting</strong> when you finish.</li>
</ol>
<p>Choose a single window or tab where you can, and close private messages first.</p>
<h3>Recording</h3>
<p>Recording is available on some Google Workspace plans, not on all free accounts, so check what your account offers. If it is available, open the <strong>Activities</strong> menu and choose <strong>Recording</strong>. Everybody in the meeting will see a notice. The recording is later saved in the organiser's Drive. Always tell participants before you record and follow your organisation's privacy rules.</p>
<h3>Hosting well</h3>
<ul>
<li>Share an agenda before the meeting and begin on time.</li>
<li>Use host controls, available to organisers, to mute noisy participants or remove someone who is disrupting.</li>
<li>Use the chat for links and short questions.</li>
<li>Ask people to mute when they are not talking.</li>
<li>End with a short summary of actions, with names and dates.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Create a new meeting and copy the link.</li>
<li>Join it, turn on live captions and test your sound.</li>
<li>Share one window and then stop presenting.</li>
<li>If a friend is available, invite them, practise raising hands and leaving the meeting.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Meet meetings begin with a link or code.</li>
<li>Check the preview, name and settings before joining.</li>
<li>Present a single window or tab to protect privacy.</li>
<li>Recording is plan-dependent and requires participants to know.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Google Calendar, Gmail and appointment scheduling',
                    'minutes' => 14,
                    'body'    => <<<'BODY'
<p>When you finish this lesson you will be able to plan your time with Google Calendar, invite people, set reminders and see how Gmail and Meet connect with it. Good planning means fewer missed meetings and deadlines.</p>
<h3>The Calendar view</h3>
<p>Open Calendar from the app launcher. At the top right you can switch between Day, Week, Month and other views. The small calendar on the left helps you move between dates, and the lower part of the left panel lists your calendars. Each calendar can have its own colour, for example one for work and one for study.</p>
<h3>Creating an event</h3>
<ol>
<li>Click on a time slot, or select <strong>Create</strong> and then <strong>Event</strong>.</li>
<li>Type a clear title, such as "Training: Excel basics".</li>
<li>Check the date and time. Tick <strong>All day</strong> if there is no time.</li>
<li>Select <strong>Add guests</strong> and enter their email addresses.</li>
<li>Select <strong>Add Google Meet video conferencing</strong> if the event is online, or add a location if it is physical.</li>
<li>Add a description or agenda.</li>
<li>Select <strong>Save</strong>. Choose whether to send invitations to the guests.</li>
</ol>
<h3>Reminders and notifications</h3>
<p>Inside the event, set one or more <strong>notifications</strong>, for example 10 minutes and 1 day before. They can appear as pop-ups on your phone or computer, or as emails. Install the Calendar app on your phone to receive them.</p>
<h3>Responding to invitations</h3>
<p>When someone invites you, a message arrives in Gmail with <strong>Yes, No, Maybe</strong> buttons. Choosing one updates the organiser. If you accept, the event is added to your calendar. Reply promptly so that the organiser can plan.</p>
<h3>Finding a time that suits everyone</h3>
<p>While creating an event, the <strong>Find a time</strong> tab shows the free and busy times of guests in your organisation, if they share their calendars. For other guests you can ask them for suitable times. Remember that people have different working hours.</p>
<h3>Appointment schedules</h3>
<p>On many accounts, Calendar offers <strong>appointment schedules</strong> (the feature may depend on the type of account). Instead of arranging times by many messages, you create a page with your available slots, such as consultation times for students. People pick a slot, and the booking appears in your calendar automatically. Look for <strong>Appointment schedule</strong> when you create an item. If you do not see it, your account may not offer it.</p>
<h3>Gmail integration</h3>
<ul>
<li>Event details in an email, such as a flight or a meeting, can be added to Calendar with a button in the message.</li>
<li>Meetings you schedule can include Meet links, and guests receive them by email.</li>
<li>Gmail, Calendar, Meet and Tasks are available in the side panel in Gmail.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Create a calendar event for a study session tomorrow with a reminder 15 minutes before.</li>
<li>Add a second event, an online meeting, with a Meet link, and invite a friend.</li>
<li>Create a new calendar named "Study" and give it a colour.</li>
<li>Check if your account offers appointment schedules.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Calendar shows events in Day, Week and Month views and syncs to all devices.</li>
<li>Add guests, a Meet link or location, and reminders to every event.</li>
<li>Respond to invitations promptly with Yes, No or Maybe.</li>
<li>Appointment schedules let others book your free time, where available.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Google Keep, Tasks and working in a team',
                    'minutes' => 14,
                    'body'    => <<<'BODY'
<p>After this lesson you will be able to capture quick notes and to-dos with Keep and Tasks, and to set up simple rules that let a team work smoothly in shared files. Teams lose more time through confusion than through lack of skill, so good habits matter.</p>
<h3>Google Keep</h3>
<p>Keep is a simple notes app. You can write a note, make a checklist, add a picture or record a voice note. In Keep, select <strong>Take a note</strong>, type a title and text, and it is saved automatically. Useful features:</p>
<ul>
<li><strong>Checklists</strong>: tick off items such as a shopping list.</li>
<li><strong>Colours and labels</strong>: group notes into topics.</li>
<li><strong>Reminders</strong>: get an alert at a chosen time.</li>
<li><strong>Collaborators</strong>: share a note with someone by email.</li>
<li><strong>Pin</strong>: keep important notes at the top.</li>
<li><strong>Insert into a Doc</strong>: use the menu to copy a note into Google Docs.</li>
</ul>
<h3>Google Tasks</h3>
<p>Tasks is a to-do list with due dates. Open it from the side panel in Gmail or Calendar, or install the app. Select <strong>Add a task</strong>, type a title, add a date, and tick it when done. You can create several lists, for example "Work" and "School". Tasks with dates also appear in Calendar, so your deadlines sit beside your meetings.</p>
<table>
<thead><tr><th>Tool</th><th>Best for</th></tr></thead>
<tbody>
<tr><td>Keep</td><td>Quick notes, ideas, shopping lists, voice notes</td></tr>
<tr><td>Tasks</td><td>Things to do with a due date</td></tr>
<tr><td>Calendar</td><td>Events at a specific time</td></tr>
<tr><td>Docs</td><td>Longer written work</td></tr>
</tbody>
</table>
<h3>Good habits for teamwork</h3>
<ol>
<li><strong>One home for files.</strong> Create one shared folder for the team and keep all project files in it.</li>
<li><strong>Naming rules.</strong> Agree on a pattern, for example <code>2026-05-Project-Report-v2</code>.</li>
<li><strong>Folder structure.</strong> Use simple subfolders such as Drafts, Final, Data and Meetings.</li>
<li><strong>Right permissions.</strong> Give most people Commenter or Viewer, and Editor only to those writing. Name one person as the owner of each document.</li>
<li><strong>Use comments, not copies.</strong> Work in the same file instead of emailing versions.</li>
<li><strong>Write down decisions.</strong> Put meeting notes and action lists in a shared document, with names and dates.</li>
<li><strong>Keep communication in the right channel.</strong> Use email for formal messages, chat for quick questions and Meet for discussion.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Make a Keep checklist of five things you need this week and pin it.</li>
<li>Add three tasks with due dates in Tasks and look for them in Calendar.</li>
<li>Create a team folder structure with Drafts, Final and Meetings, and share it with a partner as Editor.</li>
<li>Write a short naming rule for your team in a Google Doc inside the folder.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Keep is for fast notes; Tasks is for to-dos with dates.</li>
<li>Teams need a shared folder, naming rules and clear permissions.</li>
<li>Work in one shared file and use comments instead of sending copies.</li>
<li>Write down decisions and actions with names and dates.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Security: 2-step verification and a final team project',
                    'minutes' => 16,
                    'body'    => <<<'BODY'
<p>By the end of this lesson you will have secured your Google account with 2-step verification and completed a checklist for a small team project that uses everything in this course. Your account holds your email, documents and contacts, so protecting it protects your work and your identity.</p>
<h3>Why accounts get stolen</h3>
<p>Most accounts are stolen through weak passwords, the same password used on many sites, and phishing, which is a trick message or fake page that asks for your password. Once a thief has your password, they can read your email and open every document in your Drive.</p>
<h3>Turning on 2-step verification</h3>
<ol>
<li>Open your profile picture menu and select <strong>Manage your Google Account</strong>.</li>
<li>Open the <strong>Security</strong> section.</li>
<li>Find <strong>2-Step Verification</strong> and select <strong>Get started</strong>.</li>
<li>Follow the steps to add your phone. Google may send a prompt to your phone, or you can use a code from an authenticator app or a text message.</li>
<li>Save the backup codes in a safe place, such as written on paper kept at home. They help if you lose your phone.</li>
</ol>
<p>After this, signing in from a new device needs both your password and your phone. Even a thief who knows your password cannot enter.</p>
<h3>Other security habits</h3>
<ul>
<li>Use a long, unique password for your Google account. A password manager can help.</li>
<li>Check your sign-in activity in the Security section and sign out of devices you do not recognise.</li>
<li>Never type your password on a page you reached through a link in an email. Open Google yourself.</li>
<li>Keep your recovery phone and email up to date.</li>
<li>Sign out on shared computers and do not tick "remember me" there.</li>
<li>Be careful before allowing an app or website to "Sign in with Google" or access your Drive, and review them under Security.</li>
<li>Keep your phone and browser updated.</li>
</ul>
<h3>Final team project</h3>
<p>Work with one or two partners to run a small event, for example a clean-up day or a school open day. Use this checklist:</p>
<ul>
<li>Create a shared Drive folder with Drafts, Final and Meetings subfolders, with correct permissions.</li>
<li>Write a one-page plan in Docs using headings, a table of tasks and a table of contents.</li>
<li>Build a budget in Sheets with formulas for totals, a drop-down for status, and a chart.</li>
<li>Create a short Slides deck of five slides to present the plan.</li>
<li>Build a Forms registration or feedback form and link it to Sheets.</li>
<li>Schedule the event and a planning meeting in Calendar with a Meet link and reminders.</li>
<li>Hold the planning meeting in Meet, share your screen and write notes in a Doc.</li>
<li>Check that every member has 2-step verification on.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Turn on 2-step verification for your account and save your backup codes.</li>
<li>Review the devices that have signed in to your account.</li>
<li>Complete at least four items of the project checklist with a partner.</li>
<li>Present the result to your group in a short Meet session.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>2-step verification is the most effective protection for your account.</li>
<li>Use unique passwords, review devices and avoid fake sign-in pages.</li>
<li>Combine Drive, Docs, Sheets, Slides, Forms, Calendar and Meet in real projects.</li>
<li>Regular practice turns these tools into habits.</li>
</ul>
BODY,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What do you need to join a Google Meet meeting?
- A special printer
* A meeting link or code and a device with internet
- A paid Workspace account in every case
- A Slides presentation
= A link or code is enough to join. Free accounts can join many meetings.

Q: You want to show only your presentation, not your private messages. What should you share?
- Your entire screen
* A single window or tab
- Nothing at all
- The Drive folder
= Sharing one window or tab limits what others can see.

Q: Before recording a meeting you should:
- Record without telling anyone
* Tell the participants and check that your plan allows recording
- Delete the chat
- Share your password
= People must know they are being recorded, and recording is available only on some plans.

Q: Which is the best tool for a reminder with a due date, like "Submit report by Friday"?
- Slides
* Tasks
- Meet
- Forms
= Tasks is built for to-dos with dates.

Q: What does a Calendar event with a Meet link allow?
- Guests can edit your password
- It automatically records the meeting
* Guests can join the video call by clicking the link in the event or email
- It deletes the event after the call
= The Meet link is included in the event so that guests can join easily.

Q: Which is a good team habit for shared files?
- Email many versions of the same file
* Keep files in one shared folder with agreed naming rules
- Give everybody Owner access
- Use random file names
= One folder with agreed names and the right permissions prevents confusion.

Q: What does 2-step verification do?
- It makes passwords unnecessary
* Asks for a second proof, such as your phone, after the password
- Deletes old emails
- Shares your account with a friend
= Even if someone learns your password, they cannot sign in without the second step.

Q: Where should you keep your 2-step verification backup codes?
- In an email to yourself
- On a social media post
* In a safe place such as paper kept at home
- In the shared team folder
= Backup codes are secret recovery keys. Do not store them where others can find them.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What is the best username for a professional Google account?
- coolboy_2008
* grace.achan
- kingofgulu
- sweetgirl123
= A real name looks professional.

Q: Which feature opens a menu of all Google apps?
- The Trash
- The Share button
* The app launcher
- The address bar
= The nine-dot app launcher lists apps.

Q: Which Drive section lists files that other people have shared with you?
- My Drive
* Shared with me
- Starred
- Trash
= Shared files appear in Shared with me.

Q: Which sharing setting is the safest for a file that contains private information?
* Restricted
- Anyone with the link as Viewer
- Anyone with the link as Editor
- Public
= Restricted means only listed people can open it.

Q: What can a person with Commenter permission do?
- Edit the text freely
* Read and add comments
- Delete the file permanently
- Change who else has access
= Commenters can read and comment but not edit.

Q: What must you do to work offline in Docs?
- Nothing; it always works offline
* Turn on offline access while online and mark files available offline
- Print every file
- Delete the cache
= Offline access has to be set up in advance.

Q: A shared drive differs from a personal folder because:
- It only works on phones
* Files belong to the team, not to one person
- It is always free for every account
- It cannot be shared
= Shared drives keep files in the team space if a member leaves.

Q: How do you create a table of contents in Docs?
- Type the page numbers by hand
* Apply heading styles, then use Insert and Table of contents
- Use the Voice typing tool
- Change the paper size
= A table of contents is built from heading styles.

Q: In Suggesting mode, a change you make:
- Is permanent at once
* Appears as a suggestion that the owner can accept or reject
- Is deleted
- Is hidden from everyone
= Suggestions allow review before changes are accepted.

Q: What is version history for?
- Printing a document
* Viewing and restoring earlier versions of a file
- Changing the language
- Adding images
= It shows previous versions and who made changes.

Q: Which shortcut starts voice typing in Docs on Windows?
- Ctrl+B
- Ctrl+P
* Ctrl+Shift+S
- Ctrl+Z
= Ctrl+Shift+S toggles voice typing in supported browsers, and you can also use the Tools menu.

Q: When should you export a document as PDF?
- When others must keep editing it in Word
* When you want a final version with a fixed layout
- When you want to delete it
- When you want to share it with a smaller font
= PDFs keep the layout and are harder to change.

Q: Which function adds a range of numbers?
- ADDALL
* SUM
- COUNTIF
- IF
= SUM adds the numbers in a range.

Q: What does =COUNTIF(C2:C30,"Present") return?
- The sum of all values
* The number of cells in the range that say Present
- The largest value
- The average
= COUNTIF counts the cells that meet a condition.

Q: What does =IF(B2>=50,"Pass","Fail") return when B2 contains 42?
- Pass
* Fail
- 42
- An error
= 42 is less than 50, so the test is false and the second answer is shown.

Q: In =VLOOKUP(A2,F2:G10,2,FALSE), what does FALSE mean?
- The formula is switched off
* Find an exact match
- Return the first column
- Ignore errors
= FALSE tells VLOOKUP to look for an exact match.

Q: Which formula keeps pointing at cell F1 when you copy it down?
- F1
* $F$1
- =F1+
- (F1)
= Dollar signs make a reference absolute, so it does not move when copied.

Q: Which tool stops users typing anything other than Present, Absent or Late?
- Conditional formatting
* Data validation with a drop-down
- Freeze panes
- Charts
= Data validation limits what can be entered.

Q: What is conditional formatting?
- Changing the page size
* Changing the look of cells automatically according to their values
- Sorting a list
- Setting passwords
= It highlights cells that meet rules you set.

Q: What is a good guide for text on slides?
- Many long paragraphs in small text
* Short points, one main idea per slide, and large readable text
- Bright yellow on white
- Reading everything word for word
= Simple slides help the audience follow.

Q: Which view in Slides shows your notes and timer only to you?
- Grid view
* Presenter view
- Print view
- Outline
= Presenter view helps you deliver without showing your notes.

Q: In Google Forms, which question type is best for picking several answers?
- Short answer
- Multiple choice
* Checkboxes
- Paragraph
= Checkboxes allow more than one selection.

Q: How can you analyse Forms responses with formulas and charts?
- You cannot
* Link the responses to a Google Sheet
- Print every response
- Convert the form to Slides
= Linked responses appear as rows in a spreadsheet.

Q: Which are suitable things to ask in a form? (choose all that apply)
* Name and class
* Event feedback
- Passwords
- Bank PINs
= Never collect passwords or PINs in a form, and ask only for what you need.

Q: How do you join a Google Meet meeting?
- By pressing Ctrl+Q
- By opening the Trash
* By clicking the meeting link or entering the meeting code
- By sharing your password with the host
= Links and meeting codes let you join.

Q: Why is it better to share one window rather than the entire screen?
- It is quicker to type
* Others do not see private messages or other open items
- It is the only way to present
- It saves electricity
= Sharing one window keeps your private items out of sight.

Q: Which Google tool is built for a to-do list with due dates?
- Slides
* Tasks
- Forms
- Sheets only
= Tasks stores to-dos and shows dates in Calendar.

Q: Which is the best way to protect your Google account? (choose all that apply)
* Turn on 2-step verification
* Use a long, unique password
* Check which devices are signed in
- Share your password with trusted friends
= Never share a password. 2-step verification and unique passwords reduce the risk of theft.

Q: Which team habit prevents confusion about which file is current?
- Emailing copies named "final final 2"
* Working in one shared file in a shared folder with naming rules
- Giving everyone Owner access
- Keeping files only on personal phones
= One shared file and clear rules keep everyone working on the same version.
QUIZ,
];
