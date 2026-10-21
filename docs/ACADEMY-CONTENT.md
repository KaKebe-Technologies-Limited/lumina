# LAF Academy: how a starter course file is written

Each starter course is ONE PHP file in `app/config/academy/courses/` that `return`s an array. The Academy installer (Admin > Academy > Starter courses, or
`php bin/academy-install-content.php`) reads these files and stores them in the database. Check a file at any time with:

    /c/xampp/php/php.exe bin/academy-validate-content.php app/config/academy/courses/<file>.php      (one file; no argument = all files)

Fix every ERROR it prints; read every WARNING and fix it unless you have a good reason.

## The file

```php
<?php
return [
    'slug'        => 'ms-word',                       // lower-case letters, digits, hyphens; unique; same as the file name without the number prefix
    'title'       => 'Microsoft Word: Documents from Zero',
    'category'    => 'office',                        // one of: basics | office | internet | marketing | design | security | careers | web | coding | ai | hardware | data | cloud | business
    'level'       => 'beginner',                      // beginner | intermediate | advanced
    'icon'        => 'file-text',                     // one of: laptop, file-text, mail, globe, megaphone, shield, briefcase, code, layers, book, award, users, target, sprout, star, settings
    'sort'        => 30,                              // order in the catalogue (smaller first): see the table in "Courses and numbers" below
    'summary'     => 'One or two sentences shown on the course card (max 220 characters).',
    'description' => '<p>2-3 short paragraphs (HTML: p, ul, li, strong, em) on what the course is and who it is for.</p>',
    'outcomes'    => ['You can ...', 'You can ...'],  // 5-8 bullet points, each starts with a verb, "By the end you will be able to ..." style
    'requirements'=> ['A computer, laptop or smartphone', '...'],   // 1-4 short items; say "No experience needed" when true
    'pass_mark'   => 70,                              // percentage needed in the final exam
    'resources'   => [                                // 2-5 further-reading links; ONLY stable official sites you are certain exist (home or well-known help pages)
        ['title' => 'Microsoft Support: Word help', 'url' => 'https://support.microsoft.com/word'],
    ],
    'modules' => [
        [
            'title'   => 'Module 1: Getting started',
            'summary' => 'One sentence on what this module covers.',
            'lessons' => [
                [
                    'title'   => 'The Word window',
                    'minutes' => 10,                  // honest reading + practice time (5-25)
                    'body'    => '<p>...</p>',          // the lesson (HTML, see "Lesson body" below)
                ],
                // 3-5 lessons per module
            ],
            'quiz' => "Q: ...\n* right\n- wrong\n- wrong\n= why\n\nQ: ...",   // module quiz, 5-8 questions, QuizScript format (see below)
        ],
        // 4-6 modules per course
    ],
    'final_exam' => "Q: ...",                          // 20-30 questions across the whole course, QuizScript format
];
```

## Lesson body (HTML)

Allowed tags ONLY: `h3 h4 p ul ol li strong em code pre blockquote table thead tbody tr th td a br`. No `h1`/`h2`, no images, no scripts, no inline style, no classes.
Write 350-700 words per lesson. A good lesson has this shape (use these `h3` headings where they fit; vary them naturally):

1. An opening `<p>` that says what the learner will be able to do after the lesson and why it matters (one real-life reason).
2. `<h3>` sections that teach step by step. Number steps with `<ol>`. Say exactly where to click ("Select the **Home** tab, then **Bold**") and give the keyboard shortcut
   (Windows, and the Mac one in brackets when it differs). Use tables for comparisons and shortcut lists. Use `<pre>`/`<code>` for typed text, formulas or code.
3. `<h3>Try it yourself</h3> <ol>` a 3-6 step practice activity the learner does on their own computer or phone (always with an easy, concrete result).
4. `<h3>Key points</h3> <ul>` 3-5 bullets that restate the lesson.
5. Optional `<h3>Common mistakes</h3>` when useful.

