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
    'category'    => 'office',                        // one of: basics | office | internet | marketing | design | security | careers | web | coding | ai | hardware
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

`sort` = the NN of the file.
