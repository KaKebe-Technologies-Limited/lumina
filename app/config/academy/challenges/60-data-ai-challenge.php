<?php
return [
    'slug' => 'data-ai-challenge', 'kind' => 'quiz',
    'title' => 'Data and AI Challenge', 'category' => 'data',
    'summary' => 'Test your skills in spreadsheets, SQL basics, charts, data cleaning and AI literacy. 30 questions, 30 minutes, and a chance to be a Winner of the Month.',
    'description' => "The Data and AI Challenge brings together two skills that are changing every workplace: working with data and understanding artificial intelligence. You will meet spreadsheet and SQL basics, choosing and reading charts, cleaning messy data, and the practical ideas behind AI tools, including what they can and cannot do, how to write a good prompt, bias and privacy.\n\nYou will answer 30 questions in 30 minutes. Many are practical, such as spotting a problem in a table or deciding how to use an AI assistant responsibly. Some questions say 'choose all that apply'.\n\nEveryone who reaches the pass score earns a certificate of participation. The three highest scores win, and the fastest finishing time breaks any tie.",
    'rules' => "The challenge is open to registered LAF Academy learners.\nOnly approved entrants can take part.\nYou have one attempt only.\nThe clock starts the moment you start the quiz and you have 30 minutes.\nAnswers are marked by the system as soon as you finish.\nThe highest score wins, and the fastest finishing time breaks a tie.\nWinners are announced and featured as Winners of the Month, each with a certificate.\nEvery entrant who scores at or above the pass score receives a certificate of participation.\nNo outside help and no sharing of answers. The decision of the system ranking is final.",
    'prize' => 'Winner of the Month feature on the LAF Academy, a certificate of achievement and a place on the Wall of Winners.',
    'winners_count' => 3, 'quiz_minutes' => 30, 'pass_score' => 50,
    'quiz' => <<<'QUIZ'
Q: In a well-organised data table, what does each row usually represent?
- A column heading
* One record, such as one customer, one sale or one student
- The title of the file
- A chart
= Columns hold the kinds of information (fields) and rows hold the individual records.

Q: Which of these is an example of quantitative (numerical) data?
- The colour of a shirt
- A customer's village name
* The number of kilograms of maize sold
- A student's favourite subject
= Quantitative data is measured or counted. Categories like colour or village are qualitative.

Q: In a spreadsheet, which function counts how many cells in a range contain numbers?
- SUM
* COUNT
- AVERAGE
- LARGE
= COUNT counts numeric cells. SUM adds them and AVERAGE finds the mean.

Q: Five students scored 40, 50, 50, 60 and 100. What is the median score?
- 40
- 60
* 50
- 100
= The median is the middle value when the numbers are in order. The outlier of 100 raises the mean (60) but not the median.

Q: Which of these is the best way to show each product's share of total monthly sales?
- A scatter chart
* A pie or stacked bar chart, with only a few categories
- A line with fifty categories
- A table with no labels
= Pie charts work for parts of a whole when there are few categories. Many categories become hard to read.

Q: Which chart is best for comparing sales of five different products in one month?
- Pie chart with five hundred slices
- Line chart across years
* Bar or column chart
- A map
= Bars make it easy to compare sizes between separate categories.

Q: Which of these makes a chart easier to understand? (choose all that apply)
* A clear title
* Labelled axes with units
- Many bright colours with no meaning
* A legend, if there is more than one series
= A good chart tells the reader what it shows without guessing.

Q: A bar chart starts its vertical axis at 90 instead of 0, so a small difference looks huge. What is the problem?
- Nothing, it is more beautiful
* It exaggerates the difference and can mislead readers
- It makes the file larger
- It removes the data
= Bar charts should normally start at zero so the bar lengths are honest.

Q: What is data cleaning?
- Wiping a computer screen
- Deleting all your data
* Finding and fixing errors, duplicates and inconsistencies so data can be trusted
- Moving data to another country
= Typos, blank cells, repeated rows and mixed formats cause wrong results.

Q: A column of towns contains "Kampala", "kampala", "KAMPALA " and "Kampla". What should you do before counting customers per town?
- Count them as they are
* Standardise the spelling and capital letters, and remove extra spaces
- Delete the column
- Add the four counts together without looking
= Inconsistent labels split one group into several. Clean them first so the totals are right.

Q: You find the same customer listed three times in a sales list. What is the safest action?
- Keep all three because more rows look better
* Check whether they are true duplicates, then remove the extra copies and keep one
- Delete the whole sheet
- Rename the customers
= Duplicates inflate totals. Verify before deleting, and keep a backup of the original data.

Q: In a column of ages, you see the value 250. What is the best action?
- Keep it, it is data
* Treat it as a likely error, check the source and correct or mark it before analysing
- Average it with a zero
- Make it 25 without checking
= Impossible values are probably typing errors. Check the source rather than guessing.

Q: What does a spreadsheet filter do to the data?
- Permanently deletes rows that do not match
- Changes the numbers
* Temporarily hides rows that do not match the condition you choose
- Locks the sheet
= Filtering helps you focus on part of the data and the other rows are still there.

Q: What is the main purpose of SQL?
- To design logos
* To ask questions of, and manage data stored in, a database
- To send e-mails
- To edit photos
= SQL stands for Structured Query Language.

Q: Which SQL statement reads data from a table named students?
- GET students
* SELECT * FROM students
- OPEN students
- READ students
= SELECT chooses columns, and FROM names the table. The star means all columns.

Q: Which SQL clause limits the rows returned, for example only students from Gulu?
- ORDER BY
- GROUP BY
* WHERE
- LIMIT BY
= WHERE filters rows using a condition, such as WHERE town = 'Gulu'.

Q: What does this SQL return?  SELECT COUNT(*) FROM orders;
- The names of all orders
- The largest order
* The number of rows in the orders table
- The total money of all orders
= COUNT(*) counts rows. SUM would be used to add amounts.

Q: Which SQL clause sorts the results from the highest price to the lowest?
- SORT price UP
* ORDER BY price DESC
- ORDER BY price ASC
- GROUP price HIGH
= ASC is ascending (lowest first) and DESC is descending (highest first).

Q: You want total sales for each region from a sales table. Which clause do you need along with SUM?
- WHERE ONLY
- HAVE
* GROUP BY
- JOIN ALL
= GROUP BY creates one result row per region, and SUM adds the sales inside each group.

Q: What is a primary key in a database table?
- The first column alphabetically
- A password for the database
* A value, or set of values, that uniquely identifies each row
- The largest number in the table
= For example, a student ID number. No two rows should share the same primary key.

Q: What is artificial intelligence (AI) as used in everyday tools such as chat assistants?
* Computer systems that perform tasks that normally need human intelligence, often by learning patterns from large amounts of data
- A human hiding inside the computer
- A machine that always tells the truth
- A kind of internet cable
= Modern AI learns patterns from data. It does not think or understand like a person.

Q: A chat assistant gives you an answer that sounds confident, but contains a wrong fact. Which term describes this?
- Streaming
- Backup
* A hallucination
- Cleaning
= AI tools can produce fluent but false information. Always check important facts against reliable sources.

Q: Which of these tasks can AI tools often help with? (choose all that apply)
* Drafting and summarising text
* Suggesting ideas and outlines
- Guaranteeing that every answer is correct
* Explaining a concept in simpler words
= AI is a helpful assistant, but you remain responsible for checking the result.

Q: Which prompt is likely to give the most useful answer?
- Write about water
* Write a 150-word explanation of how clean water prevents disease, for 14-year-old students, in simple English
- Do it
- Tell me everything
= A good prompt gives the task, the audience, the length and the style or format.

Q: You are using a free AI chat tool. Which of these should you avoid typing into it?
- A general question about how to write a CV
- A request to explain photosynthesis
* Your national ID number, passwords or other people's private details
- A request to improve the grammar of a sentence that holds no private information
= Treat what you type into online tools as possibly stored or seen by others. Keep sensitive and private data out.

Q: An AI tool used to screen job applications mostly rejects women, because it was trained on past hiring data in which mostly men were hired. What is this problem called?
- Overheating
- Data cleaning
* Bias in the training data leading to unfair results
- A slow connection
= AI can copy and amplify unfairness found in its training data. People should check and be able to challenge its results.

Q: Your teacher asks for your own essay. Which use of an AI assistant is most honest and responsible?
- Submit its answer as your own work without saying so
* Use it to understand the topic or give feedback, write the work yourself and follow your school's rules about AI
- Ask it to write the whole essay and delete the evidence
- Let a friend use your account to do it
= Be honest about help received. Learning and integrity matter more than a fast answer.

Q: A tool offers to generate a realistic photo of a real person saying something they never said. What is the main risk?
- Nothing, it is just for fun
* Such fake media (deepfakes) can mislead people and harm someone's reputation, so it should not be made or shared without consent
- The picture will be too small
- It will use less data
= Fake images, audio and video can be used for scams and lies. Verify before you share.

Q: In a spreadsheet, a table of 1,000 survey rows has 40 blank answers in the "Age" column. What is a sensible way to handle the blanks?
- Replace every blank with 0 so that the average looks better
- Delete the entire survey
- Ignore that they exist and never mention them
* Decide on a clear rule, such as excluding blanks from the average, and note how many were missing
= Blanks are not zeros. Treat missing data openly, and say how it affected your result.

Q: A study finds that towns with more ice cream sellers also have more sunburn cases. What is the most sensible conclusion?
- Ice cream causes sunburn
- Sunburn causes more ice cream sellers to appear
* The two may be linked by a third factor, such as hot weather, so correlation alone does not prove cause
- The data must be wrong
= Two things moving together does not mean one causes the other. Look for other explanations.
QUIZ,
];
