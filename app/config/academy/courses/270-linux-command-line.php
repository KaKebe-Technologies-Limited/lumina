<?php
return [
    'slug'        => 'linux-command-line',
    'title'       => 'Linux and the Command Line',
    'category'    => 'cloud',
    'level'       => 'intermediate',
    'icon'        => 'code',
    'sort'        => 270,
    'summary'     => 'Learn to use Linux from the terminal: files, permissions, software, text tools, scripts, networking and a small web server. Practise safely on a virtual machine.',
    'description' => <<<'HTML'
<p>Most of the internet runs on Linux: web servers, cloud machines, routers, and many phones. People who can work in the Linux terminal are able to manage servers, automate boring jobs and fix problems that a mouse cannot reach. This course teaches the command line from the very first command, in plain steps, using a Debian or Ubuntu style system.</p>
<p>You will learn how the Linux filesystem is laid out, how to create, copy, move and search files, how permissions and users keep a system safe, how to install software, how to write small shell scripts, and how to look after a simple server. The final module ends with a practical project: setting up a small web server on a practice machine.</p>
<p>You do not need a spare computer. You can practise safely inside a virtual machine, with WSL on Windows, or from a live USB stick, and the course explains each option. Anything that could erase data is clearly marked before you do it.</p>
HTML,
    'outcomes'    => [
        'Explain what Linux, a distribution and the shell are',
        'Move around the filesystem and find help with pwd, ls, cd, man and --help',
        'Create, copy, move, view and delete files and folders from the terminal, with care',
        'Use redirects and pipes to join small commands into useful ones',
        'Read and change file permissions and ownership, and use sudo carefully',
        'Install and update software with apt and manage running processes',
        'Search and summarise text with grep, find, sort, uniq and wc, and write simple shell scripts',
        'Check a network connection, log in to another machine with ssh, read logs and set up a small web server',
    ],
    'requirements' => [
        'Computer basics (the course "Computer Basics and Digital Literacy" or similar experience)',
        'A computer that can run a virtual machine, WSL or boot from a USB stick (4 GB or more of memory is comfortable)',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Ubuntu: official site and documentation', 'url' => 'https://ubuntu.com'],
        ['title' => 'Debian: official site', 'url' => 'https://www.debian.org'],
        ['title' => 'The Linux Documentation Project', 'url' => 'https://tldp.org'],
        ['title' => 'Linux man pages online', 'url' => 'https://man7.org/linux/man-pages/'],
    ],
    'modules' => [

        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: Meeting Linux',
            'summary' => 'Understand what Linux is, set up a safe practice system, and take your first steps in the terminal.',
            'lessons' => [
                [
                    'title'   => 'What is Linux?',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what Linux is, name its main parts and say where it is used. This matters because Linux runs most web servers, cloud services and many devices around you, so understanding it opens the door to IT, hosting and cybersecurity work.</p>
<h3>Kernel, system and distribution</h3>
<p>The <strong>kernel</strong> is the core of an operating system. It talks to the hardware (processor, memory, disk, network) and shares them between programs. Linux is the name of a kernel, started by Linus Torvalds in 1991 and now improved by thousands of volunteers and companies. On its own a kernel is not very useful, so it is combined with tools, a way to install software, and usually a desktop. This complete package is called a <strong>distribution</strong>, or <em>distro</em>.</p>
<p>Linux is <strong>open source</strong>: the source code is public, anyone may study it, change it and share it, and most distributions are free to download and use. That is why governments, schools and small businesses can use it without licence fees.</p>
<h3>Popular distributions</h3>
<table>
<thead><tr><th>Distribution</th><th>Known for</th></tr></thead>
<tbody>
<tr><td>Ubuntu</td><td>Friendly, popular on desktops and servers, regular long-term releases</td></tr>
<tr><td>Debian</td><td>Very stable and the base for Ubuntu and many others</td></tr>
<tr><td>Linux Mint</td><td>Easy desktop that feels familiar to Windows users</td></tr>
<tr><td>Fedora and Red Hat family</td><td>Newer software and common in large companies</td></tr>
<tr><td>Alpine</td><td>Very small, often used in containers</td></tr>
</tbody>
</table>
<p>This course uses commands that work on Debian, Ubuntu and their relatives. Other families use different package tools, but the basics you learn here are the same everywhere.</p>
<h3>Desktop and server</h3>
<p>A <strong>desktop</strong> Linux system has windows, menus and a mouse, much like Windows. A <strong>server</strong> Linux system usually has no graphics at all. You log in from another computer and type commands. Servers skip graphics to save memory and reduce things that can break or be attacked, so knowing the command line is essential for server work.</p>
<h3>Where Linux is used</h3>
<ul>
<li>Websites, email and databases on servers in data centres</li>
<li>Cloud computing, where most virtual machines run Linux</li>
<li>Android phones (Android is built on the Linux kernel)</li>
<li>Routers, smart TVs, school and hospital systems, and supercomputers</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Write down three places you use Linux every day without knowing it (for example, an Android phone, a website, a router).</li>
<li>Open a search engine and find the official download page of Ubuntu. Note the name of the latest long-term support (LTS) release.</li>
<li>Name one reason a server might not have a desktop.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Linux is a kernel; a distribution adds software, tools and often a desktop.</li>
<li>Linux is open source and usually free.</li>
<li>Servers normally run without a desktop and are managed with the command line.</li>
<li>Debian and Ubuntu style systems are the example used in this course.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Practising safely: VM, WSL or live USB',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will know three safe ways to get a Linux system to practise on, and you will choose the one that suits your computer. You should never practise commands on a computer that holds your only copy of important files, so a safe practice space is the first job.</p>
<h3>Option 1: a virtual machine (best for learning)</h3>
<p>A <strong>virtual machine</strong> (VM) is a pretend computer that runs as a program inside your real one. Programs such as VirtualBox or VMware Workstation Player let you create one. Whatever you do inside the VM, including deleting everything, does not touch your real files. You can also take a <strong>snapshot</strong>, a saved moment you can return to.</p>
<ol>
<li>Download a VM program from its official website and install it.</li>
<li>Download an Ubuntu Server or Ubuntu Desktop <strong>ISO file</strong> (a disc image) from ubuntu.com. The file is large, so use a good connection or ask a centre with fast internet.</li>
<li>Create a new VM, give it at least 2 GB of memory and 20 GB of disk space, and attach the ISO.</li>
<li>Start the VM and follow the installer. The installer erases the <em>virtual</em> disk only; read each screen to be sure you are working in the VM.</li>
</ol>
<h3>Option 2: WSL on Windows 10 or 11</h3>
<p><strong>WSL</strong> (Windows Subsystem for Linux) runs a Linux environment inside Windows without a separate VM window. Open PowerShell as administrator and type the following, then restart when asked. It installs Ubuntu by default on current Windows versions.</p>
<pre><code>wsl --install</code></pre>
<p>After the restart, an Ubuntu window opens and asks you to create a username and password. You will see a normal Linux prompt. WSL is quick and light, but some things (such as a full web server reachable from other computers, or some system services) can be harder than in a VM.</p>
<h3>Option 3: a live USB</h3>
<p>A <strong>live USB</strong> is a flash drive that starts a full Linux system without installing it. You restart the computer and choose the USB in the boot menu. Nothing is saved unless you set it up to.</p>
<p><strong>Warning: be very careful when writing the USB.</strong> A tool such as Rufus or balenaEtcher erases everything on the USB stick, so copy off anything you need first and pick the correct drive. Never choose your computer's internal disk. Also never pick an option called "Install" or "Erase disk" in the Linux installer on your real computer unless you are certain what it will do, because that can wipe Windows and all your files. Choose "Try Ubuntu" instead.</p>
<h3>Which should you use?</h3>
<table>
<thead><tr><th>Option</th><th>Good for</th><th>Watch out for</th></tr></thead>
<tbody>
<tr><td>Virtual machine</td><td>Safe practice, servers, snapshots</td><td>Needs memory and disk space</td></tr>
<tr><td>WSL</td><td>Fast, light, daily commands on Windows</td><td>Fewer server-like features</td></tr>
<tr><td>Live USB</td><td>Computers with little spare space, trying the desktop</td><td>Slower, nothing saved by default; risk when writing the USB</td></tr>
</tbody>
</table>
<h3>Try it yourself</h3>
<ol>
<li>Decide which option fits your computer and why.</li>
<li>Set it up following the steps above. If you use a VM, take a snapshot called "fresh install" once it works.</li>
<li>Log in and find the terminal (on Ubuntu Desktop press Ctrl+Alt+T; in WSL the window itself is the terminal).</li>
<li>Write down your username and where your password is stored (a password notebook kept safe).</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Practise in a place where mistakes cost nothing.</li>
<li>A VM is separate from your real files; take snapshots.</li>
<li>WSL is the quickest way on Windows 10 and 11.</li>
<li>Writing a USB or installing to a real disk can erase data, so read every screen.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The terminal, the shell and the filesystem',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what the terminal and shell are, read a prompt, and describe the main folders of a Linux system. The command line looks strange at first, but it is only a conversation: you type a request, the computer replies.</p>
<h3>Terminal and shell</h3>
<p>The <strong>terminal</strong> is the window where you type. The <strong>shell</strong> is the program inside it that reads your commands and runs them. The most common shell is <strong>bash</strong>. When the shell is ready it shows a <strong>prompt</strong>, for example:</p>
<pre><code>amina@practice:~$</code></pre>
<p>This means the user <code>amina</code> is on the computer called <code>practice</code>, in her home folder (<code>~</code>), and <code>$</code> says she is an ordinary user. A <code>#</code> instead of <code>$</code> means the administrator (root), who can change anything, so take extra care there.</p>
<h3>How a command is written</h3>
<p>A command has a name, then <strong>options</strong> (usually starting with a dash) and <strong>arguments</strong> (what to work on):</p>
<pre><code>ls -l /etc</code></pre>
<p>Here <code>ls</code> is the command, <code>-l</code> is an option and <code>/etc</code> is the argument. Linux is <strong>case sensitive</strong>: <code>Notes.txt</code> and <code>notes.txt</code> are different files. Press <strong>Tab</strong> to complete names and the <strong>Up arrow</strong> to bring back earlier commands. Ctrl+C stops a running command.</p>
<h3>One tree, starting at the root</h3>
<p>Windows has drives like C: and D:. Linux has a single tree starting at <code>/</code>, called <strong>root</strong>. Every file lives somewhere under it, and folders are separated by forward slashes.</p>
<table>
<thead><tr><th>Folder</th><th>What it holds</th></tr></thead>
<tbody>
<tr><td><code>/home</code></td><td>Personal folders of users, such as /home/amina</td></tr>
<tr><td><code>/etc</code></td><td>Settings (configuration files) of the system and programs</td></tr>
<tr><td><code>/var</code></td><td>Data that changes: logs, websites, mail queues</td></tr>
<tr><td><code>/bin</code>, <code>/usr/bin</code></td><td>Programs and commands</td></tr>
<tr><td><code>/tmp</code></td><td>Temporary files, cleared often</td></tr>
<tr><td><code>/dev</code></td><td>Special files that stand for devices</td></tr>
<tr><td><code>/root</code></td><td>Home folder of the administrator</td></tr>
</tbody>
</table>
<p>A path that starts with <code>/</code> is <strong>absolute</strong>: it works from anywhere. A path without it is <strong>relative</strong> to where you are now. Two special names help: <code>.</code> means "this folder" and <code>..</code> means "the folder above".</p>
<h3>Try it yourself</h3>
<ol>
<li>Open your terminal and read your own prompt. Say aloud your username, computer name and folder.</li>
<li>Type <code>echo Hello Linux</code> and press Enter. The shell prints the words back.</li>
<li>Type <code>echo hello</code>, then press the Up arrow and Enter to run it again.</li>
<li>Type <code>ec</code> then press Tab; see what the shell does when only one name fits.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The terminal is the window; the shell (usually bash) runs your commands.</li>
<li>A command is a name, options and arguments, and case matters.</li>
<li>Linux has a single tree starting at <code>/</code>.</li>
<li>Tab completion and the Up arrow save time and typing mistakes.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'First commands: pwd, ls, cd and getting help',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to find where you are, list what is in a folder, move around, and look up how any command works. These four skills are what you will use in every session, and knowing how to find help means you never need to memorise everything.</p>
<h3>pwd: where am I?</h3>
<p><code>pwd</code> means "print working directory". It shows the full path of your current folder.</p>
<pre><code>pwd
/home/amina</code></pre>
<h3>ls: what is here?</h3>
<p><code>ls</code> lists the files and folders. Useful options:</p>
<table>
<thead><tr><th>Command</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td><code>ls</code></td><td>Simple list</td></tr>
<tr><td><code>ls -l</code></td><td>Long list: permissions, owner, size, date</td></tr>
<tr><td><code>ls -a</code></td><td>Include hidden files (names starting with a dot)</td></tr>
<tr><td><code>ls -lh</code></td><td>Long list with sizes such as 4.0K or 2.3M</td></tr>
<tr><td><code>ls /etc</code></td><td>List another folder without going there</td></tr>
</tbody>
</table>
<p>Options can be combined, so <code>ls -lah</code> means long, all files, human sizes.</p>
<h3>cd: change directory</h3>
<pre><code>cd /var/log      # go to an absolute path
cd ..            # go up one folder
cd               # go home
cd ~             # also home
cd -             # go back to the previous folder</code></pre>
<p>Anything after <code>#</code> on a line is a comment: the shell ignores it. If a name has a space, wrap it in quotes: <code>cd "My Files"</code>, or press Tab and let the shell add a backslash.</p>
<h3>Getting help</h3>
<p>Almost every command explains itself in two ways:</p>
<ol>
<li><code>command --help</code> prints a short summary of the options, for example <code>ls --help</code>. It is fast.</li>
<li><code>man command</code> opens the full <strong>manual page</strong>, for example <code>man ls</code>. Use the arrow keys or Space to scroll, <code>/word</code> to search, <code>n</code> for the next match and <code>q</code> to quit.</li>
</ol>
<p>If you only remember what a command does, try <code>apropos copy</code>, which searches manual summaries for the word. On a minimal server, man pages may be missing; install them later with the package manager.</p>
<h3>Common mistakes</h3>
<ul>
<li>Typing <code>Cd</code> or <code>LS</code>: commands are case sensitive and must be lower case.</li>
<li>Forgetting a space: write <code>cd ..</code>, not <code>cd..</code>.</li>
<li>Being stuck in <code>man</code> or <code>less</code>: press <code>q</code> to leave.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Run <code>pwd</code> and write down your home path.</li>
<li>Run <code>ls -a</code> and find at least one hidden file such as <code>.bashrc</code>.</li>
<li>Go to <code>/etc</code> with <code>cd /etc</code>, run <code>ls | head</code>, then return home with <code>cd</code>.</li>
<li>Open <code>man ls</code>, search for <code>/reverse</code> and find the option that reverses the order. Quit with <code>q</code>.</li>
</ol>
<h3>Key points</h3>
<ul>
<li><code>pwd</code> shows where you are, <code>ls</code> shows what is there, <code>cd</code> moves you.</li>
<li><code>.</code> is this folder, <code>..</code> is the parent, <code>~</code> is home.</li>
<li><code>--help</code> is the quick help; <code>man</code> is the full manual (leave with <code>q</code>).</li>
<li>Combine options such as <code>-lah</code> to get more information at once.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is Linux, strictly speaking?
- A complete desktop with a taskbar and a start menu
* A kernel that is combined with tools to make a distribution
- A programming language used for websites
- A brand of laptop
= Linux is the kernel. A distribution such as Ubuntu or Debian adds the tools, software installer and often a desktop.

Q: Which of these are Linux distributions? (choose all that apply)
* Ubuntu
* Debian
- Bash
- VirtualBox
= Ubuntu and Debian are distributions. Bash is a shell and VirtualBox is virtual machine software.

Q: Why is a virtual machine a good place for a beginner to practise?
- It makes the real computer faster
- It avoids the need for a password
* Mistakes inside it do not damage the real computer's files, and you can use snapshots
- It is the only way to run Linux
= A VM is isolated from your real system, so even deleting everything in it does no harm, and snapshots let you go back.

Q: What does the prompt symbol # (instead of $) usually tell you?
- The command failed
* You are the administrator (root) and can change anything
- The line is a comment
- You are connected to the internet
= A # prompt means root. Take extra care because root can damage the system easily.

Q: You are in /home/amina and type cd .. What is your folder now?
- /home/amina/..
- /root
* /home
- /
= The two dots mean the folder above the current one, so /home/amina becomes /home.

Q: You forget what the option -h does in ls. What is the quickest way to check?
- Delete the command and try another one
* Run ls --help or man ls
- Restart the computer
- Search the /dev folder
= Almost every command prints help with --help and has a full manual page opened with man.

Q: Which command lists hidden files too?
- ls -l
- ls -h
* ls -a
- ls -r
= Hidden files start with a dot, and the -a (all) option shows them.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Working with files',
            'summary' => 'Create, copy, move, view, edit and delete files, and join commands with redirects and pipes.',
            'lessons' => [
                [
                    'title'   => 'Making, copying, moving and removing',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to create folders and files, copy and move them, rename them and delete them. These are the everyday jobs of file management, and the command line does them quickly, even for hundreds of files at once.</p>
<h3>Creating</h3>
<pre><code>mkdir projects                # make a folder
mkdir -p projects/2026/notes  # make nested folders in one go
touch todo.txt                # make an empty file (or update its time)</code></pre>
<h3>Copying with cp</h3>
<pre><code>cp todo.txt backup.txt             # copy a file
cp todo.txt projects/              # copy into a folder
cp -r projects projects-copy       # copy a whole folder (-r = recursive)</code></pre>
<p>If the target already exists, <code>cp</code> replaces it without asking. Use <code>cp -i</code> to be asked first.</p>
<h3>Moving and renaming with mv</h3>
<p>The same command moves and renames, because renaming is moving to a new name:</p>
<pre><code>mv backup.txt old-backup.txt       # rename
mv old-backup.txt projects/        # move into a folder
mv projects/old-backup.txt .       # move it back here (the dot is this folder)</code></pre>
<h3>Removing with rm: be careful</h3>
<p>Linux has <strong>no recycle bin</strong> for command-line deletes. Once <code>rm</code> removes a file, it is gone for good in practice.</p>
<pre><code>rm old-backup.txt          # delete a file
rm -i old-backup.txt       # ask first
rmdir emptyfolder          # delete an empty folder only
rm -r projects-copy        # delete a folder and everything in it</code></pre>
<p><strong>Warning.</strong> <code>rm -r</code> on the wrong folder can erase your work in a second. Habits that protect you:</p>
<ol>
<li>Run <code>pwd</code> and <code>ls</code> first so you know where you are and what is there.</li>
<li>Use <code>rm -i</code> while learning.</li>
<li>Read the command twice before pressing Enter. A stray space, such as <code>rm -r /home/amina /old</code>, can delete far more than you meant.</li>
<li>Never copy a delete command from a stranger into a terminal if you do not understand every part of it.</li>
<li>Never run <code>rm -rf /</code> or anything similar, not even "as a joke"; on real systems it destroys the machine.</li>
</ol>
<h3>Common mistakes</h3>
<ul>
<li>Using <code>cp</code> on a folder without <code>-r</code>: you get "omitting directory".</li>
<li>Overwriting a file by copying onto an existing name.</li>
<li>Expecting to find deleted files in a bin: there is none.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Go home with <code>cd</code> and create <code>practice/week1</code> using <code>mkdir -p</code>.</li>
<li>Inside <code>practice</code>, run <code>touch notes.txt</code>, copy it to <code>notes-copy.txt</code>, then rename the copy to <code>old.txt</code>.</li>
<li>Move <code>old.txt</code> into <code>week1</code>, and check with <code>ls week1</code>.</li>
<li>Delete the file with <code>rm -i week1/old.txt</code>, answering <code>y</code>.</li>
</ol>
<h3>Key points</h3>
<ul>
<li><code>mkdir</code>, <code>touch</code>, <code>cp</code>, <code>mv</code> and <code>rm</code> create, copy, move or rename, and delete.</li>
<li><code>-r</code> is needed for folders in <code>cp</code> and <code>rm</code>.</li>
<li>There is no recycle bin: check twice before <code>rm</code>.</li>
<li><code>pwd</code> and <code>ls</code> before deleting is a good habit.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Looking inside files: cat, less, head, tail, wildcards',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to read files of any size, see just their start or end, and pick groups of files with wildcards. Reading files is how you check settings, read logs and understand what a system is doing.</p>
<h3>Showing a file</h3>
<table>
<thead><tr><th>Command</th><th>Use</th></tr></thead>
<tbody>
<tr><td><code>cat file</code></td><td>Print the whole file; best for short files</td></tr>
<tr><td><code>less file</code></td><td>Scroll through a long file (Space next page, <code>b</code> back, <code>/word</code> search, <code>q</code> quit)</td></tr>
<tr><td><code>head file</code></td><td>First 10 lines (<code>head -n 3 file</code> for 3)</td></tr>
<tr><td><code>tail file</code></td><td>Last 10 lines (<code>tail -n 20 file</code>)</td></tr>
<tr><td><code>tail -f file</code></td><td>Keep watching new lines as they arrive; stop with Ctrl+C</td></tr>
</tbody>
</table>
<p>The file <code>/etc/os-release</code> exists on most systems and is a safe one to practise on:</p>
<pre><code>cat /etc/os-release
head -n 3 /etc/os-release
less /etc/passwd</code></pre>
<p><code>/etc/passwd</code> lists the user accounts. It does not hold passwords any more, despite its name, so reading it is harmless.</p>
<h3>Wildcards</h3>
<p>A <strong>wildcard</strong> is a character that stands for other characters. The shell expands it into matching file names before the command runs.</p>
<table>
<thead><tr><th>Pattern</th><th>Matches</th></tr></thead>
<tbody>
<tr><td><code>*</code></td><td>Any number of any characters</td></tr>
<tr><td><code>?</code></td><td>Exactly one character</td></tr>
<tr><td><code>[abc]</code></td><td>One of the listed characters</td></tr>
</tbody>
</table>
<pre><code>ls *.txt          # every file ending in .txt
ls report?.txt    # report1.txt, reportA.txt, not report10.txt
ls photo[12].jpg  # photo1.jpg and photo2.jpg
cp *.txt backup/  # copy all text files into the backup folder</code></pre>
<p>Wildcards are powerful and dangerous together with <code>rm</code>. Always run <code>ls</code> with the same pattern first to see what will be affected, then change <code>ls</code> to <code>rm</code>.</p>
<h3>Hidden files</h3>
<p>Files whose names begin with a dot are hidden from normal listings. Programs keep personal settings in them, for example <code>.bashrc</code> holds your shell settings. Show them with <code>ls -a</code>. Careful: <code>*</code> does not match hidden names.</p>
<h3>Try it yourself</h3>
<ol>
<li>Run <code>cat /etc/os-release</code> and find the name and version of your system.</li>
<li>Run <code>head -n 5 /etc/passwd</code> and <code>tail -n 5 /etc/passwd</code> and compare the two outputs.</li>
<li>In your <code>practice</code> folder, create <code>a.txt</code>, <code>b.txt</code> and <code>c.log</code> with <code>touch</code>. Use <code>ls *.txt</code> and then <code>ls ?.log</code>.</li>
<li>Open <code>less /etc/services</code>, search for <code>/http</code> and quit with <code>q</code>.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Use <code>cat</code> for small files and <code>less</code> for large ones.</li>
<li><code>head</code> and <code>tail</code> show the start and end; <code>tail -f</code> follows a growing log.</li>
<li><code>*</code>, <code>?</code> and <code>[ ]</code> are wildcards expanded by the shell.</li>
<li>Test a wildcard with <code>ls</code> before you use it with <code>rm</code>.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Editing text with nano',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to create and change a text file in the terminal with the nano editor. Servers have no mouse and no Notepad, yet you must edit settings and scripts, so one easy editor is an essential tool.</p>
<h3>Why nano?</h3>
<p>Linux has many editors. <strong>nano</strong> is the friendliest: it shows its shortcuts at the bottom of the screen, and it is installed on Ubuntu and most Debian systems. (Editors such as vim are very powerful, but they take longer to learn.) If nano is missing, you will learn how to install it in Module 4.</p>
<h3>Opening and saving</h3>
<pre><code>nano notes.txt</code></pre>
<p>If the file does not exist, nano starts a new one and creates it when you save. Type your text like normal. At the bottom you will see lines such as <code>^O Write Out</code>. The <code>^</code> sign means the <strong>Ctrl</strong> key.</p>
<table>
<thead><tr><th>Keys</th><th>Action</th></tr></thead>
<tbody>
<tr><td>Ctrl+O, then Enter</td><td>Save ("write out"), keeping the file name</td></tr>
<tr><td>Ctrl+X</td><td>Exit (it asks to save if there are changes)</td></tr>
<tr><td>Ctrl+W</td><td>Search for text</td></tr>
<tr><td>Ctrl+K</td><td>Cut the current line</td></tr>
<tr><td>Ctrl+U</td><td>Paste the cut line</td></tr>
<tr><td>Ctrl+G</td><td>Show help</td></tr>
</tbody>
</table>
<p>Use the arrow keys to move: the mouse normally does not work in a terminal. To paste text from outside, use the terminal's paste shortcut (often Ctrl+Shift+V, or right-click).</p>
<h3>Editing system files</h3>
<p>Files in <code>/etc</code> usually belong to the administrator, so an ordinary user can read them but cannot save changes. You use <code>sudo</code> (explained in Module 3) in front of the editor:</p>
<pre><code>sudo nano /etc/hostname</code></pre>
<p>Before changing any important file, make a backup copy so you can undo your change:</p>
<pre><code>sudo cp /etc/hostname /etc/hostname.bak</code></pre>
<h3>Common mistakes</h3>
<ul>
<li>Pressing Ctrl+X and answering "N" by mistake, which throws away your edits.</li>
<li>Editing a file as a normal user and then being told "Permission denied" at save time.</li>
<li>Making a typo in a settings file and not keeping a backup.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Go to your <code>practice</code> folder and run <code>nano shopping.txt</code>.</li>
<li>Type three items you would buy for a small shop, one per line.</li>
<li>Save with Ctrl+O and Enter, then exit with Ctrl+X.</li>
<li>Show the file with <code>cat shopping.txt</code>, then open it again and use Ctrl+K and Ctrl+U to move the first line to another place.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>nano is a simple terminal editor with its shortcuts listed on screen.</li>
<li>Ctrl+O saves, Ctrl+X exits, Ctrl+W searches.</li>
<li>System files need <code>sudo nano</code>, and a backup first.</li>
<li>The mouse is usually not used inside terminal editors.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Redirects and pipes',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to send command output into files and join commands together. This idea, small tools combined to do big jobs, is the heart of the Linux way of working, and it saves hours when you process lists, logs and reports.</p>
<h3>Input and output streams</h3>
<p>Every command has a place it reads from (<strong>standard input</strong>), a place it writes results to (<strong>standard output</strong>, normally your screen) and a place for error messages (<strong>standard error</strong>, also your screen by default). Redirection changes those places.</p>
<h3>Redirecting to a file</h3>
<table>
<thead><tr><th>Symbol</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td><code>&gt;</code></td><td>Send output to a file, <strong>replacing</strong> what was in it</td></tr>
<tr><td><code>&gt;&gt;</code></td><td><strong>Add</strong> output to the end of a file</td></tr>
<tr><td><code>&lt;</code></td><td>Read input from a file</td></tr>
<tr><td><code>2&gt;</code></td><td>Send error messages to a file</td></tr>
</tbody>
</table>
<pre><code>echo "Opening stock count" &gt; stock.txt     # creates or replaces the file
echo "Rice: 20 bags" &gt;&gt; stock.txt          # adds a line
echo "Beans: 15 bags" &gt;&gt; stock.txt
ls /etc &gt; etc-list.txt                      # save a listing
ls /nonexistent 2&gt; errors.txt               # save only the error</code></pre>
<p><strong>Warning.</strong> A single <code>&gt;</code> overwrites without asking. If you type <code>&gt; important.txt</code> by mistake you lose its contents. When you want to add, use <code>&gt;&gt;</code>.</p>
<h3>Pipes</h3>
<p>The pipe symbol <code>|</code> (usually Shift plus the backslash key) sends the output of one command straight into the input of the next, without a file.</p>
<pre><code>ls /etc | less            # scroll a long listing
cat stock.txt | wc -l     # count the lines of the file
ls /etc | head -n 5       # first five names only
history | tail -n 10      # your last ten commands</code></pre>
<p>You can chain as many as you need: <code>command1 | command2 | command3</code>. Each does one small job. In Module 4 you will meet <code>grep</code>, <code>sort</code> and <code>uniq</code>, which work wonderfully in pipes.</p>
<h3>Handy extras</h3>
<ul>
<li><code>echo</code> prints text; <code>date</code> prints the date and time; <code>whoami</code> prints your username.</li>
<li>Combine: <code>date &gt;&gt; log.txt</code> adds a time stamp line to a file.</li>
<li><code>command1 &amp;&amp; command2</code> runs the second only if the first succeeded; <code>command1 ; command2</code> runs both.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>In <code>practice</code>, run <code>echo "Sales log" &gt; sales.txt</code>, then add two lines with <code>&gt;&gt;</code>.</li>
<li>Check the file with <code>cat sales.txt</code>, then count its lines with <code>cat sales.txt | wc -l</code>. You should get 3.</li>
<li>Run <code>ls /etc | head -n 5 &gt; first5.txt</code> and display the result.</li>
<li>Run <code>date &gt;&gt; sales.txt</code> and see the new last line with <code>tail -n 1 sales.txt</code>.</li>
</ol>
<h3>Key points</h3>
<ul>
<li><code>&gt;</code> replaces a file, <code>&gt;&gt;</code> appends, <code>2&gt;</code> saves errors.</li>
<li>A pipe <code>|</code> feeds one command's output into the next command.</li>
<li>Small tools joined together solve big problems.</li>
<li>Take care: a single <code>&gt;</code> on the wrong file erases it.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which command creates nested folders projects/2026/notes in one step?
- mkdir projects/2026/notes
* mkdir -p projects/2026/notes
- touch projects/2026/notes
- cp -r projects/2026/notes
= The -p option makes all missing parent folders. Without it mkdir fails if projects does not exist yet.

Q: How do you rename report.txt to final.txt?
- rn report.txt final.txt
- cp report.txt final.txt
* mv report.txt final.txt
- rename report.txt
= mv moves a file, and moving it to a new name in the same folder is how you rename it.

Q: What happens when you delete a file with rm in the terminal?
- It goes to a recycle bin you can restore from
- It is hidden until you restart
* It is removed with no bin, so it is very hard to get back
- It is copied to /tmp
= The command line has no recycle bin. That is why you check with pwd and ls first, and use rm -i while learning.

Q: Which command shows the last 20 lines of a log file called app.log?
- head -n 20 app.log
- cat -20 app.log
* tail -n 20 app.log
- less -20 app.log
= tail shows the end of a file. head shows the start.

Q: Which pattern matches report1.txt and report2.txt but not report10.txt?
- report*.txt
* report?.txt
- report[].txt
- report.txt
= ? matches exactly one character, while * would also match the 0 in report10.txt.

Q: You run echo "Beans: 15 bags" > stock.txt when stock.txt already has five lines. What is the result?
- The line is added at the end
- Nothing, because the file exists
* The old content is replaced by the one new line
- An error is shown asking for sudo
= A single > overwrites the file. Use >> to add to the end.

Q: Which nano keys save the file and exit? (choose the correct order)
- Ctrl+X, then Ctrl+K
* Ctrl+O then Enter, then Ctrl+X
- Ctrl+S then Ctrl+Q
- Ctrl+W then Ctrl+U
= Ctrl+O writes the file, Enter keeps the name, and Ctrl+X leaves nano.

Q: What does ls /etc | wc -l do?
- Deletes the files in /etc
* Counts how many items /etc contains
- Sorts the files by size
- Saves the list to a file named wc
= The pipe sends the list from ls into wc -l, which counts lines.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Permissions, users and processes',
            'summary' => 'Understand who may do what, use sudo wisely, and see and control running programs.',
            'lessons' => [
                [
                    'title'   => 'Users, groups and sudo',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain users and groups, check who you are, and use <code>sudo</code> safely. Linux was designed for many people sharing one machine, so its safety rules (who may read, change or run what) are the base of its reputation for security.</p>
<h3>Users and groups</h3>
<p>Every person or program that uses Linux does so as a <strong>user</strong>, each with a user name and a number. Users can belong to <strong>groups</strong>, such as a group for the finance team. Files are owned by one user and one group, and the rules about access are given to the owner, the group and everyone else.</p>
<pre><code>whoami          # your user name
id              # your user number and groups
groups          # just the group names
who             # who is logged in now</code></pre>
<p>Ordinary users can change only their own files. The special user <strong>root</strong> can change everything. Running everything as root is dangerous, because a typing mistake or a malicious program then has full power.</p>
<h3>sudo: borrowing power for one command</h3>
<p><code>sudo</code> ("superuser do") runs one command with administrator rights, if your account is allowed to (on Ubuntu the first user is in the <code>sudo</code> group). It asks for <strong>your own</strong> password, and nothing shows on screen as you type it; that is normal.</p>
<pre><code>sudo apt update          # an administrator job
sudo nano /etc/hostname  # edit a protected file
sudo whoami              # prints root</code></pre>
<h3>Why be careful with sudo</h3>
<ol>
<li>With sudo, mistakes are not stopped by permissions. <code>sudo rm -r</code> in the wrong place can wreck the system.</li>
<li>Read each command before pressing Enter. Add sudo only when the error says "Permission denied" and you know why.</li>
<li>Do not paste commands from the internet with sudo unless you understand each part.</li>
<li>Avoid <code>sudo su</code> or a long root session: leave it with <code>exit</code>.</li>
<li>Use a strong password, and never share sudo access casually.</li>
</ol>
<h3>Managing users (administrator tasks)</h3>
<p>On a practice machine you can create and remove accounts. Use these with sudo.</p>
<pre><code>sudo adduser juma                 # create user "juma" and ask for a password
sudo usermod -aG sudo juma        # add juma to the sudo group (only for trusted admins)
sudo groupadd students            # make a group
sudo usermod -aG students juma    # add juma to it
sudo deluser juma                 # remove the account (the home folder stays)</code></pre>
<p>The <code>-a</code> in <code>usermod -aG</code> means <em>append</em>. Leave it out and you replace all the user's groups, which can lock them out. Group changes take effect after the user logs out and in again.</p>
<h3>Try it yourself</h3>
<ol>
<li>Run <code>whoami</code>, <code>id</code> and <code>groups</code>. Are you in the <code>sudo</code> group?</li>
<li>Run <code>sudo whoami</code>. It should print <code>root</code>. Notice that the password is hidden as you type.</li>
<li>On your practice machine only, create a user: <code>sudo adduser guest1</code>, and answer the questions.</li>
<li>Check that the user exists with <code>grep guest1 /etc/passwd</code> and remove them with <code>sudo deluser guest1</code>.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Every user belongs to groups, and files have an owner and a group.</li>
<li>root can do anything; use it as little as possible.</li>
<li>sudo gives power for one command and asks for your own password.</li>
<li>Use <code>usermod -aG</code> to add to a group without removing others.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'File permissions: ls -l, chmod and chown',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to read a permission string, change it with chmod, and change an owner with chown. When a website cannot save an upload, or a script "will not run", the cause is nearly always a permission setting.</p>
<h3>Reading ls -l</h3>
<pre><code>ls -l report.txt
-rw-r--r-- 1 amina staff 2048 Mar 10 09:15 report.txt</code></pre>
<p>The first group of ten characters is the type and the permissions. The next items are the owner (<code>amina</code>), the group (<code>staff</code>), the size and the date.</p>
<table>
<thead><tr><th>Part</th><th>Characters</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>Type</td><td><code>-</code></td><td>Normal file (<code>d</code> would be a folder)</td></tr>
<tr><td>Owner</td><td><code>rw-</code></td><td>The owner can read and write</td></tr>
<tr><td>Group</td><td><code>r--</code></td><td>The group can only read</td></tr>
<tr><td>Others</td><td><code>r--</code></td><td>Everyone else can only read</td></tr>
</tbody>
</table>
<p><code>r</code> is read, <code>w</code> is write, <code>x</code> is execute (run as a program, or enter a folder). A dash means "not allowed".</p>
<h3>Numbers for permissions</h3>
<p>Each permission has a value: read 4, write 2, execute 1. Add them for each of owner, group and others.</p>
<table>
<thead><tr><th>Number</th><th>Letters</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>7</td><td>rwx</td><td>4 + 2 + 1</td></tr>
<tr><td>6</td><td>rw-</td><td>4 + 2</td></tr>
<tr><td>5</td><td>r-x</td><td>4 + 1</td></tr>
<tr><td>4</td><td>r--</td><td>read only</td></tr>
<tr><td>0</td><td>---</td><td>nothing</td></tr>
</tbody>
</table>
<p>So <code>644</code> is <code>rw-r--r--</code> (a normal file), <code>755</code> is <code>rwxr-xr-x</code> (a program or a shared folder) and <code>600</code> is <code>rw-------</code> (private).</p>
<h3>chmod: change the mode</h3>
<pre><code>chmod 644 report.txt        # numbers
chmod 600 secret.txt        # only the owner can read and write
chmod +x backup.sh          # letters: add execute for everyone
chmod u+x,go-w script.sh    # owner gets x; group and others lose w
chmod -R 755 website/       # whole folder (use carefully)</code></pre>
<p>In letter form, <code>u</code> is the owner (user), <code>g</code> the group, <code>o</code> others, <code>a</code> all. Avoid <code>chmod 777</code> (everyone may do everything). It is a common "quick fix" that leaves files open to anyone who can reach the system.</p>
<h3>chown: change the owner</h3>
<p>Only root may give a file to someone else:</p>
<pre><code>sudo chown juma report.txt              # new owner
sudo chown juma:students report.txt     # owner and group
sudo chown -R www-data:www-data /var/www/html   # a folder and its contents</code></pre>
<p>Be careful with <code>-R</code> and always check the path before pressing Enter: on a system folder it can break programs.</p>
<h3>Try it yourself</h3>
<ol>
<li>In <code>practice</code>, run <code>touch secret.txt</code> and <code>ls -l secret.txt</code>. Write the permission string.</li>
<li>Run <code>chmod 600 secret.txt</code> and check again. What changed for the group and others?</li>
<li>Create <code>hello.sh</code> with <code>nano</code> containing <code>echo Hello</code>. Try <code>./hello.sh</code>, read the error, then run <code>chmod +x hello.sh</code> and try again.</li>
<li>What number gives the owner full rights, the group read and execute, and others nothing? (Answer: 750.)</li>
</ol>
<h3>Key points</h3>
<ul>
<li>r is 4, w is 2, x is 1; add them for each of owner, group, others.</li>
<li><code>chmod</code> changes permissions; <code>chown</code> changes owner and needs sudo.</li>
<li>644 for files, 755 for programs and folders, 600 for private files are common choices.</li>
<li>Avoid 777; fix the real cause instead.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Processes: ps, top and kill',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to see what is running on a Linux machine, find a program that is using too much memory or processor time, and stop a program that has frozen. This is the command-line version of the Task Manager.</p>
<h3>What is a process?</h3>
<p>A <strong>process</strong> is a program that is running. Each has a number called the <strong>PID</strong> (process ID). Some processes belong to you, and some are <strong>services</strong> (also called daemons) that run quietly in the background, such as a web server.</p>
<h3>ps: a snapshot</h3>
<pre><code>ps                # processes in this terminal
ps aux            # all processes, with owner, CPU and memory use
ps aux | grep nano   # find a particular one</code></pre>
<p>In the <code>ps aux</code> table, <code>%CPU</code> and <code>%MEM</code> show how much processor and memory each process uses, and <code>COMMAND</code> shows what it is. (The <code>grep</code> command, which filters lines, is covered in Module 4; here it just picks out lines that contain the word.)</p>
<h3>top: a live view</h3>
<pre><code>top</code></pre>
<p>The screen refreshes every few seconds. The top lines show how long the machine has been running, the <strong>load average</strong> (a rough measure of how busy it is), and memory use. Below is a list of processes, busiest first. Keys: <code>q</code> quits, <code>M</code> sorts by memory, <code>P</code> sorts by processor, <code>k</code> asks for a PID to kill. Many systems also have the friendlier <code>htop</code>, which you can install later.</p>
<h3>Stopping a process with kill</h3>
<p>Despite the name, <code>kill</code> sends a <strong>signal</strong> to a process. The polite one asks it to finish; the forceful one removes it at once.</p>
<pre><code>kill 4321          # ask process 4321 to end politely (SIGTERM)
kill -9 4321       # force it to stop (SIGKILL), last resort
pkill firefox      # by name
killall nano       # by name, all copies</code></pre>
<ol>
<li>Try the polite <code>kill PID</code> first and wait a few seconds.</li>
<li>Use <code>kill -9</code> only if the program does not respond; it cannot save its work.</li>
<li>Never kill a process if you do not know what it is. Killing system services can crash the machine.</li>
<li>You can stop your own processes; stopping others needs sudo.</li>
</ol>
<h3>Foreground and background</h3>
<p>Add <code>&amp;</code> to start a command in the background, such as <code>sleep 300 &amp;</code>. Use <code>jobs</code> to list them, and Ctrl+C to stop a command running in front. Later, you will see services managed with <code>systemctl</code>, which works on Ubuntu and Debian.</p>
<pre><code>systemctl status ssh        # is the service running? (service name may differ)
free -h                     # memory use
df -h                       # disk space use
uptime                      # how long the machine has been on</code></pre>
<h3>Try it yourself</h3>
<ol>
<li>Run <code>sleep 600 &amp;</code> to start a harmless background process.</li>
<li>Run <code>ps aux | grep sleep</code> and note its PID (the second column).</li>
<li>Stop it with <code>kill</code> followed by that PID, then run the <code>ps</code> command again to confirm it is gone.</li>
<li>Run <code>free -h</code> and <code>df -h</code> and write down your free memory and free disk space.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Every running program is a process with a PID.</li>
<li><code>ps aux</code> gives a snapshot, <code>top</code> a live view.</li>
<li><code>kill PID</code> asks politely; <code>kill -9</code> forces, so use it last.</li>
<li><code>free -h</code>, <code>df -h</code> and <code>uptime</code> give a quick health check.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does sudo do?
- Switches off the firewall
* Runs one command with administrator rights, after asking for your password
- Makes the file hidden
- Logs you out
= sudo lends administrator power for a single command. On Ubuntu it asks for your own password.

Q: The permission string -rwxr-x--- means:
- Everyone can read and run the file
* The owner can read, write and run; the group can read and run; others have no access
- The owner can only read
- It is a folder owned by root
= The first dash is the type (a file); then owner rwx, group r-x and others ---.

Q: Which number is the same as rw-r--r--?
- 755
- 600
* 644
- 777
= Owner rw- is 4+2=6, group r-- is 4, others r-- is 4, so 644.

Q: A script says "Permission denied" when you run ./backup.sh. What is the most likely fix?
- Delete the script
* chmod +x backup.sh
- chown root backup.sh
- Restart the computer
= The file is probably missing its execute permission, and chmod +x adds it.

Q: Why is chmod 777 a poor "quick fix"?
- It deletes the file
- It only works for root
* It lets everyone read, change and run the file, which is unsafe
- It makes the file hidden
= 777 gives every user full control. Find the real cause (usually owner or group) and fix that instead.

Q: Which commands add the user juma to an existing group called students without removing other groups?
- usermod -G students juma
* sudo usermod -aG students juma
- sudo groupadd juma
- sudo chown students juma
= -a (append) with -G keeps the existing groups. Without -a the groups would be replaced.

Q: A program has frozen. Which is the best order of actions?
- kill -9 first, then think
- Restart the computer immediately
* Find its PID with ps, try kill PID, and use kill -9 only if needed
- Delete its folder
= Polite termination lets the program close properly. kill -9 is the last resort.

Q: Which command shows disk space in readable units?
- free -h
- top
* df -h
- ps aux
= df shows disk space, free shows memory, and -h gives human-readable sizes.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Software and text tools',
            'summary' => 'Install and update software, search and summarise text, write small scripts and schedule jobs.',
            'lessons' => [
                [
                    'title'   => 'Installing software with apt',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to install, update and remove software on Debian and Ubuntu with <code>apt</code>. Linux programs come from trusted online collections, not random websites, which is one big reason Linux systems are safer and easier to keep up to date.</p>
<h3>Packages and repositories</h3>
<p>Software comes as <strong>packages</strong>. A <strong>package manager</strong> downloads, installs, updates and removes them, and also installs anything the package needs (its <em>dependencies</em>). Packages are stored in <strong>repositories</strong>, servers run by the distribution and checked with digital signatures. Debian and Ubuntu use <code>apt</code>. Other families have their own tools (for example <code>dnf</code> on Fedora), but the idea is the same.</p>
<h3>The main commands</h3>
<table>
<thead><tr><th>Command</th><th>Job</th></tr></thead>
<tbody>
<tr><td><code>sudo apt update</code></td><td>Refresh the list of available packages (installs nothing)</td></tr>
<tr><td><code>sudo apt upgrade</code></td><td>Install newer versions of what you already have</td></tr>
<tr><td><code>sudo apt install htop</code></td><td>Install a package</td></tr>
<tr><td><code>sudo apt remove htop</code></td><td>Remove a package, keep its settings</td></tr>
<tr><td><code>sudo apt purge htop</code></td><td>Remove a package and its settings</td></tr>
<tr><td><code>sudo apt autoremove</code></td><td>Remove unneeded leftover dependencies</td></tr>
<tr><td><code>apt search word</code></td><td>Search package names and descriptions</td></tr>
<tr><td><code>apt show htop</code></td><td>Read about a package</td></tr>
</tbody>
</table>
<h3>A normal session</h3>
<pre><code>sudo apt update
sudo apt install htop tree
htop
tree practice</code></pre>
<p>apt shows what it will do and asks <code>Do you want to continue? [Y/n]</code>. Read the list, especially packages to be <em>removed</em>, before you press Y. Add <code>-y</code> only in scripts when you trust the command.</p>
<h3>Keeping the system updated</h3>
<p>Updates fix security holes, so run these regularly on any machine you manage:</p>
<pre><code>sudo apt update &amp;&amp; sudo apt upgrade</code></pre>
<p>Run <code>apt update</code> first, otherwise your computer does not know that newer versions exist. If you see "Could not get lock", another update is running; wait a few minutes. If apt cannot download, check your internet connection (Module 5).</p>
<h3>Be careful where software comes from</h3>
<ul>
<li>Prefer packages from the official repositories.</li>
<li>Do not add unknown repositories or run installer scripts from strangers, because they run with your power.</li>
<li>Other formats exist (snap, Flatpak, downloading a <code>.deb</code> file for <code>sudo apt install ./file.deb</code>), but only use them from sources you trust.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Run <code>sudo apt update</code> and watch the lines scroll by.</li>
<li>Install two small tools: <code>sudo apt install tree htop</code>.</li>
<li>Run <code>tree</code> inside your <code>practice</code> folder, then start <code>htop</code> and quit with <code>q</code>.</li>
<li>Remove <code>tree</code> with <code>sudo apt remove tree</code> and confirm with <code>which tree</code> (it prints nothing when missing).</li>
</ol>
<h3>Key points</h3>
<ul>
<li>apt installs software and its dependencies from signed repositories.</li>
<li>Run <code>apt update</code> before <code>apt upgrade</code> or install.</li>
<li>Keep systems updated for security.</li>
<li>Use only trusted sources; read the confirmation list before saying yes.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Searching and summarising text: grep, find, sort, uniq, wc',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to search inside files, find files by name, and sort, count and summarise lines. These tools turn a big log or list into answers in seconds, for example "how many failed logins were there today?"</p>
<h3>Practice data</h3>
<p>Create a small file so you can follow along:</p>
<pre><code>cd ~/practice
printf "mango\nbanana\nmango\norange\nbanana\nmango\n" &gt; fruit.txt
cat fruit.txt</code></pre>
<h3>grep: search for text</h3>
<pre><code>grep mango fruit.txt           # lines containing "mango"
grep -i MANGO fruit.txt        # ignore upper/lower case
grep -v mango fruit.txt        # lines that do NOT contain it
grep -c mango fruit.txt        # count matching lines (3)
grep -n banana fruit.txt       # show line numbers
grep -r "PermitRootLogin" /etc/ssh   # search inside every file in a folder</code></pre>
<p>Without a file name, grep reads from a pipe, so you can filter any output: <code>ps aux | grep sleep</code>.</p>
<h3>find: search for files</h3>
<pre><code>find ~/practice -name "*.txt"          # by name (quote the pattern)
find /var/log -name "*.log" -size +1M  # larger than 1 MB
find ~ -type d -name "week*"           # folders only
find ~/practice -mtime -1              # changed within the last day</code></pre>
<p>Reading <code>/var</code> or <code>/etc</code> as a normal user may print "Permission denied" for some folders; that only means you may not look inside them.</p>
<h3>sort, uniq and wc</h3>
<table>
<thead><tr><th>Command</th><th>Job</th></tr></thead>
<tbody>
<tr><td><code>sort file</code></td><td>Sort lines alphabetically (<code>-n</code> numbers, <code>-r</code> reverse)</td></tr>
<tr><td><code>uniq</code></td><td>Remove <em>neighbouring</em> repeated lines (so sort first); <code>-c</code> counts them</td></tr>
<tr><td><code>wc -l file</code></td><td>Count lines (<code>-w</code> words, <code>-c</code> bytes)</td></tr>
</tbody>
</table>
<p>Because <code>uniq</code> only compares neighbours, you nearly always use it after <code>sort</code>:</p>
<pre><code>sort fruit.txt | uniq -c
      2 banana
      3 mango
      1 orange
sort fruit.txt | uniq -c | sort -nr     # most common first
wc -l fruit.txt                          # 6 fruit.txt</code></pre>
<h3>A real example</h3>
<p>Count failed login attempts recorded in an authentication log (the file name may differ; on Ubuntu servers it is often <code>/var/log/auth.log</code>, and it needs sudo to read):</p>
<pre><code>sudo grep -c "Failed password" /var/log/auth.log</code></pre>
<h3>Try it yourself</h3>
<ol>
<li>Create <code>fruit.txt</code> as shown, and run <code>grep -c banana fruit.txt</code>. The answer is 2.</li>
<li>Run <code>sort fruit.txt | uniq -c | sort -nr</code> and name the most common fruit.</li>
<li>Use <code>find ~ -name "fruit*"</code> to locate your file from your home folder.</li>
<li>Run <code>grep -r "http" /etc/services | wc -l</code> to count how many lines mention http (the number may differ on your system).</li>
</ol>
<h3>Key points</h3>
<ul>
<li><code>grep</code> finds lines; <code>find</code> finds files.</li>
<li><code>sort</code> then <code>uniq -c</code> counts how often each line appears.</li>
<li><code>wc -l</code> counts lines.</li>
<li>Pipes join these tools; build the pipeline one step at a time.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Your first shell scripts',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a short shell script with variables, a decision and a loop, and run it. Scripts save time by repeating a job exactly the same way every time, such as a daily backup or a report of disk space.</p>
<h3>A first script</h3>
<p>A <strong>shell script</strong> is a text file of commands. The first line, called the <em>shebang</em>, says which program runs it.</p>
<pre><code>nano hello.sh</code></pre>
<pre><code>#!/bin/bash
echo "Hello from my first script"
date</code></pre>
<p>Save, then make it executable and run it:</p>
<pre><code>chmod +x hello.sh
./hello.sh</code></pre>
<p>The <code>./</code> says "the file in this folder". Without it the shell searches only the system program folders.</p>
<h3>Variables</h3>
<p>A variable stores a value. Write it with <strong>no spaces</strong> around the equals sign, and read it with a dollar sign.</p>
<pre><code>#!/bin/bash
name="Amina"
today=$(date +%F)
echo "Hello $name, today is $today"</code></pre>
<p><code>$(command)</code> puts a command's output into the variable. <code>date +%F</code> prints the date as 2026-03-10 style. Arguments given to the script appear as <code>$1</code>, <code>$2</code> and so on.</p>
<h3>Decisions with if</h3>
<pre><code>#!/bin/bash
file="$1"
if [ -f "$file" ]; then
    echo "$file exists"
else
    echo "$file is missing"
fi</code></pre>
<p>Run it as <code>./check.sh notes.txt</code>. Spaces inside the square brackets are required. Useful tests: <code>-f</code> file exists, <code>-d</code> folder exists, <code>-z</code> text is empty. To compare numbers use <code>-gt</code> (greater than), <code>-lt</code> (less than) and <code>-eq</code> (equal): <code>[ "$n" -gt 10 ]</code>.</p>
<h3>Loops</h3>
<pre><code>#!/bin/bash
for f in *.txt; do
    echo "Found: $f"
done

count=1
while [ "$count" -le 3 ]; do
    echo "Round $count"
    count=$((count + 1))
done</code></pre>
<p>A <code>for</code> loop repeats once for each item. A <code>while</code> loop repeats as long as the test is true. <code>$((...))</code> does arithmetic.</p>
<h3>A useful backup script</h3>
<pre><code>#!/bin/bash
src="$HOME/practice"
dest="$HOME/backups"
mkdir -p "$dest"
tar -czf "$dest/practice-$(date +%F).tar.gz" "$src"
echo "Backup finished"</code></pre>
<p>It makes a compressed archive of the folder, named with today's date. Always put quotes around variables so names with spaces do not break the script.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write <code>hello.sh</code> as above, make it executable and run it.</li>
<li>Change it to greet a name given as the first argument: <code>echo "Hello $1"</code>, and run <code>./hello.sh Juma</code>.</li>
<li>Write the <code>for</code> loop script and run it in a folder with at least two <code>.txt</code> files.</li>
<li>Run the backup script, then check with <code>ls ~/backups</code>.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A script starts with <code>#!/bin/bash</code> and needs <code>chmod +x</code>.</li>
<li>Variables have no spaces around <code>=</code>; read them with <code>$</code>.</li>
<li><code>if</code>, <code>for</code> and <code>while</code> add decisions and repetition.</li>
<li>Quote your variables and test scripts on practice data first.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Scheduling jobs with cron',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what cron is and schedule a script to run at a chosen time. Scheduled jobs are how servers back up data, clean old files and send reports without anyone being there.</p>
<h3>What cron does</h3>
<p><strong>cron</strong> is a service that runs commands on a schedule. Each user has a list of scheduled jobs called a <strong>crontab</strong>. Edit yours with:</p>
<pre><code>crontab -e</code></pre>
<p>The first time, it may ask which editor to use: choose <code>nano</code>. See your list with <code>crontab -l</code>.</p>
<h3>The five time fields</h3>
<p>Each line has five time fields and then the command:</p>
<pre><code>minute  hour  day-of-month  month  day-of-week  command</code></pre>
<table>
<thead><tr><th>Field</th><th>Allowed values</th></tr></thead>
<tbody>
<tr><td>Minute</td><td>0 to 59</td></tr>
<tr><td>Hour</td><td>0 to 23 (24-hour clock)</td></tr>
<tr><td>Day of month</td><td>1 to 31</td></tr>
<tr><td>Month</td><td>1 to 12</td></tr>
<tr><td>Day of week</td><td>0 to 7 (0 and 7 are Sunday; 1 is Monday)</td></tr>
</tbody>
</table>
<p>An asterisk <code>*</code> means "every". A slash such as <code>*/15</code> means "every 15".</p>
<h3>Examples</h3>
<pre><code>30 2 * * *   /home/amina/backup.sh          # every day at 02:30
0 8 * * 1    /home/amina/weekly-report.sh   # every Monday at 08:00
*/15 * * * * /home/amina/check.sh           # every 15 minutes
0 0 1 * *    /home/amina/monthly.sh         # first day of each month at midnight</code></pre>
<h3>Good practice for cron jobs</h3>
<ol>
<li>Use full paths for the script and any files, because cron has a very small environment and does not start in your folder.</li>
<li>Make the script executable and test it by hand first.</li>
<li>Save the output so you can see failures: add <code>&gt;&gt; /home/amina/backup.log 2&gt;&amp;1</code> at the end of the line. The <code>2&gt;&amp;1</code> part sends error messages to the same place as normal output.</li>
<li>Remember the server's time zone: check with <code>date</code> or <code>timedatectl</code>.</li>
<li>Do not schedule destructive commands until you have tested them in a safe folder.</li>
</ol>
<p>A complete example line with logging:</p>
<pre><code>30 2 * * * /home/amina/backup.sh &gt;&gt; /home/amina/backup.log 2&gt;&amp;1</code></pre>
<p>Other tools also schedule work: <code>at</code> runs a job once at a given time, and systemd timers are a newer alternative, but cron is everywhere and is the one to learn first.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a script <code>~/tick.sh</code> containing <code>#!/bin/bash</code> and <code>date &gt;&gt; ~/tick.log</code>, and make it executable.</li>
<li>Run <code>crontab -e</code> and add the line <code>* * * * * /home/YOURNAME/tick.sh</code>, replacing YOURNAME with your user name.</li>
<li>Wait three minutes and then run <code>cat ~/tick.log</code>. You should see several time stamps.</li>
<li>Remove the job again with <code>crontab -e</code> so it does not run forever.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>cron runs jobs on a schedule from a crontab edited with <code>crontab -e</code>.</li>
<li>The five fields are minute, hour, day of month, month and day of week.</li>
<li>Use full paths, log the output and test first.</li>
<li>Check the server time zone when scheduling.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Why should you run sudo apt update before sudo apt upgrade?
- To delete old packages
* To refresh the list of available package versions so upgrade knows what is new
- To restart the computer
- To change the repository password
= apt update only refreshes the package list; upgrade then installs newer versions from that list.

Q: Which command removes a package AND its settings?
- sudo apt remove name
- sudo apt autoremove name
* sudo apt purge name
- sudo apt delete name
= purge removes the package together with its configuration files. remove keeps the settings.

Q: What does grep -c mango fruit.txt print?
- The lines that contain mango
- The line numbers
* How many lines contain mango
- Nothing, it deletes mango
= The -c option counts the matching lines instead of printing them.

Q: Why do you usually write sort file | uniq -c rather than only uniq -c file?
- uniq cannot read files
* uniq only compares neighbouring lines, so equal lines must be next to each other first
- sort makes the file shorter
- uniq requires sudo
= uniq removes only adjacent duplicates. Sorting first puts equal lines together.

Q: Which command finds all .log files under /var/log?
- grep .log /var/log
* find /var/log -name "*.log"
- ls /var/log/.log
- cat /var/log/*
= find searches for files by name, size or time. The quoted pattern stops the shell expanding it too early.

Q: In a script you write name = "Amina" and it fails. What is wrong?
- Names cannot contain capital letters
- You must use sudo
* Shell variables must be written with no spaces around the equals sign
- Quotes are not allowed
= It must be name="Amina". With spaces the shell thinks name is a command.

Q: What does the cron line 30 2 * * * mean?
- Every 30 minutes on the second of the month
* Every day at 02:30
- Every Tuesday at 03:00
- Only on 30 February
= The fields are minute (30), hour (2), and then every day, month and weekday.

Q: Why should cron jobs use full file paths?
- Short paths are not allowed in Linux
- It makes the script run faster
* Cron runs with a small environment and does not start in your own folder
- Full paths turn on logging
= cron does not use your normal working folder or settings, so relative paths often fail.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Networking and servers',
            'summary' => 'Check connections, log in to remote machines, read logs, and build and look after a small web server.',
            'lessons' => [
                [
                    'title'   => 'Network commands: ip, ping, ssh, scp and curl',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to check a machine's network address, test a connection, log in to another computer securely, copy files between machines and fetch web pages from the terminal. Nearly every server job begins with one of these commands.</p>
<h3>Looking at your network</h3>
<pre><code>ip addr                 # network cards and their IP addresses
ip -br addr             # a shorter table
ip route                # routes, including the default gateway
hostname -I             # the machine's addresses (one line)</code></pre>
<p>Look for an address such as <code>192.168.1.25</code> next to a name like <code>eth0</code>, <code>ens33</code> or <code>wlan0</code>. The line starting <code>default via</code> in <code>ip route</code> shows your <strong>gateway</strong>, the router that connects you to the internet. The address <code>127.0.0.1</code> (<code>lo</code>) is always your own machine.</p>
<h3>ping: is it reachable?</h3>
<pre><code>ping -c 4 8.8.8.8        # four tries to a public address
ping -c 4 example.com    # also tests name lookup (DNS)</code></pre>
<p>On Linux, ping runs forever unless you add <code>-c</code> for a count, or press Ctrl+C. If the number works but the name does not, the problem is DNS. If neither works, check the cable, Wi-Fi, gateway and ISP. Some machines block ping, so a failed ping is a clue, not proof.</p>
<h3>ssh: log in to another machine</h3>
<p><strong>SSH</strong> (Secure Shell) gives you an encrypted terminal on a remote computer. The server needs the SSH service installed:</p>
<pre><code>sudo apt install openssh-server       # on the machine you will log in TO
ssh amina@192.168.1.25                # from another machine
ssh -p 2222 amina@server.example      # when the port is not 22</code></pre>
<p>The first time, ssh shows a <em>fingerprint</em> and asks you to confirm it: say yes only if you trust the machine. Leave with <code>exit</code>. Stronger than passwords are <strong>key pairs</strong>: <code>ssh-keygen</code> creates a private and a public key, and <code>ssh-copy-id amina@192.168.1.25</code> installs the public key on the server. Never share the private key.</p>
<h3>scp: copy files securely</h3>
<pre><code>scp report.txt amina@192.168.1.25:/home/amina/       # send
scp amina@192.168.1.25:/home/amina/data.csv .        # fetch to this folder
scp -r photos amina@192.168.1.25:/home/amina/        # a whole folder</code></pre>
<h3>curl: talk to the web</h3>
<pre><code>curl https://example.com                 # print the page
curl -I https://example.com              # only the headers
curl -o page.html https://example.com    # save to a file
curl -s ifconfig.me                      # your public address (a third-party service)</code></pre>
<p><code>curl</code> is also how developers test web services (APIs). <code>wget URL</code> is a similar tool for downloading files.</p>
<h3>Try it yourself</h3>
<ol>
<li>Run <code>ip -br addr</code> and write down your machine's private IP address.</li>
<li>Run <code>ip route</code> and note your default gateway, then <code>ping -c 3</code> that gateway.</li>
<li>Run <code>ping -c 3 example.com</code>. If it fails, try <code>ping -c 3 8.8.8.8</code> to see whether it is a DNS problem.</li>
<li>Run <code>curl -I https://example.com</code> and read the first line (look for <code>200</code>).</li>
</ol>
<h3>Key points</h3>
<ul>
<li><code>ip addr</code> and <code>ip route</code> show your address and gateway.</li>
<li><code>ping -c 4</code> tests reachability; name works but number does not means DNS trouble.</li>
<li><code>ssh</code> and <code>scp</code> are encrypted; keys are safer than passwords.</li>
<li><code>curl</code> fetches web content and tests services.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Firewalls and logs',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what a firewall does, set simple rules with ufw, and read logs to find out what happened on a server. Servers on the internet are scanned constantly, so a few basic controls and the habit of reading logs make a real difference.</p>
<h3>What a firewall does</h3>
<p>Programs listen for connections on numbered <strong>ports</strong>: for example 22 for SSH, 80 for web pages (HTTP) and 443 for secure web pages (HTTPS). A <strong>firewall</strong> decides which ports may be reached. The safest idea is: <em>block everything coming in, then allow only what you need</em>.</p>
<h3>ufw: the simple firewall</h3>
<p>Ubuntu includes <code>ufw</code> (Uncomplicated Firewall); install it with <code>sudo apt install ufw</code> if needed. <strong>Warning:</strong> if you are connected by ssh, allow SSH <em>before</em> turning the firewall on, or you will lock yourself out of the machine.</p>
<pre><code>sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh              # port 22
sudo ufw allow 80/tcp           # web
sudo ufw allow 443/tcp          # secure web
sudo ufw enable
sudo ufw status verbose</code></pre>
<p>Remove a rule with <code>sudo ufw delete allow 80/tcp</code>. To check which services are listening, use <code>sudo ss -tulpn</code>; it lists the ports and the programs that opened them. Close what you do not need (stop the service) rather than only hiding it.</p>
<h3>Where logs live</h3>
<p>Logs are the system's diary. Most are in <code>/var/log</code>; on modern systems, the <strong>journal</strong> collects messages from services too.</p>
<table>
<thead><tr><th>Where</th><th>What it records</th></tr></thead>
<tbody>
<tr><td><code>/var/log/syslog</code></td><td>General system messages on Ubuntu and Debian (when present)</td></tr>
<tr><td><code>/var/log/auth.log</code></td><td>Logins and sudo use (when present)</td></tr>
<tr><td><code>/var/log/apt/</code></td><td>What apt installed and removed</td></tr>
<tr><td><code>/var/log/nginx/</code> or <code>/var/log/apache2/</code></td><td>Web server access and error logs</td></tr>
</tbody>
</table>
<pre><code>sudo tail -n 30 /var/log/syslog
sudo tail -f /var/log/auth.log      # watch live, Ctrl+C to stop
sudo grep "Failed" /var/log/auth.log | tail</code></pre>
<h3>journalctl</h3>
<pre><code>journalctl -b                      # messages since this boot
journalctl -u ssh                  # messages from one service
journalctl -u ssh --since "1 hour ago"
journalctl -p err -b               # errors only, this boot
sudo journalctl -f                 # follow live</code></pre>
<p>If a service will not start, run <code>systemctl status name</code> and then <code>journalctl -u name</code>; the reason is usually written there. Some minimal systems do not keep logs in the same files, so use the journal when a file is missing.</p>
<h3>Try it yourself</h3>
<ol>
<li>On a practice machine, run <code>sudo ufw status</code>. If it is inactive, allow ssh first, then enable it.</li>
<li>Run <code>sudo ss -tulpn</code> and list the ports that are listening.</li>
<li>Run <code>journalctl -b -p err</code> and read any error lines. Do you understand them?</li>
<li>Run <code>sudo tail -n 10 /var/log/auth.log</code> (or <code>journalctl -u ssh</code>) and find your last login.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A firewall blocks incoming traffic except the ports you allow.</li>
<li>Allow SSH before enabling ufw on a remote machine.</li>
<li>Logs are in <code>/var/log</code> and in the journal; read them first when something fails.</li>
<li><code>systemctl status</code> and <code>journalctl -u</code> explain service problems.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Capstone: a small web server',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>In this capstone you will put your skills together by installing a web server on a practice machine, publishing a page, securing the machine and viewing the result. After it you will understand the basic steps behind a school website or a small business site.</p>
<p><strong>Use a practice virtual machine only</strong> (Ubuntu Server or Desktop), not a computer that holds important files. If you take a VM snapshot first, you can always go back.</p>
<h3>Plan</h3>
<ol>
<li>Update the system.</li>
<li>Install a web server (we use nginx; Apache works the same way with different names).</li>
<li>Check it is running and reachable.</li>
<li>Replace the default page with your own.</li>
<li>Open the firewall for web traffic and check the logs.</li>
</ol>
<h3>Step 1: update and install</h3>
<pre><code>sudo apt update
sudo apt install nginx</code></pre>
<p>On Debian and Ubuntu the service normally starts by itself. Check it:</p>
<pre><code>systemctl status nginx
sudo ss -tulpn | grep :80</code></pre>
<h3>Step 2: test from inside the machine</h3>
<pre><code>curl -I http://localhost</code></pre>
<p>The first line should say <code>HTTP/1.1 200 OK</code>. To test from another computer, find the VM's address with <code>ip -br addr</code>. In VirtualBox, the default "NAT" network hides the VM from other machines; switch the adapter to "Bridged" (or add a port forward) so your browser can reach it.</p>
<h3>Step 3: publish your own page</h3>
<p>nginx serves files from <code>/var/www/html</code>. Make a backup, then write your page:</p>
<pre><code>sudo cp /var/www/html/index.nginx-debian.html ~/index.default.bak
sudo nano /var/www/html/index.html</code></pre>
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;&lt;title&gt;My Practice Site&lt;/title&gt;&lt;/head&gt;
&lt;body&gt;
&lt;h1&gt;Welcome to my practice server&lt;/h1&gt;
&lt;p&gt;Built with Linux and nginx.&lt;/p&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
<p>Reload the page in your browser. (nginx prefers <code>index.html</code>, so it replaces the default page.)</p>
<h3>Step 4: firewall and logs</h3>
<pre><code>sudo ufw allow ssh
sudo ufw allow 'Nginx Full'     # ports 80 and 443
sudo ufw enable
sudo ufw status
sudo tail -n 5 /var/log/nginx/access.log</code></pre>
<p>Each visit appears as a line in the access log with the visitor's address and the page asked for. Errors go to <code>error.log</code>. After editing nginx settings, test them with <code>sudo nginx -t</code> and apply them with <code>sudo systemctl reload nginx</code>.</p>
<h3>Good habits and backups</h3>
<table>
<thead><tr><th>Habit</th><th>Why</th></tr></thead>
<tbody>
<tr><td>Update regularly</td><td>Fixes security holes</td></tr>
<tr><td>Use SSH keys and a strong sudo password</td><td>Stops guessing attacks</td></tr>
<tr><td>Open only needed ports</td><td>Fewer ways in</td></tr>
<tr><td>Read logs weekly</td><td>You notice problems early</td></tr>
<tr><td>Back up and test the restore</td><td>A backup you never restored may not work</td></tr>
</tbody>
</table>
<p>A simple backup of the site with the script from Module 4:</p>
<pre><code>tar -czf ~/site-backup-$(date +%F).tar.gz /var/www/html
scp ~/site-backup-*.tar.gz amina@backup-machine:/home/amina/backups/</code></pre>
<p>Keep a copy on a different machine or drive, since a backup on the same disk is lost with it.</p>
<h3>Try it yourself</h3>
<ol>
<li>Complete steps 1 to 4 on your practice machine and view your page from a browser.</li>
<li>Edit the page to add your name and a second paragraph, then reload.</li>
<li>Find your own visit in <code>/var/log/nginx/access.log</code>.</li>
<li>Create a backup with the <code>tar</code> command and check it with <code>ls -lh ~</code>.</li>
<li>Write five lines in a text file about what you would do before putting a real site on the internet.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Install, verify, customise, secure and monitor: the pattern for any server service.</li>
<li>nginx serves <code>/var/www/html</code>; test configuration with <code>nginx -t</code>.</li>
<li>Allow SSH in the firewall before enabling it.</li>
<li>Updates, least access, logs and tested backups keep a server healthy.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Good habits for working in the terminal',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will have a checklist of working habits that prevent most Linux disasters and make you faster. Experienced administrators are not people who never make mistakes; they are people who set up their work so that mistakes are small and can be undone.</p>
<h3>Before you press Enter</h3>
<ol>
<li><strong>Read the command twice</strong>, especially anything with <code>rm</code>, <code>chmod -R</code>, <code>chown -R</code>, <code>dd</code> or <code>&gt;</code>.</li>
<li><strong>Know where you are</strong> with <code>pwd</code>, and what is there with <code>ls</code>.</li>
<li><strong>Test first</strong>: replace <code>rm</code> by <code>ls</code> to see what a wildcard matches. Many tools have a dry-run option such as <code>apt -s upgrade</code> (simulate) or <code>rsync -n</code>.</li>
<li><strong>Understand pasted commands.</strong> Never run a command from a forum or video that you cannot explain, especially with sudo.</li>
<li><strong>Keep a backup</strong> of any file before editing it: <code>cp file file.bak</code>.</li>
</ol>
<h3>Work faster</h3>
<table>
<thead><tr><th>Trick</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Tab</td><td>Completes names; press twice to list options</td></tr>
<tr><td>Up / Down</td><td>Walk through earlier commands</td></tr>
<tr><td>Ctrl+R</td><td>Search your history while typing</td></tr>
<tr><td>Ctrl+A / Ctrl+E</td><td>Jump to start or end of the line</td></tr>
<tr><td>Ctrl+L</td><td>Clear the screen</td></tr>
<tr><td><code>!!</code></td><td>Repeat the last command, for example <code>sudo !!</code></td></tr>
<tr><td><code>history</code></td><td>List what you typed earlier</td></tr>
</tbody>
</table>
<p>You can also create <strong>aliases</strong>, shortcuts for long commands. Add this line to <code>~/.bashrc</code> using nano, then run <code>source ~/.bashrc</code>:</p>
<pre><code>alias ll='ls -lah'</code></pre>
<h3>Look after the system</h3>
<ul>
<li>Run updates on a schedule and reboot when the kernel changes.</li>
<li>Check disk space (<code>df -h</code>) and memory (<code>free -h</code>) often; a full disk causes odd failures.</li>
<li>Give each person their own account, and remove accounts that are no longer needed.</li>
<li>Do not log in as root; use sudo when needed.</li>
<li>Write short notes of what you changed and why, in a text file. Future you will be grateful.</li>
</ul>
<h3>When something goes wrong</h3>
<ol>
<li>Stay calm; do not start deleting things.</li>
<li>Read the error message in full. It usually names the file or the reason.</li>
<li>Check the logs with <code>journalctl</code> and <code>/var/log</code>.</li>
<li>Search the exact error message in a trusted source, or ask for help with the message, the command and your system version (<code>cat /etc/os-release</code>).</li>
<li>Restore from backup or snapshot if you broke something badly.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Add the <code>ll</code> alias to <code>~/.bashrc</code>, run <code>source ~/.bashrc</code> and test <code>ll</code>.</li>
<li>Use Ctrl+R and type <code>apt</code> to find an earlier apt command.</li>
<li>Run <code>sudo apt -s upgrade</code> to simulate an upgrade without changing anything.</li>
<li>Write a personal checklist of five habits you will follow before using sudo or rm.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Check where you are, test with ls or a dry run, and keep backups.</li>
<li>History, Tab and Ctrl+R save time and prevent typing errors.</li>
<li>Read errors and logs before searching for fixes.</li>
<li>Never run commands you do not understand, especially with sudo.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which command shows your default gateway on a modern Linux system?
- ls -l /gateway
- ping gateway
* ip route
- hostname -g
= The line starting with default via names the gateway (your router).

Q: ping 8.8.8.8 works but ping example.com fails. What is the most likely problem?
- The network cable is unplugged
- The firewall is turned off
* Name lookup (DNS) is failing
- The computer has no IP address
= The number reaches the internet, so the connection works. Turning a name into an address (DNS) is what failed.

Q: You are logged in by ssh and want to enable ufw. What must you do first?
- Reboot the server
- Uninstall ssh
* Allow the SSH port in ufw before enabling it
- Disable all outgoing traffic
= If you enable a deny-by-default firewall without allowing SSH, you lock yourself out of the remote machine.

Q: Which command copies report.txt to another machine securely?
- cp report.txt amina@192.168.1.25
- ping report.txt 192.168.1.25
* scp report.txt amina@192.168.1.25:/home/amina/
- cat report.txt | amina
= scp copies files over SSH. The destination has the form user@host:path.

Q: A service called ssh will not start. Which commands help you find out why?
* systemctl status ssh and journalctl -u ssh
- ls /ssh and rm /ssh
- ip addr and ip route
- crontab -l
= The status and the service's journal usually contain the exact reason for a failed start.

Q: Which are good habits for a server administrator? (choose all that apply)
* Update regularly
* Keep tested backups on another machine
- Use chmod 777 to fix permission errors
- Log in as root all the time
= Updates and tested off-machine backups protect a server. 777 and permanent root sessions create risk.

Q: Where does nginx serve the default web page from on Debian and Ubuntu?
- /etc/passwd
- /home/www
* /var/www/html
- /usr/bin/nginx
= The default web root for nginx on these systems is /var/www/html.

Q: Why test a wildcard with ls before using it with rm?
- ls is faster than rm
- rm does not support wildcards
* You can see exactly which files match before you delete anything
- ls changes file permissions
= The shell expands the wildcard the same way for both commands, so ls is a safe preview of what rm would remove.
QUIZ,
        ],
    ],

    'final_exam' => <<<'QUIZ'
Q: Which statement best describes a Linux distribution?
- The part of the system that talks to hardware
* The kernel combined with tools, software and often a desktop
- A type of shell
- A virtual machine program
= The kernel alone is Linux; a distribution such as Ubuntu bundles it with everything else.

Q: Why do most servers run without a desktop?
- Linux cannot show graphics
- Desktops are illegal on servers
* It saves memory and reduces the software that can break or be attacked
- Servers have no keyboard
= Servers are managed remotely by command line, so skipping graphics is more efficient and safer.

Q: Which is the safest way for a beginner to practise Linux commands?
- On the family computer that holds all school documents
* In a virtual machine, with a snapshot taken first
- On a company server
- By running every command you find online with sudo
= A VM keeps mistakes away from real data and snapshots let you return to a working state.

Q: You are making a live USB with a tool that writes an ISO. What is the danger?
- The USB will become hot
* The tool erases everything on the USB, so choosing the wrong drive loses data
- The internet will stop working
- Ubuntu will refuse to start
= Writing an ISO wipes the target drive. Check the drive letter or name carefully and never choose an internal disk.

Q: In the prompt amina@practice:~$ what does ~ stand for?
- The root folder
- The /tmp folder
* The user's home folder
- A hidden file
= The tilde is a shortcut for the current user's home directory.

Q: Which absolute path is correct for the file notes.txt in amina's home folder?
- ~/notes.txt/amina
* /home/amina/notes.txt
- home/amina/notes.txt
- /amina/home/notes.txt
= An absolute path starts with / and follows the tree from the root: /home, then amina, then the file.

Q: Which command gives you the full manual for ls?
- ls --manual
- manual ls
* man ls
- help ls -all
= man opens the manual page, and q quits it.

Q: Which command copies a whole folder called photos to photos-backup?
- cp photos photos-backup
* cp -r photos photos-backup
- mv -r photos photos-backup
- copy photos photos-backup
= Folders need -r (recursive) with cp.

Q: What is the safest habit before running rm *.log ?
- Run it with sudo to be sure
- Close other terminals
* Run ls *.log first to see which files match
- Add -f to skip warnings
= ls with the same pattern previews exactly what rm would delete.

Q: You want to add a line to the end of notes.txt without losing what is there. Which do you use?
- echo "text" > notes.txt
* echo "text" >> notes.txt
- echo "text" | notes.txt
- echo "text" < notes.txt
= >> appends, while > replaces the file contents.

Q: Which command follows a log file live as new lines arrive?
- head -f file
- cat -f file
* tail -f file
- less -n file
= tail -f keeps the file open and prints new lines. Press Ctrl+C to stop.

Q: What does the permission string drwxr-xr-x begin with d for?
- It is deleted
* It is a directory (folder)
- It is a device
- It is a document
= The first character shows the type; d means directory.

Q: What number represents the permissions rwxr-xr-x?
- 644
- 700
- 775
* 755
= Owner 7 (4+2+1), group 5 (4+1), others 5 (4+1) gives 755.

Q: Which command makes a file readable and writable only by its owner?
* chmod 600 file
- chmod 644 file
- chown 600 file
- chmod +x file
= 600 is rw------- . 644 would let the group and others read it.

Q: Which of these needs sudo? (choose all that apply)
* Installing a package with apt
* Editing /etc/hostname
- Listing your own home folder with ls
- Using cd to enter your own folder
= Changing system files and installing software need administrator rights. Working in your own folder does not.

Q: What is the best response to a "Permission denied" error?
- Run chmod -R 777 on the whole system
- Switch to root permanently
* Work out why (owner, group, permissions) and use sudo or fix the specific permission
- Delete the file
= Understand the cause and make the smallest change that fixes it.

Q: A program is frozen. kill 2345 does nothing after a minute. What next?
* Try kill -9 2345 as the last resort
- Reinstall Linux
- Run rm -r /proc
- Use ls -l 2345
= -9 forces the process to stop, but it cannot save its work, so it comes after the polite signal.

Q: What does sudo apt update do?
- Installs the newest versions of all programs
* Refreshes the list of packages and versions that are available
- Removes old software
- Upgrades the kernel only
= It downloads fresh package information. apt upgrade is the command that installs newer versions.

Q: Which command finds all folders whose names start with "week" in your home folder?
- grep week ~
* find ~ -type d -name "week*"
- ls week*
- cd week*
= find with -type d limits the search to folders and -name matches the pattern.

Q: In the pipeline sort names.txt | uniq -c | sort -nr, what is the final result?
- Names in random order
* Each name with its count, with the most frequent name first
- Only the duplicates, deleted
- The number of lines
= sort groups equal lines, uniq -c counts them, and sort -nr sorts those counts from highest to lowest.

Q: Which shell script line correctly makes a variable?
- count = 5
- $count := 5
* count=5
- set count to 5
= Shell variables are assigned with no spaces around the equals sign.

Q: What is the purpose of the first line #!/bin/bash in a script?
- It is a comment that does nothing
* It tells the system which program should run the script
- It makes the script hidden
- It gives the script root rights
= The shebang names the interpreter. Without it the script may be run by the wrong shell.

Q: Which cron line runs a script every Monday at 08:00?
- 8 0 * * 1 /home/amina/report.sh
* 0 8 * * 1 /home/amina/report.sh
- 0 8 1 * * /home/amina/report.sh
- 1 8 * * 0 /home/amina/report.sh
= The order is minute, hour, day of month, month, day of week. Minute 0, hour 8, weekday 1 (Monday).

Q: Which command shows the IP addresses of your network cards on a modern Linux system?
* ip addr
- ping -a
- ssh addr
- grep ip
= ip addr lists each interface and its addresses (ip -br addr gives a shorter table).

Q: You can ping an IP address but not a name. What does this suggest?
- The cable is broken
* A DNS (name lookup) problem
- The CPU is overloaded
- SSH is disabled
= The path to the internet works, but names are not being turned into addresses.

Q: Why are SSH key pairs preferred to passwords for servers?
- They never need to be kept secret
- They make the server faster
* They are much harder to guess or steal, and the private key never crosses the network
- They allow root to log in without limits
= Keys avoid password guessing attacks. The private key must still be kept secret.

Q: Which ufw approach is the safest?
- Allow all incoming traffic
* Deny all incoming by default and allow only the ports needed
- Disable the firewall when it is slow
- Open every port above 1000
= Least access: close everything and open only what a service needs.

Q: Which command shows logs from the ssh service?
- cat /ssh
* journalctl -u ssh
- ls /var/ssh
- top -u ssh
= journalctl -u lets you read the messages of a single service.

Q: When is it appropriate to run a command you copied from a website with sudo?
- Always, because the site looks professional
- When it is very short
* Only when you understand what every part of it does
- When it has many likes
= sudo gives the command full power, so you must understand what it will do first.

Q: Which backup practice is best for a web server?
- Keep one copy on the same disk
- Back up once a year
* Keep regular copies on a different machine or drive and test restoring them
- Never back up, since Linux is stable
= Copies on the same disk are lost with the disk, and an untested backup may not work when needed.
QUIZ,
];
