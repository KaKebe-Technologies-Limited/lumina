<?php
return [
    'slug'        => 'mastering-your-pitch',
    'title'       => 'Mastering Your Pitch: Win Investors, Customers and Partners',
    'category'    => 'business',
    'level'       => 'intermediate',
    'icon'        => 'target',
    'sort'        => 560,
    'summary'     => 'Learn to build and deliver a pitch that wins investors, customers, grant panels and partners: story structure, honest numbers, deck design, delivery, tough questions, online pitching, negotiation basics and follow-up.',
    'description' => '<p>A good idea does not sell itself. Every founder, freelancer and community project leader eventually has to explain what they do, why it matters and what they need, in front of people who are busy and sceptical. A pitch is that explanation, shaped so the listener can say yes. This course teaches you how to build one from scratch and how to deliver it with confidence.</p><p>You will learn who you are pitching to and what each audience cares about, how to prepare a 30-second, a 2-minute and a 10-minute version, and the classic story structure from problem to the ask. You will practise writing sharp problem and solution statements, showing traction and numbers honestly, and asking for money with a clear plan. Then you will design a simple deck, write a one-page summary and an email pitch, and prepare for competitions and grant applications.</p><p>The later modules cover your voice, pace and body language, how to manage nerves, rehearsal methods, answering tough questions, pitching on video calls, the basics of negotiation and term sheets in plain words, follow-up, learning from rejection and a complete pitch day plan. Examples come from East African businesses, and every lesson ends with a practical task.</p>',
    'outcomes'    => [
        'Explain what a pitch is and adapt it to investors, customers, grant panels, judges and partners',
        'Prepare 30-second, 2-minute and 10-minute versions of the same pitch',
        'Structure a pitch from problem to ask, with a sharp problem and solution',
        'Present traction, market size and financial numbers honestly and clearly',
        'Design a simple deck, a one-page summary and a pitch email',
        'Deliver with a steady voice, good pace, purposeful pauses and calm body language',
        'Answer tough questions, bridge back to your message and say "I do not know" well',
        'Pitch online, understand negotiation and term-sheet basics, follow up and learn from rejection',
    ],
    'requirements' => ['Comfortable using a computer or smartphone and writing short documents', 'Business and Entrepreneurship for Startups (startup-entrepreneurship) is helpful but not required', 'A real or imagined business idea to practise with'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Canva: free design tool for slides and one-pagers', 'url' => 'https://www.canva.com/'],
        ['title' => 'Google Slides', 'url' => 'https://slides.google.com/'],
        ['title' => 'Google Meet', 'url' => 'https://meet.google.com/'],
        ['title' => 'Zoom', 'url' => 'https://zoom.us/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: Pitch foundations',
            'summary' => 'Understand what a pitch is, who you pitch to, the three standard lengths, the story structure and how to write a sharp problem and solution.',
            'lessons' => [
                [
                    'title'   => 'What a pitch is and who you are pitching to',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what a pitch is, name the five audiences you are most likely to face, and say what each one wants to hear. This matters because the same business can win one listener and lose another, simply because the founder talked about the wrong things.</p>

<h3>A pitch is a short request, not a speech</h3>
<p>A <strong>pitch</strong> is a short, planned talk that explains an idea and asks the listener for one clear next step. The next step might be money, a purchase, a signed partnership, a prize or a second meeting. If you cannot say what you want the listener to do afterwards, you do not yet have a pitch; you have a description.</p>

<h3>The five audiences</h3>
<table>
<thead><tr><th>Audience</th><th>What they really ask themselves</th><th>What to stress</th></tr></thead>
<tbody>
<tr><td>Investor</td><td>Will this grow big and return my money?</td><td>Market size, traction, team, the ask and the exit</td></tr>
<tr><td>Customer</td><td>Will this solve my problem at a price I can afford?</td><td>Benefit, price, proof, easy next step</td></tr>
<tr><td>Grant panel</td><td>Will this create the social or economic change we fund?</td><td>Impact, beneficiaries, budget discipline, sustainability</td></tr>
<tr><td>Competition judges</td><td>Is this the most convincing idea in the room today?</td><td>Clarity, originality, a memorable story, a good demo</td></tr>
<tr><td>Partner</td><td>What do we gain, and what will it cost us in effort?</td><td>Shared benefit, roles, how it fits their business</td></tr>
</tbody>
</table>

<h3>Worked example: one business, two pitches</h3>
<p>Imagine Sunbeam Power, a company in Kampala that sells solar lamps on pay-as-you-go: families pay a small daily amount by mobile money and the lamp switches off if they do not pay. Here is the same company pitched to a customer and then to an investor.</p>
<pre>To a customer (weak):
"We are a solar company with innovative energy solutions
for the base of the pyramid."

To a customer (strong):
"Do you spend money on paraffin every week? A Sunbeam lamp
costs 1,500 shillings a day through mobile money. After one
year the lamp is yours, the light is brighter, and your
children can read after dark without smoke."</pre>
<pre>To an investor (strong):
"Two out of three homes in our region have no reliable
power. Sunbeam gives them a lamp on daily mobile money
payments. We have 2,400 active customers, 91 percent pay on
time, and we are asking for 180 million shillings to reach
10,000 customers in 12 months."</pre>
<p>The product is identical. The words, numbers and request change completely, because the customer wants a benefit and the investor wants a growth story.</p>

<h3>Case scenario</h3>
<p>Grace runs a farm produce app. She used one pitch for everyone. At a small business fair a buyer from a supermarket listened politely, but when Grace spent four minutes on her "scalable technology platform", the buyer asked, "How many tomatoes can you deliver on Monday?" Grace did not have the answer ready and lost the meeting. <strong>Lesson:</strong> the buyer cared about reliable supply, not technology. Always ask yourself who is listening and what decision they must make.</p>

<blockquote><p><strong>Quick check:</strong> What is the difference between a description and a pitch?</p><p>Answer: A pitch ends with a clear request for a next step, while a description only explains what you do.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A grant panel and an investor listen to you. Which one cares most about measurable social impact?</p><p>Answer: The grant panel, because their purpose is to fund change for beneficiaries rather than to earn a financial return.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write one sentence describing your business or idea.</li>
<li>Under it, write what you would ask of a customer, an investor and a partner (one sentence each).</li>
<li>Choose the audience you will pitch to first and list three things they care about.</li>
<li>Read your sentence aloud to a friend and ask, "What would you do next if you heard this?"</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A pitch is a short talk that ends with a clear request.</li>
<li>Investors, customers, grant panels, judges and partners each need a different emphasis.</li>
<li>The product stays the same; the words, numbers and ask change with the audience.</li>
<li>Always know the decision your listener has to make.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The 30-second, 2-minute and 10-minute pitch',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to prepare three versions of your pitch for three different moments. This matters because opportunities do not announce themselves: you may meet an investor in a taxi queue, get two minutes at a networking event, or be given ten minutes in a boardroom.</p>

<h3>Three lengths, three jobs</h3>
<table>
<thead><tr><th>Version</th><th>Words (spoken)</th><th>Job</th><th>Where you use it</th></tr></thead>
<tbody>
<tr><td>30 seconds</td><td>about 70 to 80</td><td>Make them curious enough to ask a question</td><td>Introductions, queues, events</td></tr>
<tr><td>2 minutes</td><td>about 260 to 300</td><td>Explain problem, solution, proof and ask</td><td>Short meetings, competition rounds</td></tr>
<tr><td>10 minutes</td><td>about 1,300 and slides</td><td>Make the full case and invite discussion</td><td>Investor meetings, grant panels</td></tr>
</tbody>
</table>
<p>Most people speak at about 130 words a minute when they are relaxed. Nervous speakers rush, so plan for fewer words than the maths suggests.</p>

<h3>How to build all three from one core</h3>
<ol>
<li>Write a single <strong>core sentence</strong>: "We help [who] to [do what] by [how]."</li>
<li>Add one fact that proves it works.</li>
<li>Add the ask.</li>
<li>For the 2-minute version, add the problem story, how the product works and the main numbers.</li>
<li>For the 10-minute version, add market, business model, competition, team and plans, and leave time for questions.</li>
</ol>

<h3>Worked example: Dada Pads</h3>
<p>Dada Pads makes washable reusable sanitary pads in Jinja and sells them to schools and mothers. Here are weak and strong 30-second versions.</p>
<pre>Weak:
"Hello, I am Mary. Our company makes pads. We have been
operating for a while and we have many products. We would
love your support."

Strong:
"One in four girls in our district misses school every month
because she cannot afford pads. Dada Pads makes washable pads
that last a year for 12,000 shillings, made and sold by local
women. We have equipped 14 schools. I am looking for one more
school sponsor. May I send you our one-page summary?"</pre>
<p>The strong version has a problem, a solution, a number, proof and an ask in about 70 words. The weak version has none of these.</p>

<h3>The 2-minute spine</h3>
<pre>Hook (10 s): the problem in one human picture
Solution (20 s): what you do, in plain words
Proof (30 s): customers, sales, a result
Market and model (30 s): who pays, how you earn
Team (10 s): why you
Ask (20 s): exactly what you need</pre>

<h3>Case scenario</h3>
<p>Peter met an investor at a conference tea break. He had only a 10-minute pitch memorised, so he started at the beginning of his story and was still describing his childhood when the bell rang. The investor said, "Send me an email," and never replied. A week later Peter wrote a 30-second pitch and used it at the next event. Two listeners asked for meetings. <strong>Lesson:</strong> a short pitch earns the long one.</p>

<blockquote><p><strong>Quick check:</strong> Roughly how many words fit into a 30-second pitch?</p><p>Answer: About 70 to 80, because relaxed speech is about 130 words a minute and you need room to breathe.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is the job of the 30-second pitch?</p><p>Answer: To make the listener curious enough to ask a question, not to explain everything.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write your core sentence in the form "We help ... to ... by ...".</li>
<li>Write a 30-second pitch of about 75 words ending in a question or ask.</li>
<li>Time yourself reading it aloud; adjust until it takes 30 seconds.</li>
<li>Expand it into a 2-minute outline using the spine above.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Prepare three lengths; each has a different job.</li>
<li>Build them from one core sentence, one proof fact and one ask.</li>
<li>Speak fewer words than you think you can; nerves speed you up.</li>
<li>The short pitch earns the right to give the long one.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The story structure: from problem to ask',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to arrange a pitch in the nine-part story order that investors, judges and partners expect. This matters because a listener who gets lost stops listening, and a familiar order lets them find the answers to their own questions quickly.</p>

<h3>The nine parts</h3>
<ol>
<li><strong>Problem:</strong> who suffers, how often, how badly.</li>
<li><strong>Solution:</strong> what you offer and how it removes the pain.</li>
<li><strong>Why now:</strong> what has changed (mobile money, cheaper solar panels, a new rule) that makes this the right moment.</li>
<li><strong>Market:</strong> how many people or businesses have this problem and could pay.</li>
<li><strong>Traction:</strong> proof that it already works.</li>
<li><strong>Business model:</strong> who pays, how much and how you earn.</li>
<li><strong>Competition:</strong> what customers use today and why you are better for them.</li>
<li><strong>Team:</strong> why these people can do it.</li>
<li><strong>The ask:</strong> how much, for what, and what it will achieve.</li>
</ol>
<p>You may reorder a little for the audience. A customer pitch moves traction forward and skips the ask for investment. A grant pitch moves impact to the front. But the thread stays the same: a problem, a solution that works, and a request.</p>

<h3>Why this order works</h3>
<p>Listeners decide in the first minute whether the problem is real. The solution only matters once they feel the problem. "Why now" calms the fear that you are too early or too late. Traction before the ask means the request feels reasonable, not hopeful.</p>

<h3>Worked example: FarmLink</h3>
<p>FarmLink is an app that connects smallholder farmers near Mbarara to hotels and market traders so they sell directly. A condensed script in the nine-part order:</p>
<pre>Problem: Farmers lose up to a third of their tomatoes
because middlemen arrive late and set the price.
Solution: FarmLink lets a farmer post what is ready; buyers
order on the app and pay by mobile money on delivery.
Why now: Smartphones and mobile money are now in most
villages, and hotels want fresher produce.
Market: 40,000 farming households in our region and
300 hotels and restaurants.
Traction: 600 farmers, 45 buyers, 38 million shillings of
produce sold in six months.
Model: we take 6 percent of every sale.
Competition: middlemen. They are fast but unfair; we are
transparent and pay farmers within an hour.
Team: an agronomist, an app developer and a former hotel
purchaser.
Ask: 120 million shillings to add three districts and four
collection centres in 12 months.</pre>

<h3>Common mistakes</h3>
<ul>
<li>Starting with the company history, not the problem.</li>
<li>Skipping "why now", which leaves listeners wondering why nobody did it before.</li>
<li>Putting the team last without saying why they are the right people.</li>
<li>Ending with "thank you" and no ask.</li>
</ul>

<h3>Case scenario</h3>
<p>Joan pitched a school fees app at a competition. She began with her software features, spent six minutes on screens and mentioned the market only at the end. A judge said afterwards, "I never understood who was in pain." Joan reordered her talk to start with a headteacher chasing parents for fees every term. At the next competition she reached the finals. <strong>Lesson:</strong> start with the person in pain, then the product.</p>

<blockquote><p><strong>Quick check:</strong> Which part of the pitch answers "why is this the right moment?"</p><p>Answer: "Why now", because it names the change, such as mobile money, that makes the idea possible today.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why is traction usually placed before the ask?</p><p>Answer: Proof makes the request feel reasonable and lowers the investor's perceived risk.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write the nine headings on a sheet of paper.</li>
<li>Under each, write one sentence about your own business.</li>
<li>Mark any heading where you have no proof yet, and note how you could get some.</li>
<li>Read the nine sentences aloud in order and check that they flow like a story.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Nine parts: problem, solution, why now, market, traction, model, competition, team, ask.</li>
<li>Start with the person in pain, not with your history.</li>
<li>Reorder lightly for the audience but keep the thread.</li>
<li>Always end with a specific ask.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Writing the problem and the solution sharply',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a problem statement that makes a listener nod and a solution statement that a ten-year-old could repeat. This matters because most weak pitches fail in the first thirty seconds, long before the numbers are seen.</p>

<h3>The sharp problem</h3>
<p>A sharp problem names <strong>who</strong> is hurt, <strong>how often</strong>, and <strong>what it costs</strong> them in money, time or opportunity. It uses one real person or one real number, not a general complaint.</p>
<table>
<thead><tr><th>Vague</th><th>Sharp</th></tr></thead>
<tbody>
<tr><td>Many people lack energy access.</td><td>A family in rural Kamuli spends 8,000 shillings a week on paraffin, and the smoke makes the children cough.</td></tr>
<tr><td>Schools struggle with fees.</td><td>A headteacher spends two weeks every term writing receipts by hand and still cannot tell who has paid.</td></tr>
<tr><td>Girls lack hygiene products.</td><td>A girl in Primary 7 stays home five days a month because pads cost a full day of her mother's earnings.</td></tr>
</tbody>
</table>

<h3>A formula for the problem</h3>
<pre>[Person] tries to [goal], but [obstacle],
which costs them [money, time or dignity].</pre>

<h3>The sharp solution</h3>
<p>A sharp solution says what the customer <strong>gets</strong> and <strong>does</strong>, in everyday words. Avoid words such as "innovative", "disruptive", "holistic", "platform" and "ecosystem" unless you explain them with a picture. Use the formula:</p>
<pre>[Product] helps [person] to [outcome] by [simple how],
so they [benefit].</pre>

<h3>Worked example: school fees app</h3>
<pre>Weak:
"We are a fintech platform that leverages a digital
ecosystem to transform school fee collection."

Strong:
"SchoolPay helps head teachers collect fees by mobile money.
A parent gets a message with the amount, pays from their
phone, and the school gets a receipt and an updated list
within a minute. No queues, no lost books, no arguments."</pre>
<p>Notice the strong version uses short sentences, shows the parent's steps and ends with three plain benefits.</p>

<h3>The mum test</h3>
<p>Read your solution to someone outside your field, such as a relative or a neighbour. If they can repeat it in their own words, it is sharp. If they ask, "So what do you actually do?" rewrite it.</p>

<h3>Common mistakes</h3>
<ul>
<li>Describing the product's features instead of the customer's result.</li>
<li>Using a huge, vague problem ("poverty") that no product can fix.</li>
<li>Jargon the audience does not use.</li>
<li>Telling three problems at once. Pick the sharpest.</li>
</ul>

<h3>Case scenario</h3>
<p>David pitched a "smart agricultural analytics platform" to a bank officer. She asked, "Is it for farmers or for us?" David spent five minutes trying to clarify and the meeting ended without a next step. A mentor told him to say, "Farmers send a photo of a sick plant; in an hour they get advice and the nearest shop that sells the right medicine." The next pitch took thirty seconds, and the officer asked for a pilot. <strong>Lesson:</strong> say what happens to a real farmer, not what the technology is called.</p>

<blockquote><p><strong>Quick check:</strong> Which is a sharper problem: "farmers struggle" or "a farmer loses a third of her tomatoes because buyers arrive late"?</p><p>Answer: The second, because it names a person, a loss and a cause.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is the "mum test"?</p><p>Answer: Explaining your solution to someone outside your field; if they can repeat it, your wording is clear.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write your problem in the formula: person, goal, obstacle, cost.</li>
<li>Add one real number or a real person's name (with permission or changed).</li>
<li>Write your solution in the second formula without using any banned jargon word.</li>
<li>Test both on someone outside your field and rewrite until they can repeat them.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A sharp problem names who, how often and the cost.</li>
<li>A sharp solution states the customer's result in everyday words.</li>
<li>Avoid jargon and pick one problem, not three.</li>
<li>Test with a person outside your field.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What best describes a pitch?
- A long document describing your whole business
* A short planned talk that ends with a clear request for a next step
- A general conversation about your hobbies and ideas
- A legal contract between you and an investor
= A pitch is short and purposeful, and it ends with a request such as money, an order or a meeting.

Q: Which audience is most interested in market size, traction and a possible return on money?
- A customer
- A grant panel
* An investor
- A friendly neighbour
= Investors want to see that the business can grow large enough to return their money.

Q: A supermarket buyer asks, "How many tomatoes can you deliver on Monday?" Which earlier mistake most likely caused you to be unprepared?
- Speaking too slowly
- Wearing the wrong clothes
* Pitching technology to a buyer who cares about supply
- Using a deck with too many colours
= The buyer is a customer who cares about reliable supply, so a good pitch would have been ready with quantities and dates.

Q: About how many words fit into a relaxed 30-second pitch?
- 20
- 150
- 300
* 70 to 80
= At about 130 words a minute, 30 seconds holds about 65 to 80 words, leaving room for pauses.

Q: At a conference tea break an investor asks, "So what do you do?" You have time for one breath. Which opening is best?
- "Let me start with how I grew up in Masaka."
* "We help farmers sell tomatoes directly to hotels, and we have already paid out 38 million shillings. May I send you a summary?"
- "I have a platform with many innovative features."
- "It is complicated, can I show you a 40-slide deck?"
= The strong reply is short, has proof, and ends with an ask; the others are slow, vague or too heavy.

Q: Which part of the story structure explains what has changed to make the idea possible now?
- Market
* Why now
- Competition
- Team
= "Why now" names the change, such as cheap solar panels or mobile money, that makes this the right moment.

Q: Which is the sharpest problem statement?
- Many people lack energy.
- Energy is a big challenge in Africa.
* A family in Kamuli spends 8,000 shillings a week on paraffin and the children cough from the smoke.
- Our company solves energy problems.
= The sharp version names a person, a number and a harm, so the listener can feel the problem.

Q: A judge says, "I never understood who was in pain." What is the best fix for the next pitch?
- Add more slides about technical features
* Open with one real person experiencing the problem, then show the product
- Speak faster to fit more in
- Remove the problem and keep only the solution
= Starting with a person in pain makes the problem clear before the solution is introduced.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Proof, numbers and the ask',
            'summary' => 'Show traction honestly, size the market, explain competition and team, and make a clear financial ask with milestones.',
            'lessons' => [
                [
                    'title'   => 'Showing traction and numbers honestly',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose the numbers that prove your business works, present them clearly, and avoid exaggeration that destroys trust. This matters because investors and judges check numbers, and one inflated figure can make them doubt everything else you say.</p>

<h3>What traction means</h3>
<p><strong>Traction</strong> is evidence that real people want what you offer. It is not a plan or a hope. Good traction can be paying customers, repeat purchases, signed letters of intent, a successful pilot, waiting-list sign-ups or a growing monthly revenue. Early businesses with no sales can still show traction: interviews with 50 target customers, a pilot with 3 schools, or a prototype tested by 20 users.</p>

<h3>Choose the numbers that matter</h3>
<table>
<thead><tr><th>Number</th><th>What it proves</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Paying customers</td><td>People will pay</td><td>2,400 active lamp customers</td></tr>
<tr><td>Revenue per month</td><td>The business earns</td><td>14 million shillings in March</td></tr>
<tr><td>Growth rate</td><td>Momentum</td><td>Sales up 20 percent a month for 6 months</td></tr>
<tr><td>Repeat or retention</td><td>People come back</td><td>70 percent of schools reorder</td></tr>
<tr><td>Payment rate</td><td>The model works</td><td>91 percent of daily payments on time</td></tr>
</tbody>
</table>
<p>Pick three or four. A slide with fifteen numbers hides the story.</p>

<h3>Be honest in four ways</h3>
<ol>
<li><strong>Say what the number counts.</strong> "600 registered farmers, 210 active in the last month" is honest. "600 farmers" alone may mislead.</li>
<li><strong>Do not mix periods.</strong> Compare month to month or year to year, and say which.</li>
<li><strong>Separate facts from forecasts.</strong> Label a forecast as a forecast.</li>
<li><strong>Explain bad numbers.</strong> If sales dropped in one month, say why and what you changed.</li>
</ol>

<h3>Worked example: Dada Pads</h3>
<pre>Weak:
"We are growing very fast and everyone loves our product.
We have sold thousands of pads."

Strong:
"In 12 months we sold 3,100 pads to 1,450 girls in 14
schools. 62 percent of buyers reordered within six months.
Absenteeism in our pilot school fell from 5 days a month to
2, measured against the school register. Our weak point is
delivery: 20 percent of orders arrived late, so we hired a
rider in March and late orders are now 6 percent."</pre>
<p>The strong version gives units, periods, a source for the claim and an honest weakness with the fix. This makes the good numbers more believable.</p>

<h3>Case scenario</h3>
<p>Moses told a panel that his app had "10,000 users". A panelist asked how many used it last week. Moses admitted 400. The panel's mood changed at once, and they questioned every other figure. Months later Moses re-pitched with "10,000 downloads, 400 weekly active users, and weekly use is doubling every month". The same investors invited him to a second meeting. <strong>Lesson:</strong> honest, precise numbers create trust; vanity numbers create doubt.</p>

<blockquote><p><strong>Quick check:</strong> Which is more honest: "5,000 users" or "5,000 sign-ups, 600 active this month"?</p><p>Answer: The second, because it explains what the number counts and shows real activity.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You have no sales yet. What can count as traction?</p><p>Answer: Evidence such as customer interviews, a pilot, waiting-list sign-ups or letters of intent, because they show real demand.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List every number you have about your business.</li>
<li>Choose the three that best prove people want it.</li>
<li>Write each as a full sentence with the unit and the period.</li>
<li>Write one honest weakness and what you are doing about it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Traction is evidence of demand, not a plan.</li>
<li>Pick three or four numbers and explain what each counts.</li>
<li>Never mix time periods or present forecasts as facts.</li>
<li>Admitting a weakness with a fix builds trust.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Market, competition and team',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to size a market in a believable way, describe competitors without insulting them, and present your team as the right people for the job. This matters because many founders say "we have no competition" or quote a huge market, and listeners immediately stop believing them.</p>

<h3>Sizing the market the believable way</h3>
<p>Do not say "everyone in Africa is our customer". Count from the bottom up:</p>
<ol>
<li>Decide who your <strong>first customer group</strong> is (for example, primary schools in three districts).</li>
<li>Count how many exist (for example, 1,200 schools).</li>
<li>Multiply by what each pays in a year (for example, 600,000 shillings).</li>
<li>That gives about 720 million shillings a year, the market you can reach first.</li>
</ol>
<p>Then add one sentence about how it can grow: more districts, more products, other countries. A small, well-counted market is more convincing than a giant guess.</p>

<h3>Competition: everyone has some</h3>
<p>Your competitor is whatever customers do today, even if it is nothing. For a solar lamp seller, competitors include paraffin lamps, phone torches and other solar brands. Show a simple table and be fair.</p>
<table>
<thead><tr><th>Option</th><th>Upfront cost</th><th>Weakness</th><th>Our advantage</th></tr></thead>
<tbody>
<tr><td>Paraffin lamp</td><td>Low</td><td>Smoke, fire risk, weekly spending</td><td>Cleaner, cheaper over a year</td></tr>
<tr><td>Cash solar lamp</td><td>High</td><td>Few families can pay at once</td><td>Daily mobile money payments</td></tr>
<tr><td>Other pay-as-you-go brand</td><td>Medium</td><td>Weak after-sales service</td><td>Local agents who visit</td></tr>
</tbody>
</table>

<h3>The team slide</h3>
<p>Investors back people. Show for each person: <strong>name, role, and one proof of why them</strong>. "Aisha, operations, ran 40 delivery riders for a courier company" beats "Aisha, operations, passionate about growth". Include advisers if they bring real help, and be honest about gaps: "We are hiring a finance lead with the first funds."</p>

<h3>Worked example: three weak lines and their repairs</h3>
<pre>Weak: "We have no competition."
Strong: "Families use paraffin today. Among solar sellers,
two others serve our region; neither offers daily
mobile money payments or a visiting agent."

Weak: "The market is 40 million people."
Strong: "We start with 90,000 off-grid households in four
districts. At 365,000 shillings a lamp, that is a
33 billion shilling opportunity we can reach first."

Weak: "Our team is hardworking and passionate."
Strong: "Our founder spent six years in field sales for a
telecom. Our engineer built the payment system now handling
2,400 customers."</pre>

<h3>Case scenario</h3>
<p>Ruth pitched a clothing delivery service and told judges, "We have no competitors." A judge named three delivery companies already active in her town. Ruth had no answer and lost credibility. Later she restated: "Customers use boda riders and shop owners today. We are 30 percent cheaper and give tracking." She won second prize in a later contest. <strong>Lesson:</strong> naming competitors shows you understand the market.</p>

<blockquote><p><strong>Quick check:</strong> What is wrong with "we have no competitors"?</p><p>Answer: Customers always have an alternative, even doing nothing, so the claim suggests you have not studied the market.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What three things should each team member's line contain?</p><p>Answer: A name, a role and one proof of why they are right for it.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Define your first customer group and count how many exist.</li>
<li>Multiply by the yearly spend to estimate your first market.</li>
<li>Draw a small table with three alternatives customers use now.</li>
<li>Write one line per team member with a proof of ability.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Count the market from the bottom up, starting with a reachable group.</li>
<li>Competition includes what customers do today, even doing nothing.</li>
<li>Be fair to competitors and clear about your advantage.</li>
<li>Show team members with proof, and be honest about gaps.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The financial slide and the ask',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain your business model, show simple financial numbers and make an ask that states how much, for what and which milestones it will unlock. This matters because many good presentations end vaguely with "we need funding", and a vague ask gets a vague answer.</p>

<h3>The business model in one sentence</h3>
<p>Say who pays, how much, and how often. "Each family pays 1,500 shillings a day for 365 days; our cost per lamp is 140,000 shillings, leaving 400,000 shillings of gross profit" is clear. A listener should be able to repeat your model to a colleague.</p>

<h3>The simple financial slide</h3>
<p>You do not need a full spreadsheet on one slide. Show a small table with the last year (actual) and the next one or two years (forecast), clearly labelled.</p>
<table>
<thead><tr><th>Item (million shillings)</th><th>Last year (actual)</th><th>Next year (forecast)</th></tr></thead>
<tbody>
<tr><td>Revenue</td><td>96</td><td>320</td></tr>
<tr><td>Cost of lamps and delivery</td><td>58</td><td>176</td></tr>
<tr><td>Running costs (staff, rent)</td><td>41</td><td>90</td></tr>
<tr><td>Profit or loss</td><td>-3</td><td>54</td></tr>
</tbody>
</table>
<p>Be ready to explain every line and the main assumptions, such as the number of new customers per month.</p>

<h3>The ask has three parts</h3>
<ol>
<li><strong>How much:</strong> one number or a tight range.</li>
<li><strong>For what:</strong> a short breakdown of where the money goes.</li>
<li><strong>Milestones:</strong> what you will achieve and by when, so the investor sees the result.</li>
</ol>

<h3>Worked example: Sunbeam Power</h3>
<pre>Weak:
"We are looking for investors to help us grow. Any support
would be appreciated."

Strong:
"We are raising 180 million shillings. 110 million buys
4,000 lamps, 40 million hires and trains 12 field agents,
and 30 million covers app upgrades and working capital.
In 12 months this takes us from 2,400 to 10,000 customers
and to monthly profit by month 9. Our next milestone is
5,000 customers by month 6, when we will report back."</pre>
<p>The strong ask can be tested: the investor can see exactly what the money does and when to check progress.</p>

<h3>What about the valuation?</h3>
<p>Early on, many founders avoid shouting a valuation and instead state the amount and what they are open to: equity, a loan or a grant. If asked, give a reasoned answer, not a random number. Always know what share of the business you are willing to give for the money.</p>

<h3>Case scenario</h3>
<p>Brenda pitched her tailoring training centre for 50 million shillings but could not say what it would buy. An investor asked, "If I gave you 20 million, what changes?" Brenda said, "We would do many things." No offer followed. Later she prepared a plan: 20 million buys 10 machines and trains 60 students, which brings in 2.4 million shillings a month. A community fund agreed to finance the machines. <strong>Lesson:</strong> money needs a mission with a number.</p>

<blockquote><p><strong>Quick check:</strong> Which three parts make a strong ask?</p><p>Answer: How much, what it is for, and the milestones it will reach.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A financial table shows both last year and next year. What must you do to avoid misleading?</p><p>Answer: Label which numbers are actual and which are forecasts, and be ready to explain the assumptions.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write your business model in one sentence: who pays, how much, how often.</li>
<li>Make a small table of revenue, costs and profit for the last year and next year.</li>
<li>Write an ask with an amount, a breakdown and two milestones with dates.</li>
<li>Ask someone to read it and tell you what the money would achieve.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>State the business model in one repeatable sentence.</li>
<li>Show a simple table and label actuals and forecasts.</li>
<li>A good ask gives the amount, the use of funds and the milestones.</li>
<li>Know the share or terms you are willing to offer before you ask.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is traction?
- A detailed plan for the next five years
- A list of your best ideas
* Evidence that real people want what you offer
- The amount of money you hope to raise
= Traction is proof such as customers, revenue, pilots or sign-ups, not a plan or a wish.

Q: Which statement is the most honest way to present your users?
- "We have 10,000 users."
- "Everyone loves our app."
* "10,000 downloads, of which 400 used the app this week."
- "We are the fastest growing app in Africa."
= It explains what the number counts and shows real activity, so listeners can trust the rest.

Q: A panelist asks how many of your 10,000 users were active last week, and the true number is 400. What is the best reply?
- Change the subject quickly
* Give the true number, then explain the growth plan to raise it
- Say that active users do not matter
- Claim you do not have that data
= Honest numbers keep trust; hiding or dodging makes the panel doubt everything else.

Q: Which is the best way to size your first market?
- Say "everyone in Africa is our customer"
- Use the world's population
* Count your first reachable customer group and multiply by yearly spend
- Pick the biggest number you can find online
= A bottom-up count from a reachable group is believable and checkable.

Q: A judge says you have competition, but you said you had none. What should you do?
- Argue that the judge is wrong
* Admit it, name the alternatives customers use, and explain your advantage
- Leave the competition slide out
- Offer to lower your prices
= Naming competitors, including doing nothing, shows that you understand the market.

Q: Which line best shows a team member's value?
- "Aisha is passionate and hardworking."
- "Aisha is our friend from university."
* "Aisha ran 40 delivery riders for a courier company."
- "Aisha will learn on the job."
= A proof from past experience tells investors why this person can do the job.

Q: An investor asks, "If I invest 20 million shillings, what changes in your business?" What is the best answer?
- "Many things will improve."
* "It buys 10 machines and trains 60 students, which adds about 2.4 million shillings a month."
- "We have not planned that yet."
- "Whatever you think is best."
= A concrete use of funds with a measurable result shows planning and discipline.

Q: Which is a complete ask?
- "We need funding."
- "Please support our dream."
* "We are raising 180 million shillings for lamps, agents and working capital, to reach 10,000 customers in 12 months."
- "How much would you give us?"
= It gives the amount, the use and the result with a time frame.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Your pitch materials',
            'summary' => 'Design a clear deck, write a one-page summary and a pitch email, and adapt your pitch for competitions and grant applications.',
            'lessons' => [
                [
                    'title'   => 'Designing a clear pitch deck',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a simple pitch deck of about ten slides that supports your talk instead of replacing it. This matters because a crowded slide makes people read instead of listen, and then they remember neither the slide nor you.</p>

<h3>The deck is your backdrop, not your script</h3>
<p>A <strong>pitch deck</strong> is a set of slides shown while you speak. The slides give the eyes something simple to hold; your voice carries the story. If someone could understand your business by reading the deck alone, with you absent, it probably has too much text. That kind of document is called a "reading deck" and is sent by email, not presented live.</p>

<h3>Rules for a clear slide</h3>
<ul>
<li><strong>One idea per slide.</strong> The slide title should state it as a sentence: "Families spend 8,000 shillings a week on paraffin".</li>
<li><strong>Few words.</strong> A good guide is no more than 20 to 30 words, with a font size of at least 28 points so the back row can read it.</li>
<li><strong>One chart, one message.</strong> Put the main number in large type and label axes in plain words.</li>
<li><strong>Strong contrast.</strong> Dark text on a light background, or light on dark. Avoid text over busy photos.</li>
<li><strong>Two fonts at most</strong> and a small set of colours, the same on every slide.</li>
<li><strong>Real photos</strong> of your customers and product beat stock images, when you have permission to use them.</li>
</ul>

<h3>A ten-slide outline</h3>
<ol>
<li>Title and one-line promise</li>
<li>Problem</li>
<li>Solution (with a photo or screenshot)</li>
<li>Why now</li>
<li>Market</li>
<li>Traction (one chart)</li>
<li>Business model</li>
<li>Competition</li>
<li>Team</li>
<li>The ask and the milestones</li>
</ol>
<p>Keep extra detail in an <strong>appendix</strong> at the end for questions, such as full financials and a product roadmap.</p>

<h3>Free tools</h3>
<p>You do not need paid software. <strong>Google Slides</strong> (https://slides.google.com/) and <strong>Canva</strong> (https://www.canva.com/) both have free plans, with ready-made presentation templates you can edit. Features and free limits change, so check the current options. In either tool, choose a simple template, delete decorative clutter, set your fonts and colours once, and export a PDF as a backup in case the projector or internet fails.</p>

<h3>Worked example: one slide, before and after</h3>
<pre>Before (a crowded problem slide):
"Problem: Many households in rural areas of Uganda do not
have access to affordable, reliable and clean sources of
lighting, leading to health, education and economic issues
that affect the entire community in many ways..."

After:
Title: "Families spend 8,000 shillings a week on paraffin"
Large number: 8,000 UGX per week
One line: "And the smoke makes the children cough."
One photo: a child studying by a paraffin lamp.</pre>
<p>While showing the second slide, the founder says aloud the rest of the story. The slide is easy to see, and the voice adds the detail.</p>

<h3>Case scenario</h3>
<p>Esther's school fees app pitch had 34 slides with paragraphs of text. Judges at a competition started reading and stopped looking at her. She ran over time and never reached her ask. A coach helped her cut the deck to 10 slides with one message each and moved the details to an appendix. At the next event she finished with a minute to spare and won the audience prize. <strong>Lesson:</strong> fewer slides let you finish with your ask.</p>

<blockquote><p><strong>Quick check:</strong> What should the title of a pitch slide do?</p><p>Answer: State the single idea of that slide as a short sentence, so the audience gets the point at a glance.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why export a PDF copy of your deck?</p><p>Answer: It is a backup if the projector, software or internet fails on the day.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open Google Slides or Canva and choose a plain presentation template.</li>
<li>Create the ten slides from the outline, each with a sentence title.</li>
<li>Keep each slide to 30 words or fewer and use at least 28-point text.</li>
<li>Export a PDF and ask a friend to view it for 10 seconds per slide and tell you what each one says.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>The deck supports your talk; it does not replace it.</li>
<li>One idea per slide, large text, strong contrast.</li>
<li>Use about ten slides and keep extra detail in an appendix.</li>
<li>Free tools such as Google Slides and Canva are enough.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The one-page summary and the email pitch',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a one-page summary and a short pitch email that busy people actually read and answer. This matters because many pitches happen on paper or on a phone screen long before you ever stand in a room, and a good email can open the door.</p>

<h3>The one-page summary</h3>
<p>A <strong>one-page summary</strong> (sometimes called a one-pager or executive summary) is a single page that tells the whole story in brief. It is what you leave behind or attach to an email. Use clear headings and short paragraphs:</p>
<ol>
<li><strong>Name and one-line description</strong> at the top.</li>
<li><strong>Problem</strong> in two or three sentences.</li>
<li><strong>Solution</strong> in two or three sentences.</li>
<li><strong>Traction:</strong> three key numbers.</li>
<li><strong>Business model and market</strong> in a few lines.</li>
<li><strong>Team</strong> in one line per person.</li>
<li><strong>The ask</strong> and how funds will be used.</li>
<li><strong>Contact details:</strong> name, phone, email.</li>
</ol>
<p>Write it in a word processor or in Canva, export to PDF and keep the file name professional, for example <code>Sunbeam-Power-one-page-summary.pdf</code>. Keep it under 300 words.</p>

<h3>The pitch email</h3>
<p>A cold email, one sent to someone who does not know you, must earn a reply in under 30 seconds of reading. Use:</p>
<ul>
<li>A <strong>specific subject line</strong> that states the point, such as "Solar lamps on daily mobile money: 2,400 customers, seeking 180M UGX".</li>
<li>A <strong>first sentence</strong> that explains why you are writing to this person.</li>
<li><strong>Three or four short sentences</strong> on problem, solution and proof.</li>
<li>One <strong>clear ask</strong> with an easy step, such as a 15-minute call.</li>
<li>The one-pager attached, and no more than one attachment.</li>
</ul>

<h3>Worked example: weak and strong email</h3>
<pre>Weak:
Subject: Business opportunity
"Dear Sir/Madam, I hope this email finds you well. We are a
dynamic company offering innovative solutions. Please see our
attached company profile and revert."

Strong:
Subject: Girls' pads for 14 schools; can we talk for 15 minutes?
"Dear Ms Nakato, I read that your foundation sponsors
education projects in Busoga. Dada Pads makes washable
pads that cost 12,000 shillings and last a year. In 14 schools,
girls' absence fell from five days a month to two. We would
like a sponsor for 6 more schools (about 9 million shillings).
Could we have a 15-minute call next week? Our one-page summary
is attached. Kind regards, Mary Auma, 07xx xxx xxx"</pre>

<h3>Good habits</h3>
<ul>
<li>Use the person's name and spell it correctly.</li>
<li>Check the email twice for typing errors; they signal carelessness.</li>
<li>Never send mass emails with everyone's address visible.</li>
<li>If there is no reply, send one polite follow-up after about a week.</li>
</ul>

<h3>Case scenario</h3>
<p>Samuel sent the same email, "Please support my startup", to 80 investors using a copied list. He received two automatic replies and no meetings. A friend suggested he research ten funds, personalise each email with one line about why he chose them, and attach a one-page summary. He sent ten emails and received four replies and two calls. <strong>Lesson:</strong> a short, personal, specific email beats a large, generic one.</p>

<blockquote><p><strong>Quick check:</strong> What belongs in the subject line of a pitch email?</p><p>Answer: The specific point of the message, such as what you do, a key result and what you want.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> How long should a one-page summary be?</p><p>Answer: One page, under about 300 words, so it can be read in a minute or two.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write your one-page summary with the eight headings and keep it under 300 words.</li>
<li>Export it as a PDF with a clear file name.</li>
<li>Write a pitch email of under 120 words to a real or imagined contact.</li>
<li>Send it to a friend and ask them to tell you what you want after 30 seconds of reading.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A one-pager tells the whole story in about 300 words.</li>
<li>A pitch email needs a specific subject, a reason for writing and a clear ask.</li>
<li>Personalise every email; avoid mass messages.</li>
<li>Check spelling and send one polite follow-up.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Competitions and grant applications',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to prepare for a pitching competition and write a grant application that scores well. This matters because competitions and grants are among the few ways young founders can win money without giving up ownership, but they are judged by strict rules.</p>

<h3>Read the rules first</h3>
<p>Before you write one slide, read the call carefully and make a checklist. Typical questions: Who is eligible? What is the time limit? What is the scoring? How many slides are allowed? What documents are required, and what is the deadline? Many applications fail simply because they miss a rule. If something is unclear, email the organisers early and keep their reply.</p>

<h3>How competition judges score</h3>
<p>Competitions usually publish criteria. A common set is shown below; check the real one for your event.</p>
<table>
<thead><tr><th>Criterion</th><th>What judges look for</th></tr></thead>
<tbody>
<tr><td>Problem and solution</td><td>A real problem and a clear, believable answer</td></tr>
<tr><td>Innovation</td><td>Something new, or better for local conditions</td></tr>
<tr><td>Traction</td><td>Evidence of real users or sales</td></tr>
<tr><td>Team</td><td>The ability to carry it out</td></tr>
<tr><td>Impact and sustainability</td><td>Benefits that continue after the prize money</td></tr>
<tr><td>Presentation</td><td>Clarity, timing, confidence</td></tr>
</tbody>
</table>
<p>Design your pitch so that each criterion is answered clearly and in the same order. Judges often score quickly, and an easy-to-score pitch is a high-scoring pitch.</p>

<h3>Writing a grant application</h3>
<ol>
<li><strong>Match the funder.</strong> Say in your own words what the fund wants to achieve, and show how your project does it.</li>
<li><strong>Be specific about outcomes.</strong> "Reduce girls' absence from 5 days to 2 days a month in 20 schools" scores higher than "improve girls' education".</li>
<li><strong>Show a realistic budget.</strong> Every line should connect to an activity. Avoid vague "miscellaneous" lines.</li>
<li><strong>Explain sustainability.</strong> Say how the project continues after the grant, for example through sales.</li>
<li><strong>Answer every question</strong> within the word limit, then ask someone to proofread.</li>
</ol>

<h3>Worked example: grant answer, weak and strong</h3>
<pre>Question: "How will your project be sustainable?"

Weak:
"We will continue to work hard and look for more funding."

Strong:
"After the grant ends, schools buy replacement pads from our
local women sellers at 12,000 shillings per girl per year.
Revenue from 3,000 girls covers the cost of 6 women employed
and the delivery rider. The grant pays only for the first
year of training and equipment."</pre>

<h3>Case scenario</h3>
<p>Juma entered a national youth competition with a good farm app but did not read the rules. The limit was five minutes and he spoke for seven; the judges stopped him before his ask. In the next competition he timed his talk to 4 minutes 30 seconds, answered each criterion in order, and received the highest score for clarity. He did not win the main prize, but he got a mentoring offer. <strong>Lesson:</strong> following the rules is part of being judged competent.</p>

<blockquote><p><strong>Quick check:</strong> What is the first thing to do before preparing a competition pitch?</p><p>Answer: Read the rules and scoring criteria, because missing a rule can disqualify you or lose marks.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why do grant panels care about sustainability?</p><p>Answer: They want the benefits to continue after the grant money is spent.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Find a real competition or grant call, or use an imagined one.</li>
<li>Make a checklist of its eligibility rules, time limit, documents and deadline.</li>
<li>Draw a small table linking each scoring criterion to a part of your pitch.</li>
<li>Write a 100-word sustainability answer with numbers.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Read the call and scoring rules before you write anything.</li>
<li>Make each criterion easy for the judges to score.</li>
<li>Grants reward specific outcomes, honest budgets and sustainability.</li>
<li>Stay within time and word limits.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What should the title of a pitch slide state?
* The single idea of that slide as a short sentence
- The name of your company on every slide
- A long description of the whole business
- A decorative phrase with no meaning
= A sentence title tells the audience the point of the slide at a glance.

Q: Which is the best guide for text on a live pitch slide?
- Full paragraphs in a small font so nothing is left out
- As many bullet points as will fit
* About 30 words or fewer in large type
- Only photos with no words at all in any slide
= Short, large text keeps the audience listening to you instead of reading.

Q: Your projector fails ten minutes before the pitch. What preparation helps most?
- Having a second copy of the deck only in your head
* A PDF copy of the deck on a phone or flash drive
- Apologising to the judges
- Skipping the slides and speaking for twice as long
= A backup file lets you present on another device and keep calm.

Q: Which subject line is best for a cold pitch email?
- Hello
- Business opportunity
* Girls' pads for 14 schools: can we talk for 15 minutes?
- URGENT!!! Please read
= It is specific, tells the reader what the email is about and what you want.

Q: You emailed 80 investors with the same message and nobody replied. What is the best change?
- Send the same email again every day
* Research a smaller list and add one personal line to each email
- Make the email twice as long
- Attach five documents
= Personalised, targeted emails show that you chose the reader on purpose.

Q: A competition limit is five minutes. Your practice talk takes seven. What should you do?
- Speak faster on the day
- Hope the judges will be patient
* Cut content until you finish in about four and a half minutes
- Skip the ask to save time
= Staying in the time limit lets you reach the ask and shows that you respect the rules.

Q: A grant form asks how the project will be sustainable. Which answer scores best?
- "We will work hard."
- "We will find more funders later."
* "Schools buy pads from local women sellers, and sales cover the staff and the delivery rider after year one."
- "That is not important for a young project."
= It names a repeatable source of income that continues after the grant.

Q: Why should you match each competition scoring criterion to a part of your pitch?
- It makes the talk longer
* It makes the pitch easy for judges to score fully
- It lets you avoid the weak parts of your business
- It is required by law
= Judges often score quickly, so a pitch that answers each criterion clearly earns more marks.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Delivery and rehearsal',
            'summary' => 'Use your voice, pace, pauses and body language well, manage nerves, and rehearse with proven methods and strict timing.',
            'lessons' => [
                [
                    'title'   => 'Voice, pace and pauses',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to control how loud, how fast and how clearly you speak, and use pauses to make key points land. This matters because listeners judge your confidence by your voice before they judge your idea, and a rushed or mumbled pitch hides good content.</p>

<h3>Four tools of the voice</h3>
<table>
<thead><tr><th>Tool</th><th>What it is</th><th>Practical advice</th></tr></thead>
<tbody>
<tr><td>Volume</td><td>How loud you speak</td><td>Speak to the person at the back of the room; do not shout</td></tr>
<tr><td>Pace</td><td>How fast you speak</td><td>Aim for about 120 to 140 words a minute; slow down for numbers</td></tr>
<tr><td>Pitch</td><td>How high or low the voice is</td><td>Let it rise and fall naturally; a flat voice sounds bored</td></tr>
<tr><td>Pauses</td><td>Planned silence</td><td>Stop for one to two seconds after key lines</td></tr>
</tbody>
</table>

<h3>The power of the pause</h3>
<p>Nervous speakers fear silence and fill it with "um", "like" and "you know". A planned pause feels long to you but sounds confident to the listener. Use it in three places:</p>
<ol>
<li><strong>After your opening hook,</strong> so the problem sinks in.</li>
<li><strong>Before and after a big number,</strong> so people notice it.</li>
<li><strong>Before the ask,</strong> to signal that you are about to say the most important thing.</li>
</ol>

<h3>Worked example: marking a script</h3>
<p>Write your script and mark it with simple signs: a slash for a short pause, two slashes for a long pause, and capital letters for words to stress. Here is the school fees app opening, marked.</p>
<pre>Weak (flat, rushed, no marks):
"We help schools collect fees using mobile money and have
got 40 schools signed up and are looking for investment."

Strong (marked for delivery):
"Every term, a headteacher in our district spends TWO WEEKS
chasing fees. // SchoolPay ends that. / A parent pays from
their phone, / and the school gets a receipt in ONE MINUTE. //
Forty schools already use it. // We are asking for
sixty million shillings / to reach four hundred."</pre>
<p>The strong version is the same content, but pauses and stress guide the listener to the key points: two weeks, one minute, forty schools, sixty million.</p>

<h3>Clear speaking habits</h3>
<ul>
<li>Open your mouth and finish each word, especially the endings.</li>
<li>Breathe from your stomach: place a hand below your ribs and feel it move outwards as you breathe in.</li>
<li>Drop your voice slightly at the end of a statement; do not lift it as if asking a question.</li>
<li>Replace filler words with a pause. Record yourself to count how many you use.</li>
<li>Use your natural accent. Clarity matters more than sounding foreign.</li>
</ul>

<h3>Case scenario</h3>
<p>Harriet had a strong farm marketplace pitch but spoke in a fast whisper because she was nervous. At a competition, the judges at the back could not hear the first minute and missed the problem. Her coach made her record the pitch on her phone. She heard it was both fast and quiet. She practised the marked script, slower, and spoke to the back wall. Her next pitch won her a place in the finals. <strong>Lesson:</strong> record yourself; your ears will tell you what your nerves hide.</p>

<blockquote><p><strong>Quick check:</strong> Where is a pause most useful in a pitch?</p><p>Answer: After the opening hook, around big numbers, and just before the ask, because it gives the point time to land.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What does it sound like when a speaker's voice rises at the end of every sentence?</p><p>Answer: Like asking questions or being unsure, so drop the voice slightly at the end of statements.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Record yourself reading your 30-second pitch on your phone.</li>
<li>Listen and count your filler words and note if you are too fast or quiet.</li>
<li>Mark the script with pauses and stressed words.</li>
<li>Record again and compare the two versions.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use volume, pace, pitch and pauses on purpose.</li>
<li>Aim for about 120 to 140 words a minute and slow down for numbers.</li>
<li>Pause after the hook, around numbers and before the ask.</li>
<li>Record yourself; it is the fastest way to improve.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Eye contact, body language and handling nerves',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to stand, move and look at your audience in a way that shows confidence, and use simple techniques to calm your nerves before and during a pitch. This matters because nearly every speaker feels nervous, and the ones who succeed are not fearless, they simply know how to manage the feeling.</p>

<h3>Body language that helps</h3>
<ul>
<li><strong>Stand steady:</strong> feet about shoulder width apart, weight even, and shoulders relaxed. Do not sway or pace.</li>
<li><strong>Use open hands:</strong> gestures at about waist to chest height, to show size, steps or numbers. Avoid hiding hands in pockets or clasping them tightly.</li>
<li><strong>Face the audience:</strong> glance at your slides, but do not read them with your back turned.</li>
<li><strong>Smile at natural moments,</strong> such as the hello and at good news. Do not keep a fixed smile while describing a serious problem.</li>
<li><strong>Dress neatly</strong> in clothes you are comfortable in, which suit your audience; a clean, simple outfit is enough.</li>
</ul>

<h3>Eye contact</h3>
<p>Look at one person for a whole thought, about three to five seconds, then move to another person in a different part of the room. This is better than scanning quickly, which looks nervous, or staring at one person, which feels uncomfortable. If looking into eyes frightens you, look at the forehead or the space between the eyebrows. On a small panel, share your attention between all the people, including those who say nothing.</p>

<h3>Why nerves happen and what to do</h3>
<p>Your body treats a pitch like a danger, so your heart beats fast, your hands shake and your mouth gets dry. Those signs are energy, and you can use it. Try these methods:</p>
<ol>
<li><strong>Breathe slowly:</strong> breathe in for four counts and out for six, three times, before you go on.</li>
<li><strong>Prepare the first 30 seconds perfectly.</strong> A good start settles the rest.</li>
<li><strong>Arrive early</strong> and stand where you will speak, so the room feels familiar.</li>
<li><strong>Drink water</strong> and avoid too much tea or soda that makes you jittery.</li>
<li><strong>Reframe the feeling:</strong> tell yourself, "I am excited", not "I am afraid".</li>
<li><strong>Focus on the listener:</strong> your job is to help them understand, not to be perfect.</li>
</ol>

<h3>Worked example: a strong opening with posture</h3>
<pre>Weak start:
(looking at the floor, arms crossed, very quiet)
"Um, good morning, I'm, er, John, and, um, today I want
to talk about, I mean we have an app..."

Strong start:
(stand still, breathe, look at the panel, one-two-three)
"Good morning. My name is John Okello. Last term, 3 out of 10
parents in a school near here paid fees late, / and the head
teacher paid teachers from her own pocket. / FeePal fixes
that. Here is how."</pre>

<h3>Case scenario</h3>
<p>Aisha froze at the start of a pitch and forgot her opening line. She stood silent for ten seconds, which felt like an hour. Then she breathed, said, "Let me begin again," smiled and used her prepared first sentence. The judges later remarked on how calmly she recovered. She did not win, but they invited her to present at an investor evening. <strong>Lesson:</strong> a calm recovery impresses more than a perfect start.</p>

<blockquote><p><strong>Quick check:</strong> How long should you look at one person before moving to another?</p><p>Answer: About three to five seconds, long enough to finish a thought and not so long that it feels uncomfortable.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You forget your opening line. What is the best response?</p><p>Answer: Pause, breathe, say "Let me begin again" and continue calmly; recovery shows composure.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Stand in front of a mirror or a friend and deliver your 30-second pitch.</li>
<li>Check your feet, hands and shoulders, and fix one thing at a time.</li>
<li>Practise the slow breathing method three times before your next attempt.</li>
<li>Deliver the pitch to two friends in different parts of the room and share your eye contact.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Stand steady, use open gestures and face the audience.</li>
<li>Hold eye contact for a full thought, then move on.</li>
<li>Nerves are energy; breathe slowly and prepare the opening perfectly.</li>
<li>If you stumble, pause and restart calmly.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Rehearsal methods and timing',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to rehearse in stages, time your talk accurately and learn it without sounding like a robot. This matters because good pitches look easy only because the speaker practised many times, and untimed talks nearly always run over.</p>

<h3>Do not memorise word for word</h3>
<p>Memorising every word makes you sound stiff, and one forgotten word can derail you. Instead, learn the <strong>structure</strong> and the <strong>key lines</strong>. Memorise exactly three things: the opening sentence, the key numbers and the closing ask. For the middle, remember the order of ideas and speak naturally.</p>

<h3>A five-stage rehearsal plan</h3>
<ol>
<li><strong>Write it out</strong> as a script and read it aloud once to check the flow.</li>
<li><strong>Reduce to cue cards.</strong> One card per slide, with only 3 to 5 words, like "8,000 per week" or "38 million sold".</li>
<li><strong>Practise alone,</strong> standing up, with the slides, speaking aloud. Reading silently does not count.</li>
<li><strong>Record on a phone</strong> and watch it for pace, filler words and posture.</li>
<li><strong>Practise on real people:</strong> friends, family or a mentor. Ask them to say what they remember and what they did not understand.</li>
</ol>
<p>Rehearse at least five full times before an important pitch, and more for a 2-minute competition round.</p>

<h3>Timing</h3>
<p>Always time yourself with a phone stopwatch. Make a <strong>timing table</strong> for each slide, then check it while practising:</p>
<table>
<thead><tr><th>Slide</th><th>Target time</th><th>Running total</th></tr></thead>
<tbody>
<tr><td>Problem</td><td>1 min</td><td>1:00</td></tr>
<tr><td>Solution</td><td>1 min</td><td>2:00</td></tr>
<tr><td>Traction</td><td>1 min</td><td>3:00</td></tr>
<tr><td>Market and model</td><td>2 min</td><td>5:00</td></tr>
<tr><td>Competition and team</td><td>2 min</td><td>7:00</td></tr>
<tr><td>The ask</td><td>1 min</td><td>8:00</td></tr>
</tbody>
</table>
<p>Aim to finish at 8 minutes in a 10-minute slot. This leaves room for nerves and a slightly slower pace on the day. Choose one or two <strong>cut points</strong> in advance, such as part of the competition slide, so you can skip them if you are running behind.</p>

<h3>Worked example: cue cards for FarmLink</h3>
<pre>Card 1: "Lose a third of tomatoes" - pause - story of Mama Ruth
Card 2: "Post, order, pay on delivery"
Card 3: "600 farmers / 45 buyers / 38M sold"
Card 4: "6 percent per sale"
Card 5: "ASK: 120M, 3 districts, 12 months"</pre>
<p>Five cards support a whole talk. Compare this to a page of full sentences, which tempts you to read aloud and lose eye contact.</p>

<h3>Case scenario</h3>
<p>Kevin read his entire pitch aloud only once, in his head, the night before. On stage he found it took 14 minutes, though he had 8. The chairperson cut him off during the traction slide, and he never made his ask. For his next pitch he timed five rehearsals, trimmed to 7 minutes 40 seconds and marked a cut point. He finished with the ask on time and received a follow-up call. <strong>Lesson:</strong> timing by a stopwatch, out loud, is not optional.</p>

<blockquote><p><strong>Quick check:</strong> What three things should you memorise exactly?</p><p>Answer: The opening sentence, the key numbers and the closing ask; the rest you can say naturally.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why finish your rehearsal at 8 minutes for a 10-minute slot?</p><p>Answer: Nerves and interruptions usually add time, so leaving a buffer lets you still reach your ask.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write cue cards with 3 to 5 words for each slide of your deck.</li>
<li>Deliver your pitch standing up and time it with a stopwatch.</li>
<li>Make a timing table and mark one cut point.</li>
<li>Present to two people and ask what they remember most.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Learn the structure and key lines, not every word.</li>
<li>Rehearse aloud, standing, at least five times.</li>
<li>Time every practice and aim to finish early.</li>
<li>Plan a cut point in case you are running over.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: About how fast should you speak when pitching?
- 200 to 250 words a minute
* 120 to 140 words a minute
- 60 words a minute
- As fast as possible to fit more in
= A relaxed pace of about 120 to 140 words a minute is clear and easy to follow, and you can slow down for key numbers.

Q: Where are planned pauses most useful?
- Only at the very end of the talk
- Between every word
* After the hook, around big numbers and before the ask
- Never; silence looks unprepared
= A pause lets the important points sink in and shows confidence.

Q: You notice you say "um" and "you know" many times. What is the best fix?
- Speak faster so there are fewer gaps
* Replace the filler words with a short pause and practise with a recording
- Use more complicated vocabulary
- Look at the floor while speaking
= Silence sounds confident, and recording yourself shows you how often the habit occurs.

Q: Which is the best way to use eye contact on a panel of four people?
- Stare at the chairperson only
- Look at the slides all the time
* Hold each person's eyes for a whole thought, then move to another
- Scan the room quickly without stopping
= Holding for three to five seconds feels natural and includes everybody.

Q: You walk on stage and forget your opening line. What do you do?
- Run off the stage
* Pause, breathe, say "Let me begin again" and use your prepared opening
- Pretend nothing happened and mumble
- Read the whole deck aloud
= A calm recovery shows composure and often impresses judges more than a flawless start.

Q: Which technique helps calm nerves just before you speak?
* Breathe in for four counts and out for six, a few times
- Drink several cups of strong coffee
- Skip rehearsal to avoid thinking about it
- Speak as quickly as possible to finish early
= Slow breathing slows the heart rate and steadies the voice.

Q: What is the best approach to learning a 10-minute pitch?
- Memorise every word of the script
- Read it silently the night before
* Learn the structure, memorise the opening, key numbers and ask, and rehearse aloud with cue cards
- Improvise without any practice
= Structure and key lines let you sound natural and still be safe if you lose your place.

Q: Your practice talk takes 12 minutes but you have a 10-minute slot. What should you do?
- Talk faster on the day
- Hope the chairperson gives extra time
* Cut content, plan a cut point and aim to finish at about 8 minutes
- Remove the ask to save time
= Cutting early and leaving a buffer means you reach your ask even if nerves slow you down.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Questions, online pitching and negotiation',
            'summary' => 'Handle tough questions and objections, bridge back to your message, say "I do not know" well, pitch on video calls and understand negotiation and term-sheet basics.',
            'lessons' => [
                [
                    'title'   => 'Preparing for Q&A and tough questions',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to predict the questions you will be asked, prepare short strong answers and respond to doubts without becoming defensive. This matters because the question session is often where an investor or panel decides, and it shows how well you really know your business.</p>

<h3>A question is interest, not attack</h3>
<p>When an investor asks a hard question, they are usually testing whether the business can survive real life. Silence from an audience is worse than questions. Treat each question as a chance to prove you have thought deeply.</p>

<h3>Build a question bank</h3>
<p>Write the 15 hardest questions you could be asked and prepare an answer of 20 to 40 seconds for each. Typical categories:</p>
<table>
<thead><tr><th>Category</th><th>Example questions</th></tr></thead>
<tbody>
<tr><td>Market</td><td>How do you know customers will pay? Why is the market not already served?</td></tr>
<tr><td>Competition</td><td>What stops a bigger company copying you?</td></tr>
<tr><td>Money</td><td>How did you calculate this cost? When do you break even?</td></tr>
<tr><td>Risk</td><td>What happens if customers stop paying? What is your biggest risk?</td></tr>
<tr><td>Team</td><td>Why are you the right people? What will you do when a key person leaves?</td></tr>
<tr><td>Ask</td><td>Why this amount? What happens if we give less?</td></tr>
</tbody>
</table>

<h3>A simple answer shape</h3>
<ol>
<li><strong>Listen fully</strong> and do not interrupt. Nod.</li>
<li><strong>Pause</strong> for one breath. It shows you are thinking.</li>
<li><strong>Repeat or rephrase</strong> the question if it was long or if others may not have heard it.</li>
<li><strong>Answer directly</strong> in the first sentence, then give one proof or example.</li>
<li><strong>Stop.</strong> Do not keep talking to fill silence.</li>
</ol>

<h3>Worked example: handling an objection</h3>
<p>An investor says to the girls' reusable pad maker, "Washable pads sound good but parents will not pay 12,000 shillings when disposable pads are cheap per packet."</p>
<pre>Weak reply (defensive):
"No, you are wrong. Parents love our product. It is much
better than disposable ones."

Strong reply:
"That is a fair concern. A packet of disposable pads costs
about 3,000 shillings a month, which is 36,000 a year. Our
pad lasts a year for 12,000, so a family saves 24,000. In
our pilot, 62 percent of buyers reordered. To make it easier
we also offer payment in two instalments by mobile money."</pre>
<p>The strong reply accepts the concern, answers with numbers and proves with traction. It never attacks the questioner.</p>

<h3>Common mistakes</h3>
<ul>
<li>Arguing or sounding offended.</li>
<li>Giving a five-minute answer to a one-sentence question.</li>
<li>Answering a different question than the one asked.</li>
<li>Having no answer for the obvious "what is your biggest risk?"</li>
</ul>

<h3>Case scenario</h3>
<p>Daniel was asked, "What stops a bigger company copying you?" He answered, "Nobody can copy us, we are the best." The investor looked unconvinced and moved on. A better answer was available: "Our advantage is our network of 40 trained local agents and 3 years of repayment data. A bigger company can copy the product but not that data or those relationships quickly." The investor did not fund Daniel then, but later invited the better-prepared team of a competing founder. <strong>Lesson:</strong> back every claim with a reason.</p>

<blockquote><p><strong>Quick check:</strong> Why pause briefly before answering a hard question?</p><p>Answer: It shows you are thinking, helps you organise your answer and keeps you calm.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What should your first sentence of an answer be?</p><p>Answer: The direct answer to the question, followed by a proof or example.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write the 15 hardest questions about your business.</li>
<li>Write a 30-second answer to the five scariest ones.</li>
<li>Ask a friend to act as an investor and ask you the questions in random order.</li>
<li>Record your answers and check for directness and length.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Tough questions show interest and test your depth.</li>
<li>Prepare a bank of 15 questions and short answers.</li>
<li>Pause, answer directly, add a proof and stop.</li>
<li>Do not argue; accept the concern and respond with facts.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Bridging, objections and saying "I do not know" well',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to steer a conversation back to your key message, respond to objections politely, and admit what you do not know without losing respect. This matters because nobody has every answer, and the way you handle the gaps often earns more trust than the answers themselves.</p>

<h3>Bridging</h3>
<p><strong>Bridging</strong> means answering a question briefly and then moving to a point you want the listener to remember. It is useful when a question is off-topic, hostile or too narrow. Use a bridge phrase such as:</p>
<ul>
<li>"What matters most here is..."</li>
<li>"That connects to something important..."</li>
<li>"The real question for families is..."</li>
</ul>
<p>The method has three steps: <strong>acknowledge</strong> the question, <strong>bridge</strong> with a phrase, then <strong>deliver</strong> your key point. Bridging is not dodging. You must still give a true answer; you simply choose where to end up.</p>

<h3>Worked example: a hostile question</h3>
<p>A judge says to the solar lamp founder, "Isn't pay-as-you-go just a way to charge poor people more?"</p>
<pre>Weak (defensive or dodging):
"No. Next question please."

Weak (rambling):
"Well, it depends, and there are many models, and some
people think this, but, um, in Africa you know..."

Strong (acknowledge, bridge, deliver):
"It is a fair question. The total price is higher than a
cash lamp because we carry the risk and the service. / What
matters is what families can afford. Most cannot find 140,000
shillings at once, so they keep buying paraffin. With us they
pay 1,500 a day, own the lamp in a year, and have already
saved on paraffin. Our customers choose us for that."</pre>

<h3>Responding to objections</h3>
<ol>
<li><strong>Welcome it:</strong> "That is a fair point."</li>
<li><strong>Clarify:</strong> "Do you mean the price, or the delivery?" Many objections are different from what they first sound like.</li>
<li><strong>Answer with evidence:</strong> a number, a customer story or a plan.</li>
<li><strong>Check:</strong> "Does that address your concern?"</li>
</ol>

<h3>Saying "I do not know" well</h3>
<p>If you do not know an answer, do not bluff. Experienced investors can smell it. Use this three-part reply:</p>
<pre>"I do not have that number to hand, so I will not guess.
/ What I do know is [related fact]. / I will find the
exact figure and email it to you by Friday."</pre>
<p>Then do it. Keeping that promise on time is a small proof that you are reliable. If you really should have known (for example, your own monthly revenue), accept responsibility and fix it: "I should have that ready. I will send it today."</p>

<h3>Case scenario</h3>
<p>Naomi was asked what her customer acquisition cost was. She said, "About 5,000 shillings," a guess. The investor asked how she calculated it and she could not explain. The meeting cooled. At a later pitch, she was asked a different question she could not answer, and said, "I do not know yet; we have only tracked it for two months. Early data suggests 6,500, and I will send you the spreadsheet." That investor replied with a meeting request. <strong>Lesson:</strong> a calm "I do not know, and here is when I will find out" beats a confident wrong answer.</p>

<blockquote><p><strong>Quick check:</strong> What are the three steps of bridging?</p><p>Answer: Acknowledge the question, use a bridge phrase, then deliver your key point.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What should you do after promising to send an answer later?</p><p>Answer: Send it on time, because keeping the promise builds trust.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write the three most uncomfortable questions about your business.</li>
<li>Write an acknowledge, bridge and deliver answer for each.</li>
<li>Write your own "I do not know" sentence using the three parts.</li>
<li>Practise all of them aloud with a partner who interrupts a little.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Bridging means acknowledging, linking and delivering your key point.</li>
<li>Treat objections as chances: clarify, answer with evidence and check.</li>
<li>Never bluff; say what you know, and promise a time to follow up.</li>
<li>Always keep the promises you make.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Pitching online on video calls',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to set up your space and your technology for an online pitch, and adapt your style to a camera. This matters because many investors, grant panels and international partners now meet founders on Google Meet or Zoom, and technical problems can ruin a strong pitch.</p>

<h3>Prepare the technology</h3>
<ul>
<li><strong>Test early.</strong> Join the call link ten minutes before the start. Both Google Meet (https://meet.google.com/) and Zoom (https://zoom.us/) run in a browser or an app; learn which one the organiser uses and how to share your screen.</li>
<li><strong>Check your internet.</strong> If the connection is weak, use a cable or sit close to the router, close other apps and ask others at home to avoid heavy downloads. Have a mobile data hotspot as a backup.</li>
<li><strong>Charge your device</strong> and plug in the charger.</li>
<li><strong>Use a headset or earphones with a microphone</strong> if you can; clear sound matters more than a clear picture.</li>
<li><strong>Close other tabs and silence notifications</strong> so private messages do not appear when you share your screen.</li>
<li><strong>Have a backup:</strong> send the deck as a PDF to the organiser in advance, and keep it on your phone.</li>
</ul>

<h3>Prepare the space</h3>
<ol>
<li>Sit facing a window or lamp so light falls on your face, not behind you.</li>
<li>Place the camera at eye level; raise a laptop on a pile of books.</li>
<li>Choose a plain, tidy background, and tell the people in the house that you will be on a call.</li>
<li>Frame yourself from the chest up with a little space above your head.</li>
</ol>

<h3>Adapt your delivery</h3>
<p>Looking at the camera lens, not at the faces on screen, feels like eye contact to the listener. Smile slightly and speak with a bit more energy than normal, because video flattens emotion. Use shorter slides and speak in shorter chunks, with a quick check after each: "Does that make sense so far?" Keep your gestures within the frame. Put sticky notes with key numbers beside the screen.</p>

<h3>Worked example: opening an online pitch</h3>
<pre>Weak:
(microphone muted, back to a bright window, screen shows
private email notification)
"Hello? Can you hear me? Sorry, one moment, my internet..."

Strong:
(joined early, tested sound, headset on, camera at eye level)
"Good morning, everyone, and thank you for joining. I will
share my screen now. If anything is unclear, please unmute
or type in the chat. I will pause for questions at the end
of each section. Let me begin with one number: 8,000 shillings
a week. That is what a family spends on paraffin."</pre>

<h3>Case scenario</h3>
<p>Peninah had a pitch to an international fund on a video call. Her home power went off ten minutes before the call, and her laptop was at 12 percent. She had no backup and joined late from her phone, with a poor picture and without being able to share slides. The fund rescheduled but she had lost the first impression. Later, she kept a charged power bank, a mobile data hotspot and a PDF on her phone, and joined 15 minutes early. <strong>Lesson:</strong> prepare for failures before they happen.</p>

<blockquote><p><strong>Quick check:</strong> Where should you look to appear to make eye contact on a video call?</p><p>Answer: At the camera lens, because that is where the listener sees you looking.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why send your deck as a PDF in advance?</p><p>Answer: It is a backup if screen sharing or your connection fails.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Make a free test call with a friend using Google Meet or Zoom.</li>
<li>Check light, camera height, sound and background.</li>
<li>Share your screen and present three slides.</li>
<li>Ask your friend what was hard to see or hear, then fix it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Join early and test sound, camera and screen sharing.</li>
<li>Light from the front, camera at eye level and a tidy background.</li>
<li>Look at the lens and bring slightly more energy.</li>
<li>Prepare a backup for power, internet and the deck.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Negotiation basics and term-sheet awareness',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to prepare for a negotiation, recognise the main words in a term sheet, and know when to ask for professional advice. This matters because the first offer after a good pitch can feel exciting, and excited founders sometimes agree to things they do not understand. This lesson explains ideas in plain words; it is not legal advice, and any real agreement should be checked by a qualified lawyer.</p>

<h3>Prepare before you negotiate</h3>
<ol>
<li><strong>Know your goal:</strong> what you want, for example an amount and a time frame.</li>
<li><strong>Know your walk-away point:</strong> the worst deal you would accept. If an offer is below it, you decline.</li>
<li><strong>Know your alternatives:</strong> a loan, another investor, a grant, or growing slowly from sales. People with alternatives negotiate with confidence.</li>
<li><strong>Know their interests:</strong> what does the other side really want, such as a stake, a quick return or a social result?</li>
<li><strong>Identify trade-offs:</strong> things cheap for you but valuable to them, like monthly reports.</li>
</ol>

<h3>Negotiation habits that help</h3>
<ul>
<li>Listen more than you speak, and ask "why".</li>
<li>Do not accept or reject on the spot. Say, "Thank you. Let me review it and respond by Thursday."</li>
<li>Keep it respectful. You may work together for years.</li>
<li>Get every agreed point in writing.</li>
</ul>

<h3>What a term sheet is</h3>
<p>A <strong>term sheet</strong> is a short document where an investor lists the main conditions of a proposed investment. It is usually not a final contract. It is the outline from which the final agreements are written. Here are common words in plain language:</p>
<table>
<thead><tr><th>Word</th><th>Plain meaning</th></tr></thead>
<tbody>
<tr><td>Equity</td><td>A share of ownership of your company</td></tr>
<tr><td>Valuation</td><td>The agreed worth of your company when the investment is made</td></tr>
<tr><td>Dilution</td><td>Your percentage shrinks when new shares are issued</td></tr>
<tr><td>Board seat</td><td>A right for the investor to join decisions at top level</td></tr>
<tr><td>Vesting</td><td>Founders earn their shares over time instead of all at once</td></tr>
<tr><td>Liquidation preference</td><td>Who gets paid first if the company is sold or closed</td></tr>
<tr><td>Loan and interest</td><td>Borrowed money you must repay with a charge, with no ownership given</td></tr>
</tbody>
</table>

<h3>Worked example: understanding an offer</h3>
<pre>Founder: "We asked for 180 million shillings."
Investor: "We will give 180 million for 30 percent of the
company, with one board seat."

Simple maths:
180 million for 30 percent means the company is valued at
600 million shillings after the investment (180 / 0.30).
Before the investment it was worth 420 million.
Founder keeps 70 percent, which will shrink with later
investors.

Strong reply:
"Thank you, we are pleased. Can you explain how you reached
that valuation? We were thinking of 20 percent. We would
also like to understand what the board seat decides. Let me
take the sheet to my lawyer and come back on Thursday."</pre>

<h3>Case scenario</h3>
<p>Isaac was thrilled when an investor offered money within a day of his pitch. He signed without reading the page of conditions, which gave the investor 60 percent and the right to replace him as manager. Within a year the investor removed him from the business he had built. He realised too late that he should have asked questions and taken advice. <strong>Lesson:</strong> excitement is not a reason to skip understanding.</p>

<blockquote><p><strong>Quick check:</strong> What is a walk-away point?</p><p>Answer: The worst deal you would accept, so you can say no to anything below it.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> If an investor puts in 100 million for 20 percent, what is the company valued at after the investment?</p><p>Answer: 500 million shillings, because 100 million divided by 0.20 equals 500 million.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write your goal, your walk-away point and two alternatives to this investor.</li>
<li>List what you can offer that is cheap for you but valuable to them.</li>
<li>Use the formula to calculate the valuation if someone offers 50 million for 25 percent.</li>
<li>Write a polite sentence asking for time to review an offer.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Prepare a goal, a walk-away point and alternatives before negotiating.</li>
<li>A term sheet outlines the main terms; learn its plain-language words.</li>
<li>Never sign under pressure; ask questions and take time.</li>
<li>Have a qualified lawyer check any real agreement; this lesson is not legal advice.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the best way to treat a tough question from an investor?
- As an attack to be defended against
* As interest and a chance to show you understand your business
- As a signal to end the meeting
- As something to answer as briefly as possible with "no"
= Hard questions test whether the business can survive real life, and a good answer builds confidence.

Q: Which shape gives the strongest answer to a question?
- A long story, then the answer at the end
* Pause, answer directly in the first sentence, give one proof, then stop
- Argue first and explain later
- Repeat your whole pitch
= A direct answer with proof is clear and respectful of the listener's time.

Q: An investor says, "Parents will not pay 12,000 shillings when disposable pads are cheap per packet." What is the best reply?
- "You are wrong."
- "Our product is simply the best."
* "That is a fair concern. Disposable pads cost about 36,000 shillings a year, ours costs 12,000 and 62 percent of buyers reordered."
- "Let's move to the next slide."
= The strong reply accepts the concern and answers with a comparison and traction.

Q: A judge asks a hostile question about charging poor people more. What is bridging?
- Ignoring the question
* Acknowledging it, using a bridge phrase, and delivering your key point
- Asking the judge to leave
- Giving a very long technical explanation
= Bridging gives a true answer and then steers to what matters most to your message.

Q: An investor asks your customer acquisition cost and you do not know it exactly. What is the best reply?
- Guess a convincing number
- Say it is confidential
* Say you will not guess, share what you do know, and promise to send the figure by a set day
- Change the subject
= Honesty with a follow-up promise keeps trust; bluffing risks being caught.

Q: Which preparation helps most before an online pitch?
- Joining exactly at the start time
* Testing sound, camera and screen sharing early and having a PDF and hotspot backup
- Using a background with a bright window behind you
- Turning off your microphone throughout
= Early tests and backups prevent technical problems from ruining the first impression.

Q: On a video call, where should you look to seem to make eye contact?
- At your own face on screen
* At the camera lens
- At the keyboard
- At the listener's name tile
= The lens is where the viewers see you looking.

Q: An investor offers 100 million shillings for 20 percent of your company. What is the company worth after the investment?
- 100 million shillings
- 200 million shillings
- 400 million shillings
* 500 million shillings
= The value is 100 million divided by 0.20, which is 500 million shillings.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 6
        [
            'title'   => 'Module 6: After the pitch and pitch day',
            'summary' => 'Follow up well, learn from rejection and put everything together in a complete pitch day plan.',
            'lessons' => [
                [
                    'title'   => 'Following up after a pitch',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to send a timely, useful follow-up message and manage a pipeline of conversations. This matters because most deals are not decided in the room; they are decided in the days after, and founders who follow up well are remembered while others disappear.</p>

<h3>The 24-hour thank-you</h3>
<p>Within 24 hours of the pitch, send a short message to each person you met. Keep it to a few lines and include four things:</p>
<ol>
<li><strong>Thanks</strong> for their time, naming one thing they said that you valued.</li>
<li><strong>A one-line reminder</strong> of what you do and what you asked for.</li>
<li><strong>Anything you promised</strong> during the meeting, such as the financial model or a customer reference.</li>
<li><strong>A clear next step</strong> with a date, such as "May I call you on Tuesday?"</li>
</ol>

<h3>Worked example: weak and strong follow-up</h3>
<pre>Weak:
"Hi, just checking if you have made a decision yet?? Please
let me know ASAP."

Strong:
Subject: Thank you, and the numbers you asked for
"Dear Mr Kato, thank you for the time yesterday, and for the
question about delivery costs. As promised, I attach our cost
sheet. In short, we reduced late deliveries from 20 percent
to 6 percent after hiring a rider. FarmLink is raising 120
million shillings to reach three more districts. Could I
call you on Thursday at 10 am to answer any questions?
Kind regards, Grace Atim"</pre>

<h3>A follow-up rhythm</h3>
<table>
<thead><tr><th>When</th><th>Action</th></tr></thead>
<tbody>
<tr><td>Within 24 hours</td><td>Thank-you message with promised items</td></tr>
<tr><td>3 to 5 days</td><td>If no reply, a short polite nudge</td></tr>
<tr><td>2 to 3 weeks</td><td>Share a piece of real news: a new customer, a milestone</td></tr>
<tr><td>Monthly</td><td>A brief update, even if the answer was "not now"</td></tr>
</tbody>
</table>
<p>Do not send daily messages. Silence often means busy, not no. After three polite attempts with no reply, pause and move on.</p>

<h3>Track your conversations</h3>
<p>Keep a simple spreadsheet or notebook with the person, organisation, date, what they said, what you promised and the next step with a date. Free tools such as Google Sheets are enough. Without tracking, promises get lost.</p>

<h3>Case scenario</h3>
<p>Ben pitched to a regional fund and left with a warm "we will think about it". He waited three weeks without writing. When he finally called, the officer had moved on to other applicants and said the budget was used. A different founder, Sarah, sent a thank-you the next day, shared a new customer a fortnight later, and was invited to the second round. <strong>Lesson:</strong> consistent, useful follow-up keeps you in mind without being a nuisance.</p>

<blockquote><p><strong>Quick check:</strong> When should you send the first thank-you message after a pitch?</p><p>Answer: Within 24 hours, while the meeting is still fresh in the listener's mind.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why share news such as a new customer in a later follow-up?</p><p>Answer: It gives a real reason to write and shows momentum without just asking for an answer.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a thank-you message for an imagined investor meeting with the four parts.</li>
<li>Create a tracking table with columns for name, date, promise and next step.</li>
<li>List two pieces of news you could share in two weeks.</li>
<li>Plan a calendar reminder for your follow-up dates.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Send a thank-you within 24 hours with anything you promised.</li>
<li>Follow up on a calm schedule with real news.</li>
<li>Track every promise and next step.</li>
<li>Do not pester; stop after three polite attempts and try again later with news.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Learning from rejection',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to handle a "no" without losing heart, ask for useful feedback and turn each pitch into a better one. This matters because almost every successful founder was rejected many times, and the ones who succeed treat each no as information.</p>

<h3>What a "no" really means</h3>
<p>A "no" can mean many different things. It may mean the idea is weak, but it may also mean the timing is wrong, the investor funds a different sector, the budget is used up, or the pitch was unclear. A single rejection says very little. Ten rejections with the same reason say a lot.</p>

<h3>Ask for feedback politely</h3>
<p>Within a few days, send a short message such as:</p>
<pre>"Thank you for considering Dada Pads. I respect your
decision. If you have two minutes, could you tell me the
main reason it was not the right fit, and one thing I
could improve? It would really help my next pitch."</pre>
<p>Some people will not reply. Those who do often give a valuable sentence that you could not get otherwise. Always thank them, even if you disagree, and never argue.</p>

<h3>Learn systematically</h3>
<ol>
<li><strong>Record each pitch:</strong> the date, the audience, what you asked, the result and the reasons given.</li>
<li><strong>Look for patterns</strong> after five or more pitches. Did people question the market? The team? The price?</li>
<li><strong>Separate fixable from unfixable.</strong> Fix unclear slides. Accept that some investors simply do not invest in your sector.</li>
<li><strong>Change one thing at a time</strong> so you can tell what improved.</li>
<li><strong>Try again,</strong> with new traction if possible. Many funders welcome a stronger second application.</li>
</ol>

<h3>Worked example: a rejection log</h3>
<table>
<thead><tr><th>Date</th><th>Audience</th><th>Result</th><th>Reason given</th><th>Change</th></tr></thead>
<tbody>
<tr><td>3 March</td><td>Angel group</td><td>No</td><td>Market unclear</td><td>Add bottom-up market count</td></tr>
<tr><td>20 March</td><td>Bank</td><td>No</td><td>No collateral</td><td>Apply to grant fund instead</td></tr>
<tr><td>15 April</td><td>Foundation</td><td>Yes</td><td>Strong impact numbers</td><td>Keep and repeat</td></tr>
</tbody>
</table>

<h3>Protect your spirit</h3>
<ul>
<li>Separate yourself from your pitch: a poor pitch is not a poor person.</li>
<li>Talk to a mentor or another founder; they have all been rejected too.</li>
<li>Celebrate small wins, such as a request for a second meeting.</li>
<li>Rest, then review the notes after a day, not at once.</li>
</ul>

<h3>Case scenario</h3>
<p>Lydia's girls' pad business was turned down by six funders in four months. After the fourth, she asked one funder for feedback and heard, "We could not see how you pay for deliveries to remote schools." She had never explained it. She added a delivery plan and a cost line to her deck, and the seventh funder approved a two-year grant. <strong>Lesson:</strong> the rejection that hurt most contained the one sentence she needed.</p>

<blockquote><p><strong>Quick check:</strong> Why ask a funder for feedback after a "no"?</p><p>Answer: It can reveal a fixable weakness that you cannot see yourself, and improve your next pitch.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why change only one thing at a time after a rejection?</p><p>Answer: So you can tell which change made the difference.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a rejection log with columns for date, audience, result, reason and change.</li>
<li>Write a polite feedback request you could send after a "no".</li>
<li>Think of one past disappointment and write what it taught you.</li>
<li>Choose one improvement to make to your pitch this week.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>One "no" says little; patterns say a lot.</li>
<li>Ask for feedback politely and thank everyone.</li>
<li>Keep a log, look for patterns and change one thing at a time.</li>
<li>Protect your confidence; separate yourself from the pitch.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'A complete pitch day plan',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to run the last week, the last day and the last hour before a pitch with a clear plan. This matters because the best preparation can be wasted by a lost file, a late arrival or an empty stomach, and a plan removes those risks.</p>

<h3>One week before</h3>
<ol>
<li>Finish the deck and the one-page summary, and check every number against your records.</li>
<li>Rehearse at least three times, standing, and time each run.</li>
<li>Do two practice sessions with outsiders who ask tough questions.</li>
<li>Confirm the time, place, link, panel members, and how long you have.</li>
<li>Make backups: deck on a flash drive, in email and on your phone.</li>
</ol>

<h3>The day before</h3>
<ul>
<li>Do one light rehearsal, then stop. Over-practising the night before raises stress.</li>
<li>Prepare clothes, charger, cue cards, printed one-pagers and a pen.</li>
<li>Plan transport with extra time for traffic, and know how to reach the place.</li>
<li>Sleep well, and eat a proper dinner.</li>
</ul>

<h3>The morning of the pitch</h3>
<table>
<thead><tr><th>Time before pitch</th><th>Action</th></tr></thead>
<tbody>
<tr><td>3 hours</td><td>Eat a light meal, drink water, review your cue cards once</td></tr>
<tr><td>90 minutes</td><td>Arrive, or for online, set up and test your technology</td></tr>
<tr><td>30 minutes</td><td>Check the room and equipment, load your slides, test the clicker or screen sharing</td></tr>
<tr><td>10 minutes</td><td>Slow breathing, a short walk, say your first lines quietly</td></tr>
<tr><td>2 minutes</td><td>Phone silent, stand tall, smile</td></tr>
</tbody>
</table>

<h3>During the pitch</h3>
<p>Start with your prepared hook. Keep your pace slow and use pauses. Look at each person. Watch the clock. If you run over, use your planned cut point. End on the ask, then stop and say "Thank you, I would be glad to take your questions."</p>

<h3>Worked example: a full 2-minute script for a solar lamp company</h3>
<pre>"Good morning. Mama Esther in Kamuli spends 8,000 shillings
a week on paraffin, and her children cough from the smoke. //
Sunbeam Power gives her a bright solar lamp for 1,500
shillings a day on mobile money. After one year, it is hers.
// Today we serve 2,400 families, 91 percent pay on time, and
we earned 96 million shillings last year. // There are 90,000
off-grid homes in our four districts; that is a 33 billion
shilling opportunity we can reach first. We earn about 400,000
shillings gross profit per lamp. // Our founder spent six years
in field sales and our engineer built our payment system. //
We are raising 180 million shillings for 4,000 lamps, twelve
field agents and working capital. In 12 months, that takes
us to 10,000 customers and to monthly profit by month 9. //
Will you join us? Thank you."</pre>
<p>This script follows the nine-part story, uses real numbers, has pauses marked and ends with the ask. Read it aloud: it takes about two minutes.</p>

<h3>After the pitch</h3>
<p>Write down every question you were asked and how you answered. Send thank-you messages within 24 hours, track your promises, and add the lessons to your rejection and success log.</p>

<h3>Case scenario</h3>
<p>Joseph arrived at a competition ten minutes late because of a traffic jam, with his slides on a phone that was almost flat. He had no printed copy, no flash drive and no breakfast. His hands shook, he skipped half the slides and ran out of time before the ask. A month later he used a pitch day checklist: arrived 90 minutes early, loaded the deck on three devices and ate well. He won the audience award. <strong>Lesson:</strong> preparing for the day is part of preparing the pitch.</p>

<blockquote><p><strong>Quick check:</strong> How should you end the pitch?</p><p>Answer: With a clear ask, followed by a thank-you and an invitation to questions.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why do one light rehearsal the day before rather than several long ones?</p><p>Answer: Too much practice the night before increases stress and tiredness without improving the pitch.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a pitch day checklist with the items for a week before, the day before and the morning of.</li>
<li>List your three backups for your deck and your connection.</li>
<li>Write a countdown timetable for your next real or imagined pitch.</li>
<li>Deliver your 2-minute pitch to two people and ask for one improvement.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Plan the last week, day and hour before the pitch.</li>
<li>Arrive early, test your technology and carry backups.</li>
<li>Start with a hook, watch the time and finish on the ask.</li>
<li>Follow up within 24 hours and log what you learned.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: When should you send the first thank-you message after a pitch?
- After one month
- Only if they ask for it
* Within 24 hours
- Never; waiting looks confident
= A quick message keeps you fresh in the listener's mind and lets you deliver anything you promised.

Q: Which item belongs in a follow-up message?
- A complaint that the investor was slow
* A thank-you, a one-line reminder, anything promised and a dated next step
- Ten new attachments
- A request for the investor's private opinion of your competitors
= These four parts are polite, useful and make the next step easy.

Q: You sent two polite follow-ups and heard nothing. What is the best next move?
- Send a message every day
- Phone the investor's family
* Send a third polite message with real news and then pause until you have another update
- Delete the contact and forget it
= Calm, newsworthy follow-ups show professionalism; endless nagging damages your reputation.

Q: A funder says no. What is the most useful reply?
- "You made a mistake."
* A thank-you with a polite request for one reason and one suggestion to improve
- No reply at all
- A long message explaining why they are wrong
= Respectful feedback requests keep the relationship open and give you information.

Q: After six rejections, you find that four funders asked how you pay for rural delivery. What is the best action?
- Ignore it because it is only opinion
* Add a clear delivery plan and cost line to your pitch
- Stop pitching altogether
- Apply only to funders who never ask questions
= A repeated question is a pattern that points to a fixable gap.

Q: Why change only one thing at a time between pitches?
- To spend less money
* To learn which change made the improvement
- Because the rules require it
- To keep your friends from noticing
= Changing one thing at a time lets you see what works.

Q: What is the best use of the day before your pitch?
- Rehearse for eight hours without rest
- Skip dinner to feel sharp
* Do one light rehearsal, prepare equipment and backups, and sleep well
- Rewrite the whole deck
= Rest and preparation lower stress and protect performance.

Q: You are three minutes from starting. Which action helps most?
- Check social media
* Silence your phone, breathe slowly, stand tall and recall your first line
- Add three more slides
- Drink a large energy drink
= Calm breathing and a clear first line steady the body and the voice.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What is a pitch?
- A long business plan
* A short planned talk that ends with a request for a clear next step
- A casual chat about an idea
- A legal contract
= A pitch is short and purposeful, and asks for a decision or an action.

Q: Which audience is most focused on social impact, beneficiaries and budget discipline?
- Customers
- Competition judges only
* A grant panel
- Suppliers
= Grant panels exist to fund change, so impact and responsible spending come first.

Q: A buyer for a supermarket wants to hear about supply, not technology. This shows that you should
- Always use the same pitch
* Adapt the pitch to what the listener must decide
- Avoid numbers
- Avoid questions
= Different listeners decide on different things, so the emphasis must change.

Q: Roughly how long does a 30-second pitch last in words?
- 20 words
* 70 to 80 words
- 300 words
- 1,300 words
= Relaxed speech is about 130 words a minute, so 30 seconds is about 70 to 80 words with pauses.

Q: Which part of the story structure answers "why is the timing right?"
- Competition
- Team
* Why now
- The ask
= "Why now" names the change, such as mobile money, that makes the idea possible today.

Q: Which problem statement is sharpest?
- Energy is a problem in Africa.
- Many people struggle.
* A family in Kamuli spends 8,000 shillings a week on paraffin and the children cough.
- Our company has solutions.
= It names a person, a number and a harm.

Q: Which statement of a solution is clearest?
- "We leverage a digital ecosystem to transform payments."
* "A parent pays school fees from their phone and the school gets a receipt within a minute."
- "We are an innovative disruptive platform."
- "We are a fintech."
= It shows what happens to real people in everyday words.

Q: Which is the most honest way to present your traction?
- "Thousands love our product."
* "1,450 girls bought pads in 12 months and 62 percent reordered."
- "We are growing very fast."
- "Everyone wants it."
= It states units, periods and a real behaviour.

Q: A panelist discovers that your "10,000 users" are mostly inactive. What is the best path in future pitches?
- Keep the same claim
* Give both numbers: downloads and weekly active users
- Remove all numbers
- Blame the app store
= Honest, precise numbers rebuild and protect trust.

Q: Which method gives a believable market size?
- Claim the whole continent
* Count the first reachable customers and multiply by yearly spend
- Quote the largest report you can find
- Skip the market slide
= A bottom-up count is realistic and easy to check.

Q: A judge says you have competition even though you said none. What do you do?
- Argue
* Accept it and explain what customers do now and why you are better
- Say the judge is mistaken
- Leave the stage
= Naming alternatives, even doing nothing, shows you know your market.

Q: Which is a complete ask?
- "We need help."
- "Please invest."
* "We are raising 180 million shillings for lamps, agents and working capital, to reach 10,000 customers in 12 months."
- "How much do you have?"
= It states how much, what for and what it will achieve.

Q: Which is best practice for a slide title?
- A single word
* A short sentence stating the slide's one idea
- A paragraph
- The name of the company
= A sentence title gives the main point at a glance.

Q: Which text size and amount fits a live pitch slide best?
- Dense paragraphs in small type
- Twenty bullet points
* About 30 words or fewer in large type
- No words and no images
= It keeps the audience listening to the speaker.

Q: What belongs in a strong pitch email subject line?
- "Hello"
- "Opportunity!!!"
* The specific point, such as what you do, a result and the request
- Your whole life story
= A specific subject tells the reader why to open it.

Q: Why should you read a competition call carefully before preparing a pitch?
- Rules are optional
* Missing a rule or criterion can cost marks or disqualify you
- Only the prize matters
- The judges ignore the rules
= Scoring criteria and limits shape what a winning pitch contains.

Q: A grant form asks how the project continues after the grant. Which answer is best?
- "We will do our best."
- "We will look for another grant."
* "Schools buy pads from local women sellers, and sales pay the staff and delivery after year one."
- "That is not our job."
= It shows a repeatable income that outlives the grant.

Q: What is the best way to use pauses?
- Never pause
* After the hook, around key numbers and before the ask
- After every word
- Only at the end
= Planned pauses let important points sink in and sound confident.

Q: How long should you look at one person before moving to another?
- Half a second
- One minute
* About three to five seconds, for a whole thought
- Never look at anyone
= It feels natural and includes everyone.

Q: Which approach helps most with nerves?
- Avoid practising
* Slow breathing and a perfectly prepared opening
- Speak as fast as possible
- Stare at the floor
= Calm breathing and a strong start settle the rest of the talk.

Q: What should you memorise word for word?
- The whole pitch
- Nothing at all
* The opening, the key numbers and the closing ask
- Only the thank-you
= These are the points that matter most and the easiest to lose under stress.

Q: Your rehearsals take 12 minutes and your slot is 10. What do you do?
- Speak faster
* Cut content, mark a cut point and aim for about 8 minutes
- Drop the ask
- Hope for extra time
= A buffer lets you finish with the ask even if nerves slow you.

Q: An investor asks, "What stops a bigger company copying you?" Which reply is best?
- "Nobody can."
* "They can copy the product, but not our 40 trained local agents and three years of repayment data quickly."
- "We do not worry about that."
- "That is a secret."
= It gives a reason and evidence for a lasting advantage.

Q: A hostile question is "Isn't pay-as-you-go just charging poor people more?" What is the best technique?
- Refuse to answer
* Acknowledge, bridge and deliver your key point with facts
- Attack the questioner
- Change the subject permanently
= Bridging answers truthfully and then returns to the message that matters.

Q: You do not know the customer acquisition cost an investor asks about. What is best?
- Make up a number
* Say you will not guess, share what you know and promise a figure by a set day
- Say it does not matter
- End the meeting
= Honesty with a dated promise protects your credibility.

Q: Which setup is best for an online pitch?
- Window behind you, camera low, notifications on
* Light on your face, camera at eye level, headset, notifications off and a PDF backup
- Phone only with a weak signal and no backup
- A busy background
= Good light, sound, framing and backups avoid technical problems.

Q: An investor offers 90 million shillings for 30 percent. What is the company worth after the investment?
- 90 million shillings
- 120 million shillings
* 300 million shillings
- 270 million shillings
= 90 million divided by 0.30 equals 300 million shillings.

Q: After a pitch, an investor emails a term sheet by evening and asks you to sign tonight. What is the best response?
- Sign at once to avoid losing the deal
* Thank them, ask questions, and take it to a qualified lawyer before replying
- Ignore the email
- Change the numbers and sign
= Never sign under pressure; real agreements need understanding and professional advice.

Q: After a rejection, which action teaches you the most?
- Ignoring it
* Asking politely for one reason and recording the pattern across pitches
- Arguing with the funder
- Quitting
= Feedback and patterns show what is fixable.

Q: You arrive for your pitch with a flat phone, no backup deck and no breakfast. Which part of preparation was missing?
- Better slides
* A pitch day plan with backups, early arrival and meals
- A longer script
- More jokes
= A pitch day checklist protects the performance from avoidable problems.
QUIZ,
];
