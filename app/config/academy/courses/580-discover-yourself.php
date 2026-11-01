<?php
return [
    'slug'        => 'discover-yourself',
    'title'       => 'Discover Yourself: Find Your Strengths, Values and Purpose',
    'category'    => 'careers',
    'level'       => 'beginner',
    'icon'        => 'sprout',
    'sort'        => 580,
    'summary'     => 'Understand your strengths, interests, values and story, learn from feedback and your community, then use what you discover to choose a study, work or business direction and write a 12-month plan.',
    'description' => '<p>Many young people are told to choose a course, a job or a business before anyone has helped them ask a simpler question: who am I, and what do I care about? This course helps you answer it step by step, honestly and without spending money. It is written for school leavers, university students, young parents, job seekers and anyone who feels unsure about the next step.</p><p>You will practise self-awareness, find your strengths and what gives you energy, name and rank your values, look at your beliefs and mindset, and understand personality differences in a simple, label-free way. You will study your life story and turning points, think about purpose and a good life, and face weaknesses and blind spots kindly. You will also look at the family, community and cultural influences that shape you, and how to respect them while still thinking for yourself.</p><p>The last modules turn what you learn into action: comparing options for studies, work or business, testing them cheaply, making decisions that fit your values, and writing a one-page personal profile and a 12-month direction statement. This course is for learning and reflection. It is not a medical or psychological test, and it does not replace a counsellor, teacher or trusted adult when life feels heavy.</p>',
    'outcomes'    => [
        'Explain what self-awareness is and use simple habits to build it',
        'Identify your strengths, talents and skills using feedback, past wins and honest reflection',
        'Describe your interests and the activities that give you energy',
        'Name your core values and rank them to guide choices',
        'Tell the difference between a fixed and a growth mindset and challenge limiting beliefs',
        'Understand personality differences without labels and work better with others',
        'Describe your life story, purpose and vision of a good life in your own words',
        'Spot weaknesses and blind spots and plan small steps to improve',
        'Compare study, work and business options and write a one-page profile with a 12-month direction statement',
    ],
    'requirements' => ['A notebook and pen, or a phone to type notes', 'No experience needed; an open mind and honesty with yourself'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'World Health Organization: mental health', 'url' => 'https://www.who.int/health-topics/mental-health'],
        ['title' => 'UNICEF Uganda', 'url' => 'https://www.unicef.org/uganda/'],
        ['title' => 'Khan Academy: free learning', 'url' => 'https://www.khanacademy.org/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: Starting to know yourself',
            'summary' => 'Learn what self-awareness is, then find your strengths and the interests and activities that give you energy.',
            'lessons' => [
                [
                    'title'   => 'Self-awareness: why knowing yourself matters',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what self-awareness is and use three simple habits to grow it. This matters because every big choice, such as what to study, whom to work with or when to start a business, goes better when you understand what you are good at, what you care about and how you react under pressure.</p>

<h3>What self-awareness means</h3>
<p><strong>Self-awareness</strong> is the ability to notice your own thoughts, feelings, habits and behaviour, and to see how they affect you and other people. It has two sides. The inside side is how you see yourself: what you feel, want and believe. The outside side is how others see you: what they notice about the way you speak, work and treat people. Strong self-awareness means these two pictures are close to each other.</p>

<h3>Why it matters</h3>
<ul>
<li><strong>Better choices:</strong> you pick a path that suits you, not only one that looks impressive.</li>
<li><strong>Better relationships:</strong> you notice when you are tired, angry or unfair, and you fix it before it hurts someone.</li>
<li><strong>Faster growth:</strong> you can only improve what you can see.</li>
<li><strong>More confidence:</strong> knowing your real strengths makes you steady when others doubt you.</li>
</ul>

<h3>Three habits that build self-awareness</h3>
<ol>
<li><strong>Pause and name it.</strong> When a strong feeling comes, say to yourself, "I am feeling nervous" or "I am feeling jealous". Naming a feeling makes it easier to handle.</li>
<li><strong>Write a short daily note.</strong> Two minutes at night: what went well, what was hard, what did I learn?</li>
<li><strong>Ask for honest feedback.</strong> Choose one or two people you trust and ask, "What do you see me doing well, and what could I do better?"</li>
</ol>

<h3>Worked example: Achieng in Gulu</h3>
<p>Achieng has just finished Senior 6 in Gulu. Everyone says she should study medicine because her marks are high. She keeps a small notebook for two weeks. Each night she writes what she enjoyed. The same lines appear again and again: "helped Mama count the shop money", "explained fractions to my cousin", "enjoyed planning the school fundraiser". She had not noticed that she was never writing about hospitals or laboratories. Her notebook shows a pattern that her marks alone could not.</p>

<h3>Case scenario</h3>
<p>Brian, a university student in Kampala, always lost his temper in group assignments. He believed the others were "lazy". A friend gently told him, "You take over and then complain nobody helps." Brian was upset at first, then decided to test it: in the next project he asked each member what task they preferred and let them lead it. The group finished early and nobody argued. The lesson: other people often see what we cannot, and listening to honest feedback gave Brian a skill he did not know he lacked.</p>

<blockquote><p><strong>Quick check:</strong> What are the inside and outside sides of self-awareness?</p><p>Answer: The inside side is how you see yourself, and the outside side is how others see you; good self-awareness brings the two close together.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why is writing a short daily note useful?</p><p>Answer: It shows repeating patterns in what you enjoy and struggle with, which you may miss in the rush of daily life.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>For three evenings, write three lines: one thing that went well, one thing that was hard, one thing you learned.</li>
<li>Read the three notes and underline anything that repeats.</li>
<li>Ask one trusted person: "What is one thing you see me doing well?" Write down the answer without arguing.</li>
<li>Write one sentence starting "I am learning that I ..."</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Self-awareness is noticing your thoughts, feelings and behaviour, and how others see you.</li>
<li>It helps with choices, relationships, growth and confidence.</li>
<li>Pause and name feelings, keep a short note, and ask for honest feedback.</li>
<li>Patterns over time tell you more than one good or bad day.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Strengths, talents and skills: finding what you do well',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to tell a talent from a skill, and find your own strengths using feedback, past wins and simple self-checks. This matters because people who build their life around what they do well, and keep improving it, usually progress faster and feel more satisfied than those who only try to fix every weakness.</p>

<h3>Talent, skill and strength</h3>
<table>
<thead><tr><th>Word</th><th>Meaning</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Talent</td><td>A natural leaning, something that comes easily to you</td><td>Remembering people's names, staying calm in noise</td></tr>
<tr><td>Skill</td><td>Something you learned and can practise</td><td>Typing, sewing, using a spreadsheet, public speaking</td></tr>
<tr><td>Strength</td><td>A talent or skill that you use well, often, and that gives you energy</td><td>Explaining ideas clearly to younger students</td></tr>
</tbody>
</table>
<p>A talent grows into a strength only when you practise it. Someone with a talent for numbers who never practises will be passed by someone who studies every day.</p>

<h3>Four ways to find your strengths</h3>
<ol>
<li><strong>Past wins.</strong> List five times you were proud of something you did. For each, ask: what did I do well there? Repeating words are your strengths.</li>
<li><strong>Feedback.</strong> Ask three people: "When do you see me at my best?" Look for the same answer from different people.</li>
<li><strong>What others ask you for.</strong> Do people ask you to fix their phones, write their messages, settle their quarrels or style their hair? Requests show what others trust you for.</li>
<li><strong>Simple self-checks.</strong> Free online questionnaires and school career guidance sheets can give ideas to think about. Treat them as conversation starters only. They are not scientific proof of who you are, and no test can decide your future.</li>
</ol>

<h3>Worked example: Naki the young mother</h3>
<p>Naki, a young mother in Mukono, wanted to start a small business but felt she had "no skills". She listed past wins: she organised the family wedding food for 150 guests, she kept her late mother's savings group records, and neighbours always asked her to braid hair for events. Her repeating strengths were organising, careful records and working with people. Together they pointed to a catering and event-help business, something she had overlooked because she thought these were just "normal things".</p>

<h3>Common mistakes</h3>
<ul>
<li>Believing "if it is easy for me, it is not a real skill". Easy for you does not mean easy for others.</li>
<li>Copying a friend's strengths list instead of using your own evidence.</li>
<li>Collecting praise only from people who always say nice things. Ask for specific examples.</li>
</ul>

<h3>Case scenario</h3>
<p>Samuel, a boda-boda rider in Jinja, said he had "no talent". When asked what customers said about him, he laughed: they often thanked him for being on time, remembering regular customers' routes and keeping the helmet clean. Passengers recommended him to friends and schoolchildren's parents trusted him for the morning trips. Samuel realised reliability and customer care were real strengths. He started a small list of regular customers and agreed monthly rates. The lesson: strengths often hide in everyday work, and other people's comments can reveal them.</p>

<blockquote><p><strong>Quick check:</strong> What is the difference between a talent and a skill?</p><p>Answer: A talent comes naturally, while a skill is learned through practice; both become strengths when you use them well.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why should a free online questionnaire not decide your future?</p><p>Answer: It is only a prompt for thinking and does not prove who you are, so you should compare it with your own evidence and feedback.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List five times you felt proud of something you did, and write what you did well in each.</li>
<li>Circle words that repeat; these are possible strengths.</li>
<li>Ask three people: "When do you see me at my best?" Write their answers.</li>
<li>Write a short list of your top three strengths with one proof sentence each.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Talents come naturally; skills are learned; strengths are what you use well and often.</li>
<li>Use past wins, feedback and what people ask of you to find strengths.</li>
<li>Questionnaires are only prompts, not scientific proof.</li>
<li>Everyday work can hide real strengths.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Interests and energy: what draws you in',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe your real interests and tell which activities give you energy and which drain it. This matters because a path that matches your interests is easier to stay on when work gets hard, and many young people choose a course only because it sounds respected, then feel stuck.</p>

<h3>Interest is not the same as skill</h3>
<p>An <strong>interest</strong> is a subject or activity you want to know more about. You may be interested in football, farming or fashion without yet being good at it. A <strong>skill</strong> can be built later, so an honest interest is a good starting point. The best direction often lies where your interests meet your strengths and the needs of people around you.</p>

<h3>The energy test</h3>
<p>Some activities leave you tired but happy; others leave you tired and empty. Think of the last two weeks and sort what you did into two columns.</p>
<table>
<thead><tr><th>Gives me energy</th><th>Drains my energy</th></tr></thead>
<tbody>
<tr><td>Teaching a friend to use WhatsApp Business</td><td>Writing long reports alone</td></tr>
<tr><td>Fixing a broken phone screen</td><td>Sitting in meetings without a role</td></tr>
<tr><td>Cooking for a family gathering</td><td>Counting stock late at night</td></tr>
</tbody>
</table>
<p>Look at the columns for clues. Do you like working with people or alone? With ideas, things or numbers? Outdoors or indoors? Building something or helping someone? Tidy routines or changing days? Every answer narrows the field.</p>

<h3>Follow curiosity for a week</h3>
<p>Interests grow when you act on them. Spend a few minutes each day on one curiosity: watch a short video, read an article, talk to a person who does that work, or try a tiny task. Notice whether your interest grows or fades after you try. Both outcomes are useful, because they save you years of guessing.</p>

<h3>Worked example: Moses in Kampala</h3>
<p>Moses is a second-year student of business in Kampala. He thought he wanted to be an accountant. His energy list showed that the parts of his week he enjoyed were organising the faculty social events and talking to nervous first-years. Accounting practice left him flat. He asked an events manager for a short chat and volunteered at one event. His interest in people and events grew stronger, so he kept his degree but began choosing electives in marketing and event planning. He did not waste his studies; he steered them.</p>

<h3>Case scenario</h3>
<p>Peace, a Senior 6 leaver in Gulu, chose engineering because her uncle was an engineer. In her first term she disliked the drawing and mathematics labs but loved the group presentation on rural water access. She felt guilty for wanting to change. She booked a talk with a school career counsellor, listed her energy sources, and discovered that community development and public health fit her. She changed to a related course in time, with her family's support after she explained her reasons. The lesson: changing direction early after honest observation is wisdom, not failure.</p>

<blockquote><p><strong>Quick check:</strong> Can you be interested in something you are not yet good at?</p><p>Answer: Yes, because interests come first and skills can be learned with practice.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What does the energy test ask you to notice?</p><p>Answer: Which activities leave you feeling alive and which leave you drained, because this shows what suits you.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Draw two columns and list at least five activities that gave you energy and five that drained you in the last two weeks.</li>
<li>Write the pattern you see: people, ideas, things or numbers; indoors or outdoors.</li>
<li>Pick one curiosity and spend 15 minutes on it each day for a week.</li>
<li>At the end write: "My interest grew / stayed the same / faded because ..."</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Interests are what you want to learn about; skills can be built later.</li>
<li>Notice which activities give energy and which drain it.</li>
<li>Test an interest with small actions before making a big commitment.</li>
<li>Changing direction early is better than staying on a path that does not fit.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which best describes self-awareness?
- Thinking you are better than others
* Noticing your own thoughts, feelings and behaviour and how others see you
- Never making a mistake
- Knowing everything about other people
= Self-awareness is understanding yourself, inside and outside, so that you can choose and grow wisely.

Q: Why does asking trusted people for feedback help build self-awareness?
- It proves they are always right
- It lets you avoid thinking for yourself
* Others may notice strengths and habits you cannot see yourself
- It makes you more popular
= Feedback adds the outside view. You still decide what to do with it, but it fills your blind spots.

Q: What is the difference between a talent and a skill?
* A talent comes naturally, while a skill is learned through practice
- A talent is learned and a skill is natural
- They mean exactly the same thing
- Only adults have skills
= Talents are natural leanings and skills are built. Both become strengths when used well and often.

Q: Which are good ways to find your strengths? (choose all that apply)
* Listing past wins and what you did well in them
* Asking several people when they see you at your best
- Copying a friend's list of strengths
* Noticing what people often ask you for help with
= Evidence from your own life and from others is reliable. A copied list is not about you.

Q: Samuel, a boda-boda rider, says he has no talent, yet passengers often thank him for being reliable and remembering their routes. What should he conclude?
- That passengers are only being polite
* That reliability and customer care are real strengths he can build on
- That he should stop riding
- That strengths only count if you have a degree
= Repeated comments from different customers are evidence. Everyday strengths are real and can support a better business.

Q: A free online questionnaire says Esther should be an accountant. What is the wisest use of the result?
- Follow it exactly without questions
- Ignore it completely and never reflect
* Treat it as an idea to think about and compare with her own evidence and feedback
- Show it to employers as proof of her ability
= A simple questionnaire is a conversation starter, not scientific proof or a decision about your future.

Q: Peace feels drained in her engineering labs but energised when working on community water projects. What does this suggest?
- She is lazy
* Her energy pattern points to community-focused work, so she should explore related courses with a counsellor
- She should stay silent and finish the course anyway
- Interest does not matter in a career
= Energy patterns are useful clues. Exploring options early with guidance is better than staying on a path that does not fit.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Values, beliefs and personality',
            'summary' => 'Name and rank your values, examine your beliefs and mindset, and understand personality differences in a simple, label-free way.',
            'lessons' => [
                [
                    'title'   => 'Personal values: what matters most to you',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to name your core values, rank them and use them to judge choices. This matters because when you do not know your values, you can drift into a life that looks fine to others but feels wrong to you, and you can be pushed around by whoever speaks loudest.</p>

<h3>What values are</h3>
<p><strong>Values</strong> are the principles you believe are important and want to live by, such as honesty, family, faith, learning, freedom, service, security, adventure or fairness. They are different from goals. A goal is something you reach, such as passing an exam. A value is how you want to live while reaching it, such as doing it honestly. Values are also different from rules that others give you. A value only becomes yours when you choose it after thinking.</p>

<h3>How to find your values</h3>
<ol>
<li><strong>Peak moments.</strong> Recall two times you felt really proud. Which principle were you living?</li>
<li><strong>Anger and discomfort.</strong> Recall two times you felt angry or ashamed. Which principle was broken?</li>
<li><strong>Admiration.</strong> Whom do you admire, and for what qualities?</li>
<li><strong>Choose a list.</strong> From a list of about 30 words, circle the 10 that feel most true to you.</li>
</ol>

<h3>How to rank them</h3>
<p>Ranking is the hard but useful part, because values sometimes pull in different directions. Take your ten words and compare them in pairs: "If I could only keep one, which would it be?" Keep going until you have your top five, with number one at the top. Then write one sentence for each: what does this value look like in my daily actions?</p>
<table>
<thead><tr><th>Value</th><th>What it looks like in daily life</th></tr></thead>
<tbody>
<tr><td>Honesty</td><td>I tell customers the real price and do not hide faults</td></tr>
<tr><td>Family</td><td>I call my grandmother every Sunday and send what I can</td></tr>
<tr><td>Learning</td><td>I read or practise something new for 20 minutes each day</td></tr>
</tbody>
</table>

<h3>Worked example: Grace in Kampala</h3>
<p>Grace, a final-year student, was offered an internship with a company that paid well but expected staff to inflate sales figures. She listed her top values: honesty, learning, family, security. Honesty was number one. She also valued security, so the pay was tempting. By ranking, she saw that honesty came before pay. She declined politely and accepted a lower-paid internship at a firm known for fair practice. Two years later that firm hired her permanently, and she slept well in the meantime.</p>

<h3>Case scenario</h3>
<p>Ronald, a young man in Gulu, valued both family and freedom. His uncle offered him a stable job in the family shop but with strict hours and no room to try his own ideas. Ronald felt torn and angry. He listed what each value meant: family meant being useful to relatives, freedom meant creating his own plans. He proposed a compromise: work in the shop four days a week and use one day to test a small phone-accessories stall. His uncle agreed for six months. The lesson: knowing your values helps you negotiate honestly instead of just obeying or rebelling.</p>

<blockquote><p><strong>Quick check:</strong> What is the difference between a goal and a value?</p><p>Answer: A goal is something you reach, while a value is how you want to live as you reach it.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why do you need to rank your values?</p><p>Answer: Values can pull in different directions, so ranking tells you which one wins when you must choose.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write about two proud moments and two angry or uncomfortable moments; name the value in each.</li>
<li>From a list of about 30 values, circle ten and then narrow to five by pair comparison.</li>
<li>Write one sentence for each top value describing what it looks like in your daily actions.</li>
<li>Keep the list somewhere you will see it, such as the first page of your notebook.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Values are principles you choose to live by; goals are things you reach.</li>
<li>Peak moments, anger and admiration reveal your values.</li>
<li>Ranking shows what matters most when values compete.</li>
<li>Writing what a value looks like in action makes it real.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Beliefs and mindset: fixed or growth',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to spot beliefs that hold you back, explain the difference between a fixed and a growth mindset, and practise changing one belief. This matters because what you believe about yourself decides what you try, and what you try decides what you become.</p>

<h3>What a belief is</h3>
<p>A <strong>belief</strong> is an idea you accept as true. Some beliefs help ("If I practise, I can improve"). Others limit you ("I am bad at maths", "Girls cannot do engineering", "People from my village do not succeed"). Many beliefs come from childhood, school, family or a single bad experience. They feel like facts, but often they are only old stories.</p>

<h3>Fixed and growth mindset</h3>
<p>Researchers who study motivation use the idea of a <strong>fixed mindset</strong> (ability is set at birth) and a <strong>growth mindset</strong> (ability can grow with effort, good methods and help). Real people hold a mix of both depending on the topic, so do not label yourself as one or the other. Use the idea as a tool to notice your own thinking.</p>
<table>
<thead><tr><th>Situation</th><th>Fixed thought</th><th>Growth thought</th></tr></thead>
<tbody>
<tr><td>Failing an exam</td><td>I am just not clever</td><td>What did I miss, and how can I study differently?</td></tr>
<tr><td>A friend succeeds</td><td>They are lucky and better than me</td><td>What can I learn from how they work?</td></tr>
<tr><td>Hard new task</td><td>I should avoid it so I do not look foolish</td><td>I cannot do it yet, so I will practise</td></tr>
<tr><td>Criticism</td><td>They are attacking me</td><td>Is there a useful point I can use?</td></tr>
</tbody>
</table>

<h3>Changing a limiting belief</h3>
<ol>
<li><strong>Write it down.</strong> "I am bad at speaking in public."</li>
<li><strong>Ask where it came from.</strong> One bad presentation in Senior 2, and a teacher who laughed.</li>
<li><strong>Look for evidence against it.</strong> "I explained the plan clearly to my church youth group."</li>
<li><strong>Rewrite it in a truthful, hopeful way.</strong> "Speaking is hard for me now, and I can improve with practice."</li>
<li><strong>Take one small action.</strong> Speak for one minute to two friends this week.</li>
</ol>
<p>Note that a growth mindset does not mean unlimited ability or that effort alone is enough. It means that improvement is possible and that the right method, support and time usually matter more than a label.</p>

<h3>Worked example: Joan, young mother</h3>
<p>Joan believed "I am too old to study again" because she was 24 with a baby. She wrote the belief down and listed evidence against it: a neighbour finished a certificate at 30, and Joan herself had learned to run her savings group records. She rewrote: "Studying will be harder with a baby, and I can do it in small steps." She enrolled in an evening bookkeeping short course and studied for 30 minutes while her baby slept.</p>

<h3>Case scenario</h3>
<p>Isaac, a Senior 6 leaver in Gulu, failed mathematics and decided he was "not for numbers" and abandoned plans for a technical course. A cousin pointed out that Isaac had once repaired his family's solar lamp by reading a diagram. Isaac decided to find a tutor and practised for 20 minutes a day, using free lessons on his phone. He improved his result at the retake and joined a solar installation course. The lesson: one failed result is information about method and effort, not a permanent verdict on you.</p>

<blockquote><p><strong>Quick check:</strong> What is a limiting belief?</p><p>Answer: An idea you accept as true that stops you from trying, even though it may only be an old story.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Does a growth mindset mean you can do anything if you try hard?</p><p>Answer: No, it means ability can improve with effort, good methods and help, but time, support and circumstances also matter.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write three beliefs about yourself that stop you from trying something.</li>
<li>For one belief, write where it came from and two pieces of evidence against it.</li>
<li>Rewrite the belief in a truthful, hopeful way.</li>
<li>Do one small action this week that tests the new belief and note what happened.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Beliefs shape what you try; many are old stories, not facts.</li>
<li>Fixed thinking says ability is set; growth thinking says it can improve.</li>
<li>Most people use both, so use the idea as a tool, not a label.</li>
<li>Change a belief by testing it with evidence and one small action.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Personality differences, explained simply',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe how people differ in the way they like to work and communicate, without putting yourself or anyone else into a box. This matters because many conflicts at school, at work and at home come from people who are simply different, not wrong.</p>

<h3>Differences, not boxes</h3>
<p>People differ in how they get energy, how they make decisions, how they plan and how they speak. Some online quizzes claim to give you a fixed "type" for life. Be careful: many of these tests are popular for fun, but they cannot predict your future or decide what job you should do, and people's behaviour changes with the situation, age and experience. This lesson uses simple <strong>scales</strong> instead of types. Most people sit somewhere in the middle and move depending on the day.</p>

<h3>Four simple scales</h3>
<table>
<thead><tr><th>Scale</th><th>One end</th><th>Other end</th></tr></thead>
<tbody>
<tr><td>Energy</td><td>Gains energy from being with people and talking</td><td>Gains energy from quiet time and small groups</td></tr>
<tr><td>Planning</td><td>Likes lists, schedules and finishing early</td><td>Likes flexibility and deciding as things develop</td></tr>
<tr><td>Decisions</td><td>Leans on facts and logic first</td><td>Leans on people's feelings and relationships first</td></tr>
<tr><td>Change</td><td>Likes proven methods</td><td>Likes trying new ideas</td></tr>
</tbody>
</table>
<p>Place yourself with a dot on each line. There is no good or bad end. A team needs people who plan and people who adapt, people who speak up and people who listen.</p>

<h3>Using this in daily life</h3>
<ul>
<li><strong>Study:</strong> a quiet-energy person may revise best alone, while a people-energy person may learn by explaining to a study group.</li>
<li><strong>Work:</strong> if you plan early and your colleague adapts late, agree on a middle: clear deadlines plus room to adjust.</li>
<li><strong>Family:</strong> a parent who likes proven methods may not be refusing your idea out of disrespect. Show a small safe test.</li>
<li><strong>Stretch:</strong> you can act outside your comfort zone when needed. A quiet person can give a good speech; it just costs more energy, so rest afterwards.</li>
</ul>

<h3>Worked example: two friends in Kampala</h3>
<p>Diana and Kato run a small online clothes shop. Diana makes a schedule for posts, orders and deliveries a week ahead. Kato likes to decide on the day and brings fresh ideas for offers. They argued often until they placed themselves on the planning scale and saw that Diana was near the schedule end and Kato near the flexible end. They now agree on fixed delivery days and a Friday slot for Kato's new ideas. Their sales rose and the arguments stopped.</p>

<h3>Case scenario</h3>
<p>Mercy, a quiet university student, was told by classmates she was "unfriendly" because she rarely spoke in groups. She felt hurt and wondered if something was wrong with her. After learning about energy differences, she explained to her group: "I think best when I write first. Please let me send my ideas by message before the meeting." The group agreed, and Mercy's written ideas became the base for their project. A lecturer later praised her careful thinking. The lesson: being different is not a fault, and a calm explanation can turn a misunderstanding into teamwork. If you feel that your shyness or worry stops you from living normally, speak to a counsellor or trusted adult; that is not a weakness.</p>

<blockquote><p><strong>Quick check:</strong> Why should you be careful with quizzes that give you a fixed personality type?</p><p>Answer: They are often only for fun and cannot predict your future, since behaviour changes with situation, age and experience.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Is it better to be outgoing than quiet?</p><p>Answer: No, each style has strengths, and teams do better when both are respected.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Draw the four scales as lines and place a dot for yourself on each.</li>
<li>Write one situation where your style helps you and one where it makes things hard.</li>
<li>Think of a person you sometimes clash with and guess where they sit on each line.</li>
<li>Write one thing you will do differently when working with that person.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>People differ in energy, planning, decisions and attitude to change.</li>
<li>Use scales, not boxes; most people sit in the middle and shift.</li>
<li>No style is better; teams need variety.</li>
<li>Explaining your style calmly reduces misunderstandings.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is a value?
- A goal you hope to reach by next year
* A principle you choose to live by, such as honesty or service
- A rule that someone forces on you
- A talent you were born with
= Values are chosen principles. A goal is reached, but a value shapes how you live while you pursue it.

Q: Why is it useful to rank your values?
- It shows which friends are better
- It removes all difficult choices
- It proves you are a good person
* It tells you which value should win when two of them pull in different directions
= Values sometimes conflict, for example family and freedom. Ranking helps you decide with less confusion.

Q: Which activities help you find your values? (choose all that apply)
* Remembering times you felt proud
- Copying the values of the most popular person
* Remembering times you felt angry or ashamed
* Noticing whom you admire and why
= Strong feelings and admiration point to what matters to you. Copying someone else tells you nothing about yourself.

Q: Grace values honesty most, but a well-paid internship asks her to inflate sales figures. Using her ranked values, what is the best choice?
* Decline politely and choose a fair opportunity even if it pays less
- Accept and hope nobody notices
- Accept because pay is always first
- Argue loudly with the manager
= Her top value is honesty, so a choice that breaks it will cost her peace and trust later.

Q: Which sentence shows a growth mindset about failing an exam?
- I am just not clever
* What did I miss, and how can I study differently next time?
- Exams are only for lucky people
- I will never try again
= Growth thinking treats failure as information about method and effort, which you can change.

Q: Which statement about personality is the most accurate?
- A quiz can decide your career for life
- Quiet people cannot lead
* People differ along scales, most sit in the middle, and behaviour can change with the situation
- People with the same type always behave the same
= Simple scales respect differences without boxes. Quizzes are for reflection, not prediction.

Q: Isaac fails mathematics and says, "I am not for numbers." He once repaired a solar lamp using a diagram. What is the most helpful response?
- Agree and drop all technical plans
- Say mathematics does not matter
* Challenge the belief, find a tutor, practise a little daily and retake the paper
- Wait for luck to change
= One failed result shows what to improve, not who he is. Evidence of past success and a better method can change the outcome.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Your story, purpose and blind spots',
            'summary' => 'Read your life story and turning points, think about purpose and a good life, and face weaknesses and blind spots kindly.',
            'lessons' => [
                [
                    'title'   => 'Your life story and turning points',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to map your life story, pick out turning points and describe what they taught you. This matters because looking back carefully shows patterns in what you survive, enjoy and value, and it helps you tell your story with honesty and dignity instead of shame.</p>

<h3>Why your story matters</h3>
<p>Your story is not only what happened to you but also how you understand it. Two people can live through the same hard event, such as losing a parent or failing a year, and tell very different stories about it. One story says "my life is ruined". Another says "that was the hardest time, and it taught me how strong my family is". You cannot change the past, but you can choose carefully what you learn from it.</p>

<h3>Draw a lifeline</h3>
<ol>
<li>Draw a long horizontal line on a page. The left is your birth and the right is today.</li>
<li>Mark 6 to 10 important events: moving home, a school change, a loss, a success, a person who helped you, a decision you made.</li>
<li>Place high points above the line and low points below it.</li>
<li>Next to each event write one line: <em>What did I learn? What did it show about me?</em></li>
</ol>
<p>Your turning points are the events after which your direction changed. They may be good (a teacher who believed in you) or hard (school fees running out). Stay gentle with yourself. If a memory feels too painful, skip it for now and talk to a counsellor, elder or trusted adult before you return to it. You do not need to write about everything.</p>

<h3>Look for patterns</h3>
<p>When the lifeline is finished, read it as if it were about someone else. Ask: what strengths appear again and again? When did I feel most alive? Which people helped me? What did I do in hard times that worked? These patterns are clues to your strengths, values and direction.</p>

<h3>Worked example: Akello in Gulu</h3>
<p>Akello finished Senior 6 in Gulu after missing a year because her family could not pay fees. Her lifeline shows low points: the missed year, her father's illness. High points: a teacher lending her books, winning a debate, saving UGX 300,000 from selling tomatoes. The lessons she wrote: "I keep going when things are hard", "I learn best from people who believe in me", "I like speaking and selling". Her patterns suggested a path in business or teaching, and her story became a source of strength rather than shame.</p>

<h3>Case scenario</h3>
<p>Brian, a boda-boda rider in Kampala, was embarrassed by a year in which an accident kept him off work and in debt. When asked to write his story for a savings group loan application, he first left that year out. A group member advised him to tell it truthfully: the accident, how he repaid the debt in eight months and the safety training he completed afterwards. The group saw discipline and honesty and approved his loan for a second motorcycle. The lesson: telling your hard chapters truthfully and showing what you learned builds trust.</p>

<blockquote><p><strong>Quick check:</strong> What is a turning point?</p><p>Answer: An event after which your direction or understanding changed, whether it was good or hard.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What should you do if a memory in your lifeline is too painful?</p><p>Answer: Skip it for now and talk with a counsellor, elder or trusted adult, because you do not need to write about everything alone.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Draw your lifeline with 6 to 10 events, high points above and low points below.</li>
<li>Write one sentence of learning beside each event.</li>
<li>Circle the strengths or values that appear more than once.</li>
<li>Write three sentences: where I started, my turning point, where I am heading.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>You cannot change the past, but you can choose what you learn from it.</li>
<li>A lifeline shows events, turning points and repeating strengths.</li>
<li>Be gentle; seek a counsellor or trusted adult for painful memories.</li>
<li>Telling hard chapters truthfully, with the lesson learned, builds trust.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Purpose, meaning and a vision of a good life',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe what purpose means, draft a purpose sentence and picture a good life in your own words. This matters because people with a sense of purpose usually find it easier to keep going through hard seasons, and a clear picture of a good life protects you from chasing things that do not matter to you.</p>

<h3>What purpose means</h3>
<p><strong>Purpose</strong> is the reason you get up and work beyond simply surviving: something you care about that helps other people or makes your community better. It does not have to be big or famous. A nurse who comforts frightened mothers, a teacher who never gives up on slow learners and a shopkeeper who sells honest goods at fair prices all have purpose. Many people do not find purpose in one flash; it grows as they act, learn and serve. It can also change as life changes, so you can hold it lightly.</p>

<h3>Four questions that point toward purpose</h3>
<table>
<thead><tr><th>Question</th><th>Example answer</th></tr></thead>
<tbody>
<tr><td>What do I love doing?</td><td>Explaining things and helping people understand</td></tr>
<tr><td>What am I good at?</td><td>Patience, clear speech, planning</td></tr>
<tr><td>What does my community need?</td><td>Better exam support for village students</td></tr>
<tr><td>What could I be paid or supported for?</td><td>Tutoring, a learning centre, a training programme</td></tr>
</tbody>
</table>
<p>Where your answers overlap, a possible purpose appears. Write it as a sentence: <em>"I want to use my [strength] to help [who] with [what]."</em></p>

<h3>Your vision of a good life</h3>
<p>Imagine yourself five to ten years from now, living a life you would call good. Do not write what others expect; write what you truly want. Cover these areas: work or study, money, family and relationships, health, faith or inner life, community, free time. A good life does not need to be rich. For one person it means a home, a loving family and honest work; for another it means travel and research; for another it means serving the village where they grew up. Your vision is yours alone and may change.</p>

<h3>Worked example: Doreen in Kampala</h3>
<p>Doreen studies computer science. Her four answers: she loves solving puzzles, she is good at patient teaching, her neighbourhood has few girls learning computing and a training centre could earn small fees. Her purpose sentence: "I want to use my computer skills and patience to help girls in my community learn technology." Her vision of a good life includes a stable income, time for her family and a small learning centre. She does not have to open it tomorrow; she starts by running a free Saturday class for five girls.</p>

<h3>Case scenario</h3>
<p>Patrick, a young man who sells second-hand clothes in Mbale, felt his work had no meaning and kept comparing himself with friends in offices. A neighbour told him how Patrick's affordable school uniforms had helped her three children stay in school. Patrick began tracking how many families he helped and decided to partner with a tailor to sell clean low-cost uniforms. He found his work felt meaningful once he saw whom it served. The lesson: purpose often hides in the ordinary work you already do when you see its effect on people. If you ever feel that life has no meaning at all and that thought will not go away, talk to a counsellor, health worker or trusted adult soon.</p>

<blockquote><p><strong>Quick check:</strong> Must a purpose be big and famous?</p><p>Answer: No, because it can be ordinary work done with care that helps others, such as teaching, nursing or honest selling.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why describe a good life in your own words instead of copying a friend's?</p><p>Answer: Your vision should reflect what you truly value, otherwise you may chase things that do not satisfy you.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Answer the four questions in the table for yourself with at least two points each.</li>
<li>Write a purpose sentence: "I want to use my ___ to help ___ with ___."</li>
<li>Write a half-page about a good life in ten years, covering work, family, health, community and free time.</li>
<li>Underline three words that matter most and place the page in your notebook.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Purpose is a reason to work beyond survival, often linked to helping others.</li>
<li>It can be ordinary, and it can change over time.</li>
<li>Combine what you love, what you do well, what people need and what can support you.</li>
<li>A good life is defined by your values, not by comparison.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Weaknesses and blind spots: facing them kindly',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to name a few real weaknesses, understand blind spots and choose one small improvement to work on. This matters because everyone has areas to improve, and the people who grow fastest are those who look at them honestly without hating themselves.</p>

<h3>Weaknesses are not the same as worth</h3>
<p>A <strong>weakness</strong> is an area where your skills or habits are not yet strong enough for what you want to do. It is information, not an insult. Being slow at spreadsheets, shy in a meeting or weak at saving money does not make you a bad person. Many weaknesses are simply skills you have not practised yet. Others are habits, such as always arriving late or avoiding hard conversations, that can be changed gradually.</p>

<h3>What a blind spot is</h3>
<p>A <strong>blind spot</strong> is something other people can see about you but you cannot. A person may think they are a good listener while friends feel interrupted. A manager may think they are decisive while the team feels ignored. Blind spots exist for everyone because we see our intentions, while others see our actions.</p>

<h3>Finding weaknesses and blind spots</h3>
<ol>
<li><strong>Review repeated problems.</strong> What complaint or trouble keeps coming back: lateness, unfinished work, quarrels, money that disappears?</li>
<li><strong>Ask for feedback safely.</strong> Ask a trusted person: "What is one thing I could do better?" Listen without defending. Say "thank you" even if it stings.</li>
<li><strong>Look at your goals.</strong> What skill would you need that you do not have yet?</li>
<li><strong>Check for patterns across people.</strong> If two or three different people say the same thing, take it seriously.</li>
</ol>

<h3>Choose one improvement</h3>
<p>Do not try to fix everything at once. Pick one weakness that matters for your goals and write a small plan: what I will do, how often, and how I will know it is working. Weaknesses that are not critical can be handled by teamwork, tools or ignoring them. For example, a person weak at design can use ready-made templates or work with a friend who enjoys design.</p>
<table>
<thead><tr><th>Weakness</th><th>Small step</th><th>Check</th></tr></thead>
<tbody>
<tr><td>Always late</td><td>Set phone alarm 30 minutes earlier and prepare bag the night before</td><td>On time 4 days out of 5</td></tr>
<tr><td>Poor at saving</td><td>Move UGX 2,000 to a separate mobile money wallet each day</td><td>Balance after one month</td></tr>
<tr><td>Afraid to speak</td><td>Give one comment in each class</td><td>Number of comments per week</td></tr>
</tbody>
</table>

<h3>Worked example: Ruth, a Senior 6 leaver</h3>
<p>Ruth in Gulu realised from a teacher's remark that she started many projects and rarely finished them. She had not seen it as a problem. She chose one step: finish one small task each day before starting another, and tick it on a paper chart. After a month she had completed 24 tasks. Her teacher noticed and gave her charge of the class library.</p>

<h3>Case scenario</h3>
<p>Dan, a young man who ran a phone repair stall in Jinja, believed he was honest and fast. Customers kept returning to complain about delays. A friend, who watched him for a day, saw that Dan promised "ten minutes" for jobs that took an hour. Dan felt embarrassed but changed: he began giving realistic times and sending a WhatsApp message when a job was ready. Complaints dropped, and customers began recommending him. The lesson: a blind spot becomes a strength once someone shows it to you and you act.</p>

<blockquote><p><strong>Quick check:</strong> What is a blind spot?</p><p>Answer: Something others can see about you that you cannot, such as interrupting people while thinking you listen well.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why choose only one weakness to work on at a time?</p><p>Answer: Small, focused steps are easier to keep up, while trying to fix everything at once usually leads to giving up.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List three complaints or problems that have repeated in your life.</li>
<li>Ask one trusted person for one thing you could do better; write it down and say thank you.</li>
<li>Pick one weakness that matters for your goals and write a small step with a way to check it.</li>
<li>Try the step for one week and note what changed.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A weakness is information about skills and habits, not about your worth.</li>
<li>Blind spots are visible to others but not to you; feedback reveals them.</li>
<li>Choose one improvement and make it small and measurable.</li>
<li>If worries or low mood affect daily life, talk to a counsellor or trusted adult.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is a turning point in your life story?
- Any event, however small, that you forgot
- Only a happy event such as a party
* An event after which your direction or understanding changed
- A rule your family follows
= Turning points can be good or hard. They show how your life changed and what you learned.

Q: Why do we draw a lifeline?
* To see events, turning points and patterns in our strengths and values
- To prove who has had the hardest life
- To blame people for our problems
- To predict exactly what will happen next year
= A lifeline helps you read your own story and spot patterns. It is not for blame or competition.

Q: Which is true about purpose? (choose all that apply)
* It can be ordinary work that helps people
- It must be famous and large
* It can change as life changes
* It often grows through action and learning
= Purpose is personal and flexible. Many people discover it gradually while working and serving.

Q: Doreen loves solving puzzles, teaches patiently and sees few girls learning computing in her area. Which purpose sentence fits best?
- I want to be rich quickly
- I want to be famous online
* I want to use my computer skills and patience to help girls in my community learn technology
- I want to avoid all difficulty
= It links her strengths, a community need and the people she wants to serve.

Q: What is a blind spot?
- A weakness that everyone agrees on
* Something others see about you that you do not
- A fear of the dark
- A skill you never use
= Blind spots exist because we know our intentions, while others see our actions.

Q: Dan believes he is fast, yet customers complain about delays because he promises ten-minute repairs that take an hour. What is the best action?
- Ignore complaints
- Blame customers for being impatient
* Give realistic times and message customers when jobs are ready
- Close the stall
= Listening to feedback and changing behaviour fixes the problem and builds trust.

Q: Brian left out a hard year from his loan application. A group member advised him to include it. What is the best reason?
- Hard years should always be shared in public
* Telling it truthfully and showing what he learned can build trust
- Nobody checks stories anyway
- It makes the application longer
= Honest accounts of difficulty, with lessons learned, show maturity and discipline.

Q: A memory from your lifeline is too painful to write about. What should you do?
- Force yourself to write all of it tonight
* Skip it for now and speak with a counsellor, elder or trusted adult
- Pretend it never happened
- Post it online
= You do not have to face painful memories alone. Support from a trusted person is a sign of wisdom.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Your world: family, community and support',
            'summary' => 'See how family, community and culture shape you, learn to use feedback and mentors well, and know when and where to get support.',
            'lessons' => [
                [
                    'title'   => 'Family, community and culture: the influences around you',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to name the main influences in your life, respect them and still think for yourself. This matters because family expectations, religion, tribe, school and neighbours shape what you believe is possible, and understanding these influences helps you make choices without needless conflict or guilt.</p>

<h3>Where influences come from</h3>
<p>No one grows up alone. Your <strong>environment</strong> includes your family, friends, school, church or mosque, neighbourhood, local leaders, culture, language and the media you watch. Each one gives you messages, such as "a good child becomes a doctor", "girls should marry early", "business is risky", "respect your elders", "money is hard to get". Some messages are wise, some are outdated, and some are simply fears from the past. Your task is to notice them, not to throw them all away.</p>

<h3>Sort the messages</h3>
<table>
<thead><tr><th>Message I received</th><th>Keep, adjust or question?</th><th>Reason</th></tr></thead>
<tbody>
<tr><td>Respect elders</td><td>Keep</td><td>It builds trust and wisdom in the community</td></tr>
<tr><td>Only government jobs are safe</td><td>Question</td><td>Few jobs exist, and many safe incomes now come from skills and small business</td></tr>
<tr><td>Girls need less education</td><td>Question</td><td>Education benefits everyone and families</td></tr>
<tr><td>Share with relatives in need</td><td>Adjust</td><td>Keep the spirit but plan how much you can give without hurting your goals</td></tr>
</tbody>
</table>

<h3>Respecting culture while choosing your path</h3>
<ol>
<li><strong>Listen first.</strong> Ask your parents or elders why they hold a view. Often it comes from love and fear for your safety.</li>
<li><strong>Explain with respect.</strong> Say what you have learned about yourself and why the choice fits your values.</li>
<li><strong>Offer a test.</strong> "Let me try this for six months and we will review." A small trial is easier to accept than a big jump.</li>
<li><strong>Seek a go-between.</strong> An aunt, uncle, teacher, pastor or respected family friend may help the family understand.</li>
<li><strong>Stay safe.</strong> Culture should never be used to justify harm. If anyone threatens, forces or hurts you, tell a trusted adult, teacher, religious leader or local support service.</li>
</ol>

<h3>Worked example: Namusoke in Kampala</h3>
<p>Namusoke's father wanted her to study law, as he had. She loved graphic design. She listened to him first and learned that he feared she would struggle to find work. She showed him three design jobs she had already been paid for, and a list of local companies that hire designers. She proposed to study a degree in communication with design electives and run a small side business. He agreed and later introduced her to two clients. By listening and offering evidence, she kept the relationship and her path.</p>

<h3>Case scenario</h3>
<p>Alex, a Senior 6 leaver in Gulu, wanted to learn motorcycle mechanics but his family believed the trade was for people who failed school. He felt ashamed and nearly dropped the idea. A respected uncle who owned a garage invited Alex to visit his workshop and explained how much customers pay for skilled repair. The family visited and saw the busy garage. They agreed to let Alex take a short technical course. The lesson: respect and a trusted go-between often move a family further than argument.</p>

<blockquote><p><strong>Quick check:</strong> Should you throw away every message you received from your family?</p><p>Answer: No, you should sort them into keep, adjust or question, because many carry wisdom while some are outdated.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is a good way to introduce a new career idea to worried parents?</p><p>Answer: Listen to their worry, show evidence, and offer a time-limited trial, because this respects them while showing your plan.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List five messages you received about school, money, work and success.</li>
<li>Mark each message: keep, adjust or question, and write the reason.</li>
<li>Choose one family expectation you disagree with and write how you would explain your view respectfully.</li>
<li>Name one person who could help explain your choices to your family.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Family, faith, school, community and culture all shape your beliefs about what is possible.</li>
<li>Sort messages into keep, adjust or question.</li>
<li>Listen first, explain with evidence and offer a trial.</li>
<li>Never accept harm in the name of culture; tell a trusted adult.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Feedback, mentors and role models',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to ask for useful feedback, receive it calmly, and find and use a mentor. This matters because you cannot see everything about yourself, and one wise person who is a few steps ahead of you can save you years of trial and error.</p>

<h3>Getting good feedback</h3>
<p>General questions such as "Am I okay?" bring general answers such as "Yes, you are fine." Better questions are specific and about behaviour:</p>
<ul>
<li>"When I presented yesterday, what was clear and what was confusing?"</li>
<li>"What is one thing you think I should stop doing, and one thing I should keep?"</li>
<li>"If you were me, what would you practise first?"</li>
</ul>
<p>Choose people who know your work, want you to grow and will be honest kindly. A friend who only flatters you and a person who always criticises are both poor sources. Ask three or four different people and look for repeated points.</p>

<h3>Receiving feedback</h3>
<ol>
<li><strong>Listen fully.</strong> Do not interrupt or explain yet.</li>
<li><strong>Ask for an example.</strong> "Can you tell me when you saw this?"</li>
<li><strong>Say thank you.</strong> It is a gift even if it stings.</li>
<li><strong>Wait a day.</strong> Then decide what is useful and what is not. You do not have to accept everything. Feedback about your behaviour is useful; cruel comments about who you are, or remarks made out of jealousy, are not.</li>
<li><strong>Act on one point.</strong> Then go back and tell the person what you changed.</li>
</ol>

<h3>Mentors and role models</h3>
<p>A <strong>mentor</strong> is a person with more experience who gives you advice, encouragement and honest feedback over time. A <strong>role model</strong> is someone whose life or work you admire, even if you never meet them. Mentors can be a teacher, an uncle, an older student, a business owner or a community leader. They do not need to be famous.</p>
<p>How to approach one: choose someone whose work you respect, send a short polite message saying who you are, what you admire and what specific help you want, and ask for 20 minutes. Prepare three questions. After the meeting, thank them and later share your progress. Respect their time, and never pay anyone who promises to "fix your life" for a fee or asks for something that makes you uncomfortable. If anyone behaves badly toward you, leave and tell another trusted adult.</p>

<h3>Worked example: Florence, a young mother</h3>
<p>Florence in Wakiso wanted to grow her home bakery but did not know about pricing. She asked a woman who ran a successful cake stall: "Could I ask you three questions about pricing for 20 minutes?" The woman taught her to add up all costs, including gas and transport, before setting a price. Florence found she had been selling some cakes at a loss. She raised prices slightly, kept her regular customers, and sent the mentor a thank-you message with her first month's results.</p>

<h3>Case scenario</h3>
<p>Henry, a second-year university student in Kampala, asked his lecturer for feedback on a business plan and received a long list of faults. He felt crushed and wanted to quit. Two days later he reread it and saw three points that were fair and two that did not apply. He corrected the three, thanked the lecturer and returned a week later with a stronger plan, which won a small campus grant. The lesson: feedback hurts in the moment, but waiting, sorting and acting on it can change the result.</p>

<blockquote><p><strong>Quick check:</strong> Why are specific questions better when asking for feedback?</p><p>Answer: They lead to clear, useful answers about behaviour, whereas general questions usually get polite replies.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What should you do before deciding what to do with harsh feedback?</p><p>Answer: Wait a little, then sort useful points from unfair ones, because strong feelings can hide valuable advice.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write three specific feedback questions about something you did recently.</li>
<li>Ask two different people and note any points that repeat.</li>
<li>Choose one point to act on this week and tell the person what you changed.</li>
<li>Write a short message to a possible mentor asking for 20 minutes with three questions.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Ask specific questions of people who are honest and kind.</li>
<li>Listen, ask for examples, thank them, wait, then decide.</li>
<li>A mentor offers advice over time; a role model inspires from a distance.</li>
<li>Stay safe: tell a trusted adult if any mentor makes you uncomfortable.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Pressure, comparison and getting support when life is hard',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to recognise common pressures on young people, manage comparison, and know when and how to ask for help. This matters because it is hard to discover yourself when you are tired, worried or hurting, and asking for help early is a sign of good sense.</p>

<h3>Common pressures</h3>
<p>Young people in East Africa often carry real pressure: school fees, unemployment, family responsibilities, expectations to marry or support relatives, exam results and the picture of perfect lives on social media. It is normal to feel stressed, sad or worried at times. These feelings do not mean you are weak.</p>

<h3>The comparison trap</h3>
<p>Social media shows other people's best moments: new phones, trips, wedding photos, "congratulations" posts. It rarely shows their debts, doubts or failures. Comparing your ordinary day with someone's highlight reel is unfair to yourself. Try these habits:</p>
<ol>
<li>Compare yourself with <strong>who you were last year</strong>, not with a stranger online.</li>
<li>Limit scrolling at times when you feel low, and spend that time on a task or a walk.</li>
<li>Follow people who teach you something and mute accounts that leave you feeling small.</li>
<li>Write three things you did this week that you are glad of.</li>
</ol>

<h3>Everyday ways to care for yourself</h3>
<ul>
<li>Sleep enough, eat regular meals and move your body, even a short walk.</li>
<li>Talk to a friend or relative you trust instead of keeping everything inside.</li>
<li>Break large problems into small steps you can do today.</li>
<li>Pray, reflect or spend quiet time in the way that suits your faith and culture.</li>
</ul>

<h3>When to get help</h3>
<p>This course is for learning and reflection. It is not medical advice and cannot diagnose or treat anything. Talk to a school counsellor, a health worker, a religious leader you trust or a trusted adult if you notice that:</p>
<ul>
<li>sadness, worry, anger or fear lasts for weeks and stops you from studying, working, eating or sleeping;</li>
<li>you feel hopeless, or you have thoughts of harming yourself or ending your life;</li>
<li>someone is hurting, threatening or abusing you, or pressuring you to do something unsafe;</li>
<li>you use alcohol or drugs to cope.</li>
</ul>
<p>If you or someone near you is in immediate danger, contact the local emergency number, go to the nearest health facility or tell a trusted adult right now. In many places, health centres and schools have people trained to listen. Asking for help is a strong, responsible choice and many people have walked the same road.</p>

<h3>Worked example: Joel in Kampala</h3>
<p>Joel, a final-year student, felt behind when classmates posted internship announcements. He wrote a list of what he had done: two group projects, a part-time job and a CV workshop. He then unfollowed three accounts that made him anxious and asked a lecturer for advice on internships. Within a month he had two interviews. He still felt nervous but stopped treating other people's posts as a score against him.</p>

<h3>Case scenario</h3>
<p>Sharon, a young mother in Mbarara, stopped sleeping and felt too low to run her stall after her business failed. She believed she should handle it alone. Her sister noticed that Sharon was no longer eating properly and persuaded her to visit the health centre, where a nurse listened and referred her to a counsellor. With support and a small repayment plan, Sharon reopened her stall on smaller stock. The lesson: telling someone early gave Sharon more strength than silence did.</p>

<blockquote><p><strong>Quick check:</strong> Why is comparing yourself with people on social media unfair?</p><p>Answer: You see their best moments but not their struggles, so the comparison is not accurate.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Name two signs that you should talk to a counsellor or trusted adult.</p><p>Answer: For example, sadness lasting weeks that stops daily life, or thoughts of harming yourself; any threat or abuse is also a reason to seek help.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List three pressures you feel now and one small step for each.</li>
<li>Write three things you did this week that you are glad about.</li>
<li>Choose two people (a trusted adult and a friend) you could talk to if life became heavy.</li>
<li>Write the name and place of a school counsellor, health centre or leader you could visit.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Pressure and stress are normal; comparison with online highlights is unfair.</li>
<li>Compare yourself with your own past, and look after sleep, food and movement.</li>
<li>This course does not replace professional help or diagnose anything.</li>
<li>Seek a counsellor, health worker or trusted adult early, and immediately in danger.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the best first step when your family expects a career you do not want?
- Ignore them completely
* Listen to why they want it, then explain your view respectfully with evidence
- Argue until they give up
- Pretend to agree and do nothing
= Parents often act from love and fear. Listening first and showing evidence makes agreement more likely.

Q: When you sort family messages, what are the three options?
- Obey, reject, forget
* Keep, adjust or question
- Win, lose, draw
- Believe, hate, copy
= Sorting messages lets you keep wise ones, adapt useful ones and question outdated ones.

Q: Which of these are good ways to ask for feedback? (choose all that apply)
* "What is one thing I should keep doing and one thing I should stop?"
- "Am I okay?"
* Asking several people and looking for repeated points
* Asking people who know your work and are honest kindly
= Specific questions to informed, honest people give useful answers. General questions bring polite replies.

Q: Henry receives a long list of faults on his business plan and feels crushed. What is the wisest next move?
- Delete the plan
* Wait a day or two, sort fair points from unfair ones, correct the useful ones and thank the lecturer
- Argue with the lecturer immediately
- Never ask for feedback again
= Strong feelings can hide valuable advice. Sorting and acting on useful points improves the work.

Q: How should you approach a possible mentor?
- Demand free money from them
- Ask them to solve all your problems
* Send a short polite message, say what you admire and ask for 20 minutes with a few clear questions
- Wait for them to notice you
= A respectful, specific request is easy to say yes to, and it shows that you value their time.

Q: Which is a sign that you should talk to a counsellor or trusted adult?
- You felt sad for one afternoon after a bad exam
* Sadness or worry lasting weeks has stopped you from eating, sleeping or studying
- You are slightly nervous before a presentation
- You disagree with a friend
= Short-lived feelings are normal. Persistent distress that disrupts life is a reason to seek support.

Q: Sharon, a young mother, is not eating or sleeping and feels she must cope alone after her stall failed. What is the best advice?
- Keep silent so nobody worries
- Wait a few years to see if it passes
* Visit a health centre or counsellor and talk to a trusted person early
- Compare herself with successful traders online
= Early support helps people recover and rebuild. Silence often makes problems heavier.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Choosing a direction',
            'summary' => 'Turn what you have learned into options for studies, work or business, test them cheaply and decide in a way that fits your values.',
            'lessons' => [
                [
                    'title'   => 'From self-knowledge to options: studies, work or business',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to turn your strengths, interests and values into a short list of real options for study, work or business. This matters because self-knowledge only helps when it leads to action, and a clear shortlist is far easier to explore than the vague question "What should I do with my life?"</p>

<h3>Gather what you know</h3>
<p>Put your earlier notes on one page: top five strengths, interests and energy sources, top five ranked values, your purpose sentence, and the needs around you. Add practical facts too: money available, family duties, where you live, time you can give and any limits such as health. Honesty about limits is not defeat; it helps you pick a path you can actually walk.</p>

<h3>Three broad roads</h3>
<table>
<thead><tr><th>Road</th><th>What it means</th><th>Suits you when</th></tr></thead>
<tbody>
<tr><td>Further study</td><td>University, college, vocational or technical training, apprenticeship</td><td>The work you want requires a qualification or specific skills</td></tr>
<tr><td>Employment</td><td>Working for an employer, a company, school, NGO or government</td><td>You value steady income, learning from others and team work</td></tr>
<tr><td>Business or self-employment</td><td>Selling a product or service, farming, freelancing, a family enterprise</td><td>You value independence, can handle uncertainty and have a real customer need to meet</td></tr>
</tbody>
</table>
<p>These roads can be combined: study part-time while working, or run a small business beside a job. Many people use more than one road over a lifetime.</p>

<h3>Build a shortlist in four steps</h3>
<ol>
<li><strong>Brainstorm.</strong> Write ten possible directions without judging them.</li>
<li><strong>Match.</strong> Next to each, mark how well it matches your strengths, interests and values: strong, partly, weak.</li>
<li><strong>Check reality.</strong> Ask: Is there a need or demand? Can I afford the first step? Is it legal and safe?</li>
<li><strong>Keep three.</strong> Choose the three strongest options to explore in the next lessons.</li>
</ol>

<h3>Worked example: Akello in Gulu</h3>
<p>Akello's strengths are speaking, selling and persistence. Her values: family, honesty, learning. Her purpose: help young women earn their own income. Her ten ideas include teaching, tailoring, a market stall, a business diploma, a savings group support role and agriculture marketing. After matching and reality checks, her three options are a diploma in business studies, a part-time job with a cooperative that sells farm produce and a small stall selling household goods. She has a shortlist she can test, instead of a confusing cloud of ideas.</p>

<h3>Case scenario</h3>
<p>Ivan, a Senior 6 leaver in Kampala, wrote only one option: medicine, because his family wanted it. His points were not high enough and he felt like a failure. A teacher asked him to list what he enjoyed about medicine. He answered: helping sick people and science. They brainstormed nursing, laboratory technology, public health, pharmacy technician training and community health work. Each needed a lower entry grade and served people in the same way. Ivan chose a laboratory technology diploma, kept a path to a degree later and explained his plan to his family. The lesson: behind a closed door there are usually several open ones that fit the same values.</p>

<blockquote><p><strong>Quick check:</strong> Why should you list practical limits such as money and family duties?</p><p>Answer: Honest limits help you choose a path you can actually follow and prevent plans that collapse in the first month.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Can you combine study, work and business?</p><p>Answer: Yes, many people study part-time while working or run a small business beside a job, so the roads are not exclusive.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Put your strengths, interests, values, purpose sentence and practical limits on one page.</li>
<li>Brainstorm ten possible directions and mark each strong, partly or weak match.</li>
<li>Check demand, cost and safety for the strong ones.</li>
<li>Circle three options to explore this month.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Combine self-knowledge and practical facts before choosing.</li>
<li>Study, employment and business are three roads that can be combined.</li>
<li>Brainstorm many options, match, check reality and keep three.</li>
<li>If one door closes, other options often serve the same purpose.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Testing your options cheaply and learning from real life',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to test a career or business idea with small, low-cost experiments before committing years or money. This matters because many young people lose fees, savings or time on choices they never tried, and a small test can show within weeks whether an option fits you.</p>

<h3>Why test first</h3>
<p>Reading about a job and doing it are very different. A small experiment gives real information: do I enjoy the daily tasks? Do people need this? Can I earn enough? Will my family support it? It is also safer. A test costing UGX 20,000 and two weekends is much better than a loan or a year of fees spent on a guess.</p>

<h3>Six cheap ways to test</h3>
<ol>
<li><strong>Talk to people.</strong> Ask two or three people doing the work: What does a normal day look like? What do you dislike? What would you do differently? How did you start?</li>
<li><strong>Shadow or visit.</strong> Spend a day or part of a day watching at a workplace, clinic, school, garage, farm or shop, with permission.</li>
<li><strong>Volunteer or intern.</strong> Offer a few hours a week to an organisation in the field.</li>
<li><strong>Try a mini task.</strong> Design one poster, repair one device, tutor one student, cook for ten customers, or sell a few items on a trial day.</li>
<li><strong>Take a short course.</strong> A free or low-cost short course or online lesson shows whether the subject holds your interest.</li>
<li><strong>Research.</strong> Check entry requirements, costs and demand from official college pages, local offices or people in the industry.</li>
</ol>

<h3>Record what you learn</h3>
<table>
<thead><tr><th>Option</th><th>What I tried</th><th>Energy (1 to 5)</th><th>Fit with values (1 to 5)</th><th>Real demand or pay</th></tr></thead>
<tbody>
<tr><td>Catering</td><td>Cooked lunch for 15 neighbours</td><td>5</td><td>4</td><td>Six asked to order again</td></tr>
<tr><td>Tailoring</td><td>Two-day visit to a workshop</td><td>2</td><td>3</td><td>Many tailors in the area</td></tr>
</tbody>
</table>
<p>Scores are for thinking, not science. What matters is that you gather real evidence and look honestly at it. A test that fails is a success, because it saves you from a bigger mistake.</p>

<h3>Worked example: Naki the young mother</h3>
<p>Naki in Mukono wanted to start a catering business. Before buying equipment, she cooked 20 packed lunches with UGX 60,000 and offered them to office workers near a nearby building. She sold all 20, noted which dishes sold first, found that delivery took too long and asked for feedback. She earned a small profit and a list of seven regular customers. Only then did she buy a second large pot and plan a weekly schedule.</p>

<h3>Case scenario</h3>
<p>Moses, a boda-boda rider in Gulu, dreamed of opening a motorcycle spare parts shop and was ready to borrow UGX 5 million. A mentor suggested he first spend two Saturdays at a parts shop and track what customers asked for. Moses learned that most sales were small items and that credit customers rarely paid on time, which made the business harder than he thought. He started instead by selling the five fastest items from a small stand beside his stage, funded by savings. After six months he knew the market, had paying customers and could borrow a smaller amount safely. The lesson: a small test taught him more than months of imagining.</p>

<blockquote><p><strong>Quick check:</strong> Why is a failed test still useful?</p><p>Answer: It tells you cheaply that an option does not fit, saving you from a larger loss of money or years.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Name two low-cost ways to test a career idea.</p><p>Answer: For example, talk to people doing the work, shadow them for a day, volunteer, try a mini task or take a short course.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose one of your three options and decide on a test that costs little or nothing.</li>
<li>Ask two people in that field three questions each.</li>
<li>Do your test within two weeks and record energy, fit with values and real demand.</li>
<li>Decide: continue, adjust or drop, and write one reason.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Test before you commit money or years.</li>
<li>Talk, shadow, volunteer, try a mini task, take a short course or research.</li>
<li>Record what you learn so that you judge by evidence, not hope.</li>
<li>A failed test is cheap information, not failure.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Making decisions that fit your values',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use a simple method to make an important decision calmly, check it against your values and handle the fear of choosing wrongly. This matters because many people delay choices for years or let others decide for them, and both bring regret.</p>

<h3>Why decisions feel hard</h3>
<p>Decisions feel hard when you want to please many people, when money is tight, when the future is uncertain and when you fear regret. It helps to remember three facts. First, few choices are permanent; most can be adjusted. Second, not deciding is also a decision, and usually the one that others take advantage of. Third, you will never have perfect information, so aim for a good, well-thought choice, not a perfect one.</p>

<h3>A six-step decision method</h3>
<ol>
<li><strong>State the decision clearly.</strong> "Which course should I apply for this year?"</li>
<li><strong>List two to four real options</strong>, including "wait one year and work" if that is real.</li>
<li><strong>Gather facts.</strong> Costs, entry requirements, time, risks, likely income, who can advise.</li>
<li><strong>Score against your values.</strong> Take your top five values and give each option a score from 1 to 5 for how well it respects each value.</li>
<li><strong>Imagine the worst and best cases.</strong> Could I recover if it goes badly? What would I do?</li>
<li><strong>Decide, set a review date, and act.</strong> Write the decision and when you will review it.</li>
</ol>

<h3>A simple values scoring table</h3>
<table>
<thead><tr><th>Value</th><th>Option A: Business diploma</th><th>Option B: Work at cooperative</th><th>Option C: Start a stall</th></tr></thead>
<tbody>
<tr><td>Family (support)</td><td>3</td><td>5</td><td>3</td></tr>
<tr><td>Learning</td><td>5</td><td>4</td><td>3</td></tr>
<tr><td>Honesty</td><td>4</td><td>4</td><td>4</td></tr>
<tr><td>Independence</td><td>3</td><td>2</td><td>5</td></tr>
<tr><td><strong>Total</strong></td><td><strong>15</strong></td><td><strong>15</strong></td><td><strong>15</strong></td></tr>
</tbody>
</table>
<p>A tie is useful too. It tells you the options are close, so you can weigh your top-ranked value more heavily, or choose the one that keeps the most doors open and is easiest to reverse.</p>

<h3>Worked example: Doreen</h3>
<p>Doreen in Kampala had two offers: a paid internship at a software company and a place on a one-year teacher training programme. Her top values were learning, service and family. The internship scored high on learning and income, the training scored high on service. She weighed service most heavily because of her purpose sentence. She asked the company whether she could start the internship six months later; they agreed. She did the training first, and the internship later with a stronger plan to teach technology.</p>

<h3>Case scenario</h3>
<p>Beatrice, a young mother in Jinja, was offered a higher-paying job selling on commission at a company that expected employees to push customers into loans they did not need. Her top values were honesty and family. The job paid UGX 600,000 more a month. She used the six-step method, imagined her worst case (feeling ashamed and leaving after a year) and her best case (money but guilt), and spoke with her sister. She declined and instead took a lower-paid administrative post with a reputable clinic, adding weekend tailoring orders to cover costs. The lesson: values give you a clear reason to say no, and a plan helps you carry the cost of that choice.</p>

<blockquote><p><strong>Quick check:</strong> Is "not deciding" a neutral choice?</p><p>Answer: No, because delay is also a decision and often allows circumstances or other people to decide for you.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What can you do when two options score the same against your values?</p><p>Answer: Give more weight to your top value, or pick the one that keeps more doors open and is easier to reverse.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write one real decision you must make in the next six months.</li>
<li>List two to four options and gather three facts about each.</li>
<li>Score each option from 1 to 5 against your top five values.</li>
<li>Write your decision, one possible worst case with a recovery plan, and a review date.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Few decisions are permanent, and delay is a choice too.</li>
<li>Use a clear method: define, list, gather facts, score by values, test worst case, decide.</li>
<li>Values give you a reason to say yes or no with confidence.</li>
<li>Set a review date so the decision can adjust as you learn.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Why combine your self-knowledge with practical facts such as money and family duties?
- Practical facts do not matter for dreams
* So that you choose a path you can really follow
- So that you can avoid thinking about your values
- So that others will approve
= A good direction fits both who you are and the real situation you live in.

Q: Which are the three broad roads described in this module?
- Luck, favour and chance
* Further study, employment, and business or self-employment
- School, church and market
- Hobby, holiday and rest
= These roads can be combined, for example studying part-time while working or running a small business.

Q: Which are good low-cost ways to test a career idea? (choose all that apply)
* Talking to two or three people who do the work
* Spending a day shadowing someone at their workplace
- Taking a large loan to start immediately
* Doing a small trial task such as one poster or ten lunches
= Small tests give real evidence cheaply. A large loan commits you before you know anything.

Q: Moses wants to borrow UGX 5 million for a spare parts shop. What is the wisest next step?
- Borrow at once so he does not lose the idea
* Spend a few Saturdays in a parts shop and start with a small stand to learn the market
- Stop thinking about business forever
- Copy another shop exactly
= A small test reveals customers, credit risks and demand before heavy debt.

Q: Ivan's points are too low for medicine, which was his only plan. What is the best way forward?
- Give up on helping people
* List what he enjoyed about medicine and explore related options such as laboratory or community health training
- Repeat the same plan without changes
- Choose any course at random
= The values behind a closed door, such as helping people and science, can often be served through other paths.

Q: What does a tie in your values scoring table tell you?
- You made a mistake and must start again
* The options are close, so you can weigh your top value more or choose the one that is easiest to reverse
- You should flip a coin and forget your values
- The facts are not important
= A tie is information. Use ranking and reversibility to break it.

Q: Beatrice is offered a higher-paying sales job that pushes customers into loans they do not need, but she values honesty and family. What fits this module best?
- Accept; money is the only value
* Use a decision method, check it against her values, and decline if it breaks her top value, with a plan for the lower income
- Accept and hide the details from her family
- Make no decision and hope the offer disappears
= Values give a clear reason to say no, and a plan helps her carry the cost.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 6
        [
            'title'   => 'Module 6: Your personal profile and 12-month direction',
            'summary' => 'Bring everything together in a one-page personal profile, a 12-month direction statement and habits to review your progress.',
            'lessons' => [
                [
                    'title'   => 'Writing your one-page personal profile',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a one-page personal profile that sums up what you have discovered about yourself. This matters because writing things down makes them clear and gives you a page to return to when you doubt yourself, make a big decision or need to explain your plans to family, a mentor or an employer.</p>

<h3>What the profile is</h3>
<p>Your <strong>personal profile</strong> is a private, honest summary of who you are today. It is not a CV, a social media post or a promise. It is for you, and you may choose to share parts with people you trust. It will change as you grow, so date it and update it once or twice a year.</p>

<h3>The nine sections</h3>
<table>
<thead><tr><th>Section</th><th>What to write</th></tr></thead>
<tbody>
<tr><td>1. My strengths</td><td>Top three to five, each with one proof sentence</td></tr>
<tr><td>2. My interests and energy</td><td>Activities that give me energy; what drains me</td></tr>
<tr><td>3. My top values</td><td>Five ranked values, each with a short sentence on how I live it</td></tr>
<tr><td>4. My beliefs to grow</td><td>One limiting belief and the truer belief I am choosing</td></tr>
<tr><td>5. How I work with others</td><td>My style on the four scales, and how I like to be treated in a team</td></tr>
<tr><td>6. My story</td><td>Three sentences: where I started, my turning point, where I am heading</td></tr>
<tr><td>7. My purpose</td><td>One sentence: I want to use my ___ to help ___ with ___</td></tr>
<tr><td>8. My growth area</td><td>One weakness or blind spot and my small step</td></tr>
<tr><td>9. My support</td><td>Three people who can advise me, including one trusted adult</td></tr>
</tbody>
</table>

<h3>Tips for writing it well</h3>
<ol>
<li>Use plain words and short sentences, as if telling a friend.</li>
<li>Use evidence: numbers, examples and names of projects, not only adjectives.</li>
<li>Be kind and truthful at the same time. Avoid both boasting and self-insult.</li>
<li>Keep it to one page. If something is not important, leave it out.</li>
<li>Read it aloud. If a line sounds false, rewrite it.</li>
</ol>

<h3>Worked example: part of Moses' profile</h3>
<pre><code>Strengths: organising (ran the faculty social for 200 students),
listening (first-years often come to me for advice), persuasion.
Energy: people, planning, events. Drains: working alone on long reports.
Top values: 1 Honesty, 2 Family, 3 Learning, 4 Service, 5 Freedom.
Belief to grow: from "I am not good at numbers" to "Numbers are hard for me
now, and I improve with practice".
Purpose: I want to use my organising and people skills to help small
businesses in Kampala reach more customers.</code></pre>
<p>Notice how each line is short, specific and checkable.</p>

<h3>Case scenario</h3>
<p>Judith, a Senior 6 leaver in Gulu, wrote her profile and read it to her aunt, a nurse. Her aunt pointed out that Judith had left out her biggest proof: she had run the school health club and taught younger pupils about handwashing. Judith had thought it was too ordinary to include. After she added it, she saw that her purpose sentence about health education had solid evidence behind it. She used the profile to explain her wish to study public health to her father, who then supported her application. The lesson: another person can help you notice proof that you overlook, and a written profile gives you words to explain your choices.</p>

<blockquote><p><strong>Quick check:</strong> Is your personal profile the same as a CV?</p><p>Answer: No, because it is a private, honest summary for your own use, while a CV is written for employers.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why should the profile use evidence, not only adjectives?</p><p>Answer: Specific facts and examples can be checked and remembered, while adjectives such as hardworking say very little.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Gather your notes from the earlier lessons into one place.</li>
<li>Write the nine sections in plain words, one to three lines each.</li>
<li>Read it aloud and change any line that sounds false.</li>
<li>Show it to one trusted person, ask what you missed, and add it. Write today's date at the top.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A personal profile is a private, honest one-page summary of who you are today.</li>
<li>Cover strengths, energy, values, beliefs, working style, story, purpose, growth area and support.</li>
<li>Use evidence, plain words and kind honesty.</li>
<li>Date it and update it once or twice a year.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Your 12-month direction statement',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a clear 12-month direction statement with goals, small steps and ways to check your progress. This matters because a direction is only useful when it turns into actions in the next few months, and a one-year horizon is short enough to feel real and long enough to change something.</p>

<h3>Direction, not a fixed destination</h3>
<p>You do not need to know your whole life. A <strong>direction statement</strong> says where you intend to move over the next 12 months, based on your profile. It is different from a wish. A wish is "I want to be successful". A direction statement is specific: "Over the next 12 months I will ... so that I ...". It may change when you learn something new, and that is normal.</p>

<h3>The structure</h3>
<ol>
<li><strong>Focus sentence.</strong> "In the next 12 months I will move toward ___ because it fits my strengths and values of ___."</li>
<li><strong>Three goals.</strong> Choose one each for learning or skills, for earning or studying, and for people or health. Make each specific and measurable.</li>
<li><strong>Quarterly steps.</strong> For each quarter (months 1 to 3, 4 to 6, 7 to 9, 10 to 12), write one or two actions.</li>
<li><strong>Support and resources.</strong> Who will help, what money or time is needed, what you will not do.</li>
<li><strong>Checks.</strong> How and when you will review, and what would make you change the plan.</li>
</ol>

<h3>Make goals specific</h3>
<table>
<thead><tr><th>Vague goal</th><th>Specific goal</th></tr></thead>
<tbody>
<tr><td>Get better at business</td><td>Complete a free short course on bookkeeping by month 3 and record every sale and expense for 9 months</td></tr>
<tr><td>Save money</td><td>Save UGX 20,000 every week for 40 weeks in a separate wallet</td></tr>
<tr><td>Improve my English</td><td>Read one page aloud daily and write a short paragraph twice a week</td></tr>
</tbody>
</table>

<h3>Worked example: Samuel the boda-boda rider</h3>
<p>Samuel in Jinja dreams of owning two motorcycles and training younger riders in safe riding. His direction statement: "In the next 12 months I will move toward becoming a trusted rider-owner because I am reliable, I value honesty and family, and I enjoy teaching." His three goals: (1) skills: finish a road safety short course by month 4; (2) earning: save UGX 1.5 million toward a second motorcycle by month 12, with UGX 30,000 weekly; (3) people: sign up 15 regular customers and give two hours a month to coach a new rider. Quarterly steps are listed under each goal. He will review on the first Sunday of each month with his brother and will pause the plan only if his family faces a medical emergency.</p>

<h3>Case scenario</h3>
<p>Rose, a university student in Kampala, wrote a plan with fifteen goals and abandoned it within three weeks. Her mentor asked her to cut it to three goals and one action for the next 30 days. Rose kept: finish her final-year project early, apply for two internships and exercise three times a week. She tracked the actions on a paper chart, and after three months she had applied for six internships and received two interviews. The lesson: fewer goals with clear small steps are more likely to be done than a long list.</p>

<blockquote><p><strong>Quick check:</strong> How many main goals does this lesson suggest for a 12-month direction statement?</p><p>Answer: Three, covering skills, earning or study, and people or health, because a short list is easier to follow.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why should a direction statement include a review plan?</p><p>Answer: Life changes and you learn new things, so regular checks let you adjust instead of quitting or sticking to a plan that no longer fits.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write your focus sentence using the structure in this lesson.</li>
<li>Choose three specific, measurable goals for the year.</li>
<li>Write one or two actions for each quarter, and the first action you will do this week.</li>
<li>Name your supporters, your review date each month and what would make you change the plan.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A direction statement is a specific plan for 12 months, based on your profile.</li>
<li>Use a focus sentence, three goals, quarterly steps, support and review checks.</li>
<li>Make goals specific and measurable.</li>
<li>Fewer goals with clear small steps beat a long list.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Habits, review and staying true to yourself',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build small habits that keep you moving, review your direction regularly and stay true to yourself when pressure comes. This matters because knowing yourself is not a one-day project, and people who review regularly adjust sooner, waste less and stay confident through setbacks.</p>

<h3>Small habits beat big promises</h3>
<p>A habit is something you do so often that it needs little effort. Big promises such as "I will study four hours every day" usually break by the second week. Small habits last. The trick is to tie a new action to something you already do: after I drink tea in the morning, I will read one page; after I close my stall, I will write three lines about my day.</p>
<table>
<thead><tr><th>Habit</th><th>Time</th><th>Linked to</th></tr></thead>
<tbody>
<tr><td>Write three lines about the day</td><td>3 minutes</td><td>Before sleeping</td></tr>
<tr><td>Practise one skill (typing, speaking, bookkeeping)</td><td>15 minutes</td><td>After breakfast</td></tr>
<tr><td>Move UGX 2,000 to savings</td><td>1 minute</td><td>After each sale</td></tr>
<tr><td>Message one person you want to learn from</td><td>5 minutes</td><td>Every Friday</td></tr>
</tbody>
</table>

<h3>The monthly review</h3>
<p>Once a month, spend 30 minutes with your profile and direction statement. Ask five questions:</p>
<ol>
<li>What did I do this month that moved me toward my goals?</li>
<li>What got in the way, and what can I change?</li>
<li>What did I learn about my strengths, interests or values?</li>
<li>Do my goals still fit, or has something changed?</li>
<li>What is my one priority for next month?</li>
</ol>
<p>Be patient: progress is usually slow, uneven and sometimes backward. A missed week is a signal to restart, not a reason to quit.</p>

<h3>Staying true to yourself</h3>
<p>Pressure to follow the crowd will come from friends, relatives, social media or money. Three ideas help. First, return to your top values and ask whether a choice respects them. Second, give yourself time: "Let me think and answer tomorrow." Third, keep a few trusted people who tell you the truth. Staying true to yourself does not mean ignoring others; it means listening, then choosing with your own head and heart. If you feel unable to cope, talk to a counsellor or trusted adult.</p>

<h3>Worked example: Naki's review</h3>
<p>After three months, Naki in Mukono reviewed her catering plan. She had done well on cooking but missed her savings goal four weeks out of twelve. Her review showed that she forgot to save after weekday orders. She tied the saving habit to closing her phone calculator after each sale and moved the saving amount to UGX 3,000. Her second review showed 11 of 12 weeks saved.</p>

<h3>Case scenario</h3>
<p>Isaac, a Senior 6 leaver in Gulu, was making steady progress on his solar installation course until friends invited him to join a quick-money betting scheme that promised big returns. He felt left behind as friends boasted. He returned to his profile, where honesty and family were his top values, and remembered that two relatives had lost money in similar schemes. He told his friends he would pass and chose to double his practice hours instead. Six months later he had a small paid installation job. The lesson: your written values give you a firm place to stand when the crowd moves quickly.</p>

<blockquote><p><strong>Quick check:</strong> What is a good way to make a new habit stick?</p><p>Answer: Make it small and attach it to something you already do each day, so it needs little effort.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What should you do after a missed week?</p><p>Answer: Treat it as a signal to restart and adjust, because progress is uneven and one missed week is not failure.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose two tiny habits and link each to something you already do daily.</li>
<li>Put a date in your phone for a 30-minute monthly review.</li>
<li>Write the five review questions on the back of your profile page.</li>
<li>Name one situation where you might be pressured and write your reply in advance.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Small habits tied to daily routines last longer than big promises.</li>
<li>A monthly review keeps your profile and plan alive and honest.</li>
<li>Progress is uneven; restart after a miss.</li>
<li>Your values and a few trusted people help you resist pressure and stay true to yourself.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is a personal profile in this course?
- A public advertisement for employers
* A private, honest one-page summary of who you are today
- A list of your friends
- A guarantee of your future
= The profile is for your own clarity and for sharing with trusted people. It changes as you grow.

Q: Which of these belong in your personal profile? (choose all that apply)
* Strengths with proof
* Ranked top values
- A promise to never change
* A purpose sentence and a growth area
= Evidence-based strengths, values, purpose and growth areas make a useful profile. It should stay open to change.

Q: Which goal is specific and measurable?
- Be better with money
- Try harder in business
* Save UGX 20,000 every week for 40 weeks in a separate wallet
- Become successful
= Specific numbers and time frames let you check progress.

Q: Rose wrote fifteen goals and quit within three weeks. What does this module suggest?
- Write twenty goals next time
* Cut to three goals with small actions for the next 30 days and track them
- Never plan again
- Ask others to do the plan for her
= Fewer clear goals are more likely to be done than a long list.

Q: What is a good way to build a new habit?
- Promise to do a large amount every day
* Make it very small and link it to something you already do daily
- Wait until you feel motivated
- Tell nobody
= Small habits tied to existing routines last longer than big promises.

Q: Isaac is pressured by friends to join a quick-money betting scheme. His top values are honesty and family. What helps most?
- Join so that he does not feel left behind
* Return to his written values, remember the risks and decline, using his time for his course
- Ask friends to decide for him
- Hide from everyone
= Written values give you a firm place to stand when the crowd moves quickly.

Q: Why have a monthly review?
- To prove you were right at the start
* To check progress, learn what changed and adjust the plan
- To compare yourself with others
- Because plans never change
= Regular reviews let you adjust sooner and stay realistic about progress.

Q: After missing a week of your habit, what is the best response?
- Stop completely
- Blame yourself harshly
- Pretend it never happened
* Treat it as a signal to restart and adjust the habit if needed
= Progress is uneven. Restarting quickly matters more than a perfect record.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What does self-awareness mean?
* Noticing your own thoughts, feelings and behaviour and how others see you
- Knowing what everyone else is thinking
- Never feeling doubt
- Always agreeing with your family
= Self-awareness has an inside view and an outside view, and the two should be close.

Q: Which habit builds self-awareness?
- Avoiding all feedback
- Comparing yourself with strangers online
* Writing a short note each night about what went well, what was hard and what you learned
- Waiting for others to define you
= Regular reflection shows patterns you might miss day to day.

Q: What is the difference between a talent and a skill?
- A talent is learned and a skill is natural
* A talent comes naturally, while a skill is learned through practice
- Both mean exactly the same
- Only adults have talents
= Talents are natural leanings and skills are built; both become strengths with use.

Q: Which are good ways to discover your strengths? (choose all that apply)
* Reviewing times you were proud and what you did well
- Copying a friend's list
* Asking several people when they see you at your best
* Noticing what people often ask you to help with
= Evidence from your life and from others is more reliable than a copied list.

Q: How should you treat a free online questionnaire about your career type?
- As proof of your future
* As an idea to think about, compared with your own evidence and feedback
- As an official qualification
- As a reason to ignore your interests
= Such tools are prompts for reflection, not scientific verdicts.

Q: What does the energy test help you notice?
- Which friends are richer
- How much sleep you need
- Which subjects are easiest to pass
* Which activities leave you feeling alive and which leave you drained
= Energy patterns point to work and study that suit you.

Q: Peace feels drained by her engineering labs but alive when working on rural water projects. What is the best step?
- Stay silent and suffer through the course
* Talk to a counsellor and explore related courses, such as community development or public health
- Stop all education
- Decide that interests do not matter
= Early, guided exploration is better than staying on a path that does not fit.

Q: What is a value?
- A rule you were forced to follow
- A goal you reach by next month
* A principle you choose to live by, such as honesty or service
- A talent you were born with
= Values guide how you live as you pursue goals.

Q: Why rank your values?
- To judge friends
- To avoid all decisions
* To know which value should win when two of them conflict
- To impress others
= Values like family and freedom can pull different ways, and a ranking helps you choose.

Q: Grace values honesty first, but a well-paid internship asks her to inflate sales figures. What does her ranking suggest?
- Accept because pay matters most
* Decline and choose a fair opportunity even if it pays less
- Accept and keep quiet
- Argue with the manager in public
= Choosing against your top value brings regret and loss of trust later.

Q: Which sentence shows a growth mindset?
- I am just not clever
- Only lucky people succeed
* I cannot do this yet, so I will practise and ask for help
- There is no point trying
= Growth thinking sees ability as something that can improve with effort and good methods.

Q: Which statement about personality is most accurate?
* People differ along scales, most sit in the middle, and behaviour changes with the situation
- A quiz can fix your type for life
- Quiet people cannot be leaders
- Everyone of the same type behaves identically
= Simple scales respect differences without boxing people in.

Q: Mercy is quiet and is called unfriendly by classmates. What is a good response?
- Pretend to be someone else forever
- Stop attending group work
* Calmly explain how she thinks best and offer to send her ideas in writing before meetings
- Argue with everyone
= Explaining your style helps others work with you; teams benefit from both quiet and outgoing people.

Q: What is a turning point?
- A forgotten event
* An event after which your direction or understanding changed
- A rule in your family
- Only a happy occasion
= Turning points may be good or hard and show how your life changed.

Q: A painful memory comes up while drawing your lifeline. What should you do?
* Skip it for now and talk to a counsellor, elder or trusted adult
- Force yourself to write everything tonight
- Post it online
- Pretend you have no past
= You do not have to face painful memories alone.

Q: Which are true about purpose? (choose all that apply)
* It can be ordinary work that helps others
- It must be famous
* It can change over time
* It can grow through action and learning
= Purpose is personal and flexible.

Q: Patrick sells second-hand clothes and feels his work is meaningless. What did he discover?
- That purpose only exists in offices
* That his low-cost uniforms helped families keep children in school, so his work had meaning
- That he should stop selling
- That friends are always right
= Purpose often hides in ordinary work when you see its effect on people.

Q: What is a blind spot?
- A weakness everyone agrees on
- A fear of the dark
* Something others can see about you that you do not
- A skill you never use
= We see our intentions while others see our actions.

Q: Dan promises ten-minute repairs that take an hour and customers complain. Which action is best?
- Ignore complaints
* Give realistic times and message customers when jobs are ready
- Blame customers
- Close the stall
= Feedback plus changed behaviour repairs trust.

Q: Your family expects a career you do not want. Which first step does this course suggest?
- Ignore them
- Pretend to agree and do nothing
* Listen to their reasons, then explain your view respectfully with evidence and offer a trial
- Argue until they give in
= Respect and evidence make agreement more likely than argument.

Q: Alex wants to learn motorcycle mechanics but his family thinks the trade is for school failures. Which approach worked in this course?
- Leave home without telling anyone
- Drop the idea at once
* Ask a respected uncle to show the family a busy garage, then agree on a short technical course
- Hide the decision until it is final
= A trusted go-between and visible evidence can change family views.

Q: Which is a good feedback question?
- Am I okay?
- Do you like me?
* What is one thing you think I should keep doing and one thing I should stop?
- Is everyone else better than me?
= Specific questions bring useful answers about behaviour.

Q: Which is a sign you should talk to a counsellor or trusted adult?
- You are mildly nervous before a talk
- You had one sad afternoon
* Sadness or worry lasting weeks stops you from eating, sleeping or studying
- You disagreed with a friend
= Short-lived feelings are normal. Persistent distress that disrupts life is a reason to ask for support.

Q: Why test an option cheaply before committing?
- Because tests are required by law
* Because small experiments show real evidence without risking much money or time
- Because planning is useless
- Because family decides anyway
= A cheap test can save fees, savings and years.

Q: Naki cooked 20 lunches and sold them before buying equipment. What did this achieve?
- It proved she should not start a business
* It gave her real demand data, feedback and regular customers before she spent more
- It replaced her need for a plan
- It was only a hobby
= A small trial shows what customers want and what to fix.

Q: Which are valid parts of a six-step decision method? (choose all that apply)
* List real options and gather facts
* Score options against your top values
- Ask only the loudest person to decide
* Imagine the worst case and set a review date
= A calm, evidence-based method with a review date is better than being pushed.

Q: Beatrice is offered a higher-paying sales job that pushes customers into loans they do not need, and she values honesty. What fits this course best?
- Take it for the money
* Check it against her values, decline and plan for the lower income
- Take it and keep quiet
- Delay forever without deciding
= Her top value gives her a clear reason to say no, and a plan helps her carry the cost.

Q: What should a one-page personal profile contain?
- Only your CV
* Strengths, interests, values, a belief to grow, working style, story, purpose, a growth area and supporters
- Names of all your relatives
- Predictions of the next ten years
= The profile summarises what you have discovered about yourself in nine short sections.

Q: Which goal is best for a 12-month direction statement?
- Be successful
- Try harder
* Finish a bookkeeping short course by month 3 and record every sale for nine months
- Be famous
= Specific goals with numbers and time frames can be checked.

QUIZ,
];
