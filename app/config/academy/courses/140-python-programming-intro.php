<?php
return [
    'slug'        => 'python-programming-intro',
    'title'       => 'Introduction to Programming with Python',
    'category'    => 'coding',
    'level'       => 'intermediate',
    'icon'        => 'code',
    'sort'        => 140,
    'summary'     => 'Learn to think like a programmer and write real Python: variables, decisions, loops, lists, dictionaries, functions, files and small projects such as a quiz game and an expense tracker.',
    'description' => '<p>Programming is the skill of giving a computer clear, step-by-step instructions. With it you can automate boring tasks, build tools for your business or community, analyse data and, one day, create apps. <strong>Python</strong> is one of the best languages to start with because its code is short and almost reads like English, yet it is used by professionals in many fields.</p><p>This course takes you from "what is a program?" to building your own small projects. Every idea is explained in plain language, then shown in code that you type, run and change yourself. You will meet errors early and learn to read them calmly, because every programmer, however experienced, meets them every day.</p><p>You do not need any earlier programming knowledge. You do need to be comfortable using a computer, installing a program, and working patiently, one small step at a time.</p>',
    'outcomes'    => [
        'Explain what a program and an algorithm are, and plan a solution with a flowchart.',
        'Install Python, write a program in an editor and run it.',
        'Use variables, strings, numbers and user input to build interactive programs.',
        'Make programs decide with if, elif, else and the logical operators.',
        'Repeat work with while loops and for loops.',
        'Store and process data with lists, tuples, dictionaries and sets.',
        'Write your own functions and use modules from the standard library.',
        'Read and write text files, debug your code and complete a small project of your own.',
    ],
    'requirements'=> [
        'A computer or laptop where you can install Python (Windows, Mac or Linux)',
        'Basic computer skills: files, folders, typing',
        'No programming experience needed',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Python.org: the official Python website', 'url' => 'https://www.python.org/'],
        ['title' => 'Python downloads', 'url' => 'https://www.python.org/downloads/'],
        ['title' => 'The Python Tutorial (official documentation)', 'url' => 'https://docs.python.org/3/tutorial/'],
        ['title' => 'Git: version control', 'url' => 'https://git-scm.com/'],
    ],
    'modules' => [
        [
            'title'   => 'Module 1: Thinking like a programmer',
            'summary' => 'Learn what programming is, plan with algorithms and flowcharts, install Python and run your first programs.',
            'lessons' => [
                [
                    'title'   => 'What is programming? Algorithms and flowcharts',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what a program is, break a problem into clear steps, and draw those steps as a flowchart. These habits matter more than any language: a programmer who can think in steps can learn any language.</p>

<h3>What is a program?</h3>
<p>A computer does exactly what it is told, quickly and without getting tired, but it cannot guess what you mean. A <strong>program</strong> is a list of instructions written in a <strong>programming language</strong>, a special language the computer can follow. A person who writes programs is a <strong>programmer</strong> or <strong>developer</strong>. Programs run your phone apps, mobile money, school results systems and the websites you visit.</p>

<h3>Algorithms: recipes for solving problems</h3>
<p>Before writing code, you need an <strong>algorithm</strong>: a clear sequence of steps that solves a problem. You already use algorithms every day. Think of making tea:</p>
<ol>
<li>Boil water.</li>
<li>Put tea leaves in a cup.</li>
<li>Pour in the hot water.</li>
<li>Wait three minutes.</li>
<li>Add sugar or milk if you like.</li>
</ol>
<p>A good algorithm is <strong>clear</strong> (no step is confusing), <strong>ordered</strong> (the steps happen in the right order), and <strong>finite</strong> (it ends). Here is a more computer-like one: finding out whether a person can vote in Uganda at 18 or older.</p>
<ol>
<li>Ask for the person's age.</li>
<li>If the age is 18 or more, say "You can vote."</li>
<li>Otherwise, say "You are too young to vote."</li>
</ol>

<h3>Flowcharts</h3>
<p>A <strong>flowchart</strong> shows an algorithm as a picture. Ovals mark the start and end, rectangles mark actions, and diamonds mark decisions with yes and no paths. Here is the voting algorithm in text form:</p>
<pre>
(START)
   |
[Ask for age]
   |
&lt;Is age 18 or more?&gt;
   |-- Yes --&gt; [Say "You can vote."] --\
   |                                      \
   |-- No ---&gt; [Say "Too young."] -------&gt; (END)
</pre>
<p>Draw flowcharts on paper before you code. They help you see the whole problem and find mistakes early.</p>

<h3>The programmer's habit: divide and conquer</h3>
<p>Large problems are solved by splitting them into small ones. To build a program that calculates a shop bill, first solve: how do I read one price? how do I add prices? how do I show the total? Solve each small piece, then join them.</p>

<h3>Try it yourself</h3>
<ol>
<li>Write an algorithm of five or more steps for a task such as "send money using mobile money" or "wash clothes".</li>
<li>Give it to a friend and see whether they can follow it exactly without asking questions. Where did they get stuck?</li>
<li>Draw a flowchart on paper for this problem: ask the user for a number and say whether it is even or odd.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A program is a list of instructions in a language the computer understands.</li>
<li>An algorithm is a clear, ordered, finite set of steps that solves a problem.</li>
<li>Flowcharts show algorithms with ovals, rectangles and diamonds.</li>
<li>Break big problems into small ones and plan before you code.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Installing Python and running your first program',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will have Python installed and you will have run your own program. Setting up can feel like the hardest part for beginners. Take it slowly; you only do it once.</p>

<h3>Install Python</h3>
<ol>
<li>Open a browser and go to the official site, <strong>python.org</strong>, and open the <strong>Downloads</strong> page.</li>
<li>Download the latest version of <strong>Python 3</strong> for your system.</li>
<li>Run the installer. On Windows, on the first screen <strong>tick the box "Add python.exe to PATH"</strong>. This lets the computer find Python from any folder. Then choose <strong>Install Now</strong>.</li>
<li>When it finishes, close the installer.</li>
</ol>
<p>Always download from python.org, not from unknown sites, to stay safe. To check that it worked, open the Start menu, type <code>cmd</code> and press Enter. In the black window type:</p>
<pre>
python --version
</pre>
<p>You should see something like <code>Python 3.12.4</code> (your number may differ). On some computers the command is <code>py --version</code> or, on Mac and Linux, <code>python3 --version</code>.</p>

<h3>Choose an editor</h3>
<p>Python comes with a simple editor called <strong>IDLE</strong>. Search for "IDLE" in the Start menu. It has two parts. The <strong>shell</strong> window shows the prompt <code>&gt;&gt;&gt;</code> where you can try one line at a time. The <strong>editor</strong> window (File, then New File) is for programs you want to save. Many people later move to a bigger editor such as VS Code, but IDLE is enough for this course.</p>

<h3>Try the shell</h3>
<p>Type this in the shell and press Enter:</p>
<pre>
&gt;&gt;&gt; 5 + 3
8
&gt;&gt;&gt; print("Hello, Uganda!")
Hello, Uganda!
</pre>
<p>The shell works like a calculator that understands Python. It is perfect for experiments.</p>

<h3>Write and run a program file</h3>
<ol>
<li>In IDLE choose <strong>File &gt; New File</strong>.</li>
<li>Type these lines:</li>
</ol>
<pre><code>print("Hello, world!")
print("My name is Akello and I am learning Python.")</code></pre>
<ol>
<li>Choose <strong>File &gt; Save</strong> and save it as <code>hello.py</code> in a folder you can find, for example <code>python-course</code> in Documents.</li>
<li>Press <code>F5</code> (or <strong>Run &gt; Run Module</strong>).</li>
</ol>
<p>In the shell window you will see:</p>
<pre>
Hello, world!
My name is Akello and I am learning Python.
</pre>
<p>You can also run a saved program from Command Prompt: open the folder, then type <code>python hello.py</code>. Python files end with <code>.py</code>.</p>

<h3>Common mistakes</h3>
<ul>
<li>Typing a capital letter: <code>Print</code> is not the same as <code>print</code>.</li>
<li>Forgetting to save before running.</li>
<li>Using curly "smart" quotes copied from a document. Type straight quotes <code>"</code> yourself.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Install Python and confirm the version in Command Prompt.</li>
<li>Open IDLE and calculate your age in months in the shell.</li>
<li>Write a program <code>about_me.py</code> that prints three lines about you, and run it with <code>F5</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Install Python 3 from python.org and tick "Add python.exe to PATH" on Windows.</li>
<li>The shell is for quick experiments; files ending in <code>.py</code> store programs.</li>
<li>Press <code>F5</code> in IDLE to run a saved program.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'print(), comments and text',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to display messages in several ways and leave notes in your code. <code>print()</code> is the first tool every Python programmer learns, and it remains useful for years, even when you are an expert.</p>

<h3>Printing text</h3>
<p>The word <code>print</code> followed by brackets is a <strong>function</strong>: a named piece of code that does a job. The value inside the brackets is what the function works with. Text is placed in quotes. Text in quotes is called a <strong>string</strong>. Single or double quotes both work, but be consistent.</p>
<pre><code>print("Welcome to LAF Academy")
print('Single quotes work too')
print("It's easy to include an apostrophe inside double quotes")</code></pre>
<pre>
Welcome to LAF Academy
Single quotes work too
It's easy to include an apostrophe inside double quotes
</pre>
<p>Numbers do not need quotes: <code>print(2026)</code> shows <code>2026</code>. Calculations are done first: <code>print(12 * 5)</code> shows <code>60</code>.</p>

<h3>Printing several things</h3>
<p>Separate items with commas. Python puts a space between them:</p>
<pre><code>print("Total:", 5000, "UGX")
print("Gulu", "Kampala", "Mbarara", sep=" - ")
print("Loading", end="...")
print("done")</code></pre>
<pre>
Total: 5000 UGX
Gulu - Kampala - Mbarara
Loading...done
</pre>
<p>The option <code>sep</code> changes the separator, and <code>end</code> changes what is printed at the end (normally a new line).</p>

<h3>Special characters</h3>
<p>Inside a string, a backslash begins an <strong>escape sequence</strong>:</p>
<table>
<thead><tr><th>Code</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td><code>\n</code></td><td>New line</td></tr>
<tr><td><code>\t</code></td><td>Tab (a wide space)</td></tr>
<tr><td><code>\"</code></td><td>A double quote inside double quotes</td></tr>
<tr><td><code>\\</code></td><td>One backslash</td></tr>
</tbody>
</table>
<pre><code>print("Name:\tAkello\nTown:\tGulu")</code></pre>
<pre>
Name:	Akello
Town:	Gulu
</pre>
<p>For text over several lines, use triple quotes: <code>print("""Line one</code> then a new line, <code>Line two""")</code>.</p>

<h3>Comments</h3>
<p>A <strong>comment</strong> starts with <code>#</code>. Python ignores everything after it on that line. Comments explain <em>why</em> you did something, to other people and to yourself next month.</p>
<pre><code># This program greets the learner
print("Hello!")   # a comment can follow code too</code></pre>
<p>A common trick is to put <code>#</code> in front of a line to switch it off for a while while testing.</p>

<h3>Try it yourself</h3>
<ol>
<li>Write a program that prints a small timetable of three lines using <code>\t</code> to line up the columns.</li>
<li>Use <code>sep</code> to print your three favourite foods separated by commas.</li>
<li>Add a comment at the top that explains what the program does, then "comment out" one print line and run again.</li>
</ol>

<h3>Key points</h3>
<ul>
<li><code>print()</code> shows values on the screen; text goes inside quotes.</li>
<li>Use commas to print several values, and <code>sep</code> and <code>end</code> to control layout.</li>
<li><code>\n</code> starts a new line and <code>\t</code> inserts a tab.</li>
<li>Comments begin with <code>#</code> and are ignored by Python.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Errors and how to read them',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will not be frightened by red error messages. You will be able to read them and fix the problem. Beginners often think an error means they are bad at programming. It does not. Errors are Python's way of telling you exactly what to fix.</p>

<h3>Kinds of errors</h3>
<table>
<thead><tr><th>Kind</th><th>When it happens</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Syntax error</td><td>Before the program runs; the code breaks the rules of the language</td><td>A missing bracket or quote</td></tr>
<tr><td>Runtime error</td><td>While the program runs; something impossible is asked</td><td>Dividing by zero</td></tr>
<tr><td>Logic error</td><td>The program runs but gives wrong answers</td><td>Using + when you meant -</td></tr>
</tbody>
</table>

<h3>A syntax error</h3>
<pre><code>print("Hello"</code></pre>
<pre>
  File "demo.py", line 1
    print("Hello"
         ^
SyntaxError: '(' was never closed
</pre>
<p>The message names the file and line, points to the place, and says what is wrong. The exact wording differs between Python versions, but the idea is the same.</p>

<h3>A runtime error and the traceback</h3>
<pre><code>price = 2500
print(totl)</code></pre>
<pre>
Traceback (most recent call last):
  File "demo.py", line 2, in &lt;module&gt;
    print(totl)
NameError: name 'totl' is not defined
</pre>
<p>This is a <strong>traceback</strong>. <strong>Read it from the bottom.</strong> The last line gives the error type (<code>NameError</code>) and the reason (the name <code>totl</code> was never created; it is a spelling mistake). The lines above tell you where: file, line number and the code.</p>

<h3>Common error types</h3>
<ul>
<li><code>SyntaxError</code>: broken grammar, such as missing brackets, quotes or colons.</li>
<li><code>IndentationError</code>: the spaces at the start of a line are wrong.</li>
<li><code>NameError</code>: you used a name that does not exist, often a typing slip.</li>
<li><code>TypeError</code>: the wrong kind of value, such as <code>"Age: " + 25</code>, which says you can only join text to text.</li>
<li><code>ValueError</code>: right kind but unsuitable value, such as <code>int("abc")</code>.</li>
<li><code>ZeroDivisionError</code>: you divided by zero.</li>
</ul>

<h3>A calm way to fix errors</h3>
<ol>
<li>Read the last line of the message first.</li>
<li>Go to the line number it shows. The real mistake can sometimes be on the line just before.</li>
<li>Check spelling, capital letters, brackets, quotes and colons.</li>
<li>Fix <strong>one</strong> thing, then run again.</li>
<li>If you are stuck, copy the error message into a search engine; thousands of people have met it already.</li>
</ol>

<h3>Try it yourself</h3>
<ol>
<li>Type <code>print("Hello)</code> (missing closing quote), run it and read the message.</li>
<li>Type <code>print(10 / 0)</code> and find the error type in the last line.</li>
<li>Type <code>print("Age: " + 25)</code> and explain the message in your own words.</li>
<li>Fix each program so it runs correctly.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Syntax, runtime and logic errors are the three main kinds.</li>
<li>Read a traceback from the bottom: the type and reason come first.</li>
<li>The line number shows where Python noticed the problem.</li>
<li>Change one thing at a time and run again.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is an algorithm?
- A type of computer chip
* A clear, ordered set of steps that solves a problem
- A programming language
- An error message
= An algorithm is a recipe: finite steps in a sensible order.

Q: In a flowchart, which shape is used for a decision with yes and no paths?
- Oval
- Rectangle
* Diamond
- Circle
= Diamonds ask a question. Rectangles show actions, and ovals mark the start and end.

Q: Which command checks the installed Python version in Command Prompt?
- python --help-me
* python --version
- install python
- version python
= python --version prints the version. On some systems you may need py or python3.

Q: Which line prints  Hello  on the screen?
- show("Hello")
- print(Hello)
* print("Hello")
- say "Hello"
= print is lower case, and text must be inside quotes.

Q: What does this print?   print("A", "B", sep="-")
- A B
* A-B
- AB-
- A,B
= sep changes the separator placed between the items.

Q: What is a comment in Python?
- A line starting with // that runs slowly
- A message that Python shows to the user
* Text after a hash symbol that Python ignores
- A function that prints text
= Comments start with # and are for human readers.

Q: A traceback ends with  NameError: name 'total' is not defined.  What is the most likely cause?
- The computer is out of memory
- Python is not installed
* The name total was misspelled or never created
- The file is too long
= NameError means Python met a name it does not know. Check spelling and whether the variable was created first.
QUIZ,
        ],
        [
            'title'   => 'Module 2: Data and operators',
            'summary' => 'Store information in variables, work with strings and numbers, ask the user for input and compare values.',
            'lessons' => [
                [
                    'title'   => 'Variables and names',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to store information in variables and change it. Variables are the memory of a program: without them it could not remember a name, a price or a score.</p>

<h3>What is a variable?</h3>
<p>A <strong>variable</strong> is a name that points to a value stored in the computer's memory. Think of a labelled box: the label is the name and the contents are the value. You create a variable with the <strong>assignment</strong> sign <code>=</code>:</p>
<pre><code>name = "Akello"
age = 17
price = 2500
print(name)
print(age)
print(price)</code></pre>
<pre>
Akello
17
2500
</pre>
<p>Read <code>age = 17</code> as "age becomes 17". The equals sign here does not mean "is equal to"; it means "store this value under this name". The part on the right is worked out first.</p>

<h3>Changing a variable</h3>
<p>The value can change while the program runs, which is why it is called a <em>variable</em>:</p>
<pre><code>balance = 10000
print(balance)
balance = balance + 5000
print(balance)</code></pre>
<pre>
10000
15000
</pre>
<p>In <code>balance = balance + 5000</code> Python first calculates the right side (10000 + 5000) and then stores the result back in <code>balance</code>. You can store several values on one line, and swap two values easily:</p>
<pre><code>x, y = 3, 8
x, y = y, x
print(x, y)</code></pre>
<pre>
8 3
</pre>

<h3>Rules for names</h3>
<ul>
<li>Use letters, digits and the underscore <code>_</code>. No spaces or symbols such as <code>-</code> or <code>$</code>.</li>
<li>Do not start with a digit: <code>2nd_place</code> is not allowed.</li>
<li>Names are <strong>case sensitive</strong>: <code>Score</code> and <code>score</code> are different variables.</li>
<li>Do not use Python's reserved words such as <code>if</code>, <code>for</code>, <code>while</code> or <code>True</code>.</li>
</ul>

<h3>Choosing good names</h3>
<p>Python programmers write names in lower case with underscores: <code>student_name</code>, <code>total_cost</code>. Choose names that explain themselves. Compare <code>t = p * q</code> with <code>total_cost = unit_price * quantity</code>. The second is far easier to understand next week. Avoid names like <code>list</code> or <code>print</code> that already mean something in Python.</p>

<h3>Common mistakes</h3>
<ul>
<li>Using a variable before giving it a value (a <code>NameError</code>).</li>
<li>Typing <code>Total</code> in one place and <code>total</code> in another.</li>
<li>Putting quotes around a variable name, which turns it into text: <code>print("name")</code> shows the word name, not the value.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Create variables for your first name, your age and the town where you live, then print a line using all three.</li>
<li>Create <code>airtime = 5000</code>, subtract <code>1500</code> for a call, and print the new balance.</li>
<li>Swap two variables using the one-line method and print the result.</li>
<li>Write one invalid name on purpose (for example <code>my-name = 5</code>) and read the error.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A variable is a name for a stored value; <code>=</code> assigns it.</li>
<li>The right side is calculated first, so <code>x = x + 1</code> makes sense.</li>
<li>Names use letters, digits and underscores, and are case sensitive.</li>
<li>Meaningful names make your code easier to read.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Strings, numbers and input()',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to work with text and numbers, ask the user to type something, and convert between types. This is where programs start to feel alive: they can respond to a person.</p>

<h3>Data types</h3>
<p>Every value has a <strong>type</strong>. Check it with <code>type()</code>.</p>
<table>
<thead><tr><th>Type</th><th>Meaning</th><th>Example</th></tr></thead>
<tbody>
<tr><td><code>str</code></td><td>String: text</td><td><code>"Gulu"</code></td></tr>
<tr><td><code>int</code></td><td>Integer: whole number</td><td><code>42</code></td></tr>
<tr><td><code>float</code></td><td>Number with a decimal point</td><td><code>3.14</code></td></tr>
<tr><td><code>bool</code></td><td>True or False</td><td><code>True</code></td></tr>
</tbody>
</table>
<pre><code>print(type("Gulu"))
print(type(42))
print(type(3.14))</code></pre>
<pre>
&lt;class 'str'&gt;
&lt;class 'int'&gt;
&lt;class 'float'&gt;
</pre>

<h3>Working with strings</h3>
<pre><code>word = "Python"
print(len(word))        # number of characters
print(word.upper())     # capitals
print(word.lower())     # small letters
print(word[0])          # first character (counting starts at 0)
print(word[-1])         # last character
print(word[0:3])        # characters 0, 1 and 2
print("Hi " + "there")  # joining
print("ha" * 3)         # repeating</code></pre>
<pre>
6
PYTHON
python
P
n
Pyt
Hi there
hahaha
</pre>
<p>Other useful methods are <code>.strip()</code> (removes spaces at both ends), <code>.replace("a", "b")</code>, and <code>.title()</code> (capital letter on each word).</p>

<h3>Getting input from the user</h3>
<p>The <code>input()</code> function shows a message, waits for the user to type and press Enter, and gives back what was typed. <strong>It always gives back a string</strong>, even if the user types digits.</p>
<pre><code>name = input("What is your name? ")
print("Hello, " + name + "!")</code></pre>
<pre>
What is your name? Okello
Hello, Okello!
</pre>

<h3>Converting types</h3>
<p>To do arithmetic with typed numbers, convert them with <code>int()</code> or <code>float()</code>. Use <code>str()</code> to turn a number into text.</p>
<pre><code>age_text = input("Your age: ")
age = int(age_text)
print("Next year you will be", age + 1)

print(int("42") + 8)
print(float("2.5") * 2)
print("Score: " + str(90))
print(int(3.9))</code></pre>
<pre>
Your age: 17
Next year you will be 18
50
5.0
Score: 90
3
</pre>
<p>Notice that <code>int(3.9)</code> cuts off the decimal part and gives 3; it does not round. If the text is not a number, such as <code>int("abc")</code>, Python raises a <code>ValueError</code>. You will learn to handle this later.</p>

<h3>Try it yourself</h3>
<ol>
<li>Ask the user for their first name and last name and print them as "Last, First".</li>
<li>Ask for a word and print its length and its first and last letters.</li>
<li>Ask for two numbers, convert them with <code>int()</code> and print their sum.</li>
<li>Type letters instead of a number and look at the error message.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>The main types are <code>str</code>, <code>int</code>, <code>float</code> and <code>bool</code>.</li>
<li>Strings can be measured, sliced, joined and changed with methods.</li>
<li><code>input()</code> always returns text; convert it with <code>int()</code> or <code>float()</code> for calculations.</li>
<li>Counting positions in a string starts at 0.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Arithmetic and f-strings',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to do calculations in Python and display the results neatly inside sentences. Whether you are totalling a shop bill or calculating interest, these are the tools you will use.</p>

<h3>The arithmetic operators</h3>
<table>
<thead><tr><th>Operator</th><th>Meaning</th><th>Example</th><th>Result</th></tr></thead>
<tbody>
<tr><td><code>+</code></td><td>Add</td><td><code>7 + 2</code></td><td>9</td></tr>
<tr><td><code>-</code></td><td>Subtract</td><td><code>7 - 2</code></td><td>5</td></tr>
<tr><td><code>*</code></td><td>Multiply</td><td><code>7 * 2</code></td><td>14</td></tr>
<tr><td><code>/</code></td><td>Divide (always a float)</td><td><code>7 / 2</code></td><td>3.5</td></tr>
<tr><td><code>//</code></td><td>Whole-number division</td><td><code>7 // 2</code></td><td>3</td></tr>
<tr><td><code>%</code></td><td>Remainder (modulus)</td><td><code>7 % 2</code></td><td>1</td></tr>
<tr><td><code>**</code></td><td>Power</td><td><code>2 ** 3</code></td><td>8</td></tr>
</tbody>
</table>
<p>The remainder operator is surprisingly useful. <code>n % 2 == 0</code> tells you that n is even. If you have 25 sweets for 4 children, <code>25 // 4</code> gives 6 each and <code>25 % 4</code> gives 1 left over.</p>

<h3>Order of operations</h3>
<p>Python follows the usual maths order: brackets first, then powers, then multiplication and division, then addition and subtraction.</p>
<pre><code>print(2 + 3 * 4)
print((2 + 3) * 4)
print(10 - 4 - 3)</code></pre>
<pre>
14
20
3
</pre>
<p>When in doubt, add brackets. They also make your intention clear to readers.</p>

<h3>Shortcuts and rounding</h3>
<pre><code>score = 10
score += 5     # same as score = score + 5
score -= 3     # same as score = score - 3
score *= 2
print(score)
print(round(3.14159, 2))</code></pre>
<pre>
24
3.14
</pre>

<h3>f-strings: neat messages</h3>
<p>An <strong>f-string</strong> is a string starting with <code>f</code>. Anything inside curly brackets is replaced by its value. It is much cleaner than joining with <code>+</code> and no <code>str()</code> is needed.</p>
<pre><code>item = "notebook"
price = 2500
quantity = 3
total = price * quantity
print(f"{quantity} x {item} at {price} UGX = {total} UGX")
print(f"Total with separators: {1500000:,}")
print(f"Average: {85.6666:.2f}")</code></pre>
<pre>
3 x notebook at 2500 UGX = 7500 UGX
Total with separators: 1,500,000
Average: 85.67
</pre>
<p>After a colon inside the brackets you can add formatting: <code>:,</code> puts commas between thousands, and <code>:.2f</code> shows two decimal places.</p>

<h3>Example: a simple shop bill</h3>
<pre><code>price = float(input("Price per item (UGX): "))
quantity = int(input("How many? "))
total = price * quantity
print(f"You owe {total:,.0f} UGX")</code></pre>
<pre>
Price per item (UGX): 1500
How many? 4
You owe 6,000 UGX
</pre>

<h3>Try it yourself</h3>
<ol>
<li>Ask for a number of minutes and print how many whole hours and leftover minutes that is, using <code>//</code> and <code>%</code>.</li>
<li>Build a bill program for three items; print each line with an f-string and a final total with commas.</li>
<li>Calculate the area of a circle using <code>3.14159 * radius ** 2</code> with a radius typed by the user, showing two decimals.</li>
</ol>

<h3>Key points</h3>
<ul>
<li><code>/</code> always gives a float; <code>//</code> and <code>%</code> give the whole part and the remainder.</li>
<li>Brackets control the order of calculations.</li>
<li>Shortcuts like <code>+=</code> update a variable in place.</li>
<li>f-strings place values inside text, with formats such as <code>:,</code> and <code>:.2f</code>.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Booleans and comparisons',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to ask yes-or-no questions about your data in code. Every decision a program makes, from checking a password to approving a loan, starts with a true or false answer.</p>

<h3>Booleans</h3>
<p>A <strong>boolean</strong> (type <code>bool</code>) has only two possible values: <code>True</code> and <code>False</code>. Notice the capital first letter. Python produces booleans when you compare things.</p>

<h3>Comparison operators</h3>
<table>
<thead><tr><th>Operator</th><th>Meaning</th><th>Example</th><th>Result</th></tr></thead>
<tbody>
<tr><td><code>==</code></td><td>Equal to</td><td><code>5 == 5</code></td><td>True</td></tr>
<tr><td><code>!=</code></td><td>Not equal to</td><td><code>5 != 5</code></td><td>False</td></tr>
<tr><td><code>&lt;</code></td><td>Less than</td><td><code>3 &lt; 7</code></td><td>True</td></tr>
<tr><td><code>&gt;</code></td><td>Greater than</td><td><code>3 &gt; 7</code></td><td>False</td></tr>
<tr><td><code>&lt;=</code></td><td>Less than or equal</td><td><code>7 &lt;= 7</code></td><td>True</td></tr>
<tr><td><code>&gt;=</code></td><td>Greater than or equal</td><td><code>6 &gt;= 7</code></td><td>False</td></tr>
</tbody>
</table>
<pre><code>age = 17
print(age &gt;= 18)
print(age == 17)
print("apple" == "Apple")
print("kampala" == "kampala")</code></pre>
<pre>
False
True
False
True
</pre>
<p>The most common beginner mistake is mixing up <code>=</code> and <code>==</code>. One equals sign <strong>stores</strong> a value. Two equals signs <strong>compare</strong> two values. Comparing text is case sensitive: <code>"apple"</code> is not equal to <code>"Apple"</code>. To compare without caring about case, convert both to lower case first.</p>

<h3>Checking if something is inside something else</h3>
<p>The word <code>in</code> checks whether one value appears in another:</p>
<pre><code>print("ka" in "Kampala")
print("z" in "Gulu")
print("lu" in "Gulu")</code></pre>
<pre>
False
False
True
</pre>
<p>The first line prints False because <code>"ka"</code> (small letters) is not found in <code>"Kampala"</code>, which begins with a capital K and then has the letters <code>am</code> before <code>pa</code>. Case matters here too.</p>

<h3>Combining conditions: and, or, not</h3>
<table>
<thead><tr><th>Operator</th><th>True when</th></tr></thead>
<tbody>
<tr><td><code>and</code></td><td>both sides are True</td></tr>
<tr><td><code>or</code></td><td>at least one side is True</td></tr>
<tr><td><code>not</code></td><td>it flips True to False and the opposite</td></tr>
</tbody>
</table>
<pre><code>age = 20
has_id = True
print(age &gt;= 18 and has_id)
print(age &lt; 13 or age &gt; 60)
print(not has_id)</code></pre>
<pre>
True
False
False
</pre>

<h3>Try it yourself</h3>
<ol>
<li>Create two variables <code>a = 12</code> and <code>b = 20</code> and print the result of every comparison operator on them.</li>
<li>Ask the user to type a password and print whether it equals the word you chose. Then make it ignore capital letters.</li>
<li>Write a line that prints True only when a number typed by the user is between 1 and 10 (use <code>and</code>).</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Comparisons produce <code>True</code> or <code>False</code>.</li>
<li><code>=</code> stores a value; <code>==</code> compares two values.</li>
<li>Text comparison is case sensitive.</li>
<li><code>and</code>, <code>or</code> and <code>not</code> combine conditions.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is stored in the variable total after these lines?  total = 5   then   total = total + 3
- 5
- 3
* 8
- 53
= Python calculates the right side (5 + 3) and stores the result back in total.

Q: Which of these is a valid variable name?
- 2nd_place
- my-name
- for
* total_cost
= Names cannot start with a digit, contain a hyphen, or be a reserved word like for.

Q: What type of value does input() always return?
- int
- float
* str
- bool
= input() gives back text. Convert it with int() or float() to do arithmetic.

Q: What is the value of 17 // 5 and 17 % 5?
- 3.4 and 2
* 3 and 2
- 2 and 3
- 3 and 3
= // gives the whole-number result (3) and % gives the remainder (2).

Q: What does this print?   print(f"{1234567:,}")
- 1234567
- 1.234567
* 1,234,567
- 1,234,567.00
= The :, format puts commas between groups of three digits.

Q: Which line compares two values rather than storing one?
- score = 10
- score + 10
* score == 10
- score += 10
= == compares and gives True or False. A single = assigns.

Q: What is the result of  not (5 > 3 and 2 > 1)?
* False
- True
- 5
- An error
= Both parts are True, so the and gives True and not flips it to False.

Q: What does  int(3.9)  return?
- 4
* 3
- 3.9
- An error
= int() cuts off the decimal part. It does not round up.
QUIZ,
        ],
        [
            'title'   => 'Module 3: Decisions and loops',
            'summary' => 'Make programs choose between actions and repeat tasks with while and for loops, then combine them in a game.',
            'lessons' => [
                [
                    'title'   => 'Making decisions with if, elif and else',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to make your programs choose what to do. Real programs do not run the same way every time: they react to a password, a score, a choice from a menu or the balance on an account.</p>

<h3>The if statement</h3>
<pre><code>age = int(input("Enter your age: "))
if age &gt;= 18:
    print("You are an adult.")
print("Thank you.")</code></pre>
<p>Three things matter here. The condition (<code>age &gt;= 18</code>) is followed by a <strong>colon</strong>. The lines that belong to the <code>if</code> are <strong>indented</strong> (moved right by four spaces; IDLE and VS Code do this when you press Tab). The last line is not indented, so it always runs. In Python, indentation is not decoration; it is how the language knows which lines belong together.</p>
<pre>
Enter your age: 20
You are an adult.
Thank you.
</pre>

<h3>else and elif</h3>
<p><code>else</code> runs when the condition is False. <code>elif</code> (short for "else if") adds more conditions. Python checks from top to bottom and runs only the <strong>first</strong> block that matches.</p>
<pre><code>score = int(input("Enter your score (0-100): "))
if score &gt;= 80:
    grade = "Distinction"
elif score &gt;= 60:
    grade = "Credit"
elif score &gt;= 40:
    grade = "Pass"
else:
    grade = "Fail"
print(f"Your result: {grade}")</code></pre>
<pre>
Enter your score (0-100): 72
Your result: Credit
</pre>
<p>If the score is 72, the first test fails, the second passes, and Python skips the rest. The order matters. If you tested <code>score &gt;= 40</code> first, every passing student would get "Pass".</p>

<h3>Combining conditions</h3>
<pre><code>age = 15
has_permission = True
if age &gt;= 18 or (age &gt;= 13 and has_permission):
    print("Access granted")
else:
    print("Access denied")</code></pre>
<pre>
Access granted
</pre>

<h3>Nested decisions</h3>
<p>You can place an <code>if</code> inside another, with one more level of indentation. Keep this simple; too much nesting is hard to read.</p>
<pre><code>password = input("Password: ")
if len(password) &gt;= 8:
    if password.isdigit():
        print("Strong length but use letters too.")
    else:
        print("Accepted.")
else:
    print("Too short.")</code></pre>

<h3>Common mistakes</h3>
<ul>
<li>Forgetting the colon at the end of <code>if</code>, <code>elif</code> or <code>else</code>.</li>
<li>Using <code>=</code> instead of <code>==</code> in a condition.</li>
<li>Wrong indentation (an <code>IndentationError</code>) or mixing tabs and spaces.</li>
<li>Putting conditions in the wrong order.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Ask for a number and print whether it is positive, negative or zero.</li>
<li>Ask for the time (0 to 23) and print "Good morning", "Good afternoon" or "Good evening".</li>
<li>Write a ticket price program: children under 12 pay 2000 UGX, ages 12 to 59 pay 5000 UGX, and 60 and above pay 2500 UGX.</li>
</ol>

<h3>Key points</h3>
<ul>
<li><code>if</code>, <code>elif</code> and <code>else</code> choose which block of code runs.</li>
<li>A colon ends each condition line, and the block below is indented.</li>
<li>Python runs only the first block whose condition is True.</li>
<li>Use <code>and</code>, <code>or</code> and brackets for complex conditions.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Repeating with while loops',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to repeat actions with a <code>while</code> loop and stop it when needed. Without loops, you would have to write the same line a hundred times to count to a hundred. Loops let a few lines do thousands of jobs.</p>

<h3>The while loop</h3>
<p>A <code>while</code> loop repeats its block <strong>as long as a condition is True</strong>.</p>
<pre><code>count = 1
while count &lt;= 5:
    print("Count:", count)
    count += 1
print("Finished")</code></pre>
<pre>
Count: 1
Count: 2
Count: 3
Count: 4
Count: 5
Finished
</pre>
<p>Python checks the condition, runs the block, then goes back and checks again. When <code>count</code> becomes 6 the condition is False and the loop ends. The line <code>count += 1</code> is essential. Without it the condition would never become False and the program would run for ever (an <strong>infinite loop</strong>). If this happens, press <code>Ctrl+C</code> to stop it.</p>

<h3>Loops with input</h3>
<p>Loops are ideal for asking again until the answer is acceptable:</p>
<pre><code>pin = ""
while pin != "1234":
    pin = input("Enter the PIN: ")
print("Welcome!")</code></pre>
<pre>
Enter the PIN: 0000
Enter the PIN: 1234
Welcome!
</pre>

<h3>break: leave the loop now</h3>
<p>A common pattern is <code>while True</code>, a loop that never ends by itself, with <code>break</code> to leave when you decide:</p>
<pre><code>total = 0
while True:
    number = int(input("Enter a number (0 to stop): "))
    if number == 0:
        break
    total += number
print("Total:", total)</code></pre>
<pre>
Enter a number (0 to stop): 5
Enter a number (0 to stop): 10
Enter a number (0 to stop): 0
Total: 15
</pre>

<h3>continue: skip to the next round</h3>
<p><code>continue</code> ends the current round and goes straight to the next check.</p>
<pre><code>n = 0
while n &lt; 10:
    n += 1
    if n % 2 == 0:
        continue
    print(n)</code></pre>
<pre>
1
3
5
7
9
</pre>
<p>Even numbers trigger <code>continue</code>, so they are never printed.</p>

<h3>Limiting attempts</h3>
<pre><code>attempts = 0
while attempts &lt; 3:
    word = input("Type the secret word: ")
    attempts += 1
    if word == "python":
        print("Correct!")
        break
else:
    print("No more attempts.")</code></pre>
<p>A <code>while</code> loop can have an <code>else</code> block. It runs only when the loop ended normally (the condition became False) and not because of a <code>break</code>.</p>

<h3>Try it yourself</h3>
<ol>
<li>Print the numbers 10 down to 1, then "Lift off!".</li>
<li>Write a program that keeps asking for a name until the user types something that is not empty.</li>
<li>Add up prices typed by the user until they type 0, then print the total with commas.</li>
</ol>

<h3>Key points</h3>
<ul>
<li><code>while</code> repeats as long as its condition is True.</li>
<li>Change something inside the loop so the condition can become False.</li>
<li><code>break</code> leaves the loop; <code>continue</code> skips to the next round.</li>
<li>Press <code>Ctrl+C</code> to stop an infinite loop.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'for loops, range() and nested loops',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to repeat a task a known number of times, or once for each item in a group. When you know in advance how many times to repeat, a <code>for</code> loop is neater and safer than a <code>while</code> loop.</p>

<h3>for with range()</h3>
<p>The <code>range()</code> function creates a sequence of whole numbers. The numbers start at 0 by default and <strong>stop before the end number</strong>.</p>
<pre><code>for i in range(5):
    print(i)</code></pre>
<pre>
0
1
2
3
4
</pre>
<p>The variable <code>i</code> takes each value in turn. You can give a start, an end and a step:</p>
<table>
<thead><tr><th>Code</th><th>Produces</th></tr></thead>
<tbody>
<tr><td><code>range(5)</code></td><td>0, 1, 2, 3, 4</td></tr>
<tr><td><code>range(1, 6)</code></td><td>1, 2, 3, 4, 5</td></tr>
<tr><td><code>range(0, 11, 2)</code></td><td>0, 2, 4, 6, 8, 10</td></tr>
<tr><td><code>range(10, 0, -3)</code></td><td>10, 7, 4, 1</td></tr>
</tbody>
</table>
<p>A multiplication table is a perfect first example:</p>
<pre><code>for number in range(1, 6):
    print(f"7 x {number} = {7 * number}")</code></pre>
<pre>
7 x 1 = 7
7 x 2 = 14
7 x 3 = 21
7 x 4 = 28
7 x 5 = 35
</pre>

<h3>for over text and other groups</h3>
<p>A <code>for</code> loop can walk through the characters of a string, or through any group of items:</p>
<pre><code>for letter in "Gulu":
    print(letter)

total = 0
for price in [1500, 2500, 1000]:
    total += price
print("Total:", total)</code></pre>
<pre>
G
u
l
u
Total: 5000
</pre>

<h3>break and continue in for loops</h3>
<pre><code>for n in range(1, 11):
    if n == 4:
        continue
    if n == 7:
        break
    print(n, end=" ")</code></pre>
<pre>
1 2 3 5 6
</pre>
<p>The number 4 is skipped, and the loop stops completely at 7.</p>

<h3>Nested loops</h3>
<p>A loop inside another loop is called a <strong>nested loop</strong>. The inner loop runs fully for every single round of the outer loop.</p>
<pre><code>for row in range(1, 4):
    for col in range(1, 4):
        print(row * col, end=" ")
    print()</code></pre>
<pre>
1 2 3
2 4 6
3 6 9
</pre>
<p>The empty <code>print()</code> at the end of each outer round moves to a new line. Here is a pattern of stars:</p>
<pre><code>for i in range(1, 5):
    print("*" * i)</code></pre>
<pre>
*
**
***
****
</pre>

<h3>for or while?</h3>
<p>Use <code>for</code> when you know how many repeats you need, or you have a group of items to process. Use <code>while</code> when you repeat until something happens, such as the user typing the right answer.</p>

<h3>Try it yourself</h3>
<ol>
<li>Print all even numbers from 2 to 20.</li>
<li>Ask for a number and print its multiplication table from 1 to 12.</li>
<li>Print a triangle of stars with 6 rows.</li>
<li>Add up the numbers from 1 to 100 using a loop and check that the answer is 5050.</li>
</ol>

<h3>Key points</h3>
<ul>
<li><code>range(a, b, step)</code> counts from a up to, but not including, b.</li>
<li><code>for</code> loops work on ranges, strings and other groups.</li>
<li><code>break</code> and <code>continue</code> work in <code>for</code> loops too.</li>
<li>In nested loops, the inner loop completes for every round of the outer one.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Project: a number-guessing game',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>In this lesson you will combine variables, input, decisions and loops into a complete game. Building something that works is the best way to turn separate ideas into skill. After this lesson you will be able to plan, write and improve a small program step by step.</p>

<h3>The plan</h3>
<ol>
<li>The computer picks a secret number from 1 to 20.</li>
<li>The player has 5 tries.</li>
<li>After each guess the computer says "Too low" or "Too high".</li>
<li>If the player guesses correctly, they win and the game stops.</li>
<li>If the tries run out, the computer reveals the number.</li>
</ol>

<h3>Version 1: the core game</h3>
<p>We use the <code>random</code> module, which belongs to Python's standard library. The line <code>import random</code> makes it available, and <code>random.randint(1, 20)</code> gives a random whole number from 1 up to and including 20.</p>
<pre><code>import random

secret = random.randint(1, 20)
max_tries = 5
tries = 0

print("I am thinking of a number from 1 to 20.")
print(f"You have {max_tries} tries.")

while tries &lt; max_tries:
    guess = int(input("Your guess: "))
    tries += 1
    if guess &lt; secret:
        print("Too low.")
    elif guess &gt; secret:
        print("Too high.")
    else:
        print(f"Correct! You won in {tries} tries.")
        break
else:
    print(f"Sorry, the number was {secret}.")</code></pre>
<p>An example run (the secret was 13):</p>
<pre>
I am thinking of a number from 1 to 20.
You have 5 tries.
Your guess: 10
Too low.
Your guess: 15
Too high.
Your guess: 13
Correct! You won in 3 tries.
</pre>
<p>Check the <code>while ... else</code> at the bottom: the <code>else</code> block runs only when the loop finished without <code>break</code>, which means the player used all the tries.</p>

<h3>Version 2: protect against bad input</h3>
<p>If the player types a word, <code>int()</code> crashes the game. The string method <code>.isdigit()</code> returns True when a string contains only digits. Replace the guess lines with:</p>
<pre><code>    text = input("Your guess: ")
    if not text.isdigit():
        print("Please type a whole number.")
        continue
    guess = int(text)
    tries += 1</code></pre>
<p>Here <code>continue</code> jumps back to the top of the loop without using up a try. Note that <code>tries += 1</code> now comes after the check, so wrong input is not counted.</p>

<h3>Version 3: ideas to extend</h3>
<ul>
<li>Ask "Play again? (y/n)" and wrap the whole game in another loop.</li>
<li>Let the player choose a difficulty that changes the range and the number of tries.</li>
<li>Keep a score across rounds.</li>
<li>Give a hint such as "very close" when the guess is within 2 of the secret, using <code>abs(guess - secret) &lt;= 2</code>.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Type Version 1 into a file called <code>guess.py</code>, run it and play three times.</li>
<li>Add the Version 2 protection and test it by typing letters.</li>
<li>Change the range to 1-50 and the number of tries to 7. Does the game still make sense?</li>
<li>Add one extension from the list.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Plan in plain steps first, then build one small working version, then improve it.</li>
<li><code>import random</code> and <code>random.randint()</code> give random numbers.</li>
<li><code>while</code> with <code>break</code> and <code>else</code> handles "stop when won or out of tries".</li>
<li>Always think about what a user might type that you did not expect.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is wrong with this code?   if age >= 18   (followed by an indented line)
- The indentation is not needed
- Nothing, it is correct
* The condition line is missing a colon at the end
- It should use = instead of >=
= Every if, elif, else, while and for line must end with a colon.

Q: A student scores 65. With tests in this order: >= 80, >= 60, >= 40, else, which block runs?
- The first
* The second
- The third
- The else block
= Python runs only the first block whose condition is True. 65 fails the first test and passes the second.

Q: What does  for i in range(2, 8, 2):  print(i)  show?
- 2 3 4 5 6 7
* 2 4 6
- 2 4 6 8
- 0 2 4 6
= range stops before 8, and the step is 2, so the values are 2, 4 and 6.

Q: What stops a loop immediately?
- continue
- pass
* break
- skip
= break leaves the loop at once. continue only skips to the next round.

Q: Why does this loop never end?   n = 1   while n < 5:   print(n)
- Because print is wrong
* Because n never changes, so the condition is always True
- Because 5 is too small
- Because while needs an else
= Without something like n += 1 inside the loop, the condition stays True for ever. Use Ctrl+C to stop it.

Q: What does the nested loop below print?   for i in range(2): for j in range(3): print("x", end="")
- xx
- xxx
* xxxxxx
- x
= The inner loop prints x three times for each of the two rounds of the outer loop, so six x characters.

Q: Which function creates a random whole number from 1 to 6 inclusive?
- random.number(6)
- random.dice(1, 6)
* random.randint(1, 6)
- random.range(1, 6)
= randint includes both end numbers, after you write import random.
QUIZ,
        ],
        [
            'title'   => 'Module 4: Collections and functions',
            'summary' => 'Organise data in lists, tuples, dictionaries and sets, write reusable functions, use the standard library and handle files.',
            'lessons' => [
                [
                    'title'   => 'Lists and tuples',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to keep many values together in one variable and process them. A school has many students, a shop has many products, and a phone has many contacts. Collections let a program handle all of them at once.</p>

<h3>Lists</h3>
<p>A <strong>list</strong> is an ordered collection written in square brackets, with items separated by commas. Items can be added, removed or changed.</p>
<pre><code>fruits = ["mango", "banana", "orange"]
print(fruits)
print(fruits[0])
print(fruits[-1])
print(len(fruits))</code></pre>
<pre>
['mango', 'banana', 'orange']
mango
orange
3
</pre>
<p>Positions (called <strong>indexes</strong>) start at 0. Negative indexes count from the end, so <code>-1</code> is the last item.</p>

<h3>Changing a list</h3>
<table>
<thead><tr><th>Code</th><th>What it does</th></tr></thead>
<tbody>
<tr><td><code>fruits.append("pawpaw")</code></td><td>Adds an item at the end</td></tr>
<tr><td><code>fruits.insert(1, "lemon")</code></td><td>Adds at position 1</td></tr>
<tr><td><code>fruits.remove("banana")</code></td><td>Removes the first matching item</td></tr>
<tr><td><code>fruits.pop()</code></td><td>Removes and returns the last item</td></tr>
<tr><td><code>fruits[0] = "guava"</code></td><td>Replaces an item</td></tr>
<tr><td><code>fruits.sort()</code></td><td>Sorts in place, A to Z</td></tr>
</tbody>
</table>
<pre><code>fruits = ["mango", "banana", "orange"]
fruits.append("pawpaw")
fruits.remove("banana")
fruits.sort()
print(fruits)</code></pre>
<pre>
['mango', 'orange', 'pawpaw']
</pre>

<h3>Looping, slicing and number functions</h3>
<pre><code>scores = [70, 85, 60]
for score in scores:
    print(score)
print("Total:", sum(scores))
print("Highest:", max(scores), "Lowest:", min(scores))
average = sum(scores) / len(scores)
print(f"Average: {average:.2f}")
print(scores[0:2])
print(85 in scores)</code></pre>
<pre>
70
85
60
Total: 215
Highest: 85 Lowest: 60
Average: 71.67
[70, 85]
True
</pre>
<p>A slice such as <code>scores[0:2]</code> takes items from position 0 up to, but not including, position 2.</p>

<h3>Building a list with a loop</h3>
<pre><code>squares = []
for n in range(1, 6):
    squares.append(n * n)
print(squares)</code></pre>
<pre>
[1, 4, 9, 16, 25]
</pre>

<h3>Tuples</h3>
<p>A <strong>tuple</strong> looks like a list but uses round brackets and <strong>cannot be changed</strong> after it is created. Use tuples for values that should stay fixed, like the coordinates of a place or the days of the week.</p>
<pre><code>gulu = (2.77, 32.30)
print(gulu[0])
lat, lon = gulu
print(lat, lon)</code></pre>
<pre>
2.77
2.77 32.3
</pre>
<p>Trying <code>gulu[0] = 3</code> gives a <code>TypeError</code>. The line <code>lat, lon = gulu</code> is called <em>unpacking</em>.</p>

<h3>Try it yourself</h3>
<ol>
<li>Make a list of five of your friends' first names; print them sorted and then print the first and last name.</li>
<li>Use a loop to ask for 3 test scores, append them to a list and print the total and average.</li>
<li>Remove the lowest score from the list using <code>min()</code> and <code>remove()</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Lists are ordered, changeable and use square brackets; indexes start at 0.</li>
<li><code>append</code>, <code>insert</code>, <code>remove</code>, <code>pop</code> and <code>sort</code> change a list.</li>
<li><code>len</code>, <code>sum</code>, <code>min</code>, <code>max</code> and <code>in</code> work on collections.</li>
<li>Tuples are fixed and use round brackets.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Dictionaries and sets',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to store information as labelled pairs and keep collections without duplicates. A <strong>dictionary</strong> works like a real dictionary or a phone book: you look something up by its name, not by its position.</p>

<h3>Dictionaries</h3>
<p>A dictionary holds <strong>key: value</strong> pairs inside curly brackets. Keys are unique labels (usually strings), and values can be anything.</p>
<pre><code>student = {
    "name": "Akello",
    "age": 17,
    "town": "Gulu"
}
print(student["name"])
print(student.get("town"))
print(student.get("phone", "not given"))</code></pre>
<pre>
Akello
Gulu
not given
</pre>
<p>Using <code>student["phone"]</code> for a missing key would cause a <code>KeyError</code>. The <code>.get()</code> method returns <code>None</code>, or a default value you choose, instead of crashing.</p>

<h3>Adding, changing and removing</h3>
<pre><code>student["age"] = 18          # change
student["class"] = "S4"      # add a new pair
del student["town"]          # remove
print(student)
print("name" in student)</code></pre>
<pre>
{'name': 'Akello', 'age': 18, 'class': 'S4'}
True
</pre>

<h3>Looping over a dictionary</h3>
<pre><code>prices = {"sugar": 4500, "rice": 5000, "salt": 1000}
for item in prices:
    print(item)
for item, price in prices.items():
    print(f"{item}: {price:,} UGX")
print(sum(prices.values()))</code></pre>
<pre>
sugar
rice
salt
sugar: 4,500 UGX
rice: 5,000 UGX
salt: 1,000 UGX
10500
</pre>
<p>Looping over the dictionary itself gives the keys. <code>.items()</code> gives key and value together, and <code>.values()</code> gives only the values.</p>

<h3>Counting with a dictionary</h3>
<pre><code>text = "to be or not to be"
counts = {}
for word in text.split():
    if word in counts:
        counts[word] += 1
    else:
        counts[word] = 1
print(counts)</code></pre>
<pre>
{'to': 2, 'be': 2, 'or': 1, 'not': 1}
</pre>
<p>The method <code>.split()</code> breaks a string into a list of words. This is the usual pattern for counting.</p>

<h3>Lists of dictionaries</h3>
<p>Real data is often a list of dictionaries, like rows in a table:</p>
<pre><code>students = [
    {"name": "Akello", "mark": 82},
    {"name": "Okello", "mark": 67},
]
for s in students:
    print(s["name"], s["mark"])</code></pre>
<pre>
Akello 82
Okello 67
</pre>

<h3>Sets</h3>
<p>A <strong>set</strong> is an unordered collection with <strong>no duplicates</strong>. It is also written with curly brackets, but with single values.</p>
<pre><code>numbers = [1, 2, 2, 3, 3, 3]
unique = set(numbers)
print(unique)
unique.add(10)
print(len(unique))
a = {1, 2, 3}
b = {3, 4}
print(a | b)
print(a &amp; b)</code></pre>
<pre>
{1, 2, 3}
4
{1, 2, 3, 4}
{3}
</pre>
<p>Sets are excellent for removing repeated values and for checking membership quickly. <code>|</code> joins two sets and <code>&amp;</code> keeps only what both share. An empty set must be written <code>set()</code>, because <code>{}</code> makes an empty dictionary.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create a dictionary for yourself with name, age and favourite subject, then print each pair with an f-string.</li>
<li>Count how many times each letter appears in the word "banana".</li>
<li>Use a set to find the unique words in a sentence you type.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Dictionaries store key: value pairs and are looked up by key.</li>
<li>Use <code>.get()</code> to avoid a <code>KeyError</code>, and <code>.items()</code> to loop over keys and values.</li>
<li>Sets hold unique values only.</li>
<li>A list of dictionaries is a simple way to model a table of records.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Functions: reusable code',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to package code into your own functions. You have been using functions already: <code>print()</code>, <code>len()</code> and <code>input()</code>. Writing your own lets you reuse code instead of copying it, and makes big programs easier to understand.</p>

<h3>Defining and calling a function</h3>
<p>You create a function with the keyword <code>def</code>, a name, brackets and a colon. The indented lines below are its <strong>body</strong>. Nothing happens until you <strong>call</strong> the function.</p>
<pre><code>def greet():
    print("Hello from a function!")

greet()
greet()</code></pre>
<pre>
Hello from a function!
Hello from a function!
</pre>

<h3>Parameters and arguments</h3>
<p>A <strong>parameter</strong> is a variable in the function's brackets that receives information. The value you pass in when calling is the <strong>argument</strong>.</p>
<pre><code>def greet(name):
    print(f"Hello, {name}!")

greet("Akello")
greet("Okello")</code></pre>
<pre>
Hello, Akello!
Hello, Okello!
</pre>

<h3>Returning values</h3>
<p>A function can hand a result back with <code>return</code>. The caller can store or print it.</p>
<pre><code>def area_of_rectangle(length, width):
    return length * width

room = area_of_rectangle(5, 3)
print(room)
print(area_of_rectangle(2, 4) + 1)</code></pre>
<pre>
15
9
</pre>
<p>As soon as Python reaches <code>return</code>, the function ends. A function without <code>return</code> gives back <code>None</code>. Note the difference: <code>print</code> only shows a value, while <code>return</code> gives it back so it can be used in more calculations.</p>

<h3>Default values</h3>
<pre><code>def power(base, exponent=2):
    return base ** exponent

print(power(5))
print(power(2, 3))</code></pre>
<pre>
25
8
</pre>

<h3>A more useful example</h3>
<pre><code>def average(numbers):
    return sum(numbers) / len(numbers)

def grade(mark):
    if mark &gt;= 80:
        return "Distinction"
    elif mark &gt;= 60:
        return "Credit"
    return "Pass or below"

marks = [70, 85, 60]
print(f"Average: {average(marks):.1f}")
print(grade(85))</code></pre>
<pre>
Average: 71.7
Distinction
</pre>

<h3>Scope: where variables live</h3>
<p>A variable created inside a function is <strong>local</strong>: it exists only while the function runs.</p>
<pre><code>def show():
    message = "inside"
    print(message)

show()
print(message)</code></pre>
<pre>
inside
Traceback (most recent call last):
  File "scope.py", line 6, in &lt;module&gt;
    print(message)
NameError: name 'message' is not defined
</pre>
<p>Pass data in with parameters and get results out with <code>return</code>; avoid depending on outside variables. This keeps functions independent and easy to test.</p>

<h3>Good function habits</h3>
<ul>
<li>One function, one job.</li>
<li>Give it a verb-like name: <code>calculate_total</code>, <code>is_even</code>.</li>
<li>Add a short description on the first line inside the function using triple quotes; this is called a <em>docstring</em>.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Write a function <code>is_even(n)</code> that returns True or False, and test it with 4 and 7.</li>
<li>Write <code>to_fahrenheit(celsius)</code> that returns <code>celsius * 9 / 5 + 32</code>; check that 100 gives 212.0.</li>
<li>Write a function that takes a list of prices and returns the total with 18% added (multiply by 1.18).</li>
</ol>

<h3>Key points</h3>
<ul>
<li><code>def</code> defines a function; calling it runs the body.</li>
<li>Parameters receive data; <code>return</code> sends a result back.</li>
<li>Variables created inside a function are local to it.</li>
<li>Small functions that do one job make programs readable.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The standard library and working with files',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use Python's built-in toolbox and to save information to a text file and read it back. Programs become truly useful when they can remember things after they are closed.</p>

<h3>The standard library</h3>
<p>Python comes with many ready-made tools grouped in <strong>modules</strong>. You use a module with <code>import</code>.</p>
<pre><code>import math
import random
from datetime import date

print(math.sqrt(16))
print(math.pi)
print(math.ceil(4.2), math.floor(4.8))

colours = ["red", "green", "blue"]
print(random.choice(colours) in colours)

today = date.today()
print(today.year)
print(today.strftime("%d %B %Y"))</code></pre>
<pre>
4.0
3.141592653589793
5 4
True
2026
21 October 2026
</pre>
<p>The last two lines show the date on which the program runs, so your output will differ. In <code>strftime</code>, <code>%d</code> is the day, <code>%B</code> the month name and <code>%Y</code> the four-digit year. Other helpful modules are <code>random</code> (<code>randint</code>, <code>choice</code>, <code>shuffle</code>), <code>math</code>, <code>os</code> and <code>time</code>.</p>

<h3>Writing to a file</h3>
<p>Use <code>open(filename, mode)</code> inside a <code>with</code> block. The block closes the file for you, even if there is an error. Modes: <code>"w"</code> write (replaces everything), <code>"a"</code> append (adds at the end), <code>"r"</code> read.</p>
<pre><code>with open("notes.txt", "w", encoding="utf-8") as file:
    file.write("First line\n")
    file.write("Second line\n")</code></pre>
<p>The file <code>notes.txt</code> is created in the same folder from which the program runs. Remember to add <code>\n</code> yourself because <code>write</code> does not add a new line.</p>

<h3>Reading from a file</h3>
<pre><code>with open("notes.txt", "r", encoding="utf-8") as file:
    content = file.read()
print(content)

with open("notes.txt", "r", encoding="utf-8") as file:
    for line in file:
        print(line.strip())</code></pre>
<pre>
First line
Second line

First line
Second line
</pre>
<p>The first print shows a blank line afterwards because the text itself ends with a new line. In the second version, <code>line.strip()</code> removes the new line that each line carries.</p>

<h3>Appending and checking</h3>
<pre><code>import os

with open("notes.txt", "a", encoding="utf-8") as file:
    file.write("Third line\n")

if os.path.exists("notes.txt"):
    print("The file exists.")
else:
    print("No file yet.")</code></pre>
<pre>
The file exists.
</pre>
<p>Opening a file that does not exist in <code>"r"</code> mode raises a <code>FileNotFoundError</code>, so checking first with <code>os.path.exists</code> is helpful.</p>

<h3>Saving a list</h3>
<pre><code>names = ["Akello", "Okello", "Atim"]
with open("names.txt", "w", encoding="utf-8") as file:
    for name in names:
        file.write(name + "\n")

loaded = []
with open("names.txt", "r", encoding="utf-8") as file:
    for line in file:
        loaded.append(line.strip())
print(loaded)</code></pre>
<pre>
['Akello', 'Okello', 'Atim']
</pre>

<h3>Try it yourself</h3>
<ol>
<li>Write a program that asks for three of your goals and saves them to <code>goals.txt</code>, one per line.</li>
<li>Write a second program that reads and prints them with numbers: 1, 2, 3.</li>
<li>Use <code>random.choice</code> to pick one of your goals as "goal of the day".</li>
<li>Open <code>goals.txt</code> in Notepad to see what Python wrote.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Modules such as <code>math</code>, <code>random</code> and <code>datetime</code> are loaded with <code>import</code>.</li>
<li>Use <code>with open(...)</code> so files are always closed properly.</li>
<li>Modes: <code>"w"</code> replaces, <code>"a"</code> adds, <code>"r"</code> reads.</li>
<li>Use <code>strip()</code> to remove the new line from lines you read.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does this print?   items = ["a", "b", "c"]   print(items[-1])
- a
- b
* c
- An error
= Index -1 means the last item in the list.

Q: What is the main difference between a list and a tuple?
- Lists are faster to type
* Tuples cannot be changed after they are created; lists can
- Tuples can hold only numbers
- Lists cannot be looped over
= Use a tuple when the values should stay fixed.

Q: Which line safely reads a key that might be missing?
- student["phone"]
* student.get("phone", "not given")
- student.phone
- student(phone)
= get returns a default instead of raising a KeyError.

Q: What is the result of  len(set([4, 4, 5, 5, 5, 6]))  ?
- 6
- 2
* 3
- 1
= A set keeps unique values only: 4, 5 and 6.

Q: What does the function below return for  add(2, 3)?   def add(a, b):   print(a + b)
- 5
* None, because it prints but does not return anything
- 23
- An error
= Without return, a function gives back None. It prints 5, but the caller receives None.

Q: A variable is created inside a function. What happens if you print it outside the function?
- It prints its value
- It prints None
* A NameError, because the variable is local to the function
- It prints zero
= Local variables exist only while the function runs.

Q: Which file mode adds text to the end of a file without deleting what is there?
- "r"
- "w"
* "a"
- "x"
= Mode "a" means append. Mode "w" would replace the whole file.

Q: Why is  with open("data.txt") as file:  preferred?
- It makes the file bigger
* It closes the file automatically, even when an error happens
- It works without a file name
- It encrypts the file
= The with block handles closing the file safely.
QUIZ,
        ],
        [
            'title'   => 'Module 5: Building small projects',
            'summary' => 'Debug your code, build a quiz game, an expense tracker, a contact book and a converter, and plan your next steps.',
            'lessons' => [
                [
                    'title'   => 'Debugging and good programming habits',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to find and fix bugs methodically and write code that other people (and you in six months) can read. A <strong>bug</strong> is a mistake in a program, and <strong>debugging</strong> is finding and fixing it. Professional programmers spend a large part of their time debugging, so a good method is a superpower.</p>

<h3>A method for hunting bugs</h3>
<ol>
<li><strong>Reproduce it.</strong> Find the exact input that causes the problem.</li>
<li><strong>Read the error.</strong> Start with the last line of the traceback and the line number.</li>
<li><strong>Look at your values.</strong> Add temporary <code>print()</code> lines to show variables as the program runs.</li>
<li><strong>Divide the problem.</strong> Comment out half the code to find which half contains the bug.</li>
<li><strong>Change one thing at a time</strong> and test again.</li>
<li><strong>Explain it aloud.</strong> Describing each line to a friend, or even to an object on your desk, often reveals the mistake. This is known as rubber-duck debugging.</li>
</ol>

<h3>Print debugging in action</h3>
<p>This function should add the numbers from 1 to n, but it gives the wrong answer:</p>
<pre><code>def sum_to(n):
    total = 0
    for i in range(1, n):
        total += i
    return total

print(sum_to(5))</code></pre>
<pre>
10
</pre>
<p>We expected 15. Add a print inside the loop:</p>
<pre><code>    for i in range(1, n):
        print("i is", i)
        total += i</code></pre>
<pre>
i is 1
i is 2
i is 3
i is 4
10
</pre>
<p>The number 5 never appears. The cause is that <code>range(1, n)</code> stops before n. The fix is <code>range(1, n + 1)</code>. This kind of mistake is called an <strong>off-by-one error</strong> and is very common.</p>

<h3>Handling errors with try and except</h3>
<p>Some errors come from the outside world, such as a user typing letters where a number is needed. Catch them with <code>try</code> and <code>except</code>:</p>
<pre><code>while True:
    try:
        age = int(input("Your age: "))
        break
    except ValueError:
        print("Please type a whole number.")
print("Age saved:", age)</code></pre>
<pre>
Your age: abc
Please type a whole number.
Your age: 16
Age saved: 16
</pre>

<h3>Testing your code</h3>
<p>Test with normal values, with edge cases (zero, empty, the biggest and smallest allowed) and with wrong input. A simple tool is <code>assert</code>, which does nothing when the statement is True and raises an error when it is False.</p>
<pre><code>def is_even(n):
    return n % 2 == 0

assert is_even(4) == True
assert is_even(7) == False
print("All tests passed")</code></pre>

<h3>Habits of readable code</h3>
<ul>
<li>Use meaningful names (<code>total_cost</code>, not <code>tc</code>).</li>
<li>Keep functions short, each doing one job.</li>
<li>Comment on the reason behind tricky parts, not the obvious.</li>
<li>Do not repeat yourself; put repeated code in a function.</li>
<li>Be consistent: four spaces per indent, one style for names.</li>
</ul>

<h3>Version control: the big idea</h3>
<p><strong>Version control</strong> software such as <strong>Git</strong> records the history of your project. You save named snapshots called <em>commits</em>, so you can see what changed, undo a mistake and work with other people. Even as a beginner, you can keep copies like <code>project_v1.py</code> and <code>project_v2.py</code> before big changes. Learning Git is a very good next step.</p>

<h3>Try it yourself</h3>
<ol>
<li>Type the buggy <code>sum_to</code> function, find the bug with a print statement and fix it.</li>
<li>Add a <code>try/except</code> so a program asking for a price does not crash on text.</li>
<li>Write three <code>assert</code> tests for your <code>to_fahrenheit</code> function from the previous module.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Debug by reproducing, reading the error, printing values and changing one thing at a time.</li>
<li>Off-by-one mistakes with <code>range</code> are common.</li>
<li><code>try</code> and <code>except</code> handle problems such as bad user input.</li>
<li>Readable names, short functions and tests make code reliable; Git records its history.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Project: a quiz game and an expense tracker',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>In this lesson you will build two complete programs from the tools you now know. Type them yourself, run them, and then change them. After this lesson you will be able to combine lists, dictionaries, loops and decisions into useful tools.</p>

<h3>Project 1: a quiz game</h3>
<p>Each question and its answer is stored as a pair (a tuple) inside a list. The loop asks every question, compares the reply with the answer, and keeps the score.</p>
<pre><code>questions = [
    ("What is the capital of Uganda?", "kampala"),
    ("How many days are in a week?", "7"),
    ("What is 6 x 7?", "42"),
]

score = 0
for question, answer in questions:
    reply = input(question + " ").strip().lower()
    if reply == answer:
        print("Correct!")
        score += 1
    else:
        print("Wrong. The answer is", answer)

print(f"You scored {score} out of {len(questions)}.")</code></pre>
<pre>
What is the capital of Uganda? Kampala
Correct!
How many days are in a week? 6
Wrong. The answer is 7
What is 6 x 7? 42
Correct!
You scored 2 out of 3.
</pre>
<p>The methods <code>.strip()</code> and <code>.lower()</code> make the game forgiving about spaces and capital letters. Ideas to improve it: shuffle the questions with <code>random.shuffle(questions)</code>, add more questions, and give a message based on the final percentage.</p>

<h3>Project 2: an expense tracker</h3>
<p>This tracker keeps a list of dictionaries and shows a menu until the user quits.</p>
<pre><code>expenses = []

while True:
    print("\n1. Add expense")
    print("2. Show expenses")
    print("3. Quit")
    choice = input("Choose: ")

    if choice == "1":
        item = input("Item: ")
        amount_text = input("Amount in UGX: ")
        if amount_text.isdigit():
            expenses.append({"item": item, "amount": int(amount_text)})
            print("Saved.")
        else:
            print("Please type the amount as a whole number.")
    elif choice == "2":
        total = 0
        for expense in expenses:
            print(f"{expense['item']}: {expense['amount']:,} UGX")
            total += expense["amount"]
        print(f"Total: {total:,} UGX")
    elif choice == "3":
        print("Goodbye!")
        break
    else:
        print("Invalid choice.")</code></pre>
<p>A short example run:</p>
<pre>
1. Add expense
2. Show expenses
3. Quit
Choose: 1
Item: Bread
Amount in UGX: 3500
Saved.

1. Add expense
2. Show expenses
3. Quit
Choose: 2
Bread: 3,500 UGX
Total: 3,500 UGX
</pre>
<p>Notice inside the f-string we use single quotes for the dictionary keys, because the f-string itself is in double quotes.</p>

<h3>Improve the tracker</h3>
<ul>
<li>Save the expenses to a file when the user quits, and load them when the program starts.</li>
<li>Add a budget limit and warn the user when the total goes over it.</li>
<li>Add a category ("food", "transport") to each expense and show totals per category using a dictionary.</li>
<li>Move each menu action into its own function.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Type and run the quiz game with five questions about your own school or district.</li>
<li>Type and run the expense tracker and add at least four items.</li>
<li>Improve one of them with an idea from the lists above.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A list of tuples is a simple way to store questions and answers.</li>
<li>A list of dictionaries stores records such as expenses.</li>
<li>A <code>while True</code> menu loop with <code>break</code> is the standard pattern for interactive programs.</li>
<li>Validate user input before using it.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Project: a contact book and a converter',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>In this lesson you will build two more tools, this time organising the code into functions. After this lesson you will be able to structure a longer program so that each part has one clear job.</p>

<h3>Project 3: a contact book</h3>
<p>The contacts are kept in a dictionary where the key is the name and the value is the phone number. Each action is a function.</p>
<pre><code>contacts = {}

def add_contact():
    name = input("Name: ").strip().title()
    phone = input("Phone: ").strip()
    contacts[name] = phone
    print(name, "saved.")

def find_contact():
    name = input("Search name: ").strip().title()
    if name in contacts:
        print(name, "-&gt;", contacts[name])
    else:
        print("Not found.")

def list_contacts():
    if not contacts:
        print("No contacts yet.")
    for name in sorted(contacts):
        print(f"{name}: {contacts[name]}")

while True:
    print("\n1. Add  2. Find  3. List  4. Quit")
    choice = input("Choose: ")
    if choice == "1":
        add_contact()
    elif choice == "2":
        find_contact()
    elif choice == "3":
        list_contacts()
    elif choice == "4":
        break
    else:
        print("Invalid choice.")</code></pre>
<p>A short run:</p>
<pre>
1. Add  2. Find  3. List  4. Quit
Choose: 1
Name: akello grace
Phone: 0700 000 111
Akello Grace saved.

1. Add  2. Find  3. List  4. Quit
Choose: 3
Akello Grace: 0700 000 111
</pre>
<p>The functions change the <code>contacts</code> dictionary without needing a special keyword, because they modify the existing dictionary rather than creating a new variable with the same name. The method <code>.title()</code> makes names consistent, and <code>sorted(contacts)</code> lists the names alphabetically. Using the name as the key means saving the same name again replaces the old number, which is a simple way to update a contact.</p>

<h3>Project 4: a currency and temperature converter</h3>
<p>Exchange rates change daily, so the program asks the user for today's rate instead of storing one. Always check the real rate from a trusted source such as your bank.</p>
<pre><code>def usd_to_ugx(usd, rate):
    return usd * rate

def ugx_to_usd(ugx, rate):
    return ugx / rate

def celsius_to_fahrenheit(celsius):
    return celsius * 9 / 5 + 32

print("1. USD to UGX")
print("2. UGX to USD")
print("3. Celsius to Fahrenheit")
choice = input("Choose: ")

if choice == "1":
    rate = float(input("Rate (UGX for 1 USD): "))
    usd = float(input("Amount in USD: "))
    print(f"{usd:,.2f} USD = {usd_to_ugx(usd, rate):,.0f} UGX")
elif choice == "2":
    rate = float(input("Rate (UGX for 1 USD): "))
    ugx = float(input("Amount in UGX: "))
    print(f"{ugx:,.0f} UGX = {ugx_to_usd(ugx, rate):,.2f} USD")
elif choice == "3":
    celsius = float(input("Degrees Celsius: "))
    print(f"{celsius}C = {celsius_to_fahrenheit(celsius)}F")
else:
    print("Invalid choice.")</code></pre>
<p>With an example rate of 3700 (this is only for practice), the run could look like this:</p>
<pre>
1. USD to UGX
2. UGX to USD
3. Celsius to Fahrenheit
Choose: 1
Rate (UGX for 1 USD): 3700
Amount in USD: 100
100.00 USD = 370,000 UGX
</pre>
<p>If the user chose option 3 and typed 30, the output would be <code>30.0C = 86.0F</code>. Because <code>float()</code> would crash on bad input, a good improvement is to wrap the conversions in <code>try</code> and <code>except ValueError</code>.</p>

<h3>Try it yourself</h3>
<ol>
<li>Type the contact book and add three contacts. Test finding one that does not exist.</li>
<li>Add a "Delete" option using <code>del contacts[name]</code> (check that the name exists first).</li>
<li>Save the contacts to a file when the user quits and load them when the program starts.</li>
<li>Add a km-to-miles option to the converter (1 km is about 0.621 miles).</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Dictionaries are ideal for lookup tables such as contacts.</li>
<li>Splitting a program into functions makes it easier to read, test and extend.</li>
<li>Ask for changing values such as exchange rates instead of hard-coding them.</li>
<li>Wrap risky conversions in <code>try</code> and <code>except</code>.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Next steps and your final project',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>You have gone from "what is a program?" to writing games, trackers and tools. After this lesson you will know where Python can take you and have a clear brief for a final project that proves your skills. Learning to program is a long road, but you have taken the hardest steps, which are the first ones.</p>

<h3>What you can now do</h3>
<ul>
<li>Plan solutions with algorithms and flowcharts.</li>
<li>Use variables, strings, numbers, input and output.</li>
<li>Decide with <code>if</code> and repeat with <code>while</code> and <code>for</code>.</li>
<li>Organise data in lists, tuples, dictionaries and sets.</li>
<li>Write functions, use modules and work with files.</li>
<li>Debug methodically and write readable code.</li>
</ul>

<h3>Where Python can take you</h3>
<table>
<thead><tr><th>Path</th><th>What it involves</th><th>What to learn next</th></tr></thead>
<tbody>
<tr><td>Data analysis</td><td>Cleaning, summarising and charting information, such as survey results or sales</td><td>Libraries such as pandas and a charting library; spreadsheets help too</td></tr>
<tr><td>Web development</td><td>Building websites and services with a back end</td><td>HTML and CSS first, then a Python web framework</td></tr>
<tr><td>Automation</td><td>Renaming files, sending reports, collecting data from the web</td><td>More of the standard library: files, dates, working with text</td></tr>
<tr><td>AI and machine learning</td><td>Programs that learn patterns from data</td><td>Maths and statistics basics, data analysis, then machine learning libraries</td></tr>
</tbody>
</table>

<h3>How to keep improving</h3>
<ol>
<li><strong>Code a little every day.</strong> Thirty minutes daily beats a whole day once a month.</li>
<li><strong>Build projects you care about.</strong> A tool for your family shop or your football club will keep you motivated.</li>
<li><strong>Read other people's code</strong> and the official Python documentation.</li>
<li><strong>Learn Git</strong> and keep your projects in a repository.</li>
<li><strong>Join or start a study group.</strong> Explaining code to others is the best way to learn it.</li>
<li><strong>Be patient with errors.</strong> They are part of the work, not a sign of failure.</li>
</ol>

<h3>Final project brief</h3>
<p>Choose <strong>one</strong> of these ideas, or invent your own of similar size:</p>
<ul>
<li>A shop stock manager that adds items, sells items, and warns when stock is low.</li>
<li>A savings goal tracker with deposits, withdrawals and progress shown as a percentage.</li>
<li>A student results system that stores marks, calculates averages and grades, and lists the best student.</li>
<li>A quiz program with at least ten questions in two categories and a high-score file.</li>
</ul>
<p>Your project should include:</p>
<ol>
<li>A written plan: the problem, the steps, and a flowchart.</li>
<li>User input with checks for wrong input.</li>
<li>At least one list or dictionary, at least three functions, and loops and decisions.</li>
<li>Saving data to a file and loading it again.</li>
<li>Readable names and comments, and several test runs with different inputs.</li>
</ol>

<h3>Try it yourself</h3>
<ol>
<li>Choose your project and write the plan and flowchart on paper first.</li>
<li>Build the smallest version that works, then add features one by one, running often.</li>
<li>Test with a friend, who should try to break it with unusual input.</li>
<li>Write three sentences: what the program does, what was hardest, and what you would add next.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>You now have the foundations for data analysis, web, automation and AI work.</li>
<li>Regular practice and real projects matter more than reading.</li>
<li>Plan first, build small, test often and ask for feedback.</li>
<li>Finish a final project to show what you can do.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A program runs without errors but prints the wrong answer. What kind of error is this?
- Syntax error
- Runtime error
* Logic error
- Install error
= Logic errors do not crash the program, so you find them by checking results and printing values.

Q: What is an off-by-one error?
- Forgetting to save the file
* A loop or index that goes one step too far or stops one step too early
- Using one variable twice
- Having a single space too many
= For example, range(1, 5) stops at 4, not 5.

Q: Which code handles a user typing letters instead of a number?
- if int(text):
* try: age = int(text)   then   except ValueError: print("Number please")
- while int(text):
- print(int(text) or "error")
= try and except catch the ValueError so the program does not crash.

Q: What is the benefit of putting repeated code into a function?
- It makes the program run faster in every case
* It is written once, easier to read and test, and easy to change in one place
- It removes the need for variables
- It hides errors
= Functions help avoid repeating yourself.

Q: In the expense tracker, why check text.isdigit() before int(text)?
- It makes the number bigger
- It converts text to numbers
* It avoids a crash if the user types something that is not a whole number
- It is required by Python
= isdigit confirms the text contains only digits so the conversion will succeed.

Q: Which is the best description of version control such as Git?
- A way to make programs run faster
* A system that records the history of changes so you can review and undo them
- A kind of programming language
- A website for downloading Python
= Commits are named snapshots of your project.

Q: Why did the contact book store contacts in a dictionary?
* Looking up a phone number by name is quick, and each name appears only once
- Dictionaries are the only type that can hold text
- Lists cannot be sorted
- It uses less typing than any other method
= The name is the key, so find and update operations are simple.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which of these best describes an algorithm?
- A fast computer
* A clear sequence of steps to solve a problem
- A Python module
- A type of error
= An algorithm is a plan you can follow step by step.

Q: Which flowchart shape represents a decision?
- Oval
- Rectangle
- Arrow
* Diamond
= A diamond asks a yes or no question.

Q: On Windows, which option is important to tick when installing Python?
* Add python.exe to PATH
- Install for all printers
- Disable updates
- Use only IDLE
= This lets you run Python from any folder in Command Prompt.

Q: What does  print("a", "b", "c", sep="")  display?
- a b c
* abc
- a,b,c
- Nothing
= An empty separator joins the items with no space.

Q: Which symbol begins a comment in Python?
- //
- --
* #
- /*
= Python ignores everything after # on the same line.

Q: When reading a traceback, where do you look first?
- At the top line
- At the middle line
* At the last line, which names the error type and reason
- At the file size
= The final line says what went wrong; the lines above say where.

Q: What is the type of the value returned by input("Age: ") when the user types 25?
- int
- float
* str
- bool
= input always returns text.

Q: What is printed by  print(2 + 3 * 2)  ?
- 10
* 8
- 12
- 7
= Multiplication happens before addition: 3 * 2 = 6, then 2 + 6 = 8.

Q: What is the result of  9 % 4  ?
- 2.25
- 2
* 1
- 5
= The remainder when 9 is divided by 4 is 1.

Q: What does  f"{0.5:.2f}"  produce?
- 0.5
- .50
* 0.50
- 0.500
= .2f shows exactly two decimal places.

Q: Which expression is True when age is between 13 and 19, inclusive?
- age >= 13 or age <= 19
* age >= 13 and age <= 19
- age == 13 and 19
- age > 13 or < 19
= Both conditions must be True at the same time, so use and.

Q: Why does  if score = 50:  cause an error?
- Because 50 is too small
* Because = assigns; a comparison needs ==
- Because if cannot use numbers
- Because there is no else
= Use == to compare values inside conditions.

Q: What does a while loop do?
- Repeats a block a fixed number of times, always ten
* Repeats a block as long as its condition is True
- Runs a block once
- Stops the program
= The condition is checked before every round.

Q: Which keyword leaves a loop immediately?
- continue
- stop
- exit
* break
= continue only skips the rest of the current round.

Q: What numbers does  range(1, 4)  produce?
- 1, 2, 3, 4
- 0, 1, 2, 3
* 1, 2, 3
- 2, 3, 4
= The end value is not included.

Q: What does this print?   for i in range(3):   print("*" * i)
- *, **, ***
* An empty line, then *, then **
- ***, ***, ***
- Only ***
= i takes the values 0, 1, 2, so the first line is empty.

Q: Which method adds an item to the end of a list?
- add
- push
* append
- insert_end
= list.append(item) adds at the end.

Q: What is the output?   nums = [3, 1, 2]   nums.sort()   print(nums)
- [3, 1, 2]
- [3, 2, 1]
* [1, 2, 3]
- None
= sort changes the list in place into ascending order.

Q: Which type would you choose to store a phone number for each person's name?
- A set
* A dictionary
- A tuple of numbers only
- An integer
= A dictionary links each name (key) to its number (value).

Q: What does  set([5, 5, 6])  give?
- [5, 5, 6]
- (5, 6)
* {5, 6}
- {5, 5, 6}
= A set removes duplicates.

Q: What does a function return when it has no return statement?
- 0
- An empty string
* None
- An error
= Python gives back None by default.

Q: Which is the correct way to define a function that doubles a number?
- function double(n): return n * 2
- def double(n) return n * 2
* def double(n):   return n * 2   (the second part indented)
- double def (n): n * 2
= A function starts with def, the name, brackets, a colon, and an indented body.

Q: Which statements about opening files are correct? (choose all that apply)
* Mode "w" replaces the existing content
* Mode "a" adds to the end
* A with block closes the file automatically
- Mode "r" creates the file if it does not exist
= Reading a missing file gives a FileNotFoundError.

Q: What does  random.randint(1, 10)  return?
- A whole number from 1 to 9
* A whole number from 1 to 10, including both
- A decimal between 1 and 10
- Always 10
= randint includes both ends of the range.

Q: Which is an example of a logic error?
- Forgetting a closing bracket
- Using an undefined variable
* A program that calculates an average by dividing by the wrong number
- Opening a missing file
= It runs, but the answer is wrong.

Q: A program must keep asking for an age until a valid whole number is typed. Which structure is best?
- A single if statement
* A while loop with try and except
- A tuple
- A for loop over a string
= The loop repeats until the conversion succeeds, and except handles the ValueError.

Q: Which habits make code easier to maintain? (choose all that apply)
* Meaningful variable names
* Short functions that do one job
* Comments that explain why
- Using single-letter names for everything
= Readable code is easier to fix and extend.

Q: Which of these is a good next step towards data analysis with Python?
- Stop practising loops
* Learn a data library such as pandas and practise on real data
- Learn only HTML
- Memorise error messages
= Data libraries build on the Python basics you have learned.
QUIZ,
];
