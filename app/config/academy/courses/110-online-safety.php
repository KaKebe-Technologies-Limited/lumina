<?php
return [
    'slug'        => 'online-safety',
    'title'       => 'Online Safety and Cybersecurity Basics',
    'category'    => 'security',
    'level'       => 'beginner',
    'icon'        => 'shield',
    'sort'        => 110,
    'summary'     => 'Protect your money, accounts, phone and reputation online. Learn to spot scams, build strong passwords, guard your privacy and know where to get help.',
    'description' => <<<'HTML'
<p>More of life now happens on phones and computers: mobile money, school and work, family chats, job hunting and business. This also means more people try to trick, steal from or harass others online. Most attacks do not need clever hacking. They need you to click, reply or share a secret by mistake.</p>
<p>This course teaches the habits that stop most problems. You will learn how scams and viruses work, how to protect your accounts and phone, how to control what others can see about you, and what to do if something goes wrong. You will also learn how to be kind and safe in online spaces, and how to help children and young people.</p>
<p>No technical background is needed. Every lesson ends with a short activity you can do on your own phone or computer.</p>
HTML,
    'outcomes'    => [
        'Explain the main online threats and what you could lose to them',
        'Recognise common mobile money, job, scholarship, romance and investment scams',
        'Create strong passwords and passphrases and switch on two-step verification',
        'Identify phishing messages and fake websites before you click',
        'Protect your phone, your data and your privacy settings on social media',
        'Respond calmly and correctly if an account is hacked or money is lost',
        'Support yourself and others against bullying, harassment and image abuse',
        'Build and keep a personal security checklist',
    ],
    'requirements'=> [
        'No experience needed',
        'A smartphone or a computer with internet (for the practice activities)',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Google Safety Center', 'url' => 'https://safety.google'],
        ['title' => 'Have I Been Pwned (check if your email was in a data breach)', 'url' => 'https://haveibeenpwned.com'],
        ['title' => 'Microsoft Support: Security and privacy', 'url' => 'https://support.microsoft.com'],
    ],
    'modules' => [
        // ------------------------------------------------------------------ MODULE 1
        [
            'title'   => 'Module 1: Why online safety matters',
            'summary' => 'Understand the threats, who is behind them, what you can lose, and how your digital footprint works.',
            'lessons' => [
                [
                    'title'   => 'The online threats around us',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to name the most common online threats and explain how each one reaches an ordinary person. This matters because you cannot defend against something you cannot recognise.</p>
<h3>The main kinds of threat</h3>
<p>Most online harm falls into a few groups. Learn these names, because the rest of the course builds on them.</p>
<table>
<thead><tr><th>Threat</th><th>What it is</th><th>Simple example</th></tr></thead>
<tbody>
<tr><td>Scam (fraud)</td><td>Someone lies to you to get money or goods</td><td>A message says you won a prize but must pay a "release fee"</td></tr>
<tr><td>Phishing</td><td>A fake message that tricks you into giving a password, PIN or code</td><td>An SMS says your account is blocked and asks you to click a link</td></tr>
<tr><td>Malware</td><td>Harmful software (a virus, spyware or ransomware) that runs on your device</td><td>A "free" app that secretly reads your messages</td></tr>
<tr><td>Account takeover</td><td>A stranger logs into your account</td><td>Your WhatsApp or Facebook starts sending money requests to your friends</td></tr>
<tr><td>Identity theft</td><td>Someone uses your name or ID details to act as you</td><td>A loan is taken using your details</td></tr>
<tr><td>Harassment and abuse</td><td>Bullying, threats or shaming online</td><td>Nasty comments, or private photos shared without consent</td></tr>
</tbody>
</table>
<h3>How threats reach you</h3>
<p>Attackers use the channels you already use every day: SMS, phone calls, WhatsApp, Facebook, email, fake websites and apps. A message can look like it comes from a friend, a bank, a mobile money company or an employer. The trick is almost always to make you feel <strong>excited</strong> (you won something), <strong>afraid</strong> (your account will close) or <strong>rushed</strong> (reply in 10 minutes).</p>
<h3>Why "I have nothing to steal" is wrong</h3>
<p>Many people think they are too poor or too unknown to be a target. In fact attackers send the same message to thousands of people at once and only need a few to respond. Your phone number, your mobile money wallet, your social media account and your contacts all have value. A hacked account is also used to cheat your friends and family.</p>
<h3>The good news</h3>
<p>You do not need to be a computer expert. A small number of habits stop most attacks: never share codes or PINs, use strong and different passwords, switch on two-step verification, slow down and check before you click, and keep your phone updated. You will practise each of these in this course.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open a notebook or a notes app on your phone.</li>
<li>Write down three suspicious messages you or someone you know has received (SMS, WhatsApp, email or a call).</li>
<li>Next to each one, write which threat from the table it was, and what the sender wanted: money, a password, a code or a click.</li>
<li>Write one feeling each message tried to create: excitement, fear or rush.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The common threats are scams, phishing, malware, account takeover, identity theft and harassment.</li>
<li>Attackers use SMS, calls, WhatsApp, social media, email, fake sites and apps.</li>
<li>They try to create excitement, fear or a rush so you act without thinking.</li>
<li>Everyone is a target, because attackers send the same trick to many people.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Who attacks, and why',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn who is behind online attacks and what they want. Knowing their motives helps you predict their tricks and stay calm when you meet them.</p>
<h3>The most common reason: money</h3>
<p>Most online attackers are simply trying to make money. Some work alone, and some belong to organised groups that run call centres and use many phone numbers and fake profiles. They may be in another country or even in your own town. They are not clever geniuses. They repeat the same few tricks because those tricks work often enough.</p>
<h3>Other reasons people attack</h3>
<ul>
<li><strong>Stealing information.</strong> Your personal details, ID numbers and contact lists can be sold or used to commit fraud.</li>
<li><strong>Using your account.</strong> A hacked social media or email account can be used to spread scams to your friends, who trust you.</li>
<li><strong>Revenge or jealousy.</strong> A former partner, classmate or colleague may try to read your messages or embarrass you.</li>
<li><strong>Control and harassment.</strong> Some people bully, threaten or shame others for power or fun.</li>
<li><strong>Curiosity or showing off.</strong> Some young people hack for the thrill. This is still a crime.</li>
</ul>
<h3>Insiders and people you know</h3>
<p>Not every attacker is a stranger. A friend who knows your phone password, a relative who borrows your SIM card, or a shop attendant who sees your PIN can all cause harm, sometimes by accident. Protecting yourself includes protecting your passwords and phone from people close to you.</p>
<h3>What attackers look for</h3>
<table>
<thead><tr><th>They look for</th><th>Because</th></tr></thead>
<tbody>
<tr><td>Easy targets</td><td>Weak passwords, no updates and trusting habits save them effort</td></tr>
<tr><td>Urgent emotions</td><td>A worried or excited person checks less</td></tr>
<tr><td>Public information</td><td>Your birthday, school, workplace and photos help them sound believable</td></tr>
<tr><td>Trusted names</td><td>A message "from" a bank, a boss or a friend is more likely to be believed</td></tr>
</tbody>
</table>
<h3>Think like a defender</h3>
<p>Attackers want the <em>easiest</em> route. If your accounts need a code that only you receive, and you never share that code, most attacks simply fail and the attacker moves on to someone easier. You do not need to be perfect. You need to be harder to trick than the next person.</p>
<h3>Try it yourself</h3>
<ol>
<li>Think about your own phone, accounts and mobile money wallet.</li>
<li>Write the question: "If I were a criminal, what is the easiest way to get into my accounts?"</li>
<li>List three answers, for example: a simple password, a phone with no screen lock, a friend who knows my PIN.</li>
<li>Choose one and fix it today (change the password, set a screen lock, or change the PIN).</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Most attackers want money, and they repeat simple tricks that work.</li>
<li>Your accounts can be used to cheat your friends and family.</li>
<li>Some risks come from people close to you, not only strangers.</li>
<li>Being harder to trick than others is a strong defence.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'What is at risk: money, identity, reputation, accounts',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to list what you could lose in an attack and rank what matters most to protect. It is easier to keep motivated about safety when you can see what is at stake.</p>
<h3>Money</h3>
<p>Money is the most direct risk: mobile money and bank balances, savings group funds, school fees, business takings and loan accounts. Losses can happen in one minute and are hard to recover. A small business owner who loses a week of takings may not be able to restock.</p>
<h3>Identity</h3>
<p>Your identity is the set of details that prove who you are: full name, date of birth, National ID number, phone number, SIM registration, passport details and photos. If a criminal gets enough of these, they can pretend to be you to open accounts, take loans, replace your SIM card or cheat other people in your name. Sorting out identity theft can take months.</p>
<h3>Reputation</h3>
<p>What appears online about you can follow you for years. A hacked account that posts offensive content, a private photo shared without your consent, or a false rumour can affect friendships, school places, scholarships and job offers. Employers and colleges often search your name before they decide.</p>
<h3>Accounts</h3>
<p>Your email account is the most valuable of all. Most other accounts use it for "forgot password" messages, so a person who controls your email can reset almost everything else. Your phone number works in a similar way, because codes arrive there. Protect email and phone first.</p>
<h3>Other things at risk</h3>
<ul>
<li><strong>Your files and photos.</strong> Ransomware can lock them until you pay.</li>
<li><strong>Your privacy.</strong> Spyware can read messages, track your location or watch you through the camera.</li>
<li><strong>Your safety.</strong> Sharing your location or daily routine can put you at physical risk.</li>
<li><strong>Your relationships.</strong> Scams and abuse can cause shame, fear and broken trust.</li>
</ul>
<h3>Ranking what to protect first</h3>
<ol>
<li>Your phone and SIM card (they receive your codes).</li>
<li>Your main email account.</li>
<li>Mobile money and banking.</li>
<li>Social media and messaging accounts.</li>
<li>Photos, documents and files.</li>
</ol>
<h3>Common mistakes</h3>
<ul>
<li>Using the same password for email and social media.</li>
<li>Thinking only rich people lose money to scams.</li>
<li>Keeping a copy of the National ID in a public chat group.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Draw a table with four columns: Money, Identity, Reputation, Accounts.</li>
<li>Under each column, write the specific things you own (for example "MTN wallet", "Facebook page", "NIN photo on my phone").</li>
<li>Circle the three items you would hate to lose most.</li>
<li>Write one protection step for each circled item. Keep this list for the checklist in Module 5.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Money, identity, reputation, accounts, files and even physical safety are at risk.</li>
<li>Your email account and phone number unlock almost everything else.</li>
<li>Protect your phone, SIM and email first.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Mobile money scams and your digital footprint',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn the most common mobile money and online banking tricks in East Africa, and how your digital footprint gives scammers information. You will be able to refuse these tricks calmly.</p>
<h3>Common mobile money tricks</h3>
<ul>
<li><strong>"Sent by mistake."</strong> A caller or message says money was sent to you by mistake and asks you to send it back. Often the confirmation message is fake, or the real money is reversed later and you lose your own.</li>
<li><strong>The fake agent or fake support call.</strong> Someone says they work for the mobile money company and your account has a "problem". They ask for your PIN or a code. Real companies never ask for your PIN.</li>
<li><strong>Prize and promotion messages.</strong> "You won 5,000,000 shillings. Send 50,000 to release it." No real prize needs you to pay first.</li>
<li><strong>Reversal and verification requests.</strong> They ask you to dial a code or "confirm" a transaction. Dialling unknown codes can send your money away.</li>
<li><strong>SIM swap.</strong> A criminal convinces the network to move your number to their SIM and then receives your codes. See Module 4.</li>
<li><strong>The relative in trouble.</strong> A message from a "new number" claims to be a family member who needs urgent money.</li>
</ul>
<h3>Rules that stop these tricks</h3>
<ol>
<li>Never share your PIN, password or one-time code (OTP) with anyone, even staff.</li>
<li>Do not send money to receive money.</li>
<li>If a message makes you rush, stop. Hang up and call the official number you already know, or visit an official service point.</li>
<li>Check the name shown before you confirm any transfer.</li>
<li>Keep your own records of transactions and report problems quickly through the official channel.</li>
</ol>
<h3>Your digital footprint</h3>
<p>Your <strong>digital footprint</strong> is the trail of information about you online: posts, photos, comments, accounts, tags, school and work details, and data collected by apps and websites. Some you leave on purpose (a post), and some is collected without you noticing (location history, browsing habits).</p>
<p>Scammers use your footprint to sound believable. If your birthday, school, parents' names and workplace are public, a criminal can answer "security questions" or pretend to know you.</p>
<h3>Types of footprint</h3>
<table>
<thead><tr><th>Type</th><th>Examples</th></tr></thead>
<tbody>
<tr><td>Active</td><td>Posts, comments, photos you upload, forms you fill in</td></tr>
<tr><td>Passive</td><td>Location history, cookies, apps recording how you use them</td></tr>
<tr><td>Shared by others</td><td>Photos where friends tag you, group chats, news mentions</td></tr>
</tbody>
</table>
<h3>Try it yourself</h3>
<ol>
<li>Search your full name in quotation marks in a search engine.</li>
<li>Note what appears: profiles, photos, school or work pages.</li>
<li>Decide if anything shows more than you want strangers to know (birthday, phone number, home area).</li>
<li>Write down one thing to remove or make private. You will learn how in Module 4.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Real mobile money companies never ask for your PIN or OTP.</li>
<li>Never pay money in order to receive a prize, loan or job.</li>
<li>If pressured, stop and call the official number yourself.</li>
<li>Your digital footprint is what scammers use to make lies believable.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A caller says he is from your mobile money company and needs your PIN to "fix a problem". What should you do?
* Refuse, end the call and contact the official number or service point yourself
- Give the PIN, because he knows your name
- Give only half of the PIN
- Send him a small amount first to prove it is you
= Real companies never ask for your PIN. Hanging up and contacting the official channel yourself is the safe response.

Q: Which feelings do scammers most often try to create so you act without thinking? (choose all that apply)
* Excitement
* Fear
* Rush or urgency
- Boredom
= Excitement, fear and urgency stop you from checking. Slow down when you feel any of them.

Q: Which account is usually the most valuable to protect first, because it can reset many others?
- Your gaming account
* Your main email account
- A shopping app you rarely use
- Your music playlist
= Most "forgot password" messages go to email, so whoever controls your email can often take over your other accounts.

Q: A message says you won a big prize but must send a "release fee" first. This is most likely:
- A normal promotion
- A bank procedure
* A scam, because real prizes never require you to pay first
- A tax rule
= Asking you to pay to receive money is a classic advance-fee scam.

Q: Which of the following is part of your digital footprint? (choose all that apply)
* Photos your friends tag you in
* Posts you wrote last year
* Location history collected by apps
- A paper notebook kept at home
= Your footprint includes active and passive data and what others post about you. A paper notebook is offline.

Q: Someone you do not know says they sent you money by mistake and begs you to send it back. What is the best step?
- Send it back at once so they are not upset
- Send half back
* Do not send anything; check your own balance through official channels and report it to the provider
- Share your PIN so they can fix it
= The confirmation message can be fake, or a later reversal can leave you out of pocket. Verify through the official channel.

Q: Why do attackers like public details such as your birthday, school and workplace?
* They make fake messages and security-question guesses more believable
- They make your phone run slower
- They are required by law
- They cancel your data bundle
= Public details help criminals pretend to know you and guess answers to account questions.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 2
        [
            'title'   => 'Module 2: Passwords and accounts',
            'summary' => 'Build strong passwords, use a password manager, add two-step verification and stay safe on shared and public networks.',
            'lessons' => [
                [
                    'title'   => 'Strong passwords and passphrases',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to create passwords that are hard to guess but easy for you to remember. A weak password is like a door with a toy lock: it keeps out honest people only.</p>
<h3>How passwords are broken</h3>
<p>Criminals rarely type guesses by hand. Programs try millions of common passwords, names, dates and word patterns per second. They also use passwords leaked from other websites. If you used the same password on two sites, a leak from the weaker site opens the stronger one.</p>
<h3>What makes a password weak</h3>
<ul>
<li>Short (under 10 characters).</li>
<li>Names, birthdays, phone numbers, football teams or "password", "123456", "Uganda2024".</li>
<li>One small change on an old password, for example "Mary1", "Mary2".</li>
<li>The same password used everywhere.</li>
</ul>
<h3>Passphrases: long and memorable</h3>
<p>Length matters more than tricky symbols. A <strong>passphrase</strong> is several random words joined together. It is long, so it is hard to break, but easy to remember because you can picture it.</p>
<pre><code>weak:    P@ssw0rd1
better:  riverbus-candle-green-mango-drum
</code></pre>
<p>The words must be random and not a famous sentence or a quote. Four or five unrelated words, with a number or symbol if you like, is a strong start. For important accounts, make it 16 characters or more.</p>
<h3>Rules to follow</h3>
<ol>
<li>Use a <strong>different</strong> password for every important account. Email, mobile money and banking must never share one.</li>
<li>Make passwords at least 12 characters, longer for email.</li>
<li>Do not use personal information that others can find on your profile.</li>
<li>Never send a password by SMS, WhatsApp or email.</li>
<li>Change a password straight away if you think it leaked or if a website reports a breach.</li>
</ol>
<h3>PINs</h3>
<p>PINs are short, so protect them differently. Avoid 1234, 0000, your birth year or the same PIN for your phone lock and mobile money. Cover the keypad when you type. Do not write your PIN on the back of your card or phone.</p>
<h3>Try it yourself</h3>
<ol>
<li>Pick five random words by looking at five different objects around you and writing their names (for example door, bottle, stone, chair, rain).</li>
<li>Join them with hyphens and add a number: <code>door-bottle-stone-chair-rain-7</code>. Do not use this exact example.</li>
<li>Practise typing it three times without looking.</li>
<li>Use it to replace the password of your most important account (usually your email).</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Length beats complexity: a long passphrase is strong and memorable.</li>
<li>Never reuse passwords across important accounts.</li>
<li>Avoid names, dates, phone numbers and simple patterns.</li>
<li>Protect PINs: no obvious numbers, cover the keypad, never share.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Password managers and account recovery',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>You will learn how a password manager lets you use a different strong password everywhere without remembering them all, and how to prepare so you can get back into an account if you lose access.</p>
<h3>Why a password manager helps</h3>
<p>Nobody can remember 40 different strong passwords. So people reuse a few, which is dangerous. A <strong>password manager</strong> is an app that stores your passwords in an encrypted (scrambled) vault. You remember only one strong master passphrase. The manager creates and fills in a unique password for each site.</p>
<h3>Where to find one</h3>
<ul>
<li>Google Password Manager is built into Chrome and Android. You manage it at your Google account under Security.</li>
<li>Apple Passwords (iCloud Keychain) is built into iPhone and Mac.</li>
<li>Dedicated apps also exist. Choose one that is well known and has good reviews, and download it only from the official store.</li>
</ul>
<h3>How to start</h3>
<ol>
<li>Choose a manager and protect your account with a long master passphrase that you have never used elsewhere.</li>
<li>Turn on two-step verification for that account (next lesson).</li>
<li>Save passwords as you sign in. Let the manager generate new ones when you register or change a password.</li>
<li>Begin with your most important accounts: email first, then mobile money and banking apps that allow it, then social media.</li>
</ol>
<p>Never save your mobile money PIN as a note in your phone, and do not store passwords in a plain document called "Passwords". That is easy for a thief to find.</p>
<h3>Account recovery</h3>
<p>Sooner or later you may forget a password or lose a phone. <strong>Recovery options</strong> let you prove you own the account. Set them up before you need them:</p>
<ul>
<li>A recovery phone number that you control.</li>
<li>A recovery email (a second email address, also protected).</li>
<li>Backup codes. Many services let you download or print one-time codes. Keep them in a safe place, not in your photo gallery.</li>
<li>A trusted contact, where the service offers it.</li>
</ul>
<h3>Security questions</h3>
<p>If a site asks "your mother's maiden name" or "first school", the real answers can be found by others. Treat the answer as another password: invent a false answer and store it in your password manager.</p>
<h3>Common mistakes</h3>
<ul>
<li>Forgetting the master passphrase. Write it once on paper and keep it somewhere safe at home.</li>
<li>Letting a friend set up a recovery number that is not yours.</li>
<li>Giving recovery codes to a "support agent". No genuine support agent needs them.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Open your Google, Apple or Microsoft account security page on your phone or computer.</li>
<li>Check the recovery phone number and recovery email. Correct them if needed.</li>
<li>Find the saved passwords list (on Chrome or Android: Settings, then Google, then Password Manager; on iPhone: Settings, then Passwords).</li>
<li>Pick one weak or repeated password from the list and change it to a new strong one.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A password manager lets you have unique strong passwords for every site.</li>
<li>Protect the manager with a long master passphrase and two-step verification.</li>
<li>Set up recovery phone, recovery email and backup codes in advance.</li>
<li>Treat security-question answers like passwords.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Two-step verification and never sharing codes',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to switch on two-step verification (also called 2FA or two-factor authentication) and you will know why a one-time code must never be shared. This is the single most effective thing you can do to protect an account.</p>
<h3>What it is</h3>
<p>With a password alone, anyone who learns it can log in. With <strong>two-step verification</strong> you need two things: something you <em>know</em> (the password) and something you <em>have</em> (your phone or an app). A thief with only your password gets stuck at the second step.</p>
<h3>Types of second step</h3>
<table>
<thead><tr><th>Type</th><th>How it works</th><th>Strength</th></tr></thead>
<tbody>
<tr><td>Authenticator app</td><td>An app on your phone shows a code that changes every 30 seconds</td><td>Strong</td></tr>
<tr><td>Prompt on your phone</td><td>You tap "Yes, it is me" on a notification</td><td>Strong, if you only approve your own sign-ins</td></tr>
<tr><td>SMS code</td><td>A code is sent to your number by text</td><td>Good, but weaker if SIM swap happens</td></tr>
<tr><td>Security key</td><td>A small physical device you plug in or tap</td><td>Very strong</td></tr>
</tbody>
</table>
<h3>How to turn it on</h3>
<ol>
<li>Open the account's <strong>Security</strong> or <strong>Privacy and security</strong> settings (the exact name differs).</li>
<li>Choose <strong>2-step verification</strong>, <strong>Two-factor authentication</strong> or similar.</li>
<li>Pick an authenticator app or phone prompt if available; otherwise use SMS.</li>
<li>Save the backup codes in a safe place.</li>
<li>Test it by signing out and signing in again.</li>
</ol>
<p>WhatsApp has its own feature called <strong>Two-step verification</strong> under Settings, Account. It asks for a six-digit PIN when you register your number again. Turn it on, and never share that PIN.</p>
<h3>The one-time code (OTP) rule</h3>
<p>A one-time password (OTP) is a code sent to you for a single action: logging in, paying, or registering WhatsApp. Scammers often ask for it. They may say "I sent a code to your phone by mistake, please read it to me." This is the exact moment they are trying to log into your account.</p>
<blockquote>No bank, mobile money company, employer, or friend should ever need your code. If someone asks, it is an attack.</blockquote>
<h3>Common mistakes</h3>
<ul>
<li>Approving a login prompt you did not start, "just to make it stop".</li>
<li>Reading a code from SMS to a caller.</li>
<li>Losing the phone with no backup codes saved.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Choose your email account (Gmail, Outlook or other).</li>
<li>Find its security settings and turn on two-step verification.</li>
<li>Download or write down the backup codes and put them in a safe place.</li>
<li>Do the same for one social media account and for WhatsApp two-step verification.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Two-step verification needs a password plus something you have.</li>
<li>Authenticator apps and phone prompts are stronger than SMS codes.</li>
<li>Keep backup codes safe and never share a one-time code with anyone.</li>
<li>If you receive a login prompt you did not start, deny it and change your password.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Wi-Fi, shared computers and public places',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn how to use Wi-Fi, cybercafe computers, school labs and other people's devices safely. Many people in our community go online on shared or borrowed devices, so these habits matter.</p>
<h3>Safer Wi-Fi at home</h3>
<ol>
<li>Change the default router password (the admin login) that comes on the sticker. Anyone can look up default ones.</li>
<li>Use a strong Wi-Fi password, with WPA2 or WPA3 security chosen in the router settings. Never leave the network open without a password.</li>
<li>Do not share the Wi-Fi password widely. Change it if you suspect strangers are using it.</li>
<li>Keep the router updated and switch off features you do not use.</li>
</ol>
<h3>Public Wi-Fi</h3>
<p>Free Wi-Fi in hotels, restaurants and markets is useful but not private. On an open network, a criminal nearby can try to read the traffic or create a <strong>fake hotspot</strong> with a believable name such as "Free_Hotel_WiFi".</p>
<ul>
<li>Ask staff for the exact network name and password.</li>
<li>Avoid banking, mobile money apps and shopping on public Wi-Fi. Use your mobile data instead.</li>
<li>Only visit websites that start with <code>https://</code> and show a padlock, but remember this alone does not prove a site is honest.</li>
<li>Turn off automatic connection to open networks on your phone.</li>
<li>Turn off file sharing, and turn Wi-Fi and Bluetooth off when you do not need them.</li>
</ul>
<h3>Shared and public computers</h3>
<p>Cybercafe, library and school computers may be used by dozens of people. Some have been set up to record what you type.</p>
<ol>
<li>Avoid logging into important accounts (email, banking) on them if you can.</li>
<li>If you must, use a private or incognito window (<code>Ctrl + Shift + N</code> in Chrome and Edge; <code>Ctrl + Shift + P</code> in Firefox).</li>
<li>Never tick "Remember me" or "Keep me signed in".</li>
<li>Do not save files, passwords or forms in the browser.</li>
<li>When finished, click <strong>Sign out</strong> on each site. Closing the tab is not enough. Then close the browser and delete downloaded files.</li>
<li>If you suspect the computer is unsafe, change your passwords later from your own trusted device.</li>
</ol>
<h3>Lending your phone</h3>
<p>If someone asks to borrow your phone, stay with them, and do not leave mobile money or banking apps open. Use guest mode if your phone has it, and use an app lock for sensitive apps.</p>
<h3>Common mistakes</h3>
<ul>
<li>Doing mobile money on café Wi-Fi.</li>
<li>Leaving email open on a school lab computer.</li>
<li>Connecting to any network named "free".</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Open your phone's Wi-Fi settings and look at the list of saved networks.</li>
<li>Remove the ones you no longer use, especially open networks.</li>
<li>Turn off any "connect automatically" or "join open networks" option.</li>
<li>On a computer, open a private window and practise signing in to a test account, then signing out properly.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Change the router admin password and use WPA2 or WPA3.</li>
<li>Avoid sensitive tasks on public Wi-Fi; use mobile data instead.</li>
<li>On shared computers use a private window, never tick "remember me", and sign out.</li>
<li>Do not leave your phone unlocked or apps open when lending it.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which password is the strongest?
- Uganda2024
- Mary@1998
* lantern-bicycle-ocean-pepper-hill-4
- Password123!
= Long, random, unrelated words make a strong passphrase. The others use common words, names or dates.

Q: Why is reusing one password on many sites dangerous?
- It makes the sites load slower
* A leak from one weak site lets criminals try the same password on your other accounts
- Websites forbid it
- It changes your phone number
= Criminals try leaked passwords on other services, so one leak can open many accounts.

Q: What does a password manager do?
- Uploads your passwords to social media
* Stores unique passwords in an encrypted vault, protected by one strong master passphrase
- Makes your internet faster
- Deletes old accounts automatically
= The manager creates and fills in a different strong password for each site so you only remember one.

Q: Someone calls and asks you to read out a code that just arrived by SMS. What should you do?
- Read it, since it was sent to your phone
- Read only the last three digits
* Refuse and end the call, because the code lets them into your account
- Forward the SMS to them
= One-time codes must never be shared. Scammers request them while they are logging in as you.

Q: Which are valid second steps for two-step verification? (choose all that apply)
* An authenticator app code
* A prompt you approve on your phone
- Your date of birth
- Your name
= A second step must be something you have and that an attacker cannot easily guess. Birth date and name are public knowledge.

Q: You must use a cybercafe computer to check email. Which is the safest approach?
- Tick "Remember me" so it is faster next time
* Use a private window, avoid saving anything, and sign out fully when done
- Leave the page open for the next person
- Email yourself your password
= Private mode, no saved data and proper sign-out reduce what the next user can find.

Q: Which is the safest choice for mobile money on a public hotel Wi-Fi?
- Use it, because the hotel is trustworthy
* Use your own mobile data instead
- Use it only after midnight
- Ask a stranger to try it first
= Public Wi-Fi may be watched or fake. Mobile data is safer for sensitive tasks.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 3
        [
            'title'   => 'Module 3: Scams, phishing and malware',
            'summary' => 'Recognise phishing and common scams, protect your devices from malware, check websites, and know what to do if you are hacked.',
            'lessons' => [
                [
                    'title'   => 'Phishing by email, SMS and WhatsApp',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>You will learn what phishing is and how to spot it in an email, an SMS or a WhatsApp message. Phishing is the most common way accounts are stolen, so this lesson protects you from the biggest risk.</p>
<h3>What phishing is</h3>
<p><strong>Phishing</strong> means a fake message pretending to come from a trusted person or organisation (a bank, a mobile money company, a school, an employer, a friend) to trick you into clicking a link, opening a file or giving information. The word comes from "fishing": the message is the bait.</p>
<h3>Warning signs</h3>
<ul>
<li><strong>Pressure.</strong> "Act in 24 hours or your account will be closed."</li>
<li><strong>Unexpected message.</strong> You did not apply, buy or request anything.</li>
<li><strong>A strange sender.</strong> The email address does not match the real organisation, for example <code>support@mtn-help-center.example</code> instead of the official domain.</li>
<li><strong>Greeting by "Dear customer".</strong> Real organisations often use your name, but this alone is not proof.</li>
<li><strong>Spelling or grammar errors</strong>, odd logos or strange formatting. Good scammers can still make clean copies, so do not rely on this alone.</li>
<li><strong>A link or attachment you must open.</strong> Especially files ending in .zip, .exe, or Office files asking you to "enable macros".</li>
<li><strong>A request for secrets.</strong> Passwords, PINs, OTPs, ID numbers or card details.</li>
</ul>
<h3>Smishing and WhatsApp scams</h3>
<p>Phishing by SMS is called <em>smishing</em>. By phone call it is called <em>vishing</em>. On WhatsApp and Facebook Messenger, you may get:</p>
<ul>
<li>A message from a friend's hacked account asking for money or a "verification code".</li>
<li>A link to join a "free data" or "job" group.</li>
<li>A fake voice note or screenshot "proving" a payment.</li>
</ul>
<h3>What to do with a suspicious message</h3>
<ol>
<li><strong>Stop.</strong> Do not click, reply or call back using details in the message.</li>
<li><strong>Check through another way.</strong> Call the person or organisation using a number you already know.</li>
<li><strong>Look at the link without opening it.</strong> On a computer, hover over it. On a phone, press and hold to preview it. Does the address really belong to the organisation?</li>
<li><strong>Report and delete.</strong> Use "Report spam" or "Report phishing" in your email app, and block the sender on WhatsApp.</li>
<li>If you already clicked or typed something, go to the lesson on being hacked below.</li>
</ol>
<h3>Common mistakes</h3>
<ul>
<li>Believing a message because it knows your name or phone number. Those details are easy to find.</li>
<li>Clicking "Unsubscribe" in a suspicious email, which can confirm your address is active.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Look at your spam folder or SMS messages for a suspicious message.</li>
<li>Without clicking, list the warning signs you see: pressure, strange sender, request for secrets.</li>
<li>Press and hold the link to see its real address, then close the preview.</li>
<li>Mark it as spam or phishing and block the sender.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Phishing is a fake message that tries to make you click or share secrets.</li>
<li>Pressure, a surprise, odd senders and requests for secrets are the main warning signs.</li>
<li>Check using a trusted contact method, never using details in the message.</li>
<li>Report, block and delete; do not reply.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Fake jobs, scholarships, romance and investment scams',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>This lesson explains the scams that target hopes: for a job, a place abroad, love or a quick profit. After it you will be able to recognise their patterns and say no, even when the offer feels like exactly what you need.</p>
<h3>Fake job offers</h3>
<p>Young people looking for work are a main target. Typical signs:</p>
<ul>
<li>An offer arrives for a job you never applied for, or after a very short chat on WhatsApp or Telegram.</li>
<li>The employer asks you to <strong>pay</strong> for training, uniforms, a medical test, a visa or "registration".</li>
<li>No proper interview and no official company email or website.</li>
<li>Very high pay for very easy work, such as "like and share videos".</li>
<li>Overseas jobs promised through an agent with no licence.</li>
</ul>
<p>A real employer does not charge you to give you a job. Always check the company independently before you share your documents, because identity documents are valuable to criminals.</p>
<h3>Fake scholarships and admission offers</h3>
<ul>
<li>A message says you have won a scholarship you never applied for.</li>
<li>You must pay an "processing" or "guarantee" fee.</li>
<li>The email comes from a free address (such as one ending in a free email service) instead of the school's own domain.</li>
</ul>
<p>Check on the official website of the school or programme, typed into your browser yourself, and ask your school or a trusted adult.</p>
<h3>Romance scams</h3>
<p>A stranger, often with attractive photos, builds a close relationship online over weeks. They say they love you, then an emergency appears: a sick relative, a stuck parcel or a plane ticket. They ask for money or gift cards, or ask you to receive and forward money for them. Warning signs: they refuse video calls, they quickly say "I love you", and they always have a reason they cannot meet. Never send money to someone you have not met in person.</p>
<h3>Investment and "double your money" scams</h3>
<ul>
<li>Promises of guaranteed or very high returns, for example "invest 100,000 and get 300,000 in a week".</li>
<li>Early investors are paid with money from new investors (a pyramid or Ponzi scheme), until it collapses.</li>
<li>Pressure to recruit friends, and fake screenshots of profits.</li>
<li>Cryptocurrency or "forex" groups where a "coach" asks you to deposit on a website they control.</li>
</ul>
<p>Rule: if it sounds too good to be true, it is. Real investments carry risk and are offered by licensed companies. You can ask a licensed financial institution or the relevant regulator whether a company is authorised.</p>
<h3>The checks that protect you</h3>
<ol>
<li>Never pay to receive a job, scholarship, loan or prize.</li>
<li>Search the name of the company or person with the word "scam" or "reviews".</li>
<li>Talk to someone you trust before sending money or documents.</li>
<li>Insist on a real video call and an official email.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Imagine this message: "Congratulations! You are selected for a job in Dubai. Pay 400,000 for your visa."</li>
<li>Write three red flags in it.</li>
<li>Write what you would reply, or whether you would reply at all.</li>
<li>Share what you learned with one friend who is job hunting.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Real employers and schools do not charge fees to give you a job or scholarship.</li>
<li>Romance scammers build trust, then ask for money; never send money to someone you have not met.</li>
<li>Guaranteed high returns are a warning sign of an investment scam.</li>
<li>Verify independently and ask a trusted person before paying or sending documents.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Malware, antivirus, safe downloads and updates',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>You will learn what malware is, how it gets onto devices, and the four habits that keep it out: safe downloads, updates, antivirus and backups. This protects your phone, your files and your money.</p>
<h3>What is malware?</h3>
<p><strong>Malware</strong> is short for "malicious software": programs made to harm you or spy on you.</p>
<table>
<thead><tr><th>Type</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Virus / worm</td><td>Spreads between files and devices and can damage them</td></tr>
<tr><td>Spyware</td><td>Secretly watches what you type, read or where you go</td></tr>
<tr><td>Ransomware</td><td>Locks your files and demands payment to unlock them</td></tr>
<tr><td>Trojan</td><td>Pretends to be a useful program or game but hides harmful code</td></tr>
<tr><td>Adware</td><td>Shows endless pop-up adverts and can slow your device</td></tr>
</tbody>
</table>
<h3>How it arrives</h3>
<ul>
<li>Cracked or "free" versions of paid software, games and movies.</li>
<li>Apps downloaded from outside the official store, or from a link in a chat.</li>
<li>Email attachments and links.</li>
<li>Infected flash drives passed between computers.</li>
<li>Out-of-date software with known weak spots.</li>
</ul>
<h3>Habit 1: safe downloads</h3>
<ol>
<li>On Android, install apps only from Google Play. On iPhone, only from the App Store.</li>
<li>Before you install, check the publisher, the number of downloads and the reviews.</li>
<li>Look at the permissions requested. A torch app has no reason to read your contacts.</li>
<li>On a computer, download from the maker's official website, not a "download portal" with big green buttons.</li>
<li>Avoid cracked software. It is the most common source of infection.</li>
</ol>
<h3>Habit 2: updates</h3>
<p>Updates fix security holes that criminals know about. Turn on <strong>automatic updates</strong> for your phone system, apps, browser and computer. On Windows, open <strong>Settings</strong>, then <strong>Windows Update</strong>.</p>
<h3>Habit 3: antivirus</h3>
<p>Windows 10 and 11 include <strong>Windows Security</strong> (Microsoft Defender) that is free and good for most home users. Keep it on. On Android, <strong>Google Play Protect</strong> scans apps. Extra antivirus apps are optional; avoid unknown "cleaner" or "booster" apps that claim your phone has 15 viruses, as they are often adverts or malware themselves.</p>
<h3>Habit 4: backups</h3>
<p>If ransomware or a lost phone hits you, a backup is your rescue. Keep copies of important files in two places, for example your phone and a cloud drive or an external disk that is not always connected.</p>
<h3>Signs your device may be infected</h3>
<ul>
<li>Sudden slowness, overheating or battery drain.</li>
<li>Pop-ups and new apps you did not install.</li>
<li>Files that will not open or have strange extensions.</li>
<li>Browser homepage changes by itself.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>On Windows, open <strong>Start</strong>, type <code>Windows Security</code> and open it. Check that there are no warnings.</li>
<li>Check for updates: <strong>Settings</strong>, <strong>Windows Update</strong>, <strong>Check for updates</strong>. On a phone, open Settings and search for "software update".</li>
<li>Look at the apps on your phone and uninstall any you do not use or recognise.</li>
<li>Choose five important files and copy them to a second place.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Malware includes viruses, spyware, ransomware, trojans and adware.</li>
<li>Download only from official stores and websites, and avoid cracked software.</li>
<li>Keep systems and apps updated automatically.</li>
<li>Backups protect you from ransomware, theft and loss.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Fake websites, HTTPS and what to do if you are hacked',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn how to tell a real website from a fake one, and a clear recovery plan if an account is hacked or you have fallen for a scam. Acting fast and calmly limits the damage.</p>
<h3>Reading a web address</h3>
<p>The part of the address that matters is the <strong>domain</strong>, found just before the first single slash. In <code>https://www.example.com/login</code>, the domain is <code>example.com</code>. Everything before it, such as <code>www.</code>, is a sub-part.</p>
<p>Fake sites use tricks:</p>
<ul>
<li><code>example.com.secure-login.net</code>: the real domain is <code>secure-login.net</code>, not example.com.</li>
<li>Look-alike spellings: <code>examp1e.com</code> (number 1 instead of letter l) or an extra word like <code>example-support.com</code>.</li>
<li>Addresses shortened by services like bit.ly hide where you are going.</li>
</ul>
<h3>HTTPS and the padlock</h3>
<p>The <code>https://</code> prefix and padlock mean the connection between you and the site is encrypted, so others cannot easily read it. This is necessary but <strong>not proof the site is honest</strong>. Scammers can also get a padlock. Always check the domain too.</p>
<h3>More signs of a fake shop or site</h3>
<ul>
<li>Prices far below the market with a countdown timer.</li>
<li>Only payment by mobile money to a personal number or by transfer, with no delivery information or returns policy.</li>
<li>No contact details or a company that does not exist when you search it.</li>
<li>A social media page created last month with copied photos.</li>
</ul>
<p>When shopping, prefer paying on delivery, or use a platform that holds your money until you receive the goods.</p>
<h3>If you are hacked or tricked</h3>
<ol>
<li><strong>Stay calm and act quickly.</strong> Do not be ashamed; it happens to clever people too.</li>
<li><strong>Change the password</strong> of the affected account from a safe device. If you used it elsewhere, change those too. Start with your email.</li>
<li><strong>Sign out all other devices</strong> in the account's security settings and turn on two-step verification.</li>
<li><strong>Contact your bank or mobile money provider</strong> immediately through the official number if money or a PIN is involved. Ask them to block or freeze.</li>
<li><strong>Tell your contacts</strong> if your account was used to send them messages, so they do not click.</li>
<li><strong>Scan your device</strong> for malware and remove unknown apps. In serious cases back up your files and reset the device.</li>
<li><strong>Keep evidence</strong>: screenshots, phone numbers, transaction IDs and times.</li>
<li><strong>Report</strong> to the platform and, for fraud, to the police (see Module 5).</li>
</ol>
<h3>Learn from it</h3>
<p>After recovery, ask: how did they get in? Weak password, clicked link, shared code? Then fix that one gap.</p>
<h3>Try it yourself</h3>
<ol>
<li>Read these addresses and decide which belongs to the real example.com: <code>example.com/help</code>, <code>example.com.help-login.net</code>, <code>exarnple.com</code>.</li>
<li>Check the address of the websites you visit most. Look at the domain only.</li>
<li>Write a short "if hacked" plan on paper with the phone numbers of your bank or mobile money support.</li>
<li>Keep it where you can find it quickly.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The domain just before the first slash tells you whose site it is.</li>
<li>A padlock means the connection is encrypted, not that the site is trustworthy.</li>
<li>If hacked: change passwords, sign out devices, contact your provider, warn contacts, report.</li>
<li>Keep evidence and do not feel ashamed to ask for help.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the best first reaction to an unexpected message that says your account will close in 24 hours unless you click a link?
- Click the link quickly to save the account
- Reply asking if it is real
* Do not click; contact the organisation using a number or website you already know
- Forward it to all your friends as a warning with the link
= Pressure plus a link is a classic phishing sign. Verify through a trusted channel.

Q: Which of these are warning signs of a fake job offer? (choose all that apply)
* You are asked to pay a registration or visa fee
* You were offered the job with no proper interview
- The company has a website and an official email address you checked independently
- The employer asks for your CV
= Paying to get a job and having no interview are red flags. A real company with verified contacts and a request for your CV is normal.

Q: A person you have chatted with online for a month, but never met, asks you to send money for an emergency. You should:
- Send it, because you trust them
- Send half
* Refuse, and tell a trusted person, because this matches a romance scam pattern
- Ask them to send you money first
= Sending money to someone you have not met in person is the heart of romance scams.

Q: Which is the safest place to install an app on Android?
- A link sent in a WhatsApp group
- A website offering cracked versions
* Google Play
- A flash drive from a friend
= Official stores have checks. Links, cracked copies and drives are common ways to spread malware.

Q: What does ransomware do?
- Shows you adverts
* Locks your files and demands payment to unlock them
- Makes your battery last longer
- Repairs damaged files
= Ransomware encrypts files for ransom. Backups are the best protection.

Q: Which web address really belongs to the company "example.com"?
- https://example.com.login-help.net
- https://exarnple.com
* https://www.example.com/login
- https://example-support.net
= Only the domain just before the first slash counts. The others use look-alike tricks.

Q: The padlock and https appear on a website. What does that tell you?
- The site is certainly honest
* The connection is encrypted, but you must still check the domain and the offer
- The site is owned by the government
- The site has no viruses
= HTTPS protects the connection, not the honesty of the site owner.

Q: Your social media account was hacked. Which steps should you take? (choose all that apply)
* Change the password and sign out other devices
* Turn on two-step verification
* Warn your contacts not to click anything from your account
- Wait a month to see what happens
= Quick action limits damage. Waiting lets the attacker keep cheating your contacts.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 4
        [
            'title'   => 'Module 4: Privacy and data protection',
            'summary' => 'Control what you share, understand personal data and consent, and protect your phone and files.',
            'lessons' => [
                [
                    'title'   => 'Social media privacy and what not to post',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to set your social media accounts to share only with the people you choose, and you will know what never to post. Once something is online, you cannot fully control who copies it.</p>
<h3>Think before you post</h3>
<p>Ask three questions before you post a photo or comment:</p>
<ol>
<li>Would I be comfortable if my family, teacher or future employer saw this?</li>
<li>Does this show where I am right now, or where I live?</li>
<li>Could this hurt, embarrass or endanger someone else?</li>
</ol>
<p>If the answer to any is "no" or "not sure", leave it out.</p>
<h3>What not to post</h3>
<ul>
<li>Your home address, school uniform with the school name and location together, or your daily routine.</li>
<li>Photos of your ID card, passport, ATM card, tickets with barcodes or boarding passes.</li>
<li>Your phone number and birth date in full on a public profile.</li>
<li>Your live location, especially when travelling or alone. Post trip photos after you return.</li>
<li>Photos of other people, especially children, without their (or their parent's) permission.</li>
<li>Private photos that you would not want the whole world to see.</li>
<li>Angry posts about people, work or school written in the heat of the moment.</li>
</ul>
<h3>Privacy settings you should check</h3>
<p>Menus differ by app and change from time to time, so look for <strong>Settings</strong>, then <strong>Privacy</strong> (or <strong>Settings and privacy</strong>).</p>
<table>
<thead><tr><th>App</th><th>What to check</th></tr></thead>
<tbody>
<tr><td>Facebook</td><td>Who can see your future posts; review tags before they appear; limit old posts; who can find you by phone number or email</td></tr>
<tr><td>Instagram</td><td>Private account; who can message you; who can tag you; hide activity status</td></tr>
<tr><td>WhatsApp</td><td>Who can see Last seen, Profile photo, About and add you to groups: choose My contacts</td></tr>
<tr><td>TikTok</td><td>Private account; who can send messages, comment, or use Duet and Stitch; hide your location</td></tr>
</tbody>
</table>
<h3>Friends and followers</h3>
<p>Accept requests only from people you know. Fake profiles with stolen photos are often used to collect information or to cheat people later. Remove or block people you no longer trust. Review your friends list once in a while.</p>
<h3>If something harmful is posted about you</h3>
<p>Take a screenshot as evidence, report the post to the platform, block the person if needed, and tell a trusted person. Module 5 has more help.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open the privacy settings of the social media app you use most.</li>
<li>Set your audience for new posts to Friends or Followers, not Public.</li>
<li>Check who can see your phone number, birthday and friends list.</li>
<li>Use the "View as" or similar feature, if present, to see how your profile looks to a stranger.</li>
<li>Delete or hide one old post that shares too much.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Think about family, teachers and employers before you post.</li>
<li>Do not post ID documents, addresses, live locations or private photos.</li>
<li>Use privacy settings to limit who sees your posts and finds you.</li>
<li>Accept only people you know and keep screenshots if someone harms you.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'App permissions, personal data and your rights',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>This lesson explains what apps ask to use on your phone, what "personal data" means, and the basic idea of data protection law. You will be able to say no to unnecessary requests and understand your rights in general terms.</p>
<h3>App permissions</h3>
<p>When you install or open an app, it may ask for permission to use your camera, microphone, contacts, photos, location, SMS or storage. Each permission is a door into your private life. Give only what the app truly needs.</p>
<table>
<thead><tr><th>App</th><th>Reasonable permission</th><th>Suspicious permission</th></tr></thead>
<tbody>
<tr><td>Map or taxi app</td><td>Location while using the app</td><td>Contacts, microphone all the time</td></tr>
<tr><td>Torch / flashlight</td><td>Camera flash</td><td>Contacts, location, SMS</td></tr>
<tr><td>Photo editor</td><td>Photos</td><td>SMS, call logs</td></tr>
<tr><td>Video call app</td><td>Camera and microphone</td><td>Reading all your files</td></tr>
</tbody>
</table>
<p>Where possible, choose <strong>While using the app</strong> or <strong>Ask every time</strong> instead of <strong>Always</strong>.</p>
<h3>Reviewing permissions</h3>
<p>On Android: <strong>Settings</strong>, then <strong>Privacy</strong> (or <strong>Security and privacy</strong>), then <strong>Permission manager</strong>. On iPhone: <strong>Settings</strong>, then <strong>Privacy and Security</strong>. Check each permission type and remove it from apps that do not need it. Uninstall apps you do not use.</p>
<h3>What is personal data?</h3>
<p><strong>Personal data</strong> is any information that can identify you: your name, phone number, address, ID number, photos, voice, location, email, and also things like medical or financial details. Some of it is more sensitive, such as health, religion, and information about children.</p>
<h3>Consent</h3>
<p><strong>Consent</strong> means you freely agree to how your information is used after being told clearly. Be careful with long "I agree" pages. Before you tick, ask: what are they collecting, why, who will get it, and can I say no? Do not give data you are not comfortable with just to receive a small reward, such as a free bundle or a lucky draw.</p>
<h3>Data protection law in general terms</h3>
<p>Many countries, including Uganda, have laws to protect personal data. In Uganda this is the Data Protection and Privacy Act. In general terms, these laws say that organisations holding your data should:</p>
<ul>
<li>Collect only what they need and say why.</li>
<li>Keep it safe and not use it for unrelated purposes.</li>
<li>Not share it without a lawful reason or your consent.</li>
<li>Let you ask to see, correct or request deletion of your data in some cases.</li>
</ul>
<p>The details are legal and can change, so if you have a serious concern about how an organisation used your data, ask the organisation first, then seek advice from a legal aid provider or the official authority responsible for data protection.</p>
<h3>When you handle other people's data</h3>
<p>If you work at a shop, school, clinic or NGO and keep customer or student information, you are responsible for protecting it. Keep it locked, do not post it in group chats, and share only with people who need it.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open your phone's permission manager.</li>
<li>Look at "Location", "Microphone" and "Contacts". See which apps have access.</li>
<li>Remove access from at least three apps that do not need it.</li>
<li>Uninstall one app you have not used in the last three months.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Give each app only the permissions it needs, and prefer "while using the app".</li>
<li>Personal data is anything that identifies you; some kinds are especially sensitive.</li>
<li>Consent should be free and informed, and you may say no.</li>
<li>Data protection laws give organisations duties and give you rights; seek advice if needed.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Protecting your phone: lock, find-my-device and SIM swap',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>Your phone holds your messages, photos, mobile money and the codes for your other accounts. After this lesson you will know how to lock it, find it when lost, and defend your number against SIM swap fraud.</p>
<h3>Screen lock</h3>
<p>A screen lock stops other people opening your phone. Choose a PIN of at least six digits, a strong password, or fingerprint or face unlock together with a PIN. Avoid simple patterns (like an L shape) that leave marks on the screen. Set the screen to lock after 30 seconds to one minute. Do not tell your PIN to friends, partners or classmates; trust can change.</p>
<h3>Find my device</h3>
<p>If your phone is lost or stolen, a tracking feature may help you locate, lock or erase it.</p>
<ol>
<li><strong>Android:</strong> make sure <strong>Find Hub</strong> (also called Find My Device) is on in your Google settings, and that you are signed in to a Google account.</li>
<li><strong>iPhone:</strong> go to <strong>Settings</strong>, tap your name, then <strong>Find My</strong>, and turn on <strong>Find My iPhone</strong>.</li>
<li>Test it from another device or a computer, using your account.</li>
<li>Know how to lock and erase remotely. If it is stolen, lock it first. Erase only when you are sure you will not recover it, and remember to keep your backup.</li>
</ol>
<p>Also write down your phone's IMEI number (dial <code>*#06#</code> to see it). Police and your network may ask for it when you report a theft.</p>
<h3>If your phone is lost or stolen</h3>
<ol>
<li>Lock or locate it using the tool above.</li>
<li>Call your network to block the SIM and ask them to protect your mobile money. Then replace the SIM at an official service point.</li>
<li>Change passwords for email, social media and banking from another device.</li>
<li>Report to the police if needed and keep the report number.</li>
</ol>
<h3>SIM swap fraud</h3>
<p>In a <strong>SIM swap</strong>, a criminal convinces your mobile network (or an agent), often using stolen or fake ID details, to move your number onto a new SIM they control. They then receive your calls, messages and codes and can access mobile money, bank apps and social media.</p>
<p>Warning signs: your phone suddenly loses signal for no reason and you cannot call or text, or you receive messages about a SIM change you did not request.</p>
<p>How to protect yourself:</p>
<ul>
<li>Keep your ID details private, and never send photos of your ID or SIM to strangers.</li>
<li>Use authenticator apps for important accounts rather than SMS codes.</li>
<li>If you lose signal unexpectedly, contact your network at once from another phone and check your mobile money.</li>
<li>Use a strong PIN for mobile money and change it if you suspect anything.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Check your screen lock and upgrade it to a six-digit PIN if it is shorter.</li>
<li>Turn on Find Hub / Find My iPhone, and write down your IMEI in a safe place.</li>
<li>Write the customer care number of your network and mobile money service in your notebook.</li>
<li>Turn on automatic locking after one minute.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Use a strong screen lock and do not share it.</li>
<li>Switch on the device-finding tool and note your IMEI.</li>
<li>Act fast if the phone is lost: lock, block the SIM, change passwords.</li>
<li>SIM swap gives criminals your codes; watch for sudden loss of signal.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Backups and encryption basics',
                    'minutes' => 11,
                    'body'    => <<<'HTML'
<p>You will learn why backups matter, a simple rule for making them, and what encryption means so you can protect files on your phone and computer. Photos of your family and documents for school or work are often irreplaceable.</p>
<h3>Why back up?</h3>
<p>Devices break, get lost, are stolen, or are locked by ransomware. If your files exist in only one place, one accident can erase years of work. A backup is simply another copy kept somewhere else.</p>
<h3>The 3-2-1 idea, made simple</h3>
<ul>
<li><strong>3</strong> copies of anything important (the original and two backups).</li>
<li><strong>2</strong> different kinds of storage (for example your phone and a flash disk).</li>
<li><strong>1</strong> copy in a different place (for example in the cloud or at a relative's home).</li>
</ul>
<p>You do not need to be perfect. Even one extra copy is a big improvement.</p>
<h3>Ways to back up</h3>
<table>
<thead><tr><th>Method</th><th>Good for</th><th>Remember</th></tr></thead>
<tbody>
<tr><td>Cloud storage (Google Drive, OneDrive, iCloud)</td><td>Photos, documents, phone data</td><td>Needs internet and a protected account with two-step verification</td></tr>
<tr><td>External hard disk or flash disk</td><td>Large files and full backups</td><td>Keep it safe and unplug it afterwards, so ransomware cannot reach it</td></tr>
<tr><td>Another computer or phone</td><td>Quick extra copy</td><td>Easy to forget; set a regular day</td></tr>
</tbody>
</table>
<h3>Turn on automatic backup</h3>
<ol>
<li>On Android, open <strong>Settings</strong>, then <strong>Google</strong>, then <strong>Backup</strong> and switch it on.</li>
<li>For photos, use Google Photos backup or iCloud Photos, mindful of your data bundle (choose Wi-Fi only).</li>
<li>On Windows, use <strong>Settings</strong>, <strong>Accounts</strong>, <strong>Windows Backup</strong> (Windows 11), or copy folders to an external disk.</li>
<li>Test by opening a file from the backup once in a while. A backup you cannot open is useless.</li>
</ol>
<h3>Encryption in simple words</h3>
<p><strong>Encryption</strong> scrambles information so only someone with the right key (a password or PIN) can read it. You already use it: HTTPS websites, WhatsApp messages (end-to-end encrypted), and many phones encrypt stored data when you set a screen lock.</p>
<p>What you can do:</p>
<ul>
<li>Always set a screen lock, as this activates encryption on modern phones.</li>
<li>On Windows, Pro editions have BitLocker, and many devices offer "Device encryption" in Settings, under Privacy and security. This protects data if a laptop is stolen.</li>
<li>Put a password on sensitive files and share the password through a different channel from the file.</li>
<li>Encrypted data is only as safe as its key. If you forget the key you may lose the data, so keep a recovery key safely.</li>
</ul>
<h3>Common mistakes</h3>
<ul>
<li>Keeping the only copy of your thesis on a flash disk.</li>
<li>Leaving the backup disk plugged in all the time.</li>
<li>Selling or giving away a phone without erasing it. Always back up, sign out and reset it first.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Choose ten files or photos you cannot afford to lose.</li>
<li>Copy them to a second place, such as a flash disk or a cloud folder.</li>
<li>Switch on your phone's automatic backup if it is off.</li>
<li>Set a reminder on your phone to repeat this on the first day of each month.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A backup is a second copy stored somewhere else; follow the 3-2-1 idea where you can.</li>
<li>Use automatic cloud backup plus an occasional offline copy.</li>
<li>Encryption scrambles data; a screen lock helps turn it on.</li>
<li>Keep recovery keys safe and test your backups.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which item should you NOT post publicly?
- A photo of a beautiful sunset
* A photo of your National ID or ATM card
- A congratulatory message to a friend
- A picture of your football team
= Documents and cards contain data criminals use for identity theft and fraud.

Q: A torch (flashlight) app asks for access to your contacts and SMS. What should you do?
- Allow it, since the app is free
* Deny it, because a torch does not need those permissions, and consider uninstalling the app
- Allow it only on weekends
- Allow it and give your PIN
= Permissions should match the job of the app. A mismatch is a warning sign.

Q: What is "personal data"?
- Only your bank balance
* Any information that can identify you, such as name, phone number, photos, ID number or location
- Only information on paper
- Only passwords
= Personal data is broad. Some types, such as health or children's data, are especially sensitive.

Q: Your phone suddenly loses all signal, you cannot call or text, and you did not do anything. What might be happening?
- Nothing; it is always harmless
* A possible SIM swap, so contact your network at once from another phone and check your mobile money
- Your phone is too clean
- Your screen lock is too strong
= Sudden loss of service can mean your number was moved to another SIM. Quick action matters.

Q: Which settings make your phone safer if it is stolen? (choose all that apply)
* A strong screen lock
* Find Hub or Find My iPhone turned on
- Writing your PIN on the back cover
- Leaving it unlocked for convenience
= Lock plus device tracking help protect and recover the phone. A written PIN defeats the lock.

Q: Which is the best backup habit?
- Keep the only copy on your phone
* Keep at least one extra copy in another place, such as the cloud or a stored disk, and test it
- Back up only once, ever
- Keep the backup disk always connected to the same computer
= Copies in separate places survive loss, theft and ransomware. Testing proves they work.

Q: What does encryption do?
* Scrambles information so only someone with the key can read it
- Makes files bigger
- Removes viruses
- Hides your phone number from the network
= Encryption protects confidentiality, but you must keep the key or recovery information safe.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 5
        [
            'title'   => 'Module 5: Safe and respectful online life',
            'summary' => 'Deal with bullying and abuse, protect children, look after your wellbeing, know where to report and build your personal security checklist.',
            'lessons' => [
                [
                    'title'   => 'Cyberbullying and online harassment',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will recognise cyberbullying and online harassment, know what to do if it happens to you or someone you know, and understand how to avoid causing harm yourself. Nobody deserves to be treated cruelly online.</p>
<h3>What it is</h3>
<p><strong>Cyberbullying</strong> is the use of phones and the internet to hurt, scare, shame or exclude someone, again and again. <strong>Online harassment</strong> is unwanted, threatening or abusive contact, and it can happen to adults as well as young people.</p>
<p>Examples include insulting or threatening messages, spreading rumours, making fun posts or edited photos, excluding someone from a group chat on purpose, pretending to be someone else, and sharing private information to embarrass them. This behaviour can be against the rules of platforms and, when it includes threats or serious harm, against the law.</p>
<h3>Why it hurts so much</h3>
<p>It can reach you at home at any time, may be seen by many people, and can be copied and kept. Victims may feel lonely, ashamed, anxious or unable to sleep or concentrate. It is not your fault, and it is not weakness to feel hurt.</p>
<h3>If it happens to you</h3>
<ol>
<li><strong>Do not reply in anger.</strong> Responding often makes things worse.</li>
<li><strong>Save evidence.</strong> Take screenshots with dates, names and links.</li>
<li><strong>Block and mute</strong> the person. Use the privacy settings to limit who can contact or comment.</li>
<li><strong>Report</strong> the account, message or post to the platform using the Report option.</li>
<li><strong>Tell someone you trust</strong>: a parent, guardian, teacher, counsellor, older sibling, pastor or friend. If the school is involved, share the evidence with the school.</li>
<li>If there are threats to your safety, contact the police, and look after yourself while it is being dealt with.</li>
</ol>
<h3>If you see it happening to someone else</h3>
<ul>
<li>Do not join in, like or share the post.</li>
<li>Support the person privately: "I saw that, I am sorry, I am here."</li>
<li>Report the content and encourage the person to tell a trusted adult.</li>
</ul>
<h3>Your own behaviour</h3>
<p>Think before you type. Words on a screen lack tone, so jokes can hurt. Do not forward humiliating photos or rumours. Do not use group chats to gossip or exclude. If you have hurt someone, stop, apologise sincerely and remove the content.</p>
<h3>Looking after yourself</h3>
<p>If you feel hopeless or in danger of harming yourself, please speak to a trusted person or a health worker or counsellor today. You do not have to carry this alone.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open one social app and find the Block and Report options on a profile or message. Do not use them on a real person unless needed, just learn where they are.</li>
<li>Practise taking a screenshot on your phone (usually the power and volume-down buttons together).</li>
<li>Write down the names of two people you could tell if you were being bullied online.</li>
<li>Write a short, kind message you could send to a friend who is being targeted.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Cyberbullying and harassment are repeated, hurtful or threatening acts online. They are never the victim's fault.</li>
<li>Save evidence, block, report, and tell someone you trust.</li>
<li>Do not join in or share harmful content.</li>
<li>Talk to a trusted person or counsellor if you are struggling.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Protecting children and young people; sextortion and image abuse',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>This lesson helps parents, teachers, older siblings and young people understand risks to children online, including blackmail with private images, and how to get help. The goal is protection and support, not fear. The lesson avoids graphic detail on purpose.</p>
<h3>Why children are at risk</h3>
<p>Children and teenagers are curious and trusting, and they may chat with people they do not know. Some adults pretend to be young, make friends, gain trust and then pressure the child to share private pictures or meet. A child cannot give meaningful consent in these situations, and the responsibility always lies with the adult.</p>
<h3>Warning signs a child may be in trouble</h3>
<ul>
<li>Becomes secretive about their phone, or upset after being online.</li>
<li>Gets gifts, airtime or money from someone unknown.</li>
<li>Withdraws from friends, sleeps badly, or changes mood suddenly.</li>
<li>Hides messages or deletes chats quickly.</li>
</ul>
<h3>Sextortion and image abuse</h3>
<p><strong>Sextortion</strong> is blackmail: someone threatens to share private or intimate images, real or fake, unless the victim sends money, more images or does something else. <strong>Image-based abuse</strong> means sharing or threatening to share someone's private images without their consent. Both can happen to teenagers and adults. Edited or fake images can also be used to threaten people.</p>
<p>If this happens to you or someone you know:</p>
<ol>
<li><strong>It is not your fault.</strong> The person doing the blackmail is the one committing the wrong.</li>
<li><strong>Stop contact and do not pay.</strong> Paying rarely ends it; the demands usually increase.</li>
<li><strong>Keep evidence.</strong> Save usernames, links and messages (do not delete the chat before you have taken screenshots). Do not forward any intimate image to others, even to show evidence, especially if the person is a child.</li>
<li><strong>Block and report</strong> the account on the platform. Most platforms have a route for reporting intimate image abuse and can remove content.</li>
<li><strong>Tell a trusted adult</strong> or counsellor immediately, and report to the police. Children's cases can be handled by specialist police desks and child helplines.</li>
</ol>
<h3>For parents and guardians</h3>
<ul>
<li>Talk early and often, in a calm voice. Make sure the child knows they will not be punished for telling you about a problem.</li>
<li>Set up age-appropriate privacy and parental controls, and learn the apps they use.</li>
<li>Agree simple family rules: no meeting online friends alone, no sharing of photos or personal details with strangers, and ask when unsure.</li>
<li>Keep devices in shared spaces for younger children.</li>
</ul>
<h3>For young people</h3>
<ul>
<li>Keep accounts private and accept only people you know.</li>
<li>Never send private pictures, even to someone you trust, because you cannot control what happens next.</li>
<li>If something makes you uncomfortable, stop and tell an adult you trust.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Write down three trusted adults a young person could tell about a problem online.</li>
<li>Find and note the child helpline number or the police child protection contact for your area (ask a teacher, local leader or search an official site).</li>
<li>If you are a parent or teacher, have a ten-minute talk with a young person about the "tell someone" rule.</li>
<li>Check the privacy settings on one child's account together with them.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Adults, not children, are responsible for abuse; victims are never to blame.</li>
<li>In sextortion do not pay, keep evidence, block, report and tell a trusted adult.</li>
<li>Never forward intimate images of anyone, and especially not of a child.</li>
<li>Open conversation and privacy settings protect young people best.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Digital wellbeing and responsible sharing',
                    'minutes' => 11,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to manage your time online, notice when it affects your health, and share information responsibly. Healthy habits help you study, work and relate to people better.</p>
<h3>Signs of unhealthy use</h3>
<ul>
<li>You check your phone constantly, even when nothing important is happening.</li>
<li>You lose sleep because of scrolling or chatting at night.</li>
<li>You feel anxious, jealous or low after viewing other people's posts.</li>
<li>You neglect study, work, family or exercise.</li>
<li>You feel angry or upset when you cannot get online.</li>
</ul>
<h3>Simple habits that help</h3>
<ol>
<li><strong>Check your screen time.</strong> On Android use <strong>Digital Wellbeing</strong> in Settings; on iPhone use <strong>Screen Time</strong>.</li>
<li><strong>Set app limits</strong> for the apps that eat most of your time.</li>
<li><strong>Turn off non-essential notifications</strong> so your phone does not interrupt you.</li>
<li><strong>Keep the phone out of the bedroom</strong> or switch on a night mode or Do Not Disturb from bedtime.</li>
<li><strong>Take breaks.</strong> Every 30 to 60 minutes look away from the screen, stretch and drink water.</li>
<li><strong>Do things offline</strong>: sport, reading, church or mosque, or time with family and friends.</li>
</ol>
<h3>Remember what you see is selected</h3>
<p>People usually post the best moments. Comparing your normal life with other people's highlights can hurt your confidence. It is fine to unfollow accounts that make you feel bad.</p>
<h3>Responsible sharing</h3>
<p>Misinformation (false information) spreads quickly, especially about health, politics, money and emergencies. Before you forward a message, take a minute:</p>
<ol>
<li><strong>Who</strong> wrote it? Is it from a known, trusted source?</li>
<li><strong>What</strong> is the evidence? Is it only a screenshot or a "forwarded many times" message?</li>
<li><strong>When</strong> was it posted? Old news is often shared as new.</li>
<li><strong>Other sources:</strong> do reliable news organisations or official health or government pages say the same?</li>
<li><strong>Feeling check:</strong> does it make you very angry or afraid? Strong emotion is a reason to pause.</li>
</ol>
<p>Fake or edited images and videos, including some made by AI, are becoming more common. If a photo or video looks shocking, check it before believing or sharing.</p>
<p>Also respect others: ask before posting a photo of someone, do not share people's private chats, and give credit when you use someone else's work.</p>
<h3>Common mistakes</h3>
<ul>
<li>Forwarding "urgent" health tips without checking.</li>
<li>Sharing a screenshot of a private conversation.</li>
<li>Posting a picture of an accident or someone in distress.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Open your phone's Digital Wellbeing or Screen Time and see your daily average.</li>
<li>Pick your most used app and set a daily limit that is about 30 minutes lower.</li>
<li>Turn off notifications from three apps that are not urgent.</li>
<li>Before forwarding your next message, ask the five questions: who, what, when, other sources and feeling.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Notice effects on sleep, mood, study and relationships, and set limits.</li>
<li>Use screen time tools, fewer notifications and offline activities.</li>
<li>Pause and check a message before you forward it.</li>
<li>Respect other people's privacy and credit their work.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Getting help in Uganda and your personal security checklist',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>You will finish this course by learning where to report problems and by building a personal security checklist to use every month. Knowing who to contact before a problem happens saves precious time.</p>
<h3>Where to get help, in general terms</h3>
<p>Contact details and procedures change, so always confirm current numbers and addresses through official sources such as the organisation's own website, a bank card or an official service point.</p>
<table>
<thead><tr><th>Problem</th><th>Who can help</th></tr></thead>
<tbody>
<tr><td>Money lost through mobile money</td><td>The mobile money provider's official customer care or service centre (report at once); then the police</td></tr>
<tr><td>Bank card or online banking fraud</td><td>Your bank, using the number on your card or the bank's official website</td></tr>
<tr><td>Fraud, threats, blackmail, stalking or hacking</td><td>The nearest police station, which may refer you to a specialised cybercrime or child and family protection unit</td></tr>
<tr><td>Abusive post, fake account, hacked social media</td><td>The platform's Report or Help centre tools</td></tr>
<tr><td>Cyberbullying at school</td><td>A teacher, head teacher or school counsellor</td></tr>
<tr><td>Child at risk</td><td>A trusted adult, the police child protection unit or a child helpline</td></tr>
<tr><td>Feeling distressed, afraid or alone</td><td>A trusted person, health worker, counsellor or faith leader</td></tr>
<tr><td>Legal advice you cannot afford</td><td>A legal aid organisation or local council office</td></tr>
</tbody>
</table>
<h3>What to bring when you report</h3>
<ul>
<li>Screenshots, phone numbers, usernames and links.</li>
<li>Transaction IDs, dates and amounts.</li>
<li>A short written timeline of what happened.</li>
<li>Your ID, if asked.</li>
</ul>
<h3>Report early, report calmly</h3>
<p>Many people hesitate because they are embarrassed. Please do not wait. Early reports give providers a better chance to freeze money, remove content or trace an account. Helpers deal with cases like yours regularly and are there to assist, not to blame.</p>
<h3>Your personal security checklist</h3>
<p>Use this list every month. Tick each item.</p>
<ol>
<li>My phone has a strong screen lock and automatic lock.</li>
<li>My email and main accounts have unique strong passwords or passphrases.</li>
<li>Two-step verification is on for email, social media and WhatsApp.</li>
<li>I never share PINs, passwords or one-time codes.</li>
<li>My phone, apps and computer have automatic updates on.</li>
<li>I install apps only from official stores and check permissions.</li>
<li>My social media privacy settings are reviewed and set to friends only.</li>
<li>Find-my-device is on and I know my IMEI.</li>
<li>I have a backup of my important files, and I tested it.</li>
<li>I know who to call if I lose money, an account or my phone.</li>
<li>I pause and check before I click, pay or forward.</li>
<li>I support friends and report abuse when I see it.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Copy the checklist into a notebook or notes app.</li>
<li>Tick the items you already do and mark the ones you do not.</li>
<li>Pick the three most important missing items and do them this week.</li>
<li>Set a monthly reminder to review the whole list.</li>
<li>Share the checklist with a family member and help them do the same.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Report quickly to the right place: provider, bank, platform, school or police.</li>
<li>Bring evidence: screenshots, IDs, times and amounts.</li>
<li>Do not be ashamed; helpers are there to assist.</li>
<li>Use the checklist monthly and teach others.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A classmate keeps sending you insulting messages online. What is a good first set of steps?
- Reply with insults in the same way
* Save screenshots, block and report the person, and tell a trusted adult
- Delete everything and say nothing to anyone
- Post about it publicly with their phone number
= Evidence, blocking, reporting and telling someone you trust is the safest, most effective approach.

Q: Someone threatens to share private pictures of you unless you pay. What should you do? (choose all that apply)
* Not pay, and stop contact
* Keep evidence and tell a trusted adult
* Report the account to the platform and the matter to the police
- Send the money to make it stop
= Paying usually leads to more demands. Evidence and reporting give you the best chance of help. It is not your fault.

Q: Which statement about child safety online is correct?
- Children who receive messages from strangers are always to blame
* The adult who abuses or pressures a child is responsible, and the child should be supported
- Only older teenagers are at risk
- Parents should never talk about it
= Responsibility rests with the adult. Calm, open conversation helps children report problems early.

Q: You receive a shocking "urgent health warning" forwarded many times. Best action?
- Forward it to all contacts immediately
* Check the source and compare with official health sources before sharing
- Reply with angry emojis
- Post it as your own
= Misinformation spreads fast. Checking source, date and other sources prevents harm.

Q: Which habits support digital wellbeing? (choose all that apply)
* Setting app limits
* Turning off non-essential notifications
* Keeping the phone away from the bed at night
- Checking your phone every few minutes
= Limits, fewer interruptions and good sleep habits protect your mood and focus.

Q: You lose money through a mobile money scam. What is the most important step to take quickly?
- Wait several weeks to be sure
* Contact the provider through its official channel immediately and keep transaction details
- Ask the scammer to return it
- Post your PIN online to find them
= Quick reporting to the official provider gives the best chance of freezing or tracing the money.

Q: Which item belongs on a monthly personal security checklist?
- Share my password with a close friend
* Check updates, backups, privacy settings and two-step verification
- Leave my phone unlocked
- Use the same password everywhere
= The checklist reinforces the habits you learned. The other options reduce your safety.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which message is MOST likely to be a scam?
- A message from your school with the term timetable that you expected
* An SMS saying you won a prize and must send 30,000 shillings to receive it
- A notification from your bank app that you opened yourself
- A friend calling you by voice and talking about a normal topic
= Asking you to pay to receive a prize is a classic advance-fee scam.

Q: Why is your main email account especially important to protect?
- It stores your phone contacts only
* Password resets for many other accounts are sent to it
- It makes your phone faster
- It is needed to charge your battery
= Whoever controls your email can often reset the passwords of your other accounts.

Q: Which is the best password for an important account?
- Mary1998
- Uganda@123
* teacup-lantern-river-bicycle-orange-8
- 12345678
= Long, random, unrelated words are strong and still memorable.

Q: Which people should have your mobile money PIN?
- Staff of the mobile money company
- Your best friend
* Nobody; you keep it private
- Anyone who calls with a good reason
= A PIN is for you alone. Real providers never ask for it.

Q: What is two-step verification?
- Typing your password twice
* A second proof, such as a code or approval on your phone, in addition to your password
- Using two different browsers
- Having two email addresses
= It combines something you know with something you have, so a stolen password alone is not enough.

Q: A caller says he sent you a code by mistake and asks you to read it to him. What is happening?
- He wants to help you
* He is probably trying to log into your account or register your number elsewhere
- It is a free promotion
- Your phone is broken
= One-time codes should never be shared.

Q: Which Wi-Fi practices are safe? (choose all that apply)
* Changing the default admin password of the home router
* Using mobile data for mobile money instead of a public hotspot
- Connecting to any network called "Free WiFi"
- Sharing the home Wi-Fi password in public groups
= Default passwords are widely known, and public or fake hotspots may be watched.

Q: On a shared cybercafe computer you should:
- Tick "Remember me"
* Use a private window, avoid saving passwords and sign out fully
- Leave the account open
- Email yourself your PIN
= Reduce traces and always sign out of each account.

Q: Which is a typical sign of phishing?
- A message that politely greets you
* Pressure to act immediately and click a link or share a code
- A newsletter you subscribed to
- A message with correct spelling
= Urgency plus a link or a secret request is the heart of phishing.

Q: The best way to check an unexpected message from your bank is to:
- Call the number in the message
* Contact the bank using the number on your card or official website
- Click the link to see
- Reply "STOP"
= Details inside a suspicious message may be fake. Use known official contact details.

Q: A job offer asks you to pay a training and visa fee before you start. The best response is:
- Pay quickly to secure the job
- Ask a friend to pay it
* Refuse, and check the company independently, because real employers do not charge to hire you
- Send a copy of your ID first
= Charging fees to give a job is a major warning sign of fraud.

Q: What is ransomware?
- Software that shows adverts
* Malware that locks your files and demands payment
- A free antivirus
- A type of Wi-Fi
= Backups are your best protection against ransomware.

Q: Which are good ways to reduce the risk of malware? (choose all that apply)
* Install apps only from official stores
* Keep your system and apps updated
* Avoid cracked software
- Click every attachment to be polite
= Safe sources, updates and avoiding pirated software block the main routes for malware.

Q: In the address https://example.com.account-check.net/login, whose site is it?
- example.com
* account-check.net
- The government
- Impossible to know
= The domain is the part just before the first single slash; here it is account-check.net.

Q: What does the padlock and https show?
- The site is run by honest people
* The connection is encrypted, but the site could still be a scam
- The site is free from viruses
- The site is approved by the police
= Always also check the domain and the offer.

Q: Your social media account is hacked. Which comes first?
- Wait and see
* Change the password from a safe device, sign out other sessions and turn on two-step verification
- Delete your phone
- Create a new account with the same password
= Quick control of the account limits further damage.

Q: A romance scammer commonly:
- Agrees to video calls immediately
* Builds trust over weeks, then asks for money for an emergency
- Meets you in person on day one
- Never asks for anything
= Emotional trust is used to get money from the victim.

Q: Which setting best protects who sees your posts?
- Public audience for all posts
* Friends or followers only, with tag review turned on
- Accepting every friend request
- Posting your live location
= Limiting audience and reviewing tags reduces exposure.

Q: A mapping app asks for your location while you use it. A calculator app asks for your microphone and contacts. Which is suspicious?
- The mapping app only
* The calculator app, because the permissions do not match its purpose
- Both are normal
- Neither
= Permissions should fit the app's function.

Q: Your phone is stolen. Which steps should you do? (choose all that apply)
* Lock or locate it with Find Hub or Find My iPhone
* Ask your network to block the SIM and protect mobile money
* Change passwords for key accounts from another device
- Wait until next month
= Rapid action prevents misuse of your accounts and money.

Q: In a SIM swap attack, the criminal wants to:
- Improve your signal
* Receive your calls and one-time codes by moving your number to their SIM
- Replace your phone screen
- Clean your phone
= Control of your number lets them reset passwords and access money.

Q: Which backup approach is best?
- A single copy on the phone
* An automatic cloud backup plus an occasional separate copy that you test
- Never open your backups
- Keep your only copy on a flash disk plugged in all the time
= Multiple copies in different places survive loss, theft and ransomware.

Q: What does encryption do?
- Deletes data
* Scrambles data so only someone with the key can read it
- Speeds up the internet
- Finds viruses
= It protects privacy if a device or message is intercepted, but you must keep keys safe.

Q: Someone is sharing hurtful rumours about a classmate in a group chat. What is a good response?
- Join in so you are not left out
* Do not share it, support the classmate privately, and report or tell a trusted adult
- Screenshot it and post publicly to shame the sender
- Ignore it forever
= Do not take part; support and report to help stop it.

Q: Someone threatens to post your private images unless you pay. The right response includes: (choose all that apply)
* Not paying
* Saving evidence and reporting
* Telling a trusted adult
- Blaming yourself
= It is not your fault. Support and reporting are the way forward.

Q: Before forwarding a viral health message you should:
- Forward it quickly
* Check the source, date and whether reliable organisations say the same
- Add your name and send again
- Wait for ten likes
= Checking prevents the spread of false information.

Q: Which is NOT a good habit for wellbeing?
- Setting app time limits
- Taking screen breaks
* Scrolling in bed all night
- Doing offline activities
= Late-night scrolling harms sleep and mood.

Q: Which is the best description of consent for personal data?
- Ticking without reading
* Freely agreeing after being told clearly what is collected, why and who receives it
- Forced agreement
- Hidden agreement
= Consent should be informed and free, and you may say no.

Q: Which item should always appear on your monthly security checklist?
* Updates, backups, two-step verification and privacy settings
- Sharing passwords
- Public location sharing
- Tapping every link
= These habits protect your accounts and devices.

Q: When reporting a scam or abuse, what should you keep ready? (choose all that apply)
* Screenshots and usernames
* Transaction IDs, dates and amounts
* A short timeline of events
- Nothing, since details do not help
= Clear evidence helps providers, platforms and police act faster.
QUIZ,
];
