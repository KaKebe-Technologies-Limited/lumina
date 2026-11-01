<?php
return [
    'slug'        => 'discipline-time-management',
    'title'       => 'Discipline and Time Management for Young People',
    'category'    => 'careers',
    'level'       => 'beginner',
    'icon'        => 'settings',
    'sort'        => 610,
    'summary'     => 'Learn to see where your time goes, choose what matters, plan your day, week and term, beat distraction and procrastination, build habits that last and recover quickly when you slip.',
    'description' => '<p>Discipline is the skill of doing what you decided to do, even when you do not feel like it. It is not a talent some people are born with. It is a set of small habits and systems that anyone can learn. This course is written for students, graduates, young traders, apprentices and first-time employees who feel that the day disappears before the important things are done.</p><p>You will begin by measuring where your time really goes, then learn to choose priorities with the urgent and important matrix and the big rocks idea. You will plan a day, a week and a term using a paper diary, a phone calendar and simple to-do lists, and you will handle distractions such as TikTok, WhatsApp groups, noise and power cuts. You will study the causes of procrastination and practise fixes that work.</p><p>The later modules cover habits and routines, sleep and energy, effective studying and working, saying no politely, group work and deadlines, basic money discipline, and how to get back on track after a bad week. You finish with a personal system and a 30-day discipline challenge. Every lesson has worked examples from boarding schools, universities, markets and workplaces in East Africa, and a short task you can do the same day.</p>',
    'outcomes'    => [
        'Explain the difference between discipline and motivation and why systems beat mood',
        'Record and analyse where your time goes using a simple time audit',
        'Sort tasks with the urgent and important matrix and protect your big rocks',
        'Plan a day, a week and a term using a diary, phone calendar and to-do lists',
        'Reduce distraction from your phone, social media, noise and power cuts',
        'Recognise the causes of procrastination and apply the 2-minute rule, Pomodoro and eating the frog',
        'Build habits and routines with a cue, a routine and a reward, and protect your sleep and energy',
        'Study and work effectively using active recall, spaced practice and deep work blocks',
        'Say no politely, manage deadlines and group work, and keep a basic budget',
        'Recover from setbacks and run your own 30-day discipline challenge',
    ],
    'requirements' => ['A notebook and pen, or a smartphone with a calendar app', 'No experience needed; you only need willingness to try small daily experiments'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Khan Academy: free lessons and study help', 'url' => 'https://www.khanacademy.org/'],
        ['title' => 'Google Calendar Help', 'url' => 'https://support.google.com/calendar'],
        ['title' => 'Google Digital Wellbeing', 'url' => 'https://wellbeing.google/'],
        ['title' => 'Mind Tools: time management resources', 'url' => 'https://www.mindtools.com/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: Discipline, time and priorities',
            'summary' => 'Understand what discipline really is, find out where your time goes, and decide what deserves it.',
            'lessons' => [
                [
                    'title'   => 'Discipline versus motivation',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain the difference between motivation and discipline and use three simple tools that make discipline easier. This matters because many young people wait to "feel like" studying or working, and the feeling often never arrives on the days it is needed most.</p>

<h3>What each word means</h3>
<p><strong>Motivation</strong> is a feeling of wanting to act. It is strong after an inspiring speech and weak on a tired Tuesday. <strong>Discipline</strong> is choosing to act according to your plan whatever the feeling. Discipline is not punishment and it is not about being harsh with yourself. It is keeping small promises to yourself so that you can trust yourself.</p>
<table>
<thead><tr><th></th><th>Motivation</th><th>Discipline</th></tr></thead>
<tbody>
<tr><td>Source</td><td>Mood and excitement</td><td>Decisions, habits and systems</td></tr>
<tr><td>Reliability</td><td>Comes and goes</td><td>Steady once practised</td></tr>
<tr><td>Best use</td><td>Starting something new</td><td>Continuing when it gets boring</td></tr>
</tbody>
</table>

<h3>Three tools that make discipline easier</h3>
<ol>
<li><strong>A clear reason.</strong> Write why the task matters to you, for example "I want to pass Accounting so I can join the finance programme".</li>
<li><strong>A fixed time and place.</strong> "Study at 5:00 pm at the library desk" removes the daily argument with yourself.</li>
<li><strong>A small first step.</strong> "Open the notebook and write the date" is easy, and starting is the hardest part.</li>
</ol>

<h3>Worked example: a boarding-school student</h3>
<p>Brian is in Senior 5 at a boarding school in Mbarara. Before: he waits to feel ready, so prep time (the supervised evening study period) is spent chatting, and he crams at night. After: he decides that the first 20 minutes of every prep is Chemistry, always at the same desk, with the textbook already open before the bell. Some evenings he does not feel like it, but the system carries him. After a term his Chemistry mark rises from 48 to 61 percent.</p>
<table>
<thead><tr><th>Time</th><th>Before</th><th>After</th></tr></thead>
<tbody>
<tr><td>7:00 pm</td><td>Chat, wait for mood</td><td>Chemistry, 20 minutes</td></tr>
<tr><td>7:20 pm</td><td>Chat</td><td>Mathematics practice, 40 minutes</td></tr>
<tr><td>8:00 pm</td><td>Start worrying</td><td>Short break, then revise notes</td></tr>
</tbody>
</table>

<h3>Case scenario</h3>
<p>Aisha, a first-year student, felt very inspired after a motivational talk and promised to study four hours daily. For three days she managed it. On day four she was tired, skipped, felt guilty and gave up for two weeks. She decided to reset with a smaller rule: 45 minutes every day at 6:00 pm, no matter what. Even on a bad day she did the 45 minutes. After a month she had studied more hours in total than the four-hour plan would have given. The lesson: a small plan you can keep beats a big plan that depends on mood.</p>

<blockquote><p><strong>Quick check:</strong> Is discipline a talent you are born with?</p><p>Answer: No, it is a skill built through small repeated decisions, habits and systems.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why does a fixed time and place help?</p><p>Answer: It removes the daily decision, so you do not have to rely on mood to begin.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose one task you keep postponing, such as revision or a business record.</li>
<li>Write one sentence on why it matters to you.</li>
<li>Pick a fixed time, a place and a tiny first step.</li>
<li>Do it for three days, even for only 10 minutes, and tick each day.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Motivation is a feeling; discipline is a decision backed by a system.</li>
<li>Start with a reason, a fixed time and place, and a tiny first step.</li>
<li>A small plan you keep is better than a big plan you drop.</li>
<li>Discipline means keeping promises to yourself, not being harsh on yourself.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Know where your time goes: the time audit',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to run a three-day time audit and read the results honestly. This matters because most people believe they "have no time" when in fact an hour or two a day leaks away without being noticed, and you cannot fix a leak you cannot see.</p>

<h3>What a time audit is</h3>
<p>A <strong>time audit</strong> is a written record of what you actually did, hour by hour, for several days. It records the truth, not your plan. Use a notebook or the notes app on your phone. Every two or three hours, write what you did in the last block and how long it took. Do it for three days that include at least one weekend day, because weekends reveal different habits.</p>

<h3>Group your hours</h3>
<p>At the end, sort the hours into simple groups: sleep, study or work, travel, meals and chores, phone and social media, rest and fun, and "lost" time (waiting, drifting, looking for things). Add up each group.</p>

<h3>Worked example: a university student living off campus</h3>
<p>Peter studies in Kampala and rents a room in Kikoni. His audit for one weekday looked like this.</p>
<table>
<thead><tr><th>Activity</th><th>Hours</th></tr></thead>
<tbody>
<tr><td>Sleep</td><td>6</td></tr>
<tr><td>Lectures</td><td>4</td></tr>
<tr><td>Travel (taxi, walking, waiting for the taxi to fill)</td><td>3</td></tr>
<tr><td>Meals, washing, chores</td><td>3</td></tr>
<tr><td>Phone and social media</td><td>4</td></tr>
<tr><td>Real study</td><td>1</td></tr>
<tr><td>Rest, chatting</td><td>3</td></tr>
</tbody>
</table>
<p>Peter was shocked. He thought he studied "for hours". In fact he had four hours on his phone and one hour of study. Without cancelling his social life, he moved one hour of phone time into study on the taxi ride (reading notes) and used a free lecture gap for revision. His study time rose from one hour to three hours a day.</p>

<h3>Questions to ask about your results</h3>
<ol>
<li>Where are the biggest blocks of time, and were they planned?</li>
<li>Which activities gave me value, and which only felt like rest?</li>
<li>When in the day was I most alert? Where was that time spent?</li>
<li>Which small blocks (10 to 30 minutes) could be used better?</li>
</ol>

<h3>Case scenario</h3>
<p>Sarah, a young trader at Owino market, was sure she was too busy for any bookkeeping. She tried a one-day audit and found she spent about two hours waiting for stock deliveries, doing nothing. She decided to use those waits to write the day's sales in a notebook. She did it for a week and, for the first time, saw which items earned profit. However, in the first audit she cheated: she wrote down what she wished she had done on Saturday. A friend pointed out that her record had no phone time at all, which was not believable. She redid it honestly and the real picture helped. The lesson: an audit only works if it is truthful.</p>

<blockquote><p><strong>Quick check:</strong> Should a time audit record your plan or what you actually did?</p><p>Answer: What you actually did, because only honest data shows where time leaks.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why include a weekend day in the audit?</p><p>Answer: Weekends show different habits, such as late sleeping or long phone sessions, that weekdays hide.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Draw a table in your notebook with the hours of the day down the side.</li>
<li>For three days, fill in what you did every two or three hours.</li>
<li>Add up the hours in each group.</li>
<li>Circle the group that surprised you most and write one change you will test.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A time audit records what you really did, hour by hour.</li>
<li>Three days, including a weekend day, give a useful picture.</li>
<li>Look for big blocks, wasted gaps and your best energy hours.</li>
<li>Small reclaimed gaps add up to hours every week.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Priorities: urgent, important and big rocks',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to sort your tasks into four groups and schedule the most valuable ones first. This matters because being busy is not the same as being productive; many people spend the day answering urgent small things while the important work waits until it becomes an emergency.</p>

<h3>The urgent and important matrix</h3>
<p><strong>Urgent</strong> means it needs attention soon, often because someone is waiting or a deadline is near. <strong>Important</strong> means it moves you towards your goals, your health or your responsibilities. Put each task in one of four boxes.</p>
<table>
<thead><tr><th></th><th>Urgent</th><th>Not urgent</th></tr></thead>
<tbody>
<tr><td><strong>Important</strong></td><td>Box 1: Do now (assignment due tomorrow, a sick relative)</td><td>Box 2: Plan and protect (revision, exercise, saving, skills practice)</td></tr>
<tr><td><strong>Not important</strong></td><td>Box 3: Delegate or limit (many calls, some messages, favours that are not yours)</td><td>Box 4: Drop or minimise (endless scrolling, gossip)</td></tr>
</tbody>
</table>
<p>Success lives in Box 2. If you give it time early, fewer tasks fall into Box 1 later.</p>

<h3>Big rocks first</h3>
<p>Imagine a jar. If you pour in sand (small tasks) first, the big rocks will not fit. If you place the big rocks first, the sand fills the gaps. Your <strong>big rocks</strong> are the two or three most important things for the week, for example "finish the case study", "stock check at the shop" and "gym or walk three times". Schedule them first, then fit small tasks around them.</p>

<h3>Worked example: sorting a to-do list</h3>
<p>Grace, a final-year student, wrote these tasks for Monday.</p>
<table>
<thead><tr><th>Task</th><th>Box</th><th>Decision</th></tr></thead>
<tbody>
<tr><td>Submit project report (due Tuesday 9:00 am)</td><td>1</td><td>Do today, first</td></tr>
<tr><td>Revise for test in two weeks</td><td>2</td><td>Schedule 1 hour daily</td></tr>
<tr><td>Reply to 15 WhatsApp messages</td><td>3</td><td>Do in one 15-minute block at lunch</td></tr>
<tr><td>Watch a long series episode</td><td>4</td><td>Only after the first two are done</td></tr>
</tbody>
</table>

<h3>Case scenario</h3>
<p>Moses, a shift worker at a hotel in Jinja, finished work at 11:00 pm and spent his free mornings helping friends and chatting. He wanted to complete an online accounting certificate but "had no time". He decided to treat the course as a big rock: 9:00 to 10:30 am on four mornings, booked like a work shift. He told friends he was unavailable at that time. In the first two weeks he slipped twice because a friend called and he went out. He did not quit; he moved his phone to silent for those blocks. In three months he finished the course. The lesson: important but not urgent work only happens if you book it and protect it.</p>

<blockquote><p><strong>Quick check:</strong> Revising for a test in two weeks is in which box?</p><p>Answer: Box 2, important but not urgent; doing it early prevents a Box 1 emergency later.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is the point of the big rocks idea?</p><p>Answer: Schedule the most important tasks first, because small tasks will otherwise fill all the time.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write every task you can think of for this week.</li>
<li>Mark each with 1, 2, 3 or 4 using the matrix.</li>
<li>Choose your two or three big rocks and put them in your diary at fixed times.</li>
<li>Cross out or limit at least one Box 4 task.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Urgent is not always important; important is not always urgent.</li>
<li>Box 2 tasks build your future, so protect time for them.</li>
<li>Schedule big rocks first and fit small tasks around them.</li>
<li>Say clearly when you are unavailable during protected time.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Goals you can actually schedule',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to turn a vague wish into a clear goal with steps and dates. This matters because "I want to do better this term" cannot be put in a diary, but "complete 30 practice questions every Saturday" can.</p>

<h3>From wish to goal</h3>
<p>A good goal is <strong>specific</strong> (clear action), <strong>measurable</strong> (you can count it), <strong>realistic</strong> (possible with your life now), and has a <strong>deadline</strong>. Compare these.</p>
<table>
<thead><tr><th>Vague wish</th><th>Clear goal</th></tr></thead>
<tbody>
<tr><td>Be better at Mathematics</td><td>Raise my Mathematics mark from 52 to 65 by the end of term by doing 10 practice questions on four evenings a week</td></tr>
<tr><td>Save money</td><td>Save UGX 20,000 every Friday for 20 weeks to reach UGX 400,000</td></tr>
<tr><td>Get fit</td><td>Walk briskly for 30 minutes on Monday, Wednesday and Saturday for six weeks</td></tr>
</tbody>
</table>

<h3>Break it into steps</h3>
<ol>
<li>Write the goal and the date.</li>
<li>Work backwards: what must be done by the middle of the period? By each week?</li>
<li>Pick this week's actions and put them in your diary with a day and time.</li>
<li>Review every Sunday: done, partly done, not done, and why.</li>
</ol>

<h3>Worked example: a young trader</h3>
<p>Ritah sells second-hand clothes in Mbale. Her wish: "grow my business". Her goal: "Increase weekly sales from UGX 300,000 to UGX 400,000 in three months." Monthly steps: month 1, keep a daily sales book; month 2, add two new popular items and post photos on WhatsApp status three times a week; month 3, offer a small discount for repeat customers. Weekly diary entries make each step visible: Monday 7:00 pm, take photos; Thursday 7:00 pm, update the sales book.</p>
<table>
<thead><tr><th>Week</th><th>Action</th><th>When</th></tr></thead>
<tbody>
<tr><td>Week 1</td><td>Start daily sales book</td><td>Every evening, 8:00 pm</td></tr>
<tr><td>Week 2</td><td>Add photo posts</td><td>Mon, Wed, Fri, 7:00 pm</td></tr>
<tr><td>Week 3</td><td>Review which items sold</td><td>Sunday, 30 minutes</td></tr>
</tbody>
</table>

<h3>Case scenario</h3>
<p>Daniel set the goal "read more" and failed. He then set "finish one 250-page book this month" and started with 25 pages a day. By day 12 he had missed three days and was behind, so he felt he had failed again. A mentor suggested he check the math: 250 pages in 30 days is only about eight pages a day, and he had picked too much. He changed to 10 pages every night before bed. He finished the book in 25 days. The lesson: check that the daily action matches the goal and your real life; an unrealistic plan is a setup for failure.</p>

<blockquote><p><strong>Quick check:</strong> What four features make a goal easy to schedule?</p><p>Answer: It is specific, measurable, realistic and has a deadline.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why work backwards from the goal date?</p><p>Answer: It shows what must be done each month and week, so you can place actions in your diary.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick one wish for this term or month.</li>
<li>Rewrite it using the four features: specific, measurable, realistic, deadline.</li>
<li>List three weekly actions that would achieve it.</li>
<li>Put the first week's actions in your diary with days and times.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A goal needs a clear action, a number and a date.</li>
<li>Break goals into monthly and weekly steps.</li>
<li>Check the daily workload is realistic before you start.</li>
<li>Review weekly and adjust without giving up.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which statement describes discipline best?
- Waiting until you feel inspired
- Being harsh and punishing yourself when you fail
* Doing what you planned even when you do not feel like it
- Having a talent that some people are born with
= Discipline is choosing to follow your plan whatever the mood. It can be learned through small habits and systems.

Q: What is the main purpose of a time audit?
- To plan next year's timetable
* To record what you actually did so you can see where time leaks
- To prove to others that you are busy
- To count how many tasks you have
= The audit shows the truth about your day. Without it you only guess where the hours go.

Q: Tasks that are important but not urgent, such as steady revision for a test next month, belong in
- Box 1, do now
- Box 3, delegate
* Box 2, plan and protect
- Box 4, drop
= Box 2 work builds results and prevents later emergencies, so it needs protected time.

Q: Moses wants to finish an online course but spends every free morning with friends. Which step from this module helps most?
- Wait until he feels motivated
* Book fixed course blocks as big rocks and tell friends he is unavailable then
- Stop seeing friends completely
- Try to study only late at night after work
= Important but not urgent work needs a fixed, protected slot, like a work shift.

Q: Sort these into the right order of action for Monday: (1) report due Tuesday 9 am, (2) 15 non-urgent messages, (3) revision for a test in two weeks, (4) endless scrolling. Which order is best?
- 4, 2, 3, 1
- 2, 1, 4, 3
* 1, 3, 2, 4
- 3, 4, 1, 2
= Do the urgent and important report first, schedule the important revision, handle messages in a short block, and leave scrolling for last.

Q: Which of these are features of a good goal? (choose all that apply)
* Specific
* Measurable
* Has a deadline
- As big as possible with no plan
= Clear, countable goals with a date can be scheduled. A giant goal without a plan cannot.

Q: Daniel planned to read 250 pages in a month but started with 25 pages a day and quit. What is the best fix?
- Set a bigger goal
* Check the sums and choose a realistic daily amount, such as 10 pages
- Read only on weekends
- Forget about reading
= 250 pages in 30 days is about 8 pages a day. A realistic daily action keeps the plan alive.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Planning your day, week and term',
            'summary' => 'Build simple plans for a day, a week and a whole term, and choose the diary, calendar and list tools that suit you.',
            'lessons' => [
                [
                    'title'   => 'Planning your day',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to plan a realistic day in five minutes and keep to it when things change. This matters because a day that begins without a plan is usually run by other people's messages and requests.</p>

<h3>The five-minute daily plan</h3>
<ol>
<li><strong>Look at fixed things first.</strong> Classes, work shifts, meals, travel and prayers or church are already set.</li>
<li><strong>Choose the top three tasks.</strong> What three things would make today a good day? Write only three.</li>
<li><strong>Place them in time slots.</strong> Put the hardest task in your best energy period.</li>
<li><strong>Add buffers.</strong> Leave 15 to 30 minutes of empty space; things always take longer than expected, especially travel.</li>
<li><strong>Do it the night before.</strong> Planning at 9:00 pm for tomorrow lets you wake up knowing what to do.</li>
</ol>

<h3>Worked example: a boarding-school student</h3>
<p>Esther is in Senior 3. School bells set the structure, but she controls free slots and prep. Here is a before and after of her Wednesday.</p>
<table>
<thead><tr><th>Time</th><th>Before (no plan)</th><th>After (planned)</th></tr></thead>
<tbody>
<tr><td>5:30 to 6:30 am</td><td>Sleeping late, rushing</td><td>Wake, wash, 15 minutes Biology notes</td></tr>
<tr><td>1:00 to 2:00 pm</td><td>Lunch and idle chat</td><td>Lunch, then 20 minutes quick revision</td></tr>
<tr><td>4:30 to 5:30 pm</td><td>Sports and wandering</td><td>Sports, then wash and rest</td></tr>
<tr><td>7:00 to 9:00 pm</td><td>Prep time spent on whatever is nearest</td><td>Mathematics, 45 min; English essay, 45 min; review, 30 min</td></tr>
</tbody>
</table>

<h3>Planning when you do shift work</h3>
<p>Kato works the 2:00 pm to 10:00 pm shift as a security guard in Kampala and wants to study for a diploma. His planning rule: mornings are for study (9:00 to 11:00), chores and cooking at noon, and the break at work is for a short reading session. His top three tasks the night before are written in a small notebook in his pocket.</p>

<h3>Case scenario</h3>
<p>Florence planned nine tasks for one day and finished three, so she felt like a failure. She was also surprised when a taxi strike added two hours to her journey. She decided to plan three tasks and two buffers. The next day, with the same taxi delays, she still completed her three tasks and even one extra. But two weeks later she ignored the plan after a late night and spent the day recovering, which cost her an important deadline. She learned that protecting sleep is part of daily planning. The lesson: plan fewer things, add buffers and include rest.</p>

<blockquote><p><strong>Quick check:</strong> How many top tasks should you choose each day?</p><p>Answer: About three, because a short list can actually be completed.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why add buffers to a daily plan?</p><p>Answer: Tasks and travel often take longer than expected, and buffers stop one delay from ruining the day.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Tonight, write your fixed commitments for tomorrow.</li>
<li>Choose three top tasks and give each a time slot.</li>
<li>Add two buffer gaps of at least 15 minutes.</li>
<li>Tomorrow evening, tick what you finished and note one thing to change.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Plan the day the night before in about five minutes.</li>
<li>Choose three top tasks and put the hardest in your best energy time.</li>
<li>Add buffers so small delays do not break the day.</li>
<li>Include rest, meals and sleep in the plan.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Planning your week and your term',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to make a weekly plan and a term map so that deadlines never surprise you. This matters because most missed deadlines are visible weeks ahead, but only if someone looked.</p>

<h3>The weekly review and plan</h3>
<p>Pick one time every week, such as Sunday at 5:00 pm, and spend 20 minutes on three questions. What happened last week? What is coming this week (deadlines, tests, events)? What are my big rocks? Then fill a weekly grid: first the fixed items, then the big rocks, then smaller tasks, and last the free time.</p>

<h3>Worked example: a weekly plan for a university student</h3>
<table>
<thead><tr><th>Day</th><th>Morning</th><th>Afternoon</th><th>Evening</th></tr></thead>
<tbody>
<tr><td>Monday</td><td>Lectures</td><td>Library: assignment draft (2 h)</td><td>Cook, rest, plan</td></tr>
<tr><td>Tuesday</td><td>Lectures</td><td>Group meeting (1 h)</td><td>Revise Statistics (1 h)</td></tr>
<tr><td>Wednesday</td><td>Lectures</td><td>Library: finish assignment</td><td>Exercise, rest</td></tr>
<tr><td>Thursday</td><td>Lectures</td><td>Part-time tutoring (3 h)</td><td>Light revision</td></tr>
<tr><td>Friday</td><td>Revise</td><td>Submit assignment, errands</td><td>Free</td></tr>
<tr><td>Saturday</td><td>Laundry, chores</td><td>Study session (2 h)</td><td>Family call, social</td></tr>
<tr><td>Sunday</td><td>Church or rest</td><td>Weekly review (20 min)</td><td>Prepare for Monday</td></tr>
</tbody>
</table>

<h3>The term map</h3>
<p>At the start of the term, write on one page: the first and last day, exam dates, assignment deadlines, holidays, school fees dates, and big events such as sports or family occasions. Then work backwards from each deadline and place a start date at least two weeks before. A term map turns "exams are far away" into "my revision must begin in week six".</p>
<table>
<thead><tr><th>Week</th><th>What is due</th><th>Start by</th></tr></thead>
<tbody>
<tr><td>5</td><td>Research assignment</td><td>Week 2</td></tr>
<tr><td>8</td><td>Group presentation</td><td>Week 4</td></tr>
<tr><td>12</td><td>Final exams begin</td><td>Revision starts week 8</td></tr>
</tbody>
</table>

<h3>Case scenario</h3>
<p>Isaac, a college student, never made a term map. In week 10 he discovered that three assignments and a presentation were due in the same fortnight, and he handed in two of them late and badly. In the next term he made a one-page map in the first week, and in week 3 he noticed another clash early. He spoke to a lecturer, who allowed an extension for one paper. However, he forgot to update the map when a deadline moved, and he nearly missed it. The lesson: build the map early, and update it whenever you hear of a change.</p>

<blockquote><p><strong>Quick check:</strong> When should you start work on a deadline listed in your term map?</p><p>Answer: Well before it, ideally two weeks or more, so there is time for drafts and problems.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is the purpose of the weekly review?</p><p>Answer: To look back, see what is coming and place your big rocks before the week begins.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Draw a weekly grid with the seven days.</li>
<li>Fill in fixed commitments, then two big rocks, then free time.</li>
<li>On a separate page, make a term map with all known deadlines.</li>
<li>Set a weekly reminder for your 20-minute review.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Plan the week in a regular 20-minute review.</li>
<li>Put fixed items first, then big rocks, then small tasks.</li>
<li>A term map shows deadlines early and shows when to begin.</li>
<li>Update your plans whenever dates change.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Diaries, phone calendars and to-do lists',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose a planning tool that fits your life and use it well. This matters because the best system is the one you will open every day, and no tool helps if it stays unused in a bag.</p>

<h3>Three common tools</h3>
<table>
<thead><tr><th>Tool</th><th>Strengths</th><th>Weaknesses</th></tr></thead>
<tbody>
<tr><td>Paper diary or notebook</td><td>Cheap, works without power or data, fewer distractions, easy to see the whole week</td><td>Can be lost; no reminders; hard to change many items</td></tr>
<tr><td>Phone calendar (such as Google Calendar)</td><td>Reminders, repeating events, always with you, can be backed up</td><td>Phone brings distractions; battery and data limits</td></tr>
<tr><td>To-do list (paper or app)</td><td>Quick capture; satisfying to tick off</td><td>Gets very long; has no time attached to tasks</td></tr>
</tbody>
</table>

<h3>Using each tool well</h3>
<ol>
<li><strong>Calendar for things with a time.</strong> Classes, meetings, deadlines and appointments go in the calendar. Set a reminder a day before and one hour before for important items.</li>
<li><strong>List for things without a time.</strong> Keep one master list and each morning choose three items for today.</li>
<li><strong>Turn tasks into appointments.</strong> If "write the report" is important, give it a calendar block: Thursday 2:00 to 4:00 pm.</li>
<li><strong>Capture quickly.</strong> Keep a small notebook or phone note for ideas and tasks as they come, so you do not rely on memory.</li>
</ol>

<h3>Worked example: a young trader</h3>
<p>Joseph runs a phone-accessories stall in Gulu and uses a pocket notebook plus his phone. The notebook holds his daily top three and his sales numbers. The phone calendar holds supplier visits, rent due dates and a Sunday 6:00 pm review. A to-do list on the phone holds longer jobs such as "repaint signboard". Each morning he moves three items from the list into the notebook.</p>
<table>
<thead><tr><th>Item</th><th>Where it goes</th></tr></thead>
<tbody>
<tr><td>Pay stall rent on the 5th</td><td>Phone calendar with reminder two days before</td></tr>
<tr><td>Buy more chargers</td><td>To-do list, then daily top three on the supplier day</td></tr>
<tr><td>Today's sales</td><td>Pocket notebook</td></tr>
</tbody>
</table>

<h3>Case scenario</h3>
<p>Naomi switched to a phone-only system and set 30 reminders a day. Within a week she was ignoring all of them, and she missed a real appointment. She decided to keep only reminders for fixed-time events and use a daily top-three list for the rest. Later her phone was stolen and she lost all her notes because she had no backup. She started to sync her calendar to her email account and to write the term map on paper as well. The lesson: use few, meaningful reminders, and keep a backup of anything important.</p>

<blockquote><p><strong>Quick check:</strong> What kind of item belongs in a calendar rather than a to-do list?</p><p>Answer: Anything with a fixed time or date, such as a class, appointment or deadline.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why back up your planning tool?</p><p>Answer: A lost or stolen phone or diary would otherwise erase deadlines and notes you depend on.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose one main tool: paper, phone calendar or both with clear roles.</li>
<li>Enter all deadlines and appointments for the next month.</li>
<li>Create a master to-do list and star the three items for tomorrow.</li>
<li>Check that you have a backup, such as a synced calendar or a photo of your page.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Pick the tool you will actually use every day.</li>
<li>Calendar for timed events, list for loose tasks.</li>
<li>Give important tasks a time block in the calendar.</li>
<li>Keep reminders few and keep a backup.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: When is the best time to plan tomorrow?
- At the start of the lesson tomorrow
* The evening before, in about five minutes
- Only on Sundays
- Never, plans always change
= Planning the night before helps you wake up with a clear list, and it takes only a few minutes.

Q: Why add buffers to your plan?
- To waste time on purpose
- To make the plan look longer
* Because tasks and travel often take longer than expected
- Because you do not need rest
= Buffers absorb delays so one late taxi or long task does not ruin the day.

Q: Which of these belong in a phone calendar rather than only a to-do list? (choose all that apply)
* A class at 10:00 am on Tuesday
* A rent due date
- A vague idea to maybe learn guitar one day
* A deadline for an assignment
= Items with a date or time suit a calendar and its reminders. Vague ideas belong on a loose list.

Q: Isaac finds in week 10 that three assignments are due in the same fortnight. Which habit would have prevented this?
- Studying only at night
- Ignoring the dates
* A term map made in the first week, with start dates worked back from each deadline
- Asking friends to remind him
= A term map shows clashes early so work can be started and spread out.

Q: Look at this plan: Monday has 9 tasks, no gaps, no meals, and study until 1:00 am. What is the best fix?
- Add three more tasks
* Cut to three top tasks, add buffers, and include meals and sleep
- Do the plan faster
- Stop planning altogether
= A realistic plan has few priorities, spare time and rest, so it can be followed.

Q: Naomi set 30 phone reminders a day and now ignores them all. What should she do?
- Add more reminders
* Keep reminders only for timed events and use a daily top-three list
- Delete the calendar
- Buy a new phone
= Too many reminders become noise. A few meaningful ones stay useful.

Q: What is the purpose of a weekly review?
* To look back, check what is coming and place your big rocks
- To decide to quit when behind
- To make the week longer
- To compare yourself with friends
= The review keeps your plan connected to real events and priorities.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Focus, distraction and procrastination',
            'summary' => 'Control your phone and environment, plan around noise and power cuts, and understand and beat procrastination.',
            'lessons' => [
                [
                    'title'   => 'Phones, social media and attention',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use your phone on purpose instead of letting it use you. This matters because every notification pulls your attention away, and it can take many minutes to return to deep thinking after each interruption.</p>

<h3>Why the phone wins</h3>
<p>Apps such as TikTok, Instagram and WhatsApp are designed to keep you using them. A new video or message gives a small pleasant surprise, so your brain keeps checking. This is not a weakness in you; it is clever design. The answer is to change the environment so that willpower is not needed all the time.</p>

<h3>Practical steps</h3>
<ol>
<li><strong>Turn off non-essential notifications.</strong> Keep calls and messages from key people; turn off badges and sounds for the rest.</li>
<li><strong>Put the phone out of reach.</strong> A phone in another room or in your bag is far less tempting than one beside your book. Use airplane mode or Do Not Disturb during study.</li>
<li><strong>Mute busy WhatsApp groups.</strong> Check them at set times, such as 1:00 pm and 7:00 pm.</li>
<li><strong>Delete or limit one app.</strong> Use the screen-time or digital-wellbeing feature on your phone to set a daily limit.</li>
<li><strong>Give yourself a reward.</strong> Scroll freely after the work is done, for example 20 minutes after each study block.</li>
</ol>

<h3>Worked example: before and after</h3>
<p>Mercy, a university student, studied with her phone on the desk. Over a two-hour evening she picked it up 28 times. After she changed her setup, she did the same two hours with 3 phone checks.</p>
<table>
<thead><tr><th>Time</th><th>Before</th><th>After</th></tr></thead>
<tbody>
<tr><td>6:00 pm</td><td>Open notes, phone beside book, notifications on</td><td>Phone on silent in her bag across the room</td></tr>
<tr><td>6:30 pm</td><td>Check group, then TikTok for 25 minutes</td><td>Study continues</td></tr>
<tr><td>7:30 pm</td><td>Realises one page is done</td><td>Five-minute phone break, then next block</td></tr>
<tr><td>8:00 pm</td><td>Guilt and rush</td><td>Four topics reviewed</td></tr>
</tbody>
</table>

<h3>Worked example: a shift worker</h3>
<p>Brenda works in a supermarket in Entebbe and had a habit of scrolling in bed until 1:00 am. She set a rule: phone charges in the kitchen from 10:30 pm, and an alarm clock replaces the phone alarm. She sleeps earlier and wakes more alert for the morning shift.</p>

<h3>Case scenario</h3>
<p>Hassan, a Senior 6 student, kept his phone in the dormitory for "emergencies" and watched videos in hidden moments. He decided to hand the phone to the matron during the week and call home on agreed days. His grades improved over the term. But in the holidays he went back to heavy use and lost two weeks of his revision plan. He then set a daily limit of two hours and a no-phone hour before sleep. The lesson: your environment decides your behaviour, so design it again whenever the situation changes.</p>

<blockquote><p><strong>Quick check:</strong> Is it better to rely on willpower or to change your environment when studying?</p><p>Answer: Change the environment, for example by putting the phone away, because willpower runs out.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> How can you handle a very busy WhatsApp group?</p><p>Answer: Mute it and read it at set times, so it does not interrupt study or work.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open your phone screen-time page and note your daily average.</li>
<li>Turn off notifications for three apps you do not need instantly.</li>
<li>Study or work for 30 minutes with the phone in another room.</li>
<li>Reward yourself with a 5-minute check, and note how you felt.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Apps are designed to hold your attention; plan for that.</li>
<li>Turn off notifications and put the phone out of reach.</li>
<li>Mute groups and check them at set times.</li>
<li>Use phone time as a reward after focused work.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Noise, space and power cuts: planning your environment',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a workable study or work space and plan around power cuts, noise and shared rooms. This matters because many young people in East Africa live in busy homes, hostels or rented rooms, and waiting for perfect conditions means waiting forever.</p>

<h3>Make the best of the space you have</h3>
<ul>
<li><strong>Choose one spot</strong> and use it only for study or work, such as a corner of the room or a library desk. Your brain learns "this place means focus".</li>
<li><strong>Prepare it the night before:</strong> notebooks, pens, charged torch, water.</li>
<li><strong>Handle noise:</strong> use earplugs or headphones with calm instrumental sound, choose a quieter time (early morning), or use a library or classroom after hours.</li>
<li><strong>Talk to people you live with.</strong> Agree on "quiet hours" and what a closed door means.</li>
</ul>

<h3>Planning for power cuts and load-shedding</h3>
<p>Power cuts are a normal part of life in many places. The disciplined response is to plan for them instead of being surprised. Keep a simple two-track plan.</p>
<table>
<thead><tr><th>Task type</th><th>Do it when there is power</th><th>Do it when power is off</th></tr></thead>
<tbody>
<tr><td>Study</td><td>Typing, online research, videos, downloads</td><td>Reading printed notes, practice questions, flashcards, memorising, summaries by hand</td></tr>
<tr><td>Business</td><td>Charging phones, posting online, updating spreadsheets</td><td>Stock counting, calling customers, cleaning, planning</td></tr>
<tr><td>Preparation</td><td>Charge phone, power bank and torch fully</td><td>Use the torch or solar lamp; save battery</td></tr>
</tbody>
</table>
<p>Keep two or three offline tasks ready at all times so that a power cut becomes a change of task, not the end of work. Download videos and notes when you have data and electricity.</p>

<h3>Worked example: a university student off campus</h3>
<p>Peter's area has power cuts most evenings. Before, he stopped studying whenever the lights went out and fell behind. After, he keeps a "no-power folder" in his bag with printed past papers and handwritten notes. His plan for a typical evening:</p>
<table>
<thead><tr><th>Time</th><th>Power on</th><th>Power off</th></tr></thead>
<tbody>
<tr><td>6:00 to 7:00 pm</td><td>Online tutorial and note typing</td><td>Past paper questions by torch</td></tr>
<tr><td>7:00 to 7:30 pm</td><td>Charge phone, cook</td><td>Cook on charcoal, recite notes</td></tr>
<tr><td>7:30 to 8:30 pm</td><td>Group call</td><td>Mind-map summary on paper</td></tr>
</tbody>
</table>

<h3>Case scenario</h3>
<p>Annet, a trader running an online shop from Kampala, had a laptop battery that died in the middle of a customer order during a power cut, and she lost the order details. She decided to charge everything every evening and to keep a paper order book as a backup. For a month she did well. Then she forgot to charge her power bank before a long outage and could not answer customers for hours. She added "charge everything" to her night routine as a checklist item. The lesson: plan for the failure of power before it happens.</p>

<blockquote><p><strong>Quick check:</strong> What can you do during a power cut if you have prepared offline tasks?</p><p>Answer: Switch to tasks such as reading printed notes or practising questions, so no time is lost.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why use one fixed spot for study?</p><p>Answer: Your brain links the place with focus, so starting becomes easier.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose your study or work spot and clear it of unrelated items.</li>
<li>Make a no-power folder with at least three offline tasks.</li>
<li>Add "charge phone, power bank, torch" to tonight's checklist.</li>
<li>Agree a quiet-hour rule with someone you live with.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Do not wait for perfect conditions; improve the space you have.</li>
<li>Keep offline tasks ready for power cuts.</li>
<li>Charge devices and download materials in advance.</li>
<li>Agree quiet times with people you share space with.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Procrastination: causes and fixes',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain why you delay important tasks and use three techniques to start. This matters because procrastination (putting things off) rarely comes from laziness. It usually comes from feelings we want to avoid, and once you know the feeling you can solve it.</p>

<h3>Common causes</h3>
<table>
<thead><tr><th>Cause</th><th>What it sounds like</th><th>Fix</th></tr></thead>
<tbody>
<tr><td>Fear of failing</td><td>"If I try and it is bad, I look stupid"</td><td>Aim for a rough first draft; allow imperfect work</td></tr>
<tr><td>Task too big or unclear</td><td>"I do not know where to start"</td><td>Break into small steps; write the very first action</td></tr>
<tr><td>Boredom</td><td>"This is dull"</td><td>Use a timer and a reward</td></tr>
<tr><td>Tiredness</td><td>"I have no energy"</td><td>Sleep, eat, move; use best-energy times</td></tr>
<tr><td>Distraction nearby</td><td>"Just one more video"</td><td>Remove the temptation</td></tr>
</tbody>
</table>

<h3>Three techniques</h3>
<ol>
<li><strong>The 2-minute rule.</strong> If a task takes less than two minutes (reply to a message, wash a cup, write a date), do it now. For a big task, commit only to two minutes of starting. Starting often leads to continuing.</li>
<li><strong>The Pomodoro method.</strong> Work for 25 minutes with full attention, then rest for 5 minutes. After four rounds take a longer break of 15 to 30 minutes. A timer makes the work feel limited.</li>
<li><strong>Eating the frog.</strong> The "frog" is your hardest or most dreaded task. Do it first, before the easy things. The rest of the day then feels lighter.</li>
</ol>

<h3>Worked example: a university student</h3>
<p>Lydia had a 2,000-word essay due on Friday and kept avoiding it. She split it: Monday, choose three sources (30 minutes); Tuesday, outline (30 minutes); Wednesday, write 800 words in two Pomodoros; Thursday, write the rest; Friday morning, edit.</p>
<table>
<thead><tr><th>Day</th><th>Task</th><th>Time</th></tr></thead>
<tbody>
<tr><td>Monday</td><td>Choose three sources</td><td>30 min</td></tr>
<tr><td>Tuesday</td><td>Outline with headings</td><td>30 min</td></tr>
<tr><td>Wednesday</td><td>Write 800 words</td><td>2 x 25 min</td></tr>
<tr><td>Thursday</td><td>Write 1,200 words</td><td>3 x 25 min</td></tr>
<tr><td>Friday</td><td>Edit and submit</td><td>45 min</td></tr>
</tbody>
</table>

<h3>Case scenario</h3>
<p>Ben, a young freelance designer, avoided calling a client to ask for payment because he feared an argument. Three weeks passed and he was short of money. He decided to apply the 2-minute rule: write one polite message and send it. The client apologised and paid the next day. A month later, he delayed another difficult message and the issue grew bigger, because he did not use the same step. He then made a rule: unpleasant messages are the frog and are sent before 10:00 am. The lesson: small actions defeat fear, and unpleasant tasks do not get easier with waiting.</p>

<blockquote><p><strong>Quick check:</strong> What is the 2-minute rule?</p><p>Answer: Do a quick task at once, or commit to just two minutes of a big task, because starting is the hardest part.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What does "eat the frog" mean?</p><p>Answer: Do your hardest or most dreaded task first, so it does not weigh on you all day.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Name one task you are avoiding and write the feeling behind it.</li>
<li>Write the first action that takes under two minutes.</li>
<li>Set a timer for 25 minutes and work only on that task.</li>
<li>Take a 5-minute break and decide whether to do another round.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Procrastination usually comes from a feeling, not laziness.</li>
<li>Break big tasks into a first small step.</li>
<li>Use the 2-minute rule, Pomodoro and eating the frog.</li>
<li>Do unpleasant tasks early in the day.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which step helps most when studying with a tempting phone nearby?
- Keep it on the desk but try hard to ignore it
* Put it in another room or on silent in a bag
- Check it every five minutes to feel calm
- Use it as your timer
= Changing the environment works better than relying on willpower.

Q: A very busy WhatsApp group keeps interrupting your evening work. What is the best response?
- Leave all your friend groups for ever
- Read every message at once
* Mute it and read it at fixed times
- Reply to everyone immediately
= Muting and checking at set times keeps contact without constant interruption.

Q: Which of these are good ways to plan for power cuts? (choose all that apply)
* Keep printed notes or past papers ready for offline study
* Charge the phone, power bank and torch each evening
- Stop work for the night when the lights go out
* Download videos and notes when you have power and data
= A power cut should change the task, not end the work. Preparation makes this possible.

Q: Peter's area has power cuts every evening, and he stops studying each time. Which fix is best?
- Move to a new city
* Prepare a no-power folder with offline tasks and swap tasks when the lights go off
- Study only at midnight
- Wait for the government to fix the power
= Offline tasks keep study going. Waiting for perfect conditions wastes time.

Q: What is the purpose of the Pomodoro method?
- To study for as long as possible without a break
* To work in short focused rounds of about 25 minutes with short breaks
- To avoid doing hard tasks
- To replace sleep
= A timer limits the task, so it feels doable, and breaks protect attention.

Q: Lydia has a 2,000-word essay due Friday and has not started. Which plan is best?
- Wait until Thursday night
- Write all 2,000 words on Friday morning
* Split the work: sources, outline, draft in sessions, and edit before submission
- Ask a friend to write it
= Breaking it into dated small steps removes the fear of the big task and leaves time to edit.

Q: Ben fears asking a client for payment and keeps delaying. According to this module, what should he do?
- Wait until the client remembers
* Send one short polite message now, treating it as the frog
- Stop working for that client without a word
- Send an angry message
= Small actions beat fear, and unpleasant tasks grow worse with waiting.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Habits, energy and effective study',
            'summary' => 'Build habits and routines, protect sleep and energy, and use study methods that work.',
            'lessons' => [
                [
                    'title'   => 'Habits and routines: cue, routine, reward',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a new habit using a cue, a routine and a reward, and create morning and evening routines. This matters because habits run automatically, so the more of your important behaviour is a habit, the less discipline you need each day.</p>

<h3>How a habit works</h3>
<p>A <strong>habit</strong> is an action that you do almost without thinking. Every habit has three parts, called a loop.</p>
<ol>
<li><strong>Cue:</strong> the trigger, such as a time, place, feeling or an earlier action.</li>
<li><strong>Routine:</strong> the action itself.</li>
<li><strong>Reward:</strong> the good feeling that makes your brain want to repeat it.</li>
</ol>
<table>
<thead><tr><th>Cue</th><th>Routine</th><th>Reward</th></tr></thead>
<tbody>
<tr><td>After I brush my teeth at night</td><td>I write tomorrow's top three tasks</td><td>I tick a box on my wall chart and feel ready</td></tr>
<tr><td>When I arrive at the library</td><td>I put my phone in my bag and open my notes</td><td>A cup of tea after 50 minutes</td></tr>
<tr><td>After I close my shop</td><td>I write the day's sales</td><td>Seeing my profit grow in the notebook</td></tr>
</tbody>
</table>

<h3>Tips for building habits</h3>
<ul>
<li><strong>Start tiny.</strong> "Read one page" is a better start than "read for an hour".</li>
<li><strong>Attach to an existing habit.</strong> "After breakfast, I review my plan."</li>
<li><strong>Make it easy.</strong> Lay out your books or sports shoes the night before.</li>
<li><strong>Track it.</strong> Mark an X on a calendar for each day done and try not to break the chain. If you miss one day, never miss two.</li>
</ul>

<h3>Worked example: morning and evening routines</h3>
<p>Joan, a boarding-school student, built simple routines that fit the school bell.</p>
<table>
<thead><tr><th>Routine</th><th>Steps</th><th>Time</th></tr></thead>
<tbody>
<tr><td>Morning</td><td>Wake, wash, make bed, 10-minute revision of yesterday's notes</td><td>5:30 to 6:15 am</td></tr>
<tr><td>Evening</td><td>Pack bag, review tomorrow's timetable, write three things learned today, sleep by 10:00 pm</td><td>9:15 to 10:00 pm</td></tr>
</tbody>
</table>
<p>For a shift worker such as a nurse on night duty, the same idea works with different clock times: the "morning routine" starts when she wakes in the afternoon.</p>

<h3>Case scenario</h3>
<p>Samuel decided to run every morning at 5:30 am. He began with a full hour, felt sore after three days, and quit. He restarted with just 10 minutes of walking after his tea, tracked on a chart, and kept it for five weeks. In week six he travelled for a funeral and missed four days, then stopped altogether. He later realised he had no plan for travel days. He added a backup rule: on travel days do five minutes of stretching. The lesson: start small, track it, and make a "minimum version" for difficult days.</p>

<blockquote><p><strong>Quick check:</strong> What are the three parts of a habit loop?</p><p>Answer: Cue, routine and reward, because the cue starts the action and the reward makes it repeat.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why attach a new habit to an existing one?</p><p>Answer: The existing habit acts as a ready-made cue, so you remember to start.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose one habit, such as 10 minutes of revision after supper.</li>
<li>Write the cue, the routine and the reward.</li>
<li>Draw a 30-box chart and mark each day you do it.</li>
<li>Write a minimum version for tired or travel days.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A habit loop is cue, routine and reward.</li>
<li>Start tiny and attach the habit to something you already do.</li>
<li>Track progress and never miss twice.</li>
<li>Make short morning and evening routines that fit your real schedule.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Sleep, energy and your study and work rhythm',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to protect your sleep and arrange your hardest work for your best energy hours. This matters because a tired mind learns poorly, makes mistakes and finds every task harder, so staying up late to "gain time" often loses time.</p>

<h3>Sleep is part of the plan</h3>
<p>Most young people need about 8 to 10 hours of sleep. Sleep is when your brain stores what you studied. Practical rules:</p>
<ul>
<li>Go to bed and wake up at about the same time, even on weekends.</li>
<li>Avoid phone screens and heavy meals in the hour before sleep.</li>
<li>Avoid very strong tea, coffee or energy drinks in the afternoon and evening.</li>
<li>If you must study late for an exam, plan a catch-up sleep and do not repeat it many nights.</li>
</ul>

<h3>Food, water and movement</h3>
<p>Skipping breakfast, drinking little water and sitting for hours reduce focus. A simple meal, water and a 5-minute walk between study blocks keep energy steady.</p>

<h3>Find your best hours</h3>
<p>Most people have a high-energy period when hard thinking is easy, and a low-energy period when it is hard. Use your time audit to notice yours. Put difficult tasks (new topics, writing, numbers) in your peak time and easy tasks (tidying notes, errands, messages) in your low time.</p>
<table>
<thead><tr><th>Energy time</th><th>Best use</th></tr></thead>
<tbody>
<tr><td>Peak</td><td>Hardest topic, essay writing, problem solving</td></tr>
<tr><td>Medium</td><td>Reading, practice questions, group work</td></tr>
<tr><td>Low</td><td>Tidying, organising notes, errands, light chores, a short rest</td></tr>
</tbody>
</table>

<h3>Worked example: before and after for a university student</h3>
<p>Doreen studied Economics from 11:00 pm to 2:00 am, then slept until 9:30 am and missed 8:00 am lectures. After her audit she saw she was alert from 6:00 to 9:00 am.</p>
<table>
<thead><tr><th>Time</th><th>Before</th><th>After</th></tr></thead>
<tbody>
<tr><td>6:00 to 8:00 am</td><td>Asleep</td><td>Economics theory (peak energy)</td></tr>
<tr><td>8:00 am to 1:00 pm</td><td>Missed or sleepy lectures</td><td>Attends lectures awake</td></tr>
<tr><td>3:00 to 5:00 pm</td><td>Phone and rest</td><td>Practice questions, then exercise</td></tr>
<tr><td>9:30 pm</td><td>Start late-night study</td><td>Review plan, phone away</td></tr>
<tr><td>10:00 pm</td><td>Still studying</td><td>Sleep</td></tr>
</tbody>
</table>

<h3>Case scenario</h3>
<p>Brian, a shift worker in a factory in Jinja, rotated between day and night shifts and slept badly. He tried to study during any free hour and learned little. He decided to plan around the shift: after a night shift, sleep first in a dark, quiet room, then study for one hour in the evening. This worked for two weeks. When overtime appeared he skipped sleep for three nights, made mistakes at work and had to repeat a task. The lesson: protect sleep first; study and work quality depend on it, and rotating schedules need their own plan.</p>

<blockquote><p><strong>Quick check:</strong> Why is it unwise to sacrifice sleep to study?</p><p>Answer: Sleep helps memory and focus, so a tired mind learns less and makes more mistakes.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> When should you schedule your hardest task?</p><p>Answer: In your peak energy hours, because hard thinking is easier then.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>For three days, rate your energy from 1 to 5 at morning, noon, afternoon and evening.</li>
<li>Find your peak period.</li>
<li>Move one hard task into that period this week.</li>
<li>Set a fixed bedtime and phone-away time and follow it for five nights.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Sleep is when learning is stored; protect it.</li>
<li>Food, water and short walks keep energy steady.</li>
<li>Match hard tasks to peak energy and easy tasks to low energy.</li>
<li>Shift workers need a sleep-first plan that fits their roster.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Studying and working effectively: recall, spacing and deep work',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to study in ways that stick, and organise deep work blocks for study or business tasks. This matters because many students spend hours re-reading notes and remember little, so more time does not always mean more learning.</p>

<h3>Active recall</h3>
<p><strong>Active recall</strong> means testing yourself instead of only reading. Close the book and write or say what you remember. Use flashcards, practice questions and past papers, or explain the topic aloud to a friend. It feels harder than re-reading, and that effort is why it works.</p>

<h3>Spaced practice</h3>
<p><strong>Spaced practice</strong> means reviewing the same material across several days instead of in one long session. A simple pattern: review after 1 day, after 3 days, after 1 week and after 2 weeks. Short, repeated reviews beat one all-night cram.</p>
<table>
<thead><tr><th>Method</th><th>Works well</th><th>Works poorly</th></tr></thead>
<tbody>
<tr><td>Re-reading and highlighting</td><td>First look at a topic</td><td>Long-term memory</td></tr>
<tr><td>Active recall</td><td>Remembering and exams</td><td>None, though it feels harder</td></tr>
<tr><td>Spaced practice</td><td>Lasting knowledge</td><td>Last-minute cramming</td></tr>
<tr><td>Studying with a friend by quizzing</td><td>Finding gaps</td><td>Chatting without a plan</td></tr>
</tbody>
</table>

<h3>Deep work blocks</h3>
<p><strong>Deep work</strong> is 60 to 90 minutes of focus on one demanding task, with phone away and a clear goal for the block, such as "finish 15 questions" rather than "study". Plan one or two deep work blocks daily in your peak energy time. It applies to work too: a trader may use one block to plan stock purchases, a designer to finish one logo.</p>

<h3>Worked example: a week of spaced study for a boarding-school student</h3>
<table>
<thead><tr><th>Day</th><th>Topic</th><th>Method</th></tr></thead>
<tbody>
<tr><td>Monday</td><td>Learn Chapter 3 (Biology)</td><td>Read once, make 10 question cards</td></tr>
<tr><td>Tuesday</td><td>Chapter 3 review</td><td>Answer cards without notes (15 min)</td></tr>
<tr><td>Thursday</td><td>Chapter 3 review</td><td>Cards plus 5 past paper questions</td></tr>
<tr><td>Next Monday</td><td>Chapter 3 review</td><td>Teach it to a classmate</td></tr>
</tbody>
</table>

<h3>Case scenario</h3>
<p>Patience studied by re-reading her notes for six hours the night before a test and scored 40 percent. For the next test she began a week earlier, used question cards and tested herself for 30 minutes a day. She scored 68 percent. For the third test, she became confident and skipped her reviews for four days, so her score dropped to 55. She went back to the schedule and recovered in the end-of-term exam. The lesson: the method works only if you keep it going, and early starts beat long nights.</p>

<blockquote><p><strong>Quick check:</strong> What is active recall?</p><p>Answer: Testing yourself without looking at the notes, which strengthens memory more than re-reading.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why is spaced practice better than one long cram?</p><p>Answer: Repeated reviews over days build lasting memory, while cramming fades fast.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick one topic and make 10 question cards on paper.</li>
<li>Test yourself tomorrow without notes and mark the wrong ones.</li>
<li>Plan reviews after 3 days and 1 week in your diary.</li>
<li>Schedule one 60-minute deep work block with a clear goal and phone away.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Test yourself instead of only re-reading.</li>
<li>Space reviews across days and weeks.</li>
<li>Use deep work blocks with one clear goal.</li>
<li>Begin early; cramming is a weak plan.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What are the three parts of a habit loop?
- Plan, action, result
* Cue, routine, reward
- Goal, deadline, penalty
- Motivation, mood, effort
= The cue triggers the routine and the reward makes you want to repeat it.

Q: Which is the best way to begin a new reading habit?
- Read for two hours every night from day one
* Read one page after supper and track each day
- Wait until you feel inspired
- Buy many books first
= Tiny, attached habits are easy to keep and can grow naturally.

Q: Which of these help protect your sleep? (choose all that apply)
* A fairly regular bedtime and wake time
* Phone away in the last hour before sleep
- Energy drinks late at night
- Skipping meals so you stay awake
= Regular timing and fewer stimulants help sleep, which in turn supports memory and focus.

Q: Doreen studies from 11 pm to 2 am but is alert at 6 am and misses morning lectures. What is the best fix?
- Study until 4 am
* Sleep earlier, study hard topics at 6 to 8 am and attend lectures awake
- Skip lectures completely
- Drink more energy drinks
= Using peak-energy hours and protecting sleep improves both study and attendance.

Q: Which study method is the strongest for remembering?
- Highlighting a page many times
* Testing yourself with questions, spread over several days
- Reading the notes once the night before
- Copying notes without thinking
= Active recall and spaced practice build lasting memory.

Q: Patience scored 40 percent after six hours of re-reading the night before. Which plan should she use for the next test?
- Re-read for eight hours
- Skip the test
* Start a week earlier, make question cards and test herself for 30 minutes a day
- Copy a friend's notes only
= Short, spaced self-testing sessions beat one cram.

Q: Samuel quit his morning run after missing four days on a trip. What would have helped most?
- A longer run
* A minimum version for hard days, such as five minutes of stretching
- A stricter punishment for missing
- Choosing a different goal each week
= A minimum version keeps the chain alive in difficult periods.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: People, deadlines and money',
            'summary' => 'Say no politely, handle family and social demands, manage group work and deadlines, and practise basic money discipline.',
            'lessons' => [
                [
                    'title'   => 'Saying no and handling family and social demands',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to refuse or delay requests politely while keeping good relationships. This matters because many young people lose study and work time to favours, visits, chats and events, and every "yes" to one thing is a "no" to something else.</p>

<h3>Why it is hard to say no</h3>
<p>In many East African communities, being helpful, respectful and available to family and friends is a strong value, and it should stay so. The goal is not to become selfish. The goal is to be helpful without sacrificing your most important commitments. Sometimes the right answer is not a flat "no" but "not now" or "yes, in a different way".</p>

<h3>Four kinds of reply</h3>
<table>
<thead><tr><th>Type</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Kind no</td><td>"Thank you for asking. I cannot join this time because I have an exam on Monday."</td></tr>
<tr><td>Not now</td><td>"I cannot come this afternoon, but I am free on Saturday at 3:00 pm."</td></tr>
<tr><td>Partial yes</td><td>"I cannot stay all evening, but I can come for one hour."</td></tr>
<tr><td>Alternative</td><td>"I cannot go with you, but my cousin may be able to help."</td></tr>
</tbody>
</table>

<h3>A simple method</h3>
<ol>
<li><strong>Pause.</strong> Say "Let me check my plan and tell you tonight". This stops quick yeses.</li>
<li><strong>Check your priorities.</strong> Does this fit your big rocks and your week?</li>
<li><strong>Reply kindly and clearly.</strong> Give a short reason, not a long apology.</li>
<li><strong>Offer an alternative where it makes sense.</strong></li>
<li><strong>Keep your word.</strong> If you do say yes, do it well.</li>
</ol>

<h3>Worked example: a university student off campus</h3>
<p>Ivan has a study block on Saturday 9:00 am to 12:00 noon. A friend invites him to help move house at 9:00 am. Before: he says yes and loses the block. After: he replies, "I have a study block until noon. I can join you at 1:00 pm and help carry the last items." The friend agrees. Ivan stays disciplined, and the friendship is not hurt.</p>

<h3>Family demands</h3>
<p>Family needs, such as caring for a sick relative or a funeral, are often truly important and urgent. Use the matrix: if it is Box 1, give it your time and adjust your plan. Then tell your teacher or manager early, and reschedule your work instead of hiding the problem. Share your timetable with family so they understand your study hours.</p>

<h3>Case scenario</h3>
<p>Rose, a young trader, always said yes when relatives asked for small loans from her business money. By the end of the month she could not pay her supplier. She decided to set a monthly "family support" amount of UGX 50,000 and explained it kindly: "This is what I can give each month; the rest has to stay in the business." Relatives accepted it, though one was upset for a while. A few months later, she broke her own rule when a cousin pressed her, and she had to delay her stock purchase. She realised a rule works only if she keeps it. The lesson: set clear limits ahead of time, explain them kindly, and stick to them.</p>

<blockquote><p><strong>Quick check:</strong> What can you say to avoid agreeing too quickly?</p><p>Answer: "Let me check my plan and tell you tonight", which gives you time to think.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is a partial yes?</p><p>Answer: Agreeing to a smaller or shorter part of the request, such as one hour instead of the whole evening.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List three requests you struggle to refuse.</li>
<li>Write a kind reply for each using one of the four types.</li>
<li>Practise saying one aloud to a friend.</li>
<li>Use the pause sentence the next time someone asks for your time.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Every yes costs time; protect your big rocks.</li>
<li>Use kind no, not now, partial yes or alternative.</li>
<li>Pause before answering and give a short reason.</li>
<li>Real family emergencies are Box 1; adjust and tell people early.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Deadlines and group work',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to meet deadlines reliably and run group work fairly. This matters because deadlines and teamwork appear in school, university and every workplace, and your reputation grows when people can count on you.</p>

<h3>Managing deadlines</h3>
<ol>
<li><strong>Write the real deadline</strong> in your diary, including the hour.</li>
<li><strong>Set your own earlier deadline,</strong> at least a day or two before. This is your safety margin.</li>
<li><strong>Break the work into stages</strong> with dates: research, outline, draft, edit, submit.</li>
<li><strong>Start early,</strong> even for 20 minutes. A first draft makes the rest easier.</li>
<li><strong>Warn early if there is a problem.</strong> Tell your teacher or manager before the deadline, with a clear new date proposal.</li>
</ol>

<h3>Planning backwards: example</h3>
<table>
<thead><tr><th>Date</th><th>Stage</th></tr></thead>
<tbody>
<tr><td>Friday week 1</td><td>Topic and sources chosen</td></tr>
<tr><td>Wednesday week 2</td><td>Outline approved by supervisor</td></tr>
<tr><td>Monday week 3</td><td>First draft finished</td></tr>
<tr><td>Wednesday week 3</td><td>Edited version done (own deadline)</td></tr>
<tr><td>Friday week 3</td><td>Official submission</td></tr>
</tbody>
</table>

<h3>Group work that works</h3>
<ul>
<li><strong>Hold a first meeting</strong> and write who does what, by when.</li>
<li><strong>Use one shared place</strong> such as a WhatsApp group or shared document, and agree a reply time like 24 hours.</li>
<li><strong>Divide work by strengths,</strong> but keep fair amounts for everyone.</li>
<li><strong>Set an internal deadline</strong> two days before the real one, for combining the parts.</li>
<li><strong>Check progress</strong> early, not on the last night.</li>
</ul>
<table>
<thead><tr><th>Task</th><th>Person</th><th>Due</th></tr></thead>
<tbody>
<tr><td>Research background</td><td>Amina</td><td>Tuesday</td></tr>
<tr><td>Collect survey data</td><td>Brian and Joy</td><td>Thursday</td></tr>
<tr><td>Write the first draft</td><td>Amina</td><td>Monday</td></tr>
<tr><td>Slides and rehearsal</td><td>Joy</td><td>Wednesday</td></tr>
</tbody>
</table>

<h3>Worked example: a boarding-school project</h3>
<p>Four students at a boarding school had a Geography project due on Friday. Instead of waiting for prep on Thursday, they met on Monday, divided sections, and set a Wednesday evening review during prep. Because of the early check, they found that one member had misunderstood his part and had time to fix it.</p>

<h3>Case scenario</h3>
<p>Kevin was in a group where one member, Tom, kept saying he would send his part tomorrow. Kevin waited until the last night and then found Tom had sent nothing. The group submitted an incomplete assignment. In the next project, Kevin set a rule: parts due two days before the deadline, with a check-in on the first day. When Tom was late again, the group knew by Wednesday and Kevin spoke to him privately and politely, and Tom finished his part with help. However, Kevin was also late with one of his own parts that time, and he apologised and took responsibility. The lesson: early checks, clear tasks and honest communication protect the group, and they apply to you too.</p>

<blockquote><p><strong>Quick check:</strong> Why set your own deadline before the official one?</p><p>Answer: It gives a safety margin for problems such as power cuts, illness or slow internet.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What should a group agree at its first meeting?</p><p>Answer: Who does which task, by what date, and how members will communicate.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Take a real upcoming deadline and work backwards with five dated stages.</li>
<li>Add your own early deadline two days before the real one.</li>
<li>For any group task, write a table with person, task and due date.</li>
<li>Share it with the group and agree a check-in date.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Set an early personal deadline and work in stages.</li>
<li>Warn people early if a problem appears.</li>
<li>In groups, write who does what and by when.</li>
<li>Check progress early, not on the final night.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Money discipline basics',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to make a simple monthly budget and use three habits that protect your money. This matters because money problems cause stress that harms study and work, and the same discipline that manages time also manages money.</p>

<h3>Why money and time are linked</h3>
<p>Both are limited, both disappear if you do not track them, and both are spent best when you decide in advance. A time audit shows where hours go; a spending record shows where shillings go.</p>

<h3>A simple budget</h3>
<ol>
<li><strong>List your income</strong> for the month: allowance, wages, business profit, gifts.</li>
<li><strong>List needs first:</strong> rent, food, transport, school fees, airtime and data.</li>
<li><strong>Add savings</strong> as a fixed amount, paid first, not what remains.</li>
<li><strong>Allow a small fun amount</strong> so the plan is realistic.</li>
<li><strong>Record every spend</strong> for a month in a notebook or phone note and compare with the plan.</li>
</ol>

<h3>Worked example: a university student</h3>
<table>
<thead><tr><th>Item</th><th>Planned (UGX)</th><th>Actual (UGX)</th></tr></thead>
<tbody>
<tr><td>Income (allowance + tutoring)</td><td>400,000</td><td>400,000</td></tr>
<tr><td>Rent</td><td>150,000</td><td>150,000</td></tr>
<tr><td>Food</td><td>120,000</td><td>135,000</td></tr>
<tr><td>Transport</td><td>40,000</td><td>55,000</td></tr>
<tr><td>Airtime and data</td><td>20,000</td><td>35,000</td></tr>
<tr><td>Savings</td><td>40,000</td><td>10,000</td></tr>
<tr><td>Fun and other</td><td>30,000</td><td>15,000</td></tr>
</tbody>
</table>
<p>Looking at the table, data and transport went over, so savings fell short. The student made a plan: buy a weekly data bundle only and walk short distances. These are the same checks as in a time audit.</p>

<h3>Three protecting habits</h3>
<ul>
<li><strong>Pay yourself first.</strong> Move savings away on the day money arrives, for example to a separate mobile money wallet or savings group.</li>
<li><strong>Wait 24 hours</strong> before any non-essential purchase above a set amount.</li>
<li><strong>Keep an emergency amount,</strong> even a small one, to avoid borrowing for small shocks.</li>
</ul>

<h3>Worked example: a young trader</h3>
<p>Moses separates money into three envelopes every evening: stock money, savings and personal use. This stops him from mixing business money with daily spending, a common cause of failing small businesses.</p>

<h3>Case scenario</h3>
<p>Lillian, a shift worker, received her salary and spent heavily in the first week on clothes and treating friends. By week three she had no money for transport and borrowed from a loan app with high charges. She decided to budget, save UGX 30,000 first, and set a weekly spending limit. It worked for two months. Then a sale tempted her and she overspent again. She did not give up: she applied the 24-hour rule, found she no longer wanted the item, and returned to the plan. The lesson: money discipline is the same as time discipline: plan first, track honestly, and recover after slips.</p>

<blockquote><p><strong>Quick check:</strong> What does "pay yourself first" mean?</p><p>Answer: Set aside savings as soon as you receive money, before spending on wants.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why wait 24 hours before a non-essential purchase?</p><p>Answer: The urge often fades, so you avoid spending on impulse.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write your income and needs for this month.</li>
<li>Decide a savings amount and move it first when money arrives.</li>
<li>Record every spend for one week.</li>
<li>Find the biggest overspend and plan one change.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A budget lists income, needs, savings and a little fun.</li>
<li>Save first, then spend what remains.</li>
<li>Track spending honestly, as you track time.</li>
<li>Use the 24-hour rule for non-essential purchases.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A friend asks you to help this afternoon, but you have a study block. Which reply fits this module best?
- Say yes and skip the block
- Ignore the message
* "I have a study block until noon. I can come at 1:00 pm."
- Say you are ill
= A "not now" or partial yes protects your priority and keeps the friendship.

Q: What is a good first response to a request when you are unsure?
* "Let me check my plan and tell you tonight."
- "Yes" immediately
- "No" in an angry voice
- Silence
= A pause helps you decide against your priorities instead of reacting.

Q: Which are good practices for deadlines? (choose all that apply)
* Set your own earlier deadline
* Break the work into dated stages
- Start on the last night to feel pressure
* Warn the teacher or manager early if a problem appears
= Early margins, stages and honest communication prevent late work.

Q: Kevin's group submitted incomplete work because one member never sent his part. What would have prevented this?
- Doing nothing and hoping
* A first meeting with clear tasks and dates, and an early check-in
- Waiting for the last night
- Choosing a bigger group
= Written roles, internal deadlines and early checks reveal problems while there is time to fix them.

Q: A student's budget shows data and transport over plan, and savings short. What is the best step?
- Stop recording spending
* Pay savings first next month and set weekly limits for data and transport
- Borrow from a loan app
- Ignore the table
= Paying yourself first and setting limits fixes the problem shown by the record.

Q: Rose gives relatives money from her business whenever asked, and now cannot pay her supplier. What should she do?
- Stop talking to relatives
* Set a clear monthly family support amount and explain it kindly
- Close her business
- Hide her money
= A clear limit protects the business and allows kindness within what she can afford.

Q: What does "pay yourself first" mean?
- Buy yourself a reward before bills
* Move savings aside when money arrives, before other spending
- Pay others last
- Spend all money on the first day
= Saving first makes savings a fixed habit and not what is left over.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 6
        [
            'title'   => 'Module 6: Setbacks and your personal system',
            'summary' => 'Recover from failures, put the course together into your own system and run a 30-day discipline challenge.',
            'lessons' => [
                [
                    'title'   => 'Setbacks: getting back on track',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to recover from a bad day or week without guilt or giving up. This matters because everyone slips; the difference between people who succeed and people who quit is how fast they return.</p>

<h3>Why slips happen</h3>
<p>Typical reasons are illness, a family emergency, a change of routine, exhaustion, overplanning, or a goal that no longer fits. A slip is information, not a verdict on your character. The harmful thing is the "what the hell" effect: "I missed one day, so I may as well quit".</p>

<h3>A five-step recovery plan</h3>
<ol>
<li><strong>Stop blaming.</strong> Speak to yourself as you would to a good friend.</li>
<li><strong>Find the cause.</strong> Ask what happened, without excuses or drama.</li>
<li><strong>Shrink the plan.</strong> Go back to the minimum version of your habit for a few days.</li>
<li><strong>Fix the system.</strong> Change one thing, such as bedtime, location or reminder.</li>
<li><strong>Restart today.</strong> Not Monday, not next month. Do 10 minutes now.</li>
</ol>

<h3>The never-miss-twice rule</h3>
<p>Missing one day is a small event. Missing two begins a new habit of not doing it. If you miss once, do the minimum next day, even for two minutes.</p>

<h3>Worked example: a recovery week</h3>
<p>Martha, a student, fell ill for a week and missed all revision. She had three weeks until exams.</p>
<table>
<thead><tr><th>Day</th><th>Plan</th></tr></thead>
<tbody>
<tr><td>Day 1</td><td>Rest, then 15 minutes of revision; update the term map</td></tr>
<tr><td>Day 2</td><td>45 minutes on the most important subject</td></tr>
<tr><td>Day 3</td><td>Two 45-minute blocks; ask a classmate for notes</td></tr>
<tr><td>Days 4 to 7</td><td>Back to normal timetable with one catch-up block</td></tr>
</tbody>
</table>
<p>She did not try to make up all lost hours in two days; that plan would have exhausted her.</p>

<h3>Case scenario</h3>
<p>Charles, a boarding-school student, scored 35 percent on a test after a week of poor preparation and decided he was "just not clever". He stopped studying for a fortnight. His teacher showed him his test: most marks were lost in two topics. Charles made a recovery plan, targeting those two topics for 30 minutes a day, and his next score was 58 percent. He then relaxed too soon, and the following test fell again to 45 percent. He learned to keep the daily habit even after improvement. The lesson: a failure is feedback; use it to adjust the plan, and do not stop when things improve.</p>

<blockquote><p><strong>Quick check:</strong> What is the "never miss twice" rule?</p><p>Answer: After one missed day, do at least a minimum version the next day so the habit does not break.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> After a setback, should you try to make up all lost time at once?</p><p>Answer: No, because it leads to exhaustion; return with a smaller plan and build up.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Recall a recent slip and write its real cause.</li>
<li>Write one change to your system that would prevent it.</li>
<li>Write the minimum version of your key habit.</li>
<li>Do that minimum version today.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Slips are normal; quitting is the real danger.</li>
<li>Find the cause, shrink the plan and fix the system.</li>
<li>Never miss twice.</li>
<li>Restart today instead of waiting for a perfect Monday.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Building your personal discipline system',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to join the course ideas into one simple personal system you can run every week. This matters because tools only help when they work together, and a short, clear system is easier to follow than ten separate tips.</p>

<h3>The six parts of a system</h3>
<ol>
<li><strong>Purpose:</strong> one sentence on what you are working towards this term or year.</li>
<li><strong>Priorities:</strong> two or three big rocks per week from Box 2 of the matrix.</li>
<li><strong>Plans:</strong> a term map, a weekly grid and a nightly top three.</li>
<li><strong>Environment:</strong> phone settings, study space, offline tasks for power cuts.</li>
<li><strong>Habits:</strong> a morning routine, an evening routine and a sleep time.</li>
<li><strong>Review:</strong> a 20-minute weekly review and a monthly check.</li>
</ol>

<h3>One-page system sheet</h3>
<table>
<thead><tr><th>Part</th><th>My choice (example for a university student)</th></tr></thead>
<tbody>
<tr><td>Purpose</td><td>Graduate with a good second class degree and find work in banking</td></tr>
<tr><td>Weekly big rocks</td><td>Two library blocks for assignments; one revision block</td></tr>
<tr><td>Tools</td><td>Phone calendar for deadlines, notebook for daily top three</td></tr>
<tr><td>Environment</td><td>Phone silent in bag during study; no-power folder</td></tr>
<tr><td>Habits</td><td>Phone off at 10:00 pm; 6:30 am wake; 10 minutes of recall after supper</td></tr>
<tr><td>Review</td><td>Sunday 5:00 pm for 20 minutes</td></tr>
</tbody>
</table>

<h3>Worked example: a shift worker with study goals</h3>
<p>Sandra works as a hotel receptionist on rotating shifts in Kampala and studies for a part-time diploma. Her system is built around the roster, which she receives each Friday.</p>
<table>
<thead><tr><th>Shift</th><th>Study plan</th><th>Sleep plan</th></tr></thead>
<tbody>
<tr><td>Morning shift (6 am to 2 pm)</td><td>Study 5 to 6:30 pm</td><td>Sleep by 10 pm</td></tr>
<tr><td>Afternoon shift (2 pm to 10 pm)</td><td>Study 9 to 11 am</td><td>Sleep 12 midnight</td></tr>
<tr><td>Night shift</td><td>Short review before shift; no study after</td><td>Sleep 8 am to 3 pm</td></tr>
</tbody>
</table>

<h3>Keep the system simple</h3>
<p>Test your system by asking, "Could I explain it in one minute?" If not, remove something. A simple system you follow at 70 percent beats a perfect system you never use. Review it each month and change only one part at a time.</p>

<h3>Case scenario</h3>
<p>Emmanuel, a graduate seeking work, designed a complex system with six apps, colour codes and 15 daily habits. In week one he followed everything, and in week two he was exhausted and abandoned it. After a talk with a mentor he reduced it to a notebook with a top three, one deep work block and a Sunday review. He used it for two months and got a job interview. But one week he stopped the Sunday review and drifted for ten days. He saw that the review is the part that holds the system together. The lesson: keep the system small, and never skip the review.</p>

<blockquote><p><strong>Quick check:</strong> What are the six parts of the personal system?</p><p>Answer: Purpose, priorities, plans, environment, habits and review, which together guide and maintain your work.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why is a simple system better than a complicated one?</p><p>Answer: You are more likely to follow it consistently, and consistency produces results.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Copy the six-part table into your notebook.</li>
<li>Fill in each part with your own real choices.</li>
<li>Put the weekly review in your calendar with a reminder.</li>
<li>Read it aloud; remove anything you would not do on a tired day.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A system joins purpose, priorities, plans, environment, habits and review.</li>
<li>Shift workers build the system around the roster.</li>
<li>Keep it simple enough to explain in a minute.</li>
<li>The weekly review keeps it alive.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Your 30-day discipline challenge',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to design and run a 30-day challenge that puts everything in this course into practice. This matters because lasting change comes from doing, and 30 days is long enough to feel a difference and short enough to stay committed.</p>

<h3>Choose a small, clear challenge</h3>
<p>Pick one or two habits, not ten. Make each specific and measurable. Examples:</p>
<ul>
<li>"Study 45 minutes at 6:00 pm daily with the phone away."</li>
<li>"Write tomorrow's top three before 9:30 pm."</li>
<li>"Sleep by 10:30 pm on school or work nights."</li>
<li>"Record all spending every evening."</li>
</ul>

<h3>The four weeks</h3>
<table>
<thead><tr><th>Week</th><th>Focus</th><th>Check</th></tr></thead>
<tbody>
<tr><td>Week 1</td><td>Start tiny; set up environment and tracker</td><td>Did I do it at least five of seven days?</td></tr>
<tr><td>Week 2</td><td>Make it regular; attach to a cue</td><td>Where did I slip, and why?</td></tr>
<tr><td>Week 3</td><td>Increase slightly; add a second habit if steady</td><td>Is my energy and sleep holding?</td></tr>
<tr><td>Week 4</td><td>Protect the habit during busy days; plan the next 30 days</td><td>What worked best, what will I keep?</td></tr>
</tbody>
</table>

<h3>Tools for the challenge</h3>
<ol>
<li><strong>A tracking chart:</strong> 30 boxes on one page, ticked daily.</li>
<li><strong>An accountability partner:</strong> a friend, sibling or classmate you message each evening with "done" or "missed, because...".</li>
<li><strong>A reward plan:</strong> a small treat after 7, 14 and 30 days.</li>
<li><strong>A minimum version:</strong> what you will do on a very bad day.</li>
<li><strong>Weekly review:</strong> Sunday, 20 minutes.</li>
</ol>

<h3>Worked example: a young trader's 30 days</h3>
<p>Grace sells vegetables in a Mbale market. Her challenge: record sales and costs every evening for 30 days, and wake at 5:00 am to reach the market before the rush. Her minimum version: write only the total of sales and costs. Her partner is her sister, who gets a text each night. At the end she has a month of data showing that tomatoes earned the most, and she changes her stock purchases.</p>

<h3>Case scenario</h3>
<p>Peter began a 30-day challenge to study 60 minutes daily. He did well for 12 days, then a cousin's wedding took four days and he lost the chain. He felt he had ruined the challenge and wanted to stop. His accountability partner reminded him of the never-miss-twice rule and the minimum version. Peter restarted with 15 minutes the next morning, finished the 30 days (with 26 days completed) and improved his test results. He noted that he should have planned a minimum version for wedding days in advance. The lesson: a challenge is not about being perfect; it is about returning every time.</p>

<blockquote><p><strong>Quick check:</strong> How many habits should a 30-day challenge include at the start?</p><p>Answer: One or two, because focus on a few habits makes success more likely.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is the use of an accountability partner?</p><p>Answer: A person you report to, which makes you more likely to keep your promise.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose one or two specific habits and write them with a time.</li>
<li>Draw a 30-box tracker and put it where you see it daily.</li>
<li>Pick an accountability partner and send them your plan today.</li>
<li>Write your minimum version and a small reward for day 7.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Start with one or two clear, small habits.</li>
<li>Track daily and report to a partner.</li>
<li>Use a minimum version for bad days.</li>
<li>Review weekly and plan the next 30 days.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: You missed your study habit yesterday. According to this module, what is the best action today?
- Wait until next Monday to restart
* Do at least a minimum version today
- Double tomorrow's hours as a punishment
- Decide you are not disciplined
= Never miss twice. A small action today stops one slip from becoming a new habit.

Q: Martha lost a week of revision through illness and has three weeks to the exams. What is the best plan?
- Study 12 hours a day for two days
* Restart with a short session today, then build up while updating the term map
- Give up the subject
- Skip all sleep
= A gradual restart avoids exhaustion and brings back the routine.

Q: Which are parts of a personal discipline system? (choose all that apply)
* Priorities
* Weekly review
- Waiting for motivation
* Environment setup
= A system combines purpose, priorities, plans, environment, habits and review. Waiting for motivation is not a system part.

Q: Emmanuel built a system with six apps and 15 daily habits and quit after a week. What is the best fix?
- Add more apps
* Reduce to a notebook top three, one deep work block and a weekly review
- Ignore planning
- Copy a friend's exact system
= A simple system you can follow every day beats a complicated one you abandon.

Q: Sandra works rotating shifts and studies for a diploma. Which approach fits best?
- Study at the same time every day regardless of shift
- Study only on days off
* Build the study and sleep plan around each week's roster
- Give up studying
= Shift workers need a plan that changes with the roster, with sleep protected.

Q: Which is the best 30-day challenge for a beginner?
- Wake at 4 am, study 5 hours, exercise 2 hours, stop all social media and fast
- Do whatever feels right each day
* Study 45 minutes at 6 pm daily with the phone away, tracked on a chart
- Change goals every three days
= One small, specific, tracked habit is the best way to start.

Q: Peter lost four days of his 30-day challenge because of a wedding. What should he do?
* Restart with a minimum version the next day and plan minimum versions for busy days
- Quit the challenge
- Start again from day 1 and hide the failure
- Blame his family
= Returning quickly matters more than a perfect record, and minimum versions protect the chain.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What is the best definition of discipline?
- A feeling of excitement about a goal
* Doing what you planned, whatever your mood, supported by habits and systems
- Being strict with other people
- A talent you are born with
= Discipline is a learnable skill of following your plan.

Q: Why do a three-day time audit?
- To impress your teacher
* To see honestly where your hours go, including a weekend day
- To decide which friends to avoid
- To plan your marriage
= An audit gives the real data you need before you change your schedule.

Q: Which task belongs in Box 2 of the urgent and important matrix?
- A phone call that interrupts you and is not important
- An assignment due in two hours
* Steady revision for a test three weeks away
- Endless scrolling
= Box 2 is important but not urgent, and it is where long-term results are built.

Q: What does the big rocks idea teach?
- Do small tasks first
* Schedule the most important tasks first and fit small tasks around them
- Avoid hard tasks
- Only plan weekends
= Big rocks first ensures priorities get time before small tasks fill the day.

Q: Which is the best goal?
- I will be better at science
* I will do 10 science practice questions on four evenings a week for this term
- I will try my best
- I will do everything
= It is specific, measurable and has a time frame.

Q: Plan your Monday. Fixed: lectures 8 am to 1 pm. Tasks: submit report due Tuesday 9 am; revise for a test in two weeks; reply to non-urgent messages; watch a series. Which order of effort is best?
- Series, messages, revision, report
* Report, revision slot, messages in a short block, series last
- Messages, series, report, revision
- Revision, series, messages, report
= Do the urgent important task, protect the important one, limit the less important, and leave the least valuable for last.

Q: When should you do your daily planning?
- During the first lecture
- Only at the end of the term
* The evening before, in about five minutes
- Never
= Planning the night before lets you start the day with clear priorities.

Q: How many top tasks should you choose for each day?
- Ten
- As many as possible
* About three
- None
= A short list can be finished; long lists lead to discouragement.

Q: What is the purpose of a term map?
* To show deadlines and when you must start work
- To list friends' names
- To track expenses
- To replace attending class
= A map reveals clashes early and allows early starts.

Q: Which item suits a calendar best?
- A vague idea for a hobby
* A deadline at 9:00 am on Friday
- A list of random thoughts
- A shopping wish with no date
= Items with a date or time should be in the calendar with reminders.

Q: Naomi lost her phone and with it all her notes and dates. What should she have done?
- Used no planning tools
* Backed up her calendar and kept a paper copy of important dates
- Added more reminders
- Stored everything in her head
= A backup protects your planning from loss and theft.

Q: Which step helps most to reduce phone distraction while studying?
- Put the phone on the desk face-up
* Put it in another room or on silent in a bag
- Check it every few minutes
- Use it as a dictionary
= The environment should do the work so you need less willpower.

Q: Which are good ways to prepare for power cuts? (choose all that apply)
* Charge devices every evening
* Keep printed notes or past papers ready
- Wait for the lights to return before working
* Download notes and videos when power and data are available
= A power cut should switch your task to an offline one, not stop you.

Q: Which technique asks you to work for about 25 minutes, then rest for 5?
- Eating the frog
- The matrix
* Pomodoro
- Big rocks
= The Pomodoro method uses short timed rounds with breaks.

Q: What does "eat the frog" mean?
- Do easy tasks first
* Do your most dreaded or hardest task first
- Skip hard tasks
- Delegate everything
= Doing the hardest task first removes the weight of it for the rest of the day.

Q: Ben avoids asking a client for payment. Which step is best?
- Wait for the client to pay
- Write an angry message
* Send one polite message now before 10 am
- Stop work forever
= Small action beats fear, and delay makes unpleasant tasks worse.

Q: Which is the correct order of a habit loop?
- Reward, cue, routine
* Cue, routine, reward
- Routine, reward, cue
- Cue, reward, routine
= The cue triggers the routine and the reward makes it repeat.

Q: Samuel quit running after missing four days on a trip. What would help most next time?
- Running 2 hours a day
* A minimum version for hard days
- Never travelling
- Quitting early
= A minimum version keeps the habit alive on difficult days.

Q: Which supports good study energy? (choose all that apply)
* Regular sleep times
* Drinking water and a short walk between blocks
- Energy drinks late at night
- Skipping meals to save time
= Regular sleep, hydration and movement keep focus, while stimulants late at night harm sleep.

Q: Doreen studies 11 pm to 2 am but is alert at 6 am. What is the best change?
- Study until 4 am
* Sleep earlier and study hard topics at 6 to 8 am
- Skip all study
- Use more energy drinks
= Using peak hours and protecting sleep improves learning.

Q: Which method is strongest for remembering for an exam?
- Highlighting the whole page
* Self-testing with questions, repeated over several days
- Reading once the night before
- Watching a video without notes
= Active recall and spaced practice are strongly linked to lasting memory.

Q: What is a deep work block?
- A time to chat with friends while studying
* 60 to 90 minutes of focus on one demanding task with a clear goal and phone away
- A very long all-night session
- A time to answer messages
= Deep work needs one task, one goal and no interruptions.

Q: A relative asks you to help at a time you planned to study. What is the best first response?
- Say yes at once
* "Let me check my plan and tell you tonight."
- Ignore them
- Say you are sick
= A pause lets you compare the request with your priorities.

Q: Which is a "partial yes"?
- "No, never."
* "I cannot stay all evening, but I can come for one hour."
- "Yes, anything you want."
- "Ask someone else, I do not care."
= A partial yes gives a smaller part of what is asked while protecting your priorities.

Q: Kevin's group did not finish because one member sent nothing. What would help in the next project?
- Hoping people work harder
* Clear tasks with dates, an internal deadline and an early check-in
- Doing everyone's work alone at the end
- Choosing friends only
= Written roles and early checks reveal problems while they can be fixed.

Q: Why set your own deadline earlier than the real one?
- To make others work harder
* To create a safety margin for illness, power cuts or slow internet
- To stop planning
- Because real deadlines never matter
= The margin protects you from unplanned problems.

Q: A student plans to save UGX 40,000 from a UGX 400,000 allowance. Which habit best protects the saving?
- Save what is left at month end
* Pay the saving first when the money arrives
- Spend first and hope
- Borrow to save
= Paying yourself first makes saving automatic and protected.

Q: You missed your habit yesterday. What does the never-miss-twice rule suggest?
- Quit
* Do at least a minimum version today
- Punish yourself with a double session
- Wait for next month
= One miss is a slip; two begin a new habit of not doing it.

Q: Which are parts of a personal discipline system? (choose all that apply)
* Priorities and plans
* Habits and environment
* A weekly review
- Waiting for inspiration
= A system joins purpose, priorities, plans, environment, habits and review.

Q: Emmanuel's system has six apps and 15 habits and he quit in two weeks. What is the best fix?
- Add another app
* Simplify to a top three list, one deep work block and a weekly review
- Never plan again
- Buy a new phone
= Simple systems are easier to follow consistently.
QUIZ,
];
