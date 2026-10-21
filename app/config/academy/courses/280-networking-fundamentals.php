<?php
return [
    'slug'        => 'networking-fundamentals',
    'title'       => 'Networking Fundamentals: IP Addressing, Routers and Wi-Fi',
    'category'    => 'hardware',
    'level'       => 'intermediate',
    'icon'        => 'globe',
    'sort'        => 280,
    'summary'     => 'Understand how networks work: IP addresses, subnetting, DNS, routers, NAT, Wi-Fi, cabling and troubleshooting, and design a small school or office network.',
    'description' => <<<'HTML'
<p>Every school lab, business and phone depends on networks. This course explains how they really work, so that you can set up a small network, understand the numbers on your router's screen, and find faults without guessing. It builds on the beginner course "Computer Maintenance, Troubleshooting and Networking Basics".</p>
<p>You will learn the layered models of networking, how devices are addressed, how to divide an address range into subnets with worked examples, how routers and NAT connect you to the internet, how Wi-Fi works and how to make it secure, and how to troubleshoot in a calm, step-by-step way. The course ends with a design task for a small computer lab.</p>
<p>The commands shown work on Windows 10 and 11, and the Linux equivalents are given where they differ. Everything can be tried on a normal home or school network without special equipment, and we never ask you to test anything on a network that is not yours.</p>
HTML,
    'outcomes'    => [
        'Describe the OSI and TCP/IP models and explain packets, protocols and MAC addresses',
        'Explain the difference between hubs, switches and routers',
        'Read an IPv4 address and subnet mask, and convert between mask and CIDR notation',
        'Divide a network into subnets and calculate network, broadcast and host ranges correctly',
        'Explain DHCP, DNS, NAT, ports and firewalls and what happens when you open a website',
        'Choose Wi-Fi standards, bands and channels and place access points well',
        'Secure a network with WPA2 or WPA3, guest networks and updated firmware',
        'Troubleshoot with ping, ipconfig, nslookup and traceroute, and design and document a small lab network',
    ],
    'requirements' => [
        'Completion of "Computer Maintenance, Troubleshooting and Networking Basics" or equivalent basic knowledge',
        'A computer connected to a network (home, school or centre) for the practice tasks',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Cisco Networking Academy', 'url' => 'https://www.netacad.com'],
        ['title' => 'Wi-Fi Alliance', 'url' => 'https://www.wi-fi.org'],
        ['title' => 'Internet Society: how the internet works', 'url' => 'https://www.internetsociety.org'],
        ['title' => 'Microsoft Support', 'url' => 'https://support.microsoft.com'],
    ],
    'modules' => [

        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: How networks work',
            'summary' => 'Layers, packets, protocols, Ethernet, switches and MAC addresses.',
            'lessons' => [
                [
                    'title'   => 'Networks, packets and protocols',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what a network is, what a packet is and why protocols matter. This is the base for everything else in the course, and it helps you picture what happens inside the cables and the air when you send a message on WhatsApp or open a page.</p>
<h3>What is a network?</h3>
<p>A <strong>network</strong> is two or more devices connected so that they can share data and resources such as files, printers and an internet connection. Networks come in sizes:</p>
<table>
<thead><tr><th>Type</th><th>Size</th><th>Example</th></tr></thead>
<tbody>
<tr><td>PAN</td><td>One person</td><td>Phone and Bluetooth earphones</td></tr>
<tr><td>LAN (local area network)</td><td>A room, building or campus</td><td>A school computer lab</td></tr>
<tr><td>WAN (wide area network)</td><td>Cities, countries, the world</td><td>A bank linking its branches; the internet itself</td></tr>
</tbody>
</table>
<p>The <strong>internet</strong> is a huge collection of connected networks (the name means "inter-network").</p>
<h3>Packets</h3>
<p>Data is not sent as one long stream. It is cut into small pieces called <strong>packets</strong>. Each packet carries a piece of the data (the <em>payload</em>) and a <strong>header</strong> with the sender address, the destination address and numbers that help put the pieces back in order. Think of sending a big book by post as many envelopes, each with an address and a page number.</p>
<p>Packets are useful because many users can share the same cable at once, and if one packet is lost only that piece needs to be sent again. The packets of one message may even travel different routes and still be assembled correctly at the end.</p>
<h3>Protocols</h3>
<p>A <strong>protocol</strong> is an agreed set of rules for communication, like a shared language and manners. Both sides must follow the same protocol or they cannot understand each other. Some you will meet:</p>
<table>
<thead><tr><th>Protocol</th><th>Job</th></tr></thead>
<tbody>
<tr><td>IP (Internet Protocol)</td><td>Addresses packets and moves them between networks</td></tr>
<tr><td>TCP</td><td>Reliable delivery: checks that everything arrives, in order</td></tr>
<tr><td>UDP</td><td>Fast delivery without checking; used for calls, video and games</td></tr>
<tr><td>HTTP and HTTPS</td><td>Web pages (HTTPS is encrypted)</td></tr>
<tr><td>DNS</td><td>Turns names into addresses</td></tr>
<tr><td>DHCP</td><td>Hands out addresses automatically</td></tr>
<tr><td>SMTP, IMAP</td><td>Sending and receiving email</td></tr>
</tbody>
</table>
<h3>Client and server</h3>
<p>In many network conversations one device, the <strong>client</strong>, asks for something and another, the <strong>server</strong>, answers. Your browser is a client; the website's computer is the server. In a <strong>peer-to-peer</strong> setup, devices share directly with each other without a central server.</p>
<h3>Speed words</h3>
<p><strong>Bandwidth</strong> is how much data a link can carry per second, in megabits per second (Mbps). <strong>Latency</strong> is the delay for one packet to arrive, in milliseconds (ms). A link can have high bandwidth but high latency (some satellite links), which feels slow for video calls even if downloads are fast.</p>
<h3>Try it yourself</h3>
<ol>
<li>Name three devices on your home or school network and say whether each is acting as a client, a server or both.</li>
<li>Open a command prompt and type <code>ping -n 4 example.com</code> (on Linux or macOS use <code>ping -c 4</code>). Write down the time in ms: that is the latency.</li>
<li>List two services where fast delivery matters more than perfect delivery (hint: live calls).</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A network connects devices to share data and resources; the internet connects networks.</li>
<li>Data travels in packets with addresses in their headers.</li>
<li>Protocols are the agreed rules; TCP is reliable and UDP is fast.</li>
<li>Bandwidth is capacity; latency is delay.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The OSI and TCP/IP models in plain words',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to name the layers of the OSI and TCP/IP models and use them to describe where a problem lives. Layers give technicians a shared map, so that "it is a layer 1 problem" means "look at the cables first".</p>
<h3>Why layers?</h3>
<p>Sending a message is a complicated job, so it is divided into layers. Each layer does one small task and talks only to the layer above and below. A layer can be improved without changing the others, in the same way a courier can change from a bicycle to a van without changing the letter inside.</p>
<h3>The OSI model: seven layers</h3>
<p>OSI means Open Systems Interconnection. It is a teaching model, and a useful way to organise your thinking. A common memory phrase, from the bottom, is "Please Do Not Throw Sausage Pizza Away".</p>
<table>
<thead><tr><th>No.</th><th>Layer</th><th>Plain meaning</th><th>Examples</th></tr></thead>
<tbody>
<tr><td>7</td><td>Application</td><td>The service the user sees</td><td>Browser, email, HTTP, DNS</td></tr>
<tr><td>6</td><td>Presentation</td><td>Format, encryption</td><td>Encoding, TLS (often placed here)</td></tr>
<tr><td>5</td><td>Session</td><td>Starting and ending conversations</td><td>Login sessions</td></tr>
<tr><td>4</td><td>Transport</td><td>Delivery between programs</td><td>TCP, UDP, ports</td></tr>
<tr><td>3</td><td>Network</td><td>Addresses and routes between networks</td><td>IP, routers</td></tr>
<tr><td>2</td><td>Data link</td><td>Delivery on one local network</td><td>Ethernet, Wi-Fi, MAC addresses, switches</td></tr>
<tr><td>1</td><td>Physical</td><td>Signals on the medium</td><td>Cables, radio waves, plugs</td></tr>
</tbody>
</table>
<h3>The TCP/IP model: four layers</h3>
<p>The internet itself is built on the simpler TCP/IP model. It combines the OSI layers like this:</p>
<table>
<thead><tr><th>TCP/IP layer</th><th>OSI layers it covers</th></tr></thead>
<tbody>
<tr><td>Application</td><td>5, 6 and 7</td></tr>
<tr><td>Transport</td><td>4</td></tr>
<tr><td>Internet</td><td>3</td></tr>
<tr><td>Network access (link)</td><td>1 and 2</td></tr>
</tbody>
</table>
<h3>Encapsulation: wrapping data</h3>
<p>When you send data, each layer adds its own header, like putting a letter in an envelope, then in a bigger envelope. The application hands data down; transport adds port numbers; IP adds addresses; the link layer adds hardware addresses; finally the bits travel over the cable or radio. The receiver reverses the steps, opening each envelope. The data unit has different names: a <em>segment</em> at the transport layer, a <em>packet</em> at the network layer, and a <em>frame</em> at the link layer.</p>
<h3>Using the model to troubleshoot</h3>
<table>
<thead><tr><th>Symptom</th><th>Layer to check first</th></tr></thead>
<tbody>
<tr><td>No link light, loose cable</td><td>1 (physical)</td></tr>
<tr><td>Connected to Wi-Fi but no address</td><td>2 and 3 (link, network)</td></tr>
<tr><td>Can ping an address but a website does not open</td><td>4 to 7 (ports, DNS, the service)</td></tr>
</tbody>
</table>
<h3>Try it yourself</h3>
<ol>
<li>Write the seven OSI layers from the bottom up from memory, then check yourself against the table.</li>
<li>For each item, name its OSI layer: a Cat6 cable, an IP address, a web browser, a MAC address, TCP.</li>
<li>Your laptop shows Wi-Fi connected, yet nothing loads. Write down which layers you would check first, in order.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>OSI has seven layers; TCP/IP has four and is what the internet uses.</li>
<li>Each layer adds a header; this is called encapsulation.</li>
<li>Cables and radio are layer 1; switches and MACs layer 2; IP and routers layer 3.</li>
<li>Layers help you troubleshoot from the bottom up.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Ethernet, hubs, switches and MAC addresses',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain how devices on a local network find each other, and why a switch is better than a hub. This helps you choose the right equipment for a lab or office.</p>
<h3>Ethernet</h3>
<p><strong>Ethernet</strong> is the standard way wired local networks work. A device sends <strong>frames</strong> over a cable, usually a twisted-pair cable with RJ45 plugs, at speeds such as 100 Mbps (Fast Ethernet), 1 Gbps (Gigabit) or 10 Gbps. Gigabit is now the normal choice for new offices and labs. Wi-Fi is a wireless cousin that follows a different standard but is meant to work together with Ethernet.</p>
<h3>MAC addresses</h3>
<p>Every network card has a <strong>MAC address</strong> (Media Access Control), a 48-bit hardware identifier written as twelve hexadecimal digits, for example <code>3C:52:82:1A:B4:09</code>. The first half identifies the manufacturer and the second half is unique to the card. Frames use MAC addresses to reach the right device <em>on the same local network</em>. Modern phones may use randomised ("private") MAC addresses for Wi-Fi, which is a privacy feature.</p>
<p>You can see yours with <code>ipconfig /all</code> (Windows; look for "Physical Address") or <code>ip link</code> (Linux).</p>
<h3>Hubs: old and noisy</h3>
<p>A <strong>hub</strong> repeats every signal it receives out of all its other ports. Everyone hears everyone, and devices must take turns; two devices speaking at once cause a <em>collision</em>. Hubs are slow, waste bandwidth and let any connected device see all the traffic. They are obsolete.</p>
<h3>Switches: the smart way</h3>
<p>A <strong>switch</strong> learns the MAC address of the device on each port and keeps a table. When a frame arrives, it sends it only to the port where the destination lives. Many pairs can talk at the same time, at full speed, and traffic is not broadcast everywhere.</p>
<table>
<thead><tr><th></th><th>Hub</th><th>Switch</th></tr></thead>
<tbody>
<tr><td>Sends frames to</td><td>All ports</td><td>Only the right port</td></tr>
<tr><td>Collisions</td><td>Common</td><td>Avoided</td></tr>
<tr><td>Speed per device</td><td>Shared</td><td>Full speed each</td></tr>
<tr><td>Privacy</td><td>Poor</td><td>Better</td></tr>
<tr><td>Today</td><td>Obsolete</td><td>Standard</td></tr>
</tbody>
</table>
<p>If the switch does not yet know where a MAC address is, it floods the frame out of every port once, learns the answer, and remembers it. A <strong>broadcast</strong> frame (destination <code>FF:FF:FF:FF:FF:FF</code>) goes to every device on purpose, for example when a device asks "who has this IP address?". This question is part of <strong>ARP</strong> (Address Resolution Protocol), which maps an IP address to a MAC address. See your computer's list with <code>arp -a</code>.</p>
<h3>Switch features</h3>
<ul>
<li><strong>Unmanaged</strong> switches work out of the box and suit small labs.</li>
<li><strong>Managed</strong> switches can be configured (VLANs, monitoring, port control) and suit larger sites.</li>
<li><strong>PoE</strong> (Power over Ethernet) switches power devices such as access points and cameras over the cable.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Run <code>ipconfig /all</code> (or <code>ip link</code>) and write down the MAC address of your network card.</li>
<li>Run <code>arp -a</code> and find the entry for your router (its IP is your default gateway). Note its MAC address.</li>
<li>Look at a real switch or router at your centre. Count the ports and read the speed marking (10/100/1000).</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A MAC address is a built-in 48-bit address for a network card, used on the local network.</li>
<li>A hub repeats to every port; a switch forwards only to the right port.</li>
<li>ARP finds the MAC address that belongs to an IP address.</li>
<li>Use gigabit switches for new installations; PoE helps with access points.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Network devices and topologies',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to name the main network devices, say what each does, and describe how a network is laid out. When you plan a lab, you will know what to buy and how it connects together.</p>
<h3>Common devices</h3>
<table>
<thead><tr><th>Device</th><th>Job</th><th>OSI layer</th></tr></thead>
<tbody>
<tr><td>Network card (NIC)</td><td>Connects a computer to the network; has the MAC address</td><td>1 and 2</td></tr>
<tr><td>Switch</td><td>Connects wired devices in one LAN</td><td>2</td></tr>
<tr><td>Router</td><td>Connects different networks, such as your LAN and the internet</td><td>3</td></tr>
<tr><td>Access point (AP)</td><td>Provides Wi-Fi and joins wireless devices to the wired network</td><td>1 and 2</td></tr>
<tr><td>Modem or ONT</td><td>Converts the provider's line or fibre signal into Ethernet</td><td>1</td></tr>
<tr><td>Firewall</td><td>Filters traffic by rules</td><td>3 to 7</td></tr>
<tr><td>Repeater or extender</td><td>Extends a Wi-Fi signal</td><td>1</td></tr>
</tbody>
</table>
<p>A <strong>home router</strong> is usually several devices in one box: a router, a small switch (the LAN ports), a Wi-Fi access point, a firewall and a DHCP server. A school or office usually separates them so that each can be chosen and replaced on its own.</p>
<h3>Topologies: the shape of a network</h3>
<p>A <strong>topology</strong> is the layout of how devices connect.</p>
<ul>
<li><strong>Star:</strong> every device connects to a central switch. This is what almost all modern LANs use. If one cable fails only one device is affected; if the central switch fails, everything on it stops.</li>
<li><strong>Bus:</strong> all devices share a single cable. It is old and fragile and appears only in textbooks and old buildings.</li>
<li><strong>Ring:</strong> each device connects to two neighbours, with data going round a loop. It is rare today.</li>
<li><strong>Mesh:</strong> devices connect to several others, so there are many paths. Wi-Fi mesh systems and the internet use this idea for resilience.</li>
<li><strong>Hybrid or tree:</strong> a mix, for example several star networks linked to a central switch, as in a campus.</li>
</ul>
<h3>Wired and wireless</h3>
<table>
<thead><tr><th></th><th>Wired (Ethernet)</th><th>Wireless (Wi-Fi)</th></tr></thead>
<tbody>
<tr><td>Speed and stability</td><td>High, steady</td><td>Good, but varies with distance and walls</td></tr>
<tr><td>Security</td><td>Harder to intercept</td><td>Needs strong encryption</td></tr>
<tr><td>Mobility</td><td>None</td><td>Move freely</td></tr>
<tr><td>Cost to set up</td><td>Cable and labour</td><td>Lower, but needs planning</td></tr>
</tbody>
</table>
<p>A common good design is wired for fixed computers, servers and access points, and wireless for phones and laptops.</p>
<h3>Try it yourself</h3>
<ol>
<li>Look at your home or school router. List which of the roles (router, switch, access point, firewall, DHCP) it performs.</li>
<li>Draw a star-topology diagram of a lab with one switch, five computers, a printer and a router to the internet.</li>
<li>Mark on your drawing what would stop working if the switch failed.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Switches join devices in a LAN; routers join different networks.</li>
<li>An access point provides Wi-Fi; a home router often combines many roles.</li>
<li>The star topology is the standard layout for wired networks.</li>
<li>Use cable where you can and Wi-Fi where you must.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Why is data sent as small packets rather than one long stream?
- Because cables can only carry small files
* Many users can share the link, and only a lost piece needs to be re-sent
- Because packets are encrypted
- To make the connection slower
= Packets let a link be shared fairly and make recovery from loss cheap.

Q: Which protocol is the better choice for a live video call where speed matters more than perfect delivery?
- TCP
* UDP
- SMTP
- ARP
= UDP skips the checking and resending that TCP does, which suits live calls and games.

Q: At which OSI layer do routers and IP addresses work?
- Layer 1
- Layer 2
* Layer 3
- Layer 7
= The network layer (layer 3) handles addresses and routes between networks.

Q: A computer shows no link light and the cable is loose. Which layer is the problem?
* Layer 1, the physical layer
- Layer 4, the transport layer
- Layer 7, the application layer
- Layer 3, the network layer
= Cables and connectors belong to the physical layer, so start there.

Q: What is a MAC address?
- A number given by the internet provider
- A website address
* A hardware address built into a network card
- A password for Wi-Fi
= The MAC address identifies the network card and is used on the local network.

Q: What does a switch do that a hub does not?
- It provides internet access
* It sends a frame only to the port where the destination device is
- It assigns IP addresses
- It encrypts all traffic
= A switch learns which MAC address is on each port and forwards frames only there.

Q: What does ARP do?
- Turns website names into IP addresses
- Gives devices IP addresses
* Finds the MAC address that matches an IP address on the local network
- Blocks unsafe traffic
= ARP maps IP addresses to MAC addresses. DNS maps names to IP addresses.

Q: Which topology is used by almost all modern wired LANs?
- Bus
- Ring
* Star
- Tree with no switch
= Each device connects to a central switch, which makes faults easy to isolate.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: IP addressing',
            'summary' => 'IPv4, private and public addresses, masks, CIDR, subnetting, IPv6, DHCP and DNS.',
            'lessons' => [
                [
                    'title'   => 'IPv4 addresses: structure, private and public',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to read an IPv4 address, explain its binary form, and tell private addresses from public ones. Every device on a network needs an address, and knowing the ranges helps you recognise at a glance whether an address is local or on the internet.</p>
<h3>Four numbers, 32 bits</h3>
<p>An <strong>IPv4 address</strong> is a 32-bit number, written as four decimal numbers between 0 and 255 separated by dots. Each number is one <strong>octet</strong> (8 bits). For example:</p>
<pre><code>192.168.1.25
= 11000000.10101000.00000001.00011001</code></pre>
<p>To convert one octet to binary, use the place values 128, 64, 32, 16, 8, 4, 2, 1. For 192: 128 + 64 = 192, so the first two bits are 1 and the rest are 0, giving 11000000. For 168: 128 + 32 + 8 = 168, giving 10101000. For 25: 16 + 8 + 1 = 25, giving 00011001.</p>
<p>With 32 bits there are 2^32, about 4.3 billion, possible addresses. That sounds like a lot, but the world ran short of them, which is why private addresses, NAT and IPv6 were created.</p>
<h3>Network part and host part</h3>
<p>An address has two parts: the <strong>network</strong> part, which names the network, and the <strong>host</strong> part, which names the device inside it. Devices on the same network share the same network part. The subnet mask (next lesson) tells where the split is.</p>
<h3>Public and private addresses</h3>
<p>A <strong>public</strong> address is unique on the internet and given by your internet provider. <strong>Private</strong> addresses are reserved for use inside homes, schools and offices, and may be reused by everyone, because they are never routed over the internet. The private ranges (from a standard called RFC 1918) are:</p>
<table>
<thead><tr><th>Range</th><th>CIDR</th><th>Typical use</th></tr></thead>
<tbody>
<tr><td>10.0.0.0 to 10.255.255.255</td><td>10.0.0.0/8</td><td>Large organisations</td></tr>
<tr><td>172.16.0.0 to 172.31.255.255</td><td>172.16.0.0/12</td><td>Medium networks</td></tr>
<tr><td>192.168.0.0 to 192.168.255.255</td><td>192.168.0.0/16</td><td>Homes and small offices</td></tr>
</tbody>
</table>
<p>A common mistake is thinking that 172.32.1.1 is private. It is not: the private block ends at 172.31.255.255. So 172.16.5.4 is private but 172.32.5.4 is public.</p>
<h3>Special addresses</h3>
<ul>
<li><code>127.0.0.1</code> (and all of 127.0.0.0/8) is <strong>loopback</strong>: "this computer". Pinging it tests your own network software.</li>
<li><code>169.254.x.x</code> is <strong>link-local</strong> (APIPA on Windows). A device that shows this has failed to get an address from DHCP.</li>
<li><code>0.0.0.0</code> means "no address yet" or "any address".</li>
<li>The last address of a network is the <strong>broadcast</strong> address, for "everyone on this network"; the first is the <strong>network address</strong>, the name of the network itself. Neither can be given to a device.</li>
<li><code>100.64.0.0/10</code> is used by some providers (carrier-grade NAT) and is not normal public space either.</li>
</ul>
<h3>Static and dynamic</h3>
<p>An address can be <strong>static</strong> (typed in by hand, good for servers and printers) or <strong>dynamic</strong> (handed out automatically by DHCP, good for phones and laptops).</p>
<h3>Try it yourself</h3>
<ol>
<li>Run <code>ipconfig</code> (or <code>ip -br addr</code> on Linux) and write down your IPv4 address. Is it private or public? Which range is it in?</li>
<li>Convert 10.0.5.200 to binary, octet by octet. (Answer: 00001010.00000000.00000101.11001000.)</li>
<li>Classify each as private, public or special: 192.168.50.7, 8.8.8.8, 172.20.3.3, 169.254.7.9, 172.40.1.1.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>An IPv4 address is 32 bits, written as four octets in decimal.</li>
<li>Private ranges: 10/8, 172.16/12 and 192.168/16; everything else routable is public.</li>
<li>169.254.x.x means DHCP failed; 127.0.0.1 is the computer itself.</li>
<li>The first and last addresses of a network are not given to hosts.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Subnet masks and CIDR notation',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to read a subnet mask, convert it to CIDR notation, and say how many hosts a network can hold. A router and every computer need the mask to know which addresses are on their own network and which are somewhere else.</p>
<h3>What a subnet mask does</h3>
<p>A <strong>subnet mask</strong> is a 32-bit number that marks which bits of an address are the network part (written as 1s) and which are the host part (written as 0s). The 1s always come first, with no gaps.</p>
<pre><code>255.255.255.0 = 11111111.11111111.11111111.00000000</code></pre>
<p>Here the first 24 bits are the network part and the last 8 are the host part. The computer does a bitwise AND of the address and the mask to find the network address. For 192.168.1.25 with mask 255.255.255.0 the result is 192.168.1.0. Two devices that get the same network address are on the same network and can talk directly; otherwise they go through the router.</p>
<h3>CIDR notation</h3>
<p><strong>CIDR</strong> (Classless Inter-Domain Routing) writes the mask as a slash and the number of 1s: 255.255.255.0 is <code>/24</code>, so 192.168.1.25 with that mask is written <code>192.168.1.25/24</code>. It is shorter and easier.</p>
<table>
<thead><tr><th>CIDR</th><th>Mask</th><th>Total addresses</th><th>Usable hosts</th></tr></thead>
<tbody>
<tr><td>/8</td><td>255.0.0.0</td><td>16,777,216</td><td>16,777,214</td></tr>
<tr><td>/16</td><td>255.255.0.0</td><td>65,536</td><td>65,534</td></tr>
<tr><td>/24</td><td>255.255.255.0</td><td>256</td><td>254</td></tr>
<tr><td>/25</td><td>255.255.255.128</td><td>128</td><td>126</td></tr>
<tr><td>/26</td><td>255.255.255.192</td><td>64</td><td>62</td></tr>
<tr><td>/27</td><td>255.255.255.224</td><td>32</td><td>30</td></tr>
<tr><td>/28</td><td>255.255.255.240</td><td>16</td><td>14</td></tr>
<tr><td>/29</td><td>255.255.255.248</td><td>8</td><td>6</td></tr>
<tr><td>/30</td><td>255.255.255.252</td><td>4</td><td>2</td></tr>
</tbody>
</table>
<h3>The host formula</h3>
<p>If h is the number of host bits (32 minus the CIDR number), then:</p>
<pre><code>total addresses = 2^h
usable hosts    = 2^h - 2   (minus the network address and the broadcast address)</code></pre>
<p>For /24: h = 8, so 2^8 = 256 addresses and 254 usable hosts. For /26: h = 6, so 64 addresses and 62 usable hosts. For /28: h = 4, so 16 addresses and 14 hosts.</p>
<h3>Where do the mask numbers come from?</h3>
<p>In an octet, the leftmost bits set to 1 give these values: 1 bit = 128, 2 bits = 192, 3 bits = 224, 4 bits = 240, 5 bits = 248, 6 bits = 252, 7 bits = 254, 8 bits = 255. So /26 is 24 bits plus 2 more: the fourth octet is 11000000 = 192, hence 255.255.255.192. And /27 is 24 + 3 = 11100000 = 224.</p>
<h3>Common mistakes</h3>
<ul>
<li>Forgetting to subtract 2 when counting usable hosts.</li>
<li>Writing a mask with gaps, such as 255.0.255.0, which is not valid.</li>
<li>Giving two devices different masks on the same cable, which makes one think the other is far away.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Run <code>ipconfig</code> and note your subnet mask. Convert it to CIDR (255.255.255.0 is /24).</li>
<li>Fill in: /27 has mask 255.255.255.___, ___ addresses and ___ usable hosts. (Answer: 224, 32, 30.)</li>
<li>How many usable hosts are in a /29? (Answer: 2^3 - 2 = 6.)</li>
<li>A school needs 100 hosts in one network. What is the smallest CIDR that fits? (Answer: /25, which gives 126.)</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The mask separates the network part from the host part; CIDR counts the 1 bits.</li>
<li>Usable hosts = 2^h - 2, where h is the number of host bits.</li>
<li>Learn the fourth-octet values 128, 192, 224, 240, 248, 252.</li>
<li>Devices on the same network have the same network address.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'A gentle introduction to subnetting',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to split one network into smaller ones and write down each subnet's network address, usable range and broadcast address. Subnetting lets a school keep staff, students and servers on separate networks, which improves safety and reduces noise.</p>
<h3>The block size trick</h3>
<p>For masks that end in the fourth octet, there is a quick method. Take 256 minus the mask's interesting octet; that is the <strong>block size</strong> (the distance between one subnet and the next).</p>
<table>
<thead><tr><th>CIDR</th><th>Fourth octet of mask</th><th>Block size</th></tr></thead>
<tbody>
<tr><td>/25</td><td>128</td><td>128</td></tr>
<tr><td>/26</td><td>192</td><td>64</td></tr>
<tr><td>/27</td><td>224</td><td>32</td></tr>
<tr><td>/28</td><td>240</td><td>16</td></tr>
<tr><td>/29</td><td>248</td><td>8</td></tr>
<tr><td>/30</td><td>252</td><td>4</td></tr>
</tbody>
</table>
<p>Subnets start at multiples of the block size: 0, 64, 128, 192 for /26. The broadcast address is the last address before the next subnet. Usable hosts lie between.</p>
<h3>Worked example 1: split 192.168.10.0/24 into four subnets</h3>
<p>We need 4 subnets, which takes 2 extra bits (2^2 = 4), so the new mask is /26 (24 + 2), which is 255.255.255.192, block size 64, with 62 usable hosts per subnet.</p>
<table>
<thead><tr><th>Subnet</th><th>Network address</th><th>Usable hosts</th><th>Broadcast</th></tr></thead>
<tbody>
<tr><td>1</td><td>192.168.10.0/26</td><td>.1 to .62</td><td>192.168.10.63</td></tr>
<tr><td>2</td><td>192.168.10.64/26</td><td>.65 to .126</td><td>192.168.10.127</td></tr>
<tr><td>3</td><td>192.168.10.128/26</td><td>.129 to .190</td><td>192.168.10.191</td></tr>
<tr><td>4</td><td>192.168.10.192/26</td><td>.193 to .254</td><td>192.168.10.255</td></tr>
</tbody>
</table>
<p>Check: 4 subnets of 64 addresses is 256, which is the whole /24.</p>
<h3>Worked example 2: which subnet is this host in?</h3>
<p>Host 192.168.5.77/26. Block size is 64. The multiples of 64 are 0, 64, 128, 192. The largest multiple that is not above 77 is 64, so the network address is 192.168.5.64. The next subnet starts at 128, so the broadcast is 192.168.5.127. Usable range: 192.168.5.65 to 192.168.5.126.</p>
<h3>Worked example 3: choose a size for each department</h3>
<p>A centre has the block 192.168.20.0/24. It needs one network for 50 computers, one for 25 and one for 10.</p>
<ol>
<li>50 hosts needs 2^h - 2 of at least 50, so h = 6 (62 hosts), a /26.</li>
<li>25 hosts: h = 5 gives 30 usable, a /27.</li>
<li>10 hosts: h = 4 gives 14 usable, a /28.</li>
</ol>
<p>Place the biggest first so that blocks line up: 192.168.20.0/26 (addresses 0 to 63), then 192.168.20.64/27 (64 to 95), then 192.168.20.96/28 (96 to 111). The rest, from 192.168.20.112 on, is free for future use. This method of using different sizes is called <strong>VLSM</strong> (variable length subnet masking).</p>
<h3>Worked example 4: a link between two routers</h3>
<p>A link between two routers needs only 2 addresses. A /30 gives 4 addresses and 2 usable: for 10.0.0.0/30 the hosts are 10.0.0.1 and 10.0.0.2, with 10.0.0.3 as the broadcast.</p>
<h3>Try it yourself</h3>
<ol>
<li>Split 192.168.1.0/24 into two subnets. Write each network address, range and broadcast. (Answer: 192.168.1.0/25 with .1 to .126 and broadcast .127; 192.168.1.128/25 with .129 to .254 and broadcast .255.)</li>
<li>Find the network and broadcast address of 10.1.1.130/27. (Answer: block 32; 128 is the multiple; network 10.1.1.128, broadcast 10.1.1.159.)</li>
<li>Work out the smallest subnet for 12 hosts. (Answer: /28, 14 usable.)</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Block size is 256 minus the mask octet; subnets start at multiples of it.</li>
<li>The broadcast address is one below the next subnet; hosts lie between.</li>
<li>To size a subnet, find the smallest h where 2^h - 2 covers your hosts.</li>
<li>Always check that your subnets add up and do not overlap.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'IPv6, DHCP and DNS',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what IPv6 is, how DHCP gives out addresses and how DNS turns names into addresses. Together these three services explain why you can plug in or join Wi-Fi and open a website by name, without typing any numbers.</p>
<h3>IPv6 overview</h3>
<p>IPv4 has about 4.3 billion addresses, which is not enough for the world's phones, computers and sensors. <strong>IPv6</strong> uses 128-bit addresses, which gives a number so large that every device can have its own. An IPv6 address is written as eight groups of four hexadecimal digits separated by colons:</p>
<pre><code>2001:0db8:0000:0000:0000:0000:0000:0001
= 2001:db8::1          (short form)</code></pre>
<p>To shorten, drop leading zeros in each group, and replace one run of all-zero groups with <code>::</code> (only once per address). The documentation range 2001:db8::/32 is used in examples. Other types: <code>::1</code> is loopback and <code>fe80::/10</code> is link-local (used automatically on each local link). IPv6 does not need NAT for address shortage, and devices can create their own address automatically. Many providers and mobile networks now support it, and IPv4 and IPv6 often run side by side (<em>dual stack</em>).</p>
<h3>DHCP: addresses on request</h3>
<p><strong>DHCP</strong> (Dynamic Host Configuration Protocol) lets a device get its settings automatically. Usually the router is the DHCP server. The conversation has four steps, often remembered as DORA:</p>
<ol>
<li><strong>Discover:</strong> the new device shouts to everyone: "Is there a DHCP server?"</li>
<li><strong>Offer:</strong> the server offers an address.</li>
<li><strong>Request:</strong> the device says "I would like that one."</li>
<li><strong>Acknowledge:</strong> the server confirms and lends it for a period called a <em>lease</em>.</li>
</ol>
<p>DHCP gives the address, subnet mask, <strong>default gateway</strong> (the router) and <strong>DNS server</strong>. Two problems to know: if two DHCP servers (say two routers) are on one network they can confuse devices; and if no server answers, Windows picks a 169.254.x.x address. For printers and servers, set a <strong>DHCP reservation</strong> (the router always gives the same address to that MAC address) or a static address outside the DHCP range, so that the address never changes.</p>
<h3>DNS: the internet phone book</h3>
<p>People remember names; computers need addresses. <strong>DNS</strong> (Domain Name System) translates a name such as <code>www.example.com</code> to an address. Your device asks a <strong>DNS resolver</strong> (often your provider's, or a public one). If the resolver does not know, it asks on your behalf, starting from the root servers, then the servers for <code>.com</code>, then those of <code>example.com</code>, and keeps the answer for a while (caching).</p>
<table>
<thead><tr><th>Record</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>A</td><td>Name to IPv4 address</td></tr>
<tr><td>AAAA</td><td>Name to IPv6 address</td></tr>
<tr><td>CNAME</td><td>One name is an alias for another</td></tr>
<tr><td>MX</td><td>Mail servers for a domain</td></tr>
<tr><td>NS</td><td>The servers in charge of a domain</td></tr>
</tbody>
</table>
<h3>Try it yourself</h3>
<ol>
<li>Run <code>ipconfig /all</code>. Find the DHCP server, lease times, default gateway and DNS servers.</li>
<li>Run <code>nslookup example.com</code> and write down the address(es) returned.</li>
<li>Shorten this IPv6 address: 2001:0db8:0000:0000:0000:0000:0000:0010. (Answer: 2001:db8::10.)</li>
<li>Explain why a printer should not get a new address every day.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>IPv6 has 128-bit addresses written in hexadecimal and shortened with <code>::</code>.</li>
<li>DHCP (Discover, Offer, Request, Acknowledge) gives address, mask, gateway and DNS server.</li>
<li>Use reservations or static addresses for printers and servers.</li>
<li>DNS turns names into addresses; no DNS means names fail but IP addresses still work.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which of these addresses is a PUBLIC address?
- 192.168.4.20
- 10.200.1.5
- 172.20.8.8
* 172.32.8.8
= The private block 172.16.0.0/12 ends at 172.31.255.255, so 172.32.8.8 is public.

Q: A laptop shows the address 169.254.33.8. What does this most likely mean?
- It is connected directly to the internet
* It could not get an address from a DHCP server
- It is using IPv6
- It is the router
= 169.254.x.x is the link-local address a Windows computer uses when DHCP fails.

Q: What is the CIDR notation of the mask 255.255.255.192?
- /24
- /25
* /26
- /27
= 255.255.255.192 has 24 + 2 = 26 bits set to 1.

Q: How many usable host addresses are in a /27 network?
- 32
* 30
- 28
- 62
= A /27 has 5 host bits: 2^5 = 32 addresses, minus the network and broadcast addresses gives 30.

Q: What are the network and broadcast addresses of the host 192.168.5.77/26?
- 192.168.5.0 and 192.168.5.255
- 192.168.5.64 and 192.168.5.255
* 192.168.5.64 and 192.168.5.127
- 192.168.5.76 and 192.168.5.78
= The block size is 64, and 77 falls in the block that starts at 64 and ends at 127.

Q: A centre needs a subnet for 25 computers. What is the smallest suitable CIDR prefix?
- /26
* /27
- /28
- /29
= A /27 gives 30 usable hosts. A /28 gives only 14, which is too few.

Q: What are the four steps DHCP uses?
- Query, Reply, Accept, Done
- Ask, Give, Take, End
* Discover, Offer, Request, Acknowledge
- Connect, Lease, Release, Reset
= DORA: Discover, Offer, Request, Acknowledge.

Q: Which are valid ways to write the IPv6 address 2001:0db8:0000:0000:0000:0000:0000:0001? (choose all that apply)
* 2001:db8::1
* 2001:db8:0:0:0:0:0:1
- 2001:db8:::1
- 2001:db8::0::1
= Leading zeros can be dropped and one run of zero groups can become ::, but :: may be used only once.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Routers and the internet',
            'summary' => 'Routing, default gateway, NAT, ports, firewalls, ISPs, what happens when you open a website, traceroute and ping.',
            'lessons' => [
                [
                    'title'   => 'Routing, default gateway and NAT',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain how a router moves packets between networks, what the default gateway is, and how NAT lets many devices share one public address. Understanding this makes router settings far less mysterious.</p>
<h3>What a router does</h3>
<p>A <strong>router</strong> connects different networks and forwards packets between them. It has an address on each network it joins. When a packet arrives, the router looks at the destination IP address, checks its <strong>routing table</strong> (a list of networks and where to send packets for each) and sends the packet to the next router, called the <strong>next hop</strong>. Every router along the way makes its own decision, until the packet arrives.</p>
<p>View your computer's routing table with <code>route print</code> (Windows) or <code>ip route</code> (Linux). The entry for 0.0.0.0/0 is the <strong>default route</strong>: "if nothing else matches, send it here".</p>
<h3>The default gateway</h3>
<p>Your computer follows a simple rule when it sends a packet:</p>
<ol>
<li>If the destination is on my own network (judged with my mask), send it directly using ARP and the MAC address.</li>
<li>Otherwise, send it to the <strong>default gateway</strong>, the router on my network, and let it deal with it.</li>
</ol>
<p>If the default gateway is wrong or missing, your computer can talk to local devices but not to the internet. Typical gateway addresses are the first or last usable address of the network, such as 192.168.1.1.</p>
<h3>Static and dynamic routing</h3>
<p><strong>Static routes</strong> are typed in by an administrator, which is fine for small networks. <strong>Dynamic routing protocols</strong> (such as OSPF inside an organisation, and BGP between providers across the internet) let routers share what they know and adjust automatically when a link fails.</p>
<h3>NAT: sharing a public address</h3>
<p>Private addresses cannot be used on the internet, and public IPv4 addresses are scarce. <strong>NAT</strong> (Network Address Translation) solves this, usually in your router. When a device with a private address sends a packet out, the router replaces the source address with its own public address and remembers the conversation, usually by also changing the source <strong>port</strong>. When the reply returns, the router looks up its table and forwards it to the right device. This version, which many devices share one public address, is also called PAT or "NAT overload".</p>
<table>
<thead><tr><th>Inside (private)</th><th>After NAT (public)</th></tr></thead>
<tbody>
<tr><td>192.168.1.10:51000 to a web server</td><td>203.0.113.5:40001 to the same server</td></tr>
<tr><td>192.168.1.11:51000 to the same server</td><td>203.0.113.5:40002 to the same server</td></tr>
</tbody>
</table>
<p>(The 203.0.113.x range is reserved for examples.) NAT has two effects. First, devices behind NAT are not directly reachable from the internet, which gives a basic level of protection, though NAT is not a real firewall. Second, if you want to host a server at home, you must create a <strong>port forward</strong>, a rule that sends incoming traffic on a chosen port to one inside device. Some mobile and fibre providers use <strong>carrier-grade NAT</strong>, meaning you do not even have a public address of your own, so port forwarding is impossible without a special service.</p>
<h3>Try it yourself</h3>
<ol>
<li>Run <code>route print</code> (or <code>ip route</code>) and find the line for 0.0.0.0, the default route. Note the gateway.</li>
<li>Run <code>ipconfig</code> and compare the "Default Gateway" with that value.</li>
<li>Compare your computer's private IP with your public IP (search "what is my IP" in a browser). Explain why they differ.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Routers use a routing table to forward packets to the next hop.</li>
<li>The default gateway is where a computer sends traffic for other networks.</li>
<li>NAT lets many private devices share one public address by tracking ports.</li>
<li>Hosting from inside NAT needs a port forward (and a real public address).</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Ports, firewalls and ISPs',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what a port is, how a firewall decides what to allow, and what an internet service provider does. These ideas are behind most "why can I not reach this service?" questions.</p>
<h3>Ports</h3>
<p>An IP address finds the computer; a <strong>port</strong> finds the program on it. A port is a number from 0 to 65535, used with TCP or UDP. A computer might run a web server, an email server and a remote login service at once, each listening on its own port. Ports are grouped as well-known (0 to 1023), registered (1024 to 49151) and dynamic or ephemeral (49152 and above, used for the client side).</p>
<table>
<thead><tr><th>Port</th><th>Service</th></tr></thead>
<tbody>
<tr><td>22</td><td>SSH (secure remote login)</td></tr>
<tr><td>25</td><td>SMTP (sending email between servers)</td></tr>
<tr><td>53</td><td>DNS</td></tr>
<tr><td>67 and 68</td><td>DHCP (UDP)</td></tr>
<tr><td>80</td><td>HTTP (web)</td></tr>
<tr><td>143</td><td>IMAP (reading email)</td></tr>
<tr><td>443</td><td>HTTPS (secure web)</td></tr>
<tr><td>3389</td><td>Remote Desktop (Windows)</td></tr>
</tbody>
</table>
<p>A full conversation is identified by two addresses and two ports, for example your 192.168.1.10:51000 talking to a server at port 443. See your open connections with <code>netstat -an</code>.</p>
<h3>Firewalls</h3>
<p>A <strong>firewall</strong> checks traffic against rules and allows or blocks it. It can be software on a computer (Windows Defender Firewall, ufw on Linux) or a device or feature in a router. Rules usually look at addresses, ports and protocol. A <strong>stateful</strong> firewall remembers conversations, so replies to requests you started are allowed in automatically, while unrequested incoming traffic is blocked.</p>
<ul>
<li>The safest approach is <strong>default deny</strong>: block everything and allow only what is needed.</li>
<li>Only open ports for services you actually run, and close them when you stop.</li>
<li>Do not turn a firewall off to "fix" a problem; find the right rule instead. Disabling it, even for a test, should be brief and only on a network you trust.</li>
<li>Exposing Remote Desktop (3389) directly to the internet is a common cause of attacks; use a VPN instead.</li>
</ul>
<h3>Internet service providers</h3>
<p>An <strong>ISP</strong> sells you a connection to the internet. Common types include fibre, fixed wireless, DSL over phone lines and mobile data (4G and 5G), and in some places satellite. Questions to ask when choosing: real speed and not just "up to" speed, upload as well as download speed, monthly data limits, price in local currency, reliability and the support offered, and whether you get a public IP address. ISPs connect to each other at exchange points and through bigger networks, which is how traffic gets from your provider to a website hosted far away.</p>
<table>
<thead><tr><th>Connection</th><th>Good for</th><th>Watch out for</th></tr></thead>
<tbody>
<tr><td>Fibre</td><td>Fast and stable for labs and offices</td><td>Not available everywhere</td></tr>
<tr><td>Fixed wireless</td><td>Where cables do not reach</td><td>Needs line of sight and good signal</td></tr>
<tr><td>Mobile data (4G/5G)</td><td>Backup or small sites</td><td>Data caps and cost</td></tr>
<tr><td>Satellite</td><td>Remote places</td><td>Higher latency and cost</td></tr>
</tbody>
</table>
<h3>Try it yourself</h3>
<ol>
<li>Run <code>netstat -an</code> in a command prompt and find a line with port 443 while a web page is open.</li>
<li>Open Windows Security, then <strong>Firewall and network protection</strong>, and check that the firewall is on for your current network.</li>
<li>Write down your ISP's name, the connection type, and compare the speed you pay for with a speed test result.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The IP address finds the computer; the port finds the program.</li>
<li>Know the common ports: 22, 25, 53, 80, 143, 443.</li>
<li>A firewall should default to deny and allow only what is needed.</li>
<li>Choose an ISP by reliability, real speed, data limits and price.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'What happens when you open a website',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe, step by step, everything that happens between typing a web address and seeing a page. It joins together what you learned so far, and it is a favourite interview question for IT jobs.</p>
<h3>The journey</h3>
<p>Imagine you type <code>https://www.example.com</code> into your browser on a laptop connected to home Wi-Fi.</p>
<ol>
<li><strong>The browser checks its caches.</strong> It looks to see whether it already knows the address, or even already has the page saved.</li>
<li><strong>DNS lookup.</strong> The laptop asks its DNS resolver (the one that DHCP supplied) for the address of <code>www.example.com</code>. The answer, say 93.184.216.34, is returned and cached.</li>
<li><strong>Is the server local?</strong> The laptop compares that address with its own network using the subnet mask. It is not on the same network, so the packets must go to the default gateway.</li>
<li><strong>ARP.</strong> The laptop needs the gateway's MAC address. It asks on the local network with ARP, and the router replies.</li>
<li><strong>TCP handshake.</strong> The laptop and the server set up a connection with three messages: SYN (let us talk), SYN-ACK (agreed) and ACK (started). The destination port is 443.</li>
<li><strong>TLS handshake.</strong> For HTTPS, they agree on encryption and the server proves its identity with a certificate. After this, everything is encrypted and only the two ends can read it.</li>
<li><strong>The HTTP request.</strong> The browser sends a request such as GET for the page.</li>
<li><strong>Across the internet.</strong> Your router applies NAT, so the packet leaves with the public address. It is passed along by your ISP and many other routers, each choosing the next hop, until it reaches the server's network.</li>
<li><strong>The server responds</strong> with the page (HTML) and status code 200, which means OK. The reply travels back, NAT sends it to your laptop, and the browser reassembles the packets.</li>
<li><strong>The browser builds the page.</strong> The HTML names more files (images, style sheets, scripts), and the browser repeats requests for each of these, often to other servers, and then displays everything.</li>
</ol>
<h3>Common HTTP status codes</h3>
<table>
<thead><tr><th>Code</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>200</td><td>OK</td></tr>
<tr><td>301 or 302</td><td>Moved: go to another address</td></tr>
<tr><td>403</td><td>Forbidden: you may not see this</td></tr>
<tr><td>404</td><td>Not found</td></tr>
<tr><td>500</td><td>Server error</td></tr>
</tbody>
</table>
<h3>Where it can fail</h3>
<table>
<thead><tr><th>Error you see</th><th>Likely step</th></tr></thead>
<tbody>
<tr><td>"Server not found" or "DNS probe failed"</td><td>DNS lookup</td></tr>
<tr><td>"Connection timed out"</td><td>Gateway, ISP or a firewall in the path</td></tr>
<tr><td>"Your connection is not private"</td><td>The TLS certificate (wrong date on your computer, or a problem on the site)</td></tr>
<tr><td>404 page</td><td>The server works but the page does not exist</td></tr>
</tbody>
</table>
<h3>Try it yourself</h3>
<ol>
<li>Run <code>nslookup example.com</code> to do step 2 by hand.</li>
<li>Run <code>curl -I https://example.com</code> (curl is included in Windows 10 and 11) and read the status line.</li>
<li>In your browser, press F12 and open the <strong>Network</strong> tab, then reload a page. Count how many requests one page needs.</li>
<li>Write the ten steps in your own words, in a few lines each.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>DNS gives the address; ARP finds the gateway; TCP and TLS set up a secure connection.</li>
<li>Traffic to other networks goes to the default gateway, through NAT and many routers.</li>
<li>One page needs many requests.</li>
<li>The error message shows which step to investigate.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Ping and traceroute',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to use ping and traceroute to test a connection and to find where along the path a problem lies. They are the first tools a network technician reaches for, and you already have them on your computer.</p>
<h3>Ping: is it alive?</h3>
<p><code>ping</code> sends a small message (ICMP echo request) to a destination and waits for a reply. It reports whether the device answered and how long the round trip took.</p>
<pre><code>ping 192.168.1.1
ping -n 4 example.com        (Windows: 4 tries)
ping -c 4 example.com        (Linux and macOS: 4 tries)</code></pre>
<p>A reply line looks like <code>Reply from 93.184.216.34: bytes=32 time=48ms TTL=56</code>. At the end you get a summary of packets sent, received and lost, and the minimum, average and maximum times.</p>
<table>
<thead><tr><th>Result</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>Replies, low time (under 50 ms locally)</td><td>The path works</td></tr>
<tr><td>Some packets lost</td><td>Poor Wi-Fi, a congested link or a faulty cable</td></tr>
<tr><td>Request timed out</td><td>No answer: the device is off, unreachable, or blocks ping</td></tr>
<tr><td>Destination host unreachable</td><td>A router says it has no route</td></tr>
<tr><td>Could not find host</td><td>DNS failed (the name did not resolve)</td></tr>
</tbody>
</table>
<h3>A ping ladder</h3>
<p>Test from near to far to find the faulty step:</p>
<ol>
<li><code>ping 127.0.0.1</code>: does your own network software work?</li>
<li><code>ping</code> your own IP address: is your network card working?</li>
<li><code>ping</code> the default gateway: can you reach the router?</li>
<li><code>ping 8.8.8.8</code> (a well-known public DNS server): do you reach the internet by number?</li>
<li><code>ping example.com</code>: does name lookup work?</li>
</ol>
<p>The first step that fails shows where to look. Remember that some devices and firewalls block ping on purpose, so a failure is a clue rather than final proof.</p>
<h3>Traceroute: the path</h3>
<p>Every IP packet has a <strong>TTL</strong> (time to live) number that each router decreases by one. When it reaches zero, the router drops the packet and sends back an error. Traceroute uses this: it sends packets with TTL 1, then 2, then 3, and so on, and each router along the path reveals itself when it reports the expired packet.</p>
<pre><code>tracert example.com          (Windows)
traceroute example.com       (Linux)</code></pre>
<p>Each line is a hop, with three round-trip times and the router's name or address. Stars (<code>*</code>) mean no reply for that hop, which is often harmless, since some routers do not answer. What matters is where the times jump sharply or where all later hops fail. A first hop that is slow points to your own Wi-Fi or router; a jump on a later hop points to the ISP or a long-distance link (for instance an undersea cable, which adds delay).</p>
<h3>Try it yourself</h3>
<ol>
<li>Run the ping ladder above and record the result of each step.</li>
<li>Run <code>tracert example.com</code>. Count the hops, and note the first hop (it should be your router).</li>
<li>Run <code>ping -n 20 192.168.1.1</code> (use your own gateway) while you walk away from the router with a laptop on Wi-Fi. Does the time or loss change?</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Ping tests reachability, delay and packet loss.</li>
<li>Test from near to far: loopback, own IP, gateway, public IP, name.</li>
<li>Traceroute lists each router on the path using the TTL trick.</li>
<li>Stars or blocked ping are clues, not proof of failure.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does a computer do with a packet whose destination is on a different network?
- Broadcasts it to everyone
* Sends it to the default gateway
- Drops it
- Sends it to the DNS server
= Traffic for other networks goes to the default gateway, the router, which forwards it.

Q: What is the main job of NAT in a home or office router?
- To speed up downloads
* To let many private addresses share one public address
- To give out IP addresses
- To translate names into addresses
= NAT translates private addresses to the public one, keeping track of conversations using ports.

Q: A printer on the lab network should always keep the same address. What is the best method?
- Let it pick a new address each day
- Use a public address
* A DHCP reservation or a static address outside the DHCP range
- Turn off the firewall
= A fixed address means computers always find it where they expect.

Q: Which port does HTTPS normally use?
- 80
- 22
* 443
- 53
= HTTPS uses 443. HTTP is 80, SSH is 22 and DNS is 53.

Q: What is the safest basic idea for firewall rules?
- Allow everything and block known bad traffic
* Block everything by default and allow only the needed services
- Turn the firewall off at night
- Open all ports above 1024
= Default deny keeps the number of exposed services as small as possible.

Q: You open a website and see "server not found". Which step in the journey most likely failed?
- The TLS handshake
- The TCP handshake
* DNS lookup
- ARP for the gateway
= "Server not found" usually means the name could not be turned into an address.

Q: In your ping ladder, you can ping your gateway and 8.8.8.8, but not example.com. What is the likely fault?
- The cable
- The network card
- The gateway
* DNS
= Reaching a public address by number proves the connection works, so only name resolution is failing.

Q: What does a row of stars (*) in a traceroute output mean?
- The connection is cut for certain
- Your computer has a virus
* That hop did not reply, which is often harmless
- The packet was encrypted
= Some routers do not answer traceroute packets. What matters is where later hops stop replying.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Wi-Fi and building a network',
            'summary' => 'Wi-Fi standards, bands and channels, access point placement, security, small networks, cabling and mobile data.',
            'lessons' => [
                [
                    'title'   => 'Wi-Fi standards, bands and channels',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to compare Wi-Fi 4, 5 and 6, explain 2.4 GHz and 5 GHz bands, and choose sensible channels. This explains why Wi-Fi is sometimes fast and sometimes slow, and how to improve it for a lab or classroom.</p>
<h3>Wi-Fi standards in plain words</h3>
<p>Wi-Fi follows the IEEE 802.11 family of standards. The Wi-Fi Alliance now uses simple numbers:</p>
<table>
<thead><tr><th>Name</th><th>Technical name</th><th>Bands</th><th>Rough idea</th></tr></thead>
<tbody>
<tr><td>Wi-Fi 4</td><td>802.11n</td><td>2.4 and 5 GHz</td><td>Older but still common; fine for basic browsing</td></tr>
<tr><td>Wi-Fi 5</td><td>802.11ac</td><td>5 GHz</td><td>Fast; good for video and many devices</td></tr>
<tr><td>Wi-Fi 6</td><td>802.11ax</td><td>2.4 and 5 GHz</td><td>Handles many devices at once better; more efficient</td></tr>
<tr><td>Wi-Fi 6E</td><td>802.11ax</td><td>Adds the 6 GHz band</td><td>Extra, less crowded channels (needs compatible devices)</td></tr>
<tr><td>Wi-Fi 7</td><td>802.11be</td><td>2.4, 5 and 6 GHz</td><td>Newest; best with new devices</td></tr>
</tbody>
</table>
<p>Speed figures printed on boxes (such as "AX3000") are theoretical totals that add several bands together. Real speeds are much lower and are shared among all devices. A connection is only as fast as the weakest part: an old phone will use the older standard, and a cheap 100 Mbps wired port limits everything behind it. Newer standards are backward compatible, so an old device still connects but slows the network somewhat.</p>
<h3>Bands: 2.4 GHz and 5 GHz</h3>
<table>
<thead><tr><th></th><th>2.4 GHz</th><th>5 GHz</th></tr></thead>
<tbody>
<tr><td>Range</td><td>Longer; goes through walls better</td><td>Shorter; walls reduce it more</td></tr>
<tr><td>Speed</td><td>Lower</td><td>Higher</td></tr>
<tr><td>Crowding</td><td>Very crowded: neighbours, Bluetooth, microwaves</td><td>More channels, less crowded</td></tr>
</tbody>
</table>
<p>A good rule: use 5 GHz when you are in the same room or close, and 2.4 GHz for far away or simple devices such as smart plugs. Many routers combine both under one name and decide automatically (<em>band steering</em>); you can also give each band its own name if some devices behave badly.</p>
<h3>Channels</h3>
<p>Each band is split into <strong>channels</strong>, like lanes. Access points close together on the same channel must share the air and slow each other down. In the 2.4 GHz band in most countries only channels <strong>1, 6 and 11</strong> do not overlap, so choose one of these three, and give neighbouring access points different ones. The 5 GHz band has many more separate channels, and wider channels (40, 80 or 160 MHz) give more speed but have more chance of interference, so 20 or 40 MHz is wise in crowded places on 2.4 GHz, and 80 MHz is common on 5 GHz.</p>
<p>Use a free Wi-Fi analyser app on a phone to see nearby networks and their channels, and pick the quietest one. Several access points on the same network should have the same network name (SSID) and security settings so that devices can move between them.</p>
<h3>Try it yourself</h3>
<ol>
<li>Find out which Wi-Fi standard your router supports (look at the label or the settings page).</li>
<li>Use a Wi-Fi analyser app (or the router's settings) to see which channels the networks around you use.</li>
<li>Test speed with a speed-test website once near the router and once two rooms away, on 5 GHz and then on 2.4 GHz if both are available. Record the four numbers.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Wi-Fi 4, 5 and 6 are friendly names for 802.11n, ac and ax; newer generally means more efficient.</li>
<li>2.4 GHz has more range; 5 GHz has more speed and less crowding.</li>
<li>In 2.4 GHz use channels 1, 6 or 11; space neighbouring access points apart.</li>
<li>Box speeds are theoretical; real speed is shared and lower.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Access points, placement and Wi-Fi security',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to place access points sensibly and secure a wireless network with WPA2 or WPA3, a strong password and a guest network. Weak Wi-Fi security is one of the easiest ways for outsiders to enter a network, so this matters as much as speed.</p>
<h3>Placing access points</h3>
<ol>
<li><strong>Central and high.</strong> Put the access point in the middle of the area it serves, ideally on a ceiling or high on a wall, not on the floor or inside a cupboard.</li>
<li><strong>Away from obstacles.</strong> Thick concrete walls, metal shelves, mirrors, water tanks and fridges weaken the signal. Brick walls cost more signal than wooden ones.</li>
<li><strong>Away from interference.</strong> Keep it away from microwaves, cordless phones and piles of other equipment.</li>
<li><strong>One per area.</strong> One access point per classroom or large room is often better than one strong router for the whole building, and each should be connected by cable, not just wirelessly.</li>
<li><strong>Plan the channels.</strong> Neighbouring access points use different channels, with a small overlap of coverage so users can move without dropping off.</li>
<li><strong>Do not rely on "more power".</strong> Extra transmit power helps your phone hear the router, but the router may not hear your phone, so coverage is a two-way matter.</li>
</ol>
<p>Repeaters and extenders can fill gaps, but they halve the speed when they use the same radio to receive and to send. A wired access point or a <em>mesh</em> system with a wired link is better. Test by walking with a phone, watching the signal strength and running speed tests in each corner.</p>
<h3>Wi-Fi security</h3>
<table>
<thead><tr><th>Setting</th><th>Verdict</th></tr></thead>
<tbody>
<tr><td>Open network (no password)</td><td>Anyone can join and may read unencrypted traffic; avoid except for a deliberate public hotspot</td></tr>
<tr><td>WEP</td><td>Broken for many years; never use it</td></tr>
<tr><td>WPA (first version)</td><td>Old and weak; avoid</td></tr>
<tr><td>WPA2 (with AES)</td><td>Good when the password is strong; works on almost every device</td></tr>
<tr><td>WPA3</td><td>Best choice when all devices support it; stronger protection against password guessing</td></tr>
</tbody>
</table>
<p>If some old devices cannot do WPA3, use the "WPA2/WPA3" mixed mode, or WPA2 with AES. Choose <strong>WPA2-Personal / WPA3-Personal</strong> at home or in a small office (one shared password), and <strong>Enterprise</strong> modes (a login for each person, checked by a server) for bigger organisations.</p>
<h3>Strong Wi-Fi settings</h3>
<ul>
<li>Use a long password or passphrase of 12 or more characters, such as four unrelated words with a number. Never keep the default one printed on the router.</li>
<li>Change the router's <strong>admin</strong> password from the default; this is separate from the Wi-Fi password.</li>
<li>Turn off <strong>WPS</strong> (the push-button or PIN setup), which has known weaknesses.</li>
<li>Hiding the network name (SSID) is not real security; do not rely on it.</li>
<li>Change the Wi-Fi password when a person who knew it leaves the organisation.</li>
</ul>
<h3>Guest networks</h3>
<p>A <strong>guest network</strong> is a separate Wi-Fi name with its own password that reaches the internet but not your computers, printers and files. Use it for visitors, for students' own phones and for smart gadgets. Switch on "client isolation" so that guests cannot see each other either, and consider a bandwidth limit so that guests do not use up the connection.</p>
<h3>Try it yourself</h3>
<ol>
<li>Log in to your router's page (the address is usually the default gateway from <code>ipconfig</code>), if you are allowed to. Find the Wi-Fi security mode.</li>
<li>Check whether WPS is enabled and whether the admin password is still the default. Do not change anything on a network you do not own.</li>
<li>Invent a strong passphrase of four words and a number, and write why it is stronger than a name and a birth year.</li>
<li>Sketch a floor plan of a school building and mark where you would place three access points.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Place access points high, central, wired and away from obstacles.</li>
<li>Use WPA3 if possible, otherwise WPA2 with AES; never WEP or open networks for private data.</li>
<li>Long passphrase, a changed admin password and WPS off.</li>
<li>Use a guest network for visitors and untrusted devices.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Setting up a small office or school network',
                    'minutes' => 18,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to plan the parts of a small network, connect them in the right order and configure the basic settings. The same steps work for a small office, a shop or a computer lab with up to a few dozen devices.</p>
<h3>Step 1: understand the need</h3>
<p>Write down how many wired computers, how many Wi-Fi devices, what is shared (printer, files), what the internet is used for (browsing, video classes, cloud tools) and how many people are online at once. Plan for growth by adding about 30 percent to the number of ports and addresses you count today.</p>
<h3>Step 2: choose the parts</h3>
<ul>
<li><strong>Internet connection</strong> from an ISP, with a modem or ONT.</li>
<li><strong>Router with a firewall</strong>, which provides NAT and DHCP.</li>
<li><strong>Switch</strong> with enough gigabit ports (plus spares), PoE if you will power access points over cable.</li>
<li><strong>Access point(s)</strong> for Wi-Fi, wired back to the switch.</li>
<li><strong>Cables</strong> (Cat5e or Cat6), patch leads, and a labelled place to keep equipment off the floor, ventilated and protected from dust and power surges (use a surge protector or UPS).</li>
</ul>
<h3>Step 3: connect in order</h3>
<ol>
<li>Modem or ONT to the router's <strong>WAN</strong> (internet) port.</li>
<li>Router's <strong>LAN</strong> port to the switch.</li>
<li>Computers and the printer to switch ports with patch cables.</li>
<li>Access points to switch ports (PoE or with their own power).</li>
<li>Power on from the modem outward: modem, router, switch, access points.</li>
</ol>
<h3>Step 4: configure the router</h3>
<ol>
<li>Log in with the administrator address from the device's label. Change the admin password at once.</li>
<li>Check that the internet connection (WAN) is up. Your ISP may need PPPoE details or may use DHCP automatically.</li>
<li>Set the LAN address range, for example 192.168.10.0/24 with the router at 192.168.10.1.</li>
<li>Set the DHCP range to leave room for fixed devices, for example give out .100 to .200, and keep .2 to .99 for printers, servers and access points (static or reserved).</li>
<li>Set up Wi-Fi: name, WPA2/WPA3 and a strong password, and a separate guest network.</li>
<li>Update the firmware, set the right time zone, and save a copy of the settings.</li>
</ol>
<h3>Step 5: test and record</h3>
<ul>
<li>Check each computer gets an address in the right range and can ping the gateway and a public address.</li>
<li>Open a website and run a speed test from a wired and a Wi-Fi device.</li>
<li>Print a test page from two computers.</li>
<li>Record addresses, device names, passwords (stored safely), and a simple diagram.</li>
</ul>
<h3>Sharing files and printers</h3>
<p>Use a trusted file server, a network drive or a cloud folder for shared files, with separate accounts per person rather than one shared login. For printers, give a fixed address or reservation, and install the correct driver on each computer. On Windows, set the network to <strong>Private</strong> only on trusted networks, and keep it on <strong>Public</strong> otherwise so that sharing is off.</p>
<h3>Try it yourself</h3>
<ol>
<li>Draw a network diagram of your home or school: internet, router, switch, access points and devices.</li>
<li>Write an address plan: LAN range, router address, DHCP range and list of fixed addresses.</li>
<li>List the equipment and approximate costs of a network for 10 computers, a printer and Wi-Fi.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Plan first: devices, sharing, internet use and growth.</li>
<li>Connect modem to router to switch to devices, and power on in that order.</li>
<li>Secure the router: new admin password, WPA2 or WPA3, guest network, updated firmware.</li>
<li>Test everything and document what you built.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Cabling basics and mobile data',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose network cable, understand how a cable is terminated, and use mobile data and hotspots wisely. Cables fail more often than people expect, and mobile data is the main connection for many people in East Africa, so both are worth understanding well.</p>
<h3>Twisted-pair cable</h3>
<p>Most network cable has eight copper wires twisted in four pairs, which reduces interference. It ends in an <strong>RJ45</strong> plug. The maximum length of one run is <strong>100 metres</strong> (including patch leads). Longer needs a switch or fibre.</p>
<table>
<thead><tr><th>Category</th><th>Typical use</th></tr></thead>
<tbody>
<tr><td>Cat5e</td><td>Up to 1 Gbps; fine for most offices and labs</td></tr>
<tr><td>Cat6</td><td>1 Gbps to 100 m; 10 Gbps over short runs (up to about 55 m); better against interference</td></tr>
<tr><td>Cat6a</td><td>10 Gbps to 100 m; thicker and more expensive</td></tr>
</tbody>
</table>
<p>For a new installation, Cat6 is a sensible choice. Use <strong>solid copper</strong> cable for fixed runs inside walls and trunking, and stranded patch leads for the short flexible connections. Avoid "CCA" (copper-clad aluminium) cable, which is cheap but breaks easily and does not meet the standards; look for pure copper. Keep network cables away from mains power cables, since crossing them at right angles is better than running them side by side.</p>
<h3>Fibre optic</h3>
<p>Fibre sends light in glass and is used for long distances, between buildings and for ISPs. It is not affected by electrical interference. It needs special connectors and equipment, so it is usually installed by specialists.</p>
<h3>Making a cable: the crimping idea</h3>
<p>To make a patch cable you need cable, RJ45 plugs, a crimping tool and a cable tester. Read this as the idea, and practise on spare cable first.</p>
<ol>
<li>Strip about 2.5 cm of the outer jacket, without nicking the wires inside.</li>
<li>Untwist the pairs and straighten the wires.</li>
<li>Arrange the eight wires in the <strong>T568B</strong> order: white-orange, orange, white-green, blue, white-blue, green, white-brown, brown. Use the same standard at both ends for a normal (straight-through) cable.</li>
<li>Cut the wires level, push them fully into the plug (you can see the copper tips at the end) and crimp firmly.</li>
<li>Test with a cable tester. All eight lights should go in order.</li>
</ol>
<p>Many modern devices detect the cable type automatically, so a "crossover" cable is rarely needed today. When a lot of cabling is needed, buy ready-made leads or use a trained installer; badly made cables are a common source of slow or dropping connections.</p>
<h3>Mobile data and hotspots</h3>
<ul>
<li><strong>Mobile data</strong> (3G, 4G, 5G) uses the mobile network. Speeds change with signal and with how busy the tower is. It is paid for per bundle, so large updates and video can use up the bundle quickly.</li>
<li>A <strong>hotspot</strong> (tethering) turns your phone into a Wi-Fi access point so that a laptop can share its mobile data. On Android go to <strong>Settings</strong>, then <strong>Network and internet</strong>, then <strong>Hotspot and tethering</strong>; on iPhone, <strong>Settings</strong>, then <strong>Personal Hotspot</strong>.</li>
<li>Give the hotspot a strong password, and turn it off when you finish: it drains the battery and allows others to use up your bundle.</li>
<li>Set a data limit and switch off automatic updates on a hotspot connection ("metered connection" in Windows).</li>
<li>A small <strong>4G or 5G router</strong> with a SIM card can serve as the main or backup connection for a small office when there is no fibre.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Look at a network cable at your centre and read the printing on its jacket (Cat5e or Cat6). Measure the longest cable run in the lab.</li>
<li>Look inside an RJ45 plug and name the wire colours in the T568B order from memory.</li>
<li>Turn on your phone's hotspot with a strong password, connect a laptop, run a speed test, then turn the hotspot off.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Twisted-pair runs are limited to 100 m; Cat5e and Cat6 are the common choices.</li>
<li>Use pure copper cable and the same T568B order at both ends of a normal cable.</li>
<li>Test every cable you make.</li>
<li>Hotspots share mobile data: secure them, watch the bundle and switch them off.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which Wi-Fi name matches the 802.11ax standard?
- Wi-Fi 4
- Wi-Fi 5
* Wi-Fi 6
- Wi-Fi 3
= Wi-Fi 4 is 802.11n, Wi-Fi 5 is 802.11ac and Wi-Fi 6 is 802.11ax.

Q: You need Wi-Fi to reach a room at the far end of a building through two thick walls. Which band usually helps most?
* 2.4 GHz
- 5 GHz
- Neither, bands make no difference
- 60 GHz
= 2.4 GHz travels farther and through walls better, though at lower speed.

Q: In the 2.4 GHz band, which set of channels does not overlap?
- 1, 2 and 3
- 3, 5 and 7
* 1, 6 and 11
- 2, 4 and 8
= Channels 1, 6 and 11 are spaced far enough apart not to overlap in most countries.

Q: What is the best place for an access point in a classroom?
- On the floor behind the teacher's desk
- Inside a metal cupboard
* High on a wall or ceiling, in the room, connected by cable
- Next to the microwave
= High and central with a cable link gives the best coverage and speed.

Q: Which Wi-Fi security choice is best when all devices support it?
- WEP
- Open network
- WPA
* WPA3
= WPA3 gives the strongest protection. WEP and the first WPA are weak and must be avoided.

Q: Which are good Wi-Fi security practices? (choose all that apply)
* Change the router's admin password
* Turn off WPS
- Hide the SSID and rely on that for safety
- Keep the default password printed on the router
= Admin password changes and turning off WPS are useful; hiding the name is not real security and default passwords are widely known.

Q: What is the maximum length of one twisted-pair Ethernet run?
- 10 metres
- 55 metres
* 100 metres
- 500 metres
= Twisted-pair Ethernet is limited to 100 metres, including patch leads.

Q: Why use a guest network?
- It makes the internet faster for everyone
* Visitors can use the internet without reaching your computers and printers
- It removes the need for a firewall
- It lets guests change router settings
= A guest network is separated from the main network, protecting internal devices.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Troubleshooting and security',
            'summary' => 'A troubleshooting method, diagnostic tools, common faults, basic security, documentation and a lab design task.',
            'lessons' => [
                [
                    'title'   => 'A troubleshooting method and the diagnostic tools',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to follow a clear troubleshooting method and use ipconfig, ip, nslookup and related tools to find out what is wrong. A calm method beats random clicking: it finds the cause faster and avoids making things worse.</p>
<h3>A six-step method</h3>
<ol>
<li><strong>Identify the problem.</strong> Ask what exactly is wrong, for whom, since when, and what changed recently (a new router, an update, a moved cable). Try to reproduce it.</li>
<li><strong>Guess the cause.</strong> Start with the simplest and most likely: cable, power, Wi-Fi switch, wrong password.</li>
<li><strong>Test the guess.</strong> Use tools and check one thing at a time. If you are wrong, make another guess.</li>
<li><strong>Make a plan and fix it.</strong> Think about who will be affected, and take a backup of settings before changing them.</li>
<li><strong>Check the result.</strong> Verify that the problem is gone and that nothing else broke.</li>
<li><strong>Write it down.</strong> Record the symptoms, cause and fix, so next time is quicker.</li>
</ol>
<p>Work from the bottom of the OSI model up, or divide the path in half and test the middle (the ping ladder). Ask whether the fault affects one device (look at that device), all devices on Wi-Fi (the access point), or everyone (the router or ISP).</p>
<h3>ipconfig and ip</h3>
<pre><code>ipconfig                  (Windows: addresses, mask, gateway)
ipconfig /all             (adds MAC, DHCP server, DNS servers, lease times)
ipconfig /release         (give up the current DHCP address)
ipconfig /renew           (ask for a new one)
ipconfig /flushdns        (clear the DNS cache)

ip addr                   (Linux: addresses)
ip route                  (Linux: routes and gateway)
ifconfig                  (older Linux and macOS tool, similar to ip addr)</code></pre>
<p>Read the output carefully. Questions to ask: is the address in the expected range? Is the mask right? Is the gateway the router's address? Are the DNS servers set? An address starting with 169.254 means DHCP failed. An address in a different range from the others means the device is on another network (the wrong Wi-Fi, or a second router).</p>
<h3>nslookup</h3>
<pre><code>nslookup example.com              (ask your default DNS server)
nslookup example.com 8.8.8.8      (ask a specific public server instead)</code></pre>
<p>If the name works with 8.8.8.8 but not with your default server, the problem is your DNS server. On Linux, <code>dig example.com</code> gives more detail where installed.</p>
<h3>Other helpful tools</h3>
<table>
<thead><tr><th>Tool</th><th>Use</th></tr></thead>
<tbody>
<tr><td><code>ping</code>, <code>tracert</code> / <code>traceroute</code></td><td>Reachability and the path</td></tr>
<tr><td><code>arp -a</code></td><td>IP to MAC table: finds duplicates and checks the gateway</td></tr>
<tr><td><code>netstat -an</code></td><td>Open ports and connections</td></tr>
<tr><td><code>netsh wlan show interfaces</code></td><td>Windows Wi-Fi details: signal, channel, speed</td></tr>
<tr><td>Cable tester</td><td>Checks cables for breaks and wrong wiring</td></tr>
<tr><td>Speed test</td><td>Real speed to the internet</td></tr>
</tbody>
</table>
<h3>Try it yourself</h3>
<ol>
<li>Run <code>ipconfig /all</code> and write down address, mask, gateway, DHCP server and DNS servers.</li>
<li>Run <code>nslookup example.com</code> then <code>nslookup example.com 8.8.8.8</code>. Compare the answers.</li>
<li>Run <code>ipconfig /flushdns</code> and note the message. (Safe to do; it only clears a temporary list.)</li>
<li>Practise the method on an imaginary problem: "the lab printer has stopped working". Write the six steps with your guesses.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Identify, guess, test, fix, verify, document.</li>
<li>Find the scope: one device, one area or everyone.</li>
<li>ipconfig or ip shows the settings; nslookup tests DNS; ping and traceroute test the path.</li>
<li>Change one thing at a time and write down what you did.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Common faults and how to fix them',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to recognise the most common network faults from their symptoms and apply the right fix. Most network problems have simple causes, and a technician who knows the usual ones solves them quickly.</p>
<h3>Symptom table</h3>
<table>
<thead><tr><th>Symptom</th><th>Likely cause</th><th>First fix to try</th></tr></thead>
<tbody>
<tr><td>No link light; "network cable unplugged"</td><td>Loose, damaged or wrong cable; dead switch port</td><td>Reseat both ends, try another cable and another port</td></tr>
<tr><td>Connected to Wi-Fi, "no internet"</td><td>Router lost its internet, wrong DNS, or the ISP is down</td><td>Ping the gateway, then 8.8.8.8; restart the router; call the ISP</td></tr>
<tr><td>Address 169.254.x.x</td><td>No DHCP answer</td><td>Check the DHCP server is on; <code>ipconfig /renew</code>; try another cable</td></tr>
<tr><td>"IP address conflict"</td><td>Two devices have the same address (often a static address inside the DHCP range)</td><td>Move static addresses outside the DHCP range; renew</td></tr>
<tr><td>Websites fail by name but ping by number works</td><td>DNS problem</td><td>Change DNS servers; <code>ipconfig /flushdns</code></td></tr>
<tr><td>Slow Wi-Fi</td><td>Weak signal, crowded channel, old devices, too many users</td><td>Move closer, change channel or band, add an access point</td></tr>
<tr><td>Wi-Fi drops out in some rooms</td><td>Walls, distance or interference</td><td>Move or add an access point, use a wired access point</td></tr>
<tr><td>Can reach the internet but not the printer or file server</td><td>Different network or subnet, firewall, or the device changed address</td><td>Check addresses and masks; use a reservation</td></tr>
<tr><td>Internet very slow for everyone</td><td>A device downloading heavily, a large update, or a faulty link</td><td>Check which device uses the bandwidth; set limits; test the speed at the router</td></tr>
<tr><td>Two routers and strange problems</td><td>Two DHCP servers or double NAT</td><td>Turn off DHCP on the second device or use it as an access point</td></tr>
</tbody>
</table>
<h3>The restart rule</h3>
<p>Restarting a router or computer clears many temporary faults. The proper order is to restart from the modem toward the user: unplug the modem and the router, wait 30 seconds, plug in the modem, wait until its lights are steady, then the router, and then check again. Do not use restarts to avoid finding the cause when a problem keeps returning: note when it happens and look for a pattern.</p>
<h3>Wi-Fi-specific checks</h3>
<ul>
<li>Is the device connected to the right network, with the right password?</li>
<li>Is Airplane mode off and Wi-Fi switched on?</li>
<li>Does another device work in the same place? If yes, the fault is in the first device (try "forget this network" and reconnect, or update the Wi-Fi driver).</li>
<li>Does the problem appear only at certain times? A busy time or an appliance like a microwave may be the cause.</li>
</ul>
<h3>Wired checks</h3>
<ul>
<li>Look at the link lights on the computer and the switch.</li>
<li>Swap the patch lead and then try another wall socket or switch port.</li>
<li>If a cable is longer than 100 m, or runs beside power cables, expect trouble.</li>
<li>Use a cable tester on wall sockets that you suspect.</li>
</ul>
<h3>Common mistakes</h3>
<ul>
<li>Changing several things at once, then not knowing what fixed it.</li>
<li>Resetting a router to factory settings without saving the configuration.</li>
<li>Blaming the ISP before checking the local equipment.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Pick three symptoms from the table and write the exact commands you would run for each.</li>
<li>Ask a friend to unplug a cable or switch Wi-Fi off on a practice computer without telling you which. Use the method to find it.</li>
<li>Find out where your router saves a backup of its settings, and make one if you are allowed to.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Most faults are cables, power, DHCP, DNS or Wi-Fi signal.</li>
<li>Use ping by number then by name to separate connection from DNS problems.</li>
<li>Restart in order, modem first, but still look for the cause.</li>
<li>Change one thing at a time and back up settings first.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Basic network security and documentation',
                    'minutes' => 16,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to apply basic security to a small network, explain VLANs and VPNs in simple terms, and document a network so others can manage it. A network that is secure and well recorded is far easier to run, and survives staff changes.</p>
<h3>Basic security practices</h3>
<ol>
<li><strong>Strong passwords everywhere.</strong> A long, unique Wi-Fi passphrase, a changed router admin password, and different passwords for different devices. A password manager helps.</li>
<li><strong>Keep firmware and software updated.</strong> Routers, access points and switches have software called firmware. Updates fix security holes, so check for them regularly and turn on automatic updates where offered.</li>
<li><strong>Use the firewall.</strong> Keep the router's firewall on and the computers' firewalls on. Open only needed ports.</li>
<li><strong>Disable what you do not use.</strong> Turn off WPS, remote administration from the internet, and unused services.</li>
<li><strong>Use WPA2 or WPA3</strong> and a guest network for visitors.</li>
<li><strong>Control who joins.</strong> Know which devices are on the network (check the router's list of clients) and remove ones you do not recognise after changing the password.</li>
<li><strong>Protect the equipment physically.</strong> Switches and routers in a locked cupboard or room, because anyone with physical access can plug in or reset them.</li>
<li><strong>Back up</strong> router and switch settings and important data.</li>
</ol>
<h3>VLANs: separate networks on one switch</h3>
<p>A <strong>VLAN</strong> (virtual LAN) divides one physical switch into several separate logical networks. Computers in different VLANs cannot talk to each other directly, even when plugged into the same switch, unless a router allows it. A school might put staff computers, student computers, and the office server in three VLANs, each with its own subnet. This limits damage when one part has a virus, and makes it easy to give each group its own rules. VLANs need a <em>managed</em> switch, and traffic between VLANs goes through a router or firewall, where it can be controlled.</p>
<h3>VPNs: a protected tunnel</h3>
<p>A <strong>VPN</strong> (virtual private network) creates an encrypted tunnel across an untrusted network such as the internet or public Wi-Fi. Two common uses are:</p>
<ul>
<li><strong>Remote access:</strong> a staff member at home connects through a VPN to reach the office network as if they were there. This is much safer than opening Remote Desktop to the internet.</li>
<li><strong>Privacy on public Wi-Fi:</strong> a VPN service hides your traffic from others on the same hotspot and from the hotspot owner. It does not make you anonymous, and you must trust the VPN provider, so choose carefully and be wary of free ones.</li>
</ul>
<h3>Public Wi-Fi habits</h3>
<ul>
<li>Prefer sites that use HTTPS (the padlock), but a padlock does not mean the site is honest.</li>
<li>Avoid banking and mobile money logins on open Wi-Fi if you can use mobile data instead.</li>
<li>Turn off file sharing and automatic connection to open networks.</li>
</ul>
<h3>Documenting a network</h3>
<p>Keep a simple network record, in a folder that the responsible people can find, and update it whenever you change something. It should contain:</p>
<table>
<thead><tr><th>Item</th><th>Details</th></tr></thead>
<tbody>
<tr><td>Diagram</td><td>Internet, router, switches, access points, servers, and how they connect</td></tr>
<tr><td>Address plan</td><td>Subnets, router address, DHCP range, fixed addresses and reservations</td></tr>
<tr><td>Device list</td><td>Name, make and model, location, MAC address, serial number, warranty</td></tr>
<tr><td>Accounts</td><td>Who has admin access (store passwords in a secure password manager or sealed envelope, not on sticky notes)</td></tr>
<tr><td>Provider information</td><td>ISP name, account number, support phone, type of line</td></tr>
<tr><td>Change log</td><td>Date, what changed, who did it, why</td></tr>
<tr><td>Cable labels</td><td>Matching labels at both ends, linked to switch ports and wall sockets</td></tr>
</tbody>
</table>
<h3>Try it yourself</h3>
<ol>
<li>Check the router client list (if you may) and name each device. Which ones are unknown?</li>
<li>Look up the current firmware version of your router and find out whether a newer one exists from the maker's official site.</li>
<li>Create a one-page document with a diagram and address plan for your own network.</li>
<li>Explain in two sentences to a friend what a VLAN does and what a VPN does.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Strong passwords, updates, a firewall, WPA2 or WPA3 and a guest network are the basics.</li>
<li>VLANs separate groups on one switch; VPNs protect traffic over untrusted networks.</li>
<li>Protect equipment physically and back up settings.</li>
<li>Document diagrams, addresses, devices and changes.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Capstone: design a small computer lab network',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>In this capstone you will design a network for a small school computer lab on paper, using everything in the course. After it you will have a design you can show in an interview, or use as a starting point for a real project.</p>
<h3>The brief</h3>
<p>A community school in a town is opening a lab in one classroom with these needs:</p>
<ul>
<li>30 student computers, 1 teacher computer, 1 printer and 1 small file server.</li>
<li>Wi-Fi for the teacher's laptop and tablets in the lab and the office next door, plus a separate network for visitors.</li>
<li>One fibre internet connection of 50 Mbps shared by everyone.</li>
<li>The school office (10 computers) must be kept separate from the student lab.</li>
<li>A small budget, power cuts happen, and the equipment must be safe from theft and dust.</li>
</ul>
<h3>Step 1: count and size the subnets</h3>
<p>Use the block 192.168.30.0/24 and share it with VLSM, largest first.</p>
<table>
<thead><tr><th>Network</th><th>Devices needed</th><th>Subnet chosen</th><th>Range and broadcast</th></tr></thead>
<tbody>
<tr><td>Student lab</td><td>30 PCs + teacher PC + spare = about 40</td><td>192.168.30.0/26 (62 hosts)</td><td>.1 to .62, broadcast .63</td></tr>
<tr><td>Office</td><td>10 PCs + printer + growth = about 20</td><td>192.168.30.64/27 (30 hosts)</td><td>.65 to .94, broadcast .95</td></tr>
<tr><td>Servers and printers</td><td>Under 10</td><td>192.168.30.96/28 (14 hosts)</td><td>.97 to .110, broadcast .111</td></tr>
<tr><td>Guest Wi-Fi</td><td>Up to 25</td><td>192.168.30.128/27 (30 hosts)</td><td>.129 to .158, broadcast .159</td></tr>
</tbody>
</table>
<p>Check: the blocks (0 to 63, 64 to 95, 96 to 111, 128 to 159) do not overlap, and addresses 112 to 127 and 160 to 255 are left free for growth.</p>
<h3>Step 2: choose the equipment</h3>
<ul>
<li>A firewall-capable router, with the fibre ONT connected to its WAN port.</li>
<li>A managed gigabit switch with at least 36 ports (30 + teacher + printer + server + 2 access points + spares), or two smaller switches linked together. Add PoE if the access points are powered through it.</li>
<li>Two access points, one in the lab and one in the office, wired to the switch, with a main and a guest network name.</li>
<li>Cat6 cabling in trunking from each desk to a patch panel, labelled at both ends.</li>
<li>A UPS to keep the router, switch and server running through short power cuts, and surge protection for the lab.</li>
<li>A wall rack or lockable cabinet for the equipment.</li>
</ul>
<h3>Step 3: plan the settings</h3>
<ol>
<li>Create a VLAN for each subnet on the switch, and let the router or firewall route between them with rules (students may reach the internet and the printer, but not the office network).</li>
<li>Router at the first address of each subnet, for example 192.168.30.1 for the lab VLAN. Use DHCP for the lab and the office, with reservations for the printer and server.</li>
<li>Use WPA2 or WPA3 with a strong passphrase on the staff network and a different one on guest Wi-Fi with client isolation.</li>
<li>Limit bandwidth for guests and for the student VLAN, so that the office keeps a fair share of the 50 Mbps.</li>
<li>Choose different channels for the two access points (for example 1 and 11 on 2.4 GHz), and use 5 GHz where possible.</li>
<li>Change all default passwords, update firmware, back up the configuration.</li>
</ol>
<h3>Step 4: deliverables</h3>
<ul>
<li>A network diagram with all devices and cables.</li>
<li>An address plan (the table above, with the final addresses).</li>
<li>A parts list with quantities.</li>
<li>A one-page security checklist and a plan for backups and who is responsible.</li>
<li>A test plan: addresses, ping to gateway and internet, speed test, print test, guest network isolation test.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Draw the diagram and complete the address plan for the lab described.</li>
<li>Change one requirement (for example 60 computers instead of 30) and redo the subnet sizing. (The lab then needs about 70 hosts, which needs a /25 with 126 hosts.)</li>
<li>Write your top five risks, such as power failure or theft, and a defence for each.</li>
<li>Present your design to a friend in three minutes and note the questions they ask.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Design starts from needs: devices, separation, internet use, budget and risks.</li>
<li>Use VLSM, largest block first, check for overlaps and leave room to grow.</li>
<li>Separate groups with VLANs and a guest network; protect the equipment physically and with power backup.</li>
<li>Deliver a diagram, address plan, parts list, security checklist and test plan.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which is the best first step in troubleshooting a network problem?
- Reset the router to factory settings
- Replace all cables
* Identify exactly what is wrong, for whom, and what changed recently
- Call the ISP
= Understanding the problem and its scope prevents wasted effort and avoids making things worse.

Q: Only one laptop cannot get online while phones and other computers in the same room work. Where do you look first?
- The ISP
- The router's firmware
* That laptop's settings, Wi-Fi connection and address
- The fibre cable
= If others work, the shared equipment and the internet are fine, so the fault is probably in the one laptop.

Q: A computer reports "IP address conflict". What is a likely cause?
- The cable is too long
* Two devices use the same address, for example a static address inside the DHCP range
- The DNS server is slow
- The Wi-Fi channel is crowded
= Static addresses should be outside the DHCP range, or each fixed device should use a reservation.

Q: Which command clears the DNS cache on Windows?
- ipconfig /renew
- ipconfig /release
* ipconfig /flushdns
- netstat -an
= flushdns empties the list of remembered name-to-address answers.

Q: Why is a VLAN useful in a school?
- It makes Wi-Fi faster
- It replaces the need for a router
* It separates groups, such as students and staff, on the same switch
- It gives free internet
= VLANs make separate logical networks on shared hardware, which limits the spread of problems.

Q: What is a safe way for staff to reach the office network from home?
- Open Remote Desktop port 3389 to the internet
- Share the admin password by message
* Connect through a VPN
- Turn off the office firewall
= A VPN gives an encrypted, controlled path instead of exposing services directly to the internet.

Q: Which items belong in a good network record? (choose all that apply)
* Network diagram
* Address plan with DHCP range and fixed addresses
* A log of changes
- Passwords written on a sticky note by the router
= Diagrams, address plans and change logs make a network manageable. Passwords belong in a secure password manager, not on a note.

Q: A router needs a firmware update. Why is this important?
- It changes the colour of the lights
* Updates fix known security weaknesses and bugs
- It increases the number of ports
- It removes the need for a password
= Outdated firmware is a common way for attackers to get in.
QUIZ,
        ],
    ],

    'final_exam' => <<<'QUIZ'
Q: What is the difference between bandwidth and latency?
- They mean the same thing
* Bandwidth is how much data per second a link carries; latency is the delay for one packet
- Bandwidth is the delay; latency is the capacity
- Latency is only about Wi-Fi
= High bandwidth does not guarantee low delay; both matter for experience.

Q: At which OSI layer do switches and MAC addresses work?
- Layer 1
* Layer 2
- Layer 3
- Layer 4
= The data link layer handles delivery on one local network using MAC addresses.

Q: Which sequence lists OSI layers from top to bottom correctly?
- Physical, Data link, Network, Transport, Session, Presentation, Application
* Application, Presentation, Session, Transport, Network, Data link, Physical
- Application, Session, Presentation, Network, Transport, Physical, Data link
- Network, Transport, Application, Physical, Data link, Session, Presentation
= From the top, the layers are Application, Presentation, Session, Transport, Network, Data link, Physical.

Q: What is the main advantage of a switch over a hub?
- It is cheaper
- It uses less electricity
- It sends every frame to every port
* It forwards frames only to the port of the destination device
= Switches learn MAC addresses per port and avoid collisions and needless traffic.

Q: What does a protocol do?
- Stores files on a server
* Defines agreed rules so devices can communicate
- Encrypts the cable
- Provides electricity to devices
= Both sides must follow the same rules to understand each other.

Q: What does ARP do on a local network?
- Gives out IP addresses
- Blocks malicious traffic
* Finds the MAC address that goes with an IP address
- Converts names to addresses
= ARP maps IP addresses to MAC addresses on the same network.

Q: How many bits are in an IPv4 address?
- 16
- 24
* 32
- 128
= IPv4 uses 32 bits, written as four octets; IPv6 uses 128 bits.

Q: Which address is private?
- 8.8.4.4
- 172.32.0.1
* 172.31.200.9
- 200.10.10.10
= The private block 172.16.0.0 to 172.31.255.255 includes 172.31.200.9; 172.32.0.1 is outside it.

Q: The binary form of the octet 192 is:
- 10101000
* 11000000
- 11100000
- 10000001
= 128 + 64 = 192, so the first two bits are 1 and the rest 0.

Q: What is the mask for /27 in dotted decimal?
- 255.255.255.192
- 255.255.255.240
- 255.255.255.248
* 255.255.255.224
= /27 is 24 + 3 bits: 11100000 in the last octet is 224.

Q: How many usable hosts does a /28 network have?
- 16
- 12
* 14
- 30
= 2^4 = 16 addresses, minus network and broadcast leaves 14.

Q: What is the usable host range of 192.168.10.64/26?
- 192.168.10.64 to 192.168.10.127
- 192.168.10.65 to 192.168.10.127
* 192.168.10.65 to 192.168.10.126
- 192.168.10.66 to 192.168.10.128
= The block is 64 addresses (64 to 127). The first is the network address and the last is the broadcast address.

Q: What is the broadcast address of 10.1.1.130/27?
- 10.1.1.131
- 10.1.1.140
* 10.1.1.159
- 10.1.1.255
= The block size is 32, the subnet starts at 128 and ends at 159, which is the broadcast address.

Q: A department needs 50 hosts. Which is the smallest suitable subnet?
- /25
* /26
- /27
- /28
= A /26 has 62 usable hosts. A /27 has only 30.

Q: You subdivide 192.168.1.0/24 into four equal subnets. Which is the third subnet?
- 192.168.1.64/26
* 192.168.1.128/26
- 192.168.1.192/26
- 192.168.1.96/26
= The /26 subnets start at 0, 64, 128 and 192, so the third is 192.168.1.128/26.

Q: Which are valid host addresses in 192.168.1.0/26? (choose all that apply)
* 192.168.1.1
* 192.168.1.62
- 192.168.1.63
- 192.168.1.64
= .63 is the broadcast address and .64 belongs to the next subnet.

Q: What does DHCP provide to a device? (choose all that apply)
* An IP address
* A default gateway
* DNS server addresses
- A Wi-Fi password
= DHCP supplies the main network settings, but not the Wi-Fi password.

Q: A computer has the address 169.254.12.5. What should you check?
- The firewall rules
- The speed of the ISP
* Whether the DHCP server (usually the router) is working and reachable
- The monitor cable
= The 169.254 range is self-assigned when no DHCP reply is received.

Q: Which DNS record maps a name to an IPv6 address?
- A
- MX
- CNAME
* AAAA
= A is for IPv4 addresses, AAAA for IPv6, MX for mail servers and CNAME for aliases.

Q: What does the default gateway do?
- Assigns IP addresses
* Forwards traffic for other networks, usually to the internet
- Translates names to addresses
- Encrypts Wi-Fi
= Packets for destinations outside the local network are sent to the router that serves as the default gateway.

Q: Why do many devices at home appear on the internet with a single public address?
- Each device has a hidden public address
* NAT in the router translates private addresses to the public address using ports
- DHCP gives the same address to everyone
- DNS merges them
= NAT keeps track of conversations by port numbers.

Q: Which port is used by SSH?
* 22
- 25
- 80
- 3389
= SSH uses port 22; 80 is HTTP, 25 is SMTP and 3389 is Remote Desktop.

Q: What describes a stateful firewall?
- It blocks everything permanently
* It remembers conversations, so replies to your own requests are allowed back in
- It only works on Wi-Fi
- It assigns addresses
= Tracking connection state lets it allow the replies while blocking unrequested incoming traffic.

Q: In the process of opening an HTTPS website, which happens first?
- The TCP handshake
- The TLS handshake
* Finding the server's IP address with DNS (after checking caches)
- The page is displayed
= The browser needs an address before it can connect. Then come TCP, TLS, the request and the page.

Q: In a traceroute, what is the TTL trick?
- Packets are sent faster and faster
* Packets are sent with TTL 1, 2, 3 and so on, so each router reveals itself when the packet expires
- The router encrypts the packet
- The destination sends packets in reverse
= Each router lowers the TTL by one and reports the packet when it reaches zero.

Q: Which Wi-Fi standard is also called 802.11ac?
- Wi-Fi 4
* Wi-Fi 5
- Wi-Fi 6
- Wi-Fi 3
= Wi-Fi 4 is 802.11n, Wi-Fi 5 is 802.11ac, Wi-Fi 6 is 802.11ax.

Q: Which channels should you use for non-overlapping 2.4 GHz Wi-Fi?
- 1, 3 and 5
- 2, 4 and 6
* 1, 6 and 11
- 7, 8 and 9
= These three are far enough apart not to overlap.

Q: Which is the best security setting for a modern home or small office network?
- WEP
- Open with a captive page
- WPA with TKIP
* WPA3, or WPA2 with AES when devices need it
= WEP and early WPA are weak. WPA3 or WPA2-AES with a strong password is the right choice.

Q: What is the maximum recommended length of a twisted-pair Ethernet run?
- 55 m
* 100 m
- 250 m
- 1 km
= Twisted-pair Ethernet runs are limited to 100 metres.

Q: A trainee suggests turning off the firewall to fix a connection problem. What is the best response?
- Agree, because firewalls cause most problems
* Find the specific rule that blocks the traffic and fix that, rather than removing protection
- Delete all rules and leave the firewall on
- Move the computer
= Disabling the firewall removes protection. A targeted rule fixes the issue safely.
QUIZ,
];
