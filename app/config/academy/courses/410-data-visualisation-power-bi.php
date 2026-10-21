<?php
return [
    'slug'        => 'data-visualisation-power-bi',
    'title'       => 'Data Visualisation and Reporting with Power BI',
    'category'    => 'data',
    'level'       => 'intermediate',
    'icon'        => 'layers',
    'sort'        => 410,
    'summary'     => 'Turn raw tables into clear, interactive reports with Power BI: import and clean data, build a model, write basic DAX measures, design accessible pages and understand publishing and refresh.',
    'description' => <<<'HTML'
<p>Organisations collect more data than ever: sales sheets, attendance registers, survey results, health and school records, project budgets. A table of numbers does not help a manager, a donor or a head teacher make a decision until someone turns it into a clear picture. Power BI is a Microsoft tool built for this job. It connects to your data, cleans and combines it, lets you define your own calculations and presents the results as interactive reports.</p>
<p>This course teaches the full workflow in plain steps: getting data in, cleaning it with Power Query, building a simple data model, writing basic measures in DAX, choosing the right chart, designing report pages with filters and slicers, making reports accessible, and understanding how reports are published, shared and refreshed. The last module walks through one complete mini-project from a small sample table that you can recreate yourself.</p>
<p>The course assumes you can already work with data in Excel, including tables, formulas and PivotTables. Power BI Desktop is free to download and runs on Windows. The online service has features that depend on licensing, and licensing options change over time, so this course explains the ideas and does not quote prices.</p>
HTML,
    'outcomes'    => [
        'Describe the parts of Power BI and what each one is used for',
        'Connect to Excel and CSV data and load it into Power BI Desktop',
        'Clean and reshape data with Power Query and keep every step repeatable',
        'Build a star-schema data model with relationships between tables',
        'Write basic DAX measures using SUM, SUMX, CALCULATE, DIVIDE and simple time intelligence',
        'Choose a chart that matches the question and build clear report pages with slicers and filters',
        'Apply design and accessibility practices so that more people can read and use a report',
        'Explain how reports are published, shared and refreshed, and complete a small end-to-end report',
    ],
    'requirements'=> [
        'Completion of Excel for Data Analysis: PivotTables, Charts and Dashboards (excel-data-analysis), or equal experience with Excel tables, formulas and PivotTables',
        'A Windows computer to install Power BI Desktop (free), or access to a Windows machine at a school, cyber cafe or learning centre',
        'Microsoft Excel or a spreadsheet tool to prepare the practice data',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Microsoft Learn: Power BI documentation', 'url' => 'https://learn.microsoft.com/power-bi/'],
        ['title' => 'Microsoft Power BI: product home and Desktop download', 'url' => 'https://powerbi.microsoft.com'],
        ['title' => 'Microsoft Learn: DAX function reference', 'url' => 'https://learn.microsoft.com/dax/'],
        ['title' => 'DAX Guide: searchable list of DAX functions', 'url' => 'https://dax.guide'],
    ],
    'modules' => [
        // ------------------------------------------------------------------ MODULE 1
        [
            'title'   => 'Module 1: Meet Power BI',
            'summary' => 'Understand what Power BI is, find your way around Power BI Desktop and bring data into it.',
            'lessons' => [
                [
                    'title'   => 'What Power BI is and how the pieces fit',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what Power BI does, name its three main parts and describe the workflow from raw data to a shared report. This matters because knowing the big picture stops you from using the wrong tool for a job, for example trying to share a report from a program that cannot do it.</p>
<h3>What is business intelligence?</h3>
<p><strong>Business intelligence (BI)</strong> means turning data into information that helps people decide. A shop owner who sees that one branch sells less every Friday can act. A school that sees pass rates by class can target extra lessons. Power BI is a BI tool: it collects data, prepares it, calculates and displays it.</p>
<h3>The three main parts</h3>
<table>
<thead><tr><th>Part</th><th>What it is</th><th>Typical use</th></tr></thead>
<tbody>
<tr><td>Power BI Desktop</td><td>A free Windows program you install on your computer</td><td>Build reports: connect to data, clean it, model it, design pages</td></tr>
<tr><td>Power BI service</td><td>The online part, used in a web browser</td><td>Publish, share, view and refresh reports; build dashboards</td></tr>
<tr><td>Power BI mobile app</td><td>An app for phones and tablets</td><td>View reports and dashboards on the move</td></tr>
</tbody>
</table>
<p>A common mistake is to think Power BI is only the website. In practice, most report builders create the report in <strong>Desktop</strong> and then publish it to the <strong>service</strong>. People who only read reports may never open Desktop. Features of the online service and of sharing depend on the type of licence that you or your organisation has, and these change over time, so always check Microsoft's current documentation for your situation.</p>
<h3>Working on a Mac or a phone</h3>
<p>Power BI Desktop is made for Windows. If you use a Mac, you can work in the online service in a browser, which has some report editing, or use a Windows computer, a shared lab or a virtual machine. Some training centres provide Windows machines for this reason.</p>
<h3>The workflow</h3>
<ol>
<li><strong>Get data</strong> from files, databases or online sources.</li>
<li><strong>Clean and shape</strong> it with Power Query.</li>
<li><strong>Model</strong> it by connecting tables with relationships.</li>
<li><strong>Calculate</strong> with measures written in DAX (Data Analysis Expressions).</li>
<li><strong>Visualise</strong> with charts, tables and slicers on report pages.</li>
<li><strong>Publish and share</strong> to the service, then keep the data fresh.</li>
</ol>
<p>You will meet each step in this course. Most of the time in real projects is spent on steps 2 and 3, not on drawing charts.</p>
<h3>Key words</h3>
<ul>
<li><strong>Report:</strong> one or more pages of visuals built on a data model. Created in Desktop or the service.</li>
<li><strong>Dashboard:</strong> a single page in the service that pins important visuals from one or more reports so you can monitor key numbers at a glance.</li>
<li><strong>Semantic model (also called dataset):</strong> the data and the model behind the report that you publish. Microsoft has renamed this over time, so you will see both words.</li>
<li><strong>Visual:</strong> a chart, table, card or map on a page.</li>
<li><strong>Measure:</strong> a calculation, written in DAX, that is worked out when the report is used.</li>
</ul>
<h3>Power BI and Excel</h3>
<p>Power BI is not a replacement for Excel. Excel is excellent for entering and calculating data cell by cell. Power BI is better when you have larger data, several related tables, a need for repeatable cleaning steps and a report that many people view. Many analysts use both: prepare the data in Excel, load it into Power BI, then present.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write a one-sentence question that a report could answer for your school, club or business, such as "Which branch sold the most this quarter?".</li>
<li>List the data you would need to answer it.</li>
<li>Decide which of the six workflow steps you think will take the most time and why.</li>
<li>Visit the Power BI product page listed in the course resources and find the Desktop download page, without installing yet.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Power BI turns data into interactive reports that help people decide.</li>
<li>Desktop builds reports, the service shares them and the mobile app views them.</li>
<li>The workflow is get data, clean, model, calculate, visualise and publish.</li>
<li>Licensing and exact features vary and change, so check current official documentation.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Power BI Desktop: the window and your first visual',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to find the main areas of Power BI Desktop and build your first chart from an Excel table. This matters because a first quick win builds confidence, and knowing where things are saves much time later.</p>
<h3>Getting Power BI Desktop</h3>
<p>Download Power BI Desktop from the official Microsoft site or the Microsoft Store on Windows. It is free to install. Your computer must meet the current system requirements listed by Microsoft, and the download is fairly large, so use a good connection. On a shared computer, ask the lab administrator before installing.</p>
<h3>The three views</h3>
<p>On the left edge you will see icons that switch between views:</p>
<table>
<thead><tr><th>View</th><th>Purpose</th></tr></thead>
<tbody>
<tr><td>Report view</td><td>Design pages with visuals. This is the main canvas.</td></tr>
<tr><td>Table view</td><td>See the rows and columns of the loaded data, one table at a time.</td></tr>
<tr><td>Model view</td><td>See tables as boxes and the relationships between them as lines.</td></tr>
</tbody>
</table>
<p>Newer versions may add further views, such as one for writing DAX queries. The three above are the ones you will use most.</p>
<h3>The main areas in Report view</h3>
<ul>
<li><strong>Ribbon</strong> at the top, with tabs such as Home, Insert and Modeling. The Home tab has Get data, Transform data, Publish and Refresh.</li>
<li><strong>Canvas</strong> in the middle, where you place visuals on the page.</li>
<li><strong>Pages</strong> at the bottom, one tab for each page of the report.</li>
<li><strong>Filters pane</strong> for filters that apply to a visual, a page or the whole report.</li>
<li><strong>Visualizations pane</strong> with the chart types, the field wells and the Format options.</li>
<li><strong>Data pane</strong> on the right, listing tables and their fields.</li>
</ul>
<h3>Build your first chart</h3>
<p>Create a small Excel table first. Put these columns in a sheet and format it as a table (Insert, then Table): <code>Branch</code>, <code>Product</code>, <code>Revenue</code>. Add about eight rows with at least three branches. Save the file and close it.</p>
<ol>
<li>In Power BI Desktop select <strong>Home</strong>, then <strong>Get data</strong>, then <strong>Excel workbook</strong>.</li>
<li>Choose your file. In the Navigator window tick your table, then select <strong>Load</strong>.</li>
<li>In the <strong>Data</strong> pane tick <code>Branch</code> and <code>Revenue</code>. Power BI draws a chart or table automatically.</li>
<li>In the Visualizations pane choose <strong>Clustered bar chart</strong> to change the type.</li>
<li>Select the visual and use the <strong>Format your visual</strong> options to add a title, such as "Revenue by branch".</li>
<li>Choose <strong>File</strong>, then <strong>Save</strong>. Power BI Desktop saves a file with the extension <code>.pbix</code>.</li>
</ol>
<h3>Fields and field wells</h3>
<p>Power BI draws visuals by placing fields into <strong>wells</strong>. For a bar chart, a text field such as Branch goes into the <strong>Y axis</strong> (or X axis for column charts) and a number such as Revenue goes into <strong>X axis</strong> (or Y axis). Numbers are summed by default. You can change the summary, for example to Average or Count, with the drop-down arrow beside the field.</p>
<h3>Save often</h3>
<p>Desktop does not always recover well after a power cut. Save every few minutes with <strong>Ctrl+S</strong>. Name versions clearly, for example <code>Sales-report-v1.pbix</code>, and keep a backup copy of the file and the source data.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create the three-column Excel table described above with at least eight rows.</li>
<li>Load it into Power BI Desktop.</li>
<li>Build a bar chart of revenue by branch and a second chart of revenue by product.</li>
<li>Change one chart to a different type and notice what changes.</li>
<li>Switch to Table view and Model view and describe what you see.</li>
<li>Save the file with a clear name.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Report, Table and Model views serve designing, inspecting and relating data.</li>
<li>Visuals are built by dropping fields into wells.</li>
<li>Numeric fields are summarised, usually summed, by default.</li>
<li>Save frequently and keep backup copies.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Getting data: Excel, CSV, web and folders',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to connect Power BI to common data sources and choose a sensible loading approach. This matters because every report depends on the quality and the reliability of its source, and a report that cannot be refreshed becomes out of date quickly.</p>
<h3>Common sources</h3>
<table>
<thead><tr><th>Source</th><th>When to use it</th></tr></thead>
<tbody>
<tr><td>Excel workbook</td><td>Small business sheets, budgets, registers. Load tables or named ranges, not a messy sheet.</td></tr>
<tr><td>Text or CSV file</td><td>Exports from other systems, survey tools or banks. Simple and reliable.</td></tr>
<tr><td>Folder</td><td>Many files with the same layout, such as one file per month. Power BI can combine them.</td></tr>
<tr><td>Web page or online file</td><td>Public tables of statistics from an official website, if the site allows it</td></tr>
<tr><td>Database</td><td>Data held in systems such as SQL Server or MySQL, with permission and credentials</td></tr>
<tr><td>Online services</td><td>Cloud files, SharePoint lists and other connectors supported by Power BI</td></tr>
</tbody>
</table>
<p>The list of available connectors changes between versions. Open <strong>Home</strong>, <strong>Get data</strong>, <strong>More</strong> to see the current list.</p>
<h3>Prepare the source first</h3>
<ol>
<li><strong>Use one header row</strong> with clear unique column names.</li>
<li><strong>Keep one record per row</strong> and one kind of fact per column.</li>
<li><strong>Do not mix totals and detail.</strong> Remove "Total" rows from the data and let Power BI calculate totals.</li>
<li><strong>Format Excel data as a table</strong> (Ctrl+T) so that new rows are picked up when you refresh.</li>
<li><strong>Do not merge cells</strong> or leave blank rows and decoration inside the data.</li>
</ol>
<h3>Connecting to a CSV file</h3>
<ol>
<li>Select <strong>Home</strong>, <strong>Get data</strong>, <strong>Text/CSV</strong>.</li>
<li>Choose the file. A preview window shows how Power BI reads it. Check the file origin (encoding) and the delimiter. If the characters look wrong, change the file origin.</li>
<li>Select <strong>Transform Data</strong> to clean it in Power Query, or <strong>Load</strong> if it is already clean.</li>
</ol>
<h3>Import and DirectQuery</h3>
<p>There are two main ways to hold data. <strong>Import</strong> copies the data into the report file. It is fast and works offline until you refresh. <strong>DirectQuery</strong> leaves the data in the source and asks it questions every time someone uses the report. It suits very large or constantly changing data, but needs a good connection to a supported database, and some features are limited. For beginners and most small projects, Import is the right choice.</p>
<h3>Data sources and privacy</h3>
<p>Power BI remembers the credentials and the <strong>privacy levels</strong> of each source (private, organizational, public). These stop data being mixed in ways you did not intend. Open <strong>File</strong>, <strong>Options and settings</strong>, <strong>Data source settings</strong> to review them. Use public data only from sources that allow reuse, and never publish personal data, such as names with health records, without permission and a lawful reason. Remove columns you do not need.</p>
<h3>Where the file lives matters</h3>
<p>If your Excel file is on your own laptop at <code>C:\Data\sales.xlsx</code>, a report published online cannot reach it unless a gateway or a cloud location is set up. For a simple setup, keep the source in a cloud location that the service supports, and use the same location for the whole team. Plan for this at the start.</p>
<h3>Try it yourself</h3>
<ol>
<li>Export or create a small CSV file, for example an attendance list with Date, Class and Present.</li>
<li>Use <strong>Get data</strong>, <strong>Text/CSV</strong> to connect to it and read the preview.</li>
<li>Open <strong>Data source settings</strong> and find the entry for your file.</li>
<li>Write which of the source preparation rules your file already follows and which you must fix.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Power BI connects to files, folders, web sources and databases.</li>
<li>Clean source layouts (one header, one record per row, no totals) save hours later.</li>
<li>Import copies data into the report, while DirectQuery queries the source live.</li>
<li>Check privacy levels, permissions and where the source file is stored.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which part of Power BI is a free Windows program used to build reports?
- Power BI mobile app
* Power BI Desktop
- Power BI service
- Excel Online
= Desktop is where you connect, clean, model and design. The service shares and refreshes reports.

Q: What is the correct order of the workflow?
- Publish, visualise, clean, model
* Get data, clean, model, calculate, visualise, publish
- Visualise, get data, publish, model
- Calculate, publish, clean, get data
= You prepare and model the data before you build visuals for sharing.

Q: What file extension does a Power BI Desktop report use?
- .xlsx
- .docx
* .pbix
- .csv
= A report file saved from Desktop has the extension .pbix.

Q: Which view shows tables as boxes with lines between them?
- Report view
- Table view
* Model view
- Filters view
= Model view shows the relationships between tables.

Q: Which source layout is best for loading?
- Merged cells with decorative titles
* One header row, one record per row and no total rows
- Several tables on the same sheet with blank rows between them
- Colour-coded rows with no headings
= Clean tabular data makes cleaning and calculating easier.

Q: Which description fits Import mode?
* A copy of the data is stored in the report and refreshed when you ask
- The report asks the source a question every time it is used
- The report has no data
- The data is deleted when the report opens
= Import stores the data in the file. DirectQuery leaves it in the source.

Q: Why should you review privacy levels and permissions when connecting data? (choose all that apply)
* To avoid mixing private data with public data by accident
* To make sure you are allowed to use and share the data
- To make the charts more colourful
- To increase file size
= Data protection and permission are part of responsible reporting.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 2
        [
            'title'   => 'Module 2: Cleaning and shaping data with Power Query',
            'summary' => 'Prepare messy data with repeatable steps so that every refresh gives the same clean result.',
            'lessons' => [
                [
                    'title'   => 'The Power Query Editor and cleaning basics',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to open the Power Query Editor and perform the most common cleaning steps: promote headers, set data types, remove errors, trim text and remove duplicates. This matters because dirty data produces wrong totals, and Power Query records your steps so you never need to repeat the cleaning by hand.</p>
<h3>What is Power Query?</h3>
<p><strong>Power Query</strong> is the data preparation tool inside Power BI (and Excel, where it is called Get and Transform). Every action you take is saved as a <strong>step</strong> in a list called <strong>Applied Steps</strong>. When the data is refreshed, Power Query replays the steps on the new data automatically. This is the biggest difference from cleaning data manually in a spreadsheet, where every new file means doing it all again.</p>
<h3>Open the editor</h3>
<p>From Report view select <strong>Home</strong>, then <strong>Transform data</strong>. A new window opens with a list of queries (one per table) on the left, a preview in the middle, the <strong>Applied Steps</strong> and properties on the right and the ribbon on top. When you finish, select <strong>Close and Apply</strong> to load the cleaned data into the model.</p>
<h3>The most useful cleaning steps</h3>
<table>
<thead><tr><th>Problem</th><th>Where to fix it</th></tr></thead>
<tbody>
<tr><td>First row holds the names</td><td>Home, then Use First Row as Headers</td></tr>
<tr><td>Numbers read as text, or dates as text</td><td>Click the data type icon in the column header, or Transform, Data Type</td></tr>
<tr><td>Extra spaces around text, like " Kampala"</td><td>Transform, Format, Trim</td></tr>
<tr><td>Different capitals, like "KAMPALA" and "kampala"</td><td>Transform, Format, Capitalize Each Word (or lowercase)</td></tr>
<tr><td>Empty or junk rows at the top or bottom</td><td>Home, Remove Rows, Remove Top Rows or Remove Blank Rows</td></tr>
<tr><td>Repeated records</td><td>Select the key column or columns, Home, Remove Rows, Remove Duplicates</td></tr>
<tr><td>Wrong spelling or code</td><td>Transform, Replace Values</td></tr>
<tr><td>Rows with errors</td><td>Home, Remove Rows, Remove Errors, or replace errors</td></tr>
<tr><td>Columns you do not need</td><td>Select them, right-click, Remove Columns</td></tr>
</tbody>
</table>
<h3>Data types matter</h3>
<p>Every column has a type: whole number, decimal number, date, text, true or false and so on. If revenue is stored as text, Power BI cannot sum it. If dates are text, time calculations will not work. Always set the right type, and for dates check whether the day and month are read in the order that matches the source, as regional settings can swap them.</p>
<h3>Null and blank values</h3>
<p>A <strong>null</strong> in Power Query means there is no value. Decide what a null should mean. A missing quantity might be zero, or it might mean unknown. Use <strong>Replace Values</strong> to change null to 0 only when that is true. Do not hide missing data silently, because it can mislead readers.</p>
<h3>Name steps and queries clearly</h3>
<p>Right-click a step in <strong>Applied Steps</strong> and choose <strong>Rename</strong> to give it a meaning, for example "Trim branch names". Rename your queries to clear names such as <code>Sales</code> and <code>Products</code>. This helps you and others later.</p>
<h3>Undo is the Applied Steps list</h3>
<p>Click on an earlier step to see the data at that point. Select the small cross beside a step to delete it. Steps depend on each other, so deleting an early one can break later ones. Add a new step instead of editing the source data where possible.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create an Excel table with deliberate mess: extra spaces, "kampala" and "Kampala", a duplicate row, a text entry "n/a" in a number column.</li>
<li>Load it with <strong>Transform data</strong> instead of Load.</li>
<li>Apply Trim, Capitalize Each Word, Remove Duplicates and set the types.</li>
<li>Replace "n/a" with null, then decide what to do with it and write your reason.</li>
<li>Rename three steps, select <strong>Close and Apply</strong> and check the totals.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Power Query saves every action as a step and replays it on each refresh.</li>
<li>Set correct data types, trim text and remove duplicates and junk rows.</li>
<li>Decide on purpose what a null or blank means.</li>
<li>Rename steps and queries so that the logic is clear to others.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Reshaping: split, unpivot, group and fill down',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to reshape awkward tables into the tidy layout that Power BI works best with. This matters because many real spreadsheets are designed to be read by people, with months spread across columns, not designed for analysis.</p>
<h3>What a tidy table looks like</h3>
<p>A tidy table has <strong>one row per observation</strong>, <strong>one column per variable</strong> and <strong>one value per cell</strong>. Compare these two layouts for the same data:</p>
<pre><code>Wide layout (hard to analyse)
Branch    Jan    Feb    Mar
Kampala   520    610    580
Jinja     300    280    340

Tidy layout (easy to analyse)
Branch    Month    Sales
Kampala   Jan      520
Kampala   Feb      610
Kampala   Mar      580
Jinja     Jan      300
...</code></pre>
<p>With the wide layout, adding April means adding a column and changing every chart. With the tidy layout you just add rows.</p>
<h3>Unpivot columns</h3>
<p><strong>Unpivot</strong> turns columns into rows, which converts a wide layout into a tidy layout.</p>
<ol>
<li>In the Power Query Editor, click the <code>Branch</code> column.</li>
<li>Select the <strong>Transform</strong> tab, then <strong>Unpivot Columns</strong> arrow, then <strong>Unpivot Other Columns</strong>. This keeps Branch and turns all the others into rows, and it keeps working when new month columns appear.</li>
<li>Rename the new columns <code>Attribute</code> and <code>Value</code> to <code>Month</code> and <code>Sales</code>.</li>
</ol>
<h3>Split columns</h3>
<p>Sometimes one column holds two facts, such as "Kampala - Central" or "Nakato Grace". Use <strong>Transform</strong>, then <strong>Split Column</strong>, then <strong>By Delimiter</strong> (choose the character such as a dash or a space) or <strong>By Number of Characters</strong>. Trim the results afterwards because spaces often remain. You can also combine columns with <strong>Merge Columns</strong>, for example first and last name.</p>
<h3>Fill down</h3>
<p>Some sheets show a heading only once and leave blanks beneath it:</p>
<pre><code>Region    Branch
Central   Kampala
          Entebbe
Eastern   Jinja
          Mbale</code></pre>
<p>Select the Region column, then use <strong>Transform</strong>, <strong>Fill</strong>, <strong>Down</strong>. The blanks must be real nulls for this to work. If they are empty text, first use Replace Values to turn them into null.</p>
<h3>Group by</h3>
<p><strong>Group By</strong> summarises rows before they reach the model, such as totals per branch. Select <strong>Home</strong>, then <strong>Group By</strong>, choose the column to group on and the operation, such as Sum or Count Rows. Use it when you want a smaller table. In most reports it is better to keep the detail and let measures do the summing, because the detail lets people filter and drill.</p>
<h3>Add a column from existing ones</h3>
<ul>
<li><strong>Add Column</strong>, then <strong>Custom Column</strong>, lets you write a formula in the Power Query language (called M), such as <code>[Units] * [UnitPrice]</code>.</li>
<li><strong>Add Column</strong>, then <strong>Conditional Column</strong>, builds an if-then rule without code, for example "Large order" when units are at least 10.</li>
<li><strong>Add Column</strong>, then <strong>Column From Examples</strong>, lets you type a few results and have Power Query guess the rule.</li>
</ul>
<h3>Pivot, transpose and others</h3>
<p><strong>Pivot Column</strong> does the opposite of unpivot. <strong>Transpose</strong> swaps rows and columns. They are less common, but good to know about.</p>
<h3>Try it yourself</h3>
<ol>
<li>Build the wide table above in Excel with at least three branches and four months.</li>
<li>Load it with <strong>Transform data</strong>.</li>
<li>Use <strong>Unpivot Other Columns</strong> and rename the columns to Month and Sales.</li>
<li>Add a conditional column that labels each row "High" for sales of 500 or more and "Normal" otherwise.</li>
<li>Close and apply, then build a column chart of sales by month.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Tidy tables have one row per observation and one column per variable.</li>
<li>Unpivot Other Columns converts months in columns into rows and survives new columns.</li>
<li>Split, merge, fill down and group by fix common layout problems.</li>
<li>Keep detail rows and let measures do the summing where possible.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Combining data: append, merge and folders',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to stack tables with the same columns (append) and add columns from a related table (merge). This matters because real projects usually have data in several files and several tables, and combining them correctly avoids double counting and missing rows.</p>
<h3>Append: stack tables on top of each other</h3>
<p>Use <strong>Append Queries</strong> when you have the same kind of table in several places, for example sales for January, February and March in three sheets with identical columns.</p>
<ol>
<li>Load each table as its own query in the Power Query Editor.</li>
<li>Select the first query. Choose <strong>Home</strong>, then <strong>Append Queries</strong>, then <strong>Append Queries as New</strong>.</li>
<li>Choose <strong>Three or more tables</strong> if needed and add each query.</li>
<li>Name the new query <code>Sales</code>.</li>
<li>Make sure column names match exactly (including spelling and capitals). If not, you will see extra columns with nulls. Fix the names first.</li>
</ol>
<h3>Folder: append many files automatically</h3>
<p>If a new file arrives every month with the same layout, use <strong>Get data</strong>, then <strong>Folder</strong>. Power BI lists every file in the folder. Choose <strong>Combine</strong> and then <strong>Combine and Transform Data</strong>. It builds a query that stacks all files and, as you add new files to the folder, a refresh picks them up. Make sure the files really have the same layout and do not include other files in the folder.</p>
<h3>Merge: look up information from another table</h3>
<p><strong>Merge Queries</strong> works like a lookup. It matches rows in two tables by a key column and brings in columns from the second table. Imagine a Sales table with ProductID and a Products table with ProductID, Product and Category.</p>
<ol>
<li>Select the Sales query. Choose <strong>Home</strong>, then <strong>Merge Queries</strong>.</li>
<li>Choose Products as the second table.</li>
<li>Click <code>ProductID</code> in each table to select the match columns.</li>
<li>Choose the <strong>join kind</strong>: usually <strong>Left Outer</strong>, which keeps every sales row and adds product details where there is a match.</li>
<li>Select OK. A new column appears with the word Table in each cell.</li>
<li>Click the expand icon in the header and tick only the columns you want, such as Category. Untick "Use original column name as prefix" if you prefer.</li>
</ol>
<h3>Join kinds in plain words</h3>
<table>
<thead><tr><th>Join kind</th><th>Result</th></tr></thead>
<tbody>
<tr><td>Left outer</td><td>All rows from the first table, plus matching rows from the second</td></tr>
<tr><td>Right outer</td><td>All rows from the second, plus matching from the first</td></tr>
<tr><td>Inner</td><td>Only rows that match in both tables</td></tr>
<tr><td>Full outer</td><td>All rows from both tables</td></tr>
<tr><td>Left anti</td><td>Rows in the first table that have no match in the second. Useful for finding errors.</td></tr>
</tbody>
</table>
<h3>Merge or relationship?</h3>
<p>Merging copies columns into one big table. A <strong>relationship</strong> in the data model, which the next module teaches, connects tables without copying. In Power BI the preferred approach is usually to keep tables separate and relate them, and use a merge when you need a one-time lookup or the key needs preparation. Using relationships keeps the model smaller and clearer.</p>
<h3>Check the results</h3>
<ul>
<li>Compare the row count before and after. A merge using a key that is not unique in the second table can multiply rows and double your totals.</li>
<li>Use Left anti to find IDs that have no match, such as a product code that is missing from the product list.</li>
<li>Remove duplicate keys in the lookup table first.</li>
</ul>
<h3>Disable load for helper queries</h3>
<p>If a query is only a step on the way to another one, right-click it and untick <strong>Enable load</strong>. This keeps the model free of clutter.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create two small tables in Excel: Sales (OrderID, ProductID, Units) and Products (ProductID, Product, Category).</li>
<li>Load both into Power Query and perform a left outer merge on ProductID.</li>
<li>Expand the Product and Category columns.</li>
<li>Check that the number of rows has not changed.</li>
<li>Add a product code to Sales that does not exist in Products and use Left anti to find it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Append stacks tables that have the same columns, and a folder query automates it for new files.</li>
<li>Merge looks up columns from another table using a key and a join kind.</li>
<li>Always compare row counts after merging to catch duplicated rows.</li>
<li>Prefer relationships in the model for ongoing analysis and use merges for preparation.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the list on the right of the Power Query Editor called?
- Field wells
* Applied Steps
- Report pages
- Slicer list
= Each action is recorded as a step and replayed at every refresh.

Q: Revenue values are stored as text. What is the effect?
- None
* They cannot be summed until the type is changed to a number
- They are automatically summed anyway
- The report is deleted
= Correct data types are necessary for calculations.

Q: Which step turns months spread across columns into rows?
- Merge Columns
- Group By
* Unpivot Other Columns
- Remove Duplicates
= Unpivoting produces a tidy table with one row per branch and month.

Q: What does Trim do?
- Deletes the whole column
* Removes extra spaces at the start and end of text
- Rounds a number
- Sorts a table
= Hidden spaces make "Kampala" and " Kampala" look different.

Q: You have three monthly tables with identical columns. Which operation stacks them into one?
* Append Queries
- Merge Queries
- Group By
- Split Column
= Append stacks rows. Merge adds columns from another table.

Q: A left outer merge of Sales with Products keeps:
- Only matching rows
- Only non-matching rows
* All rows from Sales and the matching Products details
- Only the Products table
= A left outer join keeps every row from the first table.

Q: After a merge your row count grows from 100 to 140. What is the most likely cause?
- Power BI added rows by magic
* The key in the lookup table is not unique, so some rows matched more than once
- The columns were renamed
- The data types are wrong
= Duplicate keys in the lookup table multiply rows and inflate totals.

Q: Why is a Folder query useful?
* It combines every file in a folder and picks up new files on refresh
- It deletes old files
- It encrypts files
- It converts files into photos
= A folder query is good for monthly files with the same layout.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 3
        [
            'title'   => 'Module 3: The data model and DAX measures',
            'summary' => 'Connect tables with relationships and write your first calculations with DAX.',
            'lessons' => [
                [
                    'title'   => 'Data model: star schema and relationships',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to design a simple star-schema model and create relationships between tables. This matters because a good model makes charts and calculations correct and fast, while a bad model gives wrong totals that look believable.</p>
<h3>Facts and dimensions</h3>
<p>Power BI models work best when tables play one of two roles:</p>
<ul>
<li><strong>Fact table:</strong> records events, one row per sale, payment, attendance or visit. It has numbers to add up (units, amount) and keys that point to other tables. It is usually long and grows over time.</li>
<li><strong>Dimension table:</strong> describes things you group or filter by: products, branches, customers, dates. It has one row per item and descriptive columns (name, category, region).</li>
</ul>
<p>When a fact table sits in the middle and dimension tables surround it, the diagram looks like a star. That layout is called a <strong>star schema</strong>.</p>
<pre><code>             Products           Branches
                  \             /
                   \           /
                      Sales
                   /           \
                  /             \
             Date               Customers</code></pre>
<h3>What a relationship does</h3>
<p>A <strong>relationship</strong> links a column in one table to a column in another, such as <code>Products[ProductID]</code> and <code>Sales[ProductID]</code>. When you click on a product category in a chart, the relationship passes that choice from Products to Sales so only the matching sales rows are included. This passing of filters is the heart of Power BI.</p>
<h3>Cardinality and direction</h3>
<table>
<thead><tr><th>Setting</th><th>Meaning</th><th>Normal choice</th></tr></thead>
<tbody>
<tr><td>One-to-many (1 to *)</td><td>One row in the dimension matches many rows in the fact table</td><td>The standard in a star schema</td></tr>
<tr><td>Many-to-many</td><td>Both sides may repeat values</td><td>Avoid as a beginner. It often causes confusion.</td></tr>
<tr><td>Single cross-filter direction</td><td>Filters flow from the dimension to the fact table</td><td>Use this by default</td></tr>
<tr><td>Both directions</td><td>Filters flow both ways</td><td>Only when you understand why you need it</td></tr>
</tbody>
</table>
<p>A key on the "one" side must be <strong>unique</strong>. If Products contains two rows for ProductID P01, a one-to-many relationship cannot be made, and you must clean the dimension table.</p>
<h3>Create a relationship</h3>
<ol>
<li>Load the Sales and Products tables.</li>
<li>Open <strong>Model view</strong>. Power BI may have already created relationships automatically from matching column names.</li>
<li>Drag <code>ProductID</code> from the Products table onto <code>ProductID</code> in the Sales table, or choose <strong>Home</strong>, then <strong>Manage relationships</strong>, then <strong>New</strong>.</li>
<li>Check the dialog: the "one" side should be Products with cardinality <strong>One to many</strong> and cross filter direction <strong>Single</strong>.</li>
<li>Select OK. A line appears with 1 on the dimension side and an asterisk on the fact side.</li>
</ol>
<h3>Active and inactive relationships</h3>
<p>Between two tables only one relationship can be <strong>active</strong> at a time. Inactive ones are drawn with dashed lines. A common example is a Sales table with both an OrderDate and a DeliveryDate linked to a Date table. One is active, and measures can use the other when needed. Beginners can begin with one date column.</p>
<h3>Tidy the model</h3>
<ul>
<li>Hide key columns from the report (right-click the column and choose Hide in report view) so people use the dimension fields instead.</li>
<li>Give tables and columns friendly names.</li>
<li>Keep descriptive columns in dimension tables, not repeated in the fact table.</li>
<li>Set the right default summarisation, for example set ProductID to "Don't summarize".</li>
</ul>
<h3>Testing a relationship</h3>
<p>Build a table visual with Products[Category] and the sum of Sales[Units]. If each category shows a different sensible number, the relationship works. If every category shows the same total, the relationship is missing or the keys do not match.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a Sales table (OrderID, ProductID, Units) and a Products table (ProductID, Product, Category) in Excel with at least six sales and three products.</li>
<li>Load both and open Model view.</li>
<li>Create or check the one-to-many relationship from Products to Sales.</li>
<li>Build a table visual of Category and sum of Units to test it.</li>
<li>Deliberately duplicate a ProductID in Products and note the error or warning.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Fact tables hold events and numbers, while dimension tables hold descriptions.</li>
<li>A star schema connects dimensions to one central fact table with one-to-many relationships.</li>
<li>The key on the "one" side must be unique, and direction should normally be single.</li>
<li>Test every relationship with a simple visual before building more.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Measures and calculated columns: your first DAX',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write simple DAX measures and know when to use a measure instead of a calculated column. This matters because measures are how you define business numbers such as total revenue, profit and average order once, and reuse them in every visual.</p>
<h3>What is DAX?</h3>
<p><strong>DAX</strong> (Data Analysis Expressions) is the formula language of Power BI. It looks like Excel formulas but it works on whole columns and tables and responds to the filters in a report. If you can write Excel formulas such as SUM and IF, you already know the style.</p>
<h3>Two kinds of calculation</h3>
<table>
<thead><tr><th>Item</th><th>Calculated column</th><th>Measure</th></tr></thead>
<tbody>
<tr><td>Calculated when</td><td>When data is loaded or refreshed, row by row</td><td>When the report is used, based on the current filters</td></tr>
<tr><td>Stored</td><td>In the model, using memory</td><td>As a formula only</td></tr>
<tr><td>Result</td><td>One value in each row</td><td>One value for each visual cell</td></tr>
<tr><td>Use it for</td><td>Labels and categories you want to filter or group by</td><td>Totals, ratios, averages, anything you add up</td></tr>
</tbody>
</table>
<p>As a rule, if the result is something you will <strong>add up or average</strong>, create a measure. If it is a <strong>label per row</strong> that you want on an axis or in a slicer, create a column.</p>
<h3>Create your first measure</h3>
<ol>
<li>Select the Sales table in the Data pane.</li>
<li>Choose <strong>Home</strong>, then <strong>New measure</strong>. The formula bar opens.</li>
<li>Type the formula below and press Enter.</li>
</ol>
<pre><code>Total Units = SUM(Sales[Units])</code></pre>
<p>The name comes first, then an equals sign, then the formula. Column references use the form <code>Table[Column]</code>. Measures are referenced with just square brackets, like <code>[Total Units]</code>.</p>
<h3>Useful beginner functions</h3>
<table>
<thead><tr><th>Function</th><th>Purpose</th><th>Example</th></tr></thead>
<tbody>
<tr><td>SUM</td><td>Adds a column</td><td><code>SUM(Sales[Units])</code></td></tr>
<tr><td>AVERAGE</td><td>Mean of a column</td><td><code>AVERAGE(Sales[Units])</code></td></tr>
<tr><td>COUNTROWS</td><td>Counts rows of a table</td><td><code>COUNTROWS(Sales)</code></td></tr>
<tr><td>DISTINCTCOUNT</td><td>Counts different values</td><td><code>DISTINCTCOUNT(Sales[OrderID])</code></td></tr>
<tr><td>MIN and MAX</td><td>Smallest or largest value</td><td><code>MAX(Sales[Date])</code></td></tr>
<tr><td>SUMX</td><td>Works row by row, then adds</td><td><code>SUMX(Sales, Sales[Units] * Sales[UnitPrice])</code></td></tr>
</tbody>
</table>
<h3>Why SUMX?</h3>
<p>Revenue is units times price for each row, then added up. <code>SUM(Sales[Units]) * SUM(Sales[UnitPrice])</code> would be wrong because it multiplies two totals. <strong>SUMX</strong> goes through the table row by row, calculates the expression for each row and then adds the results.</p>
<pre><code>Total Revenue = SUMX(Sales, Sales[Units] * Sales[UnitPrice])</code></pre>
<h3>Measures use other measures</h3>
<pre><code>Total Cost = SUMX(Sales, Sales[Units] * RELATED(Products[UnitCost]))
Profit = [Total Revenue] - [Total Cost]</code></pre>
<p><strong>RELATED</strong> reaches across a relationship to fetch a value from the "one" side, here the unit cost of the product. It works inside row-by-row calculations such as SUMX.</p>
<h3>A calculated column example</h3>
<p>Select the Sales table, choose <strong>Table tools</strong> (or <strong>Home</strong>), then <strong>New column</strong> and write:</p>
<pre><code>Order Size = IF(Sales[Units] &gt;= 10, "Large", "Small")</code></pre>
<p>Now Order Size can be used on an axis or in a slicer.</p>
<h3>Organising measures</h3>
<p>Give measures clear names, set the format (currency, thousands separators, decimals) in the <strong>Measure tools</strong> ribbon and consider keeping them together in one table. You can add a comment in a formula by starting a line with two slashes.</p>
<h3>Try it yourself</h3>
<ol>
<li>In your Sales and Products model, create measures Total Units and Average Units per Order with AVERAGE.</li>
<li>Add a Revenue measure with SUMX, using a UnitPrice column in the Sales table.</li>
<li>Place a card visual for each measure on a page.</li>
<li>Add a column chart of Total Revenue by Products[Category].</li>
<li>Format the revenue measure with thousands separators.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A measure is calculated in context when the report is used, a column is calculated per row on refresh.</li>
<li>Use SUMX for calculations that must happen row by row before adding.</li>
<li>RELATED fetches values from a related dimension table.</li>
<li>Measures can use other measures, which keeps formulas short and consistent.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'CALCULATE, filter context and DIVIDE',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use CALCULATE to change the filters of a measure, and DIVIDE to build safe ratios such as profit margin and share of total. This matters because most useful business questions are comparisons: this branch versus all branches, this category versus the total.</p>
<h3>Filter context</h3>
<p>The same measure shows different numbers in different places. In a bar chart split by branch, <code>[Total Revenue]</code> shows one total per bar. In a card with no other filters, it shows the grand total. The set of filters that applies to each number is called the <strong>filter context</strong>. It comes from the rows and columns of the visual, slicers, page and report filters, and relationships.</p>
<h3>CALCULATE: change the context</h3>
<p><strong>CALCULATE</strong> evaluates an expression after adding, replacing or removing filters. It is the most important DAX function.</p>
<pre><code>Revenue Bags = CALCULATE([Total Revenue], Products[Category] = "Bags")</code></pre>
<p>This measure always calculates revenue for the Bags category, whatever you put in the chart. Write the first argument as the thing to calculate and the next arguments as filters. You can give several filters, and they all apply together.</p>
<pre><code>Kampala Large Orders =
CALCULATE([Total Revenue], Branches[Branch] = "Kampala", Sales[Order Size] = "Large")</code></pre>
<h3>ALL: remove filters</h3>
<p>To compare a part with the total, you need a number that ignores the current filter. <strong>ALL</strong> removes filters from a column or table inside CALCULATE.</p>
<pre><code>Revenue All Categories = CALCULATE([Total Revenue], ALL(Products[Category]))</code></pre>
<p>In a chart of categories, this measure shows the same grand total on each bar, because the Category filter is removed. It is the denominator you need for a share.</p>
<h3>DIVIDE: safe division</h3>
<p>In Excel, dividing by zero shows an error. In DAX, you should use <strong>DIVIDE(numerator, denominator)</strong>, which returns blank when the denominator is zero or blank. You may supply a third argument as an alternative result.</p>
<pre><code>Profit Margin % = DIVIDE([Profit], [Total Revenue])
Share of Revenue % = DIVIDE([Total Revenue], [Revenue All Categories])</code></pre>
<p>Format both measures as <strong>Percentage</strong> using the Measure tools ribbon.</p>
<h3>Reading a result</h3>
<table>
<thead><tr><th>Category</th><th>Total Revenue</th><th>Revenue All Categories</th><th>Share of Revenue %</th></tr></thead>
<tbody>
<tr><td>Bags</td><td>75,000</td><td>191,000</td><td>39.3%</td></tr>
<tr><td>Electronics</td><td>60,000</td><td>191,000</td><td>31.4%</td></tr>
<tr><td>Stationery</td><td>56,000</td><td>191,000</td><td>29.3%</td></tr>
</tbody>
</table>
<p>The shares add up to 100 percent apart from small rounding.</p>
<h3>Use filters that return text or numbers</h3>
<p>For simple filters such as <code>Products[Category] = "Bags"</code>, you write the column, an equals sign and the value in quotation marks for text or plain for numbers. For more complex logic you can learn FILTER and other functions later. This course keeps to the simple form.</p>
<h3>Common mistakes</h3>
<ul>
<li>Writing the plain division symbol and getting errors when a denominator is zero. Use DIVIDE.</li>
<li>Forgetting that a visual still adds its own filters. A CALCULATE filter on the same column replaces the visual's filter, which can surprise you.</li>
<li>Using ALL on a whole table when you only wanted to remove one column's filter.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Create a measure that calculates revenue for one category of your choice using CALCULATE.</li>
<li>Create <code>Revenue All Categories</code> using ALL on the Category column.</li>
<li>Create <code>Share of Revenue %</code> with DIVIDE and format it as a percentage.</li>
<li>Show Category, Total Revenue and Share of Revenue % in a table visual and check that the shares add up to about 100 percent.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Filter context is the set of filters that applies to each number in a visual.</li>
<li>CALCULATE changes the filter context of a measure.</li>
<li>ALL removes filters so that you can compute totals for comparison.</li>
<li>DIVIDE is the safe way to compute ratios and percentages.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The date table and simple time intelligence',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to create a date table and write measures that compare periods, such as month to date, year to date and the previous month. This matters because nearly every report asks "how are we doing compared with before?".</p>
<h3>Why a separate date table?</h3>
<p>Time intelligence functions need a table with <strong>one row for every day</strong> in the period, with no gaps, marked as the model's date table. If you only use the dates in the Sales table, days with no sales are missing, and the functions will give wrong or blank results. A date table also gives you year, quarter and month columns for charts and slicers.</p>
<h3>Create a date table</h3>
<ol>
<li>Select <strong>Home</strong> (or <strong>Modeling</strong>), then <strong>New table</strong>.</li>
<li>Type the formula that suits your period. A fixed period is easy to read:</li>
</ol>
<pre><code>Date = CALENDAR(DATE(2025, 1, 1), DATE(2025, 12, 31))</code></pre>
<p>Or, to let Power BI cover whole years that match your data, use <code>Date = CALENDARAUTO()</code>. Both create a column called <code>Date</code>.</p>
<h3>Add useful columns</h3>
<p>With the Date table selected, add columns with <strong>New column</strong>:</p>
<pre><code>Year = YEAR('Date'[Date])
Month Number = MONTH('Date'[Date])
Month = FORMAT('Date'[Date], "mmm")
Year-Month = FORMAT('Date'[Date], "yyyy-mm")</code></pre>
<p>Text months sort alphabetically (Apr, Aug, Dec). To fix that, select the Month column, choose <strong>Column tools</strong>, then <strong>Sort by column</strong>, and pick Month Number.</p>
<h3>Mark it and relate it</h3>
<ol>
<li>Right-click the Date table in the Data pane and choose <strong>Mark as date table</strong>, then pick the Date column.</li>
<li>In Model view, drag <code>Date[Date]</code> to <code>Sales[Date]</code>. The relationship is one to many with single direction.</li>
<li>Make sure the Sales[Date] column is of type Date, without a time part.</li>
</ol>
<h3>Simple time intelligence measures</h3>
<pre><code>Revenue YTD = TOTALYTD([Total Revenue], 'Date'[Date])
Revenue Last Month = CALCULATE([Total Revenue], DATEADD('Date'[Date], -1, MONTH))
Revenue Last Year = CALCULATE([Total Revenue], SAMEPERIODLASTYEAR('Date'[Date]))
Month Growth % = DIVIDE([Total Revenue] - [Revenue Last Month], [Revenue Last Month])</code></pre>
<table>
<thead><tr><th>Function</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>TOTALYTD</td><td>Adds from 1 January of the year up to the current date in the filter</td></tr>
<tr><td>DATEADD</td><td>Shifts the dates by a number of days, months, quarters or years</td></tr>
<tr><td>SAMEPERIODLASTYEAR</td><td>Shifts the current dates back exactly one year</td></tr>
</tbody>
</table>
<p>Time intelligence needs you to put <code>Date[Month]</code> or <code>Date[Date]</code> from the Date table on the visual's axis, not the date column from the Sales table. Otherwise the measures will not respond correctly.</p>
<h3>A worked reading</h3>
<p>Suppose monthly revenue is 52,000 in January, 85,000 in February and 54,000 in March. The measure Revenue Last Month in February shows 52,000, and Month Growth % is (85,000 minus 52,000) divided by 52,000, which is about 63.5 percent. In March, last month is 85,000 and growth is about minus 36.5 percent. In January, last month is blank, so growth is blank, which is correct because there is no earlier data.</p>
<h3>Check the fiscal year</h3>
<p>Some organisations use a financial year that starts in July. TOTALYTD accepts an optional year-end date for this purpose, for example <code>TOTALYTD([Total Revenue], 'Date'[Date], "30/6")</code>. The format of that text depends on settings, so check the documentation for your version before using it.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a Date table that covers all the dates in your sample sales.</li>
<li>Add Year, Month Number and Month columns, sort Month by Month Number and mark it as a date table.</li>
<li>Relate Date[Date] to Sales[Date].</li>
<li>Write Revenue YTD and Revenue Last Month.</li>
<li>Show Month, Total Revenue and Revenue Last Month in a table visual and check one row by hand.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Time intelligence needs a complete date table marked as the date table.</li>
<li>Always use fields from the Date table on axes and slicers for time measures.</li>
<li>TOTALYTD, DATEADD and SAMEPERIODLASTYEAR cover many common comparisons.</li>
<li>Sort text months by a month number column.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: In a star schema, which table normally holds sales events and numbers to add up?
- The dimension table
* The fact table
- The date slicer
- The theme file
= Fact tables hold events and measurable numbers. Dimension tables describe them.

Q: Which relationship type is the standard between a dimension and a fact table?
- Many-to-many
- One-to-one only
* One-to-many with a single filter direction
- No relationship
= The unique key is on the dimension (one) side and filters flow to the fact table.

Q: When is a measure better than a calculated column?
- When you want a label for each row
* When you want a total, average or ratio that responds to filters
- When you need a text label for a slicer
- Never
= Measures calculate in context at report time and are right for numbers that are added up.

Q: Which formula calculates revenue correctly when you have Units and UnitPrice columns in the Sales table?
- SUM(Sales[Units]) * SUM(Sales[UnitPrice])
- SUM(Sales[Revenue Column]) without making the column
* SUMX(Sales, Sales[Units] * Sales[UnitPrice])
- COUNTROWS(Sales)
= SUMX multiplies for each row first and then adds the results.

Q: What does CALCULATE do?
- Deletes data
- Sorts a column
* Evaluates an expression after changing the filters
- Creates a relationship
= CALCULATE modifies the filter context of an expression.

Q: Why use DIVIDE instead of the division symbol?
- It runs faster only
- It colours the result
* It handles a zero or blank denominator without an error
- It changes the data type
= DIVIDE returns a blank (or an alternative result) when the denominator is zero.

Q: A time intelligence measure shows wrong results when the axis uses the date column from the Sales table. What is the likely fix?
- Remove the visual
* Use the Date column from a complete date table that is marked as the date table
- Change the page size
- Use a pie chart
= Time intelligence needs a continuous date table related to the fact table.

Q: Which measure shows revenue for the same period one year earlier?
- TOTALYTD
* CALCULATE with SAMEPERIODLASTYEAR
- COUNTROWS
- AVERAGE
= SAMEPERIODLASTYEAR shifts the date filter back one year.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 4
        [
            'title'   => 'Module 4: Charts, report pages and design',
            'summary' => 'Choose the right visual, build interactive pages with slicers and filters, and design reports that people can read and use.',
            'lessons' => [
                [
                    'title'   => 'Choosing the right chart',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to match a question to a suitable chart type and avoid charts that mislead. This matters because the same numbers can either explain a story or hide it, depending on the chart you choose.</p>
<h3>Start with the question</h3>
<p>Before you open the Visualizations pane, write down what the reader wants to know. Most questions fall into a few families:</p>
<table>
<thead><tr><th>Question</th><th>Good choices</th><th>Example</th></tr></thead>
<tbody>
<tr><td>How much in total?</td><td>Card, KPI</td><td>Total revenue this month</td></tr>
<tr><td>Which is bigger or smaller?</td><td>Bar chart (horizontal), column chart</td><td>Revenue by branch</td></tr>
<tr><td>How does it change over time?</td><td>Line chart, column chart over months</td><td>Monthly revenue</td></tr>
<tr><td>What share of the whole?</td><td>Stacked bar, donut or treemap (few parts only)</td><td>Revenue share by category</td></tr>
<tr><td>How are two numbers related?</td><td>Scatter chart</td><td>Price versus units sold per product</td></tr>
<tr><td>Where is it?</td><td>Map</td><td>Learners per district</td></tr>
<tr><td>I need exact values</td><td>Table or matrix</td><td>Revenue by branch and month</td></tr>
</tbody>
</table>
<h3>Chart-by-chart advice</h3>
<ul>
<li><strong>Bar and column charts</strong> are the most reliable. Use horizontal bars when labels are long. Sort bars from largest to smallest unless the categories have a natural order, such as months. Start the value axis at zero, because truncated axes exaggerate small differences.</li>
<li><strong>Line charts</strong> show trends over continuous time. Use few lines (about four or fewer) so the picture stays readable.</li>
<li><strong>Pie and donut charts</strong> work only for a small number of parts (about three to five) that add up to a whole. Differences between similar slices are hard to see. If in doubt, use a bar chart.</li>
<li><strong>Treemaps</strong> show many parts of a whole as rectangles, but exact comparisons are difficult.</li>
<li><strong>Scatter charts</strong> show relationships and outliers, and need some chart reading skill from the audience.</li>
<li><strong>Cards</strong> show one important number and are good at the top of a page.</li>
<li><strong>Tables and matrices</strong> give exact values. A matrix puts one field in rows and another in columns, like a PivotTable.</li>
<li><strong>Gauges</strong> look attractive but use a lot of space for one number. A card with a target is often clearer.</li>
</ul>
<h3>Things to avoid</h3>
<ol>
<li>3D effects and heavy decoration that distort the sizes.</li>
<li>Too many colours. Use one colour for neutral data and a second colour to highlight what matters.</li>
<li>Dual axes with different scales that suggest relationships that do not exist.</li>
<li>Pie charts with many tiny slices.</li>
<li>Charts without titles, units or a time period.</li>
</ol>
<h3>Write a title that says something</h3>
<p>A title such as "Revenue by branch" is correct. A title such as "Kampala earns almost half of all revenue" tells the reader what to notice. Use a descriptive title for exploration and a message title for a final report. Always add the unit (UGX, learners, percent) and the period.</p>
<h3>Change the visual type</h3>
<p>You can switch the type without rebuilding. Select the visual, then click another icon in the Visualizations pane. The fields stay in place if the new type can use them. Try two or three types and see which one answers the question fastest.</p>
<h3>Try it yourself</h3>
<ol>
<li>Use your sample sales data or any small table with at least three categories and several months.</li>
<li>Build a bar chart sorted from largest to smallest. To sort, select the three dots at the top of the visual, then <strong>Sort axis</strong>, then <strong>Sort descending</strong>.</li>
<li>Build a line chart of revenue by month.</li>
<li>Build a donut chart of the same categories, then compare it with the bar chart. Which is easier to read?</li>
<li>Give each chart a message title.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Choose the chart from the question the reader wants answered.</li>
<li>Bar and line charts are the safest everyday choices, and pies work only with a few parts.</li>
<li>Start value axes at zero and avoid 3D and decoration.</li>
<li>Write titles that state the message, with units and period.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Building report pages: cards, tables and matrices',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to arrange visuals on a page, format them and build a clear one-page summary with cards, charts and a table. This matters because a reader should be able to understand the main message of a page in a few seconds.</p>
<h3>Plan the page on paper first</h3>
<p>Sketch the page. Put the most important numbers (cards) at the top, the main chart on the left or centre, supporting charts beside or beneath it and a detailed table at the bottom. People in languages that read left to right usually start at the top left, so place the most important item there.</p>
<h3>Page size and background</h3>
<p>In Report view select the empty canvas, then open <strong>Format page</strong> in the Visualizations pane. Under <strong>Canvas settings</strong> the default type is 16:9, which suits most screens. Choose a plain light background or white to make the visuals stand out.</p>
<h3>Add and format visuals</h3>
<ol>
<li>Click an empty part of the canvas, then click a visual icon in the Visualizations pane.</li>
<li>Drag the fields from the Data pane into the wells. Measures go into <strong>Values</strong>, dimension fields into <strong>Axis</strong>, <strong>Legend</strong>, <strong>Rows</strong> or <strong>Columns</strong> depending on the visual.</li>
<li>Resize with the handles and move by dragging the top edge.</li>
<li>Select <strong>Format your visual</strong> (the paint roller icon). Each visual has <strong>Visual</strong> settings and <strong>General</strong> settings. Under General, you can turn on <strong>Title</strong> and <strong>Alt text</strong>.</li>
<li>Use <strong>Format painter</strong> (Home tab) to copy formatting from one visual to another.</li>
</ol>
<h3>Cards and the new card visual</h3>
<p>A <strong>card</strong> shows one value, for example Total Revenue. Give it a title or a category label that reads "Total revenue (UGX)". Newer versions of Power BI include a "new card" visual that can show several values and labels in one object. Either is fine, and the icon names can vary by version, so hover over icons to see their names.</p>
<h3>Tables and matrices</h3>
<p>A <strong>table</strong> lists records and values. Put fields such as Branch, Product and the measures into the Columns well. A <strong>matrix</strong> allows grouping:</p>
<ul>
<li>Put Branch in <strong>Rows</strong>, Month in <strong>Columns</strong> and Total Revenue in <strong>Values</strong>.</li>
<li>Use <strong>Conditional formatting</strong> (right-click a value field in the Values well, or use the Cell elements section) to colour cells or add data bars.</li>
<li>Switch off row and column totals if they confuse readers.</li>
</ul>
<h3>Number formats and units</h3>
<p>For each measure, use the Measure tools ribbon to set a format. Choose <strong>Currency</strong> or a number with thousands separators. For big numbers, use <strong>Display units</strong> in the visual's format options to show thousands (K) or millions (M). Always state the unit in the title or the label, so that the reader knows whether 85 means 85 shillings or 85,000.</p>
<h3>Multiple pages</h3>
<p>Do not put everything on one page. Add a new page with the plus sign at the bottom. A good small report might have:</p>
<ol>
<li><strong>Overview:</strong> key totals and the main trend.</li>
<li><strong>Detail:</strong> by branch, product or class.</li>
<li><strong>Data table:</strong> for people who want to check numbers.</li>
</ol>
<p>Rename pages by double-clicking the tab, and keep names short.</p>
<h3>Align and group</h3>
<p>Select several visuals with Ctrl and click, then use <strong>Format</strong>, then <strong>Align</strong> to line edges or distribute spacing evenly. Use the <strong>Selection pane</strong> (View tab) to see the list of objects, rename them clearly and hide those you do not want on the page.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a page with three cards: Total Revenue, Total Units and Profit Margin %.</li>
<li>Add a bar chart of revenue by branch and a line chart of revenue by month.</li>
<li>Add a matrix with Branch in rows and Month in columns.</li>
<li>Apply a background colour to the matrix values with conditional formatting.</li>
<li>Align and evenly space the visuals and give every visual a title.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Sketch the layout first and put the most important numbers at the top left.</li>
<li>Use cards for key totals, bars and lines for comparison and trends, and matrices for detail.</li>
<li>Format numbers and state units in titles or labels.</li>
<li>Use several short pages instead of one crowded page.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Slicers, filters and interactions',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to let readers explore a report by using slicers, filters, cross-highlighting and drill-down. This matters because one report can answer many questions for different people without you building a new page each time.</p>
<h3>Slicers</h3>
<p>A <strong>slicer</strong> is a visual that filters other visuals on the page. To add one:</p>
<ol>
<li>Click an empty part of the canvas and select the <strong>Slicer</strong> icon in the Visualizations pane.</li>
<li>Drag a field such as <code>Branches[Branch]</code> or <code>Date[Month]</code> into the <strong>Field</strong> well.</li>
<li>In <strong>Format your visual</strong>, open <strong>Slicer settings</strong> and choose a style: List, Dropdown, Tile or Between (for number and date ranges).</li>
<li>Use <strong>Ctrl and click</strong> to select several items, or turn on <strong>Multi-select with Ctrl</strong> in the settings if you want to change it.</li>
</ol>
<p>Choose the style by the number of items. Tiles or lists suit five or fewer items. A dropdown is better for many items. A date slicer such as Between lets readers choose a range. Always tell readers how to clear a slicer. Many versions show an eraser icon in the corner.</p>
<h3>Syncing slicers across pages</h3>
<p>Slicers apply only to their own page unless you sync them. Select the slicer, open <strong>View</strong>, then <strong>Sync slicers</strong>, and tick the pages where it should appear and where it should apply.</p>
<h3>The Filters pane</h3>
<p>The <strong>Filters pane</strong> offers three levels:</p>
<table>
<thead><tr><th>Level</th><th>Affects</th></tr></thead>
<tbody>
<tr><td>Filters on this visual</td><td>Only the selected visual</td></tr>
<tr><td>Filters on this page</td><td>All visuals on the page</td></tr>
<tr><td>Filters on all pages</td><td>The whole report</td></tr>
</tbody>
</table>
<p>Drag a field into one of the areas and choose <strong>Basic filtering</strong> (tick items), <strong>Advanced filtering</strong> (rules such as "is greater than") or <strong>Top N</strong> (for example the top 5 products by revenue). Use filters to restrict the data that the report author wants, and slicers for choices that the reader should make.</p>
<h3>Cross-highlighting and Edit interactions</h3>
<p>When a reader clicks a bar, the other visuals on the page usually highlight or filter to match that choice. This is called <strong>cross-filtering or cross-highlighting</strong>. If you do not want one visual to affect another, select the visual that is clicked, choose <strong>Format</strong>, then <strong>Edit interactions</strong>, and use the small icons above the other visuals to choose Filter, Highlight or None.</p>
<h3>Drill down and hierarchies</h3>
<p>A hierarchy lets readers move from a general level to a detailed one, such as Year, Quarter, Month, Day. Drag several fields into the <strong>X-axis</strong> well in order. Then use the drill arrows at the top of the visual to go down one level. You can also build your own hierarchy by right-clicking a field in the Data pane and choosing <strong>Create hierarchy</strong>, for example Region, Branch, Salesperson.</p>
<h3>Drillthrough and tooltips</h3>
<p><strong>Drillthrough</strong> lets a reader right-click a data point, such as a branch, and jump to a detail page already filtered to that branch. You create a page, add the field to the <strong>Drill through</strong> well and design the details. <strong>Report page tooltips</strong> show a small chart when a reader hovers over a data point. Both features are advanced, so use them after your basic pages work well.</p>
<h3>Bookmarks</h3>
<p>A <strong>bookmark</strong> saves the current state of a page, including filters and visibility. You can connect bookmarks to buttons for simple navigation or to switch between views, for example between a chart and a table. Keep it simple because too many hidden layers confuse people and can slow the report.</p>
<h3>Try it yourself</h3>
<ol>
<li>Add a Branch slicer using the Tile style and a Month slicer using a Dropdown.</li>
<li>Click one branch and watch the other visuals change, then clear the slicer.</li>
<li>Add a page-level Top N filter to show only the top 3 products by revenue in a bar chart.</li>
<li>Use Edit interactions to stop the cards from being filtered by a bar chart click.</li>
<li>Build a hierarchy with Year and Month and try the drill arrows.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Slicers let readers choose, and filters let authors restrict the data.</li>
<li>Filters can apply to one visual, one page or the whole report.</li>
<li>Edit interactions control which visuals respond to a click.</li>
<li>Hierarchies, drillthrough and bookmarks add depth, but keep the design simple first.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Design principles and accessibility for reports',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to improve a report so that it is clear, consistent and usable by people with different abilities. This matters because a report is only useful when its audience can read it, including people with colour blindness, low vision, small phone screens or a screen reader.</p>
<h3>Clear design in five habits</h3>
<ol>
<li><strong>One message per page.</strong> If a page tries to answer ten questions, it answers none. Split it.</li>
<li><strong>Visual hierarchy.</strong> The most important number is the largest and sits at the top left. Supporting information is smaller and quieter.</li>
<li><strong>White space.</strong> Leave space between visuals. Crowded pages tire the reader.</li>
<li><strong>Consistency.</strong> Use the same fonts, colours, number formats and positions for the same things on every page. If blue means Kampala on one chart, it should not mean Jinja on another.</li>
<li><strong>Less ink.</strong> Remove gridlines, borders and shadows that do not help. Keep what the reader needs.</li>
</ol>
<h3>Colour</h3>
<ul>
<li>Pick a small palette. Use a neutral colour for most data and a strong colour for what you want to highlight.</li>
<li>Do not use red and green as the only way to separate good and bad, because many people cannot tell them apart. Add icons, labels or different shades.</li>
<li>Check contrast. Dark text on a light background, or the reverse, is easiest to read. Avoid light grey on white.</li>
<li>Use a theme. Under <strong>View</strong>, then <strong>Themes</strong>, you can pick a built-in theme or import a custom one so that all visuals share the same colours and fonts. Some built-in themes are designed for accessibility.</li>
</ul>
<h3>Text</h3>
<ul>
<li>Use readable sizes. Body text such as axis labels should not be tiny. If the report will be shown on a projector or phone, test it from a distance.</li>
<li>Use plain language for titles and labels, and avoid abbreviations your audience may not know.</li>
<li>Show data labels when exact values matter, and remove them when they clutter.</li>
</ul>
<h3>Accessibility features in Power BI</h3>
<table>
<thead><tr><th>Feature</th><th>How to use it</th></tr></thead>
<tbody>
<tr><td>Alt text</td><td>Select a visual, open Format your visual, General, Alt text, and describe what the visual shows and the main message</td></tr>
<tr><td>Titles</td><td>Give every visual a meaningful title</td></tr>
<tr><td>Tab order</td><td>In the Selection pane or Format settings, set the order in which keyboard users move through visuals, matching a logical reading order</td></tr>
<tr><td>Hide decorative items</td><td>Mark shapes or lines that only decorate so that screen readers can skip them</td></tr>
<tr><td>Show as a table</td><td>Readers can view the data behind a visual as a table, which helps screen reader users and anyone who wants the numbers</td></tr>
<tr><td>Keyboard navigation</td><td>Test that you can reach the slicers and visuals using the keyboard</td></tr>
</tbody>
</table>
<p>The exact menu names move around between versions, so use the Microsoft Learn accessibility pages in the resources to check them.</p>
<h3>Mobile layout</h3>
<p>In Desktop, choose <strong>View</strong>, then <strong>Mobile layout</strong> to arrange a version for phone screens. Place the most important cards and one or two charts in a single column. Many readers in East Africa will view the report on a phone, so test this layout.</p>
<h3>Honesty in reporting</h3>
<ul>
<li>Show the period and the source of the data on the page, for example "Source: branch sales sheets, January to March 2025".</li>
<li>Do not remove an inconvenient category or truncate an axis to make a result look better.</li>
<li>Protect personal data. Do not show names and sensitive details unless necessary and permitted.</li>
</ul>
<h3>A quick review checklist</h3>
<ol>
<li>Can a stranger tell the main message in five seconds?</li>
<li>Do all visuals have titles with units?</li>
<li>Is colour never the only signal?</li>
<li>Do the visuals have alt text and a sensible tab order?</li>
<li>Does the mobile layout work?</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Take a page from your sample report and remove clutter: gridlines, extra borders and unneeded legends.</li>
<li>Apply a theme from <strong>View</strong>, then <strong>Themes</strong>.</li>
<li>Add alt text to every visual and check the tab order.</li>
<li>Create the mobile layout with one card and one chart.</li>
<li>Ask one person to read the page and tell you the message after five seconds.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Good design means one message per page, a clear hierarchy, white space and consistency.</li>
<li>Do not rely on colour alone, and keep contrast high.</li>
<li>Add alt text, titles, a sensible tab order and a mobile layout.</li>
<li>State the source and the period, and report honestly.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which chart is best for comparing revenue across eight branches?
- A pie chart with eight slices
- A 3D donut chart
* A sorted bar chart
- A gauge
= Bars are easy to compare and to sort.

Q: Which chart is most suitable for a trend across twelve months?
* Line chart
- Treemap
- Pie chart
- Card
= Lines show change over continuous time.

Q: Why should a bar chart's value axis start at zero?
- It looks tidier
* A truncated axis exaggerates small differences and misleads
- It makes the file smaller
- It is required for slicers
= Bars represent length, so the baseline must be zero.

Q: Which action best reduces the risk of colour-blind readers misreading a chart?
- Use red and green only
- Use thin pale lines
* Add labels or icons and do not rely on colour alone
- Remove the title
= Different signals help people who cannot tell certain colours apart.

Q: What does a slicer do?
- Deletes data
* Filters other visuals on the page according to the reader's choices
- Combines two tables
- Changes the data type
= Slicers are interactive filters placed on the report page.

Q: At which level does a filter set in "Filters on all pages" apply?
- One visual
- One page
* The whole report
- Only the slicers
= The three levels are visual, page and all pages.

Q: What is the purpose of alt text on a visual? (choose all that apply)
* It describes the visual for screen reader users
* It states the main message in words
- It increases the refresh speed
- It changes the colour theme
= Alt text makes charts understandable for people who cannot see them.

Q: What is a good use of Edit interactions?
- To rename a table
* To stop a click on one visual from filtering another visual
- To add a data source
- To change a relationship
= Edit interactions set whether visuals filter, highlight or ignore each other.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 5
        [
            'title'   => 'Module 5: Publishing, refreshing and a complete mini-project',
            'summary' => 'Learn how reports are shared and kept current, then build a full small report from a sample table.',
            'lessons' => [
                [
                    'title'   => 'Publishing, sharing, dashboards and refresh',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain how a report moves from Desktop to the online service, how people can view it and how its data is kept up to date. This matters because a beautiful report that nobody can open, or that shows last year's numbers, has no value.</p>
<h3>Publishing from Desktop</h3>
<p>In Power BI Desktop, select <strong>Home</strong>, then <strong>Publish</strong>. Sign in with a work or school account, as the service usually needs one, and choose a <strong>workspace</strong>. A workspace is a shared area in the service that holds reports, dashboards and semantic models. Some people have a personal workspace called "My workspace". Team workspaces allow several people to work together. After publishing you get a link that opens the report in the browser.</p>
<p>Whether you can create team workspaces, share with others and use certain features depends on the <strong>licence</strong> that you or your organisation holds. Options and names have changed several times and may differ by country and organisation, so ask your administrator and read Microsoft's current documentation instead of relying on old advice.</p>
<h3>Reports and dashboards: not the same thing</h3>
<table>
<thead><tr><th>Item</th><th>Report</th><th>Dashboard</th></tr></thead>
<tbody>
<tr><td>Pages</td><td>One or more</td><td>Exactly one</td></tr>
<tr><td>Built in</td><td>Desktop or the service</td><td>The service only</td></tr>
<tr><td>Data sources</td><td>One semantic model, usually</td><td>Visuals from many reports and models</td></tr>
<tr><td>Interaction</td><td>Slicers, filters, drill down</td><td>Mostly viewing, click to open the source report</td></tr>
<tr><td>Purpose</td><td>Explore and explain</td><td>Monitor key numbers at a glance</td></tr>
</tbody>
</table>
<p>To create a dashboard, open a report in the service, hover over a visual and select the <strong>Pin</strong> icon, then choose a new or existing dashboard. Pinned visuals update when the data refreshes. Many small teams only need reports and can skip dashboards.</p>
<h3>Sharing options in general terms</h3>
<ul>
<li><strong>Share a link</strong> to a report with named people. They may need a licence or the report may need to be in a suitable workspace.</li>
<li><strong>Workspace access</strong> gives team members roles such as viewer, contributor, member and admin.</li>
<li><strong>Apps</strong> package a set of reports and dashboards for a wider audience.</li>
<li><strong>Export</strong> a report to PDF or PowerPoint for people who do not use the service.</li>
<li><strong>Embed or publish to the web</strong> options exist, but publishing to the web makes data visible to anyone with the link. <strong>Never use it for private or sensitive data.</strong></li>
</ul>
<h3>Row-level security in one paragraph</h3>
<p><strong>Row-level security (RLS)</strong> lets one report show different rows to different viewers, for example each branch manager sees only their own branch. You define roles with a filter rule in Desktop (Modeling, then Manage roles), publish and then assign people to the roles in the service. It is a powerful way to protect data, and you should test it carefully before sharing.</p>
<h3>Keeping the data fresh</h3>
<p>Published reports contain the data as it was when you last refreshed. There are two ways to refresh:</p>
<ol>
<li><strong>Manual refresh in Desktop:</strong> select <strong>Home</strong>, then <strong>Refresh</strong>, then publish again.</li>
<li><strong>Scheduled refresh in the service:</strong> open the semantic model's settings, find <strong>Scheduled refresh</strong>, choose the times and turn it on. The number of refreshes per day depends on the licence.</li>
</ol>
<p>The service needs to reach your data. If the data is in a supported cloud location, you can enter credentials in the settings. If the data is on your own computer or a private network, a <strong>gateway</strong> is a small program that creates a secure bridge. Gateways need care and an administrator. For learners, the easiest route is to keep the source files in a cloud location that the service supports.</p>
<h3>Data refresh problems</h3>
<ul>
<li>A source file was moved or renamed.</li>
<li>Credentials changed or expired.</li>
<li>A column was renamed in the source, so a query step fails.</li>
<li>The new data has a different layout.</li>
</ul>
<p>The service shows refresh history and error messages. Read the message, fix the source or the query, and refresh again.</p>
<h3>Version control and ownership</h3>
<p>Keep the master .pbix file in a shared, backed-up place, name versions clearly, and decide who is the owner of each report. A report that nobody owns will slowly break.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write a short plan for your sample report: who will read it, how they will open it and how often the data changes.</li>
<li>Decide whether the readers need a dashboard, a report or just a PDF.</li>
<li>List where the source file will be stored so that a scheduled refresh could reach it.</li>
<li>If you have access to the service, publish a practice report to My workspace and open it in the browser.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Publishing sends the report and its model to a workspace in the service.</li>
<li>Reports are interactive and multi-page, while dashboards are single pages of pinned visuals.</li>
<li>Scheduled refresh keeps the data current, and gateways connect to private data.</li>
<li>Licensing and features vary, so confirm them with your administrator, and never publish sensitive data to the open web.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Mini-project, part 1: data, Power Query and model',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will have a clean, related data model for a small shop, ready for measures and visuals. This matters because it shows how the steps of the earlier modules join into one process that you can repeat for your own data.</p>
<h3>The scenario</h3>
<p>A small school-supplies company has three branches: Kampala, Jinja and Mbarara. The owner wants a report that shows revenue, profit, the best products and how sales change by month during the first quarter of 2025. The numbers below are made up for practice.</p>
<h3>Step 1: Create the data in Excel</h3>
<p>Create a workbook named <code>shop-data.xlsx</code> with two sheets, each formatted as a table (select the cells, press <strong>Ctrl+T</strong>). Name the tables <code>Sales</code> and <code>Products</code> using <strong>Table Design</strong>, <strong>Table Name</strong>.</p>
<p><strong>Sales table</strong></p>
<pre><code>OrderID  Date        Branch    ProductID  Units  UnitPrice
S001     05/01/2025  Kampala   P01        10     2000
S002     12/01/2025  Kampala   P02        4      5000
S003     20/01/2025  Jinja     P01        6      2000
S004     03/02/2025  Jinja     P03        3      12000
S005     14/02/2025  Kampala   P03        2      12000
S006     21/02/2025  Mbarara   P02        5      5000
S007     02/03/2025  Mbarara   P01        12     2000
S008     15/03/2025  Kampala   P02        6      5000</code></pre>
<p><strong>Products table</strong></p>
<pre><code>ProductID  Product        Category     UnitCost
P01        Exercise book  Stationery   1200
P02        School bag     Bags         3500
P03        Calculator     Electronics  8500</code></pre>
<p>The dates are written as day, month, year. Type them as real dates in Excel and check that they are right aligned. Save and close the workbook.</p>
<h3>Step 2: Load with Power Query</h3>
<ol>
<li>In Power BI Desktop select <strong>Home</strong>, <strong>Get data</strong>, <strong>Excel workbook</strong> and choose your file.</li>
<li>Tick both tables, then select <strong>Transform Data</strong>.</li>
<li>In the Sales query, check the types: Date is Date, Units is Whole Number, UnitPrice is Whole Number (or Fixed decimal), the rest are Text.</li>
<li>Select the Branch column and use <strong>Transform</strong>, <strong>Format</strong>, <strong>Trim</strong>, to remove accidental spaces.</li>
<li>In the Products query, check that UnitCost is a number and ProductID is text.</li>
<li>Confirm that ProductID is unique in Products. Select the column and choose <strong>Home</strong>, <strong>Remove Rows</strong>, <strong>Remove Duplicates</strong> if needed.</li>
<li>Select <strong>Close and Apply</strong>.</li>
</ol>
<h3>Step 3: Add a Branches dimension</h3>
<p>The branch name is repeated in Sales. To follow the star schema, create a small Branches table: choose <strong>Home</strong>, <strong>Enter data</strong>, create one column called <code>Branch</code> with the values Kampala, Jinja and Mbarara and one column called <code>Region</code> with Central, Eastern and Western. Name the table <code>Branches</code> and load it. (Real regions depend on your own business; these are examples.)</p>
<h3>Step 4: Create the Date table</h3>
<p>Select <strong>Home</strong>, <strong>New table</strong> and enter:</p>
<pre><code>Date = CALENDAR(DATE(2025, 1, 1), DATE(2025, 3, 31))</code></pre>
<p>Add columns as in the date lesson: <code>Month Number = MONTH('Date'[Date])</code> and <code>Month = FORMAT('Date'[Date], "mmm")</code>. Sort Month by Month Number and mark the table as a date table.</p>
<h3>Step 5: Build the relationships</h3>
<p>Open <strong>Model view</strong> and make sure these exist, each one to many, single direction, with the dimension on the "one" side:</p>
<ul>
<li><code>Products[ProductID]</code> to <code>Sales[ProductID]</code></li>
<li><code>Branches[Branch]</code> to <code>Sales[Branch]</code></li>
<li><code>Date[Date]</code> to <code>Sales[Date]</code></li>
</ul>
<p>Your model is now a star with Sales in the middle and three dimensions around it. Hide <code>Sales[ProductID]</code>, <code>Sales[Branch]</code> and <code>Sales[Date]</code> from Report view, so that people use the dimension fields.</p>
<h3>Step 6: Test the model</h3>
<p>Go to Report view and create a table visual with <code>Products[Category]</code> and the sum of <code>Sales[Units]</code>. You should get: Bags 15, Electronics 5 and Stationery 28. Check by hand: bags are 4 + 5 + 6 = 15, electronics 3 + 2 = 5 and stationery 10 + 6 + 12 = 28. If the numbers match, the model works.</p>
<h3>Try it yourself</h3>
<ol>
<li>Build the workbook exactly as shown or with your own similar data.</li>
<li>Load, clean and apply the queries.</li>
<li>Create the Branches and Date tables and mark the date table.</li>
<li>Check the three relationships in Model view.</li>
<li>Pass the test above: category unit totals of 15, 5 and 28.</li>
<li>Save as <code>shop-report.pbix</code>.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A reliable report starts with clean, correctly typed tables.</li>
<li>One fact table and three dimensions form a simple star schema.</li>
<li>Always test the model with a small result you can verify by hand.</li>
<li>Save the file as soon as the model is correct.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Mini-project, part 2: measures and report pages',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will have a working two-page report with measures, cards, charts and slicers built on the model from part 1. This matters because it demonstrates how a few well-chosen measures can answer the owner's questions clearly.</p>
<h3>Step 1: Write the measures</h3>
<p>Select the Sales table and use <strong>Home</strong>, then <strong>New measure</strong> for each formula:</p>
<pre><code>Total Units = SUM(Sales[Units])
Total Revenue = SUMX(Sales, Sales[Units] * Sales[UnitPrice])
Total Cost = SUMX(Sales, Sales[Units] * RELATED(Products[UnitCost]))
Profit = [Total Revenue] - [Total Cost]
Profit Margin % = DIVIDE([Profit], [Total Revenue])
Revenue Last Month = CALCULATE([Total Revenue], DATEADD('Date'[Date], -1, MONTH))
Month Growth % = DIVIDE([Total Revenue] - [Revenue Last Month], [Revenue Last Month])</code></pre>
<p>Format Total Revenue, Total Cost and Profit with thousands separators and no decimals. Format the two percentage measures as percentages with one decimal place.</p>
<h3>Step 2: Know the right answers</h3>
<p>Before you draw anything, calculate what the report should show, so that you can catch mistakes.</p>
<table>
<thead><tr><th>Measure (all data)</th><th>Expected value</th></tr></thead>
<tbody>
<tr><td>Total Units</td><td>48</td></tr>
<tr><td>Total Revenue</td><td>191,000</td></tr>
<tr><td>Total Cost</td><td>128,600</td></tr>
<tr><td>Profit</td><td>62,400</td></tr>
<tr><td>Profit Margin %</td><td>32.7%</td></tr>
</tbody>
</table>
<table>
<thead><tr><th>Month</th><th>Revenue</th><th>Growth</th></tr></thead>
<tbody>
<tr><td>Jan</td><td>52,000</td><td>blank</td></tr>
<tr><td>Feb</td><td>85,000</td><td>63.5%</td></tr>
<tr><td>Mar</td><td>54,000</td><td>minus 36.5%</td></tr>
</tbody>
</table>
<table>
<thead><tr><th>Branch</th><th>Revenue</th></tr></thead>
<tbody>
<tr><td>Kampala</td><td>94,000</td></tr>
<tr><td>Jinja</td><td>48,000</td></tr>
<tr><td>Mbarara</td><td>49,000</td></tr>
</tbody>
</table>
<h3>Step 3: Page 1, the Overview</h3>
<ol>
<li>Rename the first page <strong>Overview</strong>.</li>
<li>Add three <strong>cards</strong> across the top: Total Revenue, Profit and Profit Margin %.</li>
<li>Add a <strong>line chart</strong> or column chart: Axis = <code>Date[Month]</code>, Values = Total Revenue. Title: "Revenue rose in February, then fell in March (UGX)".</li>
<li>Add a <strong>bar chart</strong>: Axis = <code>Branches[Branch]</code>, Values = Total Revenue, sorted descending. Title: "Kampala brings in about half of revenue (UGX)".</li>
<li>Add a <strong>column chart</strong> or bar chart of Profit by <code>Products[Category]</code>.</li>
<li>Add a <strong>slicer</strong> for <code>Date[Month]</code> in Tile style.</li>
</ol>
<p>Test the slicer: choose Feb. The cards should show revenue 85,000. Profit in February: revenue of S004 to S006 is 85,000 and cost is 25,500 + 17,000 + 17,500 = 60,000, so the profit should be 25,000. Clear the slicer afterwards.</p>
<h3>Step 4: Page 2, Products and detail</h3>
<ol>
<li>Add a new page named <strong>Products</strong>.</li>
<li>Add a <strong>matrix</strong> with <code>Products[Product]</code> in Rows, <code>Date[Month]</code> in Columns and Total Revenue in Values.</li>
<li>Add a <strong>table</strong> with Branch, Product, Total Units and Total Revenue, so that readers can see the detail.</li>
<li>Add a <strong>Branch slicer</strong> in List style, and sync it with the Overview page using <strong>View</strong>, <strong>Sync slicers</strong>.</li>
<li>Add a bar chart of Profit by Product to show which product earns the most.</li>
</ol>
<h3>Step 5: Check and label</h3>
<ul>
<li>Every visual has a title with the unit.</li>
<li>A text box on each page states the source and period: "Source: shop-data.xlsx, January to March 2025. Sample data for practice."</li>
<li>Numbers match the expected values above.</li>
</ul>
<h3>Common problems</h3>
<ul>
<li><strong>Profit shows the same value for every row:</strong> a relationship is missing. Check the Model view.</li>
<li><strong>Months appear in alphabetical order:</strong> sort Month by Month Number.</li>
<li><strong>Month Growth % is blank for every month:</strong> the axis uses the Sales date and not the Date table.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Create all seven measures and format them.</li>
<li>Compare each result with the expected tables and fix any difference.</li>
<li>Build both pages as described.</li>
<li>Test both slicers.</li>
<li>Save the file.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A small set of measures (units, revenue, cost, profit, margin and growth) answers most basic business questions.</li>
<li>Know the expected answers by hand before trusting any chart.</li>
<li>Message titles and a source note make pages easy to understand.</li>
<li>Slicers and sync make a small report interactive.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Mini-project, part 3: polish, check and hand over',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to finish a report professionally: improve its design and accessibility, test it, prepare a short guide for readers and plan how to keep it alive. This matters because the difference between a practice file and a trusted report is care in the last twenty percent of the work.</p>
<h3>Step 1: Polish the design</h3>
<ol>
<li>Choose a theme under <strong>View</strong>, then <strong>Themes</strong>. Pick one with strong contrast.</li>
<li>Use a single highlight colour for the most important series, such as Kampala, and a neutral grey for the others where appropriate.</li>
<li>Align the cards and charts with <strong>Format</strong>, then <strong>Align</strong>, and make the gaps equal.</li>
<li>Remove clutter such as unneeded legends, borders and gridlines.</li>
<li>Check number formats and the units in every title.</li>
</ol>
<h3>Step 2: Accessibility pass</h3>
<ol>
<li>Add <strong>alt text</strong> to each visual, for example: "Bar chart of revenue by branch. Kampala is highest at 94,000 shillings, Mbarara 49,000 and Jinja 48,000."</li>
<li>Set a logical <strong>tab order</strong>: title, cards, charts, slicers, table.</li>
<li>Check that colour is not the only signal. For example, add data labels.</li>
<li>Create a <strong>mobile layout</strong> with the three cards and the monthly chart.</li>
</ol>
<h3>Step 3: Test like a user</h3>
<table>
<thead><tr><th>Test</th><th>What to check</th></tr></thead>
<tbody>
<tr><td>Numbers</td><td>All totals match the expected values and add up across branches and months</td></tr>
<tr><td>Slicers</td><td>Clearing and combining slicers behaves sensibly</td></tr>
<tr><td>Edge cases</td><td>What happens when a branch has no sales in a month? Are blanks handled?</td></tr>
<tr><td>Refresh</td><td>Add a new row in Excel (S009 for Jinja, 19/03/2025, product P02, 3 units at 5000). Refresh and see the total revenue rise by 15,000 to 206,000</td></tr>
<tr><td>Reader test</td><td>Ask someone who has not seen it to explain the message in 30 seconds</td></tr>
</tbody>
</table>
<p>Delete or undo the test row afterwards if you want to keep the expected values from earlier lessons. If the date table stops at 31 March, rows after that date will not appear in time-based visuals, so extend the date range when your data grows.</p>
<h3>Step 4: Document the report</h3>
<p>Add a small <strong>About</strong> page. Include the purpose, the data source and refresh date, a short definition of each measure (for example: "Profit = revenue minus cost of goods"), the owner's name and contact, and a note that the figures are sample data. Documenting definitions prevents arguments about what a number means.</p>
<h3>Step 5: Publish and share (if you have access)</h3>
<ol>
<li>Select <strong>Home</strong>, then <strong>Publish</strong> and choose a workspace.</li>
<li>Open the report in the browser and check that all pages and slicers work.</li>
<li>Share only with the people who should see it, and not through public links.</li>
<li>If your source is in a supported cloud location, set up scheduled refresh and confirm that a refresh succeeds.</li>
</ol>
<p>If you cannot publish, export the report pages to PDF using <strong>File</strong>, <strong>Export</strong>, <strong>Export to PDF</strong>, which is useful for sharing with people who do not use Power BI.</p>
<h3>Step 6: What next?</h3>
<ul>
<li>Practise with real public data, such as statistics from national statistics offices, as long as the licence allows reuse.</li>
<li>Learn more DAX: FILTER, ALL variations, RANKX, and variables written with VAR and RETURN.</li>
<li>Learn about performance: remove unused columns, avoid too many visuals per page and prefer measures over many calculated columns.</li>
<li>Explore Microsoft's learning paths and the documentation listed in the resources, and consider a certification if it suits your career, but check the current exams on the official site.</li>
<li>Show your report to a real organisation, such as a school club or a local business, and ask what they would change. This is the best way to improve.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Apply the design polish and accessibility steps to your report.</li>
<li>Run the refresh test with the extra Excel row and confirm the new total of 206,000.</li>
<li>Write the About page with at least five measure definitions.</li>
<li>Ask one person to review the report and write down their comments.</li>
<li>Save your final file with a version name and keep a backup.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Polish, accessibility and testing turn a draft into a trustworthy report.</li>
<li>Test with a changed source row to prove the refresh works.</li>
<li>Document sources, definitions and the owner on an About page.</li>
<li>Share only with intended audiences and keep improving with feedback.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is a workspace in the Power BI service?
- A page inside a report
* A shared area in the service that holds reports, dashboards and semantic models
- A type of chart
- A column in a table
= Workspaces organise and control access to content in the service.

Q: How does a dashboard differ from a report?
- A dashboard has many pages, a report has one
* A dashboard is a single page in the service that pins visuals from reports, a report can have several pages
- They are identical
- A dashboard is built only in Excel
= Dashboards are for monitoring key numbers at a glance.

Q: Which sharing method is NOT suitable for private data?
- Sharing with named people
- A workspace role
* Publishing to the web for anyone with the link
- Row-level security
= Publishing to the web makes data visible to anyone who gets the link.

Q: What does row-level security do?
- Deletes rows after 30 days
* Lets different viewers see different rows of the same report
- Speeds up refresh
- Merges tables
= For example, branch managers see only their branch.

Q: Your source Excel file is only on your laptop and you want a scheduled refresh in the service. What may you need?
- Nothing
* A cloud location the service supports, or a gateway
- A new theme
- A larger monitor
= The service must be able to reach the data source.

Q: In the mini-project the Products table has two rows with ProductID P01. What happens?
- Nothing at all
- The report becomes faster
- Power BI automatically deletes one of the rows
* The one-to-many relationship cannot be created until the key is unique
= The "one" side of a one-to-many relationship must contain unique keys.

Q: In the sample data, what is the total revenue for all eight orders?
- 128,600
* 191,000
- 62,400
- 48
= Revenue is the sum of units times price for all orders, which is 191,000.

Q: You add a new order row to the source and refresh. Total revenue does not change in the report. What should you check first? (choose all that apply)
* That the new row is inside the Excel table and the file was saved
* That the date is within the date range of the Date table
- The colour theme
- The font size
= Missing data usually means the row is outside the table or its date falls outside the date table.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which part of Power BI is normally used to build a report?
- The mobile app
- The service only
* Power BI Desktop
- A PDF reader
= Desktop is where you connect, clean, model and design. The service publishes and shares.

Q: Which sequence shows the usual workflow?
- Publish, model, clean, get data
* Get data, clean, model, calculate, visualise, publish
- Visualise, get data, clean, publish
- Model, publish, get data, clean
= Prepare and model data before building visuals for sharing.

Q: What is the main benefit of Power Query's Applied Steps?
- They make charts colourful
* They record the cleaning actions and replay them at each refresh
- They store passwords
- They create relationships
= Repeatable steps save you from cleaning each new file by hand.

Q: A column of dates is stored as text. What is the best fix?
- Leave it and hope
* Change its data type to Date in Power Query
- Delete the column
- Change the chart to a pie
= Time calculations need a true date type.

Q: Months are spread across columns (Jan, Feb, Mar). Which step makes the table tidy?
- Remove Duplicates
- Split Column
* Unpivot Other Columns
- Trim
= Unpivot turns columns into rows, one row per branch and month.

Q: What does Append Queries do?
- Adds columns from another table using a key
* Stacks tables that have the same columns into one table
- Deletes rows
- Splits a column
= Append combines rows. Merge adds columns by matching keys.

Q: Which join kind keeps all rows of the first table and adds matching details from the second?
- Inner
- Left anti
* Left outer
- Full outer
= A left outer merge keeps every row of the first table.

Q: In a star schema the fact table is:
- A table of descriptions such as product names
* A table of events and numbers, such as sales rows
- A table with one row
- A page of the report
= Dimensions describe, facts record events.

Q: Which setting is usual for a relationship from Products to Sales?
- Many-to-many, both directions
* One-to-many, single direction from Products to Sales
- One-to-one, none
- Many-to-one with no key
= The unique product key is on the dimension side and filters flow to the facts.

Q: Which is a calculated column rather than a measure?
- Total Revenue = SUMX(Sales, Sales[Units] * Sales[UnitPrice])
- Profit Margin % = DIVIDE([Profit], [Total Revenue])
- Total Units = SUM(Sales[Units])
* Order Size = IF(Sales[Units] >= 10, "Large", "Small")
= It is evaluated for each row and gives a label that can be used on axes and slicers.

Q: Why is SUMX used for revenue from units and price columns?
* It multiplies row by row first and then adds
- It sorts the table
- It removes duplicates
- It formats numbers
= Multiplying two totals would give a wrong answer.

Q: What does RELATED do?
- Creates a relationship
- Deletes related rows
* Fetches a value from a related table on the "one" side
- Renames a column
= RELATED reaches across a relationship, for example to get a unit cost from Products.

Q: What is the purpose of CALCULATE?
- To format a number
* To evaluate an expression with modified filters
- To import data
- To publish a report
= It is how you change the filter context of a measure.

Q: A bar chart shows Share of Revenue % for each category. Which function helps to get the all-category total for the denominator?
- DATEADD
- SUM only
* ALL used inside CALCULATE
- FORMAT
= ALL removes the category filter so the denominator is the grand total.

Q: Why use DIVIDE instead of dividing with the slash symbol?
- It makes the result bigger
* It returns a blank or an alternative result when the denominator is zero
- It changes the chart type
- It forces whole numbers
= DIVIDE avoids errors from division by zero.

Q: What must a date table have for time intelligence to work?
- Only the dates that appear in Sales
* One row for every day in the period, marked as the date table and related to the fact table
- A pie chart
- Text dates
= Gaps in the dates break time intelligence functions.

Q: Revenue is 52,000 in January and 85,000 in February. What is Month Growth % for February, to the nearest tenth?
- 33.0 percent
- 38.8 percent
* 63.5 percent
- 163.5 percent
= (85,000 minus 52,000) divided by 52,000 is 0.6346, about 63.5 percent.

Q: Which chart is the best choice to compare revenue across several branches?
* A sorted bar chart
- A 3D pie chart
- A gauge for each branch
- A scatter chart
= Sorted bars make comparison easy.

Q: Which practice makes a chart misleading?
- Starting the value axis of a bar chart at zero
- Using a clear title
* Truncating the value axis of a bar chart so small differences look large
- Showing units
= Bars encode length from a zero baseline.

Q: How should good and bad status be shown for readers who cannot distinguish red and green?
- Use red and green only
* Use labels, icons or different shades in addition to colour
- Remove the legend
- Use very light colours
= Colour should never be the only signal.

Q: What is a slicer?
- A filter on the whole page that only the author can see
* A visual that lets readers choose values to filter other visuals
- A type of relationship
- A DAX function
= Slicers give readers control over what the page shows.

Q: What does Edit interactions let you control?
- The refresh schedule
- The data types
* Whether a click on one visual filters, highlights or does not affect another visual
- The licence
= It sets how visuals respond to each other.

Q: Which of these improve accessibility? (choose all that apply)
* Alt text on visuals
* A logical tab order
- Using tiny fonts to fit more
- Using colour as the only signal
= Alt text and tab order help screen reader and keyboard users.

Q: Which statement about a dashboard is correct?
- It is built only in Desktop
* It is a single page in the service made of pinned visuals
- It has unlimited pages
- It replaces the data model
= Dashboards give an at-a-glance view of key numbers.

Q: A report must show each branch manager only their own branch. Which feature is designed for this?
- Bookmarks
* Row-level security
- Themes
- Mobile layout
= RLS restricts the rows each viewer can see.

Q: Your published report must show new data every morning from a file in a supported cloud location. What do you set up?
- A new theme
* Scheduled refresh in the service
- A different chart
- A larger canvas
= Scheduled refresh keeps the published model up to date.

Q: In the mini-project, a table of Category and sum of Units shows 15, 5 and 28 for Bags, Electronics and Stationery. What does it confirm?
* The relationship from Products to Sales is working and the totals are correct
- That the date table is missing
- That the theme is wrong
- That the file is too large
= The values match the hand calculation, so the model is working.
QUIZ,
];
