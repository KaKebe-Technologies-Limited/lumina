<?php
return [
    'slug'        => 'computer-basics',
    'title'       => 'Computer Basics and Digital Literacy',
    'category'    => 'basics',
    'level'       => 'beginner',
    'icon'        => 'laptop',
    'sort'        => 10,
    'summary'     => 'Start from zero: learn what a computer is, how its parts work, how to use it with confidence, and how to stay safe and healthy while you do.',
    'description' => '<p>This course is your first step into the digital world. You will learn what a computer is, how the main parts work together, and how to switch on, log in, use the mouse and keyboard, and open and close programs.</p><p>You will also meet the software that runs on computers and phones, learn how to install it safely, and build good habits: strong passwords, backups, caring for your equipment and using a computer in a healthy way.</p><p>The course is written for complete beginners. No experience is needed, and every lesson ends with a short activity you can do on your own computer, laptop or phone.</p>',
    'outcomes'    => [
        'Explain in simple words what a computer is and how it turns input into output.',
        'Name the main parts of a computer and say what each one does.',
        'Compare storage types and read memory sizes such as MB, GB and TB.',
        'Switch a computer on and off safely and move around the desktop.',
        'Use a mouse, touchpad and keyboard with growing confidence.',
        'Tell operating systems from applications and install software safely.',
        'Create safer passwords and make a simple backup plan.',
        'Look after your equipment, your health and other people online.',
    ],
    'requirements' => ['No experience needed', 'A computer, laptop or smartphone to practise on (a shared one is fine)'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Microsoft Support: Windows help and learning', 'url' => 'https://support.microsoft.com/windows'],
        ['title' => 'GCFGlobal: Computer Basics (free tutorials)', 'url' => 'https://edu.gcfglobal.org/en/computerbasics/'],
        ['title' => 'Khan Academy: Computing', 'url' => 'https://www.khanacademy.org/computing'],
    ],
    'modules' => [
        [
            'title'   => 'Module 1: Meet your computer',
            'summary' => 'What a computer is, how hardware and software differ, and how a computer turns input into output.',
            'lessons' => [
                [
                    'title'   => 'What is a computer?',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what a computer is and recognise one in everyday life. This matters because computers are no longer only on office desks: they are in phones, bank machines, school registers and mobile money systems, and understanding them helps you use them with confidence.</p>
<h3>A simple definition</h3>
<p>A <strong>computer</strong> is an electronic machine that accepts information, follows instructions to work with it, and gives you a result. The information you give it is called <strong>data</strong>. The instructions are called a <strong>program</strong>. The result is called <strong>output</strong>.</p>
<p>A computer is not clever by itself. It does exactly what its programs tell it to do, very fast and without getting tired. A calculator is a tiny computer that does only sums. A laptop can do thousands of different jobs because you can change the programs it runs.</p>
<h3>Computers you already meet</h3>
<p>You may use computers without noticing them. Here are some examples from daily life in Uganda:</p>
<ul>
<li>A smartphone used for calls, WhatsApp and mobile money.</li>
<li>A point-of-sale machine in a supermarket that adds up your shopping.</li>
<li>An ATM that gives out cash after you enter your PIN.</li>
<li>A school or office computer used to type letters and keep records.</li>
<li>A television decoder, a modern car dashboard and a digital water or electricity meter.</li>
</ul>
<h3>What computers are good at</h3>
<p>Computers are excellent at four things: they are <strong>fast</strong>, <strong>accurate</strong> (they follow instructions exactly), they can <strong>store</strong> huge amounts of information, and they can do the same job again and again without tiring. A small shop owner who records sales in a spreadsheet can total a month of sales in a second. Doing it by hand with a pen could take an hour.</p>
<p>Computers are not good at judgement, kindness or common sense. They cannot tell that you typed the wrong number if the wrong number looks valid. That is why people must always check the results.</p>
<h3>The information cycle</h3>
<p>Almost everything a computer does follows four steps, which we call the <strong>information processing cycle</strong>:</p>
<ol>
<li><strong>Input:</strong> you give the computer information (typing, tapping, speaking).</li>
<li><strong>Processing:</strong> the computer works on that information.</li>
<li><strong>Storage:</strong> the computer keeps the information so you can use it later.</li>
<li><strong>Output:</strong> the computer shows or prints the result.</li>
</ol>
<p>When you send a message on your phone, you type it (input), the phone prepares it (processing), a copy is kept in the chat (storage), and the other person sees it on their screen (output).</p>
<h3>Try it yourself</h3>
<ol>
<li>Write down five devices you used or saw in the last week that contain a computer.</li>
<li>For each one, name one input (what you gave it) and one output (what it gave back).</li>
<li>Pick one device and describe the four steps of the information cycle for a task you did on it.</li>
<li>Share your list with a friend and see whether they thought of different devices.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A computer is an electronic machine that takes in data, processes it by following a program, and gives output.</li>
<li>Computers are fast, accurate and can store a lot, but they do not think for themselves.</li>
<li>Phones, ATMs and shop tills are all computers.</li>
<li>The cycle is input, processing, storage and output.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Hardware and software',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn the difference between hardware and software and be able to sort the things you meet into the right group. This matters because when something goes wrong, the first question a technician asks is whether the problem is in the hardware or the software.</p>
<h3>Hardware: the parts you can touch</h3>
<p><strong>Hardware</strong> means the physical parts of a computer: the screen, keyboard, mouse, the box with the electronics inside, the cables and the printer. If you can pick it up (or at least touch it), it is hardware. If you drop it, it can break.</p>
<h3>Software: the instructions you cannot touch</h3>
<p><strong>Software</strong> means the programs and instructions that tell the hardware what to do. You can see software working on the screen, but you cannot hold it. A word processor, a game, a web browser and the WhatsApp app are all software.</p>
<p>A useful comparison is a radio. The radio itself, with its speaker and buttons, is the hardware. The songs and news being broadcast are like software: without them the radio makes only noise, and without the radio the songs have nowhere to play.</p>
<table>
<thead><tr><th>Hardware</th><th>Software</th></tr></thead>
<tbody>
<tr><td>Physical, can be touched</td><td>Instructions, cannot be touched</td></tr>
<tr><td>Wears out or breaks</td><td>Can be corrupted, deleted or out of date</td></tr>
<tr><td>Examples: monitor, mouse, flash drive</td><td>Examples: Windows, Chrome, a photo editor</td></tr>
<tr><td>Replaced by buying a part</td><td>Updated by downloading a new version</td></tr>
</tbody>
</table>
<h3>Two kinds of software</h3>
<p><strong>System software</strong> looks after the computer itself. The most important example is the <strong>operating system</strong> (such as Windows or Android). <strong>Application software</strong> (apps) helps you do your own tasks, such as writing a letter, doing accounts or watching a video. You will study both in more detail in Module 4.</p>
<h3>Why both are needed</h3>
<p>New hardware with no software is like a new kitchen with no cook: nothing happens. Software with no hardware is like a recipe with no kitchen: it cannot be used. A computer works only when both are present and working together.</p>
<h3>Try it yourself</h3>
<ol>
<li>Look around the room and list five pieces of hardware you can see.</li>
<li>Open any three apps or programs on your computer or phone and write down their names. These are software.</li>
<li>Sort this list into hardware and software: keyboard, Excel, speaker, Android, USB flash drive, YouTube app.</li>
<li>Think of one problem you had with a device and decide whether it was likely hardware (for example a cracked screen) or software (for example an app that keeps closing).</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Hardware is physical; software is the set of instructions the hardware follows.</li>
<li>System software runs the computer; application software helps you do tasks.</li>
<li>Both must work together for a computer to be useful.</li>
</ul>
<h3>Common mistakes</h3>
<p>Many beginners call the main box the "hard drive" or the "CPU". The box is the <em>system unit</em> or <em>case</em>; the hard drive and CPU are only two of the parts inside it.</p>
HTML,
                ],
                [
                    'title'   => 'Types of computers and devices',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will know the main kinds of computers and be able to choose which one suits a given job. This matters because the right device saves money: a student may not need a powerful desktop, and a graphic designer may find a phone too small.</p>
<h3>Desktop computers</h3>
<p>A <strong>desktop computer</strong> stays in one place. It usually has a separate monitor, keyboard, mouse and system unit. Desktops are often cheaper for the same power, easier to repair and upgrade, and comfortable to use for long hours. They need mains electricity all the time, so a power cut switches them off unless you have a UPS (a battery backup, explained in Module 5).</p>
<h3>Laptops</h3>
<p>A <strong>laptop</strong> folds the screen, keyboard, touchpad and battery into one portable body. You can carry it to class, an office or a cybercafe and work for a few hours on battery. Laptops cost more than a desktop of the same power and are harder to upgrade, but the battery is a real advantage when electricity is unreliable.</p>
<h3>Tablets and smartphones</h3>
<p>A <strong>tablet</strong> is a flat device with a touch screen and no physical keyboard (you can add one). A <strong>smartphone</strong> is a pocket-sized computer that also makes calls. Both are controlled by touching the screen. They are excellent for communication, photos, learning videos and mobile money. They are less comfortable for typing long documents or managing many files.</p>
<h3>Other computers</h3>
<ul>
<li><strong>Servers:</strong> powerful computers that serve websites, email or files to many people at once.</li>
<li><strong>Mainframes and supercomputers:</strong> very large machines used by governments, banks and scientists.</li>
<li><strong>Embedded computers:</strong> tiny computers hidden inside machines such as washing machines, cars and card readers.</li>
</ul>
<h3>Choosing the right device</h3>
<table>
<thead><tr><th>Need</th><th>Good choice</th></tr></thead>
<tbody>
<tr><td>Typing assignments and learning at home</td><td>Laptop or desktop</td></tr>
<tr><td>Running a computer lab for many learners</td><td>Desktops</td></tr>
<tr><td>Messaging, mobile money, photos</td><td>Smartphone</td></tr>
<tr><td>Reading, watching videos, light notes</td><td>Tablet</td></tr>
<tr><td>Working while travelling or during power cuts</td><td>Laptop</td></tr>
</tbody>
</table>
<h3>Try it yourself</h3>
<ol>
<li>Count how many computing devices are in your home or school and name each type.</li>
<li>Imagine you start a small tailoring business and need to keep customer records and print receipts. Which device would you choose and why?</li>
<li>Write two advantages and two disadvantages of a laptop compared with a desktop.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Desktops are fixed and good value; laptops are portable with a battery.</li>
<li>Tablets and smartphones use touch screens and are best for communication and light tasks.</li>
<li>Servers and embedded computers work quietly behind the services we use every day.</li>
<li>Choose a device by thinking about the job, the budget and the power supply.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'How a computer works',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>This lesson explains in simple terms what happens inside a computer between pressing a key and seeing a letter on the screen. After it, you will be able to describe the roles of input, processing, memory, storage and output. Knowing this helps you understand why a computer slows down or loses unsaved work.</p>
<h3>Binary: the language of computers</h3>
<p>Inside, a computer only understands two states: electricity on or off. We write these as <strong>1</strong> and <strong>0</strong>. This system is called <strong>binary</strong>. A single 1 or 0 is a <strong>bit</strong>, and eight bits together make a <strong>byte</strong>. One byte can hold one letter, such as the letter A. Photos, songs and videos are also stored as long lines of 1s and 0s.</p>
<h3>The journey of a keystroke</h3>
<p>Follow what happens when you type the letter A in a document:</p>
<ol>
<li>You press the key. The keyboard sends a small electrical signal. This is <strong>input</strong>.</li>
<li>The signal travels to the computer, where the <strong>processor</strong> (CPU) works out which key you pressed.</li>
<li>The program (your word processor) is loaded in <strong>memory</strong> (RAM), so the processor can quickly decide where to put the letter.</li>
<li>The letter is sent to the graphics part, which draws it on the <strong>screen</strong>. This is <strong>output</strong>.</li>
<li>When you press save, the document is copied from memory to <strong>storage</strong>, where it stays even after switching off.</li>
</ol>
<h3>Memory and storage are different</h3>
<p>Beginners often mix these up. <strong>Memory (RAM)</strong> is the computer's short-term workspace, like the top of your desk while you work. It is very fast but forgets everything when the power goes off. <strong>Storage</strong> is the long-term cupboard where files are kept safely. It is slower but remembers.</p>
<p>This is why unsaved work is lost in a power cut: it was only on the desk (RAM) and never reached the cupboard (storage). Save often.</p>
<h3>The operating system is the manager</h3>
<p>The <strong>operating system</strong> controls everything. It starts the machine, lets programs share the processor and memory, and handles your files. When you click an icon, the operating system finds the program in storage, loads it into memory and tells the processor to run it.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open a text program (Notepad on Windows or Notes on a phone) and type a short sentence. This text is in memory only.</li>
<li>Save it with a name such as "my first note". Now it is also in storage.</li>
<li>Close the program, open it again and find your file to prove it was stored.</li>
<li>In your own words, explain to a friend why saving protects you from a power cut.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Computers use binary: bits (1 or 0) grouped into bytes.</li>
<li>The processor does the work, memory is the fast short-term workspace, and storage keeps files for the long term.</li>
<li>The operating system manages all the parts and starts programs.</li>
<li>Always save your work; unsaved work lives only in memory.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which of these best describes a computer?
* An electronic machine that accepts data, processes it by following a program, and gives output
- A machine that can only do calculations
- A device that can think and make its own decisions
- A television connected to a keyboard
= A computer follows stored instructions to work on data. It does not think for itself, and it does far more than calculations.

Q: Which of the following are examples of hardware? (choose all that apply)
* A monitor
- Microsoft Word
* A USB flash drive
- The Android operating system
= Hardware is anything physical you can touch. Word and Android are software.

Q: What are the four steps of the information processing cycle, in order?
- Storage, input, output, processing
* Input, processing, storage, output
- Output, input, storage, processing
- Processing, input, output, storage
= Data goes in (input), is worked on (processing), is kept (storage), and results come out (output).

Q: A learner types an essay but the power goes off before they press Save. Why is the work lost?
- The monitor stopped working
* The text was only in RAM, which forgets everything when power is lost
- The hard drive was full
- The keyboard deleted it
= RAM is short-term working memory. Only saved files are copied to long-term storage.

Q: Which device is the best choice for someone who must keep working during frequent power cuts?
- A desktop computer with no battery backup
- An old television
* A laptop with a working battery
- A printer
= A laptop contains its own battery and can keep running for some hours without mains electricity.

Q: What is one byte made of?
- Two bits
- Four bits
* Eight bits
- One hundred bits
= A byte is eight bits and can hold a single letter, number or symbol.

Q: Which statement about the operating system is true?
- It is a type of printer
* It manages the computer's parts and starts programs
- It is only needed on phones
- It can be touched like hardware
= The operating system is system software that manages hardware, programs and files.
QUIZ,
        ],
        [
            'title'   => 'Module 2: Parts and devices',
            'summary' => 'The parts inside and around a computer: processor, memory, storage, ports, peripherals and how to measure memory.',
            'lessons' => [
                [
                    'title'   => 'Inside the computer: CPU, RAM and motherboard',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to name the three core parts inside a computer and say what each does. This helps you read a computer advertisement, understand why one machine is faster than another, and talk sensibly with a technician.</p>
<h3>The CPU: the brain</h3>
<p>The <strong>CPU</strong> (Central Processing Unit), also called the <strong>processor</strong>, carries out instructions. It is a small square chip. Its speed is measured in <strong>gigahertz (GHz)</strong>, and modern processors have several <strong>cores</strong>, which are like several workers inside one chip. More cores help when you run many programs at once. Common makers are Intel, AMD and Qualcomm, and phones use similar processors designed for low power use.</p>
<p>The CPU gets hot while working, so it has a heat sink and a small fan to cool it. If a fan is clogged with dust, the computer may become slow or switch itself off.</p>
<h3>RAM: the workspace</h3>
<p><strong>RAM</strong> (Random Access Memory) holds the programs and files you are using right now. More RAM lets you keep more things open without slowing down. For basic work today, 4 GB is the minimum, 8 GB is comfortable and 16 GB is for heavy work such as video editing. RAM is cleared when the computer is switched off.</p>
<h3>The motherboard: the connector</h3>
<p>The <strong>motherboard</strong> is the large circuit board that everything plugs into. The CPU, RAM, storage drive, graphics and ports all connect to it, and it lets them talk to each other. Think of it as the road network that joins all the buildings in a town.</p>
<h3>Other important internal parts</h3>
<ul>
<li><strong>Power supply:</strong> turns mains electricity into the lower voltages the parts need (in a laptop, the charger and battery do this).</li>
<li><strong>Graphics card or GPU:</strong> draws pictures on the screen. Many computers have it built in.</li>
<li><strong>Sound and network parts:</strong> let the computer play audio and connect to Wi-Fi or a cable.</li>
</ul>
<h3>Reading a specification</h3>
<p>Shops describe a computer with a line like <code>Intel Core i5, 8 GB RAM, 256 GB SSD</code>. This means a mid-level processor, 8 GB of working memory and 256 GB of fast storage. When comparing two computers, look at these three numbers first.</p>
<h3>Try it yourself</h3>
<ol>
<li>On Windows, right-click the <strong>Start</strong> button and choose <strong>System</strong> (Windows 11) or open <strong>Settings</strong>, then <strong>System</strong>, then <strong>About</strong>.</li>
<li>Find the processor name and the installed RAM. Write them down.</li>
<li>Look at the shop advertisement for any computer and explain its specification in your own words.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The CPU does the processing; more GHz and more cores generally means more speed.</li>
<li>RAM is short-term workspace and is cleared at switch off.</li>
<li>The motherboard joins all the parts together.</li>
<li>Check processor, RAM and storage when comparing computers.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Storage and measuring memory',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>You will learn the main types of storage and how to read sizes such as KB, MB, GB and TB. This matters whenever you buy a flash drive or phone, wonder whether a video will fit, or buy mobile data.</p>
<h3>Units of measurement</h3>
<p>Computer storage is counted in bytes. Because one byte is tiny, we use larger units:</p>
<table>
<thead><tr><th>Unit</th><th>Approximate size</th><th>What it can hold</th></tr></thead>
<tbody>
<tr><td>Byte (B)</td><td>One character</td><td>One letter</td></tr>
<tr><td>Kilobyte (KB)</td><td>About 1,000 bytes</td><td>A short text message or a page of text</td></tr>
<tr><td>Megabyte (MB)</td><td>About 1,000 KB</td><td>A photo or a song</td></tr>
<tr><td>Gigabyte (GB)</td><td>About 1,000 MB</td><td>A film or many photos</td></tr>
<tr><td>Terabyte (TB)</td><td>About 1,000 GB</td><td>A whole library of films and files</td></tr>
</tbody>
</table>
<p>Each step is about one thousand times bigger than the one before (computers technically use 1,024, but 1,000 is close enough for everyday use). If your data bundle is 1 GB, it is roughly 1,000 MB.</p>
<h3>Types of storage</h3>
<ul>
<li><strong>Hard disk drive (HDD):</strong> a spinning magnetic disk inside a case. Cheap and large, but slower and can be damaged if dropped.</li>
<li><strong>Solid state drive (SSD):</strong> uses memory chips with no moving parts. Much faster, quieter and tougher, but costs more per GB. A computer with an SSD starts and opens programs far quicker.</li>
<li><strong>Flash drive (USB stick) and memory card:</strong> small portable storage, good for carrying documents between computers. They are easy to lose and can carry viruses.</li>
<li><strong>External hard drive:</strong> a larger portable drive, ideal for backups.</li>
<li><strong>Cloud storage:</strong> space on the internet, such as Google Drive or OneDrive. You can reach files from any device, but you need a connection.</li>
</ul>
<h3>How much storage do you need?</h3>
<p>The operating system and programs take a large part of the drive. For a student, 256 GB is usually enough. Photos and videos use the most space. A one-minute phone video can be over 100 MB.</p>
<h3>Try it yourself</h3>
<ol>
<li>On Windows, open <strong>File Explorer</strong> (press the Windows key + E), then select <strong>This PC</strong>. Note the size and free space of your drive.</li>
<li>On a phone, open <strong>Settings</strong> and find <strong>Storage</strong>. Which type of file uses the most space?</li>
<li>Right-click a photo and choose <strong>Properties</strong> to see its size. Is it in KB or MB?</li>
<li>Work out roughly how many 4 MB photos fit in 2 GB.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Sizes go byte, KB, MB, GB, TB, each about 1,000 times bigger.</li>
<li>SSDs are faster and stronger than HDDs but cost more.</li>
<li>Flash drives are handy but easy to lose, so keep copies elsewhere.</li>
<li>Cloud storage lets you reach files from any device with internet.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Ports, cables and peripherals',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn to recognise common ports and cables and to name the devices you can plug into a computer. You will then be able to connect a mouse, flash drive, projector or printer without fear.</p>
<h3>Ports and connectors</h3>
<p>A <strong>port</strong> is a socket on the computer where you plug in a cable or device. Common ports are:</p>
<table>
<thead><tr><th>Port</th><th>Used for</th></tr></thead>
<tbody>
<tr><td>USB-A (rectangular)</td><td>Flash drives, mice, keyboards, phone chargers</td></tr>
<tr><td>USB-C (small oval)</td><td>Charging, data and sometimes video on newer devices</td></tr>
<tr><td>HDMI</td><td>Sends picture and sound to a monitor, TV or projector</td></tr>
<tr><td>VGA</td><td>Older blue connector for monitors and projectors</td></tr>
<tr><td>Ethernet (network)</td><td>Wired internet connection</td></tr>
<tr><td>Headphone socket</td><td>Headphones or speakers</td></tr>
<tr><td>Power socket</td><td>Charger or power cable</td></tr>
</tbody>
</table>
<p>Many connectors fit only one way, so never force a plug. If it does not go in, turn it over gently.</p>
<h3>Peripherals</h3>
<p>A <strong>peripheral</strong> is any device you connect to the computer. They fall into groups:</p>
<ul>
<li><strong>Input devices:</strong> keyboard, mouse, scanner, microphone, webcam, touch screen.</li>
<li><strong>Output devices:</strong> monitor, speakers, headphones, printer, projector.</li>
<li><strong>Both input and output:</strong> a touch screen, a headset with microphone and external drives that you read from and write to.</li>
</ul>
<h3>Printers and scanners</h3>
<p>An <strong>inkjet printer</strong> sprays tiny drops of ink and is good for colour photos and low volumes. A <strong>laser printer</strong> uses toner powder and is faster and cheaper per page for lots of text. A <strong>scanner</strong> turns a paper page into a digital image or document. Many machines combine printing, scanning and copying.</p>
<h3>Wireless connections</h3>
<p>Not everything needs a cable. <strong>Bluetooth</strong> connects over short distances (headphones, mice, speakers). <strong>Wi-Fi</strong> connects you to a network and the internet through a router. Wireless devices still need charged batteries or power.</p>
<h3>Try it yourself</h3>
<ol>
<li>Look at the sides and back of a computer or laptop. List every port you can find and match it to the table.</li>
<li>Plug a flash drive into a USB port (or a mouse if available) and see what the computer shows.</li>
<li>Decide whether these are input or output: microphone, projector, scanner, speakers.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Ports connect devices; USB is the most common.</li>
<li>Input devices send data in; output devices bring results out.</li>
<li>Inkjet suits colour and small jobs; laser suits many pages of text.</li>
<li>Never force a plug; check the direction first.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which part of a computer is often called its "brain"?
- RAM
- The motherboard
* The CPU
- The power cable
= The CPU (processor) carries out instructions and does the actual processing.

Q: What is the main job of the motherboard?
* To connect all the parts so they can communicate
- To store files permanently
- To cool the processor
- To display pictures
= Every part plugs into the motherboard, which lets them work together.

Q: A computer is advertised as "8 GB RAM, 256 GB SSD". What does the 256 GB refer to?
- The working memory
- The speed of the processor
* The size of the storage drive
- The screen size
= The SSD is the storage drive. RAM is the 8 GB figure.

Q: Which sequence of units goes from smallest to largest?
- GB, MB, KB, TB
* KB, MB, GB, TB
- MB, KB, TB, GB
- TB, GB, MB, KB
= Each unit is about 1,000 times larger than the one before it.

Q: Which are advantages of an SSD over an HDD? (choose all that apply)
* It is faster
* It has no moving parts
- It is always cheaper per GB
- It does not need a computer to work
= SSDs are quicker and tougher, but they usually cost more per GB.

Q: Which port would you most likely use to send picture and sound from a laptop to a projector or TV?
- Headphone socket
- Ethernet
* HDMI
- Power socket
= HDMI carries both video and audio to a screen or projector.

Q: Which of these is an output device?
- Scanner
- Microphone
* Printer
- Keyboard
= A printer takes information out of the computer onto paper.

Q: Why should you keep a second copy of files that are only on a flash drive?
- Flash drives cannot store documents
* They are easy to lose, damage or infect with viruses
- Flash drives delete files every week
- Computers cannot read them
= Small portable drives are easily lost or broken, so an extra copy protects your work.
QUIZ,
        ],
        [
            'title'   => 'Module 3: Starting and using a computer',
            'summary' => 'Switching on and off, the desktop, the mouse and touchpad, the keyboard, and working with windows and programs.',
            'lessons' => [
                [
                    'title'   => 'Switching on, logging in and shutting down',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to start a computer, sign in, and shut it down correctly. Doing this properly protects your files and your equipment, especially where power cuts are common.</p>
<h3>Before you switch on</h3>
<p>Check that the power cable is plugged in firmly, the monitor is on, and the wall socket or surge protector (see Module 5) has electricity. On a laptop, check the battery has some charge or plug in the charger.</p>
<h3>Starting the computer</h3>
<ol>
<li>Press the <strong>power button</strong> once, then release it. It often has a circle with a line through the top.</li>
<li>Wait while the computer <strong>boots</strong> (starts up). You may see a logo and a loading circle. Do not press keys or switch it off.</li>
<li>When you see the <strong>lock screen</strong> (a picture, with the time), press any key or click to continue.</li>
</ol>
<h3>Logging in</h3>
<p>A <strong>user account</strong> keeps your files and settings separate from other people. You sign in by typing a <strong>password</strong> or <strong>PIN</strong>, or by using a fingerprint or face on some laptops. Type the password slowly and check that Caps Lock is off, because passwords are case sensitive. Never share your password and never write it on a sticky note on the screen.</p>
<p>If several people use the same computer, click your own name or picture on the sign-in screen. Always <strong>sign out</strong> when you finish on a shared computer so others cannot see your work.</p>
<h3>Shutting down safely</h3>
<ol>
<li>Save your work and close your programs.</li>
<li>Select the <strong>Start</strong> button (the Windows logo at the bottom).</li>
<li>Select the <strong>Power</strong> icon, then <strong>Shut down</strong>.</li>
<li>Wait until the screen goes black and lights go off before removing power.</li>
</ol>
<h3>Other power choices</h3>
<table>
<thead><tr><th>Choice</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Sleep</td><td>Keeps your work in memory using a little power; wakes in seconds</td></tr>
<tr><td>Restart</td><td>Shuts down and starts again; fixes many small problems</td></tr>
<tr><td>Shut down</td><td>Closes everything and turns off completely</td></tr>
<tr><td>Sign out</td><td>Closes your account but leaves the computer on for someone else</td></tr>
</tbody>
</table>
<p>If the computer freezes and nothing responds, hold the power button for about five seconds to force it off. Use this only as a last resort, because unsaved work will be lost.</p>
<h3>Try it yourself</h3>
<ol>
<li>Start your computer and watch the boot process.</li>
<li>Sign in with your account. Notice the name shown on the sign-in screen.</li>
<li>Choose <strong>Restart</strong> from the Power menu and see it come back on.</li>
<li>Finally, shut down properly and wait for the lights to go off.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Start with the power button and wait for the computer to finish booting.</li>
<li>Keep your password private and sign out on shared computers.</li>
<li>Always shut down through the Start menu, not by pulling the plug.</li>
<li>Forced shutdown by the power button is a last resort.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The desktop, mouse and touchpad',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>In this lesson you will become comfortable with the desktop and with controlling the pointer using a mouse or touchpad. These skills are used in every program you will ever open.</p>
<h3>The desktop</h3>
<p>The <strong>desktop</strong> is the main screen you see after logging in. It is like the top of a real desk. The parts you will see are:</p>
<ul>
<li><strong>Background (wallpaper):</strong> the picture behind everything.</li>
<li><strong>Icons:</strong> small pictures that open programs, files or folders, such as the <strong>Recycle Bin</strong>.</li>
<li><strong>Taskbar:</strong> the strip along the bottom with the Start button, search, open programs and the clock.</li>
<li><strong>Pointer (cursor):</strong> the small arrow that follows your mouse.</li>
</ul>
<h3>Mouse actions</h3>
<table>
<thead><tr><th>Action</th><th>How</th><th>Used for</th></tr></thead>
<tbody>
<tr><td>Click</td><td>Press the left button once</td><td>Select something</td></tr>
<tr><td>Double-click</td><td>Press the left button twice quickly</td><td>Open an icon or file</td></tr>
<tr><td>Right-click</td><td>Press the right button once</td><td>Open a menu of choices</td></tr>
<tr><td>Drag and drop</td><td>Hold the left button, move, then release</td><td>Move an item</td></tr>
<tr><td>Scroll</td><td>Roll the wheel</td><td>Move up or down a page</td></tr>
</tbody>
</table>
<p>Hold the mouse lightly with your palm on top, index finger on the left button and middle finger on the right one. Keep your wrist straight and move the mouse slowly at first. If you run out of space, lift the mouse and put it back down in the middle.</p>
<h3>Using a touchpad</h3>
<p>The <strong>touchpad</strong> on a laptop does the same jobs. Slide one finger to move the pointer. Tap once to click and tap with two fingers to right-click. Slide two fingers up or down to scroll. Three fingers swiped up show all your open windows on many Windows laptops. The buttons below the pad work like mouse buttons.</p>
<h3>Phone and tablet gestures</h3>
<p>On a touch screen you <strong>tap</strong> to select, <strong>press and hold</strong> for a menu, <strong>swipe</strong> to scroll, and <strong>pinch</strong> two fingers together or apart to zoom.</p>
<h3>Try it yourself</h3>
<ol>
<li>Practise moving the pointer to each corner of the screen and back.</li>
<li>Right-click an empty space on the desktop and read the menu, then press <strong>Esc</strong> to close it.</li>
<li>Double-click the <strong>Recycle Bin</strong> icon to open it, then close it with the <strong>X</strong> in the top right corner.</li>
<li>Drag a desktop icon a short way and drop it. Notice it moves.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The desktop has a wallpaper, icons and a taskbar.</li>
<li>Click selects, double-click opens, right-click shows options, and drag moves.</li>
<li>Touchpads use one, two or three finger gestures; phones use tap, swipe and pinch.</li>
<li>Move slowly at first; speed comes with practice.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Keyboard layout and shortcuts',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>You will learn what the keys on a keyboard do and a few shortcuts that save time. Good keyboard knowledge makes everything from writing a CV to filling in forms faster.</p>
<h3>The areas of a keyboard</h3>
<ul>
<li><strong>Letter keys:</strong> the main block, laid out in the QWERTY pattern (named after the first six letters on the top row).</li>
<li><strong>Number row:</strong> numbers and symbols above the letters.</li>
<li><strong>Numeric keypad:</strong> numbers on the right of full keyboards, switched with <strong>Num Lock</strong>.</li>
<li><strong>Function keys (F1 to F12):</strong> special jobs depending on the program. F5, for example, refreshes a web page.</li>
<li><strong>Arrow keys:</strong> move the cursor up, down, left and right.</li>
</ul>
<h3>Important keys</h3>
<table>
<thead><tr><th>Key</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Spacebar</td><td>Adds a space</td></tr>
<tr><td>Enter</td><td>Starts a new line or confirms a choice</td></tr>
<tr><td>Backspace</td><td>Deletes the character before the cursor</td></tr>
<tr><td>Delete</td><td>Deletes the character after the cursor, or the selected item</td></tr>
<tr><td>Shift</td><td>Hold to type capital letters and the top symbol on a key</td></tr>
<tr><td>Caps Lock</td><td>Switches capitals on and off</td></tr>
<tr><td>Tab</td><td>Jumps to the next box or indents text</td></tr>
<tr><td>Esc</td><td>Cancels or closes a menu</td></tr>
<tr><td>Ctrl, Alt, Windows key</td><td>Used in combinations for shortcuts</td></tr>
</tbody>
</table>
<h3>Useful shortcuts</h3>
<p>A <strong>shortcut</strong> means holding one key while pressing another. Learn these first (Mac keys in brackets):</p>
<table>
<thead><tr><th>Shortcut</th><th>Action</th></tr></thead>
<tbody>
<tr><td>Ctrl + C (Cmd + C)</td><td>Copy</td></tr>
<tr><td>Ctrl + X (Cmd + X)</td><td>Cut</td></tr>
<tr><td>Ctrl + V (Cmd + V)</td><td>Paste</td></tr>
<tr><td>Ctrl + Z (Cmd + Z)</td><td>Undo your last action</td></tr>
<tr><td>Ctrl + S (Cmd + S)</td><td>Save</td></tr>
<tr><td>Ctrl + A (Cmd + A)</td><td>Select everything</td></tr>
</tbody>
</table>
<h3>Good typing habits</h3>
<p>Place your fingers on the <strong>home row</strong>: left hand on A S D F, right hand on J K L and the semicolon. Small bumps on F and J help you find the position without looking. Use all ten fingers, look at the screen and not the keys, and aim for accuracy first, speed later.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open Notepad and type your full name, then press <strong>Enter</strong> and type your school or village.</li>
<li>Hold <strong>Shift</strong> and type your name in capitals; then fix a mistake using <strong>Backspace</strong>.</li>
<li>Select your text with <strong>Ctrl + A</strong>, copy it with <strong>Ctrl + C</strong>, click at the end and paste it with <strong>Ctrl + V</strong>.</li>
<li>Press <strong>Ctrl + Z</strong> to undo the paste.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Know the letter, number, function and arrow areas of the keyboard.</li>
<li>Learn Ctrl + C, X, V, Z, S and A first.</li>
<li>Rest your fingers on the home row and type without looking down.</li>
<li>Accuracy matters more than speed when starting.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Working with windows and programs',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to open programs, manage several windows at once and close them properly. This is the skill that makes you productive, because real work often means switching between a document, a web page and a calculator.</p>
<h3>Opening a program</h3>
<ol>
<li>Select the <strong>Start</strong> button.</li>
<li>Find the program in the list or type its name in the search box, for example "Notepad".</li>
<li>Click it once. The program opens in its own <strong>window</strong>.</li>
</ol>
<p>You can also double-click a desktop icon, or click an icon pinned to the taskbar.</p>
<h3>Parts of a window</h3>
<ul>
<li><strong>Title bar:</strong> the top strip showing the program and file name. Drag it to move the window.</li>
<li><strong>Minimise (-):</strong> hides the window to the taskbar; the program keeps running.</li>
<li><strong>Maximise (square):</strong> makes the window fill the screen. Click again to restore it.</li>
<li><strong>Close (X):</strong> shuts the window and the program.</li>
<li><strong>Menu bar or ribbon:</strong> the commands, such as File, Edit and View.</li>
<li><strong>Scroll bar:</strong> lets you move through long content.</li>
</ul>
<h3>Menus, dialog boxes and buttons</h3>
<p>A <strong>menu</strong> lists choices; click one to run it. Items followed by three dots (...) open a <strong>dialog box</strong> where you must make further choices, then press <strong>OK</strong> or <strong>Cancel</strong>. A tick box allows several choices; a round option button allows only one.</p>
<h3>Switching between programs</h3>
<table>
<thead><tr><th>Method</th><th>How</th></tr></thead>
<tbody>
<tr><td>Taskbar</td><td>Click the icon of the program you want</td></tr>
<tr><td>Alt + Tab</td><td>Hold Alt and press Tab to cycle through open windows</td></tr>
<tr><td>Windows key + Tab</td><td>Show all windows (Task View)</td></tr>
</tbody>
</table>
<h3>Closing properly</h3>
<p>Save your work first. If the program asks "Do you want to save changes?" choose <strong>Save</strong>. Closing with the X, or pressing <strong>Alt + F4</strong>, ends the program. Too many programs left open use memory and may slow the computer.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open <strong>Notepad</strong> and <strong>Calculator</strong> from the Start menu.</li>
<li>Switch between them with the taskbar and then with <strong>Alt + Tab</strong>.</li>
<li>Minimise Notepad, then bring it back by clicking its taskbar icon.</li>
<li>Drag Calculator by its title bar to the side of the screen.</li>
<li>Close both programs with the X button.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Open programs from Start, search or taskbar icons.</li>
<li>Minimise hides, maximise enlarges and close ends the program.</li>
<li>Alt + Tab switches fast between open windows.</li>
<li>Save before you close, and close what you no longer use.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the safest way to turn off a Windows computer?
- Pull out the power cable
- Hold the power button every time
* Save your work, then use Start, Power, Shut down
- Close the monitor only
= Shutting down through the Start menu lets the system close files properly and avoids damage.

Q: You are using a shared computer in a school lab and have finished. What should you do?
- Walk away and leave it on your page
* Save your work and sign out
- Write your password on a note by the screen
- Unplug the keyboard
= Signing out protects your files and your accounts from the next user.

Q: What does a double-click normally do to a desktop icon?
- Deletes it
- Shows a menu
* Opens it
- Copies it
= A double-click opens a program, file or folder.

Q: On a laptop touchpad, how do you usually right-click?
* Tap with two fingers
- Tap once with one finger
- Press the power button
- Hold Shift and slide
= A two-finger tap normally acts as a right-click on most laptops.

Q: Which shortcut copies selected text?
- Ctrl + V
- Ctrl + Z
* Ctrl + C
- Ctrl + S
= Ctrl + C copies. Ctrl + V pastes, Ctrl + Z undoes, and Ctrl + S saves.

Q: What does minimising a window do?
- Closes the program and deletes the file
* Hides the window on the taskbar while the program keeps running
- Makes it fill the screen
- Restarts the computer
= Minimise is for hiding a window temporarily. Click its taskbar icon to bring it back.

Q: Where should your fingers rest when touch typing?
- On the number row
* On the home row (A S D F and J K L ;)
- On the function keys
- On the arrow keys
= The home row is the starting position from which all other keys can be reached.
QUIZ,
        ],
        [
            'title'   => 'Module 4: Software and apps',
            'summary' => 'Operating systems, applications, safe installation, updates and licences.',
            'lessons' => [
                [
                    'title'   => 'Operating systems',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn what an operating system does and recognise the main ones used on computers and phones. This helps you choose a device, download the right apps and ask for help in the right way.</p>
<h3>What an operating system does</h3>
<p>The <strong>operating system (OS)</strong> is the main software on any computer or phone. It starts the machine, lets you open programs, manages files, controls the screen and connects to Wi-Fi and printers. Without an OS, the hardware is just a collection of parts.</p>
<h3>Common operating systems</h3>
<table>
<thead><tr><th>Operating system</th><th>Used on</th><th>Notes</th></tr></thead>
<tbody>
<tr><td>Windows</td><td>Most PCs and laptops</td><td>Made by Microsoft; Windows 10 and 11 are common in Uganda</td></tr>
<tr><td>macOS</td><td>Apple Mac computers</td><td>Used in design and media work; runs only on Apple hardware</td></tr>
<tr><td>Linux</td><td>PCs, servers, many school labs</td><td>Free and open source; many versions such as Ubuntu</td></tr>
<tr><td>Android</td><td>Most smartphones and tablets</td><td>Made by Google; used by many different phone makers</td></tr>
<tr><td>iOS (iPadOS)</td><td>iPhone and iPad</td><td>Made by Apple</td></tr>
<tr><td>ChromeOS</td><td>Chromebook laptops</td><td>Works mostly with web apps</td></tr>
</tbody>
</table>
<h3>The user interface</h3>
<p>The part you see and touch is the <strong>user interface (UI)</strong>. Most systems today use a <strong>graphical user interface (GUI)</strong> with windows, icons, menus and a pointer. Phones use touch interfaces. Older systems used only typed commands.</p>
<h3>What the OS manages</h3>
<ul>
<li><strong>Processor and memory:</strong> shares them fairly between programs.</li>
<li><strong>Files:</strong> stores them in folders you can find again.</li>
<li><strong>Devices:</strong> talks to printers, cameras and flash drives through small programs called <strong>drivers</strong>.</li>
<li><strong>Security:</strong> controls who may sign in and what each user may change.</li>
</ul>
<h3>Why it matters when buying</h3>
<p>A program made for Windows will not run directly on Android, and an app from the Play Store is for Android only. Before buying or downloading, check that it supports your operating system. Also check the <strong>version</strong>: very old versions stop receiving security updates and become risky.</p>
<h3>Try it yourself</h3>
<ol>
<li>On Windows, press the Windows key + R, type <code>winver</code> and press Enter to see your Windows version. On Android, open <strong>Settings</strong>, then <strong>About phone</strong>.</li>
<li>Write down the name and version of the operating system on your device.</li>
<li>Ask two friends what operating system their phones use and compare.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The operating system manages hardware, files and programs.</li>
<li>Windows, macOS and Linux run on computers; Android and iOS run on phones.</li>
<li>Apps must match your operating system.</li>
<li>Keep the OS updated so it stays secure.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Applications and installing software safely',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>You will learn about the main kinds of application software and how to install and remove programs without bringing viruses onto your device. This matters because careless downloads are one of the most common ways computers and phones get infected.</p>
<h3>Types of application software</h3>
<ul>
<li><strong>Productivity:</strong> word processors, spreadsheets, presentation programs.</li>
<li><strong>Communication:</strong> email, WhatsApp, Zoom, Google Meet.</li>
<li><strong>Web browsers:</strong> Chrome, Edge, Firefox, Safari, used to visit websites.</li>
<li><strong>Media:</strong> music, video players and photo editors.</li>
<li><strong>Utilities:</strong> antivirus, file compressors, backup tools.</li>
<li><strong>Games and education:</strong> learning programs, typing tutors, games.</li>
</ul>
<h3>Where to get software safely</h3>
<p>Use only <strong>official sources</strong>:</p>
<ul>
<li>On Android, the <strong>Google Play Store</strong>. On iPhone, the <strong>App Store</strong>.</li>
<li>On Windows, the <strong>Microsoft Store</strong> or the official website of the company that makes the program.</li>
<li>Avoid cracked or "free full version" downloads and files sent by strangers. They often contain hidden malware, which is harmful software.</li>
</ul>
<h3>Installing an app on Windows</h3>
<ol>
<li>Download the installer from the official website. It usually ends with <code>.exe</code> or <code>.msi</code>.</li>
<li>Double-click the file. If Windows asks "Do you want to allow this app to make changes?", read the publisher name before choosing <strong>Yes</strong>.</li>
<li>Follow the steps. Read each screen, because some installers try to add extra toolbars or programs; untick anything you do not want.</li>
<li>When it finishes, look for the program in the Start menu.</li>
</ol>
<h3>Installing on a phone</h3>
<p>Open the store, search for the app, check the developer's name, the number of downloads and the reviews, then tap <strong>Install</strong>. Look at the permissions it asks for. A torch app does not need access to your contacts.</p>
<h3>Uninstalling software</h3>
<p>On Windows, open <strong>Settings</strong>, select <strong>Apps</strong>, then <strong>Installed apps</strong> (or Apps and features), choose the program and select <strong>Uninstall</strong>. Do not just delete its icon; that leaves the program on your drive. On Android, press and hold the app icon and choose <strong>Uninstall</strong>.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open your phone's app store or the Microsoft Store and look at one free app's page. Note its publisher, rating and the permissions or description.</li>
<li>On Windows, open <strong>Settings</strong>, then <strong>Apps</strong>, and look through the installed apps. Which ones do you not use?</li>
<li>Write a short rule for yourself about where you will download software from.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Application software helps you do tasks; there are many types.</li>
<li>Download only from official stores and official company websites.</li>
<li>Read installer screens and permission requests before you accept.</li>
<li>Uninstall through Settings, not by deleting the icon.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Updates, licences, free and paid software',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>After this lesson you will understand why software needs updates, what a licence is, and how free and paid software differ. This helps you save money legally and keep your device secure.</p>
<h3>Why update?</h3>
<p>Software makers regularly release <strong>updates</strong>. They fix mistakes, add features and, most importantly, close <strong>security holes</strong> that criminals could use. A device that is never updated slowly becomes unsafe. Updates may use data, so on mobile data, wait for Wi-Fi when you can.</p>
<p>On Windows, updates are found under <strong>Settings</strong>, then <strong>Windows Update</strong>. On Android, open the <strong>Play Store</strong>, tap your profile picture and choose <strong>Manage apps and device</strong> to see updates. You can also turn on automatic updates.</p>
<h3>What is a licence?</h3>
<p>When you buy or download software, you do not own the program; you receive a <strong>licence</strong>, which is permission to use it under certain rules. The rules may say how many computers you may install it on, and whether you may share it or sell it. Using software without paying for it when payment is required is called <strong>piracy</strong>. It is illegal and also risky, because pirated copies often carry malware and cannot be updated safely.</p>
<h3>Types of software by cost</h3>
<table>
<thead><tr><th>Type</th><th>Meaning</th><th>Example idea</th></tr></thead>
<tbody>
<tr><td>Commercial (paid)</td><td>You pay once or subscribe monthly or yearly</td><td>Microsoft 365 subscription</td></tr>
<tr><td>Freeware</td><td>Free to use, but you cannot change it</td><td>Many free apps and viewers</td></tr>
<tr><td>Free with ads or in-app purchases</td><td>Free to start, with adverts or paid extras</td><td>Many phone games</td></tr>
<tr><td>Shareware or trial</td><td>Free for a limited time or limited features</td><td>30-day trial of a program</td></tr>
<tr><td>Open source</td><td>Free, and the code is public so people can study and improve it</td><td>Linux, LibreOffice</td></tr>
</tbody>
</table>
<h3>Cheaper legal choices</h3>
<p>You can do most office work without spending money. LibreOffice is a free office suite, and Google Docs and Sheets work in a web browser with a free account. Many schools and organisations also offer education licences at a discount, so always ask.</p>
<h3>Try it yourself</h3>
<ol>
<li>Check whether your phone or computer has updates waiting, and install them when you have Wi-Fi.</li>
<li>Find one free alternative to a paid program you have heard of, and note whether it is open source or freeware.</li>
<li>Explain to a classmate in two sentences why using cracked software is a bad idea.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Updates fix problems and security holes; install them regularly.</li>
<li>A licence gives you permission to use software; piracy breaks it.</li>
<li>Software can be paid, free, trial or open source.</li>
<li>Good free tools exist for most everyday tasks.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which of these is an operating system?
- Microsoft Word
- Google Chrome
* Android
- WhatsApp
= Android manages the phone's hardware. The others are applications.

Q: A friend sends you a link to a "free full version" of a paid program. What is the best response?
- Download it quickly before it disappears
- Install it and turn off the antivirus
* Do not use it; get software from the official source or choose a free legal alternative
- Share it with everyone you know
= Cracked programs are illegal and often carry malware.

Q: Why are software updates important? (choose all that apply)
* They close security holes
* They fix mistakes in the program
- They make the device immune to all viruses
- They always remove the need for passwords
= Updates improve safety and quality, but they do not make a device invulnerable.

Q: What is the correct way to remove a program from Windows?
- Delete its desktop icon
* Use Settings, then Apps, and choose Uninstall
- Switch off the computer
- Drag it into a flash drive
= Deleting an icon only removes a shortcut, not the program.

Q: Which of the following is open source software?
- A pirated copy of a paid program
- A phone game with paid extras
* Linux
- A 30-day trial program
= Open source means the code is public and may be studied and improved. Linux is a well-known example.

Q: A flashlight app asks for permission to read all your contacts and messages. What should you do?
- Accept, apps always need this
* Be careful; the permission does not fit the app, so do not install it
- Install it and ask your friend for the PIN
- Turn off your phone
= An app should only request permissions it truly needs.

Q: What does a software licence give you?
- Ownership of the program's code
- The right to sell copies
* Permission to use the software under certain rules
- Protection from viruses
= A licence is permission to use software according to the maker's terms.
QUIZ,
        ],
        [
            'title'   => 'Module 5: Being digital',
            'summary' => 'Digital literacy, passwords, backups, caring for equipment, power protection, online manners and healthy use.',
            'lessons' => [
                [
                    'title'   => 'What is digital literacy?',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn what digital literacy means and see how it affects school, work and daily life in Uganda. By the end you will be able to name the skills you already have and the ones you still want to develop.</p>
<h3>A simple meaning</h3>
<p><strong>Digital literacy</strong> is the ability to use digital devices and the internet to find information, communicate, create and solve problems, safely and sensibly. Just as reading and writing opened doors in the past, digital skills open doors today.</p>
<h3>Why it matters</h3>
<ul>
<li><strong>Education:</strong> many schools use online registration, e-learning and research on the web.</li>
<li><strong>Work:</strong> jobs ask for typed applications, email and basic office programs, and many small businesses use phones to sell and to receive payments.</li>
<li><strong>Services:</strong> paying bills, getting government information, banking and mobile money are increasingly digital.</li>
<li><strong>Connection:</strong> staying in touch with family and finding communities and opportunities.</li>
</ul>
<h3>The main areas of digital literacy</h3>
<table>
<thead><tr><th>Area</th><th>Example skills</th></tr></thead>
<tbody>
<tr><td>Using devices</td><td>Start a computer, use a mouse and keyboard, open apps</td></tr>
<tr><td>Information</td><td>Search the web, judge if a source is reliable</td></tr>
<tr><td>Communication</td><td>Write an email, join a video call, share politely</td></tr>
<tr><td>Creating</td><td>Make a document, a poster or a slide show</td></tr>
<tr><td>Safety</td><td>Use passwords, avoid scams, protect privacy</td></tr>
</tbody>
</table>
<h3>A first look at the internet</h3>
<p>The <strong>internet</strong> is a huge network that joins computers around the world. The <strong>web</strong> is the collection of websites you view on it with a <strong>browser</strong> such as Chrome or Edge. You connect through Wi-Fi or mobile data. A <strong>search engine</strong> (for example Google) helps you find pages by typing words. You will learn more in the internet courses of the Academy.</p>
<h3>Checking if information is true</h3>
<p>Not everything online is correct. Before you believe or share something, ask: Who wrote it? Can I find the same information on another trusted site? Is it very new, very shocking, or asking me to forward it to ten people? Those are warning signs of rumours and fake news.</p>
<h3>Try it yourself</h3>
<ol>
<li>Draw the table above and mark each area as "strong", "growing" or "new to me".</li>
<li>Choose one skill from a "new to me" row and write a plan to learn it this month.</li>
<li>Search for a piece of news, find it on two different sites, and note whether they agree.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Digital literacy means using technology confidently, safely and wisely.</li>
<li>It covers devices, information, communication, creating and safety.</li>
<li>The internet connects computers; the web is what you browse.</li>
<li>Always question information before you believe or share it.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Passwords and backups',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>You will learn how to create strong passwords and how to make backups so you never lose important files. These two habits protect you from the two biggest digital disasters: someone breaking into your accounts, and losing your work.</p>
<h3>Strong passwords</h3>
<p>A password is a secret that proves you are who you say you are. Weak passwords such as "123456", "password" or your name can be guessed in seconds. A strong password:</p>
<ul>
<li>is at least 12 characters long;</li>
<li>uses different words, numbers and symbols, such as a sentence you can remember;</li>
<li>is not your name, birthday, phone number or a common word;</li>
<li>is different for each important account, so one leak does not open every door.</li>
</ul>
<p>A good method is a <strong>passphrase</strong>: pick four unrelated words and add a number and a symbol, for example <code>Green-Boda-Mango-47!</code> (use your own, not this one). Never share passwords, even with friends, and never tell anyone the codes sent to your phone, including by mobile money agents or callers who claim to be from the bank.</p>
<h3>Extra protection: two-step verification</h3>
<p><strong>Two-step verification</strong> asks for a second proof, such as a code sent to your phone, after the password. Turn it on for email and social media. Then even a stolen password is not enough.</p>
<h3>Why backups matter</h3>
<p>Computers can be stolen, dropped, flooded, infected or stopped by a power surge. A <strong>backup</strong> is an extra copy of your files kept somewhere else. If you have only one copy of your project, it is not safe.</p>
<h3>The 3-2-1 idea</h3>
<ul>
<li><strong>3</strong> copies of important files (the original plus two backups);</li>
<li><strong>2</strong> different places or devices (computer and external drive);</li>
<li><strong>1</strong> copy away from home, for example in cloud storage.</li>
</ul>
<h3>How to back up</h3>
<ol>
<li>Choose your most important folder, such as Documents.</li>
<li>Plug in a flash drive or external drive.</li>
<li>Open File Explorer, copy the folder and paste it onto the drive.</li>
<li>Also upload it to a cloud account such as Google Drive or OneDrive.</li>
<li>Repeat weekly, or whenever you finish important work.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Create a new passphrase for one of your accounts, and write a hint (not the password) in a safe place.</li>
<li>Check whether two-step verification is available in your email settings.</li>
<li>Make a test backup of one small folder onto a flash drive and open it there to check it worked.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Long, unique passphrases are strong; never share them.</li>
<li>Two-step verification adds a second lock.</li>
<li>Follow 3-2-1 for backups: three copies, two places, one off-site.</li>
<li>Test your backup, because an unchecked backup may be useless.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Caring for equipment and power protection',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>After this lesson you will know simple ways to protect a computer from dust, heat, water, theft and, most importantly in our setting, unstable electricity. A little care can add years to the life of a machine that may have cost a month of savings.</p>
<h3>Daily care</h3>
<ul>
<li>Keep food and drinks away from keyboards. A small spill can destroy a laptop.</li>
<li>Wash and dry your hands before use if they are dirty or oily.</li>
<li>Do not use a computer on a bed or sofa, where fans become blocked and it overheats.</li>
<li>Close the lid gently and carry laptops in a padded bag.</li>
<li>Keep the computer out of direct sun, rain and damp places.</li>
</ul>
<h3>Cleaning</h3>
<p>Switch off and unplug first. Wipe the screen with a soft, slightly damp cloth, never a spray directly onto the screen. Use a soft brush or a can of compressed air to remove dust from the keyboard and fan openings. Dust is one of the main causes of overheating. Cover computers at night in dusty areas.</p>
<h3>Electricity problems in Uganda</h3>
<p>Power cuts, sudden returns of power, and voltage that is too high or too low can harm electronics. Protect your equipment with:</p>
<table>
<thead><tr><th>Device</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Surge protector (power strip with protection)</td><td>Blocks short, sharp spikes of voltage from reaching the computer</td></tr>
<tr><td>Voltage stabiliser</td><td>Keeps voltage within a safe range</td></tr>
<tr><td>UPS (Uninterruptible Power Supply)</td><td>A battery that keeps the computer running for several minutes during a cut, so you can save and shut down</td></tr>
<tr><td>Solar power with inverter</td><td>Gives a steady supply where the grid is weak, when properly installed</td></tr>
</tbody>
</table>
<p>Choose protection suited to the equipment, and buy from a reputable shop, since cheap fake devices may offer no real protection. During a thunderstorm, unplug computers from the wall, because lightning can pass through the lines.</p>
<h3>Security of the equipment</h3>
<p>Do not leave a laptop or phone unattended in public places, and never leave it visible in a parked vehicle. Mark your device and note the serial number so it can be traced if stolen. Set a screen lock.</p>
<h3>Try it yourself</h3>
<ol>
<li>Look at how your computer is plugged in. Is there a surge protector? Write what you could improve.</li>
<li>Switch off and unplug the device, then gently clean the screen and keyboard.</li>
<li>Find your computer's serial number (usually on a label underneath) and keep it in a notebook.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Keep drinks away, keep fans clear of dust and keep devices cool and dry.</li>
<li>Unstable electricity is a real danger; use a surge protector, stabiliser or UPS.</li>
<li>Unplug in storms.</li>
<li>Protect against theft by never leaving devices unattended.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Healthy and respectful computer use',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>This last lesson is about using technology in a way that is healthy for your body and kind to other people. You will learn how to sit, how to rest your eyes and how to behave online, and you will plan your next steps in the Academy.</p>
<h3>Posture and comfort</h3>
<p>Long hours at a computer can cause back, neck, wrist and eye pain. Set up your space like this:</p>
<ul>
<li>Sit with your back supported and your feet flat on the floor.</li>
<li>Keep your screen about an arm's length away, with the top at or just below eye level.</li>
<li>Keep elbows close to your body at about a right angle and wrists straight when typing.</li>
<li>Make sure the room is well lit and that light does not shine on the screen.</li>
</ul>
<h3>Take breaks</h3>
<p>Try the <strong>20-20-20 rule</strong>: every 20 minutes, look at something 20 feet (about 6 metres) away for 20 seconds. Stand up, stretch and walk for a few minutes every hour. Drink water. If you have headaches, tired eyes or tingling hands, rest and ask a health worker for advice.</p>
<h3>Balance and wellbeing</h3>
<p>Spending too many hours on games and social media can reduce sleep, schoolwork and real friendships. Set limits, keep phones out of the bedroom at night, and remember that people only show their best moments online.</p>
<h3>Respect and ethics online</h3>
<ul>
<li>Treat others the way you would in person. Do not insult, bully or spread rumours.</li>
<li>Ask permission before posting someone's photo, and never share private messages.</li>
<li>Do not copy other people's work and claim it as yours (plagiarism). Give credit.</li>
<li>Think before you post: online content can stay for years and can affect jobs and relationships.</li>
<li>Report abuse, threats or harmful content to a trusted adult, the platform, or the police where it is serious.</li>
</ul>
<h3>Where next?</h3>
<p>You now have a base. Continue in the Academy with <em>Using Windows and Managing Your Files</em>, then typing skills, the internet and email, and Microsoft Office or Google Workspace. Practise a little every day, because regular short practice beats one long session.</p>
<h3>Try it yourself</h3>
<ol>
<li>Adjust your seat, desk or screen to follow the posture advice, then work for 20 minutes.</li>
<li>Set a reminder on your phone to rest your eyes every 20 minutes during study.</li>
<li>Write three personal rules for behaving respectfully online.</li>
<li>Choose the next Academy course you will take and decide on a start date.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Good posture, lighting and regular breaks protect your body.</li>
<li>Use the 20-20-20 rule for your eyes.</li>
<li>Be respectful, protect privacy and give credit for others' work.</li>
<li>Keep learning with short, regular practice.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which of these is the strongest password?
- password123
- Sarah2005
* Green-Boda-Mango-47!
- 12345678
= Long, unrelated words with numbers and symbols are hard to guess. Names, dates and simple sequences are weak.

Q: What is the benefit of two-step verification?
- It makes the internet faster
* A stolen password alone is not enough to open the account
- It removes the need for a password
- It deletes old messages
= A second proof, such as a code, adds a second lock.

Q: According to the 3-2-1 idea, how should you keep backups?
- One copy on the same computer
* Three copies, on two different kinds of storage, with one copy kept away from home
- Two copies on the same flash drive
- Only a printed copy
= Spreading copies across devices and places protects against theft, fire, damage and failure.

Q: What does a UPS do during a power cut?
- Makes the electricity bill smaller
* Keeps the computer running for a few minutes on battery so you can save and shut down
- Removes viruses
- Speeds up the processor
= A UPS has a battery that bridges short outages and protects against sudden shut-offs.

Q: What does the 20-20-20 rule suggest?
* Every 20 minutes, look at something about 20 feet away for 20 seconds
- Type for 20 minutes then sleep for 20 hours
- Use 20 browser tabs at once
- Charge your phone to 20 percent
= It rests the eyes and reduces strain from screens.

Q: Which behaviours show good online manners? (choose all that apply)
* Asking permission before posting someone's photo
* Giving credit when using someone else's work
- Forwarding every rumour you receive
- Insulting people you disagree with
= Respect, privacy and honesty make online life better for everyone.

Q: A stranger calls and asks for the code sent to your phone, saying it is to fix your account. What should you do?
- Give the code, they sound official
* Refuse and end the call; never share codes
- Give half the code
- Send the code by text
= Codes and PINs are private. Real organisations never ask you for them.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which describes the correct order of the information processing cycle?
- Output, storage, input, processing
* Input, processing, storage, output
- Storage, output, processing, input
- Processing, output, input, storage
= Data is entered, worked on, kept, and then shown or printed.

Q: Which of these are hardware? (choose all that apply)
* Keyboard
* Monitor
- Windows
- Google Chrome
= Hardware is physical. Windows and Chrome are software.

Q: Where is an unsaved document held while you are typing it?
- On the SSD
* In RAM
- In the cloud
- In the printer
= Until you save, the work is only in RAM, which is cleared when power is lost.

Q: A learner has a small budget, needs to work at home, and the power fails often. Which choice is most sensible?
- A desktop with no backup power
* A laptop with a good battery, plus a surge protector for charging
- A television
- A second printer
= A laptop's battery keeps working during power cuts, and surge protection guards against spikes.

Q: What is the role of the CPU?
- To store files permanently
- To display pictures
* To carry out instructions and do the processing
- To connect the cables
= The CPU is the processor, or brain, of the computer.

Q: Which sequence of storage units is in the correct order from smallest to largest?
- TB, GB, MB, KB
- MB, KB, GB, TB
* KB, MB, GB, TB
- GB, KB, TB, MB
= Each unit is roughly 1,000 times bigger than the one before.

Q: Approximately how many 5 MB photos fit in 1 GB?
- About 20
- About 50
* About 200
- About 5,000
= One GB is about 1,000 MB, and 1,000 divided by 5 is about 200.

Q: Which storage type has no moving parts and is the fastest of these?
- HDD
* SSD
- CD
- Floppy disk
= An SSD uses chips, so it is faster and tougher than a spinning hard disk.

Q: Which port carries both picture and sound to a TV or projector?
- USB-A
- Ethernet
* HDMI
- Headphone socket
= HDMI sends video and audio together.

Q: Which of these is an input device?
- Printer
- Speakers
- Projector
* Scanner
= A scanner takes information from paper into the computer.

Q: What should you do before removing power from a Windows computer you have finished with?
* Save your work and shut down using the Start menu
- Pull the plug immediately
- Close the lid and unplug it
- Disconnect the keyboard
= A normal shut down closes everything correctly and protects your files.

Q: What does the Sleep option do?
- Deletes your work
* Keeps your work in memory using little power so you can resume quickly
- Turns off all power
- Signs you out for good
= Sleep is a low-power pause; the computer wakes in seconds.

Q: Which action opens a file or program from a desktop icon?
- Right-click
- Single click on the taskbar clock
* Double-click
- Scroll
= Double-click opens the item. Right-click shows a menu.

Q: Which shortcut undoes your last action?
- Ctrl + C
- Ctrl + S
* Ctrl + Z
- Ctrl + A
= Ctrl + Z is Undo (Cmd + Z on a Mac).

Q: What does the minimise button on a window do?
- Closes the program
* Hides the window on the taskbar while the program keeps running
- Deletes the file
- Enlarges the window to full screen
= Minimise hides the window; the program stays open.

Q: Which is an operating system for smartphones?
- Excel
- Linux Mint desktop
- Photoshop
* Android
= Android and iOS are the main phone operating systems.

Q: Where should you download a phone app from?
- A link sent by an unknown person
* The official app store
- A random website offering a "cracked" version
- A USB stick of an unknown owner
= Official stores check apps and make safe downloads more likely.

Q: Why do you read each screen when installing a program?
- To make the installation longer
* To untick extra software you do not want and to check what you are agreeing to
- Because it makes the computer faster
- It is not necessary
= Installers sometimes add extra programs that can be declined.

Q: What is the correct way to remove a program from Windows?
- Drag its icon to a flash drive
* Open Settings, choose Apps, and uninstall it
- Restart three times
- Delete the Start button
= Uninstalling from Settings removes the program properly.

Q: Using cracked or pirated software is a problem because:
- It always runs faster
* It is illegal and often carries malware
- It always costs more
- It uses less electricity
= Pirated copies break licence rules and are a common route for viruses.

Q: Which of these is free, open source software for office work?
- A pirated copy of a paid suite
* LibreOffice
- A stolen licence key
- A trial that expires in 7 days
= LibreOffice is a free office suite whose code is open.

Q: Which makes the best password?
- Your first name and birth year
- 11111111
* A long passphrase of unrelated words with numbers and a symbol
- The word "password"
= Length and unpredictability make passwords hard to guess.

Q: Which are good backup habits? (choose all that apply)
* Keep a copy in the cloud
* Keep a second copy on an external drive
- Keep the only copy on one flash drive
- Never check that the backup opens
= Several copies in different places, and a test that they work, protect your files.

Q: What does a surge protector do?
* Blocks sudden voltage spikes from reaching your equipment
- Makes the internet faster
- Cleans dust from the keyboard
- Adds RAM
= Surges can damage electronics, and a protector absorbs the spike.

Q: During a thunderstorm, what is the safest approach for a desktop computer?
- Keep working normally
* Save your work, shut down and unplug it from the wall
- Put it near a window
- Touch the metal case
= Lightning can travel through power lines. Unplugging avoids damage.

Q: A friend shares a shocking message saying "forward to ten people or something terrible will happen." What is the best response?
- Forward it at once
* Check whether it is true on trusted sources before sharing, and do not forward chain messages
- Post it on every platform
- Reply with your password
= Rumours and chain messages are often false. Check before you share.

Q: According to the 20-20-20 rule, what should you do every 20 minutes?
- Switch off the screen for 20 hours
* Look at something about 6 metres away for 20 seconds
- Type 20 words
- Close 20 tabs
= Looking into the distance relaxes tired eye muscles.

Q: Which sitting habit is best for using a computer?
- Slouching on a bed with the laptop on your knees
* Back supported, feet flat, screen at about arm's length and wrists straight
- Leaning forward with the screen right next to your face
- Standing on one leg
= Good posture reduces pain and fatigue.

Q: A classmate wants to post a photo of you at a party. What is the respectful thing for them to do?
- Post it without asking
* Ask your permission first
- Tag your whole family
- Edit it and say it is theirs
= Respect for privacy means asking before sharing images of other people.

Q: Which of these show digital literacy? (choose all that apply)
* Checking if information is reliable before believing it
* Using strong, different passwords
- Sharing your PIN with a caller from your bank
- Installing software from any random site
= Digital literacy includes using technology safely and critically.
QUIZ,
];
