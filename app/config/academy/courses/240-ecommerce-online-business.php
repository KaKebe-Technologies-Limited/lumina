<?php
return [
    'slug'        => 'ecommerce-online-business',
    'title'       => 'E-commerce and Selling Online',
    'category'    => 'business',
    'level'       => 'intermediate',
    'icon'        => 'briefcase',
    'sort'        => 240,
    'summary'     => 'Learn how to choose what to sell, set up an online shop or social selling, take mobile money payments, deliver, market, keep records and grow, with a business plan and a 30-day launch plan.',
    'description' => '<p>E-commerce means buying and selling through the internet and mobile phones. In East Africa it often starts on WhatsApp, Facebook and Instagram, grows through mobile money and boda boda or courier delivery, and sometimes moves to a full online shop.</p><p>This course follows a seller from the first idea to a running business: choosing a product, pricing it, setting up a place to sell, getting paid and delivering safely, finding and keeping customers, and keeping honest books. Examples use Uganda shillings (UGX) and everyday situations you will recognise.</p><p>This is general education. It is not investment, legal or tax advice, so check current rules with the relevant authorities or a qualified adviser before you make big decisions.</p>',
    'outcomes'    => [
        'Compare online business models and choose a product or service worth selling',
        'Research a market and set prices that cover costs and leave a profit',
        'Choose a selling channel and prepare strong photos, descriptions and shop policies',
        'Accept mobile money and other payments safely and keep invoices and receipts',
        'Plan delivery, keep stock records and recognise fraud and handle disputes',
        'Attract customers with social media, content, reviews and consent-based messaging',
        'Keep simple books, track key numbers and understand profit and cash flow',
        'Write a business plan and a 30-day launch plan for your own online business',
    ],
    'requirements' => [
        'Completed The Internet, Email and Online Communication and Digital Marketing Fundamentals, or equivalent skills',
        'A smartphone or computer with internet access',
        'A notebook or spreadsheet for practice tasks',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Uganda Revenue Authority', 'url' => 'https://ura.go.ug'],
        ['title' => 'Uganda Registration Services Bureau', 'url' => 'https://ursb.go.ug'],
        ['title' => 'WhatsApp Business Help Centre', 'url' => 'https://faq.whatsapp.com'],
        ['title' => 'Personal Data Protection Office, Uganda', 'url' => 'https://www.pdpo.go.ug'],
        ['title' => 'Google Skillshop (free marketing courses)', 'url' => 'https://skillshop.withgoogle.com'],
    ],
    'modules' => [
        // ------------------------------------------------------------------ MODULE 1
        [
            'title'   => 'Module 1: The online business landscape',
            'summary' => 'Business models, choosing a product or service, researching the market, pricing and margins, and what makes online selling in East Africa different.',
            'lessons' => [
                [
                    'title'   => 'Online business models and the East African context',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to name the main ways of earning money online and describe what is special about selling in Uganda and East Africa. This matters because the right model is the one that fits your skills, money and local conditions, not simply the one that is fashionable.</p>
<h3>What is e-commerce?</h3>
<p><strong>E-commerce</strong> is buying and selling using the internet. The customer finds, chooses and often pays online or by mobile phone. The product may be physical, such as shoes, or digital, such as a design or a course, or it may be a service, such as tailoring or tutoring.</p>
<h3>Common business models</h3>
<table>
<thead><tr><th>Model</th><th>How it works</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Make and sell</td><td>You produce the item yourself</td><td>Handmade bags, baked goods, crafts</td></tr>
<tr><td>Buy and resell</td><td>You buy from a wholesaler and sell with a profit</td><td>Clothes, phone accessories, groceries</td></tr>
<tr><td>Service</td><td>You sell your time and skill</td><td>Graphic design, repairs, photography, tutoring</td></tr>
<tr><td>Digital products</td><td>You sell files that can be copied without stock</td><td>Templates, e-books, online lessons</td></tr>
<tr><td>Marketplace seller</td><td>You list items on a large platform that brings buyers</td><td>Listing on a classifieds or marketplace website</td></tr>
<tr><td>Agent or commission</td><td>You connect buyers with a supplier for a fee or percentage</td><td>Selling on behalf of farmers or other businesses</td></tr>
<tr><td>Dropshipping</td><td>A supplier ships to your customer; you handle the sale</td><td>Needs careful trust and quality checks</td></tr>
</tbody>
</table>
<h3>The East African context</h3>
<ul>
<li><strong>Mobile money</strong> is the main way many people pay and get paid. A seller who cannot take mobile money loses customers.</li>
<li><strong>Trust is the biggest hurdle.</strong> Many buyers have heard of fake sellers, so photos, real names, a physical location and reviews count a lot.</li>
<li><strong>Delivery</strong> often relies on boda boda riders, taxi and bus parcel services, and couriers. Addresses can be informal, so you may need landmarks and phone calls.</li>
<li><strong>Data costs and phones.</strong> Many customers use low-cost smartphones and buy small data bundles, so light images and short messages work best.</li>
<li><strong>Language.</strong> Use the language your customer prefers, such as English, Luganda, Acholi, Swahili or others.</li>
<li><strong>Cash on delivery</strong> is still common, which carries risks of no-shows that you must plan for.</li>
</ul>
<h3>Strengths of selling online</h3>
<p>You can start with little money, reach customers beyond your town, work from home, and see who is interested. But competition is high, customers compare prices quickly, and your reputation spreads fast in both directions.</p>
<h3>Choosing a model</h3>
<ol>
<li>List your skills, tools and the money you can safely risk.</li>
<li>Choose the model that you can start with the least cash, for example a service or small resale.</li>
<li>Consider the time you can give each day.</li>
<li>Start small, learn, then grow.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>List three business models from the table that you could start in your area.</li>
<li>For each, write one advantage and one risk.</li>
<li>Ask two people how they prefer to pay for things they buy online.</li>
<li>Choose the model you will use for your practice business plan.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>E-commerce means selling physical products, digital products or services through the internet and phones.</li>
<li>Pick a model that fits your skills, time and money, and start small.</li>
<li>In East Africa, mobile money, trust and delivery shape every online sale.</li>
<li>Light images and the customer's own language help sales.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Choosing what to sell and researching the market',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose a product or service with a real chance of selling and check demand and competition before spending money. Many small businesses fail not because the owner worked too little, but because nobody wanted the product at that price.</p>
<h3>Start with a problem</h3>
<p>The best products solve a clear problem for a specific group of people. Examples: students who need affordable printed notes, working mothers who want ready-cut vegetables, shop owners who need neat labels, farmers who need a way to find buyers. Ask: <em>Who has this problem? How do they solve it now? What annoys them about that?</em></p>
<h3>Idea filter</h3>
<p>Score each idea from 1 to 5 on these questions, then compare totals:</p>
<ol>
<li>Do I know the product or can I learn quickly?</li>
<li>Can I buy or make it consistently?</li>
<li>Can I start with the money I have?</li>
<li>Are there people willing to pay, and are they easy to reach online?</li>
<li>Is it easy to deliver and not easily damaged?</li>
<li>Is it legal and does it follow any rules for this kind of product, such as food, cosmetics or medicine, which may need approvals?</li>
<li>Is the profit large enough to be worth the work?</li>
</ol>
<h3>Researching the market</h3>
<p><strong>Market research</strong> means gathering facts about customers and competitors. You can do it for free:</p>
<ul>
<li><strong>Talk to customers.</strong> Ask ten people what they buy, how often, where from, what they pay and what they dislike.</li>
<li><strong>Study competitors.</strong> Look at similar sellers on social media and marketplaces. Note their prices, photos, delivery terms, reviews and what customers complain about.</li>
<li><strong>Test the interest.</strong> Post a photo and a price and see who asks. Take a small pre-order before buying large stock.</li>
<li><strong>Look at search and trends.</strong> Use search suggestions and platform search to see what people are looking for.</li>
</ul>
<h3>Find your difference</h3>
<p>If you only copy everyone, you will compete on price alone. Look for a difference customers care about: better quality, faster delivery, a local language, a neat gift pack, reliable after-sales help, or a clear niche such as "school uniforms for primary schools". A <strong>niche</strong> is a small, specific group of customers that you serve very well.</p>
<h3>Test small first</h3>
<p>A <strong>minimum viable product</strong> is the simplest version you can sell to learn quickly. Sell ten pieces, ask for feedback, improve, then grow. Never put all your savings or borrowed money into your first batch of stock.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write down three product or service ideas and score them with the idea filter.</li>
<li>Talk to five possible customers about the best idea and note their answers.</li>
<li>List three competitors, their prices and one weakness of each.</li>
<li>Write one sentence describing what will make your offer different.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Start with a real problem faced by a specific group of customers.</li>
<li>Score ideas against skills, money, demand, delivery, legality and profit.</li>
<li>Use free research: customer chats, competitor study and small tests.</li>
<li>Find a niche or difference and test small before investing heavily.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Pricing, costs and margins',
                    'minutes' => 17,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to calculate the true cost of a product, set a price that leaves a profit, and compute margin and break-even. Wrong pricing is the quiet killer of small shops: you can sell a lot and still lose money.</p>
<h3>Know your costs</h3>
<p>There are two main types of cost:</p>
<ul>
<li><strong>Variable costs</strong> change with each item sold: the purchase price or materials, packaging, delivery you pay, mobile money charges, and sales commission.</li>
<li><strong>Fixed costs</strong> stay the same however much you sell: rent, internet, a phone plan, a website, monthly adverts, wages.</li>
</ul>
<p>Beginners forget costs like transport to buy stock, packaging, airtime, losses from damaged items, and their own time. Write them all down.</p>
<h3>Cost per item</h3>
<p>Example: a seller makes tote bags. For one bag the cloth costs 6,000 UGX, thread and zip 1,000 UGX, packaging 500 UGX, and transport share 500 UGX. Variable cost per bag is <code>8,000 UGX</code>.</p>
<h3>Margin and markup</h3>
<p><strong>Gross profit</strong> is the selling price minus the variable cost. If you sell the bag at 15,000 UGX, gross profit per bag is <code>15,000 - 8,000 = 7,000 UGX</code>.</p>
<p><strong>Gross margin</strong> is gross profit as a percentage of the selling price: <code>7,000 / 15,000 = 46.7 percent</code>. <strong>Markup</strong> is gross profit as a percentage of cost: <code>7,000 / 8,000 = 87.5 percent</code>. People confuse these, so say which one you mean.</p>
<h3>Cover your fixed costs: break-even</h3>
<p>Suppose fixed costs are 280,000 UGX each month. Break-even units are fixed costs divided by gross profit per item:</p>
<pre>280,000 / 7,000 = 40 bags per month</pre>
<p>Below 40 bags you lose money; above it you make a profit. If 40 bags a month seems unlikely, lower your fixed costs, raise the price or find cheaper materials.</p>
<h3>Setting the price</h3>
<ol>
<li><strong>Start with cost.</strong> The price must be above total cost.</li>
<li><strong>Check competitors.</strong> What do similar buyers pay?</li>
<li><strong>Consider value.</strong> If your item is better, faster or more unique, you can charge more.</li>
<li><strong>Include fees</strong> such as mobile money withdrawal charges and delivery, or state clearly who pays them.</li>
<li><strong>Test.</strong> Try a price for a few weeks and watch how customers respond.</li>
</ol>
<p>Avoid competing only by being the cheapest: someone can always go lower, and a very low margin leaves you no money to grow. Use clear prices in UGX, for example "15,000 UGX, delivery in Gulu town 3,000 UGX". Discounts should be planned, for example 10 percent for a second item, and you should calculate whether you still earn a profit.</p>
<h3>Try it yourself</h3>
<ol>
<li>List every variable cost for one unit of your product or one hour of your service.</li>
<li>Choose a price and calculate gross profit, gross margin and markup.</li>
<li>Add up your monthly fixed costs and calculate break-even units.</li>
<li>Write down what you would change if break-even is too high.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Count all variable and fixed costs, including transport, packaging and fees.</li>
<li>Gross profit equals price minus variable cost; margin is profit divided by price.</li>
<li>Break-even equals fixed costs divided by gross profit per item.</li>
<li>Price for profit and value, not only to be the cheapest.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A woman sews school bags herself and sells them. Which business model is this?
- Dropshipping
* Make and sell
- Agent or commission
- Digital product
= She produces the item herself, so it is the make-and-sell model.

Q: Which is a special feature of online selling in Uganda that you should plan for?
- Customers never use phones.
- Mobile money is rarely used.
* Trust and delivery are big concerns and mobile money is a common way to pay.
= Sellers need clear proof they are genuine, a way to deliver, and a mobile money option.

Q: What is a niche?
* A small, specific group of customers that you serve very well
- A type of tax
- A large supermarket
- A payment system
= A niche lets you stand out instead of competing with everyone on price.

Q: Which are good free ways to research a market? (choose all that apply)
* Talk to ten possible customers
* Study competitors' prices and reviews
- Guess without asking anyone
- Spend all savings on stock first
= Research with real customers and competitors reduces risk before you spend money.

Q: Which of these is a fixed cost?
- Packaging for each item
- Cloth bought per bag
* Monthly rent for a stall
= Fixed costs stay the same however many items you sell.

Q: A bag costs 8,000 UGX to make and sells for 15,000 UGX. What is the gross profit per bag?
- 15,000 UGX
- 8,000 UGX
* 7,000 UGX
- 23,000 UGX
= Gross profit is price minus variable cost: 15,000 minus 8,000 equals 7,000 UGX.

Q: Fixed costs are 280,000 UGX per month and gross profit per bag is 7,000 UGX. What is the break-even?
* 40 bags per month
- 7 bags per month
- 280 bags per month
= Break-even is fixed costs divided by gross profit per item: 280,000 divided by 7,000 equals 40.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 2
        [
            'title'   => 'Module 2: Setting up to sell',
            'summary' => 'Choose a selling channel, prepare photos and descriptions that sell, write clear policies and understand legal basics.',
            'lessons' => [
                [
                    'title'   => 'Choosing where to sell',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to compare the main places to sell online and pick the best starting point for your product and customers. You do not need an expensive website on day one; many successful sellers began with a phone and a WhatsApp number.</p>
<h3>Your main options</h3>
<table>
<thead><tr><th>Channel</th><th>Strengths</th><th>Weaknesses</th></tr></thead>
<tbody>
<tr><td>Social selling (Facebook, Instagram, TikTok, status posts)</td><td>Free to start; customers already there; easy to show products</td><td>Hard to organise orders; you depend on the platform's rules and reach</td></tr>
<tr><td>WhatsApp Business with a catalogue</td><td>Free business tools; labels, quick replies, product catalogue; direct chat</td><td>Customers must find you first; manual order handling</td></tr>
<tr><td>Online marketplace or classified site</td><td>Existing buyers and search; some offer payments or delivery</td><td>Fees or commission; competitors beside you; platform sets rules</td></tr>
<tr><td>Your own online shop</td><td>Full control, own brand, own customer list, orders and stock in one place</td><td>Needs set-up, domain and hosting costs, and your own marketing</td></tr>
<tr><td>Physical plus online</td><td>Builds trust; customers can see products and collect</td><td>Needs a place and time</td></tr>
</tbody>
</table>
<p>Fees and features change, so read the current terms of any platform before you commit.</p>
<h3>Online shop platforms in general terms</h3>
<p>When you outgrow chat-based selling, you can use <strong>shop platforms</strong>. These are services where you pay a monthly fee to get a ready shop with product pages, a basket, checkout, order lists and sometimes payment tools. Another route is a WordPress site with an online-shop plugin, which gives you more control but needs more technical care. When comparing, check:</p>
<ul>
<li>Monthly cost and any commission on sales.</li>
<li>Which payment methods work in Uganda, including mobile money.</li>
<li>Whether the shop works well on phones and light connections.</li>
<li>How easy it is to add products and to export your data if you leave.</li>
<li>Support and learning resources.</li>
</ul>
<h3>Start simple, then step up</h3>
<ol>
<li><strong>Stage 1:</strong> WhatsApp Business plus a social page. Learn what sells and what customers ask.</li>
<li><strong>Stage 2:</strong> Add a catalogue, order form or marketplace listing as volume grows.</li>
<li><strong>Stage 3:</strong> Build your own shop when you have repeat customers and steady orders.</li>
</ol>
<h3>Do not rent your whole business</h3>
<p>If you rely only on one social platform, a banned account or changed rule can end your sales overnight. Collect customer phone numbers and, with consent, email addresses so you can reach them elsewhere. Keep copies of your photos and product lists.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose a channel for your first 30 days and write three reasons for the choice.</li>
<li>Set up a WhatsApp Business profile or social page with a name, logo, description, hours and location.</li>
<li>Add at least three products to a catalogue with price and photo.</li>
<li>Make a plan for when you will move to the next stage.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>You can start selling with a phone, WhatsApp Business and a social page.</li>
<li>Compare cost, payments, control and ease of use before choosing a platform.</li>
<li>Grow in stages as orders and repeat customers increase.</li>
<li>Keep your own customer list so you do not depend on one platform.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Product photos and descriptions that sell',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to take clear product photos with a phone and write descriptions that answer the buyer's questions. Online customers cannot touch your product, so your photos and words must do that job and prove you are real.</p>
<h3>Photography with a phone</h3>
<ol>
<li><strong>Clean the lens</strong> with a soft cloth before every session.</li>
<li><strong>Use daylight.</strong> Stand near a window or shoot outdoors in shade. Avoid harsh direct sun and avoid the flash, which makes shiny items and faces look unnatural.</li>
<li><strong>Use a plain background:</strong> a white sheet, clean wall or a table. Remove clutter.</li>
<li><strong>Hold steady.</strong> Rest your elbows on a table or use a small tripod. Tap the screen on the product so it focuses.</li>
<li><strong>Take several angles:</strong> front, back, side, close-up of details, and one showing size, such as in a hand or next to a coin or ruler.</li>
<li><strong>Show it in use</strong> in at least one photo.</li>
<li><strong>Be honest.</strong> Do not edit colours so much that the product looks different when delivered. Mismatched photos cause returns and bad reviews.</li>
</ol>
<p>Crop and lightly brighten with your phone's editor. Keep files light enough to open quickly on mobile data. Do not use someone else's photos: use your own, or images you have permission for.</p>
<h3>Writing product descriptions</h3>
<p>A good description answers the questions a customer would ask in a shop. Use this pattern:</p>
<ul>
<li><strong>Name:</strong> clear and specific, such as "Women's Leather Sandals, Brown, Size 38".</li>
<li><strong>Price:</strong> in UGX, plus any delivery charge.</li>
<li><strong>Key details:</strong> size, colour, material, weight, capacity, what is included.</li>
<li><strong>Benefit:</strong> one sentence on how it helps the customer.</li>
<li><strong>Condition:</strong> new or used, with honest notes on flaws.</li>
<li><strong>Availability:</strong> how many are in stock and how long a preorder takes.</li>
<li><strong>How to order:</strong> the number, link or button to use.</li>
</ul>
<p>Example:</p>
<blockquote><p>School backpack, navy blue, 25 litres. Strong zips, padded straps, space for a laptop up to 14 inches. 45,000 UGX. Delivery in Gulu 3,000 UGX. 12 in stock. Message "BAG" on WhatsApp to order.</p></blockquote>
<h3>Common mistakes</h3>
<ul>
<li>Dark, blurry photos or photos with other people's watermarks.</li>
<li>"DM for price", which makes buyers leave. Show the price.</li>
<li>Exaggerated claims such as "the best in Africa" that you cannot prove.</li>
<li>Medical or health claims you cannot support. They can be false and harmful.</li>
<li>Missing sizes or colours, leading to wasted chats.</li>
</ul>
<h3>Keep a consistent look</h3>
<p>Use the same background and style for all photos, and put your business name or logo discreetly in a corner. A tidy, consistent catalogue looks trustworthy.</p>
<h3>Try it yourself</h3>
<ol>
<li>Photograph one product from four angles using window light.</li>
<li>Pick the best three and lightly crop them.</li>
<li>Write a description using the pattern above, including price and delivery.</li>
<li>Ask a friend to read it and say what they still wonder about, then improve it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Daylight, a plain background and several angles make a phone photo sell.</li>
<li>Show honest photos that match what you deliver.</li>
<li>A good description gives name, price, key details, benefit, condition, stock and how to order.</li>
<li>Show prices and avoid claims you cannot prove.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Policies and legal basics',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write simple shop policies for delivery, returns and payment, and describe general legal steps a seller should consider. Clear rules protect both you and your customers and prevent most arguments. This is general education, not legal advice.</p>
<h3>Why policies matter</h3>
<p>Customers who cannot see you in person need to know what happens if something goes wrong. A short written policy builds trust and gives you something to point to when a disagreement arises. Post it on your page or catalogue, and send it with the first order.</p>
<h3>Four policies every seller needs</h3>
<ol>
<li><strong>Payment policy:</strong> accepted methods, whether you need a deposit, and when payment must be made. Example: "50 percent deposit by mobile money to confirm custom orders; balance on delivery."</li>
<li><strong>Delivery policy:</strong> areas covered, who pays, expected times, what happens if nobody answers the phone, and how long you keep an order before cancelling.</li>
<li><strong>Returns and refunds policy:</strong> what can be returned, within how many days, in what condition, who pays return transport, and whether you refund, replace or give a credit. Be fair and clear. Example: "Unused items in original packaging may be returned within 3 days. Perishable items cannot be returned."</li>
<li><strong>Privacy statement:</strong> how you use customers' names, numbers and addresses (only to fulfil orders and, with permission, send updates) and that you do not share them.</li>
</ol>
<p>Keep each policy short, in plain language and in the language your customers use.</p>
<h3>Legal basics in general terms</h3>
<p>Rules depend on what you sell and how big you are, and they change. Here are areas to learn about from official sources:</p>
<ul>
<li><strong>Business registration.</strong> Many businesses register a name or a company with the national registration body. Registration can help with opening accounts, tenders and trust.</li>
<li><strong>Taxes and licences.</strong> Businesses may need a taxpayer number and may owe different taxes depending on income and activity. Local authorities may also require trading licences. Ask the tax authority or a qualified adviser what applies to you.</li>
<li><strong>Product rules.</strong> Food, cosmetics, medicines and some electronics may need approval by a standards or regulatory body. Selling fake, stolen or banned goods is illegal.</li>
<li><strong>Consumer rights.</strong> Sell goods that match your description and are fit for use. Do not mislead about price, quality or origin.</li>
<li><strong>Data protection.</strong> Customer data is personal data. Collect only what you need, keep it safe and use it only for the stated purpose.</li>
<li><strong>Intellectual property.</strong> Do not copy other people's logos, photos, music or product designs, and do not sell counterfeit brands.</li>
</ul>
<h3>Keep records</h3>
<p>Keep proof of orders, payments, delivery and conversations. If a dispute arises, written records protect you.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write a three-line delivery policy and a three-line returns policy for your business.</li>
<li>Write a one-sentence privacy statement for your order form.</li>
<li>Visit the websites of your national business registration and tax authorities and note one requirement that may apply to you.</li>
<li>List any products you intend to sell that might need approval.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Clear payment, delivery, returns and privacy policies build trust and prevent disputes.</li>
<li>Check registration, tax, licensing and product rules with official sources or an adviser.</li>
<li>Never sell fake, stolen or banned goods or copy others' brands.</li>
<li>Keep written records of orders and communication.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is a sensible first channel for a beginner with a small budget?
- A custom-built website with all features
* WhatsApp Business with a catalogue and a social page
- A large paid advertising campaign
- Renting a shop in the city
= Starting simple lets you learn what sells at low cost. You can grow later.

Q: Why should you keep your own customer list?
* So you do not depend on a single platform that could change rules or ban your account
- Because social media forbids it
- To sell the data to others
= Your own list lets you reach customers whatever happens to one platform.

Q: Which are good practices for product photos? (choose all that apply)
* Use daylight and a plain background
* Show several angles and a size reference
- Edit colours so the product looks better than it is
- Use photos copied from another seller
= Honest, original, clear photos build trust and reduce returns.

Q: Which description is better?
- Nice bag, DM for price.
* School backpack, navy, 25 litres, 45,000 UGX, delivery in Gulu 3,000 UGX, 12 in stock, message BAG to order.
- The best bag in Africa!
= A good description includes details, price, delivery, stock and how to order.

Q: Which policy tells customers what happens to unwanted items?
- Privacy statement
- Payment policy
* Returns and refunds policy
= A returns policy states what can be returned, when and how it is refunded or replaced.

Q: Which statement about legal matters is best?
- No rules apply to online sellers.
- All rules are the same for every product.
* Rules depend on what you sell; check official sources such as tax and registration authorities or ask an adviser.
= Registration, tax, licences and product rules vary and change, so rely on current official information.

Q: Why keep records of orders, payments and chats?
- To fill your phone memory
* They protect you if a dispute arises
- Customers must be shown all your chats
= Written proof helps resolve disagreements fairly.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 3
        [
            'title'   => 'Module 3: Payments and logistics',
            'summary' => 'Take payments safely, issue invoices and receipts, plan delivery and stock, and handle disputes and fraud.',
            'lessons' => [
                [
                    'title'   => 'Mobile money, cards and payment gateways',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain the main ways customers can pay you online, set up payments carefully and avoid the common scams. Getting paid safely and easily is the heart of selling, and mistakes with payments cost real money.</p>
<h3>Ways to receive payment</h3>
<table>
<thead><tr><th>Method</th><th>How it works</th><th>Points to watch</th></tr></thead>
<tbody>
<tr><td>Mobile money (personal number)</td><td>Customer sends money to your registered number</td><td>Quick and familiar; use only a number in your own name; check charges and daily limits</td></tr>
<tr><td>Mobile money merchant account</td><td>A business account or code from the mobile network</td><td>Better for businesses; usually lower or clearer fees; needs registration documents</td></tr>
<tr><td>Bank transfer or deposit</td><td>Customer pays into your bank account</td><td>Good for larger amounts; check the money has cleared</td></tr>
<tr><td>Cards</td><td>Customer pays online with a bank card</td><td>Needs a payment provider; card use is lower in many communities but growing</td></tr>
<tr><td>Cash on delivery</td><td>Customer pays when the item arrives</td><td>Risk of refusal; confirm the order first and consider a deposit</td></tr>
<tr><td>Payment links and wallets</td><td>A provider sends a link or a request that the customer approves</td><td>Convenient; use only reputable providers</td></tr>
</tbody>
</table>
<h3>What is a payment gateway?</h3>
<p>A <strong>payment gateway</strong> is a service that connects your online shop to payment methods such as mobile money and cards. The customer chooses how to pay on a secure page; the gateway processes it and passes the money to you, after taking a fee. In general terms, to use one you register your business, give identification and bank or mobile money details, connect it to your shop and test it. Compare providers by fees, supported methods, payout time, customer support and how they treat disputes. Use only providers that are properly licensed and well known; ask your bank or the financial regulator if unsure.</p>
<h3>Safe payment habits</h3>
<ol>
<li><strong>Confirm money has actually arrived</strong> by checking the official confirmation or your account balance, not only a screenshot. Fake confirmation messages and edited screenshots are common.</li>
<li><strong>Never release goods</strong> against only a message saying "I have sent".</li>
<li><strong>Never share your PIN, passwords or one-time codes</strong> with anyone, including people who claim to be from the mobile money company or a bank.</li>
<li><strong>Beware of overpayment tricks:</strong> a buyer "accidentally" pays too much and asks you to send back the difference before the original payment is confirmed or reversed.</li>
<li><strong>Use a deposit</strong> for custom or high-value orders.</li>
<li><strong>Keep personal and business money apart</strong> where you can, so records stay clear.</li>
<li><strong>Be clear about fees</strong> and who pays them.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>List the payment methods your customers already use.</li>
<li>Find the current fees for receiving money on your mobile money line, or for a merchant account, and write down the cost for a 50,000 UGX sale.</li>
<li>Write a simple payment instruction message for customers, including the name that will appear when they pay.</li>
<li>Write your rule for confirming payment before sending goods.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Offer the payment methods your customers actually use, starting with mobile money.</li>
<li>A payment gateway links your shop to payment methods for a fee; choose only reputable providers.</li>
<li>Confirm that money has really arrived before you release goods.</li>
<li>Never share PINs or one-time codes and beware overpayment tricks.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Invoices, receipts and delivery',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to create a proper invoice and receipt, compare delivery options, and keep customers informed. Professional paperwork and reliable delivery turn a first-time buyer into a returning one.</p>
<h3>Invoices and receipts</h3>
<p>An <strong>invoice</strong> is a request for payment. A <strong>receipt</strong> is proof that payment has been made. Both can be a simple document, a PDF or a message, as long as they contain the right facts. A basic invoice includes:</p>
<ul>
<li>Your business name, contact details and any tax number.</li>
<li>The customer's name and delivery details.</li>
<li>A unique invoice number, such as INV-0012, and the date.</li>
<li>Items, quantities and prices in UGX, with delivery shown separately.</li>
<li>The total, payment terms and how to pay.</li>
</ul>
<p>A receipt shows the same details plus the amount paid, the method, the date and the words "Paid". Number your documents in order and keep copies in a folder on your phone or computer. A spreadsheet or a free template saves time. If you are registered for taxes, follow the official rules on the format of receipts and invoices.</p>
<h3>Delivery options</h3>
<table>
<thead><tr><th>Option</th><th>Best for</th><th>Watch out for</th></tr></thead>
<tbody>
<tr><td>Customer pickup</td><td>Locals, bulky goods</td><td>Set times and a safe, public place</td></tr>
<tr><td>Boda boda or local rider</td><td>Same-day delivery within town</td><td>Trust; agree a price; share the order only with the rider</td></tr>
<tr><td>Bus or taxi parcel services</td><td>Parcels between towns</td><td>Delays; get a receipt and a contact at the other end</td></tr>
<tr><td>Courier or postal service</td><td>Tracked delivery, longer distances</td><td>Cost and speed vary; ask about compensation for loss</td></tr>
<tr><td>Delivery apps</td><td>Food and quick local items</td><td>Commission and platform rules</td></tr>
</tbody>
</table>
<h3>Delivery steps that prevent problems</h3>
<ol>
<li>Confirm the order, price, delivery fee and address by message.</li>
<li>Get a landmark and two phone numbers for the customer.</li>
<li>Package safely: sturdy bag or box, wrapped fragile items, sealed against rain.</li>
<li>Label the parcel with the name, phone number and order number.</li>
<li>Tell the customer when it is sent, who the rider or company is and the expected time.</li>
<li>Take a photo of the packed item before sending it, as evidence of its condition.</li>
<li>Ask for confirmation on receipt, and ask politely for feedback.</li>
</ol>
<p>Work out delivery costs in advance and decide whether you charge a flat fee, charge by distance or include delivery in the price. State it clearly to avoid arguments.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a simple invoice template in a document or spreadsheet with the fields above.</li>
<li>Fill it in for a sample sale of two items with delivery.</li>
<li>Write your delivery fees for three areas near you.</li>
<li>Write the message you would send a customer when their parcel is on the way.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>An invoice requests payment; a receipt proves it. Number and keep copies of both.</li>
<li>Choose a delivery option that matches distance, cost, speed and trust.</li>
<li>Package well, label clearly and keep the customer informed.</li>
<li>Photograph items before dispatch and ask for confirmation on arrival.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Stock records, disputes and fraud awareness',
                    'minutes' => 17,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to keep a basic stock record, handle a complaint calmly and fairly, and recognise common online fraud. These three skills protect both your money and your reputation.</p>
<h3>Why stock records matter</h3>
<p>If you do not know what you have, you cannot tell what is missing, what is selling and when to reorder. You may promise a customer an item that is no longer there. A simple stock record fixes this.</p>
<h3>A simple stock sheet</h3>
<p>Use a notebook or spreadsheet with these columns:</p>
<pre>Item | Opening stock | Added | Sold | Damaged/lost | Closing stock | Reorder level</pre>
<ol>
<li>Count stock at the start of each week and write it as opening stock.</li>
<li>Record each purchase in <strong>Added</strong> and each sale in <strong>Sold</strong>, daily.</li>
<li>Record damaged, expired or lost items separately; these are real costs.</li>
<li>Calculate closing stock as opening plus added minus sold minus damaged.</li>
<li>Count the shelf and compare it with the sheet. Investigate differences.</li>
<li>Set a <strong>reorder level</strong>: when stock falls to this number, order more.</li>
</ol>
<p>Sell older items first, especially food and cosmetics with expiry dates.</p>
<h3>Handling disputes</h3>
<p>Even good sellers get complaints. A calm response can win a customer for life. Use this script:</p>
<ol>
<li><strong>Listen</strong> and let the customer explain fully. Do not argue.</li>
<li><strong>Apologise</strong> for the trouble without admitting fault before you check.</li>
<li><strong>Ask for evidence</strong> such as photos and the order number.</li>
<li><strong>Check your records:</strong> the order, payment, packing photo and delivery confirmation.</li>
<li><strong>Offer a fair solution</strong> within your policy: replace, repair, refund or credit.</li>
<li><strong>Follow up</strong> to confirm the customer is satisfied, and note what you will change.</li>
</ol>
<p>Stay polite even if the customer is rude. If agreement fails, you can suggest a neutral person, a local leader or the platform's dispute process, or seek advice from a consumer or legal body.</p>
<h3>Fraud awareness</h3>
<table>
<thead><tr><th>Scam</th><th>How to protect yourself</th></tr></thead>
<tbody>
<tr><td>Fake payment screenshot or message</td><td>Check your account or the official confirmation</td></tr>
<tr><td>Overpayment and "send back the difference"</td><td>Never refund before the original payment is confirmed</td></tr>
<tr><td>Fake "agent" asking for PIN or codes</td><td>Never share them; real companies never ask</td></tr>
<tr><td>Buyer who insists on an odd delivery arrangement</td><td>Use your own trusted delivery method</td></tr>
<tr><td>Fake sellers who take deposits and vanish (when you are the buyer)</td><td>Check reviews, a real location and use pay on delivery for new sellers</td></tr>
<tr><td>Phishing links that imitate banks or platforms</td><td>Type the address yourself; do not log in through links in messages</td></tr>
</tbody>
</table>
<p>If you are a victim of fraud, keep evidence, report it to the platform, your mobile money provider or bank, and the police, and warn others where appropriate without naming anyone falsely.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a stock sheet for five items and fill in sample numbers for a week.</li>
<li>Calculate closing stock for each item and set reorder levels.</li>
<li>Write your reply to a customer who says the item arrived damaged.</li>
<li>List three scam signs that you will watch out for.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Record stock in, out, damaged and remaining, and count the shelf against the record.</li>
<li>Handle complaints by listening, checking records and offering a fair solution within your policy.</li>
<li>Verify payments and never share PINs or codes.</li>
<li>Keep evidence and report fraud promptly.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is a payment gateway?
- A person who delivers goods
* A service that connects your online shop to payment methods such as mobile money and cards
- A type of bank loan
- A shop policy
= A gateway handles the payment securely and passes the money to you, usually for a fee.

Q: A buyer sends a screenshot saying they paid. What should you do?
- Release the goods at once.
* Check your own account or the official confirmation before releasing goods.
- Ask the buyer to send the screenshot again.
= Screenshots and messages can be faked, so confirm the money has arrived.

Q: Which should you never share with anyone? (choose all that apply)
* Your mobile money PIN
* One-time codes sent to your phone
- Your shop's opening hours
- Your price list
= Real companies never ask for PINs or codes. Sharing them lets criminals take your money.

Q: What does an invoice do?
* Requests payment and lists items, prices and the total
- Proves payment has been made
- Replaces a delivery policy
= An invoice asks for payment; a receipt proves it was paid.

Q: Which step helps prevent delivery disputes?
- Sending the parcel without telling the customer
- Using unlabeled parcels
* Photographing the packed item and confirming delivery with the customer
= Evidence of condition and confirmation of receipt help settle complaints fairly.

Q: Closing stock equals:
- Opening stock plus sold
* Opening stock plus added minus sold minus damaged
- Sold minus added
- Opening stock only
= The formula tracks what should be on the shelf, and you compare it with a real count.

Q: A customer pays 20,000 UGX too much and asks you to send the difference back quickly. What is the safest response?
- Send the difference immediately.
* Wait until the original payment is confirmed in your account, then follow your policy.
- Block the customer without a word.
= Overpayment tricks rely on you refunding money before the first payment fails or is reversed.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 4
        [
            'title'   => 'Module 4: Marketing and customers',
            'summary' => 'Sell through social media and content, use ads wisely, collect reviews, serve customers well and build repeat business with consent-based messaging.',
            'lessons' => [
                [
                    'title'   => 'Social media selling and content',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to plan a week of social media content for your business and turn followers into buyers. Social media is where most small East African sellers meet their customers, but posting random photos rarely works. A simple plan does.</p>
<h3>Choose where your customers are</h3>
<p>You cannot be everywhere. Ask your customers where they spend time. Facebook and WhatsApp suit broad local audiences, Instagram and TikTok suit visual products and younger customers, and a business page on a professional network suits services for companies. Pick one or two and do them well.</p>
<h3>Set up a trustworthy profile</h3>
<ul>
<li>Clear name, logo or a good photo, and a short description of what you sell.</li>
<li>Phone or WhatsApp number, location, opening hours and delivery areas.</li>
<li>A pinned post showing how to order and how to pay.</li>
<li>Photos of the real shop, products and, with permission, happy customers.</li>
</ul>
<h3>A simple content mix</h3>
<p>Use a mix so the page does not feel like constant advertising. A common balance is:</p>
<table>
<thead><tr><th>Type</th><th>Examples</th><th>Share</th></tr></thead>
<tbody>
<tr><td>Show products</td><td>New items, price lists, short demonstration videos</td><td>About 40 percent</td></tr>
<tr><td>Teach and help</td><td>How to choose, care tips, answers to common questions</td><td>About 30 percent</td></tr>
<tr><td>Build trust</td><td>Behind the scenes, customer feedback, delivery proof</td><td>About 20 percent</td></tr>
<tr><td>Offers and calls to action</td><td>Promotions, limited stock, how to order</td><td>About 10 percent</td></tr>
</tbody>
</table>
<h3>Writing posts that sell</h3>
<ol>
<li>Begin with a hook: a question or benefit in the first line.</li>
<li>Keep the message short and clear.</li>
<li>Show the price and how to order.</li>
<li>End with one call to action, such as "Message us to order today".</li>
<li>Use a few relevant hashtags and your location.</li>
<li>Reply to comments and messages quickly, ideally within an hour in business hours.</li>
</ol>
<h3>Short videos and WhatsApp status</h3>
<p>Short videos of 15 to 30 seconds showing the product in use, a "how it is made" clip or an unboxing often get more attention than still photos. Use WhatsApp status for daily updates to contacts who already know you. Keep videos light so they play on mobile data.</p>
<h3>Plan a week</h3>
<p>Choose three posting days, prepare your photos in one session, and write captions ahead of time. Consistency beats volume: three good posts a week is better than twelve rushed ones followed by silence.</p>
<h3>Be honest and respectful</h3>
<p>Do not make false claims, post customers' photos or chats without permission, use other sellers' pictures, or run fake "giveaways". Platforms may remove pages that break their rules, and customers remember dishonest sellers.</p>
<h3>Try it yourself</h3>
<ol>
<li>Pick one platform and improve your profile using the checklist above.</li>
<li>Plan three posts for the next week: one product, one helpful tip, one trust-building post.</li>
<li>Write the captions with a clear call to action.</li>
<li>Publish them and note how many reactions and messages each gets.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Choose one or two platforms where your customers already are.</li>
<li>A complete profile with contact details and ordering steps builds trust.</li>
<li>Mix product posts with helpful, trust-building and offer posts.</li>
<li>Reply fast, be consistent and be honest.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Ads, reviews and customer service',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to run a small, safe test of paid adverts, collect honest reviews and respond to customers in a way that makes them return. Spending on ads without a plan wastes money, while good service costs almost nothing and pays back many times.</p>
<h3>Paid ads basics</h3>
<p>Social platforms let you pay to show your post or advert to chosen people. You normally choose a goal (messages, website visits, sales), an audience (location, age range, interests), a budget and a duration. For a first test:</p>
<ol>
<li><strong>Pick a product that already sells well</strong> without ads. Ads speed up success; they do not fix a poor offer.</li>
<li><strong>Choose a clear goal</strong>, usually "get messages on WhatsApp".</li>
<li><strong>Target locally</strong>, for example people within 20 kilometres of Gulu aged 18 to 45.</li>
<li><strong>Set a small daily budget</strong>, such as 5,000 to 10,000 UGX, for 5 to 7 days. Never use rent money or borrowed money.</li>
<li><strong>Use a strong photo or video</strong> and a short message with price and call to action.</li>
<li><strong>Measure:</strong> cost per message and how many messages became orders. If you spent 40,000 UGX and got 4 orders, your cost per order is 10,000 UGX. Compare it with your profit per order.</li>
<li><strong>Keep what works</strong> and stop what does not.</li>
</ol>
<p>Ad tools and rules change often, so read the platform's current guidance before spending.</p>
<h3>Reviews and proof</h3>
<p>People trust other customers more than they trust sellers. After a successful delivery, politely ask happy customers for a short review or permission to share their comment. Do these things:</p>
<ul>
<li>Ask for permission before sharing names, photos or chats.</li>
<li>Never write fake reviews or pay for them; this is dishonest and platforms can punish it.</li>
<li>Reply to every review, thanking good ones and answering negative ones calmly and helpfully.</li>
<li>Keep a folder of real photos and comments to show new customers.</li>
</ul>
<h3>Customer service scripts</h3>
<p>Prepared replies save time and keep your tone consistent. Adapt these to your own style:</p>
<blockquote><p>Greeting: "Hello, thank you for contacting [Business]. How can I help you today?"</p></blockquote>
<blockquote><p>Price enquiry: "The [item] is [price] UGX. Delivery in [area] is [fee] UGX. We have [number] in stock. Would you like to order?"</p></blockquote>
<blockquote><p>Order confirmation: "Thank you. Your order is [item] for [total]. Please pay to [number, name]. We will send it once the payment is confirmed."</p></blockquote>
<blockquote><p>Delay: "We are sorry. Your order will arrive tomorrow by [time] instead. Thank you for your patience."</p></blockquote>
<blockquote><p>Complaint: "I am sorry about this. Please send a photo and your order number, and we will fix it today."</p></blockquote>
<p>WhatsApp Business offers quick replies, away messages and labels that help you manage chats. Always answer politely and honestly, even when you must say no.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write a small ad test plan: product, goal, audience, budget, dates and how you will measure results.</li>
<li>Calculate the maximum you can pay per order and still make a profit.</li>
<li>Write five quick replies for common questions.</li>
<li>Ask two recent customers for permission to share a review.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Test ads with a small budget on a product that already sells, and measure cost per order.</li>
<li>Collect honest reviews with permission and never fake them.</li>
<li>Prepared, polite scripts speed up service and keep quality steady.</li>
<li>Good service is the cheapest marketing.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Repeat customers, referrals and consent-based lists',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a list of customers who have agreed to hear from you, keep them coming back and encourage referrals. It usually costs far less to sell again to an existing happy customer than to find a new one.</p>
<h3>Why repeat customers matter</h3>
<p>A returning customer already trusts you, needs less convincing, buys more often and tells friends. Even a small business can grow steadily by keeping its customers happy. Track how many customers buy a second time: this is your <strong>repeat rate</strong>.</p>
<h3>Build a list with consent</h3>
<p>A <strong>contact list</strong> is a record of customers' names and contact details, such as WhatsApp numbers or emails. To use it for marketing messages, you must have their <strong>consent</strong>, meaning they clearly agreed to receive them. Uganda's data protection rules treat personal data seriously, so follow these principles and check the current law:</p>
<ol>
<li><strong>Ask clearly:</strong> "May we send you our new arrivals and offers on WhatsApp about twice a month? Reply YES to agree."</li>
<li><strong>Keep proof</strong> of the consent, such as the message or a sign-up form.</li>
<li><strong>Do not add people</strong> to groups or broadcast lists without asking.</li>
<li><strong>Make it easy to stop.</strong> Say "Reply STOP to leave" and honour it quickly.</li>
<li><strong>Collect only what you need,</strong> keep it safe and never sell or share it.</li>
<li><strong>Send useful messages,</strong> not spam. Too many messages cause blocks and reports.</li>
</ol>
<p>WhatsApp broadcast lists send a message individually only to people who have saved your number. Email works the same way: use a simple email tool, include your business name and address, and an unsubscribe link.</p>
<h3>What to send</h3>
<ul>
<li>New arrivals with photos and prices.</li>
<li>A thank-you after purchase and a check that everything is fine.</li>
<li>Helpful tips on using the product.</li>
<li>Reminders when customers usually run out of something.</li>
<li>Special offers or early access for regular customers.</li>
<li>Seasonal messages, such as before school term or festive periods.</li>
</ul>
<h3>Referrals and loyalty</h3>
<p>Happy customers are your best advertisers. Make it easy and rewarding for them:</p>
<ul>
<li>Ask directly after a good experience: "Do you know anyone else who might like this?"</li>
<li>Offer a small reward for both people, such as a 5 percent discount for the friend and the referrer. Check you still earn a profit.</li>
<li>Use a simple loyalty card or a record: the tenth purchase gets a small gift or a discount.</li>
<li>Say thank you in a personal way and remember names and preferences.</li>
</ul>
<h3>Common mistakes</h3>
<ul>
<li>Adding every contact in your phone to a promotion list.</li>
<li>Sending messages late at night or too often.</li>
<li>Ignoring people who ask to stop.</li>
<li>Sharing customers' numbers in a big group where everyone can see them.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Write a consent message to invite customers to your list.</li>
<li>Create a simple table with name, number, consent date, consent method and last purchase.</li>
<li>Plan one useful monthly message and one referral reward, and calculate the cost per reward.</li>
<li>Write the reply you will use when someone asks to stop messages.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Returning customers cost less to win and buy more.</li>
<li>Only message people who have clearly agreed, keep proof and honour requests to stop.</li>
<li>Send useful, occasional messages, not spam.</li>
<li>Reward referrals and loyalty, and make sure the reward still leaves a profit.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the best first step before running paid ads?
- Spend a large budget immediately.
* Choose a product that already sells and set a small test budget and a clear goal.
- Use borrowed money.
- Copy a competitor's advert exactly.
= Ads multiply what works. Start small, measure, and keep only what pays.

Q: You spent 40,000 UGX on ads and received 4 orders. What is your cost per order?
- 4,000 UGX
* 10,000 UGX
- 160,000 UGX
- 40,000 UGX
= Cost per order is the amount spent divided by the number of orders: 40,000 divided by 4 equals 10,000.

Q: Which is a good way to get reviews? (choose all that apply)
* Ask happy customers politely after delivery
* Ask permission before sharing their photos or comments
- Write fake reviews yourself
- Pay strangers to post praise
= Honest reviews build real trust. Fake ones are dishonest and can get you punished.

Q: Which social media content mix works best?
- Only adverts every day
* A mix of product posts, helpful tips, trust-building posts and a few offers
- Only jokes
= Customers follow pages that help and entertain as well as sell.

Q: Why use prepared customer service replies?
- They replace the need to read messages.
* They save time and keep your tone consistent and polite
- They are required by law.
= Scripts help you respond quickly but should still be adapted to each customer.

Q: Which is the proper way to start a WhatsApp promotion list?
- Add all phone contacts without telling them
- Buy a list of numbers
* Ask customers clearly, keep proof of consent and let them stop easily
= Consent-based lists protect customers and your reputation and respect data protection rules.

Q: What should you do when a customer replies STOP?
* Remove them from the list promptly
- Keep sending messages
- Add them to a second list
= Respecting requests to stop is both polite and part of good data practice.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 5
        [
            'title'   => 'Module 5: Running and growing',
            'summary' => 'Keep simple books, track key numbers, understand taxes in general terms, scale safely and plan your business and first 30 days.',
            'lessons' => [
                [
                    'title'   => 'Bookkeeping basics: income, costs, profit and cash flow',
                    'minutes' => 17,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to keep a simple record of money in and out, calculate profit and understand cash flow. Many small sellers think they are doing well because money passes through their phone, but without records they cannot tell whether they are actually earning.</p>
<h3>Key words</h3>
<ul>
<li><strong>Income (revenue or sales):</strong> money received from customers.</li>
<li><strong>Costs (expenses):</strong> money spent to run the business, such as stock, transport, airtime, adverts and rent.</li>
<li><strong>Profit:</strong> income minus costs. A positive number is profit; negative is a loss.</li>
<li><strong>Cash flow:</strong> the timing of money coming in and going out. You can be profitable on paper and still run out of cash if customers pay late or money is tied up in unsold stock.</li>
</ul>
<h3>Separate business and personal money</h3>
<p>Use a separate mobile money line or bank account for the business if you can. If not, write down every business movement and pay yourself a fixed amount regularly, like a salary. Mixing the two makes it impossible to know the truth.</p>
<h3>The simple cashbook</h3>
<p>A <strong>cashbook</strong> lists every sale and every expense in date order. Use a notebook or a spreadsheet with these columns:</p>
<pre>Date | Description | Money in (UGX) | Money out (UGX) | Balance | Payment method</pre>
<ol>
<li>Record each sale on the day it happens, with the amount received.</li>
<li>Record each purchase or expense, with the amount paid.</li>
<li>Keep receipts, screenshots of confirmations and invoices in one folder.</li>
<li>At the end of each week, add up money in and money out.</li>
<li>Compare the balance in the book with your real cash and mobile money balance.</li>
</ol>
<h3>Calculating profit</h3>
<p>Example for one month:</p>
<table>
<thead><tr><th>Item</th><th>UGX</th></tr></thead>
<tbody>
<tr><td>Sales</td><td>1,500,000</td></tr>
<tr><td>Cost of stock sold</td><td>900,000</td></tr>
<tr><td>Delivery and packaging</td><td>120,000</td></tr>
<tr><td>Airtime, data and mobile money fees</td><td>60,000</td></tr>
<tr><td>Adverts</td><td>80,000</td></tr>
<tr><td>Profit before your own pay</td><td>340,000</td></tr>
</tbody>
</table>
<p>Profit equals <code>1,500,000 - 900,000 - 120,000 - 60,000 - 80,000 = 340,000 UGX</code>. Decide how much to keep in the business for restocking and how much to take as pay.</p>
<h3>Cash flow in practice</h3>
<ul>
<li>Forecast the next three months: expected money in, money out, and the balance at the end of each month.</li>
<li>Keep a small cash reserve for slow weeks or surprises.</li>
<li>Ask for deposits and avoid giving credit unless you know the customer.</li>
<li>Do not spend all profit on new stock if old stock has not sold.</li>
<li>Pay close attention to unsold stock; it is money sitting on a shelf.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Create a cashbook in a spreadsheet with the columns above.</li>
<li>Enter ten sample transactions, including sales and expenses.</li>
<li>Calculate total money in, total money out and the profit.</li>
<li>Write what you would do if the balance were lower than expected.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Profit equals income minus costs, and cash flow is about timing.</li>
<li>Keep business and personal money separate and record every transaction.</li>
<li>Reconcile your book with real balances every week.</li>
<li>Keep a cash reserve and watch unsold stock.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'KPIs, spreadsheets and taxes awareness',
                    'minutes' => 17,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to pick a few key numbers to track, build a simple weekly dashboard in a spreadsheet and describe in general terms how taxes can affect a small business. What you measure improves; what you guess about stays a mystery.</p>
<h3>What is a KPI?</h3>
<p>A <strong>key performance indicator (KPI)</strong> is a number that tells you whether the business is doing well. Do not track everything: choose five or six. Good ones for an online seller are:</p>
<table>
<thead><tr><th>KPI</th><th>How to calculate</th><th>What it tells you</th></tr></thead>
<tbody>
<tr><td>Sales</td><td>Total money from customers in the period</td><td>Business size</td></tr>
<tr><td>Gross margin</td><td>(Sales minus cost of goods) divided by sales</td><td>Whether prices are healthy</td></tr>
<tr><td>Net profit</td><td>Sales minus all costs</td><td>What is left for you and for growth</td></tr>
<tr><td>Number of orders and average order value</td><td>Sales divided by orders</td><td>How much each customer spends</td></tr>
<tr><td>Conversion rate</td><td>Orders divided by enquiries or visitors</td><td>How well you turn interest into sales</td></tr>
<tr><td>Cost per customer acquired</td><td>Marketing spend divided by new customers</td><td>Whether marketing pays</td></tr>
<tr><td>Repeat rate</td><td>Returning customers divided by all customers</td><td>Loyalty and service quality</td></tr>
<tr><td>Return and complaint rate</td><td>Problems divided by orders</td><td>Quality of product and delivery</td></tr>
</tbody>
</table>
<h3>A simple spreadsheet dashboard</h3>
<ol>
<li>Create a sheet with one row for each week and columns for enquiries, orders, sales, costs and profit.</li>
<li>Add formulas, for example <code>=C2/B2</code> for conversion rate and <code>=D2-E2</code> for profit.</li>
<li>Use the <code>SUM</code> formula for totals and <code>AVERAGE</code> for averages.</li>
<li>Add a simple chart of weekly sales and profit.</li>
<li>Review it every Monday for five minutes and write one action for the week.</li>
</ol>
<p>Example: 80 enquiries led to 20 orders, so the conversion rate is 25 percent. If sales were 600,000 UGX, average order value is 30,000 UGX.</p>
<h3>Taxes awareness</h3>
<p>This is a general introduction and not tax advice. Rules differ by business type and size and they change, so confirm with the national tax authority or a qualified adviser.</p>
<ul>
<li>Businesses may need to register for a taxpayer number, and some may be required to register for taxes such as value added tax once they pass certain turnover levels.</li>
<li>Business income may be taxable. Records of income and expenses are what the authority will ask to see.</li>
<li>Local authorities may charge trading licence fees.</li>
<li>Mobile money and bank transfers leave a record, so honest books matter.</li>
<li>Keep invoices, receipts and the cashbook for several years, and follow the official rules on how long.</li>
<li>Set aside money regularly for tax obligations, so you are never surprised.</li>
</ul>
<p>The official website and offices of the tax authority provide guides and help desks for small businesses.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose five KPIs for your business and write the formula for each.</li>
<li>Build a four-week spreadsheet using sample numbers and the formulas above.</li>
<li>Calculate your conversion rate and average order value.</li>
<li>Visit the tax authority website and write down two questions you would ask an adviser.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Track five or six KPIs rather than everything.</li>
<li>A simple weekly spreadsheet turns records into decisions.</li>
<li>Tax rules depend on your situation and change, so confirm with the authority or an adviser.</li>
<li>Good records are the basis of both good management and tax compliance.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Scaling, automation, risks and ethics',
                    'minutes' => 17,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to grow a small online business sensibly, use simple automation to save time, and spot the main risks and ethical duties. Growth is exciting, but fast growth without systems often ends in confusion, unhappy customers and lost money.</p>
<h3>When are you ready to grow?</h3>
<p>Before you scale, ask: Do orders arrive steadily? Is each sale profitable? Can I deliver on time? Do customers come back? If the answer to any is no, fix it first. Growing a broken process only makes a bigger mess.</p>
<h3>Ways to grow</h3>
<ul>
<li><strong>Sell more to existing customers</strong> with bundles, new products or reminders.</li>
<li><strong>Reach new customers</strong> through new areas, another platform or small paid ads.</li>
<li><strong>Add products</strong> that fit the same customer, such as shoe polish for a shoe seller.</li>
<li><strong>Raise your prices</strong> when quality and demand allow.</li>
<li><strong>Improve operations</strong> to cut costs and mistakes.</li>
<li><strong>Add people or partners</strong> such as a packer, rider or a social media helper, and agree responsibilities in writing.</li>
</ul>
<h3>Simple automation</h3>
<p><strong>Automation</strong> means letting tools do repeated tasks. Start with free or low-cost options:</p>
<ol>
<li>WhatsApp Business greeting, away message and quick replies.</li>
<li>A shared order sheet or form that fills a spreadsheet automatically.</li>
<li>Scheduled social media posts.</li>
<li>Invoice and receipt templates.</li>
<li>Reminders for restocking and for follow-up messages.</li>
<li>Spreadsheet formulas for totals, profit and stock alerts.</li>
</ol>
<p>Automate only a process that already works by hand, and keep a personal touch so customers never feel ignored.</p>
<h3>Common risks</h3>
<table>
<thead><tr><th>Risk</th><th>How to reduce it</th></tr></thead>
<tbody>
<tr><td>Cash runs out</td><td>Cash reserve, deposits, careful stock buying</td></tr>
<tr><td>Supplier fails or raises prices</td><td>Have at least two suppliers</td></tr>
<tr><td>Account banned or hacked</td><td>Strong passwords, 2-step verification, own customer list</td></tr>
<tr><td>Fraud</td><td>Verify payments, never share codes</td></tr>
<tr><td>Delivery problems</td><td>Reliable partners, packaging, tracking and clear policy</td></tr>
<tr><td>Over-borrowing</td><td>Borrow only what you can repay from realistic sales; avoid predatory lenders</td></tr>
<tr><td>Rule changes</td><td>Follow official updates</td></tr>
</tbody>
</table>
<h3>Ethics</h3>
<p>Customers trust you with their money and personal details. Act honestly:</p>
<ul>
<li>Describe products truthfully and honour your prices and promises.</li>
<li>Do not sell fake or harmful goods or copy others' brands.</li>
<li>Protect customer data and respect their messaging choices.</li>
<li>Pay suppliers, staff and riders fairly and on time.</li>
<li>Do not use child labour or pressure sales.</li>
<li>Avoid schemes that promise easy riches; if an opportunity asks you to recruit others or pay first for guaranteed profits, it is likely a scam. Never treat this course as investment advice.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Answer the four readiness questions for your business honestly.</li>
<li>List three tasks you repeat daily and choose one to automate.</li>
<li>Write your top three risks and one action to reduce each.</li>
<li>Write a short code of conduct of five promises you make to customers.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Fix what is broken before you scale.</li>
<li>Automate repeated tasks that already work, and keep a human touch.</li>
<li>Plan for risks such as cash shortages, supplier failure, fraud and account loss.</li>
<li>Honesty and fairness protect your reputation and your customers.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Business plan template and your 30-day launch plan',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>After this lesson you will have a one-page business plan and a day-by-day 30-day launch plan for your online business. A plan does not guarantee success, but it forces you to answer the hard questions before you spend money.</p>
<h3>One-page business plan template</h3>
<p>Copy these headings into a document and answer each in two to four sentences. Use numbers where possible.</p>
<ol>
<li><strong>Business idea:</strong> what you sell, to whom and what problem you solve.</li>
<li><strong>Customers:</strong> who they are, where they live, how they pay and where you reach them online.</li>
<li><strong>Competition and difference:</strong> three competitors and what makes you different.</li>
<li><strong>Products and prices:</strong> your main products, cost per item, selling price and margin.</li>
<li><strong>Where you sell:</strong> your channel and how customers order and pay.</li>
<li><strong>Delivery and suppliers:</strong> how you get stock and how you deliver.</li>
<li><strong>Marketing plan:</strong> your content, offers and how you will build a consent-based list.</li>
<li><strong>Start-up costs and monthly costs:</strong> a list in UGX, and the break-even number of sales.</li>
<li><strong>Money:</strong> how you will fund the start from your own savings or other legitimate sources, and a rough three-month cash flow forecast.</li>
<li><strong>Legal and records:</strong> registration, tax and licence checks you must do, and how you will keep books.</li>
<li><strong>Risks:</strong> your top three risks and your responses.</li>
<li><strong>Goals:</strong> sales, profit and customers you want after 3 and 6 months.</li>
</ol>
<h3>The 30-day launch plan</h3>
<table>
<thead><tr><th>Days</th><th>Focus</th><th>Tasks</th></tr></thead>
<tbody>
<tr><td>1-3</td><td>Decide</td><td>Choose product and niche, talk to ten customers, write the one-page plan</td></tr>
<tr><td>4-7</td><td>Costs and prices</td><td>List costs, set prices, calculate margin and break-even, check legal basics</td></tr>
<tr><td>8-12</td><td>Set up</td><td>Create the WhatsApp Business profile and social page, take photos, write descriptions, build the catalogue</td></tr>
<tr><td>13-15</td><td>Policies and payments</td><td>Write delivery, return and payment policies, set up mobile money, create invoice and receipt templates</td></tr>
<tr><td>16-18</td><td>Prepare operations</td><td>Buy a small first stock, arrange packaging and delivery partners, start the stock sheet and cashbook</td></tr>
<tr><td>19-21</td><td>Soft launch</td><td>Sell to friends, family and neighbours, ask for honest feedback and fix problems</td></tr>
<tr><td>22-26</td><td>Public launch</td><td>Post content daily, announce in groups, test a small ad, collect first reviews and consented contacts</td></tr>
<tr><td>27-29</td><td>Review</td><td>Calculate sales, profit and KPIs, list what worked and what to change</td></tr>
<tr><td>30</td><td>Plan the next 30 days</td><td>Set goals, adjust prices or products, restock, and update your plan</td></tr>
</tbody>
</table>
<h3>Rules for the first month</h3>
<ul>
<li>Start small and spend only money you can afford to lose.</li>
<li>Record every shilling from day one.</li>
<li>Reply to every message promptly.</li>
<li>Ask for feedback and change what customers tell you needs changing.</li>
<li>Do not borrow for stock until you know the product sells.</li>
</ul>
<h3>Capstone</h3>
<p>Complete your full one-page plan, calculation of break-even, a cashbook with at least 10 sample entries, a product catalogue of three items with photos and descriptions, your policies, and the 30-day plan with dates. Ask a friend or mentor to read it and note the weakest assumption, then improve it.</p>
<h3>Try it yourself</h3>
<ol>
<li>Fill in the one-page template for your business.</li>
<li>Copy the 30-day table and add real dates and your own names for each task.</li>
<li>Put the first week's tasks in your phone calendar with reminders.</li>
<li>Share your plan with one person and ask them to challenge it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A one-page plan answers who, what, how much, how and what could go wrong.</li>
<li>The 30-day plan moves from research to setup to a soft launch, a public launch and review.</li>
<li>Start small, keep records and ask for feedback.</li>
<li>This course is general education, not investment, legal or tax advice.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is profit?
- Money received from customers
* Income minus costs
- The money in your phone
- The cost of stock only
= Profit is what remains after all costs are subtracted from income.

Q: A shop earns 1,000,000 UGX in sales and spends 700,000 UGX on stock and 150,000 UGX on other costs. What is its profit?
- 300,000 UGX
- 850,000 UGX
* 150,000 UGX
- 1,850,000 UGX
= 1,000,000 minus 700,000 minus 150,000 equals 150,000 UGX.

Q: Why can a profitable business still run out of cash?
* Customers may pay late or money may be tied up in unsold stock
- Profit always arrives instantly
- Cash flow does not matter
= Cash flow is about timing. Watching it prevents sudden shortages.

Q: 80 enquiries lead to 20 orders. What is the conversion rate?
- 4 percent
- 80 percent
* 25 percent
= Orders divided by enquiries is 20 divided by 80, which is 25 percent.

Q: Which statement about taxes is best?
- Small online businesses never need to think about taxes.
* Rules depend on your situation and change, so check with the tax authority or an adviser and keep good records.
- Records are not needed if you only use mobile money.
= Mobile money leaves a trail and good books help you meet your obligations.

Q: Which is a good approach to automation?
- Automate a process that does not yet work.
* Automate repeated tasks that already work and keep a personal touch
- Remove all customer contact.
= Automation should save time while keeping service quality.

Q: An opportunity promises guaranteed high profits if you pay a fee and recruit others. What is the best response?
- Join quickly before it ends.
* Treat it as a likely scam and do not pay
- Ask your friends for loans so you can join.
= Pay-first, recruit-others schemes are classic scams. This course gives no investment advice.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which business model means you produce the item you sell?
- Agent or commission
- Dropshipping
- Marketplace listing
* Make and sell
= Producing the product yourself, such as handmade bags, is the make-and-sell model.

Q: Which factor matters most in East African online selling?
- Nobody uses mobile money.
* Trust, mobile money and reliable delivery
- Only big websites sell.
- Addresses are always exact.
= Sellers must plan for payment, proof of honesty and delivery in places with informal addresses.

Q: Which are good ways to test demand before buying big stock? (choose all that apply)
* Post a photo and price and see who asks
* Take small pre-orders
- Spend all savings on a big first order
- Assume people will buy
= Small tests reduce risk before you commit money.

Q: Fixed costs are 300,000 UGX per month and gross profit per item is 6,000 UGX. What is the break-even quantity?
- 6 items
* 50 items
- 300 items
- 1,800 items
= 300,000 divided by 6,000 equals 50 items per month.

Q: An item costs 10,000 UGX and sells for 15,000 UGX. What is the gross margin?
- 50 percent
- 5,000 percent
* 33.3 percent
- 66.7 percent
= Gross profit is 5,000, and 5,000 divided by the price 15,000 is 33.3 percent. Markup would be 50 percent.

Q: What is a niche?
- A cheap product
* A small, specific group of customers that you serve very well
- A tax rule
= A niche helps you avoid competing with everyone on price.

Q: Which is the best starting channel for a seller with little money?
* WhatsApp Business plus a social page
- A custom website with its own payment system
- A large rented shop
- Television advertising
= Start simple, learn, then grow in stages.

Q: Why should you collect your own customer list?
- It is a legal requirement for all sellers.
* You are not dependent on one platform that could ban you or change rules
- Because it is free to sell.
= Your list lets you reach customers elsewhere if a platform changes.

Q: Which makes a good product photo? (choose all that apply)
* Daylight and a plain background
* Several angles including one showing size
- Heavy filters that change colours
- Pictures copied from other sellers
= Honest, clear, original photos reduce returns and build trust.

Q: What should a good product description include?
- Only the product name
- The seller's life story
* Name, price, key details, condition, stock and how to order
- Only emojis
= Customers need enough facts to decide without asking many questions.

Q: What does a returns policy tell customers?
* What can be returned, within what time, and how it is refunded or replaced
- Your phone contacts
- How to delete the page
= A clear returns policy prevents arguments and builds trust.

Q: Which statement about legal and tax rules is best?
- They are the same for all products.
- They never apply to online sellers.
* They depend on what you sell and change over time, so check official sources or ask an adviser
= This course gives general education, not legal or tax advice.

Q: What does a payment gateway do?
- Delivers goods
- Prints receipts
- Writes product descriptions
* Connects an online shop to payment methods such as mobile money and cards
= It processes payments securely and passes the money to the seller, usually for a fee.

Q: You receive a screenshot that claims to prove payment. What is the safest action?
- Send the goods at once.
* Confirm in your own account or via the official confirmation first
- Ask for another screenshot only.
= Fake screenshots are a common scam.

Q: Which of these should you never share? (choose all that apply)
* Your mobile money PIN
* One-time codes
- Your product prices
- Your delivery areas
= Anyone asking for PINs or codes is likely a fraudster.

Q: What does a receipt show?
- That a payment is requested
* That a payment has been made
- A delivery route
= An invoice asks for payment; a receipt proves it.

Q: What is a good first reaction to a customer's complaint?
* Listen, apologise for the trouble, ask for evidence and check your records
- Argue that the customer is wrong
- Delete their message
= A calm, fair response often keeps the customer.

Q: How is closing stock calculated?
- Opening stock plus sold
- Added plus sold
* Opening stock plus added minus sold minus damaged
- Opening stock minus added
= Compare the result with a real count to find missing items.

Q: Which is the best way to test paid ads?
- Spend a month of rent on one advert.
* Choose a product that sells, use a small daily budget for a few days and measure cost per order
- Copy any advert and run it for free
- Run ads with no goal
= Small tests with measurement let you stop what fails and repeat what pays.

Q: You spent 30,000 UGX on ads and received 5 orders. What is your cost per order?
- 150,000 UGX
- 35,000 UGX
* 6,000 UGX
- 25,000 UGX
= 30,000 divided by 5 equals 6,000 UGX. Compare it with the profit per order.

Q: Which is the correct way to add customers to a promotion list?
- Add everyone in your contacts
- Buy numbers
* Ask clearly, keep proof and make it easy to stop
= Consent-based marketing protects customers and your business.

Q: Why is a repeat customer valuable?
- They never complain.
* They trust you, cost less to win and often tell others
- They pay double.
= Keeping customers is cheaper than finding new ones.

Q: What is cash flow?
- Total profit for the year
* The timing of money coming in and going out
- The cost of stock only
- The number of customers
= A business can be profitable on paper and still run out of cash.

Q: In a month, sales are 800,000 UGX, stock sold cost 500,000 UGX, and other costs are 120,000 UGX. What is the profit?
- 300,000 UGX
* 180,000 UGX
- 120,000 UGX
- 1,420,000 UGX
= 800,000 minus 500,000 minus 120,000 equals 180,000 UGX.

Q: Which is the best approach to automation?
* Automate repeated tasks that already work by hand
- Automate everything immediately
- Never use tools
= Automate working processes and keep a personal touch.

Q: Which is a sign of a likely scam opportunity?
- A clear explanation of costs and risks
* A promise of guaranteed high profits if you pay first and recruit others
- A written receipt for the purchase
= Be careful with schemes that promise easy riches. This course does not give investment advice.

Q: Which item belongs in a 30-day launch plan before a public launch?
- Running paid ads before you have any product
* A soft launch with friends and neighbours to collect feedback
- Borrowing money for large stock
= A soft launch lets you fix problems before the public sees them.

Q: Which should be tracked as KPIs for an online seller? (choose all that apply)
* Sales and profit
* Conversion rate and repeat rate
- The colour of your logo
- The number of emoji you used
= Choose a few numbers that show whether the business is healthy.
QUIZ,
];
