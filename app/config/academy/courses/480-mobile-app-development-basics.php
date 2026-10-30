<?php
return [
    'slug'        => 'mobile-app-development-basics',
    'title'       => 'Mobile App Development: Build Your First App',
    'category'    => 'coding',
    'level'       => 'intermediate',
    'icon'        => 'laptop',
    'sort'        => 480,
    'summary'     => 'Plan, build, test and publish a simple Android and iPhone app with React Native and Expo, designed for low data, slow networks and the realities of East Africa.',
    'description' => '<p>Most people in Uganda and across Africa meet the internet first on a phone. That makes mobile apps one of the most useful things a young developer can learn to build: a savings-group tracker, a school notice board, a shop price list, a farm record book. This course takes you from the idea on paper to a working app on a real phone.</p><p>You will learn how mobile apps work and the main ways of building them, how to design for low data, cheap Android phones and patchy networks, and how to plan an app before writing code. Then you build with React Native and Expo, using JavaScript: screens, state, lists, forms, navigation, calling a web API and saving data on the device. You will handle offline use, permissions, privacy and security, test on a real phone and an emulator, keep your app small and fast, and publish it. The course ends with a capstone project.</p><p>The code samples are short and are meant to be typed and tried. Tools change quickly, so the course teaches ideas that stay useful and tells you where to check the official documentation.</p>',
    'outcomes'    => [
        'Explain the difference between native apps, cross-platform apps, web apps and progressive web apps, and choose between them',
        'Design an app for low data, slow networks, small screens and low-end Android phones',
        'Plan an app by defining the problem, the users, a short feature list and simple wireframes',
        'Set up a free development environment and run an Expo app on a real phone',
        'Build screens with React Native components, state, lists, forms and navigation',
        'Fetch data from a web API and show loading, error and empty states',
        'Store data on the device and keep an app useful when the network is down',
        'Apply permissions, privacy and basic security practice, and test and tune an app for speed and size',
        'Prepare an app for publishing and describe realistic costs, updates and ways to earn from it',
    ],
    'requirements' => ['Completed JavaScript for Beginners (javascript-basics) or equivalent: variables, functions, arrays, objects and arrow functions', 'A computer (Windows, Mac or Linux) with a stable internet connection for setting up tools', 'An Android phone or iPhone to test on is very helpful, but an emulator can be used instead'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Expo documentation', 'url' => 'https://docs.expo.dev/'],
        ['title' => 'React Native documentation', 'url' => 'https://reactnative.dev/docs/getting-started'],
        ['title' => 'React Navigation documentation', 'url' => 'https://reactnavigation.org/'],
        ['title' => 'MDN Web Docs: Progressive web apps', 'url' => 'https://developer.mozilla.org/docs/Web/Progressive_web_apps'],
        ['title' => 'Android Developers', 'url' => 'https://developer.android.com/'],
    ],
    'modules' => [
        [
            'title'   => 'Module 1: Understanding and planning mobile apps',
            'summary' => 'How mobile apps are built, how to design for African realities, and how to plan an app before coding.',
            'lessons' => [
                [
                    'title'   => 'How mobile apps work: native, cross-platform, web and PWA',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to name the four main ways to build a mobile app and choose the right one for a given project. This matters because the wrong choice can cost months of work, and many small teams in Uganda cannot afford to build the same app twice.</p>

<h3>What is a mobile app?</h3>
<p>A mobile app is a program that runs on a phone or tablet. Most phones in East Africa run <strong>Android</strong> (made by Google, used by many brands). A smaller group use <strong>iOS</strong>, the system on iPhones. The two systems use different programming languages and tools, which is why the choice of approach is important.</p>

<h3>The four approaches</h3>
<p><strong>Native apps</strong> are written separately for each system: Kotlin (or Java) for Android, Swift for iPhone. They have full access to the phone and the best speed, but you build and maintain two apps.</p>
<p><strong>Cross-platform apps</strong> use one code base to produce an app for both Android and iPhone. React Native (JavaScript) and Flutter (Dart) are well-known examples. You write once and share most of the code.</p>
<p><strong>Web apps</strong> are websites designed for phones. They open in the browser, need no installation and work on any phone, but they have limited access to phone features and need a network for most tasks.</p>
<p><strong>Progressive web apps (PWAs)</strong> are web apps with extra abilities: the user can add them to the home screen, they can work offline by saving files in the browser, and they can open full screen like an app.</p>

<table>
<thead><tr><th>Approach</th><th>Languages</th><th>Install from</th><th>Offline</th><th>Phone features</th><th>Effort</th></tr></thead>
<tbody>
<tr><td>Native</td><td>Kotlin, Swift</td><td>App store</td><td>Excellent</td><td>Full</td><td>High (two apps)</td></tr>
<tr><td>Cross-platform</td><td>JavaScript, Dart</td><td>App store or APK file</td><td>Very good</td><td>Most</td><td>Medium</td></tr>
<tr><td>Web app</td><td>HTML, CSS, JavaScript</td><td>Just a link</td><td>Weak</td><td>Few</td><td>Low</td></tr>
<tr><td>PWA</td><td>HTML, CSS, JavaScript</td><td>Link, then "Add to home screen"</td><td>Good</td><td>Some</td><td>Low to medium</td></tr>
</tbody>
</table>

<h3>Mini-case: a school notice app</h3>
<p>A secondary school in Mbarara wants parents to see notices on their phones. Most parents have low-cost Android phones with little storage and expensive data. A PWA could work: no store account, no big download, easy updates. But the head teacher also wants push alerts and wants the app to be found in Google Play. The team chooses a cross-platform app, because the school has one JavaScript teacher and needs Android first, iPhone later. The decision came from users, skills and budget, not from what is fashionable.</p>

<blockquote><p><strong>Quick check:</strong> A market-vendor group wants a price list that relatives can open instantly from a WhatsApp link, with no installing. Which approach fits best?</p><p>Answer: A web app or PWA, because it opens from a link and needs no download or store account.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why does a team that knows only JavaScript often pick a cross-platform framework over two native apps?</p><p>Answer: One code base in a language they already know covers both Android and iPhone, saving time and money.</p></blockquote>

<h3>How to decide</h3>
<ul>
<li>Do users need it in an app store, or is a link enough?</li>
<li>Does it need the camera, contacts, Bluetooth or background work?</li>
<li>Which phones do your users really have?</li>
<li>Which skills does your team already have?</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Open any website on your phone and, in the browser menu, look for "Add to Home screen" or "Install app".</li>
<li>Write down three apps on your phone that you use daily.</li>
<li>For each, decide whether it could be a PWA or needs to be native, and give one reason.</li>
<li>Pick one local problem you know (for example boda boda trip records) and choose an approach for it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Native gives the most power; cross-platform shares code; web apps and PWAs need no store.</li>
<li>Choose from users, features, skills and budget.</li>
<li>This course builds a cross-platform app with React Native and Expo.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Designing for African realities',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to list the design rules that make an app work for people with expensive data, cheap phones and unreliable networks. This matters because an app that is perfect on a new phone in an office may be useless in a village trading centre.</p>

<h3>The real conditions</h3>
<ul>
<li><strong>Data costs money.</strong> A user who buys 500 MB of bundles will delete an app that eats it.</li>
<li><strong>Networks come and go.</strong> A signal may drop in a taxi, a valley or a power cut.</li>
<li><strong>Low-end Android phones.</strong> Many phones have little storage, small memory (RAM) and slow processors, and run older Android versions.</li>
<li><strong>Small screens and bright sun.</strong> Text must be big, with strong contrast.</li>
<li><strong>Shared phones and mixed literacy.</strong> Some users share a phone or are not confident readers of English.</li>
<li><strong>Mobile money is the payment method,</strong> not bank cards.</li>
<li><strong>Many languages.</strong> Luganda, Swahili, Runyankole, Acholi and others are used daily.</li>
</ul>

<h3>Design rules that follow</h3>
<table>
<thead><tr><th>Reality</th><th>Design response</th></tr></thead>
<tbody>
<tr><td>Costly data</td><td>Small downloads, compressed images, fetch only what is needed, a "data saver" option</td></tr>
<tr><td>Weak network</td><td>Offline-first: save data on the phone, sync later, show clear status</td></tr>
<tr><td>Low-end phones</td><td>Few animations, short lists, small app size, test on a cheap phone</td></tr>
<tr><td>Small screen, sunlight</td><td>Large text (16 px or more), big tap buttons, strong colour contrast</td></tr>
<tr><td>Mobile money</td><td>Support MTN MoMo and Airtel Money flows, clear confirmation messages</td></tr>
<tr><td>Local languages</td><td>Keep text in one place so it can be translated; use simple words and icons</td></tr>
</tbody>
</table>

<h3>Offline-first thinking</h3>
<p><strong>Offline-first</strong> means the app is designed to work without internet and treats the network as a bonus. The user can open the app, see their last data and even add new records. When the network returns, the app sends changes to the server. A savings-group treasurer who records contributions at a meeting under a tree should never see "No connection" instead of a form.</p>

<h3>Mini-case: a farm record app</h3>
<p>A team builds an app for coffee farmers. Version one downloads 40 photos on every launch and needs a login before showing anything. Farmers complain about data costs and the app crashing on phones with 2 GB of RAM. Version two loads text first, shows small thumbnails on request, caches recent records and lets farmers record harvests offline. Ratings improve, because the app now fits the conditions.</p>

<blockquote><p><strong>Quick check:</strong> Your app shows a list of 200 products, each with a 1 MB photo. What is the first fix for users with limited data?</p><p>Answer: Use small compressed images (or thumbnails) and load pictures only when needed, because large images are the biggest data cost.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A treasurer has no signal during a meeting. What should the app do when she saves a contribution?</p><p>Answer: Save it on the phone, show it as "waiting to sync", and send it when the network returns.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Take an app you use. Turn on aeroplane mode and open it. Write what still works.</li>
<li>Check how much storage the app takes in your phone settings.</li>
<li>Write five design rules for an app your cousin in a village would use.</li>
<li>Ask one person with an older phone what annoys them most about apps.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Design for expensive data, weak networks, cheap phones and mobile money.</li>
<li>Offline-first means the app works without a network and syncs later.</li>
<li>Test on the weakest phone your users have, not the best one you own.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Planning an app: problem, users, features and wireframes',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to turn an idea into a one-page plan with a problem statement, user description, short feature list and simple wireframes. This matters because most failed apps solved no clear problem, and a plan costs only a pencil and paper.</p>

<h3>Step 1: the problem</h3>
<p>Write one sentence in this shape: "[Who] struggles to [do what] because [reason]." For example: "Savings-group treasurers struggle to track who has paid each month because they use loose notebooks that get lost." A good problem is specific and real. If you cannot find three people who have it, find another problem.</p>

<h3>Step 2: the users</h3>
<p>Describe one typical user: age, phone type, data habits, language and comfort with technology. For example: Grace, 38, treasurer of a 25-member group, uses a low-cost Android phone, buys small bundles and prefers Luganda and English.</p>

<h3>Step 3: the features</h3>
<p>List everything you could build, then sort it. Keep a <strong>minimum viable product</strong> (MVP): the smallest app that solves the main problem. Everything else waits for version two.</p>
<table>
<thead><tr><th>Feature</th><th>Must have (MVP)</th><th>Later</th></tr></thead>
<tbody>
<tr><td>Add a member</td><td>Yes</td><td></td></tr>
<tr><td>Record a contribution</td><td>Yes</td><td></td></tr>
<tr><td>See who has not paid</td><td>Yes</td><td></td></tr>
<tr><td>Send SMS reminders</td><td></td><td>Later</td></tr>
<tr><td>Mobile money payments inside the app</td><td></td><td>Later</td></tr>
</tbody>
</table>

<h3>Step 4: wireframes</h3>
<p>A <strong>wireframe</strong> is a rough sketch of a screen showing where things go. Draw boxes on paper: a title at the top, a list in the middle, a big button at the bottom. Draw the screens in order and connect them with arrows to show how a user moves. This is called a <strong>screen flow</strong>.</p>
<pre><code>[Members list]  --tap member--&gt;  [Member detail]
      |                               |
  tap "+"                       tap "Add payment"
      v                               v
[Add member form]              [Payment form]</code></pre>

<h3>Mini-case: the plan that saved weeks</h3>
<p>A student planned a "super school app" with timetable, fees, chat, results and games. After talking to teachers, the real pain was that parents missed notices. The MVP became one list of notices with a read marker. It was built in two weekends and used by three classes, and only then did he add fee reminders.</p>

<blockquote><p><strong>Quick check:</strong> Which is a better problem statement: "Make a great app for farmers" or "Coffee farmers cannot easily see what they sold last season because records are on paper"?</p><p>Answer: The second one, because it names who, what and why, so you can design and test a solution.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why keep the first version small?</p><p>Answer: A small MVP is finished sooner, can be tested with real users, and shows which features are truly needed.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write one problem sentence for a problem in your community.</li>
<li>Describe one user in three lines.</li>
<li>List eight features and circle the three that make the MVP.</li>
<li>Sketch three wireframes on paper and show them to a friend. Ask them what each screen is for.</li>
<li>Change the sketch based on their confusion.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Start with a specific problem and a real user.</li>
<li>Build the MVP first; list the rest for later.</li>
<li>Paper wireframes and screen flows are cheap ways to find mistakes early.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A hardware shop owner wants customers to check prices from a link sent on WhatsApp, with nothing to install. Which approach fits best?
- A native Android app written in Kotlin
* A web app or progressive web app
- A native iPhone app written in Swift
- A desktop program for Windows
= A web app opens from a link and needs no installation or store account, which suits this case.

Q: Your team knows only JavaScript and must reach both Android and iPhone users. What is the most sensible approach?
- Write two native apps and learn two new languages first
- Build a desktop program and ask users to buy laptops
* Use a cross-platform framework such as React Native
- Wait until you have a bigger team
= A cross-platform framework lets you reuse JavaScript knowledge and one code base for both systems.

Q: Which ability do PWAs add to a basic website?
- Opening in a browser
- Having a web address
* Being added to the home screen and working offline from saved files
- Using HTML and CSS
= PWAs add home-screen install and offline support through saved files on top of a normal website.

Q: Users of your app complain that it eats their bundles. Which change helps most?
- Add more animations
* Compress images, fetch only what is needed and cache data
- Make the app download all data at every start
- Increase the font size
= Reducing the amount of data downloaded directly cuts the cost to users.

Q: A treasurer records contributions at a meeting with no signal. What does an offline-first app do?
- Shows an error and asks her to try again later
- Refuses to open without internet
* Saves the record on the phone and syncs it when the network returns
- Sends the record by SMS automatically without asking
= Offline-first apps keep working locally and send changes later.

Q: Which statement is the best problem statement?
- Build an app for everyone in Uganda
- Make the best school app
* Parents miss school notices because notices are pinned on a board they rarely visit
- Use React Native for a project
= A good problem statement names who has the problem, what it is and why it happens.

Q: What belongs in a minimum viable product (MVP)?
- Every idea the team has had
* Only the smallest set of features that solves the main problem
- Only the colours and logo
- The features that are hardest to build
= The MVP is the smallest useful version that can be tested with real users.

Q: What does a wireframe show?
- The final colours and photographs
- The server settings
* A rough layout of a screen and where items go
- The app store listing
= A wireframe is a simple sketch of layout, made before design or code.
QUIZ,
        ],
        [
            'title'   => 'Module 2: Your first screens with React Native and Expo',
            'summary' => 'Set up free tools, run an app on a phone, and build screens with components, state, lists and forms.',
            'lessons' => [
                [
                    'title'   => 'Setting up a free development environment',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to install the free tools you need and run a working app on your own phone. This matters because the first run is the hardest step: once an app appears on your screen, every later lesson becomes easier.</p>

<h3>What you need</h3>
<ul>
<li><strong>Node.js</strong> (the LTS, or "long-term support", version) from the official Node.js website. It lets you run JavaScript tools on your computer.</li>
<li><strong>A code editor.</strong> Visual Studio Code is free and popular.</li>
<li><strong>Expo.</strong> Expo is a free set of tools and services built around React Native. It handles the difficult setup so you can start writing app code at once.</li>
<li><strong>The Expo Go app</strong> on your phone, free from Google Play or the App Store. It runs your project while you develop.</li>
<li>A phone and computer on the <strong>same Wi-Fi network</strong> (a phone hotspot also works).</li>
</ul>

<h3>Create and run the project</h3>
<p>Open a terminal (in VS Code: <strong>Terminal</strong> menu, then <strong>New Terminal</strong>) and check Node is installed:</p>
<pre><code>node --version
npm --version</code></pre>
<p>Both should print a version number. Then create a new project from the plain "blank" template and start it:</p>
<pre><code>npx create-expo-app@latest SavingsApp --template blank
cd SavingsApp
npx expo start</code></pre>
<p>The terminal shows a QR code. On Android, open Expo Go and choose to scan the QR code. On iPhone, scan it with the normal Camera app. Your app opens on the phone with a short line of text. Edit the text in <code>App.js</code>, save, and the phone updates in a second. This is called <strong>fast refresh</strong>.</p>

<h3>The project folder</h3>
<table>
<thead><tr><th>File or folder</th><th>Purpose</th></tr></thead>
<tbody>
<tr><td><code>App.js</code></td><td>The first screen of your app. You will edit this most</td></tr>
<tr><td><code>package.json</code></td><td>Lists the libraries (dependencies) and commands of your project</td></tr>
<tr><td><code>app.json</code></td><td>App name, icon, version and other settings</td></tr>
<tr><td><code>node_modules/</code></td><td>Downloaded libraries. It is large; never edit it or share it</td></tr>
<tr><td><code>assets/</code></td><td>Images and icons</td></tr>
</tbody>
</table>

<h3>If it does not connect</h3>
<ol>
<li>Check that phone and computer share the same network. Some school or office Wi-Fi blocks devices from talking to each other; use your phone's hotspot instead.</li>
<li>Try restarting with <code>npx expo start --clear</code>.</li>
<li>If a firewall asks, allow Node.js on private networks.</li>
<li>If the QR code still fails, try the tunnel option: <code>npx expo start --tunnel</code>. It is slower but works across networks. You may be asked to install an extra package.</li>
</ol>

<h3>No phone? Alternatives</h3>
<p>You can run the app in an <strong>Android emulator</strong> (a virtual phone on your computer) using Android Studio, or in a browser with <code>npx expo start --web</code> after installing the web packages that Expo suggests. The emulator needs a decent computer, so a real phone is easier on weak laptops.</p>

<blockquote><p><strong>Quick check:</strong> You run <code>npx expo start</code> but the phone cannot load the app. The laptop is on school Wi-Fi and the phone is on mobile data. What is the first fix?</p><p>Answer: Put both on the same network (for example, the phone's hotspot), because Expo Go must reach the laptop on the local network.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You change text in <code>App.js</code> and save. What should happen on the phone?</p><p>Answer: The screen updates within a moment through fast refresh, with no reinstall needed.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Install Node.js LTS and VS Code, then run <code>node --version</code>.</li>
<li>Create the SavingsApp project with the command above.</li>
<li>Run <code>npx expo start</code> and open the app in Expo Go.</li>
<li>Change the text in <code>App.js</code> to "Karibu, my first app" and save.</li>
<li>Confirm the phone shows the new text.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>You need Node.js, an editor, Expo and a phone with Expo Go; all are free.</li>
<li>Phone and laptop usually must be on the same network.</li>
<li>Fast refresh shows each change in seconds.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Building screens: components, styles and layout',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a clean screen using React Native components and style it with flexbox. This matters because every screen in every app is made from a few simple building blocks, and knowing them lets you build anything.</p>

<h3>Components are building blocks</h3>
<p>A <strong>component</strong> is a function that returns a piece of screen. React Native supplies core components that become real Android and iPhone controls. Unlike the web there is no <code>div</code> or <code>p</code>; all text must be inside <code>Text</code>.</p>
<table>
<thead><tr><th>Component</th><th>Use</th><th>Web equivalent</th></tr></thead>
<tbody>
<tr><td><code>View</code></td><td>A box that groups other items</td><td>div</td></tr>
<tr><td><code>Text</code></td><td>Any text</td><td>p, span</td></tr>
<tr><td><code>Image</code></td><td>A picture</td><td>img</td></tr>
<tr><td><code>TextInput</code></td><td>Typing box</td><td>input</td></tr>
<tr><td><code>Pressable</code></td><td>Anything the user can tap</td><td>button</td></tr>
<tr><td><code>ScrollView</code></td><td>A box that scrolls</td><td>overflow: scroll</td></tr>
</tbody>
</table>

<h3>A first screen</h3>
<pre><code>import { View, Text, StyleSheet } from 'react-native';

export default function App() {
  return (
    &lt;View style&#61;{styles.container}&gt;
      &lt;Text style&#61;{styles.title}&gt;Umoja Savings Group&lt;/Text&gt;
      &lt;Text style&#61;{styles.total}&gt;Total saved: UGX 1,250,000&lt;/Text&gt;
    &lt;/View&gt;
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, padding: 20, backgroundColor: '#ffffff' },
  title: { fontSize: 24, fontWeight: 'bold', color: '#1a1a1a' },
  total: { fontSize: 18, marginTop: 8, color: '#2d6a4f' },
});</code></pre>
<p>Styles are JavaScript objects, with names in camelCase (<code>backgroundColor</code>, not <code>background-color</code>). Numbers have no unit; they are density-independent pixels, so sizes look similar on different screens.</p>

<h3>Layout with flexbox</h3>
<p>Layout uses <strong>flexbox</strong>. By default a <code>View</code> lays its children in a column (top to bottom). Key properties:</p>
<ul>
<li><code>flexDirection: 'row'</code> places children side by side.</li>
<li><code>justifyContent</code> spaces children along the main direction (for example <code>'space-between'</code>).</li>
<li><code>alignItems</code> aligns them across (for example <code>'center'</code>).</li>
<li><code>flex: 1</code> makes a box fill the available space.</li>
</ul>

<h3>Buttons that are easy to tap</h3>
<p>Fingers are bigger than mouse pointers. Make tap targets at least about 44 by 44 units and add space between them. Use <code>Pressable</code> with padding:</p>
<pre><code>&lt;Pressable style&#61;{styles.btn} onPress&#61;{() =&gt; alert('Saved')}&gt;
  &lt;Text style&#61;{styles.btnText}&gt;Save&lt;/Text&gt;
&lt;/Pressable&gt;</code></pre>

<blockquote><p><strong>Quick check:</strong> You write <code>&lt;p&gt;Hello&lt;/p&gt;</code> in a React Native screen. Will it work?</p><p>Answer: No. React Native has no HTML tags; use the Text component for text.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Two items must appear side by side in a row, one at each edge. Which style properties do you set on the parent?</p><p>Answer: flexDirection: 'row' and justifyContent: 'space-between'.</p></blockquote>

<h3>Mini-case: readable in sunlight</h3>
<p>A student built a light grey text on a white background and found nobody could read it outside. He changed to dark text (#1a1a1a) on white, raised the size to 18 and the app became usable at a bus stop. Contrast and size are design features, not decoration.</p>

<h3>Try it yourself</h3>
<ol>
<li>Replace the contents of <code>App.js</code> with the screen above and use your group name.</li>
<li>Add a second <code>Text</code> with the date.</li>
<li>Put two Text items in a row using <code>flexDirection: 'row'</code>.</li>
<li>Add a Pressable "Save" button with padding of 14 and a green background.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use View, Text, Image, TextInput and Pressable instead of HTML tags.</li>
<li>Styles are JavaScript objects made with StyleSheet.create.</li>
<li>Flexbox controls layout; keep text big and tap targets large.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'State: making screens react to the user',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use state so that your screen changes when the user taps or types. This matters because an app that cannot remember anything is just a poster, and almost every useful feature depends on state.</p>

<h3>What is state?</h3>
<p><strong>State</strong> is data that can change while the app runs and that affects what is shown: a counter, text in a box, a list of contributions. In React you create state with the <code>useState</code> hook. When state changes, React draws the screen again automatically.</p>
<pre><code>import { useState } from 'react';
import { View, Text, Pressable, StyleSheet } from 'react-native';

export default function App() {
  const [count, setCount] = useState(0);

  return (
    &lt;View style&#61;{styles.box}&gt;
      &lt;Text style&#61;{styles.big}&gt;Members present: {count}&lt;/Text&gt;
      &lt;Pressable style&#61;{styles.btn} onPress&#61;{() =&gt; setCount(count + 1)}&gt;
        &lt;Text style&#61;{styles.btnText}&gt;Add one&lt;/Text&gt;
      &lt;/Pressable&gt;
    &lt;/View&gt;
  );
}</code></pre>
<p><code>useState(0)</code> gives you a pair: the current value (<code>count</code>) and a function to change it (<code>setCount</code>). Never change <code>count</code> directly (for example <code>count = 5</code>); always call <code>setCount</code>, otherwise the screen will not update.</p>

<h3>Rules for state</h3>
<ol>
<li>Call <code>useState</code> at the top of a component, not inside loops or if statements.</li>
<li>State holds numbers, text, true/false, objects and arrays.</li>
<li>For arrays and objects, make a <em>new</em> copy instead of changing the old one.</li>
</ol>
<pre><code>const [members, setMembers] = useState(['Grace', 'Joseph']);

// add one: make a new array
setMembers([...members, 'Aisha']);

// remove one: filter makes a new array
setMembers(members.filter((m) =&gt; m !== 'Joseph'));</code></pre>

<h3>Controlled text boxes</h3>
<p>A text box is usually tied to state so the app always knows what was typed:</p>
<pre><code>const [name, setName] = useState('');
&lt;TextInput value={name} onChangeText&#61;{setName} placeholder="Member name" /&gt;</code></pre>

<h3>Conditional display</h3>
<p>Show something only when a condition is true using <code>&amp;&amp;</code> or a ternary:</p>
<pre><code>{count &gt; 20 ? &lt;Text&gt;Quorum reached&lt;/Text&gt; : &lt;Text&gt;Waiting for members&lt;/Text&gt;}</code></pre>

<table>
<thead><tr><th>Need</th><th>Use</th></tr></thead>
<tbody>
<tr><td>A value that changes and updates the screen</td><td>useState</td></tr>
<tr><td>A fixed value that never changes</td><td>A normal const</td></tr>
<tr><td>A value passed in from a parent</td><td>A prop</td></tr>
</tbody>
</table>

<h3>Mini-case: the counter that did not move</h3>
<p>A learner wrote <code>let total = 0;</code> and a button that did <code>total = total + 500</code>. The number on screen never changed. The fix was <code>const [total, setTotal] = useState(0)</code> and <code>setTotal(total + 500)</code>. Plain variables do not tell React to redraw; state does.</p>

<blockquote><p><strong>Quick check:</strong> Why does <code>count = count + 1</code> not update the screen, while <code>setCount(count + 1)</code> does?</p><p>Answer: Only the setter function tells React that state changed, so React redraws the screen.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You have <code>items</code> in state and want to add one more. Is <code>items.push('x')</code> the right approach?</p><p>Answer: No. Create a new array, for example setItems([...items, 'x']), so React can see the change.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Build the counter above and test it on your phone.</li>
<li>Add a "Reset" button that sets the count back to 0.</li>
<li>Add a TextInput tied to state and show what is typed in a Text below it.</li>
<li>Show "Quorum reached" in green when the count is above 20.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>State is changing data; update it only with its setter function.</li>
<li>Copy arrays and objects instead of changing them in place.</li>
<li>Tie text boxes to state so you always know what the user typed.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Lists and forms: showing and capturing data',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to show a list of items efficiently and build a form that adds a new item. This matters because lists and forms are the heart of most business apps: customers, orders, members, payments.</p>

<h3>Lists with FlatList</h3>
<p>You could use <code>ScrollView</code> and draw every item, but with hundreds of items that is slow, especially on cheap phones. <code>FlatList</code> draws only the rows visible on screen, which saves memory.</p>
<pre><code>const DATA = [
  { id: '1', name: 'Grace', paid: 50000 },
  { id: '2', name: 'Joseph', paid: 0 },
];

&lt;FlatList
  data={DATA}
  keyExtractor={(item) =&gt; item.id}
  renderItem={({ item }) =&gt; (
    &lt;View style&#61;{styles.row}&gt;
      &lt;Text style&#61;{styles.name}&gt;{item.name}&lt;/Text&gt;
      &lt;Text&gt;UGX {item.paid.toLocaleString()}&lt;/Text&gt;
    &lt;/View&gt;
  )}
  ListEmptyComponent={&lt;Text&gt;No members yet. Tap Add.&lt;/Text&gt;}
/&gt;</code></pre>
<p>Three props matter: <code>data</code> (the array), <code>renderItem</code> (how to draw one row) and <code>keyExtractor</code> (a unique key per row so React can track items). <code>ListEmptyComponent</code> shows a friendly message when the list is empty.</p>

<h3>A form that adds items</h3>
<pre><code>export default function Members() {
  const [name, setName] = useState('');
  const [amount, setAmount] = useState('');
  const [members, setMembers] = useState([]);

  function addMember() {
    if (name.trim() === '' || isNaN(Number(amount))) {
      alert('Enter a name and a number');
      return;
    }
    const m = { id: String(Date.now()), name: name.trim(), paid: Number(amount) };
    setMembers([m, ...members]);
    setName('');
    setAmount('');
  }
  // ...TextInputs, Pressable and FlatList here
}</code></pre>
<p>Notes: <code>TextInput</code> always gives text, so convert with <code>Number()</code>. Use <code>keyboardType="numeric"</code> on the amount box so the number keypad appears. Always validate before saving.</p>

<h3>Good form habits</h3>
<table>
<thead><tr><th>Habit</th><th>Why</th></tr></thead>
<tbody>
<tr><td>Short labels and placeholders</td><td>Users should know what to type</td></tr>
<tr><td>Right keyboard type</td><td>Numbers, phone numbers and email each have their own keypad</td></tr>
<tr><td>Check input before saving</td><td>Blank names or letters in a money field break your data</td></tr>
<tr><td>Clear message when wrong</td><td>Say what to fix, not just "Error"</td></tr>
<tr><td>Clear the form after success</td><td>Shows that it worked</td></tr>
</tbody>
</table>

<h3>Mini-case: the duplicate rows</h3>
<p>A learner used the member's name as the key in a list. Two members called "John" caused a warning and strange behaviour when one was deleted. Keys must be unique and stable, such as an id.</p>

<blockquote><p><strong>Quick check:</strong> A list has 500 customers. Why is FlatList better than ScrollView here?</p><p>Answer: FlatList draws only the visible rows, so it uses less memory and runs better on low-end phones.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A user types "abc" in the amount box. What should your app do before saving?</p><p>Answer: Validate it, for example with isNaN(Number(amount)), and show a clear message instead of saving bad data.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create the members screen with a name box, an amount box and an Add button.</li>
<li>Show the members in a FlatList below the form.</li>
<li>Add validation so empty names are rejected.</li>
<li>Show a "Total saved" line that adds up all amounts using <code>members.reduce(...)</code>.</li>
<li>Add the ListEmptyComponent message.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>FlatList renders only visible rows; always give each row a unique key.</li>
<li>Form values are text; convert and validate them before use.</li>
<li>Show a friendly empty state and clear error messages.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Your laptop runs `npx expo start` but your phone cannot open the app. The laptop is on office Wi-Fi that blocks device-to-device traffic. What is a good next step?
- Reinstall Windows
* Use the phone's hotspot for both devices, or try the tunnel option
- Buy a new phone
- Delete the node_modules folder of another project
= Expo Go needs to reach your laptop; a hotspot or tunnel works around a network that blocks it.

Q: Which file holds the first screen in a blank Expo project?
- package.json
- app.json
* App.js
- node_modules
= App.js is where the starting screen component lives; package.json lists libraries and app.json holds settings.

Q: A learner writes `<p>Total saved</p>` in a React Native screen. What happens?
- It shows normally as a paragraph
* It fails, because React Native has no HTML tags and text must be in Text
- It shows in bold
- It is ignored silently and the app works
= React Native uses its own components such as View and Text instead of HTML elements.

Q: You want two Text items side by side, one on the left edge and one on the right. What do you set on the parent View?
- flexDirection: 'column'
- display: 'inline'
* flexDirection: 'row' and justifyContent: 'space-between'
- float: 'left'
= Row direction places items side by side; space-between pushes them to opposite edges.

Q: A counter button does `total = total + 500` on a plain variable, but the screen never changes. What is the fix?
- Make the font bigger
- Restart the phone each time
* Use useState and call the setter function
- Use a ScrollView instead
= Only changing state through its setter makes React redraw the screen.

Q: You hold members in state as an array and want to add one. Which is correct?
- members.push(newMember)
* setMembers([...members, newMember])
- members = newMember
- setMembers(members)
= Creating a new array lets React see that the state changed.

Q: Why should every FlatList row have a unique key such as an id?
- It makes the text bold
- It sets the colour of the row
* React uses it to track rows correctly when the list changes
- It makes the list load from the internet
= Unique, stable keys avoid wrong or duplicated rows when items are added or removed.

Q: A user types a contribution amount. Which approach is best?
- Save whatever text was typed
* Use a numeric keyboard, convert with Number() and check it is valid before saving
- Hide the error and save zero
- Ask the user to retype it in a different app
= Validating input prevents bad data and gives the user a clear message.
QUIZ,
        ],
        [
            'title'   => 'Module 3: Navigation, web APIs and data on the device',
            'summary' => 'Move between screens, fetch data from the internet and keep data stored on the phone.',
            'lessons' => [
                [
                    'title'   => 'Navigation: moving between screens',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build an app with several screens and pass information from one screen to another. This matters because real apps are never one page: a list leads to a detail, a detail leads to a form, and users need a clear way back.</p>

<h3>Why a navigation library?</h3>
<p>A phone app has no address bar and no browser back button. A navigation library keeps a <strong>stack</strong> of screens: opening a screen puts it on top, and going back removes it, like a pile of plates. React Navigation is the most widely used library for React Native. The official guide at reactnavigation.org shows current installation steps; they change a little over time, so check it when you start a project.</p>

<h3>Install</h3>
<pre><code>npm install @react-navigation/native @react-navigation/native-stack
npx expo install react-native-screens react-native-safe-area-context</code></pre>
<p>Use <code>npx expo install</code> for native libraries, because it chooses versions that match your Expo version.</p>

<h3>A two-screen app</h3>
<pre><code>import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';

const Stack = createNativeStackNavigator();

function MembersScreen({ navigation }) {
  return (
    &lt;View&gt;
      &lt;Pressable onPress&#61;{() =&gt; navigation.navigate('Detail', { name: 'Grace', paid: 50000 })}&gt;
        &lt;Text&gt;Grace&lt;/Text&gt;
      &lt;/Pressable&gt;
    &lt;/View&gt;
  );
}

function DetailScreen({ route }) {
  const { name, paid } = route.params;
  return &lt;Text&gt;{name} has paid UGX {paid}&lt;/Text&gt;;
}

export default function App() {
  return (
    &lt;NavigationContainer&gt;
      &lt;Stack.Navigator&gt;
        &lt;Stack.Screen name="Members" component={MembersScreen} /&gt;
        &lt;Stack.Screen name="Detail" component={DetailScreen} /&gt;
      &lt;/Stack.Navigator&gt;
    &lt;/NavigationContainer&gt;
  );
}</code></pre>
<p>Screens registered in the navigator receive a <code>navigation</code> object (to move around) and a <code>route</code> object (which carries <code>params</code>, the data sent along). The first screen listed is shown first. The header with a back arrow is added for you, and Android's back button works automatically.</p>

<h3>Types of navigation</h3>
<table>
<thead><tr><th>Type</th><th>Looks like</th><th>Good for</th></tr></thead>
<tbody>
<tr><td>Stack</td><td>Screens pile up; back arrow</td><td>List to detail to form</td></tr>
<tr><td>Bottom tabs</td><td>Icons along the bottom</td><td>Three to five main sections</td></tr>
<tr><td>Drawer</td><td>Side menu from the left</td><td>Many sections, rarely used ones</td></tr>
</tbody>
</table>
<p>Tabs and drawers are separate packages in the same library. For most small apps, a stack plus a few bottom tabs is enough.</p>

<h3>Mini-case: sending only an id</h3>
<p>A learner passed a whole list of 300 members as a parameter and later found data was out of date after editing. A better approach: send only the member's id and let the detail screen read the latest record from storage. Keep one source of truth.</p>

<blockquote><p><strong>Quick check:</strong> On the Members screen you tap a member and want the Detail screen to know which one. How do you send the information?</p><p>Answer: Pass it as the second argument, for example navigation.navigate('Detail', { id: 3 }), and read it from route.params.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why use <code>npx expo install</code> instead of <code>npm install</code> for libraries such as react-native-screens?</p><p>Answer: It picks the library version that matches your Expo version, avoiding mismatches and crashes.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Install React Navigation with the two commands above.</li>
<li>Build Members and Detail screens as shown.</li>
<li>Pass the member's name and amount to Detail and show them.</li>
<li>Add a third screen, "AddMember", and open it from a button.</li>
<li>Press the Android back button or the header arrow to confirm you return correctly.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A stack navigator keeps screens in a pile and gives you a back button.</li>
<li>Use navigation.navigate(name, params) and route.params to move and share data.</li>
<li>Pass small values like ids, and keep the real data in one place.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Calling a web API and showing the data',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to fetch data from a web API and show it in your app with proper loading and error messages. This matters because many apps are windows onto data held on a server: school notices, product prices, weather, exchange rates.</p>

<h3>What is an API?</h3>
<p>An <strong>API</strong> is a web address that returns data for programs rather than pages for people. Most return <strong>JSON</strong>, text written as objects and arrays, which JavaScript reads easily. You send a request with <code>fetch</code> and get a response back. You already met fetch in the JavaScript course; it works the same way in React Native.</p>

<h3>The pattern: three states</h3>
<p>Network calls take time and can fail. Every screen that loads data needs three states: <strong>loading</strong>, <strong>error</strong> and <strong>success</strong>. Showing only the success case is the most common beginner mistake.</p>
<pre><code>import { useEffect, useState } from 'react';
import { View, Text, FlatList, ActivityIndicator, Pressable } from 'react-native';

export default function Notices() {
  const [notices, setNotices] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  async function load() {
    setLoading(true);
    setError(null);
    try {
      const res = await fetch('https://example.com/api/notices');
      if (!res.ok) throw new Error('Server said ' + res.status);
      const data = await res.json();
      setNotices(data);
    } catch (e) {
      setError('Could not load notices. Check your connection.');
    } finally {
      setLoading(false);
    }
  }

  useEffect(() =&gt; { load(); }, []);

  if (loading) return &lt;ActivityIndicator size="large" /&gt;;
  if (error) return (
    &lt;View&gt;
      &lt;Text&gt;{error}&lt;/Text&gt;
      &lt;Pressable onPress&#61;{load}&gt;&lt;Text&gt;Try again&lt;/Text&gt;&lt;/Pressable&gt;
    &lt;/View&gt;
  );
  return &lt;FlatList data={notices} keyExtractor={(n) =&gt; String(n.id)}
    renderItem={({ item }) =&gt; &lt;Text&gt;{item.title}&lt;/Text&gt;} /&gt;;
}</code></pre>
<p>The address above is a placeholder. When you practise, use any free public JSON test service that you find in its official documentation, or run your own small API. Never invent or guess an address in production code.</p>

<h3>Key ideas in the code</h3>
<ul>
<li><code>useEffect</code> with an empty array <code>[]</code> runs once when the screen first appears.</li>
<li><code>await fetch(...)</code> pauses until the server answers; <code>res.ok</code> is false for errors such as 404 or 500.</li>
<li><code>try / catch / finally</code> catches failures such as no network and always turns off the spinner.</li>
<li>A "Try again" button gives users control.</li>
</ul>

<h3>Sending data: POST</h3>
<pre><code>await fetch('https://example.com/api/payments', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ memberId: 3, amount: 20000 }),
});</code></pre>

<table>
<thead><tr><th>Method</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>GET</td><td>Read data</td></tr>
<tr><td>POST</td><td>Create something new</td></tr>
<tr><td>PUT / PATCH</td><td>Change something</td></tr>
<tr><td>DELETE</td><td>Remove something</td></tr>
</tbody>
</table>

<h3>Mini-case: the endless spinner</h3>
<p>A school notice app showed a spinner forever when parents had no signal, because the code had no catch block. After adding the error state, a message and a Try again button, support calls dropped. The server was never the problem; the missing error screen was.</p>

<blockquote><p><strong>Quick check:</strong> The user has no internet. Which part of the code shows a useful message?</p><p>Answer: The catch block, which sets an error state that the screen displays with a Try again button.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why do you add <code>[]</code> as the second argument of <code>useEffect</code> for loading data?</p><p>Answer: It makes the effect run only once when the screen appears, instead of after every redraw.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Find a free public test API that returns a list in JSON (check its official documentation for the address).</li>
<li>Write the Notices screen with loading, error and success states.</li>
<li>Switch on aeroplane mode and reload. Confirm the error message and Try again button appear.</li>
<li>Switch the network on and tap Try again.</li>
<li>Show only the first 10 items to save data.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>An API returns JSON; fetch with async/await reads it.</li>
<li>Always handle loading, error and success.</li>
<li>Fetch less data to save your users' bundles.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Storing data on the device',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to save data on the phone so it is still there when the app is closed and reopened. This matters because state disappears when the app stops, and a treasurer cannot lose a month of records because the phone ran out of battery.</p>

<h3>State is temporary; storage is lasting</h3>
<p>State lives in memory. When the app closes, it is gone. To keep data you write it to the phone's storage. There are several options:</p>
<table>
<thead><tr><th>Option</th><th>Best for</th><th>Notes</th></tr></thead>
<tbody>
<tr><td>AsyncStorage</td><td>Small settings and simple lists</td><td>Stores text by key; not encrypted; keep it small</td></tr>
<tr><td>SQLite (expo-sqlite)</td><td>Many records, searching, sorting</td><td>A real database file on the phone</td></tr>
<tr><td>SecureStore (expo-secure-store)</td><td>Small secrets such as a login token</td><td>Uses the system's secure storage; keep values small</td></tr>
<tr><td>Files (expo-file-system)</td><td>Photos, exports, documents</td><td>You manage the files yourself</td></tr>
</tbody>
</table>

<h3>Using AsyncStorage</h3>
<pre><code>npx expo install @react-native-async-storage/async-storage</code></pre>
<p>AsyncStorage saves text, so convert arrays and objects with <code>JSON.stringify</code> and back with <code>JSON.parse</code>.</p>
<pre><code>import AsyncStorage from '@react-native-async-storage/async-storage';

async function saveMembers(list) {
  await AsyncStorage.setItem('members', JSON.stringify(list));
}

async function loadMembers() {
  const text = await AsyncStorage.getItem('members');
  return text ? JSON.parse(text) : [];
}</code></pre>

<h3>Load on start, save on change</h3>
<pre><code>const [members, setMembers] = useState([]);
const [ready, setReady] = useState(false);

useEffect(() =&gt; {
  loadMembers().then((list) =&gt; { setMembers(list); setReady(true); });
}, []);

useEffect(() =&gt; {
  if (ready) saveMembers(members);
}, [members, ready]);</code></pre>
<p>The <code>ready</code> flag stops the app from saving an empty list over your real data before loading has finished. This is a classic bug that wipes users' records.</p>

<h3>Choosing a store</h3>
<ul>
<li>A few settings (language, last screen): AsyncStorage.</li>
<li>Hundreds or thousands of records you want to search: SQLite.</li>
<li>A login token or API key for the user: SecureStore, never plain AsyncStorage.</li>
<li>Remember that device storage can be cleared by the user, and a lost phone loses the data. Plan a backup to a server for important records.</li>
</ul>

<h3>Mini-case: the wiped list</h3>
<p>A learner's app saved the member list every time the list changed, including the very first render when the list was still empty. Each start overwrote saved data. Adding the ready flag fixed it. Test by closing and reopening the app, not only by tapping around.</p>

<blockquote><p><strong>Quick check:</strong> You need to store a user's login token on the phone. Which option fits best?</p><p>Answer: SecureStore, because it keeps small secrets in the system's protected storage.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> AsyncStorage returns <code>null</code> the first time you read a key. How should loadMembers handle it?</p><p>Answer: Return an empty list when the stored text is null, so the app starts cleanly.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Install AsyncStorage with the command above.</li>
<li>Add loadMembers and saveMembers to your members screen.</li>
<li>Add two members, then fully close the app in Expo Go and reopen it. The members should still be there.</li>
<li>Remove the ready flag on purpose, restart, and watch the list disappear. Then put the flag back.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>State is temporary; storage survives restarts.</li>
<li>AsyncStorage for small data, SQLite for many records, SecureStore for secrets.</li>
<li>Load first, then save; never save an empty list over real data.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: In a stack navigator, what does navigation.navigate('Detail', { id: 7 }) do?
- Deletes the Detail screen
* Opens the Detail screen and sends it the value id = 7
- Closes the app
- Fetches data from the internet
= navigate opens a screen and the second argument carries parameters, read later from route.params.

Q: Which command is the best way to add a native library such as react-native-screens to an Expo project?
- npm install -g react-native-screens
* npx expo install react-native-screens
- Download the folder by hand from a friend
- Copy it into App.js
= expo install chooses a version that matches your Expo version.

Q: You have three main sections that users switch between all day (Home, Payments, Settings). Which navigation type suits best?
- Only a stack
* Bottom tabs
- A pop-up alert
- No navigation at all
= Bottom tabs give quick access to a few main sections.

Q: Your notice screen only handles the success case, and parents with no signal see a spinner forever. What is missing?
- A bigger font
* Error handling with a message and a retry button
- More images
- A second API address
= Every data screen needs loading, error and success states.

Q: What does `if (!res.ok) throw new Error(...)` protect you from?
- Slow typing
* Treating a failed response such as 404 or 500 as if it were good data
- Using too much storage
- Forgetting a key in FlatList
= fetch does not throw for HTTP error codes, so checking res.ok is needed.

Q: A learner calls fetch directly in the body of the component (not inside useEffect) and the app keeps reloading. Why?
- The phone is too old
* Every redraw runs the fetch again, which updates state and redraws again
- The API is down
- JSON cannot be read
= Side effects such as fetch belong in useEffect so they run at the right time, not on every redraw.

Q: Which data should NOT be stored in plain AsyncStorage?
- The user's chosen language
- The last screen opened
* A login token or password
- A theme colour
= AsyncStorage is not encrypted; secrets belong in secure storage.

Q: Your app overwrites saved members with an empty list every time it starts. What is the likely cause?
- The phone has too little storage
- JSON.parse is broken
* The save runs before loading has finished, with no ready check
- FlatList is slow
= Save only after the initial load completes, using a ready flag.
QUIZ,
        ],
        [
            'title'   => 'Module 4: Reliable, safe and fast apps',
            'summary' => 'Handle offline use, protect users, test properly and keep the app small and quick.',
            'lessons' => [
                [
                    'title'   => 'Offline use and slow networks',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to make an app that stays useful with no signal and saves changes to send later. This matters because in much of East Africa a connection is something that comes and goes, and apps that depend on it all the time lose users fast.</p>

<h3>Know when you are online</h3>
<p>Expo provides a library that reports the network state. Install it with <code>npx expo install @react-native-community/netinfo</code>, then:</p>
<pre><code>import NetInfo from '@react-native-community/netinfo';

useEffect(() =&gt; {
  const stop = NetInfo.addEventListener((state) =&gt; {
    setOnline(Boolean(state.isConnected));
  });
  return () =&gt; stop();
}, []);</code></pre>
<p>Treat this as a hint, not a promise. A phone may be "connected" to Wi-Fi that has no internet, so a real request can still fail. Always handle failed requests as well.</p>

<h3>The cache-first pattern</h3>
<ol>
<li>On opening a screen, show the saved copy at once (from AsyncStorage or SQLite).</li>
<li>In the background, ask the server for fresh data.</li>
<li>If it works, replace the data and save the new copy. If it fails, keep showing the saved copy and say when it was last updated.</li>
</ol>
<pre><code>async function loadNotices() {
  const saved = await AsyncStorage.getItem('notices');
  if (saved) setNotices(JSON.parse(saved));      // show old data first
  try {
    const res = await fetch(URL);
    if (!res.ok) throw new Error('bad');
    const fresh = await res.json();
    setNotices(fresh);
    await AsyncStorage.setItem('notices', JSON.stringify(fresh));
  } catch (e) {
    setStatus('Offline: showing saved notices');
  }
}</code></pre>

<h3>The outbox: saving changes for later</h3>
<p>When the user creates something offline, add it to an <strong>outbox</strong> (a saved list of pending actions). When the network returns, send each item and remove it only after the server confirms.</p>
<pre><code>// each pending item
{ id: 'a1', type: 'payment', data: { memberId: 3, amount: 20000 }, sent: false }</code></pre>
<p>Give every item its own unique id and have the server ignore an id it has already received. Otherwise a slow network and a retry could record the same payment twice, which is serious with money.</p>

<table>
<thead><tr><th>Situation</th><th>Good behaviour</th></tr></thead>
<tbody>
<tr><td>No network, reading</td><td>Show saved data with "last updated" time</td></tr>
<tr><td>No network, writing</td><td>Save to outbox, show "waiting to sync"</td></tr>
<tr><td>Slow network</td><td>Show progress; set a time limit; allow cancel and retry</td></tr>
<tr><td>Request failed</td><td>Keep the data, tell the user, retry later</td></tr>
<tr><td>Two devices changed the same record</td><td>Decide a rule, such as "latest change wins", and tell the user</td></tr>
</tbody>
</table>

<h3>Mini-case: the double payment</h3>
<p>A fee-collection app sent a payment, lost signal before the reply and sent it again on retry. The parent was charged twice. The fix was a unique request id: the server saw the repeated id and returned the first result instead of charging again.</p>

<blockquote><p><strong>Quick check:</strong> A user opens your notice app in a place with no signal. What should the screen show?</p><p>Answer: The last saved notices, with a short message that they are offline, rather than an empty error screen.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why give each outbox item a unique id?</p><p>Answer: So the server can recognise and ignore duplicates when a request is retried, avoiding double records.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Add the cache-first loader to your Notices screen.</li>
<li>Open the app online so it saves a copy, then turn on aeroplane mode and reopen it.</li>
<li>Show a small "Offline" banner when NetInfo says not connected.</li>
<li>Write down in one paragraph how you would design an outbox for your capstone app.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Show saved data first, refresh in the background.</li>
<li>Keep offline changes in an outbox and send them when the network returns.</li>
<li>Use unique ids so retries never create duplicates.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Permissions, privacy and security',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to ask for phone permissions properly, protect the personal data your app holds and avoid common security mistakes. This matters because members, parents and customers trust you with names, phone numbers and money, and one leak can end that trust and break the law.</p>

<h3>Permissions</h3>
<p>Phones protect features such as the camera, location, contacts and photos. An app must ask the user first. Good practice:</p>
<ul>
<li>Ask only for what the feature really needs. A notice board does not need the contact list.</li>
<li>Ask <em>when</em> the feature is used, not all at start, and explain why in plain words.</li>
<li>Keep working if the user says no; offer another way.</li>
</ul>
<pre><code>import * as ImagePicker from 'expo-image-picker';

async function pickPhoto() {
  const perm = await ImagePicker.requestMediaLibraryPermissionsAsync();
  if (!perm.granted) {
    alert('Without photo access you can still type the details.');
    return;
  }
  const result = await ImagePicker.launchImageLibraryAsync({ quality: 0.5 });
  if (!result.canceled) {
    // use result.assets[0].uri
  }
}</code></pre>
<p>Note <code>quality: 0.5</code>: smaller photos save the user's data and storage when uploaded.</p>

<h3>Privacy and the law</h3>
<p>Uganda has a Data Protection and Privacy Act that protects personal data, and similar laws exist in Kenya, Tanzania and other countries. The simple ideas to follow are:</p>
<ol>
<li><strong>Collect only what you need.</strong> If you do not need a national ID number, do not ask for it.</li>
<li><strong>Say what you collect and why,</strong> in a short privacy statement in the app.</li>
<li><strong>Get consent</strong> where it is needed and let people ask for their data to be corrected or deleted.</li>
<li><strong>Keep it safe</strong> and tell people if it is leaked. Check the law with an advisor for a real product.</li>
</ol>

<h3>Security basics for app developers</h3>
<table>
<thead><tr><th>Mistake</th><th>Better practice</th></tr></thead>
<tbody>
<tr><td>Secret keys written inside the app code</td><td>Keep secrets on your server; anything in the app can be extracted</td></tr>
<tr><td>Calling an API over plain http</td><td>Use https so data is encrypted in transit</td></tr>
<tr><td>Storing tokens in AsyncStorage</td><td>Use SecureStore</td></tr>
<tr><td>Trusting the app to decide who can see data</td><td>The server must check permission on every request</td></tr>
<tr><td>Showing full phone numbers or ID numbers everywhere</td><td>Show only what the person needs; mask the rest</td></tr>
<tr><td>Logging passwords or tokens while debugging</td><td>Never log secrets; remove debug logs before release</td></tr>
</tbody>
</table>

<h3>Mini-case: the key in the code</h3>
<p>A student put a paid SMS-service key directly in the app so the phone could send messages. Someone unpacked the app, found the key and used it to send thousands of messages, and the student paid the bill. The correct design: the app asks his server, the server checks the user and uses the key privately.</p>

<blockquote><p><strong>Quick check:</strong> Your school notice app asks for access to contacts at first launch. What is wrong with that?</p><p>Answer: It requests a permission the feature does not need and asks too early, which reduces trust and is poor privacy practice.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Where should a secret API key for a payment service live?</p><p>Answer: On your server, not in the app, because app code can be extracted by anyone.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List every piece of personal data your capstone app will collect.</li>
<li>Cross out any you do not truly need.</li>
<li>Write a three-sentence privacy note in plain English: what, why, who can see it.</li>
<li>Search your project for the words "password", "secret" and "key" and check none are exposed.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Ask for permissions only when needed and explain why.</li>
<li>Collect minimal data and be honest about it.</li>
<li>Use https, secure storage and server-side checks; never ship secret keys in the app.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Testing on a real phone and an emulator',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to test your app systematically on real devices and emulators and fix problems before users find them. This matters because a bug in a payment or record screen costs real money and trust, and your own phone is not like everyone else's.</p>

<h3>Where to test</h3>
<table>
<thead><tr><th>Option</th><th>Strength</th><th>Weakness</th></tr></thead>
<tbody>
<tr><td>Real phone with Expo Go</td><td>Real touch, speed, camera and network</td><td>Only the phones you own</td></tr>
<tr><td>Android emulator (Android Studio)</td><td>Many virtual phones and Android versions</td><td>Needs a strong computer; not exactly real speed</td></tr>
<tr><td>iOS simulator</td><td>Checks iPhone layout</td><td>Needs a Mac</td></tr>
<tr><td>Friends' phones (a test build)</td><td>Real variety of devices</td><td>Needs a way to share the build</td></tr>
</tbody>
</table>
<p>Always include one cheap, older phone if you can. Slow screens, small memory and old Android versions show problems that a new phone hides.</p>

<h3>A simple test plan</h3>
<p>Write the checks down and repeat them before every release. A basic plan for the savings app:</p>
<ol>
<li>First start: the app opens, shows an empty state, no crash.</li>
<li>Add a member with a valid name and amount: it appears in the list.</li>
<li>Add a member with a blank name: a clear error appears.</li>
<li>Close the app completely and reopen: data is still there.</li>
<li>Aeroplane mode: the app opens and shows saved data.</li>
<li>Rotate the screen and use a large font setting: nothing is cut off.</li>
<li>Press the Android back button on every screen: it behaves sensibly.</li>
</ol>

<h3>Debugging tools</h3>
<ul>
<li><code>console.log(value)</code> prints in the terminal where Expo is running.</li>
<li>Red error screens show the message and the file and line; read them from the top.</li>
<li>Shake the phone (or press <code>m</code> in the terminal) to open the developer menu.</li>
<li>Remove debug logs before you release.</li>
</ul>
<pre><code>console.log('members after add:', members.length);</code></pre>

<h3>Automatic tests</h3>
<p>For logic such as totals and validation, write small functions and test them with a tool like Jest. For example, a function <code>totalPaid(members)</code> is easy to test. Screen testing by hand still matters for touch and layout.</p>

<h3>Test with real users</h3>
<p>Give the app to three people from your target group and watch them without helping. Where they hesitate is where your design needs work. Ask them to do a task, such as "record a contribution of UGX 20,000 for Grace", and note how long it takes.</p>

<h3>Mini-case: the tiny-phone bug</h3>
<p>A button worked perfectly on the developer's large phone but was pushed off the screen on a small one, so users could not save. A quick test with a small emulator and with a ScrollView around the form would have found it in minutes.</p>

<blockquote><p><strong>Quick check:</strong> Your app works on your new phone. Why still test on an older, cheaper one?</p><p>Answer: Older phones expose slow screens, low memory and small-screen layout problems that many of your users will face.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is the value of watching a real user try your app without helping?</p><p>Answer: You see where they get confused, which reveals design problems you cannot see yourself.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a test plan of at least seven checks for your app.</li>
<li>Run every check on your phone and tick them off.</li>
<li>If you can, run it again on an emulator or a friend's phone.</li>
<li>Ask one person to complete a task while you watch silently, and write down three observations.</li>
<li>Fix the most serious problem found.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Test on real phones and emulators, including a weak phone.</li>
<li>A written test plan makes testing repeatable.</li>
<li>Watching real users finds problems no one else will.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'App size and performance',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to find and fix the common causes of big, slow apps. This matters because a download of 150 MB can cost a user more than a day's income in data, and a slow app on a cheap phone gets uninstalled within a week.</p>

<h3>Why size matters</h3>
<p>Users on limited bundles and small storage check an app's size before installing. Many phones have only a few gigabytes free. A smaller app downloads faster, installs on more phones and updates more cheaply.</p>

<h3>Ways to reduce size</h3>
<ul>
<li><strong>Compress images.</strong> Resize pictures to the largest size you actually show. A banner for a phone rarely needs to be wider than 1080 pixels. Use compressed formats such as JPEG or WebP.</li>
<li><strong>Remove unused libraries.</strong> Every package adds weight. Before installing, ask whether a few lines of your own code would do.</li>
<li><strong>Avoid bundling big files</strong> (videos, large fonts, big data files). Download them only when needed.</li>
<li><strong>Use app bundles for the Play Store.</strong> Google Play delivers a version trimmed to the user's phone. Expo's build service produces them for you.</li>
<li><strong>Load remote images small.</strong> Ask your server for thumbnails.</li>
</ul>

<h3>Ways to improve speed</h3>
<table>
<thead><tr><th>Problem</th><th>Fix</th></tr></thead>
<tbody>
<tr><td>Long list feels jerky</td><td>Use FlatList, not ScrollView; keep rows simple; use small images</td></tr>
<tr><td>Screen redraws too often</td><td>Keep state small and close to where it is used; avoid storing everything at the top</td></tr>
<tr><td>Slow start</td><td>Show the first screen quickly and load other data afterwards</td></tr>
<tr><td>Heavy work freezes the screen</td><td>Do it in smaller pieces, or on the server</td></tr>
<tr><td>Too many network calls</td><td>Fetch once, cache, and ask for only the fields you need</td></tr>
<tr><td>Animations lag on cheap phones</td><td>Use fewer or none; plain screens are fine</td></tr>
</tbody>
</table>

<h3>Measure, do not guess</h3>
<p>Before and after each change, measure: the size of the build, the time to open, and how a long list scrolls on your weakest phone. The React Native documentation has a page on performance, and the developer menu can show performance information. Improve the slowest thing first.</p>
<pre><code>// simple timing of a screen load
const t0 = Date.now();
await loadMembers();
console.log('Loaded in', Date.now() - t0, 'ms');</code></pre>

<h3>Mini-case: the 90 MB banner app</h3>
<p>A shop app was 90 MB, mostly because five product photos of 4 MB each were bundled inside. The developer resized them to 800 pixels wide (about 150 KB each) and moved the full catalogue to the server. The install fell to 30 MB and the app opened twice as fast.</p>

<blockquote><p><strong>Quick check:</strong> The biggest files in your project are five 4 MB photos. What is your first step?</p><p>Answer: Resize and compress them (or serve small versions from the server), since images are usually the largest part of an app.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A list of 1000 rows scrolls badly in a ScrollView. What should you change?</p><p>Answer: Use FlatList so only the visible rows are drawn.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Look in your project's assets folder and write down the three largest files.</li>
<li>Resize or compress one of them and compare the file sizes.</li>
<li>Add the timing code above to a screen and read the result on your phone.</li>
<li>List three libraries in package.json and decide if you really need each one.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Small apps are cheaper to download and fit on more phones.</li>
<li>Compress images, drop unused libraries and fetch only what you need.</li>
<li>Measure on a weak phone and fix the biggest problem first.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which is the best behaviour when a notice app opens with no signal?
- Show a blank screen
- Close itself
* Show the last saved notices and a note that you are offline
- Ask the user to restart the phone
= Cache-first design keeps the app useful offline and tells the user the data may be old.

Q: A fee app resends a payment after a timeout and the parent is charged twice. What would have prevented this?
- A bigger button
* A unique request id that the server uses to ignore duplicates
- Removing the error message
- Using a slower network
= Unique ids make retries safe because the server recognises a repeated request.

Q: A phone shows it is connected to Wi-Fi, yet your request fails. What does this tell you?
- NetInfo is always wrong and should not be used
* A connection signal is only a hint, so you must still handle failed requests
- The server is broken for everyone
- Your app has too many images
= Connected does not guarantee working internet; code must handle failures anyway.

Q: When is the best time to ask for camera permission in an expense-receipt app?
- At the very first launch, before showing anything
- Never, and just crash if missing
* When the user taps the button to scan a receipt, with a short reason
- Every time the app is opened
= Asking in context, with a reason, is more trusted and more likely to be accepted.

Q: A learner stores the secret key of an SMS service inside the app so the phone can send messages. What is the problem?
- The key is too long
* Anyone can extract the key from the app, so it should stay on a server
- SMS is not allowed in apps
- The key makes the app slower
= Secrets in app code can be found; the app should call your server, which holds the key.

Q: Which is better for storing a user's login token on the phone?
- AsyncStorage in plain text
- A Text component
* SecureStore
- A public text file
= SecureStore uses the system's protected storage, which is meant for small secrets.

Q: Your app works on your new phone, and testers on older phones report a cut-off Save button. What is the best lesson?
- Ignore it because your phone works
* Test on small and older devices and make forms scrollable
- Hide the Save button
- Tell users to buy new phones
= Real users have varied phones; testing on weak, small ones reveals layout and speed problems.

Q: A shop app is 90 MB because of large bundled photos. What is the most effective fix?
- Add more screens
* Resize and compress images and load full ones from the server only when needed
- Make the photos bigger
- Remove all text
= Images are usually the largest files; reducing them cuts download size and loading time.
QUIZ,
        ],
        [
            'title'   => 'Module 5: Publishing, earning and your capstone app',
            'summary' => 'Release an app, understand costs, mobile money and updates, then build a complete simple app.',
            'lessons' => [
                [
                    'title'   => 'Publishing: app stores, APK sharing and PWA install',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe the ways to get your app onto users' phones and the costs and steps of each. This matters because an app on your own laptop helps nobody, and the right distribution path can decide whether your first hundred users arrive or not.</p>

<h3>Ways to distribute</h3>
<table>
<thead><tr><th>Way</th><th>How users get it</th><th>Cost</th><th>Good for</th></tr></thead>
<tbody>
<tr><td>Google Play Store</td><td>Search and install</td><td>One-time developer registration fee (about 25 US dollars; check the Play Console page)</td><td>Reaching the public on Android</td></tr>
<tr><td>Apple App Store</td><td>Search and install on iPhone</td><td>Yearly Apple Developer Program fee (about 99 US dollars; check Apple's page)</td><td>iPhone users</td></tr>
<tr><td>APK file sharing</td><td>You send a file; users install it manually</td><td>Free</td><td>Testing, a small group, schools</td></tr>
<tr><td>PWA</td><td>Open a link, "Add to Home screen"</td><td>Only web hosting</td><td>Wide reach, tiny download</td></tr>
</tbody>
</table>
<p>Fees and rules change, so always read the official pages before you plan a budget. The Google Play Console and Apple Developer sites list current prices and review rules.</p>

<h3>Building a release with Expo</h3>
<p>Expo's build service, EAS Build, builds your app in the cloud so you do not need a powerful computer. The basic steps are:</p>
<ol>
<li>Create a free Expo account on the Expo website.</li>
<li>Install the tool: <code>npm install -g eas-cli</code> and log in with <code>eas login</code>.</li>
<li>Run <code>eas build:configure</code> to create <code>eas.json</code>.</li>
<li>For a shareable test file, set a build profile for Android with <code>"buildType": "apk"</code>, then run <code>eas build -p android --profile preview</code>.</li>
<li>For the Play Store, build an Android App Bundle (AAB) with the production profile.</li>
</ol>
<p>Free accounts have limits on builds and waiting time; the Expo pricing page explains them. Check your app's name, icon and version in <code>app.json</code> before building.</p>

<h3>Sharing an APK safely</h3>
<p>An APK is an installable Android file. To install it a user must allow "install from unknown sources" for the app they open it with. This is risky when people install files from strangers, so:</p>
<ul>
<li>Send the file only to people you know, and tell them who made it.</li>
<li>Host it on your own site over https, with the file size shown.</li>
<li>Move to the Play Store as soon as the app is stable, so users get automatic updates and safety checks.</li>
</ul>

<h3>Store listing checklist</h3>
<ul>
<li>A clear name, short description and full description in plain language.</li>
<li>Screenshots of the real app.</li>
<li>A privacy policy web page (stores require one if you collect personal data).</li>
<li>A content rating questionnaire and contact email.</li>
<li>A version number you will increase with every update.</li>
</ul>

<h3>Mini-case: APK first, store later</h3>
<p>A youth group built a savings tracker for three nearby groups. They shared an APK by WhatsApp with 40 people, fixed bugs for a month, then published on Google Play. Users who installed from the store received updates automatically, while those on the APK needed reminders. The staged release found problems cheaply.</p>

<blockquote><p><strong>Quick check:</strong> You want 20 members of one group to test your app this week at no cost. Which route is simplest?</p><p>Answer: Build an APK with a preview profile and share it directly with them, because it needs no store account.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why do app stores ask for a privacy policy page?</p><p>Answer: Apps that collect personal data must tell users what is collected and why, and the policy page is where that is explained.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open the Google Play Console and Apple Developer websites and write down the current fee for each.</li>
<li>Check your app's name, icon and version in <code>app.json</code>.</li>
<li>Write the short description (under 80 words) for your app in plain English.</li>
<li>Draft a one-page privacy policy: what you collect, why, who sees it, how to contact you.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Choose among store, APK sharing and PWA by audience, cost and trust.</li>
<li>EAS Build creates app files in the cloud; always read current fees and limits on official pages.</li>
<li>Prepare a listing, screenshots and a privacy policy before you publish.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Earning, mobile money and keeping the app alive',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain realistic ways an app can earn, how mobile money payments work in concept, and how to maintain an app after launch. This matters because an app with no plan for income or upkeep slowly dies, and the best idea means little if you cannot keep it running.</p>

<h3>Ways to earn</h3>
<table>
<thead><tr><th>Model</th><th>How it works</th><th>Fit in Uganda</th></tr></thead>
<tbody>
<tr><td>Paid by organisations</td><td>A school, SACCO or business pays a monthly or yearly fee</td><td>Often the most realistic</td></tr>
<tr><td>Freemium</td><td>Basic use free; extra features paid</td><td>Good if free users bring growth</td></tr>
<tr><td>Transaction fee</td><td>A small percentage on payments made through the app</td><td>Good for marketplaces and fees</td></tr>
<tr><td>Advertising</td><td>Ads shown inside the app</td><td>Needs many users; adds data use</td></tr>
<tr><td>Custom app for clients</td><td>You build apps for others</td><td>Fast income while learning</td></tr>
</tbody>
</table>
<p>Many African users pay by mobile money rather than card, and many will not pay for a small app at all. That is why selling to groups and organisations, or taking a small share of real money flows, often works better than charging each person.</p>

<h3>How mobile money integration works</h3>
<p>Mobile money providers such as MTN and Airtel offer programming interfaces, and payment companies (aggregators) offer one connection that covers several providers. You usually need a registered business to get access. The common flow is:</p>
<ol>
<li>The app sends the amount and the customer's phone number to <em>your server</em>.</li>
<li>Your server asks the payment provider to request payment.</li>
<li>The customer gets a prompt on their phone and enters their PIN. The PIN is never typed into your app.</li>
<li>The provider tells your server the result (success, failed or pending), often through a <strong>callback</strong> (webhook).</li>
<li>Your server updates the record, and the app shows the result.</li>
</ol>
<pre><code>App --amount, phone--&gt; Your server --request--&gt; Payment provider
App &lt;--status--        Your server &lt;--callback-- Payment provider</code></pre>
<p>Key rules: all provider keys stay on your server; treat "pending" as a real state and check it again; keep a record of every transaction; and test with the provider's test (sandbox) environment before using real money. Follow the provider's current documentation, because details differ and change.</p>

<h3>Mini-case: the pending payment</h3>
<p>A ticket app showed "Payment failed" when the provider took 40 seconds to answer. Customers paid again, creating duplicates. The team added a "Waiting for confirmation" screen that polled the server until a final result arrived and prevented a second payment for the same ticket.</p>

<h3>Maintenance after launch</h3>
<ul>
<li><strong>Fix bugs quickly.</strong> Read reviews and messages; reply politely.</li>
<li><strong>Update for new Android versions</strong> and library updates every few months. Old libraries can have security holes.</li>
<li><strong>Version your releases:</strong> 1.0.0, then 1.0.1 for fixes and 1.1.0 for new features.</li>
<li><strong>Keep a change list</strong> so users know what is new.</li>
<li><strong>Back up</strong> server data and plan for the day a phone is lost.</li>
<li><strong>Measure</strong> how many people open the app and which screens they use, respecting privacy.</li>
</ul>
<p>Expo can also deliver small JavaScript-only fixes over the air with its update service; changes to native parts still need a new store release. Check current details in the Expo documentation.</p>

<blockquote><p><strong>Quick check:</strong> Where should the customer enter their mobile money PIN?</p><p>Answer: On their own phone in the provider's prompt, never inside your app, so you never handle the PIN.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A payment status comes back as "pending". What should your app do?</p><p>Answer: Show a waiting message and check the final status again, rather than treating it as a failure and letting the user pay twice.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose an earning model for your capstone app and write why it fits your users.</li>
<li>Draw the five-step payment flow for your app on paper.</li>
<li>Write a maintenance list: three things you will check every month.</li>
<li>Decide your first three version numbers and what each release will contain.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Organisation fees, freemium and transaction fees often work best locally.</li>
<li>Mobile money flows go through your server; keys stay there and PINs stay on the customer's phone.</li>
<li>An app needs regular updates, backups and a reply to users.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Capstone: build a savings-group tracker',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will have a complete small app that lists group members, records contributions, shows who has not paid and keeps working offline. This matters because one finished project teaches more than ten half-built ones, and it is something you can show employers, clients and your community.</p>

<h3>The plan</h3>
<table>
<thead><tr><th>Item</th><th>Decision</th></tr></thead>
<tbody>
<tr><td>Problem</td><td>Treasurers lose track of who paid each month</td></tr>
<tr><td>User</td><td>A group treasurer with a low-cost Android phone, often offline</td></tr>
<tr><td>MVP features</td><td>Add member, record payment, list with totals, works offline</td></tr>
<tr><td>Screens</td><td>Members list, Add member form, Member detail with payment form</td></tr>
<tr><td>Data</td><td>Stored on the phone with AsyncStorage</td></tr>
<tr><td>Libraries</td><td>React Navigation, AsyncStorage</td></tr>
</tbody>
</table>

<h3>Data shape</h3>
<pre><code>{
  id: '1730000000000',
  name: 'Grace',
  payments: [ { id: 'p1', month: '2026-10', amount: 20000 } ]
}</code></pre>

<h3>Build steps</h3>
<ol>
<li><strong>Project:</strong> create it with the blank template, install React Navigation and AsyncStorage.</li>
<li><strong>Storage helpers:</strong> write <code>loadMembers</code> and <code>saveMembers</code>.</li>
<li><strong>Shared state:</strong> keep the members array in <code>App</code> and pass it and an <code>updateMembers</code> function to screens.</li>
<li><strong>Members screen:</strong> a FlatList showing each name and the total paid, with an empty message.</li>
<li><strong>Add member screen:</strong> a validated form that adds a member and goes back.</li>
<li><strong>Detail screen:</strong> shows payments for the member; a form records a payment with a month and amount.</li>
<li><strong>Totals:</strong> compute with <code>reduce</code>.</li>
</ol>
<pre><code>function totalPaid(member) {
  return member.payments.reduce((sum, p) =&gt; sum + p.amount, 0);
}

function addPayment(members, memberId, month, amount) {
  return members.map((m) =&gt;
    m.id === memberId
      ? { ...m, payments: [...m.payments, { id: String(Date.now()), month, amount }] }
      : m
  );
}</code></pre>
<p>Notice that <code>addPayment</code> returns a new array and new objects instead of changing the old ones, which keeps state updates correct.</p>

<h3>Polish and quality checks</h3>
<ul>
<li>Large text, high contrast, big buttons.</li>
<li>Friendly empty states and clear validation messages.</li>
<li>An "Unpaid this month" filter on the list.</li>
<li>Ask before deleting anything.</li>
<li>Test the full plan from the testing lesson on a real phone, including aeroplane mode and reopening.</li>
<li>Check app size and that no personal data is shown more than needed.</li>
</ul>

<h3>Stretch ideas</h3>
<ul>
<li>Export a monthly summary as text to share on WhatsApp.</li>
<li>Send data to a server with an outbox when online.</li>
<li>Add Luganda or Swahili labels by moving texts to one file.</li>
</ul>

<blockquote><p><strong>Quick check:</strong> In the capstone, why does addPayment use map and spread instead of pushing into the old array?</p><p>Answer: Creating new arrays and objects lets React see the change and redraw correctly.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which feature in the MVP table should you cut first if time is short?</p><p>Answer: Anything that is not add member, record payment, totals and offline storage, because those solve the main problem.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Build the app following the seven steps, testing after each one.</li>
<li>Add five fake members and ten payments and check that the totals are right.</li>
<li>Close the app and reopen it to prove the data was saved.</li>
<li>Give it to a real treasurer or a classmate, watch them use it and note three improvements.</li>
<li>Write a short project summary: problem, solution, what you learned, next steps.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A small, complete app is better than a big, unfinished one.</li>
<li>Combine what you learned: components, state, lists, forms, navigation and storage.</li>
<li>Test with a real user, then improve and publish when it is ready.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A youth group wants 30 members to try their new app next week without paying store fees. Which is the simplest route?
- Publish on the Apple App Store
* Build an APK and share it directly with the group
- Rewrite the app as two native apps
- Wait six months for approval
= An APK can be shared directly for testing without a store account, though it should be sent only to people who know and trust you.

Q: Why is a privacy policy page needed before publishing an app that collects names and phone numbers?
- It makes the app load faster
* Stores and the law expect you to tell users what you collect and why
- It removes the need for permissions
- It adds ads
= A privacy policy explains personal data use, which stores require and good practice demands.

Q: Which statement about store fees is the most responsible?
- They never change, so memorise today's number
* They can change, so check the official Google Play and Apple pages when budgeting
- Both stores are always free
- Only Apple has a review
= Prices and rules are set by the stores and change, so confirm on official pages.

Q: A savings-app developer expects most users to pay by mobile money and to resist paying a subscription. Which model is often most realistic?
- Charging each member 50,000 UGX monthly
* Charging groups, SACCOs or schools, or taking a small transaction fee
- Showing ads on every screen to people with expensive data
- Giving the app away with no plan at all
= Selling to organisations or earning from real money flows often fits local conditions better than individual subscriptions.

Q: In a mobile money payment flow, where does the customer type their PIN?
- In a text box inside your app
- Into your server's website
* On their own phone, in the provider's prompt
- In the app's screenshot
= The PIN stays with the provider's prompt, so your app never handles it.

Q: Your payment status returns "pending". What is the best app behaviour?
- Mark it as failed and let the customer pay again
* Show a waiting message and check the final status again
- Ignore it and show success
- Delete the order
= Pending is a real state; treating it as failed can cause double payments.

Q: Where should the payment provider's secret keys be stored?
- Inside the app code so it is easy to use
* On your server, never inside the app
- In a public Git repository
- In the app's description
= App code can be extracted, so secrets stay on a server that you control.

Q: In the capstone, a treasurer's records disappear after restarting the app. What do you check first?
- Whether the font is large enough
- Whether the Add button is green
* Whether the data is saved to storage and loaded again on start
- Whether the app has an icon
= State alone is lost on restart, so storage loading and saving must work together.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: A clinic wants patients to open appointment information from a link, with no installation and a very small data cost. Which approach fits best?
- A native iPhone app
- Two separate native apps
* A web app or PWA
- A desktop program
= A web app or PWA opens from a link and needs no store or large download.

Q: Which is the strongest reason to choose React Native with Expo for a first mobile project?
- It only works on iPhones
* One JavaScript code base can produce an app for Android and iPhone
- It needs no testing
- It removes the need for a plan
= Cross-platform frameworks share code and use JavaScript skills you already have.

Q: Your users have 2 GB RAM phones and buy small data bundles. Which design choice helps most?
- Large animations and autoplay videos
* Small images, cached data, simple screens and a small app size
- Downloading all records at every launch
- Requiring a fast network for every action
= Light screens and small downloads fit weak phones and costly data.

Q: A treasurer works at meetings with no signal. Which design fits?
- Online-only forms
* Offline-first: save locally, sync later, show the sync status
- A message asking her to find signal
- Clearing the form when the network drops
= Offline-first apps stay useful without a network and send changes later.

Q: Which problem statement is the most useful for planning?
- Create an amazing app
- Everyone needs apps
* Boda boda riders cannot see their weekly earnings because they use loose notes
- Learn React Native
= It names who, what and why, so you can design and test a solution.

Q: What is the purpose of a minimum viable product?
- To include every idea in version one
* To release the smallest useful version and learn from real users
- To avoid testing
- To make the app look advanced
= A small first version is finished sooner and shows what users actually need.

Q: You run `npx expo start` and the phone cannot open the app. Which is the most likely first fix?
- Uninstall Windows
* Put phone and computer on the same network, such as a phone hotspot, or try the tunnel option
- Rename App.js
- Increase the font size
= Expo Go must reach the computer on the network.

Q: A learner writes `<div>Hello</div>` inside a React Native screen. What should they use?
- span
* View, with the text inside a Text component
- p
- h1
= React Native uses View and Text instead of HTML elements.

Q: In `const [total, setTotal] = useState(0)`, how do you correctly add 500?
- total = total + 500
- total + 500
* setTotal(total + 500)
- useState(total + 500)
= Only the setter updates state and redraws the screen.

Q: You must remove a member from an array held in state. Which is correct?
- members.splice(index, 1) and leave state as it is
* setMembers(members.filter((m) => m.id !== idToRemove))
- members = []
- delete members
= filter creates a new array without the removed item, which React can detect.

Q: Why use FlatList for 800 customers?
- It changes the colour of rows
- It sends data to the server
* It draws only visible rows, saving memory on weak phones
- It sorts the list for you
= FlatList is built for long lists.

Q: A form field for amount receives "12abc". What should the app do?
- Save it
* Check it, show a clear message and not save until it is a valid number
- Convert it silently to 0
- Close the app
= Validation protects your data and tells the user what to fix.

Q: On the Members screen you want the Detail screen to know which member was tapped. What do you use?
- A global variable only
* navigation.navigate('Detail', { id: member.id }) and read route.params
- A new project
- Alert()
= Route parameters carry small pieces of information between screens.

Q: Which three states should every screen that loads data handle?
- Red, green and blue
- Start, middle and end
* Loading, error and success
- Login, logout and delete
= Network calls take time and can fail, so all three must be shown properly.

Q: Why is `useEffect(() => { load(); }, [])` used for the first load?
- It makes the load run on every redraw
* It runs the load once when the screen first appears
- It stops the app from loading data
- It saves data to the server
= The empty array means the effect runs once after the first render.

Q: Which storage should hold a user's login token?
- AsyncStorage in plain text
- A global string in App.js
* SecureStore
- The screen title
= SecureStore is meant for small secrets, using protected system storage.

Q: Your app overwrites saved data with an empty list every time it starts. What is the fix?
- Use a bigger phone
- Delete the save function
* Only save after the initial load has finished, using a ready flag
- Save twice
= Saving before loading completes replaces real data with the empty starting state.

Q: A shop app opens with no signal and shows only an error message. What would improve it most?
- Making the error red
* Showing saved data first and refreshing in the background
- Removing the Try again button
- Fetching more data
= Cache-first loading keeps the app useful offline.

Q: Why should each queued offline action have a unique id?
- To make the list longer
* So the server can ignore a duplicate when a request is retried
- To change the app icon
- To hide the data
= Unique ids make retries safe, which matters most with payments.

Q: A flashlight-style notice app asks for contacts, camera and location at the first launch. What is the best correction?
- Keep all of them in case they are useful later
* Ask only for needed permissions, at the moment of use, with a reason
- Ask three times
- Hide the explanation
= Minimal, contextual requests protect privacy and build trust.

Q: A developer puts a paid service's secret key in the app so the phone can call the service directly. What is the right design?
- Make the key shorter
* Keep the key on a server and let the app call that server
- Hide the key in a comment
- Put the key in the app description
= App code can be unpacked, so secret keys belong on a server.

Q: Which testing approach is best before releasing a money-record app?
- Test only on your own new phone once
* Follow a written test plan on real phones, including an older one and offline mode
- Ask users to find bugs after release
- Skip validation tests
= Repeatable checks on varied devices catch problems before users lose records.

Q: Your build is 90 MB mostly because of large photos. What do you do first?
- Add more libraries
* Resize and compress the images and load large ones from a server when needed
- Make the photos larger
- Remove the app name
= Images are often the biggest files, so reducing them gives the largest saving.

Q: A group wants to test your app with 25 members this week and pay nothing. Which option fits?
- Publish in two stores
* Share a built APK directly with them
- Print the code
- Ask them to use a laptop
= An APK can be installed directly for testing, though it should only go to trusted people.

Q: A mobile money request returns "pending". What should the app do?
- Treat it as a failure and let the user pay again
* Show a waiting state and check the final status through your server
- Show success immediately
- Delete the order
= Pending is a real state; wait for the final result to avoid double payments or wrong records.

Q: Which maintenance habit protects a published app best?
- Never update after launch
* Fix bugs, update libraries regularly, increase the version number with each release and back up server data
- Delete the code after launch
- Change the app name each week
= Regular care keeps the app secure, working on new phones and trusted by users.
QUIZ,
];
