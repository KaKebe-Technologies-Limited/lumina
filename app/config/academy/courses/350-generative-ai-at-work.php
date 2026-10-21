<?php
return [
    'slug'        => 'generative-ai-at-work',
    'title'       => 'Generative AI at Work: Prompting, Safety and Productivity',
    'category'    => 'ai',
    'level'       => 'intermediate',
    'icon'        => 'star',
    'sort'        => 350,
    'summary'     => 'Use generative AI tools well and safely at work or school: write strong prompts, draft, summarise and analyse, check facts, protect private data, follow policy and build repeatable workflows.',
    'description' => '<p>Generative AI tools can write, summarise, translate and explain in seconds. Used well, they save hours on routine work. Used carelessly, they invent facts, leak private information and produce work that is not really yours. The people who benefit most are those who know how the tools work, how to ask clearly, and how to check the answer.</p><p>This intermediate course builds practical habits for offices, schools and small businesses. You will learn how these tools work in plain words, a reliable pattern for writing prompts, how to iterate on results, and how to use AI for drafting, summarising, analysing and translating. Then you will learn the responsible side: checking facts, spotting bias, protecting confidential data, respecting copyright, being open about AI use and following school or workplace rules. The course ends with building simple repeatable workflows.</p><p>The course is about skills that stay useful as tools change, so it does not depend on any single product.</p>',
    'outcomes'    => [
        'Explain in plain words how a generative AI tool produces its answers and why it can be wrong',
        'Choose tasks that suit AI and tasks that should stay with a person',
        'Write clear prompts using role, context, task, format and examples',
        'Improve a weak first answer through follow-up instructions and step-by-step requests',
        'Use AI to draft, summarise, analyse information and translate for office and small-business work',
        'Check AI output for errors, made-up facts and bias before using it',
        'Protect personal and confidential information when using AI tools',
        'Follow copyright, honesty and disclosure rules, and school or workplace AI policies',
        'Build a simple, repeatable AI workflow with a personal prompt library',
    ],
    'requirements' => ['Completed Practical AI Tools: Using AI Assistants Wisely (ai-tools-literacy), or equivalent experience with an AI chat assistant', 'A computer or smartphone with internet access and access to at least one free AI assistant of your choice', 'Comfortable reading and writing in English at work level'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'UNESCO: Artificial intelligence in education', 'url' => 'https://www.unesco.org/en/digital-education/artificial-intelligence'],
        ['title' => 'NIST AI Risk Management Framework', 'url' => 'https://www.nist.gov/itl/ai-risk-management-framework'],
        ['title' => 'Elements of AI (free online course)', 'url' => 'https://www.elementsofai.com/'],
        ['title' => 'Uganda Data Protection and Privacy Act, information from the Personal Data Protection Office', 'url' => 'https://pdpo.go.ug/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: How generative AI works',
            'summary' => 'Understand what these tools do, why they sometimes make things up, and which jobs suit them.',
            'lessons' => [
                [
                    'title'   => 'What generative AI is, in plain words',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what a generative AI tool is and how it produces text, in words a colleague would understand. This matters because people who understand the basic idea use the tools more wisely: they know when to trust them and when to doubt them.</p>

<h3>From recognising to creating</h3>
<p>Many older AI systems <em>recognise</em> things: they decide whether an email is spam, whether a photo shows a face, or whether a bank transaction looks suspicious. <strong>Generative AI</strong> creates something new: text, images, audio, computer code or video. When you type a request and get a written answer, you are using a type of generative AI called a <strong>large language model</strong>, or LLM.</p>

<h3>How a language model produces text</h3>
<p>Here is the idea without the mathematics.</p>
<ol>
<li><strong>Training.</strong> The model is shown an enormous amount of written text, such as books, articles, websites and code. During training it practises one task over and over: given the words so far, predict what word piece comes next.</li>
<li><strong>Patterns, not a library.</strong> From this practice it learns patterns about language, facts that appear often, styles and how ideas connect. It does not keep a neat copy of each document like a library. It keeps numbers that capture patterns.</li>
<li><strong>Generating.</strong> When you write a prompt, the model looks at your words and predicts the most likely next piece of text, adds it, and repeats until the answer is complete. A small amount of controlled randomness is included, so the same question can produce different wording each time.</li>
</ol>
<p>Think of someone who has read a huge number of books and is very good at continuing any sentence in a convincing way. They sound confident, but they are not looking things up. That is the key to everything that follows.</p>

<h3>Useful words</h3>
<table>
<thead><tr><th>Word</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>Prompt</td><td>The instruction or question you give the tool</td></tr>
<tr><td>Token</td><td>A small piece of text (part of a word) that the model reads and writes</td></tr>
<tr><td>Context window</td><td>How much text the model can consider at one time, including your messages and its replies</td></tr>
<tr><td>Training data</td><td>The text and other material used to teach the model</td></tr>
<tr><td>Hallucination</td><td>A confident answer that is wrong or invented</td></tr>
<tr><td>Chatbot or assistant</td><td>A friendly interface that lets you talk to the model</td></tr>
</tbody>
</table>

<h3>What follows from how it works</h3>
<ul>
<li>It is very good with language: rewriting, summarising, explaining and drafting.</li>
<li>It predicts plausible text, so it can produce a smooth, wrong answer.</li>
<li>It does not truly understand your situation unless you tell it. It has no knowledge of your office, your customers or yesterday's events unless you provide them or the tool has a search feature.</li>
<li>Its knowledge has a cut-off date from when the training data was collected, so recent events may be missing.</li>
<li>It may remember the conversation only within the context window of that chat.</li>
</ul>

<h3>Tools differ</h3>
<p>Different tools use different models and settings, and some can search the web, read your files or run calculations. Features change often. Learn what your own tool can and cannot do from its help pages instead of assuming.</p>

<h3>Try it yourself</h3>
<ol>
<li>Open a free AI assistant.</li>
<li>Ask: "Complete this sentence in three different ways: The market was busy because..." and read the three endings.</li>
<li>Ask the same question again in a new chat. Notice that the answers differ.</li>
<li>Write one sentence in your own words on why the answers differ.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Generative AI creates new content; an LLM predicts the next piece of text.</li>
<li>It learns patterns from training data and does not look facts up like a database.</li>
<li>Answers can differ each time and can be confident but wrong.</li>
<li>It knows only its training and what you give it in the conversation.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Strengths, limits and hallucinations',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to list what generative AI does well, recognise its typical failures, and spot a hallucination. This matters because the most dangerous AI mistake is the one that looks perfect, such as an invented statistic placed in a report that a director then repeats.</p>

<h3>What it does well</h3>
<ul>
<li><strong>Language tasks:</strong> rewriting for a different audience, fixing grammar, changing tone, shortening or expanding.</li>
<li><strong>Summarising</strong> text you provide.</li>
<li><strong>Brainstorming:</strong> producing many ideas to start from.</li>
<li><strong>Explaining:</strong> describing a concept at different levels, with examples.</li>
<li><strong>Structuring:</strong> turning messy notes into an outline, a table or a checklist.</li>
<li><strong>Translating</strong> between many languages, with human checks.</li>
<li><strong>Helping with code and formulas</strong>, which you must test.</li>
</ul>

<h3>What it does poorly or unreliably</h3>
<ul>
<li><strong>Facts, numbers, dates and quotations,</strong> especially precise or obscure ones.</li>
<li><strong>Sources:</strong> it may invent book titles, court cases, web links or research papers that look real.</li>
<li><strong>Recent events</strong> and anything after its cut-off date, unless it can search.</li>
<li><strong>Arithmetic and logic puzzles,</strong> particularly long chains of steps.</li>
<li><strong>Local knowledge:</strong> small places, local laws, local languages and community context are usually thinner in the training data, so quality can drop.</li>
<li><strong>Knowing what it does not know.</strong> It rarely says "I am not sure" unless prompted.</li>
</ul>

<h3>Hallucinations</h3>
<p>A <strong>hallucination</strong> is output that sounds right but is false or unsupported. It happens because the tool is built to produce plausible text, not to verify truth. Typical examples:</p>
<ul>
<li>A reference to a research paper that does not exist, with a believable author and year.</li>
<li>A law or policy clause that was never written.</li>
<li>A wrong total in a calculation presented with confidence.</li>
<li>A biography with invented details about a real person.</li>
</ul>

<h3>How to spot and reduce them</h3>
<ol>
<li><strong>Be suspicious of specifics:</strong> names, numbers, dates, quotes, links.</li>
<li><strong>Ask for sources,</strong> then open each one yourself. If it does not exist or does not say that, discard the claim.</li>
<li><strong>Give the material.</strong> Paste the text and say "Use only the text below." The tool then works from your material rather than memory.</li>
<li><strong>Allow "I don't know".</strong> Add: "If you are not sure, say so instead of guessing."</li>
<li><strong>Ask the same thing differently,</strong> or in a new chat. If answers disagree, treat all with caution.</li>
<li><strong>Check with a reliable source</strong> such as an official website, a textbook or an expert.</li>
</ol>

<h3>Common mistakes</h3>
<ul>
<li>Trusting an answer because it is well written.</li>
<li>Asking the tool to check its own work and treating its "yes, it is correct" as proof.</li>
<li>Copying references into a school essay without opening them.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Ask your AI tool: "Give me three published sources about mobile money use in Uganda, with authors and years."</li>
<li>Search for each source on the web or a library site.</li>
<li>Record how many exist and match exactly.</li>
<li>Ask again, this time adding: "If you are unsure a source exists, say so." Compare.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>AI is strong at language tasks and weak at precise facts, sources and numbers.</li>
<li>Hallucinations are confident, plausible errors; fluency is not proof.</li>
<li>Give the tool your own material and permission to say "I don't know".</li>
<li>Verify every important fact with a reliable source.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Choosing the right tasks for AI',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to decide whether a task is suitable for generative AI, needs careful human review, or should not be given to AI at all. This matters because productivity comes not from using AI for everything but from using it where it helps and where mistakes are cheap to catch.</p>

<h3>A simple decision test</h3>
<p>Ask four questions about any task.</p>
<ol>
<li><strong>What happens if the output is wrong?</strong> A wrong word in a casual invitation is a small problem. A wrong dosage, wrong legal advice or wrong salary figure is a serious one.</li>
<li><strong>Can I check the result quickly?</strong> Summaries and rewrites are easy to compare with your source; a brand-new statistic is not.</li>
<li><strong>Does the task involve private or confidential information?</strong> If yes, you must not paste it into a public tool (Module 4).</li>
<li><strong>Is it mine to hand over?</strong> Marks for an exam, a reference letter for a colleague or a decision about a person are your responsibility, and may be restricted by policy.</li>
</ol>

<h3>A traffic-light guide</h3>
<table>
<thead><tr><th>Light</th><th>Type of task</th><th>Examples</th></tr></thead>
<tbody>
<tr><td>Green: good fit</td><td>Low risk, easy to check, no private data</td><td>Improving the wording of your own text, brainstorming event names, creating a meeting agenda template, explaining a concept you are studying</td></tr>
<tr><td>Amber: use with care</td><td>Medium risk, needs checking or anonymising</td><td>Summarising a report, drafting a client email, analysing figures with names removed, translating a notice for the community</td></tr>
<tr><td>Red: do not use AI, or only with explicit approval</td><td>High risk or confidential</td><td>Medical, legal or financial decisions, grading learners fairly on its own, evaluating a person's job application with no human check, handling passwords, ID numbers or client records, anything an exam rule forbids</td></tr>
</tbody>
</table>

<h3>Think of AI as a fast assistant, not a manager</h3>
<p>A good model is a speedy junior assistant: it produces a first draft quickly, but you are the author and the responsible person. This keeps your judgement in charge. It also protects your skills: if you let AI do all the thinking while learning, you will not build the abilities employers want. Use it to practise (ask it to quiz you, or to explain a mistake), not only to produce finished answers.</p>

<h3>Time saved or time lost?</h3>
<p>AI saves time when the task is repetitive and you can check the result fast. It can waste time when you must correct long, wrong outputs. If you spend longer fixing a draft than writing it, change your approach: give a clearer prompt, a shorter task, or write it yourself.</p>

<h3>Common mistakes</h3>
<ul>
<li>Using AI for everything because it is available.</li>
<li>Skipping the review because the answer sounds sure.</li>
<li>Giving AI tasks that rely on private data or on human judgement and care.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>List ten tasks you do each week at work, school or in a business.</li>
<li>For each, answer the four questions and mark it green, amber or red.</li>
<li>Pick two green tasks and try them with an AI tool.</li>
<li>Write down how long each took, compared with doing it yourself, including checking.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Consider the cost of a mistake, how easily you can check, privacy and responsibility.</li>
<li>Green tasks are low risk and easy to check; red tasks should stay with people.</li>
<li>AI is a fast assistant; you remain the responsible author.</li>
<li>Measure real time saved, including your checking time.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does a large language model do at its core?
- Looks up the correct answer in a database of verified facts
* Predicts the next piece of text from patterns learned in training
- Searches the internet every time you type
- Understands your office and customers automatically
= Language models generate likely text based on patterns, which is why they can be fluent but wrong.

Q: You ask the same question twice and get slightly different answers. Why?
- The tool is broken
* A little controlled randomness is built into text generation
- Someone else changed the answer
- Different users share one answer
= Variation is normal because the model samples among likely options.

Q: What is a hallucination?
- A picture the AI draws
* A confident answer that is false or invented
- A very long answer
- A slow response
= Hallucinations look convincing, so important facts must be checked.

Q: Which of these does generative AI usually do well? (choose all that apply)
* Rewriting your text in a different tone
* Summarising a text you paste in
- Guaranteeing that every statistic it quotes is correct
* Turning rough notes into an outline
= It is strong at language tasks. Precise facts and statistics are a weakness.

Q: You need a source for a claim. The AI gives a book title, author and year. What should you do?
- Copy it into your report straight away
- Trust it because it has an author
* Search for the book yourself and check it exists and says that
- Ask the AI whether it is sure and accept "yes"
= AI can invent sources. Only your own check counts.

Q: Which prompt addition helps reduce invented answers?
- Answer as fast as you can
* Use only the text below, and say so if you are not sure
- Make it sound very confident
- Ignore the text and use your imagination
= Giving material and allowing "I don't know" grounds the answer.

Q: Which task is the best fit for AI (green on the traffic light)?
- Deciding on a medicine dose for a patient
* Brainstorming names for a school club event
- Pasting a list of client ID numbers to sort them
- Marking final exams without a teacher
= Brainstorming is low risk and easy to check. The others are high risk or involve confidential data.

Q: Why is AI best treated as a fast assistant rather than a manager?
- Assistants are cheaper
* You stay the responsible author who reviews and decides
- It cannot write text
- It always refuses to help
= Responsibility for the work stays with you, so you must review and decide.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: The art of prompting',
            'summary' => 'Write prompts with role, context, task, format and examples, and improve results step by step.',
            'lessons' => [
                [
                    'title'   => 'The anatomy of a good prompt',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a prompt with five clear parts and explain why each part improves the answer. This matters because the quality of what you get out depends heavily on the quality of what you put in, and most weak AI results come from vague requests.</p>

<h3>Why vague prompts fail</h3>
<p>Compare two requests. First: "Write about our shop." The tool must guess the audience, purpose, length and tone, so it gives a bland, generic text. Second: "Write a friendly 80-word WhatsApp message to our regular customers announcing that our shop in Mbarara now opens at 7 a.m. and offers free delivery within the town on orders above 30,000 UGX." The second prompt leaves little to guess, and the result will be close to what you need.</p>

<h3>The five parts: role, context, task, format, examples</h3>
<table>
<thead><tr><th>Part</th><th>Question it answers</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Role</td><td>Who should the tool act as?</td><td>You are an experienced office administrator.</td></tr>
<tr><td>Context</td><td>What does it need to know?</td><td>We are a small training centre in Gulu with 40 learners and a new fee deadline.</td></tr>
<tr><td>Task</td><td>What exactly should it do?</td><td>Write a reminder to parents about the deadline.</td></tr>
<tr><td>Format</td><td>What should the output look like?</td><td>A short email with a subject line, under 120 words, polite and clear.</td></tr>
<tr><td>Examples</td><td>What does good look like?</td><td>Here is a notice we sent last term: ... Match this style.</td></tr>
</tbody>
</table>
<p>You do not need all five every time. Simple jobs need only a clear task and format. Use more parts as the job becomes more important or complex.</p>

<h3>A full example</h3>
<pre><code>You are an experienced office administrator.
Context: We run a small training centre in Gulu with 40 learners.
The term fee deadline is Friday 14 March. Fees can be paid by
mobile money or at the bank.
Task: Write a reminder to parents about the deadline.
Format: A short email with a subject line, under 120 words,
polite and clear, ending with the office phone number placeholder.</code></pre>

<h3>Tips for each part</h3>
<ul>
<li><strong>Role:</strong> choose a role relevant to the job, such as teacher, editor or customer-care officer. It shapes vocabulary and tone, though it does not give the tool real qualifications.</li>
<li><strong>Context:</strong> include purpose, audience, location, constraints and any facts that must be used. Provide facts yourself rather than trusting the tool's memory.</li>
<li><strong>Task:</strong> use one clear action verb: write, summarise, compare, list, rewrite, translate, classify.</li>
<li><strong>Format:</strong> specify length, structure (bullets, table, numbered steps), tone and reading level.</li>
<li><strong>Examples:</strong> one or two samples of the style you want are often better than a long description.</li>
</ul>

<h3>Use delimiters for pasted text</h3>
<p>When you include a long text, mark where it starts and ends so the tool does not mix it with your instructions:</p>
<pre><code>Summarise the notice between the lines in three bullet points.
---
(paste the notice here)
---</code></pre>

<h3>Common mistakes</h3>
<ul>
<li>Giving the task but no audience or purpose.</li>
<li>Asking for several different things in one sentence.</li>
<li>Forgetting to say how long or in what format the answer should be.</li>
<li>Putting private details into the prompt that are not needed.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Choose a real message you need to write, such as a notice, an email or a social media post.</li>
<li>First try a one-line prompt and save the answer.</li>
<li>Rewrite the prompt with role, context, task, format and an example.</li>
<li>Compare both results. List three differences.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Clear prompts have role, context, task, format and, when useful, examples.</li>
<li>Give the facts yourself and say who the audience is.</li>
<li>One clear action verb and a defined output format improve results.</li>
<li>Separate pasted text from instructions with clear markers.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Showing examples and controlling the format',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to teach an AI tool your preferred style with examples, and request outputs in tables, lists and fixed layouts. This matters because the results you can use directly at work are those in the right shape, saving you time reformatting.</p>

<h3>Examples teach faster than descriptions</h3>
<p>Showing one or more samples is often called <strong>few-shot prompting</strong>: you give "a few shots" of the pattern, and the tool follows it. It is useful when you need a certain tone, a certain structure, or a repeated task such as sorting customer messages.</p>
<p>Example for classifying customer messages:</p>
<pre><code>Classify each customer message as Order, Complaint or Question.
Reply with the label only.

Message: "Where is my parcel? It was due on Monday."  Label: Complaint
Message: "Do you sell 20-litre jerrycans?"            Label: Question
Message: "Please send two bags of rice to Ntinda."    Label: Order

Message: "The sugar I received was damp."             Label:</code></pre>
<p>The tool sees the pattern and finishes the last line. Always include examples that cover different cases, including an awkward one, and keep them correct. If your examples contain a mistake, the tool copies it.</p>

<h3>Matching your writing style</h3>
<ol>
<li>Paste two or three short samples of your own writing, such as emails you wrote.</li>
<li>Say: "Describe my writing style in five bullet points." Read the description and correct it.</li>
<li>In later prompts say: "Write in this style: (the bullets)".</li>
</ol>
<p>Only use your own writing or text you are allowed to share. Do not paste confidential emails.</p>

<h3>Asking for specific formats</h3>
<table>
<thead><tr><th>You need</th><th>Ask like this</th></tr></thead>
<tbody>
<tr><td>A table</td><td>Present this as a table with columns Item, Quantity and Price in UGX.</td></tr>
<tr><td>A checklist</td><td>Give me a checklist of 8 items, each starting with a verb.</td></tr>
<tr><td>Numbered steps</td><td>Explain how to do this in numbered steps for a beginner.</td></tr>
<tr><td>Length control</td><td>Write exactly three sentences. / Under 100 words.</td></tr>
<tr><td>Reading level</td><td>Use simple English suitable for a secondary school learner.</td></tr>
<tr><td>Tone</td><td>Professional but warm, no slang, no exclamation marks.</td></tr>
<tr><td>Fixed layout</td><td>Use these headings: Problem, Cause, Action, Deadline.</td></tr>
</tbody>
</table>

<h3>Structured output for spreadsheets</h3>
<p>If you want to paste results into a spreadsheet, ask for a format it understands, for example: "Return the result as a table with columns separated by commas, with no extra text." Then paste into a text file or use Paste Special. Check the columns line up and that numbers are correct.</p>

<h3>Negative instructions and boundaries</h3>
<p>Tell the tool what to avoid: "Do not invent names or numbers. Use [placeholder] where information is missing." Placeholders are helpful, because they stop the tool from filling gaps with guesses, and make it obvious what you must complete.</p>

<h3>Common mistakes</h3>
<ul>
<li>Giving only one extreme example, so every output looks the same.</li>
<li>Giving examples with errors.</li>
<li>Asking for a format and then not checking that the data inside is correct.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Write five short customer messages for an imaginary shop.</li>
<li>Prepare a classification prompt with three correct examples as above.</li>
<li>Run it on your five messages and check the labels.</li>
<li>Now ask the tool to present the five messages and labels as a table.</li>
<li>Add "Use [placeholder] where information is missing" to a drafting prompt and see the difference.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Examples (few-shot prompting) teach style and structure quickly.</li>
<li>Name the format: table, checklist, steps, word count, headings, tone and reading level.</li>
<li>Use placeholders to avoid invented details.</li>
<li>Check the content as well as the layout.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Iterating: improving the first answer',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to turn a mediocre first answer into a strong one through short follow-up instructions. This matters because good users of AI rarely accept the first output. They treat the first answer as a draft and steer it, which is usually faster than writing a perfect prompt from the start.</p>

<h3>A conversation, not a vending machine</h3>
<p>The tool keeps your earlier messages in view (within its context window), so you can refine. Instead of starting again, say what to change. Specific feedback gets better results than "make it better".</p>

<h3>Useful follow-up instructions</h3>
<table>
<thead><tr><th>Problem</th><th>Follow-up</th></tr></thead>
<tbody>
<tr><td>Too long</td><td>Cut this to 100 words and keep the deadline and the contact details.</td></tr>
<tr><td>Too formal or stiff</td><td>Make it warmer and more direct, as if speaking to a neighbour, but still professional.</td></tr>
<tr><td>Too general</td><td>Add two concrete examples from a small shop in Kampala.</td></tr>
<tr><td>Wrong audience</td><td>Rewrite for parents who may not speak English fluently. Use short sentences.</td></tr>
<tr><td>Unsure about facts</td><td>Mark any statement you are not certain about with [CHECK].</td></tr>
<tr><td>Wrong structure</td><td>Reorder it: start with the action required, then the reason.</td></tr>
<tr><td>Too many ideas</td><td>Keep only the three strongest and explain why you chose them.</td></tr>
</tbody>
</table>

<h3>Ask for options and compare</h3>
<p>Request several versions: "Give me three different subject lines: one formal, one friendly, one urgent." Then say which you prefer and why: "I like version 2, but make it shorter." Choosing and combining is faster than starting from nothing.</p>

<h3>Ask the tool to improve its own prompt</h3>
<p>If results disappoint, say: "Ask me up to five questions you need answered to do this task well." Answer the questions, and the tool will have better context. This is a good habit for complex work.</p>

<h3>Know when to restart</h3>
<p>Long conversations can drift or be confused by earlier instructions. If the tool keeps repeating a mistake, start a new chat, and paste a clean summary of what you need. Keep a small note of the best prompt wording as you discover it.</p>

<h3>A short worked example</h3>
<ol>
<li>First prompt: "Write a short post announcing our free coding class." The answer is generic.</li>
<li>Follow-up: "Add the date (Saturday 12 April), place (Lira Youth Centre) and that learners should bring a charged phone."</li>
<li>Follow-up: "Make it 60 words, friendly, with two emojis and a call to action to register by Thursday."</li>
<li>Follow-up: "Now give me a version in simple English for first-time learners."</li>
</ol>
<p>Four quick steps produced a useful post. You still check every date, place and phone number yourself before publishing.</p>

<h3>Common mistakes</h3>
<ul>
<li>Accepting the first draft without improving it.</li>
<li>Giving feedback that is too vague, such as "improve this".</li>
<li>Continuing a confused conversation instead of restarting clean.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Choose a real task and write a quick, simple prompt.</li>
<li>Give three specific follow-up instructions from the table above.</li>
<li>Ask for three alternative versions and choose the best parts.</li>
<li>Try "Ask me questions before you start" on a new task and compare the results.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Treat the first answer as a draft and steer it with specific follow-ups.</li>
<li>Ask for options, then choose and combine.</li>
<li>Let the tool ask you questions to gather missing context.</li>
<li>Restart with a clean prompt when a conversation drifts.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Step-by-step thinking and self-checking prompts',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use prompt patterns that break complex tasks into steps, request reasoning you can inspect, and make the tool critique its own draft. This matters because bigger tasks, such as a plan, an analysis or a policy comparison, go wrong when asked for in one jump.</p>

<h3>Break big tasks into stages</h3>
<p>Instead of "Plan our school fundraising event", split the work:</p>
<ol>
<li>"List the main tasks needed to organise a fundraising concert for 200 people with a budget of 2,000,000 UGX."</li>
<li>"Group these tasks into a four-week timeline."</li>
<li>"For week one, create a checklist with who does what."</li>
<li>"Identify five risks and a simple action for each."</li>
</ol>
<p>Each step is easier to check and adjust. This method is called <strong>prompt chaining</strong>: the output of one prompt feeds the next.</p>

<h3>Ask for reasoning you can inspect</h3>
<p>For analysis or comparisons, request the reasoning along with the answer: "Show your reasoning step by step, then give a recommendation." Reading the steps helps you notice weak assumptions or errors. Be careful: a written explanation can look logical and still be wrong, and it is not proof of how the system actually produced the answer. Treat the steps as something to review, not as a guarantee.</p>

<h3>Self-critique</h3>
<p>After a draft, ask the tool to review it from a defined viewpoint.</p>
<pre><code>Review the email above as a strict supervisor. List up to five
weaknesses: unclear points, missing information, wrong tone.
Then rewrite it to fix them.</code></pre>
<p>This often improves clarity. But remember the tool cannot reliably verify facts. A "review" will not catch an invented number if the tool itself believes it. Facts are still your job.</p>

<h3>Assumptions and questions</h3>
<ul>
<li>"List the assumptions you made. I will correct any that are wrong."</li>
<li>"What information is missing for a reliable answer?"</li>
<li>"Give a best case, a worst case and a most likely case."</li>
</ul>

<h3>Different viewpoints</h3>
<p>Ask for a counter-argument: "Give three reasons this plan could fail" or "Argue the opposite view fairly". This helps you avoid one-sided thinking when making decisions, though you must judge which points are realistic for your situation.</p>

<h3>Role-play for practice</h3>
<p>The tool can act as a pretend interviewer, customer or exam marker so you can practise: "Act as an interviewer for a customer-service assistant job. Ask me one question at a time and wait for my answer. After each answer, give me short feedback." This is a good use for learning: you do the thinking, and the tool gives feedback.</p>

<h3>Common mistakes</h3>
<ul>
<li>Asking for a complete plan, report or analysis in one prompt.</li>
<li>Believing a step-by-step explanation proves the answer is right.</li>
<li>Using AI's review as a replacement for fact-checking.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Choose a project, such as a small community clean-up day.</li>
<li>Use the four-stage chain above and note what each stage produced.</li>
<li>Ask the tool to list its assumptions and correct at least one.</li>
<li>Ask for five weaknesses in the plan and decide which are real.</li>
<li>Practise a short mock interview using the role-play prompt.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Chain prompts: split big tasks into smaller, checkable steps.</li>
<li>Ask for reasoning and assumptions to review, but do not treat them as proof.</li>
<li>Self-critique improves drafts but cannot replace fact-checking.</li>
<li>Role-play is a strong way to practise skills such as interviews.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which prompt will most likely give a useful result?
- Write about our shop
* Write a friendly 80-word WhatsApp message to regular customers saying the Mbarara shop opens at 7 a.m. and offers free delivery within town on orders above 30,000 UGX
- Make an announcement
- Do marketing
= The specific prompt gives audience, purpose, facts, length and tone, leaving little to guess.

Q: Which parts make up the prompt pattern in this course? (choose all that apply)
* Role
* Context
* Format
- Password
= The pattern is role, context, task, format and examples. A password has no place in a prompt.

Q: Why use delimiters such as lines of dashes around pasted text?
- They make the text look nicer
* They show the tool exactly where your pasted material starts and ends, separate from instructions
- They hide the text from the tool
- They are required by law
= Clear markers prevent the tool from mixing your instructions with the material.

Q: What is few-shot prompting?
- Asking a question briefly
* Showing a few examples of the pattern you want so the tool follows it
- Using the tool only a few times
- Shortening the output
= Examples teach style and structure quickly.

Q: Why add "Use [placeholder] where information is missing" to a drafting prompt?
- To make the document longer
* To stop the tool from inventing details and to show you what you must fill in
- To change the language
- To make the tool slower
= Placeholders replace guesses with visible gaps that you complete from true information.

Q: The first draft is too long and too stiff. What is the best next step?
- Give up and write it yourself
* Reply with specific follow-ups, such as cut to 100 words and make the tone warmer
- Start a new chat with the same prompt
- Say "make it better"
= Specific instructions steer the draft faster than a vague request.

Q: What does prompt chaining mean?
- Linking many users to one chat
* Splitting a large task into stages where each answer feeds the next prompt
- Writing prompts in capital letters
- Repeating the same prompt many times
= Chaining breaks complex work into smaller, checkable steps.

Q: You ask the tool to review its own draft and it says everything is correct. What does that show?
- The facts are verified
* Nothing reliable; you still need to check facts with trusted sources
- The draft is perfect
- The tool has been tested by experts
= A self-review can improve clarity but cannot reliably verify facts.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: AI for everyday work',
            'summary' => 'Practical uses for drafting, summarising, analysing information, translating and running a small business.',
            'lessons' => [
                [
                    'title'   => 'Drafting emails, letters and reports',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use AI to produce a first draft of workplace writing and then turn it into a message that is accurate and sounds like you. This matters because writing takes up a large part of office work, and starting from a blank page is the slowest part.</p>

<h3>A safe drafting workflow</h3>
<ol>
<li><strong>Decide the point first.</strong> Write in two lines what the reader must know or do, and by when. If you do not know the point, the AI cannot supply it.</li>
<li><strong>Gather the true facts</strong> you want included: names (only if appropriate), dates, amounts, references.</li>
<li><strong>Prompt with role, context, task and format.</strong> Provide the facts and ask for placeholders for unknown details.</li>
<li><strong>Read the draft slowly</strong> and check every fact, name, date and amount against your source.</li>
<li><strong>Edit for your voice.</strong> Remove phrases you would never say, such as "I hope this email finds you well" if you never use it.</li>
<li><strong>Add what only you know:</strong> relationship, history, small personal touches.</li>
</ol>

<h3>Sample prompt for an email</h3>
<pre><code>You are an assistant for a small NGO programme officer.
Task: Draft an email to a school headteacher requesting a meeting
about our after-school coding club.
Facts: The club would run on Wednesdays 3 to 5 p.m. for 20 learners.
We need one classroom with power sockets. We would like to meet
next week. My name: [NAME]. Phone: [PHONE].
Format: Polite, professional, under 150 words, with a subject line.
Do not invent any facts. Use [placeholders] for missing information.</code></pre>

<h3>Other drafting jobs</h3>
<ul>
<li><strong>Reports:</strong> ask for a structure (headings) first, then draft each section from your notes.</li>
<li><strong>Meeting minutes:</strong> paste your rough notes (no private details) and ask for decisions, actions, owners and deadlines in a table.</li>
<li><strong>Job and grant applications:</strong> let the tool tighten your own text. Never let it invent experience or results.</li>
<li><strong>Social media posts and notices:</strong> ask for three versions in different tones.</li>
<li><strong>Templates:</strong> ask for a reusable template with placeholders, such as for payment reminders.</li>
</ul>

<h3>Rewriting and editing</h3>
<p>Rewriting your own work is a strong use. Try prompts like: "Edit the text below for grammar and clarity. Keep my meaning and tone. Show your changes as a list." Or: "Make this paragraph easier to read for a Primary 7 reader." Always compare with the original to be sure the meaning stayed the same.</p>

<h3>Keep your own voice and skills</h3>
<p>If every message you send sounds like the same generic robot, readers notice. Develop a habit of writing the first sentence or the key points yourself and using AI to polish. If you are a student learning to write, write the draft yourself first and ask AI for feedback, not for the whole text.</p>

<h3>Common mistakes</h3>
<ul>
<li>Sending the draft without reading it carefully, including leftover placeholders such as [NAME].</li>
<li>Letting the tool add promises, prices or dates you did not approve.</li>
<li>Using a tone that does not fit the culture or relationship with the reader.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Choose a real email or notice you need to write this week.</li>
<li>Write your two-line point and list the true facts.</li>
<li>Use the sample prompt shape to generate a draft.</li>
<li>Check each fact and fix the tone so it sounds like you.</li>
<li>Count how long the whole process took compared with writing from scratch.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Decide the point and gather the facts before you prompt.</li>
<li>Use placeholders and verify every detail in the draft.</li>
<li>Edit for your own voice and add what only you know.</li>
<li>Use AI to polish and structure rather than to replace your thinking.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Summarising documents and meetings',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to summarise long texts reliably with AI and spot when a summary has lost or changed something important. This matters because most people drown in long reports, emails and meeting notes, and a fast, accurate summary helps you decide what needs your full attention.</p>

<h3>Why summaries work well, and why they can mislead</h3>
<p>Summarising is a language task and works on text you provide, so it is one of the most dependable uses. But a summary always leaves things out, and the tool decides what. It may drop an exception, a deadline or a number, or slightly change a meaning. A summary is a map, not the territory.</p>

<h3>Prompts that produce useful summaries</h3>
<p>Tell the tool the purpose, the reader and the shape.</p>
<table>
<thead><tr><th>Need</th><th>Prompt idea</th></tr></thead>
<tbody>
<tr><td>Quick overview</td><td>Summarise the text below in five bullet points for a busy manager.</td></tr>
<tr><td>Decisions and actions</td><td>List all decisions, actions, owners and deadlines in a table. If an owner or deadline is not stated, write "not stated".</td></tr>
<tr><td>Different audience</td><td>Explain this policy in simple language for new volunteers, in under 150 words.</td></tr>
<tr><td>Key numbers</td><td>List every figure, date and amount mentioned, with its context.</td></tr>
<tr><td>Questions</td><td>List five questions this document leaves unanswered.</td></tr>
</tbody>
</table>

<h3>Handling long documents</h3>
<ol>
<li>Check the tool's limits. Very long texts may not fit in its context window, or may be partly ignored.</li>
<li>If needed, split the document into sections (by headings) and summarise each part separately.</li>
<li>Then ask for a combined summary of the section summaries.</li>
<li>For important documents, ask: "Quote the sentence in the text that supports each bullet." You can then check the quotes against the original.</li>
</ol>

<h3>Meeting notes</h3>
<p>If you take typed notes, paste them (removing names or sensitive details if the meeting was confidential) and ask for minutes: summary, decisions, action list. Do not record or transcribe people without their knowledge and permission. Check your workplace rules before using any recording or transcription feature, and check what the tool does with the recording.</p>

<h3>Verifying a summary</h3>
<ul>
<li>Read the original introduction and conclusion and see if the summary agrees.</li>
<li>Search the original for key numbers and dates mentioned in the summary.</li>
<li>Ask the tool: "What important points from the original are not in your summary?"</li>
<li>For decisions that matter (money, legal, safety), read the full text of the relevant part yourself.</li>
</ul>

<h3>Common mistakes</h3>
<ul>
<li>Pasting a confidential document into a public AI tool (see Module 4).</li>
<li>Relying on a summary for a legal contract or medical instruction.</li>
<li>Not noticing that the summary quietly changed "may" into "will".</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Choose a public text of about 800 words, such as a news article or a public notice.</li>
<li>Ask for a five-bullet summary, then for a table of dates and numbers.</li>
<li>Verify each number against the original.</li>
<li>Ask for the points left out and decide whether any are important.</li>
<li>Write your own two-sentence summary and compare it with the tool's.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Summarising text you provide is dependable but not perfect.</li>
<li>State the purpose, the reader and the format in the prompt.</li>
<li>Split very long texts and ask for supporting quotes.</li>
<li>Verify numbers and read key sections yourself for important decisions.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Analysing information and data with AI',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use AI to explore information, such as survey comments and simple tables, and to explain results, while keeping the checking of the numbers in your own hands. This matters because many offices have data they never analyse for lack of time, and AI can help you start, if used carefully.</p>

<h3>Where AI helps with analysis</h3>
<ul>
<li><strong>Organising text answers.</strong> Group 100 survey comments into themes such as Price, Quality, Delivery and Staff.</li>
<li><strong>Explaining formulas and methods.</strong> "Explain what this spreadsheet formula does, step by step."</li>
<li><strong>Suggesting approaches.</strong> "I have monthly sales for three shops. What questions could I answer, and which charts would suit?"</li>
<li><strong>Writing formulas or small scripts</strong> that you then test.</li>
<li><strong>Drafting a plain-language explanation</strong> of findings that you have already verified.</li>
</ul>

<h3>Be careful with numbers</h3>
<p>A language model is not a calculator. It may make arithmetic mistakes, miscount rows or skip entries in a long list. Some tools can run code or spreadsheets to compute accurately, but you should still confirm. Safer practice:</p>
<ol>
<li>Do the calculations in a spreadsheet or calculator you trust.</li>
<li>Use AI to suggest formulas, then test them on a few rows where you know the answer.</li>
<li>Spot-check totals by hand, and compare with a second method.</li>
<li>Ask the tool to show the steps and the formula it used, so you can inspect them.</li>
</ol>

<h3>Example: coding survey comments</h3>
<pre><code>Below are 40 customer comments, one per line (names removed).
Task: group them into no more than six themes. For each theme give
a name, the number of comments, and two example comments quoted
exactly. List any comment that does not fit as "Other".</code></pre>
<p>Then check: do the counts add up to 40? Are the quoted comments really in your list? Read ten comments yourself and see if the groups make sense. You may disagree with a grouping, and that is fine, you decide the final categories.</p>

<h3>Remove personal data first</h3>
<p>Before pasting data, remove names, phone numbers, ID numbers and any detail that could identify a person, unless your organisation has approved the tool for that data. Summaries and totals are usually safer to share than raw personal records.</p>

<h3>Avoid over-claiming</h3>
<ul>
<li>Correlation is not cause. If sales rose when it rained, the AI may suggest a reason. That is a guess, not proof.</li>
<li>Small samples mislead. Ten comments are not "what customers think".</li>
<li>Ask: "What are the limits of this analysis?" and include them in your report.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Write 15 short, made-up customer comments for a shop.</li>
<li>Ask the tool to group them into themes with counts and quotes.</li>
<li>Check the counts and quotes by hand.</li>
<li>Give it a small sales table of 8 rows and ask it to explain which formula calculates total revenue, then test the formula in a spreadsheet.</li>
<li>Write two sentences on the limits of your analysis.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>AI is good for organising text, explaining methods and suggesting approaches.</li>
<li>Calculate in a spreadsheet and verify; do not trust mental arithmetic from a language model.</li>
<li>Remove personal data before sharing information with a tool.</li>
<li>State the limits and avoid claiming causes you cannot support.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Translation, language support and small-business uses',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use AI for translation and language support in East African settings, and for practical small-business tasks, with the right human checks. This matters because many entrepreneurs and workers communicate in several languages and need help with marketing, customer service and planning at low cost.</p>

<h3>Translation with AI</h3>
<p>AI can translate between many languages quickly, and is often good at major languages such as English, French, Swahili and Arabic. Quality is usually lower for languages with less written material online, including many Ugandan and other African languages, and for local expressions, proverbs and technical vocabulary. Treat any translation as a draft.</p>
<ol>
<li>Tell the tool the audience and purpose: "Translate into Swahili for farmers in a rural community notice. Use simple everyday words."</li>
<li>Ask it to keep names, numbers and dates unchanged.</li>
<li>Ask for a back-translation into English and compare it with your original. Differences reveal errors.</li>
<li>Have a fluent speaker review anything important, such as health advice, contracts and safety notices.</li>
</ol>

<h3>Language learning and support</h3>
<ul>
<li>Ask it to explain why a sentence is wrong and to give three correct examples.</li>
<li>Practise conversations: "Be my English conversation partner. Correct my mistakes after each reply."</li>
<li>Simplify text: "Rewrite this paragraph at a simple reading level and explain hard words."</li>
<li>Improve accessibility: ask for plain-language versions of forms and notices.</li>
</ul>

<h3>Small-business uses</h3>
<table>
<thead><tr><th>Task</th><th>How AI helps</th><th>Your check</th></tr></thead>
<tbody>
<tr><td>Product descriptions</td><td>Writes several short options from your facts</td><td>Remove claims you cannot prove</td></tr>
<tr><td>Customer replies</td><td>Drafts polite answers to common questions</td><td>Confirm prices, stock and policy</td></tr>
<tr><td>Social media calendar</td><td>Suggests post ideas for a month</td><td>Choose only what fits your brand and local culture</td></tr>
<tr><td>Business plan outline</td><td>Gives headings and questions to answer</td><td>Fill numbers with your real research</td></tr>
<tr><td>Price list or invoice template</td><td>Creates a layout with placeholders</td><td>Check all amounts and tax rules yourself</td></tr>
<tr><td>Training your staff</td><td>Writes simple step-by-step instructions</td><td>Test them on a real person</td></tr>
</tbody>
</table>

<h3>Cultural care</h3>
<p>Marketing text and jokes from AI may not suit your customers' culture or religion. Check names, titles and greetings. Greetings and respectful forms matter in many communities, and a translated message that is too blunt can offend. Do not use AI to create fake customer reviews or fake testimonials: this is dishonest and may break advertising and consumer rules.</p>

<h3>Common mistakes</h3>
<ul>
<li>Publishing an unchecked translation of a health or safety message.</li>
<li>Letting the tool promise discounts or delivery times you cannot keep.</li>
<li>Giving the tool customer phone numbers and details to personalise replies.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Write a short notice in English, such as a change of shop opening hours.</li>
<li>Translate it into another language you know with an AI tool.</li>
<li>Ask for a back-translation and compare it with your original.</li>
<li>Ask for three product descriptions for something you sell or could sell, using only the facts you supply.</li>
<li>Delete any claim you cannot prove and note the changes.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Translations are drafts; back-translate and have a fluent speaker check important texts.</li>
<li>AI helps small businesses with descriptions, replies, calendars and templates.</li>
<li>Check prices, claims and culture; never invent reviews.</li>
<li>Keep customer personal details out of the tool.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What should you do before asking AI to draft an important email?
- Nothing, simply paste "write an email"
* Decide the point you want to make and gather the true facts to include
- Remove all facts so the tool decides
- Ask the tool to choose the recipient
= Your point and facts must come from you, so the tool can build the draft on them.

Q: You receive an AI draft with the text "[NAME]" still in it. What does this show?
- The draft is finished
* A placeholder is left to complete, and you must read carefully before sending
- The tool refused the task
- The tool added a name from its memory
= Placeholders show where details are missing, and leftover ones signal that the draft was not checked.

Q: Which is the best way to verify a summary of a long report for an important decision?
- Trust it because it is short
* Check key numbers and dates against the original and read the relevant sections yourself
- Ask the tool to say it is correct
- Ask another person to ignore the original
= Summaries omit and can change details, so confirm against the source.

Q: Which are good uses of AI for data work? (choose all that apply)
* Grouping text comments into themes, then checking counts
* Explaining what a spreadsheet formula does
- Using its mental arithmetic as the final total in a financial report
* Suggesting questions and charts for a dataset
= It can organise, explain and suggest, but calculations should be done and verified in reliable tools.

Q: Why remove names and phone numbers before sharing survey data with an AI tool?
- It makes the data longer
* To protect people's personal information unless the tool has been approved for that data
- Because AI cannot read names
- Because phone numbers are always wrong
= Personal data should be protected, and removing identifiers reduces risk.

Q: What is a useful way to check a machine translation of a notice?
- Trust it completely
* Ask for a back-translation into the original language and have a fluent speaker review it
- Translate it again with the same tool and compare nothing
- Remove all the numbers
= Back-translation and human review catch errors that may be invisible to a non-speaker.

Q: A tool suggests that sales rose last month because of the rain. How should you treat this?
- As proven fact
* As a guess that needs evidence, because correlation is not cause
- As an instruction
- As a legal rule
= Patterns may suggest ideas, but causes require evidence.

Q: Which small-business use of AI is dishonest and should be avoided?
- Drafting product descriptions from your own facts
- Creating a template for invoices
* Creating fake customer reviews to attract buyers
- Writing step-by-step staff instructions
= Fake reviews deceive customers and may break advertising and consumer rules.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Checking, bias and privacy',
            'summary' => 'Verify what AI tells you, notice unfair patterns, and keep personal and confidential information safe.',
            'lessons' => [
                [
                    'title'   => 'Fact-checking AI output',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to check AI output with a simple routine and judge which sources to trust. This matters because the person who sends the message, signs the report or hands in the assignment is responsible for what it says, and "the AI told me" is not a defence.</p>

<h3>What to check</h3>
<p>Not everything needs the same level of checking. Match the effort to the risk. Always check:</p>
<ul>
<li><strong>Numbers, statistics and calculations.</strong></li>
<li><strong>Names, titles, dates, places and quotations.</strong></li>
<li><strong>Claims about laws, policies, medicine, safety or money.</strong></li>
<li><strong>Sources, links and references.</strong></li>
<li><strong>Anything that would embarrass or harm someone if wrong.</strong></li>
</ul>

<h3>A five-step checking routine</h3>
<ol>
<li><strong>Underline the claims.</strong> Read the output and mark every statement that could be true or false.</li>
<li><strong>Rank them.</strong> Which ones would cause harm or loss if wrong? Check those first.</li>
<li><strong>Find a trustworthy source</strong> for each: an official website, a government or university site, an established news organisation, a textbook or an expert you can ask.</li>
<li><strong>Compare.</strong> Do at least two independent sources agree? If you can find none, remove the claim.</li>
<li><strong>Record.</strong> Note the source next to each important claim so you can answer if someone asks.</li>
</ol>

<h3>Judging sources</h3>
<table>
<thead><tr><th>Question</th><th>Why it matters</th></tr></thead>
<tbody>
<tr><td>Who is behind it?</td><td>Official bodies and known organisations are accountable for errors.</td></tr>
<tr><td>Is it current?</td><td>Laws, prices and guidance change. Check the date.</td></tr>
<tr><td>Does it show evidence?</td><td>Data, references and methods can be checked.</td></tr>
<tr><td>Does it have a motive?</td><td>Adverts and persuasion pages may present only one side.</td></tr>
<tr><td>Do others agree?</td><td>A claim found only in one odd place is suspicious.</td></tr>
</tbody>
</table>

<h3>Check references and links</h3>
<p>If the tool gives a web address, open it. Many links lead nowhere, or lead to a page that does not say what the tool claimed. If it names a report or book, search for the title, author and year. A citation that cannot be found must not appear in your work.</p>

<h3>Tools with web search</h3>
<p>Some AI tools can search the web and show links. This can reduce hallucinations, but it does not remove the need to check. The tool might pick a poor source, misread a page or summarise it wrongly. Open the sources it cites and read the relevant part.</p>

<h3>Check images and audio too</h3>
<p>AI can create realistic fake images, voices and videos. Before sharing a surprising picture or audio clip, check where it came from, search for the original, and look for odd details such as strange hands, text or shadows. If you cannot confirm it, do not share it. Spreading false content can cause real harm in communities, especially on WhatsApp groups.</p>

<h3>Common mistakes</h3>
<ul>
<li>Checking only the parts that look suspicious, and not the confident ones.</li>
<li>Asking the same AI tool whether its answer is true and accepting its yes.</li>
<li>Using a source that is old, anonymous or selling something.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Ask an AI tool to write a 150-word explanation of a topic you can verify, such as how mobile money charges work in Uganda.</li>
<li>Underline every factual claim.</li>
<li>Check the three most important claims with an official or trustworthy source.</li>
<li>Mark each claim as confirmed, wrong or not found.</li>
<li>Write a corrected version.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>You are responsible for what you publish or send, whoever drafted it.</li>
<li>Check numbers, names, dates, quotes, laws and sources; rank claims by risk.</li>
<li>Prefer official, current, evidence-based sources and confirm with a second one.</li>
<li>Open every link and confirm every reference; remove what you cannot verify.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Bias, fairness and responsible judgement',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain where AI bias comes from, recognise biased output, and take steps to reduce unfairness in your own use. This matters because AI-written texts and AI-supported decisions can quietly repeat stereotypes, leave groups out and treat people unequally, and it is often the user who has to notice.</p>

<h3>Where bias comes from</h3>
<p><strong>Bias</strong> means a systematic leaning that treats some people or ideas unfairly. AI tools learn from human-written text and data, which contain human prejudices and gaps.</p>
<ul>
<li><strong>Uneven data.</strong> Some languages, regions and communities appear much more often than others online. A tool may know a lot about large Western cities and little about a rural district in Uganda.</li>
<li><strong>Historical stereotypes.</strong> If old texts mostly describe nurses as women and engineers as men, the model may repeat that.</li>
<li><strong>Design choices.</strong> The people who build and test the tool decide what counts as a good answer, and may overlook some users.</li>
<li><strong>Your prompt.</strong> Your wording can push the answer, for example by asking leading questions or including assumptions.</li>
</ul>

<h3>What biased output can look like</h3>
<ul>
<li>A story or image about "a doctor" always showing the same type of person.</li>
<li>Advice that assumes a fast internet connection, a car, or a credit card.</li>
<li>Descriptions of a region or ethnic group that rely on stereotypes.</li>
<li>Names or places from only one culture in examples.</li>
<li>A CV-screening tool that ranks candidates lower because of gender, tribe, age, disability or the area they come from.</li>
</ul>

<h3>Test for bias</h3>
<ol>
<li><strong>Swap test.</strong> Run the same prompt with a different name, gender, tribe, region or age. Do the answers differ in quality or tone?</li>
<li><strong>Representation check.</strong> Ask for ten examples of leaders, workers or families. Look at who is included and who is missing.</li>
<li><strong>Assumption check.</strong> Ask: "What assumptions about the reader did you make in this advice?"</li>
<li><strong>Ask others.</strong> People affected by the text can spot problems you do not see.</li>
</ol>

<h3>Reducing bias in your use</h3>
<ul>
<li>Ask directly for diverse and local examples: "Use examples from different regions of Uganda and different kinds of work."</li>
<li>Remove unnecessary personal details, such as names, gender and age, when asking for an evaluation of a text or application.</li>
<li>Do not ask AI to judge people's character or ability from photos, names or short descriptions.</li>
<li>Edit stereotypes out of the text before publishing.</li>
</ul>

<h3>Decisions about people need humans</h3>
<p>Hiring, marks, loans, discipline and access to services affect lives. An AI suggestion may look neutral but carry hidden bias, and it cannot explain itself fairly. Keep a trained person responsible, provide a way for people to ask for review, and follow the law and your organisation's policy. Many guidelines, such as those from UNESCO, stress human oversight, fairness, transparency and accountability as principles for AI use.</p>

<h3>Other responsible-use concerns</h3>
<ul>
<li><strong>Over-reliance.</strong> If you stop thinking, your skills decline.</li>
<li><strong>Harmful content.</strong> Do not use AI to harass, deceive, impersonate or create fake news.</li>
<li><strong>Environmental and cost impact.</strong> AI uses electricity and computing resources. Use it where it adds value.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Ask an AI tool to write a short story about "a successful entrepreneur" and note who the person is.</li>
<li>Repeat with the same prompt but ask for a character from a different region or background.</li>
<li>Run a swap test with two names on a prompt such as "Write a reference summary for this candidate".</li>
<li>List any differences you notice and how you would correct the output.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Bias comes from uneven data, old stereotypes, design choices and your own prompts.</li>
<li>Use swap and representation tests, and ask for diverse local examples.</li>
<li>Do not leave decisions about people to AI; keep accountable humans involved.</li>
<li>Do not use AI for harassment, impersonation or misleading content.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Privacy and confidential data',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to decide what information must never go into an AI tool, remove personal details from a prompt, and know the basic ideas of data protection law. This matters because text you type into an online tool leaves your device, and a careless paste can expose learners, customers or your employer.</p>

<h3>Where your prompts go</h3>
<p>Most AI assistants run on servers owned by a company. What you type is sent there and may be stored. Depending on the service and your settings, conversations may be reviewed by staff, kept for a period, or used to improve the tool. Policies differ and change, so read the privacy settings and terms of the tool you use, and look for options that limit storing or training use. Business and education versions often have stronger protections than free personal versions, but you should confirm with your organisation.</p>

<h3>What never to paste into a public tool</h3>
<ul>
<li><strong>Passwords, PINs, mobile money PINs, one-time codes and security answers.</strong></li>
<li><strong>Identity numbers</strong>, such as national ID, passport, bank account and card details.</li>
<li><strong>Personal data of others</strong> without a lawful reason: names with phone numbers, addresses, health, school records, photos of learners.</li>
<li><strong>Confidential work material</strong>: unpublished financial results, contracts, client lists, security plans, staff matters, tender documents.</li>
<li><strong>Sensitive personal information</strong>: health, religion, sexual life, political views and similar details, which need special care in data protection laws.</li>
<li><strong>Source code or secrets</strong> such as API keys.</li>
</ul>

<h3>Data protection law, in brief</h3>
<p>Many countries, including Uganda with its Data Protection and Privacy Act, have laws about handling personal data. Common ideas are: collect only what you need, use it for a clear purpose, keep it secure, do not share it without a lawful basis or consent, and respect people's rights over their data. Organisations are accountable for how staff handle it. If your work involves personal data, ask your organisation which tools are approved, and read the national authority's guidance. This course gives general awareness, not legal advice.</p>

<h3>How to protect information in a prompt</h3>
<ol>
<li><strong>Ask first:</strong> do I need to include this detail? Usually you do not.</li>
<li><strong>Anonymise.</strong> Replace names with labels like "Learner A", phone numbers with [PHONE], amounts with rounded numbers if the exact value is sensitive.</li>
<li><strong>Generalise.</strong> Instead of "Our client Mukasa Ltd owes us 45,000,000 UGX", write "A client owes us a large amount".</li>
<li><strong>Use fake examples</strong> to practise a prompt, then apply the result to the real case offline.</li>
<li><strong>Use approved tools only.</strong> If your workplace provides a specific tool, use it instead of a personal account.</li>
<li><strong>Review settings.</strong> Turn off chat history or training use where the option exists, and delete conversations you no longer need.</li>
</ol>

<h3>Before and after</h3>
<table>
<thead><tr><th>Risky prompt</th><th>Safer prompt</th></tr></thead>
<tbody>
<tr><td>Write a warning letter to Okello Peter, ID CM9001..., who was late 12 times and stole stock.</td><td>Write a general template for a warning letter about repeated lateness. Use [EMPLOYEE NAME] and [DATES].</td></tr>
<tr><td>Summarise this patient record for Nakato Sarah...</td><td>Do not use AI for patient records without an approved, secure system. Ask for a general summary template instead.</td></tr>
</tbody>
</table>

<h3>Shared devices and accounts</h3>
<p>If you use a shared computer or phone, log out and clear history. Use a strong, unique password and two-step verification on your AI account, because the chat history may contain sensitive material.</p>

<h3>Common mistakes</h3>
<ul>
<li>Pasting a full spreadsheet of customers because it is faster.</li>
<li>Using a personal AI account for official documents.</li>
<li>Believing that deleting a chat deletes everything everywhere.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Take a short paragraph of a real-looking work email that contains names, phone numbers and amounts (use a made-up one).</li>
<li>Rewrite it for an AI prompt using labels and placeholders only.</li>
<li>Find the privacy and data settings of your AI tool and note what they say about storage and training.</li>
<li>List five types of information you will never paste into it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Prompts are sent to external servers and may be stored; check the settings and terms.</li>
<li>Never paste passwords, IDs, financial secrets, confidential files or others' personal data.</li>
<li>Anonymise, generalise and use approved tools.</li>
<li>Data protection laws make organisations and staff accountable for personal data.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A colleague says "the AI wrote it, so the mistake is not mine." What is the correct view?
- They are right, the AI is responsible
* The person who sends or publishes the work is responsible for checking it
- Only the tool's company is responsible
- Nobody is responsible
= Responsibility stays with the user who chooses to send or publish the text.

Q: Which claim should you check first in an AI-written report?
- A friendly greeting
- The font size
* A statistic and a legal claim that would cause harm if wrong
- The length of the title
= Check the claims with the highest risk and impact first.

Q: Which of these are trustworthy checking sources? (choose all that apply)
* An official government or institution website that is current
* A recognised textbook or an expert you can ask
- An anonymous post with no evidence
* An established news organisation with named sources
= Reliable sources are accountable, current and show evidence.

Q: An AI tool with web search shows a link supporting its claim. What should you do?
- Assume it is correct because it has a link
* Open the page and check that it really says that
- Share the link without reading
- Delete the link
= Tools can cite poor sources or misread pages, so read the source yourself.

Q: What is a "swap test" for bias?
- Swapping your device
* Running the same prompt with a different name, gender or region and comparing the answers
- Swapping two AI tools
- Replacing your password
= If the answer changes in quality or tone, the tool may be treating groups differently.

Q: Which is a main reason AI can be biased?
- It is angry
* Training data and the world it comes from contain gaps and stereotypes
- It prefers short prompts
- It cannot read
= Models reflect patterns, including unfair ones, in the data they learn from.

Q: Which information should never be pasted into a public AI tool?
- A general question about how to write a polite refusal
* A mobile money PIN and a client's identity number
- A public news article
- A made-up example with fake names
= Secrets and identity numbers must be kept out of tools that send data to external servers.

Q: How do you make a prompt about a staff warning letter safer?
- Include the real name and ID so it is accurate
* Ask for a template with placeholders such as [EMPLOYEE NAME] and [DATES]
- Add the employee's phone number
- Paste the personnel file
= Templates and placeholders give the help you need without exposing personal data.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Rules, honesty and repeatable workflows',
            'summary' => 'Respect copyright, be open about AI use, follow policies, and build simple workflows and a prompt library you can reuse.',
            'lessons' => [
                [
                    'title'   => 'Copyright, originality and disclosure',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use AI-generated material honestly, respect other people's creative work and decide when and how to say that you used AI. This matters because plagiarism, copyright claims and loss of trust can damage your studies, your job and your business reputation.</p>

<h3>Copyright in simple terms</h3>
<p><strong>Copyright</strong> is the legal protection given to creators over original works such as writing, music, photographs, software and designs. Generally, you may not copy and publish someone else's protected work without permission or a legal exception. Laws differ from country to country, and the rules on AI are still developing, so this is general awareness, not legal advice.</p>

<h3>Questions about AI and copyright</h3>
<ul>
<li><strong>Who owns AI output?</strong> The answer depends on the country and on the tool's terms of use. In some places, work created entirely by a machine may not get copyright protection at all. Read the terms of the tool you use if you plan to sell or publish the results.</li>
<li><strong>Can the output copy existing work?</strong> Sometimes. A tool can produce text, code or images very similar to existing material. If you publish something that matches another person's work, you may be responsible.</li>
<li><strong>Can I paste other people's work into AI?</strong> Pasting a book chapter or a paid course into a tool to rewrite it may breach the rights of its owner or the terms of the material. Use only material you may share.</li>
<li><strong>Images and logos.</strong> Do not ask for images "in the style of" a named living artist to sell as your own, and do not use brand logos or real people's faces in misleading ways.</li>
</ul>

<h3>Plagiarism and academic honesty</h3>
<p><strong>Plagiarism</strong> is presenting someone else's words or ideas as your own. Submitting AI-written work as your own where it is not permitted counts as dishonesty in most schools and universities. It also harms you: the point of assignments is to build your skill. Some institutions allow limited use, such as brainstorming or checking grammar, if you say so. Always follow your teacher's rules.</p>

<h3>Disclosure: telling people you used AI</h3>
<p>Disclosure means being open about when AI contributed. Reasons to disclose:</p>
<ul>
<li>The rules of your school, employer, publisher or client require it.</li>
<li>The reader would reasonably want to know, for example in news, research, reviews or advice.</li>
<li>Trust: people feel misled if they discover hidden AI use.</li>
</ul>
<p>A simple statement is enough: "I used an AI assistant to help draft and edit this report. I checked the facts and take responsibility for the content." Describe how it was used: brainstorming, drafting, translation, editing. Never disclose less than the truth.</p>

<h3>Citing and crediting</h3>
<ul>
<li>When AI helps you find ideas, find and cite the real original sources for facts.</li>
<li>If you quote a person or a document, credit them. Do not quote what the AI claims they said without checking.</li>
<li>Follow your institution's style guide for mentioning AI use.</li>
</ul>

<h3>Honest use at work</h3>
<ul>
<li>Do not use AI to fake qualifications, work experience or testimonials.</li>
<li>Do not impersonate people with AI voices, messages or images.</li>
<li>Do not claim machine-written work as the result of hours you did not work if your client pays for your time and expects your own work. Agree openly on how AI may be used.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Find the terms of use or help page of the AI tool you use and note what it says about who may use the output.</li>
<li>Write a two-sentence disclosure for a report where AI helped with drafting and editing.</li>
<li>List three situations in your life (school, work, business) where disclosure would be required or wise.</li>
<li>Check one AI-generated paragraph by searching a distinctive phrase online to see if it matches existing text.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Copyright protects creators; read the terms and avoid copying others' work.</li>
<li>Submitting AI work as your own where forbidden is plagiarism.</li>
<li>Disclose AI use honestly when rules or readers expect it, and say how you used it.</li>
<li>Never use AI for fake credentials, reviews or impersonation.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'School and workplace AI policies',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to find and follow the AI rules of a school or workplace, and help write a short, sensible policy for a small organisation. This matters because rules differ between places, and breaking them, even by accident, can lead to marks lost, disciplinary action or a damaged business relationship.</p>

<h3>Why organisations make AI rules</h3>
<p>Organisations worry about confidentiality, accuracy, fairness, legal responsibility and fairness in exams. Some allow AI widely, some allow it only for certain tasks, and some ban it in specific settings. The rules may sit in a staff handbook, a student code of conduct, a course outline, or an email from management. If you cannot find a rule, ask. "Nobody told me" is a weak defence, and asking shows professionalism.</p>

<h3>Questions to ask about any rule</h3>
<ol>
<li>Which tools are approved, and which are not?</li>
<li>What tasks are allowed (brainstorming, editing, summarising, translating) and which are forbidden (exams, marking, client data, final submissions)?</li>
<li>What data may never go into a tool?</li>
<li>Must I disclose AI use, and how?</li>
<li>Who reviews the output before it goes out?</li>
<li>Who do I ask when I am unsure?</li>
</ol>

<h3>Typical rules you may meet</h3>
<table>
<thead><tr><th>Setting</th><th>Common rule</th></tr></thead>
<tbody>
<tr><td>Exams and tests</td><td>No AI at all unless the invigilator says so</td></tr>
<tr><td>Coursework</td><td>AI allowed for brainstorming or grammar if you disclose; not for writing the whole answer</td></tr>
<tr><td>Research projects</td><td>AI use must be described, and every source verified</td></tr>
<tr><td>Office work</td><td>Only approved tools, no client or staff data, a person reviews the final text</td></tr>
<tr><td>Customer communication</td><td>Human approval before sending; tell customers when they are talking to an automated assistant</td></tr>
<tr><td>Recruitment and marking</td><td>AI may help organise but humans decide</td></tr>
</tbody>
</table>

<h3>A short policy for a small organisation</h3>
<p>If your team has no policy, you could propose one page covering these points:</p>
<ol>
<li><strong>Purpose.</strong> AI may be used to save time on drafting, summarising and ideas.</li>
<li><strong>Approved tools.</strong> A named list, reviewed every six months.</li>
<li><strong>Protected information.</strong> A list of data that must never be entered (client data, staff records, passwords, financials, children's information).</li>
<li><strong>Human review.</strong> A named person checks all external communication and any numbers.</li>
<li><strong>Honesty.</strong> Staff do not claim AI work as human expertise where it matters, and disclose where required.</li>
<li><strong>No high-risk decisions.</strong> No AI-only decisions about hiring, pay, discipline or services.</li>
<li><strong>Training and reporting.</strong> Staff learn the basics, and report mistakes or leaks quickly and without blame.</li>
</ol>

<h3>If something goes wrong</h3>
<p>If you paste something you should not, or send an AI error to a client, act quickly. Tell your supervisor, do not hide it, delete the chat if the tool allows it, and follow your organisation's process. Quick honesty limits the damage and builds trust. Learn from it and update the rules.</p>

<h3>Keep up to date</h3>
<p>Tools and rules change often. Revisit your organisation's guidance regularly, follow guidance from education ministries, regulators and professional bodies, and review your own habits every few months.</p>

<h3>Try it yourself</h3>
<ol>
<li>Find the AI or technology rules for your school, workplace or an organisation you belong to. If none exist, write down who you would ask.</li>
<li>Answer the six questions above for that setting.</li>
<li>Draft the seven-point policy for a small organisation you know, in one page.</li>
<li>Share it with a trusted person for feedback.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Find out the rules before using AI, and ask when unsure.</li>
<li>Rules often cover approved tools, protected data, disclosure and human review.</li>
<li>A small organisation can start with a simple one-page policy.</li>
<li>If something goes wrong, report it quickly and honestly.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Building repeatable workflows and a prompt library',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to turn one-off AI successes into a repeatable workflow with a saved prompt library and a review checklist. This matters because the real gain in productivity comes when a task that takes an hour is reduced, reliably, to ten minutes, every time, by you or by your whole team.</p>

<h3>From one good result to a routine</h3>
<p>When a prompt works well, do not lose it. Convert it into a reusable <strong>template</strong> with fixed instructions and clear spaces for the details that change. A workflow is the full routine: what you gather, what you prompt, what you check, and where you save the result.</p>

<h3>Designing a workflow</h3>
<ol>
<li><strong>Pick a repeated task.</strong> Choose one you do weekly or monthly, such as a customer update, meeting minutes, a social post or a progress report.</li>
<li><strong>Write the steps by hand first.</strong> What inputs do you need? What decisions are yours? What would a good result look like?</li>
<li><strong>Decide which steps AI may do</strong>, using the traffic-light test from Module 1, and which stay with you.</li>
<li><strong>Write the prompt template</strong> with placeholders.</li>
<li><strong>Add a check step</strong> with a short checklist.</li>
<li><strong>Test it three times</strong> on different real examples and improve the template.</li>
<li><strong>Save it</strong> with a name and date, and share it with the team.</li>
</ol>

<h3>A prompt template example</h3>
<pre><code>Name: Weekly customer update
Role: You are a friendly customer-care officer for [BUSINESS].
Context: This week we [KEY NEWS]. Our customers are [WHO].
Task: Write a WhatsApp update message.
Format: Under 80 words, simple English, one call to action:
[ACTION]. Do not invent offers, prices or dates. Use [CHECK]
if you are unsure about a detail.</code></pre>
<p>Each time, you fill the capital-letter placeholders with true information.</p>

<h3>A review checklist</h3>
<ul>
<li>Are all names, numbers, dates and prices correct?</li>
<li>Are there any claims I cannot prove?</li>
<li>Is any confidential or personal data included?</li>
<li>Is the tone right for this reader?</li>
<li>Have all placeholders been replaced?</li>
<li>Does it need disclosure under our rules?</li>
</ul>

<h3>Your prompt library</h3>
<p>Keep a simple document or spreadsheet where each row has: task name, template, when to use it, what to check, who last tested it and the date. Review it every few months, because tools change and a prompt that worked may need updating. Remove outdated entries. Never store passwords or private data in the library.</p>
<table>
<thead><tr><th>Task</th><th>Template saved as</th><th>Check</th><th>Time before and after</th></tr></thead>
<tbody>
<tr><td>Meeting minutes</td><td>Minutes v2</td><td>Owners and dates</td><td>40 min to 15 min</td></tr>
<tr><td>Product description</td><td>Product text v1</td><td>No unproven claims</td><td>20 min to 8 min</td></tr>
</tbody>
</table>

<h3>Measure and reflect</h3>
<p>Track how long a task takes with and without AI, including checking time, and how many corrections you needed. If the saving is small or errors are frequent, redesign the workflow. Reflect on what you still need to learn yourself, so you build skills and not only dependence.</p>

<h3>Keep human judgement central</h3>
<p>A workflow does not remove responsibility. Keep a person accountable for each output, keep a record of what was approved, and review your workflows when rules, tools or business needs change.</p>

<h3>Try it yourself</h3>
<ol>
<li>Choose one repeated task and write its steps by hand.</li>
<li>Create a prompt template with placeholders and a six-point checklist.</li>
<li>Test it on three real or realistic examples.</li>
<li>Record the time saved, including checking.</li>
<li>Save it in a prompt library with a date and a note on what to check.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Turn successful prompts into templates with placeholders.</li>
<li>A workflow defines inputs, AI steps, human checks and where results are saved.</li>
<li>A review checklist protects accuracy, privacy and tone.</li>
<li>Maintain the prompt library, measure the time saved and keep people accountable.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Who owns the text an AI tool produces for you?
- You always own it fully, everywhere
* It depends on the country's law and the tool's terms, so check both before selling or publishing
- The tool always owns it
- Nobody ever owns anything
= Rules on AI-generated output vary and are still developing, so read the terms and the law.

Q: What is plagiarism in the context of AI?
- Using a spell checker
* Presenting AI-written or copied work as your own where that is not allowed
- Reading AI output
- Writing in a second language
= Passing off work you did not create as yours, against the rules, is dishonest.

Q: Which is a good disclosure statement?
- No statement is ever needed
* I used an AI assistant to help draft and edit this report; I checked the facts and take responsibility for the content
- AI wrote everything and I do not know what is in it
- The report is completely my own work (when AI wrote part of it)
= A clear, truthful statement says how AI was used and who takes responsibility.

Q: Which are typical elements of an organisation's AI policy? (choose all that apply)
* A list of approved tools
* A list of information that must never be entered
* Human review of outside communication
- Allowing AI to decide hiring alone
= Good policies protect data and keep humans accountable, especially for decisions about people.

Q: You cannot find any AI rule for your workplace. What should you do?
- Assume everything is allowed
* Ask your supervisor and avoid using confidential data until you know
- Use a personal account for client documents
- Ignore the question
= Asking shows professionalism and prevents accidental breaches.

Q: You accidentally pasted client information into a public AI tool. What is the best action?
- Say nothing and hope for the best
* Tell your supervisor quickly, delete the chat if possible, and follow the organisation's process
- Blame the tool
- Paste more data to correct it
= Prompt, honest reporting limits harm and builds trust.

Q: What belongs in a prompt template saved for repeated use?
- A real customer's password
* Fixed instructions with placeholders for details that change, and a note on what to check
- Only the word "Hello"
- Private staff records
= Templates are reusable because the changing details are placeholders, and secrets must never be stored.

Q: Why measure the time saved including checking?
- Because checking is not important
* To see whether the workflow really saves time and where it should be improved
- To prove that AI is always faster
- To avoid having a prompt library
= The honest time includes review. If errors or corrections are frequent, the workflow needs redesign.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which best describes how a large language model generates text?
- It retrieves a stored page that matches your question
* It predicts the next piece of text from patterns learned during training
- It asks a human expert in real time
- It copies the first search result
= Language models produce likely text from learned patterns, not verified lookups.

Q: Why can an AI answer be fluent and still wrong?
- Because it is always lying on purpose
* Because it is built to produce plausible text, not to check truth
- Because fluent text is always wrong
- Because it has no training
= Plausibility is not accuracy, which is why checking matters.

Q: Which is an example of a hallucination?
- A correct summary of text you pasted
* A reference to a research paper that does not exist, with a believable author and year
- A slow response
- A refusal to answer
= Invented but convincing details are hallucinations.

Q: Which task is the best fit for green on the traffic-light guide?
- Deciding a patient's treatment
* Rewording your own paragraph for clarity
- Choosing which job applicant to hire with no human review
- Handling client ID numbers
= Rewriting your own text is low risk and easy to check.

Q: Which are the first four questions in the decision test for choosing tasks? (choose all that apply)
* What happens if the output is wrong?
* Can I check the result quickly?
* Does it involve private or confidential information?
- What colour is the tool's logo?
= The test weighs the cost of error, ease of checking, privacy and responsibility.

Q: Which prompt part tells the tool who it should act as?
* Role
- Format
- Example
- Delimiter
= The role sets the viewpoint and vocabulary, such as "experienced office administrator".

Q: What is the benefit of adding "If you are not sure, say so instead of guessing" to a prompt?
- It makes answers longer
* It gives permission to admit uncertainty and can reduce invented answers
- It deletes the previous chat
- It removes the need to check
= It may reduce guessing, but you still need to verify important facts.

Q: Why give the tool the text and say "Use only the text below"?
- To save electricity
* To ground the answer in your material rather than the tool's memory
- To hide the text from other users
- To make the answer rhyme
= Providing material and limiting the answer to it lowers the chance of invented facts.

Q: What is few-shot prompting?
- Asking one short question
* Giving a few examples of the pattern you want the tool to follow
- Using the tool a few times a day
- Ending the chat quickly
= Examples teach format and style effectively.

Q: Your first draft from the tool is too general. Which follow-up is best?
- Make it better
* Add two concrete examples from a small shop in Kampala and cut it to 100 words
- Start a new chat and write nothing
- Delete it
= Specific instructions steer the result.

Q: Which technique splits a big task into stages where each result feeds the next prompt?
* Prompt chaining
- Prompt hiding
- Prompt copying
- Prompt deleting
= Prompt chaining breaks complex work into checkable steps.

Q: What is the main risk of treating a step-by-step explanation from AI as proof?
- It is always too short
* It can look logical and still be wrong
- It cannot be printed
- It uses data
= Reasoning written by the tool is something to review, not a guarantee.

Q: Before sending an AI-drafted email, which checks are essential? (choose all that apply)
* Names, dates and amounts are correct
* Placeholders are replaced
* The tone suits the reader
- You added more exclamation marks
= Always review facts, completeness and tone before sending.

Q: When summarising a long report for an important decision, what should you do?
- Trust the summary only
* Check key numbers against the original and read the key sections yourself
- Ask the tool to say it is correct
- Skip the decision
= A summary can omit or alter details, so verify against the source.

Q: Why should arithmetic from a language model be verified?
* Language models are not calculators and can make arithmetic errors
- Numbers are illegal in prompts
- Spreadsheets cannot add
- It uses only roman numerals
= Calculate with trusted tools and spot-check results.

Q: A translation of a health notice will be shared with a community. What is the best practice?
- Publish it straight away
* Back-translate it and have a fluent speaker review it first
- Remove all numbers
- Translate it twice and publish the shorter one
= Important translations need human review, especially for health and safety messages.

Q: Which is the best way to check a claim in AI output?
- Ask the same AI if it is correct
* Compare with a current, trustworthy source such as an official website
- Check only if it feels wrong
- Check only the first sentence
= Independent, reliable sources are the real test.

Q: What does the "swap test" help reveal?
- Spelling mistakes
* Whether outputs differ unfairly when only a name, gender or region changes
- The speed of the tool
- Copyright ownership
= Differences in quality or tone for similar prompts can indicate bias.

Q: Which decision should not be left to AI alone?
- A choice of font colour
* Hiring, marks, discipline or access to services affecting people
- A list of party games
- A rhyme for a poster
= Decisions about people need accountable human judgement.

Q: Which of the following should never be typed into a public AI tool?
- A general question about writing a polite complaint
* A mobile money PIN and a customer's ID number
- A public news article
- An invented example with fake names
= Secrets and identity numbers must stay out of tools that send data elsewhere.

Q: What is the safest way to get help with a staff warning letter?
- Paste the personnel file
* Ask for a template using [EMPLOYEE NAME] and [DATES] placeholders
- Add the employee's phone number
- Include their ID
= Templates avoid sharing personal data.

Q: Which statement about copyright and AI is most accurate?
- AI output is always free of any problem
* Ownership and risk depend on the law and the tool's terms, and output might resemble existing work
- Copyright does not exist online
- You may copy any text if an AI rewrote it
= Check the terms and law, and avoid publishing material that copies others.

Q: A student submits AI-written coursework as their own against the school's rules. This is:
- Allowed everywhere
* Academic dishonesty, a form of plagiarism
- A spelling error
- A formatting choice
= Passing off work against the rules is dishonest, and it also undermines learning.

Q: Which is a good reason to disclose AI use?
* The rules or readers expect it, and honesty builds trust
- It makes the work longer
- It proves the work is perfect
- It is never useful
= Disclosure supports trust and compliance with rules.

Q: What should you do if you cannot find your workplace's AI rule?
- Assume anything is allowed
* Ask your supervisor and avoid confidential data until you know
- Use a personal account for client work
- Ignore it
= Asking prevents accidental breaches.

Q: What belongs in a reusable prompt library entry? (choose all that apply)
* Template with placeholders
* What to check before using the result
* Date last tested
- Customers' passwords
= A library stores reusable instructions and checks, never secrets or private data.
QUIZ,
];
