<?php
return [
    'slug'        => 'digital-finance-mobile-money',
    'title'       => 'Digital Finance, Mobile Money and Safe Online Payments',
    'category'    => 'business',
    'level'       => 'beginner',
    'icon'        => 'briefcase',
    'sort'        => 400,
    'summary'     => 'Manage money in a digital world: budgeting, saving, mobile money, bank apps, online payments, scams, loans, insurance and simple records for a small business, with a strong focus on staying safe.',
    'description' => <<<'HTML'
<p>Money has moved onto our phones. Millions of people in East Africa now receive wages, pay school fees, buy airtime, save and borrow through mobile money, bank apps and online shops. This brings speed and convenience, but it also brings new risks: fake agents, SIM swap fraud, phishing messages, predatory digital loans and investment schemes that promise easy riches.</p>
<p>This course teaches the basic ideas of personal and small-business money management and shows how to use digital financial services with confidence. You will learn to budget and save, understand interest and loan costs, use mobile money and bank apps correctly, pay online and by QR code, recognise and report scams, keep simple records and accept digital payments in a small business.</p>
<p>No experience is needed. The course gives general education only. It does not give personal financial advice, and it does not recommend any company. Rules, limits, fees and consumer protections differ between countries and between providers, so always check the current terms from your own provider and your national regulator.</p>
HTML,
    'outcomes'    => [
        'Prepare a simple monthly budget and set a realistic saving goal',
        'Explain simple and compound interest and compare the true cost of savings and loans',
        'Use mobile money and bank apps for sending, receiving, paying and checking balances correctly',
        'Pay online and by QR code while checking that a site or request is genuine',
        'Recognise common scams such as SIM swap, fake agents, phishing, fake loans and investment fraud',
        'Protect your PIN, SIM card, phone and accounts with strong habits',
        'Read a digital loan offer, compute its total cost and use credit responsibly',
        'Keep simple records, separate personal and business money and accept digital payments safely',
    ],
    'requirements'=> [
        'No experience needed',
        'A mobile phone or computer is helpful for the practice activities, but a notebook is enough',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Bank of Uganda: consumer and financial education information', 'url' => 'https://www.bou.or.ug'],
        ['title' => 'Central Bank of Kenya', 'url' => 'https://www.centralbank.go.ke'],
        ['title' => 'Uganda Communications Commission: information for telecom users', 'url' => 'https://www.ucc.co.ug'],
        ['title' => 'FSD Africa: financial sector research and learning', 'url' => 'https://www.fsdafrica.org'],
        ['title' => 'Consumers International: consumer rights', 'url' => 'https://www.consumersinternational.org'],
    ],
    'modules' => [
        // ------------------------------------------------------------------ MODULE 1
        [
            'title'   => 'Module 1: Money basics: budgeting, saving and interest',
            'summary' => 'Build the habits and the vocabulary that every digital financial service depends on.',
            'lessons' => [
                [
                    'title'   => 'Income, spending and the monthly budget',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a simple monthly budget and use it to decide what you can afford. This matters because most money problems, from school fees stress to unpaid loans, begin with not knowing where the money goes.</p>
<h3>What is a budget?</h3>
<p>A <strong>budget</strong> is a plan for your money over a period, usually one month. You write down the money you expect to receive (<strong>income</strong>) and the money you plan to spend (<strong>expenses</strong>). A budget is not a punishment. It is a map that helps you reach what you want.</p>
<h3>Fixed, variable and irregular costs</h3>
<table>
<thead><tr><th>Type</th><th>Meaning</th><th>Examples</th></tr></thead>
<tbody>
<tr><td>Fixed</td><td>Same amount each month</td><td>Rent, a loan instalment, a monthly subscription</td></tr>
<tr><td>Variable</td><td>Changes from month to month</td><td>Food, transport, airtime, data bundles, electricity tokens</td></tr>
<tr><td>Irregular</td><td>Comes a few times a year</td><td>School fees, medical bills, festival gifts, repairs</td></tr>
</tbody>
</table>
<p>Irregular costs surprise people. A good trick is to divide the yearly amount by twelve and set that aside every month. If school fees are UGX 600,000 a term, three terms a year, the yearly cost is UGX 1,800,000, so you set aside UGX 150,000 each month.</p>
<h3>Needs, wants and savings</h3>
<p><strong>Needs</strong> are things you cannot live or work without: food, shelter, basic transport, school and health. <strong>Wants</strong> are things that are nice to have: new clothes, outings, extra data for games. A common rule of thumb divides income into about half for needs, about three tenths for wants and about two tenths for savings and debt repayment. It is only a starting point. If your needs take more than half, adjust honestly.</p>
<h3>Making your first budget step by step</h3>
<ol>
<li>Write your <strong>expected income</strong> for the month. Include every source: salary, pocket money, small business profit, support from family.</li>
<li>List <strong>fixed costs</strong> first, because they must be paid.</li>
<li>List <strong>variable costs</strong>. Use your mobile money statement and last month's memory to guess honestly.</li>
<li>Add a line for <strong>irregular costs</strong> (the monthly set-aside above).</li>
<li>Add a line called <strong>Savings</strong> and pay it to yourself first, as soon as money arrives.</li>
<li>Subtract total expenses from income. If the answer is negative, you are planning to spend more than you earn. Reduce wants or find extra income.</li>
</ol>
<h3>A small worked example</h3>
<pre><code>Income (part-time job + small sales)   400,000
Rent share                             120,000
Food                                    90,000
Transport                               50,000
Airtime and data                        30,000
School materials set-aside              40,000
Savings                                 50,000
Fun and gifts                           20,000
Total expenses                         400,000
Money left to allocate                       0</code></pre>
<p>This is a <strong>zero-based budget</strong>: every shilling has a job. If money is left at the end of the month, it can move to savings.</p>
<h3>Tracking</h3>
<p>A budget only works when you compare it with reality. At the end of the week, check your spending using a notebook, a spreadsheet or your mobile money statement. Notice which lines go over and why. Small daily spends such as snacks and extra bundles add up quickly.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write all your income sources for the coming month.</li>
<li>List your fixed, variable and irregular costs using the table above.</li>
<li>Decide a savings amount and write it as the first expense.</li>
<li>Subtract the total. Adjust until the result is zero or positive.</li>
<li>After one week, compare your actual spending with the plan.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A budget is a plan that matches spending to income.</li>
<li>Fixed, variable and irregular costs all need a place in the plan.</li>
<li>Pay savings first, not last.</li>
<li>Track real spending and adjust the budget every month.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Saving: goals, emergency funds and where to keep money',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to set a saving goal, build a small emergency fund and compare safe places to keep your savings. This matters because a savings cushion stops a sudden problem, such as an illness or a broken phone, from forcing you into expensive borrowing.</p>
<h3>Why save?</h3>
<p>Saving means keeping part of your income for the future instead of spending it now. There are three common reasons: to handle <strong>emergencies</strong>, to pay for <strong>planned goals</strong> (school fees, a laptop, stock for a business) and to build <strong>long-term security</strong> (land, a house, retirement).</p>
<h3>The emergency fund</h3>
<p>An <strong>emergency fund</strong> is money set aside only for unexpected, necessary costs. Experts often suggest building toward three to six months of basic living costs. That can feel impossible at the start, so begin small. A first target of one week of basic costs is a real achievement. Then grow it.</p>
<p>Keep the emergency fund separate from your everyday money, so you are not tempted to spend it. Do not count it as available for a new phone or a party.</p>
<h3>Setting a SMART saving goal</h3>
<p>A good goal is Specific, Measurable, Achievable, Relevant and has a Time limit. Compare:</p>
<ul>
<li>Weak: "I want to save money."</li>
<li>Strong: "I will save UGX 600,000 in 12 months for a second-hand laptop by saving UGX 50,000 every month."</li>
</ul>
<p>Divide the target by the number of months to find the monthly amount. If that is too large, extend the time or lower the target.</p>
<h3>Where can you keep savings?</h3>
<table>
<thead><tr><th>Option</th><th>Good points</th><th>Things to watch</th></tr></thead>
<tbody>
<tr><td>Cash at home</td><td>Easy to reach</td><td>Can be lost, stolen or spent too easily</td></tr>
<tr><td>Mobile money wallet</td><td>Convenient, no travel</td><td>Made for moving money; wallet limits apply; easy to spend</td></tr>
<tr><td>Savings account at a licensed bank or microfinance institution</td><td>Regulated, may earn interest, may be protected by a deposit protection scheme in some countries</td><td>Fees, minimum balances, withdrawal rules</td></tr>
<tr><td>Savings group (VSLA) or SACCO</td><td>Discipline, community support, small loans</td><td>Check how the group is run, who keeps the box or account, and whether the SACCO is registered and supervised</td></tr>
<tr><td>Mobile savings products linked to a wallet</td><td>Locked savings possible</td><td>Read the terms and any fees</td></tr>
</tbody>
</table>
<p>Rules about deposit protection and how SACCOs and savings groups are supervised differ by country, so ask your national regulator or provider.</p>
<h3>Beating the urge to spend</h3>
<ul>
<li>Set up the saving transfer on the day you are paid.</li>
<li>Keep savings out of sight, for example in a different account or a locked product.</li>
<li>Give each goal a name, such as "Laptop" or "Fees". Naming makes the money feel real.</li>
<li>Celebrate small milestones, such as reaching a quarter of the target.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Write one saving goal with an amount, a date and a reason.</li>
<li>Divide the amount by the number of months to find the monthly saving.</li>
<li>Choose a safe place for the money and write why you chose it.</li>
<li>Write the date of your first transfer and set a phone reminder.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Save for emergencies, planned goals and the long term.</li>
<li>Start the emergency fund small and keep it separate.</li>
<li>Use SMART goals and divide the target by the months available.</li>
<li>Use regulated, trustworthy places for savings and read the terms.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Interest, inflation and the real cost of money',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to calculate simple and compound interest and explain why a loan or a saving product costs or earns more than it first seems. This matters because every loan advert, savings offer and digital lending app speaks the language of interest.</p>
<h3>What is interest?</h3>
<p><strong>Interest</strong> is the price of using money. When you borrow, you pay interest to the lender on top of the amount you borrowed (the <strong>principal</strong>). When you save in a bank or similar product, the institution may pay you interest for using your money.</p>
<h3>Simple interest</h3>
<p>With <strong>simple interest</strong>, interest is calculated only on the original principal.</p>
<pre><code>Interest = Principal x Rate x Time
Example: UGX 200,000 at 10% a year for 2 years
Interest = 200,000 x 0.10 x 2 = 40,000
Total to repay = 240,000</code></pre>
<h3>Compound interest</h3>
<p>With <strong>compound interest</strong>, interest is added to the principal and then earns interest itself. Over time this grows faster. It works for you when you save and against you when you borrow and do not repay.</p>
<pre><code>UGX 200,000 at 10% a year, compounded yearly
Year 1: 200,000 x 1.10 = 220,000
Year 2: 220,000 x 1.10 = 242,000
Year 3: 242,000 x 1.10 = 266,200</code></pre>
<p>After two years, compound interest gives 242,000 compared with 240,000 for simple interest. The gap widens every year.</p>
<h3>Rates need a time period</h3>
<p>A rate is meaningless without a period. "3 percent" could mean per day, per week, per month or per year. Always ask: <strong>per what period?</strong> A rate of 10 percent a month is about 120 percent a year before compounding, which is very expensive.</p>
<h3>APR and total cost</h3>
<p>The <strong>annual percentage rate (APR)</strong> or effective annual rate tries to show the yearly cost of a loan including interest and some fees, so you can compare offers. Not every country or lender shows it the same way, so the safest question is: <strong>"If I borrow this amount, what is the total I will repay, in shillings, and by when?"</strong></p>
<h3>Inflation</h3>
<p><strong>Inflation</strong> is a general rise in prices. If prices rise 5 percent in a year, UGX 100,000 buys less next year. When your savings earn less interest than inflation, their real buying power falls. This is why money kept for years under a mattress or in a wallet with no growth slowly loses value. It also shows why simply "saving" is not the same as keeping value for the long term.</p>
<h3>The rule of 72</h3>
<p>A quick mental tool: divide 72 by the yearly rate to estimate how many years it takes to double money with compound growth. At 6 percent, 72 divided by 6 gives about 12 years. It is only an estimate.</p>
<h3>Try it yourself</h3>
<ol>
<li>Calculate simple interest on UGX 300,000 at 12 percent a year for 3 years.</li>
<li>Calculate the compound total for the same amount, rate and time, year by year, with a calculator.</li>
<li>Write the difference between the two answers.</li>
<li>Find a loan or saving advert and write down whether it states the period of the rate.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Interest is the price of using money, paid by borrowers and sometimes earned by savers.</li>
<li>Compound interest grows faster than simple interest because interest earns interest.</li>
<li>Always ask what period a rate covers and what the total repayment will be.</li>
<li>Inflation reduces what money can buy, so compare returns with price rises.</li>
</ul>
<h3>Common mistakes</h3>
<ul>
<li>Comparing a monthly rate with a yearly one as if they were equal.</li>
<li>Looking only at the instalment and not at the total repayment.</li>
<li>Ignoring fees that are charged on top of the interest.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is a zero-based budget?
- A budget with no savings line
* A budget where income minus planned spending and saving equals zero, so every shilling has a job
- A budget with zero fixed costs
- A budget you never check
= Every shilling is given a purpose, including savings, so nothing disappears unnoticed.

Q: School fees of UGX 900,000 are due three times a year. How much should you set aside every month to cover them?
- UGX 75,000
- UGX 150,000
* UGX 225,000
- UGX 900,000
= Three terms of 900,000 make 2,700,000 a year. Dividing by 12 gives 225,000 a month.

Q: Which of these is an irregular cost?
- Monthly rent
* Hospital bill after an accident
- Weekly airtime
- A loan instalment
= Irregular costs come unexpectedly or only a few times a year.

Q: Which saving goal is the strongest?
- "Save more this year"
- "Try to keep some money"
* "Save UGX 60,000 every month for 10 months to buy a UGX 600,000 laptop"
- "Maybe buy a laptop one day"
= A SMART goal has an amount, a date and a clear reason.

Q: Why should an emergency fund be kept separate from everyday money?
* So it is not spent on non-emergencies
- Because it earns no value
- Because the law requires it
- So that it can be hidden from family forever
= Separation reduces the temptation to use the fund for wants.

Q: UGX 100,000 is borrowed at 10 percent simple interest a year for 3 years. What is the total to repay?
- 110,000
- 133,100
* 130,000
- 100,300
= Interest is 100,000 x 0.10 x 3 = 30,000, so the total is 130,000. The figure 133,100 is the compound result.

Q: A lender advertises "5 percent" with no period. What should you ask?
- Whether the lender has a nice office
* Is it per day, week, month or year, and what is the total I repay?
- How many friends have already borrowed
- Nothing, 5 percent is always small
= A rate without a period cannot be compared. Five percent a week would be very costly.

Q: What does inflation do to money kept without growth for years?
- Increases what it can buy
- Has no effect
* Reduces what it can buy as prices rise
- Doubles it automatically
= When prices rise faster than your money grows, buying power falls.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 2
        [
            'title'   => 'Module 2: Mobile money, bank accounts and cards',
            'summary' => 'Understand how digital money moves and use wallets, agents, bank apps and cards correctly.',
            'lessons' => [
                [
                    'title'   => 'How mobile money works',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what mobile money is, who is involved and what you can do with it. This matters because mobile money is the main way many people in East Africa save, send and pay, and understanding the system helps you spot when something is wrong.</p>
<h3>What is mobile money?</h3>
<p><strong>Mobile money</strong> is a service that lets you store and move money using your mobile phone number. Your money sits in a <strong>wallet</strong> (also called a mobile money account) linked to your SIM card and your registered identity. You do not need a bank account to open one in most countries, although you usually need your national ID and a registered SIM.</p>
<h3>The people and parts of the system</h3>
<ul>
<li><strong>The provider:</strong> a licensed company, often a telecom company or a company working with one, that runs the wallets. In many countries the money held for customers is kept in regulated bank accounts and the service is supervised by the central bank and the telecom regulator.</li>
<li><strong>Agents:</strong> shops and kiosks that help customers put cash in (<strong>cash-in</strong> or deposit) and take cash out (<strong>cash-out</strong> or withdrawal), and often register new customers.</li>
<li><strong>Customers:</strong> people like you who hold wallets.</li>
<li><strong>Merchants:</strong> businesses that accept mobile money for goods and services, often through a till or merchant number.</li>
<li><strong>The regulator:</strong> sets rules on licensing, customer protection and limits.</li>
</ul>
<h3>What you can usually do</h3>
<table>
<thead><tr><th>Action</th><th>What it means</th></tr></thead>
<tbody>
<tr><td>Send money</td><td>Move money from your wallet to another wallet</td></tr>
<tr><td>Receive money</td><td>Money arrives in your wallet; you get a confirmation message</td></tr>
<tr><td>Cash in and cash out</td><td>Exchange physical cash for e-money, and back, at an agent</td></tr>
<tr><td>Pay bills</td><td>Pay electricity, water, TV, school fees or taxes through a bill number</td></tr>
<tr><td>Buy airtime and data</td><td>Top up your own or someone else's phone</td></tr>
<tr><td>Pay merchants</td><td>Pay a shop or online seller using a till or code</td></tr>
<tr><td>Bank links</td><td>Move money between a wallet and a bank account in many countries</td></tr>
<tr><td>Save and borrow</td><td>Some providers offer savings and small loans through partner institutions</td></tr>
</tbody>
</table>
<h3>How you reach the service</h3>
<p>Most services work in two ways: an <strong>app</strong> on a smartphone, and a <strong>USSD menu</strong> that works on any phone by dialling a short code that starts with an asterisk and ends with a hash. USSD works without internet, which helps in places with poor data coverage. Short codes and menus differ by provider and country, so use the code that your own provider publishes.</p>
<h3>Fees, limits and rules</h3>
<p>Providers charge fees for many actions, and they set <strong>limits</strong>: the most you can hold in a wallet, send in one transaction and move in a day or month. Limits and fees are set by providers within the rules of each country and change from time to time. This course does not quote any current amounts. To find the current ones, look in your provider's app or published tariff, ask at an official service centre or visit the provider's official website.</p>
<p>Some countries also apply taxes on certain transactions. Rules differ, so read the information from your own regulator and provider.</p>
<h3>Why it is called e-money</h3>
<p>The balance in your wallet is <strong>electronic money</strong>. It is a record of how much the provider owes you, which you can turn into cash at an agent. That is why the registered owner, your PIN and your SIM matter so much.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open your provider's app, or dial the USSD code printed in their official materials.</li>
<li>Find the menu for checking your balance and your mini-statement.</li>
<li>Find where the provider lists fees and limits and write down the limit for one transaction.</li>
<li>List three things you used mobile money for last month.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Mobile money is a wallet linked to your registered SIM and identity.</li>
<li>Agents turn cash into e-money and e-money into cash.</li>
<li>Fees and limits exist and change, so check your provider's current terms.</li>
<li>Rules differ by country and provider.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Sending, receiving and paying step by step',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to send, receive and pay with mobile money carefully and to know what to do if you make a mistake. This matters because a single wrong digit in a phone number can send your money to a stranger.</p>
<h3>Sending money safely</h3>
<ol>
<li>Open the app or the USSD menu and choose <strong>Send money</strong>.</li>
<li>Type the recipient's number. <strong>Read every digit twice.</strong> Better still, save the number in your contacts first.</li>
<li>Enter the amount.</li>
<li>Check the confirmation screen. Most services show the recipient's registered name. If the name is not the one you expect, <strong>stop and cancel</strong>.</li>
<li>Look at the fee shown, if any.</li>
<li>Enter your <strong>PIN</strong> only on the official screen or menu, never in a text message and never to a person who is "helping" you.</li>
<li>Wait for the confirmation message and keep it. It contains a <strong>transaction ID</strong>, which is the reference number you will need if there is a problem.</li>
</ol>
<h3>Receiving money</h3>
<p>You receive money passively: the sender sends, and your wallet balance goes up. The provider sends you a message. Before you release goods, hand over a phone or give change, <strong>check your balance in the app or by dialling the official menu</strong>. Fake messages that look like confirmations are a common trick, which Module 3 explains.</p>
<h3>Cash-out and cash-in at an agent</h3>
<ul>
<li>Use agents with a visible licence or official branding, and a proper till or shop, not a stranger who approaches you.</li>
<li>Start the cash-out yourself on your own phone. Never hand your phone and PIN to the agent.</li>
<li>Check the amount on your screen before confirming.</li>
<li>Count the cash before leaving.</li>
<li>Keep the message as your record.</li>
</ul>
<h3>Paying bills and merchants</h3>
<p>Choose <strong>Pay bill</strong> or <strong>Pay merchant</strong> in the menu and enter the business or biller number and the account reference, such as a meter number or student number. Check the name shown. Paying to the wrong account number is like posting a letter with the wrong address, and it may take days to fix. For school fees, always keep the transaction ID and the school's receipt.</p>
<h3>If you send money to the wrong number</h3>
<ol>
<li>Do not panic and do not send more money.</li>
<li>Note the transaction ID, the amount, the time and the number.</li>
<li>Contact your provider immediately through its <strong>official</strong> customer service channel to ask for help with a reversal. Providers generally need the recipient's cooperation or follow a formal process, so reversals are not guaranteed, but speed helps.</li>
<li>Do not trust people who call and say they will "reverse" it for a fee.</li>
</ol>
<h3>Good habits</h3>
<ul>
<li>Check your wallet statement regularly and look for payments you did not make.</li>
<li>Use a transaction limit that suits your needs, if your provider lets you set one.</li>
<li>Delete or ignore messages that ask you to dial a code you do not recognise.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Save the number of a trusted friend or family member in your contacts with their full name.</li>
<li>Walk through the Send money menu to the confirmation screen without sending. Notice whether the name appears.</li>
<li>Write a short checklist of five checks to make before sending money and keep it in your notes.</li>
<li>Find your provider's official customer care contact and save it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Check the number and the displayed name before you confirm.</li>
<li>Keep the transaction ID and every confirmation message.</li>
<li>Check your own balance before handing over goods.</li>
<li>Report mistakes quickly through the official channel and ignore helpers who ask for fees.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Bank accounts, cards and banking apps',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe the main types of bank accounts and cards and use a banking app safely. This matters because a bank account can protect larger savings, give you a record of your income and connect you to loans, while cards allow payments at shops, ATMs and online.</p>
<h3>Bank accounts</h3>
<table>
<thead><tr><th>Account</th><th>Purpose</th><th>Notes</th></tr></thead>
<tbody>
<tr><td>Savings account</td><td>Keep money safely and earn interest</td><td>May limit how often you withdraw; may have a minimum balance</td></tr>
<tr><td>Current or checking account</td><td>Everyday payments, salary, business use</td><td>Often has more payment tools and higher fees</td></tr>
<tr><td>Student or youth account</td><td>Simple accounts with lower requirements</td><td>Check the age and document rules</td></tr>
<tr><td>Fixed deposit</td><td>Lock money for a set time for a higher rate</td><td>Early withdrawal may cost you</td></tr>
</tbody>
</table>
<p>To open an account you generally provide a national ID or passport, proof of address, passport photos and sometimes a reference. Banks and similar institutions must verify who you are. This is called <strong>Know Your Customer (KYC)</strong> and it protects you and the system from fraud.</p>
<h3>Types of cards</h3>
<ul>
<li><strong>Debit card:</strong> spends money that is already in your account. If the money is not there, the payment is declined.</li>
<li><strong>Credit card:</strong> spends money the bank lends you, which you must repay, often with high interest if you pay late.</li>
<li><strong>Prepaid card:</strong> you load an amount first and can only spend that amount. It can be useful for online shopping because it limits the risk.</li>
</ul>
<p>Every card has a long number, an expiry date and a three or four digit security code (CVV or CVC). The code on the back and your PIN are separate secrets. Never share a card number with the security code and the one-time passwords sent to you.</p>
<h3>ATMs and agent banking</h3>
<p>Use ATMs in safe places, such as inside a bank or a busy shopping centre. Cover the keypad when you enter your PIN. If a machine looks odd, with loose parts or a camera lens, do not use it. Many banks also work through <strong>agents</strong> who allow small deposits and withdrawals in villages.</p>
<h3>Banking apps and USSD</h3>
<p>Most banks offer a mobile app, internet banking and a USSD service. With them you can check balances, transfer money, pay bills and sometimes freeze a card. Follow these rules:</p>
<ol>
<li>Install the app only from your phone's official app store and check that the publisher is the bank.</li>
<li>Protect the app with a strong password or fingerprint, and keep your phone locked.</li>
<li>Turn on transaction alerts so you see every movement.</li>
<li>Never click on links in messages that claim to come from the bank. Open the app yourself.</li>
<li>Log out after use, especially on a shared computer.</li>
</ol>
<h3>Connecting banks and wallets</h3>
<p>In many countries you can move money between your bank account and your mobile wallet and between different banks. Fees and speed vary. Always check the confirmation screen and the receiving name.</p>
<h3>Try it yourself</h3>
<ol>
<li>Visit the official website of one bank in your country and list the account types it offers and the documents needed to open one.</li>
<li>Write the differences between a debit card, a credit card and a prepaid card in your own words.</li>
<li>If you use a banking app, check that transaction alerts are switched on.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Different accounts serve saving, daily use and fixed-term goals.</li>
<li>Debit cards spend your own money, credit cards spend borrowed money.</li>
<li>Card numbers, security codes, PINs and one-time passwords are secrets.</li>
<li>Use official apps, alerts and locked phones for safe banking.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Paying online and by QR code',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to pay for goods online and by QR code while checking that the seller and the payment page are genuine. This matters because online and QR payments are fast, but they are also a favourite target for criminals who copy shops and codes.</p>
<h3>Ways of paying online</h3>
<ul>
<li><strong>Card payment:</strong> you enter the card number, expiry date and security code on a checkout page.</li>
<li><strong>Mobile money payment:</strong> you choose mobile money, enter your number and approve the payment on your phone with your PIN.</li>
<li><strong>Bank transfer or bank app:</strong> you send money from your account to the seller's account.</li>
<li><strong>Payment links and gateways:</strong> a business uses a payment service that shows you a secure page. You pay there and are returned to the shop.</li>
<li><strong>Cash on delivery:</strong> you pay when the goods arrive, which lowers the risk for new sellers but not always for the courier.</li>
</ul>
<h3>Check before you pay</h3>
<ol>
<li><strong>The address bar.</strong> The page address should begin with <code>https://</code> and show a padlock. This means your connection is encrypted. It does NOT prove the seller is honest, because criminals can use https too.</li>
<li><strong>The spelling of the site name.</strong> Fake sites often change one letter.</li>
<li><strong>The seller's history.</strong> Look for real reviews, a physical address, a working phone number and a clear return policy.</li>
<li><strong>The price.</strong> A brand new phone at half price is almost always a trap.</li>
<li><strong>The payment page.</strong> A genuine page asks for payment details only at the final step.</li>
</ol>
<h3>Approving payments</h3>
<p>For card payments many banks send a <strong>one-time password (OTP)</strong> to your phone, or ask for approval in the app. This extra step is called two-factor or 3-D Secure authentication. Never give an OTP to anyone who calls you, even if they say they are from the bank. A real bank never asks for your OTP or full PIN.</p>
<h3>QR codes</h3>
<p>A <strong>QR code</strong> is a square pattern your phone camera reads. In payments it holds the details of the merchant so you do not need to type them. The usual steps are:</p>
<ol>
<li>Open your payment or banking app and choose <strong>Scan to pay</strong>.</li>
<li>Scan the merchant's code.</li>
<li>Check the merchant's name and enter the amount.</li>
<li>Confirm with your PIN or fingerprint.</li>
<li>Keep the receipt message.</li>
</ol>
<p>Be careful: a criminal can paste a fake QR code over the real one at a shop. Check that the sticker has not been stuck on top of another one and that the name shown matches the shop. Do not scan QR codes sent to you by strangers, and do not scan a code to <em>receive</em> money. You never need to scan or approve anything to get paid.</p>
<h3>Safer habits</h3>
<ul>
<li>Do not pay from public Wi-Fi without a protected connection. Use mobile data for important payments.</li>
<li>Use a separate card or wallet with a small balance for online shopping.</li>
<li>Do not save your card details on websites you rarely use.</li>
<li>Keep screenshots and emails of orders and receipts.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Pick any online shop and check it with the five checks above. Write what you found.</li>
<li>Find a QR payment sticker at a local shop, or look at a picture of one, and write how you would check that it is genuine.</li>
<li>Write two sentences telling a friend why an OTP must never be shared.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The padlock shows a private connection, not an honest seller.</li>
<li>Never share an OTP or PIN with anybody.</li>
<li>Check the merchant name before you confirm a QR payment.</li>
<li>Keep records of every online payment.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the first thing to check on the confirmation screen before sending mobile money?
- The colour of the screen
* That the number and the displayed registered name match the person you intend to pay
- Your phone's battery level
- The weather
= Most wrong-number losses happen because the recipient was not checked.

Q: You receive an SMS that says money has arrived and a customer wants change. What is the safest action?
- Give change immediately because the SMS looks real
- Reply to the SMS with your PIN
* Check your balance through the official app or menu before giving anything
- Ask the customer to dial a code for you
= Fake confirmation messages are common. Check the real balance first.

Q: Which of these is NOT something an agent should ever ask of you?
- To show the amount on your phone screen
* To hand over your phone unlocked and tell them your PIN
- To count the cash with you
- To show their licence
= Your PIN is a secret even from agents.

Q: Which card spends only money that is already in your account?
- Credit card
* Debit card
- Store loan card
- Overdraft voucher
= A debit card draws on your own balance. A credit card uses borrowed money.

Q: A website has a padlock and https at the start of its address. What does this prove?
- The seller is honest
- The goods are genuine
* The connection between you and the site is encrypted, but not that the seller can be trusted
- The prices are correct
= Criminals can also obtain https certificates, so you still need to check the seller.

Q: A caller says he is from the bank and asks for the OTP just sent to you, to "cancel a fraud". What do you do?
* Refuse, end the call and contact the bank through its official number
- Give the OTP quickly because it is urgent
- Give half of the OTP
- Ask him to call back tomorrow
= Banks never ask for OTPs. Urgency is a pressure tactic.

Q: When paying a merchant by QR code, what should you confirm before approving? (choose all that apply)
* The merchant name shown on your screen
* The amount
- The colour of the QR sticker
- How many people have scanned it
= The name and amount are the details that matter. A pasted sticker could send your money to a criminal.

Q: Which is a good first step if you sent money to a wrong number?
- Send the same amount again to the right person and forget it
- Pay someone who promises to reverse it
* Note the transaction ID and contact the provider's official channel immediately
- Wait a month
= Speed and the official route give the best chance of help.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 3
        [
            'title'   => 'Module 3: Scams, fraud and how to protect yourself',
            'summary' => 'Recognise the tricks criminals use, protect your secrets and know exactly what to do if you are targeted.',
            'lessons' => [
                [
                    'title'   => 'Common digital money scams',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to recognise the most common digital money scams and the pressure tricks behind them. This matters because fraud is rarely about clever computers. It is mostly about convincing a person to hand over a PIN, a code or money.</p>
<h3>How scammers think</h3>
<p>Scammers use <strong>social engineering</strong>: they manipulate people instead of breaking systems. Almost every scam uses some of these levers: <strong>urgency</strong> ("act in ten minutes"), <strong>fear</strong> ("your account will be closed"), <strong>greed</strong> ("double your money"), <strong>trust</strong> (pretending to be a bank, a relative or an official) and <strong>secrecy</strong> ("do not tell anyone").</p>
<h3>Fake agents and fake customer care</h3>
<p>A caller or message claims to be from the provider's customer care or a helpful agent. They say there is a problem with your wallet, a prize waiting for you or a reversal pending, and ask for your PIN, an OTP or to dial a code. <strong>A genuine provider never asks for your PIN.</strong> Hang up and call the official number yourself.</p>
<h3>The wrong-number "reversal" trick</h3>
<p>You receive a real or fake message saying money was sent to you by mistake. A person calls, begs for it back, and sometimes becomes rude or threatening. In the fake version, no money arrived at all. Always check your own balance first. If money really came to you by mistake, do not send it back yourself. Contact your provider and let it handle the reversal, because scammers also exploit this process.</p>
<h3>SIM swap fraud</h3>
<p>In a <strong>SIM swap</strong>, a criminal convinces the telecom company, or a dishonest insider, to move your phone number to a SIM card they control. Then calls, texts and OTPs go to the criminal, who can reset your wallet or bank access. Warning signs: your phone suddenly loses network for no reason and you get messages about SIM change. If this happens, use another phone to call your provider at once and report it.</p>
<h3>Phishing and smishing</h3>
<p><strong>Phishing</strong> uses emails, social media messages, WhatsApp texts or SMS ("smishing") that contain a link to a fake page. The page looks like the real bank or provider and steals your login details. Signs: a strange sender, spelling mistakes, an urgent threat, a link that does not match the real website, a request to "verify" or "confirm" your details.</p>
<h3>Fake loans and advance-fee fraud</h3>
<p>An advert offers a loan with no checks, very low interest or a "guaranteed approval". You are told to pay an insurance fee, a processing fee or a "security deposit" first. After you pay, the loan never comes, or more fees are demanded. A real lender takes its fee from the loan or states it clearly in a contract and does not ask for money by mobile money to a personal number.</p>
<h3>Investment frauds and pyramid schemes</h3>
<ul>
<li>They promise very high, fast and "guaranteed" returns, such as doubling money in a week.</li>
<li>They pay early members with the money of new members, so they collapse when recruitment slows.</li>
<li>They rely on recruiting friends and family.</li>
<li>They use fake screenshots and testimonials, and fake celebrity pictures.</li>
</ul>
<p>Real investments carry risk, and nobody can promise a guaranteed high return. Before you hand over any money, check that the company is licensed with the relevant financial regulator in your country.</p>
<h3>Other tricks</h3>
<ul>
<li><strong>Romance and friendship scams:</strong> a new online friend suddenly needs money for an emergency.</li>
<li><strong>Job and scholarship scams:</strong> you are asked to pay "registration" before getting work.</li>
<li><strong>Fake giveaways:</strong> a post says share and pay a small amount to receive a prize.</li>
<li><strong>Relative in trouble:</strong> a message from "your cousin" with a new number asks for urgent money.</li>
</ul>
<h3>Red flags checklist</h3>
<table>
<thead><tr><th>Red flag</th><th>What to do</th></tr></thead>
<tbody>
<tr><td>Request for PIN or OTP</td><td>Refuse. No honest provider asks.</td></tr>
<tr><td>Pressure to act immediately</td><td>Stop and take time. Call someone you trust.</td></tr>
<tr><td>Pay a fee to receive money</td><td>Do not pay. This is advance-fee fraud.</td></tr>
<tr><td>Guaranteed high profit</td><td>Walk away.</td></tr>
<tr><td>Contact from an unknown number or profile</td><td>Verify through a channel you already know.</td></tr>
</tbody>
</table>
<h3>Try it yourself</h3>
<ol>
<li>Write down three scam messages you or friends have received, or imagine three using the examples above.</li>
<li>For each, mark the pressure lever used: urgency, fear, greed, trust or secrecy.</li>
<li>Write the safe reply for each: usually to stop, verify and report.</li>
<li>Share one tip with a family member who does not use a smartphone.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Scammers manipulate people with urgency, fear, greed and false trust.</li>
<li>No genuine provider or bank asks for your PIN or OTP.</li>
<li>Never pay a fee to receive a loan, prize or job.</li>
<li>If something sounds too good to be true, verify through official channels.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Protecting your PIN, SIM, phone and accounts',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to set up layers of protection around your money. This matters because one stolen secret can empty an account in minutes, so careful habits are your best insurance.</p>
<h3>Protecting your PIN</h3>
<ul>
<li>Choose a PIN that is <strong>not</strong> your birth year, phone number, repeated digits (1111) or a simple run (1234).</li>
<li>Never write the PIN on the SIM pack, the phone cover or in your contacts.</li>
<li>Never share it with family members, agents, friends or "customer care".</li>
<li>Change it if you suspect anyone has seen it, and from time to time.</li>
<li>Cover the screen when you type it in public.</li>
</ul>
<h3>Protecting your SIM card</h3>
<p>Your SIM is the key to your wallet because messages and OTPs reach you through it. Register the SIM in your own name and keep your ID details private. Do not lend the SIM for "a quick call" to strangers. If the SIM or phone is lost or stolen, <strong>report it immediately</strong> to your provider to block the SIM and the wallet. Some providers also let you set a PIN lock on the SIM itself. Ask your provider about a protection against unauthorised SIM swaps, for example extra verification at the shop.</p>
<h3>Protecting your phone</h3>
<ol>
<li><strong>Use a screen lock</strong> (PIN, pattern, fingerprint or face unlock) and keep the time to auto-lock short.</li>
<li><strong>Keep the system and apps updated.</strong> Updates fix security holes.</li>
<li><strong>Install apps only from the official store</strong> and read permissions. A torch app has no need to read your messages.</li>
<li><strong>Do not install apps from links</strong> sent by chat or SMS.</li>
<li><strong>Turn on Find my device</strong> so you can lock or erase a lost phone.</li>
<li><strong>Back up</strong> important data to your account.</li>
</ol>
<h3>Protecting online accounts</h3>
<p>Use a <strong>different strong password</strong> for each important account, such as email, bank and social media. A password manager helps you remember them. A good password is long, for example a phrase of several unrelated words with numbers added. Turn on <strong>two-factor authentication (2FA)</strong>, which asks for a second proof, such as a code from an authenticator app or a prompt on your phone. App-based codes are usually safer than SMS codes because they cannot be redirected in a SIM swap.</p>
<p>Your <strong>email account</strong> is the master key. Anyone who controls it can reset the passwords for your other accounts, so protect it most carefully.</p>
<h3>Safe use of public places</h3>
<ul>
<li>Avoid doing banking on shared computers in cyber cafes. If you must, log out and clear history.</li>
<li>Be careful on free public Wi-Fi. Do not log in to financial services on it.</li>
<li>Do not let anyone look at your screen while you use a financial app.</li>
</ul>
<h3>Protect your personal data</h3>
<p>Your ID number, date of birth, mother's name and address are used for account recovery. Share them only when needed and with legitimate organisations. Do not post photographs of your ID, cards or tickets online. Many countries have data protection laws that give you rights over how organisations handle your information.</p>
<h3>Check often</h3>
<p>Review your statements and alerts at least weekly. Early detection reduces losses. If you see a payment you do not recognise, report it at once.</p>
<h3>Try it yourself</h3>
<ol>
<li>Change your PIN or screen lock if it is easy to guess.</li>
<li>Switch on 2FA for your main email account.</li>
<li>Check that your phone has Find my device (or the equivalent) enabled.</li>
<li>Write the number of your provider's official customer care on a card in your wallet or notes.</li>
<li>Review the last ten transactions on your wallet.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>PIN, SIM and phone together protect your money. Keep each safe.</li>
<li>Use unique passwords and two-factor authentication, and protect your email most.</li>
<li>Report a lost SIM or phone immediately.</li>
<li>Check your statements and alerts often.</li>
</ul>
<h3>Common mistakes</h3>
<ul>
<li>Choosing a PIN that is easy to guess.</li>
<li>Letting a trusted friend use your wallet "just once".</li>
<li>Waiting a few days to report a lost phone.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'When something goes wrong: reporting and recovery',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to act quickly and correctly if you lose money to fraud or make a payment error. This matters because speed and good records greatly improve your chances of help, and because reporting protects other people too.</p>
<h3>First, stay calm and stop the damage</h3>
<ol>
<li><strong>Stop sending money.</strong> Scammers often ask for more, claiming the first payment failed.</li>
<li><strong>Secure your accounts.</strong> If you gave out a PIN, password or OTP, change it at once using the official app or menu, or visit the provider's service centre.</li>
<li><strong>Call your provider</strong> from another phone if your SIM stopped working. Ask to freeze the wallet or block the card.</li>
<li><strong>Write everything down while you remember it:</strong> the time, the amounts, the numbers, names used and what was said.</li>
</ol>
<h3>Collect evidence</h3>
<ul>
<li>Transaction IDs and confirmation messages.</li>
<li>Screenshots of chats, profiles, adverts and websites. Save the website address.</li>
<li>Call logs with times and numbers.</li>
<li>Your mobile money or bank statement for the period.</li>
</ul>
<p>Do not delete messages, even if they embarrass you. They are evidence.</p>
<h3>Who to report to</h3>
<table>
<thead><tr><th>Who</th><th>Why</th></tr></thead>
<tbody>
<tr><td>Your financial provider (mobile money company, bank, card issuer)</td><td>They can freeze accounts, investigate and sometimes reverse or recover funds</td></tr>
<tr><td>Your telecom operator</td><td>For SIM swap or SIM misuse</td></tr>
<tr><td>The police or cybercrime unit</td><td>To open a criminal case and get a reference number</td></tr>
<tr><td>The financial or telecom regulator</td><td>Many regulators accept consumer complaints, especially when the provider does not respond</td></tr>
<tr><td>The platform where the scam happened</td><td>Social media and marketplaces can remove fake accounts</td></tr>
</tbody>
</table>
<p>Every country has its own offices and complaint steps. Look up the central bank, the communications regulator and the police cybercrime contacts from their official websites. The Resources list for this course shows several starting points.</p>
<h3>Making a good report</h3>
<p>State clearly: who you are, what happened, when, how much was lost, which numbers or accounts were involved and what you have done so far. Keep the report short and factual. Ask for a <strong>reference number</strong> and the name of the person you spoke to. Follow up politely if you hear nothing.</p>
<h3>Do not be fooled twice</h3>
<p>After a loss, "recovery agents" appear. They claim they can get your money back for a fee, often by phone or social media. They are usually the same criminals or copycats. No legitimate organisation charges an upfront fee to recover stolen money by chat.</p>
<h3>Be realistic and learn</h3>
<p>Recovery is not always possible, especially when you voluntarily gave a PIN or sent cash. Do not feel ashamed. Many intelligent people have been tricked. Telling friends and family what happened protects them, and learning which lever the scammer used will help you spot the next attempt.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a note on your phone named "If I am scammed" with the official numbers of your wallet provider, bank and telecom operator.</li>
<li>Add the steps you will follow in the first hour.</li>
<li>Look up the website of your central bank and find its page for consumer complaints, if there is one.</li>
<li>Practise writing a three-line fraud report from an imaginary case.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Stop payments, secure accounts and call the provider quickly.</li>
<li>Collect evidence and never delete messages.</li>
<li>Report to the provider, the operator, the police and the regulator where appropriate.</li>
<li>Beware of "recovery" helpers who ask for fees.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which is the clearest sign that a call is a scam?
- The caller speaks politely
- The caller knows your name
* The caller asks for your PIN or an OTP
- The call comes in the afternoon
= No genuine provider or bank asks for PINs or OTPs. Names are easy to find.

Q: What is a SIM swap fraud?
- Buying two SIM cards
* A criminal gets your number moved to a SIM they control so they receive your calls and OTPs
- Changing your network voluntarily
- Swapping your phone for a better one
= After a SIM swap the criminal receives your OTPs and can reset your wallet or bank access.

Q: You are offered a loan but must first pay a "processing fee" by mobile money to a personal number. What is this most likely?
- A normal procedure
* Advance-fee fraud
- A government rule
- A discount
= Honest lenders do not ask for fees to be sent to personal numbers before disbursement.

Q: Which feature is typical of an investment fraud?
- Clear documents and a licence you can verify with the regulator
- Honest warnings about risk
* Guaranteed very high returns and pressure to recruit friends
- A published address and contact details
= Guaranteed high returns and recruitment are classic signs of a pyramid scheme.

Q: Which of these is the best PIN practice?
- Use your birth year so you remember it
- Write the PIN on the back of your SIM pack
* Choose a PIN that is hard to guess and never share it
- Tell a trusted agent in case you forget
= A PIN works only while it is secret and unpredictable.

Q: Which kind of second factor is usually safer against SIM swap?
- A code sent by SMS
* A code generated by an authenticator app on your phone
- Your mother's maiden name
- A shorter password
= App-based codes do not travel through your phone number, so a swapped SIM cannot intercept them.

Q: You think you have been defrauded. What should you do first? (choose all that apply)
* Stop sending money and secure your accounts
* Contact the provider through its official channel and keep the evidence
- Delete all messages so nobody sees them
- Pay a recovery agent who promises refunds
= Quick action and evidence help. Recovery agents asking for fees are another scam.

Q: Someone claims he sent you money by mistake and begs you to send it back, but you have not checked your balance. What should you do?
- Send it back immediately
* Check your balance through the official channel, and if the money really arrived, ask your provider to handle any reversal
- Send half
- Give him your PIN to fix it
= Verify first. The fake reversal story is a common trick.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 4
        [
            'title'   => 'Module 4: Credit, insurance, pensions and your rights',
            'summary' => 'Borrow wisely, understand protection products and know how to claim your rights as a customer.',
            'lessons' => [
                [
                    'title'   => 'Digital loans and credit awareness',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to read a digital loan offer, work out what it will really cost and decide whether you can repay it. This matters because small instant loans are easy to get and easy to misuse, and missed repayments can damage your future access to credit.</p>
<h3>What is a digital loan?</h3>
<p>A <strong>digital loan</strong> is credit that you request and receive through an app, a USSD menu or a mobile wallet. Some are offered by banks and microfinance institutions, some by regulated digital lenders and some by informal or unlicensed lenders. They often decide quickly using your mobile money history, phone data or a short form.</p>
<h3>The questions to ask before accepting</h3>
<ol>
<li><strong>Is the lender licensed?</strong> Check the list of licensed institutions on the website of your central bank or the relevant regulator. Rules differ by country.</li>
<li><strong>How much will I actually receive?</strong> Some loans deduct fees at the start, so you may receive less than the amount you applied for.</li>
<li><strong>What is the total I will repay, and when?</strong> Look for the full cost in shillings.</li>
<li><strong>How long is the loan?</strong> Many mobile loans are for days or weeks, so a small fee can mean a very high yearly rate.</li>
<li><strong>What happens if I am late?</strong> Ask about penalties, extra interest and rollovers.</li>
<li><strong>What data does the app want?</strong> Contacts, photos and messages should not be needed for a loan.</li>
</ol>
<h3>Work out the real cost</h3>
<pre><code>You borrow 100,000 for 30 days.
Fee and interest: 10,000
Total to repay after 30 days: 110,000

Cost for 30 days: 10,000 / 100,000 = 10%
If you could roll this loan over 12 times a year,
the cost would be about 12 x 10% = 120% a year (before extra fees).</code></pre>
<p>A short loan can look cheap but be very costly if repeated again and again. Compare lenders by the total cost for the same amount and period.</p>
<h3>Good and bad reasons to borrow</h3>
<table>
<thead><tr><th>Usually sensible</th><th>Usually risky</th></tr></thead>
<tbody>
<tr><td>Stock that you will sell quickly at a profit</td><td>Paying a normal bill that you cannot afford every month</td></tr>
<tr><td>School fees to protect a learner's place, with a clear repayment plan</td><td>Borrowing to repay another loan</td></tr>
<tr><td>A tool that raises your income</td><td>Betting or gambling</td></tr>
<tr><td>A real emergency when savings are not enough</td><td>Impressing other people</td></tr>
</tbody>
</table>
<h3>Credit scores and reference bureaus</h3>
<p>In many countries, lenders report how you repay to <strong>credit reference bureaus</strong>. Good payment history can help you qualify for bigger loans at better terms. Defaults and late payments can lead to being <strong>listed</strong> negatively, which can make it hard to borrow later. You usually have a right to request your own credit report and to correct mistakes. Ask the bureau or your regulator how this works in your country.</p>
<h3>Recognising predatory lending</h3>
<ul>
<li>The lender is not licensed or hides its address.</li>
<li>The terms are not shown clearly before you accept.</li>
<li>The app demands access to your contacts and threatens to message them if you are late.</li>
<li>The lender uses threats, insults or shame to collect.</li>
</ul>
<p>Harassment and public shaming are not acceptable collection methods in most places. Keep the evidence and report to the regulator.</p>
<h3>Borrowing wisely</h3>
<ol>
<li>Borrow only what you need.</li>
<li>Check that your monthly repayment fits in your budget.</li>
<li>Repay on time, or earlier if there is no penalty.</li>
<li>Ask for help before you default. Many lenders will agree a new plan.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Imagine a loan of UGX 150,000 for 14 days with total repayment of UGX 165,000. Calculate the percentage cost for the period.</li>
<li>Estimate the cost if you took it 26 times a year.</li>
<li>Write three questions you would ask the lender before agreeing.</li>
<li>Look up how to confirm if a lender is licensed in your country.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Always find the total repayment in shillings and the due date.</li>
<li>Short loans can be extremely expensive when repeated.</li>
<li>Use only licensed lenders and refuse apps that demand your contacts.</li>
<li>Your repayment record can open or close future opportunities.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Insurance, pensions and long-term protection',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what insurance and pensions are for and how to read the basic terms of a product. This matters because one accident, one illness or one failed harvest can wipe out years of savings, and because old age comes to everyone.</p>
<h3>What is insurance?</h3>
<p><strong>Insurance</strong> is a way of sharing risk. Many people pay small regular amounts called <strong>premiums</strong> into a pool. When someone in the pool suffers a covered loss, the insurer pays a <strong>claim</strong> from the pool. You exchange a small certain cost for protection against a large uncertain one.</p>
<h3>Key words</h3>
<table>
<thead><tr><th>Word</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>Premium</td><td>The price you pay for cover, monthly, yearly or in one payment</td></tr>
<tr><td>Sum insured or cover limit</td><td>The most the insurer will pay</td></tr>
<tr><td>Excess or deductible</td><td>The part of a loss that you pay yourself</td></tr>
<tr><td>Exclusion</td><td>Something that is not covered</td></tr>
<tr><td>Waiting period</td><td>Time after you buy during which you cannot claim</td></tr>
<tr><td>Beneficiary</td><td>The person who receives a payout, for example in life cover</td></tr>
<tr><td>Claim</td><td>Your request for payment after a loss</td></tr>
</tbody>
</table>
<h3>Common kinds of cover</h3>
<ul>
<li><strong>Health insurance:</strong> helps pay for treatment and hospital bills.</li>
<li><strong>Life and funeral cover:</strong> pays your family if you die.</li>
<li><strong>Motor and motorcycle insurance:</strong> covers accidents, theft or damage to others, and may be required by law.</li>
<li><strong>Property and business insurance:</strong> covers fire, theft or damage to a shop and stock.</li>
<li><strong>Crop and livestock insurance:</strong> helps farmers after drought, floods or disease. It is often sold in small units.</li>
<li><strong>Microinsurance:</strong> low-cost, simple cover designed for people with low incomes, often paid through mobile money.</li>
</ul>
<p>Availability and rules differ by country. In many places insurance is regulated by a separate insurance authority.</p>
<h3>Buying insurance wisely</h3>
<ol>
<li>Buy only from a company or agent that is licensed by the insurance regulator.</li>
<li>Ask what is covered, what is excluded, how much you pay and how to claim.</li>
<li>Read the policy document or summary before you pay.</li>
<li>Keep the policy number and the contact for claims.</li>
<li>Ask what proof you will need: receipts, a police report, a medical report.</li>
<li>Do not buy several covers that overlap without reason.</li>
</ol>
<p>Be careful with offers that arrive as SMS or social media messages promising huge payouts for tiny premiums. Verify first.</p>
<h3>What is a pension?</h3>
<p>A <strong>pension</strong> is a long-term savings arrangement that pays you an income when you stop working because of age or disability. It may come from a compulsory national social security scheme for formal workers, from an employer's scheme, from a personal retirement savings plan or from voluntary schemes designed for informal workers. In some countries, mobile money-linked pension and savings schemes aim to reach informal workers such as market traders and boda boda riders.</p>
<h3>Why start early?</h3>
<p>Because of compound interest, a small amount saved from age 20 can grow far more than a larger amount started at 40. Check that the scheme is licensed, that you know who manages the money, the fees and how to see your balance. Ask your regulator or employer for the rules of the scheme you are in, as they differ between countries.</p>
<h3>Try it yourself</h3>
<ol>
<li>List three risks that could seriously harm your family's finances.</li>
<li>Write which of the covers above would protect against each.</li>
<li>Find out one insurance or pension scheme for which you might qualify and write what you would ask them.</li>
<li>Note the website of the regulator where you can check if the provider is licensed.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Insurance shares risk: many people pay premiums so that those with a loss are paid.</li>
<li>Read exclusions, limits and the claim steps before you buy.</li>
<li>Use only licensed insurers, agents and pension schemes.</li>
<li>Starting a pension early benefits from compound growth.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Consumer rights, data privacy and complaints',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe your basic rights as a financial customer and make a complaint that is taken seriously. This matters because many people accept bad treatment or hidden charges simply because they do not know they can question them.</p>
<h3>Your basic rights</h3>
<p>The details are set by laws and regulators in each country, but good financial systems share these principles:</p>
<ul>
<li><strong>The right to clear information.</strong> Fees, interest, limits and conditions must be explained in plain language before you agree.</li>
<li><strong>The right to fair treatment.</strong> No discrimination, no pressure and no abusive collection.</li>
<li><strong>The right to privacy.</strong> Your data should only be collected and used for lawful, stated purposes.</li>
<li><strong>The right to receipts and records.</strong> You can get confirmation and statements of your transactions.</li>
<li><strong>The right to complain</strong> and to have your complaint handled within a reasonable time.</li>
<li><strong>The right to financial education</strong> and to safe, reliable services.</li>
</ul>
<h3>Responsibilities too</h3>
<p>Rights come with duties: read the terms, keep your PIN safe, give correct information, repay what you borrow and report problems early.</p>
<h3>Reading terms and conditions</h3>
<p>Nobody enjoys long terms, but before you accept anything that involves money, scan for these items: fees, interest and penalties, what happens if you miss a payment, how to cancel, what data is shared and with whom, how to complain. If the terms are not available in a language you understand, ask for an explanation.</p>
<h3>Data privacy</h3>
<p>Financial apps collect information such as your name, ID number, location, contacts and transaction history. Before you grant access, ask: <strong>Is this needed for the service?</strong> Deny permissions that do not make sense. Many countries have data protection laws and an authority you can complain to if your data is misused or leaked.</p>
<h3>The complaint ladder</h3>
<ol>
<li><strong>Contact the provider first</strong> through its official customer service channel. Explain clearly and give the transaction ID, the date, the amount and what you want done.</li>
<li><strong>Write it down.</strong> Use email or a written form if possible. Ask for a reference number.</li>
<li><strong>Give a reasonable time</strong> as stated by the provider, often a few days or weeks.</li>
<li><strong>Escalate</strong> to the provider's complaints manager if you have not been helped.</li>
<li><strong>Go to the regulator or ombudsman.</strong> The central bank, the insurance regulator, the communications regulator or a consumer protection body may accept your complaint. Some countries also have a financial ombudsman or tribunal.</li>
<li><strong>Seek legal advice or support</strong> from a consumer organisation or a legal aid service for serious losses.</li>
</ol>
<h3>A sample complaint</h3>
<pre><code>Subject: Complaint about a wrong deduction, transaction 12345

On 14 March I paid a bill of UGX 50,000 but UGX 55,000 was deducted.
The fee shown on the screen was UGX 500.
Please explain the difference and refund UGX 4,500.
Reference: transaction ID 12345. My number: (your number).
I attach my statement.</code></pre>
<h3>Being a smart consumer</h3>
<ul>
<li>Compare two or three providers before choosing.</li>
<li>Do not sign anything you do not understand.</li>
<li>Keep your documents in one folder, paper or digital.</li>
<li>Teach friends and family these rights.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Read the terms of one financial app you use. Find the fees, the data it uses and the complaint process.</li>
<li>Write a short complaint about an imaginary overcharge using the sample.</li>
<li>Find the website of the regulator in your country that handles complaints about financial services.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>You have rights to clear information, fair treatment, privacy and a complaints process.</li>
<li>Read key terms and deny permissions that are not needed.</li>
<li>Complain in writing, with evidence and a reference number.</li>
<li>Escalate to regulators if the provider fails to help.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A lender offers UGX 200,000 for 30 days and asks for UGX 230,000 back. What is the cost for the 30 days?
- 5 percent
- 30 percent of the whole year
* 15 percent
- 23 percent
= The extra amount is 30,000, which is 15 percent of 200,000.

Q: Which is a warning sign of a predatory loan app?
- It shows total repayment clearly
* It demands access to your contacts and threatens to message them if you are late
- It displays a licence you can verify
- It lets you read the terms before accepting
= Harassing borrowers and their contacts is unacceptable and often illegal.

Q: What is a premium?
- The money paid to you after a loss
* The amount you pay for insurance cover
- The penalty for a late claim
- A gift from the insurer
= A premium is the price of the cover. A payout after a loss is a claim.

Q: In insurance, what is an exclusion?
- A discount for new customers
- A special bonus
* Something the policy does not cover
- A late fee
= Read exclusions to avoid surprises when you claim.

Q: Why is it wise to start pension saving early?
- Because pensions never carry fees
- Because the government pays everyone the same pension anyway
* Because compound growth has more years to work
- Because early savers never need to check the scheme
= Time allows compound growth, but you must still check fees and whether the scheme is genuine.

Q: Which is the best first step when you have a complaint about an overcharge?
- Post angry comments with your PIN
* Contact the provider's official channel with the transaction ID and ask for a reference number
- Ignore it
- Share your PIN with a "helper"
= Provide clear evidence and keep a record of the complaint.

Q: Which of these is a consumer responsibility? (choose all that apply)
* Keeping your PIN secret
* Reading the main terms before agreeing
- Allowing anyone to use your wallet
- Hiding your fraud losses from the provider
= Rights come with responsibilities. Quick reporting helps everybody.

Q: A message says you have won a prize if you first pay a small fee to a personal number. What is the best response?
* Ignore and report it as a probable scam
- Pay quickly before the offer expires
- Give your ID number as proof
- Share it with ten friends
= You never need to pay to receive a genuine prize.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 5
        [
            'title'   => 'Module 5: Digital money for a small business',
            'summary' => 'Keep clear records, accept digital payments safely and put a personal safety plan together.',
            'lessons' => [
                [
                    'title'   => 'Records, receipts and separating business from personal money',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to keep simple business records and separate your own money from the money of the business. This matters because many small businesses close not because they lack customers but because the owner cannot tell whether they are making a profit.</p>
<h3>Why records matter</h3>
<p>Good records show you whether you are making money, which products sell best, who owes you and what you owe others. They also help you apply for a loan, prepare tax returns where required and settle disputes about payments.</p>
<h3>Separate the money</h3>
<p>Use a <strong>separate wallet, SIM or bank account for the business</strong>. If this is not possible, at least record business and personal transactions differently. When the owner takes money for personal use, record it as <strong>owner's withdrawal</strong> or "salary", not as an unexplained gap. Mixing the two hides losses and creates conflict in families and partnerships.</p>
<h3>The simple cash book</h3>
<p>A <strong>cash book</strong> records every payment in and out in date order. You can keep it in an exercise book or a spreadsheet with these columns:</p>
<table>
<thead><tr><th>Date</th><th>Description</th><th>Cash or wallet</th><th>Money in</th><th>Money out</th><th>Balance</th></tr></thead>
<tbody>
<tr><td>2</td><td>Sales of the day</td><td>Cash</td><td>85,000</td><td></td><td>85,000</td></tr>
<tr><td>2</td><td>Bought flour and sugar</td><td>Wallet</td><td></td><td>40,000</td><td>45,000</td></tr>
<tr><td>3</td><td>Sales of the day</td><td>Wallet</td><td>60,000</td><td></td><td>105,000</td></tr>
<tr><td>3</td><td>Paid transport</td><td>Cash</td><td></td><td>5,000</td><td>100,000</td></tr>
</tbody>
</table>
<h3>Income, costs and profit</h3>
<pre><code>Profit = Sales - Costs
Example: Sales 1,200,000 - Costs 900,000 = Profit 300,000
Profit margin = Profit / Sales = 300,000 / 1,200,000 = 25%</code></pre>
<p>Costs include goods bought, rent, transport, electricity, wages, airtime and also transaction fees. Remember to include the fees you pay for mobile money and bank services as a cost, since they reduce your profit.</p>
<h3>Receipts and statements</h3>
<ul>
<li><strong>Give receipts</strong> to customers. A simple receipt book with the date, item, amount and your business name is fine.</li>
<li><strong>Keep the receipts</strong> for what you buy and attach them in order.</li>
<li><strong>Download or print your wallet and bank statements</strong> every month. They are strong evidence of payments.</li>
<li><strong>Back up</strong> digital records, for example on an email account or a cloud drive.</li>
</ul>
<h3>Reconcile once a week</h3>
<p><strong>Reconciling</strong> means comparing your records with the real balances. Count the cash. Check the wallet and bank balances. Compare them with the cash book. If there is a difference, find it now, while you remember. Small errors repeated for months become large losses.</p>
<h3>Credit sales</h3>
<p>If you let people take goods now and pay later, record each debt: name, date, amount, due date. Review the list every week and follow up politely. Limit how much credit you give and avoid giving more to people who have not paid.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a cash book in an exercise book or a spreadsheet using the columns above.</li>
<li>Record the last five business or personal transactions as practice.</li>
<li>Calculate the profit and margin for an imaginary week with your own numbers.</li>
<li>Decide how you will separate business and personal money from next week.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Records show whether a business makes a profit.</li>
<li>Keep business and personal money apart and record owner withdrawals.</li>
<li>Profit equals sales minus costs, including fees.</li>
<li>Reconcile cash, wallet and bank balances regularly.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Accepting digital payments in your business',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to set up and run a safe way of accepting mobile money, QR and card payments in a small shop or online business. This matters because customers increasingly carry little cash and a business that cannot accept digital payments can lose sales.</p>
<h3>Ways to accept payments</h3>
<ul>
<li><strong>A merchant account or till number</strong> from a mobile money provider, which separates business sales from personal transfers.</li>
<li><strong>A personal wallet number.</strong> This is easy, but it mixes business and personal money and may not suit large volumes. Rules can differ for personal accounts used for business.</li>
<li><strong>A QR code</strong> linked to your merchant account for customers to scan.</li>
<li><strong>A card machine (POS)</strong> from a bank or payment service for card payments.</li>
<li><strong>Online payment links or checkout</strong> through a payment gateway for web and social media sales.</li>
<li><strong>Bank transfer</strong> for invoices from larger customers.</li>
</ul>
<p>Requirements, fees and limits differ by country and provider. To register as a merchant you usually need an ID, business registration or a trading licence, and a registered line. Ask a few providers and compare the terms in writing.</p>
<h3>Add fees to your pricing</h3>
<p>Some payments cost you a small fee. Include these costs when you calculate your margin. Do not hide them in a way that breaks the rules, and do not charge customers extra unless the law and the provider's rules allow it. Where a surcharge is allowed, display it clearly before payment.</p>
<h3>Confirm before you release goods</h3>
<ol>
<li>Ask the customer to pay, and show the amount.</li>
<li>Wait for the payment to arrive. <strong>Check the balance or the official transaction list in your app or menu.</strong> Do not rely only on an SMS shown by the customer.</li>
<li>Match the amount, the name or number and the time.</li>
<li>Only then release the goods or services.</li>
<li>Record the sale in your cash book with the transaction ID.</li>
</ol>
<h3>Typical tricks to watch for</h3>
<table>
<thead><tr><th>Trick</th><th>Protection</th></tr></thead>
<tbody>
<tr><td>Fake confirmation SMS or edited screenshot</td><td>Check your own app or balance</td></tr>
<tr><td>Customer pays a smaller amount "by mistake" then asks for a refund of the extra</td><td>Check the amount received first and refund through the provider</td></tr>
<tr><td>Pasted fake QR sticker over your code</td><td>Check your code often and protect it behind glass</td></tr>
<tr><td>Chargeback or reversal claims after goods are handed over</td><td>Keep order records, proof of delivery and receipts</td></tr>
<tr><td>Fake supplier invoices with changed bank details</td><td>Confirm any change of payment details by phone using a known number</td></tr>
</tbody>
</table>
<h3>Protect your business account</h3>
<ul>
<li>Allow only one or two trusted people to approve payments from the business account.</li>
<li>Do not share the merchant PIN with every employee. Use separate access or limits if the provider allows.</li>
<li>Withdraw and bank cash regularly instead of leaving large balances in a wallet.</li>
<li>Check the daily transaction report at closing time.</li>
</ul>
<h3>Online sales</h3>
<p>If you sell on social media or your own website, state prices clearly, describe delivery and returns, give a contact, and use a secure payment option. Keep copies of chats and orders. Do not ask customers to pay into a personal number if the shop claims to be a company, because that harms trust.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write the steps your business or an imaginary shop will follow to accept payment safely.</li>
<li>Contact or look up two providers and list their requirements for a merchant account.</li>
<li>Write a short "How to pay us" notice for a counter, with the till number and the steps.</li>
<li>List three checks a cashier must do before handing over goods.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A merchant or till account separates business sales from personal money.</li>
<li>Always confirm the payment in your own app before releasing goods.</li>
<li>Include transaction fees when pricing and reporting profit.</li>
<li>Limit who can approve payments and review reports daily.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'A worked example: one month in a small shop',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to follow a whole month of money in a small business, from budget to cash book to profit and a safe digital payment routine. This matters because you can see how all the earlier ideas fit together in a single, realistic picture.</p>
<h3>The business</h3>
<p>Amina runs a small shop selling school supplies and snacks near a secondary school. She sells for cash and for mobile money. She is 24, lives with her family and wants to save for a second fridge. The figures below are invented to practise with. Real numbers will differ.</p>
<h3>Step 1: Plan the month</h3>
<pre><code>Expected sales                        1,500,000
Cost of goods (stock to buy)            950,000
Rent                                    120,000
Transport                                40,000
Electricity                              30,000
Airtime and data                         15,000
Mobile money and bank fees               20,000
Total business costs                  1,175,000

Expected profit                         325,000</code></pre>
<h3>Step 2: Pay herself</h3>
<p>Amina decides to take a fixed owner's salary of UGX 200,000 and keep UGX 125,000 in the business for growth and a fridge fund. She transfers the salary from the business wallet to her personal wallet on the 28th. Every transfer goes into the cash book as "owner's withdrawal".</p>
<h3>Step 3: The weekly routine</h3>
<ol>
<li>At closing time, count the cash drawer and note the amount.</li>
<li>Print or screenshot the day's merchant transactions from the official app.</li>
<li>Write both totals into the cash book and check them against sales records.</li>
<li>Put the cash in a safe place and bank or deposit a part of it.</li>
<li>On Sunday, reconcile and check the credit book for unpaid items.</li>
</ol>
<h3>Step 4: Results after four weeks</h3>
<table>
<thead><tr><th>Item</th><th>Planned</th><th>Actual</th></tr></thead>
<tbody>
<tr><td>Sales</td><td>1,500,000</td><td>1,420,000</td></tr>
<tr><td>Cost of goods</td><td>950,000</td><td>930,000</td></tr>
<tr><td>Other costs</td><td>225,000</td><td>245,000</td></tr>
<tr><td>Profit</td><td>325,000</td><td>245,000</td></tr>
</tbody>
</table>
<p>Compute the actual profit: 1,420,000 minus 930,000 minus 245,000 equals 245,000. The margin is 245,000 divided by 1,420,000, about 17 percent. Amina sees sales were lower and other costs were higher, mainly because a refrigerator repair appeared and fees grew with more mobile payments.</p>
<h3>Step 5: Decisions</h3>
<ul>
<li>She still pays herself UGX 200,000 and saves the rest, UGX 45,000.</li>
<li>She decides to review prices because fees are increasing.</li>
<li>She stops giving credit to a customer who has not paid in two months.</li>
<li>She puts her QR sticker behind clear plastic and checks it each morning.</li>
</ul>
<h3>Where the money safety rules appear</h3>
<p>During the month, a customer showed her a message "payment received" for UGX 30,000, but her own app did not show it. She refused to hand over the goods until the money arrived. The customer left. A fake message could have cost her the stock and the sale. This is why every confirmation comes from her own app, not from the customer's phone.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose a small business, real or imaginary, and write a monthly plan like the one above.</li>
<li>Choose an owner's salary and list what is left for the business.</li>
<li>Invent actual figures for the month and calculate the actual profit and margin.</li>
<li>Write three decisions you would make from the results.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Plan, record and compare the plan with actual results.</li>
<li>Pay yourself a fixed amount and leave the rest in the business.</li>
<li>Fees are real costs and should be tracked.</li>
<li>Confirm every payment in your own app before releasing goods.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Your personal digital money safety plan',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a one-page plan that brings together everything in this course. This matters because knowledge helps only if you turn it into habits that you actually follow when you are busy or under pressure.</p>
<h3>Why write a plan?</h3>
<p>When a scam call arrives, fear makes it hard to think. A written plan removes the need to decide in a hurry. You simply follow steps you prepared while calm. Companies and families use the same method for fire safety and first aid.</p>
<h3>The five parts of the plan</h3>
<ol>
<li><strong>Budget and savings.</strong> Your monthly budget, your emergency fund target and your main saving goal.</li>
<li><strong>Accounts and limits.</strong> A list of the wallets, bank accounts and cards you have, and the limits you want to set.</li>
<li><strong>Protection.</strong> Your PIN rules, screen lock, two-factor authentication and backup.</li>
<li><strong>Debt and protection products.</strong> Your loans, if any, with due dates, and the insurance or pension cover you have or want.</li>
<li><strong>If something goes wrong.</strong> The official numbers to call, the evidence to keep and the order of steps.</li>
</ol>
<h3>A model plan</h3>
<pre><code>1. BUDGET
   Income: ______  Savings per month: ______
   Emergency fund target: ______  Goal: ______ by ______

2. ACCOUNTS
   Wallet (provider): ______  Bank: ______  Card: ______

3. PROTECTION
   [ ] PIN not guessable  [ ] Screen lock on
   [ ] 2FA on email       [ ] Phone tracking on
   [ ] Alerts on          [ ] Backup done

4. DEBT AND COVER
   Loans: lender / amount / due date
   Insurance or pension: ______

5. IF SOMETHING GOES WRONG
   Provider hotline: ______  Telecom: ______
   Police: ______  Regulator: ______
   Steps: stop, secure, call, keep evidence, report</code></pre>
<h3>Family and community</h3>
<p>Scammers often target parents, grandparents and young people. Share your plan with someone you trust. Agree a family <strong>code word</strong> for emergencies, so that a message "from a relative" asking for urgent money can be checked. Teach older relatives that no bank asks for a PIN. Encourage friends to check their balance before giving change.</p>
<h3>Review every three months</h3>
<p>Fees, products and rules change. Every three months, check that your contact numbers are correct, your budget still fits and your savings are growing. After any scam attempt, review what you could improve.</p>
<h3>Keep learning</h3>
<p>Follow your central bank, your communications regulator and your own provider's official pages for warnings. Trusted community radio, schools and youth groups also teach about fraud. Do not rely on a single source, and be careful about financial advice you find on social media. This course gives general education, and for decisions about your own situation you should speak with a licensed professional or a registered advice service.</p>
<h3>Try it yourself</h3>
<ol>
<li>Copy the model plan into a notebook or a note on your phone.</li>
<li>Fill in every blank honestly. Leave nothing empty without a reason.</li>
<li>Tick the protection boxes that are done. Do the rest today.</li>
<li>Show it to one family member and agree your code word.</li>
<li>Put a reminder in your phone calendar to review the plan in three months.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A written plan lets you act calmly under pressure.</li>
<li>Cover budget, accounts, protection, debt and cover, and what to do when something goes wrong.</li>
<li>Share the plan with people close to you.</li>
<li>Review it regularly because rules and risks change.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Why should a small business keep its money separate from the owner's personal money?
- It looks more professional only
* It makes the true profit visible and avoids hidden losses and conflict
- It is always required by law everywhere
- It reduces the number of customers
= Mixing money hides whether the business really earns a profit.

Q: A shop had sales of 800,000 and total costs of 640,000. What is the profit margin?
- 16 percent
* 20 percent
- 25 percent
- 80 percent
= Profit is 160,000. Dividing by sales 800,000 gives 0.20, or 20 percent.

Q: What does it mean to reconcile your records?
- Throw away old receipts
- Increase prices at month end
- Copy last month's figures into this month's book
* Compare the records with the real cash, wallet and bank balances and explain any difference
= To reconcile is to compare your books with real balances and find the reason for differences.

Q: Before releasing goods for a mobile money payment you should:
- Rely on the SMS shown on the customer's phone
- Wait a week
* Confirm the payment in your own app or official transaction list
- Ask the customer for the PIN
= Fake confirmation messages are common.

Q: Which is a sensible way to treat transaction fees in your business records?
- Ignore them
* Record them as a cost of the business
- Treat them as profit
- Charge them to the customer in secret
= Fees reduce profit and must be tracked.

Q: A supplier sends an email asking you to pay to a new bank account. What should you do?
- Pay immediately to avoid delays
- Reply with your PIN
* Confirm the change by calling the supplier on a number you already know
- Forward it to all employees
= Fake invoices with changed details are common business scams.

Q: Which are part of a personal digital money safety plan? (choose all that apply)
* A budget and savings goal
* The official numbers to call if you are scammed
- Your PIN written next to the numbers
- A promise to never check statements
= The plan holds contacts and steps, never your secrets.

Q: How often should a family agree to review their safety plan?
- Never
* Regularly, for example every three months
- Only after losing money
- Every ten years
= Rules, products and scams change, so reviews keep the plan useful.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: What is the main purpose of a monthly budget?
- To limit how much you may earn
* To plan how income will cover spending and saving
- To impress lenders
- To avoid keeping records
= A budget matches your spending to your income and your goals.

Q: A yearly cost of UGX 1,200,000 is best handled by:
- Saving nothing until it is due
* Setting aside UGX 100,000 each month
- Borrowing from several apps
- Hoping for help
= Dividing an irregular cost by twelve turns a shock into a small monthly habit.

Q: Where is it generally safest to keep larger long-term savings?
- In a drawer at home
- In a friend's wallet
* With a licensed and supervised institution
- In an informal scheme with no records
= Regulated institutions offer better protection and records.

Q: UGX 500,000 at 8 percent simple interest for 2 years. What is the interest?
- 40,000
* 80,000
- 8,000
- 160,000
= 500,000 x 0.08 x 2 = 80,000.

Q: What is the difference between simple and compound interest?
- None
- Compound interest is calculated only on the original amount
* Compound interest is calculated on the amount plus earlier interest
- Simple interest is always higher
= With compound interest, interest earns interest.

Q: Why must you always ask for the period of a loan rate?
- Because rates are unimportant
* Because 5 percent per week is far more costly than 5 percent per year
- Because the period decides the lender's name
- Because only banks have periods
= Only rates with the same period can be compared.

Q: Which of these is an e-money wallet balance?
- A promise that has no backing
* A record of money the provider owes you, which you can cash out at an agent
- A gift voucher
- A tax refund
= Wallet funds are electronic money linked to your registered identity.

Q: Before confirming a send-money transaction you should check:
- The sender's colour theme
* The number and the registered name that is displayed
- Your battery level
- How many contacts you have
= Wrong recipients cause most avoidable losses.

Q: What should you keep after every transaction?
- Nothing
- Your PIN message
* The confirmation message with the transaction ID
- Only the agent's phone number
= The ID is the reference needed if there is a dispute.

Q: Which statements about fees and limits are correct? (choose all that apply)
* They differ between providers and countries
* They can change over time
- They are the same everywhere forever
- They are never published
= Always check your own provider's current terms.

Q: A card has a number, expiry date and security code. Which is the most dangerous combination to share on the phone?
- Your first name
* The card number, the security code and a one-time password
- Your bank's name
- The day you opened the account
= Together these allow payments in your name.

Q: What does the padlock and https in an address prove?
- The shop is honest
* The connection is encrypted
- The prices are low
- The goods will arrive
= It protects data on its way, not the seller's honesty.

Q: Which is the safest way to pay a stranger for a high-value item online?
- Send money first to a personal number
- Pay by a link in a social media chat from a new profile
* Use a protected route with buyer protection or cash on delivery after checking the goods
- Give your PIN so the seller can "process" it
= Reduce your risk by paying only when you can verify the goods.

Q: What do scammers use to make you act without thinking?
- Patience
* Urgency, fear, greed and false trust
- Receipts
- Polite refusal
= These pressure levers appear in almost every scam.

Q: Which signal could indicate a SIM swap?
- A slow phone battery
* A sudden loss of network for no clear reason, followed by unexpected messages about SIM changes
- A new case on your phone
- A missed advertisement
= If your phone goes silent suddenly, call your provider from another phone at once.

Q: Which is the safest second-factor method?
- Telling the code to a friend
* An authenticator app code
- A short numeric password
- The same password for each account
= App-based codes cannot be intercepted by swapping your SIM.

Q: You lose your phone with your wallet. First action?
- Wait three days to see if it returns
* Report to your provider immediately to block the SIM and wallet
- Post about it and ask for the PIN to be reset by strangers
- Send your ID photo to a number in the post comments
= Quick blocking limits the damage.

Q: A "lender" asks you to pay a fee before the loan is released. This is most likely:
- Normal practice
* Advance-fee fraud
- A discount
- A government tax
= Honest lenders do not ask for upfront payments to personal numbers.

Q: A short loan costs 8 percent for 14 days. If renewed repeatedly through the year (26 times), the rough yearly cost is:
- About 8 percent
- About 26 percent
* More than 200 percent
- About 80 percent
= 8 percent x 26 is about 208 percent, ignoring extra fees.

Q: Which of these is a good sign that a lender is trustworthy?
- It asks for access to your contact list
- It threatens your friends
* It is licensed and states the total repayment clearly
- It will not show its terms until after you accept
= Licences and clear terms matter.

Q: In insurance, what is an excess (or deductible)?
- A free bonus
* The amount of the loss that you pay yourself
- A late fee
- A refund
= Read it before you buy.

Q: Which should you do before buying insurance?
- Skip the exclusions
* Check that the insurer is licensed and read what is covered and excluded
- Buy because an SMS promises a huge payout
- Pay cash to an unknown agent
= Verify and read before you pay.

Q: Why should you keep business and personal money apart?
- It is boring
* It shows the real profit and prevents hidden losses
- It forces customers to pay more
- It is illegal to mix them in every country
= Separate money gives clear information.

Q: A business had sales of UGX 2,000,000 and costs of UGX 1,500,000 including fees. The profit is:
- 3,500,000
- 1,500,000
* 500,000
- 250,000
= Profit is sales minus costs: 2,000,000 minus 1,500,000.

Q: A customer shows an SMS saying payment was sent. Your app has not received it. What do you do?
* Wait for the payment to appear in your own app before handing over the goods
- Hand over goods because the customer looks honest
- Ask for his PIN
- Return change first
= Only your official app or statement proves payment.

Q: What is the correct order for escalating a complaint about a financial service?
- Regulator, then provider, then forget
* Provider first, then the complaints manager, then the regulator or ombudsman
- Social media only
- The police only for overcharges
= Give the provider a chance, keep records and escalate when needed.

Q: Which of these are good elements of a personal safety plan? (choose all that apply)
* Official contact numbers for providers
* A regular review of statements and the plan
- A copy of your PIN in your wallet
- A promise to ignore alerts
= Plans hold contacts and steps, never secrets.
QUIZ,
];
