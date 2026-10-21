<?php
return [
    'slug'        => 'ai-tools-literacy',
    'title'       => 'Practical AI Tools: Using AI Assistants Wisely',
    'category'    => 'ai',
    'level'       => 'beginner',
    'icon'        => 'star',
    'sort'        => 150,
    'summary'     => 'Learn what AI is, how to write clear prompts, and how to use AI assistants for study, work and creative projects safely, honestly and with good judgement.',
    'description' => <<<'HTML'
<p>Artificial intelligence (AI) tools can now write, explain, translate, summarise and draw in seconds. Used well, they are like a patient helper who is available at any hour. Used badly, they can spread mistakes, expose private information or tempt people into cheating.</p>
<p>This course is for complete beginners. You will learn in plain words what AI is and is not, how to talk to an AI assistant so that it gives useful answers, and how to check what it tells you. You will practise with real tasks: studying, writing a CV, planning a small business, making a poster caption and more.</p>
<p>We keep tool names general (for example AI chat assistants such as ChatGPT, Gemini or Copilot) because products and features change often. The skills you learn here work with any of them.</p>
HTML,
    'outcomes'    => [
        'Explain in simple words what AI, machine learning and generative AI are',
        'Describe the main strengths and limits of AI chat assistants, including hallucinations and bias',
        'Write clear prompts using role, task, context, format and examples',
        'Improve an answer by asking follow-up questions and checking the facts',
        'Use AI to study, draft emails, prepare a CV and plan work without cheating',
        'Protect your privacy by knowing what never to paste into an AI tool',
        'Recognise AI-made images, voices and deepfakes and think about copyright',
        'Write a short personal AI-use policy that keeps you honest and safe',
    ],
    'requirements' => [
        'A smartphone, tablet or computer with internet access',
        'No experience needed; you should be able to browse the web and type',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Elements of AI (free introductory course)', 'url' => 'https://www.elementsofai.com'],
        ['title' => 'UNESCO: Artificial intelligence', 'url' => 'https://www.unesco.org/en/artificial-intelligence'],
        ['title' => 'ChatGPT', 'url' => 'https://chatgpt.com'],
        ['title' => 'Google Gemini', 'url' => 'https://gemini.google.com'],
        ['title' => 'Microsoft Copilot', 'url' => 'https://copilot.microsoft.com'],
    ],
    'modules' => [

        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: What AI is (and is not)',
            'summary' => 'Understand AI, machine learning and generative AI in plain words, and learn what these tools do well and badly.',
            'lessons' => [
                [
                    'title'   => 'Artificial intelligence in plain words',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what AI is to a friend in your own words and name three places where you already meet it. This matters because you cannot use a tool wisely until you know what it really is.</p>
<h3>What is artificial intelligence?</h3>
<p><strong>Artificial intelligence (AI)</strong> means computer programs that do tasks which normally need human thinking, such as understanding speech, recognising a face in a photo, translating a sentence or suggesting what to watch next. The word "artificial" means made by people. The word "intelligence" is used loosely: the computer does not think or feel like a person. It follows patterns it has learned from data.</p>
<h3>Three words you will hear often</h3>
<table>
<thead><tr><th>Term</th><th>Simple meaning</th><th>Example</th></tr></thead>
<tbody>
<tr><td>AI</td><td>The big idea: machines doing "smart" tasks</td><td>A phone that understands your voice</td></tr>
<tr><td>Machine learning</td><td>A way of building AI where the computer learns patterns from many examples instead of being given fixed rules</td><td>A spam filter that learns which emails are junk</td></tr>
<tr><td>Generative AI</td><td>AI that creates new text, pictures, sound or code</td><td>A chat assistant that writes a letter for you</td></tr>
</tbody>
</table>
<p>Think of it like this: AI is the whole family, machine learning is one big branch of the family, and generative AI is the newest and most talked-about child.</p>
<h3>How does a machine "learn"?</h3>
<p>Imagine teaching a child to know a mango. You do not list rules. You show many mangoes, and the child slowly sees what they have in common. Machine learning is similar. Engineers give a program thousands or millions of examples. The program finds patterns and uses them to guess about new things it has never seen.</p>
<h3>AI in your everyday life</h3>
<ul>
<li><strong>Phone assistants</strong> that set alarms when you speak.</li>
<li><strong>Translation apps</strong> that turn Luganda, Swahili or English text into another language.</li>
<li><strong>Recommendations</strong> on video and music apps ("you may also like").</li>
<li><strong>Maps</strong> that predict traffic and suggest the fastest route.</li>
<li><strong>Mobile money and bank systems</strong> that flag unusual transactions as possible fraud.</li>
<li><strong>Phone cameras</strong> that blur the background or find faces.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Look at your phone for ten minutes and write down every feature you think uses AI (voice typing, camera, keyboard suggestions, maps).</li>
<li>Open a translation app and translate a short sentence from English into another language. Translate it back. Is it the same?</li>
<li>Open a video or music app and look at the "recommended" list. Write one sentence on why you think it chose those items.</li>
<li>Explain AI in two sentences to someone at home. Ask them if it made sense.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>AI means computers doing tasks that usually need human thinking.</li>
<li>Machine learning means learning patterns from many examples.</li>
<li>Generative AI creates new text, images, audio or code.</li>
<li>You already use AI daily on your phone, maps and apps.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'How AI chat assistants work',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>In this lesson you will understand, at a simple level, how a chat assistant produces its answers. You do not need to be an engineer. Knowing the basic idea helps you trust the tool the right amount, not too much and not too little.</p>
<h3>A very good guesser of the next word</h3>
<p>An AI chat assistant is built on a <strong>language model</strong>. A language model is a program trained on a huge amount of written text from books, websites and other sources. During training it learned which words and ideas tend to follow each other. When you type a question, it builds its answer one small piece of text at a time, each time predicting what fits best next.</p>
<p>Think of how your phone keyboard suggests the next word as you type. A chat assistant is like that suggestion feature, but vastly more powerful, so the sentences it writes are long, polite and often very useful.</p>
<h3>What this means for you</h3>
<ul>
<li>The assistant does not "look up" the truth like a library catalogue. It generates likely-sounding text.</li>
<li>It does not understand like a human. It can sound confident even when it is wrong.</li>
<li>Its knowledge has a cut-off date. It may not know recent news, unless the tool is connected to a search feature.</li>
<li>Asking the same question twice can give different answers, because there is some randomness in the process.</li>
</ul>
<h3>Parts of a conversation</h3>
<table>
<thead><tr><th>Word</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>Prompt</td><td>What you type to the assistant: your question or instruction</td></tr>
<tr><td>Response</td><td>The answer the assistant writes</td></tr>
<tr><td>Chat or conversation</td><td>A series of prompts and responses; the assistant can use earlier messages as context</td></tr>
<tr><td>Token</td><td>A small piece of text (part of a word) that the model reads and writes; long chats use many tokens</td></tr>
</tbody>
</table>
<h3>Where to find AI assistants</h3>
<p>Well-known examples include ChatGPT, Gemini and Copilot. Many are free for basic use, usually with limits, and some offer paid plans. Features, limits and names change often, so check the tool itself for what it can do today. Some phone apps and websites also build an assistant inside them, such as inside a writing app or search engine.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open one AI chat assistant in your browser or as a phone app. Sign in only if you are comfortable with its terms.</li>
<li>Type: <code>Explain what a language model is, using an example from a Ugandan market.</code></li>
<li>Press Enter and read the answer. Then ask exactly the same question again.</li>
<li>Compare the two answers. What is the same? What changed? Write one sentence about what this shows.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A chat assistant predicts useful text based on patterns learned from large amounts of writing.</li>
<li>It does not know that something is true; it produces what sounds right.</li>
<li>The same prompt can give different answers each time.</li>
<li>Product names and features change, but the basic idea stays the same.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Strengths and limits: hallucinations and bias',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will know when an AI assistant is a good helper and when you must be careful. The two biggest problems to understand are hallucinations and bias. Learning to spot them will save you from embarrassing and sometimes harmful mistakes.</p>
<h3>What AI assistants do well</h3>
<ul>
<li>Explaining a difficult idea in simpler words.</li>
<li>Drafting first versions of emails, letters and reports.</li>
<li>Summarising long text and rewriting it in a different tone.</li>
<li>Brainstorming names, ideas and lists.</li>
<li>Translating and fixing grammar.</li>
<li>Working fast: a task that takes you an hour may take a minute to start.</li>
</ul>
<h3>Hallucinations: confident mistakes</h3>
<p>A <strong>hallucination</strong> happens when an AI invents something that is not true but states it as fact. It might make up a book that does not exist, give a wrong date, create a fake web link or quote a law incorrectly. It does this because it is built to produce believable text, not to check facts.</p>
<p>Warning signs: very specific numbers or names with no source, links you cannot open, answers that agree with whatever you suggest, and "expert" tone with no evidence.</p>
<h3>Bias: unfair patterns</h3>
<p><strong>Bias</strong> means the AI's answers lean unfairly toward some people or views. This happens because the training text came from the real world, which contains stereotypes, and because some languages, places and groups appear in the data much more than others. For example, an assistant may know a lot about big Western cities and much less about Gulu or a village in northern Uganda. Ask it to describe a "doctor" or a "farmer" and watch whether it assumes gender or race.</p>
<h3>Other limits</h3>
<ul>
<li><strong>Old information:</strong> it may not know recent events, prices or exam rules.</li>
<li><strong>Weak maths and logic:</strong> it can make arithmetic mistakes, so check calculations with a calculator.</li>
<li><strong>No real-life experience:</strong> it has never run a shop, felt hunger or visited your school.</li>
<li><strong>Local knowledge:</strong> answers about Uganda or local languages can be thin or wrong.</li>
</ul>
<h3>A simple rule: trust, then verify</h3>
<p>Use the answer as a starting point. For anything important such as health, law, money, school facts or safety, confirm with a trusted source: an official website, a teacher, a doctor or a textbook.</p>
<h3>Try it yourself</h3>
<ol>
<li>Ask an AI assistant: <code>Give me three facts about Gulu and tell me how sure you are about each.</code></li>
<li>Check each fact on a reliable website or ask someone from the area.</li>
<li>Ask it: <code>What is 17 times 23, then add 489?</code> and check the result with a calculator.</li>
<li>Write down which answers were correct, partly correct or wrong.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>AI is excellent at drafting, explaining and summarising, but can state false things confidently (hallucinations).</li>
<li>Bias comes from unfair patterns in training data.</li>
<li>Always verify important facts with a trusted source.</li>
<li>Check any maths yourself.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Types of AI tools you can meet',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>AI is not only chat. After this lesson you will be able to tell the main kinds of AI tools apart and choose the right kind for a task. This helps you avoid wasting time with the wrong tool.</p>
<h3>The main kinds of AI tools</h3>
<table>
<thead><tr><th>Kind</th><th>What it does</th><th>Example use</th></tr></thead>
<tbody>
<tr><td>Chat assistants</td><td>Answer questions and write text in a conversation</td><td>Explain photosynthesis; draft an email</td></tr>
<tr><td>Image generators</td><td>Create pictures from a text description</td><td>A poster background for a youth event</td></tr>
<tr><td>Voice and speech tools</td><td>Turn speech into text, text into speech, or clean audio</td><td>Typing meeting notes by speaking</td></tr>
<tr><td>Translation tools</td><td>Convert text between languages</td><td>Read a document written in French</td></tr>
<tr><td>Writing helpers inside apps</td><td>Suggest improvements in documents, email and slides</td><td>Shortening a long paragraph in a word processor</td></tr>
<tr><td>Search assistants</td><td>Search the web and summarise findings, often with links</td><td>Compare phone prices from several sources</td></tr>
<tr><td>Code assistants</td><td>Suggest or explain program code</td><td>Explaining an error message</td></tr>
</tbody>
</table>
<h3>Free versus paid</h3>
<p>Many tools have a free version with limits, such as fewer messages per day or simpler features, and a paid version with more. As a learner you can do a great deal with free versions. Do not pay for anything until you are sure you need it, and never share bank or mobile money PINs with any website or app.</p>
<h3>Choosing the right tool</h3>
<ol>
<li>Decide what you want as a result: text, a picture, a translation, a summary.</li>
<li>Pick the kind of tool made for that result.</li>
<li>Read the privacy information before you upload anything.</li>
<li>Start with a small test before using it for something important.</li>
</ol>
<h3>Data costs and connectivity</h3>
<p>AI tools need the internet, and images and voice can use a lot of mobile data. Use Wi-Fi where you can, and try text tools first if your data bundle is limited. Many apps also have a "lite" or data-saver mode.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write the five tasks you do most on a computer or phone (for example, writing assignments, sending messages, making posters).</li>
<li>Next to each task, write which kind of AI tool from the table could help.</li>
<li>Choose one and try it for ten minutes. Write down what worked and what did not.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>There are many kinds of AI tools: chat, image, voice, translation, search and more.</li>
<li>Match the tool to the task.</li>
<li>Free versions are enough for learning; watch your data use.</li>
<li>Read the privacy information before uploading anything.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which statement best describes machine learning?
- A computer that thinks and feels like a person
* A way for computers to learn patterns from many examples instead of following only fixed rules
- A program that only works without the internet
- A type of computer screen
= Machine learning means the computer finds patterns in examples, such as a spam filter learning from many emails.

Q: A chat assistant writes a very confident answer containing a book title that does not exist. This is called:
- Encryption
- A software update
* A hallucination
- A virus
= A hallucination is invented information stated as if it were true, which is why you must verify important facts.

Q: Which of these are examples of generative AI? (choose all that apply)
* A tool that writes a letter from your short instructions
* A tool that creates a picture from a text description
- A calculator adding two numbers
- A torch
= Generative AI creates new content such as text and images. A basic calculator follows fixed rules and creates nothing new.

Q: Why might an AI assistant give weaker answers about northern Uganda than about a large Western city?
- It refuses to talk about Africa
* The text it learned from contains less information about some places and groups
- Uganda has no internet
- AI only works in big cities
= Uneven training data is one source of bias: some places and languages appear far less than others.

Q: What is the safest approach to an AI answer about health, law or money?
- Accept it because the tone is professional
- Share it with friends immediately
* Use it as a starting point and confirm with a trusted source or professional
- Ask the same AI to confirm itself and stop there
= For important topics, always check with a doctor, official website, lawyer or teacher.

Q: You ask the same question twice and get two slightly different answers. What does this show?
- The tool is broken
* Responses are generated with some randomness, so wording and details can vary
- Someone else is using your account
- AI always lies
= Generated text is not fixed; this is one more reason to review and verify what you receive.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Using AI assistants well',
            'summary' => 'Write clear prompts, improve answers step by step, use AI to learn, and know what never to share.',
            'lessons' => [
                [
                    'title'   => 'Writing clear prompts',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a prompt that gets a useful answer on the first or second try. A prompt is only the message you type, but small changes make a big difference, just like asking a shopkeeper clearly for exactly what you need.</p>
<h3>The five parts of a strong prompt</h3>
<table>
<thead><tr><th>Part</th><th>Question it answers</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Role</td><td>Who should the AI act as?</td><td>"You are a friendly careers teacher."</td></tr>
<tr><td>Task</td><td>What exactly must it do?</td><td>"Write a short email asking for an internship."</td></tr>
<tr><td>Context</td><td>What does it need to know?</td><td>"I am a final-year Business student in Gulu with no work experience."</td></tr>
<tr><td>Format</td><td>How should the answer look?</td><td>"Use 120 words, polite tone, three short paragraphs."</td></tr>
<tr><td>Examples</td><td>What does a good answer look like?</td><td>"Start like this: Dear Hiring Manager, ..."</td></tr>
</tbody>
</table>
<p>You do not always need all five, but the more clearly you describe what you want, the less the AI has to guess.</p>
<h3>Weak versus strong</h3>
<p><strong>Weak:</strong> <code>Write about farming.</code></p>
<p><strong>Strong:</strong> <code>You are an agriculture extension officer. Write a one-page guide for small-scale farmers in northern Uganda on how to store maize after harvest. Use simple English, short sentences and a bullet list of five tips.</code></p>
<h3>Helpful habits</h3>
<ol>
<li><strong>One main task per prompt.</strong> Break big jobs into steps.</li>
<li><strong>State your audience:</strong> "for a 12-year-old", "for a village meeting", "for my boss".</li>
<li><strong>Set limits:</strong> number of words, number of points, reading level.</li>
<li><strong>Say what to avoid:</strong> "Do not use technical words."</li>
<li><strong>Ask the AI to ask you questions</strong> if it needs more information: "Ask me up to three questions before you answer."</li>
</ol>
<h3>Common mistakes</h3>
<ul>
<li>Being too short and vague.</li>
<li>Asking for several unrelated things at once.</li>
<li>Forgetting to say who the reader is.</li>
<li>Accepting the first answer without reading it carefully.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Choose a real task, such as a message to a landlord, a short speech or a study plan.</li>
<li>First type a very short prompt and save the answer.</li>
<li>Now write a second prompt using role, task, context and format.</li>
<li>Compare both answers and write one sentence on what improved.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A good prompt includes role, task, context, format and, when helpful, an example.</li>
<li>State your audience and limits.</li>
<li>Clear instructions save time and give better answers.</li>
<li>Always read the result critically.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Iterating: improving answers step by step',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>The first answer from an AI is a draft, not the finished product. In this lesson you will learn to improve answers through follow-up messages, and to ask for steps, tables and summaries. This is the skill that separates casual users from confident ones.</p>
<h3>Treat the chat as a conversation</h3>
<p>The assistant remembers what was said earlier in the same chat, so you can refine its work instead of starting over. Useful follow-up messages include:</p>
<ul>
<li><code>Make it shorter, about 100 words.</code></li>
<li><code>Use simpler words. I am a beginner.</code></li>
<li><code>Make the tone more formal.</code></li>
<li><code>Give me two more options.</code></li>
<li><code>You missed the point about cost. Add it.</code></li>
<li><code>Explain step 3 in more detail.</code></li>
</ul>
<h3>Ask for the shape you need</h3>
<table>
<thead><tr><th>You want</th><th>Say</th></tr></thead>
<tbody>
<tr><td>Steps</td><td>"Give me numbered steps for setting up an email account."</td></tr>
<tr><td>A table</td><td>"Compare three types of phone in a table with price range, battery and camera."</td></tr>
<tr><td>A summary</td><td>"Summarise this in five bullet points."</td></tr>
<tr><td>A checklist</td><td>"Make a checklist I can tick off before an interview."</td></tr>
<tr><td>A simple version</td><td>"Explain this like I am a complete beginner."</td></tr>
</tbody>
</table>
<h3>When the answer is wrong</h3>
<ol>
<li>Say clearly what is wrong: <code>The date you gave is incorrect. Please check again.</code></li>
<li>Give the correct information if you know it.</li>
<li>Ask: <code>What are you unsure about in your answer?</code></li>
<li>If the chat has become confused, start a new chat and write a fresh, clear prompt.</li>
</ol>
<h3>Checking facts</h3>
<p>Make a habit of asking: <code>Which parts of this should I double-check?</code> Then check them. If the tool gives sources, open them and make sure they exist and really say what the AI claims. Do not trust a link or reference that you cannot find.</p>
<h3>Try it yourself</h3>
<ol>
<li>Ask: <code>Write a 150-word announcement for a community clean-up on Saturday morning.</code></li>
<li>Follow up three times: make it shorter, make it friendlier, then add a line asking people to bring gloves.</li>
<li>Ask for the final text as a table with columns for what, when, where and who to contact. Invent suitable details.</li>
<li>Write down which follow-up changed the answer the most.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Treat the first answer as a draft and improve it with follow-up messages.</li>
<li>Ask for the format you need: steps, tables, summaries, checklists.</li>
<li>Tell the AI exactly what is wrong when it makes mistakes.</li>
<li>Verify facts and sources yourself.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Using AI to learn',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>An AI assistant can be a tireless tutor: it never gets tired of "why?" questions. After this lesson you will be able to use AI to understand difficult topics, make practice questions and test yourself, while still doing the thinking yourself.</p>
<h3>Ask for explanations at your level</h3>
<p>If a textbook paragraph is confusing, paste a short piece of it (not a full copyrighted book) or name the topic and ask:</p>
<pre>Explain how a bank loan interest works like I am a complete beginner. Use an example in Uganda shillings, then ask me one question to check I understand.</pre>
<p>Useful phrases: "explain like I am a beginner", "give an everyday example", "use an analogy", "explain it step by step", "what are the common mistakes?".</p>
<h3>Build a study routine with AI</h3>
<ol>
<li><strong>Learn:</strong> read your class notes first, then ask the AI to explain anything unclear.</li>
<li><strong>Summarise:</strong> write your own summary, then ask the AI to point out what you missed.</li>
<li><strong>Practise:</strong> ask for 10 practice questions on the topic, without answers.</li>
<li><strong>Test yourself:</strong> answer in writing, then ask the AI to mark and explain your mistakes.</li>
<li><strong>Repeat:</strong> a week later ask for a quick quiz again. Spaced practice helps memory.</li>
</ol>
<h3>Example prompts for learners</h3>
<ul>
<li><code>Create a 5-question multiple-choice quiz on the water cycle for Senior 2. Do not show answers until I reply.</code></li>
<li><code>Be my language partner. We will chat in simple English and you will gently correct my mistakes.</code></li>
<li><code>Make a one-week study timetable for me. I study after 5 pm and have a maths exam in 10 days.</code></li>
<li><code>Explain my mistake in this solution, but do not give me the final answer.</code></li>
</ul>
<h3>Learning, not copying</h3>
<p>If you let the AI do all your homework, you will pass the task but fail the exam, because the learning never happened. Use AI to understand, not to replace your thinking. A good test: could you explain the answer to someone else without the AI?</p>
<h3>Check against your syllabus</h3>
<p>AI may teach methods or facts that differ from your school's syllabus or national exam. Always compare with your textbook and ask your teacher when in doubt.</p>
<h3>Try it yourself</h3>
<ol>
<li>Pick one topic you find difficult in your studies or work.</li>
<li>Ask the AI to explain it as if you were a beginner, with a local example.</li>
<li>Ask for five practice questions and answer them on paper.</li>
<li>Paste your answers in and ask for feedback. Note which ones you got wrong and why.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>AI can explain, quiz and give feedback at your own pace.</li>
<li>Do the thinking and writing yourself, then ask for feedback.</li>
<li>Compare with your textbooks and teachers.</li>
<li>Practise again after a few days to remember better.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Writing help without cheating, and protecting your privacy',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn where help ends and cheating begins, and what you must never paste into an AI tool. These two ideas, honesty and privacy, protect your reputation and your safety.</p>
<h3>Help versus cheating</h3>
<table>
<thead><tr><th>Usually acceptable (if allowed by your teacher or employer)</th><th>Usually cheating</th></tr></thead>
<tbody>
<tr><td>Asking the AI to explain a topic</td><td>Submitting an AI-written essay as your own work</td></tr>
<tr><td>Checking grammar and spelling in your draft</td><td>Using AI during a test where it is not allowed</td></tr>
<tr><td>Brainstorming ideas, then writing yourself</td><td>Copying an AI answer without understanding it</td></tr>
<tr><td>Asking for feedback on your own writing</td><td>Pretending AI work is the result of your research</td></tr>
</tbody>
</table>
<p>The rules differ between schools, universities and workplaces. When in doubt, ask. If AI is allowed, it is honest to say how you used it, for example: "I used an AI assistant to check my grammar."</p>
<h3>Write first, polish after</h3>
<ol>
<li>Write your own draft, even if it is rough.</li>
<li>Ask: <code>Point out unclear sentences and grammar mistakes, but do not rewrite the whole text.</code></li>
<li>Decide which suggestions to accept.</li>
<li>Read the final version aloud to make sure it still sounds like you.</li>
</ol>
<h3>What never to paste into an AI tool</h3>
<p>Many AI services store what you type and may use it to improve their systems, depending on their settings. Treat a chat box like a public notice board unless you are sure of the privacy settings.</p>
<ul>
<li><strong>Passwords, PINs and security codes</strong>, including mobile money PINs and one-time codes.</li>
<li><strong>Bank, card and mobile money details.</strong></li>
<li><strong>National ID, passport or driving permit numbers.</strong></li>
<li><strong>Private information about other people</strong>: names with health details, phone numbers, addresses, photos of others without consent.</li>
<li><strong>Confidential work documents</strong>, customer lists, contracts or exam papers.</li>
<li><strong>Medical records and anything very personal.</strong></li>
</ul>
<p>Instead, remove or replace details: write "Mr X" instead of a real name, or "[amount]" instead of the real figure.</p>
<h3>Check your settings</h3>
<p>Look in the tool's settings for options about chat history and whether your chats can be used to train the system. Choose the most private options you are comfortable with, and log out on shared computers.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open the settings of an AI tool you use and find the privacy or data controls. Note what you find.</li>
<li>Take a short real message you wrote. Remove or replace all personal details with placeholders such as [name].</li>
<li>Ask the AI to improve the cleaned message, then put the real details back yourself.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Use AI to improve your own work, not to replace it.</li>
<li>Follow the rules of your school or workplace and be honest about AI use.</li>
<li>Never share passwords, PINs, ID numbers or other people's private information.</li>
<li>Remove personal details before asking for help.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which prompt is most likely to give a useful answer?
- Write about business.
- Help me.
* You are a small business adviser. Write five simple tips for a new phone-accessories shop in Gulu, in bullet points, in plain English.
- Business tips now
= The strong prompt gives a role, task, context and format, so the AI has less to guess.

Q: The AI gives you a draft that is too long. What is the best next step?
- Start a completely new account
* Reply in the same chat: "Make it shorter, about 100 words."
- Delete the answer and give up
- Insult the assistant
= Follow-up messages let you refine a draft without starting again.

Q: Which of these should you NEVER paste into an AI tool? (choose all that apply)
* Your mobile money PIN
* Your national ID number
- A general question about how photosynthesis works
- A fictional story you made up
= PINs and ID numbers are sensitive. General questions and invented stories carry no personal risk.

Q: You wrote an essay and want honest help. What is the best use of AI?
- Ask it to write a new essay and submit that
* Ask it to point out unclear sentences and grammar mistakes in your own draft
- Ask it to do the whole assignment and say nothing
- Paste your teacher's private marking notes
= Getting feedback on your own work supports learning and honesty, as long as your school allows it.

Q: What is a good way to use AI when you struggle with a topic?
- Ask for the final answers to all homework questions
* Ask for a beginner explanation, then practice questions, and answer them yourself first
- Never ask any questions
- Memorise the first answer without checking it
= Practising and testing yourself builds real understanding.

Q: You need to ask an AI about a customer complaint. What should you do first?
- Paste the customer's full name, phone number and address
* Remove or replace personal details with placeholders before pasting
- Post the complaint on social media
- Ask the customer for their PIN
= Protecting other people's privacy is part of responsible use.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: AI for study and work',
            'summary' => 'Use AI for summaries, emails, CVs, planning, spreadsheets, translation, meeting notes and small business messages.',
            'lessons' => [
                [
                    'title'   => 'Summarising, translating and simplifying text',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use AI to shorten long text, simplify difficult documents and translate between languages, while checking that nothing important is lost. These are some of the most useful everyday uses of AI.</p>
<h3>Summarising</h3>
<p>Long reports, articles and emails take time to read. An AI assistant can give you the main points quickly. Paste the text (if it is not confidential) and ask:</p>
<pre>Summarise the text below in five bullet points for a busy manager. Then list any dates or amounts mentioned.

[paste text here]</pre>
<p>Summaries are useful for deciding what to read in full. They are not a replacement for reading anything important, because the AI may miss details or change meaning.</p>
<h3>Simplifying</h3>
<p>Official letters, contracts, terms and medical leaflets can be hard to read. Ask:</p>
<ul>
<li><code>Explain this letter in simple English. What must I do, and by when?</code></li>
<li><code>Rewrite this paragraph for a Primary 6 reading level.</code></li>
<li><code>Give me a glossary of the difficult words.</code></li>
</ul>
<p>For legal or health documents, use the AI only to understand the general idea, then confirm with a lawyer, doctor or the issuing office.</p>
<h3>Translating</h3>
<p>AI translation is very good for major languages such as English, French and Swahili, and weaker for some local languages. Quality varies, so:</p>
<ol>
<li>Tell the AI the audience and tone: <code>Translate to Swahili in a polite tone for a village meeting notice.</code></li>
<li>Ask for a "back translation" into English to check the meaning.</li>
<li>Ask a fluent speaker to review anything important, such as health advice or contracts.</li>
<li>Be careful with names, numbers and idioms, which are often translated wrongly.</li>
</ol>
<h3>Accessibility uses</h3>
<p>AI can help people with different needs: turning speech into text for people who are deaf or hard of hearing, reading text aloud for people with low vision, or simplifying text for people who find reading difficult. Always respect the privacy of the people being helped.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose a news article or a long paragraph from your textbook that is not confidential.</li>
<li>Ask the AI for a three-bullet summary and compare it to the original. Did it miss anything?</li>
<li>Ask it to rewrite the same text for a younger reader.</li>
<li>Translate one short paragraph into another language you know, then translate it back and compare.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>AI can summarise, simplify and translate text quickly.</li>
<li>Always check numbers, names and meaning against the original.</li>
<li>Use human experts for legal, medical and other important documents.</li>
<li>Do not paste confidential content into public tools.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Drafting emails, letters, CVs and cover letters',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>In this lesson you will use AI to write professional emails, letters and a CV that really sounds like you. Writing in a second language or with little practice can be stressful, and AI can give you a strong starting point. You stay responsible for what you send.</p>
<h3>Emails and formal letters</h3>
<p>Give the AI the facts and let it handle the style:</p>
<pre>Write a polite email to my lecturer asking for a two-day extension on my assignment because I was sick. Keep it under 100 words. My name is Akello, course code BUS102.</pre>
<p>Then read it carefully, correct anything untrue, and send it yourself. Never send anything you have not read.</p>
<h3>Using AI for your CV</h3>
<ol>
<li><strong>Collect your facts first:</strong> education, work or volunteer experience, skills, achievements, and references.</li>
<li><strong>Ask for a structure:</strong> <code>Suggest a one-page CV layout for a first job in customer service.</code></li>
<li><strong>Turn duties into achievements:</strong> <code>Rewrite this line to show results: "I helped customers at my uncle's shop."</code></li>
<li><strong>Tailor it</strong> to each job advert by pasting the advert and asking which skills to highlight.</li>
<li><strong>Proofread</strong> and make sure every statement is true.</li>
</ol>
<h3>Cover letters</h3>
<p>Ask for a draft using your real details, then edit so it sounds like you. Employers dislike generic letters. Add one specific sentence about why you want that organisation, which only you can write.</p>
<h3>Honesty rules</h3>
<ul>
<li>Never invent qualifications, jobs or dates. This is dishonest and is often discovered.</li>
<li>Do not copy a template unchanged; many applicants will receive similar text.</li>
<li>Be ready to explain every line in an interview.</li>
<li>Do not paste your ID number, home address or phone number into a public AI tool; add them yourself in your document afterwards.</li>
</ul>
<h3>Common mistakes</h3>
<ul>
<li>Sending a letter that still contains placeholders such as [Company Name].</li>
<li>Using a tone that is too friendly or too stiff for the reader.</li>
<li>Not checking names, dates and spelling of the recipient.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Write five bullet points about your skills and experience in your own words.</li>
<li>Ask the AI to turn them into a short professional profile (three sentences).</li>
<li>Find a real job or internship advert and ask the AI which of your skills match it.</li>
<li>Edit the result until every sentence is true and sounds like you.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Give the AI true facts and clear instructions for emails, letters and CVs.</li>
<li>Always edit, check and personalise before sending.</li>
<li>Never invent experience or qualifications.</li>
<li>Keep personal identifiers out of public AI tools.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Planning, brainstorming and meeting notes',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use AI as a planning partner: for to-do lists, project plans, ideas, and neat notes from meetings. These uses save time and help you think, because you can compare options quickly.</p>
<h3>Planning</h3>
<p>Tell the AI your goal, deadline and limits, and ask for a plan:</p>
<pre>I want to organise a youth football tournament in 6 weeks with a budget of UGX 500,000. Give me a week-by-week plan with tasks and a simple budget table.</pre>
<p>Then check the plan against reality. The AI does not know local prices, permits or who is available, so adjust amounts and dates yourself.</p>
<h3>Brainstorming</h3>
<p>Brainstorming means generating many ideas without judging them at first. AI is good at this:</p>
<ul>
<li><code>Give me 15 name ideas for a poultry farm in Gulu.</code></li>
<li><code>List 10 low-cost ways to promote a tailoring business.</code></li>
<li><code>What problems might I face starting a school club? Give me solutions too.</code></li>
</ul>
<p>Pick the best ideas and improve them with your own knowledge of your community. AI ideas are often generic, so add what makes your project special.</p>
<h3>Meeting notes</h3>
<ol>
<li>Take your own rough notes during the meeting or use a speech-to-text tool if everyone agrees to it.</li>
<li>Do not include confidential information if the tool is public.</li>
<li>Ask: <code>Turn these notes into minutes with: attendees, decisions, actions with names and deadlines.</code></li>
<li>Check names, numbers and deadlines carefully, then share with the group.</li>
</ol>
<h3>A sample output layout</h3>
<table>
<thead><tr><th>Action</th><th>Who</th><th>Deadline</th></tr></thead>
<tbody>
<tr><td>Book the venue</td><td>Brenda</td><td>Friday</td></tr>
<tr><td>Print posters</td><td>Moses</td><td>Next Monday</td></tr>
</tbody>
</table>
<h3>Be the decision maker</h3>
<p>AI can list options but it cannot know your values, budget, or community. You decide. Ask it to give pros and cons, then make your own choice and explain why.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose a small goal, such as holding a school fundraiser or starting a savings group.</li>
<li>Ask the AI for a four-week plan in a table.</li>
<li>Change at least three things in the plan to fit your real situation.</li>
<li>Ask for 10 ideas to promote it, and pick your best three.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>AI can create plans, lists and ideas quickly.</li>
<li>You must adjust everything to local reality.</li>
<li>For meeting notes, check names, decisions and deadlines.</li>
<li>Avoid sharing confidential meeting content in public AI tools.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'AI for spreadsheets and small business messages',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>In this lesson you will use AI to understand spreadsheet formulas and to write clear messages to customers. These are practical skills for office work and for running a small business such as a shop, salon, farm or repair service.</p>
<h3>Help with spreadsheet formulas</h3>
<p>If you use Excel or Google Sheets and cannot remember a formula, describe the job in plain words:</p>
<pre>In Excel, column B has prices and column C has quantities. Write a formula for the total in column D and show how to copy it down.</pre>
<p>A good answer would be something like <code>=B2*C2</code>, with steps to drag it down. Then:</p>
<ol>
<li>Type the formula into a small test sheet first.</li>
<li>Check the result with a calculator on two or three rows.</li>
<li>Ask the AI to explain each part of the formula so you can learn it.</li>
<li>If you get an error message, paste the message and the formula (without private data) and ask what is wrong.</li>
</ol>
<p>Other helpful questions: "How do I count how many customers paid?" "How do I highlight rows where stock is below 5?" "Explain what VLOOKUP does with an example."</p>
<h3>Cleaning and understanding data</h3>
<p>AI can suggest how to tidy a list, for instance to separate first and last names or remove duplicates. Remember, never upload a spreadsheet with customer phone numbers, salaries or other private data to a public tool. Use sample rows instead.</p>
<h3>Messages for customers</h3>
<p>Customers appreciate quick, polite, clear replies. Give the AI the key facts:</p>
<pre>Write a short WhatsApp reply to a customer who asks if we deliver. We deliver within Gulu town for UGX 3,000, orders before 2 pm arrive the same day. Friendly tone, 40 words.</pre>
<p>You can ask for templates for: price lists, order confirmations, apology messages for late delivery, thank-you messages, and polite ways to say no.</p>
<h3>Keep your voice and check facts</h3>
<ul>
<li>Check prices, hours and promises. The AI will not know them unless you state them.</li>
<li>Edit the style so it sounds like your business.</li>
<li>Do not let AI make promises you cannot keep.</li>
<li>For complaints, read carefully and respond personally when the issue is serious.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Create a small sheet with three products, prices and quantities (made-up data).</li>
<li>Ask the AI for a formula for the total and for the grand total, and test it.</li>
<li>Ask for three short customer messages: a welcome, a delivery update and a thank you.</li>
<li>Adjust the wording until it sounds like you, and check every detail.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Describe your spreadsheet task in plain words to get a formula.</li>
<li>Test formulas on sample data and check results yourself.</li>
<li>Never upload private customer data to public tools.</li>
<li>Customer messages need your real facts and your own tone.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What should you do after an AI summarises a report for you?
- Share it widely without reading the report
* Check numbers, names and key points against the original when they matter
- Delete the report
- Assume the summary is always perfect
= Summaries can miss details or change meaning, so check anything important.

Q: You ask an AI to write your CV. Which action is dishonest?
- Asking it to improve the wording of your real experience
- Asking it to suggest a layout
* Letting it add qualifications you do not have
- Proofreading the final CV
= Inventing qualifications is dishonest and can cost you a job later.

Q: A formula suggested by an AI gives a strange total. What is the best action?
- Use it anyway
* Test it on a few rows with a calculator and ask the AI to explain the formula
- Delete the spreadsheet
- Send the sheet to a customer
= Always verify the results of formulas, since AI can make mistakes.

Q: Which is the safest way to get help with a customer spreadsheet?
- Upload the full sheet including all phone numbers
* Use a few made-up sample rows that show the same layout
- Post the sheet on social media
- Share your email password so the AI can open the file
= Sample data protects your customers' privacy.

Q: When translating a health notice into a local language using AI, you should:
- Publish it immediately
* Ask a fluent speaker to review it, because accuracy is important
- Remove all numbers
- Translate it twice and choose randomly
= Translations of health or legal information need human checking.

Q: After AI turns your meeting notes into minutes, what must you check? (choose all that apply)
* Names of people responsible
* Deadlines and decisions
- That the font is pretty
- Nothing, AI never makes mistakes
= Actions, owners and dates are the heart of minutes and must be accurate.

Q: A friendly AI-written reply to a customer promises delivery in one hour, but you cannot do that. What do you do?
- Send it; the customer will not mind
* Edit the message so it matches what your business can really deliver
- Blame the AI later
- Delete your phone
= You are responsible for every promise you send, even when AI wrote the words.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: AI for creativity and marketing',
            'summary' => 'Use AI for captions, content plans, scripts and designs, and understand copyright, deepfakes and AI voices.',
            'lessons' => [
                [
                    'title'   => 'AI for writing captions, content calendars and scripts',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use AI to produce social media captions, a month-long content calendar and short video scripts for a business, club or organisation. Posting regularly is hard when you run out of ideas, and AI can help you stay consistent.</p>
<h3>Know your audience first</h3>
<p>Before you ask for content, write down three things: who you want to reach (for example, university students in Gulu), what you offer, and the tone you want (friendly, serious, funny). Put these in your prompt every time.</p>
<h3>Captions</h3>
<pre>Write 5 Facebook captions for a bakery in Gulu announcing fresh mandazi every morning from 6 am. Friendly tone, under 30 words each, include a call to action to order by WhatsApp.</pre>
<p>Pick your favourite, edit it, and add your real phone number or link yourself. Check the spelling of place names and prices.</p>
<h3>A content calendar</h3>
<p>A <strong>content calendar</strong> is a plan showing what to post, where and when. Ask:</p>
<pre>Create a 4-week content calendar for a tailoring business. Post 3 times a week on Instagram and Facebook. Present it as a table with columns: week, day, platform, topic, post idea.</pre>
<p>Then change topics to match real events, such as school opening, festivals or harvest season.</p>
<h3>Video and audio scripts</h3>
<ol>
<li>Decide the length, for example a 30-second video.</li>
<li>Ask for a script with scenes: <code>Write a 30-second script for a phone video about hand-washing for children. Include what to show on screen and what to say.</code></li>
<li>Read it aloud. Change any words that feel unnatural in your voice.</li>
<li>Record it yourself so it feels real and local.</li>
</ol>
<h3>Hashtags and keywords</h3>
<p>AI can suggest hashtags, but check what they mean and whether they are used for something else. Choose a few relevant ones instead of many.</p>
<h3>Keep it human and truthful</h3>
<ul>
<li>Do not claim results or reviews that did not happen.</li>
<li>Do not copy competitors' posts.</li>
<li>If your audience expects a person, do not pretend a robot is a person in conversations that matter.</li>
<li>Mix AI-assisted posts with real photos and stories from your own work.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Choose a real or imaginary small business.</li>
<li>Ask for five captions and choose the best one.</li>
<li>Ask for a one-week content calendar in table form.</li>
<li>Edit one idea into your own words and write a short 20-second video script.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Give the AI your audience, tone and call to action.</li>
<li>Content calendars keep posting regular.</li>
<li>Edit AI text so it sounds like you and is truthful.</li>
<li>Real photos and stories build trust.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Image and design tools: posters, logos and their limits',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn how AI image and design tools work in general terms, how to describe the picture you want, and what they cannot do well. After it, you will be able to create a simple poster idea and judge whether the result is good enough to use.</p>
<h3>How image generators work</h3>
<p>An <strong>image generator</strong> turns a text description into a picture. It has learned from very large numbers of images with descriptions, so it can combine styles and objects in new ways. Many design apps now include AI features such as removing a background, extending a photo or suggesting layouts.</p>
<h3>Writing an image prompt</h3>
<p>Describe the subject, setting, style and mood:</p>
<pre>A friendly illustration of children planting trees at a school in northern Uganda, bright colours, flat cartoon style, space at the top for a title.</pre>
<p>Add details such as colours, lighting and the type of picture (photo-like, drawing, icon). If the first result is not right, change one thing at a time.</p>
<h3>Posters and logos</h3>
<ol>
<li>Write the poster message first: event name, date, place, contact.</li>
<li>Use AI for a background picture or illustration, not for the important text.</li>
<li>Add the text yourself in a design app so spelling and numbers are correct.</li>
<li>Keep layout simple: one main message, readable fonts, strong contrast.</li>
</ol>
<p>For a <strong>logo</strong>, AI can give you ideas to start with. But AI logos can be too similar to existing brands, may not work in black and white or at small sizes, and may not be easy to protect legally as your own. Many organisations use AI for ideas, then ask a human designer to create the final version.</p>
<h3>Limits to watch for</h3>
<ul>
<li>Wrong or garbled text inside images.</li>
<li>Strange hands, faces or extra fingers.</li>
<li>Stereotypes, for example showing only one kind of person for a job.</li>
<li>Poor accuracy for local clothing, buildings or traditions.</li>
<li>Pictures that look realistic but never happened. Do not use them as proof of real events.</li>
</ul>
<h3>Label AI images honestly</h3>
<p>When an image is AI-made, say so if it could be mistaken for a real photograph, especially in news, community messages or fundraising.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open a free AI image tool or an image feature inside a design app.</li>
<li>Write a prompt for a poster background for a community event of your choice.</li>
<li>Generate two versions, changing only the style (for example "cartoon" and "watercolour").</li>
<li>Check each image for errors. Decide which one you would use, and what text you would add yourself.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Describe subject, setting, style and mood to get better images.</li>
<li>Add important text yourself to avoid spelling mistakes.</li>
<li>AI logos need human review and may not be unique.</li>
<li>Be honest when an image is AI-made.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Copyright, attribution and AI voices',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will understand the main copyright and attribution questions around AI content, and how AI voices work. You do not need to be a lawyer, but you do need to avoid the most common mistakes that can harm artists, writers and yourself.</p>
<h3>What is copyright?</h3>
<p><strong>Copyright</strong> is the legal right of creators to control how their work (writing, music, photos, designs) is copied and used. Using someone's work without permission can lead to complaints, takedowns or legal trouble.</p>
<h3>Why AI makes this complicated</h3>
<ul>
<li>AI systems learn from huge collections of existing work, and creators and companies are still arguing about whether and how that is allowed. Laws differ by country and are changing.</li>
<li>AI output can sometimes look very close to existing work.</li>
<li>Who owns AI-generated content is unclear in many places, and the rules of each tool differ.</li>
</ul>
<h3>Sensible rules for you</h3>
<ol>
<li><strong>Read the tool's terms</strong> about commercial use and ownership before using output for business.</li>
<li><strong>Do not ask AI to copy a living artist's style</strong> or recreate a known character or brand.</li>
<li><strong>Do not paste whole books or paid course materials</strong> into AI tools.</li>
<li><strong>Use your own photos and ideas</strong> as the heart of important work.</li>
<li><strong>Credit sources</strong> when you use facts, quotes or ideas from other people, and say when AI helped.</li>
<li>If a result looks like someone else's work, do not publish it.</li>
</ol>
<h3>Attribution in practice</h3>
<p>A short note is often enough: "Image created with an AI image tool" or "Draft prepared with AI assistance and edited by the team." Schools and publishers may ask for more detail, so follow their rules.</p>
<h3>AI voices</h3>
<p>AI voice tools can read text aloud in natural voices or imitate how a person sounds from a short recording. They can help with audiobooks, accessibility, language practice and radio-style adverts. The risks are real too:</p>
<ul>
<li><strong>Voice cloning</strong> without permission is a serious breach of trust and may be illegal.</li>
<li>Scammers have used cloned voices on phone calls pretending to be a relative asking for money. Always verify by calling back on a known number or asking a question only the real person could answer.</li>
<li>Never upload someone's voice without their clear consent.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Open the terms of use of one AI tool you use and find what it says about commercial use of its output.</li>
<li>Write a one-sentence AI credit line you could add to a poster or document.</li>
<li>Agree with your family a secret code word to use if someone calls in an emergency asking for money.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Copyright protects creators; AI does not remove your duty to respect it.</li>
<li>Read the terms of each tool and credit your sources and AI help.</li>
<li>Do not copy living artists' styles or clone voices without consent.</li>
<li>Be careful of voice scams; verify before sending money.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Deepfakes and how to spot them',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn what deepfakes are and how to examine pictures, videos and audio before you believe or share them. The skill matters because fake content spreads fast on WhatsApp and social media, especially around elections, health and emergencies.</p>
<h3>What is a deepfake?</h3>
<p>A <strong>deepfake</strong> is a photo, video or recording made or changed by AI to show a person saying or doing something they never did. Not all AI media is harmful: films and jokes also use it. The harm comes when it is used to deceive, shame, scam or spread lies.</p>
<h3>Clues that something may be fake</h3>
<table>
<thead><tr><th>Where to look</th><th>Warning signs</th></tr></thead>
<tbody>
<tr><td>Faces</td><td>Skin too smooth, odd teeth, blinking that looks strange, edges around hair or face that shimmer</td></tr>
<tr><td>Hands and objects</td><td>Wrong number of fingers, melted jewellery, writing that makes no sense</td></tr>
<tr><td>Voice</td><td>Flat or robotic rhythm, strange breathing, mouth that does not match words</td></tr>
<tr><td>Background</td><td>Warped lines, repeating patterns, lighting that does not match</td></tr>
<tr><td>Message</td><td>Very shocking, asks you to act immediately or to forward to everyone</td></tr>
</tbody>
</table>
<p>Fakes are improving, so looking at the picture alone is not enough.</p>
<h3>A four-step check</h3>
<ol>
<li><strong>Pause.</strong> Strong emotions are a signal to slow down.</li>
<li><strong>Check the source.</strong> Who posted it first? Is it a known news organisation or an official account?</li>
<li><strong>Look for other reports.</strong> Search for the claim. If it were true, reliable outlets would likely report it.</li>
<li><strong>Search the image.</strong> Use a reverse image search to see if the picture is old or from another event.</li>
</ol>
<h3>Protect yourself and others</h3>
<ul>
<li>Do not forward doubtful content, even "just in case".</li>
<li>Be careful about posting clear photos and voice recordings of yourself publicly; they can be misused.</li>
<li>If someone makes a fake image of you, save evidence, report it to the platform, tell a trusted adult, and report to the police or a relevant authority where it is serious.</li>
<li>Never create fake images of real people to mock or embarrass them.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Find a viral image or message from a social app (or ask a friend for one) that seems shocking.</li>
<li>Apply the four-step check and write down what you found.</li>
<li>Do a reverse image search using the search-by-image option in your browser.</li>
<li>Share what you learned with a family member who often forwards messages.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A deepfake is AI-made or edited media that shows something false.</li>
<li>Visual clues help but are not enough; check the source and other reports.</li>
<li>Pause before sharing, especially when content makes you angry or afraid.</li>
<li>Never make or share fake media that harms someone.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: You use AI to write a poster slogan and generate a background picture. What is the best way to add the date and phone number?
- Let the image tool draw it inside the picture
* Add the text yourself in a design app and check the spelling and numbers
- Leave it out
- Ask a stranger to guess it
= Image tools often garble text, so add important details yourself.

Q: What is a content calendar?
- A tool that makes your phone cheaper
* A plan of what you will post, on which platform and when
- A type of photo filter
- A list of passwords
= A calendar helps you post regularly and avoids last-minute stress.

Q: Why should you be careful about using an AI-generated logo for a business?
- AI logos are always illegal
* It may be too similar to existing logos, may not work at small sizes, and ownership can be unclear
- Logos are not needed
- Colours do not matter
= Use AI logos for ideas, check them carefully, and consider a human designer for the final version.

Q: Which of these is a sensible copyright habit? (choose all that apply)
* Reading the tool's terms about commercial use
* Crediting sources and mentioning AI help where needed
- Asking AI to copy a living artist's style for your poster
- Pasting a whole paid textbook into the tool
= Respecting creators and the rules of the tool protects you and others.

Q: A caller with your cousin's voice asks you to send mobile money urgently. What should you do?
- Send it immediately; it sounds exactly like him
* Hang up and call him back on his known number or ask a question only he could answer
- Send half
- Post about it publicly
= Voice cloning is used in scams; always verify through a trusted channel.

Q: Which step should come first when you see a shocking video that might be a deepfake?
- Forward it to all groups
* Pause and check the source and whether reliable outlets report it
- Comment angrily
- Delete your phone
= Slowing down and checking sources is the best defence against fake media.

Q: Which is an honest use of an AI-made image?
- Presenting an AI picture as a real photo of an event that did not happen
* Using an AI illustration for a poster and labelling it when it could be mistaken for a photo
- Creating a fake picture of a classmate to embarrass them
- Using it as evidence in a complaint
= Be open about AI-made visuals and never use them to deceive or harm.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Responsible and critical use',
            'summary' => 'Think about ethics, honesty, fairness, privacy, jobs and safety, and build your own AI-use policy.',
            'lessons' => [
                [
                    'title'   => 'Ethics, honesty, fairness and consent',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to ask good ethical questions about any AI use and apply them to school, work and community life. Ethics simply means thinking about what is right and fair, and it matters because AI can affect real people at great speed.</p>
<h3>Honesty and academic integrity</h3>
<p><strong>Academic integrity</strong> means being honest in your studies: your submitted work shows your own understanding, and you give credit to others. With AI, integrity means:</p>
<ul>
<li>Following your institution's rules on AI.</li>
<li>Not claiming AI-written work as your own thinking.</li>
<li>Declaring AI help when asked.</li>
<li>Never using AI to fake data, sources or results.</li>
</ul>
<h3>Fairness and bias</h3>
<p>AI can repeat unfair patterns. Imagine a program that sorts job applications and has learned from past hiring where mostly men were chosen. It might unfairly mark down women. To reduce harm:</p>
<ol>
<li>Do not use AI alone to make decisions about people's jobs, school places, loans or discipline.</li>
<li>Check results for unfair patterns across gender, tribe, religion, disability, region and age.</li>
<li>Ask diverse people to review important AI-assisted work.</li>
<li>Challenge stereotypes in AI output and correct them.</li>
</ol>
<h3>Privacy and consent</h3>
<p><strong>Consent</strong> means a person freely agrees after understanding what will happen. Before you use anyone's photo, voice, story or data with an AI tool:</p>
<ul>
<li>Ask permission clearly, especially from children's parents or guardians.</li>
<li>Explain what the tool will do with it.</li>
<li>Accept "no" without pressure.</li>
<li>Collect and keep only what you need.</li>
</ul>
<p>Uganda has a law on data protection and privacy; if you work for an organisation, learn its rules about personal data.</p>
<h3>Transparency and accountability</h3>
<p>Be open about when AI was used, and remember that you stay responsible for the outcome. "The AI did it" is not an excuse if you share a false statement or hurt someone.</p>
<h3>Questions to ask before using AI</h3>
<table>
<thead><tr><th>Question</th><th>Why it matters</th></tr></thead>
<tbody>
<tr><td>Is it honest?</td><td>Am I hiding AI use where I should declare it?</td></tr>
<tr><td>Is it fair?</td><td>Could it harm or exclude a group?</td></tr>
<tr><td>Is it private?</td><td>Am I sharing information that belongs to others?</td></tr>
<tr><td>Is it allowed?</td><td>Does my school or workplace permit it?</td></tr>
<tr><td>Is it checked?</td><td>Have I verified the facts myself?</td></tr>
</tbody>
</table>
<h3>Try it yourself</h3>
<ol>
<li>Think of a real situation in which someone could use AI at your school or workplace.</li>
<li>Apply the five questions in the table and write a short answer to each.</li>
<li>Decide whether you would go ahead, change the plan, or stop, and explain why in two sentences.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Ethics means asking what is honest, fair, private and safe.</li>
<li>Do not let AI alone decide important matters about people.</li>
<li>Get consent before using other people's photos, voices or data.</li>
<li>You stay responsible for what you publish or submit.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'AI, jobs and the skills to build',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to think calmly about how AI may change work and know which skills to grow. Many young people worry that AI will take all the jobs. The truth is more mixed: some tasks will disappear, some jobs will change, and new opportunities will appear.</p>
<h3>Tasks, not just jobs</h3>
<p>A job is made of many tasks. AI is good at certain tasks, such as drafting text, sorting data, basic translation or answering common questions. It is weaker at tasks that need physical presence, deep trust, creativity in real settings, or understanding local people. A nurse, a mechanic, a teacher or a farmer uses many skills that AI cannot replace, but they may use AI for paperwork, learning or planning.</p>
<h3>How work may change</h3>
<ul>
<li><strong>Office work:</strong> routine writing and data entry may be faster, so people are expected to check, decide and communicate.</li>
<li><strong>Small business:</strong> owners can create ads, plans and replies cheaply.</li>
<li><strong>Farming:</strong> some tools give weather, pest and market information to farmers through phones.</li>
<li><strong>Education:</strong> learners have extra help, but also more temptation to copy.</li>
<li><strong>New jobs:</strong> people who can use, check, train and manage AI tools are needed.</li>
</ul>
<h3>Skills worth building</h3>
<table>
<thead><tr><th>Skill</th><th>Why it lasts</th></tr></thead>
<tbody>
<tr><td>Clear communication</td><td>Needed to direct people and AI</td></tr>
<tr><td>Critical thinking and fact-checking</td><td>AI makes mistakes; someone must catch them</td></tr>
<tr><td>Digital skills</td><td>Using computers, internet and office tools well</td></tr>
<tr><td>Creativity and problem solving</td><td>Finding fresh ideas for local needs</td></tr>
<tr><td>Teamwork and empathy</td><td>People still trust people</td></tr>
<tr><td>Learning how to learn</td><td>Tools keep changing</td></tr>
<tr><td>Trade and technical skills</td><td>Practical work stays in demand</td></tr>
</tbody>
</table>
<h3>Practical steps</h3>
<ol>
<li>Choose one skill you want in a year and use AI as a coach to practise it.</li>
<li>Build a small portfolio of real work: a poster, a short report, a simple spreadsheet.</li>
<li>Join a local group or club to practise teamwork and speaking.</li>
<li>Follow reliable news about technology, but avoid hype and fear.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Write down the job or business you want in five years.</li>
<li>List ten tasks involved in it. Mark each task as "AI could help", "AI could do most of it", or "needs a human".</li>
<li>Choose two skills from the table to build in the next three months and plan how.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>AI changes tasks inside jobs more than it removes every job.</li>
<li>People who combine their field with AI skills will be stronger.</li>
<li>Critical thinking, communication and learning ability last.</li>
<li>Build real skills and a portfolio, not just tool knowledge.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Verifying, citing and staying safe',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn a repeatable method for checking AI output, how to cite properly, and how young people can stay safe when using AI tools. These habits protect you from misinformation, scams and online harm.</p>
<h3>A simple verification routine</h3>
<ol>
<li><strong>Mark the claims.</strong> Highlight facts, numbers, names, dates and quotes.</li>
<li><strong>Check with two reliable sources.</strong> Use official websites, textbooks, recognised news organisations or experts.</li>
<li><strong>Test the sources.</strong> If the AI gave links or book titles, open them. Make sure they exist and say what the AI claims.</li>
<li><strong>Look for the date.</strong> Is the information current?</li>
<li><strong>Keep a note</strong> of where you verified each fact.</li>
</ol>
<h3>Citing</h3>
<p>When you use information in a school or work document, cite the original source you verified, such as the book, website or report. Do not cite "the AI" as the only source for a fact. If your institution wants you to mention AI use, include a note such as "AI assistant used for brainstorming and grammar checks" and follow their format.</p>
<h3>Safety for young people</h3>
<ul>
<li>Check the age rules of a tool. Some services are not meant for children.</li>
<li>Do not share your full name, school, address, photos or location in chats.</li>
<li>Be careful with "AI friend" or companion apps; they are designed to keep you engaged and are not real friends.</li>
<li>If an AI response makes you feel scared, pressured or confused, stop and talk to a trusted adult.</li>
<li>Never follow AI advice on medicine, self-harm, dangerous experiments or legal trouble without a qualified adult or professional.</li>
<li>Beware of scam sites that claim to be "free AI" but ask for payment details or install unknown apps. Use official app stores and well-known websites.</li>
</ul>
<h3>Health and wellbeing</h3>
<p>Spending hours chatting with AI can reduce time with real people, sleep and study. Set limits. Ask for human help when you feel lonely or stressed: friends, family, teachers, counsellors.</p>
<h3>Try it yourself</h3>
<ol>
<li>Ask an AI assistant to write a short paragraph about a topic you are studying, including two statistics.</li>
<li>Check each statistic with a reliable source and note whether it was right.</li>
<li>Write the citation of the real source in the format your school uses.</li>
<li>List three safety rules you will follow when using AI tools.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Verify claims with two reliable sources and open any sources the AI gives.</li>
<li>Cite the real source, and declare AI help when required.</li>
<li>Keep personal details private and talk to a trusted adult when worried.</li>
<li>Do not rely on AI for medical, legal or safety decisions.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Your personal AI-use policy and future skills',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>In this final lesson you will write your own short AI-use policy: a personal set of rules that keeps you honest, safe and effective. After it, you will also have a plan for the skills to keep building as AI tools change.</p>
<h3>Why write a policy?</h3>
<p>Rules in your head are easy to forget when you are tired or in a hurry, for example the night before a deadline. A short written policy reminds you what you decided when you were calm. Schools and workplaces increasingly have their own AI rules too, and your policy must always sit inside those rules.</p>
<h3>Step 1: Know the rules around you</h3>
<ol>
<li>Ask your teacher, school office or manager: "Is AI allowed? For what? Must I declare it?"</li>
<li>Look for written guidelines in a student handbook, staff policy or exam instructions.</li>
<li>If the rule is unclear, ask before you use AI, not after.</li>
</ol>
<p>Rules often differ by task. AI may be allowed for brainstorming but banned in an exam. Following the stricter rule is always safer.</p>
<h3>Step 2: Write your own policy</h3>
<p>A good personal policy has four short parts:</p>
<table>
<thead><tr><th>Part</th><th>Example sentence</th></tr></thead>
<tbody>
<tr><td>What I will use AI for</td><td>I will use AI to explain topics, practise questions, check grammar and plan my time.</td></tr>
<tr><td>What I will not do</td><td>I will not submit AI work as my own, or use it where it is banned.</td></tr>
<tr><td>How I protect privacy</td><td>I will never share passwords, PINs, ID numbers or other people's private details.</td></tr>
<tr><td>How I check</td><td>I will verify important facts with two reliable sources before using them.</td></tr>
</tbody>
</table>
<h3>Step 3: Keep growing your skills</h3>
<ul>
<li>Every month, try one new way of using AI for a real task, and note what worked.</li>
<li>Keep practising without AI: writing, calculating and speaking. Skills fade if you never use them.</li>
<li>Learn the basics of digital safety and data protection.</li>
<li>Teach someone else. Explaining AI to a family member or club deepens your own understanding.</li>
<li>Remember that tools and rules change quickly. Re-read your policy every few months and update it.</li>
</ul>
<h3>Common mistakes</h3>
<ul>
<li>Writing a policy but never reading it again.</li>
<li>Making rules so strict that you cannot follow them.</li>
<li>Assuming the rules are the same everywhere.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Write your four-part personal AI-use policy in no more than 120 words.</li>
<li>Check it against the rules of your school, workplace or training programme.</li>
<li>Save it on your phone or print it, and keep it where you will see it.</li>
<li>Set a reminder to review it in three months.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A short written policy keeps your AI use honest and safe.</li>
<li>Always follow the rules of your school or workplace first.</li>
<li>Protect privacy, verify facts and be open about AI help.</li>
<li>Keep learning and keep practising your own skills.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which action shows academic integrity?
- Submitting an AI-written essay as if you wrote it
* Following your school's AI rules and declaring AI help when required
- Asking a friend to write for you
- Making up references
= Integrity means honest work, following the rules and giving credit.

Q: A company wants to use AI alone to decide who gets a loan. What is the main concern?
- Loans are boring
* The AI may repeat unfair patterns from past data and harm some groups
- AI is too fast
- Customers like machines
= Important decisions about people need human oversight and checks for bias.

Q: Before using a classmate's photo in an AI tool, what must you do?
- Nothing, photos are free
* Ask for clear permission (consent) and explain what you will do
- Post it first
- Change the name
= Consent is essential for using other people's images, voices or data.

Q: Which skills are likely to stay valuable as AI spreads? (choose all that apply)
* Critical thinking and fact-checking
* Clear communication
- Ability to copy and paste without reading
- Avoiding all technology
= Judgement, communication and the ability to learn will always matter.

Q: An AI gives you a book title to cite. What should you do?
- Cite it without checking
* Find out whether the book exists and says what the AI claimed
- Ignore your teacher's rules
- Change the author's name
= AI can invent references, so verify them before citing.

Q: A young person feels frightened by an AI chat reply. What is the best action?
- Keep chatting until it passes
* Stop and talk to a trusted adult
- Share the chat on social media
- Give the AI more personal details
= Trusted people are the right source of help and safety.

Q: A website promises "free AI" but asks for your card and mobile money PIN. What should you do?
- Enter them quickly
* Leave the site, because it is likely a scam, and use only official apps and websites
- Ask friends to enter theirs
- Download every file it offers
= Legitimate tools never need your PIN, and scam sites imitate popular AI names.
QUIZ,
        ],
    ],

    'final_exam' => <<<'QUIZ'
Q: Generative AI is best described as AI that:
- Only sorts emails into folders
* Creates new content such as text, images or audio
- Cleans computer screens
- Only works offline
= Generative AI produces new content from patterns it has learned.

Q: Which is the best description of a hallucination?
- A slow internet connection
* A confident but false or invented answer from an AI
- A new software update
- A very long answer
= Hallucinations are invented details that sound real, so facts must be verified.

Q: A chat assistant mainly works by:
- Searching a hidden library of verified facts for every answer
* Predicting likely text based on patterns learned from large amounts of writing
- Calling a human expert for each question
- Copying your phone's contacts
= It generates likely text rather than looking up guaranteed truths.

Q: Why can the same prompt give different answers?
- The tool is broken
* Generated text includes some randomness
- The internet changes colour
- Your keyboard is old
= Variation is normal, so always review the output.

Q: Which of these are everyday examples of AI? (choose all that apply)
* Map apps predicting traffic
* Video apps recommending content
- A paper dictionary
- A wooden ruler
= Maps and recommendation features use machine learning.

Q: Which is the strongest prompt?
- Write something about school.
* You are a teacher. Write a 100-word notice for parents about a school meeting on Friday at 10 am in the hall. Polite tone.
- Notice
- Do my work
= Role, task, context, format and tone make the request clear.

Q: You want an AI to show information as a comparison. Which instruction is best?
- Tell me about phones
* Compare three phones in a table with price range, battery and camera
- Phones are good
- Write a poem
= Naming the format gets you the layout you need.

Q: The AI makes an error in its answer. What should you do?
- Ignore the error
* Tell it what is wrong, give correct information if you have it, and ask it to try again
- Delete your account
- Trust it more
= Clear feedback helps; you remain responsible for checking.

Q: Which is a good way to study with AI?
- Ask it for all answers and copy them
* Ask for an explanation, then practice questions, and answer them before checking
- Never read your notes
- Use it only during exams where it is banned
= Active practice builds understanding.

Q: Which information should you NEVER paste into an AI tool? (choose all that apply)
* Passwords and mobile money PINs
* Other people's private medical details
- A question about how rainbows form
- A poem you wrote yourself
= Sensitive and personal data must stay private.

Q: How can you get help with an email that mentions a customer's details?
- Paste everything including the phone number
* Replace names and numbers with placeholders and add the real details afterwards
- Send it to the AI by SMS
- Do not write the email
= Placeholders protect privacy while still allowing help with the wording.

Q: You use an AI to summarise a long contract. What is the best next step?
- Sign immediately
* Use the summary to understand the general idea and confirm key points with a qualified person
- Delete the contract
- Share the contract publicly
= Legal documents need expert checking.

Q: Which is dishonest when applying for a job?
- Asking AI to improve the grammar of your CV
* Letting AI add qualifications and experience you do not have
- Asking AI to suggest a CV layout
- Proofreading the final text
= Never invent facts about yourself.

Q: A formula suggested by AI for your spreadsheet should be:
- Trusted without testing
* Tested on sample rows and checked with a calculator
- Used only on customer data immediately
- Deleted
= Test before relying on any result.

Q: When AI writes minutes from your rough meeting notes, you must especially check:
- The paper colour
* Names, decisions, actions and deadlines
- The length of the title
- The size of the font
= These are the most important parts of minutes.

Q: What is a content calendar used for?
- Making printers faster
* Planning what to post, where and when
- Storing passwords
- Cleaning images
= It keeps posting regular and organised.

Q: Why should you add the date and phone number to a poster yourself rather than letting an image tool draw them?
- Image tools never make pictures
* Image tools often produce garbled text and numbers
- Phone numbers are illegal
- Dates are secret
= Typing details yourself avoids dangerous errors.

Q: Which is a good copyright habit?
- Asking AI to copy a living artist's style for a paid poster
* Reading the tool's terms and crediting sources and AI assistance
- Pasting a full paid textbook into a tool
- Ignoring licences
= Respecting creators and tool rules protects you.

Q: What is a deepfake?
- A fast internet cable
* AI-made or AI-edited media that shows something that did not happen
- A kind of antivirus
- A mobile money feature
= Deepfakes can be used to deceive, so check before sharing.

Q: You receive a shocking video from an unknown account. What should you do first?
- Forward it to all contacts
* Pause, check the source and see whether reliable outlets report it
- Comment angrily
- Believe it because it looks real
= Checking sources is the best defence against fake media.

Q: A caller using your relative's voice asks for urgent money. What is the safest response?
- Send the money at once
* Hang up and call back on a known number, or ask a question only the real person would know
- Send half now and half later
- Send your PIN
= Voice cloning scams rely on urgency and trust.

Q: Why should a company not let AI alone decide who gets a job interview?
- AI is too expensive
* It may repeat bias from past data and treat some groups unfairly
- AI cannot read
- Interviews are old fashioned
= Human oversight and checks for fairness are needed.

Q: Before using a child's photo in an AI tool you should:
- Use it without asking
* Get permission from the child's parent or guardian and explain how it will be used
- Change their name only
- Post it publicly first
= Consent protects the privacy and safety of children.

Q: Which of these skills will stay useful as AI grows? (choose all that apply)
* Critical thinking
* Teamwork and empathy
- Copying without reading
- Refusing to learn new tools
= Human judgement and the ability to learn remain valuable.

Q: Which is the best way to cite information you got through an AI assistant?
- Cite only "the AI" for every fact
* Verify the fact in a reliable source and cite that source, adding a note about AI use if required
- Invent a book title
- Remove the citation
= Cite the real source and be honest about AI help.

Q: A tool says it is "free AI" but demands your mobile money PIN. This is:
- Normal
* A warning sign of a scam
- Required by law
- A useful security feature
= Real tools never need your PIN.

Q: A friend says "The AI wrote it, so it is not my fault if it is wrong." What is the best reply?
- That is correct
* You are still responsible for what you share or submit, so check it first
- AI should be blamed in court
- Mistakes do not matter
= Accountability stays with the person who uses the output.

Q: Which statements belong in a good personal AI-use policy? (choose all that apply)
* I will not paste private information or passwords
* I will check important facts before using them
* I will follow the rules of my school or workplace and be honest about AI help
- I will use AI to submit work I do not understand
= A good policy protects privacy, truth and honesty.
QUIZ,
];
