<?php
return [
    'slug'        => 'computer-maintenance-networking',
    'title'       => 'Computer Maintenance, Troubleshooting and Networking Basics',
    'category'    => 'hardware',
    'level'       => 'beginner',
    'icon'        => 'settings',
    'sort'        => 160,
    'summary'     => 'Learn how computers are built, how to care for them, fix common problems step by step, and set up and troubleshoot a safe home or small office network.',
    'description' => <<<'HTML'
<p>Computers and phones break, slow down and lose connection, and a repair shop is not always nearby or cheap. This course teaches you to look after your devices, find out what is wrong with a calm step-by-step method, and fix the many problems that need no special tools.</p>
<p>You will start with the parts inside a computer and safe working habits, then learn cleaning, battery care, power protection and backups. Next you will practise troubleshooting for slow computers, freezing, overheating and software problems, and finish with networking: how Wi-Fi and the internet reach your device, and how to set up and secure a small network.</p>
<p>Safety comes first. We never ask you to open a power supply or work on mains electricity. For anything dangerous or beyond your confidence, the right answer is to switch off, unplug and call a qualified technician.</p>
HTML,
    'outcomes'    => [
        'Name the main parts of a computer and explain what each one does',
        'Read a specification sheet and compare two computers or phones',
        'Clean and care for devices and batteries safely, and protect them from power problems',
        'Make and follow a backup plan using the 3-2-1 idea',
        'Use a step-by-step method to troubleshoot slow, frozen or overheating computers',
        'Remove problem software, handle updates and basic malware cleaning',
        'Perform basic phone troubleshooting without losing your data',
        'Set up a secure Wi-Fi network and fix common connection problems',
    ],
    'requirements' => [
        'Access to a computer or laptop (and ideally a smartphone)',
        'Basic computer skills are helpful; no repair experience needed',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Microsoft Support', 'url' => 'https://support.microsoft.com'],
        ['title' => 'Android Help (Google)', 'url' => 'https://support.google.com/android'],
        ['title' => 'Apple Support', 'url' => 'https://support.apple.com'],
        ['title' => 'Cisco Networking Academy', 'url' => 'https://www.netacad.com'],
    ],
    'modules' => [

        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: Inside the machine',
            'summary' => 'Work safely, learn the main parts of a computer, and read specifications.',
            'lessons' => [
                [
                    'title'   => 'Safety first: static and power',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>After this lesson you will know the basic rules that keep you and your devices safe when you clean, inspect or handle a computer. Most accidents are easy to prevent, and many hardware faults are caused by careless handling, so safe habits are the first real maintenance skill.</p>
<h3>Electricity can hurt</h3>
<p>Computers use low voltage inside, but they are connected to mains electricity, which can cause serious injury or death. Follow these rules every time:</p>
<ol>
<li><strong>Switch off and unplug</strong> the power cable before you clean or open anything. For a laptop, also remove the charger.</li>
<li><strong>Never open a power supply unit (PSU), a charger or a monitor.</strong> They can hold dangerous electricity even when unplugged. Leave this to a qualified technician.</li>
<li><strong>Never work with wet hands</strong> or on a wet floor, and keep liquids away from devices.</li>
<li><strong>Do not use damaged cables or sockets.</strong> Replace frayed or hot cables.</li>
<li><strong>Do not overload a socket.</strong> Too many devices on one extension can overheat.</li>
</ol>
<h3>Static electricity</h3>
<p><strong>Static electricity</strong> is the small spark you sometimes feel when touching a door handle. It is harmless to you but can damage delicate computer parts such as memory and processors. To reduce the risk:</p>
<ul>
<li>Touch a bare metal part of the computer case (while the computer is unplugged) before touching parts.</li>
<li>Hold cards and memory by their edges, not their gold contacts or chips.</li>
<li>Work on a hard, clean table, not on carpet. Avoid wearing wool or nylon clothing.</li>
<li>Keep parts in antistatic bags until you use them.</li>
<li>If you have an antistatic wrist strap, wear it and clip it to bare metal on the case.</li>
</ul>
<h3>Safe working habits</h3>
<table>
<thead><tr><th>Do</th><th>Do not</th></tr></thead>
<tbody>
<tr><td>Work in good light with a tidy table</td><td>Work in a rush or in a crowded room</td></tr>
<tr><td>Take a photo before removing cables or parts</td><td>Force a part that does not fit</td></tr>
<tr><td>Use the right small screwdriver</td><td>Use a knife or coin to open cases</td></tr>
<tr><td>Keep screws in a small cup or tray</td><td>Leave screws on the floor</td></tr>
<tr><td>Lift heavy parts with care</td><td>Pull on cables instead of the plug</td></tr>
</tbody>
</table>
<h3>Other hazards</h3>
<p>Laptop and phone batteries can swell or catch fire if damaged. Stop using a swollen battery and take it to a technician. Computer parts can have sharp edges. Dust can irritate your lungs, so clean outdoors or wear a simple mask. Never burn old electronics.</p>
<h3>Know your limits</h3>
<p>If a device smells burnt, makes a loud noise, sparks, gets very hot or you are not sure what you are doing, stop. Switch off, unplug and contact a qualified technician.</p>
<h3>Try it yourself</h3>
<ol>
<li>Look at your own workspace and list three electrical or static risks.</li>
<li>Make each one safer, for example by moving a drink, tidying cables or using a proper surge protector.</li>
<li>Practise "ground yourself": with a device unplugged, touch a bare metal part of the case before touching anything else.</li>
<li>Write your own safety checklist of five rules.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Always switch off and unplug before cleaning or inspecting.</li>
<li>Never open a power supply, charger or monitor.</li>
<li>Static electricity can silently damage parts; ground yourself and handle by the edges.</li>
<li>If you are unsure, stop and call a qualified technician.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The main parts of a computer',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to name the main parts inside a computer and explain what each does in simple words. Knowing the parts helps you decide what to fix, what to upgrade, and what to buy.</p>
<h3>The parts and their jobs</h3>
<table>
<thead><tr><th>Part</th><th>What it does</th><th>Everyday comparison</th></tr></thead>
<tbody>
<tr><td>CPU (processor)</td><td>Carries out instructions and calculations</td><td>The brain</td></tr>
<tr><td>RAM (memory)</td><td>Temporary workspace for programs you are using; emptied when power goes off</td><td>The desk you work on</td></tr>
<tr><td>Storage (SSD or HDD)</td><td>Keeps your files and programs permanently</td><td>The filing cabinet</td></tr>
<tr><td>Motherboard</td><td>The main circuit board that connects all parts</td><td>The roads and junctions</td></tr>
<tr><td>PSU (power supply unit)</td><td>Converts mains power into the right power for each part (desktop)</td><td>The heart pumping power</td></tr>
<tr><td>GPU (graphics card)</td><td>Draws images, video and games on the screen</td><td>The artist</td></tr>
<tr><td>Fans and heat sinks</td><td>Move heat away from hot parts</td><td>Cooling system</td></tr>
<tr><td>Battery (laptop, phone)</td><td>Stores power so the device works without a plug</td><td>The fuel tank</td></tr>
</tbody>
</table>
<h3>SSD and HDD</h3>
<p>A <strong>hard disk drive (HDD)</strong> stores data on spinning magnetic disks. It is cheap but slower and can be damaged by knocks. A <strong>solid state drive (SSD)</strong> has no moving parts, is much faster and more shock resistant, and costs more per gigabyte. Replacing an HDD with an SSD is one of the biggest speed boosts for an old computer.</p>
<h3>The GPU</h3>
<p>Many computers have graphics built into the processor, which is enough for office work and video. A separate graphics card is mostly for gaming, video editing and design.</p>
<h3>Ports and connections</h3>
<ul>
<li><strong>USB:</strong> flash drives, mouse, keyboard, phones.</li>
<li><strong>HDMI or VGA:</strong> screens and projectors.</li>
<li><strong>Ethernet (RJ-45):</strong> network cable.</li>
<li><strong>Audio jack:</strong> headphones and microphone.</li>
<li><strong>Power input.</strong></li>
</ul>
<h3>How the parts work together</h3>
<p>When you open a program, the CPU asks storage to load it into RAM. The CPU works on the data in RAM, the GPU shows the result on screen, and the PSU or battery keeps everything powered. Fans take away the heat. If one part is slow or faulty, the whole computer feels slow or fails.</p>
<h3>Try it yourself</h3>
<ol>
<li>Draw a simple diagram on paper showing CPU, RAM, storage, motherboard and PSU, with arrows for how a program opens.</li>
<li>Look at the ports on your computer or laptop and write down their names.</li>
<li>Look at pictures online of an SSD and an HDD and note two differences you can see.</li>
<li>Say aloud to someone what RAM does and what storage does.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>CPU thinks, RAM is short-term workspace, storage keeps files for the long term.</li>
<li>The motherboard connects everything; the PSU supplies power.</li>
<li>SSDs are faster and tougher than HDDs.</li>
<li>Fans and heat sinks control temperature.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Laptop versus desktop, and reading specifications',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to compare laptops and desktops and read a specification sheet (specs) to judge whether a computer suits your needs. This helps you avoid wasting money on the wrong machine, especially when buying second-hand.</p>
<h3>Laptop versus desktop</h3>
<table>
<thead><tr><th>Feature</th><th>Laptop</th><th>Desktop</th></tr></thead>
<tbody>
<tr><td>Portability</td><td>Easy to carry; has a battery</td><td>Fixed in one place</td></tr>
<tr><td>Power cuts</td><td>Keeps working on battery for some hours</td><td>Stops at once unless you have a UPS</td></tr>
<tr><td>Upgrades</td><td>Limited; some parts are fixed to the board</td><td>Easy to upgrade and repair</td></tr>
<tr><td>Price for the same power</td><td>Usually higher</td><td>Usually lower</td></tr>
<tr><td>Space and cables</td><td>Compact</td><td>Needs a desk for the screen, case, keyboard</td></tr>
<tr><td>Heat</td><td>Hot more easily because the space is small</td><td>Better cooling</td></tr>
</tbody>
</table>
<h3>How to find the specifications</h3>
<ol>
<li>On Windows, press the <strong>Windows key</strong>, type <strong>About your PC</strong> and press Enter.</li>
<li>Look under <strong>Device specifications</strong> for the processor, installed RAM and system type.</li>
<li>For storage, open <strong>File Explorer</strong>, select <strong>This PC</strong> and look at the drive sizes.</li>
<li>On a phone, open <strong>Settings</strong> and look for <strong>About phone</strong>.</li>
</ol>
<h3>Reading a spec sheet</h3>
<p>A typical sheet might say: <code>Processor: Intel Core i5, 8 GB RAM, 256 GB SSD, 14-inch screen, battery up to 8 hours</code>. In plain words:</p>
<ul>
<li><strong>Processor:</strong> the family name and generation. Newer is usually faster, but check reviews before buying.</li>
<li><strong>RAM:</strong> 4 GB is the minimum for basic use today; 8 GB is comfortable; more is helpful for heavy work.</li>
<li><strong>Storage:</strong> size in GB or TB, and type. Prefer SSD over HDD.</li>
<li><strong>Screen:</strong> size and resolution (such as 1920 x 1080).</li>
<li><strong>Battery life:</strong> makers' numbers are optimistic; real life is often shorter.</li>
<li><strong>Operating system and age:</strong> an old model may not get updates any more.</li>
</ul>
<h3>Matching specs to needs</h3>
<p>For typing, internet and email, a modest computer with an SSD is enough. For design, video editing or programming, choose more RAM and a stronger processor. Buy for what you will do in the next three years.</p>
<h3>Buying second-hand</h3>
<ul>
<li>Check the screen, keyboard, ports and battery health.</li>
<li>Run it for at least 15 minutes to see if it overheats.</li>
<li>Ask for the charger and check that it is original.</li>
<li>Make sure the system is not locked to a previous owner's account.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Find the specifications of your own computer or phone as shown above and write them down.</li>
<li>Look at a shop's advert for a laptop and explain each line of its specs in your own words.</li>
<li>Choose which of two sample computers is better for a student of accounting and say why.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Laptops are portable with a battery; desktops are cheaper and easier to upgrade.</li>
<li>Check processor, RAM, storage type, screen and battery on a spec sheet.</li>
<li>An SSD makes a big difference to speed.</li>
<li>Test a second-hand computer carefully before buying.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Upgrading RAM and storage: the concept',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will understand when a RAM or storage upgrade helps, what to check before buying parts, and how to approach the job safely. We explain the concept; always find the guide for your exact model and, if you are unsure, ask a qualified technician.</p>
<h3>When is an upgrade worth it?</h3>
<table>
<thead><tr><th>Symptom</th><th>Possible upgrade</th></tr></thead>
<tbody>
<tr><td>Slow when many programs or browser tabs are open</td><td>More RAM</td></tr>
<tr><td>Takes minutes to start; the disk is always at 100%</td><td>Replace HDD with an SSD</td></tr>
<tr><td>Storage almost full</td><td>Bigger drive or an external drive</td></tr>
<tr><td>Slow even when doing one simple task</td><td>May be software, heat, or an old processor; upgrading RAM may not help</td></tr>
</tbody>
</table>
<h3>Check before you buy</h3>
<ol>
<li><strong>Find the model name</strong> (on a sticker or in Settings, About).</li>
<li><strong>Look up its upgrade guide or service manual</strong> from the maker or a trusted site.</li>
<li><strong>Find out the type of RAM</strong> it uses (for example DDR3, DDR4 or DDR5). The wrong type will not fit.</li>
<li><strong>Check the maximum RAM</strong> and how many slots there are.</li>
<li><strong>Find the size and connector of the drive</strong> (2.5-inch SATA, M.2 and so on).</li>
<li><strong>Check that the warranty allows upgrades.</strong></li>
</ol>
<h3>The general idea of the job</h3>
<ol>
<li>Back up your files first.</li>
<li>Shut down, unplug the charger, and remove the battery if the model allows it.</li>
<li>Ground yourself and open the service panel with the correct screwdriver.</li>
<li>Release the clips holding the old RAM stick, remove it, and insert the new stick at the angle shown in the guide until it clicks.</li>
<li>Close the panel, reconnect, and check the new memory size in Settings.</li>
</ol>
<p>Replacing storage also needs a way to put Windows and your files on the new drive. This is either copying (cloning) the old drive or installing Windows fresh and restoring files from a backup.</p>
<h3>Limits and cautions</h3>
<ul>
<li>Many thin laptops and phones have RAM and storage fixed to the board and cannot be upgraded.</li>
<li>Never touch the power supply inside a desktop. If the job requires it, call a technician.</li>
<li>Do not buy very cheap, unknown-brand parts; fake memory and fake-capacity flash drives are common.</li>
<li>Keep old parts, and do not throw them in ordinary rubbish.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Find your computer's model name and search for its upgrade options.</li>
<li>Write down the RAM type, number of slots and maximum supported.</li>
<li>Estimate the cost of an SSD or extra RAM in UGX from a local shop's price list and decide whether it is worth it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>More RAM helps when you multitask; an SSD helps with general speed.</li>
<li>Check compatibility with your exact model before buying.</li>
<li>Back up, power off, unplug and ground yourself before any upgrade.</li>
<li>Some devices cannot be upgraded at all.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What should you always do before cleaning inside a computer?
- Leave it switched on so you can watch it
* Switch it off and unplug the power cable (and charger)
- Spray it with water
- Open the power supply to check it
= Switching off and unplugging removes the main electrical danger.

Q: Which part should you never open yourself?
- The side panel of a desktop case
* The power supply unit or charger
- The keyboard keys
- The dust filter
= Power supplies can store dangerous electricity; leave them to qualified technicians.

Q: What is the job of RAM?
- It stores your photos permanently
* It is the temporary workspace for programs and data in use
- It supplies power
- It connects to the internet
= RAM is short-term memory that is cleared when the power goes off.

Q: Which are good ways to reduce static damage? (choose all that apply)
* Touch bare metal on the case before touching parts
* Hold cards by their edges
- Work on a thick carpet in nylon socks
- Touch the gold contacts of the RAM
= Grounding yourself and careful handling prevent damage from static.

Q: A computer takes minutes to start and the disk is always at 100% use. Which upgrade is likely to help most?
- A larger monitor
* Replacing an HDD with an SSD
- A new mouse
- A longer power cable
= An SSD is much faster at loading the system and programs.

Q: Which is an advantage of a laptop over a desktop in a place with frequent power cuts?
- It never needs a charger
* It can keep working on its battery for some hours
- It is always cheaper
- It cannot overheat
= The battery gives the laptop short-term backup during outages.

Q: Before buying RAM for your laptop you should:
- Choose the cheapest stick
* Find the RAM type and maximum your model supports
- Buy any stick that looks the same
- Remove the CPU
= Different RAM types are not interchangeable, so check the model first.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Care and cleaning',
            'summary' => 'Prevent problems with cleaning, heat control, battery care, power protection, backups and responsible disposal.',
            'lessons' => [
                [
                    'title'   => 'Cleaning screens, keyboards and vents',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to clean the outside of your devices safely and keep dust out of vents. A clean device lasts longer, runs cooler and is more pleasant to use. Dust is one of the main causes of overheating, especially in dusty dry-season conditions.</p>
<h3>What you need</h3>
<ul>
<li>A soft microfibre cloth (a clean lint-free cloth is fine).</li>
<li>A small soft brush or a clean paintbrush.</li>
<li>Cotton buds.</li>
<li>Isopropyl alcohol at 70 percent or a cleaning wipe made for screens (check the maker's advice).</li>
<li>A can of compressed air, or a small hand blower.</li>
</ul>
<h3>Cleaning the screen</h3>
<ol>
<li>Switch the device off and unplug it.</li>
<li>Dampen the cloth slightly with water or screen cleaner. <strong>Never spray liquid directly on the screen.</strong></li>
<li>Wipe gently in one direction without pressing hard.</li>
<li>Dry with the dry part of the cloth.</li>
</ol>
<p>Do not use household glass cleaners, bleach, or rough paper because they can damage coatings.</p>
<h3>Cleaning the keyboard</h3>
<ol>
<li>Switch off. For a laptop, turn it upside down and gently tap to release crumbs.</li>
<li>Use the brush or short bursts of compressed air between the keys.</li>
<li>Wipe key tops with a cloth lightly moistened with alcohol.</li>
<li>If liquid is spilled, switch off at once, unplug, turn the device upside down and take it to a technician quickly. Do not wait for it to dry on its own.</li>
</ol>
<h3>Vents and fans</h3>
<p>Vents let hot air out. Clogged vents cause overheating and noisy fans.</p>
<ul>
<li>Use short bursts of compressed air from a distance, holding the fan blades still with a cotton bud so they do not spin too fast.</li>
<li>Clean outside, or wear a mask, so you do not breathe dust.</li>
<li>For deeper cleaning inside a desktop, switch off, unplug, ground yourself, and clean the dust filters and fans with a soft brush. Do not touch the power supply.</li>
</ul>
<h3>Mouse, ports and phone</h3>
<p>Clean a mouse sensor with a cotton bud. For ports, use a plastic toothpick or a soft brush; never push metal objects into a port. For phones, remove the case occasionally and wipe both the phone and the case.</p>
<h3>How often?</h3>
<table>
<thead><tr><th>Task</th><th>Frequency</th></tr></thead>
<tbody>
<tr><td>Wipe screen and keyboard</td><td>Weekly</td></tr>
<tr><td>Clean vents and filters</td><td>Every 2 to 3 months in dusty places</td></tr>
<tr><td>Deeper clean of a desktop</td><td>Every 6 months</td></tr>
</tbody>
</table>
<h3>Try it yourself</h3>
<ol>
<li>Switch off and unplug a computer or laptop.</li>
<li>Clean the screen and keyboard using the steps above.</li>
<li>Look at the vents with a torch and brush away visible dust.</li>
<li>Note how the device feels and sounds after cleaning.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Switch off and unplug before cleaning.</li>
<li>Apply liquid to the cloth, not to the screen.</li>
<li>Keep vents free of dust to avoid overheating.</li>
<li>If liquid is spilled, switch off immediately and get help.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Heat, battery care and power protection',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn how to control heat, look after batteries, and protect devices from unstable power. Power cuts, surges and low voltage are common in many places and are a major cause of damaged computers, so these habits can save you real money.</p>
<h3>Heat</h3>
<p>All computers make heat. Too much heat shortens the life of parts and slows the machine down.</p>
<ul>
<li>Use a laptop on a hard, flat surface, not on a bed, sofa or your lap for long periods.</li>
<li>Keep it out of direct sunshine and away from stoves.</li>
<li>Leave space behind a desktop for air to flow.</li>
<li>Do not cover vents with paper, cloth or books.</li>
<li>Consider a simple laptop stand to raise the back and improve air flow.</li>
</ul>
<h3>Battery care</h3>
<ol>
<li>Avoid leaving the battery at 0 percent for long periods, and avoid heat, which ages batteries quickly.</li>
<li>If the laptop is nearly always plugged in, check your device settings for a battery-saving or charge-limit option (many makers offer one).</li>
<li>Use the original or a good-quality charger of the correct voltage. Cheap fake chargers can damage the battery and be dangerous.</li>
<li>Do not use a swollen, leaking or very hot battery; stop and take it to a technician.</li>
<li>Store a spare battery or phone at around half charge in a cool place if you will not use it for a long time.</li>
</ol>
<h3>Common power problems</h3>
<table>
<thead><tr><th>Problem</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Power cut</td><td>Computer stops at once; unsaved work and files can be lost or damaged</td></tr>
<tr><td>Surge or spike</td><td>A sudden burst of voltage that can burn parts (for example during lightning or when power returns)</td></tr>
<tr><td>Brown-out (low voltage)</td><td>Voltage too low; causes restarts and stress on parts</td></tr>
</tbody>
</table>
<h3>Protection</h3>
<ul>
<li><strong>Surge protector (power strip with protection):</strong> absorbs spikes. Choose a reliable one and replace it if it has been hit by a big surge; it does not protect against power cuts.</li>
<li><strong>UPS (uninterruptible power supply):</strong> a box with a battery that keeps a desktop running for a few minutes when power fails, giving time to save and shut down. Many also correct low voltage. Choose one big enough for your equipment.</li>
<li><strong>Voltage stabiliser (regulator):</strong> keeps the voltage steady where it swings a lot.</li>
<li><strong>Good wiring and earthing:</strong> ask a qualified electrician to check that sockets are earthed and safe.</li>
</ul>
<p>During thunderstorms, unplug desktop computers, modems and TVs if it is safe to do so.</p>
<h3>Save often</h3>
<p>Turn on autosave in your programs and press <strong>Ctrl + S</strong> regularly. In a place with frequent cuts, this habit matters even more.</p>
<h3>Try it yourself</h3>
<ol>
<li>Look at where you place your laptop or desktop and list two heat risks.</li>
<li>Check the charger label for voltage and see if it matches your device.</li>
<li>Count how many devices share one extension and check if you can reduce it.</li>
<li>Find out the price in UGX of a surge protector and a small UPS at a local shop.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Keep devices cool, ventilated and out of sunshine.</li>
<li>Use correct chargers and avoid damaged or swollen batteries.</li>
<li>Surge protectors guard against spikes; a UPS also covers short power cuts.</li>
<li>Save often and unplug during storms.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Transporting devices and a maintenance schedule',
                    'minutes' => 11,
                    'body'    => <<<'HTML'
<p>After this lesson you will know how to carry devices safely and how to turn good intentions into a simple maintenance schedule that you can actually follow. Preventive maintenance means looking after things before they fail, which is cheaper than repairs.</p>
<h3>Carrying a laptop or phone</h3>
<ul>
<li>Shut down the laptop fully or put it to sleep, and close the lid gently. Do not carry it by the screen.</li>
<li>Use a padded laptop bag or sleeve, and keep the charger in a separate pocket so it does not scratch it.</li>
<li>Do not stuff heavy items on top of the laptop in your bag.</li>
<li>Protect against rain with a plastic bag or waterproof cover.</li>
<li>Avoid leaving devices in hot cars or on a motorbike without protection; heat and vibration damage them.</li>
<li>Use a protective case and screen protector for phones.</li>
<li>Do not leave devices unattended in public places; theft is common.</li>
</ul>
<h3>Moving a desktop</h3>
<ol>
<li>Shut down, unplug all cables and take a photo of the back so that you can reconnect them correctly.</li>
<li>Pack the screen separately with soft padding.</li>
<li>Carry the case with both hands; do not carry it by the front panel.</li>
<li>Let it settle to room temperature before switching on if it has been in cold or very hot places.</li>
</ol>
<h3>A simple preventive maintenance schedule</h3>
<table>
<thead><tr><th>How often</th><th>Task</th></tr></thead>
<tbody>
<tr><td>Daily</td><td>Save work, shut down properly, keep drinks away, check for updates when prompted</td></tr>
<tr><td>Weekly</td><td>Wipe screen and keyboard, restart the computer, run a malware scan, back up important files</td></tr>
<tr><td>Monthly</td><td>Install updates, clear unwanted files, check free disk space, review startup programs</td></tr>
<tr><td>Every 3 months</td><td>Clean vents and dust filters, check cables and chargers, test your backups by restoring one file</td></tr>
<tr><td>Yearly</td><td>Deep clean, check battery health, review passwords, decide whether upgrades are needed</td></tr>
</tbody>
</table>
<h3>Keep a simple log</h3>
<p>Write in a notebook or spreadsheet the date, what you did, and any problem you noticed. A log helps you spot patterns, such as "overheats every dry season", and helps a technician understand the history. In an office or school lab, label each device with a number and keep one line per device.</p>
<h3>Common mistakes</h3>
<ul>
<li>Waiting until the computer fails before doing anything.</li>
<li>Skipping backups "because nothing has gone wrong yet".</li>
<li>Carrying a laptop switched on in a closed bag, where it can overheat.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Copy the table into your own notebook or spreadsheet and adjust the tasks to your devices.</li>
<li>Set a repeating reminder on your phone for the weekly and monthly tasks.</li>
<li>Do this week's tasks now and write the date in your log.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Pack, carry and move devices carefully and shut them down first.</li>
<li>A regular schedule prevents most faults.</li>
<li>A log shows patterns and helps repairs.</li>
<li>Test your backups, not only make them.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Backups (3-2-1) and e-waste',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a backup plan that survives theft, power damage, viruses and mistakes, and you will know how to dispose of old electronics responsibly. Hardware can be replaced, but lost photos, school work and business records often cannot.</p>
<h3>Why backups matter</h3>
<p>Drives fail, phones are stolen, ransomware locks files, and people delete the wrong folder. A <strong>backup</strong> is an extra copy of your data stored somewhere else, so you can restore it if the original is lost.</p>
<h3>The 3-2-1 rule</h3>
<table>
<thead><tr><th>Number</th><th>Meaning</th><th>Example</th></tr></thead>
<tbody>
<tr><td>3</td><td>Keep three copies of important data</td><td>The original plus two backups</td></tr>
<tr><td>2</td><td>Use two different types of storage</td><td>Computer disk and an external drive</td></tr>
<tr><td>1</td><td>Keep one copy in a different place</td><td>Cloud storage, or a drive kept at a trusted relative's home</td></tr>
</tbody>
</table>
<h3>Options</h3>
<ul>
<li><strong>External hard drive or flash drive:</strong> cheap and quick. Keep it unplugged when not backing up so a virus or power surge cannot reach it.</li>
<li><strong>Cloud storage:</strong> files are kept online in services such as Google Drive, OneDrive or iCloud. Needs the internet and a free or paid plan.</li>
<li><strong>Phone backup:</strong> use your phone's settings to back up photos, contacts and chats to your account.</li>
</ul>
<h3>Making a backup plan</h3>
<ol>
<li><strong>List what matters:</strong> documents, photos, school work, business records, contacts.</li>
<li><strong>Choose where:</strong> one external drive plus one cloud account.</li>
<li><strong>Decide how often:</strong> daily for work files, weekly for the rest.</li>
<li><strong>Automate if you can:</strong> use the backup features built into your system or cloud apps.</li>
<li><strong>Test it:</strong> once in a while restore one file to confirm it opens.</li>
</ol>
<h3>E-waste</h3>
<p><strong>E-waste</strong> is discarded electronics such as old phones, batteries, chargers and computers. They contain materials that can poison soil and water if burned or dumped, and they contain valuable metals that can be recovered.</p>
<ul>
<li>Do not burn or bury electronics, and never throw batteries into ordinary rubbish.</li>
<li>Before giving away or selling a device, back up and then erase the data properly (reset it, remove accounts and SIM or memory cards).</li>
<li>Repair or reuse first. Donate working devices to schools or community groups if they are safe.</li>
<li>Take broken devices to a registered e-waste collector, recycling programme or shop that accepts them. Ask your local authority or a trusted organisation where these exist near you.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>List the ten most important files or folders you could not bear to lose.</li>
<li>Check how many copies you have now and where they are stored.</li>
<li>Make at least one more backup so that you meet the 3-2-1 rule, for example to an external drive and a cloud account.</li>
<li>Restore one file to test it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Follow 3-2-1: three copies, two types of storage, one off-site.</li>
<li>Automate and test your backups.</li>
<li>Wipe data before giving away or selling a device.</li>
<li>Take e-waste to responsible collectors; never burn it.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the correct way to clean a laptop screen?
- Spray glass cleaner directly on it
* Lightly dampen a soft cloth and wipe gently with the device off
- Rub with rough paper
- Use bleach
= Liquid should go on the cloth, never directly on the screen.

Q: Why is dust inside vents a problem?
- It makes the device heavier
* It blocks air flow and causes overheating
- It deletes files
- It changes the screen colour
= Dust stops heat from escaping.

Q: A drink spills on your laptop keyboard. What should you do first?
- Keep working to save your file
* Switch it off, unplug it and get it checked quickly
- Charge it
- Shake it while it is on
= Switching off at once reduces the risk of short circuits and corrosion.

Q: What does a surge protector do?
- Gives you hours of backup power
* Absorbs sudden spikes in voltage
- Speeds up the internet
- Charges phones faster
= A surge protector guards against spikes; a UPS provides battery power for cuts.

Q: Which of these follow the 3-2-1 backup rule? (choose all that apply)
* The original, plus an external drive, plus a cloud copy
* Keeping one copy outside the building
- Keeping two copies on the same drive
- Backing up once and never checking
= 3-2-1 means three copies, on two types of storage, with one stored elsewhere.

Q: Which is the best way to dispose of a broken phone battery?
- Burn it
* Take it to a responsible e-waste or battery collection point
- Bury it in the garden
- Throw it in a river
= Batteries can leak toxins or catch fire and should be handled by proper collectors.

Q: What should you do before giving away an old laptop?
- Nothing, the new owner can deal with it
* Back up your files, sign out of accounts and erase your data
- Remove the screen
- Fill the hard drive
= Wiping your data protects your privacy.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Troubleshooting method',
            'summary' => 'Follow a step-by-step method and fix common problems such as no power, slow performance, freezing and overheating.',
            'lessons' => [
                [
                    'title'   => 'A step-by-step troubleshooting method',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to approach any computer problem calmly with a clear method instead of guessing. Good technicians are not people who know every fix; they are people who work in order, test one thing at a time and write down what they find.</p>
<h3>The five steps</h3>
<table>
<thead><tr><th>Step</th><th>What you do</th></tr></thead>
<tbody>
<tr><td>1. Identify</td><td>Find out exactly what the problem is and when it started</td></tr>
<tr><td>2. Test</td><td>Think of likely causes, starting with the simplest, and test them one by one</td></tr>
<tr><td>3. Fix</td><td>Apply one change at a time</td></tr>
<tr><td>4. Verify</td><td>Check that the problem is gone and nothing else broke</td></tr>
<tr><td>5. Document</td><td>Write what the problem was and how you fixed it</td></tr>
</tbody>
</table>
<h3>Step 1: Identify</h3>
<ul>
<li>Ask: What exactly happens? What message appears (write it down or take a photo)?</li>
<li>When did it start? Did anything change just before (new program, update, spill, power cut, moved the device)?</li>
<li>Does it happen all the time or only sometimes? With one program or all?</li>
<li>Back up important data if the computer still works.</li>
</ul>
<h3>Step 2: Test simple things first</h3>
<ol>
<li>Is it plugged in and switched on? Is the socket working? Try another device in it.</li>
<li>Are cables firmly connected?</li>
<li>Restart the computer.</li>
<li>Does the problem happen with another user account, another program or another device?</li>
</ol>
<p>This "divide and conquer" approach helps you separate hardware, software and network problems.</p>
<h3>Step 3: Fix, one change at a time</h3>
<p>If you change five things at once, you will not know which one worked, and you may make the situation worse. Make one change, test, then continue only if needed. Do not delete files you do not understand.</p>
<h3>Step 4: Verify</h3>
<p>Use the device as normal for a while. Ask the owner to confirm that it is fixed for them. Check that other things still work.</p>
<h3>Step 5: Document</h3>
<p>Write the date, device, symptom, cause and the fix. Next time you will remember, and others can learn from your notes.</p>
<h3>Know when to stop</h3>
<p>Call a qualified technician or the maker when you see signs of electrical danger, burning smells, liquid damage, swollen batteries, or when a repair needs special tools or opens a power supply.</p>
<h3>Try it yourself</h3>
<ol>
<li>Imagine: "My laptop will not connect to the Wi-Fi." Write the five steps for this problem with at least two simple tests for step 2.</li>
<li>Create a one-page troubleshooting log template with these columns: date, device, symptom, cause, fix, result.</li>
<li>Ask a friend or family member about a computer problem they have had, and fill in a line in your log.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Use the five steps: identify, test, fix, verify, document.</li>
<li>Always test the simplest things first.</li>
<li>Change one thing at a time.</li>
<li>Stop and ask for help when safety is involved.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'No power, no display and beep codes',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn how to approach a computer that will not switch on or shows nothing on the screen. After it, you will be able to separate a power problem from a display problem and perform the safe checks that solve most of these cases.</p>
<h3>The computer shows no sign of life</h3>
<p>No lights, no fan noise. Work from the wall to the machine:</p>
<ol>
<li>Is the wall socket working? Plug in a phone charger or lamp to test it.</li>
<li>Is the extension or surge protector switched on? Some have a reset button or a blown fuse.</li>
<li>Check the power cable at both ends. Try a different cable if you can.</li>
<li>For a desktop, check the small switch on the back of the power supply is in the ON position. <em>Do not open the power supply.</em></li>
<li>For a laptop, check the charger light and the plug. Remove the charger, hold the power button for about 15 seconds, reconnect the charger and try again. If the laptop has a removable battery, try it without the battery and then with it.</li>
<li>Disconnect extra devices (USB drives, printers) and try again.</li>
</ol>
<p>If it still shows nothing, the power supply, charger, battery or motherboard may be faulty. A qualified technician can test them.</p>
<h3>It has power but no display</h3>
<p>Fans spin, lights glow, but the screen is black.</p>
<ul>
<li>Is the screen switched on, and is the brightness turned up? On a laptop, try the brightness keys.</li>
<li>For a desktop, check the video cable (HDMI, VGA) at both ends, and try another cable or monitor.</li>
<li>Is the monitor set to the right input source (HDMI or VGA)?</li>
<li>For a laptop, connect it to an external screen or TV. If that works, the laptop screen or its cable may be faulty.</li>
<li>Try pressing <strong>Windows key + P</strong> and choosing <strong>Duplicate</strong> if you have an external display.</li>
</ul>
<h3>Beep codes</h3>
<p>When a desktop starts, a small speaker on the motherboard may beep. Beeps are signals from the startup test. Their meanings differ between makers, so look up the pattern in your motherboard manual. In general terms:</p>
<table>
<thead><tr><th>Pattern (typical)</th><th>Often points to</th></tr></thead>
<tbody>
<tr><td>One short beep</td><td>Normal start-up on many machines</td></tr>
<tr><td>Repeating long beeps</td><td>Memory (RAM) problem on many machines</td></tr>
<tr><td>Continuous beeps</td><td>Power or memory problem</td></tr>
<tr><td>Beeps with no display</td><td>Graphics or memory problem</td></tr>
</tbody>
</table>
<p>Never assume. Check the manual of your exact machine. Some laptops use flashing lights instead of beeps.</p>
<h3>Reseating parts (desktop, if confident)</h3>
<p>With the power off, cable unplugged and yourself grounded, you can remove a RAM stick and push it firmly back until it clicks. Poor contact is a common cause of no-display faults. Do not touch the power supply.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write a flow chart on paper for "computer will not start" with yes/no questions in the order above.</li>
<li>Check which brightness keys work on your laptop.</li>
<li>Find the manual or web page for your own machine's beep or light codes.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Check power from the wall socket to the machine.</li>
<li>Separate "no power" from "no display" with simple tests.</li>
<li>Beep codes differ by maker; use the manual.</li>
<li>Do not open power supplies or chargers.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Slow, freezing and overheating computers',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to find and fix the most common causes of a slow, frozen or hot computer on Windows 10 and 11. These are the problems people bring to technicians most often, and many can be fixed in minutes without spending anything.</p>
<h3>Look at what is using the computer</h3>
<ol>
<li>Press <strong>Ctrl + Shift + Esc</strong> to open <strong>Task Manager</strong>. If you see a small view, select <strong>More details</strong>.</li>
<li>On the <strong>Processes</strong> tab look at the CPU, Memory and Disk columns. Click a column title to sort.</li>
<li>If a program that you do not need is using a lot, select it and choose <strong>End task</strong>. Save your work first.</li>
</ol>
<h3>Common causes and fixes</h3>
<table>
<thead><tr><th>Cause</th><th>Fix</th></tr></thead>
<tbody>
<tr><td>Too many programs at startup</td><td>In Task Manager, open <strong>Startup apps</strong> (Startup in Windows 10), right-click unneeded ones and choose <strong>Disable</strong></td></tr>
<tr><td>Too many browser tabs or extensions</td><td>Close tabs; remove extensions you do not use</td></tr>
<tr><td>Low free disk space</td><td>Keep at least 15 to 20 percent free; use <strong>Settings > System > Storage</strong> to clean temporary files</td></tr>
<tr><td>Not enough RAM</td><td>Close programs; consider more RAM</td></tr>
<tr><td>Malware</td><td>Run a full scan with Windows Security</td></tr>
<tr><td>Missing updates or old drivers</td><td>Run Windows Update</td></tr>
<tr><td>Dust and heat</td><td>Clean vents, improve ventilation</td></tr>
<tr><td>Old hard disk</td><td>Back up; consider an SSD</td></tr>
</tbody>
</table>
<h3>When the computer freezes</h3>
<ol>
<li>Wait a minute; sometimes it is just busy.</li>
<li>Try <strong>Ctrl + Shift + Esc</strong> to open Task Manager and end the program that is not responding.</li>
<li>If nothing works, hold the power button for about 5 to 10 seconds to force a shutdown. Do this only as a last resort because unsaved work is lost.</li>
<li>After restart, note what you were doing. A repeating freeze with the same program points to that program.</li>
</ol>
<h3>Overheating</h3>
<p>Signs: loud fans, hot case, sudden shutdowns, slowing down under load.</p>
<ul>
<li>Move the device to a hard surface; remove covers on vents.</li>
<li>Clean the vents and fans.</li>
<li>Close heavy programs.</li>
<li>In some desktops, old thermal paste or a failed fan is the cause; a technician can replace them.</li>
<li>Do not use a laptop on bedding while charging.</li>
</ul>
<h3>Restart or shut down?</h3>
<p>On Windows, <strong>Shut down</strong> may use a "fast startup" feature that keeps some system state. A full <strong>Restart</strong> clears memory and applies updates properly, and is the better first step for many odd problems. Restart at least weekly.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open Task Manager and note the three biggest users of memory.</li>
<li>Disable two startup apps that you do not need at start-up.</li>
<li>Open Settings > System > Storage and check free space.</li>
<li>Restart, then compare how long the computer takes to become usable.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Task Manager shows what slows the computer.</li>
<li>Disable unneeded startup apps and keep free disk space.</li>
<li>Force a shutdown only as a last resort.</li>
<li>Heat comes from dust, blocked vents or a failing fan.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Blue screens, safe mode, drivers and resetting Windows',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will understand what a blue screen is, how to start Windows in Safe Mode, how to update drivers, and the precautions to take before resetting or reinstalling Windows. Such steps are powerful, so take care and always protect your data first.</p>
<h3>The blue screen</h3>
<p>A <strong>blue screen</strong> (also called a stop error) appears when Windows meets a problem it cannot safely continue with. It shows a short message and a stop code, and then restarts. One blue screen is not a disaster. Repeated ones need investigation:</p>
<ol>
<li>Write down the stop code (or take a photo).</li>
<li>Think about what changed: new hardware, a new driver, an update, a new program.</li>
<li>Remove the recently added item and test again.</li>
<li>Run Windows Update and a malware scan.</li>
<li>Test RAM and disk with the tools in Windows or from the maker; failing parts cause stop errors.</li>
</ol>
<h3>Safe Mode</h3>
<p><strong>Safe Mode</strong> starts Windows with only the basic programs and drivers. It helps you check whether a problem comes from a driver or startup program.</p>
<ol>
<li>Open <strong>Settings > System > Recovery</strong> (in Windows 10: Update &amp; Security > Recovery).</li>
<li>Under Advanced startup, select <strong>Restart now</strong>.</li>
<li>Choose <strong>Troubleshoot > Advanced options > Startup Settings > Restart</strong>.</li>
<li>Press <strong>4</strong> or <strong>F4</strong> for Safe Mode (or 5 for Safe Mode with Networking).</li>
</ol>
<p>In Safe Mode you can uninstall a problem program or driver and scan for malware. Restart normally to leave it.</p>
<h3>Drivers</h3>
<p>A <strong>driver</strong> is a small program that lets Windows talk to a device such as a printer, graphics card or Wi-Fi adapter. Old or wrong drivers cause crashes. To update safely:</p>
<ul>
<li>Run <strong>Windows Update</strong> first; it provides many drivers.</li>
<li>Download drivers only from the maker of your computer or the part, never from unknown "driver update" sites.</li>
<li>In <strong>Device Manager</strong>, a yellow warning sign shows a device with a problem.</li>
<li>If a new driver causes trouble, use <strong>Roll Back Driver</strong> in the device properties.</li>
</ul>
<h3>Resetting or reinstalling Windows</h3>
<p>Resetting returns Windows to a fresh condition and can fix problems that nothing else does. Precautions:</p>
<ol>
<li><strong>Back up everything</strong> that matters first, to an external drive or cloud, and check that it opens.</li>
<li>Note the programs you need and where to get their installers and licence keys.</li>
<li>Plug in the charger so the laptop does not run out of power during the process.</li>
<li>In <strong>Settings > System > Recovery</strong>, choose <strong>Reset this PC</strong>. Select <strong>Keep my files</strong> if you want to keep documents, or remove everything only if you have a backup.</li>
<li>Be patient; it can take an hour or more. Do not switch it off.</li>
</ol>
<p>Use only genuine Windows installation media from the official source. Pirated copies often contain malware.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open Device Manager (right-click Start > Device Manager) and check for yellow warning signs.</li>
<li>Find out where Windows Update is and check for updates.</li>
<li>Find the Recovery page in Settings and read the options. <strong>Do not press reset.</strong></li>
<li>Write a checklist of what you would back up before a reset.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Note the stop code and recent changes when a blue screen appears.</li>
<li>Safe Mode helps isolate software and driver problems.</li>
<li>Take drivers only from official sources.</li>
<li>Always back up before resetting Windows.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the first step of the troubleshooting method?
- Reinstall Windows
* Identify exactly what the problem is and when it started
- Buy new parts
- Open the power supply
= Understanding the problem comes before any fix.

Q: Why should you change only one thing at a time?
- It is more fun
* So you know which change worked and avoid making things worse
- It uses less electricity
- Computers cannot handle two changes
= One change at a time makes the cause clear.

Q: A desktop shows no lights or fan noise. Which is the best first check?
- Replace the motherboard
* Test the wall socket, extension and power cable
- Reset Windows
- Open the power supply
= Check power from the wall inwards before assuming a part is faulty.

Q: Which Windows shortcut opens Task Manager directly?
- Ctrl + Alt + P
* Ctrl + Shift + Esc
- Alt + F4
- Windows + L
= Ctrl + Shift + Esc opens Task Manager.

Q: Which actions can help a slow computer? (choose all that apply)
* Disabling unneeded startup apps
* Freeing disk space
- Covering the vents
- Installing five different cleaning programs
= Reducing load and freeing space help; blocked vents and extra programs make things worse.

Q: What is the purpose of Safe Mode?
- To make the computer faster
* To start Windows with only basic drivers and programs so you can find or remove a problem
- To delete all files
- To connect to Wi-Fi faster
= Safe Mode isolates problems caused by drivers or startup programs.

Q: What must you do before using Reset this PC?
- Disconnect the screen
* Back up important files and check that the backup works
- Remove the battery
- Delete the backup
= A reset can remove files, so a tested backup is essential.

Q: Where should you download a driver for your laptop?
- Any site promising a free driver scan
* The laptop maker's official support site or Windows Update
- A link sent by an unknown person
- A game forum
= Unofficial driver sites often bundle malware.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Software, security and mobile devices',
            'summary' => 'Keep software updated, remove malware, fix browser and printer problems, and troubleshoot phones.',
            'lessons' => [
                [
                    'title'   => 'Updates, antivirus and malware removal',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to keep a computer updated, recognise signs of malware and follow a safe process to clean an infected computer. Many computer problems that look like hardware faults are actually caused by old software or by harmful programs.</p>
<h3>Why updates matter</h3>
<p>Updates fix bugs, add features and, most importantly, close security holes that criminals use. Delaying updates for months leaves your device exposed.</p>
<ol>
<li>On Windows, open <strong>Settings > Windows Update</strong> and select <strong>Check for updates</strong>. Install them and restart when asked.</li>
<li>Turn on automatic updates for your browser, office programs and phone apps where possible.</li>
<li>If the device has no longer-supported system, plan to replace or upgrade it.</li>
<li>If data is expensive, do big updates on Wi-Fi or a cheaper time.</li>
</ol>
<h3>Antivirus</h3>
<p><strong>Malware</strong> is harmful software: viruses, ransomware (locks files and demands money), spyware (watches you), adware (pop-ups) and trojans (pretend to be useful). Windows comes with <strong>Windows Security</strong>, a good basic protection. Keep it on and updated. You rarely need to buy more than one antivirus; two active at once can conflict.</p>
<h3>Signs of malware</h3>
<ul>
<li>Sudden slowness and unexpected pop-ups.</li>
<li>Browser homepage or search engine changed on its own.</li>
<li>Programs you did not install.</li>
<li>Files that will not open, or messages demanding payment.</li>
<li>Friends receiving strange messages from you.</li>
</ul>
<h3>Malware removal steps</h3>
<ol>
<li><strong>Disconnect</strong> from Wi-Fi and unplug network cables to stop it spreading.</li>
<li><strong>Do not pay</strong> any ransom or call numbers shown in pop-ups.</li>
<li>Start in <strong>Safe Mode with Networking</strong> if the computer is hard to use.</li>
<li>Open <strong>Windows Security > Virus &amp; threat protection > Scan options</strong> and choose <strong>Full scan</strong> (or <strong>Microsoft Defender Offline scan</strong> for stubborn cases). Remove what it finds.</li>
<li>Uninstall unknown programs in <strong>Settings > Apps</strong>.</li>
<li>Remove unknown browser extensions and reset the browser settings.</li>
<li>Run Windows Update and update your browser.</li>
<li>Change your important passwords from a clean device, and switch on two-step verification.</li>
<li>If it keeps returning or files are encrypted, restore from a backup or ask a technician.</li>
</ol>
<h3>Prevention</h3>
<ul>
<li>Install software only from official sources.</li>
<li>Avoid cracked or pirated programs.</li>
<li>Do not open attachments or links from unexpected messages.</li>
<li>Scan flash drives from other people before opening.</li>
<li>Back up regularly. It is your best defence against ransomware.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Check that Windows Update is up to date and note the date of the last update.</li>
<li>Open Windows Security and confirm that real-time protection is switched on.</li>
<li>Run a Quick scan and write down the result.</li>
<li>Write your own five-step "what to do if I think my computer has a virus" card.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Updates close security holes; install them regularly.</li>
<li>Use one good antivirus, such as Windows Security, and keep it on.</li>
<li>Disconnect, scan, remove, update and change passwords if infected.</li>
<li>Backups are the best defence against ransomware.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Uninstalling problem software and fixing browser problems',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn to remove software that causes trouble and to fix common browser problems such as slowness, pop-ups and pages that will not load. Browsers are used constantly, so a few simple steps can improve your daily experience a lot.</p>
<h3>Uninstalling a program properly</h3>
<ol>
<li>Open <strong>Settings > Apps > Installed apps</strong> (Windows 10: <strong>Apps &amp; features</strong>).</li>
<li>Find the program, select the three dots or the program name, and choose <strong>Uninstall</strong>.</li>
<li>Follow the prompts, then restart if asked.</li>
<li>Do not just delete the program's folder; uninstalling removes it correctly.</li>
</ol>
<p>Remove programs you do not use or do not remember installing, especially toolbars, "PC cleaners" and free games bundled with other downloads. Be careful: do not remove items made by Microsoft or your computer's maker unless you are sure.</p>
<h3>When a program will not open or keeps crashing</h3>
<ul>
<li>Restart the computer and try again.</li>
<li>Check for updates for that program.</li>
<li>Right-click the program, choose <strong>Run as administrator</strong> if it needs it.</li>
<li>Repair it: in <strong>Installed apps</strong>, choose the program and look for <strong>Advanced options</strong> > <strong>Repair</strong> (available for some apps).</li>
<li>Uninstall and install again from the official source.</li>
</ul>
<h3>Browser problems</h3>
<table>
<thead><tr><th>Problem</th><th>What to try</th></tr></thead>
<tbody>
<tr><td>Browser is slow</td><td>Close tabs, remove extensions you do not use, update the browser, restart it</td></tr>
<tr><td>Page loads wrongly or shows old content</td><td>Press <strong>Ctrl + F5</strong> to reload; clear cache and cookies in browser settings</td></tr>
<tr><td>Pop-ups or strange homepage</td><td>Remove unknown extensions; reset browser settings; run a malware scan</td></tr>
<tr><td>A website will not open for everyone</td><td>Test on another device or website; the site itself may be down</td></tr>
<tr><td>Login problems</td><td>Check typing and Caps Lock; try a private window to see if cookies are the cause</td></tr>
<tr><td>Storage warnings</td><td>Clear downloads and browsing data you do not need</td></tr>
</tbody>
</table>
<h3>Clearing cache</h3>
<p>The <strong>cache</strong> stores copies of web pages so that they load faster. Old cache can sometimes cause problems. In most browsers press <strong>Ctrl + Shift + Delete</strong>, choose a time range, select cached images and files, and clear them. Note that clearing cookies may sign you out of websites.</p>
<h3>Safer extensions</h3>
<p>Browser extensions can read what you do in the browser. Install only a few from the official store, read reviews, and remove any you do not use.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open Installed apps and list three programs you no longer use. Uninstall one that is safe to remove.</li>
<li>Open your browser's extensions page and remove any you do not recognise.</li>
<li>Clear cached images and files and notice how long it takes to reload a page.</li>
<li>Check that your browser is up to date.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Uninstall programs through Settings, not by deleting folders.</li>
<li>Updating, repairing and reinstalling fix many program faults.</li>
<li>Many browser issues are solved by removing extensions or clearing cache.</li>
<li>Only keep extensions you trust and use.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Printer problems',
                    'minutes' => 11,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to solve the most common printer problems: nothing prints, paper jams, poor print quality and connection failures. Printers are used in offices, schools and shops, and they stop working at the worst moment, so a clear routine helps a lot.</p>
<h3>Nothing prints</h3>
<ol>
<li><strong>Check the basics:</strong> is the printer on, with paper, ink or toner, and no error lights?</li>
<li><strong>Check the connection:</strong> is the USB cable firm, or is it on the same Wi-Fi as the computer?</li>
<li><strong>Check the print queue:</strong> open <strong>Settings > Bluetooth &amp; devices > Printers &amp; scanners</strong>, select the printer, and open <strong>Print queue</strong>. If jobs are stuck, cancel them and try again.</li>
<li><strong>Select the right printer:</strong> make sure the chosen printer in the print window is the correct one and is not "Offline".</li>
<li><strong>Restart</strong> the printer and the computer.</li>
<li><strong>Update or reinstall the driver</strong> from the printer maker's official website. Remove the printer in Settings first, then add it again.</li>
</ol>
<h3>Paper jams</h3>
<ul>
<li>Switch off and unplug the printer.</li>
<li>Open the covers and gently pull the paper in the direction of the paper path. Do not pull hard or in the opposite direction, which can tear it and leave pieces inside.</li>
<li>Check for small torn bits and remove them.</li>
<li>Use paper of the right weight, fan the stack, and do not overfill the tray. Damp paper jams more often, so store it in a dry place.</li>
<li>Do not touch hot areas inside laser printers; wait for them to cool.</li>
</ul>
<h3>Poor print quality</h3>
<table>
<thead><tr><th>Problem</th><th>Common cause</th></tr></thead>
<tbody>
<tr><td>Streaks or missing colours</td><td>Clogged nozzles on inkjet; run the <strong>cleaning</strong> or head-alignment option from the printer's maintenance menu</td></tr>
<tr><td>Faded print</td><td>Low ink or toner</td></tr>
<tr><td>Black marks or smudges</td><td>Dirty rollers or leaking toner cartridge</td></tr>
<tr><td>Crooked print</td><td>Paper not loaded straight</td></tr>
</tbody>
</table>
<h3>Ink and toner</h3>
<p>Use cartridges recommended for your model. Very cheap refills or poor-quality toner can damage a printer. Handle toner carefully; the powder is messy and should not be inhaled. Dispose of empty cartridges responsibly, because some suppliers collect them.</p>
<h3>Wireless printers</h3>
<p>If a Wi-Fi printer stops printing after you changed the router or password, reconnect the printer to the Wi-Fi from its control panel and add it again on the computer.</p>
<h3>Try it yourself</h3>
<ol>
<li>Open the printers list on your computer and look at the status of each printer.</li>
<li>If you have a printer, print a test page from the printer's properties.</li>
<li>Write a one-page printer troubleshooting card for your office or home with the six steps above.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Check power, paper, ink and connection first.</li>
<li>Clear the print queue and reselect the correct printer.</li>
<li>Remove jammed paper gently in the direction of the paper path.</li>
<li>Use proper cartridges and drivers from official sources.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Phone troubleshooting, data and SIM basics',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to fix common problems on Android phones and iPhones: full storage, poor battery life, misbehaving apps and SIM issues. You will also know the precautions before a factory reset. Phones are the main computers for many people, so these skills are very practical.</p>
<h3>First, restart</h3>
<p>Restarting solves many glitches. Hold the power button and choose <strong>Restart</strong>. If the phone is frozen, hold the power button (and on some models a volume button) until it restarts. The exact combination depends on the model; check the maker's guide.</p>
<h3>Storage is full</h3>
<ol>
<li>Open <strong>Settings > Storage</strong> to see what takes space.</li>
<li>Back up photos and videos to the cloud or a computer, then delete them from the phone.</li>
<li>Delete large downloads and unused apps. Clear the cache of heavy apps in <strong>Settings > Apps</strong> (Android).</li>
<li>In messaging apps, check media files that fill space, and clear old ones.</li>
</ol>
<h3>Battery drains fast</h3>
<ul>
<li>Check <strong>Settings > Battery</strong> to see which apps use the most.</li>
<li>Lower screen brightness and shorten screen timeout.</li>
<li>Turn off Bluetooth, GPS and Wi-Fi when you do not need them.</li>
<li>Uninstall apps you do not use or that run constantly in the background.</li>
<li>Keep software updated; avoid extreme heat. If the battery is old and swollen, have it replaced by a trusted technician.</li>
</ul>
<h3>App problems</h3>
<ol>
<li>Close and reopen the app.</li>
<li>Update it from the official store (Google Play or the App Store).</li>
<li>Clear the app's cache (Android: Settings > Apps > the app > Storage > Clear cache).</li>
<li>Uninstall and reinstall. Make sure you know how to recover its data first.</li>
</ol>
<h3>SIM and network basics</h3>
<ul>
<li>A <strong>SIM card</strong> identifies your phone number and network account. Never share your SIM PIN or PUK code.</li>
<li>If there is "No service", restart, remove and reinsert the SIM with the phone off, and check for a damaged card.</li>
<li>If you lose your phone, report it to your network to block the SIM, and ask them about SIM replacement. Many networks need your ID to do this. Also use your phone's "find my device" feature.</li>
<li>Be careful of SIM swap fraud: if your phone suddenly loses service unexpectedly, contact your network at once and check your mobile money account.</li>
<li>Keep your mobile money PIN private and never reveal it, even to people who say they are from the company.</li>
</ul>
<h3>Factory reset precautions</h3>
<p>A factory reset erases the phone and returns it to its original state. Use it as a last resort or before selling or giving away the device.</p>
<ol>
<li>Back up photos, contacts and chats, and check the backup.</li>
<li>Remember your Google or Apple account password. Without it, the phone may be locked after reset.</li>
<li>Sign out of your accounts and remove the SIM and memory card.</li>
<li>Charge the phone above 50 percent.</li>
<li>Go to Settings, choose the reset option (usually in System or General), and follow the prompts.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Open storage settings and list your three biggest users of space.</li>
<li>Free at least 1 GB by backing up and removing items you do not need.</li>
<li>Check your battery screen and close or remove one app that uses too much.</li>
<li>Check that your phone has a backup and that you remember your account password.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Restart first; check storage, battery use and app updates next.</li>
<li>Protect your SIM and mobile money PIN.</li>
<li>Back up and know your account password before any factory reset.</li>
<li>Report a lost phone to your network and block the SIM.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Why are software updates important?
- They make the screen bigger
* They fix bugs and close security holes
- They delete your files
- They only change colours
= Updates protect you against attacks that use known weaknesses.

Q: You suspect your computer has malware. What should you do early?
- Pay the ransom shown in a pop-up
* Disconnect from the internet and run a full scan with trusted security software
- Share the file with friends
- Install five different free cleaners
= Disconnecting limits damage and scanning finds the problem; never pay a ransom.

Q: Which is the best defence against ransomware?
- A bigger screen
* Regular, tested backups kept separate from the computer
- Switching off the monitor
- Using a longer password only
= If files are locked, a clean backup lets you restore them.

Q: What is the correct way to remove a program from Windows?
- Delete its icon
* Use Settings > Apps > Installed apps and choose Uninstall
- Format the disk
- Rename its folder
= Uninstalling removes the program properly.

Q: A website shows old content that looks wrong. What can help? (choose all that apply)
* Reload with Ctrl + F5
* Clear cached images and files
- Reinstall Windows
- Buy a new router
= A hard reload or cache clearing refreshes the page without drastic steps.

Q: A printer says a job is waiting but nothing prints. What should you check?
- Whether the monitor is hot
* The print queue, the selected printer and the printer's connection
- The keyboard layout
- The mouse battery
= Stuck jobs and wrong or offline printer selection are common causes.

Q: What should you do first when a phone is full of photos and cannot take more?
- Throw the phone away
* Back up the photos to the cloud or a computer, then delete them from the phone
- Delete the operating system
- Remove the battery
= Backing up first prevents permanent loss.

Q: Someone calls saying they are from the mobile money company and asks for your PIN. You should:
- Give it to help them
* Refuse, because genuine staff never need your PIN, and report the call
- Send it by SMS
- Write it on your phone case
= Your PIN is secret; callers asking for it are almost always fraudsters.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Networking basics',
            'summary' => 'Understand networks, set up and secure Wi-Fi, fix connection problems, share resources and practise full troubleshooting scenarios.',
            'lessons' => [
                [
                    'title'   => 'What is a network? LAN, WAN and Wi-Fi',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what a network is, name the types you meet at home and in an office, and describe how your phone or laptop reaches the internet. Understanding this picture makes every network problem easier to solve.</p>
<h3>What is a network?</h3>
<p>A <strong>network</strong> is two or more devices connected so they can share information and resources such as files, printers and an internet connection. The connection can use cables or radio waves.</p>
<h3>Types of network</h3>
<table>
<thead><tr><th>Type</th><th>Meaning</th><th>Example</th></tr></thead>
<tbody>
<tr><td>LAN (Local Area Network)</td><td>Devices in one small area, such as a home, school lab or office</td><td>Your laptop, phone and printer on the same router</td></tr>
<tr><td>WAN (Wide Area Network)</td><td>Networks joined over long distances</td><td>The internet; a company linking offices in different towns</td></tr>
<tr><td>WLAN / Wi-Fi</td><td>A LAN that uses radio instead of cables</td><td>Your home wireless network</td></tr>
<tr><td>Hotspot</td><td>A phone or device sharing its mobile data</td><td>Using your phone's data on your laptop</td></tr>
</tbody>
</table>
<h3>Wired and wireless</h3>
<ul>
<li><strong>Wired (Ethernet cable):</strong> usually faster, steadier and more secure. Good for desktops, printers and servers.</li>
<li><strong>Wireless (Wi-Fi):</strong> convenient for phones and laptops, but can be slowed by walls, distance and other networks.</li>
</ul>
<h3>How you reach the internet</h3>
<ol>
<li>Your device connects to the router by Wi-Fi or cable.</li>
<li>The router passes your requests to the <strong>internet service provider (ISP)</strong>, through a fibre, radio or mobile network link.</li>
<li>The ISP connects you to the wider internet and to websites.</li>
<li>The answer comes back along the same path to your device.</li>
</ol>
<p>On a mobile network, your phone talks to the network operator's mast using 3G, 4G or 5G instead of a router.</p>
<h3>Servers and clients</h3>
<p>A <strong>server</strong> is a computer that provides a service, such as a website or shared files. A <strong>client</strong> is a device that uses it. When you open a web page, your phone is the client and the website's computer is the server.</p>
<h3>Speed words</h3>
<p><strong>Bandwidth</strong> is how much data can travel per second, usually measured in Mbps (megabits per second). <strong>Latency</strong> is the delay before data starts to arrive. A video call needs good bandwidth and low delay.</p>
<h3>Try it yourself</h3>
<ol>
<li>List all the devices at your home or workplace that connect to a network.</li>
<li>Mark each as wired or wireless.</li>
<li>Draw a simple diagram: devices, router, ISP, internet.</li>
<li>Run a speed test using a trusted speed test website or app and record the download speed.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A network connects devices to share data and resources.</li>
<li>A LAN is local; a WAN covers long distances; the internet is the biggest WAN.</li>
<li>Cables are usually steadier; Wi-Fi is more convenient.</li>
<li>Bandwidth is capacity; latency is delay.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Network devices, IP addresses and DNS',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>In this lesson you will learn what routers, modems, switches and access points do, and what IP addresses and DNS mean in plain words. These words appear in every network setup guide and error message, so knowing them gives you confidence.</p>
<h3>Network devices</h3>
<table>
<thead><tr><th>Device</th><th>Job</th></tr></thead>
<tbody>
<tr><td>Modem</td><td>Translates the signal from your provider (fibre, cable, mobile) so your network can use it</td></tr>
<tr><td>Router</td><td>Connects your local network to the internet and directs data between devices; often gives each device an address</td></tr>
<tr><td>Switch</td><td>Connects several wired devices in one LAN, sending data only to the right port</td></tr>
<tr><td>Access point (AP)</td><td>Provides Wi-Fi for devices; extends Wi-Fi coverage when connected by cable</td></tr>
<tr><td>Wi-Fi extender</td><td>Repeats an existing Wi-Fi signal (often slower than a cabled access point)</td></tr>
<tr><td>Network cable</td><td>Usually Ethernet (Cat5e or Cat6) with RJ-45 plugs</td></tr>
</tbody>
</table>
<p>At home, one box often combines modem, router, switch and access point. Many mobile Wi-Fi devices (MiFi) combine a SIM, modem, router and access point in a small portable unit.</p>
<h3>IP addresses</h3>
<p>An <strong>IP address</strong> is a number that identifies a device on a network, like a house number on a street. An example is <code>192.168.1.25</code>. There are two kinds you meet:</p>
<ul>
<li><strong>Private (local) IP address:</strong> used inside your home or office, often starting with 192.168 or 10. Your router gives these out automatically using a service called <strong>DHCP</strong>.</li>
<li><strong>Public IP address:</strong> the address your network shows to the internet.</li>
</ul>
<p>To see your computer's address on Windows, open <strong>Command Prompt</strong> and type:</p>
<pre>ipconfig</pre>
<p>Look for <strong>IPv4 Address</strong> and <strong>Default Gateway</strong>. The default gateway is usually your router.</p>
<h3>DNS: the phone book of the internet</h3>
<p>People remember names like a website address; computers need numbers. <strong>DNS (Domain Name System)</strong> translates names into IP addresses. When you type a web address, your device asks a DNS server for the number, then connects. If DNS fails, you may see "server not found" even though the internet is connected. Restarting the router or changing the DNS server in advanced settings can help; for beginners, ask your ISP or a technician before changing it.</p>
<h3>Other words</h3>
<ul>
<li><strong>SSID:</strong> the name of a Wi-Fi network.</li>
<li><strong>MAC address:</strong> a built-in hardware ID of a network card.</li>
<li><strong>Firewall:</strong> a filter that blocks unwanted traffic.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Look at your home or office router and note its lights, ports and label.</li>
<li>Open Command Prompt and run <code>ipconfig</code>. Write down your IPv4 address and default gateway.</li>
<li>Check whether your address is private (starts with 192.168 or 10).</li>
<li>Draw your own network showing modem, router and devices.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The modem brings the connection; the router shares it; a switch adds wired ports; an access point provides Wi-Fi.</li>
<li>An IP address identifies a device on a network.</li>
<li>DHCP gives addresses automatically.</li>
<li>DNS turns names into IP addresses.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Setting up a secure home or small office Wi-Fi',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to set up a wireless router and protect it so that neighbours and criminals cannot misuse your internet or spy on your devices. An unprotected Wi-Fi network can be slow, expensive and dangerous.</p>
<h3>Placing the router</h3>
<ul>
<li>Put it in a central, high, open place, not inside a cupboard or behind metal.</li>
<li>Keep it away from microwaves, cordless phones and thick walls.</li>
<li>Make sure it has airflow and is protected from rain and dust.</li>
<li>Connect it to a surge protector or UPS where power is unstable.</li>
</ul>
<h3>Basic setup steps</h3>
<ol>
<li>Connect the router to your modem or provider's line with the cable that came with it, and plug in the power.</li>
<li>Find the router's setup details on its label: its address (often like 192.168.0.1 or 192.168.1.1), default username and password and default Wi-Fi name.</li>
<li>Connect a laptop to the router by cable or to its default Wi-Fi, open a browser and type the router's address.</li>
<li>Log in with the default details on the label.</li>
<li><strong>Immediately change the admin password</strong> to a strong, unique one. Default passwords are well known to attackers.</li>
<li>Follow the setup wizard to enter the information from your provider if needed.</li>
</ol>
<h3>Secure the Wi-Fi</h3>
<table>
<thead><tr><th>Setting</th><th>Recommended</th></tr></thead>
<tbody>
<tr><td>Network name (SSID)</td><td>Change it, but do not include your name, address or phone number</td></tr>
<tr><td>Security type</td><td>WPA3 if all devices support it; otherwise WPA2 (AES). Avoid WEP and open networks</td></tr>
<tr><td>Wi-Fi password</td><td>At least 12 characters, a mix of words and numbers that you can remember but others cannot guess</td></tr>
<tr><td>Admin password</td><td>Different from the Wi-Fi password</td></tr>
<tr><td>Remote administration</td><td>Turn off unless you need it</td></tr>
<tr><td>WPS</td><td>Turn off if possible; it can be a weak point</td></tr>
<tr><td>Firmware</td><td>Check for router updates from the maker</td></tr>
<tr><td>Guest network</td><td>Use it for visitors so they do not access your main devices</td></tr>
</tbody>
</table>
<h3>Connecting devices</h3>
<ol>
<li>On Windows, select the Wi-Fi icon in the taskbar, choose your network, select <strong>Connect</strong> and enter the password.</li>
<li>On Android or iPhone, open <strong>Settings > Wi-Fi</strong>, tap your network and enter the password.</li>
<li>For wired devices, plug an Ethernet cable from the device to a router or switch port.</li>
</ol>
<h3>Sharing a connection responsibly</h3>
<p>In an office or business, keep staff and customer networks separate. Do not give the main password to everyone; change it if someone leaves or you suspect misuse. Check connected devices in the router page from time to time and remove those you do not know.</p>
<h3>Try it yourself</h3>
<ol>
<li>Find your router's label and note its address (do not share the password with others).</li>
<li>Log in and check which security type your Wi-Fi uses.</li>
<li>If you are allowed to, change the Wi-Fi name and password, and write the new password in a safe place.</li>
<li>Look at the list of connected devices and check that you know them all.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Put the router in a central, open, protected place.</li>
<li>Change the default admin and Wi-Fi passwords immediately.</li>
<li>Use WPA2 or WPA3, never an open or WEP network.</li>
<li>Use a guest network and check connected devices regularly.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Troubleshooting connectivity',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to find out why a device has no internet and fix the usual causes. Connection problems are among the most common support requests, and you can solve most of them by checking from the device towards the internet, one step at a time.</p>
<h3>Start with the question: just me, or everyone?</h3>
<ul>
<li>Do other devices connect to the same Wi-Fi and reach the internet?</li>
<li>If only one device fails, the problem is likely with that device.</li>
<li>If all devices fail, the problem is likely the router, the line or the provider.</li>
</ul>
<h3>On the device</h3>
<ol>
<li>Is Airplane mode off and Wi-Fi switched on? Some laptops have a function key for Wi-Fi.</li>
<li>Is the device connected to the correct network?</li>
<li>Forget and rejoin: on Windows go to <strong>Settings > Network &amp; internet > Wi-Fi > Manage known networks</strong>, select the network and choose <strong>Forget</strong>. Then connect again and enter the password.</li>
<li>Restart the device.</li>
<li>Run the Windows network troubleshooter: <strong>Settings > System > Troubleshoot > Other troubleshooters > Internet Connections</strong>.</li>
</ol>
<h3>Check cables and the router</h3>
<ol>
<li>Check that the Ethernet cable is firm at both ends and the lights next to the port are on or blinking.</li>
<li>Look at the router's lights. Read its manual for what the internet or WAN light should show. A red or off internet light often means a line or provider problem.</li>
<li>Restart: unplug the router (and modem) for 30 seconds, plug in the modem first, wait a couple of minutes, then the router.</li>
<li>Move closer to the router to test whether the signal is weak.</li>
</ol>
<h3>Ping: a simple test</h3>
<p><strong>Ping</strong> sends a small message to another computer and measures the reply. In Command Prompt, try these in order:</p>
<pre>ping 127.0.0.1
ping 192.168.1.1
ping 8.8.8.8
ping google.com</pre>
<p>Replace the second address with your router's address (the default gateway shown by <code>ipconfig</code>).</p>
<table>
<thead><tr><th>Result</th><th>What it suggests</th></tr></thead>
<tbody>
<tr><td>First ping fails</td><td>Problem with the computer's own network software</td></tr>
<tr><td>Router ping fails</td><td>Problem between your device and the router (Wi-Fi, cable)</td></tr>
<tr><td>Router works, 8.8.8.8 fails</td><td>Router has no internet; line or provider problem</td></tr>
<tr><td>8.8.8.8 works, name fails</td><td>DNS problem</td></tr>
<tr><td>All succeed</td><td>Network is fine; check the specific website or app</td></tr>
</tbody>
</table>
<h3>Other causes</h3>
<ul>
<li>Data bundle finished or the account is unpaid.</li>
<li>Too many devices, or someone is using lots of bandwidth with downloads or streaming.</li>
<li>Interference or a weak signal far from the router.</li>
<li>Wrong date and time on the device, which can break secure websites.</li>
<li>Provider outage. Contact your provider with the information you gathered.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Run the four ping tests above and write the results.</li>
<li>Forget your Wi-Fi network and rejoin it.</li>
<li>Write the steps you would follow if your phone has Wi-Fi but no internet.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>First decide if the problem affects one device or all.</li>
<li>Check device settings, cables, router lights and then the provider.</li>
<li>Restart the modem and router in the right order.</li>
<li>Ping tests show where the connection breaks.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Sharing, hotspots, public Wi-Fi and final scenarios',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>In this last lesson you will learn to share printers and files on a network, use a mobile hotspot and mobile data wisely, stay safe on public Wi-Fi, and practise with a set of troubleshooting scenarios that bring the whole course together.</p>
<h3>Sharing a printer</h3>
<ol>
<li>Connect the printer to the network (Wi-Fi or Ethernet), or to one computer by USB.</li>
<li>On Windows, open <strong>Settings > Bluetooth &amp; devices > Printers &amp; scanners</strong> and choose <strong>Add device</strong>. Select the printer.</li>
<li>If the printer is connected to one computer, open its <strong>Printer properties > Sharing</strong> and enable sharing, then add it from other computers.</li>
</ol>
<h3>Sharing files</h3>
<p>The easiest and safest way for beginners is cloud storage such as Google Drive or OneDrive, where you share a link or invite specific people. Local sharing on a network is possible: right-click a folder, choose <strong>Properties > Sharing</strong>, and share it with chosen users. Share only what is needed, give read-only access when possible, and stop sharing when finished.</p>
<h3>Mobile hotspot and data tips</h3>
<ul>
<li>On a phone, open <strong>Settings</strong>, find <strong>Hotspot</strong> or <strong>Tethering</strong>, switch it on, set a strong password and connect your laptop.</li>
<li>Hotspot uses your data and battery quickly; keep the phone plugged in if possible.</li>
<li>Turn off automatic updates and cloud backup over mobile data; set apps to update on Wi-Fi only.</li>
<li>Use data-saver mode in apps and browsers, and lower video quality.</li>
<li>Check usage in <strong>Settings > Network &amp; internet</strong> and set a data warning.</li>
</ul>
<h3>Public Wi-Fi safety</h3>
<ul>
<li>Treat public Wi-Fi in cafes, hotels and bus parks as untrusted.</li>
<li>Check the exact network name with staff; criminals make fake ones with similar names.</li>
<li>Use only secure websites (a padlock and https) and avoid banking or mobile money on public Wi-Fi if you can; use mobile data instead.</li>
<li>Turn off file sharing and automatic connection to open networks.</li>
<li>Log out when finished, and consider a trusted VPN service.</li>
</ul>
<h3>Final troubleshooting scenarios</h3>
<table>
<thead><tr><th>Scenario</th><th>Think about</th></tr></thead>
<tbody>
<tr><td>1. A laptop is slow after a long time; the fan is loud and the case is hot.</td><td>Dust and vents, Task Manager, startup apps, free space, malware scan</td></tr>
<tr><td>2. The office printer prints for everyone except one user.</td><td>Selected printer, queue, driver and connection on that computer</td></tr>
<tr><td>3. All phones show Wi-Fi connected but no internet.</td><td>Router lights, restart, provider or data bundle, ping tests</td></tr>
<tr><td>4. A desktop shows nothing on screen but the fan spins.</td><td>Monitor, cable, input source, reseat RAM when safe</td></tr>
<tr><td>5. A phone says storage full and will not update apps.</td><td>Storage use, backups, delete large files, clear cache</td></tr>
<tr><td>6. A student's flash drive causes pop-ups on the school lab computer.</td><td>Disconnect, scan, remove malware, scan drives before use</td></tr>
</tbody>
</table>
<h3>Try it yourself</h3>
<ol>
<li>Choose two scenarios above and write the five-step method (identify, test, fix, verify, document) for each.</li>
<li>Check the Wi-Fi security settings on your phone and turn off auto-join for open networks.</li>
<li>Set a data warning on your phone or computer.</li>
<li>Explain one scenario aloud to a friend as if you were the technician.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Share printers and files only with the people who need them.</li>
<li>Hotspots use data and battery quickly; control updates and usage.</li>
<li>Treat public Wi-Fi as untrusted and avoid sensitive tasks on it.</li>
<li>Apply the same five steps to every problem: identify, test, fix, verify, document.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is a LAN?
- The whole internet
* A network of devices in a small area such as a home or office
- A type of cable only
- A phone number
= A Local Area Network connects nearby devices.

Q: What does a router do?
- Stores your photos
* Connects your local network to the internet and directs data between devices
- Cleans the screen
- Prints documents
= The router is the gateway between your devices and the internet.

Q: What does DNS do?
- Charges your account
* Turns website names into IP addresses
- Cools the router
- Blocks all pop-ups
= DNS works like a phone book for the internet.

Q: Which Wi-Fi security choice is best?
- Open network with no password
* WPA2 or WPA3 with a strong password
- WEP with the default password
- Your phone number as password
= Modern encryption and a strong password protect your network.

Q: What is the first thing to change after setting up a new router? (choose the best)
- Nothing
* The default admin password
- The router colour
- The brand name
= Default admin passwords are well known and easily exploited.

Q: Your laptop can ping the router but not 8.8.8.8. What does this suggest?
- The keyboard is broken
* The router has no working internet link; check the line or provider
- The laptop's RAM is faulty
- The printer is offline
= Local connection works, but traffic is not reaching the internet.

Q: Which are good habits on public Wi-Fi? (choose all that apply)
* Check the correct network name with staff
* Avoid banking or mobile money transactions if you can use mobile data
- Share your files with everyone
- Auto-join every open network
= Public Wi-Fi is untrusted, so limit sensitive activity.

Q: To restart a modem and router properly you should:
- Hit them
* Unplug both, wait about 30 seconds, then power the modem first and the router after
- Open them and clean the circuit board
- Reset the PC
= A correct power cycle lets the modem connect before the router asks for an address.
QUIZ,
        ],
    ],

    'final_exam' => <<<'QUIZ'
Q: Which is the safest action before cleaning inside a computer?
- Leave it plugged in
* Switch it off and unplug it
- Use a wet cloth on the motherboard
- Open the power supply
= Always remove power first and never open the power supply.

Q: What is static electricity most likely to damage?
- The desk
* Sensitive components such as RAM and processors
- The mouse cable
- The monitor stand
= Static discharge can silently damage electronic parts.

Q: What is the role of the CPU?
- Permanent storage
* Carrying out instructions and calculations
- Cooling the machine
- Providing Wi-Fi
= The CPU is the processor, the computer's brain.

Q: Which statement about RAM and storage is correct?
- RAM keeps files after power off
* RAM is temporary workspace, storage keeps files permanently
- They are the same part
- Storage is only used for games
= RAM is cleared when power goes off; storage keeps data.

Q: Why is an SSD often a good upgrade for an old computer?
- It makes the screen brighter
* It loads the system and programs much faster than an HDD
- It increases RAM
- It adds Wi-Fi
= SSDs have no moving parts and are much faster.

Q: Which should you check on a spec sheet before buying a computer for office work? (choose all that apply)
* Amount of RAM
* Type and size of storage
- The colour of the box only
- The brand of the mouse mat
= RAM and storage strongly affect everyday performance.

Q: Why must you check compatibility before buying RAM?
- Because the shop will not sell it otherwise
* Because RAM types differ (for example DDR3 and DDR4) and the wrong one will not fit or work
- Because all RAM is the same
- Because RAM is free
= Always match the RAM type and the limits of your model.

Q: How should you clean a screen?
- Spray cleaner directly onto it
* Lightly dampen a soft cloth and wipe gently with the device off
- Use sandpaper
- Use bleach
= Apply liquid to the cloth, not the screen.

Q: What protects a computer against both power cuts and low voltage (to a point)?
- A mouse
* A UPS
- A flash drive
- A cable tie
= A UPS provides battery power and often voltage correction.

Q: Which battery is dangerous and should be taken to a technician?
- One that is half charged
* A swollen or leaking battery
- A battery that is cool
- One with a sticker
= Swollen batteries can catch fire.

Q: Which is a correct statement about the 3-2-1 backup rule?
- Three computers, two printers, one phone
* Three copies, on two types of storage, with one stored in another place
- One copy only
- Two copies on the same disk
= 3-2-1 gives protection against many kinds of loss.

Q: What is the safest thing to do with an old, broken phone?
- Burn it
* Take it to a responsible e-waste collector after backing up and wiping data
- Throw it in a pit
- Give it away with all your accounts still signed in
= Responsible disposal protects the environment and your privacy.

Q: What are the five steps of the troubleshooting method in order?
- Fix, buy, test, sell, forget
* Identify, test, fix, verify, document
- Document, fix, identify, test, verify
- Test, test, test, fix, hope
= The method moves from understanding the problem to recording the solution.

Q: A computer will not start and shows no lights. What should you check first?
- Windows settings
* The wall socket, extension and power cable
- The browser
- The printer
= Check power from the source before suspecting parts.

Q: A desktop has fans spinning but a black screen. Which are sensible checks? (choose all that apply)
* The monitor is on and set to the correct input
* The video cable is firmly connected at both ends
- Open the power supply and look inside
- Reinstall Windows immediately
= Check the display chain first, and never open a power supply.

Q: Which shortcut opens Task Manager in Windows?
- Ctrl + Alt + Z
* Ctrl + Shift + Esc
- Alt + Tab
- Windows + D
= Ctrl + Shift + Esc starts Task Manager directly.

Q: A laptop is slow and very hot. Which causes are likely?
- Too few mouse buttons
* Blocked vents with dust and too many heavy programs running
- A wrong wallpaper
- Lack of a keyboard
= Dust and heavy loads cause heat and slowness.

Q: What should you do if a Windows PC keeps showing blue screens after you installed a new driver?
- Install five more drivers
* Note the stop code, roll back or remove the new driver, and test again
- Throw the PC away
- Paint the case
= Undo the recent change to see if it was the cause.

Q: What is Safe Mode for?
- Playing games
* Starting Windows with minimal drivers and programs to find or remove a problem
- Making backups automatically
- Increasing RAM
= Safe Mode isolates software causes.

Q: Before using Reset this PC you should:
- Delete the backup
* Back up your important files and check they can be opened
- Turn off the charger
- Remove the RAM
= A reset may erase data, so a tested backup is essential.

Q: Your files are locked and a message demands payment. What is the best response?
- Pay immediately
* Disconnect from the network, do not pay, and restore from a backup or ask a technician
- Send the message to all contacts
- Format your phone
= Paying does not guarantee recovery and encourages criminals; backups are the remedy.

Q: What does a driver do?
- It cools the CPU
* It lets the operating system communicate with a hardware device
- It prints paper
- It sends emails
= Drivers are small programs that control devices.

Q: Which is the safest way to get a printer driver?
- From a random website offering free driver scans
* From the printer maker's official website or Windows Update
- From a message sent by a stranger
- From a USB drive found on the road
= Official sources reduce the risk of malware.

Q: On a phone, what should you do before a factory reset? (choose all that apply)
* Back up photos, contacts and chats
* Make sure you remember your Google or Apple account password
- Share your PIN with a friend
- Keep the battery at 2 percent
= Backups and account details prevent data loss and lock-outs.

Q: What does a switch do on a network?
- Connects your network to the internet provider only
* Connects several wired devices in a LAN and forwards data to the correct port
- Provides electricity
- Translates web names
= A switch forms the wired part of a LAN.

Q: What does DHCP usually do?
- Plays video
* Automatically gives devices IP addresses on a network
- Cleans the cache
- Encrypts files
= DHCP removes the need to set addresses manually.

Q: On a laptop you run ping 8.8.8.8 and it works, but ping google.com fails. What is the likely problem?
- A dead battery
* DNS is not translating names into addresses
- A broken screen
- Lack of RAM
= The internet link works, but name resolution does not.

Q: Which Wi-Fi security setting should you avoid?
- WPA2 with a strong password
* An open network or old WEP
- WPA3 where supported
- A guest network for visitors
= Open and WEP networks offer little or no protection.

Q: Which is safer when using public Wi-Fi in a cafe?
- Doing mobile money transactions on the open network
* Confirming the network name and avoiding sensitive transactions, or using mobile data
- Sharing folders openly
- Auto-connecting to every open network
= Public networks can be monitored or faked, so avoid sensitive tasks.
QUIZ,
];
