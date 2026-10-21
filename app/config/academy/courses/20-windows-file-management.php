<?php
return [
    'slug'        => 'windows-file-management',
    'title'       => 'Using Windows and Managing Your Files',
    'category'    => 'basics',
    'level'       => 'beginner',
    'icon'        => 'layers',
    'sort'        => 20,
    'summary'     => 'Learn to move around Windows with confidence, organise files and folders, personalise your settings and keep your computer healthy.',
    'description' => '<p>Windows is the operating system on most computers in Uganda, and almost everything you do on a PC starts there. This course takes you from the Start menu and taskbar to creating, finding and protecting your files.</p><p>You will learn how to name and organise folders so you never lose a document again, how to use flash drives and cloud storage safely, how to change settings such as the wallpaper, language and Wi-Fi, and how to keep Windows updated, secure and running smoothly.</p><p>The steps are written for Windows 11, with notes where Windows 10 differs. Each lesson ends with a practical activity, so keep a computer next to you.</p>',
    'outcomes'    => [
        'Use the Start menu, taskbar, search and Settings to find and open anything.',
        'Manage several windows with snap layouts and virtual desktops.',
        'Create, rename, copy, move, delete and restore files and folders.',
        'Read file types and extensions and choose the right program to open a file.',
        'Organise your work with a clear folder structure and good file names.',
        'Use zip files, flash drives and cloud storage safely.',
        'Change display, sound, language, accessibility, printer and Wi-Fi settings.',
        'Keep Windows updated and secure, and fix common problems such as frozen programs.',
    ],
    'requirements' => ['A computer running Windows 10 or Windows 11', 'Basic mouse and keyboard skills (the course Computer Basics will help)'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Microsoft Support: Windows help and learning', 'url' => 'https://support.microsoft.com/windows'],
        ['title' => 'Microsoft Learn', 'url' => 'https://learn.microsoft.com/training/'],
    ],
    'modules' => [
        [
            'title'   => 'Module 1: The Windows desktop',
            'summary' => 'The Start menu, taskbar, search, multitasking and a first look at Settings.',
            'lessons' => [
                [
                    'title'   => 'The Start menu, taskbar and search',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to find and open any program or setting on Windows in a few seconds. This matters because the Start menu and taskbar are the control centre of your computer, and learning them well saves time every day.</p>
<h3>The taskbar</h3>
<p>The <strong>taskbar</strong> is the strip along the bottom of the screen. From left to right (in Windows 11 the icons are centred by default) you will see:</p>
<ul>
<li><strong>Start button:</strong> the Windows logo, which opens the Start menu.</li>
<li><strong>Search:</strong> a box or magnifying glass to search your computer and the web.</li>
<li><strong>Task View:</strong> shows all open windows and your virtual desktops.</li>
<li><strong>Pinned and open programs:</strong> icons you can click to open or switch to a program. An open program has a small line underneath.</li>
<li><strong>System tray (notification area):</strong> on the right, with the Wi-Fi, volume and battery icons, the date and time, and hidden icons behind a small arrow.</li>
</ul>
<h3>The Start menu</h3>
<p>Press the <strong>Windows key</strong> or click the Start button. In Windows 11 you will see <strong>Pinned</strong> apps at the top, <strong>Recommended</strong> files below, and a button for <strong>All apps</strong>. At the bottom are your account picture and the <strong>Power</strong> button. In Windows 10 the left side lists apps and the right side shows tiles.</p>
<p>To pin a program you use often, find it in <strong>All apps</strong>, right-click it and choose <strong>Pin to Start</strong> or <strong>Pin to taskbar</strong>. To remove a pin, right-click and choose <strong>Unpin</strong>.</p>
<h3>Search: the quickest way</h3>
<ol>
<li>Press the <strong>Windows key</strong> and start typing at once, for example "calc".</li>
<li>Windows shows matching apps, files and settings. The best match is at the top.</li>
<li>Press <strong>Enter</strong> to open it.</li>
</ol>
<p>Search finds apps ("Notepad"), settings ("Wi-Fi") and your files ("budget"). You can also use it to look up a word on the web.</p>
<h3>The system tray</h3>
<p>Click the Wi-Fi or volume icons to open <strong>Quick Settings</strong> (in Windows 11), where you can connect to Wi-Fi, change volume, turn on Airplane mode or save battery. Click the date and time to see the calendar and your notifications. Click the small arrow to see hidden icons, which are usually programs running in the background.</p>
<h3>Right-click tricks</h3>
<p>Right-click the Start button to open a quick menu with links to Settings, Task Manager, File Explorer and the shut down options. Right-click an empty part of the taskbar to open taskbar settings.</p>
<h3>Try it yourself</h3>
<ol>
<li>Press the Windows key, type "notepad" and press Enter.</li>
<li>Right-click the Notepad icon on the taskbar and choose <strong>Pin to taskbar</strong>.</li>
<li>Click the Wi-Fi icon in the system tray and look at the networks listed (do not change anything yet).</li>
<li>Right-click the Start button and find the <strong>Settings</strong> link.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The taskbar holds Start, search, open programs and the system tray.</li>
<li>Pin your favourite programs so they are one click away.</li>
<li>Press the Windows key and type to find almost anything.</li>
<li>The system tray gives quick access to Wi-Fi, volume, battery and the clock.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Working with many windows: snap, Task View and virtual desktops',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn to arrange several windows on one screen and to organise your work on separate desktops. This is useful when you copy information from a web page into a document, or when you study while keeping a calculator open next to your notes.</p>
<h3>Snapping windows</h3>
<p><strong>Snap</strong> places a window neatly on one half or quarter of the screen. There are two easy ways.</p>
<ol>
<li><strong>Drag:</strong> drag a window by its title bar to the left or right edge of the screen until an outline appears, then let go. The window fills that half. Windows then offers other open windows to fill the other half.</li>
<li><strong>Keyboard:</strong> hold the <strong>Windows key</strong> and press the <strong>Left</strong> or <strong>Right arrow</strong>. Windows key + <strong>Up</strong> maximises and Windows key + <strong>Down</strong> restores or minimises.</li>
</ol>
<p>In Windows 11, hover over the maximise button on a window and a box of <strong>snap layouts</strong> appears; click a zone to place the window there. This lets you arrange two, three or four windows at once.</p>
<h3>Switching windows</h3>
<table>
<thead><tr><th>Shortcut</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Alt + Tab</td><td>Cycle through open windows</td></tr>
<tr><td>Windows key + Tab</td><td>Open Task View with all windows and desktops</td></tr>
<tr><td>Windows key + D</td><td>Show or hide the desktop</td></tr>
<tr><td>Windows key + M</td><td>Minimise all windows</td></tr>
<tr><td>Alt + F4</td><td>Close the current window</td></tr>
</tbody>
</table>
<h3>Virtual desktops</h3>
<p>A <strong>virtual desktop</strong> is a separate, empty workspace. You can keep school work on one and personal things on another, and switch between them. This keeps the screen tidy.</p>
<ol>
<li>Press <strong>Windows key + Tab</strong> to open Task View.</li>
<li>Click <strong>New desktop</strong> (or <strong>Add desktop</strong>) at the top or bottom.</li>
<li>Open programs on that desktop as you normally would.</li>
<li>Switch between desktops with <strong>Ctrl + Windows key + Left or Right arrow</strong>, or by clicking them in Task View.</li>
<li>To close a desktop, hover over it in Task View and click the small X. Its windows move to the previous desktop.</li>
</ol>
<h3>Moving and resizing windows</h3>
<p>Drag the title bar to move a window. To resize, place the pointer on an edge or corner until it turns into a double arrow, then drag. Double-clicking the title bar toggles between full-screen and the earlier size.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open Notepad and Calculator. Snap Notepad to the left half and Calculator to the right using the Windows key and arrows.</li>
<li>Press <strong>Windows key + Tab</strong> and create a new desktop.</li>
<li>Open File Explorer on the new desktop, then switch back to the first desktop using the keyboard.</li>
<li>Close the extra desktop when you are finished.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Snap windows by dragging them to an edge or using the Windows key and arrow keys.</li>
<li>Alt + Tab and Windows key + Tab switch between windows.</li>
<li>Virtual desktops keep different tasks separate and tidy.</li>
<li>Resize by dragging an edge; double-click the title bar to maximise.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'A first look at Settings',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>You will learn how to open the Settings app and understand its main sections. After this lesson you will be able to find the option you need, whether it is Wi-Fi, updates or apps, without searching blindly.</p>
<h3>Opening Settings</h3>
<p>There are three quick ways to open <strong>Settings</strong>:</p>
<ul>
<li>Press <strong>Windows key + I</strong>.</li>
<li>Click Start, then the gear icon (or <strong>Settings</strong> in the pinned apps).</li>
<li>Right-click the Start button and choose <strong>Settings</strong>.</li>
</ul>
<h3>The main sections</h3>
<table>
<thead><tr><th>Section</th><th>What you can do there</th></tr></thead>
<tbody>
<tr><td>System</td><td>Display, sound, notifications, power and battery, storage, and About (computer name and specification)</td></tr>
<tr><td>Bluetooth and devices</td><td>Add Bluetooth devices, printers and scanners; mouse and touchpad options</td></tr>
<tr><td>Network and internet</td><td>Wi-Fi, Ethernet, airplane mode and data usage</td></tr>
<tr><td>Personalisation</td><td>Wallpaper, colours, themes, lock screen, Start and taskbar</td></tr>
<tr><td>Apps</td><td>Installed apps, default apps and start-up apps</td></tr>
<tr><td>Accounts</td><td>Your account, sign-in options, family and other users</td></tr>
<tr><td>Time and language</td><td>Date, time, region, language and keyboard</td></tr>
<tr><td>Accessibility</td><td>Text size, narrator, magnifier, captions, contrast</td></tr>
<tr><td>Privacy and security</td><td>App permissions, Windows Security</td></tr>
<tr><td>Windows Update</td><td>Check for and install updates</td></tr>
</tbody>
</table>
<p>In Windows 10, the sections are shown as large tiles on the first screen and are named a little differently, for example <strong>Update and Security</strong> and <strong>Devices</strong>.</p>
<h3>Using the search box in Settings</h3>
<p>At the top of Settings is a search box. If you do not know where an option is, type a word such as "brightness" or "default printer" and click the result. This is often faster than browsing.</p>
<h3>Control Panel</h3>
<p>Older Windows tools live in the <strong>Control Panel</strong>. Press the Windows key, type "control panel" and press Enter. Most tasks are now in Settings, but some, such as certain printer or sound options, still use the Control Panel.</p>
<h3>Be careful with changes</h3>
<p>Settings is safe to explore, but change only what you understand. Note the original value before changing something, so you can return to it. If the screen suddenly looks wrong after a change, undo it. Windows often asks you to confirm a display change within 15 seconds and reverts if you do nothing.</p>
<h3>Try it yourself</h3>
<ol>
<li>Press <strong>Windows key + I</strong> to open Settings.</li>
<li>Go to <strong>System</strong>, then <strong>About</strong>. Write down your computer name and Windows edition.</li>
<li>Use the search box to find "brightness" and adjust it a little (on a laptop or screen that allows it), then set it back.</li>
<li>Browse the Accessibility page to see which options exist.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Windows key + I opens Settings immediately.</li>
<li>Settings is grouped into System, Devices, Network, Personalisation, Apps, Accounts, Time and language, Accessibility, Privacy and Update.</li>
<li>The search box inside Settings finds options quickly.</li>
<li>Note original values before you change them.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the fastest way to open a program you cannot find on the screen?
- Restart the computer
* Press the Windows key, type its name and press Enter
- Delete and reinstall Windows
- Look through every folder on the drive
= Typing after pressing the Windows key searches apps, files and settings at once.

Q: What is the system tray (notification area)?
- The folder where documents are stored
* The area on the right of the taskbar with icons like Wi-Fi, volume, battery and the clock
- A menu for printing
- A place where deleted files wait
= The tray shows the status of important things and gives quick access to them.

Q: Which shortcut opens Settings directly?
- Windows key + S
* Windows key + I
- Ctrl + Alt + S
- Alt + F4
= Windows key + I is the shortcut for Settings. Windows key + S opens search.

Q: How do you snap the current window to the left half of the screen with the keyboard?
- Alt + Left arrow
- Ctrl + Left arrow
* Windows key + Left arrow
- Shift + Left arrow
= Windows key with an arrow key snaps the window to the left, right or full screen.

Q: What is a virtual desktop useful for?
- Making the computer physically bigger
- Storing deleted files
* Keeping different groups of windows on separate workspaces, such as school and personal tasks
- Connecting to Wi-Fi
= Virtual desktops reduce clutter by separating tasks.

Q: Where would you go to check for Windows updates?
* Settings, then Windows Update
- Task Manager
- The Recycle Bin
- The Start menu pinned apps only
= Windows Update is a section of Settings.

Q: You are not sure where the "default printer" option is in Settings. What is the best approach?
- Give up
* Type "default printer" into the Settings search box
- Reinstall Windows
- Delete your printer
= Searching in Settings takes you to the option directly.
QUIZ,
        ],
        [
            'title'   => 'Module 2: Files and folders',
            'summary' => 'File types, File Explorer, and the everyday actions of creating, copying, moving, deleting and finding files.',
            'lessons' => [
                [
                    'title'   => 'What are files and folders?',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>After this lesson you will understand what files and folders are, how file names and extensions work, and how to recognise common file types. This matters because choosing the wrong file type can mean a document that a teacher, employer or friend cannot open.</p>
<h3>Files and folders</h3>
<p>A <strong>file</strong> is a named piece of information stored on a drive: a letter, a photo, a song or a program. A <strong>folder</strong> is a container that holds files and other folders, like a paper folder in a cabinet. Folders inside folders are called <strong>subfolders</strong>.</p>
<h3>File names and extensions</h3>
<p>A file name has two parts separated by a dot, for example <code>CV-Grace-Akello.docx</code>. The first part is the name you choose. The part after the dot is the <strong>extension</strong>. It tells Windows what kind of file it is and which program to open it with.</p>
<table>
<thead><tr><th>Extension</th><th>Type of file</th><th>Usually opens in</th></tr></thead>
<tbody>
<tr><td>.docx</td><td>Word document</td><td>Microsoft Word</td></tr>
<tr><td>.xlsx</td><td>Spreadsheet</td><td>Microsoft Excel</td></tr>
<tr><td>.pptx</td><td>Presentation</td><td>PowerPoint</td></tr>
<tr><td>.pdf</td><td>Portable document</td><td>Browser or PDF reader</td></tr>
<tr><td>.txt</td><td>Plain text</td><td>Notepad</td></tr>
<tr><td>.jpg or .png</td><td>Picture</td><td>Photos app</td></tr>
<tr><td>.mp3</td><td>Audio</td><td>Media player</td></tr>
<tr><td>.mp4</td><td>Video</td><td>Media player</td></tr>
<tr><td>.zip</td><td>Compressed folder</td><td>File Explorer</td></tr>
<tr><td>.exe</td><td>Program (installer)</td><td>Runs by itself</td></tr>
</tbody>
</table>
<h3>Showing extensions</h3>
<p>Windows may hide extensions. To show them in File Explorer, select <strong>View</strong> (Windows 11: <strong>View</strong>, then <strong>Show</strong>), and tick <strong>File name extensions</strong>. Seeing extensions helps you spot danger: a file called <code>photo.jpg.exe</code> is not a photo, it is a program, and could be a virus.</p>
<h3>Rules for names</h3>
<ul>
<li>A name cannot contain these characters: <code>\ / : * ? " &lt; &gt; |</code></li>
<li>Names can be long, but keep them short and meaningful.</li>
<li>Do not change an extension by hand unless you know what you are doing, because the file may stop opening.</li>
<li>Names are not case sensitive in Windows (Report and report are the same).</li>
</ul>
<h3>Paths</h3>
<p>The location of a file is called its <strong>path</strong>, for example <code>C:\Users\Grace\Documents\School\Essay.docx</code>. This means drive C, the Users folder, Grace's folder, Documents, the School folder, and the file Essay.docx. You do not have to type paths; you simply click through folders.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open File Explorer (Windows key + E) and turn on <strong>File name extensions</strong>.</li>
<li>Open your Documents folder and note the extensions of five files.</li>
<li>Decide what kind of file each is and which program opens it.</li>
<li>Look at the address bar at the top and read the path of the folder you are in.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A file holds information; a folder holds files and other folders.</li>
<li>The extension after the dot shows the file type.</li>
<li>Show extensions so you can spot suspicious files.</li>
<li>A path describes the location of a file through its folders.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'File Explorer tour',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>In this lesson you will take a tour of File Explorer, the program Windows uses to show your drives, folders and files. By the end you will be able to move around your computer and always know where you are.</p>
<h3>Opening File Explorer</h3>
<p>Press <strong>Windows key + E</strong>, or click the yellow folder icon on the taskbar. In Windows 10, it opens at <strong>Quick access</strong>; in Windows 11, it opens at <strong>Home</strong>.</p>
<h3>The parts of the window</h3>
<ul>
<li><strong>Navigation pane (left):</strong> shortcuts to Quick access (or Home), OneDrive, <strong>This PC</strong> and your drives. Common folders include Desktop, Downloads, Documents, Pictures, Music and Videos.</li>
<li><strong>Address bar (top):</strong> shows where you are. Click any folder name in it to jump back.</li>
<li><strong>Back, Forward and Up arrows:</strong> work like in a web browser.</li>
<li><strong>Search box (top right):</strong> searches inside the current folder.</li>
<li><strong>Command bar or ribbon:</strong> buttons such as New, Cut, Copy, Paste, Rename, Delete, Sort and View.</li>
<li><strong>Main area:</strong> shows the contents of the folder.</li>
<li><strong>Status bar (bottom):</strong> shows how many items are there and which are selected.</li>
</ul>
<h3>This PC and drives</h3>
<p>Click <strong>This PC</strong> to see your drives. <strong>Local Disk (C:)</strong> is the main drive where Windows lives. Other letters such as D: or E: may be extra drives, a memory card or a flash drive. A bar shows how full each drive is.</p>
<h3>Your personal folders</h3>
<p>Windows gives each user a set of folders. Save your own work in <strong>Documents</strong>, photos in <strong>Pictures</strong>, and things you download from the web land in <strong>Downloads</strong>. The <strong>Desktop</strong> is also a folder; avoid piling many files there because it slows the computer and gets messy.</p>
<h3>Changing the view</h3>
<p>Use <strong>View</strong> to change how files appear: large icons for pictures, <strong>List</strong> or <strong>Details</strong> for documents. Details view shows columns for name, date modified, type and size, which is the best view for work. You can click a column title to sort by it.</p>
<h3>Navigation habits</h3>
<ol>
<li>Double-click a folder to go in.</li>
<li>Click the <strong>Up</strong> arrow, or press <strong>Backspace</strong> or <strong>Alt + Left arrow</strong>, to go back.</li>
<li>Click a folder in the address bar to jump to it.</li>
<li>Type a folder path in the address bar and press Enter if you know it.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Open File Explorer and click <strong>This PC</strong>. Which drives do you have, and how much space is free on C:?</li>
<li>Open <strong>Documents</strong>, then switch to <strong>Details</strong> view.</li>
<li>Click the <strong>Date modified</strong> column title twice and see how the order changes.</li>
<li>Use the address bar to go back to <strong>This PC</strong> in one click.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Windows key + E opens File Explorer.</li>
<li>The left pane shows shortcuts and drives; the address bar shows your location.</li>
<li>Save your work in Documents rather than on the Desktop.</li>
<li>Details view with sortable columns helps you manage files.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Creating, renaming, copying, moving and deleting',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>This lesson teaches the five actions you will use on files every day: create, rename, copy, move and delete. By the end you will be able to handle your files without fear of losing them, and you will know how to rescue a file you deleted by mistake.</p>
<h3>Creating a folder</h3>
<ol>
<li>Open the place where the folder should be, for example Documents.</li>
<li>Click <strong>New</strong> in the command bar, then <strong>Folder</strong>. (Or right-click an empty space, choose <strong>New</strong>, then <strong>Folder</strong>. Shortcut: <strong>Ctrl + Shift + N</strong>.)</li>
<li>Type a name such as "School" and press <strong>Enter</strong>.</li>
</ol>
<h3>Renaming</h3>
<p>Click the item once to select it, then press <strong>F2</strong> (or right-click and choose <strong>Rename</strong>). Type the new name and press Enter. If extensions are visible, change only the part before the dot.</p>
<h3>Copy, cut and paste</h3>
<table>
<thead><tr><th>Action</th><th>Shortcut</th><th>Result</th></tr></thead>
<tbody>
<tr><td>Copy</td><td>Ctrl + C</td><td>Makes a duplicate when pasted; the original stays</td></tr>
<tr><td>Cut</td><td>Ctrl + X</td><td>Picks up the item to move it; it moves when pasted</td></tr>
<tr><td>Paste</td><td>Ctrl + V</td><td>Puts the copied or cut item in the current folder</td></tr>
</tbody>
</table>
<p>To move a file: select it, press Ctrl + X, open the new folder and press Ctrl + V. To copy it: use Ctrl + C instead. You can also use <strong>drag and drop</strong>. Dragging to another folder on the same drive moves the file; dragging to a different drive copies it. Hold <strong>Ctrl</strong> while dragging to force a copy.</p>
<h3>Selecting many files</h3>
<ul>
<li>Click the first file, hold <strong>Shift</strong> and click the last to select a range.</li>
<li>Hold <strong>Ctrl</strong> and click to select separate items.</li>
<li>Press <strong>Ctrl + A</strong> to select everything in the folder.</li>
<li>Drag a box around files with the mouse to select the ones inside.</li>
</ul>
<h3>Deleting and the Recycle Bin</h3>
<p>Select a file and press <strong>Delete</strong>. It is moved to the <strong>Recycle Bin</strong> on the desktop, not destroyed. To bring it back, open the Recycle Bin, right-click the file and choose <strong>Restore</strong>. The file returns to its original place. To really remove everything, right-click the bin and choose <strong>Empty Recycle Bin</strong>. After that, recovery is very difficult, so check first. <strong>Shift + Delete</strong> skips the bin and removes the file at once, and files deleted from a flash drive or network location usually do not go to the bin at all.</p>
<h3>Undo</h3>
<p>If you moved, copied, renamed or deleted something by accident, press <strong>Ctrl + Z</strong> straight away to undo it.</p>
<h3>Try it yourself</h3>
<ol>
<li>In Documents, create a folder named "Practice".</li>
<li>Inside it, create a text document (right-click, <strong>New</strong>, <strong>Text Document</strong>) called "note".</li>
<li>Copy the note, paste it, and rename the copy "note-copy".</li>
<li>Delete "note-copy", open the Recycle Bin and restore it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Create with Ctrl + Shift + N, rename with F2.</li>
<li>Copy keeps the original; cut moves it.</li>
<li>Use Shift and Ctrl to select several items.</li>
<li>Deleted files go to the Recycle Bin and can be restored until it is emptied.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Shortcuts, sorting and searching for files',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn how to find a file quickly, even when you cannot remember where you put it, and how to create shortcuts that open things in one click. These skills save hours over a year of study or work.</p>
<h3>Sorting and grouping</h3>
<p>Click <strong>Sort</strong> in the command bar to order files by Name, Date modified, Type or Size, in ascending or descending order. In Details view, click any column title. Sort by <strong>Date modified</strong> with the newest first to find the file you worked on yesterday. You can also use <strong>Group by</strong> to place files under headings such as Type.</p>
<h3>Views</h3>
<p>The <strong>View</strong> menu offers Extra large, Large, Medium and Small icons, List, Details, Tiles and Content. Choose large icons for pictures and Details for documents. A <strong>Preview pane</strong> shows a quick look at the selected file without opening it.</p>
<h3>Searching in File Explorer</h3>
<ol>
<li>Open the folder where you think the file is. Searching starts here and includes subfolders.</li>
<li>Click the search box (or press <strong>Ctrl + E</strong>) and type part of the name.</li>
<li>Press Enter. Matches appear while you type.</li>
</ol>
<p>You can use filters in the search box:</p>
<table>
<thead><tr><th>Type this</th><th>To find</th></tr></thead>
<tbody>
<tr><td><code>*.pdf</code></td><td>All PDF files (the star stands for any name)</td></tr>
<tr><td><code>kind:picture</code></td><td>Pictures only</td></tr>
<tr><td><code>modified:today</code></td><td>Files changed today</td></tr>
<tr><td><code>size:&gt;10MB</code></td><td>Files bigger than 10 MB</td></tr>
<tr><td><code>budget</code></td><td>Any file with "budget" in its name or content (when indexed)</td></tr>
</tbody>
</table>
<p>After searching, the <strong>Search</strong> options let you filter by date, kind and size. You can also search from the Start menu: press the Windows key, type the name, and use the filter tabs such as Documents or Photos.</p>
<h3>Shortcuts</h3>
<p>A <strong>shortcut</strong> is a small link to a file, folder or program stored somewhere else. Its icon has a little arrow. Deleting a shortcut does not delete the original. To make one: right-click the item, choose <strong>Show more options</strong> (Windows 11), then <strong>Send to</strong> and <strong>Desktop (create shortcut)</strong>. In Windows 10, right-click, then <strong>Send to</strong>, and <strong>Desktop (create shortcut)</strong>.</p>
<h3>Quick access</h3>
<p>To pin a folder you use often, right-click it and choose <strong>Pin to Quick access</strong>. It appears at the top of the left pane (in Windows 11, under Home). Unpin it the same way when you no longer need it.</p>
<h3>Try it yourself</h3>
<ol>
<li>Go to your Documents folder and sort by Date modified, newest first.</li>
<li>Type <code>*.pdf</code> in the search box to see all PDFs there.</li>
<li>Create a desktop shortcut to your Practice folder.</li>
<li>Pin the Practice folder to Quick access, then unpin it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Sort by date to find recent work; use Details view for the most information.</li>
<li>Search starts in the open folder; use * and filters like kind: to narrow results.</li>
<li>A shortcut is just a link; deleting it does not remove the original.</li>
<li>Pin favourite folders to Quick access.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: In the file name Budget2025.xlsx, what is ".xlsx"?
- The name chosen by the user
* The extension that shows the file is an Excel spreadsheet
- The folder name
- The drive letter
= The extension after the dot tells Windows the file type.

Q: Why is it a good idea to show file name extensions?
- It makes files smaller
* It helps you notice suspicious files such as photo.jpg.exe
- It deletes viruses
- It speeds up the internet
= A program disguised as a picture can be spotted when the real extension is visible.

Q: Which key renames a selected file?
- F1
* F2
- F5
- Esc
= F2 starts renaming the selected item.

Q: What is the difference between Copy and Cut when moving a file?
* Copy leaves the original in place; Cut moves the original to the new place when pasted
- Copy deletes the file; Cut keeps it
- They are exactly the same
- Cut can only be used on pictures
= Copy duplicates; Cut moves.

Q: You deleted a file from your Documents folder yesterday by pressing Delete. How can you probably get it back?
- It is impossible to recover
* Open the Recycle Bin, right-click the file and choose Restore
- Reinstall Windows
- Ask the printer
= Deleted files stay in the Recycle Bin until it is emptied.

Q: How do you select several files that are not next to each other?
- Click them while holding Shift
* Click them while holding Ctrl
- Double-click each one
- Press Ctrl + Z
= Ctrl + click adds or removes single items; Shift selects a continuous range.

Q: You want to find every PDF in a folder. What could you type in the File Explorer search box?
- pdf.*
* *.pdf
- /pdf
- search:pdf
= The star stands for any name, so *.pdf matches all files ending in .pdf.

Q: What happens if you delete a desktop shortcut?
- The original file is deleted too
* Only the link is removed; the original remains
- Windows restarts
- The file moves to a flash drive
= A shortcut is just a pointer to the real item.
QUIZ,
        ],
        [
            'title'   => 'Module 3: Organising and finding',
            'summary' => 'Folder structure and naming habits, zip files, properties, hidden files, USB drives and cloud storage.',
            'lessons' => [
                [
                    'title'   => 'Folder structure and naming habits',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to design a folder structure that fits your life and name your files so that you, and anyone you share them with, can find them in seconds. This matters because many people lose work not to viruses but to messy folders.</p>
<h3>Why organise?</h3>
<p>Imagine a market with no stalls or signs, where everything is thrown in one pile. You would never find your tomatoes. A computer with 500 files on the Desktop is exactly like that. A few minutes of planning gives you years of easy finding.</p>
<h3>Plan your folders</h3>
<p>Think in broad groups first, then smaller ones. A student might use:</p>
<pre>Documents
  School
    Term 1
      Mathematics
      English
    Term 2
  Personal
    CV and Certificates
    Money
  Projects
    Poster for Youth Club</pre>
<p>A small business owner might use folders for Customers, Sales, Stock, Receipts and Reports, with a subfolder for each year. Aim for no more than about 10 folders at any level, and not deeper than four or five levels.</p>
<h3>Good file-naming habits</h3>
<ul>
<li><strong>Be descriptive:</strong> "Chemistry-Notes-Atoms.docx" is better than "New document 3.docx".</li>
<li><strong>Use dates in order year-month-day:</strong> <code>2025-03-14-Meeting-Notes.docx</code> sorts correctly by name.</li>
<li><strong>Avoid spaces problems:</strong> use hyphens or underscores, or capital letters between words.</li>
<li><strong>Avoid vague words</strong> such as "final", "new" or "stuff". If you need versions, add v1, v2 or the date.</li>
<li><strong>Do not use special characters</strong> that Windows does not allow, and avoid very long names.</li>
<li><strong>Be consistent.</strong> Choose one style and keep to it.</li>
</ul>
<table>
<thead><tr><th>Poor name</th><th>Better name</th></tr></thead>
<tbody>
<tr><td>doc1.docx</td><td>Business-Plan-Draft-v2.docx</td></tr>
<tr><td>IMG_2931.jpg</td><td>2025-06-12-Youth-Day-Gulu.jpg</td></tr>
<tr><td>final final.xlsx</td><td>Sales-Report-June-2025.xlsx</td></tr>
</tbody>
</table>
<h3>Where to save</h3>
<p>When you save in a program, the <strong>Save As</strong> window lets you choose the folder. Take a moment to select the right place instead of accepting whatever the program suggests. Move items out of Downloads regularly, because it becomes a dumping ground.</p>
<h3>Weekly tidy-up</h3>
<p>Spend ten minutes each week: move files from Desktop and Downloads into proper folders, delete things you do not need, and rename vague files. Small, regular tidying beats a huge clean-up once a year.</p>
<h3>Try it yourself</h3>
<ol>
<li>Sketch on paper a folder plan for your studies or your business.</li>
<li>Create the main folder and two subfolders for it in Documents.</li>
<li>Rename three files using the date-first naming style.</li>
<li>Move five files from Downloads into your new folders.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Plan broad folders first, then add subfolders.</li>
<li>Use clear, consistent names with the date in year-month-day order.</li>
<li>Avoid vague names such as "new" and "final final".</li>
<li>Tidy a little every week.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Zip files, properties and hidden files',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn to compress files into a zip, read a file's properties and understand hidden files. These tools help you send many files by email, check how big something is, and understand why some files cannot be seen.</p>
<h3>Compressing with zip</h3>
<p>A <strong>zip file</strong> (extension <code>.zip</code>) is a single compressed folder holding one or more files. It uses less space and keeps many files together, which makes it easier to email or upload. Windows has this built in.</p>
<ol>
<li>Select the files or folders you want to compress (use Ctrl or Shift to select several).</li>
<li>Right-click one of them. In Windows 11, choose <strong>Compress to ZIP file</strong>. In Windows 10, choose <strong>Send to</strong>, then <strong>Compressed (zipped) folder</strong>.</li>
<li>Type a name for the new zip file and press Enter.</li>
</ol>
<p>To open a zip you receive, right-click it and choose <strong>Extract All</strong>, pick a destination and select <strong>Extract</strong>. Always extract before using the files inside. Be careful with zip files from unknown senders, because they can hide harmful programs. Photos and documents already in compressed formats such as JPG and PDF shrink very little.</p>
<h3>File properties</h3>
<p>Right-click a file and select <strong>Properties</strong> (or press <strong>Alt + Enter</strong>). The <strong>General</strong> tab shows:</p>
<ul>
<li>The <strong>type</strong> and the program that opens it.</li>
<li>The <strong>location</strong> (the path).</li>
<li>The <strong>size</strong> and <strong>size on disk</strong>.</li>
<li>The dates it was <strong>created</strong>, <strong>modified</strong> and <strong>accessed</strong>.</li>
<li><strong>Attributes</strong> such as <strong>Read-only</strong> (the file cannot be edited) and <strong>Hidden</strong>.</li>
</ul>
<p>For a folder, Properties shows the total size of everything inside. This helps you discover which folders are using your storage.</p>
<h3>Hidden files</h3>
<p>Some files are hidden so that beginners do not delete important system files by accident. To see them, open File Explorer, select <strong>View</strong>, then <strong>Show</strong> (Windows 11) and tick <strong>Hidden items</strong>. Hidden files appear faded. You can also hide your own file by opening Properties and ticking <strong>Hidden</strong>, but this is not security; anyone can show hidden items. Never delete files you do not recognise in the Windows folder or in Program Files.</p>
<h3>Why a file may seem to have disappeared</h3>
<ul>
<li>It was moved to another folder (use search).</li>
<li>It is hidden (show hidden items).</li>
<li>You are looking at a different drive or user account.</li>
<li>It was deleted (check the Recycle Bin).</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Select three files in your Practice folder and make a zip file called "Practice-Files".</li>
<li>Right-click the zip, select Properties, and compare its size with the original files.</li>
<li>Extract the zip to a new folder on your Desktop.</li>
<li>Turn on <strong>Hidden items</strong>, look at your Documents folder, then turn them off.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Zip files bundle and shrink files for easier sharing.</li>
<li>Extract a zip before using the files inside.</li>
<li>Properties reveal size, location, dates and attributes.</li>
<li>Hidden files protect the system; do not delete what you do not know.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'USB flash drives and external storage',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>You will learn to use a USB flash drive and to remove it safely. Flash drives are the most common way to move files in many offices and schools, and removing them wrongly is a leading cause of lost files and damaged drives.</p>
<h3>Plugging in a drive</h3>
<ol>
<li>Push the flash drive straight into a USB port. It fits only one way.</li>
<li>Wait a few seconds. Windows may show a pop-up in the corner or open the drive.</li>
<li>Open File Explorer. The drive appears under <strong>This PC</strong> with a letter such as E: or F:.</li>
<li>Double-click it to see the files.</li>
</ol>
<h3>Scanning before use</h3>
<p>Flash drives that have been used on many computers often carry viruses. Before opening anything, right-click the drive and choose <strong>Scan with Microsoft Defender</strong> (the wording may be slightly different), or open Windows Security and run a scan. Never double-click unknown <code>.exe</code> files on a flash drive, and be careful with shortcuts that suddenly replace your folders, as this is a sign of a common virus.</p>
<h3>Copying files to and from a drive</h3>
<ul>
<li>To copy to the drive: select files, press Ctrl + C, open the flash drive, press Ctrl + V.</li>
<li>To copy from the drive: do the same the other way round.</li>
<li>Or use right-click, <strong>Send to</strong>, then the drive name.</li>
</ul>
<p>Always <strong>copy</strong> rather than cut, so that the files remain on your computer, and keep your only copy of important work in at least two places. A flash drive is not a backup on its own.</p>
<h3>Removing safely</h3>
<ol>
<li>Wait until copying has finished.</li>
<li>Click the small <strong>Safely Remove Hardware</strong> icon in the system tray (it may be under the hidden arrow), then choose <strong>Eject</strong> and the drive name. Or right-click the drive in File Explorer and choose <strong>Eject</strong>.</li>
<li>Wait for the message that it is safe to remove, then pull the drive out.</li>
</ol>
<p>Pulling it out while files are still being written can corrupt the data, which means it becomes unreadable.</p>
<h3>Formatting</h3>
<p>Formatting erases everything on a drive and prepares it for use. Right-click the drive and choose <strong>Format</strong>. Only do this after copying anything you need, and check you picked the right drive, because the action cannot be undone.</p>
<h3>Care of flash drives</h3>
<p>Keep the cap on, avoid water and heat, attach it to a key ring so it is not lost, and label it. Do not leave sensitive documents on a drive that could be lost, or protect them with a password in the program that created them.</p>
<h3>Try it yourself</h3>
<ol>
<li>Insert a flash drive and find it in This PC.</li>
<li>Scan it with Windows Security.</li>
<li>Copy your Practice folder to it.</li>
<li>Eject it using the safe removal option before unplugging.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Scan drives from other people before opening files.</li>
<li>Copy, do not cut, and keep files in at least two places.</li>
<li>Always eject before removing the drive.</li>
<li>Formatting erases all data on the drive.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'OneDrive and cloud storage basics',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will know what cloud storage is, how OneDrive works with Windows, and how to keep a safe online copy of your important files. Cloud storage protects you against theft, loss and damage, and lets you reach your files from a phone or any computer.</p>
<h3>What is the cloud?</h3>
<p>The <strong>cloud</strong> simply means files stored on computers (servers) belonging to a company and reached through the internet. You keep working as normal, and a copy is kept online. Well-known services include Microsoft OneDrive, Google Drive and Dropbox. Most give a small amount of free space, with more available for a fee.</p>
<h3>OneDrive in Windows</h3>
<p>If you sign in to Windows with a Microsoft account, <strong>OneDrive</strong> is built in. You will see a OneDrive folder in the left pane of File Explorer and a cloud icon in the system tray. Files you put in the OneDrive folder are uploaded automatically and are also available at the OneDrive website and on the phone app. Free accounts commonly have 5 GB of space, though this can change, so check your account.</p>
<h3>Status icons</h3>
<table>
<thead><tr><th>Icon</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>Blue cloud</td><td>File is online only and takes no space on your computer until opened</td></tr>
<tr><td>Green tick</td><td>File is stored on your computer and in the cloud</td></tr>
<tr><td>Circling arrows</td><td>File is syncing (uploading or downloading)</td></tr>
</tbody>
</table>
<h3>Uploading and sharing</h3>
<ol>
<li>Copy or move a file into your OneDrive folder in File Explorer.</li>
<li>Wait for the sync icon to show a green tick.</li>
<li>To share, right-click the file and choose <strong>Share</strong>. Choose who can open it, and whether they can edit or only view.</li>
<li>Copy the link or send it by email.</li>
</ol>
<p>Only share with the people who need it, and prefer "specific people" over "anyone with the link" for private documents.</p>
<h3>Folder backup</h3>
<p>OneDrive can back up your Desktop, Documents and Pictures folders. Click the cloud icon in the tray, select the gear icon, then <strong>Settings</strong> and look for the backup option. Check the free space first, as photos fill 5 GB quickly.</p>
<h3>Limits and cautions</h3>
<ul>
<li>You need internet to sync, and uploading large files uses a lot of mobile data. Prefer Wi-Fi.</li>
<li>Files deleted from your OneDrive folder are deleted online too (though a recycle bin exists online for a time).</li>
<li>Protect your Microsoft account with a strong password and two-step verification.</li>
<li>Do not store the only copy of anything very important in one place, even the cloud.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Check whether OneDrive appears in File Explorer. If you have an account, note how much space is free.</li>
<li>Put a small test file in the OneDrive folder and watch its status icon.</li>
<li>Open the OneDrive website on your phone or another computer and see whether the file is there.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Cloud storage keeps files online so they are safe and reachable anywhere.</li>
<li>OneDrive syncs a folder automatically; check the status icons.</li>
<li>Share carefully and choose view-only when editing is not needed.</li>
<li>Use Wi-Fi for large uploads and protect your account.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which file name is the best choice for a meeting note written on 14 March 2025?
- note.docx
- meeting final final.docx
* 2025-03-14-Meeting-Notes.docx
- 14/3/2025 notes.docx
= Year-month-day names sort in order, and the description makes the file easy to recognise. The slash is not allowed in a file name.

Q: Why do Windows folders such as Desktop and Downloads need regular tidying?
- Windows deletes them automatically
* They collect clutter that makes files hard to find and can slow the computer
- They cannot hold subfolders
- They are used only by the printer
= A weekly tidy-up keeps work organised.

Q: What is the main advantage of a zip file?
* It bundles and compresses files so they are easier to store or send
- It repairs damaged files
- It protects against all viruses
- It makes photos sharper
= A zip puts many files in one smaller package.

Q: How do you see the size and creation date of a file?
- Press Ctrl + Z
* Right-click it and choose Properties
- Double-click the Recycle Bin
- Rename it
= The Properties window shows details such as size, location and dates.

Q: A flash drive from a friend shows your folders as shortcuts and you cannot find your files. What should you do?
- Double-click the shortcuts quickly
* Do not run them; scan the drive with Windows Security and ask for help
- Format your computer
- Share the drive with others
= This is a typical sign of a virus on a flash drive. Avoid opening the shortcuts until you have scanned the drive.

Q: Why should you eject a flash drive before pulling it out?
- To make the drive faster
* To make sure all data has finished writing so files are not corrupted
- To charge the drive
- To change its letter
= Removing a drive during writing can damage files.

Q: In OneDrive, what does a green tick on a file mean?
- It has been deleted
- It is waiting to upload
* It is stored both on your computer and online
- It is shared with everyone
= A green tick means the file is available on the device and synced.

Q: Which are good habits when using cloud storage? (choose all that apply)
* Use a strong password and two-step verification
* Share with specific people when the file is private
- Share every file with "anyone with the link"
- Keep your only copy of a vital file in one place
= Protecting the account and sharing carefully keep your files safe.
QUIZ,
        ],
        [
            'title'   => 'Module 4: Personalising and settings',
            'summary' => 'Display and appearance, sound, language, time, accessibility, accounts, printers and Wi-Fi.',
            'lessons' => [
                [
                    'title'   => 'Display, wallpaper, themes and sound',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>In this lesson you will make Windows look and sound the way you want and comfortable for your eyes. You will be able to change the wallpaper, brightness, text size and volume, and understand why a few small changes make long hours at a computer easier.</p>
<h3>Changing the wallpaper and theme</h3>
<ol>
<li>Right-click an empty part of the desktop and choose <strong>Personalise</strong> (or open Settings, then <strong>Personalisation</strong>).</li>
<li>Select <strong>Background</strong>. Choose a picture, a solid colour or a slideshow.</li>
<li>To use your own photo, choose <strong>Browse photos</strong> and pick one. Use a clear, simple picture so icons are easy to see.</li>
<li>Select <strong>Themes</strong> for a ready-made set of wallpaper and colours.</li>
</ol>
<p>Under <strong>Colours</strong>, switch between <strong>Light</strong> and <strong>Dark</strong> mode. Dark mode can be easier on the eyes in a dim room. Under <strong>Lock screen</strong>, you can pick the picture shown before you sign in.</p>
<h3>Display settings</h3>
<p>Open Settings, then <strong>System</strong>, then <strong>Display</strong>:</p>
<ul>
<li><strong>Brightness:</strong> lower it in a dark room and raise it in bright light. A bright screen in a dark room tires the eyes.</li>
<li><strong>Night light:</strong> gives the screen a warmer, yellow tone in the evening.</li>
<li><strong>Scale:</strong> makes text and apps bigger or smaller. 100 percent is the standard; 125 percent suits small high-resolution screens.</li>
<li><strong>Display resolution:</strong> use the one marked <strong>Recommended</strong>, which matches your screen.</li>
<li><strong>Multiple displays:</strong> if you connect a projector or second monitor, choose to duplicate or extend your screen. Press <strong>Windows key + P</strong> to switch quickly.</li>
</ul>
<h3>Taskbar and Start options</h3>
<p>In Settings, <strong>Personalisation</strong>, then <strong>Taskbar</strong>, you can hide icons you do not use and, in Windows 11, move the taskbar icons to the left. Under <strong>Start</strong> you can choose which folders appear next to the power button.</p>
<h3>Sound</h3>
<p>Click the speaker icon in the tray to change volume. For more, open Settings, <strong>System</strong>, then <strong>Sound</strong>. You can choose the output device (speakers or headphones), the input device (microphone) and test them. If you hear nothing, check that the volume is not muted, the right output device is selected, and the headphones are plugged in fully. Press <strong>Windows key + Ctrl + V</strong>, on newer Windows 11 versions, for a per-app volume mixer (or look for Volume mixer in Sound settings).</p>
<h3>Notifications</h3>
<p>Under System, <strong>Notifications</strong>, you can switch off alerts from apps that disturb you, or turn on <strong>Do not disturb</strong> while you study.</p>
<h3>Try it yourself</h3>
<ol>
<li>Set a new wallpaper from your pictures.</li>
<li>Try dark mode, decide whether you prefer it, and keep the one you like.</li>
<li>Change the display scale to a different value to see the effect, then return it to the recommended value.</li>
<li>Test your speakers or headphones in Sound settings.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Right-click the desktop and choose Personalise to change wallpaper, colours and themes.</li>
<li>Use the recommended resolution and a comfortable brightness and scale.</li>
<li>Windows key + P changes how a projector or second screen is used.</li>
<li>Check mute, output device and plugs when there is no sound.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Language, time, accessibility and user accounts',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to set the correct date and time, add a language or keyboard, make Windows easier to see and hear, and manage user accounts for people who share a computer. These options make the computer fit you, and not the other way round.</p>
<h3>Date, time and region</h3>
<p>The wrong date can cause strange errors, such as websites refusing to open securely. Open Settings, then <strong>Time and language</strong>, then <strong>Date and time</strong>. Turn on <strong>Set time automatically</strong> and <strong>Set time zone automatically</strong>, or choose the time zone manually; Uganda uses East Africa Time (UTC+3). Under <strong>Language and region</strong>, you can set your country or region, which affects date formats.</p>
<h3>Languages and keyboards</h3>
<p>If you need to type in another language, such as French or Swahili, go to Settings, <strong>Time and language</strong>, then <strong>Language and region</strong>, and select <strong>Add a language</strong>. Once more than one keyboard is installed, switch between them with <strong>Windows key + Space</strong> or by clicking the language code (such as ENG) in the tray. Make sure the keyboard layout matches your keys: US and UK layouts put symbols such as @ and " in different places, which confuses many beginners.</p>
<h3>Accessibility</h3>
<p>Open Settings, then <strong>Accessibility</strong>. Useful options include:</p>
<table>
<thead><tr><th>Option</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Text size</td><td>Makes text bigger on all of Windows</td></tr>
<tr><td>Mouse pointer and touch</td><td>Makes the pointer bigger or a different colour</td></tr>
<tr><td>Magnifier (Windows key + Plus)</td><td>Enlarges part of the screen</td></tr>
<tr><td>Contrast themes</td><td>Gives stronger colour contrast for low vision</td></tr>
<tr><td>Narrator (Ctrl + Windows key + Enter)</td><td>Reads the screen aloud</td></tr>
<tr><td>Captions</td><td>Shows text for spoken audio</td></tr>
<tr><td>Sticky Keys</td><td>Lets you press shortcut keys one at a time</td></tr>
<tr><td>On-screen keyboard</td><td>Lets you type by clicking keys on the screen</td></tr>
</tbody>
</table>
<h3>User accounts</h3>
<p>Each person should have their own account so that files, settings and passwords stay separate. Open Settings, then <strong>Accounts</strong>:</p>
<ul>
<li><strong>Your info:</strong> your name and picture.</li>
<li><strong>Sign-in options:</strong> change your password, set up a PIN or, on supported devices, a fingerprint or face.</li>
<li><strong>Other users</strong> (or Family and other users): add another person. A <strong>Standard</strong> account is safer for everyday use; an <strong>Administrator</strong> account can install programs and change system settings.</li>
</ul>
<p>Use a standard account for children and learners, and keep the administrator password private. Press <strong>Windows key + L</strong> to lock the computer when you leave your seat for a short time.</p>
<h3>Try it yourself</h3>
<ol>
<li>Check that your date, time and time zone are correct.</li>
<li>Look at the keyboard list in the system tray. Which layout is active?</li>
<li>Open the Magnifier with Windows key + Plus, zoom in and out, then close it.</li>
<li>Open Accounts and look at your sign-in options. Is a PIN or password set?</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Automatic date and time prevent odd errors.</li>
<li>Match the keyboard layout to your physical keyboard.</li>
<li>Accessibility settings help anyone see, hear or type more comfortably.</li>
<li>Use separate accounts, standard for everyday use, and lock the PC with Windows key + L.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Connecting to Wi-Fi and setting up a printer',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>You will learn how to join a Wi-Fi network and add a printer. These two tasks are among the most common requests a computer user faces in an office, school or cybercafe, and you will be able to do both yourself.</p>
<h3>Connecting to Wi-Fi</h3>
<ol>
<li>Click the Wi-Fi (or network) icon in the system tray.</li>
<li>In Windows 11, click the arrow beside the Wi-Fi tile to see the list of networks. If the Wi-Fi tile is off, turn it on first.</li>
<li>Select your network name (called the <strong>SSID</strong>) and tick <strong>Connect automatically</strong> if it is your own network.</li>
<li>Click <strong>Connect</strong> and enter the network password (security key).</li>
<li>Wait for the word <strong>Connected</strong>. You can now use the internet.</li>
</ol>
<p>Choose your network carefully. Public networks without a password are not private, so avoid banking or entering important passwords on them. If the internet does not work after connecting, open your browser and check whether a login page appears, which some hotels and cafes use.</p>
<h3>Wi-Fi problems</h3>
<ul>
<li>Check that Airplane mode is off and Wi-Fi is on.</li>
<li>Move closer to the router.</li>
<li>Forget the network (Settings, <strong>Network and internet</strong>, <strong>Wi-Fi</strong>, <strong>Manage known networks</strong>) and connect again.</li>
<li>Restart the computer, then the router if you are allowed to.</li>
<li>Ask whether the bundle or monthly package has run out.</li>
</ul>
<h3>Adding a printer</h3>
<p>Most printers connect with a USB cable or over Wi-Fi.</p>
<ol>
<li>Connect the printer to power and switch it on. Plug in the USB cable, or connect it to the same Wi-Fi network.</li>
<li>Open Settings, then <strong>Bluetooth and devices</strong>, then <strong>Printers and scanners</strong> (in Windows 10: <strong>Devices</strong>, then <strong>Printers and scanners</strong>).</li>
<li>Select <strong>Add device</strong> (or <strong>Add a printer or scanner</strong>). Wait for Windows to find it.</li>
<li>Select the printer name. Windows installs the needed driver.</li>
</ol>
<p>If Windows cannot find a driver, download it from the printer maker's official website using the exact model number.</p>
<h3>Printing and print problems</h3>
<p>In most programs press <strong>Ctrl + P</strong>, choose the printer, the number of copies and the pages, and click <strong>Print</strong>. Check the print preview before printing to save paper and ink. If nothing prints, check there is paper and ink or toner, the printer is on and connected, and that no stuck job waits in the queue (click the printer in Settings and choose <strong>Open print queue</strong> to cancel it). You can also choose <strong>Microsoft Print to PDF</strong> to save a document as a PDF file.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open the Wi-Fi list and write down the names of three networks you can see (do not connect to unknown ones).</li>
<li>Open <strong>Printers and scanners</strong> and see which printers are installed.</li>
<li>Open a text file, press Ctrl + P and choose <strong>Microsoft Print to PDF</strong>. Save it as a PDF in your Practice folder.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Join Wi-Fi from the tray icon; enter the password and choose automatic connection only for trusted networks.</li>
<li>Avoid sensitive activity on open public Wi-Fi.</li>
<li>Add printers through Settings, then Printers and scanners; get drivers from official sites.</li>
<li>Use print preview, and the print queue to clear stuck jobs.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: How do you open the personalisation options quickly?
- Press Ctrl + Alt + Delete
* Right-click an empty part of the desktop and choose Personalise
- Open the Recycle Bin
- Restart the computer
= Right-clicking the desktop gives the Personalise menu for wallpaper and themes.

Q: Which setting is best for a projector or second monitor when you want the same picture on both?
- Windows key + L
* Windows key + P, then choose Duplicate
- Alt + Tab
- Ctrl + P
= Windows key + P chooses how the extra display is used.

Q: Your laptop shows the wrong date, and secure websites will not open. What should you do?
- Ignore it
* Turn on automatic date, time and time zone in Settings
- Replace the screen
- Change the wallpaper
= Wrong time settings can break secure connections. Automatic time fixes this.

Q: Which feature reads text on the screen aloud?
- Sticky Keys
- Night light
* Narrator
- Magnifier
= Narrator is the built-in screen reader.

Q: Why is a standard user account better than an administrator account for everyday use?
- It runs faster
* It limits the damage a mistake or a virus can do because it cannot change system settings freely
- It cannot connect to the internet
- It has more storage
= Fewer permissions mean fewer chances of accidental or harmful system changes.

Q: You step away from a shared computer for two minutes. Which shortcut locks it?
- Windows key + D
- Windows key + E
* Windows key + L
- Alt + F4
= Windows key + L locks the screen and requires your password to continue.

Q: A document will not print. What should you check first? (choose all that apply)
* That the printer is on and connected
* That there is paper and ink or toner
- That the wallpaper is a photo
- That the mouse has a long cable
= Basic power, connection and supplies cause most printing problems, and a stuck job in the queue is another common one.
QUIZ,
        ],
        [
            'title'   => 'Module 5: Keeping it healthy',
            'summary' => 'Updates, Task Manager, storage clean-up, Windows Security, backups, troubleshooting and a shortcut cheat sheet.',
            'lessons' => [
                [
                    'title'   => 'Windows Update, Task Manager and frozen programs',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn how to keep Windows up to date and how to deal with a program that stops responding. By the end you will be able to close a frozen program without restarting the whole computer, and you will know why updates are worth the waiting.</p>
<h3>Windows Update</h3>
<p>Microsoft releases updates to fix faults, improve performance and close security holes. Updates are especially important because criminals look for computers with known weaknesses.</p>
<ol>
<li>Open <strong>Settings</strong>, then <strong>Windows Update</strong> (Windows 10: <strong>Update and Security</strong>).</li>
<li>Click <strong>Check for updates</strong>.</li>
<li>Click <strong>Download and install</strong> if any are found. Keep the laptop plugged in.</li>
<li>Restart when asked. The computer may restart more than once. Do not switch it off while it says "Working on updates".</li>
</ol>
<p>Updates use data, so connect to Wi-Fi where you can. You can pause updates for a short time or set <strong>Active hours</strong> so that restarts do not happen while you work. Do not turn updates off for long; an out-of-date system is risky.</p>
<h3>Task Manager</h3>
<p><strong>Task Manager</strong> shows what is running and how much of the computer each program uses. Open it with <strong>Ctrl + Shift + Esc</strong>, or right-click the Start button and choose <strong>Task Manager</strong>. If you see a small window, click <strong>More details</strong>.</p>
<ul>
<li><strong>Processes:</strong> lists apps and background programs with their use of CPU, memory, disk and network. Click a column to sort.</li>
<li><strong>Performance:</strong> live graphs of CPU, memory, disk and Wi-Fi.</li>
<li><strong>Startup apps:</strong> programs that start with Windows. Disabling ones you do not need makes start-up faster.</li>
</ul>
<h3>Closing a frozen program</h3>
<ol>
<li>Wait a minute first; the program may only be busy.</li>
<li>Press <strong>Ctrl + Shift + Esc</strong> to open Task Manager.</li>
<li>Find the program in <strong>Processes</strong>, marked "Not responding".</li>
<li>Select it and click <strong>End task</strong>.</li>
<li>Reopen the program. It may offer to recover your document.</li>
</ol>
<p>Unsaved work in a frozen program is often lost, so save often and use auto-save if the program has it. If the whole screen is frozen, try <strong>Ctrl + Alt + Delete</strong>, which opens a menu with Task Manager and the power button. Only if nothing works, hold the power button for five seconds.</p>
<h3>If the computer is slow</h3>
<ul>
<li>Check Task Manager for a program using most of the CPU, memory or disk.</li>
<li>Close programs and browser tabs you do not need.</li>
<li>Disable unneeded start-up apps.</li>
<li>Restart the computer. Many weeks without a restart makes it sluggish.</li>
<li>Check free disk space and scan for viruses.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Open Settings, go to Windows Update and check for updates.</li>
<li>Open Task Manager with Ctrl + Shift + Esc. Which program uses the most memory right now?</li>
<li>Click <strong>Startup apps</strong> and find three programs that start with Windows. Note their impact.</li>
<li>Open Notepad, then close it from Task Manager with End task.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Install Windows updates regularly; keep the laptop plugged in and do not interrupt them.</li>
<li>Ctrl + Shift + Esc opens Task Manager.</li>
<li>Use End task for a program that is not responding, before forcing a power-off.</li>
<li>A slow computer often needs fewer open programs, fewer start-up apps and a restart.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Storage clean-up, antivirus and backups',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to free up disk space, check that Windows Security is protecting you, and set up a simple backup. Together these three habits stop the most common computer disasters: a full drive, a virus, and lost files.</p>
<h3>Freeing storage space</h3>
<p>A drive that is almost full makes the computer slow. Open Settings, then <strong>System</strong>, then <strong>Storage</strong>. You will see what is using space: apps, temporary files, pictures and so on. Click a category to see details.</p>
<ul>
<li><strong>Temporary files:</strong> choose this category to clear old temporary files, the Recycle Bin and the Downloads folder (check the list before ticking Downloads, since it may hold files you want).</li>
<li><strong>Storage Sense:</strong> turn it on to let Windows clean temporary files and old Recycle Bin items automatically.</li>
<li><strong>Disk Cleanup:</strong> the older tool. Press the Windows key, type "Disk Cleanup", pick the drive and tick the items to remove.</li>
<li><strong>Uninstall unused apps</strong> in Settings, then <strong>Apps</strong>.</li>
<li>Move large photos and videos to an external drive or the cloud.</li>
</ul>
<h3>Windows Security (antivirus)</h3>
<p>Windows includes a free protection program called <strong>Windows Security</strong> with Microsoft Defender. Open it by pressing the Windows key and typing "Windows Security".</p>
<table>
<thead><tr><th>Area</th><th>Purpose</th></tr></thead>
<tbody>
<tr><td>Virus and threat protection</td><td>Scans for malware; shows the last scan and threats found</td></tr>
<tr><td>Firewall and network protection</td><td>Blocks unwanted connections; keep it on</td></tr>
<tr><td>App and browser control</td><td>Warns about unsafe downloads and websites</td></tr>
<tr><td>Device performance and health</td><td>Reports on updates and storage</td></tr>
</tbody>
</table>
<p>Check that the icons show green ticks. To run a scan, choose <strong>Virus and threat protection</strong>, then <strong>Quick scan</strong>. For a flash drive, use <strong>Scan options</strong> and a custom scan. Do not install a second antivirus on top of the built-in one without good reason, since two can conflict. Avoid free "cleaner" programs that promise miracles; many are scams.</p>
<h3>Backing up</h3>
<p>Files can be backed up in three simple ways:</p>
<ol>
<li><strong>Copy to a drive:</strong> plug in an external drive, open File Explorer and copy your important folders onto it. Repeat weekly.</li>
<li><strong>File History:</strong> if available on your version, open the Control Panel and search for File History, connect a drive and click <strong>Turn on</strong>. It saves versions of your files automatically.</li>
<li><strong>Cloud backup:</strong> store important folders in OneDrive or another cloud service.</li>
</ol>
<p>The best protection combines two of these. Test by opening one backed-up file from the backup.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open Settings, <strong>System</strong>, <strong>Storage</strong>, and note the category using the most space.</li>
<li>Open Windows Security and check there is no red warning.</li>
<li>Run a Quick scan.</li>
<li>Copy your Documents folder to a flash drive or external drive and open one file from it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Check Storage in Settings and use Storage Sense or Disk Cleanup to remove junk.</li>
<li>Windows Security gives free protection; keep it on and up to date.</li>
<li>Back up using at least two methods, and test the backup.</li>
<li>Do not install suspicious "cleaner" programs.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Troubleshooting and the shortcut cheat sheet',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>This final lesson gives you a calm method for fixing common Windows problems, followed by a cheat sheet of shortcuts to make you faster. You will be able to solve many everyday problems yourself instead of waiting for a technician.</p>
<h3>A simple troubleshooting method</h3>
<ol>
<li><strong>Stay calm and save</strong> any work that you still can.</li>
<li><strong>Look for a message.</strong> Write down or take a photo of any error text.</li>
<li><strong>Ask what changed.</strong> Did you install something, plug in a device, or get an update just before?</li>
<li><strong>Try the simple fixes first</strong>: restart, check cables, check internet and power.</li>
<li><strong>Change one thing at a time</strong> and test after each.</li>
<li><strong>Search the exact message</strong> on a trusted help site such as Microsoft Support.</li>
<li><strong>Ask for help</strong> when you are unsure, and say what you have already tried.</li>
</ol>
<h3>Common problems and first fixes</h3>
<table>
<thead><tr><th>Problem</th><th>Try this</th></tr></thead>
<tbody>
<tr><td>Computer very slow</td><td>Close programs, check Task Manager, restart, free disk space, run a scan</td></tr>
<tr><td>Program will not open</td><td>Restart it; restart the computer; reinstall if needed</td></tr>
<tr><td>No internet</td><td>Check Airplane mode, Wi-Fi, router, data bundle; forget and rejoin the network</td></tr>
<tr><td>No sound</td><td>Check mute, volume, output device and plugs</td></tr>
<tr><td>Flash drive not showing</td><td>Try another port; check This PC and Disk Management; scan it</td></tr>
<tr><td>Wrong characters when typing</td><td>Check the keyboard layout (Windows key + Space)</td></tr>
<tr><td>Screen very bright or dim</td><td>Adjust brightness; check power mode</td></tr>
<tr><td>Cannot find a file</td><td>Search; show hidden items; check the Recycle Bin and OneDrive</td></tr>
<tr><td>Laptop battery drains fast</td><td>Lower brightness, close programs, turn on Battery saver</td></tr>
</tbody>
</table>
<p>Windows also has built-in troubleshooters. Open Settings, <strong>System</strong>, then <strong>Troubleshoot</strong> (Windows 10: <strong>Update and Security</strong>, then <strong>Troubleshoot</strong>) and choose <strong>Other troubleshooters</strong>.</p>
<h3>Shortcut cheat sheet</h3>
<table>
<thead><tr><th>Shortcut</th><th>Action</th></tr></thead>
<tbody>
<tr><td>Windows key</td><td>Open Start and search</td></tr>
<tr><td>Windows key + E</td><td>Open File Explorer</td></tr>
<tr><td>Windows key + I</td><td>Open Settings</td></tr>
<tr><td>Windows key + D</td><td>Show the desktop</td></tr>
<tr><td>Windows key + L</td><td>Lock the computer</td></tr>
<tr><td>Windows key + Tab</td><td>Task View</td></tr>
<tr><td>Windows key + Arrow keys</td><td>Snap windows</td></tr>
<tr><td>Windows key + Shift + S</td><td>Screenshot tool</td></tr>
<tr><td>Alt + Tab</td><td>Switch windows</td></tr>
<tr><td>Alt + F4</td><td>Close the window</td></tr>
<tr><td>Ctrl + Shift + Esc</td><td>Task Manager</td></tr>
<tr><td>Ctrl + C, X, V, Z, Y</td><td>Copy, cut, paste, undo, redo</td></tr>
<tr><td>Ctrl + A</td><td>Select all</td></tr>
<tr><td>Ctrl + F</td><td>Find in a document or page</td></tr>
<tr><td>F2</td><td>Rename</td></tr>
<tr><td>F5</td><td>Refresh</td></tr>
<tr><td>Ctrl + Shift + N</td><td>New folder in File Explorer</td></tr>
</tbody>
</table>
<h3>Try it yourself</h3>
<ol>
<li>Pick one problem from the table and rehearse the first fixes aloud.</li>
<li>Open Settings, <strong>Troubleshoot</strong>, and look at the list of available troubleshooters.</li>
<li>Practise five shortcuts from the cheat sheet until you can use them without looking.</li>
<li>Write your own one-page list of your top ten shortcuts and keep it near your computer.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Solve problems step by step, trying simple fixes first and changing one thing at a time.</li>
<li>Read and note error messages exactly.</li>
<li>Built-in troubleshooters help with many common faults.</li>
<li>Shortcuts such as Windows key + E, Alt + Tab and Ctrl + Shift + Esc save a lot of time.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Why should you install Windows updates?
- They make the screen bigger
* They fix faults and close security holes
- They remove all your files
- They are only for gaming
= Updates are an important part of staying secure.

Q: Which shortcut opens Task Manager directly?
- Ctrl + Alt + Esc
* Ctrl + Shift + Esc
- Ctrl + Shift + T
- Alt + Shift + M
= Ctrl + Shift + Esc opens Task Manager at once.

Q: A program says "Not responding". What is the best first step after waiting a minute?
- Pull out the power cable
* Open Task Manager and use End task on that program
- Delete Windows
- Unplug the keyboard
= End task closes only the frozen program, avoiding a full shutdown.

Q: What is Storage Sense?
- A tool that makes the screen brighter
* A feature that automatically cleans temporary files and old Recycle Bin items
- A virus
- A kind of flash drive
= Storage Sense frees up space by removing files that are no longer needed.

Q: What is a sensible approach to antivirus on Windows?
- Install three antivirus programs at once
* Keep Windows Security turned on and up to date
- Switch it off to make the computer faster
- Install any free "cleaner" that promises miracles
= The built-in protection is good for most users; multiple programs can conflict, and miracle cleaners are often scams.

Q: What is a good backup plan? (choose all that apply)
* Copy important folders to an external drive regularly
* Also keep an online copy in the cloud
- Keep one copy only, on the Desktop
- Never open the backup to test it
= Several copies in different places, and testing them, protect your work.

Q: When troubleshooting, why change only one thing at a time?
- It is more fun
* You can tell which change fixed or caused the problem
- Windows only allows one change per day
- It stops updates
= Changing many things at once makes it impossible to know what worked.

Q: Which shortcut shows the desktop quickly?
- Windows key + L
* Windows key + D
- Ctrl + D
- Alt + D
= Windows key + D shows or hides the desktop. Windows key + L locks the computer.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: You cannot find a program on your computer. What is the quickest way to open it?
- Open every folder on drive C
* Press the Windows key, type its name and press Enter
- Restart the computer
- Ask a friend to reinstall Windows
= Typing after pressing the Windows key searches apps, files and settings.

Q: Which part of the taskbar shows Wi-Fi, volume, battery and the clock?
- The Start menu
* The system tray (notification area)
- Task View
- The Recycle Bin
= The system tray on the right holds status icons and the clock.

Q: How do you pin a program to the taskbar?
- Drag it into the Recycle Bin
* Right-click it and choose Pin to taskbar
- Press Alt + F4
- Rename it
= The right-click menu offers Pin to taskbar or Pin to Start.

Q: Which shortcut snaps the current window to the right half of the screen?
- Alt + Right arrow
* Windows key + Right arrow
- Ctrl + Right arrow
- Shift + Right arrow
= The Windows key with an arrow snaps the window.

Q: What is the benefit of virtual desktops?
- They make the computer faster
* They let you keep different tasks on separate workspaces
- They back up your files
- They connect you to Wi-Fi
= Virtual desktops reduce clutter and help you focus.

Q: What is the shortcut to open Settings?
- Windows key + S
* Windows key + I
- Windows key + R
- Alt + I
= Windows key + I opens Settings.

Q: In report.pdf, what does ".pdf" tell you?
- The folder it is in
* The file type, a portable document
- The size of the file
- The date it was made
= The extension after the dot shows the file type.

Q: Which of these file names would Windows not allow?
- Notes-Term1.docx
- Budget_2025.xlsx
* Plan: Phase 1/2.docx
- Report v2.pdf
= A file name cannot contain characters such as : and /.

Q: Which key renames a selected file?
- F1
* F2
- F4
- F12
= F2 puts the file name into edit mode.

Q: What is the difference between moving and copying a file?
* Moving takes it from the old place, copying leaves the original behind
- Moving makes two copies, copying makes none
- They are always identical
- Copying deletes the file
= Cut and paste moves; copy and paste duplicates.

Q: How do you select several separate files in a folder?
- Click them while holding Shift
* Click them while holding Ctrl
- Right-click twice
- Press Tab
= Ctrl + click picks single items; Shift picks a continuous range.

Q: You deleted a document by mistake yesterday and did not empty the Recycle Bin. What should you do?
- Nothing can be done
* Open the Recycle Bin, right-click the file and choose Restore
- Reinstall Windows
- Format the drive
= Files in the Recycle Bin can be restored until it is emptied.

Q: Which is the best way to find a file when you cannot remember its location?
- Look through every folder one by one
* Open the likely parent folder and use the search box, perhaps with a filter like *.docx
- Delete old files until it appears
- Restart the computer
= Search covers subfolders and filters narrow results.

Q: Which file name would be easiest to sort and find later?
- Document1.docx
- final.docx
* 2025-05-20-Youth-Club-Report.docx
- New.docx
= A date at the start in year-month-day order with a descriptive name works best.

Q: What does a zip file do?
* Packs one or more files into a single smaller file
- Deletes duplicate files
- Repairs viruses
- Prints documents
= A zip compresses and bundles files.

Q: Why show hidden items in File Explorer only when you need to?
- Hidden files are always viruses
* Hidden files are often important system files that should not be changed or deleted by mistake
- They slow down printing
- They cannot be opened
= Many hidden files belong to Windows and programs.

Q: You plug in a flash drive from a friend. What should you do before opening files on it?
- Double-click every file immediately
* Scan it with Windows Security
- Format your computer
- Share it
= Flash drives often spread viruses; scanning first is wise.

Q: What is the right way to remove a flash drive?
- Pull it out as soon as the copy bar looks finished
* Eject it using the safe removal option, then unplug it
- Shake the computer first
- Restart with it plugged in
= Ejecting makes sure all data has been written.

Q: A green tick on a OneDrive file means:
- The file has been deleted
* The file is stored on your computer and in the cloud
- The file is infected
- The file is on a printer
= The green tick shows the file is available locally and synced.

Q: Which settings help you read the screen more comfortably? (choose all that apply)
* Increasing the text size
* Using dark mode or Night light in a dim room
- Turning the brightness up to maximum in a dark room
- Setting a very low resolution
= Suitable text size, colours and brightness reduce eye strain.

Q: Which shortcut locks your computer when you leave your seat?
- Windows key + D
- Windows key + P
* Windows key + L
- Alt + F4
= Windows key + L locks the screen so others cannot use your session.

Q: Why is a standard user account safer for everyday use?
- It runs programs faster
* It cannot change system settings or install software without permission
- It has unlimited storage
- It does not need a password
= Limited rights reduce the damage from mistakes and malware.

Q: Your @ and " keys type the wrong symbols. What is the most likely cause?
- A virus
* The keyboard layout (language) does not match your physical keyboard
- A broken screen
- Low battery
= Switch layouts with Windows key + Space or in Settings, Time and language.

Q: What should you be careful about on open public Wi-Fi?
- Nothing at all
* Avoid banking or entering important passwords because the network is not private
- Keep the volume low
- Close the lid
= Open networks can be watched by others, so avoid sensitive activities.

Q: How do you add a printer in Windows 11?
- Settings, Apps, Uninstall
* Settings, Bluetooth and devices, Printers and scanners, Add device
- Task Manager, End task
- Right-click the Recycle Bin
= Printers are managed in Printers and scanners.

Q: A program freezes and does not respond for several minutes. What should you do?
- Pull the plug immediately
* Open Task Manager with Ctrl + Shift + Esc and End task
- Format the drive
- Reinstall Windows
= Ending only the frozen task is the gentlest fix.

Q: Which is the best description of Windows Update?
- A program that deletes your files
* A feature that installs fixes and security improvements
- A game
- A way to change the wallpaper
= Updates keep Windows secure and reliable.

Q: Which actions help when your hard drive is nearly full? (choose all that apply)
* Run Storage Sense or Disk Cleanup
* Move large photos and videos to an external drive or the cloud
- Delete the Windows folder
- Install more programs
= Removing junk and moving big files frees space safely.

Q: When troubleshooting, which is a good first step?
- Reinstall Windows immediately
* Note any error message exactly and try the simple fixes such as restarting
- Change ten settings at once
- Throw the computer away
= Start with the simplest fixes and keep a record of what you tried.
QUIZ,
];
