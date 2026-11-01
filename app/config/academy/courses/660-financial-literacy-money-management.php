<?php
return [
    'slug'        => 'financial-literacy-money-management',
    'title'       => 'Financial Literacy and Money Management',
    'category'    => 'business',
    'level'       => 'beginner',
    'icon'        => 'layers',
    'sort'        => 660,
    'summary'     => 'Take control of your money: budget, track spending, save safely, understand interest and debt, avoid scams, handle family pressure and finish with your own 90-day money plan.',
    'description' => '<p>Money problems rarely come from earning too little alone. They usually come from not knowing where the money goes, borrowing at a high cost, falling for a scheme that promises too much, or having no plan for school fees, rent and emergencies. This course gives you practical habits and simple arithmetic you can use on any income, whether you live on a student allowance, earn a salary, ride a boda-boda or run a small shop.</p><p>You will start with your money mindset and the difference between needs and wants, then look at your sources of income, including irregular income, and learn to track every shilling. Next you will build a realistic monthly budget in UGX using the 50/30/20 idea, zero-based budgeting and the envelope method, and adapt it when income changes from week to week.</p><p>The later modules cover saving and emergency funds, where to keep money safely, how interest and loan costs really work, mobile loans and loan sharks, insurance, basic investing without false promises, taxes for individuals, scams, family and relationship money pressure, and the habits that build wealth slowly. You finish with a personal financial plan and a 90-day money plan. This course gives education, not personal investment advice; for mobile money and online payment safety, see the course Digital Finance, Mobile Money and Safe Online Payments.</p>',
    'outcomes'    => [
        'Separate needs from wants and explain how your beliefs shape your money choices',
        'List your income sources and plan around irregular income',
        'Track your spending with a notebook, phone or spreadsheet',
        'Build a monthly budget in UGX using 50/30/20, zero-based or envelope methods',
        'Build an emergency fund and save for goals such as school fees and rent',
        'Calculate simple and compound interest and the true cost of a loan',
        'Spot pyramid schemes, Ponzi schemes, romance scams and loan sharks before they cost you',
        'Explain insurance, investing and individual tax basics in plain words',
        'Handle family money pressure and write your own 90-day money plan',
    ],
    'requirements' => ['No experience needed; a notebook or a phone calculator is enough', 'Willingness to be honest with yourself about your own income and spending'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Bank of Uganda', 'url' => 'https://www.bou.or.ug/'],
        ['title' => 'Uganda Revenue Authority', 'url' => 'https://www.ura.go.ug/'],
        ['title' => 'Insurance Regulatory Authority of Uganda', 'url' => 'https://www.ira.go.ug/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: Money mindset, income and tracking',
            'summary' => 'Understand how you think about money, tell needs from wants, know your income sources and learn to track where every shilling goes.',
            'lessons' => [
                [
                    'title'   => 'Money mindset: needs, wants and the choices behind them',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to tell needs from wants, notice the beliefs that drive your spending, and spot small daily leaks of money. This matters because most people do not run out of money in one big event; they lose it slowly through many small choices they never wrote down.</p>

<h3>Your money mindset</h3>
<p>Your <strong>money mindset</strong> is the set of beliefs you hold about money, often learned at home. Some people learned "money is for spending quickly before relatives ask for it". Others learned "saving is for rich people". These beliefs are not facts. A belief such as "I earn too little to save" can be tested: even UGX 1,000 a day is UGX 30,000 in a month. You can change a belief by collecting evidence against it.</p>

<h3>Needs and wants</h3>
<p>A <strong>need</strong> is something you must have to live safely and keep earning: food, shelter, basic transport, health care, school fees, and enough airtime or data to do your work or study. A <strong>want</strong> is something that improves life but can wait: a new phone when the old one works, fashion, outings, extra data bundles for entertainment. The line is not the same for everybody. A phone is a need for a freelancer who works online, but a newer model is a want.</p>
<table>
<thead><tr><th>Item</th><th>Need or want?</th><th>Why</th></tr></thead>
<tbody>
<tr><td>Hostel rent</td><td>Need</td><td>You must sleep somewhere safe</td></tr>
<tr><td>Lunch at school</td><td>Need</td><td>You must eat</td></tr>
<tr><td>Daily soda and chips</td><td>Want</td><td>Water and a cheaper snack serve the same purpose</td></tr>
<tr><td>Weekend outing</td><td>Want</td><td>Enjoyable but can be smaller</td></tr>
<tr><td>Medicine for malaria</td><td>Need</td><td>Health protects your ability to earn</td></tr>
</tbody>
</table>

<h3>Worked example: a student on an allowance</h3>
<p>Brenda, a university student, receives UGX 300,000 each month. Her needs are hostel UGX 150,000, food UGX 80,000, transport UGX 25,000 and data for study UGX 15,000. Add them: 150,000 + 80,000 + 25,000 + 15,000 = UGX 270,000. That leaves UGX 30,000, which she splits into outings UGX 20,000 and clothes UGX 10,000. The total is 270,000 + 30,000 = UGX 300,000. Seeing the numbers shows her that wants have only UGX 30,000, so one unplanned purchase of UGX 40,000 would force her to cut food.</p>

<h3>Small leaks add up</h3>
<p>Spending UGX 2,000 a day on snacks and airtime looks harmless. But 2,000 x 30 days = UGX 60,000 a month, and 60,000 x 12 = UGX 720,000 a year, which could pay one school term for a child. The goal is not to remove all joy; it is to choose small spending on purpose.</p>

<h3>Case scenario</h3>
<p>Peter, a shop assistant in Mbale, earns UGX 450,000 a month and says "my money just disappears". He buys a bottle of soda, a samosa and airtime for UGX 5,000 every day without thinking. He decided to write each purchase in a notebook for one month. The total of those small daily items was 5,000 x 30 = UGX 150,000, one third of his salary. He chose to keep UGX 2,000 a day for treats and move the rest to savings. The lesson: money does not vanish, it leaves in small pieces, and writing them down makes them visible.</p>

<blockquote><p><strong>Quick check:</strong> Is a brand-new smartphone a need or a want if your old phone still works?</p><p>Answer: A want, because the old phone already meets the need; the upgrade can wait until you have saved for it.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> If you spend UGX 3,000 a day on snacks, what is that over 30 days?</p><p>Answer: UGX 90,000, because 3,000 x 30 = 90,000.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write down one belief about money you learned as a child, and ask whether it is true or just a habit.</li>
<li>List ten things you bought last week and mark each as a need or a want.</li>
<li>Pick one small daily purchase and multiply its price by 30 and then by 12.</li>
<li>Decide on one small change you will try for the next seven days.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Your beliefs about money shape your choices, and they can be changed.</li>
<li>Needs keep you safe and earning; wants can wait or be made smaller.</li>
<li>Small daily spending grows quickly when multiplied by 30 and 365.</li>
<li>Writing amounts down turns a vague feeling into facts you can act on.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Sources of income, including irregular income',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to list your income sources, tell fixed income from irregular income, and plan around the lowest month instead of the best one. This matters because many young people in East Africa earn from several small sources, and planning as if every month will be a good month is how people fall into debt.</p>

<h3>Common income sources</h3>
<ul>
<li><strong>Salary:</strong> a fixed amount from an employer, usually monthly. Always plan with the <em>net</em> figure, which is what reaches your account after deductions such as tax and NSSF.</li>
<li><strong>Allowance:</strong> money from parents or a sponsor, often fixed but sometimes late.</li>
<li><strong>Business income:</strong> profit from selling goods or services. Profit is sales minus the cost of stock and expenses, not the money in the till.</li>
<li><strong>Side income:</strong> extra earnings such as tutoring, design jobs, poultry, or weekend farming.</li>
<li><strong>Irregular income:</strong> earnings that change from day to day or month to month, such as boda-boda riding, casual labour, seasonal farming or freelance work.</li>
</ul>

<h3>Why more than one source helps</h3>
<p>One source is a single point of failure. If a salary stops, everything stops. A second small source, even UGX 100,000 a month, gives you room to breathe. Build side income slowly and honestly, and be careful of "easy online jobs" that ask you to pay first; Module 6 explains these scams.</p>

<h3>Worked example: a boda-boda rider</h3>
<p>Isaac earns different amounts each month after fuel. His last six months, in UGX, were 450,000, 380,000, 500,000, 400,000, 350,000 and 320,000. The total is 450,000 + 380,000 + 500,000 + 400,000 + 350,000 + 320,000 = UGX 2,400,000. The average is 2,400,000 divided by 6 = UGX 400,000. But his lowest month was UGX 320,000. If he plans rent and food using the average of 400,000, then in a 320,000 month he is short by UGX 80,000 and will borrow. The safer rule is to plan with the lowest realistic month and treat anything above it as a bonus to save.</p>

<h3>Worked example: a teacher with extra income</h3>
<table>
<thead><tr><th>Source</th><th>Amount per month (UGX)</th></tr></thead>
<tbody>
<tr><td>Net salary</td><td>1,200,000</td></tr>
<tr><td>Evening tutoring</td><td>150,000</td></tr>
<tr><td>Selling eggs</td><td>90,000</td></tr>
<tr><td><strong>Total</strong></td><td><strong>1,440,000</strong></td></tr>
</tbody>
</table>
<p>Check the sum: 1,200,000 + 150,000 + 90,000 = 1,440,000. The teacher decides to budget with the salary alone, UGX 1,200,000, and send the UGX 240,000 side income straight to savings and debt repayment. That way, if tutoring stops, the household does not collapse.</p>

<h3>Case scenario</h3>
<p>Agnes, a graduate in Kampala, saw a post saying "Earn UGX 200,000 daily from your phone; pay UGX 50,000 to register". She paid by mobile money, and the "agent" asked for another UGX 100,000 for a "training fee". After she paid, the number was switched off. She lost UGX 150,000, which was more than a week of her tutoring income. She reported the number to her mobile money provider and the police, and decided that any job asking for payment before work is a warning sign. The lesson: real employers pay you; they do not charge you to start.</p>

<blockquote><p><strong>Quick check:</strong> Isaac's lowest month is UGX 320,000 and his average is UGX 400,000. Which should he use for rent and food planning?</p><p>Answer: The lowest, UGX 320,000, because planning with the average leaves him short in weaker months.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why should you use net salary, not gross, when budgeting?</p><p>Answer: Net salary is what you actually receive after deductions, so only that money can be spent.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List every source of money you had in the last three months and write the amount from each.</li>
<li>Mark each source as fixed or irregular.</li>
<li>Find your lowest month and write it as your planning figure.</li>
<li>Write one small second income idea you could test this month without paying anyone first.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Budget with net income, the amount that really reaches you.</li>
<li>Irregular income should be planned using the lowest realistic month.</li>
<li>More than one income source reduces risk.</li>
<li>Any "job" that asks you to pay first is a scam warning sign.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Tracking your spending: notebook, phone or spreadsheet',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose a tracking method, record spending in categories, and use the totals to see where your money really goes. This matters because you cannot control what you cannot see, and tracking for even one month usually reveals one or two surprising leaks.</p>

<h3>Choose a method that you will actually use</h3>
<table>
<thead><tr><th>Method</th><th>Good for</th><th>Weakness</th></tr></thead>
<tbody>
<tr><td>Notebook and pen</td><td>Cash spending, no phone needed, very simple</td><td>Adding totals by hand takes time; the book can be lost</td></tr>
<tr><td>Phone notes or a budgeting app</td><td>Quick entry in the moment</td><td>Easy to forget; apps may collect personal data, so read permissions</td></tr>
<tr><td>Spreadsheet (Excel or Google Sheets)</td><td>Automatic totals, charts and monthly comparison</td><td>Needs a computer or a phone with the app</td></tr>
</tbody>
</table>
<p>The best method is the one you can keep up for 30 days. Many people begin with a notebook and move to a spreadsheet later.</p>

<h3>How to record</h3>
<ol>
<li>Write the date, the item, the amount and the category every time you spend, or at least at the end of each day.</li>
<li>Keep receipts or take a screenshot of mobile money messages, which already list the amount and fee.</li>
<li>Use a few clear categories: food, transport, data and airtime, rent or hostel, school, health, family, savings, fun.</li>
<li>Once a week, add up each category and compare with your plan.</li>
</ol>

<h3>Worked example: a student's week</h3>
<p>Brenda, whose weekly spending plan is UGX 60,000 for flexible costs, tracked one week in a notebook.</p>
<table>
<thead><tr><th>Day</th><th>Spent (UGX)</th></tr></thead>
<tbody>
<tr><td>Monday</td><td>5,000</td></tr>
<tr><td>Tuesday</td><td>8,000</td></tr>
<tr><td>Wednesday</td><td>3,000</td></tr>
<tr><td>Thursday</td><td>12,000</td></tr>
<tr><td>Friday</td><td>20,000</td></tr>
<tr><td>Saturday</td><td>15,000</td></tr>
<tr><td>Sunday</td><td>7,000</td></tr>
<tr><td><strong>Total</strong></td><td><strong>70,000</strong></td></tr>
</tbody>
</table>
<p>The sum is 5,000 + 8,000 + 3,000 + 12,000 + 20,000 + 15,000 + 7,000 = UGX 70,000, which is UGX 10,000 over her plan of 60,000. By category: food 30,000, transport 15,000, data 7,000, social 18,000. Check: 30,000 + 15,000 + 7,000 + 18,000 = 70,000. Now she can see the overspending came from Friday and Saturday social costs, not from food.</p>

<h3>Using a spreadsheet</h3>
<p>Put dates in column A, items in column B, amounts in column C and categories in column D. To add all amounts, type <code>=SUM(C2:C31)</code> in an empty cell. To total one category, use <code>=SUMIF(D2:D31,"food",C2:C31)</code>. You do not need to be an expert; two formulas are enough.</p>

<h3>Case scenario</h3>
<p>Joan, a salon worker in Jinja, felt her tips and wages "vanished". She tracked every shilling for 30 days using a small notebook. She discovered she spent UGX 4,000 daily on airtime, snacks and small mobile money fees, which is 4,000 x 30 = UGX 120,000 a month. She also found she had lent UGX 60,000 to friends without writing it down. Once she saw the figures, she bought a weekly data bundle instead of daily airtime, wrote down every loan, and recovered most of the money owed. The lesson: tracking does not cost anything, and it often pays for itself in the first month.</p>

<blockquote><p><strong>Quick check:</strong> Joan spends UGX 4,000 a day on small items. What does she spend in 30 days?</p><p>Answer: UGX 120,000, because 4,000 x 30 = 120,000.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is the most important quality of a tracking method?</p><p>Answer: That you will use it consistently, because a simple method you keep up beats a perfect one you abandon.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose notebook, phone notes or spreadsheet and set it up today.</li>
<li>Record every purchase for seven days, however small.</li>
<li>On day seven, add the total for each category.</li>
<li>Circle the biggest category and the one that surprised you most.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Pick the tracking method you can sustain for at least 30 days.</li>
<li>Record date, item, amount and category, and keep mobile money messages.</li>
<li>Weekly totals by category show where the money truly goes.</li>
<li>Write down money you lend, so it is not forgotten or lost.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which of these is most clearly a NEED for a university student?
- A weekend outing with friends
* Safe accommodation and daily food
- A newer phone model
- Fashion shoes
= Shelter and food keep you safe and able to study. The others are wants that can wait or be reduced.

Q: A student spends UGX 3,000 every day on snacks. About how much is that in a 30-day month?
- UGX 30,000
- UGX 60,000
* UGX 90,000
- UGX 120,000
= 3,000 x 30 = 90,000. Small daily amounts become large monthly amounts.

Q: Why is "I earn too little to save" often a belief rather than a fact?
- Because saving is only for the rich
* Because even a small amount saved regularly, such as UGX 1,000 a day, builds up over time
- Because savings cannot be used
- Because interest makes everyone rich
= Saving is a habit, not a luxury. UGX 1,000 a day is UGX 30,000 a month, which is real progress.

Q: A rider's last six months of income were UGX 450,000, 380,000, 500,000, 400,000, 350,000 and 320,000. Which figure is safest to use for fixed bills?
- 500,000, the best month
- 400,000, the average
* 320,000, the lowest month
- 450,000, the first month
= Planning with the lowest realistic month prevents shortages. Anything above it becomes extra savings.

Q: A teacher earns net salary UGX 1,200,000, tutoring UGX 150,000 and egg sales UGX 90,000. What is the total monthly income?
- UGX 1,350,000
- UGX 1,290,000
* UGX 1,440,000
- UGX 1,500,000
= 1,200,000 + 150,000 + 90,000 = 1,440,000.

Q: An advert says "Earn UGX 200,000 a day from home; pay UGX 50,000 to register". What is the best response?
- Pay quickly before the offer ends
- Pay half and see
* Do not pay; real employers do not charge you to start work
- Share it with friends
= Asking for money first is a classic scam sign. Real jobs pay you, not the other way round.

Q: Which are good tracking habits? (choose all that apply)
* Record every purchase with the amount and category
* Total each category weekly
- Only record purchases above UGX 50,000
- Rely on memory at month end
= Small purchases are the leaks that matter, and memory is unreliable. Weekly totals show patterns early.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Budgeting that works',
            'summary' => 'Learn three budgeting methods, build a realistic monthly budget in UGX and adapt it for irregular income and a small business.',
            'lessons' => [
                [
                    'title'   => 'Three budgeting methods: 50/30/20, zero-based and envelope',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain three popular budgeting methods and pick the one that fits your income. This matters because a budget is not a punishment; it is a plan that tells your money where to go before it leaves, so you decide instead of wondering later.</p>

<h3>What a budget is</h3>
<p>A <strong>budget</strong> is a written plan for your income over a period, usually a month. It lists what comes in, what you will spend by category and what you will save. If you spend less than you earn, you have a surplus. If you spend more, you have a deficit, and a deficit is paid for by borrowing or by using savings.</p>

<h3>Method 1: 50/30/20 and how to adapt it</h3>
<p>The 50/30/20 rule suggests 50% of income for needs, 30% for wants and 20% for savings and debt repayment. It is a starting guide, not a law. On UGX 1,000,000 a month it gives needs 500,000, wants 300,000 and savings 200,000, and 500,000 + 300,000 + 200,000 = 1,000,000.</p>
<p>In Uganda, rent, food and transport often take more than half of a low income, so you can adapt it. On UGX 600,000 you might use 70/20/10: needs UGX 420,000, wants UGX 120,000 and savings UGX 60,000. Check: 420,000 + 120,000 + 60,000 = 600,000. The most important part is that savings has a number, even a small one.</p>

<h3>Method 2: zero-based budgeting</h3>
<p>In a <strong>zero-based budget</strong> you give every shilling a job until income minus planned spending equals zero. This does not mean spending everything; savings and debt payments count as jobs. Example for income UGX 800,000: rent 250,000, food 220,000, transport 80,000, data 30,000, health 30,000, family 50,000, savings 100,000, fun 40,000. Add: 250 + 220 + 80 + 30 + 30 + 50 + 100 + 40 = 800 (thousands). Income minus plan is zero, so nothing is left unplanned to leak away.</p>

<h3>Method 3: the envelope method</h3>
<p>With the <strong>envelope method</strong> you take the cash for a category, such as food or fun, and put it in a labelled envelope. When it is empty you stop spending in that category until the next period. It is excellent if you struggle with overspending, because the limit is physical. You can copy the idea with separate mobile money wallets or separate savings accounts, but keep cash envelopes at home safely.</p>

<table>
<thead><tr><th>Method</th><th>Best for</th><th>Main weakness</th></tr></thead>
<tbody>
<tr><td>50/30/20</td><td>Beginners with a stable income</td><td>Percentages may not fit very low incomes</td></tr>
<tr><td>Zero-based</td><td>People who like detail and control</td><td>Takes time each month</td></tr>
<tr><td>Envelope</td><td>People who overspend on small items</td><td>Cash can be stolen or lost</td></tr>
</tbody>
</table>

<h3>Case scenario</h3>
<p>Ronald, a clerk in Mbarara, earned UGX 900,000 a month and used no budget. By the 20th he was always borrowing UGX 100,000 from a mobile lender. He tried the envelope method for food and fun: UGX 200,000 for food and UGX 60,000 for fun, withdrawn at the start of the month. By the 20th his fun envelope was empty, so he stayed home instead of borrowing. After three months he stopped borrowing and saved UGX 50,000 a month. The lesson: a clear limit protects you from yourself on days when spending feels easy.</p>

<blockquote><p><strong>Quick check:</strong> On an income of UGX 1,000,000, how much does the 50/30/20 rule suggest for savings?</p><p>Answer: UGX 200,000, because 20% of 1,000,000 is 200,000.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> In a zero-based budget, is savings counted as spending?</p><p>Answer: It is counted as a planned use of money, because every shilling must have a job, and saving is a job.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write your net monthly income at the top of a page.</li>
<li>Apply 50/30/20 and then 70/20/10 to it using a calculator.</li>
<li>Compare each result with what you actually spend and note which is closer to your life.</li>
<li>Choose one method to try for the next month.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A budget is a plan for your money, written before you spend.</li>
<li>50/30/20 is a guide; adapt it to your real costs but always include savings.</li>
<li>Zero-based budgeting gives every shilling a job.</li>
<li>Envelopes set clear limits for categories where you overspend.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Building a monthly budget in UGX, step by step',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a complete monthly budget with real numbers and check that it balances. This matters because a budget that is only in your head cannot be tested, and a budget that does not balance is just a wish.</p>

<h3>Six steps to a budget</h3>
<ol>
<li><strong>Write your net income</strong> for the month, using the safe figure from Module 1.</li>
<li><strong>List fixed costs</strong>: rent, school fees saving, loan repayment, insurance. These do not change month to month.</li>
<li><strong>List variable needs</strong>: food, transport, electricity, data, health. Use your tracking notes from last month as a guide.</li>
<li><strong>Add savings first</strong>, not last. Put your emergency fund and goal savings in the plan as if they were bills.</li>
<li><strong>Add wants</strong> with what is left.</li>
<li><strong>Add everything and compare</strong> with income. The difference must be zero or a small planned buffer.</li>
</ol>

<h3>Worked example: a teacher with a salary</h3>
<p>Mr. Okello is a primary school teacher in Lira. His net salary is UGX 1,200,000. He supports one child in school and a parent in the village.</p>
<table>
<thead><tr><th>Category</th><th>Amount (UGX)</th></tr></thead>
<tbody>
<tr><td>Rent</td><td>350,000</td></tr>
<tr><td>Food</td><td>300,000</td></tr>
<tr><td>Transport</td><td>100,000</td></tr>
<tr><td>School fees saving</td><td>150,000</td></tr>
<tr><td>Electricity and water</td><td>60,000</td></tr>
<tr><td>Data and airtime</td><td>40,000</td></tr>
<tr><td>Health</td><td>40,000</td></tr>
<tr><td>Emergency savings</td><td>80,000</td></tr>
<tr><td>Family support</td><td>50,000</td></tr>
<tr><td>Wants and fun</td><td>30,000</td></tr>
<tr><td><strong>Total</strong></td><td><strong>1,200,000</strong></td></tr>
</tbody>
</table>
<p>Check the needs: 350,000 + 300,000 + 100,000 + 150,000 + 60,000 + 40,000 + 40,000 = UGX 1,040,000. Add savings 80,000 to make 1,120,000, add family support 50,000 to make 1,170,000, add wants 30,000 to make 1,200,000. The budget balances exactly. Needs are 1,040,000 divided by 1,200,000, which is about 87% of income. That is higher than 50% on purpose: this household has real costs, and the budget shows honestly where the pressure lies, so Mr. Okello knows the first way to improve is to raise income or reduce rent over time.</p>

<h3>Check, adjust, repeat</h3>
<p>At the end of the month, compare the plan with what actually happened. If food was UGX 340,000 instead of 300,000, do not give up. Raise the food line next month and take UGX 40,000 from wants. A budget is a living document; the first three months are for learning, and each month it becomes more accurate.</p>

<h3>Case scenario</h3>
<p>Sylvia, a nurse in Masaka, made a budget that totalled UGX 1,350,000 against a net income of UGX 1,100,000. She ignored the UGX 250,000 gap and hoped "something would come up". By mid-month she borrowed UGX 250,000 from a digital lender and then had to repay more than she borrowed. After that, she redid the budget, cut wants by UGX 100,000, reduced a family contribution by UGX 50,000, and found UGX 100,000 more through weekend home visits as a nurse. The new budget balanced. The lesson: if the plan is larger than your income, change the plan or the income before the month starts, not during it.</p>

<blockquote><p><strong>Quick check:</strong> A budget totals UGX 1,350,000 but income is UGX 1,100,000. How big is the gap?</p><p>Answer: UGX 250,000, because 1,350,000 minus 1,100,000 is 250,000, and it must be removed or covered by extra income.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why list savings before wants?</p><p>Answer: Because savings left until the end are usually spent; treating savings like a bill makes sure it happens.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Copy the six steps and write your own net income at the top.</li>
<li>List your fixed costs and variable needs using last month's records.</li>
<li>Add a savings line, even if it is only UGX 10,000.</li>
<li>Add all lines and check that the total equals your income.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>List income, fixed costs, variable needs, savings and wants, in that order.</li>
<li>A balanced budget has total planned spending equal to income.</li>
<li>Honest numbers matter more than perfect percentages.</li>
<li>Review monthly and adjust; accuracy improves with practice.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Budgeting with irregular income and a small shop',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to budget when your income changes from week to week, and keep a small business separate from your personal money. This matters because the usual budget advice assumes a fixed salary, but millions of people earn from riding, farming, trading and casual work.</p>

<h3>The base budget and the surplus rule</h3>
<p>With irregular income, build your budget on a <strong>base amount</strong>: the lowest realistic monthly income. Pay all needs from that. In better months, the extra money (the <strong>surplus</strong>) goes into a buffer, goals and debt repayment according to a rule you decide in advance, so good months are not simply spent.</p>

<h3>Worked example: a boda-boda rider</h3>
<p>Isaac's lowest recent month, after fuel, was UGX 320,000. His base budget:</p>
<table>
<thead><tr><th>Category</th><th>Amount (UGX)</th></tr></thead>
<tbody>
<tr><td>Rent</td><td>100,000</td></tr>
<tr><td>Food</td><td>120,000</td></tr>
<tr><td>Airtime</td><td>15,000</td></tr>
<tr><td>Motorcycle service fund</td><td>40,000</td></tr>
<tr><td>Savings</td><td>25,000</td></tr>
<tr><td>Family support</td><td>20,000</td></tr>
<tr><td><strong>Total</strong></td><td><strong>320,000</strong></td></tr>
</tbody>
</table>
<p>Check: 100,000 + 120,000 + 15,000 + 40,000 + 25,000 + 20,000 = 320,000. In a month when he earns UGX 450,000, the surplus is 450,000 minus 320,000 = UGX 130,000. His rule: buffer 80,000, bike repairs 30,000 and a goal 20,000. Check: 80,000 + 30,000 + 20,000 = 130,000. Across his six months (450,000, 380,000, 500,000, 400,000, 350,000, 320,000) the surplus above 320,000 was 130,000 + 60,000 + 180,000 + 80,000 + 30,000 + 0 = UGX 480,000. That is a real cushion that would otherwise have been spent without notice.</p>

<h3>Separate shop money from personal money</h3>
<p>A shop owner who takes cash from the till for personal needs cannot tell if the shop makes a profit. Instead, calculate profit, pay yourself a fixed "salary", and keep the rest in the business. Example for one month: sales UGX 3,000,000, cost of stock UGX 2,400,000, expenses (rent 200,000, electricity 50,000, transport 50,000) UGX 300,000. Gross margin: 3,000,000 minus 2,400,000 = 600,000. Profit: 600,000 minus 300,000 = UGX 300,000. The owner pays herself UGX 250,000 and leaves UGX 50,000 to grow stock. Use a separate mobile money number or account for the shop if you can.</p>

<h3>Case scenario</h3>
<p>Mama Esther runs a shop in Kasese. She took UGX 20,000 from the till every day for family needs and never wrote it down. In a 26-day trading month that was 20,000 x 26 = UGX 520,000, yet the shop profit was only UGX 300,000. So the shop lost UGX 220,000 of its stock value each month, and by the end of the year she could not restock and had to borrow. After learning about profit and a fixed salary, she paid herself UGX 250,000 and recorded each sale. Within six months the shop's stock began to grow. The lesson: a business that feeds the family without a salary rule slowly starves.</p>

<blockquote><p><strong>Quick check:</strong> If your lowest month is UGX 320,000 and this month you earned UGX 410,000, what is the surplus?</p><p>Answer: UGX 90,000, because 410,000 minus 320,000 is 90,000, which goes to your surplus rule.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why should a shop owner pay herself a fixed salary?</p><p>Answer: It separates personal and business money, so the shop's real profit can be seen and stock is not drained.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Find your lowest income month in the last six months and write it as your base.</li>
<li>Build a base budget that fits exactly inside it.</li>
<li>Write a surplus rule with percentages or amounts, such as buffer first, then repairs, then goals.</li>
<li>If you run a business, calculate last month's profit and choose a fixed personal salary.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Budget irregular income on the lowest realistic month.</li>
<li>Decide in advance where surplus money goes.</li>
<li>Profit is sales minus stock cost minus expenses, not cash in the till.</li>
<li>Pay yourself a fixed salary from the business and keep records.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the best description of a budget?
- A list of things you wish to buy
* A written plan for your income, spending and saving, made before you spend
- A bank statement
- A loan application
= A budget is a plan made in advance. Looking back at what you spent is tracking; a budget guides what you will do.

Q: Using the 50/30/20 rule, how much goes to wants on a monthly income of UGX 800,000?
- UGX 160,000
* UGX 240,000
- UGX 400,000
- UGX 80,000
= 30% of 800,000 = 240,000. Needs would be 400,000 and savings 160,000.

Q: Which method uses physical limits for each category, such as an envelope with cash for food?
- Zero-based budgeting
- 50/30/20
- Inflation budgeting
* The envelope method
= When the envelope is empty, spending in that category stops until the next period.

Q: A teacher's net salary is UGX 1,200,000. Planned needs are UGX 1,040,000, savings UGX 80,000, family support UGX 50,000 and wants UGX 30,000. What is the total planned, and does the budget balance?
- UGX 1,100,000, it leaves 100,000 unplanned
* UGX 1,200,000, it balances exactly
- UGX 1,300,000, it overspends
- UGX 1,150,000, it leaves 50,000 unplanned
= 1,040,000 + 80,000 + 50,000 + 30,000 = 1,200,000, which equals income, so it balances.

Q: Sylvia's budget totals UGX 1,350,000 but her net income is UGX 1,100,000. What should she do?
- Borrow the gap from a digital lender every month
- Ignore it and hope for extra income
* Reduce spending or raise income before the month starts so the plan balances
- Skip savings and rent
= A plan larger than income creates debt. Fix the plan or the income in advance.

Q: A rider's base budget is UGX 320,000 and he earns UGX 450,000 this month. How much is the surplus?
- UGX 100,000
- UGX 120,000
* UGX 130,000
- UGX 150,000
= 450,000 minus 320,000 = 130,000. A rule decides where it goes.

Q: A shop has sales of UGX 3,000,000, stock cost UGX 2,400,000 and expenses UGX 300,000. What is the profit?
- UGX 600,000
* UGX 300,000
- UGX 900,000
- UGX 2,700,000
= 3,000,000 minus 2,400,000 is 600,000; minus expenses 300,000 leaves 300,000 profit.

Q: Why should savings be placed early in the budget?
* Because money left until the end tends to be spent
- Because savings earn nothing
- Because wants do not matter
- Because banks require it
= Treating savings like a bill makes it happen.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Saving safely and planning ahead',
            'summary' => 'Build an emergency fund, save for goals, choose safe places to keep money and plan for school fees, rent and big purchases.',
            'lessons' => [
                [
                    'title'   => 'Emergency funds and savings goals',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to set an emergency fund target, break a goal into monthly savings and use simple arithmetic to plan. This matters because emergencies such as illness, a stolen phone or a lost job will happen to everyone, and without savings they force people into expensive borrowing.</p>

<h3>What an emergency fund is</h3>
<p>An <strong>emergency fund</strong> is money kept only for real, unplanned and urgent needs: a medical bill, urgent travel for a sick relative, a major repair that keeps you earning, or a gap between jobs. It is not for a sale, a party or a planned expense. Keep it separate from daily spending so you do not touch it by accident, but where you can reach it within a day or two.</p>

<h3>How big should it be?</h3>
<p>A common target is three to six months of essential expenses. Essential expenses means needs only: rent, food, transport, utilities, health, minimum debt payments. If these cost UGX 600,000 a month, then three months is 3 x 600,000 = UGX 1,800,000 and six months is 6 x 600,000 = UGX 3,600,000. That sounds large, so begin with a first mini-goal of one month, UGX 600,000. Saving UGX 50,000 a month reaches it in 600,000 divided by 50,000 = 12 months. Then keep going.</p>

<h3>Savings goals: the division method</h3>
<p>For any goal, divide the amount by the months you have. A laptop that costs UGX 1,500,000 in 10 months needs 1,500,000 divided by 10 = UGX 150,000 a month. If that is too much, extend the time: in 15 months it needs UGX 100,000 a month. Short goals (under a year), medium goals (one to five years) and long goals (over five years) can be kept in different places.</p>
<table>
<thead><tr><th>Goal</th><th>Amount (UGX)</th><th>Time</th><th>Monthly saving (UGX)</th></tr></thead>
<tbody>
<tr><td>Emergency fund, first month</td><td>600,000</td><td>12 months</td><td>50,000</td></tr>
<tr><td>Laptop</td><td>1,500,000</td><td>10 months</td><td>150,000</td></tr>
<tr><td>Rent advance</td><td>900,000</td><td>12 months</td><td>75,000</td></tr>
</tbody>
</table>

<h3>Make saving automatic</h3>
<p>"Pay yourself first": move the savings amount as soon as income arrives, before you can spend it. Use a standing order from a bank account, or move money to a separate savings wallet the same day. Saving what is left almost never works, because there is rarely anything left.</p>

<h3>Case scenario</h3>
<p>Hannah, a hairdresser in Entebbe, had no emergency fund. When her child fell ill, the hospital bill was UGX 400,000. She borrowed from a lender who asked for 20% for one month, so she repaid 400,000 x 1.2 = UGX 480,000, losing UGX 80,000 to borrowing costs. That was equal to what she could have saved in about two months. After the crisis she started saving UGX 40,000 each month in a separate account, and a year later she had UGX 480,000 ready. The lesson: an emergency fund is cheaper than emergency borrowing, and it brings peace of mind.</p>

<blockquote><p><strong>Quick check:</strong> If your essential expenses are UGX 500,000 a month, how much is a three-month emergency fund?</p><p>Answer: UGX 1,500,000, because 3 x 500,000 = 1,500,000.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Is a discount sale at a shop a good reason to use your emergency fund?</p><p>Answer: No, because an emergency is urgent and unplanned, and a sale is a choice that can wait.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Add up your essential monthly expenses (needs only).</li>
<li>Multiply by three to find your emergency fund target.</li>
<li>Choose a first mini-goal and divide it by the months you want to take.</li>
<li>Set up a separate place for it and make the first deposit this week.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>An emergency fund covers urgent, unplanned needs only.</li>
<li>Aim for three to six months of essential expenses, starting with one month.</li>
<li>Goal amount divided by months gives the monthly saving.</li>
<li>Pay yourself first and keep emergency money separate.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Where to save safely: banks, SACCOs and savings groups',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to compare places to keep savings, check whether a provider is licensed, and explain how a village savings group works. This matters because saving only helps if the money is still there when you need it, and many people have lost savings in unlicensed schemes.</p>

<h3>Where people save</h3>
<table>
<thead><tr><th>Place</th><th>Strengths</th><th>Things to watch</th></tr></thead>
<tbody>
<tr><td>Home or pocket</td><td>Easy to reach</td><td>Theft, fire and the temptation to spend; no interest</td></tr>
<tr><td>Bank savings account</td><td>Regulated, safe, can earn interest</td><td>Charges and minimum balances; read the fee list</td></tr>
<tr><td>Mobile money wallet</td><td>Convenient and quick</td><td>Good for short-term money, not as a long-term plan; withdrawal fees apply</td></tr>
<tr><td>SACCO (savings and credit cooperative)</td><td>Members save together and can borrow cheaply</td><td>Governance matters; make sure it is registered and supervised</td></tr>
<tr><td>VSLA (village savings and loan association)</td><td>Simple, local, builds discipline and gives small loans</td><td>Keep good records and clear rules; cash box security</td></tr>
</tbody>
</table>

<h3>Check that a provider is licensed</h3>
<p>Before you deposit money, always check that the provider is licensed by the Bank of Uganda or the relevant regulator. Banks and other deposit-taking financial institutions are licensed by the Bank of Uganda, and the Bank of Uganda website publishes lists of supervised institutions. Insurance companies are regulated by the Insurance Regulatory Authority, and investment products by the Capital Markets Authority. Other regulators supervise some SACCOs and money lenders; ask which regulator covers the provider and confirm it with the regulator itself, not only with the provider. Never rely on a certificate shown on a phone screen or a WhatsApp photo. This course does not quote interest rates because they change; ask the institution for its current written terms and read the fees.</p>

<h3>How a VSLA works</h3>
<p>A VSLA is a group of usually 15 to 30 neighbours who save weekly, lend small loans to each other with interest set by the group, and share out the fund at the end of a cycle. Example: 20 members each save UGX 5,000 a week for 40 weeks. Each member saves 5,000 x 40 = UGX 200,000, and the group saves 20 x 200,000 = UGX 4,000,000. Suppose the group earned UGX 800,000 in loan interest. Equal share per member: 800,000 divided by 20 = UGX 40,000. Each member receives 200,000 + 40,000 = UGX 240,000. Members who saved more shares get proportionally more, depending on the group's rules.</p>

<h3>Questions to ask before saving</h3>
<ol>
<li>Is this provider licensed, and by whom?</li>
<li>What are the fees, minimum balance and withdrawal rules?</li>
<li>Who holds the money, and what happens if the provider fails?</li>
<li>Do I get written receipts and statements?</li>
<li>Is any promise too good to be true?</li>
</ol>

<h3>Case scenario</h3>
<p>Brian, a teacher in Mukono, joined a "savings club" that promised to double his money in two months. Fifty members each deposited UGX 300,000, which is 50 x 300,000 = UGX 15,000,000 in total. The organiser had no licence and no office, only a WhatsApp group. After six weeks, the phone was switched off. Brian lost his UGX 300,000, which could have paid a term's school costs for his daughter. A colleague who had joined the registered staff SACCO still had her savings. The lesson: safety comes first; a licensed or properly registered place with clear rules is better than an exciting promise.</p>

<blockquote><p><strong>Quick check:</strong> A group says it is registered and shows a photo of a certificate on WhatsApp. Is that enough proof?</p><p>Answer: No, because photos can be faked; you should confirm with the regulator directly.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> In a VSLA of 20 members, each member saves UGX 200,000 in a cycle. What is the total saved by the group?</p><p>Answer: UGX 4,000,000, because 20 x 200,000 = 4,000,000.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List the places where you currently keep money and the risk of each.</li>
<li>Visit the Bank of Uganda website and find how supervised institutions are listed.</li>
<li>Write five questions you would ask a SACCO or bank before joining.</li>
<li>Decide where your emergency fund will live and why.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Keep savings with licensed or properly registered and supervised providers.</li>
<li>Confirm licences with the regulator, not only with the provider.</li>
<li>VSLAs and SACCOs are useful when run with clear rules and records.</li>
<li>Do not trust promises of doubled money.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Planning for school fees, rent and big purchases',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to plan for large predictable expenses, compare paying cash with paying by instalments and avoid being caught by deadlines. This matters because fees, rent and big purchases are not surprises; they arrive on dates you already know, so you can prepare for them.</p>

<h3>Sinking funds: saving for known costs</h3>
<p>A <strong>sinking fund</strong> is money you put aside regularly for an expense you know is coming. Instead of finding UGX 480,000 in one week at the start of each term, you save a small amount every month. The method is the same each time: total cost divided by months until the due date.</p>

<h3>Worked example: school fees</h3>
<p>Ms. Nabirye has one child in school. The fees are UGX 480,000 per term and there are three terms. Other yearly costs (uniform, books, trips) are UGX 360,000. Yearly total: 3 x 480,000 = 1,440,000, and 1,440,000 + 360,000 = UGX 1,800,000. Monthly saving: 1,800,000 divided by 12 = UGX 150,000. In three months she will have 3 x 150,000 = 450,000, which is just short of one term's fees, so she should start early or keep a small extra amount to cover the first term. She keeps this in a dedicated account called "school fees" so it is not mixed with daily money.</p>

<h3>Worked example: rent</h3>
<p>Many landlords ask for several months of rent in advance. If rent is UGX 300,000 a month and the landlord asks for three months upfront, that is 3 x 300,000 = UGX 900,000. Saving UGX 75,000 a month for 12 months gives 12 x 75,000 = UGX 900,000. A useful guide is that rent should not take more than about one third of your net income. If your income is UGX 900,000, one third is UGX 300,000, so rent of UGX 300,000 is at the upper limit.</p>

<h3>Cash versus hire purchase</h3>
<p>Suppose a fridge costs UGX 1,200,000 in cash. A shop offers it on instalments of UGX 100,000 a month for 16 months. The total is 100,000 x 16 = UGX 1,600,000, so you pay UGX 400,000 extra (1,600,000 minus 1,200,000), which is 400,000 divided by 1,200,000, or one third more. Alternatively, you save UGX 100,000 a month for 12 months, reach 1,200,000 and buy it in cash, and you could even bargain for a cash discount. Waiting costs time but saves money. Instalments can be reasonable for something that earns income, such as a sewing machine, but check the full total, the penalties and what happens if you miss a payment.</p>
<table>
<thead><tr><th>Option</th><th>Monthly</th><th>Months</th><th>Total (UGX)</th></tr></thead>
<tbody>
<tr><td>Save then pay cash</td><td>100,000</td><td>12</td><td>1,200,000</td></tr>
<tr><td>Instalments</td><td>100,000</td><td>16</td><td>1,600,000</td></tr>
</tbody>
</table>

<h3>Case scenario</h3>
<p>Peter, a boda rider, bought a smartphone for UGX 900,000 on weekly instalments of UGX 50,000 for 24 weeks. Total paid: 50,000 x 24 = UGX 1,200,000, which is UGX 300,000 more than the price. In week 14, he had a slow period and missed three payments. The shop added penalties and finally took the phone back, so he lost the UGX 700,000 he had already paid (14 x 50,000). He decided that next time he would save for 18 weeks first. The lesson: instalment deals must be tested against your lowest income, not your best.</p>

<blockquote><p><strong>Quick check:</strong> Yearly school costs are UGX 1,800,000. How much should you save each month?</p><p>Answer: UGX 150,000, because 1,800,000 divided by 12 = 150,000.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A fridge costs UGX 1,200,000 cash or UGX 100,000 for 16 months. How much extra does the instalment plan cost?</p><p>Answer: UGX 400,000, because 16 x 100,000 = 1,600,000 and 1,600,000 minus 1,200,000 is 400,000.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List your three biggest predictable costs this year and their due dates.</li>
<li>Divide each cost by the months left to find the monthly saving.</li>
<li>Add the three monthly figures and check that the total fits your budget.</li>
<li>If something does not fit, decide whether to extend the time or reduce the cost.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Known costs deserve a sinking fund: cost divided by months.</li>
<li>Rent that is more than about a third of income puts the budget under pressure.</li>
<li>Always compare the total of instalments with the cash price.</li>
<li>Test any payment plan against your lowest income month.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is an emergency fund for?
- Buying items on sale
* Urgent, unplanned needs such as medical bills or a gap between jobs
- Holiday spending
- Investing in shares
= An emergency fund is for real emergencies only. Using it for planned wants defeats its purpose.

Q: Essential expenses are UGX 600,000 a month. How much is a three-month emergency fund?
- UGX 1,200,000
* UGX 1,800,000
- UGX 2,400,000
- UGX 3,600,000
= 3 x 600,000 = 1,800,000. Six months would be 3,600,000.

Q: You want UGX 1,500,000 for a laptop in 10 months. How much must you save each month?
- UGX 100,000
- UGX 120,000
- UGX 175,000
* UGX 150,000
= 1,500,000 divided by 10 = 150,000.

Q: Before depositing money with a financial group, what should you do first?
- Check how many people joined
- Trust a certificate photo sent on WhatsApp
* Confirm with the regulator, such as the Bank of Uganda for banks, that it is licensed
- Ask a friend who is excited
= Licences must be confirmed with the regulator. Photos and popularity prove nothing.

Q: A VSLA of 20 members, each saving UGX 5,000 a week for 40 weeks, earned UGX 800,000 in interest. If it is shared equally, how much does each member receive in total?
- UGX 200,000
- UGX 220,000
* UGX 240,000
- UGX 260,000
= Each saves 200,000 (5,000 x 40) and gets 40,000 interest (800,000 divided by 20), so 240,000.

Q: Rent is UGX 300,000 a month and the landlord wants three months in advance. How much is that?
- UGX 600,000
- UGX 1,200,000
- UGX 300,000
* UGX 900,000
= 3 x 300,000 = 900,000. A sinking fund of 75,000 for 12 months covers it.

Q: A fridge costs UGX 1,200,000 cash, or UGX 100,000 per month for 16 months. What is the extra cost of instalments?
* UGX 400,000
- UGX 200,000
- UGX 600,000
- There is no extra cost
= 16 x 100,000 = 1,600,000, minus the cash price 1,200,000 = 400,000.

Q: Which is a warning sign of a savings scheme? (choose all that apply)
* It promises to double your money in a short time
* It has no office and only a chat group
- It gives written receipts
- It explains fees clearly
= Unrealistic returns and no verifiable identity point to a scam. Receipts and clear fees are good signs.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Interest, debt and credit',
            'summary' => 'Understand simple and compound interest, the true cost of loans and mobile credit, how to avoid loan sharks and what blacklisting means.',
            'lessons' => [
                [
                    'title'   => 'Interest explained: simple, compound and the real cost of a loan',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to calculate simple and compound interest, work out what a loan really costs and explain APR in plain words. This matters because the same money can work for you when you save and against you when you borrow, and the difference is hidden in the arithmetic.</p>

<h3>What interest is</h3>
<p><strong>Interest</strong> is the price of using someone else's money. When you borrow, you pay interest. When you save, the bank or group pays interest to you. The <strong>principal</strong> is the original amount. The <strong>rate</strong> is the interest as a percentage for a period, such as a month or a year.</p>

<h3>Simple interest</h3>
<p>Simple interest is calculated only on the principal. Formula: <code>interest = principal x rate x time</code>. Example: UGX 1,000,000 at 10% a year for 3 years gives 1,000,000 x 0.10 x 3 = UGX 300,000. The total is 1,300,000.</p>

<h3>Compound interest</h3>
<p>Compound interest is calculated on the principal plus interest already added. Same example year by year: year 1: 1,000,000 x 1.10 = 1,100,000; year 2: 1,100,000 x 1.10 = 1,210,000; year 3: 1,210,000 x 1.10 = 1,331,000. Compound interest gave UGX 331,000, simple gave UGX 300,000, a difference of UGX 31,000. Over longer periods, the gap grows fast. This is why saving early helps and why unpaid debt grows quickly. A quick idea is the "rule of 72": divide 72 by the yearly rate to estimate years to double. At 8% it is about 72 divided by 8 = 9 years; at 12% about 6 years.</p>
<table>
<thead><tr><th>Year</th><th>Simple (UGX)</th><th>Compound (UGX)</th></tr></thead>
<tbody>
<tr><td>0</td><td>1,000,000</td><td>1,000,000</td></tr>
<tr><td>1</td><td>1,100,000</td><td>1,100,000</td></tr>
<tr><td>2</td><td>1,200,000</td><td>1,210,000</td></tr>
<tr><td>3</td><td>1,300,000</td><td>1,331,000</td></tr>
</tbody>
</table>

<h3>The real cost of a loan</h3>
<p>To compare loans, look at the total repaid, the time and the extra fees. Example: you borrow UGX 600,000 with a flat rate of 5% a month for 4 months. Interest each month: 600,000 x 0.05 = 30,000. Over four months: 4 x 30,000 = 120,000. You repay 600,000 + 120,000 = UGX 720,000, which is 180,000 a month. Because you repay part of the principal each month, the true yearly cost is higher than 5% per month sounds. Always ask: "How much will I repay in total, in shillings?"</p>

<h3>APR in plain words</h3>
<p>The <strong>APR</strong> (annual percentage rate) is the yearly cost of a loan shown as one percentage, including interest and usually fees, so you can compare loans of different lengths. A short loan can hide a big yearly cost. Example: borrow UGX 100,000 for 30 days and repay UGX 110,000. The cost is 10% for one month. If you did that every month for a year it would be about 10% x 12 = 120% a year, even though "10%" sounds small. Ask any lender for the total cost and the yearly rate in writing.</p>

<h3>Case scenario</h3>
<p>Grace, a market vendor in Kampala, borrowed UGX 500,000 and was told "only UGX 75,000 interest for three months". She did not ask how this compared with a SACCO loan. The 75,000 on 500,000 is 15% for three months, which is about 60% a year if repeated (15% x 4). A SACCO member later explained that her group offered the same loan at a much lower cost. Grace repaid the first loan, but next time she compared two or three lenders before borrowing. The lesson: a loan that sounds small can be expensive; compare the total cost, not the headline.</p>

<blockquote><p><strong>Quick check:</strong> What is simple interest on UGX 500,000 at 10% a year for 2 years?</p><p>Answer: UGX 100,000, because 500,000 x 0.10 x 2 = 100,000.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why does compound interest grow faster than simple interest?</p><p>Answer: Because interest is added to the balance and then earns interest itself.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Calculate simple interest on UGX 800,000 at 5% a year for 4 years.</li>
<li>Calculate the same amount with compound interest for 2 years.</li>
<li>Take a loan offer you know of and write the total repaid and the extra cost in shillings.</li>
<li>Divide the extra cost by the amount borrowed to find the percentage for the period.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Interest is the price of money; you pay it when you borrow and earn it when you save.</li>
<li>Simple interest is on principal only; compound interest is on principal plus interest.</li>
<li>Always find the total repaid and the extra cost in shillings.</li>
<li>APR makes loans comparable; a small monthly rate can be a large yearly rate.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Borrowing wisely: mobile loans, digital lenders and loan sharks',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to decide whether a loan is wise, compare sources of credit and recognise the warning signs of loan sharks. This matters because easy phone loans can turn a small problem into a long-term debt if you do not understand the cost.</p>

<h3>Good debt and bad debt</h3>
<p>Not all debt is equal. Borrowing to buy something that helps you earn more, such as a sewing machine that you will use daily, can be reasonable if you can clearly repay it. Borrowing for entertainment, fashion or to repay another loan usually makes your position worse. Before borrowing, ask three questions: Do I truly need this? Can I repay it from my lowest income month? What is the total cost?</p>

<h3>A safe limit</h3>
<p>A common guideline is that total loan repayments should not take more than about one third of your net income. For net income of UGX 1,200,000, one third is 1,200,000 divided by 3 = UGX 400,000. If your existing repayments are already 350,000, a new loan with a monthly payment of 150,000 would bring you to 500,000, which is over the limit, so you should say no or wait.</p>

<h3>Mobile loans and digital lenders</h3>
<p>Phone-based loans are fast and need no paperwork, but they are often short and expensive. Example: you borrow UGX 50,000 for 7 days and repay UGX 58,000. The cost is 8,000, which is 8,000 divided by 50,000 = 16% for one week. If you did this every week for a year, 16% x 52 = 832% a year, although the app only shows "a small fee". Frequent use also builds a habit of living on the next loan. Before accepting, check: Is the lender licensed? Where are the terms written? What are the penalties? What permissions does the app want on your phone and contacts? Never let an app take control of your contact list to shame you when you are late.</p>

<table>
<thead><tr><th>Source</th><th>Usual cost</th><th>Risk</th></tr></thead>
<tbody>
<tr><td>Savings group or SACCO</td><td>Often lower, set by the group</td><td>You need membership and savings first</td></tr>
<tr><td>Bank loan</td><td>Regulated, may require security</td><td>Fees and collateral</td></tr>
<tr><td>Mobile and digital loan</td><td>Often very high per year</td><td>Rollover and debt habit</td></tr>
<tr><td>Informal lender or loan shark</td><td>Extremely high and unclear</td><td>Threats, seizure of property</td></tr>
</tbody>
</table>

<h3>Loan shark warning signs</h3>
<ul>
<li>No written contract, or a contract you are not allowed to keep.</li>
<li>A very high rate quoted per week or per day.</li>
<li>They hold your ID, ATM card, logbook or title as pressure.</li>
<li>They threaten you, your family or your reputation.</li>
<li>They encourage you to "roll over" the loan again and again.</li>
</ul>

<h3>Case scenario</h3>
<p>Moses, a rider in Gulu, needed UGX 200,000 for a motorcycle repair and borrowed from a lender who asked for 30% per week. After one week he owed 200,000 x 1.3 = UGX 260,000. He could not pay, so the lender rolled it over. The next week the 30% applied to 260,000, which is 78,000, so he owed 338,000. In two weeks the debt grew by UGX 138,000, and the lender then took his motorcycle. He could have asked his VSLA or SACCO, or sold a small asset. After this he reported the lender to the local authorities and joined a savings group. The lesson: delay makes a loan shark's debt grow very fast; do not borrow from one at all.</p>

<blockquote><p><strong>Quick check:</strong> Your net income is UGX 900,000. What is one third of it, the guideline limit for loan repayments?</p><p>Answer: UGX 300,000, because 900,000 divided by 3 = 300,000.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A lender holds your national ID until you repay. Is this a good sign?</p><p>Answer: No, because holding documents is a pressure tactic used by unfair lenders.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List any debt you have, with the monthly payment for each.</li>
<li>Divide the total monthly payments by your net income to find the percentage.</li>
<li>Before any new loan, write the three questions and answer them in writing.</li>
<li>Write the name of one low-cost alternative, such as a savings group, you could ask first.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Borrow only for needs or income-building, and only what your lowest month can repay.</li>
<li>Keep total repayments to about one third of net income or less.</li>
<li>Short mobile loans can have a very high yearly cost.</li>
<li>Avoid lenders who threaten, hold your documents or give no written terms.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Credit records, blacklisting and getting out of debt',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain how your repayment record follows you, protect your good name with lenders, and use a clear plan to pay off several debts. This matters because a single unpaid loan can block future opportunities, and a plan turns a heavy feeling into a list of manageable steps.</p>

<h3>Credit reference bureaus in general terms</h3>
<p>A <strong>credit reference bureau</strong> is a licensed company that collects information on how people and businesses repay loans, and shares it with lenders. In Uganda the Bank of Uganda licenses these bureaus. Banks, many SACCOs and some digital lenders send them repayment data. When you apply for a loan, the lender may check your record to decide if you are likely to repay. A history of paying on time makes borrowing cheaper and easier. A history of missed payments makes lenders say no.</p>

<h3>What blacklisting means</h3>
<p>People often say someone is "blacklisted" when a lender reports unpaid debt. In practice, a negative record can make it hard to get a loan, a phone on credit or even some jobs. It is not a punishment forever, but it takes effort to correct. Practical steps: repay what you owe or agree a written repayment plan; ask the lender in writing to update your record when you have paid; keep proof of payment; and ask the bureau how to check and correct your own record if it is wrong. Everyone has the right to understand what is being reported about them, so ask how the process works.</p>

<h3>The two ways to attack several debts</h3>
<p>Imagine you have three debts: a phone shop UGX 50,000, a friend UGX 150,000 and a loan of UGX 600,000. The total is 50,000 + 150,000 + 600,000 = UGX 800,000.</p>
<ul>
<li><strong>Snowball method:</strong> pay minimums on all, put every extra shilling on the smallest debt first. When it is cleared, move its payment to the next smallest. It gives quick wins and motivation.</li>
<li><strong>Avalanche method:</strong> put extra money on the debt with the highest interest rate first. It saves the most money. Example: two loans of UGX 400,000 each. Loan A costs 10% a month, which is 40,000 in interest per month, and Loan B costs 2% a month, which is 8,000 a month. Paying A first removes the larger cost, so you save 32,000 a month more than if you ignored A (40,000 minus 8,000).</li>
</ul>
<p>Choose the method that you will actually follow. Never take a new expensive loan to repay an old one without checking that the total cost goes down.</p>

<h3>Worked example: freeing money for debt</h3>
<p>Dorcas earns UGX 700,000 net. She tracked her spending and found UGX 90,000 of wants she could reduce to 30,000, which frees 90,000 minus 30,000 = UGX 60,000. She also sells a spare phone for UGX 140,000. With the extra, she pays the UGX 50,000 phone shop debt in month 1 (using 50,000 of the sale), and then uses the 90,000 left from the sale plus 60,000 each month towards the friend's debt of 150,000. After month 1: 90,000 + 60,000 = 150,000, so the friend is paid in full too. In two payments she cleared two of three debts.</p>

<h3>Case scenario</h3>
<p>Dorcas once ignored a mobile loan of UGX 80,000 because she was embarrassed. After several months, the lender reported her to a credit reference bureau, and her bank refused her application for a UGX 3,000,000 business loan. She contacted the lender, paid the amount owed, and requested confirmation that the account was cleared. Within a few months, her record was updated, and she kept a habit of paying small debts first. She could later borrow for her business. The lesson: avoiding a small debt can block a large opportunity, while repaying and getting proof helps repair your record.</p>

<blockquote><p><strong>Quick check:</strong> What should you ask for after fully repaying a loan that was reported as unpaid?</p><p>Answer: Written confirmation that the account is cleared and that your record has been updated, because it is proof if the record is wrong.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which method clears the highest-interest debt first?</p><p>Answer: The avalanche method, because it saves the most money in interest.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write all your debts with balance, monthly payment and the interest or fee for each.</li>
<li>Add the balances to find the total debt.</li>
<li>Choose snowball or avalanche and list the order of repayment.</li>
<li>Find at least UGX 20,000 a month from wants to speed up the first debt.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Licensed credit reference bureaus collect and share repayment history with lenders.</li>
<li>A negative record blocks future loans; pay, get proof, and ask for updates.</li>
<li>Snowball builds motivation; avalanche saves the most interest.</li>
<li>Do not hide from a lender; talk early and agree a plan in writing.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the principal in a loan?
- The interest rate
- The total repaid
* The original amount borrowed
- The lender's profit
= Principal is the starting amount. Interest is added to it.

Q: What is the simple interest on UGX 1,000,000 at 10% per year for 3 years?
- UGX 100,000
- UGX 331,000
* UGX 300,000
- UGX 130,000
= 1,000,000 x 0.10 x 3 = 300,000. Compound interest would give 331,000.

Q: UGX 1,000,000 grows at 10% compound interest per year. What is the balance after 2 years?
- UGX 1,200,000
* UGX 1,210,000
- UGX 1,100,000
- UGX 1,300,000
= Year 1: 1,100,000. Year 2: 1,100,000 x 1.10 = 1,210,000.

Q: You borrow UGX 600,000 with flat interest of 5% a month for 4 months. How much do you repay in total?
- UGX 630,000
- UGX 660,000
- UGX 690,000
* UGX 720,000
= Interest per month is 30,000, so 4 months is 120,000. 600,000 + 120,000 = 720,000.

Q: A mobile loan of UGX 50,000 for 7 days costs UGX 8,000. What percentage is that for the week?
* 16%
- 8%
- 6%
- 20%
= 8,000 divided by 50,000 = 0.16, which is 16% for one week, a very high cost.

Q: Your net income is UGX 1,200,000 and the guideline is a maximum of one third for loan repayments. What is the limit?
- UGX 300,000
- UGX 600,000
- UGX 120,000
* UGX 400,000
= 1,200,000 divided by 3 = 400,000.

Q: Which are loan shark warning signs? (choose all that apply)
* They hold your ID or ATM card
* They threaten you or your family
- They give a written contract and explain fees
- They are licensed and show their terms
= Pressure, threats and holding documents are unfair practices. Written terms and a licence are good signs.

Q: Why is repaying a small mobile loan on time important?
- It makes the lender rich
* A missed repayment can be reported and later block bigger loans
- It has no effect
- It cancels all your other debts
= Repayment history is shared with credit reference bureaus and lenders. A clean record opens future opportunities.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Protecting and growing your money',
            'summary' => 'Learn insurance basics, understand investing risk and return without false promises, and know the tax basics for individuals.',
            'lessons' => [
                [
                    'title'   => 'Insurance basics: protecting what you have built',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain how insurance works, name common types and decide what protection fits your situation. This matters because one accident, fire or illness can wipe out years of savings, and insurance moves part of that risk to a company for a small regular price.</p>

<h3>How insurance works</h3>
<p>You pay a <strong>premium</strong>, a regular fee, to an insurer. In return the insurer promises to pay for certain losses listed in a written <strong>policy</strong>. Many people pay premiums and never claim, and that is normal: the premiums of many people pay for the few who suffer a loss. Insurance is protection, not an investment, and it does not earn you profit. When someone says a policy will "make your money grow fast", ask for the terms in writing and ask which part is protection and which part is savings.</p>

<h3>Common types</h3>
<table>
<thead><tr><th>Type</th><th>What it covers</th><th>Who may need it</th></tr></thead>
<tbody>
<tr><td>Health insurance</td><td>Hospital and treatment costs</td><td>Families, workers, anyone without savings for medical bills</td></tr>
<tr><td>Life or funeral cover</td><td>A payment to your family if you die</td><td>People with dependants</td></tr>
<tr><td>Motor insurance</td><td>Damage or injury caused by vehicles; third-party cover is commonly required by law, so check the current rules</td><td>Vehicle owners, including motorcycle owners</td></tr>
<tr><td>Property or business insurance</td><td>Fire, theft and some other damage to a house, shop or stock</td><td>Shop owners and landlords</td></tr>
<tr><td>Crop and livestock insurance</td><td>Loss from drought, disease or other named events</td><td>Farmers</td></tr>
</tbody>
</table>

<h3>Terms to understand before buying</h3>
<ul>
<li><strong>Excess (deductible):</strong> the part of a claim you pay yourself.</li>
<li><strong>Exclusions:</strong> things the policy does not cover.</li>
<li><strong>Waiting period:</strong> time after buying before some claims can be made.</li>
<li><strong>Sum insured:</strong> the maximum the insurer will pay.</li>
</ul>
<p>Example: your shop stock is damaged by water and the claim is UGX 800,000. Your policy has an excess of UGX 100,000. The insurer pays 800,000 minus 100,000 = UGX 700,000.</p>

<h3>Is it worth it? A worked example</h3>
<p>A family pays a health insurance premium of UGX 120,000 a year. A single hospital admission without cover costs UGX 2,000,000. The premium is 120,000 divided by 2,000,000, which is 6% of the potential bill, and 2,000,000 is nearly 17 years of premiums (2,000,000 divided by 120,000 is about 16.7). Insurance is worth considering when a loss would be too big for your savings. For small losses you can cover yourself from your emergency fund.</p>

<h3>Buying safely</h3>
<p>Buy only from an insurer or intermediary licensed by the Insurance Regulatory Authority of Uganda, and ask for the policy document, premium receipts and claim steps. Be careful with agents who pressure you to sign quickly or who ask you to pay to a personal mobile money number. Keep your policy papers safe and tell your family where they are.</p>

<h3>Case scenario</h3>
<p>Joseph owned a small timber shop with stock worth UGX 5,000,000. A fire destroyed the shop one night. He had no insurance and no emergency fund, so he borrowed UGX 3,000,000 to restart and spent two years repaying. His neighbour, who paid a modest premium for a fire policy, received a payment and reopened within weeks. After his loss, Joseph insured his new stock with a licensed insurer and kept a small emergency fund too. The lesson: insurance does not prevent loss but it can stop a loss from destroying your whole business.</p>

<blockquote><p><strong>Quick check:</strong> A claim is UGX 800,000 and the excess is UGX 100,000. How much does the insurer pay?</p><p>Answer: UGX 700,000, because 800,000 minus 100,000 is 700,000.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Is insurance mainly a way to make money?</p><p>Answer: No, it is protection against loss; the benefit is financial security, not profit.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List the biggest financial risks in your life (illness, theft, fire, accident).</li>
<li>Mark which ones would be too big for your savings.</li>
<li>Write three questions you would ask an insurer about cover, excess and exclusions.</li>
<li>Check on the Insurance Regulatory Authority site that an insurer is licensed before buying.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Insurance spreads risk: many pay small premiums so a few can be paid after a big loss.</li>
<li>Read exclusions, excess and waiting periods before you sign.</li>
<li>Insure losses that you could not afford to pay yourself.</li>
<li>Buy only from licensed insurers and keep every document.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Investing basics: risk, return and honest expectations',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain common investment types, describe the link between risk and return, and recognise false promises. This matters because saving protects your money, while investing tries to make it grow, and growth always carries some risk. This lesson is general education and not personal investment advice; for your own decisions, speak to a licensed adviser.</p>

<h3>Saving versus investing</h3>
<p><strong>Saving</strong> is keeping money safe for short-term needs. <strong>Investing</strong> is putting money into something that may grow in value or pay you income over a longer time, accepting that it might also fall. Investing comes after you have a budget, an emergency fund and no expensive debt.</p>

<h3>Common investment types</h3>
<table>
<thead><tr><th>Type</th><th>What it is</th><th>Main risks</th></tr></thead>
<tbody>
<tr><td>Treasury bills and bonds</td><td>You lend money to the government for a set time; they are sold through Bank of Uganda auctions, often via banks</td><td>Money is locked for a period; returns can be lower than riskier options</td></tr>
<tr><td>Unit trusts</td><td>Many people pool money in a fund run by a licensed manager</td><td>Value can fall; check fees and that the manager is licensed by the Capital Markets Authority</td></tr>
<tr><td>Shares</td><td>A small ownership part of a company, for example on the Uganda Securities Exchange</td><td>Prices go up and down; you can lose money</td></tr>
<tr><td>Land and property</td><td>Buying plots or buildings</td><td>Fake titles, disputes, hard to sell quickly</td></tr>
<tr><td>Your own business</td><td>Money into stock, tools or training</td><td>Business can fail; your time and money are tied up</td></tr>
</tbody>
</table>

<h3>Risk and return</h3>
<p>In general, the higher the possible return, the higher the risk of loss. Anything promising high returns with no risk is not honest. Never accept a promise of guaranteed high profit. Also remember <strong>inflation</strong>, the general rise in prices. If prices rise 5% in a year, UGX 1,000,000 kept idle buys what about 1,000,000 divided by 1.05, or roughly UGX 952,000, would have bought a year earlier. That is why money kept for many years often needs to grow at least as fast as prices.</p>

<h3>Losses are bigger than they look</h3>
<p>If UGX 1,000,000 falls by 10%, it becomes 900,000. To return to 1,000,000 it must rise by 100,000 divided by 900,000, which is about 11.1%, not 10%. So avoiding big losses matters. A further rule is to avoid putting everything into one place. <strong>Diversification</strong> means spreading money across different types so one failure does not destroy everything.</p>

<h3>Questions to ask before investing</h3>
<ol>
<li>Who is the provider and is it licensed by the relevant regulator?</li>
<li>What exactly am I buying, and how will I get my money back?</li>
<li>What are the fees?</li>
<li>What is the worst that could happen?</li>
<li>Is anyone promising a guaranteed return? If yes, walk away.</li>
</ol>

<h3>Case scenario</h3>
<p>Samuel, a civil servant, put his whole UGX 3,000,000 savings into a plot of land that a stranger sold cheaply, saying the title was "being processed". He did not check the title at the land registry. Later he learned that the same plot had been sold to several buyers and the seller had disappeared. He lost the entire amount, and a legal case took years. After this, he began by saving three months of expenses first and then, with advice from a licensed adviser, put only a small part of his money into a regulated product. The lesson: check ownership and licences before paying, and never put everything in one place.</p>

<blockquote><p><strong>Quick check:</strong> A person promises "guaranteed 30% monthly profit, no risk". What should you conclude?</p><p>Answer: It is almost certainly a scam, because genuine investments always carry risk and cannot guarantee such returns.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> If an investment of UGX 1,000,000 falls by 10%, what is it worth?</p><p>Answer: UGX 900,000, because 10% of 1,000,000 is 100,000, which is taken away.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write whether you already have a budget, an emergency fund and no expensive debt.</li>
<li>List three investment types from the table and one risk of each in your own words.</li>
<li>Write the five questions on a card and keep it in your wallet.</li>
<li>Visit the Bank of Uganda website and find where government securities are explained.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Investing means accepting risk for possible growth; saving protects short-term money.</li>
<li>Higher possible return usually means higher risk; no honest investment is risk-free.</li>
<li>Avoid putting all your money in one place, and check licences and ownership.</li>
<li>Be suspicious of any promise of guaranteed high returns.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Taxes for individuals: the basics',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain in general terms how taxes touch your income and spending, read a payslip, and know where to get correct information. This matters because misunderstanding tax can lead to penalties, to being cheated by a "fixer", or to paying more than needed. This lesson gives general education only; tax rules change, so always confirm current details with the Uganda Revenue Authority (URA).</p>

<h3>Why taxes exist</h3>
<p>Taxes are money collected by government to pay for public services such as roads, schools, health services and security. As an individual, you meet tax in a few main ways: on income you earn, on things you buy, and sometimes on property or business activity.</p>

<h3>Tax on employment income</h3>
<p>If you are an employee, your employer normally deducts income tax (called PAYE, pay as you earn) from your salary and pays it to URA. Your employer also deducts your contribution to the National Social Security Fund (NSSF), a retirement and benefits scheme. The employee contribution is 5% of gross pay and the employer adds a further 10%. Read your payslip: it shows <strong>gross pay</strong> (before deductions), the deductions and <strong>net pay</strong> (what you receive).</p>
<table>
<thead><tr><th>Line</th><th>Amount (UGX)</th></tr></thead>
<tbody>
<tr><td>Gross pay</td><td>1,000,000</td></tr>
<tr><td>NSSF employee 5%</td><td>50,000</td></tr>
<tr><td>PAYE (an example figure only)</td><td>80,000</td></tr>
<tr><td><strong>Net pay</strong></td><td><strong>870,000</strong></td></tr>
</tbody>
</table>
<p>Check: 1,000,000 minus 50,000 minus 80,000 = 870,000. The PAYE figure above is only an illustration; the real amount depends on current rates and your pay, so use your own payslip or the URA website.</p>

<h3>Tax if you run a small business or earn on your own</h3>
<p>If you trade, run a shop, rent out property or work for yourself, you may need to register with URA, get a Taxpayer Identification Number (TIN), keep records of sales and expenses and file returns. URA has simpler arrangements for small businesses, so ask URA or visit its website to learn which applies to you. Keeping records is useful for tax and for your own profit calculation, and it also helps when you apply for a loan.</p>

<h3>Tax in your spending</h3>
<p>Value added tax (VAT) is included in the price of many goods and services. Businesses registered for VAT should issue proper receipts. Keep receipts for big purchases; they help with warranty and with business records.</p>

<h3>Protect yourself from tax scams</h3>
<ul>
<li>Pay tax only through official URA channels, never into a personal mobile money number.</li>
<li>Be careful with people who claim they can "reduce your tax" for a private fee.</li>
<li>Check messages and calls claiming to be from URA by using the official website or office, not the number in the message.</li>
</ul>

<h3>Case scenario</h3>
<p>Mercy runs a hardware shop in Mbale. She never kept records and ignored URA letters, believing small shops did not matter. A man claiming to be a "tax consultant" told her he could settle everything for UGX 500,000 paid to his personal phone. She paid him, and later found that no payment had reached URA and the penalties were growing. She went to a URA office, explained, registered properly, got her records in order, and agreed a plan. She also reported the fake consultant. The lesson: deal with tax directly through official channels and keep records from the start.</p>

<blockquote><p><strong>Quick check:</strong> A payslip shows gross pay UGX 1,000,000, NSSF UGX 50,000 and PAYE UGX 80,000. What is the net pay?</p><p>Answer: UGX 870,000, because 1,000,000 minus 130,000 in deductions is 870,000.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A stranger offers to settle your tax for a fee sent to his personal phone. What should you do?</p><p>Answer: Refuse and deal directly with URA, because tax is paid through official channels only.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>If you are employed, find your latest payslip and write gross pay, each deduction and net pay.</li>
<li>If you earn on your own, start a simple sales and expenses record for one month.</li>
<li>Visit the URA website and note how to find information about registration and a TIN.</li>
<li>Write down one rule you will follow to avoid tax scams.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Employees usually have PAYE and NSSF deducted; read the payslip.</li>
<li>Self-employed people may need a TIN, records and returns; check URA for current rules.</li>
<li>Rates change, so confirm with URA rather than relying on old information.</li>
<li>Pay tax only through official channels and keep proof.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is a premium in insurance?
- The amount the insurer pays after a loss
* The regular price you pay for the cover
- A free gift from the company
- The profit you make on a claim
= A premium is what you pay. Claim payments are what the insurer pays out.

Q: A claim is UGX 800,000 and the policy has an excess of UGX 100,000. How much does the insurer pay?
- UGX 800,000
- UGX 900,000
* UGX 700,000
- UGX 100,000
= The insurer pays the claim minus the excess: 800,000 minus 100,000 = 700,000.

Q: A shop owner wants to buy a fire policy. What should she check first?
- That the agent is very friendly
- That the price is the lowest possible
- That a friend bought one
* That the insurer is licensed by the Insurance Regulatory Authority and the policy is in writing
= A licence and written terms protect you. Friendliness and low price do not prove safety.

Q: Which statement about investing is correct?
- Some investments guarantee high returns with no risk
* Higher possible returns usually come with higher risk
- Land can never lose value
- Shares always rise
= Risk and return are linked. Honest investments never promise certain high returns.

Q: UGX 1,000,000 falls by 10%. What is it worth now?
- UGX 1,100,000
- UGX 950,000
- UGX 910,000
* UGX 900,000
= 10% of 1,000,000 is 100,000. 1,000,000 minus 100,000 = 900,000.

Q: Which should come BEFORE investing? (choose all that apply)
* A budget you follow
* An emergency fund
- Taking a loan to invest
- Putting all savings in one scheme
= Investing is for money you can leave aside. Borrowing to invest or using one scheme adds serious risk.

Q: A payslip shows gross UGX 1,000,000, NSSF UGX 50,000 and PAYE UGX 80,000. What is net pay?
- UGX 920,000
* UGX 870,000
- UGX 950,000
- UGX 830,000
= 1,000,000 minus 50,000 minus 80,000 = 870,000.

Q: A "consultant" asks you to send tax money to his personal mobile number. What do you do?
- Send it, he sounds confident
- Send half
* Refuse and pay only through official URA channels
- Ask him for a discount
= Tax is paid through official channels. A personal number is a scam sign.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 6
        [
            'title'   => 'Module 6: Scams, people and your money plan',
            'summary' => 'Recognise and avoid scams, handle family and relationship money pressure, build wealth habits and finish with a personal financial plan and 90-day money plan.',
            'lessons' => [
                [
                    'title'   => 'Scams: pyramids, Ponzi schemes, romance and phishing',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to recognise the main money scams, test any offer with a short checklist and know what to do if you have been caught. This matters because scammers target hope: the hope of fast money, love or relief from debt. For safe use of mobile money and online payments, see the course Digital Finance, Mobile Money and Safe Online Payments.</p>

<h3>Pyramid schemes</h3>
<p>In a <strong>pyramid scheme</strong> you pay to join and earn mainly by recruiting others who also pay. There is little or no real product. The money comes from new members, so the scheme must keep growing, and it must collapse because there are not enough new people. Most people at the bottom lose their money.</p>

<h3>Ponzi schemes</h3>
<p>A <strong>Ponzi scheme</strong> promises high returns from a secret investment, but it pays early investors using money from later investors. Example: 100 people each invest UGX 500,000, so the organiser holds 100 x 500,000 = UGX 50,000,000. He promises to return UGX 650,000 (a 30% profit) after one month, and he pays the first 20 people, which costs 20 x 650,000 = UGX 13,000,000. Those people tell friends, more people join, and everyone trusts the group. But no real profit exists. When new deposits stop, the organiser cannot pay and disappears. A promise of 30% a month would mean 360% a year (30 x 12), which no real business can deliver safely.</p>

<h3>Other common scams</h3>
<table>
<thead><tr><th>Scam</th><th>How it works</th><th>Defence</th></tr></thead>
<tbody>
<tr><td>Fake investment or "forex" group</td><td>Screenshots of profits, a chat group, pressure to deposit quickly</td><td>Check licence with the regulator; never send money to an individual</td></tr>
<tr><td>Romance scam</td><td>A "friend" online builds love, then needs money for a flight, customs or a hospital</td><td>Never send money to someone you have not met in person</td></tr>
<tr><td>Phishing</td><td>Messages or calls pretending to be a bank, mobile money or URA, asking for a PIN, a code or a link click</td><td>Never share PINs or codes; contact the institution using its official number</td></tr>
<tr><td>Fake job or loan fee</td><td>You pay a registration or "processing" fee first</td><td>Real jobs and real lenders do not ask for money first</td></tr>
<tr><td>Fake prize or inheritance</td><td>You must pay a fee to claim a large reward</td><td>You cannot win a competition you did not enter</td></tr>
</tbody>
</table>

<h3>A five-question check before you pay anyone</h3>
<ol>
<li>Is the person or company licensed, and can I confirm that with the regulator myself?</li>
<li>Is the return unusually high or "guaranteed"?</li>
<li>Am I being rushed or told to keep it secret?</li>
<li>Am I paid mainly for recruiting people?</li>
<li>Can I get my money back, and who exactly holds it?</li>
</ol>
<p>If you answer "no" to the first question or "yes" to any of the others, stop. Talk to a trusted older person or a bank before you act.</p>

<h3>If you were scammed</h3>
<p>Act quickly. Contact your bank or mobile money provider, report to the police, tell the regulator, save all messages, and warn others. Do not be ashamed; scammers are skilled, and reporting helps protect others. Never pay a "recovery agent", because that is another scam.</p>

<h3>Case scenario</h3>
<p>Ruth, a shop assistant in Kampala, met a man online who said he was an engineer abroad. After two months of daily chats he said he was sending her a gift parcel but needed UGX 600,000 for customs. She paid. A week later he asked for UGX 900,000 for "taxes". Only then did she tell a colleague, who recognised the pattern. Ruth had lost UGX 600,000 in total. She stopped contact, reported it, and decided never to send money to anyone she had not met. The lesson: when love or luck arrives with a fee, it is a scam.</p>

<blockquote><p><strong>Quick check:</strong> In a Ponzi scheme, where does the money for early investors' payments come from?</p><p>Answer: From later investors' deposits, because there is no real profit-making business behind it.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A friend you met online asks for UGX 300,000 for a flight to visit you. What do you do?</p><p>Answer: Refuse, because sending money to someone you have never met is the central step of a romance scam.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write the five-question check on a small card and keep it near your phone.</li>
<li>Look at your recent messages for any offers that match the scam table.</li>
<li>Tell one family member the main warning signs, especially an older relative.</li>
<li>Write who you would contact first if you were scammed: your bank, provider and police.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Pyramid schemes pay for recruiting; Ponzi schemes pay old investors with new money.</li>
<li>Guaranteed high returns and pressure to hurry are the biggest warning signs.</li>
<li>Never share PINs or codes and never send money to people you have not met.</li>
<li>If caught, report fast, keep evidence and do not pay "recovery" fees.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Family, friends and money: pressure, support and saying no',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to set a family support budget, say no kindly, lend carefully and talk about money with a partner. This matters because in many East African families, one person who earns is expected to help many; without a plan, kindness can slowly drain your income and leave you with nothing for your own future.</p>

<h3>Understanding the pressure</h3>
<p>Supporting relatives is a respected value, and helping family in need is part of many cultures. But you cannot give what you do not have. If you earn UGX 1,200,000 and relatives ask for UGX 900,000 in one month, you cannot say yes to all without damaging your own rent, food and savings. The solution is not to stop caring; it is to decide in advance how much you can give.</p>

<h3>The family support line</h3>
<p>Put a "family support" line in your budget, for example 10% of income. On UGX 1,200,000, 10% is UGX 120,000. When requests come, you give from this line. When the line is empty, the answer for the month is "not this month". A fixed amount makes the decision about the budget, not about your love for the person. It also lets you decide: a regular small amount may help more than a large, irregular one.</p>

<h3>How to say no kindly</h3>
<ul>
<li>"I would like to help, but my family support budget for this month is finished. Can we look at next month?"</li>
<li>"I can give UGX 20,000 now, not the full amount."</li>
<li>"I cannot lend money, but I can help you think of other options."</li>
<li>Offer help that is not cash, such as giving advice, time, or a connection to a savings group.</li>
</ul>

<h3>Lending to relatives and friends</h3>
<p>A good rule is to lend only an amount you could afford to lose without damage, and to treat it as a gift if it is not returned. Write down any loan with the amount, date and agreed repayment date, and ask the other person to confirm by message. Never borrow from a lender or take a loan to lend to someone else. Be extremely careful about being a <strong>guarantor</strong> (a person who promises to repay if the borrower fails). If the borrower defaults, the lender will ask you to pay.</p>

<h3>Money and relationships</h3>
<p>Money is a leading cause of disagreement in couples. Talk openly about income, debts, savings goals and how to share costs. One fair method is to share household costs in proportion to income. Example: he earns UGX 1,500,000 and she earns UGX 900,000, so their total is 2,400,000. Shared costs are UGX 1,200,000 a month. His share of income is 1,500,000 divided by 2,400,000, which is 62.5%, so he pays 62.5% of 1,200,000 = UGX 750,000. Her share is 37.5%, so she pays UGX 450,000. Check: 750,000 + 450,000 = 1,200,000. Each keeps the rest as personal money, and both agree on savings. Hidden debts and secret spending damage trust more than the money itself.</p>

<h3>Case scenario</h3>
<p>Joseph, a bank clerk in Jinja, agreed to guarantee a UGX 2,000,000 loan for his younger brother, who ran a small shop. When the shop failed, the brother stopped paying. The lender then demanded UGX 2,000,000 plus charges from Joseph. He had to pay UGX 150,000 a month for more than a year and cancel his own savings plan. The brothers stopped speaking for a while. Later, Joseph set a rule: no guaranteeing, and a fixed monthly amount for family help. He also helped his brother plan a smaller business. The lesson: signing as guarantor is the same as borrowing; if you cannot afford to repay it, do not sign.</p>

<blockquote><p><strong>Quick check:</strong> If your income is UGX 800,000 and you set a 10% family support line, how much is it?</p><p>Answer: UGX 80,000, because 10% of 800,000 is 80,000.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What does a guarantor promise?</p><p>Answer: To repay the loan if the borrower fails, so signing is a serious financial commitment for you.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Decide a monthly family support amount that fits your budget and write it down.</li>
<li>Write two polite sentences you could use to refuse a request.</li>
<li>List any loans you have given or guarantees you have signed, with amounts and dates.</li>
<li>If you have a partner, set a time this week to talk about income, debts and goals.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>You can care for family and still set a limit; plan a support line in your budget.</li>
<li>Say no kindly and offer other forms of help.</li>
<li>Lend only what you can afford to lose and write down every loan.</li>
<li>Do not guarantee a loan you cannot repay; talk openly about money in relationships.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Wealth habits, your financial plan and a 90-day money plan',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to measure your net worth, apply the habits that build wealth slowly and write a one-page financial plan with a 90-day action plan. This matters because knowledge changes nothing until it becomes a plan with dates and numbers.</p>

<h3>Know where you stand: net worth</h3>
<p><strong>Net worth</strong> is what you own (assets) minus what you owe (liabilities). Example: savings UGX 300,000, a phone worth 400,000 and a motorcycle worth 2,500,000 make assets of 300,000 + 400,000 + 2,500,000 = UGX 3,200,000. Debts: a loan of UGX 800,000. Net worth: 3,200,000 minus 800,000 = UGX 2,400,000. Calculate it every six months. If it rises, you are moving forward, even slowly.</p>

<h3>Habits that build wealth</h3>
<ol>
<li><strong>Pay yourself first.</strong> Move savings before you spend.</li>
<li><strong>Live below your income.</strong> The gap between income and spending is where wealth grows.</li>
<li><strong>Avoid expensive debt.</strong> Use it rarely, for needs or income-building, and repay early.</li>
<li><strong>Increase income.</strong> Build skills, side income and a good reputation.</li>
<li><strong>Protect what you have.</strong> Keep an emergency fund and the right insurance.</li>
<li><strong>Be patient.</strong> Small steady amounts, kept safe, grow more reliably than quick schemes.</li>
<li><strong>Review monthly.</strong> One hour a month to check your budget and goals.</li>
</ol>
<p>Example of patience: saving UGX 50,000 a month for 3 years gives 50,000 x 36 = UGX 1,800,000 before any interest, enough for a full year of school costs from the earlier example.</p>

<h3>Your one-page financial plan</h3>
<ul>
<li>Net income and your lowest month.</li>
<li>Monthly budget with a savings line.</li>
<li>Emergency fund target and where it is kept.</li>
<li>Debts and the order you will repay them.</li>
<li>Three goals with amounts and dates.</li>
<li>Insurance you need and family support amount.</li>
</ul>

<h3>The 90-day money plan</h3>
<table>
<thead><tr><th>Days</th><th>Focus</th><th>Example actions</th></tr></thead>
<tbody>
<tr><td>1 to 30</td><td>See clearly</td><td>Track every shilling, calculate net worth, build your first budget</td></tr>
<tr><td>31 to 60</td><td>Build safety</td><td>Open a separate emergency account, save UGX 50,000, list debts and cut one leak</td></tr>
<tr><td>61 to 90</td><td>Grow and protect</td><td>Review the budget, increase savings by 10%, check insurance needs, share the plan with a trusted person</td></tr>
</tbody>
</table>
<p>If you save UGX 50,000 in each of the three months, you will have 3 x 50,000 = UGX 150,000 by day 90, a real start on an emergency fund.</p>

<h3>Case scenario</h3>
<p>Evelyn, a teacher in Kabale, wrote a plan in January: track spending, save UGX 60,000 a month and repay a UGX 300,000 debt. In March a friend invited her to a group promising to double her savings in 30 days. She was tempted, but she used her five-question check, found no licence and no clear business, and declined. By day 90 she had saved UGX 180,000 (3 x 60,000), reduced her debt, and her friend later lost money in the group. The lesson: a written plan helps you say no to quick schemes, and steady progress is more reliable.</p>

<blockquote><p><strong>Quick check:</strong> Assets are UGX 3,200,000 and debts are UGX 800,000. What is the net worth?</p><p>Answer: UGX 2,400,000, because 3,200,000 minus 800,000 is 2,400,000.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> If you save UGX 60,000 for three months, how much do you have?</p><p>Answer: UGX 180,000, because 3 x 60,000 = 180,000.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Calculate your own net worth today: assets minus debts.</li>
<li>Write your one-page plan using the six headings.</li>
<li>Fill in your 90-day table with exact amounts and dates.</li>
<li>Choose a trusted person to check on your progress once a month.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Net worth equals assets minus liabilities; track it twice a year.</li>
<li>Wealth grows from steady habits: pay yourself first, live below income, avoid costly debt.</li>
<li>A one-page plan turns knowledge into action.</li>
<li>A 90-day plan creates early wins and the habits that last.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the key feature of a pyramid scheme?
- It sells a useful product at a fair price
* Members earn mainly by recruiting new members who also pay
- It is licensed by the Bank of Uganda
- It pays profits from a real factory
= Pyramids depend on endless recruitment, so they collapse and most members lose money.

Q: In a Ponzi scheme, 100 people invest UGX 500,000 each. How much does the organiser collect?
- UGX 5,000,000
- UGX 500,000
* UGX 50,000,000
- UGX 15,000,000
= 100 x 500,000 = 50,000,000. Early payouts come from this pool, not from real profit.

Q: A message says it is from your mobile money provider and asks for your PIN to "fix" your account. What do you do?
- Send the PIN
- Send half of it
- Reply with your ID number
* Never share the PIN; contact the provider on its official number
= No genuine provider asks for your PIN. This is phishing.

Q: A new online friend you have never met asks for UGX 600,000 for customs on a gift. What is this?
- A normal courtesy
* A romance scam
- A bank charge
- A tax refund
= Asking for money for fees from someone you have never met is a classic romance scam.

Q: A relative asks for UGX 900,000, but your family support line is UGX 120,000. What is the best response?
- Give the full amount and borrow later
- Refuse and stop speaking to them
* Give from the support line or explain kindly that this month's budget is used, and offer other help
- Take a mobile loan to give it all
= A fixed support line protects your budget while still allowing kindness.

Q: A couple earns UGX 1,500,000 and UGX 900,000 and shares UGX 1,200,000 of costs in proportion to income. How much does the first person pay?
- UGX 600,000
- UGX 700,000
* UGX 750,000
- UGX 900,000
= The first person earns 62.5% of the total (1,500,000 divided by 2,400,000), so pays 62.5% of 1,200,000 = 750,000.

Q: Assets are UGX 3,200,000 and debts UGX 800,000. What is net worth?
- UGX 4,000,000
* UGX 2,400,000
- UGX 3,200,000
- UGX 800,000
= Net worth is assets minus liabilities: 3,200,000 minus 800,000 = 2,400,000.

Q: Which actions help after being scammed? (choose all that apply)
* Contact your bank or mobile money provider immediately
* Report to the police and keep all messages
- Pay a "recovery agent" a fee
- Stay silent out of embarrassment
= Quick reporting and evidence help. Recovery agents are usually another scam, and silence helps scammers.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which of these is a NEED for a working adult?
- A newer phone model
* Rent, food and transport to work
- Entertainment subscriptions
- Fashion shoes
= Needs keep you safe and able to earn. The other items are wants.

Q: A student spends UGX 4,000 on snacks daily. About how much is that over 30 days?
- UGX 80,000
- UGX 100,000
* UGX 120,000
- UGX 140,000
= 4,000 x 30 = 120,000.

Q: A boda rider's last six months were UGX 450,000, 380,000, 500,000, 400,000, 350,000 and 320,000. Which is the safest figure to plan rent and food with?
- 500,000
- 400,000
- 450,000
* 320,000
= Plan with the lowest realistic month and treat the extra as surplus.

Q: Why use net income when budgeting?
* It is what you actually receive after deductions
- It is bigger than gross income
- Tax does not apply to it
- It includes loans
= Only money that reaches you can be spent, so net income is the right base.

Q: What is the main benefit of tracking spending for a month?
- It makes you earn more
* It shows where your money really goes so you can find leaks
- It removes the need for a budget
- It replaces savings
= You can only manage what you can see. Tracking reveals small leaks and surprises.

Q: Using 50/30/20 on UGX 1,000,000, how much is for savings and debt repayment?
- UGX 100,000
- UGX 300,000
- UGX 500,000
* UGX 200,000
= 20% of 1,000,000 = 200,000.

Q: A zero-based budget means:
- You spend all your money
* Income minus planned spending (including savings) equals zero
- You have no savings
- You never change the budget
= Every shilling is given a job, and savings and debt payments count as jobs.

Q: A shop earns sales of UGX 3,000,000, stock cost of UGX 2,400,000 and expenses of UGX 300,000. What is the profit?
- UGX 600,000
- UGX 2,700,000
* UGX 300,000
- UGX 900,000
= 3,000,000 minus 2,400,000 minus 300,000 = 300,000.

Q: Why should a shop owner pay herself a fixed salary?
- To avoid keeping records
* To separate personal and business money so profit and stock are protected
- To pay less tax
- To spend more freely
= Without a salary rule, daily withdrawals can exceed profit and drain the business.

Q: Essential expenses are UGX 600,000 a month. How much is a three-month emergency fund?
- UGX 1,200,000
- UGX 2,400,000
* UGX 1,800,000
- UGX 3,600,000
= 3 x 600,000 = 1,800,000.

Q: You want UGX 1,500,000 in 10 months. What must you save monthly?
* UGX 150,000
- UGX 100,000
- UGX 200,000
- UGX 125,000
= 1,500,000 divided by 10 = 150,000.

Q: Which is the best way to check that a financial provider is genuine?
- See many people joined
- Read a certificate sent on WhatsApp
- Ask the provider if it is licensed
* Confirm its licence directly with the regulator, such as the Bank of Uganda for banks
= Claims and photos can be faked. Regulators can confirm licences.

Q: Yearly school costs are UGX 1,800,000. How much should be saved monthly?
- UGX 120,000
- UGX 180,000
* UGX 150,000
- UGX 200,000
= 1,800,000 divided by 12 = 150,000.

Q: A fridge costs UGX 1,200,000 cash or UGX 100,000 per month for 16 months. How much extra do instalments cost?
- UGX 200,000
- UGX 300,000
- UGX 600,000
* UGX 400,000
= 16 x 100,000 = 1,600,000, minus 1,200,000 = 400,000.

Q: What is the simple interest on UGX 1,000,000 at 10% a year for 3 years?
- UGX 100,000
- UGX 331,000
- UGX 130,000
* UGX 300,000
= 1,000,000 x 0.10 x 3 = 300,000.

Q: You borrow UGX 600,000 at a flat 5% per month for 4 months. What do you repay in total?
- UGX 630,000
- UGX 690,000
* UGX 720,000
- UGX 800,000
= 30,000 interest per month x 4 = 120,000. 600,000 + 120,000 = 720,000.

Q: A mobile loan of UGX 50,000 for a week costs UGX 8,000 in fees. Why is this a concern?
- It is a free loan
- It is only 8%
* It is 16% for one week, which becomes a huge yearly cost if repeated
- Fees do not matter
= 8,000 divided by 50,000 = 16% for one week. Short loans can be extremely expensive over a year.

Q: Net income is UGX 900,000. What is the guideline maximum for total loan repayments (one third)?
- UGX 150,000
- UGX 450,000
* UGX 300,000
- UGX 600,000
= 900,000 divided by 3 = 300,000.

Q: Which is a loan shark warning sign? (choose all that apply)
* They hold your ID or ATM card
* They threaten you
- They give a written contract and a copy of the terms
- They have a verifiable licence
= Threats and holding documents indicate unfair practices. Written terms and a licence are positive.

Q: A lender asks 30% a week on UGX 200,000 and the loan rolls over once, with the 30% charged on the new balance. How much is owed after two weeks?
- UGX 320,000
- UGX 300,000
* UGX 338,000
- UGX 360,000
= Week 1: 200,000 x 1.3 = 260,000. Week 2: 260,000 x 1.3 = 338,000.

Q: What does a credit reference bureau do?
- Gives out free loans
* Collects repayment information and shares it with lenders
- Collects taxes
- Insures your savings
= Lenders use bureau records to decide whether to lend. Missed repayments can be reported.

Q: A claim is UGX 800,000 and the excess is UGX 100,000. How much does the insurer pay?
- UGX 800,000
- UGX 900,000
* UGX 700,000
- UGX 100,000
= The claim minus the excess: 800,000 minus 100,000 = 700,000.

Q: Which statement about investing is TRUE?
- Treasury bills can double your money in a month
- Land never loses value
* Higher possible returns usually come with higher risk, and nothing is guaranteed
- Shares never fall
= Risk and return are linked. Be wary of guarantees.

Q: UGX 1,000,000 falls by 10% to UGX 900,000. Roughly what rise is needed to get back to UGX 1,000,000?
- Exactly 10%
- 5%
- 20%
* About 11%
= The gain needed is 100,000 divided by 900,000, about 11.1%, because the percentage applies to a smaller amount.

Q: A payslip shows gross pay UGX 1,000,000, NSSF UGX 50,000 and PAYE UGX 80,000. What is the net pay?
- UGX 950,000
- UGX 920,000
* UGX 870,000
- UGX 830,000
= 1,000,000 minus 130,000 = 870,000.

Q: A stranger offers to settle your tax debt for a fee sent to his personal mobile number. What should you do?
- Pay, he seems helpful
* Refuse and deal directly with URA through official channels
- Pay half to test him
- Ask your neighbour to pay
= Tax is paid through official channels. A personal number is a scam sign.

Q: What best describes a Ponzi scheme?
* Early investors are paid with later investors' money while promising high profit from a secret business
- A savings group run by neighbours
- A bank deposit product
- A government bond
= No real profit exists. The scheme collapses when new deposits slow down.

Q: Which is a defence against romance and phishing scams? (choose all that apply)
* Never send money to someone you have not met in person
* Never share your PIN or one-time codes
- Reply quickly to urgent requests
- Click links to confirm your account
= Scammers rely on urgency and trust. Official institutions never ask for your PIN.

Q: A couple earns UGX 1,500,000 and UGX 900,000 and splits UGX 1,200,000 of shared costs in proportion to income. How much does the person earning UGX 900,000 pay?
- UGX 400,000
* UGX 450,000
- UGX 500,000
- UGX 600,000
= Her share is 900,000 divided by 2,400,000 = 37.5%. 37.5% of 1,200,000 = 450,000.

Q: Your friend asks you to guarantee his loan of UGX 2,000,000. What should you remember?
- Nothing, it is only a signature
- The lender will cancel the loan if he cannot pay
* If he defaults, you may have to repay it, so only sign if you can afford the whole amount
- Guarantors are never contacted
= A guarantor promises to repay if the borrower fails. It is a serious debt risk.
QUIZ,
];
