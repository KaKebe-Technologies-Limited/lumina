<?php
return [
    'slug'        => 'effective-communication-public-speaking',
    'title'       => 'Effective Communication and Public Speaking',
    'category'    => 'careers',
    'level'       => 'beginner',
    'icon'        => 'megaphone',
    'sort'        => 630,
    'summary'     => 'Speak, write and listen clearly at work and in life: emails, WhatsApp, meetings, difficult conversations, customers, social media and public speaking, with a plan to keep improving.',
    'description' => '<p>Almost every job, business and relationship depends on communication. This course teaches the everyday skills that make people understand you, trust you and want to work with you: listening, speaking, writing, handling feedback and conflict, and speaking in front of a group. It is written for students, graduates, job seekers, employees, small business owners and volunteers.</p><p>You will start with how communication works and why messages fail, then practise clear speaking and writing, including emails, WhatsApp messages at work, reports and minutes. You will learn phone and video call manners, conversation and networking skills, persuasion, feedback and calm handling of difficult conversations. You will also look at meetings, teamwork and the mixing of English, Luganda, Swahili and local languages in East Africa.</p><p>The last modules cover public speaking essentials (speech types, structure, storytelling, voice and questions), social media etiquette, spotting misinformation, customer communication and complaint handling, and a personal improvement plan. Every lesson has realistic weak-versus-strong examples and a practical task. For deeper work on slides and stage fright, take the companion course Presentation Skills and Confidence.</p>',
    'outcomes'    => [
        'Explain the communication process and name common barriers to understanding',
        'Use verbal, non-verbal and written communication to match your message to your listener',
        'Listen actively and ask questions that bring out useful answers',
        'Structure what you say and write using point, reason and example in plain language',
        'Write clear emails, WhatsApp messages, reports and minutes in a professional tone',
        'Handle phone and video calls, small talk, networking and introductions with confidence',
        'Give and receive feedback and manage difficult conversations and conflict calmly',
        'Run and contribute to meetings, and communicate respectfully across languages and cultures',
        'Prepare and deliver a short speech, answer questions and respond well to customers and complaints',
    ],
    'requirements' => ['A smartphone or computer for the practice tasks', 'No experience needed'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Google Workspace Learning Center', 'url' => 'https://support.google.com/a/users'],
        ['title' => 'Microsoft Support: Outlook help', 'url' => 'https://support.microsoft.com/outlook'],
        ['title' => 'WhatsApp Help Center', 'url' => 'https://faq.whatsapp.com/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: The foundations of communication',
            'summary' => 'Understand how a message travels, the three channels we use, and how to listen and question well.',
            'lessons' => [
                [
                    'title'   => 'How communication works, and why it fails',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe how a message travels from one person to another and name the barriers that stop it. This matters because most problems at work, such as missed deadlines, wrong orders and quarrels, begin with a message that was sent but not understood.</p>

<h3>The communication process</h3>
<p><strong>Communication</strong> is the sharing of meaning between people. It is not finished when you speak or write; it is finished when the other person understands what you meant. The process has six parts:</p>
<ol>
<li><strong>Sender:</strong> the person with an idea.</li>
<li><strong>Message:</strong> the idea turned into words, tone or gestures (this turning is called <em>encoding</em>).</li>
<li><strong>Channel:</strong> how it travels, such as face to face, a phone call, a letter, an email or WhatsApp.</li>
<li><strong>Receiver:</strong> the person who gets it and works out its meaning (<em>decoding</em>).</li>
<li><strong>Feedback:</strong> the reply, question, nod or action that shows what the receiver understood.</li>
<li><strong>Context:</strong> the situation around the message: the place, relationship, culture and mood.</li>
</ol>

<h3>Barriers that block messages</h3>
<table>
<thead><tr><th>Barrier</th><th>Example</th><th>Fix</th></tr></thead>
<tbody>
<tr><td>Noise (physical)</td><td>A loud market beside the phone call</td><td>Move, or call back later</td></tr>
<tr><td>Language</td><td>Technical words used with a farmer</td><td>Use simple, familiar words</td></tr>
<tr><td>Emotion</td><td>Replying while angry</td><td>Pause, then answer</td></tr>
<tr><td>Assumption</td><td>"Everybody knows the meeting moved"</td><td>Confirm in writing</td></tr>
<tr><td>Distraction</td><td>Looking at a phone while someone speaks</td><td>Give full attention</td></tr>
<tr><td>Wrong channel</td><td>Sending bad news by a short text</td><td>Speak in person or call</td></tr>
</tbody>
</table>

<h3>Worked example: a message that failed</h3>
<p>A shop owner in Kampala tells her assistant: "Please sort out the stock before they come." The assistant arranges the shelves neatly. The owner meant count the stock and prepare the list for the auditors. Look at what went wrong: "sort out" has more than one meaning, "they" was never named, no deadline was given and the owner did not ask for feedback. A stronger message is: "Please count all stock in the store room by 4 pm today and write the numbers on the sheet. The auditors from the bank arrive tomorrow at 9 am. Please tell me what you understand the task to be." The last sentence invites feedback and catches errors early.</p>

<h3>Case scenario</h3>
<p>Peter, a supervisor at a Jinja printing press, shouted across the noisy machine room, "Stop the red job after the next batch!" The operator heard "Stop the job" and halted a rush order immediately, costing two hours. Peter was angry, but he then realised that the noise, a shouted message and no confirmation were the real causes. He introduced a rule: for important instructions, walk to the person, say it once, and ask them to repeat it back. Errors fell sharply. The lesson: when a message fails, fix the process, not only the person.</p>

<blockquote><p><strong>Quick check:</strong> When is communication complete?</p><p>Answer: When the receiver understands the meaning the sender intended, which feedback helps to confirm.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Name one simple way to catch a misunderstanding early.</p><p>Answer: Ask the listener to repeat the task in their own words, because their version shows what they actually understood.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Think of a recent time you were misunderstood. Write down which of the six parts of the process broke down.</li>
<li>Name the barrier from the table that was involved.</li>
<li>Rewrite your original message so it is clearer, with who, what and when.</li>
<li>Next time you give an instruction, add "Please tell me what you understand".</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Communication is complete only when meaning is understood.</li>
<li>The process has sender, message, channel, receiver, feedback and context.</li>
<li>Barriers include noise, language, emotion, assumption and distraction.</li>
<li>Feedback and a suitable channel prevent most failures.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Verbal, non-verbal and written communication',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose the right channel for a message and make your words, voice and body say the same thing. This matters because people believe your tone and body language when they differ from your words, and a careless written message can last far longer than a spoken one.</p>

<h3>Three kinds of communication</h3>
<p><strong>Verbal</strong> communication uses spoken words. <strong>Non-verbal</strong> communication is everything else we send without words: facial expression, eye contact, posture, gestures, distance, dress and the tone, speed and loudness of the voice. <strong>Written</strong> communication uses text: letters, emails, messages, reports and notices.</p>

<h3>When to use which</h3>
<table>
<thead><tr><th>Situation</th><th>Best channel</th><th>Why</th></tr></thead>
<tbody>
<tr><td>Praise or thanks</td><td>Face to face, then in writing</td><td>Warmth is felt, and a record is kept</td></tr>
<tr><td>Bad news or a complaint</td><td>Face to face or call</td><td>Tone shows care and allows questions</td></tr>
<tr><td>Instructions with many steps</td><td>Written</td><td>People can re-read</td></tr>
<tr><td>Agreement on money or dates</td><td>Written</td><td>A record prevents disputes</td></tr>
<tr><td>Quick question</td><td>Message or call</td><td>Fast and informal</td></tr>
</tbody>
</table>

<h3>Reading body language carefully</h3>
<p>Non-verbal signals depend on culture and context, so treat them as clues, not proof. In many Ugandan communities, a young person who looks down when an elder speaks is showing respect, while in some workplaces the same behaviour may be read as lack of confidence. A good habit: notice a signal, then check it with a question such as "You look unsure; is something unclear?" Useful professional habits are an open posture, a calm face, steady but not staring eye contact, a firm friendly handshake where it is customary and a voice that matches your message.</p>

<h3>Worked example: words and tone disagree</h3>
<p>A manager says, "Of course I am happy to help," while sighing, checking her watch and not looking up. The employee leaves believing she is a nuisance. Compare a congruent version: the manager stops typing, looks up and says, "I can help, but I have a meeting at 3. Can we take ten minutes now and finish tomorrow?" The words, face and honesty agree, and the employee knows exactly where she stands.</p>
<p>Written example, weak versus strong subject for a notice: weak, "Meeting"; strong, "Staff meeting Friday 14 March, 10 am, Room 2: budget for next term".</p>

<h3>Case scenario</h3>
<p>Brenda, a junior accountant in Mbarara, disagreed with a figure in her boss's report but said "Fine" quietly, with her arms folded and eyes on the floor. Her boss took it as agreement. Two weeks later the error caused a problem with a donor. Brenda realised her words and body had hidden her real view. After that she practised saying, "I have a concern about the total on page 2. May I show you?" with a steady voice. The lesson: silence and closed signals are also messages, and they are easily misread.</p>

<blockquote><p><strong>Quick check:</strong> Which channel suits agreeing a price and delivery date, speech or writing?</p><p>Answer: Writing, because a record prevents later disagreement about what was promised.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> If someone's words and tone disagree, which do listeners usually trust?</p><p>Answer: The tone and body language, because they are harder to fake than words.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Say "I am fine" three times: happily, angrily and tiredly. Notice how the meaning changes.</li>
<li>Choose a real message you must send this week and decide the best channel, using the table.</li>
<li>Stand in front of a mirror and say a short request with an open posture and calm face.</li>
<li>Write a clear subject line for the message you chose.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Verbal, non-verbal and written channels each have strengths.</li>
<li>Bad news and sensitive topics are best spoken; agreements are best written.</li>
<li>Match your words, voice and body so the message is believable.</li>
<li>Check body-language clues with a question; do not assume.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Active listening and asking good questions',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to listen so that people feel heard and ask questions that give you useful answers. This matters because people often wait for their turn to talk instead of listening, and that causes mistakes, lost customers and strained relationships.</p>

<h3>Hearing is not listening</h3>
<p>Hearing is a body function; <strong>listening</strong> is a choice to pay attention and understand. <strong>Active listening</strong> means you show, with your actions, that you are following the speaker.</p>
<ol>
<li><strong>Give full attention.</strong> Put the phone face down and face the speaker.</li>
<li><strong>Do not interrupt.</strong> Let the person finish the thought.</li>
<li><strong>Show you follow.</strong> Nod, and use short sounds or words such as "I see".</li>
<li><strong>Paraphrase.</strong> Say it back in your own words: "So you need the report by Thursday, not Friday?"</li>
<li><strong>Notice feelings.</strong> "That sounds frustrating."</li>
<li><strong>Ask before advising.</strong> Many people want to be understood before they want a solution.</li>
</ol>

<h3>Types of questions</h3>
<table>
<thead><tr><th>Type</th><th>Example</th><th>Use it to</th></tr></thead>
<tbody>
<tr><td>Open</td><td>"What is the main problem for you?"</td><td>Get detail and stories</td></tr>
<tr><td>Closed</td><td>"Did the delivery arrive today?"</td><td>Confirm a fact quickly</td></tr>
<tr><td>Probing</td><td>"Can you give me an example?"</td><td>Go deeper</td></tr>
<tr><td>Clarifying</td><td>"When you say soon, do you mean this week?"</td><td>Remove doubt</td></tr>
<tr><td>Leading (avoid)</td><td>"You did not check, did you?"</td><td>Nothing good; it blames</td></tr>
</tbody>
</table>

<h3>Worked example: a poor listener and a good listener</h3>
<p>A customer says, "My phone screen cracked after the repair." The weak response: "No, that is not our fault, the screen was already weak." It interrupts and defends. The strong response: "I am sorry to hear that. Let me make sure I understand: the screen was fine when you collected it on Monday and cracked on Tuesday. Is that right? Can you tell me how it happened?" This paraphrases, shows care and asks an open question. Even if the shop later explains the repair did not cause the crack, the customer stays calm because he was heard first.</p>

<h3>Case scenario</h3>
<p>Aisha, a team leader at a microfinance office in Mbale, kept giving advice whenever a colleague, Moses, mentioned a problem. Moses stopped sharing. A mentor suggested a change: for two weeks, Aisha would listen for a full minute, paraphrase, and then ask, "Would you like advice, or do you just need to talk it through?" Moses began to open up and, surprisingly, often solved the problem himself while talking. The lesson: asking what the other person needs often helps more than quick advice.</p>

<blockquote><p><strong>Quick check:</strong> What is paraphrasing and why do it?</p><p>Answer: Saying what you heard in your own words; it confirms understanding and shows the speaker you listened.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which question gets more detail: "Is the delivery late?" or "What happened with the delivery?"</p><p>Answer: The second, because it is an open question and invites a full answer.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>In your next conversation, put your phone away and do not interrupt for one full minute.</li>
<li>Paraphrase once, starting with "So what you are saying is ...".</li>
<li>Ask two open questions starting with What or How.</li>
<li>Afterwards, write down what you learned that you would otherwise have missed.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Listening is active: attention, no interruption, paraphrase.</li>
<li>Open and probing questions bring out detail; avoid leading questions.</li>
<li>Understand first, then advise.</li>
<li>Repeating back prevents costly mistakes.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Communication is complete when:
- The sender has finished speaking
- The message has been sent by email or WhatsApp
* The receiver has understood the meaning the sender intended
- The receiver has said "yes"
= Understanding, not sending, completes communication. A polite "yes" can hide confusion.

Q: Which is an example of a physical barrier to communication?
- Assuming everyone knows the news
* A loud generator running beside a phone call
- Feeling angry
- Using a word the listener does not know
= Noise in the environment is physical. The others are assumption, emotional and language barriers.

Q: A manager says "I am happy to help" while sighing and looking at her watch. What will most listeners believe?
- The words, because they are clear
* The tone and body language, which signal reluctance
- Neither; they will ask for a written version
- The words, because the manager is senior
= When words and non-verbal signals disagree, people tend to trust the non-verbal signals.

Q: Which message is best sent in writing?
- A word of thanks to a colleague
- Bad news about a lost contract
* An agreed price, quantity and delivery date with a supplier
- A quick question about lunch
= Agreements about money and dates should be written so both sides have a record.

Q: Patience tells a customer, "That is not our fault," before the customer finishes explaining. Which active listening step was skipped?
- Asking a closed question
* Letting the person finish and paraphrasing what they said
- Giving advice sooner
- Looking at the clock
= Active listening begins with not interrupting and confirming understanding by paraphrase.

Q: Which are open questions? (choose all that apply)
* What is the biggest challenge in your work?
- Did you finish the report?
* How did the problem start?
- You forgot again, didn't you?
= Open questions begin with words like What and How and invite detail. The others are closed or leading.

Q: Peter shouted an instruction over loud machines and the operator stopped the wrong job. According to this module, what is the best fix?
- Hire a louder supervisor
- Blame the operator in the next meeting
* Deliver important instructions close up and ask the person to repeat them back
- Stop giving instructions
= Fix the process: remove noise, say it once clearly and use repetition as feedback.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Speaking and writing clearly',
            'summary' => 'Structure your speech, write professional emails, WhatsApp messages and reports, and handle phone and video calls.',
            'lessons' => [
                [
                    'title'   => 'Clear speaking: point, reason, example',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to give a short, organised answer using a simple structure, in plain words. This matters because listeners lose patience with rambling, and people who speak clearly are trusted with responsibility sooner.</p>

<h3>The PRE structure</h3>
<p>The simplest structure for answering a question or giving an opinion is <strong>PRE</strong>:</p>
<ol>
<li><strong>Point:</strong> say your answer first, in one sentence.</li>
<li><strong>Reason:</strong> say why.</li>
<li><strong>Example:</strong> give one real case or number.</li>
</ol>
<p>Then stop, or repeat the point in one line (PREP). Put the most important thing first, because that is what people remember.</p>

<h3>Plain language</h3>
<table>
<thead><tr><th>Jargon or heavy words</th><th>Plain alternative</th></tr></thead>
<tbody>
<tr><td>We will leverage synergies</td><td>We will work together and share the tasks</td></tr>
<tr><td>Facilitate the commencement</td><td>Help us start</td></tr>
<tr><td>At this point in time</td><td>Now</td></tr>
<tr><td>Utilise</td><td>Use</td></tr>
<tr><td>KPI misalignment</td><td>We are not measuring the same things</td></tr>
</tbody>
</table>
<p>Use short sentences, one idea each, familiar words and numbers instead of "many" or "soon". Explain any technical word the first time you use it, for example "the router, which is the device that shares our internet".</p>

<h3>Worked example: answering a question at work</h3>
<p>Your manager asks, "Should we open the shop on Sundays?" A weak answer: "Well, it depends, some customers might come but staff may be tired and also the landlord, and I think maybe we should think about it, though other shops do." A strong answer: "Yes, I think we should try it for one month (point). Our Saturday sales are the highest of the week and customers have asked about Sunday (reason). Last Saturday, 12 people left because the queue was long (example). I suggest two staff on rotation and a review after four weeks." The strong version is about 50 words and ends with a clear next step.</p>

<h3>Weak and strong self-introductions</h3>
<p>Weak: "Um, hello, my name is Sam and I do, like, many things in IT and so on." Strong: "Hello, I am Sam Okello. I fix computers and set up Wi-Fi for small offices in Kampala. If your office has slow internet, I can help."</p>

<h3>Case scenario</h3>
<p>Diana, a project assistant in Gulu, was asked in a team meeting why a training was delayed. She spoke for four minutes, starting with the history of the project, and the director interrupted, "So when will it happen?" Diana was embarrassed. Later she practised starting with the answer: "The training moves to 20 May because the venue was double-booked. We have confirmed a new venue and will inform participants by Friday." In the next meeting, her answer took twenty seconds and the director thanked her. The lesson: lead with the answer, then explain.</p>

<blockquote><p><strong>Quick check:</strong> What does PRE stand for?</p><p>Answer: Point, Reason, Example, because this order gives the answer first and then supports it.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why replace "soon" with "by Friday"?</p><p>Answer: A specific date can be acted on and checked; "soon" means different things to different people.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick the question "What is your favourite way to learn?" and answer it aloud using PRE in under 40 seconds.</li>
<li>Record yourself on your phone and listen for filler words such as "um" and "like".</li>
<li>Rewrite one jargon sentence from your work or study in plain words.</li>
<li>Write a two-sentence introduction of yourself that says what you do and for whom.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Give the point first, then the reason, then an example.</li>
<li>Choose short sentences and everyday words.</li>
<li>Use exact numbers and dates instead of vague words.</li>
<li>End with a clear next step.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Writing professional emails',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a clear, polite email that gets the reply or action you need. This matters because an email is often your first impression on an employer, client or official, and a careless one can lose you a job or a contract.</p>

<h3>The parts of a good email</h3>
<ol>
<li><strong>Subject line:</strong> specific, so the reader knows the purpose before opening.</li>
<li><strong>Greeting:</strong> "Dear Ms Nakato," or "Hello Mr Otieno,".</li>
<li><strong>Opening line:</strong> who you are, if needed, and why you are writing.</li>
<li><strong>Body:</strong> short paragraphs; one topic per email; the key request near the top.</li>
<li><strong>Action and deadline:</strong> what you need and by when.</li>
<li><strong>Closing:</strong> "Kind regards," and your full name, role and phone number.</li>
</ol>

<h3>Etiquette</h3>
<ul>
<li>Use a professional address such as a name, not a nickname like "sweetgirl2000".</li>
<li>Do not write in capital letters; it looks like shouting.</li>
<li>Use Cc for people who should know, and Bcc only for large groups where addresses should stay private. Use Reply All only when everyone needs the answer.</li>
<li>Mention an attachment in the text and check it is attached before sending.</li>
<li>Reply within one working day, even if only to say "I received this and will answer by Thursday".</li>
<li>Read it aloud once before sending. Check names, dates and amounts.</li>
</ul>

<h3>Weak and strong: a job enquiry</h3>
<p><strong>Weak:</strong></p>
<pre><code>Subject: Job

hello sir i saw your advert i want the job please see my CV
thanks</code></pre>
<p><strong>Strong:</strong></p>
<pre><code>Subject: Application: Sales Assistant, Kampala branch

Dear Mr Kato,

I am writing to apply for the Sales Assistant position advertised on 3 June.
I have a Certificate in Business Studies and two years of shop experience,
where I handled about 40 customers a day.

My CV is attached. I am available for an interview any weekday afternoon.

Kind regards,
Racheal Namuli
Tel: 07xx xxx xxx</code></pre>

<h3>Weak and strong: asking for a deadline extension</h3>
<p>Weak: "I cannot finish by Monday, sorry." Strong: "Dear Dr Ssali, I will not be able to submit the report on Monday because the survey data arrived late. May I submit it on Wednesday 9 July by noon? I have already completed the first two sections and can send them now. Thank you for your understanding."</p>

<h3>Case scenario</h3>
<p>Joshua, a graduate in Entebbe, emailed a hiring manager with the subject "Hi" and a message beginning "Yo sir". He had been qualified, but the manager did not reply. A friend showed him the strong format above. He resent a corrected email with a clear subject, a short summary and a polite closing. This time the manager answered within a day and invited him for an interview, adding that the first message had almost been deleted. The lesson: the subject line and tone decide whether your email is even read.</p>

<blockquote><p><strong>Quick check:</strong> Name two features of a strong subject line.</p><p>Answer: It is specific and states the purpose, such as the role or topic and a date, so the reader knows what to do.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> When should you use Reply All?</p><p>Answer: Only when everyone on the thread needs your answer, because otherwise you fill other inboxes needlessly.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write an email to a real or imaginary teacher asking for a meeting, including a subject, greeting, request and closing.</li>
<li>Replace any vague time with an exact day and hour.</li>
<li>Read it aloud and remove one unnecessary sentence.</li>
<li>Check that your email address and signature look professional.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A specific subject and a clear request near the top get replies.</li>
<li>Keep emails short, polite and free of capital-letter shouting.</li>
<li>Check attachments, names and dates before pressing Send.</li>
<li>Reply promptly, even if only to acknowledge.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'WhatsApp at work, reports and minutes',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use WhatsApp professionally, write a short report and take useful meeting minutes. This matters because much East African business now runs through WhatsApp groups, and the written records you produce show how organised you are.</p>

<h3>Professional WhatsApp</h3>
<p>WhatsApp is quick and personal, which is its strength and its danger. Follow these habits at work:</p>
<ul>
<li>Greet and name yourself when writing to a new contact: "Good morning Mr Mugisha, this is Esther from Bright Printers."</li>
<li>Send one complete message instead of ten short ones.</li>
<li>Avoid voice notes longer than one minute for important matters; people cannot search them.</li>
<li>Do not message late at night or on Sunday unless it is urgent or agreed.</li>
<li>Keep group chats on topic; take private matters to a private chat.</li>
<li>Use emojis sparingly, and not in serious or formal messages.</li>
<li>Never share confidential files or other people's phone numbers without permission.</li>
</ul>
<p><strong>Weak:</strong> "hi" ... "are you there" ... "I need the quotation". <strong>Strong:</strong> "Good afternoon Mr Mugisha, this is Esther from Bright Printers. Please could you send the quotation for 500 brochures by 5 pm tomorrow? Thank you."</p>

<h3>A short report</h3>
<p>A simple report has: <strong>Title and date</strong>, <strong>Purpose</strong> (one sentence), <strong>Findings</strong> (the facts, in short points or a small table), <strong>Conclusion</strong> and <strong>Recommendation</strong> (what should be done). Put the main finding and recommendation at the top in a one-paragraph summary, because busy readers may read only that.</p>

<h3>Meeting minutes</h3>
<p><strong>Minutes</strong> are the written record of what was decided at a meeting. They should show date, time, place, people present and absent, decisions and, most important, actions with the name of the person responsible and a deadline.</p>
<table>
<thead><tr><th>Weak minute</th><th>Strong minute</th></tr></thead>
<tbody>
<tr><td>Discussed the fundraising</td><td>Agreed to hold a fundraising dinner on 15 August. Action: Sarah to book the venue by 20 July.</td></tr>
<tr><td>Someone will send the list</td><td>Action: Moses to send the member list to the treasurer by Friday 5 pm.</td></tr>
</tbody>
</table>
<p>Send minutes within 24 to 48 hours while memory is fresh.</p>

<h3>Case scenario</h3>
<p>The secretary of a savings group in Masaka wrote long minutes of everything people said but never listed who would do what. At the next meeting nobody remembered agreeing to collect the contributions, and the group lost two weeks. A new secretary used a short format: decisions in three lines and a table of actions with names and dates, shared in the group within one day. The meetings became shorter and tasks were done. The lesson: minutes record decisions and actions, not every word.</p>

<blockquote><p><strong>Quick check:</strong> What is the most important part of meeting minutes?</p><p>Answer: The decisions and actions, with the responsible person and a deadline, because they make things happen.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why send one complete WhatsApp message rather than many short ones?</p><p>Answer: It is easier to read and respond to, and it feels more respectful of the reader's time.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Rewrite a recent casual WhatsApp message to a boss or customer in a professional form.</li>
<li>Write a five-line report on something simple, such as the state of your study space, with purpose, finding and recommendation.</li>
<li>Take minutes of a short family or group discussion with one action, a name and a date.</li>
<li>Check that your WhatsApp profile photo and status are suitable for work contacts.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Work WhatsApp still needs greetings, clarity and respect for time.</li>
<li>Reports lead with the summary and end with a recommendation.</li>
<li>Minutes list decisions and actions with names and deadlines.</li>
<li>Send minutes quickly.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Phone and video call etiquette',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to make and receive phone calls, and join video meetings, in a professional way. This matters because many interviews, client meetings and team discussions now happen on calls, where your voice and setup are all the other person has to judge you by.</p>

<h3>Phone calls</h3>
<ol>
<li><strong>Prepare.</strong> Write down your two or three points before you call.</li>
<li><strong>Greet and identify.</strong> "Good morning, this is Joan Atim from Nile Hardware. May I speak to Mr Odongo?"</li>
<li><strong>Ask if it is a good time.</strong> "Is it a good time for a two-minute call?"</li>
<li><strong>State the purpose</strong> in one sentence, then speak slowly and clearly.</li>
<li><strong>Summarise</strong> any agreement before ending: "So I will send the invoice today and you will pay by Friday."</li>
<li><strong>Close politely</strong> and let the other person hang up first when they are senior or a customer.</li>
</ol>
<p>When answering, answer within a few rings, smile (it changes the voice), give your name and organisation, and take a message with the caller's name, number and reason. Do not eat or speak to others while on a call, and never put a caller on hold without saying so.</p>

<h3>Video calls (Google Meet, Zoom, Microsoft Teams)</h3>
<table>
<thead><tr><th>Do</th><th>Avoid</th></tr></thead>
<tbody>
<tr><td>Join two minutes early and test sound and camera</td><td>Joining late and asking others to repeat</td></tr>
<tr><td>Mute your microphone when not speaking</td><td>Leaving background noise on</td></tr>
<tr><td>Place the camera at eye level with light in front of you</td><td>Sitting with a bright window behind you</td></tr>
<tr><td>Use a plain background and dress as for the office</td><td>Joining from bed or a noisy market</td></tr>
<tr><td>Use the raise-hand feature or chat to speak</td><td>Talking over others</td></tr>
<tr><td>Look at the camera when you speak</td><td>Checking other apps while others talk</td></tr>
</tbody>
</table>
<p>If your internet is weak, tell the host, turn off your video when needed and keep your messages short. Say at the start, "My connection may drop; if it does I will rejoin".</p>

<h3>Weak and strong: leaving a voicemail</h3>
<p>Weak: "Hello, call me." Strong: "Good afternoon, this is Joan Atim from Nile Hardware, number 07xx xxx xxx. I am calling about the order for 20 bags of cement. Please call me back before 5 pm today."</p>

<h3>Case scenario</h3>
<p>Brian joined an online interview for a bank position from a busy trading centre in Kampala, with a window behind him and his microphone left on. The panel could hardly hear him and a motorbike roared past. He did not get the job. For his next interview, he went to a quiet room at his school, tested the setup the day before, sat facing a window for light and wore a clean shirt. He answered clearly and was selected. The lesson: preparing your space is part of preparing your answers.</p>

<blockquote><p><strong>Quick check:</strong> What should you do on a video call when you are not speaking?</p><p>Answer: Mute your microphone, because background noise distracts everyone.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why summarise at the end of a phone call?</p><p>Answer: It confirms what was agreed so that nothing is misunderstood or forgotten.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a 20-second phone introduction for yourself or a business.</li>
<li>Practise leaving a voicemail with name, number, reason and a time to call back.</li>
<li>Test your camera and microphone using any video app and adjust your lighting.</li>
<li>Make a checklist of five things to do before any online meeting.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Prepare points, introduce yourself and summarise at the end of a call.</li>
<li>On video, check sound, light and background, and mute when not speaking.</li>
<li>Do not multitask; attention is visible and audible.</li>
<li>Leave clear voicemails with name, number and purpose.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does the PRE structure stand for?
- Plan, Review, End
* Point, Reason, Example
- Prepare, Rehearse, Explain
- Problem, Result, Evidence
= PRE gives your answer first, then the reason, then a real example.

Q: Which sentence is in plain language?
- We shall utilise the available resources at this point in time
- We will leverage synergies going forward
* We will use what we have now
- We will facilitate the commencement of operations
= Short, familiar words are easier to understand and remember.

Q: Which is the best subject line for an application email?
- Hello
- Please read
* Application: Sales Assistant, Kampala branch
- JOB!!!
= A specific subject names the purpose, so the email is opened and handled correctly.

Q: When should you use Reply All?
- Always, to keep everyone informed
- Never
* Only when everyone on the thread needs your reply
- Only when you are angry
= Reply All fills inboxes; use it only when everyone needs your answer.

Q: Which of these belongs in good meeting minutes? (choose all that apply)
* Decisions made
* Actions with a name and a deadline
- Every word each person said
- A list of jokes made
= Minutes record outcomes, not a transcript.

Q: Rose must send the minutes of a committee meeting that lasted two hours. What is the best approach?
- Wait until the next meeting to share them
* Send a short summary of decisions and an action table within a day or two
- Write down all conversations in detail and send after a month
- Tell members to remember what they agreed
= Prompt, short minutes with clear actions keep everyone accountable.

Q: Brian joins an online interview with a bright window behind him and his microphone noisy. What should he improve first?
- Speak more quickly
* Use a quiet place with light in front and test sound and camera beforehand
- Turn off his camera
- Wear sunglasses
= A prepared setup lets the panel see and hear you clearly, which supports your answers.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Conversations, persuasion and conflict',
            'summary' => 'Start conversations and networks, persuade honestly, give and receive feedback, and handle difficult talks.',
            'lessons' => [
                [
                    'title'   => 'Conversation skills, small talk and introductions',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to start a conversation, keep it going and introduce yourself and others politely. This matters because jobs, clients and friendships often come from short conversations at weddings, workshops, offices and events, not from formal applications.</p>

<h3>Why small talk matters</h3>
<p><strong>Small talk</strong> is light conversation about safe topics. It is not a waste of time; it builds comfort and trust so that serious conversations become easier. Safe topics include the event you are at, travel to the venue, work or study in general, food, sport and the weather. Avoid asking about salary, age, marital status, religion or politics with people you have just met.</p>

<h3>The FORD method</h3>
<p>If you do not know what to say, ask about <strong>F</strong>amily (carefully), <strong>O</strong>ccupation, <strong>R</strong>ecreation and <strong>D</strong>reams. Better still, follow the <em>ask, listen, follow up</em> pattern: ask an open question, listen to the answer and ask a question about something they just said. Share something about yourself too, so it is a two-way exchange.</p>

<h3>Introductions</h3>
<ol>
<li><strong>Greet</strong> with a smile and the right greeting for the culture or language.</li>
<li><strong>Name:</strong> say your full name clearly and listen for theirs.</li>
<li><strong>Role and value:</strong> what you do and who you help, in one sentence.</li>
<li><strong>Question:</strong> ask something about them.</li>
</ol>
<p><strong>Introducing two people:</strong> introduce the junior person to the senior one, with a connecting fact: "Dr Namubiru, may I introduce Peter Okello, who has just finished his diploma in IT. Peter, Dr Namubiru is the head of our computer department."</p>

<h3>Weak and strong networking introductions</h3>
<p><strong>Weak:</strong> "Hi... I am Kevin. I am looking for work. Do you have any?" <strong>Strong:</strong> "Good afternoon, I am Kevin Mukasa. I just finished a diploma in electrical installation and I fit solar systems for small shops. What brings you to this workshop?" Then listen. Later, ask for a contact: "May I have your number? I would like to share some information."</p>
<p>A follow-up message the next day: "Hello Mrs Auma, it was good to meet you at the SME workshop yesterday. As we discussed, I am sending my profile. Thank you for your advice about licensing. Kind regards, Kevin."</p>

<h3>Case scenario</h3>
<p>Susan, a shy fresher at a business fair in Kampala, stood alone with her phone. She decided to try a plan: say hello to three people, ask each one "What do you do?" and listen. The second person, a bakery owner, mentioned she needed help with social media. Susan said, "I manage pages for small businesses; may I send you an example?" The next day she sent a short message and two screenshots. Two weeks later, she had her first paid job. The lesson: curiosity about the other person is an easier start than selling yourself.</p>

<blockquote><p><strong>Quick check:</strong> Name two topics to avoid with someone you have just met.</p><p>Answer: Salary and politics (or religion, age and marital status), because they are personal and can cause discomfort.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> When introducing two people, who is introduced to whom?</p><p>Answer: The junior to the senior person, as a sign of respect.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a 15-second self-introduction following the greet, name, role and question format.</li>
<li>Prepare three open questions you could ask at an event.</li>
<li>Start a conversation with one new person this week, such as a classmate or neighbour.</li>
<li>Write a follow-up message of three sentences you could send after meeting someone.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Small talk builds trust; use safe topics.</li>
<li>Ask open questions, listen and follow up on what they say.</li>
<li>A good introduction says who you are, what you do and asks about them.</li>
<li>Follow up within a day.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Persuasion, giving and receiving feedback',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to persuade people honestly and give and receive feedback in a way that improves work and keeps relationships. This matters because you will often need others to agree, buy or change, and feedback is how teams and individuals improve.</p>

<h3>Honest persuasion</h3>
<p><strong>Persuasion</strong> means helping someone decide freely because they see a benefit; it is different from manipulation, which hides facts or pressures people. A simple method has four steps:</p>
<ol>
<li><strong>Understand their need.</strong> Ask what matters to them.</li>
<li><strong>Link your idea to their benefit</strong>, not only your own.</li>
<li><strong>Give evidence:</strong> a number, a sample, a story from a similar person.</li>
<li><strong>Make the next step easy and clear:</strong> "Can we try it for two weeks?"</li>
</ol>
<p><em>Example:</em> to ask a landlord to repair a gate, do not say "You are lazy". Say, "I am worried that anyone can enter the compound at night, which is a risk for all tenants and for your property. The repair quote is UGX 150,000. Can it be done by Saturday?"</p>

<h3>Giving feedback: the SBI model</h3>
<p>Use <strong>Situation, Behaviour, Impact</strong>, then agree on a next step.</p>
<table>
<thead><tr><th>Weak feedback</th><th>Strong feedback</th></tr></thead>
<tbody>
<tr><td>You are always late and careless.</td><td>At Monday's customer meeting (situation) you arrived 20 minutes after the start (behaviour), so we began without your report and the client could not see your figures (impact). Can you arrive 10 minutes early next time?</td></tr>
<tr><td>Good job.</td><td>Your summary on Tuesday was clear and short, and it helped the director decide in two minutes. Please keep that style.</td></tr>
</tbody>
</table>
<p>Give feedback soon after the event, in private for criticism and in public for praise, and speak about actions, not character.</p>

<h3>Receiving feedback</h3>
<ul>
<li>Listen to the end without defending.</li>
<li>Ask for an example: "Can you show me one time when that happened?"</li>
<li>Say thank you, even if you disagree; thanks does not mean you agree.</li>
<li>Take time to think, then decide what you will change.</li>
</ul>

<h3>Case scenario</h3>
<p>Ruth, a team leader in Mbarara, told a young sales agent, "Your work is sloppy." He became angry and stopped speaking to her. A trainer advised her to use SBI. Ruth said, "In the order forms from last week, three had no phone number, so delivery staff could not reach customers and two orders were late. Let us add a check before you submit." The agent agreed and even created a checklist for the whole team. The lesson: specific feedback about actions can be accepted, while labels about character start fights.</p>

<blockquote><p><strong>Quick check:</strong> What does SBI stand for?</p><p>Answer: Situation, Behaviour, Impact, which keeps feedback specific and focused on actions.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is the difference between persuasion and manipulation?</p><p>Answer: Persuasion is honest and lets the person choose freely; manipulation hides facts or uses pressure.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose something you want someone to agree to and write down their likely need.</li>
<li>Write a three-sentence request that links your idea to their benefit and gives one fact.</li>
<li>Write one piece of SBI feedback you could give a friend or colleague (praise counts).</li>
<li>Next time someone criticises you, say "Thank you, can you give me an example?" before replying.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Persuade by linking your idea to the listener's need, with evidence.</li>
<li>Use SBI for feedback: situation, behaviour, impact.</li>
<li>Criticise in private, praise often and in public.</li>
<li>Receive feedback calmly: listen, ask, thank, then decide.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Difficult conversations and conflict',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to prepare for and hold a difficult conversation calmly, and to reduce a conflict rather than make it worse. This matters because avoided problems grow, and angry words can damage a career or a friendship in a few seconds.</p>

<h3>Why conversations become difficult</h3>
<p>A conversation feels difficult when the topic matters, emotions run high and opinions differ. Examples are asking for a salary review, refusing a request, correcting a senior, telling a friend you cannot lend money or discussing a missed payment. Silence is rarely the best answer: the problem usually continues and your feelings turn into resentment.</p>

<h3>A five-step method</h3>
<ol>
<li><strong>Prepare.</strong> Decide your goal. What outcome do you want? Write down the facts, not guesses.</li>
<li><strong>Choose time and place.</strong> Private, calm, not when either person is rushing or angry.</li>
<li><strong>Open gently and clearly.</strong> State the purpose and your good intention: "I want to talk about the delivery delays because I want us to keep the customer."</li>
<li><strong>Use "I" statements, listen, and look for the other side.</strong> "I felt worried when..." not "You always...". Ask "How do you see it?"</li>
<li><strong>Agree on actions</strong> and a follow-up time, and summarise.</li>
</ol>

<h3>Staying calm</h3>
<p>If your heart races or your voice rises, slow your breathing, lower your voice and say, "Let me think for a moment". It is acceptable to pause: "I would like us to continue this tomorrow when we are both calm." Never insult, threaten or bring up old issues. Separate the <strong>person</strong> from the <strong>problem</strong>.</p>

<h3>Weak and strong examples</h3>
<table>
<thead><tr><th>Situation</th><th>Weak</th><th>Strong</th></tr></thead>
<tbody>
<tr><td>Asking for a salary review</td><td>"I deserve more; everyone else is paid more."</td><td>"I would like to discuss my salary. Since January I have managed the online orders, which increased sales by 20 percent. Could we review my pay next month?"</td></tr>
<tr><td>Refusing a loan to a friend</td><td>"I do not have money for you."</td><td>"I value our friendship and I cannot lend money now because I am paying school fees. I can help you write a plan to ask the SACCO."</td></tr>
<tr><td>Disagreeing with a manager</td><td>"That is wrong."</td><td>"May I share a different view? I am worried the new schedule overlaps with school hours for some staff."</td></tr>
</tbody>
</table>

<h3>Case scenario</h3>
<p>Two co-workers at a Kampala restaurant, Hassan and Mercy, stopped speaking after Hassan took credit for a menu idea. Their manager, Mr Opio, met each separately, then together. He asked each to describe the facts and what they needed. Mercy said she felt ignored; Hassan admitted he spoke carelessly. They agreed that future ideas would be named in staff meetings and that they would check in weekly. The conflict ended in two weeks. The lesson: hearing both sides in private and agreeing on actions solves more than taking sides.</p>

<blockquote><p><strong>Quick check:</strong> Why use "I felt worried when..." instead of "You always..."?</p><p>Answer: "I" statements describe your experience and cause less defence than accusations.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What can you do if you become too angry to continue?</p><p>Answer: Pause politely and agree to continue later, because decisions made in anger are usually poor.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a conversation you have been avoiding and write your goal in one sentence.</li>
<li>List three facts and one feeling, using an "I" statement.</li>
<li>Write the first two sentences you would say, including your good intention.</li>
<li>Practise aloud, then choose a calm time to hold the talk.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Prepare facts and a goal; choose a calm, private moment.</li>
<li>Use "I" statements, listen to the other side and stay calm.</li>
<li>Separate the person from the problem.</li>
<li>End with agreed actions and a follow-up time.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which topic is safest for small talk with someone you have just met?
- Their salary
- Their religion
* The event you are both attending
- Their marital status
= Neutral, shared topics are comfortable; personal questions can offend.

Q: When introducing a junior colleague to a senior person, you should:
- Introduce the senior person to the junior
* Introduce the junior person to the senior and add a connecting fact
- Say nothing and let them meet
- Introduce only yourself
= Courtesy puts the senior person first, and a connecting fact helps them start talking.

Q: What does SBI stand for in feedback?
- Speak, Believe, Insist
* Situation, Behaviour, Impact
- Start, Begin, Improve
- Subject, Body, Ideas
= SBI keeps feedback specific: when and where, what was done and the effect.

Q: Which is honest persuasion?
- Hiding the price until the customer signs
* Showing how your offer meets the customer's need, with a real number
- Telling the customer there is only one left when there are fifty
- Pressuring the customer to decide in 30 seconds
= Honest persuasion links benefit to need and uses true evidence.

Q: Which of these are good habits when receiving feedback? (choose all that apply)
* Listen to the end without defending
* Ask for an example
- Argue at once
- Walk out without speaking
= Calm listening and asking for examples helps you learn from criticism.

Q: A friend asks for a loan you cannot give. Which reply best protects the friendship?
- "I do not have money for you."
- Ignore the message
* "I value our friendship and cannot lend now; I can help you plan to approach the SACCO."
- Lie that you have lost your phone
= A kind, honest refusal with an alternative keeps respect and trust.

Q: Hassan took credit for Mercy's idea and they stopped speaking. What did the manager do best?
- Moved Mercy to another branch
- Told them to forget it
* Heard each side separately, then together, and agreed on actions
- Punished Hassan publicly
= Listening to both sides and agreeing on follow-up actions resolves conflict.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Teams, meetings and cultures',
            'summary' => 'Run and join meetings well, communicate in teams, and respect language and culture in East Africa.',
            'lessons' => [
                [
                    'title'   => 'Running and contributing to meetings',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to prepare an agenda, run a short meeting and speak up usefully as a participant. This matters because poorly run meetings waste hours of paid time, while well-run ones turn talk into decisions.</p>

<h3>Before the meeting</h3>
<p>Ask first, "Is a meeting really needed?" A short message may be enough. If a meeting is needed, decide the <strong>purpose</strong> (to decide, to inform, to solve a problem), invite only the necessary people and send an <strong>agenda</strong> at least one day before.</p>
<pre><code>Subject: Staff meeting, Friday 14 March, 10:00-10:45, Room 2

Purpose: decide how to handle the term's printing budget.

Agenda
1. Opening and apologies (2 min) - Chair
2. Printing costs this term (10 min) - Sarah
3. Options and decision (20 min) - All
4. Actions and close (8 min) - Chair</code></pre>

<h3>Running the meeting</h3>
<ol>
<li>Start on time. Waiting for latecomers punishes those who came early.</li>
<li>State the purpose and time limit.</li>
<li>Follow the agenda; park off-topic items on a "later" list.</li>
<li>Invite quiet people: "Moses, what is your view?"</li>
<li>Summarise decisions aloud as you go.</li>
<li>End by reading out actions: who, what, by when. Finish on time.</li>
<li>Send minutes within two days.</li>
</ol>

<h3>Speaking up as a participant</h3>
<p>Prepare one point in advance. Wait for a pause, then use PRE: "I recommend we reduce colour printing (point), because it costs three times more (reason). Last term it was UGX 900,000 of the 2 million budget (example)." To disagree politely: "I see it differently. May I add another view?" To interrupt a talker: "May I come in here?" If you are nervous, speak early in the meeting; the longer you wait, the harder it gets.</p>
<table>
<thead><tr><th>Weak contribution</th><th>Strong contribution</th></tr></thead>
<tbody>
<tr><td>I think maybe we might consider something.</td><td>I suggest we ask three suppliers for quotes by next Friday.</td></tr>
<tr><td>That idea will not work.</td><td>I am concerned about cost. Could we test it with one class first?</td></tr>
</tbody>
</table>

<h3>Case scenario</h3>
<p>A youth organisation in Lira held weekly meetings that began 30 minutes late and ended without decisions. The new chairperson, Daniel, sent an agenda the day before, started at the stated time even with six people present, assigned a timekeeper and ended with a list of actions read aloud. Meetings fell from two hours to 50 minutes, attendance rose and projects moved. The lesson: structure and discipline respect everyone's time.</p>

<blockquote><p><strong>Quick check:</strong> What should every meeting end with?</p><p>Answer: A clear list of actions with who, what and by when, so decisions are carried out.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What can you do when a topic is off the agenda?</p><p>Answer: Park it on a "later" list and return to the agenda, so the meeting stays on time.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write an agenda for a real or imaginary 30-minute group meeting with times and names.</li>
<li>Write a one-line purpose for the meeting.</li>
<li>Prepare one contribution using PRE for the next meeting you attend.</li>
<li>Practise the sentence "May I add another view?" aloud.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Decide whether a meeting is needed; send an agenda in advance.</li>
<li>Start and finish on time; park off-topic items.</li>
<li>End with actions: who, what, by when.</li>
<li>Speak early and briefly, using point, reason and example.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Communicating well in teams',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to share information, ask for help and raise problems early in a team. This matters because most team failures come from silence, assumption and unclear roles, not from lack of skill.</p>

<h3>What good team communication looks like</h3>
<ul>
<li><strong>Clear roles:</strong> everyone knows who decides, who does and who needs to be told.</li>
<li><strong>Regular updates:</strong> short and predictable, such as a ten-minute stand-up on Monday or a weekly message.</li>
<li><strong>Open channels:</strong> people may ask questions and admit mistakes without being laughed at.</li>
<li><strong>Single source of truth:</strong> one place where the latest plan or file is kept, so people do not work on old versions.</li>
</ul>

<h3>Choosing the channel in a team</h3>
<table>
<thead><tr><th>Need</th><th>Channel</th></tr></thead>
<tbody>
<tr><td>Urgent problem</td><td>Phone call or in-person</td></tr>
<tr><td>Decision that needs a record</td><td>Email or shared document</td></tr>
<tr><td>Daily coordination</td><td>Group chat with clear topic</td></tr>
<tr><td>Sensitive or personal matter</td><td>Private conversation</td></tr>
</tbody>
</table>

<h3>A good status update</h3>
<p>Use three lines: <strong>Done, Next, Blocked</strong>. Example: "Done: designed the poster and sent it for approval. Next: print 200 copies on Thursday. Blocked: need the budget code from finance." The word "blocked" invites help early.</p>

<h3>Asking for help and raising problems</h3>
<p><strong>Weak:</strong> silence until the deadline passes, then "It is not ready". <strong>Strong:</strong> "I am behind on the survey report because 30 questionnaires are still missing. I can finish by Friday if two people help with data entry tomorrow. Can anyone assist?" It gives the problem, the effect, a new date and a specific request. Bring bad news early, with a possible solution, and never blame in public.</p>

<h3>Speaking up respectfully upward</h3>
<p>If a junior notices an error by a senior, say it privately and politely: "Mr Kiggundu, I noticed the total on slide 6 shows 4.5 million but the table adds to 4.2 million. May I check with you?" This protects respect and still fixes the error.</p>

<h3>Case scenario</h3>
<p>A four-person team at a Kigali start-up, working partly online, kept missing deadlines. Each member assumed another was designing the brochure; nobody started. Their leader, Claudine, introduced a Monday 10-minute call and a shared task list with a name beside each task. She also asked everyone to post "Done, Next, Blocked" every Thursday. Late work dropped to nearly zero within a month. The lesson: assumptions cause delay; clarity and routine prevent it.</p>

<blockquote><p><strong>Quick check:</strong> What do the three words Done, Next, Blocked help a team do?</p><p>Answer: Share progress, plan the next task and ask for help early.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why report a problem early?</p><p>Answer: Others still have time to help and the deadline can be adjusted before damage is done.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write your own Done, Next, Blocked update for this week.</li>
<li>List the people who depend on your work and decide how you will update them.</li>
<li>Write a polite message pointing out a small error to a senior person.</li>
<li>Draft a task list for a group project with one name against each task.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Teams need clear roles, routine updates and open channels.</li>
<li>Use Done, Next, Blocked for quick updates.</li>
<li>Raise problems early with a suggested solution.</li>
<li>Correct seniors privately and politely.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Languages, culture and respect in East Africa',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose language, greetings and titles that respect your audience, and avoid misunderstandings across cultures. This matters because East African workplaces and communities mix many languages and customs, and respect shown in the first minute opens doors.</p>

<h3>A multilingual setting</h3>
<p>A typical person in Uganda, Kenya or Tanzania may use English at work, Luganda, Luo, Runyankole or another local language at home and Swahili in the market or across borders. Sheng and Luganda-English mixing are also common among young people. <strong>Code-switching</strong> means moving between languages in one conversation. It is natural and can build closeness, but it can also exclude people.</p>

<h3>Choosing the language</h3>
<ul>
<li>Use the language that everyone present understands best. Mixing in a mixed audience can leave someone out.</li>
<li>In formal writing, reports and official emails, use standard English (or standard Swahili where that is expected).</li>
<li>For a community meeting, begin with a greeting in the local language, even if you continue in English with translation. A few respectful words mean a lot.</li>
<li>Do not switch to a language to talk about someone who is present and cannot follow; it is rude.</li>
<li>When using an interpreter, speak in short sentences, look at the listener, not the interpreter, and allow time for translation.</li>
</ul>

<h3>Greetings, titles and respect</h3>
<p>Greetings are not small talk in many communities; they are the start of respect. Greet before asking for anything, and ask about the person's health or family when appropriate. Use titles: Mr, Ms, Dr, Eng., Hon., Pastor, Sheikh, Mzee, Mama or Baba, according to the person. Use a surname or title until invited to use a first name. Elders and officials are generally greeted first, and a handshake is common, though some people prefer another greeting for religious or personal reasons, so follow their lead.</p>

<h3>Worked example: weak and strong openings</h3>
<table>
<thead><tr><th>Weak</th><th>Strong</th></tr></thead>
<tbody>
<tr><td>(To a village chairman) "I need to survey your people."</td><td>"Good morning, Chairman, how are you and how is the community? I am Rose Akello from the health project. With your permission, I would like to ask some questions about clean water. May I explain what it involves?"</td></tr>
<tr><td>(To a client in a mixed group) Switching to a local language no one else follows</td><td>"Let me explain in English so everyone follows, then I will give details in Swahili for those who prefer."</td></tr>
</tbody>
</table>

<h3>Other cultural differences</h3>
<p>Ideas of time, directness and eye contact differ. Some cultures prefer indirect answers: "We will see" can mean no. Others value directness. Ask clarifying questions kindly and avoid stereotypes: people are individuals, not just members of a tribe or nation. Be careful with jokes, nicknames and comments about accent, religion or ethnicity.</p>

<h3>Case scenario</h3>
<p>Faith, a nutrition officer from Kampala, visited a village in northern Uganda and began her talk in rapid English with technical terms. Mothers stayed silent. A local health volunteer suggested starting with a greeting in the local language and using a translator. Faith greeted properly, spoke in short sentences, used familiar foods as examples and invited questions. The mothers laughed, asked many questions and agreed to attend weekly sessions. The lesson: language and greeting show respect and decide whether people listen.</p>

<blockquote><p><strong>Quick check:</strong> Why might mixing languages be a problem in a mixed group?</p><p>Answer: Some people may not understand and will feel excluded, so use a language everyone shares.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What should you do before asking an elder for a favour?</p><p>Answer: Greet respectfully and use the right title, because respect opens the conversation.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List the languages you use and where you use each.</li>
<li>Write a respectful greeting and introduction in the local language of your area (ask a friend for help).</li>
<li>Rewrite one message so that someone from outside your community would understand it.</li>
<li>Write the correct title for three people you often meet.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Choose the language your whole audience understands.</li>
<li>Greet properly and use titles until invited otherwise.</li>
<li>Do not stereotype; ask politely when unsure.</li>
<li>Standard language belongs in formal writing.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What should be sent to participants before a meeting?
- Nothing; they will find out when they arrive
* An agenda with purpose, times and who leads each item
- A long history of the organisation
- Only the minutes of the last year
= An agenda helps people prepare and keeps the meeting focused.

Q: A participant raises a topic that is not on the agenda. The chair should:
- Discuss it for an hour
- Stop the meeting
* Note it on a list for later and return to the agenda
- Ask the person to leave
= Parking off-topic items protects time and respects the person.

Q: What does a "Done, Next, Blocked" update help teams to do?
- Hide mistakes
* Show progress, plan the next step and ask for help early
- Replace all meetings
- Impress the manager
= It is a short, clear format that brings problems to light before deadlines pass.

Q: Which is the best way to point out an error to a senior colleague?
- Announce it in the group chat
* Mention it privately and politely with the facts
- Say nothing and let others find it
- Correct it secretly without telling anyone
= A private, courteous note protects respect and still fixes the problem.

Q: Which are good practices when speaking to a mixed-language group? (choose all that apply)
* Use a language everyone understands
* Greet respectfully, maybe in the local language
- Switch to a language only some understand to talk about others
- Use jargon to sound clever
= Inclusion and respect build trust; excluding people or using jargon damages it.

Q: Faith speaks quickly in technical English to village mothers and gets no response. What should she do?
- Speak faster
- Cancel the session
* Greet in the local language, use short sentences, familiar examples and a translator
- Give a printed report only
= Matching language and style to the audience makes the message understood.

Q: A meeting of a youth group always starts 30 minutes late and ends without decisions. Which change would help most?
- Remove the agenda
* Send an agenda, start on time and end by reading out actions
- Add more speakers
- Hold the meeting less often with no notice
= Agenda, punctuality and recorded actions turn discussion into decisions.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Public speaking essentials',
            'summary' => 'Choose and structure a speech, tell stories, use your voice and handle questions. For slides and stage fright, see the Presentation Skills course.',
            'lessons' => [
                [
                    'title'   => 'Speech types and a simple structure',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to recognise the main kinds of speech and organise a short speech with a clear opening, body and close. This matters because at some point everyone is asked to say something, at a wedding, a school event, a staff meeting or a funeral, and a good structure makes it easy. (For slide design and managing stage fright in depth, see the course Presentation Skills and Confidence.)</p>

<h3>Types of speech</h3>
<table>
<thead><tr><th>Type</th><th>Purpose</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Informative</td><td>Teach or explain</td><td>A talk on saving with a SACCO</td></tr>
<tr><td>Persuasive</td><td>Move people to believe or act</td><td>A campaign for a clean-up day</td></tr>
<tr><td>Ceremonial</td><td>Mark an occasion</td><td>A toast, a welcome, a vote of thanks, a tribute</td></tr>
<tr><td>Impromptu</td><td>Speak without preparation</td><td>"Please say a few words"</td></tr>
</tbody>
</table>

<h3>The three-part structure</h3>
<ol>
<li><strong>Opening</strong> (about 10 percent): get attention, say who you are if needed, and state your main message.</li>
<li><strong>Body</strong> (about 80 percent): two or three main points, each with a reason and an example. Three points is enough; people remember threes.</li>
<li><strong>Close</strong> (about 10 percent): repeat your message and end with a call to action or a memorable line.</li>
</ol>
<p>Start with your goal: finish the sentence "After my speech, my listeners will ..." Everything in the speech must serve that sentence.</p>

<h3>Ways to open</h3>
<ul>
<li>A short true story.</li>
<li>A surprising fact or number.</li>
<li>A question that listeners answer in their minds.</li>
<li>A clear promise: "In five minutes you will know three ways to save money."</li>
</ul>
<p><strong>Weak opening:</strong> "Er, good morning everyone, I was not really prepared, so sorry, but I will say a few things." <strong>Strong opening:</strong> "Good morning. Last year, 40 percent of our members missed a loan payment because of one simple problem: they forgot the date. Today I will show you three easy ways to remember."</p>

<h3>Ceremonial speeches: a vote of thanks</h3>
<p>A vote of thanks is short, one to two minutes. Thank the main guest, name the organisers, mention one specific thing that mattered, and close warmly. Example: "On behalf of the school, I thank our guest of honour, Hon. Akello, for giving us her morning. Her story of walking five kilometres to school reminded each student that effort changes lives. I also thank the teachers and parents who prepared this day. May God bless you all." A toast follows a similar pattern: address the person, share one short and kind story, wish them well and raise the glass.</p>

<h3>Case scenario</h3>
<p>Moses was asked at short notice to give a vote of thanks at a graduation in Mbale. He panicked, then used a formula: thank the guest, name one thing, thank the organisers, close. In 90 seconds he spoke clearly and calmly and sat down to warm applause. Afterwards the principal asked him to represent the school at an event. The lesson: a simple structure rescues you when time is short.</p>

<blockquote><p><strong>Quick check:</strong> Name the three parts of a speech.</p><p>Answer: Opening, body and close, because each has a clear job: attract, explain and conclude.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> How long should a vote of thanks usually be?</p><p>Answer: One to two minutes, because it should be brief, warm and specific.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Finish the sentence: "After my speech, my listeners will ..." for a topic you know.</li>
<li>Write a strong opening of two sentences using a number, question or story.</li>
<li>Outline three body points, each with an example.</li>
<li>Write a 60-second vote of thanks for a real event (for example a class or club party).</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Know your purpose: inform, persuade or celebrate.</li>
<li>Use opening, body (two or three points) and close.</li>
<li>Open with a story, fact, question or clear promise.</li>
<li>Keep ceremonial speeches short, warm and specific.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Storytelling, voice and delivery',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use a short story to make a point, and to use your voice and body so that listeners stay with you. This matters because facts are soon forgotten but stories stay, and a flat voice can make a good speech sound dull.</p>

<h3>Why stories work</h3>
<p>People remember characters, problems and results more easily than lists. A story gives your point a face. East African communities have a long tradition of storytelling, so your audience is already skilled at listening to a good one. Keep it short (under a minute inside a speech) and link it to your point.</p>

<h3>A story in four parts</h3>
<ol>
<li><strong>Character:</strong> who it is about (it can be you).</li>
<li><strong>Problem:</strong> what went wrong or what they wanted.</li>
<li><strong>Turning point:</strong> what they did or learned.</li>
<li><strong>Result and lesson:</strong> what happened and what it means for the listeners.</li>
</ol>
<p><strong>Weak:</strong> "Saving is important because it helps you in emergencies." <strong>Strong:</strong> "When Mama Ruth's son fell sick at midnight, she had no cash and the clinic would not treat him until a deposit was paid. A neighbour lent her money, but she promised herself never again. Today she saves UGX 2,000 each day in a tin and has never begged for help since. That is why I ask you to start with one small amount tomorrow."</p>

<h3>Voice</h3>
<table>
<thead><tr><th>Element</th><th>Tip</th></tr></thead>
<tbody>
<tr><td>Volume</td><td>Speak to the person in the back row; do not shout</td></tr>
<tr><td>Pace</td><td>Slower than normal conversation; nervous people speed up</td></tr>
<tr><td>Pause</td><td>Stop for a second after key points; silence adds weight</td></tr>
<tr><td>Pitch and tone</td><td>Vary them; a flat voice sounds bored</td></tr>
<tr><td>Clarity</td><td>Finish your words; open your mouth; breathe from your stomach</td></tr>
</tbody>
</table>

<h3>Body and eyes</h3>
<p>Stand tall with feet apart and weight balanced. Keep your hands relaxed, use natural gestures at waist height and avoid pacing, pocketing or covering your mouth. Look at people, one at a time, for a full thought, moving across the room. In a large hall, look at the people in the middle and the back as well as the front.</p>

<h3>Practice that works</h3>
<ul>
<li>Practise out loud, not silently in your head.</li>
<li>Record your voice and listen once for pace and fillers.</li>
<li>Replace "um" with a calm pause.</li>
<li>Time yourself and cut 10 percent.</li>
<li>Do not memorise word for word; memorise the opening, the points and the close.</li>
</ul>

<h3>Case scenario</h3>
<p>Patrick, a youth leader in Jinja, gave a speech on voting and read a page of statistics in a flat voice. People looked at their phones. At the next event, he opened with the story of a neighbour who regretted not voting, spoke more slowly, paused after the main point and spoke to different faces. Afterwards, a dozen people asked how to register. The lesson: one story, a slower pace and eye contact did more than all the statistics.</p>

<blockquote><p><strong>Quick check:</strong> What are the four parts of a short story in a speech?</p><p>Answer: Character, problem, turning point and result with a lesson, because they carry the point to the listeners.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is a better replacement for filler words like "um"?</p><p>Answer: A short, calm pause, because it gives you thinking time and sounds confident.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a 60-second true story from your life that supports a point such as "preparation pays".</li>
<li>Tell it aloud, then record it and listen for pace and filler words.</li>
<li>Mark two places in your speech where you will pause.</li>
<li>Deliver the story to one friend, looking at their eyes.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A short, true story makes a point memorable.</li>
<li>Vary volume, pace and tone; use pauses.</li>
<li>Stand steadily, use natural gestures and make eye contact.</li>
<li>Practise aloud and do not memorise every word.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Handling questions and acting as MC',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to answer audience questions calmly, including hard ones, and to host an event as a master of ceremonies (MC). This matters because the question time and the hosting often show a speaker's real character, and an MC sets the mood for the whole event.</p>

<h3>Answering questions</h3>
<ol>
<li><strong>Listen to the whole question</strong> without interrupting.</li>
<li><strong>Pause</strong> and, if needed, repeat or rephrase it so everyone hears it: "The question is whether the training will be free."</li>
<li><strong>Answer briefly</strong> with the point first, then one reason.</li>
<li><strong>Check</strong> with the asker: "Does that answer your question?"</li>
<li><strong>Move on</strong> politely if someone dominates: "Let me take a question from the back."</li>
</ol>

<h3>Hard questions</h3>
<table>
<thead><tr><th>Situation</th><th>Weak response</th><th>Strong response</th></tr></thead>
<tbody>
<tr><td>You do not know</td><td>Guess or invent an answer</td><td>"I do not have that figure today. I will find out and send it to you by Friday."</td></tr>
<tr><td>Hostile question</td><td>Argue or get angry</td><td>"I understand why you are concerned. Here is what we did and why."</td></tr>
<tr><td>Off-topic question</td><td>Ignore it</td><td>"That is a good point, but outside today's topic. Please see me after."</td></tr>
<tr><td>Long rambling question</td><td>Lose track</td><td>"If I understand correctly, you are asking about the cost. The cost is..."</td></tr>
</tbody>
</table>
<p>Honesty beats bluffing: audiences forgive "I do not know" but not an invented answer. Write down the contact details of anyone you promise to follow up with, and do follow up.</p>

<h3>Acting as MC</h3>
<p>An <strong>MC</strong> (master or mistress of ceremonies) guides the programme and keeps the audience comfortable. Duties:</p>
<ul>
<li>Get the programme in writing, with names spelled correctly and the time for each item.</li>
<li>Arrive early and check the microphone, seating and who is in charge of what.</li>
<li>Welcome the guests, say what is coming and invite the first speaker.</li>
<li>Introduce each speaker briefly: name, role and one fact. Do not give a second speech.</li>
<li>Thank each speaker and fill gaps with a short, clean comment or announcement.</li>
<li>Keep jokes light and respectful, never at the expense of religion, tribe, disabilities or the guests.</li>
<li>Close on time with thanks and the next announcement.</li>
</ul>
<p><strong>Weak introduction:</strong> "Now we have somebody to speak, I think he is from the ministry." <strong>Strong:</strong> "Please welcome Mr James Okuja, the district education officer, who has supported 40 schools to improve reading. Give him a warm applause."</p>

<h3>Case scenario</h3>
<p>At a community fundraising dinner in Entebbe, the MC, Grace, was asked a sharp question by a guest about where earlier funds went. She stayed calm, said, "That is a fair question and the community deserves a clear answer," and invited the treasurer to give the figures. The treasurer shared the totals and promised a written report. The guest thanked her and gave a donation. The lesson: calm honesty converts a hostile moment into trust.</p>

<blockquote><p><strong>Quick check:</strong> What should you say when you do not know the answer?</p><p>Answer: That you do not know, and that you will find out and reply by a stated time, because honesty keeps trust.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is an MC's job when introducing a speaker?</p><p>Answer: To give the name, role and one relevant fact briefly, and let the speaker take over.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List five questions you might be asked about a topic you know, including one hard question.</li>
<li>Write a calm answer to the hard question using point and reason.</li>
<li>Write a 20-second introduction for a real person you know, as if you were an MC.</li>
<li>Practise the phrase "I do not know, but I will find out and tell you by ...".</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Listen, pause, repeat the question and answer briefly.</li>
<li>Admit what you do not know and promise a follow-up.</li>
<li>An MC prepares the programme, introduces briefly and keeps time.</li>
<li>Keep humour respectful.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which is the best structure for a short speech?
- Body, opening, close
* Opening, body with two or three main points, close
- Several openings and no close
- One long paragraph of facts
= A clear opening, a small number of supported points and a close keep listeners with you.

Q: A vote of thanks should usually be:
- Ten minutes long, covering everything
* Short, warm and specific, thanking the guest and organisers
- Critical of the organisers
- A new speech on a different topic
= Brevity and specific thanks make a vote of thanks effective.

Q: Which opening is strongest?
- Sorry, I am not prepared
- I will speak about money
* Last year 40 percent of our members missed a payment for one simple reason; I will show you three fixes
- Um, hello everyone, so yes
= A fact plus a clear promise creates attention and direction.

Q: What is the best way to deal with a question you cannot answer?
- Invent an answer so you do not look weak
- Ignore it
* Say you do not know and promise to find out by a stated time
- Tell the person the question is silly
= Honesty protects trust, and a follow-up promise shows responsibility.

Q: Which are duties of an MC? (choose all that apply)
* Introduce each speaker briefly
* Keep to the time programme
- Give a long speech between every item
- Tell jokes about guests' tribes
= The MC guides the event; long speeches and offensive jokes work against that.

Q: A guest at your event asks a sharp question about missing funds. What is the best response?
- Argue loudly
* Stay calm, acknowledge the question and invite the right person to give the figures
- Walk off the stage
- Ask security to remove the guest
= A calm, honest response builds trust and often turns critics into supporters.

Q: Patrick's speech on voting was a page of statistics in a flat voice and people looked at phones. Which change will help most?
- Read faster
* Open with a short true story, slow down, pause and look at faces
- Add more statistics
- Remove the topic
= Stories, pauses and eye contact connect people to the message.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 6
        [
            'title'   => 'Module 6: Digital, customer and personal communication',
            'summary' => 'Communicate well on social media, spot misinformation, serve customers and complaints, and build your own improvement plan.',
            'lessons' => [
                [
                    'title'   => 'Social media communication, etiquette and misinformation',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to post and comment responsibly, protect your reputation and check whether information is true before sharing. This matters because posts spread quickly, employers and clients read them and false news can cause real harm.</p>

<h3>Think before you post</h3>
<p>Anything online may be copied, screenshotted and shared, even after you delete it. Use the <strong>THINK</strong> check: is it <strong>T</strong>rue, <strong>H</strong>elpful, <strong>I</strong>nspiring or important, <strong>N</strong>ecessary, <strong>K</strong>ind? If it fails most of the tests, do not post it. Never post while angry, and never share private details such as your ID, a child's school or someone's medical information.</p>

<h3>Professional and personal accounts</h3>
<ul>
<li>Use a clear name and a decent profile photo for professional pages such as LinkedIn.</li>
<li>Assume employers will search your name; review old posts and tagged photos.</li>
<li>Do not complain publicly about your employer or customers; raise problems through proper channels.</li>
<li>Ask permission before posting photos of other people, especially children.</li>
<li>Use privacy settings, but do not depend on them; write as if posts are public.</li>
</ul>

<h3>Commenting and disagreeing online</h3>
<p>Disagree with ideas, not people. <strong>Weak comment:</strong> "You are stupid and know nothing." <strong>Strong comment:</strong> "I see it differently. The report I read says school enrolment rose last year. Could you share where your figure comes from?" If someone is abusive, do not feed the argument; mute, block or report. In a business setting, answer complaints politely and move detailed matters to a private message.</p>

<h3>Misinformation</h3>
<p><strong>Misinformation</strong> is false information shared without intent to harm; <strong>disinformation</strong> is false information shared deliberately. Common types: fake health cures, invented quotes, old photos shown as new events and money scams such as "send a small fee to receive a prize". Before sharing, use these checks:</p>
<ol>
<li><strong>Source:</strong> who published it? Is it a known organisation or a random account?</li>
<li><strong>Date:</strong> is it recent, or an old story reposted?</li>
<li><strong>Evidence:</strong> does it give facts, names and places, or just emotion?</li>
<li><strong>Other sources:</strong> do two or three reliable outlets report the same?</li>
<li><strong>Feelings:</strong> does it make you very angry or afraid? Be extra careful; false news is built to trigger emotion.</li>
</ol>
<p>Images can be checked by searching them online to see where else they appeared. If you cannot verify a message, do not forward it, and politely tell the sender, "I could not confirm this, so I will not share."</p>

<h3>Case scenario</h3>
<p>A WhatsApp message in a parents' group in Kampala claimed that a certain common medicine caused harm and urged everyone to forward it. Esther, a parent, noticed there was no source, date or doctor's name. She checked the Ministry of Health and a pharmacist and found the message false. She posted, "I checked with a pharmacist and this seems untrue. Please check with a health worker before sharing." Several parents thanked her and the group agreed to ask for sources before forwarding. The lesson: pausing to verify protects the community.</p>

<blockquote><p><strong>Quick check:</strong> What is the difference between misinformation and disinformation?</p><p>Answer: Misinformation is false information shared without intent to harm; disinformation is shared deliberately to deceive.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Name one check to do before forwarding an alarming message.</p><p>Answer: Check the source and date, or look for the same news from reliable outlets, because false messages often lack both.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Search your own name online and list anything you would not want an employer to see.</li>
<li>Review your last five posts with the THINK check.</li>
<li>Take a viral message you have seen and apply the five verification checks.</li>
<li>Write a polite reply you could send to someone who forwarded false news.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Posts last and spread; use THINK before posting.</li>
<li>Disagree with ideas, respectfully, and report abuse.</li>
<li>Check source, date, evidence and other reports before sharing.</li>
<li>Protect other people's privacy, especially children.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Customer communication and handling complaints',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to greet and serve customers well and turn a complaint into a chance to keep the customer. This matters because customers rarely leave quietly; they tell friends, and in a small community word of mouth can build or end a business.</p>

<h3>Good everyday customer communication</h3>
<ul>
<li><strong>Greet within seconds</strong> and smile, even when busy: "Good afternoon, welcome."</li>
<li><strong>Ask and listen</strong> to find what the customer needs before you recommend.</li>
<li><strong>Use positive language:</strong> say what you can do, not only what you cannot.</li>
<li><strong>Be honest about prices, time and stock</strong>; promise only what you can deliver.</li>
<li><strong>Close well:</strong> thank the customer and invite them back.</li>
</ul>
<table>
<thead><tr><th>Negative</th><th>Positive</th></tr></thead>
<tbody>
<tr><td>We do not have it.</td><td>We are out of that size today; I can have it here by Thursday or show you a similar one.</td></tr>
<tr><td>You will have to wait.</td><td>I will serve you in about ten minutes; you may sit here.</td></tr>
<tr><td>That is not my department.</td><td>Let me find the right person and bring them to you.</td></tr>
</tbody>
</table>

<h3>The HEAT method for complaints</h3>
<ol>
<li><strong>Hear:</strong> let the customer finish without interrupting.</li>
<li><strong>Empathise:</strong> "I understand why you are upset."</li>
<li><strong>Apologise:</strong> a simple, sincere apology for the experience: "I am sorry this happened." Do not blame colleagues.</li>
<li><strong>Take action:</strong> fix it, or explain exactly what you will do and by when, then follow up.</li>
</ol>
<p><strong>Weak response to an angry customer:</strong> "Calm down. It is the policy." <strong>Strong:</strong> "I am sorry your order arrived late; that must have been frustrating, especially with your guests waiting. I will refund the delivery fee today, and I will personally check your next order."</p>

<h3>A written reply to a complaint</h3>
<pre><code>Subject: Your order no. 1042

Dear Mr Wanyama,

Thank you for telling us about the broken screen on your phone case order.
I am sorry for the trouble. We will send a replacement by courier tomorrow
and refund the delivery cost. Please send a photo of the damaged item on
WhatsApp 07xx xxx xxx so we can improve our packaging.

Kind regards,
Amina Tumusiime
Customer Care, ShopRight</code></pre>

<h3>Complaints are information</h3>
<p>Keep a simple record of complaints: what, when and how it was solved. If the same problem repeats, fix the cause. Know your limits: if a request needs a manager, explain this honestly and pass the case on with all details so the customer does not repeat the story.</p>

<h3>Case scenario</h3>
<p>A customer at a Jinja restaurant complained loudly that her food took an hour. The waiter, Samuel, first argued that the kitchen was busy. A manager stepped in, listened, said "I am sorry; an hour is too long", brought a drink at once and offered a 20 percent discount. The customer calmed down and left a good review mentioning the manager. The restaurant then added a rule that guests are updated if food takes longer than 25 minutes. The lesson: listening and a fair offer can turn anger into loyalty.</p>

<blockquote><p><strong>Quick check:</strong> What does HEAT stand for?</p><p>Answer: Hear, Empathise, Apologise, Take action, which is a simple order for calming and resolving a complaint.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why avoid blaming a colleague when apologising?</p><p>Answer: The customer deals with your business as one team; blaming others looks unprofessional and does not solve the problem.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Rewrite three negative phrases you hear in shops into positive ones.</li>
<li>Write a short, polite reply to a customer who received a late delivery.</li>
<li>Role-play a complaint with a friend using HEAT; swap roles.</li>
<li>Start a simple notebook or sheet to record complaints and solutions.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Greet quickly, listen, speak positively and be honest.</li>
<li>Use HEAT: hear, empathise, apologise, take action.</li>
<li>Follow up so the customer sees the promise kept.</li>
<li>Treat complaints as free advice for improvement.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Your communication improvement plan',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to assess your communication skills, choose two or three priorities and follow a 30-day plan. This matters because skills improve only through repeated practice with feedback; reading alone does not change habits.</p>

<h3>Step 1: assess where you are</h3>
<p>Score yourself from 1 (weak) to 5 (strong) on each area, and ask one trusted person to score you too. Differences between the two scores are valuable.</p>
<table>
<thead><tr><th>Area</th><th>You</th><th>Friend</th></tr></thead>
<tbody>
<tr><td>Listening without interrupting</td><td></td><td></td></tr>
<tr><td>Clear, structured speaking</td><td></td><td></td></tr>
<tr><td>Writing emails and messages</td><td></td><td></td></tr>
<tr><td>Meetings and speaking up</td><td></td><td></td></tr>
<tr><td>Handling conflict and feedback</td><td></td><td></td></tr>
<tr><td>Public speaking</td><td></td><td></td></tr>
<tr><td>Online behaviour and customer service</td><td></td><td></td></tr>
</tbody>
</table>

<h3>Step 2: choose priorities</h3>
<p>Pick no more than three. Choose those that would make the biggest difference to your goals, such as getting a job, serving customers better or leading a group.</p>

<h3>Step 3: set specific habits</h3>
<table>
<thead><tr><th>Vague goal</th><th>Specific habit</th></tr></thead>
<tbody>
<tr><td>Be a better speaker</td><td>Speak for two minutes on a topic using PRE, record it every Saturday</td></tr>
<tr><td>Write better</td><td>Use a clear subject line and read every email aloud before sending</td></tr>
<tr><td>Listen more</td><td>Paraphrase once in every conversation this week</td></tr>
<tr><td>Be less nervous</td><td>Speak up once in every class or meeting, in the first ten minutes</td></tr>
</tbody>
</table>

<h3>A 30-day plan</h3>
<ol>
<li><strong>Week 1:</strong> Listening and questions. Practise paraphrasing and open questions daily.</li>
<li><strong>Week 2:</strong> Clear speaking and writing. Use PRE and strong subject lines; ask for feedback on two messages.</li>
<li><strong>Week 3:</strong> Difficult conversation and feedback. Hold one conversation you have avoided; give one piece of SBI feedback.</li>
<li><strong>Week 4:</strong> Public speaking. Give a three-minute talk to friends or a club, and ask for two compliments and one suggestion.</li>
</ol>
<p>Keep a short diary: what you did, what happened and what you learned. Review it at the end of each week and adjust.</p>

<h3>Staying motivated</h3>
<p>Start with small wins, practise with friendly people first, copy good communicators you admire, use feedback and celebrate progress. Joining a club such as a debating society, a Toastmasters-style group or a church or school speaking programme gives regular practice and kind feedback.</p>

<h3>Case scenario</h3>
<p>Elias, a final-year student in Mbarara, scored himself 2 out of 5 for speaking and 2 for writing emails. He chose only those two areas. Each morning he wrote one professional email, and each Saturday he gave a two-minute talk to his hostel mates and recorded it. After 30 days, his friends scored his speaking higher, and he secured an internship after a clear interview and a polished follow-up email. The lesson: a small daily habit beats an occasional big effort.</p>

<blockquote><p><strong>Quick check:</strong> Why ask a friend to score you as well?</p><p>Answer: Others notice habits you cannot see yourself, so differences in scores show where to improve.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why choose only two or three priorities?</p><p>Answer: Focus makes real change possible; trying to fix everything at once usually fails.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Fill in the assessment table and ask one person to score you.</li>
<li>Choose two priorities and write one specific habit for each.</li>
<li>Put your four-week plan on paper or in your phone calendar, with reminders.</li>
<li>Find one group or friend to practise with and tell them your goal.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Assess yourself and ask for another person's view.</li>
<li>Choose two or three priorities and specific, small habits.</li>
<li>Follow a dated plan and keep a short diary.</li>
<li>Regular practice with feedback builds lasting skill.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which action best fits the THINK check before posting?
- Post at once if you are angry
- Post anything that is funny
* Ask if it is true, helpful, necessary and kind before posting
- Post if others are posting it
= THINK helps you avoid posts you will regret and that may harm others.

Q: What is disinformation?
- Information that is out of date
- A mistake in a news headline
* False information shared on purpose to deceive
- A private message
= Disinformation is deliberate; misinformation may be shared by mistake.

Q: What does HEAT stand for in handling complaints?
- Hurry, Explain, Argue, Terminate
* Hear, Empathise, Apologise, Take action
- Help, End, Ask, Thank
- Hide, Ease, Accept, Talk
= HEAT puts listening and care first, then solves the problem.

Q: Which is the positive way to say "We do not have it"?
- "That is not our fault."
* "We are out today; I can have it here by Thursday or show you a similar item."
- "Come back later."
- "Ask someone else."
= Positive language says what you can do and offers a solution.

Q: Which are good checks for a viral message? (choose all that apply)
* Who published it
* When it was published
- How many people forwarded it
* Whether reliable sources report the same news
= Popularity does not prove truth; source, date and confirmation do.

Q: A parent's group is told to forward a warning about a medicine. There is no source or date. What should you do?
- Forward it to be safe
* Check with a health worker or official source before sharing
- Delete your account
- Post that the sender is a liar
= Verifying protects people from harm and false alarm.

Q: Elias wants to improve but feels overwhelmed. Which plan is best?
- Fix all areas of communication this week
* Pick two priorities, set daily and weekly habits, record progress and review after 30 days
- Wait until he has more time next year
- Only read about communication
= Focused, regular practice with review produces real improvement.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which of these is the receiver's role in the communication process?
- Choosing the channel
* Decoding the message and giving feedback
- Encoding the idea
- Creating noise
= The receiver interprets the message and responds, which completes the loop.

Q: A shop owner says "Sort out the stock before they come" and the assistant tidies shelves instead of counting. What is the main problem?
- The assistant was lazy
* The message was vague: unclear task, unclear "they", no deadline and no feedback
- The channel was email
- The shop was too small
= Vague wording and no check for understanding caused the error.

Q: Which channel is best for delivering sensitive bad news?
- A short text
* Face to face or a call
- A group chat
- A notice board
= Tone and the chance to ask questions show care.

Q: Which agreement should be put in writing?
- A greeting
- A compliment
* A price, quantity and delivery date
- A joke
= Written records prevent disputes about money and dates.

Q: Active listening includes: (choose all that apply)
* Paraphrasing what you heard
* Not interrupting
- Preparing your reply while the other person talks
- Checking your phone
= Active listening needs attention and confirmation of understanding.

Q: Which is an open question?
- Did you finish?
- Is it late?
* What happened with the delivery?
- You forgot, right?
= Open questions begin with what or how and invite detail.

Q: A manager asks "Should we open on Sundays?" Which answer follows the PRE structure?
- "It depends; there are many things to think about."
* "Yes, try for one month, because Saturday sales are highest and customers asked; last Saturday 12 left because of queues."
- "Let me tell you the history of Sunday opening."
- "I do not mind."
= It begins with the point, gives a reason and an example.

Q: Which sentence is in plain language?
- We shall utilise synergies
* We will work together
- Facilitate the commencement of the exercise
- At this point in time
= Plain words help everyone understand.

Q: What is the best subject line for an email asking for a meeting?
- Hello
- Urgent!!!
* Request: 15-minute meeting on internship, Thursday or Friday
- Please
= A specific subject shows the purpose and what you need.

Q: Which email habit is professional?
- Writing in capital letters
* Reading the message aloud and checking the attachment before sending
- Using Reply All for every message
- Using a nickname address
= Checking prevents errors; capitals look like shouting.

Q: Which line is best in meeting minutes?
- Discussed the fundraiser
* Agreed to hold a dinner on 15 August; Sarah to book the venue by 20 July
- Everyone talked a lot
- No decisions
= Minutes record decisions with an owner and a deadline.

Q: Which is professional behaviour on a work WhatsApp group?
- Sending ten short messages in a row
* Greeting, identifying yourself and sending one complete message
- Sharing a customer's phone number without asking
- Voice notes of five minutes for a legal agreement
= Clear, respectful messaging and permission for sharing are professional.

Q: In a video call, you should: (choose all that apply)
* Mute your microphone when not speaking
* Test sound and camera beforehand
- Sit with a bright window behind you
* Use a quiet place
- Eat noisily
= Good setup and manners help others see and hear you.

Q: What does SBI feedback include?
- Suggestion, blame and insult
* Situation, behaviour and impact
- Silence, brief and inform
- Shout, beg and insist
= SBI keeps feedback specific and about actions.

Q: Which comment is a good way to receive criticism?
- "You are wrong."
* "Thank you. Can you give me an example so I can improve?"
- Silence and walking away
- "Everyone else does it."
= Asking for an example shows maturity and helps you act.

Q: Which is honest persuasion?
- Hiding costs until the customer signs
* Showing how your offer meets the listener's need with real evidence
- Threatening the customer
- Lying about limited stock
= Honest persuasion links true benefits to needs.

Q: Why is a private place better for a difficult conversation?
- It looks more serious
* People can speak openly without embarrassment and are less defensive
- It saves electricity
- The message is easier to deny
= Privacy reduces defensiveness and allows honest talk.

Q: You must refuse a friend a loan. Which reply is best?
- "No."
* "I value our friendship and cannot lend now; I can help you plan how to approach the SACCO."
- "I will think" and never reply
- "You never repay."
= A kind refusal with an alternative protects trust.

Q: What should a meeting chair do with an off-topic point?
- Debate it at length
* Park it on a list for later and return to the agenda
- Ignore the person rudely
- Close the meeting
= Parking protects time and respects the speaker.

Q: A project is blocked because you are waiting for a budget code. Which update is best?
- Say nothing
* "Done: poster designed. Next: print on Thursday. Blocked: I need the budget code from finance."
- "It is not my fault."
- "Everything is fine."
= Naming the blocker invites help early.

Q: At a village meeting, which opening shows respect?
- "I have come to collect data."
* A proper greeting in the local language, a title for the chairman, then your purpose and a request for permission
- Starting with technical charts
- Speaking only to your colleague
= Greetings and titles show respect and open cooperation.

Q: When is mixing languages a problem?
- Never
* When some people in the group do not understand and feel excluded
- When talking to friends who all share the languages
- When saying hello
= Use the language everyone understands in mixed groups.

Q: Which are types of speech? (choose all that apply)
* Informative
* Persuasive
* Ceremonial
- Silent
= Informative, persuasive and ceremonial speeches have different purposes.

Q: Which is the best structure for a speech?
- Close, opening, body
* Opening, body (two or three points), close
- Only a body
- Ten points and no close
= A simple structure with few points is easy to follow.

Q: Moses is asked at short notice to give a vote of thanks. What should he include?
- A new topic
* Thanks to the guest, one specific thing that mattered, thanks to organisers and a warm close
- Complaints about organisers
- A long personal story
= A short, specific, warm format works well in ceremonies.

Q: Which story structure works in a speech?
- Date, place, name only
* Character, problem, turning point, result and lesson
- A list of statistics
- A joke without a point
= A story needs a problem and change to carry the message.

Q: A speaker finishes a question by saying "I do not know that figure. I will find out and email you by Friday." What does this show?
- Weakness
* Honesty and responsibility, which build trust
- That the speaker is unprepared and should leave
- Poor manners
= Audiences prefer honesty to invented answers.

Q: Before forwarding an alarming message on WhatsApp, what should you do first?
- Forward it to everyone
* Check the source, date and whether reliable sources report it
- Delete it without reading
- Add an angry comment
= Verification stops false information from spreading.

Q: A customer is angry that an order arrived late. Which reply follows HEAT?
- "Calm down, it is policy."
* "I am sorry the order was late; I understand it affected your guests. I will refund the delivery fee today."
- "It was the rider's fault."
- "Please go to another shop."
= It hears, empathises, apologises and takes action.

Q: Elias scored himself 2 out of 5 in speaking and writing. What is the best next step?
- Wait for inspiration
* Pick these two areas, set daily and weekly habits and review progress after 30 days
- Ignore the scores
- Practise everything at once
= Focus and regular practice produce change.
QUIZ,
];
