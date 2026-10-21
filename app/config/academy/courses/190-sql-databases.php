<?php
return [
    'slug'        => 'sql-databases',
    'title'       => 'Databases and SQL for Beginners',
    'category'    => 'data',
    'level'       => 'intermediate',
    'icon'        => 'layers',
    'sort'        => 190,
    'summary'     => 'Understand how databases store information and use SQL to ask questions, summarise results, join tables and change data safely, finishing with a small library project.',
    'description' => '<p>Almost every app, website, school record system, mobile money service and shop till stores its information in a <strong>database</strong>. <strong>SQL</strong> (Structured Query Language) is the language used to ask a database questions such as "which customers have not ordered this month?" or "what were our total sales per product?". It has been in use for decades and is still one of the most useful skills in any data, office or software job.</p><p>This course teaches you to think in tables, design a small database on paper, and then write real SQL queries step by step. You will read data, summarise it, combine several tables with joins, and add, change and delete records carefully. Every example uses small sample tables that you can type into a free practice tool and check against the results printed in the lesson.</p><p>You can practise at no cost, either in a browser-based SQL playground (a web page where you type SQL and see results, with nothing to install) or with SQLite and a free visual tool such as DB Browser for SQLite. The SQL in this course works in both SQLite and MySQL; where they differ, the lesson tells you.</p>',
    'outcomes'    => [
        'Explain what tables, rows, columns, primary keys and foreign keys are.',
        'Design a small database on paper and split the information into sensible tables.',
        'Read data with SELECT, WHERE, ORDER BY, LIMIT, DISTINCT, LIKE, IN and BETWEEN.',
        'Handle missing values correctly with NULL.',
        'Summarise data with COUNT, SUM, AVG, MIN, MAX, GROUP BY, HAVING and CASE.',
        'Combine tables with INNER JOIN and LEFT JOIN and answer questions with subqueries.',
        'Create tables, insert, update and delete data safely, and understand constraints and indexes.',
        'Protect data with backups and recognise the danger of SQL injection.',
    ],
    'requirements'=> [
        'A computer or laptop with a web browser',
        'Basic computer skills and comfort with spreadsheets (see Microsoft Excel: Spreadsheets from Zero)',
        'No programming experience needed',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'SQLite: official website', 'url' => 'https://www.sqlite.org/'],
        ['title' => 'SQLite SQL language reference', 'url' => 'https://www.sqlite.org/lang.html'],
        ['title' => 'DB Browser for SQLite', 'url' => 'https://sqlitebrowser.org/'],
        ['title' => 'MySQL documentation', 'url' => 'https://dev.mysql.com/doc/'],
    ],
    'modules' => [
        [
            'title'   => 'Module 1: What a database is',
            'summary' => 'Learn how data is organised in tables, why databases beat spreadsheets for shared records, how keys connect tables, and set up a place to practise.',
            'lessons' => [
                [
                    'title'   => 'Tables, rows and columns',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what a database is and describe its parts using the words tables, rows and columns. This matters because every system you use, from a school register to a mobile money app, keeps its information in the same simple shape.</p>

<h3>What is a database?</h3>
<p>A <strong>database</strong> is an organised collection of information stored so that it can be found, changed and shared quickly. The software that looks after the database is called a <strong>database management system</strong> (DBMS). Well-known examples are SQLite, MySQL, PostgreSQL and Microsoft SQL Server. You talk to most of them using the same language, <strong>SQL</strong>.</p>

<h3>The parts of a table</h3>
<p>A relational database keeps its information in <strong>tables</strong>. A table looks like a spreadsheet sheet, but with strict rules.</p>
<ul>
<li>A <strong>column</strong> (also called a field) holds one kind of fact, such as a name or a price. Every column has a name and a data type, for example text, whole number or date.</li>
<li>A <strong>row</strong> (also called a record) holds all the facts about one thing, such as one product or one student.</li>
<li>A <strong>cell</strong> is the value where a row and a column meet.</li>
</ul>
<p>Here is a small table called <code>products</code> from a shop in Gulu:</p>
<table>
<thead><tr><th>product_id</th><th>name</th><th>category</th><th>price</th><th>stock</th></tr></thead>
<tbody>
<tr><td>1</td><td>Notebook</td><td>Stationery</td><td>2500</td><td>40</td></tr>
<tr><td>2</td><td>Pen</td><td>Stationery</td><td>500</td><td>120</td></tr>
<tr><td>3</td><td>Sugar</td><td>Food</td><td>4500</td><td>30</td></tr>
</tbody>
</table>
<p>This table has five columns and three rows. Each row is one product. Notice that every cell in the <code>price</code> column is a number and every cell in <code>name</code> is text. A database refuses to put a word in a number column. That strictness is what keeps data clean.</p>

<h3>Naming habits</h3>
<p>Good table and column names make queries easy to read. Use lower-case words joined with underscores, such as <code>order_date</code>. Name a table after the thing each row describes, usually in the plural: <code>products</code>, <code>students</code>, <code>loans</code>. Avoid spaces and special characters because they cause errors later.</p>

<h3>What can you do with a database?</h3>
<p>Four jobs cover almost everything, and programmers call them CRUD:</p>
<ul>
<li><strong>Create</strong> new records (a new customer joins).</li>
<li><strong>Read</strong> records (show me today's sales).</li>
<li><strong>Update</strong> records (the price of sugar changed).</li>
<li><strong>Delete</strong> records (remove a duplicate entry).</li>
</ul>
<p>In SQL these become INSERT, SELECT, UPDATE and DELETE. Most of this course is about reading, because asking good questions of data is the skill employers want most.</p>

<h3>Try it yourself</h3>
<ol>
<li>Pick something you know well, such as your phone contacts, a school class list or a shop stock list.</li>
<li>On paper, draw a table for it with a name at the top and 4 or 5 columns.</li>
<li>Write a data type next to each column (text, whole number, decimal number or date).</li>
<li>Fill in 3 rows of made-up data.</li>
<li>Point to one row, one column and one cell and say each word aloud.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A database is organised information; a DBMS is the software that manages it.</li>
<li>A table has columns (kinds of facts) and rows (one record each).</li>
<li>Each column has a name and a data type that the database enforces.</li>
<li>The four basic jobs are create, read, update and delete.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Spreadsheets versus databases, and relational thinking',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will know when a spreadsheet is enough and when you need a database, and you will understand why databases split information into several linked tables. This is the main idea behind everything else in the course.</p>

<h3>Where spreadsheets struggle</h3>
<p>A spreadsheet is a wonderful tool for one person with a small list. Problems appear when the list grows or several people use it at once.</p>
<table>
<thead><tr><th>Question</th><th>Spreadsheet</th><th>Database</th></tr></thead>
<tbody>
<tr><td>Many people editing at once</td><td>Conflicts and copies named "final2"</td><td>Designed for many users at the same time</td></tr>
<tr><td>Millions of rows</td><td>Slow or fails</td><td>Handles very large tables</td></tr>
<tr><td>Rules for data</td><td>Anyone can type anything in any cell</td><td>Types and rules are enforced</td></tr>
<tr><td>Repeated information</td><td>Copied again and again</td><td>Stored once and linked</td></tr>
<tr><td>Asking new questions</td><td>Manual filters and formulas</td><td>Write a query in seconds</td></tr>
</tbody>
</table>
<p>Neither tool is "better". Many people export a query result from a database into a spreadsheet to make a chart. They work well together.</p>

<h3>The problem of repeated data</h3>
<p>Imagine one sheet that records every sale and repeats the customer's name, town and phone number on each line. If Akello Grace has bought ten times, her details appear ten times. When she changes her phone number you must fix ten rows, and if you miss one, the sheet now disagrees with itself. This is called an <strong>update problem</strong>, and it also wastes space and makes typing mistakes likely ("Gulu", "gulu", "Gullu").</p>

<h3>The relational idea</h3>
<p>The fix is to keep each kind of thing in its own table and to store each fact once. Customers go in a <code>customers</code> table. Sales go in an <code>orders</code> table, and each order records only a customer number, not the whole customer. To see the name next to the order, the database looks the number up. That lookup is called a <strong>join</strong>, and you will learn it in Module 4.</p>
<p>Here is the idea in a picture made of words:</p>
<pre><code>customers                       orders
customer_id | name              order_id | customer_id | order_date
1           | Akello Grace      101      | 1           | 2025-03-01
2           | Okello Peter      102      | 1           | 2025-03-05
                                103      | 2           | 2025-03-06</code></pre>
<p>Akello's phone number (if we stored it) would live in one row of <code>customers</code>. Orders 101 and 102 both point to customer 1. Change her number once and every order sees the new number.</p>

<h3>Relationship types</h3>
<ul>
<li><strong>One-to-many:</strong> one customer has many orders, but each order belongs to one customer. This is the most common kind.</li>
<li><strong>Many-to-many:</strong> one order can contain many products and one product can appear in many orders. We handle this with a third "link" table.</li>
<li><strong>One-to-one:</strong> rare; for example a person and their single passport record.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Imagine a school with students, teachers and subjects.</li>
<li>Write down one fact that would be repeated many times if kept in a single sheet (for example a teacher's phone number).</li>
<li>Decide which table that fact should live in so it is stored once.</li>
<li>Label each relationship as one-to-many or many-to-many: student to subject, teacher to subject, class to student.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Spreadsheets suit small personal lists; databases suit shared, large, rule-bound data.</li>
<li>Repeating information causes errors; store each fact once.</li>
<li>Relational databases link tables using shared numbers called keys.</li>
<li>Relationships are one-to-many, many-to-many or (rarely) one-to-one.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Primary keys, foreign keys and designing on paper',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose a primary key, spot a foreign key and sketch a small database for a school or a shop before touching a computer. Planning on paper takes ten minutes and saves hours of repair later.</p>

<h3>Primary keys</h3>
<p>A <strong>primary key</strong> is a column (or set of columns) whose value is different for every row and never empty. It is the row's identity card. In <code>products</code>, <code>product_id</code> is the primary key: product 3 is always the sugar and no other row can use the number 3.</p>
<p>Why not use the name? Two products may share a name, and names change. Numbers given by the system never need to change, so they make safe keys. Phone numbers and national ID numbers make poor primary keys because people change phones and typing errors happen.</p>

<h3>Foreign keys</h3>
<p>A <strong>foreign key</strong> is a column in one table that holds the primary key value of a row in another table. In <code>orders</code>, the column <code>customer_id</code> is a foreign key pointing to <code>customers.customer_id</code>. A good database can enforce this: it refuses an order for customer 99 if customer 99 does not exist.</p>

<h3>Designing a school database: five steps</h3>
<ol>
<li><strong>List the things</strong> you need to record: students, teachers, subjects, classrooms.</li>
<li><strong>Make a table for each thing</strong> with a primary key, for example <code>student_id</code>.</li>
<li><strong>List the facts</strong> about each thing and make them columns. Keep each column to one fact (use <code>first_name</code> and <code>last_name</code>, not one big "name" column if you will want to sort by surname).</li>
<li><strong>Find the relationships</strong> and add foreign keys. A teacher teaches many subjects, so put <code>teacher_id</code> in <code>subjects</code>.</li>
<li><strong>Handle many-to-many</strong> with a link table. A student takes many subjects and a subject has many students, so create <code>enrolments</code> with <code>student_id</code> and <code>subject_id</code>.</li>
</ol>

<h3>The finished sketch</h3>
<pre><code>students   (student_id PK, first_name, last_name, birth_date, district)
teachers   (teacher_id PK, full_name, phone)
subjects   (subject_id PK, title, teacher_id FK -&gt; teachers)
enrolments (enrolment_id PK, student_id FK -&gt; students,
            subject_id FK -&gt; subjects, term, mark)</code></pre>
<p>Check the design by asking real questions. "Which subjects does Atim take?" starts in <code>students</code>, goes through <code>enrolments</code> and ends in <code>subjects</code>. "Who teaches mathematics?" goes from <code>subjects</code> to <code>teachers</code>. If a question needs data you did not store, add a column or table now.</p>

<h3>A shop alternative</h3>
<p>A small shop could use <code>products</code>, <code>customers</code>, <code>orders</code> and <code>order_items</code>. The last table links orders and products and also stores the quantity bought, a fact that belongs to the pair rather than to either side. You will build exactly this in Module 4.</p>

<h3>Try it yourself</h3>
<ol>
<li>Choose a school or a small shop you know.</li>
<li>Write the list of things and give each a table name.</li>
<li>Choose a primary key for each table and underline it.</li>
<li>Draw arrows for every foreign key.</li>
<li>Write three questions the database should answer and trace the path through the tables for each.</li>
</ol>

<h3>Common mistakes</h3>
<ul>
<li>Putting two facts in one column, such as "Gulu, Uganda".</li>
<li>Using a changeable value, such as a phone number, as a primary key.</li>
<li>Forgetting the link table for a many-to-many relationship.</li>
</ul>

<h3>Key points</h3>
<ul>
<li>A primary key uniquely identifies a row; a foreign key points to another table's primary key.</li>
<li>Design starts with the things you record, then their facts, then their relationships.</li>
<li>Many-to-many relationships need a link table.</li>
<li>Test the design with real questions before building it.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Setting up a place to practise',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will have a working practice database containing the <code>products</code> table used in Modules 2 and 3. You learn SQL by typing it, so a safe place to experiment is essential.</p>

<h3>Choose a practice tool</h3>
<p>All of these are free and suit this course:</p>
<ul>
<li><strong>A browser-based SQL playground.</strong> This is a web page with a box where you type SQL and see the answer. Nothing is installed, which is ideal on a shared computer. Search for "online SQLite playground" and choose one that clearly says it runs SQLite or MySQL. Your work may be lost when you close the page, so keep your setup script in a text file to paste again.</li>
<li><strong>SQLite with DB Browser for SQLite.</strong> SQLite is a tiny database engine that stores everything in one file. DB Browser is a visual program with an "Execute SQL" tab. Download it only from its official website.</li>
<li><strong>MySQL</strong> (with a tool such as MySQL Workbench, or a local server package). It needs more setup, so choose it only if your tutor or workplace uses it.</li>
</ul>
<p>SQLite and MySQL share most SQL. Whenever they differ you will see a note in the lesson.</p>

<h3>How to run SQL</h3>
<p>You type <strong>statements</strong>. Each statement ends with a semicolon <code>;</code>. Keywords such as SELECT are not case sensitive, but writing them in capitals and table names in lower case makes code easy to read. Lines starting with <code>--</code> are comments that the database ignores.</p>

<h3>Create the products table</h3>
<p>Paste this script into the SQL box and run it. It creates the table and adds eight rows. The word <code>NULL</code> in the Torch row means "no value recorded".</p>
<pre><code>CREATE TABLE products (
  product_id INTEGER PRIMARY KEY,
  name       VARCHAR(60) NOT NULL,
  category   VARCHAR(30) NOT NULL,
  price      INTEGER NOT NULL,
  stock      INTEGER
);

INSERT INTO products (product_id, name, category, price, stock) VALUES
(1, 'Notebook',    'Stationery',  2500,  40),
(2, 'Pen',         'Stationery',   500, 120),
(3, 'Sugar',       'Food',        4500,  30),
(4, 'Rice',        'Food',        5000,  25),
(5, 'Soap',        'Household',   1500,  60),
(6, 'Charger',     'Electronics',12000,   8),
(7, 'Torch',       'Electronics', 8000, NULL),
(8, 'Maize flour', 'Food',        3500,  45);</code></pre>
<p>Prices are in Uganda shillings. Text values go in single quotes, numbers do not.</p>

<h3>Check that it worked</h3>
<p>Run this query:</p>
<pre><code>SELECT COUNT(*) FROM products;</code></pre>
<p>You should see the single number <code>8</code>. If you see an error saying the table already exists, you ran the script twice. Run <code>DROP TABLE products;</code> to remove it, then run the script once more.</p>

<h3>Reading error messages</h3>
<p>Errors are normal. Read the message: it usually names the word or position where the database got confused. The most common causes are a missing comma, an unclosed quote, a misspelt column name and a forgotten semicolon between statements.</p>

<h3>Try it yourself</h3>
<ol>
<li>Choose a tool and open its SQL box.</li>
<li>Paste and run the script above.</li>
<li>Run <code>SELECT * FROM products;</code> and count the rows and columns.</li>
<li>Save the script in a text file called <code>shop-setup.sql</code> so you can rebuild the table any time.</li>
<li>Make a deliberate mistake, such as <code>SELEC * FROM products;</code>, and read the error message.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Free tools exist: browser playgrounds, SQLite with DB Browser, or MySQL.</li>
<li>Every statement ends with a semicolon; text values use single quotes.</li>
<li>Keep your setup script so you can rebuild the practice table.</li>
<li>Error messages are clues, not failures.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: In a database table, what does one row represent?
- One kind of fact about every record
* All the facts about one thing, such as one product
- The name of the table
- The data type of a column
= A row is one record; a column is one kind of fact.

Q: Why is a database usually better than a spreadsheet for a school record system used by many staff at once?
- Databases are always free
- Databases never need planning
* Data types and rules are enforced and many users can work at the same time
- Spreadsheets cannot store text
= Databases are built for shared, large and rule-checked data, though spreadsheets remain good for small personal lists.

Q: A sheet repeats a customer's phone number on every sale. What is the main danger?
- The sheet becomes too colourful
- The numbers get sorted wrongly
- Sales cannot be added
* Changing the number means fixing many rows, and some may be missed
= Repeated facts cause update problems, which is why relational designs store each fact once.

Q: Which column is the best primary key for a table of people?
- Phone number
- Full name
* A system-given number such as person_id
- Home town
= A key must be unique and stable; phone numbers and names change or repeat.

Q: In orders, the column customer_id holds values from the customers table. What is it called?
- A primary key
* A foreign key
- A data type
- A cell
= A foreign key refers to the primary key of a row in another table.

Q: Students take many subjects and each subject has many students. How should this be stored? (choose the best answer)
- Put a long list of subjects in one student column
- Make one table with a column for every possible subject
* Create a link table such as enrolments with student_id and subject_id
- Delete the subjects table
= A many-to-many relationship needs a link table with one row for each pairing.

Q: Which statement is true about SQL statements?
- They must be written in capital letters only
* Each statement normally ends with a semicolon and text values go in single quotes
- Numbers must be written in quotes
- They cannot contain comments
= Keyword case does not matter, but the semicolon and quoting rules do.
QUIZ,
        ],
        [
            'title'   => 'Module 2: Reading data',
            'summary' => 'Ask questions of one table with SELECT, filter with WHERE, sort and limit results, and handle missing values with NULL.',
            'lessons' => [
                [
                    'title'   => 'SELECT, columns and aliases',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose exactly which columns to see, calculate new values and give columns readable names. SELECT is the statement you will write most often in your whole career with data.</p>

<h3>The basic shape</h3>
<p>A query says which columns you want and which table they come from:</p>
<pre><code>SELECT column1, column2 FROM table_name;</code></pre>
<p>The star <code>*</code> means "all columns". Using the <code>products</code> table from the last module:</p>
<pre><code>SELECT * FROM products;</code></pre>
<p>This shows all five columns and all eight rows. For a large table, <code>SELECT *</code> is slow and noisy, so name the columns you really need:</p>
<pre><code>SELECT name, price FROM products;</code></pre>
<pre>name         price
-----------  -----
Notebook      2500
Pen            500
Sugar         4500
Rice          5000
Soap          1500
Charger      12000
Torch         8000
Maize flour   3500</pre>
<p>Notice that the database chose the order of the columns you wrote, not the order in the table. Also, a plain SELECT does not guarantee any row order; if order matters you must ask for it (next lessons).</p>

<h3>Calculated columns</h3>
<p>You can do arithmetic with <code>+</code>, <code>-</code>, <code>*</code> and <code>/</code> inside a query. The stored data does not change; only the result shows the calculation.</p>
<pre><code>SELECT name, price, stock, price * stock FROM products
WHERE category = 'Electronics';</code></pre>
<pre>name     price  stock  price * stock
-------  -----  -----  -------------
Charger  12000      8          96000
Torch     8000   NULL           NULL</pre>
<p>The Torch row shows NULL because arithmetic with a missing value gives a missing value. You will study NULL in a later lesson.</p>

<h3>Aliases with AS</h3>
<p>The heading <code>price * stock</code> is ugly. An <strong>alias</strong> gives a column a friendly name using <code>AS</code>:</p>
<pre><code>SELECT name AS product, price * stock AS stock_value
FROM products
WHERE category = 'Electronics';</code></pre>
<pre>product  stock_value
-------  -----------
Charger        96000
Torch           NULL</pre>
<p>If an alias contains a space, wrap it in double quotes (SQLite) or backticks (MySQL), for example <code>AS "Stock value"</code>. Simple names with underscores avoid the problem. Aliases can also shorten table names, which becomes very useful when joining tables in Module 4: <code>FROM products AS p</code> or just <code>FROM products p</code>.</p>

<h3>Writing readable queries</h3>
<p>Put each clause on its own line and indent continued lines. The database does not care about spaces and line breaks, but your future self will. Add comments with <code>--</code> to explain anything unusual.</p>

<h3>Try it yourself</h3>
<ol>
<li>Run <code>SELECT * FROM products;</code> and confirm you see eight rows.</li>
<li>Write a query that shows only <code>name</code> and <code>category</code>.</li>
<li>Show <code>name</code> and a calculated column called <code>price_in_thousands</code> (price divided by 1000). Note that SQLite divides two whole numbers as whole numbers, so use <code>price / 1000.0</code> to keep decimals.</li>
<li>Try a misspelt column name and read the error.</li>
</ol>

<h3>Common mistakes</h3>
<ul>
<li>Forgetting a comma between column names, or leaving a comma after the last one.</li>
<li>Using double quotes around text values. Text values use single quotes in standard SQL.</li>
<li>Expecting rows in a particular order without asking for one.</li>
</ul>

<h3>Key points</h3>
<ul>
<li>SELECT chooses columns and FROM names the table.</li>
<li>The star means all columns; naming columns is clearer and faster.</li>
<li>Calculations appear only in the result; the stored data is unchanged.</li>
<li>AS gives a column or table a readable alias.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Filtering rows with WHERE',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to pick only the rows you want, using comparisons, AND, OR, IN, BETWEEN and LIKE. Filtering turns a huge table into an answer to a specific question.</p>

<h3>WHERE and comparisons</h3>
<p>WHERE goes after FROM and keeps only rows for which the condition is true.</p>
<pre><code>SELECT name, price FROM products WHERE category = 'Food';</code></pre>
<pre>name         price
-----------  -----
Sugar         4500
Rice          5000
Maize flour   3500</pre>
<p>The comparison operators are <code>=</code>, <code>&lt;&gt;</code> (not equal; <code>!=</code> also works in SQLite and MySQL), <code>&lt;</code>, <code>&gt;</code>, <code>&lt;=</code> and <code>&gt;=</code>. Note that SQL uses a single <code>=</code> to compare.</p>
<pre><code>SELECT name, price FROM products WHERE price &gt;= 8000;</code></pre>
<pre>name     price
-------  -----
Charger  12000
Torch     8000</pre>

<h3>AND, OR and NOT</h3>
<p>Combine conditions. AND needs both to be true; OR needs at least one.</p>
<pre><code>SELECT name, price FROM products
WHERE category = 'Food' AND price &lt; 5000;</code></pre>
<pre>name         price
-----------  -----
Sugar         4500
Maize flour   3500</pre>
<p>AND is worked out before OR. Use brackets so the meaning is clear:</p>
<pre><code>SELECT name FROM products
WHERE (category = 'Food' OR category = 'Household') AND price &lt; 4000;</code></pre>
<pre>name
-----------
Soap
Maize flour</pre>
<p>Without the brackets, the query would mean something different, so always bracket mixed AND/OR.</p>

<h3>IN and BETWEEN</h3>
<p><code>IN</code> checks a list; it is shorter than many ORs.</p>
<pre><code>SELECT name FROM products WHERE category IN ('Food', 'Household');</code></pre>
<pre>name
-----------
Sugar
Rice
Soap
Maize flour</pre>
<p><code>BETWEEN a AND b</code> includes both end values:</p>
<pre><code>SELECT name, price FROM products WHERE price BETWEEN 2000 AND 5000;</code></pre>
<pre>name         price
-----------  -----
Notebook      2500
Sugar         4500
Rice          5000
Maize flour   3500</pre>
<p>Rice costs exactly 5000 and is included.</p>

<h3>LIKE and wildcards</h3>
<p><code>LIKE</code> matches text patterns. The percent sign <code>%</code> stands for any number of characters (including none) and the underscore <code>_</code> stands for exactly one character.</p>
<pre><code>SELECT name FROM products WHERE name LIKE 'S%';</code></pre>
<pre>name
-----
Sugar
Soap</pre>
<pre><code>SELECT name FROM products WHERE name LIKE '%er';</code></pre>
<pre>name
-------
Charger</pre>
<p>In SQLite, LIKE ignores upper and lower case for ordinary English letters. In MySQL it usually does too, but it depends on the column's collation setting. Do not rely on it if capital letters matter.</p>

<h3>Try it yourself</h3>
<ol>
<li>Write a query for products in the Stationery category.</li>
<li>Write a query for products costing less than 3000 and having stock above 50. (Expect Pen and Soap.)</li>
<li>Use IN to show Electronics and Stationery products together.</li>
<li>Use LIKE to find products whose name contains the letters <code>oo</code>. Predict the answer before running it: only Notebook qualifies.</li>
</ol>

<h3>Common mistakes</h3>
<ul>
<li>Writing <code>==</code>. SQL uses a single equals sign.</li>
<li>Putting text in double quotes. Use single quotes.</li>
<li>Mixing AND and OR without brackets.</li>
</ul>

<h3>Key points</h3>
<ul>
<li>WHERE keeps only rows whose condition is true.</li>
<li>Combine tests with AND, OR and brackets.</li>
<li>IN checks a list and BETWEEN includes both ends.</li>
<li>LIKE uses % for many characters and _ for one.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Sorting, limiting and removing duplicates',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to put results in order, show only the top few rows and list each value once. These tools produce the "top 3" lists and tidy summaries you see in real reports.</p>

<h3>ORDER BY</h3>
<p>A table has no built-in order. To sort, add <code>ORDER BY</code> at the end of the query. The default is ascending (smallest first, A to Z). Add <code>DESC</code> for descending.</p>
<pre><code>SELECT name, price FROM products ORDER BY price DESC;</code></pre>
<pre>name         price
-----------  -----
Charger      12000
Torch         8000
Rice          5000
Sugar         4500
Maize flour   3500
Notebook      2500
Soap          1500
Pen            500</pre>
<p>You can sort by several columns. The second column breaks ties in the first.</p>
<pre><code>SELECT category, name, price FROM products
ORDER BY category ASC, price DESC;</code></pre>
<pre>category     name         price
-----------  -----------  -----
Electronics  Charger      12000
Electronics  Torch         8000
Food         Rice          5000
Food         Sugar         4500
Food         Maize flour   3500
Household    Soap          1500
Stationery   Notebook      2500
Stationery   Pen            500</pre>
<p>Within each category the dearest product comes first.</p>

<h3>LIMIT</h3>
<p><code>LIMIT n</code> returns only the first n rows. Combined with ORDER BY it answers "top n" questions. The three most expensive products:</p>
<pre><code>SELECT name, price FROM products ORDER BY price DESC LIMIT 3;</code></pre>
<pre>name     price
-------  -----
Charger  12000
Torch     8000
Rice      5000</pre>
<p>To skip rows, add <code>OFFSET</code>, which is useful for "page 2" of results: <code>LIMIT 3 OFFSET 3</code> returns rows 4 to 6 of the sorted list. LIMIT works in both SQLite and MySQL. Other systems use different words (for example TOP in Microsoft SQL Server), so check the documentation when you change system.</p>

<h3>DISTINCT</h3>
<p>Our table has several products per category. <code>DISTINCT</code> removes repeated rows from the result.</p>
<pre><code>SELECT DISTINCT category FROM products ORDER BY category;</code></pre>
<pre>category
-----------
Electronics
Food
Household
Stationery</pre>
<p>Remember that DISTINCT looks at the whole row of selected columns. <code>SELECT DISTINCT category, price</code> would show every combination of category and price that exists.</p>

<h3>The order of the clauses</h3>
<p>Clauses must appear in this order, though you may leave out the optional ones:</p>
<pre><code>SELECT ... FROM ... WHERE ... ORDER BY ... LIMIT ...;</code></pre>
<p>A helpful way to read a query: first FROM picks the table, WHERE filters rows, SELECT chooses columns, ORDER BY sorts, LIMIT cuts.</p>

<h3>Try it yourself</h3>
<ol>
<li>List all products sorted by name from A to Z.</li>
<li>Show the two cheapest products. Expect Pen then Soap.</li>
<li>List the distinct categories of products costing more than 3000, sorted. Expect Electronics and Food.</li>
<li>Show products in Food sorted by stock from highest to lowest. Expect Maize flour, Sugar, Rice.</li>
</ol>

<h3>Common mistakes</h3>
<ul>
<li>Using LIMIT without ORDER BY and then assuming the rows are the "top" ones.</li>
<li>Putting ORDER BY before WHERE.</li>
<li>Expecting DISTINCT to apply to only the first column.</li>
</ul>

<h3>Key points</h3>
<ul>
<li>ORDER BY sorts; DESC reverses the order; several columns break ties.</li>
<li>LIMIT (with OFFSET) returns a slice of the sorted result.</li>
<li>DISTINCT removes repeated rows.</li>
<li>Clause order is SELECT, FROM, WHERE, ORDER BY, LIMIT.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'NULL: handling missing values',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to find, test and replace missing values correctly. NULL is the most common source of surprising query results, so understanding it early saves many headaches.</p>

<h3>What NULL means</h3>
<p><strong>NULL</strong> means "no value" or "unknown". It is not zero and it is not an empty piece of text. In our table the stock of the Torch is NULL: nobody has counted it yet. If it were zero, we would know that none are left.</p>

<h3>Testing for NULL</h3>
<p>Because NULL is unknown, comparing with it never gives true or false. <code>stock = NULL</code> is never true, even for the Torch. Use <code>IS NULL</code> and <code>IS NOT NULL</code>.</p>
<pre><code>SELECT name FROM products WHERE stock IS NULL;</code></pre>
<pre>name
-----
Torch</pre>
<pre><code>SELECT name FROM products WHERE stock = NULL;</code></pre>
<p>This second query returns no rows at all. It is a silent mistake: no error, just a wrong answer.</p>

<h3>NULL in comparisons</h3>
<p>Rows where the test is unknown are left out. Which products have low stock?</p>
<pre><code>SELECT name, stock FROM products WHERE stock &lt; 30 ORDER BY stock;</code></pre>
<pre>name     stock
-------  -----
Charger      8
Rice        25</pre>
<p>The Torch is missing. Is that what you want? If the question is "which products might need re-ordering?", the Torch should be included because we do not know its stock. Say so in the query:</p>
<pre><code>SELECT name, stock FROM products
WHERE stock &lt; 30 OR stock IS NULL
ORDER BY stock;</code></pre>
<p>In SQLite, NULL sorts first in ascending order. In MySQL it also sorts first when ascending. Other systems differ, so do not rely on it.</p>

<h3>Replacing NULL with COALESCE</h3>
<p><code>COALESCE(a, b)</code> returns the first value that is not NULL. It is available in SQLite and MySQL.</p>
<pre><code>SELECT name, COALESCE(stock, 0) AS stock_or_zero
FROM products WHERE category = 'Electronics';</code></pre>
<pre>name     stock_or_zero
-------  -------------
Charger              8
Torch                0</pre>
<p>Only do this when treating "unknown" as zero is honest for your purpose. Often it is better to leave NULL and report that some data is missing.</p>

<h3>Allowing and forbidding NULL</h3>
<p>When you create a table you decide, column by column, whether NULL is allowed. In our table <code>name</code> is declared <code>NOT NULL</code> so every product must have a name, while <code>stock</code> allows NULL. You will meet this again in Module 5.</p>

<h3>Try it yourself</h3>
<ol>
<li>List products whose stock is not NULL. You should see seven rows.</li>
<li>Run <code>SELECT name FROM products WHERE stock &gt; 0;</code>. Does the Torch appear? (No.)</li>
<li>Write a query using COALESCE that shows each product name and its stock, with -1 for missing stock.</li>
<li>Explain in one sentence why <code>stock = NULL</code> fails.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>NULL means unknown or missing; it is not zero or empty text.</li>
<li>Use IS NULL and IS NOT NULL, never <code>= NULL</code>.</li>
<li>Rows where a test is unknown are excluded from WHERE results.</li>
<li>COALESCE supplies a replacement for NULL when that is honest.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which query shows only the name and price columns of products?
- SELECT * FROM products;
* SELECT name, price FROM products;
- SELECT name price FROM products;
- GET name, price FROM products;
= List the wanted columns separated by commas after SELECT.

Q: What is the purpose of AS in SELECT price * stock AS stock_value?
- It multiplies the numbers
- It changes the stored data
* It gives the result column a readable alias
- It sorts the result
= An alias only renames the column in the result.

Q: Which query lists Food products costing less than 5000?
- SELECT name FROM products WHERE category = 'Food' OR price < 5000;
- SELECT name FROM products WHERE category == Food AND price < 5000;
* SELECT name FROM products WHERE category = 'Food' AND price < 5000;
- SELECT name FROM products WHERE category = "Food" price < 5000;
= AND needs both tests true, text uses single quotes and a single equals sign is used to compare.

Q: What does name LIKE 'S%' match? (choose all that apply)
* Sugar
* Soap
- Notebook
- Charger
= The pattern means text starting with S followed by anything.

Q: Which query returns the three most expensive products?
- SELECT name FROM products LIMIT 3 ORDER BY price;
- SELECT name FROM products ORDER BY price LIMIT 3;
* SELECT name, price FROM products ORDER BY price DESC LIMIT 3;
- SELECT TOP name FROM products WHERE price DESC;
= Sort from highest to lowest first, then cut with LIMIT; ORDER BY comes before LIMIT.

Q: A student runs SELECT name FROM products WHERE stock = NULL; and gets no rows although some stock values are missing. Why?
- The table is empty
- NULL must be written in quotes
* Comparing with NULL is never true; IS NULL should be used
- stock cannot be tested
= NULL means unknown, so only IS NULL or IS NOT NULL can test for it.

Q: What does SELECT DISTINCT category FROM products return?
- Every row of the table
* Each category value listed once
- The number of categories
- The most expensive category
= DISTINCT removes repeated rows from the result.

Q: Which operator includes both end values?
- price &gt; 2000 AND price &lt; 5000
* price BETWEEN 2000 AND 5000
- price IN (2000, 5000)
- price LIKE 2000
= BETWEEN is inclusive at both ends.
QUIZ,
        ],
        [
            'title'   => 'Module 3: Summarising data',
            'summary' => 'Turn many rows into answers with aggregate functions, group rows with GROUP BY, filter groups with HAVING and label values with CASE.',
            'lessons' => [
                [
                    'title'   => 'Aggregate functions: COUNT, SUM, AVG, MIN and MAX',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to turn a whole column into a single answer such as a total, an average or a count. Managers rarely want every row; they want the numbers that summarise them.</p>

<h3>The five aggregate functions</h3>
<p>An <strong>aggregate function</strong> reads many rows and returns one value.</p>
<table>
<thead><tr><th>Function</th><th>Returns</th></tr></thead>
<tbody>
<tr><td><code>COUNT(*)</code></td><td>Number of rows</td></tr>
<tr><td><code>COUNT(column)</code></td><td>Number of rows where the column is not NULL</td></tr>
<tr><td><code>SUM(column)</code></td><td>Total of the values</td></tr>
<tr><td><code>AVG(column)</code></td><td>Average (mean)</td></tr>
<tr><td><code>MIN(column)</code>, <code>MAX(column)</code></td><td>Smallest and largest value</td></tr>
</tbody>
</table>

<h3>Examples on the products table</h3>
<pre><code>SELECT COUNT(*) AS products,
       COUNT(stock) AS with_stock_recorded,
       SUM(stock) AS total_stock
FROM products;</code></pre>
<pre>products  with_stock_recorded  total_stock
--------  --------------------  -----------
       8                     7          328</pre>
<p>There are 8 rows, but only 7 have a stock value because the Torch's stock is NULL. Aggregates other than <code>COUNT(*)</code> skip NULL values. The total is 40 + 120 + 30 + 25 + 60 + 8 + 45 = 328.</p>
<pre><code>SELECT MIN(price) AS cheapest, MAX(price) AS dearest, AVG(price) AS average_price
FROM products;</code></pre>
<pre>cheapest  dearest  average_price
--------  -------  -------------
     500    12000         4687.5</pre>
<p>The prices add up to 37500 and 37500 divided by 8 is 4687.5. MySQL may print 4687.5000 because it shows more decimal places for averages.</p>

<h3>Rounding</h3>
<p>Use <code>ROUND(value, places)</code> to tidy decimals. It works in both systems:</p>
<pre><code>SELECT ROUND(AVG(price), 0) AS average_price FROM products;</code></pre>
<p>This gives 4688 (SQLite may show it as 4688.0). Rounding 4687.5 to the nearest whole number gives 4688.</p>

<h3>Aggregates with WHERE</h3>
<p>WHERE runs first, so the aggregate only sees the rows that passed the filter:</p>
<pre><code>SELECT SUM(stock) AS food_stock FROM products WHERE category = 'Food';</code></pre>
<pre>food_stock
----------
       100</pre>
<p>That is 30 + 25 + 45.</p>

<h3>One row only</h3>
<p>A query with aggregates and no GROUP BY returns exactly one row. Do not mix an aggregate with an ordinary column unless you use GROUP BY (next lesson). Some databases give an error; SQLite quietly picks an arbitrary row, which is worse because the answer looks fine but is meaningless.</p>

<h3>COUNT DISTINCT</h3>
<p>To count different values, put DISTINCT inside:</p>
<pre><code>SELECT COUNT(DISTINCT category) AS categories FROM products;</code></pre>
<pre>categories
----------
         4</pre>

<h3>Try it yourself</h3>
<ol>
<li>Count how many products cost more than 3000. (The answer is 5.)</li>
<li>Find the total of the price column. (37500.)</li>
<li>Find the cheapest and dearest product prices among Food items. (3500 and 5000.)</li>
<li>Run <code>SELECT COUNT(*), COUNT(stock) FROM products;</code> and explain the difference in your own words.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Aggregate functions turn many rows into one value.</li>
<li>COUNT(*) counts rows; COUNT(column) skips NULLs; SUM, AVG, MIN and MAX also ignore NULLs.</li>
<li>WHERE filters rows before the aggregate is calculated.</li>
<li>Use ROUND to display tidy numbers.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'GROUP BY and HAVING',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to produce a summary per group, for example total stock per category, and to keep only the groups that meet a condition. This is the query behind most business reports.</p>

<h3>GROUP BY</h3>
<p>GROUP BY splits the rows into groups that share a value and applies the aggregate to each group separately.</p>
<pre><code>SELECT category, COUNT(*) AS products
FROM products
GROUP BY category
ORDER BY category;</code></pre>
<pre>category     products
-----------  --------
Electronics         2
Food                3
Household           1
Stationery          2</pre>
<p>You get one row per category. Every plain column in SELECT must also appear in GROUP BY (or be inside an aggregate). This rule keeps results meaningful.</p>

<h3>More than one aggregate</h3>
<pre><code>SELECT category,
       COUNT(*)   AS products,
       SUM(stock) AS total_stock,
       AVG(price) AS average_price
FROM products
GROUP BY category
ORDER BY category;</code></pre>
<pre>category     products  total_stock  average_price
-----------  --------  -----------  -------------
Electronics         2            8        10000.0
Food                3          100        4333.333333333333
Household           1           60         1500.0
Stationery          2          160         1500.0</pre>
<p>Electronics total stock is 8 because the Torch's NULL is ignored. The exact number of decimals printed depends on the system; wrap AVG in ROUND to control it.</p>

<h3>Calculating inside a group</h3>
<p>You can aggregate a calculation. The money tied up in stock per category:</p>
<pre><code>SELECT category, SUM(price * stock) AS stock_value
FROM products
GROUP BY category
ORDER BY stock_value DESC;</code></pre>
<pre>category     stock_value
-----------  -----------
Food              417500
Stationery        160000
Electronics        96000
Household          90000</pre>
<p>For Food: 4500 x 30 = 135000, 5000 x 25 = 125000, 3500 x 45 = 157500; the total is 417500.</p>

<h3>HAVING: filtering groups</h3>
<p>WHERE filters rows before grouping, so it cannot use an aggregate. To filter groups after they are made, use <code>HAVING</code>:</p>
<pre><code>SELECT category, COUNT(*) AS products
FROM products
GROUP BY category
HAVING COUNT(*) &gt;= 2
ORDER BY category;</code></pre>
<pre>category     products
-----------  --------
Electronics         2
Food                3
Stationery          2</pre>
<p>Household has only one product, so its group is removed.</p>

<h3>WHERE and HAVING together</h3>
<p>You can use both. WHERE trims rows first, then groups are built, then HAVING trims groups:</p>
<pre><code>SELECT category, ROUND(AVG(price), 0) AS average_price
FROM products
WHERE price &gt; 1000
GROUP BY category
HAVING AVG(price) &gt; 4000
ORDER BY category;</code></pre>
<pre>category     average_price
-----------  -------------
Electronics          10000
Food                  4333</pre>
<p>(SQLite may show 10000.0 and 4333.0.) Pen is excluded by WHERE; Stationery then has only the Notebook with average 2500, which fails HAVING.</p>

<h3>The full order of a query</h3>
<pre><code>SELECT ... FROM ... WHERE ... GROUP BY ... HAVING ... ORDER BY ... LIMIT ...;</code></pre>

<h3>Try it yourself</h3>
<ol>
<li>Show the highest price in each category.</li>
<li>Show how many products are in each category, only for categories with more than one product.</li>
<li>Show the total stock per category, sorted from largest to smallest. (Stationery 160, Food 100, Household 60, Electronics 8.)</li>
<li>Explain why <code>WHERE COUNT(*) &gt; 1</code> would give an error.</li>
</ol>

<h3>Common mistakes</h3>
<ul>
<li>Using WHERE where HAVING is needed, or the other way round.</li>
<li>Selecting a plain column that is not in GROUP BY.</li>
<li>Forgetting that NULL values are skipped by SUM and AVG.</li>
</ul>

<h3>Key points</h3>
<ul>
<li>GROUP BY makes one result row per group.</li>
<li>WHERE filters rows before grouping; HAVING filters groups after.</li>
<li>Plain columns in SELECT must be in GROUP BY.</li>
<li>Clause order: WHERE, GROUP BY, HAVING, ORDER BY, LIMIT.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'CASE: labelling and conditional totals',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to create labels such as "Cheap" or "Premium" from numbers, and to count or add up only the rows that meet a condition. CASE is SQL's version of if, then, else.</p>

<h3>Simple CASE expressions</h3>
<p>A <code>CASE</code> expression checks conditions from top to bottom and returns the result of the first one that is true. If none is true it returns the ELSE value (or NULL if there is no ELSE).</p>
<pre><code>SELECT name, price,
       CASE
         WHEN price &lt; 2000 THEN 'Cheap'
         WHEN price &lt; 6000 THEN 'Medium'
         ELSE 'Premium'
       END AS price_band
FROM products
ORDER BY price;</code></pre>
<pre>name         price  price_band
-----------  -----  ----------
Pen            500  Cheap
Soap          1500  Cheap
Notebook      2500  Medium
Maize flour   3500  Medium
Sugar         4500  Medium
Rice          5000  Medium
Torch         8000  Premium
Charger      12000  Premium</pre>
<p>Order matters. A price of 500 satisfies both the first and second test, but only the first answer is used. Every CASE must end with <code>END</code>.</p>

<h3>Counting by label</h3>
<p>You can group by a CASE expression to count products per band. Both SQLite and MySQL allow the alias in GROUP BY, although some other systems require you to repeat the whole CASE.</p>
<pre><code>SELECT CASE
         WHEN price &lt; 2000 THEN 'Cheap'
         WHEN price &lt; 6000 THEN 'Medium'
         ELSE 'Premium'
       END AS price_band,
       COUNT(*) AS products
FROM products
GROUP BY price_band
ORDER BY products DESC, price_band;</code></pre>
<pre>price_band  products
----------  --------
Medium             4
Cheap              2
Premium            2</pre>
<p>Cheap and Premium tie at 2, so the second sort key, the band name, puts Cheap first.</p>

<h3>Conditional totals: CASE inside an aggregate</h3>
<p>A powerful pattern is to put CASE inside SUM or COUNT to build several columns in one pass. Here we count how many products are Food and how many are not:</p>
<pre><code>SELECT SUM(CASE WHEN category = 'Food' THEN 1 ELSE 0 END) AS food_items,
       SUM(CASE WHEN category &lt;&gt; 'Food' THEN 1 ELSE 0 END) AS other_items
FROM products;</code></pre>
<pre>food_items  other_items
----------  -----------
         3            5</pre>
<p>Each row adds 1 or 0, so the SUM becomes a count. This is how many "cross-tab" style reports are built in SQL.</p>

<h3>Putting it together: a stock report</h3>
<p>Combine everything from this module. Which categories have low or unknown stock?</p>
<pre><code>SELECT category,
       SUM(CASE WHEN stock IS NULL THEN 1 ELSE 0 END) AS stock_unknown,
       SUM(CASE WHEN stock &lt; 30 THEN 1 ELSE 0 END)    AS low_stock
FROM products
GROUP BY category
ORDER BY category;</code></pre>
<pre>category     stock_unknown  low_stock
-----------  -------------  ---------
Electronics              1          1
Food                     0          1
Household                0          0
Stationery               0          0</pre>
<p>Electronics has the Torch (unknown) and the Charger (stock 8, low). Food has Rice (stock 25). For the Torch, <code>stock &lt; 30</code> is unknown, so that row adds 0 to the low-stock total through the ELSE branch.</p>

<h3>Try it yourself</h3>
<ol>
<li>Write a CASE that labels stock as 'Low' (under 30), 'OK' (30 to 59) or 'High' (60 and above), and treats NULL as 'Unknown' by testing IS NULL first.</li>
<li>Use it to show each product's name and stock level.</li>
<li>Count how many products fall in each stock level.</li>
<li>Describe, in words, why the order of WHEN lines matters.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>CASE returns the result of the first true WHEN, or the ELSE value.</li>
<li>End every CASE with END and give it an alias.</li>
<li>CASE inside SUM or COUNT builds conditional totals.</li>
<li>Test IS NULL before comparisons if NULL needs its own label.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A table has 10 rows. In one column, 3 values are NULL. What does COUNT(column) return for that column?
- 10
- 3
* 7
- 0
= COUNT(column) skips NULL values, while COUNT(*) would return 10.

Q: Which function gives the average of a numeric column?
- MEAN()
- TOTAL()
* AVG()
- MID()
= AVG is the standard aggregate for the average.

Q: Which query returns the number of products in each category?
- SELECT category, COUNT(*) FROM products;
* SELECT category, COUNT(*) FROM products GROUP BY category;
- SELECT COUNT(category) FROM products WHERE category;
- SELECT GROUP category FROM products;
= Plain columns with an aggregate need GROUP BY.

Q: You want only the categories that have at least 2 products. Which clause do you use after GROUP BY?
- WHERE COUNT(*) >= 2
* HAVING COUNT(*) >= 2
- LIMIT 2
- ORDER BY COUNT(*) >= 2
= HAVING filters groups after aggregation; WHERE cannot use aggregates.

Q: What is the correct order of clauses? (choose the best answer)
- SELECT, WHERE, FROM, GROUP BY
- FROM, SELECT, HAVING, WHERE
* SELECT, FROM, WHERE, GROUP BY, HAVING, ORDER BY, LIMIT
- SELECT, GROUP BY, FROM, ORDER BY, WHERE
= SQL requires clauses to be written in this order.

Q: In a CASE expression, what happens if a value satisfies two WHEN conditions?
- An error is shown
- Both results are returned
* The result of the first true WHEN is used
- The ELSE value is used
= CASE checks from top to bottom and stops at the first true condition.

Q: What does SUM(CASE WHEN category = 'Food' THEN 1 ELSE 0 END) calculate?
- The total price of Food products
* The number of Food products
- The number of categories
- The largest Food price
= Each Food row adds 1 and every other row adds 0, so the total is a count.
QUIZ,
        ],
        [
            'title'   => 'Module 4: Working with several tables',
            'summary' => 'Link tables with keys, combine them with INNER JOIN and LEFT JOIN, handle many-to-many relationships and answer questions with subqueries.',
            'lessons' => [
                [
                    'title'   => 'Keys and relationships in practice: the shop database',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will have a four-table shop database (products, customers, orders and order items) and understand how its keys connect the tables. You need this data to practise joins in the rest of the module.</p>

<h3>The design</h3>
<p>You already have <code>products</code>. Now add three more tables.</p>
<ul>
<li><code>customers</code>: one row per customer.</li>
<li><code>orders</code>: one row per purchase, pointing to one customer (one-to-many).</li>
<li><code>order_items</code>: one row per product on an order; it links orders and products (many-to-many) and stores the quantity.</li>
</ul>
<pre><code>customers   (customer_id PK, name, town)
orders      (order_id PK, customer_id FK -&gt; customers, order_date)
order_items (order_id FK -&gt; orders, product_id FK -&gt; products, quantity)
            primary key = (order_id, product_id)</code></pre>
<p>The primary key of <code>order_items</code> is the pair of columns, called a <strong>composite key</strong>: the same product cannot appear twice on the same order, but it can appear on different orders.</p>

<h3>The setup script</h3>
<p>Run this after your <code>products</code> script. It works in SQLite and MySQL.</p>
<pre><code>CREATE TABLE customers (
  customer_id INTEGER PRIMARY KEY,
  name        VARCHAR(80) NOT NULL,
  town        VARCHAR(50)
);
INSERT INTO customers (customer_id, name, town) VALUES
(1, 'Akello Grace',  'Gulu'),
(2, 'Okello Peter',  'Gulu'),
(3, 'Nakato Sarah',  'Kampala'),
(4, 'Mugisha David', 'Mbarara');

CREATE TABLE orders (
  order_id    INTEGER PRIMARY KEY,
  customer_id INTEGER NOT NULL,
  order_date  DATE NOT NULL,
  FOREIGN KEY (customer_id) REFERENCES customers (customer_id)
);
INSERT INTO orders (order_id, customer_id, order_date) VALUES
(101, 1, '2025-03-01'),
(102, 1, '2025-03-05'),
(103, 3, '2025-03-06'),
(104, 2, '2025-03-09');

CREATE TABLE order_items (
  order_id   INTEGER NOT NULL,
  product_id INTEGER NOT NULL,
  quantity   INTEGER NOT NULL,
  PRIMARY KEY (order_id, product_id),
  FOREIGN KEY (order_id)   REFERENCES orders (order_id),
  FOREIGN KEY (product_id) REFERENCES products (product_id)
);
INSERT INTO order_items (order_id, product_id, quantity) VALUES
(101, 1, 2),
(101, 2, 5),
(102, 3, 1),
(103, 6, 1),
(103, 5, 3),
(104, 4, 2);</code></pre>
<p>Dates are written as text in the form <code>YYYY-MM-DD</code>, which sorts correctly and works in both systems. In SQLite, foreign key rules are only enforced after you run <code>PRAGMA foreign_keys = ON;</code> in each session. MySQL enforces them for its normal InnoDB tables.</p>

<h3>Reading the data</h3>
<p>Look at order 101: it belongs to customer 1 (Akello Grace) and has two lines, 2 notebooks (product 1) and 5 pens (product 2). Order 103 belongs to customer 3 and has a charger and 3 soaps. Mugisha David (customer 4) has no orders yet. Keep that detail in mind; it matters in the LEFT JOIN lesson.</p>

<h3>Why numbers instead of names?</h3>
<p>Orders store <code>customer_id</code>, not the customer's name. If Akello Grace changes her name or town, you change one row in <code>customers</code> and every order automatically shows the new details.</p>

<h3>Try it yourself</h3>
<ol>
<li>Run the script above after the products script.</li>
<li>Run <code>SELECT * FROM order_items;</code> and confirm six rows.</li>
<li>On paper, list which customer placed order 104 and what they bought, using only the three tables and your eyes. (Okello Peter, 2 bags of rice.)</li>
<li>Draw the four tables as boxes and draw an arrow for each foreign key.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>One-to-many: orders hold a foreign key to customers.</li>
<li>Many-to-many: order_items links orders and products and stores the quantity.</li>
<li>A composite primary key uses two or more columns together.</li>
<li>Store each fact once and refer to it by key.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'INNER JOIN: combining tables',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a query that pulls related information from two or three tables into one result. Joins are the heart of relational databases.</p>

<h3>The idea of a join</h3>
<p>A <strong>join</strong> matches rows of one table to rows of another using a condition, almost always "the foreign key equals the primary key". The syntax uses <code>JOIN ... ON</code>.</p>
<pre><code>SELECT c.name, o.order_id, o.order_date
FROM customers AS c
INNER JOIN orders AS o ON o.customer_id = c.customer_id
ORDER BY o.order_id;</code></pre>
<pre>name          order_id  order_date
------------  --------  ----------
Akello Grace       101  2025-03-01
Akello Grace       102  2025-03-05
Nakato Sarah       103  2025-03-06
Okello Peter       104  2025-03-09</pre>
<p>Let us read it. <code>c</code> and <code>o</code> are short aliases for the tables. The <code>ON</code> line says an order matches a customer when their customer numbers are equal. Prefixes like <code>c.name</code> tell the database which table a column comes from; they are required when both tables have a column with the same name, such as <code>customer_id</code>.</p>

<h3>What INNER JOIN keeps</h3>
<p>An INNER JOIN returns only rows that have a match on both sides. Mugisha David has no orders, so he does not appear. Writing just <code>JOIN</code> means the same as <code>INNER JOIN</code>.</p>

<h3>Joining three tables</h3>
<p>To show what was bought, link <code>order_items</code> to <code>products</code>. Each join adds one more table:</p>
<pre><code>SELECT oi.order_id, p.name, oi.quantity, oi.quantity * p.price AS line_total
FROM order_items AS oi
INNER JOIN products AS p ON p.product_id = oi.product_id
ORDER BY oi.order_id, p.name;</code></pre>
<pre>order_id  name      quantity  line_total
--------  --------  --------  ----------
     101  Notebook         2        5000
     101  Pen              5        2500
     102  Sugar            1        4500
     103  Charger          1       12000
     103  Soap             3        4500
     104  Rice             2       10000</pre>
<p>Now bring in the customer's name too, by chaining joins through orders:</p>
<pre><code>SELECT c.name AS customer, o.order_id, p.name AS product, oi.quantity
FROM customers AS c
INNER JOIN orders      AS o  ON o.customer_id = c.customer_id
INNER JOIN order_items AS oi ON oi.order_id   = o.order_id
INNER JOIN products    AS p  ON p.product_id  = oi.product_id
WHERE c.town = 'Gulu'
ORDER BY o.order_id, p.name;</code></pre>
<pre>customer      order_id  product   quantity
------------  --------  --------  --------
Akello Grace       101  Notebook         2
Akello Grace       101  Pen              5
Akello Grace       102  Sugar            1
Okello Peter       104  Rice             2</pre>
<p>Only customers from Gulu remain; Nakato Sarah is filtered out by WHERE.</p>

<h3>Joins with GROUP BY</h3>
<p>Joins and aggregates combine well. The total value of each order:</p>
<pre><code>SELECT oi.order_id, SUM(oi.quantity * p.price) AS order_total
FROM order_items AS oi
INNER JOIN products AS p ON p.product_id = oi.product_id
GROUP BY oi.order_id
ORDER BY oi.order_id;</code></pre>
<pre>order_id  order_total
--------  -----------
     101         7500
     102         4500
     103        16500
     104        10000</pre>
<p>Order 101 is 5000 + 2500 = 7500, and order 103 is 12000 + 4500 = 16500.</p>

<h3>Try it yourself</h3>
<ol>
<li>List each order with the customer's town. Expect four rows.</li>
<li>List the products bought on order 103 with their quantities.</li>
<li>Work out the total spent by each customer who has orders. (Akello Grace 12000, Okello Peter 10000, Nakato Sarah 16500.)</li>
<li>Remove the ON line from a join and see what happens: you may get every combination of rows. Always include ON.</li>
</ol>

<h3>Common mistakes</h3>
<ul>
<li>Forgetting the ON condition, which produces a huge, meaningless result.</li>
<li>Not prefixing a column that exists in two tables, giving an "ambiguous column" error.</li>
<li>Expecting customers with no orders to appear in an INNER JOIN.</li>
</ul>

<h3>Key points</h3>
<ul>
<li>JOIN ... ON matches rows using a foreign key equal to a primary key.</li>
<li>INNER JOIN keeps only rows that match on both sides.</li>
<li>Chain several joins to reach several tables.</li>
<li>Joins combine naturally with WHERE, GROUP BY and ORDER BY.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'LEFT JOIN and many-to-many questions',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to keep all rows from one table even when there is no match in the other, and to find "who has not done something". These questions, such as "which customers have never ordered?", are very common at work.</p>

<h3>LEFT JOIN</h3>
<p>A <code>LEFT JOIN</code> keeps every row from the left table (the one named after FROM). Where there is no match on the right, the right-side columns are filled with NULL.</p>
<pre><code>SELECT c.name, o.order_id
FROM customers AS c
LEFT JOIN orders AS o ON o.customer_id = c.customer_id
ORDER BY c.customer_id, o.order_id;</code></pre>
<pre>name           order_id
-------------  --------
Akello Grace        101
Akello Grace        102
Okello Peter        104
Nakato Sarah        103
Mugisha David      NULL</pre>
<p>Mugisha David now appears, with NULL because he has no order. Compare this with the INNER JOIN of the last lesson.</p>

<h3>Counting with LEFT JOIN</h3>
<p>To count orders per customer, including zero, count a column from the right table. <code>COUNT(o.order_id)</code> skips NULL, so a customer with no orders gets 0. (<code>COUNT(*)</code> would wrongly give 1 for him.)</p>
<pre><code>SELECT c.name, COUNT(o.order_id) AS orders
FROM customers AS c
LEFT JOIN orders AS o ON o.customer_id = c.customer_id
GROUP BY c.customer_id, c.name
ORDER BY c.customer_id;</code></pre>
<pre>name           orders
-------------  ------
Akello Grace        2
Okello Peter        1
Nakato Sarah        1
Mugisha David       0</pre>

<h3>Finding rows with no match</h3>
<p>Add a test for NULL on the right side to keep only the customers without orders:</p>
<pre><code>SELECT c.name, c.town
FROM customers AS c
LEFT JOIN orders AS o ON o.customer_id = c.customer_id
WHERE o.order_id IS NULL;</code></pre>
<pre>name           town
-------------  -------
Mugisha David  Mbarara</pre>
<p>This pattern is sometimes called an "anti-join". It answers "which ... have no ...?".</p>

<h3>Left and right matter</h3>
<p>Swap the tables and the result changes. <code>FROM orders LEFT JOIN customers</code> keeps every order, which in this database is the same as the inner join because every order has a customer. Most people write LEFT JOIN and put the "main" table first. SQLite supports RIGHT JOIN and FULL JOIN only in recent versions, and MySQL has no FULL JOIN, so using LEFT JOIN with a swapped order is the portable habit.</p>

<h3>Many-to-many in action</h3>
<p>Because <code>order_items</code> links orders and products, you can ask questions from either direction. Which customers have bought Soap?</p>
<pre><code>SELECT DISTINCT c.name
FROM products AS p
INNER JOIN order_items AS oi ON oi.product_id = p.product_id
INNER JOIN orders      AS o  ON o.order_id    = oi.order_id
INNER JOIN customers   AS c  ON c.customer_id = o.customer_id
WHERE p.name = 'Soap';</code></pre>
<pre>name
------------
Nakato Sarah</pre>
<p>And how many units of each product have been sold, including products never sold?</p>
<pre><code>SELECT p.name, COALESCE(SUM(oi.quantity), 0) AS units_sold
FROM products AS p
LEFT JOIN order_items AS oi ON oi.product_id = p.product_id
GROUP BY p.product_id, p.name
ORDER BY units_sold DESC, p.name;</code></pre>
<pre>name         units_sold
-----------  ----------
Pen                   5
Soap                  3
Notebook              2
Rice                  2
Charger               1
Sugar                 1
Maize flour           0
Torch                 0</pre>
<p>Torch and Maize flour have no rows in <code>order_items</code>, so SUM gives NULL and COALESCE turns it into 0.</p>

<h3>Try it yourself</h3>
<ol>
<li>List every product and the number of different orders it appears on, including zero.</li>
<li>List customers from Gulu with the number of orders each has.</li>
<li>Find products that were never ordered using the anti-join pattern. (Torch and Maize flour.)</li>
<li>Explain why COUNT(*) is wrong in the LEFT JOIN count above.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>LEFT JOIN keeps all rows from the left table and fills missing matches with NULL.</li>
<li>Count a right-table column to get zeros right.</li>
<li>WHERE right_key IS NULL finds rows that have no match.</li>
<li>Link tables let you ask many-to-many questions from either side.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Subqueries',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to put one query inside another to answer questions that need two steps, such as "which products cost more than the average?". Subqueries let you build answers in small, readable pieces.</p>

<h3>A subquery that returns one value</h3>
<p>A <strong>subquery</strong> is a SELECT inside brackets. If it returns a single value, you can use it like a number in a WHERE clause. Products priced above the average price:</p>
<pre><code>SELECT name, price
FROM products
WHERE price &gt; (SELECT AVG(price) FROM products)
ORDER BY price;</code></pre>
<pre>name     price
-------  -----
Rice      5000
Torch     8000
Charger  12000</pre>
<p>The inner query runs first and gives 4687.5. The outer query then keeps prices above 4687.5. You cannot write <code>WHERE price &gt; AVG(price)</code> directly because WHERE cannot use aggregates; the subquery solves that.</p>

<h3>A subquery that returns a list</h3>
<p>With <code>IN</code> a subquery may return many values. Products that appear on at least one order:</p>
<pre><code>SELECT name
FROM products
WHERE product_id IN (SELECT product_id FROM order_items)
ORDER BY product_id;</code></pre>
<pre>name
--------
Notebook
Pen
Sugar
Rice
Soap
Charger</pre>
<p>And the opposite, products never ordered, using <code>NOT IN</code>:</p>
<pre><code>SELECT name
FROM products
WHERE product_id NOT IN (SELECT product_id FROM order_items)
ORDER BY product_id;</code></pre>
<pre>name
-----------
Torch
Maize flour</pre>
<p>Warning: if the inner list contains even one NULL, <code>NOT IN</code> returns no rows at all. Here <code>product_id</code> in <code>order_items</code> can never be NULL, so it is safe. When in doubt, use the LEFT JOIN anti-join pattern from the last lesson, which is not affected.</p>

<h3>Subqueries in the FROM clause</h3>
<p>A subquery can also act as a temporary table. Here we first total each order and then find the average of those totals:</p>
<pre><code>SELECT AVG(order_total) AS average_order
FROM (
  SELECT oi.order_id, SUM(oi.quantity * p.price) AS order_total
  FROM order_items AS oi
  INNER JOIN products AS p ON p.product_id = oi.product_id
  GROUP BY oi.order_id
) AS totals;</code></pre>
<pre>average_order
-------------
       9625.0</pre>
<p>The totals are 7500, 4500, 16500 and 10000, which add to 38500; divided by 4 gives 9625. In MySQL a subquery in FROM must always have an alias (here <code>totals</code>), and it is good practice in SQLite too.</p>

<h3>Subquery or join?</h3>
<p>Often both work. Joins are usually the better choice when you need columns from both tables. Subqueries are clearer when you only need to filter by a calculated value or by membership in a list. Choose the version you can read most easily; modern databases usually run both well.</p>

<h3>Try it yourself</h3>
<ol>
<li>List products cheaper than the average price. (Notebook, Pen, Soap, Sugar and Maize flour: sugar is 4500, below 4687.5.)</li>
<li>List customers who have placed at least one order, using IN with a subquery. (Akello Grace, Okello Peter, Nakato Sarah.)</li>
<li>Find the most expensive product using <code>WHERE price = (SELECT MAX(price) FROM products)</code>.</li>
<li>Rewrite the "never ordered" query with a LEFT JOIN and compare the result.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A subquery is a SELECT in brackets; the inner query runs first.</li>
<li>Single-value subqueries work with comparison operators; list subqueries work with IN.</li>
<li>NOT IN fails silently when the list contains NULL.</li>
<li>A subquery in FROM acts as a temporary table and needs an alias.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does an INNER JOIN between customers and orders return?
- All customers, even those with no orders
* Only customers who have at least one matching order
- Only customers with no orders
- All possible combinations of rows
= INNER JOIN keeps only rows that match on both sides.

Q: Which is the correct way to join orders to customers?
- FROM orders JOIN customers WHERE customer_id
* FROM orders o JOIN customers c ON o.customer_id = c.customer_id
- FROM orders, customers ON customer_id
- JOIN orders AND customers
= JOIN needs an ON condition that matches a foreign key with a primary key.

Q: You need every customer listed, including those with no orders. Which join do you use?
- INNER JOIN with customers on the right
* LEFT JOIN with customers on the left
- JOIN with no ON
- UNION
= LEFT JOIN keeps all left-table rows and fills missing matches with NULL.

Q: In a LEFT JOIN count, why use COUNT(o.order_id) instead of COUNT(*)?
- COUNT(*) is not allowed
- It is faster
* COUNT(column) ignores NULL, so a customer with no orders gets 0 instead of 1
- It removes duplicates
= The unmatched row exists, so COUNT(*) would count it, but its order_id is NULL.

Q: Which statements about order_items in our shop are true? (choose all that apply)
* It links orders and products
* It stores the quantity of each product on an order
- It stores customer names
- It is the same as the products table
= It is the link table that handles the many-to-many relationship and holds the pair-specific quantity.

Q: Which query finds products priced above the average price?
- SELECT name FROM products WHERE price > AVG(price);
* SELECT name FROM products WHERE price > (SELECT AVG(price) FROM products);
- SELECT AVG(price) > name FROM products;
- SELECT name FROM products HAVING price > AVG;
= An aggregate cannot be used directly in WHERE, so a subquery supplies the average.

Q: What is the risk when NOT IN is used with a subquery that can return NULL?
- The query becomes slower only
* The query may return no rows at all
- The query returns every row twice
- The database deletes the NULLs
= A NULL in the list makes every NOT IN test unknown, so no row qualifies.
QUIZ,
        ],
        [
            'title'   => 'Module 5: Changing data and good habits',
            'summary' => 'Create tables with the right data types and constraints, insert, update and delete safely, understand indexes, backups and SQL injection, and finish with a library project.',
            'lessons' => [
                [
                    'title'   => 'CREATE TABLE, data types and constraints',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to design and create your own table, choosing sensible data types and adding rules (constraints) that keep bad data out. A table with good constraints protects your data even when people make typing mistakes.</p>

<h3>The CREATE TABLE statement</h3>
<pre><code>CREATE TABLE members (
  member_id INTEGER PRIMARY KEY,
  name      VARCHAR(100) NOT NULL,
  email     VARCHAR(120) UNIQUE,
  joined    DATE NOT NULL,
  status    VARCHAR(10) NOT NULL DEFAULT 'active'
);</code></pre>
<p>Each line inside the brackets is a column: its name, its data type and optional rules. Lines are separated by commas, and there is no comma after the last one.</p>

<h3>Common data types</h3>
<table>
<thead><tr><th>Kind of data</th><th>Portable choice</th><th>Notes</th></tr></thead>
<tbody>
<tr><td>Whole numbers</td><td><code>INTEGER</code></td><td>Counts, ids, quantities</td></tr>
<tr><td>Decimal numbers</td><td><code>DECIMAL(10,2)</code></td><td>Money with cents. SQLite stores it loosely; for whole shillings use INTEGER</td></tr>
<tr><td>Short text</td><td><code>VARCHAR(n)</code></td><td>Names, towns. In SQLite the length is only a hint</td></tr>
<tr><td>Long text</td><td><code>TEXT</code></td><td>Notes, descriptions</td></tr>
<tr><td>Dates</td><td><code>DATE</code></td><td>Written as <code>'2025-03-01'</code>; MySQL checks the format, SQLite keeps text</td></tr>
</tbody>
</table>
<p>SQLite is "flexible": it keeps your value even if it does not match the declared type exactly. MySQL is strict and may reject it. Write your types carefully so your table works in either.</p>

<h3>Auto-numbering keys</h3>
<p>Instead of choosing ids yourself, you can let the database number new rows. The syntax differs:</p>
<pre><code>-- MySQL
member_id INT AUTO_INCREMENT PRIMARY KEY

-- SQLite
member_id INTEGER PRIMARY KEY AUTOINCREMENT</code></pre>
<p>In SQLite, a column declared exactly <code>INTEGER PRIMARY KEY</code> already numbers itself when you omit the id on insert, so AUTOINCREMENT is optional.</p>

<h3>Constraints</h3>
<ul>
<li><strong>PRIMARY KEY</strong>: unique and never empty; identifies the row.</li>
<li><strong>NOT NULL</strong>: the column must have a value.</li>
<li><strong>UNIQUE</strong>: no two rows may share the value (good for email addresses).</li>
<li><strong>DEFAULT</strong>: the value used when none is given.</li>
<li><strong>CHECK</strong>: a rule such as <code>CHECK (price &gt;= 0)</code>. SQLite enforces it; MySQL enforces it from version 8.0.16.</li>
<li><strong>FOREIGN KEY</strong>: the value must exist in another table's key column.</li>
</ul>
<p>Here is a table with several constraints working together:</p>
<pre><code>CREATE TABLE fees (
  fee_id     INTEGER PRIMARY KEY,
  student    VARCHAR(80) NOT NULL,
  amount_ugx INTEGER NOT NULL CHECK (amount_ugx &gt; 0),
  paid_on    DATE NOT NULL
);</code></pre>
<p>An attempt to insert an amount of 0 or a negative number now fails with an error, which is exactly what you want.</p>

<h3>Changing and removing tables</h3>
<p><code>ALTER TABLE members ADD COLUMN phone VARCHAR(20);</code> adds a column and works in both systems. <code>DROP TABLE members;</code> deletes the whole table and its data, so use it with great care.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create the <code>fees</code> table above.</li>
<li>Try to insert a row with amount_ugx of 0 and read the error. (Use <code>INSERT INTO fees (student, amount_ugx, paid_on) VALUES ('Test', 0, '2025-03-01');</code>)</li>
<li>Add a column <code>receipt_no</code> of type VARCHAR(20) with ALTER TABLE.</li>
<li>Design, on paper, a <code>teachers</code> table with a suitable key, types and at least two constraints.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>CREATE TABLE lists each column with a name, a type and optional constraints.</li>
<li>Use INTEGER, VARCHAR, TEXT, DECIMAL and DATE for portable designs.</li>
<li>Auto-numbering syntax differs between MySQL and SQLite.</li>
<li>Constraints such as NOT NULL, UNIQUE, CHECK and FOREIGN KEY protect data quality.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'INSERT, UPDATE and DELETE (and why WHERE matters)',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to add, change and remove rows, and to do it safely. The statements are short, but a missing WHERE clause can damage every row in a table, so the safe habits in this lesson are the most important of the course.</p>

<h3>INSERT</h3>
<p>Add a row by naming the columns and giving matching values in the same order:</p>
<pre><code>INSERT INTO products (product_id, name, category, price, stock)
VALUES (9, 'Exercise book', 'Stationery', 1200, 100);</code></pre>
<p>You can add several rows in one statement by separating the value lists with commas. Any column you leave out gets its DEFAULT value, or NULL if it has none (and is allowed to be NULL). If you omit a NOT NULL column that has no default, the database refuses the row.</p>
<pre><code>SELECT name, price FROM products WHERE product_id = 9;</code></pre>
<pre>name           price
-------------  -----
Exercise book   1200</pre>

<h3>UPDATE</h3>
<p>UPDATE changes existing rows. The price of rice has risen to 5200:</p>
<pre><code>UPDATE products SET price = 5200 WHERE product_id = 4;</code></pre>
<p>You can change several columns at once: <code>SET price = 5200, stock = 30</code>. You can also calculate from the old value. A 10 percent discount on all Stationery, keeping whole shillings:</p>
<pre><code>UPDATE products SET price = price * 9 / 10 WHERE category = 'Stationery';</code></pre>
<p>Multiplying before dividing keeps the whole-number arithmetic accurate: 2500 x 9 / 10 gives 2250.</p>

<h3>DELETE</h3>
<p>DELETE removes rows. Remove the exercise book we added:</p>
<pre><code>DELETE FROM products WHERE product_id = 9;</code></pre>

<h3>The danger of a missing WHERE</h3>
<p>Compare:</p>
<pre><code>UPDATE products SET price = 0;
DELETE FROM products;</code></pre>
<p>With no WHERE, the first sets every price to zero and the second deletes every row. The database will not warn you. Many real businesses have lost data this way. Follow these habits every time:</p>
<ol>
<li><strong>Write the WHERE first.</strong> Type the condition before the rest of the statement, or start by writing a SELECT.</li>
<li><strong>Test with SELECT.</strong> Run <code>SELECT * FROM products WHERE product_id = 9;</code> and check that it returns exactly the rows you mean to change. Then edit the same statement into an UPDATE or DELETE.</li>
<li><strong>Use the primary key</strong> in the condition when changing one row.</li>
<li><strong>Use a transaction</strong> when your tool allows: run <code>BEGIN;</code> (SQLite and MySQL) before the change, check the result, then <code>COMMIT;</code> to keep it or <code>ROLLBACK;</code> to undo it. Some tools commit automatically, so read your tool's help first.</li>
<li><strong>Back up first</strong> before any large change (see the next lesson).</li>
</ol>

<h3>Foreign keys and deletes</h3>
<p>If a row is referenced by another table, the database may refuse to delete it. You cannot delete customer 1 while orders 101 and 102 point to her. You must remove or reassign the orders first, or design the foreign key with <code>ON DELETE CASCADE</code> so that dependent rows are removed automatically. Use cascade carefully because it can delete a lot at once.</p>

<h3>Try it yourself</h3>
<ol>
<li>Insert a new product called 'Candle' in category 'Household', price 800, stock 70, with product_id 10.</li>
<li>Check it with a SELECT on that id.</li>
<li>Update its price to 900, using the primary key in WHERE.</li>
<li>Write the SELECT you would run first to check which rows <code>DELETE FROM products WHERE category = 'Household'</code> would remove. (Soap and Candle.) Do not run the delete.</li>
<li>Delete only the candle with its product_id.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>INSERT adds rows, UPDATE changes them, DELETE removes them.</li>
<li>Without WHERE, UPDATE and DELETE affect every row.</li>
<li>Always test the WHERE with a SELECT first, and use transactions and backups.</li>
<li>Foreign keys can prevent deleting rows that other tables use.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Indexes, backups and SQL injection awareness',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will understand what an index does, know how to protect your data with backups, and recognise SQL injection, one of the most common attacks on websites. These habits separate a careful data worker from a risky one.</p>

<h3>Indexes: the idea</h3>
<p>Think of the index at the back of a textbook. Instead of reading every page to find "photosynthesis", you look in the index and jump straight to the page. A database <strong>index</strong> works the same way for one or more columns.</p>
<pre><code>CREATE INDEX idx_orders_customer ON orders (customer_id);</code></pre>
<p>Without it, finding a customer's orders means reading every row of <code>orders</code>. With it, the database jumps directly to the matching rows. With 8 rows you will not notice, but with 5 million rows the difference is huge.</p>
<ul>
<li>Primary keys and UNIQUE columns are indexed automatically.</li>
<li>Index columns that you often use in WHERE, JOIN ... ON and ORDER BY, such as foreign keys.</li>
<li>Indexes cost storage space and make inserts and updates slightly slower, so do not index every column.</li>
</ul>
<p>The commands <code>EXPLAIN</code> (MySQL) and <code>EXPLAIN QUERY PLAN</code> (SQLite) show whether a query uses an index. You do not need them yet, but remember that they exist.</p>

<h3>Backups</h3>
<p>Hardware fails, people delete the wrong thing and software has bugs. A backup is a copy you can restore. A good habit is the <strong>3-2-1 rule</strong>: keep 3 copies, on 2 different kinds of storage, with 1 copy away from the building.</p>
<ul>
<li><strong>SQLite:</strong> the whole database is one file. Close the program and copy the file, or in DB Browser use <em>File, Export, Database to SQL file</em> to save a text script that rebuilds everything.</li>
<li><strong>MySQL:</strong> use the <code>mysqldump</code> tool or the export function in your management program to create a dump file.</li>
<li>Test restoring a backup occasionally. A backup you have never tried to restore may not work.</li>
<li>Back up before big updates or deletes, and keep backups private: they contain the same personal data as the live database.</li>
</ul>

<h3>SQL injection awareness</h3>
<p><strong>SQL injection</strong> happens when a program builds a query by pasting user input directly into the text of the SQL. Imagine a login page that builds this query:</p>
<pre><code>SELECT * FROM users WHERE name = 'INPUT' AND password = 'INPUT2';</code></pre>
<p>If the attacker types <code>' OR '1'='1</code> as the name, the pasted text changes the logic of the query so that it is always true and the attacker may get in. Worse inputs can read or delete whole tables.</p>
<p>You do not need to attack anything to learn from this; you need to remember the defences:</p>
<ol>
<li><strong>Use parameterised queries</strong> (also called prepared statements). The program sends the SQL and the values separately, so the database treats the input only as data, never as commands. Every serious programming language has a way to do this.</li>
<li><strong>Never join strings</strong> to build SQL from user input.</li>
<li><strong>Give the application a limited database account</strong> that can only do what it needs, for example no DROP.</li>
<li><strong>Validate input</strong> (a phone field should hold digits) as an extra layer, but not as the only defence.</li>
<li><strong>Keep software updated</strong> and never show raw database errors to visitors.</li>
</ol>

<h3>Protecting personal data</h3>
<p>Databases often hold names, phone numbers and marks. Store only what you need, restrict who can see it, and follow your organisation's rules and Uganda's Data Protection and Privacy Act, which requires care with personal information.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create an index on <code>order_items (product_id)</code> in your shop database.</li>
<li>Export your database or copy your SQLite file as a backup named with today's date.</li>
<li>Write, in your own words, why pasting user input into SQL text is dangerous.</li>
<li>List two defences you would ask a web developer to use.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>An index speeds up searches on a column, at the cost of space and slower writes.</li>
<li>Keep tested backups following the 3-2-1 rule.</li>
<li>SQL injection comes from mixing user input into SQL text.</li>
<li>Parameterised queries and limited accounts are the main defences.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Mini project: a library database',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>In this project you will build a small library database from scratch, fill it with data and answer four real questions. It brings together design, constraints, inserts, updates, joins, aggregates and subqueries. When you finish you will have something you can show a tutor or employer.</p>

<h3>Step 1: design</h3>
<p>The library records books, members and loans. A member borrows many books over time, and a book is borrowed by many members over time, so <code>loans</code> is the link table.</p>
<pre><code>books   (book_id PK, title, author, year_published, copies)
members (member_id PK, name, joined)
loans   (loan_id PK, book_id FK, member_id FK, loan_date, return_date)</code></pre>
<p>A loan with <code>return_date</code> equal to NULL is still out.</p>

<h3>Step 2: create the tables</h3>
<pre><code>CREATE TABLE books (
  book_id        INTEGER PRIMARY KEY,
  title          VARCHAR(150) NOT NULL,
  author         VARCHAR(100) NOT NULL,
  year_published INTEGER,
  copies         INTEGER NOT NULL DEFAULT 1 CHECK (copies &gt;= 0)
);
CREATE TABLE members (
  member_id INTEGER PRIMARY KEY,
  name      VARCHAR(100) NOT NULL,
  joined    DATE NOT NULL
);
CREATE TABLE loans (
  loan_id     INTEGER PRIMARY KEY,
  book_id     INTEGER NOT NULL,
  member_id   INTEGER NOT NULL,
  loan_date   DATE NOT NULL,
  return_date DATE,
  FOREIGN KEY (book_id)   REFERENCES books (book_id),
  FOREIGN KEY (member_id) REFERENCES members (member_id)
);</code></pre>

<h3>Step 3: insert the data</h3>
<pre><code>INSERT INTO books (book_id, title, author, year_published, copies) VALUES
(1, 'Things Fall Apart',      'Chinua Achebe',           1958, 3),
(2, 'Weep Not, Child',        'Ngugi wa Thiong''o',      1964, 2),
(3, 'Song of Lawino',         'Okot p''Bitek',           1966, 2),
(4, 'Kintu',                  'Jennifer Nansubuga Makumbi', 2014, 1),
(5, 'Half of a Yellow Sun',   'Chimamanda Ngozi Adichie', 2006, 2);

INSERT INTO members (member_id, name, joined) VALUES
(1, 'Achan Rose',  '2025-01-10'),
(2, 'Opio James',  '2025-02-03'),
(3, 'Atim Mercy',  '2025-02-20');

INSERT INTO loans (loan_id, book_id, member_id, loan_date, return_date) VALUES
(1, 1, 1, '2025-03-01', '2025-03-10'),
(2, 3, 1, '2025-03-12', NULL),
(3, 2, 2, '2025-03-05', '2025-03-14'),
(4, 1, 3, '2025-03-15', NULL),
(5, 5, 2, '2025-03-20', NULL);</code></pre>
<p>Notice the doubled single quote in <code>Ngugi wa Thiong''o</code>: two single quotes inside a text value mean one apostrophe.</p>

<h3>Step 4: answer the questions</h3>
<p><strong>Which books are out right now, and who has them?</strong></p>
<pre><code>SELECT b.title, m.name, l.loan_date
FROM loans AS l
INNER JOIN books   AS b ON b.book_id   = l.book_id
INNER JOIN members AS m ON m.member_id = l.member_id
WHERE l.return_date IS NULL
ORDER BY l.loan_date;</code></pre>
<pre>title                 name         loan_date
--------------------  -----------  ----------
Song of Lawino        Achan Rose   2025-03-12
Things Fall Apart     Atim Mercy   2025-03-15
Half of a Yellow Sun  Opio James   2025-03-20</pre>
<p><strong>How many loans has each member made?</strong></p>
<pre><code>SELECT m.name, COUNT(l.loan_id) AS loans
FROM members AS m
LEFT JOIN loans AS l ON l.member_id = m.member_id
GROUP BY m.member_id, m.name
ORDER BY loans DESC, m.name;</code></pre>
<pre>name        loans
----------  -----
Achan Rose      2
Opio James      2
Atim Mercy      1</pre>
<p><strong>Which book has never been borrowed?</strong></p>
<pre><code>SELECT title FROM books
WHERE book_id NOT IN (SELECT book_id FROM loans);</code></pre>
<pre>title
-----
Kintu</pre>
<p><strong>How many copies of each book are available now?</strong> Copies minus the loans not yet returned. The test on <code>return_date</code> goes in the ON clause so books with no open loans still appear:</p>
<pre><code>SELECT b.title, b.copies - COUNT(l.loan_id) AS available
FROM books AS b
LEFT JOIN loans AS l ON l.book_id = b.book_id AND l.return_date IS NULL
GROUP BY b.book_id, b.title, b.copies
ORDER BY b.book_id;</code></pre>
<pre>title                 available
--------------------  ---------
Things Fall Apart             2
Weep Not, Child               2
Song of Lawino                1
Kintu                         1
Half of a Yellow Sun          1</pre>

<h3>Step 5: record a return</h3>
<p>Achan Rose returns Song of Lawino on 25 March:</p>
<pre><code>UPDATE loans SET return_date = '2025-03-25' WHERE loan_id = 2;</code></pre>

<h3>Try it yourself</h3>
<ol>
<li>Build the database, run all four queries and compare with the results above.</li>
<li>Record the return, then rerun the "out right now" query. Only two rows should remain.</li>
<li>Add a new book and a new member of your own, and a loan connecting them.</li>
<li>Write one new query of your own, for example "books published before 1970".</li>
<li>Export a backup of the finished database.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Plan tables and keys on paper first, then create them with constraints.</li>
<li>A NULL return date neatly means "still on loan".</li>
<li>Put conditions on the right-hand table in the ON clause when using LEFT JOIN with aggregates.</li>
<li>Finish a project by testing, recording changes safely and making a backup.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which constraint stops two members from having the same email address?
- NOT NULL
* UNIQUE
- DEFAULT
- INDEX
= UNIQUE refuses a second row with the same value in that column.

Q: What happens if you run DELETE FROM products; with no WHERE clause?
- Nothing, because WHERE is required
- Only the first row is deleted
* Every row in the table is deleted
- The table structure is deleted but rows remain
= Without a condition the statement applies to every row; the table itself stays but is empty.

Q: What is the safest first step before running UPDATE products SET price = 6000 WHERE category = 'Food'?
- Run it and see
* Run a SELECT with the same WHERE to check which rows will change
- Drop the table
- Remove the WHERE clause
= A SELECT with the same condition shows exactly which rows would be affected.

Q: Which INSERT is written correctly?
- INSERT products VALUES name = 'Candle';
- ADD INTO products ('Candle');
* INSERT INTO products (product_id, name, category, price, stock) VALUES (10, 'Candle', 'Household', 800, 70);
- INSERT INTO products SET 'Candle';
= INSERT INTO table (columns) VALUES (values) is the standard form.

Q: What is the main purpose of an index?
- To keep the data private
* To make searches on a column faster
- To delete duplicate rows
- To compress the database
= An index lets the database find rows without reading the whole table.

Q: Which are good defences against SQL injection? (choose all that apply)
* Parameterised (prepared) queries
* A database account with limited permissions
- Building queries by joining user text into the SQL
- Showing full database errors to visitors
= Parameters keep input as data, and limited accounts reduce damage; the last two options make attacks easier.

Q: In SQLite, how do you store a text value that contains an apostrophe, such as p'Bitek?
- Remove the apostrophe
- Use double quotes only
* Write two single quotes inside the text value
- Put a star in front of it
= A doubled single quote inside a quoted text value stands for one apostrophe.

Q: Which backup habit is best?
- Keep one copy on the same computer
* Keep several copies, including one stored elsewhere, and test restoring them
- Back up only after a failure
- Never back up personal data
= Copies in different places and tested restores protect against loss.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What is a foreign key?
- A column that is always empty
- The first column of every table
* A column that holds the primary key value of a row in another table
- A password for the database
= Foreign keys link tables together.

Q: A column that holds unique, never-empty values that identify each row is called:
- A foreign key
- An alias
- A constraint violation
* A primary key
= The primary key is the row's identity.

Q: Which design is best for students who take many subjects, where each subject has many students?
- A student table with 20 subject columns
- One table with names and subjects in a single text column
* Students, subjects and a link table such as enrolments
- A subject table with a column listing student names
= Many-to-many relationships need a link table.

Q: Which query shows each product's name and price, cheapest first?
- SELECT name, price FROM products SORT price;
* SELECT name, price FROM products ORDER BY price;
- SELECT name, price ORDER BY products;
- ORDER products BY price SELECT name;
= ORDER BY sorts ascending by default.

Q: Which condition finds products in the Food or Household categories?
- category = Food, Household
- category BETWEEN 'Food' AND 'Household'
- category LIKE Food OR Household
* category IN ('Food', 'Household')
= IN checks a list of values.

Q: What does the pattern '%oo%' in a LIKE test match?
- Text that starts with oo
- Text that ends with oo
* Text that contains oo anywhere
- Text that is exactly oo
= Percent signs on both sides mean "anything before and after".

Q: What is the result of SELECT name FROM products WHERE stock = NULL; ?
- All rows with missing stock
- An error message
* No rows, because comparing with NULL is never true
- Every row in the table
= Use IS NULL to test for missing values.

Q: Which query returns the 2 most expensive products?
- SELECT name FROM products LIMIT 2 ORDER BY price DESC;
* SELECT name, price FROM products ORDER BY price DESC LIMIT 2;
- SELECT TOP 2 name FROM products WHERE price;
- SELECT name FROM products ORDER BY LIMIT 2 price;
= Sort descending first and then LIMIT; ORDER BY comes before LIMIT.

Q: A column holds the values 10, 20 and NULL. What does AVG(column) return?
- 10
- 7.5
* 15
- NULL
= AVG ignores NULL, so it calculates (10 + 20) / 2.

Q: Which function counts all rows in a table, including those with NULL values in some columns?
- COUNT(column)
* COUNT(*)
- SUM(*)
- TOTAL(rows)
= COUNT(*) counts rows; COUNT(column) skips NULLs.

Q: Why does this query give an error? SELECT category, price FROM products GROUP BY category;
- GROUP BY needs a WHERE
* price is neither grouped nor inside an aggregate function
- category cannot be grouped
- Tables cannot be grouped
= Plain columns must be in GROUP BY or inside an aggregate.

Q: Which clause filters groups after GROUP BY?
- WHERE
- LIMIT
* HAVING
- DISTINCT
= HAVING works on grouped results.

Q: In this query, what is the role of WHERE? SELECT category, COUNT(*) FROM products WHERE price > 1000 GROUP BY category;
- It filters groups after counting
* It removes rows with price 1000 or less before the groups are built
- It sorts the groups
- It renames the column
= WHERE runs before grouping.

Q: What does this return? SELECT CASE WHEN price < 2000 THEN 'Cheap' ELSE 'Other' END AS band FROM products WHERE product_id = 2;
* Cheap
- Other
- NULL
- An error
= Product 2 (Pen) costs 500, which is below 2000.

Q: Which join returns all customers, whether or not they have orders?
- orders INNER JOIN customers
- customers INNER JOIN orders
* customers LEFT JOIN orders
- orders JOIN orders
= LEFT JOIN keeps every row from the left table.

Q: Which query finds customers who have never placed an order?
- SELECT name FROM customers INNER JOIN orders ON orders.customer_id = customers.customer_id;
* SELECT c.name FROM customers c LEFT JOIN orders o ON o.customer_id = c.customer_id WHERE o.order_id IS NULL;
- SELECT name FROM customers WHERE orders = 0;
- SELECT name FROM orders WHERE customer_id IS NULL;
= LEFT JOIN with a NULL test on the right-hand key shows unmatched rows.

Q: What does the ON part of a join do?
- It sorts the result
* It states how rows of the two tables are matched
- It limits the number of rows
- It names the table
= ON gives the matching rule, usually foreign key equals primary key.

Q: Which query lists products priced above the average price?
- SELECT name FROM products WHERE price > AVG(price);
- SELECT name FROM products HAVING price > AVG(price);
* SELECT name FROM products WHERE price > (SELECT AVG(price) FROM products);
- SELECT AVG(name) FROM products;
= A subquery provides the average to the outer WHERE.

Q: Which statement is true about the order_items table in the shop database? (choose all that apply)
* It has a composite primary key made of two columns
* It links orders and products
- It stores each customer's address
- It makes a one-to-one relationship
= It is a link table whose key combines order_id and product_id.

Q: Which CREATE TABLE column definition makes sure the amount is always above zero?
- amount INTEGER DEFAULT 0
- amount INTEGER UNIQUE
* amount INTEGER NOT NULL CHECK (amount > 0)
- amount INTEGER INDEX
= CHECK enforces a rule on the values.

Q: What is the safest way to make sure an UPDATE changes only one row?
- Leave out WHERE
- Use WHERE with a column that has many repeated values
* Use WHERE with the primary key
- Use LIMIT 1 only and hope
= The primary key identifies exactly one row.

Q: What does the following statement do? UPDATE products SET price = 0;
- Sets the price of the first row to 0
- Does nothing
* Sets the price of every row to 0
- Deletes the price column
= With no WHERE, UPDATE changes all rows.

Q: Which of these helps you undo a mistaken change in a database that supports transactions? (choose all that apply)
* Starting with BEGIN and using ROLLBACK
* A recent tested backup
- Running the statement twice
- Dropping the table
= Transactions and backups let you return to a safe state.

Q: When is an index most useful?
- On every column of every table
* On columns often used in WHERE, JOIN conditions or sorting in large tables
- On a table with 5 rows
- Only on text columns
= Indexes speed up searches but cost space and slow writes slightly.

Q: A website builds SQL by joining user input into text, and an attacker types text that changes the meaning of the query. This is called:
- A join error
- Database indexing
* SQL injection
- Data normalisation
= User input was treated as part of the SQL commands.

Q: Which is the best defence against SQL injection?
- Hiding the database name
* Parameterised queries, so input is sent separately from the SQL
- Using capital letters in SQL
- Making queries shorter
= Parameters keep user input as data only.

Q: What does the 3-2-1 backup rule suggest?
- Back up 3 times a year
* Keep 3 copies, on 2 kinds of storage, with 1 copy off-site
- Use 3 passwords for 2 users
- Keep 1 copy only, in 2 places
= Spreading copies reduces the chance of losing everything at once.

Q: In SQLite, how do you tell that a book loan has not been returned if return_date is NULL for open loans?
- WHERE return_date = NULL
- WHERE return_date = 0
* WHERE return_date IS NULL
- WHERE return_date = ''
= IS NULL is the correct test for missing values.

Q: Why is a spreadsheet a poor choice for a payroll that many staff update at the same time?
- Spreadsheets cannot do arithmetic
* Conflicting edits and weak rules make errors likely, which a database controls better
- Databases are always faster for 10 rows
- Spreadsheets cannot be backed up
= Concurrency and data rules are strengths of databases.
QUIZ,
];
