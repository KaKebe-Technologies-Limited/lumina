<?php
return [
    'slug'        => 'excel-data-analysis',
    'title'       => 'Excel for Data Analysis: PivotTables, Charts and Dashboards',
    'category'    => 'data',
    'level'       => 'intermediate',
    'icon'        => 'layers',
    'sort'        => 170,
    'summary'     => 'Turn raw lists into answers: clean data, write analysis formulas, build PivotTables and charts, and finish with a one-page dashboard you can share.',
    'description' => '<p>Most workplaces collect data: sales receipts, school marks, survey answers, attendance, donations. The skill that people value is not typing the data in but turning it into answers: which product sells best, which class improved, which month was weakest. Excel can do this very well when the data is prepared properly and the right tools are used.</p><p>This intermediate course takes you from a messy list to a finished dashboard. You will clean data, write formulas such as SUMIFS and XLOOKUP, summarise thousands of rows with PivotTables, choose honest charts, and combine everything into a one-page report. Every module uses small, realistic examples from shops, schools and community projects.</p><p>You should already be comfortable with the basics of Excel: entering data, simple formulas, SUM and IF, sorting and filtering, and basic charts.</p>',
    'outcomes'    => [
        'Prepare raw data so it is tidy, consistent and ready to analyse',
        'Clean text, dates and numbers stored as text using Excel tools and functions',
        'Write analysis formulas with SUMIFS, COUNTIFS, AVERAGEIFS, IFS, IFERROR and XLOOKUP or INDEX-MATCH',
        'Use Excel Tables, named ranges and structured references to make formulas easier to read',
        'Build PivotTables with grouping, calculated fields, slicers and timelines, and turn them into PivotCharts',
        'Choose the right chart for a question and avoid charts that mislead',
        'Use sparklines and conditional formatting to show patterns at a glance',
        'Build, protect, print and share a one-page KPI dashboard from raw data',
    ],
    'requirements' => ['Completed Microsoft Excel: Spreadsheets from Zero, or equivalent skills (formulas, SUM, IF, sorting, filtering, simple charts)', 'A computer with Excel 2019 or Microsoft 365 (a few features need Microsoft 365 or 2021 and are marked in the lessons)', 'Comfort with saving files and using folders'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Microsoft Support: Excel help and training', 'url' => 'https://support.microsoft.com/excel'],
        ['title' => 'Microsoft Support: Overview of PivotTables', 'url' => 'https://support.microsoft.com/excel'],
        ['title' => 'Microsoft Learn: Excel documentation', 'url' => 'https://learn.microsoft.com/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: Preparing data',
            'summary' => 'Shape data into a tidy layout and clean up the common problems before any analysis begins.',
            'lessons' => [
                [
                    'title'   => 'Tidy data and Excel Tables',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to lay out data in a tidy way and convert a range into an Excel Table. This matters because PivotTables, charts and formulas all work best, and sometimes only work, when the data has a clean shape. A few minutes of preparation saves hours of fixing later.</p>

<h3>What tidy data means</h3>
<p>Tidy data follows three simple rules.</p>
<ul>
<li><strong>One column, one kind of information.</strong> A column called <code>Amount</code> holds only amounts, not amounts mixed with notes.</li>
<li><strong>One row, one record.</strong> Each row is one sale, one learner or one donation.</li>
<li><strong>One header row.</strong> The first row holds short, unique column names with no blank headers, no merged cells and no totals mixed in.</li>
</ul>
<p>Beginners often build reports that look nice, with a month in each column and merged title cells. That layout is good for reading but poor for analysis. Keep a plain, tidy data sheet and build the nice report separately from it.</p>

<h3>A bad layout and a good layout</h3>
<table>
<thead><tr><th>Messy</th><th>Tidy</th></tr></thead>
<tbody>
<tr><td>Title row merged across the top, then a blank row, then headers</td><td>Headers in row 1, data starting in row 2</td></tr>
<tr><td>Months as columns: Jan, Feb, Mar...</td><td>One <code>Month</code> column and one <code>Sales</code> column</td></tr>
<tr><td>Subtotal rows between groups</td><td>No subtotals; add them later with a PivotTable</td></tr>
<tr><td>Two facts in one cell: <code>Gulu - Retail</code></td><td>Separate <code>Town</code> and <code>Channel</code> columns</td></tr>
<tr><td>Blank cells meaning "same as above"</td><td>Every cell filled in</td></tr>
</tbody>
</table>

<h3>Turn the range into an Excel Table</h3>
<p>An <strong>Excel Table</strong> is a special range that grows automatically when you add rows and keeps its formatting and formulas consistent.</p>
<ol>
<li>Click any cell inside your data.</li>
<li>Press <strong>Ctrl + T</strong> (Mac: <strong>Cmd + T</strong>), or select the <strong>Insert</strong> tab, then <strong>Table</strong>.</li>
<li>Tick <strong>My table has headers</strong> and click <strong>OK</strong>.</li>
<li>On the <strong>Table Design</strong> tab, type a clear name in the <strong>Table Name</strong> box, for example <code>Sales</code>.</li>
</ol>
<p>Once your data is a Table, new rows typed directly below it are included automatically. Filter buttons appear on each header, and a formula typed in one cell fills the whole column.</p>

<h3>Habits that prevent problems</h3>
<ul>
<li>Use short header names without line breaks. <code>Unit Price</code> is better than <code>Price per unit (UGX) - see note 2</code>.</li>
<li>Keep one table per sheet, and keep notes outside the table.</li>
<li>Store dates as real dates and numbers as real numbers. Lesson 3 shows how to check this.</li>
<li>Do not colour cells to carry meaning. Add a column such as <code>Status</code> instead, because a PivotTable cannot count colours.</li>
<li>Always keep an untouched copy of the original data before you clean it.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Type a small sales list with the headers <code>Date</code>, <code>Item</code>, <code>Town</code>, <code>Quantity</code>, <code>Unit Price</code> and about eight rows.</li>
<li>Click inside it and press <strong>Ctrl + T</strong> to make it a Table.</li>
<li>Name the Table <code>Sales</code> on the Table Design tab.</li>
<li>Type a new row directly under the last one and notice that the Table grows.</li>
<li>In a new column called <code>Total</code>, type <code>=[@Quantity]*[@[Unit Price]]</code> in the first row and watch it fill down automatically.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Tidy data means one column per kind of information, one row per record and one header row.</li>
<li>Keep data separate from the pretty report; no merged cells or subtotals in the data.</li>
<li>Ctrl + T converts a range to a Table that grows and fills formulas automatically.</li>
<li>Give each Table a clear name; you will use it in formulas and PivotTables.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Cleaning text: TRIM, CLEAN and friends',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to remove extra spaces and hidden characters, fix capital letters, and split or join text in Excel. This matters because data typed by many people, or copied from forms, websites and WhatsApp, is rarely consistent. If <code>Gulu</code>, <code>gulu</code> and <code>Gulu </code> (with a space at the end) appear in one column, a PivotTable will count them as three different towns.</p>

<h3>Spot the problems first</h3>
<p>Common troubles are extra spaces at the start, middle or end of a cell, line breaks and other hidden characters, mixed capitals, and two facts joined in one cell. Excel has simple functions for each.</p>
<table>
<thead><tr><th>Function</th><th>What it does</th><th>Example</th></tr></thead>
<tbody>
<tr><td><code>TRIM</code></td><td>Removes extra spaces, leaving single spaces between words</td><td><code>=TRIM(A2)</code></td></tr>
<tr><td><code>CLEAN</code></td><td>Removes non-printing characters such as line breaks</td><td><code>=CLEAN(A2)</code></td></tr>
<tr><td><code>PROPER</code></td><td>Capitalises the first letter of each word</td><td><code>=PROPER(A2)</code></td></tr>
<tr><td><code>UPPER</code> / <code>LOWER</code></td><td>All capitals or all small letters</td><td><code>=UPPER(A2)</code></td></tr>
<tr><td><code>LEFT</code>, <code>RIGHT</code>, <code>MID</code></td><td>Take part of the text</td><td><code>=LEFT(A2,3)</code></td></tr>
<tr><td><code>SUBSTITUTE</code></td><td>Replaces certain text with other text</td><td><code>=SUBSTITUTE(A2,"-","")</code></td></tr>
</tbody>
</table>

<h3>A reliable cleaning recipe</h3>
<p>You can combine functions. This formula removes hidden characters, then extra spaces, then fixes the capitals:</p>
<pre><code>=PROPER(TRIM(CLEAN(A2)))</code></pre>
<p>Type it in a helper column next to the messy data and fill it down. The cleaned results are still formulas, so convert them to plain values:</p>
<ol>
<li>Select the cleaned cells and press <strong>Ctrl + C</strong>.</li>
<li>Right-click the first cell of the original column and choose <strong>Paste Special</strong>, then <strong>Values</strong> (or press <strong>Ctrl + Alt + V</strong>, then <strong>V</strong>, then <strong>Enter</strong>).</li>
<li>Delete the helper column.</li>
</ol>
<p>A phone number copied from a website sometimes contains a non-breaking space that TRIM does not remove. If a cell still looks wrong, try <code>=SUBSTITUTE(A2,CHAR(160)," ")</code> first.</p>

<h3>Splitting one column into two</h3>
<p>Suppose column A holds <code>Akello Grace</code> and you want first and last names apart.</p>
<ol>
<li>Select the column.</li>
<li>Choose the <strong>Data</strong> tab, then <strong>Text to Columns</strong>.</li>
<li>Pick <strong>Delimited</strong>, click <strong>Next</strong>, tick <strong>Space</strong>, and finish.</li>
</ol>
<p>Make sure there are empty columns to the right, because Text to Columns writes over whatever is there. In Microsoft 365 you can also use <code>=TEXTSPLIT(A2," ")</code>. To join text, use the ampersand: <code>=B2&amp;" "&amp;C2</code>, or <code>TEXTJOIN</code> for many cells.</p>

<h3>Flash Fill</h3>
<p>Type the result you want in the first row, then press <strong>Ctrl + E</strong>. Excel guesses the pattern and fills the rest. Always check the results, because Flash Fill is a guess and does not update when the source changes.</p>

<h3>Try it yourself</h3>
<ol>
<li>In column A type five towns with problems: <code>  gulu</code>, <code>GULU</code>, <code>Lira </code>, <code>kitgum</code>, <code>Gulu</code>.</li>
<li>In column B enter <code>=PROPER(TRIM(CLEAN(A1)))</code> and fill it down.</li>
<li>Copy column B and paste it over column A as values only.</li>
<li>Delete column B. How many different towns do you really have?</li>
<li>Type <code>Okello Peter</code> in a cell and split it with Text to Columns.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Inconsistent text splits one group into several in a PivotTable, so clean it first.</li>
<li>TRIM, CLEAN and PROPER together fix most typing problems.</li>
<li>Paste Special, then Values, turns formula results into fixed text.</li>
<li>Text to Columns and Flash Fill split text; both work on the original data, so keep a backup.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Dates and numbers stored as text',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to recognise numbers and dates that Excel treats as text, and convert them into real values. This matters because text cannot be added, sorted by date or grouped by month. A column of "numbers" that will not total is one of the most common beginner frustrations.</p>

<h3>How to recognise text that looks like a number</h3>
<ul>
<li>The values line up on the <strong>left</strong> of the cell (real numbers and dates line up on the right by default).</li>
<li>A small green triangle appears in the top-left corner of the cell with a warning.</li>
<li><code>SUM</code> gives 0 or ignores some cells.</li>
<li>The Home tab shows the number format as <strong>Text</strong>.</li>
</ul>
<p>Test a cell with <code>=ISNUMBER(A2)</code>. It returns <code>TRUE</code> for a real number or date and <code>FALSE</code> for text.</p>

<h3>Fixing numbers stored as text</h3>
<p>Choose the method that suits the situation.</p>
<ol>
<li><strong>Warning triangle.</strong> Select the cells, click the warning icon and choose <strong>Convert to Number</strong>.</li>
<li><strong>Text to Columns.</strong> Select the column, choose <strong>Data</strong>, <strong>Text to Columns</strong>, then click <strong>Finish</strong> straight away. This forces Excel to re-read the values.</li>
<li><strong>Multiply by 1.</strong> In a helper column type <code>=A2*1</code> or <code>=VALUE(A2)</code>, then paste as values.</li>
</ol>
<p>If numbers contain symbols such as <code>UGX 25,000</code>, remove them first: <code>=VALUE(SUBSTITUTE(SUBSTITUTE(A2,"UGX",""),",",""))</code>. Always keep the original column until you are sure the result is right.</p>

<h3>Fixing dates stored as text</h3>
<p>Dates are stored as serial numbers (the count of days since 1 January 1900), displayed with a date format. When data is imported, dates such as <code>2025.03.14</code> or <code>14th March 2025</code> may arrive as text. You can fix them with:</p>
<ul>
<li><code>=DATEVALUE(A2)</code> for text that Excel recognises as a date, then format the result as a date.</li>
<li><code>=DATE(LEFT(A2,4),MID(A2,6,2),RIGHT(A2,2))</code> for a fixed pattern such as <code>2025.03.14</code>.</li>
<li>Text to Columns, choosing the <strong>Date</strong> step and the right order (DMY, MDY or YMD).</li>
</ul>

<h3>The day-month confusion</h3>
<p>The date <code>03/04/2025</code> means 3 April in Uganda and the UK, but 4 March in the United States. Your computer's regional settings decide how Excel reads typed dates. When you receive data from other people, check a date with a day above 12, such as <code>25/03/2025</code>. If Excel left it as text, the order does not match your settings. Also avoid a two-digit year, because <code>25</code> might be read as 1925 or 2025 in old data.</p>

<h3>Format versus value</h3>
<p>Changing a cell's format from Text to Number does not convert existing entries. Excel only applies the new format to what you type afterwards. Use one of the conversion methods above, then set the format.</p>

<h3>Try it yourself</h3>
<ol>
<li>Format cells A1:A4 as <strong>Text</strong>, then type <code>1500</code>, <code>2300</code>, <code>900</code> and <code>4100</code>.</li>
<li>In A6 enter <code>=SUM(A1:A4)</code>. What do you get?</li>
<li>Select A1:A4, change the format to <strong>General</strong>, then use Text to Columns and Finish.</li>
<li>Check that the sum is now 8800 and that the numbers line up on the right.</li>
<li>In B1 type <code>=ISNUMBER(A1)</code> and confirm it shows TRUE.</li>
</ol>

<h3>Common mistakes</h3>
<ul>
<li>Changing the format and expecting old values to change.</li>
<li>Forgetting that a leading apostrophe (<code>'0772</code>) forces text. This is correct for phone numbers and IDs with leading zeros, which should stay as text.</li>
<li>Converting dates without checking the day-month order.</li>
</ul>

<h3>Key points</h3>
<ul>
<li>Left-aligned "numbers" and green triangles usually mean text.</li>
<li>ISNUMBER tells you the truth about a cell.</li>
<li>Convert with the warning icon, Text to Columns, VALUE or DATEVALUE; changing the format alone does not work.</li>
<li>Check the day-month order of dates every time data comes from outside.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Duplicates, find and replace, and data validation',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to remove duplicate records safely, correct many mistakes at once with Find and Replace, and stop bad entries before they happen with data validation. This matters because analysis is only as good as the data. Prevention at entry is cheaper than cleaning afterwards.</p>

<h3>Removing duplicates</h3>
<p>A duplicate is a record entered twice, such as the same receipt number captured by two clerks. Be careful: two rows can look similar and still be different sales. Decide which columns together identify a unique record.</p>
<ol>
<li>Click inside your data or Table.</li>
<li>Choose the <strong>Data</strong> tab, then <strong>Remove Duplicates</strong>.</li>
<li>Tick only the columns that together define a duplicate, for example <code>Receipt No</code>.</li>
<li>Click <strong>OK</strong>. Excel tells you how many rows were removed and how many remain.</li>
</ol>
<p>This action deletes rows. If you want to look before deleting, first highlight duplicates: select the column, choose <strong>Home</strong>, <strong>Conditional Formatting</strong>, <strong>Highlight Cells Rules</strong>, <strong>Duplicate Values</strong>. In Microsoft 365 you can also use <code>=UNIQUE(range)</code> to produce a list without duplicates and leave the original untouched. Press <strong>Ctrl + Z</strong> if you delete by mistake and have not done anything else.</p>

<h3>Find and Replace</h3>
<p>Press <strong>Ctrl + H</strong> to open Replace. Type what to find and what to replace it with. Click <strong>Options</strong> for important settings.</p>
<ul>
<li><strong>Within</strong>: this sheet or the whole workbook.</li>
<li><strong>Match entire cell contents</strong>: tick it when replacing a whole value, otherwise <code>UG</code> inside <code>Kampala UG</code> also changes.</li>
<li><strong>Look in</strong>: formulas or values.</li>
</ul>
<p>Wildcards help: <code>*</code> stands for any number of characters, and <code>?</code> for one. Searching <code>Gulu*</code> finds <code>Gulu City</code> and <code>Gulu Town</code>. Click <strong>Replace All</strong> only after using <strong>Find Next</strong> a few times to check what will change. Select the column first so Excel changes only that column.</p>

<h3>Data validation: stop errors at the door</h3>
<p>Data validation controls what can be typed into a cell.</p>
<ol>
<li>Select the cells, for example the <code>Town</code> column.</li>
<li>Choose <strong>Data</strong>, then <strong>Data Validation</strong>.</li>
<li>Under <strong>Allow</strong> choose <strong>List</strong>. In <strong>Source</strong> type <code>Gulu,Lira,Kitgum,Arua</code> or select a range that holds the allowed towns.</li>
<li>On the <strong>Error Alert</strong> tab write a friendly message such as "Please choose a town from the list."</li>
</ol>
<table>
<thead><tr><th>Allow type</th><th>Good for</th></tr></thead>
<tbody>
<tr><td>List</td><td>Towns, products, status, classes</td></tr>
<tr><td>Whole number</td><td>Quantity between 1 and 1000</td></tr>
<tr><td>Decimal</td><td>Marks between 0 and 100</td></tr>
<tr><td>Date</td><td>Dates in the current year only</td></tr>
<tr><td>Text length</td><td>Phone numbers of exactly 10 characters</td></tr>
</tbody>
</table>
<p>Validation does not stop pasted values, so combine it with checking. To find existing bad entries, use <strong>Data Validation</strong>, then <strong>Circle Invalid Data</strong>. If your list source is a Table column, the drop-down updates itself when you add items.</p>

<h3>Try it yourself</h3>
<ol>
<li>Type ten receipt numbers in a column, with two repeated.</li>
<li>Highlight the duplicates with Conditional Formatting and check them.</li>
<li>Copy the sheet, then use Remove Duplicates on the copy. How many rows remain?</li>
<li>Add a <code>Marks</code> column and set validation to allow decimals from 0 to 100 with an error message.</li>
<li>Try to type 150 and read your own message.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Choose the columns that define a duplicate; remove duplicates only after looking.</li>
<li>Ctrl + H with "Match entire cell contents" avoids accidental partial replacements.</li>
<li>Validation lists and limits keep new data consistent.</li>
<li>Prevention at entry beats cleaning afterwards.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which layout is best for data you plan to analyse with a PivotTable?
- Months as separate columns with merged title cells above
* One header row, one record per row, one kind of information per column
- Subtotal rows after each group so the totals are visible
- Colour-coding rows instead of adding a Status column
= Tidy data has one header row and one record per row. Merged cells, subtotals and colour meanings all get in the way of analysis.

Q: What does pressing Ctrl + T on a cell inside your data do?
- Inserts a total row
* Converts the range to an Excel Table that grows with new rows
- Transposes rows and columns
- Opens the Text to Columns wizard
= Ctrl + T creates an Excel Table. Tables expand automatically and fill formulas down their columns.

Q: Which formula removes hidden characters, extra spaces and fixes capitals in A2?
- =TRIM(PROPER(A2))
- =CLEAN(A2)
* =PROPER(TRIM(CLEAN(A2)))
- =SUBSTITUTE(A2," ","")
= CLEAN removes non-printing characters, TRIM removes extra spaces and PROPER fixes the capital letters. The others do only part of the job or remove every space.

Q: A column of numbers will not add up and they line up on the left. What is the most likely cause?
- The column is too narrow
* The numbers are stored as text
- Excel is set to manual calculation
- The numbers are negative
= Left-aligned "numbers" with green triangles are usually text. Convert them to real numbers before using SUM.

Q: You change a text-formatted column to the Number format but the values still do not add. Why?
- Excel needs to be restarted
- The format change only works on Mac
* Changing the format does not convert existing entries; you must re-enter or convert them
- You must also sort the column
= The format applies to new typing only. Use Convert to Number, Text to Columns or VALUE to convert the values that are already there.

Q: When using Remove Duplicates on a sales list, which columns should you tick? (choose the best answer)
- Always every column
- Only the first column
* The columns that together identify a unique record, such as the receipt number
- None; Excel decides automatically
= Remove Duplicates compares only the columns you tick, so tick those that define what a duplicate means for your data.

Q: Which settings keep a Find and Replace from changing part of other cells?
- Search by columns
* Match entire cell contents
- Look in comments
- Format only
= Matching entire cell contents means Excel changes a cell only when the whole value equals what you typed.

Q: What is a limit of data validation?
- It cannot create drop-down lists
* It does not stop values that are pasted in from elsewhere
- It only works on text
- It deletes invalid data
= Pasting can bypass validation rules, so combine validation with checks such as Circle Invalid Data.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Formulas for analysis',
            'summary' => 'Conditional totals, lookups, nested logic, text and date functions, and readable formulas using names and Tables.',
            'lessons' => [
                [
                    'title'   => 'SUMIFS, COUNTIFS and AVERAGEIFS',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to total, count and average only the rows that meet your conditions. This matters because real questions are conditional: "How much did we sell in Gulu in March?" or "How many learners scored above 70?" These three functions answer such questions without any filtering by hand.</p>

<h3>The pattern</h3>
<p>All three functions work the same way: you give pairs of a <em>range</em> to look in and a <em>criterion</em> to test. A row is included only when it passes every test.</p>
<pre><code>=SUMIFS(sum_range, criteria_range1, criteria1, criteria_range2, criteria2, ...)
=COUNTIFS(criteria_range1, criteria1, criteria_range2, criteria2, ...)
=AVERAGEIFS(average_range, criteria_range1, criteria1, ...)</code></pre>
<p>Note that in SUMIFS and AVERAGEIFS the range to add or average comes <strong>first</strong>. COUNTIFS has no such range, because it simply counts matching rows.</p>

<h3>Examples with a Table called Sales</h3>
<p>Imagine the columns <code>Date</code>, <code>Town</code>, <code>Item</code>, <code>Quantity</code> and <code>Amount</code>.</p>
<pre><code>=SUMIFS(Sales[Amount], Sales[Town], "Gulu")
=SUMIFS(Sales[Amount], Sales[Town], "Gulu", Sales[Item], "Soap")
=COUNTIFS(Sales[Town], "Lira")
=AVERAGEIFS(Sales[Amount], Sales[Item], "Rice")</code></pre>
<p>Better still, put the criteria in cells so the formula can be reused. If <code>G1</code> holds a town name:</p>
<pre><code>=SUMIFS(Sales[Amount], Sales[Town], G1)</code></pre>

<h3>Criteria with comparisons and dates</h3>
<p>For operators, place them inside quotation marks and join to a cell with an ampersand.</p>
<table>
<thead><tr><th>You want</th><th>Criterion</th></tr></thead>
<tbody>
<tr><td>Amount above 50,000</td><td><code>"&gt;50000"</code></td></tr>
<tr><td>Not equal to Soap</td><td><code>"&lt;&gt;Soap"</code></td></tr>
<tr><td>Date on or after the date in H1</td><td><code>"&gt;="&amp;H1</code></td></tr>
<tr><td>Text starting with S</td><td><code>"S*"</code></td></tr>
<tr><td>Blank cells</td><td><code>""</code></td></tr>
</tbody>
</table>
<p>To total sales between two dates, in H1 and H2:</p>
<pre><code>=SUMIFS(Sales[Amount], Sales[Date], "&gt;="&amp;H1, Sales[Date], "&lt;="&amp;H2)</code></pre>

<h3>Rules that cause errors</h3>
<ul>
<li>All ranges must have the same size. A mismatch gives a <code>#VALUE!</code> error.</li>
<li>Criteria are not case sensitive: <code>"gulu"</code> matches <code>Gulu</code>.</li>
<li>If a number or date criterion returns zero when it should not, check that the data column is real numbers, not text (Module 1).</li>
<li>The older <code>SUMIF</code> and <code>COUNTIF</code> take one condition only. SUMIFS and COUNTIFS also work with a single condition, so you can use them always.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Make a Table named <code>Sales</code> with at least 12 rows covering three towns and three items.</li>
<li>Write a SUMIFS for the total amount of one town.</li>
<li>Add a second condition for one item and compare the result with a manual filter.</li>
<li>Write a COUNTIFS that counts sales above 20,000.</li>
<li>Write an AVERAGEIFS for one item and format the result with two decimals.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Each pair of range and criterion adds one more condition; all must be true.</li>
<li>In SUMIFS and AVERAGEIFS the range to calculate comes first.</li>
<li>Join operators and cell references like <code>"&gt;="&amp;H1</code>.</li>
<li>Ranges must be the same size, and data must be real numbers and dates.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Logic: nested IF, IFS and IFERROR',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to make Excel choose between several outcomes and handle errors gracefully. This matters because many reports need a label or a decision, such as grading marks, flagging low stock, or hiding ugly <code>#N/A</code> messages from a report that a manager will read.</p>

<h3>A quick look back at IF</h3>
<p><code>IF</code> tests a condition and returns one result when it is true and another when it is false.</p>
<pre><code>=IF(B2&gt;=50, "Pass", "Fail")</code></pre>
<p>Combine tests with <code>AND</code> (all must be true) and <code>OR</code> (at least one must be true):</p>
<pre><code>=IF(AND(B2&gt;=50, C2&gt;=50), "Pass", "Retake")</code></pre>

<h3>Nested IF</h3>
<p>When there are more than two outcomes, put an IF inside another IF. Excel checks from left to right and stops at the first true test, so the order matters. Grading from highest to lowest:</p>
<pre><code>=IF(B2&gt;=80, "A", IF(B2&gt;=70, "B", IF(B2&gt;=60, "C", IF(B2&gt;=50, "D", "F"))))</code></pre>
<p>If you tested <code>B2&gt;=50</code> first, a mark of 85 would be labelled D. Nested IFs work, but they become hard to read after three levels, and each IF needs its own closing bracket.</p>

<h3>IFS: a tidier way (Excel 2019 and Microsoft 365)</h3>
<p><code>IFS</code> lists test and result pairs in order, with no nesting.</p>
<pre><code>=IFS(B2&gt;=80, "A", B2&gt;=70, "B", B2&gt;=60, "C", B2&gt;=50, "D", TRUE, "F")</code></pre>
<p>The last pair <code>TRUE, "F"</code> acts as "otherwise". Without it, a mark below 50 gives <code>#N/A</code>. If you must share the file with someone using Excel 2016 or older, IFS will not work for them, so use nested IF instead.</p>

<h3>When the rules are a table</h3>
<p>If grade boundaries are likely to change, put them in a small table instead of inside the formula, and use a lookup with approximate match (Lesson 3 of this module). That way you change one cell, not hundreds of formulas.</p>

<h3>IFERROR: handle errors on purpose</h3>
<p><code>IFERROR</code> returns your chosen value if the formula gives an error.</p>
<pre><code>=IFERROR(B2/C2, 0)
=IFERROR(XLOOKUP(A2, Prices[Item], Prices[Price]), "Not found")</code></pre>
<table>
<thead><tr><th>Error</th><th>Usual meaning</th></tr></thead>
<tbody>
<tr><td><code>#DIV/0!</code></td><td>Division by zero or an empty cell</td></tr>
<tr><td><code>#N/A</code></td><td>A lookup did not find the value</td></tr>
<tr><td><code>#NAME?</code></td><td>A misspelt function or name</td></tr>
<tr><td><code>#REF!</code></td><td>A cell the formula used was deleted</td></tr>
<tr><td><code>#VALUE!</code></td><td>Text where a number was expected</td></tr>
</tbody>
</table>
<p>Use IFERROR carefully. It hides <em>every</em> error, including mistakes in your own formula. Wrap only the part that may fail for an expected reason, and use a clear message such as "Not found" rather than silently showing 0, which could be mistaken for real data. For lookups only, <code>IFNA</code> is safer because it hides only <code>#N/A</code>.</p>

<h3>Try it yourself</h3>
<ol>
<li>Enter marks 92, 74, 65, 51 and 30 in column B.</li>
<li>Write a nested IF in column C to give grades A to F as above.</li>
<li>Write the same with IFS in column D (if your Excel has it) and compare the results.</li>
<li>In E2 type <code>=B2/0</code> and then wrap it as <code>=IFERROR(B2/0,"Check")</code>.</li>
<li>Change the first test order in your nested IF and see what goes wrong.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Nested IF and IFS test conditions in order and stop at the first true one.</li>
<li>IFS is neater but needs Excel 2019 or Microsoft 365; add <code>TRUE</code> for "otherwise".</li>
<li>IFERROR and IFNA give friendly results; do not use them to hide real mistakes.</li>
<li>Put changing rules in a table, not inside the formula.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Lookups: XLOOKUP and INDEX-MATCH',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to pull information from one table into another using XLOOKUP, or INDEX with MATCH when XLOOKUP is not available. This matters because data is often split: a sales list holds product codes while the prices live in a separate price list. A lookup joins them without copying and pasting.</p>

<h3>XLOOKUP (Microsoft 365 and Excel 2021)</h3>
<pre><code>=XLOOKUP(lookup_value, lookup_array, return_array, [if_not_found], [match_mode])</code></pre>
<p>It finds the value in one column and returns the value from the same position in another column. Suppose a Table <code>Prices</code> has <code>Item</code> and <code>Price</code>. In the Sales table, to fetch the price for the item in the same row:</p>
<pre><code>=XLOOKUP([@Item], Prices[Item], Prices[Price], "Not found")</code></pre>
<p>Compared with the older VLOOKUP, XLOOKUP looks in any direction, does not break when you insert columns, defaults to an exact match, and has a built-in message for missing values. It can also return several columns at once in Microsoft 365.</p>

<h3>INDEX and MATCH (all versions)</h3>
<p>If you use Excel 2019 or older, or share files with people who do, combine two functions.</p>
<ul>
<li><code>MATCH(value, range, 0)</code> tells you the position of the value in a range. The <code>0</code> means exact match.</li>
<li><code>INDEX(range, position)</code> returns the value at that position.</li>
</ul>
<pre><code>=INDEX(Prices[Price], MATCH([@Item], Prices[Item], 0))
=IFERROR(INDEX(Prices[Price], MATCH([@Item], Prices[Item], 0)), "Not found")</code></pre>
<p>Beginners know VLOOKUP, which is still common. Its weaknesses are that the lookup column must be the left-most column, and a typed column number such as <code>3</code> breaks when columns change. INDEX-MATCH avoids both.</p>

<h3>Approximate match for bands</h3>
<p>For grade boundaries or commission rates, build a table sorted from small to large:</p>
<table>
<thead><tr><th>Minimum mark</th><th>Grade</th></tr></thead>
<tbody>
<tr><td>0</td><td>F</td></tr>
<tr><td>50</td><td>D</td></tr>
<tr><td>60</td><td>C</td></tr>
<tr><td>70</td><td>B</td></tr>
<tr><td>80</td><td>A</td></tr>
</tbody>
</table>
<p>Then use match mode <code>-1</code> in XLOOKUP, which finds the exact value or the next smaller one:</p>
<pre><code>=XLOOKUP(B2, Grades[Minimum mark], Grades[Grade], "", -1)</code></pre>
<p>With INDEX-MATCH, use <code>MATCH(B2, Grades[Minimum mark], 1)</code>; the final <code>1</code> means "largest value not above", and the list must be sorted in ascending order.</p>

<h3>Why a lookup fails</h3>
<ul>
<li>Extra spaces or different capitals in the lookup value (clean first, see Module 1).</li>
<li>A number on one side and the same number as text on the other.</li>
<li>Duplicates in the lookup column; a lookup returns only the first match.</li>
<li>For approximate match, an unsorted table.</li>
</ul>
<p>When a lookup returns <code>#N/A</code>, test with <code>=COUNTIF(Prices[Item], [@Item])</code>. A result of 0 confirms the item truly is not in the list.</p>

<h3>Try it yourself</h3>
<ol>
<li>Make a Table named <code>Prices</code> with five items and their prices.</li>
<li>Make a Table named <code>Orders</code> with an <code>Item</code> and <code>Quantity</code> column.</li>
<li>Add a <code>Price</code> column in Orders using XLOOKUP or INDEX-MATCH.</li>
<li>Add a <code>Total</code> column that multiplies quantity by price.</li>
<li>Type an item that is not in the price list and make the formula show "Not found".</li>
</ol>

<h3>Key points</h3>
<ul>
<li>XLOOKUP is the modern lookup; INDEX with MATCH works in every version.</li>
<li>Always use exact match (<code>0</code>) for names and codes; use approximate match only for sorted bands.</li>
<li>Lookup failures usually come from spaces, text-numbers or missing items.</li>
<li>Lookups return the first match only, so keep lookup keys unique.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Names, structured references, text and date functions',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to make formulas readable with names and structured references, and use text and date functions to prepare fields for analysis. This matters because a formula such as <code>=SUM(Sales[Amount])</code> can be understood by a colleague a year from now, while <code>=SUM(F2:F5000)</code> cannot.</p>

<h3>Structured references</h3>
<p>When your data is an Excel Table, formulas can use the Table and column names instead of cell addresses.</p>
<table>
<thead><tr><th>Reference</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td><code>Sales[Amount]</code></td><td>The whole Amount column (data rows only)</td></tr>
<tr><td><code>[@Amount]</code></td><td>The Amount value in this same row</td></tr>
<tr><td><code>Sales[[#Headers],[Amount]]</code></td><td>The header cell</td></tr>
<tr><td><code>Sales[#Totals]</code></td><td>The total row, when switched on</td></tr>
</tbody>
</table>
<p>The big advantage is that these references grow with the Table. When you add 100 new rows, <code>=SUM(Sales[Amount])</code> includes them without any change. To type one, start the formula, click the column header and Excel writes the reference for you. To show a total row, tick <strong>Total Row</strong> on the Table Design tab.</p>

<h3>Named ranges</h3>
<p>A <strong>named range</strong> gives a cell or range a meaningful name, useful for single values such as a tax rate.</p>
<ol>
<li>Select the cell holding the value, for example <code>18%</code>.</li>
<li>Click the <strong>Name Box</strong> (left of the formula bar), type <code>VAT_Rate</code> and press <strong>Enter</strong>.</li>
<li>Use it: <code>=[@Amount]*VAT_Rate</code>.</li>
</ol>
<p>Manage names under <strong>Formulas</strong>, then <strong>Name Manager</strong>. Names cannot contain spaces, so use an underscore. A named cell behaves as an absolute reference, so you no longer need dollar signs.</p>

<h3>Text functions for analysis</h3>
<ul>
<li><code>LEFT</code>, <code>RIGHT</code> and <code>MID</code> take part of a code, such as the first three letters of a branch code.</li>
<li><code>LEN</code> counts characters, useful for checking phone numbers.</li>
<li><code>FIND</code> gives the position of a character, so <code>=LEFT(A2,FIND("-",A2)-1)</code> takes the text before a hyphen.</li>
<li><code>TEXT(value,"format")</code> turns a number or date into text, for example <code>=TEXT(A2,"mmm yyyy")</code> gives <code>Mar 2025</code>.</li>
</ul>

<h3>Date functions</h3>
<p>Because dates are numbers, you can calculate with them.</p>
<table>
<thead><tr><th>Function</th><th>Example</th><th>Result</th></tr></thead>
<tbody>
<tr><td><code>YEAR</code>, <code>MONTH</code>, <code>DAY</code></td><td><code>=MONTH(A2)</code></td><td>3</td></tr>
<tr><td><code>WEEKDAY</code></td><td><code>=WEEKDAY(A2,2)</code></td><td>1 = Monday ... 7 = Sunday</td></tr>
<tr><td><code>EOMONTH</code></td><td><code>=EOMONTH(A2,0)</code></td><td>Last day of that month</td></tr>
<tr><td><code>DATEDIF</code></td><td><code>=DATEDIF(A2,TODAY(),"y")</code></td><td>Complete years between dates</td></tr>
<tr><td><code>NETWORKDAYS</code></td><td><code>=NETWORKDAYS(A2,B2)</code></td><td>Working days between two dates</td></tr>
</tbody>
</table>
<p>A very useful habit is to add helper columns to your Table, such as <code>Year</code> and <code>Month</code>, so that PivotTables and SUMIFS can group easily. A <code>Month</code> column can be <code>=TEXT([@Date],"yyyy-mm")</code>, which also sorts correctly.</p>

<h3>Try it yourself</h3>
<ol>
<li>Use your <code>Sales</code> Table. Add a <code>Month</code> column with <code>=TEXT([@Date],"yyyy-mm")</code>.</li>
<li>Add a <code>Weekday</code> column with <code>=WEEKDAY([@Date],2)</code>.</li>
<li>Name a cell <code>VAT_Rate</code> holding 18%, and add a <code>VAT</code> column using it.</li>
<li>Show the Total Row and set it to sum the Amount column.</li>
<li>Add three new rows and watch the total update.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Structured references like <code>Sales[Amount]</code> are readable and grow with the Table.</li>
<li>Named ranges give single values a clear name; use underscores, not spaces.</li>
<li>TEXT, LEFT, MID and FIND prepare labels; YEAR, MONTH and EOMONTH prepare dates.</li>
<li>Helper columns such as Month make later analysis simpler.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: In =SUMIFS(Sales[Amount], Sales[Town], "Gulu"), what does the first argument represent?
* The range of values to add up
- The first condition range
- The criterion text
- The number of matching rows
= In SUMIFS the range to add comes first, followed by pairs of criteria range and criterion.

Q: How do you write a criterion for "amount above 50,000" in SUMIFS?
- >50000 without quotation marks
- "greater 50000"
* ">50000"
- ">" + 50000 as two arguments
= Operators and numbers go together inside quotation marks, for example ">50000".

Q: You write =IF(B2>=50,"D",IF(B2>=80,"A","F")). What is the problem?
- Nested IF cannot use numbers
* A mark of 85 returns D, because the tests are in the wrong order
- It needs an AND function
- Nothing is wrong
= Excel stops at the first true test, so the highest boundary must be tested first.

Q: What does IFERROR do?
- Fixes the formula automatically
* Returns a value you choose when the formula gives an error, but it also hides real mistakes
- Only hides #N/A errors
- Stops Excel from calculating
= IFERROR hides all errors, so use it only on the part that may fail for an expected reason. IFNA hides only #N/A.

Q: Which lookup works in every version of Excel, not only Microsoft 365?
- XLOOKUP
- TEXTSPLIT
* INDEX with MATCH
- UNIQUE
= INDEX and MATCH have been in Excel for many years. XLOOKUP needs Microsoft 365 or Excel 2021.

Q: What does the final 0 mean in MATCH(A2, Prices[Item], 0)?
- Return the first column
- Start at row 0
- Ignore capital letters
* Find an exact match
= The 0 asks for an exact match, which is what you want for names and codes.

Q: What is the main benefit of the reference Sales[Amount] over F2:F500?
- It calculates faster
* It is readable and grows automatically when rows are added to the Table
- It ignores blank cells only
- It works without a Table
= Structured references follow the Table size and make formulas easier to understand.

Q: Which formula gives the last day of the month for the date in A2?
- =MONTH(A2)
* =EOMONTH(A2,0)
- =DATEDIF(A2,TODAY(),"m")
- =WEEKDAY(A2)
= EOMONTH with 0 months returns the end of the same month.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: PivotTables',
            'summary' => 'Summarise large lists in seconds: build, group, calculate, filter with slicers, refresh and chart.',
            'lessons' => [
                [
                    'title'   => 'Building your first PivotTable',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to create a PivotTable from a Table and read its four areas. This matters because a PivotTable lets you answer questions such as "sales by town and by item" in a few clicks, without writing a single formula, even when the list has 50,000 rows.</p>

<h3>What a PivotTable is</h3>
<p>A <strong>PivotTable</strong> is a summary report built from your data. It does not change the data. You drag column names into areas, and Excel groups the rows and calculates totals, counts or averages for you.</p>

<h3>Create one</h3>
<ol>
<li>Click any cell inside your Table (for example <code>Sales</code>).</li>
<li>Select the <strong>Insert</strong> tab, then <strong>PivotTable</strong>.</li>
<li>Confirm the table name and choose <strong>New Worksheet</strong>, then <strong>OK</strong>.</li>
<li>A blank report and the <strong>PivotTable Fields</strong> pane appear on the right.</li>
</ol>

<h3>The four areas</h3>
<table>
<thead><tr><th>Area</th><th>What goes there</th></tr></thead>
<tbody>
<tr><td><strong>Rows</strong></td><td>Fields that become the row labels, such as Town</td></tr>
<tr><td><strong>Columns</strong></td><td>Fields that become column headings, such as Item</td></tr>
<tr><td><strong>Values</strong></td><td>The numbers to summarise, such as Amount</td></tr>
<tr><td><strong>Filters</strong></td><td>Fields that let you show one part of the data, such as Year</td></tr>
</tbody>
</table>
<p>Drag <code>Town</code> to Rows and <code>Amount</code> to Values. You get the total sales per town. Now drag <code>Item</code> to Columns and you get a grid of town by item.</p>

<h3>Changing the calculation</h3>
<p>Numeric fields are summed and text fields are counted. To change this, click the field in the Values area, choose <strong>Value Field Settings</strong>, and pick Sum, Count, Average, Max, Min and so on. You can also use the <strong>Show Values As</strong> tab to display each value as a percentage of the column or grand total, which is excellent for shares. Rename the heading to something friendly such as <code>Total sales (UGX)</code> and set the <strong>Number Format</strong> to use thousand separators.</p>

<h3>Sorting, filtering and layout</h3>
<ul>
<li>Right-click a number and choose <strong>Sort</strong>, then <strong>Largest to Smallest</strong> to rank towns.</li>
<li>Use the filter arrow on Row Labels to show only some items, or <strong>Top 10</strong> via Value Filters.</li>
<li>On the <strong>Design</strong> tab, change <strong>Report Layout</strong> to <strong>Tabular Form</strong>, and turn Subtotals and Grand Totals on or off.</li>
</ul>

<h3>Recommended PivotTables</h3>
<p>The <strong>Insert</strong> tab also offers <strong>Recommended PivotTables</strong>, which previews some suggested summaries. It is a good way to explore, but learn the manual method because it gives you control.</p>

<h3>Rules that keep PivotTables healthy</h3>
<ul>
<li>Every column needs a header and the headers must be unique.</li>
<li>No blank rows or merged cells in the source. A Table avoids these problems.</li>
<li>Do not type over the PivotTable; change it with the Fields pane.</li>
<li>If a number field is being counted instead of summed, some of its cells are probably text (see Module 1).</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Create a PivotTable from your <code>Sales</code> Table on a new sheet.</li>
<li>Put <code>Town</code> in Rows and <code>Amount</code> in Values.</li>
<li>Add <code>Item</code> to Columns and check that the grand total matches your SUMIFS total from Module 2.</li>
<li>Right-click the values and use <strong>Show Values As</strong> to see percentage of grand total.</li>
<li>Sort towns from largest to smallest.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A PivotTable summarises data without changing it.</li>
<li>Rows and Columns label the report, Values calculates, Filters narrows.</li>
<li>Value Field Settings change Sum to Count, Average and percentages.</li>
<li>A Table source with clean headers prevents most problems.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Grouping dates and numbers, and calculated fields',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to group dates into months and quarters, group numbers into bands, and add your own calculated field to a PivotTable. This matters because decision makers rarely want daily detail. They want "sales per month" or "learners by age band", and sometimes a result such as profit that is not a column in the data.</p>

<h3>Grouping dates</h3>
<p>If your dates are real dates (not text), Excel can group them in one step. In recent versions of Excel, dates dropped into Rows may be grouped automatically.</p>
<ol>
<li>Drag <code>Date</code> into Rows and <code>Amount</code> into Values.</li>
<li>Right-click any date in the report and choose <strong>Group</strong>.</li>
<li>Select <strong>Months</strong> and <strong>Years</strong> (and <strong>Quarters</strong> if you wish), then <strong>OK</strong>.</li>
</ol>
<p>Selecting both Months and Years prevents January of different years from being merged together. If the Group command is greyed out or gives an error, the date column contains blanks or text. Fix the data first. To remove grouping, right-click and choose <strong>Ungroup</strong>.</p>

<h3>Grouping numbers into bands</h3>
<p>Drag a numeric field such as <code>Age</code> or <code>Marks</code> into Rows, right-click and choose <strong>Group</strong>. Type a <strong>Starting at</strong> value, an <strong>Ending at</strong> value and a <strong>By</strong> size, for example 0, 100 and 10. You get bands such as 0-9, 10-19 and so on. Drag the same field into Values with Count to see how many records fall in each band, which is a quick histogram.</p>

<h3>Grouping text items</h3>
<p>Select several row labels with <strong>Ctrl + click</strong>, right-click and choose <strong>Group</strong>. Excel creates a group such as <code>Group1</code>, which you can rename by typing over it. This is handy for combining towns into regions without changing the source data. A cleaner long-term method is to add a <code>Region</code> column in the source with a lookup.</p>

<h3>Calculated fields</h3>
<p>A <strong>calculated field</strong> is a new field made from other fields, calculated on the totals.</p>
<ol>
<li>Click inside the PivotTable.</li>
<li>Select the <strong>PivotTable Analyze</strong> tab, then <strong>Fields, Items &amp; Sets</strong>, then <strong>Calculated Field</strong>.</li>
<li>Type a name such as <code>Profit</code>.</li>
<li>In Formula type <code>= Amount - Cost</code>, choosing the fields with <strong>Insert Field</strong>.</li>
<li>Click <strong>Add</strong>, then <strong>OK</strong>, and drag the new field into Values.</li>
</ol>
<p>Important limit: a calculated field sums the fields first and then applies the formula. For <code>Amount - Cost</code> that is fine. For an average price such as <code>Amount / Quantity</code> it gives the total amount divided by the total quantity, which is usually what you want, but it is not the average of row prices. Calculated fields cannot use lookups, and the field must exist in the source. Often the simplest approach is to add a <code>Profit</code> column in the Table and use it in the PivotTable.</p>

<h3>Details on demand</h3>
<p>Double-click any number in a PivotTable to see the rows behind it in a new sheet. This is a quick way to check or explain a figure. Delete that sheet afterwards to keep the workbook tidy.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create a PivotTable with <code>Date</code> in Rows and <code>Amount</code> in Values.</li>
<li>Group the dates by Months and Years.</li>
<li>Replace dates with <code>Quantity</code> in Rows and group the numbers in steps of 5.</li>
<li>Add a <code>Cost</code> column to your data, then create a calculated field named <code>Profit</code>.</li>
<li>Double-click one total to see the rows behind it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Group dates by months and years together; the data must hold real dates with no blanks.</li>
<li>Number grouping makes bands and quick histograms.</li>
<li>Calculated fields work on totals, not row by row; a source column is often simpler.</li>
<li>Double-click a value to see the records behind it.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Slicers, timelines and refreshing',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to add slicers and timelines for point-and-click filtering, connect them to several PivotTables, and refresh your report when the data changes. This matters because a good report is used by other people, and they should be able to explore it without understanding PivotTables.</p>

<h3>Slicers</h3>
<p>A <strong>slicer</strong> is a set of buttons that filter a PivotTable. Click a button and the report shows only that item.</p>
<ol>
<li>Click inside the PivotTable.</li>
<li>Choose the <strong>PivotTable Analyze</strong> tab, then <strong>Insert Slicer</strong>.</li>
<li>Tick one or more fields, such as <code>Town</code> and <code>Item</code>, and click <strong>OK</strong>.</li>
<li>Click a button to filter. Hold <strong>Ctrl</strong> to pick several. Use the small funnel icon with a cross to clear the filter.</li>
</ol>
<p>Slicers can be resized, given several columns, and restyled on the <strong>Slicer</strong> tab. Choose clear captions, because the caption is what the reader sees.</p>

<h3>Timelines</h3>
<p>A <strong>timeline</strong> is a slicer made for dates. Choose <strong>Insert Timeline</strong>, tick the date field and drag along the bar to choose a period. The drop-down at the top switches between Days, Months, Quarters and Years. A timeline needs a real date column. In Excel Online, a timeline may not be available, so check which features your version offers.</p>

<h3>One slicer for many PivotTables</h3>
<p>By default a slicer controls only the PivotTable it was created from. To connect more:</p>
<ol>
<li>Click the slicer.</li>
<li>On the <strong>Slicer</strong> tab choose <strong>Report Connections</strong> (older versions call it <strong>PivotTable Connections</strong>).</li>
<li>Tick every PivotTable that should respond.</li>
</ol>
<p>The PivotTables must share the same source or the same <em>PivotCache</em>, which is the stored copy of the data. PivotTables created by copying an existing one share it. This is the foundation of a dashboard where one click updates every table and chart.</p>

<h3>Refreshing</h3>
<p>A PivotTable does <strong>not</strong> update automatically when the source data changes. You must refresh it.</p>
<ul>
<li>Right-click the PivotTable and choose <strong>Refresh</strong>, or press <strong>Alt + F5</strong>.</li>
<li><strong>Refresh All</strong> on the Data tab (<strong>Ctrl + Alt + F5</strong>) updates every PivotTable in the workbook.</li>
<li>In PivotTable Options, on the Data tab, tick <strong>Refresh data when opening the file</strong> so a report is current when opened.</li>
</ul>
<p>If new rows do not appear after refreshing, the source is probably a plain range that did not grow. Convert it to a Table, or change the source with <strong>Change Data Source</strong>.</p>

<h3>Stop column widths jumping</h3>
<p>In PivotTable Options, on the Layout and Format tab, untick <strong>Autofit column widths on update</strong> to keep your formatting stable after a refresh. Also remember that old items can remain in drop-down lists after deleted data. Under the Data tab of the options, set <strong>Number of items to retain per field</strong> to <strong>None</strong>.</p>

<h3>Try it yourself</h3>
<ol>
<li>Add a slicer for <code>Town</code> to your PivotTable and click each town.</li>
<li>Copy the PivotTable to another place on the sheet and change it to show Items by Amount.</li>
<li>Connect the slicer to both PivotTables with Report Connections.</li>
<li>Add a new row to the Sales Table, then press <strong>Alt + F5</strong> and check the totals change.</li>
<li>Add a timeline for dates if your Excel has one.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Slicers and timelines filter with buttons that anyone can use.</li>
<li>Report Connections let one slicer control several PivotTables that share a source.</li>
<li>PivotTables never refresh by themselves; use Alt + F5 or Refresh All.</li>
<li>A Table source makes new rows arrive automatically on refresh.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'PivotCharts',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to create a PivotChart from a PivotTable, change its type and keep it tidy. This matters because a chart that moves with your filters is far more useful than a picture drawn once. When the viewer clicks a slicer, the chart updates with the table.</p>

<h3>Create a PivotChart</h3>
<ol>
<li>Click inside a PivotTable, for example Total sales by month.</li>
<li>Select the <strong>PivotTable Analyze</strong> tab, then <strong>PivotChart</strong>.</li>
<li>Choose a chart type, such as <strong>Column</strong> or <strong>Line</strong>, and click <strong>OK</strong>.</li>
</ol>
<p>The chart and the PivotTable are linked. Change the fields in the PivotTable and the chart follows. Filter the chart and the table follows. Field buttons appear on the chart; hide them from the <strong>PivotChart Analyze</strong> tab (<strong>Field Buttons</strong>) before sharing, so that it looks clean.</p>

<h3>Choose a sensible layout</h3>
<p>A chart is easy to read when the PivotTable behind it is simple.</p>
<ul>
<li>One field in Rows (for example Month) and one in Values gives a simple column or line chart.</li>
<li>Adding a field in Columns creates several series, such as one line per town. Keep to five series or fewer.</li>
<li>Use a bar chart (horizontal) when the labels are long, such as item names.</li>
</ul>

<h3>Limits to know</h3>
<ul>
<li>Some chart types, such as scatter and bubble, are not available as PivotCharts.</li>
<li>Adding a trendline or manually formatting a data point can disappear after a refresh or a change of layout. Use chart styles and PivotTable options, and check after refreshing.</li>
<li>If you only need a fixed chart, you can build a normal chart from cells next to the PivotTable using formulas such as <code>GETPIVOTDATA</code>, which Excel writes when you click a PivotTable cell in a formula. It is less flexible but more stable.</li>
</ul>

<h3>Formatting steps</h3>
<ol>
<li>Click the chart title and write a message, not a label. "Gulu led sales in every quarter" is more useful than "Sales by town".</li>
<li>Click the <strong>+</strong> button beside the chart to switch on data labels, or off gridlines.</li>
<li>Use <strong>Format Axis</strong> to show numbers in thousands or millions so that labels are shorter.</li>
<li>Right-click the chart and choose <strong>Move Chart</strong> to put it on its own sheet or on your dashboard sheet.</li>
</ol>

<h3>Charts from the same PivotTable</h3>
<p>You can have only one PivotChart per PivotTable. If you need two charts, make a second PivotTable, copy it, or use the same cache. A slicer connected to both will update both charts together. This is the pattern used in the dashboard module.</p>

<h3>Try it yourself</h3>
<ol>
<li>Build a PivotTable with <code>Month</code> in Rows and <code>Amount</code> in Values.</li>
<li>Insert a PivotChart of type Line.</li>
<li>Add <code>Town</code> to Columns, and see how the chart changes. Then remove it.</li>
<li>Hide the field buttons and write a message-style title.</li>
<li>Click your slicer and watch the chart follow.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A PivotChart is a chart linked to a PivotTable and moves with its filters.</li>
<li>Keep the PivotTable simple and limit the number of series.</li>
<li>Hide field buttons and write a title that states the message.</li>
<li>Check manual formatting after a refresh, because it can be lost.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What are the four areas of the PivotTable Fields pane?
- Headers, Footers, Totals and Notes
* Filters, Columns, Rows and Values
- Source, Target, Sum and Count
- Data, Chart, Slicer and Timeline
= Rows and Columns label the report, Values holds the calculation and Filters narrows what is shown.

Q: Your PivotTable counts an Amount field instead of summing it. What is the most likely cause?
- The field name is too long
- Excel is set to manual calculation
* Some Amount cells contain text or are blank, so Excel chooses Count
- The PivotTable is too wide
= A field containing text is counted by default. Convert text-numbers to real numbers, then change the summary to Sum.

Q: Why select both Months and Years when grouping dates?
- Excel requires it
* So that the same month in different years is not merged together
- It makes the PivotTable faster
- It converts text into dates
= Grouping by months alone combines every January. Adding Years keeps each year separate.

Q: A calculated field named Profit uses the formula = Amount - Cost. When is the calculation performed?
- On each source row before summing
* On the summed totals of the fields
- Only when you refresh
- Only on the grand total
= Calculated fields sum the underlying fields first and then apply the formula to the totals.

Q: What does a slicer do?
- Splits the data into separate worksheets
* Gives buttons that filter one or more PivotTables
- Removes duplicate rows
- Calculates percentages
= A slicer is a clickable filter. It can be connected to several PivotTables that share a source.

Q: You add new rows to the source but the PivotTable does not change. What should you do?
- Delete the PivotTable and start again
- Retype the Rows fields
* Refresh it (Alt + F5), and make sure the source is a Table that includes the new rows
- Sort the data
= PivotTables do not update by themselves. A Table source grows with new rows so a refresh picks them up.

Q: Which statement about PivotCharts is true?
- They are independent of the PivotTable
- They can only be pie charts
* They are linked to a PivotTable and change when it is filtered or rearranged
- They refresh automatically in real time without any action
= A PivotChart and its PivotTable move together. Like the PivotTable, it needs refreshing when the source changes.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Charts and visual storytelling',
            'summary' => 'Pick the right chart, combine chart types, add sparklines and conditional formatting, and avoid misleading visuals.',
            'lessons' => [
                [
                    'title'   => 'Choosing the right chart',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to match a question to a chart type and build a clear, readable chart. This matters because the wrong chart hides the message. A pie chart with eleven slices or a 3-D column chart can confuse readers who would have understood a simple bar chart straight away.</p>

<h3>Start with the question</h3>
<p>Before touching the Insert tab, write in one sentence what you want the reader to learn. Then pick the chart from the table below.</p>
<table>
<thead><tr><th>Your question</th><th>Best chart</th></tr></thead>
<tbody>
<tr><td>How do categories compare? (towns, items, classes)</td><td>Bar or column chart</td></tr>
<tr><td>How does something change over time?</td><td>Line chart (or column chart for a few periods)</td></tr>
<tr><td>What share does each part have of the whole?</td><td>Stacked bar, or a pie/doughnut with 2 to 4 slices only</td></tr>
<tr><td>Is there a relationship between two numbers?</td><td>Scatter chart (XY)</td></tr>
<tr><td>How are values spread? (marks, ages)</td><td>Histogram</td></tr>
<tr><td>How did a total build up or change?</td><td>Waterfall chart</td></tr>
</tbody>
</table>

<h3>Create a chart</h3>
<ol>
<li>Select the data, including the headers. A small PivotTable or summary table works well.</li>
<li>Choose the <strong>Insert</strong> tab and click <strong>Recommended Charts</strong> to see suggestions, or pick a type directly from the chart groups.</li>
<li>Click the chart. Use the <strong>+</strong> button for chart elements and the paintbrush for styles.</li>
</ol>
<p>Shortcut: select your data and press <strong>Alt + F1</strong> to insert a default chart on the same sheet, or <strong>F11</strong> to put it on its own sheet.</p>

<h3>Rules of clarity</h3>
<ul>
<li><strong>Sort bars</strong> from largest to smallest unless the categories have a natural order such as months.</li>
<li><strong>Use horizontal bars</strong> when category names are long.</li>
<li><strong>Write a message title</strong>, for example "Lira sales doubled after March".</li>
<li><strong>Remove clutter</strong>: delete gridlines you do not need, avoid 3-D effects, and do not repeat information in both labels and a legend.</li>
<li><strong>Label directly</strong>: put data labels on the bars or lines and drop the legend when you can.</li>
<li><strong>Use colour with purpose</strong>: one colour for most bars and a strong colour for the one you want people to notice.</li>
<li><strong>Keep axis titles short</strong> and include the unit, such as UGX thousand.</li>
</ul>

<h3>When pie charts work</h3>
<p>Human eyes are poor at comparing angles. A pie chart is acceptable only when there are few slices, they add up to a whole, and one or two differ clearly. For more than four categories, use a sorted bar chart.</p>

<h3>Accessibility</h3>
<p>Do not rely on colour alone, because some readers are colour-blind or print in black and white. Use direct labels, patterns or different line styles. Right-click the chart, choose <strong>Edit Alt Text</strong> and describe the message for readers who use screen readers.</p>

<h3>Try it yourself</h3>
<ol>
<li>Make a small table of sales by town for five towns.</li>
<li>Insert a column chart, then change it to a bar chart and sort the table from largest to smallest.</li>
<li>Delete the legend and gridlines, and add data labels.</li>
<li>Colour the top town in a strong colour and the others in grey.</li>
<li>Write a title that states the message in one sentence.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Choose the chart from the question: compare, trend, share, relationship or spread.</li>
<li>Sort bars, label directly and remove clutter.</li>
<li>Use colour to draw attention, not to decorate.</li>
<li>Add alt text and avoid depending on colour alone.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Combo charts and sparklines',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to combine two chart types in one chart, use a secondary axis wisely, and add tiny sparkline charts inside cells. This matters because some stories need two measures together, such as sales in shillings and the number of customers, and sparklines let you show a whole trend in the space of one cell.</p>

<h3>Combo charts</h3>
<p>A <strong>combo chart</strong> mixes types, for example columns for revenue and a line for profit margin. Use it when the two series share the same categories (such as months) but have different units or sizes.</p>
<ol>
<li>Select your data, including headers. For example <code>Month</code>, <code>Revenue</code> and <code>Margin %</code>.</li>
<li>Choose <strong>Insert</strong>, then <strong>Recommended Charts</strong>, then the <strong>All Charts</strong> tab, then <strong>Combo</strong>.</li>
<li>Set <code>Revenue</code> to <strong>Clustered Column</strong> and <code>Margin %</code> to <strong>Line</strong>.</li>
<li>Tick <strong>Secondary Axis</strong> for the series with a different scale, here <code>Margin %</code>, and click <strong>OK</strong>.</li>
</ol>

<h3>Use secondary axes with care</h3>
<p>Two vertical axes can be misleading, because the reader may think that when the line crosses the columns something important happened. It usually did not; the scales were just chosen that way. Protect your reader:</p>
<ul>
<li>Colour each axis title to match its series, and label both axes with units.</li>
<li>Make sure both axes start in a sensible place; avoid stretching a scale to force a crossing.</li>
<li>If the two measures are not closely related, use two small charts instead.</li>
</ul>
<p>Another useful combo is actual versus target: columns for actual values and a line or marker for the target.</p>

<h3>Sparklines</h3>
<p>A <strong>sparkline</strong> is a tiny chart that sits inside a single cell. It shows the shape of a trend without axes or labels, perfect for a table with one row per item.</p>
<ol>
<li>Click the empty cell at the end of a row, where the sparkline should go.</li>
<li>Choose <strong>Insert</strong>, then pick <strong>Line</strong>, <strong>Column</strong> or <strong>Win/Loss</strong> in the Sparklines group.</li>
<li>For <strong>Data Range</strong>, select the monthly values of that row, and click <strong>OK</strong>.</li>
<li>Drag the fill handle down to copy the sparkline for other rows.</li>
</ol>
<p>On the <strong>Sparkline</strong> tab, switch on <strong>High Point</strong> and <strong>Last Point</strong> markers to guide the eye. Under <strong>Axis</strong>, choose <strong>Same for All Sparklines</strong> for both minimum and maximum if you want to compare sizes across rows. Otherwise each row uses its own scale, which shows the shape but hides which row is larger. Widen the row height and column so that the sparkline is visible.</p>

<h3>Good uses of sparklines</h3>
<ul>
<li>Monthly sales per branch, one sparkline per branch.</li>
<li>Attendance per class across a term.</li>
<li>Stock levels of each product across the year.</li>
</ul>
<p>Sparklines are cell backgrounds and not objects. They are not available in all lightweight viewers, and if the data in the source range is deleted, the sparkline empties.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create a table with months in rows (6 months) and columns <code>Revenue</code> and <code>Margin %</code>.</li>
<li>Insert a combo chart with columns for revenue and a line for margin on a secondary axis.</li>
<li>Add axis titles with units and give the chart a message title.</li>
<li>Create a second table with three products across six months and add a line sparkline for each product.</li>
<li>Switch on the High Point marker and try the same-scale axis setting.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A combo chart mixes chart types for related measures with different units.</li>
<li>A secondary axis needs clear labels and honest scales.</li>
<li>Sparklines show a trend inside one cell; use a common axis to compare rows.</li>
<li>Use two separate charts if one combo would confuse the reader.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Conditional formatting and misleading charts',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use conditional formatting to make patterns visible in a table, and recognise the common ways charts mislead. This matters because both tools are powerful. A well-formatted table lets a manager see problems in seconds, while a careless chart can lead people to the wrong decision.</p>

<h3>Conditional formatting</h3>
<p><strong>Conditional formatting</strong> changes a cell's look automatically depending on its value. Select the cells first, then choose <strong>Home</strong>, then <strong>Conditional Formatting</strong>.</p>
<table>
<thead><tr><th>Tool</th><th>Good for</th></tr></thead>
<tbody>
<tr><td>Highlight Cells Rules</td><td>Values above a number, between two numbers, text containing a word, duplicates</td></tr>
<tr><td>Top/Bottom Rules</td><td>Top 10 items, below average</td></tr>
<tr><td>Data Bars</td><td>Showing size inside cells, like mini bars</td></tr>
<tr><td>Color Scales</td><td>Heat maps: low to high with colour</td></tr>
<tr><td>Icon Sets</td><td>Arrows or traffic lights for status</td></tr>
</tbody>
</table>

<h3>Rules using a formula</h3>
<p>For full control choose <strong>New Rule</strong>, then <strong>Use a formula to determine which cells to format</strong>. Write the formula for the first selected cell, with a dollar sign to fix the column when you want the whole row coloured:</p>
<pre><code>=$E2&lt;$F2</code></pre>
<p>If this rule is applied to <code>A2:F100</code>, a whole row is highlighted whenever the stock in column E is below the reorder level in column F. Manage your rules under <strong>Conditional Formatting</strong>, then <strong>Manage Rules</strong>, where you can change the order, edit the range, or delete a rule. Too many rules slow a workbook down, so clear unused ones.</p>

<h3>Use restraint</h3>
<ul>
<li>Choose a few meaningful colours; red for danger, amber for caution and green for good only when that is true.</li>
<li>Do not colour a whole sheet; if everything is highlighted, nothing stands out.</li>
<li>Add a small key that says what each colour means.</li>
<li>Remember colour-blind readers: pair colours with icons or words.</li>
</ul>

<h3>Misleading charts to avoid</h3>
<ol>
<li><strong>A truncated axis.</strong> A column chart that starts at 90 instead of 0 makes a small difference look huge. Bars must start at zero, because bar length means the value. Line charts may use a different starting point, but say so.</li>
<li><strong>Distorted scales.</strong> Stretching or squeezing the height of a chart changes the apparent slope.</li>
<li><strong>3-D effects.</strong> Perspective makes front items look bigger.</li>
<li><strong>Too many slices or lines.</strong> Beyond five or six series, no one can follow them.</li>
<li><strong>Cherry-picked periods.</strong> Showing only the three best months hides a bad year.</li>
<li><strong>Dual axes with unrelated measures</strong>, which create false patterns.</li>
<li><strong>Missing context.</strong> Show units, the time period and the data source.</li>
</ol>
<p>A quick honesty test: ask whether a person who sees the chart for five seconds would get a fair impression of the data.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create a stock table with columns <code>Item</code>, <code>In stock</code> and <code>Reorder level</code>.</li>
<li>Add a formula-based rule that colours the item name red when stock is below the reorder level.</li>
<li>Add data bars to the <code>In stock</code> column.</li>
<li>Make a column chart of two close values with the axis starting at 90, then reset the axis to start at 0 and compare the impression.</li>
<li>Open Manage Rules and check that you understand every rule listed.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Conditional formatting makes patterns visible; formula rules give full control.</li>
<li>Use few, meaningful colours with a key, and do not rely on colour alone.</li>
<li>Bars must start at zero; avoid 3-D, crowded charts and cherry-picked periods.</li>
<li>Always show units, period and source.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: You want to show how monthly sales changed over twelve months. Which chart is the best first choice?
- Pie chart
- Scatter chart
* Line chart
- Doughnut chart
= A line chart shows change over time clearly. Pie and doughnut charts show parts of a whole at one point in time.

Q: Why should a column chart axis start at zero?
- Excel will not draw it otherwise
* Bar length represents the value, so a cut axis exaggerates differences
- It makes labels fit better
- Zero is the default colour
= A truncated axis on bars makes small differences look large and misleads the reader.

Q: Which is a good title for a chart?
- Chart 1
- Sales by town
* Gulu sales led every quarter of 2025
- Figure showing the sales numbers
= A message title tells the reader what to notice; a label only says what the chart is.

Q: When is a pie chart acceptable?
* When there are few slices that make up a whole and some clearly differ
- When there are 12 categories to compare over time
- When showing a trend
- Always, because it is familiar
= People compare angles poorly, so use pie charts only for a few parts of a whole.

Q: What does a sparkline show?
- A full-size chart with axes and legend
- A map of values
* A tiny chart inside one cell that shows the shape of a trend
- A forecast
= Sparklines are cell-sized charts, ideal for one trend per row.

Q: What is the risk of a combo chart with a secondary axis?
- Excel cannot print it
* The reader may see a false link where the line and columns cross because of how the scales were chosen
- It needs a Table
- It always hides data
= Different scales can create apparent crossings that mean nothing. Label both axes and use separate charts when unsure.

Q: A rule uses the formula =$E2<$F2 on A2:F100. What does it do?
- Formats only column E
- Formats cells when column F is empty
* Formats the entire row when the value in column E is below the value in column F
- Causes an error because of the dollar sign
= The dollar sign fixes the column, so each row compares its own E and F values and the whole row is formatted.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Dashboards and reporting',
            'summary' => 'Design a clear one-page dashboard, link it to your analysis, and complete a project from raw data to a shared report.',
            'lessons' => [
                [
                    'title'   => 'Dashboard design and KPIs',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to plan a dashboard, choose useful KPIs and lay out a one-page view that a busy person can read in a minute. This matters because a dashboard that tries to show everything ends up helping nobody. Planning on paper first saves much rework.</p>

<h3>What a dashboard is</h3>
<p>A <strong>dashboard</strong> is a single page that shows the most important numbers and charts about a topic, usually with a few controls to filter. A <strong>KPI</strong> (key performance indicator) is a number that tells you how well you are doing against a goal, for example monthly sales, learner pass rate or average days to deliver an order.</p>

<h3>Plan before you build</h3>
<ol>
<li><strong>Who is the reader?</strong> A school head teacher, a shop owner and a donor need different views.</li>
<li><strong>What decisions will they make?</strong> List 3 to 5 questions the dashboard must answer.</li>
<li><strong>Choose 3 to 6 KPIs.</strong> Each one should be measurable, tied to a goal and available in your data.</li>
<li><strong>Sketch the page</strong> on paper: where the KPIs, charts and filters go.</li>
</ol>

<h3>A reliable layout</h3>
<table>
<thead><tr><th>Area</th><th>Content</th></tr></thead>
<tbody>
<tr><td>Top left</td><td>Title, period and the date the data was last updated</td></tr>
<tr><td>Top row</td><td>KPI cards: large numbers with small captions</td></tr>
<tr><td>Middle</td><td>The two or three main charts (trend, comparison)</td></tr>
<tr><td>Side or top</td><td>Slicers and timeline</td></tr>
<tr><td>Bottom</td><td>A detail table or notes, and the data source</td></tr>
</tbody>
</table>
<p>People read from top left to bottom right, so put the most important item first. Keep to one screen, with a clear alignment grid and consistent spacing. Use two or three colours and one or two fonts.</p>

<h3>KPI cards</h3>
<p>A KPI card is just a cell showing a large number linked to your analysis. For total sales use <code>=SUM(Sales[Amount])</code>; for the number of orders <code>=COUNTA(Sales[Date])</code>; for average order value divide one by the other, wrapped in IFERROR. Format with a large font, thousand separators and a short caption such as "Total sales (UGX)". To show change, add a comparison, for example "+12% vs last month", calculated with a formula and coloured with conditional formatting or an arrow icon set.</p>

<h3>Good KPIs and bad KPIs</h3>
<ul>
<li>Good: pass rate this term versus last term; average delivery time; share of repeat customers.</li>
<li>Poor: numbers nobody can change or act on; totals without a comparison; a dozen measures with no priority.</li>
</ul>
<p>Always give context: compared with a target, last period or last year. A number alone, such as 4.2 million, is hard to judge.</p>

<h3>Set up the workbook</h3>
<ul>
<li>Use separate sheets: <code>Data</code>, <code>Analysis</code> (PivotTables and helper formulas) and <code>Dashboard</code>.</li>
<li>Turn off gridlines on the Dashboard sheet: <strong>View</strong> tab, untick <strong>Gridlines</strong>.</li>
<li>Set the zoom so the whole page fits the screen, and freeze nothing that wastes space.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Choose a topic you know, such as shop sales or class marks, and write down the reader and three questions.</li>
<li>Pick three KPIs and note how each is calculated from your data.</li>
<li>Sketch the layout on paper with boxes for KPIs, two charts and a slicer.</li>
<li>In Excel, create sheets named Data, Analysis and Dashboard.</li>
<li>Build one KPI card with a formula and a clear caption.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Start with the reader and the decisions, not the charts.</li>
<li>Three to six KPIs with context are better than a crowd of numbers.</li>
<li>Layout: title and date, KPI cards, main charts, filters, notes.</li>
<li>Keep Data, Analysis and Dashboard on separate sheets.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Building the dashboard: linking, protecting and sharing',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to assemble a dashboard from PivotTables and charts, lock it so that it cannot be broken, and share it safely. This matters because a dashboard is only valuable if people can open it, trust it and use it without damaging it.</p>

<h3>Assemble the pieces</h3>
<ol>
<li>On the <code>Analysis</code> sheet, build one PivotTable per chart: for example sales by month, sales by town and sales by item.</li>
<li>For each, insert a PivotChart, format it, hide field buttons, then cut it (<strong>Ctrl + X</strong>) and paste it on the <code>Dashboard</code> sheet.</li>
<li>Insert slicers for the key fields (such as Town and Year) and a timeline if available.</li>
<li>Use <strong>Report Connections</strong> to link each slicer to every PivotTable, so one click updates all the charts.</li>
<li>Build KPI cards from formulas. KPI cards should respond to the slicers, which formulas on the raw table do not. Two ways exist: read values from a PivotTable using <code>GETPIVOTDATA</code>, or use a PivotTable's Grand Total cell by clicking it when writing the formula.</li>
</ol>
<p>Align everything: select several objects holding <strong>Ctrl</strong>, then use <strong>Shape Format</strong>, <strong>Align</strong> and <strong>Distribute</strong>. Hide the Analysis sheet from casual view by right-clicking its tab and choosing <strong>Hide</strong>, if you wish, but document it so that others can find it.</p>

<h3>Protecting the workbook</h3>
<p>Protection prevents accidents. It is not strong security, so never rely on it for confidential data.</p>
<ol>
<li>Select any cells people must be able to change, such as an input cell, and press <strong>Ctrl + 1</strong>, then on the <strong>Protection</strong> tab untick <strong>Locked</strong>.</li>
<li>Choose <strong>Review</strong>, then <strong>Protect Sheet</strong>. Enter a password if you want one, and tick the actions you still allow, such as <strong>Use PivotTable &amp; PivotChart</strong>.</li>
<li>For the structure, choose <strong>Protect Workbook</strong> to stop sheets being added, deleted or moved.</li>
</ol>
<p>Write the password down in a safe place. Lost passwords are very hard to recover. Slicer buttons can only be clicked on a protected sheet if the slicer object has <strong>Locked</strong> switched off in its Slicer Properties, and the option to edit objects is allowed in Protect Sheet. Test your protection by opening the file as a user would.</p>

<h3>Sharing</h3>
<table>
<thead><tr><th>Method</th><th>Notes</th></tr></thead>
<tbody>
<tr><td>Share link from OneDrive or SharePoint (File, Share)</td><td>Several people can open or edit; choose view-only for readers; versions are kept</td></tr>
<tr><td>Email as an attachment</td><td>Simple, but copies multiply and become out of date</td></tr>
<tr><td>PDF</td><td>Fixed snapshot; no slicers; best for printing and archiving</td></tr>
</tbody>
</table>
<p>Before you share, remove personal or confidential data, because a PivotTable keeps a copy of source data. Use <strong>File</strong>, <strong>Info</strong>, <strong>Check for Issues</strong>, <strong>Inspect Document</strong> to find hidden information. Name the file clearly, such as <code>Sales-Dashboard-2025-Q1.xlsx</code>.</p>

<h3>Test like a user</h3>
<ul>
<li>Click every slicer and confirm all charts and cards change.</li>
<li>Refresh with new data and check the totals against the source.</li>
<li>Open on a smaller screen and confirm the layout still fits.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Build two PivotTables from your Sales Table and a PivotChart for each.</li>
<li>Place both charts on the Dashboard sheet with a Town slicer connected to both.</li>
<li>Add one KPI card for total sales that uses a PivotTable grand total.</li>
<li>Protect the Dashboard sheet allowing PivotTable use, then test the slicer.</li>
<li>Save a PDF copy using <strong>File</strong>, <strong>Export</strong>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>One PivotTable per chart; connect slicers to all of them with Report Connections.</li>
<li>KPI cards that must react to slicers should read from PivotTables.</li>
<li>Sheet protection prevents accidents, not determined access; keep the password safe.</li>
<li>Check for hidden data before sharing, and test the file as a user would.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Worked project: from raw data to report',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will have completed an end-to-end analysis: raw data, cleaning, formulas, PivotTables, charts, a dashboard, and an exported report. This matters because skills become useful only when they are joined together. Follow the steps below with the data suggested, or use your own data from a shop, school or project.</p>

<h3>The scenario</h3>
<p>A small shop with branches in three towns recorded a year of sales. The owner asks: <em>Which branch and which product groups perform best, how do sales change through the year, and what should we stock more of?</em> The raw export has columns <code>Date</code>, <code>Branch</code>, <code>Product</code>, <code>Category</code>, <code>Quantity</code> and <code>Unit Price</code>. Make about 150 rows yourself, or use a school version with <code>Class</code>, <code>Subject</code> and <code>Marks</code>.</p>

<h3>Step 1: Prepare</h3>
<ol>
<li>Keep an original copy of the data on its own sheet.</li>
<li>Convert the data to a Table named <code>Sales</code>.</li>
<li>Clean text with TRIM and PROPER, check dates and numbers with ISNUMBER, and remove duplicates carefully.</li>
<li>Add validation to <code>Branch</code> and <code>Category</code> for future entries.</li>
</ol>

<h3>Step 2: Add calculations</h3>
<ol>
<li>Add <code>Amount</code> as <code>=[@Quantity]*[@[Unit Price]]</code>.</li>
<li>Add <code>Month</code> as <code>=TEXT([@Date],"yyyy-mm")</code>.</li>
<li>If you have a price list, look up cost with XLOOKUP or INDEX-MATCH, and add <code>Profit</code>.</li>
</ol>

<h3>Step 3: Analyse</h3>
<ol>
<li>PivotTable 1: <code>Month</code> in Rows, <code>Amount</code> in Values (sales trend).</li>
<li>PivotTable 2: <code>Branch</code> in Rows, <code>Amount</code> and <code>Profit</code> in Values, sorted largest first.</li>
<li>PivotTable 3: <code>Category</code> in Rows, <code>Amount</code> shown as percentage of grand total.</li>
<li>Cross-check the grand total of each PivotTable against <code>=SUM(Sales[Amount])</code>. They must match. If they do not, find the reason before going on.</li>
</ol>

<h3>Step 4: Visualise</h3>
<ol>
<li>A line chart for the monthly trend.</li>
<li>A sorted bar chart for branches.</li>
<li>A bar or doughnut chart (few slices only) for category share.</li>
<li>Use message titles, direct labels and one highlight colour.</li>
</ol>

<h3>Step 5: Build the dashboard</h3>
<ol>
<li>On the <code>Dashboard</code> sheet add the title, period and "data last updated" date.</li>
<li>Add three KPI cards: total sales, total profit and best branch.</li>
<li>Place the three charts, with slicers for <code>Branch</code> and <code>Category</code> and a timeline.</li>
<li>Connect all slicers to all PivotTables, hide gridlines and align the objects.</li>
</ol>

<h3>Step 6: Protect, print and export</h3>
<ol>
<li>Protect the Dashboard sheet while allowing PivotTable use.</li>
<li>Set the print area: <strong>Page Layout</strong>, <strong>Print Area</strong>, <strong>Set Print Area</strong>. Choose <strong>Landscape</strong> orientation and <strong>Fit Sheet on One Page</strong> under Scale to Fit.</li>
<li>Add a header or footer with the file name and date from <strong>Insert</strong>, <strong>Header &amp; Footer</strong>.</li>
<li>Choose <strong>File</strong>, <strong>Export</strong>, <strong>Create PDF/XPS</strong>, and save a PDF for sharing.</li>
</ol>

<h3>Step 7: Write the findings</h3>
<p>Under the dashboard, or on a short separate page, write three sentences: what you found, why it matters and what you recommend, such as "Gulu leads sales, but Lira has the highest profit margin; stock more of the soap range there." Numbers show, sentences explain.</p>

<h3>Try it yourself</h3>
<ol>
<li>Complete Steps 1 to 3 with at least 100 rows of data.</li>
<li>Confirm that all PivotTable totals match the SUM of the Table.</li>
<li>Complete Steps 4 and 5 and test every slicer.</li>
<li>Finish with print setup and a PDF export.</li>
<li>Ask a friend to read the dashboard for one minute, then tell you what the main message was.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Work in order: prepare, calculate, analyse, visualise, assemble, protect, share.</li>
<li>Reconcile every summary with the source total before trusting it.</li>
<li>Keep an original copy, and document the steps you took.</li>
<li>Finish with a written message; a dashboard supports a decision.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What should you do first when planning a dashboard?
- Choose colours and fonts
- Insert as many charts as possible
* Identify the reader and the decisions the dashboard must support
- Protect the sheet
= Planning starts with who will use it and what they need to decide; the design follows from that.

Q: Which is the best set of KPI cards?
- Twelve numbers with no comparisons
* Three to six measures tied to goals, each with a comparison such as last period or target
- Only decorative icons
- The raw data table
= A few meaningful KPIs with context are easier to act on than a crowd of numbers.

Q: You want one slicer to filter three PivotTables. What do you use?
- Copy the slicer three times
- Protect Sheet
* Report Connections on the slicer
- Group
= Report Connections lets you tick every PivotTable the slicer should control, provided they share the same source.

Q: Why should KPI cards for a dashboard with slicers read from a PivotTable?
- Formulas on the raw table cannot be formatted
* Plain formulas on the raw table do not react to slicers, while PivotTable values do
- PivotTables calculate faster
- It is required by Excel
= Slicers filter PivotTables, not ordinary formulas on the raw Table, so cards that must change need to read from a PivotTable.

Q: What is true about Protect Sheet?
- It encrypts the file securely
* It prevents accidental changes but is not strong security
- It prevents sharing
- It deletes hidden data
= Sheet protection helps stop accidents. Do not rely on it to protect confidential data.

Q: What step helps catch errors in the worked project?
- Hiding the Analysis sheet
- Increasing the font size
* Checking that each PivotTable grand total matches the sum of the source Table
- Removing all slicers
= Reconciling the summaries with the source confirms that no data was lost or double-counted.

Q: Which export is best for a fixed, printable snapshot of the dashboard?
- A copy of the workbook sent by email
* A PDF created with File, Export
- A screenshot of the Name Box
- A CSV file
= A PDF shows the dashboard as it was at that time and prints reliably, but it has no working slicers.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which set of rules describes tidy data?
- Merged titles, one record per column, subtotals between groups
* One header row, one record per row, one kind of information per column
- Colours showing meaning, blank cells for repeated values
- Dates stored as text so they cannot be changed
= Tidy data is plain and consistent so that PivotTables, formulas and charts can use it.

Q: A Table is named Sales. What is the benefit of =SUM(Sales[Amount]) compared with =SUM(F2:F200)?
- It ignores new data
- It only works for text
* It includes new rows automatically and is easier to read
- It runs faster because it skips blanks
= Structured references follow the size of the Table and describe themselves.

Q: Which formula correctly cleans A2 of hidden characters, extra spaces and inconsistent capitals?
- =UPPER(A2)
- =CLEAN(A2)&TRIM(A2)
* =PROPER(TRIM(CLEAN(A2)))
- =SUBSTITUTE(A2," ","")
= The three functions work together: remove non-printing characters, remove extra spaces, set capital letters.

Q: Which test tells you whether A2 holds a real number or date?
- =TEXT(A2)
* =ISNUMBER(A2)
- =LEN(A2)
- =ISBLANK(A2)
= ISNUMBER returns TRUE for real numbers and dates and FALSE for text, whatever the cell looks like.

Q: You change the format of a text column to Number but values still behave as text. What should you do?
- Nothing; Excel will convert them on save
- Sort the column
* Convert them with Convert to Number, Text to Columns or VALUE
- Hide the column
= A format change affects new entries only; existing text must be converted.

Q: The date 03/04/2025 appears in a file from another country. What is the correct precaution?
- Assume it means 3 April
- Delete the column
* Check a date with a day above 12 to confirm the day-month order before converting
- Convert to text
= Day-month order differs between regions, so test with a date such as 25/03/2025 to learn how it was written.

Q: What does Remove Duplicates do when you tick only the Receipt No column?
- Highlights duplicates without deleting
- Deletes every row with a receipt number
* Deletes rows that repeat the same Receipt No, keeping the first
- Sorts the data
= Remove Duplicates compares only the ticked columns and permanently deletes later repeats, so check first.

Q: What does data validation with a List setting do?
- Lists every value already in the column
* Restricts typed entries to the items in the list and shows a drop-down
- Sorts the column
- Converts text to numbers
= Validation controls what can be entered. It does not fix values that were already there or stop pasting.

Q: Which formula totals Amount for Gulu and for Soap only?
- =SUMIFS(Sales[Amount],Sales[Town],"Gulu")+Sales[Item]
- =SUMIF(Sales[Town],"Gulu",Sales[Item],"Soap")
- =COUNTIFS(Sales[Town],"Gulu",Sales[Item],"Soap")
* =SUMIFS(Sales[Amount],Sales[Town],"Gulu",Sales[Item],"Soap")
= SUMIFS takes the range to add first, followed by criteria ranges and criteria; every pair must be true.

Q: Which criterion selects dates on or after the date in H1?
- ">=H1"
* ">="&H1
- >=&"H1"
- "H1>="
= Join the operator text and the cell reference with an ampersand.

Q: Which functions can test several conditions without nesting? (choose all that apply)
* IFS
* SUMIFS
- LEN
- TRIM
= IFS tests several conditions in order and SUMIFS adds when several conditions are true; LEN and TRIM are text functions.

Q: What is the danger of wrapping a whole complicated formula in IFERROR?
- It slows Excel to a stop
* It hides real mistakes in the formula as well as expected errors
- It only works for numbers
- It changes the cell format
= Use IFERROR on the part that may fail for a known reason, or use IFNA for lookups.

Q: Which lookup works in all versions of Excel?
- XLOOKUP
- FILTER
* INDEX with MATCH
- TEXTSPLIT
= XLOOKUP and the dynamic array functions need Microsoft 365 or Excel 2021; INDEX and MATCH always work.

Q: For grade boundaries 0, 50, 60, 70, 80, how should the lookup table be arranged for an approximate match?
- Sorted from largest to smallest
- In any order
* Sorted from smallest to largest
- With text instead of numbers
= Approximate match relies on an ascending order of the lower limits.

Q: What does a PivotTable do to the source data?
- Changes it to match the report
* Nothing; it only summarises it
- Removes duplicates
- Sorts it permanently
= A PivotTable is a separate report built from the data. The source is untouched.

Q: In a PivotTable, which area holds the numbers that are summed or counted?
- Rows
- Filters
- Columns
* Values
= Values is the calculation area; Rows and Columns label it; Filters narrows it.

Q: Why does grouping dates require real dates with no blanks or text?
- Excel only groups blue cells
* The Group command needs every value in the field to be a valid date
- Grouping deletes text
- Groups only work in Excel Online
= If any entry is blank or text, Excel cannot group the field, so clean the data first.

Q: What happens when a PivotTable source is a plain range and you add rows below it?
- The PivotTable includes them automatically
* They may be missing after refresh unless the source is a Table or you change the data source
- Excel shows a warning and deletes the rows
- The slicers stop working
= A Table grows with new rows, a plain range does not.

Q: Which shortcut refreshes the selected PivotTable?
- Ctrl + R
* Alt + F5
- F2
- Ctrl + T
= Alt + F5 refreshes the PivotTable you are in; Ctrl + Alt + F5 refreshes all.

Q: Which chart best compares sales of eight products?
- Eight-slice pie chart
- 3-D area chart
* Sorted bar chart
- Doughnut chart
= A sorted bar chart makes comparing many categories simple and fair.

Q: A column chart axis begins at 95 and the bars look very different, but the values are 96 and 98. What is the problem?
- The chart type is wrong for text
* The truncated axis exaggerates a small difference
- The values are too big
- Nothing; the chart is accurate
= Column length should reflect the value, so the axis should start at zero.

Q: What are sparklines best used for? (choose all that apply)
* Showing a trend in one cell
* Giving one tiny chart per row of a table
- Replacing all full-size charts
- Making a PivotTable refresh
= Sparklines give a quick shape of data in a small space and cannot replace full charts with axes and labels.

Q: When might you choose two separate charts instead of a combo chart with a secondary axis?
- When both use the same unit
- When there is only one month
- Never; combo charts are always clearer
* When the measures are unrelated and the crossing of lines could mislead
= A secondary axis can create false patterns, so two clear charts are sometimes more honest.

Q: Which is the best first step in planning a dashboard?
- Choose a colour theme
- Open the Insert tab
* Identify the reader and the questions the dashboard must answer
- Add a macro
= The design should serve a clear purpose for a specific reader.

Q: How do you make one slicer control several PivotTables?
- Create three slicers with the same name
- Paste the slicer as a picture
* Use Report Connections to tick each PivotTable
- Group the PivotTables
= Report Connections link a slicer to every PivotTable that shares the same source.

Q: Which statement about sheet protection is correct?
- It makes a workbook safe from anyone with access
* It prevents accidental edits, but it is not strong security
- It is required before using PivotTables
- It cannot be combined with slicers
= Protection stops mistakes. Slicers still work if allowed in the protection settings and unlocked in their properties.

Q: Which export gives a fixed snapshot suitable for printing and archive?
- Save as .xlsx
* PDF via File, Export
- Share link with edit rights
- Save as .csv
= A PDF fixes the layout. Excel files and links allow changes and keep working controls.

Q: In the worked project, what should you do before trusting your PivotTable summaries?
- Hide the source sheet
* Compare their grand totals with the SUM of the source Table
- Apply a theme
- Print the sheet
= Reconciling totals shows that the summaries are complete and not double counting.
QUIZ,
];
