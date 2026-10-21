<?php
return [
    'slug'        => 'internet-email',
    'title'       => 'The Internet, Email and Online Communication',
    'category'    => 'internet',
    'level'       => 'beginner',
    'icon'        => 'mail',
    'sort'        => 70,
    'summary'     => 'Learn how the internet works, search safely, set up and use email, write professional messages and join online meetings with confidence.',
    'description' => '<p>The internet is now part of school, work, business and community life. Jobs are advertised online, schools share notes online, and many meetings happen on video. This course takes you from "What is the internet?" to sending a professional email and joining a video call.</p><p>You will learn how to use a web browser, search for reliable information, spot fake news and scams, create and manage an email account, and communicate politely and clearly online. Every lesson ends with a short practice activity so that you learn by doing.</p><p>The course is written for beginners. If you can switch on a phone or computer, you can start here.</p>',
    'outcomes'    => [
        'Explain in simple words what the internet and the web are and how your device connects to them',
        'Use a web browser with tabs, bookmarks, history and downloads',
        'Search the web with good keywords and judge whether a source can be trusted',
        'Create an email account and send, reply, forward and organise messages with attachments',
        'Recognise spam and phishing emails and protect your account',
        'Write a clear, polite professional email, including a job application',
        'Join and host a video meeting and share a file with a link',
        'Follow good manners (netiquette) on email, chat and social media',
    ],
    'requirements' => [
        'A computer, laptop or smartphone (an internet connection is needed for the practice activities)',
        'No experience needed',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Gmail Help', 'url' => 'https://support.google.com/mail'],
        ['title' => 'Google Search Help', 'url' => 'https://support.google.com/websearch'],
        ['title' => 'Microsoft Support: Outlook help', 'url' => 'https://support.microsoft.com/outlook'],
        ['title' => 'Google Meet Help', 'url' => 'https://support.google.com/meet'],
    ],
    'modules' => [
        [
            'title'   => 'Module 1: How the internet works',
            'summary' => 'What the internet and the web are, how you connect, how to save data, and how to use a browser.',
            'lessons' => [
                [
                    'title'   => 'What the internet and the web are',
                    'minutes' => 10,
                    'body'    => <<<'BODY'
<p>After this lesson you will be able to explain what the internet is, how it differs from the web, and what happens when you open a page. This matters because when you understand the basics, it is much easier to fix problems and to stay safe.</p>
<h3>The internet: a network of networks</h3>
<p>A <strong>network</strong> is two or more computers connected so they can share information. The <strong>internet</strong> is a huge worldwide network that joins millions of smaller networks: homes, schools, banks, companies and governments. It is made of cables (including cables under the sea), radio links and satellites. Nobody owns the whole internet.</p>
<h3>The web is not the same as the internet</h3>
<p>The <strong>World Wide Web</strong> (the web) is one service that runs on the internet. It is the collection of websites and web pages you open with a browser. Other services also use the internet: email, WhatsApp, video calls and online games. A simple way to remember it: the internet is the road, and the web is one kind of vehicle that travels on it.</p>
<h3>Key words you will meet</h3>
<table>
<thead><tr><th>Word</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>Website</td><td>A group of web pages that belong together, such as a school website</td></tr>
<tr><td>Web page</td><td>One page of a website</td></tr>
<tr><td>Server</td><td>A powerful computer that stores websites and sends them to you when you ask</td></tr>
<tr><td>Browser</td><td>The app you use to open websites, for example Chrome, Edge, Firefox or Safari</td></tr>
<tr><td>Online / offline</td><td>Connected to the internet / not connected</td></tr>
</tbody>
</table>
<h3>What happens when you open a page</h3>
<ol>
<li>You type an address or tap a link in your browser.</li>
<li>Your device sends a request through your connection to the internet.</li>
<li>The request reaches the server where that website is stored.</li>
<li>The server sends the page back, usually in less than a second.</li>
<li>Your browser shows the page on your screen.</li>
</ol>
<p>Many people say "the internet is slow" when the real problem is a weak signal or a nearly finished data bundle. You will learn how to check this in the next lesson.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open your browser and visit any website you know, such as a news site.</li>
<li>Click a link inside the page and notice that a new page loads. That page came from a server.</li>
<li>Switch off Wi-Fi or mobile data and try to open another page. Note the "no internet" message.</li>
<li>Switch the connection back on and reload the page. Write one sentence explaining the difference between being online and offline.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The internet is a worldwide network of networks; the web is one service that uses it.</li>
<li>Websites are stored on servers and sent to your browser when you ask.</li>
<li>A browser is the app that lets you view websites.</li>
<li>Email, WhatsApp and video calls also use the internet but are not "the web".</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Connecting: data, Wi-Fi, ISPs and saving bundles',
                    'minutes' => 12,
                    'body'    => <<<'BODY'
<p>By the end of this lesson you will know the main ways to get online, what an internet service provider does, and how to make your data bundle last longer. In Uganda, data costs real money, so knowing how to save it helps every day.</p>
<h3>Internet service providers</h3>
<p>An <strong>ISP</strong> (internet service provider) is a company that sells you access to the internet. Mobile network operators sell data bundles for phones, and other companies install fixed connections in offices, schools and homes. You pay for access, usually monthly or by bundle.</p>
<h3>Ways to connect</h3>
<table>
<thead><tr><th>Type</th><th>How it works</th><th>Good for</th><th>Watch out for</th></tr></thead>
<tbody>
<tr><td>Mobile data (4G/5G)</td><td>Your SIM card connects to the operator's network</td><td>Anywhere there is a signal</td><td>Bundles run out; signal changes by place</td></tr>
<tr><td>Wi-Fi</td><td>A router shares one connection with nearby devices by radio</td><td>Home, school, office</td><td>Slow when many people share it; weak far from the router</td></tr>
<tr><td>Fixed line (fibre or cable)</td><td>A cable brings the connection to a building</td><td>Fast and steady connection</td><td>Only available in some areas</td></tr>
<tr><td>Hotspot</td><td>Your phone shares its mobile data as Wi-Fi to a laptop</td><td>Getting a laptop online</td><td>Uses your phone's bundle and battery quickly</td></tr>
</tbody>
</table>
<p>Wi-Fi is not the internet itself. Wi-Fi only connects your device to a router, and the router must still be connected to an ISP.</p>
<h3>Understanding your bundle</h3>
<p>Data is measured in megabytes (MB) and gigabytes (GB); 1 GB is about 1,000 MB. Video uses the most data, photos use a medium amount, and plain text and email use very little. Check your balance using your operator's code or app, and read the bundle's expiry date.</p>
<h3>Ways to save data</h3>
<ul>
<li>Use Wi-Fi when it is available and safe, especially for downloads and updates.</li>
<li>Turn off <strong>auto-play video</strong> and auto-download of media in WhatsApp and social apps.</li>
<li>Lower video quality (for example, 360p instead of HD) when you only need the sound.</li>
<li>Turn on the <strong>Data Saver</strong> or "Lite mode" option in your browser or phone settings.</li>
<li>Set app updates to Wi-Fi only.</li>
<li>Close apps running in the background, and switch mobile data off when not needed.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Find out your remaining data balance and its expiry date using your operator's code or app.</li>
<li>Open your phone settings and find the data usage screen. Which three apps used the most data this month?</li>
<li>Turn on Data Saver (or the equivalent) and set app updates to Wi-Fi only.</li>
<li>In WhatsApp, open Settings and find the option for automatic media download. Switch off downloads on mobile data.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>An ISP sells you internet access; Wi-Fi only shares a connection locally.</li>
<li>Mobile data, Wi-Fi and fixed lines each have strengths and limits.</li>
<li>Video uses the most data; text uses the least.</li>
<li>Data saver settings and Wi-Fi-only updates stretch your bundle.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Web addresses, domains and safe sites',
                    'minutes' => 10,
                    'body'    => <<<'BODY'
<p>In this lesson you will learn to read a web address, understand its parts and check whether a site looks safe. Reading addresses carefully protects you from fake websites that copy real ones.</p>
<h3>The parts of a URL</h3>
<p>A <strong>URL</strong> (web address) tells your browser exactly where to find a page. Look at this example:</p>
<pre><code>https://www.example.org/news/article.html</code></pre>
<table>
<thead><tr><th>Part</th><th>Example</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>Protocol</td><td>https://</td><td>The way the page is sent; the "s" means secure (encrypted)</td></tr>
<tr><td>Domain name</td><td>www.example.org</td><td>The name of the website</td></tr>
<tr><td>Path</td><td>/news/article.html</td><td>The exact page inside the website</td></tr>
</tbody>
</table>
<h3>Domains and endings</h3>
<p>A <strong>domain name</strong> is the human-friendly name of a website. Computers actually use number addresses, and the domain name system translates names to numbers, like a phone book. The ending is called the <strong>top-level domain</strong>. Common ones include <code>.com</code> (commercial), <code>.org</code> (organisations), <code>.edu</code> (education), <code>.gov</code> (government) and country endings such as <code>.ug</code> for Uganda, <code>.ke</code> for Kenya or <code>.co.ug</code> for companies in Uganda.</p>
<p>The ending alone does not prove trust, because anyone can buy many of them. Always read the main name just before the ending.</p>
<h3>Spotting a fake address</h3>
<p>Scammers use small changes: <code>g00gle.com</code> with zeros, <code>mtn-bonus-win.com</code> with extra words, or a real name placed in front of a false one, such as <code>mybank.com.secure-login.net</code>. In that last case the real domain is <code>secure-login.net</code>, because the true domain is the part just before the ending.</p>
<h3>The padlock and https</h3>
<p>Most browsers show a padlock or similar icon next to secure sites. It means the connection is encrypted so others cannot easily read what you send. It does not mean the site owner is honest, so a scam site can also have https. Use the padlock together with careful reading of the name.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open any news website. Click the address bar and identify the protocol, the domain name and the path.</li>
<li>Write down the top-level domain and say what it suggests.</li>
<li>Look at these and decide which are suspicious: <code>paypal.com</code>, <code>paypa1.com</code>, <code>school.ug.login-update.com</code>.</li>
<li>Find a padlock icon on a site that uses https. Click it and read what the browser says.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A URL has a protocol, a domain name and a path.</li>
<li>The real domain is the name just before the ending.</li>
<li>https and the padlock mean an encrypted connection, not a guarantee of honesty.</li>
<li>Misspellings and extra words in a domain are warning signs.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Using a web browser: tabs, bookmarks, history and downloads',
                    'minutes' => 14,
                    'body'    => <<<'BODY'
<p>When you finish this lesson you will be able to move around the web smoothly: opening several pages, saving favourites, finding past pages and downloading files safely. The steps below are almost the same in Chrome, Edge and Firefox.</p>
<h3>The main parts of a browser</h3>
<ul>
<li><strong>Address bar</strong>: type a web address here, or type words to search.</li>
<li><strong>Back and Forward buttons</strong>: go to the page before or after the current one.</li>
<li><strong>Reload</strong>: loads the page again, useful when it freezes.</li>
<li><strong>Tabs</strong>: let you keep several pages open in one window.</li>
<li><strong>Menu</strong>: usually three dots or lines at the top right, leading to settings, history and downloads.</li>
</ul>
<h3>Working with tabs</h3>
<ol>
<li>Select the <strong>+</strong> button next to the last tab (or press Ctrl+T, Cmd+T on Mac) to open a new tab.</li>
<li>Click a tab to switch to it. Press Ctrl+Tab to move to the next tab.</li>
<li>Close a tab with its <strong>x</strong> (or Ctrl+W, Cmd+W). If you close one by mistake, press Ctrl+Shift+T (Cmd+Shift+T) to bring it back.</li>
</ol>
<p>Too many tabs make a device slow, so close the ones you no longer need.</p>
<h3>Bookmarks</h3>
<p>A <strong>bookmark</strong> (or favourite) saves a page so you can return with one click. Press Ctrl+D (Cmd+D) or click the star icon in the address bar, give it a clear name and choose a folder. You can show the bookmarks bar from the browser menu.</p>
<h3>History</h3>
<p>The browser remembers pages you have visited. Open the menu and select <strong>History</strong> (or press Ctrl+H) to find a page you saw yesterday. On a shared computer, clear your history from that screen when you finish, and use a private window (Incognito or InPrivate) for sensitive tasks. A private window does not save history on that device, but it does not hide you from the websites or your ISP.</p>
<h3>Downloads</h3>
<p>Downloading means copying a file from the internet to your device. Files usually go to the <strong>Downloads</strong> folder. Open the downloads list from the menu (or Ctrl+J) to see them. Download only from sites you trust, and be careful with files ending in <code>.exe</code>, <code>.apk</code> or <code>.zip</code> from unknown sources.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open three tabs: a news site, a weather site and a school or NGO site. Switch between them with the keyboard.</li>
<li>Bookmark the weather site in a new folder called "Daily".</li>
<li>Close all tabs, then use Ctrl+Shift+T to reopen one.</li>
<li>Open History and find the news page you visited.</li>
<li>Download a free PDF (for example a public information leaflet), then find it in your Downloads folder.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Tabs let you keep many pages open; close those you do not need.</li>
<li>Bookmarks save pages; history shows where you have been.</li>
<li>Private windows only stop your device from saving history.</li>
<li>Download only from trusted sources and check the Downloads folder.</li>
</ul>
BODY,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which statement is correct?
- The web and the internet are exactly the same thing.
* The web is one service that runs on the internet.
- The internet is a single website stored in one country.
- The web is the cables, and the internet is the websites.
= The internet is the worldwide network; the web (websites opened with a browser) is only one of the services using it, along with email and video calls.

Q: Your phone shows full Wi-Fi bars but pages will not load. What is the most likely explanation?
- Wi-Fi bars always mean you are connected to the internet.
- The browser has too many bookmarks.
* The router may not be connected to an ISP, or the connection behind it has a problem.
- Your phone's SIM card has been deleted.
= Wi-Fi only links your device to a router. The router still needs a working connection to an ISP before you can reach the internet.

Q: Which activities usually use the MOST mobile data? (choose all that apply)
* Watching HD video
* Downloading a large app update
- Reading a plain text email
- Sending a short text message in a chat app
= Video and large downloads use far more data than text.

Q: Look at this address: https://mybank.com.secure-login.net/home. What is the real domain?
- mybank.com
- home
* secure-login.net
- https
= The real domain is the name just before the ending. Here it is secure-login.net, so this is likely a fake page pretending to be mybank.

Q: What does a padlock and https in the address bar tell you?
- The website owner is certainly honest.
- The website cannot contain viruses.
* The connection between you and the site is encrypted.
- You are in a private window.
= The padlock means the connection is encrypted. Scam sites can use it too, so still read the domain name carefully.

Q: Which shortcut reopens a tab you closed by mistake in most browsers?
- Ctrl+D
- Ctrl+H
* Ctrl+Shift+T
- Ctrl+J
= Ctrl+Shift+T (Cmd+Shift+T on Mac) restores the last closed tab. Ctrl+D bookmarks, Ctrl+H opens history and Ctrl+J opens downloads.

Q: Which setting helps you save mobile data? (choose all that apply)
* Turning on Data Saver mode
* Setting app updates to Wi-Fi only
- Leaving video auto-play switched on
= Data Saver and Wi-Fi-only updates reduce data use, while auto-play video wastes it.
QUIZ,
        ],
        [
            'title'   => 'Module 2: Searching and judging information',
            'summary' => 'How to search well, find different kinds of results and decide what to trust.',
            'lessons' => [
                [
                    'title'   => 'Search engines and better searching',
                    'minutes' => 12,
                    'body'    => <<<'BODY'
<p>After this lesson you will search the web faster and find what you need with fewer tries. This saves data and time, and it helps you find school notes, jobs, prices and health information.</p>
<h3>What a search engine does</h3>
<p>A <strong>search engine</strong>, such as Google, Bing or DuckDuckGo, keeps a huge index of web pages. When you type words, it lists the pages that match best. You can type your words in the address bar of most browsers or on the search engine's own page.</p>
<h3>Choosing good keywords</h3>
<p>Search engines work best with <strong>keywords</strong>, not long stories. Compare these:</p>
<table>
<thead><tr><th>Weak search</th><th>Better search</th></tr></thead>
<tbody>
<tr><td>I want to know how to grow tomatoes at home</td><td>how to grow tomatoes Uganda</td></tr>
<tr><td>exams</td><td>UNEB past papers S4 mathematics</td></tr>
<tr><td>job</td><td>accounts assistant jobs Gulu</td></tr>
</tbody>
</table>
<p>Add details like place, year, subject or file type. If you get too many results, add words. If you get none, remove words or use simpler ones.</p>
<h3>Simple search tricks</h3>
<table>
<thead><tr><th>Trick</th><th>Example</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Quotation marks</td><td><code>"data protection act" Uganda</code></td><td>Finds the exact phrase</td></tr>
<tr><td>Minus sign</td><td><code>jaguar -car</code></td><td>Leaves out pages containing "car"</td></tr>
<tr><td>site:</td><td><code>scholarships site:gov.ug</code></td><td>Searches only inside one website or domain</td></tr>
<tr><td>filetype:</td><td><code>CV template filetype:pdf</code></td><td>Finds only a certain kind of file</td></tr>
<tr><td>OR</td><td><code>laptop OR notebook price</code></td><td>Finds pages with either word</td></tr>
</tbody>
</table>
<h3>Reading the results page</h3>
<p>Some top results are marked <strong>Ad</strong> or <strong>Sponsored</strong>. They are paid adverts, not necessarily the best answers. Read the page title, the address and the short description before you click. Use the tabs under the search box to switch between All, Images, Videos, News and Maps.</p>
<h3>Try it yourself</h3>
<ol>
<li>Search for "weather Gulu this week" and note the result at the top.</li>
<li>Search for a phrase you know, first without quotation marks and then with them, and compare the results.</li>
<li>Use <code>filetype:pdf</code> to find a PDF guide on a topic you like.</li>
<li>Find two results marked Ad or Sponsored, if any appear, and write down how you recognised them.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Use a few specific keywords, not a whole sentence.</li>
<li>Quotation marks, minus, site: and filetype: make searches precise.</li>
<li>Ads can appear at the top of results; check before you click.</li>
<li>If a search fails, change the words and try again.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Images, maps, news and translation',
                    'minutes' => 10,
                    'body'    => <<<'BODY'
<p>This lesson shows you how to use the other search tools, such as images, maps, news and translation. After it you will be able to find directions to a place, check pictures and read a page in another language.</p>
<h3>Searching for images</h3>
<p>Select the <strong>Images</strong> tab after a search to see pictures. You can use tools such as size, colour or usage rights to narrow the results. Remember that most images belong to someone. Seeing a picture online does not mean you may use it freely in your own flyer or website (more on this in a later lesson).</p>
<p>You can also search with a picture. Many search engines have a camera icon or "search by image" option. This helps you check where a photo came from, because old photos are often shared again with a new, false story.</p>
<h3>Using online maps</h3>
<ol>
<li>Open a maps app or website and type the name of a place, such as a market, hospital or office.</li>
<li>Select <strong>Directions</strong> and choose how you travel: walking, car, public transport or motorbike where offered.</li>
<li>Read the estimated time and distance. Maps may show current traffic.</li>
<li>Save useful places, such as your workplace, with a star or label.</li>
</ol>
<p>Maps may not show every small road or every business name in the same detail in all areas, so confirm with a phone call when the trip is important. Some map apps allow you to download an area for offline use, which saves data.</p>
<h3>Finding news</h3>
<p>The <strong>News</strong> tab lists recent articles from many publishers. Compare at least two sources for an important story. Look at the date, because old stories sometimes appear again.</p>
<h3>Translating text</h3>
<p>Online translation tools can translate a word, a sentence or a whole web page between many languages. They are helpful for getting the general meaning, but they can make mistakes, especially with local expressions and with technical words. Do not use machine translation alone for legal, medical or very important documents.</p>
<h3>Try it yourself</h3>
<ol>
<li>Search for a place near you in a maps tool and ask for directions from your home to it. Note the distance and time.</li>
<li>Search the Images tab for "Murchison Falls" and use the tools to show only large pictures.</li>
<li>Open the News tab, choose one story and find the same story from a second publisher. Do they agree?</li>
<li>Translate a short English sentence into another language you know, then translate it back and compare.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Images, Maps, News and Video tabs help you find different kinds of information.</li>
<li>Reverse image search helps you find where a picture came from.</li>
<li>Maps are good guides, but confirm important trips by phone.</li>
<li>Translation is useful for meaning, but check it for important texts.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Fake news and judging sources',
                    'minutes' => 14,
                    'body'    => <<<'BODY'
<p>By the end of this lesson you will be able to ask five simple questions that help you decide whether to believe something you read online. This is important because false messages about health, money, elections and tribes spread quickly on WhatsApp and Facebook, and can cause real harm.</p>
<h3>Types of false information</h3>
<ul>
<li><strong>Misinformation</strong>: false information shared by someone who believes it is true.</li>
<li><strong>Disinformation</strong>: false information created or shared on purpose to mislead.</li>
<li><strong>Scams</strong>: lies meant to take your money or details, such as "You have won a prize, pay a small fee".</li>
<li><strong>Old content in a new story</strong>: a real photo or video used with a false description.</li>
</ul>
<h3>Five questions to ask (the SIFT habit)</h3>
<ol>
<li><strong>Stop.</strong> Do not share at once. Strong feelings such as anger or fear are a sign to slow down.</li>
<li><strong>Who is the source?</strong> Is the author or organisation known? Is it a real news outlet, an official body or an unknown page?</li>
<li><strong>What is the evidence?</strong> Does the story name people, dates and places? Does it link to original documents?</li>
<li><strong>Is it reported elsewhere?</strong> Search for the same news on other trusted sites. A big story is rarely reported by only one page.</li>
<li><strong>What is the date?</strong> Check when it was published and whether it is still true.</li>
</ol>
<h3>Warning signs</h3>
<table>
<thead><tr><th>Sign</th><th>Why it is a concern</th></tr></thead>
<tbody>
<tr><td>"Forward to 10 people or bad luck follows"</td><td>Real information does not threaten you into sharing</td></tr>
<tr><td>Capital letters, many exclamation marks</td><td>Meant to excite emotions, not to inform</td></tr>
<tr><td>No author, no date, no source</td><td>You cannot check it</td></tr>
<tr><td>Cure-all health claims</td><td>Check with a health worker or the Ministry of Health</td></tr>
<tr><td>Spelling errors and an odd web address</td><td>May be a fake copy of a real site</td></tr>
</tbody>
</table>
<h3>Fact-checking</h3>
<p>Search for the claim with the word "fact check" or "hoax". Professional fact-checking organisations publish articles testing popular claims. Also search the picture (reverse image search) to see if it appeared earlier in a different story.</p>
<h3>Try it yourself</h3>
<ol>
<li>Find a viral message you received in a WhatsApp group (or ask a friend for one).</li>
<li>Apply the five questions and write short answers.</li>
<li>Search for the claim with the word "fact check" and note what you find.</li>
<li>Decide: true, false or not sure. Write one sentence explaining your decision.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Pause before sharing, especially if a message makes you angry or afraid.</li>
<li>Check the source, the evidence, other reports and the date.</li>
<li>Messages that demand forwarding are often false.</li>
<li>When in doubt, do not share.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Copyright, citing sources and safe downloading',
                    'minutes' => 12,
                    'body'    => <<<'BODY'
<p>After this lesson you will know when you may use content you find online, how to give credit, and how to download files without picking up viruses. These habits protect you in school, at work and in business.</p>
<h3>What copyright means</h3>
<p><strong>Copyright</strong> is the legal right of a creator to control how their work is copied and used. Writing, photos, music, videos, software and designs are usually protected automatically, even without a copyright symbol. Copying someone's work and presenting it as your own is called <strong>plagiarism</strong>. It can cause failed exams, lost jobs and legal trouble.</p>
<h3>What you may do</h3>
<ul>
<li>Use work in the <strong>public domain</strong> (the copyright has ended).</li>
<li>Use work with an open licence, such as <strong>Creative Commons</strong>, following its conditions (for example, giving credit).</li>
<li>Use free images and music from sites that clearly state they are free to use, and read their rules.</li>
<li>Quote short parts of a text for study, giving credit.</li>
<li>Ask the owner for permission in writing.</li>
</ul>
<h3>Giving credit</h3>
<p>When you use ideas or words from a source, write who made them, the title, the website and the date you accessed it. A simple format is:</p>
<pre><code>Author or organisation. Title of page. Website name. Date of publication. Web address. (Accessed 12 March 2026)</code></pre>
<p>Put quotation marks around exact words. Even when you rewrite in your own words, you must still give credit for the idea.</p>
<h3>Safe downloading</h3>
<p>Downloads can contain <strong>malware</strong>, which is harmful software. Follow these rules:</p>
<ol>
<li>Use official sites and official app stores (for phones, Google Play or the Apple App Store).</li>
<li>Be careful with "free" versions of paid programs, games or films; these often carry viruses.</li>
<li>Check the file name and type. A "photo" that ends in <code>.exe</code> is not a photo.</li>
<li>Keep your antivirus (Windows Security is built into Windows) switched on and updated.</li>
<li>Do not click pop-ups that say "Your phone has a virus, install this now".</li>
<li>Read app permissions. A torch app does not need access to your contacts.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Search for a free image on a site that states its licence. Read the conditions of use.</li>
<li>Write a credit line for it using the format above.</li>
<li>Choose an article and write two sentences about it in your own words, followed by a credit line.</li>
<li>Open your device's Downloads folder and look for any file you do not recognise. Do not open it; ask a trusted person if unsure.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Most online content is protected by copyright, even if it has no symbol.</li>
<li>Use open-licence or permitted content and always give credit.</li>
<li>Plagiarism is presenting someone else's work as your own.</li>
<li>Download only from official, trusted sources and be suspicious of "free" cracked software.</li>
</ul>
BODY,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which search is most likely to give a useful result?
- how can I find out about jobs
* accounts assistant jobs Gulu 2026
- jobs jobs jobs
- please tell me everything about work
= Specific keywords such as the job title, place and year help the search engine find relevant pages.

Q: What does putting words in quotation marks do in a search?
- It translates them into English.
* It searches for that exact phrase.
- It hides adverts.
- It searches only images.
= Quotation marks tell the search engine to find the words together in that order.

Q: You want only PDF files from a government site. Which search fits best?
- filetype:pdf scholarships
* scholarships site:gov.ug filetype:pdf
- scholarships -gov.ug
- "scholarships" pdf OR doc
= site: limits results to one website or domain, and filetype: limits the file type. You can combine them.

Q: A message says: "URGENT!!! Forward to 20 people or the government will block your phone." What is the best action?
- Forward it quickly just in case.
- Reply asking the sender to send more details.
* Pause, check the source and look for reports elsewhere before sharing.
- Delete your phone's apps.
= Threats to force sharing are a classic sign of false information. Stop, check the source and the evidence first.

Q: Which are good ways to check a suspicious claim? (choose all that apply)
* Search for the claim with the words "fact check"
* Look for the same story on other trusted sites
* Check the date and the author
- Count how many people have already shared it
= Number of shares does not make something true. Sources, evidence and independent reports do.

Q: You find a nice photo online and want to use it in your business flyer. What should you do first?
- Use it, because it was on the internet.
- Remove the watermark and use it.
* Check its licence or ask for permission, and give credit if required.
- Change its colours slightly.
= Most images are protected by copyright. Use images with a clear licence, or permission, and follow the conditions.

Q: Which of these is the safest way to install an app on an Android phone?
- Download a cracked version from a forum
- Click a pop-up that says your phone has a virus
* Install it from the official app store after checking the publisher and permissions
- Accept a file from an unknown number
= Official app stores check apps and show permissions. Cracked files and pop-ups often carry malware.
QUIZ,
        ],
        [
            'title'   => 'Module 3: Email from scratch',
            'summary' => 'Create an account, send and organise messages, protect yourself from scams and use email on a phone.',
            'lessons' => [
                [
                    'title'   => 'What email is and creating your account',
                    'minutes' => 14,
                    'body'    => <<<'BODY'
<p>After this lesson you will understand how an email address works and you will have created your own account. An email address is needed for job applications, online learning, banking apps and almost every website, so it is one of the most important digital tools you can have.</p>
<h3>What is email?</h3>
<p><strong>Email</strong> (electronic mail) is a way to send written messages, photos and documents across the internet within seconds. Unlike a chat app, email is designed for longer, more formal messages and keeps a clear record.</p>
<h3>Parts of an email address</h3>
<pre><code>achan.grace@gmail.com</code></pre>
<ul>
<li><code>achan.grace</code> is the <strong>username</strong>, chosen by you.</li>
<li><code>@</code> means "at".</li>
<li><code>gmail.com</code> is the <strong>provider</strong>, the company that stores your mail.</li>
</ul>
<p>Every address is unique. Other free providers include Outlook and Yahoo. Schools and companies may also give you an address with their own name after the @.</p>
<h3>Choosing a professional address</h3>
<p>Employers read your email address. Use your real name, such as <code>grace.achan@...</code>, and avoid nicknames like <code>sweetgirl2005</code> or <code>kingofgulu</code>. Do not include your birth year if you do not wish to share your age.</p>
<h3>Creating a Gmail account</h3>
<ol>
<li>Open your browser and go to Google's sign-in page, then select <strong>Create account</strong> and choose "For my personal use".</li>
<li>Enter your first name and last name.</li>
<li>Enter your date of birth and gender as requested.</li>
<li>Choose your email address. If your first choice is taken, add a dot or number, or choose one of the suggestions.</li>
<li>Create a strong password: at least 12 characters, mixing words, numbers and symbols. Do not use your name or birthday.</li>
<li>Add a phone number and recovery email if asked. These help you get back into your account if you forget the password.</li>
<li>Read and accept the terms, then select <strong>Next</strong>.</li>
</ol>
<p>Write your password in a safe place or use a password manager, and never share it, not even with a friend or someone claiming to be from the email company.</p>
<h3>Signing in and out</h3>
<p>To sign in, go to the Gmail page or app and enter your address and password. On a shared computer or phone, always select your profile picture and choose <strong>Sign out</strong> when you finish.</p>
<h3>Try it yourself</h3>
<ol>
<li>Decide on a professional username using your real name.</li>
<li>Create a new email account, or use one you already have.</li>
<li>Add a recovery phone number or recovery email.</li>
<li>Sign out and sign in again to practise.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>An email address has a username, the @ sign and a provider.</li>
<li>Choose a professional username based on your real name.</li>
<li>Use a strong password and add recovery details.</li>
<li>Always sign out on shared devices.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Writing, sending, replying and forwarding',
                    'minutes' => 14,
                    'body'    => <<<'BODY'
<p>When you finish this lesson you will be able to write and send an email, reply to messages and forward them correctly. These are the actions you will use every day.</p>
<h3>Composing a new message</h3>
<ol>
<li>In Gmail, select <strong>Compose</strong> (usually at the top left, or a pencil button on a phone).</li>
<li>In the <strong>To</strong> field, type the recipient's address. Check the spelling, because one wrong letter sends your mail to a stranger.</li>
<li>Type a short, clear <strong>Subject</strong>, for example "Request for a meeting on Friday".</li>
<li>Write your message in the large body area.</li>
<li>Select <strong>Send</strong>.</li>
</ol>
<p>Gmail offers an <strong>Undo</strong> link for a few seconds after you send. Use it if you notice a mistake straight away.</p>
<h3>To, Cc and Bcc</h3>
<table>
<thead><tr><th>Field</th><th>Meaning</th><th>Who sees it</th></tr></thead>
<tbody>
<tr><td>To</td><td>People who must act or reply</td><td>Everyone sees these addresses</td></tr>
<tr><td>Cc (carbon copy)</td><td>People who should be informed</td><td>Everyone sees these addresses</td></tr>
<tr><td>Bcc (blind carbon copy)</td><td>People who receive a copy privately</td><td>Other recipients cannot see Bcc addresses</td></tr>
</tbody>
</table>
<p>Use Bcc when you send to many people who do not know each other, such as a notice to parents. This protects their privacy.</p>
<h3>Reply, Reply all and Forward</h3>
<ul>
<li><strong>Reply</strong> answers only the sender.</li>
<li><strong>Reply all</strong> answers the sender and everyone else who received the message. Use it only when everyone needs your answer.</li>
<li><strong>Forward</strong> sends the message to someone new. Check that the conversation does not contain private information you should not share.</li>
</ul>
<h3>Attachments</h3>
<p>An <strong>attachment</strong> is a file sent with an email. Select the <strong>paperclip</strong> icon, choose the file and wait until it finishes uploading. Mention the attachment in your text ("I have attached my CV"). Most providers allow about 25 MB per email; larger files should be shared through a cloud link instead. Name files clearly, for example <code>Grace-Achan-CV.pdf</code>, and use PDF when you do not want others to change the document.</p>
<h3>Drafts</h3>
<p>Gmail saves unfinished messages automatically in <strong>Drafts</strong>. You can return to them later.</p>
<h3>Try it yourself</h3>
<ol>
<li>Send an email to yourself (or a friend) with a clear subject and a three-line message.</li>
<li>Attach a small file such as a photo or a PDF.</li>
<li>Reply to the message you received, then forward it to another address.</li>
<li>Send a message with two friends in Bcc and check that they cannot see each other.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Check the recipient's address and always write a clear subject.</li>
<li>To is for action, Cc for information, Bcc for private copies.</li>
<li>Use Reply all only when everyone needs the answer.</li>
<li>Mention attachments in your message and keep files small, or share a link.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Organising your inbox: labels, search, signatures and contacts',
                    'minutes' => 12,
                    'body'    => <<<'BODY'
<p>After this lesson you will be able to keep your inbox tidy, find any message quickly and make your emails look professional with a signature. A messy inbox makes you miss important messages such as interview invitations.</p>
<h3>The Gmail folders</h3>
<table>
<thead><tr><th>Place</th><th>What it holds</th></tr></thead>
<tbody>
<tr><td>Inbox</td><td>New messages</td></tr>
<tr><td>Starred</td><td>Messages you marked as important to you</td></tr>
<tr><td>Sent</td><td>Messages you have sent</td></tr>
<tr><td>Drafts</td><td>Unfinished messages</td></tr>
<tr><td>Spam</td><td>Messages Gmail thinks are junk</td></tr>
<tr><td>Trash</td><td>Deleted messages, removed after about 30 days</td></tr>
</tbody>
</table>
<h3>Labels instead of folders</h3>
<p>A <strong>label</strong> works like a folder, but one message can have more than one label. To create one, open the menu on the left, choose <strong>Create new label</strong> and name it, for example "Applications" or "School". To apply it, open or select a message and use the <strong>Labels</strong> button. You can then click the label on the left to see all messages in it.</p>
<h3>Archive, delete and star</h3>
<ul>
<li><strong>Archive</strong> removes a message from the inbox but keeps it; you can still find it by search.</li>
<li><strong>Delete</strong> moves it to Trash.</li>
<li><strong>Star</strong> marks messages you want to deal with soon.</li>
</ul>
<h3>Searching your mail</h3>
<p>Click the search box at the top. You can type a name or word, or use helpers:</p>
<pre><code>from:grace
has:attachment
subject:interview
after:2026/01/01</code></pre>
<p>You can combine them, for example <code>from:grace has:attachment</code>.</p>
<h3>Signatures</h3>
<p>A <strong>signature</strong> is text added automatically to the end of your messages. Open <strong>Settings</strong> (the gear icon), then <strong>See all settings</strong>, find <strong>Signature</strong>, create one and save. Keep it short:</p>
<pre><code>Grace Achan
Assistant Accountant
Phone: 07xx xxx xxx</code></pre>
<h3>Contacts</h3>
<p>When you reply to a person, Gmail may save them in your contacts. You can also open Google Contacts from the apps menu to add names, phone numbers and groups. Then, when you type a name in the To field, the address completes itself.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create two labels: "Learning" and "Jobs".</li>
<li>Apply the right label to at least two messages.</li>
<li>Search for messages that have attachments.</li>
<li>Create a signature with your name and phone number, then send yourself a test email.</li>
<li>Add one contact with a name and email address.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Labels group messages; one message can have several.</li>
<li>Archive keeps messages but clears your inbox.</li>
<li>Search helpers such as from: and has:attachment find mail fast.</li>
<li>A short signature makes your email look professional.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Spam, phishing and email on your phone',
                    'minutes' => 15,
                    'body'    => <<<'BODY'
<p>By the end of this lesson you will recognise the most common email scams, know what to do when you see one, and be able to use email on your phone. Scam emails cost people their savings and accounts every day, so this is a lesson to take seriously.</p>
<h3>Spam and phishing</h3>
<p><strong>Spam</strong> is unwanted bulk email, mostly adverts. <strong>Phishing</strong> is a scam email that pretends to come from a bank, mobile money company, employer or email provider in order to trick you into giving a password, PIN or money. The word sounds like "fishing": the scammer throws out bait.</p>
<h3>Signs of a phishing email</h3>
<ul>
<li>It creates panic: "Your account will be closed in 24 hours".</li>
<li>It asks for a password, PIN, OTP code or bank details. Real companies never ask for these by email.</li>
<li>The sender's address looks odd, such as <code>support@gmai1-security.com</code>.</li>
<li>It uses a general greeting like "Dear customer" and has spelling errors.</li>
<li>It promises a prize, a job or an inheritance that you did not expect.</li>
<li>It has an attachment or link you did not expect.</li>
</ul>
<h3>What to do</h3>
<ol>
<li>Do not click the links or open the attachments.</li>
<li>Hover your mouse over a link (without clicking) to see where it really goes. On a phone, press and hold the link to preview it.</li>
<li>If you are unsure, contact the company using a phone number or website you already know, not the one in the email.</li>
<li>Use <strong>Report spam</strong> or <strong>Report phishing</strong> in the message menu, then delete it.</li>
<li>If you already gave your password, change it at once, then sign in and review your account's security.</li>
</ol>
<h3>Protecting your account</h3>
<p>Turn on <strong>2-step verification</strong> in your Google account security settings. After your password, you also confirm with your phone, so a thief with only your password cannot get in. Never use the same password on many sites.</p>
<h3>Email on a phone</h3>
<ol>
<li>On Android, the Gmail app is usually installed. On an iPhone, you can install Gmail from the App Store or use the Mail app.</li>
<li>Open the app and sign in with your address and password.</li>
<li>Tap the <strong>Compose</strong> button to write, and the paperclip to attach a photo or file.</li>
<li>Swipe a message to archive or delete it, depending on your settings.</li>
<li>Allow notifications only for what you need, to save battery and avoid distractions.</li>
</ol>
<h3>A word about Outlook</h3>
<p>Outlook is another popular email program, common in offices. It has the same ideas: Inbox, Sent Items, folders, attachments and a calendar. If you can use Gmail, you can learn Outlook quickly; the buttons are in slightly different places.</p>
<h3>Try it yourself</h3>
<ol>
<li>Look in your Spam folder and find one message. List two reasons it was marked as spam.</li>
<li>Read this example and mark every warning sign: "Dear user, your bank account is locked. Click here and send your PIN today."</li>
<li>Turn on 2-step verification for your email account.</li>
<li>Install the email app on your phone, sign in and send a test message.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Phishing emails use panic and urgency to steal passwords and money.</li>
<li>Never give a PIN or password by email; verify through a number you already trust.</li>
<li>2-step verification gives strong extra protection.</li>
<li>The same email account works on your phone and your computer.</li>
</ul>
BODY,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: In the address amina.k@mailhost.com, what is the provider?
- amina.k
- @
* mailhost.com
- amina
= The part after the @ is the provider (the mail service); the part before is the username.

Q: Which email username looks most professional for a job application?
- sweetboy256
- kingofgulu
* peter.okello
- peter_is_cool_2005
= Real names look professional and help employers find and remember you.

Q: You are sending a notice to 60 parents who do not know each other. Where should you put their addresses?
- To
- Cc
* Bcc
- Subject
= Bcc hides the recipients' addresses from each other and protects their privacy.

Q: What does Reply all do?
- Sends your answer only to the sender.
* Sends your answer to the sender and everyone else on the message.
- Forwards the message to your contacts.
- Deletes the original message.
= Reply all reaches everyone included in the original message. Use it only when everyone needs the answer.

Q: Which of these help you find an old email? (choose all that apply)
* Typing from:name in the search box
* Using has:attachment
- Putting it into a different font
= Search helpers find messages by sender, attachments, subject and date.

Q: An email says your bank account will close in 24 hours unless you click a link and enter your PIN. What should you do?
- Click quickly before it closes.
- Reply with your PIN to prove who you are.
* Do not click; contact the bank using a number or website you already know, and report the email.
- Forward it to all your friends.
= This is a typical phishing message with panic and a request for a PIN. Banks never ask for your PIN by email.

Q: What does 2-step verification do?
- It makes your emails load faster.
- It deletes spam automatically.
* It asks for a second proof, such as a code on your phone, after your password.
- It lets friends use your password.
= With 2-step verification, a thief who learns your password still cannot sign in without your phone.

Q: Which is the best action if you already typed your password on a fake page?
- Wait to see what happens.
* Change that password immediately and check your account security.
- Delete your phone.
- Create a second fake page.
= Change the password at once on the real site and review the account's security. Change it anywhere else you used the same password.
QUIZ,
        ],
        [
            'title'   => 'Module 4: Professional communication',
            'summary' => 'Write emails that get results, apply for jobs by email and use chat and social media with good manners.',
            'lessons' => [
                [
                    'title'   => 'Email etiquette, subject lines and tone',
                    'minutes' => 12,
                    'body'    => <<<'BODY'
<p>After this lesson you will be able to write emails that are clear, polite and easy to act on. People often judge you by how you write. A good email can help you win a job, a customer or a scholarship.</p>
<h3>The structure of a good email</h3>
<ol>
<li><strong>Subject line</strong>: tells the reader what the email is about.</li>
<li><strong>Greeting</strong>: "Dear Mr Okello," or "Hello Grace,".</li>
<li><strong>Opening</strong>: say why you are writing in one sentence.</li>
<li><strong>Details</strong>: the key facts, short paragraphs, one idea in each.</li>
<li><strong>Request or next step</strong>: say clearly what you need and by when.</li>
<li><strong>Closing</strong>: "Kind regards," followed by your name and contact details.</li>
</ol>
<h3>Writing subject lines</h3>
<table>
<thead><tr><th>Weak</th><th>Strong</th></tr></thead>
<tbody>
<tr><td>Hello</td><td>Request for a reference letter by 15 May</td></tr>
<tr><td>Urgent!!!</td><td>Meeting moved to Thursday 10 a.m.</td></tr>
<tr><td>(empty)</td><td>Application: Field Officer, Gulu</td></tr>
</tbody>
</table>
<p>A good subject line is short (usually under about eight words), specific and mentions the action or date.</p>
<h3>Choosing the right tone</h3>
<p><strong>Tone</strong> is the feeling that your words give. On paper there is no face or voice, so a message that you meant as friendly can seem rude. Use polite words such as "please" and "thank you", keep sentences short and avoid sarcasm or anger.</p>
<ul>
<li>Do not type in ALL CAPITALS; it looks like shouting.</li>
<li>Avoid too many exclamation marks and emojis in formal emails.</li>
<li>Do not use text-message spelling such as "u" and "pls" with people you do not know.</li>
<li>Read your email aloud before sending.</li>
<li>If you are angry, wait an hour before you reply.</li>
</ul>
<h3>Other manners</h3>
<ul>
<li>Reply within one or two working days. If you need longer, say so briefly.</li>
<li>Check names, dates and attachments before sending.</li>
<li>Do not forward private emails without permission.</li>
<li>Do not write what you would be ashamed to see on a notice board; email can be forwarded.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Rewrite this weak subject line to a strong one: "Hi".</li>
<li>Write a four-sentence email asking your teacher or supervisor for a short meeting. Use a greeting, a reason, a request and a closing.</li>
<li>Read it aloud and remove any unfriendly or unclear words.</li>
<li>Send it to a friend and ask whether the tone feels polite.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Use a clear subject line and a simple structure.</li>
<li>Keep paragraphs short and say what you want and by when.</li>
<li>Tone matters: be polite, avoid capital letters and anger.</li>
<li>Check the email before you press Send.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Writing a job application email',
                    'minutes' => 15,
                    'body'    => <<<'BODY'
<p>When you finish this lesson you will be able to write a short, professional job application email with a CV attached. Many employers in Uganda now ask for applications by email, and many applications are rejected because of careless mistakes.</p>
<h3>Before you write</h3>
<ol>
<li>Read the job advert carefully and find the exact email address, the subject line they ask for and the closing date.</li>
<li>Prepare your CV and cover letter as PDF files. Name them clearly, such as <code>Peter-Okello-CV.pdf</code>.</li>
<li>Attach any documents that the employer requested, and nothing extra.</li>
<li>Check that your own email address is professional.</li>
</ol>
<h3>The email itself</h3>
<p>You can either paste a short cover message in the body and attach the CV, or attach a cover letter and write a short note. Keep it brief. Here is a sample:</p>
<pre><code>Subject: Application for Field Officer position - Peter Okello

Dear Hiring Manager,

I am writing to apply for the Field Officer position advertised on 3 May 2026. I hold a Diploma in Community Development and have two years of experience working with youth groups in Gulu.

I have attached my CV and cover letter. I would be glad to attend an interview at a time that suits you.

Thank you for your time and consideration.

Kind regards,
Peter Okello
Phone: 07xx xxx xxx
Email: peter.okello@example.com</code></pre>
<h3>Checklist before you send</h3>
<ul>
<li>The subject line follows the advert's instructions.</li>
<li>You wrote the manager's name if the advert gave it, spelled correctly.</li>
<li>The attachments are present, open correctly and are not too large.</li>
<li>No spelling mistakes; use the spell checker and ask a friend to read it.</li>
<li>Your phone number is correct and switched on.</li>
</ul>
<h3>After sending</h3>
<p>Keep a copy and note the date. If you hear nothing after a week or two, a short polite follow-up is acceptable: ask whether they received your application and if more information is needed. Check your inbox and spam folder regularly, as interview invitations may land there. Answer them promptly.</p>
<h3>Common mistakes</h3>
<ul>
<li>Sending a blank email with only an attachment.</li>
<li>Using "To whom it may concern" when a name is available.</li>
<li>Attaching a photo of a printed CV instead of a PDF document.</li>
<li>Applying from an unprofessional email address.</li>
<li>Copying the same text for every job without changing the job title.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Find a real job advert online or in a newspaper.</li>
<li>Draft an application email using the sample as a guide, and write the subject line exactly as the advert requires.</li>
<li>Attach any PDF as a stand-in for your CV and send the draft to yourself.</li>
<li>Check that the attachment opens on your phone.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Follow the advert's instructions exactly.</li>
<li>Write a short message, attach PDFs and name files clearly.</li>
<li>Proofread, and make sure your contact details are correct.</li>
<li>Check your inbox and spam for replies.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Formal and informal writing, out-of-office and busy inboxes',
                    'minutes' => 12,
                    'body'    => <<<'BODY'
<p>By the end of this lesson you will know how to change your writing style to suit the reader, set an automatic reply when you are away and keep control of a busy inbox. These habits make you look reliable at work.</p>
<h3>Formal and informal writing</h3>
<table>
<thead><tr><th>Situation</th><th>Style</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Employer, lecturer, official, new customer</td><td>Formal</td><td>"Dear Madam, I would like to request an appointment."</td></tr>
<tr><td>Close colleague or friend</td><td>Informal</td><td>"Hi Sam, can we meet tomorrow?"</td></tr>
<tr><td>Team you know well</td><td>Semi-formal</td><td>"Hello team, here is the plan for Monday."</td></tr>
</tbody>
</table>
<p>If you are unsure, begin formally. You can become more relaxed when the other person does. Use full words rather than slang, and write "Kind regards" instead of "Cheers" for new contacts.</p>
<h3>Setting an out-of-office reply</h3>
<p>An <strong>out-of-office</strong> (vacation) reply automatically answers people who write to you while you are away. In Gmail, open <strong>Settings</strong>, then <strong>See all settings</strong>, and scroll to <strong>Vacation responder</strong>. Switch it on, choose the first and last day, write a subject and message, and save. A good message is short:</p>
<pre><code>Thank you for your email. I am away from 10 to 14 June and will reply when I return on 15 June. For urgent matters please contact Sarah at sarah@example.com.</code></pre>
<h3>Managing a busy inbox</h3>
<ol>
<li><strong>Set times to check mail</strong>, for example morning, midday and end of day, rather than every minute.</li>
<li><strong>Handle each message once</strong>: reply now if it takes two minutes, label it for later if longer, archive it if finished, delete it if useless.</li>
<li><strong>Use labels and stars</strong> for what is important.</li>
<li><strong>Unsubscribe</strong> from newsletters you never read, using the link at the bottom of the message.</li>
<li><strong>Use filters</strong> (in Settings or the search options) to label certain senders automatically.</li>
<li>Aim for an inbox that holds only messages that need action.</li>
</ol>
<h3>Replying well</h3>
<p>Answer the question that was asked, quote only what is needed and keep the same subject so that the conversation stays together. If a reply will take time, send a short note such as "Received, I will reply by Friday".</p>
<h3>Try it yourself</h3>
<ol>
<li>Rewrite the informal sentence "Hey boss, I can't come tmrw, I'm sick" as a formal message.</li>
<li>Set up a vacation responder for tomorrow only, then switch it off after testing.</li>
<li>Unsubscribe from one newsletter you do not read.</li>
<li>Clear ten messages from your inbox using archive, delete or labels.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Match your style to the reader; begin formally when unsure.</li>
<li>An out-of-office reply says when you will return and who to contact.</li>
<li>Check email at planned times and handle each message once.</li>
<li>Unsubscribe, label and filter to keep your inbox calm.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Chat tools, WhatsApp for business and netiquette',
                    'minutes' => 14,
                    'body'    => <<<'BODY'
<p>After this lesson you will be able to choose the right channel for a message and behave well on chat apps and social media. Many Ugandan businesses reach customers through WhatsApp, and employers look at what you post, so good online manners protect your reputation.</p>
<h3>Choosing the right channel</h3>
<table>
<thead><tr><th>Channel</th><th>Best for</th></tr></thead>
<tbody>
<tr><td>Email</td><td>Formal messages, applications, documents, anything needing a record</td></tr>
<tr><td>SMS</td><td>Very short notices and reminders, works without data</td></tr>
<tr><td>WhatsApp</td><td>Quick questions and updates, photos, voice notes</td></tr>
<tr><td>Team chat apps (Slack, Microsoft Teams chat)</td><td>Daily teamwork in channels, with files and threads</td></tr>
<tr><td>Phone or video call</td><td>Urgent, sensitive or complicated matters</td></tr>
</tbody>
</table>
<h3>WhatsApp Business basics</h3>
<p>WhatsApp Business is a free app for small businesses. It lets you add a business profile with your address, hours and description, a catalogue of products, quick replies and automatic greeting or away messages. To use it professionally:</p>
<ul>
<li>Use a clear profile photo or logo and a business name.</li>
<li>Reply politely and mention your opening hours.</li>
<li>Save common answers (such as prices) as quick replies.</li>
<li>Ask permission before adding people to a group or broadcast list.</li>
<li>Avoid sending customers' details to others.</li>
</ul>
<h3>Professional use of chat</h3>
<ul>
<li>Begin with a greeting and your name when you contact someone new.</li>
<li>Write your whole request in one message instead of sending many tiny messages.</li>
<li>Do not send messages late at night unless it is urgent.</li>
<li>Use voice notes only when the person is happy to listen; keep them short.</li>
<li>In groups, stay on topic and do not reply to everyone when only one person needs the answer.</li>
</ul>
<h3>Netiquette on social media</h3>
<p><strong>Netiquette</strong> means good manners on the internet.</p>
<ol>
<li>Think before posting; nothing online is truly deleted.</li>
<li>Do not share other people's photos or private details without permission.</li>
<li>Respect other opinions; disagree politely and never insult or threaten.</li>
<li>Do not spread rumours or unchecked news.</li>
<li>Keep personal and work accounts separate if you can, and check your privacy settings.</li>
<li>Remember that employers and schools may look at your public posts.</li>
</ol>
<p>Cyberbullying and sharing private images of others are serious and may break the law. If you are harassed, save screenshots, block the person and report them to the platform and to a trusted adult or authority.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose the best channel for each: telling a client your price list, reminding a friend about a meeting in an hour, sending a signed contract.</li>
<li>Review the privacy settings of one social media account and make sure only what you choose is public.</li>
<li>Write a polite first message to a new business contact on WhatsApp.</li>
<li>Look through your last ten public posts and ask whether you would be happy for an employer to read them.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Pick the channel that fits the message: email for formal, chat for quick, calls for urgent.</li>
<li>WhatsApp Business offers a profile, catalogue and quick replies.</li>
<li>Netiquette means respect, privacy and thinking before you post.</li>
<li>Block and report abuse; keep evidence.</li>
</ul>
BODY,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which subject line is best for an email asking your supervisor to approve leave?
- Hello
- URGENT!!!
* Leave request for 10-14 June
- Please read this email now
= A good subject line is specific and tells the reader the topic and the dates.

Q: Which of these makes an email seem rude or angry?
- Saying please and thank you
* Writing in ALL CAPITAL LETTERS
- Using the person's name
- Keeping paragraphs short
= Capital letters read like shouting. Polite words, names and short paragraphs help.

Q: How should you name the CV file you attach to a job application?
- document1.pdf
- mycv_final_final.pdf
* Peter-Okello-CV.pdf
- IMG_20260101.pdf
= A name with your own name tells the employer immediately whose CV it is.

Q: You are unsure how formal to be with someone you have never met. What should you do?
- Use slang to seem friendly.
* Begin formally and relax later if they do.
- Write only in capital letters.
- Send a voice note.
= Beginning formally is safe and shows respect.

Q: What should an out-of-office message include? (choose all that apply)
* The dates you are away
* When you will reply
* Who to contact for urgent matters
- Your password
= An out-of-office message helps people know when to expect an answer and who else can help. Never share passwords.

Q: Which channel is best for sending a signed contract?
- A single WhatsApp voice note
- SMS
* Email with the document attached
- A social media comment
= Email keeps a record and can carry documents. It is better for formal agreements.

Q: What is netiquette?
- A type of internet cable
- A tool for finding Wi-Fi
* Good manners and respectful behaviour online
- A way to hide your IP address
= Netiquette means polite, respectful behaviour on the internet.

Q: A person is sending you threatening messages on social media. What is the best response?
- Reply with threats of your own.
* Save screenshots, block the person and report them.
- Delete your account at once without telling anyone.
- Share their messages in a public group.
= Keep evidence, block and report. Do not respond with abuse and tell a trusted adult or the authorities if you feel unsafe.
QUIZ,
        ],
        [
            'title'   => 'Module 5: Meetings and collaboration online',
            'summary' => 'Join and host video calls, schedule with calendars, share files by link and prepare for a virtual class.',
            'lessons' => [
                [
                    'title'   => 'Video calls: joining and hosting',
                    'minutes' => 14,
                    'body'    => <<<'BODY'
<p>After this lesson you will be able to join a video meeting from a link and start your own. Video calls are used for classes, interviews, family contact and business meetings, so they are a core skill for study and work.</p>
<h3>Popular tools</h3>
<table>
<thead><tr><th>Tool</th><th>Notes</th></tr></thead>
<tbody>
<tr><td>Zoom</td><td>Widely used for classes and events; join with a link or a meeting ID</td></tr>
<tr><td>Google Meet</td><td>Works in a browser with a Google account; common in schools</td></tr>
<tr><td>Microsoft Teams</td><td>Used by many companies; combines chat, files and meetings</td></tr>
</tbody>
</table>
<p>All of them follow similar steps. Free plans can have time limits or feature limits, which may change, so check the tool's current rules.</p>
<h3>Joining a meeting</h3>
<ol>
<li>Open the invitation email or message and select the meeting <strong>link</strong>. Some meetings also give an ID number and a passcode.</li>
<li>If asked, allow the browser or app to use your camera and microphone.</li>
<li>Check your name; use your real name so the host recognises you.</li>
<li>Look at the preview, then select <strong>Join</strong> or <strong>Ask to join</strong>. The host may need to admit you from a waiting room.</li>
</ol>
<p>On a phone, installing the app is better than using a browser. Join 5 minutes early to test sound and picture.</p>
<h3>Hosting a meeting</h3>
<ol>
<li>Open the tool and choose <strong>New meeting</strong> or <strong>Schedule</strong>.</li>
<li>Copy the meeting link or choose to send the invitation by email.</li>
<li>Join the meeting yourself and admit participants as they arrive.</li>
<li>Welcome everyone, explain how you will take questions and start on time.</li>
<li>At the end, choose <strong>End</strong> or <strong>Leave</strong>. Hosts can usually end the meeting for everybody.</li>
</ol>
<h3>Bandwidth and connection tips</h3>
<ul>
<li>A one-hour video meeting can use a lot of data. Turn your camera off if the connection is weak.</li>
<li>Close other apps and downloads.</li>
<li>Sit near the Wi-Fi router, or find a place with a good signal.</li>
<li>Charge your device or plug it in.</li>
<li>If you freeze or drop out, leave the call and rejoin through the same link.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Open Google Meet or another video tool and create a new meeting for yourself.</li>
<li>Test your camera and microphone, and check that you can see and hear yourself.</li>
<li>Copy the link and send it to a friend or family member.</li>
<li>Start the call, say hello, then leave the meeting properly.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>You join a meeting by clicking the link; use your real name.</li>
<li>Allow camera and microphone access and test them early.</li>
<li>Hosts create the meeting, share the link and admit people.</li>
<li>Turn off the camera to save data if the connection is poor.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Audio, video, screen sharing and meeting manners',
                    'minutes' => 12,
                    'body'    => <<<'BODY'
<p>In this lesson you will learn to control your sound and camera, share your screen, and behave professionally in an online meeting. These small actions decide whether people see you as prepared and respectful.</p>
<h3>The meeting controls</h3>
<p>At the bottom or top of the meeting window you will usually find these buttons:</p>
<table>
<thead><tr><th>Control</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Microphone</td><td>Mutes or unmutes your sound</td></tr>
<tr><td>Camera</td><td>Turns your video on or off</td></tr>
<tr><td>Present / Share screen</td><td>Shows your screen or a window to everyone</td></tr>
<tr><td>Chat</td><td>Opens a text panel for questions and links</td></tr>
<tr><td>Raise hand / Reactions</td><td>Signals you want to speak</td></tr>
<tr><td>Participants</td><td>Shows who is in the meeting</td></tr>
<tr><td>Leave / End</td><td>Exits the call</td></tr>
</tbody>
</table>
<p>The icons are similar in all tools, and a red line through an icon means it is off.</p>
<h3>Good audio and video</h3>
<ul>
<li>Stay <strong>muted</strong> when you are not speaking, to cut background noise.</li>
<li>Use headphones if you have them; they reduce echo.</li>
<li>Put light in front of you, such as a window, not behind you.</li>
<li>Place the camera at eye level and keep your background tidy, or use a blurred background if offered.</li>
<li>Dress as you would for a meeting in person.</li>
</ul>
<h3>Sharing your screen</h3>
<ol>
<li>Prepare the file or page before the meeting and close private tabs, chats and notifications.</li>
<li>Select <strong>Share screen</strong> (or <strong>Present now</strong>) and choose a single window, a tab or your entire screen.</li>
<li>If sharing a video or music, tick the option to share computer sound.</li>
<li>Tell people what they will see.</li>
<li>Select <strong>Stop sharing</strong> when finished.</li>
</ol>
<p>Share a single window rather than the whole screen when possible, so people do not see private messages.</p>
<h3>Meeting manners</h3>
<ul>
<li>Arrive on time and say hello in the chat or aloud.</li>
<li>Do not talk over others; use the raise-hand button or write in the chat.</li>
<li>Look at the camera sometimes, not only at the screen.</li>
<li>Avoid eating, side conversations and other tasks during the meeting.</li>
<li>Ask permission before recording; people must know.</li>
<li>Thank people at the end and share notes or actions afterwards.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Start a test meeting alone and practise muting and unmuting.</li>
<li>Open a document or photo and share only that window, then stop sharing.</li>
<li>Check your lighting: move so that light falls on your face.</li>
<li>Write three rules you would give participants at the start of a class.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Mute when not speaking and test your sound and picture.</li>
<li>Good light and a tidy background improve the call.</li>
<li>Share one window and close private items first.</li>
<li>Respect others: do not interrupt, and ask before recording.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Calendars, invitations and sharing files with links',
                    'minutes' => 14,
                    'body'    => <<<'BODY'
<p>By the end of this lesson you will be able to schedule an event in an online calendar, invite people, and share a file with a link instead of a heavy attachment. These tools stop confusion and save data.</p>
<h3>Online calendars</h3>
<p>An <strong>online calendar</strong>, such as Google Calendar or Outlook Calendar, stores your appointments in your account, so you see the same events on your phone and your computer. It can remind you before an event starts.</p>
<h3>Creating an event and inviting people</h3>
<ol>
<li>Open your calendar and select <strong>Create</strong> or click on a time slot.</li>
<li>Enter a title that says exactly what the event is, such as "Youth group planning meeting".</li>
<li>Choose the date, start time and end time. Check your time zone.</li>
<li>Add <strong>guests</strong> by typing their email addresses.</li>
<li>Add a video meeting link if the event is online, and a location if it is physical.</li>
<li>Set a <strong>notification</strong>, such as 30 minutes before.</li>
<li>Select <strong>Save</strong> and choose whether to send invitations.</li>
</ol>
<p>Guests can answer Yes, No or Maybe. You will see their replies, which helps you plan.</p>
<h3>Good invitations</h3>
<ul>
<li>Include the purpose in the title or description.</li>
<li>Give an agenda or the documents people should read.</li>
<li>Choose a time that suits everyone and respects working hours.</li>
<li>Update the event, rather than creating a new one, if the time changes.</li>
</ul>
<h3>Sharing files with links</h3>
<p>Big files are better shared through cloud storage such as Google Drive or OneDrive. The file stays in one place, and everyone sees the latest version.</p>
<ol>
<li>Upload the file to your cloud storage.</li>
<li>Right-click it (or open its menu) and choose <strong>Share</strong>.</li>
<li>Decide who may open it: only named people, or anyone with the link.</li>
<li>Choose the permission: <strong>Viewer</strong> (can only read), <strong>Commenter</strong> or <strong>Editor</strong> (can change).</li>
<li>Copy the link or send it by email.</li>
</ol>
<p>Choose the strictest option that works. Do not give "anyone with the link" access to documents with personal or financial information. You can stop sharing later from the same Share window.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a calendar event for a study session tomorrow with a 15-minute reminder.</li>
<li>Invite one friend and check that they receive it.</li>
<li>Upload a small document to your online storage.</li>
<li>Share it with a friend as Viewer, then change the permission to Commenter.</li>
<li>Remove the sharing when you finish the test.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Online calendars send invitations and reminders and work on every device.</li>
<li>Write clear event titles, add guests, a link or location, and a reminder.</li>
<li>Share a link instead of attaching big files.</li>
<li>Pick the minimum permission needed: Viewer, Commenter or Editor.</li>
</ul>
BODY,
                ],
                [
                    'title'   => 'Online forms, remote teamwork and joining a LAF Academy class',
                    'minutes' => 14,
                    'body'    => <<<'BODY'
<p>After this lesson you will be able to collect information with an online form, work with a team who are in different places, and prepare to join a virtual class at LAF Academy. This lesson also ends with a practical checklist for the whole course.</p>
<h3>Online forms</h3>
<p>An <strong>online form</strong>, such as Google Forms or Microsoft Forms, lets people answer questions on their phones. Answers are collected automatically in a table. This is useful for registration, feedback, orders and simple surveys.</p>
<ol>
<li>Open the forms tool and choose a new blank form.</li>
<li>Give the form a title and a short description.</li>
<li>Add questions; choose types such as short answer, multiple choice or tick boxes.</li>
<li>Mark essential questions as <strong>required</strong>.</li>
<li>Use the preview option to test the form yourself.</li>
<li>Choose <strong>Send</strong> to copy the link, then share it by email or WhatsApp.</li>
</ol>
<p>Ask only for the information you really need. Do not collect passwords, bank details or other sensitive data in a form.</p>
<h3>Working together remotely</h3>
<ul>
<li>Agree where files live (one shared folder) and how to name them.</li>
<li>Set a regular meeting time and keep a shared list of tasks with names and deadlines.</li>
<li>Write short notes after each meeting and send them to everyone.</li>
<li>Use comments in shared documents instead of sending many copies by email.</li>
<li>Be patient with time zones, slow connections and power cuts.</li>
</ul>
<h3>Joining a LAF Academy virtual class</h3>
<ol>
<li>Check the class date, time and link in your invitation or on the Academy page.</li>
<li>The day before, test your device, camera, microphone and the video tool.</li>
<li>Charge your device and, if you can, plug in a power bank. Make sure you have enough data or access to Wi-Fi.</li>
<li>Find a quiet place and have a notebook ready.</li>
<li>Join 5 to 10 minutes early using your real name.</li>
<li>Stay muted until you wish to speak; use the raise-hand button or chat for questions.</li>
<li>After the class, finish the practice activity and note questions for next time.</li>
</ol>
<h3>Final practical checklist</h3>
<ul>
<li>I can explain the internet, the web and what an ISP does.</li>
<li>I can use tabs, bookmarks and the downloads folder.</li>
<li>I can search with good keywords and test a claim before sharing it.</li>
<li>I have a professional email address with a strong password and 2-step verification.</li>
<li>I can send an email with an attachment, using To, Cc and Bcc correctly.</li>
<li>I can spot a phishing email.</li>
<li>I can write a job application email.</li>
<li>I can join, host and share my screen in a video meeting.</li>
<li>I can create a calendar event and share a file by link with the right permission.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Create a three-question form asking friends for feedback on a topic you choose.</li>
<li>Test it, then send the link to two people and see their answers arrive.</li>
<li>Go through the practical checklist and tick every item you can do. Practise any item you cannot tick.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Online forms collect answers in one table; ask only for what you need.</li>
<li>Remote teams need clear places for files, tasks and meetings.</li>
<li>Prepare your device, data and place before a virtual class.</li>
<li>Use the checklist to find skills that need more practice.</li>
</ul>
BODY,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What should you do before joining an important video meeting?
- Nothing; just click the link at the last second.
* Test your camera, microphone and connection a few minutes early.
- Delete your browser history.
- Turn on every notification you have.
= Testing early gives you time to fix problems such as blocked permissions or weak signal.

Q: Your internet connection is weak during a video call. What helps most?
- Turn on every screen effect.
* Switch off your camera and close other apps.
- Increase the volume.
- Start a download in the background.
= Video is the biggest use of data. Turning off your camera and closing other apps reduces the load.

Q: Why should you share a single window instead of your entire screen?
- It makes the sound louder.
- The meeting lasts longer.
* Others will not see your private messages and other open items.
- It is the only way to present a document.
= Sharing one window limits what the audience can see.

Q: When should you stay muted?
- Always, even when you are asked a question
* When you are not speaking
- Only at the end of the meeting
- Never; background noise helps people concentrate
= Muting reduces background noise and echo. Unmute when you speak.

Q: Which permission lets a person read a shared file but not change it?
- Editor
- Owner
* Viewer
- Host
= A Viewer can open and read; an Editor can change; a Commenter can add comments.

Q: What is a good reason to share a file by link rather than by attachment?
* The file is large and everyone should see the latest version
* You can change or remove access later
- Links cannot be forwarded under any circumstances
= Cloud links save mailbox space, keep one latest copy and can be turned off. They can still be forwarded if the access allows it.

Q: Which information should you NOT collect in an online form?
- Name and class
- Preferred workshop date
* Passwords or bank PINs
- Feedback comments
= Forms are not safe places for secrets. Ask only for the information you really need.

Q: Before a LAF Academy virtual class, which is the best preparation?
- Join at the start with no preparation and a low battery.
* Check the link and time, test your device, charge it and join a few minutes early.
- Wait until the class has ended to find the link.
- Use someone else's name on your account.
= Preparing your device, data and link means you will not miss the beginning of the class.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: The internet is best described as:
- A single large website
* A worldwide network of connected networks
- A kind of web browser
- The software on your phone
= The internet is the global network; the web, email and video calls are services that use it.

Q: What does an ISP do?
- Creates web pages for you
- Cleans viruses from your phone
* Sells you access to the internet
- Stores your passwords
= An internet service provider (such as a mobile network or fixed-line company) gives you access to the internet.

Q: Which is NOT a way to reduce mobile data use?
- Turning on Data Saver
- Setting updates to Wi-Fi only
* Leaving video auto-play on
- Lowering video quality
= Auto-play video uses a lot of data.

Q: In https://shop.example.ug/prices/list.html, what is the path?
- https
- shop.example.ug
* /prices/list.html
- .ug
= The path leads to the exact page inside the website.

Q: Which address is most likely a fake copy of a bank site?
- www.mybank.com
- mybank.com/contact
* mybank.com.login-check.net
- www.mybank.co.ug
= The real domain in the third address is login-check.net, not mybank.

Q: What does Ctrl+D normally do in a browser?
- Opens downloads
* Bookmarks the page
- Closes the tab
- Opens a private window
= Ctrl+D (Cmd+D on Mac) adds a bookmark.

Q: A private (Incognito) window:
- Hides everything you do from the websites and your ISP
- Makes downloads safe
* Does not save your history on that device
- Stops all adverts
= It only keeps your device from storing the history; it does not make you invisible.

Q: Which search finds the exact phrase "climate change" together?
- climate -change
* "climate change"
- climate OR change
- site:climate
= Quotation marks search for the exact phrase.

Q: How can you limit a search to PDF files?
- pdf@ search
- only:pdf
* filetype:pdf
- type=pdf
= The filetype: helper restricts results to one file type.

Q: A message full of capital letters says a cure for a disease has been found and you must share it with 10 people. What is the best step?
- Share it to be safe.
* Check the source and look for reports from trusted health sources first.
- Reply to ask for the sender's password.
- Delete your WhatsApp.
= Emotional pressure and cure claims are warning signs. Check with a health worker or an official source before sharing.

Q: Which of these help to check a photo that is going around? (choose all that apply)
* Reverse image search
* Searching the claim with "fact check"
- Counting the likes
= Likes do not prove truth. Searching the image and fact-checkers can show if it is old or used out of context.

Q: Copying someone else's essay and presenting it as your own is called:
- Bookmarking
- Phishing
* Plagiarism
- Streaming
= Plagiarism is presenting another person's work as yours.

Q: What does a Creative Commons licence usually allow?
- Anything, with no conditions
* Use of the work under stated conditions, often including credit
- Only use by governments
- Nothing at all
= Open licences allow use if you follow their terms, such as giving credit to the creator.

Q: Which is the safest place to get a phone app?
- A link sent by an unknown person
* The official app store
- A pop-up advert
- A forum offering "cracked" versions
= Official stores check apps and show permissions.

Q: In the email address ruth.a@example.com, "example.com" is the:
- Username
- Password
* Provider (domain)
- Subject
= The provider or domain is after the @ sign.

Q: Which password is strongest?
- ruth1998
- password123
* Dusk-Mango-Bridge-47!
- 12345678
= Long passwords with a mix of words, numbers and symbols are harder to guess; avoid names and birth years.

Q: You want colleagues to know about an email but not to act on it. Where do you put them?
- To
* Cc
- Bcc only
- Subject
= Cc is for people who should be informed.

Q: Why use Bcc when writing to many people who do not know each other?
- It makes the email arrive faster.
* It hides their addresses from each other.
- It adds attachments automatically.
- It prevents replies.
= Bcc protects the privacy of recipients.

Q: Which Gmail feature lets one message appear under several headings?
- Folders
- Spam
* Labels
- Drafts
= One message can carry more than one label.

Q: What is a typical sign of a phishing email? (choose all that apply)
* A threat that your account will close today
* A request for your PIN or password
* A link that goes to an unfamiliar web address
- A polite greeting using your full name from someone you know
= Phishing uses panic, requests for secret details and false links.

Q: What does 2-step verification add?
- A second email address
* A second proof of identity, such as a code on your phone
- A bigger inbox
- Automatic spam deletion
= It protects your account even if someone learns your password.

Q: What is the best subject line for a job application?
- Hi
- Please help
* Application for Accounts Assistant - Grace Achan
- Urgent!!!
= It is specific and follows the format employers expect.

Q: A job advert says to send your CV as a PDF. Which action is correct?
- Send a photo of a printed CV
* Attach a PDF named with your own name
- Send only a link to your social media
- Send an editable file with no message
= Follow the instructions exactly, attach a clear file and include a short message.

Q: Which tone is appropriate in a first email to a company manager?
- "Hey boss, give me a job lol"
* "Dear Mr Mugisha, I am writing to apply for..."
- "URGENT!!! REPLY NOW"
- "yo wassup"
= Polite, formal language is best for a first contact.

Q: What does the out-of-office reply tell senders?
- Your password
* That you are away, when you will return and who to contact
- That their message was deleted
- Your location in real time
= It sets expectations about when you will respond.

Q: Which is the best channel for a long, formal request that needs a record?
- A voice note
- A WhatsApp status
* Email
- A comment on a social media post
= Email keeps a clear record and supports attachments.

Q: Which is good netiquette?
- Sharing a friend's private photo without asking
* Disagreeing politely and thinking before posting
- Posting in capital letters to show passion
- Spreading a rumour to get attention
= Respect, privacy and thoughtfulness are the heart of good manners online.

Q: Before sharing your screen in a meeting you should:
- Open many private chats so people see you are busy
* Close private tabs and notifications and share only the window you need
- Mute everyone
- Leave the meeting
= Prepare your screen so that private information is not shown.

Q: When joining a meeting by link, what is the waiting room for?
- To play music
* To let the host admit people before they enter
- To store recordings
- To block all participants
= The host can control who enters the meeting.

Q: You share a salary spreadsheet with a colleague so they can look at it but not change it. What permission do you give?
- Editor
* Viewer
- Owner
- Anyone with the link as Editor
= Viewer permission lets a person read without changing the file. Choose the lowest access that works.
QUIZ,
];
