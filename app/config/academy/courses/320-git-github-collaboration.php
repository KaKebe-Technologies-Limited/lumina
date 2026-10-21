<?php
return [
    'slug'        => 'git-github-collaboration',
    'title'       => 'Git and GitHub: Version Control and Teamwork',
    'category'    => 'web',
    'level'       => 'intermediate',
    'icon'        => 'code',
    'sort'        => 320,
    'summary'     => 'Keep a safe history of your work with Git, then share and build projects with other people on GitHub using branches, pull requests, issues and GitHub Pages.',
    'description' => '<p>Anyone who has saved files called <em>report_final</em>, <em>report_final2</em> and <em>report_REAL_final</em> already knows the problem that version control solves. Git is a free tool that remembers every version of your project, lets you go back in time, and lets several people work on the same files without overwriting one another. GitHub is a website where Git projects are stored, shared and discussed.</p><p>In this intermediate course you will install Git on Windows, record your own history with commits, work on ideas safely in branches, merge them back, and fix conflicts calmly. You will then connect to GitHub, push and pull, send pull requests, track work with issues, and publish a free web page with GitHub Pages. We finish with the habits and good manners of a respected team member.</p><p>You do not need to be a programmer, but you should be comfortable with files, folders and a text editor, and you should have met basic web pages. The examples use small projects such as a school club website and a community notice board.</p>',
    'outcomes'    => [
        'Explain what version control is and why Git and GitHub are used together',
        'Install and configure Git for Windows and run basic commands in a terminal',
        'Create a repository and record changes with init, status, add and commit',
        'Read history with log and diff and undo mistakes safely with restore and revert',
        'Write a .gitignore file and a clear README for a project',
        'Create, switch and merge branches, and resolve a merge conflict',
        'Push, pull and clone projects between your computer and GitHub',
        'Collaborate with pull requests, issues and code review, and publish a site with GitHub Pages',
    ],
    'requirements' => ['Completed Web Basics: Build Your First Web Page (HTML and CSS), or equivalent comfort with files, folders and a text editor', 'A Windows 10 or 11 computer (Mac and Linux work too) with an internet connection', 'A free email address for your GitHub account'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Git documentation and the Pro Git book', 'url' => 'https://git-scm.com/doc'],
        ['title' => 'GitHub Docs', 'url' => 'https://docs.github.com/'],
        ['title' => 'GitHub Desktop', 'url' => 'https://desktop.github.com/'],
        ['title' => 'GitHub Pages', 'url' => 'https://pages.github.com/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: Getting started with Git',
            'summary' => 'Understand version control, install Git, and record your first commits.',
            'lessons' => [
                [
                    'title'   => 'What version control is and why it matters',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what version control is, describe the difference between Git and GitHub, and say why teams rely on them. This matters because almost every software, web and data team in the world uses Git, and employers expect you to know it.</p>

<h3>The problem with copies of files</h3>
<p>Imagine a school club website. Grace edits the home page on Monday. Peter edits the same page on Tuesday on his own laptop. When they meet, whose copy is right? Without a tool, someone must compare both files by eye and hope nothing is lost. Folders full of names such as <code>index_final_v3</code> do not solve this; they only hide it.</p>
<p><strong>Version control</strong> is a system that records the changes made to files over time. Each saved step is a <strong>version</strong>. You can see who changed what and when, compare two versions, and return to an older one.</p>

<h3>Git and GitHub are different things</h3>
<table>
<thead><tr><th></th><th>Git</th><th>GitHub</th></tr></thead>
<tbody>
<tr><td>What it is</td><td>A free program on your computer</td><td>A website and online service</td></tr>
<tr><td>Main job</td><td>Records the history of your project</td><td>Stores copies online and helps people collaborate</td></tr>
<tr><td>Needs internet?</td><td>No, it works offline</td><td>Yes</td></tr>
<tr><td>Made by</td><td>Linus Torvalds and the open-source community</td><td>A company owned by Microsoft</td></tr>
</tbody>
</table>
<p>Other websites, such as GitLab and Bitbucket, also host Git projects. What you learn here works on all of them.</p>

<h3>Key words you will meet</h3>
<ul>
<li><strong>Repository (repo):</strong> a project folder that Git is tracking, including its hidden history.</li>
<li><strong>Commit:</strong> a saved snapshot of your project with a short message describing it.</li>
<li><strong>Branch:</strong> a separate line of work, so you can try an idea without disturbing the main version.</li>
<li><strong>Remote:</strong> a copy of the repository stored somewhere else, usually on GitHub.</li>
<li><strong>Clone:</strong> to download a full copy of a remote repository.</li>
</ul>

<h3>Why teams use it</h3>
<ol>
<li><strong>Safety.</strong> If you break something, you can go back to a version that worked.</li>
<li><strong>Teamwork.</strong> Many people can work at the same time and combine their changes.</li>
<li><strong>Accountability.</strong> Every change shows who made it and why.</li>
<li><strong>Experiments.</strong> Branches let you try risky ideas without fear.</li>
</ol>
<p>Git is not only for programmers. Writers, designers and researchers use it for any text-based files, such as web pages, reports written in plain text, and data files. It works best with text; large pictures and videos are not its strength.</p>

<h3>Try it yourself</h3>
<ol>
<li>Think of a project where you have ever made several copies of one file (a CV, an essay, a design).</li>
<li>Write down three problems that those copies caused.</li>
<li>Next to each problem write how version control would help, using the key words above.</li>
<li>Visit the GitHub home page and look at any public project to see a list of files and the latest commit message.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Version control records changes over time so you can compare and go back.</li>
<li>Git runs on your computer; GitHub is an online home for Git projects.</li>
<li>A repository, commit, branch and remote are the four ideas you will use daily.</li>
<li>Git is best with text files and is useful far beyond programming.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Installing Git and first-time setup',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to install Git for Windows, open a terminal, and tell Git who you are. This matters because every commit carries your name and email, and Git will not behave properly until this is set.</p>

<h3>Install Git for Windows</h3>
<ol>
<li>Open your browser and go to the official Git website, <strong>git-scm.com</strong>, and choose the download for Windows. Download only from the official site.</li>
<li>Run the installer. If Windows asks for permission, choose <strong>Yes</strong>.</li>
<li>Most screens can be left on their default choices. Pick a text editor you know (such as Notepad or Visual Studio Code) when the installer asks which editor Git should use.</li>
<li>When asked about the name of the initial branch, choosing <strong>Override the default branch name</strong> and typing <code>main</code> matches what GitHub uses today.</li>
<li>Finish the installation.</li>
</ol>
<p>The installer adds <strong>Git Bash</strong>, a terminal that understands Git and common Linux-style commands. You can also use Command Prompt or PowerShell. On a Mac, Git can be installed with the developer tools; on Linux, with the package manager, for example <code>sudo apt install git</code>.</p>

<h3>Open a terminal and check</h3>
<p>Press the Windows key, type <strong>Git Bash</strong> and press Enter. A text window opens. Type this and press Enter:</p>
<pre><code>git --version</code></pre>
<p>You should see a line such as <code>git version 2.45.0</code>. The exact number does not matter. If you see "command not found", close the window and reopen it, or run the installer again.</p>

<h3>Tell Git who you are</h3>
<p>Run these two commands once, using your own name and the email you will use on GitHub:</p>
<pre><code>git config --global user.name "Akello Grace"
git config --global user.email "grace@example.com"
git config --global init.defaultBranch main</code></pre>
<p>The word <code>--global</code> means "for every project on this computer". Check what you saved with:</p>
<pre><code>git config --list</code></pre>
<p>GitHub lets you keep your real email private and gives you a special no-reply address in your email settings. If you want privacy, use that address here.</p>

<h3>A few terminal skills you need</h3>
<table>
<thead><tr><th>Command</th><th>What it does</th></tr></thead>
<tbody>
<tr><td><code>pwd</code></td><td>Shows the folder you are in</td></tr>
<tr><td><code>ls</code></td><td>Lists the files in the folder</td></tr>
<tr><td><code>cd foldername</code></td><td>Moves into a folder</td></tr>
<tr><td><code>cd ..</code></td><td>Moves up one folder</td></tr>
<tr><td><code>mkdir name</code></td><td>Makes a new folder</td></tr>
</tbody>
</table>
<p>Press the <strong>Up arrow</strong> to bring back your last command and <strong>Tab</strong> to finish a folder name. Folder names with spaces need quotes, so for easier work use names such as <code>club-site</code>.</p>

<h3>Try it yourself</h3>
<ol>
<li>Install Git for Windows from the official website.</li>
<li>Open Git Bash and run <code>git --version</code>.</li>
<li>Set your name, email and default branch with the three config commands.</li>
<li>Run <code>git config --list</code> and find your name in the output.</li>
<li>Use <code>mkdir</code> and <code>cd</code> to make a folder called <code>club-site</code> and enter it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Download Git only from the official site; Git Bash is installed with it.</li>
<li><code>git --version</code> confirms the installation.</li>
<li>Set user.name and user.email once with <code>git config --global</code>.</li>
<li>Learn <code>pwd</code>, <code>ls</code>, <code>cd</code> and <code>mkdir</code> to move around.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Your first repository: init, status, add and commit',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to turn a folder into a repository and save your first commits. This matters because these four commands are the heart of daily Git work; you will type them hundreds of times.</p>

<h3>The three places a change lives</h3>
<p>Git moves your work through three areas. Understanding them removes most confusion.</p>
<ol>
<li><strong>Working folder.</strong> The files you see and edit.</li>
<li><strong>Staging area.</strong> A waiting room where you choose exactly which changes go into the next snapshot.</li>
<li><strong>Repository.</strong> The permanent history of commits.</li>
</ol>
<p>Think of taking a class photograph. You arrange the students (staging) and then press the camera button (commit).</p>

<h3>Create a repository</h3>
<p>Inside your <code>club-site</code> folder, run:</p>
<pre><code>git init</code></pre>
<p>Git creates a hidden folder called <code>.git</code>. That folder is the repository; never delete it by accident, because it holds your whole history. Now create a file, for example <code>index.html</code>, in any text editor, with a title and one line of text.</p>

<h3>Check, stage and commit</h3>
<ol>
<li>Run <code>git status</code>. Git lists <code>index.html</code> as <strong>untracked</strong>, meaning it is not yet in the history.</li>
<li>Run <code>git add index.html</code> to stage it. Use <code>git add .</code> to stage everything in the folder.</li>
<li>Run <code>git status</code> again. The file is now under "Changes to be committed".</li>
<li>Run <code>git commit -m "Add home page"</code>. The text in quotes is the <strong>commit message</strong>.</li>
</ol>
<p>Git replies with a short summary and a code such as <code>a3f9c21</code>. That code is the commit's identity, called a hash.</p>

<h3>The everyday loop</h3>
<p>Edit the file, then repeat: <code>git status</code>, <code>git add</code>, <code>git commit -m "..."</code>. A good commit holds one idea, for instance "Add contact section". Write the message as a short instruction in the present tense, such as "Fix broken menu link". Avoid messages like "stuff" or "changes", because in a month nobody will know what they mean.</p>
<table>
<thead><tr><th>Command</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td><code>git init</code></td><td>Start tracking this folder</td></tr>
<tr><td><code>git status</code></td><td>Show what has changed and what is staged</td></tr>
<tr><td><code>git add file</code></td><td>Move a change into the staging area</td></tr>
<tr><td><code>git commit -m "msg"</code></td><td>Save the staged changes with a message</td></tr>
</tbody>
</table>

<h3>Common mistakes</h3>
<ul>
<li>Running <code>git init</code> in the wrong folder, such as your whole Documents folder. Always check with <code>pwd</code> first.</li>
<li>Forgetting <code>git add</code>, then wondering why a commit saved nothing.</li>
<li>Committing without a message and getting stuck in an editor. In Vim, press <code>Esc</code>, type <code>:wq</code> and press Enter to save and leave.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>In <code>club-site</code>, run <code>git init</code>.</li>
<li>Create <code>index.html</code> with a heading and run <code>git status</code>.</li>
<li>Stage and commit it with the message "Add home page".</li>
<li>Add a paragraph to the file, then stage and commit with "Add welcome paragraph".</li>
<li>Run <code>git status</code> and confirm it says the working tree is clean.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Changes travel from the working folder to the staging area to the repository.</li>
<li><code>git init</code> creates the hidden <code>.git</code> history folder.</li>
<li>Stage with <code>git add</code>, save with <code>git commit -m</code>.</li>
<li>Small commits with clear messages make history useful.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the main job of Git?
* Record the history of your project so you can compare and go back
- Host websites for the public
- Make files smaller to send by email
- Translate code into other languages
= Git is a version control tool; it records snapshots of your work over time.

Q: Which statement about Git and GitHub is correct?
- They are two names for the same thing
* Git runs on your computer, while GitHub is an online service for sharing Git projects
- GitHub must be installed before Git
- Git only works when you are online
= Git works offline on your machine; GitHub is a website where repositories can be stored and shared.

Q: Which two commands set your identity for all projects on your computer? (choose all that apply)
* git config --global user.name "Your Name"
* git config --global user.email "you@example.com"
- git init --name "Your Name"
- git commit --identity
= Both user.name and user.email are stored with every commit you make.

Q: You ran git init in a folder. What was created?
- A file called README
- A copy of the folder on GitHub
* A hidden .git folder that holds the repository history
- A backup zip file
= The .git folder is the repository itself; deleting it deletes the history.

Q: What is the purpose of the staging area?
- It uploads files to GitHub
* It lets you choose exactly which changes go into the next commit
- It deletes old files
- It shows the history of the project
= You stage with git add, then commit only the staged changes.

Q: Which sequence correctly saves a changed file index.html?
- git commit, then git add index.html
* git add index.html, then git commit -m "Update home page"
- git save index.html
- git init index.html
= You must stage first, then commit with a message.

Q: Which commit message is the most useful?
- stuff
- changes
- fixed it
* Fix broken menu link on contact page
= A short, specific message tells your team what changed and why.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: History, undoing and project files',
            'summary' => 'Read your history, undo mistakes safely, and prepare a project with .gitignore and a README.',
            'lessons' => [
                [
                    'title'   => 'Reading history: log, diff and show',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to look back through a project's history and see exactly what changed in each step. This matters because when something breaks, the fastest way to fix it is to find out when and where it changed.</p>

<h3>See the list of commits</h3>
<p>Run:</p>
<pre><code>git log</code></pre>
<p>Git prints each commit with its hash, author, date and message, newest first. Press <strong>Space</strong> to scroll down and <strong>q</strong> to leave the viewer. The full log is long, so these short forms are popular:</p>
<table>
<thead><tr><th>Command</th><th>What you get</th></tr></thead>
<tbody>
<tr><td><code>git log --oneline</code></td><td>One short line per commit</td></tr>
<tr><td><code>git log --oneline --graph --all</code></td><td>A drawing of branches and merges</td></tr>
<tr><td><code>git log -3</code></td><td>Only the last three commits</td></tr>
<tr><td><code>git log --author="Grace"</code></td><td>Commits by one person</td></tr>
<tr><td><code>git log -- index.html</code></td><td>Commits that touched one file</td></tr>
</tbody>
</table>

<h3>See what changed with diff</h3>
<p>A <strong>diff</strong> shows the lines that were added or removed. Lines starting with <code>+</code> were added and lines starting with <code>-</code> were removed.</p>
<ol>
<li><code>git diff</code> compares your working folder with the staging area, so it shows changes you have not staged yet.</li>
<li><code>git diff --staged</code> shows what is staged and will go into the next commit.</li>
<li><code>git diff a3f9c21 b81d0e4</code> compares two commits (use hashes from the log).</li>
</ol>
<p>Run <code>git diff --staged</code> before every commit. It is the best final check: you read your own work once more and often catch a forgotten test line or a typing mistake.</p>

<h3>Look inside one commit</h3>
<p>Use <code>git show a3f9c21</code> to see the message and the exact changes of one commit. You only need the first six or seven characters of the hash, as long as they are unique. <code>git show HEAD</code> shows the latest commit. <strong>HEAD</strong> is Git's name for "where I am now", normally the latest commit on your current branch.</p>

<h3>Who changed this line?</h3>
<p>Run <code>git blame index.html</code> to see, for every line, the commit and person who last changed it. The word sounds harsh, but the purpose is to find the commit message that explains <em>why</em> the line exists, not to point fingers.</p>

<h3>Try it yourself</h3>
<ol>
<li>In your <code>club-site</code> repository, run <code>git log --oneline</code> and count your commits.</li>
<li>Change a word in <code>index.html</code> but do not stage it. Run <code>git diff</code> and find the <code>-</code> and <code>+</code> lines.</li>
<li>Stage the file and run <code>git diff --staged</code>.</li>
<li>Commit it, then run <code>git show HEAD</code> to see that same change recorded.</li>
<li>Run <code>git log --oneline --graph --all</code> and press <strong>q</strong> to leave.</li>
</ol>

<h3>Key points</h3>
<ul>
<li><code>git log --oneline</code> gives a quick map of history.</li>
<li><code>git diff</code> shows unstaged changes; <code>git diff --staged</code> shows what you are about to commit.</li>
<li><code>git show</code> displays one commit in detail, and HEAD means the current position.</li>
<li>Reviewing your own diff before committing prevents many mistakes.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Undoing mistakes safely',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to discard unwanted edits, fix your last commit, and reverse an older commit without destroying history. This matters because everyone makes mistakes, and Git is only a safety net if you know which command to use.</p>

<h3>Choose the right tool</h3>
<table>
<thead><tr><th>Situation</th><th>Command</th></tr></thead>
<tbody>
<tr><td>I edited a file and want the last committed version back</td><td><code>git restore file</code></td></tr>
<tr><td>I staged a file by mistake</td><td><code>git restore --staged file</code></td></tr>
<tr><td>My last commit has a typo in the message or a forgotten file</td><td><code>git commit --amend</code></td></tr>
<tr><td>An older commit was wrong and is already shared</td><td><code>git revert hash</code></td></tr>
</tbody>
</table>

<h3>Throw away edits you have not committed</h3>
<pre><code>git restore index.html</code></pre>
<p>This puts the file back exactly as it was in the last commit. <strong>Warning:</strong> the edits you discard cannot be recovered, because Git never saved them. Read <code>git status</code> and <code>git diff</code> first to be sure.</p>
<p>To unstage a file but keep your edits, use <code>git restore --staged index.html</code>. The change stays in your working folder and simply leaves the waiting room.</p>

<h3>Fix the last commit</h3>
<p>You committed and then noticed a spelling mistake in the message, or forgot to add a file.</p>
<ol>
<li>For the message only, run <code>git commit --amend -m "Correct message"</code>.</li>
<li>For a forgotten file, run <code>git add forgotten.html</code> and then <code>git commit --amend --no-edit</code>.</li>
</ol>
<p>Amending replaces the last commit with a new one. Only do it for commits you have <strong>not yet pushed</strong> to a shared place, because rewriting history that others already downloaded causes trouble for them.</p>

<h3>Reverse an older commit</h3>
<p><code>git revert a3f9c21</code> creates a <em>new</em> commit that does the opposite of the chosen one. The bad commit stays in the history, but its effect is cancelled. This is the safe method on shared projects because nobody's history is rewritten.</p>

<h3>The dangerous command</h3>
<p>You will see advice online to run <code>git reset --hard</code>. It moves your project back to a chosen commit and <strong>throws away</strong> all uncommitted work. It is useful when you understand it, but as a beginner prefer <code>restore</code> and <code>revert</code>. If you ever commit something and then lose it, run <code>git reflog</code>; it lists where HEAD has recently been, and often lets you find the lost commit hash.</p>

<h3>Common mistakes</h3>
<ul>
<li>Using <code>restore</code> without checking, and losing hours of edits.</li>
<li>Amending a commit that teammates have already pulled.</li>
<li>Putting a password in a file, committing it, and then thinking a revert removes it. It still remains in history; change the password instead.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Change a heading in <code>index.html</code>, run <code>git status</code>, then undo it with <code>git restore index.html</code>.</li>
<li>Make a small change, stage it, then unstage it with <code>git restore --staged</code>.</li>
<li>Commit a change with a misspelled message and fix it with <code>git commit --amend -m</code>.</li>
<li>Make another commit, then run <code>git revert HEAD</code> and look at the new commit in <code>git log --oneline</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li><code>restore</code> discards uncommitted edits permanently.</li>
<li><code>--amend</code> fixes the latest commit, only before sharing.</li>
<li><code>revert</code> cancels an old commit by adding a new one.</li>
<li>Avoid <code>reset --hard</code> until you are confident; <code>reflog</code> can help you recover.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Ignoring files and writing a README',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to keep unwanted files out of Git with a <code>.gitignore</code> file and to write a README that helps visitors understand your project. This matters because a clean, well-explained repository looks professional and avoids leaking private information.</p>

<h3>Why ignore files?</h3>
<p>Some files should never be in the history:</p>
<ul>
<li><strong>Secrets</strong> such as passwords, API keys and files named <code>.env</code>.</li>
<li><strong>Generated files</strong> that can be rebuilt, such as <code>node_modules/</code> folders or compiled output.</li>
<li><strong>Personal clutter</strong> such as <code>Thumbs.db</code>, <code>.DS_Store</code> and editor settings.</li>
<li><strong>Very large files</strong> like videos that make the repository slow.</li>
</ul>

<h3>Create a .gitignore file</h3>
<ol>
<li>In the top folder of your project, create a plain text file named exactly <code>.gitignore</code> (it starts with a dot and has no other extension).</li>
<li>Write one pattern per line.</li>
<li>Stage and commit the file itself so everyone on the team shares the same rules.</li>
</ol>
<pre><code># Secrets
.env

# Folders
node_modules/
backup/

# File types
*.log
*.tmp

# Windows and Mac clutter
Thumbs.db
.DS_Store</code></pre>
<p>A line starting with <code>#</code> is a comment. A star <code>*</code> means "anything", so <code>*.log</code> ignores every log file. A slash at the end names a folder. A line beginning with <code>!</code> makes an exception.</p>

<h3>An important limit</h3>
<p>.gitignore only affects files Git is not yet tracking. If you committed <code>secret.txt</code> earlier and add it to .gitignore later, Git keeps tracking it. Stop tracking it, but keep the file on your disk, with:</p>
<pre><code>git rm --cached secret.txt</code></pre>
<p>Then commit. If a real secret was ever committed, treat it as public: change the password or key.</p>

<h3>Write a README</h3>
<p>The <strong>README</strong> is the front page of your repository. GitHub shows it automatically below the file list. Name it <code>README.md</code>; the <code>.md</code> means <strong>Markdown</strong>, a simple way of formatting plain text.</p>
<pre><code># Youth Club Website

A simple website for the Kitgum Youth Club.

## How to use
Open index.html in your browser.

## Contributing
Ideas are welcome. Please open an issue first.

## Licence
MIT</code></pre>
<p>In Markdown, <code>#</code> makes a heading, <code>##</code> a smaller heading, <code>-</code> at the start of a line makes a bullet, and <code>**bold**</code> makes bold text. A good README answers four questions: what is this, how do I run or use it, how can I help, and what is the licence.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create a <code>.gitignore</code> containing <code>*.log</code> and <code>backup/</code>.</li>
<li>Create a file <code>test.log</code> and run <code>git status</code>. Notice it does not appear.</li>
<li>Create <code>README.md</code> with a heading, one sentence about the project and a "How to use" section.</li>
<li>Commit both files with the message "Add gitignore and README".</li>
</ol>

<h3>Key points</h3>
<ul>
<li>.gitignore lists patterns of files Git must not track; commit it.</li>
<li>Never commit passwords or keys; if you do, change them.</li>
<li>Use <code>git rm --cached</code> to stop tracking a file that was committed by mistake.</li>
<li>A README.md in Markdown explains the project to newcomers.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which command shows one short line for each commit?
- git show --short
* git log --oneline
- git diff --list
- git status -s
= git log --oneline gives a compact history.

Q: You have edited a file but not staged it. Which command shows the changes?
* git diff
- git diff --staged
- git log
- git revert
= Plain git diff compares the working folder with the staging area; --staged shows what is already staged.

Q: What does HEAD mean in Git?
- The first commit ever made
- The newest file in the folder
* The commit you are currently on, normally the latest on your branch
- The name of the remote
= HEAD is the pointer to where you are now.

Q: You want to throw away uncommitted edits to index.html and restore the last saved version. Which command do you use?
- git revert index.html
* git restore index.html
- git amend index.html
- git log index.html
= git restore replaces the file with the committed version. The discarded edits cannot be recovered.

Q: An older commit that is already on GitHub was wrong. What is the safest way to cancel its effect?
- git reset --hard on everyone's computer
- Delete the .git folder
* git revert with that commit's hash
- git commit --amend
= Revert adds a new commit that undoes the old one without rewriting shared history.

Q: Which of these belong in .gitignore? (choose all that apply)
* A .env file holding passwords
* Log files such as *.log
- index.html
- README.md
= Secrets and generated files are ignored; real project files are tracked.

Q: You committed secret.txt last week and then added it to .gitignore, but Git still tracks it. What should you do?
- Nothing; .gitignore removes tracked files automatically
* Run git rm --cached secret.txt, commit, and change the secret if it was real
- Rename the file to .gitignore
- Run git init again
= .gitignore only affects untracked files. The secret remains in old history, so change it.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Branches, merging and conflicts',
            'summary' => 'Work on ideas in separate branches, merge them back, and resolve conflicts calmly.',
            'lessons' => [
                [
                    'title'   => 'Branches: working on ideas safely',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to create a branch, switch between branches and understand what a branch really is. This matters because branches let you build a new feature or fix a bug without disturbing the working version that others rely on.</p>

<h3>What is a branch?</h3>
<p>A <strong>branch</strong> is a movable label that points to a commit. When you start a repository you are on a branch called <code>main</code>. Making a new branch gives you a second label at the same place. Each new commit you make moves only the branch you are on, so the two lines of work drift apart. Branches are cheap and fast in Git, so teams create them all the time, often one per task.</p>
<p>Think of a main road with a side road. You can explore the side road, and if it leads nowhere the main road is untouched. If it is good, you join it back later by merging.</p>

<h3>The commands</h3>
<table>
<thead><tr><th>Command</th><th>What it does</th></tr></thead>
<tbody>
<tr><td><code>git branch</code></td><td>Lists branches; a star marks the current one</td></tr>
<tr><td><code>git branch contact-page</code></td><td>Creates a branch but stays where you are</td></tr>
<tr><td><code>git switch contact-page</code></td><td>Moves to that branch</td></tr>
<tr><td><code>git switch -c contact-page</code></td><td>Creates and switches in one step</td></tr>
<tr><td><code>git switch main</code></td><td>Returns to main</td></tr>
<tr><td><code>git branch -d contact-page</code></td><td>Deletes a branch that is already merged</td></tr>
</tbody>
</table>
<p>Older tutorials use <code>git checkout</code> for switching. It still works, but <code>switch</code> is clearer and recommended today.</p>

<h3>Watch a branch at work</h3>
<ol>
<li>Run <code>git switch -c contact-page</code>.</li>
<li>Create <code>contact.html</code>, then stage and commit it with "Add contact page".</li>
<li>Run <code>git switch main</code> and look at your folder: <code>contact.html</code> has disappeared.</li>
<li>Run <code>git switch contact-page</code> and it returns.</li>
</ol>
<p>The file did not vanish; Git simply rebuilt your working folder to match the branch you chose. Because of this, <strong>commit or restore your changes before switching</strong>. If Git refuses to switch and mentions local changes, commit them first.</p>

<h3>Naming and good habits</h3>
<ul>
<li>Use short lower-case names with hyphens: <code>fix-menu-link</code>, <code>add-gallery</code>.</li>
<li>One branch for one purpose. Do not mix a new gallery with an unrelated spelling fix.</li>
<li>Keep <code>main</code> in a working state at all times; do risky work elsewhere.</li>
<li>Delete branches after merging to keep the list tidy.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>In <code>club-site</code>, run <code>git switch -c about-page</code>.</li>
<li>Create <code>about.html</code> and commit it.</li>
<li>Run <code>git log --oneline --graph --all</code> and note where each branch label sits.</li>
<li>Switch back to <code>main</code> and check that <code>about.html</code> is gone.</li>
<li>Switch to <code>about-page</code> again to confirm it returns.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A branch is a movable pointer to a commit, not a copy of the whole project.</li>
<li><code>git switch -c name</code> creates and enters a branch.</li>
<li>Switching branches changes the files in your folder to match.</li>
<li>Keep main stable and do each task on its own short-lived branch.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Merging branches together',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to bring the work from one branch into another using <code>git merge</code>. This matters because the whole point of a branch is to finish the work and return it to the main project.</p>

<h3>The basic merge</h3>
<p>You always merge <strong>into the branch you are standing on</strong>. To bring the contact page into main:</p>
<ol>
<li>Switch to the branch that will receive the work: <code>git switch main</code>.</li>
<li>Run <code>git merge contact-page</code>.</li>
<li>Check the result with <code>git log --oneline --graph</code>.</li>
<li>Delete the finished branch: <code>git branch -d contact-page</code>.</li>
</ol>

<h3>Two kinds of merge</h3>
<table>
<thead><tr><th>Type</th><th>When it happens</th><th>What you see</th></tr></thead>
<tbody>
<tr><td>Fast-forward</td><td>main has no new commits since you branched</td><td>Git just moves the main label forward; no extra commit</td></tr>
<tr><td>Merge commit</td><td>Both branches have new commits</td><td>Git combines them and creates a new commit with two parents</td></tr>
</tbody>
</table>
<p>With a merge commit Git opens your editor for a message. The default message such as "Merge branch 'contact-page'" is fine; save and close the editor. If you prefer to see every merge in the history even when a fast-forward is possible, use <code>git merge --no-ff contact-page</code>.</p>

<h3>A short example</h3>
<p>Suppose Grace is on <code>main</code> and Peter's work is on <code>add-gallery</code>. Peter changed <code>gallery.html</code>, while Grace changed <code>index.html</code>. Because they touched different files, Git merges them automatically. Git is clever when changes are in different files or in different parts of the same file. Trouble only starts when two people change the very same lines. That is the next lesson.</p>

<h3>Before you merge: a checklist</h3>
<ol>
<li>Is your working folder clean? Run <code>git status</code>.</li>
<li>Are you on the correct receiving branch?</li>
<li>Does the branch work? Open the site or run the tests first.</li>
<li>Is the branch up to date with main? Many teams merge main <em>into</em> their feature branch first, to find problems early.</li>
</ol>

<h3>If you merge by mistake</h3>
<p>If Git has just finished a merge and you want to undo it, <code>git reset --hard HEAD~1</code> would move back one commit, but it is a dangerous command and should be used only if you have not pushed. If the merge was already shared, use <code>git revert -m 1 HEAD</code>, which creates an opposite commit. Ask a teammate to look with you the first time.</p>

<h3>Try it yourself</h3>
<ol>
<li>Make sure your <code>about-page</code> branch has a commit.</li>
<li>Switch to <code>main</code> and run <code>git merge about-page</code>.</li>
<li>Was it a fast-forward? Read the message Git printed.</li>
<li>Run <code>git log --oneline --graph --all</code> to see the result.</li>
<li>Delete the branch with <code>git branch -d about-page</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Switch to the receiving branch, then run <code>git merge other-branch</code>.</li>
<li>Fast-forward merges just move a label; merge commits join two histories.</li>
<li>Git merges automatically when changes do not overlap.</li>
<li>Check that your folder is clean and the work is tested before merging.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Resolving merge conflicts',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to recognise a merge conflict, read the markers Git inserts, and fix the file. This matters because conflicts are a normal part of teamwork, and people who handle them calmly are valued in every team.</p>

<h3>What is a conflict?</h3>
<p>A <strong>merge conflict</strong> happens when two branches changed the same lines of the same file differently, so Git cannot decide which version to keep. Git does not guess. It stops, marks the file, and asks you to decide. A conflict is not an error or a sign that someone did something wrong.</p>

<h3>Create one on purpose</h3>
<ol>
<li>On <code>main</code>, set the title line in <code>index.html</code> to <code>&lt;h1&gt;Kitgum Youth Club&lt;/h1&gt;</code> and commit.</li>
<li>Run <code>git switch -c new-title</code>, then change that same line to <code>&lt;h1&gt;Youth Club of Kitgum&lt;/h1&gt;</code> and commit.</li>
<li>Run <code>git switch main</code>, change the same line to <code>&lt;h1&gt;Kitgum Youth Club Home&lt;/h1&gt;</code> and commit.</li>
<li>Run <code>git merge new-title</code>.</li>
</ol>
<p>Git replies "CONFLICT (content): Merge conflict in index.html" and "Automatic merge failed".</p>

<h3>Read the markers</h3>
<p>Open the file. Git has written both versions between markers:</p>
<pre><code>&lt;&lt;&lt;&lt;&lt;&lt;&lt; HEAD
&lt;h1&gt;Kitgum Youth Club Home&lt;/h1&gt;
=======
&lt;h1&gt;Youth Club of Kitgum&lt;/h1&gt;
&gt;&gt;&gt;&gt;&gt;&gt;&gt; new-title</code></pre>
<ul>
<li>Between <code>&lt;&lt;&lt;&lt;&lt;&lt;&lt; HEAD</code> and <code>=======</code> is your current branch's version.</li>
<li>Between <code>=======</code> and <code>&gt;&gt;&gt;&gt;&gt;&gt;&gt; new-title</code> is the incoming version.</li>
</ul>

<h3>Fix it in four steps</h3>
<ol>
<li><strong>Decide</strong> what the final text should be: yours, theirs, or a mix. Talk to your teammate if unsure.</li>
<li><strong>Edit</strong> the file so only the final text remains, and <strong>delete all three marker lines</strong>.</li>
<li>Run <code>git add index.html</code> to tell Git it is resolved.</li>
<li>Run <code>git commit</code> to finish the merge (the prepared message is fine).</li>
</ol>
<p>Editors such as Visual Studio Code show the conflict with buttons such as <strong>Accept Current Change</strong>, <strong>Accept Incoming Change</strong> and <strong>Accept Both Changes</strong>. They do the same editing for you. Run <code>git status</code> at any time to see which files still need attention. If you are afraid you made a mess, run <code>git merge --abort</code> to return to how things were before the merge.</p>

<h3>Reduce conflicts in the first place</h3>
<ul>
<li>Pull and merge often, so differences stay small.</li>
<li>Keep branches short; finish and merge within days.</li>
<li>Agree who works on which file.</li>
<li>Do not reformat whole files for no reason, which touches every line.</li>
</ul>

<h3>Common mistakes</h3>
<ul>
<li>Leaving the <code>&lt;&lt;&lt;&lt;&lt;&lt;&lt;</code> markers in the file and committing them.</li>
<li>Choosing one side without reading the other, and deleting a teammate's work.</li>
<li>Forgetting <code>git add</code> after editing.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Create the conflict described above in your <code>club-site</code> repository.</li>
<li>Open <code>index.html</code> and find the three markers.</li>
<li>Write a final title that combines both ideas and remove the markers.</li>
<li>Stage the file and commit to finish the merge.</li>
<li>Run <code>git log --oneline --graph</code> to see the merge commit.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A conflict means two branches edited the same lines; Git asks a human to choose.</li>
<li>Edit the file, remove every marker, then add and commit.</li>
<li><code>git merge --abort</code> safely cancels a merge in progress.</li>
<li>Small, frequent merges and good communication keep conflicts rare.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is a Git branch?
- A full copy of the project in a new folder
* A movable pointer to a commit that lets you work on a separate line of changes
- A backup stored on GitHub
- A type of file
= Branches are lightweight labels, which is why Git can create them instantly.

Q: Which command creates a branch called add-gallery and moves you onto it?
- git branch -d add-gallery
- git merge add-gallery
* git switch -c add-gallery
- git new add-gallery
= The -c flag means create. git branch alone would create it but leave you where you are.

Q: You are on main and run git merge about-page. Where do the changes end up?
* In main, the branch you are standing on
- In about-page
- In a new repository
- On GitHub only
= You always merge into the current branch.

Q: When does Git perform a fast-forward merge?
- When two people edit the same lines
- When the branch is deleted
* When the receiving branch has no new commits since the other branch started
- When you use git log
= Git then just moves the branch label forward.

Q: What causes a merge conflict?
- Using too many branches
- Forgetting to commit
* Two branches changed the same lines of a file in different ways
- Having a README file
= Git cannot choose between competing edits of the same lines, so it asks you.

Q: After editing a conflicted file to the final text, what must you do next? (choose all that apply)
* Remove the <<<<<<<, ======= and >>>>>>> marker lines
* Run git add on the file and then git commit
- Delete the .git folder
- Run git init
= The markers must not remain, and git add tells Git the conflict is resolved.

Q: You start a merge, panic, and want to go back to how things were before. Which command helps?
- git merge --continue
* git merge --abort
- git branch -d
- git restore --staged
= Abort cancels the unfinished merge and restores the earlier state.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: GitHub and remotes',
            'summary' => 'Create a GitHub account, connect a remote, and move work between your computer and GitHub.',
            'lessons' => [
                [
                    'title'   => 'GitHub accounts and pushing your first repository',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to create a GitHub repository, connect your local project to it, and push your commits online. This matters because an online copy is a backup, a portfolio and the starting point for all teamwork.</p>

<h3>Create your account and a repository</h3>
<ol>
<li>Go to <strong>github.com</strong> and choose <strong>Sign up</strong>. Use an email you can access and a strong, unique password, and switch on two-factor authentication in the account security settings.</li>
<li>Choose a professional username, because it becomes part of your web address and your future CV.</li>
<li>Click the <strong>+</strong> icon at the top right and choose <strong>New repository</strong>.</li>
<li>Type a name such as <code>club-site</code>, add a short description, and choose <strong>Public</strong> (anyone can see it) or <strong>Private</strong> (only people you invite).</li>
<li>Because you already have a local repository, do <em>not</em> tick the options to add a README or .gitignore. Click <strong>Create repository</strong>.</li>
</ol>
<p>GitHub then shows a page with commands. The address of your repository looks like <code>https://github.com/yourname/club-site.git</code>.</p>

<h3>Connect and push</h3>
<p>In Git Bash, inside your project folder, run:</p>
<pre><code>git remote add origin https://github.com/yourname/club-site.git
git branch -M main
git push -u origin main</code></pre>
<ul>
<li><code>git remote add origin ...</code> gives the online copy a nickname. <strong>origin</strong> is the traditional name for the main remote.</li>
<li><code>git branch -M main</code> makes sure your branch is called main.</li>
<li><code>git push -u origin main</code> uploads your commits. The <code>-u</code> remembers the link, so next time <code>git push</code> alone is enough.</li>
</ul>
<p>The first time, a window may open asking you to sign in to GitHub. Git for Windows includes <strong>Git Credential Manager</strong>, which handles this safely and remembers you. GitHub no longer accepts your account password for pushing from the command line, so use this browser sign-in, a personal access token, or an SSH key (explained in the lesson on authentication).</p>

<h3>Check that it worked</h3>
<p>Refresh the repository page on GitHub. You should see your files, the README and the latest commit message. Click the <strong>commits</strong> link to see the same history you saw with <code>git log</code>. Run <code>git remote -v</code> any time to see which addresses your project is connected to.</p>

<h3>The daily push habit</h3>
<ol>
<li>Commit your work locally as often as you like.</li>
<li>When you finish a session, run <code>git push</code>.</li>
<li>Commits that are not pushed exist only on your computer, so a stolen or broken laptop would lose them.</li>
</ol>

<h3>Common mistakes</h3>
<ul>
<li>Pushing private information to a <strong>public</strong> repository. Check your .gitignore first.</li>
<li>Typing the web address of the page instead of the <code>.git</code> clone address; both usually work, but copy it from the green <strong>Code</strong> button to be safe.</li>
<li>Expecting a push to include uncommitted files. Only commits are pushed.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Create a free GitHub account and enable two-factor authentication.</li>
<li>Create an empty repository named <code>club-site</code>.</li>
<li>Connect your local project with <code>git remote add origin</code> and push with <code>git push -u origin main</code>.</li>
<li>Refresh GitHub and find your README and commit list.</li>
<li>Make one more commit locally and push it with <code>git push</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A GitHub repository is the online copy; origin is the usual name for it.</li>
<li><code>git push -u origin main</code> links and uploads; later <code>git push</code> is enough.</li>
<li>Only committed work is pushed.</li>
<li>Use a strong password with two-factor authentication, and never push secrets.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Clone, fetch and pull',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to download a project from GitHub and keep your copy up to date with other people's changes. This matters because in a team, other people push work all day, and you must collect it before you continue.</p>

<h3>Clone: get a full copy</h3>
<p>To download a repository for the first time, use <code>git clone</code>:</p>
<pre><code>git clone https://github.com/yourname/club-site.git</code></pre>
<p>Git creates a folder named <code>club-site</code> containing all files and the complete history. The remote called <code>origin</code> is set up for you, so you do not need <code>git remote add</code>. You can clone any public repository, which is a great way to study how other people build things. On GitHub, click the green <strong>Code</strong> button to copy the correct address.</p>

<h3>Fetch and pull</h3>
<p>Later, your friend pushes new commits to GitHub. Your computer does not know until you ask.</p>
<table>
<thead><tr><th>Command</th><th>What it does</th><th>Changes your files?</th></tr></thead>
<tbody>
<tr><td><code>git fetch</code></td><td>Downloads new commits and updates your view of the remote, but leaves your work alone</td><td>No</td></tr>
<tr><td><code>git pull</code></td><td>Does a fetch and then merges the new commits into your current branch</td><td>Yes</td></tr>
</tbody>
</table>
<p>If you are curious what changed before merging, run <code>git fetch</code> and then <code>git log --oneline main..origin/main</code>. The name <code>origin/main</code> is your local note of where main is on GitHub.</p>

<h3>A safe daily routine</h3>
<ol>
<li>Start the day with <code>git pull</code> on the branch you will work on.</li>
<li>Work, and commit in small steps.</li>
<li>Before pushing, run <code>git pull</code> again in case others pushed meanwhile.</li>
<li>Run <code>git push</code>.</li>
</ol>

<h3>When a push is rejected</h3>
<p>If someone pushed before you, Git refuses your push with a message that the updates were rejected because the remote contains work you do not have. This protects the team. The fix is simple:</p>
<ol>
<li>Run <code>git pull</code>. If the changes do not overlap, Git merges them automatically.</li>
<li>If there is a conflict, resolve it as you learned in Module 3, then commit.</li>
<li>Run <code>git push</code> again.</li>
</ol>
<p>Never use <code>git push --force</code> to get past a rejection. It overwrites other people's work on the remote. In an emergency, ask the team first; a safer variant is <code>--force-with-lease</code>.</p>

<h3>Common mistakes</h3>
<ul>
<li>Working for days without pulling, and then facing many conflicts.</li>
<li>Cloning into a folder that is already a Git repository.</li>
<li>Pulling with uncommitted changes that touch the same files; commit or stash first.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Move up one folder with <code>cd ..</code> and clone your own repository into a second folder with a different name: <code>git clone https://github.com/yourname/club-site.git club-copy</code>.</li>
<li>In <code>club-copy</code>, edit a file, commit, and push.</li>
<li>Go back to your first folder and run <code>git fetch</code>, then <code>git pull</code>.</li>
<li>Confirm the new change appears in your files and in <code>git log --oneline</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li><code>git clone</code> downloads a full repository with its history.</li>
<li><code>git fetch</code> only looks; <code>git pull</code> fetches and merges.</li>
<li>Pull before you start and before you push.</li>
<li>A rejected push means pull first; avoid force pushing.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Signing in safely: HTTPS, tokens, SSH and GitHub Desktop',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose a safe way to sign in to GitHub from your computer, and you will know when GitHub Desktop is a good alternative to the command line. This matters because many beginners get stuck with "authentication failed" messages at exactly this step.</p>

<h3>Three ways to prove who you are</h3>
<table>
<thead><tr><th>Method</th><th>How it works</th><th>Good for</th></tr></thead>
<tbody>
<tr><td>HTTPS with Git Credential Manager</td><td>A browser window asks you to sign in once; the manager stores a secure token</td><td>Most beginners on Windows</td></tr>
<tr><td>Personal access token</td><td>A long secret text you create on GitHub and use in place of a password</td><td>Scripts and special cases</td></tr>
<tr><td>SSH key</td><td>A pair of files; the private one stays on your computer, the public one goes to GitHub</td><td>Daily developers who push often</td></tr>
</tbody>
</table>
<p>The important rule: <strong>a token or private key is as secret as a password</strong>. Never put one in a README, a screenshot, a chat message or a repository.</p>

<h3>Setting up an SSH key</h3>
<ol>
<li>In Git Bash run: <code>ssh-keygen -t ed25519 -C "grace@example.com"</code>.</li>
<li>Press Enter to accept the default file location. Type a passphrase when asked (a password for the key) and confirm it.</li>
<li>Display your <em>public</em> key with <code>cat ~/.ssh/id_ed25519.pub</code> and copy the whole line.</li>
<li>On GitHub, open <strong>Settings</strong>, then <strong>SSH and GPG keys</strong>, then <strong>New SSH key</strong>. Give it a title such as "Home laptop", paste the key and save.</li>
<li>Test with <code>ssh -T git@github.com</code>. The first time, type <code>yes</code> to trust the server. GitHub greets you by username.</li>
</ol>
<p>Then use the SSH address of a repository, which looks like <code>git@github.com:yourname/club-site.git</code>. To switch an existing project, run <code>git remote set-url origin git@github.com:yourname/club-site.git</code>. Only share the file ending in <code>.pub</code>; never the one without it.</p>

<h3>GitHub Desktop</h3>
<p><strong>GitHub Desktop</strong> is a free application from GitHub with buttons instead of typed commands. Install it from desktop.github.com and sign in. You can then:</p>
<ul>
<li>Clone a repository with <strong>File</strong>, then <strong>Clone repository</strong>.</li>
<li>See changed files and lines in the left panel and tick which to include.</li>
<li>Type a summary, press <strong>Commit to main</strong>, and press <strong>Push origin</strong>.</li>
<li>Create branches from the <strong>Current branch</strong> menu and open a pull request from the <strong>Branch</strong> menu.</li>
</ul>
<p>It is a good way to start and to review changes visually. However, learn the commands too: they work on every computer and server, they are what job interviews and tutorials use, and when something goes wrong the command line tells you more.</p>

<h3>Try it yourself</h3>
<ol>
<li>Decide which sign-in method you will use and write down why.</li>
<li>If you choose SSH, create a key with <code>ssh-keygen</code>, add the public key to GitHub and run <code>ssh -T git@github.com</code>.</li>
<li>Optionally install GitHub Desktop and open your <code>club-site</code> repository in it.</li>
<li>Make a small change, commit it in whichever tool you prefer, and push.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>GitHub does not accept account passwords for command-line pushes; use the credential manager, a token or SSH.</li>
<li>Tokens and private keys are secrets; share only the public key.</li>
<li>GitHub Desktop offers the same actions with buttons.</li>
<li>Learning the commands helps you everywhere, even if you also use Desktop.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: You created an empty repository on GitHub and have a local project with commits. Which command uploads it for the first time and remembers the link?
- git clone origin main
* git push -u origin main
- git pull origin
- git fetch -u
= The -u option sets origin/main as the default so later pushes need only git push.

Q: What does the name "origin" usually refer to?
- The first commit
- Your username
* The default nickname for the remote repository
- The main branch
= origin is just the conventional name for the remote address.

Q: What is the difference between git fetch and git pull?
* Fetch downloads new commits without changing your files; pull fetches and then merges them
- Fetch uploads; pull downloads
- They are identical
- Pull works offline; fetch does not
= Pull is fetch plus merge.

Q: Your push was rejected because the remote has commits you do not have. What is the right response?
- Use git push --force immediately
- Delete the repository and start again
* Run git pull, resolve any conflicts, then push again
- Wait a week
= Forcing would overwrite your teammates' work. Pull first, then push.

Q: Which of these should you never share or commit? (choose all that apply)
* A personal access token
* Your SSH private key file
- Your SSH public key (the .pub file)
- The repository README
= Public keys are meant to be shared with GitHub; tokens and private keys must stay secret.

Q: Which command downloads a complete repository with its history for the first time?
- git init
- git fetch
* git clone
- git remote add
= Clone creates a new folder and sets up origin automatically.

Q: Which statement about GitHub Desktop is true?
- It replaces Git so you do not need to install anything else ever
* It is a free app with buttons for common Git actions such as clone, commit and push
- It only works for private repositories
- It cannot create branches
= Desktop is a friendly interface to Git, but the command line remains useful to know.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Collaboration on GitHub',
            'summary' => 'Use forks, pull requests, issues and GitHub Pages, and learn the habits of a good teammate.',
            'lessons' => [
                [
                    'title'   => 'Pull requests: proposing and reviewing changes',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to propose a change with a pull request, review someone else's pull request, and merge it. This matters because on most teams nobody pushes straight to main; changes are discussed and checked first.</p>

<h3>What is a pull request?</h3>
<p>A <strong>pull request</strong> (often called a PR) is a request on GitHub that says, "I made changes on this branch; please review them and merge them into that branch." It shows the differences, lets people comment line by line, and keeps a record of the decision. It is not a Git command; it is a GitHub feature built on branches.</p>

<h3>The branch and PR workflow</h3>
<ol>
<li>Update main: <code>git switch main</code> then <code>git pull</code>.</li>
<li>Create a branch: <code>git switch -c add-gallery</code>.</li>
<li>Make your changes and commit them in small steps.</li>
<li>Push the branch: <code>git push -u origin add-gallery</code>.</li>
<li>On GitHub a yellow banner appears offering <strong>Compare &amp; pull request</strong>. Click it.</li>
<li>Write a clear <strong>title</strong> and a <strong>description</strong> explaining what you changed, why, and how to check it.</li>
<li>Click <strong>Create pull request</strong>.</li>
</ol>

<h3>Writing a good description</h3>
<pre><code>Add photo gallery page

- New gallery.html with six photos from the 2025 clean-up day
- Link added to the main menu

How to check: open gallery.html and click each photo.
Closes #4</code></pre>
<p>Writing <code>Closes #4</code> links the pull request to issue number 4, and GitHub closes that issue when the PR is merged.</p>

<h3>Reviewing a pull request</h3>
<ol>
<li>Open the <strong>Files changed</strong> tab to read the differences.</li>
<li>Hover over a line and click the blue <strong>+</strong> to add a comment on it.</li>
<li>Click <strong>Review changes</strong> and choose <strong>Comment</strong>, <strong>Approve</strong> or <strong>Request changes</strong>.</li>
<li>When the author has answered your comments and it looks good, the repository owner clicks <strong>Merge pull request</strong>.</li>
</ol>
<p>GitHub offers a few merge styles, such as a normal merge commit, squash (combine all commits into one) and rebase. Your team will tell you which to use; the normal merge is fine to start with. After merging, click <strong>Delete branch</strong>, then locally run <code>git switch main</code> and <code>git pull</code>.</p>

<h3>Giving and receiving feedback</h3>
<ul>
<li>Comment on the code, not the person: "This link may break on phones" rather than "You wrote this badly".</li>
<li>Explain why, and offer a suggestion.</li>
<li>Say what is good too.</li>
<li>If you are the author, say thank you, answer every comment, and push fixes to the same branch; the pull request updates by itself.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Create a branch named <code>improve-readme</code> and add a "Contact" section to README.md.</li>
<li>Commit and push the branch to GitHub.</li>
<li>Open a pull request with a clear title and description.</li>
<li>Review your own pull request in the <strong>Files changed</strong> tab, then merge it.</li>
<li>Delete the branch and run <code>git pull</code> on main.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A pull request asks for a branch to be reviewed and merged; it is a GitHub feature.</li>
<li>Describe what, why and how to check; link issues with "Closes #number".</li>
<li>Review kindly and specifically; request changes when needed.</li>
<li>After merging, delete the branch and pull the updated main.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Forks and contributing to other people\'s projects',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to fork a project you do not own, make a change, and send it back as a pull request. This matters because it is how contributions to open-source projects work, and your first contribution is a great line for a CV.</p>

<h3>Why forks exist</h3>
<p>You cannot push to a repository that belongs to a stranger; you do not have permission. A <strong>fork</strong> is your own copy of someone else's repository, stored in your GitHub account. You can change your fork freely, then ask the original owner to accept your work through a pull request. Inside a team where everyone has write access, people usually skip forks and use branches in the one shared repository.</p>

<h3>The contribution steps</h3>
<ol>
<li>Open the project on GitHub and click <strong>Fork</strong>. Choose your account.</li>
<li>Clone <em>your fork</em>: <code>git clone https://github.com/yourname/project.git</code>.</li>
<li>Add the original as a second remote, traditionally named <code>upstream</code>: <code>git remote add upstream https://github.com/owner/project.git</code>.</li>
<li>Create a branch: <code>git switch -c fix-typo</code>.</li>
<li>Make the change, commit, and push to your fork: <code>git push -u origin fix-typo</code>.</li>
<li>On GitHub, open a pull request from your branch to the original project's main branch.</li>
</ol>

<h3>Staying up to date</h3>
<p>The original project keeps moving after you fork it. To bring its new work into your copy:</p>
<pre><code>git switch main
git fetch upstream
git merge upstream/main
git push origin main</code></pre>
<p>On GitHub you can also click <strong>Sync fork</strong> on your fork's page. Doing this before starting new work avoids conflicts.</p>

<h3>Before you contribute: read the rules</h3>
<ul>
<li>Open <strong>README.md</strong> to understand the project.</li>
<li>Look for <strong>CONTRIBUTING.md</strong>, which explains how the owners want contributions.</li>
<li>Check the <strong>licence</strong> file to know how the code may be used.</li>
<li>Look for a <strong>Code of Conduct</strong> describing how people must behave.</li>
<li>Browse the issues; many projects mark easy tasks with labels such as "good first issue".</li>
</ul>

<h3>Good first contributions</h3>
<p>You do not have to write complicated code. Fixing a spelling mistake, improving instructions, translating a README into Luganda or Swahili where the project welcomes it, or adding an example are all valuable. Start small, follow the project's style, and keep each pull request about one thing.</p>

<h3>Be patient</h3>
<p>Maintainers are often volunteers. If nobody replies for a week, politely add a comment. If they ask for changes, make them without arguing; if your pull request is declined, thank them and learn from it.</p>

<h3>Try it yourself</h3>
<ol>
<li>Ask a classmate or friend for the address of their practice repository, or find a public practice repository such as a "first contributions" exercise on GitHub.</li>
<li>Fork it and clone your fork.</li>
<li>Create a branch, add your name to a text file or fix a small typo, and commit.</li>
<li>Push the branch to your fork and open a pull request, writing a polite description.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A fork is your own online copy of a project you do not own.</li>
<li>Clone the fork, add <code>upstream</code>, work on a branch, push, and open a pull request.</li>
<li>Read README, CONTRIBUTING, licence and code of conduct first.</li>
<li>Small, polite, focused contributions are the best way to begin.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Issues, labels and project organisation',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to report a problem or idea as a GitHub issue and use labels, assignees and milestones to organise team work. This matters because good teams do not rely on memory or scattered chat messages; they keep a clear, shared list of what needs doing.</p>

<h3>What is an issue?</h3>
<p>An <strong>issue</strong> is a numbered discussion thread attached to a repository. It can describe a bug, a request for a new feature, a question or a task. Each issue has a title, a description, comments and a status of open or closed. Because the numbers are permanent (#1, #2, #3), you can refer to them anywhere.</p>

<h3>Create a good issue</h3>
<ol>
<li>Open your repository and click the <strong>Issues</strong> tab, then <strong>New issue</strong>.</li>
<li>Write a specific title: "Contact form button does not work on phones" is better than "Bug".</li>
<li>In the description, give the details below.</li>
<li>Click <strong>Submit new issue</strong>.</li>
</ol>
<table>
<thead><tr><th>Part</th><th>Example</th></tr></thead>
<tbody>
<tr><td>What happened</td><td>Pressing Send does nothing</td></tr>
<tr><td>What you expected</td><td>A thank-you message appears</td></tr>
<tr><td>Steps to repeat</td><td>1. Open contact.html on a phone. 2. Fill the form. 3. Press Send.</td></tr>
<tr><td>Where</td><td>Android phone, Chrome browser</td></tr>
</tbody>
</table>
<p>Before opening an issue on someone else's project, search the existing issues; yours may already be there. Never post passwords, tokens or other people's private data in an issue, because the thread may be public.</p>

<h3>Organise with labels, assignees and milestones</h3>
<ul>
<li><strong>Labels</strong> are coloured tags such as <code>bug</code>, <code>enhancement</code>, <code>documentation</code> and <code>good first issue</code>. Choose them from the right-hand panel.</li>
<li><strong>Assignees</strong> show who is responsible. One owner per task avoids "I thought you were doing it".</li>
<li><strong>Milestones</strong> group issues into a goal, such as "Launch version 1".</li>
<li><strong>Projects</strong> give a simple board with columns like To do, In progress and Done, which is useful for school and community teams.</li>
</ul>

<h3>Connect issues to your work</h3>
<p>Put the issue number in a commit message or pull request, such as <code>Fix send button, closes #7</code>. Words like <code>closes</code>, <code>fixes</code> and <code>resolves</code> followed by the number close the issue automatically when the work reaches the default branch. You can also mention a teammate in a comment by typing <code>@username</code>; they receive a notification.</p>

<h3>Common mistakes</h3>
<ul>
<li>Vague titles such as "It is not working".</li>
<li>Putting five different problems into one issue; make one issue per problem.</li>
<li>Starting work without telling anyone, and duplicating a teammate's effort. Comment "I will take this" or assign yourself first.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>In your <code>club-site</code> repository, create an issue titled "Add a photo gallery page", with a short description of what it should contain.</li>
<li>Add the label <code>enhancement</code> and assign it to yourself.</li>
<li>Create a branch, make a small change, and commit with the message "Add gallery page, closes #1" (use the real issue number).</li>
<li>Push and merge the work, then check that the issue closed itself.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>An issue is a numbered thread for a bug, idea or task.</li>
<li>Clear titles and steps to repeat help others act quickly.</li>
<li>Labels, assignees and milestones keep work organised.</li>
<li>"Closes #number" links your commits and pull requests to issues.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Publishing with GitHub Pages and good team habits',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to publish a simple web page for free with GitHub Pages, and you will know the habits that make you a reliable teammate. This matters because you can show a real, live website made from your repository, and teamwork is judged by how you behave as much as by what you build.</p>

<h3>What is GitHub Pages?</h3>
<p><strong>GitHub Pages</strong> is a free service that turns the HTML, CSS and image files in a repository into a public website. It serves static pages only: no PHP and no database. That is perfect for a club site, a CV or a portfolio.</p>

<h3>Publish a page in six steps</h3>
<ol>
<li>Make sure your repository contains a file called <code>index.html</code> in the top folder, and that it is pushed to GitHub.</li>
<li>Open the repository on GitHub and click <strong>Settings</strong>.</li>
<li>In the left menu choose <strong>Pages</strong>.</li>
<li>Under <strong>Build and deployment</strong>, set the source to <strong>Deploy from a branch</strong>.</li>
<li>Choose the branch <code>main</code> and the folder <code>/ (root)</code>, then click <strong>Save</strong>.</li>
<li>Wait a minute or two and refresh. GitHub shows the address, usually <code>https://yourname.github.io/club-site/</code>.</li>
</ol>
<p>From now on, every time you push to <code>main</code>, the website updates by itself after a short wait. If you see a 404 error, check that the file is named exactly <code>index.html</code> in lower case, and that you waited a little. Use relative links such as <code>about.html</code> and <code>images/logo.png</code> so the pages work under the repository address. Pages sites are public even if the code is only for you, so do not publish personal data.</p>

<h3>Habits of a good teammate</h3>
<table>
<thead><tr><th>Habit</th><th>Why</th></tr></thead>
<tbody>
<tr><td>Pull before you start and before you push</td><td>Prevents conflicts and rejected pushes</td></tr>
<tr><td>Commit small and often with clear messages</td><td>Makes history readable and mistakes easy to reverse</td></tr>
<tr><td>Work on branches, not directly on main</td><td>Keeps the shared version working</td></tr>
<tr><td>Review your own diff before committing</td><td>Catches silly mistakes and secrets</td></tr>
<tr><td>Never force-push shared branches</td><td>Protects other people's work</td></tr>
<tr><td>Never commit secrets or personal data</td><td>Once public, it cannot be taken back</td></tr>
<tr><td>Answer comments and issues politely and promptly</td><td>Builds trust and keeps work moving</td></tr>
<tr><td>Respect licences and give credit</td><td>Honest use of other people's work</td></tr>
</tbody>
</table>

<h3>Choosing a licence</h3>
<p>A public repository without a licence means other people are not clearly allowed to reuse your work. On GitHub use <strong>Add file</strong>, then <strong>Create new file</strong>, name it <code>LICENSE</code> and pick a template. The MIT licence is short and permissive; others are stricter. For creative content many people use Creative Commons licences. When you use someone else's code, keep their licence and credit them.</p>

<h3>Try it yourself</h3>
<ol>
<li>Make sure <code>club-site</code> has an <code>index.html</code> with real content, and push it.</li>
<li>Enable GitHub Pages from the <code>main</code> branch.</li>
<li>Open the published address on your phone.</li>
<li>Add a LICENSE file using the GitHub template picker and pull it to your computer.</li>
<li>Write the live address in your README and push the change.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>GitHub Pages hosts static sites for free from a branch of your repository.</li>
<li>Each push to the chosen branch updates the live site.</li>
<li>Small commits, branches, pulling often and kind reviews define a good teammate.</li>
<li>Add a licence, and never publish secrets or private data.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is a pull request?
- A Git command that downloads new commits
* A GitHub request asking for your branch to be reviewed and merged
- A way to delete a branch
- A type of backup
= Pull requests are a GitHub feature built on branches.

Q: In a pull request description you write "Closes #12". What happens when the PR is merged?
- Nothing at all
- The repository is closed
* Issue number 12 is closed automatically
- Your branch is renamed
= Keywords like closes, fixes and resolves link work to issues.

Q: Which is the best way to give feedback in a review?
- "This is terrible."
* "This link may break on phones; could we use a relative path instead?"
- Say nothing and merge
- Rewrite the file without telling the author
= Good review comments are specific, kind and focus on the work.

Q: You want to contribute to a project you do not have write access to. What do you do first?
- Push directly to its main branch
* Fork it, then clone your fork
- Delete your repository
- Send the files by email
= A fork gives you a copy you can change and then propose back by pull request.

Q: What is conventionally the name of the remote that points to the original project when you work from a fork?
- origin
- backup
* upstream
- master
= origin is your fork; upstream is the original repository.

Q: Which of these make a good issue? (choose all that apply)
* A specific title
* Steps to repeat the problem
- A very short title such as "Bug"
- Five unrelated problems in one thread
= Clear, focused issues are easier to understand and fix.

Q: You enabled GitHub Pages for the main branch and later push a new commit to main. What happens?
- The site never changes
* The published site updates automatically after a short wait
- The repository becomes private
- You must delete and re-enable Pages
= Pages rebuilds the site from the chosen branch on every push.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What is version control?
* A system that records changes to files over time so you can compare and restore versions
- A way to make files smaller
- A type of website
- A password manager
= Version control keeps a history of your work.

Q: Which statement is true?
- GitHub is installed on your computer and Git is a website
* Git is a program on your computer and GitHub is an online service that hosts Git repositories
- Both need internet at all times
- Git can only be used with GitHub
= Git works offline; GitHub is one of several hosting services.

Q: What does git init do?
- Downloads a repository
* Starts tracking the current folder by creating a hidden .git folder
- Uploads files to GitHub
- Deletes old commits
= git init turns an ordinary folder into a repository.

Q: What is the correct order to save a change?
- commit, add, status
* edit, add, commit
- push, commit, add
- init, push, add
= You stage with add before committing.

Q: What does the staging area allow you to do?
- Store your passwords
* Choose exactly which changes are included in the next commit
- Publish a website
- Merge two branches
= Staging lets you build a clean, focused commit.

Q: Which command shows files that are changed, staged or untracked?
- git log
- git diff --staged
* git status
- git show
= git status is the command to run most often.

Q: Which command shows the changes you have staged and are about to commit?
- git diff
* git diff --staged
- git log --staged
- git status --diff
= Plain git diff shows only unstaged changes.

Q: Which is a well-written commit message?
- update
- asdf
* Add contact form validation for phone numbers
- final final 2
= Clear, specific messages make history useful.

Q: You edited a file and want to throw away the edits and return to the last commit. Which command do you use, knowing the edits cannot be recovered?
- git revert file
* git restore file
- git commit --amend
- git branch -d file
= restore discards uncommitted edits for good.

Q: You forgot to add one file to the commit you just made, and you have not pushed. What is a safe fix?
- Delete the repository
- git push --force
* git add the file, then git commit --amend --no-edit
- git revert without a hash
= Amending is fine for commits that have not been shared.

Q: An old commit that has been pushed and pulled by others introduced a bug. What is the safest way to cancel it?
- Rewrite history with reset --hard and force push
* git revert with that commit's hash
- Delete the .git folder
- Ask everyone to restart their computers
= Revert adds a new commit and does not disturb shared history.

Q: Which files should usually be listed in .gitignore? (choose all that apply)
* .env files with passwords
* Generated folders such as node_modules/
- index.html
- README.md
= Ignore secrets and rebuildable output; track real source files.

Q: A secret key was committed and pushed to a public repository. What is the most important action?
- Add it to .gitignore and do nothing else
* Change or revoke the key, because it remains in the history
- Rename the file
- Delete the README
= Once public, assume the secret is exposed and replace it.

Q: What is a branch?
- A copy of the project on GitHub
* A movable pointer to a commit, used for a separate line of work
- A type of commit message
- A file that stores passwords
= Branches are cheap pointers, so use one per task.

Q: Which command creates a branch and switches to it in one step?
- git branch -d name
* git switch -c name
- git merge name
- git fork name
= -c means create.

Q: You want to bring the work from feature-gallery into main. What do you do?
- Stay on feature-gallery and run git merge main
* Switch to main and run git merge feature-gallery
- Run git clone feature-gallery
- Run git push feature-gallery
= You merge into the branch you are on.

Q: What is a merge conflict?
- A failed internet connection
* Two branches changed the same lines differently, so Git needs you to choose
- A bug in Git
- An error caused by a missing README
= Conflicts are normal and fixable.

Q: What must be removed from a conflicted file before you commit? (choose all that apply)
* The <<<<<<< line
* The ======= line
* The >>>>>>> line
- Every comment in the file
= The three marker lines must be gone, leaving only the final text.

Q: Which command connects your local repository to a GitHub repository named origin?
- git clone origin
* git remote add origin <address>
- git pull origin
- git branch origin
= remote add stores the address under the nickname origin.

Q: What does git pull do?
- Uploads your commits
* Fetches new commits from the remote and merges them into your current branch
- Deletes the remote
- Creates a new branch
= Pull is fetch plus merge.

Q: Your push is rejected because teammates pushed first. What should you do?
- Force push
* Pull, resolve any conflicts, then push again
- Create a new repository
- Ignore the message
= Never overwrite teammates' work with a forced push.

Q: Which is the safest advice about personal access tokens and SSH private keys?
- Post them in a README so you do not forget them
* Treat them like passwords and never share or commit them
- Email them to the team
- Put them in screenshots for tutorials
= Anyone with the secret can act as you.

Q: What is a pull request used for?
- To download files
* To propose changes from a branch or fork and have them reviewed before merging
- To rename a repository
- To change your password
= It is the standard way to review work on GitHub.

Q: You want to fix a typo in a public project owned by someone else. What is the usual route?
- Push to their main branch
* Fork, clone your fork, make a branch, push, and open a pull request
- Email them a zip of your computer
- Delete the project and start your own
= Contributors without write access work through forks.

Q: Which things does GitHub Pages need in order to publish a simple site? (choose all that apply)
* An index.html file in the published folder
* Pages enabled in the repository Settings with a branch chosen
- A database server
- A PHP installation
= Pages serves static files only, from the branch you choose.
QUIZ,
];
