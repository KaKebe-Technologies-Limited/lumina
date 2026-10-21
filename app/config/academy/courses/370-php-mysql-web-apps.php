<?php
return [
    'slug' => 'php-mysql-web-apps',
    'title' => 'Web Applications with PHP and MySQL',
    'category' => 'web',
    'level' => 'advanced',
    'icon' => 'code',
    'sort' => 370,
    'summary' => 'Build secure, database-driven web applications with PHP 8 and MySQL: forms, sessions, PDO, CRUD, login, safe uploads, MVC-style structure, JSON APIs and deployment on shared hosting.',
    'description' => '<p>PHP and MySQL power a large share of the websites that Ugandan businesses, schools and organisations use every day, from small shops to large content systems. Knowing how to build a proper database-driven application in PHP is a practical skill that local employers and freelance clients ask for, and shared hosting that runs PHP is cheap and widely available.</p><p>This course takes you from a working XAMPP set-up to a deployed application. You will write modern PHP 8 code, handle forms and sessions, talk to MySQL with <strong>PDO</strong> and prepared statements, build create, read, update and delete (CRUD) screens, register and log in users with <code>password_hash</code>, and defend your code against the most common attacks: cross-site scripting (XSS), cross-site request forgery (CSRF) and SQL injection. You will then organise code in an MVC-style structure with simple routing, add JSON endpoints, and put the finished application on shared hosting.</p><p>The course assumes you can write HTML and CSS, understand tables and queries in SQL, and know basic JavaScript. Security is taught throughout, not as an afterthought, because a web application that works but is not safe is a liability to the people who trust it with their data.</p>',
    'outcomes' => [
        'Install and run a local PHP and MySQL development environment with XAMPP.',
        'Write PHP 8 code using variables, arrays, functions, classes and control flow, and process HTML forms.',
        'Connect to MySQL with PDO and use prepared statements for every query.',
        'Build complete create, read, update and delete screens with validation and the post-redirect-get pattern.',
        'Register and log in users securely with sessions and password_hash, and protect pages.',
        'Prevent XSS, CSRF and SQL injection and handle file uploads safely.',
        'Organise an application with includes, controllers, views, models and a front-controller router, and expose JSON endpoints.',
        'Configure errors, secrets and deployment so the application runs safely on shared hosting.',
    ],
    'requirements' => [
        'Completion of Web Basics: Build Your First Web Page (HTML and CSS)',
        'Completion of Databases and SQL for Beginners',
        'Completion of JavaScript for Beginners: Make Web Pages Interactive',
        'A Windows, Mac or Linux computer on which you may install software (XAMPP)',
    ],
    'pass_mark' => 70,
    'resources' => [
        ['title' => 'PHP: official website', 'url' => 'https://www.php.net/'],
        ['title' => 'PHP manual', 'url' => 'https://www.php.net/manual/en/'],
        ['title' => 'MySQL: official website', 'url' => 'https://www.mysql.com/'],
        ['title' => 'XAMPP: Apache Friends', 'url' => 'https://www.apachefriends.org/'],
        ['title' => 'OWASP: Web security project', 'url' => 'https://owasp.org/'],
    ],
    'modules' => [
        [
            'title' => 'Module 1: Your development environment and PHP fundamentals',
            'summary' => 'Install XAMPP, run your first PHP page, learn the core language and receive data from HTML forms safely.',
            'lessons' => [
                [
                    'title' => 'Setting up XAMPP and running your first PHP page',
                    'minutes' => 16,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to run Apache, MySQL and PHP on your own computer and view a PHP page in your browser. A local server lets you build and break things safely, without a hosting account or even an internet connection, which matters when data bundles are expensive.</p>

<h3>How a PHP page works</h3>
<p>A normal HTML file is sent to the browser exactly as it is. A PHP file is different: the web server (<strong>Apache</strong>) first passes it to the PHP interpreter, which runs the PHP code inside it and produces plain HTML. Only that HTML reaches the browser, so visitors never see your PHP code, your database password or your logic. The data lives in <strong>MySQL</strong> (XAMPP actually ships MariaDB, a compatible fork, which behaves the same for everything in this course).</p>
<p>XAMPP is a free bundle of Apache, MariaDB or MySQL, PHP and a database tool called phpMyAdmin. The letters stand for cross-platform (X), Apache, MariaDB, PHP and Perl.</p>

<h3>Installing and starting</h3>
<ol>
<li>Download XAMPP from the official site, <code>apachefriends.org</code>, choosing a version that includes PHP 8.1 or newer. Run the installer and keep the default folder, <code>C:\xampp</code> on Windows.</li>
<li>Open the <strong>XAMPP Control Panel</strong> and click <strong>Start</strong> next to <strong>Apache</strong> and next to <strong>MySQL</strong>. Their names turn green when they run.</li>
<li>Open a browser and go to <code>http://localhost</code>. You should see the XAMPP welcome page.</li>
</ol>
<p><strong>If Apache will not start</strong>, another program is probably using port 80 (Skype and some other services do, and so does IIS on Windows). Either stop that program or click <strong>Config</strong> next to Apache, open <code>httpd.conf</code>, change <code>Listen 80</code> to <code>Listen 8080</code>, and visit <code>http://localhost:8080</code> instead. If MySQL will not start, port 3306 is in use, most likely by another MySQL installation.</p>

<h3>Your first page</h3>
<p>Apache serves files from the <strong>htdocs</strong> folder, <code>C:\xampp\htdocs</code>. Create a folder inside it called <code>hello</code> and a file <code>index.php</code> with this content:</p>
<pre><code>&lt;?php
$name = "Amina";
$year = date("Y");
?&gt;
&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;&lt;meta charset="utf-8"&gt;&lt;title&gt;My first PHP page&lt;/title&gt;&lt;/head&gt;
&lt;body&gt;
  &lt;h1&gt;Hello, &lt;?= $name ?&gt;!&lt;/h1&gt;
  &lt;p&gt;The year is &lt;?= $year ?&gt;.&lt;/p&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
<p>Visit <code>http://localhost/hello/</code>. The page shows "Hello, Amina!" and the current year. If you instead see the raw code, you opened the file from your disk (the address begins with <code>file://</code>) instead of through the server, or the file does not end with <code>.php</code>. Choose <strong>View page source</strong> in the browser and notice that no PHP appears, only the finished HTML.</p>
<p>The tag <code>&lt;?php</code> starts PHP code and <code>?&gt;</code> ends it. The short tag <code>&lt;?= $name ?&gt;</code> prints a value. In files that contain only PHP, leave out the closing <code>?&gt;</code>, which avoids accidental blank lines that cause trouble later.</p>

<h3>The command line and phpMyAdmin</h3>
<p>XAMPP includes the PHP program itself. Open a terminal and run it with its full path:</p>
<pre><code>C:\xampp\php\php.exe -v</code></pre>
<p>It prints the version, and the first line begins with "PHP 8". The Admin button next to MySQL in the control panel opens <strong>phpMyAdmin</strong> at <code>http://localhost/phpmyadmin</code>, a web tool for databases that we use in module 2.</p>

<h3>A good editor</h3>
<p>Write code in an editor made for it, such as Visual Studio Code, with a PHP extension. Avoid word processors, which add hidden formatting. Save files as UTF-8 so that names with accents and other symbols display correctly.</p>

<h3>Local security note</h3>
<p>A default XAMPP database has a user called <code>root</code> with no password. That is fine on your own computer behind your router, but never use that set-up on a real server, and never expose your local XAMPP to a public network.</p>

<h3>Try it yourself</h3>
<ol>
<li>Install XAMPP and start Apache and MySQL.</li>
<li>Create the <code>hello</code> folder and the page above, and open it through <code>localhost</code>.</li>
<li>Change <code>$name</code> to your own name and refresh the page.</li>
<li>View the page source and confirm that no PHP code is visible.</li>
<li>Run <code>php.exe -v</code> and note your PHP version.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Apache runs your PHP files and sends only the resulting HTML to the browser.</li>
<li>Put your projects in <code>htdocs</code> and open them through <code>http://localhost/</code>, never through <code>file://</code>.</li>
<li>Port conflicts are the most common reason Apache or MySQL fails to start.</li>
<li>The default root user without a password is for local use only.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'PHP syntax: variables, types and strings',
                    'minutes' => 17,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to store values in variables, work with PHP's main data types and join and format text. These are the building blocks of every script you will write, so it is worth being precise about them.</p>

<h3>Variables and output</h3>
<p>A variable name starts with a dollar sign. Names are case sensitive (<code>$Price</code> and <code>$price</code> are different) and can contain letters, digits and underscores but cannot start with a digit. Statements end with a semicolon. A comment starts with <code>//</code>, <code>#</code> or sits between <code>/*</code> and <code>*/</code>.</p>
<pre><code>&lt;?php
declare(strict_types=1);

$product = "Maize flour";
$price   = 4500;            // integer
$weight  = 2.5;             // float
$inStock = true;            // boolean
$note    = null;            // no value

echo $product, " costs UGX ", number_format($price), "\n";
echo "Weight: $weight kg\n";
var_dump($inStock);
var_dump($note);</code></pre>
<pre>Maize flour costs UGX 4,500
Weight: 2.5 kg
bool(true)
NULL</pre>
<p><code>declare(strict_types=1);</code> must be the first statement in the file. It stops PHP from quietly converting values when you pass them to typed functions, which reveals bugs early. <code>var_dump</code> shows both the type and the value and is the best debugging friend you have.</p>

<h3>The main types</h3>
<table>
<thead><tr><th>Type</th><th>Example</th><th>Note</th></tr></thead>
<tbody>
<tr><td>string</td><td><code>"Kampala"</code></td><td>Text, in single or double quotes</td></tr>
<tr><td>int</td><td><code>42</code></td><td>Whole numbers</td></tr>
<tr><td>float</td><td><code>3.14</code></td><td>Decimal numbers; they are approximate</td></tr>
<tr><td>bool</td><td><code>true</code>, <code>false</code></td><td>Yes or no</td></tr>
<tr><td>array</td><td><code>[1, 2, 3]</code></td><td>A list or map, covered next lesson</td></tr>
<tr><td>null</td><td><code>null</code></td><td>The absence of a value</td></tr>
</tbody>
</table>

<h3>Single versus double quotes</h3>
<p>A double-quoted string <em>interpolates</em>: variables inside it are replaced by their values, and escapes such as <code>\n</code> (new line) work. A single-quoted string is taken literally. Use single quotes for plain text, which is slightly clearer and safer.</p>
<pre><code>$town = "Mbale";
echo "Welcome to $town\n";
echo 'Welcome to $town', "\n";
echo "Welcome to {$town}s\n";</code></pre>
<pre>Welcome to Mbale
Welcome to $town
Welcome to Mbales</pre>
<p>Curly braces tell PHP where the variable name ends. Join strings with the dot operator: <code>"Hello, " . $name</code>.</p>

<h3>Useful string functions</h3>
<pre><code>$text = "  Lumina Africa  ";
echo trim($text), "|\n";
echo strtoupper(trim($text)), "\n";
echo strlen(trim($text)), "\n";
echo str_replace("Africa", "Foundation", trim($text)), "\n";
echo substr("Kampala", 0, 4), "\n";
var_dump(str_contains("Kampala", "pal"));</code></pre>
<pre>Lumina Africa|
LUMINA AFRICA
13
Lumina Foundation
Kamp
bool(true)</pre>
<p>These functions count bytes, not letters, so for names with accented or non-Latin characters use the <code>mb_</code> versions such as <code>mb_strlen</code> and <code>mb_strtoupper</code>.</p>

<h3>Operators and comparison</h3>
<p>Arithmetic uses <code>+ - * / %</code> and <code>**</code> for powers. The comparison <code>==</code> converts types before comparing, which gives surprising results, while <code>===</code> compares value and type. Prefer <code>===</code> and <code>!==</code> always.</p>
<pre><code>var_dump(0 == "a");
var_dump("1" == "01");
var_dump(1 === "1");
echo 7 % 3, " ", 2 ** 3, " ", intdiv(7, 2), "\n";</code></pre>
<pre>bool(false)
bool(true)
bool(false)
1 8 3</pre>
<p>In PHP 8, <code>0 == "a"</code> is false (older versions said true, a famous source of bugs). But <code>"1" == "01"</code> is still true because both look like numbers, which is another reason to use <code>===</code>.</p>

<h3>Floats are approximate</h3>
<p>Computers store decimals in binary, so <code>0.1 + 0.2</code> is not exactly <code>0.3</code>. For money, store whole amounts in the smallest unit (shillings) as integers, or use a DECIMAL column in the database and format only when displaying.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create <code>types.php</code> in your project folder with a product name, a price, a weight and an in-stock flag.</li>
<li>Print them in a sentence with <code>echo</code> and use <code>number_format</code> for the price.</li>
<li>Use <code>var_dump</code> on each variable and read the types.</li>
<li>Try the comparisons <code>"5" == 5</code> and <code>"5" === 5</code> and explain the results.</li>
<li>Run the file in the browser and then with <code>php.exe types.php</code> in the terminal.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Variables begin with <code>$</code>; use <code>declare(strict_types=1);</code> at the top of your files.</li>
<li>Double quotes interpolate variables, single quotes do not.</li>
<li>Use <code>===</code> instead of <code>==</code> to avoid surprising conversions.</li>
<li>Do not store money in floats; use integers or DECIMAL.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'Control flow, arrays and functions',
                    'minutes' => 18,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to make decisions, repeat actions, store lists of data and package code into reusable functions. With these four tools you can already write real programs, such as a price calculator or a report generator.</p>

<h3>Decisions</h3>
<pre><code>&lt;?php
$marks = 68;

if ($marks &gt;= 80) {
    $grade = "A";
} elseif ($marks &gt;= 60) {
    $grade = "B";
} else {
    $grade = "C";
}
echo $grade, "\n";

$label = match (true) {
    $marks &gt;= 80 =&gt; "Distinction",
    $marks &gt;= 60 =&gt; "Credit",
    default      =&gt; "Pass",
};
echo $label, "\n";</code></pre>
<pre>B
Credit</pre>
<p>The <code>match</code> expression (PHP 8) returns a value, compares strictly and throws an error if nothing matches and there is no <code>default</code>, which is safer than the older <code>switch</code>.</p>

<h3>Arrays</h3>
<p>An array holds many values. A <strong>list</strong> uses numbered positions starting at 0. An <strong>associative array</strong> uses names (keys), like a row of a table.</p>
<pre><code>$towns = ["Kampala", "Gulu", "Mbale"];
$towns[] = "Jinja";                 // add to the end
echo count($towns), " ", $towns[1], "\n";

$student = ["name" =&gt; "Amina", "age" =&gt; 19, "town" =&gt; "Gulu"];
echo $student["name"], " is ", $student["age"], "\n";

foreach ($towns as $index =&gt; $town) {
    echo $index, ": ", $town, "\n";
}</code></pre>
<pre>4 Gulu
Amina is 19
0: Kampala
1: Gulu
2: Mbale
3: Jinja</pre>
<p>Reading a key that does not exist gives a warning in PHP 8. Use the null coalescing operator <code>??</code> to give a default: <code>$student["phone"] ?? "not given"</code>. To check first, use <code>isset($student["phone"])</code> or <code>array_key_exists</code>.</p>

<h3>Useful array functions</h3>
<pre><code>$prices = [4500, 1200, 3000];

echo array_sum($prices), "\n";
echo max($prices), "\n";
sort($prices);
echo implode(", ", $prices), "\n";

$doubled = array_map(fn($p) =&gt; $p * 2, $prices);
echo implode(", ", $doubled), "\n";
$cheap = array_filter($prices, fn($p) =&gt; $p &lt; 3500);
echo implode(", ", $cheap), "\n";</code></pre>
<pre>8700
4500
1200, 3000, 4500
2400, 6000, 9000
1200, 3000</pre>
<p><code>fn($p) => $p * 2</code> is a short anonymous function (an arrow function) that is handy with <code>array_map</code> and <code>array_filter</code>.</p>

<h3>Loops</h3>
<p><code>for</code> repeats a set number of times, <code>while</code> repeats while a condition is true, and <code>foreach</code> walks through an array. Use <code>break</code> to stop early and <code>continue</code> to skip to the next round.</p>

<h3>Functions</h3>
<p>A <strong>function</strong> is a named block of code you can call again and again. Give parameters and the return value types, which makes mistakes visible at once.</p>
<pre><code>declare(strict_types=1);

function vat(int $amount, float $rate = 0.18): int
{
    return (int) round($amount * $rate);
}

echo vat(10000), "\n";
echo vat(10000, 0.1), "\n";</code></pre>
<pre>1800
1000</pre>
<p>Here the rate has a default value of 0.18, so it can be left out. With strict types on, passing the text <code>"abc"</code> as the amount throws a <code>TypeError</code> instead of silently converting. Functions can also return arrays, and a type such as <code>?string</code> allows a string or null.</p>

<h3>Variable scope</h3>
<p>A variable created inside a function exists only there, and a function cannot see outer variables unless you pass them in as parameters. This is a feature: it keeps functions independent and easy to test.</p>

<h3>A brief look at classes</h3>
<p>You will use classes for the model and controller code later. A class bundles data and functions (called properties and methods). For now just recognise the shape: <code>class Product { public function __construct(public string $name, public int $price) {} }</code> creates a type, and <code>$p = new Product("Soap", 2500);</code> makes one object whose parts you read as <code>$p-&gt;name</code>.</p>

<h3>Try it yourself</h3>
<ol>
<li>Write a function <code>grade(int $marks): string</code> that returns A, B or C using the thresholds above.</li>
<li>Make an array of five marks and use <code>foreach</code> to print each mark with its grade.</li>
<li>Use <code>array_sum</code> and <code>count</code> to print the average mark.</li>
<li>Use <code>array_filter</code> to list only marks of 60 or more.</li>
</ol>

<h3>Key points</h3>
<ul>
<li><code>if</code>, <code>elseif</code> and <code>match</code> make decisions; <code>foreach</code> walks arrays.</li>
<li>Associative arrays use keys, and <code>??</code> gives a default for missing keys.</li>
<li>Type your function parameters and return values.</li>
<li>Variables inside functions are separate from outside ones.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'Handling HTML forms: GET, POST and safe output',
                    'minutes' => 18,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to receive data that a visitor types into a form, check it and display it without creating a security hole. Forms are how users talk to your application, and everything that arrives from a form must be treated as untrusted.</p>

<h3>GET and POST</h3>
<table>
<thead><tr><th></th><th>GET</th><th>POST</th></tr></thead>
<tbody>
<tr><td>Where the data goes</td><td>In the address (<code>?q=maize</code>)</td><td>In the body of the request</td></tr>
<tr><td>PHP variable</td><td><code>$_GET</code></td><td><code>$_POST</code></td></tr>
<tr><td>Use for</td><td>Searching, filtering, reading</td><td>Creating, changing, deleting, logging in</td></tr>
<tr><td>Can be bookmarked</td><td>Yes</td><td>No</td></tr>
</tbody>
</table>
<p>Never put passwords or private information in a GET request, because the address is saved in browser history and server logs. Use GET only for requests that read data and change nothing.</p>

<h3>A simple form</h3>
<p>Create <code>contact.php</code>. The same file shows the form and handles it.</p>
<pre><code>&lt;?php
declare(strict_types=1);

$errors = [];
$name = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name    = trim($_POST["name"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if ($name === "") {
        $errors[] = "Please enter your name.";
    }
    if (mb_strlen($message) &lt; 10) {
        $errors[] = "The message must have at least 10 characters.";
    }
    if ($errors === []) {
        $sent = true;      // later: save to the database or send an email
    }
}
?&gt;
&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;&lt;meta charset="utf-8"&gt;&lt;title&gt;Contact us&lt;/title&gt;&lt;/head&gt;
&lt;body&gt;
&lt;?php if (!empty($sent)): ?&gt;
  &lt;p&gt;Thank you, &lt;?= htmlspecialchars($name, ENT_QUOTES, "UTF-8") ?&gt;. We received your message.&lt;/p&gt;
&lt;?php else: ?&gt;
  &lt;?php foreach ($errors as $e): ?&gt;
    &lt;p&gt;&lt;?= htmlspecialchars($e, ENT_QUOTES, "UTF-8") ?&gt;&lt;/p&gt;
  &lt;?php endforeach; ?&gt;
  &lt;form method="post" action=""&gt;
    &lt;label&gt;Name &lt;input name="name" value="&lt;?= htmlspecialchars($name, ENT_QUOTES, "UTF-8") ?&gt;"&gt;&lt;/label&gt;
    &lt;label&gt;Message &lt;textarea name="message"&gt;&lt;?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?&gt;&lt;/textarea&gt;&lt;/label&gt;
    &lt;button type="submit"&gt;Send&lt;/button&gt;
  &lt;/form&gt;
&lt;?php endif; ?&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>

<h3>What is happening</h3>
<ol>
<li>The first time the page loads, the method is GET, so only the form is shown.</li>
<li>When the user presses Send, the browser makes a POST request to the same page and PHP finds the values in <code>$_POST</code>, using the field <code>name</code> attributes as keys.</li>
<li><code>trim</code> removes accidental spaces and <code>?? ""</code> avoids a warning if a field is missing. A missing field is possible because users and attackers can send any request, not only the one your form creates.</li>
<li>Errors are collected in an array and shown to the user, and the form keeps what they typed.</li>
</ol>

<h3>Escaping output with htmlspecialchars</h3>
<p>Anything the user typed must be passed through <code>htmlspecialchars</code> before it is printed in a page. It converts the characters that have special meaning in HTML into harmless codes:</p>
<pre><code>echo htmlspecialchars('&lt;b&gt;Hi&lt;/b&gt; &amp; "bye"', ENT_QUOTES, "UTF-8");</code></pre>
<pre>&amp;lt;b&amp;gt;Hi&amp;lt;/b&amp;gt; &amp;amp; &amp;quot;bye&amp;quot;</pre>
<p>Without it, a visitor could type a <code>&lt;script&gt;</code> tag as their name, and your page would run it in other people's browsers. That attack is called cross-site scripting (XSS) and we study it in module 3. Make escaping a habit: define a small helper <code>function e(string $s): string</code> that calls <code>htmlspecialchars</code> and use it everywhere.</p>

<h3>Validation versus sanitising</h3>
<p><strong>Validate</strong> means checking that a value is acceptable and refusing it when it is not, for example an email that is not an email. PHP can help: <code>filter_var($email, FILTER_VALIDATE_EMAIL)</code> returns the email or false. <strong>Escape</strong> means making a value safe for the place where it is printed. These are different jobs, and you need both.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create <code>contact.php</code> as above and test an empty submission and a short message.</li>
<li>Add an email field and validate it with <code>filter_var</code> and <code>FILTER_VALIDATE_EMAIL</code>.</li>
<li>Type <code>&lt;b&gt;bold&lt;/b&gt;</code> as the name and check that the thank-you page shows the text instead of making it bold.</li>
<li>Temporarily remove <code>htmlspecialchars</code> from one place to see the difference, then restore it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use GET to read data and POST to change data; read values from <code>$_GET</code> and <code>$_POST</code>.</li>
<li>Treat every incoming value as untrusted, use <code>trim</code>, <code>??</code> and validation.</li>
<li>Escape all output with <code>htmlspecialchars</code> using ENT_QUOTES and UTF-8.</li>
<li>Show helpful errors and keep the user's input in the form.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does Apache do with a file called index.php?
- Sends the PHP code as text to the browser
* Passes it to the PHP interpreter and sends the resulting HTML to the browser
- Compiles it into an application for the phone
- Stores it in MySQL
= PHP code runs on the server. Only the output reaches the browser.

Q: You double-click a PHP file and see the raw PHP code in the browser with an address starting with file://. What is wrong?
- PHP is not installed in the browser
* The file was opened directly from disk instead of being requested through the web server at localhost
- The file has too many lines
- MySQL is stopped
= PHP files must be served by Apache, for example at http://localhost/hello/.

Q: What is the output of var_dump(1 === "1")?
- bool(true)
* bool(false)
- int(1)
- NULL
= The strict comparison checks type as well as value, and an integer is not a string.

Q: Which statement about single and double quoted strings is correct?
* Double quotes replace variables inside the string with their values; single quotes do not
- Single quotes replace variables; double quotes do not
- They behave exactly the same
- Only double quotes can hold text
= Interpolation happens only in double-quoted strings (and heredocs).

Q: Why should you not store prices as float values in your program?
- PHP does not support floats
* Floats are approximate (0.1 + 0.2 is not exactly 0.3); use integers in the smallest unit or DECIMAL in the database
- Floats are too big
- Floats cannot be echoed
= Use integers for shillings or DECIMAL columns to avoid rounding surprises.

Q: Given $s = ["name" => "Amina"], which expression safely returns "not given" for a missing phone key?
- $s["phone"]
- $s->phone
* $s["phone"] ?? "not given"
- phone($s)
= The null coalescing operator avoids a warning and supplies a default.

Q: Which request method should a form that creates or changes data use?
- GET, so users can bookmark it
* POST
- Either, there is no difference
- HEAD
= GET should only read data. Changes should be made with POST so they are not triggered by links, prefetching or history.

Q: Which function should you use before printing user-supplied text into an HTML page?
- trim
- strtoupper
* htmlspecialchars
- str_replace
= htmlspecialchars converts characters such as less than and ampersand into safe codes and prevents XSS.
QUIZ,
        ],
        [
            'title' => 'Module 2: Databases with PDO',
            'summary' => 'Create MySQL tables, connect from PHP with PDO and build complete create, read, update and delete screens using prepared statements.',
            'lessons' => [
                [
                    'title' => 'MySQL, phpMyAdmin and designing a table',
                    'minutes' => 17,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to create a database and a properly designed table in phpMyAdmin or with SQL, and to create a safe database user for your application. A good table design prevents whole classes of bugs before you write any PHP.</p>

<h3>Create the database</h3>
<ol>
<li>Start MySQL in the XAMPP control panel and open <code>http://localhost/phpmyadmin</code>.</li>
<li>Select <strong>New</strong>, type a database name such as <code>noticeboard</code> and choose the collation <code>utf8mb4_unicode_ci</code>. Click <strong>Create</strong>.</li>
</ol>
<p><strong>utf8mb4</strong> is the character set that stores every letter and emoji, including names in Luganda, Swahili, Arabic or Chinese. The older <code>utf8</code> in MySQL stores only part of Unicode, so always choose utf8mb4.</p>

<h3>Create a table with SQL</h3>
<p>Open the <strong>SQL</strong> tab and run the statements below. You met this language in the SQL course; here we apply it to a small notice board where members post notices.</p>
<pre><code>CREATE TABLE users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notices (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    title      VARCHAR(150) NOT NULL,
    body       TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notice_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;</code></pre>

<h3>Why these choices?</h3>
<ul>
<li><strong>PRIMARY KEY with AUTO_INCREMENT</strong> gives every row a unique number that MySQL assigns.</li>
<li><strong>NOT NULL</strong> forbids empty values where a value is required, so the database enforces your rules even if your PHP forgets.</li>
<li><strong>UNIQUE on email</strong> makes it impossible to register the same address twice, even if two people click at the same moment. This is a rule the database must enforce, since PHP checks alone can be beaten by timing.</li>
<li><strong>password_hash VARCHAR(255)</strong> leaves room for the long hashes that PHP produces; we never store passwords themselves.</li>
<li><strong>FOREIGN KEY</strong> links each notice to a real user; with <code>ON DELETE CASCADE</code>, deleting a user also deletes their notices. InnoDB is the engine that supports foreign keys and transactions.</li>
<li>A <strong>TIMESTAMP DEFAULT CURRENT_TIMESTAMP</strong> records when a row was created without extra code.</li>
</ul>

<h3>Choosing data types</h3>
<table>
<thead><tr><th>Data</th><th>Column type</th></tr></thead>
<tbody>
<tr><td>Short text such as names, titles</td><td><code>VARCHAR(n)</code></td></tr>
<tr><td>Long text</td><td><code>TEXT</code></td></tr>
<tr><td>Whole numbers, ids</td><td><code>INT</code> (UNSIGNED when never negative)</td></tr>
<tr><td>Money</td><td><code>DECIMAL(12,2)</code> or whole shillings as <code>BIGINT</code></td></tr>
<tr><td>Dates and times</td><td><code>DATE</code>, <code>DATETIME</code>, <code>TIMESTAMP</code></td></tr>
<tr><td>Yes or no</td><td><code>TINYINT(1)</code></td></tr>
</tbody>
</table>

<h3>A dedicated database user</h3>
<p>Your application should not connect as <code>root</code>, the all-powerful administrator. If the application is attacked, the attacker would own the whole server. Create a user that has rights only to this one database:</p>
<pre><code>CREATE USER 'notice_app'@'localhost' IDENTIFIED BY 'choose-a-long-random-password';
GRANT SELECT, INSERT, UPDATE, DELETE ON noticeboard.* TO 'notice_app'@'localhost';</code></pre>
<p>Use your own long random password, not this example. This user can read and change rows but cannot drop tables or touch other databases. In phpMyAdmin you can do the same under <strong>User accounts</strong>. For local learning you may continue with root, but get used to the better habit.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create the <code>noticeboard</code> database with the utf8mb4 collation.</li>
<li>Run the two CREATE TABLE statements and open the <strong>Structure</strong> tab to check the columns.</li>
<li>Insert a test user with an INSERT statement and a test notice that points to its id.</li>
<li>Try to insert a second user with the same email and read the error.</li>
<li>Create the limited database user.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use utf8mb4 and the InnoDB engine.</li>
<li>Let the database enforce rules with NOT NULL, UNIQUE and foreign keys.</li>
<li>Store password hashes, never passwords, in a column of VARCHAR(255).</li>
<li>Connect from PHP as a limited user, not as root.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'Connecting with PDO and prepared statements',
                    'minutes' => 20,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to connect to MySQL from PHP with PDO and run queries safely with prepared statements. This is the most important security habit in the whole course: it is how you stop SQL injection, one of the most damaging attacks on web applications.</p>

<h3>What is PDO?</h3>
<p><strong>PDO</strong> (PHP Data Objects) is PHP's standard layer for talking to databases. It gives one consistent set of functions, supports prepared statements and reports problems through exceptions. Older code used the <code>mysql_</code> functions (removed from PHP) or <code>mysqli_</code>; for new projects PDO is the modern choice.</p>

<h3>Connecting</h3>
<p>Put the connection in its own file, <code>db.php</code>, so every page can reuse it.</p>
<pre><code>&lt;?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=127.0.0.1;dbname=noticeboard;charset=utf8mb4";
        $pdo = new PDO($dsn, "notice_app", "choose-a-long-random-password", [
            PDO::ATTR_ERRMODE            =&gt; PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE =&gt; PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   =&gt; false,
        ]);
    }
    return $pdo;
}</code></pre>
<p>The <strong>DSN</strong> says which driver, host, database and character set to use. The options turn on exceptions (so errors are not silent), return each row as an associative array and use real prepared statements. The <code>static</code> variable means that the connection is made once per request, however many times you call <code>db()</code>.</p>

<h3>The danger: SQL injection</h3>
<p>Imagine you build a query by gluing the user's text into it:</p>
<pre><code>// NEVER do this
$email = $_POST["email"];
$sql = "SELECT * FROM users WHERE email = '$email'";</code></pre>
<p>If the visitor types <code>' OR '1'='1</code> as the email, the query becomes <code>SELECT * FROM users WHERE email = '' OR '1'='1'</code>, which is true for every row. In worse cases attackers add commands that read other tables or delete data. The user's text was allowed to become part of the program.</p>

<h3>The cure: prepared statements</h3>
<p>With a prepared statement you send the SQL <em>shape</em> first, with placeholders, and send the values separately. The database never treats the values as SQL, whatever they contain.</p>
<pre><code>$stmt = db()-&gt;prepare("SELECT id, name FROM users WHERE email = :email");
$stmt-&gt;execute(["email" =&gt; $_POST["email"] ?? ""]);
$user = $stmt-&gt;fetch();       // one row as an array, or false

if ($user === false) {
    echo "No such user";
} else {
    echo "Found: ", htmlspecialchars($user["name"], ENT_QUOTES, "UTF-8");
}</code></pre>
<p>Here <code>:email</code> is a <strong>named placeholder</strong>. You may also use question marks and pass a list: <code>prepare("... WHERE email = ?")</code> then <code>execute([$email])</code>. Use placeholders for every value that did not come from your own code: from forms, the address bar, cookies, files or other tables.</p>

<h3>What placeholders cannot do</h3>
<p>Placeholders replace <em>values</em> only, not table names, column names or keywords such as <code>ASC</code>. If you need a dynamic sort column, check it against a fixed allow-list in PHP:</p>
<pre><code>$allowed = ["title", "created_at"];
$col = in_array($_GET["sort"] ?? "", $allowed, true) ? $_GET["sort"] : "created_at";
$stmt = db()-&gt;query("SELECT id, title FROM notices ORDER BY $col DESC");</code></pre>
<p>This is safe because <code>$col</code> can only be one of the strings that you wrote yourself.</p>

<h3>Fetching results</h3>
<table>
<thead><tr><th>Method</th><th>Returns</th></tr></thead>
<tbody>
<tr><td><code>fetch()</code></td><td>The next row, or false when there are no more</td></tr>
<tr><td><code>fetchAll()</code></td><td>All remaining rows as an array of rows</td></tr>
<tr><td><code>fetchColumn()</code></td><td>The first column of the next row, for example a count</td></tr>
<tr><td><code>rowCount()</code></td><td>Rows affected by an INSERT, UPDATE or DELETE</td></tr>
<tr><td><code>lastInsertId()</code> (on the PDO object)</td><td>The AUTO_INCREMENT id of the row just inserted</td></tr>
</tbody>
</table>
<pre><code>$count = db()-&gt;query("SELECT COUNT(*) FROM notices")-&gt;fetchColumn();
echo "There are $count notices";</code></pre>

<h3>Handling failures</h3>
<p>Because we turned on exceptions, a failed query throws a <code>PDOException</code>. In development you want to see it. In production you must never show it to visitors, because the message can reveal table names and even passwords. Lesson 16 shows how to log errors and show a friendly page instead.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create <code>db.php</code> with your own database user and password.</li>
<li>Write <code>test.php</code> that requires <code>db.php</code> and prints the count of rows in <code>users</code>.</li>
<li>Insert a user with a prepared statement and print <code>lastInsertId()</code>.</li>
<li>Fetch the user by email with a placeholder and print the name safely.</li>
<li>Deliberately misspell a column name and read the PDOException message, then fix it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>PDO with exceptions on, real prepared statements and utf8mb4 is the standard set-up.</li>
<li>Never place user input inside SQL text; send values through placeholders.</li>
<li>Placeholders cannot replace column names or keywords, so use an allow-list for those.</li>
<li><code>fetch</code>, <code>fetchAll</code>, <code>fetchColumn</code> and <code>lastInsertId</code> read results and new ids.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'CRUD, part 1: create and read',
                    'minutes' => 20,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to add records through a form and list them on a page, with validation and with output that is safe to display. These two operations, Create and Read, are the heart of nearly every business application, from a student register to a stock list.</p>

<h3>Plan the pages</h3>
<table>
<thead><tr><th>File</th><th>Job</th></tr></thead>
<tbody>
<tr><td><code>db.php</code></td><td>The <code>db()</code> function and a helper <code>e()</code> for escaping</td></tr>
<tr><td><code>index.php</code></td><td>List all notices</td></tr>
<tr><td><code>create.php</code></td><td>Show the form and save a new notice</td></tr>
</tbody>
</table>
<p>For this lesson the notice has a title and a body, and we use user id 1 as the author; module 3 replaces this with the logged-in user. Add to <code>db.php</code> the escaping helper:</p>
<pre><code>function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}</code></pre>

<h3>Create: saving a record</h3>
<pre><code>&lt;?php
declare(strict_types=1);
require __DIR__ . "/db.php";

$errors = [];
$title = $body = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $body  = trim($_POST["body"] ?? "");

    if ($title === "" || mb_strlen($title) &gt; 150) {
        $errors[] = "The title is required and may have at most 150 characters.";
    }
    if ($body === "") {
        $errors[] = "The notice text is required.";
    }

    if ($errors === []) {
        $stmt = db()-&gt;prepare(
            "INSERT INTO notices (user_id, title, body) VALUES (:user, :title, :body)"
        );
        $stmt-&gt;execute(["user" =&gt; 1, "title" =&gt; $title, "body" =&gt; $body]);
        header("Location: index.php");
        exit;
    }
}
?&gt;
&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;&lt;meta charset="utf-8"&gt;&lt;title&gt;New notice&lt;/title&gt;&lt;/head&gt;
&lt;body&gt;
&lt;h1&gt;New notice&lt;/h1&gt;
&lt;?php foreach ($errors as $err): ?&gt;&lt;p&gt;&lt;?= e($err) ?&gt;&lt;/p&gt;&lt;?php endforeach; ?&gt;
&lt;form method="post"&gt;
  &lt;label&gt;Title &lt;input name="title" maxlength="150" value="&lt;?= e($title) ?&gt;"&gt;&lt;/label&gt;
  &lt;label&gt;Text &lt;textarea name="body"&gt;&lt;?= e($body) ?&gt;&lt;/textarea&gt;&lt;/label&gt;
  &lt;button type="submit"&gt;Save&lt;/button&gt;
&lt;/form&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
<p>Three points deserve attention. First, validation happens on the server even though the HTML has <code>maxlength</code>, because attackers do not use your form. Second, the SQL uses placeholders. Third, after saving, the script redirects with <code>header("Location: ...")</code> and calls <code>exit</code>. This is the <strong>post-redirect-get</strong> pattern: if the page showed "saved" directly after the POST, a refresh would send the form again and create a duplicate. Redirecting turns the final page into a harmless GET.</p>

<h3>Read: listing records</h3>
<pre><code>&lt;?php
declare(strict_types=1);
require __DIR__ . "/db.php";

$notices = db()-&gt;query(
    "SELECT n.id, n.title, n.created_at, u.name
       FROM notices n
       JOIN users u ON u.id = n.user_id
      ORDER BY n.created_at DESC
      LIMIT 20"
)-&gt;fetchAll();
?&gt;
&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;&lt;meta charset="utf-8"&gt;&lt;title&gt;Notice board&lt;/title&gt;&lt;/head&gt;
&lt;body&gt;
&lt;h1&gt;Notice board&lt;/h1&gt;
&lt;p&gt;&lt;a href="create.php"&gt;Add a notice&lt;/a&gt;&lt;/p&gt;
&lt;?php if ($notices === []): ?&gt;
  &lt;p&gt;No notices yet.&lt;/p&gt;
&lt;?php endif; ?&gt;
&lt;?php foreach ($notices as $n): ?&gt;
  &lt;article&gt;
    &lt;h2&gt;&lt;a href="view.php?id=&lt;?= (int) $n["id"] ?&gt;"&gt;&lt;?= e($n["title"]) ?&gt;&lt;/a&gt;&lt;/h2&gt;
    &lt;p&gt;By &lt;?= e($n["name"]) ?&gt; on &lt;?= e($n["created_at"]) ?&gt;&lt;/p&gt;
  &lt;/article&gt;
&lt;?php endforeach; ?&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
<p>No user input enters this query, so <code>query()</code> is fine here. The <code>JOIN</code> fetches the author's name from the users table in one trip. Every value printed goes through <code>e()</code>, and the id in the link is cast with <code>(int)</code>.</p>

<h3>Reading one record</h3>
<p>The page <code>view.php?id=3</code> takes the id from the address, which is user input:</p>
<pre><code>$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if ($id === false || $id === null) {
    http_response_code(400);
    exit("Bad request");
}
$stmt = db()-&gt;prepare("SELECT title, body FROM notices WHERE id = :id");
$stmt-&gt;execute(["id" =&gt; $id]);
$notice = $stmt-&gt;fetch();
if ($notice === false) {
    http_response_code(404);
    exit("Notice not found");
}</code></pre>
<p>The code validates that the id is an integer, uses a placeholder, and answers with proper status codes: 400 for a malformed request and 404 when nothing is found.</p>

<h3>Common mistakes</h3>
<ul>
<li>Forgetting <code>exit</code> after <code>header("Location: ...")</code>. The script keeps running, and code after the redirect still executes.</li>
<li>Printing database values without <code>e()</code>. Data in your own database can contain attacker input too.</li>
<li>Using <code>SELECT *</code> everywhere. Select only the columns you need.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Create <code>create.php</code> and <code>index.php</code> and add three notices.</li>
<li>Submit the form with an empty title and check that the error appears and your typing is kept.</li>
<li>Create <code>view.php</code> and open <code>view.php?id=9999</code> to see the 404 message.</li>
<li>Add a notice whose title is <code>&lt;i&gt;test&lt;/i&gt;</code> and confirm that it shows as text on the list page.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Validate on the server, use placeholders for INSERT and escape every printed value.</li>
<li>Post-redirect-get stops duplicate submissions on refresh.</li>
<li>Validate ids from the address and return 400 or 404 when appropriate.</li>
<li>Always call <code>exit</code> after a redirect.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'CRUD, part 2: update and delete',
                    'minutes' => 19,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to edit and delete records without exposing your application to accidental or malicious deletions. Update and Delete change data, so they need more care than Create and Read: who may do this, and was the request really made by the user?</p>

<h3>Update: edit.php</h3>
<p>The edit page works in two phases. On GET it loads the current values into the form. On POST it validates and saves them. The id travels in the address and the new values in the body.</p>
<pre><code>&lt;?php
declare(strict_types=1);
require __DIR__ . "/db.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    exit("Bad request");
}

$stmt = db()-&gt;prepare("SELECT id, title, body FROM notices WHERE id = :id");
$stmt-&gt;execute(["id" =&gt; $id]);
$notice = $stmt-&gt;fetch();
if ($notice === false) {
    http_response_code(404);
    exit("Notice not found");
}

$errors = [];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $body  = trim($_POST["body"] ?? "");
    if ($title === "" || mb_strlen($title) &gt; 150) {
        $errors[] = "A title of up to 150 characters is required.";
    }
    if ($body === "") {
        $errors[] = "The text is required.";
    }
    $notice["title"] = $title;     // show what the user typed again
    $notice["body"]  = $body;

    if ($errors === []) {
        $upd = db()-&gt;prepare(
            "UPDATE notices SET title = :title, body = :body WHERE id = :id"
        );
        $upd-&gt;execute(["title" =&gt; $title, "body" =&gt; $body, "id" =&gt; $id]);
        header("Location: view.php?id=" . $id);
        exit;
    }
}
?&gt;
&lt;form method="post"&gt;
  &lt;input name="title" value="&lt;?= e($notice["title"]) ?&gt;"&gt;
  &lt;textarea name="body"&gt;&lt;?= e($notice["body"]) ?&gt;&lt;/textarea&gt;
  &lt;button type="submit"&gt;Save changes&lt;/button&gt;
&lt;/form&gt;</code></pre>
<p>Never write an UPDATE without a <code>WHERE</code> clause: it would change every row in the table. Use <code>$stmt-&gt;rowCount()</code> to see how many rows were affected. Note that MySQL reports 0 affected rows if you save identical values, which is not an error.</p>

<h3>Delete: use POST, never a link</h3>
<p>A delete link such as <code>delete.php?id=5</code> is dangerous. Search engines, browser prefetching and anti-virus tools follow links, and an attacker can hide such a link in an image on another website so that your own logged-in user deletes data without knowing. Rule: <strong>anything that changes data must be a POST request.</strong> Use a small form with a button:</p>
<pre><code>&lt;form method="post" action="delete.php"&gt;
  &lt;input type="hidden" name="id" value="&lt;?= (int) $n["id"] ?&gt;"&gt;
  &lt;button type="submit"&gt;Delete&lt;/button&gt;
&lt;/form&gt;</code></pre>
<p>You may add a JavaScript <code>confirm</code> dialog (from a separate script file) as a courtesy to the user, but it does not protect you, because a request can be sent without your page. The real protections are the POST rule here, a login check and the CSRF token that we add in module 3. And the handler:</p>
<pre><code>&lt;?php
declare(strict_types=1);
require __DIR__ . "/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed");
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
if ($id) {
    $stmt = db()-&gt;prepare("DELETE FROM notices WHERE id = :id");
    $stmt-&gt;execute(["id" =&gt; $id]);
}
header("Location: index.php");
exit;</code></pre>
<p>Status 405 means "method not allowed". After deletion we redirect back to the list.</p>

<h3>Soft deletion</h3>
<p>Sometimes users delete by mistake, or the law or your business requires a history. Then add a column such as <code>deleted_at TIMESTAMP NULL</code> and have the "delete" action set it to the current time. Every query that lists data then adds <code>WHERE deleted_at IS NULL</code>. The data can be restored, but remember that it still exists, so personal data may need a real purge later.</p>

<h3>Pagination and searching</h3>
<p>A list with thousands of rows should be split into pages with <code>LIMIT</code> and <code>OFFSET</code>. Convert the page number from the address to an integer, and calculate the offset in PHP. Placeholders work in LIMIT and OFFSET as long as you bind them as integers (and we turned emulated prepares off, so MySQL receives them as real numbers):</p>
<pre><code>$page   = max(1, (int) ($_GET["page"] ?? 1));
$per    = 10;
$offset = ($page - 1) * $per;

$stmt = db()-&gt;prepare("SELECT id, title FROM notices ORDER BY id DESC LIMIT :n OFFSET :o");
$stmt-&gt;bindValue(":n", $per, PDO::PARAM_INT);
$stmt-&gt;bindValue(":o", $offset, PDO::PARAM_INT);
$stmt-&gt;execute();
$rows = $stmt-&gt;fetchAll();</code></pre>
<p>For a search box, bind the term inside a LIKE pattern: <code>execute(["q" =&gt; "%" . $term . "%"])</code> with <code>WHERE title LIKE :q</code>. The percent signs belong in the bound value, not in the SQL.</p>

<h3>Try it yourself</h3>
<ol>
<li>Write <code>edit.php</code> and test changing a title, then leave it empty and see the error.</li>
<li>Replace any delete link with a POST form and write <code>delete.php</code> with the 405 check.</li>
<li>Open <code>delete.php</code> directly in the address bar and confirm that it refuses.</li>
<li>Add pagination with 3 notices per page and test pages 1, 2 and a huge page number.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Updates need validation and a <code>WHERE</code> clause, and deletes need a POST request.</li>
<li>Never delete or change data through a link that a GET request can trigger.</li>
<li>Soft deletion keeps a restorable record using a <code>deleted_at</code> column.</li>
<li>Bind LIMIT and OFFSET as integers, and put the LIKE wildcards in the bound value.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Why should the application connect to MySQL as a limited user instead of root?
- Root cannot connect from PHP
* If the application is attacked, the damage is limited to what the limited user may do
- Limited users run queries faster
- Root cannot create tables
= Least privilege reduces the harm from a breach or a bug.

Q: Which column design prevents two accounts with the same email address even when two people register at the same moment?
- A PHP check using SELECT before INSERT
- A hidden form field
* A UNIQUE constraint on the email column
- A very long VARCHAR
= Only a constraint in the database is immune to timing races between two requests.

Q: What does a prepared statement do that protects against SQL injection?
- It encrypts the database
* It sends the SQL shape and the values separately so that values are never treated as SQL
- It hides error messages
- It removes quotation marks from the input
= The database receives the values as data only, whatever characters they contain.

Q: Which line is a SQL injection risk?
- $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
* $pdo->query("SELECT * FROM users WHERE email = '$email'");
- $stmt->execute(["id" => $id]);
- $pdo->prepare("DELETE FROM notices WHERE id = ?");
= Gluing user text into the SQL string lets the input change the query. The others use placeholders.

Q: You must let users sort a list by a column chosen in the address. How do you do it safely?
- Place the column name in a placeholder
- Escape it with htmlspecialchars
* Compare it with a fixed allow-list of column names in PHP and use only a name from the list
- Use it directly because column names cannot be dangerous
= Placeholders cannot stand in for identifiers, so an allow-list is the safe method.

Q: Why is the pattern post-redirect-get used after saving a form?
- It makes the database faster
* A browser refresh would not resubmit the form and create a duplicate record
- It encrypts the form data
- It avoids the need for validation
= Redirecting makes the page shown after saving a simple GET request.

Q: Why must a delete action be a POST form and not a simple link?
- Links are slower
- Forms look better
* Links can be triggered by crawlers, prefetching or hidden images on other sites, changing data without the user intending it
- POST requests do not need a login check
= Requests that change data must not be possible through a GET request.

Q: What does an UPDATE statement without a WHERE clause do?
- Nothing
* Changes every row in the table
- Changes the first row only
- Raises a syntax error
= Without WHERE the change applies to all rows, so always include it.
QUIZ,
        ],
        [
            'title' => 'Module 3: Users, security and uploads',
            'summary' => 'Register and log in users with sessions and password hashing, then defend against XSS, CSRF and SQL injection and handle file uploads safely.',
            'lessons' => [
                [
                    'title' => 'Sessions and secure registration and login',
                    'minutes' => 22,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to register users, log them in, keep them logged in across pages and protect pages from visitors who are not logged in. Almost every real application has accounts, and getting passwords wrong is one of the most harmful mistakes a developer can make.</p>

<h3>Why sessions?</h3>
<p>HTTP has no memory: every request arrives as a stranger. A <strong>session</strong> gives the server memory about a visitor. PHP creates a random session id and sends it to the browser in a <strong>cookie</strong>. The data itself stays on the server in <code>$_SESSION</code>, and the browser only returns the id on each request.</p>
<pre><code>&lt;?php
session_start();                 // must run before any output

$_SESSION["visits"] = ($_SESSION["visits"] ?? 0) + 1;
echo "You have visited ", $_SESSION["visits"], " times in this session.";</code></pre>
<p>Refresh the page and the number goes up. Call <code>session_start()</code> at the top of every page that uses the session.</p>

<h3>Never store passwords, store hashes</h3>
<p>If your database leaks, plain passwords expose every user, and because many people reuse passwords, their other accounts too. Instead store a <strong>hash</strong>: a one-way scrambled form that cannot be turned back into the password. PHP gives you two functions, and you should use nothing else:</p>
<pre><code>$hash = password_hash("my secret phrase", PASSWORD_DEFAULT);

echo strlen($hash), "\n";
var_dump(password_verify("my secret phrase", $hash));
var_dump(password_verify("wrong guess", $hash));</code></pre>
<pre>60
bool(true)
bool(false)</pre>
<p>With the current default algorithm (bcrypt) the hash is 60 characters long, which is why the database column is VARCHAR(255): the default may change in future PHP versions and produce longer hashes. The hash includes a random <strong>salt</strong>, so two users with the same password get different hashes, and the cost setting makes guessing slow on purpose. Never use <code>md5</code> or <code>sha1</code> for passwords; they are far too fast.</p>

<h3>Registering a user</h3>
<pre><code>$name  = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$pass  = $_POST["password"] ?? "";

if ($name === "") { $errors[] = "Name is required."; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Enter a valid email."; }
if (mb_strlen($pass) &lt; 8) { $errors[] = "The password needs at least 8 characters."; }

if ($errors === []) {
    try {
        $stmt = db()-&gt;prepare(
            "INSERT INTO users (name, email, password_hash) VALUES (:n, :e, :h)"
        );
        $stmt-&gt;execute([
            "n" =&gt; $name,
            "e" =&gt; mb_strtolower($email),
            "h" =&gt; password_hash($pass, PASSWORD_DEFAULT),
        ]);
        header("Location: login.php");
        exit;
    } catch (PDOException $ex) {
        if ($ex-&gt;getCode() === "23000") {          // duplicate entry
            $errors[] = "That email is already registered.";
        } else {
            throw $ex;
        }
    }
}</code></pre>
<p>Notes: do not trim or alter the password (spaces can be part of it), and encourage long passphrases rather than strange symbols. The UNIQUE constraint rejects duplicate emails and PHP turns that into a friendly message (code 23000 is the SQL standard code for a constraint violation).</p>

<h3>Logging in</h3>
<pre><code>session_start();

$email = mb_strtolower(trim($_POST["email"] ?? ""));
$pass  = $_POST["password"] ?? "";

$stmt = db()-&gt;prepare("SELECT id, name, password_hash FROM users WHERE email = :e");
$stmt-&gt;execute(["e" =&gt; $email]);
$user = $stmt-&gt;fetch();

if ($user !== false &amp;&amp; password_verify($pass, $user["password_hash"])) {
    session_regenerate_id(true);          // new id after login
    $_SESSION["user_id"] = (int) $user["id"];
    $_SESSION["user_name"] = $user["name"];
    header("Location: index.php");
    exit;
}
$errors[] = "Email or password is incorrect.";</code></pre>
<p>Three details matter. The message is the same whether the email or the password was wrong, so attackers cannot discover which emails are registered. <code>session_regenerate_id(true)</code> issues a fresh session id at login, which defeats <strong>session fixation</strong>, where an attacker plants a known id on a victim. And only the user's id goes into the session, not the password or hash. Consider also limiting repeated failed attempts, for example by recording failures and pausing logins for a few minutes.</p>

<h3>Protecting pages and logging out</h3>
<p>Put a guard function in a shared file and call it at the top of every private page:</p>
<pre><code>function require_login(): void
{
    if (empty($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }
}</code></pre>
<p>To log out, clear the session data and the cookie, then destroy the session. Logging out should be a POST request, for the same reason as deleting.</p>
<pre><code>$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), "", time() - 3600, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
}
session_destroy();</code></pre>

<h3>Cookie settings</h3>
<p>Before <code>session_start()</code>, set the cookie options: <code>session_set_cookie_params(["httponly" =&gt; true, "samesite" =&gt; "Lax", "secure" =&gt; true])</code>. <code>httponly</code> hides the cookie from JavaScript, <code>samesite</code> limits cross-site sending and <code>secure</code> sends it only over HTTPS (use it on your live site, and set it to false only for plain-HTTP local testing).</p>

<h3>Try it yourself</h3>
<ol>
<li>Create the visit counter page and refresh it several times.</li>
<li>Build <code>register.php</code> and register two users; look at the <code>users</code> table in phpMyAdmin and confirm that only hashes are stored.</li>
<li>Build <code>login.php</code> and a private page that calls <code>require_login()</code>.</li>
<li>Try opening the private page after logging out.</li>
<li>Try registering the same email twice and read your friendly error.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Sessions keep state on the server and only a random id travels in a cookie.</li>
<li>Store passwords with <code>password_hash</code> and check them with <code>password_verify</code>.</li>
<li>Regenerate the session id at login and give the same error for a wrong email or password.</li>
<li>Guard every private page and log out with a POST request.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'Validation and cross-site scripting (XSS)',
                    'minutes' => 19,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to explain XSS, show how it works and prevent it with correct escaping. XSS is among the most common web security flaws, and one visitor's careless comment can turn into an attack on all the others.</p>

<h3>What is XSS?</h3>
<p><strong>Cross-site scripting</strong> happens when an application prints text that a user supplied into a page without making it safe, so that the browser treats that text as code. Suppose your notice board prints notice titles directly. An attacker posts a notice whose title is a script tag. Every visitor who opens the list now runs the attacker's JavaScript with the powers of your site: it can read what is on the page, send forms as the user, show a fake login box or try to steal session information. This kind, where the bad text is stored in the database, is <strong>stored XSS</strong>. When the text comes from the address and is echoed straight back, it is <strong>reflected XSS</strong>.</p>

<h3>The defence: escape at output</h3>
<p>The primary defence is to <strong>escape output</strong>: convert characters that have special meaning in the place where the text is printed. In HTML that means <code>htmlspecialchars</code>, which you have used since lesson 4.</p>
<pre><code>$title = '&lt;script&gt;alert(1)&lt;/script&gt;';
echo htmlspecialchars($title, ENT_QUOTES, "UTF-8");</code></pre>
<pre>&amp;lt;script&amp;gt;alert(1)&amp;lt;/script&amp;gt;</pre>
<p>The browser now displays the harmless text <code>&lt;script&gt;alert(1)&lt;/script&gt;</code> instead of running it. The right escaping depends on the context:</p>
<table>
<thead><tr><th>Where the value goes</th><th>How to make it safe</th></tr></thead>
<tbody>
<tr><td>HTML text and attribute values</td><td><code>htmlspecialchars($v, ENT_QUOTES, "UTF-8")</code>, and always quote attributes</td></tr>
<tr><td>A URL query value</td><td><code>urlencode($v)</code> or <code>http_build_query</code></td></tr>
<tr><td>Inside JavaScript</td><td><code>json_encode($v, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</code></td></tr>
<tr><td>A URL that the user supplies as a link</td><td>Allow only <code>http</code> and <code>https</code> (check the start of the string), then escape for HTML</td></tr>
<tr><td>A SQL value</td><td>Prepared statement placeholders</td></tr>
</tbody>
</table>
<p>The last row to understand is the link. A user-provided website address that uses the <code>javascript</code> scheme (an address starting with the word javascript and a colon) can run code when someone clicks it, even if it is HTML-escaped. So when users can give you a URL, accept only addresses that start with <code>https://</code> or <code>http://</code>.</p>

<h3>Allowing some formatting</h3>
<p>If users must write formatted text, do not try to remove dangerous tags yourself with regular expressions; attackers know many tricks. Safer choices are to allow plain text and convert new lines to paragraphs after escaping with <code>nl2br(e($text))</code>, or to use a simple markup such as Markdown and render it with a library that is built to produce safe HTML. A site that accepts raw HTML from the public needs a maintained sanitising library and extra defences.</p>

<h3>A second layer: Content-Security-Policy</h3>
<p>A <strong>Content-Security-Policy</strong> header tells the browser what scripts it may run. A simple policy sent from PHP is:</p>
<pre><code>header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; frame-ancestors 'none'");</code></pre>
<p>It blocks inline scripts and scripts from other sites, which makes many XSS attacks harmless even if you forget to escape somewhere. It also means your pages must keep their JavaScript in separate files and not inside <code>onclick</code> attributes. Treat it as an additional layer, not a replacement for escaping. Add the header <code>X-Content-Type-Options: nosniff</code> too.</p>

<h3>Validation: accept only what is valid</h3>
<p>Validation says what a field may contain. Escaping makes output safe, validation keeps garbage and abuse out. Be strict and specific:</p>
<pre><code>function valid_phone(string $v): bool
{
    // Uganda mobile, e.g. 0772123456 or +256772123456
    return preg_match('/^(\+256|0)7\d{8}$/', $v) === 1;
}

var_dump(valid_phone("0772123456"));
var_dump(valid_phone("+256772123456"));
var_dump(valid_phone("12345"));</code></pre>
<pre>bool(true)
bool(true)
bool(false)</pre>
<ul>
<li>Check the length (with <code>mb_strlen</code>), the type and the range of numbers.</li>
<li>For choices from a list (a role, a category) check against an allow-list with <code>in_array($v, $allowed, true)</code>.</li>
<li>Use <code>filter_var</code> for emails, integers and URLs, and <code>DateTime::createFromFormat</code> for dates.</li>
<li>Validate on the server always. Browser checks (<code>required</code>, <code>type="email"</code>) are only convenience.</li>
<li>Reject bad values, do not silently "fix" them, and give the user a clear message.</li>
</ul>
<p>The phone check above is only an example for Ugandan numbers; other countries have other formats, so match the rule to your users.</p>

<h3>Try it yourself</h3>
<ol>
<li>Temporarily print a notice title without escaping and save a notice whose title is <code>&lt;b&gt;test&lt;/b&gt;</code>. Notice that it turns bold, which proves that HTML is accepted.</li>
<li>Restore escaping and confirm the text appears literally.</li>
<li>Write <code>valid_phone</code> and test it with three valid and three invalid numbers.</li>
<li>Add a role field to a form with the choices member and admin and enforce an allow-list.</li>
<li>Send the Content-Security-Policy header on one page and look in the browser console for blocked items.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>XSS happens when untrusted text is printed so that the browser runs it as code.</li>
<li>Escape every value for the place where it is printed; <code>htmlspecialchars</code> for HTML.</li>
<li>Accept only <code>http</code> and <code>https</code> in links supplied by users.</li>
<li>Validate strictly on the server; a Content-Security-Policy is an extra layer.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'CSRF protection and a recap of SQL injection defences',
                    'minutes' => 20,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to protect forms with CSRF tokens and to review your code for SQL injection. These two attacks work in opposite ways: SQL injection abuses your database, while CSRF abuses the trust your server has in a logged-in user's browser.</p>

<h3>What is CSRF?</h3>
<p><strong>Cross-site request forgery</strong> tricks a logged-in user's browser into sending a request to your site that the user never intended. Browsers attach cookies automatically, so if Amina is logged in to your notice board and visits a malicious page, that page can contain a hidden form that posts to <code>yoursite/delete.php</code>. Your server sees a valid session cookie and obeys. The attacker never sees the response; the damage is done by the request alone: deleting data, changing an email address or sending money.</p>

<h3>The defence: a secret token in every form</h3>
<p>The defence is a random <strong>CSRF token</strong> that is stored in the user's session and also placed in a hidden field of each form. The attacker's page cannot read it (browsers prevent that), so a forged request arrives without the right token and you refuse it.</p>
<pre><code>&lt;?php
function csrf_token(): string
{
    if (empty($_SESSION["csrf"])) {
        $_SESSION["csrf"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf"];
}

function csrf_field(): string
{
    return '&lt;input type="hidden" name="csrf" value="' . csrf_token() . '"&gt;';
}

function csrf_check(): void
{
    $sent = $_POST["csrf"] ?? "";
    if (!hash_equals($_SESSION["csrf"] ?? "", $sent)) {
        http_response_code(403);
        exit("Invalid request");
    }
}</code></pre>
<p><code>random_bytes</code> creates unpredictable data from the system's secure generator and <code>bin2hex</code> turns it into a 64-character text. <code>hash_equals</code> compares two strings in constant time, which avoids leaking information through timing. Never use <code>rand()</code> or <code>uniqid()</code> for security tokens. The token only contains hexadecimal characters, so it is safe to print without extra escaping.</p>
<p>Use it in every form that changes anything:</p>
<pre><code>&lt;form method="post" action="delete.php"&gt;
  &lt;?= csrf_field() ?&gt;
  &lt;input type="hidden" name="id" value="&lt;?= (int) $n["id"] ?&gt;"&gt;
  &lt;button type="submit"&gt;Delete&lt;/button&gt;
&lt;/form&gt;</code></pre>
<p>And in the handler (after <code>session_start()</code>):</p>
<pre><code>if ($_SERVER["REQUEST_METHOD"] !== "POST") { http_response_code(405); exit; }
csrf_check();
// ... now it is safe to act</code></pre>
<p>Remember the login and registration forms too (a "login CSRF" can log a victim into the attacker's account). For JavaScript requests, send the token in a header such as <code>X-CSRF-Token</code> and check it the same way.</p>

<h3>More layers against CSRF</h3>
<ul>
<li>Use <code>samesite=Lax</code> (or Strict) on the session cookie: modern browsers then do not send it on most cross-site POST requests.</li>
<li>Change data only with POST (or PUT and DELETE for APIs), never GET.</li>
<li>For very sensitive actions such as changing a password, ask for the current password again.</li>
</ul>

<h3>SQL injection: a review checklist</h3>
<p>Search your project for every <code>query(</code>, <code>exec(</code> and <code>prepare(</code> and ask the questions below.</p>
<ol>
<li>Does any SQL string contain a variable joined with <code>.</code> or placed in double quotes? If it holds data from outside, rewrite it with a placeholder.</li>
<li>Are table or column names chosen by the user? Replace them with an allow-list.</li>
<li>Do you build <code>IN (...)</code> lists? Create one question mark per value:</li>
</ol>
<pre><code>$ids = [3, 5, 8];
$marks = implode(",", array_fill(0, count($ids), "?"));
$stmt = db()-&gt;prepare("SELECT id, title FROM notices WHERE id IN ($marks)");
$stmt-&gt;execute($ids);</code></pre>
<p>Here the only text inserted into the SQL is the string <code>?,?,?</code>, which our own code produced, and the values go through placeholders. Also remember: <code>addslashes</code> and old "magic quotes" are not defences. Use the database user with the fewest rights, hide database error messages from visitors, and remember the second-order case: text that was stored safely but later glued into a query is still dangerous.</p>

<h3>Other habits worth having</h3>
<table>
<thead><tr><th>Risk</th><th>Habit</th></tr></thead>
<tbody>
<tr><td>Password guessing</td><td>Limit login attempts; encourage long passphrases</td></tr>
<tr><td>Access control mistakes</td><td>Check on every request that this user may see or change this record (for example <code>WHERE id = :id AND user_id = :me</code>)</td></tr>
<tr><td>Data exposure</td><td>Return only needed columns; never print hashes or secrets</td></tr>
<tr><td>Outdated software</td><td>Keep PHP and libraries updated</td></tr>
<tr><td>Open redirects</td><td>Never redirect to an address taken from user input without an allow-list</td></tr>
</tbody>
</table>
<p>The list of top web risks is kept up to date by the OWASP project, which is a good place to continue learning.</p>

<h3>Try it yourself</h3>
<ol>
<li>Add the three CSRF functions to a shared file and put <code>csrf_field()</code> into your create, edit and delete forms.</li>
<li>Add <code>csrf_check()</code> to the three handlers.</li>
<li>Open the browser developer tools, change the hidden token value and submit. Confirm that you get the 403 message.</li>
<li>Make sure that edit and delete also check that the notice belongs to the logged-in user.</li>
<li>Audit your project for SQL strings built with variables and fix any you find.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>CSRF exploits the browser's automatic cookies; a random per-session token in every changing form stops it.</li>
<li>Create tokens with <code>random_bytes</code> and compare them with <code>hash_equals</code>.</li>
<li>SameSite cookies, POST-only changes and re-asking for passwords add layers.</li>
<li>Audit SQL for any variable inside the query text; use placeholders and allow-lists.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'Handling file uploads safely',
                    'minutes' => 20,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to accept an uploaded image, check it properly and store it so that it cannot harm your server. File uploads are risky: a careless upload form has allowed attackers to put a PHP script on a server and take control of it.</p>

<h3>The form</h3>
<p>A form that sends a file needs <code>enctype="multipart/form-data"</code> and method POST:</p>
<pre><code>&lt;form method="post" enctype="multipart/form-data"&gt;
  &lt;?= csrf_field() ?&gt;
  &lt;label&gt;Photo &lt;input type="file" name="photo" accept="image/png,image/jpeg"&gt;&lt;/label&gt;
  &lt;button type="submit"&gt;Upload&lt;/button&gt;
&lt;/form&gt;</code></pre>
<p>The <code>accept</code> attribute is only a hint for the user's file picker and gives no security. Everything must be checked again on the server.</p>

<h3>What PHP gives you</h3>
<p>The file arrives in <code>$_FILES["photo"]</code> with these parts: <code>name</code> (the original name, supplied by the user and not to be trusted), <code>type</code> (also supplied by the browser and not trusted), <code>tmp_name</code> (the temporary file on the server), <code>error</code> (a code, where <code>UPLOAD_ERR_OK</code> means success) and <code>size</code> (in bytes).</p>

<h3>A safe upload handler</h3>
<pre><code>&lt;?php
function save_photo(array $file, string $dir): string
{
    if ($file["error"] !== UPLOAD_ERR_OK) {
        throw new RuntimeException("The upload failed (code " . $file["error"] . ").");
    }
    if (!is_uploaded_file($file["tmp_name"])) {
        throw new RuntimeException("Invalid upload.");
    }
    if ($file["size"] &gt; 2 * 1024 * 1024) {
        throw new RuntimeException("The file is larger than 2 MB.");
    }

    // check the real content, not the name or the browser's claim
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo-&gt;file($file["tmp_name"]);
    $allowed = ["image/jpeg" =&gt; "jpg", "image/png" =&gt; "png"];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException("Only JPEG and PNG images are allowed.");
    }
    if (getimagesize($file["tmp_name"]) === false) {
        throw new RuntimeException("The file is not a valid image.");
    }

    // our own name, our own extension
    $name = bin2hex(random_bytes(16)) . "." . $allowed[$mime];
    if (!move_uploaded_file($file["tmp_name"], rtrim($dir, "/") . "/" . $name)) {
        throw new RuntimeException("Could not save the file.");
    }
    return $name;
}</code></pre>
<p>Walk through the protections one at a time:</p>
<ol>
<li><strong>Error code and <code>is_uploaded_file</code></strong> make sure PHP really received the file.</li>
<li><strong>Size limit.</strong> Also set <code>upload_max_filesize</code> and <code>post_max_size</code> in <code>php.ini</code> (or <code>.user.ini</code> on shared hosting). If the whole request exceeds <code>post_max_size</code>, <code>$_FILES</code> and <code>$_POST</code> arrive empty, so check for that case too.</li>
<li><strong>Real type detection.</strong> <code>finfo</code> reads the first bytes of the file to determine its type, which a user cannot fake by renaming <code>virus.php</code> to <code>photo.jpg</code>. <code>getimagesize</code> is a second check that it parses as an image.</li>
<li><strong>Allow-list of types</strong> mapped to extensions we choose. We never copy the user's extension.</li>
<li><strong>A random name</strong> prevents overwriting other files, path tricks such as <code>../../</code> in a filename, and guessable addresses. Never use the original name in the path. If you want to show it, store the original name in the database and escape it when you print it.</li>
<li><strong><code>move_uploaded_file</code></strong> moves the file out of the temporary folder.</li>
</ol>

<h3>Where to store files</h3>
<ul>
<li>Best: a folder <em>outside</em> the public web folder, with a PHP script that checks the user's permission and then sends the file with <code>readfile</code> and the right <code>Content-Type</code> header. Use this for private documents.</li>
<li>For public images, a folder such as <code>public/uploads</code> is common, but you must stop PHP from running there. For Apache put a <code>.htaccess</code> file inside the uploads folder:</li>
</ul>
<pre><code>php_flag engine off
RemoveHandler .php .phtml .php5
RemoveType .php .phtml .php5
Options -Indexes</code></pre>
<p>The <code>php_flag</code> line works only when PHP runs as an Apache module; on some hosts it causes a server error, so test it and ask your host for the correct method. <code>Options -Indexes</code> stops visitors from browsing the folder listing. Because we create the extension ourselves from an allow-list, a script can never get a <code>.php</code> name; these settings are a second layer.</p>

<h3>Store the name, not the file, in the database</h3>
<p>Save only the generated file name in a column such as <code>photo VARCHAR(64)</code> and build the address when you display it. Delete the file with <code>unlink</code> when its record is deleted, or you will slowly fill the disk.</p>

<h3>Other file types</h3>
<p>For documents such as PDF, use the same method with their MIME types, and treat them as downloads, not as pages: send <code>Content-Disposition: attachment</code>. Think about privacy: images can hold hidden metadata such as the place where a photo was taken. Re-saving an image with the GD functions removes that data and also proves that it really is an image.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create an <code>uploads</code> folder and the form above.</li>
<li>Write <code>save_photo</code> and upload a real JPEG. Check that the saved name is random and ends with <code>.jpg</code>.</li>
<li>Rename a text file to <code>fake.jpg</code> and upload it. Confirm that it is rejected.</li>
<li>Try a file larger than 2 MB and confirm the message.</li>
<li>Add the <code>.htaccess</code> file to the uploads folder and test that the site still serves your images.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Never trust the file name, the browser's type or the extension.</li>
<li>Check the upload error, size and real content type, and keep an allow-list of types.</li>
<li>Generate your own random file name and extension; save the file with <code>move_uploaded_file</code>.</li>
<li>Prevent PHP from running in the upload folder, or keep private files outside the web folder.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: How does a PHP session remember a visitor between requests?
- It stores all the data in the browser cookie
* A random session id is stored in a cookie and the data stays on the server
- It uses the visitor's IP address only
- It keeps a file open between requests
= The cookie carries only the id. The data lives on the server in $_SESSION.

Q: What is the correct way to store a password?
- Save it as plain text in the database
- Store md5($password)
* Store password_hash($password, PASSWORD_DEFAULT) and check it with password_verify
- Encrypt it with a key stored in the same database
= password_hash is a slow, salted one-way hash designed for passwords. md5 is far too fast.

Q: Why do login pages say "Email or password is incorrect" rather than saying which was wrong?
- To save a line of code
* So attackers cannot discover which email addresses are registered
- Because PHP does not allow two messages
- To make the page shorter
= Different messages would let an attacker test which accounts exist.

Q: Why call session_regenerate_id(true) right after a successful login?
- To make the session larger
* To defeat session fixation by replacing any session id an attacker may have planted
- To log the user out of other devices
- To remove the cookie
= A fresh id is issued at the moment the privilege level changes.

Q: A visitor posts a notice title that contains a script tag. Which defence stops it from running for other visitors?
- Using prepared statements
- Using password_hash
* Escaping the title with htmlspecialchars when printing it
- Using POST for the form
= This is XSS. Prepared statements protect SQL, not the HTML output. Escaping at output is the primary defence.

Q: A form lets users give their website address and you print it as a link. Which check is important? (choose all that apply)
* Accept only addresses that start with http:// or https://
* Escape the address for HTML when printing it
- Check that it ends in .com
- Convert it to lowercase
= A javascript: address can run code when clicked, so restrict schemes and escape for HTML.

Q: A malicious page contains a hidden form that posts to your delete.php while Amina is logged in to your site. Which protection stops this?
- A confirm dialog in JavaScript
- Hiding the delete.php file name
* A secret CSRF token in the form, checked against the session on the server
- Escaping the output
= The attacker's page cannot read the token, so the forged request fails the check.

Q: Which is NOT a safe step when handling an uploaded image?
- Checking the real MIME type with finfo
- Saving it under a random name with an extension chosen from an allow-list
* Saving it under the original file name that the user supplied
- Setting a size limit
= The original name is user-controlled and may contain path tricks or a dangerous extension.
QUIZ,
        ],
        [
            'title' => 'Module 4: Structure, routing and APIs',
            'summary' => 'Organise a growing application with includes, controllers, views and models, route requests through one entry point and publish JSON endpoints.',
            'lessons' => [
                [
                    'title' => 'Structuring code: includes, helpers and an MVC-style layout',
                    'minutes' => 20,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to move from a folder of tangled scripts to an organised project with a clear place for every kind of code. Mixing SQL, logic and HTML in one file is acceptable for a first page, but it becomes impossible to maintain as the application grows.</p>

<h3>The idea of MVC</h3>
<p><strong>Model-View-Controller</strong> is a way of separating three jobs:</p>
<table>
<thead><tr><th>Part</th><th>Job</th><th>In our notice board</th></tr></thead>
<tbody>
<tr><td>Model</td><td>Talks to the database and holds the business rules</td><td>The <code>Notice</code> class with methods such as <code>all()</code> and <code>find()</code></td></tr>
<tr><td>View</td><td>Produces HTML; contains almost no logic</td><td>Template files that print variables with escaping</td></tr>
<tr><td>Controller</td><td>Receives the request, uses models, chooses a view</td><td>The <code>NoticeController</code> class with methods such as <code>index()</code> and <code>store()</code></td></tr>
</tbody>
</table>
<p>The benefit is that you can change the page design without touching SQL, or change a query without touching HTML, and you know where to look when something breaks. You do not need a large framework to apply the idea.</p>

<h3>A folder layout</h3>
<pre><code>noticeboard/
  public/              the only folder visitors can reach
    index.php          front controller (entry point)
    .htaccess          sends every request to index.php
    css/  js/  uploads/
  app/
    Controllers/NoticeController.php
    Models/Notice.php
    Views/layout.php
    Views/notices/index.php
    helpers.php        e(), csrf functions, redirect()
    Database.php       the db() function
  config/
    config.php         database settings, outside public/
  storage/logs/        error logs</code></pre>
<p>Only <code>public/</code> is exposed by the web server. If a mistake ever makes PHP stop running, visitors still cannot download your configuration or code, because those files are outside the folder that is served. This is much better than the old habit of putting everything in <code>htdocs</code> next to each other.</p>

<h3>A model class</h3>
<pre><code>&lt;?php
declare(strict_types=1);

namespace App\Models;

use PDO;

final class Notice
{
    public function __construct(private PDO $pdo) {}

    /** @return array&lt;int, array&lt;string, mixed&gt;&gt; */
    public function all(int $limit = 20): array
    {
        $stmt = $this-&gt;pdo-&gt;prepare(
            "SELECT id, title, created_at FROM notices ORDER BY id DESC LIMIT :n"
        );
        $stmt-&gt;bindValue(":n", $limit, PDO::PARAM_INT);
        $stmt-&gt;execute();
        return $stmt-&gt;fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this-&gt;pdo-&gt;prepare("SELECT id, title, body FROM notices WHERE id = :id");
        $stmt-&gt;execute(["id" =&gt; $id]);
        $row = $stmt-&gt;fetch();
        return $row === false ? null : $row;
    }

    public function create(int $userId, string $title, string $body): int
    {
        $stmt = $this-&gt;pdo-&gt;prepare(
            "INSERT INTO notices (user_id, title, body) VALUES (:u, :t, :b)"
        );
        $stmt-&gt;execute(["u" =&gt; $userId, "t" =&gt; $title, "b" =&gt; $body]);
        return (int) $this-&gt;pdo-&gt;lastInsertId();
    }
}</code></pre>
<p>The model receives the PDO connection through its constructor: this is called <strong>dependency injection</strong>, and it makes the class easy to test and keeps it independent of how the connection is made. The doc comment tells editors what the array contains. <code>final</code> means no other class may extend it, and <code>?array</code> means "an array or null".</p>

<h3>Views and a controller</h3>
<p>A view is a PHP file that only prints. A tiny helper loads it and gives it variables:</p>
<pre><code>function render(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);
    ob_start();
    require __DIR__ . "/Views/" . $view . ".php";
    $content = ob_get_clean();
    require __DIR__ . "/Views/layout.php";   // prints $content inside the page frame
}</code></pre>
<p>Here <code>$view</code> always comes from your own code, never from user input. A controller method then looks like this:</p>
<pre><code>public function index(): void
{
    $notices = (new Notice(db()))-&gt;all();
    render("notices/index", ["notices" =&gt; $notices]);
}</code></pre>
<p>And the view <code>notices/index.php</code> contains only HTML and escaped output: <code>&lt;?php foreach ($notices as $n): ?&gt;&lt;h2&gt;&lt;?= e($n["title"]) ?&gt;&lt;/h2&gt;&lt;?php endforeach; ?&gt;</code>. A shared <code>layout.php</code> holds the page header, navigation and footer once for the whole site.</p>

<h3>Autoloading</h3>
<p>With many classes you do not want to write a <code>require</code> line for each. A small autoloader finds class files from their names, or you can use Composer, PHP's package manager, with a PSR-4 autoload setting. For a first project, a simple <code>spl_autoload_register</code> that turns <code>App\Models\Notice</code> into <code>app/Models/Notice.php</code> is enough, and it teaches you the idea that Composer automates.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create the folder layout and move your notice board files into it.</li>
<li>Write the <code>Notice</code> model with <code>all</code>, <code>find</code> and <code>create</code>.</li>
<li>Write <code>render()</code> and a layout with a header and a footer.</li>
<li>Create the controller method <code>index</code> and the view that lists notices with escaped output.</li>
<li>Check that no SQL remains in any view file.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>MVC separates data (model), page output (view) and request handling (controller).</li>
<li>Only the <code>public</code> folder should be reachable from the web; keep configuration and code outside it.</li>
<li>Pass the database connection into models rather than creating it inside them.</li>
<li>Views print escaped data and contain no SQL.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'Routing with a front controller',
                    'minutes' => 18,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to send every request through a single entry point and map clean addresses such as <code>/notices/5</code> to controller methods. A router gives you neat links, one place for security checks and a clear picture of everything your application can do.</p>

<h3>The front controller pattern</h3>
<p>Instead of having a separate file for each page, you send all requests that do not match a real file to <code>public/index.php</code>. That file starts the session, loads the code, decides which controller to run and sends the response. Apache needs a rule for this in <code>public/.htaccess</code>:</p>
<pre><code>RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]</code></pre>
<p>The conditions say: if the requested path is not an existing file or directory, rewrite to <code>index.php</code>, keeping the query string (<code>QSA</code>). Real files such as CSS and images are still served directly. The module that does this, <code>mod_rewrite</code>, is on by default in XAMPP and available on nearly all shared hosts. If your project lives in a subfolder such as <code>/noticeboard/public</code>, remember to account for that prefix in the router.</p>

<h3>A small router</h3>
<pre><code>&lt;?php
declare(strict_types=1);

final class Router
{
    /** @var array&lt;int, array{0:string,1:string,2:callable}&gt; */
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler): void
    {
        $this-&gt;routes[] = [$method, $pattern, $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = rtrim(parse_url($uri, PHP_URL_PATH) ?: "/", "/") ?: "/";

        foreach ($this-&gt;routes as [$m, $pattern, $handler]) {
            $regex = "#^" . preg_replace("#\{(\w+)\}#", "(?P&lt;$1&gt;[^/]+)", $pattern) . "$#";
            if ($m === $method &amp;&amp; preg_match($regex, $path, $found)) {
                $args = array_filter($found, "is_string", ARRAY_FILTER_USE_KEY);
                $handler(...array_values($args));
                return;
            }
        }
        http_response_code(404);
        echo "Page not found";
    }
}</code></pre>
<p>Each route is a method (GET or POST), a pattern with placeholders such as <code>{id}</code>, and a function to run. The pattern is turned into a regular expression where each <code>{name}</code> captures one path segment. <code>parse_url</code> separates the path from the query string, and a request for a path that no route matches gets a 404. A path that matches only under a different method is treated as not found in this simple version; a fuller router would answer 405.</p>

<h3>Registering routes</h3>
<pre><code>$router = new Router();

$router-&gt;add("GET",  "/",              fn() =&gt; (new NoticeController())-&gt;index());
$router-&gt;add("GET",  "/notices/{id}",  fn(string $id) =&gt; (new NoticeController())-&gt;show((int) $id));
$router-&gt;add("GET",  "/notices/new",   fn() =&gt; (new NoticeController())-&gt;create());
$router-&gt;add("POST", "/notices",       fn() =&gt; (new NoticeController())-&gt;store());
$router-&gt;add("POST", "/notices/{id}/delete", fn(string $id) =&gt; (new NoticeController())-&gt;destroy((int) $id));

$router-&gt;dispatch($_SERVER["REQUEST_METHOD"], $_SERVER["REQUEST_URI"]);</code></pre>
<p>Order matters: <code>/notices/new</code> must be listed <em>before</em> <code>/notices/{id}</code>, otherwise the word "new" would be taken as an id. The captured id arrives as a string, and the cast <code>(int)</code> turns it into a number; a controller should then check that the notice exists and return 404 otherwise. Using a POST route for deletion keeps the rule from the CRUD lessons.</p>

<h3>The controller and the response</h3>
<pre><code>public function show(int $id): void
{
    $notice = (new Notice(db()))-&gt;find($id);
    if ($notice === null) {
        http_response_code(404);
        render("errors/404");
        return;
    }
    render("notices/show", ["notice" =&gt; $notice]);
}

public function store(): void
{
    csrf_check();
    require_login();
    // validate, then Notice::create(...), then:
    redirect("/");
}</code></pre>
<p>Write a helper <code>redirect(string $to): never</code> that sends the Location header and calls <code>exit</code>, so you cannot forget it.</p>

<h3>Central security in one place</h3>
<p>Because every request passes through <code>index.php</code>, you can set the security headers, start the session with safe cookie settings and handle errors once. You can also protect groups of routes by wrapping them: a route that is only for administrators can check the role before the controller runs. Do not rely on a link being hidden in the menu to protect a page; the route itself must check the permission.</p>

<h3>Links in templates</h3>
<p>Write links through a helper such as <code>url("/notices/5")</code> that adds the base path in one place, so that moving the project from a subfolder to the root of a domain, as you will do when you deploy, needs only one change.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create <code>public/.htaccess</code> and <code>public/index.php</code> with the router class.</li>
<li>Register the routes above and make each controller method print a short message first, to test them.</li>
<li>Open <code>/notices/new</code> and <code>/notices/7</code> and check which route runs.</li>
<li>Visit an address that has no route and confirm the 404.</li>
<li>Swap the order of two routes on purpose and explain what breaks.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A front controller sends every request to one file; Apache rewrite rules make it work.</li>
<li>A router maps a method and a path pattern to a handler and captures values such as ids.</li>
<li>List specific routes before routes with placeholders, and answer unknown paths with 404.</li>
<li>Check permissions inside the route or controller, never only in the menu.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'REST-style JSON endpoints',
                    'minutes' => 20,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to build endpoints that return JSON so that JavaScript pages, mobile apps and other programs can use your data. Since you studied JavaScript and fetch, you can now build both sides of a modern application: the server that supplies the data and the page that shows it.</p>

<h3>What does REST-style mean?</h3>
<p>In a REST-style API each kind of thing (a resource, such as a notice) has an address, and the HTTP <em>method</em> says what you want to do with it:</p>
<table>
<thead><tr><th>Method and address</th><th>Meaning</th><th>Success status</th></tr></thead>
<tbody>
<tr><td><code>GET /api/notices</code></td><td>List notices</td><td>200 OK</td></tr>
<tr><td><code>GET /api/notices/5</code></td><td>Read notice 5</td><td>200 OK, or 404 if missing</td></tr>
<tr><td><code>POST /api/notices</code></td><td>Create a notice</td><td>201 Created</td></tr>
<tr><td><code>PUT /api/notices/5</code></td><td>Replace or update notice 5</td><td>200 OK</td></tr>
<tr><td><code>DELETE /api/notices/5</code></td><td>Delete notice 5</td><td>204 No Content</td></tr>
</tbody>
</table>
<p>Status codes tell the client what happened: 400 for a bad request, 401 when the caller is not logged in, 403 when they may not do this, 404 when not found, 422 when validation fails and 500 for a server fault. Use them correctly; do not return 200 with the word "error" inside.</p>

<h3>A JSON response helper</h3>
<pre><code>function json_response(mixed $data, int $status = 200): never
{
    http_response_code($status);
    header("Content-Type: application/json; charset=utf-8");
    header("X-Content-Type-Options: nosniff");
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

// Example output of json_response(["id" =&gt; 5, "title" =&gt; "Meeting"]):</code></pre>
<pre>{"id":5,"title":"Meeting"}</pre>
<p><code>json_encode</code> turns arrays into JSON text. An associative array becomes a JSON object and a plain list becomes a JSON array. <code>JSON_THROW_ON_ERROR</code> raises an exception rather than quietly returning false.</p>

<h3>Reading a JSON request body</h3>
<p>When JavaScript posts JSON, the data does not appear in <code>$_POST</code>. Read the raw body and decode it:</p>
<pre><code>function json_input(): array
{
    $raw = file_get_contents("php://input");
    try {
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        json_response(["error" =&gt; "Invalid JSON"], 400);
    }
    return is_array($data) ? $data : [];
}</code></pre>

<h3>Endpoints in the controller</h3>
<pre><code>public function apiIndex(): void
{
    json_response(["data" =&gt; (new Notice(db()))-&gt;all()]);
}

public function apiShow(int $id): void
{
    $notice = (new Notice(db()))-&gt;find($id);
    if ($notice === null) {
        json_response(["error" =&gt; "Notice not found"], 404);
    }
    json_response(["data" =&gt; $notice]);
}

public function apiStore(): void
{
    if (empty($_SESSION["user_id"])) {
        json_response(["error" =&gt; "Please log in"], 401);
    }
    $in    = json_input();
    $title = trim((string) ($in["title"] ?? ""));
    $body  = trim((string) ($in["body"] ?? ""));
    if ($title === "" || $body === "") {
        json_response(["error" =&gt; "Title and body are required"], 422);
    }
    $id = (new Notice(db()))-&gt;create((int) $_SESSION["user_id"], $title, $body);
    json_response(["data" =&gt; ["id" =&gt; $id]], 201);
}</code></pre>
<p>The same rules as for forms apply: validate everything, use prepared statements, and check permissions. JSON does not escape HTML: when JavaScript puts a field into the page, it must use <code>textContent</code> and not <code>innerHTML</code>, or XSS returns through the back door.</p>

<h3>Calling it from JavaScript</h3>
<pre><code>const res = await fetch("/api/notices");
const json = await res.json();
for (const n of json.data) {
  const li = document.createElement("li");
  li.textContent = n.title;      // safe: text, not HTML
  document.querySelector("#list").append(li);
}</code></pre>
<p>For requests that change data from the browser, send the CSRF token in a header and check it on the server, because cookies are still sent automatically. For a mobile app or another server you would use token-based authentication, such as a secret API key or a signed token in the <code>Authorization</code> header, and always HTTPS.</p>

<h3>Good habits for APIs</h3>
<ul>
<li>Return the same shape every time: <code>{"data": ...}</code> for success and <code>{"error": "..."}</code> for failure.</li>
<li>Never include secrets, hashes or other users' private details in a response.</li>
<li>Limit list size and offer <code>?page=</code> and <code>?limit=</code> with a maximum.</li>
<li>Do not use CORS headers such as <code>Access-Control-Allow-Origin: *</code> on endpoints that use cookies for login; allow only the specific origins you trust.</li>
<li>Version the address, for example <code>/api/v1/notices</code>, so that you can change it later without breaking apps already in use.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Write <code>json_response</code> and an endpoint <code>GET /api/notices</code> and open it in the browser.</li>
<li>Add <code>GET /api/notices/{id}</code> and test an id that does not exist to see the 404 JSON.</li>
<li>Create a small HTML page with JavaScript that fetches the list and builds <code>li</code> elements with <code>textContent</code>.</li>
<li>Try <code>POST /api/notices</code> with the browser console or a tool such as curl and check the 401 and 422 responses.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>REST-style APIs use the method (GET, POST, PUT, DELETE) and the address to say what to do with a resource.</li>
<li>Use accurate status codes and a consistent JSON shape.</li>
<li>Read JSON bodies from <code>php://input</code> and validate them like any other input.</li>
<li>When showing API data in a page, use <code>textContent</code>; protect state-changing calls with CSRF tokens or API tokens.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: In MVC, which part should contain the SQL queries?
- The view
- The layout
* The model
- The CSS
= The model talks to the database; views only print prepared data.

Q: Why should configuration and application code live outside the public web folder?
- So that PHP runs faster
* So that visitors can never download them, even if PHP stops processing files by mistake
- Because Apache cannot read files in public
- To make URLs shorter
= Only the public folder is reachable by URL, which protects secrets and source files.

Q: What does the rewrite rule in .htaccess with the two RewriteCond lines do?
- Blocks all CSS files
* Sends requests that are not real files or folders to index.php
- Encrypts the URL
- Redirects every request to HTTPS
= Existing files are served normally while other addresses go to the front controller.

Q: Why must the route /notices/new be registered before /notices/{id}?
- Alphabetical order is required
* Otherwise the word "new" would match the {id} pattern and run the wrong handler
- {id} routes are slower
- PHP forgets routes after the first match
= Routes are tested in order and the first match wins.

Q: A controller receives the id from the path as a string. What should it do before using it?
- Print it directly
* Cast or validate it as an integer, use it in a prepared statement and return 404 if the record does not exist
- Add it to the SQL text
- Ignore it
= Values from the path are user input and must be validated like any other.

Q: Which status code means that a new resource was created successfully?
- 200
* 201
- 204
- 301
= 201 Created is the standard answer to a successful POST that creates something.

Q: A JavaScript page receives a notice title from your JSON API and adds it to the page. Which is the safe way?
- element.innerHTML = notice.title
* element.textContent = notice.title
- document.write(notice.title)
- eval(notice.title)
= textContent inserts text, not HTML, which prevents XSS from API data.

Q: What does JSON_THROW_ON_ERROR change when you call json_decode?
- It makes decoding faster
* It throws an exception on invalid JSON instead of quietly returning null
- It encodes the data in XML
- It disables Unicode
= Errors become exceptions that you can catch and handle with a 400 response.
QUIZ,
        ],
        [
            'title' => 'Module 5: Configuration, deployment and a finished project',
            'summary' => 'Configure errors and secrets properly, deploy to shared hosting and bring everything together with a final project checklist.',
            'lessons' => [
                [
                    'title' => 'Configuration, errors, logging and secrets',
                    'minutes' => 18,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to separate development settings from production settings, log errors without showing them to visitors and keep passwords out of your code. A page that prints "SQLSTATE ... access denied for user" tells an attacker exactly where to look, while a blank page tells you nothing when you need to repair it. We need the middle path.</p>

<h3>Show errors in development, log them in production</h3>
<table>
<thead><tr><th>Setting</th><th>Development (your computer)</th><th>Production (live site)</th></tr></thead>
<tbody>
<tr><td><code>display_errors</code></td><td>On</td><td>Off</td></tr>
<tr><td><code>log_errors</code></td><td>On</td><td>On</td></tr>
<tr><td><code>error_reporting</code></td><td><code>E_ALL</code></td><td><code>E_ALL</code></td></tr>
</tbody>
</table>
<p>Always report all errors, including notices and warnings, because they point to real bugs. The difference is where they go: to the screen while you build, and to a log file when the public is watching. Set this at the start of <code>index.php</code>:</p>
<pre><code>$env = $config["env"] ?? "production";

error_reporting(E_ALL);
ini_set("display_errors", $env === "development" ? "1" : "0");
ini_set("log_errors", "1");
ini_set("error_log", __DIR__ . "/../storage/logs/php-error.log");</code></pre>
<p>The default is "production", so if the setting is missing the safe behaviour applies. Make sure the <code>storage/logs</code> folder is writable by PHP and is not reachable from the web.</p>

<h3>Friendly error pages</h3>
<p>Catch every uncaught problem in the front controller. The visitor sees a calm page and you receive the details in the log:</p>
<pre><code>set_exception_handler(function (Throwable $e) use ($env): void {
    error_log(get_class($e) . ": " . $e-&gt;getMessage() . " in " . $e-&gt;getFile() . ":" . $e-&gt;getLine());
    http_response_code(500);
    if ($env === "development") {
        echo "&lt;pre&gt;" . htmlspecialchars((string) $e, ENT_QUOTES, "UTF-8") . "&lt;/pre&gt;";
    } else {
        echo "Something went wrong. Please try again later.";
    }
});</code></pre>
<p>Use <code>try</code> and <code>catch</code> where you can do something useful, such as showing "that email is already registered", and let unexpected errors reach the handler above. Never catch an exception just to hide it. Never log passwords, tokens or full card or ID numbers.</p>

<h3>Configuration in one file</h3>
<p>Settings that differ between your laptop and the server (database name, user, password, environment) belong in a configuration file outside <code>public/</code>:</p>
<pre><code>&lt;?php
// config/config.php   (NOT committed to a public repository)
return [
    "env" =&gt; "production",
    "db"  =&gt; [
        "host" =&gt; "localhost",
        "name" =&gt; "mysite_db",
        "user" =&gt; "mysite_user",
        "pass" =&gt; "a-long-random-password-here",
    ],
    "base_path" =&gt; "",
];</code></pre>
<p>Load it once with <code>$config = require __DIR__ . "/../config/config.php";</code> and pass the database settings to your connection function. Keep a copy named <code>config.example.php</code> with fake values in your project so that others know what to fill in.</p>

<h3>Keep secrets out of version control</h3>
<p>If you use Git, add the real config file to <code>.gitignore</code>. A password pushed to a public repository must be treated as stolen, even if you delete it later, because the history remains and bots scan for secrets. Change it at once. Use different passwords for development and production, and give the production database user only the rights it needs.</p>

<h3>Time zones and character sets</h3>
<p>Set the time zone once so that dates are consistent, for example <code>date_default_timezone_set("Africa/Kampala")</code>. For the database consider storing times in UTC and converting for display. Make sure files, the database connection (<code>charset=utf8mb4</code>) and the HTML header (<code>&lt;meta charset="utf-8"&gt;</code>) all agree, which prevents garbled text.</p>

<h3>Useful checks before launch</h3>
<ul>
<li>Run <code>php -l file.php</code> to check a file for syntax errors.</li>
<li>Use a fresh browser profile to test the first visit, as a new user would.</li>
<li>Test with wrong, empty and very long input on every form.</li>
<li>Check what happens when the database is unavailable: the visitor should see the friendly page.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Create <code>config/config.php</code> and <code>config.example.php</code> and make your connection code read from the config.</li>
<li>Set the environment to development and create an error on purpose (call an undefined function). Read the message on screen.</li>
<li>Set it to production and repeat. Confirm that the screen shows the friendly message and that the log file contains the details.</li>
<li>Add the real config path to <code>.gitignore</code> if you use Git.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Show errors only in development; in production log them and show a friendly page.</li>
<li>Keep database settings and secrets in a configuration file outside the public folder and out of version control.</li>
<li>Default to the safe production behaviour when a setting is missing.</li>
<li>Never log or print passwords, tokens or other sensitive values.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'Deploying to shared hosting',
                    'minutes' => 20,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to move your application from XAMPP to a live shared-hosting account and check that it is safe and working. A project that only runs on your own laptop helps nobody, and the first deployment always teaches something, so we use a checklist.</p>

<h3>What you need</h3>
<ul>
<li>A hosting account that supports PHP 8.x and MySQL, with <strong>cPanel</strong> or a similar control panel. Check the PHP version offered; you want a supported 8.x release.</li>
<li>A domain name pointed to the host (the host explains the DNS settings), or a temporary address they provide.</li>
<li>An FTP or SFTP program, or the host's File Manager, and access to phpMyAdmin.</li>
</ul>

<h3>Step 1: prepare the application</h3>
<ol>
<li>Set the configuration for production: <code>env</code> to production, and base path to the correct folder.</li>
<li>Remove test files, <code>phpinfo()</code> pages, sample data and anything that reveals the structure of your site.</li>
<li>Export your database from phpMyAdmin using <strong>Export</strong>, choosing SQL format. Remove test users and test content first, or create an install script that sets up a clean database.</li>
</ol>

<h3>Step 2: create the database on the host</h3>
<ol>
<li>In cPanel open <strong>MySQL Databases</strong>. Create a database and a user with a long random password, and then add the user to the database with the privileges that the application needs. On shared hosting the names are usually prefixed with your account name, for example <code>acct_notices</code>.</li>
<li>Open <strong>phpMyAdmin</strong> on the host, select the new database and <strong>Import</strong> your SQL file. Check that tables exist and use the correct collation.</li>
</ol>

<h3>Step 3: upload the files</h3>
<p>Many shared hosts expose a folder called <code>public_html</code> as the web root. Our layout has a <code>public/</code> folder that should be the only one visible, so use one of these arrangements, in order of preference:</p>
<ol>
<li>Upload the whole project <em>next to</em> <code>public_html</code>, outside it, and make the contents of <code>public/</code> the contents of <code>public_html</code> (or point the domain's document root to <code>public/</code> if your panel allows it, which is the cleanest solution). Edit the paths in <code>index.php</code> that load <code>app/</code> and <code>config/</code> accordingly.</li>
<li>If you must put everything inside <code>public_html</code>, move the code into a sub-folder, and add a <code>.htaccess</code> file in the code folders with <code>Require all denied</code> (or <code>Deny from all</code> on old servers) so that the files cannot be downloaded. Then test by trying to open <code>/config/config.php</code> in the browser: you should get an error and never see code or settings.</li>
</ol>
<p>Upload in binary mode, keep the folder structure and make <code>storage/logs</code> and <code>uploads</code> writable (permissions 755 for folders and 644 for files are the usual safe values, only raising a folder if the host requires it; never use 777).</p>

<h3>Step 4: configure and test</h3>
<ol>
<li>Edit <code>config/config.php</code> on the server with the real database name, user and password.</li>
<li>In the control panel choose the PHP version (often called <strong>Select PHP Version</strong> or <strong>MultiPHP Manager</strong>) and make sure that extensions such as <code>pdo_mysql</code>, <code>mbstring</code> and <code>fileinfo</code> are enabled.</li>
<li>Turn on HTTPS. Most hosts offer a free certificate through <strong>SSL/TLS</strong> or <strong>Let's Encrypt</strong> in the panel. Then force HTTPS by adding the rule below to <code>.htaccess</code> in the web root.</li>
</ol>
<pre><code>RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]</code></pre>
<p>After the certificate is active, test the whole site:</p>
<ol>
<li>Visit the site and walk through every feature: register, log in, create, edit, delete, upload, log out. Test on a phone too.</li>
<li>Cause an error deliberately (for example by requesting a missing page) and confirm that visitors see friendly messages and that the log is being written.</li>
</ol>

<h3>Step 5: harden and maintain</h3>
<ul>
<li>Set the session cookie option <code>secure</code> to true now that HTTPS works.</li>
<li>Take regular <strong>backups</strong> of both the files and the database. The host's backup is not a promise; keep your own copy somewhere else, and test that you can restore it.</li>
<li>Keep a changelog and deploy in small steps. Upload a new version to a staging folder or sub-domain and test it before you replace the live one.</li>
<li>Watch the error log weekly and update PHP when your host retires the version you use.</li>
<li>Never edit live files carelessly: a typo can take the site down. Use version control and upload from a known good copy.</li>
<li>Write down who owns the domain and hosting, and where the passwords are stored (in a password manager), so that the organisation does not depend on one person's memory.</li>
</ul>

<h3>Common problems</h3>
<table>
<thead><tr><th>Symptom</th><th>Likely cause</th></tr></thead>
<tbody>
<tr><td>500 Internal Server Error on every page</td><td>A syntax error, a bad <code>.htaccess</code> rule (such as <code>php_flag</code> on a host that does not allow it) or wrong permissions. Check the error log.</td></tr>
<tr><td>Blank white page</td><td>A PHP error with display switched off. Read the log file.</td></tr>
<tr><td>"Access denied for user"</td><td>Wrong database user, password or host, or the user is not attached to the database.</td></tr>
<tr><td>Links beyond the home page give 404</td><td>The rewrite rules are not active or the base path is wrong.</td></tr>
<tr><td>Garbled characters</td><td>Character set mismatch between files, database and headers; use UTF-8 and utf8mb4 everywhere.</td></tr>
<tr><td>Uploads fail</td><td>Folder not writable or upload limits too low.</td></tr>
</tbody>
</table>

<h3>Try it yourself</h3>
<ol>
<li>Write your own deployment checklist from the steps above.</li>
<li>Export your local database and look at the SQL file in a text editor.</li>
<li>If you have hosting, follow the steps with a small test project. If you do not, practise the file and database parts using a second XAMPP folder with a different database name.</li>
<li>Try to open your config file by its address and confirm that it is blocked.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Create the database and user on the host, import your SQL and update the configuration.</li>
<li>Keep code and configuration outside the web root, or deny access to them, and test that.</li>
<li>Use HTTPS, correct permissions (never 777) and the right PHP version and extensions.</li>
<li>Back up files and database yourself, and deploy changes in small, tested steps.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'Final project: a members\' notice board',
                    'minutes' => 22,
                    'body' => <<<'HTML'
<p>After this lesson you will have a plan and a checklist for building and releasing a complete application from everything you have learned. The best proof of skill is a finished project in which every part is secure, tidy and works for real users, and you can show it to a client or an employer.</p>

<h3>The brief</h3>
<p>Build a <strong>members' notice board</strong> for a youth group, a school club or a small organisation in your community. Members register and log in. Logged-in members can post notices with an optional image, edit or delete their own notices, and everyone can read the notices. An administrator can delete any notice. The application also provides a JSON list of the latest notices for a future mobile app.</p>

<h3>Build in stages</h3>
<ol>
<li><strong>Database.</strong> Tables <code>users</code> (with a <code>role</code> column holding member or admin), <code>notices</code> (with an optional <code>image</code> column) and foreign keys. Use utf8mb4 and InnoDB. Create a limited database user.</li>
<li><strong>Skeleton.</strong> The folder layout from lesson 13, <code>public/.htaccess</code>, a front controller, a router and helpers for <code>e()</code>, CSRF tokens, redirects and rendering.</li>
<li><strong>Accounts.</strong> Registration, login, logout, <code>require_login()</code> and <code>require_admin()</code> guards, with password hashing and a regenerated session id.</li>
<li><strong>Notices.</strong> Create, list with pagination, view, edit and delete. Every changing action is a POST with a CSRF token. Editing and deleting check that the notice belongs to the user or that the user is an administrator.</li>
<li><strong>Images.</strong> The safe upload function from lesson 12, a size limit of 2 MB, random names and an uploads folder that cannot run PHP.</li>
<li><strong>API.</strong> <code>GET /api/notices</code> returning the latest 20 notices as JSON with an accurate status code and no private fields.</li>
<li><strong>Quality.</strong> Error handling, logging, a friendly 404 and 500 page, and configuration files.</li>
<li><strong>Release.</strong> Deploy to shared hosting or to a second local environment, with HTTPS if available.</li>
</ol>

<h3>Authorisation: the check people forget</h3>
<p>Logging in proves who someone is. <strong>Authorisation</strong> decides what they may do, and it must be checked on the server for every action. A very common flaw is to hide the "Edit" button from other people's notices but forget to check ownership in the edit handler, so anyone can change <code>/notices/7/edit</code> to another number and edit someone else's notice. Put the rule into the query, so it cannot be forgotten:</p>
<pre><code>// the notice is deleted only if it is yours, or you are an admin
if ($isAdmin) {
    $stmt = db()-&gt;prepare("DELETE FROM notices WHERE id = :id");
    $stmt-&gt;execute(["id" =&gt; $id]);
} else {
    $stmt = db()-&gt;prepare("DELETE FROM notices WHERE id = :id AND user_id = :me");
    $stmt-&gt;execute(["id" =&gt; $id, "me" =&gt; $_SESSION["user_id"]]);
}
if ($stmt-&gt;rowCount() === 0) {
    http_response_code(403);
}</code></pre>

<h3>The security checklist</h3>
<table>
<thead><tr><th>Check</th><th>Done when</th></tr></thead>
<tbody>
<tr><td>SQL injection</td><td>Every query with outside values uses placeholders; sort columns come from allow-lists</td></tr>
<tr><td>XSS</td><td>Every printed value goes through <code>e()</code>; user links allow only http and https</td></tr>
<tr><td>CSRF</td><td>Every form and changing request checks a token; session cookie uses SameSite</td></tr>
<tr><td>Passwords</td><td>Hashed with <code>password_hash</code>; same message for wrong email and wrong password</td></tr>
<tr><td>Sessions</td><td>Session id regenerated at login; cookies are HttpOnly and Secure on HTTPS</td></tr>
<tr><td>Authorisation</td><td>Each action checks login, ownership and role on the server</td></tr>
<tr><td>Uploads</td><td>Real type check, size limit, random name, no PHP execution in the folder</td></tr>
<tr><td>Errors</td><td>Display off in production, details in a log, friendly pages</td></tr>
<tr><td>Secrets</td><td>Config outside <code>public/</code>, not in version control</td></tr>
<tr><td>Backups</td><td>Files and database copied regularly and a restore has been tested</td></tr>
</tbody>
</table>

<h3>Test like an attacker</h3>
<ul>
<li>Type <code>&lt;script&gt;alert(1)&lt;/script&gt;</code> and a single quote <code>'</code> in every text field. Nothing should run or break.</li>
<li>Change ids in the address to other numbers and try to view, edit or delete things that are not yours.</li>
<li>Remove or alter the hidden CSRF field and submit. You should get a 403.</li>
<li>Upload a renamed text file and an oversized image. Both must be refused.</li>
<li>Visit private pages while logged out.</li>
</ul>

<h3>Showing your work</h3>
<p>Write a short README: what the application does, how to install it (create the database, import the SQL, copy <code>config.example.php</code>), the folder layout and how you protected it. Take screenshots, and keep the code in a Git repository without secrets. For a client, add a one-page guide for the administrator and a list of the maintenance tasks: backups, updates and log checks. A project that others can install and understand is worth much more than one that only works for its author.</p>

<h3>Where next?</h3>
<p>You can grow in several directions: learn Composer and a mature framework such as Laravel, which applies the ideas in this course with many ready-made tools; add automated tests with PHPUnit; learn Git and continuous deployment; or build the front end of your application further with the JavaScript you already know. Whatever you choose, the habits from this course remain: validate input, escape output, use placeholders, check permissions on the server and treat the users' data with respect.</p>

<h3>Try it yourself</h3>
<ol>
<li>Write the database schema for the final project and create it.</li>
<li>Build the stages in order and tick each off the security checklist as you complete it.</li>
<li>Ask a classmate to try to break the finished application with the attacker tests and record what they find.</li>
<li>Deploy it and write the README.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Build in stages and finish each one, including its security, before moving on.</li>
<li>Authorisation must be checked on the server for every action, ideally inside the query.</li>
<li>Test with hostile input and by changing ids, tokens and files.</li>
<li>Document the project and keep it maintainable; secure and tidy code is what clients pay for.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What should display_errors and log_errors be set to on a live production site?
- display_errors on, log_errors off
* display_errors off, log_errors on
- Both off
- Both on
= Visitors must not see error details, but you must be able to read them in the log.

Q: Which is the safest place for the file that holds your database password?
- Inside public/ so that PHP can find it easily
- In a JavaScript file
* Outside the public web folder and excluded from version control
- In the page footer as a comment
= It must not be downloadable and must not be committed to a repository.

Q: You accidentally pushed a database password to a public Git repository and then deleted the file. What should you do?
- Nothing, it is deleted
* Treat the password as stolen and change it immediately
- Rename the repository
- Wait a week
= The history keeps the old file and bots scan for secrets, so the password must be replaced.

Q: Which file permissions are normally safe for uploaded PHP project files and folders on a shared host?
- 777 everywhere
* 644 for files and 755 for folders
- 000 for everything
- 666 for folders
= 777 lets anyone write; use the least permissive values that work.

Q: On the live site, any page shows "SQLSTATE access denied for user". What is the most likely cause?
- The CSS is missing
* Wrong database user name, password or host in the configuration, or the user is not attached to the database
- HTTPS is turned on
- The router has too many routes
= The database server rejected the login details.

Q: A hidden "Edit" button does not appear next to other people's notices, but the edit URL works for any notice id. What is missing?
- A bigger font
* A server-side authorisation check of ownership or role in the edit handler
- A longer password
- A different charset
= Hiding buttons is not security. The server must check permission on every request.

Q: Which ensures that a member can delete only their own notice, even if the check elsewhere is forgotten?
- Using a bigger LIMIT
* Including user_id = :me in the DELETE query's WHERE clause (unless the user is an admin)
- Hiding the delete button
- Using a GET link
= Putting the ownership rule inside the query makes it hard to bypass.

Q: What is the best proof of your PHP skills for a client or employer?
- A list of the functions you memorised
* A finished, secure, documented application that others can install and test
- A project with no security because it was quick
- Only screenshots
= Working, documented and secure projects are the evidence clients value.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which statement about how PHP pages are served is true?
- The browser runs the PHP code
* The server runs the PHP code and sends only the generated output to the browser
- PHP code is visible to visitors if they choose View source
- PHP files must be opened with file:// to work
= PHP runs on the server, so visitors never see the code.

Q: What does declare(strict_types=1); do?
- Turns off all warnings
* Stops PHP from silently converting scalar values passed to typed functions, so mistakes appear as errors
- Makes all variables constant
- Enables the database
= Strict typing reveals type mistakes early.

Q: What is the result of var_dump(1 === "1")?
- bool(true)
* bool(false)
- int(1)
- string(1) "1"
= The strict comparison also compares types.

Q: In PHP, what does the expression $row["phone"] ?? "none" return when the key phone does not exist?
* "none"
- A warning and null
- An empty array
- false
= The null coalescing operator supplies the default without a warning.

Q: Which request method should be used for a form that creates or deletes data?
- GET
* POST
- Either, there is no rule
- OPTIONS
= Requests that change data should not be possible through simple links, so POST is used.

Q: Which function should wrap user-supplied text when it is printed in HTML?
- trim
* htmlspecialchars with ENT_QUOTES and UTF-8
- strtolower
- md5
= It turns special characters into harmless entities and prevents XSS.

Q: Which is the correct DSN part to make PDO use full Unicode?
- charset=latin1
* charset=utf8mb4
- charset=ascii
- charset=utf7
= utf8mb4 covers every character, including emoji.

Q: Which PDO option makes errors throw exceptions?
* PDO::ATTR_ERRMODE set to PDO::ERRMODE_EXCEPTION
- PDO::ATTR_TIMEOUT set to 0
- PDO::FETCH_ASSOC
- PDO::PARAM_INT
= ERRMODE_EXCEPTION stops silent failures.

Q: Which code is vulnerable to SQL injection?
- $pdo->prepare("SELECT * FROM users WHERE id = :id")
- $stmt->execute([$email])
* $pdo->query("SELECT * FROM users WHERE name = '" . $_GET["name"] . "'")
- $stmt->bindValue(":id", $id, PDO::PARAM_INT)
= The user's text becomes part of the SQL command.

Q: Which of these can NOT be replaced by a placeholder in a prepared statement?
- A string value in a WHERE clause
- An integer in LIMIT
* A column name in ORDER BY
- A value in INSERT
= Placeholders stand for values only. Identifiers must be selected from an allow-list.

Q: Why does the INSERT and redirect pattern end with header("Location: ...") followed by exit?
- To clear the session
* To send the user to a new page and stop the script, so a refresh does not resend the form
- To log the user out
- To make the query faster
= Post-redirect-get avoids duplicate submissions, and exit stops the script from continuing.

Q: How should a password be stored?
- As plain text
- As md5 of the password
* With password_hash, verified with password_verify
- Base64 encoded
= password_hash is salted, slow and designed for the purpose.

Q: Which database column definition is suitable for the output of password_hash?
- CHAR(10)
- VARCHAR(20)
* VARCHAR(255)
- TINYINT
= Hashes are currently 60 characters and may grow, so 255 is the recommended size.

Q: Why is session_regenerate_id(true) called after login?
- To free memory
* To prevent session fixation by issuing a new id when the user becomes authenticated
- To change the password
- To turn on HTTPS
= An attacker who knew the old id gains nothing.

Q: What is XSS?
- Sending too many requests to a server
* Getting a browser to run an attacker's script because untrusted text was printed as HTML
- Guessing passwords
- Stealing database backups
= Cross-site scripting happens when output is not escaped.

Q: A user-provided website address is shown as a link. Which value is most dangerous if allowed?
- https://example.org
- http://example.org/page
* javascript:alert(1)
- https://example.org/a?b=1
= The javascript: scheme runs code when clicked, so only http and https should be allowed.

Q: What does a CSRF token protect against?
- SQL injection
- Weak passwords
* Forged requests that the browser sends automatically with the user's cookies
- Slow queries
= The attacker's page cannot know the secret token.

Q: Which function creates a cryptographically secure random value suitable for tokens?
- rand()
- uniqid()
* random_bytes()
- time()
= random_bytes uses the system's secure generator.

Q: Which checks belong in a safe image upload handler? (choose all that apply)
* Upload error code and size limit
* Detecting the real MIME type from the file's contents
* Generating a random file name with an extension from an allow-list
- Using the extension that the user's file name has
= The user's name and extension are untrusted. The others are essential.

Q: In an MVC-style project, what should a view file mostly contain?
- SQL queries
- Business rules and password checks
* HTML with escaped output and simple loops
- Database passwords
= Views only present data prepared by the controller.

Q: What is the main benefit of having only a public folder reachable from the web?
- Faster pages
* Code and configuration cannot be downloaded even if PHP stops running files
- It is required by PHP
- It makes SQL safe
= Files outside the document root cannot be requested by URL.

Q: Which is the purpose of the .htaccess rule with RewriteCond %{REQUEST_FILENAME} !-f?
- To block all files
* To send addresses that are not real files to index.php, where the router handles them
- To delete files
- To compress images
= It implements the front controller pattern.

Q: Two routes, /items/new and /items/{id}, exist. Why must /items/new be added first?
- It is faster
* A router tries routes in order and /items/{id} would otherwise match "new"
- PHP sorts routes alphabetically
- {id} cannot match letters
= The first matching route wins.

Q: Which status code should an API return when a requested notice does not exist?
- 200
- 201
* 404
- 500
= 404 Not Found tells the client that the resource is missing.

Q: A browser page gets a notice title from your JSON API. How should it insert the title in the page safely?
- innerHTML
- document.write
* textContent
- eval
= textContent treats the value as text, avoiding XSS.

Q: Which environment setting is right for production?
- display_errors on and no logging
* display_errors off, errors logged to a file, friendly error page
- error_reporting off
- phpinfo() page left online
= Visitors must not see technical details, but you need the log.

Q: What should you do with files such as config.php in a Git project?
- Commit them to a public repository
* Keep real secrets out of version control and keep an example file with fake values
- Rename them to .txt and commit
- Send them by chat
= Secrets in repositories are routinely stolen.

Q: On shared hosting your site shows a blank white page after an upload. What is the first thing to check?
- The monitor
* The PHP error log, because errors are hidden from the screen in production
- The domain registrar
- The browser icon
= Logs reveal the cause when display_errors is off.

Q: Which description of authorisation is correct?
- Proving who the user is
* Deciding what an authenticated user is allowed to do, checked on the server for every action
- Encrypting passwords
- Hiding buttons in the menu
= Authentication is who you are, authorisation is what you may do, and hiding buttons does not enforce it.
QUIZ,
];
