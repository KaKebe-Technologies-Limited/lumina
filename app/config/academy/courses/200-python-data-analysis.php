<?php
return [
    'slug'        => 'python-data-analysis',
    'title'       => 'Data Analysis with Python: pandas and Charts',
    'category'    => 'data',
    'level'       => 'advanced',
    'icon'        => 'layers',
    'sort'        => 200,
    'summary'     => 'Use Python, pandas and matplotlib to load, clean, summarise and chart real-world data, then complete a full survey analysis project with ethical, privacy-aware habits.',
    'description' => '<p>Organisations in Uganda and around the world collect data every day: sales records, school results, survey answers, health clinic registers, mobile money logs. The people who can turn that raw data into clear answers are in demand. <strong>pandas</strong> is the most widely used Python library for this job, and <strong>matplotlib</strong> is the standard library for drawing charts.</p><p>This course follows the real workflow of an analyst: ask a question, load the data, clean it, summarise it, chart it and report it honestly. You will work in Jupyter notebooks or Google Colab, use small datasets that you can check by hand, and finish with a complete survey project. Each snippet shows the output you should see so that you always know whether your code is right.</p><p>The course assumes you have finished the Python introduction and can write variables, loops, lists, dictionaries and functions without help. Everything else is taught here. All code uses pandas 2.x syntax, and the lessons point out the few places where newer or older versions differ.</p>',
    'outcomes'    => [
        'Set up a Python environment and work in Jupyter notebooks or Google Colab.',
        'Load CSV data with pandas and inspect its shape, types and summary statistics.',
        'Select, filter, sort and create columns using loc, iloc and boolean masks.',
        'Clean data: handle missing values, duplicates, wrong types, messy text and outliers.',
        'Combine tables with merge and concat and check that the result is correct.',
        'Summarise data with groupby, aggregations, pivot tables and simple time series.',
        'Draw clear, honest bar, line, histogram and scatter charts with matplotlib and export your results.',
        'Complete an end-to-end analysis project and handle personal data ethically.',
    ],
    'requirements'=> [
        'Completion of Introduction to Programming with Python, or equivalent Python experience (variables, loops, lists, dictionaries, functions)',
        'A computer that can run Python, or a browser and a Google account for Google Colab',
        'Basic spreadsheet knowledge helps (see Excel for Data Analysis)',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'pandas: official website', 'url' => 'https://pandas.pydata.org/'],
        ['title' => 'pandas documentation', 'url' => 'https://pandas.pydata.org/docs/'],
        ['title' => 'Matplotlib: official website', 'url' => 'https://matplotlib.org/'],
        ['title' => 'NumPy: official website', 'url' => 'https://numpy.org/'],
        ['title' => 'Project Jupyter', 'url' => 'https://jupyter.org/'],
    ],
    'modules' => [
        [
            'title'   => 'Module 1: The workflow and the tools',
            'summary' => 'Learn the steps of a data analysis, set up Python, Jupyter or Google Colab, meet NumPy arrays and read your first CSV file with pandas.',
            'lessons' => [
                [
                    'title'   => 'The data analysis workflow',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe the steps of a data analysis and set up a Python environment that holds the tools you need. Knowing the workflow stops you from jumping into code before you understand the question, which is the most common beginner mistake.</p>

<h3>Six steps of an analysis</h3>
<ol>
<li><strong>Ask a clear question.</strong> "Which shop earns more per sale?" is better than "look at the sales".</li>
<li><strong>Collect the data.</strong> Files from a spreadsheet, a database export, a survey tool or a public data portal.</li>
<li><strong>Inspect and clean.</strong> Check sizes, types, missing values and mistakes. Analysts commonly spend most of their time on this step.</li>
<li><strong>Explore and summarise.</strong> Totals, averages, groups and comparisons.</li>
<li><strong>Visualise.</strong> Draw charts that show the pattern clearly and honestly.</li>
<li><strong>Communicate.</strong> Write what you found, what you are unsure about and what should happen next.</li>
</ol>
<p>You will move back and forth between steps. A chart may reveal a data error, so you return to cleaning. That is normal.</p>

<h3>The tools</h3>
<table>
<thead><tr><th>Tool</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Python</td><td>The programming language that runs everything</td></tr>
<tr><td>NumPy</td><td>Fast arrays of numbers; pandas is built on it</td></tr>
<tr><td>pandas</td><td>Tables of data (DataFrames) with tools to clean and summarise them</td></tr>
<tr><td>matplotlib</td><td>Draws charts</td></tr>
<tr><td>Jupyter or Colab</td><td>A notebook where code, results and notes live together</td></tr>
</tbody>
</table>

<h3>Setting up on your own computer</h3>
<p>Install Python from its official website (version 3.10 or newer is a safe choice). Then create a <strong>virtual environment</strong>, a private folder of packages for one project, so that projects do not interfere with each other. Open a terminal in your project folder:</p>
<pre><code>python -m venv venv
venv\Scripts\activate          (Windows)
source venv/bin/activate       (Mac and Linux)
python -m pip install pandas matplotlib jupyter</code></pre>
<p>On some Windows computers the command is <code>py</code> instead of <code>python</code>. After activation your prompt shows <code>(venv)</code>. The install needs internet and may take a few minutes.</p>

<h3>Check that it works</h3>
<p>Start Python (or a notebook, next lesson) and run:</p>
<pre><code>import sys
import pandas as pd

print(sys.version_info.major)
print(pd.__version__)</code></pre>
<pre>3
2.2.3</pre>
<p>The first line must show 3. The second shows your pandas version, so your number may differ from 2.2.3; anything starting with 2 matches this course. If you see <code>ModuleNotFoundError</code>, the package is not installed in the environment you are using. Activate your virtual environment and install it again.</p>

<h3>If you cannot install software</h3>
<p>On a shared or school computer you may not be allowed to install anything. Google Colab, covered in the next lesson, runs in a web browser and already includes pandas, NumPy and matplotlib, so it is a complete alternative.</p>

<h3>Try it yourself</h3>
<ol>
<li>Write one question you would like to answer with data (for example about a school, shop or sport).</li>
<li>List which data you would need and where it might come from.</li>
<li>Create a virtual environment and install the packages, or open a Colab notebook.</li>
<li>Run the version check above and write down your pandas version.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>An analysis follows six steps: question, collect, clean, explore, visualise, communicate.</li>
<li>pandas holds the tables, matplotlib draws the charts, NumPy supplies fast numbers.</li>
<li>A virtual environment keeps each project's packages separate.</li>
<li>Colab is a no-install option that works in a browser.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Jupyter notebooks and Google Colab',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to run code in a notebook, mix notes with results and avoid the most common notebook traps. Analysts like notebooks because the code, the output and the explanation sit together, so the work can be read, repeated and shared.</p>

<h3>What is a notebook?</h3>
<p>A <strong>notebook</strong> is a document made of <strong>cells</strong>. A <em>code cell</em> holds Python that you can run on its own; the result appears just under it. A <em>Markdown cell</em> holds formatted text such as headings and bullet points, so you can explain what you are doing. Notebook files end in <code>.ipynb</code>.</p>

<h3>Option 1: Jupyter on your computer</h3>
<ol>
<li>Activate your virtual environment.</li>
<li>Type <code>jupyter notebook</code> in the terminal. A browser tab opens showing your folders.</li>
<li>Choose <strong>New</strong>, then <strong>Python 3 (ipykernel)</strong>.</li>
<li>Click the title to rename the notebook, for example <code>sales-analysis</code>.</li>
</ol>

<h3>Option 2: Google Colab</h3>
<p>Colab is a free service from Google that runs notebooks on Google's computers. You need a Google account and an internet connection, and you use it entirely in the browser. Search for "Google Colab", sign in, choose <strong>New notebook</strong>, and you can type code at once. pandas, NumPy and matplotlib are already installed.</p>
<ul>
<li>To use a data file, open the <strong>Files</strong> icon in the left bar and upload it. Then read it with <code>pd.read_csv("sales.csv")</code>.</li>
<li>Uploaded files are temporary. They disappear when the session ends, so keep your originals on your computer or Google Drive.</li>
<li>Choose <strong>File, Save a copy in Drive</strong> to keep your notebook.</li>
<li>Do not upload confidential data about real people to a service you do not control.</li>
</ul>

<h3>Working with cells</h3>
<table>
<thead><tr><th>Action</th><th>Shortcut</th></tr></thead>
<tbody>
<tr><td>Run the cell and move to the next</td><td>Shift + Enter</td></tr>
<tr><td>Run the cell and stay</td><td>Ctrl + Enter</td></tr>
<tr><td>Insert a cell above or below (command mode)</td><td>Esc, then A or B</td></tr>
<tr><td>Change a cell to Markdown or code</td><td>Esc, then M or Y</td></tr>
</tbody>
</table>
<p>In Colab many of these are under a Ctrl+M prefix, for example Ctrl+M then B. The menus always work. Try it:</p>
<pre><code>price = 2500
units = 10
price * units</code></pre>
<pre>25000</pre>
<p>A notebook automatically displays the value of the <em>last line</em> of a cell. Use <code>print()</code> when you want to show several values.</p>

<h3>Notebook traps</h3>
<ul>
<li><strong>Hidden state.</strong> Variables live in memory. If you run cells out of order, or delete a cell that created a variable, the notebook may work today and fail tomorrow. Before sharing, choose <strong>Kernel, Restart and run all</strong> (Colab: <strong>Runtime, Restart and run all</strong>). If it runs from top to bottom without error, the notebook is sound.</li>
<li><strong>Everything at once.</strong> Keep cells short and focused. Put imports in the first cell.</li>
<li><strong>No notes.</strong> Add a Markdown cell for each major step explaining what you found, not just what you did.</li>
</ul>

<h3>Sharing your work</h3>
<p>Jupyter can export a notebook as HTML or PDF (<strong>File, Download as</strong>). Anyone can read an HTML export without installing anything. Colab notebooks can be shared like a Google Doc, but remember that the reader needs to re-run the cells to see fresh output.</p>

<h3>Try it yourself</h3>
<ol>
<li>Open a new notebook in Jupyter or Colab and name it.</li>
<li>Add a Markdown cell with a heading and one sentence about your question.</li>
<li>In a code cell, create <code>price</code> and <code>units</code> and display their product.</li>
<li>Run the cells out of order on purpose, then use Restart and run all.</li>
<li>Save the notebook.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A notebook is made of code cells and Markdown cells; the result appears under the cell.</li>
<li>Jupyter runs on your computer; Colab runs in the browser with the libraries ready.</li>
<li>Run cells in order and use Restart and run all to prove the notebook works.</li>
<li>Files uploaded to Colab are temporary; protect personal data.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'NumPy arrays in brief',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to create NumPy arrays and calculate with them without writing loops. You need only a little NumPy, because pandas uses it behind the scenes, but the ideas of arrays and "do it to the whole column at once" appear everywhere in data work.</p>

<h3>Why arrays?</h3>
<p>A Python list can hold anything, but it is slow for numbers and does not do arithmetic on all items together. A NumPy <strong>array</strong> holds numbers of one type in a compact block, and operations apply to every item at once. This is called <strong>vectorisation</strong>.</p>
<pre><code>import numpy as np

units  = np.array([10, 50, 5, 8])
prices = np.array([2500, 500, 4500, 5000])

revenue = units * prices
print(revenue)
print(revenue.sum())
print(revenue.mean())</code></pre>
<pre>[25000 25000 22500 40000]
112500
28125.0</pre>
<p>The line <code>units * prices</code> multiplied the arrays item by item with no loop. With a plain list this would be an error or a different operation.</p>

<h3>Comparisons and filtering</h3>
<p>A comparison gives an array of True and False values, called a <strong>boolean mask</strong>. Using a mask inside square brackets keeps only the True positions. This idea returns in pandas.</p>
<pre><code>print(units &gt; 7)
print(revenue[units &gt; 7])</code></pre>
<pre>[ True  True False  True]
[25000 25000 40000]</pre>

<h3>Shape and two dimensions</h3>
<p>An array can have rows and columns. The <code>shape</code> tells you its size, and <code>axis</code> says which direction a calculation runs: axis 0 goes down the rows (one result per column) and axis 1 goes across the columns (one result per row).</p>
<pre><code>grid = np.array([[1, 2, 3],
                 [4, 5, 6]])
print(grid.shape)
print(grid.sum(axis=0))
print(grid.sum(axis=1))</code></pre>
<pre>(2, 3)
[5 7 9]
[ 6 15]</pre>

<h3>Useful ways to create arrays</h3>
<pre><code>print(np.arange(0, 10, 2))
print(np.zeros(3))</code></pre>
<pre>[0 2 4 6 8]
[0. 0. 0.]</pre>
<p><code>arange</code> works like <code>range</code> but returns an array; its end value is not included. <code>zeros</code> makes an array of zeros, handy as a starting point.</p>

<h3>Missing values: NaN</h3>
<p>NumPy stores a missing number as <code>np.nan</code> ("not a number"). Any calculation that touches NaN usually returns NaN:</p>
<pre><code>values = np.array([1.0, np.nan])
print(values.mean())
print(np.nanmean(values))</code></pre>
<pre>nan
1.0</pre>
<p>Functions starting with <code>nan</code> ignore the missing values. pandas handles NaN more gently: its <code>mean()</code> skips missing values by default, which you will see later. Always remember that a result that skips NaN is based on fewer numbers than you may expect.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create an array of the prices <code>[1200, 800, 1500]</code> and an array of quantities <code>[3, 10, 2]</code>.</li>
<li>Multiply them and print the total of the result. (3600 + 8000 + 3000 = 14600.)</li>
<li>Create a mask for items with quantity above 2 and use it to print the matching prices.</li>
<li>Make a 2 by 3 array and print the sum of each column.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Arrays hold numbers of one type and calculate on all items at once.</li>
<li>A boolean mask inside brackets filters an array.</li>
<li>Axis 0 runs down the rows and axis 1 runs across the columns.</li>
<li>np.nan marks a missing number and spreads through calculations unless you use nan-aware functions.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Reading a CSV file with pandas',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to load a CSV file into a pandas DataFrame and take a first careful look at it. This is how almost every analysis begins, and a good first look catches many problems early.</p>

<h3>What is a CSV file?</h3>
<p>A <strong>CSV</strong> (comma-separated values) file is plain text where each line is a row and commas separate the columns. Spreadsheets and databases can both export CSV, so it is the common language of data. The first line normally holds the column names.</p>

<h3>The sales data</h3>
<p>You will use this small file in Modules 1, 2 and 4. To keep the lessons self-contained, we put the text inside the code and read it as if it were a file using <code>io.StringIO</code>:</p>
<pre><code>import io
import pandas as pd

csv_text = """date,shop,product,category,units,unit_price
2025-01-03,Gulu,Notebook,Stationery,10,2500
2025-01-03,Kampala,Pen,Stationery,50,500
2025-01-04,Gulu,Sugar,Food,5,4500
2025-01-05,Kampala,Rice,Food,8,5000
2025-01-06,Gulu,Pen,Stationery,30,500
2025-01-07,Kampala,Notebook,Stationery,12,2500
2025-01-08,Gulu,Rice,Food,4,5000
2025-01-09,Kampala,Sugar,Food,6,4500
"""

df = pd.read_csv(io.StringIO(csv_text))</code></pre>
<p>Save the same text in a file called <code>sales.csv</code> next to your notebook with <code>df.to_csv("sales.csv", index=False)</code>. From now on you can load it with <code>pd.read_csv("sales.csv")</code>. Using a real file path works the same way; on Windows write paths as raw strings, for example <code>r"C:\data\sales.csv"</code>, so the backslashes are not misread.</p>

<h3>The first look</h3>
<pre><code>print(df.shape)
print(df.head(3))
print(df.dtypes)</code></pre>
<pre>(8, 6)
         date     shop   product    category  units  unit_price
0  2025-01-03     Gulu  Notebook  Stationery     10        2500
1  2025-01-03  Kampala       Pen  Stationery     50         500
2  2025-01-04     Gulu     Sugar        Food      5        4500
date          object
shop          object
product       object
category      object
units          int64
unit_price     int64
dtype: object</pre>
<p><code>shape</code> is (rows, columns): 8 rows and 6 columns. <code>head(n)</code> shows the first n rows (five by default) and <code>tail(n)</code> shows the last. <code>dtypes</code> shows each column's type. Notice that <code>date</code> is <code>object</code>, which means plain text: pandas did not recognise the dates. Newer pandas versions may show <code>str</code> instead of <code>object</code> for text columns. You will fix dates in Module 2. The method <code>df.info()</code> prints a similar overview including how many values are not missing.</p>

<h3>Summary statistics</h3>
<pre><code>print(df["units"].describe())</code></pre>
<pre>count     8.000000
mean     15.625000
std      16.177034
min       4.000000
25%       5.750000
50%       9.000000
75%      16.500000
max      50.000000
Name: units, dtype: float64</pre>
<p>The mean (15.6) is much higher than the median (the 50% row, 9). One big sale of 50 pulls the mean up, a first hint to look at that row.</p>

<h3>Useful options of read_csv</h3>
<ul>
<li><code>sep=";"</code> when the file uses semicolons.</li>
<li><code>usecols=["shop", "units"]</code> to load only some columns.</li>
<li><code>nrows=1000</code> to load just the first 1000 rows of a huge file.</li>
<li><code>parse_dates=["date"]</code> to turn a column into real dates.</li>
<li><code>na_values=["n/a", "-"]</code> to treat these texts as missing.</li>
<li><code>encoding="utf-8"</code> (or <code>"latin-1"</code>) when special characters look wrong.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Type the sales code into a notebook and run it.</li>
<li>Print <code>df.tail(2)</code> and check that the last row is the Kampala sugar sale.</li>
<li>Print <code>df["unit_price"].describe()</code> and read the minimum and maximum. (500 and 5000.)</li>
<li>Save the file with <code>to_csv</code>, then read it back with <code>read_csv</code> and confirm the shape is still (8, 6).</li>
</ol>

<h3>Key points</h3>
<ul>
<li>read_csv turns a CSV file into a DataFrame.</li>
<li>shape, head, dtypes, info and describe give a quick first look.</li>
<li>Dates are read as text unless you convert them or use parse_dates.</li>
<li>Options such as sep, usecols, na_values and encoding handle awkward files.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What should be the very first step of a data analysis?
- Draw a chart
- Delete rows with missing values
* Ask a clear question the data should answer
- Install more libraries
= A clear question guides every later choice about data, cleaning and charts.

Q: Why create a virtual environment for a project?
- It makes Python run faster
* It keeps the project's packages separate from other projects
- It is required to open a CSV file
- It deletes old Python versions
= Separate environments prevent version clashes between projects.

Q: Which statements about Google Colab are true? (choose all that apply)
* It runs in a web browser and needs a Google account
* Common libraries such as pandas are already installed
- Files uploaded to a session stay forever
- It needs no internet connection
= Uploaded files are temporary, and Colab needs internet because it runs on Google's computers.

Q: What does pressing Shift + Enter do in a Jupyter notebook?
- Deletes the cell
- Saves the notebook as HTML
* Runs the current cell and moves to the next one
- Restarts the kernel
= Shift + Enter runs the cell and selects the next one.

Q: Why use Restart and run all before sharing a notebook?
- To make the file smaller
* To prove it works from top to bottom without hidden variables from earlier runs
- To rename the cells
- To convert it to Excel
= Hidden state from out-of-order runs is the commonest notebook problem.

Q: What does np.array([1, 2, 3]) * 2 give?
- [1, 2, 3, 1, 2, 3]
* [2 4 6]
- An error
- [1 2 3 2]
= NumPy multiplies every item, unlike a plain list which would repeat.

Q: What does df.shape return for a DataFrame with 8 rows and 6 columns?
- (6, 8)
- 14
* (8, 6)
- [8, 6, 14]
= shape is (number of rows, number of columns).

Q: A date column shows dtype object after read_csv. What does this mean?
- The dates were converted correctly
* The values are still plain text and need converting to dates
- The column is empty
- The column is a number
= pandas keeps dates as text unless you convert them or use parse_dates.
QUIZ,
        ],
        [
            'title'   => 'Module 2: pandas basics',
            'summary' => 'Work with Series and DataFrames, select data with loc, iloc and boolean masks, sort, add columns and manage data types.',
            'lessons' => [
                [
                    'title'   => 'Series and DataFrames',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will understand the two core pandas objects, the Series and the DataFrame, and be able to build them by hand. Every other pandas skill rests on these two ideas.</p>

<h3>Series: one labelled column</h3>
<p>A <strong>Series</strong> is a one-dimensional list of values with a label (the <strong>index</strong>) for each value. Think of one column of a spreadsheet with row labels.</p>
<pre><code>import pandas as pd

units = pd.Series([10, 50, 5], index=["Notebook", "Pen", "Sugar"], name="units")
print(units)
print(units["Pen"])
print(units.sum())</code></pre>
<pre>Notebook    10
Pen         50
Sugar        5
Name: units, dtype: int64
50
65</pre>
<p>The printed form shows the labels on the left, the values on the right and a footer with the name and the data type (<code>dtype</code>). If you leave out <code>index</code>, pandas numbers the rows 0, 1, 2 and so on.</p>
<p>Arithmetic works on the whole Series and keeps the labels:</p>
<pre><code>print(units * 2)</code></pre>
<pre>Notebook     20
Pen         100
Sugar        10
Name: units, dtype: int64</pre>

<h3>DataFrame: a table of Series</h3>
<p>A <strong>DataFrame</strong> is a table: several Series that share the same index, one per column. The simplest way to build one is from a dictionary of lists:</p>
<pre><code>shops = pd.DataFrame({
    "shop": ["Gulu", "Kampala", "Lira"],
    "staff": [4, 9, 3],
    "rent": [800000, 2500000, 600000],
})
print(shops)</code></pre>
<pre>      shop  staff     rent
0     Gulu      4   800000
1  Kampala      9  2500000
2     Lira      3   600000</pre>
<p>Each key becomes a column name and each list becomes a column. All lists must be the same length.</p>

<h3>Looking inside</h3>
<pre><code>print(shops.shape)
print(shops.columns.tolist())
print(shops["rent"].mean())
print(type(shops["rent"]))</code></pre>
<pre>(3, 3)
['shop', 'staff', 'rent']
1300000.0
&lt;class 'pandas.core.series.Series'&gt;</pre>
<p>Picking one column with <code>shops["rent"]</code> gives a Series. The mean of the three rents is (800000 + 2500000 + 600000) / 3 = 1300000. Other helpful parts: <code>shops.index</code> (the row labels), <code>shops.columns</code> (the column names) and <code>shops.values</code> (the underlying NumPy array).</p>

<h3>Why the index matters</h3>
<p>pandas lines up data by index label, not by position. When you add two Series, it matches labels first. This prevents the classic spreadsheet mistake of adding the wrong rows together, but it also means rows with no matching label produce NaN. Later you will use <code>set_index("column")</code> to make a meaningful column the index, and <code>reset_index()</code> to turn it back into an ordinary column.</p>

<h3>Method and attribute</h3>
<p>Notice that <code>shape</code> and <code>columns</code> have no brackets (they are attributes, facts about the object), while <code>mean()</code> and <code>head()</code> have brackets because they are methods that do work. If you forget the brackets on a method, Python prints something like <code>&lt;bound method ...&gt;</code> instead of an answer.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create a Series of three exam marks with the subjects as the index and print the highest with <code>.max()</code>.</li>
<li>Build a DataFrame of four students with columns name, age and district.</li>
<li>Print its shape and the list of column names.</li>
<li>Print one column and check that its type is a Series.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A Series is a labelled column; a DataFrame is a table of Series sharing one index.</li>
<li>A dictionary of equal-length lists is the easiest way to build a DataFrame.</li>
<li>Selecting one column returns a Series.</li>
<li>Attributes have no brackets; methods need brackets.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Selecting data with loc and iloc',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to pull out exactly the columns, rows and cells you want. Selecting is the move you will repeat hundreds of times in any analysis, so the small differences between the methods are worth learning well.</p>

<p>Start by loading the sales data you saved earlier:</p>
<pre><code>import pandas as pd
df = pd.read_csv("sales.csv")</code></pre>

<h3>Selecting columns</h3>
<p>One column in single brackets gives a Series. A list of names in double brackets gives a DataFrame.</p>
<pre><code>print(df["product"].head(3))
print(df[["shop", "units"]].head(3))</code></pre>
<pre>0    Notebook
1         Pen
2       Sugar
Name: product, dtype: object
      shop  units
0     Gulu     10
1  Kampala     50
2     Gulu      5</pre>

<h3>loc: select by label</h3>
<p><code>df.loc[rows, columns]</code> uses labels. Here the row labels are the numbers 0 to 7. Important: a <code>loc</code> slice includes the end label.</p>
<pre><code>print(df.loc[2, "product"])
print(df.loc[0:2, ["shop", "units"]])</code></pre>
<pre>Sugar
      shop  units
0     Gulu     10
1  Kampala     50
2     Gulu      5</pre>
<p>Rows 0, 1 and 2 are all returned, three rows, because the end of a loc slice is included.</p>

<h3>iloc: select by position</h3>
<p><code>df.iloc[rows, columns]</code> uses positions counted from 0, like Python lists, and the end of a slice is <em>not</em> included.</p>
<pre><code>print(df.iloc[0, 2])
print(df.iloc[0:2, 0:3])</code></pre>
<pre>Notebook
         date     shop   product
0  2025-01-03     Gulu  Notebook
1  2025-01-03  Kampala       Pen</pre>
<p>The first number picks rows 0 and 1 (two rows) and the second picks columns 0, 1 and 2 (date, shop, product).</p>

<table>
<thead><tr><th></th><th>loc</th><th>iloc</th></tr></thead>
<tbody>
<tr><td>Uses</td><td>Labels (index and column names)</td><td>Positions (0, 1, 2 ...)</td></tr>
<tr><td>Slice end</td><td>Included</td><td>Excluded</td></tr>
<tr><td>Best for</td><td>Readable code, conditions</td><td>"First 5 rows", "last column"</td></tr>
</tbody>
</table>

<h3>loc with a condition</h3>
<p>You can give <code>loc</code> a True/False mask for the rows (more on masks in the next lesson) and a column name for the columns:</p>
<pre><code>print(df.loc[df["shop"] == "Gulu", "units"])</code></pre>
<pre>0    10
2     5
4    30
6     4
Name: units, dtype: int64</pre>
<p>This returns the units sold in Gulu only. The row labels 0, 2, 4, 6 stay attached to the values.</p>

<h3>Changing values safely</h3>
<p>Use <code>loc</code> to change a value, because it clearly says which rows and column you mean. Work on a copy when experimenting so the original stays safe:</p>
<pre><code>trial = df.copy()
trial.loc[trial["product"] == "Pen", "unit_price"] = 600
print(trial["unit_price"].tolist())</code></pre>
<pre>[2500, 600, 4500, 5000, 600, 2500, 5000, 4500]</pre>
<p>Both Pen rows changed, while <code>df</code> itself is untouched. Avoid chained selections such as <code>df[df["product"] == "Pen"]["unit_price"] = 600</code>; pandas may warn or silently change a temporary copy.</p>

<h3>Try it yourself</h3>
<ol>
<li>Print only the <code>date</code> and <code>product</code> columns for the first 4 rows.</li>
<li>Use <code>loc</code> to print the product of row 5. (Notebook.)</li>
<li>Use <code>iloc</code> to print the last row with <code>df.iloc[-1]</code>.</li>
<li>Use <code>loc</code> with a condition to print the revenue-related column <code>unit_price</code> for Kampala sales.</li>
</ol>

<h3>Common mistakes</h3>
<ul>
<li>Using single brackets with a list of columns; you need double brackets.</li>
<li>Forgetting that loc includes the end of a slice and iloc does not.</li>
<li>Assigning to a chained selection instead of using loc.</li>
</ul>

<h3>Key points</h3>
<ul>
<li>df["col"] gives a Series; df[["a", "b"]] gives a DataFrame.</li>
<li>loc selects by label and includes the slice end; iloc selects by position and excludes it.</li>
<li>Use loc with a condition and a column to read or change specific cells.</li>
<li>Work on df.copy() when you are experimenting.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Filtering with boolean masks, and sorting',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to answer questions such as "which sales were at least 10 units?" by filtering rows, and to put results in order. Filtering and sorting are the heart of exploring a table.</p>
<pre><code>import pandas as pd
df = pd.read_csv("sales.csv")</code></pre>

<h3>A boolean mask</h3>
<p>A comparison on a column gives a Series of True and False, one value per row. This is the <strong>mask</strong>. Putting it inside square brackets keeps only the True rows.</p>
<pre><code>mask = df["units"] &gt;= 10
big = df[mask]
print(big[["shop", "product", "units"]])</code></pre>
<pre>      shop   product  units
0     Gulu  Notebook     10
1  Kampala       Pen     50
4     Gulu       Pen     30
5  Kampala  Notebook     12</pre>
<p>Four of the eight sales have 10 or more units. The original row labels (0, 1, 4, 5) are kept.</p>

<h3>Combining conditions</h3>
<p>Use <code>&amp;</code> for and, <code>|</code> for or, and <code>~</code> for not. Each condition <strong>must be in brackets</strong> because of how Python orders its operators. Do not use the words <code>and</code> or <code>or</code> here.</p>
<pre><code>food_in_gulu = df[(df["shop"] == "Gulu") &amp; (df["category"] == "Food")]
print(food_in_gulu[["product", "units"]])</code></pre>
<pre>  product  units
2   Sugar      5
6    Rice      4</pre>

<h3>isin, between and query</h3>
<p><code>isin</code> checks membership in a list, <code>between</code> checks an inclusive range, and <code>query</code> lets you write the condition as a short string.</p>
<pre><code>chosen = df[df["product"].isin(["Pen", "Rice"])]
print(chosen[["product", "shop", "units"]])

print(df[df["units"].between(10, 30)]["product"].tolist())

print(df.query("units &gt; 10 and shop == 'Gulu'")[["product", "shop", "units"]])</code></pre>
<pre>   product     shop  units
1      Pen  Kampala     50
3     Rice  Kampala      8
4      Pen     Gulu     30
6     Rice     Gulu      4
['Notebook', 'Pen', 'Notebook']
  product  shop  units
4     Pen  Gulu     30</pre>
<p>The range 10 to 30 includes both ends, so the Notebook sale with exactly 10 units counts. In <code>query</code> you may write plain <code>and</code> and <code>or</code>, and put text values in quotes inside the string.</p>

<h3>Sorting</h3>
<p><code>sort_values</code> orders the rows by one or more columns. The default is ascending; set <code>ascending=False</code> for largest first.</p>
<pre><code>top = df.sort_values("units", ascending=False).head(3)
print(top[["product", "shop", "units"]])</code></pre>
<pre>    product     shop  units
1       Pen  Kampala     50
4       Pen     Gulu     30
5  Notebook  Kampala     12</pre>
<p>To sort by several columns give lists. Here shops are sorted A to Z, and within each shop the biggest sales come first:</p>
<pre><code>ordered = df.sort_values(["shop", "units"], ascending=[True, False])
print(ordered[["shop", "product", "units"]].head(4))</code></pre>
<pre>   shop   product  units
4  Gulu       Pen     30
0  Gulu  Notebook     10
2  Gulu     Sugar      5
6  Gulu      Rice      4</pre>
<p>Sorting returns a new DataFrame; it does not change <code>df</code> unless you assign the result back. <code>sort_index()</code> restores the original order, and <code>reset_index(drop=True)</code> renumbers the rows from 0.</p>

<h3>Quick answers</h3>
<p><code>df["units"].max()</code> gives 50, and <code>df["units"].idxmax()</code> gives the row label of that maximum, 1. Counting matches is easy because True counts as 1: <code>(df["units"] &gt;= 10).sum()</code> returns 4.</p>

<h3>Try it yourself</h3>
<ol>
<li>Show Kampala sales with more than 8 units. (Pen 50, Notebook 12.)</li>
<li>Show sales that are not in the Food category using <code>~</code> or <code>!=</code>.</li>
<li>Find the three cheapest unit prices using <code>sort_values</code>.</li>
<li>Count how many sales have a unit price of at least 4500. (4.)</li>
</ol>

<h3>Common mistakes</h3>
<ul>
<li>Writing <code>and</code> instead of <code>&amp;</code>, or forgetting the brackets around each condition.</li>
<li>Expecting sort_values to change the original without assignment.</li>
<li>Using a single equals sign inside a condition; comparisons need two.</li>
</ul>

<h3>Key points</h3>
<ul>
<li>A comparison creates a True/False mask; df[mask] keeps the True rows.</li>
<li>Combine conditions with &amp;, | and ~, each in brackets.</li>
<li>isin, between and query make common filters shorter.</li>
<li>sort_values returns a new sorted DataFrame; use ascending=False for largest first.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'New columns and data types',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to calculate new columns, check the type of each column and convert types when pandas guessed wrongly. Good types make calculations correct and memory use smaller.</p>
<pre><code>import numpy as np
import pandas as pd
df = pd.read_csv("sales.csv")</code></pre>

<h3>Creating columns from existing ones</h3>
<p>Assigning to a new column name creates it. Calculations run on whole columns at once, with no loop.</p>
<pre><code>df["revenue"] = df["units"] * df["unit_price"]
df["big_sale"] = df["revenue"] &gt;= 25000
print(df[["product", "revenue", "big_sale"]])
print(df["revenue"].sum())</code></pre>
<pre>    product  revenue  big_sale
0  Notebook    25000      True
1       Pen    25000      True
2     Sugar    22500     False
3      Rice    40000      True
4       Pen    15000     False
5  Notebook    30000      True
6      Rice    20000     False
7     Sugar    27000      True
204500</pre>
<p>Revenue is units multiplied by unit price for each row, for example 10 x 2500 = 25000. The <code>big_sale</code> column holds True or False. The total revenue is 204,500 shillings.</p>

<h3>Choosing between two labels: np.where</h3>
<p><code>np.where(condition, value_if_true, value_if_false)</code> builds a column from a condition.</p>
<pre><code>print(np.where(df["units"] &gt;= 10, "bulk", "single"))</code></pre>
<pre>['bulk' 'bulk' 'single' 'single' 'bulk' 'bulk' 'single' 'single']</pre>
<p>You can store the result in a new column the same way as before. For three or more labels, see <code>pd.cut</code> and <code>apply</code> in Module 4.</p>

<h3>Renaming and dropping</h3>
<pre><code>print(df.rename(columns={"unit_price": "price"}).columns.tolist())
print(df.drop(columns=["big_sale"]).columns.tolist())</code></pre>
<pre>['date', 'shop', 'product', 'category', 'units', 'price', 'revenue', 'big_sale']
['date', 'shop', 'product', 'category', 'units', 'unit_price', 'revenue']</pre>
<p>Both methods return a new DataFrame; <code>df</code> is unchanged until you assign the result back to it.</p>

<h3>Data types (dtypes)</h3>
<table>
<thead><tr><th>dtype</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td><code>int64</code></td><td>Whole numbers</td></tr>
<tr><td><code>float64</code></td><td>Decimal numbers (a column with missing numbers is float)</td></tr>
<tr><td><code>object</code></td><td>Text (or mixed values)</td></tr>
<tr><td><code>bool</code></td><td>True or False</td></tr>
<tr><td><code>datetime64[ns]</code></td><td>Dates and times</td></tr>
<tr><td><code>category</code></td><td>A small set of repeated labels, stored efficiently</td></tr>
</tbody>
</table>

<h3>Converting types</h3>
<pre><code>df["date"] = pd.to_datetime(df["date"])
df["category"] = df["category"].astype("category")
print(df.dtypes)</code></pre>
<pre>date          datetime64[ns]
shop                  object
product               object
category            category
units                  int64
unit_price             int64
revenue                int64
big_sale                bool
dtype: object</pre>
<p><code>pd.to_datetime</code> turns date text into real dates, so you can later extract months, compare dates and resample by time. <code>astype("category")</code> suits columns with few repeated values such as shop or category. For numbers use <code>astype(float)</code> or <code>pd.to_numeric</code>. Newer pandas versions may display a slightly different date type (for example <code>datetime64[us]</code>) and <code>str</code> instead of <code>object</code>; the idea is the same.</p>

<h3>Why types matter</h3>
<p>If a numbers column is stored as text, <code>sum()</code> joins the text together instead of adding, and sorting puts "100" before "20". Checking <code>df.dtypes</code> straight after loading prevents these puzzles.</p>

<h3>Try it yourself</h3>
<ol>
<li>Add a column <code>discounted</code> equal to <code>revenue * 0.9</code> and print its first three values (22500.0, 22500.0, 20250.0).</li>
<li>Add a column that says "bulk" or "single" using <code>np.where</code>.</li>
<li>Convert the <code>date</code> column and print <code>df["date"].dt.day_name().tolist()</code>. The first value should be Friday.</li>
<li>Print <code>df.dtypes</code> and say which columns hold text.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Assign to a new column name to create a calculated column.</li>
<li>np.where chooses between two values based on a condition.</li>
<li>Check dtypes after loading; convert with to_datetime, astype and to_numeric.</li>
<li>rename and drop return new DataFrames unless you assign the result.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What do you get when you select one column of a DataFrame with df["units"]?
- A DataFrame with one row
- A list of numbers
* A Series
- A NumPy shape
= Single brackets with one name return a Series.

Q: Which expression selects the first three rows and first two columns by position?
- df.loc[0:3, 0:2]
* df.iloc[0:3, 0:2]
- df.head[3, 2]
- df[0:3][0:2]
= iloc uses positions and excludes the slice end, so 0:3 gives three rows.

Q: What does df.loc[0:2] return?
- Rows 0 and 1
* Rows 0, 1 and 2 (three rows)
- Only row 2
- An error
= The end of a loc slice is included.

Q: Which filter keeps rows where shop is Gulu and units is above 5?
- df[df["shop"] == "Gulu" and df["units"] > 5]
- df[df["shop"] = "Gulu" & df["units"] > 5]
* df[(df["shop"] == "Gulu") & (df["units"] > 5)]
- df[df["shop"] == "Gulu", df["units"] > 5]
= Use & with each condition in brackets; the word and does not work on columns.

Q: Which call sorts the sales from the largest to the smallest units?
- df.sort_values("units")
* df.sort_values("units", ascending=False)
- df.sort("units", reverse=True)
- df.order_by("units")
= sort_values with ascending=False puts the largest first.

Q: In the sales data, how many rows does df[df["units"] >= 10] return?
- 2
- 3
* 4
- 8
= The units are 10, 50, 5, 8, 30, 12, 4 and 6; four of them (10, 50, 30, 12) are at least 10.

Q: Which line creates a revenue column from units and unit_price?
- df.revenue(units, unit_price)
* df["revenue"] = df["units"] * df["unit_price"]
- df["revenue"] == df["units"] * df["unit_price"]
- revenue = df.add("units", "unit_price")
= Assigning to a new column name with a column calculation creates it.

Q: A date column holds text such as "2025-01-03". Which call converts it to real dates?
- df["date"].astype(int)
- df["date"].sort_values()
* pd.to_datetime(df["date"])
- df["date"].round()
= to_datetime creates a datetime column so you can extract months and resample.
QUIZ,
        ],
        [
            'title'   => 'Module 3: Cleaning data',
            'summary' => 'Find and handle missing values, duplicates, wrong types, messy text and outliers, then combine tables with merge and concat.',
            'lessons' => [
                [
                    'title'   => 'Finding and handling missing values',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to find missing values, decide what to do with them and avoid being misled by them. Real data is rarely complete, and what you do about the gaps can change your conclusions.</p>

<h3>A messy table</h3>
<p>This small file of people has several problems on purpose. You will clean it through this module.</p>
<pre><code>import io
import pandas as pd

people_csv = """name,age,district,joined,income
 Akello Grace ,24,gulu,2025-01-05,350000
Okello Peter,31,Gulu,2025-01-07,
Nakato Sarah,,Kampala,2025-01-09,500000
Okello Peter,31,Gulu,2025-01-07,
Mugisha David,29,MBARARA,2025-02-10,420000
Atim Mercy,420,Gulu,2025-02-14,380000
Opio James,27,Lira,2025-02-20,310000
Auma Joy,35,lira,2025-03-01,450000
"""
people = pd.read_csv(io.StringIO(people_csv))</code></pre>
<p>Empty fields between commas become <code>NaN</code>, pandas' marker for a missing value.</p>

<h3>Counting and viewing the gaps</h3>
<pre><code>print(people.isna().sum())
print(people[people.isna().any(axis=1)])</code></pre>
<pre>name        0
age         1
district    0
joined      0
income      1
dtype: int64
           name   age  district      joined    income
1  Okello Peter  31.0      Gulu  2025-01-07       NaN
2  Nakato Sarah   NaN   Kampala  2025-01-09  500000.0
3  Okello Peter  31.0      Gulu  2025-01-07       NaN</pre>
<p><code>isna()</code> marks each missing cell True; <code>sum()</code> counts them per column. <code>any(axis=1)</code> asks "does this row have any missing value?", so the second line prints those rows. Notice that <code>age</code> and <code>income</code> now show decimals such as 31.0, because a column containing NaN is stored as float.</p>

<h3>Why are values missing?</h3>
<p>Before acting, think about the reason. A person may have refused to answer, a field may not apply, a form may have been skipped, or someone may have forgotten to type it. The reason decides the fix. Never assume that missing means zero.</p>

<h3>Option 1: drop</h3>
<pre><code>print(people.dropna().shape)
print(people.dropna(subset=["income"]).shape)</code></pre>
<pre>(5, 5)
(6, 5)</pre>
<p><code>dropna()</code> removes every row with any gap, leaving 5 of 8 rows, which is a lot of data lost. Using <code>subset</code> drops a row only when the named column is missing. Dropping is sensible when few rows are affected and the gaps look random.</p>

<h3>Option 2: fill</h3>
<p><code>fillna</code> replaces gaps. Choose the replacement carefully:</p>
<pre><code>print(people["age"].mean().round(1))
print(people["age"].median())</code></pre>
<pre>85.3
31.0</pre>
<p>The mean age is 85.3 because of the impossible value 420 (a typing error you will handle shortly). The <strong>median</strong>, the middle value, is 31.0 and is hardly affected by that outlier. When data has extreme values the median is usually the safer fill value. You fill with <code>people["age"].fillna(people["age"].median())</code>. Other approaches include filling with a fixed label such as "Unknown" for text, or <code>ffill()</code> to copy the previous value forward in time-ordered data.</p>

<h3>Option 3: leave it</h3>
<p>Many pandas functions ignore NaN automatically; <code>mean()</code>, <code>sum()</code> and <code>count()</code> skip missing values. This is often exactly right. Just remember the result is based on fewer rows, so report how many values were missing.</p>

<h3>Missing values hidden as text</h3>
<p>Sometimes gaps are written as "n/a", "-", "unknown" or 999. pandas does not know these are missing. Tell <code>read_csv</code> about them: <code>pd.read_csv("file.csv", na_values=["n/a", "-", "unknown"])</code>. Check unusual values with <code>df["col"].value_counts()</code> or <code>df["col"].unique()</code>.</p>

<h3>Try it yourself</h3>
<ol>
<li>Load the people data and run <code>isna().sum()</code>.</li>
<li>Print the rows where income is missing. (Rows 1 and 3.)</li>
<li>Compute the median income of the people who have an income. (400000.0.)</li>
<li>Write one sentence explaining whether you would drop or fill the missing income and why.</li>
</ol>

<h3>Common mistakes</h3>
<ul>
<li>Filling with zero when zero is a real, different value.</li>
<li>Dropping rows without checking how many you lose.</li>
<li>Forgetting that "n/a" text is not seen as missing.</li>
</ul>

<h3>Key points</h3>
<ul>
<li>isna().sum() counts missing values per column.</li>
<li>Decide on the reason first, then drop, fill or leave the gaps.</li>
<li>The median is a safer fill value than the mean when outliers exist.</li>
<li>Use na_values to tell read_csv about missing markers written as text.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Duplicates, types and dates',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to remove duplicate rows, convert text to numbers and dates, and put together a repeatable cleaning step. Duplicates inflate totals, and wrong types break calculations, so both are worth checking in every dataset.</p>
<p>We continue with the <code>people</code> table from the last lesson (load it again with the code there if you need to).</p>

<h3>Finding duplicates</h3>
<pre><code>print(people.duplicated().sum())
print(people[people.duplicated(keep=False)][["name", "joined"]])</code></pre>
<pre>1
           name      joined
1  Okello Peter  2025-01-07
3  Okello Peter  2025-01-07</pre>
<p><code>duplicated()</code> marks a row True when an identical row appeared earlier. One row is a repeat. Adding <code>keep=False</code> marks all copies so you can see both. Think before you delete: two different people can share a name. Here every column matches, so it is a true duplicate. To compare only some columns use <code>subset</code>, for example <code>duplicated(subset=["name", "joined"])</code>.</p>

<h3>Removing them</h3>
<pre><code>people = people.drop_duplicates().reset_index(drop=True)
print(people.shape)</code></pre>
<pre>(7, 5)</pre>
<p><code>reset_index(drop=True)</code> renumbers the rows 0 to 6 and discards the old numbers. The row order of the remaining rows is unchanged.</p>

<h3>Order matters: the median changed</h3>
<p>Remember the median age was 31.0 before? The duplicate Okello Peter counted twice. Now compute it again:</p>
<pre><code>print(people["age"].median())</code></pre>
<pre>30.0</pre>
<p>The six ages that exist are 24, 27, 29, 31, 35 and 420. The middle two are 29 and 31, so the median is 30.0. The lesson is to remove duplicates <em>before</em> you calculate values you will use for filling.</p>
<pre><code>people["age"] = people["age"].fillna(people["age"].median())
print(people["age"].tolist())</code></pre>
<pre>[24.0, 31.0, 30.0, 29.0, 420.0, 27.0, 35.0]</pre>
<p>Nakato Sarah's missing age was replaced with 30.0.</p>

<h3>Converting dates</h3>
<pre><code>people["joined"] = pd.to_datetime(people["joined"])
print(people["joined"].dt.month.tolist())
print(people.dtypes)</code></pre>
<pre>[1, 1, 1, 2, 2, 2, 3]
name                object
age                float64
district            object
joined      datetime64[ns]
income             float64
dtype: object</pre>
<p>Once <code>joined</code> is a real date, the <code>.dt</code> accessor gives parts such as <code>.dt.month</code>, <code>.dt.year</code> and <code>.dt.day_name()</code>. Dates written in other layouts need a format code, otherwise 05/02/2025 could be read as 2 May:</p>
<pre><code>raw = pd.Series(["05/02/2025", "17/03/2025"])
parsed = pd.to_datetime(raw, format="%d/%m/%Y")
print(parsed.dt.strftime("%d %B %Y").tolist())</code></pre>
<pre>['05 February 2025', '17 March 2025']</pre>
<p>Here <code>%d</code> is the day, <code>%m</code> the month and <code>%Y</code> the four-digit year. Stating the format removes the guesswork. To turn impossible dates into missing values instead of an error, add <code>errors="coerce"</code>.</p>

<h3>Numbers stored as text</h3>
<pre><code>raw_income = pd.Series(["1,200,000", "850,000", "n/a"])
clean = pd.to_numeric(raw_income.str.replace(",", ""), errors="coerce")
print(clean.tolist())</code></pre>
<pre>[1200000.0, 850000.0, nan]</pre>
<p>The commas are removed with the string method <code>replace</code>, and <code>to_numeric</code> converts the text. With <code>errors="coerce"</code> the "n/a" becomes NaN rather than stopping your program.</p>

<h3>Keep the steps together</h3>
<p>Put your cleaning steps in one cell, in the order you decided, so that anyone can rerun them from the raw file. Never edit the raw file by hand.</p>

<h3>Try it yourself</h3>
<ol>
<li>Reload the raw people data and count duplicates.</li>
<li>Drop them and check that the shape is (7, 5).</li>
<li>Convert the <code>joined</code> column and print the day names with <code>.dt.day_name().tolist()</code>.</li>
<li>Convert <code>["2,500", "900", "1,100"]</code> into numbers. (2500, 900, 1100.)</li>
</ol>

<h3>Key points</h3>
<ul>
<li>duplicated finds repeated rows; drop_duplicates removes them.</li>
<li>Remove duplicates before computing fill values such as the median.</li>
<li>to_datetime with a format string converts dates; the dt accessor extracts parts.</li>
<li>to_numeric with errors="coerce" converts text numbers and turns bad values into NaN.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Cleaning text and spotting outliers',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to tidy messy text columns and find values that are suspiciously large or small. Inconsistent spelling splits one group into several, and a single typing error can distort every average.</p>
<p>Continue with <code>people</code> as cleaned so far (duplicates removed, index reset, missing ages filled with 30.0, dates converted). Run the steps from the last lesson again if your notebook was closed.</p>

<h3>The str accessor</h3>
<p>String methods live under <code>.str</code> and work on a whole column. Look at the district column and the names:</p>
<pre><code>print(people["district"].tolist())
print(people["name"].tolist()[0])</code></pre>
<pre>['gulu', 'Gulu', 'Kampala', 'MBARARA', 'Gulu', 'Lira', 'lira']
 Akello Grace </pre>
<p>"gulu" and "Gulu" would be counted as different districts, and the first name has spaces at both ends. Clean both:</p>
<pre><code>people["name"] = people["name"].str.strip()
people["district"] = people["district"].str.strip().str.title()
print(people["district"].tolist())
print(people["district"].value_counts().head(2))</code></pre>
<pre>['Gulu', 'Gulu', 'Kampala', 'Mbarara', 'Gulu', 'Lira', 'Lira']
district
Gulu    3
Lira    2
Name: count, dtype: int64</pre>
<p><code>strip()</code> removes spaces at the ends and <code>title()</code> makes "MBARARA" into "Mbarara". Now Gulu appears three times, as it should.</p>

<h3>More useful string methods</h3>
<table>
<thead><tr><th>Method</th><th>Does</th></tr></thead>
<tbody>
<tr><td><code>.str.lower()</code>, <code>.str.upper()</code></td><td>Change case</td></tr>
<tr><td><code>.str.replace("a", "b")</code></td><td>Replace text</td></tr>
<tr><td><code>.str.contains("Ok")</code></td><td>True where the text contains the pattern</td></tr>
<tr><td><code>.str.split(" ")</code></td><td>Split into parts</td></tr>
<tr><td><code>.str.len()</code></td><td>Length of each text</td></tr>
</tbody>
</table>
<pre><code>print(people["name"].str.split(" ").str[0].tolist())
print(people["name"].str.contains("Okello").sum())</code></pre>
<pre>['Akello', 'Okello', 'Nakato', 'Mugisha', 'Atim', 'Opio', 'Auma']
1</pre>
<p>The first line takes the first word of each name; the second counts names containing "Okello".</p>

<h3>What is an outlier?</h3>
<p>An <strong>outlier</strong> is a value far from the others. It may be a typing mistake (age 420), a measurement fault, or a true but unusual case (one very rich customer). You must decide which, and the decision needs knowledge of the data, not only code.</p>

<h3>The IQR rule</h3>
<p>A common test uses the quartiles: Q1 (the value 25 percent of the way up the sorted data) and Q3 (75 percent). The <strong>interquartile range</strong> is IQR = Q3 - Q1. A value is flagged when it is more than 1.5 x IQR below Q1 or above Q3.</p>
<pre><code>q1 = people["age"].quantile(0.25)
q3 = people["age"].quantile(0.75)
iqr = q3 - q1
low, high = q1 - 1.5 * iqr, q3 + 1.5 * iqr
print(q1, q3, low, high)

outliers = people[(people["age"] &lt; low) | (people["age"] &gt; high)]
print(outliers[["name", "age"]])</code></pre>
<pre>28.0 33.0 20.5 40.5
         name    age
4  Atim Mercy  420.0</pre>
<p>The ages sorted are 24, 27, 29, 30, 31, 35, 420. Q1 is 28.0 and Q3 is 33.0, so the IQR is 5 and the fence runs from 20.5 to 40.5. Only 420 lies outside.</p>

<h3>What to do with an outlier</h3>
<ol>
<li><strong>Investigate.</strong> Check the source. Was the age really 42?</li>
<li><strong>Fix</strong> it if the correct value is known.</li>
<li><strong>Mark it missing</strong> if the value is impossible but the correct one is not known.</li>
<li><strong>Keep it</strong> if it is real, and report results with and without it.</li>
</ol>
<pre><code>people.loc[people["age"] &gt; 120, "age"] = float("nan")
print(people["age"].max())</code></pre>
<pre>35.0</pre>
<p>No human is older than 120, so we mark the value missing instead of guessing. Never delete outliers just because they are inconvenient.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create <code>pd.Series([" Gulu", "GULU ", "lira"])</code> and clean it into "Gulu", "Gulu", "Lira".</li>
<li>Use <code>str.contains</code> to count names that contain the letter "m" ignoring case (use <code>case=False</code>).</li>
<li>Apply the IQR rule to the income column. Are any values flagged? (None: the incomes lie between 310000 and 500000.)</li>
<li>Write what you would do if an income of 50,000,000 appeared.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>The str accessor cleans whole text columns: strip, lower, title, replace, contains, split.</li>
<li>Clean spelling and spacing before counting or grouping.</li>
<li>The IQR rule flags values beyond 1.5 times the IQR from the quartiles.</li>
<li>Investigate outliers; fix, mark missing or keep them, but never delete without a reason.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Merging and concatenating tables',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to join two tables on a shared column and stack tables on top of each other. Real analyses almost always need information from more than one file, such as people in one table and regions in another.</p>

<h3>merge: like a database join</h3>
<p><code>merge</code> matches rows of two tables by the values of a key column. We add a <code>region</code> to each person. Use the cleaned <code>people</code> table with tidy names and district spelling.</p>
<pre><code>regions = pd.DataFrame({
    "district": ["Gulu", "Lira", "Kampala", "Arua"],
    "region": ["Northern", "Northern", "Central", "Northern"],
})

merged = people.merge(regions, on="district", how="left")
print(merged[["name", "district", "region"]])</code></pre>
<pre>            name  district    region
0   Akello Grace      Gulu  Northern
1   Okello Peter      Gulu  Northern
2   Nakato Sarah   Kampala   Central
3  Mugisha David   Mbarara       NaN
4     Atim Mercy      Gulu  Northern
5     Opio James      Lira  Northern
6       Auma Joy      Lira  Northern</pre>
<p>Every person is kept. Mbarara is missing from the <code>regions</code> table, so Mugisha David gets NaN. Arua has no people, so it does not appear at all.</p>

<h3>The how option</h3>
<table>
<thead><tr><th>how</th><th>Keeps</th></tr></thead>
<tbody>
<tr><td><code>"inner"</code> (default)</td><td>Only keys found in both tables</td></tr>
<tr><td><code>"left"</code></td><td>All rows of the left table, with NaN where there is no match</td></tr>
<tr><td><code>"right"</code></td><td>All rows of the right table</td></tr>
<tr><td><code>"outer"</code></td><td>All rows from both tables</td></tr>
</tbody>
</table>
<p>With the default inner merge, Mugisha would vanish silently and you would have 6 rows instead of 7. That is why the next step matters.</p>

<h3>Check every merge</h3>
<pre><code>inner = people.merge(regions, on="district")
print(len(people), len(merged), len(inner))
print(merged["region"].isna().sum())</code></pre>
<pre>7 7 6
1</pre>
<p>A left merge should not change the number of rows when the key in the right table is unique. Here 7 stays 7, good, while the inner merge lost a row. If the row count grows, the right table has duplicate keys and rows were multiplied. You can ask pandas to check this: <code>merge(..., validate="many_to_one")</code> raises an error if the right table has repeated keys. The option <code>indicator=True</code> adds a <code>_merge</code> column that tells you whether each row matched in both tables or only in the left one.</p>
<p>Keys must be cleaned first. "gulu" and "Gulu" would not match, which is one reason to clean text before merging. If the key columns have different names, use <code>left_on="a", right_on="b"</code>.</p>

<h3>concat: stacking tables</h3>
<p>Use <code>pd.concat</code> to put tables with the same columns on top of each other, such as one file per month:</p>
<pre><code>jan = pd.DataFrame({"product": ["Pen", "Sugar"], "units": [50, 5]})
feb = pd.DataFrame({"product": ["Pen", "Rice"], "units": [40, 8]})

both = pd.concat([jan, feb], ignore_index=True)
print(both)</code></pre>
<pre>  product  units
0     Pen     50
1   Sugar      5
2     Pen     40
3    Rice      8</pre>
<p><code>ignore_index=True</code> gives clean numbering 0 to 3. Without it the second block would repeat the labels 0 and 1. If a column exists in only one table, the other rows get NaN there. Concatenating many files from a folder is a common pattern: read each into a DataFrame, collect them in a list and call <code>pd.concat</code> once on the list.</p>

<h3>Try it yourself</h3>
<ol>
<li>Build the <code>regions</code> table and merge it with <code>people</code> using <code>how="left"</code>.</li>
<li>Add the missing row <code>Mbarara, Western</code> to <code>regions</code> and merge again. How many missing regions remain? (0.)</li>
<li>Print the lengths before and after to confirm the row count did not change.</li>
<li>Concatenate two small tables of your own and use <code>ignore_index=True</code>.</li>
</ol>

<h3>Common mistakes</h3>
<ul>
<li>Merging on a column with different spelling or spaces, giving unexpected NaN.</li>
<li>Not checking row counts, so duplicated keys silently multiply rows.</li>
<li>Using an inner merge and losing rows without noticing.</li>
</ul>

<h3>Key points</h3>
<ul>
<li>merge joins tables on key columns; how controls which rows are kept.</li>
<li>Always compare row counts and missing values after a merge.</li>
<li>concat stacks tables with the same columns; use ignore_index=True for neat numbering.</li>
<li>Clean key columns before merging.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which expression counts missing values in each column?
- df.count_missing()
* df.isna().sum()
- df.missing.total()
- df.dropna().sum()
= isna marks missing cells and sum counts them per column.

Q: A column has a few extreme values. Which is usually the safer value for filling gaps?
- The maximum
- Zero
* The median
- The mean
= The median is hardly changed by extreme values, unlike the mean.

Q: What does df.dropna(subset=["income"]) do?
- Removes the income column
* Removes only rows where income is missing
- Removes all rows with any missing value
- Fills income with zero
= subset limits the check to the named columns.

Q: Which call removes repeated rows and renumbers the index from 0?
- df.duplicated().reset_index()
- df.drop_duplicates()
* df.drop_duplicates().reset_index(drop=True)
- df.sort_index()
= drop_duplicates removes repeats and reset_index(drop=True) gives new numbering without keeping the old numbers.

Q: Dates are written like 05/02/2025 meaning 5 February. Which format string reads them correctly?
- "%m/%d/%Y"
* "%d/%m/%Y"
- "%Y-%m-%d"
- "%d-%b-%y"
= %d is day, %m is month and %Y is the four-digit year in the order they appear.

Q: What does errors="coerce" do in pd.to_numeric or pd.to_datetime?
- Stops with an error message
- Fills with zero
* Turns values that cannot be converted into missing values
- Deletes those rows
= Coerce replaces unconvertible values with NaN or NaT instead of failing.

Q: After people.merge(regions, on="district", how="left"), one person has NaN in region. What is the most likely cause?
- The merge deleted the region column
* That person's district does not exist in the regions table
- The index was reset
- Left merges always create NaN
= A left merge keeps all left rows and gives NaN where there is no match.

Q: You find a value of age 420 in the data. What is the best first step?
- Delete the whole dataset
- Always replace it with the mean
* Check the source to see whether it is an error, then fix, mark it missing or keep it with a reason
- Ignore it
= Outliers need investigation because they may be mistakes or true unusual values.
QUIZ,
        ],
        [
            'title'   => 'Module 4: Summarising and reshaping',
            'summary' => 'Group and aggregate data, count categories, transform columns with apply and cut, build pivot tables and analyse simple time series.',
            'lessons' => [
                [
                    'title'   => 'groupby and aggregations',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to answer "per" questions: revenue per shop, units per product, average per group. This split-apply-combine pattern is the most useful idea in all of pandas.</p>
<pre><code>import pandas as pd
df = pd.read_csv("sales.csv")
df["revenue"] = df["units"] * df["unit_price"]</code></pre>

<h3>Split, apply, combine</h3>
<p><code>groupby</code> <strong>splits</strong> the rows into groups that share a value, <strong>applies</strong> a calculation to each group and <strong>combines</strong> the results into a new table.</p>
<pre><code>print(df.groupby("shop")["revenue"].sum())</code></pre>
<pre>shop
Gulu        82500
Kampala    122000
Name: revenue, dtype: int64</pre>
<p>Read it as "group by shop, take the revenue column, add it up". Gulu: 25000 + 22500 + 15000 + 20000 = 82500. Kampala earned 122,000. The two shops add to 204,500, which matches the grand total from before. A quick check like that is a good habit.</p>

<h3>Several statistics at once</h3>
<p>Use <code>agg</code> with <strong>named aggregation</strong>: each new column is given as <code>name=("column", "function")</code>.</p>
<pre><code>summary = df.groupby("shop").agg(
    sales=("revenue", "sum"),
    avg_units=("units", "mean"),
    biggest=("revenue", "max"),
)
print(summary)</code></pre>
<pre>          sales  avg_units  biggest
shop
Gulu      82500      12.25    25000
Kampala  122000      19.00    40000</pre>
<p>The shop names became the index. Call <code>summary.reset_index()</code> when you want them back as an ordinary column. Common function names are <code>"sum"</code>, <code>"mean"</code>, <code>"median"</code>, <code>"min"</code>, <code>"max"</code>, <code>"count"</code> and <code>"nunique"</code> (number of different values).</p>

<h3>Grouping by two columns</h3>
<p>Give a list. With <code>as_index=False</code> the group columns stay as normal columns:</p>
<pre><code>by_two = df.groupby(["shop", "category"], as_index=False)["revenue"].sum()
print(by_two)</code></pre>
<pre>      shop    category  revenue
0     Gulu        Food    42500
1     Gulu  Stationery    40000
2  Kampala        Food    67000
3  Kampala  Stationery    55000</pre>

<h3>Counting group sizes</h3>
<pre><code>print(df.groupby("shop").size())</code></pre>
<pre>shop
Gulu       4
Kampala    4
dtype: int64</pre>
<p><code>size()</code> counts all rows in each group, while <code>count()</code> counts only the non-missing values in a column. They differ when data has gaps.</p>

<h3>Sorting the answer and finding the best</h3>
<pre><code>by_product = df.groupby("product")["revenue"].sum().sort_values(ascending=False)
print(by_product)
print(by_product.idxmax())</code></pre>
<pre>product
Rice        60000
Notebook    55000
Sugar       49500
Pen         40000
Name: revenue, dtype: int64
Rice</pre>
<p>Rice earned the most (40000 + 20000 = 60000). <code>idxmax()</code> returns the label of the largest value, so you get the name of the best product directly.</p>

<h3>Missing values in groups</h3>
<p>Rows with a missing group key are dropped from the result by default. Rows with a missing value in the calculated column are skipped by <code>sum</code> and <code>mean</code>. Always check how many rows are in each group before trusting an average, because an average of two values is much weaker than an average of two hundred.</p>

<h3>Try it yourself</h3>
<ol>
<li>Find the total units sold per product. (Notebook 22, Pen 80, Rice 12, Sugar 11.)</li>
<li>Find the average unit price per category. (Food 4750.0, Stationery 1500.0.)</li>
<li>Use named aggregation to get total revenue and number of sales per shop in one table.</li>
<li>Find which category earned the most with <code>idxmax()</code>. (Food: 109500 against Stationery 95000.)</li>
</ol>

<h3>Common mistakes</h3>
<ul>
<li>Forgetting to select the column, so pandas tries to sum text columns too.</li>
<li>Trusting averages from very small groups.</li>
<li>Forgetting that the group keys become the index.</li>
</ul>

<h3>Key points</h3>
<ul>
<li>groupby splits rows into groups, applies a calculation and combines the results.</li>
<li>Named aggregation with agg builds a tidy summary table.</li>
<li>Group by a list of columns for multi-level summaries; as_index=False keeps ordinary columns.</li>
<li>Check group sizes and totals so that the summary makes sense.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'value_counts, apply and cut',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to count how often values occur, create labels from numbers and apply your own logic to a column. These tools turn raw values into categories you can group and chart.</p>

<h3>value_counts</h3>
<p><code>value_counts()</code> counts how many times each value appears, from most to least common.</p>
<pre><code>import pandas as pd

payment = pd.Series(["Mobile money", "Cash", "Mobile money", "Mobile money",
                     "Cash", "Bank", "Mobile money", "Cash"])
print(payment.value_counts())
print(payment.value_counts(normalize=True))</code></pre>
<pre>Mobile money    4
Cash            3
Bank            1
Name: count, dtype: int64
Mobile money    0.500
Cash            0.375
Bank            0.125
Name: proportion, dtype: float64</pre>
<p>Half the payments (4 of 8) were mobile money. With <code>normalize=True</code> you get shares that add to 1; multiply by 100 for percentages. By default, missing values are not counted; add <code>dropna=False</code> to count them. In older pandas versions the result series is not named "count", so the footer may look slightly different.</p>

<h3>cut: turn numbers into bands</h3>
<p><code>pd.cut</code> sorts numbers into ranges you define. The bins are written as edges, and each range includes its right edge by default.</p>
<pre><code>df = pd.read_csv("sales.csv")
price_band = pd.cut(df["unit_price"], bins=[0, 1000, 4000, 10000],
                    labels=["low", "mid", "high"])
print(price_band.tolist())</code></pre>
<pre>['mid', 'low', 'high', 'high', 'low', 'mid', 'high', 'high']</pre>
<p>The bands are 0 to 1000 (low), above 1000 up to 4000 (mid) and above 4000 up to 10000 (high). A price of 2500 is mid, 500 is low and 4500 or 5000 is high. A value outside every band, such as 20000, would become NaN, so make the outer edges generous. <code>pd.qcut</code> is a cousin that makes bands with equal numbers of rows (for example quartiles).</p>

<h3>map: replace using a dictionary</h3>
<pre><code>df["shop_code"] = df["shop"].map({"Gulu": "GLU", "Kampala": "KLA"})
print(df["shop_code"].tolist())</code></pre>
<pre>['GLU', 'KLA', 'GLU', 'KLA', 'GLU', 'KLA', 'GLU', 'KLA']</pre>
<p>Any value not in the dictionary becomes NaN, so check the result with <code>isna().sum()</code>.</p>

<h3>apply: your own function</h3>
<p>When the logic is too complex for a simple comparison, write a function and let <code>apply</code> call it on every value:</p>
<pre><code>def size_label(units):
    if units &gt;= 30:
        return "large"
    if units &gt;= 10:
        return "medium"
    return "small"

df["size_label"] = df["units"].apply(size_label)
print(df[["product", "units", "size_label"]])</code></pre>
<pre>    product  units size_label
0  Notebook     10     medium
1       Pen     50      large
2     Sugar      5      small
3      Rice      8      small
4       Pen     30      large
5  Notebook     12     medium
6      Rice      4      small
7     Sugar      6      small</pre>
<p>A sale of exactly 30 is "large" because the first test uses greater than or equal. Order your tests carefully.</p>

<h3>Prefer vectorised methods</h3>
<p><code>apply</code> runs Python code row by row, which is slow on millions of rows. When a built-in does the job (arithmetic, comparisons, <code>np.where</code>, <code>cut</code>, <code>map</code>, <code>str</code> methods), use it. Keep <code>apply</code> for logic that really needs a function. With <code>df.apply(function, axis=1)</code> the function receives a whole row, useful but slower still.</p>

<h3>Combining with groupby</h3>
<p>The new labels can be used to group:</p>
<pre><code>print(df.groupby("size_label")["revenue"].sum()) </code></pre>
<p>This needs a <code>revenue</code> column (units times unit price), so create it first as in the last lesson. The result lists total revenue for large, medium and small sales.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create a Series of 10 ratings from 1 to 5 and count each rating with <code>value_counts().sort_index()</code>.</li>
<li>Use <code>pd.cut</code> to label units as "few" (up to 9), "some" (10 to 29) and "many" (30 and above). The edges could be <code>[0, 9, 29, 1000]</code>.</li>
<li>Write a function that returns "peak" for sales of at least 25000 and "normal" otherwise, and apply it to the revenue column.</li>
<li>Count how many sales fall into each of your labels.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>value_counts counts values; normalize=True gives shares.</li>
<li>pd.cut turns numbers into labelled bands defined by edges.</li>
<li>map swaps values with a dictionary; apply runs your own function.</li>
<li>Use vectorised methods first because apply is slow on big data.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Pivot tables',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a pivot table in pandas, a grid with one dimension down the side and another across the top, just like the pivot tables in Excel. It is the clearest way to compare two categories at once.</p>
<pre><code>import pandas as pd
df = pd.read_csv("sales.csv")
df["revenue"] = df["units"] * df["unit_price"]</code></pre>

<h3>The idea</h3>
<p>Earlier you grouped revenue by shop and category and got four rows. A pivot table lays the same numbers out as a grid, which is easier to read: shops as rows and categories as columns.</p>

<h3>pivot_table</h3>
<pre><code>pt = df.pivot_table(index="shop", columns="category", values="revenue",
                    aggfunc="sum", margins=True, margins_name="Total")
print(pt.rename_axis(columns=None).reset_index())</code></pre>
<pre>      shop    Food  Stationery   Total
0     Gulu   42500       40000   82500
1  Kampala   67000       55000  122000
2    Total  109500       95000  204500</pre>
<p>The parts are:</p>
<ul>
<li><code>index</code>: what goes down the side (the rows).</li>
<li><code>columns</code>: what goes across the top.</li>
<li><code>values</code>: the numbers to summarise.</li>
<li><code>aggfunc</code>: how to summarise ("sum", "mean", "count", "max" and so on).</li>
<li><code>margins=True</code>: adds a total row and column; <code>margins_name</code> names them.</li>
</ul>
<p>The last line removes the extra label from the column axis and turns the shop names back into an ordinary column, which is tidier to print and to save. Check the answer: Gulu Food is 22500 + 20000 = 42500, and the grand total 204500 equals the sum of all revenue.</p>

<h3>Empty cells and fill_value</h3>
<p>If a shop never sold a category, that cell would be NaN. Use <code>fill_value=0</code> when zero is the honest answer for "no sales". In our data every combination exists, so there are no gaps.</p>

<h3>More than one statistic</h3>
<p>Give <code>aggfunc</code> a list to see several, or a dictionary to use a different function for each value column:</p>
<pre><code>pt2 = df.pivot_table(index="shop", values=["units", "revenue"],
                     aggfunc={"units": "sum", "revenue": "mean"})
print(pt2)</code></pre>
<pre>         revenue  units
shop
Gulu     20625.0     49
Kampala  30500.0     76</pre>
<p>Gulu's average revenue per sale is 82500 / 4 = 20625, and its total units are 10 + 5 + 30 + 4 = 49. Kampala: 122000 / 4 = 30500 and 50 + 8 + 12 + 6 = 76 units. The columns are shown in alphabetical order.</p>

<h3>crosstab: counting combinations</h3>
<p><code>pd.crosstab(df["shop"], df["category"])</code> counts how many rows have each combination. In this data each shop has two Food sales and two Stationery sales, so every cell holds 2. Add <code>normalize="index"</code> to see each row as shares of 1.</p>

<h3>groupby or pivot_table?</h3>
<p>They do the same calculation. <code>groupby</code> gives a long, tidy list, best for further processing and charts. <code>pivot_table</code> gives a wide grid, best for reading and reports. You can move between them: <code>unstack()</code> turns a two-level groupby result into a grid, and <code>melt()</code> turns a wide grid back into a long list.</p>

<h3>Try it yourself</h3>
<ol>
<li>Build the pivot table above and check one cell by hand.</li>
<li>Create a pivot of the average unit price with products as rows and shops as columns. (Prices are the same in both shops, so each cell is that product's price.)</li>
<li>Use <code>aggfunc="count"</code> with <code>values="units"</code> to count sales per shop and category.</li>
<li>Explain in your own words when you would choose a pivot table instead of groupby.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>pivot_table puts one category down the side, another across the top and a summary in each cell.</li>
<li>aggfunc chooses the calculation; margins=True adds totals; fill_value handles gaps.</li>
<li>crosstab counts combinations of two columns.</li>
<li>Use groupby for tidy lists and pivot tables for reports; always check a cell by hand.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Dates and simple time series',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to work with dates in pandas, pick out a period of time and total data by month with resample. Many business questions are about time: are sales growing, which month was the busiest, is there a weekly pattern?</p>

<h3>A date-indexed table</h3>
<p>Here is a small file of the shop's takings on some days. We read the date column as real dates and make it the index.</p>
<pre><code>import io
import pandas as pd

csv_text = """date,amount
2025-01-05,120000
2025-01-20,95000
2025-02-02,110000
2025-02-17,130000
2025-02-25,90000
2025-03-04,150000
2025-03-18,140000
2025-03-29,160000
"""
daily = pd.read_csv(io.StringIO(csv_text), parse_dates=["date"])
daily = daily.set_index("date")</code></pre>
<p>A table whose index is made of dates is called a <strong>time series</strong>. With a date index you gain powerful shortcuts.</p>

<h3>Selecting a period</h3>
<p>pandas understands partial dates written as text:</p>
<pre><code>print(daily.loc["2025-02"])</code></pre>
<pre>            amount
date
2025-02-02  110000
2025-02-17  130000
2025-02-25   90000</pre>
<p>You can also give a range with <code>daily.loc["2025-01-15":"2025-02-15"]</code>, which includes both ends. For this, the index should be sorted by date, which you can ensure with <code>sort_index()</code>.</p>

<h3>resample: totals per period</h3>
<p><code>resample</code> is groupby for time. It cuts the series into regular periods and applies a calculation to each.</p>
<pre><code>monthly = daily["amount"].resample("MS").sum()
print(monthly)</code></pre>
<pre>date
2025-01-01    215000
2025-02-01    330000
2025-03-01    450000
Freq: MS, Name: amount, dtype: int64</pre>
<p>The code <code>"MS"</code> means "month start": each month is labelled by its first day. January is 120000 + 95000 = 215000; February is 110000 + 130000 + 90000 = 330000; March is 150000 + 140000 + 160000 = 450000.</p>
<table>
<thead><tr><th>Code</th><th>Period</th></tr></thead>
<tbody>
<tr><td><code>"D"</code></td><td>Day</td></tr>
<tr><td><code>"W"</code></td><td>Week (ending Sunday)</td></tr>
<tr><td><code>"MS"</code></td><td>Month, labelled by its first day</td></tr>
<tr><td><code>"ME"</code></td><td>Month, labelled by its last day (pandas 2.2 and newer; older versions write "M")</td></tr>
<tr><td><code>"YS"</code></td><td>Year, labelled by its first day</td></tr>
</tbody>
</table>
<p>Days with no data produce 0 for a sum, or NaN for a mean. That honest gap is useful: you see which periods are empty.</p>

<h3>Growth from one period to the next</h3>
<pre><code>growth = (monthly.pct_change() * 100).round(1)
print(growth)</code></pre>
<pre>date
2025-01-01     NaN
2025-02-01    53.5
2025-03-01    36.4
Freq: MS, Name: amount, dtype: float64</pre>
<p><code>pct_change()</code> compares each period with the one before. February is 330000 / 215000 - 1 = 0.535, which is 53.5 percent growth. March grew 36.4 percent over February. The first month has no earlier month, so it is NaN.</p>

<h3>Date parts and weekdays</h3>
<p>For a normal column of dates (not the index) use the <code>.dt</code> accessor, as you did earlier. On a date index use the index directly:</p>
<pre><code>print(daily.index.month.tolist())
sales = pd.read_csv("sales.csv", parse_dates=["date"])
print(sales["date"].dt.day_name().tolist())</code></pre>
<pre>[1, 1, 2, 2, 2, 3, 3, 3]
['Friday', 'Friday', 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday']</pre>
<p>Grouping by weekday (<code>sales.groupby(sales["date"].dt.day_name())</code>) is a quick way to find out which days are busiest.</p>

<h3>Try it yourself</h3>
<ol>
<li>Type in the daily takings and select only March with <code>loc</code>. (Three rows.)</li>
<li>Resample to months and compute the mean instead of the sum. (January 107500.0, February 110000.0, March 150000.0.)</li>
<li>Calculate the percentage change and write one sentence describing the trend.</li>
<li>Count how many entries fall on each weekday using <code>.index.day_name().value_counts()</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use parse_dates and set_index to create a date-indexed time series.</li>
<li>Partial strings such as "2025-02" select a whole month.</li>
<li>resample("MS").sum() totals per month; pct_change shows growth between periods.</li>
<li>Use the .dt accessor on date columns for months, years and weekday names.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does df.groupby("shop")["revenue"].sum() return?
- The total revenue of the whole table as one number
* The total revenue for each shop
- The number of shops
- The shop with the largest revenue
= It splits by shop, sums revenue in each group and combines the results.

Q: In named aggregation, what does sales=("revenue", "sum") mean?
- Create a column called revenue holding the word sum
* Create a result column called sales that adds up the revenue column
- Sort by sales
- Rename the revenue column to sum
= The pattern is new_name=("column", "function").

Q: How do size() and count() differ in a group?
- They are identical
* size counts all rows; count counts only non-missing values of a column
- size is only for text columns
- count includes missing values and size does not
= The difference shows up when a column has gaps.

Q: What does pivot_table(index="shop", columns="category", values="revenue", aggfunc="sum", margins=True) add?
- A chart
* Total row and column
- A new index
- Sorted data
= margins=True adds totals for each row and column.

Q: What does daily["amount"].resample("MS").sum() do?
- Sums the whole column into one number
* Totals the amounts for each month, labelled by the first day of the month
- Drops months without data
- Sorts the amounts
= resample is groupby for time; MS means month start.

Q: What does payment.value_counts(normalize=True) return?
- The counts as whole numbers
* The share of each value, adding up to 1
- Only the most common value
- The number of unique values
= normalize=True converts counts into proportions.

Q: A sale of exactly 30 units must be labelled "large" when 30 or more is large. What should the apply function do?
- Test units less than 30 first and return large
* Test units greater than or equal to 30 before the other labels
- Use the word and between the tests
- Return the labels in any order
= The first condition that is true wins, so order and the boundary operator matter.

Q: Which is usually the best choice for a simple two-way label such as bulk or single?
- A for loop over rows
- apply with a long function
* np.where on the whole column
- Deleting rows by hand
= A vectorised method is shorter and much faster than row-by-row code.
QUIZ,
        ],
        [
            'title'   => 'Module 5: Visualising and reporting',
            'summary' => 'Draw bar, line, histogram and scatter charts with matplotlib, label them honestly, export results, complete a survey project and handle data ethically.',
            'lessons' => [
                [
                    'title'   => 'matplotlib basics: bar, line, histogram and scatter',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to draw the four most useful charts with matplotlib and choose the right one for a question. A good chart shows a pattern in seconds that a table of numbers hides.</p>

<h3>Figure and axes</h3>
<p>matplotlib draws on a <strong>figure</strong> (the whole picture) that holds one or more <strong>axes</strong> (a single chart with its x and y scales). The clearest way to work is:</p>
<pre><code>import matplotlib.pyplot as plt

fig, ax = plt.subplots(figsize=(6, 4))
# draw on ax here
plt.show()</code></pre>
<p><code>figsize</code> is width and height in inches. In Jupyter and Colab the chart appears under the cell automatically; in a plain script <code>plt.show()</code> opens a window. The examples below use the sales data:</p>
<pre><code>import pandas as pd
df = pd.read_csv("sales.csv")
df["revenue"] = df["units"] * df["unit_price"]</code></pre>

<h3>Bar chart: compare categories</h3>
<pre><code>by_product = df.groupby("product")["revenue"].sum().sort_values(ascending=False)

fig, ax = plt.subplots(figsize=(6, 4))
ax.bar(by_product.index, by_product.values)
ax.set_title("Revenue by product, 3 to 9 January 2025")
ax.set_ylabel("Revenue (UGX)")
plt.show()</code></pre>
<p>You see four bars, from the tallest Rice (60,000) down through Notebook (55,000) and Sugar (49,500) to Pen (40,000). Sorting the bars makes comparison easy. For long names, <code>ax.barh</code> draws horizontal bars.</p>

<h3>Line chart: change over time</h3>
<pre><code>daily = pd.DataFrame({
    "date": pd.to_datetime(["2025-01-01", "2025-02-01", "2025-03-01"]),
    "amount": [215000, 330000, 450000],
})
fig, ax = plt.subplots(figsize=(6, 4))
ax.plot(daily["date"], daily["amount"], marker="o")
ax.set_ylabel("Takings (UGX)")
plt.show()</code></pre>
<p>A line rising from left to right through three dots shows growth over the three months. Use lines only when the x-axis is ordered, usually time.</p>

<h3>Histogram: the shape of one number</h3>
<pre><code>fig, ax = plt.subplots(figsize=(6, 4))
counts, edges, patches = ax.hist(df["units"], bins=4)
print(counts)
plt.show()</code></pre>
<pre>[6. 0. 1. 1.]</pre>
<p>A histogram cuts the number range into equal bins and counts how many values fall in each. The units run from 4 to 50, so with 4 bins six sales fall in the first bin (4 to 15.5), none in the second, one in the third and one in the last. The picture is a tall bar on the left and two short ones on the right: most sales are small, a few are large. Changing <code>bins</code> changes the picture, so try several.</p>

<h3>Scatter plot: two numbers together</h3>
<pre><code>fig, ax = plt.subplots(figsize=(6, 4))
ax.scatter(df["units"], df["revenue"])
ax.set_xlabel("Units sold")
ax.set_ylabel("Revenue (UGX)")
plt.show()</code></pre>
<p>Each of the eight sales is one dot. Dots drifting upward to the right show that bigger sales generally earn more, though not perfectly because prices differ.</p>

<h3>Choosing a chart</h3>
<table>
<thead><tr><th>Question</th><th>Chart</th></tr></thead>
<tbody>
<tr><td>Which category is bigger?</td><td>Bar</td></tr>
<tr><td>How did it change over time?</td><td>Line</td></tr>
<tr><td>How are values spread?</td><td>Histogram</td></tr>
<tr><td>Are two numbers related?</td><td>Scatter</td></tr>
</tbody>
</table>

<h3>A quick shortcut</h3>
<p>pandas can draw too, using matplotlib underneath: <code>by_product.plot(kind="bar")</code>. It is handy for quick looks; the figure-and-axes style gives you full control for reports.</p>

<h3>Try it yourself</h3>
<ol>
<li>Draw the sorted bar chart of revenue by product.</li>
<li>Draw a horizontal bar chart (<code>barh</code>) of revenue by shop.</li>
<li>Draw a histogram of <code>unit_price</code> with 3 bins and describe it in a sentence.</li>
<li>Draw a scatter plot of unit price against units. Is there a clear pattern?</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use fig, ax = plt.subplots() and draw on ax.</li>
<li>Bar compares categories, line shows time, histogram shows spread, scatter shows relationships.</li>
<li>Sort bars and use only ordered x-axes for lines.</li>
<li>Try different bin counts because they change a histogram's look.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Honest charts: labels, scales and exporting',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to label charts so that anyone can understand them, avoid the common ways charts mislead, and save your charts and tables to files. A chart is a claim about the world, so it must be both clear and honest.</p>

<h3>Label everything</h3>
<p>A reader who sees your chart alone, without your notebook, should know what it shows. Always include:</p>
<ul>
<li>A <strong>title</strong> that states the point or the subject and period.</li>
<li><strong>Axis labels</strong> with units, such as "Revenue (UGX)".</li>
<li>A <strong>source</strong> or note when useful, for example "Source: shop records, January 2025, 8 sales".</li>
<li>A <strong>legend</strong> when several series share a chart.</li>
</ul>
<pre><code>import matplotlib.pyplot as plt
from matplotlib.ticker import StrMethodFormatter

fig, ax = plt.subplots(figsize=(6, 4))
ax.bar(["Gulu", "Kampala"], [82500, 122000], color="#2a6f97")
ax.set_title("Revenue by shop, 3 to 9 January 2025")
ax.set_ylabel("Revenue (UGX)")
ax.yaxis.set_major_formatter(StrMethodFormatter("{x:,.0f}"))
ax.set_ylim(0, 140000)
for position, value in enumerate([82500, 122000]):
    ax.text(position, value + 2000, f"{value:,}", ha="center")
plt.show()</code></pre>
<p>This draws two bars labelled with their totals, 82,500 and 122,000, with thousands separators on the y-axis. The formatter <code>"{x:,.0f}"</code> adds commas and no decimals. The <code>ax.text</code> loop writes each value just above its bar.</p>

<h3>Misleading charts to avoid</h3>
<ul>
<li><strong>Truncated bar axis.</strong> Starting a bar chart at 80,000 instead of 0 makes a small difference look huge. Bars encode their value by length, so they must start at zero (<code>ax.set_ylim(0, ...)</code>). Lines and scatter plots may start elsewhere if the axis is clearly labelled.</li>
<li><strong>Too many slices.</strong> Pie charts with more than three or four slices, or similar-sized slices, are hard to compare. A sorted bar chart is almost always clearer.</li>
<li><strong>3D effects and decoration.</strong> They distort sizes and distract.</li>
<li><strong>Cherry-picked periods.</strong> Showing only the months that look good hides the real story.</li>
<li><strong>Colour that excludes people.</strong> Avoid relying on red against green; many people cannot tell them apart. Use one calm colour, and a second contrasting colour only when you need it.</li>
<li><strong>Hiding the sample size.</strong> An average based on eight sales is not the same as one based on eight thousand. Say how many values you used.</li>
</ul>

<h3>Saving a chart</h3>
<p>Use <code>fig.savefig</code>. In a plain script call it <em>before</em> <code>plt.show()</code>, because show can clear the figure and leave you with a blank file.</p>
<pre><code>fig.savefig("revenue_by_shop.png", dpi=150, bbox_inches="tight")</code></pre>
<p><code>dpi=150</code> gives a sharp image for documents; <code>bbox_inches="tight"</code> trims extra white space. Save as <code>.svg</code> or <code>.pdf</code> for graphics that must scale without blurring. When you place a chart in a report, add a short sentence describing what it shows, which also helps readers who use screen readers.</p>

<h3>Saving tables</h3>
<pre><code>import pandas as pd

by_shop = pd.DataFrame({"shop": ["Gulu", "Kampala"], "revenue": [82500, 122000]})
by_shop.to_csv("revenue_by_shop.csv", index=False)</code></pre>
<p>The file contains exactly these lines:</p>
<pre>shop,revenue
Gulu,82500
Kampala,122000</pre>
<p><code>index=False</code> stops the row numbers from being written as an extra column. To create an Excel file use <code>by_shop.to_excel("revenue_by_shop.xlsx", index=False)</code>; this needs the <code>openpyxl</code> package, installed with <code>python -m pip install openpyxl</code>.</p>

<h3>Reporting your findings</h3>
<p>A short written report might have: the question, the data (source, dates, size, problems found), the method in one or two sentences, two or three charts with captions, the answer in plain words, the limitations and a recommendation. State what you do not know as clearly as what you found.</p>

<h3>Try it yourself</h3>
<ol>
<li>Recreate the labelled bar chart above, then remove the zero baseline by setting <code>ax.set_ylim(80000, 130000)</code> and see how it exaggerates the gap.</li>
<li>Restore the zero baseline and save the chart as a PNG.</li>
<li>Export your by-shop table to CSV and open it in a spreadsheet.</li>
<li>Write a two-sentence caption for the chart that states the finding and the number of sales behind it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Every chart needs a title, labelled axes with units and, where helpful, a source and sample size.</li>
<li>Bar charts must start at zero; avoid 3D, crowded pies and colour-only meaning.</li>
<li>Save charts with fig.savefig before plt.show; save tables with to_csv(index=False).</li>
<li>A report states the question, data, method, answer and limitations.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Mini project: a youth digital skills survey',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>In this project you will take a small survey of twelve young people from start to finish: load, check, clean, summarise, chart and report. It uses every skill from the course. The question is: <strong>how do online time, spending and mobile money use differ by district and age?</strong></p>

<h3>The data</h3>
<pre><code>import io
import pandas as pd
import matplotlib.pyplot as plt

csv_text = """respondent,district,age,gender,hours_online,monthly_spend_ugx,uses_mobile_money
1,Gulu,19,F,14,20000,yes
2,Gulu,24,M,20,35000,yes
3,Lira,22,F,8,10000,no
4,Gulu,17,M,25,30000,yes
5,Kampala,27,F,30,60000,yes
6,Lira,31,M,5,,no
7,Kampala,23,M,18,45000,yes
8,Gulu,29,F,10,15000,yes
9,Lira,20,F,12,18000,no
10,Kampala,35,F,6,25000,yes
11,Gulu,21,M,16,28000,yes
12,Lira,26,M,9,12000,yes
"""
survey = pd.read_csv(io.StringIO(csv_text))</code></pre>
<p>The numbers are invented for practice. Hours online is per week.</p>

<h3>Step 1: inspect and clean</h3>
<pre><code>print(survey.shape)
print(survey["monthly_spend_ugx"].isna().sum())
print(survey[["district", "age", "hours_online"]].head(3))

survey["uses_mobile_money"] = survey["uses_mobile_money"].eq("yes")</code></pre>
<pre>(12, 7)
1
  district  age  hours_online
0     Gulu   19            14
1     Gulu   24            20
2     Lira   22             8</pre>
<p>There are 12 rows and one missing spending value (respondent 6). We leave it missing, because it is unknown and zero would be wrong; pandas averages skip it. The yes/no column is converted into True and False so that its mean becomes the share of yes answers.</p>

<h3>Step 2: summarise by district</h3>
<pre><code>summary = survey.groupby("district").agg(
    respondents=("respondent", "count"),
    avg_hours=("hours_online", "mean"),
    avg_spend=("monthly_spend_ugx", "mean"),
    mobile_money=("uses_mobile_money", "mean"),
).round(2)
print(summary)</code></pre>
<pre>          respondents  avg_hours  avg_spend  mobile_money
district
Gulu                5       17.0   25600.00          1.00
Kampala             3       18.0   43333.33          1.00
Lira                4        8.5   13333.33          0.25</pre>
<p>Check one number by hand: Lira's spending values are 10000, 18000 and 12000 (one is missing), so the average of three is 40000 / 3 = 13333.33. Lira respondents spend and use the internet least and are the only district where most say no to mobile money.</p>

<h3>Step 3: age groups</h3>
<pre><code>survey["age_group"] = pd.cut(survey["age"], bins=[0, 19, 24, 120],
                             labels=["19 and under", "20-24", "25+"])
print(survey.groupby("age_group", observed=True)["hours_online"].mean())
print(survey["hours_online"].corr(survey["monthly_spend_ugx"]).round(2))</code></pre>
<pre>age_group
19 and under    19.5
20-24           14.8
25+             12.0
Name: hours_online, dtype: float64
0.83</pre>
<p>Younger respondents spend more hours online. The correlation of 0.83 between hours and spending is strong, but with 11 complete pairs it is only a hint. The <code>observed=True</code> option avoids a warning about groups with no rows.</p>

<h3>Step 4: chart</h3>
<pre><code>fig, ax = plt.subplots(figsize=(6, 4))
ax.bar(summary.index, summary["avg_hours"], color="#2a6f97")
ax.set_title("Average weekly hours online by district (12 respondents)")
ax.set_ylabel("Hours per week")
ax.set_ylim(0, 20)
fig.savefig("hours_by_district.png", dpi=150, bbox_inches="tight")
plt.show()</code></pre>
<p>Three bars of height 17.0 (Gulu), 18.0 (Kampala) and 8.5 (Lira), starting at zero. A second chart, <code>ax.scatter(survey["hours_online"], survey["monthly_spend_ugx"])</code>, shows spending climbing with hours; matplotlib simply leaves out the point with missing spending.</p>

<h3>Step 5: write the report</h3>
<p>Write the finding with its limits: "In this sample of 12 young people, Gulu and Kampala respondents spend about twice as many hours online (17 to 18 hours a week) as Lira respondents (8.5). All Gulu and Kampala respondents use mobile money, against one in four in Lira. The sample is tiny and not random, so these results cannot be generalised." Add your recommendation, for example collecting a larger and more representative sample.</p>

<h3>Try it yourself</h3>
<ol>
<li>Run all the steps in a fresh notebook and compare each output with the lesson.</li>
<li>Add a table of average spending by gender. (F: about 24,667 across six values; M: 30,000 across the five known values.)</li>
<li>Create a chart for mobile money share by district with the axis from 0 to 1.</li>
<li>Export the summary table with <code>summary.to_csv("district_summary.csv")</code>.</li>
<li>Write your own three-sentence report with one limitation.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A project follows the workflow: question, load, check, clean, summarise, chart, report.</li>
<li>Check results by hand on small data, and report what is missing.</li>
<li>Small samples give hints, not proof; say so in the report.</li>
<li>Save charts and tables so others can use them.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Ethics and privacy of data',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to handle personal data responsibly, check whether a table could identify people, and avoid common ways analysis misleads. Behind every row of data there may be a real person, and as an analyst you are trusted with their information.</p>

<h3>Why it matters</h3>
<p>Data about health, money, location or children can cause real harm if it leaks or is misused: embarrassment, discrimination, fraud or even danger. Laws also apply. Uganda has the Data Protection and Privacy Act, 2019, which sets rules for collecting and handling personal data, and many other countries have similar laws. Learn the rules that apply to your organisation and ask your supervisor or a legal adviser when you are unsure.</p>

<h3>Principles to follow</h3>
<ul>
<li><strong>Consent and purpose.</strong> Tell people why you collect data and what you will do with it, and use it only for that purpose.</li>
<li><strong>Minimise.</strong> Collect and keep only what you need. If you do not need names or phone numbers for the analysis, do not collect them.</li>
<li><strong>Protect.</strong> Store files securely, protect them with passwords, share only with people who need them, and avoid sending raw personal data by chat apps or uploading it to services you do not control.</li>
<li><strong>Be accurate and fair.</strong> Document cleaning steps and do not change numbers to suit a story.</li>
<li><strong>Delete when finished.</strong> Do not keep personal data longer than necessary.</li>
</ul>

<h3>Removing direct identifiers</h3>
<p>Direct identifiers are names, phone numbers, national ID numbers, email addresses and exact addresses. Remove them before analysis or sharing:</p>
<pre><code>safe = people.drop(columns=["name", "phone"])</code></pre>
<p>If you need to link records without showing names, replace them with a meaningful but anonymous code such as the respondent number. This is called <strong>pseudonymisation</strong>.</p>

<h3>Small groups can still reveal people</h3>
<p>Even without names, a combination such as district, age band and gender can point to one person, especially in a small group. Check the size of each combination before you publish a table. Using the survey from the last lesson:</p>
<pre><code>sizes = (survey.groupby(["district", "age_group"], observed=True)
               .size()
               .reset_index(name="n"))
print(sizes[sizes["n"] &lt; 2])</code></pre>
<pre>   district  age_group  n
2      Gulu        25+  1
3   Kampala      20-24  1</pre>
<p>Two combinations contain exactly one person. If this were a real survey, publishing average spending for "Gulu, 25 and over" would reveal that one person's answer. The fixes are to merge small groups into bigger ones (for example widen the age bands), suppress the small cells, or report only the district totals. A common rule of thumb is to avoid showing groups smaller than about 5 to 10 people, but the right limit depends on how sensitive the data is.</p>

<h3>Bias and fairness</h3>
<ul>
<li><strong>Who is missing?</strong> A phone survey misses people without phones. An online survey misses people without internet. Say whom your data represents.</li>
<li><strong>Sampling.</strong> Twelve volunteers do not represent a country.</li>
<li><strong>Correlation is not causation.</strong> Two things moving together do not prove one causes the other. Spending and hours online rise together, but a third factor such as income may drive both.</li>
<li><strong>Measures hide differences.</strong> An average can hide that one group is doing much worse than another. Look at groups.</li>
<li><strong>Models and AI.</strong> If you use results to make decisions about people, such as who receives a service, check whether some groups are treated unfairly and keep a human in the loop.</li>
</ul>

<h3>Reproducibility and honesty</h3>
<p>Keep the raw data unchanged, put all cleaning in code, note the versions of your tools, and write down every decision (for example "age 420 marked as missing"). When another analyst can rerun your notebook and get the same answers, your work can be trusted. If you use AI tools to help write code, test their output with small examples you can check by hand, as in this course, and never paste personal data into a tool without permission.</p>

<h3>Try it yourself</h3>
<ol>
<li>Take a table you have used and list which columns are direct identifiers.</li>
<li>Drop them with <code>drop(columns=[...])</code> and confirm with <code>columns.tolist()</code>.</li>
<li>Run the small-group check on the survey and decide how you would fix each small group.</li>
<li>Write three questions to ask before collecting a new dataset about people.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Collect only what you need, protect it, and use it only for the stated purpose.</li>
<li>Remove direct identifiers and check small groups before sharing results.</li>
<li>Think about who is missing from the data and avoid claiming causation from correlation.</li>
<li>Document every cleaning decision so your analysis can be repeated and trusted.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which chart is best for showing how monthly takings changed over a year?
- Pie chart
- Histogram
* Line chart
- Scatter plot of month against month
= A line shows change over an ordered axis such as time.

Q: Why must a bar chart axis start at zero?
- matplotlib refuses otherwise
* Bars show value by length, so a cut-off baseline exaggerates differences
- It makes the file smaller
- It is only a style rule for colour
= A truncated bar axis makes small differences look big.

Q: What does a histogram show?
- The relationship between two categories
- A total per category
* How the values of one numeric column are spread across ranges
- A trend over time
= A histogram counts how many values fall in each bin.

Q: Which call saves a sharp PNG of a figure called fig?
- fig.show("chart.png")
* fig.savefig("chart.png", dpi=150, bbox_inches="tight")
- plt.write("chart.png")
- fig.export(150)
= savefig writes the file; dpi sets the resolution.

Q: Which items should a chart always include? (choose all that apply)
* A title
* Axis labels with units
- A 3D effect
* The sample size or source when useful
= Clear labels let readers understand the chart without help; 3D effects distort.

Q: What does the correlation of 0.83 between hours online and spending tell us?
- Hours online cause the spending
* The two tend to rise together, but it does not prove one causes the other
- The survey is correct for all of Uganda
- There is no relationship
= Correlation shows association, not cause, and small samples give only hints.

Q: A table of average spending is based on a group containing one person. What is the main concern?
- The average is too accurate
- Charts cannot show it
* The group could reveal that person's private information
- The group has no mean
= Very small groups can identify individuals even without names.

Q: Which practice best protects respondents' privacy?
- Share the full file with names so others can check it
* Remove direct identifiers, collect only what is needed and share only with those who need it
- Upload the raw data to any free online tool
- Keep the data forever
= Minimising, protecting and limiting access are core privacy habits.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which sequence best describes the usual analysis workflow?
- Chart, clean, ask, collect
* Ask a question, collect data, clean, explore and summarise, visualise, communicate
- Communicate, collect, ask, delete
- Install, delete, chart, ask
= Analysts start with a question and finish by communicating results, with the work in between often repeated.

Q: Why is a virtual environment useful?
- It speeds up the internet
* It keeps one project's packages separate from other projects
- It converts CSV files
- It replaces Python
= Separate environments prevent package version conflicts.

Q: In a notebook, what is the best check before you share it?
- Delete all the outputs
* Use Restart and run all to confirm it works from top to bottom
- Rename the cells
- Run only the last cell
= Out-of-order runs create hidden state that Restart and run all exposes.

Q: What is the result of np.array([10, 20, 30]) > 15?
- [10 20 30]
- 2
* [False  True  True]
- An error
= The comparison gives a boolean mask, one value per item.

Q: What does df.shape return for 8 rows and 6 columns?
* (8, 6)
- (6, 8)
- 14
- ["8", "6"]
= shape is (rows, columns).

Q: What does df["units"].describe() NOT include?
- The mean
- The median (50%)
* The data type of every other column
- The minimum and maximum
= describe on one column gives count, mean, std, min, quartiles and max for that column only.

Q: Which selection returns a DataFrame with two columns?
- df["shop", "units"]
- df.shop.units
* df[["shop", "units"]]
- df("shop", "units")
= Use double brackets with a list of column names.

Q: What is the difference between loc and iloc? (choose all that apply)
* loc selects by label and includes the end of a slice
* iloc selects by position and excludes the end of a slice
- iloc selects by label
- They never differ
= The two differ in what they use to select and in how slice ends work.

Q: Which expression keeps sales with units of at least 10 in Kampala?
- df[df["units"] >= 10 and df["shop"] == "Kampala"]
- df[df["units"] >= 10, df["shop"] == "Kampala"]
- df.filter(units >= 10)
* df[(df["units"] >= 10) & (df["shop"] == "Kampala")]
= Use & with brackets around each condition.

Q: Which call returns the three rows with the largest units?
- df.sort_values("units").head(3)
* df.sort_values("units", ascending=False).head(3)
- df.head(3).sort_values("units")
- df.loc[3, "units"]
= Sort descending first, then take the top rows.

Q: A numbers column is stored as text. What goes wrong when you sort or sum it?
- Nothing
* Text sorts alphabetically ("100" before "20") and cannot be added as numbers
- The column is deleted
- Python crashes the computer
= Types control how calculations and sorting behave, so convert with to_numeric or astype.

Q: How should you handle a column where "n/a" and "-" mean missing?
- Ignore them
- Replace them with zero by hand in the raw file
* Tell read_csv with na_values, for example na_values=["n/a", "-"]
- Delete the column
= Declaring the markers makes pandas treat them as NaN.

Q: A dataset has an impossible outlier (age 420) that skews the mean. Which fill value for other gaps is more robust?
- The mean
* The median
- The maximum
- 420
= The median is much less affected by extreme values.

Q: What does df.drop_duplicates().reset_index(drop=True) do?
* Removes repeated rows and renumbers the index from 0
- Removes only the first row
- Sorts the table
- Deletes the index column and the data
= It deletes repeats and gives clean new row numbers.

Q: Which format code reads the date 17/03/2025 (day first)?
- "%Y/%m/%d"
- "%m/%d/%Y"
* "%d/%m/%Y"
- "%y-%d-%m"
= %d day, %m month, %Y year, in the order of the text.

Q: What does pd.to_numeric(series, errors="coerce") do with the text "n/a"?
- Raises an error
* Turns it into NaN
- Turns it into 0
- Keeps it as text
= Coerce replaces unconvertible values with missing values.

Q: What does a left merge guarantee when the right table has unique keys?
- Only matching rows are kept
- Every right-table row appears
* Every row of the left table is kept, with NaN where there is no match
- The row count doubles
= Left merges keep all left rows; unmatched ones get NaN.

Q: After a merge, the number of rows grew unexpectedly. What is the most likely cause?
- The index was reset
* The right table has duplicate keys, multiplying matching rows
- The merge used how="left"
- NaN values were added
= Repeated keys in the right table create extra combinations; validate="many_to_one" can catch it.

Q: What does df.groupby("shop")["revenue"].sum() return?
- One number for the whole table
- The shop with the highest revenue
* Total revenue for each shop
- The revenue column sorted
= It splits by shop and adds revenue in each group.

Q: In the sales data, the revenue per shop is Gulu 82500 and Kampala 122000. What is the total revenue?
- 82,500
- 122,000
- 40,000
* 204,500
= 82500 + 122000 = 204500.

Q: What is the difference between value_counts() and value_counts(normalize=True)?
- None
* The first gives counts; the second gives shares that add up to 1
- The first sorts and the second does not
- The second only counts missing values
= normalize=True converts counts to proportions.

Q: A pivot table needs index, columns, values and aggfunc. What does columns do?
- Chooses the rows of the grid
* Chooses the categories placed across the top of the grid
- Sets the file name
- Counts missing cells
= index goes down the side and columns goes across the top.

Q: What does daily["amount"].resample("MS").sum() require of daily?
- A text index
- No index at all
* A DatetimeIndex (dates as the index)
- A column called MS
= resample works on a time-indexed table.

Q: Monthly totals are 215000 for January and 330000 for February. What is the percentage growth?
- 35.0
* About 53.5
- 115.0
- About 65.2
= (330000 - 215000) / 215000 = 0.535, or 53.5 percent.

Q: Which method should you try first to label sales as "bulk" or "single" for a large table?
- A for loop over every row
- apply with a long function written by hand
* np.where on the whole column
- Typing the labels by hand
= Vectorised methods are shorter and faster than row-by-row code.

Q: Which chart type best shows whether two numeric columns are related?
- Pie chart
- Bar chart of totals
* Scatter plot
- Table of means
= A scatter plot places each row as a point using two numbers.

Q: Which practices make a chart honest? (choose all that apply)
* Start bar charts at zero
* Label axes with units and state the sample size
- Use a 3D pie chart to look professional
- Show only the months that look good
= Honest charts do not exaggerate, hide data or confuse readers.

Q: Why should an analyst avoid publishing average spending for a group containing a single person?
- The average cannot be computed
- Charts will not draw it
* It could reveal that individual's private answer
- It would be too small to see
= Small groups can identify people even without names.

Q: Which is a direct identifier that should be removed before sharing a dataset?
- District of residence
* Phone number
- Hours online per week
- Age band
= Phone numbers, names and ID numbers identify individuals directly.

Q: A study finds that people who spend more hours online also spend more money. What is the correct conclusion?
- Going online makes people spend more
- Spending money makes people go online
- Neither could be related
* The two are associated, but this alone does not prove one causes the other
= Correlation shows association; other factors may explain it.
QUIZ,
];
