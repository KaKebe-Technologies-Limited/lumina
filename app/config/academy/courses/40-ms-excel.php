<?php
return [
    'slug'        => 'ms-excel',
    'title'       => 'Microsoft Excel: Spreadsheets from Zero',
    'category'    => 'office',
    'level'       => 'beginner',
    'icon'        => 'layers',
    'sort'        => 40,
    'summary'     => 'Learn Excel step by step: enter data, format it, write formulas, look things up, draw charts and build real budgets, mark sheets and stock lists.',
    'description' => '<p>Excel is the most widely used spreadsheet program in the world. Shops use it to track stock, schools use it for marks, NGOs use it for budgets and reports, and offices use it for almost everything that involves numbers.</p><p>This course starts from the very beginning. You do not need to know what a formula is. Each lesson explains one idea in plain language, shows you exactly where to click, and ends with a short activity you do yourself.</p><p>By the end you will have built a budget, a mark sheet and a stock sheet, and you will know how to print, protect and share your work.</p>',
    'outcomes'    => [
        'Describe a workbook, worksheet, cell, row and column and move around them quickly',
        'Enter, edit, copy and fill data, and save your work safely',
        'Format numbers as UGX, percentages and dates, and make a sheet easy to read',
        'Sort and filter a list and highlight important values automatically',
        'Write formulas and use SUM, AVERAGE, IF, COUNT and text and date functions',
        'Use relative and absolute cell references and fix common formula errors',
        'Look up values, summarise data with SUMIF and a PivotTable, and draw clear charts',
        'Build, print, protect and share a budget, a mark sheet and a stock sheet',
    ],
    'requirements' => ['A computer with Microsoft Excel 2019 or Microsoft 365 (Excel Online in a browser also works for most lessons)', 'No experience needed'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Microsoft Support: Excel help and training', 'url' => 'https://support.microsoft.com/excel'],
        ['title' => 'Microsoft Support: Excel functions (by category)', 'url' => 'https://support.microsoft.com/excel'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: Getting started with Excel',
            'summary' => 'What a spreadsheet is, how Excel is laid out, and how to enter, select, save and fill data.',
            'lessons' => [
                [
                    'title'   => 'What is a spreadsheet?',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what a spreadsheet is, name the main parts of an Excel file, and open a blank workbook. This matters because almost every job that handles money, marks, stock or lists of people uses spreadsheets.</p>

<h3>A spreadsheet is a grid for numbers and words</h3>
<p>A <strong>spreadsheet</strong> is a large grid of small boxes where you type information. Unlike a table in a Word document, a spreadsheet can calculate. If you change one number, every total that depends on it updates by itself. Think of a shopkeeper's exercise book where the totals rewrite themselves.</p>

<h3>The parts you must know</h3>
<table>
<thead><tr><th>Word</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td><strong>Workbook</strong></td><td>The whole Excel file you save (for example <code>Budget.xlsx</code>). A workbook holds one or more worksheets.</td></tr>
<tr><td><strong>Worksheet</strong></td><td>One page of the grid inside the workbook. Its name appears on a tab at the bottom (Sheet1, Sheet2...).</td></tr>
<tr><td><strong>Column</strong></td><td>A vertical line of boxes. Columns are named with letters: A, B, C...</td></tr>
<tr><td><strong>Row</strong></td><td>A horizontal line of boxes. Rows are named with numbers: 1, 2, 3...</td></tr>
<tr><td><strong>Cell</strong></td><td>One box where a row and a column cross. Its address joins the column letter and row number, for example <code>B3</code>.</td></tr>
<tr><td><strong>Active cell</strong></td><td>The cell that is selected now. It has a thick green border. Whatever you type goes into it.</td></tr>
</tbody>
</table>

<h3>What people use Excel for</h3>
<ul>
<li>Lists: names, phone numbers, members of a savings group.</li>
<li>Calculations: totals, averages, loan interest, profit.</li>
<li>Records: stock in a shop, fees paid at a school, attendance.</li>
<li>Charts: a picture of monthly sales that anyone can understand.</li>
</ul>

<h3>Opening Excel</h3>
<ol>
<li>Click the <strong>Start</strong> button (or press the Windows key) and type <code>Excel</code>.</li>
<li>Click <strong>Excel</strong> in the results.</li>
<li>On the start screen choose <strong>Blank workbook</strong>.</li>
</ol>
<p>You now see an empty grid. If you use Excel Online, sign in with a Microsoft account, then choose <strong>New blank workbook</strong>. On a Mac, open Excel from the Launchpad.</p>

<h3>Try it yourself</h3>
<ol>
<li>Open a blank workbook.</li>
<li>Click the cell where column D crosses row 5. Look at the <strong>Name Box</strong> (top left, above the grid). It should say <code>D5</code>.</li>
<li>Type your first name and press <strong>Enter</strong>. The active cell moves down one row.</li>
<li>Count how many sheet tabs are at the bottom of the window.</li>
<li>Close Excel without saving (choose <strong>Don't Save</strong> when asked).</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A workbook is the file; a worksheet is one page inside it.</li>
<li>A cell address is the column letter followed by the row number, such as <code>B3</code>.</li>
<li>A spreadsheet recalculates automatically when numbers change.</li>
<li>The Name Box shows the address of the active cell.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The Excel window and moving around',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn the parts of the Excel window and the fastest ways to move around a large sheet. Knowing where things are saves a lot of time, and moving with the keyboard is much quicker than scrolling.</p>

<h3>The parts of the window</h3>
<ul>
<li><strong>Title bar</strong>: the file name at the very top.</li>
<li><strong>Ribbon</strong>: the strip of tabs (<strong>Home</strong>, <strong>Insert</strong>, <strong>Page Layout</strong>, <strong>Formulas</strong>, <strong>Data</strong>, <strong>Review</strong>, <strong>View</strong>) and the buttons under each tab.</li>
<li><strong>Name Box</strong>: shows the address of the active cell. You can also type an address here to jump to it.</li>
<li><strong>Formula bar</strong>: shows what is really inside the active cell. A cell may display <code>150</code> while the formula bar shows <code>=100+50</code>.</li>
<li><strong>Column letters and row numbers</strong>: the labels on the top and left edges.</li>
<li><strong>Sheet tabs</strong>: at the bottom. Click the <strong>+</strong> button to add a sheet.</li>
<li><strong>Status bar</strong>: the bottom strip. If you select several numbers it shows their sum and average.</li>
<li><strong>Zoom slider</strong>: bottom right. Drag it to make the sheet bigger or smaller.</li>
</ul>

<h3>Moving with the mouse</h3>
<p>Click any cell to make it active. Use the scroll wheel to move up and down. Hold <strong>Shift</strong> while scrolling to move sideways.</p>

<h3>Moving with the keyboard</h3>
<table>
<thead><tr><th>Press</th><th>What happens</th></tr></thead>
<tbody>
<tr><td>Arrow keys</td><td>Move one cell in that direction</td></tr>
<tr><td>Tab</td><td>Move one cell right</td></tr>
<tr><td>Enter</td><td>Move one cell down</td></tr>
<tr><td>Ctrl+Home (Mac: Fn+Control+Left arrow)</td><td>Jump to cell A1</td></tr>
<tr><td>Ctrl+End (Mac: Fn+Control+Right arrow)</td><td>Jump to the last cell that contains data</td></tr>
<tr><td>Ctrl+Arrow key (Mac: Command+Arrow key)</td><td>Jump to the edge of the block of data you are in</td></tr>
<tr><td>Page Down / Page Up</td><td>Move one screen down or up</td></tr>
<tr><td>Ctrl+G or F5 (Mac: Control+G)</td><td>Open "Go To" and type an address</td></tr>
</tbody>
</table>
<p>The Ctrl+Arrow trick is the most useful. If you have 2,000 names in column A, click the first one and press <strong>Ctrl+Down arrow</strong> to reach the last in a blink.</p>

<h3>Working with sheets</h3>
<ol>
<li>Click a tab to open that sheet.</li>
<li>Double-click a tab name to rename it, type a new name (for example <code>January</code>) and press Enter.</li>
<li>Right-click a tab to see options such as <strong>Delete</strong>, <strong>Move or Copy</strong> and <strong>Tab Color</strong>.</li>
</ol>

<h3>Try it yourself</h3>
<ol>
<li>Open a blank workbook and click cell A1.</li>
<li>Type <code>Z100</code> into the Name Box and press Enter. Notice where you land.</li>
<li>Press Ctrl+Home to return to A1.</li>
<li>Rename Sheet1 to <code>Practice</code> and colour its tab green.</li>
<li>Drag the zoom slider to 150 percent, then back to 100 percent.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>The formula bar shows the real contents of a cell; the cell shows the result.</li>
<li>The Name Box lets you jump to any address.</li>
<li>Ctrl+Arrow and Ctrl+Home are the quickest ways to move in big sheets.</li>
<li>Rename sheet tabs so that you and others can find things.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Entering, editing and selecting data',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>You will learn how to type data into cells, correct mistakes, and select cells so you can work on many at once. Almost everything you do in Excel starts with these skills.</p>

<h3>Typing data</h3>
<p>Click a cell and type. When you finish, press <strong>Enter</strong> (go down) or <strong>Tab</strong> (go right). Press <strong>Esc</strong> to cancel what you are typing.</p>
<p>Excel treats what you type in three ways:</p>
<ul>
<li><strong>Text</strong> (labels such as <code>Sugar</code>) is left-aligned.</li>
<li><strong>Numbers</strong> (such as <code>2500</code>) are right-aligned.</li>
<li><strong>Dates</strong> (such as <code>15/03/2025</code>) are also right-aligned, because Excel stores a date as a number.</li>
</ul>
<p>Do not type the letter <code>O</code> instead of zero, or put spaces and "UGX" inside a number you want to calculate with. Type <code>2500</code> and format it later.</p>

<h3>Editing a cell</h3>
<ol>
<li>To replace everything: click the cell and type. The old content disappears.</li>
<li>To change part of it: double-click the cell, or click it and press <strong>F2</strong>, then use the arrow keys and Backspace.</li>
<li>To clear a cell: click it and press <strong>Delete</strong>.</li>
</ol>
<p>Made a mistake? Press <strong>Ctrl+Z</strong> (Mac: Command+Z) to undo. Press <strong>Ctrl+Y</strong> (Mac: Command+Y) to redo.</p>

<h3>Selecting cells</h3>
<table>
<thead><tr><th>To select</th><th>Do this</th></tr></thead>
<tbody>
<tr><td>One cell</td><td>Click it</td></tr>
<tr><td>A block of cells (a range)</td><td>Click the first cell and drag, or click the first and Shift+click the last</td></tr>
<tr><td>A whole column or row</td><td>Click its letter or number</td></tr>
<tr><td>Cells that are not next to each other</td><td>Hold Ctrl (Mac: Command) and click or drag each one</td></tr>
<tr><td>The entire sheet</td><td>Click the small triangle at the top-left corner, or press Ctrl+A (Mac: Command+A)</td></tr>
<tr><td>A block of data up to its edge</td><td>Shift+Ctrl+Arrow key</td></tr>
</tbody>
</table>
<p>A range is written with a colon. <code>B2:B6</code> means "from B2 down to B6". <code>A1:C3</code> means a block three columns wide and three rows tall.</p>

<h3>Cut, copy and paste</h3>
<p>Select cells, press <strong>Ctrl+C</strong> to copy (or <strong>Ctrl+X</strong> to cut), click where you want them, and press <strong>Ctrl+V</strong>. On a Mac use Command instead of Ctrl. Copied cells show a moving dotted border; press Esc to remove it.</p>

<h3>Try it yourself</h3>
<ol>
<li>In A1 type <code>Item</code>, in B1 type <code>Price (UGX)</code>.</li>
<li>Below them enter five items: Sugar 4500, Rice 5000, Beans 3500, Soap 2000, Salt 1000.</li>
<li>Change the price of Rice to 5200 by clicking the cell and typing.</li>
<li>Select B2:B6 by dragging, and read the Sum on the status bar.</li>
<li>Copy A1:B6 and paste it into D1.</li>
</ol>

<h3>Common mistakes</h3>
<ul>
<li>Typing numbers with letters or spaces, which turns them into text that cannot be added.</li>
<li>Typing over a cell when you only wanted to fix one character; use F2 instead.</li>
</ul>

<h3>Key points</h3>
<ul>
<li>Enter moves down, Tab moves right, Esc cancels.</li>
<li>F2 edits inside a cell; Delete clears it.</li>
<li>A range such as <code>B2:B6</code> uses a colon.</li>
<li>Ctrl+Z undoes your last action.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Saving, AutoFill and Flash Fill',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>Here you will learn to save your workbook where you can find it again, and to let Excel fill in repeating data for you. AutoFill can turn an hour of typing into five seconds.</p>

<h3>Saving your work</h3>
<ol>
<li>Press <strong>Ctrl+S</strong> (Mac: Command+S).</li>
<li>The first time, choose a place (for example <strong>Documents</strong> or <strong>OneDrive</strong>), type a clear file name such as <code>Shop stock 2025</code>, and click <strong>Save</strong>.</li>
<li>After that, press Ctrl+S often. It saves quietly under the same name.</li>
</ol>
<p>Use <strong>File</strong> then <strong>Save As</strong> to make a copy under a new name. Excel files end in <code>.xlsx</code>. To send a plain list to another program, choose the type <strong>CSV (Comma delimited)</strong>. Excel can also recover an unsaved file after a crash (<strong>File</strong>, <strong>Open</strong>, <strong>Recover Unsaved Workbooks</strong>), but never rely on it. Keep a second copy on a flash disk or in the cloud.</p>

<h3>AutoFill: the fill handle</h3>
<p>When you select a cell, a tiny square appears at its bottom-right corner. This is the <strong>fill handle</strong>. Drag it down or across to copy or continue a pattern.</p>
<ul>
<li>Type <code>Monday</code>, drag the handle down: Excel continues Tuesday, Wednesday...</li>
<li>Type <code>Jan</code>, drag: Feb, Mar...</li>
<li>Type <code>1</code> and <code>2</code> in two cells, select both, drag: 3, 4, 5... Excel copies the step between your two numbers.</li>
<li>Type <code>10</code> and <code>20</code>, select both, drag: 30, 40, 50.</li>
</ul>
<p>After you drop, a small <strong>Auto Fill Options</strong> button appears. Click it to choose <strong>Copy Cells</strong> (repeat the same value), <strong>Fill Series</strong> (continue the pattern) or <strong>Fill Formatting Only</strong>. Double-clicking the fill handle fills down as far as the data next to it goes.</p>
<p>You can also use the keyboard: select the cells including the one that holds the value and press <strong>Ctrl+D</strong> to fill down, or <strong>Ctrl+R</strong> to fill right.</p>

<h3>Flash Fill: Excel learns your pattern</h3>
<p>Flash Fill (Excel 2013 and later) spots a pattern in what you type and finishes the column. Suppose column A holds full names like <code>Akello Grace</code> and you want first names in column B.</p>
<ol>
<li>In B2 type <code>Akello</code>.</li>
<li>In B3 start typing the first name of the next person. Excel shows a grey preview for the rest.</li>
<li>Press <strong>Enter</strong> to accept, or use <strong>Data</strong>, <strong>Flash Fill</strong>, or press <strong>Ctrl+E</strong> (Mac: Command+E).</li>
</ol>
<p>It works for splitting names, joining parts, and tidying phone numbers. Always check the results, because Flash Fill guesses.</p>

<h3>Try it yourself</h3>
<ol>
<li>In A1 type <code>Week 1</code> and drag the fill handle right to make Week 4.</li>
<li>In A3 type <code>1</code> and in A4 type <code>2</code>, select both, and fill down to A12.</li>
<li>In C1 type <code>Okello Peter</code>, in C2 type <code>Auma Beatrice</code>. In D1 type <code>Okello</code> and try Ctrl+E in D2.</li>
<li>Save the workbook as <code>My first workbook</code> in your Documents folder.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Save with Ctrl+S early and often, and keep a backup copy.</li>
<li>The fill handle copies values or continues patterns such as days, months and numbers.</li>
<li>Flash Fill (Ctrl+E) learns from one or two examples; check its answers.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the address of the cell where column C crosses row 7?
- 7C
* C7
- C:7
- R7C
= A cell address is the column letter followed by the row number, so C7.

Q: What is the difference between a workbook and a worksheet?
- They are two names for the same thing
- A worksheet contains many workbooks
* A workbook is the whole file and it contains one or more worksheets
- A workbook is only used for text
= The workbook is the saved file (.xlsx). Each page inside it is a worksheet.

Q: Where do you look to see what is really inside the active cell, such as a formula?
- The status bar
- The sheet tab
* The formula bar
- The Name Box
= The formula bar shows the true contents of the active cell. The Name Box shows only its address.

Q: Which shortcut jumps straight to cell A1?
- Ctrl+End
- Ctrl+A
* Ctrl+Home
- Tab
= Ctrl+Home takes you to A1. Ctrl+End goes to the last used cell.

Q: What does the range B2:B6 mean?
* The cells from B2 down to B6
- Only cells B2 and B6
- Column B and row 6
- Everything between column B and column 6
= The colon means "from ... to ...", so B2:B6 is a block of five cells in column B.

Q: You typed 5 and 10 in two cells, selected both, and dragged the fill handle down. What will Excel most likely continue with?
- 5, 10, 5, 10
- 10, 10, 10
* 15, 20, 25
- 6, 7, 8
= Excel copies the step between the two numbers (5), so the series continues 15, 20, 25.

Q: Which shortcut starts Flash Fill? (Windows)
- Ctrl+F
* Ctrl+E
- Ctrl+L
= Ctrl+E fills a column after Excel learns the pattern from your example.

Q: You typed a wrong price in a cell. Which is the best way to change just one digit?
- Delete the whole sheet
* Click the cell and press F2 to edit inside it
- Press Ctrl+A and retype everything
- Insert a new row
= F2 (or double-clicking) lets you edit inside the cell without retyping everything.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Formatting and organising data',
            'summary' => 'Make a sheet clear and professional: number formats, fonts, borders, column widths, sorting, filtering, tables and colour rules.',
            'lessons' => [
                [
                    'title'   => 'Number formats: UGX, percentages and dates',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn to change how numbers look without changing their value. This is how you show money as UGX, a fraction as a percentage, and a date in a style people understand. Good number formats make a sheet readable at a glance.</p>

<h3>Formatting changes the look, not the number</h3>
<p>If a cell holds <code>1500000</code>, you can display it as <code>1,500,000</code> or <code>UGX 1,500,000</code>. The number underneath is still 1500000, and formulas use that. Check the formula bar to see the real value.</p>

<h3>Where the commands are</h3>
<p>Select the cells, then use the <strong>Home</strong> tab, in the <strong>Number</strong> group. The drop-down box shows <strong>General</strong> by default. You can also press <strong>Ctrl+1</strong> (Mac: Command+1) to open <strong>Format Cells</strong>, which has every option.</p>

<h3>Common formats</h3>
<table>
<thead><tr><th>Format</th><th>Example result</th><th>Use for</th></tr></thead>
<tbody>
<tr><td>Number with comma style (button with a comma)</td><td>1,500,000</td><td>Big numbers</td></tr>
<tr><td>Currency</td><td>UGX 1,500,000</td><td>Money</td></tr>
<tr><td>Percentage</td><td>15%</td><td>Rates, shares, marks out of 100</td></tr>
<tr><td>Short Date / Long Date</td><td>15/03/2025 or 15 March 2025</td><td>Dates</td></tr>
<tr><td>Text</td><td>0772123456</td><td>Phone numbers and codes</td></tr>
</tbody>
</table>

<h3>Showing UGX</h3>
<ol>
<li>Select the money cells.</li>
<li>Press <strong>Ctrl+1</strong> and choose the <strong>Number</strong> tab.</li>
<li>Choose <strong>Currency</strong> (or <strong>Accounting</strong>), set <strong>Decimal places</strong> to 0, and in <strong>Symbol</strong> pick <strong>UGX</strong> if it is in the list.</li>
<li>If UGX is not there, choose <strong>Custom</strong> and type this in the Type box: <code>"UGX" #,##0</code></li>
<li>Click <strong>OK</strong>.</li>
</ol>
<p>Use the <strong>Increase Decimal</strong> and <strong>Decrease Decimal</strong> buttons to show more or fewer digits after the point. Uganda shillings are normally shown with no decimals.</p>

<h3>Percentages</h3>
<p>Excel stores 15 percent as <code>0.15</code>. If you type <code>0.15</code> and click the <strong>%</strong> button (Ctrl+Shift+5), you see 15%. If you format an empty cell as a percentage first and then type <code>15</code>, Excel treats it as 15%.</p>

<h3>Dates</h3>
<p>Excel stores a date as a counting number, which lets you subtract one date from another. If a date shows as a string of hash marks (<code>#####</code>), the column is too narrow; widen it. To change the style, open <strong>Format Cells</strong>, choose <strong>Date</strong> and pick one. Note that the day-month-year order depends on your computer's regional settings.</p>

<h3>Phone numbers</h3>
<p>A number like <code>0772123456</code> loses its first zero because Excel treats it as a number. Format the cells as <strong>Text</strong> before typing, or type an apostrophe first: <code>'0772123456</code>.</p>

<h3>Try it yourself</h3>
<ol>
<li>Type 1500000, 250000 and 75000 in A1:A3 and format them as UGX with no decimals.</li>
<li>In B1 type 0.18 and format it as a percentage.</li>
<li>In C1 type today's date and change it to Long Date.</li>
<li>In D1 type a phone number starting with 0 so that the zero stays.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Formats change appearance, not the stored value.</li>
<li>Ctrl+1 opens Format Cells; use Custom for patterns such as <code>"UGX" #,##0</code>.</li>
<li>Percentages are stored as decimals: 15% is 0.15.</li>
<li>Use Text format for phone numbers and codes with leading zeros.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Fonts, borders, colours and cell size',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>This lesson shows how to make a sheet neat: choose fonts, add borders and fill colours, resize columns and rows, wrap long text, merge cells and freeze headings. A tidy sheet is easier to read, and people trust it more.</p>

<h3>Fonts and alignment</h3>
<p>On the <strong>Home</strong> tab, the <strong>Font</strong> group lets you change the typeface, size, <strong>Bold</strong> (Ctrl+B, Mac: Command+B), <em>Italic</em> (Ctrl+I) and colour. Use the <strong>Alignment</strong> group to align left, centre or right, or to align cells to the top, middle or bottom. Keep to one or two fonts and make only the headings bold.</p>

<h3>Borders and fill colours</h3>
<ol>
<li>Select the cells.</li>
<li>Click the arrow beside the <strong>Borders</strong> button (in the Font group) and choose <strong>All Borders</strong> for a grid, or <strong>Thick Outside Borders</strong> to frame a block.</li>
<li>Click the paint bucket (<strong>Fill Color</strong>) to colour the background. Choose a light colour for headings so that black text stays readable.</li>
</ol>
<p>The faint gridlines you see on screen do not print. Borders do.</p>

<h3>Column width and row height</h3>
<ul>
<li>Move the pointer to the line between two column letters until it becomes a double arrow, then drag.</li>
<li>Double-click that line to <strong>AutoFit</strong>, which sizes the column to the longest entry.</li>
<li>For exact sizes, select the columns, right-click a letter and choose <strong>Column Width</strong>. Rows work the same way with <strong>Row Height</strong>.</li>
</ul>
<p>If a cell shows <code>#####</code>, the column is too narrow for the number.</p>

<h3>Wrap text and merge</h3>
<p><strong>Wrap Text</strong> (Home tab, Alignment group) keeps long text inside the cell by making the row taller. <strong>Merge &amp; Center</strong> joins several cells into one, which is useful for a title across the top of a table. Use merging only for titles. It can cause problems when you sort or filter. A safer choice is <strong>Center Across Selection</strong> in the Format Cells Alignment tab.</p>

<h3>Freeze panes</h3>
<p>When a list is long, the headings scroll away. To keep them visible:</p>
<ol>
<li>Click the cell just below the heading row and to the right of any column you want to keep (for row 1 only, click A2).</li>
<li>Choose <strong>View</strong>, then <strong>Freeze Panes</strong>, then <strong>Freeze Panes</strong>.</li>
</ol>
<p>The quick options <strong>Freeze Top Row</strong> and <strong>Freeze First Column</strong> do the common cases. Use <strong>Unfreeze Panes</strong> to undo.</p>

<h3>Format Painter</h3>
<p>Select a formatted cell, click the paintbrush (<strong>Format Painter</strong>) on the Home tab, then click or drag across the cells that should look the same. Double-click the brush to paint several times.</p>

<h3>Try it yourself</h3>
<ol>
<li>Use your item and price list from earlier, or type a new one with 10 rows.</li>
<li>Make the headings bold with a light green fill, and add All Borders to the whole table.</li>
<li>AutoFit the columns by double-clicking the column boundaries.</li>
<li>Type a long sentence in a cell and turn on Wrap Text.</li>
<li>Freeze the top row and scroll down to check it stays.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use bold, fill colour and borders sparingly to guide the eye.</li>
<li>Double-click a column border to AutoFit it.</li>
<li>Freeze Panes keeps headings on screen while you scroll.</li>
<li>Merge only titles; avoid merging inside data.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Sorting, filtering and tables',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>You will learn to put a list in order, show only the rows you need, and turn a list into an Excel Table. These tools let you answer questions such as "who owes fees?" or "which goods are almost finished?" in seconds.</p>

<h3>Prepare your data</h3>
<p>Sorting and filtering work best on a clean list: one row of headings at the top, then one record per row, with no blank rows or columns in the middle. Each column holds one kind of information.</p>

<h3>Sorting</h3>
<ol>
<li>Click any cell inside the column you want to sort by (do not select just one column, or the other columns will stay behind).</li>
<li>On the <strong>Data</strong> tab click <strong>Sort A to Z</strong> (smallest or alphabetical first) or <strong>Sort Z to A</strong>.</li>
</ol>
<p>Excel moves the whole row together. For several levels, click <strong>Sort</strong> on the Data tab, then <strong>Add Level</strong>. For example, sort by <em>Class</em> first, then by <em>Name</em>. If you make a mistake, press Ctrl+Z at once.</p>

<h3>Filtering</h3>
<ol>
<li>Click inside the list.</li>
<li>Choose <strong>Data</strong>, then <strong>Filter</strong> (or press <strong>Ctrl+Shift+L</strong>, Mac: Command+Shift+F). Small arrows appear in each heading.</li>
<li>Click an arrow. Tick or untick values, or use <strong>Number Filters</strong> (for example "Greater Than") or <strong>Text Filters</strong> (for example "Contains").</li>
<li>Click <strong>OK</strong>. Rows that do not match are hidden, not deleted. The row numbers turn blue.</li>
</ol>
<p>To bring everything back, click <strong>Clear</strong> on the Data tab, or press the filter button again to switch filters off.</p>

<h3>Excel Tables</h3>
<p>A <strong>Table</strong> is a list that Excel manages for you. It adds filter arrows, banded rows and automatic growth when you type below it.</p>
<ol>
<li>Click inside your list.</li>
<li>Press <strong>Ctrl+T</strong> (Mac: Command+T), or choose <strong>Insert</strong>, <strong>Table</strong>.</li>
<li>Tick <strong>My table has headers</strong> and click <strong>OK</strong>.</li>
<li>Use the <strong>Table Design</strong> tab to choose a style and to tick <strong>Total Row</strong>, which adds a totals row with a drop-down of calculations.</li>
</ol>
<p>Give the table a name in the Table Name box at the left of the Table Design tab, for example <code>Stock</code>. Tables also keep formulas consistent: a formula written once fills the whole column.</p>

<h3>Try it yourself</h3>
<ol>
<li>Type a list of 8 students with columns <code>Name</code>, <code>Class</code> and <code>Mark</code>.</li>
<li>Sort by Mark from largest to smallest.</li>
<li>Turn on the filter and show only one class.</li>
<li>Clear the filter, then convert the list to a Table with Ctrl+T.</li>
<li>Add a Total Row and read the total of the Mark column.</li>
</ol>

<h3>Common mistakes</h3>
<ul>
<li>Selecting only one column before sorting, which separates names from their marks.</li>
<li>Leaving blank rows inside the list, so Excel thinks the list ends early.</li>
</ul>

<h3>Key points</h3>
<ul>
<li>Sort a whole list by clicking one cell in the column, not selecting the column.</li>
<li>Filters hide rows; they do not delete them.</li>
<li>Ctrl+T makes a Table that grows and styles itself.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Conditional formatting and cell styles',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>This lesson teaches you to make Excel colour cells automatically based on their values, for example red for marks below 50 or green for goods well in stock. You will also learn cell styles for fast, consistent formatting. Colour rules help people spot problems immediately.</p>

<h3>Conditional formatting</h3>
<p><strong>Conditional formatting</strong> changes how a cell looks only when a rule is true. When the value changes, the colour changes with it.</p>

<h3>Highlight cells that meet a rule</h3>
<ol>
<li>Select the cells, for example the marks in C2:C30.</li>
<li>On the <strong>Home</strong> tab click <strong>Conditional Formatting</strong>.</li>
<li>Choose <strong>Highlight Cells Rules</strong>, then <strong>Less Than</strong>.</li>
<li>Type <code>50</code> and choose a format such as <strong>Light Red Fill with Dark Red Text</strong>.</li>
<li>Click <strong>OK</strong>.</li>
</ol>
<p>Other useful choices in the same menu are <strong>Greater Than</strong>, <strong>Between</strong>, <strong>Equal To</strong>, <strong>Text that Contains</strong>, <strong>A Date Occurring</strong> and <strong>Duplicate Values</strong>.</p>

<h3>Top and bottom rules</h3>
<p><strong>Top/Bottom Rules</strong> highlight the top 10 items, bottom 10 percent, or values above average. Useful for finding your best-selling goods.</p>

<h3>Data bars, colour scales and icon sets</h3>
<ul>
<li><strong>Data Bars</strong> draw a bar inside the cell, longer for bigger numbers.</li>
<li><strong>Color Scales</strong> shade cells from one colour to another, such as red (low) to green (high).</li>
<li><strong>Icon Sets</strong> add small arrows, traffic lights or ticks.</li>
</ul>
<p>Use them lightly. Too many colours confuse readers. Remember that some people cannot tell red from green, so combine colour with words or icons where it matters.</p>

<h3>Managing and removing rules</h3>
<p>Choose <strong>Conditional Formatting</strong>, then <strong>Manage Rules</strong> to see, edit or delete rules. To remove all rules from a selection choose <strong>Clear Rules</strong>, then <strong>Clear Rules from Selected Cells</strong>.</p>

<h3>Cell styles</h3>
<p>A <strong>cell style</strong> is a saved set of fonts, colours and number formats. On the Home tab, open <strong>Cell Styles</strong> and click one, such as <strong>Title</strong>, <strong>Heading 1</strong>, <strong>Good</strong>, <strong>Bad</strong>, <strong>Input</strong> or <strong>Currency [0]</strong>. Using styles keeps a whole workbook consistent, and if you change a style, every cell using it changes.</p>

<h3>Try it yourself</h3>
<ol>
<li>Type ten student marks between 20 and 95 in a column.</li>
<li>Add a rule that colours marks below 50 light red.</li>
<li>Add a second rule that colours marks of 80 or more light green (use Greater Than with 79, or use Between).</li>
<li>Change one mark from 45 to 65 and watch the colour change.</li>
<li>Apply the <strong>Title</strong> cell style to a heading you type above the list.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Conditional formatting colours cells automatically when a rule is true.</li>
<li>Manage Rules lets you edit or delete rules later.</li>
<li>Data bars and colour scales show size at a glance; use them sparingly.</li>
<li>Cell styles give quick, consistent formatting.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A cell holds 0.25 and you click the Percentage button. What does the cell show, and what does Excel store?
- Shows 0.25%; stores 25
* Shows 25%; stores 0.25
- Shows 25%; stores 25
- Shows 2.5%; stores 0.025
= Excel stores percentages as decimals. 0.25 is displayed as 25%.

Q: A phone number 0772123456 loses its first zero when typed. What is the best fix?
- Type it with spaces
- Make the font bigger
* Format the cell as Text before typing, or type an apostrophe first
- Put it in bold
= Excel treats a plain number as a number and drops leading zeros. Text format keeps them.

Q: What does a cell full of ##### usually mean?
- The formula is wrong
* The column is too narrow to show the value
- The file is damaged
- The number is negative
= Widen the column (for example by double-clicking its border) and the value appears.

Q: Which shortcut opens the Format Cells box on Windows?
- Ctrl+F
* Ctrl+1
- Ctrl+T
= Ctrl+1 (Mac: Command+1) opens Format Cells with all number and style options.

Q: Why should you click inside a list instead of selecting just one column before sorting?
- It is faster to click
* Selecting one column can sort that column alone and separate it from the rest of each row
- Excel cannot sort selected columns
- Clicking changes the font
= Sorting must move whole rows together, so names stay with their marks.

Q: Which are true about filters? (choose all that apply)
* Filtering hides rows that do not match
* Clear on the Data tab brings back all rows
- Filtering permanently deletes the unmatched rows
= Filters only hide rows; the data stays in the sheet.

Q: You want marks below 50 to turn red automatically. Which tool do you use?
- Fill Color
- Merge and Center
* Conditional Formatting
- Freeze Panes
= Conditional formatting applies a colour only when the rule is true and updates when values change.

Q: What does Freeze Panes do?
- Stops anyone editing the sheet
* Keeps chosen rows or columns visible while you scroll
- Locks the file with a password
- Deletes empty rows
= It is used to keep headings on screen in long lists.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Formulas and functions',
            'summary' => 'Write formulas, understand cell references, use the everyday functions and fix common errors.',
            'lessons' => [
                [
                    'title'   => 'Your first formulas and cell references',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a formula, copy it to many rows, and choose between relative and absolute cell references. This is the heart of Excel: a formula lets the sheet do the arithmetic for you and keep it correct when the numbers change.</p>

<h3>Formula or function?</h3>
<p>A <strong>formula</strong> is a calculation you type that always starts with an equals sign <code>=</code>. A <strong>function</strong> is a ready-made calculation with a name, such as <code>SUM</code>, that you can use inside a formula. For example, <code>=A1+A2</code> is a formula; <code>=SUM(A1:A10)</code> is a formula that uses the SUM function.</p>

<h3>Operators</h3>
<table>
<thead><tr><th>Symbol</th><th>Meaning</th><th>Example</th><th>Result</th></tr></thead>
<tbody>
<tr><td><code>+</code></td><td>Add</td><td><code>=10+5</code></td><td>15</td></tr>
<tr><td><code>-</code></td><td>Subtract</td><td><code>=10-5</code></td><td>5</td></tr>
<tr><td><code>*</code></td><td>Multiply</td><td><code>=10*5</code></td><td>50</td></tr>
<tr><td><code>/</code></td><td>Divide</td><td><code>=10/5</code></td><td>2</td></tr>
<tr><td><code>^</code></td><td>Power</td><td><code>=2^3</code></td><td>8</td></tr>
</tbody>
</table>
<p>Excel follows the usual order: brackets first, then powers, then multiply and divide, then add and subtract. <code>=2+3*4</code> gives 14, but <code>=(2+3)*4</code> gives 20.</p>

<h3>Use cell addresses, not numbers</h3>
<p>Suppose B2 holds a price (4500) and C2 holds a quantity (3). In D2 type:</p>
<pre><code>=B2*C2</code></pre>
<p>Press Enter to see 13500. You can build the formula by clicking cells instead of typing their addresses: type <code>=</code>, click B2, type <code>*</code>, click C2, press Enter. If the price later changes, the total updates by itself.</p>

<h3>Copying a formula: relative references</h3>
<p>Click D2 and double-click its fill handle. Excel copies the formula down, and changes the row numbers as it goes: D3 becomes <code>=B3*C3</code>, D4 becomes <code>=B4*C4</code>. This is a <strong>relative reference</strong>. It is relative to where the formula sits.</p>

<h3>Absolute references with the dollar sign</h3>
<p>Sometimes one cell must stay fixed, such as a tax rate or exchange rate. Suppose F1 holds 18% and you want each price in column B multiplied by it. Write:</p>
<pre><code>=B2*$F$1</code></pre>
<p>The dollar signs lock the column and row, so when you copy the formula down it still points at F1. This is an <strong>absolute reference</strong>. While editing a formula, press <strong>F4</strong> (Mac: Command+T or Fn+F4) to switch between <code>F1</code>, <code>$F$1</code>, <code>F$1</code> and <code>$F1</code>.</p>
<table>
<thead><tr><th>Type</th><th>Looks like</th><th>When copied</th></tr></thead>
<tbody>
<tr><td>Relative</td><td><code>B2</code></td><td>Changes both column and row</td></tr>
<tr><td>Absolute</td><td><code>$B$2</code></td><td>Never changes</td></tr>
<tr><td>Mixed</td><td><code>$B2</code> or <code>B$2</code></td><td>One part is locked</td></tr>
</tbody>
</table>

<h3>Try it yourself</h3>
<ol>
<li>In A1:A3 type Sugar, Rice, Beans; in B1:B3 prices 4500, 5000, 3500; in C1:C3 quantities 3, 2, 4.</li>
<li>In D1 write <code>=B1*C1</code> and fill it down to D3.</li>
<li>In F1 type 0.18. In E1 write <code>=D1*$F$1</code> and fill down.</li>
<li>Change a quantity and watch the totals update.</li>
<li>Click E3 and read the formula in the formula bar.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Every formula starts with <code>=</code>.</li>
<li>Use cell addresses so the answer updates when data changes.</li>
<li>Relative references change when copied; absolute ones (with $) do not.</li>
<li>F4 adds the dollar signs for you.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Everyday functions: SUM, AVERAGE, COUNT, MIN, MAX, ROUND',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>In this lesson you will meet the functions you will use almost every day. They add, average, count and round numbers across whole columns in one step. Typing <code>=B2+B3+B4+...</code> for 200 rows would be slow and risky; a function is both quicker and safer.</p>

<h3>The shape of a function</h3>
<p>Every function has a name, then brackets holding its <strong>arguments</strong> (the information it needs), separated by commas:</p>
<pre><code>=FUNCTIONNAME(argument1, argument2)</code></pre>
<p>Ranges such as <code>B2:B20</code> are the most common argument.</p>

<h3>The main five</h3>
<table>
<thead><tr><th>Function</th><th>What it does</th><th>Example</th></tr></thead>
<tbody>
<tr><td><code>SUM</code></td><td>Adds numbers</td><td><code>=SUM(B2:B20)</code></td></tr>
<tr><td><code>AVERAGE</code></td><td>Finds the mean</td><td><code>=AVERAGE(B2:B20)</code></td></tr>
<tr><td><code>MIN</code></td><td>Smallest number</td><td><code>=MIN(B2:B20)</code></td></tr>
<tr><td><code>MAX</code></td><td>Largest number</td><td><code>=MAX(B2:B20)</code></td></tr>
<tr><td><code>COUNT</code></td><td>Counts cells holding numbers</td><td><code>=COUNT(B2:B20)</code></td></tr>
</tbody>
</table>
<p><code>COUNTA</code> counts all cells that are not empty (numbers and text). If a list of 30 names sits in column A, <code>=COUNTA(A2:A31)</code> gives 30. <code>COUNT</code> would give 0 because names are not numbers.</p>

<h3>AutoSum: the quickest way</h3>
<ol>
<li>Click the empty cell just below a column of numbers.</li>
<li>On the <strong>Home</strong> tab click <strong>AutoSum</strong> (the sigma symbol), or press <strong>Alt+=</strong> (Mac: Command+Shift+T).</li>
<li>Excel guesses the range and shows it with a dashed border. Press <strong>Enter</strong> if it is right.</li>
</ol>
<p>The small arrow beside AutoSum gives Average, Count Numbers, Max and Min.</p>

<h3>Typing a function</h3>
<p>Type <code>=SUM(</code> and Excel shows a tooltip. Select the cells with the mouse, type the closing bracket and press Enter. As you type a function's name, Excel lists suggestions; press <strong>Tab</strong> to accept one.</p>

<h3>ROUND</h3>
<p><code>ROUND(number, digits)</code> rounds a value. <code>=ROUND(3.14159,2)</code> gives 3.14. With <code>0</code> digits you get a whole number, and with <code>-3</code> you round to the nearest thousand: <code>=ROUND(12476,-3)</code> gives 12000. Note that formatting only hides decimals, while ROUND really changes the value. Use ROUND when you add rounded money amounts and the total must match.</p>

<h3>Several ranges</h3>
<p>Separate them with commas: <code>=SUM(B2:B10, D2:D10)</code>. You can mix a single cell and a range: <code>=SUM(B2:B10, 5000)</code>.</p>

<h3>Try it yourself</h3>
<ol>
<li>Type these sales in B1:B6: 120000, 85000, 240000, 60000, 175000, 90000.</li>
<li>In B7 use AutoSum to total them.</li>
<li>In B8 write <code>=AVERAGE(B1:B6)</code>, in B9 <code>=MAX(B1:B6)</code>, in B10 <code>=MIN(B1:B6)</code>.</li>
<li>In B11 write <code>=COUNT(B1:B6)</code>.</li>
<li>In B12 write <code>=ROUND(B8,-3)</code> to round the average to the nearest thousand.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>SUM, AVERAGE, MIN, MAX and COUNT work on a range such as <code>B2:B20</code>.</li>
<li>COUNT counts numbers; COUNTA counts anything non-empty.</li>
<li>Alt+= inserts AutoSum quickly.</li>
<li>ROUND changes the stored value; number formats only change the look.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Making decisions with IF, and text and date functions',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>This lesson lets your sheet decide and tidy text. You will use IF to give a result such as Pass or Fail, text functions to join and clean names, and date functions to find today's date and a person's age. These turn a plain table into a working tool.</p>

<h3>IF: choose between two answers</h3>
<pre><code>=IF(test, value_if_true, value_if_false)</code></pre>
<p>If a mark is in C2 and 50 or more is a pass:</p>
<pre><code>=IF(C2&gt;=50,"Pass","Fail")</code></pre>
<p>Text answers go inside quotation marks. Comparison signs are <code>=</code>, <code>&gt;</code>, <code>&lt;</code>, <code>&gt;=</code>, <code>&lt;=</code> and <code>&lt;&gt;</code> (not equal to). IF can also return numbers or calculations: <code>=IF(D2&lt;10,"Reorder","OK")</code> in a stock sheet.</p>

<h3>Nested IF: more than two answers</h3>
<p>Put another IF in the false part:</p>
<pre><code>=IF(C2&gt;=80,"A",IF(C2&gt;=60,"B",IF(C2&gt;=50,"C","F")))</code></pre>
<p>Excel checks each test in order and stops at the first true one, so always start with the highest value. Keep nesting to three or four levels; deeper formulas are hard to read.</p>

<h3>Joining and cleaning text</h3>
<table>
<thead><tr><th>Function</th><th>Example</th><th>Result</th></tr></thead>
<tbody>
<tr><td><code>CONCAT</code></td><td><code>=CONCAT(A2," ",B2)</code></td><td>First and last name joined with a space</td></tr>
<tr><td><code>TEXTJOIN</code></td><td><code>=TEXTJOIN(", ",TRUE,A2:C2)</code></td><td>Items joined with a comma, skipping blanks</td></tr>
<tr><td><code>LEFT</code></td><td><code>=LEFT(A2,3)</code></td><td>First 3 characters</td></tr>
<tr><td><code>RIGHT</code></td><td><code>=RIGHT(A2,4)</code></td><td>Last 4 characters</td></tr>
<tr><td><code>LEN</code></td><td><code>=LEN(A2)</code></td><td>Number of characters</td></tr>
<tr><td><code>TRIM</code></td><td><code>=TRIM(A2)</code></td><td>Removes extra spaces</td></tr>
<tr><td><code>UPPER</code></td><td><code>=UPPER(A2)</code></td><td>CAPITAL LETTERS (LOWER and PROPER also exist)</td></tr>
</tbody>
</table>
<p>The ampersand also joins text: <code>=A2&amp;" "&amp;B2</code>. CONCAT and TEXTJOIN exist in Excel 2019 and Microsoft 365 but not in older versions, where CONCATENATE or the ampersand is used.</p>

<h3>Date functions</h3>
<ul>
<li><code>=TODAY()</code> shows today's date and updates each day you open the file. It needs empty brackets.</li>
<li><code>=NOW()</code> shows the date and time.</li>
<li><code>=DATEDIF(start, end, "Y")</code> gives complete years between two dates, <code>"M"</code> gives months and <code>"D"</code> days. Age from a birth date in B2 is <code>=DATEDIF(B2,TODAY(),"Y")</code>. DATEDIF works but does not appear in the suggestion list, so type it fully.</li>
</ul>
<p>You can also subtract dates: <code>=D2-TODAY()</code> gives days left until a deadline (format the cell as Number).</p>

<h3>Try it yourself</h3>
<ol>
<li>Type five marks in column C, then in D write the nested IF above to give grades A, B, C or F; fill it down.</li>
<li>Type first names in A and surnames in B, then in C join them with <code>=A1&amp;" "&amp;B1</code>.</li>
<li>Put a birth date in E1 and write <code>=DATEDIF(E1,TODAY(),"Y")</code> in F1.</li>
<li>Type <code>  extra   spaces  </code> in a cell and clean it with TRIM in the next cell.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>IF returns one result when a test is true and another when false; text goes in quotation marks.</li>
<li>In a nested IF, test the highest value first.</li>
<li>TRIM, UPPER, LEFT, RIGHT and LEN clean and examine text.</li>
<li>TODAY and DATEDIF handle dates and ages.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Understanding and fixing formula errors',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>Every Excel user meets error messages. In this lesson you will learn what the common ones mean and how to fix them. Reading an error calmly is a skill: it usually tells you exactly where to look.</p>

<h3>The common errors</h3>
<table>
<thead><tr><th>Error</th><th>Meaning</th><th>Typical cause and fix</th></tr></thead>
<tbody>
<tr><td><code>#DIV/0!</code></td><td>Division by zero</td><td>The divisor cell is empty or 0. Fill it in, or write <code>=IF(B2=0,"",A2/B2)</code>.</td></tr>
<tr><td><code>#NAME?</code></td><td>Excel does not recognise a name</td><td>A function is misspelt (<code>=SUMM(...)</code>), or text is missing quotation marks. Check spelling.</td></tr>
<tr><td><code>#REF!</code></td><td>A reference is broken</td><td>You deleted a row, column or sheet that a formula used. Press Ctrl+Z, or rewrite the formula.</td></tr>
<tr><td><code>#VALUE!</code></td><td>Wrong type of data</td><td>You tried to calculate with text, such as <code>=A1+B1</code> where A1 holds "n/a". Fix the cell or exclude it.</td></tr>
<tr><td><code>#N/A</code></td><td>Value not found</td><td>A lookup found no match. Check the spelling and spaces of the item you are looking for.</td></tr>
<tr><td><code>#####</code></td><td>Not a true error</td><td>The column is too narrow. Widen it.</td></tr>
</tbody>
</table>

<h3>A step-by-step way to find the problem</h3>
<ol>
<li>Click the cell with the error and look at the formula in the formula bar.</li>
<li>Check each part: are the brackets paired? Are the commas in the right places? Is every function name spelt right?</li>
<li>Press <strong>F2</strong> to edit. Excel colours the cells the formula uses and draws coloured borders around them, so you can see what it points at.</li>
<li>Click the small yellow warning button beside the cell. It offers <strong>Show Calculation Steps</strong> or help.</li>
<li>Use <strong>Formulas</strong>, <strong>Evaluate Formula</strong> to step through the calculation one part at a time.</li>
</ol>

<h3>Other tools on the Formulas tab</h3>
<ul>
<li><strong>Trace Precedents</strong> draws arrows from the cells a formula uses.</li>
<li><strong>Trace Dependents</strong> shows the cells that use the active cell.</li>
<li><strong>Show Formulas</strong> (Ctrl+` , the key beside 1) displays every formula on the sheet instead of results. Press it again to switch back.</li>
<li><strong>Error Checking</strong> goes through the sheet looking for problems.</li>
</ul>

<h3>Hiding errors politely</h3>
<p>Wrap a formula in <code>IFERROR</code> to show something friendly:</p>
<pre><code>=IFERROR(A2/B2,"Check data")</code></pre>
<p>Use this carefully. It hides the problem as well as the message, so use it only after you understand why the error might appear.</p>

<h3>Numbers stored as text</h3>
<p>A small green triangle in the cell corner often means a number is stored as text, so SUM ignores it. Click the warning button and choose <strong>Convert to Number</strong>.</p>

<h3>Circular references</h3>
<p>If a formula refers to its own cell, such as typing <code>=SUM(A1:A5)</code> in A5, Excel warns about a circular reference. Move the formula to a cell outside the range it adds.</p>

<h3>Try it yourself</h3>
<ol>
<li>Type 10 in A1 and leave B1 empty. In C1 write <code>=A1/B1</code> and read the error.</li>
<li>Fix it with <code>=IFERROR(A1/B1,"Enter a divisor")</code>.</li>
<li>In D1 write <code>=SUMM(A1:A5)</code>, see #NAME?, then correct the spelling.</li>
<li>Press Ctrl+` to show formulas, then press it again.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>The error name tells you the kind of problem.</li>
<li>F2 highlights the cells a formula uses.</li>
<li>#REF! comes from deleting something a formula needs.</li>
<li>IFERROR displays a friendly message, but understand the cause first.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What must every Excel formula begin with?
- A colon
- A dollar sign
* An equals sign
- A bracket
= Excel treats anything starting with = as a formula.

Q: What is the result of =2+3*4?
- 20
- 9
* 14
- 24
= Multiplication happens before addition: 3*4=12, then 12+2=14.

Q: A formula in D2 is =B2*$F$1. You copy it to D3. What does D3 contain?
- =B3*F2
* =B3*$F$1
- =B2*$F$1
- =B3*$F$2
= The B reference is relative and moves to row 3, while $F$1 is absolute and stays fixed.

Q: Which function counts how many cells contain names (text)?
- COUNT
- SUM
* COUNTA
- AVERAGE
= COUNT counts only numbers. COUNTA counts all cells that are not empty.

Q: What does =ROUND(12476,-3) return?
- 12476
- 12500
* 12000
- 13000
= A negative number of digits rounds to the left of the decimal point; -3 rounds to the nearest thousand.

Q: What does =IF(C2>=50,"Pass","Fail") show when C2 holds 49?
- Pass
* Fail
- 49
- An error
= 49 is not 50 or more, so the test is false and Excel shows Fail.

Q: Which error appears when you divide by an empty cell?
- #NAME?
- #REF!
* #DIV/0!
- #VALUE!
= An empty cell counts as zero, and division by zero gives #DIV/0!.

Q: You wrote =SUMM(A1:A5) and got #NAME?. What is the likely problem?
- The range is too long
* The function name is misspelt
- The sheet is protected
- The cells contain dates
= Excel does not know a function called SUMM. Correct it to SUM.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Lookups, data tools and charts',
            'summary' => 'Find values with lookups, summarise with SUMIF and PivotTables, control input, and draw charts.',
            'lessons' => [
                [
                    'title'   => 'Looking up values: VLOOKUP and XLOOKUP',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>You will learn to make Excel find a value in a table, such as the price of an item from its code, or a student's name from an admission number. Lookups save you from searching by eye and prevent copying mistakes.</p>

<h3>The idea</h3>
<p>Imagine a price list with item codes in column A and prices in column C. You type a code in another cell and want the price to appear. A lookup function searches the first column for the code and returns something from the same row.</p>

<h3>VLOOKUP</h3>
<pre><code>=VLOOKUP(lookup_value, table_array, col_index_num, FALSE)</code></pre>
<ul>
<li><strong>lookup_value</strong>: what you are looking for (for example the code in E2).</li>
<li><strong>table_array</strong>: the table to search, such as <code>A2:C50</code>. The thing you search for must be in its <em>first</em> column.</li>
<li><strong>col_index_num</strong>: which column of that table to return (first column is 1, second is 2...).</li>
<li><strong>FALSE</strong>: means exact match. Always use FALSE for codes and names.</li>
</ul>
<p>Example: find the price in column 3 for the code in E2:</p>
<pre><code>=VLOOKUP(E2,$A$2:$C$50,3,FALSE)</code></pre>
<p>Use dollar signs on the table so it does not move when you copy the formula down. If the code is not found you get <code>#N/A</code>. Wrap with IFERROR for a friendly message: <code>=IFERROR(VLOOKUP(E2,$A$2:$C$50,3,FALSE),"Not found")</code>.</p>
<p>Limits of VLOOKUP: it cannot look to the left of the search column, and it breaks if you insert a column into the table, because the column number no longer points to the right place.</p>

<h3>XLOOKUP (Excel 2021 and Microsoft 365)</h3>
<pre><code>=XLOOKUP(lookup_value, lookup_array, return_array, "Not found")</code></pre>
<p>The same example is:</p>
<pre><code>=XLOOKUP(E2,$A$2:$A$50,$C$2:$C$50,"Not found")</code></pre>
<p>Here you give the column to search and the column to return separately. It does exact matching by default, can look in any direction, does not break when columns are inserted, and has a built-in "not found" message. If you use Excel 2019 or older, XLOOKUP is not available, so use VLOOKUP.</p>

<h3>Keeping lookups reliable</h3>
<ul>
<li>Codes must match exactly. A hidden space after a code (<code>A101 </code>) causes #N/A; use TRIM to clean data.</li>
<li>Numbers stored as text will not match real numbers.</li>
<li>Make sure each code appears only once in the table.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Build a table in A1:C6 with headings Code, Item, Price and five rows, such as A101 Sugar 4500.</li>
<li>In E1 type a code, for example <code>A103</code>.</li>
<li>In F1 write a VLOOKUP that returns the item name for the code in E1.</li>
<li>In G1 write another that returns the price. Try a code that does not exist and wrap it in IFERROR.</li>
<li>If your Excel has XLOOKUP, write the same lookup with it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>VLOOKUP searches the first column of a table and returns a value from a column you number.</li>
<li>Use FALSE for an exact match and lock the table with dollar signs.</li>
<li>XLOOKUP is simpler and safer where available.</li>
<li>Clean spaces and match data types to avoid #N/A.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'SUMIF, COUNTIF, data validation and removing duplicates',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn to total and count only the rows that match a condition, to stop wrong data being typed, and to remove repeated records. These tools answer questions such as "how much did we spend on transport?" and keep your lists reliable.</p>

<h3>SUMIF: add only what matches</h3>
<pre><code>=SUMIF(range, criteria, sum_range)</code></pre>
<p>If column A holds categories (Food, Transport, Rent) and column B holds amounts, then:</p>
<pre><code>=SUMIF(A2:A30,"Transport",B2:B30)</code></pre>
<p>gives the total of all Transport rows. Instead of typing the word, you can point to a cell that holds it: <code>=SUMIF(A2:A30,D2,B2:B30)</code>. For conditions with numbers, put the sign inside the quotes: <code>=SUMIF(B2:B30,"&gt;100000")</code> adds only amounts above 100000.</p>

<h3>COUNTIF and AVERAGEIF</h3>
<ul>
<li><code>=COUNTIF(C2:C40,"Pass")</code> counts how many cells say Pass.</li>
<li><code>=COUNTIF(B2:B40,"&lt;50")</code> counts marks below 50.</li>
<li><code>=AVERAGEIF(A2:A30,"Food",B2:B30)</code> averages only the Food amounts.</li>
</ul>
<p>For several conditions use <code>SUMIFS</code>, <code>COUNTIFS</code> and <code>AVERAGEIFS</code>. In SUMIFS the sum range comes first: <code>=SUMIFS(B2:B30,A2:A30,"Food",C2:C30,"March")</code>.</p>

<h3>Data validation: a drop-down list</h3>
<p>Data validation controls what can be typed in a cell. It prevents spelling differences ("Transport", "transport ", "Transprt") that spoil SUMIF results.</p>
<ol>
<li>Select the cells that should have a list, for example A2:A30.</li>
<li>Choose <strong>Data</strong>, then <strong>Data Validation</strong>.</li>
<li>Under <strong>Allow</strong>, pick <strong>List</strong>.</li>
<li>In <strong>Source</strong> type your choices separated by commas, such as <code>Food,Transport,Rent,School fees</code>, or select a range that holds the choices.</li>
<li>On the <strong>Error Alert</strong> tab, write a message and click <strong>OK</strong>.</li>
</ol>
<p>A small arrow now appears beside the cell so users can pick. You can also validate numbers, for example allow only whole numbers between 0 and 100 for exam marks, or dates after a certain day. The <strong>Input Message</strong> tab shows a helpful tip when the cell is selected.</p>

<h3>Remove duplicates</h3>
<ol>
<li>Make a copy of your sheet first (right-click the tab, choose <strong>Move or Copy</strong>, tick <strong>Create a copy</strong>), because this action deletes rows.</li>
<li>Click inside the list and choose <strong>Data</strong>, then <strong>Remove Duplicates</strong>.</li>
<li>Tick the columns that together make a record a duplicate (for example Name and Phone) and click <strong>OK</strong>.</li>
<li>Excel tells you how many duplicates it removed and how many unique values remain.</li>
</ol>
<p>To only look at duplicates first, use <strong>Conditional Formatting</strong>, <strong>Highlight Cells Rules</strong>, <strong>Duplicate Values</strong>.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create a list with Category and Amount columns holding 10 expenses.</li>
<li>Add a drop-down list for Category with Food, Transport and Rent.</li>
<li>Below, write a SUMIF for the total of each category.</li>
<li>Add a COUNTIF that counts the Food items.</li>
<li>Type one expense twice, highlight duplicates, then remove them.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>SUMIF, COUNTIF and AVERAGEIF work on rows that match a condition.</li>
<li>Drop-down lists keep data consistent and spelled the same.</li>
<li>Remove Duplicates deletes rows, so work on a copy.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Charts and sparklines',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>Here you will learn to turn numbers into a chart that people understand in seconds. You will choose the right chart type, build it, polish it, and add tiny charts inside cells called sparklines. Charts are used in reports, school presentations and funding proposals.</p>

<h3>Choosing a chart type</h3>
<table>
<thead><tr><th>Chart</th><th>Best for</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Column (or bar)</td><td>Comparing amounts between groups</td><td>Sales of each product</td></tr>
<tr><td>Line</td><td>Change over time</td><td>Monthly income through the year</td></tr>
<tr><td>Pie</td><td>Parts of one whole (a few slices only)</td><td>Share of budget spent on each category</td></tr>
</tbody>
</table>
<p>Avoid pie charts with more than five slices, and never use a pie for numbers that do not add to a meaningful whole.</p>

<h3>Creating a chart</h3>
<ol>
<li>Type your data with labels in the first column and a heading above the numbers, for example months in A2:A7 and sales in B2:B7 with the heading <code>Sales</code> in B1.</li>
<li>Select the whole block, headings included (A1:B7).</li>
<li>Choose <strong>Insert</strong>, then pick a chart from the <strong>Charts</strong> group, such as <strong>Clustered Column</strong>. You can also click <strong>Recommended Charts</strong> and let Excel suggest.</li>
<li>Excel places the chart on the sheet. Drag it by its border to move it and drag the corners to resize.</li>
</ol>

<h3>Formatting a chart</h3>
<p>Click the chart. Three small buttons appear at its right: <strong>Chart Elements</strong> (the plus), <strong>Chart Styles</strong> (the brush) and <strong>Chart Filters</strong>. The <strong>Chart Design</strong> and <strong>Format</strong> tabs also appear on the ribbon.</p>
<ul>
<li>Click the title and type a clear one: "Monthly sales, 2025 (UGX)".</li>
<li>Under Chart Elements, add <strong>Axis Titles</strong> and <strong>Data Labels</strong> where helpful.</li>
<li>Delete the legend if there is only one series, and delete heavy gridlines.</li>
<li>Choose a simple colour. Use one colour for one series; use different colours only when they mean something.</li>
<li>Use <strong>Chart Design</strong>, <strong>Change Chart Type</strong> to try another type, and <strong>Switch Row/Column</strong> if the data is plotted the wrong way.</li>
</ul>
<p>To move a chart to its own sheet, use <strong>Move Chart</strong> on the Chart Design tab.</p>

<h3>Good chart habits</h3>
<ul>
<li>One message per chart.</li>
<li>Label the axes and units.</li>
<li>Start column charts at zero so bars are honest.</li>
<li>Keep the chart readable on a phone or projector: large fonts, few colours.</li>
</ul>

<h3>Sparklines</h3>
<p>A <strong>sparkline</strong> is a tiny chart inside one cell, ideal beside a row of monthly figures.</p>
<ol>
<li>Click the empty cell at the end of the row.</li>
<li>Choose <strong>Insert</strong>, then <strong>Line</strong> (or <strong>Column</strong>) in the <strong>Sparklines</strong> group.</li>
<li>Select the row of numbers as the <strong>Data Range</strong> and click <strong>OK</strong>.</li>
<li>Use the <strong>Sparkline</strong> tab to show High Point or Markers.</li>
</ol>

<h3>Try it yourself</h3>
<ol>
<li>Type six months of sales in two columns (month and amount).</li>
<li>Insert a Clustered Column chart and give it a clear title.</li>
<li>Change it to a Line chart and compare the look.</li>
<li>Type three categories with amounts that add to a budget and make a pie chart with data labels showing percentages.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Column compares, line shows change over time, pie shows parts of a whole.</li>
<li>Select the data with its headings before inserting a chart.</li>
<li>Give every chart a clear title and labelled axes.</li>
<li>Sparklines are mini charts inside a cell.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'PivotTables: summarise data in a few clicks',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>A PivotTable lets you summarise hundreds or thousands of rows by dragging and dropping, without writing a formula. After this lesson you will be able to build one and answer questions like "how much did each branch sell each month?". It sounds advanced, but the basic version is easy.</p>

<h3>What you need first</h3>
<p>Your data must be a clean list: one heading per column, no blank columns, no totals rows mixed in, and each row one record. For example, a sales list with columns <code>Date</code>, <code>Branch</code>, <code>Product</code> and <code>Amount</code>. The best way is to turn the list into a Table first with Ctrl+T.</p>

<h3>Creating a PivotTable</h3>
<ol>
<li>Click any cell in your list.</li>
<li>Choose <strong>Insert</strong>, then <strong>PivotTable</strong>.</li>
<li>Check that the range is right and choose <strong>New Worksheet</strong>. Click <strong>OK</strong>.</li>
<li>A blank area and a <strong>PivotTable Fields</strong> panel appear on the right.</li>
</ol>

<h3>The four areas</h3>
<p>Drag field names into the four boxes at the bottom of the panel:</p>
<table>
<thead><tr><th>Area</th><th>What it does</th></tr></thead>
<tbody>
<tr><td><strong>Rows</strong></td><td>Each different value becomes a row, for example each Branch</td></tr>
<tr><td><strong>Columns</strong></td><td>Each different value becomes a column, for example each Product</td></tr>
<tr><td><strong>Values</strong></td><td>The numbers to add up or count, for example Amount</td></tr>
<tr><td><strong>Filters</strong></td><td>A drop-down above the table to narrow everything, for example Month</td></tr>
</tbody>
</table>
<p>Drag <code>Branch</code> to Rows and <code>Amount</code> to Values. Instantly you see total sales per branch. Drag <code>Product</code> to Columns and you get a grid of branch against product.</p>

<h3>Changing the calculation</h3>
<p>Numbers go into Values as a <strong>Sum</strong> by default. Click the field in the Values box, choose <strong>Value Field Settings</strong>, and pick <strong>Count</strong>, <strong>Average</strong>, <strong>Max</strong> or others. Use <strong>Show Values As</strong> for percentage of the total. Text fields in Values are counted.</p>

<h3>Grouping dates</h3>
<p>If you drag a Date field into Rows, Excel often groups it into Months or Years automatically in Microsoft 365. If not, right-click a date in the PivotTable, choose <strong>Group</strong>, and select Months and Years.</p>

<h3>Refreshing</h3>
<p>A PivotTable does not update itself when the source list changes. Right-click inside it and choose <strong>Refresh</strong> (or press <strong>Alt+F5</strong>). If you turned the source into a Table, new rows are included automatically.</p>

<h3>PivotCharts</h3>
<p>With the PivotTable active, choose <strong>PivotTable Analyze</strong> (on some versions <strong>Analyze</strong>), then <strong>PivotChart</strong> to draw a chart that follows the table.</p>

<h3>Try it yourself</h3>
<ol>
<li>Type 15 rows of sales: date, branch (Gulu, Lira, Kitgum), product (Rice, Beans, Sugar) and amount.</li>
<li>Press Ctrl+T to turn them into a Table.</li>
<li>Insert a PivotTable on a new sheet.</li>
<li>Put Branch in Rows, Product in Columns and Amount in Values.</li>
<li>Change the Values calculation to Count and see the difference, then change back to Sum.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>PivotTables summarise a clean list without formulas.</li>
<li>Rows, Columns, Values and Filters are the four drop areas.</li>
<li>You must refresh a PivotTable after changing the source data.</li>
<li>Starting from a Table makes the PivotTable easier to keep current.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: In =VLOOKUP(E2,$A$2:$C$50,3,FALSE), what does the number 3 mean?
- Look in row 3
* Return the value from the third column of the table
- Search three times
- Round to 3 digits
= The col_index_num counts columns inside the table, starting at 1 for the first.

Q: Why do you normally put FALSE at the end of a VLOOKUP?
- To make the answer false
- To sort the list
* To ask for an exact match
- To ignore capital letters
= FALSE means exact match, which is what you want for codes and names.

Q: A lookup returns #N/A even though the code looks identical. What is a likely cause?
- The sheet is too long
* A hidden extra space after one of the codes
- The font is wrong
- The table has borders
= Trailing spaces make two codes differ. Use TRIM to clean data.

Q: Which formula totals the Amount (B2:B30) for rows where Category (A2:A30) is Transport?
- =SUM("Transport",B2:B30)
- =COUNTIF(A2:A30,"Transport")
* =SUMIF(A2:A30,"Transport",B2:B30)
- =IF(A2:A30="Transport",B2:B30)
= SUMIF takes the range to test, the criteria and the range to add.

Q: What is the main benefit of a data validation drop-down list?
- It makes the sheet print faster
* It keeps entries consistent so formulas such as SUMIF match correctly
- It adds colours automatically
- It locks the workbook
= Typing the same category in different ways spoils totals; a list prevents that.

Q: Which chart type is best for showing how income changed month by month?
- Pie
* Line
- Doughnut with five rings
- Table
= A line chart shows change over time clearly.

Q: Which are good chart habits? (choose all that apply)
* Give the chart a clear title
* Label the axes and units
- Use as many bright colours as possible
= Clarity matters more than decoration.

Q: You added new rows to the source list of a PivotTable, but it still shows old totals. What do you do?
- Delete the PivotTable
- Retype all the data
* Right-click the PivotTable and choose Refresh
- Change the font
= PivotTables update only when refreshed (Alt+F5).
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Real tasks, printing and sharing',
            'summary' => 'Build a budget, a mark sheet, a stock sheet and a loan calculation; then print, protect, share and work faster.',
            'lessons' => [
                [
                    'title'   => 'Project: a household or small-business budget',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>You will build a monthly budget from scratch, using many of the skills from this course. A budget shows how much money comes in, how much goes out and what is left. Whether it is for your household or a small shop, it helps you make decisions instead of guessing.</p>

<h3>Plan the layout</h3>
<p>Before typing, decide the columns. A simple monthly budget has:</p>
<table>
<thead><tr><th>Column</th><th>Contents</th></tr></thead>
<tbody>
<tr><td>A</td><td>Item (Salary, Sales, Rent, Food...)</td></tr>
<tr><td>B</td><td>Planned amount (UGX)</td></tr>
<tr><td>C</td><td>Actual amount (UGX)</td></tr>
<tr><td>D</td><td>Difference (planned minus actual)</td></tr>
</tbody>
</table>

<h3>Step by step</h3>
<ol>
<li>In A1 type the title <code>Monthly Budget - March</code>. Make it bold and larger.</li>
<li>In A3 type <code>INCOME</code> and list items below: Salary or Sales, Other income. Put planned amounts in column B and actual amounts in column C.</li>
<li>Below the income rows, type <code>Total income</code> and use <code>=SUM(B4:B5)</code>. Copy it across to column C.</li>
<li>Leave a blank row and type <code>EXPENSES</code>. List items: Rent, Food, Transport, School fees, Airtime and data, Savings, Other.</li>
<li>Type <code>Total expenses</code> and use SUM again.</li>
<li>Type <code>Balance (income minus expenses)</code> and write <code>=B10-B18</code> (use your own row numbers). If it is positive you have money left; if negative, you overspend.</li>
<li>In column D write <code>=B5-C5</code> in the first expense row and fill down. A positive value in an expense row means you spent less than planned.</li>
</ol>

<h3>Make it clear</h3>
<ul>
<li>Format B:D as UGX with no decimals.</li>
<li>Give the INCOME and EXPENSES headings a light fill colour and bold text.</li>
<li>Add conditional formatting to the Balance cell: red if less than 0, green otherwise.</li>
<li>Add a column E, <code>% of income</code>, with <code>=C5/C$10</code> (lock the row of total income) formatted as percentage, so you see how much of your income each expense takes.</li>
</ul>

<h3>Add a chart</h3>
<p>Select the expense item names and actual amounts (hold Ctrl to select the two blocks) and insert a pie or bar chart to show where the money goes.</p>

<h3>Good budget habits</h3>
<ul>
<li>Save first (including savings as an expense line) and spend what is left.</li>
<li>Update the Actual column weekly, not at the end of the month.</li>
<li>Copy the sheet for each month: right-click the tab, choose <strong>Move or Copy</strong>, and tick <strong>Create a copy</strong>.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Build the budget above with your own realistic figures (or a small shop's figures).</li>
<li>Check that the balance formula is correct by changing one amount and watching the balance change.</li>
<li>Apply the conditional formatting to the balance.</li>
<li>Insert a chart of your expenses and give it a title.</li>
<li>Save the file as <code>Budget 2025</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Plan columns first, then type, then format.</li>
<li>Totals use SUM; the balance is income minus expenses.</li>
<li>Comparing planned and actual amounts shows where to adjust.</li>
<li>A chart makes spending patterns visible.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Project: a class mark sheet and a stock sheet',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>In this lesson you will build two tools that real schools and shops use every day: a mark sheet that works out totals, averages and grades, and a stock sheet that warns you when goods run low. Both use formulas you already know, joined into a working system.</p>

<h3>Part A: a class mark sheet</h3>
<ol>
<li>In row 1 type the headings: <code>Name</code>, <code>English</code>, <code>Maths</code>, <code>Science</code>, <code>Total</code>, <code>Average</code>, <code>Grade</code>, <code>Position</code>.</li>
<li>Enter eight students with marks out of 100 in columns B to D.</li>
<li>In E2 write <code>=SUM(B2:D2)</code>. In F2 write <code>=ROUND(AVERAGE(B2:D2),1)</code>.</li>
<li>In G2 write a nested IF for the grade:
<pre><code>=IF(F2&gt;=80,"A",IF(F2&gt;=70,"B",IF(F2&gt;=60,"C",IF(F2&gt;=50,"D","F"))))</code></pre></li>
<li>In H2 write the rank: <code>=RANK(E2,$E$2:$E$9)</code>. The range is locked with dollar signs. The best total gets position 1. Some Excel versions also offer RANK.EQ, which does the same.</li>
<li>Select E2:H2 and drag the fill handle down to row 9.</li>
</ol>
<p>Add data validation to B2:D9 so that only whole numbers from 0 to 100 are accepted, and add a conditional format that colours failing averages red. Under the table, use <code>=AVERAGE(B2:B9)</code> for the class average of each subject, <code>=MAX(...)</code> for the top mark and <code>=COUNTIF(G2:G9,"F")</code> for the number of failures. Finish by sorting by Position.</p>

<h3>Part B: a stock sheet</h3>
<ol>
<li>Headings: <code>Code</code>, <code>Item</code>, <code>Opening stock</code>, <code>Bought</code>, <code>Sold</code>, <code>Closing stock</code>, <code>Unit price</code>, <code>Stock value</code>, <code>Status</code>.</li>
<li>Enter eight items with opening stock, quantities bought and sold.</li>
<li>Closing stock in F2: <code>=C2+D2-E2</code>.</li>
<li>Stock value in H2: <code>=F2*G2</code>.</li>
<li>Status in I2, with a reorder level of 10 stored in a cell, say L1:
<pre><code>=IF(F2&lt;=$L$1,"Reorder","OK")</code></pre></li>
<li>Fill the formulas down. Format G and H as UGX.</li>
<li>Below the table: total stock value <code>=SUM(H2:H9)</code> and items to reorder <code>=COUNTIF(I2:I9,"Reorder")</code>.</li>
<li>Apply conditional formatting to the Status column so "Reorder" cells turn orange.</li>
</ol>
<p>Because the reorder level is in one cell, you can change it once and the whole sheet updates. That is the benefit of an absolute reference.</p>

<h3>Tips for sheets that others will use</h3>
<ul>
<li>Put assumptions such as the reorder level or pass mark in labelled cells, not inside formulas.</li>
<li>Turn the data into a Table (Ctrl+T) so new rows get the formulas automatically.</li>
<li>Freeze the heading row.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Build the mark sheet with eight students and check the grades by hand for two of them.</li>
<li>Build the stock sheet with six items and make two of them fall below the reorder level.</li>
<li>Confirm that the Status turns to Reorder when you raise the reorder level in L1.</li>
<li>Save both as separate sheets in one workbook named <code>School and shop</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A mark sheet combines SUM, AVERAGE, IF and RANK.</li>
<li>A stock sheet follows: closing = opening + bought - sold.</li>
<li>Keep settings like the pass mark or reorder level in one cell and refer to it with $.</li>
<li>Always test formulas with a few rows you can check by hand.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Savings and loan calculations',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>You will learn to use Excel to work out simple interest, compound savings and monthly loan repayments. Many people in savings groups, SACCOs and small businesses borrow or save money, and a spreadsheet lets you see the true cost before you sign anything.</p>

<h3>Simple interest</h3>
<p>Simple interest is charged only on the original amount. If you borrow UGX 1,000,000 at 12 percent a year for 2 years:</p>
<pre><code>=B1*B2*B3</code></pre>
<p>where B1 holds the loan (1000000), B2 the rate (12%) and B3 the years (2). Interest is 240,000 and the total repayable is 1,240,000. Label each input in column A so the sheet explains itself.</p>

<h3>Compound savings</h3>
<p>With compound interest, you earn interest on interest. If you save UGX 500,000 at 8 percent a year for 5 years:</p>
<pre><code>=B1*(1+B2)^B3</code></pre>
<p>This gives about 734,664. Compare this with simple interest to see the difference grow over time. To show year-by-year growth, start with the amount in B2, then in B3 write <code>=B2*(1+$E$1)</code> where E1 holds the rate, and fill down.</p>

<h3>Monthly loan repayment: the PMT function</h3>
<pre><code>=PMT(rate, nper, pv)</code></pre>
<ul>
<li><strong>rate</strong>: the interest rate for each period. For a yearly rate paid monthly divide by 12.</li>
<li><strong>nper</strong>: the total number of payments.</li>
<li><strong>pv</strong>: the amount borrowed now.</li>
</ul>
<p>For a loan of UGX 2,000,000 at 18 percent a year over 12 months:</p>
<pre><code>=-PMT(18%/12,12,2000000)</code></pre>
<p>The minus sign at the start turns the answer positive; Excel's PMT gives money you pay out as a negative number. The result is about 183,360 per month. The total you repay is the payment times the number of months, and the interest is the difference from the amount borrowed. Use cells for inputs instead of typing the numbers, so you can test "what if I borrow for 18 months instead?" in seconds. Banks and SACCOs may use different methods (for example flat-rate interest), so ask how a lender calculates before comparing, and treat Excel as a planning aid.</p>

<h3>What-if comparison table</h3>
<p>Make a small table with several loan lengths (6, 12, 18, 24 months) in column A and a PMT formula in column B that refers to A and to locked input cells. Compare the monthly payment with the total interest to choose a loan you can afford.</p>

<h3>Try it yourself</h3>
<ol>
<li>Open a new sheet. In A1:A3 type Loan amount, Yearly rate, Months; in B1:B3 type 2000000, 18% and 12.</li>
<li>In B5 write <code>=-PMT(B2/12,B3,B1)</code> and format as UGX.</li>
<li>In B6 write <code>=B5*B3</code> for the total repaid, and in B7 write <code>=B6-B1</code> for the interest.</li>
<li>Change the months to 24 and notice the payment drops while the interest rises.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Simple interest is amount x rate x years.</li>
<li>Compound growth is amount x (1 + rate) ^ years.</li>
<li>PMT gives a regular repayment; divide the yearly rate by 12 for monthly payments.</li>
<li>Put inputs in labelled cells and test different scenarios.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Printing, protecting, sharing and shortcuts',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>The last step of any spreadsheet is to print it, protect it from accidents and share it with others. In this lesson you will also collect the keyboard shortcuts that make you faster, and finish with a checklist for good spreadsheets.</p>

<h3>Printing</h3>
<ol>
<li>Select the area you want to print and choose <strong>Page Layout</strong>, <strong>Print Area</strong>, <strong>Set Print Area</strong>. Otherwise Excel prints every cell that has data.</li>
<li>Choose <strong>Page Layout</strong>, <strong>Orientation</strong>, then <strong>Landscape</strong> for wide tables.</li>
<li>Under <strong>Page Layout</strong>, use <strong>Print Titles</strong> and set <strong>Rows to repeat at top</strong> to <code>$1:$1</code> so headings appear on every page.</li>
<li>Open <strong>Insert</strong>, <strong>Header &amp; Footer</strong> to add a title, date or page number such as "Page 1 of 3".</li>
<li>In the <strong>File</strong>, <strong>Print</strong> screen (Ctrl+P, Mac: Command+P), look at the preview. In the settings at the bottom, choose <strong>Fit All Columns on One Page</strong> if columns spill onto a second page.</li>
<li>Choose the printer and number of copies and click <strong>Print</strong>.</li>
</ol>
<p>Always check the preview first; it saves paper. You can also use <strong>View</strong>, <strong>Page Break Preview</strong> to drag the blue page-break lines. To print a table with gridlines, tick <strong>Print</strong> under <strong>Gridlines</strong> on the Page Layout tab, or add borders.</p>

<h3>Protecting cells</h3>
<p>You may want people to type in the input cells but not change your formulas. By default every cell is "locked", but the lock only works once you protect the sheet.</p>
<ol>
<li>Select the cells people may edit (for example the marks). Press Ctrl+1, open the <strong>Protection</strong> tab and untick <strong>Locked</strong>. Click OK.</li>
<li>Choose <strong>Review</strong>, then <strong>Protect Sheet</strong>.</li>
<li>Type a password if you want one (do not forget it, because Microsoft cannot recover it) and click <strong>OK</strong>.</li>
</ol>
<p>Now only the unlocked cells can be changed. <strong>Review</strong>, <strong>Unprotect Sheet</strong> removes it. To require a password to open a file, use <strong>File</strong>, <strong>Info</strong>, <strong>Protect Workbook</strong>, <strong>Encrypt with Password</strong>. Sheet protection is meant to prevent accidents, not to stop a determined person.</p>

<h3>Sharing and co-authoring</h3>
<ul>
<li>Save the workbook to <strong>OneDrive</strong>, click <strong>Share</strong> at the top right, enter an email address and choose whether the person can edit or only view.</li>
<li>Several people can then edit at the same time, and you see their cursors.</li>
<li>To send a copy, use <strong>File</strong>, <strong>Share</strong>, <strong>Attach a copy</strong> or save as PDF through <strong>File</strong>, <strong>Export</strong>.</li>
<li>Use <strong>Review</strong>, <strong>New Comment</strong> to leave notes for others.</li>
</ul>
<p>Only share with people you trust, and remove sensitive data such as phone numbers if they are not needed.</p>

<h3>Useful shortcuts</h3>
<table>
<thead><tr><th>Windows</th><th>Does</th></tr></thead>
<tbody>
<tr><td>Ctrl+S (Mac: Command+S)</td><td>Save</td></tr>
<tr><td>Ctrl+Z / Ctrl+Y</td><td>Undo / Redo</td></tr>
<tr><td>Ctrl+C, X, V</td><td>Copy, cut, paste</td></tr>
<tr><td>Ctrl+1</td><td>Format Cells</td></tr>
<tr><td>Ctrl+Arrow, Ctrl+Shift+Arrow</td><td>Jump to / select to the edge of data</td></tr>
<tr><td>Alt+=</td><td>AutoSum</td></tr>
<tr><td>F4</td><td>Absolute reference, or repeat last action</td></tr>
<tr><td>Ctrl+; (semicolon)</td><td>Insert today's date as a fixed value</td></tr>
<tr><td>Ctrl+Shift+L</td><td>Turn filters on or off</td></tr>
</tbody>
</table>

<h3>Final checklist for a good spreadsheet</h3>
<ul>
<li>Clear title and labelled columns.</li>
<li>Correct number formats (UGX, %, dates).</li>
<li>Formulas tested on a few rows by hand.</li>
<li>Inputs in labelled cells; no hidden numbers inside formulas.</li>
<li>Saved with a sensible name and backed up.</li>
<li>Print preview checked.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Open your mark sheet. Set a print area, landscape orientation and repeat the heading row.</li>
<li>Add a footer with the page number and view the print preview.</li>
<li>Unlock the mark cells, then protect the sheet and try to change a formula cell.</li>
<li>Go through the checklist and fix anything missing.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use Print Area, Landscape, Print Titles and the preview to print well.</li>
<li>Unlock input cells, then Protect Sheet to guard formulas.</li>
<li>Share through OneDrive for co-authoring, with view or edit rights.</li>
<li>Shortcuts and a final checklist make your work faster and more reliable.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: In a stock sheet, which formula correctly gives the closing stock?
- Opening stock minus bought plus sold
* Opening stock plus bought minus sold
- Bought times sold
- Sold minus opening stock
= Stock goes up with purchases and down with sales: opening + bought - sold.

Q: You keep the reorder level in cell L1 and write =IF(F2<=$L$1,"Reorder","OK"). Why use $L$1?
- To make the text bold
* So the reference stays on L1 when the formula is copied down
- To round the number
- To hide the cell
= The dollar signs make the reference absolute so every row compares to the same cell.

Q: What does =RANK(E2,$E$2:$E$9) calculate?
- The average of the range
* The position of E2 among the totals in the range
- The sum of the range
- The number of students
= RANK tells you where a value sits when the list is ordered, with 1 for the largest.

Q: You borrow UGX 1,000,000 at 12% a year simple interest for 2 years. What is the total interest?
- 120,000
* 240,000
- 1,240,000
- 12,000
= Simple interest = 1,000,000 x 12% x 2 = 240,000. The total repayable would be 1,240,000.

Q: For a monthly PMT on a yearly rate of 18%, what rate do you use?
- 18%
- 18%*12
* 18%/12
- 1.8%
= Divide the yearly rate by 12 when payments are monthly.

Q: Which Page Layout setting makes headings appear at the top of every printed page?
- Print Area
- Margins
* Print Titles (Rows to repeat at top)
- Gridlines
= Rows to repeat at top repeats the chosen heading row on each page.

Q: To protect formulas but still let people type marks, what do you do first?
- Delete the formulas
* Unlock the mark cells, then protect the sheet
- Merge all cells
- Hide the ribbon
= Cells are locked by default, but locking only works after the sheet is protected. Unlock the input cells first.

Q: Which are good habits when sharing a workbook? (choose all that apply)
* Share only with people you trust
* Choose view-only rights when people do not need to edit
- Always give everyone edit access
= Limit access to what each person needs and remove sensitive data you do not need to share.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What is a workbook in Excel?
- One cell
* The whole file, which can hold several worksheets
- A single column
- A chart
= A workbook is the Excel file. Worksheets are the pages inside it.

Q: Which of these is a valid cell address?
- 5B
- B-5
* B5
- Row5Col2
= Excel addresses join the column letter and the row number, such as B5.

Q: Which shortcut returns you to cell A1?
- Ctrl+End
* Ctrl+Home
- Ctrl+Z
- Ctrl+Space
= Ctrl+Home jumps to A1.

Q: What does the fill handle do?
- Deletes a cell
- Changes the font
* Copies a value or continues a pattern when dragged
- Saves the file
= The small square at the corner of a selected cell is used for AutoFill.

Q: Which are examples of Excel treating something as a number? (choose all that apply)
* 2500
* 15/03/2025 (a date)
- Sugar
= Numbers and dates are stored as numbers. Words are text.

Q: A cell has 0.18 and you format it as a percentage. What is displayed?
- 0.18%
* 18%
- 1.8%
- 180%
= A percentage format multiplies by 100 for display.

Q: Why would you format a phone number cell as Text?
- To make it bold
- To make it add up
* To keep the leading zero
- To sort it by size
= Numbers lose leading zeros; Text format keeps them.

Q: What does Freeze Top Row do?
- Deletes row 1
* Keeps the heading row visible while scrolling
- Protects the sheet
- Prints row 1 twice
= It stops the headings scrolling out of sight.

Q: Before sorting a list, what should you do?
- Select only one column
* Click one cell inside the list so whole rows move together
- Delete the headings
- Merge the cells
= Sorting must keep each record's information together.

Q: Which shortcut creates an Excel Table? (Windows)
* Ctrl+T
- Ctrl+L
- Ctrl+U
= Ctrl+T (Mac: Command+T) makes the list a Table.

Q: Which Conditional Formatting rule colours marks lower than 50?
- Top 10 Items
* Highlight Cells Rules, Less Than 50
- Data Bars only
- Remove Duplicates
= Less Than highlights cells under the number you choose.

Q: What is the result of =(2+3)*4?
- 14
- 24
* 20
- 9
= Brackets are calculated first: 5*4=20.

Q: In D2 you write =B2*$F$1 and fill it down. What stays the same in every row?
- B
* $F$1
- D2
- Nothing
= The dollar signs lock F1, while B2 changes to B3, B4 and so on.

Q: Which function counts only cells that contain numbers?
* COUNT
- COUNTA
- SUMIF
- LEN
= COUNT counts numbers; COUNTA counts anything not empty.

Q: What does =AVERAGE(B2:B6) calculate?
- The total of B2 to B6
- The largest value
* The mean of the five values
- The number of cells
= AVERAGE adds the values and divides by how many there are.

Q: Which formula gives "Pass" for marks of 50 or more in C2, otherwise "Fail"?
- =IF(C2<50,"Pass","Fail")
* =IF(C2>=50,"Pass","Fail")
- =SUM(C2,"Pass")
- =IF(C2,"Pass")
= The test is C2>=50, followed by the true and false results.

Q: What does TRIM do?
- Rounds a number
* Removes extra spaces from text
- Deletes a column
- Capitalises a word
= TRIM cleans spaces so lookups and comparisons work.

Q: Which formula finds the complete years of age from a birth date in B2?
- =TODAY(B2)
* =DATEDIF(B2,TODAY(),"Y")
- =AGE(B2)
- =B2-NOW
= DATEDIF with "Y" returns whole years between two dates.

Q: A formula returns #REF!. What most likely happened?
- You divided by zero
- You misspelt SUM
* A row, column or sheet the formula used was deleted
- The column is too narrow
= #REF! means a reference is no longer valid.

Q: In VLOOKUP, what should the last argument be for an exact match?
- TRUE
* FALSE
- 0.5
- "exact"
= FALSE asks for an exact match.

Q: Which function adds only the rows that meet a condition?
- SUM
- COUNTA
* SUMIF
- LEN
= SUMIF totals values where another range meets your criteria.

Q: What is the purpose of a drop-down list made with Data Validation?
* To limit entries to chosen values and keep spelling consistent
- To change the font
- To print the sheet
- To create a chart
= Controlled input prevents errors and keeps summaries correct.

Q: Which chart best shows parts of a whole with just a few categories?
- Line
- Scatter
* Pie
- Sparkline
= A pie chart shows shares of a total; use it only with few slices.

Q: Which drop area in a PivotTable holds the numbers to add up?
- Rows
- Filters
- Columns
* Values
= Fields in Values are summed, counted or averaged.

Q: In a stock sheet, which formula gives stock value for one row, with quantity in F2 and unit price in G2?
- =F2+G2
* =F2*G2
- =F2/G2
- =SUM(F2)
= Value = quantity multiplied by the price per unit.

Q: You borrow UGX 2,000,000 at 18% a year over 12 months. Which formula gives the monthly repayment as a positive number?
- =PMT(18%,12,2000000)
- =2000000*18%
* =-PMT(18%/12,12,2000000)
- =SUM(18%,12)
= PMT needs the monthly rate, the number of months and the loan amount. The minus sign makes the answer positive.

Q: Which print settings help a wide table print neatly? (choose all that apply)
* Landscape orientation
* Fit All Columns on One Page
- Deleting the headings
= Orientation and scaling keep the table on one page width.

Q: What must you do before protecting a sheet if people still need to type in some cells?
- Delete the formulas
* Unlock those input cells
- Hide the workbook
- Change the file type
= All cells are locked by default; unlock the ones people may edit and then protect the sheet.

Q: You accidentally typed over a formula. What is the fastest way to recover?
- Close without saving, always
* Press Ctrl+Z to undo
- Delete the sheet
- Restart the computer
= Undo reverses your latest action immediately.
QUIZ,
];
