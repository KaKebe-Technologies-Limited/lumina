<?php
return [
    'slug'        => 'nlp-chatbots-conversational-ai',
    'title'       => 'Chatbots and Natural Language Processing for Africa',
    'category'    => 'ai',
    'level'       => 'intermediate',
    'icon'        => 'users',
    'sort'        => 460,
    'summary'     => 'Learn how computers work with human language, why African languages need more attention, and how to design, build, test and run a helpful bilingual chatbot for a school, clinic or small business.',
    'description' => '<p>Every day, millions of people in Africa write messages, send voice notes and ask questions in languages such as Luganda, Swahili, English, Kinyarwanda and many more. <strong>Natural language processing</strong> (NLP) is the field that teaches computers to work with that language: to count words, sense feelings, find names, translate and answer questions. A <strong>chatbot</strong> is one of the most useful ways to put NLP to work, because it can answer common questions at any hour on a channel people already use.</p><p>This intermediate course starts with the building blocks of NLP in plain words, then looks at why most African languages have far less data and tools than English, and what communities are doing about it. You will learn how to design a chatbot on paper first (goals, users, channels, intents, conversation flows, hand-off to a human), how to write a knowledge base, how to build a simple bot with a free no-code tool, and how to try a short beginner Python example. Then you will test with real users, measure success, and protect privacy, consent and accessibility.</p><p>The course ends with a capstone: a bilingual FAQ chatbot design and prototype for a school, clinic or small business. The skills are about thinking and design, so they stay useful as tools change.</p>',
    'outcomes'    => [
        'Explain in plain words what NLP is and describe tokens, text cleaning, sentiment, classification, named entities, translation and speech',
        'Count words and run a simple sentiment check on short messages',
        'Explain why many African languages are under-resourced and describe community-led responses',
        'Judge the ethics of collecting and using language data, including consent and fair credit',
        'Design a chatbot with clear goals, users, channels, intents, entities and conversation flows',
        'Choose between rule-based, retrieval and generative bots for a given job',
        'Write a clear knowledge base and FAQ in simple language, with a safe fallback and hand-off to a person',
        'Build and test a simple FAQ chatbot, and measure it with resolution rate and satisfaction',
        'Protect privacy, obtain consent and make a chatbot accessible to all users',
    ],
    'requirements' => ['Completed Practical AI Tools: Using AI Assistants Wisely (ai-tools-literacy), or equivalent experience with an AI chat assistant', 'A computer or smartphone with internet access', 'Basic Python (Introduction to Programming with Python) is helpful but not required'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Masakhane: grassroots NLP for African languages', 'url' => 'https://www.masakhane.io/'],
        ['title' => 'Mozilla Common Voice: open voice data in many languages', 'url' => 'https://commonvoice.mozilla.org/'],
        ['title' => 'Python documentation', 'url' => 'https://docs.python.org/3/'],
        ['title' => 'Personal Data Protection Office, Uganda', 'url' => 'https://pdpo.go.ug/'],
        ['title' => 'WhatsApp Business', 'url' => 'https://business.whatsapp.com/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: How computers work with language',
            'summary' => 'Meet the building blocks of NLP: tokens, cleaning, counting, sentiment, classification, entities, translation and speech.',
            'lessons' => [
                [
                    'title'   => 'What NLP is and how text becomes data',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what natural language processing is and show how a sentence is broken into pieces a computer can handle. This matters because every chatbot, translator and voice assistant starts with these same first steps.</p>

<h3>What NLP means</h3>
<p><strong>Natural language processing (NLP)</strong> is the part of computing that works with human language, written or spoken. Natural language is the language people use every day, such as Luganda, Swahili or English. It is "natural" because it grew among people, unlike a programming language that someone designed with strict rules. People understand "Nze nja kujja enkya" or "I will come tomorrow" without effort. A computer sees only characters and numbers, so NLP is the set of methods that turn language into something a computer can count, compare and learn from.</p>

<h3>From text to tokens</h3>
<p>The first step is usually to split text into small pieces called <strong>tokens</strong>. A token is often a word, but it can also be a part of a word, a number or a punctuation mark. This step is called <strong>tokenisation</strong>.</p>
<pre><code>Sentence: "Mobile money is fast."
Word tokens: ["Mobile", "money", "is", "fast", "."]</code></pre>
<p>Splitting on spaces works well for English, but languages differ. In many Bantu languages, such as Luganda and Swahili, one written word can carry a whole idea: the subject, the tense and the object are joined together. The Swahili word "nitakupigia" means "I will call you", and it is a single word. A tool built only for English may handle such words badly, which is an early hint at the challenges in Module 2.</p>

<h3>Turning tokens into numbers</h3>
<p>Computers calculate with numbers, so tokens are given numeric forms. The simplest way is to count how often each word appears. More advanced systems give each token a long list of numbers, called an <strong>embedding</strong>, so that words with similar meaning get similar numbers. You do not need the mathematics. You only need the idea: text is turned into numbers, and patterns in those numbers let the computer do useful work.</p>

<h3>Common NLP jobs</h3>
<table>
<thead><tr><th>Job</th><th>What it does</th><th>Everyday example</th></tr></thead>
<tbody>
<tr><td>Classification</td><td>Puts text in a group</td><td>Spam or not spam in your inbox</td></tr>
<tr><td>Sentiment analysis</td><td>Detects positive, negative or neutral feeling</td><td>Customer comments about a shop</td></tr>
<tr><td>Named entity recognition</td><td>Finds names, places, dates, amounts</td><td>Pulling "Kampala" and "50,000 UGX" from a message</td></tr>
<tr><td>Machine translation</td><td>Converts text between languages</td><td>English to Swahili notices</td></tr>
<tr><td>Speech recognition</td><td>Turns speech into text</td><td>Voice typing on your phone</td></tr>
<tr><td>Question answering</td><td>Finds or writes an answer</td><td>A clinic chatbot answering opening hours</td></tr>
</tbody>
</table>

<h3>A mini-case</h3>
<p>Nakato runs a small tailoring shop in Jinja. Customers send her WhatsApp messages such as "How much for a school uniform?" and "When will my dress be ready?" She wants to sort these messages automatically into "price" and "order status". That is a classification job. Before a computer can do it, the messages must be split into tokens and turned into numbers, which is exactly the process in this lesson.</p>

<blockquote><p><strong>Quick check:</strong> Why does a tool built only for English tokenisation sometimes struggle with Swahili or Luganda?</p><p>Answer: In these languages one written word can join several ideas (subject, tense, object), so simply splitting on spaces can give pieces that do not match how the language works.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Nakato wants to sort customer messages into "price" and "order status". Which NLP job is this?</p><p>Answer: Classification, because each message is placed into one of a small set of groups.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write five real questions that customers, parents or patients might send to a school, clinic or shop you know.</li>
<li>Split each question into word tokens by hand, writing each token in a separate box.</li>
<li>Mark any word that carries more than one idea, such as a Swahili or Luganda word that joins a verb and a person.</li>
<li>Next to each question, name the NLP job from the table that would help answer it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>NLP is the set of methods that let computers work with human language.</li>
<li>Text is split into tokens and then turned into numbers.</li>
<li>Common jobs are classification, sentiment, entity finding, translation, speech and question answering.</li>
<li>Languages differ in how words are built, so tools must fit the language.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Cleaning text and counting words',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to clean a short piece of text and count which words appear most often. This matters because real messages are untidy, and counting words is the simplest way to discover what people are actually talking about.</p>

<h3>Why text needs cleaning</h3>
<p>People write messages quickly. One customer writes "PRICE?", another writes "price pls", another writes "Price, please!!!". To a human these are the same question. To a computer the strings are different, so counting them would give wrong results. <strong>Text cleaning</strong> (also called pre-processing) makes the text more regular before counting or learning from it.</p>

<h3>Common cleaning steps</h3>
<table>
<thead><tr><th>Step</th><th>Before</th><th>After</th></tr></thead>
<tbody>
<tr><td>Lower-case everything</td><td>Price PLEASE</td><td>price please</td></tr>
<tr><td>Remove punctuation</td><td>price, please!!!</td><td>price please</td></tr>
<tr><td>Remove extra spaces</td><td>price&nbsp;&nbsp;&nbsp;please</td><td>price please</td></tr>
<tr><td>Remove stop words (very common words)</td><td>what is the price</td><td>price</td></tr>
<tr><td>Normalise spellings</td><td>pls, plz</td><td>please</td></tr>
</tbody>
</table>
<p>A <strong>stop word</strong> is a very common word such as "the", "is" or "and" that rarely tells you what the message is about. Be careful, though. Cleaning can remove meaning. In "I do not like it", deleting "not" would turn a complaint into praise. Clean only as much as you need, and look at the result.</p>

<h3>Counting words</h3>
<p>After cleaning, count how many times each word appears. This is called a <strong>word frequency</strong> count. If 40 of 100 messages to a clinic contain "appointment" and 25 contain "price", you know what to put first in your answers.</p>
<pre><code>Messages:
1. "Price please"
2. "What is the price of a uniform?"
3. "Uniform ready?"

Counts after cleaning (lower-case, no stop words):
price   2
uniform 2
ready   1</code></pre>

<h3>Scenario: the clinic reception</h3>
<p>A small clinic in Mbale receives 200 WhatsApp messages in a week. A staff member copies them into a spreadsheet, makes everything lower-case with the LOWER formula, and counts the words with a pivot table. The top words are "appointment", "time", "malaria", "price" and "open". Within an hour the clinic knows which five questions a chatbot must answer well. No advanced AI was needed, just cleaning and counting.</p>

<h3>Common mistakes</h3>
<ul>
<li>Cleaning too hard and removing words such as "not" that change the meaning.</li>
<li>Forgetting that spelling varies: "tomorrow", "tomorow" and "2morrow" may all appear.</li>
<li>Counting only English words when customers mix English with Luganda or Swahili in the same message.</li>
</ul>

<blockquote><p><strong>Quick check:</strong> Why is it risky to delete the word "not" as a stop word before checking sentiment?</p><p>Answer: "Not" reverses meaning, so "not happy" would look like "happy" and a complaint would be counted as praise.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A clinic's top counted words are "appointment", "time" and "price". What should the chatbot do first?</p><p>Answer: Answer appointments, opening times and prices well, because those are the most common questions.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Collect 15 short messages or questions (real ones with permission, or invented ones) for a shop or school.</li>
<li>Type them into a spreadsheet, one per row, and use the LOWER function to make a lower-case copy.</li>
<li>Remove punctuation by hand or with Find and Replace, then split the words into separate cells.</li>
<li>Count how often each word appears and list the top five.</li>
<li>Write one sentence on what these five words tell you about your users.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Cleaning makes different spellings and styles of the same message look the same.</li>
<li>Stop words are very common words; remove them only when it helps.</li>
<li>Word counts show what users talk about most.</li>
<li>Always check that cleaning has not changed the meaning.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Sentiment, classification and named entities',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain three core NLP tasks (sentiment, classification and entity recognition) and say how a chatbot uses each. This matters because these tasks are how a bot decides what a person wants and how they feel.</p>

<h3>Sentiment analysis</h3>
<p><strong>Sentiment analysis</strong> estimates whether a text is positive, negative or neutral. A simple method uses word lists: count the positive words ("good", "thanks", "fast") and the negative words ("bad", "late", "broken"), and compare. More advanced methods learn from thousands of labelled examples. A business can use sentiment to notice when customers are becoming unhappy.</p>
<p>Sentiment is hard in practice. Sarcasm ("Great, the shop is closed again") looks positive by word count. People also mix languages, use slang and emoji, and write the same feeling in many ways. A tool trained mostly on English may do poorly on Luganda or Sheng, so treat the result as a hint, not a fact.</p>

<h3>Classification and intents</h3>
<p><strong>Text classification</strong> puts a message into one of several groups. In a chatbot these groups are called <strong>intents</strong>: the thing the person wants. "How much is a bed at the ward?", "What is the cost of admission?" and "Bei gani?" may all belong to the intent <em>ask_price</em>. The bot uses the intent to pick the right answer. You will design intents in Module 3.</p>

<h3>Named entity recognition</h3>
<p>A <strong>named entity</strong> is a specific item in the text, such as a person, place, date, time, organisation or amount. <strong>Named entity recognition (NER)</strong> finds and labels them. In a chatbot these details are called <strong>entities</strong> (or slots).</p>
<pre><code>Message: "I want to book an appointment on Friday at 10am in Mbale"
Intent:   book_appointment
Entities: day = Friday
          time = 10am
          place = Mbale</code></pre>
<p>Notice the teamwork: the intent tells the bot <em>what</em> the person wants, and the entities supply the details it needs to act. If a detail is missing, the bot asks a follow-up question, such as "Which day would you like?"</p>

<h3>Putting it together</h3>
<table>
<thead><tr><th>Message</th><th>Sentiment</th><th>Intent</th><th>Entities</th></tr></thead>
<tbody>
<tr><td>Thanks, how much is school fees for Term 2?</td><td>Positive</td><td>ask_fees</td><td>term = 2</td></tr>
<tr><td>My order is late again!</td><td>Negative</td><td>order_status</td><td>none yet</td></tr>
<tr><td>Is the pharmacy open on Sunday?</td><td>Neutral</td><td>opening_hours</td><td>day = Sunday</td></tr>
</tbody>
</table>

<h3>Scenario: spotting an unhappy customer</h3>
<p>A small online shop notices that messages with negative sentiment and the intent <em>order_status</em> often come together. The owner decides that such messages should skip the bot and go straight to a person, with a quick apology. Combining two NLP results gave a better rule than either alone.</p>

<blockquote><p><strong>Quick check:</strong> In "Book me a dentist visit on Monday", which part is the intent and which is an entity?</p><p>Answer: The intent is booking a dentist visit (the goal), and "Monday" is an entity (a detail needed to act on it).</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why can a word-list sentiment tool misread "Great, the clinic is closed again"?</p><p>Answer: It counts the positive word "great" and misses the sarcasm, so a complaint looks like praise.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write six messages a user might send to a clinic or school.</li>
<li>For each, write the sentiment (positive, negative, neutral) and the intent in a short label such as ask_fees.</li>
<li>Underline the entities (dates, places, names, amounts) and write what type each is.</li>
<li>Find one message where sentiment could be misread and explain why.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Sentiment estimates feeling but struggles with sarcasm, slang and mixed languages.</li>
<li>Classification gives the intent: what the person wants.</li>
<li>Entity recognition finds the details such as dates, places and amounts.</li>
<li>Bots combine intent and entities, and ask follow-up questions when details are missing.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Translation and speech technology',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain how machine translation and speech technology work in simple terms, and say when to trust them and when to check with a person. This matters because many Africans prefer to speak or write in their home language, and these tools can open services to people who find English difficult.</p>

<h3>Machine translation</h3>
<p><strong>Machine translation</strong> converts text from one language to another. Modern systems learn from very large collections of sentences that exist in two languages. They notice patterns between the sentences, and then produce a new translation word piece by word piece. For languages with lots of translated material, such as French and English, the results are often good. For languages with little material, the results can be rough, with wrong words, missing meaning or an unnatural tone.</p>
<p>Quality depends on three things: how much data the language has, how close the language is to others the system already knows, and the kind of text. A simple notice such as "The clinic opens at 8 a.m." usually translates better than a joke, a proverb or a legal paragraph.</p>

<h3>Speech recognition and speech synthesis</h3>
<table>
<thead><tr><th>Technology</th><th>Direction</th><th>Example use</th></tr></thead>
<tbody>
<tr><td>Speech recognition (speech-to-text)</td><td>Voice to written text</td><td>Typing a message by speaking; turning a voice note into text</td></tr>
<tr><td>Speech synthesis (text-to-speech)</td><td>Written text to voice</td><td>A phone line reading out information; helping someone who cannot see well</td></tr>
</tbody>
</table>
<p>Speech systems need many hours of recorded speech with matching text. Accents, background noise and code-switching (changing language in a sentence) all make the job harder. Voice is very important in Africa, where many people prefer to speak than to type and where some adults have limited reading skills. That is why voice-based services are an exciting area for the future.</p>

<h3>A safe way to use translation</h3>
<ol>
<li>Translate with a tool to get a first draft.</li>
<li>Translate the result back into the first language to see if the meaning survived.</li>
<li>Ask a fluent speaker to check important messages, such as health, money or safety notices.</li>
<li>Keep sentences short and plain; avoid idioms.</li>
<li>Save corrected versions so you can reuse them.</li>
</ol>

<h3>Scenario: a vaccination notice</h3>
<p>A health centre wants to send a message in Luganda about a vaccination day. The nurse uses a translation tool for a draft, then asks a Luganda-speaking colleague to read it. The colleague notices that one phrase sounds like the vaccine is for adults only, which is not the intended meaning. Without the human check, parents could have stayed away. For sensitive content the human check is not optional.</p>

<blockquote><p><strong>Quick check:</strong> Why does machine translation usually work better for French than for a smaller African language?</p><p>Answer: There are far more translated sentences available to learn from for French, and more data usually means better quality.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A shop wants a voice note turned into written text. Which technology is that?</p><p>Answer: Speech recognition (speech-to-text), because it converts spoken sound into text.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a plain 20-word notice in English, such as opening hours for a clinic or shop.</li>
<li>Use a free translation tool to translate it into Swahili, Luganda or another language you know.</li>
<li>Translate the result back into English and compare it with your original.</li>
<li>Ask a fluent speaker to rate the translation and note any change they would make.</li>
<li>Write down what kinds of sentences the tool handled well and badly.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Machine translation learns from sentence pairs; quality depends on available data.</li>
<li>Speech recognition turns voice into text; speech synthesis turns text into voice.</li>
<li>Voice tools matter where many people prefer speaking or have limited reading skills.</li>
<li>Always have a fluent person check important translations.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the first step in most NLP pipelines, splitting a sentence into small pieces?
- Embedding
* Tokenisation
- Translation
- Classification
= Tokenisation splits text into tokens (words or word parts) so they can be counted or turned into numbers.

Q: A clinic receives messages "PRICE?", "price pls" and "Price, please!!!". Which cleaning steps help count them as the same question?
* Make lower-case, remove punctuation and normalise "pls" to "please"
- Translate them into French
- Delete every message that has capital letters
- Count only the messages with exclamation marks
= Lower-casing, removing punctuation and normalising spellings make different forms look alike so the counts are correct.

Q: You remove all stop words before running a sentiment check on "I am not happy with the service". What problem might occur?
- The message becomes too long
* Removing "not" could make the message look positive
- The computer will refuse to read it
- Nothing, stop words never matter
= "Not" reverses the meaning, so careless cleaning can turn a complaint into apparent praise.

Q: A customer writes "I want to book a haircut on Saturday at 3pm". Which statement is correct?
- Intent: Saturday; Entity: book a haircut
* Intent: book a haircut; Entities: Saturday and 3pm
- Intent: 3pm; Entity: haircut
- There is no intent, only sentiment
= The intent is what the person wants; the entities are the details needed to act on it.

Q: Which of these are good reasons to have a fluent speaker check a machine translation? (choose all that apply)
* The text is a health or safety notice
* The language has little training data, so errors are likelier
- The text is longer than one line
* A wrong meaning could cause people to act wrongly
= Important, sensitive or low-resource content needs a human check. Length alone is not the reason.

Q: A shop owner wants to know which topics customers ask about most. What is the quickest useful method?
- Train a speech synthesis model
* Clean the messages and count the most frequent words
- Translate all messages into English first
- Ask a chatbot to guess
= Word frequency counts on cleaned messages quickly show common topics.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: African languages and NLP',
            'summary' => 'See why many African languages have little data, how communities are building it, and how to treat language data ethically.',
            'lessons' => [
                [
                    'title'   => 'Why African languages are under-resourced',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what "under-resourced" means for a language, list the main reasons many African languages lack NLP tools, and describe how this affects real people. This matters because if you build a chatbot in Uganda or Kenya, you will quickly meet these limits, and knowing them helps you plan around them.</p>

<h3>Many languages, little data</h3>
<p>Africa is home to well over two thousand languages, and many people speak two or three of them every day. Yet most NLP tools are built first for a handful of languages with huge amounts of text online, such as English, Chinese, Spanish and French. A language is called <strong>under-resourced</strong> (or low-resource) when there is little digital data and few tools for it. That does not mean the language is small or unimportant. Swahili, Hausa, Yoruba and Luganda are spoken by millions of people, but they have much less digital text, translation pairs and recorded speech than English.</p>

<h3>The main reasons</h3>
<table>
<thead><tr><th>Reason</th><th>What it means in practice</th></tr></thead>
<tbody>
<tr><td>Little written content online</td><td>Many languages are mostly spoken, or written in notebooks, church books and school texts that have never been digitised</td></tr>
<tr><td>Few translated texts</td><td>Machine translation needs sentence pairs; they are scarce for most African language pairs</td></tr>
<tr><td>Few recorded and labelled voices</td><td>Speech tools need many hours of recordings with matching text</td></tr>
<tr><td>Spelling and writing differences</td><td>Several spellings, dialects and tone marks make the data uneven</td></tr>
<tr><td>Code-switching</td><td>People mix English, Luganda, Swahili or Sheng in one sentence, which tools trained on single languages handle badly</td></tr>
<tr><td>Limited funding and fewer trained researchers</td><td>Less money and fewer jobs for people working on these languages</td></tr>
<tr><td>Keyboards and fonts</td><td>Some characters and tone marks are hard to type, so people leave them out</td></tr>
</tbody>
</table>

<h3>Who is affected</h3>
<p>When tools work badly in your language, you are pushed towards a language that is not your own. A grandmother who speaks only Lusoga cannot use a banking app that talks only in English. A farmer who prefers Runyankole cannot ask a voice assistant about planting. Children may find that educational tools do not respect their mother tongue. Under-resourced languages can also be pushed aside over time if all digital life happens in a few big languages.</p>

<h3>What it means for chatbot builders</h3>
<p>If you want to serve people in a local language, you cannot assume a ready-made tool will understand it. You may need to write the answers yourself, test with native speakers, offer a menu of choices instead of free typing, and plan a hand-off to a person when the bot is lost. Being honest about the bot's limits is part of good design.</p>

<h3>Scenario: a bot that did not understand</h3>
<p>A school launches a WhatsApp bot in English only. Many parents reply in Luganda or in a mix. The bot answers "Sorry, I did not understand" over and over, and parents stop using it. The school then adds a simple menu with numbered choices in two languages and a button to talk to the office. Use rises, because people no longer have to guess the "right" words.</p>

<blockquote><p><strong>Quick check:</strong> Does "under-resourced" mean the language has few speakers?</p><p>Answer: No, it means the language has little digital data and few tools, even if millions of people speak it.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A bot only understands formal Luganda but users mix Luganda and English. What is one good design response?</p><p>Answer: Offer numbered menu choices and a hand-off to a person, and test with real users who mix languages, so the bot does not depend on perfect free typing.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a language you know well other than English.</li>
<li>Search the web for a news article, a Wikipedia page and a translation tool in that language, and note how many you find.</li>
<li>Try typing a sentence into a free translation tool in both directions and judge the quality.</li>
<li>Write down three reasons your language may have less digital data than English.</li>
<li>Write one sentence on what a chatbot builder should do about it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Under-resourced means little digital data and few tools, not few speakers.</li>
<li>Causes include little online text, few translations, few recordings and mixed language use.</li>
<li>People who use only local languages can be left out of digital services.</li>
<li>Builders must plan for limits: menus, native-speaker tests and human hand-off.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Community efforts: building data and tools together',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe how communities collect language data, name examples of community-led efforts, and see where you could contribute. This matters because progress for African languages is largely being made by Africans themselves, and you can be part of it.</p>

<h3>The idea of community-driven NLP</h3>
<p>Big companies cannot easily build good tools for languages they do not speak. Speakers of the language know the words, the spelling, the jokes and the context. So community-driven projects bring together students, teachers, researchers, translators and volunteers to build data and tools "for us, by us". One well-known example is <strong>Masakhane</strong>, a grassroots community of African researchers and volunteers working on NLP for African languages, such as machine translation, with open sharing of work. Another is <strong>Mozilla Common Voice</strong>, an open project where volunteers record themselves reading sentences and others listen and check them, building free speech data in many languages, including some African ones.</p>

<h3>Ways of collecting data</h3>
<table>
<thead><tr><th>Method</th><th>How it works</th><th>Watch out for</th></tr></thead>
<tbody>
<tr><td>Volunteer recording</td><td>People read prompts aloud and others validate</td><td>Mostly young, urban, male voices unless you reach out widely</td></tr>
<tr><td>Translation by volunteers or paid translators</td><td>Sentences from English are translated into the local language</td><td>Translators need fair pay and clear style rules</td></tr>
<tr><td>Digitising existing texts</td><td>Scanning and typing books, newspapers, school texts</td><td>Copyright and permission from owners</td></tr>
<tr><td>Annotation</td><td>People label text (for example positive or negative, or names of places)</td><td>Labellers must understand the language and be paid fairly</td></tr>
<tr><td>Community workshops</td><td>Groups meet to write, correct and discuss data</td><td>Needs organisation and follow-up</td></tr>
</tbody>
</table>

<h3>Other helpful efforts</h3>
<ul>
<li>Universities and research labs in Africa that study speech and language for local languages.</li>
<li>Funders that pay for open datasets in African languages, so that data is shared rather than locked away.</li>
<li>Open-source tools and open datasets that anyone can use and improve.</li>
<li>Writing and correcting content in your language on public knowledge sites, which improves the text available for everyone.</li>
</ul>

<h3>What you can do as a learner</h3>
<ol>
<li>Record and validate sentences in your language on an open voice project, if your language is available.</li>
<li>Translate or correct short, useful sentences such as health messages and school notices.</li>
<li>Build small word lists, for example common greetings, numbers and chatbot phrases, and share them with permission.</li>
<li>Test tools with your community and report errors honestly.</li>
<li>Join a learning community and ask how to help.</li>
</ol>

<h3>Scenario: a student word list</h3>
<p>Brian, a diploma student in Gulu, builds a list of 300 common chatbot phrases in English and Acholi with the help of two teachers and his grandmother. Each row has the English phrase, the Acholi phrase and a note on formal or casual use. He asks permission from the teachers and agrees on how the list may be shared. His small list will make his own school chatbot better, and it could help other builders later.</p>

<blockquote><p><strong>Quick check:</strong> Why do community-driven projects often produce better data for a language than outsiders working alone?</p><p>Answer: Speakers know the vocabulary, spelling and context, so they can write and check data that is accurate and natural.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Brian wants to share his phrase list publicly. What should he do first?</p><p>Answer: Agree with everyone who contributed on how it may be used and shared, and credit them, before publishing.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Visit the Masakhane and Common Voice websites from the resources list and read about what they do.</li>
<li>Check whether your language is listed on Common Voice.</li>
<li>Write ten useful sentences (greetings, opening hours, thanks) in your language with English meanings.</li>
<li>Ask one fluent speaker to correct them.</li>
<li>Decide who gave help and how you will thank them.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Communities of speakers are central to building good language data and tools.</li>
<li>Data comes from recordings, translations, digitised texts, labelling and workshops.</li>
<li>Fair pay, credit and permission are part of doing it properly.</li>
<li>Learners can contribute through small, careful, well-checked data.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Ethics of language data',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to judge whether a plan to collect or use language data is fair, safe and respectful. This matters because language data comes from people, and careless collection can expose their private lives or take value from a community without giving anything back.</p>

<h3>Whose words are they?</h3>
<p>A WhatsApp voice note, a church sermon recording, a farmer's phone call and a school essay are all language data. They also belong to real people. Before you use them to build or train anything, ask: who made this, did they agree, and who benefits?</p>

<h3>Six questions to ask before collecting data</h3>
<ol>
<li><strong>Consent:</strong> Did people clearly agree, in a language they understand, to how the data will be used? Silence is not consent.</li>
<li><strong>Privacy:</strong> Does the data contain names, phone numbers, health details, voices or locations that identify someone? Can you remove or hide these?</li>
<li><strong>Purpose:</strong> Are you collecting only what you need for a clear aim?</li>
<li><strong>Fair return:</strong> Do the people and the community gain something, such as payment, a free tool or recognition?</li>
<li><strong>Representation:</strong> Does your data include women and men, young and old, towns and villages, and different dialects, or only one group?</li>
<li><strong>Control and sharing:</strong> Who can see, copy or sell the data? Can a person ask to have their data removed?</li>
</ol>

<h3>Good and poor practice</h3>
<table>
<thead><tr><th>Poor practice</th><th>Better practice</th></tr></thead>
<tbody>
<tr><td>Copying private group chats to train a bot</td><td>Asking members, and using only data they agree to share</td></tr>
<tr><td>Recording people without explaining the purpose</td><td>Using a short consent script in the local language, read aloud</td></tr>
<tr><td>Paying contributors nothing and keeping all credit</td><td>Paying fairly and naming contributors who wish to be named</td></tr>
<tr><td>Keeping data forever "just in case"</td><td>Keeping data only as long as needed, then deleting it</td></tr>
<tr><td>Collecting only from one group</td><td>Reaching a mix of voices so the tool works for everyone</td></tr>
</tbody>
</table>

<h3>The law and good manners</h3>
<p>Uganda has a Data Protection and Privacy Act (2019), and many other countries have similar laws. They generally say that personal data must be collected for a stated purpose, kept safe and not kept longer than needed, and that people have rights over it. You are not expected to be a lawyer, but you should know such laws exist, look for your country's rules, and follow them. Beyond the law, ask whether you would be comfortable explaining your plan to the people whose words you are using.</p>

<h3>Bias in data and tools</h3>
<p>If a speech tool is trained mostly on men's voices, it may work worse for women. If a text tool is trained on one dialect, it may treat other dialects as "wrong". Bias in data becomes bias in the chatbot. Test with a variety of real users and fix gaps.</p>

<h3>Scenario: the helpful volunteer</h3>
<p>A volunteer wants to collect 500 voice recordings of mothers at a village clinic to train a health bot. She plans to ask the nurse to "just let her record the consultations". Using the six questions, she realises these are private health talks and the mothers have not agreed. She changes her plan: she invites volunteers to read pre-written sentences aloud, explains the project, uses a consent script and pays for their transport.</p>

<blockquote><p><strong>Quick check:</strong> A friend says, "People posted these messages publicly, so I can use them any way I like." What is wrong with this?</p><p>Answer: Public posting does not mean permission for every use; people may not expect their words to train a tool, and personal details may still need protection.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why does it matter to collect voices from both women and men?</p><p>Answer: Tools trained on one group often work worse for others, so a mixed set makes the chatbot fairer and more accurate.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Imagine you will collect 100 voice or text samples for a school chatbot in a local language.</li>
<li>Write a three-sentence consent message in plain English that explains the purpose, how data is kept and how to withdraw.</li>
<li>Translate it into your local language with help from a fluent speaker.</li>
<li>Answer the six questions in the lesson for your plan, and change the plan if any answer is weak.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Language data comes from people, so consent, privacy and fairness matter.</li>
<li>Collect only what you need, keep it safe and delete it when it is no longer needed.</li>
<li>Pay and credit contributors and include different voices.</li>
<li>Follow your country's data protection law and go beyond it with good manners.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does "under-resourced language" mean in NLP?
- A language with very few speakers
- A language that is no longer used
* A language with little digital data and few tools, whatever the number of speakers
- A language that cannot be written
= Swahili or Luganda can have millions of speakers and still be under-resourced online.

Q: Which is a likely reason a translation tool performs poorly for a smaller African language?
- African languages are harder than all others
* There are few translated sentence pairs to learn from
- The internet blocks those languages
- Computers cannot process accents
= Machine translation learns from sentence pairs, and those are scarce for many African language pairs.

Q: Parents reply to a school bot in a mix of Luganda and English and the bot keeps saying it does not understand. What is the best improvement?
- Tell parents to write only in formal English
* Add a bilingual numbered menu and a way to reach a staff member
- Remove the bot completely
- Make the bot reply faster
= A menu avoids depending on perfect free typing, and hand-off keeps the service useful.

Q: Which are examples of community-driven language data work? (choose all that apply)
* Volunteers recording and validating sentences on an open voice project
* Students and teachers building a shared word list with permission
- Copying private chats without telling anyone
* Native speakers correcting machine translations
= Community work involves speakers, permission and checking. Secretly copying private chats is not acceptable.

Q: A researcher wants voice recordings from a village clinic. Which plan is the most ethical?
- Record the consultations and tell patients later
* Invite volunteers to read prepared sentences, explain the use, obtain consent and pay for their time
- Ask the nurse to give permission on behalf of the patients
- Record at night when nobody notices
= Consent, a clear purpose and fair return are essential, and private consultations should not be recorded without agreement.

Q: A speech bot works well for men but badly for women. What is the most likely cause and fix?
- Women speak a different language; ignore the problem
* Training data had too few women's voices; collect a more balanced set and test again
- The microphone is too loud; lower the volume
- Nothing can be done
= Unbalanced data creates unfair tools. Representative data and testing with all groups reduce the problem.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Designing a chatbot',
            'summary' => 'Plan goals, users, channels, intents, conversation flows, tone, languages and hand-off, and choose the right kind of bot.',
            'lessons' => [
                [
                    'title'   => 'Goals, users and channels',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a clear goal for a chatbot, describe its users and pick the best channel for them. This matters because most failed chatbots fail before any code is written: they try to do too much, or they sit on a channel their users do not use.</p>

<h3>Start with a goal, not a tool</h3>
<p>A good goal is specific and measurable. "Have a chatbot" is not a goal. Better: "Answer the ten most common parent questions on WhatsApp within one minute, so the school office receives 30 percent fewer calls." Write the goal in one sentence and list what the bot will <em>not</em> do. A bot that answers fees, term dates and uniforms well is more useful than one that tries to do everything badly.</p>

<h3>Know your users</h3>
<p>Write a short profile of the main users. Ask:</p>
<ul>
<li>Who are they (parents, patients, customers, students)?</li>
<li>What language or languages do they use, and in what mix?</li>
<li>What phones do they have: smartphones, or basic phones?</li>
<li>Do they have data bundles and Wi-Fi, or only airtime?</li>
<li>How comfortable are they with reading and typing? Do they prefer voice?</li>
<li>When do they need help: night, weekends, market days?</li>
</ul>
<p>The best way to learn is to speak to ten of them. Ask what questions they ask now, how they ask, and where they ask.</p>

<h3>Choosing a channel</h3>
<table>
<thead><tr><th>Channel</th><th>Good for</th><th>Limits</th></tr></thead>
<tbody>
<tr><td>WhatsApp</td><td>Widely used, supports text, images, voice notes, buttons and lists</td><td>Needs a smartphone and data; business messaging has rules and may cost money</td></tr>
<tr><td>Website chat</td><td>People already on your site; easy to link to forms and pages</td><td>Only reaches people who visit and have data</td></tr>
<tr><td>SMS</td><td>Works on any phone, no data needed</td><td>Short messages (a single message is about 160 characters), no pictures, may cost the user</td></tr>
<tr><td>USSD (menu codes such as *123#)</td><td>Works on any phone, no data, familiar from mobile money</td><td>Numbered menus only, short sessions, usually set up with a telecom or aggregator</td></tr>
<tr><td>Messenger and other social apps</td><td>Reach people on social media</td><td>Depends on the platform's rules and changes</td></tr>
<tr><td>Voice call (IVR)</td><td>Reaches people who prefer speaking or cannot read</td><td>Needs good speech tools in the language; more complex</td></tr>
</tbody>
</table>
<p>Do not guess about platform rules, prices or features, because they change. Read the current help pages of the channel you plan to use and ask your users what they actually use.</p>

<h3>Scenario: two shops, two channels</h3>
<p>A hardware shop in Kampala has many customers who already message on WhatsApp, so a WhatsApp bot that shares prices and opening hours fits well. A village savings group, however, has members with basic phones and little data. For them a USSD menu or a simple SMS service would reach more people. The topic is similar, but the right channel is different because the users are different.</p>

<blockquote><p><strong>Quick check:</strong> Which is a better chatbot goal: "Make an AI bot" or "Answer the ten most common fee questions within one minute on WhatsApp"?</p><p>Answer: The second, because it is specific, names the channel and can be measured.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Your users have basic phones and no data bundles. Which channels suit them?</p><p>Answer: SMS or USSD, because they work on any phone without data, unlike WhatsApp or a website.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick a school, clinic or small business you know.</li>
<li>Write a one-sentence goal with a number and a time in it.</li>
<li>List what the bot will not do (for example, give medical advice or take payments).</li>
<li>Write a short profile of your main users using the six questions.</li>
<li>Choose one main channel and give two reasons for your choice.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Start with a specific, measurable goal and clear limits.</li>
<li>Learn about real users: languages, phones, data and habits.</li>
<li>Pick the channel your users already use; SMS and USSD reach basic phones.</li>
<li>Check the current rules and costs of any channel before you commit.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Intents, entities and conversation flows',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to list the intents for a chatbot, give example messages for each, and draw a simple conversation flow with questions and answers. This matters because a clear plan on paper saves days of confusion when you start building.</p>

<h3>Listing intents</h3>
<p>Remember that an <strong>intent</strong> is what the user wants. Begin by collecting real questions (from your word counts, phone calls and WhatsApp messages) and group them. For a small school you may find eight to twelve intents.</p>
<table>
<thead><tr><th>Intent</th><th>Example messages</th></tr></thead>
<tbody>
<tr><td>ask_fees</td><td>How much is school fees? / Fees for Term 2? / Ssente z'essomero zibalibwa zitya?</td></tr>
<tr><td>term_dates</td><td>When does school open? / Next term start date</td></tr>
<tr><td>uniform_info</td><td>Where can I buy the uniform? / Uniform price</td></tr>
<tr><td>contact_office</td><td>I want to talk to someone / Office phone number</td></tr>
<tr><td>greeting</td><td>Hello / Good morning / Oli otya</td></tr>
</tbody>
</table>
<p>Write at least five example messages per intent, in each language, including spelling mistakes and short forms. More variety helps the bot (and the people testing it) cope with real life.</p>

<h3>Entities and slots</h3>
<p>Some intents need details. To tell a parent the fees you need the class: that is an <strong>entity</strong> (also called a slot) to fill. The bot asks for any missing slot: "Which class is your child in?" It should accept answers in several forms, such as "P5", "Primary 5" or "class five".</p>

<h3>Drawing a conversation flow</h3>
<p>A <strong>conversation flow</strong> is a map of how the chat moves. Draw it with boxes and arrows on paper before building. Or write it as a short script:</p>
<pre><code>Bot:  Hello! I can help with fees, term dates and uniforms.
      Reply 1 for Fees, 2 for Term dates, 3 for Uniforms, 4 to talk to the office.
User: 1
Bot:  Which class? (for example P5 or S2)
User: P5
Bot:  Primary 5 fees are 450,000 UGX per term. Do you want the payment options?
User: yes
Bot:  You can pay by mobile money or at the bank. [details]. Anything else? Reply MENU.</code></pre>

<h3>Good flow habits</h3>
<ul>
<li>Offer choices (buttons or numbers) so users do not need to guess commands.</li>
<li>Ask only one question at a time.</li>
<li>Confirm important details: "You asked for P5 fees. Is that right?"</li>
<li>Give a way back: "Reply MENU to start again."</li>
<li>Keep answers short and break long ones into steps.</li>
<li>Plan for what happens when the user changes their mind in the middle.</li>
</ul>

<h3>Scenario: appointment flow for a clinic</h3>
<p>A clinic bot books appointments. It needs three slots: service, day and time. If the user says "I want to see a doctor tomorrow", the bot fills the day and asks for the service and time. If the user says "tomorrow morning" the bot offers two time buttons. At the end it repeats the booking and asks for confirmation. This avoids wrong bookings caused by misunderstandings.</p>

<blockquote><p><strong>Quick check:</strong> In an appointment bot, "doctor", "Friday" and "10am" are what part of the design?</p><p>Answer: Entities (slots), the details the bot must fill to complete the booking.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why should a bot repeat a booking before finishing?</p><p>Answer: Confirming catches misunderstandings, such as a wrong day or time, before they become real problems.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write eight intents for your chosen school, clinic or business.</li>
<li>For each, write five example messages in at least one local language and English, including a spelling slip or short form.</li>
<li>Pick the intent that needs a slot and write the follow-up question and acceptable answers.</li>
<li>Write one full conversation flow as a short script, like the example, ending with a MENU option.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Intents come from real user questions, grouped by purpose.</li>
<li>Entities (slots) are details the bot may need to ask for.</li>
<li>Draw the conversation flow before building anything.</li>
<li>Use choices, one question at a time, confirmation and a way back to the menu.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Fallback, human hand-off, tone and language',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to design what the bot does when it does not understand, when it must pass a person to a human, and how it should sound in more than one language. This matters because every bot meets questions it cannot answer, and how it fails decides whether people trust it.</p>

<h3>Fallback: when the bot is lost</h3>
<p>A <strong>fallback</strong> is the bot's response when it cannot match the message to an intent. A poor fallback repeats "Sorry, I did not understand" again and again. A good one helps the user move forward:</p>
<ol>
<li>Admit the problem kindly: "I am not sure I understood that."</li>
<li>Offer options: show the main menu or buttons.</li>
<li>Invite a rewrite: "You can also try asking in a shorter way."</li>
<li>After two misses, offer a person: "Would you like to talk to our office?"</li>
</ol>
<p>Keep a log of fallback messages. They show you the questions your bot is missing, and you can add them as new intents or answers.</p>

<h3>Human hand-off</h3>
<p>Some conversations should never stay with a bot. Plan a <strong>hand-off</strong> to a real person when:</p>
<ul>
<li>the user asks for a person;</li>
<li>the user is upset, in danger or ill (words such as "emergency", "bleeding", "stolen");</li>
<li>the question involves money disputes, complaints or legal matters;</li>
<li>the bot fails twice in a row;</li>
<li>the topic is sensitive, such as health advice or abuse.</li>
</ul>
<p>Tell the user what happens next, with honest timing: "Our office will reply on weekdays between 8 a.m. and 5 p.m." Pass the conversation history to the person so the user does not have to repeat everything. For emergencies, show the right emergency number straight away, not a promise of a reply later.</p>

<h3>Tone and personality</h3>
<table>
<thead><tr><th>Choose</th><th>Instead of</th></tr></thead>
<tbody>
<tr><td>Polite, warm and short sentences</td><td>Long, formal, legal-sounding paragraphs</td></tr>
<tr><td>Telling users they are talking to a bot</td><td>Pretending to be a human</td></tr>
<tr><td>Respectful greetings suited to the culture (elders, titles)</td><td>Casual slang with older users</td></tr>
<tr><td>Plain words and local examples</td><td>Jargon and long acronyms</td></tr>
</tbody>
</table>

<h3>Working in more than one language</h3>
<ul>
<li>Ask the user at the start: "Reply 1 for English, 2 for Luganda." Remember the choice.</li>
<li>Write every answer in each language yourself, or have it written by fluent speakers; do not rely on raw machine translation for important answers.</li>
<li>Allow for mixed-language messages and local spellings.</li>
<li>Keep the same meaning and the same facts across languages; check that prices and dates match.</li>
</ul>

<h3>Scenario: the late-night message</h3>
<p>A parent writes to a school bot at 9 p.m.: "My son has fainted at home, what should I do?" The bot should not try to give medical advice. A well-designed bot recognises urgent words, tells the parent to contact emergency services or a health worker right away, shows the number, and also alerts the school office. The designers planned this in advance, rather than hoping it would not happen.</p>

<blockquote><p><strong>Quick check:</strong> A user writes something the bot cannot match for the second time in a row. What should it do?</p><p>Answer: Offer help from a person (and the menu), because repeating "I did not understand" frustrates users.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why should a bot tell users it is a bot?</p><p>Answer: Honesty builds trust and helps users know what to expect, including that a person is available for harder matters.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write your bot's fallback message in two languages with a menu offer.</li>
<li>Write a rule for when to hand off to a person, with at least four triggers.</li>
<li>Write the message the bot sends at hand-off, including honest response times.</li>
<li>Write the greeting in a warm tone, saying that it is a bot.</li>
<li>Ask a fluent speaker to read all messages and mark any that sound strange or rude.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A good fallback helps the user move on and escalates after repeated misses.</li>
<li>Hand off to a person for urgent, sensitive, angry or repeated-failure cases.</li>
<li>Be honest that it is a bot, and keep a warm, plain, respectful tone.</li>
<li>Write answers carefully in each language and keep facts consistent.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Rule-based, retrieval and generative bots',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe three kinds of chatbot, compare their strengths and risks, and choose one for a given job. This matters because the choice affects cost, accuracy, and how well the bot serves languages with little data.</p>

<h3>Three families of bots</h3>
<p><strong>Rule-based bots</strong> follow fixed paths that you design: menus, buttons and keyword rules. If the user presses 1, show fees. They do exactly what you wrote and nothing else.</p>
<p><strong>Retrieval bots</strong> look for the best match among answers you have written. They compare the user's message with your questions and return the closest prepared answer. They may use NLP to match meaning, not just exact words, but the reply is always text you approved.</p>
<p><strong>Generative bots</strong> use a large language model to write a new answer each time. They are flexible and fluent, can handle surprising questions and may work across many languages. But they can invent facts (hallucinate) and their quality in under-resourced languages can be weaker.</p>

<h3>Comparison</h3>
<table>
<thead><tr><th>Feature</th><th>Rule-based</th><th>Retrieval</th><th>Generative</th></tr></thead>
<tbody>
<tr><td>Accuracy of facts</td><td>High (you wrote it)</td><td>High (approved answers)</td><td>Can be wrong unless grounded</td></tr>
<tr><td>Handles unexpected wording</td><td>Poorly</td><td>Fairly well</td><td>Very well</td></tr>
<tr><td>Control over replies</td><td>Full</td><td>Full</td><td>Limited</td></tr>
<tr><td>Set-up effort</td><td>Design every path</td><td>Write good Q&amp;A pairs</td><td>Prompts, source documents and testing</td></tr>
<tr><td>Running cost</td><td>Low</td><td>Low to medium</td><td>Often higher; may be per use</td></tr>
<tr><td>Local languages</td><td>As good as your writing</td><td>As good as your writing and matching</td><td>Depends on the model; test carefully</td></tr>
<tr><td>Risk</td><td>Frustrates users who stray off the path</td><td>Wrong match if questions overlap</td><td>Invented or unsafe answers</td></tr>
</tbody>
</table>

<h3>A hybrid approach</h3>
<p>Many good bots combine them: buttons for the main paths (rule-based), retrieval for FAQs, and, where allowed and tested, a generative layer that is told to answer <em>only</em> from your approved documents and to say "I don't know" otherwise. A common idea is <strong>retrieval-augmented generation</strong>: first retrieve the relevant passages from your knowledge base, then ask the model to write the reply using only those passages. This lowers, but does not remove, the risk of errors.</p>

<h3>How to choose</h3>
<ol>
<li>If mistakes are costly (health, money, legal), prefer rule-based or retrieval with approved answers.</li>
<li>If you have a small, stable list of questions, a menu with retrieval is enough.</li>
<li>If questions are varied and you can test well, consider adding a generative layer with limits and human review.</li>
<li>If your language is under-resourced, test generative output with native speakers before trusting it.</li>
<li>Always have fallback and hand-off, whatever the type.</li>
</ol>

<h3>Scenario: a pharmacy bot</h3>
<p>A pharmacy wants a bot to answer opening hours, delivery areas and stock questions. Opening hours and delivery are fixed facts, so a menu works. For "Do you have medicine X?" the bot reads from a stock sheet. For questions about doses or side effects, it does not answer but offers to connect the customer to the pharmacist. A generative model that improvises medical advice would be too risky here.</p>

<blockquote><p><strong>Quick check:</strong> A bot returns the closest prepared answer to a user's question. Which family is it?</p><p>Answer: Retrieval, because it chooses from answers you wrote instead of inventing new ones.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why might a clinic avoid a free-writing generative bot for dosage questions?</p><p>Answer: It can produce confident but wrong answers, and a wrong dose could harm a patient.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose your capstone organisation (school, clinic or business).</li>
<li>List ten questions it receives and mark each as "fixed fact", "needs lookup" or "needs human judgement".</li>
<li>Decide which family (rule-based, retrieval, generative or a mix) fits each group.</li>
<li>Write two sentences defending your choice, including the risk you are avoiding.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Rule-based bots follow your paths; retrieval bots return approved answers; generative bots write new text.</li>
<li>Generative bots are flexible but can invent facts and may be weaker in local languages.</li>
<li>Hybrid bots use menus, approved answers and limited generation.</li>
<li>High-risk topics should stay with approved answers or people.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which is the best chatbot goal for a school?
- Build an AI bot that does everything
* Answer the ten most common parent questions on WhatsApp in under one minute and reduce office calls by 30 percent
- Make the bot sound clever
- Have the most buttons possible
= A good goal is specific, measurable and limited, so you can design and test for it.

Q: A village group has members with basic phones and no data. Which channel fits best?
- A website chat widget
- A WhatsApp bot with images
* USSD or SMS
- A video call bot
= SMS and USSD work on any phone without data.

Q: "Which class?" is asked by the bot to complete a fee answer. What is "class" in chatbot design?
- A fallback
* An entity (slot) that the bot needs to fill
- A channel
- A greeting
= Entities or slots are details the bot needs to give the right answer.

Q: A bot fails to understand the same user twice. What is the best next response?
- Repeat "Sorry, I did not understand" a third time
- End the chat without a message
* Offer a menu and a way to reach a person
- Reply with a random answer
= After repeated misses, a path forward and a human option keeps the user helped.

Q: Which messages should usually trigger a hand-off to a person? (choose all that apply)
* "My child has fainted"
* "I was charged twice, give me my money back"
- "What time do you open?"
* "I want to speak to someone"
= Urgent, money-dispute and personal requests need a human. A simple opening hours question is a normal bot job.

Q: A clinic bot must answer dosage questions safely. Which design is wisest?
- A free-writing generative bot with no limits
- A bot that guesses from the symptoms typed
* Approved answers for fixed facts and a hand-off to a pharmacist or nurse for dosage questions
- A bot that answers only in English
= High-risk topics need approved content and human review, not improvisation.

Q: What does retrieval-augmented generation mean in simple terms?
- The bot memorises the whole internet
* The bot first finds relevant passages from your own documents, then writes a reply using them
- The bot translates every question twice
- The bot sends all questions to a human
= Retrieving your own source text first keeps the generated answer closer to approved facts.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Building a chatbot',
            'summary' => 'Write a knowledge base and FAQ, build a bot with a free no-code tool, and try a short Python example.',
            'lessons' => [
                [
                    'title'   => 'Writing a knowledge base and FAQ',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a clear knowledge base for a chatbot, with short accurate answers in plain language and in more than one language. This matters because a bot is only as good as the content behind it, and most chatbot problems are content problems.</p>

<h3>What a knowledge base is</h3>
<p>A <strong>knowledge base</strong> is the organised collection of facts and answers that your bot uses. For a small bot this can be a spreadsheet with one row per question. For a bigger one it can be a set of documents. Either way, one person should own it and keep it up to date.</p>

<h3>A simple spreadsheet layout</h3>
<table>
<thead><tr><th>ID</th><th>Intent</th><th>Question examples</th><th>Answer (English)</th><th>Answer (Luganda)</th><th>Owner</th><th>Last checked</th></tr></thead>
<tbody>
<tr><td>F01</td><td>term_dates</td><td>When does school open? / Term start</td><td>Term 2 starts on 5 May. Please check the notice board for changes.</td><td>(checked translation)</td><td>Office</td><td>1 May</td></tr>
<tr><td>F02</td><td>ask_fees</td><td>School fees / Cost of Term 2</td><td>Fees depend on class. Reply with your class, for example P5.</td><td>(checked translation)</td><td>Bursar</td><td>1 May</td></tr>
</tbody>
</table>
<p>Keep the "owner" and "last checked" columns. They stop old prices and dates from living in the bot for months.</p>

<h3>Writing good answers</h3>
<ol>
<li><strong>Answer first.</strong> Put the main fact in the first sentence: "The clinic opens at 8 a.m., Monday to Saturday."</li>
<li><strong>Keep it short.</strong> Aim for two to four short sentences on a phone screen. Use a link or a follow-up button for more.</li>
<li><strong>Use plain words</strong> and short sentences that a tired parent can read quickly.</li>
<li><strong>Give the next step:</strong> where to go, whom to call, what to bring.</li>
<li><strong>Include exact numbers:</strong> amounts in UGX, dates and times, and the date when they might change.</li>
<li><strong>Do not give advice you are not qualified to give.</strong> Refer to the right person.</li>
</ol>

<h3>Before and after</h3>
<pre><code>Weak:
"Kindly be informed that the institution's remittance requirements are
as stipulated in the circular."

Better:
"Term 2 fees for P5 are 450,000 UGX. You can pay by mobile money or at
the bank. Reply PAY for the steps."</code></pre>

<h3>Cover the same ground in each language</h3>
<p>Write the English first, then have a fluent speaker write the second language (rather than translating word by word). Check that numbers, names and dates match. Read it aloud: if it sounds odd to a native speaker, rewrite it. Include common spellings and alternative words in the question examples so the bot can match them.</p>

<h3>Scenario: the outdated price</h3>
<p>A hair salon's bot still says a braiding service costs 40,000 UGX after the price rose to 50,000 UGX. Customers arrive expecting the old price, and staff must argue with them. A simple monthly review of the knowledge base, with a named owner, would have prevented this. Content maintenance is part of running a chatbot, not an extra.</p>

<blockquote><p><strong>Quick check:</strong> Why include an "owner" and "last checked" date for each answer?</p><p>Answer: They make sure someone is responsible for keeping each answer current, so old prices and dates are not served for months.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which is a better opening for an answer: "Fees depend on a number of factors..." or "P5 fees are 450,000 UGX per term."?</p><p>Answer: The second, because it gives the main fact first in plain words.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open a spreadsheet and create the columns shown in the table.</li>
<li>Write ten rows for your capstone organisation, with three to five question examples each.</li>
<li>Write the English answers, each in two to four short sentences with the main fact first.</li>
<li>Ask a fluent speaker to write the second-language answers and check the numbers.</li>
<li>Fill in owners and dates, and set a reminder to review monthly.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>The knowledge base is the heart of the bot; keep it organised and owned.</li>
<li>Write answers short, plain, exact and action-focused, main fact first.</li>
<li>Have bilingual answers written and checked by fluent speakers.</li>
<li>Review regularly so facts never go stale.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Building a no-code chatbot',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build and test a simple FAQ chatbot using a free no-code tool, without writing programs. This matters because you can have a working prototype in an afternoon, and users can react to something real instead of an idea on paper.</p>

<h3>What no-code tools offer</h3>
<p>A <strong>no-code chatbot builder</strong> lets you create a bot by filling in forms and connecting blocks, not by programming. Typical features are: a flow editor (boxes for messages, questions and buttons), a place to store intents and example phrases, a test window, and options to connect to channels such as a website chat or messaging apps. Features, free limits and prices differ and change often, so always read the tool's current pages. Several well-known platforms have a free plan, and some open-source platforms can be installed on your own server.</p>

<h3>If you cannot find a suitable free tool</h3>
<p>You can build a working prototype with tools you already have:</p>
<ul>
<li><strong>A form-and-spreadsheet bot:</strong> a spreadsheet with questions and answers plus a simple look-up formula.</li>
<li><strong>A WhatsApp Business app catalogue and quick replies:</strong> the free WhatsApp Business app has features such as a greeting message, away message and saved quick replies. You can set these up with no programming, though they are not a full chatbot. Check which features are currently available.</li>
<li><strong>A slide-based prototype:</strong> slides with buttons linking to the next slide, to test the flow with users.</li>
</ul>

<h3>Build steps in a typical no-code tool</h3>
<ol>
<li><strong>Create a new bot</strong> and name it, choosing the main language.</li>
<li><strong>Add a welcome block</strong> with a greeting, the fact that it is a bot, and a language choice.</li>
<li><strong>Add the main menu</strong> with buttons or numbered options for your top intents.</li>
<li><strong>Add an answer block</strong> for each intent, copying text from your knowledge base.</li>
<li><strong>Add question blocks</strong> where slots are needed (for example, class), with buttons for valid answers.</li>
<li><strong>Add keywords or example phrases</strong> if the tool supports free typing, using your question examples.</li>
<li><strong>Set the fallback:</strong> a friendly message with the menu and a hand-off option after two misses.</li>
<li><strong>Add a hand-off block:</strong> a message with the phone number, email or WhatsApp number of a staff member and office hours.</li>
<li><strong>Test inside the builder</strong> by walking through each path, then share a test link or number with a few users.</li>
</ol>

<h3>Naming and tidying</h3>
<table>
<thead><tr><th>Do</th><th>Avoid</th></tr></thead>
<tbody>
<tr><td>Name blocks clearly: ask_fees_class, fees_answer_P5</td><td>Block 1, Block 2, New block</td></tr>
<tr><td>Keep one idea per message</td><td>Walls of text</td></tr>
<tr><td>Add a MENU option on every answer</td><td>Dead ends</td></tr>
<tr><td>Keep a copy of your content in a spreadsheet</td><td>Content that lives only inside the tool</td></tr>
</tbody>
</table>

<h3>Scenario: the weekend prototype</h3>
<p>A student group builds a clinic prototype in one Saturday. In the morning they fill the spreadsheet; at midday they build the menu and five answers; in the afternoon three family members test it. They discover that "Reply 1" confuses people who send "one" in words, and that the bot has no answer for "Do you treat children?" They add both, and the second test goes much better. Quick cycles beat perfect plans.</p>

<blockquote><p><strong>Quick check:</strong> Why keep your answers in a spreadsheet as well as in the chatbot tool?</p><p>Answer: You keep your own copy that is easy to review and update, and you can move to another tool if the first changes its plans or prices.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A user types "one" instead of "1". What should the designer do?</p><p>Answer: Accept both forms (add "one" as an accepted answer), because real users do not follow the exact format expected.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a free no-code tool (or the simple alternatives above) and create a new bot.</li>
<li>Build a welcome message with a language choice and a main menu of four to six options.</li>
<li>Create an answer for each option, using your knowledge base text.</li>
<li>Add the fallback and the hand-off block.</li>
<li>Test every path yourself, then ask two friends to try it and note where they hesitate.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>No-code tools let you build flows with forms and blocks; check current features and limits.</li>
<li>If needed, prototype with quick replies, spreadsheets or slides.</li>
<li>Always build the welcome, menu, answers, fallback and hand-off.</li>
<li>Test early with real users and fix what confuses them.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'A beginner Python NLP example',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to run short Python programs that clean text, count words, score simple sentiment and answer questions by keyword. This matters because even simple code shows you how NLP ideas work, and it prepares you for bigger tools later. You do not need to be an expert: if you have not done Python before, read the code slowly and run it exactly as shown.</p>

<h3>Getting ready</h3>
<p>You need Python 3 installed, or a free online Python environment in your browser. Create a file called <code>nlp1.py</code>, paste the code, and run it with <code>python nlp1.py</code>. Use plain quotation marks when you type.</p>

<h3>Program 1: clean and count words</h3>
<pre><code>import string

text = "Mobile money is good. Mobile money is fast!"

clean = text.lower()
for mark in string.punctuation:
    clean = clean.replace(mark, "")

words = clean.split()

counts = {}
for word in words:
    counts[word] = counts.get(word, 0) + 1

print(counts)</code></pre>
<p>This program makes the text lower-case, removes punctuation, splits it into words (tokens) and counts them in a dictionary. The output is:</p>
<pre><code>{'mobile': 2, 'money': 2, 'is': 2, 'good': 1, 'fast': 1}</code></pre>
<p>Python also has a helper called <code>Counter</code> that does the counting for you:</p>
<pre><code>from collections import Counter
print(Counter(words).most_common(3))</code></pre>

<h3>Program 2: a simple sentiment score</h3>
<pre><code>positive = {"good", "great", "fast", "happy", "thanks"}
negative = {"bad", "slow", "late", "angry", "broken"}

def sentiment(message):
    score = 0
    for word in message.lower().split():
        if word in positive:
            score += 1
        elif word in negative:
            score -= 1
    if score > 0:
        return "positive"
    if score &lt; 0:
        return "negative"
    return "neutral"

print(sentiment("The delivery was fast thanks"))
print(sentiment("My order is late and the box is broken"))</code></pre>
<p>It prints <code>positive</code> then <code>negative</code>. Remember the limits from Module 1: this method misses sarcasm, "not good", slang and other languages. You can add Luganda or Swahili words to the lists, but only with the help of fluent speakers.</p>

<h3>Program 3: a tiny keyword bot</h3>
<pre><code>answers = {
    "fees": "P5 fees are 450,000 UGX per term.",
    "uniform": "Uniforms are sold at the school shop.",
    "open": "The school opens on 5 May.",
}

def reply(message):
    for word in message.lower().split():
        if word in answers:
            return answers[word]
    return "Sorry, I am not sure. Reply with fees, uniform or open."

print(reply("How much are the fees"))
print(reply("Where is the toilet"))</code></pre>
<p>The first call finds the keyword <code>fees</code> and returns the fee answer. The second finds no keyword and gives the fallback message. This is a rule-based bot in about ten lines.</p>

<h3>Why this matters and what is next</h3>
<p>These examples are simple on purpose. Real systems use machine learning, but the same steps appear: clean, split, count, match. Libraries such as NLTK offer ready-made tools, and the Python documentation is a good place to learn more. Always check the output yourself, and add test messages that include spelling mistakes.</p>

<blockquote><p><strong>Quick check:</strong> In Program 3, what does the bot reply to "Where is the toilet"?</p><p>Answer: The fallback message, because none of the words in the message is a key in the answers dictionary.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why would the Program 2 score for "not good" be wrong?</p><p>Answer: It only counts "good" as positive and ignores "not", so a negative phrase is scored as positive.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Type Program 1 and run it. Change the text to three sentences from a real customer message set and check the counts.</li>
<li>Add five more positive and five more negative words to Program 2, including at least two from a local language with help from a speaker.</li>
<li>Add two more keywords and answers to Program 3 for your capstone organisation.</li>
<li>Test with five messages, including one with capital letters and one with a typing slip, and write what happened.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Cleaning, splitting and counting words takes only a few lines of Python.</li>
<li>A word-list sentiment score is simple and useful but limited.</li>
<li>A keyword bot shows the rule-based idea and the need for a fallback.</li>
<li>Test with messy, real messages, not only perfect ones.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Why does a chatbot knowledge base need an owner and a "last checked" date?
- To make the table look more professional
* So someone is responsible for keeping answers current
- Because tools require it
- To count how many users there are
= Facts like prices and dates change, and without an owner they go stale.

Q: Which answer is best for a mobile phone chatbot?
- "Kindly be informed that fee schedules are as stipulated in the circular."
- A five-paragraph essay on fee policy
* "P5 fees are 450,000 UGX per term. Pay by mobile money or at the bank. Reply PAY for steps."
- "Fees depend."
= It leads with the fact, is short and tells the user the next step.

Q: A Luganda answer was produced by pasting the English into a translation tool and publishing it. What is the best improvement?
- Nothing, machine translation is always perfect
* Ask a fluent speaker to write or check it and confirm that numbers and dates match
- Delete the Luganda option
- Translate it back into English and publish both
= Important answers need a human check for meaning, tone and exact figures.

Q: Users type "one" while your bot expects "1". What is the best response?
- Tell users they are typing wrongly
* Accept both forms of the answer
- Remove the menu
- Switch to a different channel
= Users do not follow exact formats, so designs should accept natural variations.

Q: Which blocks should every chatbot flow include? (choose all that apply)
* A fallback message
* A way to reach a person
- A hidden block with the owner's password
* A menu or way back to the start
= These keep users from being stuck. Passwords must never be put in a bot.

Q: In the Python keyword bot, what happens if the message has no keyword in the dictionary?
- The program crashes
* The function returns the fallback message
- It returns the first answer
- It returns an empty line
= The loop ends without a match and then the final return statement runs.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Testing, responsibility and the capstone',
            'summary' => 'Test with real users, measure success, protect privacy, make the bot accessible, and complete a bilingual FAQ chatbot capstone.',
            'lessons' => [
                [
                    'title'   => 'Testing with real users and measuring success',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to test a chatbot with real people and measure whether it is helping, using simple numbers such as resolution rate and satisfaction. This matters because your own opinion of the bot is the least reliable one: you know all the right words to type, and real users do not.</p>

<h3>Test in stages</h3>
<ol>
<li><strong>Self-test:</strong> walk through every path, button and error case, and fix broken links and typos.</li>
<li><strong>Friendly test:</strong> ask five to ten people who are like your real users (not your classmates who know the project) to try specific tasks.</li>
<li><strong>Observed test:</strong> sit beside a user, ask them to "think aloud", and watch where they hesitate. Do not help unless they ask.</li>
<li><strong>Pilot:</strong> launch to a small group for one or two weeks and review the logs.</li>
<li><strong>Launch and keep improving:</strong> review weekly at first, then monthly.</li>
</ol>

<h3>Give test tasks, not instructions</h3>
<p>Instead of "Press 1 for fees", say "Find out how much your child's class pays for Term 2." Then see what the person does. Include tasks in every language, a task the bot cannot do, and a task that needs a person. Write down exact words users type, since these are gold for your example phrases.</p>

<h3>Key numbers</h3>
<table>
<thead><tr><th>Measure</th><th>How to calculate</th><th>What it tells you</th></tr></thead>
<tbody>
<tr><td>Resolution rate</td><td>Conversations where the user got an answer without needing a person, divided by all conversations</td><td>Whether the bot actually solves problems</td></tr>
<tr><td>Fallback rate</td><td>Fallback replies divided by user messages</td><td>How often the bot is lost</td></tr>
<tr><td>Hand-off rate</td><td>Conversations passed to a person divided by all conversations</td><td>Which topics the bot cannot handle</td></tr>
<tr><td>Satisfaction</td><td>Average score from a quick question such as "Did this help? Yes or No"</td><td>How users feel</td></tr>
<tr><td>Response time</td><td>Time to first useful answer</td><td>Speed and reliability</td></tr>
<tr><td>Drop-off</td><td>Where users leave the conversation</td><td>Confusing steps</td></tr>
</tbody>
</table>
<p>A worked example: in one week a school bot has 200 conversations. In 140 the parent got an answer and did not call the office; in 40 the bot handed over to the office; in 20 the parent left without an answer. Resolution rate is 140 divided by 200, which is 70 percent. The hand-off rate is 20 percent. The 20 who left are the group to investigate.</p>

<h3>Read the logs, learn and fix</h3>
<ul>
<li>List the most common unmatched messages and decide: add an intent, add examples or add an answer.</li>
<li>Look at conversations that ended in hand-off and ask whether the bot could have helped.</li>
<li>Read comments from users, especially complaints.</li>
<li>Change one or two things at a time and measure again, so you know what helped.</li>
</ul>

<h3>Beware of misleading numbers</h3>
<p>A high resolution rate does not prove users were happy; some may have given up. Pair it with satisfaction and a few real conversations. Also protect privacy: when you review logs, remove names and phone numbers, and share only what colleagues need.</p>

<blockquote><p><strong>Quick check:</strong> A bot handled 300 conversations, and 180 ended with the user getting an answer without a person. What is the resolution rate?</p><p>Answer: 60 percent, because 180 divided by 300 is 0.6.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why test with people who are not on your project team?</p><p>Answer: Team members know the expected wording, so they hide problems that real users will meet.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write five test tasks for your chatbot, including one in a local language and one the bot cannot do.</li>
<li>Ask three people outside your team to try them while you watch silently and take notes.</li>
<li>Record how many completed each task and what words they typed.</li>
<li>Calculate a simple resolution rate for your small test.</li>
<li>List three fixes and apply them, then retest with a different person.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Test in stages, with people who resemble your real users.</li>
<li>Give tasks, watch quietly and write down real user phrases.</li>
<li>Track resolution rate, fallback rate, hand-off rate and satisfaction.</li>
<li>Read the logs, fix the biggest gaps first, and keep privacy in mind.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Privacy, consent and accessibility',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to make a chatbot respect users' privacy, ask for consent properly and be usable by people with different abilities and skills. This matters because a bot talks to people about fees, health and money, and trust is lost quickly if their information is leaked or if some people cannot use the service.</p>

<h3>Privacy by design</h3>
<p>Privacy means people control information about themselves. Build it in from the start:</p>
<ul>
<li><strong>Collect the minimum.</strong> If you only need a class name to give fees, do not ask for the child's full name and birth date.</li>
<li><strong>Explain why you ask.</strong> "We ask for your phone number so that the clinic can send a reminder."</li>
<li><strong>Avoid sensitive details in chat.</strong> Tell users not to send passwords, mobile money PINs, ID numbers or full medical histories. A real organisation never asks for a PIN in a chat.</li>
<li><strong>Limit who can see chat records.</strong> Use strong passwords on your tool and give access only to people who need it.</li>
<li><strong>Delete old data.</strong> Decide how long logs are kept, and remove them after that.</li>
<li><strong>Be careful with third-party tools.</strong> Know where the tool stores data, and read its privacy terms before connecting real user information.</li>
</ul>

<h3>Consent in a chat</h3>
<p>Consent means the person freely agrees after understanding what will happen. In a chatbot you can ask at the start:</p>
<pre><code>Bot: Hello, I am the Hillside Clinic assistant (a bot, not a person).
     I keep this chat for 30 days to improve our service.
     Do not send passwords or PINs.
     Reply 1 to continue, or 2 to talk to the clinic staff instead.</code></pre>
<p>Keep the text short and plain, offer it in the user's language, and give a real alternative if they say no. Do not send promotional messages to people who did not agree to receive them, and respect the rules of the channel. Look up the rules of your country, such as Uganda's Data Protection and Privacy Act, and follow them for personal data.</p>

<h3>Accessibility</h3>
<table>
<thead><tr><th>Need</th><th>What to do</th></tr></thead>
<tbody>
<tr><td>Low reading skill or a second language</td><td>Short sentences, common words, simple menus, icons or emoji only as extras</td></tr>
<tr><td>Prefers to speak</td><td>Accept voice notes if your tool supports it, or offer a phone number to call</td></tr>
<tr><td>Low vision or uses a screen reader</td><td>Do not put essential facts only in pictures; write text; avoid long strings of emoji</td></tr>
<tr><td>Hearing difficulty</td><td>Offer text alternatives for any audio</td></tr>
<tr><td>Basic phone or little data</td><td>Offer SMS or USSD, and keep messages small</td></tr>
<tr><td>Older users or people new to phones</td><td>Large buttons where possible, numbered options, patient wording</td></tr>
<tr><td>Cost worries</td><td>Say if the service costs money; keep answers short to save data</td></tr>
</tbody>
</table>

<h3>Scenario: the PIN request</h3>
<p>A customer chats with a mobile money shop bot and types their PIN to "check the balance", because a scam message told them to. A good bot greets with a warning, never asks for PINs, detects the sequence of digits and replies, "Please never share your PIN. Staff will never ask for it." It also offers to connect the person to the shop owner. Designing with privacy and fraud in mind protects the user and the business.</p>

<blockquote><p><strong>Quick check:</strong> Why should a clinic bot not ask for a patient's full medical history in a chat?</p><p>Answer: Chat records can be seen by more people or leaked, and the bot only needs the minimum data to do its job; sensitive details are best handled in person or through a secure process.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A user says no to the data notice. What should the bot do?</p><p>Answer: Offer a real alternative, such as talking to staff, instead of blocking the user or ignoring the choice.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a welcome message for your bot that says it is a bot, how long chats are kept and what not to send.</li>
<li>Translate it into your second language with a fluent speaker.</li>
<li>List every piece of personal data your bot asks for and remove any that are not essential.</li>
<li>Check your bot against the accessibility table and note three improvements.</li>
<li>Write a rule for who may read chat logs and for how long they are kept.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Collect the minimum personal data, explain why, and delete it when no longer needed.</li>
<li>Ask for consent in plain language and offer a real alternative.</li>
<li>Never request PINs or passwords, and warn users not to share them.</li>
<li>Design for low literacy, voice, low vision, basic phones and low data.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Capstone: a bilingual FAQ chatbot',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will have a complete design and a working prototype of a bilingual FAQ chatbot for a school, clinic or small business, and you will be able to present it. This matters because a finished project shows employers and communities what you can do with the ideas in this course.</p>

<h3>Choose your organisation</h3>
<p>Pick a real organisation if you can, with the owner's permission, such as a primary school, a health centre, a salon, a hardware shop or a savings group. Real questions will make your project better than invented ones. If you cannot, use a realistic fictional one and say so.</p>

<h3>Capstone steps</h3>
<ol>
<li><strong>Goal and users:</strong> write a one-sentence goal with a number, a list of what the bot will not do, and a short user profile.</li>
<li><strong>Channel:</strong> choose one main channel and give reasons based on your users' phones and data.</li>
<li><strong>Question research:</strong> collect at least 20 real or realistic questions, clean them and count the common words.</li>
<li><strong>Intents:</strong> define 8 to 12 intents with at least five example messages each in both languages.</li>
<li><strong>Flows:</strong> draw the welcome, language choice, main menu, one slot-filling flow, fallback and hand-off.</li>
<li><strong>Knowledge base:</strong> write 10 to 15 answers in both languages, with owners and dates, checked by a fluent speaker.</li>
<li><strong>Prototype:</strong> build it in a free no-code tool, quick replies or slides; optionally add the Python keyword bot as a bonus.</li>
<li><strong>Test:</strong> test with at least five people who are like the real users; record the resolution rate and comments.</li>
<li><strong>Responsibility:</strong> write a privacy and consent message, say what data you keep and for how long, and check accessibility.</li>
<li><strong>Improve and present:</strong> fix the top three problems, then present for five minutes.</li>
</ol>

<h3>Example outline: a primary school bot</h3>
<table>
<thead><tr><th>Part</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Goal</td><td>Answer common parent questions on WhatsApp so the office receives 30 percent fewer calls in Term 2</td></tr>
<tr><td>Languages</td><td>English and Luganda, chosen at the start</td></tr>
<tr><td>Intents</td><td>greeting, ask_fees, term_dates, uniform_info, pickup_times, contact_office, payment_methods, lost_property</td></tr>
<tr><td>Slot flow</td><td>ask_fees asks for the class, then confirms before answering</td></tr>
<tr><td>Fallback</td><td>Menu plus "talk to the office" after two misses</td></tr>
<tr><td>Hand-off</td><td>Office number and hours; urgent words trigger an emergency message</td></tr>
<tr><td>Privacy</td><td>Notice at start; logs deleted after 30 days; no names asked</td></tr>
<tr><td>Measures</td><td>Resolution rate, fallback rate, a Yes/No "Did this help?"</td></tr>
</tbody>
</table>

<h3>Presenting your project</h3>
<p>In five minutes cover: the problem, the users, a short live demo (or screenshots), your test results with numbers, what you changed after testing, and what you would do next. Be honest about weaknesses. A project that says "our resolution rate was 60 percent, and these are the three fixes we made" is stronger than one that claims perfection.</p>

<h3>Marking yourself</h3>
<p>Ask: Is the goal measurable? Do the answers use plain language in both languages? Does the bot say it is a bot? Is there a fallback and a person to reach? Did real users test it? Are privacy and accessibility considered? Is the knowledge base owned and dated?</p>

<blockquote><p><strong>Quick check:</strong> Why does the capstone ask for testing with people like the real users?</p><p>Answer: Their wording, habits and language mix reveal problems that you and your team cannot see.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A presenter says "Our bot is perfect and needs no changes." What is missing?</p><p>Answer: Evidence from testing, such as numbers and user comments, and the improvements made because of them.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose your organisation and write the goal, user profile and channel on one page.</li>
<li>Complete the intents, flows and knowledge base for both languages.</li>
<li>Build the prototype and test it with five people.</li>
<li>Calculate your resolution rate, list the top three fixes and apply them.</li>
<li>Prepare your five-minute presentation and practise it once with a friend.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A capstone combines goal, users, channel, intents, flows, content, build, testing and responsibility.</li>
<li>Real questions and real testers make the project credible.</li>
<li>Show numbers and the changes you made, and be honest about limits.</li>
<li>Keep a plan for maintenance: someone must keep the answers current.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: In one week a school bot has 250 conversations. In 175 the parent got an answer without calling the office. What is the resolution rate?
- 25 percent
- 50 percent
* 70 percent
- 175 percent
= 175 divided by 250 is 0.7, which is 70 percent.

Q: Why is it a problem if only your project team tests the bot?
- Teams never find mistakes
* They know the expected wording and miss problems real users will meet
- Teams type too slowly
- The tool does not allow it
= Real users bring different words, languages and habits.

Q: A high resolution rate is reported, but many users in the logs wrote "ok never mind". What should you do?
- Celebrate and make no changes
* Check satisfaction and read those conversations, since users may have given up
- Delete the logs
- Increase the number of menu items
= Resolution alone can hide users who gave up, so pair it with satisfaction and real conversation reviews.

Q: Which practices protect privacy in a chatbot? (choose all that apply)
* Ask only for data you truly need
* Tell users not to send PINs or passwords
- Keep all chats forever in case they are useful
* Delete logs after a stated period
= Minimum data, clear warnings and deletion limits are core privacy practices. Keeping everything forever is a risk.

Q: A user declines the data notice at the start of the chat. What is the best design?
- Block the user from using the service
- Ignore the answer and continue storing the chat
* Offer another way, such as talking to staff
- Ask again every message until they agree
= Consent must be a real choice, so users need an alternative path.

Q: Which change best improves accessibility for users with low reading skill?
- Use longer, more formal sentences
* Use short sentences, common words and numbered menus
- Put all information inside images
- Remove the language choice
= Plain words and simple structure make the bot easier to understand, and images alone exclude people who use screen readers.

Q: A capstone presenter says only "It works well". Which addition makes the presentation stronger?
- A longer introduction about themselves
* Test numbers, user comments and the changes made as a result
- More emoji in the slides
- Skipping the demo
= Evidence from testing and visible improvements show real quality.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which best describes natural language processing (NLP)?
- Writing programs in Python only
* Methods that let computers work with human language in text or speech
- Making computers faster
- Designing computer keyboards
= NLP covers tasks such as counting words, sentiment, translation and speech.

Q: What is a token?
* A small piece of text, often a word or part of a word
- A password for a chatbot
- A payment coin
- A type of sentiment
= Tokenisation splits text into tokens that can be counted and processed.

Q: A shop owner has 300 customer messages and wants to know the most common topics. What is a simple, effective first step?
- Train a translation system
* Clean the text and count word frequencies
- Ask a generative bot to guess
- Delete messages with capitals
= Word counts on cleaned messages quickly show frequent topics.

Q: In "I want to pay school fees for Term 3 on Friday", which is the intent and which is an entity?
- Intent: Friday; Entity: pay fees
* Intent: pay school fees; Entities: Term 3 and Friday
- Intent: Term 3; Entity: school
- There is no intent
= The intent is the goal; the entities are the details.

Q: A word-list sentiment tool gives "Great, the clinic is closed again" a positive score. What explains this?
- The tool understands sarcasm perfectly
* It counted the word "great" and missed the sarcasm
- Clinics always get positive scores
- The word "again" is positive
= Simple word lists ignore tone and context.

Q: Which is the safest approach to an important health notice translated by a tool?
- Publish it at once, because the tool is fast
* Check it with a fluent speaker, ideally also translating it back
- Shorten it until nobody can misread it
- Post it only in English
= Wrong health information can harm people, so human review is needed.

Q: Which statement about "under-resourced" languages is true?
- They have few speakers
- They cannot be written down
* They have little digital data and few tools, even if many people speak them
- They are always dying languages
= Swahili and Luganda are widely spoken, but have far less digital data than English.

Q: Which are community-driven ways to improve data for an African language? (choose all that apply)
* Volunteers recording and checking sentences
* Speakers correcting translations
- Secretly collecting private chats
* Building shared word lists with permission and credit
= Community efforts rely on consent, fairness and speaker knowledge.

Q: A volunteer wants health-bot training data. Which plan respects the mothers at a clinic?
- Record consultations and tell them later
- Ask the clinic to share its records without telling patients
* Recruit volunteers to read prepared sentences with a clear consent script and fair payment
- Use public photos of the mothers
= Consent, purpose and fair return are essential, and private consultations should not be used without agreement.

Q: Which goal is best for a clinic chatbot?
- Show that the clinic uses AI
* Answer opening times, location and service questions on WhatsApp within one minute and cut phone calls by a quarter
- Replace the nurses
- Be available on every possible platform
= A good goal is specific, measurable and realistic.

Q: A women's savings group has basic phones and little data. Which channel is the best fit?
* USSD or SMS
- A website with videos
- A bot that needs a large app download
- A video call line
= SMS and USSD work without data on basic phones.

Q: A bot asks "Which class is your child in?" before giving fees. What is this question for?
- Greeting the user
* Filling a slot (entity) needed for the answer
- Testing sentiment
- Recording speech
= Slots collect details the bot needs to give a correct answer.

Q: The best fallback response when a bot does not understand twice is:
- Repeat the same apology
- Say nothing
* Offer the main menu and a way to reach a person
- Answer with a random fact
= Fallbacks should help users move on and escalate to a human when needed.

Q: Which message should always trigger a hand-off to a human and emergency information?
- "What time do you open?"
- "Thank you"
* "My baby is not breathing well"
- "Hello"
= Urgent health words need immediate human help or emergency guidance, not a normal bot reply.

Q: Which type of bot returns only answers you wrote and approved, choosing the closest match?
- Generative
* Retrieval
- Speech synthesis
- Sentiment
= A retrieval bot picks from prepared answers.

Q: A pharmacy wants a bot for dosage questions. Which design is wisest?
- A free-text generative bot with no limits
- A bot that guesses doses from symptoms
* Approved information for fixed facts and hand-off to a pharmacist for dosage questions
- A bot that avoids all human contact
= High-risk topics need approved content and human judgement.

Q: What is the main purpose of an "owner" and "last checked" date in a knowledge base?
- Decoration
- To track user names
* To keep every answer current and someone responsible for it
- To translate the answer automatically
= Without ownership, outdated information stays in the bot.

Q: Which answer is best for a chatbot on a phone?
- A long legal paragraph
- "It depends."
* "The clinic opens at 8 a.m., Monday to Saturday. Reply MENU for other topics."
- An image with all the text inside it
= It gives the key fact first, is short and offers a next step; text inside an image is hard to read for some users.

Q: In the Python code, counts[word] = counts.get(word, 0) + 1 is used to:
- Delete a word
* Add one to the count for that word, starting from zero if it is new
- Translate the word
- Print the word
= The get method returns 0 when the word is not yet in the dictionary.

Q: Your Python keyword bot has the key "fees" and receives "How much are the FEES". What must you do for it to match?
- Nothing, it always matches
* Convert the message to lower-case before checking the words
- Remove all spaces
- Translate the message first
= Lower-casing makes "FEES" match "fees".

Q: A bot handled 400 conversations and 260 were resolved without a human. What is the resolution rate?
- 15 percent
- 40 percent
- 52 percent
* 65 percent
= 260 divided by 400 is 0.65.

Q: Why should testers be people like real users, not only the project team?
* The team knows the expected wording and will not meet the same problems
- Teams cannot use phones
- Real users are always kinder
- It is cheaper
= Real users type differently and mix languages in ways the team may not expect.

Q: Which are good privacy practices for a chatbot? (choose all that apply)
* Collect only what is needed and explain why
* Warn users never to share PINs or passwords
* Delete chat logs after a stated time
- Keep every chat forever for future use
= Minimum data, warnings and deletion limits protect users.

Q: A user refuses the data notice. What should the bot do?
- Continue as normal and store everything
- Lock the user out
* Offer another way to get help, such as staff contact
- Repeat the notice every minute
= Consent must be a real choice with an alternative.

Q: Which feature improves accessibility for many users?
- Long, formal paragraphs
* Short sentences, simple menus and an option to speak with a person
- Essential information only inside pictures
- Hiding the hand-off option
= Plain language, simple structure and human support help users with different skills.

Q: A bot works well in English but poorly in Luganda. What is the best first action?
- Hide the Luganda option
* Test with fluent Luganda speakers, improve the written answers and add a menu and hand-off
- Tell users to learn English
- Replace the bot with a generative one and stop testing
= Fix content with native speakers, support users with menus, and always test.
QUIZ,
];
