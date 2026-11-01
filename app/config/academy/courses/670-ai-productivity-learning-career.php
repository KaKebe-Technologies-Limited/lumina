<?php
return [
    'slug'        => 'ai-productivity-learning-career',
    'title'       => 'AI for Productivity, Learning and Career Growth',
    'category'    => 'ai',
    'level'       => 'beginner',
    'icon'        => 'star',
    'sort'        => 670,
    'summary'     => 'Use AI assistants for your own life: study smarter, write better, plan your time, prepare for jobs and earn on the side, while staying honest, safe and in charge of your own thinking.',
    'description' => '<p>AI assistants can explain a hard topic at your level, draft an email, quiz you before an exam or role-play a job interview at midnight when no teacher is awake. They can also invent facts, flatter your mistakes and tempt you to stop thinking. This course teaches you to use them for <strong>your own</strong> studies, productivity and career, and to know exactly where the risks are.</p><p>You will recap what AI assistants can and cannot do, learn simple prompt patterns for personal use, and protect your privacy. Then you will turn AI into a study partner, a writing coach and a research helper, always checking facts and respecting school and university rules. Later modules cover planning your time, low-data and offline workflows, building a skills roadmap, improving your CV and LinkedIn profile, practising interviews, and using AI for a small business or side income.</p><p>The course ends with critical thinking, so that you keep your own skills growing, and a personal AI workflow with a 30-day plan. Every lesson has realistic East African examples, a case scenario that includes things going wrong, and a practical task you can do on a phone.</p>',
    'outcomes'    => [
        'Explain what AI assistants can and cannot do, including hallucinations, bias and privacy risks',
        'Write clear prompts using role, task, context, format, examples and iteration',
        'Use AI as a study partner for summaries, explanations, practice questions, flashcards and revision plans',
        'Draft, improve and proofread emails, reports and applications while keeping your own voice',
        'Follow academic integrity rules and verify facts before you trust or share them',
        'Plan your time, meetings and goals, and work with AI on low data or offline',
        'Analyse your skills gap, improve your CV and LinkedIn profile and practise interviews with AI',
        'Spot scams and deepfakes, protect private information and build a personal AI workflow',
    ],
    'requirements' => ['A smartphone or computer with internet access (an AI assistant account is helpful, free versions are enough)', 'No experience needed; the course Practical AI Tools: Using AI Assistants Wisely (ai-tools-literacy) is helpful but not required'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Khan Academy (free learning)', 'url' => 'https://www.khanacademy.org/'],
        ['title' => 'LinkedIn Help Center', 'url' => 'https://www.linkedin.com/help/linkedin'],
        ['title' => 'Google Account: manage your privacy', 'url' => 'https://myaccount.google.com/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: Foundations for personal use',
            'summary' => 'Recap what AI assistants can and cannot do, learn prompt patterns for everyday use, and stay safe from privacy risks, scams and deepfakes.',
            'lessons' => [
                [
                    'title'   => 'What AI assistants can and cannot do for you',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to say which personal tasks an AI assistant handles well, which it handles badly, and how to protect yourself from its mistakes. This matters because students and job seekers who trust every answer lose marks and opportunities, while those who understand the limits save hours every week.</p>

<h3>A short recap</h3>
<p>An <strong>AI assistant</strong> (such as ChatGPT, Gemini, Claude and Copilot) is a program that predicts useful text from patterns it learned from huge amounts of writing. It does not "know" things the way a teacher does, and it does not understand your life. Features, limits and prices of these tools change often, so check the current details in the tool itself. If you want the basics in more depth, the course Practical AI Tools: Using AI Assistants Wisely covers them.</p>

<h3>Good at, and bad at</h3>
<table>
<thead><tr><th>Usually good at</th><th>Usually risky or weak at</th></tr></thead>
<tbody>
<tr><td>Explaining ideas in simple words</td><td>Exact facts, dates, statistics and quotes</td></tr>
<tr><td>Drafting and rewriting text</td><td>Knowing your school, employer or local rules</td></tr>
<tr><td>Making lists, plans and practice questions</td><td>Recent news and changing prices</td></tr>
<tr><td>Giving ideas and different viewpoints</td><td>Maths with many steps, unless you check it</td></tr>
<tr><td>Role-playing a conversation</td><td>Keeping a secret (treat everything you type as not private)</td></tr>
</tbody>
</table>

<h3>Three risks to remember</h3>
<ol>
<li><strong>Hallucination:</strong> the assistant states something false in a confident voice, such as a book that does not exist. It is not lying on purpose; it is producing text that sounds right.</li>
<li><strong>Bias:</strong> it learned from human writing, which contains stereotypes and gaps. Information about East Africa is often thinner than information about Europe or America.</li>
<li><strong>Privacy:</strong> what you type may be stored or reviewed according to the company's settings. Never treat the chat as a private diary.</li>
</ol>

<h3>Worked example: one question, two prompts</h3>
<p>A student in Kisumu wants to understand inflation. A weak prompt:</p>
<pre><code>Tell me about inflation.</code></pre>
<p>This usually returns a long general essay that may be too advanced and does not help with her exam. A better prompt:</p>
<pre><code>Explain inflation to a Form Four student in Kenya in 150 words. Use an example with the price of maize flour. End with two questions to test me.</code></pre>
<p>This typically returns a short, level-appropriate answer with a local example and two questions. Her next step is still to compare it with her textbook, because the assistant can slip on details.</p>

<h3>Case scenario</h3>
<p>Brian, a final-year student in Mbarara, asked an assistant for "five journal articles on solar power in Uganda" for his project. It returned five neat references with authors and years. When he tried to find them at the library, three did not exist. He nearly submitted them. After a lecturer's warning he changed his method: he used the assistant to suggest search words, then found real articles himself. The lesson: an assistant can invent sources that look real, so every source must be found and opened by you.</p>

<blockquote><p><strong>Quick check:</strong> What is a hallucination in AI?</p><p>Answer: A confident answer that is false or invented, because the tool produces likely-sounding text rather than checked facts.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Name one task an assistant is usually good at and one it is risky at.</p><p>Answer: Good at explaining an idea simply; risky at giving exact statistics or quotes, because it may invent them.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open an AI assistant and ask it to explain one idea from your studies or work in simple words.</li>
<li>Ask for three facts that include a number or date, then check each in a trusted source.</li>
<li>Write down how many were right and how many were wrong or unclear.</li>
<li>Keep that result as your reminder to verify.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Assistants predict likely text; they do not check facts.</li>
<li>They are strong at explaining, drafting and planning, and weak at exact facts.</li>
<li>Watch for hallucinations, bias and privacy risks.</li>
<li>Tool features and prices change, so check them yourself.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Prompt patterns for personal use',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write prompts that get useful answers for your own studies, writing and planning. This matters because the same assistant gives a vague answer to a vague question and a sharp answer to a clear one, and learning a simple pattern saves you data, time and frustration.</p>

<h3>The six building blocks</h3>
<table>
<thead><tr><th>Block</th><th>What it does</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Role</td><td>Sets the point of view</td><td>Act as a patient biology teacher</td></tr>
<tr><td>Task</td><td>Says what to do</td><td>Explain photosynthesis</td></tr>
<tr><td>Context</td><td>Gives your situation</td><td>I am in Senior Three and I find it confusing</td></tr>
<tr><td>Format</td><td>Shapes the answer</td><td>Use five short bullet points</td></tr>
<tr><td>Examples</td><td>Shows the style you want</td><td>Here is a sample answer I liked</td></tr>
<tr><td>Iteration</td><td>Improves the answer</td><td>Make it shorter and add a local example</td></tr>
</tbody>
</table>
<p>You do not need all six every time. Context and format are the two that help most.</p>

<h3>Ask for questions before answers</h3>
<p>A powerful habit is to let the assistant ask you what it needs. Add this line to many prompts:</p>
<pre><code>Before you answer, ask me up to three questions if you need more information.</code></pre>
<p>This stops the assistant from guessing about your situation.</p>

<h3>Iterate, do not restart</h3>
<p>The first answer is a draft. Follow up with short instructions: "shorter", "simpler words", "add a Ugandan example", "give me a table", "what might be wrong with this answer?". Each follow-up costs little data compared with starting again.</p>

<h3>Worked example: a message to a landlord</h3>
<p>Peter in Nairobi needs to ask his landlord to repair a leaking roof. Weak prompt:</p>
<pre><code>Write a message to my landlord.</code></pre>
<p>This usually gives a generic letter with placeholders and the wrong tone. Stronger prompt:</p>
<pre><code>Act as a polite but firm tenant. Write a WhatsApp message of under 80 words to my landlord, Mr Otieno. The roof above my bedroom has leaked for two weeks, I have sent photos, and I pay rent on time. Ask for a repair this week. Keep a respectful tone.</code></pre>
<p>This typically returns a short, usable message. Peter then changes any detail that is not true and sends it in his own name.</p>

<h3>Worked example: reuse a template</h3>
<p>Save a prompt that works. For example, keep this in your phone notes and paste in your own text each time:</p>
<pre><code>Act as an editor. Improve the clarity of the text below without changing my meaning. Keep it under 120 words. Then list what you changed.

[paste text here]</code></pre>

<h3>Case scenario</h3>
<p>Sandra, a job seeker in Jinja, typed "make my CV better" and pasted her whole CV. The assistant rewrote it with impressive achievements she never had, such as "managed a team of 20". She sent it without reading closely and was embarrassed when an interviewer asked about the team. Afterwards she changed her prompt to "Improve the wording only. Do not add any facts, numbers or achievements that I did not write." The lesson: the assistant tries to be helpful and may add things, so tell it what it must not change and read every line.</p>

<blockquote><p><strong>Quick check:</strong> Which two prompt blocks usually help most?</p><p>Answer: Context and format, because they tell the assistant about your situation and the shape of answer you need.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> The first answer is poor. What should you do?</p><p>Answer: Iterate with a short follow-up such as "simpler words" or "add an example", because the first answer is a draft.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick a real task this week, such as a message, a study topic or a plan.</li>
<li>Write a weak one-line prompt and note the answer.</li>
<li>Rewrite it using role, context and format, and compare the answers.</li>
<li>Save the better prompt in a notes app as a template.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use role, task, context, format, examples and iteration as needed.</li>
<li>Give context and say what the answer must look like.</li>
<li>Tell the assistant what it must not change or invent.</li>
<li>Follow up instead of starting again, and save prompts that work.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Privacy, scams and deepfakes: staying safe',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to decide what must never be typed into an AI assistant and recognise AI-powered scams and fake media. This matters because criminals now use the same tools you do, and one careless message can cost you money, a job or your reputation.</p>

<h3>Never paste these</h3>
<ul>
<li>Passwords, PINs, one-time codes and mobile money PINs</li>
<li>National ID numbers, passport numbers, bank or card numbers</li>
<li>Private photos or messages of other people</li>
<li>Confidential material from your school or employer, such as exam papers, student records, customer lists or unpublished plans</li>
<li>Medical details you would not want shared</li>
</ul>
<p>A safe habit: before you paste, ask "Would I be comfortable if this appeared on a notice board?" If not, remove names and numbers, or do not use the tool for that task. Many tools have settings about saving chat history or using it for training; look in the settings and choose the most private option available, remembering that options differ and change.</p>

<h3>How to anonymise</h3>
<p>Replace real details with labels. Instead of a real customer's name, phone number and debt, write "Customer A owes a late payment of about 300,000 shillings". The assistant can still help with the wording, and nothing private leaves your hands.</p>

<h3>AI-powered scams</h3>
<p>Scammers use AI to write convincing messages in any language, to clone a voice from a short clip and to make fake photos and videos, called <strong>deepfakes</strong>. Common patterns include a "relative" calling in an urgent voice to ask for mobile money, a fake job offer that asks you to pay a fee, or a "bank officer" asking for your code.</p>
<table>
<thead><tr><th>Warning sign</th><th>What to do</th></tr></thead>
<tbody>
<tr><td>Urgency: "send now or something bad happens"</td><td>Stop, hang up and call the person on a number you already know</td></tr>
<tr><td>Request for money, codes or PINs</td><td>Never share them; real banks and mobile money providers do not ask</td></tr>
<tr><td>Job that asks you to pay first</td><td>Treat as a scam; check the company through its official channel</td></tr>
<tr><td>Video or audio of a public figure saying something shocking</td><td>Check trusted news sources before believing or sharing</td></tr>
</tbody>
</table>

<h3>Worked example: a family code word</h3>
<p>Agnes in Entebbe agreed a secret word with her family. If anyone calls asking for money in an emergency, they must say the word. A scammer later used a cloned voice that sounded like her brother, but could not give the word, so she hung up and called her brother directly. He was safe at work. A simple, free habit beat an advanced fake.</p>

<h3>Case scenario</h3>
<p>Moses, a payroll assistant at a small company in Kampala, pasted the staff salary sheet into a free assistant to "make a summary table" and then forgot about it. The sheet had names, phone numbers and salaries. The manager discovered it during a review and Moses received a formal warning. The better choice would have been to remove names and numbers or to use a spreadsheet's own tools. The lesson: confidential work data belongs to your employer, and convenience never excuses sharing it.</p>

<blockquote><p><strong>Quick check:</strong> Is it safe to paste a customer list into a free AI assistant to tidy it?</p><p>Answer: No, because it holds private data you do not own; remove identifying details or use another method.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A voice that sounds like your cousin begs for urgent mobile money. What first?</p><p>Answer: Hang up and call your cousin on a known number, because voices can be cloned.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open the settings of the AI assistant you use and find the options about chat history and data use.</li>
<li>Choose the most private option you are comfortable with.</li>
<li>Agree a family or team code word for emergency money requests.</li>
<li>Take a message you plan to paste into an assistant and replace each name and number with a label.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Never paste passwords, PINs, ID numbers or confidential school or employer data.</li>
<li>Anonymise with labels before asking for help.</li>
<li>Urgent money requests, even in a familiar voice, must be verified another way.</li>
<li>Deepfakes exist, so check surprising videos and audio against trusted sources.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Why can an AI assistant give a confident but false answer?
- It always searches the internet live and copies errors
- It is built to deceive users
* It produces likely-sounding text and does not check facts
- It only knows facts from the last week
= Assistants predict plausible text. They can sound sure even when the content is invented, so important facts must be verified.

Q: Which prompt is most likely to give a useful answer?
- Explain economics
- Tell me everything about money
* Act as a tutor and explain supply and demand to a Senior Four student in Uganda in 120 words, using an example with tomatoes
- Economics please
= The strong prompt includes role, context, format and an example, which shapes a focused and level-appropriate answer.

Q: Which items should you never paste into an AI assistant? (choose all that apply)
* A mobile money PIN
* A national ID number
- A request to explain how photosynthesis works
* A confidential exam paper from your school
= PINs, ID numbers and confidential school material are private or protected. A general science question is safe.

Q: A student asks an assistant for five references and uses them without checking. Two do not exist. What is the best lesson?
- Choose a different assistant and trust it
* Find and open every source yourself before citing it
- Remove the dates so nobody can check
- Add more references from the same tool
= Assistants can invent sources that look real. A source counts only when you have found and read it.

Q: A caller using your sister's voice asks for urgent mobile money. What should you do?
- Send a small amount first to test
* Hang up and call her on a number you already know
- Ask the caller to send a photo
- Reply with your mobile money PIN so she can help herself
= Voices can be cloned. Verify through a known contact, and never share your PIN.

Q: Sandra asked an assistant to improve her CV and it added a team of 20 people she never managed. What should she do?
- Keep it because it makes her look strong
* Remove it, and next time say the assistant must not add facts
- Add more invented achievements
- Stop using CVs
= Added claims are false and can damage trust in an interview. A prompt should forbid new facts, and you must read every line.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: AI as your study partner',
            'summary' => 'Summarise notes, get hard ideas explained at your level, practise with questions and flashcards, plan revision and learn languages.',
            'lessons' => [
                [
                    'title'   => 'Summaries and explanations at your level',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to turn your own notes into clear summaries and ask for hard ideas to be explained at the right level. This matters because understanding comes before memorising, and an assistant can offer a second explanation at any hour when your teacher or classmates are not available.</p>

<h3>Summarising your own notes</h3>
<p>Paste a section of your notes (not a whole textbook, and never material you are not allowed to share) and give a clear instruction. Useful summary prompts:</p>
<pre><code>Summarise my notes below in 8 bullet points for revision. Keep my key terms. Do not add facts that are not in my notes.

[paste notes]</code></pre>
<p>The last sentence matters. Without it, an assistant may add details from its own memory, which may be wrong or may not match your syllabus. After the summary, compare it with your notes and mark anything missing.</p>

<h3>Explaining at your level</h3>
<p>If a textbook paragraph confuses you, paste it and ask for help using one of these patterns:</p>
<ul>
<li><strong>Level:</strong> "Explain this as if I am 14", or "as a first-year university student".</li>
<li><strong>Analogy:</strong> "Use an example from farming or a boda boda".</li>
<li><strong>Steps:</strong> "Break this into numbered steps".</li>
<li><strong>Different angle:</strong> "Explain it a different way. I did not understand the first one."</li>
</ul>

<h3>The teach-back method</h3>
<p>After reading an explanation, close the chat and write what you understood in your own words. Then paste your version and ask: "Tell me what is correct, what is missing and what is wrong." This turns the assistant into a coach and forces your own brain to work, which is how learning sticks.</p>

<h3>Worked example: electricity in Physics</h3>
<p>Aisha, a Senior Five student in Mbale, does not understand the difference between current and voltage. Weak prompt:</p>
<pre><code>What is voltage?</code></pre>
<p>This usually returns a textbook definition that repeats what confused her. Stronger prompt:</p>
<pre><code>I am a Senior Five Physics student. I mix up current and voltage. Explain the difference using water flowing in a pipe from a tank. Then give me two short questions to check I understood. Do not give the answers until I reply.</code></pre>
<p>This typically gives an analogy of pressure and flow, followed by two questions. Aisha answers in her own words, then asks the assistant to check them. She still confirms the definitions against her textbook, because analogies are simplifications and can mislead if pushed too far.</p>

<h3>Case scenario</h3>
<p>Joseph, a university student in Kampala, asked an assistant to explain a statistics formula. It gave a confident explanation, and he copied it into his notes. Days later, his lecturer showed that the formula used the wrong divisor, a small but common mistake. Joseph lost marks on a test. He now checks formulas in his lecture slides or textbook before writing them down, and uses the assistant to explain what the correct formula means rather than to supply it. The lesson: use AI to understand, but confirm formulas, definitions and numbers in your official course material.</p>

<blockquote><p><strong>Quick check:</strong> Why tell the assistant "do not add facts that are not in my notes"?</p><p>Answer: So the summary stays faithful to your course, because it may otherwise add details that are wrong or off-syllabus.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is the teach-back method?</p><p>Answer: Explain the idea in your own words and ask the assistant to correct you, because producing the explanation yourself builds memory.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose one page of your own notes and ask for an 8-bullet summary with no added facts.</li>
<li>Compare the summary with the page and note anything missing or changed.</li>
<li>Pick a confusing idea and ask for an explanation with a local analogy.</li>
<li>Write the idea in your own words and ask the assistant to mark it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Tell the assistant to stay inside your notes when summarising.</li>
<li>Ask for explanations at your level with local analogies.</li>
<li>Use teach-back so you do the thinking.</li>
<li>Check formulas and definitions in official course material.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Practice questions, flashcards and revision plans',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to generate practice questions and flashcards from your own notes, and build a revision plan that spreads study over time. This matters because testing yourself and revisiting topics at intervals are among the best-known ways to remember, and an assistant can prepare the practice material in minutes.</p>

<h3>Self-testing beats re-reading</h3>
<p>Re-reading feels easy but often gives a false feeling of knowing. Trying to recall an answer without looking is harder and works better. An assistant can set the questions while you do the recalling.</p>
<pre><code>Using only my notes below, write 10 questions for a Senior Four Chemistry test: 6 short-answer and 4 multiple-choice. Do not show the answers. I will answer first and you will mark me.

[paste notes]</code></pre>

<h3>Flashcards</h3>
<p>A flashcard has a question on one side and an answer on the other. Ask for a format you can paste into a notes app, spreadsheet or app of your choice:</p>
<pre><code>Make 15 flashcards from my notes in the format: Question | Answer. Keep each answer under 15 words.</code></pre>
<p>Check each card against your notes. A wrong flashcard is worse than none, because you will learn the mistake.</p>

<h3>Spaced revision</h3>
<p><strong>Spaced revision</strong> means reviewing a topic after one day, then three days, then a week, then a month, rather than in one long night. Ask for a plan that fits your real life:</p>
<pre><code>My exam is in 5 weeks. I can study 90 minutes on weekdays and 3 hours on Saturday, and I have no power on Tuesday evenings. I have 6 topics: [list]. Make a weekly plan that revisits each topic after 1 day, 3 days and 7 days. Put it in a table.</code></pre>

<table>
<thead><tr><th>Weak request</th><th>Better request</th></tr></thead>
<tbody>
<tr><td>Make me a revision plan</td><td>Give exam date, hours available, topics, and power or data limits</td></tr>
<tr><td>Give me test questions</td><td>Give level, subject, number and type of questions, and say "from my notes only"</td></tr>
<tr><td>Quiz me</td><td>Ask one question at a time and wait for my answer before correcting</td></tr>
</tbody>
</table>

<h3>Worked example: a one-question-at-a-time quiz</h3>
<p>Daniel, preparing for a business studies exam in Nakuru, uses this prompt:</p>
<pre><code>Quiz me on the notes below. Ask one question at a time, wait for my answer, tell me if I am right, explain briefly, then ask the next. Start with easier questions and make them harder. After 10 questions, list the topics I should revise.</code></pre>
<p>This typically produces an interactive session where he answers by typing, gets feedback and finishes with a list of weak topics. He then spends his next study session only on those topics, which saves time.</p>

<h3>Case scenario</h3>
<p>Mercy, a Senior Six student in Gulu, asked an assistant for "the most likely exam questions" and revised only those. The questions were guesses and did not match the national paper, which tested other parts of the syllabus. She scored far below her target. Her teacher advised using the official syllabus and past papers as the guide, and using AI to create extra practice on every topic. The lesson: an assistant cannot know your exam; it can only guess, so build your revision around the official syllabus.</p>

<blockquote><p><strong>Quick check:</strong> Why is self-testing better than only re-reading notes?</p><p>Answer: Recalling answers is harder and strengthens memory, while re-reading can create a false feeling of knowing.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What should you do with AI-made flashcards before studying them?</p><p>Answer: Check each against your notes, because a wrong card teaches a mistake.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose one topic from your notes and ask for 8 questions without answers.</li>
<li>Answer them on paper first, then ask the assistant to mark you.</li>
<li>Ask for 10 flashcards and correct any that disagree with your notes.</li>
<li>Ask for a one-week spaced revision plan that fits your real timetable and power or data limits.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Test yourself instead of only re-reading.</li>
<li>Ask for questions from your notes and answer before seeing solutions.</li>
<li>Verify flashcards against your notes.</li>
<li>Use the official syllabus and past papers as the guide; plan revision with spacing.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Checking your answers and learning languages',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to ask an assistant to check your own work without giving you the answer, and to practise a language through conversation. This matters because feedback is what turns practice into progress, and many learners have nobody nearby to give it quickly.</p>

<h3>Work first, feedback second</h3>
<p>If you ask for the solution before trying, you learn little. Make a rule: <strong>attempt first, then ask for feedback.</strong> Paste your attempt and say what kind of feedback you want.</p>
<pre><code>Here is my answer to a Geography essay question about soil erosion. Do not rewrite it. Tell me: (1) two things I did well, (2) two things missing, (3) one sentence that is unclear. Then ask me to improve it.

[paste your answer]</code></pre>
<p>For problem solving such as maths, ask for hints rather than solutions:</p>
<pre><code>Check my working below line by line. Tell me which line first goes wrong, but do not give the full answer. Give me one hint.</code></pre>
<p>Because assistants can make arithmetic mistakes, verify the final result yourself with a calculator or by substituting back.</p>

<h3>Learning a language</h3>
<p>An assistant can act as a patient conversation partner for English, French, Swahili, Kinyarwanda or other languages, though quality varies by language and is often weaker for smaller languages. Use it for practice, and confirm important points with a teacher or a good dictionary.</p>
<ul>
<li><strong>Conversation:</strong> "Chat with me in simple French about the market. Correct my mistakes after each message and explain briefly."</li>
<li><strong>Vocabulary:</strong> "Give me 10 useful phrases for a hotel receptionist, with meaning and an example."</li>
<li><strong>Grammar:</strong> "Explain the past tense with three examples, then give me five sentences to fix."</li>
<li><strong>Pronunciation:</strong> use voice features if your tool has them, and listen to real speakers too.</li>
</ul>

<h3>Worked example: improving English for work</h3>
<p>Brenda, a hotel trainee in Mombasa, wants to speak better English with guests. She sets up a role-play:</p>
<pre><code>Act as a hotel guest checking in. Speak in simple English. After each of my replies, add a short line called Tips with one correction to my English. Keep going for 10 turns.</code></pre>
<p>This typically gives a realistic dialogue with small corrections. She practises ten minutes a day on her phone. The tips are helpful but not perfect, so she writes down any she is unsure about and asks her supervisor.</p>

<h3>Common mistakes</h3>
<ul>
<li>Copying the corrected version without understanding why it changed.</li>
<li>Trusting the assistant on rare local words, idioms or tone in smaller languages.</li>
<li>Practising only by typing, when the real goal is speaking and listening.</li>
</ul>

<h3>Case scenario</h3>
<p>Ivan, a university student in Dar es Salaam, used an assistant to check his translation of a Swahili proverb for a language assignment. The assistant gave a smooth English version, but it changed the meaning, and his lecturer, a native speaker, marked it wrong. Ivan realised he had skipped his own judgement. For future work, he wrote his own translation first, asked the assistant for alternatives, and asked a Swahili-speaking friend to confirm the meaning. The lesson: for languages with less data online, the assistant may sound fluent but still be wrong, so involve a human who knows the language.</p>

<blockquote><p><strong>Quick check:</strong> Why attempt a problem before asking an assistant for feedback?</p><p>Answer: Your own attempt shows what you actually understand and makes the feedback specific and useful.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why confirm AI translations of smaller languages with a person?</p><p>Answer: Quality is often weaker for such languages and meaning can change while still sounding fluent.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a short answer to a question from your course and ask for feedback without a rewrite.</li>
<li>Redo the answer using the feedback and compare the two versions.</li>
<li>Start a 10-turn role-play in a language you are learning and ask for one correction per turn.</li>
<li>Note two corrections and check them in a dictionary or with a person.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Attempt first, then ask for feedback or hints.</li>
<li>Check maths and final results yourself.</li>
<li>Use role-play for language practice and ask for brief corrections.</li>
<li>Confirm meaning with a human, especially in smaller languages.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which instruction keeps an AI summary faithful to your notes?
- Make it as long as possible
- Add everything you know about the topic
* Do not add facts that are not in my notes
- Use difficult words so it sounds academic
= Without that rule the assistant may add details from its own memory that may be wrong or off-syllabus.

Q: What does the teach-back method involve?
* Explaining the idea in your own words and asking the assistant to correct you
- Copying the assistant's explanation into your notes
- Asking the assistant to take your exam
- Reading the explanation five times
= Producing the explanation yourself builds memory and shows gaps that the assistant can then point out.

Q: Which is the better way to use AI for maths homework?
- Paste the question and copy the answer
* Attempt it, then ask which line goes wrong and for one hint
- Ask for the answers to every question at once
- Avoid checking the final result
= Hints after your own attempt build skill. Verify results yourself because assistants can make arithmetic errors.

Q: Mercy revised only "the most likely exam questions" an assistant guessed, and the real paper tested other topics. What should she have used as her guide?
- Only the assistant's guesses
- Her friends' predictions
* The official syllabus and past papers, with AI used for extra practice on every topic
- Only the topics she liked
= The assistant cannot know the exam. The syllabus and past papers show what is tested.

Q: Which are good features of a spaced revision plan? (choose all that apply)
* Topics are revisited after increasing gaps such as 1, 3 and 7 days
- All study is done the night before
* It fits your real hours and power or data limits
- It avoids any self-testing
= Spacing and a realistic timetable improve memory. Cramming and skipping testing work less well.

Q: Ivan trusted an assistant's English version of a Swahili proverb, and the lecturer marked it wrong. What is the best lesson?
- Smaller languages are always translated perfectly
- Never use any translation tool
* Write your own version first and confirm meaning with a speaker of the language
- Make the sentence longer
= Output can sound fluent yet change the meaning, especially in smaller languages. A human who knows the language should confirm important work.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Writing, honesty and research',
            'summary' => 'Draft and improve your writing in your own voice, follow academic integrity rules, and research with AI while verifying every fact.',
            'lessons' => [
                [
                    'title'   => 'Drafting and improving writing in your own voice',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use an assistant to draft, improve and proofread emails, reports and applications while keeping your own voice and your own facts. This matters because clear writing opens doors, and many people in East Africa write in a second or third language and benefit from a patient editor.</p>

<h3>Three jobs for an assistant as writing helper</h3>
<ol>
<li><strong>Structure:</strong> suggest an outline so you know what to say first.</li>
<li><strong>Edit:</strong> improve clarity, grammar and tone of text you wrote.</li>
<li><strong>Proofread:</strong> find spelling and punctuation errors only.</li>
</ol>
<p>Editing and proofreading keep your ideas yours. Asking the assistant to write the whole thing from nothing gives you generic text that does not sound like you.</p>

<h3>Keep your voice</h3>
<p>Your <strong>voice</strong> is how you naturally write: your word choice, rhythm and warmth. To protect it, show the assistant a sample:</p>
<pre><code>Here is a paragraph I wrote that sounds like me:
"[paste 3-4 sentences]"
Edit the email below to match this style. Keep my facts exactly. Do not add anything I did not write.

[paste email]</code></pre>
<p>Then read the result aloud. If you would never say a sentence, change it. Words that nobody uses in daily speech, such as "delve" or "tapestry", are often a sign of copied AI text.</p>

<h3>Ask for choices, then choose</h3>
<p>Request two or three versions with different tones, such as formal, friendly and short. Choosing and combining parts is a skill, and it keeps you in charge.</p>

<h3>Worked example: a leave request</h3>
<p>Patrick, an assistant teacher in Mbale, writes a rough message:</p>
<pre><code>boss i need leave 3 days from monday my aunt died in Soroti i will come back thursday and give my classes to teacher Ruth</code></pre>
<p>Weak prompt: "Fix this." This often returns a polished letter, but the assistant may invent dates or a reason. Better prompt:</p>
<pre><code>Rewrite this as a respectful email to my headmaster. Keep all facts as written. Do not add dates, names or reasons. Under 70 words. Then list any information that seems to be missing.</code></pre>
<p>This typically returns a short, respectful email and a list such as "exact dates" that Patrick can fill in. He sends the message after checking it.</p>

<h3>Case scenario</h3>
<p>Doreen, applying for a scholarship, asked an assistant to "write my personal statement" from only her name and course. The statement was smooth but generic, with a moving story about a village childhood that was not hers. The panel had read many similar statements and invited only applicants whose stories felt specific. Doreen was not shortlisted. For her next application she wrote her own rough story about her mother's tailoring shop, then used the assistant only to tighten the language, and she was shortlisted. The lesson: specific true details make writing powerful, and an assistant cannot supply your life.</p>

<blockquote><p><strong>Quick check:</strong> Which keeps your voice better, "write it for me" or "edit what I wrote"?</p><p>Answer: Editing what you wrote, because the ideas, facts and style remain yours.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What instruction stops an assistant adding invented details?</p><p>Answer: "Keep all facts as written and do not add anything," because it removes the temptation to fill gaps.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a rough version of a real email or message you must send soon.</li>
<li>Ask an assistant to improve clarity and tone, keeping your facts, and to list missing information.</li>
<li>Read the result aloud and change every sentence you would not say.</li>
<li>Send it, or save it as a template for next time.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use AI for structure, editing and proofreading of your own ideas.</li>
<li>Show a sample of your style and forbid invented facts.</li>
<li>Read aloud and keep only what sounds like you.</li>
<li>Specific true details beat smooth generic text.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Honesty and academic integrity',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to tell when using AI is allowed, when it is cheating, and how to be open about your use. This matters because penalties for breaking rules can include lost marks, cancelled results or dismissal from a course, and honesty is also part of your personal brand.</p>

<h3>Rules differ, so ask</h3>
<p><strong>Academic integrity</strong> means doing your own work honestly and giving credit where it is due. Schools, universities, exam boards and employers each set their own AI rules, and these rules are changing. Some allow AI for brainstorming but not for writing. Some allow it only if you declare it. Some ban it for certain assignments. There is no single answer, so read the rules in your handbook or assignment brief, and when in doubt, <strong>ask your teacher or lecturer in writing.</strong></p>

<h3>A simple traffic light</h3>
<table>
<thead><tr><th>Colour</th><th>Examples (always check your own rules)</th></tr></thead>
<tbody>
<tr><td>Usually fine</td><td>Asking for an explanation of a topic; making practice questions; checking grammar of your own text when allowed</td></tr>
<tr><td>Ask first</td><td>Brainstorming ideas; getting an outline; getting feedback on a draft</td></tr>
<tr><td>Cheating where forbidden</td><td>Submitting AI-written text as your own; using AI in a closed-book test; inventing references</td></tr>
</tbody>
</table>

<h3>Be open and cite</h3>
<p>When AI use is allowed, say how you used it. A short note is enough:</p>
<pre><code>AI use statement: I used an AI assistant to suggest an outline and to check my grammar. The ideas, research and final writing are my own.</code></pre>
<p>Follow your institution's way of citing AI if it has one. Also remember that an assistant is not a source of evidence: cite the real books, articles and official reports you found, never "the AI said".</p>

<h3>Why shortcuts backfire</h3>
<ul>
<li>You lose the skill the course was meant to give you, and the exam or interview will test it.</li>
<li>AI-detection tools are unreliable and can wrongly flag honest work, so keep your notes and drafts as proof of your own process.</li>
<li>Invented references and facts are easy for a marker to spot.</li>
</ul>

<h3>Worked example: asking permission</h3>
<p>Naomi, a college student in Eldoret, is unsure if she can use AI for a report. She sends her lecturer this message:</p>
<pre><code>Good afternoon. May I use an AI assistant to check the grammar of my report and to suggest a structure? The research and writing will be my own, and I will add a note saying how I used it. Thank you.</code></pre>
<p>The lecturer replies that grammar checking is allowed with a declaration but structure suggestions are not. Naomi now knows exactly what she can do, and she has the answer in writing.</p>

<h3>Case scenario</h3>
<p>Timothy, a Diploma student in Kampala, pasted an assignment question into an assistant and submitted the answer. The college's rules banned this. His lecturer noticed that the answer used terms never taught in class and that two of the references could not be found. Timothy was asked to explain it orally and could not. He received zero for the assignment and a formal warning. If he had asked first, he could have used AI for practice and explanations, which would have helped him pass. The lesson: the short-term gain is small, and the risk and loss of learning are large.</p>

<blockquote><p><strong>Quick check:</strong> You are unsure whether AI is allowed for an assignment. What should you do?</p><p>Answer: Ask your teacher or lecturer, preferably in writing, because rules differ between institutions and tasks.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Can you cite "the AI said" as evidence for a claim?</p><p>Answer: No, because an assistant is not a source; you must cite real sources that you found and read.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Find the written rules on AI use for your school, college, exam or workplace.</li>
<li>List what is allowed, what needs declaring and what is forbidden.</li>
<li>If the rules are unclear, write a short polite message to ask.</li>
<li>Draft a two-sentence AI use statement you could add to future work.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Rules differ and change, so read them and ask when unsure.</li>
<li>Never submit AI work as your own where it is forbidden.</li>
<li>Declare your use when allowed and cite real sources, not the assistant.</li>
<li>Keep drafts and notes as proof of your own work.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Researching with AI and verifying facts',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use an assistant to start research, and then check every important fact against reliable sources. This matters because decisions about health, money, studies and work depend on correct information, and wrong facts spread quickly when copied from a chat.</p>

<h3>What AI is good for in research</h3>
<ul>
<li>Explaining background so you know what to search for.</li>
<li>Suggesting keywords, questions and angles.</li>
<li>Comparing viewpoints in a simple table.</li>
<li>Helping you plan a research question and outline.</li>
</ul>
<p>It is not a library. Treat what it says as <strong>leads to check</strong>, not as evidence.</p>

<h3>The verification routine</h3>
<ol>
<li><strong>Mark the claims.</strong> Highlight every number, date, name, law, quotation and "studies show".</li>
<li><strong>Find a primary or official source.</strong> Government statistics offices, ministries, central banks, university and hospital websites, and recognised international organisations are better than blogs.</li>
<li><strong>Check the date.</strong> Laws, prices and rates change.</li>
<li><strong>Cross-check.</strong> Look for two independent reliable sources.</li>
<li><strong>Record where you found it.</strong> Keep the real link or title for citing.</li>
</ol>

<h3>Signs of a weak source</h3>
<table>
<thead><tr><th>Warning sign</th><th>Why it matters</th></tr></thead>
<tbody>
<tr><td>No author, date or organisation</td><td>You cannot judge who is responsible</td></tr>
<tr><td>Shocking claims with no evidence</td><td>Often made to get shares or sell something</td></tr>
<tr><td>Promises of guaranteed cures or quick money</td><td>Common in scams</td></tr>
<tr><td>Only one site says it</td><td>It may be copied error</td></tr>
</tbody>
</table>

<h3>Ask the assistant to help you doubt</h3>
<pre><code>List the main factual claims in your last answer that I should verify. For each, suggest what kind of official source would confirm it. Say clearly where you are unsure.</code></pre>
<p>This does not make the answer reliable, but it points you at the weak spots. Never ask "Are you sure?" and accept the reply as proof; an assistant may simply agree or change its answer.</p>

<h3>Worked example: a farming fact</h3>
<p>Esther, a farmer's daughter in Masaka, asks an assistant for the best spacing for coffee seedlings. It gives a confident number in metres. She does not copy it. Instead she asks, "What should I verify, and from what kind of source?" The assistant suggests an agricultural research body or the local extension officer. Esther calls the extension officer, who gives advice for her variety and soil. The number from the assistant was close, but not the same, and the difference mattered for her planting plan.</p>

<h3>Case scenario</h3>
<p>Hassan, a youth leader in Kisumu, asked an assistant for statistics on youth unemployment for a funding proposal. It returned a precise percentage and the name of a report. Hassan put them in the proposal. The funder's officer searched for the report and could not find it, and the percentage did not match official figures. The proposal lost credibility and was rejected. Hassan learned to take statistics only from official statistics offices and to cite the real document with its year. The lesson: a precise-looking number is not proof, and one invented figure can sink an otherwise good proposal.</p>

<blockquote><p><strong>Quick check:</strong> Name two kinds of claims you must always verify.</p><p>Answer: Numbers and quotations (also dates, laws and names), because assistants often invent or distort them.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> If you ask "Are you sure?" and the assistant says yes, is the fact now verified?</p><p>Answer: No, because it may simply agree; only a reliable independent source verifies a fact.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Ask an assistant a factual question about a topic you care about, such as a health, farming or money fact.</li>
<li>Highlight every number, date and name in the answer.</li>
<li>Find two reliable sources and compare.</li>
<li>Write the correct version and the source you used.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use AI to start research; treat its output as leads.</li>
<li>Verify claims with official or primary sources and check dates.</li>
<li>Do not accept "Are you sure?" as proof.</li>
<li>Cite the real source you found, never an invented one.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which request best keeps your own voice in an email?
- Write the whole email from scratch with no details from me
* Edit my draft to match this sample of my style and do not add facts
- Make it sound as fancy as possible
- Replace all my words with new ones
= Giving your own draft and style sample keeps the ideas and voice yours. The assistant only polishes.

Q: What should you do when you are unsure whether AI is allowed in an assignment?
- Use it quietly and hope it is fine
* Ask your teacher or lecturer, preferably in writing
- Ask the AI to decide
- Ask a classmate who has no authority
= Rules differ between institutions and tasks. Written permission protects you.

Q: Which of these usually counts as cheating when it is forbidden? (choose all that apply)
* Submitting AI-written text as your own work
- Asking an assistant to explain a topic you are studying where explanations are allowed
* Using AI in a closed-book test
* Citing references the assistant invented
= Passing off AI work, breaking test rules and using invented references are dishonest. Studying with explanations is usually fine where allowed.

Q: Doreen's AI-written scholarship statement told a village story that was not hers and she was not shortlisted. What worked better later?
- Writing a longer statement with more adjectives
* Writing her own rough true story and using AI only to tighten the language
- Copying a winner's statement
- Letting the assistant invent a stronger story
= Specific true details make a statement credible. Fabricated stories are generic and dishonest.

Q: Hassan used an assistant's statistic in a funding proposal and it could not be found in official sources. What should he have done?
- Added more decimal places
* Taken the figure from an official statistics source and cited the real document and year
- Asked the assistant "Are you sure?" and accepted yes
- Removed the date
= Official sources are verifiable. A precise number from a chat is not proof.

Q: Why does "Are you sure?" not verify a fact?
* The assistant may simply agree or change its answer without checking anything
- Assistants never answer that question
- It uses too much data
- It always makes the answer shorter
= Verification needs independent reliable sources. The assistant's confidence is not evidence.

Q: Esther received a spacing figure for coffee seedlings from an assistant. What is the best next step?
- Plant using it immediately
- Post it online as advice
* Check it with the local agricultural extension officer or a reliable agricultural source
- Ask for a bigger number
= Practical advice depends on variety, soil and place. A local expert or official source gives reliable guidance.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Planning and everyday productivity',
            'summary' => 'Plan your time and goals, handle meeting notes, voice, translation, spreadsheets and presentations, and work well on low data or offline.',
            'lessons' => [
                [
                    'title'   => 'Planning your time: timetables, to-do lists and goals',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use an assistant to turn a messy list of tasks into a realistic timetable and a clear goal plan. This matters because most people fail at plans that ignore real life, such as travel time, power cuts or family duties, and an assistant can help you fit those in if you tell it.</p>

<h3>Step 1: dump everything</h3>
<p>Write every task and commitment in rough words, in any order, including deadlines and how long each might take. Do not worry about neatness.</p>

<h3>Step 2: give real limits</h3>
<p>The best plans include your constraints. Tell the assistant your wake-up time, travel, work or class hours, family duties, prayer or rest times, and any power or data problems.</p>
<pre><code>Make a Monday to Friday timetable for me. Fixed: classes 8:00 to 1:00, matatu travel 1:00 to 2:00 and 5:30 to 6:30. Tasks: finish assignment (6 hours, due Friday), revise Economics (4 hours), apply for two internships (2 hours), washing and cooking daily (1 hour). My power usually fails after 8 pm. Show it as a table and leave one free evening.</code></pre>

<h3>Step 3: prioritise</h3>
<p>Ask the assistant to sort tasks into <strong>urgent and important</strong>, <strong>important but not urgent</strong>, <strong>urgent but not important</strong> and <strong>neither</strong>. Then you decide, because only you know your priorities. A useful follow-up prompt is: "What am I probably underestimating in this plan?"</p>

<h3>Goal planning with SMART</h3>
<p>A <strong>SMART goal</strong> is Specific, Measurable, Achievable, Relevant and Time-bound. Compare:</p>
<table>
<thead><tr><th>Vague goal</th><th>SMART goal</th></tr></thead>
<tbody>
<tr><td>Get better at English</td><td>Practise English for 20 minutes a day for 60 days and score at least 70 percent on a practice test</td></tr>
<tr><td>Save money</td><td>Save 20,000 shillings every Friday for 6 months from my tailoring income</td></tr>
<tr><td>Start a business</td><td>Test one product with 10 customers by 30 November and record their feedback</td></tr>
</tbody>
</table>
<p>Ask an assistant: "Turn this vague goal into a SMART goal and break it into weekly steps. Ask me questions if anything is missing."</p>

<h3>Worked example: Brenda's week</h3>
<p>Brenda, a nursing student in Mbarara, pastes her tasks and limits. The assistant returns a timetable with study blocks of 50 minutes and 10-minute breaks. It puts her hardest subject in the morning because she said she is freshest then. It also warns that her plan has only 5 hours for 9 hours of tasks. Brenda decides to move the internship applications to the weekend. The assistant offered an overloaded plan on its own, but only the follow-up question exposed it.</p>

<h3>Case scenario</h3>
<p>Kelvin, a part-time student and boda boda rider in Jinja, asked an assistant to plan his week and did not mention that he rides until late on Fridays and Saturdays. The plan had him studying from 8 pm on both nights. He followed it for three days, felt exhausted and abandoned the plan altogether. Kelvin repeated the exercise with honest details: two free mornings, short study blocks after rides and a rest day. This plan survived the month. The lesson: a plan is only as good as the information you give, and being honest about your real limits makes it work.</p>

<blockquote><p><strong>Quick check:</strong> What makes a timetable from an assistant realistic?</p><p>Answer: Giving it your true limits such as travel, work, duties and power cuts, because it cannot guess them.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What does the T in SMART stand for?</p><p>Answer: Time-bound, meaning the goal has a deadline, which makes progress measurable.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write all your tasks for next week, with deadlines and rough hours.</li>
<li>List your fixed commitments and real limits.</li>
<li>Ask for a timetable table and then ask "What am I underestimating?"</li>
<li>Turn one vague goal into a SMART goal with weekly steps.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>List all tasks, then add real limits before asking for a plan.</li>
<li>Ask the assistant to challenge the plan, then decide yourself.</li>
<li>Use SMART goals with weekly steps.</li>
<li>Honest information gives plans you can follow.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Meeting notes, voice, translation, spreadsheets and slides',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use low-cost tools and AI help to turn voice into text, summarise meetings, translate carefully, get help with spreadsheets and prepare simple presentations. This matters because these everyday jobs eat hours, and doing them well makes you look organised at school, at work or in your own business.</p>

<h3>Voice notes to text</h3>
<p>Many phones and apps can turn speech into text, and some can transcribe recorded audio. Features and accuracy vary by phone, app and language, and accuracy is often lower for accents, noise and smaller languages. Always read the transcript and correct names and numbers. Get permission before recording other people, and do not record private or confidential conversations.</p>

<h3>Meeting notes and action lists</h3>
<p>Take your own rough notes or a transcript you are allowed to use, remove sensitive details, and ask:</p>
<pre><code>From the notes below, write: (1) a 5-line summary, (2) decisions made, (3) action items in a table with Owner, Task and Deadline. If an owner or deadline is not stated, write "not stated". Do not guess.

[paste notes]</code></pre>
<p>The phrase "write not stated, do not guess" is important. Without it the assistant may invent owners and dates.</p>

<h3>Translation</h3>
<p>Assistants and translation apps help with everyday messages, but they can miss tone, local expressions and technical terms. For anything important, such as a contract, a medical instruction or a school letter, have a person who knows both languages check it. A useful trick is "back-translation": translate the result back into the first language and see whether the meaning stayed the same.</p>

<h3>Spreadsheets with AI help</h3>
<p>If you use Excel or Google Sheets, you can describe your problem in words and ask for a formula or explanation. Example prompt:</p>
<pre><code>In Google Sheets, column B has quantities and column C has unit prices in shillings. Give me a formula for the total in column D for row 2 that I can copy down, and explain it in one sentence.</code></pre>
<p>Test the formula with two rows where you know the answer by hand. Do not paste private customer data; use made-up sample rows to get the formula.</p>

<h3>Presentations</h3>
<p>Ask for an outline, not the final deck: "Suggest a 6-slide outline for a 5-minute talk on saving with a village savings group, with one key message per slide." Then add your own facts, local examples and photos. Keep slides simple and speak in your own words.</p>

<h3>Worked example: a youth group meeting</h3>
<p>Rose, secretary of a youth group in Mukono, records rough bullet notes. She asks the assistant for a summary and an action table. It returns a clear table, but one row shows "Owner: Peter" when the notes never mentioned Peter. Because she used the "do not guess" rule and read the result, she spotted the error and corrected it before sending to members.</p>

<h3>Case scenario</h3>
<p>Dennis, a shopkeeper in Kampala, asked an assistant for a spreadsheet formula to total his daily sales. He copied it without testing. The formula added the wrong column, so his weekly profit looked about 15 percent higher than it was. He ordered extra stock based on this and had to sell it at a loss. Later he tested the formula with three rows he calculated on paper and found the mistake in minutes. The lesson: always test formulas on a small example whose answer you already know.</p>

<blockquote><p><strong>Quick check:</strong> Why add "write not stated, do not guess" when asking for action items?</p><p>Answer: It stops the assistant inventing owners or deadlines that were never in the notes.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> How can you test a formula from an assistant?</p><p>Answer: Try it on a few rows where you know the answer by hand, because a wrong formula can look believable.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Record or write a 2-minute set of notes about something you did today.</li>
<li>Ask for a short summary and an action table with "not stated" for gaps.</li>
<li>Ask for a spreadsheet formula for a small task and test it on three sample rows.</li>
<li>Ask for a 5-slide outline on a topic you know and add one local example to each slide.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Correct transcripts and respect privacy when recording.</li>
<li>Tell the assistant not to guess missing details.</li>
<li>Check important translations with a person and use back-translation.</li>
<li>Test every formula; ask for outlines, then add your own content.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Low-data and offline workflows',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to get value from AI tools when data is expensive and the network is weak, and to keep working when it is not available. This matters because data bundles, slow connections and power cuts are real limits for many learners, and a workflow that ignores them will not last.</p>

<h3>Spend data wisely</h3>
<ul>
<li><strong>Plan before you connect.</strong> Write your prompts in a notes app first, then paste them in one go over Wi-Fi or a good connection.</li>
<li><strong>Make one strong prompt, not ten weak ones.</strong> Give context and format at the start to avoid long back-and-forth.</li>
<li><strong>Ask for short answers.</strong> "Under 150 words" saves reading time and data.</li>
<li><strong>Avoid unnecessary uploads.</strong> Images, audio and video use far more data than text. Paste text where possible.</li>
<li><strong>Check data settings.</strong> Many phones let you restrict background data and show which apps use the most.</li>
</ul>

<h3>Save what works</h3>
<p>Copy useful answers into a notes app or document and name them clearly, such as "Economics - supply and demand summary". Later you can read them offline. Keep your prompt templates in the same place.</p>

<h3>Work offline when you can</h3>
<table>
<thead><tr><th>Task</th><th>Offline approach</th></tr></thead>
<tbody>
<tr><td>Studying</td><td>Print or save AI-made questions and flashcards, then self-test on paper without internet</td></tr>
<tr><td>Writing</td><td>Draft in your own words offline; edit with AI only when you have a connection</td></tr>
<tr><td>Planning</td><td>Keep the timetable as a screenshot or printed table</td></tr>
<tr><td>Translation and dictionaries</td><td>Some apps allow downloading language packs; check what your app offers before relying on it</td></tr>
</tbody>
</table>
<p>Some AI tools and apps can run in a limited form on a device without a connection, but availability, quality and storage needs differ and change, so test before depending on it.</p>

<h3>Power cuts and phones</h3>
<p>Carry a power bank if you can, lower screen brightness and close apps you are not using. Do key study during hours when power and signal are reliable, and use the unreliable times for paper tasks.</p>

<h3>Worked example: a batch session</h3>
<p>Judith, a student in Fort Portal, buys a bundle on Sundays. Before connecting, she writes five prompts in her notes: a summary, 10 practice questions, a flashcard set, a one-week plan and a list of weak points to ask her teacher. She pastes them in sequence, copies each answer into her notes and disconnects. Then she studies offline all week using her own saved material. Her total data use is small, and she spends the rest of the week learning, not scrolling.</p>

<h3>Case scenario</h3>
<p>Vincent, a trainee in Lira, relied on an AI assistant for every study question during the day. His bundle finished by Wednesday and his studying stopped. At the same time he had never saved any of the answers. He changed his approach: one planned session per week, saved answers in a notes app, and paper practice for the rest. His results improved, and he spent less. The lesson: constant online help is not required for learning, and saved, organised material is more useful than a chat history that disappears.</p>

<blockquote><p><strong>Quick check:</strong> Name two ways to use less data with AI tools.</p><p>Answer: Prepare prompts offline and ask for short answers (or paste text instead of uploading images), because images and long chats use more data.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why save AI answers in a notes app?</p><p>Answer: You can read and revise them offline later, and you do not need to pay data to ask again.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Check which of your apps use the most mobile data this month.</li>
<li>Write three prompts in a notes app, run them in one session and save the answers with clear names.</li>
<li>Print or write out 10 AI-made practice questions and answer them offline.</li>
<li>Note how much data the session used.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Plan prompts offline and send strong, short, complete requests.</li>
<li>Prefer text to images, audio and video when data is limited.</li>
<li>Save answers and templates for offline use.</li>
<li>Test offline features before depending on them.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What should you give an assistant to get a realistic timetable?
- Only the subject names
* Your fixed commitments, task lengths, deadlines and real limits such as travel and power cuts
- Nothing, because it can guess
- Only your wake-up time
= An assistant cannot know your life. Honest limits are what make a plan workable.

Q: Which is a SMART goal?
- Get better at English
* Practise English for 20 minutes a day for 60 days and score 70 percent on a practice test
- Try harder at school
- Be rich someday
= A SMART goal is specific, measurable, achievable, relevant and time-bound.

Q: When asking for meeting action items, which instruction prevents invented details?
- Make it sound professional
- Add reasonable guesses
* If an owner or deadline is not stated, write "not stated" and do not guess
- Ignore the notes
= Without a no-guessing rule the assistant may fill gaps with invented owners or dates.

Q: Dennis copied a sales formula from an assistant without testing and ordered too much stock. What should he have done?
- Trust it because it looked professional
* Tested it on a few rows whose answers he could calculate by hand
- Added more rows
- Deleted the spreadsheet
= Formulas can look correct and still be wrong. A small test with known answers catches errors early.

Q: Which are good ways to save data when using AI tools? (choose all that apply)
* Write your prompts offline first and paste them in one session
- Upload large videos for every question
* Ask for short answers and save useful ones for offline use
* Paste text instead of uploading images when possible
= Planning, short answers and saving results cut data use. Large uploads use much more data.

Q: Vincent used AI all day and his data finished by Wednesday, with nothing saved. What is the best change?
- Buy more data every day
- Stop studying
* Plan one session a week, save the answers and practise offline on paper
- Ask the same questions again each day
= Saved, organised material works offline and costs no more data, while a chat history may be lost.

Q: Why should you check a transcript of a voice recording?
- Transcripts are always perfect
* Names, numbers and local words are often wrong, especially with accents or noise
- It makes the audio louder
- It is required by law everywhere
= Accuracy varies by phone, app and language, so read and correct before using or sharing.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: AI for career growth',
            'summary' => 'Find your skills gap, build a learning roadmap, improve your CV, cover letter and LinkedIn profile, and practise interviews and portfolio ideas.',
            'lessons' => [
                [
                    'title'   => 'Skills gap analysis and learning roadmaps',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to compare your skills with a target job, find the gaps and build a realistic learning roadmap with the help of an assistant. This matters because career growth is easier when you know exactly what to learn next instead of collecting random certificates.</p>

<h3>Step 1: choose a target</h3>
<p>Pick one realistic role for the next 6 to 12 months, such as "junior data clerk", "customer support agent" or "assistant farm manager". Collect two or three real job adverts for that role from reliable places and note the skills they repeat.</p>

<h3>Step 2: list your skills honestly</h3>
<p>Write what you can do now, with proof. Include soft skills such as communication and punctuality, and tools you have used.</p>

<h3>Step 3: ask for a gap analysis</h3>
<pre><code>Act as a career coach. My target role is junior customer support agent in Kampala. Below are skills from two job adverts and a list of my current skills. Make a table with: Skill, Do I have it (yes, partly, no), and Priority (high, medium, low). Do not invent skills I did not list.

Adverts: [paste text]
My skills: [list]</code></pre>
<p>The assistant usually returns a table you can discuss. Review it: it may rank something as high priority only because it appears often, or miss a skill that matters in your local market.</p>

<h3>Step 4: build a roadmap</h3>
<p>Ask for a plan with small steps and free or low-cost resources, and ask it to name the type of resource instead of exact websites you cannot verify:</p>
<pre><code>Make a 12-week learning roadmap for the high-priority gaps. I can study 6 hours a week, have limited data, and prefer free resources. For each week give a goal, a practice task and a way to prove it, such as a small project. Suggest types of resources and I will find them myself.</code></pre>

<table>
<thead><tr><th>Roadmap part</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Weeks 1 to 3</td><td>Learn spreadsheet basics; build a simple customer list</td></tr>
<tr><td>Weeks 4 to 6</td><td>Practise professional email and phone scripts; record yourself</td></tr>
<tr><td>Weeks 7 to 9</td><td>Learn a help-desk tool basics; write 10 sample replies</td></tr>
<tr><td>Weeks 10 to 12</td><td>Apply to five jobs; update CV with proof of new skills</td></tr>
</tbody>
</table>

<h3>Worked example: a graduate's gap</h3>
<p>Mildred, a statistics graduate in Kampala, wants a data analyst job. Her gap analysis shows strong theory, partial spreadsheet skills and no experience with charts or reports. Her roadmap includes making a one-page report from public data each fortnight. After 12 weeks she has three small reports, each with a short explanation, which she can show employers. A certificate alone would not give her this proof.</p>

<h3>Case scenario</h3>
<p>Isaac, a graduate in Arusha, asked an assistant "what skills pay the most" and enrolled in an expensive course on a skill that the assistant listed as "in huge demand in East Africa". Months later he found no local adverts that asked for it. The assistant had generalised from global trends. Isaac now checks real local job adverts and talks to people in the field before choosing a course. The lesson: use AI to organise your thinking, but base career decisions on real local evidence and people who work in the field.</p>

<blockquote><p><strong>Quick check:</strong> Why paste real job adverts into a gap analysis prompt?</p><p>Answer: They show what employers actually ask for, so the analysis is based on evidence rather than guesses.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What proof makes a roadmap step convincing to employers?</p><p>Answer: A small finished project or sample work, because it shows the skill rather than only claiming it.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose one target role and collect two real job adverts for it.</li>
<li>List your current skills with proof.</li>
<li>Ask for a gap table and check it against the adverts.</li>
<li>Ask for a 12-week roadmap that fits your hours and data, and write the first week in your diary.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Start from a specific target role and real adverts.</li>
<li>Be honest about your skills and forbid invented ones.</li>
<li>Build a roadmap with small steps and proof projects.</li>
<li>Confirm local demand with real adverts and people in the field.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'CV, cover letter and LinkedIn with AI help',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to improve your CV, cover letter and LinkedIn profile with an assistant without inventing anything or losing your own voice. This matters because recruiters often read each application for only a short time, and clear, honest, tailored documents win interviews.</p>

<h3>Start with your facts</h3>
<p>Prepare a plain list of true facts: jobs, dates, tasks, results, education and skills. This is your source material. The assistant improves wording; it must not add facts.</p>

<h3>Improve CV bullet points</h3>
<p>A strong bullet starts with a verb and shows a result. Compare:</p>
<table>
<thead><tr><th>Weak</th><th>Stronger</th></tr></thead>
<tbody>
<tr><td>Responsible for sales</td><td>Sold phones to about 30 customers a day and kept daily records without errors</td></tr>
<tr><td>Helped in the office</td><td>Filed and updated 200 student records in a new spreadsheet in two weeks</td></tr>
</tbody>
</table>
<pre><code>Rewrite the CV bullet points below to start with strong verbs and be clear and brief. Use only the facts I gave. Do not add numbers, tools or achievements. If a number would help, tell me what to measure instead of inventing it.

[paste bullets]</code></pre>

<h3>Tailor to each job</h3>
<p>Paste the job advert and your CV and ask: "Which of my real experiences match this advert? Which requirements do I not meet? Suggest which parts of my CV to move up. Do not add anything untrue." Then reorder and adjust yourself.</p>

<h3>Cover letter</h3>
<p>A good letter is short, specific and honest: why this role, why this organisation, two pieces of proof, and a polite close. Write your own rough paragraph for each, then ask the assistant to tighten them. Avoid letters that could be sent to any company. Names, places and details must be right, so check the employer's name and the job title at the end.</p>

<h3>LinkedIn profile</h3>
<p>Ask for options for a headline and "About" section from your facts: "Give me three headlines under 120 characters that say what I do and who I help. Use only the facts below." Pick one and edit it. Do not claim certificates, awards or experience you do not hold, because they are easy to check.</p>

<h3>Worked example: Mark's CV</h3>
<p>Mark, a shop assistant in Nairobi applying to be a sales officer, pastes his bullets and the advert. The assistant produces cleaner bullets and highlights that the advert asks for "target tracking", which Mark never wrote about. Mark remembers that he kept a monthly sales tally for his manager and adds that true detail himself. His CV now matches the advert honestly and still sounds like him.</p>

<h3>Case scenario</h3>
<p>Caroline, applying for a bank internship, asked an assistant to "make my CV stronger". The result listed "advanced Excel" and "led a team of 10". She had basic Excel and had only helped a group of three. At the interview she was asked to do a pivot table on a laptop and could not. The interviewer thanked her but ended the interview early. She rewrote the CV truthfully, took a short Excel practice course and later got another internship. The lesson: an overstated CV is tested in the interview, and it is far better to show honest skills with a plan to grow.</p>

<blockquote><p><strong>Quick check:</strong> What rule should every CV prompt include?</p><p>Answer: "Use only the facts I gave; do not add anything," because invented claims are discovered in interviews.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why must you check names and job titles in an AI-written cover letter?</p><p>Answer: Assistants can leave wrong or placeholder names, and a mistake looks careless.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a plain list of your true facts: jobs, tasks, results, education and skills.</li>
<li>Ask for stronger CV bullets using only those facts and note what numbers you could measure.</li>
<li>Paste a real job advert and ask which of your true experiences match it.</li>
<li>Write a three-sentence LinkedIn headline and About draft, then edit it into your own words.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Begin with a list of true facts; AI polishes, it does not invent.</li>
<li>Use strong verbs, clear results and tailoring to each advert.</li>
<li>Keep cover letters specific and check every name and detail.</li>
<li>Never claim skills or awards you do not have.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Interview practice and portfolio ideas',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to practise interviews with an assistant as a role-play partner and to plan small portfolio projects that prove your skills. This matters because confidence and evidence are what employers and clients remember, and both improve with practice that costs almost nothing.</p>

<h3>Interview role-play</h3>
<p>An assistant can play the interviewer, ask follow-up questions and give feedback on your answers. Type your answers, or use voice features if your tool has them, and also practise aloud with a friend.</p>
<pre><code>Act as a hiring manager interviewing me for a junior sales officer job at a distribution company in Kampala. Ask one question at a time and wait for my answer. After each answer give me one strength, one improvement and a better example sentence. Ask 8 questions, mixing background, teamwork and a difficult situation.</code></pre>

<h3>The STAR method</h3>
<p>For questions like "Tell me about a time you solved a problem", use <strong>STAR</strong>: Situation, Task, Action, Result. Keep each part to one or two sentences.</p>
<table>
<thead><tr><th>Part</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Situation</td><td>Our shop ran out of a popular item before the weekend</td></tr>
<tr><td>Task</td><td>I had to avoid losing regular customers</td></tr>
<tr><td>Action</td><td>I called two suppliers, found the item and told customers the day it would arrive</td></tr>
<tr><td>Result</td><td>Most customers waited and sales that week matched the previous week</td></tr>
</tbody>
</table>
<p>Ask the assistant to help you structure a real story in STAR form, but the story must be one that happened. Practise it until it sounds natural, not memorised.</p>

<h3>Prepare questions to ask</h3>
<p>Ask the assistant for questions you could ask the employer, such as about training, how success is measured and the team. Choose the ones that genuinely interest you.</p>

<h3>Portfolio ideas</h3>
<p>A <strong>portfolio</strong> is a small collection of your work that proves your skills. An assistant can suggest project ideas for your field, and you do the work.</p>
<pre><code>I am learning customer support and want a portfolio. Suggest 5 small projects I can finish in a week each with free tools. For each, say what skill it proves and what the finished result looks like.</code></pre>
<p>Examples: a set of 10 model replies to customer complaints, a one-page report from public data, a short poster you designed, a simple budget template, or a clean spreadsheet with charts. Keep a short description for each project: the problem, what you did and what you learned.</p>

<h3>Worked example: Joan's practice</h3>
<p>Joan, a graduate in Mbarara, does a 10-minute interview role-play every evening for a week. The assistant points out that she keeps saying "we" and never describes her own actions. She rewrites her stories with "I did" where true. In the real interview she gives a clear STAR answer about organising a fundraiser, and the panel asks her to explain more, which is a good sign.</p>

<h3>Case scenario</h3>
<p>Emmanuel, preparing for a bank interview in Nairobi, asked an assistant for "the best answers" and memorised them word for word. In the interview he gave an answer about leading a team of fifteen, which was not true, and the panel asked detailed follow-up questions. He could not answer and became nervous. He later practised with his own true STAR stories and asked the assistant only to challenge him with follow-ups. The lesson: borrowed answers collapse under follow-up questions, but real stories you know well hold up.</p>

<blockquote><p><strong>Quick check:</strong> What does STAR stand for?</p><p>Answer: Situation, Task, Action, Result, which together structure a clear story about something you did.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why are memorised AI answers risky?</p><p>Answer: They may not be true for you, and follow-up questions quickly show it.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick a job you want and run a 5-question interview role-play with an assistant.</li>
<li>Write one true STAR story and practise it aloud twice.</li>
<li>Ask for five portfolio project ideas for your field and choose one.</li>
<li>Finish a first draft of that project within a week and write three lines describing it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use role-play to practise and get feedback, one question at a time.</li>
<li>Use STAR with true stories you can explain in detail.</li>
<li>Do not memorise answers that are not yours.</li>
<li>A small finished portfolio is proof that words cannot give.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the best first step in a skills gap analysis?
- Buy an expensive course
* Choose a specific target role and collect real job adverts for it
- Ask the assistant which job you should want
- Copy a friend's CV
= Real adverts show what employers ask for. A clear target makes the gap analysis useful.

Q: Which CV prompt is safest?
- Make my CV impressive
- Add skills to make me look stronger
* Rewrite my bullets with strong verbs using only the facts I gave, and tell me what to measure instead of inventing numbers
- Write a CV for a perfect candidate
= The safe prompt forbids invented facts and uses the assistant to polish true content.

Q: Which parts make up the STAR method? (choose all that apply)
* Situation
* Task
- Salary
* Result
* Action
= STAR stands for Situation, Task, Action and Result. Salary is not part of it.

Q: Caroline's CV listed "advanced Excel" which she did not have, and she failed a practical test at interview. What is the lesson?
- Tests are unfair
* An overstated CV is tested in the interview, so show honest skills and a plan to grow
- Add more skills to the next CV
- Avoid interviews
= Claims are checked. Honest skills with evidence build trust, and skills can be learned later.

Q: Isaac chose an expensive course because an assistant said the skill was in huge demand locally, and he found no local adverts. What should he do next time?
- Trust the assistant more
* Check real local job adverts and talk to people working in the field
- Pick the most expensive course
- Ask the same question again
= Assistants often generalise from global trends. Local evidence and people in the field give a better picture.

Q: Emmanuel memorised AI answers and was caught out by follow-up questions. What works better?
- Memorise more answers
- Give shorter answers
* Prepare your own true STAR stories and use the assistant to ask difficult follow-ups
- Ask the panel for the questions in advance
= True stories you know well survive follow-up questions. Borrowed answers do not.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 6
        [
            'title'   => 'Module 6: Side income, clear thinking and your personal plan',
            'summary' => 'Use AI for a small business or side income, keep your own skills and judgement strong, and build a personal AI workflow with a 30-day plan.',
            'lessons' => [
                [
                    'title'   => 'AI for a small business or side income',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use an assistant to reply to customers, write product descriptions, plan social media content and think through simple prices and budgets for a small business or side income. This matters because small business owners often work alone, and saving an hour a day on writing and planning can be the difference between growing and standing still.</p>

<h3>Customer replies</h3>
<p>Give the assistant the situation, your tone and your rules. Never include customer phone numbers or private details.</p>
<pre><code>Write a polite WhatsApp reply in simple English and Luganda-friendly tone to a customer who says her cake arrived late. Our policy: apologise, offer 10 percent off her next order, no refunds. Under 60 words.</code></pre>
<p>Read the reply and make sure it matches your policy. Assistants sometimes promise refunds or discounts you never offered, and a promise made to a customer is yours to keep.</p>

<h3>Product descriptions</h3>
<pre><code>Write a 50-word description for my handmade sisal baskets for an Instagram post. Facts: made in Kabale, 3 sizes, take 4 days to make, prices from 25,000 shillings. Do not add materials, claims or awards that I did not give.</code></pre>

<h3>Social media content calendar</h3>
<p>Ask for a four-week plan with your real products, events and capacity:</p>
<pre><code>Make a 2-week content calendar for my hair salon in Gulu. I can post 4 times a week. Mix: before and after photos, a hair care tip, a customer story with permission, and a promotion. Give each post an idea and a caption of under 40 words. Show it as a table.</code></pre>
<p>Use your own photos and real customers (with their consent). Do not post AI images pretending to be real work or real customers.</p>

<h3>Simple price and budget thinking</h3>
<p>An assistant can explain ideas such as cost, margin and break-even in plain words and help structure a budget table. But <strong>numbers must be verified.</strong> Do your own arithmetic or use a spreadsheet, and use your real receipts.</p>
<table>
<thead><tr><th>Question to ask</th><th>Why it helps</th></tr></thead>
<tbody>
<tr><td>What costs should a baker list besides ingredients?</td><td>Reminds you of gas, packaging, transport and your own time</td></tr>
<tr><td>How do I find my break-even number of sales?</td><td>Explains the method; you insert your real costs</td></tr>
<tr><td>What could go wrong with this price?</td><td>Prompts you to think about competition and customers</td></tr>
</tbody>
</table>

<h3>Worked example: a juice seller</h3>
<p>Fatuma sells fresh juice near a university in Kampala. She gives the assistant her costs per bottle, her price and her daily sales and asks it to explain her profit. The assistant gives a clear explanation, but its total is wrong. She checks in her spreadsheet and finds the error. The explanation was valuable; the arithmetic needed her check.</p>

<h3>Case scenario</h3>
<p>Sylvia, who sells second-hand clothes online in Nairobi, asked an assistant to write product descriptions. It described a jacket as "100 percent genuine leather" because the photo looked like leather. It was imitation. A buyer complained publicly and Sylvia had to refund and apologise. After this she gave the assistant only facts she had checked and read every claim before posting. The lesson: a false product claim is your responsibility, so give the assistant true facts and remove every claim you cannot prove.</p>

<blockquote><p><strong>Quick check:</strong> Who is responsible for a promise an assistant writes in a customer reply that you send?</p><p>Answer: You are, because the message goes out in your name; check it against your real policy.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What should you do with prices and totals an assistant gives you?</p><p>Answer: Recalculate them yourself with real figures, because arithmetic errors are common.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write the rules for your business or idea: tone, discounts, delivery, refunds.</li>
<li>Ask for two customer replies and check them against your rules.</li>
<li>Ask for a 2-week content calendar using products you really have.</li>
<li>List your real costs and calculate profit per item yourself.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Give the assistant true facts and your rules; never private customer data.</li>
<li>Remove claims you cannot prove from descriptions and ads.</li>
<li>Use AI for ideas and structure; verify all numbers yourself.</li>
<li>You remain responsible for what you send and sell.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Critical thinking: staying in charge of your own skills',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to recognise when AI is helping you learn and when it is replacing your thinking, and to use simple habits that keep your skills growing. This matters because skills you never practise fade, and employers and teachers still need people who can think, write and decide without a machine.</p>

<h3>Helping versus replacing</h3>
<p>Using a calculator helped most people do harder maths, but someone who never learns basic arithmetic cannot notice a wrong answer. AI is similar. If it does all your thinking, you lose the very ability you need to check it. A simple question to ask yourself: <em>"Did I learn something, or did I just get something?"</em></p>
<table>
<thead><tr><th>Helps you grow</th><th>Replaces your thinking</th></tr></thead>
<tbody>
<tr><td>Explaining a topic, then you explain it back</td><td>Copying the explanation into your notes unread</td></tr>
<tr><td>Feedback on your draft</td><td>Having the whole essay written</td></tr>
<tr><td>Hints for a problem</td><td>Full solutions to every problem</td></tr>
<tr><td>Practice questions you answer first</td><td>Looking at answers immediately</td></tr>
</tbody>
</table>

<h3>Habits for clear thinking</h3>
<ol>
<li><strong>Think first.</strong> Spend five minutes writing your own idea or attempt before opening the assistant.</li>
<li><strong>Question the answer.</strong> Ask "What is the strongest argument against this?" and "What might be missing?"</li>
<li><strong>Check the source.</strong> Where could I confirm this?</li>
<li><strong>Notice bias.</strong> Does the answer assume a European or American setting? Are some groups left out?</li>
<li><strong>Keep no-AI practice days.</strong> Write, calculate and solve problems by hand regularly, like exercise for the mind.</li>
<li><strong>Own the decision.</strong> The assistant may suggest; you decide, and you carry the results.</li>
</ol>

<h3>Beware flattery and overconfidence</h3>
<p>Assistants often agree with you or praise your work, and a pleasant answer can feel like proof. Ask for criticism directly: "Act as a strict marker. List the weaknesses of my answer. Do not flatter me." Also be careful with advice on health, law and money. An assistant can help you prepare questions, but a qualified person should give advice that affects your health, rights or savings.</p>

<h3>Worked example: a decision</h3>
<p>Samuel, a graduate in Jinja, is deciding between a job in town and starting a poultry business. He first writes his own pros and cons. Then he asks the assistant: "Here are my options and my reasons. What have I not considered? Ask me five hard questions." The assistant raises questions about start-up money, risk of disease and his family support. Samuel answers them on paper and talks to a farmer he trusts. The assistant widened his thinking, but the decision stayed his.</p>

<h3>Case scenario</h3>
<p>Lillian, a second-year student in Kampala, used an assistant to write every assignment for a semester and passed. In the final practical exam, with no access to AI, she could not write a simple report and failed. She had also not noticed that the assistant had given her wrong definitions in two earlier assignments. She started again with a new rule: she writes the first draft herself and uses AI only for feedback. Her marks recovered and her confidence returned. The lesson: if AI always does the work, you will not have the skill when it matters, and you will not notice the errors.</p>

<blockquote><p><strong>Quick check:</strong> What question tells you if AI is helping you learn?</p><p>Answer: "Did I learn something, or did I just get something?" because learning needs your own thinking.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why ask an assistant to criticise instead of just review your work?</p><p>Answer: Assistants often flatter, so asking directly for weaknesses gives more useful feedback.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a task you normally give to AI and do the first draft yourself.</li>
<li>Then ask the assistant to act as a strict marker and list weaknesses.</li>
<li>Pick one day a week as a no-AI practice day and plan what you will do by hand.</li>
<li>Write one decision you face and ask the assistant what you have not considered.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>AI should help you learn, not replace your thinking.</li>
<li>Think first, question the answer and check sources.</li>
<li>Ask for honest criticism because assistants tend to flatter.</li>
<li>Keep your own skills and decisions in your hands.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Your personal AI workflow and 30-day plan',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to design a simple personal AI workflow with clear rules and follow a 30-day plan to build the habit safely. This matters because good results come from steady routines and clear boundaries, not from trying every new tool.</p>

<h3>What is a personal AI workflow?</h3>
<p>A <strong>workflow</strong> is a repeatable set of steps for a task. A personal AI workflow answers: which tasks will I use AI for, with which prompt, what will I check, and what will I never do? Keep it small and written down.</p>

<h3>Build it in four parts</h3>
<ol>
<li><strong>Tasks:</strong> choose three to five where AI saves time or improves learning, such as study questions, email drafts, weekly planning and interview practice.</li>
<li><strong>Prompts:</strong> save one template for each task in a notes app.</li>
<li><strong>Checks:</strong> decide what you will verify each time, such as facts, numbers, names and tone.</li>
<li><strong>Rules:</strong> write your safety and honesty limits.</li>
</ol>

<h3>Example personal rules</h3>
<pre><code>1. I never paste passwords, PINs, ID numbers or confidential school or work data.
2. I attempt first, then ask for feedback.
3. I verify every number, date, name and quotation in an official source.
4. I follow school and work rules and declare AI use when required.
5. I read everything aloud and change what does not sound like me.
6. One no-AI practice day each week.</code></pre>

<h3>Your 30-day plan</h3>
<table>
<thead><tr><th>Days</th><th>Focus</th><th>Action</th></tr></thead>
<tbody>
<tr><td>1 to 5</td><td>Set up</td><td>Choose your tool, check privacy settings, write your rules and three prompt templates</td></tr>
<tr><td>6 to 12</td><td>Study</td><td>Use summaries, practice questions and spaced revision for one subject; track marks</td></tr>
<tr><td>13 to 19</td><td>Writing and planning</td><td>Improve one real email or application; plan each week with a timetable</td></tr>
<tr><td>20 to 26</td><td>Career</td><td>Run a gap analysis, update your CV with true facts, do three interview role-plays</td></tr>
<tr><td>27 to 30</td><td>Review</td><td>Count time saved, errors found and skills gained; keep what works, drop what does not</td></tr>
</tbody>
</table>

<h3>Review honestly</h3>
<p>At the end, ask yourself: Did I save time? Did I learn more? How many errors did AI make that I caught? Did I ever break my rules? Adjust the workflow, and re-check the tools you use now and then, because features and prices change.</p>

<h3>Worked example: Winnie's workflow</h3>
<p>Winnie, a final-year student in Mbarara, picks four tasks: practice questions, weekly planning, CV improvement and interview practice. She saves four prompts and writes five rules. In week three she notes that her marks on practice quizzes rose from 55 to 68 percent, and that she caught two wrong facts in AI answers by checking her textbook. She decides to keep the routine and add a weekly no-AI writing session. Her workflow is small, but it is hers and she follows it.</p>

<h3>Case scenario</h3>
<p>Peter, a young entrepreneur in Entebbe, signed up for six AI tools in one weekend and used none of them regularly. He spent his data on trying features, and his actual work did not improve. He also pasted a customer list into one tool during testing, which he later regretted. He decided to keep one tool, three tasks and written rules. After a month he saved about four hours a week and no longer worried about privacy. The lesson: a small, steady workflow with rules beats many tools used without a plan.</p>

<blockquote><p><strong>Quick check:</strong> What four parts does a personal AI workflow include?</p><p>Answer: Tasks, prompts, checks and rules, because together they make AI use repeatable and safe.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why review your workflow after 30 days?</p><p>Answer: To see what saved time, what errors occurred and what to change, because tools and needs change.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose three tasks for AI and write one prompt template for each.</li>
<li>Write five personal rules, including your privacy and honesty limits.</li>
<li>Copy the 30-day plan into your diary and set a start date.</li>
<li>On day 30, write one page: what worked, what failed and what you will change.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A small written workflow beats many tools used randomly.</li>
<li>Include tasks, prompts, checks and personal rules.</li>
<li>Follow a 30-day plan and review it honestly.</li>
<li>Keep checking tools, rules and your own skills as things change.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which example shows AI helping you grow rather than replacing your thinking?
- Pasting a task and submitting the answer
* Attempting a problem first, then asking for feedback and hints
- Having every assignment written for you
- Copying definitions without reading
= Doing the thinking first and using AI for feedback builds skill.

Q: Why ask an assistant to "act as a strict marker and not flatter me"?
- Because flattery uses more data
* Assistants often agree and praise, so you must ask for honest weaknesses
- Because assistants cannot read work
- To make the answer longer
= Pleasant feedback can feel like proof but may hide real weaknesses.

Q: Which belong in a personal AI workflow? (choose all that apply)
* A list of tasks you will use AI for
* Saved prompt templates
- Pasting passwords to save time
* Written rules and checks
= A workflow needs tasks, prompts, checks and rules. Sharing passwords is never safe.

Q: Sylvia's AI-written description called an imitation jacket "100 percent genuine leather", and a buyer complained. What should she do next time?
- Trust the assistant to judge photos
* Give only verified facts and remove any claim she cannot prove
- Add more strong claims
- Stop selling online
= The seller is responsible for product claims. Descriptions must use facts she has checked.

Q: Lillian had AI write every assignment and then failed a practical exam. What is the most useful change?
- Use a different assistant
* Write the first draft herself and use AI for feedback
- Stop studying
- Copy a classmate
= Skills come from practice. AI feedback after her own attempt keeps her learning.

Q: Peter signed up for six tools and used none regularly. What approach works better?
* One tool, three tasks and written rules, reviewed after a month
- Ten more tools
- Never reviewing
- Using whichever tool is newest each day
= A small, steady workflow with rules gives better results than many tools without a plan.

Q: Fatuma's assistant explained her juice profit clearly but the total was wrong. What should she do?
- Use the total because the explanation was good
* Recalculate with her real figures in a spreadsheet
- Ask the assistant to round the total
- Ignore her costs
= Assistants can explain well and still make arithmetic errors, so verify numbers.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What is an AI hallucination?
- A slow response from the tool
* A confident answer that is false or invented
- A picture that the tool refuses to make
- A warning about data use
= Hallucinations are plausible-sounding but untrue outputs, so important facts must be checked.

Q: Which prompt is strongest for studying?
- Help me with biology
* I am in Senior Three. Explain photosynthesis in 120 words with a farming example, then ask me two questions
- Biology notes
- Explain everything
= Context, format and a follow-up task give a focused, useful answer.

Q: Which should you never type into an AI assistant? (choose all that apply)
* Your mobile money PIN
- A request to explain compound interest
* A school's confidential student records
* Your national ID number
= PINs, ID numbers and confidential records are private or protected. A general explanation request is safe.

Q: Brian used five references from an assistant and three did not exist. What is the correct rule?
- Keep the ones that look real
* Find and open every source yourself before citing it
- Ask the assistant to confirm they exist
- Remove the authors only
= An assistant can invent sources, so only sources you have found and read count.

Q: A caller with your cousin's voice demands urgent money. What is the best response?
- Send half the money
* Hang up and call your cousin on a number you already know
- Ask the caller for a selfie
- Give your PIN so he can send it
= Voices can be cloned. Verify through a trusted channel and never share your PIN.

Q: Moses pasted his employer's salary sheet into a free assistant. What was the main problem?
- The sheet was too long
* Confidential data belonging to the employer was shared without permission
- Spreadsheets cannot be summarised
- He used the wrong font
= Confidential employer data must not be shared with tools. Remove identifying details or use another method.

Q: When asking an assistant to summarise your notes, which instruction helps most?
- Make it dramatic
* Do not add facts that are not in my notes
- Use at least 500 words
- Write it as a poem
= Staying within your notes prevents added details that may be wrong or off-syllabus.

Q: Joseph copied a statistics formula from an assistant and lost marks. What should he do in future?
- Avoid statistics
* Confirm formulas in his lecture slides or textbook and use the assistant to explain meaning
- Copy a different formula from the assistant
- Ask the assistant if it is sure
= Official course material is the reliable reference for formulas and definitions.

Q: Which gives the best learning from practice questions?
- Reading the answers first
* Answering from memory before seeing solutions, then having your answers marked
- Skipping hard questions
- Only doing multiple-choice
= Retrieving answers yourself strengthens memory. Looking first removes the benefit.

Q: Mercy revised only "likely exam questions" from an assistant and the paper tested other topics. What should guide revision?
- The assistant's guesses alone
* The official syllabus and past papers
- Friends' predictions
- Only favourite topics
= The syllabus and past papers show what is examined. The assistant can only guess.

Q: Which is a good use of AI for a language learner?
- Copying translations into an assignment without checking
* Role-play conversations with brief corrections, then confirming doubtful points with a teacher or dictionary
- Never speaking aloud
- Trusting translations for any language equally
= Practice with feedback helps, but quality varies by language and a person should confirm important meaning.

Q: Which request best protects your own voice?
- Write my entire statement for me
* Edit my draft to match my style, keep my facts and add nothing new
- Make it sound like a famous writer
- Replace every word
= Editing your own draft keeps ideas and voice yours.

Q: Doreen submitted a generic AI statement with an invented village story. What was the main weakness?
- It was too short
* It was dishonest and lacked specific true details
- It had no title
- It used English
= Fabricated stories are dishonest, and generic text is not memorable. Specific true details persuade.

Q: Your teacher has not said whether AI may be used for an assignment. What should you do?
- Assume it is allowed
* Ask your teacher, preferably in writing
- Ask the assistant to decide
- Do not tell anyone you used it
= Rules differ and change. Written permission protects you.

Q: Which are examples of cheating where AI use is forbidden? (choose all that apply)
* Submitting AI-written work as your own
* Using AI during a closed-book test
- Asking for a practice quiz when practice is allowed
* Citing references that the assistant invented
= Passing off AI work, breaking test rules and using invented references are dishonest.

Q: Hassan used an assistant's unverified statistic in a funding proposal and lost credibility. What should he have used?
- A rounder number
* An official statistics source cited with its year
- The same figure with more decimals
- A friend's guess
= Official sources can be checked. A precise number from a chat is not evidence.

Q: Which step belongs in a verification routine?
* Check numbers, dates and names against official or primary sources and note the date
- Ask the assistant "Are you sure?" and accept yes
- Trust the first source you see
- Skip verification when the answer sounds confident
= Verification needs independent reliable sources. Confidence is not evidence.

Q: Kelvin's weekly plan failed because he left out his late riding hours. What does this show?
- Plans never work
* An assistant can only plan around the real limits you tell it
- Assistants cannot make tables
- Study blocks should be longer
= The plan was based on incomplete information. Honest limits produce workable plans.

Q: Which is a SMART goal?
- Be better at business
* Test one product with 10 customers by 30 November and record their feedback
- Make a lot of money
- Work hard
= SMART goals are specific, measurable, achievable, relevant and time-bound.

Q: When asking for meeting action items, what prevents invented owners?
- Asking for a confident tone
* Telling the assistant to write "not stated" for anything missing and not to guess
- Asking for a longer summary
- Removing all names
= A no-guessing rule stops the assistant filling gaps with invented details.

Q: Dennis used an untested spreadsheet formula and over-ordered stock. What would have caught the error?
- Using bigger numbers
* Testing the formula on a few rows with answers he could calculate by hand
- Adding more columns
- Asking for a nicer layout
= Small tests with known answers reveal wrong formulas quickly.

Q: Which habits save data when using AI tools? (choose all that apply)
* Prepare prompts offline and send one clear request
* Ask for short answers and save the useful ones
- Upload large videos for simple questions
- Re-ask the same question every day
= Planning, short answers and saving results cut data use. Large uploads and repeats waste it.

Q: In a skills gap analysis, why include real job adverts?
- They make the prompt longer
* They show what employers actually ask for, so the analysis uses evidence
- They guarantee a job
- They replace a CV
= Real adverts ground the analysis in actual demand.

Q: Mark's assistant noticed a job advert asked for "target tracking". What should Mark do?
- Invent a target tracking achievement
* Add it only if he truly did it, such as a real monthly sales tally
- Ignore all adverts
- Ask the assistant to add it anyway
= CV content must be true. The advert can remind him of real experience he forgot.

Q: Emmanuel memorised AI interview answers and was caught out by follow-ups. What is a better method?
- Memorise more answers
* Practise true STAR stories and use the assistant to ask follow-up questions
- Avoid specific examples
- Give very short answers
= True stories you know well survive detailed follow-up questions.

Q: Sylvia's product description claimed "genuine leather" for an imitation jacket. Who is responsible?
- The assistant only
* The seller who published it
- The buyer
- Nobody
= The business owner is responsible for claims made in their name, so they must verify facts.

Q: Which question best tests whether AI is helping you learn?
- Did I finish quickly?
* Did I learn something, or did I just get something?
- Did the assistant sound confident?
- Did I use many tools?
= Learning requires your own thinking. Speed and confidence do not show understanding.

Q: A personal AI workflow should include which of these? (choose all that apply)
* Chosen tasks and saved prompts
* Checks for facts and numbers
- Permission to paste any private data
* Written safety and honesty rules
= A workflow combines tasks, prompts, checks and rules. Sharing private data is never part of a safe workflow.

Q: After a 30-day trial, you notice you caught three AI errors by checking a textbook. What is the best conclusion?
- Stop checking because the errors were small
* Keep verifying and adjust the workflow to include checks every time
- Stop using AI completely without thinking
- Use more tools instead
= Catching errors proves checking is valuable and belongs in the routine.
QUIZ,
];
