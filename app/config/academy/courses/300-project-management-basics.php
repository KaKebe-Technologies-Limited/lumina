<?php
return [
    'slug'        => 'project-management-basics',
    'title'       => 'Project Management Basics',
    'category'    => 'business',
    'level'       => 'beginner',
    'icon'        => 'target',
    'sort'        => 300,
    'summary'     => 'Plan, run and finish projects that deliver: scope, goals, schedules, budgets, risks, teams and reporting, using free tools and a worked youth ICT club example.',
    'description' => <<<'HTML'
<p>Every community group, school, church, business and NGO runs projects: a training week, a borehole, a tree-planting day, a new website, a fundraising dinner. Projects that are planned and tracked well finish on time and make a real difference. Projects that are not often run out of money, lose volunteers and disappoint the people they were meant to serve.</p>
<p>This course teaches the basic ideas and tools of project management in plain language. You will learn how to define a project, write clear goals, break work into tasks, build a schedule and budget, manage risks, communicate, track progress with simple boards, and close a project properly with lessons learned and a report for donors.</p>
<p>No experience is needed. You can use a notebook, a spreadsheet or a free online tool. The final module walks through a complete plan for a youth ICT club so you can see every step together.</p>
HTML,
    'outcomes'    => [
        'Explain how a project differs from routine work and describe its life cycle',
        'Identify stakeholders and write clear success criteria',
        'Write SMART goals and a scope statement',
        'Build a work breakdown structure, estimate effort and draw a simple schedule',
        'Prepare a basic project budget in a spreadsheet',
        'Create a risk register, a communication plan and run effective meetings',
        'Track work on a kanban board and handle changes and delays',
        'Close a project with a handover, lessons learned and a simple report to donors',
    ],
    'requirements'=> [
        'No experience needed',
        'A notebook, or a computer or smartphone with a spreadsheet app (free tools are fine)',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Project Management Institute (PMI)', 'url' => 'https://www.pmi.org'],
        ['title' => 'Scrum Guides', 'url' => 'https://scrumguides.org'],
        ['title' => 'Google Sheets help', 'url' => 'https://support.google.com/docs'],
        ['title' => 'Trello guide (free kanban boards)', 'url' => 'https://trello.com/guide'],
    ],
    'modules' => [
        // ------------------------------------------------------------------ MODULE 1
        [
            'title'   => 'Module 1: What a project is',
            'summary' => 'Understand projects, their life cycle, the people involved and what success looks like.',
            'lessons' => [
                [
                    'title'   => 'Projects and routine work',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to tell a project from everyday routine work. This matters because projects need different planning: they have an end date, a goal and a limited budget, so you cannot manage them by habit.</p>
<h3>What is a project?</h3>
<p>A <strong>project</strong> is a temporary effort to create something new or to make a change. It has a start, an end, a clear result (a <strong>deliverable</strong>) and a limited set of resources such as people, money and time.</p>
<p><strong>Routine work</strong> (also called operations) repeats again and again with no planned end, for example cooking daily meals in a school kitchen, paying salaries each month or answering customer calls.</p>
<table>
<thead><tr><th>Question</th><th>Project</th><th>Routine work</th></tr></thead>
<tbody>
<tr><td>Does it end?</td><td>Yes, on a planned date</td><td>No, it continues</td></tr>
<tr><td>Is the result new?</td><td>Yes, something unique</td><td>No, the same process repeats</td></tr>
<tr><td>How is it managed?</td><td>With a plan, a budget and milestones</td><td>With procedures and targets</td></tr>
<tr><td>Example</td><td>Opening a computer lab in a rural school</td><td>Running the lab every day after it opens</td></tr>
</tbody>
</table>
<h3>Examples of projects</h3>
<ul>
<li>Organising a community clean-up day</li>
<li>Training 50 young women in basic computer skills over three months</li>
<li>Building a new website for a cooperative</li>
<li>Starting a school garden</li>
<li>Planning a wedding or a school graduation ceremony</li>
</ul>
<h3>Why project management?</h3>
<p><strong>Project management</strong> means planning, organising and guiding people and resources so a project reaches its goal. It helps you answer: What exactly are we making? Who does what? By when? With how much money? What could go wrong? Without answers, the work depends on luck and on the energy of one person.</p>
<h3>Programmes and portfolios</h3>
<p>A <strong>programme</strong> is a group of related projects managed together, such as a youth empowerment programme made up of a skills training project, a mentoring project and a job fair. Many NGOs describe their work this way.</p>
<h3>Who manages projects?</h3>
<p>You do not need the title "project manager". Teachers, volunteers, shop owners and club leaders manage projects every day. The skills are the same: organise, communicate, track and finish.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write down five activities you or your group did in the last year.</li>
<li>Mark each as a project or routine work using the table.</li>
<li>Pick one project and write its start, its end and its main deliverable in one sentence each.</li>
<li>Write one thing that went well and one that went badly, and what planning might have changed.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A project is temporary and creates something unique.</li>
<li>Routine work repeats without a planned end.</li>
<li>Project management answers what, who, when, how much and what could go wrong.</li>
<li>Anyone can manage a project if they learn the basic steps.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The project life cycle',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe the five stages every project passes through. This matters because knowing where you are tells you what to do next and prevents the common mistake of rushing into action without a plan.</p>
<h3>The five stages</h3>
<ol>
<li><strong>Initiating.</strong> You decide whether the project is worth doing. You describe the problem or need, the idea, the main benefits, and get approval from whoever decides, such as a board, a head teacher or a donor.</li>
<li><strong>Planning.</strong> You decide in detail what will be done, by whom, when and at what cost. You also plan for risks, communication and quality.</li>
<li><strong>Executing.</strong> The team does the work and delivers the product or service.</li>
<li><strong>Monitoring and controlling.</strong> At the same time as executing, you check progress against the plan, deal with problems and approve changes. This is not a stage after the work, but an activity alongside it.</li>
<li><strong>Closing.</strong> You finish the work, hand over results, thank people, report and record lessons learned.</li>
</ol>
<h3>A picture in words</h3>
<pre><code>Initiate -> Plan -> Execute (with Monitor and Control all the time) -> Close</code></pre>
<h3>Example: a school computer lab project</h3>
<table>
<thead><tr><th>Stage</th><th>What happens</th></tr></thead>
<tbody>
<tr><td>Initiating</td><td>Teachers notice learners have no computer access. The head teacher approves a proposal to find funds.</td></tr>
<tr><td>Planning</td><td>The team decides on a room, 15 computers, power and security, a budget and a timetable</td></tr>
<tr><td>Executing</td><td>Room is prepared, equipment is bought and set up, teachers are trained</td></tr>
<tr><td>Monitoring and controlling</td><td>The coordinator checks costs weekly, solves delivery delays and approves a cheaper desk design</td></tr>
<tr><td>Closing</td><td>The lab opens, a handover is made to the ICT teacher, a report goes to donors and lessons are recorded</td></tr>
</tbody>
</table>
<h3>Why the early stages matter</h3>
<p>Time spent in initiating and planning is not wasted. A mistake caught on paper costs almost nothing. The same mistake found after the equipment is bought can cost a lot. However, do not plan for ever. Plan enough to start with confidence, and keep adjusting as you learn.</p>
<h3>Phases and approvals</h3>
<p>Larger projects add a short <strong>decision point</strong> at the end of each stage: "Do we continue?" This protects money and gives donors confidence.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose a small project, for example a school sports day or a youth meeting.</li>
<li>Write what happens in each of the five stages, using one or two lines each.</li>
<li>Mark which stage you usually skip or rush.</li>
<li>Add one decision point where you will ask "Should we continue?"</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The stages are initiating, planning, executing, monitoring and controlling, and closing.</li>
<li>Monitoring and controlling happens throughout the work.</li>
<li>Planning early saves time and money later.</li>
<li>Decision points help you stop, change or continue wisely.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Stakeholders and roles',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to list the people affected by a project and describe who does what in a project team. This matters because many projects fail not from technical problems but because someone important was ignored.</p>
<h3>What is a stakeholder?</h3>
<p>A <strong>stakeholder</strong> is any person or group that is affected by the project, can affect it, or cares about its result. Stakeholders can be inside your team or outside it.</p>
<table>
<thead><tr><th>Stakeholder</th><th>What they care about</th></tr></thead>
<tbody>
<tr><td>Beneficiaries (learners, farmers, patients)</td><td>Useful results, respect, convenient times</td></tr>
<tr><td>Donors and funders</td><td>Value for money, reports, impact</td></tr>
<tr><td>Local leaders and community</td><td>Respect for culture, benefits for the area, no harm</td></tr>
<tr><td>Team members and volunteers</td><td>Clear roles, fair treatment, recognition</td></tr>
<tr><td>Government offices and regulators</td><td>Legal compliance, safety</td></tr>
<tr><td>Suppliers and partners</td><td>Clear orders, prompt payment</td></tr>
</tbody>
</table>
<h3>Stakeholder analysis</h3>
<p>Draw a simple grid with <strong>influence</strong> (high or low power over the project) and <strong>interest</strong> (high or low concern). Then decide how to treat each group:</p>
<ul>
<li><strong>High influence, high interest:</strong> manage closely, involve in decisions.</li>
<li><strong>High influence, low interest:</strong> keep satisfied with short updates.</li>
<li><strong>Low influence, high interest:</strong> keep informed, listen to feedback.</li>
<li><strong>Low influence, low interest:</strong> monitor, with little effort.</li>
</ul>
<p>Remember that the people with little power, such as children or persons with disabilities, may be the most affected. Make sure their voices are heard even if they are not on your grid as powerful.</p>
<h3>Key roles</h3>
<ul>
<li><strong>Sponsor:</strong> the person or body that approves and provides the money or authority.</li>
<li><strong>Project manager or coordinator:</strong> plans, organises, tracks and reports. Accountable for the result.</li>
<li><strong>Team members:</strong> do the tasks.</li>
<li><strong>Volunteers:</strong> give time, often with flexible hours, and need good guidance.</li>
<li><strong>Beneficiaries and community representatives:</strong> give input and feedback.</li>
</ul>
<h3>Clear responsibilities</h3>
<p>For each task name <strong>one person</strong> who is responsible. A simple tool is a table that lists tasks down the side and people across the top, marking who does the task, who must approve and who needs to be told. This avoids the confusion of "I thought you were doing it".</p>
<h3>Try it yourself</h3>
<ol>
<li>Pick a project idea, for example a community library.</li>
<li>List at least eight stakeholders, including quiet ones such as children or elders.</li>
<li>Place each on the influence and interest grid.</li>
<li>Write one way you will involve each of the top three groups.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A stakeholder is anyone who affects or is affected by the project.</li>
<li>Analyse influence and interest to decide how to engage each group.</li>
<li>Give every task a single responsible person.</li>
<li>Include the voices of people with little power.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Success criteria and the triple constraint',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to define what success means for a project and explain the trade-offs between time, cost and scope. This matters because teams argue and projects drift when nobody agrees what "done" and "good" mean.</p>
<h3>Success criteria</h3>
<p><strong>Success criteria</strong> are the specific, measurable conditions that show the project achieved what it set out to do. Agree them at the beginning with the sponsor and the main stakeholders.</p>
<p>Weak criteria: "Learners will know computers." Strong criteria: "By 30 November, 40 learners aged 15 to 24 will each complete a 12-week course, and at least 30 will pass the final practical test."</p>
<p>Good criteria cover several areas:</p>
<ul>
<li><strong>Output:</strong> what was delivered (a lab, a course, a report).</li>
<li><strong>Time and cost:</strong> finished by the date and within the budget.</li>
<li><strong>Quality:</strong> meets agreed standards and users are satisfied.</li>
<li><strong>Outcome:</strong> the change you hoped for, such as learners using their skills.</li>
</ul>
<h3>The triple constraint</h3>
<p>Every project is limited by three connected factors:</p>
<table>
<thead><tr><th>Constraint</th><th>Question</th></tr></thead>
<tbody>
<tr><td>Scope</td><td>How much work and what features are included?</td></tr>
<tr><td>Time</td><td>By when must it be done?</td></tr>
<tr><td>Cost</td><td>How much money and effort can we use?</td></tr>
</tbody>
</table>
<p>They are linked like three sides of a triangle. If you pull one, the others move. Add more scope and you need more time or money. Reduce the time and you may need more money, or less scope. Cut the budget and you may have to reduce scope or accept more time.</p>
<h3>Quality in the middle</h3>
<p>Quality sits in the middle. When a project is squeezed on all sides, quality is often what gets lost silently. Decide in advance what quality level is not negotiable, such as safety, accuracy or the dignity of participants.</p>
<h3>Using the triple constraint in practice</h3>
<ol>
<li>At the start, ask the sponsor which is most fixed: the date, the budget or the scope. A donor grant often fixes the budget and the end date, leaving scope flexible.</li>
<li>When a change is requested, ask: what does this do to time, cost and scope?</li>
<li>Explain trade-offs honestly: "We can add 10 more learners, but it will need two more computers and an extra week."</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Choose a project idea and write three strong success criteria with numbers and dates.</li>
<li>Decide which constraint is most fixed and which is most flexible.</li>
<li>Write what you would give up if the budget were cut by 20 percent.</li>
<li>Name one quality standard you would never compromise.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Success criteria are specific and measurable and agreed at the start.</li>
<li>Scope, time and cost are linked: changing one affects the others.</li>
<li>Protect quality by deciding in advance what cannot be compromised.</li>
<li>Honest trade-off conversations prevent surprises.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which of these is a project?
- Cleaning the school compound every morning
- Paying salaries each month
* Organising a one-week coding bootcamp with a start and end date
- Answering customer calls
= A project is temporary and produces something unique. The others repeat as routine work.

Q: During which activity do you check progress against the plan and approve changes?
- Initiating only
* Monitoring and controlling, which runs alongside the work
- Closing only
- It is not needed
= Monitoring and controlling continues through planning and execution.

Q: Which are stakeholders of a school computer lab project? (choose all that apply)
* Learners
* Donors
* Parents and the local community
- Nobody outside the team
= Anyone affected by or able to affect the project is a stakeholder.

Q: A local chief has high influence but little daily interest in your project. What is the best approach?
- Ignore the chief
- Involve the chief in every task
* Keep them satisfied with short, respectful updates
- Give them full control of the budget
= High influence and low interest means keep satisfied without overloading them.

Q: Which is the strongest success criterion?
- Learners will enjoy computers
* 40 learners complete a 12-week course by 30 November and 30 pass the test
- The project will go well
- We will try our best
= It is specific, measurable and time-bound.

Q: What does the triple constraint say?
- Projects only have three tasks
* Scope, time and cost are linked, so changing one affects the others
- Cost is never important
- Quality has no importance
= Trade-offs among the three are central to project decisions.

Q: A donor asks to add 20 extra learners with no extra money or time. What should the coordinator do?
- Say yes and hope
* Explain the impact on scope, time, cost and quality, and agree a decision
- Hide the problem
- Cancel the project
= Honest trade-off discussion avoids hidden quality loss.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 2
        [
            'title'   => 'Module 2: Planning',
            'summary' => 'Define scope and goals, break the work down, estimate, schedule and budget.',
            'lessons' => [
                [
                    'title'   => 'Scope and SMART goals',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a clear goal and a scope statement for a project. This matters because unclear goals lead to arguments, wasted effort and the slow growth of extra tasks that nobody planned for.</p>
<h3>Goals and objectives</h3>
<p>A <strong>goal</strong> is the big change you want, such as "Young people in Gulu can use computers to find work." <strong>Objectives</strong> are the specific results that lead to the goal, written so that you can tell whether you achieved them.</p>
<h3>SMART objectives</h3>
<table>
<thead><tr><th>Letter</th><th>Meaning</th><th>Question to ask</th></tr></thead>
<tbody>
<tr><td>S</td><td>Specific</td><td>What exactly will be achieved, for whom?</td></tr>
<tr><td>M</td><td>Measurable</td><td>What number or evidence will show it?</td></tr>
<tr><td>A</td><td>Achievable</td><td>Can our team, money and time really do this?</td></tr>
<tr><td>R</td><td>Relevant</td><td>Does it help the main goal and the needs of the community?</td></tr>
<tr><td>T</td><td>Time-bound</td><td>By what date?</td></tr>
</tbody>
</table>
<p>Weak: "Teach computer skills." SMART: "Train 40 young people aged 15 to 24 in basic computer skills, with at least 30 passing a practical test, by 30 November."</p>
<h3>Scope</h3>
<p><strong>Scope</strong> is the full list of work that the project will do, and the limits of that work. A <strong>scope statement</strong> is a short document that says:</p>
<ul>
<li><strong>What is included</strong> (the deliverables and main activities).</li>
<li><strong>What is not included</strong> (out of scope). This is very important. For example: "Providing internet at home for learners is not part of this project."</li>
<li><strong>Assumptions:</strong> things you believe are true, such as "The school will provide a room and electricity."</li>
<li><strong>Constraints:</strong> fixed limits, such as the grant amount and end date.</li>
</ul>
<h3>Scope creep</h3>
<p><strong>Scope creep</strong> is when extra tasks keep being added without changing time, money or people. It is friendly and dangerous: "Could we also quickly add...?" Protect against it by writing scope down, and by handling every extra request through a simple change process (see Module 4).</p>
<h3>A simple project charter</h3>
<p>Many projects begin with a one-page <strong>charter</strong> that holds the purpose, goal, objectives, scope, main deliverables, key dates, budget, team and sponsor approval. It is the agreement that everything else is built on.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose a project idea and write its main goal in one sentence.</li>
<li>Write three SMART objectives using the table.</li>
<li>List three things that are in scope and three things that are out of scope.</li>
<li>Write two assumptions that must be true for the plan to work.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The goal is the big change; objectives are specific results.</li>
<li>SMART means specific, measurable, achievable, relevant and time-bound.</li>
<li>A scope statement lists what is in, what is out, and the assumptions.</li>
<li>Control scope creep with writing and a change process.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Work breakdown structure and estimates',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to break a project into manageable tasks and estimate how long they will take. This matters because big jobs look impossible until they are split into small, clear pieces that people can actually do.</p>
<h3>The work breakdown structure (WBS)</h3>
<p>A <strong>work breakdown structure</strong> splits the whole project into smaller and smaller parts until each piece is a clear task. It is built around deliverables, not around vague actions. Think of it as an outline, like the table of contents of a book.</p>
<pre><code>1. Youth ICT club
   1.1 Venue and equipment
       1.1.1 Agree room with school
       1.1.2 Buy or borrow 10 laptops
       1.1.3 Set up power and security
   1.2 Training programme
       1.2.1 Write the 12-week curriculum
       1.2.2 Train two volunteer facilitators
       1.2.3 Run the weekly sessions
   1.3 Recruitment and communication
   1.4 Evaluation and reporting</code></pre>
<h3>How to build it</h3>
<ol>
<li>Write the project name at the top.</li>
<li>List the main deliverables or phases (three to six).</li>
<li>Break each into smaller parts until a task takes about a day to a week and one person can own it.</li>
<li>Check completeness with the "100 percent rule": all the parts together must equal the whole project, with nothing missing and nothing extra.</li>
<li>Use sticky notes or a spreadsheet so you can move items around.</li>
</ol>
<h3>Estimating</h3>
<p>For each task estimate <strong>duration</strong> (how long it will take on the calendar) and <strong>effort</strong> (how many hours of work). Remember that a task of eight hours of effort might take a week if the person works on it two hours a day.</p>
<ul>
<li><strong>Ask the people who will do the work.</strong> They know best.</li>
<li><strong>Use past experience.</strong> How long did a similar task take last time?</li>
<li><strong>Use three numbers</strong> for uncertain tasks: best case, most likely and worst case. A common blend is (best + 4 x most likely + worst) divided by 6.</li>
<li><strong>Add a buffer</strong> for the unexpected, such as 10 to 20 percent, especially when waiting for others.</li>
</ul>
<h3>Common estimation traps</h3>
<ul>
<li>Being too optimistic. Most people forget interruptions, travel and waiting.</li>
<li>Forgetting tasks such as approvals, shopping, testing and cleaning up.</li>
<li>Estimating without asking the person who does the work.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Choose a project and write its top-level deliverables.</li>
<li>Break one deliverable into at least five tasks.</li>
<li>Estimate effort in hours and duration in days for each task.</li>
<li>Add a buffer and note who would own each task.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A WBS breaks the project into deliverables and small tasks.</li>
<li>The parts must add up to 100 percent of the work.</li>
<li>Estimate with the doers, use experience and add a buffer.</li>
<li>Effort in hours is not the same as duration in days.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Schedules, milestones and Gantt charts',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to turn your task list into a schedule and draw a simple Gantt chart. This matters because a schedule shows what must happen first, who is busy when, and whether the end date is realistic.</p>
<h3>Dependencies</h3>
<p>Some tasks cannot start until another finishes. These links are <strong>dependencies</strong>. You cannot train learners before the room is ready, and you cannot buy laptops before the budget is approved. Write next to each task which tasks must come before it. Tasks without a link can run at the same time, which saves calendar time.</p>
<h3>Milestones</h3>
<p>A <strong>milestone</strong> is an important point in the project, with no duration, that marks progress, such as "Venue ready" or "First 20 learners enrolled". Choose milestones that are easy to verify. They help you report to sponsors and celebrate progress.</p>
<h3>The critical path in simple terms</h3>
<p>The <strong>critical path</strong> is the longest chain of linked tasks. It decides the earliest finish date. If a task on that path is late, the whole project is late. Tasks not on it have some slack (spare time). Watch the critical path most carefully.</p>
<h3>Gantt chart</h3>
<p>A <strong>Gantt chart</strong> is a bar chart of tasks against time. Each task is a horizontal bar from its start to its finish. You can see overlaps, dependencies and milestones at a glance.</p>
<table>
<thead><tr><th>Task</th><th>Wk1</th><th>Wk2</th><th>Wk3</th><th>Wk4</th><th>Wk5</th><th>Wk6</th></tr></thead>
<tbody>
<tr><td>Agree room</td><td>X</td><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td>Buy laptops</td><td></td><td>X</td><td>X</td><td></td><td></td><td></td></tr>
<tr><td>Write curriculum</td><td>X</td><td>X</td><td>X</td><td></td><td></td><td></td></tr>
<tr><td>Train facilitators</td><td></td><td></td><td></td><td>X</td><td></td><td></td></tr>
<tr><td>Open the club (milestone)</td><td></td><td></td><td></td><td></td><td>X</td><td></td></tr>
</tbody>
</table>
<h3>Building a Gantt in a spreadsheet</h3>
<ol>
<li>Put tasks in column A, start date in B, duration in days in C and end date in D (start plus duration).</li>
<li>Make columns E onward weeks or days.</li>
<li>Use conditional formatting or colour the cells where a task is active.</li>
<li>Update it weekly, marking progress with a different colour.</li>
</ol>
<p>Free online tools and many spreadsheet programs have Gantt templates. The interface varies by tool, so look for "timeline" or "Gantt" in the template gallery.</p>
<h3>Schedule realistically</h3>
<p>Include public holidays, school terms, rainy seasons and market days. Avoid planning events when your key people or participants are busy. Add buffer time before big deadlines.</p>
<h3>Try it yourself</h3>
<ol>
<li>Take the tasks from your WBS and write which tasks must finish before others start.</li>
<li>Mark at least three milestones.</li>
<li>Create a simple Gantt in a spreadsheet or on paper.</li>
<li>Check the end date against any fixed deadline and adjust.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Dependencies decide the order of tasks.</li>
<li>Milestones mark clear progress points.</li>
<li>The critical path controls the finish date.</li>
<li>A Gantt chart shows the plan on one page and should be updated often.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Budgets and free planning tools',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to prepare a simple project budget and choose free tools for planning. This matters because a budget turns your plan into money terms, shows whether the plan is affordable and is required by almost every donor.</p>
<h3>What goes into a budget</h3>
<p>A <strong>budget</strong> is a plan of how much money you expect to spend and where it comes from. List every cost for every task in your WBS. Common categories:</p>
<table>
<thead><tr><th>Category</th><th>Examples</th></tr></thead>
<tbody>
<tr><td>People</td><td>Facilitator allowances, coordinator salary</td></tr>
<tr><td>Equipment</td><td>Laptops, projector, extension cables</td></tr>
<tr><td>Supplies</td><td>Notebooks, printing, refreshments</td></tr>
<tr><td>Travel and venue</td><td>Transport, room hire, fuel</td></tr>
<tr><td>Communication</td><td>Airtime, internet data, posters</td></tr>
<tr><td>Administration</td><td>Bank charges, audit, insurance</td></tr>
<tr><td>Contingency</td><td>A reserve (often 5 to 10 percent) for surprises</td></tr>
</tbody>
</table>
<h3>Direct and indirect costs</h3>
<p><strong>Direct costs</strong> belong to this project alone, such as the laptops. <strong>Indirect costs</strong> are shared overheads, such as office rent or the accountant, and are often charged as a percentage. Donors have rules about what they allow, so read the guidelines.</p>
<h3>Making the budget in a spreadsheet</h3>
<ol>
<li>Create columns: Item, Unit, Number of units, Cost per unit, Total.</li>
<li>In the Total column type a formula such as <code>=C2*D2</code> and copy it down.</li>
<li>Add subtotals for each category and a grand total with <code>=SUM(E2:E20)</code>.</li>
<li>Add a column for "Source" (grant, own contribution, in-kind) and a column for "Actual" to fill as you spend.</li>
</ol>
<pre><code>Item: Facilitator allowance | Unit: session | Units: 24 | Cost: 40,000 | Total: 960,000 UGX</code></pre>
<h3>In-kind contributions</h3>
<p>Show donated items or time, such as a free room or volunteer hours, with an estimated value. They show community ownership and strengthen funding applications.</p>
<h3>Honest budgeting</h3>
<p>Get real quotes, not guesses. Use realistic prices and consider exchange rate changes if you buy imported items. Never hide costs or inflate numbers. Keep receipts and records so you can account for every shilling.</p>
<h3>Free tools</h3>
<ul>
<li><strong>Spreadsheets</strong> (free office suites and web spreadsheets) for budgets and Gantt charts.</li>
<li><strong>Free kanban boards</strong> for tasks (look for the free plan of popular board tools).</li>
<li><strong>Shared documents and calendars</strong> for plans, minutes and deadlines.</li>
<li><strong>Messaging groups</strong> for quick coordination, with decisions also recorded in writing.</li>
</ul>
<p>Choose the simplest tool your team will actually use. A notebook that everyone reads beats a perfect tool nobody opens.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open a spreadsheet and set up the five budget columns.</li>
<li>Enter at least ten budget lines for a project, with real local prices.</li>
<li>Use a formula for each total and a SUM for the grand total.</li>
<li>Add a 10 percent contingency line and a column for actual spending.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A budget lists every cost, grouped in categories, with a contingency.</li>
<li>Use formulas in a spreadsheet so totals stay correct.</li>
<li>Show in-kind contributions and keep records.</li>
<li>Choose simple free tools your team will really use.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which objective is SMART?
- Teach computer skills
* Train 40 young people in basic computer skills and have 30 pass a practical test by 30 November
- Make the community better
- Try to start a club
= It is specific, measurable, achievable, relevant and time-bound.

Q: Why list what is out of scope?
- To make the document longer
* To avoid misunderstanding and scope creep
- It is a donor trick
- To reduce staff
= Clear limits prevent unplanned extra work.

Q: The "100 percent rule" for a WBS means:
- All tasks take 100 days
- Everyone works 100 percent
* The parts together equal the whole project with nothing missing or extra
- The budget is 100 percent spent
= The WBS must cover all the work and only the work.

Q: Which are good habits when estimating? (choose all that apply)
* Ask the people who will do the work
* Use best, likely and worst-case numbers for uncertain tasks
* Add a buffer
- Always assume nothing will go wrong
= Realistic estimates include uncertainty and experience.

Q: A task cannot begin until the room is ready. This is called a:
- Milestone
* Dependency
- Budget line
- Stakeholder
= A dependency is a link where one task must finish first.

Q: What does the critical path decide?
- The colour of the chart
* The earliest possible finish date, because delays on it delay the project
- The size of the team
- The budget total
= Late tasks on the critical path delay the whole project.

Q: Which is an example of a direct cost?
- General office rent shared by many projects
* Laptops bought only for this project
- The director's overall salary
- Bank charges for all projects
= Direct costs belong to one project alone.

Q: Why add a contingency to a budget?
- To spend extra money
* To cover unexpected costs
- To impress donors
- It has no purpose
= Surprises happen, and a reserve avoids stopping the project.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 3
        [
            'title'   => 'Module 3: Risk, quality and communication',
            'summary' => 'Prepare for what could go wrong, plan quality, and keep everyone informed.',
            'lessons' => [
                [
                    'title'   => 'Managing risk',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to identify risks, rate them and plan responses in a risk register. This matters because every project meets surprises, and a team that has thought about them in advance reacts calmly and loses less.</p>
<h3>What is a project risk?</h3>
<p>A <strong>risk</strong> is an uncertain event that, if it happens, affects the project. It can be negative (a threat) or positive (an opportunity, such as a donor offering extra equipment). An <strong>issue</strong> is a risk that has already happened and now needs action.</p>
<h3>Step 1: Identify risks</h3>
<p>Hold a short brainstorming session with the team and some stakeholders. Look at each part of your WBS and ask "What could go wrong here?" Typical risks for community projects: late funds, volunteers dropping out, bad weather, power cuts, price increases, poor internet, safety concerns, participants missing sessions, and changes in leadership.</p>
<h3>Step 2: Rate each risk</h3>
<p>Score <strong>probability</strong> (low, medium or high) and <strong>impact</strong> (low, medium or high). Multiply or plot them to see which risks matter most.</p>
<h3>Step 3: Plan the response</h3>
<table>
<thead><tr><th>Response</th><th>Meaning</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Avoid</td><td>Change the plan so the risk cannot happen</td><td>Hold sessions indoors so rain does not matter</td></tr>
<tr><td>Reduce</td><td>Take action to make it less likely or smaller</td><td>Train a second facilitator in case one is absent</td></tr>
<tr><td>Transfer</td><td>Pass the effect to another party</td><td>Hire an insured transport company</td></tr>
<tr><td>Accept</td><td>Do nothing now but be ready</td><td>Keep a small contingency for minor price changes</td></tr>
</tbody>
</table>
<h3>The risk register</h3>
<pre><code>ID | Risk | Probability | Impact | Response | Owner | Status
R1 | Grant money arrives late | Medium | High | Agree a bridging plan with the board | Treasurer | Open
R2 | Only one facilitator available | High | Medium | Train a second volunteer | Coordinator | Open</code></pre>
<h3>Keep it live</h3>
<ol>
<li>Review the register at every team meeting for a few minutes.</li>
<li>Add new risks as they appear and close old ones.</li>
<li>Name one owner for every risk response.</li>
<li>Report the top three risks to the sponsor each month.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Choose a project and list at least eight risks.</li>
<li>Rate each risk and highlight the top three.</li>
<li>Write a response, an owner and a date for each of the top three.</li>
<li>Set a recurring reminder to review the register.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A risk is an uncertain event; an issue has already happened.</li>
<li>Rate risks by probability and impact and focus on the biggest.</li>
<li>Responses are avoid, reduce, transfer or accept.</li>
<li>Review the register regularly and give every risk an owner.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Quality planning and communication plans',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to define quality for your project and write a simple communication plan. This matters because a project can finish on time and on budget and still fail if the result is poor or if people were not kept informed.</p>
<h3>Quality in plain words</h3>
<p><strong>Quality</strong> means that the result meets the agreed needs and standards of the people who will use it. Quality is planned, not added at the end. It is cheaper to prevent problems than to fix them later.</p>
<h3>Steps in a simple quality plan</h3>
<ol>
<li><strong>Define the standards.</strong> For each deliverable write what "good" looks like. For a training session: starts on time, every learner gets a computer, the lesson is in plain language, and learners can do the practice task by the end.</li>
<li><strong>Prepare checklists.</strong> A short list used before handing over a deliverable, for example "Lab ready: power works, laptops charged, software installed, door locks, attendance sheet printed."</li>
<li><strong>Check early and often.</strong> Test one session before running twelve. Ask a small group to try the material.</li>
<li><strong>Collect feedback.</strong> Use short surveys, suggestion boxes, phone calls and observation. Include quieter participants.</li>
<li><strong>Fix and learn.</strong> Record problems and change the process.</li>
</ol>
<h3>The communication plan</h3>
<p>A <strong>communication plan</strong> says who needs what information, how often, by which channel and from whom. Without it, some people are overloaded with messages and others are left in the dark.</p>
<table>
<thead><tr><th>Audience</th><th>Information</th><th>How often</th><th>Channel</th><th>Owner</th></tr></thead>
<tbody>
<tr><td>Team</td><td>Tasks, problems, decisions</td><td>Weekly</td><td>Short meeting and a messaging group</td><td>Coordinator</td></tr>
<tr><td>Sponsor or board</td><td>Progress, budget, risks</td><td>Monthly</td><td>Written report and a meeting</td><td>Coordinator</td></tr>
<tr><td>Donor</td><td>Results, spending</td><td>As agreed</td><td>Formal report</td><td>Project manager</td></tr>
<tr><td>Community</td><td>Activities, opportunities, results</td><td>Monthly</td><td>Announcements, radio, notice boards, meetings</td><td>Outreach officer</td></tr>
</tbody>
</table>
<h3>Good communication habits</h3>
<ul>
<li>Use simple words and the languages your audience uses, such as English and Luo.</li>
<li>Give bad news early, with a proposed solution.</li>
<li>Write down decisions and who is responsible.</li>
<li>Choose the channel to match the message: calls for urgent matters, written notes for decisions.</li>
<li>Respect privacy. Do not share personal details of participants, especially children, in open groups.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Write three quality standards for one deliverable of your project.</li>
<li>Make a five-line checklist for it.</li>
<li>Create a communication table for at least four audiences.</li>
<li>Decide how you will collect feedback from participants who rarely speak.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Quality means meeting agreed needs and is planned from the start.</li>
<li>Checklists and early testing prevent expensive mistakes.</li>
<li>A communication plan matches audience, message, timing and channel.</li>
<li>Share bad news early and protect privacy.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Meetings, reports and managing expectations',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to run short, useful meetings, write a simple progress report and manage what people expect from your project. This matters because many project problems start as misunderstandings that a good conversation would have solved.</p>
<h3>Meetings that work</h3>
<ol>
<li><strong>Have a purpose.</strong> If you cannot say in one sentence why you are meeting, do not meet. A message may be enough.</li>
<li><strong>Send an agenda</strong> in advance with times for each topic.</li>
<li><strong>Invite only needed people.</strong> Others can read the notes.</li>
<li><strong>Start and end on time.</strong> Respect people's travel and other duties.</li>
<li><strong>Assign a note-taker</strong> and a time-keeper.</li>
<li><strong>End with actions:</strong> who will do what by when.</li>
<li><strong>Send minutes</strong> within 24 hours.</li>
</ol>
<h3>A short weekly team meeting (20 minutes)</h3>
<ul>
<li>What did we finish since last time?</li>
<li>What will we do before the next meeting?</li>
<li>What is blocking us?</li>
<li>Risks and decisions needed.</li>
</ul>
<p>Make sure everyone, including quiet and younger members, gets a chance to speak. Online meetings need extra care with data costs and time zones. Offer a call-in or notes for those with weak internet.</p>
<h3>Progress reports</h3>
<p>A one-page <strong>status report</strong> may include:</p>
<table>
<thead><tr><th>Section</th><th>Content</th></tr></thead>
<tbody>
<tr><td>Summary</td><td>Overall status: on track, at risk or late, with one sentence of why</td></tr>
<tr><td>Done</td><td>Tasks and milestones completed this period</td></tr>
<tr><td>Next</td><td>Planned work for next period</td></tr>
<tr><td>Budget</td><td>Spent so far versus planned</td></tr>
<tr><td>Risks and issues</td><td>Top three and how they are handled</td></tr>
<tr><td>Decisions needed</td><td>What you need from the sponsor</td></tr>
</tbody>
</table>
<p>Be honest. A report that shows a problem and a plan builds trust. A report that hides problems destroys it when the truth appears.</p>
<h3>Managing expectations</h3>
<p>Stakeholders have different hopes. Some people expect more than the project can give. To manage this:</p>
<ul>
<li>Say clearly at the start what the project will and will not deliver.</li>
<li>Promise less and deliver a little more, not the reverse.</li>
<li>Listen first. Ask what people expect and why.</li>
<li>Update regularly so there are no surprises.</li>
<li>Be respectful when you must say no, and explain the reason.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Write an agenda for a 20-minute team meeting for your project.</li>
<li>Run it with at least two other people, or role play it, and write three action points.</li>
<li>Write a one-page status report using the table.</li>
<li>List two expectations someone may have that your project cannot meet, and write how you would explain this.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Meetings need a purpose, an agenda, a short length and written actions.</li>
<li>A status report is short, honest and ends with decisions needed.</li>
<li>Manage expectations by being clear from the start.</li>
<li>Give people a chance to speak and to receive notes.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the difference between a risk and an issue?
- There is no difference
* A risk might happen; an issue has already happened
- A risk is always good
- An issue is only about money
= Once a risk happens, it becomes an issue that needs action.

Q: A project trains a second facilitator in case the first is absent. Which response is this?
- Accept
- Transfer
- Avoid
* Reduce
= It lowers the effect of the risk if it happens.

Q: Which should every risk response have?
- A long report
* An owner and a date
- A donor
- A new logo
= Ownership makes the response happen.

Q: Which are good meeting practices? (choose all that apply)
* Send an agenda in advance
* End with who will do what by when
* Send minutes soon after
- Invite everyone in the organisation to every meeting
= Focused, actionable meetings respect people's time.

Q: What does a communication plan include?
- Only the logo
* Audience, information, frequency, channel and owner
- Only the budget
- Only the team list
= It makes sure the right people receive the right information.

Q: A problem appears that will delay the project by two weeks. What is the best action?
- Hide it until the end
* Tell the sponsor early with a proposed solution
- Blame the team
- Cancel the report
= Early honest communication builds trust and allows solutions.

Q: Why define quality standards at the start?
* So everyone knows what good looks like and can check it early
- To make the project longer
- To increase the budget
- They are not useful
= Quality is planned, and early checks prevent costly fixes.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 4
        [
            'title'   => 'Module 4: Doing and tracking',
            'summary' => 'Run the work with simple agile tools, track progress, handle change and work well with people.',
            'lessons' => [
                [
                    'title'   => 'Kanban and Scrum in plain words',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to set up a kanban board and explain how Scrum works. This matters because these simple methods help small teams see their work, finish tasks steadily and adapt when plans change.</p>
<h3>Kanban</h3>
<p><strong>Kanban</strong> is a method that shows work on a board with columns. Each task is a card that moves from left to right as work progresses. You can use sticky notes on a wall or a free online board.</p>
<table>
<thead><tr><th>To do</th><th>Doing</th><th>Review</th><th>Done</th></tr></thead>
<tbody>
<tr><td>Book the room</td><td>Write curriculum</td><td>Poster draft</td><td>Agree budget</td></tr>
<tr><td>Print posters</td><td>Buy cables</td><td></td><td>Recruit volunteers</td></tr>
</tbody>
</table>
<p>The key rules:</p>
<ol>
<li><strong>Make work visible.</strong> Every task is on the board.</li>
<li><strong>Limit work in progress (WIP).</strong> For example, allow no more than three cards in "Doing" per person. Too many open tasks slow everything down.</li>
<li><strong>Finish before starting.</strong> Pull a new card only when you have space.</li>
<li><strong>Review regularly</strong> and improve the process.</li>
</ol>
<h3>Scrum</h3>
<p><strong>Scrum</strong> is a framework for teams to deliver work in short cycles called <strong>sprints</strong>, usually one to four weeks. It is used a lot in software but also works for other creative and community work.</p>
<ul>
<li><strong>Product backlog:</strong> a prioritised list of everything that might be done.</li>
<li><strong>Sprint planning:</strong> the team picks items for the next sprint and agrees on a sprint goal.</li>
<li><strong>Daily Scrum:</strong> a 15-minute stand-up meeting where team members share progress, plans and blocks.</li>
<li><strong>Sprint review:</strong> at the end, the team shows what was finished to stakeholders and receives feedback.</li>
<li><strong>Retrospective:</strong> the team discusses what went well and what to improve.</li>
</ul>
<p>Scrum names three roles: the <strong>Product Owner</strong> (decides priorities), the <strong>Scrum Master</strong> (helps the team follow the method and removes obstacles) and the <strong>Developers</strong> (the people who do the work, whatever their skills).</p>
<h3>Which to use?</h3>
<table>
<thead><tr><th>Situation</th><th>Suggested approach</th></tr></thead>
<tbody>
<tr><td>Steady flow of small tasks, small team</td><td>Kanban board</td></tr>
<tr><td>Needs frequent feedback and changing requirements</td><td>Scrum sprints</td></tr>
<tr><td>Fixed plan with clear steps, such as an event</td><td>Traditional plan with a Gantt chart, plus a kanban board for daily tasks</td></tr>
</tbody>
</table>
<p>Many teams mix methods. Do not worry about doing it "perfectly". Use what helps.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a kanban board on paper, a wall or a free online tool with four columns.</li>
<li>Add at least ten tasks from your project plan.</li>
<li>Set a WIP limit and move two tasks across the board.</li>
<li>Plan a one-week sprint with a goal and list what you will finish.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Kanban shows work on a board and limits work in progress.</li>
<li>Scrum delivers in short sprints with planning, daily meetings, review and retrospective.</li>
<li>Choose the method that fits your work, and mix when helpful.</li>
<li>Visible work helps teams finish tasks.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Tracking progress, changes and delays',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to measure progress honestly and handle changes and delays without panic. This matters because even good plans change, and the best teams notice early and respond calmly.</p>
<h3>Tracking progress</h3>
<p>Compare what the plan says should be done with what is actually done. Useful measures:</p>
<ul>
<li><strong>Tasks done:</strong> how many of the planned tasks are finished? Avoid "90 percent done" for a task that has been 90 percent done for weeks. Count it as done only when it meets the standard.</li>
<li><strong>Milestones:</strong> reached on time or not?</li>
<li><strong>Budget:</strong> actual spending against planned spending to date.</li>
<li><strong>Quality:</strong> feedback, attendance, errors.</li>
<li><strong>Results:</strong> numbers against your objectives, such as learners enrolled and learners attending.</li>
</ul>
<h3>A simple tracking table</h3>
<table>
<thead><tr><th>Item</th><th>Planned</th><th>Actual</th><th>Status</th></tr></thead>
<tbody>
<tr><td>Laptops bought</td><td>10 by week 3</td><td>6 by week 3</td><td>Late</td></tr>
<tr><td>Learners enrolled</td><td>40 by week 4</td><td>44 by week 4</td><td>Ahead</td></tr>
<tr><td>Budget spent</td><td>2,000,000 UGX</td><td>2,300,000 UGX</td><td>Over</td></tr>
</tbody>
</table>
<p>Use simple signals: green (on track), amber (some risk) and red (needs a decision).</p>
<h3>Handling delays</h3>
<ol>
<li><strong>Find the cause.</strong> Was it a late supplier, poor estimate, sickness, weather?</li>
<li><strong>Check the critical path.</strong> Does the delay change the end date?</li>
<li><strong>List options.</strong> Add people or time, reduce scope, run tasks in parallel, or change the date.</li>
<li><strong>Choose and agree</strong> with the sponsor if time, cost or scope will change.</li>
<li><strong>Update the plan</strong> and tell the team.</li>
</ol>
<h3>Change control</h3>
<p>Changes will be requested by donors, beneficiaries and team members. Use a simple process:</p>
<ol>
<li>Write the request down: what, why and who asked.</li>
<li>Assess the impact on scope, time, cost, risk and quality.</li>
<li>Decide: approve, reject or postpone. Agree who has authority to decide, based on how big the change is.</li>
<li>If approved, update the plan, budget and risk register and tell everyone concerned.</li>
</ol>
<p>Small changes may be quick. Large ones need the sponsor. Keep a change log with dates and decisions so there is a record.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a tracking table for five items of your project.</li>
<li>Make up a delay and write a short note on cause, impact and options.</li>
<li>Draft a one-page change request form with the four steps.</li>
<li>Decide who in your team may approve small changes and who must refer to the sponsor.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Track tasks, milestones, budget, quality and results against the plan.</li>
<li>Find causes of delays and check the effect on the critical path.</li>
<li>Use a written, simple change process.</li>
<li>Record decisions in a change log.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Volunteers, partners and remote teams',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to work well with volunteers, partner organisations and team members who are not in the same place. This matters because many community and NGO projects depend on goodwill, shared effort and phone-based coordination rather than on large budgets.</p>
<h3>Working with volunteers</h3>
<p>Volunteers give their time freely, so they need to feel valued and clear about what to do.</p>
<ol>
<li><strong>Define the role.</strong> Write a short description: tasks, hours, location and who they report to.</li>
<li><strong>Recruit fairly.</strong> Explain the opportunity honestly, including what skills they will gain. For roles with children, carry out safeguarding checks and follow your child protection policy.</li>
<li><strong>Give an induction.</strong> Share the project goal, rules, safety information and contacts.</li>
<li><strong>Match tasks to strengths</strong> and offer chances to learn.</li>
<li><strong>Be flexible but reliable.</strong> Have a backup person for key tasks.</li>
<li><strong>Say thank you</strong> in public and in private, give certificates or references, and share results.</li>
<li><strong>Do not use volunteers to replace paid jobs that should be paid.</strong> Cover reasonable costs such as transport and meals where you can.</li>
</ol>
<h3>Working with partners</h3>
<p>Partners may be schools, local councils, churches, businesses or other NGOs. Good partnerships start with clarity.</p>
<ul>
<li>Agree each side's responsibilities, resources and benefits in writing, such as a short memorandum of understanding (MoU).</li>
<li>Agree how decisions are made and how disputes are solved.</li>
<li>Agree who speaks to the media and to donors.</li>
<li>Meet regularly and share information openly.</li>
<li>Respect each partner's rules and ways of working.</li>
</ul>
<h3>Remote and hybrid teams</h3>
<p>A <strong>remote</strong> team works in different places, and a <strong>hybrid</strong> team mixes office and remote work. In areas with costly data and uneven power, plan carefully:</p>
<table>
<thead><tr><th>Challenge</th><th>Practical response</th></tr></thead>
<tbody>
<tr><td>Weak or expensive internet</td><td>Use short calls, voice notes or text, and share notes so people can catch up later</td></tr>
<tr><td>Power cuts</td><td>Agree a backup time, and keep written plans on paper or in shared files that work offline</td></tr>
<tr><td>Missing context</td><td>Write clear tasks with who, what and by when</td></tr>
<tr><td>Isolation</td><td>Hold regular check-ins that include some personal talk</td></tr>
<tr><td>Time differences</td><td>Choose a time that is fair and rotate inconvenient slots</td></tr>
</tbody>
</table>
<h3>Teamwork and conflict</h3>
<p>Disagreements are normal. Speak early, in private if needed, listen to understand, focus on the issue rather than the person, and agree next steps. If the issue is serious, involve the sponsor.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write a half-page volunteer role description for one task in your project.</li>
<li>List three things you would cover in an induction.</li>
<li>List what you would put in an MoU with a partner.</li>
<li>Choose one remote-working rule that suits your area, such as "send an update by 5 pm every Friday".</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Volunteers need clear roles, induction, appreciation and safeguarding checks where needed.</li>
<li>Write partner agreements and communicate openly.</li>
<li>Plan remote work around data cost, power and clarity.</li>
<li>Handle conflict early and respectfully.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the purpose of a work-in-progress (WIP) limit in kanban?
- To make more cards appear
* To stop too many tasks being open at once so that work gets finished
- To limit the budget
- To stop volunteers joining
= Finishing tasks before starting new ones keeps work flowing.

Q: In Scrum, what is a sprint?
- A race between team members
* A short, fixed period in which the team completes chosen work
- A budget category
- A type of report
= Sprints usually last one to four weeks.

Q: Which are Scrum events? (choose all that apply)
* Sprint planning
* Daily Scrum
* Sprint review and retrospective
- The yearly party
= These events help the team plan, coordinate, inspect and improve.

Q: A task has been "90 percent done" for three weeks. How should you report it?
- 90 percent done
* Not done until it meets the agreed standard, with the blocker explained
- Done
- Cancelled
= Count work as complete only when it truly meets the standard.

Q: A donor requests an extra training day. What should happen first?
- Say yes immediately
- Ignore it
* Write down the request and assess the impact on scope, time, cost and quality
- Cancel the project
= A change process protects the plan and keeps decisions transparent.

Q: What is a good practice for volunteers working with children?
- Skip checks to save time
* Follow safeguarding checks and the child protection policy
- Give them unsupervised access
- Share children's photos publicly
= Safeguarding protects children and the organisation.

Q: How can a remote team cope with expensive data and power cuts?
- Require daily long video calls
* Use short calls, voice or text updates, written notes and backup times
- Stop communicating
- Only use paper
= Practical adjustments keep communication fair and effective.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 5
        [
            'title'   => 'Module 5: Finishing and learning',
            'summary' => 'Close a project well, evaluate its impact, report to donors and see a full plan in action.',
            'lessons' => [
                [
                    'title'   => 'Closing, handover and lessons learned',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to close a project properly and capture lessons for the future. This matters because many projects simply fade away, leaving unpaid bills, lost files, confused beneficiaries and the same mistakes repeated next time.</p>
<h3>Why closing matters</h3>
<p>Closing confirms that the work is finished, the results are accepted and the people and resources are released. It protects your reputation with donors and communities and makes future projects stronger.</p>
<h3>The closing checklist</h3>
<ol>
<li><strong>Confirm deliverables.</strong> Compare results with the scope and success criteria. Ask the sponsor or users to sign acceptance.</li>
<li><strong>Finish the money.</strong> Pay all suppliers, reconcile the budget, collect receipts and prepare financial records. Return unspent funds if the agreement says so.</li>
<li><strong>Hand over.</strong> Give the new owner everything needed to run the result: keys, passwords (stored safely), manuals, contact lists, maintenance plans and training.</li>
<li><strong>Release people.</strong> Thank team members and volunteers, give certificates or references, and end contracts correctly.</li>
<li><strong>Archive records.</strong> Store the plan, reports, photos (with consent), agreements and financial files in an organised folder and a backup.</li>
<li><strong>Close agreements.</strong> End contracts and partnership arrangements formally.</li>
<li><strong>Celebrate and share results</strong> with the team and the community.</li>
</ol>
<h3>A good handover</h3>
<p>A handover is more than giving keys. For example, when the youth ICT club project ends and the school's ICT teacher takes over, the handover includes: the equipment list with serial numbers, how to charge and store laptops, the curriculum and slides, student records (with privacy rules), the list of volunteers, and a one-hour training for the teacher. Both sides sign an equipment and responsibility form.</p>
<h3>Lessons learned</h3>
<p>A <strong>lessons-learned review</strong> is a blame-free meeting held near the end, where the team and some stakeholders discuss:</p>
<table>
<thead><tr><th>Question</th><th>Purpose</th></tr></thead>
<tbody>
<tr><td>What went well?</td><td>Keep and repeat good practices</td></tr>
<tr><td>What did not go well?</td><td>Understand problems without blaming people</td></tr>
<tr><td>What surprised us?</td><td>Find hidden risks and assumptions</td></tr>
<tr><td>What would we do differently?</td><td>Give practical advice for the next project</td></tr>
</tbody>
</table>
<p>Write the lessons in a short document with clear actions, and share it with the organisation. Lessons are useless if they stay in someone's head.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose a completed or imagined project and write a closing checklist with at least seven items.</li>
<li>Write the contents of a handover pack for one deliverable.</li>
<li>Hold or imagine a lessons-learned meeting and write three things to keep and three to change.</li>
<li>Decide where the records will be stored and who will keep a backup.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Closing confirms results, finishes finances, hands over and releases people.</li>
<li>A good handover gives the next owner everything needed.</li>
<li>Lessons-learned meetings are blame-free and end in written actions.</li>
<li>Archive records safely and respect privacy.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Evaluation, impact and donor reporting',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain the difference between outputs, outcomes and impact, and write a simple report for a donor. This matters because funders and communities want to know not only what you did, but what changed because of it.</p>
<h3>Outputs, outcomes and impact</h3>
<table>
<thead><tr><th>Level</th><th>Meaning</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Inputs</td><td>What you put in</td><td>Money, laptops, volunteer hours</td></tr>
<tr><td>Outputs</td><td>What the project directly produces</td><td>40 learners trained, 24 sessions held</td></tr>
<tr><td>Outcomes</td><td>Short and medium term changes for people</td><td>32 learners can use a computer for CVs and e-mail</td></tr>
<tr><td>Impact</td><td>Longer-term, wider change</td><td>More young people in the area find work or start small businesses</td></tr>
</tbody>
</table>
<p>It is easy to count outputs. Outcomes and impact are harder to measure but matter more. Be honest about what you can show and what you only hope for.</p>
<h3>Monitoring and evaluation (M and E)</h3>
<p><strong>Monitoring</strong> is regular checking during the project. <strong>Evaluation</strong> is a judgement at or after the end about how well the project worked, what changed and why.</p>
<h3>Simple indicators and data</h3>
<ol>
<li>Choose a few <strong>indicators</strong>: measurable signs, such as "number of learners who pass the practical test" or "percentage who report using computer skills after three months".</li>
<li>Collect a <strong>baseline</strong> at the start (for example how many learners could use a computer before).</li>
<li>Collect data with attendance sheets, tests, short surveys and interviews.</li>
<li>Include stories and quotes, with permission. Numbers show scale; stories show meaning.</li>
<li>Protect personal data and ask consent, especially for children.</li>
</ol>
<h3>Donor and grant reporting basics</h3>
<p>Read your grant agreement carefully for dates, formats and rules. A narrative report usually includes:</p>
<ul>
<li><strong>Summary:</strong> the main results in a few lines.</li>
<li><strong>Activities:</strong> what was done compared with the plan.</li>
<li><strong>Results:</strong> outputs and outcomes against the indicators, with numbers.</li>
<li><strong>Challenges and changes:</strong> what went wrong, how you responded and what was agreed with the donor.</li>
<li><strong>Lessons and next steps:</strong> what you learned and what happens after the grant.</li>
<li><strong>Financial report:</strong> budget against actual spending, with explanations of big differences and supporting receipts.</li>
</ul>
<h3>Honest reporting</h3>
<p>Do not exaggerate results or hide failures. Donors prefer honest reports with solutions. If you must change the budget or activities, ask for approval before you do it, not after. Report on time, and say thank you.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose a project and write one input, one output, one outcome and one impact.</li>
<li>Write three indicators with a baseline and a target.</li>
<li>Draft a half-page summary for a donor report for a three-month project.</li>
<li>List the consent you would need to share a photo or story.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Outputs are what you produce; outcomes and impact are the changes that follow.</li>
<li>Use a few clear indicators with a baseline.</li>
<li>Donor reports cover activities, results, challenges, lessons and finances.</li>
<li>Be honest, ask permission for changes and respect consent.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Worked example: a youth ICT club plan',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will have seen a complete project plan from idea to closure, so you can copy the steps for your own project. The example is a youth ICT club at a secondary school in a northern Uganda town. The numbers are examples only.</p>
<h3>1. Initiating</h3>
<p><strong>Need:</strong> Many learners and out-of-school youth have never used a computer. <strong>Idea:</strong> a weekly ICT club for 40 young people aged 15 to 24 for 12 weeks, run by two trained volunteers with school support. <strong>Sponsor:</strong> the head teacher, with a small grant from a local foundation.</p>
<p><strong>Success criteria:</strong> 40 learners enrolled by week 2; at least 30 complete the course; at least 30 pass a practical test; the school keeps running the club after the grant.</p>
<h3>2. Stakeholders</h3>
<p>Learners, parents, head teacher, ICT teacher, volunteers, the funder, the local education office, the community leader. High influence and interest: head teacher and funder (manage closely). Learners are most affected, so their feedback is gathered every month.</p>
<h3>3. Scope and objectives</h3>
<ul>
<li><strong>Objective 1:</strong> enrol 40 learners by week 2.</li>
<li><strong>Objective 2:</strong> deliver 12 weekly sessions of two hours.</li>
<li><strong>Objective 3:</strong> 30 learners pass the test by the end of month 4.</li>
<li><strong>Out of scope:</strong> home internet, giving laptops to learners to keep.</li>
<li><strong>Assumptions:</strong> the school provides a room and power.</li>
</ul>
<h3>4. Work breakdown and schedule</h3>
<table>
<thead><tr><th>Phase</th><th>Main tasks</th><th>Timing</th></tr></thead>
<tbody>
<tr><td>Set-up</td><td>Agree room, borrow 10 laptops, recruit and train 2 volunteers, write curriculum</td><td>Weeks 1 to 4</td></tr>
<tr><td>Enrolment</td><td>Announce at school and community, register learners with parental consent</td><td>Weeks 3 to 5</td></tr>
<tr><td>Delivery</td><td>12 weekly sessions, monthly feedback</td><td>Weeks 6 to 17</td></tr>
<tr><td>Test and showcase</td><td>Practical test, showcase day with families</td><td>Week 18</td></tr>
<tr><td>Closure</td><td>Report, handover, lessons learned</td><td>Weeks 19 to 20</td></tr>
</tbody>
</table>
<p>Milestones: venue ready (week 4), 40 enrolled (week 5), session 6 done (week 11), test held (week 18), handover signed (week 20).</p>
<h3>5. Budget (example)</h3>
<p>Volunteer allowances for 24 sessions, printing and notebooks, airtime for communication, refreshments for the showcase day, a lockable cupboard, a laptop repair reserve and a 10 percent contingency. The school's room and electricity are shown as in-kind support.</p>
<h3>6. Risks</h3>
<ul>
<li>Learners drop out: reduce with reminders, flexible make-up sessions and a buddy system.</li>
<li>Power cuts: keep laptops charged and prepare paper-based activities.</li>
<li>Volunteer unavailable: train a third volunteer.</li>
<li>Child safety: safeguarding training, two adults present, and a clear policy on photos and online contact.</li>
</ul>
<h3>7. Doing, tracking and closing</h3>
<p>A kanban board tracks tasks, and a 20-minute team meeting happens each week. A monthly report goes to the head teacher and the funder. At closure the ICT teacher receives the laptops, curriculum and records with a signed form, a lessons-learned meeting is held with learners and volunteers, and a donor report shows outputs (40 enrolled, number completed and passed), outcomes (learners' own stories and a three-month follow-up) and a budget against actual spending.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose a project idea of your own and copy the seven headings above.</li>
<li>Fill in each heading with two to five lines.</li>
<li>Present your plan to a friend or mentor for five minutes and note their questions.</li>
<li>Improve the plan using their questions, and save it as your first full project plan.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A full plan joins need, criteria, stakeholders, scope, schedule, budget, risks and closure.</li>
<li>Protect children through safeguarding and consent.</li>
<li>Plan the handover from the beginning so the benefit continues.</li>
<li>Review and improve your plan with other people.</li>
</ul>
<h3>Certifications in general terms</h3>
<p>If you enjoy project work, you may later explore recognised credentials such as entry-level project management certificates from international bodies, and agile or Scrum fundamentals courses. Many have free introductory learning. Check current requirements, costs and recognition on the official site before paying, and remember that practical experience from real community projects is also valuable.</p>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What should happen at the closing of a project? (choose all that apply)
* Confirm deliverables against success criteria
* Hand over what the next owner needs
* Archive records and hold a lessons-learned review
- Leave unpaid bills for later
= Proper closing protects relationships and preserves learning.

Q: What is the purpose of a lessons-learned meeting?
- To find someone to blame
* To record what to keep and what to change for future projects
- To shorten the project
- To replace the donor report
= The meeting is blame-free and ends with actions.

Q: "40 learners trained and 24 sessions held" is an example of:
- Impact
- Inputs
* Outputs
- Risks
= Outputs are the direct products of the activities.

Q: Which is an outcome?
- Money received from the donor
* 32 learners use computer skills to write CVs and send e-mail
- 24 sessions delivered
- Ten laptops bought
= Outcomes are the changes for people that follow from the outputs.

Q: Why collect a baseline?
- To increase the budget
* To compare the situation before and after the project
- To hide results
- Donors never want it
= A baseline shows how much has changed.

Q: You need to change how a grant is spent. What should you do?
- Spend first and tell the donor later
- Hide the change
* Ask the donor for approval before changing
- Cancel the report
= Honest and early communication keeps trust and respects the agreement.

Q: In the youth ICT club example, why plan the handover at the start?
- It is a donor trick
* So the benefits continue after the grant ends
- To avoid training volunteers
- To stop learners attending
= Sustainability depends on the next owner being ready.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which best describes a project?
- A task that repeats each day without end
* A temporary effort with a clear result, a start and an end
- Any meeting
- Any budget
= Projects are temporary and create something unique.

Q: Which is routine work and not a project?
- Building a new school library
* Answering parents' phone calls each day
- Organising a one-day sports event
- Running a three-month training course
= Routine work repeats with no planned end.

Q: What are the five stages of a project life cycle?
- Dream, hope, spend, rest, forget
* Initiating, planning, executing, monitoring and controlling, closing
- Budgeting, buying, building, banking, bragging
- Start, stop, pause, resume, finish
= Monitoring and controlling takes place alongside the other stages.

Q: A stakeholder is:
- Only the donor
- Only the project manager
* Anyone who affects or is affected by the project
- Only the beneficiary
= Stakeholders include many people inside and outside the team.

Q: A group with high influence but low interest should be:
- Ignored
* Kept satisfied with brief, relevant updates
- Given every task
- Removed from the project
= They can affect the project, so keep them informed without overload.

Q: Which would be a strong success criterion?
- Make the community happy
* 30 of 40 learners pass a practical test by 30 November
- Work hard
- Finish sometime next year
= It is specific, measurable and time-bound.

Q: The triple constraint links:
- Staff, volunteers and donors
* Scope, time and cost
- Risk, quality and communication
- Goals, objectives and reports
= Changing one of these usually affects the others.

Q: What does the "M" in SMART stand for?
- Maybe
- Money
* Measurable
- Meeting
= A goal needs a number or evidence that shows achievement.

Q: What is scope creep?
- Reducing the budget
* Adding extra work without adjusting time, cost or people
- Cancelling the project
- A kind of Gantt chart
= Control it with a written scope and a change process.

Q: A work breakdown structure is used to:
- List donors
* Split the project into deliverables and small tasks
- Show the budget only
- Record meetings
= It organises work into manageable pieces.

Q: Which approach helps with estimating? (choose all that apply)
* Ask the people who will do the task
* Use best, likely and worst-case numbers
* Add a buffer for the unexpected
- Assume no delays will ever occur
= Realistic estimates reflect experience and uncertainty.

Q: A dependency means:
- A volunteer who depends on a donor
* One task cannot start until another finishes
- A task with no owner
- A budget line
= Dependencies decide the order of work.

Q: What does a Gantt chart show?
- Only the budget
* Tasks as bars against time, including overlaps and milestones
- Only risks
- Only people
= It shows the schedule on one page.

Q: Which is a direct cost for a project?
- A share of office rent for the whole organisation
* Laptops bought only for this project
- General bank charges
- The director's general salary
= Direct costs belong to the project alone.

Q: What is a project risk?
- A task already finished
* An uncertain event that may affect the project
- A person
- A budget total
= Risks are possible events, and issues are risks that have happened.

Q: Training a backup facilitator because one might be absent is which type of response?
- Avoid
- Accept
- Transfer
* Reduce
= It lowers the effect if the risk happens.

Q: What does a communication plan state?
- Only phone numbers
* Who needs what information, how often, by which channel and from whom
- Only the project title
- Only the donors
= It makes sure people receive the right messages.

Q: Which are good meeting practices? (choose all that apply)
* A clear purpose and agenda
* Written actions with owners and dates
* Minutes sent quickly
- Long meetings with everyone invited
= Short, focused meetings with clear actions are most useful.

Q: A project is behind schedule. What is the best first step?
- Hide it
* Find the cause and check the effect on the critical path
- Add more tasks
- Blame the volunteers
= Understanding the cause and impact allows good decisions.

Q: What is the purpose of a WIP limit on a kanban board?
- To make the board larger
* To prevent too many tasks being open so that work is finished
- To increase the budget
- To reduce quality
= Finishing before starting keeps the flow smooth.

Q: Which of these are Scrum ideas? (choose all that apply)
* Sprints
* Daily Scrum
* Retrospective
- Never meeting
= Scrum uses short cycles and regular events for inspection and improvement.

Q: A donor suddenly asks for an extra activity. What is the best response?
- Add it silently
* Assess the effect on scope, time, cost and quality, then decide with the sponsor
- Refuse without listening
- Spend the budget immediately
= A written change process keeps the project under control.

Q: For volunteers working with children, what should you do?
- Skip checks because they are volunteers
* Follow safeguarding checks and the child protection policy
- Give them private access to children
- Share children's photos on public groups
= Safeguarding protects children.

Q: What is a good way to document a partnership with another organisation?
- Nothing in writing
* A short memorandum of understanding with roles and responsibilities
- A social media post
- A verbal promise only
= Written agreements prevent misunderstandings.

Q: Which item is a good part of closing a project? (choose all that apply)
* Handover to the next owner
* Finishing payments and records
* Thanking the team and volunteers
- Leaving it to fade out
= Proper closure protects relationships and results.

Q: A lessons-learned review should be:
- A search for who to blame
* Blame-free and finished with written actions
- Secret
- Skipped when the project succeeded
= Even successes provide lessons.

Q: What is the difference between an output and an outcome?
- They mean the same
* An output is what you produce; an outcome is the change that results for people
- An outcome is the budget
- An output is the donor
= Outcomes show whether the project made a difference.

Q: What should a baseline do?
- Replace the final report
* Show the situation at the start for later comparison
- Increase costs
- Count only staff
= Without a baseline, you cannot show change.

Q: In a donor report, what should you do about problems?
* Explain them honestly with what you did and what you learned
- Hide them
- Blame the community
- Leave out the budget
= Honest reporting builds trust.
QUIZ,
];
