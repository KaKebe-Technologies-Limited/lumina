<?php
return [
    'slug'        => 'personal-branding',
    'title'       => 'Personal Branding: Build a Reputation That Opens Doors',
    'category'    => 'careers',
    'level'       => 'beginner',
    'icon'        => 'star',
    'sort'        => 540,
    'summary'     => 'Learn to define who you are and what you offer, then show it consistently on your CV, LinkedIn, social media and in person, so the right clients, employers and partners find and trust you.',
    'description' => '<p>Everyone already has a personal brand: it is what people say about you when you are not in the room. This course teaches you to shape that reputation on purpose, honestly and without spending money. It is written for graduates, job seekers, freelancers, artists, small business owners and anyone who wants to be known for something good.</p><p>You will start by understanding what a personal brand is and is not, then look carefully at your strengths, values and story. You will choose an audience, write a one-line brand statement, and decide how you sound and look, using free tools such as Canva. You will then make your CV, LinkedIn profile and short bio tell the same story, and check what the internet already says about you.</p><p>The later modules cover content that builds credibility, networking online and in person, follow-up messages, handling criticism and mistakes, ethics and honesty, and how the ideas apply to job seekers, freelancers, artists and shop owners. You finish with a way to measure progress and a clear 90-day plan. Every lesson has a worked example and a practical task, so you end with real material you can use the same week.</p>',
    'outcomes'    => [
        'Explain what a personal brand is, and what it is not, in your own words',
        'Assess your strengths, values and story and turn them into clear proof',
        'Define a target audience and write a one-line brand statement',
        'Choose a consistent voice, colours, fonts and photo style using free tools',
        'Write a CV, LinkedIn profile and short bio that tell the same honest story',
        'Audit your online footprint and protect your privacy and safety',
        'Create useful posts, short articles and talks that build credibility',
        'Network politely online and in person, and follow up with confidence',
        'Handle criticism and mistakes, avoid fake claims and track progress with a 90-day plan',
    ],
    'requirements' => ['A smartphone or computer with internet access', 'No experience needed; a social media or email account is helpful for the practice tasks'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'LinkedIn Help Center', 'url' => 'https://www.linkedin.com/help/linkedin'],
        ['title' => 'Canva (free design tool)', 'url' => 'https://www.canva.com/'],
        ['title' => 'Google Fonts', 'url' => 'https://fonts.google.com/'],
        ['title' => 'Google Account: manage your privacy', 'url' => 'https://myaccount.google.com/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: Understanding your personal brand',
            'summary' => 'Learn what a personal brand really is, take stock of your strengths, values and story, and define your audience and positioning.',
            'lessons' => [
                [
                    'title'   => 'What a personal brand is, and what it is not',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what a personal brand is, clear away common myths, and say why it matters for your work and income. This matters because opportunities such as jobs, clients and invitations usually go to people who are known and trusted, and trust is built long before anyone meets you.</p>

<h3>A simple definition</h3>
<p>Your <strong>personal brand</strong> is the reputation you carry: the picture people hold in their minds about what you are good at, how you behave and what it is like to deal with you. You do not choose whether you have one. A tailor in Masaka already has a brand if customers say "she always finishes on the promised day". Personal branding simply means managing that picture on purpose so that it is accurate, clear and easy to remember.</p>

<h3>What a personal brand is not</h3>
<table>
<thead><tr><th>Myth</th><th>Reality</th></tr></thead>
<tbody>
<tr><td>It is only for celebrities and influencers</td><td>Every working person has one; it is simply either shaped or left to chance</td></tr>
<tr><td>It means pretending to be someone else</td><td>It is the honest, best-organised version of the real you</td></tr>
<tr><td>It is a logo or a nice photo</td><td>Visuals help, but behaviour and results are the core</td></tr>
<tr><td>It needs thousands of followers</td><td>A few hundred right people who trust you are worth more</td></tr>
<tr><td>It is bragging</td><td>Showing proof of your work helps others decide; hiding it helps nobody</td></tr>
</tbody>
</table>

<h3>The three parts of a brand</h3>
<ol>
<li><strong>What you do:</strong> your skills and the results you create.</li>
<li><strong>How you do it:</strong> your values, manner and quality, such as honest, punctual or creative.</li>
<li><strong>Who it is for:</strong> the people who most need what you offer.</li>
</ol>
<p>When these three are clear and consistent, people can describe you in one sentence. When they are unclear, people forget you or confuse you with someone else.</p>

<h3>Worked example</h3>
<p>Two graduates in Kampala finish the same accounting degree. Both apply to many companies. Ruth sends a plain CV and never posts anything. Daniel writes two short posts a month about simple bookkeeping tips for small shops, and his profile says "Accounting graduate helping small retailers keep clear records". When a shop owner asks a friend for a young accountant, Daniel is the name that comes up. Same degree, different brand, different outcome.</p>

<h3>Case scenario</h3>
<p>Grace, a nurse in Gulu, believed branding was "for show-offs" and refused to be visible. A colleague recommended another nurse for a training job because everyone knew that nurse's work. Grace decided to start small: she wrote a short profile about her maternal-health experience and shared one lesson a month with colleagues. Within six months a clinic manager invited her to lead a community training. The lesson: staying invisible is not modesty; it simply means people cannot recommend you.</p>

<blockquote><p><strong>Quick check:</strong> A friend says, "I do not have a personal brand because I am not famous." Is that correct?</p><p>Answer: No, because a brand is simply your reputation, and everyone who deals with other people already has one.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which matters more for a lasting brand, a stylish logo or reliable work?</p><p>Answer: Reliable work, because people trust results and behaviour; visuals only support that trust.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Ask three people who know you (a teacher or boss, a friend, a colleague) to describe you in three words each.</li>
<li>Write the nine words in a list and circle the ones that repeat.</li>
<li>Write one sentence: "Right now people think of me as ..."</li>
<li>Write a second sentence: "I would like people to think of me as ..."</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A personal brand is your reputation: what people expect from you.</li>
<li>You already have one; branding means shaping it honestly.</li>
<li>It rests on what you do, how you do it and who it is for.</li>
<li>Proof and consistency matter more than followers or fancy design.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Know yourself: strengths, values and your story',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to list your real strengths, name your core values and shape your experiences into a short, true story. This matters because a brand built on what is genuinely yours is easy to keep up, while a copied brand becomes tiring and is soon noticed as fake.</p>

<h3>Step 1: strengths with proof</h3>
<p>A strength is something you do well and can show. "Hardworking" is a claim; "I managed the school canteen accounts for two years without a missing shilling" is proof. For each strength, write the evidence next to it.</p>
<table>
<thead><tr><th>Weak claim</th><th>Strength with proof</th></tr></thead>
<tbody>
<tr><td>Good with people</td><td>Handled 40 customers a day in my aunt's shop and most became regulars</td></tr>
<tr><td>Creative</td><td>Designed posters for three school events that were printed and displayed</td></tr>
<tr><td>Fast learner</td><td>Taught myself spreadsheets in a month and now keep the church accounts</td></tr>
</tbody>
</table>

<h3>Step 2: values</h3>
<p><strong>Values</strong> are the principles that guide your choices, such as honesty, service, excellence, faith, family or learning. Pick three to five. A useful test: think of a time you felt proud and a time you felt angry or ashamed. Pride often shows a value you lived by, and anger often shows one that was broken. Your values decide what work you accept and what you refuse.</p>

<h3>Step 3: your story</h3>
<p>People remember stories better than lists. A good brand story has three short parts: <strong>where you started</strong>, <strong>the turning point</strong> and <strong>where you are heading</strong>. It must be true and it must connect to the work you want.</p>
<p><em>Example:</em> "I grew up repairing radios with my uncle in Mbarara. Studying electrical engineering showed me how much small businesses lose when equipment fails. Now I install and maintain solar systems for rural shops." Three sentences, all true, all pointing to the same career.</p>

<h3>Worked example: a freelance photographer</h3>
<p>Sarah photographs weddings in Entebbe. Her list of strengths with proof: calm with nervous couples (shot 25 weddings), delivers edited photos within seven days (kept this promise 22 times), good in low light (church and evening receptions). Her values: punctuality, respect for families, quality. Her story: she started by photographing family events with a borrowed phone, then saved for a camera. That is far more distinctive than "I am a passionate photographer".</p>

<h3>Case scenario</h3>
<p>Joel, a graduate in Mbale, wrote on his first profile that he was "a hardworking, passionate, team player". Employers skimmed past it because thousands of profiles say the same. Joel decided to replace the adjectives with facts: he organised a campus clean-up for 80 students and raised UGX 600,000 for it. After the change, a recruiter mentioned that exact project in his interview invitation. The lesson: evidence and a story make you memorable; adjectives do not.</p>

<blockquote><p><strong>Quick check:</strong> Which is stronger, "I am a good leader" or "I led a team of six to finish the school garden in three weeks"?</p><p>Answer: The second, because it names a specific result that others can check.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What three parts make a simple brand story?</p><p>Answer: Where you started, the turning point, and where you are heading, because together they show direction and growth.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write five strengths and, beside each, one piece of proof (a result, a number, a task you completed).</li>
<li>Choose three to five values and write one sentence on how you have lived each one.</li>
<li>Write your story in three sentences: start, turning point, direction.</li>
<li>Read it aloud. Delete any word you would not say to a friend.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Every strength needs proof; adjectives alone are weak.</li>
<li>Values guide the work you accept and the way you behave.</li>
<li>A short true story makes you memorable.</li>
<li>Build only on what is genuinely yours so the brand is easy to sustain.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Audience, positioning and your one-line brand statement',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe your target audience, explain how you are different from others, and write a one-line brand statement. This matters because a person who tries to speak to everyone usually reaches no one, while a clear message makes it easy for the right people to say "that is exactly who we need".</p>

<h3>Choose your audience</h3>
<p>Your <strong>audience</strong> is the group of people whose opinion affects your goals: employers, clients, customers, supporters or collaborators. Be specific. "Everyone in Uganda" is too wide. "Owners of small restaurants in Kampala who want more customers from social media" is a group you can understand and serve. To describe them, answer: Who are they? What problem keeps them worried? Where do they spend time online? What would make them trust you?</p>

<h3>Positioning</h3>
<p><strong>Positioning</strong> means the space you occupy in the audience's mind compared with others. Ask: what do I do that is different, better or more suited to this audience? The difference can be a skill mix, a place, a language, a price level, a way of working or your own experience. An agronomist who also speaks Luganda and Runyankole and visits farms in person is positioned differently from one who only sends emails in English.</p>

<h3>The brand statement formula</h3>
<pre><code>I help [who] to [get what result] by [how / what makes me different].</code></pre>
<p>Keep it to one sentence that a ten-year-old could repeat.</p>
<table>
<thead><tr><th>Before (vague)</th><th>After (clear)</th></tr></thead>
<tbody>
<tr><td>I am a passionate marketer</td><td>I help small boutiques in Kampala get more customers through simple, low-cost Instagram and WhatsApp marketing</td></tr>
<tr><td>I am an agronomist</td><td>I help smallholder maize farmers in Mbarara raise yields with practical soil testing and planting advice in their own language</td></tr>
<tr><td>I make music</td><td>I write Luganda acoustic songs about student life for young listeners who want honest, calm music</td></tr>
</tbody>
</table>

<h3>Worked example: a tailor</h3>
<p>Mariam, a tailor in Jinja, first said, "I sew all kinds of clothes". After thinking about her customers, she saw that most were working women needing smart office dresses that fit well within five days. Her statement became: "I make well-fitted office dresses for working women in Jinja, ready in five days, with free adjustments." Her WhatsApp status, signboard and word of mouth now say the same thing, and new customers arrive already knowing what to ask for.</p>

<h3>Case scenario</h3>
<p>Kevin, a freelance graphic designer, advertised "logos, posters, websites, photography, video, anything". Clients doubted he was excellent at any of it, and he competed only on price. He decided to focus on one thing: logos and simple brand kits for new restaurants. He rewrote his profile around that and showed four restaurant projects. Within three months he was turning down cheap work and charging more. The lesson: focus does not shrink your opportunity; it makes you the obvious choice for a specific one.</p>

<blockquote><p><strong>Quick check:</strong> Why is "I help everyone with anything" a weak brand statement?</p><p>Answer: It is not specific, so nobody can tell what you do best or whether you are right for them.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What are the three blanks in the statement formula?</p><p>Answer: Who you help, what result you give, and how or why you are different, because those three make the message clear.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write one paragraph describing your ideal audience: who they are, their main problem and where they spend time.</li>
<li>List three things that make you different from others doing similar work.</li>
<li>Write three versions of your brand statement using the formula.</li>
<li>Read them to two people and ask, "What do you think I do?" Keep the version they understand fastest.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Choose a specific audience whose problem you can solve.</li>
<li>Positioning is the clear difference between you and others.</li>
<li>Use the formula: I help [who] to [result] by [how].</li>
<li>Focus makes you easier to remember and recommend.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which best describes a personal brand?
- A logo and business card
* The reputation people hold about what you do and what you are like to deal with
- The number of followers you have
- A slogan you invent for advertising
= A brand is your reputation. Logos and followers are only tools or side effects.

Q: A classmate says personal branding means pretending to be someone impressive. What is the best reply?
- Yes, you should copy a successful person
- Yes, but only on social media
* No, it is presenting the honest, best-organised version of the real you
- No, because brands only matter to big companies
= A lasting brand must be true. Pretending is hard to maintain and damages trust when discovered.

Q: Which of these is a strength WITH proof?
- I am very hardworking
* I kept the youth group accounts for a year and presented a clear report at every meeting
- I am a passionate person
- I am good at many things
= Specific facts and results show your strength; adjectives alone cannot be checked.

Q: Amos wrote "team player, passionate, fast learner" on his profile and gets no replies. What should he change first?
- Add more adjectives
* Replace the adjectives with specific projects and results
- Delete the profile
- Copy another person's profile
= Recruiters see the same adjectives everywhere. Evidence and numbers make a profile stand out.

Q: Which is the best brand statement?
- I do many things for many people
- I am the best designer
* I help new restaurants in Kampala get a clear logo and menu design within two weeks
- Please hire me for anything
= It names who, the result and a timeframe, so it is specific and easy to remember.

Q: Which parts make up a simple brand story? (choose all that apply)
* Where you started
* A turning point
* Where you are heading
- A list of everyone you know
= Story parts show growth and direction. A list of contacts says nothing about you.

Q: A tailor says she "sews everything for everyone" and competes only on price. Based on this module, what is the most useful step?
* Choose a clear audience and focus, such as office dresses for working women, and say so everywhere
- Lower prices further
- Stop advertising
- Copy the biggest tailor in town
= Focus makes a business easy to recommend and lets you compete on quality and fit, not only on price.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Voice and visual identity',
            'summary' => 'Decide how you sound and look: a consistent voice, colours, fonts, photo and a simple logo or banner made with free tools.',
            'lessons' => [
                [
                    'title'   => 'Your brand voice: how you sound',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe your brand voice and write short messages that sound the same on every platform. This matters because people recognise you by how you speak and write, and a steady voice quietly builds trust.</p>

<h3>What brand voice is</h3>
<p>Your <strong>voice</strong> is your personality in words: the kind of words you choose, the length of your sentences and how formal or friendly you are. <strong>Tone</strong> is how that voice changes with the situation, for example gentler when apologising and brighter when celebrating. A good voice is simply your natural way of speaking, polished.</p>

<h3>Choose three voice words</h3>
<p>Pick three adjectives that describe how you want to sound, then write what each means in practice.</p>
<table>
<thead><tr><th>Voice word</th><th>What it means in practice</th><th>What it avoids</th></tr></thead>
<tbody>
<tr><td>Clear</td><td>Short sentences, everyday words, one idea per paragraph</td><td>Long jargon and complicated phrases</td></tr>
<tr><td>Warm</td><td>Greets people by name, says thank you, shows care</td><td>Cold, rude or commanding messages</td></tr>
<tr><td>Confident</td><td>States results plainly, gives straight advice</td><td>Boasting or constant apologies</td></tr>
</tbody>
</table>

<h3>Same message, different voices</h3>
<p>Imagine announcing a new service. Compare:</p>
<pre><code>Too stiff:   "We hereby wish to notify our esteemed clientele of a new offering."
Too casual:  "Yo guys!! new thing, come thru!!!"
On brand:    "Good news for our clients: from Monday we deliver in Kampala the same day."</code></pre>
<p>The third version is clear, warm and professional. It could be spoken aloud without embarrassment.</p>

<h3>Worked example: a young musician</h3>
<p>Peter, a young musician in Lira, chose the voice words honest, humble and playful. His posts are short and in simple English with a few Luo phrases. Instead of "my new banger is out, stream now!", he writes, "I wrote this song after my grandmother's funeral. It took three tries to finish. Listen if you want, and tell me what you feel." The message is honest and personal, and listeners reply with their own stories, which builds a loyal community.</p>

<h3>Case scenario</h3>
<p>Esther, a freelance writer, used very formal language in emails but a joking style in her public posts. A potential client was confused about who she really was and chose someone else. Esther decided to write a one-page voice guide with three voice words and five example phrases she would use and five she would avoid. She applied it to her emails, her profile and her WhatsApp business messages. A month later, a client told her, "Your messages always sound like you." The lesson: consistency across places is what makes a voice feel like a brand.</p>

<blockquote><p><strong>Quick check:</strong> What is the difference between voice and tone?</p><p>Answer: Voice is your steady personality in words; tone is how it adjusts for the situation, such as gentler when apologising.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why should your email and social media sound similar?</p><p>Answer: Because a steady voice helps people recognise you and trust that you are the same person everywhere.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose three voice words and write one line on what each means in practice.</li>
<li>Write the same announcement in three ways: too stiff, too casual and on brand.</li>
<li>List five phrases you will use often and five you will avoid.</li>
<li>Rewrite your last social media post or WhatsApp status in your chosen voice.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Voice is your personality in words; tone adjusts to the moment.</li>
<li>Three voice words with examples make writing consistent.</li>
<li>Avoid both stiff and careless extremes.</li>
<li>Sound the same everywhere to be recognised and trusted.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Colours, fonts and photos that look like you',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose a small set of colours and fonts and take a professional profile photo with a phone. This matters because people form an opinion in seconds, and a tidy, consistent look tells them you are careful and reliable before they read a word.</p>

<h3>Choose a colour palette</h3>
<p>Use a small palette: one <strong>main colour</strong>, one <strong>accent colour</strong> and two neutrals (a dark one for text and a light one for backgrounds). Colours carry feelings, though meanings vary by culture and context.</p>
<table>
<thead><tr><th>Colour</th><th>Common feeling</th><th>Fits well for</th></tr></thead>
<tbody>
<tr><td>Blue</td><td>Trust, calm, professional</td><td>Finance, health, consulting</td></tr>
<tr><td>Green</td><td>Growth, nature, health</td><td>Agriculture, environment, wellness</td></tr>
<tr><td>Orange or yellow</td><td>Energy, warmth, friendliness</td><td>Food, creative work, youth services</td></tr>
<tr><td>Black or deep grey</td><td>Elegance, seriousness</td><td>Photography, fashion, luxury</td></tr>
</tbody>
</table>
<p>Check that text is easy to read: dark text on a light background, or the reverse, never two similar shades. Free tools such as Canva show suggested palettes, and you can note each colour's code (for example #1F6F5C) so you can reuse it exactly.</p>

<h3>Choose fonts</h3>
<p>Pick at most two fonts: one for headings and one for body text. Choose clear, readable fonts; many are free at Google Fonts, and Canva includes many as well. Use the same fonts on your CV, banners and posters. Avoid decorative fonts for long text, because they are tiring to read.</p>

<h3>The profile photo</h3>
<ol>
<li>Stand facing a window so soft daylight falls on your face. Avoid a bright window behind you.</li>
<li>Choose a plain, tidy background such as a wall.</li>
<li>Wear what you would wear to meet a client, in a colour that suits your palette.</li>
<li>Ask a friend to take the photo at eye level, from chest up. Smile naturally.</li>
<li>Take many photos and choose the one where you look like the friendly professional people will meet.</li>
<li>Crop so your face fills about half the frame and keep the file reasonably sharp.</li>
</ol>

<h3>Worked example: an agronomist</h3>
<p>Dr. Hannah, an agronomist in Mbarara, chose deep green as her main colour, warm yellow as an accent, and one clean heading font with a simple body font. Her profile photo shows her in the field in a clean work shirt, face clearly visible, taken at soft morning light. Her CV, LinkedIn banner and farmer-training slides all use the same colours and fonts. Farmers and partners say her materials "look serious and are easy to read".</p>

<h3>Case scenario</h3>
<p>Brian, a recent graduate, used a blurry photo cropped from a wedding picture, with other people's shoulders still visible. He applied to 30 jobs without replies. A mentor suggested a new photo. Brian decided to spend an afternoon taking proper pictures by a window with a friend, in a clean shirt. He updated all profiles the same day and began receiving profile views and two interview invitations in a month. We cannot say the photo alone was the reason, but it removed an easy reason for employers to look away. The lesson: small details of presentation affect first impressions.</p>

<blockquote><p><strong>Quick check:</strong> How many fonts should a simple personal brand use?</p><p>Answer: No more than two, because too many fonts look messy and reduce readability.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Where should light come from for a phone profile photo?</p><p>Answer: In front of you, such as from a window facing you, because it lights your face evenly and avoids dark shadows.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a main colour, an accent and two neutrals, and write down their colour codes.</li>
<li>Pick two free fonts from Google Fonts or Canva and note their names.</li>
<li>Take at least ten photos by a window and choose the best one as your profile picture.</li>
<li>Make a small style card in Canva or a document showing your colours, fonts and photo together.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use a small palette: main colour, accent and two neutrals.</li>
<li>Use no more than two readable fonts.</li>
<li>A clear, well-lit, friendly photo makes a strong first impression.</li>
<li>Repeat the same look everywhere so people recognise you.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'A simple logo and banner with free tools',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to make a simple name-based logo and a profile banner using a free design tool, and know when you do not need a logo at all. This matters because a neat visual signature makes your pages look finished, and you can create one in an afternoon without paying a designer.</p>

<h3>Do you need a logo?</h3>
<p>Not always. Many employees, nurses and graduates do well with just their name written in a consistent font and colour. A freelancer, artist or small business owner benefits more from a simple mark. A name written well <em>is</em> a logo. Start simple and improve later when your income or audience grows.</p>

<h3>Three easy logo styles</h3>
<ul>
<li><strong>Wordmark:</strong> your name in a chosen font and colour, such as "Sarah Namatovu Photography".</li>
<li><strong>Monogram:</strong> your initials inside a circle or square, such as "SN".</li>
<li><strong>Name plus symbol:</strong> a very simple icon (a camera, leaf, needle or musical note) beside the name.</li>
</ul>

<h3>Making a logo in Canva</h3>
<ol>
<li>Open Canva (https://www.canva.com/) in your browser or the phone app and sign in with a free account.</li>
<li>Search for "Logo" and choose a blank design.</li>
<li>Add a text box with your name or initials and set your brand font and colour.</li>
<li>If you want a symbol, search the Elements library for a simple shape and recolour it with your palette. Check the licence note on any element, since some are only for paid users.</li>
<li>Keep it simple: two colours, one or two fonts, plenty of empty space.</li>
<li>Choose <strong>Share &gt; Download</strong> and pick PNG, with a transparent background if your account offers it, or otherwise a PNG on a plain background.</li>
</ol>
<p>Always zoom out to the size of a small profile picture and check it is still readable. If it is not, make it simpler.</p>

<h3>Making a profile banner</h3>
<p>A banner is the wide picture on top of a profile. Use a ready-made banner size in Canva (for example search "LinkedIn banner" or "Facebook cover"). Put your short brand statement or your best work on it, keep important text away from the edges and the area covered by your profile photo, and use only your colours and fonts. Banner sizes change over time, so check the platform's help pages for current guidance.</p>

<h3>Worked example: a tailor</h3>
<p>Mariam, the Jinja tailor, made a monogram "M" inside a deep-blue circle, with the words "Mariam Tailoring, well-fitted office wear" below it in a clean font. She used it as her WhatsApp Business picture, printed it on her labels and added it to the corner of photos of finished dresses. Customers now recognise her work immediately when her pictures are shared.</p>

<h3>Case scenario</h3>
<p>Isaac, a young musician in Kampala, paid someone UGX 150,000 for a complicated logo with five colours and thin lines. It looked messy as a tiny profile picture on music apps. He decided to simplify: he used only his stage name in bold white letters on a dark background, made in Canva in 20 minutes. The new version was clear everywhere. The lesson: simple, readable marks beat complicated ones, and expensive does not always mean effective.</p>

<blockquote><p><strong>Quick check:</strong> What is a monogram logo?</p><p>Answer: A logo made from your initials, such as "SN" in a circle, because it is short and works well at small sizes.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why test your logo at tiny size?</p><p>Answer: Because it will often appear as a small profile picture, and fine details disappear at that size.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Decide which style suits you: wordmark, monogram or name plus symbol.</li>
<li>Create a logo in Canva using your two colours and fonts.</li>
<li>Shrink it to the size of a thumbnail and check it still reads clearly.</li>
<li>Create a banner with your brand statement and download both files into a folder named "My brand kit".</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A neat, consistent name can be enough of a logo.</li>
<li>Wordmarks, monograms and simple symbols are easy to make for free.</li>
<li>Keep it simple and test at small size.</li>
<li>Use the same logo, colours and fonts on every platform.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is a brand voice?
- The volume at which you speak
* Your personality in words, kept consistent across places
- A recording of your greeting
- The language of your country
= Voice is how you sound in writing and speech: word choice, sentence length and warmth.

Q: Which announcement matches a clear, warm, professional voice?
- "We hereby wish to notify our esteemed clientele of a new offering."
- "Yo guys!! new thing, come thru!!!"
* "Good news for our clients: from Monday we deliver in Kampala the same day."
- "Product available. Buy."
= The third version is clear, friendly and professional without being stiff or careless.

Q: How many fonts are best for a simple personal brand?
- Five or six, to show variety
* One or two readable fonts
- A different font for every post
- Only decorative fonts
= Few fonts look consistent and are easier to read.

Q: Which is the best setup for a phone profile photo?
- Standing with a bright window behind you
- Using a cropped wedding photo
* Facing a window with soft daylight on your face and a tidy background
- Using a dark room with the flash on
= Light in front of you shows your face clearly and looks professional.

Q: Which are good checks for a logo? (choose all that apply)
* It stays readable at thumbnail size
- It uses as many colours as possible
* It uses a few colours and fonts
- It has very thin, tiny details
= Simple logos with few colours work at all sizes; tiny details disappear.

Q: Dorcas writes very formal emails but jokes and uses slang on her public posts. A client says he is not sure what she is like. What should she do?
- Stop posting
* Choose three voice words and apply them in both emails and posts
- Make her emails even more formal
- Change her name
= A written voice guide with examples helps her sound like the same person everywhere.

Q: Moses paid for a logo with five colours and thin lines. It is unreadable as a small profile icon. What is the best fix?
- Add more details
- Make the picture file bigger and keep the design
* Simplify to a bold name or monogram in two colours and test it at small size
- Remove his logo and use no name
= Simplicity and testing at small size make marks work on phones and apps.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: CV, LinkedIn and bio: one story everywhere',
            'summary' => 'Write a professional CV, a LinkedIn profile and a short bio that all tell the same true story.',
            'lessons' => [
                [
                    'title'   => 'A professional CV that sells your proof',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a clear one- or two-page CV that shows results, not just duties, and matches your brand statement. This matters because many employers spend less than a minute on a first look, and a clean CV with clear proof gets you into the "yes" pile.</p>

<h3>Structure of a good CV</h3>
<ol>
<li><strong>Name and contacts:</strong> your name large, a professional email address, phone number and town. A LinkedIn link if you have one.</li>
<li><strong>Profile summary:</strong> two or three lines, based on your brand statement.</li>
<li><strong>Experience or projects:</strong> newest first, with results.</li>
<li><strong>Education and training:</strong> degree, school and year, plus short courses.</li>
<li><strong>Skills:</strong> a short, honest list that matches the job.</li>
<li><strong>Referees:</strong> two people who agreed to speak for you, or "available on request".</li>
</ol>
<p>A photo is not required in many places; follow the style of the country or the job advert. Use your brand fonts and one accent colour, plenty of white space, and save it as a PDF so formatting does not change.</p>

<h3>From duties to results</h3>
<p>Weak CVs list what you were responsible for. Strong CVs show what changed because of you. Use a verb, the task and a result, with numbers where honest.</p>
<table>
<thead><tr><th>Before (duty)</th><th>After (result)</th></tr></thead>
<tbody>
<tr><td>Responsible for customer service</td><td>Served about 40 customers daily and resolved complaints, which kept regular customers coming back</td></tr>
<tr><td>Helped with social media</td><td>Grew the shop's WhatsApp contact list from 80 to 300 in four months with weekly product updates</td></tr>
<tr><td>Did data entry</td><td>Entered and checked 500 patient records a week with fewer than five corrections a month</td></tr>
</tbody>
</table>
<p>Only use numbers you can defend in an interview. If you do not have exact figures, use honest estimates and say "about".</p>

<h3>Before and after summary</h3>
<pre><code>Before: "Hardworking graduate seeking any opportunity to grow."
After:  "Accounting graduate who helps small retailers keep clear records.
         Kept the university savings group books for two years with no errors."</code></pre>

<h3>Worked example: a graduate in Kampala</h3>
<p>Ruth had a two-page CV listing every subject she had studied. Her mentor helped her cut it to one page. She moved education below a strong profile line and listed three bullet results from her internship: reconciling 60 accounts monthly, finding a UGX 800,000 recording error, and training a new intern. Her first invitation arrived within two weeks of sending the new version to ten companies.</p>

<h3>Case scenario</h3>
<p>Patrick sent the same CV to every employer, including a long list of unrelated jobs and an email address "cooldude2000". Few replied. He decided to create a professional email using his name, to keep a master CV and to adjust the top summary and skills for each job. He also saved files as "Patrick-Okello-CV.pdf" instead of "cv final final2". Replies increased. The lesson: tailoring and small professional details show respect for the reader.</p>

<blockquote><p><strong>Quick check:</strong> Why save a CV as a PDF before sending?</p><p>Answer: Because a PDF keeps the layout the same on any device, whereas a word-processor file can change.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which is stronger, "Responsible for sales" or "Sold about 30 items a week and increased repeat orders"?</p><p>Answer: The second, because it shows what you achieved, not only what the job was.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a two-line profile summary using your brand statement.</li>
<li>Choose your three best experiences or projects and write each as a verb, a task and a result.</li>
<li>Create a professional email address with your name if you do not have one.</li>
<li>Save your CV as a PDF named with your full name and ask one person to proofread it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A CV should be short, clear and easy to scan.</li>
<li>Show results, not only duties, and keep numbers honest.</li>
<li>Match the summary to your brand statement and the job.</li>
<li>Use a professional email address and send a PDF.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'A LinkedIn profile that works while you sleep',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a complete LinkedIn profile that matches your CV and helps the right people find you. This matters because recruiters, clients and partners often search LinkedIn before they contact anyone, and a good profile works for you around the clock.</p>

<h3>What LinkedIn is</h3>
<p>LinkedIn is a professional networking website and app. Think of it as an online CV that people can search, with the ability to connect, post and message. Features and menus change, so check the LinkedIn Help Center (https://www.linkedin.com/help/linkedin) when something looks different.</p>

<h3>The parts that matter most</h3>
<table>
<thead><tr><th>Section</th><th>What to do</th></tr></thead>
<tbody>
<tr><td>Profile photo</td><td>A clear, friendly head-and-shoulders photo from the previous module</td></tr>
<tr><td>Banner</td><td>Your Canva banner showing your focus or best work</td></tr>
<tr><td>Headline</td><td>The line under your name; say who you help and how, not just "Student"</td></tr>
<tr><td>About</td><td>Three short paragraphs: your story, your proof, how to work with you</td></tr>
<tr><td>Experience</td><td>Same results as your CV, written in the same wording</td></tr>
<tr><td>Skills</td><td>Choose skills for the work you want; ask people to confirm them</td></tr>
<tr><td>Contact information</td><td>A professional email, and a custom link if available</td></tr>
</tbody>
</table>

<h3>Writing a headline</h3>
<pre><code>Before: "Student at University"
After:  "Public health graduate | Helping rural clinics improve patient records | Kampala"</code></pre>
<p>The headline appears everywhere your name does, such as in search results and comments, so make it count.</p>

<h3>Writing the About section</h3>
<ol>
<li><strong>Paragraph 1:</strong> start with the person you help and their problem, or with a short story.</li>
<li><strong>Paragraph 2:</strong> give two or three proofs of your work.</li>
<li><strong>Paragraph 3:</strong> say what you are looking for and how to contact you.</li>
</ol>
<p>Write in the first person ("I"), in your brand voice, with short paragraphs.</p>

<h3>Worked example: an agronomist</h3>
<p>Hannah's headline reads "Agronomist | Helping smallholder maize farmers raise yields | Mbarara". Her About begins, "Many farmers I meet lose half their harvest to poor soil and late planting..." and continues with three results from farmer trainings and a line inviting NGOs and cooperatives to contact her. She added photos of training days to her Featured section and asked two supervisors to write recommendations.</p>

<h3>Case scenario</h3>
<p>Naomi, a nurse, joined LinkedIn but left it nearly blank, with only her name and job title. She decided to complete it over one weekend: photo, headline about maternal care, an About section with three real examples, and her training certificates. She then connected with former classmates and colleagues. A hospital recruiter found her profile by searching for the skills she listed and invited her to apply. The lesson: a complete profile makes you findable; a blank one makes you invisible.</p>

<blockquote><p><strong>Quick check:</strong> Which part of the profile appears next to your name in search results and comments?</p><p>Answer: The headline, so it should say who you help and how, not just your job title or "Student".</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Should your LinkedIn experience contradict your CV?</p><p>Answer: No, because differences look careless or dishonest; both should tell the same true story.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create or open your LinkedIn account and add your profile photo and banner.</li>
<li>Write three headline options and choose the one that says who you help.</li>
<li>Write your About section in three short paragraphs.</li>
<li>Add ten relevant skills and ask two people to write short recommendations.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>LinkedIn works like a searchable online CV.</li>
<li>The headline and About section carry your brand statement and story.</li>
<li>Keep wording and facts identical to your CV.</li>
<li>Check the LinkedIn Help Center when menus change.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Short bios and one story across every platform',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write bios of different lengths from one core story and keep your message the same on every platform. This matters because every event, website, magazine and social media page asks for a bio, and a prepared one saves time and avoids contradictions.</p>

<h3>Why you need several lengths</h3>
<p>Different places have different space. Prepare three versions from the same facts:</p>
<table>
<thead><tr><th>Length</th><th>Where it is used</th><th>What to include</th></tr></thead>
<tbody>
<tr><td>One line (under 15 words)</td><td>Instagram or X bio, email signature</td><td>Who you help and how</td></tr>
<tr><td>Short (50 to 70 words)</td><td>Event programme, website team page</td><td>Role, proof, personality, contact</td></tr>
<tr><td>Long (150 to 200 words)</td><td>About page, speaker profile, article author note</td><td>Story, results, values, what you are working on</td></tr>
</tbody>
</table>

<h3>A bio formula</h3>
<ol>
<li>Start with your name and what you do, in plain words.</li>
<li>Add one or two proofs: a result, an award or a number of people helped.</li>
<li>Add a human detail that shows your values or personality.</li>
<li>End with how people can reach you or what you are working on now.</li>
</ol>
<p>Write in the third person for programmes and websites ("Sarah is...") and the first person for social media ("I help..."). Both must carry the same facts.</p>

<h3>Before and after bios</h3>
<pre><code>Before: "I am a very passionate and hardworking person who loves photography and meeting people and I have a lot of experience."

After (one line): "Wedding photographer in Entebbe. Calm photos, delivered in 7 days."

After (short): "Sarah Namatovu is a wedding and family photographer based in Entebbe.
She has photographed more than 25 weddings and delivers edited photos within
seven days. She started with a borrowed phone and still believes the best
pictures come from relaxed families. Book through the link below."</code></pre>

<h3>Keep the story the same</h3>
<p>Make a small "brand sheet" document that holds your brand statement, three voice words, one-line, short and long bios, your colour codes and links. When you update your CV or profile, update the sheet first and copy from it. Check every platform quarterly for mismatches such as different job titles or dates.</p>

<h3>Worked example: a young musician</h3>
<p>Peter's one-line bio on music apps is "Luganda acoustic songs about student life". On his profile for a school concert, the short bio explains where he studies and his first single, and the long bio on his website tells the story of how his grandmother's songs inspired him. A listener who sees any of the three finds the same name, tone and message.</p>

<h3>Case scenario</h3>
<p>Fatuma, a small business owner who sells handmade soap, wrote "founder and CEO" on Instagram, "student" on LinkedIn and "soap seller" on Facebook. A supplier who looked her up could not tell which was true and hesitated to give credit. Fatuma decided to use one title everywhere: "Founder, Fatuma Naturals". She wrote three bios from one brand sheet and updated all pages in an hour. The supplier later approved her order. The lesson: contradictions look careless, and one clear title looks reliable.</p>

<blockquote><p><strong>Quick check:</strong> Why keep a brand sheet with your bios?</p><p>Answer: So you can copy the same facts and wording to every platform quickly and avoid contradictions.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which person is a short event bio usually written in?</p><p>Answer: The third person ("Sarah is...") because it reads like an introduction by someone else, though the facts must match your first-person bios.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a one-line bio under 15 words.</li>
<li>Write a short bio of about 60 words and a long bio of about 160 words.</li>
<li>Create a brand sheet with your statement, voice words, bios, colours and links.</li>
<li>Compare your CV, LinkedIn and social media pages and correct every mismatch.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Prepare one-line, short and long bios from the same facts.</li>
<li>Use a bio formula: what you do, proof, human detail, contact.</li>
<li>A brand sheet keeps wording and facts consistent.</li>
<li>Check all platforms regularly for mismatches.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What should the profile summary at the top of a CV be based on?
- A copy of someone else's CV
* Your brand statement and your best proof
- A long list of all your hobbies
- Your date of birth and religion
= The summary should present who you help and why you are credible in two or three lines.

Q: Which CV line is the strongest?
- Responsible for customer service
- Hardworking team member
* Served about 40 customers daily and resolved complaints, which kept regular customers coming back
- Good at everything
= It shows an action and a result. Duties and adjectives tell the employer much less.

Q: Why send a CV as a PDF?
* The layout stays the same on any phone or computer
- PDFs are always smaller than 1 KB
- Employers cannot read other files
- It hides your name
= PDF keeps your formatting fixed so it looks as you designed it.

Q: Which LinkedIn headline is best for a public health graduate in Kampala?
- Student
- Looking for work
* Public health graduate | Helping rural clinics improve patient records | Kampala
- Hello everyone
= A good headline states who you help and how, so people find you in searches.

Q: Which of these belong in a good bio? (choose all that apply)
* What you do, in plain words
* One or two proofs
- Your phone's PIN
* How people can contact you
= Bios should help readers trust and reach you. Never share private security details.

Q: Fatuma uses three different job titles on three platforms and a supplier hesitates to trust her. What is the best solution?
- Delete all her profiles
- Add a fourth title
* Choose one honest title and update every platform from a single brand sheet
- Ask the supplier to guess
= Consistency across platforms shows reliability and prevents confusion.

Q: Naomi, a nurse, has a nearly blank LinkedIn profile and no recruiter contacts. What is the most effective first step?
- Wait for recruiters to find her
- Add random connections only
* Complete her photo, headline, About section and skills with real examples
- Write "open to anything" as her headline
= A complete, specific profile appears in searches and gives recruiters reasons to contact her.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Your online footprint and credible content',
            'summary' => 'Audit what the internet says about you, protect your privacy, and create posts, articles and talks that build trust.',
            'lessons' => [
                [
                    'title'   => 'Your online footprint: audit and privacy',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to find out what the internet says about you, clean up anything harmful and set privacy so that you stay safe. This matters because employers, clients and sponsors often search for your name, and old posts or open personal details can cost you opportunities or put you at risk.</p>

<h3>What is a digital footprint?</h3>
<p>Your <strong>digital footprint</strong> is everything about you that can be found online: your posts, photos, comments, profiles, school or club websites, news items and what other people post about you. Some of it you control, such as your profiles. Some you only partly control, such as photos in which friends tag you.</p>

<h3>The five-step footprint audit</h3>
<ol>
<li><strong>Search your name</strong> in a search engine, with and without quotation marks, and add your town or school. Also search image results.</li>
<li><strong>Open a private (incognito) window</strong> so the results are closer to what a stranger sees.</li>
<li><strong>Look at each platform</strong> you use and scroll back through old posts, comments and photos.</li>
<li><strong>List what helps and what harms:</strong> helpful items support your brand; harmful ones are rude jokes, angry rants, private pictures, or old claims that are no longer true.</li>
<li><strong>Decide an action</strong> for each harmful item: delete, hide, edit, ask a friend to remove a tag, or ask a site to remove it.</li>
</ol>

<h3>Privacy settings and safety</h3>
<ul>
<li>On each app, open <strong>Settings &gt; Privacy</strong> and choose who can see your posts, friends list and tags. Menus differ between apps and change over time.</li>
<li>Do not publish your home address, ID number, bank details, passwords, or exact live location. Never share one-time codes.</li>
<li>Use a strong, unique password and turn on two-step verification where offered.</li>
<li>You can review your Google account's privacy options at https://myaccount.google.com/.</li>
<li>Separate your professional pages from personal ones if you prefer, but remember that anything public can still be found, so behave as if everything is visible.</li>
</ul>

<h3>Worked example: a graduate in Kampala</h3>
<p>Ruth searched her name and found a public photo album from a party and an angry comment she wrote about a lecturer three years ago. She changed the album to "friends only", deleted the comment, and asked a friend to remove a tag. She then wrote two new posts about her accounting internship so that the first results show her work. Positive content now fills what a recruiter sees first.</p>

<h3>Case scenario</h3>
<p>Samuel, a final-year student, was shortlisted for an internship. The company searched his name and found jokes about women and a rude post about a former employer. He lost the opportunity, and a friend later told him why. Samuel decided to audit all accounts, delete the posts and write a short note to the company that he had matured and cleaned his accounts. They did not reverse their decision, but another company hired him a few months later after seeing his improved profile. The lesson: audit before you apply, not after you are rejected.</p>

<blockquote><p><strong>Quick check:</strong> Why search your name in a private window?</p><p>Answer: It shows results closer to what a stranger sees, since it is less influenced by your own history and logins.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Is it safe to post your exact live location for all to see?</p><p>Answer: No, because strangers can use it to find or follow you; share location only with trusted people.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Search your full name, then your name plus your town or school, in a private window and note the first 15 results.</li>
<li>Check each of your social accounts and list five items to delete, hide or change.</li>
<li>Open the privacy settings of two apps and tighten who can see your posts and tags.</li>
<li>Turn on two-step verification on your email account.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Employers and clients often search your name before they contact you.</li>
<li>Audit search results, old posts and photos, then delete, hide or fix.</li>
<li>Never share private details such as ID numbers, passwords or live location.</li>
<li>Replace weak results by publishing good, relevant content.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Content that builds credibility: posts and short articles',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to plan and write useful posts and a short article that show your skills and help your audience. This matters because content is how people see your knowledge before they meet you, and helpful content makes you the person they think of first.</p>

<h3>Share value, not noise</h3>
<p>Credible content solves a small problem for your audience. People trust those who teach, explain and show honest results, not those who only shout "buy from me". A useful guide is the <strong>teach, show, share</strong> mix:</p>
<table>
<thead><tr><th>Type</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Teach</td><td>"Three signs your maize seed is too old to plant"</td></tr>
<tr><td>Show</td><td>Before and after photos of an alteration, with a short story</td></tr>
<tr><td>Share</td><td>A lesson from a mistake, or a thank you to someone who helped you</td></tr>
</tbody>
</table>

<h3>A simple post formula</h3>
<ol>
<li><strong>Hook:</strong> the first line names the problem or promise.</li>
<li><strong>Body:</strong> give two or three short, practical points.</li>
<li><strong>Call to action:</strong> invite a comment, a question or a message.</li>
</ol>
<pre><code>Hook:  "Many small shops lose money because they never count their stock."
Body:  "Try this on Saturday: 1) write all items, 2) count them,
        3) compare with your sales book, 4) note the differences."
CTA:   "Want my free stock sheet? Send me a message."</code></pre>

<h3>Writing a short article</h3>
<p>A short article of 300 to 600 words can live on LinkedIn, Medium-style sites or a school blog. Use this plan: a clear title that promises a result; a short opening that names the problem; three headings with one idea each; one real example; a closing with the key steps and how to contact you. Read it aloud once and cut anything that does not help the reader.</p>

<h3>How often?</h3>
<p>Consistency beats volume. One useful post a week for twelve weeks is better than ten posts in one day and silence afterward. Keep a simple list of 20 content ideas drawn from questions customers ask you, and write down one idea each time someone asks you something.</p>

<h3>Worked example: an agronomist</h3>
<p>Hannah posts every Wednesday. Week one: "Why your tomatoes crack after rain". Week two: a photo of a soil test with a plain explanation. Week three: a lesson from a failed trial. Within four months, cooperatives began tagging her in questions, and a seed company invited her to speak at a farmer day. She never posted advertisements, only helpful information.</p>

<h3>Case scenario</h3>
<p>Joan, a freelance bookkeeper, posted ten times a day, most posts being "Hire me!" images. Followers stopped engaging, and she got no leads. She decided to change: three posts a week, each giving one small bookkeeping tip, with one client success story a month (with permission). In two months she received six messages from shop owners asking for help. The lesson: usefulness attracts people, and constant selling pushes them away.</p>

<blockquote><p><strong>Quick check:</strong> What does the "teach, show, share" mix mean?</p><p>Answer: A balance of posts that teach tips, show real work, and share honest lessons or thanks, because variety builds trust without constant selling.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Is it better to post ten times in one day then stop, or once a week for months?</p><p>Answer: Once a week for months, because steady, consistent posting builds recognition and trust.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List 20 questions your audience asks you or would like answered.</li>
<li>Choose one and write a post using the hook, body, call to action formula.</li>
<li>Write a 300-word article from a second question and ask a friend to read it for clarity.</li>
<li>Create a weekly posting schedule for the next four weeks.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Credibility comes from helping, not from shouting.</li>
<li>Use the teach, show, share mix.</li>
<li>Posts need a hook, practical points and a call to action.</li>
<li>Consistent, regular content beats bursts of activity.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Talks, speaking and showing your work in public',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to prepare a short talk, find places to give it and show your work with confidence. This matters because standing up to speak, even for five minutes, makes people remember you far more than a hundred silent posts.</p>

<h3>Where you can speak</h3>
<ul>
<li>School, university or church clubs and youth groups.</li>
<li>Community meetings, farmer groups, women's savings groups or market associations.</li>
<li>Career days, workshops and local meet-ups.</li>
<li>Live audio, video calls or short recorded videos on social media.</li>
</ul>
<p>Start small. Offer to give a ten-minute talk for free to a group that would benefit. Your first aim is practice and proof, not payment.</p>

<h3>The five-minute talk plan</h3>
<ol>
<li><strong>Open</strong> with a question or short story (30 seconds).</li>
<li><strong>State your one message</strong> in a single sentence.</li>
<li><strong>Give three points</strong>, each with one example.</li>
<li><strong>Close</strong> with one action the audience can take today.</li>
<li><strong>Introduce yourself briefly</strong> using your short bio, and share a way to reach you.</li>
</ol>
<p>Write only keywords on small cards, not the whole speech. Practise aloud three times, timing yourself, and once in front of a friend.</p>

<h3>Managing nerves</h3>
<ul>
<li>Breathe slowly before you begin and pause between points.</li>
<li>Speak a little slower than you think you should and look at friendly faces.</li>
<li>Remember the audience wants you to succeed.</li>
<li>If you forget a point, look at your cards, smile and continue; nobody knows your plan except you.</li>
</ul>

<h3>Showing your work: a portfolio</h3>
<p>Keep a simple portfolio of your best five items: photos, documents, projects, reports or testimonials. A free Canva presentation, a PDF, or a LinkedIn Featured section can serve as one. For each item write a two-line story: the problem, what you did and the result. Always get permission before showing clients' work or faces.</p>

<h3>Worked example: a nurse</h3>
<p>Naomi offered a ten-minute talk to a local mothers' group on "Three danger signs in a newborn". She used one story, three points and a closing action. A clinic manager in the audience asked for her number and later invited her to train community health volunteers. The talk was free but opened a paid door.</p>

<h3>Case scenario</h3>
<p>David, a graduate in agriculture, was afraid of speaking. He decided to start by recording a two-minute phone video giving a farming tip, sharing it with one friend, then posting it. After five videos, he offered a short talk at his old school's career day. The students asked many questions and a teacher recommended him to a farmers' cooperative. The lesson: build the speaking muscle with small steps and you will soon be ready for bigger stages.</p>

<blockquote><p><strong>Quick check:</strong> What should the end of a short talk give the audience?</p><p>Answer: One clear action they can take today, because it turns your message into a result.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why get permission before putting a client's photo in your portfolio?</p><p>Answer: Because it respects their privacy and avoids harm or complaints; showing people without consent is unethical.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a topic you know well and write a five-minute talk plan on small cards.</li>
<li>Practise it three times aloud and time yourself.</li>
<li>Record a two-minute video of it on your phone and watch it once to spot one improvement.</li>
<li>Contact one local group and offer to give the talk, or collect your five best works into a mini portfolio.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Speaking, even briefly, makes you memorable and credible.</li>
<li>Use one message, three points and one closing action.</li>
<li>Start small and free to gain practice and proof.</li>
<li>A portfolio with short stories shows what you can do; always ask permission to use others' work.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is your digital footprint?
- Only the photos on your phone
* Everything about you that can be found online, including what others post
- The size of your internet bundle
- Your list of saved contacts
= Your footprint includes your own posts and profiles and anything others share about you.

Q: What is a sensible first step in an online footprint audit?
- Delete all accounts immediately
* Search your name in a private window and note what appears
- Post more photos
- Change your name
= Seeing what a stranger sees tells you what to keep, fix or remove.

Q: Which of these should you never post publicly? (choose all that apply)
* Your national ID number
* Your passwords or one-time codes
- A tip about your profession
* Your exact live location at all times
= Private and security details can be misused. A helpful professional tip is exactly what builds a brand.

Q: Which post follows the hook, body, call to action formula?
- "Buy my product now!!!"
- A photo with no words
* "Many shops lose money by not counting stock. Try these 3 steps on Saturday. Want my free sheet? Message me."
- A long angry complaint
= It opens with a problem, gives practical steps and ends with an invitation.

Q: Which posting pattern is best for building credibility?
- Ten posts in one day, then silence for a month
- Only advertisements
* One useful post a week, kept up for months
- Posting only when someone complains
= Consistency builds recognition and trust better than bursts.

Q: Samuel loses an internship because of old rude posts found by the company. What should he do to prepare for his next application?
- Ignore the problem
* Audit his accounts, delete or hide harmful posts, and publish positive, relevant content
- Make his accounts public so people see everything
- Use a fake name
= Fixing the footprint before applying removes easy reasons to reject him.

Q: Joan posts "Hire me!" ten times a day and gets no leads. Which change is most likely to help?
- Post even more adverts
- Stop using social media
* Share three useful bookkeeping tips a week and one client story a month with permission
- Copy another bookkeeper's posts
= Helpful content attracts people who then want to work with her.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Networking, reputation and ethics',
            'summary' => 'Meet people, introduce yourself, follow up, protect your reputation through consistency and criticism, and stay honest.',
            'lessons' => [
                [
                    'title'   => 'Networking online and in person, and your elevator introduction',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to introduce yourself in 20 seconds and start useful relationships online and at events. This matters because most opportunities come through people who know, trust and remember you, and networking is simply the habit of building those relationships kindly.</p>

<h3>What networking really is</h3>
<p><strong>Networking</strong> is not collecting phone numbers or asking strangers for jobs. It is building honest, two-way relationships: you help, you learn and you stay in touch. A good rule is to give first. Share a useful link, congratulate someone on a success, or introduce two people who could help each other.</p>

<h3>The elevator introduction</h3>
<p>An <strong>elevator introduction</strong> (or pitch) is what you can say in about 20 seconds, the length of a short lift ride. Use this pattern:</p>
<pre><code>"Hello, I'm [name]. I help [who] to [result]. For example, [one proof].
 What do you do?"</code></pre>
<table>
<thead><tr><th>Before</th><th>After</th></tr></thead>
<tbody>
<tr><td>"Um, I am looking for any job."</td><td>"I'm Ruth, an accounting graduate. I help small shops keep clear records. Last year I fixed a UGX 800,000 error in a bookkeeping file. What do you do?"</td></tr>
<tr><td>"I do photography."</td><td>"I'm Sarah, a wedding photographer in Entebbe. I deliver calm, natural photos within seven days. What kind of events do you organise?"</td></tr>
</tbody>
</table>
<p>Finish with a question. It turns your introduction into a conversation.</p>

<h3>Networking in person</h3>
<ol>
<li>Arrive early; it is easier to meet people before a room fills.</li>
<li>Smile, greet properly and give your name clearly.</li>
<li>Ask open questions: "What brought you here?" and "What are you working on?"</li>
<li>Listen more than you talk and remember one detail about each person.</li>
<li>Exchange contacts, then write a note on the back of the card or in your phone about what you discussed.</li>
</ol>

<h3>Networking online</h3>
<ul>
<li>Send a connection request with a short, personal note: why you are connecting and what you have in common.</li>
<li>Comment helpfully on people's posts instead of only "nice".</li>
<li>Respect people's time; do not send long messages or ask for a favour in your first message.</li>
<li>Join relevant groups and answer questions kindly.</li>
</ul>

<h3>Worked example: a young musician</h3>
<p>Peter went to a music workshop in Kampala with a goal: meet three people. He spoke with a radio presenter, a sound engineer and another singer. He used his introduction, asked questions, and noted each person's name and interest. Next day he messaged each with a thank-you and one useful thing: a link to a song idea for the singer, and a short clip for the presenter. A month later the sound engineer offered him cheaper studio time.</p>

<h3>Case scenario</h3>
<p>Brenda messaged 30 strangers on LinkedIn with: "Please give me a job." No one replied. She decided to change: she picked five people in the field she wanted, read their posts, commented thoughtfully and then sent notes like, "I enjoyed your post about patient records. I am a public health graduate learning about this topic. May I ask one question?" Three replied and one introduced her to a hiring manager. The lesson: personal, polite and relevant beats mass requests.</p>

<blockquote><p><strong>Quick check:</strong> What should an elevator introduction end with?</p><p>Answer: A question, because it invites the other person to speak and starts a conversation.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Is it good practice to ask a new contact for a job in your first message?</p><p>Answer: No, build a relationship and offer value first; an immediate request feels pushy.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write your 20-second introduction with a proof and a closing question.</li>
<li>Practise it aloud until it sounds natural, then say it to two people.</li>
<li>Choose five people in your field and write a personal connection note for each.</li>
<li>Plan one event, online or in person, and set a goal to meet three people.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Networking means building honest, two-way relationships.</li>
<li>Use the pattern: who I help, the result, a proof, a question.</li>
<li>Give first; listen and remember details.</li>
<li>Send personal, polite connection notes, not mass messages.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Follow-up messages and keeping relationships alive',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write short follow-up messages that people answer and keep contacts warm over months. This matters because most networking fails not at the first meeting but in the silence afterwards; a simple follow-up turns a brief chat into a lasting connection.</p>

<h3>Follow up within 48 hours</h3>
<p>Send your message within a day or two, while the person still remembers you. A good follow-up has four parts: remind them where you met, thank them or mention something specific, offer something useful, and suggest a small next step.</p>
<pre><code>Subject: Good to meet you at the career fair

Hello Mr. Okello,

Thank you for speaking with me at the career fair in Kampala on Friday.
I enjoyed hearing how your clinic keeps patient records on paper and
computer. As promised, here is a one-page checklist for tidy records.

If it helps, I would be glad to share how I organised the records at my
internship. Could I call you for ten minutes next week?

Kind regards,
Ruth Nakato
Accounting graduate | ruth@example.com</code></pre>

<h3>Different situations</h3>
<table>
<thead><tr><th>Situation</th><th>How to follow up</th></tr></thead>
<tbody>
<tr><td>Met at an event</td><td>Thank, remind, offer help, suggest a call</td></tr>
<tr><td>After an interview</td><td>Thank the panel in 3 to 4 lines, restate your interest and one strength</td></tr>
<tr><td>No reply after a week</td><td>One polite reminder; if still silent, wait or let it go</td></tr>
<tr><td>Client finished a project</td><td>Thank them, ask for a testimonial, mention you are available again</td></tr>
</tbody>
</table>

<h3>Keep relationships alive</h3>
<ul>
<li>Keep a simple list in a notebook or spreadsheet with name, where you met, one detail and the date of last contact.</li>
<li>Every month, reach out to two or three contacts without asking for anything: share an article, congratulate a promotion, or ask how a project went.</li>
<li>Reply to messages quickly and be reliable about promises.</li>
<li>Say thank you again when someone's advice helps you; tell them the result.</li>
</ul>

<h3>Messaging etiquette</h3>
<p>Use the person's title and name until they invite you to be casual. Write short messages with a clear subject. Proofread before sending. On WhatsApp, introduce yourself in the first message because the person may not have your number saved, and do not send voice notes to people you have not met before unless they invite it.</p>

<h3>Worked example: a freelance photographer</h3>
<p>After each wedding, Sarah sends the couple a thank-you message, a link to their photos and a gentle note: "If you enjoyed the experience, a short review would help me a lot." Three months later she sends a friendly message, "How are you both? Happy to take anniversary photos." Many couples recommend her to friends. The messages take ten minutes and produce a steady stream of bookings.</p>

<h3>Case scenario</h3>
<p>Michael met a hotel manager at a business fair, took her card and never contacted her. Six months later he saw a hotel job advert and sent a cold message. She barely remembered him. He decided to make a habit: a list of contacts, and a monthly reminder to message two people with something useful. A year later, when a vacancy opened, the manager herself messaged him because he had stayed in her mind. The lesson: relationships grow when you tend them before you need them.</p>

<blockquote><p><strong>Quick check:</strong> When should you send a follow-up after meeting someone?</p><p>Answer: Within about 48 hours, while they still remember you.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You wrote to a contact and got no answer after a week. What is a sensible step?</p><p>Answer: Send one short, polite reminder, because a single nudge is acceptable and repeated messages are annoying.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a follow-up message template with your own details and four parts: remind, thank, offer, next step.</li>
<li>Create a contact list with ten people and one detail about each.</li>
<li>Send a thank-you message to someone who helped you in the last year.</li>
<li>Set a monthly reminder to contact two people without asking for anything.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Follow up within 48 hours with a short, specific message.</li>
<li>Offer something useful and suggest a small next step.</li>
<li>Keep a contact list and reach out regularly, not only when you need help.</li>
<li>Be polite, brief and reliable.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Reputation, consistency and handling criticism and mistakes',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to protect your reputation through consistent behaviour, respond calmly to criticism and recover from mistakes. This matters because a brand takes years to build and a single badly handled problem can damage it, while a well-handled problem can actually increase trust.</p>

<h3>Reputation is built by small promises kept</h3>
<p>Your reputation grows from many small actions: arriving on time, replying to messages, delivering as promised, and treating people politely. Brand words on your profile mean little if behaviour does not match. Each time you keep a promise, you add to your reputation; each time you break one, you remove from it. Promise only what you can deliver, and deliver slightly early where possible.</p>

<h3>Consistency</h3>
<p>Consistency means the same quality, values and manner every time, in public and in private, online and offline. If your profile says "reliable" but clients wait weeks for replies, people believe your behaviour, not your words. Create simple routines: reply to messages within 24 hours, use a checklist before delivering work, and keep one calendar for all deadlines.</p>

<h3>Handling criticism in five steps</h3>
<ol>
<li><strong>Pause.</strong> Do not reply while angry. Wait at least an hour.</li>
<li><strong>Read again.</strong> Separate facts from feelings. Is any part true?</li>
<li><strong>Thank and acknowledge.</strong> "Thank you for telling me. I am sorry the delivery was late."</li>
<li><strong>Fix or explain calmly.</strong> Offer a solution, such as a refund, a redo or a clear next step. Do not blame.</li>
<li><strong>Learn.</strong> Change your process so it does not repeat.</li>
</ol>

<h3>Public and private responses</h3>
<table>
<thead><tr><th>Situation</th><th>Better response</th></tr></thead>
<tbody>
<tr><td>Fair public complaint</td><td>Reply briefly and politely in public, then move details to a private message</td></tr>
<tr><td>Unfair or abusive comment</td><td>Stay calm, state the facts once, then stop; use block or report if it is abuse</td></tr>
<tr><td>Constructive advice</td><td>Say thank you and apply what is useful</td></tr>
</tbody>
</table>
<p>Never delete honest negative reviews or argue with the person; readers judge you by how you respond.</p>

<h3>When you make a mistake</h3>
<p>Own it fast: say what happened, apologise sincerely, explain what you will do to fix it and by when, then do it. Do not make excuses or hide the problem. A prompt, honest apology often leaves customers trusting you more than before.</p>

<h3>Worked example: a tailor</h3>
<p>Mariam delivered a wedding dress two days late. The bride posted an angry comment. Mariam replied publicly, "I am very sorry, you were right to be upset. I have refunded 20 percent and will bring the final adjustments to your home today." She then called the bride. The bride later edited her post, thanking her for the quick solution, and recommended her to two friends.</p>

<h3>Case scenario</h3>
<p>Dennis, a freelance designer, got a harsh review saying his logo was "copied and cheap". He felt angry and wrote a long reply attacking the client. Others who read it saw him as unprofessional. Dennis later decided to remove his reply, apologise publicly, share the original sketches to show the work was his, and offer a free revision. Several readers commented in his support. He lost some trust but kept the client's respect, and he added a written revision policy. The lesson: the reply shapes the brand more than the criticism itself.</p>

<blockquote><p><strong>Quick check:</strong> What should you do before replying to angry criticism?</p><p>Answer: Pause and read it again calmly, because replies written in anger usually make the situation worse.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Is it wise to delete honest negative reviews?</p><p>Answer: No, because it looks like hiding the truth; reply politely and fix the problem instead.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List three promises you make in your work and a routine that helps you keep each.</li>
<li>Write a calm sample reply to a fair complaint using thank, acknowledge, fix.</li>
<li>Recall one mistake you made and write what you would say if you apologised properly.</li>
<li>Write a personal rule for how fast you reply to messages.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Reputation is built by small promises kept and consistent behaviour.</li>
<li>Pause before replying to criticism; stay calm and fix the issue.</li>
<li>Own mistakes quickly, apologise sincerely and solve the problem.</li>
<li>Learn from every complaint to improve your process.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Ethics: honesty and avoiding fake claims',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to promote yourself truthfully, spot claims that cross the line and protect other people's rights. This matters because a brand built on lies collapses the moment someone checks, and in a small community, news of dishonesty travels very fast.</p>

<h3>The honesty test</h3>
<p>Before you publish a claim about yourself, ask three questions: Is it true? Can I prove it? Would I be comfortable if my client, teacher or employer checked it? If the answer to any is no, change it or leave it out. Personal branding is about presenting your real strengths in the best light, not inventing them.</p>

<h3>Common fake claims</h3>
<table>
<thead><tr><th>Dishonest claim</th><th>Honest alternative</th></tr></thead>
<tbody>
<tr><td>"Certified expert" with no certificate</td><td>"Learning and practising; completed an online course in bookkeeping"</td></tr>
<tr><td>Using a photo of someone else's work as your portfolio</td><td>Show your own work, even if it is small, and label practice projects as practice</td></tr>
<tr><td>Buying followers or fake reviews</td><td>Ask real clients for honest reviews</td></tr>
<tr><td>Listing a degree you did not finish</td><td>"Studied accounting at X university, 2 years completed"</td></tr>
<tr><td>Claiming to know a famous person</td><td>Mention only real, relevant connections</td></tr>
</tbody>
</table>

<h3>Respecting others</h3>
<ul>
<li><strong>Credit your sources.</strong> If you use another person's idea, photo or text, ask permission and say who made it.</li>
<li><strong>Use only licensed images and fonts.</strong> Check licences; free to view does not always mean free to use. Sites such as Creative Commons (https://creativecommons.org/) explain common licences.</li>
<li><strong>Protect client confidentiality.</strong> Do not share private details or photos without consent.</li>
<li><strong>Do not criticise competitors falsely.</strong> Compete by being better, not by spreading rumours.</li>
<li><strong>Be careful with AI-made content.</strong> If you use AI tools to help write or create images, check the facts yourself and be open about it where it matters.</li>
</ul>

<h3>Be honest about your level</h3>
<p>It is acceptable, and even attractive, to say "I am new, but I work carefully and I have done three projects for free". Honesty about your stage builds trust and sets realistic expectations. Overpromising leads to disappointment and bad reviews.</p>

<h3>Worked example: a graduate in Kampala</h3>
<p>Alex wanted to apply for a data job but had only completed one online course. Instead of claiming "data analyst", his profile says, "Graduate learning data analysis; completed a spreadsheets course and analysed sales data for my uncle's shop." An employer valued the honesty and offered a trainee role that matched his level.</p>

<h3>Case scenario</h3>
<p>Lydia, a young designer, copied three beautiful logos from the internet into her portfolio and a client asked about them. Another designer recognised the logos and told the client. Lydia lost the project and her name was talked about in a design group. She decided to remove the false items, confess to the client, and rebuild her portfolio with her own small projects, labelling them as practice. It took a year to recover trust. The lesson: one fake item can undermine all your real ones, so never risk it.</p>

<blockquote><p><strong>Quick check:</strong> Is it acceptable to say "I am new, but I work carefully"?</p><p>Answer: Yes, because honesty about your stage builds trust and keeps expectations realistic.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Can you use any image found online in your marketing?</p><p>Answer: No, because many images are protected; use images you made or that are licensed for your use.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Read your CV and profile and underline any claim you cannot prove, then rewrite it honestly.</li>
<li>Check where your logo, photos and fonts come from, and note their licences.</li>
<li>Write a sentence describing your current level honestly and positively.</li>
<li>Ask two real clients or colleagues for honest short reviews.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Test every claim: is it true, provable and checkable?</li>
<li>Fake followers, reviews, work or certificates damage trust.</li>
<li>Credit sources, respect licences and protect client privacy.</li>
<li>Be honest about your level and grow into bigger claims.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is an elevator introduction?
- A speech lasting 30 minutes
* A clear 20-second introduction of who you are and who you help
- A request for a job
- A printed advert
= It is a short, clear self-introduction that ends by inviting conversation.

Q: How should you write to a new LinkedIn contact for the first time?
- Ask for a job immediately
- Send the same message to hundreds of people
* Send a short personal note about why you are connecting and what you have in common
- Say nothing and just click Connect forever
= Personal, polite, relevant messages are far more likely to get answers.

Q: Within what time should you ideally follow up after a meeting?
- After six months
- Never
* About 48 hours
- Exactly one year
= A prompt message reminds people who you are while they still remember you.

Q: What is the best first reaction to an angry public complaint?
- Delete it and block the person
- Reply in anger immediately
* Pause, read it again calmly, then reply politely and offer to fix the problem
- Ignore it forever
= Calm, polite replies show professionalism and often win back trust.

Q: Which are honest ways to build social proof? (choose all that apply)
* Ask real clients for honest reviews
- Buy fake followers
* Show your own work, even small projects
- Copy another person's portfolio
= Real reviews and real work build lasting credibility; fakes are discovered.

Q: Mariam delivered a dress late and the bride posted a complaint. What is the best response?
- Argue that the bride was unreasonable
* Apologise publicly, offer a fix such as a partial refund and quick delivery, then follow up privately
- Delete the comment and stop speaking to her
- Post negative things about the bride
= A quick, sincere apology with a real solution can turn complaints into recommendations.

Q: Lydia added three logos she copied to her portfolio and a client discovers it. What should she do?
- Deny everything
* Remove the false items, take responsibility and rebuild a portfolio of her own labelled work
- Add more copied logos
- Change her name only
= Honesty and corrective action are the only way to rebuild trust after a fake claim.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 6
        [
            'title'   => 'Module 6: Putting your brand to work',
            'summary' => 'Apply personal branding as a job seeker, freelancer, artist or small business owner, then measure progress and follow a 90-day plan.',
            'lessons' => [
                [
                    'title'   => 'Personal branding for graduates and job seekers',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to present yourself as a strong candidate when you have little experience, and plan a branded job search. This matters because every year many graduates compete for the same few advertised jobs, and those who show clear proof and a clear direction are chosen more often.</p>

<h3>Think like an employer</h3>
<p>An employer asks: Can this person do the job? Will they fit in? Can I trust them? Your brand should answer these three questions quickly. With little work experience, you show ability through projects, volunteering, internships, class work and short courses. Show fit through your values and manner. Show trust through consistent, honest information and good references.</p>

<h3>Build a proof portfolio</h3>
<ul>
<li>A project from your studies, described in three lines: the problem, what you did, the result.</li>
<li>Volunteer work, such as managing a club's records or running a school clean-up.</li>
<li>Short certified courses that match the job you want.</li>
<li>Letters or messages from supervisors and lecturers confirming your work (with their permission).</li>
</ul>

<h3>Target, then tailor</h3>
<ol>
<li>Choose one or two job types, not twenty.</li>
<li>Read five job adverts and list the skills that repeat.</li>
<li>Match your proof to those skills on your CV and LinkedIn.</li>
<li>Write a short cover letter for each application that says why that organisation, using their name and one specific detail.</li>
<li>Use your network: tell friends, lecturers and former supervisors exactly what job you want.</li>
</ol>

<h3>Before and after: graduate headline</h3>
<pre><code>Before: "Fresh graduate seeking opportunities"
After:  "Business administration graduate | Office coordination and customer service |
         Organised a 120-person university career fair"</code></pre>

<h3>Interview as brand delivery</h3>
<p>Prepare three short stories using the structure situation, action, result. Practise your elevator introduction. Dress to match the workplace, arrive early and bring a notebook. Afterwards send a short thank-you message. Everything you do, from the email you send to the way you greet the receptionist, is part of your brand.</p>

<h3>Worked example: a graduate in Kampala</h3>
<p>Ruth wanted accounting jobs in small and medium businesses. She identified skills in adverts (reconciliation, spreadsheets, customer records) and wrote three projects that matched. She sent ten tailored applications, told five contacts exactly what role she wanted, and posted a short tip every week. She received three interviews within two months and an offer after the second.</p>

<h3>Case scenario</h3>
<p>Sharon sent 80 identical applications for "any job" and received only two replies. Her cousin advised her to focus. She decided to target customer-service roles at banks and telecom companies, rewrote her CV with customer-handling examples from her aunt's shop, and sent 15 well-tailored applications. She got five replies and two interviews. The lesson: fewer, better-targeted applications beat mass sending.</p>

<blockquote><p><strong>Quick check:</strong> What three questions does an employer silently ask?</p><p>Answer: Can this person do the job, will they fit in, and can I trust them, so your brand must answer all three.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You have no work experience. What can you use as proof?</p><p>Answer: Study projects, volunteer work, internships, short courses and references, because they show real skills and reliability.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose one target job type and collect five adverts for it.</li>
<li>List the repeating skills and match each to a project or experience of yours.</li>
<li>Rewrite your headline and CV summary for that job type.</li>
<li>Write three situation, action, result stories for interviews and practise them aloud.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Show employers you can do the job, fit in and be trusted.</li>
<li>Use projects, volunteering and courses as proof when experience is limited.</li>
<li>Target a few roles and tailor each application.</li>
<li>Prepare stories and treat every contact as part of your brand.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Branding for freelancers, artists and small business owners',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to apply branding to selling your own services, art or products, and choose where to be seen. This matters because people who work for themselves have no employer to speak for them: your brand is how new customers decide whether to trust you with their money.</p>

<h3>What is different when you sell yourself</h3>
<p>Freelancers, artists and small business owners are the product. Customers judge both the work and the person. Three things matter most: a clear offer (what exactly you sell and to whom), visible proof (examples and reviews), and an easy way to buy or book. If any of these is missing, interested people drift away.</p>

<h3>The clear offer</h3>
<table>
<thead><tr><th>Weak offer</th><th>Clear offer</th></tr></thead>
<tbody>
<tr><td>I do graphics</td><td>Logo and social media pack for new restaurants, ready in one week</td></tr>
<tr><td>I sell clothes</td><td>Ready-made African print shirts for men, sizes S to XXL, delivered in Kampala same day</td></tr>
<tr><td>I make music</td><td>Live acoustic sets for weddings and corporate dinners, 90 minutes, own sound equipment</td></tr>
</tbody>
</table>

<h3>Proof that sells</h3>
<ul>
<li><strong>Portfolio:</strong> five best examples with a short story each.</li>
<li><strong>Testimonials:</strong> short quotes from happy clients, used with their permission.</li>
<li><strong>Process:</strong> a simple description of how you work and how long it takes, which lowers fear.</li>
<li><strong>Prices:</strong> show starting prices or ranges where you can; hidden prices make buyers nervous.</li>
</ul>

<h3>Choose two main places to be seen</h3>
<p>Do not try to be on every platform. Pick two where your audience already is. A photographer may choose Instagram plus a Google Business Profile; a farmer-trainer may choose WhatsApp groups plus LinkedIn; a tailor may choose WhatsApp Business plus Facebook. WhatsApp Business offers a catalogue and quick replies, which suit small sellers; check the official help pages for current features.</p>

<h3>Make buying easy</h3>
<ol>
<li>Put a clear contact method on every page: phone, WhatsApp link or email.</li>
<li>State how to pay, for example mobile money, and the steps after payment.</li>
<li>Use a simple written agreement or order confirmation that lists work, price, deadline and revisions.</li>
<li>Reply quickly; speed itself is part of your brand.</li>
</ol>

<h3>Worked example: a freelance photographer</h3>
<p>Sarah chose Instagram and WhatsApp Business. Her catalogue lists three packages with prices, her highlights show real weddings with permission, and every message gets a reply within an hour. A new couple said, "We chose you because everything was clear, and you answered quickly".</p>

<h3>Case scenario</h3>
<p>Ben, a young painter in Kampala, posted his paintings with no prices and no contact details, only "DM for info". Many people liked but few bought. He decided to add prices for small works, a short story about each piece, and a WhatsApp link, and he held one small online viewing a month. Sales rose from one painting every two months to three a month. The lesson: remove friction and give buyers the information they need to say yes.</p>

<blockquote><p><strong>Quick check:</strong> What three things matter most when you sell yourself?</p><p>Answer: A clear offer, visible proof and an easy way to buy or book.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why choose only two main platforms?</p><p>Answer: Because doing a few well and consistently beats being thin and careless everywhere.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write your offer in one sentence: what you sell, to whom, in what time.</li>
<li>Collect five portfolio items and two client testimonials, with permission.</li>
<li>Choose two platforms and complete their profiles with the same logo and bio.</li>
<li>Add a clear contact and payment method to each page and test it by messaging yourself.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>When you sell yourself, the offer, proof and ease of buying decide sales.</li>
<li>Show prices, process and testimonials to reduce fear.</li>
<li>Choose two platforms and use them well.</li>
<li>Speed, clarity and written agreements make you look professional.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Measuring progress and your 90-day plan',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose simple measures of progress and follow a clear 90-day plan to turn everything you learned into habits. This matters because branding is a slow, steady activity, and without a plan and a way to see progress, most people quit after two weeks.</p>

<h3>What to measure</h3>
<p>Choose a few numbers linked to your goal, not just likes. Match them to what you want.</p>
<table>
<thead><tr><th>Goal</th><th>Useful measures</th></tr></thead>
<tbody>
<tr><td>Get a job</td><td>Applications sent, interviews received, recruiter messages, profile views</td></tr>
<tr><td>Win clients</td><td>Enquiries per month, quotes sent, bookings, repeat clients</td></tr>
<tr><td>Be known as an expert</td><td>Invitations to speak, requests for advice, comments from your target audience</td></tr>
<tr><td>Grow a business</td><td>Customers per month, referrals, reviews, sales</td></tr>
</tbody>
</table>
<p>Likes and followers are weak measures unless they come from your target audience. Ten messages from real prospects beat a thousand likes from strangers.</p>

<h3>A monthly review</h3>
<ol>
<li>Set a fixed day each month, for example the last Sunday.</li>
<li>Record your numbers in a simple table in a notebook or spreadsheet.</li>
<li>Ask: What worked? What did not? What will I stop, keep and start?</li>
<li>Ask one trusted person: "What do you think I am known for now?"</li>
<li>Adjust the next month's plan.</li>
</ol>

<h3>The 90-day plan</h3>
<table>
<thead><tr><th>Days</th><th>Focus</th><th>Actions</th></tr></thead>
<tbody>
<tr><td>1 to 30</td><td>Foundation</td><td>Strengths and story, brand statement, voice words, palette, photo, footprint audit, CV and LinkedIn updated, brand sheet created</td></tr>
<tr><td>31 to 60</td><td>Visibility</td><td>One useful post a week, one short article, five new connections a week with personal notes, first talk or video, portfolio of five items</td></tr>
<tr><td>61 to 90</td><td>Relationships and results</td><td>Follow-up messages to every contact, two events attended, ask for three testimonials or recommendations, monthly review, plan the next 90 days</td></tr>
</tbody>
</table>
<p>Keep the plan small enough that you can really do it. Set weekly tasks of 30 to 60 minutes and mark each as done.</p>

<h3>Staying motivated</h3>
<ul>
<li>Find an accountability partner and share your plan with them weekly.</li>
<li>Celebrate small wins, such as a first comment from a stranger.</li>
<li>Expect slow starts; results often come after the third month.</li>
<li>When you fall behind, restart with the next small task, not with guilt.</li>
</ul>

<h3>Worked example: an agronomist</h3>
<p>Hannah's 90-day plan: in month one, she updated her CV, LinkedIn and bio; in month two she posted weekly and recorded two farmer-tip videos; in month three she gave a talk at a cooperative meeting and asked three farmers for testimonials. At the review she found enquiries rose from one per month to five. She decided to keep the weekly post and add a monthly talk.</p>

<h3>Case scenario</h3>
<p>Violet started with great energy, posting daily for ten days, then stopped because she did not see followers rising. She decided to change her approach: she chose one measure, enquiries from her target audience, set four weekly tasks and reviewed monthly. After three months she had seven enquiries and two paying clients, even though her followers had risen only a little. The lesson: measure what matters, keep a modest rhythm and give it time.</p>

<blockquote><p><strong>Quick check:</strong> Why are likes a weak measure of progress?</p><p>Answer: They may come from people who will never buy or hire you; measures tied to your goal, such as enquiries, show real progress.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What question should you ask a trusted person during a review?</p><p>Answer: "What do you think I am known for now?" because it shows whether your brand matches your intention.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose one main goal and three measures linked to it.</li>
<li>Copy the 90-day table into a notebook and add dates and weekly tasks.</li>
<li>Pick an accountability partner and send them your plan.</li>
<li>Put your first monthly review date in your phone calendar.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Measure results tied to your goal, not only likes.</li>
<li>Review monthly: what worked, what to stop, keep and start.</li>
<li>Follow a 90-day plan: foundation, visibility, relationships and results.</li>
<li>Small steady actions beat big bursts that fade.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What three questions do employers silently ask about a candidate?
* Can they do the job, will they fit, and can I trust them?
- Are they rich, famous and young?
- Do they have many followers, a car and a house?
- Do they speak loudly, dress expensively and travel?
= Employers look for ability, fit and trustworthiness. Your brand should answer all three.

Q: With little work experience, which is the best proof to include?
- Nothing; wait until you have a job
- A list of adjectives
* Study projects, volunteering, internships and short courses described with results
- A photo of a car
= Real projects and volunteer work show skills and reliability even before a first job.

Q: Which strategy usually works best for a graduate job search?
- Sending 80 identical applications
* Targeting one or two job types and tailoring each application
- Waiting for the employer to call
- Applying only to jobs you are overqualified for
= Focus and tailoring show employers you understand the role.

Q: Which three things matter most when you sell your own services?
- Expensive office, a car and a suit
* A clear offer, visible proof and an easy way to buy or book
- Many hashtags, many emojis and daily posts
- A secret price and a long delay
= Clear offers, proof and easy contact remove doubt and make buying simple.

Q: Which of these are good progress measures for a freelancer? (choose all that apply)
* Enquiries per month
- Number of random followers only
* Bookings and repeat clients
* Referrals from happy customers
= Measures linked to income and relationships show real progress; follower counts alone can mislead.

Q: Ben posts paintings without prices or contacts and sells very little. What should he do?
- Delete the paintings
- Post more of the same
* Add prices, short stories about each piece and a clear WhatsApp contact
- Wait for buyers to guess the price
= Removing friction and giving clear information helps interested people become buyers.

Q: Violet quit after ten days because followers did not rise quickly. What would be the better approach?
- Buy followers
* Choose a goal-linked measure such as enquiries, set weekly tasks and review each month for 90 days
- Quit social media for good
- Post only at midnight
= A steady rhythm and meaningful measures show progress that follower counts hide.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which statement about personal brands is true?
- Only celebrities have them
* Everyone who deals with people has a reputation, so the choice is whether to shape it
- They are created by buying a logo
- They require thousands of followers
= A brand is a reputation, and shaping it on purpose makes it clearer and more accurate.

Q: Which of these is a strength with proof?
- I am a natural leader
- I have lots of potential
* I organised a clean-up for 80 students and raised UGX 600,000
- I am a good person
= Concrete facts can be checked and remembered, unlike adjectives.

Q: A good brand story includes which parts? (choose all that apply)
* Where you started
* A turning point
* Where you are heading
- A secret you promised to keep
= Those three parts show your direction and growth, and every part must be true.

Q: Which is the best brand statement formula?
- I do everything for everyone
* I help [who] to [result] by [how or what makes me different]
- I am the best in the world
- Please hire me
= The formula makes the audience, result and difference clear in one sentence.

Q: Kevin, a designer, offers "logos, posters, websites, photos, videos, anything" and only gets cheap clients. What is the most useful change?
- Lower his prices further
* Focus on one clear audience and service, such as logos for new restaurants
- Remove his portfolio
- Offer even more services
= A clear focus makes him the obvious choice and lets him compete on value.

Q: What is brand voice?
- Your phone ringtone
* Your personality in words, kept consistent across platforms
- A recording studio
- The language of your friends
= A steady voice helps people recognise and trust you.

Q: How should light fall when taking a phone profile photo?
- From a bright window behind you
* Softly on your face from the front, such as facing a window
- From a flash in a dark room
- No light is needed
= Front lighting shows your face clearly and avoids dark silhouettes.

Q: Which logo is likely to work best as a tiny profile picture?
- One with five colours and thin lines
* A bold name or monogram in two colours
- A very detailed drawing with tiny text
- A photo with a long caption
= Simple marks stay readable at small sizes.

Q: Which CV bullet is strongest?
- Responsible for sales
- Hardworking person
* Sold about 30 items a week and increased repeat orders
- Good with everything
= It shows action and result, which employers value more than duties.

Q: Why should you save your CV as a PDF before emailing it?
* The layout stays the same on every device
- It makes the file impossible to open
- It hides your skills
- It is required by law
= PDFs keep your design fixed, so your CV looks as you intended.

Q: A strong LinkedIn headline for a public health graduate would be:
- Student
- Hello world
- Unemployed
* Public health graduate | Helping rural clinics improve patient records | Kampala
= The headline appears next to your name everywhere and should say who you help and how.

Q: Fatuma uses three different job titles on three platforms. What should she do?
- Use a different title every month
* Choose one honest title and update every platform from a brand sheet
- Delete the titles
- Ask customers to guess
= Consistent facts across platforms prevent confusion and build reliability.

Q: Which is the best first step in an online footprint audit?
- Delete all your accounts
* Search your name, including images, in a private window and note what appears
- Post a lot of new content at once
- Change your phone number
= Seeing what strangers see tells you what to keep, fix or remove.

Q: Which should you NEVER post publicly? (choose all that apply)
* Passwords and one-time codes
* National ID number
- A helpful tip from your work
* Your exact live location at all times
= Private and security details can be misused to harm you.

Q: Which post follows a good structure?
- "Buy now!!!"
* A hook naming a problem, two or three practical points and an invitation to message
- A blurry photo with no words
- A long angry complaint
= A hook, useful content and a call to action make a post helpful and clear.

Q: Which posting habit best builds credibility?
- Posting once in a while with no plan
- Posting ten ads a day
* One useful post a week, kept going for months
- Posting only when you are angry
= Consistent, helpful content builds recognition and trust.

Q: What is the best way to begin speaking in public if you are nervous?
- Wait until you are famous
* Start with a short free talk or a two-minute video, then build up
- Avoid it forever
- Read a long script without looking up
= Small, repeated practice builds confidence and gives you proof of your skill.

Q: What should an elevator introduction end with?
- A request for money
* A question that invites the other person to talk
- An apology
- A long list of your qualifications
= A question turns a pitch into a conversation.

Q: Brenda sent "Please give me a job" to 30 strangers and no one replied. What is a better approach?
- Send it to 300 strangers
* Pick a few relevant people, comment thoughtfully and send a personal note asking one specific question
- Stop networking
- Send the message again every day
= Personal, relevant and polite messages get answers.

Q: Within roughly what time should you follow up after a meeting?
- After six months
* Within about 48 hours
- Only when you need a favour
- Never
= A quick follow-up helps the person remember you and shows professionalism.

Q: You wrote a follow-up and got no answer after a week. What is sensible?
- Send ten messages a day
* Send one short, polite reminder and then be patient
- Post about the person publicly
- Delete their contact and complain
= One gentle reminder respects their time and may be all they need.

Q: What is the best first reaction to a harsh public comment about your work?
- Reply immediately in anger
- Delete all negative comments
* Pause, read again calmly, acknowledge any truth, and reply politely with a fix
- Block everyone
= Calm responses protect your reputation and show professionalism.

Q: Dennis answers a harsh review with a long attack on the client. Other readers see it. What is the best repair?
* Remove or correct it, apologise publicly, show the facts calmly and offer a fix
- Write more attacks
- Delete his account
- Ignore everything
= How you respond shapes your brand more than the criticism itself.

Q: Which of these is an unethical brand practice?
- Asking real clients for reviews
* Buying fake followers or reviews
- Showing your own practice projects
- Crediting a photographer whose picture you use with permission
= Fake proof misleads people and damages trust when discovered.

Q: Alex has finished one online data course. What is the most honest and effective profile line?
- Expert data scientist
* Graduate learning data analysis; completed a spreadsheets course and analysed sales data for a family shop
- Famous analyst
- Certified by every company
= Honest descriptions of your level build trust and attract suitable opportunities.

Q: Which is the best proof for a graduate with no work experience?
- A long list of adjectives
- Nothing, wait for a job
* Study projects, volunteering, internships, short courses and references described with results
- A picture of a friend's car
= Real activities with results demonstrate skills and reliability.

Q: Sharon applied for 80 "any job" openings and got two replies. What should she do?
- Send 80 more
* Target one or two role types and tailor her CV and letters for each application
- Stop looking for work
- Apply only to jobs she dislikes
= Focused, tailored applications show employers she understands the role.

Q: What matters most when a freelancer sells services online?
- Using the most hashtags
* A clear offer, visible proof and an easy way to book or buy
- A secret price list
- Changing the logo weekly
= Customers need to know what you offer, trust you and be able to act easily.

Q: Which is the best measure of progress for a freelancer who wants clients?
- Likes from strangers only
* Enquiries from the target audience, bookings and repeat clients
- Number of emojis used
- Hours spent online
= Measures tied to your goal show whether your brand is working.

Q: What is a good shape for a 90-day branding plan?
- Do everything on day one, then stop
* Foundation in the first 30 days, visibility in the next 30, relationships and results in the last 30
- Wait 90 days before starting
- Change your name every month
= A phased, steady plan builds habits and results step by step.
QUIZ,
];
