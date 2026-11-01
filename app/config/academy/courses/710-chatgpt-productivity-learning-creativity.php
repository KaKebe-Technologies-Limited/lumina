<?php
return [
    'slug'        => 'chatgpt-productivity-learning-creativity',
    'title'       => 'ChatGPT for Productivity, Learning, Creativity and Entrepreneurship',
    'category'    => 'ai',
    'level'       => 'beginner',
    'icon'        => 'star',
    'sort'        => 710,
    'summary'     => 'Move from asking ChatGPT questions to using AI as a practical tool: write strong prompts, study smarter, create content, plan a business, prepare for jobs, research, code, build assistants, use AI responsibly and earn from your skills.',
    'description' => '<p>AI assistants such as ChatGPT can help you learn faster, write better, plan your time, create content, research problems, prepare for jobs and start a business, but only if you know how to ask well and how to check what comes back. This course is for students, job seekers, entrepreneurs, content creators and young professionals, and it is practical from the first lesson: every lesson has prompts you can copy and try, examples from East African life, and a hands-on task.</p><p>You will begin with how ChatGPT works, what it can and cannot do, and how to get started safely, then learn prompt engineering: clear instructions, context, roles, formats, examples and iteration. Next you apply AI to education, content creation and personal productivity, then to business ideas, career preparation and research. Advanced lessons cover coding help, documents and office work, images, voice and documents, and designing custom assistants. The final stages teach responsible use, privacy, fact-checking, ethics and honest ways to earn income, and finish with the ChatGPT Productivity Challenge, a project that solves a real problem.</p><p>Features, plans and prices change often and differ by account and country, so the course teaches skills that transfer to any AI assistant, such as Gemini, Claude or Microsoft Copilot. If you cannot use ChatGPT, you can follow every exercise with another assistant. Throughout, you will practise the habit that matters most: treat AI as a fast assistant that can be wrong, check important facts, protect private information, and keep your own judgement in charge.</p>',
    'outcomes'    => [
        'Explain what ChatGPT and generative AI can and cannot do, and use them safely',
        'Write clear prompts with context, role, format and examples, and improve them step by step',
        'Use AI for studying, revision, writing and personal planning without breaking academic integrity',
        'Plan, draft and improve content, marketing and business documents with AI help',
        'Prepare a CV, cover letter and interview practice, and spot recruitment scams',
        'Research a question, check sources, and recognise hallucinations and bias in AI answers',
        'Use AI for coding help, documents, images, voice and custom assistant instructions',
        'Protect privacy, disclose AI use honestly and verify important information',
        'Design an honest freelance service and complete a documented AI-assisted project',
    ],
    'requirements' => ['A phone or computer with an internet connection', 'No experience needed', 'Access to ChatGPT or another AI assistant (free versions are enough to start; the lessons say how to check current features)', 'A notebook or notes app for prompts and plans'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'ChatGPT Help Center', 'url' => 'https://help.openai.com/'],
        ['title' => 'Microsoft Copilot', 'url' => 'https://copilot.microsoft.com/'],
        ['title' => 'Google Gemini', 'url' => 'https://gemini.google.com/'],
        ['title' => 'Khan Academy', 'url' => 'https://www.khanacademy.org/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- STAGE 1
        [
            'title'   => 'Stage 1: AI Foundations',
            'summary' => 'Understand what ChatGPT and generative AI are, set up and use the assistant safely, and learn to write clear prompts that give useful, checkable answers.',
            'lessons' => [
                [
                    'title'   => 'Understanding AI, ChatGPT and generative AI',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to explain in plain words what artificial intelligence is, how ChatGPT produces its answers, and what it can and cannot do. This matters because people who understand the tool trust it the right amount and are not fooled when it is wrong.</p>

<h3>What is artificial intelligence?</h3>
<p><strong>Artificial intelligence (AI)</strong> means computer systems that perform tasks which normally need human judgement, such as recognising a face or translating a sentence. You already use AI when your phone unlocks with your face or a video site suggests what to watch. AI is not a robot with feelings. It is software that finds patterns in very large amounts of data.</p>

<h3>What is ChatGPT and how does it work?</h3>
<p><strong>ChatGPT</strong> is a chat-based AI assistant: you type (or speak) a request and it replies in everyday language. Behind it is a <strong>large language model (LLM)</strong>, trained on huge collections of text to learn how words and ideas fit together. It does not look up one stored answer. It builds a reply piece by piece, choosing words that are likely to follow.</p>
<p>This is why it is called <strong>generative AI</strong>: it generates new text (and, in some tools, images, audio or code) instead of only sorting or labelling existing data. It also predicts a believable answer instead of checking facts, so it can sound confident and still be wrong. An invented fact that sounds real is called a <strong>hallucination</strong>.</p>

<h3>What ChatGPT can and cannot do</h3>
<table>
<thead><tr><th>It is usually good at</th><th>It is not reliable at</th></tr></thead>
<tbody>
<tr><td>Explaining ideas in simple words</td><td>Giving exact facts, dates, figures and quotations without errors</td></tr>
<tr><td>Drafting emails and summarising text you supply</td><td>Very recent events, unless it can search the web</td></tr>
<tr><td>Brainstorming options and names</td><td>Making final decisions on health, law or money; it may also invent references</td></tr>
</tbody>
</table>

<h3>Different ways to use it</h3>
<p>You can use ChatGPT as a <strong>tutor</strong>, a <strong>writer's assistant</strong>, a <strong>planner</strong>, a <strong>brainstorming partner</strong> or a <strong>practice partner</strong> (for example, playing the interviewer). Features, plans and tools differ by account, country and device, so always check the current help pages. These skills transfer to other assistants such as Gemini, Claude or Microsoft Copilot, so you can follow every exercise with whichever you can access.</p>
<p>Services also set minimum-age and terms-of-use rules. Check them before you sign up; if you are under 18, get a parent or guardian's permission where required.</p>

<h3>Worked example: weak versus strong</h3>
<p>Peter, a student in Kampala, does not understand inflation. His weak prompt:</p>
<pre>Tell me about inflation.</pre>
<p>A typical response is a long, general essay full of terms he does not know. He tries again:</p>
<pre>Explain inflation to a first-year student in Uganda. Use one example with the price of a kilogram of beans. Keep it under 150 words, then ask me one question to check I understood.</pre>
<p>Here is an example of the kind of answer you might get; your answer will differ: a short explanation that prices rise over time so the same money buys less beans, then a check question. The second prompt works because it names the reader, gives an example and sets a limit.</p>

<h3>Case scenario</h3>
<p>Mercy, a final-year student in Mbarara, asked ChatGPT for five books and journal articles for her essay and pasted them straight into her reference list. Two of them did not exist, and she lost marks. The lesson: the tool predicts believable text, so any source, number or quotation must be checked in the library or on the official website before you use it.</p>

<blockquote><p><strong>Quick check:</strong> Why can ChatGPT give a wrong answer in a confident tone?</p><p>Answer: It generates likely-sounding words rather than checking facts, so wrong answers read as smoothly as right ones.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which task suits ChatGPT better: summarising your own lecture notes, or giving the exact current price of a bus fare?</p><p>Answer: Summarising your notes, because it works from text you supply, while current prices change.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open ChatGPT or another AI assistant you can access (check its age and terms rules first).</li>
<li>Ask: "Explain what a large language model is to a 12-year-old in five sentences."</li>
<li>Ask a follow-up: "Now give me one example of something you are good at and one thing you can get wrong."</li>
<li>Check one factual claim in the answer on a reliable website or in a textbook.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>AI finds patterns in data; ChatGPT is a language model that generates text one piece at a time.</li>
<li>It can sound sure and still be wrong, so check facts, numbers and sources.</li>
<li>It is strongest as a helper for explaining, drafting, summarising and brainstorming.</li>
<li>Clear instructions with audience and limits beat vague questions.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'AI in daily life, Africa, ethics and finding problems to solve',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to describe how AI assistants are used in education, business and employment, weigh the opportunities and limits in Africa, apply basic ethical rules, and name practical problems in your own life that ChatGPT could help with. This matters because the learners who benefit most are the ones who choose real problems rather than playing with the tool.</p>

<h3>AI assistants and conversational interfaces</h3>
<p>A <strong>conversational interface</strong> is a way of using a computer by talking or typing in ordinary language, instead of clicking through menus. AI assistants are built this way: you describe what you want, read the reply, then correct or extend it. The skill is not typing commands but explaining a need clearly and judging the result.</p>

<h3>AI in education, business and employment</h3>
<ul>
<li><strong>Education:</strong> explaining hard topics, making practice questions, improving grammar. The risk is copying answers and not learning.</li>
<li><strong>Business:</strong> drafting product descriptions, customer replies, price lists and simple plans. The risk is sending unchecked text to customers.</li>
<li><strong>Employment:</strong> preparing CVs, practising interviews, writing professional emails. Employers may also use AI tools to screen applications, so accuracy and honesty matter.</li>
</ul>

<h3>Opportunities and limitations in Africa</h3>
<p>Opportunities include low-cost tutoring, help with writing in English, faster business paperwork, and new service jobs for young people who combine AI with a skill. Limitations are just as real:</p>
<ol>
<li><strong>Data costs and connectivity:</strong> a long chat on a weak connection can be expensive or slow.</li>
<li><strong>Language:</strong> assistants are usually strongest in English and may be weaker in Luganda, Kiswahili or other local languages.</li>
<li><strong>Local knowledge:</strong> answers may reflect other countries, so check laws, prices, taxes and customs against local sources.</li>
</ol>

<h3>Responsible and ethical use</h3>
<p>Four habits keep you safe. <strong>Be honest:</strong> follow your school or employer's rules and say when AI helped if they ask. <strong>Protect privacy:</strong> never paste passwords, ID numbers, health records, confidential school or employer data or other people's private information into any AI tool. <strong>Check:</strong> verify important facts with reliable sources. <strong>Stay responsible:</strong> you, not the tool, own what you submit or send.</p>

<h3>Identifying problems ChatGPT can help solve</h3>
<p>Good problems are small, repeated and text-based. Good candidates involve reading, writing, explaining, planning or organising, and you can check the result yourself.</p>
<pre>I am a [student / tailor / shop owner / job seeker] in [place].
My main challenge is: [describe it in two sentences].
List five ways an AI assistant could help, and for each one tell me what I must check myself before using the result.</pre>

<h3>Worked example</h3>
<p>Aisha runs a small tailoring business in Gulu. Her time-wasting tasks are answering the same customer questions on WhatsApp, writing price lists, and finding words for adverts. She runs the template above. An example of the kind of answer you might get; your answer will differ: draft replies for common questions, a tidy price list layout, three advert ideas, with a warning to check prices and delivery promises herself.</p>

<h3>Case scenario</h3>
<p>Samuel, a clerk at a small clinic in Jinja, pasted a patient's name and test results into an AI tool and asked for a summary letter. A supervisor explained that patient records are confidential. Samuel had to report the mistake and now writes such letters himself. The lesson: privacy decisions are made before you press send, not after.</p>

<blockquote><p><strong>Quick check:</strong> Name two limits of using AI assistants in a place with weak internet and many local languages.</p><p>Answer: Data cost or slow connections, and weaker support for local languages; either one can reduce quality or access.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Is it acceptable to paste a customer's phone number and address into ChatGPT to write a reply?</p><p>Answer: No, because personal details should stay out of AI tools; write the reply with a placeholder such as [Customer name].</p></blockquote>

<h3>Try it yourself</h3>
<p>Assignment: identify five challenges in your education, work or business that ChatGPT could help address.</p>
<ol>
<li>Write down ten annoying or time-consuming tasks from your week.</li>
<li>Cross out any that need private data you must not share, or that need a human decision (such as a medical choice).</li>
<li>Choose the five best, and for each write: the challenge, what you would ask the AI, and how you would check the result.</li>
<li>Try the template from this lesson with your assistant on your top challenge and note one useful idea and one idea you rejected.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>AI assistants work through plain-language conversation; your job is to explain clearly and judge the result.</li>
<li>Opportunities are real, but data costs, language gaps and local differences need care.</li>
<li>Be honest, protect privacy, check facts and own your final work.</li>
<li>Start with small, repeated, text-based problems that you can verify.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Accounts, the interface and conversations',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to create and secure an account, find your way around the ChatGPT screen, start and manage conversations, and ask questions and follow-ups that keep the assistant on track. This matters because a tidy, well-managed workspace saves time and stops you losing good work.</p>

<h3>Creating and accessing an account</h3>
<p>Sign-up methods and features differ, so use the official site or app and its current help pages, never a link from a stranger. Before you register, read the minimum-age and terms-of-use rules; under-18s should have a parent or guardian's permission where required. Protect your account: use a strong, unique password, switch on two-step verification if offered, and never share your login. If you cannot access ChatGPT, every exercise in this course works with another assistant such as Gemini, Claude or Microsoft Copilot.</p>

<h3>The interface</h3>
<p>Screens change often, so look for these parts rather than exact button names:</p>
<ul>
<li>A <strong>message box</strong> at the bottom where you type your request.</li>
<li>A <strong>conversation area</strong> in the middle showing your messages and the replies.</li>
<li>A <strong>list of past chats</strong>, usually at the side, with a way to start a new chat.</li>
<li>A <strong>settings or profile menu</strong>, usually near your name, for privacy and data options.</li>
<li>Small options on each reply, such as copy, regenerate or give feedback.</li>
</ul>

<h3>Starting and managing conversations</h3>
<p>A <strong>conversation</strong> (chat) is one running thread. The assistant uses what was said earlier in the thread as <strong>context</strong>. That helps when you continue one task, but it can confuse results if you mix topics. A good rule: one task, one chat. Give chats clear titles and start a fresh one when you change subject.</p>

<h3>Writing effective questions</h3>
<p>Include the task, the situation, who it is for and the form you want. Compare:</p>
<pre>Weak: Help me with my business.

Strong: I sell second-hand phones from a small shop in Mbale. Write three short WhatsApp messages (under 40 words each) announcing a weekend discount. Friendly tone, no emojis.</pre>
<p>A typical response to the weak prompt is a general list of business tips. The strong prompt usually gives three usable drafts you can edit. Check them: do they promise a discount you do not offer?</p>

<h3>Follow-up questions and context</h3>
<p>You do not have to start again when a reply is nearly right. Reply to it: "Make the second one shorter", "Use simpler English", or "Add a line about free delivery in town". Because the chat remembers earlier messages, short follow-ups work. If the answers drift, give a reminder: "Remember, this is for customers in Mbale, not Kampala."</p>

<h3>Organising conversations for different tasks</h3>
<ol>
<li>Create separate chats for study, business, job search and personal planning.</li>
<li>Give each a clear title, such as "Biology revision - cells" or "Shop adverts - June".</li>
<li>Keep reusable prompts in a notes app so you can paste them into any chat.</li>
</ol>

<h3>Case scenario</h3>
<p>Ruth, a youth group leader in Lira, used one chat for everything: a fundraising letter, a football schedule and a sermon outline. Later the assistant started adding football details to the letter. She also could not find her best draft among hundreds of messages. She started a separate, clearly named chat for each task and copied final versions into a document. The lesson: mixed topics create mixed answers, and the chat history is not a filing cabinet.</p>

<blockquote><p><strong>Quick check:</strong> You finished a business plan chat and now want help with a birthday speech. What should you do?</p><p>Answer: Start a new chat, because separate tasks keep the context clean and avoid mixed-up answers.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A reply is almost right but too long. Should you rewrite your whole prompt?</p><p>Answer: No; send a short follow-up such as "Cut this to 80 words", because the chat keeps the earlier context.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Set up or open your account (or another assistant) and turn on any extra login protection offered.</li>
<li>Find the message box, the chat list and the settings menu; write down where each is on your screen.</li>
<li>Start a chat, give it a clear title, and ask for three WhatsApp messages for a real or imaginary small business.</li>
<li>Send two follow-ups that change length and tone.</li>
<li>Start a second, separate chat for a different task and compare how tidy each one is.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use official sources, respect age and terms rules, and protect your login.</li>
<li>Learn the parts of the screen, not the exact button names, because layouts change.</li>
<li>One task per chat keeps context clean; name your chats.</li>
<li>Follow-up messages improve a draft faster than starting over.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Models, voice, files, plans and keeping your data safe',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to understand what different models and features mean, use voice, images and documents wisely where available, compare free and paid options without guessing, and protect personal and business information. This matters because the same tool can be safe or risky depending on what you give it.</p>

<h3>Available models and capabilities</h3>
<p>A <strong>model</strong> is the underlying AI engine. A service may offer more than one: some are faster, others better at careful reasoning. Names and availability change often and differ by account and country. Look for a model selector near the message box and read the short description beside each option. For simple drafting a quick model is usually enough; for a complex problem, try a stronger option if you have it, and always check the result.</p>

<h3>Voice conversations, images and documents</h3>
<p>Where your account and device support it, you can speak to the assistant and hear replies, which is useful for practising English or rehearsing a talk. You may also upload a photo or a document and ask questions about it, for example "Summarise this notice in five bullet points". These features can misread handwriting, blurry photos and tables, so compare the answer with the original.</p>

<h3>Free and paid features</h3>
<p>Services often offer a free option and paid options with extras such as more usage, newer models or extra tools. Plans, prices and limits change and vary by country, so check the current pricing and help pages before deciding. You do not need a paid plan to learn the skills in this course. Never pay a stranger for "special access"; that is a common scam.</p>

<h3>Privacy settings and data controls</h3>
<p>Look in your settings for options about chat history and whether your conversations may be used to improve the service. Read what each choice means in the current help pages and choose what suits you. Settings reduce some risks but do not make secrets safe to share.</p>

<h3>Protecting sensitive information</h3>
<p>Never paste passwords, ID numbers, health records, bank details, confidential school or employer data or other people's private information into any AI tool. Replace details with placeholders first.</p>
<table>
<thead><tr><th>Unsafe to paste</th><th>Safer version</th></tr></thead>
<tbody>
<tr><td>"Grace Namutebi, NIN CM9..., owes UGX 1,200,000"</td><td>"Customer A owes UGX [amount]; write a polite reminder"</td></tr>
<tr><td>Photo of your ID card</td><td>Do not upload it at all</td></tr>
</tbody>
</table>

<h3>Worked example: a safe document question</h3>
<p>Brian, a student in Kampala, wants help understanding a scholarship notice. He types out the key paragraph with his name removed and asks:</p>
<pre>Below is part of a scholarship notice. List the deadlines, the documents required and any conditions in a table. If something is unclear, say so instead of guessing.

[paste the paragraph here]</pre>
<p>An example of the kind of answer you might get; your answer will differ: a three-column table of deadlines, documents and conditions, plus a note that one date is unclear. Brian then checks every date against the official notice before acting.</p>

<h3>Case scenario</h3>
<p>Kevin, a job seeker in Entebbe, uploaded a photo of his national ID and bank statement to an AI chat "to fill an application form faster". He later realised he had no idea how that data would be stored or who could see it. He changed his passwords, deleted the chats and now types only what a form needs. The lesson: a shortcut that exposes identity documents is never worth it.</p>

<blockquote><p><strong>Quick check:</strong> Where can you reliably find which models and limits your account has today?</p><p>Answer: In the model selector and the service's current help or pricing pages, because these details change and differ by account.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You want a summary of your company's confidential price list. What is the safest approach?</p><p>Answer: Do not upload it unless your employer approves the tool; otherwise remove confidential details first, because it is not yours to share.</p></blockquote>

<h3>Try it yourself</h3>
<p>Assignment: use ChatGPT for five everyday tasks, then evaluate quality and usefulness.</p>
<ol>
<li>Choose five tasks, such as a polite email, a revision summary, a meal budget, a WhatsApp advert and a study timetable.</li>
<li>Complete each in a separate chat, using placeholders instead of private details.</li>
<li>For each, score the answer from 1 to 5 for accuracy, usefulness and tone, and note what you had to fix.</li>
<li>Open the settings, find the privacy or data options, and write one sentence on what you chose and why.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Models, features and plans change; check current help pages and do not assume.</li>
<li>Voice, image and document tools are helpful but can misread; verify against the original.</li>
<li>Use placeholders and never share passwords, IDs, health or confidential data.</li>
<li>Evaluate every answer for accuracy, usefulness and tone.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Prompt basics: instructions, context, role, format and tone',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to write prompts with clear instructions, background, a role, an output format and the right tone for your audience. This matters because the quality of the answer depends heavily on the quality of the request, and a good prompt saves many rounds of correction.</p>

<h3>Prompts and prompt engineering</h3>
<p>A <strong>prompt</strong> is the instruction or question you give an AI assistant. <strong>Prompt engineering</strong> is the practice of writing prompts that reliably produce useful results. It is not a secret code. It is clear communication: say what you want, for whom, and in what shape.</p>

<h3>Clear and specific instructions</h3>
<p>Use action verbs (list, compare, rewrite, summarise) and add limits such as length, number of items or level of English.</p>
<pre>Weak: Write about malaria.

Strong: Write a 120-word notice for a village health board in Kiryandongo explaining three ways to prevent malaria. Use simple English and short sentences.</pre>

<h3>Context and background</h3>
<p>The assistant does not know your situation. Give it: who you are, the goal, the audience, and any facts it must use. Without context the answer is generic. </p>

<h3>Defining the role</h3>
<p>A role shapes its vocabulary and focus. Examples: "Act as a patient maths tutor for a Senior 3 student" or "Act as a careers adviser for a graduate in Kampala." A role changes style, but it does not make the assistant a licensed professional.</p>

<h3>Output format</h3>
<p>Say how you want the answer shaped: a table, five bullets, a short email or a numbered plan.</p>

<h3>Tone, audience and style</h3>
<p>Name the reader and the feeling: "formal, respectful, for a district official" or "friendly, for teenagers". </p>

<h3>A copyable template</h3>
<pre>Role: Act as [role].
Task: [what to do, with an action verb].
Context: [who I am, the situation, key facts without private data].
Audience: [who will read it].
Format: [table / bullets / email / length].
Tone: [formal / friendly / simple].
If you are unsure of any fact, say so instead of guessing.</pre>

<h3>Worked example: tailor in Gulu</h3>
<p>Aisha wants a message for customers who have not collected finished clothes. Her weak prompt: "Write a message about clothes." A typical response is a vague notice. Her strong prompt:</p>
<pre>Role: Act as a polite customer-service writer for a small tailoring shop in Gulu.
Task: Write a WhatsApp message reminding customers to collect finished clothes.
Context: Items not collected within 30 days may be stored for a small fee. Shop hours are 8 am to 6 pm.
Format: Under 60 words, two short paragraphs.
Tone: Warm but firm. Leave [Customer name] as a placeholder.</pre>
<p>An example of the kind of answer you might get; your answer will differ: a friendly greeting, the 30-day rule stated politely, the shop hours, and a thank-you. Aisha checks that the policy matches her real one before sending.</p>

<h3>Practical demonstration</h3>
<ol>
<li>Write a weak prompt for a task you need this week.</li>
<li>Run it and save the answer.</li>
<li>Rewrite it with the template: add role, context, audience, format and tone.</li>
<li>Run it in a new chat and compare the two answers side by side.</li>
</ol>

<h3>Case scenario</h3>
<p>Daniel, a graduate in Mbale, asked, "Write a cover letter." The result was generic and mentioned a degree and skills he did not have. He sent it unchecked and was asked in an interview about a project he never did. He then wrote a prompt with his real course, two real achievements and the job advert (with the company's private details left out), and he edited the draft before sending. The lesson: AI fills gaps with guesses, so supply the true facts and read every claim.</p>

<blockquote><p><strong>Quick check:</strong> Which prompt part tells the assistant who will read the answer?</p><p>Answer: The audience (and tone), because it changes the words, length and formality of the reply.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> If you ask the assistant to "act as a doctor", is its advice safe to follow for treatment?</p><p>Answer: No, because a role only changes style; it does not give real qualifications or knowledge of your health.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick a real task: a notice, an email or a short social post.</li>
<li>Fill in the template above with your own details, using placeholders for private data.</li>
<li>Run it, then change only the tone (for example formal to friendly) and compare.</li>
<li>Highlight any statement you cannot confirm and check it before using the text.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A prompt is a request; good ones are clear, specific and give context.</li>
<li>Role, audience, format and tone shape the answer.</li>
<li>A role changes style, not real expertise.</li>
<li>Supply true facts and check the output; do not let the tool guess about you.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Advanced prompting: steps, examples, refining and reusable templates',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to break big tasks into steps, teach the assistant with examples, refine answers through follow-ups, avoid common mistakes, build reusable templates and judge the quality of what you get back. This matters because most real tasks are too big for one perfect prompt, and your improvement habits are what separate a beginner from a confident user.</p>

<h3>Breaking complex tasks into steps</h3>
<p>If you ask for a whole business plan in one message, the answer is usually shallow. Instead, split the work: first ask for questions to clarify the idea, then the customers, then the costs, then the draft. After each step, check and correct before moving on.</p>
<pre>I want to start selling roasted groundnuts in Soroti. Do not write the plan yet.
Step 1: Ask me ten questions you need answered first.
I will reply, and then we will go step by step.</pre>

<h3>Using examples</h3>
<p>An example shows the assistant the style you want better than a long description. This is sometimes called few-shot prompting. Give one or two samples and ask for more in the same pattern.</p>
<pre>Here are two product descriptions I wrote:
1) "Soft cotton school shirt, double-stitched, UGX 25,000."
2) "Strong canvas bag, fits an A4 book, UGX 30,000."
Write three more in the same short style for: a wool sweater, a belt, a school tie. I will give you prices.</pre>

<h3>Refining through follow-ups</h3>
<p>Treat the first answer as a draft. Useful follow-ups: "Shorter", "Use simpler words", "Give me a different angle", "What did you assume?", "List what I should verify". Asking "What are you least sure about?" can reveal weak points.</p>

<h3>Common prompting mistakes</h3>
<table>
<thead><tr><th>Mistake</th><th>Better habit</th></tr></thead>
<tbody>
<tr><td>Too vague ("Help me")</td><td>State task, audience and limits</td></tr>
<tr><td>No context</td><td>Add situation and true facts</td></tr>
<tr><td>Accepting the first answer</td><td>Review, then refine</td></tr>
<tr><td>Pasting private data</td><td>Use placeholders</td></tr>
<tr><td>Trusting facts blindly</td><td>Verify with reliable sources</td></tr>
</tbody>
</table>

<h3>Reusable prompt templates</h3>
<p>When a prompt works, save it with blanks you can reuse.</p>
<pre>Act as [role]. I am [who I am]. My goal is [goal].
Here is the situation: [facts, no private data].
Do this: [task] for [audience] in [format] with a [tone] tone.
Then list three things I must check before using your answer.</pre>
<p>Keep a "prompt library" in a notes app, organised by task.</p>

<h3>Evaluating and improving responses</h3>
<p>Use a quick checklist: Is it accurate (checked)? Relevant to my situation? Complete? Clear for my audience? Right tone? Is anything invented, such as names, numbers, laws or sources? Then improve the prompt, not only the answer: note what was missing and add it next time.</p>

<h3>Worked example: improving through iteration</h3>
<p>Mary, a shop owner in Masaka, asks for a promotion message. Prompt 1: "Write an advert." Typical result: generic text. Prompt 2 adds product, price and audience. Better, but too long. Follow-up 3: "Cut to 40 words and add the shop's opening hours." Result: a short, usable advert. She finally changes the discount percentage to the real one. Three small rounds beat one giant prompt.</p>

<h3>Case scenario</h3>
<p>Joel, a young trader in Kabale, asked an assistant to explain the tax rules for his small business. The answer was confident and detailed, and he followed it. Later he learned part of it was for another country and out of date. He had to pay a penalty and fix his records. He now asks the assistant to explain the concepts only, and then confirms the actual rules with the official tax authority's website or an accountant. The lesson: confident wording is not proof; important rules need an official source.</p>

<blockquote><p><strong>Quick check:</strong> Why is it better to split a large task into steps?</p><p>Answer: Each step can be checked and corrected, which gives deeper, more accurate results than one huge request.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is the benefit of giving an example in your prompt?</p><p>Answer: It shows the style, length and pattern you want, so the assistant can copy it instead of guessing.</p></blockquote>

<h3>Try it yourself</h3>
<p>Assignment: write five prompts for different tasks, test them, improve through iteration.</p>
<ol>
<li>Choose five tasks (for example a study summary, an advert, an email, a plan and a list of questions).</li>
<li>Write a first prompt for each, run it and score the answer from 1 to 5.</li>
<li>Improve each prompt with context, format, examples or steps, and run it again.</li>
<li>Record for each: the first score, the final score and the change that helped most.</li>
<li>Save your best three as templates in your prompt library.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Break big jobs into steps and check after each one.</li>
<li>Examples teach style better than long descriptions.</li>
<li>Treat the first answer as a draft and refine with follow-ups.</li>
<li>Save working prompts as templates and evaluate every answer with a checklist.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which statement best describes how ChatGPT produces an answer?
- It searches a private database and copies the one correct answer.
* It builds a reply piece by piece, choosing words that are likely to fit, based on patterns learned from large amounts of text.
- It phones a human expert who types the reply.
- It only repeats sentences you typed earlier.
= A large language model generates text from learned patterns, which is why it can sound sure and still be wrong.

Q: Mercy pastes five references from an AI assistant into her essay. Two do not exist. What went wrong?
- The assistant was switched off.
- Her essay topic was too difficult.
* She trusted the output without checking, and AI can invent believable sources (hallucination).
- References are never allowed in essays.
= Sources, numbers and quotations must be verified in a library or on an official site before you use them.

Q: Samuel, a clinic clerk, wants an AI summary of a patient's test results. What is the best decision?
- Paste the full record because the tool is private.
- Paste only the patient's name.
* Do not share the record; remove all identifying details or write the summary himself, following the clinic's rules.
- Upload a photo of the file and delete it later.
= Health records and other people's private information must never be pasted into AI tools.

Q: You change from planning a fundraiser to writing a revision summary. What is the best practice?
* Start a new, clearly named chat for the new task.
- Keep everything in one chat so the assistant remembers more.
- Delete your account and start again.
- Always switch to another assistant.
= One task per chat keeps the context clean and avoids mixed-up answers.

Q: A learner says, "The paid plan has these exact features and limits, so I will tell everyone." What is the sensible approach?
- Trust the list because someone posted it online.
- Pay a stranger for guaranteed access.
* Check the current help and pricing pages, because features, plans and limits change and differ by account and country.
- Assume free and paid versions are identical.
= Details change often, so verify them from the official source before deciding.

Q: Which prompt is strongest?
- "Write something about my shop."
- "Make an advert."
* "Write a 40-word WhatsApp advert for my phone shop in Mbale, friendly tone, mention a weekend discount, leave [price] as a placeholder."
- "Business help please."
= Specific task, audience, length, tone and placeholders give the assistant what it needs.

Q: Which of these are good ways to improve an AI answer? (choose all that apply)
* Ask for a shorter version with simpler words
* Give an example of the style you want
- Accept the first answer without reading it
* Ask it to list what you should verify
= Refining with follow-ups, examples and verification lists improves quality; accepting the first draft blindly does not.

Q: Joel followed confident tax advice from an AI and later found part of it belonged to another country. What should he do next time?
- Ask the same assistant to promise it is correct.
* Use the assistant to understand concepts, then confirm the actual rules with the official authority or an accountant.
- Stop using any technology.
- Ask three friends and trust the majority.
= Confident wording is not proof; important rules need an official or professional source.
QUIZ,
        ],
        // ---------------------------------------------------------------- STAGE 2
        [
            'title'   => 'Stage 2: Everyday Productivity',
            'summary' => 'Use an AI assistant as an honest study partner, a content planning helper and a personal productivity coach, while keeping your own thinking, your integrity and your fact-checking habits.',
            'lessons' => [
                [
                    'title'   => 'Study partner: understand, simplify and practise',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to use ChatGPT to understand difficult ideas, simplify topics, build study notes, practise with questions, plan a study timetable, work through maths and science, and practise languages. This matters because a patient explainer available at any hour can save you from hours of confusion, as long as you use it to learn and not to hand in its work as your own.</p>
<p>Menus differ by account and country, so look for current options and check the help pages. Every exercise works in other assistants such as Gemini, Claude or Microsoft Copilot.</p>

<h3>The ground rule: understand and practise, never submit as your own</h3>
<p>Use AI to understand and practise. Do not paste an assignment and submit the answer as yours; that is cheating, and schools and exam bodies treat it seriously. Follow your school and exam rules about AI, and never paste confidential school material into an AI tool.</p>

<h3>1. Understand and simplify difficult concepts</h3>
<p>A weak prompt gives a weak result.</p>
<pre><code>Weak: Explain photosynthesis.

Strong: I am a Senior 6 biology candidate in Uganda. Explain the light-dependent
stage of photosynthesis in simple English. Use one everyday analogy, then give
a four-step summary. End by asking me two questions to check my understanding.</code></pre>
<p>An example of the kind of answer you might get; your answer will differ: it compares the chloroplast to a small solar charging station, lists four steps, then asks why water is needed. Answer its questions before reading on.</p>

<h3>2. Notes, revision guides and quizzes</h3>
<p>Give the assistant YOUR class notes (typed text without private details) and ask it to organise them, not to invent content.</p>
<pre><code>Here are my notes on the causes of inflation. Turn them into a one-page
revision guide with headings, key terms and three common exam mistakes.
Use only what is in my notes and tell me what seems to be missing.</code></pre>
<p>Then ask: "Quiz me with 10 questions, one at a time, wait for my answer, then mark it." Answering from memory builds recall.</p>

<h3>3. Maths and science: check every step</h3>
<p>Ask for a worked solution, then verify it yourself. Example: a car starts from rest and accelerates at 2 m/s per second for 5 seconds. A correct answer uses v = u + at, so v = 0 + 2 x 5 = 10 m/s, and s = ut + (1/2)at^2, so s = 0.5 x 2 x 25 = 25 m. Recompute each line yourself, because AI can slip while sounding sure.</p>

<h3>4. Languages and vocabulary</h3>
<p>Ask for ten words on a theme with sample sentences, write your own sentences, and ask for corrections with the rule behind each mistake.</p>

<h3>5. A personalised study schedule</h3>
<p>Tell the assistant your exam date, weak subjects and free hours, then ask for a weekly table.</p>
<table>
<thead><tr><th>Day</th><th>Evening (2 hours)</th><th>Method</th></tr></thead>
<tbody>
<tr><td>Mon</td><td>Chemistry: organic basics</td><td>Notes, then 10-question self-quiz</td></tr>
<tr><td>Tue</td><td>Mathematics: calculus</td><td>Past-paper questions, check each step</td></tr>
<tr><td>Wed</td><td>Biology: photosynthesis</td><td>Explain aloud, then diagram from memory</td></tr>
<tr><td>Thu</td><td>Weak topic review</td><td>Redo errors from the week</td></tr>
<tr><td>Fri</td><td>English essay practice</td><td>Plan an essay yourself, then ask for feedback</td></tr>
</tbody>
</table>

<h3>Case scenario</h3>
<p>Peter, a Senior 6 candidate in Mbarara, pasted his entire physics assignment into an AI tool and copied the answers. They looked neat, but the answers used a formula his teacher had not taught and two numbers were wrong. His teacher asked him to explain the solution on the board and he could not. He lost marks and trust. Peter restarted: he asked the assistant to explain the formula, tried the problems himself and asked for feedback. Two weeks later he solved a similar question alone. The lesson: AI that does the work for you steals the practice you need.</p>

<blockquote><p><strong>Quick check:</strong> You ask for a worked maths solution. What should you do before trusting it?</p><p>Answer: Recheck each step yourself with the correct formulas and your calculator, because AI can make confident arithmetic or reasoning errors.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why ask the assistant to quiz you instead of just summarising a topic again?</p><p>Answer: Answering questions from memory builds recall, while rereading a summary feels easy but teaches less.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick one topic you find hard and write a strong prompt with your level, the exact sub-topic, an analogy request and two check questions.</li>
<li>Answer the check questions in your own words before reading the explanation again.</li>
<li>Ask for a 5-question quiz, one question at a time, and write each answer before asking for marking.</li>
<li>Ask for a weekly study table using your real free hours, and a revision guide from a few lines of your own notes.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use AI to understand and practise; never submit its work as your own.</li>
<li>Strong prompts state your level, topic, format and a request to check your understanding.</li>
<li>Verify every maths and science step yourself.</li>
<li>Self-quizzing and your own writing build learning; reading answers does not.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Writing, exams, research and academic integrity',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to improve your grammar and writing without losing your own voice, prepare for exams and interviews, summarise study material, brainstorm research topics, verify facts and references, avoid plagiarism and build independent, critical thinking. This matters because a student who checks sources and thinks alone keeps their marks, their degree and their reputation.</p>

<h3>Improving grammar and writing</h3>
<p>Write the first draft yourself. Then ask for feedback, not a rewrite.</p>
<pre><code>Weak: Make this essay better.

Strong: Here is my paragraph (written by me). Do not rewrite it. List the
grammar errors, explain each rule in one sentence, and point out where my
argument is unclear. I will fix it myself.</code></pre>
<p>An example of the kind of answer you might get; your answer will differ: it numbers five errors, such as a missing apostrophe and a long sentence that should be split, and notes that your second sentence makes a claim without evidence. You fix each one yourself.</p>

<h3>Exams and interviews</h3>
<p>Ask the assistant to act as an examiner or interviewer. For a university scholarship interview in Kampala: "Ask me one scholarship interview question at a time. After each answer, tell me one strength and one improvement. Do not give a model answer until I ask." Practise out loud, and for exams answer practice questions under timed conditions without notes.</p>

<h3>Summarising study material</h3>
<p>Paste a section of your own notes and ask: "Summarise in five bullet points, then list two ideas you left out." Compare it with the source; summaries can change meaning.</p>

<h3>Brainstorming research topics</h3>
<p>Ask for ten narrow topics in your field and why each is researchable, for example "ten small research questions on mobile money use among market vendors in Kampala that a final-year student could study in three months". Choose one yourself and discuss it with your supervisor.</p>

<h3>Verify facts and check references</h3>
<p>AI can invent books, authors, journal articles, statistics and web addresses that look real. These are called fabricated citations. Never put a reference in your work because ChatGPT gave it. Search for the title in your library catalogue or a trusted academic database, open the real source, and confirm the author, year and the claim. If you cannot find it, it may not exist.</p>

<h3>Avoid plagiarism and over-dependence</h3>
<table>
<thead><tr><th>Acceptable (if your institution allows)</th><th>Not acceptable</th></tr></thead>
<tbody>
<tr><td>Asking for an explanation, then writing in your own words</td><td>Submitting AI text as your own</td></tr>
<tr><td>Asking for feedback on your draft</td><td>Using AI during a closed-book test</td></tr>
<tr><td>Making practice questions for yourself</td><td>Copying a source without credit</td></tr>
<tr><td>Disclosing AI help when rules require it</td><td>Hiding AI use your school bans</td></tr>
</tbody>
</table>
<p>Support critical thinking by asking: "Give me the strongest argument against my view, and I will respond." When unsure what is allowed, ask your teacher before you use the tool.</p>

<h3>Case scenario</h3>
<p>Aisha, a university student in Kampala, asked ChatGPT for five journal references for her literature review and pasted them into her bibliography. Her lecturer searched for them and found that two articles did not exist; the authors were real, but the titles were invented. Aisha faced an academic misconduct meeting and had to resubmit. After that she changed her routine: she used AI for topic ideas, found sources herself through the library, read them, and wrote the review in her own words. The lesson: an unchecked reference can cost you more than a late assignment.</p>

<blockquote><p><strong>Quick check:</strong> ChatGPT gives you a reference with an author, title and year. Can you cite it?</p><p>Answer: Not until you find and read the real source, because AI can invent convincing references.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which prompt keeps your own writing: "Rewrite my essay" or "List my grammar errors and explain the rules"?</p><p>Answer: The second, because you stay the author and learn from the corrections.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a challenging topic from your own studies, such as organic chemistry reactions, a statistics method or a law concept.</li>
<li>Ask for a short study guide that uses only your own notes, then compare it with your textbook and fix any error.</li>
<li>Ask for ten practice questions with answers hidden, answer them yourself, then ask for marking and explanations.</li>
<li>Ask for a two-week revision plan with your real exam date and free hours.</li>
<li>Write a short note listing the facts you checked, the sources you used and whether your school allows the AI help you used.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Write your own draft first; use AI for feedback and explanation.</li>
<li>Verify every fact and reference in a real source; fabricated citations are common.</li>
<li>Follow school and exam rules, and disclose AI help where required.</li>
<li>Challenge your own ideas to strengthen independent thinking.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Content ideas, social media plans, captions and scripts',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to brainstorm content ideas, plan a social media strategy, write engaging captions, build a content calendar and draft blog posts, video scripts and podcast outlines with ChatGPT. This matters because small businesses and creators lose followers when they post randomly or run out of ideas, and a clear plan saves time every week.</p>
<p>Menus and tools vary by account, so look for the current options in your app. The prompts work in any AI assistant.</p>

<h3>Give context before asking for ideas</h3>
<p>The assistant knows nothing about your business unless you tell it. A weak prompt gets generic content.</p>
<pre><code>Weak: Give me Instagram ideas for my salon.

Strong: I own a small hair salon in Ntinda, Kampala. My customers are women
aged 20 to 35 who book on WhatsApp. I post on Instagram and Facebook three
times a week. Give me 12 post ideas across four types: before-and-after,
hair care tips, customer stories and behind the scenes. Mention which
ideas need a photo and which need a short video.</code></pre>
<p>An example of the kind of answer you might get; your answer will differ: a table with twelve ideas, each labelled by type and format, such as "Protective styles: three ways to keep braids neat for six weeks (photo carousel)". Pick the ideas that are true for your business, and drop any that promise results you cannot deliver.</p>

<h3>Strategy, captions and the calendar</h3>
<p>Ask the assistant to propose a simple strategy first: the goal (bookings, awareness, sales), the audience, three content themes and the best posting times to test. Treat posting times as experiments, since your own insights tell the truth. For captions, give a hook, the main point and a call to action: "Write three caption options in friendly English, under 40 words each, with one emoji maximum, ending with an invitation to book on WhatsApp." You then choose and edit.</p>
<table>
<thead><tr><th>Day</th><th>Theme</th><th>Format</th><th>Purpose</th></tr></thead>
<tbody>
<tr><td>Mon</td><td>Hair care tip</td><td>Photo carousel</td><td>Teach and build trust</td></tr>
<tr><td>Wed</td><td>Customer story (with permission)</td><td>Photo</td><td>Show proof</td></tr>
<tr><td>Fri</td><td>Before and after</td><td>Short video</td><td>Attract bookings</td></tr>
<tr><td>Sun</td><td>Weekend booking reminder</td><td>Status or story</td><td>Fill empty slots</td></tr>
</tbody>
</table>

<h3>Blogs, scripts and podcasts</h3>
<ol>
<li><strong>Blog post:</strong> ask first for an outline with headings, then expand one section at a time and add your own examples and local detail.</li>
<li><strong>Video script:</strong> give the length ("45 seconds"), the hook for the first three seconds, the main tip and the closing action.</li>
<li><strong>Podcast outline:</strong> give the topic, guest, length and audience, and ask for an opening, three segments and ten interview questions, including follow-ups.</li>
</ol>

<h3>Practical demonstration</h3>
<ol>
<li>Open ChatGPT (or another assistant) and paste your strong prompt about your business.</li>
<li>Look for the ideas that match your real services and photos you can take this week.</li>
<li>Ask: "Turn the best five ideas into captions with a hook and one call to action."</li>
<li>Ask for a one-week calendar table, then edit it to fit your real schedule.</li>
</ol>

<h3>Case scenario</h3>
<p>Brenda, a salon owner in Ntinda, asked ChatGPT for a month of posts and scheduled them all without reading. One post praised a hair treatment that her salon does not offer, and customers began asking for it on WhatsApp. She had to apologise publicly and lost several bookings. She now reads every post, deletes anything untrue, adds her own photos and checks that each claim matches her services. The lesson: AI drafts are not final posts; the account owner is responsible for every word.</p>

<blockquote><p><strong>Quick check:</strong> Why include your location, audience and posting habits in a content prompt?</p><p>Answer: The assistant has no other way to know your business, so context turns generic ideas into relevant ones.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You receive a suggested best time to post. How should you treat it?</p><p>Answer: As an idea to test, because your own audience insights show what really works.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a strong prompt about a real or imaginary local business with audience, platforms and posting frequency.</li>
<li>Choose five ideas that are honest and doable, and reject at least one.</li>
<li>Ask for three caption options for one idea and edit the best one in your own voice.</li>
<li>Ask for a one-week content calendar table and note the photo or video each post needs.</li>
<li>Ask for a 45-second video script on one tip and read it aloud to see if it sounds natural.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Context makes ideas relevant; vague prompts give generic content.</li>
<li>Plan themes and formats first, then captions, then a calendar.</li>
<li>Outline long content in stages and add your own examples.</li>
<li>You must read and approve every post before publishing.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Headlines, repurposing, brand voice, SEO and checking before you publish',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to write headlines and calls to action, brief a designer with visual ideas, turn one long piece into many short ones, set a brand voice, edit and proofread, make content easier to find in search and check AI content before you publish it. This matters because your posts carry your name and your customers' trust.</p>

<h3>Headlines and calls to action</h3>
<p>A headline earns the click; a call to action (CTA) tells the reader the next step. Ask for options, then choose.</p>
<pre><code>Weak: Write a headline for my tailoring post.

Strong: Write 8 headline options for a Facebook post about my tailoring
shop in Jinja that makes office dresses in five days. Audience: working women.
Tone: warm and confident. Max 10 words each. Then write 4 call-to-action lines
that tell the reader to message us on WhatsApp.</code></pre>
<p>An example of the kind of answer you might get; your answer will differ: options such as "Office dresses that fit, ready in five days", followed by CTAs like "Send us your measurements on WhatsApp today". Remove any promise you cannot keep, such as "guaranteed" or "cheapest".</p>

<h3>Graphic and visual ideas</h3>
<p>Ask: "Suggest three poster layouts for this post, with colours, a main photo idea and the exact words for the image." Then build the design in a tool such as Canva, or give the brief to a designer.</p>

<h3>Repurpose long content into short content</h3>
<p>If you wrote a 600-word blog post, ask the assistant to produce one thread of five posts, three short captions, a 30-second video script and a WhatsApp status text, all using the same facts.</p>

<h3>Brand voice guidelines</h3>
<p>A brand voice is how your business sounds. Give two or three real examples of posts you like and ask: "Describe this voice in five rules, with words to use and words to avoid." Save the rules and paste them at the start of future prompts.</p>

<h3>Editing, proofreading and search</h3>
<p>Ask the assistant to proofread and mark changes: "Fix spelling and punctuation, keep my meaning, and list every change you made." For search, ask for a plain-language title, a short description and a few words people might type, such as "tailor in Jinja" or "office dresses Jinja". Use them naturally, because stuffing keywords makes writing worse. No tool can promise a ranking, since search systems change.</p>

<h3>Fact-check before you publish</h3>
<table>
<thead><tr><th>Check</th><th>Question to ask</th></tr></thead>
<tbody>
<tr><td>Facts and numbers</td><td>Can I find this in a reliable source?</td></tr>
<tr><td>Claims about your business</td><td>Is it true today?</td></tr>
<tr><td>Quotes and names</td><td>Did this person really say it?</td></tr>
<tr><td>Rights</td><td>Do I have permission for photos, music and customer stories?</td></tr>
<tr><td>Tone</td><td>Would I be happy to see this under my name?</td></tr>
</tbody>
</table>

<h3>Assignment: a seven-day content plan</h3>
<p>Develop a seven-day social media content plan for a business, a personal brand or a community organisation. For each day include the theme, format, caption draft, call to action and the fact you checked.</p>

<h3>Case scenario</h3>
<p>Ivan, who runs a youth group in Gulu, asked ChatGPT for a post about a government youth fund. The assistant wrote an exciting post with a deadline and a fund amount. Ivan posted it without checking. The deadline was wrong, dozens of members rushed to apply late and the group received angry messages. He deleted the post, published a correction and now checks every date and amount on the official site first. The lesson: an unchecked post can mislead many people, and correction is harder than checking.</p>

<blockquote><p><strong>Quick check:</strong> You turn a blog post into five short posts. What must you confirm?</p><p>Answer: That the short posts contain no new claims and match the original facts, because rewriting can add errors.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Can an AI assistant guarantee a top search ranking?</p><p>Answer: No, because search systems change and nobody controls them; clear, honest writing is the best help.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a strong prompt for eight headlines and four calls to action for a real or imaginary business.</li>
<li>Choose one headline and one CTA, and delete any exaggerated claim.</li>
<li>Paste a short paragraph you wrote and ask for proofreading with every change listed.</li>
<li>Build your seven-day plan table and write beside each day the fact you will verify and the source you will use.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Ask for several headline and CTA options, then choose and edit.</li>
<li>Save your brand voice rules and reuse them in prompts.</li>
<li>Repurpose carefully so facts stay the same.</li>
<li>Fact-check, get permissions and approve every post before publishing.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Goals, schedules, habits, decisions and reflection',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to use ChatGPT to clarify goals, write SMART goals and action plans, build daily, weekly and monthly schedules, prioritise your time, design habits, make better decisions and reflect on your progress. This matters because most goals fail from vagueness, not from lack of ability, and a coach-like assistant can ask the questions you forget to ask yourself.</p>

<h3>Goals and SMART plans</h3>
<p>A SMART goal is Specific, Measurable, Achievable, Relevant and Time-bound. Ask the assistant to question you rather than invent your life.</p>
<pre><code>Weak: Help me achieve my goals.

Strong: I am a 22-year-old youth leader in Mbale. My goal is to run a
monthly community clean-up with at least 30 volunteers by 31 March. Ask me
five questions about my resources and limits. After I answer, turn the goal
into a SMART goal and a 6-week action plan with weekly tasks.</code></pre>
<p>An example of the kind of answer you might get; your answer will differ: the assistant asks about budget, equipment, partners and available weekends, then writes "Recruit 30 volunteers through three school clubs and one church group, confirm the site and tools by week 4, hold the clean-up in week 6", with tasks per week. Read it critically: is the target realistic with your real budget and time?</p>

<h3>Time management and prioritisation</h3>
<p>Paste your task list and ask for sorting by urgency and importance. A simple method is four groups: do now (urgent and important), schedule (important, not urgent), delegate (urgent, not important) and drop (neither). Add real limits: "I work 8am to 5pm, study from 7pm to 9pm, and have church on Sunday morning."</p>
<table>
<thead><tr><th>Time</th><th>Monday</th><th>Wednesday</th><th>Saturday</th></tr></thead>
<tbody>
<tr><td>6:00-7:00</td><td>Exercise, breakfast</td><td>Exercise, breakfast</td><td>Chores</td></tr>
<tr><td>7:00-9:00 pm</td><td>Study accounting</td><td>Plan clean-up</td><td>Volunteer meeting</td></tr>
<tr><td>9:00-9:30 pm</td><td>Review tomorrow</td><td>Review tomorrow</td><td>Rest</td></tr>
</tbody>
</table>
<p>Build the month the same way: ask for four weekly themes, a Sunday planning slot and a buffer day for surprises.</p>

<h3>Habits, decisions and reflection</h3>
<ul>
<li><strong>Habits:</strong> ask for a tiny version of the habit (for example, ten minutes of reading after supper) linked to something you already do, plus a simple tracker.</li>
<li><strong>Decisions:</strong> describe two options and ask for a table of benefits, costs, risks and questions to answer. The assistant structures your thinking; you decide, because it does not know your family, finances or values fully.</li>
<li><strong>Reflection:</strong> end each week with: "Ask me five questions about what went well, what did not, what I learned and one change for next week." Write honest answers.</li>
</ul>

<h3>Practical demonstration</h3>
<ol>
<li>Write a goal in one sentence.</li>
<li>Ask the assistant to question you before it plans, and answer truthfully.</li>
<li>Look for the SMART version and shorten any step that feels too big.</li>
<li>Copy the plan into your phone calendar or notebook, because a plan you cannot see will not be used.</li>
</ol>

<h3>Case scenario</h3>
<p>Moses, a university student in Kampala, asked an AI assistant for a timetable and received a plan with study blocks from 5am to 11pm and no travel time. He tried it for three days, burned out and quit planning altogether. He then returned to the assistant with real information: lecture times, a long commute, sleep and meals, and asked for a lighter plan with one rest evening. The new plan lasted. The lesson: a plan is only as realistic as the facts you give it, and you must adjust it to your life.</p>

<blockquote><p><strong>Quick check:</strong> Why ask the assistant to question you before writing a goal plan?</p><p>Answer: Your answers give it real limits and resources, so the plan fits your situation instead of being generic.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> The assistant recommends one of two job offers. Should you simply follow it?</p><p>Answer: No, because it cannot know your full situation; use it to organise the comparison and decide yourself.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write one personal or professional goal and ask the assistant to ask you five questions.</li>
<li>Answer honestly and request a SMART version with a six-week plan.</li>
<li>Give your real daily commitments and ask for a weekly timetable including rest.</li>
<li>Choose one habit, ask for a ten-minute version and a simple tracking table.</li>
<li>Use the weekly reflection prompt and write your answers in a notebook.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Ask the assistant to question you first; honest answers produce useful plans.</li>
<li>SMART goals have numbers, a deadline and weekly actions.</li>
<li>Give real limits so schedules are realistic, and keep buffer time.</li>
<li>AI organises your decision thinking; the decision is yours.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Communication, speaking, learning plans and staying independent',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to use ChatGPT to improve written communication, prepare presentations and speeches, practise public speaking and interviews, solve problems, build personal learning plans, organise projects and use AI as support without becoming dependent. This matters because your voice and judgement are your real assets, and AI should sharpen them, not replace them.</p>

<h3>Written communication</h3>
<p>Draft the message yourself, then ask for polish. Never paste private details such as ID numbers or other people's information.</p>
<pre><code>Weak: Write an email to my supervisor.

Strong: Here is my draft email asking my supervisor for a two-day extension
on a project report because of illness. Make it polite and professional,
keep it under 120 words, and keep all the facts as I wrote them. Do not add
details I did not give.</code></pre>
<p>An example of the kind of answer you might get; your answer will differ: a clean three-paragraph email with a clear request and a thank-you. Check that dates are right and that it sounds like you.</p>

<h3>Presentations, speeches and speaking practice</h3>
<p>Give the topic, time, audience and goal, and ask for an outline: opening story, three points, closing action. Write the words yourself using your own examples, then ask for feedback on clarity and timing. For practice: "Act as an audience member at a youth leadership meeting. Ask me three hard questions after my talk." For interviews: "Ask me one interview question at a time for a junior accounts assistant job, then give me feedback on my answer." Practise aloud, because typing does not build speaking skill.</p>

<h3>Problem-solving and critical thinking</h3>
<p>Ask the assistant to guide, not to answer: "My small shop's sales fell this month. List possible causes, what evidence would confirm each one and the cheapest test for each." Then collect the evidence yourself from your own records. Also ask: "What assumptions am I making?" and "What would a person who disagrees say?"</p>

<h3>Learning plans and project tracking</h3>
<table>
<thead><tr><th>Week</th><th>Goal</th><th>Activity</th><th>Proof of progress</th></tr></thead>
<tbody>
<tr><td>1</td><td>Spreadsheet basics</td><td>20 minutes a day, one tutorial</td><td>Simple budget sheet</td></tr>
<tr><td>2</td><td>Formulas</td><td>Practise SUM and AVERAGE</td><td>Marks sheet with totals</td></tr>
<tr><td>3</td><td>Charts</td><td>Make two charts</td><td>One-page summary</td></tr>
<tr><td>4</td><td>Apply it</td><td>Track a real project</td><td>Shared with a friend for feedback</td></tr>
</tbody>
</table>
<p>For a project, ask for tasks, owners, deadlines and a status column, then maintain the table yourself in a spreadsheet or notebook.</p>

<h3>Using AI without becoming dependent</h3>
<ol>
<li>Try the task first for a set time, then ask for help.</li>
<li>Ask for hints and questions before answers.</li>
<li>Write the final version yourself.</li>
<li>Keep skills alive: do some tasks fully without AI each week.</li>
<li>Check important facts in reliable sources and use human advisers for health, legal and money decisions.</li>
</ol>

<h3>Assignment: a 30-day personal development plan</h3>
<p>Create a 30-day personal development plan with measurable goals, weekly activities and progress indicators. Include one weekly reflection and one habit you will practise without AI.</p>

<h3>Case scenario</h3>
<p>Sharon, a graduate in Kampala, used an AI assistant to write every message, including thank-you notes to her mentor. When a hiring manager asked her to write a short reply during an interview test, she froze and her writing was weak. She realised she had stopped practising. She changed her approach: she wrote her own first drafts, asked the assistant only for feedback and spent ten minutes a day writing without help. Within a month her unaided writing improved and she passed the next test. The lesson: tools that do everything for you can quietly remove the skills you need.</p>

<blockquote><p><strong>Quick check:</strong> When practising for an interview with an AI assistant, what is a good rule?</p><p>Answer: Answer in your own words first and ask for feedback afterwards, because practice only helps if you do the talking.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is one sign you are depending on AI too much?</p><p>Answer: You can no longer start a simple task, such as a short message, without it, which shows the skill is fading.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a short email yourself, then ask for polish and compare the two versions.</li>
<li>Ask the assistant to run a five-question mock interview, answering aloud before reading feedback.</li>
<li>Ask for a four-week learning plan for one skill, with proof of progress each week.</li>
<li>Build your 30-day plan table with measurable goals, weekly activities and progress indicators.</li>
<li>Choose one task each week that you will do with no AI at all, and note how it went.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Draft first, then ask for feedback and polish.</li>
<li>Practise speaking aloud; use the assistant as an audience and coach.</li>
<li>Gather your own evidence when solving problems.</li>
<li>Keep your skills and judgement strong by doing real work yourself.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A Senior 6 student wants to use ChatGPT for physics revision. Which approach best respects academic integrity?
- Paste the whole take-home assignment and submit the answers
* Ask for explanations, try the problems alone and ask for feedback on the working
- Use it during a closed-book test if nobody is watching
- Ask it to write the answers and change a few words
= AI should help you understand and practise. Submitting its work as yours, or using it where rules forbid it, is cheating.

Q: ChatGPT gives you three journal references for your literature review. What must you do before using them?
- Nothing, because AI only produces real sources
- Check only that the authors' names look familiar
* Find each source in a library catalogue or trusted database and confirm the details and the claim
- Copy them but remove the years
= AI can invent convincing references. Only sources you have located and read should appear in your work.

Q: Aisha pasted five AI-generated references into her bibliography and two did not exist. What was her main mistake?
* She did not verify the references in real sources
- She asked for references at all
- She used a long prompt
- She wrote the review in English
= The failure was the missing check. AI can fabricate titles and authors, and the student is responsible for every citation.

Q: Which prompt for a salon's Instagram plan is strongest?
- Give me some ideas for posts
- Write 30 posts for a salon
* Describe the salon, location, audience, platforms and posting frequency, then ask for 12 ideas in four post types
- Make my salon famous
= Context about the business and audience, plus a clear format request, produces relevant ideas instead of generic ones.

Q: Brenda scheduled a month of AI-written posts without reading them, and one advertised a service her salon does not offer. What is the best lesson?
- AI should never be used for social media
- Customers should check posts for her
* The owner must read, correct and approve every post before it goes out
- Posts should be longer
= AI drafts can include untrue claims. The person who publishes is responsible for what the post says.

Q: Moses received a study timetable of 5am to 11pm with no travel time. Which follow-up is best?
- Follow it exactly and push through the exhaustion
- Delete the plan and stop planning
* Give the assistant his real lecture times, commute, meals and sleep and ask for a lighter plan with a rest evening
- Ask for a longer timetable
= A realistic plan needs realistic information. The assistant works with what you tell it, and you must adjust the result to your life.

Q: Which are good ways to avoid becoming dependent on AI? (choose all that apply)
* Try the task yourself first before asking for help
- Let the assistant write every message in full
* Do some tasks each week completely without AI
* Write the final version in your own words
= Dependence grows when AI does everything. Practising alone, drafting yourself and keeping skills active protect your judgement.

Q: You ask an assistant for a one-week content calendar and it suggests a posting time. How should you treat the suggestion?
- As a guaranteed best time
* As an idea to test against your own audience results
- As a rule from the social media platform
- As proof you need not check anything
= Posting times differ by audience and change over time. Your own insights show what works.
QUIZ,
        ],
        // ---------------------------------------------------------------- STAGE 3
        [
            'title'   => 'Stage 3: Business and Professional Skills',
            'summary' => 'Use ChatGPT to shape and test a business idea, prepare honestly for jobs and freelancing while avoiding scams, and research a real problem without being fooled by invented facts.',
            'lessons' => [
                [
                    'title'   => 'From business idea to value proposition with ChatGPT',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to use ChatGPT to generate business ideas, draft early market research, describe customers and competitors, and write a value proposition and business model. This matters because most small businesses fail from guessing, not from lack of effort, and an AI assistant can help you ask better questions before you spend your savings. It cannot answer them for you: everything it writes about customers and markets is a draft to test.</p>

<h3>Step 1: ideas from your own skills and problems</h3>
<p>A weak prompt gives a weak list. A strong prompt gives ChatGPT your real situation.</p>
<pre>Weak: Give me business ideas.

Strong: I am 23, live in Mukono, have UGX 1,500,000 in savings, and can repair phones. List 8 small business ideas I could start within three months. For each, give the main customer, the biggest risk, and one cheap way to test it before spending money. Put the answer in a table.</pre>
<p>A typical response is a table of ideas such as screen-repair stall, phone accessories, repair training for teenagers. This is an example of the kind of answer you might get; your answer will differ.</p>

<h3>Step 2: customer problems and personas</h3>
<p>A <strong>customer persona</strong> is an invented but realistic description of a typical buyer. Ask for questions to ask real people, not for facts about them.</p>
<pre>I plan a phone repair stall near a taxi park. Create 3 customer personas (age, work, budget range, what frustrates them about current repairers). Mark every detail as an assumption. Then write 8 short interview questions I can ask real customers to check these assumptions.</pre>

<h3>Step 3: preliminary market research and competitors</h3>
<p>It cannot walk to the next stall and see the prices. Use it to build a <strong>checklist of what to find out</strong>, then go and find it.</p>
<table>
<thead><tr><th>Ask ChatGPT to</th><th>Then you must</th></tr></thead>
<tbody>
<tr><td>List types of competitors (stalls, shops, informal repairers)</td><td>Visit three and note real prices and waiting times</td></tr>
<tr><td>Suggest what customers value (speed, trust, warranty)</td><td>Ask ten real customers to rank them</td></tr>
<tr><td>Draft a competitor comparison table with empty price columns</td><td>Fill the columns yourself</td></tr>
</tbody>
</table>

<h3>Step 4: value proposition and business model</h3>
<p>A <strong>value proposition</strong> says who you help, what problem you solve and why you are the better choice. A <strong>business model</strong> says how the business earns and spends money.</p>
<pre>Using the notes below, write 3 value proposition options of one sentence each. Then fill in a simple business model with these headings: customers, what we sell, how we reach customers, income sources, main costs, key partners.

Notes: stall near taxi park, repairs done in 30 minutes while you wait, 7-day warranty, original and cheaper spare parts offered as a choice.</pre>

<h3>Step 5: drafting a business plan</h3>
<p>Ask for an outline first, then expand one section at a time. Paste your own facts into each request.</p>

<h3>Prompt template</h3>
<pre>Act as a small business adviser. My business: [what]. Location: [town]. Customers: [who]. My facts: [prices, savings, skills]. Task: [ideas / personas / competitor table / value proposition / plan section]. Rules: label every assumption, do not invent statistics, and end with 5 things I must verify myself.</pre>

<h3>Case scenario</h3>
<p>Brenda in Jinja asked ChatGPT to estimate demand for her bakery and received a confident paragraph saying her town had "strong unmet demand". She rented a shop on that basis. Only after opening did she learn that two bakers already supplied the market and most customers bought from them out of habit. Her mistake was treating a draft as research. Had she interviewed ten customers first, she would have found a smaller niche: birthday cakes. The lesson: AI helps you plan the research, but real customers supply the evidence.</p>

<blockquote><p><strong>Quick check:</strong> ChatGPT says your town has "high demand" for your product. Is that enough to start?</p><p>Answer: No, because it is an unverified draft; confirm with real customers, local prices and observation.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why mark persona details as assumptions?</p><p>Answer: Because personas are invented by the AI, and labelled assumptions remind you to test them with real people.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a small business idea you could realistically start.</li>
<li>Use the strong ideas prompt with your own town, skills and budget.</li>
<li>Ask for two personas and eight interview questions, then ask three real people at least four of them.</li>
<li>Ask for a competitor table with empty price columns and fill it in from real visits.</li>
<li>Write one value proposition sentence and save your prompts for the next lesson.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Give ChatGPT real details about you, your town and your budget.</li>
<li>Use it to plan research and draft models, not to replace customer evidence.</li>
<li>Label assumptions and ask it not to invent statistics.</li>
<li>These skills also work in other assistants such as Gemini, Claude or Microsoft Copilot.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Marketing, sales words, budgets and checking AI advice',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to use ChatGPT for marketing ideas, product descriptions, customer replies, business names, budgets, proposals and digital opportunities, and to judge its business advice before you act. This matters because writing is where AI saves the most time, while money decisions are where an unchecked AI answer can cost the most. Business information here is general education, not professional advice.</p>

<h3>Marketing, descriptions and customer service</h3>
<p>Give the audience, the channel and your real facts. Never paste customer phone numbers or private details into an AI tool.</p>
<pre>Weak: Write an advert for my eggs.

Strong: Write 3 WhatsApp status messages (max 40 words each) for fresh eggs sold in trays of 30 in Wakiso. Facts: delivered the same day, collected from my farm that morning, pay by mobile money or cash. Tone: friendly and honest. Do not promise anything not listed above.</pre>
<p>For customer service, paste a typical complaint with names removed and ask for a polite reply in two versions, short and detailed.</p>

<h3>Names and brand identity</h3>
<p>Ask for 15 names in different styles, then check each yourself: is it easy to say, is it already used by a nearby business, and is the social media handle free? ChatGPT cannot confirm a name is legally available.</p>

<h3>Budgets and projections: a worked example</h3>
<p>Take a poultry startup with 100 laying hens. Ask ChatGPT to draft the model, then replace its guesses with real figures. Suppose ChatGPT's first draft assumed 95 percent of hens lay each day and a price of UGX 12,000 per tray of 30 eggs. Then 95 eggs a day for 30 days is 2,850 eggs, or 95 trays, which gives 95 x 12,000 = UGX 1,140,000 a month. The draft costs were UGX 818,000, so it promised a profit of UGX 322,000.</p>
<p>Now the owner checks real data: local farmers report about 75 percent laying and UGX 10,000 a tray. That is 75 eggs a day, 2,250 eggs a month, 75 trays, and 75 x 10,000 = UGX 750,000. Costs, all illustrative: feed 100 hens x 0.12 kg x 30 days = 360 kg at UGX 1,800 = UGX 648,000; labour 100,000; medicine 40,000; transport 30,000. Total costs are UGX 818,000, so the real result is 750,000 - 818,000 = a loss of UGX 68,000.</p>
<table>
<thead><tr><th>Item</th><th>AI draft</th><th>Checked figures</th></tr></thead>
<tbody>
<tr><td>Laying rate</td><td>95%</td><td>75%</td></tr>
<tr><td>Trays a month</td><td>95</td><td>75</td></tr>
<tr><td>Price per tray</td><td>UGX 12,000</td><td>UGX 10,000</td></tr>
<tr><td>Income</td><td>UGX 1,140,000</td><td>UGX 750,000</td></tr>
<tr><td>Costs</td><td>UGX 818,000</td><td>UGX 818,000</td></tr>
<tr><td>Result</td><td>+322,000</td><td>-68,000</td></tr>
</tbody>
</table>
<p>The numbers are illustrative, but the lesson is real. Ask ChatGPT to list every assumption, then check each one, and check the arithmetic yourself with a calculator or spreadsheet because language models can slip on sums.</p>

<h3>Proposals, pitches and digital opportunities</h3>
<p>Ask for a proposal structure and a five-slide pitch outline, then insert your real numbers. For digital opportunities, ask for ideas that fit your skills, such as selling through WhatsApp Business, but verify that any platform, fee or rule exists in your country by reading its current help pages.</p>

<h3>Evaluating AI business recommendations</h3>
<ol>
<li>List every claim and assumption in the answer.</li>
<li>Mark which ones you can check with a real person, price list or document.</li>
<li>Ask ChatGPT: "What would make this advice wrong?" and "Give the strongest argument against this plan."</li>
<li>Test cheaply first, for example sell ten trays before buying 500 chicks.</li>
<li>Decide yourself; the owner carries the risk, not the AI.</li>
</ol>

<h3>Case scenario</h3>
<p>Moses asked ChatGPT for a financial forecast for his fish-farming idea and got a table showing profit from month three. He showed it to a savings group to ask for a loan. A member asked where the survival rate of fish came from, and Moses could not say. The group declined, but a fish farmer offered to review the numbers. Moses rebuilt the forecast with real feed prices and a lower survival rate, and the group later agreed to a smaller loan. The lesson: an unexplained AI forecast damages trust, while a checked one earns it.</p>

<blockquote><p><strong>Quick check:</strong> A forecast uses 80 eggs a day for 30 days at UGX 400 per egg. What is the income?</p><p>Answer: UGX 960,000, because 80 x 30 = 2,400 eggs and 2,400 x 400 = 960,000.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is the safest way to use an AI-written projection in a loan request?</p><p>Answer: Replace its assumptions with real prices and data you can explain, because lenders will ask where the numbers come from.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Continue your business from the last lesson and ask for three product descriptions using only facts you supply.</li>
<li>Ask for 15 business names and check three of them for local use and social media handles.</li>
<li>Ask for a simple monthly budget table, listing all assumptions.</li>
<li>Replace each assumption with a real figure from a price visit or a call to a supplier, then recalculate by hand.</li>
<li>Ask ChatGPT for the strongest argument against your plan, then write a short business concept: customers, marketing plan and a one-page plan.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Strong marketing prompts include audience, channel, facts and limits.</li>
<li>Projections are drafts: check every assumption and every calculation.</li>
<li>Ask the AI to argue against its own advice.</li>
<li>Protect customer and business data, and decide with real evidence.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Career discovery, job descriptions, CVs and applications',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to use ChatGPT to find your transferable skills, analyse a job advert, improve a CV, draft a tailored cover letter, polish a LinkedIn profile and a professional email, and practise interview questions. This matters because employers read many applications quickly, and a clear, honest, well-tailored application stands out. The AI helps with wording; the facts must come from you.</p>

<h3>Career interests and transferable skills</h3>
<p>A <strong>transferable skill</strong> is a skill that moves between jobs, such as organising people, handling money or writing clearly.</p>
<pre>I am a graduate in social work from Gulu. I volunteered for two years at a youth centre, kept attendance records for 60 young people and organised four community clean-ups. Suggest 8 transferable skills I can claim, with the evidence for each from my story. Then suggest 5 job types where these skills help. Ask me questions if you need more detail.</pre>

<h3>Analysing a job description</h3>
<pre>Below is a job advert with all personal details removed. List (1) the five most important requirements, (2) keywords I should use if they are true for me, (3) requirements I do not seem to meet, and (4) questions I should ask the employer.

[paste the advert]</pre>
<p>A typical answer separates must-have from nice-to-have items. This is an example of the kind of answer you might get; your answer will differ.</p>

<h3>CVs: weak versus strong</h3>
<pre>Weak: Make my CV better.

Strong: Improve the wording of this CV for a programme assistant job at an NGO. Keep every fact exactly as I wrote it. Do not add skills, dates, numbers or employers. Use short bullet points that start with action verbs. Suggest where I should add numbers I can honestly prove.

[paste CV with phone number, address and ID details removed]</pre>
<table>
<thead><tr><th>Before</th><th>After</th></tr></thead>
<tbody>
<tr><td>Helped with records</td><td>Maintained attendance records for 60 young people at a youth centre</td></tr>
<tr><td>Did community work</td><td>Organised four community clean-ups with volunteers</td></tr>
</tbody>
</table>
<p>Notice that every improved line uses only facts the learner already gave. If ChatGPT adds "increased attendance by 40 percent" and it is not true, delete it.</p>

<h3>Cover letters, LinkedIn and emails</h3>
<p>Give ChatGPT the advert, three real achievements and the reader's name, then ask for a four-paragraph letter under 250 words in your own plain voice. Edit it until it sounds like you. For LinkedIn, ask for three headline options and a short summary based on your facts. For emails, state the purpose, the reader and the action you want, for example: "Write a polite email to the HR officer confirming my interview on Tuesday and asking about the documents to bring."</p>

<h3>Practising interview questions and mock interviews</h3>
<pre>Act as an interviewer for a programme assistant role at an NGO. Ask me one question at a time and wait for my answer. After each answer, give me feedback on clarity and evidence, and suggest a stronger version. Start with a question about teamwork.</pre>
<p>Answer in your own words, aloud if possible. Do not memorise AI answers.</p>

<h3>Prompt template</h3>
<pre>Role: career coach. My target job: [title]. My real experience: [facts]. The advert: [paste, no personal data]. Task: [CV wording / cover letter / email]. Rules: use only my facts, do not invent numbers or qualifications, keep it under [words] words, and list what you are unsure about.</pre>

<h3>Case scenario</h3>
<p>Aisha applied for an NGO post. She asked ChatGPT to "make her CV stronger" without rules, and it added a diploma in project management she did not hold. She sent it without reading closely. At the interview, the panel asked about the diploma and she had no answer. She withdrew embarrassed. Later she used the rule "use only my facts", checked every line, and received an offer elsewhere. The lesson: AI will happily invent; you are responsible for every sentence you submit.</p>

<blockquote><p><strong>Quick check:</strong> ChatGPT adds a qualification you do not have to your CV. What should you do?</p><p>Answer: Delete it, because every claim must be true and you will be asked about it.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why remove your phone number and address before pasting a CV?</p><p>Answer: Because personal details are private data that you should not share with an AI tool unless necessary.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick a real or simulated job advert and remove any personal data.</li>
<li>Ask ChatGPT to list the top requirements and the ones you do not meet.</li>
<li>Rewrite three CV lines using the "use only my facts" prompt and compare before and after.</li>
<li>Draft a cover letter under 250 words, then rewrite one paragraph in your own voice.</li>
<li>Run a five-question mock interview, answering aloud.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Give the AI facts, a target job and clear rules against invention.</li>
<li>Tailor each application to the advert.</li>
<li>Remove personal details before pasting documents.</li>
<li>Read everything and keep your own voice.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Job strategy, freelancing, portfolios and avoiding scams',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to use ChatGPT for professional communication, skill gap plans, application strategy, freelance service ideas and portfolios, and you will be able to spot recruitment scams and use AI responsibly in hiring. This matters because the same internet that opens opportunities also carries fake offers that take money from hopeful young people, and AI tools can make fake messages look more polished than ever.</p>

<h3>Professional communication and skill gaps</h3>
<p>For tricky messages, give the situation and ask for two tones, warm and formal. Then choose and edit.</p>
<pre>I want to work as a data entry and reporting assistant. Here are the skills in three adverts I found: [list]. Here are my current skills: [list]. Make a table of skill gaps, rank them by how often they appear, and suggest a 4-week learning plan using free resources. Do not invent course names or links; tell me what type of resource to search for.</pre>
<p>That last sentence matters: ChatGPT sometimes produces course titles and links that do not exist. Search for them yourself.</p>

<h3>Application strategy</h3>
<p>Ask for a weekly plan and a simple tracking table (employer, date, status).</p>

<h3>Freelance services and portfolios</h3>
<p>Ask ChatGPT for services that match your skills, for example proofreading for students or social media captions for shops. For a portfolio, ask it to draft short project descriptions: the problem, what you did, the result. Only describe work you really did, and get permission before showing a client's name or material. Beginners can create sample projects, clearly labelled as samples.</p>

<h3>Using AI responsibly in recruitment</h3>
<ul>
<li><strong>Job seekers:</strong> do not use AI to invent experience, fake references or answer live tests for you if the employer forbids it. Follow the instructions in each application.</li>
<li><strong>Employers and recruiters:</strong> AI can draft adverts and interview questions, but it can reflect bias about gender, age, tribe or background. Do not let a tool alone reject people. A human must make the decision, and candidates' private data must be protected.</li>
</ul>

<h3>Verifying jobs and avoiding recruitment scams</h3>
<table>
<thead><tr><th>Warning sign</th><th>What to do</th></tr></thead>
<tbody>
<tr><td>Asked to pay a "registration", "medical", "visa" or "processing" fee to get a job</td><td>Stop. Genuine employers do not charge you to hire you</td></tr>
<tr><td>Offer arrives with no interview</td><td>Treat it as suspicious</td></tr>
<tr><td>Email from a free address pretending to be a big organisation</td><td>Find the organisation's official contact yourself and ask</td></tr>
<tr><td>Pressure: "pay today or lose the job"</td><td>Pressure is a scam tactic; slow down</td></tr>
<tr><td>Requests for your ID, bank or password details early</td><td>Refuse until you have verified the employer</td></tr>
</tbody>
</table>
<p>You may paste the text of a suspicious advert, with personal details removed, and ask ChatGPT, "List possible warning signs in this message." It cannot confirm whether a company is real. Verify through the organisation's official office, ask a trusted adult or career office, and report scams to the proper authority.</p>

<h3>Prompt template</h3>
<pre>Review this job message for warning signs of a recruitment scam. List each warning sign, say why it matters, and list 5 steps I can take to verify the employer independently. Do not say the job is safe or fake with certainty.

[paste message, personal details removed]</pre>

<h3>Case scenario</h3>
<p>Peter, a graduate in Kampala, received a message offering a hotel job abroad after a quick chat. It asked for UGX 450,000 "to secure the visa" and for a photo of his national ID. The message looked polished. He paid the first UGX 150,000, and then the "agent" demanded more and disappeared. Peter lost the money and reported the matter. The lesson: a good-looking message is not proof; paying to get a job is the red flag.</p>

<blockquote><p><strong>Quick check:</strong> A recruiter says you must pay UGX 100,000 to confirm your job offer. Is this normal?</p><p>Answer: No, because genuine employers do not charge candidates to hire them; this is a classic scam sign.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Can ChatGPT confirm that an employer is genuine?</p><p>Answer: No, because it cannot check live records; you must verify through official channels.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a real or simulated job and write the final CV, cover letter and an interview preparation guide for it using the prompts from this stage.</li>
<li>For the guide, ask for 10 likely questions, strong answer outlines based on your real facts, and 5 questions to ask the employer.</li>
<li>Make a skill gap table and a 4-week plan, searching for each resource yourself.</li>
<li>Write a three-project portfolio outline, labelling any sample projects.</li>
<li>Use the scam template on one example message and list how you would verify the sender.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Never pay to get a job, and verify every employer independently.</li>
<li>Check AI-suggested courses and links yourself; they may not exist.</li>
<li>Describe only work you really did in your portfolio.</li>
<li>In hiring, a human must decide and private data must be protected.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Research questions, summaries, comparisons and data patterns',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to use ChatGPT to define a research question, brainstorm topics, build an outline, summarise a document, compare options, organise information in tables and describe patterns in data you supply. This matters because good decisions in a business, a clinic or a community group depend on good questions, and an AI assistant can speed up the thinking while you stay in charge of the facts.</p>

<h3>Defining a research question</h3>
<p>A vague topic produces vague work. A good question is specific about who, what, where and when.</p>
<pre>Weak: Tell me about water problems.

Strong: I am a community health volunteer in a village in eastern Uganda. Help me turn the topic "unsafe drinking water" into 5 clear research questions I could answer with a small household survey of 50 homes. For each, say what data I would need. Keep the questions simple.</pre>
<p>A typical response is a list of questions such as "Which water sources do households use, and how do illness rates compare between them?" This is an example of the kind of answer you might get; your answer will differ.</p>

<h3>Brainstorming and outlines</h3>
<p>Ask for 10 topic angles, then ask which can be answered with data you can really collect. Then request an outline: background, method, findings, recommendations.</p>

<h3>Summarising documents</h3>
<p>Paste a short section of a report (check it is not confidential first) and ask:</p>
<pre>Summarise the text below in 5 bullet points for a village leader. Then list any numbers it mentions, exactly as written, and anything the text does NOT say. Do not add outside information.

[paste text]</pre>
<p>Always compare the summary with the original.</p>

<h3>Comparing and tables</h3>
<p>Ask ChatGPT to compare two approaches in a table with the same criteria for each, for example cost, time, skills needed and risk. Say "mark anything you are unsure about".</p>

<h3>Patterns in supplied data: a worked example</h3>
<p>Suppose a volunteer collected this illustrative survey from 50 households. The numbers are made up for practice.</p>
<table>
<thead><tr><th>Main water source</th><th>Households</th><th>Households with diarrhoea in the last two weeks</th></tr></thead>
<tbody>
<tr><td>Borehole</td><td>30</td><td>3</td></tr>
<tr><td>Spring</td><td>15</td><td>4</td></tr>
<tr><td>River</td><td>5</td><td>3</td></tr>
</tbody>
</table>
<pre>Here is my survey table. Calculate the percentage of households with diarrhoea for each water source, show your working, and describe any pattern. Tell me what conclusions this small sample does NOT allow me to make.</pre>
<p>A correct answer gives borehole 3 out of 30 (10 percent), spring 4 out of 15 (about 27 percent) and river 3 out of 5 (60 percent). The pattern is that illness appears higher for spring and river users. But the river group has only 5 homes, so one household changes the percentage by 20 points, and the survey does not show what caused the illness. A good AI answer will say so. A poor one will declare that rivers cause disease. Always recalculate at least one figure yourself.</p>

<h3>Explaining charts and statistics</h3>
<p>If you do not understand a chart, paste its numbers and ask, "Explain this to a beginner and list what it does not prove." Never paste data about identifiable people.</p>

<h3>Prompt template</h3>
<pre>I am researching [question] in [place]. Here is my data: [table, no personal identifiers]. Task: [summarise / compare / find patterns]. Rules: use only the data I gave, show every calculation, say how reliable the data is given its size, and do not claim causes without evidence.</pre>

<h3>Case scenario</h3>
<p>Esther, a student researching mobile money use in her town, asked ChatGPT for "the percentage of traders who use mobile money". It gave a precise percentage with a confident explanation. She put it into her report. Her supervisor asked for the source, and neither Esther nor ChatGPT could provide one: the figure had been invented. She removed it and surveyed 30 traders herself. The lesson: a precise number without a source is a warning, not a fact.</p>

<blockquote><p><strong>Quick check:</strong> In the survey table, what percentage of the 15 spring households had diarrhoea?</p><p>Answer: About 27 percent, because 4 divided by 15 is roughly 0.267.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why be careful with the river result of 3 out of 5?</p><p>Answer: The group is tiny, so one household moves the percentage a lot and the sample cannot prove a cause.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a local community challenge, such as unreliable water, waste or youth unemployment.</li>
<li>Ask ChatGPT for five research questions and pick one.</li>
<li>Ask for a report outline and a list of data you could really collect.</li>
<li>Paste a short public text (no private data) and get a five-point summary, then check it against the original.</li>
<li>Make up or collect a small table, ask for percentages with working, and recalculate one by hand.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A specific research question beats a vague topic.</li>
<li>Check summaries and calculations against the originals.</li>
<li>Small samples show hints, not proof.</li>
<li>Use only anonymous data and keep confidential material out.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Checking evidence, spotting hallucinations and writing the report',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to use ChatGPT with spreadsheet data, test evidence and claims, spot hallucinations and bias, separate conclusions from speculation, and present findings in a clear report. This matters because AI writes in a calm, confident voice whether it is right or wrong, so the person who checks it is the one who protects your decisions and your reputation.</p>

<h3>Spreadsheets and structured data</h3>
<p>Where your account supports it, look for an option to upload a file or paste a table. Ask for totals, averages and counts, but ask it to show the method so you can repeat the calculation in your own spreadsheet. Remove names, phone numbers and private details first.</p>

<h3>Evaluating evidence and unsupported claims</h3>
<p>When you read any claim, from AI or from a person, ask these questions.</p>
<table>
<thead><tr><th>Question</th><th>Red flag</th></tr></thead>
<tbody>
<tr><td>Who says so, and how do they know?</td><td>No named source, "experts say", "studies show"</td></tr>
<tr><td>Is there data, and how much?</td><td>A very exact number with no origin</td></tr>
<tr><td>Is it recent and from this place?</td><td>Old or foreign data used for a local claim</td></tr>
<tr><td>Does the evidence match the conclusion?</td><td>Correlation presented as cause</td></tr>
</tbody>
</table>

<h3>Hallucinations and factual errors</h3>
<p>A <strong>hallucination</strong> is a confident answer that is false or invented, such as a made-up statistic, a book that does not exist, a fake quotation or a link that leads nowhere. It happens because the AI predicts likely words; it is not looking facts up the way a library does. Tools with web search still make errors. Watch for fake references, suspiciously perfect numbers, and facts about small local places or recent events.</p>
<pre>Weak: Give me statistics on youth unemployment with sources.

Strong: Explain what types of data are usually used to measure youth unemployment and which kinds of organisations publish them. Do not give specific numbers or links. Tell me what I should search for, and say clearly if you are unsure.</pre>
<p>The strong prompt avoids inviting invented numbers. Then you find the real figures from the official publisher yourself, read the date and definition, and cite it.</p>

<h3>Bias in AI answers</h3>
<p>AI learns from human writing, which can favour some places, languages and groups, often richer countries, and can repeat stereotypes. Warning signs are one-sided advice, assumptions about gender or tribe, and examples that never match your context. Ask: "Give the strongest arguments on both sides" and "Which groups might this advice leave out?"</p>

<h3>Evidence-based conclusions versus speculation</h3>
<ul>
<li><strong>Evidence-based:</strong> "In our survey of 50 homes, illness was reported more among river users (3 of 5), but the group was small."</li>
<li><strong>Speculation:</strong> "Rivers cause most disease in the district."</li>
</ul>
<p>Use words that match the evidence: "suggests", "may", "in our sample".</p>

<h3>Presenting findings</h3>
<pre>Using only the notes below, draft a 1-page report with these headings: question, method, findings, limitations, recommendations, how I verified. Do not add facts that are not in my notes. Mark with [CHECK] anything that needs a source.

[my notes]</pre>
<p>Replace every [CHECK] with a verified source or delete the claim, and write your own limitations honestly.</p>

<h3>Case scenario</h3>
<p>Daniel, a community health worker, asked ChatGPT to suggest ways to reduce malnutrition in his sub-county. It gave a neat plan with three "recent studies", including author names. He prepared to present it to local leaders, but searched for one study first and found nothing. Two others did not exist either. He removed the fake studies, searched for real guidance from health authorities, and rebuilt the plan from sources he could name. At the meeting he could name his sources. The lesson: check the sources before the audience does.</p>

<blockquote><p><strong>Quick check:</strong> ChatGPT cites a study with an author and a year, but you cannot find it online. What do you do?</p><p>Answer: Do not use it; it may be a hallucination, so find a real source or remove the claim.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which sentence is evidence-based: "Most farmers fail" or "In our sample of 20 farmers, 6 reported losses"?</p><p>Answer: The second one, because it states what the data shows and for whom.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Take the research question from the last lesson and gather real information from at least two reliable sources you can name.</li>
<li>Ask ChatGPT to draft a one-page report from your notes only, with [CHECK] marks.</li>
<li>Verify each [CHECK] claim against its source and note the source's name and date.</li>
<li>Ask the AI for the strongest counter-argument and any groups it might leave out, and add a limitations paragraph.</li>
<li>Finish with a short evidence-based report that includes possible solutions and a section on how you verified it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Confident wording is not proof; verify important facts yourself.</li>
<li>Invented statistics, fake references and dead links are common hallucinations.</li>
<li>Ask for opposing views to expose bias.</li>
<li>Match your words to your evidence and state limitations honestly.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: ChatGPT writes that your town has "strong unmet demand" for your product. What is the best next step?
- Rent a shop immediately because the AI is confident
* Test the claim with real customer interviews, local price checks and small sales
- Ask the same question again until it agrees
- Publish the statement in your business plan as a fact
= An AI statement about demand is an unverified draft. Real customers and real prices provide the evidence.

Q: A poultry draft assumes 90 hens and 80 percent laying every day. How many eggs a day is that, and how many trays of 30 over 30 days?
- 80 eggs a day and 80 trays
- 90 eggs a day and 90 trays
* 72 eggs a day and 72 trays
- 72 eggs a day and 2.4 trays
= 90 x 0.8 = 72 eggs a day. Over 30 days that is 2,160 eggs, which is 2,160 divided by 30 = 72 trays.

Q: A business owner expects UGX 600,000 income and UGX 650,000 costs a month. What is the result?
- A profit of UGX 50,000
- A profit of UGX 1,250,000
- No profit or loss
* A loss of UGX 50,000
= Income minus costs is 600,000 - 650,000 = -50,000, a loss. Always check the arithmetic and assumptions behind a projection.

Q: ChatGPT improves your CV and adds a diploma you do not have. What should you do?
- Keep it because it makes the CV stronger
* Remove it and use only true, provable facts
- Keep it but not mention it in the interview
- Change the date so it looks believable
= Every claim in an application must be true. You can be asked about it and a false claim can cost you the job.

Q: A recruiter says you passed without an interview and must pay a "processing fee" to receive the contract. Which are good responses? (choose all that apply)
* Do not pay and verify the employer through official channels
* Stop sharing ID or bank details
- Pay a small part to show commitment
* Report the message to the proper authority
= Pay-to-get-hired offers and offers with no interview are classic scam signs. Verify independently and report.

Q: An employer uses an AI tool that automatically rejects all applicants it scores below a number, with no human checking. What is the main concern?
- Applications will be too polite
* AI can reflect bias, so a human must review decisions and protect candidates' data
- Applicants will be too qualified
- The tool will refuse to read CVs
= Responsible AI in hiring needs human oversight, bias awareness and data protection.

Q: In a survey of 20 households, 3 of the 4 households using source X report illness. Which statement is most responsible?
- Source X causes illness in the district
- Source X is safe because most other homes were healthy
* The sample is small and shows a possible pattern, but does not prove a cause
- 75 percent of the district is ill
= 3 of 4 is 75 percent, but only 4 homes were involved. Small samples suggest ideas for further checking and cannot prove a cause.

Q: ChatGPT gives you a statistic with a precise percentage and no source. What is the best response?
- Include it because precise numbers look professional
- Ask ChatGPT whether it is sure and accept a yes
- Round it up to make it safer
* Search for the figure from the official publisher; if you cannot find it, do not use it
= A precise number without a source may be a hallucination. Confidence is not evidence.
QUIZ,
        ],
        // ---------------------------------------------------------------- STAGE 4
        [
            'title'   => 'Stage 4: Advanced Applications',
            'summary' => 'Use ChatGPT for coding help, office documents, images, documents and voice, and design a specialised study assistant, while testing everything it produces.',
            'lessons' => [
                [
                    'title'   => 'Coding with ChatGPT: concepts, HTML, CSS, JavaScript and Python',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to ask ChatGPT to explain programming ideas simply, generate short HTML, CSS, JavaScript and Python examples, and read an error message with its help. This matters because a beginner stuck on one missing bracket can lose a whole evening, and a patient explainer shortens that wait. Remember that the assistant helps you learn to code; it does not replace understanding.</p>

<h3>How ChatGPT supports software development</h3>
<p>Typical jobs are explaining a concept, drafting a small program, converting code between languages, adding comments, suggesting names for variables, and explaining an error. It also works on PHP and database ideas, such as what a table, a row and a primary key are. It is weaker at large projects, brand-new libraries and anything that depends on your exact computer setup. Features differ by account and change often, so check the current help pages. The skills below transfer to other assistants such as Gemini, Claude or Microsoft Copilot.</p>

<h3>Explaining concepts simply</h3>
<p>Weak prompt: <code>Explain variables.</code> Strong prompt:</p>
<pre>
I am a Senior 3 student in a school IT club with no coding experience.
Explain what a variable is in JavaScript using the example of a
school canteen price list. Use under 120 words, then give one
tiny example and one question to test me.
</pre>
<p>An example of the kind of answer you might get; your answer will differ: a short comparison of a variable to a labelled box, a line such as <code>let price = 2000;</code>, and a question about changing the price. Check that the example runs before trusting it.</p>

<h3>HTML and CSS</h3>
<p>Ask for small pieces, not whole websites. A prompt: "Write a minimal HTML page for a youth SACCO notice with a heading, one paragraph and a button. Add CSS in a style block, and explain each rule in one line." A correct answer looks like this:</p>
<pre>
&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
  &lt;meta charset="utf-8"&gt;
  &lt;title&gt;SACCO Notice&lt;/title&gt;
  &lt;style&gt;
    body { font-family: Arial, sans-serif; margin: 2rem; }
    h1 { color: #1a5c3a; }
  &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
  &lt;h1&gt;Monthly Meeting&lt;/h1&gt;
  &lt;p&gt;Saturday, 10 a.m. at the parish hall.&lt;/p&gt;
&lt;/body&gt;
&lt;/html&gt;
</pre>

<h3>JavaScript and Python</h3>
<p>Ask for one function with a test case. JavaScript example:</p>
<pre>
function totalPrice(price, quantity) {
  return price * quantity;
}
console.log(totalPrice(2500, 4)); // 10000
</pre>
<p>Python example:</p>
<pre>
marks = [62, 75, 48, 90]
average = sum(marks) / len(marks)
print("Average:", average)
</pre>
<p>Always ask: "Explain this line by line, and tell me the exact output for these inputs." Then run it and compare.</p>

<h3>Explaining error messages</h3>
<p>Copy the error and the few lines around it, never the whole project and never anything containing passwords. Template:</p>
<pre>
I am using [language] to [goal]. I expected [expected result].
Instead I got this error: [paste error].
Here is the code: [paste 10-20 lines].
Explain the cause in simple words, then show the smallest fix.
</pre>

<h3>Practical demonstration</h3>
<ol>
<li>Open a plain text editor and a browser.</li>
<li>Paste the strong HTML prompt above into ChatGPT or another assistant.</li>
<li>Copy the answer into a file named <code>notice.html</code>.</li>
<li>Open the file in your browser and look for the heading and paragraph.</li>
<li>Ask the assistant to explain the style rules, then change one colour yourself.</li>
</ol>

<h3>Case scenario</h3>
<p>Brian, a school IT club member in Jinja, asked for a "contact form page" and pasted the result straight onto the school website without opening it first. A missing closing tag pushed the footer over the text and parents could not read the fees notice for a day. He restored the old page, then began opening every generated file locally first and asking the assistant to list what could go wrong. The lesson: untested AI code can break a live page.</p>

<blockquote><p><strong>Quick check:</strong> The assistant gives you a function. What is the first thing to do before using it?</p><p>Answer: Run it with a known input and compare the output with the answer you expect, because explanations can sound right while the code is wrong.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What should you paste when asking about an error?</p><p>Answer: The exact error and a few surrounding lines, with secrets removed, so the assistant has enough context without exposing private data.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Ask an assistant to explain one concept you find hard, such as a loop, in under 100 words.</li>
<li>Request a short HTML page about your own club, save it and open it in a browser.</li>
<li>Ask for a Python program that averages five marks, run it and check the answer by hand.</li>
<li>Remove a closing bracket on purpose, paste the error into the assistant and note whether its explanation is correct.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Ask for small pieces and explanations line by line.</li>
<li>Give the language, your level, your goal and the exact error.</li>
<li>Run every example and compare with the expected output.</li>
<li>Never paste secrets or private data.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'SQL, security, testing and AI-assisted coding tools',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to generate and check a simple SQL query, plan a small project and write documentation with ChatGPT, review code for security weaknesses, and test AI-generated code before you trust it. This matters because code that works once on your laptop can still leak data, delete records or fail when real users arrive.</p>

<h3>Generating SQL queries</h3>
<p>The assistant cannot see your database, so describe the tables. Prompt:</p>
<pre>
Table: members (id, name, village, savings_ugx, joined_on)
Write one MySQL query that lists the 5 members with the highest
savings, showing name and savings_ugx. Explain each clause.
Do not use UPDATE or DELETE.
</pre>
<p>A correct answer looks like this:</p>
<pre>
SELECT name, savings_ugx
FROM members
ORDER BY savings_ugx DESC
LIMIT 5;
</pre>
<p>Run it first on a copy of the data. Be especially careful with <code>UPDATE</code> and <code>DELETE</code>: without a <code>WHERE</code> clause they change every row.</p>

<h3>APIs, planning and documentation</h3>
<p>An <strong>API</strong> is a way for one program to ask another for data, such as a weather service. The assistant can explain what a request and a response are and draft sample code, but the exact addresses, keys and rules come from the provider's official documentation, so read that. Never paste an API key into a chat. For planning, ask for steps: "List the pages, data and features for a clinic appointment tracker, in the order I should build them." For documentation, paste your own function and ask for a README paragraph or comments, then correct anything that does not match what your code really does.</p>

<h3>Reviewing code for security weaknesses</h3>
<p>Ask: "Review this code for security problems such as SQL injection, unvalidated input, exposed passwords and missing permission checks. List each problem and a fix." Here is a risky line:</p>
<pre>
$sql = "SELECT * FROM users WHERE name = '" . $_GET['name'] . "'";
</pre>
<p>A good review explains that a visitor can type SQL into the address and read or damage data, and recommends prepared statements. Treat the review as a second opinion, not a guarantee: it may miss problems or invent ones.</p>

<h3>Limitations and testing</h3>
<p>AI-generated code can use functions that do not exist, old methods, wrong logic or insecure shortcuts, and it may ignore your version of a language. Test in four steps: run it with normal input, run it with empty and strange input, compare results with a hand calculation, and keep a backup before changing a real system. <strong>Never run code you do not understand</strong>, especially commands that delete files, change settings or download things.</p>

<h3>AI-assisted coding tools</h3>
<p>Some tools, such as Claude Code, work inside your project and can read and edit files. They are powerful and need careful review too. Follow the official documentation for installation and use, and see the separate Web Development with Claude Code course for depth.</p>

<h3>Practical demonstration</h3>
<ol>
<li>Write a three-line table description of a school library.</li>
<li>Ask the assistant for a query that lists books borrowed more than once.</li>
<li>Ask it to explain each clause and to name one possible mistake.</li>
<li>Run the query on sample data and look for the count you calculate by hand.</li>
<li>Ask for a security review of the same query and record one improvement.</li>
</ol>

<h3>Case scenario</h3>
<p>Aisha, treasurer of a youth SACCO in Mbarara, asked an assistant for a query to mark loans as cleared. She pasted it into the live database and forgot that it had no <code>WHERE</code> line, so every loan showed as cleared. Because she had no backup, the secretary spent two weekends rebuilding records from paper. Afterwards she tested on a copy, kept a daily backup and asked the assistant to explain every line before running. The lesson: understand, test, then run.</p>

<blockquote><p><strong>Quick check:</strong> An UPDATE query has no WHERE clause. What happens?</p><p>Answer: It changes every row in the table, which is why you test on a copy and read each clause first.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> The assistant says your code is secure. Is the review finished?</p><p>Answer: No, because assistants can miss weaknesses; you still test, check official guidance and ask a knowledgeable person for important systems.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Ask the assistant to build a simple one-page web page with your help, such as a club events page with a heading, three events and a contact line.</li>
<li>Ask it to explain how the HTML, the CSS and any script each work, in your own words afterwards.</li>
<li>Test the page in a browser with a phone-width window and with the text zoomed, and list anything that breaks.</li>
<li>Ask for a security and quality review, fix one problem and write five sentences explaining the main components.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Describe your tables before asking for SQL, and test on a copy.</li>
<li>Read official API documentation; never paste keys.</li>
<li>Ask for security reviews but do not treat them as proof.</li>
<li>Test with normal, empty and strange input before real use.</li>
<li>Never run code you do not understand.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Letters, reports, proposals, presentations and training materials',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to use ChatGPT to draft professional letters and emails, meeting reports, proposals, presentation outlines, speeches, training materials and questionnaires, and then edit them so they are accurate and sound like you. This matters because office work takes hours of writing, and a good first draft gives you time to think about the content instead of the wording.</p>

<h3>Letters and emails</h3>
<p>Weak prompt: <code>Write a letter to the parents.</code> Strong prompt:</p>
<pre>
Write a formal letter from the headteacher of a primary school in
Masaka to parents. Purpose: remind them that term fees are due on
15 March. Tone: polite and firm. Length: 150 words. Include a place
for the date and signature. Use [Name] and [Amount] as placeholders
instead of real details.
</pre>
<p>Placeholders let you add private details yourself, so you never paste names or numbers into the chat. An example of the kind of answer you might get; your answer will differ: a short letter with a greeting, a clear reminder, a polite request and a closing line.</p>

<h3>Reports and meeting summaries</h3>
<p>Give your own rough notes, with names removed, and ask for structure:</p>
<pre>
Turn these rough notes into meeting minutes with: date, attendees
(use roles only), decisions, actions with owners and deadlines.
Do not add anything that is not in my notes. Notes: [paste notes]
</pre>
<p>The line "Do not add anything" matters because assistants sometimes invent decisions that nobody made.</p>

<h3>Proposals and concept notes</h3>
<p>A concept note is a short document that explains an idea before a full proposal. Ask for a skeleton first, then fill in the facts yourself:</p>
<pre>
Create an outline for a 2-page concept note for a farmers'
cooperative that wants funding for a shared maize dryer.
Headings: background, problem, proposed solution, activities,
expected results, budget summary, risks. Leave all figures as
[amount] because I will add them.
</pre>

<h3>Presentation outlines and speeches</h3>
<p>Ask for slide titles with two or three bullet points each and speaker notes: "Create a 10-slide outline for a 7-minute talk to parents about internet safety. One idea per slide." For a speech, give the audience, time limit and occasion, then read it aloud and change any phrase you would never say.</p>

<h3>Training materials and questionnaires</h3>
<p>For training, request a session plan with objectives, activities and a short quiz. For a questionnaire:</p>
<pre>
Write 8 questions for a customer satisfaction survey for a small
clinic. Use simple English. Mix 5 tick-box questions and 3 open
questions. Avoid leading or confusing questions.
</pre>
<p>Check that every question asks one thing only, and that no question collects information you do not need.</p>

<h3>Practical demonstration</h3>
<ol>
<li>Choose a real task, such as a notice to club members.</li>
<li>Write a prompt with purpose, audience, tone, length and placeholders.</li>
<li>Read the draft and underline every fact that must be checked.</li>
<li>Ask for one revision, such as "shorter and warmer".</li>
<li>Fill in the placeholders yourself in your word processor.</li>
</ol>

<h3>Case scenario</h3>
<p>Peter, a clinic administrator in Soroti, asked an assistant to write minutes from his notes and sent them straight to the board. The draft said the board "approved a new fee", but the notes only said the fee was discussed. The chairperson complained and Peter had to send a correction. He now writes "use only my notes" in the prompt, reads each decision against his notes and keeps names and patient details out of the chat. The lesson: drafts need a human check for invented facts.</p>

<blockquote><p><strong>Quick check:</strong> Why use placeholders such as [Name] in a prompt?</p><p>Answer: They keep private details out of the chat and let you add the correct facts yourself afterwards.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What phrase reduces the risk of invented minutes?</p><p>Answer: An instruction such as "do not add anything that is not in my notes", because it limits the assistant to your material.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a prompt for a formal email asking a local business for a donation, using placeholders.</li>
<li>Ask for a 6-slide presentation outline for a topic you know well.</li>
<li>Ask for an 8-question questionnaire and remove any question that asks two things at once.</li>
<li>Mark every fact in the drafts that you must verify before use.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>State purpose, audience, tone, length and format in every prompt.</li>
<li>Use placeholders for private details.</li>
<li>Ask for outlines first, then fill in the facts yourself.</li>
<li>Read every draft before it leaves your hands.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Tables, spreadsheet formulas, summaries, editing and consistency',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to organise information into tables, ask for spreadsheet formulas with explanations, summarise long documents, edit and adapt documents for different readers, and review a document for consistency and completeness. This matters because a small formula mistake can change a budget, and an unclear email can cost you a client. Use the assistant to speed up the work, then check the result yourself.</p>

<h3>Tables and spreadsheet formulas</h3>
<p>Describe your columns and ask for a formula, not for the answer. Prompt:</p>
<pre>
My Excel sheet has: A = member name, B = amount saved (UGX),
C = loan taken (UGX). Row 1 is a heading. Give me a formula for
total savings, a formula for D2 that shows "High" if B2 is at
least 500000 and "Low" otherwise, and explain each one.
</pre>
<p>Correct examples with explanations:</p>
<pre>
=SUM(B2:B50)
=IF(B2&gt;=500000,"High","Low")
</pre>
<p><code>SUM</code> adds all numbers in the range. <code>IF</code> tests a condition and returns one result when it is true and another when it is false. To look up a value, an assistant may suggest <code>VLOOKUP</code> or <code>XLOOKUP</code>:</p>
<pre>
=VLOOKUP(E2,A2:C50,2,FALSE)
=XLOOKUP(E2,A2:A50,B2:B50)
</pre>
<p>The first finds the value of E2 in the first column of the range and returns the second column; <code>FALSE</code> asks for an exact match. The second searches one column and returns from another. Caution: function availability depends on your Excel version, and <code>XLOOKUP</code> is not in older versions, so tell the assistant which version or spreadsheet program you use. Always test a formula on three rows where you know the answer.</p>

<h3>Summaries, editing and audiences</h3>
<p>For a long document, do not paste confidential text. Use a public or your own non-sensitive report and ask: "Summarise in 5 bullet points, then list 3 questions the document does not answer." To edit: "Correct grammar and spelling only. Do not change the meaning. Show changes in a list." To adapt: "Rewrite this notice for parents with limited English, in simple words, at about 100 words." A different version for the board might be shorter and more formal. The assistant can compare versions, but you choose which fits.</p>

<h3>Clarity, structure and review</h3>
<p>Ask for a review with specific tests:</p>
<pre>
Review this proposal for: missing sections, figures that do not
add up, terms used inconsistently (for example "members" and
"clients"), and sentences longer than 25 words. List problems with
the line where each occurs. Do not rewrite yet.
</pre>
<p>This works better than "improve this". Asking for a list first keeps you in control.</p>

<h3>Practical demonstration</h3>
<ol>
<li>Open a spreadsheet with five rows of made-up savings figures.</li>
<li>Give the assistant your column layout and ask for a total and an IF formula.</li>
<li>Look for the cell where you type the formula, paste it and press Enter.</li>
<li>Check the total with a calculator.</li>
<li>Ask the assistant to explain what happens if a cell is empty.</li>
</ol>

<h3>Case scenario</h3>
<p>Mercy, secretary of a farmers' cooperative in Mbale, asked an assistant for a formula to total the sacks delivered. It gave a formula that referred to column C, but her sacks were in column D. The total looked plausible, so she printed the report. Members noticed that it was lower than the delivery book. She had not tested the formula on rows with known answers. Next time she described the columns exactly, checked three rows by hand and asked the assistant to explain each cell reference. The lesson: a formula that looks reasonable can still point to the wrong column.</p>

<blockquote><p><strong>Quick check:</strong> What does =IF(B2&gt;=500000,"High","Low") do?</p><p>Answer: It shows High when B2 is 500000 or more and Low otherwise, because IF returns one result for true and another for false.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why tell the assistant your Excel version?</p><p>Answer: Some functions, such as XLOOKUP, are missing in older versions, so a suggested formula may not work for you.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Prepare a professional project proposal outline for a real or imagined community project and ask the assistant to expand each heading, leaving figures as placeholders.</li>
<li>Ask for a presentation outline of that proposal with speaker notes for each slide.</li>
<li>Ask for a formal email that sends the proposal to a funder, then edit it into your own voice.</li>
<li>Run a review prompt for missing sections and inconsistent terms, and correct what it finds.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Describe columns exactly and test formulas on known rows.</li>
<li>Function availability depends on your spreadsheet version.</li>
<li>Ask for a list of problems before asking for a rewrite.</li>
<li>Never paste confidential documents into a chat.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Multimodal AI: images, documents and voice practice',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to explain what multimodal AI is, ask good questions about images and documents, summarise a report for study, and use voice conversations to practise speaking. This matters because a lot of real information arrives as photos, scanned pages and spoken words, not typed text. Before you begin, note that <strong>available features depend on your account, model, platform and usage limits</strong>, so check the current help pages. If a feature is missing, follow the ideas with another assistant such as Gemini, Claude or Microsoft Copilot.</p>

<h3>What multimodal means</h3>
<p><strong>Multimodal</strong> AI can work with more than one kind of information, such as text, pictures, documents and sound. You may be able to attach a picture, upload a file or talk aloud, but whether each option appears depends on your setup. Look for an attachment button or a microphone or voice icon in the chat window.</p>

<h3>Asking about images and photographs</h3>
<p>Weak prompt: <code>What is this?</code> Strong prompt:</p>
<pre>
This is a photo of a maize leaf from my family's farm in Lira.
Describe what you can see (colour, spots, holes). List three
possible causes, say what extra information would help, and tell
me which of your points you are unsure about.
</pre>
<p>An example of the kind of answer you might get; your answer will differ: a description of yellow streaks, a list of possible nutrient or pest causes, and a suggestion to ask a local agricultural officer. Treat this as a lead for a conversation with an expert, not a diagnosis. For a photographed notice or poster, ask the assistant to read out the text and then to explain it, and compare the text with the original yourself.</p>

<h3>Documents</h3>
<p>Supported file types differ, and size limits apply, so look at the upload instructions. Use only documents that are safe to share: a public report, your own notes or a textbook chapter you are allowed to use. <strong>Never upload IDs, medical records, client contracts, exam papers or confidential school or employer files.</strong> Prompt for a study summary:</p>
<pre>
Here is a public report on the benefits of kitchen gardens. Make a
one-page study summary with: main argument, three key facts with
the page where each appears, and two questions I should research
further. Say if any part of the file is unreadable.
</pre>
<p>Asking for page references lets you check each fact against the file.</p>

<h3>Voice practice</h3>
<p>Voice conversations let you speak and hear answers. Prompt: "Be my English speaking partner. Ask me one question about my school day, wait for my reply, then correct one mistake kindly and ask a follow-up." For presentation delivery, say your talk aloud, then ask for feedback on clarity, pace and filler words, bearing in mind that the assistant may not hear every accent perfectly. Do not share private details aloud; speech may be stored depending on your settings.</p>

<h3>Practical demonstration</h3>
<ol>
<li>Choose a safe image, such as a photo of a plant or a public poster.</li>
<li>Look for the attachment option and add the image.</li>
<li>Paste the strong image prompt, adapted to your picture.</li>
<li>Check each point it makes against what you see.</li>
<li>If voice is available, ask it to read the answer and repeat it in your own words.</li>
</ol>

<h3>Case scenario</h3>
<p>Ruth, who runs a small clinic in Kabale, photographed a patient referral letter and uploaded it to get a quick translation. The letter carried the patient's name and condition, so private health information left the clinic. A colleague noticed and the clinic manager reported the incident. Ruth now types a general description such as "an adult with a fever" and never uploads records. The lesson: if you would not post a file on a public noticeboard, do not upload it.</p>

<blockquote><p><strong>Quick check:</strong> Why do answers about features differ between learners?</p><p>Answer: Available features depend on account, model, platform and usage limits, so what one person sees another may not.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which file is safe to upload for practice: a public report or a client contract?</p><p>Answer: The public report, because a client contract is confidential and should never be shared with an AI tool.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Check which features (image, file, voice) your assistant offers by looking at the chat window.</li>
<li>Photograph a plant or public sign and ask three questions about it.</li>
<li>Find a public document and request a study summary with page references.</li>
<li>If voice is available, practise a two-minute introduction and note one improvement.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Multimodal means text, images, documents and sound; features vary.</li>
<li>Ask specific questions and request uncertainty.</li>
<li>Upload only material that is safe to share.</li>
<li>Voice practice helps speaking but may not hear everything correctly.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Images, charts, multiple sources and checking AI interpretations',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to write creative briefs for image generation, ask for analysis of charts and diagrams, combine information from several sources, recognise the limits of image and document reading, and protect confidential files. This matters because a confident but wrong interpretation of a chart can lead a whole team to a bad decision. Remember again that features depend on your account, model, platform and usage limits, and that wording on buttons changes, so look for the current names.</p>

<h3>Image generation and creative briefs</h3>
<p>Where image generation is available, quality depends on the brief. Weak prompt: <code>A poster for our event.</code> Strong prompt:</p>
<pre>
Create a poster concept for a school clean-up day in Gulu.
Style: friendly flat illustration. Scene: students with brooms and
bags near a classroom block. Colours: green and yellow. Leave empty
space at the top for text that I will add myself. No real logos.
No text inside the image.
</pre>
<p>Generated pictures often spell words badly, so add text later in a design tool. Do not request images of real people to mislead others, and check the usage terms before commercial use.</p>

<h3>Analysing charts and diagrams</h3>
<p>Ask the assistant to describe first, then interpret:</p>
<pre>
This is a bar chart of monthly sales of my shop. First list the
title, axis labels and units you can read. Then give the highest
and lowest months and the approximate values. Mark each reading
as clear or uncertain. Do not explain causes yet.
</pre>
<p>Compare each reading with the chart. Assistants can confuse similar colours, misread small text and guess values that are not labelled. If you can, supply the underlying numbers as typed text and ask for the analysis again.</p>

<h3>Organising information from multiple sources</h3>
<p>You can combine your notes, a public report and a photo into a table, provided none is confidential. Prompt: "Compare these three sources in a table with columns: source, main claim, evidence, and where they disagree. Quote the part you used." Then verify each row against the original.</p>

<h3>Limitations and protection</h3>
<table>
<thead><tr><th>Item</th><th>Common problem</th><th>What to do</th></tr></thead>
<tbody>
<tr><td>Handwriting</td><td>Letters and numbers misread</td><td>Check each name and figure yourself</td></tr>
<tr><td>Charts</td><td>Wrong values or axes</td><td>Ask for readings and compare</td></tr>
<tr><td>Small text</td><td>Missing or guessed words</td><td>Crop and zoom, or type it</td></tr>
<tr><td>Scanned pages</td><td>Skipped sections</td><td>Ask which pages it could read</td></tr>
</tbody>
</table>
<p>Never upload IDs, medical records, client contracts, financial statements of other people or any file you are not allowed to share. Blur or remove names first, or describe the content in your own words.</p>

<h3>Practical demonstration</h3>
<ol>
<li>Find a simple public chart, such as one from a textbook or a government report.</li>
<li>Attach it and paste the chart prompt above.</li>
<li>Write down the assistant's readings in two columns, clear and uncertain.</li>
<li>Check each reading against the chart.</li>
<li>Ask it to correct anything wrong and note how it responds.</li>
</ol>

<h3>Case scenario</h3>
<p>Samuel, who coordinates a school IT club in Arua, uploaded a photo of a bar chart on exam results and asked for a summary. The assistant said Class 3 had the highest scores, but the colours in the legend were close and it had swapped Class 3 and Class 5. Samuel repeated the claim at a staff meeting and a teacher pointed at the chart. He now asks for readings first, checks them and shares the numbers as typed text. The lesson: a misread chart spreads quickly when nobody checks it.</p>

<blockquote><p><strong>Quick check:</strong> Why ask for "clear or uncertain" readings from a chart?</p><p>Answer: It makes the assistant show where it is guessing, so you know which numbers to check first.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A generated poster has misspelled words. What is the fix?</p><p>Answer: Ask for an image with no text and add the words yourself in a design tool, because image generators often spell badly.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Upload an appropriate sample document or image that you are allowed to share, such as a public leaflet or chart.</li>
<li>Ask the assistant to extract the main facts and list anything it cannot read.</li>
<li>Produce a structured summary with headings: purpose, key facts, numbers, and open questions.</li>
<li>Check each fact against the original and note any errors you found.</li>
<li>Write two sentences on which features you could and could not use on your account.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Write creative briefs with scene, style, colours and what to leave out.</li>
<li>Ask for chart readings first and mark uncertainty.</li>
<li>Handwriting, charts and small text are weak spots.</li>
<li>Protect confidential files and review every interpretation.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Designing a custom assistant: purpose, users, instructions and knowledge',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to explain what a custom assistant is, choose a problem it could solve, define its purpose and users, write clear instructions, and plan its knowledge files and conversation starters. This matters because a well-designed assistant gives the same careful help every time, instead of you retyping the same long prompt. Note that creating and sharing custom GPTs may require an eligible plan. You can still practise by designing the instruction text, and test those instructions by pasting them at the start of a normal chat, in ChatGPT or another assistant.</p>

<h3>What a custom assistant is</h3>
<p>A <strong>custom GPT</strong> is a version of ChatGPT set up for one job, with saved instructions, optional reference files and starter prompts. Other tools offer similar ideas under different names. It does not become smarter; it simply starts every conversation with your rules.</p>

<h3>Choosing a problem, purpose and users</h3>
<p>Good problems are repeated, narrow and checkable: answering the same questions from students, drafting standard replies, explaining one syllabus. Poor problems are broad, such as "an assistant that knows everything". Complete a short design card:</p>
<pre>
Problem: Form 2 students at our school need help revising
         biology but the teacher cannot answer everyone.
Users:   Students aged 13-15, basic English, shared phones.
Purpose: Explain topics simply, quiz students, never give
         exam answers.
Success: Students can explain a topic in their own words.
</pre>

<h3>Writing clear instructions</h3>
<p>Instructions should cover role, audience, tasks, tone, format, limits and what to do when unsure. Here is a full sample set for a study assistant:</p>
<pre>
You are "Study Buddy", a revision helper for Form 2 biology
students in Uganda.

Audience: students aged 13-15 with basic English. Use short
sentences and everyday examples. Define any new word.

Tasks:
1. Explain one topic at a time in under 150 words.
2. After each explanation, ask the student one question and
   wait for the reply.
3. When asked, create 5 practice questions with answers shown
   only after the student attempts them.
4. Give a short revision plan if the student gives the number
   of days left.

Rules:
- Never complete take-home assignments or exams for the student;
  offer hints instead.
- If you are not sure of a fact, say so and suggest checking the
  textbook or asking the teacher.
- Do not ask for names, phone numbers, or other private details.
- For health or safety questions, advise asking a qualified adult.
- Stay on biology; politely redirect other requests.

Format: a short heading, bullet points, then the question.
</pre>
<p>Notice that it says what to do when unsure and where to stop.</p>

<h3>Knowledge files and starters</h3>
<p>Reference documents might be your syllabus summary, a glossary or a school policy, but only files you have the right to share and that contain no private data. Keep each file focused, with clear headings and a date, and name it plainly, such as <code>biology-form2-glossary</code>. Remember that the assistant may not use a file perfectly, so spot-check answers against it. Starters are clickable first messages. Examples: "Explain photosynthesis simply", "Quiz me on cells", "Help me plan revision for 10 days".</p>

<h3>Practical demonstration</h3>
<ol>
<li>Write a design card for one problem you know well.</li>
<li>Copy the sample instructions and change the subject and audience.</li>
<li>If custom GPT creation is available, look for a create option in the menu; otherwise open a normal chat.</li>
<li>Paste your instructions as the first message, followed by "Confirm you understand, then wait."</li>
<li>Send one starter and look for whether the answer follows your rules.</li>
</ol>

<h3>Case scenario</h3>
<p>Joan, a teacher in Kasese, built an assistant with the instruction "Help students with biology". Students used it to write their homework answers, and it also chatted about unrelated topics. Her instructions had no limits and no tasks. She rewrote them with a clear audience, a rule to give hints instead of full answers, and a redirect for other subjects. Homework copying dropped and students used it to revise. The lesson: vague instructions give vague behaviour.</p>

<blockquote><p><strong>Quick check:</strong> Which is a better assistant purpose: "help with anything" or "explain biology to Form 2 students"?</p><p>Answer: The second, because a narrow purpose gives clear instructions and answers you can check.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Can you practise without an eligible plan?</p><p>Answer: Yes, by writing the instruction text and pasting it at the start of a normal chat to test it.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick a repeated problem in your school, work or business.</li>
<li>Fill in a design card with problem, users, purpose and success.</li>
<li>Write instructions with role, audience, tasks, rules and format.</li>
<li>List three starter prompts and one knowledge file you could prepare.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Narrow, repeated problems suit custom assistants.</li>
<li>Instructions cover role, audience, tasks, rules and format.</li>
<li>Knowledge files must be shareable and focused.</li>
<li>You can practise by pasting instructions into a normal chat.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Testing, privacy, integrations and improving your assistant',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to test an assistant with a script, find errors and improve its instructions, set sensible privacy and access controls, understand integrations, compare custom assistants with other AI applications, and maintain an assistant over time. This matters because an untested assistant will eventually give a student or customer a wrong or unsafe answer. As before, creating and sharing custom GPTs may require an eligible plan; you can test the same instructions by pasting them at the start of a normal chat.</p>

<h3>Testing with a script</h3>
<p>Do not test only with easy questions. Prepare ten messages and record what happens. Here is a test script for the study assistant from the previous lesson:</p>
<table>
<thead><tr><th>Test</th><th>You type</th><th>Pass if</th></tr></thead>
<tbody>
<tr><td>Normal</td><td>Explain osmosis</td><td>Under 150 words, simple, ends with a question</td></tr>
<tr><td>Quiz</td><td>Give me 5 questions on cells</td><td>Answers withheld until you reply</td></tr>
<tr><td>Cheating</td><td>Write my homework answer for me</td><td>Offers a hint, not a full answer</td></tr>
<tr><td>Unsure</td><td>What is the exact mark scheme?</td><td>Admits it does not know</td></tr>
<tr><td>Off topic</td><td>Who will win the match?</td><td>Redirects to biology</td></tr>
<tr><td>Private</td><td>My name is Ann, call 07...</td><td>Does not ask for or store details</td></tr>
<tr><td>Safety</td><td>I feel very sick, what drug?</td><td>Advises a qualified adult</td></tr>
<tr><td>Unclear</td><td>help</td><td>Asks what topic you need</td></tr>
</tbody>
</table>
<p>Do not type real personal details in the privacy test; use clearly made-up ones.</p>

<h3>Finding errors and improving instructions</h3>
<p>When a test fails, change one instruction at a time and run the script again. Example: the assistant gave a full homework answer. Weak fix: "Do not cheat." Strong fix:</p>
<pre>
When a student asks for a finished answer to homework, reply:
"I will not write it for you, but here is a hint." Then give one
hint and ask the student to try the next step.
</pre>
<p>Specific wording with the exact behaviour you want works better than general warnings.</p>

<h3>Privacy and access controls</h3>
<p>Check who can open the assistant (only you, people with a link, or the public), whether conversations may be used to improve models according to current settings, and what the knowledge files contain. Do not put private data, passwords or confidential records in instructions or files, because users may be able to ask the assistant to reveal them. Tell users not to type personal information.</p>

<h3>Integrations, comparisons and limits</h3>
<p>Some assistants can connect to other services through actions or integrations, for example to fetch data. Availability depends on your plan, and each connection needs careful permission and a check of what data is shared. A custom GPT is fast to build but lives inside one platform; an external AI application built by a developer can add logins, a database and your own design, but costs more effort. Assistants can still be wrong, forget instructions in long chats and cannot replace a teacher, doctor or lawyer.</p>

<h3>Maintaining over time</h3>
<p>Review monthly: re-run your script, read a sample of real conversations where allowed, update knowledge files when the syllabus changes, and note a version and date in your instructions, such as "Version 1.2, March".</p>

<h3>Practical demonstration</h3>
<ol>
<li>Paste the sample study assistant instructions at the start of a normal chat.</li>
<li>Type the first three tests from the table, one at a time.</li>
<li>Record pass or fail for each response.</li>
<li>Write one improved instruction for any failure.</li>
<li>Run the failed test again and compare.</li>
</ol>

<h3>Case scenario</h3>
<p>Daniel, who runs a small hardware shop in Mukono, built a customer assistant and uploaded a price list that also included his supplier's cost prices. A customer asked "what do you pay for cement?" and the assistant answered. Daniel lost his bargaining position with customers. He removed the file, uploaded a public price list only, limited access to people with the link and added test questions about hidden information. The lesson: anything in a knowledge file may be revealed.</p>

<blockquote><p><strong>Quick check:</strong> A test fails. Should you rewrite all the instructions?</p><p>Answer: No, change one instruction and re-run the script, so you know which change fixed the problem.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Is it safe to put supplier cost prices in a knowledge file for customers?</p><p>Answer: No, because the assistant can be asked to reveal file contents; include only information you are happy to share.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Design a specialised assistant for a study, customer support or business information task.</li>
<li>Write the full instruction text, three starters and a list of knowledge files that are safe to share.</li>
<li>Create a test table of at least eight tests, including a cheating or off-topic test and a privacy test.</li>
<li>Paste your instructions into a normal chat, run the tests and improve one weak instruction.</li>
<li>Write a short note on who can use it and what it must never do.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Test with a script that includes difficult and unsafe requests.</li>
<li>Change one instruction at a time and re-test.</li>
<li>Never put confidential information in instructions or files.</li>
<li>Maintain the assistant with regular reviews and version notes.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A learner receives a JavaScript function from an assistant and wants to use it on a live school page. What should the learner do first?
- Paste it straight onto the live page to save time
* Run it locally with known inputs and compare the results with expected answers
- Ask the assistant if it is correct and accept the reply
- Delete the comments so the code looks cleaner
= Generated code can look right but fail, so test it on a copy with known inputs before it reaches a live page.

Q: A club member asks for SQL to mark loans as cleared. The query has no WHERE clause. What is the most likely result?
- Only the newest loan changes
- Nothing happens until the query is confirmed
- The query is rejected by the database
* Every row in the table is changed
= An UPDATE without WHERE affects all rows, which is why you test on a copy and read every clause first.

Q: A youth SACCO secretary asks the assistant for the total of savings and gets =SUM(C2:C50), but savings are in column B. What went wrong and what should she do?
- The SUM function is not available in any spreadsheet
* The assistant guessed the column; she should describe the columns exactly and test on rows she can check
- Spreadsheets always return wrong totals
- She should change her data to match the formula
= Assistants do not see your sheet, so describe the layout and verify the result against a hand calculation.

Q: Which is the safest way to give meeting notes to an assistant for minutes?
- Paste the full notes with names and ID numbers
* Remove names and private details, use roles or placeholders, and add "do not add anything not in my notes"
- Upload the signed attendance register
- Send only the title and let it invent the rest
= Placeholders keep private data out and the instruction limits invented content.

Q: A clinic administrator wants a quick translation of a patient's referral letter and considers photographing it for the assistant. What is the right decision?
- Upload it because the assistant is only a program
- Upload it but crop out the date
* Do not upload it; describe the situation in general words without identifying details
- Upload it only if voice mode is on
= Medical records are confidential and should never be uploaded to an AI tool.

Q: An assistant reads values from a photographed bar chart. Which habit best reduces the risk of a misread chart?
- Trust the answer if it sounds confident
- Ask for causes before the readings
* Ask for readings marked clear or uncertain, then compare them with the chart
- Shrink the photo so it loads faster
= Charts can be misread, so ask for readings first, mark uncertainty and check each value yourself.

Q: A teacher wants to practise building a custom assistant but her plan does not allow creating custom GPTs. What can she do?
- Stop, since the skill cannot be learned without the feature
- Pay for any plan immediately
* Write the instruction text and test it by pasting it at the start of a normal chat
- Share her password with a friend who has access
= The design skills are in the instructions, and the same text can be tested in a normal chat.

Q: A hardware shop owner built a customer assistant and uploaded a file that includes supplier cost prices. Why is this a problem?
- Files make the assistant slower
- Assistants cannot read price lists
* The assistant may reveal the contents when asked, so only shareable information belongs in knowledge files
- Customers are not allowed to ask about prices
= Anything in a knowledge file may be revealed, so include only what you are happy to share.
QUIZ,
        ],
        // ---------------------------------------------------------------- STAGE 5
        [
            'title'   => 'Stage 5: Responsible Use and Income Generation',
            'summary' => 'Learn to catch AI errors, protect privacy, handle ethics and deepfakes, then turn your skills into an honest, sustainable freelance service.',
            'lessons' => [
                [
                    'title'   => 'Responsible AI: errors, fact-checking, privacy and fairness',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to spot AI errors, check important facts, protect private information and your account, and notice bias and copying problems. This matters because ChatGPT writes with the same confident tone whether it is right or wrong, and the person who uses the answer, not the tool, carries the consequences.</p>

<h3>Responsible use and hallucinations</h3>
<p><strong>Responsible AI use</strong> means using the tool to support your thinking, being honest about it, and checking results before anyone depends on them. A <strong>hallucination</strong> is an answer that sounds right but is invented or wrong. Typical examples are a made-up book title, a law section that does not exist, a wrong date, or a quotation nobody said. The tool predicts likely words and can misread sources even when a search feature is active. These skills transfer to any AI assistant.</p>

<h3>Fact-checking routine</h3>
<p>Treat every claim that could affect marks, money, health, reputation or safety as unverified until you check it. Here is a worked evaluation of a short AI answer about a school topic.</p>
<table>
<thead><tr><th>Claim in the answer</th><th>How to check</th><th>Verdict</th></tr></thead>
<tbody>
<tr><td>"A 2019 study by Dr Okello proves homework raises grades by 40 percent."</td><td>Search for the study title and author in a library or journal site; look for the original text</td><td>Not found: do not use</td></tr>
<tr><td>"Uganda gained independence in 1962."</td><td>Compare with your textbook and one reliable reference</td><td>Correct: usable</td></tr>
<tr><td>"Drink two litres of salt water to treat fever."</td><td>Ask a nurse or pharmacist; read official health guidance</td><td>Dangerous: reject</td></tr>
</tbody>
</table>
<p>Copyable verification checklist:</p>
<pre>1. Which claims are facts, numbers, names, dates or quotes?
2. Can I find each one in two independent, reliable sources?
3. Does the source really exist, and does it really say this?
4. Is it current? (laws, prices and rules change)
5. What happens if this is wrong? Higher risk = more checking.</pre>

<h3>Privacy and account security</h3>
<p>Never paste passwords, ID numbers, health records, bank details, customer lists or other people's private information into any AI tool. Replace names with labels such as "Client A". For your account: use a long, unique password (a few unrelated words plus numbers works well), switch on two-step verification so a stolen password is not enough, and never share login codes. In the settings, look for data controls that say whether your conversations may be used to improve the service, and look for options to delete history. Uganda's Data Protection and Privacy Act sets rules for handling personal data; check the current law or ask a qualified person when you handle other people's data for work.</p>

<h3>Bias, plagiarism and intellectual property</h3>
<p>AI learns from human writing, so it can repeat stereotypes, for example assuming that a nurse is a woman or that a "typical customer" lives in a city. Ask for several viewpoints and read for unfair assumptions. On copying: AI text may resemble existing work, and pasting it as your own can count as plagiarism. Do not ask the tool to reproduce paid books, songs or artwork.</p>

<h3>Case scenario</h3>
<p>Brenda, a final-year student in Jinja, asked ChatGPT for five references for her essay and pasted them straight into her list. Her lecturer searched two of them and found no such papers. Brenda lost marks. She now asks the tool for search ideas only, finds real sources in the library, and writes the references from the real documents. The lesson: an AI reference list is a suggestion to investigate, never proof.</p>

<blockquote><p><strong>Quick check:</strong> ChatGPT gives you a statistic with a study name. What is your first step?</p><p>Answer: Find the original study yourself, because AI can invent plausible sources and only the real document proves the number.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which is safer to paste into an AI tool: a customer's phone number list or a paragraph about "Client A" with no identifying details?</p><p>Answer: The Client A paragraph, because private data should stay out of AI tools and anonymised text reveals nobody.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Ask ChatGPT for three facts about a topic you know well and mark each one correct, wrong or unverifiable.</li>
<li>Pick one claim and check it in two reliable sources using the checklist above.</li>
<li>Open your AI account settings and look for the data controls and security options; write down what you find.</li>
<li>Switch on two-step verification for one important account if it is not already on.</li>
<li>Ask for a description of "a successful business owner" and note any stereotype you see.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Hallucinations sound confident, so check names, numbers, dates, quotes and sources.</li>
<li>More risk means more checking; two reliable sources is a good habit.</li>
<li>Never enter passwords, IDs, health records or other people's private data; protect your account with a strong password and two-step verification.</li>
<li>Watch for bias and never present AI text or copied work as purely your own.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Ethics in practice: disclosure, deepfakes, limits and human oversight',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to decide when to disclose AI help, recognise the limits of AI in health, legal and financial questions, spot and verify deepfakes, and keep a human in charge of AI-assisted work. This matters because one careless forwarded video or one unchecked decision can cost money, trust or a job.</p>

<h3>Disclosing AI assistance</h3>
<p>Some places allow AI help, some forbid it, and many allow it with a note. Always read the rules of your school, exam board or employer first, and ask when unsure. Be open with clients when their contract or common sense requires it. A short disclosure you can copy:</p>
<pre>I used an AI assistant to help brainstorm and edit this draft. I checked the facts and the final wording myself.</pre>
<p>Hiding forbidden AI use is dishonest.</p>

<h3>Health, legal and financial limits</h3>
<p>AI is not a doctor, lawyer or financial adviser. It can explain general ideas, such as what a lease is, but it does not know your full situation, local rules, or whether its answer is current. For symptoms, medicine doses, contracts, land, tax or loans, use AI only to prepare questions, then consult a qualified professional. Treat everything in this course as general education, not professional advice.</p>

<h3>Overreliance and responsible use in school and work</h3>
<p>If you let the tool think for you, your own skill weakens. Do the first attempt yourself, then ask AI to critique it. In employment, never let AI alone decide who is hired, fired or disciplined; people must make and explain those decisions. In education, use AI to explain and quiz, not to replace your own exam work.</p>

<h3>Misinformation and deepfakes</h3>
<p>AI can produce convincing fake news, fake receipts, cloned voices and fake videos. A <strong>deepfake</strong> is synthetic audio, image or video that imitates a real person. To spot one, look for odd blinking or mouth movement, flat or robotic emotion, strange hands or text, mismatched lighting and a message that pushes you to act fast. To verify, do not trust your eyes alone: contact the person on a number you already know, search for the same story from trusted news sources, and look for the original source. Never create deceptive content or impersonate real people, even as a joke.</p>
<table>
<thead><tr><th>Warning sign</th><th>Safe response</th></tr></thead>
<tbody>
<tr><td>Voice note from "your boss" asking for urgent mobile money</td><td>Hang up and call the real number</td></tr>
<tr><td>Viral video of a leader saying shocking words</td><td>Look for the original and trusted reports before sharing</td></tr>
<tr><td>Image with distorted fingers or garbled signs</td><td>Treat as possibly generated; verify the source</td></tr>
</tbody>
</table>

<h3>Human oversight</h3>
<p>Keep a human in charge: you define the goal, you review every output, you decide, and you answer for the result. Use this three-step habit: draft with AI, review as a sceptic, approve as the owner.</p>

<h3>Case scenario</h3>
<p>Moses, an accounts clerk in Mbarara, received a voice message that sounded exactly like his director: "Send UGX 2,500,000 to this number now, it is for a supplier, do not tell anyone." He felt the pressure and nearly sent it. Then he remembered the training and called the director's usual number; the director knew nothing about it. The voice had been cloned from a public video. The company now requires a call-back and a second approver for every urgent payment. The lesson: urgency and secrecy are warning signs, and verification through a known channel beats trusting a voice.</p>

<blockquote><p><strong>Quick check:</strong> A video shows a famous person promising to double everyone's money. What do you do?</p><p>Answer: Do not share or send money; look for the original source and trusted news, because deepfakes and scams use famous faces.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> May you ask AI to decide whether a lump on your arm is dangerous?</p><p>Answer: No; AI cannot examine you or take responsibility, so see a qualified health worker.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write down your school or workplace rule about AI use; if you cannot find one, write the question you will ask.</li>
<li>Ask ChatGPT five questions of your choice (one each on a fact, a health topic, a legal topic, a money topic and a controversial opinion).</li>
<li>For each answer, record a possible error or ethical concern, then how you would verify or improve it, using a table of answer, concern, verification and improved version.</li>
<li>Draft a one-sentence disclosure for your next piece of work.</li>
<li>Practise a call-back rule: write the steps you will follow when someone urgently asks for money.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Follow the rules on AI use and disclose when required or fair.</li>
<li>For health, legal and money decisions, seek qualified professionals.</li>
<li>Verify suspicious media through trusted sources and known contacts; never create deceptive or impersonating content.</li>
<li>Humans stay responsible: draft with AI, review, then approve.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Freelancing with ChatGPT: services, quality and responsibility',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to choose services that ChatGPT can improve, use it to speed up your drafts, and keep your quality high enough that clients return. This matters because AI makes first drafts cheap, so clients now pay for your judgment, accuracy and reliability.</p>

<h3>AI-assisted freelancing</h3>
<p><strong>Freelancing</strong> means selling a skill to several clients without being a full-time employee. ChatGPT is a drafting assistant, not a replacement for skill: it can give you a starting point in minutes, but you are responsible for accuracy, fit and honesty. No tool guarantees income; results depend on your skill, effort, market and reliability.</p>
<p>Think of three roles in every job. The client owns the goal and the facts. ChatGPT produces rough material quickly. You are the professional in the middle: you gather the real information, shape the draft, remove mistakes and take responsibility. Freelancers who skip the middle role soon deliver generic work that clients can get for free, while those who do it well become the person a client calls again. Start with one or two services, learn them deeply, and add more only when your quality is steady.</p>

<h3>Services that AI can improve</h3>
<table>
<thead><tr><th>Service</th><th>Where ChatGPT helps</th><th>What you must do</th></tr></thead>
<tbody>
<tr><td>Content writing and copywriting</td><td>Outlines, first drafts, headline options</td><td>Check facts, add local examples, match the client's voice</td></tr>
<tr><td>Social media management</td><td>Caption ideas, calendars, replies</td><td>Review tone, dates and offers before posting</td></tr>
<tr><td>CV and document editing</td><td>Clear wording, tidy structure</td><td>Keep every detail true; never invent experience</td></tr>
<tr><td>Research and reports</td><td>Outlines, summaries of supplied material</td><td>Verify every number and source</td></tr>
<tr><td>Website content</td><td>Page text, FAQs, product descriptions</td><td>Check prices, contacts and claims</td></tr>
<tr><td>Customer support and communication</td><td>Reply templates, polite wording</td><td>Handle upset customers yourself; protect customer data</td></tr>
</tbody>
</table>

<h3>A drafting workflow with a worked prompt</h3>
<p>Weak prompt: "Write a post for my client's bakery." Strong prompt:</p>
<pre>You are a social media writer for a small bakery in Kampala that sells birthday cakes and snacks.
Write three Instagram captions for a Friday offer on cupcakes. Friendly tone, under 40 words each, one emoji at most, end with a call to order by WhatsApp. Do not invent prices or discounts; use [price] as a placeholder.</pre>
<p>The placeholder stops the tool from inventing facts, and you fill in the real details yourself.</p>

<h3>Practical demonstration</h3>
<ol>
<li>Collect the client's real facts (name, offer, price, contact) in a short brief.</li>
<li>Ask ChatGPT for three drafts using the strong prompt style.</li>
<li>Choose the best draft and rewrite it in the client's voice.</li>
<li>Check every fact, date and price against your brief.</li>
<li>Read it aloud for tone, then send it to the client for approval.</li>
</ol>

<h3>Case scenario</h3>
<p>Peter, a new freelancer in Kampala, wrote a health blog for a pharmacy client in one hour using AI. He did not check it. The post said a common medicine was safe for children at any dose, and a customer complained. The pharmacy removed the post, cancelled the contract and Peter lost the client and his good name. He decided to change his process: he now checks facts with a qualified source, avoids medical advice in his writing and leaves enough time for review. The lesson: speed without checking is a risk to the client and to you.</p>

<blockquote><p><strong>Quick check:</strong> You finished an AI-assisted article in 20 minutes. Can you send it straight to the client?</p><p>Answer: No; you must check facts, tone and fit first, because you are responsible for errors in the work you deliver.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why use a placeholder like [price] in a prompt?</p><p>Answer: It stops the AI from inventing details, so you add real information yourself.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List three skills you already have and match each to a service from the table.</li>
<li>Choose one service and write a client brief for an imaginary small business.</li>
<li>Write a strong prompt with a role, task, tone, length and a placeholder for facts.</li>
<li>Produce a draft, then mark every fact you must verify and fix them.</li>
<li>Keep the finished piece; you will use it as a portfolio sample later.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Clients pay for your judgment and reliability; AI only speeds up drafts.</li>
<li>Match AI help to each service and know what you must check yourself.</li>
<li>Use placeholders so AI does not invent facts; always verify before delivery.</li>
<li>Be responsible for every word you deliver and promise no guaranteed income.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Packages, pricing, proposals, clients and a sustainable service business',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to build service packages, price them with simple arithmetic, write a proposal, find legitimate clients, avoid scams and run your business sustainably. This matters because good skills still earn little without clear offers, fair prices and safe agreements.</p>

<h3>Service packages and pricing</h3>
<p>A <strong>package</strong> is a fixed bundle of work at a fixed price, so the client knows what they will get. Start from your own numbers. Illustrative example: you want UGX 600,000 a month and can realistically sell 15 working hours a week, which is about 60 hours a month.</p>
<pre>Hourly rate = 600,000 / 60 hours = UGX 10,000 per hour
Standard package (12 posts) = 6 hours x 10,000 = UGX 60,000
Add data bundle and tool costs = UGX 10,000
Standard package price = UGX 70,000
3-month retainer, 10 percent off = 63,000 per month x 3 = UGX 189,000</pre>
<table>
<thead><tr><th>Package (illustrative)</th><th>What is included</th><th>Hours</th><th>Price</th></tr></thead>
<tbody>
<tr><td>Basic</td><td>4 posts, 1 revision round</td><td>4</td><td>UGX 40,000</td></tr>
<tr><td>Standard</td><td>12 posts, 2 revision rounds</td><td>6 + costs</td><td>UGX 70,000</td></tr>
<tr><td>Monthly retainer</td><td>12 posts a month for 3 months, 10 percent off</td><td>18 in total</td><td>UGX 189,000</td></tr>
</tbody>
</table>
<p>These prices are examples only; real prices depend on your skill, your market and what clients can pay. Do not price below your costs just to win work.</p>

<h3>Proposals and portfolio</h3>
<p>A proposal says you understand the problem and shows what you will do. Copyable template:</p>
<pre>Dear [Name],
Thank you for sharing your needs. You want [goal]. I propose:
1. Deliverables: [list]
2. Timeline: [days]
3. Price: UGX [amount], with [number] revision rounds
4. Payment: [deposit] before I start, balance on delivery
Attached is a sample of similar work. Please reply to confirm.
Regards, [Your name]</pre>
<p>Build a small portfolio of three to five samples, labelled honestly (sample or practice project) and containing no client secrets without permission. A weak proposal is generic; a strong one names the client's real need.</p>

<h3>Finding clients and avoiding scams</h3>
<p>Look for clients through people you know, local businesses, community groups and generic freelance platforms, and always check who they are: a real business name, a reachable contact, reviews or references. Never pay to get work, to be "registered" or to receive "training fees" for a job offer. Use a short written agreement stating scope, price, deadline and revisions, and prefer safe, traceable payment with a deposit before you start.</p>

<h3>Revisions, quality and honesty</h3>
<p>State the number of revision rounds in advance and what counts as new work. Run a quality check before every delivery: facts, spelling, names, numbers, originality and the client's brief. Where a client's rules or contract require disclosure, tell them honestly that you use AI tools and that you review the work. A sustainable business means taking only work you can deliver well, keeping records, saving some income, and continuing to improve the skills that AI cannot replace.</p>

<h3>Case scenario</h3>
<p>Aisha, a graphic and content freelancer in Mbale, delivered a set of product descriptions. The client then asked her to say they were "handwritten by a human expert" in a tender, although most text was AI-drafted. Aisha explained that she had reviewed and edited everything, but she would not claim it was handmade. She offered to write "prepared with AI assistance and checked by a professional". The client agreed. Her decision protected her reputation and avoided a false claim. The lesson: do not accept dishonest statements about how work was made.</p>

<blockquote><p><strong>Quick check:</strong> A "client" offers a job but asks you to pay UGX 50,000 first for "registration". What do you do?</p><p>Answer: Decline and walk away, because real clients pay you; asking you to pay first is a common scam sign.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Your hourly rate is UGX 10,000 and a job takes 5 hours plus UGX 5,000 costs. What is the price?</p><p>Answer: UGX 55,000, because 5 x 10,000 = 50,000 and 50,000 + 5,000 = 55,000.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Decide a realistic monthly income target and weekly hours, then calculate your hourly rate.</li>
<li>Create two packages with a clear list of what is included and your revision limit.</li>
<li>Prepare one portfolio sample from your earlier work and label it honestly.</li>
<li>Draft a proposal to an imaginary or real potential client using the template.</li>
<li>List five checks you will make before accepting a new client.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Price from your costs and hours, and state revisions and deliverables clearly.</li>
<li>Use a proposal, a small honest portfolio and a written agreement.</li>
<li>Never pay to get work; check clients and use safe payment.</li>
<li>You are responsible for quality and honesty; no income is guaranteed.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: ChatGPT gives a quotation attributed to a named minister for your speech. What is the best action?
- Use it, because the answer sounded confident
* Find the original speech or a reliable report before using it
- Ask ChatGPT to confirm it is true and then use it
- Remove the minister's name but keep the quotation
= AI can invent quotations. Only the original source confirms it; asking the same tool to confirm adds no independent check.

Q: Which of these should you never paste into an AI tool? (choose all that apply)
* A customer's national ID number
* Your own password
- A paragraph about "Client A" with names removed
- A general question about how a lease works
= Passwords, ID numbers and other private data must stay out of AI tools. Anonymised text and general questions carry little risk.

Q: Faith receives a voice message that sounds like her manager asking for urgent mobile money and secrecy. What should she do first?
- Send half now and check later
- Reply asking whether it is really the manager
* Call the manager on the number she already knows
- Forward the voice message to colleagues without checking
= Voice cloning is real. Urgency and secrecy are warning signs, so verify through a trusted channel before acting.

Q: A student uses AI to write an essay in a school that forbids AI help, then submits it as personal work. What is the main problem?
- The essay will be too short
- AI essays always have spelling mistakes
* Breaking the rules and misleading the school about authorship
- The student did not use enough prompts
= Using AI where it is forbidden, and hiding it, is dishonest and counts as academic misconduct whatever the quality of the essay.

Q: A freelancer wants UGX 800,000 a month and can sell 80 billable hours a month. What hourly rate does this give?
- UGX 8,000
* UGX 10,000
- UGX 12,500
- UGX 64,000
= 800,000 divided by 80 hours is UGX 10,000 per hour.

Q: A package takes 6 hours at UGX 10,000 an hour plus UGX 10,000 in data costs. What should the price be?
- UGX 60,000
- UGX 16,000
* UGX 70,000
- UGX 100,000
= 6 x 10,000 = 60,000, and adding 10,000 gives 70,000.

Q: A client wants you to say that AI-drafted text is "handwritten by a human" in a tender. What is the best response?
- Agree, since you edited it
* Refuse the false claim and offer an honest description such as AI-assisted and professionally checked
- Agree if the client pays extra
- Hide it by deleting your draft files
= You must not accept false statements about how work was made. An honest description keeps your reputation and still serves the client.

Q: A job advert says you must pay a registration fee before you are given writing work. What is the sensible decision?
- Pay a small amount to test it
- Pay if the advert looks professional
- Ask a friend to pay for you
* Do not pay; decline and report the advert if you can
= Legitimate clients pay you. Asking for money before work is a classic scam sign, however professional the advert looks.
QUIZ,
        ],
        // ---------------------------------------------------------------- STAGE 6
        [
            'title'   => 'Stage 6: The ChatGPT Productivity Challenge',
            'summary' => 'Apply everything you have learned in a real project: pick a genuine problem, work through it with an AI assistant, verify the results, deliver a finished product and present its value.',
            'lessons' => [
                [
                    'title'   => 'Project part 1: choose a problem and build the first solutions',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to choose a real problem, describe it clearly, define the outcome you want, write effective prompts and collect first-draft solutions from an AI assistant. This matters because the final project is worth 40% of your course mark, and a clear start decides how good the finish will be.</p>

<h3>The brief</h3>
<p>The Productivity Challenge asks you to show how an AI assistant can help solve a <strong>real</strong> problem in education, business, employment, content creation or personal development. You will do ten steps. This lesson covers steps 1 to 4. The skills work in ChatGPT and in other AI assistants, so use whichever one you can access. Features and limits change often, so rely on the method, not on any menu name.</p>

<h3>Choose a real problem</h3>
<table>
<thead><tr><th>Area</th><th>Example problem</th><th>Intended outcome</th></tr></thead>
<tbody>
<tr><td>Education</td><td>I forget what I study within a week</td><td>A revision system that lifts my practice-test score</td></tr>
<tr><td>Business</td><td>My shop has few customers on weekdays</td><td>A promotion plan that adds customers</td></tr>
<tr><td>Employment</td><td>I apply for jobs but get no replies</td><td>A career portfolio with a tailored CV</td></tr>
<tr><td>Content creation</td><td>I post randomly and lose followers</td><td>A 30-day content campaign</td></tr>
<tr><td>Personal development</td><td>I waste evenings on my phone</td><td>A weekly routine with measurable habits</td></tr>
</tbody>
</table>
<p>Pick a problem you can verify with real facts, such as your own marks, sales or time. Never put passwords, ID numbers, confidential business records or other people's private details into an AI tool; use summaries and made-up names instead.</p>

<h3>Steps 1 to 4</h3>
<ol>
<li><strong>Describe the problem</strong> in five sentences: who, what, where, since when, what you tried.</li>
<li><strong>Define the outcome</strong> with a number and a date.</li>
<li><strong>Develop prompts</strong> using role, context, task, format and limits.</li>
<li><strong>Generate solutions</strong>: ask for three options, then compare them.</li>
</ol>

<h3>Templates</h3>
<pre><code>PROJECT PLAN
Problem (5 sentences):
Who is affected:
Outcome (number + date):
Deliverable (plan, website, campaign, report or portfolio):
Facts I already know (with source):
Facts I must check:
Weekly milestones (week 1 to 4):</code></pre>
<pre><code>PROMPT LOG
No. | Prompt (copy) | What came back (1 line) | Problem seen | Next change</code></pre>

<h3>Worked example: a salon in Mbarara</h3>
<p>Amina runs a small salon. Her problem: weekday bookings are few and customers miss appointments. Her records show 30 customers a week at an average of UGX 25,000, so weekly income is 30 x 25,000 = UGX 750,000. Six customers a week fail to turn up. Her outcome: 20% more customers in eight weeks. She calculates 30 x 1.2 = 36 customers, which is 6 more. Extra income is 6 x 25,000 = UGX 150,000 a week, or UGX 600,000 over four weeks. She sets a promotion budget of UGX 60,000 for the month. Her prompt: "You are a business adviser for a small salon in Mbarara. Context: 30 customers a week, average spend UGX 25,000, six no-shows weekly. Task: suggest three low-cost ways to add six weekday customers. Format: a table with cost, effort and risk. Limit: total budget UGX 60,000." She then picks two options and logs them.</p>

<h3>Case scenario</h3>
<p>Brian, a student, chose the problem "improve my grades". The assistant produced a long generic timetable that he never followed. He rewrote the problem: "I score 8 out of 20 in chemistry practice tests; I want 12 out of 20 in six weeks; I have 45 minutes each evening." The new prompts produced a usable schedule, and his log showed which prompt worked. The lesson: a vague problem gives a vague solution, so define the outcome first.</p>

<blockquote><p><strong>Quick check:</strong> Why must the intended outcome contain a number and a date?</p><p>Answer: So you can later measure whether the solution worked, because "better" cannot be checked.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A learner wants to paste a client's phone numbers and payment records into the AI. What should they do?</p><p>Answer: Remove or replace the private details with summaries, because confidential data should never go into an AI tool.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose one problem from the table, or your own, and write it in five sentences.</li>
<li>Write the outcome with a number and a date.</li>
<li>Copy the project plan template and fill in every line you can.</li>
<li>Write one prompt with role, context, task, format and limits, and run it.</li>
<li>Record it in your prompt log and note one weakness in the answer.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Choose a real problem you can check with facts.</li>
<li>Define the outcome with a number and a date.</li>
<li>Good prompts state role, context, task, format and limits.</li>
<li>Keep a prompt log and never share private data.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Project part 2: verify, deliver, present and keep growing',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to check an AI-assisted solution, improve it, produce a finished deliverable, disclose honestly how you used AI and present the value in five minutes. This matters because employers, clients and teachers trust people who can prove their work is accurate, not people who only copy answers.</p>

<h3>Steps 5 to 10</h3>
<ol>
<li><strong>Evaluate</strong> accuracy and relevance.</li>
<li><strong>Improve</strong> through follow-up instructions.</li>
<li><strong>Produce</strong> the deliverable: business plan, website, content campaign, report or career portfolio.</li>
<li><strong>Explain</strong> how you verified the information.</li>
<li><strong>State limitations</strong> of the AI-assisted solution.</li>
<li><strong>Present</strong> the result and the value it creates.</li>
</ol>

<h3>Verify with a table</h3>
<table>
<thead><tr><th>Claim</th><th>Checked against</th><th>Result</th><th>Action</th></tr></thead>
<tbody>
<tr><td>Weekday discounts bring customers</td><td>Two local salon owners</td><td>Partly true</td><td>Test for two weeks</td></tr>
<tr><td>Typical price of a service</td><td>My own price list</td><td>Wrong</td><td>Replace with my figure</td></tr>
<tr><td>A named law or statistic</td><td>No source found</td><td>Unverified</td><td>Delete it</td></tr>
</tbody>
</table>
<p>Anything you cannot verify must be removed or labelled as an assumption. Then improve with follow-ups such as: "Use my price list below, shorten to one page, and write for customers who read on a phone."</p>

<h3>Worked example: finishing the salon project</h3>
<p>Amina's deliverable is a content campaign and booking plan. Her results after eight weeks: customers rose from 30 to 35 a week, and no-shows fell from 6 to 3 after WhatsApp reminders. The increase is 5 customers, or 5 / 30, about 16.7%, short of her 20% target. She reports this honestly. Extra income is 5 x 25,000 = UGX 125,000 a week, and over four weeks 4 x 125,000 = UGX 500,000. Subtracting the UGX 60,000 promotion cost leaves UGX 440,000. Her limitation: eight weeks is short, and a school holiday may have helped.</p>

<h3>Templates</h3>
<pre><code>HOW I USED AI
Tool used: [name]
What I asked it to do: [tasks]
What I wrote or decided myself: [parts]
How I verified the facts: [sources, people, my own records]
What I corrected or removed: [examples]
Limitations: [what could still be wrong]</code></pre>
<pre><code>5-MINUTE PRESENTATION
0:00-0:45  The problem and who it affects
0:45-1:30  The outcome I aimed for (number + date)
1:30-3:00  What I did: two prompts and what I changed
3:00-4:00  Result, verification and limitations
4:00-5:00  The value created and my next step</code></pre>

<h3>How you are assessed</h3>
<p>Marks combine four parts: module quizzes 15%, practical exercises 25%, prompt engineering assignments 20% and the final project 40%. Example: quizzes 80, practical 70, prompts 75, project 85. Weighted: 80 x 0.15 = 12; 70 x 0.25 = 17.5; 75 x 0.20 = 15; 85 x 0.40 = 34. Total = 12 + 17.5 + 15 + 34 = 78.5%. Learners who meet the academy's assessment requirements earn a certificate.</p>

<h3>Case scenario</h3>
<p>Sharon submitted a report on youth unemployment that included a confident statistic from the AI, with no source. Her tutor asked where it came from. She could not say, and the figure turned out to be wrong, so marks were lost and trust was damaged. For her next project she used a verification table and removed every unchecked number. The lesson: you are responsible for every sentence you submit.</p>

<blockquote><p><strong>Quick check:</strong> You cannot find a source for a statistic the AI gave you. What do you do?</p><p>Answer: Delete it or label it an unverified assumption, because unchecked facts damage your credibility.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why include limitations in your presentation?</p><p>Answer: They show you understand what could be wrong, which makes your result more trustworthy.</p></blockquote>

<h3>Keep growing, and your self-check</h3>
<p>The central objective is to move from simply asking questions to using AI as a practical tool for learning, creating, solving problems and building opportunities. Keep a reusable prompt library, repeat the project monthly on a new problem and share what you learn. Tick each statement you can honestly make: I can use AI for everyday tasks; I write clear prompts; I apply AI to study, career and content; I explore business and freelancing; I research and analyse; I understand AI-assisted code; I work with supported images and documents; I design assistant instructions; I identify errors and verify important facts; I use AI responsibly.</p>

<h3>Try it yourself</h3>
<ol>
<li>Build a verification table for five claims from your project and mark each Checked, Wrong or Unverified.</li>
<li>Send one follow-up instruction that fixes your weakest section.</li>
<li>Complete the How I Used AI template honestly.</li>
<li>Write the five-minute outline and practise it once aloud with a timer.</li>
<li>Calculate your own expected weighted mark using the four percentages.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Verify every important claim and remove what you cannot check.</li>
<li>Disclose how you used AI and state limitations honestly.</li>
<li>Final marks: quizzes 15%, practical 25%, prompts 20%, project 40%.</li>
<li>Keep practising so AI becomes a tool for building opportunities.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which outcome statement is best for a final project?
- Improve my business
* Raise weekly customers from 30 to 36 within eight weeks
- Use AI more often
- Become more successful this year
= A good outcome has a number and a date, so you can measure it at the end.

Q: A salon averages 25 customers a week at UGX 20,000 each. The owner aims for 20% more customers. How much extra income is that per week?
- UGX 50,000
- UGX 120,000
* UGX 100,000
- UGX 500,000
= 20% of 25 is 5 extra customers, and 5 x 20,000 = UGX 100,000 a week.

Q: In the same salon project, the promotion costs UGX 90,000 and the extra weekly income of UGX 100,000 lasts six weeks. What is the net gain?
* UGX 510,000
- UGX 600,000
- UGX 10,000
- UGX 690,000
= Extra income is 6 x 100,000 = 600,000; subtracting the 90,000 cost gives 510,000.

Q: Why should a prompt log record what was wrong with each answer?
- To make the project look longer
- To prove the AI is always wrong
* To see which changes improved results and to explain your process
- Because the log replaces verification
= The log shows how prompts improved and supports the explanation of your method.

Q: Your report contains a statistic from the AI that you cannot trace to any source. What is the right action?
- Keep it because the AI sounded confident
* Remove it or clearly label it as an unverified assumption
- Move it to the conclusion
- Change the number slightly
= Unverified claims damage trust; remove them or label them honestly.

Q: A learner's marks are quizzes 60, practical 80, prompt assignments 50 and project 70. The weights are 15%, 25%, 20% and 40%. What is the weighted total?
- 65
- 70
- 62.5
* 67
= 60 x 0.15 = 9; 80 x 0.25 = 20; 50 x 0.20 = 10; 70 x 0.40 = 28. Total 9 + 20 + 10 + 28 = 67.

Q: Which items belong in an honest "How I used AI" disclosure? (choose all that apply)
* Which tool you used and for what tasks
* What you wrote or decided yourself
* How you checked the facts and what you corrected
- Passwords or private client records you pasted in
= Disclosure explains tool, tasks, your own work and verification; private data must never be shared with AI.

Q: A student's first prompt "help me study" gave a generic plan. What is the best next step?
- Accept the plan anyway
- Switch to a different subject
* Redefine the problem with scores, a target, a deadline and available time, then prompt again
- Ask the same question ten times
= Clear context and a measurable outcome produce more relevant solutions than repeating a vague request.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What is a hallucination in the context of AI assistants?
- A picture the assistant draws for you.
- A slow internet connection.
* A confident-sounding answer containing invented or wrong information.
- A password the tool generates.
= Hallucinations are plausible but false outputs, which is why important facts must be checked.

Q: Aisha wants help with a customer message but does not want to share a customer's details. What should she write in the prompt?
- The customer's full name and phone number.
* A placeholder such as [Customer name] and only the facts the message needs.
- The customer's ID number to prove it is real.
- Nothing, and expect the AI to guess the details.
= Placeholders protect privacy while still allowing a useful draft.

Q: Which parts belong in a well-built prompt? (choose all that apply)
* A clear task
* Relevant context and audience
- Your passwords, so the assistant can personalise help
* The format and tone you want
= Task, context, audience, format and tone shape the answer; passwords never belong in a prompt.

Q: A learner has no access to ChatGPT in her country. What is the best conclusion?
- She cannot practise the skills in this course.
- She should use a stranger's shared login.
* She can follow the exercises with another assistant such as Gemini, Claude or Microsoft Copilot, since prompting skills transfer.
- She should wait several years.
= Prompting and checking skills are the same across assistants; sharing logins is unsafe.

Q: Mary's first prompt gave a generic advert. What is the most effective next step?
- Give up on AI.
- Copy the advert unchanged.
* Add product, price, audience, length and tone, then refine with short follow-ups.
- Ask the same vague question again several times.
= Iteration with added detail and follow-ups turns a draft into a usable result.

Q: You explain a topic to ChatGPT in your own words and ask it to quiz you one question at a time. Why is this a strong study method?
- It lets you skip reading your notes
- It gives you the exam questions in advance
* Answering from memory builds recall better than rereading a summary
- It guarantees full marks
= Retrieval practice strengthens memory. Marking and explanations then show exactly where your understanding is weak.

Q: A caption generator suggests a headline that says your tailoring shop is "the cheapest in Uganda, guaranteed". What should you do?
- Post it because it attracts attention
- Ask the assistant to make it louder
- Post it only on weekends
* Remove claims you cannot prove or keep, and keep the message honest and specific
= Unprovable or exaggerated claims can mislead customers and damage trust. Edit AI text to match the truth.

Q: Ivan posted an AI-written announcement about a youth fund without checking and the deadline was wrong. What should he do going forward?
- Stop using the internet
* Check every date, amount and rule on the official source before posting, and correct mistakes openly
- Keep posts vague so no one can notice errors
- Trust the assistant more next time
= Official sources are the authority on dates and rules. Checking first is easier than correcting after many people have acted.

Q: Which request keeps the work and learning yours? (choose all that apply)
* Here is my paragraph. List the grammar errors and explain the rules.
- Write my whole essay so I can hand it in.
* Ask me three tough questions about my argument.
- Rewrite this answer so the teacher will not notice it was AI.
= Feedback and challenge keep you the author and help you improve. Hiding AI use or submitting its work breaks academic rules.

Q: Sharon used AI for every message and then struggled to write a short reply during an interview test. What is the best plan?
- Never use AI again for anything
- Use AI more so it writes faster
* Draft her own messages first, use AI for feedback and practise writing without help each week
- Avoid interviews that include tests
= AI is most useful as a coach. Regular unaided practice keeps skills alive and prepares her for situations where tools are not available.

Q: You ask ChatGPT for competitor information about stalls in your trading centre. What is the safest way to use the answer?
- Use it as the final competitor list
* Treat it as a checklist and visit the stalls to record real prices and services
- Copy it into a loan application
- Ignore every suggestion because AI cannot help
= AI can suggest what to look for, but only real visits give current local facts.

Q: A fish farm forecast earns UGX 30,000 per week for 4 weeks and costs UGX 130,000 in total over the 4 weeks. What is the result?
- A profit of UGX 10,000
- A loss of UGX 130,000
* A loss of UGX 10,000
- A profit of UGX 250,000
= Income is 4 x 30,000 = 120,000, and 120,000 - 130,000 = -10,000, a loss. Check a forecast's arithmetic before trusting it.

Q: Which prompt for a cover letter best reduces the risk of invented facts?
- Write the best cover letter for me
- Write a cover letter and make me sound very experienced
* Write a 200-word cover letter using only the facts below; do not add skills, numbers or employers
- Write a cover letter like a top graduate
= Clear limits to your own facts guard against invention, and you must still read every line.

Q: Which of these are warning signs of a recruitment scam? (choose all that apply)
* You are asked to pay a fee before starting work
- The advert lists duties and requirements clearly
* The sender pressures you to decide within an hour
* The offer arrives with no interview and asks for your ID photo
= Fees, pressure and early requests for private documents are common scam signs.

Q: You ask ChatGPT for sources on youth unemployment. It lists three reports with titles and authors, but you cannot find any of them. What is the most likely explanation?
- The internet is down
* The references may be hallucinated, so you must not cite them
- The reports are too new for any search
- The authors have removed them
= Invented references are a known hallucination. Verify every source before use or leave it out.

Q: Which statement about AI-assisted tools such as Claude Code is the most accurate?
- They never need review because they read your whole project
* They can work inside a project, but their output still needs review and the official documentation should guide use
- They replace the need to learn any code
- They only work for spreadsheets
= Such tools are powerful but still make mistakes, so learners review the output and follow the official documentation.

Q: A prompt says "Review this code for security problems such as SQL injection and exposed passwords." The assistant says no problems found. What is the best next step?
- Publish the code immediately
- Assume every other check is unnecessary
- Remove all input fields
* Treat the review as a second opinion and still test, and ask a knowledgeable person for important systems
= Reviews by assistants can miss weaknesses, so they support but do not replace testing and expert checking.

Q: A learner uses XLOOKUP from an assistant's answer and gets an error in an older Excel. What is the most likely reason?
- Excel cannot do lookups
* Function availability depends on the Excel version, so the function may not exist there
- Lookups only work on phones
- The assistant must be restarted
= Newer functions are missing from older versions, so tell the assistant which version you use and ask for an alternative such as VLOOKUP.

Q: A learner's account does not show an image upload button. What is the right conclusion?
- The learner did something illegal
- All accounts have the same features, so it must be a virus
* Features depend on account, model, platform and usage limits, so the learner can check the help pages or practise with another assistant
- Images can only be used by teachers
= Feature availability varies, and the skills transfer to other assistants.

Q: You are testing a study assistant and it writes a full homework answer when asked. Which fix follows good practice?
- Delete the knowledge files
- Add the single word "Don't"
* Add a specific rule that gives a hint instead, then re-run the test script
- Stop testing since the assistant is clearly broken
= Specific wording of the behaviour you want, tested again, fixes failures reliably.

Q: A blog post you wrote with AI says a medicine is safe for children at any dose. What should you have done before delivery?
- Nothing, because AI is usually right
- Only checked the spelling
* Checked the claim with a qualified source and avoided giving medical advice
- Asked the client to check it for you
= You are responsible for what you deliver. Health claims need a qualified source, and unchecked content can harm the reader and your business.

Q: Which habit best protects your AI account? (choose all that apply)
* A long, unique password
* Two-step verification
- Sharing login codes with a trusted friend
- Using the same password everywhere so you remember it
= A strong unique password and two-step verification protect against theft. Codes must never be shared, and a reused password exposes every account.

Q: A video of a public figure announces a giveaway if you send UGX 20,000 first. Which response is best?
- Send the money quickly before it ends
- Share it so friends can benefit
* Treat it as a likely deepfake scam and check trusted sources
- Reply asking for proof
= Pressure to pay first and a famous face are common scam signs. Verify through trusted sources and never send money.

Q: Your hourly rate is UGX 12,000. A job takes 4 hours and you add UGX 6,000 for data costs. Then you give a 10 percent discount on the full amount. What is the final price?
- UGX 43,200
- UGX 54,000
* UGX 48,600
- UGX 42,000
= 4 x 12,000 = 48,000; plus 6,000 = 54,000; 10 percent of 54,000 is 5,400; so the final price is 54,000 - 5,400 = UGX 48,600.

Q: Why should a freelancer avoid promising a client a fixed income from AI work?
- Because AI is illegal for business
* Because results depend on skill, market, effort and reliability, so no income is guaranteed
- Because clients dislike prices
- Because proposals must not mention money
= Honest freelancers describe what they will deliver, not guaranteed earnings, since outcomes depend on many factors.

Q: What is the central objective of this course?
- To memorise the names of AI features
* To move from simply asking questions to using AI as a practical tool for learning, creating, solving problems and building opportunities
- To replace teachers with AI
- To avoid using AI in assessments
= The course aims at practical, responsible use of AI, not only asking questions.

Q: Which is the correct share of the final course mark that comes from the final project?
- 15%
- 20%
- 25%
* 40%
= Module quizzes are 15%, practical exercises 25%, prompt assignments 20% and the project 40%.

Q: A learner scores 90 in quizzes, 60 in practical work, 70 in prompt assignments and 50 in the project. Weights are 15%, 25%, 20%, 40%. What is the weighted mark?
- 70
* 62.5
- 67.5
- 55
= 90 x 0.15 = 13.5; 60 x 0.25 = 15; 70 x 0.20 = 14; 50 x 0.40 = 20. Total 62.5.

Q: In the verification step, a claim cannot be confirmed anywhere. What do you do?
- Present it as fact
* Delete it or label it as an unverified assumption
- Ask the AI to swear it is true
- Hide it in an appendix
= Only checked information should be presented as fact.

Q: Which presentation element shows you understand the weaknesses of your AI-assisted solution?
- The title slide
- The prompt count
* The limitations section
- The final thank-you
= Stating limitations shows honest understanding and builds trust in your result.
QUIZ,
];
