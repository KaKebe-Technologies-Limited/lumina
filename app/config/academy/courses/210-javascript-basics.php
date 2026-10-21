<?php
return [
    'slug'        => 'javascript-basics',
    'title'       => 'JavaScript for Beginners: Make Web Pages Interactive',
    'category'    => 'web',
    'level'       => 'intermediate',
    'icon'        => 'code',
    'sort'        => 210,
    'summary'     => 'Learn JavaScript from the first line: variables, decisions, loops, functions, the DOM and events. Build a to-do list, tip calculator, quiz and tabs, then learn debugging and fetch.',
    'description' => '<p>HTML gives a web page its structure and CSS gives it style. <strong>JavaScript</strong> gives it behaviour: menus that open, forms that check what you typed, lists that update without reloading the page. It is the programming language that every web browser understands, so anything you build with it can run on any phone or computer with no extra software.</p><p>This course takes you step by step from your first line of code to small, working features. You will learn the core ideas of the language, then use them to change a real page: select elements, react to clicks, validate forms and save data in the browser. You will also learn how to find and fix mistakes, which is a large part of every developer\'s day.</p><p>You should already be able to write a simple web page with HTML and CSS. No programming experience is needed. Type every example yourself, run it, and change it to see what happens: that is how programming is learned.</p>',
    'outcomes'    => [
        'Explain what JavaScript does in the browser and run code in the console and from a script file.',
        'Store and combine data with variables, data types, operators and template strings.',
        'Make programs decide and repeat with if, switch, for and while.',
        'Write functions, including arrow functions, and explain scope.',
        'Work with arrays and objects using map, filter, forEach and find.',
        'Select and change page elements with the DOM and respond to click, input and submit events.',
        'Validate a form and build small features such as a to-do list, tip calculator, quiz and tabs.',
        'Save data with localStorage, debug with the console and breakpoints, and fetch JSON from an API.',
    ],
    'requirements'=> [
        'The LAF Academy course "Web Basics: Build Your First Web Page (HTML and CSS)" or equal knowledge',
        'A computer with a modern browser (Chrome, Edge, Firefox) and a text editor such as VS Code or Notepad++',
        'No programming experience needed',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'MDN Web Docs: JavaScript', 'url' => 'https://developer.mozilla.org/docs/Web/JavaScript'],
        ['title' => 'MDN Web Docs: Learn web development', 'url' => 'https://developer.mozilla.org/docs/Learn'],
        ['title' => 'javascript.info: The Modern JavaScript Tutorial', 'url' => 'https://javascript.info/'],
        ['title' => 'Visual Studio Code', 'url' => 'https://code.visualstudio.com/'],
    ],
    'modules' => [
        [
            'title'   => 'Module 1: First steps with JavaScript',
            'summary' => 'Understand what JavaScript does, run your first code, and learn variables, data types, operators and template strings.',
            'lessons' => [
                [
                    'title'   => 'What JavaScript does and how to run it',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will know what JavaScript is for, and you will be able to run your own code in two ways: in the browser console and from a script file linked to a web page. Every later lesson depends on being able to do this comfortably.</p>

<h3>HTML, CSS and JavaScript</h3>
<p>A web page is built from three layers. <strong>HTML</strong> is the content and structure (headings, paragraphs, buttons). <strong>CSS</strong> is the appearance (colours, spacing, layout). <strong>JavaScript</strong> is the behaviour: it can react when a visitor clicks, check a form before it is sent, change text on the page, or load fresh information without refreshing. When you use a "Like" button, a search box that suggests words as you type, or a mobile money web form that tells you the phone number is too short, JavaScript is usually at work.</p>
<p>JavaScript runs inside the visitor's browser, on their own device. That is why it works for everyone without installing anything. Despite the similar name, it has nothing to do with the Java language.</p>

<h3>Way 1: the browser console</h3>
<p>The <strong>console</strong> is a built-in tool where you can type JavaScript and see the result straight away. It is perfect for experiments.</p>
<ol>
<li>Open Chrome or Edge. Press <strong>F12</strong> (or <strong>Ctrl+Shift+J</strong>; on a Mac <strong>Cmd+Option+J</strong>).</li>
<li>Click the <strong>Console</strong> tab.</li>
<li>Type the line below and press <strong>Enter</strong>.</li>
</ol>
<pre><code>console.log("Hello from Gulu!");</code></pre>
<p><code>console.log()</code> prints whatever you put between the brackets. The text in quotes is a <strong>string</strong>. The semicolon at the end finishes the instruction. Try some arithmetic too: type <code>12 * 500</code> and press Enter, and the console answers 6000.</p>

<h3>Way 2: a script file</h3>
<p>Real projects keep code in files. Make a folder called <code>js-practice</code> with two files. First <code>index.html</code>:</p>
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
  &lt;meta charset="utf-8"&gt;
  &lt;title&gt;My first script&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
  &lt;h1 id="title"&gt;Hello&lt;/h1&gt;
  &lt;script src="app.js"&gt;&lt;/script&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
<p>Then <code>app.js</code> in the same folder:</p>
<pre><code>console.log("The script has loaded.");
document.getElementById("title").textContent = "Hello from JavaScript";</code></pre>
<p>Open <code>index.html</code> in your browser (double-click it). The heading changes, and the console shows your message. The <code>script</code> tag sits at the end of the body so the page elements already exist when the code runs. You can also write code between <code>&lt;script&gt;</code> and <code>&lt;/script&gt;</code> directly in the page, but a separate file is tidier and is the habit to build.</p>

<h3>Comments</h3>
<p>A <strong>comment</strong> is a note for humans that the browser ignores. Write <code>// a note</code> for one line, or <code>/* a longer note */</code> across several lines. Good comments explain <em>why</em> you did something.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create the <code>js-practice</code> folder with the two files above and open the page.</li>
<li>Change the text in <code>app.js</code> so the heading shows your own name.</li>
<li>Add three <code>console.log</code> lines that print your name, your town and the result of <code>7 * 8</code>.</li>
<li>Open the console (F12) and confirm all three lines appear. Add a comment above each line.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>HTML is structure, CSS is style, JavaScript is behaviour; it runs in the visitor's browser.</li>
<li>The console (F12) lets you test code instantly with <code>console.log()</code>.</li>
<li>A script file is linked with a <code>script</code> tag with a <code>src</code> attribute, usually at the end of the body.</li>
<li>Comments (<code>//</code> and <code>/* */</code>) explain code and are ignored by the browser.</li>
</ul>

<h3>Common mistakes</h3>
<p>If nothing happens, check that the file name in <code>src</code> matches exactly (capital letters count), and look in the console for a red error message.</p>
HTML,
                ],
                [
                    'title'   => 'Variables and data types',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to store information in variables using <code>let</code> and <code>const</code>, and recognise the main data types. Programs are mostly about storing, changing and comparing information, so this is the foundation.</p>

<h3>What is a variable?</h3>
<p>A <strong>variable</strong> is a named box that holds a value. You give the box a name and put something in it. Later you use the name to get the value back.</p>
<pre><code>let age = 19;
const country = "Uganda";
console.log(country, age);   // Uganda 19
age = 20;                    // allowed: let can change
console.log(age);            // 20</code></pre>
<p>The word <code>let</code> creates a variable whose value can change later. The word <code>const</code> (short for constant) creates one that cannot be reassigned. If you write <code>country = "Kenya"</code>, JavaScript stops with an error: <em>Assignment to constant variable</em>. A good habit: use <code>const</code> by default, and change to <code>let</code> only when the value really must change. You may see older code using <code>var</code>; avoid it in new code because it behaves in surprising ways.</p>

<h3>Naming rules</h3>
<ul>
<li>Names may contain letters, digits, <code>_</code> and <code>$</code>, but cannot start with a digit.</li>
<li>Names are case sensitive: <code>total</code> and <code>Total</code> are different variables.</li>
<li>Do not use reserved words such as <code>let</code>, <code>const</code>, <code>if</code> or <code>function</code>.</li>
<li>The usual style is <strong>camelCase</strong>: <code>shopName</code>, <code>unitPrice</code>, <code>isPaid</code>. Choose names that say what the value is.</li>
</ul>

<h3>The main data types</h3>
<table>
<thead><tr><th>Type</th><th>Example</th><th>Used for</th></tr></thead>
<tbody>
<tr><td>string</td><td><code>"Akello"</code>, <code>'Gulu'</code></td><td>Text</td></tr>
<tr><td>number</td><td><code>25</code>, <code>3.5</code></td><td>Whole and decimal numbers</td></tr>
<tr><td>boolean</td><td><code>true</code>, <code>false</code></td><td>Yes or no answers</td></tr>
<tr><td>undefined</td><td><code>undefined</code></td><td>A variable with no value yet</td></tr>
<tr><td>null</td><td><code>null</code></td><td>Deliberately empty</td></tr>
<tr><td>object</td><td><code>{ name: "Ojok" }</code>, <code>[1, 2, 3]</code></td><td>Groups of related data</td></tr>
</tbody>
</table>
<p>Use <code>typeof</code> to ask for a type:</p>
<pre><code>console.log(typeof "hello");   // string
console.log(typeof 42);         // number
console.log(typeof true);       // boolean
let price;
console.log(typeof price);      // undefined
console.log(typeof [1, 2]);     // object (arrays are objects)</code></pre>

<h3>Converting types</h3>
<p>Text typed by a user is always a string, even if it looks like a number. Convert with <code>Number()</code>:</p>
<pre><code>const typed = "250";
const amount = Number(typed);
console.log(amount + 50);        // 300
console.log(Number("abc"));      // NaN (Not a Number)</code></pre>
<p><code>NaN</code> is the result when a conversion fails. You can turn a number into text with <code>String(25)</code>.</p>

<h3>Try it yourself</h3>
<ol>
<li>In <code>app.js</code>, create <code>const</code> variables <code>shopName</code>, <code>itemPrice</code> (a number in UGX) and <code>isOpen</code> (a boolean).</li>
<li>Print each one with <code>console.log</code> together with its <code>typeof</code>.</li>
<li>Create <code>let stock = 20;</code>, then change it to 15, and print it.</li>
<li>Try changing a <code>const</code> and read the error message in the console.</li>
</ol>

<h3>Key points</h3>
<ul>
<li><code>let</code> makes a changeable variable, <code>const</code> makes one that cannot be reassigned; prefer <code>const</code>.</li>
<li>Names are case sensitive and written in camelCase.</li>
<li>The main types are string, number, boolean, undefined, null and object.</li>
<li>Convert user text to a number with <code>Number()</code>; failure gives <code>NaN</code>.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Operators and template strings',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to calculate with numbers, compare values, combine conditions, and build readable sentences from variables using template strings. These tools appear in almost every program you will write, from a shop total to a form check.</p>

<h3>Arithmetic operators</h3>
<table>
<thead><tr><th>Operator</th><th>Meaning</th><th>Example</th><th>Result</th></tr></thead>
<tbody>
<tr><td><code>+</code></td><td>Add</td><td><code>5 + 2</code></td><td>7</td></tr>
<tr><td><code>-</code></td><td>Subtract</td><td><code>5 - 2</code></td><td>3</td></tr>
<tr><td><code>*</code></td><td>Multiply</td><td><code>5 * 2</code></td><td>10</td></tr>
<tr><td><code>/</code></td><td>Divide</td><td><code>5 / 2</code></td><td>2.5</td></tr>
<tr><td><code>%</code></td><td>Remainder</td><td><code>5 % 2</code></td><td>1</td></tr>
<tr><td><code>**</code></td><td>Power</td><td><code>2 ** 3</code></td><td>8</td></tr>
</tbody>
</table>
<p>The remainder operator is handy: <code>n % 2 === 0</code> is true when <code>n</code> is even. Shortcuts exist for updating a variable: <code>total += 500</code> means <code>total = total + 500</code>, and <code>count++</code> adds 1.</p>
<pre><code>let total = 1000;
total += 500;      // 1500
total *= 2;        // 3000
let count = 0;
count++;           // 1
console.log(total, count);</code></pre>

<h3>Strings and the + trap</h3>
<p>With strings, <code>+</code> joins (concatenates) text. When one side is a string, the other becomes text too:</p>
<pre><code>console.log("Total: " + 5 + 5);    // Total: 55
console.log("Total: " + (5 + 5));  // Total: 10
console.log("5" * 2);              // 10 (the * forces a number)</code></pre>

<h3>Template strings</h3>
<p>Joining many pieces with <code>+</code> is clumsy. A <strong>template string</strong> uses backticks (the key left of <code>1</code> on most keyboards) and puts values inside <code>${ }</code>:</p>
<pre><code>const name = "Auma";
const qty = 3;
const price = 2500;
console.log(`${name} bought ${qty} items for UGX ${qty * price}.`);
// Auma bought 3 items for UGX 7500.</code></pre>
<p>Anything valid as an expression may go inside <code>${ }</code>. Template strings can also span several lines.</p>

<h3>Comparison operators</h3>
<p>Comparisons give a boolean: <code>&gt;</code>, <code>&lt;</code>, <code>&gt;=</code>, <code>&lt;=</code>, <code>===</code> (equal) and <code>!==</code> (not equal). Always use the triple versions. The double <code>==</code> converts types first and gives odd results such as <code>"5" == 5</code> being true, while <code>"5" === 5</code> is false.</p>

<h3>Logical operators</h3>
<ul>
<li><code>&amp;&amp;</code> (and): true only if both sides are true.</li>
<li><code>||</code> (or): true if at least one side is true.</li>
<li><code>!</code> (not): flips true to false.</li>
</ul>
<pre><code>const age = 17;
const hasId = true;
console.log(age &gt;= 18 &amp;&amp; hasId);   // false
console.log(age &gt;= 18 || hasId);   // true
console.log(!hasId);               // false</code></pre>

<h3>Try it yourself</h3>
<ol>
<li>Write variables for the price of an item and a quantity, and print the total in a template string such as <code>Total: UGX 15000</code>.</li>
<li>Add a 10 percent discount using <code>total * 0.9</code> and print the new total.</li>
<li>Check whether a number is even using <code>%</code>, and print the boolean.</li>
<li>Predict, then test: <code>"10" + 5</code>, <code>"10" - 5</code>, <code>"10" === 10</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Arithmetic uses <code>+ - * / % **</code>; <code>+=</code> and <code>++</code> update variables.</li>
<li><code>+</code> joins text, which can surprise you when numbers are mixed with strings.</li>
<li>Template strings with backticks and <code>${ }</code> make clear sentences.</li>
<li>Use <code>===</code> and <code>!==</code> to compare; combine tests with <code>&amp;&amp;</code>, <code>||</code> and <code>!</code>.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does the console.log() function do?
- Saves a file to the computer
* Prints a value to the browser console so you can see it
- Changes the colour of the page
- Sends the page to a server
= console.log writes information to the console, which is the main way developers check what their code is doing.

Q: Which keyword creates a variable that cannot be reassigned?
- let
- var
* const
- change
= const makes a binding that cannot be given a new value. Use it by default.

Q: What is the value of the expression "Total: " + 5 + 5 ?
- Total: 10
* Total: 55
- 10
- It gives an error
= Once one side is text, + joins strings, so the two 5s are added to the text one after another.

Q: Which of these are valid variable names? (choose all that apply)
* shopName
* unit_price
- 2ndPrice
- let
= Names cannot start with a digit and cannot be a reserved word such as let.

Q: What does 17 % 5 give?
- 3.4
* 2
- 12
- 85
= The % operator returns the remainder of the division: 17 divided by 5 is 3 remainder 2.

Q: Why should you use === rather than ==?
- It is shorter to type
- It only works with numbers
* It compares without converting types, so results are predictable
- It changes the variable
= The triple equals compares value and type, avoiding surprises like "5" == 5 being true.

Q: A user types 250 into a text box. In JavaScript, what type is the value?
* A string
- A number
- A boolean
- Undefined
= Everything typed into form fields arrives as text, so convert with Number() when you need to calculate.
QUIZ,
        ],
        [
            'title'   => 'Module 2: Decisions, loops and functions',
            'summary' => 'Make code choose and repeat, package it in functions, and organise data with arrays and objects.',
            'lessons' => [
                [
                    'title'   => 'Making decisions with if, else and switch',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to make your program choose between actions using <code>if</code>, <code>else if</code>, <code>else</code> and <code>switch</code>. Decisions are what make software feel intelligent: a form that accepts or rejects, a shop that gives a discount only when you spend enough.</p>

<h3>The if statement</h3>
<p>An <code>if</code> statement runs a block of code only when a condition is true. The condition goes in round brackets and the block in curly brackets.</p>
<pre><code>const age = 19;
if (age &gt;= 18) {
  console.log("You can register to vote.");
}</code></pre>

<h3>else and else if</h3>
<p>Add <code>else</code> for what happens when the condition is false, and <code>else if</code> to test further conditions in order. JavaScript uses the first one that is true and skips the rest.</p>
<pre><code>const score = 68;
let grade;
if (score &gt;= 80) {
  grade = "A";
} else if (score &gt;= 70) {
  grade = "B";
} else if (score &gt;= 60) {
  grade = "C";
} else {
  grade = "Try again";
}
console.log(`Score ${score}: ${grade}`);   // Score 68: C</code></pre>
<p>Order matters. If you tested <code>score &gt;= 60</code> first, a score of 90 would wrongly get a C.</p>

<h3>Truthy and falsy values</h3>
<p>The condition does not have to be a true boolean. JavaScript treats some values as <strong>falsy</strong>: <code>false</code>, <code>0</code>, <code>""</code> (empty string), <code>null</code>, <code>undefined</code> and <code>NaN</code>. Everything else is <strong>truthy</strong>. This gives a short way to check for empty input:</p>
<pre><code>const name = "";
if (name) {
  console.log("Hello " + name);
} else {
  console.log("Please type your name.");
}</code></pre>

<h3>Combining conditions</h3>
<pre><code>const isMember = true;
const spent = 60000;
if (isMember &amp;&amp; spent &gt;= 50000) {
  console.log("10% discount");
}</code></pre>

<h3>The ternary operator</h3>
<p>For a simple two-way choice that produces a value, the <strong>ternary operator</strong> is a short form: <code>condition ? valueIfTrue : valueIfFalse</code>.</p>
<pre><code>const hour = 15;
const greeting = hour &lt; 12 ? "Good morning" : "Good afternoon";
console.log(greeting);   // Good afternoon</code></pre>

<h3>switch</h3>
<p>When you compare one value against many exact options, <code>switch</code> is tidier. Each <code>case</code> ends with <code>break</code>, otherwise execution "falls through" to the next case.</p>
<pre><code>const day = "Sat";
switch (day) {
  case "Sat":
  case "Sun":
    console.log("Weekend");
    break;
  case "Fri":
    console.log("Almost the weekend");
    break;
  default:
    console.log("Weekday");
}
// Weekend</code></pre>
<p>The <code>default</code> block runs when no case matches. Notice that <code>Sat</code> and <code>Sun</code> share one block by leaving out <code>break</code> on purpose.</p>

<h3>Try it yourself</h3>
<ol>
<li>Write a program with a <code>const temperature</code> that prints "Cold" under 18, "Warm" from 18 to 27, and "Hot" above 27.</li>
<li>Change the temperature several times and check each branch runs.</li>
<li>Rewrite a two-way choice from your program with the ternary operator.</li>
<li>Write a <code>switch</code> on a variable <code>network</code> ("MTN", "Airtel", "Other") that prints a different message for each.</li>
</ol>

<h3>Key points</h3>
<ul>
<li><code>if</code>, <code>else if</code> and <code>else</code> pick the first branch whose condition is true.</li>
<li>Falsy values are false, 0, empty string, null, undefined and NaN; the rest are truthy.</li>
<li>The ternary operator gives a compact two-way choice that returns a value.</li>
<li><code>switch</code> compares one value to many cases; remember <code>break</code> and <code>default</code>.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Loops: repeating work with for and while',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to repeat an action many times using <code>for</code> and <code>while</code> loops, and to stop or skip a repeat with <code>break</code> and <code>continue</code>. Loops let ten lines of code do the work of ten thousand: printing a class list, adding up a column of sales, checking every answer in a quiz.</p>

<h3>The for loop</h3>
<p>A <code>for</code> loop has three parts in its brackets: a start, a condition that keeps it going, and a step that runs after each round.</p>
<pre><code>for (let i = 1; i &lt;= 5; i++) {
  console.log("Round " + i);
}
// Round 1 ... Round 5</code></pre>
<p>Reading it aloud: start with <code>i</code> at 1; while <code>i</code> is at most 5, run the block; after each round add 1 to <code>i</code>. The variable <code>i</code> is a common name for the loop counter. The condition is checked before every round, so if it is false at the start, the block never runs.</p>

<h3>Adding things up</h3>
<p>A typical pattern is a variable that collects a result as the loop runs:</p>
<pre><code>let total = 0;
for (let i = 1; i &lt;= 10; i++) {
  total += i;
}
console.log(total);   // 55</code></pre>

<h3>The while loop</h3>
<p>Use <code>while</code> when you do not know in advance how many rounds are needed. It repeats as long as the condition is true.</p>
<pre><code>let savings = 1000;
let months = 0;
while (savings &lt; 5000) {
  savings = savings * 1.1;   // grows by 10% each month
  months++;
}
console.log(`It takes ${months} months.`);   // It takes 17 months.</code></pre>
<p>A warning: if nothing inside the loop ever makes the condition false, the loop never ends and the browser tab freezes. This is an <strong>infinite loop</strong>. If it happens, close the tab. Always check that something in the loop moves towards the end.</p>

<h3>break and continue</h3>
<p><code>break</code> leaves the loop immediately. <code>continue</code> skips the rest of the current round and starts the next one.</p>
<pre><code>for (let n = 1; n &lt;= 10; n++) {
  if (n % 2 === 0) continue;   // skip even numbers
  if (n &gt; 7) break;            // stop after 7
  console.log(n);
}
// 1 3 5 7</code></pre>

<h3>Looping over a list</h3>
<p>The <code>for...of</code> loop visits each item of an array, without a counter:</p>
<pre><code>const crops = ["maize", "beans", "cassava"];
for (const crop of crops) {
  console.log("Planting " + crop);
}</code></pre>
<p>Arrays are covered in detail in a later lesson, but this form is the one you will use most often.</p>

<h3>Nested loops</h3>
<p>A loop inside a loop runs the inner loop fully for each round of the outer one. This builds a multiplication table:</p>
<pre><code>for (let a = 1; a &lt;= 3; a++) {
  let line = "";
  for (let b = 1; b &lt;= 3; b++) {
    line += a * b + " ";
  }
  console.log(line);
}
// 1 2 3
// 2 4 6
// 3 6 9</code></pre>

<h3>Try it yourself</h3>
<ol>
<li>Write a <code>for</code> loop that prints the 7 times table from 7 x 1 to 7 x 12 using template strings.</li>
<li>Write a loop that adds the numbers 1 to 100 and prints 5050.</li>
<li>Use a <code>while</code> loop to find how many times you must double 50 before passing 1000.</li>
<li>Use <code>for...of</code> to print every item of an array of your five favourite foods.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A <code>for</code> loop has start, condition and step; use it when you know how many rounds.</li>
<li>A <code>while</code> loop repeats while a condition holds; make sure it can end.</li>
<li><code>break</code> stops a loop and <code>continue</code> skips to the next round.</li>
<li><code>for...of</code> is the simplest way to visit every item in an array.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Functions, arrow functions and scope',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write your own functions, use arrow function syntax, and explain where variables can and cannot be seen. A <strong>function</strong> is a named, reusable block of code. Instead of copying the same lines again and again, you write them once and call them whenever you need them.</p>

<h3>Declaring and calling a function</h3>
<pre><code>function greet(name) {
  return "Hello, " + name + "!";
}
const message = greet("Opio");
console.log(message);   // Hello, Opio!</code></pre>
<p>The names in the brackets (<code>name</code>) are <strong>parameters</strong>: placeholders. The values you pass when calling (<code>"Opio"</code>) are <strong>arguments</strong>. The <code>return</code> keyword sends a value back to the caller and ends the function. A function without <code>return</code> gives back <code>undefined</code>.</p>

<h3>A more useful example</h3>
<pre><code>function calculateTotal(price, quantity, discount = 0) {
  const subtotal = price * quantity;
  return subtotal - subtotal * discount;
}
console.log(calculateTotal(2000, 5));        // 10000
console.log(calculateTotal(2000, 5, 0.1));   // 9000</code></pre>
<p><code>discount = 0</code> is a <strong>default parameter</strong>: if the caller does not give a third argument, 0 is used. Good functions do one job and have a name that is a verb: <code>calculateTotal</code>, <code>isValidPhone</code>, <code>formatPrice</code>.</p>

<h3>Function expressions and arrow functions</h3>
<p>You can store a function in a variable. The modern short form is the <strong>arrow function</strong>, written with <code>=&gt;</code>:</p>
<pre><code>const square = function (n) {
  return n * n;
};
const squareArrow = (n) =&gt; n * n;          // one expression: return is implied
const add = (a, b) =&gt; {                   // braces: write return yourself
  const result = a + b;
  return result;
};
console.log(square(4), squareArrow(5), add(2, 3));   // 16 25 5</code></pre>
<p>Arrow functions are everywhere in modern JavaScript, especially as short inputs to other functions, which you will meet in the lesson on arrays.</p>

<h3>Scope: who can see which variable?</h3>
<p><strong>Scope</strong> means the area of code where a variable exists. Variables made with <code>let</code> and <code>const</code> live only inside the curly brackets where they were created (<strong>block scope</strong>). Variables made inside a function cannot be seen outside it.</p>
<pre><code>const shop = "Lumina Stores";       // global: visible everywhere

function showStock() {
  const stock = 40;                 // local: only inside the function
  console.log(shop, stock);         // works
}
showStock();
console.log(shop);                  // works
// console.log(stock);              // ReferenceError: stock is not defined</code></pre>
<p>A function can read variables from the outside, but outside code cannot read the function's own variables. Prefer passing values in as parameters and getting results out with <code>return</code>, rather than relying on global variables. It keeps functions predictable and easy to test.</p>

<h3>Try it yourself</h3>
<ol>
<li>Write a function <code>isEven(n)</code> that returns true when <code>n</code> is even. Test it with 4 and 7.</li>
<li>Write <code>formatPrice(amount)</code> that returns a string such as <code>UGX 15000</code>.</li>
<li>Rewrite <code>isEven</code> as an arrow function.</li>
<li>Declare a variable inside a function, then try to print it outside, and read the error.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Functions package code for reuse; parameters receive values and <code>return</code> sends a result back.</li>
<li>Default parameters supply a value when an argument is missing.</li>
<li>Arrow functions give a short syntax: <code>(a, b) =&gt; a + b</code>.</li>
<li>Variables exist only in the block or function where they are declared; keep functions free of hidden dependencies.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Arrays and objects with common methods',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to store lists of items in arrays and records in objects, and to process them with the methods <code>forEach</code>, <code>map</code>, <code>filter</code> and <code>find</code>. Almost all real data, such as a list of students or products, is held this way.</p>

<h3>Arrays</h3>
<p>An <strong>array</strong> is an ordered list written in square brackets. Items are numbered from 0.</p>
<pre><code>const fruits = ["mango", "pawpaw", "banana"];
console.log(fruits[0]);        // mango
console.log(fruits.length);    // 3
fruits.push("orange");         // add to the end
fruits.pop();                  // remove the last item
console.log(fruits.includes("banana"));   // true</code></pre>
<p>A <code>const</code> array can still be changed with <code>push</code> and <code>pop</code>: <code>const</code> only stops you from replacing the whole array with a different one.</p>

<h3>Objects</h3>
<p>An <strong>object</strong> groups related values under names (<strong>properties</strong>), written in curly brackets as <code>name: value</code> pairs.</p>
<pre><code>const student = {
  name: "Adong",
  age: 17,
  subjects: ["Maths", "ICT"]
};
console.log(student.name);          // Adong
console.log(student["age"]);        // 17
student.age = 18;                   // change
student.school = "Gulu High";       // add
console.log(student.subjects[1]);   // ICT</code></pre>

<h3>An array of objects</h3>
<p>Lists of records are the most common shape in web programming:</p>
<pre><code>const products = [
  { name: "Notebook", price: 2500, inStock: true },
  { name: "Pen", price: 500, inStock: true },
  { name: "Calculator", price: 25000, inStock: false },
  { name: "Ruler", price: 1000, inStock: true }
];</code></pre>

<h3>forEach: do something for each item</h3>
<pre><code>products.forEach((item) =&gt; {
  console.log(`${item.name}: UGX ${item.price}`);
});</code></pre>

<h3>map: transform every item</h3>
<p><code>map</code> returns a <em>new</em> array of the same length, with each item changed by your function.</p>
<pre><code>const names = products.map((item) =&gt; item.name);
console.log(names);   // ["Notebook", "Pen", "Calculator", "Ruler"]</code></pre>

<h3>filter: keep only some items</h3>
<p><code>filter</code> returns a new array containing only the items for which your function returns true.</p>
<pre><code>const available = products.filter((item) =&gt; item.inStock);
const cheap = products.filter((item) =&gt; item.price &lt;= 1000);
console.log(available.length, cheap.length);   // 3 2</code></pre>

<h3>find: get the first match</h3>
<p><code>find</code> returns the first matching item, or <code>undefined</code> if nothing matches.</p>
<pre><code>const pen = products.find((item) =&gt; item.name === "Pen");
console.log(pen.price);   // 500</code></pre>

<h3>reduce: combine into one value</h3>
<pre><code>const total = products.reduce((sum, item) =&gt; sum + item.price, 0);
console.log(total);   // 29000</code></pre>
<p>These methods can be chained: <code>products.filter((p) =&gt; p.inStock).map((p) =&gt; p.name)</code> gives the names of products in stock.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create an array of five student objects with <code>name</code> and <code>mark</code>.</li>
<li>Use <code>filter</code> to get students with a mark of 50 or more and print their names with <code>map</code>.</li>
<li>Use <code>find</code> to look up one student by name.</li>
<li>Use <code>reduce</code> to compute the class average (total divided by the array length).</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Arrays are numbered lists starting at 0; objects hold named properties.</li>
<li>An array of objects is the usual shape for lists of records.</li>
<li><code>forEach</code> runs code for each item; <code>map</code> transforms; <code>filter</code> selects; <code>find</code> returns the first match; <code>reduce</code> combines.</li>
<li><code>map</code> and <code>filter</code> return new arrays and leave the original unchanged.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does this print? const n = 5; if (n > 3) { console.log("A"); } else if (n > 1) { console.log("B"); } else { console.log("C"); }
* A
- B
- C
- A and B
= The first true condition wins: 5 is greater than 3, so A is printed and the rest are skipped.

Q: Which of these values is falsy?
- The string "0"
- The array []
* An empty string ""
- The string "false"
= An empty string is falsy. Non-empty strings (even "0" and "false") and arrays are truthy.

Q: What is the result of a for loop declared as for (let i = 0; i < 3; i++)? How many times does the body run?
- 2 times
* 3 times
- 4 times
- Forever
= i takes the values 0, 1 and 2, then the condition i < 3 fails, so the body runs 3 times.

Q: What is the difference between a parameter and an argument?
* A parameter is the placeholder in the function definition; an argument is the value passed when calling
- They are the same thing
- A parameter is the returned value
- An argument is a variable declared with const
= In function add(a, b), a and b are parameters; in add(2, 3), the values 2 and 3 are arguments.

Q: Which method creates a new array containing only the items that pass a test?
- map
- forEach
- find
* filter
= filter keeps items for which the test function returns true. map changes every item, and find returns just one item.

Q: What happens if you try to read a variable declared with const inside a function, from outside that function?
- It returns undefined
- It returns null
* A ReferenceError, because it is out of scope
- It returns 0
= Variables exist only in the function or block where they were declared.

Q: Which statements about arrow functions are true? (choose all that apply)
* (a, b) => a + b returns the sum
* They are written with =>
- They can only be used inside loops
- They cannot take parameters
= Arrow functions are a short syntax for functions. With one expression and no braces, the value is returned automatically.

Q: Given const items = [10, 20, 30], what does items.map((x) => x * 2) return?
- 60
- [10, 20, 30]
* [20, 40, 60]
- 20
= map returns a new array with each item transformed by the function.
QUIZ,
        ],
        [
            'title'   => 'Module 3: The DOM: controlling the page',
            'summary' => 'Select and change elements, create new ones, react to events and handle and validate forms.',
            'lessons' => [
                [
                    'title'   => 'Selecting elements and changing the page',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to find elements on a page and change their text, attributes, classes and styles from JavaScript. This is the step where code stops being exercises in the console and starts changing something visitors can see.</p>

<h3>What is the DOM?</h3>
<p>When the browser loads your HTML, it builds a tree of objects in memory called the <strong>DOM</strong> (Document Object Model). Every tag becomes an object, nested inside its parent. JavaScript can read and change this tree through the global object <code>document</code>, and the page on screen updates immediately. Changing the DOM does not change your HTML file; it changes the live page until it is reloaded.</p>
<p>Use this small page for the lesson. Save it as <code>dom.html</code> next to a file <code>dom.js</code>:</p>
<pre><code>&lt;h1 id="title"&gt;Market prices&lt;/h1&gt;
&lt;p class&#61;"note"&gt;Prices are in UGX.&lt;/p&gt;
&lt;ul id="list"&gt;
  &lt;li&gt;Tomatoes&lt;/li&gt;
  &lt;li&gt;Onions&lt;/li&gt;
  &lt;li&gt;Cabbage&lt;/li&gt;
&lt;/ul&gt;
&lt;script src="dom.js"&gt;&lt;/script&gt;</code></pre>

<h3>Selecting elements</h3>
<p>The most flexible tools take a CSS selector, exactly as you wrote in your stylesheets:</p>
<pre><code>const title = document.querySelector("#title");        // first match
const note = document.querySelector(".note");
const items = document.querySelectorAll("#list li");   // all matches
console.log(items.length);   // 3
items.forEach((li) =&gt; console.log(li.textContent));</code></pre>
<p><code>querySelector</code> returns the first matching element, or <code>null</code> if none exists. <code>querySelectorAll</code> returns a list of all matches (it can be looped with <code>forEach</code> or <code>for...of</code>). Older code uses <code>getElementById</code>, which also works well for ids.</p>

<h3>Changing text and HTML</h3>
<pre><code>title.textContent = "Gulu market prices";
note.innerHTML = "Prices are in &lt;strong&gt;UGX&lt;/strong&gt;.";</code></pre>
<p><code>textContent</code> sets plain text and is the safe choice. <code>innerHTML</code> reads the text as HTML, which is powerful but dangerous when the value comes from a user, because a visitor could inject harmful code. Use <code>textContent</code> unless you really need markup, and never put untrusted input into <code>innerHTML</code>.</p>

<h3>Changing attributes, classes and styles</h3>
<pre><code>const link = document.querySelector("a");
link.setAttribute("href", "https://example.com");

note.classList.add("highlight");      // add a CSS class
note.classList.remove("highlight");   // remove it
note.classList.toggle("highlight");   // add if missing, remove if present

title.style.color = "darkgreen";      // quick inline change
title.style.backgroundColor = "#eef";</code></pre>
<p>Notice that CSS names with hyphens become camelCase in JavaScript (<code>background-color</code> becomes <code>backgroundColor</code>). The best practice is to define the look in your CSS file as a class, and use <code>classList</code> to switch it on and off, rather than setting many inline styles. That keeps appearance in CSS and behaviour in JavaScript.</p>

<h3>Reading values back</h3>
<p>You can also read: <code>title.textContent</code>, <code>link.getAttribute("href")</code>, and <code>note.classList.contains("highlight")</code> (true or false). For form fields you read <code>input.value</code>, which we use in a later lesson.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create <code>dom.html</code> and <code>dom.js</code> as above and open the page.</li>
<li>In <code>dom.js</code>, change the heading text and make the note paragraph italic using <code>style.fontStyle</code>.</li>
<li>Loop over all <code>li</code> items and add the text " - fresh" to each with <code>textContent +=</code>.</li>
<li>Add a CSS rule for <code>.highlight</code> in a style block, then add that class to the second list item.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>The DOM is the live tree of objects representing the page; <code>document</code> is the way in.</li>
<li><code>querySelector</code> gets one element and <code>querySelectorAll</code> gets all matches using CSS selectors.</li>
<li>Use <code>textContent</code> for text; avoid <code>innerHTML</code> with untrusted data.</li>
<li>Switch looks with <code>classList</code>; use <code>setAttribute</code> for attributes.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Creating and removing elements',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build new page elements in JavaScript, add them to the page, and remove them again. This is how a to-do list grows when you add a task, or how a list of products appears from data.</p>

<h3>Create, fill, attach</h3>
<p>There are three steps: create the element in memory, set its content, then attach it to an element already on the page.</p>
<pre><code>&lt;ul id="fruits"&gt;&lt;/ul&gt;
&lt;script&gt;
  const list = document.querySelector("#fruits");
  const li = document.createElement("li");   // 1 create
  li.textContent = "Mango";                  // 2 fill
  list.appendChild(li);                      // 3 attach (at the end)
&lt;/script&gt;</code></pre>
<p>Until you attach it, the new element does not appear anywhere. Useful placement methods are <code>appendChild</code> and <code>append</code> (to the end), <code>prepend</code> (to the start), and <code>before</code> and <code>after</code> (next to an existing element).</p>

<h3>Building a list from data</h3>
<p>Combine loops and arrays with element creation to display data on the page. This pattern appears in almost every web app:</p>
<pre><code>const products = [
  { name: "Notebook", price: 2500 },
  { name: "Pen", price: 500 },
  { name: "Ruler", price: 1000 }
];
const ul = document.querySelector("#fruits");
products.forEach((p) =&gt; {
  const item = document.createElement("li");
  item.textContent = `${p.name} - UGX ${p.price}`;
  ul.appendChild(item);
});</code></pre>

<h3>Giving new elements classes and attributes</h3>
<pre><code>const card = document.createElement("div");
card.classList.add("card");
const img = document.createElement("img");
img.src = "photo.jpg";
img.alt = "A farmer holding maize";   // always give images alt text
card.appendChild(img);
document.body.appendChild(card);</code></pre>
<p>Notice that common attributes such as <code>src</code> and <code>alt</code> can be set as properties directly. Never skip <code>alt</code> text: screen reader users depend on it.</p>

<h3>Removing and clearing</h3>
<pre><code>const first = document.querySelector("#fruits li");
first.remove();               // remove one element

ul.textContent = "";          // clear all children quickly</code></pre>

<h3>Why not always use innerHTML?</h3>
<p>You will see code like <code>ul.innerHTML += "&lt;li&gt;" + text + "&lt;/li&gt;"</code>. It is short, but it has two problems. First, if <code>text</code> comes from a user, they can type HTML or script and your page will run it (a security flaw called cross-site scripting). Second, each use rebuilds all the existing elements, which loses things like typed values and attached events. Creating elements with <code>createElement</code> and <code>textContent</code> avoids both problems.</p>

<h3>Templates for repeated markup</h3>
<p>If the structure is complex, write a function that builds and returns one element, then call it for each item:</p>
<pre><code>function createCard(product) {
  const card = document.createElement("article");
  const heading = document.createElement("h3");
  heading.textContent = product.name;
  const price = document.createElement("p");
  price.textContent = `UGX ${product.price}`;
  card.append(heading, price);
  return card;
}
products.forEach((p) =&gt; document.body.append(createCard(p)));</code></pre>
<p>The <code>append</code> method accepts several elements at once, which makes this tidy.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create a page with an empty <code>ul</code> and an array of five school subjects. Display each as a list item.</li>
<li>Add a <code>createCard(subject)</code> function that builds an <code>article</code> with a heading and a paragraph.</li>
<li>Remove the last list item with <code>remove()</code> after the page loads, and confirm it disappears.</li>
<li>Add a class to every new item and style it in CSS.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Create with <code>createElement</code>, fill with <code>textContent</code>, attach with <code>append</code> or <code>appendChild</code>.</li>
<li>Build lists by looping over an array and creating one element per item.</li>
<li>Use <code>remove()</code> to delete an element and <code>textContent = ""</code> to clear a container.</li>
<li>Prefer <code>createElement</code> to <code>innerHTML</code> for user data, for safety.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Events: click, input and keyboard',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to make a page respond to what the visitor does: clicking buttons, typing in a box and pressing keys. An <strong>event</strong> is something that happens in the browser, and an <strong>event listener</strong> is a function you attach to wait for it.</p>

<h3>addEventListener</h3>
<p>The standard way to listen is <code>addEventListener</code>, which takes the event name and a function to run (the <strong>handler</strong>).</p>
<pre><code>&lt;button id="btn"&gt;Click me&lt;/button&gt;
&lt;p id="out"&gt;Clicks: 0&lt;/p&gt;
&lt;script&gt;
  const btn = document.querySelector("#btn");
  const out = document.querySelector("#out");
  let clicks = 0;
  btn.addEventListener("click", () =&gt; {
    clicks++;
    out.textContent = `Clicks: ${clicks}`;
  });
&lt;/script&gt;</code></pre>
<p>Notice that you pass the function itself, without brackets after it. Writing <code>btn.addEventListener("click", handleClick())</code> would run the function immediately, a very common mistake.</p>

<h3>The event object</h3>
<p>The browser gives your handler an <strong>event object</strong> with details about what happened. Name it <code>event</code> or <code>e</code>.</p>
<pre><code>document.addEventListener("keydown", (event) =&gt; {
  console.log("You pressed:", event.key);
  if (event.key === "Escape") {
    console.log("Closing...");
  }
});</code></pre>
<p>Useful properties include <code>event.target</code> (the element that was clicked) and <code>event.key</code> (the key pressed).</p>

<h3>The input event: reacting as people type</h3>
<pre><code>&lt;input type="text" id="name" placeholder="Your name"&gt;
&lt;p id="hello"&gt;&lt;/p&gt;
&lt;script&gt;
  const nameBox = document.querySelector("#name");
  const hello = document.querySelector("#hello");
  nameBox.addEventListener("input", () =&gt; {
    hello.textContent = nameBox.value ? `Hello, ${nameBox.value}!` : "";
  });
&lt;/script&gt;</code></pre>
<p>The <code>input</code> event fires on every change, so the greeting updates letter by letter. The <code>value</code> property holds the current text (always a string). Another event, <code>change</code>, fires when the user finishes editing (for example leaves the box or picks a menu option).</p>

<h3>Events for many items: delegation</h3>
<p>If you have a list with many buttons, adding a listener to each is wasteful, especially when items are added later. Instead, listen once on the parent and check <code>event.target</code>:</p>
<pre><code>const list = document.querySelector("#tasks");
list.addEventListener("click", (event) =&gt; {
  if (event.target.matches("button.delete")) {
    event.target.parentElement.remove();
  }
});</code></pre>
<p>Click events bubble up from the clicked element to its parents, so the list hears clicks from buttons inside it, including buttons created later. This is called <strong>event delegation</strong>.</p>

<h3>Other common events</h3>
<table>
<thead><tr><th>Event</th><th>When it fires</th></tr></thead>
<tbody>
<tr><td><code>click</code></td><td>Mouse click or tap, or Enter on a focused button</td></tr>
<tr><td><code>input</code></td><td>Value of a field changes</td></tr>
<tr><td><code>submit</code></td><td>A form is submitted</td></tr>
<tr><td><code>keydown</code></td><td>A key is pressed</td></tr>
<tr><td><code>DOMContentLoaded</code></td><td>The HTML has been fully read</td></tr>
</tbody>
</table>

<h3>Try it yourself</h3>
<ol>
<li>Build a button that toggles a paragraph between visible and hidden using <code>classList.toggle("hidden")</code> and a CSS rule that sets <code>display: none</code> for <code>.hidden</code>.</li>
<li>Add a text box and a paragraph that shows how many characters have been typed, using the <code>input</code> event and <code>value.length</code>.</li>
<li>Print <code>event.key</code> to the console whenever a key is pressed.</li>
<li>Make a button change the page background colour each time it is clicked, cycling through three colours in an array.</li>
</ol>

<h3>Key points</h3>
<ul>
<li><code>addEventListener(name, handler)</code> attaches behaviour; pass the function without calling it.</li>
<li>The event object tells you what happened (<code>target</code>, <code>key</code>).</li>
<li>Use <code>input</code> for live typing and <code>click</code> for buttons.</li>
<li>Event delegation: one listener on a parent handles all children, even new ones.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Handling and validating forms',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to read the values of a form, stop the page reloading, and check the input with your own rules. Form checking gives a visitor quick, friendly feedback, for example "this phone number is too short", before anything is sent anywhere.</p>

<h3>A form to work with</h3>
<pre><code>&lt;form id="signup" novalidate&gt;
  &lt;label for="fullName"&gt;Full name&lt;/label&gt;
  &lt;input id="fullName" name="fullName" type="text"&gt;
  &lt;p id="nameError" role="alert"&gt;&lt;/p&gt;

  &lt;label for="phone"&gt;Phone (10 digits)&lt;/label&gt;
  &lt;input id="phone" name="phone" type="tel"&gt;
  &lt;p id="phoneError" role="alert"&gt;&lt;/p&gt;

  &lt;button type="submit"&gt;Register&lt;/button&gt;
  &lt;p id="result"&gt;&lt;/p&gt;
&lt;/form&gt;</code></pre>
<p>Every input has a <code>label</code> linked by <code>for</code> and <code>id</code>, so screen readers and touch users can tap the label. The attribute <code>novalidate</code> turns off the browser's default bubbles so we can show our own messages. Without JavaScript, HTML alone can also check with attributes such as <code>required</code>, <code>minlength</code> and <code>type="email"</code>; use those too whenever possible.</p>

<h3>Listening for submit</h3>
<p>Listen for the <code>submit</code> event on the form, not a click on the button, because forms can also be sent by pressing Enter. Call <code>event.preventDefault()</code> to stop the browser reloading the page.</p>
<pre><code>const form = document.querySelector("#signup");
form.addEventListener("submit", (event) =&gt; {
  event.preventDefault();
  const fullName = document.querySelector("#fullName").value.trim();
  const phone = document.querySelector("#phone").value.trim();
  console.log(fullName, phone);
});</code></pre>
<p><code>trim()</code> removes spaces at the start and end, which people often type by accident.</p>

<h3>Writing validation functions</h3>
<p>Keep each rule in a small function that returns an error message, or an empty string when the value is fine:</p>
<pre><code>function checkName(value) {
  if (value === "") return "Please enter your name.";
  if (value.length &lt; 2) return "The name is too short.";
  return "";
}

function checkPhone(value) {
  if (!/^[0-9]{10}$/.test(value)) {
    return "Enter exactly 10 digits, for example 0771234567.";
  }
  return "";
}</code></pre>
<p>The pattern <code>/^[0-9]{10}$/</code> is a <strong>regular expression</strong>: it matches text that is exactly ten digits from start (<code>^</code>) to end (<code>$</code>). The method <code>test</code> returns true or false.</p>

<h3>Showing the result</h3>
<pre><code>form.addEventListener("submit", (event) =&gt; {
  event.preventDefault();
  const fullName = document.querySelector("#fullName").value.trim();
  const phone = document.querySelector("#phone").value.trim();

  const nameMsg = checkName(fullName);
  const phoneMsg = checkPhone(phone);
  document.querySelector("#nameError").textContent = nameMsg;
  document.querySelector("#phoneError").textContent = phoneMsg;

  if (nameMsg === "" &amp;&amp; phoneMsg === "") {
    document.querySelector("#result").textContent = `Thank you, ${fullName}.`;
    form.reset();
  }
});</code></pre>
<p>The <code>role="alert"</code> on the message paragraphs tells screen readers to announce a message when it appears. After success, <code>form.reset()</code> empties the fields.</p>

<h3>Always validate on the server too</h3>
<p>Checking in the browser is for convenience. A determined person can bypass it, so any real system must repeat the checks on the server. Never treat front-end validation as security.</p>

<h3>Try it yourself</h3>
<ol>
<li>Build the form above and test it with empty input, a short name and a phone number with letters.</li>
<li>Add an email field with a function that returns an error unless the value contains an <code>@</code> and a dot after it.</li>
<li>Add a password field that must have at least 8 characters, and a second field that must match.</li>
<li>Clear the error messages when the user starts typing again, using the <code>input</code> event.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Listen for <code>submit</code> on the form and call <code>event.preventDefault()</code>.</li>
<li>Read values with <code>.value</code>, and clean them with <code>trim()</code>.</li>
<li>Write small validation functions that return a clear, kind message.</li>
<li>Browser checks are a convenience; the server must validate again.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does document.querySelector("#title") return?
- All elements with the id title
* The first element matching the selector, or null if none matches
- The text of the element
- A new element
= querySelector returns the first matching element, using the same selectors as CSS.

Q: Which property is the safest for putting text from a user onto the page?
- innerHTML
* textContent
- outerHTML
- document.write
= textContent treats the value as plain text, so injected HTML or scripts are not run.

Q: What are the three steps to add a new list item to the page?
- Select it, hide it, delete it
* Create the element, set its content, attach it to a parent
- Write HTML, save the file, reload the page
- Declare, assign, return
= createElement, then textContent, then append or appendChild.

Q: What is wrong with btn.addEventListener("click", handleClick())?
- Nothing, it is correct
* The brackets run the function immediately instead of passing it to be called on click
- The event name is wrong
- You must use var
= Pass the function name without brackets so the browser can call it later.

Q: Why call event.preventDefault() in a submit handler?
- To make the form faster
* To stop the browser from reloading the page so your code can handle the data
- To delete the form
- To hide the button
= By default, submitting a form reloads the page. preventDefault stops that.

Q: What is event delegation?
- Giving events to another page
* Listening once on a parent element and using event.target to handle clicks from its children
- Removing all events
- Using many setTimeout calls
= Events bubble upwards, so a parent can handle events from children, including ones added later.

Q: Which are good reasons to validate a form in JavaScript? (choose all that apply)
* It gives quick feedback to the visitor
* It reduces mistakes before data is sent
- It replaces the need for server-side validation
- It makes the site secure on its own
= Front-end checks help users but can be bypassed, so the server must always validate as well.
QUIZ,
        ],
        [
            'title'   => 'Module 4: Building small interactive features',
            'summary' => 'Put everything together in four mini projects: a to-do list that remembers its tasks, a tip calculator, a quiz and a tabbed gallery.',
            'lessons' => [
                [
                    'title'   => 'Project: a to-do list that saves with localStorage',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>In this lesson you will build a complete to-do list: add tasks, tick them off, delete them, and keep them even after the page is closed. You will practise arrays, objects, events, DOM creation and a new tool, <code>localStorage</code>.</p>

<h3>The page</h3>
<pre><code>&lt;h1&gt;My tasks&lt;/h1&gt;
&lt;form id="taskForm"&gt;
  &lt;label for="taskInput"&gt;New task&lt;/label&gt;
  &lt;input id="taskInput" type="text" required&gt;
  &lt;button type="submit"&gt;Add&lt;/button&gt;
&lt;/form&gt;
&lt;ul id="taskList"&gt;&lt;/ul&gt;
&lt;script src="todo.js"&gt;&lt;/script&gt;</code></pre>

<h3>What is localStorage?</h3>
<p><code>localStorage</code> is a small store (about 5 MB) inside the visitor's browser. It keeps text under a name and remembers it after the tab or browser is closed. It can only store strings, so we convert arrays and objects to text with <code>JSON.stringify</code> and back with <code>JSON.parse</code>.</p>
<pre><code>localStorage.setItem("city", "Gulu");
console.log(localStorage.getItem("city"));   // Gulu
localStorage.removeItem("city");

const data = [{ text: "Buy salt", done: false }];
localStorage.setItem("tasks", JSON.stringify(data));
const back = JSON.parse(localStorage.getItem("tasks"));
console.log(back[0].text);   // Buy salt</code></pre>
<p>Do not store passwords or private information in <code>localStorage</code>; anyone using the same browser, or any script on the page, can read it.</p>

<h3>The code: todo.js</h3>
<pre><code>const form = document.querySelector("#taskForm");
const input = document.querySelector("#taskInput");
const list = document.querySelector("#taskList");

let tasks = loadTasks();
render();

function loadTasks() {
  const saved = localStorage.getItem("tasks");
  return saved ? JSON.parse(saved) : [];
}

function saveTasks() {
  localStorage.setItem("tasks", JSON.stringify(tasks));
}

function render() {
  list.textContent = "";
  tasks.forEach((task, index) =&gt; {
    const li = document.createElement("li");

    const box = document.createElement("input");
    box.type = "checkbox";
    box.checked = task.done;
    box.setAttribute("aria-label", `Mark done: ${task.text}`);
    box.addEventListener("change", () =&gt; {
      tasks[index].done = box.checked;
      saveTasks();
      render();
    });

    const label = document.createElement("span");
    label.textContent = task.text;
    if (task.done) label.style.textDecoration = "line-through";

    const del = document.createElement("button");
    del.type = "button";
    del.textContent = "Delete";
    del.addEventListener("click", () =&gt; {
      tasks.splice(index, 1);
      saveTasks();
      render();
    });

    li.append(box, label, del);
    list.appendChild(li);
  });
}

form.addEventListener("submit", (event) =&gt; {
  event.preventDefault();
  const text = input.value.trim();
  if (text === "") return;
  tasks.push({ text: text, done: false });
  saveTasks();
  render();
  input.value = "";
  input.focus();
});</code></pre>

<h3>How it works</h3>
<p>The program keeps one source of truth: the <code>tasks</code> array. Every change follows the same three steps: change the array, save it, redraw the list with <code>render()</code>. The <code>render</code> function clears the list and rebuilds it from the array, so the screen can never disagree with the data. The array method <code>splice(index, 1)</code> removes one item at a position. The first line of the submit handler stops the page reloading, and <code>return</code> exits early when the box is empty. After adding, <code>input.focus()</code> puts the cursor back in the box so the user can type the next task at once.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create the HTML and <code>todo.js</code>, then add three tasks, tick one and refresh the page. Confirm that they are still there.</li>
<li>Open the browser tools (F12), go to the <strong>Application</strong> tab (Storage in Firefox), find Local Storage and see your saved data.</li>
<li>Add a paragraph that shows how many tasks are not yet done, updated inside <code>render()</code>.</li>
<li>Add a "Clear completed" button that removes all done tasks with <code>filter</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li><code>localStorage</code> keeps strings in the browser; use <code>JSON.stringify</code> and <code>JSON.parse</code> for arrays and objects.</li>
<li>Keep data in one array and redraw the page from it with a <code>render()</code> function.</li>
<li>Every change: update the data, save, render.</li>
<li>Never keep secrets in <code>localStorage</code>.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Project: a tip and bill calculator',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>In this project you will build a calculator that takes a bill amount, a tip percentage and a number of people, then shows the tip, the total and the amount each person pays. It practises reading numbers from inputs, validating them, formatting results and updating the page live.</p>

<h3>The page</h3>
<pre><code>&lt;h1&gt;Tip calculator&lt;/h1&gt;
&lt;form id="calc"&gt;
  &lt;label for="bill"&gt;Bill (UGX)&lt;/label&gt;
  &lt;input id="bill" type="number" min="0" step="100"&gt;

  &lt;label for="tip"&gt;Tip (%)&lt;/label&gt;
  &lt;select id="tip"&gt;
    &lt;option value="0"&gt;No tip&lt;/option&gt;
    &lt;option value="5"&gt;5%&lt;/option&gt;
    &lt;option value="10" selected&gt;10%&lt;/option&gt;
    &lt;option value="15"&gt;15%&lt;/option&gt;
  &lt;/select&gt;

  &lt;label for="people"&gt;People&lt;/label&gt;
  &lt;input id="people" type="number" min="1" value="1"&gt;
&lt;/form&gt;
&lt;p id="message" role="status"&gt;&lt;/p&gt;
&lt;ul id="results"&gt;&lt;/ul&gt;
&lt;script src="tip.js"&gt;&lt;/script&gt;</code></pre>
<p>The <code>role="status"</code> attribute asks screen readers to announce changes politely. Using <code>type="number"</code> gives phone users a number keyboard.</p>

<h3>Planning the logic</h3>
<p>Before coding, write the steps in plain words: read the three values; if the bill is not a positive number, show a message and stop; otherwise compute the tip as bill times percent divided by 100; the total is the bill plus the tip; each person pays the total divided by the number of people; show all three results.</p>

<h3>The code: tip.js</h3>
<pre><code>const billInput = document.querySelector("#bill");
const tipSelect = document.querySelector("#tip");
const peopleInput = document.querySelector("#people");
const message = document.querySelector("#message");
const results = document.querySelector("#results");

function calculate(bill, tipPercent, people) {
  const tip = bill * tipPercent / 100;
  const total = bill + tip;
  return { tip: tip, total: total, perPerson: total / people };
}

function formatUGX(amount) {
  return "UGX " + Math.round(amount).toLocaleString("en-UG");
}

function update() {
  const bill = Number(billInput.value);
  const tipPercent = Number(tipSelect.value);
  const people = Math.floor(Number(peopleInput.value));

  results.textContent = "";
  if (billInput.value === "" || !(bill &gt; 0)) {
    message.textContent = "Enter a bill amount greater than zero.";
    return;
  }
  if (!(people &gt;= 1)) {
    message.textContent = "The number of people must be at least 1.";
    return;
  }
  message.textContent = "";

  const r = calculate(bill, tipPercent, people);
  const lines = [
    `Tip: ${formatUGX(r.tip)}`,
    `Total: ${formatUGX(r.total)}`,
    `Each person pays: ${formatUGX(r.perPerson)}`
  ];
  lines.forEach((text) =&gt; {
    const li = document.createElement("li");
    li.textContent = text;
    results.appendChild(li);
  });
}

[billInput, tipSelect, peopleInput].forEach((field) =&gt; {
  field.addEventListener("input", update);
});
update();</code></pre>

<h3>Notes on the code</h3>
<p>The <code>calculate</code> function does only maths and knows nothing about the page, so it is easy to test: <code>calculate(50000, 10, 5)</code> returns a tip of 5000, a total of 55000 and 11000 per person. The <code>update</code> function reads inputs, validates, and shows results. The test <code>!(bill &gt; 0)</code> catches zero, negative numbers and <code>NaN</code> in one check, because any comparison with <code>NaN</code> is false. <code>Math.round</code> rounds to the nearest whole shilling and <code>toLocaleString</code> adds thousands separators. A <code>select</code> menu also fires the <code>input</code> event when its choice changes, so one listener covers all three fields. Separating calculating, formatting and displaying is a habit that makes bigger programs manageable.</p>

<h3>Try it yourself</h3>
<ol>
<li>Build the calculator and test with a bill of 50000, 10 percent, and 5 people. You should see 11,000 each.</li>
<li>Try a negative bill, an empty bill and zero people to confirm each shows a helpful message.</li>
<li>Add a custom tip option: a number input for "Other %", used instead of the menu when filled.</li>
<li>Show a fifth line that rounds each person's share up to the nearest 100 shillings using <code>Math.ceil(x / 100) * 100</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Inputs always give strings; convert with <code>Number()</code> and validate before calculating.</li>
<li>Keep calculation in a pure function and display in another function.</li>
<li><code>!(x &gt; 0)</code> is a compact check that also catches <code>NaN</code>.</li>
<li>Use the <code>input</code> event to update results live.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Project: a multiple-choice quiz',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>In this project you will build a quiz that shows one question at a time, records the answers, and shows a score at the end. The data lives in an array of objects, and the page is drawn from it, a pattern that scales from this small quiz to large applications.</p>

<h3>The data</h3>
<p>Store the questions as data, separate from the code that displays them. Adding a question later then means adding an object, not changing logic.</p>
<pre><code>const questions = [
  {
    text: "Which language adds behaviour to a web page?",
    options: ["HTML", "CSS", "JavaScript"],
    answer: 2
  },
  {
    text: "Which keyword makes a variable that cannot be reassigned?",
    options: ["let", "const", "var"],
    answer: 1
  },
  {
    text: "What does === check?",
    options: ["Value only", "Value and type", "Nothing"],
    answer: 1
  }
];</code></pre>
<p>The <code>answer</code> is the position of the correct option (starting from 0).</p>

<h3>The page</h3>
<pre><code>&lt;h1&gt;JavaScript quiz&lt;/h1&gt;
&lt;p id="progress"&gt;&lt;/p&gt;
&lt;h2 id="question"&gt;&lt;/h2&gt;
&lt;div id="options"&gt;&lt;/div&gt;
&lt;p id="feedback" role="status"&gt;&lt;/p&gt;
&lt;button id="next" type="button" hidden&gt;Next&lt;/button&gt;
&lt;script src="quiz.js"&gt;&lt;/script&gt;</code></pre>

<h3>The logic: quiz.js</h3>
<p>Put the <code>questions</code> array at the top of the file, then add:</p>
<pre><code>const progress = document.querySelector("#progress");
const questionEl = document.querySelector("#question");
const optionsEl = document.querySelector("#options");
const feedback = document.querySelector("#feedback");
const nextBtn = document.querySelector("#next");

let current = 0;
let score = 0;

function showQuestion() {
  const q = questions[current];
  progress.textContent = `Question ${current + 1} of ${questions.length}`;
  questionEl.textContent = q.text;
  optionsEl.textContent = "";
  feedback.textContent = "";
  nextBtn.hidden = true;

  q.options.forEach((option, index) =&gt; {
    const btn = document.createElement("button");
    btn.type = "button";
    btn.textContent = option;
    btn.addEventListener("click", () =&gt; choose(index));
    optionsEl.appendChild(btn);
  });
}

function choose(index) {
  const q = questions[current];
  if (index === q.answer) {
    score++;
    feedback.textContent = "Correct!";
  } else {
    feedback.textContent = `Not quite. The answer is: ${q.options[q.answer]}.`;
  }
  optionsEl.querySelectorAll("button").forEach((b) =&gt; {
    b.disabled = true;
  });
  nextBtn.textContent = current === questions.length - 1 ? "See my score" : "Next";
  nextBtn.hidden = false;
  nextBtn.focus();
}

nextBtn.addEventListener("click", () =&gt; {
  current++;
  if (current &lt; questions.length) {
    showQuestion();
  } else {
    showResult();
  }
});

function showResult() {
  const percent = Math.round((score / questions.length) * 100);
  progress.textContent = "";
  questionEl.textContent = "Finished!";
  optionsEl.textContent = "";
  nextBtn.hidden = true;
  feedback.textContent = `You scored ${score} out of ${questions.length} (${percent}%).`;
}

showQuestion();</code></pre>

<h3>Walking through it</h3>
<p>Two variables describe the whole state: <code>current</code> (which question) and <code>score</code>. <code>showQuestion</code> draws the current question; <code>choose</code> checks the answer, gives feedback, disables the buttons so a learner cannot answer twice, and reveals the Next button; the Next button moves on or finishes. The <code>hidden</code> attribute hides an element without CSS. Moving keyboard focus to the Next button with <code>focus()</code> helps keyboard and screen reader users carry on without hunting for it.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create the quiz files and play it through. Check that the score is right when you answer all correctly and all wrongly.</li>
<li>Add three more questions of your own about web development; you should only need to edit the array.</li>
<li>Add a "Try again" button on the result screen that resets <code>current</code> and <code>score</code> and calls <code>showQuestion()</code>.</li>
<li>Challenge: shuffle the questions at the start of each attempt.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Keep content as data (an array of objects) and draw the page from it.</li>
<li>A small set of state variables, here <code>current</code> and <code>score</code>, drives the whole interface.</li>
<li>Disable buttons after an answer to prevent double answers.</li>
<li>Manage focus and use <code>role="status"</code> so the quiz works for keyboard and screen reader users.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Project: tabs and an image gallery',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>In this lesson you will build two widgets found on many websites: a set of tabs that shows one panel at a time, and an image gallery with thumbnails that change a large picture. Both rely on the same idea: change which element is "active" and let CSS do the showing and hiding.</p>

<h3>Tabs: the HTML</h3>
<pre><code>&lt;div id="tabs"&gt;
  &lt;div role="tablist" aria-label="About us"&gt;
    &lt;button role="tab" id="tab-1" aria-controls="panel-1" aria-selected="true"&gt;Mission&lt;/button&gt;
    &lt;button role="tab" id="tab-2" aria-controls="panel-2" aria-selected="false" tabindex="-1"&gt;Programs&lt;/button&gt;
    &lt;button role="tab" id="tab-3" aria-controls="panel-3" aria-selected="false" tabindex="-1"&gt;Contact&lt;/button&gt;
  &lt;/div&gt;
  &lt;div role="tabpanel" id="panel-1" aria-labelledby="tab-1"&gt;We help young people learn.&lt;/div&gt;
  &lt;div role="tabpanel" id="panel-2" aria-labelledby="tab-2" hidden&gt;Skills training and mentoring.&lt;/div&gt;
  &lt;div role="tabpanel" id="panel-3" aria-labelledby="tab-3" hidden&gt;Visit us in Gulu.&lt;/div&gt;
&lt;/div&gt;</code></pre>
<p>The <code>role</code> and <code>aria-</code> attributes describe the widget to assistive technology: which buttons are tabs, which one is selected, and which panel each controls. We hide inactive panels with the <code>hidden</code> attribute. Only the selected tab is in the keyboard tab order (<code>tabindex="-1"</code> on the others), and arrow keys move between tabs.</p>

<h3>Tabs: the JavaScript</h3>
<pre><code>const tabs = Array.from(document.querySelectorAll('[role="tab"]'));

function selectTab(selected) {
  tabs.forEach((tab) =&gt; {
    const isSelected = tab === selected;
    tab.setAttribute("aria-selected", String(isSelected));
    tab.tabIndex = isSelected ? 0 : -1;
    document.getElementById(tab.getAttribute("aria-controls")).hidden = !isSelected;
  });
  selected.focus();
}

tabs.forEach((tab, index) =&gt; {
  tab.addEventListener("click", () =&gt; selectTab(tab));
  tab.addEventListener("keydown", (event) =&gt; {
    if (event.key === "ArrowRight") {
      selectTab(tabs[(index + 1) % tabs.length]);
    } else if (event.key === "ArrowLeft") {
      selectTab(tabs[(index - 1 + tabs.length) % tabs.length]);
    }
  });
});</code></pre>
<p>The percent sign (<code>%</code>) wraps around: after the last tab, <code>(index + 1) % tabs.length</code> goes back to 0. Adding <code>tabs.length</code> before the remainder avoids a negative index when moving left from the first tab.</p>

<h3>Gallery: the HTML</h3>
<pre><code>&lt;figure&gt;
  &lt;img id="big" src="photos/1.jpg" alt="Students in a computer lab"&gt;
  &lt;figcaption id="caption"&gt;Students in a computer lab&lt;/figcaption&gt;
&lt;/figure&gt;
&lt;div id="thumbs"&gt;&lt;/div&gt;</code></pre>

<h3>Gallery: the JavaScript</h3>
<p>Describe the pictures as data and build the thumbnail buttons from it. Use your own image files, or files you are allowed to use.</p>
<pre><code>const photos = [
  { src: "photos/1.jpg", alt: "Students in a computer lab" },
  { src: "photos/2.jpg", alt: "A trainer helping a learner" },
  { src: "photos/3.jpg", alt: "Graduates holding certificates" }
];
const big = document.querySelector("#big");
const caption = document.querySelector("#caption");
const thumbs = document.querySelector("#thumbs");

photos.forEach((photo) =&gt; {
  const btn = document.createElement("button");
  btn.type = "button";
  const img = document.createElement("img");
  img.src = photo.src;
  img.alt = photo.alt;
  img.width = 80;
  btn.appendChild(img);
  btn.addEventListener("click", () =&gt; {
    big.src = photo.src;
    big.alt = photo.alt;
    caption.textContent = photo.alt;
  });
  thumbs.appendChild(btn);
});</code></pre>
<p>Each thumbnail is a real <code>button</code>, so it is reachable by keyboard and works with Enter and Space without any extra code.</p>

<h3>Try it yourself</h3>
<ol>
<li>Build the tabs with your own three headings and text. Use the mouse, then the Tab key and arrow keys, to confirm both work.</li>
<li>Make a <code>photos</code> folder with three images and build the gallery.</li>
<li>Add "Previous" and "Next" buttons that move through the photos using a counter.</li>
<li>Add a CSS rule that gives the selected tab a bold look using the <code>[aria-selected="true"]</code> selector.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Widgets work by changing one "active" item and hiding the rest.</li>
<li>Use <code>aria-selected</code>, <code>role</code> and <code>hidden</code> so assistive technology understands tabs.</li>
<li>Build galleries from data, with real buttons for the thumbnails.</li>
<li>The remainder operator makes index lists wrap around.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What type of data can localStorage store directly?
- Arrays
- Objects
* Strings only
- Functions
= localStorage holds text. Use JSON.stringify to save arrays and objects and JSON.parse to read them back.

Q: In the to-do list project, why does render() rebuild the list from the tasks array after each change?
* So the screen can never disagree with the data
- Because the browser forces a reload
- To make the code longer
- Because the DOM cannot be edited otherwise
= Keeping one source of truth and redrawing from it avoids the page and the data getting out of step.

Q: A user leaves the bill box empty in the tip calculator. What does Number("") give, and why is it checked?
- NaN, so it is fine
* 0, so you must check the empty case yourself
- null
- An error that stops the page
= Number("") is 0, which would silently show a zero bill, so check for an empty value before calculating.

Q: Why is the calculate function kept separate from the code that reads and displays values?
- JavaScript requires it
* It does only maths, so it is easy to test and reuse
- It makes the page load faster
- It hides the code from users
= Separate jobs make programs easier to understand, test and change.

Q: Why store quiz questions as an array of objects?
- Arrays are faster than variables
* Content is kept separate from logic, so adding a question means adding data only
- Objects cannot be displayed otherwise
- It is required by HTML
= Data-driven pages are easy to extend without touching the logic.

Q: In the tabs widget, why should every tab be a button element?
* Buttons are focusable and work with keyboard and assistive technology by default
- Buttons are the only elements that can have text
- It makes the tabs load faster
- Divs cannot have events
= Native buttons give keyboard support for free, which a div would need extra code to match.

Q: What does the expression (index + 1) % tabs.length do when index is the last position?
- Throws an error
* Wraps back to 0 so the first tab is selected
- Returns the length
- Returns -1
= The remainder of a number equal to the length divided by the length is 0, so the sequence wraps around.
QUIZ,
        ],
        [
            'title'   => 'Module 5: Good practice and next steps',
            'summary' => 'Debug with the console and breakpoints, build accessible widgets, organise your code, fetch JSON and plan where to go next.',
            'lessons' => [
                [
                    'title'   => 'Debugging: reading errors and finding bugs',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to read an error message, find the line that caused it, and use the console and breakpoints to understand what your code is really doing. Every developer writes bugs daily; the difference between a beginner and an expert is mostly how calmly and systematically they hunt them.</p>

<h3>Reading an error message</h3>
<p>Open the console (F12). Errors appear in red with a type, a message, and a file and line number you can click.</p>
<pre><code>const total = price * 2;
// Uncaught ReferenceError: price is not defined
//     at app.js:1</code></pre>
<table>
<thead><tr><th>Error</th><th>Usual meaning</th></tr></thead>
<tbody>
<tr><td><code>ReferenceError</code></td><td>You used a name that does not exist (spelling, wrong scope, not declared)</td></tr>
<tr><td><code>TypeError</code></td><td>You used a value in a way its type cannot do, such as calling <code>undefined</code> as a function or reading a property of <code>null</code></td></tr>
<tr><td><code>SyntaxError</code></td><td>The code is not valid JavaScript, such as a missing bracket, quote or comma. Nothing in the file runs</td></tr>
</tbody>
</table>
<p>The most common message for beginners is <em>Cannot read properties of null</em>. It means a selector found nothing, for example <code>document.querySelector("#btn")</code> returned <code>null</code> because the id is misspelt or the script ran before the element existed.</p>

<h3>Debugging with console.log</h3>
<p>The simplest tool is to print values at key points:</p>
<pre><code>function average(numbers) {
  console.log("input:", numbers);
  let sum = 0;
  numbers.forEach((n) =&gt; { sum += n; });
  console.log("sum:", sum);
  return sum / numbers.length;
}
console.log(average([10, 20, 30]));   // 20</code></pre>
<p>Other helpful console tools: <code>console.table(arrayOfObjects)</code> shows a neat table, <code>console.error()</code> prints in red, and <code>console.warn()</code> in yellow. Remove stray logs before publishing.</p>

<h3>Breakpoints: pausing the program</h3>
<p>A <strong>breakpoint</strong> pauses your code at a chosen line so you can look around.</p>
<ol>
<li>Press <strong>F12</strong> and open the <strong>Sources</strong> tab (called <strong>Debugger</strong> in Firefox).</li>
<li>Open your script file from the file list.</li>
<li>Click the line number you want to pause on. A marker appears.</li>
<li>Trigger the code (reload, or click the button). The page freezes at that line.</li>
<li>Hover over variables to see their values, or read the <strong>Scope</strong> panel.</li>
<li>Use <strong>Step over</strong> (F10) to run one line, <strong>Step into</strong> (F11) to go inside a function, and <strong>Resume</strong> (F8) to continue.</li>
</ol>
<p>You can also write the word <code>debugger;</code> in your code, and the browser pauses there when developer tools are open.</p>

<h3>A debugging method</h3>
<ol>
<li><strong>Reproduce</strong> the bug reliably: find the exact steps.</li>
<li><strong>Read</strong> the error message and the line it points to.</li>
<li><strong>Guess</strong> what is wrong and <strong>test</strong> the guess with a log or breakpoint. Change only one thing at a time.</li>
<li>If lost, <strong>shrink</strong> the problem: comment out code until the bug disappears, then bring it back piece by piece.</li>
<li>Explain the problem aloud to a friend or even to a pencil. Often you spot the mistake while explaining.</li>
</ol>

<h3>Common mistakes to look for first</h3>
<ul>
<li>Typo in a name, or wrong capital letters (<code>queryselector</code> instead of <code>querySelector</code>).</li>
<li>Using <code>=</code> (assign) where you meant <code>===</code> (compare).</li>
<li>Forgetting that input values are strings, so <code>"2" + "3"</code> becomes <code>"23"</code>.</li>
<li>Script placed before the elements it needs.</li>
<li>Calling a function with brackets when you meant to pass it as a handler.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Create a file with this broken code and fix each error using the console messages: <code>const items = [1, 2, 3]; items.forEach(n =&gt; console.log(n * 2); </code> (hint: a bracket is missing).</li>
<li>Write <code>document.querySelector("#nothing").textContent = "Hi";</code> and read the error, then explain it in one sentence.</li>
<li>Set a breakpoint inside the <code>average</code> function above and step through it, watching <code>sum</code> change.</li>
<li>Use <code>console.table</code> on an array of three objects.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Read the error type, message and line number: they usually point close to the problem.</li>
<li><code>console.log</code> is quick; breakpoints let you inspect everything while the code is paused.</li>
<li>Change one thing at a time and test; shrink the problem when you are stuck.</li>
<li>Check typos, <code>=</code> versus <code>===</code>, string versus number, and script position first.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Accessible widgets and organising your code',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will know the key rules that make interactive pages usable by everyone, including keyboard users and people using screen readers, and you will be able to organise your JavaScript into several files using modules. Accessibility is not an extra: many learners and customers in your community use older phones, slow connections, keyboards instead of a mouse, or assistive tools.</p>

<h3>Accessibility rules for interactive widgets</h3>
<ul>
<li><strong>Use the right element.</strong> A <code>button</code> for actions, a link (<code>a</code>) for navigation. Buttons are focusable and respond to Enter and Space automatically. A clickable <code>div</code> does neither unless you add a lot of extra code.</li>
<li><strong>Everything must work with the keyboard alone.</strong> Test by pressing Tab, Shift+Tab, Enter, Space and arrow keys. You should always see where focus is.</li>
<li><strong>Never remove the focus outline</strong> without giving a clear alternative style.</li>
<li><strong>Label form fields</strong> with <code>label</code> elements, and give images useful <code>alt</code> text (an empty <code>alt</code> for pure decoration).</li>
<li><strong>Announce changes.</strong> When your script updates a message, put it in an element with <code>role="status"</code> (polite) or <code>role="alert"</code> (urgent) so screen readers read it out.</li>
<li><strong>Keep state in attributes</strong> such as <code>aria-expanded</code>, <code>aria-selected</code> and <code>aria-pressed</code>, and update them from JavaScript.</li>
<li><strong>Do not rely on colour alone</strong> to show errors or status. Add text or an icon too, and keep contrast high.</li>
</ul>

<h3>Example: an accessible show/hide button</h3>
<pre><code>&lt;button id="toggle" aria-expanded="false" aria-controls="details"&gt;Show details&lt;/button&gt;
&lt;div id="details" hidden&gt;More information is shown here.&lt;/div&gt;
&lt;script&gt;
  const toggle = document.querySelector("#toggle");
  const details = document.querySelector("#details");
  toggle.addEventListener("click", () =&gt; {
    const open = toggle.getAttribute("aria-expanded") === "true";
    toggle.setAttribute("aria-expanded", String(!open));
    details.hidden = open;
    toggle.textContent = open ? "Show details" : "Hide details";
  });
&lt;/script&gt;</code></pre>
<p>The button announces itself as expanded or collapsed, and works by mouse, keyboard and touch.</p>

<h3>Organising code in several files</h3>
<p>As programs grow, one long file becomes hard to read. Split code by purpose. Modern JavaScript supports <strong>modules</strong>: files that share code through <code>export</code> and <code>import</code>. Make three files:</p>
<pre><code>// math.js
export function add(a, b) {
  return a + b;
}
export const TAX_RATE = 0.18;

// format.js
export function formatPrice(amount) {
  return "UGX " + amount.toLocaleString("en-UG");
}

// main.js
import { add, TAX_RATE } from "./math.js";
import { formatPrice } from "./format.js";

const net = add(10000, 5000);
console.log(formatPrice(net * (1 + TAX_RATE)));   // UGX 17,700</code></pre>
<p>In your HTML, load only the main file and mark it as a module:</p>
<pre><code>&lt;script type="module" src="main.js"&gt;&lt;/script&gt;</code></pre>
<p>Modules have their own scope, so names in one file do not clash with names in another. One important point: browsers refuse to load modules from a page opened by double-clicking the file (a <code>file:</code> address). You need a small local web server. The easiest is the <strong>Live Server</strong> extension in VS Code, or running <code>python -m http.server</code> in the folder if Python is installed, then opening the <code>localhost</code> address it shows.</p>

<h3>Tips for tidy code</h3>
<ul>
<li>One file or function, one job. Name functions with verbs.</li>
<li>Avoid copying code: if you paste the same lines twice, make a function.</li>
<li>Use <code>const</code>, consistent indentation, and comments that explain why.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Build the show/hide button and test it using only the keyboard (Tab to focus, Enter to toggle).</li>
<li>Take your to-do list or quiz and split the code into two or three module files.</li>
<li>Start a local server (Live Server or <code>python -m http.server</code>) and load the page.</li>
<li>Unplug your mouse mentally: use only Tab on one of your earlier projects and note anything you cannot reach.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use native elements, labels, alt text and visible focus; test with the keyboard alone.</li>
<li>Announce dynamic changes with <code>role="status"</code> or <code>role="alert"</code> and keep ARIA state attributes up to date.</li>
<li>Modules (<code>export</code> and <code>import</code>) split code into small, focused files.</li>
<li>Modules need a web server, not a double-clicked file.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Fetching JSON and where to go next',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to load data from the internet with <code>fetch</code>, understand what JSON is, show the data on a page, and choose a sensible path for continuing to learn. Many sites you use every day, from weather and news to mobile money dashboards, load fresh data this way.</p>

<h3>What is JSON?</h3>
<p><strong>JSON</strong> (JavaScript Object Notation) is a text format for sending data. It looks like JavaScript objects and arrays, with a few strict rules: property names are in double quotes, strings use double quotes, and there are no trailing commas or comments.</p>
<pre><code>{
  "name": "Lumina Africa Foundation",
  "town": "Gulu",
  "programs": ["Climate", "Digital skills"],
  "open": true
}</code></pre>
<p>Use <code>JSON.parse(text)</code> to turn JSON text into an object and <code>JSON.stringify(object)</code> to turn an object into JSON text.</p>

<h3>Asking a server for data with fetch</h3>
<p>The <code>fetch</code> function sends a request over the network. Because the network takes time, it returns a <strong>promise</strong>: a placeholder for a result that will arrive later. You wait for it with <code>then</code>, or with the cleaner <code>async</code> and <code>await</code> keywords.</p>
<pre><code>async function loadPosts() {
  const response = await fetch("https://jsonplaceholder.typicode.com/posts?_limit=3");
  if (!response.ok) {
    throw new Error("Request failed with status " + response.status);
  }
  const posts = await response.json();
  return posts;
}

loadPosts()
  .then((posts) =&gt; {
    posts.forEach((p) =&gt; console.log(p.id, p.title));
  })
  .catch((error) =&gt; {
    console.error("Something went wrong:", error.message);
  });</code></pre>
<p>Step by step: <code>await fetch(...)</code> pauses the function until the server answers. <code>response.ok</code> is true for success statuses (200 to 299); fetch does not fail on a 404 or 500 by itself, so check it. <code>await response.json()</code> reads the body and parses the JSON. The <code>catch</code> handles network failures and our own thrown error. The address used above is a free practice service that returns sample data; this is only sample content.</p>

<h3>Showing the data on a page</h3>
<pre><code>&lt;ul id="posts"&gt;&lt;/ul&gt;
&lt;p id="status" role="status"&gt;Loading...&lt;/p&gt;
&lt;script&gt;
  const listEl = document.querySelector("#posts");
  const statusEl = document.querySelector("#status");

  fetch("https://jsonplaceholder.typicode.com/posts?_limit=5")
    .then((res) =&gt; res.json())
    .then((posts) =&gt; {
      statusEl.textContent = "";
      posts.forEach((p) =&gt; {
        const li = document.createElement("li");
        li.textContent = p.title;
        listEl.appendChild(li);
      });
    })
    .catch(() =&gt; {
      statusEl.textContent = "Sorry, we could not load the posts. Check your connection.";
    });
&lt;/script&gt;</code></pre>
<p>Always show a loading message and a friendly error message: connections in the real world drop often. Advanced lessons in the next course cover APIs, keys and promises in depth.</p>

<h3>Where to go next</h3>
<ul>
<li><strong>Practise</strong> with small projects: a budget tracker, a weather widget, a flash-card app, a page for a local business.</li>
<li><strong>Learn Git and GitHub</strong> to keep versions of your work and show it to employers.</li>
<li><strong>Take the next LAF Academy course</strong>, Modern Web Development, for modules, APIs, tooling and deployment.</li>
<li><strong>Read the MDN Web Docs</strong>, the best free reference for web technologies.</li>
<li>Later, explore a framework such as React or Vue, or a server language such as Node.js, once the basics feel natural.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Copy the page above, open it through a local server, and check that five titles appear.</li>
<li>Change <code>_limit=5</code> to 10 and show the author number as well as the title.</li>
<li>Break the web address on purpose (add a letter) and check that your friendly error message appears.</li>
<li>Write down your next three projects and the first step for each.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>JSON is a text format for data; convert with <code>JSON.parse</code> and <code>JSON.stringify</code>.</li>
<li><code>fetch</code> returns a promise; use <code>await</code> or <code>then</code>, check <code>response.ok</code>, then call <code>response.json()</code>.</li>
<li>Always handle loading and error states.</li>
<li>Keep learning by building small real projects and reading MDN.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does "Cannot read properties of null" most often mean for a beginner?
- The computer has run out of memory
* A selector found no element, so you tried to use a property of null
- The browser is out of date
- A loop never ended
= querySelector returns null when nothing matches, often because of a misspelt id or a script that ran too early.

Q: What does a breakpoint do?
- Deletes a line of code
- Prevents the page loading
* Pauses the program at a chosen line so you can inspect values
- Speeds up the code
= Breakpoints let you look at variables and step through code one line at a time.

Q: Which element is best for an action such as "Delete task"?
- div
- span
* button
- p
= A button is focusable and works with Enter and Space by default, so it is accessible without extra code.

Q: Which of these help make dynamic updates accessible? (choose all that apply)
* Putting messages in an element with role="status" or role="alert"
* Updating aria-expanded when a panel opens
- Removing the focus outline
- Using colour alone to show errors
= Announce changes and keep state attributes current. Never hide focus or rely on colour alone.

Q: How do you load a JavaScript file as a module in a page?
- Add the word module to the file name
- Use the link tag
* Use a script tag with type="module"
- It is not possible in browsers
= type="module" lets the file use import and export.

Q: Why must a module page be served from a web server rather than opened from a file?
- Modules only work on mobile phones
- File names cannot contain dots
* Browsers block loading modules from file: addresses for security reasons
- Servers make JavaScript run faster
= Use Live Server or a similar local server while developing.

Q: After await fetch(url), what should you check before calling response.json()?
- response.length
* response.ok, because fetch does not reject on a 404 or 500 status
- response.title
- Nothing is needed
= A failed HTTP status still resolves the promise, so check response.ok.

Q: Which is valid JSON?
- {name: 'Akello'}
* {"name": "Akello"}
- {'name': "Akello"}
- {"name": "Akello",}
= JSON needs double quotes on names and strings, and no trailing comma.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which layer of a web page is responsible for behaviour such as reacting to clicks?
- HTML
- CSS
* JavaScript
- The web server only
= HTML gives structure, CSS gives style and JavaScript adds behaviour.

Q: Where does JavaScript on a normal web page run?
- Only on the web server
* In the visitor's browser
- Inside the router
- Only in the text editor
= Browsers contain a JavaScript engine, so the code runs on the visitor's own device.

Q: What is the output? let a = 4; a += 3; a *= 2; console.log(a);
- 7
- 10
* 14
- 24
= a becomes 7 after += 3, then 14 after *= 2.

Q: What does typeof [1, 2, 3] return?
- array
* object
- list
- number
= Arrays are objects in JavaScript. Use Array.isArray() to test for an array specifically.

Q: What does the template string `${2 + 3} items` produce?
- 2 + 3 items
- 23 items
* 5 items
- An error
= Anything inside ${ } is evaluated, so 2 + 3 gives 5.

Q: Which comparison is false?
- 5 === 5
- "a" !== "b"
* "5" === 5
- 7 >= 7
= Strict equality compares type as well, and a string is not the same type as a number.

Q: Which values are falsy? (choose all that apply)
* 0
* null
* an empty string
- the string "false"
= Falsy values are false, 0, empty string, null, undefined and NaN. The text "false" is a non-empty string, so it is truthy.

Q: What happens in a switch if you forget break at the end of a case?
- The program stops with an error
- Nothing runs
* Execution continues into the next case
- The default case is skipped for ever
= Without break, the code "falls through" into the following case.

Q: How many times does this loop run? for (let i = 1; i <= 4; i++) { }
- 3
* 4
- 5
- It never ends
= i takes 1, 2, 3 and 4, and stops when i becomes 5.

Q: What is the risk in while (x > 0) { console.log(x); } if x never changes inside the loop?
- It prints nothing
* It becomes an infinite loop and freezes the page
- JavaScript fixes it automatically
- x becomes negative
= Something inside the loop must move the condition towards false.

Q: What does a function return when it has no return statement?
- null
- 0
* undefined
- An empty string
= Functions without return give back undefined.

Q: Which arrow function correctly doubles a number?
- (n) -> n * 2
- n => { n * 2 }
* (n) => n * 2
- function => n * 2
= With one expression and no braces the value is returned automatically. With braces you would need an explicit return.

Q: What does this print? function f() { const secret = 1; } f(); console.log(typeof secret);
- number
* undefined
- null
- It throws an error
= secret is local to f, so outside it is not defined. typeof on an undeclared name returns "undefined" without an error.

Q: Given const nums = [1, 2, 3, 4], what is nums.filter((n) => n % 2 === 0)?
- [1, 3]
- [1, 2, 3, 4]
* [2, 4]
- 6
= filter keeps the items for which the test is true: the even numbers.

Q: Which method gives only the first item that matches a test?
- map
- filter
- reduce
* find
= find returns a single item (or undefined). filter returns an array of all matches.

Q: Which property should you use to put text coming from a user into the page safely?
- innerHTML
- outerHTML
* textContent
- insertAdjacentHTML
= textContent never interprets HTML, so injected markup cannot run.

Q: What is the correct order for adding a new element to the page?
- Attach, create, fill
* Create, fill, attach
- Fill, attach, create
- Select, delete, create
= An element must exist before you can set its content, and is shown only after it is attached.

Q: Why do we call event.preventDefault() in a form's submit handler?
- To make the form submit faster
* To stop the page from reloading so we can validate and handle the data in JavaScript
- To remove the form
- To clear the fields
= The default action of submit is a page reload or navigation.

Q: What is event delegation useful for?
* Handling clicks on many child elements with one listener on the parent, including children added later
- Making events slower
- Preventing all events
- Sending events to other websites
= Events bubble up, and event.target shows which child was used.

Q: How should you save an array of objects in localStorage?
- localStorage.setItem("tasks", tasks)
* localStorage.setItem("tasks", JSON.stringify(tasks))
- localStorage.tasks.push(tasks)
- It cannot be saved
= localStorage stores only strings, so convert with JSON.stringify and read back with JSON.parse.

Q: Which of the following should never be stored in localStorage? (choose all that apply)
* Passwords
* Private identity numbers
- A visitor's chosen colour theme
- The last tab a visitor opened
= localStorage is readable by any script on the page, so keep secrets out. Harmless preferences are fine.

Q: A selector in your code returns null and you see "Cannot read properties of null". What is the first thing to check?
- The speed of your internet
* That the selector matches an element that exists when the script runs
- The version of Windows
- Whether you have enough memory
= Misspelt ids or scripts that run before the element exists are the usual causes.

Q: Which F-key steps over one line while paused at a breakpoint in Chrome's debugger?
- F5
- F12
* F10
- F1
= F10 is Step over, F11 is Step into and F8 resumes the script.

Q: A form field value is "12" and another is "8". What does value1 + value2 give if both are read straight from the inputs?
- 20
* "128"
- 4
- NaN
= Input values are strings, so + joins them. Convert with Number() first.

Q: Which of these helps keyboard and screen reader users of a custom tab widget? (choose all that apply)
* Using button elements for tabs
* Updating aria-selected when the tab changes
* Supporting arrow keys to move between tabs
- Removing the focus outline for a cleaner look
= Native buttons, ARIA state and keyboard support make a widget usable; hiding focus makes it unusable for keyboard users.

Q: After await fetch(url), the server answers with status 404. What does the promise do?
- It rejects with an error automatically
* It resolves, and response.ok is false, so you must check it
- It never finishes
- It returns the data anyway
= fetch rejects only on network failure; an HTTP error status still resolves.

Q: Your next step after finishing this course is to build real projects. Which plan is best?
- Only read about JavaScript for a year
* Build small projects, keep them on GitHub and continue with the Modern Web Development course
- Copy projects without trying to understand them
- Stop learning because the course is over
= Skills grow by making things and fixing the bugs that appear.
QUIZ,
];
