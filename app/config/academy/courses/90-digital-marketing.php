<?php
return [
    'slug'        => 'digital-marketing',
    'title'       => 'Digital Marketing Fundamentals',
    'category'    => 'marketing',
    'level'       => 'beginner',
    'icon'        => 'megaphone',
    'sort'        => 90,
    'summary'     => 'Learn how to promote a small business, project or NGO online: know your audience, create content, use search, email and ads, and measure what works.',
    'description' => '<p>Digital marketing means using the internet and mobile phones to tell people about your work and to bring them closer to it. A shop owner in Gulu, a youth group, a school club and a community organisation can all use it, often with very little money.</p><p>This course takes you from the basic ideas to a real 30-day campaign plan. You will learn who your audience is, how to write and plan content, how search engines, email, social media and ads work, and how to measure results honestly.</p><p>No experience is needed. If you can use a phone or a computer and browse the internet, you can start.</p>',
    'outcomes'    => [
        'Explain what digital marketing is and name the main channels',
        'Set a clear SMART goal and choose simple measures of success',
        'Describe your audience with a buyer persona and write a value proposition',
        'Plan and write content for social media using a content calendar',
        'Apply basic search engine optimisation to a page or business listing',
        'Build a consent-based email list and write a clear newsletter',
        'Set up a small online ad campaign with a sensible budget and test',
        'Read basic analytics, report results and plan a 30-day campaign',
    ],
    'requirements' => [
        'A smartphone, laptop or computer with internet access',
        'No experience needed',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Google Skillshop (free marketing courses)', 'url' => 'https://skillshop.withgoogle.com'],
        ['title' => 'Google Search Central: SEO starter guide', 'url' => 'https://developers.google.com/search/docs/fundamentals/seo-starter-guide'],
        ['title' => 'Google Analytics Help', 'url' => 'https://support.google.com/analytics'],
        ['title' => 'Meta Business Help Centre', 'url' => 'https://www.facebook.com/business/help'],
        ['title' => 'Personal Data Protection Office, Uganda', 'url' => 'https://www.pdpo.go.ug'],
    ],
    'modules' => [
        // ------------------------------------------------------------------ MODULE 1
        [
            'title'   => 'Module 1: The digital marketing landscape',
            'summary' => 'What digital marketing is, the main channels, the customer journey, and how to set goals.',
            'lessons' => [
                [
                    'title'   => 'What digital marketing is and why it matters',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain digital marketing in your own words and say why even a very small business or organisation should care about it. In Uganda more and more people find shops, schools, clinics and charities through their phones first, so being easy to find online is now part of being trusted.</p>
<h3>What is marketing?</h3>
<p><strong>Marketing</strong> is everything you do to help the right people learn about your product or service, trust it and choose it. A banner outside a shop is marketing. So is a radio announcement, a word from a happy customer, or a clear price list.</p>
<h3>What makes it digital?</h3>
<p><strong>Digital marketing</strong> is marketing done through digital tools: websites, search engines, social media, email, messaging apps such as WhatsApp, and online adverts. The big difference from older methods is that you can often see, almost at once, who looked at your message and what they did next.</p>
<table>
<thead><tr><th>Traditional marketing</th><th>Digital marketing</th></tr></thead>
<tbody>
<tr><td>Poster, radio, flyer, church or market announcement</td><td>Facebook post, WhatsApp status, website, email, online advert</td></tr>
<tr><td>Hard to know how many people noticed</td><td>You can see views, clicks, replies and messages</td></tr>
<tr><td>Often costs a fixed amount upfront</td><td>You can start free and spend a little at a time</td></tr>
<tr><td>Reaches mostly people nearby</td><td>Can reach people nearby, or across the world</td></tr>
</tbody>
</table>
<p>Neither is better in every case. Many good campaigns use both, for example a poster at the trading centre that also shows a WhatsApp number.</p>
<h3>Why it matters for small organisations</h3>
<ul>
<li><strong>Low cost.</strong> You can begin with a free Facebook page and a WhatsApp Business account.</li>
<li><strong>Two-way talk.</strong> People can ask questions and you can answer, which builds trust.</li>
<li><strong>You can measure.</strong> You learn what works and stop wasting money on what does not.</li>
<li><strong>Fair competition.</strong> A small tailor with good photos can look as professional as a big shop.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Pick a real small business or organisation you know, such as a salon, a school or a youth group.</li>
<li>On your phone, search for it by name. Write down what you find: a Facebook page, a map listing, a website, or nothing.</li>
<li>Write three sentences: what is easy to find, what is missing, and what one online step would help most.</li>
<li>Share your three sentences with a friend and ask whether they agree.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Marketing is helping the right people learn about, trust and choose what you offer.</li>
<li>Digital marketing uses websites, search, social media, email, messaging and online ads.</li>
<li>Its main strengths are low starting cost, two-way conversation and the ability to measure.</li>
<li>It works best together with offline methods, not instead of them.</li>
</ul>
<h3>Common mistakes</h3>
<p>Many beginners think they need a lot of money or a big following to start. You do not. Start small, be consistent and learn from the numbers.</p>
HTML,
                ],
                [
                    'title'   => 'The main digital channels',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will know the six main digital marketing channels and be able to choose which ones suit a small business or NGO. You cannot be everywhere, so choosing well saves time and money.</p>
<h3>1. Search</h3>
<p>People type a question into Google, such as "best boda boda repair near me". If your business appears in the results, you gain visitors who already want what you offer. This is done through <strong>SEO</strong> (making your pages easy for search engines to understand) and through paid search adverts.</p>
<h3>2. Social media</h3>
<p>Platforms like Facebook, Instagram, TikTok, YouTube, X and LinkedIn let you share posts, photos and videos and talk with followers. They are good for building relationships and showing your work.</p>
<h3>3. Email</h3>
<p>Email reaches people who have agreed to hear from you. It is excellent for newsletters, receipts, event reminders and updates to donors or customers. Email addresses belong to you; you do not depend on one platform.</p>
<h3>4. Content</h3>
<p><strong>Content marketing</strong> means giving useful information for free: a blog post on how to start a vegetable garden, a short video on how to write a CV. People trust you because you helped them first.</p>
<h3>5. Display and online ads</h3>
<p>These are paid adverts that appear on websites, apps, search results and social feeds. You choose who sees them, for example women aged 20 to 35 within 20 kilometres of Gulu.</p>
<h3>6. Messaging</h3>
<p>WhatsApp, SMS and Telegram let you speak directly with customers. In East Africa WhatsApp is often the place where a sale actually happens, so it deserves a serious place in your plan.</p>
<h3>Choosing your channels</h3>
<p>Ask three questions: Where is my audience already? What can I do well and keep doing every week? What does my goal need? A hairdresser may choose Instagram, WhatsApp and a Google Business listing. A school may choose Facebook, email to parents and a simple website.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write the six channels on a sheet of paper.</li>
<li>Next to each one, give a score from 0 to 5 for how many of your audience use it.</li>
<li>Give each a second score for how easy it is for you to use it every week.</li>
<li>Circle the two or three channels with the highest total. These are your starting channels.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The six main channels are search, social media, email, content, online ads and messaging.</li>
<li>Choose channels where your audience already spends time and that you can keep up.</li>
<li>Two or three well-used channels beat ten neglected ones.</li>
</ul>
<h3>Common mistakes</h3>
<p>Opening accounts on every platform and then posting nothing makes your organisation look inactive. It is better to be active on fewer channels.</p>
HTML,
                ],
                [
                    'title'   => 'The customer journey and the marketing funnel',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe the steps a person takes before they buy or join, and match your messages to each step. This matters because a person who has never heard of you needs a very different message from a person who is ready to pay.</p>
<h3>The journey</h3>
<p>The <strong>customer journey</strong> is the path a person follows from first hearing about you to becoming a regular customer or supporter. A simple picture of this path is called a <strong>funnel</strong>, because many people enter at the top and fewer reach the bottom.</p>
<ol>
<li><strong>Awareness.</strong> The person discovers you exist. For example they see a short video or a friend shares your post.</li>
<li><strong>Interest.</strong> They look more closely: they visit your page, read reviews or watch more videos.</li>
<li><strong>Consideration.</strong> They compare you with other options, ask about prices and read comments.</li>
<li><strong>Action.</strong> They buy, register, donate or visit.</li>
<li><strong>Loyalty and advocacy.</strong> They come back and tell other people.</li>
</ol>
<h3>Matching messages to each step</h3>
<table>
<thead><tr><th>Step</th><th>What the person needs</th><th>Example content</th></tr></thead>
<tbody>
<tr><td>Awareness</td><td>A reason to notice you</td><td>An eye-catching photo, short video or helpful tip</td></tr>
<tr><td>Interest</td><td>To learn what you do</td><td>An about page, a story, a how-it-works post</td></tr>
<tr><td>Consideration</td><td>Proof and clear details</td><td>Prices, customer reviews, before and after photos, answers to questions</td></tr>
<tr><td>Action</td><td>An easy way to act</td><td>A clear button, WhatsApp number or form</td></tr>
<tr><td>Loyalty</td><td>To feel valued</td><td>Thank-you message, update, small offer, request for a review</td></tr>
</tbody>
</table>
<h3>An example</h3>
<p>A tailor posts a photo of a finished dress (awareness). A woman visits the page and sees more designs and a price list (interest and consideration). She messages the WhatsApp number and orders (action). The tailor sends a thank-you message and asks for a photo of her wearing it (loyalty). Notice each step has a job.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose a product or service you know well.</li>
<li>Draw five boxes in a row and label them with the five steps.</li>
<li>Under each box write one thing a person would see or do at that step.</li>
<li>Find the step where you think people are lost. This is where your next effort should go.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>People move through awareness, interest, consideration, action and loyalty.</li>
<li>Each step needs a different kind of message.</li>
<li>Keeping existing customers happy is usually cheaper than finding new ones.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Setting SMART goals and choosing KPIs',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a clear marketing goal and choose a few numbers to show whether you are succeeding. Without a goal you cannot tell if your work is helping, and you may spend months posting without a result.</p>
<h3>What is a SMART goal?</h3>
<p>A <strong>SMART</strong> goal has five qualities:</p>
<ul>
<li><strong>Specific:</strong> it says exactly what you want.</li>
<li><strong>Measurable:</strong> you can count it.</li>
<li><strong>Achievable:</strong> it is possible with your time and money.</li>
<li><strong>Relevant:</strong> it supports what the organisation really needs.</li>
<li><strong>Time-bound:</strong> it has a deadline.</li>
</ul>
<p>A weak goal: "Get more customers online." A SMART goal: "Receive 30 new WhatsApp enquiries from our Facebook page between 1 March and 30 April."</p>
<h3>What is a KPI?</h3>
<p>A <strong>KPI</strong> (key performance indicator) is a number you watch to know if you are moving toward the goal. Choose few; three to five are plenty.</p>
<table>
<thead><tr><th>Goal type</th><th>Useful KPIs</th></tr></thead>
<tbody>
<tr><td>Be known (awareness)</td><td>Reach, video views, new followers</td></tr>
<tr><td>Get interest</td><td>Website visits, link clicks, comments</td></tr>
<tr><td>Get results</td><td>Enquiries, sign-ups, sales, donations</td></tr>
<tr><td>Keep people</td><td>Repeat customers, email open rate</td></tr>
</tbody>
</table>
<h3>Beware of vanity numbers</h3>
<p>A <strong>vanity metric</strong> looks good but does not show real progress. Ten thousand followers who never buy is less useful than 300 followers who often send enquiries. Always connect your KPIs to the real result you want.</p>
<h3>Write your goal in three steps</h3>
<ol>
<li>Start with the real need: more students enrolled, more customers, more volunteers.</li>
<li>Turn it into a number and a date.</li>
<li>List the three KPIs that show progress, and note where you will read each one (for example Facebook Insights or a notebook of orders).</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Write one weak goal for a business or group you know.</li>
<li>Rewrite it as a SMART goal. Check each of the five letters.</li>
<li>Choose three KPIs and write where you can find each number.</li>
<li>Decide how often you will check them, for example every Friday.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A SMART goal is specific, measurable, achievable, relevant and time-bound.</li>
<li>KPIs are the few numbers that show progress toward the goal.</li>
<li>Prefer numbers linked to real results over vanity numbers like total likes.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which of these is the best definition of digital marketing?
- Printing posters with a phone number
* Promoting products or services using websites, search, social media, email, messaging and online ads
- Buying a computer for the office
- Having a Facebook account for personal photos
= Digital marketing means using digital channels to help the right people find, trust and choose what you offer.

Q: A small shop wants to be found by people who search for "phone repair Gulu". Which channel helps most directly?
- Email newsletters
- Display adverts on unrelated websites
* Search, through SEO and a map listing
- Printed receipts
= Search puts you in front of people who are already looking for what you offer.

Q: Which TWO are real strengths of digital marketing for a small organisation? (choose all that apply)
* You can start with little or no money
- You never need to answer customer questions
* You can see how people respond to your posts
- It always replaces offline marketing
= Digital marketing is cheap to start and measurable, but it works best together with offline methods and still needs real conversations.

Q: A person is comparing your prices with two other sellers and reading your reviews. Which funnel step is this?
- Awareness
- Loyalty
* Consideration
- Action
= Comparing options and looking for proof is the consideration step.

Q: Which is a SMART goal?
- Get more followers
- Be the best salon online
* Get 40 new WhatsApp enquiries from Instagram between 1 June and 31 July
- Post as much as possible
= It is specific, measurable, achievable, relevant and has a deadline.

Q: Your page has 8,000 followers but almost nobody orders. What is the main lesson?
- Followers are the only number that matters
* Followers alone are a vanity metric; track enquiries and sales as well
- You should buy more followers
- Stop posting
= Numbers should connect to the real result you want.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 2
        [
            'title'   => 'Module 2: Know your audience and your brand',
            'summary' => 'Buyer personas, free research, your value proposition, brand voice, competitor analysis and a simple marketing plan.',
            'lessons' => [
                [
                    'title'   => 'Understanding your audience with buyer personas',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe your ideal customer or supporter in detail and use that description to decide what to say and where to say it. When you try to talk to everyone, you usually speak clearly to no one.</p>
<h3>What is a buyer persona?</h3>
<p>A <strong>buyer persona</strong> is a fictional but realistic description of a typical person you want to reach, built from what you really know about your audience. It is not a stereotype; it is a working sketch to help you make decisions.</p>
<h3>What to include</h3>
<ul>
<li><strong>Basic facts:</strong> age range, where they live, work or study, language.</li>
<li><strong>Daily life:</strong> how they spend their day, which phone they use, how much data they can afford.</li>
<li><strong>Needs and problems:</strong> what they are trying to achieve and what blocks them.</li>
<li><strong>Where they spend time online:</strong> WhatsApp groups, Facebook, TikTok, radio, local events.</li>
<li><strong>What makes them trust you:</strong> reviews, recommendations, seeing your shop, a respected leader.</li>
<li><strong>What stops them acting:</strong> price, distance, fear of being cheated, lack of time.</li>
</ul>
<h3>An example</h3>
<p><em>Grace, 24, finished senior six and wants to learn computer skills to find work. She uses an Android phone, buys small data bundles, is active on WhatsApp and Facebook, and worries about fees. She trusts what friends and former teachers recommend.</em></p>
<p>From this sketch you can see what to do: use short WhatsApp-friendly messages, mention the free or low price clearly, share stories of other young people, and post in the evening when she has time.</p>
<h3>How to gather real information</h3>
<ol>
<li>Talk to ten real customers or beneficiaries. Ask simple open questions: "How did you hear about us?" "What almost stopped you?"</li>
<li>Read comments and messages you already receive.</li>
<li>Note the questions people ask most often.</li>
<li>Write one persona for each main group. Two or three personas are enough.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Choose the group you most want to reach.</li>
<li>Ask two or three real people from that group two questions: where they look for information, and what worries them.</li>
<li>Write a persona of about eight lines using the headings above.</li>
<li>Underline two decisions your persona helps you make, such as the platform and the language.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A persona is a realistic sketch of a typical person you want to reach.</li>
<li>Build it from real conversations, not guesses.</li>
<li>Use it to choose channels, language, timing and offers.</li>
</ul>
<h3>Common mistakes</h3>
<p>Do not use a persona to make assumptions about people because of their tribe, religion or background. Base it on needs and behaviour, and keep checking it against real people.</p>
HTML,
                ],
                [
                    'title'   => 'Simple market research and competitor analysis',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to find out what people want and what others in your field are doing, using free tools and your own observation. Good research prevents you from spending money on something nobody asked for.</p>
<h3>Free ways to research your market</h3>
<ul>
<li><strong>Ask directly.</strong> Short surveys with Google Forms, a WhatsApp poll or simple questions at your counter.</li>
<li><strong>Read what people say.</strong> Comments, reviews and questions on your page and on similar pages.</li>
<li><strong>Use search.</strong> Type a few words into Google and read the suggestions that appear as you type; they show what people commonly search for. The "People also ask" box does the same.</li>
<li><strong>Google Trends.</strong> A free tool that shows how interest in a topic rises or falls over time and by region. It is helpful for seasonal products such as school supplies.</li>
<li><strong>Social platforms.</strong> Look at which posts in your field get many comments and shares.</li>
</ul>
<h3>Looking at competitors</h3>
<p>A <strong>competitor</strong> is anyone your audience could choose instead of you. This includes direct competitors (another computer training centre) and indirect ones (a YouTube tutorial that teaches the same skill for free).</p>
<ol>
<li>List three to five competitors.</li>
<li>Visit their pages and websites. Note what they offer, how they price, how often they post and how people respond.</li>
<li>Write down their strengths and weaknesses honestly.</li>
<li>Find a gap: something your audience wants that nobody does well.</li>
</ol>
<table>
<thead><tr><th>Question</th><th>Competitor A</th><th>Competitor B</th><th>Us</th></tr></thead>
<tbody>
<tr><td>What do they offer?</td><td></td><td></td><td></td></tr>
<tr><td>How do they price?</td><td></td><td></td><td></td></tr>
<tr><td>Which channels do they use?</td><td></td><td></td><td></td></tr>
<tr><td>How do customers respond?</td><td></td><td></td><td></td></tr>
<tr><td>What could we do better?</td><td></td><td></td><td></td></tr>
</tbody>
</table>
<p>The aim is to learn and to stand out, never to copy. Copying another person's photos, words or designs is dishonest and may break copyright law.</p>
<h3>Try it yourself</h3>
<ol>
<li>Draw the table above on paper with the names of two real competitors.</li>
<li>Fill in each box using what you can see on their public pages.</li>
<li>In the "Us" column, be honest about your own position.</li>
<li>Write one sentence beginning "A gap we can fill is...".</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Free research includes asking people, reading comments, search suggestions and Google Trends.</li>
<li>Competitors include indirect ones, such as free online tutorials.</li>
<li>Study competitors to learn and find a gap, not to copy.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Value proposition, brand voice and a simple marketing plan',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain in one sentence why someone should choose you, describe how your brand speaks, and bring your ideas together in a one-page marketing plan.</p>
<h3>Your value proposition</h3>
<p>A <strong>value proposition</strong> is a short statement of the benefit you give, to whom, and what makes you different. A simple pattern is:</p>
<pre><code>We help [who] to [get what result] by [how], unlike [alternative].</code></pre>
<p>Example: "We help young people in Gulu to find work by teaching practical computer skills in short evening classes, with free practice time on our computers."</p>
<p>Notice it talks about the benefit to the person, not only about you. Test it: would your persona understand it in five seconds?</p>
<h3>Brand identity and voice</h3>
<p>Your <strong>brand</strong> is how people think and feel about you. Its parts include:</p>
<ul>
<li><strong>Name and logo.</strong></li>
<li><strong>Colours and fonts</strong> you use every time.</li>
<li><strong>Voice:</strong> the personality of your words. Is it warm and friendly, serious and professional, or playful? Choose three words, such as "warm, clear, respectful", and write like that everywhere.</li>
<li><strong>Promises you keep:</strong> reliability and honesty matter more than any logo.</li>
</ul>
<h3>A one-page marketing plan</h3>
<p>A plan does not have to be long. One page that you really use is better than twenty pages in a drawer.</p>
<ol>
<li><strong>Goal:</strong> your SMART goal.</li>
<li><strong>Audience:</strong> your main persona in two lines.</li>
<li><strong>Message:</strong> your value proposition.</li>
<li><strong>Channels:</strong> two or three, with how often you will post.</li>
<li><strong>Content ideas:</strong> five topics your audience cares about.</li>
<li><strong>Budget:</strong> money and hours per week.</li>
<li><strong>Measures:</strong> your three KPIs and when you will check them.</li>
<li><strong>Review date:</strong> when you will look at results and adjust.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Write your value proposition using the pattern above. Read it aloud to someone and ask what they understood.</li>
<li>Choose three words that describe your brand voice.</li>
<li>Fill in the eight parts of the one-page plan for a real or imagined project.</li>
<li>Put a review date in your calendar.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A value proposition states who you help, the result, and what makes you different.</li>
<li>A consistent voice, name, colours and honest behaviour build a brand.</li>
<li>A one-page plan covers goal, audience, message, channels, content, budget, measures and review date.</li>
</ul>
<h3>Common mistakes</h3>
<p>Do not promise things you cannot deliver. A false promise may bring one sale but loses trust for a long time.</p>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is a buyer persona?
* A realistic sketch of a typical person you want to reach, based on real information
- A list of every customer's phone number
- A guess about people based on their tribe
- A paid advert
= A persona helps you make decisions about content and channels. It must come from real information, not stereotypes.

Q: Which is the best way to learn what your audience really worries about?
- Guess from your own opinion
- Copy a competitor
* Talk to or survey real members of the audience
- Wait for sales to fall
= Direct conversations give you true information at almost no cost.

Q: Which free tool shows how interest in a topic changes over time and by region?
- Microsoft Paint
* Google Trends
- A spreadsheet of expenses
- A calculator
= Google Trends is free and shows search interest over time.

Q: A free YouTube tutorial that teaches the same skill as your class is an example of
- Not a competitor at all
- A direct competitor only
* An indirect competitor
- Your best customer
= Anything your audience might choose instead of you is competition, including free alternatives.

Q: Which of these is the best value proposition?
- We are the best in the world
- Call us now
* We help young people in Gulu find work by teaching practical computer skills in evening classes
- Established many years ago
= It states who you help, the result and how, using words the audience will understand.

Q: Which TWO belong in a one-page marketing plan? (choose all that apply)
* A review date
- A list of everything competitors do wrong
* Three KPIs
- A promise to be everywhere online
= A plan needs measures and a date to check them. It does not need attacks on competitors or a promise to be everywhere.

Q: Your brand voice is described as "warm, clear and respectful". What should you do?
* Use that voice on every channel and in every reply
- Change style for every post
- Use it only in the logo
- Ignore it when you are busy
= A consistent voice helps people recognise and trust you.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 3
        [
            'title'   => 'Module 3: Content and social media marketing',
            'summary' => 'Types of content, planning with a calendar, writing for people, and using each social platform well and honestly.',
            'lessons' => [
                [
                    'title'   => 'Content types and the content calendar',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to list the kinds of content you can make and plan a week or month of posts in a simple calendar. Planning keeps you consistent and removes the daily stress of wondering what to post.</p>
<h3>Types of content</h3>
<ul>
<li><strong>Photos:</strong> your work, your team, products, events.</li>
<li><strong>Short videos:</strong> demonstrations, quick tips, behind the scenes.</li>
<li><strong>Text posts:</strong> announcements, questions, short stories.</li>
<li><strong>Graphics:</strong> posters, quotes, price lists, infographics.</li>
<li><strong>Longer articles:</strong> blog posts or guides on your website.</li>
<li><strong>Customer stories and reviews:</strong> with permission.</li>
<li><strong>Live sessions:</strong> questions and answers, events.</li>
</ul>
<h3>A helpful mix</h3>
<p>If every post says "buy now" people stop listening. A common rule is to mix four purposes:</p>
<table>
<thead><tr><th>Purpose</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Educate</td><td>Three tips to keep a phone battery healthy</td></tr>
<tr><td>Inspire or tell a story</td><td>A graduate of your course tells how she found work</td></tr>
<tr><td>Entertain or connect</td><td>A friendly question or a light moment in the office</td></tr>
<tr><td>Promote</td><td>New class starting Monday; here is how to register</td></tr>
</tbody>
</table>
<p>A good guide is that most posts help or connect, and only some directly promote.</p>
<h3>Building a content calendar</h3>
<p>A <strong>content calendar</strong> is a table that shows what you will post, where and when. A spreadsheet or even a paper notebook works.</p>
<ol>
<li>Decide how often you can post. Three times a week that you keep is better than daily for two weeks.</li>
<li>Make columns: Date, Platform, Purpose, Idea, Format, Who prepares it, Status.</li>
<li>Add fixed dates first: public holidays, school terms, market days, events.</li>
<li>Fill the other days with ideas from your persona's questions.</li>
<li>Prepare posts in batches, for example one afternoon to make a week's content.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Open a spreadsheet or draw a table with the columns above.</li>
<li>Plan seven days of posts for a business or group you know.</li>
<li>Make sure at least three different purposes from the table appear.</li>
<li>Prepare the first post (text and photo) and keep it ready.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Content comes in many forms; choose those your audience enjoys and you can make.</li>
<li>Mix educating, inspiring, connecting and promoting.</li>
<li>A content calendar keeps you consistent and saves time.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Writing for the web and copywriting basics',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write short, clear marketing text that people actually read and act on. Most readers look at a post for only a few seconds on a phone, so every word must work.</p>
<h3>Write for the reader</h3>
<ul>
<li>Use short sentences and everyday words. Write "buy" not "purchase".</li>
<li>Talk about "you", the reader, more than "we".</li>
<li>Put the most important point first.</li>
<li>Keep paragraphs to one to three lines.</li>
<li>Use the language your audience uses, which may be English, Luo, Luganda or a mix. Be clear and respectful.</li>
</ul>
<h3>Features and benefits</h3>
<p>A <strong>feature</strong> is a fact about what you offer. A <strong>benefit</strong> is what that fact does for the person. People respond to benefits.</p>
<table>
<thead><tr><th>Feature</th><th>Benefit</th></tr></thead>
<tbody>
<tr><td>Evening classes, 6 to 8 pm</td><td>You can study and still work during the day</td></tr>
<tr><td>Free practice time</td><td>You can learn without owning a computer</td></tr>
<tr><td>Certificate of completion</td><td>You can show employers what you have learned</td></tr>
</tbody>
</table>
<h3>A simple post structure</h3>
<ol>
<li><strong>Hook:</strong> a first line that makes someone stop. A question or a surprising fact works well.</li>
<li><strong>Value:</strong> the helpful or interesting part.</li>
<li><strong>Call to action (CTA):</strong> one clear next step, such as "Send us a WhatsApp message to reserve a seat" or "Comment your question below".</li>
</ol>
<pre><code>Struggling to write a CV?
Here are 3 things every CV needs: your contact details, your skills, and your education.
Want a free CV check? Send us a message today.</code></pre>
<h3>Honesty and care</h3>
<p>Do not exaggerate or invent claims, reviews or numbers. Write what you can prove. Check spelling and names before posting; mistakes make a business look careless. Read your text aloud once before you publish.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose one service or product and list two features.</li>
<li>Turn each feature into a benefit using "so you can...".</li>
<li>Write a post of under 60 words with a hook, a benefit and one call to action.</li>
<li>Ask someone to read it and tell you in one sentence what you want them to do.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Use short sentences, simple words and put the main point first.</li>
<li>Explain benefits, not only features.</li>
<li>Give each post one hook and one clear call to action.</li>
<li>Be honest and check your writing before posting.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Choosing the right social media platform',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will know what each popular platform is good for and be able to choose the ones that match your audience and goal. Features and popularity change, so always check where your own audience spends time.</p>
<h3>The main platforms</h3>
<table>
<thead><tr><th>Platform</th><th>Good for</th><th>Usually works best with</th></tr></thead>
<tbody>
<tr><td>Facebook</td><td>Community pages, events, groups, local businesses, reaching a wide age range</td><td>Photos, short videos, announcements, group discussion</td></tr>
<tr><td>Instagram</td><td>Visual brands such as fashion, food, crafts, travel and creative work</td><td>Photos, reels (short videos), stories</td></tr>
<tr><td>WhatsApp</td><td>Direct customer conversations, orders, support, updates to groups</td><td>Short messages, catalogue, status updates</td></tr>
<tr><td>TikTok</td><td>Reaching younger audiences with short, creative videos</td><td>Tips, trends, behind the scenes, stories</td></tr>
<tr><td>LinkedIn</td><td>Professional networking, jobs, partnerships, reaching donors and employers</td><td>Articles, achievements, project updates</td></tr>
<tr><td>X</td><td>News, quick public conversations, reaching media and institutions</td><td>Short text, links, timely comments</td></tr>
<tr><td>YouTube</td><td>Longer how-to videos and lasting content that people search for</td><td>Tutorials, talks, event recordings</td></tr>
</tbody>
</table>
<h3>How to choose</h3>
<ol>
<li>Look at your persona: where do they already spend time?</li>
<li>Look at your goal: awareness, enquiries, jobs, donations?</li>
<li>Look at what you can produce: do you have a person who can make video each week?</li>
<li>Start with two platforms and add more only when you are consistent.</li>
</ol>
<h3>Organic and paid</h3>
<p><strong>Organic</strong> reach is the number of people who see your content without you paying. <strong>Paid</strong> reach comes from adverts. Organic is free but slow and unpredictable; paid is faster but costs money. Many organisations use organic posting to build trust and a small paid budget to boost the best content.</p>
<h3>Short video</h3>
<p>Short vertical videos (reels, TikToks, YouTube Shorts) are currently one of the most effective ways to be discovered. A phone is enough. Good lighting near a window, clear sound and a strong first three seconds matter more than expensive equipment.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write the names of your two likeliest platforms.</li>
<li>For each, write one reason it suits your audience and one thing you will post there.</li>
<li>Visit three accounts similar to yours on each platform and note what posts attract most comments.</li>
<li>Decide how many posts per week you can honestly keep up.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Each platform has strengths; choose by audience, goal and your capacity.</li>
<li>Start with two platforms and do them well.</li>
<li>Organic builds trust over time; paid speeds results.</li>
<li>Short video is powerful and can be made with a phone.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Community management and ethical marketing',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to reply to your audience professionally, handle criticism calmly and market honestly. Your reputation is built by how you behave in public, not only by what you post.</p>
<h3>What community management is</h3>
<p><strong>Community management</strong> means looking after the people who follow, comment and message you. It includes answering questions, thanking people, removing abusive content and listening to feedback.</p>
<h3>Good habits</h3>
<ul>
<li><strong>Reply quickly,</strong> ideally within a day. Say when you will answer if you need time.</li>
<li><strong>Use the person's name</strong> and a friendly tone.</li>
<li><strong>Move private matters</strong> (prices for a special order, personal details) to a private message.</li>
<li><strong>Thank people</strong> for praise and share good reviews with permission.</li>
<li><strong>Keep a list of common answers</strong> so you reply accurately every time.</li>
</ul>
<h3>Handling criticism</h3>
<ol>
<li>Pause. Do not reply while you are angry.</li>
<li>Thank the person and acknowledge the problem: "Thank you for telling us. We are sorry the delivery was late."</li>
<li>Offer a solution or a next step, and move the details to a private message.</li>
<li>Follow up afterwards.</li>
<li>Never delete honest criticism. Remove only abuse, hate speech or spam.</li>
</ol>
<h3>Ethical marketing</h3>
<ul>
<li><strong>Be truthful.</strong> No fake reviews, fake followers or invented testimonials.</li>
<li><strong>Be clear about adverts.</strong> If you are paid or given something to promote a product, say so.</li>
<li><strong>Respect people.</strong> Do not shame, mock or use fear to sell. Take special care with children and vulnerable people.</li>
<li><strong>Ask permission</strong> before posting photos of people, and never share private information.</li>
<li><strong>Respect copyright.</strong> Use your own photos, or those you have a right to use.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Imagine this comment on your page: "I ordered two weeks ago and nothing came. Terrible."</li>
<li>Write a public reply of two or three sentences using the steps above.</li>
<li>Write the private message you would send next.</li>
<li>List three rules for your page about what you will and will not post.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Community management means replying, thanking and listening.</li>
<li>Answer criticism calmly, offer a solution and take details to a private message.</li>
<li>Honesty, consent and respect for copyright are the basis of ethical marketing.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Why make a content calendar?
- To post as many times as possible each day
* To stay consistent, plan ahead and save time
- To copy competitors
- Because social media demands it
= A calendar helps you post regularly without daily stress.

Q: Which post mix is best for most pages?
- Only direct sales messages
- Only jokes
* A mix of educating, inspiring, connecting and promoting
- Only photos of the owner
= People tire of constant selling. A mix keeps the audience interested.

Q: "Evening classes from 6 to 8 pm" is a feature. Which is the matching benefit?
- Our classes are the best
- Classes happen in the evening
- Call us
* You can study without leaving your daytime job
= A benefit explains what the feature does for the person.

Q: What is a call to action?
- The title of the page
* One clear next step you ask the reader to take
- A type of image
- The number of likes
= A call to action tells people what to do next, such as send a message or register.

Q: Which platform is usually the most useful for professional networking and finding jobs?
- TikTok
- WhatsApp status
* LinkedIn
- Snapchat filters
= LinkedIn is built for professional profiles, jobs and partnerships.

Q: A customer posts an angry public comment about a late delivery. What should you do first?
- Delete it
- Argue publicly
- Ignore it
* Pause, thank them, apologise for the problem and offer to solve it privately
= Honest criticism handled well shows other readers that you care.

Q: Which TWO actions are unethical? (choose all that apply)
* Writing fake customer reviews
- Asking permission before posting a customer's photo
* Hiding that a post is a paid promotion
- Replying kindly to a question
= Fake reviews and hidden paid promotion mislead people and damage trust.

Q: What is organic reach?
- People reached through paid adverts
- People who block your page
* People who see your content without you paying for it
- The size of your email list
= Organic reach is free but slower and less predictable than paid.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 4
        [
            'title'   => 'Module 4: Search, email and online ads',
            'summary' => 'How search works, SEO basics, email marketing with consent, paid adverts and WhatsApp Business.',
            'lessons' => [
                [
                    'title'   => 'How search engines work and SEO basics',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will understand in simple terms how Google finds pages, and you will be able to improve a page or website so that more of the right people find it. This is called search engine optimisation, or SEO.</p>
<h3>How search works</h3>
<p>A search engine does three jobs. It <strong>crawls</strong> (visits pages on the web by following links), it <strong>indexes</strong> (stores what it finds), and it <strong>ranks</strong> (puts the most useful pages first when someone searches). It tries to guess what the person really wants, so helpful and trustworthy pages usually do best.</p>
<h3>Keywords</h3>
<p>A <strong>keyword</strong> is a word or phrase people type into a search box. Think like your customer: they might write "cheap laptops Gulu" or "how to write a CV". To find keywords, list the questions your audience asks, use the search suggestions, and keep a list of the phrases that appear again and again.</p>
<h3>On-page basics</h3>
<ul>
<li><strong>Page title:</strong> a clear title of about 50 to 60 characters that includes the main phrase and describes the page honestly.</li>
<li><strong>Meta description:</strong> one or two sentences that appear under the title in results. Write them to make people want to click.</li>
<li><strong>Headings:</strong> use headings to divide the text into logical parts.</li>
<li><strong>Useful content:</strong> answer the question fully, in plain language. Do not stuff the page with repeated keywords.</li>
<li><strong>Image descriptions (alt text):</strong> describe each image in a few words. This also helps people who use screen readers.</li>
<li><strong>Links:</strong> link to other helpful pages on your site and, when useful, to trusted sources. Links from respected sites to yours are a sign of trust.</li>
</ul>
<h3>Mobile and speed</h3>
<p>Most people in Uganda browse on a phone, often on slow data. A site that is mobile-friendly and loads quickly keeps visitors and is favoured by search engines. Compress large images before uploading and avoid unnecessary heavy content.</p>
<h3>Trust</h3>
<p>Show who you are: a real address, phone number and about page, and correct information that matches everywhere you appear online.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose one page of a website, or imagine one for a business you know.</li>
<li>Write the main search phrase a customer would use.</li>
<li>Write a title under 60 characters with that phrase, and a meta description of about 150 characters.</li>
<li>Write alt text for two images.</li>
<li>Search the phrase on Google and note which results appear first and why they might be useful to the reader.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Search engines crawl, index and rank pages.</li>
<li>Choose keywords by thinking like your customer.</li>
<li>Good titles, descriptions, headings, alt text, links and helpful content improve your chances.</li>
<li>Mobile-friendly, fast and trustworthy pages perform better.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Local SEO and your Google Business Profile',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to make a local business or office easy to find on Google Search and Google Maps. Many searches are local, such as "pharmacy near me", so this is one of the cheapest and most effective steps for a small organisation.</p>
<h3>What is local SEO?</h3>
<p><strong>Local SEO</strong> means helping your business appear when people search for something in a particular place. The main tool is a <strong>Google Business Profile</strong>, a free listing that can show your name, address, opening hours, phone number, photos, reviews and a link to your website.</p>
<h3>Setting up your profile</h3>
<ol>
<li>Sign in with a Google account that you will keep long-term and share access with a trusted colleague.</li>
<li>Search for Google Business Profile and choose to add or claim your business.</li>
<li>Enter the exact name of the business as people know it.</li>
<li>Choose a category that best describes what you do, for example "computer training school".</li>
<li>Add your location, and the area you serve if you travel to customers.</li>
<li>Add a phone number and a website, if you have one.</li>
<li>Complete the verification that Google asks for. This proves the business is real and yours.</li>
<li>Add opening hours, a short description, services, and good clear photos.</li>
</ol>
<p>The steps and wording may change over time; follow the instructions on screen.</p>
<h3>Keep it strong</h3>
<ul>
<li><strong>Be consistent.</strong> Use the same name, address and phone number everywhere online.</li>
<li><strong>Update</strong> hours for holidays and changes.</li>
<li><strong>Add new photos</strong> regularly.</li>
<li><strong>Ask happy customers for reviews</strong> by sharing your profile link. Never pay for reviews or write fake ones.</li>
<li><strong>Reply to every review,</strong> kindly, including the poor ones.</li>
</ul>
<h3>Directions for places without street names</h3>
<p>In many areas addresses are given by landmarks. Write a clear description such as "Opposite the main market, second floor" and pin the location on the map accurately so visitors can find you.</p>
<h3>Try it yourself</h3>
<ol>
<li>Search for a local business in your town on Google Maps.</li>
<li>List what its profile shows: hours, photos, reviews, phone number.</li>
<li>Write what is missing or unclear.</li>
<li>If you run a business or organisation, begin your own profile, or write a checklist of the information you need to prepare.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Local SEO helps people near you find you on Search and Maps.</li>
<li>A Google Business Profile is free and needs honest, complete information.</li>
<li>Consistent details, fresh photos and genuine reviews improve trust.</li>
<li>Never buy or invent reviews.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Email marketing and WhatsApp Business',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a list of people who want to hear from you, write a clear newsletter, and use WhatsApp Business to talk with customers professionally. These channels are personal, so respect matters more than anything.</p>
<h3>Email marketing</h3>
<p>Email lets you reach people directly with news, offers and stories. Each person on your list is someone who has agreed to hear from you.</p>
<h3>Building a list with consent</h3>
<ul>
<li>Ask people to join through a sign-up form, a paper form at events or a link on your page.</li>
<li>Say clearly what they will receive and how often.</li>
<li>Collect only what you need, usually a name and an email address.</li>
<li>Give an easy way to unsubscribe in every message.</li>
<li>Never buy lists and never add people without their permission.</li>
</ul>
<h3>Writing a good email</h3>
<ol>
<li><strong>Subject line:</strong> short, honest and interesting, around six to nine words. Do not mislead.</li>
<li><strong>Greeting:</strong> use the person's name if you have it.</li>
<li><strong>One main message</strong> in a few short paragraphs.</li>
<li><strong>One clear call to action</strong>, such as a button or link.</li>
<li><strong>Sign-off</strong> from a real person, with contact details.</li>
</ol>
<p>A newsletter might include one story, one useful tip and one reminder of what is coming next. Send at a regular time so people know what to expect.</p>
<h3>WhatsApp Business</h3>
<p>WhatsApp Business is a free app for small businesses. Useful features include a business profile (address, hours, website), a catalogue to show products with prices, quick replies for common questions, away and greeting messages, and labels to organise customers.</p>
<ul>
<li>Use a profile photo such as your logo and a clear business description.</li>
<li>Create quick replies for frequent questions like prices and location.</li>
<li>Do not add people to groups or broadcast lists without their permission.</li>
<li>Reply politely and avoid sending too many messages.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Write a sign-up sentence that explains what people will receive and how often.</li>
<li>Write three possible subject lines for a newsletter and choose the clearest.</li>
<li>Draft a four-paragraph newsletter with one story, one tip and one call to action.</li>
<li>Write three quick replies you would save in WhatsApp Business.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Build your email list with clear consent and an easy way to unsubscribe.</li>
<li>Use honest subject lines, one main message and one call to action.</li>
<li>WhatsApp Business helps with a profile, catalogue and quick replies.</li>
<li>Never send messages to people who have not agreed to receive them.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Online advertising basics',
                    'minutes' => 17,
                    'body'    => <<<'HTML'
<p>After this lesson you will understand how paid online adverts work and be able to plan a small, safe test campaign. Paid adverts can reach many people quickly, but without a plan money is easily wasted.</p>
<h3>Main places to advertise</h3>
<ul>
<li><strong>Meta ads</strong> (Facebook and Instagram): show adverts to chosen groups of people while they browse feeds, stories and reels.</li>
<li><strong>Google Ads:</strong> show text or image adverts when people search for particular words, or on websites and YouTube.</li>
<li>Other platforms such as TikTok and LinkedIn also have advertising tools.</li>
</ul>
<h3>Key ideas</h3>
<table>
<thead><tr><th>Idea</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>Objective</td><td>What you want the advert to achieve, such as messages, website visits or video views</td></tr>
<tr><td>Audience (targeting)</td><td>Who sees the advert: location, age, interests, language</td></tr>
<tr><td>Budget</td><td>How much you will spend per day or in total</td></tr>
<tr><td>Creative</td><td>The image, video and words of the advert</td></tr>
<tr><td>Impressions and reach</td><td>How many times it was shown and how many different people saw it</td></tr>
<tr><td>Click-through rate (CTR)</td><td>Clicks divided by impressions, as a percentage</td></tr>
<tr><td>Cost per result</td><td>How much you paid for each message, click or sign-up</td></tr>
</tbody>
</table>
<h3>Setting up a small campaign</h3>
<ol>
<li>Choose one objective that matches your SMART goal.</li>
<li>Define the audience from your persona. Start with a clear location and age range.</li>
<li>Set a small daily budget you can afford to lose, for example the price of a lunch, and a short run of five to seven days.</li>
<li>Create a clear image or short video, a headline, a short message and a call to action.</li>
<li>Send people to a place that works well on a phone, such as WhatsApp, a form or a simple page.</li>
<li>Publish, and check results daily without changing everything every hour.</li>
</ol>
<h3>A/B testing</h3>
<p>An <strong>A/B test</strong> compares two versions that differ in only one thing, for example two images. Run both with the same budget and see which gets the cheaper result. Change one thing at a time so you know what made the difference.</p>
<h3>Advertising rules</h3>
<p>Platforms have policies. Do not make false claims, misuse photos of people, or target vulnerable people unfairly. Adverts that break rules may be rejected or your account may be blocked.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write a one-line objective, the audience and a tiny budget for an advert for a real or imagined local event.</li>
<li>Draft two versions of the headline for an A/B test.</li>
<li>Write the call to action and where it sends people.</li>
<li>Decide the figure that would make you say the test was a success.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>An ad campaign has an objective, audience, budget and creative.</li>
<li>Start small, test one change at a time and measure cost per result.</li>
<li>Send people to a simple, phone-friendly next step.</li>
<li>Follow platform rules and advertise honestly.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which list gives the three jobs of a search engine in the correct order?
* Crawl, index, rank
- Rank, crawl, delete
- Pay, post, share
- Index, advertise, crawl
= Search engines visit pages (crawl), store them (index), then order them by usefulness (rank).

Q: Which is the best page title for a computer training page in Gulu?
- Home
- Welcome to our wonderful and amazing website for everyone
* Computer Training in Gulu: Evening Classes for Beginners
- Click here
= A good title is short, honest and contains the phrase people would search.

Q: What is the purpose of alt text on an image?
- It makes the image bigger
* It describes the image for search engines and screen-reader users
- It hides the image
- It increases your followers
= Alt text describes an image in words and improves accessibility.

Q: Which is the free listing that helps a business show up on Google Maps?
- Google Docs
- Google Calendar
- Gmail signature
* Google Business Profile
= A Google Business Profile shows your details and reviews on Search and Maps.

Q: Which TWO are correct ways to build an email list? (choose all that apply)
* A sign-up form that explains what people will receive
- Buying a list of addresses
* An unsubscribe link in every email
- Adding all your phone contacts without asking
= Email lists must be built with consent and let people leave easily.

Q: What is an A/B test in advertising?
- Using two different budgets for two years
- Asking two friends for advice
* Comparing two versions that differ in one thing to see which performs better
- Running the same advert twice
= Change one thing at a time so you can learn what made the difference.

Q: Your advert got 50 clicks and cost 5,000 UGX. What is the cost per click?
- 50 UGX
* 100 UGX
- 250 UGX
- 1,000 UGX
= 5,000 divided by 50 equals 100 UGX per click.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 5
        [
            'title'   => 'Module 5: Measure and improve',
            'summary' => 'Analytics, conversion and return on investment, reporting, data protection, and a 30-day campaign plan.',
            'lessons' => [
                [
                    'title'   => 'Analytics: reading the numbers',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to find the main numbers on your social pages and website, explain what they mean, and decide what to change. Numbers are useful only when they lead to a decision.</p>
<h3>Where to find data</h3>
<ul>
<li><strong>Platform insights.</strong> Facebook and Instagram pages show reach, engagement, followers and the best-performing posts. Look for an Insights or Professional dashboard in your account.</li>
<li><strong>Google Analytics.</strong> A free tool that, once added to a website, shows how many people visit, where they come from, which pages they view and what they do.</li>
<li><strong>Search Console.</strong> A free Google tool that shows which searches bring people to your site.</li>
<li><strong>Your own records.</strong> A notebook of orders, a column in a spreadsheet for "How did you hear about us?" is valuable data too.</li>
</ul>
<h3>Common terms</h3>
<table>
<thead><tr><th>Term</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>Reach</td><td>How many different people saw your content</td></tr>
<tr><td>Impressions</td><td>How many times it was shown, including repeats</td></tr>
<tr><td>Engagement</td><td>Likes, comments, shares, saves and clicks</td></tr>
<tr><td>Traffic</td><td>Visits to your website</td></tr>
<tr><td>Traffic source</td><td>Where visitors came from: search, social, email, direct</td></tr>
<tr><td>Bounce or engagement rate</td><td>Whether visitors stayed and interacted or left quickly</td></tr>
</tbody>
</table>
<h3>How to read a report</h3>
<ol>
<li>Choose a time period, such as the last 28 days.</li>
<li>Compare it with the previous period to see the direction.</li>
<li>Find the best three and worst three posts or pages.</li>
<li>Ask why. Was it the topic, the picture, the time of day?</li>
<li>Decide one thing to do more of and one to stop or change.</li>
</ol>
<h3>Cautions</h3>
<p>Do not draw big conclusions from very small numbers. Remember that correlation is not proof: a post may have done well simply because it was posted before a holiday. Look for patterns over several weeks.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open the insights of a page you manage, or look at a sample dashboard in the help centre of a platform.</li>
<li>Write down the numbers for reach, engagement and followers for the last 28 days.</li>
<li>Note the best post and write two possible reasons it performed well.</li>
<li>Write one change you will test next week.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Use platform insights, Google Analytics and your own records.</li>
<li>Know the difference between reach, impressions and engagement.</li>
<li>Compare periods, find patterns and decide what to change.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Conversion, return on investment and reporting',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to calculate simple measures of success and present them clearly to a manager, a board or donors. Reporting honestly helps you earn trust and budget for the next stage.</p>
<h3>Conversion and conversion rate</h3>
<p>A <strong>conversion</strong> is when a person does the action you wanted, such as ordering, registering, donating or sending an enquiry. The <strong>conversion rate</strong> is the share of people who did so.</p>
<pre><code>Conversion rate = (number of conversions / number of visitors) x 100</code></pre>
<p>Example: 400 people visit your registration page and 20 register. The conversion rate is 20 divided by 400 times 100, which is 5 percent.</p>
<h3>Cost per result</h3>
<pre><code>Cost per result = money spent / number of results</code></pre>
<p>If you spend 60,000 UGX on adverts and get 30 enquiries, each enquiry cost 2,000 UGX.</p>
<h3>Return on investment (ROI)</h3>
<p><strong>ROI</strong> shows whether the money you spent earned more than it cost.</p>
<pre><code>ROI = ((gain - cost) / cost) x 100</code></pre>
<p>If you spent 60,000 UGX and the advert brought in sales worth 180,000 UGX, ROI = (180,000 - 60,000) / 60,000 x 100 = 200 percent. For an NGO whose gain is not money, compare the cost per person reached, trained or helped from one campaign to the next.</p>
<h3>Writing a simple report</h3>
<ol>
<li><strong>Goal:</strong> remind readers what you aimed to do.</li>
<li><strong>Results:</strong> show your three to five KPIs against the targets. Use a small table or chart.</li>
<li><strong>What worked and what did not:</strong> be honest.</li>
<li><strong>What we learned.</strong></li>
<li><strong>Next steps and the support needed:</strong> time, money or decisions.</li>
</ol>
<p>Keep it to one or two pages, use plain language, and put the most important result first. Show actual numbers and explain them rather than hiding weak results.</p>
<h3>Try it yourself</h3>
<ol>
<li>Imagine a campaign: spend 90,000 UGX, 3,000 people visit a page, 120 people register.</li>
<li>Calculate the conversion rate and the cost per registration.</li>
<li>Write a three-line summary a manager could read in one minute.</li>
<li>Add one recommendation for the next month.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Conversion rate is conversions divided by visitors, as a percentage.</li>
<li>Cost per result is money spent divided by results.</li>
<li>ROI compares the gain with the cost.</li>
<li>A good report states the goal, results, lessons and next steps honestly.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Data protection and your 30-day campaign plan',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will know the basic duties you have when you collect people's information, and you will be able to put together a 30-day campaign plan, which is also the outline for your final project.</p>
<h3>Data protection and consent</h3>
<p>Whenever you collect names, phone numbers, email addresses, photos or any information about people, you handle <strong>personal data</strong>. In Uganda this is governed by the Data Protection and Privacy Act, 2019, and overseen by the Personal Data Protection Office. In general terms, the law expects you to follow these principles. This is not legal advice; for your own organisation, read the official guidance or ask a qualified adviser.</p>
<ul>
<li><strong>Have a good reason and be open.</strong> Tell people what you collect and why.</li>
<li><strong>Get consent</strong> where it is needed, and make it easy to withdraw.</li>
<li><strong>Collect only what you need.</strong></li>
<li><strong>Keep it accurate and secure.</strong> Use passwords, protect phones and devices, and share access only with those who need it.</li>
<li><strong>Do not keep it longer than necessary,</strong> and do not use it for unrelated purposes.</li>
<li><strong>Take extra care with children</strong> and sensitive information.</li>
</ul>
<h3>Practical habits</h3>
<ul>
<li>Add a short privacy note to forms and your website.</li>
<li>Ask for permission before posting a person's photo or story.</li>
<li>Do not share customer lists with other people.</li>
<li>Delete data you no longer need.</li>
</ul>
<h3>Your 30-day campaign plan</h3>
<p>Bring everything together on a few pages:</p>
<ol>
<li><strong>Goal and KPIs</strong> (one SMART goal, three KPIs).</li>
<li><strong>Audience</strong> (persona) and <strong>message</strong> (value proposition).</li>
<li><strong>Channels</strong> and posting rhythm.</li>
<li><strong>Content calendar</strong> for four weeks: week 1 awareness, week 2 interest, week 3 proof and offer, week 4 action and thanks.</li>
<li><strong>Budget</strong> in money and hours, with a small paid test if affordable.</li>
<li><strong>Consent and safety checks</strong> for photos, data and replies.</li>
<li><strong>Measurement:</strong> when you will check numbers, and who will write the final report.</li>
</ol>
<h3>Final project outline</h3>
<p>Choose a real or imagined organisation and prepare: the persona, the value proposition, the 30-day calendar, two sample posts, one email or WhatsApp message, a small ad idea with an A/B test, and a one-page reporting template.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write a title page with the organisation name and your SMART goal.</li>
<li>Fill a four-week calendar with at least 12 post ideas.</li>
<li>Add your consent checklist (photos, forms, messages).</li>
<li>Show the plan to a friend and ask what is unclear.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Handle personal data openly, with consent, only as needed and securely.</li>
<li>A 30-day plan combines goal, audience, message, channels, calendar, budget, safety and measurement.</li>
<li>Review results at the end and use them to plan the next month.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the difference between reach and impressions?
- There is no difference
* Reach counts different people; impressions count every time content was shown
- Reach counts money; impressions count followers
- Impressions are only for emails
= One person may see a post several times: reach counts them once, impressions count each view.

Q: 500 people visit a page and 25 register. What is the conversion rate?
- 2 percent
* 5 percent
- 20 percent
- 25 percent
= 25 divided by 500 is 0.05, which is 5 percent.

Q: You spend 40,000 UGX and receive 20 enquiries. What is the cost per enquiry?
- 200 UGX
- 800 UGX
* 2,000 UGX
- 20,000 UGX
= 40,000 divided by 20 equals 2,000 UGX.

Q: What should a good report to a manager contain?
- Only the best numbers
* Goal, results against targets, lessons and next steps, stated honestly
- A long list of every post
- Only screenshots
= A short honest report with next steps helps decision-making.

Q: Which TWO are good data protection habits? (choose all that apply)
* Ask permission before posting a person's photo
- Share your customer list with friends
* Collect only the information you need
- Keep all data forever
= Collect what you need, protect it, and use it only for the purpose people agreed to.

Q: Which law governs personal data in Uganda?
- The Highway Code
- The Insurance Act
* The Data Protection and Privacy Act, 2019
- The Library Rules
= The Data Protection and Privacy Act, 2019, with the Personal Data Protection Office, covers personal data.

Q: In a 30-day plan, what is the best use of week 4?
- Stop posting
* Focus on action and thanks, then review the results
- Start a new unrelated project
- Delete the page
= Week 4 invites people to act, thanks them and prepares the review.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which best describes digital marketing?
- Using only television and radio
* Promoting products or causes through online channels such as search, social media, email and messaging
- Making computers
- Selling phones only
= It uses digital channels and often allows measurement.

Q: A salon owner has little time. What is the best channel strategy?
- Open accounts on every platform
- Use none
* Choose two or three channels where the customers are, and keep them active
- Copy a competitor's channels exactly
= Consistent use of a few suitable channels beats being everywhere.

Q: Which are the main digital channels? (choose all that apply)
* Search
* Email
- Printed notice boards only
* Messaging apps
= Search, email, social, content, ads and messaging are digital channels.

Q: Someone reads your reviews and compares prices with other sellers. Which step is this?
- Awareness
* Consideration
- Loyalty
- Interest
= Consideration is the stage when people compare options.

Q: Which is the best match of message to the awareness step?
- Detailed price list
* A short, eye-catching video or helpful tip
- A form to complete
- A thank-you note
= At the awareness stage people need a reason to notice you.

Q: Which is a SMART goal?
- Improve our online presence
* Reach 2,000 people and receive 25 registrations for the October workshop by 30 September
- Be the best
- Post often
= It is specific, measurable, achievable, relevant and time-bound.

Q: Which is a vanity metric when used alone?
- Number of orders
- Number of enquiries
* Total likes on old posts
- Donations received
= Total likes may look good but may not lead to real results.

Q: What should a buyer persona be based on?
- Stereotypes
- The owner's imagination only
* Real information from conversations, messages and observation
- A single customer's name
= Personas are working sketches built from real information.

Q: Which free tool helps you see how interest in a topic changes over time?
* Google Trends
- Notepad
- Calculator
- Paint
= Google Trends displays search interest over time and by region.

Q: What is the aim of studying competitors?
- To copy their posts
* To learn and find a gap you can fill
- To criticise them online
- To get their customers by tricking them
= Learning and differentiation are ethical and effective.

Q: Which sentence is a value proposition?
- We have been open for years
- Please follow our page
* We help farmers in Gulu sell fresh vegetables directly to families by delivering twice a week
- Best prices
= It states who is helped, the result and how.

Q: What is a call to action?
- The date a post was made
- The colour of a button
- The name of a campaign
* A clear instruction about the next step you want people to take
= A call to action guides the reader to act.

Q: What is the best way to use a content calendar?
- Post when you remember
- Post everything on the same day
* Plan, prepare posts in batches and check results
- Copy last year's posts
= A calendar supports consistency.

Q: Which is a benefit rather than a feature?
- Classes are on Saturday
- The room has 20 computers
* You can learn on weekends and still work during the week
- The course lasts six weeks
= A benefit describes the result for the person.

Q: A customer leaves an angry public comment. What is the best first response?
- Delete it and block the person
* Respond calmly, thank them, apologise if appropriate and take details to a private message
- Reply with an insult
- Do nothing
= Calm, helpful replies protect your reputation.

Q: Which behaviours are unethical? (choose all that apply)
* Buying fake followers to look popular
* Posting a child's photo without permission
- Thanking customers for feedback
- Explaining prices clearly
= Dishonest tactics and lack of consent harm trust and may harm people.

Q: A search engine ranks pages. What does that mean?
- It deletes pages
- It sells pages
* It orders pages by how useful and relevant they appear for a search
- It copies pages
= Ranking is ordering results.

Q: Which two elements of a page does SEO advice commonly recommend improving? (choose all that apply)
* A clear page title
* A helpful meta description
- Repeating the keyword 100 times
- Hiding text in the page
= Titles and descriptions help both people and search engines. Keyword stuffing and hidden text are poor practice.

Q: What is the purpose of a Google Business Profile?
- To send emails
- To design logos
* To show accurate business information, photos and reviews on Search and Maps
- To block competitors
= It helps local customers find and contact you.

Q: What must an email list builder always provide?
- A purchased list
* Clear consent and an easy way to unsubscribe
- Hidden sign-up
- Fake sender names
= Consent and unsubscribe options are basic good practice.

Q: Which WhatsApp Business feature saves time answering common questions?
- Dark mode
- Voice filters
- Stickers
* Quick replies
= Quick replies let you answer common questions fast.

Q: In an advert test you compare two images with the same text and budget. What is this?
- Retargeting
* A/B testing
- SEO
- Reach
= Changing one element at a time shows which version works better.

Q: You spent 50,000 UGX on an advert and got 25 sales enquiries. What is the cost per enquiry?
- 1,000 UGX
* 2,000 UGX
- 2,500 UGX
- 5,000 UGX
= 50,000 divided by 25 is 2,000 UGX.

Q: 800 people visited a form and 40 submitted it. What is the conversion rate?
- 4 percent
* 5 percent
- 8 percent
- 40 percent
= 40 divided by 800 times 100 equals 5 percent.

Q: A campaign cost 100,000 UGX and produced 250,000 UGX in sales. What is the ROI?
- 100 percent
- 250 percent
- 50 percent
* 150 percent
= ROI = (250,000 - 100,000) / 100,000 x 100 = 150 percent.

Q: Which habits support data protection? (choose all that apply)
* Telling people why you collect their information
* Keeping devices and accounts protected with passwords
- Selling customer details to other businesses
* Deleting data you no longer need
= These habits protect people and help you comply with data protection principles.

Q: Why should a report mention what did not work?
- To embarrass the team
* Because honest lessons help you improve the next campaign
- It is required by Facebook
- To make the report longer
= Learning from weaker results is part of improving.

Q: What is a good use of week 4 in a 30-day plan?
- Delete old posts
- Stop checking numbers
* Invite action, thank people and review results
- Change the brand completely
= The last week converts attention into action and prepares the review.
QUIZ,
];
