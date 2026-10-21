<?php
return [
    'slug'        => 'typing-keyboard-skills',
    'title'       => 'Typing and Keyboard Skills',
    'category'    => 'basics',
    'level'       => 'beginner',
    'icon'        => 'laptop',
    'sort'        => 60,
    'summary'     => 'Learn to type with all ten fingers, build speed and accuracy, use keyboard shortcuts, and meet the typing standards employers expect.',
    'description' => '<p>Typing is the skill behind almost every computer task: writing emails, filling forms, doing homework, applying for jobs and entering data. Most beginners type with one or two fingers while staring at the keys. With the right method, anyone can learn to type faster, with fewer mistakes and less tiredness.</p><p>This course teaches touch typing, which means typing without looking at the keys, in short steps. You will learn the layout of the keyboard, the correct finger positions, how to practise every day, how to measure your speed, and how to use shortcuts that save time.</p><p>There is no software to install. Each lesson gives you practice text and a clear plan you can follow on any computer, laptop or phone.</p>',
    'outcomes'    => [
        'Sit and place your hands correctly to type comfortably and avoid strain',
        'Name the main keys and groups of keys on a keyboard',
        'Type the home, top and bottom rows with the correct fingers',
        'Type capital letters, numbers, symbols and punctuation without looking down',
        'Measure your typing speed in words per minute and your accuracy',
        'Follow a four-week practice plan to improve steadily',
        'Use the most useful keyboard shortcuts in Windows and Mac',
        'Write clear messages, enter data accurately and prepare for a typing test at work',
    ],
    'requirements' => ['A computer or laptop with a keyboard (a smartphone helps for one lesson)', 'No experience needed'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Microsoft Support: Keyboard shortcuts in Windows', 'url' => 'https://support.microsoft.com/windows'],
        ['title' => 'Microsoft Support: Word help', 'url' => 'https://support.microsoft.com/word'],
        ['title' => 'Apple Support: Mac keyboard shortcuts', 'url' => 'https://support.apple.com/mac'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: Foundations',
            'summary' => 'Why typing matters, how to sit, and how the keyboard is organised.',
            'lessons' => [
                [
                    'title'   => 'Why typing matters, and how to sit',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>After this lesson you will know why typing well is worth learning and how to set up your body and your desk so that you can type comfortably for a long time. Good posture is the base of every other skill in this course.</p>

<h3>Why learn to type properly?</h3>
<p>Most computer work is writing: CVs, school assignments, emails, reports, messages, forms and records. A person who types with two fingers might manage 10 to 15 words a minute. A trained touch typist can reach 40 or more, which is almost three times faster. That saves hours every week.</p>
<ul>
<li><strong>Time</strong>: you finish tasks sooner and have time for other work.</li>
<li><strong>Accuracy</strong>: when your eyes stay on the screen you notice mistakes at once.</li>
<li><strong>Confidence</strong>: typing tests are common in job applications for office, data entry, bank, school and NGO jobs.</li>
<li><strong>Comfort</strong>: using ten fingers spreads the work and reduces tiredness.</li>
</ul>

<h3>What is touch typing?</h3>
<p><strong>Touch typing</strong> means typing without looking at the keyboard. Each finger has its own keys, and your fingers learn where they are by feel, just as you can find your mouth with a spoon in the dark. It takes a few weeks of short daily practice to build this habit, and the habit lasts for life.</p>

<h3>Set up your workspace</h3>
<ol>
<li><strong>Chair</strong>: sit with your back straight and supported by the chair. Your feet should rest flat on the floor. If your feet do not reach, put a book or a box under them.</li>
<li><strong>Height</strong>: your forearms should be level with the floor, with your elbows bent at about 90 degrees. If the table is too high, raise your chair or use a cushion.</li>
<li><strong>Screen</strong>: the top of the screen should be at or slightly below eye level, about an arm's length away. On a laptop, a stack of books under it (with a separate keyboard) lifts it to the right height.</li>
<li><strong>Keyboard</strong>: place it directly in front of you, close to the table edge. Keep the mouse next to it so your arm does not stretch.</li>
<li><strong>Light</strong>: avoid light behind the screen, and avoid glare on it.</li>
</ol>

<h3>Hands and wrists</h3>
<ul>
<li>Keep your wrists straight and floating slightly above the keys, not bent up or down and not pressed on the table edge.</li>
<li>Keep your fingers curved, as if holding a small ball.</li>
<li>Press keys lightly. You do not need to hit them hard.</li>
<li>Relax your shoulders. If they rise toward your ears, drop them.</li>
</ul>

<h3>Take breaks</h3>
<p>Every 20 to 30 minutes, stop for a minute. Stretch your fingers, roll your shoulders, and look at something far away to rest your eyes.</p>

<h3>Try it yourself</h3>
<ol>
<li>Sit at your computer and check each point of the setup above, from feet to screen.</li>
<li>Place your hands on the table in front of you, fingers curved. Wiggle each finger from 1 to 10 (left little finger to right little finger).</li>
<li>Open a blank document or Notepad and type your full name and your town five times, at a relaxed pace, keeping your wrists straight.</li>
<li>Stretch: open your hands wide, then make a soft fist, ten times.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Touch typing means typing without looking at the keys, using all ten fingers.</li>
<li>Sit straight, feet flat, elbows at about 90 degrees, screen at eye level.</li>
<li>Keep wrists straight and press keys lightly.</li>
<li>Take a short break every 20 to 30 minutes.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The keyboard layout',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn the main parts of the keyboard and what the special keys do. You cannot type quickly if you do not know where things are, and a few special keys will save you from many beginner problems.</p>

<h3>QWERTY</h3>
<p>Most keyboards in Uganda and across the world use the <strong>QWERTY</strong> layout. The name comes from the first six letters on the top row of letters. The letters are not in alphabetical order, so you must learn their places.</p>

<h3>The groups of keys</h3>
<table>
<thead><tr><th>Group</th><th>Where</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Letter keys</td><td>Centre</td><td>A to Z</td></tr>
<tr><td>Number row</td><td>Above the letters</td><td>0 to 9 and symbols such as <code>!</code> <code>@</code> <code>#</code></td></tr>
<tr><td>Function keys</td><td>Top row, F1 to F12</td><td>Special actions that depend on the program. F5 refreshes a web page and F2 renames a file.</td></tr>
<tr><td>Navigation keys</td><td>Arrow keys, Home, End, Page Up, Page Down</td><td>Move the cursor or the page</td></tr>
<tr><td>Numeric keypad</td><td>Right side (full keyboards)</td><td>A calculator-style block for entering numbers quickly</td></tr>
</tbody>
</table>
<p>On many laptops the function keys also control volume, brightness and Wi-Fi. You may have to hold the <strong>Fn</strong> key at the same time.</p>

<h3>The important special keys</h3>
<ul>
<li><strong>Spacebar</strong>: the long key at the bottom. It makes a space between words.</li>
<li><strong>Enter</strong> (Return): starts a new line or paragraph, or confirms a choice.</li>
<li><strong>Backspace</strong>: deletes the character to the left of the cursor.</li>
<li><strong>Delete</strong> (Del): deletes the character to the right of the cursor, or the selected item.</li>
<li><strong>Shift</strong>: hold it while pressing a letter to get a capital letter, or a number key to get the symbol above it. There is one on each side.</li>
<li><strong>Caps Lock</strong>: switches all letters to capitals until you press it again. A small light often shows when it is on.</li>
<li><strong>Tab</strong>: moves the cursor a set distance across, or moves to the next box in a form.</li>
<li><strong>Esc</strong> (Escape): cancels or closes something.</li>
<li><strong>Ctrl</strong> (Control), <strong>Alt</strong> and the <strong>Windows key</strong> (Mac: Command and Option): used together with other keys for shortcuts.</li>
</ul>

<h3>The cursor</h3>
<p>The blinking vertical line in a document is the <strong>cursor</strong> (or insertion point). Whatever you type appears where the cursor is. Click in the text to place it, or use the arrow keys to move it.</p>

<h3>The two "bumps"</h3>
<p>Look closely at the letters <strong>F</strong> and <strong>J</strong>. Each has a small raised line on it. These bumps let your index fingers find the home position without looking. You will use them in the next lesson.</p>

<h3>Phone and tablet keyboards</h3>
<p>On a phone, the keyboard is on the screen. It has the same QWERTY letters, a key to switch to numbers and symbols (often marked <code>?123</code>), and an arrow for Shift. We return to phone typing in Module 4.</p>

<h3>Try it yourself</h3>
<ol>
<li>Open Notepad (press the Windows key, type <code>Notepad</code>, press Enter).</li>
<li>Type your name, then press Backspace until it is gone.</li>
<li>Type three words, press Enter, and type three more words on the next line.</li>
<li>Hold Shift and type your name so that the first letter is a capital. Then press Caps Lock, type your name in capitals, and press Caps Lock again to switch it off.</li>
<li>Find the bumps on the F and J keys with your fingertips.</li>
</ol>

<h3>Common mistakes</h3>
<ul>
<li>Leaving Caps Lock on by accident and typing LIKE THIS. Check the light.</li>
<li>Pressing Delete when you meant Backspace.</li>
</ul>

<h3>Key points</h3>
<ul>
<li>The standard layout is QWERTY: letters, number row, function keys, navigation keys.</li>
<li>Backspace deletes to the left; Delete removes to the right.</li>
<li>Shift makes capitals and top symbols; Caps Lock keeps capitals on.</li>
<li>The bumps on F and J guide your index fingers.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Home row and finger placement',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>You will learn the home row position, the starting place for every touch typist, and which finger presses which key. By the end you will type your first real words without looking down. This is the most important lesson in the course, so take your time with it.</p>

<h3>The home row</h3>
<p>The <strong>home row</strong> is the middle row of letter keys. Your fingers rest here and return here after every key press.</p>
<table>
<thead><tr><th>Hand</th><th>Little finger</th><th>Ring</th><th>Middle</th><th>Index</th></tr></thead>
<tbody>
<tr><td>Left</td><td>A</td><td>S</td><td>D</td><td>F</td></tr>
<tr><td>Right</td><td>;</td><td>L</td><td>K</td><td>J</td></tr>
</tbody>
</table>
<p>Both thumbs rest on the spacebar. The left thumb or the right thumb may press it; choose one and stay consistent.</p>

<h3>Placing your hands</h3>
<ol>
<li>Sit correctly as in lesson 1.</li>
<li>Place your left index finger on <strong>F</strong> and your right index finger on <strong>J</strong>. Feel for the bumps.</li>
<li>Place the other fingers on the neighbouring keys: <code>A S D F</code> on the left and <code>J K L ;</code> on the right.</li>
<li>Rest your thumbs lightly above the spacebar.</li>
</ol>

<h3>The zones</h3>
<p>Each finger looks after its own column of keys. Your index fingers cover two columns each (F, G and J, H). The reach is simple: a finger goes up or down from its home key to reach the rows above and below, then comes back home. Do not move your whole hand; move only the finger.</p>

<h3>Rules for good technique</h3>
<ul>
<li>Return to the home row after every key.</li>
<li>Use the correct finger for each key, even when it feels slower at first.</li>
<li>Keep your eyes on the screen or on the practice paper, not on your hands.</li>
<li>Type at a pace where you make almost no mistakes.</li>
</ul>

<h3>First practice</h3>
<p>Type each line slowly, twice. Say each letter in your head. The space between groups is the spacebar.</p>
<pre>
fff jjj fff jjj fj fj fj fj
ddd kkk ddd kkk dk dk dk dk
sss lll sss lll sl sl sl sl
aaa ;;; aaa ;;; a; a; a; a;
asdf jkl; asdf jkl; asdf jkl;
</pre>
<p>Now real words, using only home row letters:</p>
<pre>
a sad lad; a flask; all fall; ask dad; salad; a fad
</pre>
<p>The letters G and H sit between the index fingers. Your left index finger reaches right to G, and your right index finger reaches left to H, and each returns to its bump. Practise:</p>
<pre>
fg fg fg jh jh jh gaff; hall; glass; flash; shall
</pre>

<h3>Try it yourself</h3>
<ol>
<li>Place your fingers on the home row. Close your eyes for five seconds and feel the position.</li>
<li>Type each of the practice lines above twice, slowly. Do not look at the keys.</li>
<li>If you lose your place, feel for the bumps on F and J and start again.</li>
<li>Finish by typing <code>a sad lad falls; dad asks a lad</code> three times.</li>
<li>Rest for a minute and shake out your hands.</li>
</ol>

<h3>Common mistakes</h3>
<ul>
<li>Using only the index fingers. Force yourself to use all fingers, even if slower.</li>
<li>Pressing hard. Light touches are enough.</li>
<li>Looking down. Cover your hands with a cloth or a sheet of card if you cannot stop.</li>
</ul>

<h3>Key points</h3>
<ul>
<li>Home row: A S D F for the left hand and J K L ; for the right.</li>
<li>Index fingers rest on the bumps on F and J; thumbs rest on the spacebar.</li>
<li>Each finger returns to the home row after every key.</li>
<li>Slow and correct beats fast and wrong.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does "touch typing" mean?
- Typing very hard on the keys
* Typing without looking at the keyboard, using all ten fingers
- Typing on a touch screen only
- Typing only numbers
= Touch typing uses muscle memory so your eyes stay on the screen.

Q: Which setup is best for comfortable typing?
- Elbows above the table, wrists bent upward
- Screen far below eye level
* Feet flat on the floor, back supported, elbows at about 90 degrees
- Keyboard far away so you have to stretch
= A neutral posture reduces tiredness and strain.

Q: What does the QWERTY layout name come from?
* The first six letters in the top row of letters
- A company called Qwerty
- The alphabetical order
- The number of keys
= The letters Q, W, E, R, T and Y begin the top letter row.

Q: Which key deletes the character to the LEFT of the cursor?
- Delete
- Tab
- Esc
* Backspace
= Backspace removes the character before the cursor; Delete removes the one after it.

Q: What is the purpose of the bumps on F and J?
- They show which keys are broken
* They help your index fingers find the home position without looking
- They mark the capital letters
- They are for decoration
= The raised marks let you feel your way to the home row.

Q: Which letters are the home row keys for the left hand?
- Q W E R
- Z X C V
* A S D F
- G H J K
= The left hand rests on A, S, D and F.

Q: You notice all your text appears in capitals. What is the most likely cause?
- The spacebar is stuck
* Caps Lock is on
- You pressed Enter twice
- The cursor is lost
= Press Caps Lock again to switch it off.

Q: Which are good habits for beginners? (choose all that apply)
* Return your fingers to the home row after each key
* Type slowly and correctly first
- Look at your hands for every key
= Accuracy and the home row come first; speed follows.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Learning the rows',
            'summary' => 'The top row, the bottom row, capitals, numbers, symbols and punctuation, and how to practise.',
            'lessons' => [
                [
                    'title'   => 'The top row and the bottom row',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>Now that you know the home row, you will learn the letters above and below it. After this lesson you will be able to type every letter of the alphabet with the correct finger. Every reach starts from, and returns to, the home row.</p>

<h3>The top row (Q W E R T Y U I O P)</h3>
<p>Each finger moves straight up from its home key:</p>
<table>
<thead><tr><th>Finger</th><th>Home key</th><th>Reaches up to</th></tr></thead>
<tbody>
<tr><td>Left little</td><td>A</td><td>Q</td></tr>
<tr><td>Left ring</td><td>S</td><td>W</td></tr>
<tr><td>Left middle</td><td>D</td><td>E</td></tr>
<tr><td>Left index</td><td>F</td><td>R and T</td></tr>
<tr><td>Right index</td><td>J</td><td>U and Y</td></tr>
<tr><td>Right middle</td><td>K</td><td>I</td></tr>
<tr><td>Right ring</td><td>L</td><td>O</td></tr>
<tr><td>Right little</td><td>;</td><td>P</td></tr>
</tbody>
</table>

<h3>The bottom row (Z X C V B N M)</h3>
<table>
<thead><tr><th>Finger</th><th>Reaches down to</th></tr></thead>
<tbody>
<tr><td>Left little</td><td>Z</td></tr>
<tr><td>Left ring</td><td>X</td></tr>
<tr><td>Left middle</td><td>C</td></tr>
<tr><td>Left index</td><td>V and B</td></tr>
<tr><td>Right index</td><td>N and M</td></tr>
<tr><td>Right middle</td><td>, (comma)</td></tr>
<tr><td>Right ring</td><td>. (full stop)</td></tr>
<tr><td>Right little</td><td>/ (slash)</td></tr>
</tbody>
</table>

<h3>How to learn a new row</h3>
<ol>
<li>Place your fingers on the home row.</li>
<li>Reach up (or down) with one finger, press the key, and return it to home.</li>
<li>Say the letter aloud as you press it. This links your eyes, voice and finger.</li>
<li>Practise single letters first, then short words.</li>
</ol>

<h3>Practice: the top row</h3>
<pre>
frf juj fre jui dec kik sws lol aqa ;p;
we are; you try; type it; write; quiet; power; tower; river
</pre>
<pre>
we were out; our true story; pretty yet quite; try to write it
</pre>

<h3>Practice: the bottom row</h3>
<pre>
fvf jnj fbf jmj dcd k,k sxs l.l aza
zero; vex; cab; man; name; vim; bonus; combine; zoom; maze
</pre>
<pre>
a calm man came; he can mix it; a new van; ban the box
</pre>

<h3>All three rows together</h3>
<p>Mix them in sentences. Many English words use all rows, so this is real practice:</p>
<pre>
the quick brown fox jumps over the lazy dog
pack my box with five dozen liquor jugs
</pre>
<p>The first sentence is a famous <em>pangram</em>: it contains every letter of the alphabet. Typing it is a good warm-up.</p>

<h3>Try it yourself</h3>
<ol>
<li>Type each practice line above twice, slowly, with your eyes on the text, not your hands.</li>
<li>Choose the three letters you find hardest (often Q, Z, P or X). Type each pair like <code>aqa aqa</code> ten times.</li>
<li>Type the pangram five times. Count your mistakes each time and try to lower them.</li>
<li>Write three short sentences about your day using words from all rows.</li>
</ol>

<h3>Common mistakes</h3>
<ul>
<li>Moving the whole hand to reach a letter instead of stretching only the finger.</li>
<li>Not returning to the home row, which leaves the hands lost on the next key.</li>
</ul>

<h3>Key points</h3>
<ul>
<li>Each finger reaches straight up or down from its home key.</li>
<li>Return to home after every key.</li>
<li>Practise difficult letters in short pairs and then in words.</li>
<li>The pangram "the quick brown fox..." uses every letter and is a useful warm-up.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Capitals, numbers, symbols and punctuation',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>Real writing needs capital letters, numbers and punctuation. In this lesson you will learn to use the Shift keys, the number row and the common punctuation marks with the right fingers. These skills turn exercises into proper, professional writing.</p>

<h3>Capital letters with Shift</h3>
<p>There are two Shift keys, one under each little finger. The rule is simple: <strong>use the Shift key on the opposite side from the letter</strong>. For a letter typed by the right hand (such as J, K, L, P) use the left Shift. For a letter typed by the left hand (such as A, S, D, T) use the right Shift. Hold Shift down with the little finger, press the letter, and release both.</p>
<pre>
Akello Grace; Okello Peter; Gulu, Uganda; Kampala; Lira
</pre>
<p>Always start a sentence and every name with a capital letter.</p>

<h3>The number row</h3>
<p>Reach up from the home row. The finger that types the letter below also types the number above it:</p>
<table>
<thead><tr><th>Finger</th><th>Numbers</th></tr></thead>
<tbody>
<tr><td>Left little</td><td>1 (and ` ~)</td></tr>
<tr><td>Left ring</td><td>2</td></tr>
<tr><td>Left middle</td><td>3</td></tr>
<tr><td>Left index</td><td>4 and 5</td></tr>
<tr><td>Right index</td><td>6 and 7</td></tr>
<tr><td>Right middle</td><td>8</td></tr>
<tr><td>Right ring</td><td>9</td></tr>
<tr><td>Right little</td><td>0, - and =</td></tr>
</tbody>
</table>
<p>If you have a numeric keypad on the right, you may use it for long lists of numbers: place your right index on 4, middle on 5 and ring on 6. Press <strong>Num Lock</strong> if the numbers do not appear.</p>
<pre>
12 34 56 78 90   2025   0772 123 456   UGX 150,000
</pre>

<h3>Symbols</h3>
<p>The symbols above the numbers need Shift (use the Shift key on the opposite side):</p>
<table>
<thead><tr><th>Press Shift +</th><th>Symbol</th><th>Use</th></tr></thead>
<tbody>
<tr><td>1</td><td><code>!</code></td><td>Exclamation</td></tr>
<tr><td>2</td><td><code>@</code></td><td>Email addresses</td></tr>
<tr><td>3</td><td><code>#</code></td><td>Hashtags, numbers</td></tr>
<tr><td>4</td><td><code>$</code></td><td>Dollars</td></tr>
<tr><td>5</td><td><code>%</code></td><td>Percent</td></tr>
<tr><td>7</td><td><code>&amp;</code></td><td>And</td></tr>
<tr><td>8</td><td><code>*</code></td><td>Star</td></tr>
<tr><td>9 and 0</td><td><code>(</code> <code>)</code></td><td>Brackets</td></tr>
</tbody>
</table>
<p>Keyboard layouts differ a little. On some keyboards the positions of <code>@</code> and <code>"</code> are swapped. Look at your own keys.</p>

<h3>Punctuation</h3>
<ul>
<li><strong>Full stop and comma</strong>: right ring finger (.) and right middle finger (,).</li>
<li><strong>Question mark</strong>: Shift plus <code>/</code>, typed by the right little finger.</li>
<li><strong>Apostrophe and quotation marks</strong>: right little finger, next to the semicolon; add Shift for quotation marks.</li>
<li><strong>Colon</strong>: Shift plus the semicolon key.</li>
<li><strong>Hyphen</strong>: the key beside 0 on the number row.</li>
</ul>
<p>Put one space after a full stop, comma, colon or semicolon, and none before them.</p>

<h3>Try it yourself</h3>
<ol>
<li>Type each of the sample lines above twice without looking at the keys.</li>
<li>Type this sentence: <code>On 12 May 2025, Grace paid UGX 45,000 (20% of the fees); was it enough?</code></li>
<li>Type your email address, including the <code>@</code> symbol, three times.</li>
<li>Write three sentences about your family using capitals, commas, a question mark and an apostrophe.</li>
</ol>

<h3>Common mistakes</h3>
<ul>
<li>Using one Shift key for everything. Alternate hands for speed and comfort.</li>
<li>Forgetting the space after punctuation.</li>
<li>Typing the letter O for zero or the letter l for 1.</li>
</ul>

<h3>Key points</h3>
<ul>
<li>Use the Shift on the opposite side from the letter.</li>
<li>Each finger types the number above the letter it normally types.</li>
<li>Symbols above the numbers need Shift.</li>
<li>Leave one space after punctuation and none before.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'How to practise: a written practice plan',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>Knowing the keys is not enough; you need regular practice. This lesson gives you a written practice plan and tips for breaking the habit of looking down. Short daily practice beats one long session a week, because your fingers learn through repetition.</p>

<h3>The rules of practice</h3>
<ol>
<li><strong>Little and often</strong>: 15 to 20 minutes every day is better than two hours once a week.</li>
<li><strong>Accuracy first</strong>: type slowly enough to make very few mistakes. Speed comes by itself.</li>
<li><strong>Eyes on the text</strong>: never look at the keyboard. If you do not know a key, think of its finger and try; then correct it.</li>
<li><strong>Same position every time</strong>: sit properly and begin on the home row.</li>
<li><strong>Stop when tired</strong>: tired fingers practise mistakes.</li>
</ol>

<h3>How to stop looking at your keys</h3>
<ul>
<li>Cover your hands with a folded cloth or a sheet of card held at wrist height.</li>
<li>Place the practice text beside the screen or keyboard so you read from it rather than from your hands.</li>
<li>If you do not know where a key is, resist looking; feel for the home bumps and try again.</li>
<li>If your keyboard keys have worn letters, do not depend on them.</li>
</ul>

<h3>A 20-minute daily session</h3>
<table>
<thead><tr><th>Time</th><th>Activity</th></tr></thead>
<tbody>
<tr><td>2 min</td><td>Warm-up: stretch fingers, then type <code>asdf jkl;</code> lines</td></tr>
<tr><td>6 min</td><td>New or weak keys: short drills such as <code>frf frf juj juj</code></td></tr>
<tr><td>8 min</td><td>Words and sentences using all keys learned so far</td></tr>
<tr><td>3 min</td><td>One paragraph copied carefully, then counted for mistakes</td></tr>
<tr><td>1 min</td><td>Stretch and rest</td></tr>
</tbody>
</table>

<h3>Sample practice text</h3>
<p>Type this paragraph slowly. Use a notebook to write down any word where you made a mistake, then type that word five times.</p>
<pre>
Learning to type is like learning to ride a bicycle. At first it feels
slow and difficult, but with daily practice your fingers begin to know
the way. Keep your eyes on the screen, keep your hands relaxed, and
never rush. Accuracy comes first. When you can type a whole page
without errors, you will find that speed arrives on its own.
</pre>
<p>Make your own practice text. Copy a paragraph from a school book, a newspaper or a story you like. Using real writing is far better than random letters because it teaches the combinations used in daily work.</p>

<h3>Keep a practice log</h3>
<p>In an exercise book, write the date, the minutes you practised, and your mistakes. A page of progress is motivating, and it shows which keys need more work.</p>

<h3>Try it yourself</h3>
<ol>
<li>Choose a daily time and place for 20 minutes of practice and write it down.</li>
<li>Do one full session using the table above and the sample paragraph.</li>
<li>Write the three words where you made the most mistakes and type each five times.</li>
<li>Record the date and result in your practice log.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Practise a little every day, with accuracy before speed.</li>
<li>Do not look at the keys; cover them if you must.</li>
<li>A session has a warm-up, drills, real text and a rest.</li>
<li>Keep a log and repeat the words you get wrong.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which finger types the letter E?
- Left ring finger
* Left middle finger
- Right middle finger
- Left index finger
= The left middle finger rests on D and reaches up to E.

Q: Which finger types the letter P?
- Right ring finger
- Right middle finger
* Right little finger
- Left little finger
= The right little finger rests on the semicolon and reaches up to P.

Q: You want to type a capital T with the left hand. Which Shift do you use?
- The left Shift
* The right Shift
- Caps Lock only
- Either; it makes no difference
= Use the Shift on the opposite side from the hand that types the letter.

Q: Which is the best practice habit for beginners?
- Practise for three hours once a week
- Look at your hands to avoid mistakes
* Practise a little every day, putting accuracy before speed
- Type as fast as possible from the first day
= Short, regular, accurate sessions build lasting habits.

Q: What should you do if you cannot find a key without looking?
- Look at the keyboard every time
* Return to the home row bumps, think of the finger, and try again
- Give up typing
- Use only your index fingers
= Training yourself to find keys by feel is the whole point of touch typing.

Q: Which sentence uses punctuation spacing correctly?
- Hello ,how are you ?
* Hello, how are you?
- Hello,how are you?
- Hello , how are you ?
= Put one space after a comma and none before punctuation marks.

Q: Which key combination types the @ symbol on most keyboards?
- Alt+2
- Ctrl+2
* Shift+2
= The symbols above the numbers need Shift. Check your own keyboard because some layouts differ.

Q: Why is real text (such as a paragraph from a book) better practice than random letters?
- It is shorter
* It teaches the letter combinations used in real writing
- It uses fewer keys
- It does not need accuracy
= Real words train the patterns your fingers need every day.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Speed and accuracy',
            'summary' => 'Measure words per minute and accuracy, build rhythm, fix error patterns and follow a four-week plan.',
            'lessons' => [
                [
                    'title'   => 'Measuring speed and accuracy',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>To improve, you must measure. In this lesson you will learn what words per minute means, how to calculate it by hand or with a clock, and how to work out your accuracy. Both numbers matter. A fast typist who makes many mistakes wastes time correcting them.</p>

<h3>What is a "word" in typing?</h3>
<p>In typing tests, a standard <strong>word is five characters</strong>, including spaces and punctuation. This allows fair comparison between short and long words. So <code>is</code> is less than one word, and <code>education</code> is nearly two.</p>

<h3>Words per minute (WPM)</h3>
<p><strong>Gross WPM</strong> is the total number of characters typed, divided by five, divided by the minutes taken.</p>
<pre>Gross WPM = (characters typed / 5) / minutes</pre>
<p>Example: you type 1,000 characters in 5 minutes. 1,000 / 5 = 200 words; 200 / 5 minutes = 40 WPM.</p>
<p>If you do not want to count each character, type a test paragraph and note how many words the program or the text says it has. Then divide the words by the minutes.</p>

<h3>Net WPM: counting your mistakes</h3>
<p>Employers usually ask for the <strong>net WPM</strong>, which subtracts errors:</p>
<pre>Net WPM = Gross WPM - (uncorrected errors / minutes)</pre>
<p>Example: Gross speed 40 WPM, 10 uncorrected mistakes in 5 minutes. 10 / 5 = 2. Net WPM = 40 - 2 = 38.</p>

<h3>Accuracy</h3>
<pre>Accuracy % = (correct characters / total characters typed) x 100</pre>
<p>Example: you typed 1,000 characters and 20 were wrong. Correct = 980. Accuracy = 980 / 1,000 x 100 = 98 percent. Most job tests ask for 95 percent or more, and good typists reach 97 to 99 percent.</p>

<h3>Doing a timed test</h3>
<ol>
<li>Choose a paragraph of at least 250 words (a page from a book, or a newspaper article).</li>
<li>Open Notepad or Word, and set a timer for exactly 3 minutes on your phone.</li>
<li>Start the timer and type the paragraph exactly as written, without stopping to fix every error.</li>
<li>When the timer ends, stop. Count the characters typed (Word shows the word count in the status bar; Notepad does not, so use Word if possible).</li>
<li>Count the mistakes by comparing your text with the original.</li>
<li>Calculate WPM and accuracy using the formulas above.</li>
</ol>
<p>Word's status bar at the bottom shows the number of words, which is good enough if you are typing mostly average-length words. Multiply by five if you want characters.</p>

<h3>What speeds are normal?</h3>
<table>
<thead><tr><th>Level</th><th>Net WPM</th></tr></thead>
<tbody>
<tr><td>Beginner (just learning)</td><td>10 to 20</td></tr>
<tr><td>Basic office work</td><td>30 to 40</td></tr>
<tr><td>Good, professional typist</td><td>45 to 60</td></tr>
<tr><td>Very fast</td><td>70 and above</td></tr>
</tbody>
</table>
<p>Do not worry if you are at the bottom today. The numbers rise with practice.</p>

<h3>Try it yourself</h3>
<ol>
<li>Take a 3-minute typing test with a paragraph from a book. Do not worry about the result.</li>
<li>Calculate gross WPM, net WPM and accuracy and write them in your practice log with today's date.</li>
<li>Write one target: for example 5 more WPM or 2 percent more accuracy in four weeks.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A typing "word" is five characters, including spaces.</li>
<li>Gross WPM = characters / 5 / minutes; net WPM subtracts uncorrected errors per minute.</li>
<li>Accuracy is correct characters divided by total, times 100.</li>
<li>Job tests often need 30 to 40 net WPM at 95 percent or better.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Rhythm, error patterns and warm-ups',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>Fast typing is not about hitting keys quickly; it is about a smooth, steady rhythm and few mistakes. In this lesson you will learn how to build rhythm, find your personal error patterns and fix them, and warm up before you practise. Accuracy comes before speed because every correction costs more time than careful typing.</p>

<h3>Accuracy before speed</h3>
<p>Imagine you type at 40 WPM but make a mistake every 20 words. Each mistake means stopping, backspacing and retyping, about three to five seconds. Over a long document, that loss is bigger than the gain of rushing. A good rule: <strong>if you make more than one mistake per line of practice, slow down</strong>.</p>

<h3>Build rhythm</h3>
<ul>
<li>Press the keys at an even tempo, like a drumbeat, with equal gaps between keys. Do not type in bursts followed by pauses.</li>
<li>Say the words in your head or whisper them. Matching your typing to the beat of speech keeps it even.</li>
<li>Look ahead. Read the next word while you type the current one. This keeps your hands moving.</li>
<li>Use a metronome app or a clock tick if you wish, and raise the pace slowly.</li>
<li>Spend the same effort on each hand. Many people have a weaker hand; give it extra drills.</li>
</ul>

<h3>Find your error patterns</h3>
<p>After a practice test, look at your mistakes. Most people repeat the same few kinds:</p>
<table>
<thead><tr><th>Pattern</th><th>Example</th><th>Fix</th></tr></thead>
<tbody>
<tr><td>Swapping letters</td><td><code>teh</code> for <code>the</code></td><td>Type the word slowly 10 times</td></tr>
<tr><td>Wrong neighbouring key</td><td><code>wirk</code> for <code>work</code></td><td>Check finger reach for O and I</td></tr>
<tr><td>Missed space or double space</td><td><code>thecat</code></td><td>Press the thumb gently, count words</td></tr>
<tr><td>Wrong capital</td><td><code>gulu</code> for <code>Gulu</code></td><td>Practise Shift with names and places</td></tr>
<tr><td>Repeated letter</td><td><code>happpy</code></td><td>Relax the finger and lighten the press</td></tr>
</tbody>
</table>
<p>Make a short list of your top five problem words or keys. Each day, type each problem word 10 times correctly. In two weeks the list will shrink.</p>

<h3>Warm-ups</h3>
<p>Before you type for a long time, warm up your hands, just as a runner stretches:</p>
<ol>
<li>Roll your shoulders backward five times.</li>
<li>Open and close your hands ten times.</li>
<li>Gently pull each finger back for three seconds.</li>
<li>Type slowly <code>asdf jkl;</code> for one minute, then a short line of easy words.</li>
</ol>

<h3>Drills for speed</h3>
<p>Once you can type with 97 percent accuracy, try these:</p>
<pre>
the and for are but not you all can had her was one our out
they will with have this from word what were when your said
</pre>
<p>These are some of the most common English words. If you can type them smoothly, most of your daily writing is fast.</p>

<h3>Try it yourself</h3>
<ol>
<li>Do the warm-up above.</li>
<li>Type the common-words drill three times, aiming for even rhythm and no mistakes.</li>
<li>Take a one-minute test and list your mistakes.</li>
<li>Pick your five worst words and type each ten times correctly.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Even rhythm and few mistakes make you faster than rushing.</li>
<li>Study your mistakes; most people have a few repeated patterns.</li>
<li>Drill problem words and the most common words.</li>
<li>Warm up your hands before long sessions.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Practice tools and a four-week plan',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>You now know the technique. This lesson explains the kinds of free tools that help you practise and gives you a four-week plan that takes you step by step from beginner to confident typist. A plan keeps you honest on days when you do not feel like practising.</p>

<h3>Kinds of practice tools</h3>
<p>You do not need to buy anything. Free tools come in several kinds. You can find them by searching the web for the type described, and choose one with a clear, simple screen and no unsafe downloads. Prefer tools that run in the browser, so you do not have to install anything.</p>
<ul>
<li><strong>Typing lesson sites</strong>: guided lessons that teach key by key and give a score. These are best for complete beginners.</li>
<li><strong>Typing test sites</strong>: you type a short passage for one, three or five minutes and the site shows your WPM and accuracy.</li>
<li><strong>Typing games</strong>: falling words or races that make practice fun, good for building speed once you know the keys.</li>
<li><strong>Phone apps</strong>: typing trainers that you can use for a few minutes when waiting. They help build the habit.</li>
<li><strong>Offline practice</strong>: typing from a printed page or a book in Notepad or Word, which is what this course uses.</li>
</ul>
<p>Safety: avoid any site that asks you to install unknown software, pay before you try, or give personal details. Typing practice only needs a keyboard.</p>

<h3>Four-week plan (20 minutes a day, six days a week)</h3>
<table>
<thead><tr><th>Week</th><th>Focus</th><th>Daily work</th><th>Goal</th></tr></thead>
<tbody>
<tr><td>1</td><td>Home row and correct fingers</td><td>Drills from Module 1; add the top row on days 4 to 6</td><td>Type with no looking at the keys, even if slowly</td></tr>
<tr><td>2</td><td>All letter rows, Shift and punctuation</td><td>Words and sentences; the pangram daily</td><td>97 percent accuracy at a slow pace</td></tr>
<tr><td>3</td><td>Numbers, symbols and common words</td><td>Common-words drill, sentences with numbers and dates; a 3-minute test on day 6</td><td>Up 5 WPM from week 1 test</td></tr>
<tr><td>4</td><td>Speed with accuracy, real text</td><td>Copy a full page each day; one 5-minute test on day 6</td><td>25 to 30 net WPM at 95 percent or more</td></tr>
</tbody>
</table>
<p>On the seventh day, rest. Resting helps the brain store what you learned.</p>

<h3>Weekly check</h3>
<ol>
<li>Take a 3-minute test on the same passage type each week.</li>
<li>Record gross WPM, net WPM and accuracy in your log.</li>
<li>Choose one habit to improve next week, for example "keep eyes on the text" or "use the correct Shift".</li>
</ol>

<h3>After the four weeks</h3>
<p>Keep practising ten minutes a day, and use your typing in real tasks: write emails, keep a diary, type notes in class. The more you type with the correct method, the more automatic it becomes. Do not be discouraged by plateaus; speed often stays flat for a week or two and then jumps.</p>

<h3>Try it yourself</h3>
<ol>
<li>Write the four-week plan in your notebook with the dates.</li>
<li>Choose a practice time that you can keep every day and set a phone reminder.</li>
<li>Do the day 1 session today and record it.</li>
<li>Search the web for a free browser-based typing lesson site and look only at the layout. Do not enter any personal details or download anything.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Free typing lessons, tests, games and phone apps exist; choose safe browser-based ones.</li>
<li>Follow the four-week plan: home row, all rows, numbers and symbols, then speed with real text.</li>
<li>Test weekly and record the results.</li>
<li>Consistency matters more than long sessions.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: In typing tests, how long is a standard "word"?
- One letter
- Three letters
* Five characters, including spaces
- Any word in the dictionary
= A standard word is five characters so tests are fair for short and long words.

Q: You type 1,500 characters in 5 minutes. What is your gross WPM?
- 30
* 60
- 300
- 7.5
= 1,500 / 5 = 300 words; 300 / 5 minutes = 60 WPM.

Q: Gross speed is 40 WPM and you made 10 uncorrected errors in 5 minutes. What is your net WPM?
- 30
- 42
* 38
- 35
= 10 errors / 5 minutes = 2 per minute; 40 - 2 = 38.

Q: You typed 500 characters and 25 were wrong. What is your accuracy?
- 90%
- 92%
* 95%
- 98%
= Correct = 475; 475 / 500 = 95%.

Q: What is the best way to improve overall typing speed?
- Type as fast as you can, ignoring mistakes
* Build accuracy first, then raise speed gradually
- Look at the keys to be sure
- Practise only once a week
= Fixing mistakes costs more time than typing carefully.

Q: Which are good ways to find and fix error patterns? (choose all that apply)
* List the words you get wrong and type each ten times
* Slow down on the keys that cause mistakes
- Ignore your mistakes
= Studying your own mistakes shows exactly where to practise.

Q: When choosing a free typing practice website, what should you avoid?
- Browser-based lessons
- Sites with a simple screen
* Sites that ask you to install unknown software or give personal details
- Sites that show your WPM
= You only need a keyboard to practise, so be careful with downloads and personal details.

Q: According to the four-week plan, what is the focus of week 1?
- Typing at 60 WPM
* The home row and correct fingers without looking
- Keyboard shortcuts
- Voice typing
= First learn the home row and finger positions; speed comes later.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Keyboard shortcuts and productivity',
            'summary' => 'Everyday shortcuts, Windows key tricks, moving through text quickly, special characters, phone typing and voice typing.',
            'lessons' => [
                [
                    'title'   => 'The essential keyboard shortcuts',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>A keyboard shortcut is a combination of keys that does something faster than using the mouse. In this lesson you will learn the ten shortcuts that almost every computer user needs. They work in nearly every program: Word, Excel, your browser, email and Notepad.</p>

<h3>How shortcuts work</h3>
<p>Hold the first key, press the second, then release both. In writing, a plus sign means "together": <code>Ctrl+C</code> means hold Ctrl and tap C. On a Mac the <strong>Command</strong> key (marked with a cloverleaf symbol) does the job that Ctrl does on Windows. Mac equivalents are shown in brackets.</p>

<h3>The essential ten</h3>
<table>
<thead><tr><th>Shortcut</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Ctrl+C (Mac: Command+C)</td><td><strong>Copy</strong> the selected text or item</td></tr>
<tr><td>Ctrl+X (Command+X)</td><td><strong>Cut</strong>: remove it and keep it ready to paste</td></tr>
<tr><td>Ctrl+V (Command+V)</td><td><strong>Paste</strong> what you copied or cut</td></tr>
<tr><td>Ctrl+Z (Command+Z)</td><td><strong>Undo</strong> the last action</td></tr>
<tr><td>Ctrl+Y (Mac: Command+Shift+Z)</td><td><strong>Redo</strong> what you undid</td></tr>
<tr><td>Ctrl+A (Command+A)</td><td><strong>Select all</strong> in the document, page or folder</td></tr>
<tr><td>Ctrl+S (Command+S)</td><td><strong>Save</strong> your work</td></tr>
<tr><td>Ctrl+F (Command+F)</td><td><strong>Find</strong> a word on the page or document</td></tr>
<tr><td>Ctrl+P (Command+P)</td><td><strong>Print</strong>, which opens the print window</td></tr>
<tr><td>Ctrl+B, Ctrl+I, Ctrl+U (Command+B, I, U)</td><td>Bold, italic, underline</td></tr>
</tbody>
</table>
<p>A good way to remember them: the keys are close together on the left of the keyboard (Z, X, C, V), so you can use them with one hand while the other holds the mouse.</p>

<h3>Using copy and paste step by step</h3>
<ol>
<li>Select the text by dragging with the mouse, or hold <strong>Shift</strong> and press the arrow keys.</li>
<li>Press <strong>Ctrl+C</strong>.</li>
<li>Click where you want the text to go.</li>
<li>Press <strong>Ctrl+V</strong>.</li>
</ol>
<p>To paste without the original formatting (fonts and colours), use <strong>Ctrl+Shift+V</strong>, which works in many programs and browsers.</p>

<h3>Other very useful shortcuts</h3>
<ul>
<li><strong>Ctrl+N</strong> (Command+N): a new document or window.</li>
<li><strong>Ctrl+O</strong> (Command+O): open a file.</li>
<li><strong>Ctrl+W</strong> (Command+W): close the current tab or window.</li>
<li><strong>Ctrl+T</strong> (Command+T): open a new tab in the browser.</li>
<li><strong>F5</strong> or <strong>Ctrl+R</strong> (Command+R): refresh a web page.</li>
<li><strong>Esc</strong>: cancel or close a menu.</li>
</ul>

<h3>Tips to learn them</h3>
<p>Do not try to learn all at once. Choose three, use them for a week instead of the mouse, then add three more. Write a small card with the shortcuts and put it beside your screen.</p>

<h3>Try it yourself</h3>
<ol>
<li>Open Notepad and type a sentence of five words.</li>
<li>Press Ctrl+A to select all, Ctrl+C to copy, click at the end, press Enter, then Ctrl+V to paste.</li>
<li>Delete the pasted text and press Ctrl+Z to bring it back, then Ctrl+Y to remove it again.</li>
<li>Press Ctrl+S, save the file as <code>shortcuts</code>, and use Ctrl+F to find one of your words.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Shortcuts use two or more keys together: hold the first, tap the second.</li>
<li>Ctrl+C, X, V copy, cut and paste; Ctrl+Z undoes and Ctrl+S saves.</li>
<li>On a Mac, use Command in place of Ctrl.</li>
<li>Learn three shortcuts at a time.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Windows key shortcuts and moving through text',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>This lesson covers shortcuts that control the computer itself, such as switching programs, and shortcuts that move through your text without a mouse. These keep your hands on the keyboard and make you much faster in long documents.</p>

<h3>The Windows key</h3>
<p>The <strong>Windows key</strong> has the Windows logo and sits between Ctrl and Alt on the bottom row. Combined with other keys, it controls Windows 10 and 11:</p>
<table>
<thead><tr><th>Shortcut</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Windows key</td><td>Open the Start menu; then type to search for a program or file</td></tr>
<tr><td>Windows+D</td><td>Show the desktop (press again to return)</td></tr>
<tr><td>Windows+E</td><td>Open File Explorer</td></tr>
<tr><td>Windows+L</td><td>Lock the computer when you leave it</td></tr>
<tr><td>Windows+I</td><td>Open Settings</td></tr>
<tr><td>Windows+Left or Right arrow</td><td>Snap a window to the left or right half of the screen</td></tr>
<tr><td>Windows+Shift+S</td><td>Take a screenshot of part of the screen</td></tr>
<tr><td>Windows+V</td><td>Show the clipboard history, if switched on</td></tr>
<tr><td>Ctrl+Shift+Esc</td><td>Open Task Manager (when a program is stuck)</td></tr>
</tbody>
</table>
<p>On a Mac, Command+Space opens Spotlight search, and Command+Shift+4 takes a part-screen screenshot.</p>

<h3>Switching between programs</h3>
<p>Hold <strong>Alt</strong> and tap <strong>Tab</strong> to jump to the previous window. Keep holding Alt and tap Tab again to move along the list, then release. On a Mac the same is <strong>Command+Tab</strong>. In a browser, <strong>Ctrl+Tab</strong> moves to the next tab and <strong>Ctrl+Shift+Tab</strong> to the previous.</p>
<p><strong>Alt+F4</strong> closes the current program (Mac: Command+Q). Save first.</p>

<h3>Moving through text</h3>
<table>
<thead><tr><th>Press</th><th>Moves the cursor</th></tr></thead>
<tbody>
<tr><td>Arrow keys</td><td>One character or line</td></tr>
<tr><td>Ctrl+Left or Right arrow (Mac: Option+Arrow)</td><td>One word at a time</td></tr>
<tr><td>Home / End (Mac: Command+Left / Right)</td><td>Start / end of the line</td></tr>
<tr><td>Ctrl+Home / Ctrl+End (Mac: Command+Up / Down)</td><td>Start / end of the document</td></tr>
<tr><td>Page Up / Page Down</td><td>One screen up or down</td></tr>
<tr><td>Ctrl+Up or Down arrow</td><td>Start of the previous or next paragraph (in Word)</td></tr>
</tbody>
</table>

<h3>Selecting text with the keyboard</h3>
<p>Add <strong>Shift</strong> to any movement shortcut to select text as the cursor moves.</p>
<ul>
<li><strong>Shift+Right arrow</strong> selects one character at a time.</li>
<li><strong>Ctrl+Shift+Right arrow</strong> selects one word at a time.</li>
<li><strong>Shift+End</strong> selects to the end of the line.</li>
<li><strong>Ctrl+Shift+End</strong> selects to the end of the document.</li>
</ul>
<p>Also, double-click a word to select it and triple-click to select a whole paragraph. To delete a whole word to the left, press <strong>Ctrl+Backspace</strong>.</p>

<h3>Try it yourself</h3>
<ol>
<li>Open Notepad and type two lines about your day.</li>
<li>Use Home, End and Ctrl+Left/Right to move through the text without touching the mouse.</li>
<li>Select the first word with Ctrl+Shift+Right arrow and press Delete.</li>
<li>Open Notepad and File Explorer (Windows+E), then switch between them with Alt+Tab.</li>
<li>Press Windows+D to show the desktop, then press it again to return.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>The Windows key opens Start; combined with D, E, L and I it does common tasks.</li>
<li>Alt+Tab switches between open programs.</li>
<li>Ctrl plus arrows jumps by word; Home and End jump to the line edges.</li>
<li>Adding Shift to a movement selects text.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Special characters, phone typing and voice typing',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>Not all writing is done on a full keyboard. In this lesson you will learn how to type special characters, how to type faster on a phone, and how to use voice typing when it is the best choice. These skills are useful when you write names, messages in other languages, or when you are away from a computer.</p>

<h3>Special characters and accents</h3>
<p>Some names and languages use letters that are not on a standard keyboard. Here are the main ways to type them.</p>
<ul>
<li><strong>Windows emoji and symbol panel</strong>: press <strong>Windows + . (full stop)</strong> to open a panel with emojis, symbols and special characters. Click one to insert it.</li>
<li><strong>Accents on a Mac</strong>: press and hold a letter such as <code>e</code> and choose the accented version from the small menu.</li>
<li><strong>Alt codes on Windows</strong>: with Num Lock on, hold <strong>Alt</strong> and type a number on the numeric keypad. For example, <code>Alt+0233</code> types an e with an acute accent. This needs a keypad, which many laptops do not have.</li>
<li><strong>In Word</strong>: choose <strong>Insert</strong>, then <strong>Symbol</strong>, to pick any character.</li>
<li><strong>Add a keyboard language</strong>: in Windows, open Settings, then <strong>Time &amp; language</strong>, then <strong>Language &amp; region</strong>, and add a language. Switch between languages with <strong>Windows+Space</strong>. In Uganda, people sometimes need Luganda, Acholi or Swahili characters; these mostly use the same Latin letters, so the standard English keyboard works well.</li>
</ul>

<h3>Useful symbols to know</h3>
<table>
<thead><tr><th>Symbol</th><th>How</th></tr></thead>
<tbody>
<tr><td><code>@</code></td><td>Shift+2 (check your keyboard)</td></tr>
<tr><td>Copyright symbol, degree sign</td><td>Windows+. panel, or Insert, Symbol</td></tr>
<tr><td>Euro and pound signs</td><td>Windows+. panel</td></tr>
<tr><td>Ellipsis, bullet</td><td>Windows+. panel</td></tr>
</tbody>
</table>

<h3>Typing faster on a phone</h3>
<ol>
<li>Hold the phone with two hands in portrait and type with both thumbs, or with one thumb for the quickest control.</li>
<li>Turn on <strong>predictive text</strong> (suggestions above the keys) and tap suggested words instead of typing them in full.</li>
<li>Use <strong>swipe typing</strong> (also called glide typing) if your keyboard offers it: slide your finger across the letters of a word without lifting.</li>
<li>Turn on <strong>auto-capitalisation</strong>, and learn that double-tapping the space bar often adds a full stop.</li>
<li>Long-press letters to reveal accents and long-press the full stop for more punctuation.</li>
<li>Add shortcuts or text replacements in your keyboard settings, such as "myemail" expanding to your email address.</li>
</ol>
<p>Always read before sending. Predictive text can change a word into the wrong one.</p>

<h3>Voice typing</h3>
<p>Voice typing turns your speech into text. It is helpful when you have a lot to write, when your hands are tired, or when you are away from a keyboard.</p>
<ul>
<li><strong>Phone</strong>: tap the microphone icon on the keyboard and speak. Say "full stop", "comma" or "new line" to add punctuation, depending on the language.</li>
<li><strong>Windows</strong>: press <strong>Windows+H</strong> to start voice typing in a text box.</li>
<li><strong>Google Docs</strong>: choose <strong>Tools</strong> and then <strong>Voice typing</strong>.</li>
<li><strong>Word</strong>: in Microsoft 365, select <strong>Dictate</strong> on the Home tab.</li>
</ul>
<p>Voice typing usually needs an internet connection and a quiet place. Speak clearly at a normal pace and then <strong>proofread</strong>, because it makes mistakes with names and accents. Be aware that your voice is sent to the service provider for processing, so do not dictate private information such as passwords or bank details.</p>

<h3>Try it yourself</h3>
<ol>
<li>On a Windows computer, open Notepad and press Windows+. to insert a degree sign and a bullet.</li>
<li>On your phone, write a message of two sentences using swipe typing or predictive text. Time yourself.</li>
<li>Try voice typing for a short paragraph (about 30 seconds) on your phone or with Windows+H. Count how many words need correcting.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Windows+. opens the symbols panel; hold a letter on a Mac or phone for accents.</li>
<li>Predictive text, swipe typing and text replacements speed up phone typing.</li>
<li>Voice typing is fast but always needs proofreading; do not dictate private details.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which shortcut copies selected text on Windows?
- Ctrl+V
* Ctrl+C
- Ctrl+X
- Ctrl+P
= Ctrl+C copies. Ctrl+V pastes and Ctrl+X cuts.

Q: What is the Mac equivalent of Ctrl+S?
- Option+S
- Control+Command+S
* Command+S
- Shift+S
= On a Mac, the Command key replaces Ctrl in most shortcuts.

Q: You pressed a wrong key and changed your text. Which shortcut brings it back?
- Ctrl+Y
* Ctrl+Z
- Ctrl+F
- Alt+F4
= Ctrl+Z undoes the last action.

Q: Which shortcut lets you jump between open programs?
- Alt+F4
- Ctrl+Shift+Esc
* Alt+Tab
- Windows+D
= Hold Alt and tap Tab to switch windows.

Q: Which keys select one word at a time to the right of the cursor?
- Shift+Right arrow
* Ctrl+Shift+Right arrow
- Ctrl+End
- Alt+Right arrow
= Ctrl moves by word; adding Shift selects as it moves.

Q: What does Windows+L do?
- Opens a list of programs
- Logs out of the internet
* Locks the computer
- Opens Settings
= Windows+L locks the screen so nobody can use your computer while you are away.

Q: Which are good habits when using voice typing? (choose all that apply)
* Speak clearly and proofread afterwards
* Avoid dictating passwords and bank details
- Send the message without reading it
= Voice typing makes mistakes, and your speech may be processed online.

Q: How can you type an accented letter on a Mac?
- Hold Ctrl and press the letter
- It is not possible
* Press and hold the letter and choose the accented version
- Press Caps Lock three times
= Holding a letter shows a menu of accented versions.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Typing at work',
            'summary' => 'Professional writing, data entry, copy typing, typing tests, formatting, proofreading and avoiding strain, with a final practice test.',
            'lessons' => [
                [
                    'title'   => 'Writing emails and messages professionally',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>Fast typing is only useful if what you type is clear and polite. After this lesson you will be able to write a short professional email or message with a proper subject, greeting, body and closing. Good writing makes a first impression, especially in job applications and communication with clients, teachers and employers.</p>

<h3>Parts of a professional email</h3>
<ol>
<li><strong>Subject line</strong>: short and specific. "Application for Data Entry Clerk" is better than "Hello" or "Job".</li>
<li><strong>Greeting</strong>: "Dear Ms Akello," or "Dear Hiring Manager," if you do not know the name. Use "Hello Peter," for a colleague you know well.</li>
<li><strong>Opening</strong>: say who you are and why you are writing in one sentence.</li>
<li><strong>Body</strong>: short paragraphs, one idea each. Put the most important point first.</li>
<li><strong>Closing</strong>: a polite line such as "Thank you for your time," followed by "Kind regards," or "Yours sincerely,".</li>
<li><strong>Signature</strong>: your full name, phone number and, if relevant, your job title.</li>
</ol>

<h3>An example</h3>
<pre>
Subject: Request for a meeting on Friday

Dear Mr Okello,

I am writing to ask if we can meet on Friday at 10:00 to discuss the
community training schedule. The meeting should take about 30 minutes.

Please let me know if this time is suitable. If not, I can meet on
Thursday afternoon.

Kind regards,
Grace Akello
0772 000 000
</pre>

<h3>Style rules</h3>
<ul>
<li>Use full words, correct spelling and punctuation. Avoid text-speak such as "u", "pls" or "thx" in formal messages.</li>
<li>Do not type in all capitals; it looks like shouting.</li>
<li>Keep sentences short and your tone polite, even when you are disappointed.</li>
<li>Write the date, time and place in full to avoid confusion.</li>
<li>Do not use too many exclamation marks or emojis in a formal message.</li>
</ul>

<h3>Messages in WhatsApp and SMS</h3>
<p>Short chat messages can be more relaxed, but if you message a manager, a customer or a school administration, begin with a greeting and keep it respectful. Voice notes are not always welcome at work; a short written message is often better.</p>

<h3>Before you click Send</h3>
<ol>
<li>Check the recipient's address. A single wrong letter sends your message to a stranger.</li>
<li>Read the whole message aloud or in your head.</li>
<li>Check the subject line and attachments. If you say "attached", make sure the file is attached.</li>
<li>Use Reply All only when everybody really needs the answer.</li>
</ol>

<h3>Try it yourself</h3>
<ol>
<li>Write the email above in Notepad or Word, using your own details.</li>
<li>Write a second email to a school or business asking for information about a course or a product.</li>
<li>Type each email at a steady pace and proofread it twice.</li>
<li>Take out any slang or text-speak you can find.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A professional email has a clear subject, greeting, opening, body, closing and signature.</li>
<li>Be clear, polite and brief; avoid capitals and text-speak.</li>
<li>Check the address, attachments and spelling before sending.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Data entry, copy typing and typing tests',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>Many entry-level jobs in banks, schools, health centres, NGOs and shops involve typing information from paper into a computer. In this lesson you will learn how to enter data accurately, how to copy-type from a page, and what employers look for in a typing test. Knowing the standards helps you prepare with confidence.</p>

<h3>What is data entry?</h3>
<p><strong>Data entry</strong> means typing information such as names, phone numbers, amounts and dates into a system, a form or a spreadsheet. Errors can be costly: a wrong digit in an account number or a mark can cause real problems. Accuracy is more valued than raw speed.</p>

<h3>Habits for accurate data entry</h3>
<ul>
<li>Use a ruler or a sheet of paper to mark the line you are copying from, so you do not skip lines.</li>
<li>Place the paper at the side of your screen at a comfortable angle, or use a document holder.</li>
<li>Type exactly what is on the paper, including spelling and capital letters. Do not "correct" the original unless you are told to; report doubts to your supervisor.</li>
<li>Say numbers silently in groups, for example "0772 123 456".</li>
<li>Use the numeric keypad with your right hand for number-heavy work.</li>
<li>Use <strong>Tab</strong> to move to the next box in a form and <strong>Shift+Tab</strong> to go back.</li>
<li>After each record or page, check a few entries against the paper before moving on.</li>
</ul>

<h3>Copy typing from paper</h3>
<p>Copy typing is harder than typing something you think of yourself because your eyes must move between paper and screen. Practise by:</p>
<ol>
<li>Reading a phrase, holding it in your memory, and typing it while your eyes go back to the page.</li>
<li>Keeping your head still, moving only your eyes.</li>
<li>Placing the paper close to the screen to reduce head movement.</li>
</ol>
<pre>
Name: Akello Grace    DOB: 14/03/2001    Phone: 0772 123 456
Item: Maize flour 50 kg    Qty: 12    Price: UGX 85,000    Total: UGX 1,020,000
</pre>

<h3>Typing tests for jobs</h3>
<p>Many employers ask applicants to take a short typing test. Typical features:</p>
<table>
<thead><tr><th>Item</th><th>Typical expectation</th></tr></thead>
<tbody>
<tr><td>Length</td><td>1 to 5 minutes</td></tr>
<tr><td>Speed for general office jobs</td><td>30 to 40 net WPM</td></tr>
<tr><td>Speed for data entry or secretarial roles</td><td>40 to 60 net WPM</td></tr>
<tr><td>Accuracy</td><td>At least 95 percent, often 97 percent</td></tr>
<tr><td>Content</td><td>Normal text, sometimes numbers and a form</td></tr>
</tbody>
</table>
<p>Requirements differ between employers, so read the job advert carefully.</p>

<h3>Tips for the test</h3>
<ol>
<li>Arrive early and use a comfortable seat. Adjust the chair and screen.</li>
<li>Warm up your fingers before you start.</li>
<li>Read the instructions twice. Do you have to correct errors? Is capitalisation counted?</li>
<li>Start steadily; do not rush the first line.</li>
<li>If you make a mistake, correct it quickly if it is allowed, or carry on if it is not.</li>
<li>Stay calm. Nerves cause mistakes.</li>
</ol>

<h3>Try it yourself</h3>
<ol>
<li>Copy the sample record lines above exactly into a Word or Notepad page, with your eyes on a printed or handwritten copy.</li>
<li>Write ten fake records with names, phone numbers and amounts, then copy them into an Excel sheet using Tab to move between columns.</li>
<li>Compare your copy with the original and count errors.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Data entry values accuracy; check your work against the source.</li>
<li>Copy exactly what is written, and use Tab to move between fields.</li>
<li>Job tests often ask for 30 to 60 net WPM at 95 percent or more.</li>
<li>Prepare calmly with warm-ups and read the instructions.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Formatting, proofreading and avoiding strain',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>Typing the words is only part of the job. In this lesson you will learn to format while you type, to proofread your work so that it is free of errors, and to protect your hands, eyes and back from injury. These habits keep you productive for years.</p>

<h3>Formatting while you type</h3>
<p>You can apply formatting as you go, using the shortcuts that work in Word, Google Docs and most email programs:</p>
<ul>
<li><strong>Ctrl+B</strong> turns bold on or off; <strong>Ctrl+I</strong> for italic; <strong>Ctrl+U</strong> for underline.</li>
<li><strong>Ctrl+E</strong> centres a line; <strong>Ctrl+L</strong> aligns left; <strong>Ctrl+R</strong> aligns right.</li>
<li><strong>Ctrl+Shift+&gt;</strong> and <strong>Ctrl+Shift+&lt;</strong> make the font bigger or smaller in Word.</li>
<li><strong>Tab</strong> at the start of a bullet line moves it in one level; <strong>Shift+Tab</strong> moves it back.</li>
</ul>
<p>Keep your formatting simple: one or two fonts, bold for headings, and enough space between paragraphs. Press Enter once between paragraphs, and avoid pressing the spacebar many times to move text across; use Tab or the alignment tools. On a Mac, use Command in place of Ctrl.</p>

<h3>Proofreading</h3>
<p>Proofreading means reading your text to find and fix mistakes before others see it. Spell-check helps, but it cannot tell that "their" should be "there", or that "form" should be "from".</p>
<ol>
<li><strong>Take a short break</strong> before checking, so you see what is actually written.</li>
<li><strong>Run the spell-check</strong>: in Word, press <strong>F7</strong>. Wavy red lines mean spelling; blue or double lines suggest grammar.</li>
<li><strong>Read slowly</strong>, aloud if possible. Your ear catches missing words.</li>
<li><strong>Check names, numbers and dates</strong> against the source. These are the most costly errors.</li>
<li><strong>Check the layout</strong>: spaces, fonts, alignment and page breaks.</li>
<li><strong>Read backwards</strong>, sentence by sentence, to spot spelling errors in a long text.</li>
</ol>
<p>Common pairs to watch: <em>their / there / they're</em>, <em>your / you're</em>, <em>its / it's</em>, <em>to / too</em>, <em>than / then</em>.</p>

<h3>Avoiding strain injuries</h3>
<p>Typing for hours without good habits can cause pain in the wrists, hands, neck, back and eyes (sometimes called repetitive strain injury). Prevent it with these steps:</p>
<ul>
<li>Keep your wrists straight and your forearms level.</li>
<li>Use a light touch. Do not hammer the keys.</li>
<li>Take a micro-break of one minute every 20 to 30 minutes and a longer break every hour. Stand up, walk, stretch.</li>
<li>Follow the <strong>20-20-20 rule</strong>: every 20 minutes, look at something 20 feet (about 6 metres) away for 20 seconds.</li>
<li>Keep the screen at arm's length and at or slightly below eye level; reduce glare and brightness if your eyes hurt.</li>
<li>Drink water and keep your whole body relaxed.</li>
<li>If you feel tingling, numbness or pain in your hands, stop, rest and see a health worker.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Type a short notice in Word with a centred bold title and a bulleted list of three items, using only keyboard shortcuts.</li>
<li>Insert three deliberate mistakes (for example "form" for "from") and see which ones the spell-check misses.</li>
<li>Proofread by reading the notice backwards, and fix everything you find.</li>
<li>Do a 30-second stretch routine for your hands, neck and shoulders.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use Ctrl+B, I, U and alignment shortcuts to format as you type.</li>
<li>Spell-check does not find every error; read slowly and check names, numbers and dates.</li>
<li>Take regular breaks and follow the 20-20-20 rule.</li>
<li>Stop and rest if you feel pain, tingling or numbness.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Final practice test: put it all together',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>This last lesson is a certification-style practice test that you can do at home. It checks your technique, speed, accuracy and shortcut skills in one sitting, just like a typing test for a job. Treat it as if it were the real thing.</p>

<h3>Before you begin</h3>
<ol>
<li>Sit properly and adjust your chair and screen.</li>
<li>Do the two-minute warm-up from Module 3.</li>
<li>Open a blank Word document (or Notepad) and set a phone timer.</li>
<li>Have your practice log and a pen ready.</li>
</ol>

<h3>Part 1: speed and accuracy (5 minutes)</h3>
<p>Type this passage for 5 minutes. If you finish it, begin again. Do not look at your hands. If you make a mistake, carry on.</p>
<pre>
Good communication is a skill that opens many doors. A person who can
write a clear email, fill in a form without errors, and type a report
quickly will always be valued at work. Computers have changed the way
we study, trade, and speak to each other, and the keyboard remains the
most important tool for putting our ideas into words. Learning to type
is not hard, but it does need patience. Practise a little every day,
keep your back straight, and relax your hands. Over time, your fingers
will find the keys by themselves, and you will be able to think about
what you are writing instead of where the letters are.
</pre>
<p><strong>Score yourself</strong>: use the formulas from Module 3. A passing level for this course is 25 net WPM at 95 percent accuracy; a strong result is 40 net WPM at 97 percent or more.</p>

<h3>Part 2: numbers and data entry (3 minutes)</h3>
<p>Type these lines exactly:</p>
<pre>
Ref 2025/0417  Akello Grace  0772 123 456  UGX 1,250,000  14/03/2025
Ref 2025/0418  Okello Peter  0753 987 654  UGX    85,500  15/03/2025
Ref 2025/0419  Auma Beatrice  0701 555 018  UGX  240,000  15/03/2025
Ref 2025/0420  Opio Samuel  0782 222 330  UGX 3,900,000  16/03/2025
</pre>
<p>Check each line against the source. Count errors. Aim for zero.</p>

<h3>Part 3: shortcuts (2 minutes)</h3>
<p>Without using the mouse menus, do the following in your document:</p>
<ol>
<li>Select all the text (Ctrl+A), copy it (Ctrl+C), go to the end (Ctrl+End), and paste it (Ctrl+V).</li>
<li>Undo the paste (Ctrl+Z), then redo it (Ctrl+Y).</li>
<li>Move to the start of the document (Ctrl+Home) and select the first line using Shift+End.</li>
<li>Make the first line bold (Ctrl+B) and centre it (Ctrl+E).</li>
<li>Find the word "keyboard" (Ctrl+F).</li>
<li>Save the file as <code>typing test</code> (Ctrl+S).</li>
</ol>

<h3>Part 4: an email (5 minutes)</h3>
<p>Write a short professional email asking an employer for a typing test appointment. Include a subject, greeting, body of two or three sentences, closing and your signature. Proofread it.</p>

<h3>Scoring yourself</h3>
<table>
<thead><tr><th>Part</th><th>Pass level</th></tr></thead>
<tbody>
<tr><td>1 Speed and accuracy</td><td>25 net WPM and 95 percent or more</td></tr>
<tr><td>2 Data entry</td><td>No more than 2 errors in the four lines</td></tr>
<tr><td>3 Shortcuts</td><td>All six steps done without the mouse</td></tr>
<tr><td>4 Email</td><td>All six parts present, no spelling errors</td></tr>
</tbody>
</table>
<p>If you pass all four, you are ready for most entry-level typing tests. If you do not, look at which part needs work, review that module, practise for another two weeks, and test again.</p>

<h3>Next steps</h3>
<ul>
<li>Keep practising 10 minutes a day to hold your skills.</li>
<li>Learn more shortcuts one at a time.</li>
<li>Move on to Word and Excel courses and use your typing in real tasks.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Complete all four parts in one sitting.</li>
<li>Write your scores in the practice log with the date.</li>
<li>Choose one skill to improve and plan two weeks of practice for it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A complete practice test covers speed, accuracy, data entry, shortcuts and writing.</li>
<li>A solid job-ready level is 30 to 40 net WPM at 95 percent or more.</li>
<li>Use your score to decide what to practise next.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which is the best subject line for an application email?
- Hello
- URGENT!!!
* Application for Data Entry Clerk
- Job
= A good subject is short and specific, so the reader knows what the email is about.

Q: Which of these should you avoid in a formal email? (choose all that apply)
* Typing everything in capital letters
* Text-speak such as "pls" and "u"
- A polite closing and signature
= Capitals look like shouting and text-speak looks careless. A greeting, closing and signature are good.

Q: In data entry, what should you do if the paper contains a spelling you think is wrong?
- Quietly change it
* Type exactly what is written and tell your supervisor about the doubt
- Skip the record
- Delete the line
= Data entry means copying exactly; raise concerns with the person in charge.

Q: Which key moves you to the next box in an online form?
- Enter
- Esc
* Tab
- Caps Lock
= Tab moves forward through fields, and Shift+Tab goes back.

Q: Why is spell-check not enough?
- It is always wrong
- It only works on numbers
* It cannot tell that a correctly spelt word is the wrong word, such as "form" for "from"
- It deletes text
= You must read carefully to catch correct words used in the wrong place.

Q: What does the 20-20-20 rule say?
- Type 20 words for 20 minutes
* Every 20 minutes, look at something about 20 feet (6 metres) away for 20 seconds
- Rest 20 hours a week
- Use 20 fonts
= It gives your eyes a regular rest.

Q: What should you do if you feel tingling or numbness in your hands while typing?
- Type faster
- Ignore it
* Stop, rest, and see a health worker if it continues
- Press harder
= These are warning signs of strain that need rest and advice.

Q: What typing standard do many general office jobs ask for?
- 5 WPM at 50 percent
* 30 to 40 net WPM at 95 percent accuracy or better
- 150 WPM
- No accuracy requirement
= Employers want reasonable speed with high accuracy.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What is the main benefit of touch typing?
- It makes the keys last longer
* It lets you type faster and more accurately with your eyes on the screen
- It removes the need for a keyboard
- It makes the computer faster
= Touch typing uses muscle memory, so you can watch the text instead of the keys.

Q: Which sitting position is recommended for typing?
- Leaning forward with wrists resting on the table edge
* Back supported, feet flat, elbows at about 90 degrees, wrists straight
- Lying back with the keyboard on your lap
- Standing with arms stretched out
= A neutral, supported posture reduces strain.

Q: Which keys have raised bumps to help you find the home position?
- A and ;
* F and J
- G and H
- Q and P
= The bumps on F and J guide your index fingers.

Q: Which finger types the letter K?
- Right index finger
* Right middle finger
- Right ring finger
- Left middle finger
= The right middle finger rests on K.

Q: Which are home row keys? (choose all that apply)
* A
* K
- Q
- M
= The home row is A S D F and J K L ;.

Q: Which key removes the character to the left of the cursor?
- Delete
- Enter
* Backspace
- Tab
= Backspace deletes backwards; Delete removes forwards.

Q: You want to type a capital L (a right-hand letter). Which Shift key should you use?
* The left Shift key
- The right Shift key
- Caps Lock and then Tab
- No Shift is needed
= Use the Shift key on the opposite side from the typing hand.

Q: Which finger usually types the number 2?
- Left middle finger
- Right ring finger
* Left ring finger
- Left little finger
= The left ring finger rests on S and reaches up to 2.

Q: Which sentence is spaced correctly?
- Please come at 9 , bring your ID .
* Please come at 9, bring your ID.
- Please come at 9,bring your ID.
- Please come at 9 ,bring your ID .
= Put one space after punctuation and none before it.

Q: What should come first when you practise typing?
- Maximum speed
* Accuracy at a comfortable pace
- Looking at the keys
- Long sessions once a week
= Accuracy first; speed follows from smooth, correct movements.

Q: What is a "word" in a standard typing test?
- Any group of letters
* Five characters, including spaces
- Ten letters
- A sentence
= Five-character words allow fair comparison between texts.

Q: You typed 900 characters in 3 minutes. What is your gross WPM?
- 30
* 60
- 90
- 300
= 900 / 5 = 180 words; 180 / 3 = 60 WPM.

Q: You typed 800 characters and 40 were wrong. What is your accuracy?
- 90%
- 92%
* 95%
- 96%
= (800 - 40) / 800 = 760 / 800 = 95%.

Q: Which habit helps build rhythm?
- Typing in sudden bursts and pausing
* Pressing keys at an even tempo
- Pressing the keys as hard as possible
- Staring at your hands
= An even, steady pace gives smoother and faster typing.

Q: Which is a safe way to use free typing practice tools?
- Download any program that promises speed
* Use trusted browser-based lessons and enter no personal details
- Pay before trying
- Share your passwords
= Practice needs only a keyboard; be careful with downloads and personal information.

Q: Which shortcut pastes copied text?
- Ctrl+C
- Ctrl+X
* Ctrl+V
- Ctrl+Z
= Ctrl+V pastes. (Mac: Command+V.)

Q: Which shortcut undoes your last action in most programs?
- Ctrl+S
* Ctrl+Z
- Ctrl+A
- Ctrl+P
= Ctrl+Z undoes and Ctrl+Y redoes.

Q: Which shortcut saves a file?
* Ctrl+S
- Ctrl+F
- Ctrl+N
- Ctrl+W
= Ctrl+S (Mac: Command+S) saves.

Q: Which keys show the Windows desktop quickly?
- Alt+Tab
* Windows+D
- Ctrl+Alt+Delete only
= Windows+D hides all windows to show the desktop, and pressing it again brings them back.

Q: How do you move between open programs?
- Press Esc twice
- Press Ctrl+P
* Hold Alt and tap Tab
- Press Shift+Enter
= Alt+Tab (Mac: Command+Tab) switches windows.

Q: Which shortcut jumps the cursor to the end of a Word document?
- Ctrl+Home
- Home
* Ctrl+End
- Page Up
= Ctrl+End goes to the end; Ctrl+Home goes to the start.

Q: How can you open the emoji and symbols panel on Windows 10 or 11?
- Alt+F4
* Windows key plus the full stop key
- Ctrl+Shift+Esc
- Caps Lock twice
= Windows+. opens a panel of emoji and symbols.

Q: Which are good ways to speed up typing on a phone? (choose all that apply)
* Using predictive text suggestions
* Using swipe typing if your keyboard offers it
- Turning off all keyboard features
= Predictive text and swipe typing save taps, but always read before sending.

Q: What must you always do after using voice typing?
- Nothing; it is perfect
* Proofread the text for mistakes
- Delete it
- Say your password aloud
= Voice typing makes mistakes, especially with names.

Q: Which email has the best opening for a job application?
- "hey, need job"
* "Dear Hiring Manager, I am writing to apply for the position of Data Entry Clerk."
- "URGENT!!! READ NOW"
- "Hi"
= It is polite, clear and states the purpose.

Q: In data entry from a paper form, what is most important?
- Typing as fast as possible
* Accuracy and matching the source exactly
- Changing things you think are wrong
- Skipping difficult lines
= Wrong data can cause real problems, so accuracy comes first.

Q: Which proofreading step catches errors that spell-check misses?
- Switching off the screen
* Reading slowly and checking names, numbers and dates against the source
- Making the font bigger only
- Reading once quickly
= Spell-check cannot tell that a real word is in the wrong place.

Q: Which advice helps prevent strain injuries? (choose all that apply)
* Take short breaks and stretch regularly
* Keep your wrists straight and use a light touch
- Type for three hours without stopping
= Regular breaks, good posture and light key presses protect your hands and body.

Q: A job advert asks for a typing test of 40 WPM with 95 percent accuracy. You scored 45 gross WPM with 10 uncorrected errors in 5 minutes and 97 percent accuracy. What is your net WPM, and do you meet the standard?
- 35; no
* 43; yes
- 45; no
- 47; yes
= Net WPM = 45 - (10 / 5) = 43, which is above 40, and 97 percent is above 95 percent.
QUIZ,
];
