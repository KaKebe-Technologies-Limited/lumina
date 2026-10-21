<?php
return [
    'slug'        => 'web-development-advanced',
    'title'       => 'Modern Web Development: JavaScript, APIs and Deployment',
    'category'    => 'web',
    'level'       => 'advanced',
    'icon'        => 'layers',
    'sort'        => 220,
    'summary'     => 'Go from basic scripts to real projects: modern JavaScript, working with APIs, Git and Node.js, quality and security, then publish your site online with free hosting.',
    'description' => '<p>Knowing how to make a button work is the start. Professional web developers also use modern language features, talk to services over the internet, keep their work under version control, check quality and security, and publish their sites so that anyone can visit. This course covers that full journey, using free tools that run on an ordinary laptop.</p><p>You will write modern JavaScript with modules, promises and async/await, build a small app that calls a public API, use Git, GitHub, Node.js and npm, make interfaces that are responsive, accessible, fast and safer, and finish by planning and shipping a capstone project of your own.</p><p>The course is for learners who have completed JavaScript for Beginners or who are comfortable with HTML, CSS, variables, functions, arrays, objects and the DOM. Expect to type and run a lot of code: the best way to learn is to build.</p>',
    'outcomes'    => [
        'Use ES modules, destructuring, spread, promises, async/await, error handling and classes.',
        'Explain HTTP, REST, status codes and CORS, and call an API with fetch.',
        'Explain why API keys must never be placed in front-end code.',
        'Use Git and GitHub to commit, branch, push and open a pull request.',
        'Set up a Node.js project with npm, linting, formatting and environment variables.',
        'Build responsive, accessible and fast interfaces and apply basic security habits against XSS.',
        'Describe how static hosting, domains and DNS work and publish a site online.',
        'Plan, build and maintain a capstone project from idea to launch.',
    ],
    'requirements'=> [
        'The LAF Academy course "JavaScript for Beginners: Make Web Pages Interactive" or equal knowledge (variables, functions, arrays, objects, DOM and events)',
        'Comfortable with HTML and CSS',
        'A computer where you can install software (Windows, Mac or Linux) and an internet connection',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'MDN Web Docs', 'url' => 'https://developer.mozilla.org/'],
        ['title' => 'Node.js', 'url' => 'https://nodejs.org/'],
        ['title' => 'Git', 'url' => 'https://git-scm.com/'],
        ['title' => 'GitHub Docs', 'url' => 'https://docs.github.com/'],
        ['title' => 'W3C Web Accessibility Initiative (WAI)', 'url' => 'https://www.w3.org/WAI/'],
    ],
    'modules' => [
        [
            'title'   => 'Module 1: Modern JavaScript',
            'summary' => 'Use the language features that professional code relies on: modules, destructuring, spread, promises, async/await, error handling and classes.',
            'lessons' => [
                [
                    'title'   => 'ES modules, destructuring and spread',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to split a program into modules, unpack arrays and objects with destructuring, and copy or combine data with the spread operator. These features appear in almost every modern project, tutorial and code review, so reading and writing them fluently makes you much faster.</p>

<h3>ES modules</h3>
<p>An <strong>ES module</strong> is a JavaScript file that can share parts of itself with other files. There are two kinds of export. A file may have many <strong>named exports</strong> and one <strong>default export</strong>.</p>
<pre><code>// cart.js
export const VAT_RATE = 0.18;

export function addVat(amount) {
  return amount * (1 + VAT_RATE);
}

export default function formatUGX(amount) {
  return "UGX " + Math.round(amount).toLocaleString("en-UG");
}

// main.js
import formatUGX, { addVat, VAT_RATE } from "./cart.js";

console.log(VAT_RATE);                 // 0.18
console.log(formatUGX(addVat(10000))); // UGX 11,800</code></pre>
<p>Named imports use curly brackets and must match the exported names exactly (you may rename with <code>import { addVat as withTax }</code>). The default import has no brackets and you choose its name. The path must start with <code>./</code> or <code>../</code> and, in a browser, include the <code>.js</code> ending. Load the entry file with <code>&lt;script type="module" src="main.js"&gt;&lt;/script&gt;</code>. Modules run in strict mode, have private scope, and are loaded once even if several files import them. Because browsers block modules opened as plain files, serve the folder through a local server during development.</p>

<h3>Destructuring</h3>
<p><strong>Destructuring</strong> pulls values out of arrays and objects into variables in one line.</p>
<pre><code>const student = { name: "Akello", age: 17, school: "Gulu High", marks: [72, 85, 64] };

const { name, age } = student;               // by property name
const { school: schoolName } = student;      // rename
const { country = "Uganda" } = student;      // default when missing
const [first, second] = student.marks;       // by position
const [, , third] = student.marks;           // skip two items

console.log(name, age, schoolName, country); // Akello 17 Gulu High Uganda
console.log(first, second, third);           // 72 85 64</code></pre>
<p>It is especially useful in function parameters, where it documents what the function expects:</p>
<pre><code>function describe({ name, age, school = "unknown" }) {
  return `${name} (${age}) from ${school}`;
}
console.log(describe({ name: "Ojok", age: 19 }));  // Ojok (19) from unknown</code></pre>

<h3>Spread and rest</h3>
<p>The three dots have two related jobs. <strong>Spread</strong> expands an array or object into separate items. <strong>Rest</strong> gathers the remaining items into one array or object.</p>
<pre><code>const a = [1, 2, 3];
const b = [...a, 4, 5];            // copy and add: [1, 2, 3, 4, 5]

const base = { theme: "light", lang: "en" };
const user = { ...base, lang: "lg", fontSize: 18 };
console.log(user);                 // { theme: "light", lang: "lg", fontSize: 18 }

function total(...numbers) {       // rest: gathers all arguments
  return numbers.reduce((sum, n) =&gt; sum + n, 0);
}
console.log(total(5, 10, 15));     // 30

const [head, ...others] = [10, 20, 30];
console.log(head, others);         // 10 [20, 30]</code></pre>
<p>In an object spread, later properties win, which makes <code>{ ...defaults, ...settings }</code> a standard way to combine defaults with user choices. Spread makes a <strong>shallow copy</strong>: nested arrays and objects are still shared with the original. Updating one inside the copy also changes the original.</p>

<h3>Immutable updates</h3>
<p>Instead of changing an object in place, create a new one. This avoids surprises and is how many frameworks expect you to work:</p>
<pre><code>const tasks = [{ id: 1, done: false }, { id: 2, done: false }];
const updated = tasks.map((t) =&gt; (t.id === 2 ? { ...t, done: true } : t));
console.log(tasks[1].done, updated[1].done);   // false true</code></pre>

<h3>Try it yourself</h3>
<ol>
<li>Create <code>utils.js</code> with a named export <code>capitalise(text)</code> and a default export <code>slugify(text)</code> that lower-cases and replaces spaces with hyphens.</li>
<li>Import both in <code>main.js</code>, print the results, and load it as a module through a local server.</li>
<li>Destructure the first and third item of an array and rename an object property while destructuring.</li>
<li>Write <code>update(task, changes)</code> that returns <code>{ ...task, ...changes }</code> and check the original is unchanged.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Modules share code with <code>export</code> and <code>import</code>; use named exports for many items and one default for a main item.</li>
<li>Destructuring unpacks arrays by position and objects by name, with renaming and defaults.</li>
<li>Spread expands (<code>[...a]</code>, <code>{...o}</code>) and rest gathers (<code>...args</code>).</li>
<li>Spread copies are shallow; update data by building new copies.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Promises and async/await',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain asynchronous code, create and use promises, and write clean asynchronous functions with <code>async</code> and <code>await</code>. Nearly everything useful on the web, loading data, reading files, waiting for a timer, takes time. Understanding how JavaScript waits without freezing the page is essential.</p>

<h3>Why asynchronous?</h3>
<p>JavaScript runs one instruction at a time on a single thread. If it stopped to wait three seconds for a server, the whole page would freeze: no scrolling, no clicking. So slow tasks start and then <em>hand back control</em>. When they finish, the result is delivered later. Compare:</p>
<pre><code>console.log("1: start");
setTimeout(() =&gt; console.log("2: after 1 second"), 1000);
console.log("3: end");
// 1: start
// 3: end
// 2: after 1 second</code></pre>
<p>The timer does not block; line 3 runs first. This ordering surprises beginners, so trace examples carefully.</p>

<h3>Promises</h3>
<p>A <strong>promise</strong> is an object that stands for a result that is not ready yet. It is in one of three states: <strong>pending</strong>, <strong>fulfilled</strong> (success, with a value) or <strong>rejected</strong> (failure, with a reason). You create one with <code>new Promise</code> and consume it with <code>then</code> and <code>catch</code>.</p>
<pre><code>function wait(ms) {
  return new Promise((resolve) =&gt; setTimeout(resolve, ms));
}

function checkAge(age) {
  return new Promise((resolve, reject) =&gt; {
    if (age &gt;= 18) resolve("Welcome");
    else reject(new Error("Too young"));
  });
}

checkAge(20)
  .then((msg) =&gt; console.log(msg))        // Welcome
  .catch((err) =&gt; console.log(err.message))
  .finally(() =&gt; console.log("done"));    // runs either way</code></pre>
<p>Each <code>then</code> returns a new promise, so steps can be chained, and one <code>catch</code> at the end handles a failure from any step above it.</p>

<h3>async and await</h3>
<p>The keywords <code>async</code> and <code>await</code> make promise code read like normal top-to-bottom code. An <code>async</code> function always returns a promise. Inside it, <code>await</code> pauses <em>that function</em> (not the page) until a promise settles and gives you its value.</p>
<pre><code>async function greetLater() {
  console.log("Waiting...");
  await wait(1000);
  console.log("Hello after one second");
  return "finished";
}
greetLater().then((result) =&gt; console.log(result));</code></pre>
<p><code>await</code> may be used only inside an <code>async</code> function or at the top level of a module.</p>

<h3>Running tasks together</h3>
<p>Awaiting tasks one after another is slow if they do not depend on each other. Start them together with <code>Promise.all</code>:</p>
<pre><code>async function loadBoth() {
  const started = Date.now();
  const [a, b] = await Promise.all([wait(1000).then(() =&gt; "A"), wait(1000).then(() =&gt; "B")]);
  console.log(a, b, Date.now() - started);   // A B 1001 (about 1 second, not 2)
}
loadBoth();</code></pre>
<p><code>Promise.all</code> succeeds when all succeed and rejects as soon as any one fails. <code>Promise.allSettled</code> waits for all and reports each result, good when failures are acceptable. <code>Promise.race</code> settles with the first to finish, which is handy for timeouts.</p>

<h3>The mistake to avoid</h3>
<p>Forgetting <code>await</code> gives you a promise instead of the value. Printing <code>fetch(url)</code> shows <code>Promise {&lt;pending&gt;}</code>, not data. Another mistake is using <code>forEach</code> with <code>await</code> inside: <code>forEach</code> does not wait. Use a <code>for...of</code> loop instead when each step must finish before the next.</p>

<h3>Try it yourself</h3>
<ol>
<li>Write <code>wait(ms)</code> and an async function that prints "3", waits one second, prints "2", and so on to "Go!".</li>
<li>Write <code>checkAge</code> as above and call it with <code>await</code> inside <code>try</code> and <code>catch</code> for ages 20 and 12.</li>
<li>Run two waits of 500 ms in sequence and then with <code>Promise.all</code>; measure each with <code>Date.now()</code>.</li>
<li>Print a promise without <code>await</code> and note what appears.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Slow tasks are asynchronous so the page stays responsive.</li>
<li>A promise is pending, fulfilled or rejected; use <code>then</code>, <code>catch</code> and <code>finally</code>.</li>
<li><code>async</code> functions return promises; <code>await</code> pauses the function until a promise settles.</li>
<li>Use <code>Promise.all</code> to run independent tasks together; never forget <code>await</code>.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Error handling and classes',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to handle errors with <code>try</code>, <code>catch</code> and <code>finally</code>, throw your own errors, and write simple classes. Real programs meet bad input, failed networks and missing data all the time; good developers plan for failure instead of hoping it will not happen.</p>

<h3>try, catch and finally</h3>
<p>Code inside <code>try</code> runs normally. If anything throws an error, JavaScript jumps to <code>catch</code>. The <code>finally</code> block runs in both cases, which is useful for clean-up such as hiding a loading spinner.</p>
<pre><code>function parseSettings(text) {
  try {
    const data = JSON.parse(text);
    return data;
  } catch (error) {
    console.error("Invalid settings:", error.message);
    return {};
  } finally {
    console.log("Parsing attempted");
  }
}
console.log(parseSettings('{"theme":"dark"}'));   // { theme: "dark" }
console.log(parseSettings("not json"));           // {}</code></pre>
<p>Use <code>try</code> around code that can fail for reasons outside your control (parsing, network, storage). Do not wrap everything: hiding errors makes bugs harder to find.</p>

<h3>Throwing your own errors</h3>
<p>You can raise an error to stop a function when it is given something it cannot use:</p>
<pre><code>function divide(a, b) {
  if (b === 0) {
    throw new Error("Cannot divide by zero");
  }
  return a / b;
}
try {
  console.log(divide(10, 2));   // 5
  console.log(divide(1, 0));
} catch (e) {
  console.log(e.name + ": " + e.message);   // Error: Cannot divide by zero
}</code></pre>
<p>Always throw an <code>Error</code> object (not a plain string), because it carries a name, message and stack trace. You can create specialised errors, for example a <code>ValidationError</code>, using classes.</p>

<h3>Errors in async code</h3>
<p>With <code>async</code> and <code>await</code>, the same <code>try</code> and <code>catch</code> catches a rejected promise:</p>
<pre><code>async function getUser(id) {
  try {
    const res = await fetch(`https://jsonplaceholder.typicode.com/users/${id}`);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return await res.json();
  } catch (error) {
    console.error("Could not load user:", error.message);
    return null;
  }
}</code></pre>

<h3>Classes</h3>
<p>A <strong>class</strong> is a blueprint for objects that bundle data and the functions that work on it. The <code>constructor</code> sets up each new object, and <code>this</code> refers to the object being used.</p>
<pre><code>class Account {
  #balance = 0;                       // private field: only usable inside the class

  constructor(owner, opening = 0) {
    this.owner = owner;
    this.#balance = opening;
  }

  deposit(amount) {
    if (amount &lt;= 0) throw new Error("Deposit must be positive");
    this.#balance += amount;
  }

  get balance() {
    return this.#balance;
  }
}

const acc = new Account("Auma", 5000);
acc.deposit(2500);
console.log(acc.owner, acc.balance);   // Auma 7500</code></pre>
<p>Here <code>#balance</code> cannot be changed from outside; the only way is through <code>deposit</code>, which checks the amount. A <code>get</code> method lets code read <code>acc.balance</code> like a property. Classes can extend others:</p>
<pre><code>class SavingsAccount extends Account {
  constructor(owner, opening, rate) {
    super(owner, opening);
    this.rate = rate;
  }
  addInterest() {
    this.deposit(this.balance * this.rate);
  }
}
const s = new SavingsAccount("Ojok", 10000, 0.05);
s.addInterest();
console.log(s.balance);   // 10500</code></pre>
<p>Use classes when you have several objects of the same kind with shared behaviour. For simple data, plain objects and functions are often clearer; classes are optional, not required.</p>

<h3>Try it yourself</h3>
<ol>
<li>Write a function <code>safeParse(text)</code> that returns the parsed object or <code>null</code> on bad JSON.</li>
<li>Write <code>sqrt(n)</code> that throws an error for negative numbers and test it in <code>try</code> and <code>catch</code>.</li>
<li>Create an <code>Account</code> class as above, add a <code>withdraw</code> method that refuses to go below zero.</li>
<li>Extend it with a class of your own and add one extra method.</li>
</ol>

<h3>Key points</h3>
<ul>
<li><code>try</code>, <code>catch</code> and <code>finally</code> handle failures; use them around risky code only.</li>
<li>Throw <code>Error</code> objects with clear messages when a function cannot continue.</li>
<li>Async errors are caught with the same <code>try</code> and <code>catch</code> around <code>await</code>.</li>
<li>Classes bundle data and behaviour; <code>#private</code> fields protect data, and <code>extends</code> reuses code.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which line correctly imports a default export called formatUGX and a named export addVat from "./cart.js"?
- import { formatUGX, addVat } from "./cart.js";
* import formatUGX, { addVat } from "./cart.js";
- import formatUGX from { addVat } "./cart.js";
- import * formatUGX, addVat "./cart.js";
= The default import has no brackets, and named imports go inside curly brackets.

Q: What does const { name, age = 18 } = { name: "Auma" } give for age?
- undefined
- null
* 18
- An error
= The default value is used when the property is missing.

Q: What is the result of { ...{ a: 1, b: 2 }, b: 5 }?
- { a: 1, b: 2 }
* { a: 1, b: 5 }
- { b: 5 }
- An error
= In an object spread, later properties override earlier ones.

Q: A copy made with the spread operator is shallow. What does this mean?
- It copies only the first property
* Nested objects and arrays are still shared with the original
- It cannot be changed
- It is slower
= Only the top level is copied; inner references still point to the same data.

Q: What order does this print? console.log("A"); setTimeout(() => console.log("B"), 0); console.log("C");
- A B C
* A C B
- B A C
- C B A
= Even with 0 delay, the callback is queued and runs after the current code has finished.

Q: What does an async function always return?
- The value directly
- undefined
* A promise
- A callback
= Even if you return a plain value, async wraps it in a promise.

Q: How do you handle a rejected promise when using await?
- You cannot
* Wrap the await in try and catch
- Use the break statement
- Use a loop
= A rejected promise throws at the await line, so try and catch work as usual.

Q: Which are valid reasons to use Promise.all? (choose all that apply)
* Running independent requests at the same time
* Waiting until several tasks have all finished
- Making code run on several CPU cores
- Avoiding the need for await
= Promise.all starts promises together and settles when all succeed or one fails; it does not add threads.
QUIZ,
        ],
        [
            'title'   => 'Module 2: Working with APIs',
            'summary' => 'Understand HTTP and REST, call APIs with fetch, read status codes, handle CORS and keys, and build a small app on a public API.',
            'lessons' => [
                [
                    'title'   => 'HTTP and REST in plain words',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain how a browser and a server talk using HTTP, read the parts of a request and response, and recognise a REST API. Understanding this conversation is what turns "magic" network code into something you can reason about and debug.</p>

<h3>The request and response conversation</h3>
<p>The web works like ordering at a restaurant. The browser (the <strong>client</strong>) sends a <strong>request</strong>. The <strong>server</strong> prepares something and sends back a <strong>response</strong>. The rules of this conversation are called <strong>HTTP</strong> (HyperText Transfer Protocol). The secure version, <strong>HTTPS</strong>, encrypts the conversation so that people on the same Wi-Fi or network cable cannot read or change it.</p>

<h3>Parts of a request</h3>
<ul>
<li><strong>URL</strong>: where to send it, for example <code>https://api.example.com/students/12?fields=name</code>. It has a protocol (<code>https</code>), a host (<code>api.example.com</code>), a path (<code>/students/12</code>) and optional query parameters after <code>?</code>.</li>
<li><strong>Method</strong>: what you want to do (see below).</li>
<li><strong>Headers</strong>: extra information such as the type of data you send or accept, or credentials.</li>
<li><strong>Body</strong>: the data you send, used with methods such as POST.</li>
</ul>

<h3>The common methods</h3>
<table>
<thead><tr><th>Method</th><th>Meaning</th><th>Example</th></tr></thead>
<tbody>
<tr><td>GET</td><td>Read data; should not change anything</td><td>Get the list of courses</td></tr>
<tr><td>POST</td><td>Create something new</td><td>Register a new learner</td></tr>
<tr><td>PUT</td><td>Replace something completely</td><td>Replace a learner's whole record</td></tr>
<tr><td>PATCH</td><td>Change part of something</td><td>Update only a phone number</td></tr>
<tr><td>DELETE</td><td>Remove something</td><td>Delete a comment</td></tr>
</tbody>
</table>

<h3>The response</h3>
<p>A response has a <strong>status code</strong> (a number saying how it went), headers, and usually a body, often JSON. The status families are covered in the lesson on fetch: 2xx success, 3xx redirects, 4xx client mistakes, 5xx server problems.</p>

<h3>What is an API?</h3>
<p>An <strong>API</strong> (Application Programming Interface) is a set of agreed ways for one program to ask another for data or actions. A <strong>web API</strong> is reached over HTTP. When a weather app shows today's forecast, it does not own a weather station: it asks a weather API.</p>

<h3>REST</h3>
<p><strong>REST</strong> is a popular style for designing web APIs. Its main ideas are simple:</p>
<ul>
<li>Everything is a <strong>resource</strong> (a student, a course, a post) with its own URL.</li>
<li>URLs use <strong>nouns</strong>, not verbs. The method says the action.</li>
<li>Each request is independent: the server does not remember earlier requests, so the request carries what the server needs.</li>
<li>Data is usually sent as JSON.</li>
</ul>
<p>A typical REST API for courses looks like this:</p>
<pre><code>GET    /courses          list all courses
GET    /courses/7        get course number 7
POST   /courses          create a new course (data in the body)
PATCH  /courses/7        change part of course 7
DELETE /courses/7        remove course 7</code></pre>
<p>Notice how the same URL <code>/courses/7</code> does different things depending on the method. This pattern makes REST APIs predictable, so learning one helps with many.</p>

<h3>Seeing it in your browser</h3>
<p>You can watch real requests. Open any website, press <strong>F12</strong>, choose the <strong>Network</strong> tab and reload. Each row is a request. Click one to see the method, status, headers and the response body. Filter by <strong>Fetch/XHR</strong> to see data requests made by JavaScript. This tab is your best friend when an API call misbehaves.</p>

<h3>Try it yourself</h3>
<ol>
<li>Open <code>https://jsonplaceholder.typicode.com/posts/1</code> in your browser. This is a free practice API. Read the JSON you see.</li>
<li>Open the Network tab, reload that page and click the request. Write down its method, status code and one response header.</li>
<li>Change the URL to <code>/posts?userId=1</code> and notice how the query parameter filters the list.</li>
<li>Write the five REST URLs you would design for a "events" resource, listing the method for each.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>HTTP is a request and response conversation; HTTPS encrypts it.</li>
<li>A request has a URL, method, headers and sometimes a body.</li>
<li>Methods express the action: GET reads, POST creates, PUT or PATCH changes, DELETE removes.</li>
<li>REST APIs use resource URLs with nouns, stateless requests and JSON.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Using fetch, JSON and status codes',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to call an API with <code>fetch</code> for reading and sending data, interpret status codes, and write a reusable, safe request helper. This is the everyday toolkit for any front-end that talks to a server.</p>

<h3>Status codes</h3>
<table>
<thead><tr><th>Range</th><th>Meaning</th><th>Common codes</th></tr></thead>
<tbody>
<tr><td>2xx</td><td>Success</td><td>200 OK, 201 Created, 204 No Content</td></tr>
<tr><td>3xx</td><td>Redirect</td><td>301 Moved, 304 Not Modified</td></tr>
<tr><td>4xx</td><td>Your request was wrong</td><td>400 Bad Request, 401 Unauthorised, 403 Forbidden, 404 Not Found, 429 Too Many Requests</td></tr>
<tr><td>5xx</td><td>The server failed</td><td>500 Internal Server Error, 503 Service Unavailable</td></tr>
</tbody>
</table>
<p>A 4xx means you should fix the request (or ask the user to). A 5xx means the problem is on the server side, so retrying later may help.</p>

<h3>A GET request</h3>
<pre><code>async function getPost(id) {
  const response = await fetch(`https://jsonplaceholder.typicode.com/posts/${id}`);
  console.log(response.status, response.ok);   // 200 true
  if (!response.ok) {
    throw new Error(`Request failed: ${response.status}`);
  }
  return response.json();
}

getPost(1).then((post) =&gt; console.log(post.title));</code></pre>
<p>Remember: <code>fetch</code> rejects only when the network itself fails (offline, DNS problem). A 404 or 500 still gives you a response, so always check <code>response.ok</code>.</p>

<h3>Sending data with POST</h3>
<p>For POST you pass a second argument with the method, headers and a body. JSON must be turned into text, and the header tells the server what it is.</p>
<pre><code>async function createPost(title, body) {
  const response = await fetch("https://jsonplaceholder.typicode.com/posts", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ title, body, userId: 1 })
  });
  if (!response.ok) throw new Error(`Failed: ${response.status}`);
  return response.json();
}

createPost("Hello", "My first post").then((created) =&gt; {
  console.log(created.id);   // the practice server gives the new post an id
});</code></pre>
<p>The practice server pretends to save data, so your new post will not really appear later. Real APIs store it. Shorthand <code>{ title, body }</code> means <code>{ title: title, body: body }</code>.</p>

<h3>Query parameters</h3>
<p>Build query strings with <code>URLSearchParams</code> so that special characters are encoded correctly:</p>
<pre><code>const params = new URLSearchParams({ userId: "1", _limit: "3" });
const url = `https://jsonplaceholder.typicode.com/posts?${params}`;
console.log(url);   // ...posts?userId=1&amp;_limit=3</code></pre>

<h3>A reusable helper</h3>
<p>Put the repeated checks in one function so every call behaves the same:</p>
<pre><code>async function request(url, options = {}) {
  let response;
  try {
    response = await fetch(url, options);
  } catch {
    throw new Error("Network problem: check your connection.");
  }
  if (!response.ok) {
    throw new Error(`The server answered ${response.status}.`);
  }
  if (response.status === 204) return null;      // no body
  return response.json();
}</code></pre>
<p>A <code>catch</code> without a name is allowed when you do not need the error object. Callers can now write <code>const posts = await request(url)</code> inside their own <code>try</code> and <code>catch</code> and show a friendly message.</p>

<h3>Cancelling and timeouts</h3>
<p>A slow request should not hang forever. Use an <code>AbortController</code>:</p>
<pre><code>const controller = new AbortController();
const timer = setTimeout(() =&gt; controller.abort(), 8000);
try {
  const data = await request("https://jsonplaceholder.typicode.com/posts", { signal: controller.signal });
  console.log(data.length);
} finally {
  clearTimeout(timer);
}</code></pre>
<p>After 8 seconds, <code>abort()</code> cancels the request and <code>fetch</code> rejects, so your <code>catch</code> can tell the user.</p>

<h3>Try it yourself</h3>
<ol>
<li>Write the <code>getPost</code> function and call it for ids 1 and 99999. Print the status for each and handle the failure.</li>
<li>Write <code>createPost</code> and log the returned object.</li>
<li>Build a URL for three posts of user 2 with <code>URLSearchParams</code> and print it.</li>
<li>Write the <code>request</code> helper and use it twice. Turn off your internet and see the friendly message.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Status codes: 2xx success, 3xx redirect, 4xx request problem, 5xx server problem.</li>
<li>Always check <code>response.ok</code>; <code>fetch</code> rejects only on network failure.</li>
<li>For POST send <code>method</code>, a <code>Content-Type</code> header and <code>JSON.stringify</code> body.</li>
<li>Wrap repeated logic in a helper and add timeouts with <code>AbortController</code>.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'CORS, API keys and keeping secrets safe',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain why a browser sometimes blocks an API call (CORS), what an API key is, and why a secret key must never be placed in front-end code. These two topics cause the most confusion and, in the case of leaked keys, the most expensive mistakes.</p>

<h3>The same-origin policy</h3>
<p>An <strong>origin</strong> is the combination of protocol, host and port: <code>https://lumina.example</code> is one origin; <code>https://api.other.example</code> is another. Browsers apply a safety rule called the <strong>same-origin policy</strong>: a script on one origin may not read responses from another origin, unless that other server gives permission. Without it, any website you open could quietly read your email in another tab, using your logged-in session.</p>

<h3>CORS in plain words</h3>
<p><strong>CORS</strong> (Cross-Origin Resource Sharing) is the permission system. When your page at origin A asks origin B for data, the browser checks B's response headers. If B says <code>Access-Control-Allow-Origin</code> with A's origin (or <code>*</code> for everyone), the browser lets your script read the data. If not, it blocks it and the console shows an error like <em>blocked by CORS policy</em>.</p>
<ul>
<li>CORS is enforced by the <strong>browser</strong>. The server could still receive your request; the browser just will not let your script read the answer.</li>
<li>The fix is on the <strong>server</strong>: it must send the right header. You cannot repair it from your front-end code.</li>
<li>Public APIs designed for websites usually allow it. If an API does not, call it from your own server instead (servers are not subject to CORS), and let your page talk to your server.</li>
<li>For some requests the browser first sends a "preflight" request with the OPTIONS method to ask permission. If you see an OPTIONS request in the Network tab, this is why.</li>
</ul>

<h3>What is an API key?</h3>
<p>Many services (maps, weather, payments, AI) require an <strong>API key</strong>: a long secret string that identifies your account. The service uses it to limit use, to bill you, and to know who is asking. Anyone who has your key can make requests as you, using your quota and your money.</p>

<h3>Why keys must not be in front-end code</h3>
<p>Everything you send to the browser can be read: HTML, CSS and JavaScript. Press F12 on any site and you can read its scripts and see every request it makes. If you write <code>const KEY = "sk_live_123..."</code> in your JavaScript, every visitor can copy it. Bots scan public pages and GitHub repositories for keys within minutes. Minifying or "hiding" it in a variable does not help.</p>
<pre><code>// NEVER do this in code that runs in the browser:
const API_KEY = "sk_live_this_is_a_secret";
fetch(`https://api.paidservice.example/data?key=${API_KEY}`);</code></pre>

<h3>The safe pattern: a small server in the middle</h3>
<p>Keep the secret on a server you control and let your page call that server:</p>
<ol>
<li>The browser asks <em>your</em> server: <code>GET /api/weather?town=Gulu</code>.</li>
<li>Your server adds the secret key (read from an environment variable) and calls the paid service.</li>
<li>Your server returns only the data the page needs.</li>
</ol>
<p>A tiny example in Node.js (you will learn Node later in this course):</p>
<pre><code>// server.js (runs on the server, never in the browser)
import http from "node:http";

const KEY = process.env.WEATHER_KEY;       // set outside the code

http.createServer(async (req, res) =&gt; {
  const reply = await fetch(`https://api.paidservice.example/data?key=${KEY}`);
  const data = await reply.text();
  res.writeHead(200, { "Content-Type": "application/json" });
  res.end(data);
}).listen(3000);</code></pre>

<h3>Other good key habits</h3>
<ul>
<li>Prefer APIs that need <strong>no key</strong> or give <strong>public, restricted keys</strong> (limited to your website address, or read-only) for front-end use.</li>
<li>Never commit secrets to Git. If you do, treat the key as stolen: <strong>revoke it and create a new one</strong>. Deleting the commit is not enough because history and copies remain.</li>
<li>Give each key the minimum permissions and a spending limit, and use separate keys for testing and production.</li>
<li>Do not paste keys into chats, screenshots or videos.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Open the Network tab on any website you use. Find one request and look at its headers; note any <code>access-control</code> headers in the response.</li>
<li>From a page opened on your computer, try <code>fetch("https://jsonplaceholder.typicode.com/todos/1")</code> in the console and note that it works because the API allows it.</li>
<li>Search your own earlier projects for the words "key", "token" and "password". Is any secret in a file that would be public?</li>
<li>Draw the browser, your server and the paid service as three boxes with arrows, and mark where the secret lives.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>The browser blocks cross-origin reads unless the other server allows them with CORS headers.</li>
<li>CORS errors are fixed on the server, not in front-end code.</li>
<li>An API key is a secret that identifies and bills you; anyone holding it can act as you.</li>
<li>Never put secret keys in front-end code or Git; keep them on a server in environment variables, and revoke any key that leaks.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Project: a small app on a public API',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>In this project you will build a "Country finder": the user types a country name, and the page shows its capital, region, population and flag emoji by calling a free public API. You will practise <code>fetch</code>, status checks, loading and error states, safe DOM updates and modules. The API address and fields below follow the REST Countries service; check its documentation if a field changes, because public services do evolve.</p>

<h3>Plan first</h3>
<ol>
<li>A form with one input and a button.</li>
<li>On submit: validate the text, show "Loading...", request the data.</li>
<li>If the request works, show a card for the first match; if the country is not found, show a message; if the network fails, show another.</li>
</ol>

<h3>index.html</h3>
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
  &lt;meta charset="utf-8"&gt;
  &lt;meta name="viewport" content="width=device-width, initial-scale=1"&gt;
  &lt;title&gt;Country finder&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
  &lt;h1&gt;Country finder&lt;/h1&gt;
  &lt;form id="search"&gt;
    &lt;label for="name"&gt;Country name&lt;/label&gt;
    &lt;input id="name" type="text" required&gt;
    &lt;button type="submit"&gt;Search&lt;/button&gt;
  &lt;/form&gt;
  &lt;p id="status" role="status"&gt;&lt;/p&gt;
  &lt;section id="result" aria-live="polite"&gt;&lt;/section&gt;
  &lt;script type="module" src="main.js"&gt;&lt;/script&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>

<h3>api.js: talking to the service</h3>
<pre><code>const BASE = "https://restcountries.com/v3.1/name";

export async function findCountry(name) {
  const url = `${BASE}/${encodeURIComponent(name)}?fields=name,capital,region,population,flag`;
  let response;
  try {
    response = await fetch(url);
  } catch {
    throw new Error("NETWORK");
  }
  if (response.status === 404) throw new Error("NOT_FOUND");
  if (!response.ok) throw new Error("SERVER");
  const list = await response.json();
  return list[0];
}</code></pre>
<p><code>encodeURIComponent</code> makes the user's text safe to place inside a URL. The function throws short codes so that the interface can pick the right message.</p>

<h3>main.js: the interface</h3>
<pre><code>import { findCountry } from "./api.js";

const form = document.querySelector("#search");
const input = document.querySelector("#name");
const statusEl = document.querySelector("#status");
const result = document.querySelector("#result");

const MESSAGES = {
  NETWORK: "No connection. Check your internet and try again.",
  NOT_FOUND: "We could not find that country. Check the spelling.",
  SERVER: "The service has a problem right now. Try again later."
};

function showCountry(country) {
  const { name, capital = [], region, population, flag } = country;
  result.textContent = "";
  const card = document.createElement("article");
  const h2 = document.createElement("h2");
  h2.textContent = `${flag ?? ""} ${name.common}`;
  const p = document.createElement("p");
  p.textContent = `Capital: ${capital[0] ?? "none"}. Region: ${region}. Population: ${population.toLocaleString("en-UG")}.`;
  card.append(h2, p);
  result.appendChild(card);
}

form.addEventListener("submit", async (event) =&gt; {
  event.preventDefault();
  const text = input.value.trim();
  if (text.length &lt; 2) {
    statusEl.textContent = "Type at least two letters.";
    return;
  }
  statusEl.textContent = "Loading...";
  result.textContent = "";
  try {
    const country = await findCountry(text);
    statusEl.textContent = "";
    showCountry(country);
  } catch (error) {
    statusEl.textContent = MESSAGES[error.message] ?? "Something went wrong.";
  }
});</code></pre>
<p>The <code>??</code> operator (nullish coalescing) gives the right side only when the left is <code>null</code> or <code>undefined</code>. It is safer than <code>||</code> here because it keeps valid values such as <code>0</code>.</p>

<h3>Improvements to consider</h3>
<ul>
<li>Cache results in a <code>Map</code> so searching the same country twice does not call the API again.</li>
<li>Disable the button while loading to prevent repeated clicks.</li>
<li>Add a timeout with <code>AbortController</code> from the earlier lesson.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Create the three files, serve the folder (for example with the Live Server extension) and search "Uganda".</li>
<li>Test a misspelt name and then switch off the internet to check both messages.</li>
<li>Add a "Currency" line by adding <code>currencies</code> to the <code>fields</code> list and reading it from the response (log the response first to see its shape).</li>
<li>Add the caching improvement and confirm in the Network tab that a repeat search sends no new request.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Plan the states first: loading, success, not found, network error.</li>
<li>Keep API code (api.js) separate from interface code (main.js).</li>
<li>Encode user input in URLs and write text with <code>textContent</code>.</li>
<li>Read the API's documentation and log a sample response before writing code that uses it.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which HTTP method is normally used to read data without changing anything?
* GET
- POST
- PUT
- DELETE
= GET is for reading. POST creates, PUT or PATCH changes and DELETE removes.

Q: In REST, which URL and method best describe "remove course 7"?
- GET /deleteCourse?id=7
- POST /courses/remove
* DELETE /courses/7
- PATCH /courses
= REST uses noun URLs and lets the method express the action.

Q: A fetch call to an API returns status 404. What does fetch do by default?
- It rejects the promise
* It resolves, so you must check response.ok
- It retries three times
- It returns an empty array
= fetch rejects only on network failure, not on HTTP error codes.

Q: Which status code family means the server itself failed?
- 2xx
- 3xx
- 4xx
* 5xx
= 5xx means a server error. 4xx means the request was wrong.

Q: Who enforces CORS?
- The API server's firewall
* The user's browser
- The internet provider
- Git
= The browser blocks scripts from reading cross-origin responses unless the server sends allowing headers.

Q: How do you fix a CORS error for an API you control?
- Add try and catch in the front-end code
- Change the fetch method to POST
* Send the correct Access-Control-Allow-Origin header from the server
- Remove the Content-Type header
= Only the server can give the browser permission.

Q: Why must a secret API key never appear in front-end JavaScript?
- It makes the page slow
* Anyone can read front-end code and copy the key
- Browsers do not support keys
- Keys only work in Node.js
= Everything sent to the browser can be read with developer tools, so secrets belong on a server.

Q: You accidentally pushed an API key to a public GitHub repository. What should you do first? (choose the best answer)
- Delete the commit and carry on
- Rename the repository
* Revoke the key and create a new one
- Change the file name
= The key may already have been copied, so it must be revoked. Removing the commit is not enough.
QUIZ,
        ],
        [
            'title'   => 'Module 3: Tooling and workflow',
            'summary' => 'Work like a professional: Git and GitHub, Node.js and npm, project structure, linting, formatting and environment variables.',
            'lessons' => [
                [
                    'title'   => 'Git basics: commits and branches',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to install Git, put a project under version control, save snapshots with commits, undo mistakes safely, and work on ideas in separate branches. Git is the tool almost every software team uses: it is a safety net, a history book and a collaboration system in one.</p>

<h3>Why version control?</h3>
<p>Without it, projects end up as folders named <em>final</em>, <em>final2</em> and <em>final-REALLY</em>. <strong>Git</strong> instead records snapshots of your whole project, called <strong>commits</strong>, each with a message saying what changed and why. You can go back to any commit, compare versions, and let several people work without overwriting each other. Git works on your own computer, with no internet needed.</p>

<h3>Setting up</h3>
<p>Download Git from <code>git-scm.com</code> and install it with the default options. Then open a terminal (on Windows, <strong>Git Bash</strong> from the Start menu; on Mac or Linux, Terminal) and tell Git who you are, once only:</p>
<pre><code>git config --global user.name "Your Name"
git config --global user.email "you@example.com"
git config --global init.defaultBranch main</code></pre>

<h3>The basic cycle</h3>
<p>Git has three areas: your <strong>working folder</strong> (files you edit), the <strong>staging area</strong> (changes you have chosen to include next), and the <strong>repository</strong> (the saved history). The loop is edit, stage, commit.</p>
<pre><code>mkdir my-site
cd my-site
git init                       # start tracking this folder
echo "&lt;h1&gt;Hello&lt;/h1&gt;" &gt; index.html
git status                     # shows index.html as untracked
git add index.html             # stage it
git commit -m "Add home page"  # save the snapshot
git log --oneline              # list the history</code></pre>
<p>Make another edit, then use these to see exactly what changed:</p>
<pre><code>git status        # which files changed
git diff          # the lines changed but not yet staged
git add .         # stage everything changed (check status first!)
git commit -m "Change heading text"</code></pre>
<p>Write commit messages in the present tense and make each commit one logical change: "Fix phone validation", not "stuff". Future you will read these.</p>

<h3>Ignoring files</h3>
<p>Some files must never be committed: secrets, generated folders and system files. List them in a file called <code>.gitignore</code>:</p>
<pre><code>node_modules/
.env
.DS_Store
dist/</code></pre>
<p>Create it before your first commit. Files already committed stay in history even if you add them to the ignore list later.</p>

<h3>Undoing things safely</h3>
<ul>
<li>Unstage a file: <code>git restore --staged index.html</code>.</li>
<li>Throw away edits you have not committed: <code>git restore index.html</code> (this cannot be undone, so be sure).</li>
<li>Undo a commit by making a new commit that reverses it: <code>git revert &lt;commit-id&gt;</code>. It keeps the history honest and is safe to use on shared work.</li>
</ul>

<h3>Branches</h3>
<p>A <strong>branch</strong> is a separate line of work. The main line is called <code>main</code>. You create a branch to try a feature without risking the working version, and merge it back when ready.</p>
<pre><code>git switch -c add-contact-page     # create and move to a new branch
# ...edit files and commit as usual...
git switch main                    # go back to main
git merge add-contact-page         # bring the feature in
git branch -d add-contact-page     # delete the finished branch</code></pre>
<p>If two branches changed the same lines, Git stops with a <strong>merge conflict</strong> and marks the file with <code>&lt;&lt;&lt;&lt;&lt;&lt;&lt;</code>, <code>=======</code> and <code>&gt;&gt;&gt;&gt;&gt;&gt;&gt;</code>. Open the file, keep the correct text, delete the markers, then <code>git add</code> and <code>git commit</code>. Conflicts look scary but are normal.</p>

<h3>Try it yourself</h3>
<ol>
<li>Install Git and set your name and email.</li>
<li>Create a folder with an <code>index.html</code>, run <code>git init</code> and make your first commit.</li>
<li>Add a <code>.gitignore</code> that lists <code>node_modules/</code> and <code>.env</code>, and commit it.</li>
<li>Create a branch, add a second page, commit, switch back to <code>main</code> and merge. Run <code>git log --oneline</code> to see the result.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Git saves snapshots (commits) of a project and lets you return to any of them.</li>
<li>The cycle is edit, <code>git add</code>, <code>git commit</code>; check with <code>git status</code> and <code>git diff</code>.</li>
<li>Use <code>.gitignore</code> to keep secrets and generated files out of history.</li>
<li>Branches isolate work; merge when ready and resolve conflicts calmly.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'GitHub: pushing, pulling and pull requests',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what GitHub adds to Git, connect a local project to a remote repository, share your work with <code>push</code> and <code>pull</code>, and describe how a pull request works. This is how developers back up code, collaborate across towns and countries, and show their work to employers.</p>

<h3>Git versus GitHub</h3>
<p><strong>Git</strong> is the tool on your computer. <strong>GitHub</strong> is a website and service that stores Git repositories online (similar services include GitLab and Bitbucket). The online copy is called a <strong>remote</strong>. GitHub adds sharing, backup, issue tracking, code review and free hosting features. A free account is enough for everything in this course.</p>

<h3>Connecting your project to GitHub</h3>
<p>On the GitHub website you create an empty repository through the <strong>New repository</strong> page, without adding a README, and GitHub shows its address. Then in your project folder:</p>
<pre><code>git remote add origin https://github.com/YOUR-NAME/my-site.git
git branch -M main
git push -u origin main</code></pre>
<p>The name <code>origin</code> is the usual nickname for the main remote. <code>push</code> uploads your commits. The <code>-u</code> option remembers the link so later you can type just <code>git push</code>. GitHub does not accept your account password from the command line; it asks you to sign in through the browser (using Git Credential Manager, installed with Git for Windows) or to use a personal access token or an SSH key. Treat tokens like passwords and never commit them.</p>

<h3>Everyday commands with a remote</h3>
<pre><code>git push                # send my new commits to GitHub
git pull                # fetch and merge new commits from GitHub
git clone https://github.com/OWNER/project.git   # copy a repository to my computer
git remote -v           # show the remote addresses</code></pre>
<p>Pull before you start work each day, so you build on the latest version. If someone pushed while you were working, Git may refuse your push; pull first, fix any conflict, and push again.</p>

<h3>Pull requests</h3>
<p>A <strong>pull request</strong> (PR) is a proposal to merge one branch into another, with space for discussion. It is how teams review each other's work before it reaches <code>main</code>. The usual workflow:</p>
<ol>
<li>Create a branch for one task: <code>git switch -c fix-footer-links</code>.</li>
<li>Commit your changes, then push the branch: <code>git push -u origin fix-footer-links</code>.</li>
<li>On GitHub, open the repository. A banner offers to compare and open a pull request for your branch. Write a clear title and a short description of what and why.</li>
<li>Teammates read the changes line by line, comment, and ask for updates. You push more commits to the same branch and the PR updates itself.</li>
<li>When approved, a maintainer merges the pull request. Then you update your local copy: <code>git switch main</code>, <code>git pull</code> and delete the old branch.</li>
</ol>
<p>If you want to contribute to someone else's project, you first make a <strong>fork</strong> (your own online copy), push changes to your fork, and open a PR from it to the original.</p>

<h3>Good habits for a public profile</h3>
<ul>
<li>Add a <code>README.md</code> to every project: what it is, how to run it, and a screenshot or link.</li>
<li>Commit small and often, with clear messages.</li>
<li>Never commit <code>.env</code> files, keys or personal data.</li>
<li>Pin your best projects on your GitHub profile. Employers and clients often look there.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Create a free GitHub account (ask a trusted adult or mentor if you are unsure about privacy settings) and a new empty repository named <code>my-site</code>.</li>
<li>Connect your local project from the last lesson with <code>git remote add</code> and push it.</li>
<li>Edit a file on GitHub's website, commit it there, then run <code>git pull</code> locally and see the change arrive.</li>
<li>Create a branch, push it, open a pull request to <code>main</code> in your own repository and merge it.</li>
<li>Write a short <code>README.md</code> for the project and push it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>GitHub hosts remote copies of Git repositories for backup and collaboration.</li>
<li><code>push</code> sends commits, <code>pull</code> brings new ones, <code>clone</code> copies a repository.</li>
<li>A pull request proposes merging a branch and lets others review it first.</li>
<li>Keep secrets out of repositories and write a helpful README.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Node.js, npm and project structure',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what Node.js and npm are, create a project with a <code>package.json</code>, install and use packages, run scripts, and arrange files sensibly. These are the foundations of modern front-end and back-end tooling.</p>

<h3>What is Node.js?</h3>
<p><strong>Node.js</strong> lets you run JavaScript outside the browser, on your own computer or on a server. It is built on the same engine as Chrome. Developers use Node to run build tools, test code and write servers. Download the <strong>LTS</strong> (long-term support) version from <code>nodejs.org</code> and install it with the defaults. Check it in a terminal:</p>
<pre><code>node --version
npm --version</code></pre>
<p>Run a script with <code>node file.js</code>. For example, <code>hello.js</code> containing <code>console.log("Hello from Node");</code> prints that text in the terminal. Node has no <code>document</code> or <code>window</code>, because there is no web page.</p>

<h3>What is npm?</h3>
<p><strong>npm</strong> (Node Package Manager) comes with Node. It downloads <strong>packages</strong>: ready-made code written by other developers, such as tools for building, testing or handling dates. Packages are listed on the npm website. Before you add one, check how popular and recently updated it is, and who maintains it, because every package is code you are trusting.</p>

<h3>Starting a project</h3>
<pre><code>mkdir tip-app
cd tip-app
npm init -y</code></pre>
<p>This creates <code>package.json</code>, the project's description file. Open it and you will see a name, a version, and a <code>scripts</code> section. Now add a package and a development tool:</p>
<pre><code>npm install dayjs                 # a library used by the app
npm install --save-dev prettier   # a tool used only while developing</code></pre>
<p>npm creates a <code>node_modules</code> folder with the downloaded code, a <code>package-lock.json</code> that records exact versions, and adds entries to <code>package.json</code> under <code>dependencies</code> and <code>devDependencies</code>. Never commit <code>node_modules</code> (it can be huge). Anyone who clones your project simply runs <code>npm install</code> to recreate it. Commit <code>package.json</code> and <code>package-lock.json</code>.</p>

<h3>npm scripts</h3>
<p>The <code>scripts</code> section stores handy commands:</p>
<pre><code>{
  "name": "tip-app",
  "version": "1.0.0",
  "type": "module",
  "scripts": {
    "start": "node src/index.js",
    "format": "prettier --write ."
  }
}</code></pre>
<p>Run them with <code>npm run format</code>, or <code>npm start</code> for the special name <code>start</code>. The setting <code>"type": "module"</code> lets Node use <code>import</code> and <code>export</code> in <code>.js</code> files.</p>
<p>Create <code>src/index.js</code>:</p>
<pre><code>import dayjs from "dayjs";

console.log("Today is " + dayjs().format("DD MMM YYYY"));</code></pre>
<p>Running <code>npm start</code> prints today's date in a format such as <code>21 Oct 2026</code>.</p>

<h3>A sensible project structure</h3>
<pre><code>my-project/
  index.html
  package.json
  README.md
  .gitignore
  src/
    main.js
    api.js
    styles.css
  public/
    images/
  tests/</code></pre>
<p>Put source code in <code>src</code>, files that are copied as they are (images, icons) in <code>public</code>, and write a README at the top. Use clear, lower-case file names without spaces. Keep one file for one purpose, and keep folders shallow.</p>

<h3>Build tools in one paragraph</h3>
<p>Larger sites use a <strong>bundler</strong> or dev server such as Vite. It starts a local server with instant reload (<code>npm run dev</code>) and produces an optimised folder (<code>npm run build</code>) with combined, shrunk files, ready to upload. You do not need one for small pages, but you will meet them often in jobs.</p>

<h3>Try it yourself</h3>
<ol>
<li>Install Node.js (LTS) and check <code>node --version</code> and <code>npm --version</code>.</li>
<li>Create the <code>tip-app</code> project with <code>npm init -y</code>, add <code>"type": "module"</code> and a <code>start</code> script.</li>
<li>Install <code>dayjs</code> and print today's date from <code>src/index.js</code>.</li>
<li>Add a <code>.gitignore</code> containing <code>node_modules/</code>, commit your project, then delete <code>node_modules</code> and recreate it with <code>npm install</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Node.js runs JavaScript outside the browser; npm installs packages.</li>
<li><code>package.json</code> describes the project; <code>npm install</code> recreates <code>node_modules</code>, which should not be committed.</li>
<li>Scripts give short names to common commands: <code>npm run name</code>.</li>
<li>Choose packages carefully and keep a tidy folder structure.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Linting, formatting and environment variables',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use a linter and a formatter to catch mistakes and keep code consistent, and to keep settings and secrets out of your code with environment variables. These habits make your code easier to read, safer to share, and more professional when you work in a team.</p>

<h3>Formatting with Prettier</h3>
<p>A <strong>formatter</strong> rewrites your code into one consistent style (indentation, quotes, line breaks), so nobody argues about spaces and every file looks the same. <strong>Prettier</strong> is the most common choice for JavaScript, HTML and CSS.</p>
<pre><code>npm install --save-dev prettier
npx prettier --write .</code></pre>
<p><code>npx</code> runs a tool installed in the project. Add a small settings file called <code>.prettierrc</code>:</p>
<pre><code>{
  "singleQuote": false,
  "semi": true,
  "printWidth": 90
}</code></pre>
<p>In VS Code you can install the Prettier extension and turn on "Format on Save" in the settings.</p>

<h3>Linting with ESLint</h3>
<p>A <strong>linter</strong> reads your code without running it and warns about likely bugs and bad habits: variables never used, a typo in a variable name, <code>==</code> instead of <code>===</code>. <strong>ESLint</strong> is the standard linter for JavaScript. The simplest way to set it up is to let the tool create its configuration for you:</p>
<pre><code>npm init @eslint/config@latest
npx eslint .</code></pre>
<p>Answer the questions (JavaScript modules, browser, no framework). It creates a config file, usually <code>eslint.config.js</code>. Options change between versions, so read the prompts and the official ESLint documentation if they differ from what you see. A typical report looks like:</p>
<pre><code>src/main.js
  12:7  error  'total' is assigned a value but never used  no-unused-vars
  20:5  error  Expected '===' and instead saw '=='         eqeqeq</code></pre>
<p>Read each message, fix the line, run again. Linters teach you good practice while you work. Prettier fixes <em>style</em>; ESLint finds <em>problems</em>. They complement each other. Add both to your <code>package.json</code> scripts:</p>
<pre><code>"scripts": {
  "lint": "eslint .",
  "format": "prettier --write ."
}</code></pre>

<h3>Environment variables</h3>
<p>An <strong>environment variable</strong> is a named setting that lives outside your code, in the environment where the program runs. They separate <em>configuration</em> (which differs between your laptop and the live server) and <em>secrets</em> (keys, passwords) from code, so the same code can run anywhere and secrets never enter Git.</p>
<p>In Node you read them with <code>process.env</code>. A common way to set them for local work is a <code>.env</code> file in the project folder:</p>
<pre><code># .env  (never commit this file)
PORT=3000
WEATHER_KEY=replace-with-your-real-key</code></pre>
<p>Since Node version 20.6 you can load that file with a built-in option, with no extra package:</p>
<pre><code>node --env-file=.env src/server.js</code></pre>
<pre><code>// src/server.js
const port = process.env.PORT ?? 3000;
const key = process.env.WEATHER_KEY;

if (!key) {
  console.error("Missing WEATHER_KEY. Add it to .env");
  process.exit(1);
}
console.log(`Starting on port ${port}`);</code></pre>
<p>On the live host you do not upload <code>.env</code>; you enter the variables in the hosting service's settings. A good habit is to commit a file called <code>.env.example</code> that lists the variable names with fake values, so teammates know what to set.</p>
<p>Remember the earlier rule: environment variables protect secrets on a <em>server</em>. Variables built into a front-end bundle end up in the browser and are public, so never put a real secret in them.</p>

<h3>Try it yourself</h3>
<ol>
<li>In your <code>tip-app</code> project, install Prettier, run it, and watch it reformat a deliberately messy file.</li>
<li>Set up ESLint and add the <code>lint</code> script. Write a variable you never use and read the warning.</li>
<li>Create a <code>.env</code> with <code>PORT=4000</code>, list <code>.env</code> in <code>.gitignore</code>, and print the port using <code>node --env-file=.env</code>.</li>
<li>Create a <code>.env.example</code> with a fake key, and commit it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Prettier formats code consistently; ESLint finds likely bugs and bad habits.</li>
<li>Add <code>lint</code> and <code>format</code> scripts so anyone can run them.</li>
<li>Environment variables keep configuration and secrets outside the code; read them from <code>process.env</code>.</li>
<li>Never commit <code>.env</code>; commit <code>.env.example</code> instead.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the correct basic Git cycle?
- Commit, add, edit
* Edit, add (stage), commit
- Push, edit, pull
- Branch, delete, merge
= You change files, stage the chosen changes with git add, then save them with git commit.

Q: Which file tells Git not to track certain files?
- .gitconfig
- package.json
* .gitignore
- README.md
= List names such as node_modules/ and .env in .gitignore.

Q: What is a pull request?
- A command that downloads files
* A proposal to merge a branch into another, with space for review and discussion
- A way to delete a branch
- A backup of GitHub
= Pull requests let teammates review changes before they are merged.

Q: What is the difference between Git and GitHub?
* Git is the version control tool; GitHub is an online service that hosts Git repositories
- They are the same
- GitHub is the tool and Git is the website
- Git only works online
= Git runs on your computer. GitHub stores remote copies and adds collaboration features.

Q: Which should NOT be committed to a repository? (choose all that apply)
* node_modules
* .env with real secrets
- package.json
- README.md
= Dependencies can be recreated with npm install and secrets must stay out of history. package.json and README belong in the repository.

Q: What does npm install do when run in a project that has a package.json?
- Deletes package.json
* Downloads the listed packages into node_modules
- Publishes your project
- Updates Git
= It recreates the dependencies from the files you committed.

Q: What is the main job of ESLint?
- Making the website load faster
- Hosting the project
* Finding likely bugs and bad habits in code without running it
- Compressing images
= A linter analyses code and reports problems. Prettier handles style.

Q: Why use environment variables for an API key in a Node server?
- They make the key shorter
* The secret stays outside the code and out of Git, and can differ between machines
- They are required by JavaScript
- They make the key public
= Configuration and secrets are kept separate from code so the same code can run anywhere.
QUIZ,
        ],
        [
            'title'   => 'Module 4: Quality: accessible, fast and secure',
            'summary' => 'Make interfaces that work for everyone on any device, load quickly on slow connections, resist common attacks and are checked by tests.',
            'lessons' => [
                [
                    'title'   => 'Responsive and accessible interfaces',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build pages that adapt to any screen size and meet the basic rules of web accessibility (WCAG). In Uganda and across Africa most visitors use phones, often small, older or on slow data, and many users have disabilities or temporary limits such as a cracked screen, bright sunlight or a broken mouse. Quality starts here.</p>

<h3>Responsive design</h3>
<p>A <strong>responsive</strong> page changes its layout to suit the screen. Three ingredients make it work. First, the viewport meta tag in the head, without which phones show a tiny zoomed-out desktop page:</p>
<pre><code>&lt;meta name="viewport" content="width=device-width, initial-scale=1"&gt;</code></pre>
<p>Second, flexible sizes: use percentages, <code>rem</code> units and <code>max-width</code> rather than fixed pixel widths. Third, <strong>media queries</strong> that adjust the layout above a certain width. Design for the small screen first ("mobile first"), then add rules for larger screens:</p>
<pre><code>img { max-width: 100%; height: auto; }

.cards {
  display: grid;
  gap: 1rem;
  grid-template-columns: 1fr;            /* phone: one column */
}

@media (min-width: 40rem) {
  .cards { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 64rem) {
  .cards { grid-template-columns: repeat(3, 1fr); }
}

/* Or, without any media query: */
.auto-cards {
  display: grid;
  gap: 1rem;
  grid-template-columns: repeat(auto-fit, minmax(16rem, 1fr));
}</code></pre>
<p>Test by resizing the browser or by using the device toolbar in developer tools (F12, then the phone icon). Make touch targets such as buttons at least about 44 pixels tall, and keep text at 16 pixels or larger.</p>

<h3>What accessibility means</h3>
<p><strong>Accessibility</strong> (often written a11y) means that people with different abilities can use your site: people who are blind or have low vision, are deaf, have limited movement, or have difficulty reading or concentrating. The international standard is the <strong>WCAG</strong> (Web Content Accessibility Guidelines). They are organised around four principles, easy to remember as <strong>POUR</strong>:</p>
<ul>
<li><strong>Perceivable</strong>: information can be seen or heard in some way. Give images <code>alt</code> text, videos captions, and keep strong colour contrast (at least 4.5 to 1 for normal text).</li>
<li><strong>Operable</strong>: everything works by keyboard, there is a visible focus, nothing flashes dangerously, and time limits can be extended.</li>
<li><strong>Understandable</strong>: use plain language, clear labels, consistent navigation, and helpful error messages that say how to fix the problem.</li>
<li><strong>Robust</strong>: use correct HTML so browsers and assistive tools such as screen readers can interpret it.</li>
</ul>

<h3>A practical checklist</h3>
<ol>
<li>Set <code>lang</code> on the <code>html</code> tag, and write a unique <code>title</code> for each page.</li>
<li>Use real headings (<code>h1</code> once, then <code>h2</code>, <code>h3</code> in order) and landmarks: <code>header</code>, <code>nav</code>, <code>main</code>, <code>footer</code>.</li>
<li>Use real buttons and links; links describe their destination ("Read the 2025 report", not "click here").</li>
<li>Associate every form field with a <code>label</code>, and describe errors in text next to the field.</li>
<li>Check colour contrast; never use colour alone to carry meaning.</li>
<li>Press Tab through the page: the order must be logical and focus must be visible.</li>
<li>Respect user choices: allow zoom, avoid fixed font sizes in pixels, and honour reduced motion:</li>
</ol>
<pre><code>@media (prefers-reduced-motion: reduce) {
  * { animation: none !important; transition: none !important; }
}</code></pre>

<h3>Testing for accessibility</h3>
<p>Automatic tools find only part of the problems, so combine methods. Use the Lighthouse panel in Chrome or Edge developer tools to get an accessibility score and a list of issues. Try browser extensions such as axe DevTools. Then do manual tests: use only the keyboard, zoom to 200 percent, and try a screen reader (NVDA is free for Windows; VoiceOver is built into Apple devices; TalkBack is on Android). Listen to a few pages and notice what is confusing.</p>

<h3>Try it yourself</h3>
<ol>
<li>Take a project of yours and open the device toolbar. Check it at 360 pixels wide. Fix any horizontal scrolling.</li>
<li>Add a media query that changes a one-column layout to two columns at <code>40rem</code>.</li>
<li>Run Lighthouse and write down the accessibility score and two issues. Fix at least one.</li>
<li>Unplug your mouse (or just do not touch it) and complete a form using only Tab, Shift+Tab, Space and Enter.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Responsive pages use the viewport tag, flexible units and media queries, designed mobile first.</li>
<li>WCAG follows four principles: perceivable, operable, understandable, robust.</li>
<li>Semantic HTML, labels, alt text, contrast and keyboard support cover most basic needs.</li>
<li>Combine automated tools with keyboard and screen reader tests.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Performance: fast pages on slow connections',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to measure how fast a page loads and apply the biggest improvements: lighter images, caching and smaller JavaScript. Performance is not a luxury. Many of your visitors pay for each megabyte and use unreliable networks. A heavy page costs them money and many will leave before it loads. Fast pages also rank better in search engines.</p>

<h3>Measure first</h3>
<p>Do not guess; measure. Open developer tools (F12), go to the <strong>Network</strong> tab, tick "Disable cache", set the throttle option to a slow profile such as "Slow 4G" or "Fast 3G", and reload. You will see every file, its size and how long it took. The <strong>Lighthouse</strong> panel gives a performance score and advice. Pay attention to total page weight, number of requests and the three "Core Web Vitals": how fast the main content appears, how quickly the page reacts to a tap, and how much the layout jumps while loading.</p>

<h3>1. Images: the biggest win</h3>
<p>Images usually make up most of a page's weight. A photo straight from a phone can be 4 MB; a web page rarely needs more than 100 to 200 KB for a large picture.</p>
<ul>
<li><strong>Resize</strong> images to the largest size they will really be shown (not a 4000-pixel photo for a 400-pixel space).</li>
<li><strong>Compress</strong> and use modern formats such as <strong>WebP</strong> or <strong>AVIF</strong> for photos, SVG for logos and icons.</li>
<li><strong>Lazy-load</strong> images below the fold, and always give <code>width</code> and <code>height</code> so the layout does not jump:</li>
</ul>
<pre><code>&lt;img src="classroom.webp" alt="Learners in a computer lab"
     width="800" height="450" loading="lazy"&gt;</code></pre>
<p>Do not lazy-load the main picture at the very top of the page, because that delays what visitors see first.</p>

<h3>2. Caching</h3>
<p><strong>Caching</strong> means the browser keeps a copy of files so it does not download them again. The server controls this with response headers such as <code>Cache-Control</code>. A common approach: files whose names change when their content changes (for example <code>app.3f9a1c.js</code>, created by build tools) can be cached for a year; the HTML page itself is checked each time so visitors get updates. Static hosting services usually set sensible caching for you. Using a CDN (content delivery network), which keeps copies of your files on servers in many countries, shortens the distance to visitors.</p>

<h3>3. Less JavaScript and CSS</h3>
<ul>
<li><strong>Bundle size</strong> is the total size of the JavaScript the visitor must download and run. On a cheap phone, large bundles are slow even after they download, because the phone must also process them.</li>
<li>Before adding a library, ask: do I need it? A 300 KB library to format one date is a poor trade; a few lines of code or the built-in <code>Intl.DateTimeFormat</code> may be enough.</li>
<li><strong>Minify</strong> (remove spaces and shorten names) and let your build tool do <strong>tree shaking</strong> (leaving out code you never use).</li>
<li><strong>Code split</strong>: load heavy features only when needed, with a dynamic import:</li>
</ul>
<pre><code>const button = document.querySelector("#show-chart");
button.addEventListener("click", async () =&gt; {
  const { drawChart } = await import("./chart.js");   // loaded only on click
  drawChart();
});</code></pre>
<ul>
<li>Load scripts with <code>defer</code> (or as modules) so they do not block the page from appearing: <code>&lt;script src="app.js" defer&gt;&lt;/script&gt;</code>.</li>
</ul>

<h3>4. Fonts and extras</h3>
<p>Each custom font file adds download time. Use one or two families and few weights, or rely on system fonts. Avoid auto-playing video, and compress or stream any video you must include. Enable compression (gzip or Brotli) on the server; most hosts do this by default.</p>

<h3>Performance budget</h3>
<p>A <strong>performance budget</strong> is a limit you set and keep, such as "the home page must stay under 500 KB" or "JavaScript under 150 KB". When a change breaks the budget, you fix it before publishing. A budget turns performance into a habit.</p>

<h3>Try it yourself</h3>
<ol>
<li>Open one of your pages, set the Network tab to a slow connection, and write down the total size and load time.</li>
<li>Resize and compress your biggest image to under 200 KB (a free online tool or image editor will do), then compare the numbers.</li>
<li>Add <code>width</code>, <code>height</code> and <code>loading="lazy"</code> to images below the first screen.</li>
<li>Run Lighthouse before and after, and write one sentence about what improved.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Measure with the Network tab and Lighthouse on a throttled connection.</li>
<li>Images are the biggest win: resize, compress, use WebP, lazy-load and set dimensions.</li>
<li>Caching and CDNs avoid repeated downloads; hashed file names allow long caching.</li>
<li>Keep JavaScript small: avoid needless libraries, minify, split code and use <code>defer</code>.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Security basics and testing ideas',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain and prevent the most common web security problems (XSS, insecure connections, trusting input, leaked secrets) and describe simple ways to test your code. Anything on the internet is attacked constantly by automated tools, so basic security is part of building, not an optional extra at the end.</p>

<h3>Cross-site scripting (XSS)</h3>
<p><strong>XSS</strong> happens when your page takes text from a user (a comment, a name, a URL parameter) and puts it into the page as HTML. If the text contains a script, the browser runs it as if you wrote it. The attacker's code can then steal data, act as the visitor or change the page.</p>
<pre><code>// DANGEROUS: user text is treated as HTML
comments.innerHTML = "&lt;p&gt;" + userText + "&lt;/p&gt;";
// If userText is   &lt;img src=x onerror&#61;stealData()&gt;   the code inside onerror runs.

// SAFE: user text is treated as plain text
const p = document.createElement("p");
p.textContent = userText;
comments.appendChild(p);</code></pre>
<p>Rules to remember:</p>
<ul>
<li>Use <code>textContent</code> (or <code>createElement</code>) for any data that is not 100 percent yours. Avoid <code>innerHTML</code>, <code>document.write</code> and <code>eval</code> with such data.</li>
<li>If you must show user-written HTML (such as a rich text editor), clean it first with a well-known sanitising library; do not write your own filter.</li>
<li>Escape output on the server too, and use a <strong>Content Security Policy</strong> header that limits where scripts may come from, as a second layer of defence.</li>
<li>Never place a user-supplied address in a link without checking that it starts with <code>https://</code> (or <code>http://</code>), because the old scheme starting with the word "javascript" can run code.</li>
</ul>

<h3>HTTPS</h3>
<p>HTTPS encrypts traffic between browser and server, so passwords and form data cannot be read or altered on the way (important on shared Wi-Fi). It also proves the site is the real one and is required by many browser features. Modern hosting services give you HTTPS for free with automatic certificates. Make sure every page, image, script and API call uses <code>https://</code>; a single <code>http://</code> script on an HTTPS page is blocked as "mixed content".</p>

<h3>Input validation</h3>
<p>Never trust data that comes from outside your code: form fields, URL parameters, files, API responses. Validate on the <strong>server</strong> (the front end can be bypassed) by checking type, length and allowed values. Validate with an <strong>allow-list</strong> (what is permitted) rather than trying to list everything bad. When talking to a database, never join user text into a query; use parameterised queries, which treat the text as data only. Limit file uploads by type and size.</p>

<h3>Secrets and passwords</h3>
<ul>
<li>Keep keys on the server in environment variables, never in front-end code or Git (see the earlier lesson on keys).</li>
<li>Never store passwords as plain text. Servers store a slow, salted hash made with a proven algorithm such as bcrypt or Argon2, and you should use a trusted login service or your framework's tools rather than inventing your own.</li>
<li>Do not put sensitive data in <code>localStorage</code>; any script on the page can read it.</li>
<li>Keep dependencies updated, and run <code>npm audit</code> to see known problems in packages you use.</li>
</ul>

<h3>Testing ideas</h3>
<p>Testing means checking automatically that code does what you expect. Three levels exist: <strong>unit tests</strong> check one small function; <strong>integration tests</strong> check that parts work together; <strong>end-to-end tests</strong> drive a real browser through a user journey. Node includes a basic test runner, so you can start with no extra tools. Create <code>format.js</code>:</p>
<pre><code>export function formatUGX(amount) {
  return "UGX " + Math.round(amount).toLocaleString("en-US");
}</code></pre>
<p>and <code>format.test.js</code>:</p>
<pre><code>import test from "node:test";
import assert from "node:assert/strict";
import { formatUGX } from "./format.js";

test("formats thousands with a separator", () =&gt; {
  assert.equal(formatUGX(15000), "UGX 15,000");
});

test("rounds to whole shillings", () =&gt; {
  assert.equal(formatUGX(999.6), "UGX 1,000");
});</code></pre>
<p>Run them with <code>node --test</code>. Good tests are small, test one behaviour, and cover normal cases, edge cases (zero, empty, very large) and error cases. Each time you fix a bug, add a test that would have caught it. Popular tools for bigger projects include Vitest or Jest for unit tests and Playwright for end-to-end tests.</p>

<h3>Try it yourself</h3>
<ol>
<li>Build a page with a text box and a button that adds the text to a list using <code>textContent</code>. Type <code>&lt;b&gt;hi&lt;/b&gt;</code> and confirm it appears as plain text.</li>
<li>Check that every address in one of your projects starts with <code>https://</code>.</li>
<li>Create the two files above, run <code>node --test</code> and watch the tests pass; then change a result in a test to see it fail.</li>
<li>Run <code>npm audit</code> in a project that has dependencies and read the summary.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>XSS is prevented by treating user data as text (<code>textContent</code>), not HTML, and by sanitising when HTML is unavoidable.</li>
<li>Use HTTPS everywhere; validate input on the server with allow-lists; use parameterised database queries.</li>
<li>Keep secrets out of code; hash passwords properly; update dependencies.</li>
<li>Write small automated tests for important functions and for every bug you fix.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does the viewport meta tag do on a mobile phone?
- Adds a mobile app icon
* Makes the page use the device width instead of a zoomed-out desktop layout
- Speeds up the page
- Blocks scripts
= Without it, phones pretend the screen is wide and shrink the whole page.

Q: What does "mobile first" mean?
- Only phones are supported
* Write the base styles for small screens, then add media queries for larger ones
- Test on a phone last
- Use only fixed pixel widths
= Starting small keeps the layout simple and fast on the devices most people use.

Q: Which are the four WCAG principles? (choose the correct set)
- Fast, Cheap, Pretty, Secure
* Perceivable, Operable, Understandable, Robust
- Visible, Clickable, Colourful, Simple
- Read, Write, Test, Publish
= The POUR principles organise the accessibility guidelines.

Q: Which change is likely to speed up a page the most?
- Using a longer file name
* Resizing and compressing large images
- Adding more fonts
- Putting scripts in the head without defer
= Images are usually the heaviest part of a page.

Q: Why give img elements width and height attributes?
- To make them clickable
* So the browser can reserve space and the layout does not jump while loading
- To hide them
- Because alt text needs them
= Reserving space prevents layout shift.

Q: Which code is safest for showing a visitor's comment on the page?
- comments.innerHTML = userText
- document.write(userText)
* paragraph.textContent = userText
- eval(userText)
= textContent treats the value as text, so injected scripts do not run.

Q: Where must input validation always be done to be secure?
- Only in the browser
* On the server (browser checks are a convenience)
- Only in CSS
- It is not needed with HTTPS
= Anyone can bypass front-end checks.

Q: What does node --test do?
* Runs test files using Node's built-in test runner
- Tests the internet connection
- Publishes the project
- Installs packages
= It finds and runs *.test.js files and reports passes and failures.
QUIZ,
        ],
        [
            'title'   => 'Module 5: Shipping your work',
            'summary' => 'Build a project end to end, publish it on free static hosting, understand domains, DNS, analytics and privacy, and finish with a capstone project.',
            'lessons' => [
                [
                    'title'   => 'From idea to finished project: a build workflow',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to take a project from a rough idea to a finished, deployable folder by following a repeatable workflow. Many beginners start coding straight away and get lost; professionals spend a little time on planning and then work in small, safe steps. This lesson pulls together everything in the course.</p>

<h3>Step 1: Define the problem and the user</h3>
<p>Write two or three sentences: <em>Who</em> is this for? <em>What</em> problem does it solve? <em>What is the one most important thing it must do?</em> Example: "Small shop owners in Gulu need a quick way to calculate daily takings and see which products sell best. The page must let them add a sale and see today's total." A clear sentence like this protects you from adding features endlessly.</p>

<h3>Step 2: List features and cut</h3>
<p>List everything you can imagine, then sort into <strong>must have</strong>, <strong>should have</strong> and <strong>later</strong>. The first version, called a <strong>minimum viable product</strong> (MVP), contains only the must-haves. Finish something small and useful before making it bigger.</p>

<h3>Step 3: Sketch and structure</h3>
<p>Draw the screens on paper, on a phone-sized rectangle first. Decide the pages, the main elements, and the data you need to store (for example, a sale has a product name, a price and a time). Choose the structure of the folder before you start:</p>
<pre><code>shop-tracker/
  index.html
  README.md
  package.json
  .gitignore
  src/
    main.js
    storage.js
    sales.js
    styles.css
  public/
    favicon.svg</code></pre>

<h3>Step 4: Set up the project</h3>
<ol>
<li>Create the folder, run <code>git init</code> and <code>npm init -y</code>.</li>
<li>Add <code>.gitignore</code> (node_modules, .env, dist) and a short <code>README.md</code>.</li>
<li>Create the repository on GitHub and push the first commit.</li>
<li>Add Prettier and ESLint with <code>format</code> and <code>lint</code> scripts.</li>
</ol>

<h3>Step 5: Build in small slices</h3>
<p>Work in thin vertical slices, one working feature at a time, each on its own branch and commit. For the shop tracker:</p>
<ol>
<li>Static HTML and CSS for the page, readable on a phone.</li>
<li>A form that adds a sale to an array and shows it in a list.</li>
<li>Totals calculated by a pure function, with a test.</li>
<li>Saving to <code>localStorage</code> and loading on start.</li>
<li>Validation and friendly messages; keyboard and screen reader checks.</li>
</ol>
<p>Here is the heart of step 3, a pure function that is easy to test:</p>
<pre><code>// sales.js
export function totalOf(sales) {
  return sales.reduce((sum, sale) =&gt; sum + sale.price * sale.qty, 0);
}

export function bestSeller(sales) {
  const counts = {};
  for (const sale of sales) {
    counts[sale.name] = (counts[sale.name] ?? 0) + sale.qty;
  }
  const entries = Object.entries(counts);
  if (entries.length === 0) return null;
  entries.sort((a, b) =&gt; b[1] - a[1]);
  return entries[0][0];
}</code></pre>
<p>A test for it, in <code>sales.test.js</code>:</p>
<pre><code>import test from "node:test";
import assert from "node:assert/strict";
import { totalOf, bestSeller } from "./sales.js";

const sales = [
  { name: "Sugar", price: 5000, qty: 2 },
  { name: "Soap", price: 2000, qty: 5 },
  { name: "Sugar", price: 5000, qty: 1 }
];

test("adds up the total", () =&gt; {
  assert.equal(totalOf(sales), 25000);
});

test("finds the best seller", () =&gt; {
  assert.equal(bestSeller(sales), "Soap");
  assert.equal(bestSeller([]), null);
});</code></pre>
<p>The numbers work out: sugar is 10000 plus 5000, soap is 10000, and the total is 25000. Soap sold 5 units against sugar's 3, so soap is the best seller.</p>

<h3>Step 6: Check quality before release</h3>
<ul>
<li>Run <code>npm run lint</code>, <code>npm run format</code> and <code>node --test</code>.</li>
<li>Test on a real phone and at 360 pixels wide; unplug the mouse and use only the keyboard.</li>
<li>Run Lighthouse for performance and accessibility.</li>
<li>Look for secrets, personal data and broken links. Read your README as a stranger would.</li>
</ul>

<h3>Step 7: Release and learn</h3>
<p>Publish (next lessons), share the link with real users, and watch how they use it. Their confusion shows what to improve. Keep a list of ideas and bugs (GitHub Issues works well) and decide what to do next.</p>

<h3>Try it yourself</h3>
<ol>
<li>Pick a small project idea from your community and write the problem statement in three sentences.</li>
<li>List features and mark which are must-haves for the first version.</li>
<li>Create the repository, folders, README and first commit.</li>
<li>Write <code>totalOf</code> and its test, run <code>node --test</code> and confirm it passes.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Start by defining the user and the problem; build a minimum viable product first.</li>
<li>Set up Git, GitHub, linting and formatting before the main coding.</li>
<li>Build in small slices with commits, pure functions and tests.</li>
<li>Check quality, release, then improve using real feedback.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Free static hosting, domains and DNS',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what a static site is, describe what the common free hosting services do and how they differ, and explain in plain words how a domain name finds your site through DNS. Knowing the ideas matters more than memorising buttons, because the screens of hosting services change often.</p>

<h3>Static versus dynamic</h3>
<p>A <strong>static site</strong> is made of files that are sent to the visitor exactly as they are stored: HTML, CSS, JavaScript, images. A <strong>dynamic site</strong> builds each page on a server for each visitor, usually with a language such as PHP or Node.js and a database. A static site can still be interactive, because JavaScript runs in the browser and can call APIs. Static sites are fast, cheap or free to host, and have fewer security risks because there is no server code to attack. Portfolios, documentation, landing pages, small tools and many business sites are perfect as static sites.</p>

<h3>What is a build?</h3>
<p>If your project uses a tool like Vite, you run <code>npm run build</code> and it produces an output folder (commonly named <code>dist</code> or <code>build</code>) with the final optimised files. That folder is what gets published. For a plain HTML project, the project folder itself is published.</p>

<h3>Free static hosting services</h3>
<p>Several services will publish a static site for free, within limits on storage and traffic. They all do the same core job: they store your files on fast servers, send them to visitors over HTTPS, and give your site an address.</p>
<table>
<thead><tr><th>Service</th><th>What it does</th><th>Typical way of working</th></tr></thead>
<tbody>
<tr><td>GitHub Pages</td><td>Publishes the files of a GitHub repository as a website at an address based on your account name</td><td>You push your code to GitHub and Pages serves the chosen branch or the output of an automatic build</td></tr>
<tr><td>Netlify</td><td>Connects to a repository, runs your build command and publishes the result; adds features such as forms and serverless functions</td><td>Each push triggers a new build and a new version of the site; every branch can get a preview address</td></tr>
<tr><td>Cloudflare Pages</td><td>Builds and publishes sites from a repository on Cloudflare's global network</td><td>Similar to Netlify, with preview deployments and easy links to Cloudflare's other services</td></tr>
</tbody>
</table>
<p>The idea behind the second and third is called <strong>continuous deployment</strong>: whenever you push to the main branch, the service automatically builds and publishes the new version. Because the old versions are kept, you can roll back if something breaks. Free plans have limits and the rules change, so read the current terms and pricing on each service's own website before choosing. The main settings you will be asked for are: which repository, which branch, the build command (for example <code>npm run build</code>) and the output folder (for example <code>dist</code>). Single-page apps may need a rule to send all paths to <code>index.html</code>; check each service's documentation.</p>

<h3>How a domain name works</h3>
<p>Computers find each other by numeric <strong>IP addresses</strong> such as <code>203.0.113.10</code>. People prefer names. The <strong>domain name</strong> (such as <code>luminaafrica.example</code>) is a readable name that points to an IP address, and the system that translates names to addresses is <strong>DNS</strong> (Domain Name System), like a phone book for the internet.</p>
<ol>
<li>You type the name in the browser.</li>
<li>The browser asks a DNS resolver (usually run by your internet provider or a public service) where that name lives.</li>
<li>The resolver follows the chain of DNS servers until it finds the answer, and returns the IP address (or the name of another host).</li>
<li>The browser connects to that address and requests the page.</li>
</ol>
<p>Answers are remembered for a while, a time set by the <strong>TTL</strong> (time to live). That is why changes to DNS can take minutes or even hours to be visible everywhere.</p>

<h3>Common DNS records</h3>
<table>
<thead><tr><th>Record</th><th>Purpose</th></tr></thead>
<tbody>
<tr><td>A</td><td>Points a name to an IPv4 address</td></tr>
<tr><td>AAAA</td><td>Points a name to an IPv6 address</td></tr>
<tr><td>CNAME</td><td>Makes one name an alias of another name (often used for <code>www</code>)</td></tr>
<tr><td>MX</td><td>Says which servers receive email for the domain</td></tr>
<tr><td>TXT</td><td>Holds text, used to prove ownership or to protect email</td></tr>
</tbody>
</table>

<h3>Getting a domain</h3>
<p>You rent a domain for a period (usually a year) from a <strong>registrar</strong>. Prices depend on the ending (<code>.com</code>, <code>.org</code>, <code>.ug</code> and so on); country endings may have their own rules, so check with the official registry or an approved registrar. To use your domain with a hosting service, you add the records that the service tells you to add (often a CNAME for <code>www</code> and A records or an alias for the main name), then wait for DNS to update. The host then issues a free HTTPS certificate for your name automatically. Keep your registrar account secure with a strong password and two-step verification: whoever controls the domain controls your website and email.</p>

<h3>Try it yourself</h3>
<ol>
<li>Choose one hosting service from the table and read its documentation page about publishing a site. Write down the four settings it requires (repository, branch, build command, output folder).</li>
<li>Draw the four steps of a DNS lookup for a site you visit.</li>
<li>Search for a domain name you might want and compare the yearly price of two different endings at a registrar (do not buy anything now).</li>
<li>Write the DNS records you would need to point <code>www</code> to a host and explain what a CNAME does.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Static sites are simple files; JavaScript can still make them interactive by calling APIs.</li>
<li>Services such as GitHub Pages, Netlify and Cloudflare Pages publish your files over HTTPS, and many rebuild automatically when you push.</li>
<li>DNS translates a readable domain name into an address; changes take time because of caching (TTL).</li>
<li>Protect the registrar account: control of the domain means control of your site and email.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Analytics, privacy, maintenance and your capstone',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain how to measure a site's use while respecting visitors' privacy, how to keep a published project healthy over time, and how to plan and deliver your capstone project. A launched site is the start of a project, not the end: it needs care, listening and updates.</p>

<h3>Analytics: learning from real use</h3>
<p><strong>Analytics</strong> tools count visits and show which pages are popular, where visitors come from, what device they use, and where they leave. This helps you decide what to improve. Examples of choices include privacy-focused services that do not use cookies, and larger platforms with more detail. You usually add a small script from the service to your pages. Some hosts also provide basic traffic numbers without any script.</p>
<p>Questions analytics can answer: Are most visitors on phones? Which page do people leave first? Does a new page get used? Treat the numbers as clues, not proof. Small numbers can be misleading, so look for patterns over weeks, and talk to real users as well.</p>

<h3>Privacy: respect your visitors</h3>
<p>People trust you with information. Good practice protects them and also protects you legally. Uganda has a Data Protection and Privacy Act, and many other countries have similar laws, so find out which rules apply to your visitors and your organisation.</p>
<ul>
<li><strong>Collect only what you need.</strong> If a contact form does not need a date of birth, do not ask for it.</li>
<li><strong>Be open.</strong> Write a short, plain-language privacy page: what you collect, why, who sees it, how long you keep it, and how to ask for deletion.</li>
<li><strong>Ask consent</strong> before setting non-essential cookies or trackers, and make declining as easy as accepting. Choosing a cookie-free analytics tool avoids many problems.</li>
<li><strong>Protect what you store.</strong> Use HTTPS, restrict who can see the data, and delete what you no longer need.</li>
<li><strong>Be careful with third-party scripts</strong> such as embedded videos, maps, fonts and widgets: they may track visitors. Include only those you need.</li>
<li><strong>Take extra care with children's data</strong> and photographs of people; get permission first.</li>
</ul>

<h3>Maintaining a project</h3>
<ul>
<li><strong>Keep a README and notes</strong> so you (or the next person) can run the project in a year.</li>
<li><strong>Update dependencies</strong> regularly: run <code>npm outdated</code> and <code>npm audit</code>, update in a branch, run your tests, then merge.</li>
<li><strong>Use Issues</strong> on GitHub to track bugs and ideas; label them and close them when done.</li>
<li><strong>Back up</strong> anything that is not in Git: content, images, databases.</li>
<li><strong>Monitor</strong>: check the site occasionally, and use a free uptime monitor if the site matters to a business.</li>
<li><strong>Renew</strong> your domain on time, and keep the contact email on the registrar account up to date.</li>
<li><strong>Version your releases</strong> with Git tags and write a short changelog, so you can see and undo what changed.</li>
</ul>

<h3>The capstone project</h3>
<p>The capstone is your chance to show everything you have learned in one real piece of work.</p>
<h4>Brief</h4>
<p>Build and publish a small, useful web application for a real audience in your school, business, church, mosque or community. It must:</p>
<ol>
<li>Solve a clearly stated problem for a named group of users, in a written problem statement of at most one page.</li>
<li>Use modern JavaScript with at least two modules, <code>async</code> and <code>await</code>, and proper error handling.</li>
<li>Call at least one public API without exposing any secret (or use your own data with <code>localStorage</code> if no API fits), with loading, success and error states.</li>
<li>Be responsive down to 360 pixels, usable with the keyboard alone, and score at least 90 for accessibility in Lighthouse.</li>
<li>Keep user text safe from XSS and keep secrets out of the repository.</li>
<li>Include at least three automated tests that run with one command.</li>
<li>Live in a GitHub repository with a clear README, regular small commits and at least one pull request.</li>
<li>Be published on a free static host over HTTPS, with a short privacy note if it collects any data.</li>
</ol>
<h4>Suggested ideas</h4>
<p>A crop price checker, a school timetable and notice page, a savings group tracker, a health tips finder, a small business catalogue with an order form that creates a message, a community events calendar, or a flash-card app for exam revision.</p>
<h4>Assessing your own work</h4>
<p>Before you call it done, ask: can a stranger understand what it is for in ten seconds? Does it work on a cheap phone on slow data? Can I use it with a keyboard only? Is anything secret in the repository? Did real users try it, and what did they say? Write a short reflection on what you would change next.</p>

<h3>Try it yourself</h3>
<ol>
<li>Write your capstone problem statement and a list of must-have features.</li>
<li>Create the repository with a README that describes the problem and how to run the project.</li>
<li>Draft a short privacy note for your project saying what, if anything, you collect.</li>
<li>Set a schedule with dates for: setup, first working slice, tests, accessibility check, publishing and user feedback.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Analytics show how people use your site; choose privacy-friendly tools and read numbers with care.</li>
<li>Collect only what you need, be open about it, ask consent where required and protect the data you hold.</li>
<li>Maintain projects with updates, issues, backups, monitoring and domain renewals.</li>
<li>The capstone brings together modern JavaScript, APIs, tooling, quality and publishing in one real project.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is a minimum viable product (MVP)?
- A product with every feature imaginable
* The smallest first version that delivers the most important thing for users
- A prototype that is never shown to anyone
- A product with no testing
= Building a small useful version first lets you learn from real users before adding more.

Q: Which of these is a static site?
* A set of HTML, CSS, JavaScript and image files served as they are
- A site that builds each page on a server from a database for every visitor
- A desktop program
- A mobile app store
= Static sites need no server-side code, though their JavaScript can still call APIs.

Q: What is continuous deployment on a service such as Netlify or Cloudflare Pages?
- You must upload files by email
* The service builds and publishes the site automatically whenever you push to the chosen branch
- The site never changes
- The service writes your code
= Pushing to the repository triggers a build and a new live version.

Q: What does DNS do?
- Encrypts your passwords
* Translates a readable domain name into the address of the server
- Compresses images
- Hosts your files
= DNS works like a phone book for the internet.

Q: You change a DNS record, but some visitors still see the old site for a few hours. Why?
- DNS is broken
* Answers are cached for a period set by the TTL
- Browsers ignore DNS
- Your host is slow
= Resolvers and devices remember old answers until the time to live expires.

Q: What does a CNAME record do?
- Sends email
* Makes one name an alias of another name
- Hosts a file
- Encrypts traffic
= It is often used so that www points to the same place as another host name.

Q: Which are good privacy practices for a small website? (choose all that apply)
* Collect only the information you need
* Write a plain-language privacy page
* Make declining non-essential cookies as easy as accepting
- Keep all data for ever in case it is useful
= Data minimisation, openness and genuine consent protect visitors. Keep data only as long as needed.

Q: Which maintenance task helps find known security problems in the packages you use?
- Renaming the repository
* Running npm audit and updating dependencies
- Deleting the README
- Changing the favicon
= Regular audits and updates reduce known risks.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What is the difference between a named export and a default export?
- There is no difference
* A file can have many named exports (imported with curly brackets) but only one default export
- Default exports can only be numbers
- Named exports cannot be functions
= Named imports must match the exported names; a default import can be given any name.

Q: What is the value of b? const [a, ...b] = [1, 2, 3];
- 1
- [1, 2]
* [2, 3]
- 3
= The rest syntax gathers the remaining items into a new array.

Q: Which expression creates a new object with the theme changed and everything else kept? Assume const settings = { theme: "light", lang: "en" };
- settings.theme = "dark"
* { ...settings, theme: "dark" }
- settings + { theme: "dark" }
- delete settings.theme
= Object spread copies the properties and the later value overrides the earlier one, without changing the original.

Q: In which order does this print? console.log(1); setTimeout(() => console.log(2), 0); Promise.resolve().then(() => console.log(3)); console.log(4);
- 1 2 3 4
- 1 4 2 3
* 1 4 3 2
- 4 3 2 1
= Synchronous code runs first (1, 4), then promise callbacks (3), then timer callbacks (2).

Q: What happens if you forget await in front of a call to an async function that returns data?
- The data is returned normally
* You get a pending promise instead of the data
- The browser crashes
- Nothing, JavaScript adds it for you
= await is what unwraps the promise into its value.

Q: Which statements about try, catch and finally are correct? (choose all that apply)
* finally runs whether or not an error occurred
* catch receives the error that was thrown
- try prevents any error from ever happening
- catch runs when there is no error
= try and catch handle failures; finally is for clean-up that must always happen.

Q: What does a # in front of a class field name, such as #balance, mean?
- It is a comment
* The field is private and can only be used inside the class
- It is a constant
- It is a number
= Private fields protect data so it can only be changed through the class's methods.

Q: Which method should be used to ask a server to create a new resource?
- GET
* POST
- HEAD
- TRACE
= POST sends data to create something new.

Q: A request returns status 403. What does it mean?
- The server crashed
- The page moved permanently
* The server understood the request but refuses to allow it
- The request succeeded
= 403 Forbidden is a client-side (4xx) problem of permission.

Q: Why does fetch not throw an error for a 500 response?
- It is a bug
* fetch rejects only on network failure; HTTP error statuses still produce a response, so check response.ok
- 500 is a success code
- The browser hides it
= The promise is rejected only if the request could not be made or completed.

Q: Which header must be set for the browser to let your script read a cross-origin response?
- Content-Length
- Set-Cookie
* Access-Control-Allow-Origin
- Accept-Language
= The server of the other origin must send it, naming your origin or *.

Q: You see "blocked by CORS policy" for an API you do not control. What is the best solution?
- Add more try and catch blocks
* Call the API from your own server and let your page call that server
- Disable HTTPS
- Use eval
= CORS is enforced by the browser; a server-to-server call is not subject to it.

Q: Which is the safest place for a paid API's secret key?
- In a JavaScript file loaded by the page
- In an HTML comment
* In an environment variable on a server you control
- In localStorage
= Anything sent to the browser can be read by visitors.

Q: Which command creates a new branch and moves to it?
- git branch -d name
* git switch -c name
- git merge name
- git commit name
= git switch -c creates and switches in one step.

Q: What should be in .gitignore for a typical Node project? (choose all that apply)
* node_modules/
* .env
- package.json
- index.html
= Dependencies can be reinstalled and secrets must not be committed. package.json and index.html belong in the repository.

Q: What is the correct way to undo a commit that has already been shared?
- Delete the repository
* Use git revert to create a new commit that reverses it
- Rename the branch
- Edit the commit message only
= Revert keeps history intact and is safe on shared branches.

Q: What does npm install do when there is a package-lock.json and a package.json?
- Publishes the project
* Installs the dependencies recorded there into node_modules
- Deletes the lock file
- Removes all packages
= It rebuilds node_modules from the recorded dependencies.

Q: What is the difference between ESLint and Prettier?
- They do exactly the same
* ESLint finds likely bugs and bad habits; Prettier formats code consistently
- Prettier runs tests; ESLint hosts websites
- ESLint only works in browsers
= They complement each other: one checks quality, the other style.

Q: Which CSS approach makes layout adapt to larger screens after designing for phones first?
- @media (max-width: 0)
* @media (min-width: 40rem) { ... } added after the base mobile styles
- position: fixed on everything
- Setting every width in pixels
= Mobile-first designs start with the small layout and add min-width media queries.

Q: Which of these help meet WCAG? (choose all that apply)
* Text alternatives for images
* Sufficient colour contrast
* Operation by keyboard alone
- Removing the focus outline to look cleaner
= Perceivable and operable content needs alt text, contrast and keyboard access, and visible focus.

Q: Which is the best first step to speed up a heavy page full of large photographs?
- Add more JavaScript
* Resize and compress the images and use modern formats
- Change the font colour
- Remove the viewport tag
= Images are usually the largest part of page weight.

Q: Why does user text placed with innerHTML create a risk?
- It is slower
* An attacker can include a script or HTML that runs on your page (XSS)
- It deletes the DOM
- It is not supported by browsers
= Use textContent for untrusted text.

Q: Which testing statement is true?
- Tests are only needed at the very end
* A unit test checks one small piece of code, and adding a test for each fixed bug prevents it coming back
- Tests make code insecure
- End-to-end tests cannot use a browser
= Small automated tests protect against regressions as projects grow.

Q: Which settings do static hosting services usually ask for when connecting a repository? (choose all that apply)
* The branch to publish
* The build command
* The output folder
- Your registrar password
= Hosting services need to know what to build and what to publish. Never give them unrelated passwords.

Q: Someone buys your domain's registrar account password from a data leak. Why is this serious?
- It is not serious
* Whoever controls the domain controls the website address and email, so use strong passwords and two-step verification
- Domains cannot be changed
- Only the logo is affected
= Protect the registrar account as you would a bank account.
QUIZ,
];
