<?php
return [
    'slug' => 'coding-web-challenge', 'kind' => 'quiz',
    'title' => 'Coding and Web Challenge', 'category' => 'coding',
    'summary' => 'Show what you know about HTML, CSS, JavaScript, Python, logic and reading short pieces of code. 30 questions, 30 minutes, and a chance to be a Winner of the Month.',
    'description' => "The Coding and Web Challenge tests the first skills of every developer: building web pages with HTML and CSS, adding behaviour with JavaScript, writing simple programs in Python and thinking through logic step by step.\n\nYou will answer 30 questions in 30 minutes. Some questions ask for facts, but many show a short piece of code and ask what it does or what it prints. HTML tags are written as plain names, for example the h1 element, so read each question carefully. Some questions say 'choose all that apply'.\n\nEveryone who reaches the pass score earns a certificate of participation. The three highest scores win, and the fastest finishing time breaks any tie.",
    'rules' => "The challenge is open to registered LAF Academy learners.\nOnly approved entrants can take part.\nYou have one attempt only.\nThe clock starts the moment you start the quiz and you have 30 minutes.\nAnswers are marked by the system as soon as you finish.\nThe highest score wins, and the fastest finishing time breaks a tie.\nWinners are announced and featured as Winners of the Month, each with a certificate.\nEvery entrant who scores at or above the pass score receives a certificate of participation.\nNo outside help and no sharing of answers. The decision of the system ranking is final.",
    'prize' => 'Winner of the Month feature on the LAF Academy, a certificate of achievement and a place on the Wall of Winners.',
    'winners_count' => 3, 'quiz_minutes' => 30, 'pass_score' => 50,
    'quiz' => <<<'QUIZ'
Q: What does HTML do for a web page?
- It makes the page look colourful and animated
* It describes the structure and content of the page, such as headings, paragraphs and links
- It stores the visitors' data
- It makes the page load faster
= HTML is the skeleton of a page. CSS styles it and JavaScript adds behaviour.

Q: Which HTML element is used for the main, top-level heading of a page?
- p
- head
* h1
- title
= The h1 element is the main heading. h2 to h6 are smaller headings.

Q: Which HTML element creates a link to another page?
- link
- url
- href
* a
= The a (anchor) element makes a link, and its href attribute holds the destination.

Q: Which attribute of the img element gives a text description for people who cannot see the picture?
- title
- src
* alt
- name
= The alt text is read by screen readers and shown if the picture does not load. The src attribute holds the picture file.

Q: What is the correct way to start a page's paragraph in HTML?
* Use a p element around the text
- Use the br element only
- Use the h1 element
- Use the div attribute
= A p element marks one paragraph. Elements normally have an opening and a closing tag.

Q: Which of these are HTML elements for making lists? (choose all that apply)
* ul
* ol
- hr
* li
= ul is an unordered (bulleted) list, ol is an ordered (numbered) list and li is a list item. hr draws a line.

Q: What does CSS stand for?
- Computer Style Script
- Creative Simple Sheets
* Cascading Style Sheets
- Coded Site Structure
= CSS controls colours, fonts, spacing and layout.

Q: In CSS, what does this rule do?  p { color: blue; }
- Makes every paragraph's background blue
- Makes the first paragraph blue only
* Makes the text of every paragraph blue
- Creates a new paragraph with a blue border
= The part before the braces is the selector (p) and inside it are properties with values.

Q: Which CSS selector targets the element that has the class "note"?
- note
- #note
* .note
- *note
= A dot selects a class. A hash (#) selects an id.

Q: In the CSS box model, which part is the space between the content and the border?
- Margin
* Padding
- Outline
- Gap
= Padding is inside the border. Margin is the space outside the border.

Q: Which CSS technique is commonly used to make a layout adapt to phones, tablets and computers?
- Writing a separate website for each device only
- Fixed pixel widths for everything
* Responsive design with flexible layouts and media queries
- Removing all images
= Responsive pages use flexible widths and rules that change the layout at different screen sizes.

Q: Which line of HTML correctly connects an external CSS file named style.css?
- A script element with src="style.css"
- An a element with href="style.css"
* A link element with rel="stylesheet" and href="style.css"
- A style attribute with the name style.css
= The link element in the head connects a stylesheet to the page.

Q: What is JavaScript mainly used for on a web page?
- Creating the page's layout grid only
* Making the page interactive, for example reacting to clicks and updating content
- Choosing the page's colours
- Registering the domain name
= JavaScript runs in the browser and can change the page after it loads.

Q: In JavaScript, what will this show?  let x = 5; let y = 3; console.log(x + y);
- 53
- x + y
* 8
- An error
= Both values are numbers, so + adds them and the console shows 8.

Q: In JavaScript, what will this show?  console.log("5" + 3);
* 53
- 8
- 15
- An error
= When one value is text, + joins them as text, so the result is "53".

Q: Which keyword declares a variable whose value can change later in modern JavaScript?
- const
- fixed
- static
* let
= let declares a variable you may change. const declares one that cannot be reassigned.

Q: What does this JavaScript do?  if (age >= 18) { console.log("Adult"); } else { console.log("Minor"); }
- Always prints Adult
- Always prints Minor
* Prints Adult when age is 18 or more, otherwise Minor
- Prints both words
= An if statement chooses between two paths depending on whether the condition is true.

Q: What does this JavaScript print?  for (let i = 1; i <= 3; i++) { console.log(i); }
- 1 2 3 4
* 1 2 3 on separate lines
- 0 1 2
- Only 3
= The loop starts at 1, runs while i is 3 or less, and adds 1 each time.

Q: In Python, what does the following print?  print("Hello" + " " + "World")
- Hello + World
- HelloWorld
* Hello World
- Error
= The + joins strings. The middle string adds a space.

Q: In Python, which symbol starts a comment that the computer ignores?
- //
* #
- --
- /*
= Anything after # on a line is a comment. It is for people reading the code.

Q: In Python, what is the value of len([4, 8, 15, 16])?
- 15
- 3
- 43
* 4
= len counts the items in the list. There are four.

Q: A Python program sets total to 0, then uses a loop to add each number in the list [2, 4, 6] to total, and finally runs print(total). What does it print?
- 6
* 12
- 246
- 0
= The loop adds 2, 4 and 6 to the running total, giving 12.

Q: A Python program sets x to 7. If x % 2 equals 0 it prints even, otherwise it prints odd. What is the output?
- even
- Nothing
- Error
* odd
= The % sign gives the remainder. 7 divided by 2 leaves 1, which is not 0, so it prints odd.

Q: In Python, why does indentation (spaces at the start of a line) matter?
- It does not matter at all
- It only makes the code prettier
* It shows which lines belong inside a block such as an if, a loop or a function
- It makes the code run faster
= Python uses indentation to group statements.

Q: What is an algorithm?
* A clear, step-by-step set of instructions for solving a problem
- A type of computer virus
- A programming language
- A kind of website
= A recipe is a good everyday example. Code is one way to write an algorithm.

Q: What is a bug in a program?
- A feature added to make code shorter
* A mistake that makes the program behave wrongly or crash
- A comment in the code
- A tool for testing
= Debugging is finding and fixing these mistakes.

Q: A program must show "Pass" if a student has 50 marks or more and has attended class. Which condition is correct (in plain logic)?
- marks is 50 or more OR attended
* marks is 50 or more AND attended
- marks is below 50 AND attended
- marks is 50 or more, without checking attendance
= AND needs both conditions to be true. OR needs only one.

Q: Which is the best first step when your code does not work and shows an error message?
- Delete everything and start again
* Read the error message, find the line it mentions and check that line and the lines before it
- Ask a friend to write it for you
- Ignore the message
= Error messages usually tell you what went wrong and where.

Q: Which of these are good coding habits? (choose all that apply)
* Use clear names for variables, such as total_price
* Test your code with different inputs
- Copy and paste code you do not understand into your project
* Add short comments where the reason is not obvious
= Readable, tested code is easier to fix and to share. Make sure you understand code before you use it.

Q: What does this JavaScript function return when called as double(4)?  function double(n) { return n * 2; }
- 4
- 6
- 42
* 8
= The function multiplies its input by 2, so 4 becomes 8.
QUIZ,
];
