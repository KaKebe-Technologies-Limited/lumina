<?php
return [
    'slug'        => 'excel-advanced-automation',
    'title'       => 'Advanced Excel: Power Query, Power Pivot and Automation',
    'category'    => 'data',
    'level'       => 'advanced',
    'icon'        => 'layers',
    'sort'        => 180,
    'summary'     => 'Go beyond formulas: import and reshape data with Power Query, model it with Power Pivot and DAX, use dynamic arrays, and automate repeat work safely.',
    'description' => '<p>When reports grow, copying and pasting from many files becomes slow and risky. Professional analysts use Excel differently: they connect to the data, clean it with repeatable steps, model it as related tables, and write measures that answer questions in any slice of the data. This course teaches those tools.</p><p>You will use Power Query to import and reshape files, Power Pivot and the Data Model to relate tables, DAX to write measures, dynamic array formulas to build live lists and reports, and macros and good workbook habits to save time and reduce errors. The course ends with a capstone project that brings them together.</p><p>This is an advanced course. You should already be comfortable with Excel Tables, PivotTables, SUMIFS and lookups. Some tools depend on your version of Excel, and each lesson says what to use if yours does not have them.</p>',
    'outcomes'    => [
        'Import data from files and folders with Power Query and refresh it with one click',
        'Transform data with repeatable steps, including merging, appending and unpivoting queries',
        'Build a Data Model with related tables following star schema principles',
        'Explain the difference between calculated columns and measures and choose correctly',
        'Write DAX measures using SUM, CALCULATE, FILTER, DIVIDE and basic time intelligence',
        'Use dynamic array functions such as FILTER, SORT, UNIQUE, SEQUENCE and LET, or alternatives in older versions',
        'Record a simple macro, explain what VBA and Office Scripts are, and recognise their risks',
        'Protect, document and tune a workbook, and deliver a complete reporting model',
    ],
    'requirements' => ['Completed Excel for Data Analysis (PivotTables, charts and dashboards), or equivalent intermediate skills: Tables, PivotTables, SUMIFS and XLOOKUP or INDEX-MATCH', 'Excel for Windows with Microsoft 365, Excel 2021 or Excel 2019 Professional Plus (Power Pivot is not in every edition; see Lesson 2.1)', 'Sample files to practise with, which you will create during the lessons'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Microsoft Support: Excel help and training', 'url' => 'https://support.microsoft.com/excel'],
        ['title' => 'Microsoft Learn: Power Query documentation', 'url' => 'https://learn.microsoft.com/power-query/'],
        ['title' => 'Microsoft Learn: DAX reference', 'url' => 'https://learn.microsoft.com/dax/'],
        ['title' => 'DAX Guide (reference for DAX functions)', 'url' => 'https://dax.guide/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: Power Query',
            'summary' => 'Import data from files and folders, transform it with repeatable steps, combine queries and refresh.',
            'lessons' => [
                [
                    'title'   => 'Why Power Query? Importing from files',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to import a CSV or Excel file with Power Query and explain why this is better than copy and paste. This matters because every month someone receives a new file with the same layout. With Power Query you record the cleaning once, and next month you only press Refresh.</p>

<h3>What Power Query is</h3>
<p><strong>Power Query</strong> is the data connection and shaping tool built into Excel (found under <strong>Data</strong> then <strong>Get Data</strong> in Excel 2016 and later; in Excel 2010 and 2013 it was a free add-in). It reads data from many sources, lets you apply steps such as removing columns or changing types, and loads the result into a worksheet or the Data Model. The original file is never changed. Power Query remembers every step, so the whole process can be repeated automatically.</p>
<p>Compare the two approaches:</p>
<table>
<thead><tr><th>Manual cleaning</th><th>Power Query</th></tr></thead>
<tbody>
<tr><td>Repeat every step each month</td><td>Steps are recorded and replay on Refresh</td></tr>
<tr><td>Easy to forget a step or make a slip</td><td>Same steps every time</td></tr>
<tr><td>Result mixed with the original data</td><td>Source stays untouched; results go to a new Table</td></tr>
<tr><td>Hard to explain what you did</td><td>The Applied Steps list documents it</td></tr>
</tbody>
</table>

<h3>Import a CSV or text file</h3>
<ol>
<li>Choose the <strong>Data</strong> tab, then <strong>Get Data</strong>, then <strong>From File</strong>, then <strong>From Text/CSV</strong>.</li>
<li>Browse to your file and click <strong>Import</strong>.</li>
<li>A preview appears. Check the <strong>File Origin</strong> and the <strong>Delimiter</strong> (comma, semicolon, tab). If letters look wrong, such as accents, try <strong>65001: Unicode (UTF-8)</strong>.</li>
<li>Click <strong>Transform Data</strong> to open the Power Query Editor, or <strong>Load</strong> to send the data straight to a sheet.</li>
</ol>
<p>For an Excel workbook choose <strong>From Excel Workbook</strong>, then pick a sheet or Table in the navigator. A Table or named range is safer than a sheet, because its edges are clear.</p>

<h3>The Power Query Editor</h3>
<ul>
<li><strong>The preview grid</strong> in the middle shows the data after each step.</li>
<li><strong>Query Settings</strong> on the right lists the <strong>Applied Steps</strong>. Click any step to see the data at that point. Delete a step with the small <strong>x</strong>.</li>
<li><strong>The formula bar</strong> shows the code of the step. The language is called <strong>M</strong>. You rarely write it, but reading it helps you debug.</li>
<li>The <strong>Queries</strong> pane on the left lists all your queries.</li>
</ul>

<h3>Load destinations</h3>
<p>Click <strong>Close &amp; Load</strong>, or the arrow beside it and <strong>Close &amp; Load To</strong>, to choose: a Table on a sheet, a PivotTable, only a connection (no data shown), and whether to <strong>Add this data to the Data Model</strong>. For a staging query that other queries use, choose <strong>Only Create Connection</strong>.</p>

<h3>Good habits from the start</h3>
<ul>
<li>Give each query a clear name, such as <code>Sales_Raw</code> or <code>Sales_Clean</code>.</li>
<li>Keep the source files in a stable folder; if you move them, the query breaks. You can repair it under <strong>Data Source Settings</strong>.</li>
<li>Do not edit the output Table by hand, because Refresh overwrites it.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Create a small CSV file in Notepad with the headers <code>Date,Branch,Product,Quantity,Price</code> and five rows. Save it as <code>sales-jan.csv</code>.</li>
<li>Import it with <strong>Data</strong>, <strong>Get Data</strong>, <strong>From Text/CSV</strong>.</li>
<li>Click <strong>Transform Data</strong> and look at the Applied Steps list.</li>
<li>Rename the query <code>Sales_Jan</code> and click <strong>Close &amp; Load</strong>.</li>
<li>Add a row to the CSV file in Notepad, save, return to Excel and press <strong>Data</strong>, then <strong>Refresh All</strong>. Does the new row appear?</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Power Query records your cleaning steps so they repeat on every refresh.</li>
<li>The source file stays unchanged; results load to a Table or the Data Model.</li>
<li>Applied Steps document and let you undo any step.</li>
<li>Name queries clearly and keep source files in a stable place.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Transforming data in Power Query',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to clean and reshape data in the Power Query Editor using the most common transformations. This matters because most real data arrives with junk rows, wrong types and awkward columns, and these steps fix them in a way that replays automatically next time.</p>

<h3>Start with the header and the data types</h3>
<p>Many exports have title rows above the real headers. Use <strong>Home</strong>, then <strong>Remove Rows</strong>, then <strong>Remove Top Rows</strong> to delete them, then <strong>Use First Row as Headers</strong>. Next fix the data type of each column with the small icon on the left of the header (ABC for text, 123 for whole number, 1.2 for decimal, a calendar for date). Set types deliberately: a wrong type is the source of many later errors. If a date column is read in the wrong order, use <strong>Change Type</strong>, then <strong>Using Locale</strong>, and choose Date with a suitable region.</p>

<h3>The everyday toolbox</h3>
<table>
<thead><tr><th>You want to</th><th>Use</th></tr></thead>
<tbody>
<tr><td>Delete unwanted columns</td><td>Select columns, right-click, <strong>Remove Columns</strong> (or <strong>Remove Other Columns</strong> to keep only the selected ones)</td></tr>
<tr><td>Remove blank or error rows</td><td><strong>Home</strong>, <strong>Remove Rows</strong>, <strong>Remove Blank Rows</strong> or <strong>Remove Errors</strong></td></tr>
<tr><td>Trim spaces and fix capitals</td><td><strong>Transform</strong>, <strong>Format</strong>, <strong>Trim</strong>, <strong>Clean</strong>, <strong>Capitalize Each Word</strong></td></tr>
<tr><td>Replace values</td><td><strong>Transform</strong>, <strong>Replace Values</strong></td></tr>
<tr><td>Split a column</td><td><strong>Split Column</strong> by delimiter or by number of characters</td></tr>
<tr><td>Fill blanks from above</td><td><strong>Transform</strong>, <strong>Fill</strong>, <strong>Down</strong></td></tr>
<tr><td>Keep only some rows</td><td>The filter arrow on a header</td></tr>
<tr><td>Remove duplicates</td><td>Right-click the column header, <strong>Remove Duplicates</strong></td></tr>
</tbody>
</table>
<p>Fill Down is a favourite: exports often show a branch name once and leave the cells below blank. Fill Down copies it into the blanks, producing tidy data.</p>

<h3>Add new columns</h3>
<ul>
<li><strong>Add Column</strong>, then <strong>Custom Column</strong>, lets you type a formula using column names, for example <code>[Quantity] * [Price]</code>. Names are case sensitive in Power Query.</li>
<li><strong>Column From Examples</strong> lets you type an example result and Power Query guesses the rule.</li>
<li><strong>Conditional Column</strong> builds if-then-else logic with menus, for example labelling sales above 100,000 as "Large".</li>
<li><strong>Date</strong> options extract the year, month name or quarter from a date column.</li>
</ul>

<h3>Order of steps matters</h3>
<p>Steps run from top to bottom. If you delete a column in step 3 and a later step needs it, the query will show an error. You can drag steps to reorder them or insert a step in the middle by clicking an earlier step and making a change, with a warning that later steps may break. Rename steps (right-click, <strong>Rename</strong>) with meaningful names such as "Removed title rows" to document your work.</p>

<h3>Errors and nulls</h3>
<p>A cell showing <code>Error</code> means a step could not be applied, usually text turned into a number. Click the word beside <code>Error</code> to read the reason. Replace errors or remove them, but investigate first. An empty value is called <code>null</code>. Nulls are not zeros: remember to replace them if you need zero in totals.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create a CSV with two title lines above the headers, a <code>Branch</code> column where the name appears only on the first row of each group, and a price column with a few blanks.</li>
<li>In the Power Query Editor, remove the top rows and promote the headers.</li>
<li>Use Fill Down on the Branch column.</li>
<li>Set the data types and add a <code>Total</code> custom column of Quantity times Price.</li>
<li>Rename three steps clearly, then close and load.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Fix the header row first, then set each column's data type on purpose.</li>
<li>Remove, filter, replace, split, fill down and trim cover most cleaning.</li>
<li>Custom and conditional columns add calculations; names are case sensitive.</li>
<li>Steps run in order; rename them so your query documents itself.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Combining data: append, merge and folders',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to stack tables with Append, join tables with Merge, and import a whole folder of files at once. This matters because data is rarely in one place. Twelve monthly files, or a sales list and a separate product list, can be combined into one clean table that updates by itself.</p>

<h3>Append: stack tables with the same columns</h3>
<p>Use <strong>Append</strong> when two or more tables have the same columns and you want one long table, for example <code>Sales_Jan</code> and <code>Sales_Feb</code>.</p>
<ol>
<li>In the Power Query Editor choose <strong>Home</strong>, then <strong>Append Queries</strong>, then <strong>Append Queries as New</strong>.</li>
<li>Pick <strong>Two tables</strong> or <strong>Three or more tables</strong> and select them.</li>
<li>Click <strong>OK</strong> and name the new query <code>Sales_All</code>.</li>
</ol>
<p>Columns are matched by name, not position. A column with a different spelling creates an extra column of nulls, so standardise names first.</p>

<h3>Merge: join tables by a key</h3>
<p>Use <strong>Merge</strong> to bring columns from one table into another, like a lookup but repeatable and able to bring many columns.</p>
<ol>
<li>Select the main query (for example <code>Sales_All</code>) and choose <strong>Home</strong>, then <strong>Merge Queries</strong>.</li>
<li>Choose the second table (for example <code>Products</code>).</li>
<li>Click the matching column in each table, such as <code>ProductCode</code>. The data types of the two keys must match.</li>
<li>Choose a <strong>Join Kind</strong> and click <strong>OK</strong>.</li>
<li>Click the expand icon on the new column and tick the columns to bring in, such as <code>Category</code> and <code>Cost</code>. Untick <strong>Use original column name as prefix</strong> if you prefer short names.</li>
</ol>
<table>
<thead><tr><th>Join kind</th><th>Result</th></tr></thead>
<tbody>
<tr><td>Left Outer (default)</td><td>All rows of the first table plus matches from the second</td></tr>
<tr><td>Inner</td><td>Only rows that match in both</td></tr>
<tr><td>Left Anti</td><td>Rows of the first table that have no match; excellent for finding missing items</td></tr>
<tr><td>Full Outer</td><td>All rows from both tables</td></tr>
</tbody>
</table>
<p>After a left merge, filter for nulls in the new columns to find product codes missing from the product list. If a merge matches fewer rows than expected, check for spaces and capitals in the keys, and the types.</p>

<h3>Import a whole folder</h3>
<p>If every month a new file is dropped into the same folder with the same layout, let Power Query read them all.</p>
<ol>
<li>Choose <strong>Data</strong>, <strong>Get Data</strong>, <strong>From File</strong>, <strong>From Folder</strong> and select the folder.</li>
<li>Click <strong>Combine</strong>, then <strong>Combine &amp; Transform Data</strong>.</li>
<li>Choose a sample file and the sheet or Table. Power Query builds a helper query that applies the same steps to every file.</li>
<li>Check the new <code>Source.Name</code> column that tells you which file each row came from; you can extract the month from it.</li>
<li>Close and load.</li>
</ol>
<p>Next month, copy the new file into the folder and press <strong>Refresh All</strong>. Everything updates. Keep only data files in that folder; a stray temporary file such as one beginning with a tilde (~) created when a workbook is open can cause errors, so close files before refreshing, or filter them out in the query.</p>

<h3>Try it yourself</h3>
<ol>
<li>Make a folder with three small CSV files for Jan, Feb and Mar with the same headers.</li>
<li>Import the folder with Combine &amp; Transform Data, then load the result.</li>
<li>Create a small <code>Products</code> list with <code>Product</code> and <code>Category</code>, and merge it into the sales query with a left merge.</li>
<li>Filter for rows with no category to find unmatched products.</li>
<li>Add a fourth CSV file to the folder, refresh and confirm the rows arrive.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Append stacks tables with matching column names; Merge joins by a key.</li>
<li>Left Outer is the usual join; Left Anti finds the missing matches.</li>
<li>From Folder reads every file with one set of steps and updates when new files are added.</li>
<li>Keys must match in type, spaces and capitals.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Unpivot, grouping and refreshing',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to turn wide, report-style tables into tidy ones with Unpivot, summarise data with Group By, and keep queries refreshed reliably. This matters because many files arrive with months across the top, a layout that is easy to read but impossible to analyse until it is reshaped.</p>

<h3>The problem with wide tables</h3>
<p>Imagine a file with the columns <code>Branch</code>, <code>Jan</code>, <code>Feb</code>, <code>Mar</code>, and one row per branch. To build a chart or PivotTable of sales by month you need a column called <code>Month</code> and a column called <code>Sales</code>. <strong>Unpivot</strong> converts columns into rows.</p>

<h3>Unpivot step by step</h3>
<ol>
<li>Load the table into the Power Query Editor.</li>
<li>Select the columns that should stay as they are, here <code>Branch</code>.</li>
<li>Choose <strong>Transform</strong>, then the arrow beside <strong>Unpivot Columns</strong>, then <strong>Unpivot Other Columns</strong>.</li>
<li>Rename the new columns <code>Attribute</code> and <code>Value</code> to <code>Month</code> and <code>Sales</code>.</li>
</ol>
<p>Always use <strong>Unpivot Other Columns</strong> rather than selecting all the month columns. When next quarter adds <code>Apr</code>, <code>May</code> and <code>Jun</code>, the query will include them automatically. Before:</p>
<table>
<thead><tr><th>Branch</th><th>Jan</th><th>Feb</th></tr></thead>
<tbody>
<tr><td>Gulu</td><td>120</td><td>150</td></tr>
<tr><td>Lira</td><td>90</td><td>110</td></tr>
</tbody>
</table>
<p>After:</p>
<table>
<thead><tr><th>Branch</th><th>Month</th><th>Sales</th></tr></thead>
<tbody>
<tr><td>Gulu</td><td>Jan</td><td>120</td></tr>
<tr><td>Gulu</td><td>Feb</td><td>150</td></tr>
<tr><td>Lira</td><td>Jan</td><td>90</td></tr>
<tr><td>Lira</td><td>Feb</td><td>110</td></tr>
</tbody>
</table>
<p>The opposite action is <strong>Pivot Column</strong> (on the Transform tab), which turns values from one column into new headings. Use it sparingly, as a PivotTable usually does that job better.</p>

<h3>Group By</h3>
<p><strong>Group By</strong> (Transform tab) summarises rows, like a PivotTable but inside the query. Select <code>Branch</code>, click <strong>Group By</strong>, choose an operation such as <strong>Sum</strong> of <code>Sales</code>, and name the new column. Use the <strong>Advanced</strong> option to group by several columns or add several calculations. It is useful when you want to load a smaller summary table rather than a huge detail table.</p>

<h3>Refreshing</h3>
<ul>
<li><strong>Data</strong>, <strong>Refresh All</strong> (<strong>Ctrl + Alt + F5</strong>) refreshes every query and PivotTable.</li>
<li>In <strong>Queries &amp; Connections</strong> (Data tab), hover or right-click a query to refresh just that one.</li>
<li>Right-click a connection and choose <strong>Properties</strong> to set <strong>Refresh data when opening the file</strong>, or refresh every n minutes for live sources.</li>
<li>If PivotTables should update after the queries, refresh all, or switch on <strong>Refresh data when opening the file</strong> for them too.</li>
</ul>
<p>Common refresh problems: the source file was moved or renamed; a column was renamed in the source so a step that mentions it fails (the message says "The column ... of the table wasn't found"); a file was open and locked; or the new data has text where a number is expected. Open the query, read the error, and fix the step.</p>

<h3>Privacy levels</h3>
<p>When combining data from different sources, Excel may ask about <strong>Privacy Levels</strong> to stop sensitive data leaking to another source. Treat the prompt seriously. Mark files that belong to your organisation as <strong>Organizational</strong> and public data as <strong>Public</strong>, rather than switching privacy checks off without thinking.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create a CSV with <code>Branch,Jan,Feb,Mar</code> and three branches.</li>
<li>Import it and use Unpivot Other Columns, then rename the columns to Month and Sales.</li>
<li>Close and load it, then build a PivotTable of sales by month.</li>
<li>Add an <code>Apr</code> column to the CSV, refresh, and confirm April appears in the PivotTable after you refresh it.</li>
<li>Use Group By in a copy of the query to get total sales per branch.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Unpivot Other Columns turns report-style columns into tidy rows and survives new columns.</li>
<li>Group By creates summaries inside the query.</li>
<li>Refresh All replays every step on new data.</li>
<li>Most refresh failures come from moved files, renamed columns or locked files.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the main advantage of Power Query over manual cleaning?
- It makes cells look nicer
* The cleaning steps are recorded and repeat automatically on Refresh
- It changes the original file so it is correct
- It removes the need for Tables
= Power Query records steps and replays them, and the source is never altered.

Q: Where do you see and edit the list of steps a query performs?
- The Name Box
- The Quick Access Toolbar
* Applied Steps in the Query Settings pane
- Page Layout view
= Applied Steps list every transformation; you can click, rename, reorder or delete them.

Q: You have twelve monthly files with the same columns in one folder. What is the best method?
- Open each and copy and paste into one sheet
* Get Data, From Folder, then Combine and Transform Data
- Use VLOOKUP across the files
- Merge the queries one by one
= The From Folder connector applies the same steps to all files and picks up new files on refresh.

Q: Which operation stacks two tables that have the same columns into one long table?
- Merge
- Pivot Column
* Append
- Group By
= Append adds rows from one table to another; Merge adds columns by joining on a key.

Q: Which join kind shows the rows of the sales table that have NO match in the product list?
- Inner
- Full Outer
- Right Outer
* Left Anti
= A left anti join returns the rows of the first table without a match in the second, good for finding missing codes.

Q: Why use Unpivot Other Columns instead of selecting the month columns and choosing Unpivot Columns?
- It is faster to type
* New month columns added later are included automatically
- It removes null values
- It changes the data type to text
= Unpivot Other Columns keeps the chosen columns and unpivots everything else, including future columns.

Q: A refresh fails with the message that a column was not found. What is the most likely cause?
- The workbook is too large
- Gridlines are turned on
* A column in the source was renamed or removed, and a query step refers to its old name
- Power Query is not installed
= Steps refer to column names. If the source changes its headings, the step breaks and must be updated.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Data modelling with Power Pivot',
            'summary' => 'Relate several tables in the Data Model, design a star schema, and tell calculated columns from measures.',
            'lessons' => [
                [
                    'title'   => 'The Data Model and relationships',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to load several tables into the Data Model, connect them with relationships and build one PivotTable that uses all of them. This matters because it replaces fragile lookup columns with a proper model: you keep each kind of information in its own table and let Excel connect them.</p>

<h3>What Power Pivot and the Data Model are</h3>
<p>The <strong>Data Model</strong> is a compact database inside your workbook that can hold millions of rows and several related tables. <strong>Power Pivot</strong> is the window used to manage that model and write DAX formulas in it. A PivotTable built on the Data Model can use fields from any related table at once.</p>
<p><strong>Version note.</strong> The Data Model itself is available in Excel 2013 and later for Windows, and in current Microsoft 365 for Mac in a more limited way. The full Power Pivot window (the <strong>Power Pivot</strong> tab) is included with Microsoft 365 for Windows, Excel 2021 and some Office 2019 editions such as Professional Plus, but not in the Home and Student editions. To check, look for a <strong>Power Pivot</strong> tab. If it is missing, go to <strong>File</strong>, <strong>Options</strong>, <strong>Add-ins</strong>, choose <strong>COM Add-ins</strong>, click <strong>Go</strong> and tick <strong>Microsoft Power Pivot for Excel</strong>. If it still does not appear, your edition does not include it. In that case you can still use Power Query, relationships and PivotTables through the Data Model where available, or use lookups to combine tables as in the intermediate course. Excel for the web can show but not build full models.</p>

<h3>Load tables into the model</h3>
<ol>
<li>Prepare your tables, for example <code>Sales</code>, <code>Products</code> and <code>Branches</code>, as Excel Tables or Power Query outputs.</li>
<li>For a Table on a sheet, click inside it and choose <strong>Power Pivot</strong>, then <strong>Add to Data Model</strong>. For a Power Query query, use <strong>Close &amp; Load To</strong> and tick <strong>Add this data to the Data Model</strong>.</li>
<li>Repeat for each table.</li>
</ol>

<h3>Create relationships</h3>
<p>A <strong>relationship</strong> links a column in one table to a matching column in another, such as <code>ProductCode</code>. In the pair, one side holds unique values (the <strong>lookup</strong> table, called <em>one</em> side) and the other can repeat them (the <strong>fact</strong> table, called the <em>many</em> side). Many sales rows share one product, so Products is the one side and Sales is the many side.</p>
<ol>
<li>On the <strong>Data</strong> tab choose <strong>Relationships</strong>, then <strong>New</strong>. (Or open Power Pivot, choose <strong>Diagram View</strong> and drag a column from one table to another.)</li>
<li>Select the many-side table and column (Sales, ProductCode), then the one-side table and column (Products, ProductCode).</li>
<li>Click <strong>OK</strong>. A line appears in Diagram View with a <code>1</code> at the one side and an asterisk at the many side.</li>
</ol>
<p>Rules to remember:</p>
<ul>
<li>The one-side column must contain unique values. Duplicates there cause an error.</li>
<li>Keys must be the same data type in both tables.</li>
<li>A relationship works on one column only. If you need two columns to match, create a combined key column in Power Query.</li>
<li>Only one relationship between two tables is active at a time. Additional ones are dashed and inactive and can be used inside DAX.</li>
</ul>

<h3>Use the model in a PivotTable</h3>
<ol>
<li>Choose <strong>Insert</strong>, <strong>PivotTable</strong>, and select <strong>Use this workbook's Data Model</strong>.</li>
<li>In the field list you will see all the tables. Drag <code>Category</code> from Products to Rows and <code>Amount</code> from Sales to Values.</li>
</ol>
<p>If the PivotTable shows the same total repeated on every row, the relationship is missing or the wrong column was used. Excel often shows a banner offering <strong>Auto-Detect</strong>; check what it creates rather than trusting it blindly.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create Tables <code>Sales</code> (Date, ProductCode, BranchCode, Quantity, Amount), <code>Products</code> (ProductCode, Product, Category) and <code>Branches</code> (BranchCode, Branch, Region).</li>
<li>Add all three to the Data Model.</li>
<li>Create two relationships: Sales to Products and Sales to Branches.</li>
<li>Insert a PivotTable from the Data Model with Region in Rows, Category in Columns and Amount in Values.</li>
<li>Check the total matches the total of the Amount column in Sales.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>The Data Model holds related tables; Power Pivot manages it, and availability depends on your Excel edition.</li>
<li>A relationship joins a many-side column to a unique one-side column of the same type.</li>
<li>Build PivotTables with Use this workbook's Data Model to mix fields from different tables.</li>
<li>Repeated totals mean a missing or wrong relationship.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Star schema basics',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to organise tables into fact and dimension tables in a star schema, and explain why that design keeps a model fast and correct. This matters because the shape of your model decides whether your numbers are right. Most mistakes in advanced reports come from poorly designed tables, not from difficult formulas.</p>

<h3>Facts and dimensions</h3>
<p>A good model separates two kinds of tables.</p>
<table>
<thead><tr><th>Type</th><th>What it holds</th><th>Examples</th></tr></thead>
<tbody>
<tr><td><strong>Fact table</strong></td><td>Events that happened, one row per event, with numbers to add up and keys that point to dimensions</td><td>Sales lines, donations, exam results, attendance records</td></tr>
<tr><td><strong>Dimension table</strong></td><td>Descriptions you use to slice the facts, one row per item, with a unique key</td><td>Products, branches, customers, dates, classes</td></tr>
</tbody>
</table>
<p>In a <strong>star schema</strong> the fact table is in the centre and each dimension table connects directly to it, like the points of a star. Relationships are one-to-many, from each dimension to the fact table. Filters flow from the dimension to the facts: choose a branch in a slicer and only that branch's sales remain.</p>

<h3>Why not one big table?</h3>
<p>A single flat table repeats text on every row, such as product name, category and branch region. It is larger, harder to correct (change a product name in thousands of rows), and slower. Separate dimension tables store each description once. Changing a category name in the Products table changes it everywhere.</p>

<h3>Design steps</h3>
<ol>
<li><strong>Decide the grain</strong> of the fact table: what does one row represent? For example, one product line on one receipt.</li>
<li><strong>List the questions</strong> you want to answer by branch, by category, by month, by customer. Each "by" suggests a dimension.</li>
<li><strong>Create a dimension table for each</strong> with a unique key and descriptive columns. Use Power Query: duplicate the source, keep the key and descriptive columns, and remove duplicates.</li>
<li><strong>Keep only keys and numbers in the fact table</strong>: dates, keys and values such as quantity and amount.</li>
<li><strong>Connect</strong> each dimension to the fact table with a one-to-many relationship.</li>
</ol>

<h3>The date table</h3>
<p>Time calculations need a dedicated <strong>Date table</strong>: one row for every day in the range, with no gaps, and columns such as Year, Month number, Month name and Quarter. Create it in Power Query, or in Power Pivot by choosing <strong>Design</strong>, then <strong>Date Table</strong>, then <strong>New</strong>. Relate its <code>Date</code> column to the date column in the fact table, then mark it: in Power Pivot choose <strong>Design</strong>, <strong>Mark as Date Table</strong>, and choose the date column. Do not use the date column of the fact table itself for time intelligence; it has gaps and duplicates.</p>

<h3>Things to avoid</h3>
<ul>
<li><strong>Many-to-many relationships</strong> between two fact tables. Connect both facts to shared dimensions instead.</li>
<li><strong>Chains of dimensions</strong> (snowflakes) unless needed; flatten them into one dimension where you can.</li>
<li><strong>Blank keys</strong> in the fact table. A sale with no product code appears under a "(Blank)" row; fix at the source or give it an Unknown member.</li>
<li><strong>Hiding keys incorrectly</strong>: hide technical columns from the field list (right-click, <strong>Hide from Client Tools</strong>) so users pick the clean columns.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Take a flat table with Date, Branch, Region, Product, Category, Quantity, Amount.</li>
<li>In Power Query, create <code>Branches</code> and <code>Products</code> dimension tables with unique keys by removing duplicates.</li>
<li>Keep only Date, keys, Quantity and Amount in the fact table.</li>
<li>Create a Date table, relate all three to the fact table and mark the Date table.</li>
<li>Build a PivotTable by Region, Category and Year to confirm the totals still match the original.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Fact tables hold events and numbers; dimension tables hold descriptions with unique keys.</li>
<li>A star schema connects each dimension to the facts with one-to-many relationships.</li>
<li>Always use a proper, marked Date table for time calculations.</li>
<li>Avoid many-to-many links and blank keys.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Calculated columns versus measures',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to create a calculated column and a measure in Power Pivot, and decide which to use. This matters because choosing wrongly gives wrong totals, slow files, or both. The difference is the most important idea in the Data Model.</p>

<h3>Two ways to calculate</h3>
<table>
<thead><tr><th></th><th>Calculated column</th><th>Measure</th></tr></thead>
<tbody>
<tr><td>Calculated</td><td>Row by row when the data is loaded or refreshed</td><td>On demand, for whatever the PivotTable is showing</td></tr>
<tr><td>Stored</td><td>Yes, in the table; uses memory</td><td>No, only the formula is stored</td></tr>
<tr><td>Where it appears</td><td>As a new column you can use in Rows, Columns or Filters</td><td>In the Values area only</td></tr>
<tr><td>Reacts to slicers and filters</td><td>No; the value of each row is fixed</td><td>Yes; recalculated for each cell of the report</td></tr>
<tr><td>Typical use</td><td>Labels and groups such as Age band, Margin category, Full name</td><td>Totals, ratios, averages, year to date</td></tr>
</tbody>
</table>

<h3>Add a calculated column</h3>
<ol>
<li>In the Power Pivot window, open the table in <strong>Data View</strong>.</li>
<li>Click in the first empty column named <strong>Add Column</strong>.</li>
<li>Type a formula in the formula bar, starting with an equals sign, for example:</li>
</ol>
<pre><code>= Sales[Quantity] * Sales[UnitPrice]</code></pre>
<p>It fills the whole column. Rename the column by double-clicking the header. A column can also use related columns from another table with the <code>RELATED</code> function, for example <code>= RELATED(Products[Category])</code>, which brings a value from the one side into the many side.</p>

<h3>Add a measure</h3>
<ol>
<li>In Excel, choose <strong>Power Pivot</strong>, then <strong>Measures</strong>, then <strong>New Measure</strong>.</li>
<li>Choose the table, name the measure (for example <code>Total Sales</code>) and type the formula:</li>
</ol>
<pre><code>=SUM(Sales[Amount])</code></pre>
<p>Then choose the format (such as Number with thousand separators) and click <strong>OK</strong>.</p>
<p>Now drag <code>Total Sales</code> into Values in a PivotTable. It gives the correct total for every row, column and slicer selection. In recent versions you can also right-click a table in the PivotTable Fields list and choose <strong>Add Measure</strong>.</p>

<h3>The classic trap: average of an average</h3>
<p>Suppose you add a calculated column <code>Margin %</code> = Profit divided by Amount for each sale. If you then summarise it as an Average in a PivotTable, you get the average of the row percentages. That is not the margin of the whole business, because small sales count as much as big ones. The right answer is a measure that divides total profit by total sales:</p>
<pre><code>Margin % = DIVIDE(SUM(Sales[Profit]), SUM(Sales[Amount]))</code></pre>
<p>Rule of thumb: <strong>use measures for any number that you will total, divide or compare</strong>, and use calculated columns for text, categories and values you need to put in Rows or Filters.</p>

<h3>Naming and organising</h3>
<ul>
<li>Give measures clear names and group them in a measure table or a single table of your choice.</li>
<li>Use the form <code>Table[Column]</code> for columns and square brackets alone, such as <code>[Total Sales]</code>, for measures. This makes formulas easier to read.</li>
<li>Hide raw numeric columns from the field list once you have measures, so that users do not drag the raw column and see odd totals.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>In your model add a calculated column <code>Line Total</code> to Sales with Quantity times UnitPrice.</li>
<li>Create a measure <code>Total Sales</code> = SUM(Sales[Line Total]).</li>
<li>Create a measure <code>Total Quantity</code> = SUM(Sales[Quantity]).</li>
<li>Build a PivotTable with Category in Rows and both measures in Values.</li>
<li>Add a slicer for Branch and confirm both measures change.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Calculated columns work row by row and are stored; measures are calculated live for each cell of the report.</li>
<li>Use measures for totals, ratios and averages; columns for labels and categories.</li>
<li>Never average percentages; divide totals with a measure.</li>
<li>Name and format measures clearly, and hide raw columns users should not use.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: In a relationship between Products and Sales, which side must contain unique values in the key column?
- The Sales side
* The Products side (the one side)
- Both sides must be unique
- Neither side
= The one side is the lookup table with one row per item. The many side is the fact table, where keys repeat.

Q: A PivotTable from the Data Model shows the same total on every row. What is the most likely cause?
- The PivotTable is too wide
* There is no relationship between the table of the row field and the table of the values
- The data is not sorted
- The workbook is protected
= Without a relationship, the filter from the row field cannot reach the values, so every row shows the overall total.

Q: Which description fits a fact table?
- One row per product with its description
* One row per event, such as a sale, with numbers and keys to dimension tables
- A list of month names
- A table with only text
= Facts record what happened. Dimensions describe the things involved.

Q: Why is a dedicated Date table recommended?
- It makes the file smaller
* Time intelligence needs one row for every day with no gaps, which a fact table date column does not provide
- Excel cannot read dates otherwise
- It replaces the need for relationships
= Functions such as year to date require a complete, unique calendar marked as a Date table.

Q: Which is best done with a calculated column rather than a measure?
- Total sales for the current filter
- Margin of the whole business
* Labelling each customer as Large, Medium or Small so you can put it in Rows
- Year to date sales
= Categories you want to use in Rows or Filters must be columns. Totals and ratios belong in measures.

Q: Why is averaging a Margin % calculated column misleading?
- Averages are never allowed
* It gives equal weight to small and large sales instead of dividing total profit by total sales
- Percent signs cannot be averaged
- Because the column is hidden
= The correct business margin is total profit divided by total sales, which a measure calculates.

Q: Your Excel edition has no Power Pivot tab and cannot be enabled. What is a sensible alternative?
- Stop learning data analysis
- Use only text functions
* Use Power Query and lookups or merged queries to combine tables, and use PivotTables on the result
- Use macros to emulate relationships
= Many analyses can still be done with Power Query merges and normal PivotTables when the full Data Model is unavailable.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: DAX essentials',
            'summary' => 'Write measures with SUM, CALCULATE, FILTER and DIVIDE, understand filter context, and add time intelligence.',
            'lessons' => [
                [
                    'title'   => 'DAX basics and filter context',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write simple DAX measures and explain why the same measure gives different results in different cells. This matters because filter context is the central idea of DAX. Once it is clear, every other function becomes easier.</p>

<h3>What DAX is</h3>
<p><strong>DAX</strong> (Data Analysis Expressions) is the formula language of the Data Model. It looks like Excel formulas, but it works on whole columns and tables rather than single cells. A measure is written as a name, an equals sign and an expression:</p>
<pre><code>Total Sales := SUM(Sales[Amount])</code></pre>
<p>In the Excel dialog you type only the part after the equals sign, and the name goes in the name box. In the text of this course the form <code>Name := expression</code> shows both together.</p>

<h3>Aggregation functions</h3>
<table>
<thead><tr><th>Function</th><th>What it does</th></tr></thead>
<tbody>
<tr><td><code>SUM(Sales[Amount])</code></td><td>Adds a numeric column</td></tr>
<tr><td><code>AVERAGE(Sales[Amount])</code></td><td>Average of a column</td></tr>
<tr><td><code>COUNTROWS(Sales)</code></td><td>Counts the rows of a table, for example the number of sales</td></tr>
<tr><td><code>DISTINCTCOUNT(Sales[CustomerID])</code></td><td>Counts different values, for example unique customers</td></tr>
<tr><td><code>MIN</code>, <code>MAX</code></td><td>Smallest and largest values</td></tr>
<tr><td><code>SUMX(Sales, Sales[Quantity] * Sales[UnitPrice])</code></td><td>Calculates an expression for each row, then adds the results</td></tr>
</tbody>
</table>
<p>The functions ending in <strong>X</strong>, called iterators, go row by row. <code>SUMX</code> is handy when you do not want to add a calculated column first.</p>

<h3>Filter context</h3>
<p>A measure does not have a fixed answer. It is evaluated again for every cell of the PivotTable, using the filters that apply to that cell. These filters come from row and column labels, slicers, and report filters. This is the <strong>filter context</strong>.</p>
<p>Example: <code>Total Sales</code> is placed in Values, Branch is in Rows, and a slicer for Year has 2025 selected. In the cell for Gulu, the filter context is Branch = Gulu and Year = 2025. The measure adds only Amounts of Sales rows that satisfy both. In the Grand Total cell, the Branch filter is absent, so it adds all branches. The formula never changed, only the context.</p>
<p>Because of relationships, a filter on a dimension table (Branches, Products, Dates) flows down to the fact table. A filter on the fact table does not flow back up to the dimensions in the normal one-to-many direction.</p>

<h3>Readable formulas</h3>
<ul>
<li>Use the full column name with its table: <code>Sales[Amount]</code>.</li>
<li>Use only the measure name in square brackets when calling another measure: <code>[Total Sales]</code>. Building measures from other measures keeps the logic in one place.</li>
<li>Press <strong>Alt + Enter</strong> in the formula box to put a long formula on several lines, and indent it.</li>
<li>Add comments with two slashes <code>//</code> at the start of a line.</li>
</ul>

<h3>Examples to build</h3>
<pre><code>Total Sales := SUM(Sales[Amount])
Total Cost := SUM(Sales[Cost])
Profit := [Total Sales] - [Total Cost]
Number of Sales := COUNTROWS(Sales)
Average Sale := DIVIDE([Total Sales], [Number of Sales])</code></pre>
<p>Notice that <code>Profit</code> uses other measures, and <code>Average Sale</code> uses <code>DIVIDE</code>, which returns blank instead of an error if the denominator is zero. Lesson 3 of this module covers it fully.</p>

<h3>Try it yourself</h3>
<ol>
<li>In your model create measures for Total Sales, Total Cost, Profit, Number of Sales and Average Sale as above.</li>
<li>Build a PivotTable with Branch in Rows and all five measures in Values.</li>
<li>Add a slicer for Year and click different years. Explain, in your own words, why the numbers change.</li>
<li>Click a cell in Grand Total and describe its filter context.</li>
<li>Create a measure <code>Unique Products</code> using DISTINCTCOUNT.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>DAX works on columns and tables, and measures are recalculated for every cell.</li>
<li>Filter context comes from row and column labels, slicers and filters.</li>
<li>Filters flow from dimension tables to the fact table across relationships.</li>
<li>Build measures from other measures and write them clearly.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'CALCULATE, FILTER and DIVIDE',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to change the filter context of a measure with CALCULATE, filter with conditions, and calculate safe ratios with DIVIDE. This matters because most useful business questions are comparisons, such as "sales of soap as a share of all sales" or "sales above 100,000 only", and CALCULATE is how you ask them.</p>

<h3>CALCULATE</h3>
<p><code>CALCULATE</code> evaluates an expression in a modified filter context.</p>
<pre><code>CALCULATE( expression, filter1, filter2, ... )</code></pre>
<p>The first argument is usually a measure. The following arguments add or replace filters. Examples:</p>
<pre><code>Soap Sales := CALCULATE([Total Sales], Products[Product] = "Soap")
Gulu Sales := CALCULATE([Total Sales], Branches[Branch] = "Gulu")
Soap in Gulu := CALCULATE([Total Sales], Products[Product] = "Soap", Branches[Branch] = "Gulu")</code></pre>
<p>Several filters are combined with AND. A filter on a column <strong>replaces</strong> any existing filter on that same column. So <code>Soap Sales</code> always shows soap, even in a row labelled Rice. It overrides that row's product filter but still respects other filters such as branch and year.</p>

<h3>FILTER for complex conditions</h3>
<p>The simple form of filter (<code>Column = value</code>) works for one column. When the condition uses a measure, several columns, or "greater than", use <code>FILTER</code>, which returns a table of the rows that pass a test.</p>
<pre><code>Large Sales := CALCULATE([Total Sales], FILTER(Sales, Sales[Amount] &gt; 100000))
High Value Customers := CALCULATE(DISTINCTCOUNT(Sales[CustomerID]), FILTER(Sales, Sales[Amount] &gt;= 500000))</code></pre>
<p><code>FILTER</code> is an iterator that tests every row, so on very large tables it can be slower than a simple column filter. Use the simple form whenever it is enough, and FILTER when you must.</p>

<h3>ALL and REMOVEFILTERS: percentages of the total</h3>
<p><code>ALL</code> removes filters from a table or column, and <code>REMOVEFILTERS</code> does the same job with a clearer name. They allow share-of-total measures:</p>
<pre><code>All Sales := CALCULATE([Total Sales], ALL(Products))
Share of Sales := DIVIDE([Total Sales], [All Sales])</code></pre>
<p>In the Soap row, <code>Total Sales</code> is the soap total, <code>All Sales</code> ignores the product filter and gives the grand total, and the ratio is soap's share. Format <code>Share of Sales</code> as a percentage. To ignore only one column, write <code>ALL(Products[Category])</code>. To ignore everything except the Year slicer, you can use <code>ALLEXCEPT</code>.</p>

<h3>DIVIDE</h3>
<p>Division by zero gives an error. <code>DIVIDE</code> handles it:</p>
<pre><code>DIVIDE(numerator, denominator, [alternate_result])</code></pre>
<p>By default it returns blank when the denominator is zero or blank, which keeps PivotTables clean. You may give a third argument, such as 0. Prefer DIVIDE to the plain <code>/</code> operator in measures.</p>

<h3>Common mistakes</h3>
<ul>
<li>Forgetting that a CALCULATE filter on a column replaces the existing filter on that column, so a slicer for the same column stops working.</li>
<li>Putting a text value without quotation marks, or using single quotes where double are needed.</li>
<li>Using a raw column in CALCULATE as the first argument without an aggregation. The first argument must be an expression that returns one value, like a measure or SUM.</li>
<li>Using FILTER on a huge table when a simple filter would do.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Create <code>Soap Sales</code> (or another product of yours) using CALCULATE.</li>
<li>Create <code>All Sales</code> with ALL on the Products table, then <code>Share of Sales</code> with DIVIDE and format it as a percentage.</li>
<li>Put Product in Rows with Total Sales and Share of Sales in Values. Check that shares add up to 100%.</li>
<li>Create <code>Large Sales</code> with FILTER, using a threshold that suits your data.</li>
<li>Add a Branch slicer and confirm every measure still respects it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>CALCULATE changes filter context; a filter on a column replaces the existing filter on that column.</li>
<li>FILTER handles complex conditions but iterates rows, so use it when needed.</li>
<li>ALL or REMOVEFILTERS gives the denominator for share-of-total measures.</li>
<li>DIVIDE avoids division errors in measures.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Time intelligence basics',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write measures for year to date, same period last year and growth. This matters because almost every report compares periods: "How are we doing this year compared with last year?" DAX has built-in functions that do the date arithmetic for you, provided the model has a proper Date table.</p>

<h3>Before you start: the Date table</h3>
<p>Time intelligence functions need a Date table with continuous dates (every day from the first to the last year in your data), related to the date column of the fact table, and marked as a Date table. If a function returns strange results, check these three things first. The Date table should have at least these columns: <code>Date</code>, <code>Year</code>, <code>Month Number</code>, <code>Month</code> (name) and <code>Quarter</code>. To sort month names in calendar order, select the <code>Month</code> column in Power Pivot and use <strong>Sort by Column</strong> with <code>Month Number</code>.</p>

<h3>Year, quarter and month to date</h3>
<pre><code>Sales YTD := TOTALYTD([Total Sales], 'Date'[Date])
Sales QTD := TOTALQTD([Total Sales], 'Date'[Date])
Sales MTD := TOTALMTD([Total Sales], 'Date'[Date])</code></pre>
<p>These accumulate from the start of the year (or quarter, month) to the last date in the current filter. In a PivotTable with months in Rows, each month shows the running total for the year so far. If your financial year starts on 1 July, add a third argument to TOTALYTD with the year-end date as text, for example <code>"30/6"</code>. Table names containing spaces or that match reserved words are written in single quotes; most model authors name the table <code>Calendar</code> or <code>Dates</code> to avoid this.</p>

<h3>Comparing with last year</h3>
<pre><code>Sales LY := CALCULATE([Total Sales], SAMEPERIODLASTYEAR('Date'[Date]))
Growth := [Total Sales] - [Sales LY]
Growth % := DIVIDE([Growth], [Sales LY])</code></pre>
<p><code>SAMEPERIODLASTYEAR</code> shifts the dates in the current context back one year. For a flexible shift, use <code>DATEADD('Date'[Date], -1, YEAR)</code> and change the number and interval (<code>MONTH</code>, <code>QUARTER</code>, <code>DAY</code>) as needed. A month compared with the previous month is <code>DATEADD('Date'[Date], -1, MONTH)</code>.</p>

<h3>Running totals and rolling periods</h3>
<p>For a rolling twelve-month total, use <code>DATESINPERIOD</code>:</p>
<pre><code>Sales R12M := CALCULATE([Total Sales], DATESINPERIOD('Date'[Date], MAX('Date'[Date]), -12, MONTH))</code></pre>
<p>Rolling measures smooth seasonality and are a common KPI. They need at least twelve months of data before the first full value.</p>

<h3>Display the result well</h3>
<ul>
<li>Format <code>Growth %</code> as a percentage with one decimal place.</li>
<li>Blank is better than zero when no last-year data exists. DIVIDE returns blank for you.</li>
<li>Use month names from the Date table in Rows, and a Year slicer to choose the year.</li>
</ul>

<h3>Common problems</h3>
<ul>
<li><strong>Same value in every month for YTD or LY.</strong> The relationship to the Date table is missing, or the Date table is not marked.</li>
<li><strong>Error that dates are not contiguous.</strong> The Date table has gaps; generate every day.</li>
<li><strong>Using the date column of the fact table</strong> in the function instead of the Date table column.</li>
<li><strong>Incomplete last period.</strong> If the data stops mid-month, YTD and growth compare unequal lengths. Note the cut-off date on your report.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Make sure your model has a marked Date table related to Sales.</li>
<li>Create <code>Sales YTD</code>, <code>Sales LY</code>, <code>Growth</code> and <code>Growth %</code>.</li>
<li>Build a PivotTable with Month from the Date table in Rows and these measures in Values, with a Year slicer.</li>
<li>Check one month by hand against the data.</li>
<li>Format Growth % as a percentage and look at the months with no last-year data.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Time intelligence needs a continuous, marked Date table related to the facts.</li>
<li>TOTALYTD, SAMEPERIODLASTYEAR and DATEADD cover most period comparisons.</li>
<li>Combine them with DIVIDE for growth percentages.</li>
<li>Check results by hand and state the data cut-off date.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Measures for dashboards',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to organise measures for a dashboard and build the typical set: totals, ratios, comparisons, rankings and dynamic titles. This matters because well-designed measures let one model serve many reports, and a user can slice by anything without you rewriting formulas.</p>

<h3>Design a measure set</h3>
<p>Start with a few <strong>base measures</strong> that read directly from the data, then build the others from them.</p>
<pre><code>Total Sales := SUM(Sales[Amount])
Total Cost := SUM(Sales[Cost])
Orders := DISTINCTCOUNT(Sales[ReceiptNo])
Profit := [Total Sales] - [Total Cost]
Margin % := DIVIDE([Profit], [Total Sales])
Avg Order Value := DIVIDE([Total Sales], [Orders])</code></pre>
<p>Then add <strong>comparison measures</strong> (last year, growth, target) and <strong>ranking or share measures</strong>. Keep related measures together in one table and name them for people, not programmers: "Profit Margin %" is better than "PM1".</p>

<h3>Targets and variances</h3>
<p>A target usually lives in its own small table, for example <code>Targets</code> with <code>Year</code>, <code>Branch</code> and <code>Target</code>. Relate it to the same dimension tables, then:</p>
<pre><code>Target := SUM(Targets[Target])
Variance := [Total Sales] - [Target]
Achievement % := DIVIDE([Total Sales], [Target])</code></pre>
<p>Use conditional formatting or icon sets on Achievement % for traffic lights.</p>

<h3>Ranking</h3>
<pre><code>Branch Rank := RANKX(ALL(Branches[Branch]), [Total Sales])</code></pre>
<p><code>RANKX</code> ranks each branch among all branches by sales. <code>ALL</code> is needed so that the ranking considers every branch, not only the one in the current row. Check that a Year slicer still gives sensible ranks.</p>

<h3>Conditional logic and text</h3>
<p>DAX has <code>IF</code> and <code>SWITCH</code>:</p>
<pre><code>Status := IF([Achievement %] &gt;= 1, "On target", IF([Achievement %] &gt;= 0.8, "Close", "Behind"))</code></pre>
<p>A <strong>dynamic title</strong> that follows the slicers makes dashboards friendly:</p>
<pre><code>Selected Year := IF(HASONEVALUE('Date'[Year]), "Sales for " &amp; VALUES('Date'[Year]), "Sales for all years")</code></pre>
<p>Put such a measure in a cell with a PivotTable of one cell, or reference it with <code>CUBEVALUE</code> formulas, then link a chart title to that cell.</p>

<h3>Cube functions for KPI cards</h3>
<p>PivotTable-based KPI cards can be awkward. Excel offers <strong>cube functions</strong> that read a measure from the Data Model directly into any cell. In a PivotTable, choose <strong>PivotTable Analyze</strong>, <strong>OLAP Tools</strong>, <strong>Convert to Formulas</strong>, and Excel writes <code>CUBEVALUE</code> formulas. You can then edit them to read a measure for chosen filters, and make them respond to slicers connected to the model. Use this technique carefully and test it, as the syntax is long and the connection name is "ThisWorkbookDataModel".</p>

<h3>Good practice</h3>
<ul>
<li>Format every measure (currency, percentage, thousand separators) in its definition, not in each PivotTable.</li>
<li>Hide helper columns and key columns from the field list.</li>
<li>Test each measure with a known small example and check the total against the source.</li>
<li>Add a description to each measure explaining what it means.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Build the six base measures on your model.</li>
<li>Add a <code>Targets</code> table with a yearly target per branch and relate it to Branches and Date.</li>
<li>Create Target, Variance and Achievement % measures.</li>
<li>Add Branch Rank using RANKX with ALL, and a Status measure with IF.</li>
<li>Build one PivotTable showing them all by branch, with a Year slicer, and format it with icon sets.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Build a small base set of measures and derive the others from them.</li>
<li>Targets belong in their own table related to the same dimensions.</li>
<li>RANKX with ALL ranks across every item; IF and SWITCH give status labels.</li>
<li>Format, name and test each measure so the model is trustworthy.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is filter context?
- The font and colour of a cell
- The list of columns in a table
* The set of filters from row and column labels, slicers and filters that applies to a particular cell when a measure is evaluated
- The order of the worksheets
= A measure is recalculated for every cell using that cell's filters, which is why the same formula gives different answers.

Q: What does this measure do? Soap Sales := CALCULATE([Total Sales], Products[Product] = "Soap")
- Adds all products and subtracts soap
* Calculates total sales with the product filter set to Soap, replacing any existing product filter
- Counts the number of soap rows
- Only works when Soap is in Rows
= CALCULATE changes the filter context. A filter on a column replaces the existing filter on that column.

Q: Which function lets you calculate a share of the grand total across all products?
- SUMX
* CALCULATE with ALL on the Products table
- DISTINCTCOUNT
- TOTALYTD
= ALL removes the product filter, so the denominator is the total across all products.

Q: Why prefer DIVIDE over the / operator in a measure?
- DIVIDE is faster to type
* It handles a zero or blank denominator without an error
- The / operator is not allowed in DAX
- It rounds the result
= DIVIDE returns blank (or an alternative value) when the denominator is zero, keeping reports clean.

Q: You use TOTALYTD but every month shows the same value. What should you check first?
- The font of the Date table
* That the Date table is related to the fact table and marked as a Date table
- That Excel is in full-screen mode
- That the measure name has no spaces
= Time intelligence depends on a marked, continuous Date table related to the data.

Q: Which function compares a measure with the same period last year?
- ALLEXCEPT
- RANKX
- COUNTROWS
* CALCULATE with SAMEPERIODLASTYEAR
= SAMEPERIODLASTYEAR shifts the dates in the current context back one year inside CALCULATE.

Q: Why is ALL used inside RANKX(ALL(Branches[Branch]), [Total Sales])?
- To sort the PivotTable
- To remove blank rows
* So each branch is ranked among all branches rather than only among the branch of the current row
- To convert text to numbers
= Without ALL, the ranking list would be filtered to the current row's branch and every rank would be 1.

Q: What is a good practice for a measure used in several dashboards?
- Copy the formula into every PivotTable
* Define, name and format it once in the model and reuse it
- Use a different name each time
- Leave it unformatted
= Defining measures in the model keeps the logic in one place and the results consistent.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Advanced formulas and dynamic arrays',
            'summary' => 'Use FILTER, SORT, UNIQUE, SEQUENCE and LET, build lookup patterns, and handle errors, with alternatives for older versions.',
            'lessons' => [
                [
                    'title'   => 'Dynamic arrays: FILTER, SORT and UNIQUE',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a single formula that returns a whole list, and explain how spilling works. This matters because tasks that once needed helper columns, copying and filters, such as "list every sale above 100,000, sorted by size", can now be done with one live formula that updates by itself.</p>

<h3>Version note</h3>
<p><strong>Dynamic array functions</strong> are available in Microsoft 365, Excel 2021 and Excel for the web. They do <strong>not</strong> exist in Excel 2019 or older. If a workbook with these formulas is opened in an older version, they appear as static values or errors. If you use an older version, see "Alternatives" below and the earlier PivotTable and lookup lessons.</p>

<h3>Spilling</h3>
<p>A dynamic array formula returns several values, which <strong>spill</strong> into the cells below and to the right. You type the formula in one cell only. The spilled range has a thin blue border when selected. Refer to the whole spill with the hash sign: <code>=SUM(E2#)</code> adds everything that the formula in E2 spills. If something blocks the spill, such as typed text in a cell in the way, Excel shows <code>#SPILL!</code>. Clear the blocking cells. Spilled results cannot be typed over; edit the formula in its first cell.</p>

<h3>FILTER</h3>
<pre><code>=FILTER(array, include, [if_empty])</code></pre>
<p>It returns the rows that meet a condition. With a Table named <code>Sales</code>:</p>
<pre><code>=FILTER(Sales, Sales[Branch]="Gulu", "No sales")
=FILTER(Sales, (Sales[Branch]="Gulu")*(Sales[Amount]&gt;100000))
=FILTER(Sales, (Sales[Branch]="Gulu")+(Sales[Branch]="Lira"))</code></pre>
<p>Use <code>*</code> for AND and <code>+</code> for OR. The conditions give arrays of TRUE and FALSE, and multiplication or addition combines them as 1 and 0. Choose columns by passing only those columns: <code>=FILTER(Sales[[Date]:[Amount]], ...)</code>, or use <code>CHOOSECOLS</code> in Microsoft 365. The third argument sets the message when nothing matches, instead of <code>#CALC!</code>.</p>

<h3>SORT and SORTBY</h3>
<pre><code>=SORT(array, [sort_index], [sort_order], [by_col])
=SORTBY(array, by_array1, [order1], ...)</code></pre>
<p><code>sort_order</code> is 1 for ascending and -1 for descending. Combine with FILTER to get a sorted, filtered report:</p>
<pre><code>=SORT(FILTER(Sales, Sales[Branch]="Gulu"), 5, -1)</code></pre>
<p>This returns Gulu's sales sorted by the fifth column, largest first. SORTBY is clearer when sorting by a column that is not in the output.</p>

<h3>UNIQUE</h3>
<pre><code>=UNIQUE(Sales[Branch])
=UNIQUE(Sales[[Branch]:[Product]])
=UNIQUE(Sales[Branch], FALSE, TRUE)</code></pre>
<p>The first gives each branch once. The second gives unique combinations of two columns. The third (exactly once) returns values that occur only once. Together with SORT, <code>=SORT(UNIQUE(Sales[Branch]))</code> gives an alphabetical list. Use the result to drive other formulas: <code>=SUMIFS(Sales[Amount], Sales[Branch], E2#)</code> spills a total beside each unique branch, forming a small summary table that grows by itself.</p>

<h3>Alternatives for older versions</h3>
<table>
<thead><tr><th>Need</th><th>Older approach</th></tr></thead>
<tbody>
<tr><td>Unique list</td><td>Remove Duplicates on a copy, a PivotTable, or Advanced Filter with Unique records only</td></tr>
<tr><td>Filtered list</td><td>Table filter buttons, Advanced Filter to a new location, or a PivotTable</td></tr>
<tr><td>Sorted list</td><td>Data, Sort on a copy</td></tr>
<tr><td>Live summary</td><td>PivotTable or Power Query, which also work in older versions</td></tr>
</tbody>
</table>

<h3>Try it yourself</h3>
<ol>
<li>Use your Sales Table and type <code>=UNIQUE(Sales[Branch])</code> in an empty cell. Watch it spill.</li>
<li>In the next column use <code>=SUMIFS(Sales[Amount], Sales[Branch], E2#)</code>, adjusting E2 to your first cell.</li>
<li>In another area type a FILTER for one branch with a message for no results.</li>
<li>Wrap it with SORT to put the largest amounts first.</li>
<li>Add a new branch to the Table and see the lists grow.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Dynamic arrays spill; refer to the whole result with the hash sign.</li>
<li>FILTER uses * for AND and + for OR, and takes a message for no results.</li>
<li>SORT, SORTBY and UNIQUE combine with FILTER for live reports.</li>
<li>Older Excel needs PivotTables, Power Query or Advanced Filter instead.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'SEQUENCE, LET and array thinking',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to generate number and date series with SEQUENCE, name parts of a formula with LET, and think in arrays instead of single cells. This matters because formulas become shorter, faster and easier to check, and you can build a month list or a rank column with one formula instead of filling down.</p>

<h3>Version note</h3>
<p><code>SEQUENCE</code> and <code>LET</code> need Microsoft 365 or Excel 2021. In Excel 2019 and older, use the fill handle, <code>ROW()</code> based formulas, or helper cells for the same results.</p>

<h3>SEQUENCE</h3>
<pre><code>=SEQUENCE(rows, [columns], [start], [step])</code></pre>
<table>
<thead><tr><th>Formula</th><th>Result</th></tr></thead>
<tbody>
<tr><td><code>=SEQUENCE(5)</code></td><td>1, 2, 3, 4, 5 down a column</td></tr>
<tr><td><code>=SEQUENCE(1,5)</code></td><td>1 to 5 across a row</td></tr>
<tr><td><code>=SEQUENCE(12,1,1,1)</code></td><td>Numbers 1 to 12, for months</td></tr>
<tr><td><code>=SEQUENCE(6,1,10,5)</code></td><td>10, 15, 20, 25, 30, 35</td></tr>
</tbody>
</table>
<p>Because dates are numbers, you can build a calendar. A list of the first day of each month of a year, starting at the date in B1, is:</p>
<pre><code>=EDATE(B1, SEQUENCE(12,1,0,1))</code></pre>
<p>A table of all dates in a year is <code>=SEQUENCE(365,1,DATE(2025,1,1),1)</code>. Use <code>SEQUENCE(ROWS(Sales))</code> to number the rows of a table.</p>

<h3>Array thinking</h3>
<p>Normal formulas give one answer per cell. Array formulas work on a whole set of values at once and can return a set. A few ideas help:</p>
<ul>
<li>A comparison on a range, such as <code>Sales[Amount]&gt;100000</code>, returns a column of TRUE and FALSE.</li>
<li>Arithmetic on two columns works row by row: <code>Sales[Quantity]*Sales[Price]</code> returns a column of line totals without a helper column.</li>
<li>Functions such as SUM, MAX and COUNT collapse an array to one number: <code>=SUM(Sales[Quantity]*Sales[Price])</code> gives total revenue directly.</li>
<li>To count how many values pass a test, multiply and add: <code>=SUM((Sales[Branch]="Gulu")*(Sales[Amount]&gt;50000))</code>.</li>
</ul>
<p>In older Excel versions, array formulas had to be entered with <strong>Ctrl + Shift + Enter</strong> and showed curly brackets. In dynamic array Excel, Enter is enough.</p>

<h3>LET: name your calculations</h3>
<pre><code>=LET(name1, value1, name2, value2, ..., calculation)</code></pre>
<p>LET stores intermediate results under names, so you can reuse them and read the formula like a recipe. For example, the share of the top branch:</p>
<pre><code>=LET(
  totals, SUMIFS(Sales[Amount], Sales[Branch], UNIQUE(Sales[Branch])),
  best,   MAX(totals),
  best / SUM(totals)
)</code></pre>
<p>The names <code>totals</code> and <code>best</code> exist only inside this formula. Without LET you would repeat the SUMIFS part twice, which is harder to read and slower. Press <strong>Alt + Enter</strong> inside the formula bar to break lines and indent. Choose names that start with a letter, contain no spaces, and do not look like cell references (so <code>x1</code> is not allowed, but <code>total_1</code> is).</p>

<h3>Other useful array functions</h3>
<ul>
<li><code>TAKE</code> and <code>DROP</code> keep or remove the first or last rows (Microsoft 365).</li>
<li><code>HSTACK</code> and <code>VSTACK</code> combine arrays side by side or one above another (Microsoft 365).</li>
<li><code>TEXTSPLIT</code> splits text into cells; <code>TEXTJOIN</code> joins many cells with a separator (<code>TEXTJOIN</code> is in Excel 2019 as well).</li>
<li><code>LAMBDA</code> lets advanced users create their own reusable functions, in Microsoft 365.</li>
</ul>
<p>Functions that appear only in the latest releases may not be on every computer, so check before sharing a file widely.</p>

<h3>Try it yourself</h3>
<ol>
<li>Use <code>=SEQUENCE(12,1,1,1)</code> to produce numbers 1 to 12, then convert to month names with <code>=TEXT(DATE(2025,SEQUENCE(12),1),"mmmm")</code>.</li>
<li>Type <code>=SUM(Sales[Quantity]*Sales[Price])</code> and compare with a column of line totals.</li>
<li>Write the "share of top branch" formula with LET, adjusting names to your Table.</li>
<li>Break it onto several lines using Alt + Enter.</li>
<li>Explain in one sentence what each name in LET stands for.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>SEQUENCE creates numbers and dates without filling down.</li>
<li>Array thinking: one formula can work on whole columns and return one value or many.</li>
<li>LET names intermediate results, making formulas readable and faster.</li>
<li>These features need Microsoft 365 or Excel 2021; plan alternatives for older versions.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Lookup patterns and error handling',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use advanced lookup patterns, such as two-way and multi-criteria lookups, and handle errors in a deliberate way. This matters because real lookups are rarely simple. A price may depend on both product and size, and bad data should produce clear messages, not a screen of <code>#N/A</code>.</p>

<h3>XLOOKUP patterns</h3>
<p>Remember the form <code>=XLOOKUP(value, lookup_array, return_array, [if_not_found], [match_mode], [search_mode])</code>. Useful patterns:</p>
<ul>
<li><strong>Return several columns:</strong> make the return array wider, for example <code>=XLOOKUP(A2, Products[Code], Products[[Product]:[Cost]])</code> spills across.</li>
<li><strong>Last match instead of first:</strong> set search mode to <code>-1</code> to search from the bottom, handy for the latest price. <code>=XLOOKUP(A2, Prices[Item], Prices[Price], "n/a", 0, -1)</code>.</li>
<li><strong>Nearest value or band:</strong> match mode <code>-1</code> (exact or next smaller) or <code>1</code> (exact or next larger).</li>
<li><strong>Wildcard match:</strong> match mode <code>2</code> allows <code>*</code> and <code>?</code>, so <code>"Gu*"</code> finds Gulu.</li>
</ul>

<h3>Lookup with two criteria</h3>
<p>Suppose a price depends on both Product and Size. One approach joins the columns in the lookup. With dynamic arrays:</p>
<pre><code>=XLOOKUP(1, (Prices[Product]=A2)*(Prices[Size]=B2), Prices[Price], "Not found")</code></pre>
<p>The multiplication produces 1 only where both conditions are true, and XLOOKUP finds the first 1. The classic alternative that works in every version is a helper key column in the data, such as <code>=[@Product]&amp;"|"&amp;[@Size]</code>, then a lookup on the combined key. Use a separator that cannot appear in the data, so that "AB" and "C" is different from "A" and "BC". Or use SUMIFS when there is exactly one matching row and the result is a number: <code>=SUMIFS(Prices[Price], Prices[Product], A2, Prices[Size], B2)</code>. It returns 0, not an error, for no match, so use it with care.</p>

<h3>Two-way lookup</h3>
<p>To find a value at the crossing of a row and a column, such as a price by product and month, nest two lookups:</p>
<pre><code>=INDEX(PriceGrid, MATCH(A2, ProductList, 0), MATCH(B2, MonthHeaders, 0))</code></pre>
<p>This works in every version. In Microsoft 365: <code>=XLOOKUP(A2, ProductList, XLOOKUP(B2, MonthHeaders, PriceGrid))</code>.</p>

<h3>Error handling strategy</h3>
<ol>
<li><strong>Understand the error first.</strong> Use Formulas, then <strong>Evaluate Formula</strong>, to step through a formula, or <strong>Error Checking</strong> and <strong>Trace Precedents</strong>.</li>
<li><strong>Prevent the error</strong> when you can: data validation, clean keys, a Table instead of a fixed range.</li>
<li><strong>Handle the expected error only.</strong> Use <code>IFNA</code> for lookups, because it hides only <code>#N/A</code>. Use <code>IFERROR</code> when you really mean any error, and say so in your documentation.</li>
<li><strong>Return a visible, honest result</strong> such as "Missing price" or a blank, not a silent 0 that gets added into totals.</li>
<li><strong>Count the problems.</strong> A check cell such as <code>=SUMPRODUCT(--ISNA(Orders[Price]))</code> or <code>=COUNTIF(Orders[Status],"Missing price")</code> tells the reader how many rows need attention.</li>
</ol>

<h3>Errors in dynamic arrays</h3>
<p>Spill errors (<code>#SPILL!</code>) mean the output range is blocked. <code>#CALC!</code> means an empty result with no message set, as in FILTER with no matches. <code>#VALUE!</code> often means two arrays of different sizes. Errors can also be removed from a list with <code>=FILTER(range, NOT(ISERROR(range)))</code>.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create a price table with Product, Size and Price, with the same product in two sizes.</li>
<li>Write a lookup with two criteria for a product and a size, using XLOOKUP or a helper key.</li>
<li>Make it show "Missing price" when the pair is not found, using IFNA.</li>
<li>Add a check cell that counts how many orders show "Missing price".</li>
<li>Build a small month-by-product grid and use INDEX with two MATCH functions.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>XLOOKUP can return many columns, search from the bottom, and match nearest values or wildcards.</li>
<li>Multi-criteria lookups use multiplied conditions, a helper key column, or SUMIFS for numbers.</li>
<li>Prevent errors first; handle only the expected one, and prefer IFNA for lookups.</li>
<li>Show honest messages and count problem rows in a check cell.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: In which versions of Excel are dynamic array functions such as FILTER and UNIQUE available?
- Every version since Excel 2007
* Microsoft 365, Excel 2021 and Excel for the web
- Only Excel for Mac
- Only Excel 2019
= Dynamic arrays arrived with Microsoft 365 and Excel 2021. Excel 2019 and older do not have them.

Q: What does the hash sign in =SUM(E2#) mean?
- A comment
* The whole spilled range produced by the formula in E2
- A text format
- A named range called E2
= The # (spill range operator) refers to the entire output of a dynamic array formula.

Q: Which formula returns Gulu rows with an amount above 100000?
- =FILTER(Sales, Sales[Branch]="Gulu" AND Sales[Amount]>100000)
* =FILTER(Sales, (Sales[Branch]="Gulu")*(Sales[Amount]>100000))
- =FILTER(Sales, Sales[Branch]="Gulu", Sales[Amount]>100000)
- =IF(Sales, "Gulu")
= Multiply conditions for AND (and add them for OR) inside the include argument of FILTER.

Q: What do you see if a spilled formula has typed data in the way?
- #NAME?
* #SPILL!
- #DIV/0!
- Nothing; Excel overwrites the data
= Excel will not overwrite existing cells; clear them to let the formula spill.

Q: What is the main benefit of LET?
- It protects the sheet
* It names intermediate results so formulas are readable and avoid repeating calculations
- It creates drop-down lists
- It works in all older versions
= LET lets you name parts of a calculation and reuse them within one formula.

Q: What is a risk of IFERROR around a whole formula?
- It slows the file
* It hides unexpected errors as well as the one you expected
- It makes numbers into text
- It only works with dates
= Prefer IFNA for lookups, and wrap only the part that can fail for a known reason.

Q: You need the latest price for each item in a list where items appear several times. Which XLOOKUP setting helps?
- Match mode 2
* Search mode -1 to search from the last entry upwards
- Sort the lookup array by name
- Use a wildcard
= Search mode -1 starts at the bottom, so the first match found is the most recent entry.

Q: A user has Excel 2019 and needs a unique list of branches. What is a suitable method?
- UNIQUE function
- SEQUENCE function
* Copy the column and use Remove Duplicates, or build a PivotTable
- LET function
= UNIQUE is not available in Excel 2019, so use Remove Duplicates, a PivotTable or Advanced Filter.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Automation and quality',
            'summary' => 'Record macros, understand VBA and Office Scripts, protect and document workbooks, tune performance and deliver a capstone project.',
            'lessons' => [
                [
                    'title'   => 'Macros, VBA and Office Scripts',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to record a simple macro, run it safely, and explain what VBA and Office Scripts are and when to use each. This matters because repeating the same ten clicks every week is slow and causes mistakes. A macro can do them in a second, but it also carries risks that you must understand.</p>

<h3>What a macro is</h3>
<p>A <strong>macro</strong> is a recorded or written set of instructions that Excel can repeat. Excel for Windows and Mac saves macros in <strong>VBA</strong> (Visual Basic for Applications), a programming language built into Office. A workbook containing macros must be saved as <strong>Excel Macro-Enabled Workbook (.xlsm)</strong>; a normal .xlsx file cannot store them.</p>

<h3>Show the Developer tab</h3>
<ol>
<li>Choose <strong>File</strong>, then <strong>Options</strong>, then <strong>Customize Ribbon</strong>.</li>
<li>On the right, tick <strong>Developer</strong> and click <strong>OK</strong>.</li>
</ol>

<h3>Record a macro</h3>
<ol>
<li>Prepare the task in your mind. A good first one: format a report header (bold, fill colour, centre alignment).</li>
<li>Choose <strong>Developer</strong>, then <strong>Record Macro</strong>. Give it a name without spaces, such as <code>FormatHeader</code>, optionally assign a shortcut like <strong>Ctrl + Shift + H</strong>, and store it in <strong>This Workbook</strong>.</li>
<li>Click <strong>OK</strong> and carry out the steps slowly and exactly.</li>
<li>Choose <strong>Stop Recording</strong>.</li>
<li>To run it, choose <strong>Developer</strong>, <strong>Macros</strong>, select the name and click <strong>Run</strong>, or use the shortcut.</li>
</ol>
<p>Use <strong>Use Relative References</strong> (in the Developer tab, before recording) when the macro should work from wherever the cursor is rather than always on the same cells. A recorded macro repeats your exact actions, including mistakes, so rehearse first and keep a copy of the file.</p>

<h3>Look at the code</h3>
<p>Press <strong>Alt + F11</strong> to open the VBA editor. In the Project window open <strong>Modules</strong>, then <strong>Module1</strong>. You will see code like this:</p>
<pre><code>Sub FormatHeader()
    With Selection
        .Font.Bold = True
        .Interior.Color = RGB(0, 90, 70)
        .Font.Color = RGB(255, 255, 255)
        .HorizontalAlignment = xlCenter
    End With
End Sub</code></pre>
<p>Even without learning VBA, you can read the code, remove unneeded lines (recorders add many), and change values. Learning VBA fully, with loops, conditions and variables, lets you automate tasks that cannot be recorded, such as processing many files.</p>

<h3>Macro security</h3>
<ul>
<li>Macros can contain harmful code. Microsoft blocks macros in files downloaded from the internet by default, and you see a security message. Do not enable macros in a file from a stranger or an unexpected email.</li>
<li>Only run macros from sources you trust and understand. Keep the default Trust Center setting that disables macros with notification: <strong>File</strong>, <strong>Options</strong>, <strong>Trust Center</strong>.</li>
<li>Share macro files within a team in a trusted location, not as random email attachments.</li>
</ul>

<h3>Office Scripts</h3>
<p><strong>Office Scripts</strong> are the newer, cloud-based way to automate Excel. They are written in <strong>TypeScript</strong> (a form of JavaScript), recorded or edited from the <strong>Automate</strong> tab, and saved in OneDrive, which works for workbooks in Excel for the web. They can be run on a schedule or triggered by Power Automate, so a report can refresh each morning without anyone opening Excel. They require a suitable Microsoft 365 business or education licence with the feature turned on by the administrator, so they may not be available to everyone, and they do not run VBA.</p>
<table>
<thead><tr><th></th><th>VBA macros</th><th>Office Scripts</th></tr></thead>
<tbody>
<tr><td>Works in</td><td>Desktop Excel (Windows, partly Mac)</td><td>Excel for the web, with Microsoft 365 licence</td></tr>
<tr><td>Language</td><td>VBA</td><td>TypeScript</td></tr>
<tr><td>File type</td><td>.xlsm</td><td>.xlsx; script saved in OneDrive</td></tr>
<tr><td>Strength</td><td>Very powerful on the desktop, huge history</td><td>Cloud automation, flows with Power Automate</td></tr>
</tbody>
</table>
<p>Often you do not need either. Power Query and Refresh All automate data work with no code, and formulas and Tables update by themselves. Reach for a macro only when a repetitive manual process remains.</p>

<h3>Try it yourself</h3>
<ol>
<li>Show the Developer tab.</li>
<li>Record a macro named <code>FormatHeader</code> that formats the selected row as a header.</li>
<li>Type some words in a new row and run the macro on it.</li>
<li>Press Alt + F11 and read the code. Change one colour value and run again.</li>
<li>Save the workbook as .xlsm and note the security prompt when you reopen it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A macro repeats recorded actions; macros are stored in VBA and need the .xlsm format.</li>
<li>Rehearse before recording, and use relative references when position varies.</li>
<li>Enable macros only from trusted sources, and keep the default security settings.</li>
<li>Office Scripts automate Excel for the web with TypeScript, depending on licence; no-code tools such as Power Query come first.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Protecting, documenting and tuning workbooks',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to protect a model against accidents, document it so others can use it, keep sensible versions, and speed up a slow workbook. This matters because an analysis model is often used for years and by people other than its author. Poor habits cost time, and wrong numbers cost trust.</p>

<h3>Protecting workbooks</h3>
<ul>
<li><strong>Lock formulas, unlock inputs.</strong> Unlock input cells (<strong>Ctrl + 1</strong>, <strong>Protection</strong>, untick <strong>Locked</strong>), then <strong>Review</strong>, <strong>Protect Sheet</strong>. Protection prevents accidental overwriting of formulas.</li>
<li><strong>Protect the structure</strong> (<strong>Review</strong>, <strong>Protect Workbook</strong>) to stop sheets being deleted or moved.</li>
<li><strong>Encrypt with a password to open</strong>: <strong>File</strong>, <strong>Info</strong>, <strong>Protect Workbook</strong>, <strong>Encrypt with Password</strong>. This is real encryption, and a lost password cannot be recovered, so store it safely.</li>
<li>Remember that sheet protection passwords are weak; they stop accidents, not a determined person. Never store confidential data in a workbook you believe is safe because of sheet protection. Use proper file permissions in OneDrive, SharePoint or your storage.</li>
</ul>

<h3>Document the model</h3>
<p>A model that nobody understands is a risk. Add these items:</p>
<ol>
<li>A first sheet named <code>ReadMe</code> with the purpose, author, date, version, data sources, how to refresh and known limits.</li>
<li>A <strong>sheet map</strong>: which sheet holds inputs, calculations and outputs.</li>
<li>A short <strong>assumptions list</strong>, such as exchange rate used, definitions of KPIs and cut-off dates.</li>
<li>Clear names for sheets, Tables, queries and measures. Add descriptions to measures.</li>
<li>Cell comments or notes only where a formula needs explanation, and colour conventions (for example blue font for inputs, black for formulas), explained on the ReadMe sheet.</li>
</ol>

<h3>Version habits</h3>
<ul>
<li>Do not name files "final", "final2" and "final-NEW". Use dates and version numbers: <code>Sales-Model_v1.3_2025-06-30.xlsx</code>.</li>
<li>Keep a <strong>change log</strong> on the ReadMe sheet: date, who, what changed.</li>
<li>Store working files in OneDrive or SharePoint where <strong>Version History</strong> (<strong>File</strong>, <strong>Info</strong>) lets you restore earlier copies. Keep a monthly archived copy of finished reports.</li>
<li>Make a copy before major changes, and test changes on the copy.</li>
<li>Separate the <strong>input</strong> files (source data) from the <strong>model</strong> workbook, so a new month of data does not mean editing formulas.</li>
</ul>

<h3>Check your numbers</h3>
<p>Build <strong>checks</strong> into the model. Examples: a cell that compares the total of a PivotTable with the total of the source, a count of rows with missing keys, a test that percentages add to 100%. Display "OK" or a red "CHECK" message on the dashboard. Use <strong>Formulas</strong>, <strong>Evaluate Formula</strong> and <strong>Trace Precedents</strong> to inspect tricky calculations. Also ask someone else to review an important model before it is used for decisions.</p>

<h3>Performance tips</h3>
<ul>
<li><strong>Avoid whole-column references</strong> in heavy formulas; use Tables.</li>
<li><strong>Reduce volatile functions</strong> such as <code>OFFSET</code>, <code>INDIRECT</code>, <code>TODAY</code> and <code>NOW</code>, which recalculate every time. Use INDEX or Tables instead.</li>
<li><strong>Load only what you need</strong> in Power Query: remove unused columns and filter rows early, before merges.</li>
<li><strong>Prefer the Data Model and measures</strong> over thousands of lookup formulas for large data.</li>
<li><strong>Remove unused columns</strong> from the Data Model, and prefer low-cardinality columns; very large text columns use much memory.</li>
<li>Set <strong>Formulas</strong>, <strong>Calculation Options</strong> to <strong>Manual</strong> temporarily while building, then back to Automatic, and press <strong>F9</strong> to recalculate.</li>
<li>Delete unused formatting and objects. <strong>File</strong> size with .xlsb (binary) can be smaller, but .xlsx is easier to share and safer.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Add a <code>ReadMe</code> sheet to a workbook you built in this course with purpose, sources, refresh instructions and a change log.</li>
<li>Add a check cell that compares a PivotTable grand total with the source total and shows "OK" or "CHECK".</li>
<li>Unlock the input cells, protect the sheet, and test that formulas cannot be typed over.</li>
<li>Save a new version of the file using a dated name.</li>
<li>List three performance problems you might meet in your own workbooks and how you would address each.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Protection prevents accidents; proper permissions and encryption protect confidential files.</li>
<li>A ReadMe sheet, clear names and a change log make a model usable by others.</li>
<li>Built-in checks and a second reviewer catch errors before decisions are made.</li>
<li>Load less data, avoid volatile and whole-column formulas, and use the Data Model for scale.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Capstone project: a complete reporting model',
                    'minutes' => 30,
                    'body'    => <<<'HTML'
<p>After this lesson you will have built a complete, refreshable reporting model that uses the main tools of this course: Power Query, a star schema Data Model, DAX measures, dynamic arrays, protection and documentation. This matters because it is a portfolio piece that shows an employer you can deliver a reliable monthly report from raw files.</p>

<h3>The brief</h3>
<p>A small chain of shops, or a school network, receives one CSV or Excel file of transactions each month from each branch. Management wants a monthly report with total sales, profit, margin, comparison with last year and with target, ranking of branches, and the top products. Anyone should be able to update it by dropping the new files in a folder and pressing Refresh All. Use real data you may legitimately use, or invent about 300 rows across three months and three branches. If you use a school, replace sales with fees collected, or marks.</p>

<h3>Step 1: Set up folders and data</h3>
<ol>
<li>Create a folder named <code>Report</code> with subfolders <code>Data</code> and <code>Archive</code>.</li>
<li>Put the monthly transaction files in <code>Data</code>. Create two small lists: <code>Products</code> (code, product, category, cost price) and <code>Branches</code> (code, branch, region), and a <code>Targets</code> list.</li>
</ol>

<h3>Step 2: Power Query</h3>
<ol>
<li>Import the <code>Data</code> folder with <strong>From Folder</strong> and combine the files. Remove title rows, set data types, trim text and handle errors.</li>
<li>Import the Products, Branches and Targets lists as separate queries.</li>
<li>Build dimension tables with unique keys, create a Date table from the minimum to maximum date, and keep the transaction table as a lean fact table with keys and numbers.</li>
<li>Load all queries to the Data Model only (connection plus Data Model), and keep staging queries as connections only.</li>
</ol>

<h3>Step 3: Model</h3>
<ol>
<li>Open the Diagram View and create one-to-many relationships from each dimension to the fact table, and from Targets to Branches and Date as suitable.</li>
<li>Mark the Date table. Hide key columns and raw numeric columns from the field list.</li>
</ol>

<h3>Step 4: Measures</h3>
<p>Create, in one measure table, with formats and descriptions:</p>
<ul>
<li>Total Sales, Total Cost, Profit, Margin %, Orders, Avg Order Value.</li>
<li>Sales YTD, Sales LY, Growth %.</li>
<li>Target, Achievement %, Branch Rank.</li>
</ul>

<h3>Step 5: Report sheet</h3>
<ol>
<li>Insert PivotTables from the Data Model: sales by month with YTD and last year, branch performance with target and rank, and category share.</li>
<li>Add PivotCharts, with message titles. Add slicers for Year, Region and Category connected to all PivotTables.</li>
<li>Use a dynamic array (if available) to show the top 5 products: for example <code>=TAKE(SORTBY(UNIQUE(Fact[Product]), ...), 5)</code> is possible in Microsoft 365, or use a PivotTable with a Top 10 value filter in any version.</li>
<li>Add KPI cards linked to PivotTable cells or cube formulas.</li>
</ol>

<h3>Step 6: Quality and delivery</h3>
<ol>
<li>Add check cells: the PivotTable total equals the source total; the number of fact rows without a product or branch is zero.</li>
<li>Add the <code>ReadMe</code> sheet with sources, refresh steps, definitions of every measure, version and change log.</li>
<li>Protect the report sheet while allowing PivotTable use; protect the workbook structure.</li>
<li>Test: drop a new month file in the Data folder, press <strong>Refresh All</strong>, confirm the new month appears and the checks stay OK.</li>
<li>Set the print area, landscape, one page wide, and export a PDF.</li>
<li>Save the model with a version number in the name and archive a copy.</li>
</ol>

<h3>Step 7: Reflect and present</h3>
<p>Write half a page on what the report shows, the main finding, what you recommend, and the limits of the data. Present it in three minutes to a friend, explaining how it refreshes, and ask what was unclear. Keep their feedback for your next version.</p>

<h3>Try it yourself</h3>
<ol>
<li>Complete Steps 1 to 3 and show that the Data Model totals match the source.</li>
<li>Create all the measures in Step 4 and test two of them by hand.</li>
<li>Complete the report sheet with at least three PivotTables or charts and two slicers.</li>
<li>Add checks and the ReadMe sheet, then protect the sheet.</li>
<li>Run the refresh test with a new file and confirm everything updates.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A good reporting model separates raw data, a star schema model, measures and a presentation sheet.</li>
<li>Power Query makes the update a folder drop and Refresh All.</li>
<li>Checks, documentation and versions make the report trustworthy and maintainable.</li>
<li>Test the full refresh cycle with new data before you hand the model over.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which file type must you save a workbook as to keep a VBA macro in it?
- .xlsx
- .csv
* .xlsm
- .pdf
= Macro-enabled workbooks use the .xlsm format. A normal .xlsx file cannot store macros.

Q: What should you do when you receive a macro-enabled file from an unknown sender by email?
- Enable macros to see the content
* Not enable macros, and delete the file or check it with the sender and your IT support
- Forward it to colleagues
- Rename it to .xlsx
= Macros can contain harmful code, so only enable them from sources you trust.

Q: What are Office Scripts?
- A replacement for formulas
* Cloud-based automation for Excel for the web written in TypeScript, depending on licence
- A type of PivotTable
- A way to protect sheets
= Office Scripts are recorded or written in TypeScript from the Automate tab and can be run with Power Automate.

Q: Before reaching for a macro, which no-code tools should you consider first?
- Merged cells
* Power Query with Refresh All, Tables and formulas
- Manual copy and paste
- Conditional formatting only
= Many repetitive data tasks are handled by Power Query and automatic recalculation without code.

Q: What does sheet protection give you?
- Strong security against determined people
* Protection against accidental changes, not strong security
- Automatic backups
- Faster calculation
= Protect Sheet passwords are weak. Use encryption and file permissions for confidential files.

Q: Which is a good performance practice?
- Use OFFSET and INDIRECT everywhere
* Remove unused columns and filter rows early in Power Query, and reduce volatile functions
- Use whole-column references in every formula
- Load all columns just in case
= Loading less data and avoiding volatile functions keep workbooks fast.

Q: What is the purpose of a check cell comparing a PivotTable total with the source total?
- It formats the PivotTable
* It warns you when the model loses or double counts data
- It protects the workbook
- It speeds up refresh
= Checks catch errors before decisions are made and build trust in the report.

Q: In the capstone, how should a new month of data be added?
- Retype the formulas
* Drop the new file in the Data folder and press Refresh All, then confirm the checks still show OK
- Insert new rows in each PivotTable
- Create a new workbook
= A well-built model updates through its queries, and checks confirm the result.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What is the Applied Steps list in Power Query used for?
- Printing the workbook
* Showing, reordering, renaming and deleting the recorded transformation steps
- Protecting a query
- Creating a PivotTable
= Applied Steps document the query and let you inspect the data after each step.

Q: Which Power Query step should you use for a report with Branch, Jan, Feb, Mar so that later months are included automatically?
- Pivot Column
- Remove Columns
* Unpivot Other Columns with Branch selected
- Group By
= Unpivot Other Columns keeps the selected columns and turns every other column into rows, including new ones.

Q: A query that merges Sales and Products returns fewer matches than expected. What should you check first?
- The colour of the header
* Spaces, capitals and data types in the key columns
- The number of worksheets
- The Developer tab
= Keys must match exactly in value and type for a merge to find matches.

Q: Which join kind lists sales rows whose product code is missing from the product list?
- Inner
* Left Anti
- Full Outer
- Append
= A left anti join returns the rows of the first table with no match in the second.

Q: What does the From Folder connector do when combining files?
- Opens each file in Excel
- Imports only the first file
* Applies one set of steps to every file and picks up new files on refresh
- Deletes the files after import
= Combine and Transform builds a helper query so every file is treated the same way.

Q: Why should the original files remain unchanged when you use Power Query?
- Power Query needs them to be read-only
* Power Query reads the sources and records steps; the source is never altered, so you can refresh again
- Excel cannot save to the same folder
- Files cannot be edited at all
= The cleaning happens in the query, leaving the source intact.

Q: In a relationship between Branches and Sales, which table is the one side?
- Sales
* Branches
- Both
- Neither
= The dimension (lookup) table has unique keys; the fact table has repeated keys.

Q: What does a star schema look like?
- One large flat table
- Two fact tables joined by many-to-many relationships
* A central fact table connected to dimension tables by one-to-many relationships
- Dimension tables chained to each other in a long line
= The fact table sits in the centre, with each dimension attached directly to it.

Q: What must a Date table contain to work with time intelligence?
- Only the dates that appear in the sales
* Every day in the range with no gaps, related to the facts and marked as a Date table
- Text months only
- Only working days
= Time intelligence functions require a continuous, marked calendar.

Q: Which of these is correctly a calculated column rather than a measure?
- Total sales for the selection
* Customer size category (Small, Medium, Large) used in Rows
- Year to date sales
- Margin % of the whole business
= Categories used for grouping must exist as columns; totals and ratios are measures.

Q: A measure shows different values in different PivotTable cells even though the formula is the same. Why?
- The formula changes by itself
* Each cell has a different filter context
- Excel makes a mistake
- The Data Model is corrupted
= A measure is recalculated for every cell using that cell's filters from rows, columns, slicers and filters.

Q: What does CALCULATE([Total Sales], Products[Product] = "Soap") do when the PivotTable row is Rice?
- Returns Rice sales
- Returns zero
* Returns soap sales, because the filter on Product replaces the row's product filter
- Returns an error
= A filter inside CALCULATE on a column replaces any existing filter on the same column.

Q: Which formula gives each product's share of total sales?
- =SUM(Sales[Amount])/COUNT(Sales[Amount])
* DIVIDE([Total Sales], CALCULATE([Total Sales], ALL(Products)))
- =FILTER(Products, Sales)
- =TOTALYTD([Total Sales], 'Date'[Date])
= ALL(Products) removes the product filter to give the grand total, and DIVIDE makes the ratio safely.

Q: Which function is best for year to date sales?
- SUMX
* TOTALYTD
- DISTINCTCOUNT
- RANKX
= TOTALYTD accumulates a measure from the start of the year to the current date in context.

Q: Which expression gets last year's value of a measure in a PivotTable by month?
- ALL('Date')
- COUNTROWS('Date')
* CALCULATE([Total Sales], SAMEPERIODLASTYEAR('Date'[Date]))
- SUM('Date'[Year])
= SAMEPERIODLASTYEAR shifts the date filter back one year inside CALCULATE.

Q: Which function uses a condition that involves several columns or comparisons, such as Amount greater than 100000?
- DIVIDE
- ALL
* FILTER
- DISTINCTCOUNT
= FILTER returns a table of the rows that pass a test, which CALCULATE can use as a filter.

Q: Which formula returns a sorted list of unique branches in Microsoft 365?
- =UNIQUE(SORT)
* =SORT(UNIQUE(Sales[Branch]))
- =FILTER(Sales[Branch])
- =LET(Sales[Branch])
= UNIQUE removes repeats and SORT puts the result in order; the result spills down.

Q: What does #SPILL! mean?
- A division by zero
* The cells where a dynamic array result should appear are blocked
- A missing function name
- A wrong data type
= Clear the cells in the way, such as text typed below the formula.

Q: Which are dynamic array functions that need Microsoft 365 or Excel 2021? (choose all that apply)
* FILTER
* SEQUENCE
- SUMIFS
- VLOOKUP
= SUMIFS and VLOOKUP work in older versions. FILTER and SEQUENCE need dynamic array Excel.

Q: What is the main advantage of LET?
- It protects formulas from editing
* It names intermediate results, so formulas are shorter, clearer and faster
- It replaces PivotTables
- It sorts a table
= LET stores values under names inside one formula.

Q: Which is the safest way to handle a missing lookup match?
- Hide all errors with IFERROR so the report looks clean
- Delete the row
* Use IFNA to show a clear message such as "Missing price", and count the rows affected
- Return 0 so totals still add up
= Handle only the expected error, show an honest message and count problems; a silent 0 hides missing data.

Q: How do you find the most recent price for an item that appears several times in a price table with XLOOKUP?
- Sort the table alphabetically
- Use match mode 2
* Use search mode -1 to search from the bottom up
- Use two XLOOKUPs
= Search mode -1 returns the last matching entry.

Q: What is the recommended way to protect the confidentiality of a workbook?
- Sheet protection with a password
* Encrypt with a password to open and use proper file permissions in OneDrive or SharePoint
- Hide the sheets
- Name the file "Confidential"
= Sheet protection stops accidents only. Encryption and permissions protect confidential data.

Q: What should be included in a model's ReadMe sheet?
- Only the author's name
* Purpose, sources, refresh steps, definitions, version and change log
- A copy of the data
- Nothing; models explain themselves
= Documentation allows others to use and maintain the model correctly.

Q: Which practice most improves the speed of a large model?
- Using OFFSET and INDIRECT in every formula
* Loading only needed columns and rows in Power Query and using measures in the Data Model
- Using whole-column references
- Turning on gridlines
= Less data and fewer volatile or heavy formulas make workbooks faster.

Q: Which statement about macros is correct? (choose all that apply)
* A macro-enabled workbook must be saved as .xlsm
* Macros from unknown sources can be harmful and should not be enabled
- Macros can be stored in a normal .xlsx file
- Macros never need testing
= Macros need the .xlsm format and must be treated as potentially dangerous code, so test and trust carefully.
QUIZ,
];
