<?php
return [
    'slug'        => 'ux-ui-design-basics',
    'title'       => 'UX and UI Design: Design Apps People Love',
    'category'    => 'design',
    'level'       => 'intermediate',
    'icon'        => 'layers',
    'sort'        => 340,
    'summary'     => 'Learn the full design process for apps and websites: research real users, plan screens, sketch and wireframe, style with colour and type, make it accessible, test it, and build a portfolio.',
    'description' => '<p>Every app on your phone was designed by someone. Some feel easy and friendly; others make you tap the wrong button, hunt for a menu or give up. The difference is usually design: understanding who the users are, what they are trying to do, and shaping every screen to help them do it.</p><p>This intermediate course teaches UX (user experience) and UI (user interface) design as a practical process. You will talk to users, write personas and journeys, organise content, sketch and wireframe, choose colours and type, build a small design system, design for accessibility and small screens, build a clickable prototype and test it with real people. You will use free tools only: paper, a pencil, and the free plan of Figma or a similar design tool.</p><p>Examples come from mobile money, school portals, local shops and community services, because good design starts with real local problems.</p>',
    'outcomes'    => [
        'Explain the difference between UX and UI and describe the design process from research to testing',
        'Plan and run simple user interviews and turn the findings into a persona and a problem statement',
        'Map a user journey and a user flow to find where people struggle',
        'Organise content with card sorting and design clear navigation',
        'Sketch and wireframe screens on paper and in a free design tool',
        'Apply hierarchy, spacing, colour, contrast and typography to make screens clear',
        'Build a small design system of reusable components',
        'Design for accessibility and for small mobile screens first',
        'Build a clickable prototype, run a usability test and improve the design',
        'Present your work as a short case study in a portfolio',
    ],
    'requirements' => ['Comfortable use of a computer or laptop and the internet (see Computer Basics and Digital Literacy, or equivalent)', 'Paper, a pencil or pen, and a smartphone for sketching and testing', 'A free account on a design tool such as the free plan of Figma (used from Module 2)', 'Optional: completed Graphic Design Fundamentals, which helps with the visual design module'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'W3C Web Accessibility Initiative: Introduction to Web Accessibility', 'url' => 'https://www.w3.org/WAI/fundamentals/accessibility-intro/'],
        ['title' => 'WCAG 2 Overview (W3C)', 'url' => 'https://www.w3.org/WAI/standards-guidelines/wcag/'],
        ['title' => 'Figma Help Center', 'url' => 'https://help.figma.com/'],
        ['title' => 'Nielsen Norman Group: UX articles and guidelines', 'url' => 'https://www.nngroup.com/articles/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: Understanding users',
            'summary' => 'What UX and UI are, how to talk to users, and how to turn what you learn into personas, journeys and a clear problem statement.',
            'lessons' => [
                [
                    'title'   => 'What UX and UI design really are',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain the difference between UX and UI design, describe the steps of the design process, and judge a simple app with a designer's eye. This matters because many people think design means "making things pretty", when its real job is making things work for people.</p>

<h3>UX and UI in plain words</h3>
<p><strong>UX</strong> (user experience) is the whole experience a person has while trying to get something done: finding the app, signing up, completing a task, getting help, and how they feel afterwards. <strong>UI</strong> (user interface) is the part they see and touch: buttons, text, colours, icons, menus and layout.</p>
<p>Think about paying for electricity with mobile money. The UX is everything from "I need to buy units" to "I received my token and the lights work". The UI is the menu you navigate, the labels on each screen and the confirmation message. A beautiful screen with a confusing payment step is good UI and poor UX.</p>
<table>
<thead><tr><th></th><th>UX design</th><th>UI design</th></tr></thead>
<tbody>
<tr><td>Main question</td><td>Does this help people reach their goal?</td><td>Is this clear, consistent and pleasant to use?</td></tr>
<tr><td>Typical work</td><td>Research, journeys, flows, wireframes, testing</td><td>Layout, colour, type, icons, components, prototypes</td></tr>
<tr><td>Example output</td><td>A map of the steps to book a clinic visit</td><td>The finished booking screens with branding</td></tr>
</tbody>
</table>
<p>In small teams one person often does both. You need to understand both halves.</p>

<h3>The design process</h3>
<p>Designers rarely start by drawing screens. A common process has five stages, and good teams repeat them in loops.</p>
<ol>
<li><strong>Empathise.</strong> Learn about users through interviews, observation and questions.</li>
<li><strong>Define.</strong> Write down the real problem in a short statement.</li>
<li><strong>Ideate.</strong> Sketch many possible solutions quickly.</li>
<li><strong>Prototype.</strong> Build a simple, testable version.</li>
<li><strong>Test.</strong> Watch real people use it, learn, then improve.</li>
</ol>
<p>This course follows these stages. The big idea is <strong>iteration</strong>: you will be wrong about some things, so you test early and cheaply, before anyone spends money building it.</p>

<h3>Designing for someone, not for yourself</h3>
<p>You are not your user. A young developer in Kampala with fast data may love a feature that a market trader with a small phone and expensive bundles cannot use. Good designers keep asking "who is this for, and what do they need?" Make that your habit.</p>

<h3>Signs of good and poor design</h3>
<ul>
<li>Good: users finish the main task quickly, rarely make mistakes, and recover easily when they do.</li>
<li>Poor: users need instructions for simple tasks, tap the wrong thing often, or leave without finishing.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Pick one app you use every day, such as a mobile money menu, a school portal or a transport app.</li>
<li>Write down the single most important task a user does in it.</li>
<li>Do the task and count the number of taps and screens.</li>
<li>Note one thing that felt easy (UX) and one thing about its look that helped or confused you (UI).</li>
<li>Write one sentence on how you would improve the task.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>UX is the whole experience; UI is what people see and touch.</li>
<li>The process runs: empathise, define, ideate, prototype, test, then repeat.</li>
<li>You are not your user, so learn from real people.</li>
<li>Good design makes tasks quick, clear and forgiving.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'User research: talking to real people',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to plan and run a short user interview and write down what you learn without bias. This matters because the cheapest way to avoid building the wrong thing is to listen to people before you design anything.</p>

<h3>Why research first</h3>
<p>Imagine a team builds an app to help farmers check market prices. They add charts, notifications and a map. Then they discover that farmers mostly want prices by SMS, because many farmers use basic phones. Thirty minutes of conversation would have saved months of work. Research reduces guessing.</p>

<h3>Common research methods</h3>
<table>
<thead><tr><th>Method</th><th>What you do</th><th>Best for</th></tr></thead>
<tbody>
<tr><td>Interview</td><td>Talk one to one for 20 to 30 minutes</td><td>Understanding goals, habits and frustrations</td></tr>
<tr><td>Observation</td><td>Watch people do a real task</td><td>Finding what people do rather than what they say</td></tr>
<tr><td>Survey</td><td>Ask the same short questions to many people</td><td>Counting how common something is</td></tr>
<tr><td>Competitor review</td><td>Use similar products and note strengths and gaps</td><td>Learning what is already normal</td></tr>
</tbody>
</table>
<p>For a small project, five to eight interviews already show strong patterns.</p>

<h3>Planning an interview</h3>
<ol>
<li>Write your <strong>research goal</strong> in one sentence, for example: "Understand how small shop owners keep track of credit given to customers."</li>
<li>Choose 5 participants who match your target users. Ask politely and explain that you are learning, not selling.</li>
<li>Write 6 to 8 open questions. Open questions cannot be answered with yes or no.</li>
<li>Get permission before you take notes or record. Keep names private.</li>
</ol>

<h3>Good and poor questions</h3>
<ul>
<li>Poor (leading): "Wouldn't a mobile app make your bookkeeping easier?" This pushes the answer.</li>
<li>Better: "Tell me about the last time a customer bought on credit. What happened?"</li>
<li>Poor (future): "Would you use an app like this?" People are bad at predicting their own behaviour.</li>
<li>Better (past): "What do you use today to remember who owes you? What is annoying about it?"</li>
</ul>
<p>Ask "why" gently and follow up with "can you show me?" Silence is useful: wait a few seconds and people often add the most honest detail.</p>

<h3>During and after the interview</h3>
<p>Listen more than you talk. Write what people <em>do and say</em>, not your opinion. After each session, spend ten minutes writing the main points while they are fresh. Then collect all notes, copy single observations onto sticky notes or lines on paper, and group similar ones. The groups are your <strong>insights</strong>, such as "Owners forget small debts" or "Customers feel shy being reminded".</p>

<h3>Try it yourself</h3>
<ol>
<li>Choose a small problem near you, such as how students find a quiet place to study.</li>
<li>Write a one-sentence research goal.</li>
<li>Write six open questions and rewrite any that lead the answer.</li>
<li>Interview two people for ten minutes each and take notes.</li>
<li>Write three insights, each starting with "People..."</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Research before design prevents building the wrong thing.</li>
<li>Interviews work best with open questions about past behaviour.</li>
<li>Avoid leading questions and let people finish.</li>
<li>Group your notes into insights that will guide the design.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Personas and empathy maps',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to turn your research into a realistic persona and an empathy map that your whole team can use. This matters because a design team cannot keep a dozen interview notes in their heads, but they can remember one clear person.</p>

<h3>What is a persona?</h3>
<p>A <strong>persona</strong> is a short profile of a fictional but realistic user, built from patterns in your research. It is not a real individual, and it is not a stereotype guessed from age or gender. It exists to answer the question "who are we designing for?"</p>

<h3>What a useful persona contains</h3>
<ul>
<li><strong>Name, age and a short background.</strong> Keep it respectful and varied, for example Achieng, 24, runs a small tailoring shop in Jinja.</li>
<li><strong>Goals.</strong> What she wants to achieve, such as "know how much profit I make each week".</li>
<li><strong>Frustrations.</strong> What stops her, such as "I forget who owes me money".</li>
<li><strong>Context.</strong> Device, data costs, electricity, language, time of day, skills.</li>
<li><strong>A quote</strong> taken from your research that captures her attitude.</li>
</ul>
<p>Do not add random details that did not come from research, such as favourite colour or pet, unless they affect the design. Every line should help someone make a decision.</p>

<h3>An example</h3>
<blockquote>Achieng, 24, tailor in Jinja. Uses a mid-range Android phone with a limited data bundle and sells mostly on credit to regular customers. Goal: know who owes her without carrying a notebook. Frustration: customers argue about amounts. Quote: "I just want to show them the number and be done."</blockquote>
<p>From this persona, the team learns that the app must work offline or with little data, must show amounts clearly, and must be quick to use while she is busy.</p>

<h3>Empathy maps</h3>
<p>An <strong>empathy map</strong> is a quick way to organise research before you write a persona. Draw a square divided into four parts and write notes in each.</p>
<table>
<thead><tr><th>Part</th><th>Question</th></tr></thead>
<tbody>
<tr><td>Says</td><td>What did the person say out loud?</td></tr>
<tr><td>Thinks</td><td>What might be on their mind but not said?</td></tr>
<tr><td>Does</td><td>What actions did you observe?</td></tr>
<tr><td>Feels</td><td>What emotions showed, such as worry, pride, shyness?</td></tr>
</tbody>
</table>
<p>Gaps between "says" and "does" often reveal the best design opportunities. Someone may say "I always save my receipts" while the observation shows a pile of loose papers in a drawer.</p>

<h3>Common mistakes</h3>
<ul>
<li>Inventing a persona without research. It then only repeats your own opinions.</li>
<li>Making a perfect, happy persona. Real users have limits and frustrations.</li>
<li>Making ten personas. Start with one to three main ones.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Take the notes from your interviews in the last lesson.</li>
<li>Draw an empathy map on paper for your strongest interview.</li>
<li>Write one persona on half a page with name, goal, frustration, context and quote.</li>
<li>Underline two details that should change your design.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A persona is a research-based profile, not a guess.</li>
<li>Include goals, frustrations and context, especially device and data limits.</li>
<li>Empathy maps organise what users say, think, do and feel.</li>
<li>Keep the number of personas small and useful.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'User journeys and problem statements',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to map a user journey, spot the moments where people struggle, and write a clear problem statement. This matters because a team that agrees on the exact problem designs better and argues less.</p>

<h3>What is a user journey map?</h3>
<p>A <strong>user journey map</strong> shows the steps a persona takes to reach a goal, what they do, think and feel at each step, and where the pain points are. It tells a story over time. You draw it on a long sheet of paper or a wall with sticky notes.</p>

<h3>Building a journey map step by step</h3>
<ol>
<li><strong>Choose one persona and one goal.</strong> For example, Achieng wants to check which customers owe her money.</li>
<li><strong>List the stages</strong> across the top: Notice need, Look for information, Record debts, Remind customers, Get paid.</li>
<li><strong>Under each stage write the actions.</strong> "Opens notebook, flips pages, reads handwriting".</li>
<li><strong>Add thoughts and feelings.</strong> "Where did I write that?" and a worried face.</li>
<li><strong>Draw a line</strong> that goes up for good moments and down for bad ones. The lowest points are your pain points.</li>
<li><strong>Mark opportunities</strong> beside each pain point, for example "search customers by name".</li>
</ol>

<h3>Example fragment</h3>
<table>
<thead><tr><th>Stage</th><th>Action</th><th>Feeling</th><th>Pain point</th><th>Opportunity</th></tr></thead>
<tbody>
<tr><td>Record debts</td><td>Writes amount in a notebook</td><td>Rushed</td><td>Pages get lost or smudged</td><td>Quick entry with a name and amount</td></tr>
<tr><td>Remind customers</td><td>Calls or visits</td><td>Awkward</td><td>Feels rude</td><td>A polite pre-written reminder message</td></tr>
</tbody>
</table>

<h3>Journey versus user flow</h3>
<p>A journey map looks at the person's whole experience, including feelings and moments outside the app. A <strong>user flow</strong> (next module) looks at the steps inside the product. Both are useful: the journey shows what to solve, the flow shows how to solve it.</p>

<h3>Writing a problem statement</h3>
<p>A problem statement turns your findings into one sentence that guides the project. A simple pattern is:</p>
<pre><code>[Persona] needs a way to [goal] because [insight].</code></pre>
<p>Example: "Small shop owners like Achieng need a way to track customer credit quickly while serving others, because paper notebooks are slow to search and cause arguments about amounts."</p>
<p>A good statement is about the user's need, not a solution. "We need an app with a dashboard" is a solution in disguise. After the statement you can write <strong>How might we</strong> questions, such as "How might we make recording a debt take under ten seconds?" These open the door to many ideas.</p>

<h3>Try it yourself</h3>
<ol>
<li>Use your persona from the previous lesson.</li>
<li>Draw a journey map with five stages across a sheet of paper.</li>
<li>Circle the two lowest emotional points.</li>
<li>Write a problem statement with the pattern above.</li>
<li>Write three "How might we" questions.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A journey map shows actions, feelings and pain points over time.</li>
<li>Opportunities come from the lowest points of the journey.</li>
<li>A problem statement describes the need, not the solution.</li>
<li>"How might we" questions start the ideation stage.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which statement best describes UX design?
- Choosing colours and fonts for an app
* Shaping the whole experience a person has while trying to reach a goal
- Writing the code that makes an app run
- Drawing icons and logos
= UX covers the entire experience, including finding, learning, using and feeling about a product. Colours and fonts belong mainly to UI.

Q: A mobile money menu looks beautiful, but users often fail to complete a payment. What is the likely problem?
- Poor UI only, because the colours are wrong
* Poor UX, because the experience of completing the task does not work, whatever the appearance
- Poor hardware
- Too many users
= Looks can be pleasant while the flow of steps is confusing. UX is judged by whether people can reach their goal.

Q: Which interview question is the best for learning about real behaviour?
- Wouldn't a mobile app make your work easier?
- Would you pay for a budgeting app?
* Tell me about the last time you gave a customer goods on credit. What happened?
- Do you like using your phone?
= The best question asks about a real past event and is open. The others lead the answer or ask people to predict the future.

Q: Which of these are examples of research methods? (choose all that apply)
* User interviews
* Observation of people doing a task
- Choosing a brand colour
* A short survey
= Interviews, observation and surveys all collect information from users. Choosing a colour is a design decision, not research.

Q: Why is a persona built from research better than one invented in a meeting?
- It looks more professional
- It is shorter
* It reflects real patterns instead of the team's own assumptions
- It means you do not need testing
= Personas are useful only when they come from evidence. Testing is still needed later.

Q: On an empathy map, which part captures emotions such as worry or shyness?
- Says
- Does
- Thinks
* Feels
= The Feels section records the emotions you observed or heard. Says and Does record words and actions.

Q: Which is a good problem statement?
- We need an app with a colourful dashboard
* Small shop owners need a way to track customer credit quickly because notebooks are slow to search and cause disputes
- Users should like our product
- Build a mobile version of the website
= A good statement names the user, the need and the reason, without choosing the solution in advance.

Q: In a journey map, where do you usually look for design opportunities?
- At the highest emotional points only
- At the title of the map
* At the pain points, where feelings and actions are lowest
- At the number of stages
= The lowest points show where users struggle and where better design will help most.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Structure and wireframes',
            'summary' => 'Organise content, map the steps users take, and sketch screens on paper and in a free design tool before choosing any colours.',
            'lessons' => [
                [
                    'title'   => 'Information architecture and card sorting',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to organise the content of an app or website into groups that make sense to users, using a card sorting exercise. This matters because the best-looking menu fails if people cannot guess where to find what they need.</p>

<h3>What is information architecture?</h3>
<p><strong>Information architecture</strong> (often shortened to IA) is how content and features are organised, grouped and labelled. Think of a supermarket: rice, sugar and flour are together, drinks are in another aisle, and signs tell you where things are. If the shop mixed soap with vegetables, you would be lost. An app needs the same sense of order.</p>
<p>IA answers three questions for the user: <em>Where am I? Where can I go? Where will I find what I want?</em></p>

<h3>Card sorting</h3>
<p><strong>Card sorting</strong> is a simple research method for finding how users group things in their own minds.</p>
<ol>
<li>List 15 to 30 items of content or features, for example for a school portal: Timetable, Exam results, Fee balance, Pay fees, Library books, Notices, Clubs, Contact the head teacher, Report cards, Past papers.</li>
<li>Write each item on its own small card or piece of paper.</li>
<li>Ask a participant to place the cards into groups that feel natural and to give each group a name.</li>
<li>Repeat with 5 to 8 people.</li>
<li>Look for groups that most people agree on, and for cards that people disagree about.</li>
</ol>
<p>In an <strong>open</strong> card sort people name the groups themselves. In a <strong>closed</strong> card sort you give them the group names and they place the cards. Use open sorting when you are creating a structure, and closed sorting when you want to check one.</p>

<h3>Turning results into a site map</h3>
<p>A <strong>site map</strong> (or app map) is a tree diagram of the structure. The school portal might become:</p>
<pre><code>Home
  Academics: Timetable, Exam results, Report cards, Past papers
  Fees: Fee balance, Pay fees
  School life: Notices, Clubs, Library books
  Contact: Head teacher, Office</code></pre>
<p>Note that "Contact the head teacher" became part of Contact even though one tester placed it under Notices. Where opinions differ, decide by what most users expect and test later.</p>

<h3>Rules for good structure and labels</h3>
<ul>
<li><strong>Use the words your users use.</strong> "Pay fees" is clearer than "Financial settlements".</li>
<li><strong>Keep groups to about 5 to 9 items.</strong> Too many choices slow people down.</li>
<li><strong>Do not hide key things deep.</strong> Important tasks should be reachable in about three taps or clicks.</li>
<li><strong>Be consistent.</strong> The same thing should have the same name everywhere.</li>
<li><strong>Avoid jargon and clever names.</strong> A label should describe what is behind it.</li>
</ul>

<h3>Common mistakes</h3>
<ul>
<li>Organising by how your organisation is structured (departments) instead of what users want to do.</li>
<li>Using the same word for two different things, such as "Account" for both login and finance.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Choose an app or website you know, such as a local clinic's website.</li>
<li>Write 20 of its features or pages on separate slips of paper.</li>
<li>Ask two friends to sort them into groups and name each group.</li>
<li>Compare their groups with each other and with the real menu.</li>
<li>Draw a simple site map with your improved structure.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Information architecture organises and labels content so people can find it.</li>
<li>Card sorting shows how real users group ideas.</li>
<li>A site map turns the groups into a clear structure.</li>
<li>Use plain labels, small groups and consistent naming.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'User flows and navigation patterns',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to draw a user flow for one task and choose a suitable navigation pattern for a mobile app. This matters because most usability problems come from missing steps, dead ends or menus that hide the important things.</p>

<h3>What is a user flow?</h3>
<p>A <strong>user flow</strong> is a diagram of the steps a user takes inside a product to complete one task, including decisions and error paths. It uses a few simple shapes.</p>
<table>
<thead><tr><th>Shape</th><th>Meaning</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Rounded box</td><td>Start or end</td><td>Open app / Payment complete</td></tr>
<tr><td>Rectangle</td><td>A screen or step</td><td>Enter amount</td></tr>
<tr><td>Diamond</td><td>A decision with yes or no</td><td>Enough balance?</td></tr>
<tr><td>Arrow</td><td>Direction of travel</td><td>From one step to the next</td></tr>
</tbody>
</table>

<h3>Drawing a flow step by step</h3>
<ol>
<li>Pick one task with a clear start and end, for example "Send money to a saved contact".</li>
<li>Write the entry point: where does the user begin?</li>
<li>Add each screen or action as a rectangle in order.</li>
<li>Add a diamond wherever the path can split, such as "Is the PIN correct?"</li>
<li>Draw the <strong>unhappy paths</strong> too: wrong PIN, no network, cancelled payment. Many designs show only the happy path and then fail in real life.</li>
<li>Count the steps and ask which can be removed or combined.</li>
</ol>
<p>Fewer steps are not always better, but each step must earn its place. If a step collects information you do not use, remove it.</p>

<h3>Navigation patterns on mobile</h3>
<p>Navigation is how users move between the main areas. Common patterns:</p>
<ul>
<li><strong>Bottom tab bar.</strong> Three to five icons with labels fixed at the bottom. It is easy to reach with a thumb and best for main areas used often.</li>
<li><strong>Hamburger menu.</strong> Three lines opening a hidden list. It saves space but hides options, so people use it less. Good for secondary items.</li>
<li><strong>Top bar with a back arrow.</strong> Shows the title and lets users go back one step. Always provide a clear way back.</li>
<li><strong>Cards and lists.</strong> Content blocks that lead deeper, common on home screens.</li>
<li><strong>Search.</strong> Essential when there is a lot of content.</li>
</ul>
<p>Choose the pattern from your IA. If users do four things most often, give them four tabs. Put rare things in a menu.</p>

<h3>Principles to follow</h3>
<ul>
<li><strong>Always show where you are:</strong> highlight the current tab and show a clear title.</li>
<li><strong>Do not trap people:</strong> every screen needs a way back or out.</li>
<li><strong>Be predictable:</strong> keep navigation in the same place on every screen.</li>
<li><strong>Use text with icons.</strong> Many icons are not understood by everyone, so add a short label.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Choose a task such as "Register for a free course" on your favourite learning site.</li>
<li>Draw its user flow on paper, with at least one decision and one error path.</li>
<li>Count the steps and circle one that could be removed.</li>
<li>Decide which pattern you would use for the main navigation and list the tab names (no more than five).</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A user flow shows the path through one task, including errors.</li>
<li>Draw unhappy paths as carefully as the happy path.</li>
<li>A bottom tab bar suits the few main areas of a mobile app.</li>
<li>Show location, offer a way back and keep navigation consistent.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Sketching and wireframing',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to sketch screen ideas quickly on paper and turn the best ones into a simple wireframe in a free design tool. This matters because wireframes let you test structure and content cheaply, before colours, photos and code distract everyone.</p>

<h3>Start with paper</h3>
<p>Paper and a pencil are the fastest design tools in the world. They cost almost nothing, nobody is shy of rubbing out a sketch, and you can do twenty ideas in ten minutes. A technique called <strong>Crazy 8s</strong> works well: fold a sheet of paper into eight boxes and sketch eight different versions of one screen in eight minutes. Quantity first, quality later. Then pick one or two ideas to develop.</p>
<p>Draw a phone outline, about 6 by 12 cm, to keep your sketches the right shape. Use simple conventions: a box with a cross for a picture, wavy lines for text, a rectangle with a word for a button.</p>

<h3>What is a wireframe?</h3>
<p>A <strong>wireframe</strong> is a simple, usually grey, drawing of a screen showing layout, content and function, without final colours or images. It answers: what is on the screen, how big is each part, and what happens if I tap this?</p>
<table>
<thead><tr><th>Fidelity</th><th>What it looks like</th><th>Use it for</th></tr></thead>
<tbody>
<tr><td>Low</td><td>Paper sketches or rough boxes</td><td>Exploring ideas, quick feedback</td></tr>
<tr><td>Medium</td><td>Neat grey boxes with real text</td><td>Checking structure and content</td></tr>
<tr><td>High</td><td>Near-final screens with colour and images</td><td>Visual design and developer hand-off</td></tr>
</tbody>
</table>

<h3>Building a wireframe in a free design tool</h3>
<p>The free plan of Figma runs in a web browser, so you only need an internet connection. The steps below also apply to other free design tools, whose menus are similar.</p>
<ol>
<li>Create a new design file and choose the <strong>Frame</strong> tool (shortcut <strong>F</strong>). Pick a phone size from the list on the right, for example 390 by 844.</li>
<li>Use the <strong>Rectangle</strong> tool (<strong>R</strong>) to draw boxes for the header, image areas and buttons. Fill them with light greys.</li>
<li>Use the <strong>Text</strong> tool (<strong>T</strong>) to add real words. Avoid "Lorem ipsum" where you can, because real content shows real problems such as long names.</li>
<li>Group related items (<strong>Ctrl + G</strong>, Mac <strong>Cmd + G</strong>) and name layers clearly.</li>
<li>Duplicate the frame (<strong>Ctrl + D</strong>) to create the next screen and change it.</li>
</ol>

<h3>Wireframe guidelines</h3>
<ul>
<li>Put the main action where a thumb can reach, towards the bottom of the screen.</li>
<li>Give one screen one main purpose and one main button.</li>
<li>Show empty states, errors and loading, not only the perfect screen.</li>
<li>Keep touch targets large enough, at least about 44 to 48 pixels high.</li>
<li>Use grey only. Colour at this stage invites arguments about taste instead of function.</li>
</ul>

<h3>Common mistakes</h3>
<ul>
<li>Jumping to a polished design before testing the structure.</li>
<li>Designing only the home screen and forgetting settings, errors and empty lists.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Choose one screen from your project, such as "Add a customer debt".</li>
<li>Do a Crazy 8s sketch on paper.</li>
<li>Choose your favourite and redraw it carefully with real text.</li>
<li>Rebuild it as a medium-fidelity wireframe in a free design tool, using greys only.</li>
<li>Show it to a friend and ask, "What would you tap first?"</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Sketch many ideas on paper before choosing one.</li>
<li>Wireframes show layout and function without colour or decoration.</li>
<li>Use real content and design the empty and error states.</li>
<li>Keep one main purpose per screen and reachable, large targets.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is information architecture?
- The electrical wiring of a building
* How content and features are organised, grouped and labelled
- The colour scheme of an app
- The programming language used
= IA is the structure and labelling that helps users find things.

Q: In a card sort, participants group cards and also name the groups themselves. What is this called?
- Closed card sort
* Open card sort
- Usability test
- Persona workshop
= In an open sort people create and name groups. In a closed sort the group names are given.

Q: A bank organises its menu by internal departments such as "Retail Operations", but customers cannot find "Pay a bill". What is the best fix?
- Add more colours
- Remove the search box
* Reorganise and rename items using the words and tasks customers use
- Move every item to a hamburger menu
= Structure should follow users' tasks and language, not the organisation chart.

Q: Why should a user flow include error paths such as a wrong PIN or no network?
- To make the diagram bigger
* Because real users meet these situations and the design must handle them
- Because diamonds look better than rectangles
- Errors are not part of design
= Showing only the happy path hides problems that appear in real use.

Q: Which navigation pattern suits three to five frequently used main areas of a mobile app?
* A bottom tab bar with labels
- A hidden menu for everything
- A long scrolling list of links
- Pop-up windows
= A bottom tab bar is easy to reach and always visible, so it suits main areas.

Q: Which is true about wireframes? (choose all that apply)
* They usually use grey boxes without final colours
* They help test structure and content cheaply
- They must always be finished before research
* They should include real text where possible
= Wireframes come after research and focus on layout and function. Real text shows true content problems.

Q: What is the purpose of the Crazy 8s sketching method?
- To make one perfect drawing
* To produce many different ideas quickly before choosing
- To test a finished product
- To choose brand colours
= Sketching eight versions in eight minutes pushes you past the first obvious idea.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Visual design and design systems',
            'summary' => 'Make screens clear and attractive with hierarchy, spacing, colour and type, then capture your choices in a small design system.',
            'lessons' => [
                [
                    'title'   => 'Layout, hierarchy and spacing',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to arrange a screen so that the most important thing is noticed first and everything else is easy to scan. This matters because people do not read screens; they glance at them, and good layout decides what they see in that glance.</p>

<h3>Visual hierarchy</h3>
<p><strong>Visual hierarchy</strong> is the order in which the eye notices things. You control it with a few tools.</p>
<ul>
<li><strong>Size:</strong> bigger things are noticed first.</li>
<li><strong>Weight and contrast:</strong> bold, dark text on a light background stands out; light grey text steps back.</li>
<li><strong>Colour:</strong> one strong colour for the main action draws attention.</li>
<li><strong>Position:</strong> people in left-to-right languages tend to look at the top left first, then scan downwards.</li>
<li><strong>Space:</strong> an item surrounded by empty space looks important.</li>
</ul>
<p>Try the <strong>squint test</strong>: half-close your eyes and look at your design. The things you can still see are your top level of hierarchy. If you see the wrong things, such as a decorative image instead of the Pay button, adjust.</p>

<h3>Grids and alignment</h3>
<p>A <strong>grid</strong> is an invisible set of columns that keeps elements aligned. On a phone, a 4-column grid with 16-pixel margins is common; on a laptop, 12 columns are typical. Aligned items look calm and professional; items that are almost aligned look like mistakes. In your design tool, turn on layout grids (in Figma, select a frame and add a <strong>Layout grid</strong> in the right panel).</p>

<h3>Spacing with a simple scale</h3>
<p>Random gaps make a design feel messy. Instead choose a spacing scale based on multiples of 4 or 8 pixels: 4, 8, 16, 24, 32, 48. Use small gaps between related items and larger gaps between groups. This is the principle of <strong>proximity</strong>: things close together are seen as related.</p>
<table>
<thead><tr><th>Relationship</th><th>Suggested gap</th></tr></thead>
<tbody>
<tr><td>Label and its input field</td><td>4 to 8 px</td></tr>
<tr><td>Two fields in a form</td><td>16 px</td></tr>
<tr><td>Between sections</td><td>32 to 48 px</td></tr>
<tr><td>Screen edge margin</td><td>16 to 24 px</td></tr>
</tbody>
</table>

<h3>White space is not wasted space</h3>
<p>Beginners often fill every corner. White space (also called negative space, even if the background is coloured) gives the eye places to rest and makes text easier to read. On small screens it may feel expensive, but crowded screens cause mis-taps and tiredness.</p>

<h3>Common mistakes</h3>
<ul>
<li>Making everything bold or big, so nothing stands out.</li>
<li>Using many different gap sizes.</li>
<li>Centering all text, which makes long lines hard to read. Left-align paragraphs.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Open the wireframe you made in Module 2 in your design tool.</li>
<li>Decide the single most important element on the screen and make it clearly the most noticeable.</li>
<li>Apply a layout grid and align all items to it.</li>
<li>Change all gaps to multiples of 8 pixels.</li>
<li>Do the squint test and ask a friend what they notice first.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Hierarchy uses size, contrast, colour, position and space to guide the eye.</li>
<li>Grids and alignment make layouts feel organised.</li>
<li>A fixed spacing scale and proximity group related things.</li>
<li>White space improves clarity; it is not wasted.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Colour and contrast',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose a simple colour palette for an app and check that text is readable. This matters because colour sets mood, signals meaning and, when badly chosen, makes content impossible to read in bright sunlight or for people with low vision.</p>

<h3>The roles of colour</h3>
<p>Do not pick colours only because you like them. Give each colour a job.</p>
<ul>
<li><strong>Primary colour:</strong> your brand colour, used for main buttons and key highlights.</li>
<li><strong>Neutral colours:</strong> white, greys and near-black for backgrounds, text and borders. These make up most of the screen.</li>
<li><strong>Accent colour:</strong> optional, for a second emphasis.</li>
<li><strong>Feedback colours:</strong> green for success, red for errors or danger, amber for warnings, blue for information.</li>
</ul>
<p>A useful guide is the 60-30-10 idea: about 60 percent neutral background, 30 percent secondary surfaces, and 10 percent strong colour. Too much colour tires the eye.</p>

<h3>Building a palette</h3>
<ol>
<li>Choose one primary colour that fits the purpose. Blue often feels trustworthy, green feels fresh or growth-related, orange feels energetic. Colour meanings vary by culture, so ask your users.</li>
<li>Create a light and a dark version of it for hover, pressed and background uses.</li>
<li>Choose 4 to 6 neutrals from near-white to near-black.</li>
<li>Add the four feedback colours.</li>
<li>Write each colour with a name and its hex code, for example <code>Primary 600 = #1B5FA8</code>.</li>
</ol>
<p>Free online tools can suggest harmonious palettes, but always check your choices against real content.</p>

<h3>Contrast and readability</h3>
<p><strong>Contrast ratio</strong> measures the difference in brightness between text and its background, from 1:1 (no contrast) to 21:1 (black on white). The Web Content Accessibility Guidelines (WCAG) set minimum levels at the AA standard:</p>
<table>
<thead><tr><th>Content</th><th>Minimum contrast ratio</th></tr></thead>
<tbody>
<tr><td>Normal text</td><td>4.5 to 1</td></tr>
<tr><td>Large text (about 24 px, or 19 px bold, and above)</td><td>3 to 1</td></tr>
<tr><td>Icons and input borders needed to understand the interface</td><td>3 to 1</td></tr>
</tbody>
</table>
<p>Light grey text on white looks elegant on a designer's bright screen but may be unreadable on a cheap phone outdoors. Many free contrast checkers let you paste two hex codes and show the ratio, and many design tools have a contrast plug-in.</p>

<h3>Never use colour alone</h3>
<p>About 1 in 12 men has some form of colour vision deficiency, and many people use screens in strong light. If an error is shown only by a red border, some users will miss it. Add an icon and a message: "Phone number must have 10 digits". The same applies to charts: use labels or patterns as well as colour.</p>

<h3>Try it yourself</h3>
<ol>
<li>Choose a primary colour for your project and write its hex code.</li>
<li>Add five neutrals and four feedback colours with names.</li>
<li>Check the contrast of your main button text and your body text against their backgrounds with a free contrast checker.</li>
<li>Adjust any colour that fails the 4.5 to 1 rule for normal text.</li>
<li>Look at your screen at low brightness and outdoors if you can.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Give each colour a role: primary, neutral, accent or feedback.</li>
<li>Use mostly neutrals and a little strong colour.</li>
<li>Aim for at least 4.5 to 1 contrast for normal text.</li>
<li>Never rely on colour alone to communicate meaning.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Typography and icons',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose and combine fonts, set readable sizes, and use icons that people understand. This matters because nearly everything in an interface is text, so type decisions affect almost every user in every second.</p>

<h3>Choosing a typeface</h3>
<p>A <strong>typeface</strong> is a family of letter designs (such as Helvetica-like or Georgia-like); a <strong>font</strong> is one style of it (bold, 16 px). For interfaces:</p>
<ul>
<li>Choose a clear, open typeface designed for screens. Plain sans-serif faces are a safe choice for apps.</li>
<li>Use <strong>one family, or at most two</strong>. One for headings and one for body text is plenty.</li>
<li>Check that it supports all the characters you need, including accented letters and the languages of your users.</li>
<li>Use free, openly licensed fonts, and check the licence before using any font commercially.</li>
</ul>

<h3>Sizes, weights and line height</h3>
<p>Set a small <strong>type scale</strong>, a short list of sizes used everywhere. Mobile example:</p>
<table>
<thead><tr><th>Style</th><th>Size</th><th>Weight</th><th>Use</th></tr></thead>
<tbody>
<tr><td>Heading 1</td><td>28 px</td><td>Bold</td><td>Screen title</td></tr>
<tr><td>Heading 2</td><td>22 px</td><td>Semibold</td><td>Section title</td></tr>
<tr><td>Body</td><td>16 px</td><td>Regular</td><td>Paragraphs and labels</td></tr>
<tr><td>Caption</td><td>13 to 14 px</td><td>Regular</td><td>Hints and small notes</td></tr>
</tbody>
</table>
<p>Body text should not go below about 16 px on mobile. <strong>Line height</strong> (the space between lines) of about 1.4 to 1.6 times the font size helps reading. Keep line length to roughly 45 to 75 characters; on a phone this happens naturally.</p>

<h3>Readability rules</h3>
<ul>
<li>Left-align text in left-to-right languages. Avoid justified text, which creates uneven gaps.</li>
<li>Avoid all capitals for long text; use it only for short labels.</li>
<li>Use bold for emphasis, not underline, which looks like a link.</li>
<li>Write short sentences. Design includes the words: this is called UX writing.</li>
<li>Let users enlarge text through their phone settings without the layout breaking.</li>
</ul>

<h3>Icons</h3>
<p>Icons save space and speed up scanning, but only when they are understood.</p>
<ul>
<li>Use well-known symbols: a magnifier for search, a house for home, a bell for notifications, a trash can for delete.</li>
<li>Keep one style: all outline or all filled, with the same stroke width.</li>
<li>Add a text label next to important icons. An icon alone can be ambiguous: a heart might mean favourite, like or health.</li>
<li>Make the tap area at least 44 to 48 px even if the icon looks smaller.</li>
<li>Use free open icon sets with a licence that allows your use.</li>
</ul>

<h3>Common mistakes</h3>
<ul>
<li>Using three or four typefaces, which looks chaotic.</li>
<li>Fancy script fonts for body text.</li>
<li>Tiny grey captions that carry essential information.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Choose one free typeface (or two) and set up a type scale with four styles.</li>
<li>Apply it to one wireframe screen and compare with the default look.</li>
<li>Check that body text is at least 16 px and has 1.5 line height.</li>
<li>Replace each unclear icon with an icon plus label, or remove it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use one or two clear typefaces and a short type scale.</li>
<li>Body text of 16 px or more, with generous line height, reads best.</li>
<li>Left-align, avoid long capitals and keep sentences short.</li>
<li>Use familiar icons in a single style, with labels where meaning could be unclear.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Components and a simple design system',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a small design system: a set of reusable components, colours and text styles. This matters because without one, every screen looks slightly different, work is repeated, and developers must guess your intentions.</p>

<h3>What is a design system?</h3>
<p>A <strong>design system</strong> is a shared collection of rules and parts used to build a product consistently. Big companies have large design systems, but even a one-person project benefits from a small one. It usually has three layers.</p>
<ul>
<li><strong>Styles (foundations):</strong> colours, type scale, spacing scale, corner radius, shadows.</li>
<li><strong>Components:</strong> reusable parts such as buttons, input fields, cards, tabs and alerts.</li>
<li><strong>Patterns and guidance:</strong> how to combine parts, such as "a form has labels above fields and one main button at the bottom", plus notes on when to use each.</li>
</ul>

<h3>Build it in a design tool</h3>
<ol>
<li><strong>Save your styles.</strong> In Figma, select a coloured element and create a <strong>color style</strong> (or variable) from the right panel. Do the same for each text style. Name them clearly, for example <code>Primary/600</code> and <code>Heading/H1</code>.</li>
<li><strong>Design a button.</strong> Draw a rounded rectangle, add text, and turn them into a <strong>component</strong> (shortcut <strong>Ctrl + Alt + K</strong>, Mac <strong>Cmd + Option + K</strong>).</li>
<li><strong>Create variants.</strong> Make versions for Primary, Secondary and Disabled, and for states such as Default, Pressed and Loading. Variants keep them in one tidy set.</li>
<li><strong>Repeat for other parts:</strong> text input (default, focused, error), card, tab bar, alert message.</li>
<li><strong>Use instances.</strong> Place copies (instances) of components in your screens. If you edit the main component, every copy updates.</li>
</ol>

<h3>Why consistency matters</h3>
<p>When a button looks and behaves the same everywhere, users learn once and trust the product. Consistent components also make design faster. A change from rounded to squarer corners becomes one edit, not fifty.</p>

<h3>Naming and documentation</h3>
<p>Write a short one-page guide in the same file or a document: the purpose of each component, when to use it, and rules such as "only one primary button per screen". Name things by purpose (<code>Button/Primary</code>) rather than appearance (<code>Big blue button</code>), because the colour may change later. A design system should be small enough that people actually read it.</p>

<h3>Hand-off to developers</h3>
<p>Developers need exact values: colours in hex, font sizes, spacing, corner radius. Design tools can display them. Share the link to your file and label screens clearly: "Login, default", "Login, error". Talk to developers early, because what looks simple in a drawing may be hard to build, and the reverse.</p>

<h3>Try it yourself</h3>
<ol>
<li>Create a new page called "Design system" in your design file.</li>
<li>Add colour styles and four text styles from the earlier lessons.</li>
<li>Build a button component with three variants.</li>
<li>Build a text field component with default, focused and error states.</li>
<li>Replace the buttons and fields on one of your screens with instances of your components.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A design system collects styles, components and usage guidance.</li>
<li>Variants and instances keep every screen consistent and easy to update.</li>
<li>Name components by purpose and document when to use them.</li>
<li>Good hand-off gives developers exact values and clear screen names.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the "squint test" used for?
- Checking spelling mistakes
* Seeing which elements stand out first in the visual hierarchy
- Measuring page loading speed
- Choosing a font
= Half-closing your eyes blurs details, so only the strongest elements remain visible. They should be the most important ones.

Q: Which principle says items placed close together are seen as related?
- Contrast
- Alignment
* Proximity
- Repetition
= Proximity groups things visually, which is why gaps between sections should be larger than gaps inside a group.

Q: What minimum contrast ratio does WCAG AA require for normal-sized text?
- 1.5 to 1
- 2 to 1
* 4.5 to 1
- 21 to 1
= 4.5 to 1 is the AA minimum for normal text. 3 to 1 applies to large text. 21 to 1 is the maximum, black on white.

Q: A form shows errors only with a red border. What is the problem?
- Red is not allowed
* Some people cannot distinguish the colour, so the error needs an icon and a clear message as well
- The border is too thin to see
- Errors should never be shown
= Do not rely on colour alone. Add text and icons so every user understands.

Q: Which is the best practice for choosing typefaces in an app?
- Use as many as possible for variety
* Use one or two clear typefaces and a short type scale
- Use decorative script for body text
- Use all capitals for paragraphs
= Few typefaces and a consistent scale look professional and read well.

Q: Which are components in a design system? (choose all that apply)
* A primary button with its states
* A text input field
- A photo taken for one campaign
* An alert message
= Components are reusable interface parts. A one-off photo is content, not a component.

Q: Why name a component "Button/Primary" rather than "Big blue button"?
- It is shorter
- Blue is not allowed
* Names should describe purpose, because the appearance may change later
- Design tools only accept slashes
= Purpose-based names stay true even when the colour or size is updated.

Q: What is a good minimum size for body text on a mobile screen?
- 8 px
- 10 px
- 12 px
* 16 px
= About 16 px keeps text comfortable to read on a phone without zooming.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Accessible and mobile-first design',
            'summary' => 'Design for everyone, on small screens, slow networks and in real-life conditions, with forms and words that help.',
            'lessons' => [
                [
                    'title'   => 'Accessibility: design for everyone',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain why accessibility matters and apply practical checks so your designs work for people with different abilities. This matters because disability, injury, tiredness, bright sunlight and a noisy bus all create barriers, and accessible design removes many of them for everybody.</p>

<h3>Who benefits</h3>
<p>Accessibility means people with disabilities can perceive, understand, navigate and interact with your product. Disabilities include blindness and low vision, colour vision deficiency, deafness, difficulty moving hands or using a mouse, and learning or attention differences. There are also temporary and situational limits: a broken arm, a glare on the screen, holding a baby in one arm, or a slow connection. Designing for the edges improves the middle.</p>

<h3>The four WCAG principles</h3>
<p>The Web Content Accessibility Guidelines (WCAG), published by the W3C, are built on four ideas, often remembered as POUR.</p>
<table>
<thead><tr><th>Principle</th><th>Meaning</th><th>Design example</th></tr></thead>
<tbody>
<tr><td>Perceivable</td><td>People can see or hear the content</td><td>Enough contrast, captions on video, text alternatives for images</td></tr>
<tr><td>Operable</td><td>People can use every control</td><td>Large tap targets, keyboard access, no tricky gestures only</td></tr>
<tr><td>Understandable</td><td>Content and behaviour are clear</td><td>Plain words, helpful error messages, predictable layout</td></tr>
<tr><td>Robust</td><td>Works with different devices and assistive technology</td><td>Proper structure so a screen reader can read it</td></tr>
</tbody>
</table>

<h3>A practical checklist for designers</h3>
<ul>
<li><strong>Contrast:</strong> 4.5 to 1 for normal text, 3 to 1 for large text and key icons.</li>
<li><strong>Do not use colour alone</strong> for status, errors or chart series.</li>
<li><strong>Text alternatives:</strong> every meaningful image needs a short description (called alt text). Decorative images need none.</li>
<li><strong>Touch targets:</strong> at least about 44 by 44 px, with space between them.</li>
<li><strong>Headings:</strong> use a clear heading structure so screen reader users can jump between sections.</li>
<li><strong>Focus:</strong> keyboard users must see which item is selected, so never remove the focus outline without replacing it.</li>
<li><strong>Text size:</strong> allow users to enlarge text up to 200 percent.</li>
<li><strong>Motion and sound:</strong> do not autoplay loud audio, and avoid flashing content, which can trigger seizures.</li>
<li><strong>Time:</strong> give people enough time, especially for forms and one-time codes.</li>
<li><strong>Language:</strong> use short, plain sentences. Plain language helps second-language readers too.</li>
</ul>

<h3>Screen readers</h3>
<p>A <strong>screen reader</strong> is software that reads the screen aloud. Phones have one built in: TalkBack on Android and VoiceOver on iPhone. A button with only a drawing of a cross is read as "button" unless it is labelled "Close". That label is a text alternative the designer should specify.</p>

<h3>Writing good alt text</h3>
<ul>
<li>Describe the purpose, not every pixel: "Graph showing school fees collected rising each term" is better than "Graph".</li>
<li>Do not start with "image of". Screen readers already say it is an image.</li>
<li>For an icon button, describe the action: "Search", not "Magnifying glass".</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Turn on TalkBack (Android: <strong>Settings</strong>, <strong>Accessibility</strong>) or VoiceOver (iPhone: <strong>Settings</strong>, <strong>Accessibility</strong>) and try a favourite app for two minutes. Turn it off again by the same route.</li>
<li>Note one thing that is hard or confusing without sight.</li>
<li>Open one of your screens and check contrast, tap size and colour-only signals.</li>
<li>Write alt text for every image and icon button on it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Accessible design helps people with permanent, temporary and situational limits.</li>
<li>WCAG is built on the principles perceivable, operable, understandable and robust.</li>
<li>Contrast, large targets, labels, alt text and clear structure fix many problems.</li>
<li>Test with a screen reader at least once to feel the difference.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Mobile-first and responsive design',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to design for small screens first and adapt the design for larger screens, with the real conditions of your users in mind. This matters because in East Africa most people reach the internet on a phone, often with limited data, small storage and unreliable power.</p>

<h3>Why mobile first</h3>
<p>If you design the big desktop screen first, squeezing it onto a phone later is painful: you must remove things, and you are tempted to hide important ones. When you start with the phone you are forced to ask what truly matters, and then you can add extras for larger screens. This is the <strong>mobile-first</strong> approach.</p>

<h3>Design for real conditions</h3>
<ul>
<li><strong>Thumbs:</strong> most people use a phone with one hand. Keep main actions in the lower half of the screen where the thumb reaches.</li>
<li><strong>Data costs:</strong> avoid heavy images and auto-playing video. Offer a light mode where useful and show how much data a download uses.</li>
<li><strong>Slow or lost networks:</strong> show loading states, keep entered data if the connection drops, and allow retry.</li>
<li><strong>Older and cheaper phones:</strong> small screens (around 360 px wide), less memory, older software. Test on a modest device, not only your own.</li>
<li><strong>Sunlight and glare:</strong> use strong contrast.</li>
<li><strong>Shared phones and languages:</strong> consider shared devices, and offer local languages where your users need them.</li>
<li><strong>Interruptions:</strong> people are walking, chatting or minding a shop. Save progress and make steps short.</li>
</ul>

<h3>Responsive design</h3>
<p><strong>Responsive design</strong> means one design adapts to different screen widths. Designers define <strong>breakpoints</strong>, the widths at which the layout changes. A simple set:</p>
<table>
<thead><tr><th>Screen</th><th>Approx. width</th><th>Typical layout</th></tr></thead>
<tbody>
<tr><td>Phone</td><td>360 to 430 px</td><td>One column, bottom navigation</td></tr>
<tr><td>Tablet</td><td>768 to 1024 px</td><td>Two columns, side navigation possible</td></tr>
<tr><td>Laptop and desktop</td><td>1280 px and up</td><td>Multi-column grid, top navigation, content centred with a maximum width</td></tr>
</tbody>
</table>
<p>In your design tool, duplicate your phone frame, change its width to the tablet size, and rearrange: cards that stacked in one column can sit in two. Use auto layout (in Figma, <strong>Shift + A</strong>) so elements resize and wrap sensibly.</p>

<h3>Progressive enhancement</h3>
<p>Begin with a basic version that works for everyone, then add features for those with better devices and networks. A page of plain text and a simple form should work even without fancy scripts or heavy images. Some projects also support USSD or SMS, which work on basic phones, as a fallback.</p>

<h3>Common mistakes</h3>
<ul>
<li>Testing only on a high-end phone on fast Wi-Fi.</li>
<li>Hover effects that do nothing on touch screens.</li>
<li>Tiny links placed close together.</li>
<li>Huge hero images that cost users money to load.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Take one screen and make sure it works in a 360 px wide frame.</li>
<li>Move the main action to the thumb zone.</li>
<li>Duplicate the frame at tablet width and rearrange the content into two columns.</li>
<li>List three things that might fail on a slow network and design a message for each.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Mobile first means starting with the smallest screen and adding for larger ones.</li>
<li>Consider thumbs, data cost, slow networks, cheap devices and bright light.</li>
<li>Breakpoints change layout as the width grows.</li>
<li>Start with a light, basic experience and enhance it.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Forms, feedback and microcopy',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to design forms that people finish and write short interface text that guides rather than confuses. This matters because forms are where users give you something valuable (a sign-up, a payment, a request) and where most of them give up.</p>

<h3>Principles for good forms</h3>
<ul>
<li><strong>Ask only what you need.</strong> Every extra field reduces completion. Do you really need a date of birth to send a newsletter?</li>
<li><strong>One column.</strong> Single-column forms are quicker to scan, especially on phones.</li>
<li><strong>Labels above fields, always visible.</strong> Placeholder text inside the field disappears as soon as you type and is often low contrast, so do not use it as the only label.</li>
<li><strong>Use the right keyboard.</strong> A phone number field should open the number pad; an email field should show the @ key.</li>
<li><strong>Group related fields</strong> and use steps for long forms, with a progress indicator such as "Step 2 of 4".</li>
<li><strong>Mark optional fields</strong> as "optional" instead of marking required ones with only a star.</li>
<li><strong>Smart defaults.</strong> Prefill what you know, like country or today's date.</li>
</ul>

<h3>Validation and error messages</h3>
<p>Validation checks that an entry is acceptable. Do it gently.</p>
<ol>
<li>Check a field when the user leaves it, not on every keystroke.</li>
<li>Place the message next to the field, in text, with an icon, not only red.</li>
<li>Say what went wrong and how to fix it. Compare "Invalid input" with "Phone number must have 10 digits, for example 0772 123 456".</li>
<li>Keep what the user already typed. Never clear a whole form because of one error.</li>
<li>Move focus to the first error so screen reader users find it.</li>
</ol>

<h3>Feedback and system status</h3>
<p>People need to know what the system is doing. Show a loading indicator when something takes more than a second, a clear success message after saving, and a confirmation before anything dangerous such as deleting. Tell users what happens next: "We have sent a code to your phone. It expires in 5 minutes." When a mobile money payment is pending, say so and tell them what to do if no prompt arrives.</p>

<h3>Microcopy</h3>
<p><strong>Microcopy</strong> is the small text in interfaces: button labels, hints, errors, empty states and confirmations. Good microcopy is clear, short, and sounds like a helpful person.</p>
<table>
<thead><tr><th>Weak</th><th>Better</th><th>Why</th></tr></thead>
<tbody>
<tr><td>Submit</td><td>Send my application</td><td>Says what will happen</td></tr>
<tr><td>Error 402</td><td>Payment failed. Check your balance and try again.</td><td>Explains and guides</td></tr>
<tr><td>No data</td><td>No customers yet. Add your first one to start.</td><td>Turns an empty state into a next step</td></tr>
<tr><td>Are you sure?</td><td>Delete this customer? This cannot be undone.</td><td>States the consequence</td></tr>
</tbody>
</table>
<p>Use the words your users use, avoid blaming ("You entered the wrong code" can become "That code did not match. Try again."), and keep the same tone throughout.</p>

<h3>Try it yourself</h3>
<ol>
<li>Take a form from your project or a sign-up form you know.</li>
<li>Remove any field you do not truly need.</li>
<li>Add visible labels, the right keyboard type and an "optional" mark where needed.</li>
<li>Rewrite three error messages so each says what happened and how to fix it.</li>
<li>Rewrite the button text to say the result of pressing it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Short, single-column forms with visible labels are completed more often.</li>
<li>Good error messages explain the problem and the fix, and keep user input.</li>
<li>Always show system status, confirmations and next steps.</li>
<li>Microcopy is part of the design: write it clearly and kindly.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What do the four WCAG principles (POUR) stand for?
- Planned, Organised, Useful, Responsive
* Perceivable, Operable, Understandable, Robust
- Personal, Open, Universal, Reliable
- Pretty, Obvious, Usable, Rapid
= WCAG organises accessibility around content that is perceivable, operable, understandable and robust.

Q: Which is the best alt text for an icon button that opens a search box?
- Magnifying glass image
- Image of a button
* Search
- Click here
= Alt text for a control describes its action. Search tells a screen reader user what will happen.

Q: Why is mobile-first design useful?
- It makes designers work less
* It forces you to focus on what matters most before adding extras for larger screens
- Desktop screens are no longer used
- It removes the need for testing
= Starting small makes you prioritise content and actions, then expand to larger screens.

Q: Which are good practices for users on limited mobile data? (choose all that apply)
* Avoid auto-playing video
* Keep images light and show loading states
- Use very large hero images on every page
* Save entered data if the connection drops
= Respecting data costs and unreliable networks helps real users. Heavy images work against them.

Q: Where should the main action button usually go on a phone screen for easy one-handed use?
- At the very top corner
* In the lower half of the screen where the thumb reaches
- Hidden in a menu
- In a pop-up that appears after one minute
= The thumb zone is the lower half of most phone screens.

Q: Why is placeholder text inside a field a poor replacement for a label?
- It uses too much data
* It disappears when typing and is often low in contrast
- It cannot contain letters
- It is always read twice by screen readers
= Visible labels remain in view and are easier to read, so placeholders should only add hints.

Q: Which error message is best?
- Invalid input
- Error 402
* Phone number must have 10 digits, for example 0772 123 456
- You made a mistake
= A good message says what is wrong and how to fix it, with an example.

Q: What is microcopy?
- Very small photographs
* The short interface text such as button labels, hints, errors and empty states
- A compressed image format
- A licence for fonts
= Microcopy is the small piece of writing that guides users through an interface.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Prototyping, testing and your portfolio',
            'summary' => 'Make your screens clickable, test them with real people, improve them, and present the whole story as a portfolio case study.',
            'lessons' => [
                [
                    'title'   => 'Building a clickable prototype',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to link your screens into a clickable prototype that feels like a real app. This matters because people react far more honestly to something they can tap than to a drawing or a description, and fixing a prototype costs a tiny fraction of fixing finished software.</p>

<h3>What a prototype is for</h3>
<p>A <strong>prototype</strong> is a simulation of the product, built to test an idea. It need not be perfect or contain real data. It needs just enough to let a person try a task. Prototypes can be low fidelity (paper), medium (clickable wireframes) or high (polished screens with transitions).</p>

<h3>Paper prototype: the quickest test</h3>
<p>Draw each screen on a card. A friend "plays the computer": when the tester taps a button on paper, your friend swaps in the next card. You can test a whole app idea in an hour with nothing but paper. Many serious mistakes are found this way.</p>

<h3>Linking screens in a design tool</h3>
<p>In Figma, the <strong>Prototype</strong> tab in the right panel creates interactions. Other free tools work in a similar way.</p>
<ol>
<li>Make sure each screen is its own frame, named clearly, for example "Home" or "Add debt".</li>
<li>Switch to the <strong>Prototype</strong> tab.</li>
<li>Select a button on a screen. Drag the small circle that appears on its edge to the frame that should open next.</li>
<li>Set the trigger (On click or On tap) and the animation, for example <strong>Smart animate</strong> or <strong>Move in</strong> for a screen sliding in.</li>
<li>Set the <strong>starting frame</strong> with the flow marker and press the <strong>Play</strong> button at the top right to try it.</li>
<li>Test on a phone using the tool's mobile app, or by opening the preview link on the phone's browser.</li>
</ol>

<h3>Interactions that make it feel real</h3>
<ul>
<li><strong>Navigation:</strong> tab bar items go to their screens, and back arrows go back.</li>
<li><strong>Overlays:</strong> pop-ups and menus that open on top of a screen.</li>
<li><strong>States:</strong> a button that changes when pressed, or a field that shows its error state.</li>
<li><strong>Scrolling:</strong> long screens should scroll as they will in real life.</li>
</ul>
<p>Do not try to prototype everything. Build only the flow you want to test, such as "register and add the first customer". Unlinked buttons confuse testers, so tell them in advance: "Some parts do not work yet. If you tap something and nothing happens, just tell me what you expected."</p>

<h3>Good habits</h3>
<ul>
<li>Use realistic content: Ugandan names, amounts in UGX, real place names, real sentences.</li>
<li>Name the flow and keep a version history, so you can compare versions after testing.</li>
<li>Test your own prototype first to catch broken links.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Choose one task from your project, such as adding a customer debt.</li>
<li>Check that you have every screen needed for it, including the confirmation and an error.</li>
<li>Link them using the Prototype tab in your design tool.</li>
<li>Play it, fix any wrong link, then open it on your phone.</li>
<li>Write the task as a short instruction for a tester, for example "Add a debt of 20,000 UGX for Akello".</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Prototypes let people try the idea before it is built.</li>
<li>Paper prototypes are fast and cheap and can be very revealing.</li>
<li>Link frames in the Prototype tab, set triggers and animations, then play.</li>
<li>Prototype only the flow you want to test, with realistic content.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Usability testing and improving',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to plan and run a usability test with five people, record what you see, and decide what to fix first. This matters because you cannot know whether a design works by looking at it yourself. You know too much to see its problems.</p>

<h3>What is usability testing?</h3>
<p>In a <strong>usability test</strong> you ask a real user to try to complete realistic tasks with your prototype while you watch quietly. You are testing the design, not the person. If someone gets lost, the design failed, not the user. Studies by usability researchers have repeatedly found that about five participants uncover most of the major problems, so you can learn a lot cheaply and then run another round after changes.</p>

<h3>Planning the test</h3>
<ol>
<li><strong>Choose 3 to 4 tasks</strong> based on the most important goals, written as a situation, not instructions: "You gave Akello 20,000 UGX of goods on credit. Record that." Do not use the words in the interface such as "Tap Add debt", or you give away the answer.</li>
<li><strong>Recruit 5 people</strong> who resemble your persona. Friends from the right group are acceptable for learning projects, but avoid people who know your design well.</li>
<li><strong>Prepare a short script:</strong> a welcome, an explanation, the tasks, and a few closing questions.</li>
<li><strong>Prepare a place and a device:</strong> a quiet space, the prototype on a phone, and a notebook. Ask permission if you will record.</li>
</ol>

<h3>Running a session</h3>
<ul>
<li>Welcome the person and say: "We are testing the design, not you. There are no wrong answers. Please think aloud, telling me what you are looking at and thinking."</li>
<li>Give one task at a time. Then stay silent. Do not help, hint or defend the design, however tempting.</li>
<li>Watch where they hesitate, tap the wrong thing, or say "hmm". Write exact quotes.</li>
<li>If they are stuck for a long time, ask "What would you do next?" before ending the task.</li>
<li>At the end ask: "What was easiest? What was most confusing? What would you change?" and thank them.</li>
</ul>

<h3>Measure and note</h3>
<table>
<thead><tr><th>Measure</th><th>How</th></tr></thead>
<tbody>
<tr><td>Task success</td><td>Did the person complete it: yes, with difficulty, or no?</td></tr>
<tr><td>Time</td><td>Roughly how long did it take?</td></tr>
<tr><td>Errors</td><td>How many wrong taps or wrong turns?</td></tr>
<tr><td>Satisfaction</td><td>A quick rating from 1 to 5 after each task</td></tr>
</tbody>
</table>

<h3>Turning observations into improvements</h3>
<ol>
<li>Put all problems on a list, noting how many people had each one.</li>
<li>Rate each problem by severity: <strong>critical</strong> (blocks the task), <strong>serious</strong> (causes frustration or errors), <strong>minor</strong> (small annoyance).</li>
<li>Fix critical problems and those that several people hit first.</li>
<li>Write what you will change and why, change the design, and test again with new people.</li>
</ol>
<p>Be careful: one person's taste is not a problem, but when three of five struggle at the same point, it is.</p>

<h3>Try it yourself</h3>
<ol>
<li>Write three task scenarios for your prototype.</li>
<li>Test with at least three people, one at a time, saying nothing to help.</li>
<li>Record success, problems and quotes for each task.</li>
<li>Rate the problems by severity and list your top three fixes.</li>
<li>Make the changes and note what you learned.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>You test the design, not the person; stay quiet and observe.</li>
<li>Five participants reveal most major problems; repeat after changes.</li>
<li>Write tasks as situations without using interface words.</li>
<li>Prioritise fixes by severity and by how many people were affected.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Your portfolio and case study',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to present your design work as a clear case study and plan a simple portfolio. This matters because employers, clients and scholarship panels hire designers on the strength of how they think and what they have made, not on certificates alone.</p>

<h3>What is a portfolio?</h3>
<p>A portfolio is a small collection of your best projects, shown in a way that lets someone understand your skills quickly. For a beginner, two to three well-told projects are better than ten weak ones. A project does not have to be paid work. A redesign of a local service, a school portal or a clinic booking idea, done properly, is a good start.</p>

<h3>The case study structure</h3>
<p>A <strong>case study</strong> tells the story of one project. People are looking for evidence that you can follow a process. A clear structure is:</p>
<ol>
<li><strong>Title and summary.</strong> One or two sentences: what it is, who it is for, and your role. For example: "A mobile app helping small shop owners track customer credit. I led research, design and testing."</li>
<li><strong>The problem.</strong> Who has it, how you know, and why it matters. Use your problem statement.</li>
<li><strong>Research.</strong> Your methods, number of participants, and key insights, with a photo of sticky notes or a persona.</li>
<li><strong>Ideas and structure.</strong> Journey map, site map, user flow and sketches, showing early thinking, including ideas you rejected.</li>
<li><strong>Design.</strong> Wireframes, visual choices, design system highlights, accessibility decisions. Explain why, not only what.</li>
<li><strong>Testing and changes.</strong> What testing showed and what you changed. Before and after screens are powerful.</li>
<li><strong>Result and reflection.</strong> What the final design achieves, what you learned, and what you would do next. Be honest about limits.</li>
</ol>

<h3>Showing your work well</h3>
<ul>
<li>Lead with the final result at the top, then tell the story.</li>
<li>Keep text short and use images of your screens, flows and prototype link.</li>
<li>Be specific about your role, especially for group work. Never claim others' work.</li>
<li>Use numbers where you honestly have them: "4 of 5 testers completed the task unaided after the changes, up from 2 of 5."</li>
<li>Respect privacy: do not publish photos or names of participants without permission, and remove confidential company information.</li>
</ul>

<h3>Where to publish</h3>
<p>Choose something simple that you can maintain: a free portfolio page or website builder, a public folder with a clear document, or presentation slides exported to PDF. Make sure it opens quickly on a phone, because many people will view it there. Add a short "About me" paragraph with a photo, your skills, and a clear way to contact you, such as an email address. Keep your design tool files tidy, since a reviewer may open them.</p>

<h3>Keep growing</h3>
<ul>
<li>Join a design community or a local tech hub to share work and get feedback.</li>
<li>Do a small design challenge each month.</li>
<li>Learn the basics of how developers build, so you design things that can be made.</li>
<li>Update your portfolio when you finish a project, and remove the weakest.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Choose the project you built across this course.</li>
<li>Write a two-sentence summary that names the user, problem and your role.</li>
<li>Collect one image for each stage in the structure above.</li>
<li>Write two or three lines for each stage, explaining your reasons.</li>
<li>Ask someone to read it and say what they understood in one minute.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A small number of well-told projects beats a long list.</li>
<li>A case study follows the story: problem, research, ideas, design, testing, result.</li>
<li>Show your reasoning and be honest about your role and limits.</li>
<li>Publish simply, make it phone-friendly and keep improving it.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is a paper prototype?
- A printed version of the final app
* Hand-drawn screens that a person swaps as the tester "taps", to test ideas cheaply
- A document listing app features
- A type of wireframe tool
= Paper prototypes let you test flows quickly with no software.

Q: In Figma, where do you create links between screens for a clickable prototype?
- The Assets panel
- The Layers panel only
* The Prototype tab in the right panel
- The Export dialog
= The Prototype tab lets you connect elements to frames, set triggers and animations.

Q: Why do usability tests often use about five participants?
- Five is the legal minimum
* A small group finds most major problems, and you can retest after fixing them
- Larger groups are not allowed
- Because five phones are enough
= Small rounds are cheap and fast; repeating them after changes works better than one big test.

Q: Which task description is best for a usability test?
- Tap the Add debt button and enter 20,000
* You gave Akello goods worth 20,000 UGX on credit. Record that.
- Test the home screen
- Find the blue button
= Task scenarios describe a goal without using the interface words, so you can see if users find the way.

Q: During a test, a participant is stuck. What should the facilitator do?
- Immediately show them where to tap
- Explain why the design was made that way
* Stay silent, observe and note it, asking what they would do next only after a long struggle
- End the whole session
= Helping hides the problem. The struggle is the data you came to collect.

Q: Which problem should be fixed first?
- A colour that one person disliked
* A critical issue that stopped 3 of 5 participants from completing the main task
- A minor spelling issue seen by one person
- A new feature requested by nobody
= Prioritise by severity and by how many users were affected.

Q: What should a good portfolio case study include? (choose all that apply)
* The problem and who it affects
* Research and testing evidence
- Only the final polished screens with no explanation
* Your role and honest reflection on what you learned
= A case study shows your thinking and process, not only the final images.

Q: What should you do before publishing photos of test participants in your portfolio?
- Nothing, they agreed to the test
* Ask for their permission, or leave them out
- Add their phone numbers
- Change their names to jokes
= Respect privacy: get consent for any personal information or images.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which statement describes the difference between UX and UI?
- UX is the code and UI is the database
* UX is the whole experience of reaching a goal; UI is the visible and touchable interface
- UX is for websites and UI is for apps
- They mean exactly the same
= UX covers the whole journey and feeling; UI is the screens, controls and visuals within it.

Q: Which is the correct order of the common design process?
- Prototype, test, empathise, define, ideate
* Empathise, define, ideate, prototype, test
- Ideate, test, define, prototype, empathise
- Define, prototype, ideate, empathise, test
= You learn about users first, define the problem, generate ideas, build a prototype and test it, then repeat.

Q: Why is "Would you use an app like this?" a weak interview question?
- It is too short
* People are poor at predicting their own future behaviour; ask about past events instead
- It must be asked in writing
- It has no answer
= Past behaviour is more reliable evidence than promises about the future.

Q: Which belong in a useful persona? (choose all that apply)
* Goals
* Frustrations
- Random facts that do not affect design decisions
* Context such as device and data limits
= Every detail should help a design decision. Random trivia adds noise.

Q: On a journey map, what do the lowest emotional points show?
- The best features
* The pain points that offer the best opportunities for improvement
- The cheapest tasks
- The slowest devices
= Where users feel worst is where better design helps most.

Q: Which problem statement is written correctly?
- Build a dashboard with charts
* Student teachers need a way to find past papers quickly because they waste time searching many groups
- Make the app modern
- Users need more features
= A good statement names the user, need and reason without prescribing the solution.

Q: In an open card sort, who names the groups?
* The participants
- The designer before the session
- The developers
- Nobody names them
= In an open sort participants create and name their own groups; in a closed sort the names are provided.

Q: Which label is best for a link to pay school fees on a school portal?
- Financial settlements
- Fee remittance module
* Pay fees
- Section C
= Use plain words that match what users expect to find.

Q: Why should a user flow include unhappy paths?
- They make diagrams more colourful
* Because errors, cancelled actions and failures happen in real use and must be designed for
- They are required by law
- Because shapes need to be balanced
= Designing only the happy path leaves users stuck when something goes wrong.

Q: Which navigation pattern is usually best for 4 main, frequently used areas on a phone?
- A hidden hamburger menu for all of them
* A bottom tab bar with icons and labels
- A pop-up menu that appears after a delay
- A long list at the top of the home screen
= A visible bottom tab bar is reachable by the thumb and shows where the user is.

Q: What is the main advantage of low-fidelity sketches and wireframes?
- They are the final product
* They allow cheap, fast testing of structure and ideas before investing in visuals
- They need expensive software
- They replace research
= Low-fidelity work is quick to change, so mistakes are cheap.

Q: Which of the following helps visual hierarchy? (choose all that apply)
* Size
* Contrast
- Making every element bold
* White space around an important element
= Hierarchy needs differences. If everything is bold, nothing stands out.

Q: A designer uses gaps of 5, 11, 17 and 23 pixels at random. What is a better approach?
- Use even more variety
* Use a spacing scale such as multiples of 4 or 8 pixels and group related items closer
- Remove all spacing
- Use only pictures
= A consistent scale makes layouts look orderly, and proximity shows relationships.

Q: Which colour combination rule meets WCAG AA for normal text?
- Contrast ratio of at least 2 to 1
- Contrast ratio of at least 3 to 1
* Contrast ratio of at least 4.5 to 1
- Any colours that look nice to the designer
= 4.5 to 1 is the minimum for normal text. 3 to 1 applies to large text.

Q: Why should you not rely on colour alone to show an error?
- Colours are expensive
* Some users cannot distinguish the colour, and glare can hide it, so add text and an icon
- Red is not allowed on phones
- Screen readers dislike colour
= Using text and icons together makes the meaning available to everyone.

Q: Which is a recommended practice for typography in an interface?
- Using five decorative typefaces
* Using one or two clear typefaces with a short type scale and body text of about 16 px or more
- Justifying all text
- Writing paragraphs in capitals
= A consistent, readable type system makes the interface easier to scan and read.

Q: What does a design system gain from using components with variants?
- Slower design work
* Consistency across screens and fast updates, because changing the main component updates every instance
- Larger file sizes only
- No need for developers
= Components and instances keep the design uniform and easy to maintain.

Q: Which is a good alt text for an image of a graph showing school fees collected rising each term?
- Image
- Graph.png
* Graph showing school fees collected rising each term
- Click to enlarge the amazing picture
= Alt text should communicate the meaning of the image concisely.

Q: What are the benefits of designing mobile first? (choose all that apply)
* It forces you to prioritise what matters most
* It matches how many users actually reach the internet
- It means you can skip desktop testing forever
* It makes light, fast experiences more likely
= Mobile first is a way to prioritise. Other screen sizes still need design and checking.

Q: Which form practice reduces abandonment?
- Asking for as much information as possible
* Asking only what you need, using visible labels and a single column
- Using only placeholder text as labels
- Clearing the form when there is an error
= Short, clear forms are completed more often, and keeping typed data respects the user's effort.

Q: Which button label is best for submitting a job application?
- Submit
- OK
* Send my application
- Go
= Specific labels tell the user what will happen when they press the button.

Q: In Figma, what is the quickest way to make a screen element behave as a link to another frame in a prototype?
- Export it as an image
* Use the Prototype tab and drag the connection to the target frame
- Rename the layer to "link"
- Change its colour
= The Prototype tab creates the interaction between a selected element and a frame.

Q: Which is the best way to write a usability test task?
- Click the Pay button
* Describe a realistic situation, such as "You need to pay this month's fees from your phone. Do it now."
- Ask the user to say if they like the colours
- Tell them where each screen is
= Scenarios test whether people can find their own way without hints from interface words.

Q: A testing session shows 3 out of 5 people cannot find the Save button. What does this mean?
- Those users need training
* It is likely a real design problem that should be fixed and retested
- It does not matter because two people succeeded
- The test failed and should be ignored
= When several participants struggle at the same point, the design needs to change.

Q: Which should be included in a good portfolio case study? (choose all that apply)
* The problem and your role
* Research and testing evidence
* Reflection on what you learned
- Private photos of participants without their permission
= Show process, evidence and honesty. Respect participants' privacy.

Q: Why do designers repeat research, design and testing in loops?
- Because they cannot decide
* Because each round of testing reveals new problems and improves the design before it is built
- Because loops are required by software
- To make projects longer
= Iteration is the heart of UX design: learn, improve, test again.
QUIZ,
];
