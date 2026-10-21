<?php
return [
    'slug' => 'office-skills-challenge', 'kind' => 'quiz',
    'title' => 'Office Skills Challenge', 'category' => 'office',
    'summary' => 'Show your skills in Word, Excel, PowerPoint and Google Workspace. 30 questions, 30 minutes, and a chance to be a Winner of the Month.',
    'description' => "The Office Skills Challenge tests the tools used in almost every office, school and small business: word processing, spreadsheets, presentations and online collaboration with Google Workspace.\n\nYou will answer 30 questions in 30 minutes. Some are about shortcuts and formulas, others about good document and slide design, and a few ask you to decide which tool or feature fits a real situation. Some questions say 'choose all that apply'.\n\nEveryone who reaches the pass score earns a certificate of participation. The three highest scores win, and the fastest finishing time breaks any tie.",
    'rules' => "The challenge is open to registered LAF Academy learners.\nOnly approved entrants can take part.\nYou have one attempt only.\nThe clock starts the moment you start the quiz and you have 30 minutes.\nAnswers are marked by the system as soon as you finish.\nThe highest score wins, and the fastest finishing time breaks a tie.\nWinners are announced and featured as Winners of the Month, each with a certificate.\nEvery entrant who scores at or above the pass score receives a certificate of participation.\nNo outside help and no sharing of answers. The decision of the system ranking is final.",
    'prize' => 'Winner of the Month feature on the LAF Academy, a certificate of achievement and a place on the Wall of Winners.',
    'winners_count' => 3, 'quiz_minutes' => 30, 'pass_score' => 50,
    'quiz' => <<<'QUIZ'
Q: In Microsoft Word, which shortcut saves your document?
- Ctrl + P
- Ctrl + O
* Ctrl + S
- Ctrl + N
= Ctrl + S saves. Ctrl + P prints, Ctrl + O opens and Ctrl + N starts a new document.

Q: Which Word feature makes the selected text darker and heavier?
* Bold
- Italic
- Underline
- Highlight
= Bold (Ctrl + B) thickens the letters. Italic slants them and underline adds a line below.

Q: What is the best way to give a long report a clear structure that can also build a table of contents?
- Make the headings bigger by hand and use bold
* Apply the built-in Heading styles (Heading 1, Heading 2)
- Type the headings in capital letters
- Put each heading on its own page
= Heading styles tell Word what is a heading, so a table of contents can be created automatically.

Q: You typed a word wrongly and want to reverse your last action in Word or Excel. Which shortcut do you use?
- Ctrl + Y
* Ctrl + Z
- Ctrl + K
- Ctrl + A
= Ctrl + Z is Undo. Ctrl + Y redoes what you undid.

Q: Which of these are good practices for a professional letter or CV in Word? (choose all that apply)
* Use one or two readable fonts
* Check spelling before sending
- Use many colours and decorative fonts
* Keep margins and spacing consistent
= Simple, consistent formatting looks professional and is easier to read.

Q: In Excel, what does a cell reference such as B3 mean?
- Row B, column 3
* Column B, row 3
- Page B, line 3
- The third cell in the whole sheet
= Columns have letters and rows have numbers, so B3 is where column B meets row 3.

Q: Every formula in Excel must begin with which symbol?
- #
- @
* =
- +
= Typing = tells Excel that you are entering a formula, for example =A1+B1.

Q: Cells B2 to B6 contain prices. Which formula gives their total?
- =TOTAL(B2:B6)
- =ADD(B2-B6)
* =SUM(B2:B6)
- =B2:B6
= SUM adds a range of cells. The colon means "from B2 to B6".

Q: Which function finds the average of the numbers in C2 to C11?
- =MEAN(C2,C11)
- =AVG(C2-C11)
* =AVERAGE(C2:C11)
- =MIDDLE(C2:C11)
= AVERAGE adds the numbers and divides by how many there are.

Q: What does the formula =B2*C2 do when B2 holds a quantity and C2 holds a price?
- Adds the quantity and the price
- Divides the quantity by the price
* Multiplies the quantity by the price to give a total cost
- Subtracts the price from the quantity
= The * sign means multiply. Use / for divide.

Q: In Excel, when you copy the formula =B2*C2 from row 2 down to row 3, what does it become by default?
- =B2*C2
* =B3*C3
- =B3*C2
- =$B$2*$C$2
= Normal references are relative, so they shift when copied. This saves you from retyping formulas.

Q: You want a formula to always point to the tax rate in cell F1, even when copied to other cells. How do you write the reference?
- F1
- F-1
- (F1)
* $F$1
= The dollar signs make an absolute reference. The cell does not change when the formula is copied.

Q: Which Excel function shows a result depending on a condition, for example "Pass" if a mark is 50 or more?
- COUNT
- ROUND
* IF
- SUMIF
= IF tests a condition and returns one value if it is true and another if it is false, for example =IF(B2>=50,"Pass","Fail").

Q: What is the best use of a chart in Excel?
- To store a large amount of text
* To show patterns, comparisons or trends in numbers at a glance
- To protect a file with a password
- To remove duplicates
= Charts turn rows of numbers into pictures that people understand quickly.

Q: Which chart type is best to show how sales changed month by month over a year?
- Pie chart
- Scatter chart
* Line chart
- Radar chart
= A line chart shows change over time clearly.

Q: Which tool would you use in Excel to show only the rows where the Region column says "Gulu"?
- Spell check
- Merge cells
- Freeze panes
* Filter
= A filter hides rows that do not match the condition, without deleting them.

Q: What does Freeze Panes do in Excel?
* Keeps chosen rows or columns, such as the header row, visible while you scroll
- Locks the file so nobody can open it
- Stops formulas from calculating
- Prints the sheet on one page
= It is useful for long lists so you can always see the column titles.

Q: In a PowerPoint slide, which is the best way to present your key points?
- Paste full paragraphs in a small font
* Use short bullet points or key phrases in a large readable font
- Use as many animations as possible
- Use light grey text on a white background
= Slides support what you say. Keep them short, readable and high in contrast.

Q: Which key starts a PowerPoint slide show from the first slide?
- F1
- F2
* F5
- F12
= F5 starts the show from the beginning. Shift + F5 starts it from the current slide.

Q: What does a slide "master" or "theme" help you do?
- Count the words in a presentation
* Apply the same fonts, colours and layout across all slides
- Send the slides by e-mail
- Translate the slides
= A consistent look makes a presentation neat and saves time.

Q: Which of these are good practices when presenting with slides? (choose all that apply)
* Face your audience instead of reading the screen
- Put everything you plan to say on the slide
* Practise and check your timing before the day
* Use pictures that support your message
= The speaker is the main message. Slides are only the support.

Q: What is the main advantage of working in Google Docs when several people write the same report?
- It works only without the internet
* Several people can edit the same document at the same time and see changes live
- It cannot be shared
- It creates spreadsheets automatically
= Real-time collaboration avoids sending many versions of a file by e-mail.

Q: In Google Docs, a colleague wants to suggest changes without changing your text directly. Which mode should they use?
- Viewing
* Suggesting
- Printing
- Presenting
= Suggesting mode records changes as suggestions that you can accept or reject.

Q: When you share a Google Drive file, which permission lets the person read it but not change it?
- Editor
- Owner
- Commenter
* Viewer
= Viewer can only look. Commenter can add comments, and Editor can change the file.

Q: You share a file with "Anyone with the link" set to Editor. What is the main risk?
- The file becomes smaller
* Anyone who gets the link, even if forwarded, could change the file
- The link stops working after one day
- Only you can open it
= Share with specific people when the content matters, and choose the lowest permission needed.

Q: Which Google Workspace tool is used to hold a video meeting with a team?
- Google Sheets
- Google Forms
* Google Meet
- Google Drive
= Meet is for video calls. Forms collects answers, and Sheets is for spreadsheets.

Q: Which tool is best for collecting answers from many people, such as a registration form for a workshop, and viewing responses in a spreadsheet?
- Google Slides
* Google Forms
- Google Calendar
- Google Keep
= Forms collects answers and can send them straight to a Google Sheet.

Q: A mail merge in Word is used for what purpose?
- To combine two PDF files
- To merge cells in a table
* To create many personalised letters or labels from one template and a list of names
- To translate a document into another language
= The template stays the same, and the names and details come from a list such as an Excel sheet.

Q: A pivot table in Excel is mainly used to do what?
- Draw pictures
- Lock cells
* Summarise and group a large table of data quickly, for example total sales per region
- Change the page orientation
= Pivot tables let you total, count and compare data without writing many formulas.

Q: Look at the formula =VLOOKUP(A2,Prices!A:B,2,FALSE). What does the 2 tell Excel?
- Look for the value in row 2
* Return the value from the second column of the lookup range
- Round the answer to two places
- Look at the second sheet
= The number is the column index. The FALSE asks for an exact match.
QUIZ,
];