Quality rules: accurate and current (Windows 10 and 11, Microsoft 365 / Office 2019+, Google Workspace, Android and iPhone where relevant); plain, friendly English for a beginner
(short sentences, define every new word the first time); learners are mostly young people in Uganda and East Africa, so examples may use UGX, mobile money, school, small business
and community life, but never stereotypes, and stay professional; no brand promotion; do not claim a product feature you are not sure exists; never invent URLs;
ORIGINAL wording only (do not copy text from manuals, websites or books).

## Quiz format (QuizScript)

```
Q: What does CPU stand for?
* Central Processing Unit
- Central Program Utility
- Computer Personal Unit
= The CPU is the "brain" of the computer.

Q: Which of these are input devices? (choose all that apply)
* Keyboard
* Mouse
- Printer
= A printer sends information OUT of the computer, so it is an output device.
```

Blocks are separated by a blank line. `Q:` question, `*` a correct answer, `-` a wrong answer, `=` explanation (always give one: it is what the learner reads after the quiz).
One `*` = pick one; several `*` = pick all that apply (and say so in the question). 3-5 answers per question. Make wrong answers believable, not silly. Spread the correct answer
over different positions. Test understanding and application, not memorisation of trivia. Plain text only. Inside a PHP double-quoted string write `\n` for line breaks,
or use a nowdoc/heredoc (`<<<'QUIZ' ... QUIZ`) which is easier: `'quiz' => <<<'QUIZ'` ... `QUIZ,`. Remember a `$` inside a double-quoted string needs a backslash.

## Courses and numbers

File names: `NN-slug.php` where NN is the order number below. Targets per course: 4-6 modules, 14-20 lessons in total, one quiz per module (5-8 questions), a final exam (20-30 questions).

| NN | slug | title | category | level |
|---|---|---|---|---|
| 10 | computer-basics | Computer Basics and Digital Literacy | basics | beginner |
| 20 | windows-file-management | Using Windows and Managing Your Files | basics | beginner |
| 30 | ms-word | Microsoft Word: Documents from Zero | office | beginner |
| 40 | ms-excel | Microsoft Excel: Spreadsheets from Zero | office | beginner |
| 50 | ms-powerpoint | Microsoft PowerPoint: Presentations that Work | office | beginner |
| 60 | typing-keyboard-skills | Typing and Keyboard Skills | basics | beginner |
| 70 | internet-email | The Internet, Email and Online Communication | internet | beginner |
| 80 | google-workspace | Google Workspace: Docs, Sheets, Slides, Drive and Meet | office | beginner |
| 90 | digital-marketing | Digital Marketing Fundamentals | marketing | beginner |
| 100 | social-media-canva | Social Media Management and Design with Canva | design | beginner |
| 110 | online-safety | Online Safety and Cybersecurity Basics | security | beginner |
| 120 | job-ready-digital-skills | Job-Ready Digital Skills: CVs, LinkedIn and Online Work | careers | beginner |
| 130 | web-basics-html-css | Web Basics: Build Your First Web Page (HTML and CSS) | web | intermediate |
| 140 | python-programming-intro | Introduction to Programming with Python | coding | intermediate |
| 150 | ai-tools-literacy | Practical AI Tools: Using AI Assistants Wisely | ai | beginner |
| 160 | computer-maintenance-networking | Computer Maintenance, Troubleshooting and Networking Basics | hardware | beginner |

