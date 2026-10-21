<?php
return [
    'slug'        => 'digital-marketing-advanced',
    'title'       => 'Advanced Digital Marketing: SEO, Analytics and Paid Ads',
    'category'    => 'marketing',
    'level'       => 'advanced',
    'icon'        => 'megaphone',
    'sort'        => 250,
    'summary'     => 'Plan full-funnel campaigns, rank in search, measure with analytics, run paid ads without wasting money, and report results honestly. Ends with a complete campaign brief.',
    'description' => <<<'HTML'
<p>You already know the basics of digital marketing. This course takes you from doing individual tasks to running a measured, joined-up programme: one that has a clear strategy, brings in visitors through search and ads, tracks what they do, and improves month after month.</p>
<p>You will learn how to plan a funnel and choose channels, how search engine optimisation (SEO) really works, how to read analytics without fooling yourself, how paid advertising is structured and where money is wasted, and how e-mail, automation and conversion work turn visitors into customers or supporters. Examples fit small businesses, schools and non-profit organisations.</p>
<p>The tools and menus of Google, Meta and others change often, so this course teaches the ideas that stay the same and tells you in general terms where to look. You finish by writing a full campaign brief that you could hand to a manager, a donor or a client.</p>
HTML,
    'outcomes'    => [
        'Build a full-funnel marketing plan with clear goals, KPIs and a realistic budget',
        'Explain attribution models in plain words and choose one for a report',
        'Research keywords and optimise a page for search, including the technical basics',
        'Set up measurement with events, conversions and UTM tags and read the reports correctly',
        'Spot false conclusions in data and respect privacy and consent rules',
        'Structure a paid search or social campaign, test ads and avoid common wasted spend',
        'Design lifecycle e-mails and simple automations that reach the inbox',
        'Report results to stakeholders and write a complete campaign brief',
    ],
    'requirements' => [
        'The Digital Marketing Fundamentals course (or equivalent knowledge of social media, content and e-mail basics)',
        'Comfort using a computer, a web browser and spreadsheets',
        'A website or social page to practise on is helpful but not required',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Google Search Central', 'url' => 'https://developers.google.com/search'],
        ['title' => 'Google Analytics Help', 'url' => 'https://support.google.com/analytics'],
        ['title' => 'Google Ads Help', 'url' => 'https://support.google.com/google-ads'],
        ['title' => 'Google Skillshop', 'url' => 'https://skillshop.withgoogle.com'],
        ['title' => 'Meta Business Help Centre', 'url' => 'https://www.facebook.com/business/help'],
    ],
    'modules' => [

        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: Strategy and funnels',
            'summary' => 'Plan campaigns around the customer journey, positioning, channels, budgets, goals and attribution.',
            'lessons' => [
                [
                    'title'   => 'Full-funnel thinking',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to map a customer journey into funnel stages and decide what each stage needs. This matters because most wasted marketing money comes from asking for a sale from people who have only just heard of you, or from forgetting people who were ready to buy.</p>
<h3>What a funnel is</h3>
<p>A <strong>funnel</strong> is a simple picture of how people move from not knowing you to becoming a customer, donor or loyal supporter. Many people enter at the top, fewer reach the bottom. Real journeys are messy, but the funnel helps you plan.</p>
<table>
<thead><tr><th>Stage</th><th>Person is thinking</th><th>Your goal</th><th>Example content</th></tr></thead>
<tbody>
<tr><td>Awareness</td><td>"I have a problem" or "who is this?"</td><td>Be found and remembered</td><td>Short videos, blog posts, radio-style clips, search ads</td></tr>
<tr><td>Consideration</td><td>"What are my options?"</td><td>Build trust and show proof</td><td>Guides, comparisons, reviews, webinars, FAQs</td></tr>
<tr><td>Conversion</td><td>"Should I do this now?"</td><td>Make acting easy</td><td>Clear offer, simple form, mobile money option, free trial</td></tr>
<tr><td>Retention</td><td>"Was it worth it?"</td><td>Deliver and keep in touch</td><td>Welcome e-mails, tips, support, updates</td></tr>
<tr><td>Advocacy</td><td>"I would tell others"</td><td>Turn happy people into promoters</td><td>Referral offers, testimonials, community events</td></tr>
</tbody>
</table>
<h3>Plan one stage at a time</h3>
<ol>
<li>Write the journey of one real customer: how did they first hear about you, what did they search, who did they ask, what nearly stopped them?</li>
<li>For each stage write the main question in the person's mind.</li>
<li>Choose one piece of content and one measure per stage. A measure is a number you can check, such as video views for awareness or form submissions for conversion.</li>
<li>Look for the <strong>leak</strong>: the stage where the most people drop out. Fix the biggest leak first, because more traffic poured into a leaking funnel is wasted.</li>
</ol>
<h3>Why full-funnel beats one-step selling</h3>
<p>If you only run "buy now" ads, you reach a small group who are ready today and you pay a high price for each. If you only run awareness, you create interest that nobody collects. A balanced plan has content for all stages, with most of the effort placed where your leak is. A school offering evening ICT classes, for example, needs awareness among parents, proof from past students, and an easy enrolment step.</p>
<h3>Common mistakes</h3>
<ul>
<li>Treating the funnel as a straight line. People move back and forth, so keep content for earlier stages available.</li>
<li>Measuring only the last click and ignoring earlier help.</li>
<li>Copying a big company's funnel. Their audience, budget and brand are different from yours.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Pick a real small business, school or organisation you know.</li>
<li>Draw the five funnel stages in a table and write the question a customer asks at each stage.</li>
<li>List one existing piece of content for each stage. Mark the stages where you have nothing.</li>
<li>Guess where the biggest leak is and write how you would check it with numbers.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A funnel pictures the journey from awareness to advocacy.</li>
<li>Each stage has a different question, goal, content type and measure.</li>
<li>Find and fix the biggest leak before buying more traffic.</li>
<li>Balanced plans serve every stage instead of only asking for the sale.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Positioning, audience and message',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a positioning statement and a clear audience profile that guide every ad, post and page. Without them, each channel says something different and the audience cannot tell why they should choose you.</p>
<h3>Positioning in plain words</h3>
<p><strong>Positioning</strong> is the place your offer holds in the mind of a specific group of people compared with the alternatives. It answers: for whom, what do we do, and why are we the better choice? A simple template is:</p>
<pre>For [audience] who [need or problem],
[name] is a [category] that [main benefit],
unlike [alternative] which [weakness].</pre>
<p>Example: "For young job seekers in Gulu who need computer skills, our centre is a free training programme that gives certificates and practical projects, unlike paid short courses that only teach theory."</p>
<h3>Know the audience, not "everyone"</h3>
<p>"Everyone with a phone" is not an audience. Build two or three <strong>audience profiles</strong> (sometimes called personas) from real facts:</p>
<ol>
<li><strong>Who</strong>: age range, place, work or study, devices and data costs.</li>
<li><strong>Jobs to be done</strong>: what they are trying to achieve, such as "get a first job" or "sell more at the market".</li>
<li><strong>Pains</strong>: fear, cost, lack of time, past bad experiences.</li>
<li><strong>Where they spend time</strong>: WhatsApp groups, Facebook, YouTube, radio, church or community meetings.</li>
<li><strong>Objections</strong>: the reasons they say no, and the proof that answers them.</li>
</ol>
<p>Get facts by talking to ten real customers, reading your messages and comments, and looking at your own analytics. Do not invent a persona from guesses.</p>
<h3>From positioning to message</h3>
<p>Your <strong>message hierarchy</strong> has three levels. The main promise is one sentence. Under it sit three supporting proof points. Under those sit details, stories and numbers. Every channel repeats the main promise in its own style: a short video, a search ad headline, an e-mail subject line.</p>
<table>
<thead><tr><th>Weak message</th><th>Stronger message</th></tr></thead>
<tbody>
<tr><td>"We are the best training provider"</td><td>"Learn to use computers in 8 weeks, free, near you"</td></tr>
<tr><td>"Quality products at good prices"</td><td>"Fresh tomatoes delivered to your gate by 8 a.m."</td></tr>
<tr><td>"Support our cause"</td><td>"UGX 30,000 pays for one girl's school books for a term"</td></tr>
</tbody>
</table>
<h3>Common mistakes</h3>
<ul>
<li>Describing features instead of the benefit to the customer.</li>
<li>Claiming to be "number one" without proof.</li>
<li>Changing the message on every channel so people see no pattern.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Write a positioning statement using the template for a real organisation.</li>
<li>Create one audience profile using real facts from at least three people you spoke to.</li>
<li>Write the main promise in one sentence and three proof points.</li>
<li>Rewrite one existing ad or post so it opens with the benefit.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Positioning says for whom, what, and why you are better than alternatives.</li>
<li>Audience profiles must come from real conversations and data.</li>
<li>One main promise supported by proof should appear in every channel.</li>
<li>Lead with benefits that the audience cares about, not with features.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Channel mix and budgets',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose a sensible set of channels and split a budget between them. Spreading a small budget over every platform is one of the commonest mistakes, so choosing is as important as doing.</p>
<h3>Types of channel</h3>
<p>Marketers often group channels by who controls them:</p>
<ul>
<li><strong>Owned</strong>: your website, e-mail list, blog, social pages. Cheap to use, slow to grow, and you control them.</li>
<li><strong>Earned</strong>: press, shares, reviews, word of mouth, links from other sites. Trusted, but you cannot buy it directly.</li>
<li><strong>Paid</strong>: search ads, social ads, sponsorships, influencer fees. Fast and scalable, but stops when you stop paying.</li>
</ul>
<p>A healthy mix uses paid channels to start momentum, owned channels to keep people, and earned channels as a reward for being useful.</p>
<h3>Choosing channels</h3>
<ol>
<li>List where your audience profile spends time and what they do there. Someone searching for "cheap laptop repair Gulu" is in a search mindset. Someone scrolling a video feed is in a discovery mindset.</li>
<li>Match each funnel stage to a channel. Search and short video suit awareness, e-mail and WhatsApp suit consideration and retention, retargeting suits conversion.</li>
<li>Pick no more than two or three main channels for the first three months. Do them well before adding a fourth.</li>
<li>Check that you can afford the content needed. A video channel without anyone to make videos will fail.</li>
</ol>
<h3>Setting a budget</h3>
<p>There are three common methods:</p>
<table>
<thead><tr><th>Method</th><th>How it works</th><th>Best when</th></tr></thead>
<tbody>
<tr><td>Percentage of revenue</td><td>Spend a fixed share, for example 5-10 percent of sales</td><td>You have steady sales</td></tr>
<tr><td>Goal-based</td><td>Work back from the target: sales wanted, divided by conversion rate, times cost per visitor</td><td>You have some past data</td></tr>
<tr><td>Test budget</td><td>A small fixed amount to learn what works before scaling</td><td>You are starting out</td></tr>
</tbody>
</table>
<p>A useful rule is the <strong>70-20-10 split</strong>: about 70 percent on channels that already work, 20 percent on promising ones, and 10 percent on bold experiments. Always keep money and time for measurement and for making content, not only for buying ads.</p>
<h3>Worked example</h3>
<p>A training centre wants 40 new students. Past data says 1 in 10 people who fill the enquiry form enrol, so it needs 400 enquiries. If one enquiry costs UGX 3,000 through ads, the ad budget is 400 x 3,000 = UGX 1,200,000. If that is too high, the plan must change: improve the form conversion, add free channels such as referrals, or aim for fewer students.</p>
<h3>Common mistakes</h3>
<ul>
<li>Choosing channels because they are fashionable.</li>
<li>Spending everything in week one and having nothing left to learn from.</li>
<li>Ignoring the staff time that "free" channels need.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Choose a real goal, for example 30 new customers in three months.</li>
<li>Work out the budget needed using the goal-based method and guessed rates; write your assumptions.</li>
<li>Pick two or three channels and write one sentence on why each fits the audience.</li>
<li>Split your budget using 70-20-10 or, if you have no data yet, a test budget.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Owned, earned and paid channels play different roles.</li>
<li>Start with two or three channels that match audience behaviour and your capacity.</li>
<li>Work backwards from the goal to estimate a budget, and write down assumptions.</li>
<li>Keep a share of the budget for testing and for measurement.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Goals, KPIs and attribution in plain words',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to set goals with OKRs, pick KPIs that matter, and explain attribution models to someone who is not a marketer. These skills stop you reporting impressive numbers that do not help the business.</p>
<h3>Objectives and key results</h3>
<p>An <strong>OKR</strong> (Objectives and Key Results) has two parts. The <strong>objective</strong> is a short, inspiring statement of what you want. The <strong>key results</strong> are two to four measurable numbers that show you got there.</p>
<pre>Objective: Become the first choice for ICT training in our town.
KR1: 600 qualified enquiries from the website by 31 March.
KR2: Enquiry-to-enrolment rate rises from 8% to 12%.
KR3: Average cost per enquiry stays under UGX 3,000.</pre>
<p>Good key results are measurable, time-bound and within your influence. "Increase brand love" is not a key result; "raise returning visitors from 15% to 25%" is.</p>
<h3>KPIs: vanity versus useful</h3>
<p>A <strong>KPI</strong> (key performance indicator) is a number you watch regularly to know if you are on track. <strong>Vanity metrics</strong> such as followers, likes or page views feel good but may not connect to money or mission. <strong>Actionable metrics</strong> link to a decision.</p>
<table>
<thead><tr><th>Stage</th><th>Useful KPI</th></tr></thead>
<tbody>
<tr><td>Awareness</td><td>Reach, unique visitors, branded searches</td></tr>
<tr><td>Consideration</td><td>Engaged sessions, time on key pages, e-mail sign-ups</td></tr>
<tr><td>Conversion</td><td>Conversion rate, cost per acquisition (CPA), revenue or donations</td></tr>
<tr><td>Retention</td><td>Repeat purchase rate, unsubscribe rate, customer lifetime value</td></tr>
</tbody>
</table>
<h3>Attribution models</h3>
<p>Most people meet you several times before acting: maybe a video, then a search, then an e-mail. <strong>Attribution</strong> is the rule for deciding which of those contacts gets credit for the sale.</p>
<ul>
<li><strong>Last click</strong>: all credit goes to the final contact. Simple, but ignores everything that built interest.</li>
<li><strong>First click</strong>: all credit goes to the first contact. It rewards discovery but ignores the closer.</li>
<li><strong>Linear</strong>: credit shared equally across all contacts.</li>
<li><strong>Time decay</strong>: more credit to contacts nearer the sale.</li>
<li><strong>Data-driven</strong>: software estimates credit from patterns in lots of data. It needs enough conversions to be reliable.</li>
</ul>
<p>No model is "true". They are different lenses. If you only look at last click, you may cut awareness work that quietly feeds your sales. Compare two models side by side and ask what changes.</p>
<h3>Common mistakes</h3>
<ul>
<li>Choosing too many KPIs. Five is plenty.</li>
<li>Changing the attribution model during a report without saying so.</li>
<li>Setting targets without a starting number to compare against.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Write one objective and three key results for a real or imaginary organisation.</li>
<li>Label each result as measurable, time-bound and in your control (yes or no).</li>
<li>Take a customer who first saw a video, then clicked a search ad, then opened an e-mail and bought. Write how each model credits each contact, using 100 points.</li>
<li>Write one sentence explaining the difference to a manager.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>OKRs combine an inspiring objective with measurable key results.</li>
<li>Prefer actionable KPIs to vanity metrics.</li>
<li>Attribution decides which contacts get credit; every model is a lens, not the truth.</li>
<li>State the model and the starting point whenever you report.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A training centre gets many website visitors but few enquiries. What should it do first?
- Buy more traffic from ads
* Find the stage where most people drop out and improve that
- Add more social media platforms
- Change its logo
= More traffic into a leaking funnel wastes money. Find the biggest leak and fix it first.

Q: Which funnel stage is a person in when they are asking "What are my options?"
- Awareness
- Retention
* Consideration
- Advocacy
= Consideration is when people compare options and look for proof and trust.

Q: Which are examples of owned channels? (choose all that apply)
* Your e-mail list
* Your website
- A news story written about you by a journalist
- A paid search ad
= Owned channels are those you control. Press coverage is earned and ads are paid.

Q: Which positioning statement is strongest?
- We offer the best quality services in the region
* For young job seekers in Gulu who need computer skills, our free programme gives certificates and practical projects
- We are number one in everything we do
- Our organisation was founded in 2010
= A strong statement names a specific audience, a need, a benefit and what makes it different.

Q: You need 40 enrolments and 1 in 10 enquiries enrols. How many enquiries do you need?
- 40
- 100
* 400
- 4,000
= 40 divided by 0.10 equals 400 enquiries.

Q: Which is a vanity metric?
- Cost per acquisition
* Number of followers on its own
- Conversion rate
- Repeat purchase rate
= Followers feel good but do not by themselves show sales, sign-ups or impact.

Q: Under last-click attribution, who gets all the credit for a sale?
- The first contact
- Every contact equally
* The final contact before the sale
- The contact that cost the most
= Last click gives 100 percent of the credit to the last interaction.

Q: Which is a good key result? (choose all that apply)
* Raise returning visitors from 15% to 25% by June
- Increase brand love
* Keep average cost per enquiry under UGX 3,000 this quarter
- Be better known
= Good key results are measurable, time-bound and within your influence.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Search engine optimisation',
            'summary' => 'Understand how search works and improve your pages with research, on-page and technical SEO, content clusters, ethical links and local SEO.',
            'lessons' => [
                [
                    'title'   => 'How search engines work',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain how a search engine finds, stores and ranks pages, and what you can and cannot control. This knowledge protects you from "guaranteed first page" promises that nobody can keep.</p>
<h3>Three steps: crawl, index, rank</h3>
<ol>
<li><strong>Crawling</strong>: automated programs called crawlers or bots follow links from page to page and download what they find. If no link leads to a page and it is not in your sitemap, the crawler may never see it.</li>
<li><strong>Indexing</strong>: the search engine analyses each page, works out its topic and quality, and stores it in a huge database called the <strong>index</strong>. Not every crawled page is indexed. Thin, duplicate or blocked pages may be left out.</li>
<li><strong>Ranking</strong>: when someone searches, the engine picks matching pages from the index and orders them using hundreds of signals, including relevance, quality, usefulness, speed, mobile friendliness and the person's location.</li>
</ol>
<h3>The search results page</h3>
<p>A results page is more than ten blue links. It can show ads, maps, images, videos, answer boxes and "people also ask" questions. The ordinary results that you do not pay for are called <strong>organic</strong> results. Where your page appears is its <strong>ranking</strong> or position.</p>
<h3>Search intent</h3>
<p>People search for different reasons. This <strong>search intent</strong> decides which pages deserve to rank.</p>
<table>
<thead><tr><th>Intent</th><th>Example search</th><th>Best page type</th></tr></thead>
<tbody>
<tr><td>Informational</td><td>how to write a CV</td><td>Guide or article</td></tr>
<tr><td>Navigational</td><td>Umeme bill payment site</td><td>The official page</td></tr>
<tr><td>Commercial research</td><td>best budget laptops Uganda</td><td>Comparison or review</td></tr>
<tr><td>Transactional</td><td>buy laptop Kampala</td><td>Product or shop page</td></tr>
</tbody>
</table>
<p>If your page does not match the intent, it will not rank well however well it is "optimised". A product page will rarely beat a guide for a "how to" search.</p>
<h3>What affects ranking, in general terms</h3>
<ul>
<li><strong>Relevance</strong>: does the page answer the question?</li>
<li><strong>Quality and trust</strong>: is it accurate, original, written by people who know the subject, and linked to by respected sites?</li>
<li><strong>Experience</strong>: is it fast, mobile friendly, safe (HTTPS) and easy to read?</li>
<li><strong>Context</strong>: location, language and past searches change results.</li>
</ul>
<p>Search engines change their methods often. Nobody outside the company knows the exact formula, so be careful with anyone who claims to. The lasting approach is to be the most helpful result for a real need.</p>
<h3>Try it yourself</h3>
<ol>
<li>Search for a phrase like "how to start a small shop" on your phone, then on a computer.</li>
<li>Note what appears: ads, map, videos, questions, ordinary results.</li>
<li>Decide the search intent behind the phrase and the page type that the top three results use.</li>
<li>Write what kind of page you would need to create to compete.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Search works by crawling, indexing and ranking.</li>
<li>Matching search intent is as important as keywords.</li>
<li>Ranking depends on relevance, quality, experience and context.</li>
<li>Nobody can guarantee a first-page position.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Keyword research and content clusters',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to find the words your audience really searches for and organise content into clusters. This lets you plan articles and pages that answer real demand instead of guessing.</p>
<h3>What a keyword is</h3>
<p>A <strong>keyword</strong> is a word or phrase people type or speak into a search engine. Short ones (head terms) like "laptop" are broad and competitive. Longer ones (long-tail terms) like "second hand laptop prices Gulu" are specific, have fewer searches but are easier to rank for and closer to a decision.</p>
<h3>A research process</h3>
<ol>
<li><strong>Seed list</strong>: write 20 words and phrases your customers use. Use their language, not your technical words.</li>
<li><strong>Expand</strong>: type each seed into a search box and note the suggestions that appear below it, the "people also ask" questions and the related searches at the bottom. Free keyword tools in advertising and search accounts and various third-party tools show estimated monthly searches. Treat those numbers as rough guides.</li>
<li><strong>Check intent</strong>: search each phrase yourself and see what kind of pages rank.</li>
<li><strong>Judge difficulty</strong>: if the top results are all large well-known sites, a new small site will find it hard. Look for phrases where smaller or local sites rank.</li>
<li><strong>Prioritise</strong>: give each keyword a score for demand, relevance to your offer, and difficulty. Pick those that are relevant first.</li>
</ol>
<h3>Keep a keyword sheet</h3>
<pre>Keyword | Intent | Est. searches | Difficulty | Target page | Status
laptop repair Gulu | Transactional | 90 | Low | /services/laptop-repair | Planned
how to speed up a slow laptop | Informational | 600 | Medium | /blog/speed-up-laptop | Written</pre>
<p>Give each target page one main topic. Two pages fighting for the same keyword is called <strong>cannibalisation</strong> and weakens both.</p>
<h3>Content clusters</h3>
<p>A <strong>content cluster</strong> (or topic cluster) is a group of pages around one subject. One <strong>pillar page</strong> covers the topic broadly, for example "Laptop care guide". Several <strong>cluster pages</strong> go deep on subtopics: battery life, cleaning, slow performance, virus removal. Each cluster page links to the pillar and the pillar links to each of them. This shows search engines, and readers, that your site covers the topic well.</p>
<h3>Common mistakes</h3>
<ul>
<li>Choosing keywords with high volume but no link to what you sell.</li>
<li>Repeating the keyword again and again (keyword stuffing). Write naturally.</li>
<li>Writing content without checking what already ranks and how you can be better or different.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Pick a subject you know well and write 15 phrases a customer might search.</li>
<li>Check suggestions in a search box and add five more.</li>
<li>Put the 20 phrases in a sheet with intent and a difficulty guess (low, medium, high).</li>
<li>Choose one pillar topic and five cluster topics, and sketch how they link.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Long-tail keywords are specific and often easier to win.</li>
<li>Research uses real language, search suggestions, intent checks and difficulty judgement.</li>
<li>One main topic per page avoids cannibalisation.</li>
<li>Clusters link a pillar page to detailed pages on subtopics.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'On-page and technical SEO',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to improve a page's content and the technical health of a site. Good on-page work helps search engines and people understand the page, and good technical work makes sure the page can be reached and loads quickly.</p>
<h3>On-page SEO checklist</h3>
<ul>
<li><strong>Title tag</strong>: the clickable headline in results. Put the main topic near the start, keep it roughly under 60 characters so it is not cut off, and make it unique and appealing.</li>
<li><strong>Meta description</strong>: the short summary under the title. It does not directly boost ranking but a clear one earns more clicks. Roughly 150 characters.</li>
<li><strong>Headings</strong>: one main heading (H1) that states the topic, then subheadings (H2, H3) that structure the page in a logical order.</li>
<li><strong>URL</strong>: short, readable and lower case, such as <code>/blog/speed-up-laptop</code>.</li>
<li><strong>Content</strong>: answers the question completely and plainly, in your own words, with examples, photos and data. Cover related questions people ask.</li>
<li><strong>Images</strong>: compress them, give files descriptive names and write <strong>alt text</strong>, a short description for screen readers and search.</li>
<li><strong>Internal links</strong>: link to other relevant pages with meaningful anchor text, not "click here".</li>
</ul>
<h3>Technical SEO</h3>
<p><strong>Speed</strong>: slow pages lose visitors, especially on mobile data. Compress images, remove unneeded scripts, use caching and good hosting. Free tools such as page speed testers show problems and suggestions in plain language.</p>
<p><strong>Mobile friendliness</strong>: most people in Uganda browse on phones, and search engines mainly judge the mobile version of a page. Text must be readable without zooming, buttons large enough to tap, and nothing should shift around while the page loads.</p>
<p><strong>HTTPS</strong>: the secure padlock. It protects visitors and is expected today.</p>
<p><strong>XML sitemap</strong>: a file that lists the pages you want indexed, usually at an address like <code>/sitemap.xml</code>. You can submit it in the search engine's webmaster tool.</p>
<p><strong>robots.txt</strong>: a small file telling crawlers which areas not to visit. A single wrong line can hide your whole site, so check it carefully.</p>
<p><strong>Canonical tags</strong>: tell search engines which version of near-identical pages is the main one.</p>
<p><strong>Structured data</strong>: extra code that labels information such as business name, opening hours, reviews or events so search engines understand it. It can make results richer but does not guarantee them. Test it using the search engine's testing tools.</p>
<p><strong>Broken links and redirects</strong>: when a page moves, redirect the old address to the new one rather than leaving an error page.</p>
<h3>A simple audit routine</h3>
<ol>
<li>Check that the site loads over HTTPS and works on a phone.</li>
<li>Run a speed test for the home page and one article.</li>
<li>Search for <code>site:yourdomain.com</code> to see which pages are indexed.</li>
<li>Check titles and descriptions for duplicates or blanks.</li>
<li>List broken links and fix the most important ones first.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Choose a page on a real website, or your own.</li>
<li>Write a new title (under 60 characters) and meta description for it.</li>
<li>Check that it has one H1, sensible subheadings and alt text on images.</li>
<li>Run a page speed test and write down the two biggest suggestions.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Titles, headings, content, images and links form the on-page basics.</li>
<li>Speed, mobile use and HTTPS are part of a good experience.</li>
<li>Sitemaps and robots.txt guide crawlers; mistakes can hide a site.</li>
<li>Structured data labels information but never guarantees a special result.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Links, local SEO and measuring results',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to earn links honestly, improve a local business's visibility and read the main measures of search performance. These three areas often decide whether good content is actually found.</p>
<h3>Link building with ethics</h3>
<p>A <strong>backlink</strong> is a link from another website to yours. Links from respected, relevant sites act like recommendations. Quality matters far more than quantity.</p>
<p>Honest ways to earn links:</p>
<ul>
<li>Publish original useful material: research, templates, local guides, clear explainers.</li>
<li>Share real expertise as a guest writer, or give quotes to journalists and bloggers.</li>
<li>Get listed in legitimate directories, partner and association websites, and school or community pages.</li>
<li>Tell people about useful work you did, for example a free skills training report.</li>
</ul>
<p>Practices to avoid: buying links, joining link exchange schemes, spamming blog comments and using networks of fake sites. Search engines treat these as manipulation, and a penalty can remove your site from results. A good test is: would I be comfortable explaining this to the search engine and to the other site's owner?</p>
<h3>Local SEO</h3>
<p>If you serve people in a place, such as a shop or clinic, <strong>local SEO</strong> helps you appear in map results and "near me" searches.</p>
<ol>
<li>Create and verify a free business profile on the main search map service. Fill in name, address, phone, hours, category, photos and services.</li>
<li>Keep your <strong>NAP</strong> (name, address, phone) identical everywhere: website, social pages, directories.</li>
<li>Describe your location in landmarks people use, since formal addresses are not always usable in many towns.</li>
<li>Ask happy customers to leave honest reviews and reply politely to all of them, including bad ones. Never pay for fake reviews.</li>
<li>Create pages for each service and area you actually serve, with real information.</li>
</ol>
<h3>Measuring SEO</h3>
<p>The search engine's free <strong>search console</strong> tool shows how your site appears in search. Look for these ideas in its reports:</p>
<table>
<thead><tr><th>Measure</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>Impressions</td><td>How often your page appeared in results</td></tr>
<tr><td>Clicks</td><td>How often people clicked it</td></tr>
<tr><td>CTR (click-through rate)</td><td>Clicks divided by impressions</td></tr>
<tr><td>Average position</td><td>Typical rank for a query</td></tr>
<tr><td>Coverage / indexing</td><td>Which pages are indexed, and which have problems</td></tr>
<tr><td>Queries</td><td>The words people used when they found you</td></tr>
</tbody>
</table>
<p>Use it to find pages with many impressions but a low CTR (improve the title) and queries where you rank on page two (improve the content). SEO results usually take weeks or months, so judge trends over time and not day-to-day changes.</p>
<h3>Try it yourself</h3>
<ol>
<li>List five honest link opportunities for a real organisation, with names of sites or groups.</li>
<li>Write a short, polite message asking one of them to feature or link to your resource.</li>
<li>Check how a local business appears when you search its name and "near me" on a phone.</li>
<li>Write three things you would fix in its local listing.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Earn links with useful material and relationships; never buy or fake them.</li>
<li>Local SEO relies on a complete business profile, consistent NAP and honest reviews.</li>
<li>Search console shows impressions, clicks, CTR, position and indexing.</li>
<li>Judge SEO over months and by trends.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the correct order of how a search engine works?
- Rank, index, crawl
* Crawl, index, rank
- Index, crawl, rank
- Rank, crawl, index
= Bots crawl pages, the engine indexes what it finds, and ranking orders indexed pages for each search.

Q: Someone searches "how to write a CV". What is the search intent?
* Informational
- Transactional
- Navigational
- Local
= The person wants to learn, so a guide matches best.

Q: What is keyword cannibalisation?
- Using too many keywords in one sentence
* Two pages of your own site competing for the same keyword
- Competitors copying your keywords
- Removing keywords from a page
= Splitting one topic over several pages weakens each. Give each page one main topic.

Q: A pillar page and its cluster pages should...
- Never link to each other
* Link to each other so the topic is covered in a connected way
- Use identical text
- Be placed on different websites
= Linking shows both readers and search engines that your site covers a topic in depth.

Q: Which are good on-page practices? (choose all that apply)
* A unique title tag for each page
* Alt text on important images
- Repeating the keyword in every sentence
- Using "click here" for all links
= Stuffing keywords and vague link text harm readability and meaning.

Q: What does robots.txt do?
- Speeds up the site
* Tells crawlers which areas they should not visit
- Writes meta descriptions
- Adds structured data
= A wrong rule in robots.txt can hide pages or the whole site, so edit with care.

Q: Which link-building method is ethical?
- Paying a network to place links on hundreds of sites
* Publishing an original local guide that others choose to link to
- Posting your link in thousands of blog comments
- Exchanging links with unrelated sites in bulk
= Earned links from useful content are safe. Manipulative schemes risk penalties.

Q: A page has many impressions but a low click-through rate. What should you try first?
- Delete the page
* Improve its title and description
- Buy links
- Add more keywords to the footer
= Many impressions show it appears often; a better title and description can earn more clicks.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Analytics',
            'summary' => 'Measure with event-based analytics, UTM tags and dashboards, interpret data carefully and handle privacy and consent responsibly.',
            'lessons' => [
                [
                    'title'   => 'Analytics concepts: events, conversions and audiences',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain how modern analytics tools such as Google Analytics 4 (GA4) record behaviour and how to turn important actions into conversions. This is the foundation for judging whether any campaign works.</p>
<h3>Events: the building block</h3>
<p>In GA4 almost everything is an <strong>event</strong>: a record that something happened, together with details about it. Opening a page is an event, scrolling, clicking a link, watching a video, submitting a form or buying something are events too. Older tools were built mainly around "page views"; the event idea is more flexible, because the same simple structure covers websites and apps.</p>
<ul>
<li><strong>Automatically collected events</strong>: basics such as page views and first visits.</li>
<li><strong>Enhanced measurement</strong>: optional events the tool can detect for you, such as scrolls, outbound clicks, file downloads and some form interactions.</li>
<li><strong>Recommended and custom events</strong>: events you define yourself, for example <code>enquiry_submitted</code> or <code>donate_click</code>.</li>
</ul>
<p>Each event can carry <strong>parameters</strong>: extra facts such as the page name, button label or value of a purchase.</p>
<h3>Users and sessions</h3>
<p>A <strong>session</strong> is a group of events during one visit. A <strong>user</strong> is counted from a browser or device (and a login, if you use one). Remember that people use several devices and clear their data, so user numbers are estimates.</p>
<h3>Conversions (key events)</h3>
<p>A <strong>conversion</strong> is an event you decide matters to your goal, such as a completed enquiry form, a donation, an enrolment, a phone call tap or a purchase. In recent versions the tool may call these <strong>key events</strong>. The name changes, but the idea does not. Mark only the actions that truly matter. If everything is a conversion, nothing is.</p>
<p>A tracking plan helps:</p>
<table>
<thead><tr><th>Goal</th><th>Event</th><th>Is it a conversion?</th><th>Value</th></tr></thead>
<tbody>
<tr><td>Get enquiries</td><td>enquiry_submitted</td><td>Yes</td><td>Estimated worth, e.g. UGX 20,000</td></tr>
<tr><td>Build the list</td><td>newsletter_signup</td><td>Yes</td><td>Smaller value</td></tr>
<tr><td>Show interest</td><td>video_play</td><td>No (supporting event)</td><td>None</td></tr>
</tbody>
</table>
<h3>Audiences</h3>
<p>An <strong>audience</strong> is a group of users who share a condition, for example "visited the courses page but did not enquire" or "purchased in the last 30 days". Audiences let you analyse groups and, where the tool is linked to advertising, show ads to them. Create audiences from behaviour that really signals intent.</p>
<h3>Where to look in the tool</h3>
<p>Interfaces change, so look in general for: a reports area (acquisition, engagement, monetisation, retention), an explore area for custom analysis, and an admin area where you manage data streams, events, conversions, audiences and linked accounts such as ad platforms and the search console. Use the built-in help search when a menu has moved.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose a real or imaginary website and list its three most important visitor actions.</li>
<li>Fill in a tracking plan table like the one above, naming each event in lower case with underscores.</li>
<li>Decide which events are conversions and give each a rough value.</li>
<li>Describe one audience you would build and what you would do with it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Modern analytics records everything as events with parameters.</li>
<li>Conversions (key events) are the few actions that matter for your goal.</li>
<li>Write a tracking plan before changing any settings.</li>
<li>Audiences group users by behaviour for analysis and targeting.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'UTM tags, dashboards and funnel reports',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to tag links so traffic sources are recorded correctly, build a simple dashboard and read funnel and cohort reports. Without tags, much of your traffic ends up in a vague "direct" or "unassigned" group and you cannot tell which campaign worked.</p>
<h3>UTM tags</h3>
<p>A <strong>UTM tag</strong> is a small piece added to the end of a web address that tells your analytics where a visitor came from. Example:</p>
<pre>https://example.org/courses?utm_source=facebook&amp;utm_medium=paid_social&amp;utm_campaign=evening_classes_march&amp;utm_content=video_a</pre>
<p>The main parts are:</p>
<table>
<thead><tr><th>Parameter</th><th>Meaning</th><th>Example</th></tr></thead>
<tbody>
<tr><td>utm_source</td><td>Where the visitor came from</td><td>facebook, newsletter, whatsapp</td></tr>
<tr><td>utm_medium</td><td>The type of channel</td><td>email, paid_social, cpc, qr</td></tr>
<tr><td>utm_campaign</td><td>The campaign name</td><td>evening_classes_march</td></tr>
<tr><td>utm_content</td><td>Which ad or link version (optional)</td><td>video_a</td></tr>
<tr><td>utm_term</td><td>Keyword for paid search (optional)</td><td>laptop_repair</td></tr>
</tbody>
</table>
<p>Rules that save you pain: use lower case only, never spaces, agree one naming guide for the whole team, record every tagged link in a shared sheet, and never put UTM tags on links inside your own website, because that breaks the original source of a visit. Do not put personal data such as names or phone numbers in tags.</p>
<h3>Dashboards</h3>
<p>A <strong>dashboard</strong> is a single page that shows your main numbers so decisions are quick. Free tools can connect to analytics and ad data. Good dashboards:</p>
<ol>
<li>Start with the question: "Are we on track to meet our key results?"</li>
<li>Show 5-8 KPIs, each with a target and comparison to the previous period.</li>
<li>Use simple charts: lines for trends over time, bars for comparisons, and a table for details.</li>
<li>Label everything clearly with the date range and data source.</li>
<li>Are checked on a regular schedule, such as weekly.</li>
</ol>
<h3>Funnel and cohort reports</h3>
<p>A <strong>funnel report</strong> shows the steps users take toward a goal, such as visit the courses page, view a course, start the form and submit. It shows how many people drop off at each step, so you find the leak that module 1 taught you to look for.</p>
<p>A <strong>cohort report</strong> groups people who started at the same time, for example everyone who first visited in January, and follows them over later weeks. It shows whether people come back, and whether changes you made improve retention for new groups.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write UTM-tagged links for three places you would share a campaign: a newsletter, a WhatsApp message and a Facebook ad. Use a consistent naming pattern.</li>
<li>Record them in a sheet with columns for date, link, owner and purpose.</li>
<li>Sketch a one-page dashboard on paper with six KPIs and their targets.</li>
<li>Write the four steps of a funnel for your own website or service.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>UTM tags identify source, medium and campaign; keep them lower case and consistent.</li>
<li>Do not tag internal links or place personal data in tags.</li>
<li>A dashboard shows a few KPIs against targets and answers one clear question.</li>
<li>Funnel reports show drop-off by step; cohort reports show behaviour over time for groups.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Interpreting data and privacy',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to avoid common false conclusions from data and explain, in general terms, how privacy and consent affect marketing measurement. Good analysts are trusted because they are honest about what the numbers can and cannot say.</p>
<h3>Common traps</h3>
<ul>
<li><strong>Small samples</strong>: 3 sales from 10 visitors is a 30 percent rate, but it tells you very little. Wait for enough data before changing anything.</li>
<li><strong>Correlation is not causation</strong>: sales and ice-cream shop traffic may both rise in hot weather without one causing the other. Ask whether something else explains both.</li>
<li><strong>Seasonality</strong>: school fees time, festivals and holidays change behaviour. Compare with the same period last year, not just last month.</li>
<li><strong>Averages that hide</strong>: an average order value may hide a few huge orders. Look at medians and at groups.</li>
<li><strong>Survivorship bias</strong>: studying only the people who converted ignores the many who left.</li>
<li><strong>Cherry-picking</strong>: choosing the date range or metric that makes the result look good.</li>
<li><strong>Tracking gaps</strong>: ad blockers, declined cookies and broken tags mean tools undercount. Treat figures as good estimates, not exact truth.</li>
</ul>
<h3>A careful reading routine</h3>
<ol>
<li>State the question before looking at the data.</li>
<li>Check the date range, filters and whether tracking was working throughout.</li>
<li>Compare against a meaningful baseline: a previous period, a target, or a test group.</li>
<li>Look for a second explanation. What else changed?</li>
<li>Write the conclusion with its confidence: "likely", "possible", "not enough data".</li>
</ol>
<h3>Privacy and consent in general terms</h3>
<p>Analytics and ads use <strong>cookies</strong> (small files stored in the browser) and other identifiers to recognise visitors. Many countries have <strong>data protection laws</strong>. Uganda has the Data Protection and Privacy Act, and laws elsewhere, such as the European GDPR, may apply if you serve people in those places. Laws differ and change, so for a real organisation ask a qualified adviser. Common principles are:</p>
<ul>
<li><strong>Lawfulness and transparency</strong>: tell people what data you collect and why in a clear privacy policy.</li>
<li><strong>Consent</strong>: where the law requires it, ask before using non-essential cookies, and make declining as easy as accepting. A cookie banner that tricks people is not real consent.</li>
<li><strong>Data minimisation</strong>: collect only what you need.</li>
<li><strong>Security</strong>: protect the data and delete it when it is no longer needed.</li>
<li><strong>People's rights</strong>: allow people to see, correct or delete their data and to opt out of marketing.</li>
</ul>
<p>Practical habits: do not collect or upload personal details such as names, phone numbers or e-mail addresses into analytics unless properly allowed, shorten data-retention settings where possible, use a consent tool that really blocks tags until permission is given, and keep personal data lists safe with passwords and limited access.</p>
<h3>Try it yourself</h3>
<ol>
<li>Find a news story or report that claims a cause from a statistic. Write two other explanations.</li>
<li>Take a small table of weekly sales and mark where seasonality might be affecting it.</li>
<li>Read the cookie banner of three websites. Which one makes declining easiest?</li>
<li>Write five lines for a simple privacy notice for a small organisation (what you collect, why, how long, who sees it, how to ask for deletion).</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Beware small samples, seasonality, hidden averages and cherry-picked ranges.</li>
<li>Correlation alone does not prove one thing caused another.</li>
<li>Tools undercount because of blockers and declined cookies.</li>
<li>Respect privacy: be transparent, ask for valid consent, collect little and keep data safe.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: In GA4-style analytics, what is an event?
- Only a page view
* A record that something happened, such as a click, scroll or form submission
- A paid advertisement
- A type of cookie banner
= Almost every interaction is recorded as an event with optional parameters.

Q: Why should you mark only important actions as conversions?
- Tools allow only one conversion
* If everything is a conversion, the number no longer shows what matters
- Conversions are free but other events cost money
- It makes the site faster
= Conversions should represent actions that serve your goal.

Q: Which UTM parameter names the campaign?
- utm_source
- utm_medium
* utm_campaign
- utm_page
= utm_campaign holds the name of the campaign; source and medium say where and how.

Q: Which are good UTM habits? (choose all that apply)
* Use lower case and no spaces
* Keep a shared sheet of all tagged links
- Add UTM tags to links between your own pages
- Include visitors' phone numbers in tags
= Internal tagging overwrites the true source, and personal data must never go in tags.

Q: A funnel report is most useful for...
- Choosing a logo
* Seeing where users drop out between steps
- Writing meta descriptions
- Setting up cookies
= It shows the number of users who continue at each step.

Q: A shop sees 3 sales from 10 visitors on Monday and changes its whole website. What is the main error?
- Using a dashboard
* Making a decision from a tiny sample
- Checking sales
- Using percentages
= Tiny samples swing wildly, so wait for enough data.

Q: Which statement about cookie consent is best?
- Hide the decline option so more people accept
- Consent is never needed
* Where required, ask before non-essential cookies and make declining as easy as accepting
- Only large companies need consent
= Valid consent is a clear, informed and free choice.

Q: Sales rose after you posted more on social media, but it was also the school fees season. What is this an example of?
- Data-driven attribution
* Possible confusion between correlation and seasonality
- A technical SEO error
- Retargeting
= Another factor may explain the rise, so compare with the same season in earlier years.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Paid advertising',
            'summary' => 'Understand search and social ad structures, bidding, creative testing, retargeting and conversion tracking, and avoid wasted spend.',
            'lessons' => [
                [
                    'title'   => 'How paid search works',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe how a search advertising account is organised and how an ad wins a place on the page. You can then build a small campaign sensibly instead of clicking through a wizard you do not understand.</p>
<h3>The account structure</h3>
<p>Search advertising platforms such as Google Ads organise work in layers. Names vary slightly but the idea is the same:</p>
<table>
<thead><tr><th>Level</th><th>What it controls</th></tr></thead>
<tbody>
<tr><td>Account</td><td>Billing, users and settings</td></tr>
<tr><td>Campaign</td><td>Goal, budget, locations, languages, bidding strategy and schedule</td></tr>
<tr><td>Ad group</td><td>A tight theme of keywords and the ads that match them</td></tr>
<tr><td>Keywords</td><td>The searches that can trigger your ads</td></tr>
<tr><td>Ads</td><td>Headlines, descriptions and the landing page</td></tr>
</tbody>
</table>
<p>Keep each ad group to one theme, for example "laptop repair" separate from "laptop sales", so your ad text matches the search closely.</p>
<h3>Keyword match types</h3>
<p>Match types control how closely a search must match your keyword. In general terms: <strong>broad</strong> matching allows related searches, <strong>phrase</strong> matching requires the meaning of your phrase to be present, and <strong>exact</strong> matching is the tightest. Broad match can waste money on irrelevant searches, so beginners usually start tighter and watch the search terms report. <strong>Negative keywords</strong> block searches you do not want, such as "free", "jobs" or "course" for a repair shop.</p>
<h3>The auction</h3>
<p>Each search runs a quick auction. Your position depends on your <strong>bid</strong> (the most you are willing to pay) and your <strong>quality</strong>, which includes expected click-through rate, ad relevance and landing page experience. A better ad can win a higher position at a lower price than a careless ad with a bigger bid. You pay when someone clicks (cost per click, CPC), usually less than your maximum.</p>
<h3>Bidding strategies in plain words</h3>
<ul>
<li><strong>Manual CPC</strong>: you set the bids. More control, more work.</li>
<li><strong>Maximise clicks</strong>: the system gets as many visits as it can within your budget, regardless of quality.</li>
<li><strong>Target cost per action (CPA)</strong> or maximise conversions: the system aims for sign-ups or sales. It needs conversion tracking and enough data to learn, often dozens of conversions per month.</li>
</ul>
<h3>Writing a good search ad</h3>
<ol>
<li>Put the searched words in the headline.</li>
<li>State a clear benefit and proof: "Same-day laptop repair, 90-day warranty".</li>
<li>Add a clear call to action: "Book a repair today".</li>
<li>Send people to a page that fits the ad, not just to the home page.</li>
<li>Use extensions or assets such as phone number, location and extra links where they are offered.</li>
</ol>
<h3>Common mistakes</h3>
<ul>
<li>Putting all keywords into one ad group.</li>
<li>Never checking the search terms report.</li>
<li>Targeting too wide an area, so you pay for clicks from people you cannot serve.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Choose a local business and write two ad groups with five keywords each.</li>
<li>List five negative keywords.</li>
<li>Write one ad with three headlines and two descriptions, following the steps above.</li>
<li>Name the landing page each ad group should use and why.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Accounts are built from campaigns, ad groups, keywords and ads.</li>
<li>Match types and negative keywords control which searches trigger your ads.</li>
<li>Ad rank combines your bid and your quality, so relevance saves money.</li>
<li>Automated bidding needs accurate conversion tracking and enough data.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Social ads: audiences, creative and retargeting',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to plan a social media ad campaign on a platform such as Meta (Facebook and Instagram), choose audiences and creative formats, and set up retargeting. Social ads interrupt people who did not search, so the quality of your idea matters even more than on search.</p>
<h3>Campaign structure</h3>
<p>Social platforms use a layered structure that is similar across brands:</p>
<ul>
<li><strong>Campaign</strong>: you choose the objective, for example awareness, traffic, leads or sales. The objective tells the system what to optimise for.</li>
<li><strong>Ad set</strong> (or ad group): audience, locations, placements, budget and schedule.</li>
<li><strong>Ad</strong>: the image or video, text, headline, button and destination.</li>
</ul>
<p>Choose the objective closest to the real outcome you want. Choosing "traffic" and hoping for sales usually gets you cheap visits from people who never buy.</p>
<h3>Audience types</h3>
<table>
<thead><tr><th>Type</th><th>Description</th><th>Use</th></tr></thead>
<tbody>
<tr><td>Core / interest</td><td>Age, place, interests and behaviours chosen by you</td><td>Reach new people</td></tr>
<tr><td>Custom</td><td>People who visited your site, watched your videos or engaged with your page, or a customer list you have the right to use</td><td>Retarget and nurture</td></tr>
<tr><td>Lookalike or similar</td><td>People resembling your best customers, found by the platform</td><td>Scale after you have good data</td></tr>
</tbody>
</table>
<p>Do not make audiences too small to learn from, and do not target in ways that treat people unfairly or use sensitive personal traits. Platform rules also limit this.</p>
<h3>Creative that works</h3>
<ol>
<li>The first second matters: show the problem, the result or a face, not a logo.</li>
<li>Design for a phone screen, vertical or square, with captions because many people watch without sound.</li>
<li>One message per ad and one clear button.</li>
<li>Use real people and places from your community rather than stock images when possible.</li>
<li>Make several variations and let the data show which wins.</li>
<li>Be honest. Misleading claims break platform rules and trust.</li>
</ol>
<h3>Retargeting</h3>
<p><strong>Retargeting</strong> (or remarketing) shows ads to people who already interacted with you: visited a page, watched a video, started a form. They are warmer, so results are often cheaper. Rules for good retargeting:</p>
<ul>
<li>Segment by behaviour. Someone who viewed a course page needs a different message from someone who finished enrolling.</li>
<li>Exclude people who already converted.</li>
<li>Limit how often the same person sees the ad. Constant repetition annoys.</li>
<li>Respect consent. Retargeting relies on tracking, so it must follow your privacy notice and the law.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Pick an objective and write why it fits a real goal.</li>
<li>Describe one core audience and one retargeting audience, with exclusions.</li>
<li>Plan three ad creatives that test different opening ideas, such as a problem, a testimonial and an offer.</li>
<li>Write the caption and button for one of them.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Choose the campaign objective that matches the outcome you really want.</li>
<li>Combine core audiences for reach with custom audiences for retargeting.</li>
<li>Mobile-first, honest, single-message creative performs best.</li>
<li>Segment and exclude in retargeting, cap frequency and respect consent.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Testing, tracking and avoiding wasted spend',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to run a fair A/B test, check that conversions are tracked and spot the most common ways advertising money is wasted. Disciplined testing is what separates a learning campaign from an expensive guess.</p>
<h3>A/B testing properly</h3>
<p>An <strong>A/B test</strong> compares two versions that differ in one thing, such as the headline, image or landing page, while everything else stays the same.</p>
<ol>
<li><strong>Write a hypothesis</strong>: "A headline that names the price will raise sign-ups."</li>
<li><strong>Change one thing</strong>. If you change the picture and the text, you will not know which caused the difference.</li>
<li><strong>Split the audience evenly</strong> and run both versions at the same time, never one this week and one next week.</li>
<li><strong>Decide the success measure</strong> before you start, such as cost per sign-up, not clicks alone.</li>
<li><strong>Wait for enough data</strong>. A small difference after 20 clicks is mostly luck. Many tools show a confidence figure; aim for a clear result before picking a winner.</li>
<li><strong>Record and apply</strong> what you learned, then test the next idea.</li>
</ol>
<h3>Check conversion tracking</h3>
<p>Ad platforms optimise toward the conversions they can see. If tracking is broken, automated bidding learns the wrong lessons. Before spending real money:</p>
<ul>
<li>Install the platform's tracking tag or use your analytics link as the platform instructs, and respect your consent setup.</li>
<li>Make a test conversion yourself and confirm it appears in both the ad account and analytics.</li>
<li>Compare the numbers. Small differences are normal because of attribution and consent; large ones suggest a problem.</li>
<li>Where possible record the real value of the conversion.</li>
</ul>
<h3>Where money is wasted</h3>
<table>
<thead><tr><th>Mistake</th><th>Fix</th></tr></thead>
<tbody>
<tr><td>Sending ad traffic to a slow or confusing page</td><td>Improve the landing page before scaling spend</td></tr>
<tr><td>No negative keywords, broad matching everywhere</td><td>Review search terms weekly and add negatives</td></tr>
<tr><td>Wrong location or language targeting</td><td>Restrict to places you really serve</td></tr>
<tr><td>Changing campaigns every day so the system never learns</td><td>Make fewer changes and give tests time</td></tr>
<tr><td>Optimising for clicks or likes instead of results</td><td>Optimise for the action that matters</td></tr>
<tr><td>Ignoring ad fatigue (same ad shown too often)</td><td>Refresh creatives when results fall</td></tr>
<tr><td>Not setting a daily budget or limits</td><td>Set budgets and check spending often</td></tr>
<tr><td>Accepting suggestions blindly</td><td>Read platform recommendations critically</td></tr>
</tbody>
</table>
<h3>Quality and ethics</h3>
<p>Ads must be truthful and follow the platform's policies and local advertising rules. Claims about health, money or guaranteed results are tightly controlled. Avoid targeting vulnerable groups unfairly. Fake testimonials, countdown timers that restart and hidden fees destroy trust and can get accounts banned.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write a hypothesis for an A/B test on a real ad or e-mail subject line.</li>
<li>List what stays the same and what changes.</li>
<li>Decide the success measure and how much data you will wait for.</li>
<li>Review a campaign's settings, real or imagined, and note three possible leaks of money from the table above.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Test one change at a time, at the same time, with a pre-decided measure.</li>
<li>Verify conversion tracking with a test before spending.</li>
<li>Most wasted spend comes from poor targeting, weak landing pages and wrong goals.</li>
<li>Honest advertising protects your account and your reputation.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: In a search ad account, what does an ad group usually contain?
- Billing details
* A tight theme of keywords with ads that match them
- All of the company's campaigns
- Only images
= Ad groups keep keywords and ad text closely matched to one theme.

Q: What are negative keywords for?
- Making ads cheaper at all times
* Blocking searches you do not want to appear for
- Writing bad reviews
- Increasing bid prices
= Terms such as "free" or "jobs" can be blocked to avoid irrelevant clicks.

Q: Ad rank in a search auction depends on...
- Only the highest bid
- Only the company size
* Bid combined with quality factors such as relevance and landing page
- The age of the account
= A relevant ad and good page can beat a higher bid.

Q: Which campaign objective best matches "get enquiries through a form"?
- Awareness
- Video views
* Leads
- Post likes
= Choose the objective closest to the real outcome so the system optimises for it.

Q: Which are good retargeting practices? (choose all that apply)
* Exclude people who already converted
* Limit how often one person sees the ad
- Show the same ad to everyone forever
- Ignore privacy and consent
= Segment, exclude, cap frequency and follow consent rules.

Q: What is wrong with testing a new headline and a new image together?
- Nothing, it is faster
* You will not know which change caused the difference
- Ads cannot have images
- Tests need three versions
= A clean test changes one element at a time.

Q: Before turning on automated bidding for conversions, you should...
- Delete the conversion tag
* Confirm conversions are tracked correctly with a test
- Reduce the budget to zero
- Add more keywords
= Automation learns from the conversions it sees; wrong tracking teaches wrong lessons.

Q: Your ads bring many clicks but few sign-ups, and the landing page takes 12 seconds to load on mobile. What is the best first move?
- Raise the bid
* Fix the landing page speed and clarity
- Add more locations
- Stop tracking
= Paying for visits to a slow page wastes money, so fix the page first.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: E-mail, automation and optimisation',
            'summary' => 'Build lifecycle e-mail and simple automations, improve conversion rates, report to stakeholders and plan a complete campaign.',
            'lessons' => [
                [
                    'title'   => 'Lifecycle e-mail, segmentation and deliverability',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to plan e-mails that match each stage of a customer's relationship with you, divide your list into useful segments and keep your messages out of the spam folder. E-mail is owned, cheap and often the most profitable channel, but only when it is permitted, relevant and well managed.</p>
<h3>Lifecycle e-mail</h3>
<p><strong>Lifecycle e-mails</strong> are sent because of where a person is in their journey with you, not on a random date.</p>
<table>
<thead><tr><th>Moment</th><th>E-mail</th><th>Purpose</th></tr></thead>
<tbody>
<tr><td>Sign-up</td><td>Welcome series (2-4 messages)</td><td>Set expectations, deliver the promised gift, introduce your best content</td></tr>
<tr><td>Interest shown, no action</td><td>Nurture or reminder</td><td>Answer doubts with proof and stories</td></tr>
<tr><td>Purchase or enrolment</td><td>Confirmation and onboarding</td><td>Help them succeed quickly</td></tr>
<tr><td>Quiet for a while</td><td>Re-engagement</td><td>Ask if they still want to hear from you; remove those who do not respond</td></tr>
<tr><td>Happy customer</td><td>Review request, referral</td><td>Build advocacy</td></tr>
</tbody>
</table>
<h3>Permission and segmentation</h3>
<p>Only e-mail people who gave you permission, and make unsubscribing simple. <strong>Segmentation</strong> means dividing the list into groups so each gets a more relevant message. Useful ways to segment: interest (what they clicked or signed up for), stage (lead, customer, past customer), place, how recently they engaged, and source. A segmented message usually earns more opens, clicks and fewer unsubscribes than a message sent to everyone.</p>
<h3>Writing e-mails that work</h3>
<ol>
<li>A clear <strong>sender name</strong> people recognise.</li>
<li>A <strong>subject line</strong> that is honest and specific. Do not trick people into opening.</li>
<li><strong>Preview text</strong> that continues the subject.</li>
<li>One purpose and one main button.</li>
<li>Short paragraphs that read well on a phone, light on images because of data costs.</li>
<li>A plain footer with your organisation's name, contact details and an unsubscribe link.</li>
</ol>
<h3>Deliverability basics</h3>
<p><strong>Deliverability</strong> is whether your message reaches the inbox rather than spam. It depends on your reputation and your setup:</p>
<ul>
<li>Send from your own domain and set up the standard authentication records (SPF, DKIM and DMARC). Your e-mail platform or IT provider can guide you.</li>
<li>Build the list honestly. Never buy lists.</li>
<li>Remove bounced addresses and long-inactive subscribers.</li>
<li>Avoid spammy habits: all capital letters, many exclamation marks, misleading subjects, huge images and attachments.</li>
<li>Watch the rates: unsubscribes, spam complaints and bounces. A sudden rise is a warning.</li>
<li>Increase sending volume gradually if you are new.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Plan a 3-message welcome series for a real organisation: what each e-mail says and when it is sent.</li>
<li>Write the subject line and preview text for each.</li>
<li>Define three segments you could create from simple information.</li>
<li>Check a recent promotional e-mail you received against the writing list above.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Lifecycle e-mails respond to a person's stage, not the calendar.</li>
<li>Use permission-based lists and segment them by interest and behaviour.</li>
<li>Honest subject lines, one purpose and one button work best.</li>
<li>Good reputation, authentication and clean lists protect deliverability.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Marketing automation and conversion optimisation',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to design a simple automated workflow and run a step-by-step conversion rate optimisation (CRO) process. Automation saves time on repeated tasks, and CRO gets more results from the visitors you already have, which is often cheaper than finding new ones.</p>
<h3>Marketing automation in concept</h3>
<p><strong>Marketing automation</strong> uses software to do actions automatically when a <strong>trigger</strong> happens, following rules you define. A workflow has three parts: a trigger, conditions and actions.</p>
<pre>Trigger: someone downloads the free guide
Condition: they are not already a customer
Actions: send guide e-mail now; wait 2 days; send tips e-mail;
         if link clicked, tag as "interested" and notify the team;
         if not, send a reminder after 5 days.</pre>
<p>Typical automations: welcome series, abandoned form or basket reminders, event reminders, follow-up after enquiry, and birthday or anniversary messages. Start with two or three simple flows and check them regularly. Automation also needs a human touch: let people reply, and stop the automatic messages once they buy or opt out.</p>
<p><strong>Lead scoring</strong> gives points for actions such as visiting the price page, so sales staff know whom to call first. Keep the scoring simple and review it against real results.</p>
<h3>Conversion rate optimisation</h3>
<p>The <strong>conversion rate</strong> is conversions divided by visitors. If 2,000 visitors produce 40 enquiries, the rate is 2 percent. CRO is a repeatable process to raise it.</p>
<ol>
<li><strong>Study the data</strong>: find the page or funnel step with the biggest drop-off.</li>
<li><strong>Study people</strong>: watch someone use the page, ask customers why they hesitated, read chat messages and survey answers.</li>
<li><strong>List hypotheses</strong>: "The form has 12 fields, which scares people. Cutting to 5 will raise enquiries."</li>
<li><strong>Rank ideas</strong> by likely impact and ease.</li>
<li><strong>Test</strong> using an A/B test, or compare before and after carefully when traffic is too small.</li>
<li><strong>Learn and repeat</strong>.</li>
</ol>
<h3>Common improvements</h3>
<ul>
<li>A headline that repeats the promise people clicked on.</li>
<li>One clear call-to-action button in a strong, visible position.</li>
<li>Fewer form fields and a mobile money or simple payment option where relevant.</li>
<li>Trust signals: real testimonials, partner logos, contact details, clear prices and policies.</li>
<li>Faster pages and larger tap targets on mobile.</li>
<li>Removing distractions such as extra menus on landing pages.</li>
</ul>
<h3>Honest design</h3>
<p>Avoid <strong>dark patterns</strong>: tricks such as hidden costs, fake scarcity, pre-ticked boxes or hard-to-find cancel buttons. They may lift short-term numbers but damage trust and may break consumer laws.</p>
<h3>Try it yourself</h3>
<ol>
<li>Calculate the conversion rate for a page using real or sample figures.</li>
<li>Write three hypotheses to improve it and rank them by impact and ease.</li>
<li>Draw an automation for a "contact form submitted" trigger with at least three steps and a condition.</li>
<li>Identify one thing in your workflow that should always be done by a human.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Automation follows triggers, conditions and actions; keep flows simple and monitored.</li>
<li>Conversion rate is conversions divided by visitors.</li>
<li>CRO combines data, human insight, hypotheses and tests.</li>
<li>Avoid dark patterns; trust is worth more than a quick click.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Reporting, ethics and the campaign capstone',
                    'minutes' => 30,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to report results clearly to stakeholders and write a complete campaign brief that joins every skill from this course. A good brief and an honest report are what turn marketing knowledge into trusted professional work.</p>
<h3>Reporting to stakeholders</h3>
<p>A <strong>stakeholder</strong> is anyone with an interest in the results: a manager, donor, board member or client. They rarely want every chart. Follow this structure:</p>
<ol>
<li><strong>Summary first</strong>: three sentences on what happened, whether goals were met and what you recommend.</li>
<li><strong>Key results</strong>: the few KPIs against targets and last period, in a simple table or chart.</li>
<li><strong>What worked and what did not</strong>, with reasons backed by evidence.</li>
<li><strong>Learnings</strong> from tests, including failed ones.</li>
<li><strong>Next steps and budget request</strong>.</li>
<li><strong>Notes on data limits</strong>: tracking gaps, small samples, attribution model used.</li>
</ol>
<p>Use plain language, avoid jargon, and never hide poor results. A report that includes bad news and a plan to fix it earns more trust than one that only celebrates.</p>
<h3>Ethics checklist</h3>
<ul>
<li>Claims are true and can be proven.</li>
<li>People gave permission for e-mails and tracking where required.</li>
<li>Data is collected sparingly and kept secure.</li>
<li>Testimonials and reviews are real.</li>
<li>Audiences are treated fairly and vulnerable people are not exploited.</li>
<li>Advertising is clearly recognisable as advertising, and partnerships are disclosed.</li>
</ul>
<h3>Campaign capstone brief</h3>
<p>Use this template to bring everything together. Fill each part with specific facts and numbers.</p>
<pre>1. Background: organisation, offer, why now
2. Objective and key results (OKR)
3. Audience profiles and insights
4. Positioning and main message
5. Funnel plan: content and channel for each stage
6. SEO plan: keywords, pillar and cluster pages, technical fixes
7. Paid plan: platform, structure, audiences, creative tests, budget
8. E-mail and automation plan
9. Measurement plan: events, conversions, UTM guide, dashboard
10. Budget and timeline (weekly plan for 12 weeks)
11. Risks, privacy and ethics notes
12. Reporting schedule and review dates</pre>
<h3>Worked mini-example</h3>
<p>A youth training centre wants 150 enrolments for its next intake. Its brief might set a key result of 1,500 qualified enquiries, use search ads and short videos for awareness, build a "how to start learning computers" pillar page with five cluster articles, retarget website visitors, send a four-message nurture series to everyone who downloaded a guide, track enquiries as conversions with UTM-tagged links, and report every Friday on cost per enquiry and enrolment rate.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose a real or imagined organisation and a goal for the next three months.</li>
<li>Complete all twelve parts of the brief in a document, with numbers where possible.</li>
<li>Create a one-page summary report template with a space for each of the six report elements.</li>
<li>Ask a friend to read the brief and say what they do not understand, then improve it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Reports lead with the summary, show KPIs against targets and state data limits.</li>
<li>Honest reporting, including failures, builds long-term trust.</li>
<li>An ethics checklist keeps campaigns truthful, fair and lawful.</li>
<li>A full brief links strategy, SEO, ads, e-mail, measurement, budget and review.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which is a lifecycle e-mail?
- A random weekly blast to everyone
* A welcome series sent after sign-up
- A purchased list mailing
- An e-mail with only a large image
= Lifecycle messages respond to where the person is in their journey.

Q: What does segmentation mean?
- Deleting inactive subscribers
* Dividing a list into groups so messages are more relevant
- Sending the same e-mail twice
- Changing the sender name
= Relevant messages to a defined group usually perform better than a message to all.

Q: Which actions protect deliverability? (choose all that apply)
* Setting up SPF, DKIM and DMARC for your domain
* Removing bounced and long-inactive addresses
- Buying an e-mail list
- Using all capital letters in subject lines
= Reputation and clean permission-based lists keep you out of spam.

Q: 2,000 visitors produce 40 enquiries. What is the conversion rate?
- 0.2%
- 4%
* 2%
- 20%
= 40 divided by 2,000 equals 0.02, or 2 percent.

Q: Which element is part of every automation workflow?
- A fixed colour scheme
* A trigger and an action
- A paid ad
- A cookie banner
= Automations run actions when a trigger happens, optionally with conditions.

Q: Which is a dark pattern?
- A clear price shown at the start
* A cancel button that is hidden from the user
- A simple form with five fields
- A genuine testimonial
= Dark patterns manipulate people and damage trust.

Q: What should a stakeholder report contain first?
- Every raw chart
* A short summary of results and recommendations
- Technical settings of each tool
- A list of all keywords
= Stakeholders need the conclusion first, with detail available after.

Q: Which is the best way to deal with a campaign that missed its target?
- Remove it from the report
- Blame the platform
* Report it honestly with likely causes and a plan to improve
- Change the target afterwards
= Honest reporting with learning builds credibility.
QUIZ,
        ],
    ],

    'final_exam' => <<<'QUIZ'
Q: Which is the best first step when a funnel gets many visitors but few conversions?
- Increase advertising spend
- Start a new social media channel
* Find the stage with the biggest drop-off and fix it
- Rewrite the company history page
= Fixing the leak makes every future visitor more valuable.

Q: A positioning statement should include...
* A specific audience, a need, a benefit and a difference from alternatives
- Only the year the company began
- A list of every product
- Slogans with the word "best"
= Positioning is clear about for whom, what and why you are different.

Q: You have a small test budget. Which approach is most sensible?
- Spend it all in the first day on every platform
* Choose two or three channels and run measured tests
- Buy followers
- Skip measurement to save money
= Focus and measurement make the budget produce learning.

Q: Which is a vanity metric when it stands alone?
- Cost per acquisition
- Conversion rate
* Total number of page likes
- Revenue
= Likes may not relate to sales, sign-ups or impact.

Q: Which attribution model gives all credit to the final contact before conversion?
- First click
- Linear
- Time decay
* Last click
= Last click credits only the final interaction.

Q: In the search process, what happens in indexing?
* The engine analyses and stores page information in its database
- A user clicks an advert
- Ads are shown
- Links are bought
= Only indexed pages can appear in results.

Q: A searcher types "buy school shoes Gulu". What intent is this?
- Informational
- Navigational
* Transactional
- No intent
= The words show a readiness to buy.

Q: Why are long-tail keywords useful?
- They always have the most searches
* They are specific, less competitive and often closer to a decision
- They are free of any difficulty
- They never need good content
= Specific phrases are easier to win and attract focused visitors.

Q: What is a sensible title tag practice?
- Use the same title for all pages
- Write 200 characters
* Make it unique, put the main topic first and keep it short
- Leave it empty
= A clear, unique title helps both ranking understanding and clicks.

Q: Which of these is part of technical SEO? (choose all that apply)
* A fast mobile-friendly site
* An XML sitemap
- Buying hundreds of links
- Hiding text in the background colour
= Speed, mobile use and sitemaps help crawling and usability; the others are manipulation.

Q: What is the best way to earn backlinks safely?
- Pay a link network
* Publish a genuinely useful resource others want to reference
- Spam forums with your link
- Copy a competitor's article
= Original useful material earns links without penalty risk.

Q: Which practice helps local SEO?
- Using different business names on each website
- Buying positive reviews
* Keeping name, address and phone identical and completing a business profile
- Hiding opening hours
= Consistency and complete information help local ranking.

Q: In search console reports, a high number of impressions with a low CTR suggests...
- The page is not indexed
* The title or description could be more appealing
- The site has no mobile version
- Nothing can be done
= People see the result but few click, so improve the snippet.

Q: In GA4-style analytics, what is a conversion or key event?
- Any page load
* An event you mark as important to your goal
- A cookie
- A paid click
= Marking only meaningful actions keeps measurement useful.

Q: Which UTM tag shows the channel type, such as email or paid_social?
- utm_campaign
- utm_content
* utm_medium
- utm_name
= Source is where, medium is how, campaign is which activity.

Q: Why should you not add UTM tags to internal links?
- They are against the law
- They make pages load slower
* They overwrite the true source of the visit
- They are not visible
= Internal tagging breaks attribution.

Q: A funnel report shows...
- The colour of buttons
* How many users continue or drop off at each step
- Server temperature
- Competitor budgets
= It reveals where people leave a journey.

Q: Which is a data-interpretation mistake?
- Comparing with the same season last year
* Concluding a cause from two numbers that moved together
- Stating the data limits
- Checking the date range
= Correlation alone does not prove cause.

Q: Which is a good privacy habit?
- Collect everything in case it is useful
* Collect only what you need, explain it and keep it secure
- Hide the privacy policy
- Share contact lists freely
= Transparency, minimisation and security are core data protection principles.

Q: In a search ad account, which is the correct hierarchy from top to bottom?
- Ad, keyword, ad group, campaign
- Keyword, campaign, ad, ad group
* Campaign, ad group, keywords and ads
- Ad group, account, campaign
= Campaigns contain ad groups, which contain keywords and ads.

Q: What do ad rank and quality factors mean for advertisers?
- Bigger bids always win
* Relevant ads and good pages can win better positions for less
- Quality is not measured
- Only brand size matters
= Platforms reward useful ads.

Q: Which is a correct use of retargeting?
- Showing the same ad hundreds of times
* Showing a tailored message to people who viewed a page, excluding those who converted
- Using lists of people who never agreed to contact
- Targeting by guessing sensitive personal traits
= Relevant, limited and consent-respecting retargeting works best.

Q: When running an A/B test you should...
* Change one element and run both versions at the same time
- Change five elements at once
- Run version A in March and version B in June
- Stop after ten clicks
= A fair test isolates one change in equal conditions.

Q: Which setup check should come before automated bidding on conversions?
- Remove the conversion tracking
* Confirm that conversions are recorded correctly
- Set the budget to zero
- Use only broad match
= Automated bidding relies on accurate conversion data.

Q: A welcome series is an example of...
- A paid search campaign
* Lifecycle e-mail
- Technical SEO
- Link building
= It is triggered by the sign-up moment.

Q: Which supports good e-mail deliverability? (choose all that apply)
* Authenticating your sending domain
* Honest, permission-based list building
- Buying lists
- Misleading subject lines
= Reputation and permission are the foundation.

Q: The conversion rate is calculated as...
- Visitors divided by conversions
* Conversions divided by visitors
- Clicks divided by cost
- Impressions divided by clicks
= For 40 conversions from 2,000 visitors the rate is 2 percent.

Q: Which best describes marketing automation?
- Writing e-mails by hand each time
* Software performing actions when a trigger and conditions are met
- Buying more ad space
- Removing all human contact
= Triggers, conditions and actions make automation, ideally with human oversight.

Q: Which is a dark pattern to avoid?
- A clear refund policy
- A real customer review
* Fake countdown timers that restart
- A simple signup form
= Fake urgency manipulates people and harms trust.

Q: A good stakeholder report begins with...
- Raw data tables
- A long history of the project
* A short summary with results and recommendations
- A list of tools used
= Lead with what matters and keep details for later.
QUIZ,
];
