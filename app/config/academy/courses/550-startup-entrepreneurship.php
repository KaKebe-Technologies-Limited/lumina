<?php
return [
    'slug'        => 'startup-entrepreneurship',
    'title'       => 'Business and Entrepreneurship for Startups',
    'category'    => 'business',
    'level'       => 'beginner',
    'icon'        => 'sprout',
    'sort'        => 550,
    'summary'     => 'Turn an idea into a real small business: test problems with customers, shape an offer, price it, keep simple books, register in Uganda, win first customers, choose funding wisely and follow a 90-day launch plan.',
    'description' => '<p>Many people in Uganda and across East Africa have good ideas for a business, but ideas alone do not pay school fees or rent. What matters is whether real customers will pay, whether the money coming in is more than the money going out, and whether the owner can keep going when things are hard. This course gives you a practical, step-by-step way to find out early and cheaply, before you spend your savings.</p><p>You will start with the entrepreneurial mindset and the different kinds of business, then learn to find real problems and test them by talking to customers. Next come customer groups, market size, the business model canvas, pricing, costs, break-even and cash flow, with worked examples in Uganda shillings from a poultry farm, a phone repair stall, a bakery, an online tailoring shop, a boda-boda delivery startup and a tutoring app.</p><p>The later modules cover simple bookkeeping, separating personal and business money, mobile money, registering a business in general terms, legal and ethical basics, marketing, sales conversations, metrics, teams and co-founders, funding options, risk and pivoting, and a 90-day launch plan. The course gives general education, not legal, tax or investment advice; rules and costs change, so always confirm with the official bodies.</p>',
    'outcomes'    => [
        'Describe the entrepreneurial mindset and compare the main types of business',
        'Find a real customer problem and test it cheaply by talking to customers before spending money',
        'Write a clear value proposition and describe your customers and market size in simple terms',
        'Fill in a business model canvas in plain words and set a sensible price',
        'Calculate unit margin, break-even and monthly cash flow with simple arithmetic',
        'Keep basic books, separate personal and business money and use mobile money safely',
        'Explain in general terms how to register a business in Uganda and where to check current rules',
        'Win first customers, handle sales conversations and track a few metrics that matter',
        'Compare funding options honestly and build a realistic 90-day launch plan',
    ],
    'requirements' => ['No experience needed; a notebook or a phone notes app is enough to do the exercises', 'Basic arithmetic (adding, subtracting, multiplying, dividing and percentages)'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Uganda Registration Services Bureau (URSB)', 'url' => 'https://ursb.go.ug/'],
        ['title' => 'Uganda Revenue Authority (URA)', 'url' => 'https://www.ura.go.ug/'],
        ['title' => 'International Labour Organization (ILO)', 'url' => 'https://www.ilo.org/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: Mindset, problems and ideas',
            'summary' => 'Understand what entrepreneurs do, find real problems, test them cheaply and turn an idea into a clear promise to customers.',
            'lessons' => [
                [
                    'title'   => 'The entrepreneurial mindset and types of businesses',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe what an entrepreneur actually does, name the main kinds of business, and decide which kind suits your situation. This matters because many young people start a business copying a neighbour without asking whether the type of business fits their money, skills and time.</p>

<h3>What is an entrepreneur?</h3>
<p>An <strong>entrepreneur</strong> is a person who notices a need, organises money, people and materials to meet it, and accepts the risk that it may not work. A <strong>business</strong> is simply an activity that sells something people want for more than it costs to provide. You do not need to be a genius. You need curiosity, discipline and the willingness to learn from customers.</p>

<h3>Habits of a useful mindset</h3>
<ul>
<li><strong>Problem focus:</strong> you start from what people struggle with, not from what you would like to sell.</li>
<li><strong>Small tests:</strong> you try cheap experiments before big spending.</li>
<li><strong>Money awareness:</strong> you know what each sale earns and what each month costs.</li>
<li><strong>Persistence with honesty:</strong> you keep going, but you also admit when the numbers say stop or change.</li>
<li><strong>Learning from others:</strong> you ask customers, suppliers and experienced owners for advice.</li>
</ul>

<h3>Types of businesses</h3>
<table>
<thead><tr><th>Type</th><th>What it does</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Trading</td><td>Buys goods and resells them</td><td>A phone accessories stall</td></tr>
<tr><td>Production</td><td>Makes or grows a product</td><td>A poultry farm selling eggs, a bakery</td></tr>
<tr><td>Service</td><td>Sells skill or time</td><td>A phone repair stall, tutoring, boda-boda delivery</td></tr>
<tr><td>Digital</td><td>Sells through an app or online platform</td><td>A tutoring app, an online tailoring shop</td></tr>
</tbody>
</table>
<p>Businesses also differ in size and aim. A <strong>self-employed</strong> business supports one person or family. A <strong>small and growing</strong> business hires workers and may open more outlets. A <strong>startup</strong> is a young business testing a model that it hopes can grow fast. A <strong>social enterprise</strong> earns money but also aims to solve a community problem. None of these is better; they simply need different amounts of money and different habits.</p>

<h3>Worked example: choosing between two paths</h3>
<p>Grace has UGX 1,500,000 in savings. She could buy 50 layer chicks and feed, or rent a small stall and offer phone screen repairs. The poultry route needs about four months before eggs come, so she must feed birds with no income in that time. The repair stall needs training she does not yet have but earns from week one. Neither is wrong. Grace picks the one that matches how long she can wait for income and which skills she already has.</p>

<h3>Case scenario</h3>
<p>Peter saw three friends make money selling second-hand clothes, so he borrowed UGX 800,000 from a lender and bought bales without visiting the market to see how many sellers there already were. Sales were slow, prices fell and he could not pay back on time. <strong>Decision point:</strong> Peter could keep buying bales, or stop and study what customers actually wanted. He stopped, sold the remaining stock at a small loss, and spent two weeks asking buyers what they wanted. <strong>Lesson:</strong> copying a trend without checking demand and competition turns a good intention into a debt. Look first, spend second.</p>

<blockquote><p><strong>Quick check:</strong> A person buys goods from a wholesaler and sells them from a shop. Which type of business is this?</p><p>Answer: Trading, because the business resells goods it did not make.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why do experienced entrepreneurs run small tests before big spending?</p><p>Answer: Tests are cheap and show whether customers really want the offer, so mistakes cost little.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write down three skills you have and three problems people around you complain about.</li>
<li>List two business types from the table that could use your skills.</li>
<li>For each, note how much money you would need and how long before the first income.</li>
<li>Choose the one that fits your savings and patience best, and write one sentence on why.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>An entrepreneur meets a need and accepts risk; no special talent is required.</li>
<li>Useful habits are problem focus, small tests, money awareness and honest persistence.</li>
<li>Main types are trading, production, service and digital, each with different cash needs.</li>
<li>Check demand and competition before copying a trend or borrowing money.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Finding problems worth solving',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to find real problems that people already spend time or money on, and rank them to choose one worth testing. This matters because the most common reason new businesses fail is not poor effort but building something nobody urgently needs.</p>

<h3>Good problems have three signs</h3>
<ol>
<li><strong>Frequent:</strong> it happens often, daily or weekly, not once a year.</li>
<li><strong>Painful:</strong> it costs real time, money or stress.</li>
<li><strong>Already paid for:</strong> people are already spending something to cope, even with a poor solution.</li>
</ol>
<p>The third sign is the strongest. If people already pay a boda-boda rider UGX 10,000 to carry a parcel, or pay a repairer to fix cracked screens, you know money flows there.</p>

<h3>Where to look</h3>
<ul>
<li><strong>Your own daily life:</strong> what wastes your time or money?</li>
<li><strong>Complaints:</strong> listen in taxis, markets, WhatsApp groups and at school gates.</li>
<li><strong>Workarounds:</strong> if people use a clumsy method, such as sending school notes through photos in a chat group, a better tool may be wanted.</li>
<li><strong>Existing businesses:</strong> where do customers queue, argue about price, or complain about delays?</li>
</ul>

<h3>Worked example: scoring problems</h3>
<p>Ruth lists three problems she saw. She scores each from 1 to 5 for frequency, pain and current spending, then adds the scores.</p>
<table>
<thead><tr><th>Problem</th><th>Frequency</th><th>Pain</th><th>Already paying</th><th>Total</th></tr></thead>
<tbody>
<tr><td>Parents struggle to get affordable Maths tutors</td><td>4</td><td>5</td><td>4</td><td>13</td></tr>
<tr><td>Cracked phone screens near campus</td><td>5</td><td>4</td><td>5</td><td>14</td></tr>
<tr><td>Wedding cakes are hard to order from home</td><td>2</td><td>3</td><td>3</td><td>8</td></tr>
</tbody>
</table>
<p>The cracked screens problem scores 14, the highest, and tutoring scores 13. The cakes problem scores 8 and drops down the list. Scores are only a guide, but they force you to think instead of falling in love with one idea.</p>

<h3>Problem or solution?</h3>
<p>Beginners often start with a solution: "I will build an app." A better start is a problem sentence: "Farmers in my village lose eggs because they cannot reach buyers while the eggs are fresh." A good problem sentence names who has the problem, what happens and why current options fail.</p>

<h3>Case scenario</h3>
<p>A team built a polished mobile app that showed bus timetables for a small town. They spent UGX 3,000,000 on development. After launch only 12 people downloaded it, because the buses did not follow timetables at all; passengers simply waited at the stage. <strong>Decision point:</strong> keep adding features or study real behaviour. They talked to passengers and found the real pain was not knowing when a bus would fill and leave. <strong>Lesson:</strong> they solved an imagined problem first and a real one only after losing money. Talk to people before you build.</p>

<blockquote><p><strong>Quick check:</strong> Which sign shows a problem is real: people say it is annoying, or people already pay something to cope?</p><p>Answer: People already paying, because actions with money are stronger evidence than polite opinions.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Ruth's scoring gave 5 + 4 + 5 for one problem. What is the total?</p><p>Answer: 14, found by adding 5, 4 and 5.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write down 10 problems you or people near you meet in a week.</li>
<li>Pick your best three and score each from 1 to 5 for frequency, pain and current spending.</li>
<li>Write one problem sentence for the top scorer: who, what happens, why current options fail.</li>
<li>Keep it for the next lesson, where you will test it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Good problems are frequent, painful and already paid for.</li>
<li>Look at your own life, complaints, workarounds and existing queues.</li>
<li>Score ideas to compare them fairly; do not fall in love with one.</li>
<li>Start from a problem sentence, not from a product.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Validating your idea: talk to customers and test cheaply',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to run honest customer interviews and design a small, low-cost test that shows whether people will really pay. This matters because friends and family often say "great idea" to be kind, while real customers only reveal the truth when money or effort is asked of them.</p>

<h3>Interviews that give true answers</h3>
<p>Talk to at least 10 to 15 people who really have the problem. Ask about the past, not the future, because people are bad at predicting what they will do.</p>
<table>
<thead><tr><th>Weak question</th><th>Better question</th></tr></thead>
<tbody>
<tr><td>Would you buy a phone repair service?</td><td>When did your screen last break, and what did you do?</td></tr>
<tr><td>Do you like my idea?</td><td>How much did that cost you, and what annoyed you most?</td></tr>
<tr><td>Would you pay UGX 20,000 for it?</td><td>What have you already tried, and what did you pay?</td></tr>
</tbody>
</table>
<p>Listen more than you talk, do not defend your idea, and write down the exact words people use. Those words later become your advertising.</p>

<h3>Cheap tests before you build</h3>
<ul>
<li><strong>Pre-orders:</strong> ask for a small deposit. A deposit is stronger proof than a smile.</li>
<li><strong>Manual first:</strong> deliver the service by hand before building any system. A tutoring app can begin as a WhatsApp group with a teacher.</li>
<li><strong>Simple advert:</strong> post a photo and a price in a community group and count the genuine enquiries.</li>
<li><strong>Sample batch:</strong> bake 20 loaves instead of renting a big oven.</li>
</ul>

<h3>Worked example: the online tailoring test</h3>
<p>Mercy wants to sell made-to-measure dresses online. Instead of renting a shop, she shares photos of three sample designs in two community groups, priced at UGX 80,000. She sets a rule before starting: "If at least 5 of 100 people who see it ask for an order within two weeks, I will continue." Her result was 9 serious enquiries and 4 paid orders with a UGX 20,000 deposit. 9 out of 100 is 9 percent, above her 5 percent target, so she goes ahead. Her total test cost was UGX 40,000 for data and fabric samples.</p>

<h3>Decide the success rule before the test</h3>
<p>Write the number you need before you look at results. Otherwise you will explain away a poor result. A good rule states how many people, how much money, and by when.</p>

<h3>Case scenario</h3>
<p>A young farmer wanted to sell pre-cut vegetable packs to office workers. Friends loved the idea, so he rented a cold room for UGX 400,000 a month. When he offered samples to 30 office workers, only 2 bought a second time, saying fresh vegetables from the roadside were cheaper. <strong>Decision point:</strong> sign a longer cold-room lease or stop. He ended the lease after one month, lost UGX 400,000, and tested a smaller idea: weekly vegetable boxes by pre-order. <strong>Lesson:</strong> a short, cheap failure is a success of learning. A long lease signed on friends' praise would have cost far more.</p>

<blockquote><p><strong>Quick check:</strong> Why are questions about past behaviour better than questions about future plans?</p><p>Answer: People describe what they actually did accurately, but they often guess wrongly or politely about what they will do.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Mercy got 9 enquiries from 100 viewers. What percentage is that?</p><p>Answer: 9 percent, because 9 divided by 100 is 0.09.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Take your problem sentence from the last lesson and write five past-behaviour questions.</li>
<li>Interview at least five real people and note their exact words.</li>
<li>Design one cheap test, such as a photo advert or a small pre-order, with a cost under UGX 50,000.</li>
<li>Write your success rule (how many, how much, by when) before you start.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Ask about the past and listen; do not sell during an interview.</li>
<li>Deposits and pre-orders are stronger proof than compliments.</li>
<li>Do the service by hand before building systems.</li>
<li>Set your success number first, then judge honestly.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'From idea to value proposition',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a clear value proposition: one or two sentences saying who your product helps, what problem it solves and why it is better than the alternatives. This matters because customers buy a result they want, not a list of features, and a clear promise makes every later decision easier.</p>

<h3>What a value proposition is</h3>
<p>A <strong>value proposition</strong> is the main reason a customer chooses you. It answers three questions: who is it for, what do they get, and why you rather than someone else? Most customers decide in seconds, so it must be simple.</p>

<h3>A simple template</h3>
<pre><code>For [customer] who [problem], our [product] gives [main benefit],
unlike [current option] which [weakness].</code></pre>

<h3>Features, benefits and outcomes</h3>
<table>
<thead><tr><th>Level</th><th>Example for a phone repair stall</th></tr></thead>
<tbody>
<tr><td>Feature (what it is)</td><td>We use tested replacement screens</td></tr>
<tr><td>Benefit (what it does)</td><td>Your screen lasts longer and touch works well</td></tr>
<tr><td>Outcome (what changes for the person)</td><td>You are back on your phone within one hour and stop worrying about cracks</td></tr>
</tbody>
</table>
<p>Lead with the outcome and support it with benefits. A student does not want "a screen"; she wants her phone working before the next lecture.</p>

<h3>Worked example: the poultry farm</h3>
<p>Isaac keeps 500 layers. A weak message: "We sell eggs." A stronger one: "For restaurants in Mbarara that get cracked or stale eggs, we deliver fresh, graded eggs twice a week, unlike open-market sellers whose supply is unreliable." Now he can test it: he offers one restaurant a trial of 10 trays at UGX 12,000 each, a total of UGX 120,000. The restaurant orders again next week, which tells him the promise holds. Notice that "twice a week" and "graded" are things he can truly deliver, which matters because a promise you cannot keep destroys trust.</p>

<h3>Differentiation without lying</h3>
<p>You do not have to be cheaper. You can be faster, more reliable, friendlier, more convenient or better for one particular group. Choose one thing to be known for. Never claim certificates, licences or results that you do not have.</p>

<h3>Case scenario</h3>
<p>A boda-boda delivery startup in Gulu advertised "fast delivery for everyone." Three weeks later it had few regular customers. Then the founders narrowed the message: "Same-day parcel delivery for small shops within 5 kilometres, with a mobile money receipt for every parcel." Within a month 14 shops became regular clients, each sending about 6 parcels a week. <strong>Decision point:</strong> stay broad or choose one group. <strong>Outcome:</strong> narrowing won loyal customers. <strong>Lesson:</strong> a specific promise to a specific group is easier to believe and easier to deliver than a promise to everyone.</p>

<blockquote><p><strong>Quick check:</strong> Which is an outcome: "tested replacement screens" or "your phone works again within one hour"?</p><p>Answer: "Your phone works again within one hour", because an outcome describes the change in the customer's life, not the product.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Isaac sells 10 trays at UGX 12,000. What is the total?</p><p>Answer: UGX 120,000, because 10 times 12,000 equals 120,000.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Fill in the template with your own idea.</li>
<li>Rewrite it so that the main outcome comes first and remove any word a customer would not use.</li>
<li>Read it to two people and ask them to repeat it back in their own words.</li>
<li>Adjust it until they understand it in one reading.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A value proposition says who it is for, what they gain and why you.</li>
<li>Sell outcomes, then benefits, then features.</li>
<li>Be specific about one group and one strength.</li>
<li>Only promise what you can deliver.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which of these best describes an entrepreneur?
- Someone who always works alone and avoids all risk
* Someone who spots a need, organises resources to meet it and accepts risk
- Someone who only sells goods bought from other people
- Someone who inherits an established company
= An entrepreneur notices a need and organises money, people and materials to meet it, knowing it may fail.

Q: A person bakes and sells bread. Which business type is this?
- Trading
- Service
* Production
- Social enterprise only
= The business makes the product itself, so it is a production business.

Q: Which signs show a problem is worth solving? (choose all that apply)
* It happens often
* It causes real pain or cost
* People already spend money coping with it
- Your friends said they like your idea
= Frequency, pain and existing spending point to a real problem; compliments from friends are weak evidence.

Q: Ruth scored one problem 3 for frequency, 5 for pain and 4 for current spending. Another scored 5, 3 and 4. Which statement is correct?
- The first problem is better with 12 against 11
- The second problem is better with 13 against 12
* Both problems score 12, so she needs more evidence to choose
- The first problem scores 13 and wins
= 3 + 5 + 4 = 12 and 5 + 3 + 4 = 12. A tie means more customer evidence is needed.

Q: Which interview question gives the most reliable information?
- Would you use my new app?
- Do you think my idea is good?
* When did you last have this problem and what did you do about it?
- How much would you pay in future?
= Questions about real past behaviour are more reliable than predictions or compliments.

Q: Mercy aims for at least 5 serious enquiries from every 100 viewers. 100 people saw her advert and 7 asked to order. What should she conclude?
* The result beats her rule, 7 percent against 5 percent, so she can continue testing
- The test failed because the number is below 10
- She should stop because 7 is not a whole number of tens
- The test proves nothing about demand
= 7 out of 100 is 7 percent, which is above the 5 percent success rule she set before the test.

Q: A farmer signs a one-year cold-room lease because friends praise his vegetable pack idea, then finds few repeat buyers. What was his main mistake?
- Choosing vegetables
- Talking to too many customers
* Committing large money before a cheap test with real customers
- Charging too little
= A small paid test would have revealed weak repeat demand before the lease.

Q: Which is the strongest value proposition?
- We sell good quality items at fair prices
* Same-day parcel delivery for shops within 5 km, with a mobile money receipt for each parcel
- We are the best business in town
- We do everything for everyone
= It names the customer, the benefit and the proof, and it is specific enough to test and deliver.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Customers, business model and pricing',
            'summary' => 'Describe who your customers are and how many there might be, lay out your business model on one page and set a price that works.',
            'lessons' => [
                [
                    'title'   => 'Customer segments and market size basics',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to split people into customer groups, choose the first group to serve, and make a sensible, honest estimate of how many customers and how much money are possible. This matters because a business that tries to serve "everybody" usually serves nobody well, and investors, banks and you yourself need a realistic size for the opportunity.</p>

<h3>What is a customer segment?</h3>
<p>A <strong>customer segment</strong> is a group of people with similar needs, similar budgets and similar ways of buying. A bakery could sell to households, to schools, to hotels or to small shops. Each group buys differently: households buy one loaf, a school may order 200 buns every morning on credit. Choose one segment to start with. You can add others later.</p>

<h3>Describing your first customer</h3>
<p>Write a short profile with these points: who they are, where they are, what problem they have, what they use now, how much they spend, and how they hear about new things. Be specific: "parents of secondary school students in Entebbe who pay for private lessons" is far better than "students".</p>

<h3>The simple market size method</h3>
<p>Estimating market size from the bottom up means counting real customers you can reach and multiplying by what each pays. Avoid the common trap of saying "Uganda has 45 million people, if we get 1 percent we are rich", because it hides every real question about reaching them.</p>
<ol>
<li><strong>Everyone with the problem</strong> (the total market): all students who might need extra lessons in your area of operation.</li>
<li><strong>The part you can serve</strong> (your serviceable market): those within the schools and towns you can actually reach.</li>
<li><strong>The part you can win soon</strong> (your first target): the share you realistically convince in the first year.</li>
</ol>

<h3>Worked example: the tutoring app</h3>
<p>A team plans a Maths tutoring app for senior 3 and senior 4 students.</p>
<table>
<thead><tr><th>Step</th><th>Assumption</th><th>Result</th></tr></thead>
<tbody>
<tr><td>Schools they can reach by visiting</td><td>20 schools</td><td>20</td></tr>
<tr><td>Students per school in these classes</td><td>300</td><td>20 x 300 = 6,000 students</td></tr>
<tr><td>Share who might pay</td><td>10 percent</td><td>6,000 x 10% = 600 students</td></tr>
<tr><td>Price per student per month</td><td>UGX 10,000</td><td>600 x 10,000 = UGX 6,000,000 a month</td></tr>
</tbody>
</table>
<p>Over a year that is UGX 72,000,000 if every assumption holds (6,000,000 times 12). The numbers are guesses, so mark them as assumptions and test the weakest ones first, such as the 10 percent. If only 3 percent pay, the figure drops to 180 students and UGX 1,800,000 a month.</p>

<h3>Competitors are information</h3>
<p>If nobody else serves a group, ask why. It may be a gap or it may be a signal that people will not pay. If others exist, visit them as a customer and note their prices, speed and weak points. Your promise should be better in one clear way.</p>

<h3>Case scenario</h3>
<p>A man opened a bakery in a trading centre with about 800 households, assuming every household would buy bread daily. In reality most families ate cassava, posho or bought from a bigger bakery three kilometres away; only about 60 households bought regularly, about 2 loaves a week each. That is about 120 loaves a week, around 17 a day, far too low to cover rent. <strong>Decision point:</strong> close or change segment. He started delivering to two schools and three shops, and sales grew. <strong>Lesson:</strong> "everyone in the area" is not a market. Count the people who actually buy.</p>

<blockquote><p><strong>Quick check:</strong> A tutoring app reaches 10 schools with 200 target students each, and 10 percent pay UGX 10,000 a month. What is the monthly income?</p><p>Answer: UGX 2,000,000, because 10 x 200 = 2,000 students, 10 percent is 200 students, and 200 x 10,000 = 2,000,000.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why is "if we get 1 percent of the country" a poor way to size a market?</p><p>Answer: It ignores whether you can reach those people and whether they will pay, so it is not based on real customers.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a profile of your first customer segment with the six points above.</li>
<li>Estimate your market size from the bottom up using three steps with clear numbers.</li>
<li>Mark which assumption you are least sure about and how you could check it this week.</li>
<li>List two competitors, their prices and one weakness of each.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A segment is a group with similar needs and budgets; start with one.</li>
<li>Estimate size by counting reachable customers times what each pays.</li>
<li>Treat every number as an assumption and test the weakest first.</li>
<li>"Everyone" is never a customer group.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The business model canvas in plain words',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe how your business creates, delivers and earns money using a one-page tool called the business model canvas. This matters because one page that you can show a partner, a lender or a trainer exposes gaps in your thinking long before they cost you money.</p>

<h3>What is a business model?</h3>
<p>A <strong>business model</strong> explains how a business makes money: who pays, for what, how you deliver it and what it costs you. The <strong>business model canvas</strong> splits this into nine boxes. You can draw it on paper in the shape of a grid and fill it with sticky notes or pencil.</p>

<table>
<thead><tr><th>Box</th><th>Question in plain words</th></tr></thead>
<tbody>
<tr><td>1. Customer segments</td><td>Who are we serving first?</td></tr>
<tr><td>2. Value proposition</td><td>What problem do we solve and why us?</td></tr>
<tr><td>3. Channels</td><td>How do customers find us and receive the product?</td></tr>
<tr><td>4. Customer relationships</td><td>How do we keep them coming back?</td></tr>
<tr><td>5. Revenue streams</td><td>How and how much do they pay?</td></tr>
<tr><td>6. Key resources</td><td>What do we need to own or use (people, tools, money)?</td></tr>
<tr><td>7. Key activities</td><td>What must we do every week to deliver?</td></tr>
<tr><td>8. Key partners</td><td>Who helps us (suppliers, riders, banks)?</td></tr>
<tr><td>9. Cost structure</td><td>What are our biggest costs?</td></tr>
</tbody>
</table>

<h3>Fill the right side first</h3>
<p>Start with customers and value proposition, then channels, relationships and revenue. These are the "money in" side. Then fill resources, activities, partners and costs, the "money out" side. Finally compare: does revenue clearly cover costs?</p>

<h3>Worked example: an online tailoring shop</h3>
<table>
<thead><tr><th>Box</th><th>Mercy's answer</th></tr></thead>
<tbody>
<tr><td>Customers</td><td>Working women in Kampala who lack time for fittings</td></tr>
<tr><td>Value proposition</td><td>Made-to-measure dresses delivered in 7 days without visiting a shop</td></tr>
<tr><td>Channels</td><td>Facebook and WhatsApp for orders; rider delivery</td></tr>
<tr><td>Relationships</td><td>WhatsApp updates at each stage; free small repair within 14 days</td></tr>
<tr><td>Revenue</td><td>UGX 80,000 per dress, 50 percent deposit</td></tr>
<tr><td>Resources</td><td>Two tailors, one machine, a phone, fabric stock</td></tr>
<tr><td>Activities</td><td>Taking measurements by video call, sewing, delivery</td></tr>
<tr><td>Partners</td><td>Fabric supplier, boda-boda riders, mobile money agent</td></tr>
<tr><td>Costs</td><td>Fabric about UGX 30,000, tailor pay UGX 20,000, delivery UGX 5,000 per dress, advertising UGX 150,000 a month</td></tr>
</tbody>
</table>
<p>A quick check: each dress earns 80,000 and costs 55,000 to make and deliver (30,000 + 20,000 + 5,000), leaving 25,000. The monthly advert cost of 150,000 is covered by 6 dresses (150,000 divided by 25,000). The model looks workable on paper and is ready for real testing.</p>

<h3>Use it as a living tool</h3>
<p>Write pencil answers and update the canvas every time you learn something from a customer. Mark the riskiest box, the one most likely to be wrong, and plan a test for it. Keep a dated copy each month to see how your thinking improved.</p>

<h3>Case scenario</h3>
<p>Two friends started a boda-boda delivery business with a clear canvas except one box: partners. They relied on one fuel station owner to extend credit, with no written arrangement. When he stopped credit after a dispute, riders could not refuel and 3 days of deliveries were lost, including a key shop's orders. <strong>Decision point:</strong> carry on with one partner or build alternatives. They agreed with two stations and kept a small fuel reserve fund. <strong>Lesson:</strong> check each box for single points of failure, especially partners and key resources.</p>

<blockquote><p><strong>Quick check:</strong> In Mercy's canvas, which two boxes would show that dresses cost UGX 55,000 to make and deliver?</p><p>Answer: Cost structure and key activities, because fabric, tailor pay and delivery are costs of the work she must do.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which box describes how customers find you and receive your product?</p><p>Answer: Channels, because it covers advertising, ordering and delivery routes.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Draw a 9-box grid on a page and label the boxes.</li>
<li>Fill the customer, value proposition, channel and revenue boxes first.</li>
<li>Fill the cost side with real numbers where you can.</li>
<li>Circle the riskiest box and write one cheap test for it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>The canvas shows on one page who pays, for what, and what it costs.</li>
<li>Fill customers and value first, then costs.</li>
<li>Check that revenue per sale covers variable costs plus a share of fixed costs.</li>
<li>Update it as you learn and watch for single points of failure.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Pricing for profit',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose a price using costs, competitors and value, and calculate the profit on each sale and the effect of discounts. This matters because a price that is too low can make every sale lose money, and many small businesses stay busy without ever making profit.</p>

<h3>Three ways to think about price</h3>
<ul>
<li><strong>Cost-based:</strong> add up what it costs you, then add a margin. This sets the floor: do not go below it for long.</li>
<li><strong>Competitor-based:</strong> look at what similar sellers charge near you. This sets the neighbourhood.</li>
<li><strong>Value-based:</strong> think about what the result is worth to the customer. A screen repaired in one hour is worth more to a busy student than a repair taking three days.</li>
</ul>
<p>A good price lies above your cost floor, close to the competitor range and as high as the value you give allows.</p>

<h3>Cost, profit and margin words</h3>
<table>
<thead><tr><th>Word</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>Variable cost</td><td>Cost that rises with each unit sold (flour, fabric, a replacement screen)</td></tr>
<tr><td>Fixed cost</td><td>Cost that stays the same whether you sell little or much (rent, a monthly salary)</td></tr>
<tr><td>Unit margin</td><td>Price minus variable cost for one unit</td></tr>
<tr><td>Margin percent</td><td>Unit margin divided by price, times 100</td></tr>
</tbody>
</table>

<h3>Worked example: the bakery loaf</h3>
<p>A bakery sells a loaf at UGX 5,000. Ingredients cost 2,800 and gas and packaging cost 700, so the variable cost is 3,500 (2,800 + 700). The unit margin is 5,000 minus 3,500, which is UGX 1,500. The margin percent is 1,500 divided by 5,000, which is 0.30, or 30 percent.</p>
<p>Now the baker offers a 10 percent discount to attract a school. 10 percent of 5,000 is 500, so the new price is 4,500. The new margin is 4,500 minus 3,500, which is UGX 1,000. A discount of only 10 percent on the price cut the margin from 1,500 to 1,000, a drop of one third. To earn the same total margin, the bakery must sell 1,500 divided by 1,000, which is 1.5 times more loaves: 150 loaves instead of 100.</p>

<h3>Pricing mistakes to avoid</h3>
<ul>
<li>Forgetting your own time or a worker's pay in the cost.</li>
<li>Copying the cheapest competitor without checking whether they lose money.</li>
<li>Offering discounts to everyone instead of for a clear reason, such as a bulk order.</li>
<li>Never reviewing prices when costs, such as feed or fabric, rise.</li>
</ul>

<h3>Case scenario</h3>
<p>An online tailor charged UGX 50,000 per dress to win customers fast. Her costs were fabric 30,000, tailor pay 20,000 and delivery 5,000, a total of 55,000. She sold 30 dresses in one month and felt successful, but she had lost 5,000 on each dress, a total of 150,000, before even paying for advertising. <strong>Decision point:</strong> keep a low price for volume, or raise it. She tested UGX 80,000 with better photos and a seven-day promise; orders fell from 30 to 22, but 22 x 25,000 margin gave UGX 550,000. <strong>Lesson:</strong> more sales at a losing price is only a faster way to lose money. Always calculate the margin per unit first.</p>

<blockquote><p><strong>Quick check:</strong> A phone screen repair sells at UGX 60,000 and the part costs 35,000. What is the unit margin?</p><p>Answer: UGX 25,000, because 60,000 minus 35,000 is 25,000.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> The same repair is discounted by UGX 6,000. What is the new margin?</p><p>Answer: UGX 19,000, because the new price is 54,000 and 54,000 minus 35,000 is 19,000.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List every variable cost for one unit of your product or service.</li>
<li>Calculate the unit margin and the margin percent.</li>
<li>Find three competitor prices and write where your price sits and why.</li>
<li>Work out how many more units you would need to sell if you gave a 10 percent discount.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use cost as the floor, competitors as the neighbourhood and value as the ceiling.</li>
<li>Unit margin is price minus variable cost for one unit.</li>
<li>Small discounts need much larger sales to give the same profit.</li>
<li>Never price below cost without a clear, short plan.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is a customer segment?
- Every person who lives in the country
* A group of people with similar needs, budgets and ways of buying
- A list of your competitors
- The total money your business earns
= A segment is a group that shares needs and behaviour, so you can design one clear offer for them.

Q: A tutoring team can reach 10 schools with 300 target students each. If 10 percent will pay UGX 10,000 a month, what is the monthly income?
- UGX 300,000
- UGX 1,000,000
* UGX 3,000,000
- UGX 30,000,000
= 10 x 300 = 3,000 students; 10 percent is 300 students; 300 x 10,000 = UGX 3,000,000 a month.

Q: Which is the best way to estimate the size of your market?
- Take the national population and assume 1 percent will buy
* Count the customers you can really reach and multiply by what each pays
- Copy a competitor's advert
- Ask only your friends
= A bottom-up estimate is based on real, reachable customers and real prices.

Q: In the business model canvas, where would "Facebook adverts and rider delivery" go?
- Cost structure
- Customer segments
* Channels
- Revenue streams
= Channels describe how customers find you and how the product reaches them.

Q: Which of these are variable costs for a bakery? (choose all that apply)
* Flour
* Packaging bags
- Monthly shop rent
- A fixed monthly salary for the manager
= Flour and bags rise with each loaf; rent and a fixed salary do not change with the number of loaves.

Q: A loaf sells at UGX 5,000 and costs UGX 3,500 in ingredients, gas and packaging. What is the margin percent?
- 15 percent
* 30 percent
- 35 percent
- 70 percent
= Margin is 1,500; 1,500 divided by 5,000 equals 0.30, which is 30 percent.

Q: A tailor sells a dress for UGX 50,000 with total variable cost of UGX 55,000. She sells 30 dresses. What is the result before advertising?
- A profit of UGX 150,000
- A profit of UGX 1,500,000
- No profit and no loss
* A loss of UGX 150,000
= Each dress loses 5,000 and 30 x 5,000 = UGX 150,000 lost.

Q: A baker thinks a 10 percent discount only costs a little. The price is UGX 5,000 and the variable cost UGX 3,500. How does the unit margin change?
- It stays at 1,500
* It falls from 1,500 to 1,000
- It rises to 2,000
- It falls to 500
= The new price is 4,500, so the margin is 4,500 minus 3,500, which is 1,000.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Money, records and the rules',
            'summary' => 'Work out break-even, keep simple books and cash flow, separate your own money from the business, register in general terms and understand legal and ethical basics.',
            'lessons' => [
                [
                    'title'   => 'Costs, break-even and unit economics',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to calculate how many sales you need each month to cover your costs, and to say whether each unit you sell earns or loses money. This matters because the break-even number turns a vague hope ("I think it will work") into a clear daily target you can measure.</p>

<h3>The break-even formula</h3>
<p><strong>Break-even</strong> is the sales level where total income equals total costs, so profit is zero. Above it you make profit; below it you make a loss.</p>
<pre><code>Break-even units = Fixed costs per month / Unit margin
Unit margin = Price - Variable cost per unit</code></pre>

<h3>Worked example 1: phone repair stall</h3>
<p>A stall charges UGX 60,000 for a screen replacement. The part costs 35,000, so the unit margin is 25,000. Fixed costs are rent UGX 300,000 per month. Break-even is 300,000 divided by 25,000, which is 12 screens a month. If the stall repairs 20 screens, the profit is 20 x 25,000 = 500,000, minus 300,000 rent, which leaves UGX 200,000. If it repairs only 8, the result is 8 x 25,000 = 200,000 minus 300,000, a loss of UGX 100,000.</p>

<h3>Worked example 2: bakery</h3>
<p>The loaf margin is UGX 1,500 (price 5,000 minus variable cost 3,500). Fixed costs are shop rent 400,000 plus a baker's wage 600,000, a total of UGX 1,000,000 a month. Break-even is 1,000,000 divided by 1,500, which is 666.67, so round up to 667 loaves. Spread over 30 days that is about 22 loaves a day. If the owner expects to sell 15 loaves a day, she can see at once that the plan fails unless costs fall or the price changes.</p>

<h3>Worked example 3: tutoring app</h3>
<p>Each student pays UGX 10,000 a month. Mobile money charges are assumed in this example at 3 percent, which is UGX 300, and hosting and support are UGX 700 per student. The variable cost is 1,000, so the unit margin is 9,000. Fixed costs are UGX 2,700,000 a month (developer, internet, marketing). Break-even is 2,700,000 divided by 9,000, which is 300 students. Check real charges with your own provider; the figures here are only for practice.</p>

<h3>Five ways to move break-even</h3>
<ol>
<li>Raise the price if customers accept it.</li>
<li>Lower variable cost, for example by buying flour in bulk.</li>
<li>Lower fixed costs, for example by sharing a stall.</li>
<li>Increase the number of units by better marketing.</li>
<li>Add a second product with a healthy margin.</li>
</ol>

<h3>Case scenario</h3>
<p>A poultry farmer planned his budget carefully. A tray of 30 eggs sold at UGX 12,000. Feed was 7,500 per tray, vaccines and labour 1,500, transport 500, so variable cost was 9,500 and margin 2,500 (about 21 percent). Fixed costs were 750,000 a month. Break-even was 750,000 divided by 2,500, which is 300 trays. His 500 hens gave about 400 trays a month at best, so 300 trays was reachable, but when feed prices rose by UGX 1,000 per tray the margin fell to 1,500 and break-even rose to 500 trays, more than his farm produced. <strong>Decision point:</strong> he raised the price to UGX 13,500 for restaurant customers and cut waste. <strong>Lesson:</strong> re-run your break-even whenever a main cost changes, or you will find the loss only at month end.</p>

<blockquote><p><strong>Quick check:</strong> Fixed costs are UGX 600,000 a month and the unit margin is UGX 2,000. What is the break-even number of units?</p><p>Answer: 300 units, because 600,000 divided by 2,000 is 300.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What happens to break-even if the unit margin falls and fixed costs stay the same?</p><p>Answer: Break-even rises, because you need more units to cover the same fixed costs.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List your fixed costs for one month and add them up.</li>
<li>Work out the unit margin for your main product.</li>
<li>Divide and round up to get break-even units, then divide by 30 for a daily target.</li>
<li>Write two changes that could lower your break-even.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Break-even equals fixed costs divided by unit margin.</li>
<li>Round up and convert to a daily target you can track.</li>
<li>A change in price, variable cost or fixed cost moves break-even.</li>
<li>Recalculate when a main cost changes.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Simple bookkeeping, cash flow and mobile money for business',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to keep a simple record of sales and expenses, forecast your cash for the month, separate your own money from the business, and use mobile money safely. This matters because many profitable-looking businesses fail when cash runs out, and you cannot manage what you do not record.</p>

<h3>The minimum records</h3>
<ul>
<li><strong>Sales book:</strong> date, what was sold, amount, paid or on credit.</li>
<li><strong>Expense book:</strong> date, what for, amount, who was paid. Keep receipts or take a phone photo.</li>
<li><strong>Stock list:</strong> what you hold and its cost.</li>
<li><strong>Credit list:</strong> who owes you, how much and due date, and who you owe.</li>
</ul>
<p>An exercise book, a spreadsheet or a phone notes app all work. The best system is the one you update every day for five minutes.</p>

<h3>Profit is not cash</h3>
<p><strong>Profit</strong> is what is left after costs. <strong>Cash</strong> is the money you can actually spend now. They differ when you buy stock to sell later, give customers credit, or pay for things up front. A business can be profitable and still run out of cash.</p>

<h3>Worked example: a monthly cash flow</h3>
<table>
<thead><tr><th>Line</th><th>UGX</th></tr></thead>
<tbody>
<tr><td>Cash at the start of the month</td><td>500,000</td></tr>
<tr><td>Cash received from sales</td><td>1,800,000</td></tr>
<tr><td>Rent</td><td>-300,000</td></tr>
<tr><td>Parts stock bought</td><td>-1,300,000</td></tr>
<tr><td>Transport and airtime</td><td>-150,000</td></tr>
<tr><td>Cash at month end</td><td>550,000</td></tr>
</tbody>
</table>
<p>Check: 500,000 + 1,800,000 = 2,300,000; then take off 300,000 + 1,300,000 + 150,000 = 1,750,000, which leaves 550,000. If the owner had also given UGX 400,000 of repairs on credit, the cash received would have been 1,400,000 and the month-end cash only 150,000, even though sales look the same. Chasing credit customers protects cash.</p>

<h3>Separate personal and business money</h3>
<ol>
<li>Open a separate business bank account or at least a separate mobile money line for business.</li>
<li>Pay yourself a fixed amount (a salary) on a fixed date, and write it down.</li>
<li>Never take money from the till for school fees or rent without recording it as your pay.</li>
<li>Do not pay business bills from your personal wallet without a note.</li>
</ol>

<h3>Mobile money for business</h3>
<p>Mobile money makes payments fast and creates a record. To use it safely: keep your PIN secret and never share it with anyone, even someone claiming to be from the company; confirm each incoming payment by reading the official message on your own phone rather than a screenshot shown by the customer; keep the business number separate from your personal one; and check current charges with the provider, since fees change. Consider limits and the business options offered by providers where available.</p>

<h3>Case scenario</h3>
<p>A busy online tailoring shop sold dresses with 100 percent credit to friends and shared one mobile money number with the owner's household. At the month end the books showed UGX 1,200,000 sales, but she had only UGX 140,000 in hand, since she had spent business money on rent for her family and customers still owed UGX 700,000. She could not buy fabric for new orders. <strong>Decision point:</strong> she began taking 50 percent deposits, opened a separate business line and paid herself a fixed UGX 300,000. <strong>Lesson:</strong> mixed money and unpaid credit hide the truth. Records and deposits protect the business.</p>

<blockquote><p><strong>Quick check:</strong> You start with UGX 200,000, receive 900,000 and pay out 700,000. What is your closing cash?</p><p>Answer: UGX 400,000, because 200,000 + 900,000 - 700,000 = 400,000.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why can a profitable business run out of cash?</p><p>Answer: Money can be tied up in stock or unpaid credit, so cash in hand is less than the profit on paper.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Set up a sales book and an expense book today, on paper or a phone.</li>
<li>Record every sale and expense for seven days.</li>
<li>Write a one-month cash flow table like the example, using your own estimates.</li>
<li>Decide on a monthly salary for yourself and the day you will pay it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Record sales, expenses, stock and credit every day.</li>
<li>Profit is not cash; watch both.</li>
<li>Pay yourself a fixed salary and keep business money separate.</li>
<li>Protect your PIN and confirm payments on your own phone.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Registering a business in Uganda: the general picture',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe, in general terms, the usual steps and the main official bodies involved when a business is formalised in Uganda, and know where to confirm current requirements. This matters because registration can open doors to banks, tenders and bigger customers, and operating without required permissions can bring penalties. This lesson is general education and not legal or tax advice. Rules, forms and fees change, so always check the latest with the official bodies before you act.</p>

<h3>Why formalise?</h3>
<ul>
<li>Larger customers, schools, companies and government offices may ask for registration details before they buy.</li>
<li>Banks and some lenders prefer to deal with registered businesses.</li>
<li>Your business name can be protected from being used by someone else.</li>
<li>It shows seriousness and helps separate you from the business, depending on the structure.</li>
</ul>

<h3>Common business forms (in general terms)</h3>
<table>
<thead><tr><th>Form</th><th>In simple words</th><th>Usual features to ask about</th></tr></thead>
<tbody>
<tr><td>Sole proprietor (business name)</td><td>One owner, simple set-up</td><td>Owner is personally responsible for debts</td></tr>
<tr><td>Partnership</td><td>Two or more people share the business</td><td>Needs a clear written agreement between partners</td></tr>
<tr><td>Company (limited by shares)</td><td>A separate legal person owned by shareholders</td><td>More reporting; owners' liability is usually limited in law</td></tr>
</tbody>
</table>
<p>Which form suits you depends on your plans, risk and partners. Ask the registration body or a qualified adviser rather than guessing.</p>

<h3>The usual path</h3>
<ol>
<li><strong>Choose a structure and a name.</strong> Check if your name is already taken.</li>
<li><strong>Register with the official registry.</strong> In Uganda, the Uganda Registration Services Bureau (URSB) handles business registration. Visit https://ursb.go.ug/ for current services.</li>
<li><strong>Register for tax.</strong> The Uganda Revenue Authority (URA) deals with tax registration and obligations. Visit https://www.ura.go.ug/ for current guidance on taxpayer registration and which taxes may apply to you.</li>
<li><strong>Check local permissions.</strong> Local governments such as the town or city authority often issue trading permits or licences for premises.</li>
<li><strong>Check industry rules.</strong> Food, health, education, transport and finance businesses may need extra approval from the relevant authority.</li>
</ol>

<h3>What not to do</h3>
<p>Do not pay a stranger on the street who promises a quick certificate. Use official offices and official websites, ask for receipts, and keep copies of every document. Do not assume that a fee quoted by a friend a year ago is still correct.</p>

<h3>Worked example: preparing a checklist</h3>
<p>A bakery owner makes a list: 1) business structure and name decision; 2) registry steps; 3) tax registration; 4) local trading permit for the shop; 5) food-safety and standards requirements for packaged bread; 6) a separate business account. She writes the cost of each step as "to be confirmed" and visits the offices or websites to fill in the real amounts. She then budgets for the total in her start-up costs instead of being surprised later.</p>

<h3>Case scenario</h3>
<p>A catering startup won a verbal promise to supply meals to a company's staff canteen, but the company asked for proof of registration and a tax number before signing. The owners had not registered, so the contract was delayed six weeks and the buyer found another caterer. <strong>Decision point:</strong> register now or wait for the next tender. They registered and started again with the next customer. <strong>Lesson:</strong> registration is not just paperwork; it is often a condition for the best customers, so plan it in your timeline.</p>

<blockquote><p><strong>Quick check:</strong> Which two official bodies should you check for business registration and tax registration in Uganda?</p><p>Answer: URSB for registration of the business and URA for tax matters, with local authorities for trading permits.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why should you not rely on a fee quoted by a friend last year?</p><p>Answer: Rules, forms and fees change, so you must confirm the current ones with the official body.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write the five steps above as a checklist for your own business.</li>
<li>Visit the official websites of URSB and URA and note the current services listed for your type of business.</li>
<li>Write "to be confirmed" next to each cost until you have the real figure from the office.</li>
<li>Ask your local authority which permits your premises need.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Formalising helps with bigger customers, banks and protecting your name.</li>
<li>Common forms are sole proprietor, partnership and company; each has different responsibilities.</li>
<li>URSB, URA and local authorities are the starting points; industry rules may add more.</li>
<li>Rules and fees change; always confirm with official sources and avoid middlemen.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Legal and ethical basics: contracts, intellectual property and data',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain why written agreements matter, protect your business name and creative work in general ways, treat customer data responsibly and run an honest business. This matters because trust is the cheapest form of marketing and the hardest to rebuild, and mistakes with contracts or customer data can cost a young business its future. This is general education, not legal advice; consult a qualified lawyer for your own situation.</p>

<h3>Agreements and contracts</h3>
<p>A <strong>contract</strong> is an agreement that the law will enforce. It can be spoken, but a written one is far easier to prove. Write down who does what, by when, for what price, how payment is made and what happens if something goes wrong. Keep the document and a signed copy for each person. Be careful with contracts you do not fully understand; ask questions before signing.</p>
<ul>
<li>With customers: price, delivery date, refunds or repairs, how to complain.</li>
<li>With suppliers: price, quality, delivery time, credit terms.</li>
<li>With workers: pay, hours, duties, notice. Check labour rules with the relevant authority.</li>
</ul>

<h3>Intellectual property (IP)</h3>
<p><strong>Intellectual property</strong> means creations of the mind that have value. The main types are in the table below. Laws and procedures differ by country, so check with the relevant office.</p>
<table>
<thead><tr><th>Type</th><th>Protects</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Trademark</td><td>Brand name, logo, slogan</td><td>The name and logo of a bakery</td></tr>
<tr><td>Copyright</td><td>Original written, visual, musical and software work</td><td>Your photos, website text, app code</td></tr>
<tr><td>Trade secret</td><td>Confidential know-how</td><td>A recipe or a supplier list kept private</td></tr>
</tbody>
</table>
<p>Practical steps: choose a name that is not already used for a similar business; keep dated copies of your designs; use agreements so that people who make logos or code for you transfer the rights clearly; never copy someone else's photos, music or code without permission.</p>

<h3>Customer data</h3>
<p>If you keep names, phone numbers, addresses or photos of customers, you hold personal data. Uganda has a data protection law, the Data Protection and Privacy Act, and you should check its current requirements for your business. Good habits are simple: collect only what you need, tell people why, keep it secure with passwords and a locked phone, do not share it or sell it without permission, and delete it when it is no longer needed. Do not add customers to marketing groups without their consent.</p>

<h3>Ethics: doing business honestly</h3>
<ul>
<li>Tell the truth about your product, its source and its price.</li>
<li>Give correct weights, quantities and receipts.</li>
<li>Pay workers and suppliers on time.</li>
<li>Do not bribe or accept bribes; it damages trust and may be a crime.</li>
<li>Treat customers and competitors with respect.</li>
</ul>

<h3>Worked example: protecting a tutoring app</h3>
<p>A founder hires a freelancer to build the app for UGX 2,000,000. She writes an agreement stating that the code and design belong to her business after payment, the freelancer will not copy it, and payment is in two parts of UGX 1,000,000. She also checks that the app name is not in use by another education business and keeps student data in a password-protected system with a short privacy note for parents.</p>

<h3>Case scenario</h3>
<p>A founder paid a friend UGX 500,000 to build a website with no written agreement. When they quarrelled, the friend refused to hand over the files and said he owned the work. Months of content were lost. <strong>Decision point:</strong> argue or rebuild. She rebuilt the site in four weeks and from then on used a short written agreement for every job. <strong>Lesson:</strong> a half-page agreement signed at the start costs nothing compared with a quarrel later.</p>

<blockquote><p><strong>Quick check:</strong> A freelancer builds your logo. What should the agreement say about ownership?</p><p>Answer: It should say clearly that the rights pass to your business after payment, so there is no later dispute.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Is it fine to add all your customers to a marketing group without telling them?</p><p>Answer: No, because you should have consent to use personal data for marketing and should respect their privacy.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a half-page agreement template for a customer order: items, price, date, payment, complaints.</li>
<li>Check that your business name is not already used for a similar business.</li>
<li>List the personal data you collect and decide how you will keep it safe.</li>
<li>Write three ethical rules for your business and display them where staff can see them.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Put important agreements in writing and keep copies.</li>
<li>Protect your name, creative work and secrets in practical ways, and respect other people's work.</li>
<li>Treat customer data carefully and check the data protection law that applies to you.</li>
<li>Honesty and fairness build the trust your business depends on.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A phone repair stall has a unit margin of UGX 25,000 and rent of UGX 300,000 a month. What is the break-even number of repairs?
- 10
* 12
- 15
- 25
= 300,000 divided by 25,000 equals 12 repairs.

Q: A bakery has a unit margin of UGX 1,500 per loaf and fixed costs of UGX 1,000,000 a month. About how many loaves must it sell to break even?
- 500
- 600
* 667
- 1,500
= 1,000,000 divided by 1,500 is 666.67, which is rounded up to 667.

Q: A shop starts the month with UGX 300,000, receives UGX 1,000,000 from sales and pays out UGX 900,000. What is the closing cash?
- UGX 100,000
* UGX 400,000
- UGX 1,300,000
- UGX 2,200,000
= 300,000 + 1,000,000 - 900,000 = 400,000.

Q: Why can a business with good sales on paper still run out of cash?
- Because mobile money does not work for business
- Because profit always equals cash
* Because money may be tied up in stock or customers who have not paid
- Because taxes are paid in advance in all cases
= Credit sales and stock purchases take cash out of your hands even when profit looks good.

Q: Which habits help you separate personal and business money? (choose all that apply)
* Keep a separate business account or mobile money line
* Pay yourself a fixed salary on a fixed date
- Take money from the till whenever the family needs it
- Pay business bills from any wallet without writing it down
= Separate accounts and a recorded salary give you clear books; random withdrawals hide the true position.

Q: Which statement about registering a business in Uganda is best?
- Fees and rules never change, so any old information is fine
- Pay a stranger who promises a quick certificate
* Check current requirements with URSB, URA and local authorities, and keep receipts
- Registration is never useful for a small business
= Rules change, so confirm current steps with official bodies and avoid middlemen.

Q: A poultry farmer has a margin of UGX 2,500 per tray and fixed costs of UGX 750,000. After feed prices rise, the margin falls to UGX 1,500. What is the new break-even in trays?
- 300
- 400
* 500
- 750
= 750,000 divided by 1,500 equals 500 trays.

Q: You pay a freelancer to create your logo with no written agreement. What is the main risk?
- The logo will be too colourful
* A later dispute about who owns the work
- Customers will not see the logo
- You will pay too little tax
= Without a written transfer of rights, ownership can be argued over; a short agreement avoids it.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Customers, selling and measuring',
            'summary' => 'Win your first customers, hold honest sales conversations and track the few numbers that tell you whether the business is healthy.',
            'lessons' => [
                [
                    'title'   => 'Marketing and winning your first customers',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose low-cost ways to reach your first 10 to 50 customers and work out what each new customer costs you. This matters because a good product that nobody hears about earns nothing, while careless advertising can eat your capital quickly.</p>

<h3>Start where your customers already are</h3>
<p>Do not begin with expensive adverts. Begin with places your first segment already visits and trusts.</p>
<ul>
<li><strong>People you know and their circles:</strong> tell them exactly what you offer and ask for introductions, not pity purchases.</li>
<li><strong>Community groups:</strong> WhatsApp groups, church or mosque networks, school parent groups, market associations. Follow each group's rules and do not spam.</li>
<li><strong>Social media:</strong> clear photos, real prices, short videos of the work, quick replies.</li>
<li><strong>Physical presence:</strong> a signboard, flyers at the right gate, a stall near where customers walk.</li>
<li><strong>Partnerships:</strong> a school, a shop or a salon that sends customers to you for a small commission.</li>
<li><strong>Referrals:</strong> a thank-you or small discount for a customer who brings another.</li>
</ul>

<h3>Message and proof</h3>
<p>Use your value proposition in the customers' own words. Add proof: photos of before and after, short written feedback from named customers who agreed, a free small trial. Never post fake reviews or photos that are not yours; people find out and trust disappears.</p>

<h3>Customer acquisition cost</h3>
<p><strong>Customer acquisition cost (CAC)</strong> is the total marketing and selling money divided by the number of customers it brought. Add everything: adverts, airtime, transport to meetings, flyers and your paid time if you are paying someone.</p>

<h3>Worked example: the tutoring app</h3>
<p>The team spends UGX 900,000 on posters, school visits and online ads in one month and gains 60 paying students. CAC is 900,000 divided by 60, which is UGX 15,000 per student. Each student pays UGX 10,000 a month, and after a payment fee and hosting the margin is UGX 9,000. The payback time is 15,000 divided by 9,000, about 1.7 months. If students stay on average five months, the margin from one student is 9,000 x 5 = UGX 45,000, three times the CAC. That is healthy. If most students left after one month, the 9,000 margin would not even repay the 15,000 spent, and the business would lose money on each new customer.</p>

<h3>Choose two or three channels, not ten</h3>
<p>Test each channel for two weeks with a small budget, count customers from each and keep the best two. Ask every new customer "How did you hear about us?" and write the answer in your sales book.</p>

<h3>Case scenario</h3>
<p>A poultry farmer paid UGX 600,000 for a large billboard on the highway and got 3 new buyers in two months, a CAC of UGX 200,000 each, though each buyer was worth about UGX 30,000 a month. Meanwhile, a free visit to 8 restaurants with a sample tray brought 5 regular customers at the cost of about UGX 40,000 in transport. <strong>Decision point:</strong> repeat the billboard or put the money into samples and referrals. He chose samples and a referral reward of one free tray for each new restaurant. <strong>Lesson:</strong> expensive does not mean effective. Measure each channel and keep the cheapest ones that work.</p>

<blockquote><p><strong>Quick check:</strong> You spend UGX 500,000 on marketing and win 25 customers. What is the CAC?</p><p>Answer: UGX 20,000, because 500,000 divided by 25 equals 20,000.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why ask every new customer how they heard about you?</p><p>Answer: The answers show which channel works, so you can spend more on it and less on the rest.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List ten places where your first customer segment gathers, online and offline.</li>
<li>Pick two and plan one action in each for the next two weeks, with a small budget.</li>
<li>Prepare one proof item, such as a before-and-after photo or a short customer quote with permission.</li>
<li>Track every enquiry and sale in your book and calculate your CAC at the end.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Begin with trusted, low-cost channels where customers already gather.</li>
<li>Use real proof and honest messages; never fake reviews.</li>
<li>CAC equals total marketing and selling cost divided by new customers.</li>
<li>Compare CAC with what a customer is worth over time and keep the best channels.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Sales conversations and customer relationships',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to run an honest sales conversation, handle common objections and follow up so that interested people become paying, returning customers. This matters because most enquiries do not buy on the first message, and how you listen, explain and follow up decides how many do.</p>

<h3>A simple five-step conversation</h3>
<ol>
<li><strong>Greet and ask:</strong> "What problem are you trying to solve?" Let the customer talk first.</li>
<li><strong>Repeat what you heard:</strong> "So you need the phone fixed before Friday and you worry about quality."</li>
<li><strong>Offer one clear solution:</strong> explain the price, what is included and how long it takes.</li>
<li><strong>Handle questions honestly:</strong> answer what you know, say "I will check" when you do not.</li>
<li><strong>Ask for the order and agree the next step:</strong> "Shall I start now? A half deposit holds the time."</li>
</ol>

<h3>Common objections and good replies</h3>
<table>
<thead><tr><th>Objection</th><th>Weak reply</th><th>Better reply</th></tr></thead>
<tbody>
<tr><td>"It is too expensive."</td><td>Drop the price at once</td><td>Ask what they compared it with, explain what is included, offer a smaller option</td></tr>
<tr><td>"I need to think."</td><td>Push harder</td><td>Ask what questions remain, and agree a follow-up day</td></tr>
<tr><td>"Someone else is cheaper."</td><td>Criticise the competitor</td><td>Explain your difference, such as warranty or speed, politely</td></tr>
<tr><td>"I do not trust online shops."</td><td>Ignore it</td><td>Show past customers' feedback, offer a deposit only, deliver and then collect the balance</td></tr>
</tbody>
</table>

<h3>Be honest about limits</h3>
<p>Never promise what you cannot do, such as "ready in one hour" when you need three. Under-promise and deliver early. Be clear about warranty, refunds and delivery fees before payment. A customer who feels tricked will tell ten others.</p>

<h3>Follow up</h3>
<p>Many sales come on the second or third contact. Keep a simple list: name, date, what they asked, next contact date. Send a short polite message after two days, not five messages in an hour. After a sale, ask whether the product worked and request a short review or a referral.</p>

<h3>Worked example: conversion rate</h3>
<p>A boda-boda delivery startup sends quotes to 50 small shops in a month. 15 agree to a trial, so the conversion rate is 15 divided by 50, which is 30 percent. Of the 15 trial shops, 9 keep using the service. Each regular shop sends about 24 parcels a month, and the company earns UGX 1,500 on each parcel after the rider's share. So the 9 shops give 9 x 24 x 1,500 = UGX 324,000 a month in margin. If follow-up raised the conversion rate to 40 percent, 20 shops would trial and, at the same 60 percent keep rate, 12 would become regular, giving 12 x 24 x 1,500 = UGX 432,000. Better conversations bring more money without any extra advertising.</p>

<h3>Case scenario</h3>
<p>A phone repair stall owner promised same-day repairs to win customers, but waited for parts that came after three days. Angry customers posted complaints in two local groups, and walk-in numbers fell from about 12 a day to 5. <strong>Decision point:</strong> argue online or repair trust. He apologised publicly, offered a free screen protector to affected customers, and began quoting honest times, such as "ready tomorrow afternoon". Within a month numbers recovered to 10 a day. <strong>Lesson:</strong> a broken promise costs more than a slower promise kept.</p>

<blockquote><p><strong>Quick check:</strong> 40 enquiries lead to 12 orders. What is the conversion rate?</p><p>Answer: 30 percent, because 12 divided by 40 is 0.30.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A customer says "Someone else is cheaper". Is it better to criticise that competitor or explain your difference?</p><p>Answer: Explain your difference politely, because attacking others harms trust in you.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write your answers to three objections you expect, using the table style.</li>
<li>Role-play a five-step conversation with a friend and ask for honest feedback.</li>
<li>Create a follow-up list with name, date, next contact and note.</li>
<li>Count enquiries and orders for two weeks and calculate your conversion rate.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Listen first, then offer one clear solution and ask for the order.</li>
<li>Answer objections with questions and facts, not discounts or attacks.</li>
<li>Under-promise, deliver early and be honest about limits.</li>
<li>Track conversion rate and follow up politely.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Metrics that matter',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose and calculate a small set of numbers that show whether your business is healthy, and to avoid being misled by "vanity" numbers. This matters because you can feel busy and popular while the business slowly loses money, and a few simple numbers tell the truth early.</p>

<h3>Vanity numbers and useful numbers</h3>
<p>A <strong>vanity metric</strong> looks good but does not show money or learning, such as page likes or app downloads. A <strong>useful metric</strong> links to cash, repeat customers or learning: money received, repeat buyers, gross margin. Ask of every number: "What decision will this help me make?"</p>

<h3>Seven numbers for a small business</h3>
<table>
<thead><tr><th>Metric</th><th>How to calculate</th><th>What it tells you</th></tr></thead>
<tbody>
<tr><td>Revenue</td><td>Total money from sales</td><td>Size of activity</td></tr>
<tr><td>Gross margin percent</td><td>(Revenue - variable costs) / Revenue x 100</td><td>How much each shilling of sales keeps</td></tr>
<tr><td>Net profit</td><td>Revenue - all costs</td><td>Whether the business pays for itself</td></tr>
<tr><td>Conversion rate</td><td>Orders / enquiries x 100</td><td>How well you sell</td></tr>
<tr><td>Repeat rate</td><td>Customers who buy again / all customers x 100</td><td>Whether people are satisfied</td></tr>
<tr><td>Customer acquisition cost</td><td>Marketing cost / new customers</td><td>What growth costs</td></tr>
<tr><td>Cash runway</td><td>Cash in hand / monthly cash burn</td><td>How many months you can continue</td></tr>
</tbody>
</table>

<h3>Worked example: a bakery month</h3>
<p>The bakery sells 900 loaves at UGX 5,000, so revenue is 4,500,000. Variable cost is 900 x 3,500 = 3,150,000. Gross profit is 4,500,000 - 3,150,000 = 1,350,000, and gross margin is 1,350,000 / 4,500,000 = 30 percent. Fixed costs are 1,000,000, so net profit is 1,350,000 - 1,000,000 = UGX 350,000. Of 120 customers, 78 returned during the month, so the repeat rate is 78 / 120 = 65 percent. These four numbers give the baker a clear picture in five minutes.</p>

<h3>Burn and runway</h3>
<p><strong>Burn</strong> is the net cash you lose each month while the business is not yet profitable. If a young app business has UGX 3,000,000 in cash and loses UGX 600,000 a month, the runway is 3,000,000 / 600,000 = 5 months. After five months it has no money unless sales grow or new funds arrive. Knowing the runway forces you to act early rather than at the last moment.</p>

<h3>Review rhythm</h3>
<p>Write your numbers weekly (a ten-minute habit) and compare month to month. Look at trends, not single days. Pick one number to improve each month, for example repeat rate from 65 to 70 percent.</p>

<h3>Case scenario</h3>
<p>A tutoring app celebrated 2,000 downloads in a month, but only 90 students paid, and 40 of them left the next month. The founders had spent UGX 1,800,000 on adverts, so CAC for the 90 paying students was 20,000 each, more than twice the monthly margin of UGX 9,000, and many left before repaying it. <strong>Decision point:</strong> keep buying downloads or fix retention. They paused adverts, called the students who left, learned that lessons felt too hard, and added short worked examples. The next month, 85 of the next 100 payers stayed. <strong>Lesson:</strong> the downloads were a vanity number; paying students and retention were the numbers that mattered.</p>

<blockquote><p><strong>Quick check:</strong> Revenue is UGX 2,000,000 and variable costs are UGX 1,400,000. What is the gross margin percent?</p><p>Answer: 30 percent, because the gross profit is 600,000 and 600,000 / 2,000,000 equals 0.30.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You have UGX 2,400,000 cash and burn UGX 400,000 a month. How long is your runway?</p><p>Answer: 6 months, because 2,400,000 divided by 400,000 equals 6.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose five numbers from the table that matter most for your business.</li>
<li>Calculate them for last month using your sales and expense books, or use estimates.</li>
<li>Create a simple weekly table with columns for each number.</li>
<li>Pick one number to improve and write one action to do this week.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Prefer numbers that link to money, repeat buying and learning.</li>
<li>Gross margin, net profit, conversion, repeat rate, CAC and runway form a useful set.</li>
<li>Runway equals cash divided by monthly burn.</li>
<li>Review weekly and improve one number at a time.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: You spend UGX 800,000 on marketing in a month and win 40 customers. What is the customer acquisition cost?
- UGX 2,000
- UGX 10,000
* UGX 20,000
- UGX 32,000,000
= 800,000 divided by 40 equals UGX 20,000 per customer.

Q: Which channel is usually the best place to start for a new local business?
- A very expensive billboard
* Places where your first customers already gather and trust, such as community groups and referrals
- Buying thousands of random followers
- Waiting for customers to find you
= Low-cost, trusted channels let you test cheaply and learn what works.

Q: A shop owner gets 50 enquiries and 20 orders. What is the conversion rate?
- 20 percent
- 25 percent
* 40 percent
- 70 percent
= 20 divided by 50 equals 0.40, which is 40 percent.

Q: A customer says "I need to think about it". What is a good response?
- Drop the price immediately by half
- Push harder until they decide
* Ask what questions remain and agree a day to follow up
- Stop all contact
= Respectful follow-up keeps the door open and can reveal the real concern.

Q: Which are useful (not vanity) metrics for a small business? (choose all that apply)
* Gross margin percent
* Repeat rate
- Number of page likes alone
* Cash runway
= Margin, repeat buying and runway link to money and survival; likes alone do not.

Q: A business has UGX 3,600,000 cash and loses UGX 900,000 a month. How long will the cash last at this rate?
- 2 months
* 4 months
- 6 months
- 9 months
= Runway is 3,600,000 divided by 900,000, which is 4 months.

Q: A tutoring app has a CAC of UGX 15,000, a monthly margin of UGX 9,000 per student and an average stay of 5 months. What is the margin from one student compared with the CAC?
- UGX 15,000 which only equals the CAC
* UGX 45,000, which is three times the CAC
- UGX 9,000, which is less than the CAC
- UGX 75,000, which is five times the CAC
= 9,000 x 5 = 45,000, and 45,000 divided by 15,000 is 3.

Q: A repair stall promised same-day repairs but parts took three days, and customers complained. What is the best long-term fix?
- Delete all complaints and promise the same again
* Apologise, repair trust and quote honest times you can keep
- Raise prices to hide the problem
- Stop answering messages
= Honest, achievable promises protect trust; a broken promise costs more than a slower promise kept.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: People, funding and risk',
            'summary' => 'Build a team and partnerships with clear agreements, compare funding options with caution and learn to handle risk, failure and pivots.',
            'lessons' => [
                [
                    'title'   => 'Teams, partners and co-founders',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to decide whether you need a partner or co-founder, agree roles and ownership fairly, and use simple written agreements to avoid quarrels. This matters because many promising businesses break up not because of the market but because friends never wrote down who owns what and who does what.</p>

<h3>Do you need a co-founder?</h3>
<p>Working alone is simple but heavy. A co-founder can add skills, share risk and keep you going, yet a bad partner can ruin a business. Ask: what skills are missing, can I pay for them instead (a freelancer, a part-time helper), and do I trust this person under stress?</p>

<h3>Choosing well</h3>
<ul>
<li>Choose complementary skills: one sells and manages people, another builds or produces.</li>
<li>Test the partnership first by working together on a small project for a few weeks.</li>
<li>Discuss hard topics early: time each person will give, money each can invest, what happens if someone leaves.</li>
<li>Be careful with "friend only" partnerships that avoid awkward questions.</li>
</ul>

<h3>Splitting ownership fairly</h3>
<p>Ownership is often called <strong>equity</strong>, meaning a share of the business. Avoid splitting 50/50 by default if contributions differ. Decide using time, money, skills and risk. A common safe idea is <strong>vesting</strong>: each founder earns their share over time, for instance over three or four years, so that someone who leaves after a month does not keep a big share. Ask a lawyer or an experienced adviser to help write the exact terms.</p>

<h3>The founders' agreement checklist</h3>
<ol>
<li>Names of the partners and what the business does.</li>
<li>Who owns what share, and how that may change.</li>
<li>Roles and who decides what.</li>
<li>Money put in, loans and salaries.</li>
<li>How profits are shared and when.</li>
<li>What happens if a partner leaves, falls ill, or breaks the agreement.</li>
<li>How disagreements will be solved, for example a named trusted mediator.</li>
</ol>

<h3>Worked example: sharing profit by contribution</h3>
<p>Two partners start a tailoring shop. Amina puts in UGX 3,000,000 and works full-time. Joel puts in UGX 1,000,000 and works part-time. They agree profits are split 70 percent to Amina and 30 percent to Joel after paying each person a basic wage for their work. In a month with UGX 800,000 profit after wages, Amina gets 70 percent, which is UGX 560,000, and Joel gets 30 percent, which is UGX 240,000, and the total is 800,000. Because the rule is written down, nobody argues about the numbers.</p>

<h3>Hiring helpers</h3>
<p>Even with one person you may need helpers. Write what the work is, pay, hours and how long the arrangement lasts. Pay on time. Check labour and tax obligations with the relevant authorities, as they may apply once you have employees. A small, clear job description prevents confusion.</p>

<h3>Case scenario</h3>
<p>Three friends started a boda-boda delivery business and shared everything equally with nothing in writing. One friend stopped working after four months but still expected a third of the profit. The other two worked seven days a week. The group quarrelled, customers noticed and the business closed with UGX 1,400,000 of unpaid supplier debts. <strong>Decision point:</strong> split by roles and time, or carry on in anger. The two who stayed later started again with a written agreement and vesting. <strong>Lesson:</strong> unwritten sharing feels friendly at first and turns bitter when effort differs. Agree early, in writing.</p>

<blockquote><p><strong>Quick check:</strong> A business makes UGX 1,000,000 profit with a 60/40 split. How much does the 40 percent partner receive?</p><p>Answer: UGX 400,000, because 40 percent of 1,000,000 is 400,000.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is the purpose of vesting?</p><p>Answer: It makes founders earn their shares over time, so someone who leaves early does not keep a big share.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List the skills your business needs and mark the ones you lack.</li>
<li>Decide for each missing skill whether to hire, partner or learn.</li>
<li>If you have a partner, write a one-page agreement using the seven-point checklist.</li>
<li>Agree a date to review it after six months.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Choose partners for complementary skills and test the partnership first.</li>
<li>Split ownership using contribution and consider vesting.</li>
<li>Put roles, money, profit sharing and exit rules in writing.</li>
<li>Treat helpers fairly and check obligations as you hire.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Funding options: savings, loans, grants and investors',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to compare the main ways to fund a startup, work out the real cost of a loan, and spot risky or fake offers. This matters because the wrong funding can sink a good business, and the best first funding is often small and slow. This lesson is general education, not financial or investment advice. Check every offer carefully and consider talking to a trusted adviser.</p>

<h3>Main options</h3>
<table>
<thead><tr><th>Source</th><th>What it is</th><th>Watch out for</th></tr></thead>
<tbody>
<tr><td>Savings and family</td><td>Your own money and help from relatives</td><td>Write down terms with family; do not risk money needed for food, rent or school fees</td></tr>
<tr><td>Revenue and pre-orders</td><td>Money from customers before or as you deliver</td><td>Slower growth, but no debt</td></tr>
<tr><td>Savings groups and SACCOs</td><td>Member-owned groups that save and lend</td><td>Know the rules, interest and what happens if you miss payments</td></tr>
<tr><td>Bank or microfinance loan</td><td>Borrowed money repaid with interest</td><td>Total cost, security demanded and penalties</td></tr>
<tr><td>Grants and competitions</td><td>Money you do not repay, usually with conditions and reports</td><td>Competitive, slow, and scams that ask you to pay first</td></tr>
<tr><td>Angel investors and equity</td><td>Individuals who give money for a share of the business</td><td>You give away part of ownership and control</td></tr>
</tbody>
</table>

<h3>Debt versus equity</h3>
<p><strong>Debt</strong> is borrowed money you must repay with interest, whether the business does well or not. <strong>Equity</strong> is money from an investor in exchange for a share of ownership; if the business fails they usually lose their money, but if it succeeds they share the rewards. Debt keeps ownership but adds pressure; equity shares ownership but removes fixed repayments. Neither is better in every case.</p>

<h3>Worked example: the real cost of a loan</h3>
<p>A poultry farmer wants to borrow UGX 2,000,000 for 12 months at a flat rate of 3 percent a month, a rate used here only as an example. Monthly interest is 3 percent of 2,000,000, which is UGX 60,000. Over 12 months, interest is 12 x 60,000 = UGX 720,000, so the total to repay is 2,720,000. Each month the repayment is 2,720,000 divided by 12, about UGX 226,667. If the business margin from the extra birds is only UGX 150,000 a month, the loan loses money each month. Before borrowing, ask: will the extra income clearly exceed the repayment, even in a bad month?</p>

<h3>Worked example: giving away equity</h3>
<p>An investor offers UGX 10,000,000 for 20 percent of a tutoring app company. That means the investor values the whole company at 10,000,000 divided by 0.20, which is UGX 50,000,000. If you later build the company to a higher value, 20 percent is gone for good. Only accept if the money truly speeds growth and the terms are explained in writing and understood.</p>

<h3>Questions to ask before you take money</h3>
<ol>
<li>What is the total I will repay or give away, in shillings and in percent?</li>
<li>What happens if I am late or the business does not do well?</li>
<li>Is there security such as land or a car, and can I afford to lose it?</li>
<li>Is it written, and have I read all of it?</li>
<li>Is the lender or investor properly registered or known by people I trust?</li>
</ol>

<h3>Warning signs</h3>
<p>Be careful of anyone who asks for an upfront fee to release a loan or grant, promises guaranteed returns, rushes you to sign, or refuses to give written terms. Never share your PIN or passwords. Do not borrow from informal lenders with hidden or extreme charges. Report suspected fraud to the police.</p>

<h3>Case scenario</h3>
<p>A young founder received a message offering a "guaranteed business grant of UGX 5,000,000" after a fee of UGX 150,000 paid by mobile money. She paid; nothing came and the number went off. <strong>Decision point:</strong> chase more or learn. She reported it, told her friends, and began saving through a group while selling pre-orders. <strong>Lesson:</strong> real funders do not charge you to receive money. Check every offer with the official body or a trusted adviser first.</p>

<blockquote><p><strong>Quick check:</strong> A loan of UGX 1,000,000 has a flat interest of UGX 20,000 a month for 10 months. What is the total to repay?</p><p>Answer: UGX 1,200,000, because interest is 200,000 over 10 months and 1,000,000 + 200,000 = 1,200,000.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Someone offers a grant but asks you to pay a fee first. What should you do?</p><p>Answer: Do not pay; real grants do not ask you to pay to receive money, so treat it as a likely scam.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write how much money you truly need and what exactly it will buy.</li>
<li>List three funding sources that fit and note the total cost of each.</li>
<li>Calculate the monthly repayment on any loan you are considering and compare it with your expected extra margin.</li>
<li>Write five questions you will ask any lender or investor.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Start with the least costly money: savings, pre-orders and revenue.</li>
<li>Debt must be repaid; equity gives away ownership.</li>
<li>Always work out the full cost of a loan and test whether income covers repayment.</li>
<li>Avoid upfront-fee offers and always get written terms.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Risk, failure, learning and pivoting',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to list the main risks in your business, set simple limits on losses, learn from setbacks and decide calmly whether to continue, change direction or stop. This matters because every business meets problems, and the owners who last are the ones who learn fast and protect themselves from losses they cannot afford.</p>

<h3>Common risks for small businesses</h3>
<ul>
<li><strong>Market risk:</strong> customers do not buy enough or buy elsewhere.</li>
<li><strong>Cash risk:</strong> money runs out because customers pay late or costs rise.</li>
<li><strong>Supplier risk:</strong> one supplier raises prices or disappears.</li>
<li><strong>People risk:</strong> a key worker or partner leaves.</li>
<li><strong>Legal and safety risk:</strong> missing permits, unsafe food, broken contracts.</li>
<li><strong>Personal risk:</strong> illness, burnout, family emergencies.</li>
</ul>

<h3>A simple risk table</h3>
<p>For each risk write how likely it is (low, medium, high), how bad it would be, and what you will do to reduce it. Start with the risks that are both likely and costly.</p>
<table>
<thead><tr><th>Risk</th><th>Likelihood</th><th>Impact</th><th>Action</th></tr></thead>
<tbody>
<tr><td>Feed price rises (poultry)</td><td>High</td><td>High</td><td>Buy in bulk when cheap, keep a reserve, review price monthly</td></tr>
<tr><td>Rider leaves (delivery)</td><td>Medium</td><td>Medium</td><td>Train a backup rider</td></tr>
<tr><td>Spare parts delayed (repair stall)</td><td>Medium</td><td>Medium</td><td>Two suppliers and common parts in stock</td></tr>
</tbody>
</table>

<h3>Set a loss limit</h3>
<p>Decide before you start how much you can afford to lose. For example: "I will spend at most UGX 500,000 and three months testing. If by day 90 fewer than 20 customers have paid, I will change the plan or stop." This stops you from throwing good money after bad. Never risk money needed for rent, food, school fees or health.</p>

<h3>What failure teaches</h3>
<p>Failure is painful, but a small, early failure is cheap tuition. After any setback, write three lines: what happened, why it happened, what I will do differently. Share what you learned with a mentor or a trusted friend.</p>

<h3>Pivoting: changing direction with evidence</h3>
<p>A <strong>pivot</strong> is a deliberate change of one big part of the plan, such as the customer, the product or the price, based on what you learned. A pivot is not quitting. It means using the evidence you have gathered.</p>

<h3>Worked example: stop, continue or pivot</h3>
<p>A tutoring app tested for three months. Spend was UGX 1,500,000. Result: 40 paying students at UGX 10,000 each, which is UGX 400,000 a month, but only 12 stayed after month two. Teacher feedback showed that students stopped when lessons were too long on a phone screen. The team tested 10-minute lessons for 30 new students and 24 stayed. Retention rose from 30 percent (12 of 40) to 80 percent (24 of 30). That is a change in the product, based on evidence, not a new idea each week.</p>

<h3>Case scenario</h3>
<p>A founder kept a bakery going for ten months after his own records showed a monthly loss of about UGX 300,000, hoping "the festive season will fix it". He borrowed from relatives to cover the gap and lost UGX 3,000,000 in total before closing. <strong>Decision point:</strong> he could have set a three-month review with a loss limit and tested delivery to schools. Later, in a new venture, he agreed review dates in advance and stopped a weak product after two months. <strong>Lesson:</strong> hope is not a plan. Write the limit, review on schedule and change course on evidence.</p>

<blockquote><p><strong>Quick check:</strong> You set a loss limit of UGX 500,000. You have already spent UGX 450,000 with no sign of customers. What should you do?</p><p>Answer: Review honestly and change or stop, because the limit protects you from a loss you cannot afford.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is a pivot?</p><p>Answer: A deliberate change to a big part of your plan, such as customer or product, based on evidence.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write your top five risks in a table with likelihood, impact and one action each.</li>
<li>Set a loss limit and a review date for your business and write them down.</li>
<li>Think of one setback you have had and write the three-line lesson.</li>
<li>Tell one trusted person your limit so they can remind you.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>List risks, rank them and act on the likely and costly ones first.</li>
<li>Set a loss limit and review date before you start.</li>
<li>Learn from setbacks in writing and share the lessons.</li>
<li>Pivot only on evidence, and stop when the limit is reached.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Two founders start a business with nothing in writing and later disagree about shares. What would have helped most?
- A bigger signboard
* A written founders' agreement covering roles, ownership, money and exit
- More advertising
- Hiring a third partner
= A written agreement settles roles, ownership and what happens if someone leaves before disagreements grow.

Q: What is the main benefit of vesting for founders?
- It guarantees profit every month
- It removes the need for any agreement
* People earn their shares over time, so someone leaving early does not keep a big share
- It reduces tax automatically
= Vesting links ownership to continued contribution.

Q: A business makes UGX 900,000 profit with a 70/30 split. How much does the 30 percent partner receive?
- UGX 90,000
- UGX 210,000
* UGX 270,000
- UGX 630,000
= 30 percent of 900,000 is 270,000; the 70 percent partner receives 630,000.

Q: What is the difference between debt and equity?
- Debt gives ownership and equity must be repaid
* Debt must be repaid with interest; equity gives the investor a share of ownership
- They are the same thing
- Both always cost nothing
= Debt creates a repayment duty, equity shares ownership and future rewards.

Q: A farmer borrows UGX 1,500,000 for 10 months with flat interest of UGX 45,000 per month. How much must he repay in total?
- UGX 1,545,000
- UGX 450,000
* UGX 1,950,000
- UGX 1,500,000
= Interest is 45,000 x 10 = 450,000, and 1,500,000 + 450,000 = 1,950,000.

Q: Someone you do not know offers a business grant of UGX 5,000,000 if you first pay a UGX 150,000 "processing fee" by mobile money. What is the wisest response?
- Pay quickly before the offer closes
- Pay half now and half later
- Share your PIN so they can verify you
* Do not pay; real funders do not charge you to receive money, so treat it as a likely scam
= Upfront-fee offers with pressure are a classic fraud pattern.

Q: You set a loss limit of UGX 500,000 and 90 days. At day 90 you have spent UGX 480,000 and have only 5 paying customers against a target of 20. What should you do?
- Borrow more and continue unchanged
* Review the evidence honestly and either change the plan on that evidence or stop
- Ignore the limit because you are close to the end
- Hide the numbers from partners
= The limit and target were set to protect you; the evidence says the current plan is not working.

Q: A tutoring app changes lesson length after learning why students leave, and retention rises from 12 of 40 students to 24 of 30. Which statement is correct?
- Retention fell from 30 percent to 80 percent
* Retention rose from 30 percent to 80 percent, a pivot based on evidence
- Retention stayed at 40 percent
- The product change had no effect
= 12 / 40 = 30 percent and 24 / 30 = 80 percent; the change came from customer evidence.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 6
        [
            'title'   => 'Module 6: Delivering, scaling and your 90-day plan',
            'summary' => 'Deliver consistently, grow only when the numbers allow it and turn everything you learned into a realistic 90-day launch plan.',
            'lessons' => [
                [
                    'title'   => 'Delivering consistently: operations, suppliers and quality',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe your delivery process step by step, reduce waste, manage suppliers and stock, and keep quality steady. This matters because customers return for a reliable result, and small leaks such as waste, delays and mistakes quietly eat the margin you worked so hard to calculate.</p>

<h3>Write down how the work is done</h3>
<p>A <strong>process</strong> is the list of steps from an order to a happy customer. Write it as numbered steps. For an online tailoring shop: take the order and deposit, take measurements by video call, buy or cut fabric, sew, check quality, deliver, collect the balance, ask for feedback. Once written, you can train helpers, find slow steps and spot where mistakes happen.</p>

<h3>Quality checks</h3>
<ul>
<li>Create a short checklist for each job, for example "seams straight, buttons firm, measurements match, ironed, packed".</li>
<li>Check before delivery, not after the complaint.</li>
<li>Record each complaint and what you changed because of it.</li>
<li>For food, follow hygiene and food safety rules and the requirements of the relevant authorities.</li>
</ul>

<h3>Suppliers and stock</h3>
<p>Your suppliers are partners. Keep at least two for your key inputs, agree prices and delivery in writing, pay on time and keep their numbers handy. Hold enough stock for the usual number of days between deliveries, but not so much that money sleeps on shelves or items spoil. Count stock regularly and compare with your records to detect loss or theft.</p>

<h3>Worked example: waste in a bakery</h3>
<p>A bakery bakes 100 loaves a day and sells 85. Revenue is 85 x 5,000 = UGX 425,000. Cost is 100 x 3,500 = 350,000, so daily profit before fixed costs is 75,000. The 15 unsold loaves cost 15 x 3,500 = UGX 52,500 a day. After two weeks of records, the baker sees that demand is about 85 on most days. She bakes 90 and adds a small second batch only if the shop runs out by noon. The cost becomes 90 x 3,500 = 315,000, revenue stays 425,000 and profit rises to 110,000 a day. Over 26 trading days that is 35,000 x 26 = UGX 910,000 more profit a month, with no change in sales.</p>

<h3>Capacity and delivery promises</h3>
<p>Know the most you can deliver. If one tailor makes 12 dresses a month and a campaign brings 40 orders, you will either fail to deliver or rush and spoil quality. Promise delivery times based on your real capacity and tell customers early if something is delayed.</p>

<h3>Case scenario</h3>
<p>A delivery startup got a flood of orders after a good social media post. It had four riders, each able to do about 15 parcels a day, 60 in total, but orders reached 140 on the first day. Parcels arrived late, two went missing and many customers left bad reviews. <strong>Decision point:</strong> accept every order or limit orders to capacity. They paused new sign-ups for a week, called affected customers with apologies and refunds, trained two more riders and set a daily limit with a waiting list. <strong>Lesson:</strong> growth that you cannot serve is harmful. Match promotion to capacity.</p>

<blockquote><p><strong>Quick check:</strong> A bakery bakes 90 loaves and sells 85, each loaf costing UGX 3,500. How many shillings of loaves are unsold?</p><p>Answer: UGX 17,500, because 5 unsold loaves x 3,500 equals 17,500.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why keep two suppliers for a key input?</p><p>Answer: If one fails or raises prices, you can still get the item and keep serving customers.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write your delivery process as numbered steps from order to feedback.</li>
<li>Create a five-point quality checklist and use it for a week.</li>
<li>Record daily waste or unsold stock for seven days and calculate its cost.</li>
<li>Write your real weekly capacity and what you will do if orders exceed it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Write down each step so that work can be taught and improved.</li>
<li>Check quality before delivery and learn from complaints.</li>
<li>Track waste and stock; small daily losses add up quickly.</li>
<li>Promote only as much as your capacity can serve well.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Growing and scaling safely',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to judge whether your business is ready to grow, compare ways to grow, and test the money maths before expanding. This matters because many businesses that survive the first year fail when they expand too early, with borrowed money and no proven system.</p>

<h3>Growth and scale are different</h3>
<p><strong>Growth</strong> means more sales, often with more costs. <strong>Scaling</strong> means sales can grow much faster than costs because the process is repeatable. A single tailor who sews every dress can grow only as fast as her hours allow; a shop with trained tailors, a checklist and an ordering system can scale further.</p>

<h3>Signs you may be ready</h3>
<ol>
<li>Your unit margin is positive and you know it.</li>
<li>You regularly pass break-even for at least three months.</li>
<li>Customers come back or refer others.</li>
<li>Your process is written and someone else can follow it.</li>
<li>Your cash flow can survive a few slow weeks.</li>
<li>You are turning customers away or delivering late because of capacity.</li>
</ol>
<p>If several of these are missing, fix them first. Scaling a weak model only makes the losses bigger.</p>

<h3>Ways to grow</h3>
<table>
<thead><tr><th>Way</th><th>Example</th><th>Risk</th></tr></thead>
<tbody>
<tr><td>Sell more to current customers</td><td>A bakery adds cakes for the same schools</td><td>Low</td></tr>
<tr><td>Reach new customers with the same offer</td><td>An online tailor delivers to a second town</td><td>Medium</td></tr>
<tr><td>Add a new product</td><td>A repair stall also sells phone covers</td><td>Medium</td></tr>
<tr><td>Open a second location</td><td>A second repair stall</td><td>High</td></tr>
<tr><td>License the model to partners</td><td>Others use your brand and system for a fee</td><td>Needs legal advice</td></tr>
</tbody>
</table>

<h3>Worked example: is a second stall worth it?</h3>
<p>The first stall earns UGX 25,000 per screen and breaks even at 12 screens a month on rent of 300,000. It now repairs 30 screens a month, so the first stall earns 30 x 25,000 = 750,000 minus 300,000 = UGX 450,000. A second stall would cost rent 300,000 plus a technician paid 250,000, a total of UGX 550,000 a month. Break-even for the second stall is 550,000 divided by 25,000 = 22 screens a month. The owner must be sure the new location can bring in 22 screens a month just to cover costs, and she needs cash for set-up and stock. She decides to test with a temporary stand two days a week first, which costs very little and shows real demand.</p>

<h3>Protect the core</h3>
<p>While you expand, keep your first business healthy. Do not take cash from the working business to fund an untested one. Keep a cash reserve covering at least a few weeks of fixed costs. Hire slowly and train well. Keep measuring the numbers from the metrics lesson, as growth can hide problems.</p>

<h3>Case scenario</h3>
<p>A bakery owner with a good first shop borrowed UGX 6,000,000 and opened two more shops in the same month. Each new shop sold only about 40 loaves a day against a break-even of 70. Quality dropped because she was always travelling, and the loan repayments used the first shop's profit. Within six months the cash was gone and two shops closed. <strong>Decision point:</strong> she could have waited and tested one new location slowly. She later reopened a single new shop with a trained manager and checklists, and reached break-even in three months. <strong>Lesson:</strong> expand one step at a time and only after proof that the model repeats.</p>

<blockquote><p><strong>Quick check:</strong> A new stall has fixed costs of UGX 500,000 a month and a unit margin of UGX 20,000. What is its break-even number of units?</p><p>Answer: 25 units, because 500,000 divided by 20,000 equals 25.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why test a second location cheaply first?</p><p>Answer: A cheap test shows real demand before you commit to rent, wages and loans that are hard to undo.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Tick off the six readiness signs for your business and write honestly what is missing.</li>
<li>Pick one low-risk growth option and write its extra costs and the sales needed to break even.</li>
<li>Design a small, cheap test of that option lasting two to four weeks.</li>
<li>Write the cash reserve you will keep untouched while you test.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Scaling means sales grow faster than costs because the process repeats.</li>
<li>Check readiness signs before expanding.</li>
<li>Compute break-even for any new site or product before you commit.</li>
<li>Test cheaply, keep a reserve and protect the core business.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Your 90-day launch plan',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a practical 90-day plan with clear goals each month, a start-up budget, a weekly routine and a decision point at the end. This matters because a plan with dates and numbers turns months of "someday" into steady action, and 90 days is long enough to learn a lot but short enough to stay honest.</p>

<h3>The three phases</h3>
<table>
<thead><tr><th>Days</th><th>Focus</th><th>Typical tasks</th></tr></thead>
<tbody>
<tr><td>1-30: Learn and test</td><td>Prove that people want it</td><td>Interviews, problem sentence, cheap test, value proposition, first costs and break-even</td></tr>
<tr><td>31-60: Launch small</td><td>Serve real customers</td><td>Set price, start records, simple registration steps, first sales, first marketing channels</td></tr>
<tr><td>61-90: Improve and decide</td><td>Learn from numbers</td><td>Review metrics, fix leaks, build repeat customers, decide to continue, pivot or stop</td></tr>
</tbody>
</table>

<h3>Set three numbers for the 90 days</h3>
<ul>
<li><strong>Customer goal:</strong> how many paying customers.</li>
<li><strong>Money goal:</strong> monthly revenue or margin you aim for.</li>
<li><strong>Learning goal:</strong> the most important question you must answer, for example "Will customers come back?"</li>
</ul>

<h3>Worked example: a phone repair stall</h3>
<p>Brian plans a screen repair stall. His start-up budget:</p>
<table>
<thead><tr><th>Item</th><th>UGX</th></tr></thead>
<tbody>
<tr><td>Tools and a workbench</td><td>400,000</td></tr>
<tr><td>First parts stock</td><td>1,000,000</td></tr>
<tr><td>Rent deposit and first month</td><td>600,000</td></tr>
<tr><td>Signboard and flyers</td><td>150,000</td></tr>
<tr><td>Total start-up</td><td>2,150,000</td></tr>
</tbody>
</table>
<p>The sum is 400,000 + 1,000,000 + 600,000 + 150,000 = 2,150,000. He adds a three-month cash buffer for rent (3 x 300,000 = 900,000), so he needs about UGX 3,050,000 in total. His unit margin is 25,000 per screen and break-even is 12 screens a month. His 90-day targets: month 1, prepare and test with 5 pre-booked repairs; month 2, 12 screens (break-even); month 3, 20 screens, which would give 20 x 25,000 = 500,000 minus 300,000 rent, so UGX 200,000 left. At day 90 he reviews: if he is below 12 a month, he checks marketing, price and location and decides whether to change or stop within his loss limit.</p>

<h3>The weekly routine</h3>
<ol>
<li>Monday: review last week's numbers (sales, costs, customers, cash).</li>
<li>Midweek: do your main growth action, for example visit five shops or post to three groups.</li>
<li>Friday: follow up with enquiries and ask customers for feedback.</li>
<li>Sunday: plan the next week and pay yourself your fixed amount if there is cash.</li>
</ol>

<h3>Common mistakes</h3>
<ul>
<li>A plan with no dates or numbers.</li>
<li>Spending the whole budget in week one.</li>
<li>Skipping records until "later".</li>
<li>Ignoring the decision point at day 90 and drifting on.</li>
</ul>

<h3>Case scenario</h3>
<p>Two young founders rented a shop and bought stock for UGX 5,000,000 in the first week, before any customer test. They did not track sales. After three months they had sold few items, could not say why, and had less than UGX 300,000 left. <strong>Decision point:</strong> they could have started with a stall and a 90-day plan. A third founder with a similar idea began with UGX 800,000, pre-sold 12 orders, kept weekly numbers and at day 90 knew exactly which product to drop. <strong>Lesson:</strong> start small, track weekly and decide on evidence.</p>

<blockquote><p><strong>Quick check:</strong> Start-up items of UGX 300,000, 700,000 and 250,000 are needed. What is the total?</p><p>Answer: UGX 1,250,000, because 300,000 + 700,000 + 250,000 equals 1,250,000.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What should you do at day 90?</p><p>Answer: Review your numbers and decide honestly to continue, change direction or stop, based on the limit you set earlier.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write your customer, money and learning goals for 90 days.</li>
<li>List your start-up costs with real prices and add a buffer for at least three months of fixed costs.</li>
<li>Fill in a three-column plan for days 1-30, 31-60 and 61-90 with dates.</li>
<li>Choose your weekly review day and tell one trusted person your plan and your day-90 decision rule.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use three phases: learn and test, launch small, improve and decide.</li>
<li>Set a customer goal, a money goal and a learning goal.</li>
<li>Budget start-up costs plus a cash buffer, and keep weekly records.</li>
<li>Decide honestly at day 90, using the evidence you gathered.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A bakery bakes 100 loaves a day and sells 85. Each loaf costs UGX 3,500 to make. What is the cost of the unsold loaves each day?
- UGX 15,000
- UGX 35,000
* UGX 52,500
- UGX 85,000
= 15 unsold loaves x 3,500 equals UGX 52,500.

Q: Why should a business keep at least two suppliers for a key input?
- So prices always fall
* So it can keep serving customers if one supplier fails or raises prices
- Because one supplier is illegal
- To avoid keeping records
= A second source protects supply and your bargaining position.

Q: A delivery startup gets 140 orders in a day but can deliver 60. What is the best response?
- Accept all orders and hope for the best
* Limit orders to capacity, communicate honestly and add capacity before promoting more
- Delete the delivery promise
- Stop recording complaints
= Promising more than you can deliver damages trust; match promotion to capacity.

Q: Which are signs a business may be ready to scale? (choose all that apply)
* Unit margin is positive and known
* The process is written and others can follow it
- The owner feels bored
* Customers regularly come back
= Positive margin, a repeatable process and repeat customers show a model that can repeat; boredom is not evidence.

Q: A second stall will have fixed costs of UGX 550,000 a month and a unit margin of UGX 25,000. What is its break-even number of units?
- 12
- 20
* 22
- 25
= 550,000 divided by 25,000 equals 22 units.

Q: What is the safest first step before opening a second location?
- Borrow the largest amount possible
* Run a small, cheap test, such as a temporary stand, and measure real demand
- Close the first location
- Skip the numbers because the first one worked
= A cheap test shows demand before you commit to rent, wages and loans.

Q: A start-up budget has items of UGX 400,000, 1,000,000, 600,000 and 150,000. What is the total?
- UGX 1,950,000
- UGX 2,050,000
* UGX 2,150,000
- UGX 2,250,000
= 400,000 + 1,000,000 + 600,000 + 150,000 = 2,150,000.

Q: At day 90 your sales are below your break-even and you have reached your loss limit. What is the best action?
- Ignore the limit and continue unchanged
- Hide the results
* Review the evidence and decide honestly to change the plan or stop
- Take a bigger loan without a plan
= The limit and review date exist so that you decide on evidence before losses grow.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which best describes a trading business?
- It makes the product itself
- It sells only skills and time
* It buys goods and resells them
- It is always a social enterprise
= Trading means buying goods and selling them again, such as a phone accessories stall.

Q: Which are signs of a problem worth solving? (choose all that apply)
* It happens often
* It costs people real time or money
* People already pay something to cope with it
- A relative says it is a nice idea
= Frequency, pain and existing spending are strong evidence; a relative's opinion is not.

Q: Which question best helps you learn from a customer interview?
- Would you like my product?
* When did this last happen and what did you do?
- Is my price fair?
- Do you think I will succeed?
= Questions about real past behaviour give more reliable answers than opinions about the future.

Q: Mercy's rule says she needs at least 5 enquiries per 100 viewers. Only 4 out of 100 asked to order. What should she conclude?
- She succeeded because 4 is a good number
- She should ignore her rule
* She did not meet her rule, so she should improve the offer or message and test again before spending more
- She must open a shop immediately
= 4 percent is below the 5 percent rule she set before the test, so the idea needs adjusting.

Q: Which is an outcome-focused statement for a phone repair stall?
- We stock many tools
* Your phone is working again within one hour
- We have a blue signboard
- We opened last year
= An outcome describes the change for the customer, not a feature of the business.

Q: A tutoring team can reach 15 schools with 200 target students each. If 8 percent will pay UGX 10,000 a month, what is the monthly income?
- UGX 240,000
- UGX 1,200,000
* UGX 2,400,000
- UGX 24,000,000
= 15 x 200 = 3,000 students; 8 percent is 240 students; 240 x 10,000 = UGX 2,400,000.

Q: In the business model canvas, "boda-boda riders and fuel stations" belong in which box?
- Revenue streams
* Key partners
- Customer segments
- Value proposition
= Riders and fuel stations are outside parties that help the business deliver.

Q: An online tailor sells a dress for UGX 80,000. Fabric costs 30,000, tailor pay 20,000 and delivery 5,000. What is the unit margin?
- UGX 20,000
* UGX 25,000
- UGX 30,000
- UGX 55,000
= Variable cost is 55,000, so the margin is 80,000 minus 55,000, which is 25,000.

Q: A repair costs UGX 60,000 and the part costs UGX 35,000. The owner gives a 10 percent discount. What is the new unit margin?
- UGX 25,000
- UGX 24,000
* UGX 19,000
- UGX 14,000
= 10 percent of 60,000 is 6,000, so the price is 54,000 and 54,000 minus 35,000 equals 19,000.

Q: Fixed costs are UGX 450,000 a month and the unit margin is UGX 1,500. What is the break-even number of units?
- 150
- 250
* 300
- 450
= 450,000 divided by 1,500 equals 300 units.

Q: A bakery has fixed costs of UGX 900,000 a month and a margin of UGX 1,500 per loaf. About how many loaves a day (over 30 days) must it sell to break even?
- 10
* 20
- 30
- 60
= 900,000 divided by 1,500 equals 600 loaves a month, or 20 a day.

Q: A shop starts the month with UGX 400,000, receives UGX 2,000,000 in sales and pays out UGX 1,700,000. What is the cash at month end?
- UGX 300,000
- UGX 2,300,000
* UGX 700,000
- UGX 4,100,000
= 400,000 + 2,000,000 - 1,700,000 = 700,000.

Q: Why is "profit" different from "cash"?
- Profit is always larger than cash
* Cash may be tied up in stock or unpaid customer credit even when profit looks good
- They are always the same
- Cash is only used for tax
= Credit sales and stock purchases can leave you with less cash than your profit suggests.

Q: Which habits help keep personal and business money separate? (choose all that apply)
* Pay yourself a fixed salary
* Use a separate business account or mobile money line
- Pay family bills from the till without recording them
* Record every owner withdrawal
= Separate accounts, a salary and recorded withdrawals keep books honest; unrecorded withdrawals hide the truth.

Q: Which mobile money habit is safest?
- Share your PIN with a helpful agent
* Confirm incoming payments with the official message on your own phone
- Accept a screenshot as proof of payment
- Use the same PIN for everything and tell your staff
= Official messages on your own phone are reliable; screenshots can be faked and PINs must stay private.

Q: Which is the best general advice on registering a business in Uganda?
- Rules never change, so use a friend's old advice
* Check current requirements with URSB, URA and local authorities, keep receipts and avoid middlemen
- Pay a stranger on the street for a quick certificate
- Never register because it is useless
= Official bodies give current rules and fees; unofficial helpers and old advice are risky.

Q: A freelancer builds your app. What should the agreement say about the code?
- Nothing, because trust is enough
* That the rights pass to your business after payment, and that the freelancer will not copy it
- That the freelancer can sell it to anyone
- That payment is never needed
= A written transfer of rights avoids disputes about who owns the work.

Q: Which good habits protect customer data? (choose all that apply)
* Collect only what you need
* Keep it secure with passwords and a locked phone
- Share it with other businesses for free
* Check the data protection law that applies to you
= Minimal collection, security and legal awareness protect customers; sharing without permission does not.

Q: You spend UGX 1,200,000 on marketing and win 80 customers. What is your CAC?
- UGX 1,500
- UGX 12,000
* UGX 15,000
- UGX 96,000,000
= 1,200,000 divided by 80 equals UGX 15,000.

Q: 60 enquiries lead to 18 orders. What is the conversion rate?
- 18 percent
- 25 percent
* 30 percent
- 42 percent
= 18 divided by 60 equals 0.30, or 30 percent.

Q: A customer says another seller is cheaper. What is the best reply?
- Insult the other seller
- Drop the price to below cost at once
* Politely explain what is different about your service, such as speed or warranty, and offer options
- End the conversation
= Explaining your difference respectfully builds trust and avoids unnecessary discounts.

Q: A business has UGX 4,500,000 in cash and loses UGX 750,000 a month. What is its runway?
- 3 months
- 5 months
* 6 months
- 9 months
= 4,500,000 divided by 750,000 equals 6 months.

Q: Revenue is UGX 3,000,000 and variable costs are UGX 2,100,000. What is the gross margin percent?
- 20 percent
* 30 percent
- 70 percent
- 90 percent
= Gross profit is 900,000 and 900,000 / 3,000,000 equals 0.30, or 30 percent.

Q: Why should co-founders write an agreement early?
- To impress customers
* To set roles, ownership, money and exit rules before quarrels start
- To avoid doing any work
- Because it is always free of any disagreement
= A written agreement settles expectations when effort or circumstances change.

Q: Someone wants to borrow UGX 1,000,000 for 12 months at flat interest of UGX 20,000 a month. How much will they repay in total?
- UGX 1,020,000
- UGX 1,200,000
* UGX 1,240,000
- UGX 1,000,000
= Interest is 20,000 x 12 = 240,000, so the total is 1,000,000 + 240,000 = 1,240,000.

Q: An investor offers UGX 6,000,000 for 15 percent of a company. What company value does this offer imply?
- UGX 15,000,000
- UGX 36,000,000
* UGX 40,000,000
- UGX 90,000,000
= 6,000,000 divided by 0.15 equals 40,000,000.

Q: A message offers a guaranteed grant if you first pay a processing fee by mobile money. What should you do?
- Pay quickly to secure it
* Do not pay; treat it as a likely scam and report it
- Send your PIN for verification
- Ask friends to pay too
= Real funders do not charge you to receive money, and guaranteed returns are a warning sign.

Q: You set a 90-day loss limit and a target of 20 paying customers. At day 90 you have 5 customers and have reached the limit. What is the wisest step?
- Continue unchanged and hope
* Review the evidence, then change the plan based on what you learned or stop
- Hide the numbers
- Borrow more without a plan
= The limit and review exist so that evidence, not hope, decides what you do next.

Q: A second stall has fixed costs of UGX 550,000 a month and a margin of UGX 25,000 per unit. What is its break-even?
- 12 units
- 20 units
* 22 units
- 30 units
= 550,000 divided by 25,000 equals 22 units.

Q: Which sequence best matches the 90-day plan phases?
- Spend, borrow, hire
* Learn and test, launch small, improve and decide
- Advertise, expand, rest
- Register, close, restart
= The plan starts with evidence, then a small launch, then a review and decision at day 90.
QUIZ,
];