| 170 | excel-data-analysis | Excel for Data Analysis: PivotTables, Charts and Dashboards | data | intermediate |
| 180 | excel-advanced-automation | Advanced Excel: Power Query, Power Pivot and Automation | data | advanced |
| 190 | sql-databases | Databases and SQL for Beginners | data | intermediate |
| 200 | python-data-analysis | Data Analysis with Python: pandas and Charts | data | advanced |
| 210 | javascript-basics | JavaScript for Beginners: Make Web Pages Interactive | web | intermediate |
| 220 | web-development-advanced | Modern Web Development: JavaScript, APIs and Deployment | web | advanced |
| 230 | wordpress-websites | Build a Website with WordPress | web | intermediate |
| 240 | ecommerce-online-business | E-commerce and Selling Online | business | intermediate |
| 250 | digital-marketing-advanced | Advanced Digital Marketing: SEO, Analytics and Paid Ads | marketing | advanced |
| 260 | cloud-computing-fundamentals | Cloud Computing Fundamentals | cloud | intermediate |
| 270 | linux-command-line | Linux and the Command Line | cloud | intermediate |
| 280 | networking-fundamentals | Networking Fundamentals: IP Addressing, Routers and Wi-Fi | hardware | intermediate |
| 290 | cybersecurity-essentials | Cybersecurity Essentials for Organisations | security | intermediate |
| 300 | project-management-basics | Project Management Basics | business | beginner |
| 310 | video-editing-content-creation | Video Editing and Content Creation | design | beginner |
| 320 | git-github-collaboration | Git and GitHub: Version Control and Teamwork | web | intermediate |
| 330 | graphic-design-fundamentals | Graphic Design Fundamentals | design | beginner |
| 340 | ux-ui-design-basics | UX and UI Design: Design Apps People Love | design | intermediate |
| 350 | generative-ai-at-work | Generative AI at Work: Prompting, Safety and Productivity | ai | intermediate |
| 360 | machine-learning-introduction | Introduction to Machine Learning | ai | advanced |
| 370 | php-mysql-web-apps | Web Applications with PHP and MySQL | web | advanced |
| 380 | cybersecurity-defence-advanced | Defending Systems: Security Operations and Incident Response | security | advanced |
| 390 | freelancing-remote-work | Freelancing and Remote Work from Africa | careers | intermediate |
| 400 | digital-finance-mobile-money | Digital Finance, Mobile Money and Safe Online Payments | business | beginner |
| 410 | data-visualisation-power-bi | Data Visualisation and Reporting with Power BI | data | intermediate |

Level guide: **beginner** assumes nothing; **intermediate** assumes the learner can use a computer and has done the relevant beginner course (say which one in `requirements`); **advanced** assumes comfortable intermediate skills (name them in `requirements`) and works on realistic projects, trade-offs and good practice. Intermediate and advanced lessons may run to 700-900 words and may use more code, formulas and screenshots-in-words, but keep the same lesson shape and the same quiz rules.

`sort` = the NN of the file.

## Challenge files (starter quiz challenges)

`app/config/academy/challenges/NN-slug.php` returns an array describing a **quiz challenge** (a timed, auto-graded competition that the administrator opens at a chosen time). Check with
`/c/xampp/php/php.exe bin/academy-validate-challenges.php [file]`.

```php
return [
    'slug' => 'ict-basics-quiz', 'kind' => 'quiz',            // quiz | innovation | hackathon
    'title' => '...', 'category' => 'basics',                 // a course topic key
    'summary' => 'One or two plain sentences (max 320 characters).',
    'description' => "Plain text, 2-3 short paragraphs separated by a blank line.",
    'rules' => "Plain text, one rule per line (6-10 lines): who may enter, time limit, one attempt, no help, scoring, tie-break, prize, certificate.",
    'prize' => 'Winner of the month feature, a certificate and ... (plain text, max 400)',
    'winners_count' => 3, 'quiz_minutes' => 30, 'pass_score' => 50,   // pass_score = % needed to qualify for a certificate of participation
    'quiz' => <<<'QUIZ'
Q: ...
* ...
- ...
= explanation
QUIZ,
];
```

Quiz challenge rules of thumb: 30 questions, a mix of easy (40%), medium (40%) and hard (20%), covering the whole topic, QuizScript format exactly as for courses, correct answer in varied positions,
believable wrong answers, every question has an explanation. Plain text only (no HTML anywhere in a challenge file).
