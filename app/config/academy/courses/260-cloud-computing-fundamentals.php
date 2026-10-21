<?php
return [
    'slug'        => 'cloud-computing-fundamentals',
    'title'       => 'Cloud Computing Fundamentals',
    'category'    => 'cloud',
    'level'       => 'intermediate',
    'icon'        => 'layers',
    'sort'        => 260,
    'summary'     => 'Understand what the cloud is, its core services, security and costs, deploy a simple website step by step, and plan a cloud move for a small organisation.',
    'description' => <<<'HTML'
<p>Almost everything you do online runs in the cloud: your e-mail, your photos, your video calls, your mobile money app. This course explains what the cloud really is, how it is built, and how organisations of every size use it, in plain language and without needing to write code.</p>
<p>You will learn the main service models and deployment types, the core building blocks (computing, storage, databases, networking and identity), how the biggest providers compare by concept, and how to keep data safe and costs under control. You will also walk through deploying a simple website, and meet containers, serverless computing and scaling.</p>
<p>Cloud products change names and menus often, so we teach concepts that apply to Amazon Web Services, Microsoft Azure, Google Cloud and others, and describe where to look in general terms. The course ends with a capstone: helping a small non-profit organisation move its files and website to the cloud.</p>
HTML,
    'outcomes'    => [
        'Explain what the cloud is, using servers, data centres and virtualisation',
        'Distinguish IaaS, PaaS and SaaS, and public, private and hybrid clouds',
        'Describe the core services: compute, storage types, databases, networking, CDNs and identity',
        'Compare the main providers by concept and understand free tiers and pricing',
        'Apply shared responsibility, least privilege, MFA, encryption and backups',
        'Describe the steps to deploy and monitor a simple website or app',
        'Control cost with budgets and alerts, and explain scaling, containers and serverless',
        'Plan a small cloud migration and describe cloud careers and learning paths',
    ],
    'requirements' => [
        'Computer and internet basics (for example the Computer Basics and Internet courses)',
        'Comfort using a web browser, file folders and online accounts',
        'No programming experience needed',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Amazon Web Services', 'url' => 'https://aws.amazon.com'],
        ['title' => 'Microsoft Azure', 'url' => 'https://azure.microsoft.com'],
        ['title' => 'Google Cloud', 'url' => 'https://cloud.google.com'],
        ['title' => 'Microsoft Learn', 'url' => 'https://learn.microsoft.com'],
    ],
    'modules' => [

        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: What the cloud is',
            'summary' => 'Servers, data centres, virtualisation, service models, deployment types, regions and the benefits and risks of the cloud.',
            'lessons' => [
                [
                    'title'   => 'Servers, data centres and the idea of the cloud',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what "the cloud" really means and where it physically lives. The word sounds mysterious, but understanding it clearly will help you make good decisions about storing data and running services.</p>
<h3>The cloud is other people's computers</h3>
<p>A <strong>server</strong> is a computer that provides a service to other computers, such as storing files, serving web pages or running a database. Instead of buying and looking after your own servers, <strong>cloud computing</strong> lets you rent computing power, storage and software over the internet from a provider and pay for what you use. The "cloud" is a way of describing these rented services, not a place in the sky.</p>
<p>When you save a photo to an online drive or open a document in a web browser, your device talks across the internet to servers owned by a company, in a building you will probably never see.</p>
<h3>Inside a data centre</h3>
<p>A <strong>data centre</strong> is a secure building full of servers, storage devices and networking equipment. It is designed to keep running all day, every day:</p>
<ul>
<li><strong>Power</strong>: connections from more than one source, plus batteries and generators for outages.</li>
<li><strong>Cooling</strong>: servers produce heat, so air conditioning or other cooling keeps them at safe temperatures.</li>
<li><strong>Networking</strong>: fast, repeated connections to the internet so no single cable failure cuts access.</li>
<li><strong>Physical security</strong>: guards, cameras, controlled entry and access logs.</li>
<li><strong>Fire protection and monitoring</strong>: sensors and automatic systems.</li>
</ul>
<p>Few organisations can afford this level of protection alone. Cloud providers share the cost between millions of customers, which is called <strong>economies of scale</strong>.</p>
<h3>On-premises versus cloud</h3>
<table>
<thead><tr><th>Feature</th><th>On-premises (your own servers)</th><th>Cloud</th></tr></thead>
<tbody>
<tr><td>Upfront cost</td><td>High: buy hardware, space, cooling</td><td>Low: pay as you go</td></tr>
<tr><td>Setup time</td><td>Weeks or months</td><td>Minutes or hours</td></tr>
<tr><td>Capacity</td><td>Fixed; hard to expand quickly</td><td>Grow or shrink on demand</td></tr>
<tr><td>Maintenance</td><td>Your staff replace parts and update systems</td><td>Provider handles the hardware</td></tr>
<tr><td>Control</td><td>Full physical control</td><td>Shared; you rely on provider</td></tr>
<tr><td>Internet dependence</td><td>Works on local network without internet</td><td>Needs a connection to reach services</td></tr>
</tbody>
</table>
<h3>Key characteristics</h3>
<p>Cloud services usually share these traits: <strong>on-demand self-service</strong> (you create resources yourself through a website or code), <strong>broad network access</strong>, <strong>resource pooling</strong>, <strong>rapid elasticity</strong> (quickly scaling up or down) and <strong>measured service</strong> (usage is metered and billed).</p>
<h3>Try it yourself</h3>
<ol>
<li>List ten apps you use on your phone. Mark which ones keep your data on a remote server.</li>
<li>Switch your phone to flight mode and see which of them still work. What does that tell you about cloud dependence?</li>
<li>Write a short paragraph explaining the cloud to a family member who has never used a computer.</li>
<li>Search for a picture or video tour of a data centre and note three features that protect it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The cloud means renting computing resources over the internet and paying for usage.</li>
<li>Data centres provide power, cooling, networking and security at a scale few can match alone.</li>
<li>Cloud trades upfront cost for flexibility but depends on internet access and provider trust.</li>
<li>Elasticity and measured use are central cloud traits.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Virtualisation: how one machine becomes many',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain how virtualisation lets providers share powerful machines between many customers, and why a "virtual machine" behaves like a real computer. Virtualisation is the technology that makes the cloud economical and fast to use.</p>
<h3>The problem it solves</h3>
<p>Imagine a large, powerful server. If one small website used it alone, most of its power would sit idle. Virtualisation divides one physical machine into several separate <strong>virtual machines</strong> (VMs), each acting like a complete computer with its own operating system, memory, storage and network settings.</p>
<h3>How it works</h3>
<ol>
<li>A physical computer, called the <strong>host</strong>, runs special software called a <strong>hypervisor</strong>.</li>
<li>The hypervisor slices the host's processor, memory and disk into shares.</li>
<li>Each slice becomes a VM, called a <strong>guest</strong>, that runs its own operating system such as Linux or Windows.</li>
<li>The hypervisor keeps guests isolated, so one VM cannot see another customer's data or crash the others.</li>
</ol>
<p>You can see a small version of this yourself on a laptop by installing free virtualisation software and running a second operating system inside a window.</p>
<h3>Benefits</h3>
<ul>
<li><strong>Efficiency</strong>: many customers share the same hardware, lowering the cost.</li>
<li><strong>Speed</strong>: a VM is a file-based set of settings, so it can start in a minute rather than waiting for hardware to be ordered.</li>
<li><strong>Isolation</strong>: faults and security problems in one VM are separated from others.</li>
<li><strong>Portability</strong>: a VM can be copied, saved as an <strong>image</strong>, or moved to another host if hardware fails.</li>
<li><strong>Flexibility</strong>: you can choose a bigger or smaller VM as your needs change.</li>
</ul>
<h3>Virtual machines and containers</h3>
<p>Containers are a lighter relative. Instead of a whole operating system for each, containers share the host's operating system kernel but keep applications packaged and separated. They start faster and use fewer resources. You will meet them again in Module 4.</p>
<table>
<thead><tr><th>Feature</th><th>Virtual machine</th><th>Container</th></tr></thead>
<tbody>
<tr><td>Includes a full operating system</td><td>Yes</td><td>No, shares the host's kernel</td></tr>
<tr><td>Start time</td><td>Usually minutes</td><td>Usually seconds</td></tr>
<tr><td>Size</td><td>Gigabytes</td><td>Often megabytes</td></tr>
<tr><td>Isolation</td><td>Strong</td><td>Good, but lighter</td></tr>
</tbody>
</table>
<h3>Common misunderstandings</h3>
<ul>
<li>A virtual machine is not "fake": it is a real computer environment using real hardware shared with others.</li>
<li>Deleting a VM usually deletes its attached disk unless you keep a snapshot or backup.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Draw a box for a physical server and divide it into three smaller boxes labelled VM1, VM2 and VM3.</li>
<li>Write one thing each VM could be used for, for example a website, a database and a test system.</li>
<li>Write what would happen to the three VMs if the hypervisor were removed.</li>
<li>Optional: install free virtualisation software on a spare or capable computer and create an empty VM, without installing anything else.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A hypervisor splits one physical host into several isolated virtual machines.</li>
<li>Virtualisation lowers cost, speeds up setup and improves flexibility.</li>
<li>Containers share the host's operating system and are lighter than VMs.</li>
<li>Back up data from a VM because deleting it can delete its disk.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Service models: IaaS, PaaS and SaaS',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to tell the three main cloud service models apart and choose the right one for a task. This choice decides how much work you do yourself and how much the provider does for you.</p>
<h3>The pizza analogy</h3>
<p>Imagine getting a pizza dinner in four ways. You can make everything at home. You can buy the ingredients ready to cook. You can order a pizza delivered and arrange the table yourself. Or you can go to a restaurant where everything is done for you. Cloud models work the same way: the more you let the provider handle, the less you manage and the less you can customise.</p>
<h3>The three models</h3>
<table>
<thead><tr><th>Model</th><th>You get</th><th>You manage</th><th>Provider manages</th><th>Example</th></tr></thead>
<tbody>
<tr><td>IaaS (Infrastructure as a Service)</td><td>Virtual machines, storage and networks</td><td>Operating system, software, data, security settings</td><td>Hardware, data centre, virtualisation</td><td>A rented virtual server</td></tr>
<tr><td>PaaS (Platform as a Service)</td><td>A ready platform to run your code or database</td><td>Your application and data</td><td>Operating system, runtime, servers</td><td>A managed web hosting platform or managed database</td></tr>
<tr><td>SaaS (Software as a Service)</td><td>A finished application used in a browser or app</td><td>Your accounts, content and settings</td><td>Everything else</td><td>Web e-mail, online office tools, video meetings</td></tr>
</tbody>
</table>
<h3>Choosing a model</h3>
<ol>
<li><strong>Can an existing finished product do the job?</strong> If yes, use SaaS. A small organisation does not need to build its own e-mail system.</li>
<li><strong>Do you build custom software but want to avoid server upkeep?</strong> Choose PaaS.</li>
<li><strong>Do you need full control of the operating system or special software?</strong> Choose IaaS, accepting more responsibility for updates and security.</li>
</ol>
<p>Other labels exist, such as FaaS (Function as a Service, a form of serverless) and DaaS (Desktop as a Service). The idea stays the same: different layers are handled by different parties.</p>
<h3>Trade-offs</h3>
<ul>
<li><strong>Control</strong>: highest in IaaS and lowest in SaaS.</li>
<li><strong>Effort</strong>: highest in IaaS and lowest in SaaS.</li>
<li><strong>Lock-in</strong>: higher-level services are often harder to move because they are built around a provider's own system.</li>
<li><strong>Cost</strong>: SaaS is usually a simple per-user fee. IaaS can be cheaper at scale but costs staff time.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Copy the table and add five real tools you use. Mark each IaaS, PaaS or SaaS.</li>
<li>Choose a project, such as a school website, and decide which model you would pick and why.</li>
<li>List two things you would need to look after yourself if you picked IaaS.</li>
<li>Write one sentence on what could go wrong if you rely entirely on SaaS for an important service.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>IaaS rents infrastructure, PaaS rents a platform, SaaS delivers a finished application.</li>
<li>The higher the model, the less you manage and the less control you have.</li>
<li>Start with SaaS where possible and go lower only when you need more control.</li>
<li>Consider lock-in and staff time as well as price.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Deployment types, regions, benefits and risks',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe public, private and hybrid clouds, explain regions and availability, and weigh the benefits and risks of moving to the cloud. A balanced view helps you make choices you can defend to a manager or donor.</p>
<h3>Deployment types</h3>
<ul>
<li><strong>Public cloud</strong>: services owned by a provider and shared by many customers over the internet. You get the benefit of low cost and fast growth.</li>
<li><strong>Private cloud</strong>: cloud technology used by one organisation only, in its own data centre or hosted exclusively for it. It gives more control and is used where rules or needs are strict, but it costs more to run.</li>
<li><strong>Hybrid cloud</strong>: a mix of private systems and public cloud connected together, for example keeping sensitive records locally while running the website in the public cloud.</li>
<li><strong>Multi-cloud</strong>: using more than one public provider, often to avoid dependence or to use the best service from each.</li>
</ul>
<h3>Regions and availability zones</h3>
<p>Providers place their data centres in locations around the world. A <strong>region</strong> is a geographic area containing several data centre groups. Inside a region there are usually several separate <strong>availability zones</strong>: independent data centre groups with their own power and cooling, connected by fast links. If you run your service in two zones and one has a fire or power cut, the other keeps working.</p>
<p>Why choose a region carefully?</p>
<ul>
<li><strong>Latency</strong>: data takes time to travel. A server closer to your users feels faster. Users in East Africa may be better served from a nearby region than one far away.</li>
<li><strong>Law and privacy</strong>: some data must stay in certain countries.</li>
<li><strong>Price and services</strong>: not every service is in every region, and prices differ.</li>
</ul>
<h3>Benefits</h3>
<ul>
<li>Lower upfront costs and pay-as-you-go pricing.</li>
<li>Rapid setup and the ability to scale up and down.</li>
<li>Reliable infrastructure with built-in backups and recovery options.</li>
<li>Access from anywhere, which supports remote teams and collaboration.</li>
<li>Access to modern tools such as analytics and artificial intelligence services.</li>
</ul>
<h3>Risks</h3>
<ul>
<li><strong>Internet dependence</strong>: no connection, no access. In places with unstable power or bandwidth this is a real concern.</li>
<li><strong>Costs that grow unexpectedly</strong> if resources are left running.</li>
<li><strong>Security mistakes</strong>, such as publicly exposed storage, are the customer's responsibility.</li>
<li><strong>Vendor lock-in</strong> and difficulty moving away.</li>
<li><strong>Outages</strong>: even large providers fail sometimes.</li>
<li><strong>Compliance and data location</strong> questions.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Choose a real organisation, such as a clinic, school or shop, and decide whether it would use public, private or hybrid cloud. Explain in two sentences.</li>
<li>Write which region you would pick for users in Uganda and list your reasons: speed, price, law.</li>
<li>Make a two-column table of three benefits and three risks that matter most for that organisation.</li>
<li>Suggest one action that reduces each of the three risks.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Public, private, hybrid and multi-cloud describe who owns and shares the infrastructure.</li>
<li>Regions and availability zones give choice of location and resilience to failures.</li>
<li>The cloud brings flexibility and savings but also dependence on internet, provider and good configuration.</li>
<li>List benefits and risks for your own situation before deciding.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the most accurate description of "the cloud"?
- A storage area in the sky
* Rented computing services delivered over the internet from provider data centres
- A free internet connection
- Software installed on your phone only
= The cloud means using servers and services owned by a provider over the internet.

Q: What does a hypervisor do?
- Cools the data centre
* Divides a physical machine into separate virtual machines
- Encrypts e-mails
- Connects a phone to Wi-Fi
= The hypervisor shares and isolates the host's resources among guests.

Q: Which is a difference between a container and a virtual machine?
- Containers need more memory
* Containers share the host operating system kernel and are usually lighter
- Containers cannot run applications
- Virtual machines have no operating system
= Containers package applications without a full guest operating system.

Q: A small NGO wants web e-mail and shared documents without running servers. Which model fits best?
- IaaS
- PaaS
* SaaS
- On-premises only
= Finished applications delivered online are SaaS.

Q: Which model gives you the most control and the most responsibility?
* IaaS
- SaaS
- PaaS
- They are all equal
= In IaaS you manage the operating system, software and security settings.

Q: Which is a hybrid cloud?
- Using only a public provider
- Using a private server room only
* Keeping sensitive records locally while running a website in the public cloud
- Using two e-mail accounts
= Hybrid joins private systems with public cloud services.

Q: Why do availability zones matter? (choose all that apply)
* They help a service stay up if one data centre group fails
* They are separate groups with independent power and cooling
- They remove all costs
- They make internet access unnecessary
= Spreading across zones improves reliability but you still need internet and payment.

Q: Which is a genuine risk of cloud adoption?
- Servers never fail
* Unexpected costs if resources are left running
- Unlimited free storage
- No need for security settings
= Costs and configuration remain the customer's responsibility.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Core services',
            'summary' => 'Compute, storage types, databases, networking, content delivery, identity, the main providers by concept, free tiers and pricing.',
            'lessons' => [
                [
                    'title'   => 'Compute: virtual machines, containers and functions',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe the main ways of running programs in the cloud and choose between them. Compute is the "engine" of the cloud, and picking the right kind affects both cost and effort.</p>
<h3>What compute means</h3>
<p><strong>Compute</strong> is processing power: the CPUs, memory and sometimes graphics processors that run your software. Every provider offers several forms of it, sold by the second, minute or hour.</p>
<h3>Virtual machines</h3>
<p>A cloud virtual machine (VM, sometimes called an instance) is the closest to a traditional server. When you create one you choose:</p>
<ol>
<li><strong>Region</strong>: where it will run.</li>
<li><strong>Image</strong>: the operating system and software to start from, for example a Linux or Windows image.</li>
<li><strong>Size</strong>: how many virtual CPUs and how much memory. Providers offer families tuned for general use, high memory, high processing, or graphics work.</li>
<li><strong>Storage</strong>: the disk attached to it.</li>
<li><strong>Network and access</strong>: which ports are open, and how you sign in, for example using a secure key.</li>
</ol>
<p>You then pay while the VM runs. Stopping it usually stops the compute charges, though attached disks and addresses may still cost money.</p>
<h3>Managed platforms</h3>
<p>Platform services run your application for you. You upload code, and the provider handles the operating system, patching and often scaling. This suits websites and APIs where you do not need to manage servers.</p>
<h3>Containers</h3>
<p>Containers package an application with everything it needs so it runs the same way anywhere. Cloud providers offer services to run containers without managing the underlying servers, and tools called orchestrators to manage many containers together.</p>
<h3>Serverless functions</h3>
<p><strong>Serverless</strong> does not mean there are no servers. It means you do not manage them. You write a small <strong>function</strong> that runs only when something triggers it, such as a file upload or a web request, and you pay only for the time it runs. It is excellent for small tasks and occasional workloads.</p>
<table>
<thead><tr><th>Option</th><th>Control</th><th>Effort</th><th>Good for</th></tr></thead>
<tbody>
<tr><td>Virtual machine</td><td>Highest</td><td>High</td><td>Custom software, legacy systems</td></tr>
<tr><td>Managed platform</td><td>Medium</td><td>Low</td><td>Typical web applications</td></tr>
<tr><td>Containers</td><td>Medium to high</td><td>Medium</td><td>Portable, modern applications</td></tr>
<tr><td>Serverless functions</td><td>Low</td><td>Lowest</td><td>Small tasks, bursts of work</td></tr>
</tbody>
</table>
<h3>Common mistakes</h3>
<ul>
<li>Choosing a VM much larger than needed. You can start small and resize.</li>
<li>Forgetting to stop test machines.</li>
<li>Choosing the newest style of compute without a reason.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Pick three small projects: a personal blog, a monthly report generator and a school management system.</li>
<li>Choose a compute option for each from the table.</li>
<li>Write one reason for each choice and one risk.</li>
<li>Look at a provider's compute pages and note the names for its VM, container and function services.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Compute options range from fully controlled VMs to hands-off serverless functions.</li>
<li>VMs are chosen by region, image, size, storage and network settings.</li>
<li>Serverless runs code only when triggered and charges only for usage.</li>
<li>Start small, stop unused machines and pick the simplest option that works.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Storage and databases',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose between object, block and file storage and between database types. Storing data properly is at the centre of almost every project, and the wrong choice can be slow, expensive or insecure.</p>
<h3>Three types of storage</h3>
<table>
<thead><tr><th>Type</th><th>How it works</th><th>Typical use</th><th>Everyday comparison</th></tr></thead>
<tbody>
<tr><td>Object storage</td><td>Files (objects) stored with labels in buckets, reached through web addresses or tools</td><td>Photos, videos, backups, website files, archives</td><td>A huge, cheap warehouse where each item has a tag</td></tr>
<tr><td>Block storage</td><td>Raw disks attached to a VM, divided into blocks</td><td>Operating system disks, databases needing fast access</td><td>A hard drive plugged into a computer</td></tr>
<tr><td>File storage</td><td>Shared folders accessible by many machines at once</td><td>Shared team files, content management</td><td>A network shared drive</td></tr>
</tbody>
</table>
<p>Object storage is very durable because providers copy data across several locations, and it scales almost without limit. It is also usually the cheapest. Providers offer <strong>storage classes</strong>: frequently used data costs more to store but is quick to read, while archive data costs little to store but takes longer or costs more to retrieve.</p>
<h3>Databases</h3>
<p>A <strong>database</strong> is organised, searchable data. Managed database services handle installation, backups, updates and sometimes failover for you.</p>
<ul>
<li><strong>Relational (SQL) databases</strong>: data in tables with rows and columns, linked by keys. Good for structured information such as customers, orders and accounts, where consistency matters.</li>
<li><strong>Non-relational (NoSQL) databases</strong>: flexible formats such as documents, key-value pairs or graphs. Good for huge scale, changing data shapes or very fast simple lookups.</li>
<li><strong>Data warehouses</strong>: systems designed to analyse large amounts of data, used for reporting and business intelligence.</li>
<li><strong>In-memory caches</strong>: very fast temporary storage to speed up repeated reads.</li>
</ul>
<h3>Choosing</h3>
<ol>
<li>What kind of data is it: files, tables or flexible documents?</li>
<li>How often is it read and changed?</li>
<li>How important are speed, consistency and cost?</li>
<li>How long must it be kept and how quickly must it be recovered?</li>
</ol>
<h3>Protecting storage</h3>
<p>Many data leaks come from storage buckets accidentally left open to the public. Keep storage private by default, give access only to those who need it, turn on versioning where it is offered so deleted or overwritten files can be recovered, and test your restore process.</p>
<h3>Try it yourself</h3>
<ol>
<li>For each item choose a storage or database type: school photos, a list of fee payments, a shared folder for staff, a nightly backup, a disk for a server's operating system.</li>
<li>Explain your reasoning briefly.</li>
<li>Look at the storage classes offered by one provider and write which class you would use for old project files you rarely open.</li>
<li>Write one sentence on how you would test that a backup can be restored.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Object storage suits files at scale, block storage suits disks, file storage suits shared folders.</li>
<li>Relational databases suit structured data, NoSQL suits flexible or very large workloads.</li>
<li>Storage classes trade cost against access speed.</li>
<li>Keep storage private, use versioning and test restores.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Networking, CDNs and identity and access management',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe how cloud resources are connected and protected, what a content delivery network does, and how identity and access management decides who can do what. These three areas connect the other services together.</p>
<h3>Virtual networks</h3>
<p>A <strong>virtual network</strong> (often called a VPC or VNet) is your own private network inside the provider's cloud. You decide its address range and divide it into <strong>subnets</strong>. Typical layout:</p>
<ul>
<li><strong>Public subnet</strong>: holds resources that must be reachable from the internet, such as a web server or load balancer.</li>
<li><strong>Private subnet</strong>: holds databases and internal systems with no direct internet access.</li>
</ul>
<p>Traffic is controlled by <strong>firewall rules</strong> (often called security groups or network access rules). Good practice is to deny everything and then allow only what is needed, for example web traffic on the standard web ports and administrative access only from known addresses.</p>
<p>A <strong>load balancer</strong> spreads incoming requests across several servers so no single server is overloaded and one failure does not stop the service. <strong>DNS</strong> (Domain Name System) turns names such as example.org into the numeric address of your service. A <strong>VPN</strong> (virtual private network) creates an encrypted tunnel between your office and the cloud network.</p>
<h3>Content delivery networks</h3>
<p>A <strong>CDN</strong> (content delivery network) is a worldwide set of servers that keep copies of your website's files, such as images and scripts, close to visitors. A person in Gulu loads those files from a nearby edge location instead of a distant main server, which makes pages faster, reduces load on your server and helps absorb attack traffic.</p>
<h3>Identity and access management (IAM)</h3>
<p><strong>IAM</strong> controls who (identity) can do what (permissions) on which resources.</p>
<ol>
<li><strong>Users and groups</strong>: people have their own accounts and are placed in groups such as "finance" or "developers".</li>
<li><strong>Roles</strong>: sets of permissions that people or services can take on temporarily.</li>
<li><strong>Policies</strong>: rules written to allow or deny actions, for example "allow read-only access to this storage bucket".</li>
<li><strong>Authentication</strong>: proving who you are, using passwords, multi-factor authentication or single sign-on.</li>
<li><strong>Audit logs</strong>: records of who did what and when.</li>
</ol>
<p>Every account that has not been used, and every permission that is wider than needed, is a risk. Give people their own accounts and never share one login.</p>
<h3>Try it yourself</h3>
<ol>
<li>Draw a diagram of a simple cloud network: internet, load balancer, two web servers in a public subnet and a database in a private subnet.</li>
<li>Write the firewall rules in words that would allow visitors to the website but protect the database.</li>
<li>Make a table of four staff roles in a small organisation and the minimum cloud permissions each needs.</li>
<li>Explain in two sentences why a CDN helps users far from your server.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Virtual networks, subnets and firewall rules isolate and protect resources.</li>
<li>Load balancers and DNS direct traffic reliably to your service.</li>
<li>A CDN stores copies of content near users for speed and resilience.</li>
<li>IAM combines identities, roles, policies, authentication and logging; share permissions minimally.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Comparing providers, free tiers and pricing',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to compare the major cloud providers by concept, read a pricing model and use free tiers safely. This helps you choose a provider on facts, not fashion, and avoid unwelcome bills.</p>
<h3>The big three and others</h3>
<p>The largest general-purpose providers are Amazon Web Services (AWS), Microsoft Azure and Google Cloud. Others include Oracle, IBM, Alibaba Cloud and many regional or specialist hosts. All offer the same broad categories of service; their names differ.</p>
<table>
<thead><tr><th>Category</th><th>What it does</th><th>How providers label it</th></tr></thead>
<tbody>
<tr><td>Virtual machines</td><td>Rented virtual servers</td><td>Each provider has its own "virtual machine" or "instance" service</td></tr>
<tr><td>Object storage</td><td>Files in buckets</td><td>One storage service per provider</td></tr>
<tr><td>Managed databases</td><td>Hosted relational and NoSQL databases</td><td>Several services per provider</td></tr>
<tr><td>Serverless functions</td><td>Code that runs on demand</td><td>A functions service per provider</td></tr>
<tr><td>Networking and CDN</td><td>Virtual networks, load balancing, edge delivery</td><td>Networking and CDN services</td></tr>
<tr><td>Identity</td><td>Accounts, roles, permissions</td><td>An identity service</td></tr>
</tbody>
</table>
<p>Skills transfer between providers because the concepts are the same. Learn the idea first, then look up that provider's name for it in its documentation.</p>
<h3>How to compare</h3>
<ol>
<li><strong>What do you need?</strong> List the services before looking at brands.</li>
<li><strong>Nearby regions</strong> and speed for your users.</li>
<li><strong>Price</strong> for your actual workload, using the provider's online calculator.</li>
<li><strong>Existing tools</strong>: an organisation using a lot of a particular office suite might find one provider integrates easily.</li>
<li><strong>Support, training and community</strong>: documentation quality, local partners, and courses.</li>
<li><strong>Compliance and data location</strong> options.</li>
<li><strong>Exit plan</strong>: how easily you can take data elsewhere.</li>
</ol>
<h3>Understanding pricing</h3>
<p>Most services have several price elements. Look for: compute time (per second, minute or hour), storage (per gigabyte per month), data transfer (often free into the cloud and charged going out), requests (per thousand or million operations) and extra features. <strong>On-demand</strong> prices are flexible but highest. <strong>Reserved or committed</strong> discounts reduce price in exchange for a one- or three-year promise. <strong>Spot or preemptible</strong> capacity is cheap but can be taken away at short notice, so it suits work that can be interrupted.</p>
<h3>Free tiers</h3>
<p>Providers offer free tiers: some services are free up to a limit, others give credits or a free trial period for a limited time. They are excellent for learning, but read the rules carefully. Set a budget alert before you start, never enter your payment details unless you accept possible charges, and delete resources when you finish practising. Terms change, so check the provider's current offer.</p>
<h3>Try it yourself</h3>
<ol>
<li>Pick a simple workload, such as a small website with 20 GB of files and one small server.</li>
<li>Open two providers' pricing calculators, or read their pricing pages, and note the price elements for each service.</li>
<li>Write a table comparing the estimated monthly cost, noting your assumptions.</li>
<li>List three precautions you would take before using a free tier.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Major providers offer equivalent categories of service under different names.</li>
<li>Compare on needs, region, price, integration, support, compliance and exit options.</li>
<li>Prices combine compute, storage, data transfer and requests.</li>
<li>Use free tiers with budget alerts and delete practice resources.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which compute option charges you mainly for the time your code actually runs when triggered?
- Virtual machine
- Dedicated server
* Serverless function
- Private data centre
= Serverless functions run on demand and are billed for execution time.

Q: Which type of storage is best for photos, videos and backups at large scale?
* Object storage
- Block storage
- RAM
- A USB flash drive
= Object storage is durable, cheap and scales greatly for files.

Q: Which are examples of relational database use? (choose all that apply)
* A list of customers and orders linked by keys
* Student records with fixed fields
- Photos and videos in bulk
- Operating system disks
= Relational databases suit structured, linked tables.

Q: What does a CDN do?
- Writes a website for you
* Keeps copies of content on servers near visitors to make loading faster
- Encrypts a laptop
- Replaces DNS
= CDNs reduce distance and load on the main server.

Q: A private subnet is typically used for...
- The public web server
- A load balancer facing the internet
* A database that should not be reachable directly from the internet
- Public advertising
= Private subnets protect internal systems.

Q: In IAM, what is a role?
- A hardware component
* A set of permissions that a person or service can take on
- A kind of CDN
- A storage class
= Roles bundle permissions so access can be granted in controlled ways.

Q: What is a safe way to use a cloud free tier?
- Ignore usage limits
* Set a budget alert, read the limits and delete resources afterwards
- Share one account among many learners
- Leave test servers running over the holidays
= Free tiers have limits and rules, so monitor and clean up.

Q: Why do skills transfer between AWS, Azure and Google Cloud?
- Their products are identical
* They offer similar categories of service built on the same concepts
- They share the same prices
- They use the same account system
= Learn the concept first and then the provider's names for it.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Security and compliance',
            'summary' => 'Shared responsibility, least privilege and MFA, encryption and backups, data location and privacy law in general terms.',
            'lessons' => [
                [
                    'title'   => 'Shared responsibility, least privilege and MFA',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain who is responsible for what in the cloud, apply the principle of least privilege and secure accounts with multi-factor authentication. Most cloud breaches come from simple mistakes by customers, not from the provider's data centre being hacked, so these basics matter a great deal.</p>
<h3>The shared responsibility model</h3>
<p>Security in the cloud is split between the provider and you. The provider is responsible for the security <em>of</em> the cloud: the buildings, hardware, network and virtualisation layer. You are responsible for security <em>in</em> the cloud: your data, your accounts, your settings and, depending on the service model, your operating systems and applications.</p>
<table>
<thead><tr><th>Layer</th><th>IaaS</th><th>PaaS</th><th>SaaS</th></tr></thead>
<tbody>
<tr><td>Physical data centre</td><td>Provider</td><td>Provider</td><td>Provider</td></tr>
<tr><td>Network and virtualisation</td><td>Provider</td><td>Provider</td><td>Provider</td></tr>
<tr><td>Operating system</td><td>You</td><td>Provider</td><td>Provider</td></tr>
<tr><td>Application</td><td>You</td><td>You</td><td>Provider</td></tr>
<tr><td>Data and content</td><td>You</td><td>You</td><td>You</td></tr>
<tr><td>Identities and access</td><td>You</td><td>You</td><td>You</td></tr>
</tbody>
</table>
<p>In every model the customer remains responsible for their data, who can access it, and their configuration choices.</p>
<h3>Least privilege</h3>
<p><strong>Least privilege</strong> means giving each person or system only the permissions needed for their job, and no more. If an account is stolen, the damage is limited.</p>
<ol>
<li>List each role in your organisation and what it must do.</li>
<li>Give a read-only permission when reading is enough.</li>
<li>Limit access to specific resources rather than the whole account.</li>
<li>Do not use the main administrator (root) account for daily work. Keep it locked away with strong protection.</li>
<li>Review permissions regularly and remove people who have left.</li>
<li>Use temporary access, and avoid sharing keys or passwords.</li>
</ol>
<h3>Multi-factor authentication</h3>
<p><strong>MFA</strong> (multi-factor authentication) requires two or more proofs of identity: something you know (a password), something you have (a phone app, security key) and something you are (a fingerprint). A stolen password alone then cannot open the account.</p>
<ul>
<li>Turn on MFA for every administrator and, ideally, every user.</li>
<li>Prefer an authenticator app or security key to text messages, which can be intercepted or lost with SIM swaps.</li>
<li>Store recovery codes safely offline.</li>
<li>Use a password manager and long unique passwords.</li>
</ul>
<h3>Other basics</h3>
<ul>
<li>Never put access keys or passwords in code or in public repositories.</li>
<li>Turn on logging and alerts for unusual sign-ins.</li>
<li>Keep systems patched where you manage them.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Choose a small organisation and list five roles. Write the permissions each needs and the permissions they should not have.</li>
<li>Check which of your own important online accounts, such as e-mail, have MFA turned on. Turn it on where missing.</li>
<li>Save recovery codes for one account in a safe place.</li>
<li>Draw the shared responsibility table for a SaaS e-mail service and mark what you must still manage.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>The provider secures the cloud; you secure your data, access and configuration.</li>
<li>Least privilege limits damage when something goes wrong.</li>
<li>MFA protects against stolen passwords; use an app or key where possible.</li>
<li>Do not use the root account for daily work or share logins.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Encryption and backups',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain encryption at rest and in transit, and design a simple backup plan with recovery goals. Together they protect your data from theft, loss, mistakes and ransomware.</p>
<h3>Encryption in plain words</h3>
<p><strong>Encryption</strong> scrambles data so that only someone with the correct key can read it. Without the key the data looks like random characters.</p>
<ul>
<li><strong>Encryption in transit</strong>: protects data while it travels across networks. The padlock in a browser (HTTPS) and the protocol called TLS do this. Always use it for websites, apps and connections to the cloud.</li>
<li><strong>Encryption at rest</strong>: protects data stored on disks, in databases and in storage buckets. If someone steals a disk or gets a copy of a file they cannot read it without the key. Most providers offer it, often turned on by default or by a simple setting.</li>
</ul>
<h3>Managing keys</h3>
<p>Encryption is only as good as its key management. Providers offer <strong>key management services</strong> where keys are created, stored, rotated and controlled by access rules. You can usually choose provider-managed keys (simple) or keys you control (more control and more responsibility). If you lose a key you control, the data is lost forever. Never store keys next to the data they protect or in code.</p>
<h3>Backups</h3>
<p>A <strong>backup</strong> is a separate copy of data that lets you recover after deletion, corruption, disaster or attack. Replication across zones is not a backup: if you delete a file by mistake, the deletion copies everywhere.</p>
<p>Use the <strong>3-2-1 rule</strong>: keep <em>3</em> copies of your data, on <em>2</em> different types of storage, with <em>1</em> copy off-site, or in another region or account.</p>
<h3>Recovery targets</h3>
<ul>
<li><strong>RPO</strong> (recovery point objective): how much data can you afford to lose, measured in time? If backups run nightly, the RPO is up to a day.</li>
<li><strong>RTO</strong> (recovery time objective): how long can you be down before it hurts? An hour? A day?</li>
</ul>
<p>Short RPO and RTO cost more. Ask the people who rely on the system what they can truly accept, and then design to that.</p>
<h3>A simple backup plan</h3>
<ol>
<li>List the data that matters: documents, databases, website, configuration, e-mail.</li>
<li>Decide RPO and RTO for each.</li>
<li>Choose automated backups with a clear schedule and retention, for example daily for 30 days and monthly for a year.</li>
<li>Copy backups to a different region or account, with restricted access so an attacker who gets in cannot delete them.</li>
<li>Test restoring a file, then a full system, at least a few times each year.</li>
<li>Document the steps so someone else could do it in an emergency.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Write RPO and RTO for three things: a school's fee records, a website and a staff photo library.</li>
<li>Sketch a 3-2-1 backup plan for one of them.</li>
<li>Explain in a sentence why keeping a copy in the same account and region is not enough.</li>
<li>Restore one file from an online drive's version history or recycle bin to see how it works.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Encrypt data in transit and at rest, and protect the keys.</li>
<li>Replication is not backup; follow the 3-2-1 rule.</li>
<li>RPO is acceptable data loss; RTO is acceptable downtime.</li>
<li>A backup that has never been restored is only a hope.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Data location, privacy law and compliance',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain, in general terms, why the physical location of data matters, what common privacy principles require and how providers support compliance. This is not legal advice. For real decisions, consult a qualified adviser.</p>
<h3>Why location matters</h3>
<p>Data stored in the cloud sits on real servers in real countries, and the laws of those places can apply. Some laws require certain data, for example health or government records, to stay inside a country. Some laws restrict sending personal data abroad unless safeguards exist. Others allow authorities to request data held in their territory. When you pick a region you choose the country where the data is stored, so check the rules that apply to you and your users.</p>
<h3>Personal data and privacy principles</h3>
<p><strong>Personal data</strong> is information that identifies a person: names, phone numbers, e-mail addresses, photos, health details, location. Many countries have data protection laws, including Uganda's Data Protection and Privacy Act and Europe's GDPR, which can also apply to organisations serving people there. Laws differ, but common principles include:</p>
<ul>
<li><strong>Purpose</strong>: collect data for a clear, lawful reason.</li>
<li><strong>Consent or another lawful basis</strong>: be able to justify your use of the data.</li>
<li><strong>Minimisation</strong>: collect only what is needed.</li>
<li><strong>Accuracy and limited keeping</strong>: correct mistakes and delete data when no longer needed.</li>
<li><strong>Security</strong>: protect it properly and report serious breaches where the law requires.</li>
<li><strong>Rights</strong>: let people see, correct or delete their data.</li>
</ul>
<h3>Who is responsible?</h3>
<p>The organisation deciding why and how personal data is used is usually the <strong>controller</strong>, and the provider that processes it for them is a <strong>processor</strong>. Using a cloud provider does not remove your responsibility for the data you collect. Read the provider's data processing terms and where it says it stores data.</p>
<h3>Compliance and certifications</h3>
<p><strong>Compliance</strong> means following rules, laws and standards that apply to you. Providers publish independent audit reports and certifications for their data centres and services, often against well-known security standards. These show the provider's controls are checked, but compliance of your own system is still your job: you must configure and use the service correctly.</p>
<h3>Practical steps</h3>
<ol>
<li><strong>Know your data</strong>: make a list of what personal data you hold and where it sits.</li>
<li><strong>Classify it</strong>: public, internal, confidential or sensitive.</li>
<li><strong>Choose regions</strong> to fit legal and user needs.</li>
<li><strong>Restrict access</strong> and log who views sensitive data.</li>
<li><strong>Write a clear privacy notice</strong> and a plan for requests and breaches.</li>
<li><strong>Review providers</strong>: their terms, security reports and exit options.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>List the personal data held by a school or clinic: at least eight items.</li>
<li>Classify each as public, internal, confidential or sensitive.</li>
<li>Decide where you think the sensitive items should be stored and why, noting that you would confirm with an adviser.</li>
<li>Write a three-sentence privacy notice for a sign-up form.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Data location determines which laws can apply.</li>
<li>Privacy principles include purpose, minimisation, security and people's rights.</li>
<li>You stay responsible for personal data even when a provider stores it.</li>
<li>Provider certifications help but do not make your configuration compliant.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Under shared responsibility, who is always responsible for the customer's data and access settings?
- The provider
* The customer
- The internet service company
- The government
= Whatever the service model, you control and protect your data and who can reach it.

Q: What is least privilege?
- Giving everyone administrator rights
* Giving each person only the permissions needed for their job
- Removing all passwords
- Sharing one login for the team
= Limiting permissions limits the damage from mistakes or stolen accounts.

Q: Which is the strongest protection for an administrator account?
- A long password only
- A short password changed daily
* A strong password plus MFA with an authenticator app or security key
- Sharing the password with a colleague
= MFA means a stolen password alone is not enough.

Q: Which are examples of encryption in transit? (choose all that apply)
* HTTPS on a website
* TLS between an app and a server
- Encrypting a stored disk
- Writing a backup schedule
= Encryption in transit protects data moving across networks; the disk is encryption at rest.

Q: Why is replication across zones not the same as a backup?
- It is too expensive
* A mistaken deletion or corruption is copied to every replica
- It is slower
- It only works for photos
= Backups are separate, versioned copies that let you go back in time.

Q: In the 3-2-1 rule, what does the "1" mean?
- One password
* One copy kept off-site or in another region or account
- One server
- One backup per year
= An off-site copy survives a disaster that destroys the main location.

Q: A business accepts losing up to one hour of data after a failure. What is that?
- RTO of one hour
* RPO of one hour
- Encryption level
- Storage class
= RPO measures acceptable data loss in time; RTO measures acceptable downtime.

Q: A cloud provider holds a clinic's patient records. Who remains responsible for deciding how that personal data is used?
- Nobody
- Only the provider
* The clinic as the organisation collecting the data, with the provider as processor
- The patients' neighbours
= Using a provider does not remove your duty for the data you collect.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Working in the cloud',
            'summary' => 'Deploy a simple website, monitor and scale it, control costs with budgets and alerts, and understand serverless and containers.',
            'lessons' => [
                [
                    'title'   => 'Deploying a simple website step by step',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe, in general terms, the steps to put a simple website online using cloud services. Even if you never run the steps yourself, understanding them helps you talk to developers, review plans and avoid costly mistakes.</p>
<h3>Choose the approach</h3>
<table>
<thead><tr><th>Approach</th><th>Best for</th><th>Effort</th></tr></thead>
<tbody>
<tr><td>Static site on object storage plus CDN</td><td>Pages with fixed content: brochure sites, portfolios, documentation</td><td>Low and cheap</td></tr>
<tr><td>Managed web platform</td><td>Sites or apps built with common frameworks</td><td>Low to medium</td></tr>
<tr><td>Virtual machine with a web server</td><td>Custom software and full control</td><td>High</td></tr>
</tbody>
</table>
<h3>The general steps</h3>
<ol>
<li><strong>Create an account</strong> with your chosen provider using a work e-mail address. Immediately turn on MFA and set a budget alert.</li>
<li><strong>Create a non-administrator user</strong> or role for daily work, so you do not use the root account.</li>
<li><strong>Choose a region</strong> near your visitors.</li>
<li><strong>Prepare your site files</strong> on your computer: pages, images, and styles. Test them locally.</li>
<li><strong>Create the hosting resource</strong>: a storage bucket set up for website hosting, a platform application, or a VM with a web server installed.</li>
<li><strong>Upload or deploy</strong> the files. On a platform you may connect a code repository so each saved change deploys automatically.</li>
<li><strong>Set permissions carefully</strong>: allow the public to read only the website files, not the whole storage and not the configuration.</li>
<li><strong>Add a CDN</strong> in front of the site for speed and extra protection.</li>
<li><strong>Connect a domain name</strong> by adding records at your domain registrar or DNS service that point your name to the service.</li>
<li><strong>Turn on HTTPS</strong> by requesting a free or paid certificate through the provider or a certificate service. Redirect plain HTTP to HTTPS.</li>
<li><strong>Test everything</strong>: open the site on phone and computer, check links, forms and speed.</li>
<li><strong>Monitor and back up</strong>: set up uptime checks and keep a copy of the site files and database elsewhere.</li>
</ol>
<h3>If the site needs a database</h3>
<p>Use a managed database in a private subnet. Allow only your web application to connect, store the password in a secrets service and not in the code, and enable automatic backups.</p>
<h3>Common mistakes</h3>
<ul>
<li>Making a whole storage bucket public.</li>
<li>Forgetting to renew a domain or certificate.</li>
<li>Putting passwords in uploaded files.</li>
<li>Choosing a large VM for a small site.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Create a simple page on your computer with a heading, a paragraph and a picture.</li>
<li>Write a checklist of the twelve steps above adapted to your chosen approach.</li>
<li>Decide which approach is best for the page and why.</li>
<li>Optional: use a provider's free tier or a free static hosting service to publish the page, after setting a budget alert, and delete it afterwards.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Match the approach to the site: static for fixed content, platform or VM for dynamic work.</li>
<li>Secure the account, choose a region and prepare files before creating resources.</li>
<li>Grant only the public access that visitors need, add a CDN, a domain and HTTPS.</li>
<li>Test, monitor and back up after launch.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Monitoring, scaling and cost control',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to watch the health of a cloud system, explain scaling, and put limits and alerts on spending. These habits are what keep a cloud project reliable and affordable after the first excitement of launching it.</p>
<h3>Monitoring</h3>
<p><strong>Monitoring</strong> means collecting and watching information about your system. Providers include monitoring services that gather three kinds of data:</p>
<ul>
<li><strong>Metrics</strong>: numbers over time, such as processor use, memory, requests per minute and response time.</li>
<li><strong>Logs</strong>: text records of events, such as errors and sign-ins.</li>
<li><strong>Traces</strong>: the path a request takes through several services, which helps find slow parts.</li>
</ul>
<p>Build a simple dashboard with the main health numbers, and create <strong>alerts</strong> that message the right person by e-mail or phone app when something goes wrong, such as the site being down or errors rising. Alerts should be useful: too many false alarms cause people to ignore them. Also run an <strong>uptime check</strong> from outside that visits your site every few minutes.</p>
<h3>Scaling</h3>
<p>Scaling means changing capacity to match demand.</p>
<ul>
<li><strong>Vertical scaling</strong> (scaling up): use a bigger machine. Simple but has limits and may need a restart.</li>
<li><strong>Horizontal scaling</strong> (scaling out): add more machines and share the work with a load balancer. This is the common cloud approach and improves resilience.</li>
<li><strong>Auto scaling</strong>: rules add or remove capacity automatically, for example "add a server when average processor use stays above 70 percent for five minutes, and remove one when it falls below 30 percent".</li>
</ul>
<p>Design applications to be <strong>stateless</strong> where possible, keeping data in a database or storage and not on a single server, so any server can handle any request.</p>
<h3>Cost control</h3>
<ol>
<li><strong>Set a budget and alerts</strong> in the billing area, with notices at, say, 50, 80 and 100 percent, sent to more than one person.</li>
<li><strong>Tag resources</strong> with labels such as project and owner so you can see who spends what.</li>
<li><strong>Delete or stop what you do not use</strong>: test machines, old disks, unused addresses, old snapshots.</li>
<li><strong>Right-size</strong>: check usage and choose smaller instances where processors sit idle.</li>
<li><strong>Use the right storage class</strong> and delete old data by rule.</li>
<li><strong>Use discounts</strong> such as reserved or committed plans for steady workloads, after you know your usage.</li>
<li><strong>Watch data transfer charges</strong>, especially data leaving the cloud.</li>
<li><strong>Review the bill monthly</strong>, looking for surprises.</li>
</ol>
<p>Remember that alerts tell you about spending, but some budget tools do not stop it. Learn whether your provider can also apply automatic limits, and what the real effects would be.</p>
<h3>Try it yourself</h3>
<ol>
<li>List five metrics you would put on a dashboard for a website.</li>
<li>Write an alert rule in words for each of two problems: site down and very slow response.</li>
<li>Write an auto scaling rule for a school registration site with a busy week at the start of term.</li>
<li>Make a cost checklist of six habits and tick the ones you would do weekly.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Metrics, logs and traces show system health; alerts bring attention to problems.</li>
<li>Scaling out and auto scaling match capacity to demand, and stateless designs make them easy.</li>
<li>Budgets, alerts, tags and regular clean-up control costs.</li>
<li>Check the bill regularly because small forgotten resources add up.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Containers and serverless explained simply',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain containers and serverless computing in simple terms and say when each is a good fit. These ideas appear in job descriptions and project plans everywhere, so a clear picture of them is very valuable.</p>
<h3>The problem containers solve</h3>
<p>Developers often say "it works on my computer" when a program fails elsewhere. The reason is usually that the other computer has different versions of software or settings. A <strong>container</strong> packages an application together with everything it needs to run, so it behaves the same on a laptop, a test server and the cloud.</p>
<h3>Container concepts</h3>
<ul>
<li><strong>Image</strong>: a read-only package, like a template or recipe, that holds the application and its parts.</li>
<li><strong>Container</strong>: a running copy of an image.</li>
<li><strong>Registry</strong>: an online library where images are stored and shared.</li>
<li><strong>Orchestration</strong>: software that starts, stops, scales and heals many containers across many machines. A widely used open standard for this is called Kubernetes, and each major provider offers a managed version.</li>
</ul>
<p>Containers start quickly, use fewer resources than virtual machines and make it easy to run many small parts of an application, sometimes called <strong>microservices</strong>. They also add complexity, so small projects do not always need them.</p>
<h3>A typical container flow</h3>
<ol>
<li>A developer describes the application in a short build file.</li>
<li>A tool builds an image from the file.</li>
<li>The image is tested and stored in a registry.</li>
<li>A cloud service pulls the image and runs it as one or more containers.</li>
<li>To update, a new image is built and the old containers are replaced gradually.</li>
</ol>
<h3>Serverless</h3>
<p>In <strong>serverless</strong> computing the provider runs the servers, scales them automatically and charges for actual use. Forms include <strong>functions</strong> that run when triggered, and serverless databases, queues and storage. You focus on your code and data.</p>
<table>
<thead><tr><th>Good fit</th><th>Poor fit</th></tr></thead>
<tbody>
<tr><td>Short tasks triggered by events, such as resizing an uploaded photo</td><td>Very long-running or constant heavy work</td></tr>
<tr><td>Irregular traffic that sometimes drops to zero</td><td>Programs needing special software on the server</td></tr>
<tr><td>Small backends and automation</td><td>Applications that need very consistent speed all the time</td></tr>
</tbody>
</table>
<p>Watch for <strong>cold starts</strong> (a short delay when a function has not run for a while), limits on running time and the risk of lock-in to a provider's way of working.</p>
<h3>Choosing between VMs, containers and serverless</h3>
<p>Ask: how much control do I need, how steady is the load, and how much operational skill does the team have? Start with the simplest option that works, and move to more advanced options only when you have a reason.</p>
<h3>Try it yourself</h3>
<ol>
<li>Explain containers to a friend using the idea of a lunch box or shipping container.</li>
<li>List three tasks in a small organisation that serverless functions could automate, such as sending a confirmation e-mail after a form is submitted.</li>
<li>For the tasks, state a trigger and the output.</li>
<li>Write one reason a small team might not choose a full container orchestration system.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Containers package an application so it runs the same everywhere.</li>
<li>Images are templates, containers are running copies and registries store images.</li>
<li>Serverless runs your code on demand with automatic scaling and pay-per-use pricing.</li>
<li>Choose the simplest model that meets your needs; advanced tools add complexity.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which hosting approach is cheapest and simplest for a brochure website with fixed pages?
* A static site on object storage with a CDN
- A large virtual machine
- A private data centre
- A container cluster
= Static content needs no server software, so storage and a CDN are enough.

Q: Which is a safe permission setting when hosting a website from a storage bucket?
- Make the whole account public
* Allow the public to read only the website files
- Publish the access keys
- Turn off logging
= Public access should be limited to what visitors need.

Q: What are metrics?
- Written reports of meetings
* Numbers measured over time, such as processor use and response time
- Encryption keys
- Domain names
= Metrics show system health as numbers over time.

Q: What is horizontal scaling?
- Using a bigger machine
* Adding more machines and sharing the work
- Deleting machines
- Buying a new domain
= Scaling out adds capacity by adding instances and balancing the load.

Q: Which actions help control cloud costs? (choose all that apply)
* Setting budget alerts
* Deleting unused test machines and old disks
- Leaving all resources running just in case
- Never checking the bill
= Regular clean-up and visibility prevent unpleasant surprises.

Q: What is a container image?
- A photo stored in the cloud
* A packaged template containing an application and what it needs to run
- A firewall rule
- A kind of database
= Containers are running copies of images.

Q: A function that resizes photos only when someone uploads one is a good example of...
- Vertical scaling
- A private cloud
* Serverless computing
- Cold storage
= It is event-triggered, short and charged only when it runs.

Q: What is a cold start in serverless functions?
- A cooling failure in the data centre
* A short delay when a function has not run recently
- A free trial
- A discount
= The first call after idle time may take a little longer.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Careers and next steps',
            'summary' => 'Cloud roles, certifications and learning paths, designing for reliability and a capstone scenario for a small NGO.',
            'lessons' => [
                [
                    'title'   => 'Cloud careers, certifications and learning paths',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe common cloud roles, explain how certifications fit in, and plan your own learning path with free resources. Cloud skills are in demand across banks, telecoms, health, education, NGOs and start-ups, including remote work for clients elsewhere.</p>
<h3>Common cloud roles</h3>
<table>
<thead><tr><th>Role</th><th>What they do</th><th>Skills to build</th></tr></thead>
<tbody>
<tr><td>Cloud support associate</td><td>Helps users and fixes common problems</td><td>Customer care, basics of networks and accounts</td></tr>
<tr><td>Cloud administrator or engineer</td><td>Builds and runs cloud systems</td><td>Linux, networking, scripting, one provider in depth</td></tr>
<tr><td>DevOps or platform engineer</td><td>Automates building, testing and deploying software</td><td>Containers, automation tools, version control</td></tr>
<tr><td>Cloud security specialist</td><td>Protects accounts, data and systems</td><td>IAM, encryption, monitoring, risk</td></tr>
<tr><td>Solutions architect</td><td>Designs systems to fit needs and budgets</td><td>Broad service knowledge, communication</td></tr>
<tr><td>Data engineer</td><td>Builds data pipelines and warehouses</td><td>Databases, SQL, programming</td></tr>
<tr><td>FinOps analyst</td><td>Tracks and optimises cloud spend</td><td>Billing, spreadsheets, reporting</td></tr>
</tbody>
</table>
<p>Cloud skills also help non-technical roles such as project managers, trainers and sales staff.</p>
<h3>Certifications</h3>
<p>A <strong>certification</strong> is an exam-based credential showing you know a defined set of skills. The main providers each offer entry-level certificates that cover the fundamentals, and then associate and professional levels in areas such as administration, development, security and architecture. Independent organisations also offer vendor-neutral certificates on topics such as cloud basics, security and networking.</p>
<ul>
<li>A certificate helps employers see that you studied seriously, but it is not a replacement for hands-on practice.</li>
<li>Exams cost money, and policies and prices change. Check the official site for current information, and look for training discounts, sponsored programmes and scholarships.</li>
<li>Start with a fundamentals-level certificate in the provider you use or that is common in your market.</li>
</ul>
<h3>A learning path</h3>
<ol>
<li><strong>Foundations</strong>: computer basics, networking basics and the Linux command line.</li>
<li><strong>Cloud concepts</strong>: this course, plus the free fundamentals courses from major providers.</li>
<li><strong>Hands-on</strong>: use free tiers (with budget alerts), build and delete small projects, and keep notes.</li>
<li><strong>Specialise</strong>: choose administration, development, data, security or DevOps.</li>
<li><strong>Prove it</strong>: build a portfolio, write about what you learned, and consider a certificate.</li>
<li><strong>Join communities</strong>: local tech meetups, online forums and open source projects.</li>
</ol>
<p>Most providers publish free learning material and sandbox labs, and many universities and non-profit organisations offer low-cost courses. Choose resources that let you practise, not only watch videos.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose two roles from the table that interest you and list the skills you already have and those you lack.</li>
<li>Look at three real job advertisements for cloud-related roles and note the common requirements.</li>
<li>Write a six-month learning plan with one goal per month and one small project.</li>
<li>Find the official page for a fundamentals certificate and note the topics it covers.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Cloud careers include support, engineering, DevOps, security, architecture, data and cost management.</li>
<li>Certificates show knowledge, while projects show ability; do both.</li>
<li>Follow a path from foundations to concepts to hands-on practice and specialisation.</li>
<li>Free tiers, free courses and communities make learning affordable.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Designing for reliability',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to apply basic design ideas that keep a cloud service running when things go wrong. Failures are normal in any large system, so the goal is not to prevent every failure but to survive them gracefully.</p>
<h3>Key ideas</h3>
<ul>
<li><strong>Availability</strong>: the share of time a service works. A target of 99.9 percent allows roughly nine hours of downtime in a year; 99.99 percent allows under an hour. Each extra nine costs more.</li>
<li><strong>Reliability</strong>: the service does what it should, consistently.</li>
<li><strong>Durability</strong>: stored data is not lost.</li>
<li><strong>Resilience</strong>: the ability to recover quickly from failure.</li>
<li><strong>Disaster recovery (DR)</strong>: the plan to restore service after a major event, such as the loss of a data centre.</li>
</ul>
<h3>Removing single points of failure</h3>
<p>A <strong>single point of failure</strong> is one component whose failure stops everything. Look for them everywhere: one server, one database, one internet link, one person who knows the password.</p>
<ol>
<li>Run at least two copies of important components, in different availability zones.</li>
<li>Put a load balancer in front so traffic goes to healthy copies.</li>
<li>Use a managed database with automatic failover to a standby copy.</li>
<li>Store files in durable object storage and use versioning.</li>
<li>Keep documentation and access shared among a small trusted team.</li>
</ol>
<h3>Disaster recovery patterns</h3>
<table>
<thead><tr><th>Pattern</th><th>Description</th><th>Cost</th><th>Recovery speed</th></tr></thead>
<tbody>
<tr><td>Backup and restore</td><td>Keep backups elsewhere; rebuild after disaster</td><td>Lowest</td><td>Hours to days</td></tr>
<tr><td>Pilot light</td><td>Keep a minimal copy of the core running and grow it when needed</td><td>Low</td><td>Under an hour or so</td></tr>
<tr><td>Warm standby</td><td>A smaller but working copy always running</td><td>Medium</td><td>Minutes</td></tr>
<tr><td>Active-active</td><td>Full copies serving users in two regions</td><td>Highest</td><td>Near instant</td></tr>
</tbody>
</table>
<p>Pick the pattern from the RPO and RTO you set earlier, and from what the organisation can afford. For many small organisations, good automated backups and a tested restore plan are the right level.</p>
<h3>Good habits</h3>
<ul>
<li><strong>Automate</strong> setup so you can rebuild quickly and avoid manual mistakes.</li>
<li><strong>Test failure</strong>: practise a restore, and simulate loss of a server to see what happens.</li>
<li><strong>Plan for slow internet and power cuts</strong> locally, for example offline copies of critical documents and a backup connection.</li>
<li><strong>Write simple runbooks</strong>: step-by-step guides for common incidents.</li>
<li><strong>After an incident, hold a blame-free review</strong> and fix causes.</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Draw the parts of a simple website and mark every single point of failure.</li>
<li>For each one write how you would remove or reduce the risk.</li>
<li>Choose a disaster recovery pattern for a small school and justify the choice in terms of cost and recovery speed.</li>
<li>Write a short runbook of five steps for "the website is down".</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Failures are normal; design so that they do not stop the service.</li>
<li>Remove single points of failure using multiple zones, load balancers and failover.</li>
<li>Choose a disaster recovery pattern to match RPO, RTO and budget.</li>
<li>Automate, test and document so recovery works under pressure.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Capstone: a small NGO moves to the cloud',
                    'minutes' => 35,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to plan a realistic, safe and affordable move to the cloud for a small organisation by combining everything in this course. The scenario is the kind of task a junior cloud professional or a volunteer adviser may be asked to do.</p>
<h3>The scenario</h3>
<p>A community non-profit in northern Uganda has 12 staff and volunteers. It keeps documents, photos and beneficiary records on two office computers and a USB drive, hosts a small website at an expensive local host, uses personal e-mail accounts, and lost a month of reports when a laptop was stolen. Internet is available at the office but sometimes unstable. The budget is small and the manager wants security, easy teamwork and a clear monthly cost.</p>
<h3>Step 1: Discover</h3>
<ol>
<li>List all data: documents, photos, spreadsheets, beneficiary records, e-mail and the website.</li>
<li>Classify it: public (website content), internal (policies), confidential (donor details) and sensitive (beneficiary personal data).</li>
<li>List users and what each one needs.</li>
<li>Note constraints: budget, internet quality, skills and legal duties for personal data.</li>
</ol>
<h3>Step 2: Choose services</h3>
<table>
<thead><tr><th>Need</th><th>Cloud answer</th><th>Model</th></tr></thead>
<tbody>
<tr><td>E-mail and calendars with the organisation's domain</td><td>Hosted business e-mail suite</td><td>SaaS</td></tr>
<tr><td>Shared documents and files with permissions</td><td>Online file storage with shared folders and version history</td><td>SaaS</td></tr>
<tr><td>Website</td><td>Static site on object storage and a CDN, or a managed website platform</td><td>PaaS or static hosting</td></tr>
<tr><td>Beneficiary records</td><td>A managed database or a reputable data-collection tool with strong access control</td><td>PaaS or SaaS</td></tr>
<tr><td>Backups</td><td>Automated backups to a second location or account</td><td>Storage</td></tr>
</tbody>
</table>
<p>A small team should favour SaaS and managed services over running servers, because nobody has time to patch machines.</p>
<h3>Step 3: Secure it</h3>
<ul>
<li>Individual accounts for everyone, MFA on all, a password manager for the team.</li>
<li>Roles: two administrators, staff with folder-level access, volunteers with limited access.</li>
<li>Sensitive records restricted to named people with logging turned on.</li>
<li>Encryption on by default, secure devices and screen locks.</li>
<li>A short privacy notice and data protection policy, and a decision about the region and provider terms for personal data.</li>
</ul>
<h3>Step 4: Migrate</h3>
<ol>
<li>Clean up: delete duplicates and old files, name folders clearly.</li>
<li>Pilot with one team first and fix problems.</li>
<li>Upload files in batches, checking numbers and opening samples.</li>
<li>Move e-mail with a plan for the old addresses and a short overlap.</li>
<li>Move the website: copy files, test on a temporary address, connect the domain, enable HTTPS, then switch DNS.</li>
<li>Train all users in one short session with a one-page guide.</li>
<li>Keep the old copies, read-only, until everything is confirmed.</li>
</ol>
<h3>Step 5: Run and review</h3>
<ul>
<li>Budget alert, a monthly bill check, and a list of who owns each service.</li>
<li>A weekly or monthly backup restore test.</li>
<li>An offline plan for bad internet: critical documents synced to laptops for offline reading.</li>
<li>A runbook: who to call, what to check if the site or e-mail fails.</li>
<li>A six-month review on cost, use and problems.</li>
</ul>
<h3>Capstone checklist</h3>
<pre>[ ] Data inventory and classification complete
[ ] Services chosen with reasons (SaaS first)
[ ] Region and provider terms checked for personal data
[ ] MFA and least-privilege roles set
[ ] Backups follow 3-2-1 and a restore was tested
[ ] Budget and alerts set, owners named
[ ] Migration plan with pilot, rollback and training
[ ] Monitoring and a simple runbook in place
[ ] Review date in the calendar</pre>
<h3>Try it yourself</h3>
<ol>
<li>Write a one-page plan for the NGO using the five steps and the checklist.</li>
<li>Estimate monthly costs by looking at the public prices of two providers for storage, e-mail seats and hosting. State your assumptions.</li>
<li>List the three biggest risks and how you will reduce each.</li>
<li>Present your plan to a friend in five minutes and note their questions.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Start by discovering and classifying data, users and constraints.</li>
<li>Prefer SaaS and managed services for small teams, and secure accounts from day one.</li>
<li>Migrate in stages with a pilot, tests and a rollback.</li>
<li>Keep running costs, backups and reviews on a schedule.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which role is mainly about designing systems to fit needs and budgets?
- Cloud support associate
- FinOps analyst
* Solutions architect
- Data entry clerk
= Architects choose and combine services to meet requirements and cost goals.

Q: How should a certificate be viewed?
- It replaces any hands-on practice
* It shows knowledge, and works best together with real projects
- It is required for all cloud jobs
- It never has any value
= Employers value both evidence of study and practical ability.

Q: A service is available 99.9 percent of the time. Roughly how much downtime per year is that?
- Under one minute
* About nine hours
- About three months
- None
= 0.1 percent of a year is about 8.8 hours.

Q: What is a single point of failure?
- A password manager
* A component whose failure stops the whole service
- A backup
- A budget alert
= Remove them by adding copies and failover.

Q: Which disaster recovery pattern is cheapest but slowest?
* Backup and restore
- Warm standby
- Active-active
- Pilot light
= It keeps only backups and rebuilds after a disaster.

Q: For a small NGO with limited IT skills, which is the best general approach?
- Run its own servers in the office
* Prefer SaaS and managed services, with MFA and backups
- Share one administrator password
- Avoid the cloud and keep one USB drive
= Managed services reduce maintenance, and strong access controls protect data.

Q: Which should be done before switching the website's DNS to a new host? (choose all that apply)
* Test it on a temporary address
* Prepare HTTPS
- Delete the old copy immediately
- Tell nobody
= Test and prepare first, and keep the old copy until the new site is confirmed.

Q: Why test restoring a backup?
- It makes the backup smaller
* To prove the backup works and the team knows how to use it
- It encrypts the data
- To lower the monthly bill
= An untested backup may fail when it is needed most.
QUIZ,
        ],
    ],

    'final_exam' => <<<'QUIZ'
Q: Which statement best describes cloud computing?
- Software that works without any internet
* Renting computing resources over the internet and paying for use
- Buying servers and keeping them in the office
- A type of weather forecast
= Cloud means on-demand, metered services from a provider's data centres.

Q: Why can cloud providers offer low prices?
- They never spend on equipment
* They share expensive infrastructure between many customers
- They do not pay for electricity
- They only serve small customers
= Economies of scale reduce cost per customer.

Q: What does a hypervisor do?
- Connects to Wi-Fi
- Creates passwords
* Splits a physical computer into isolated virtual machines
- Cools the room
= Virtualisation is the foundation of cloud efficiency.

Q: Which service model gives you a finished application to use?
- IaaS
- PaaS
* SaaS
- Hybrid
= SaaS delivers software such as web e-mail or online office tools.

Q: Which service model leaves you responsible for the operating system?
* IaaS
- SaaS
- PaaS
- None of them
= With IaaS you manage the OS and software on the virtual machine.

Q: Which describes a hybrid cloud?
- Only a public provider is used
- Only one office computer is used
* Private systems connected with public cloud services
- Two e-mail accounts
= Hybrid joins different environments for different needs.

Q: Why choose a region near your users? (choose all that apply)
* Lower delay (latency)
* Legal and data location needs
- To avoid paying for anything
- To make passwords unnecessary
= Region affects speed, law and sometimes price.

Q: What is a major risk of using the cloud?
- Hardware never fails
* Misconfiguration and unexpected costs
- Unlimited free storage
- No need for the internet
= Customers still need to configure and monitor services properly.

Q: Which compute option is billed for the time a triggered function runs?
- Virtual machine
- Bare-metal server
* Serverless function
- Private rack
= Serverless charges for actual execution.

Q: Which storage type resembles a hard drive attached to a server?
- Object storage
* Block storage
- A CDN
- DNS
= Block storage provides raw disks for VMs.

Q: Which storage type is most suitable for a large archive of photos and videos?
* Object storage
- Block storage attached to a small VM
- RAM
- An in-memory cache
= Object storage is cheap, durable and scalable for files.

Q: Which database type uses tables with rows and columns linked by keys?
- Object storage
- A CDN
* Relational database
- A load balancer
= Relational databases suit structured data with relationships.

Q: What is the purpose of a CDN?
- To manage staff accounts
* To serve content from servers close to visitors
- To back up the database
- To create virtual machines
= Edge copies make pages load faster and reduce load.

Q: In IAM, which practice is best?
- Everyone shares the root login
* Each person has their own account with only the permissions they need
- Keep unused accounts open
- Disable logging
= Individual accounts and least privilege reduce risk and give accountability.

Q: When comparing providers, what should you do first?
- Choose the one with the best advertisement
* List your needs and compare the services, regions and prices that meet them
- Pick the cheapest compute only
- Avoid reading terms
= A needs-based comparison avoids poor choices.

Q: Which is a safe way to use a free tier?
- Add many users to one account
- Ignore the limits
* Set budget alerts, read the rules and delete resources after practice
- Leave servers on for months
= Free tiers have limits and a forgotten resource may be billed.

Q: Under shared responsibility, the customer is always responsible for...
- The provider's buildings
- The provider's hypervisor
* Their data, access and configuration choices
- The data centre's cooling
= These stay with the customer in every service model.

Q: What does least privilege mean?
- Giving permissions to everybody to save time
* Giving only the permissions needed for a task
- Having one administrator for all companies
- Removing logging
= It limits the impact of mistakes and stolen accounts.

Q: Which MFA method is generally stronger than text messages?
- A shared PIN
* An authenticator app or security key
- Writing the password on paper
- Using the same password twice
= App and key methods are harder to intercept.

Q: What does encryption at rest protect?
- Data moving over a network
* Data stored on disks, in databases or buckets
- Only the website address
- Only the passwords of staff
= Without the key, stolen stored data cannot be read.

Q: Which is the 3-2-1 backup rule?
- 3 users, 2 passwords, 1 disk
* 3 copies, 2 types of storage, 1 off-site
- 3 days, 2 weeks, 1 month
- 3 servers in one room
= An off-site copy protects against loss of the main site.

Q: A company can accept losing at most 15 minutes of data. What is that target called?
- RTO
* RPO
- CDN
- IAM
= RPO is the maximum acceptable data loss measured in time.

Q: Which is true about personal data in the cloud?
- The provider alone is always responsible for it
* The organisation that collects it remains responsible, even if a provider stores it
- Laws never apply to cloud data
- Data location is irrelevant
= Controllers keep responsibility and must check provider terms and locations.

Q: What is a safe step when hosting a static website from a storage bucket?
- Make everything in the account public
* Allow public read access only for the website files
- Store passwords in the page
- Disable HTTPS
= Limit public access and use HTTPS.

Q: Which are examples of monitoring data? (choose all that apply)
* Metrics
* Logs
- Domain registrations
- Marketing slogans
= Metrics, logs and traces show how a system behaves.

Q: What is auto scaling?
- Manually restarting a server
* Rules that add or remove capacity automatically as demand changes
- Encrypting a database
- Buying a certificate
= It matches capacity to demand to protect speed and cost.

Q: Which habit most helps cost control?
- Leaving unused test machines running
* Setting budgets and alerts and deleting unused resources
- Never reviewing bills
- Always choosing the largest size
= Visibility and clean-up prevent waste.

Q: Which task suits serverless functions?
- A program that runs heavy calculations all day without pause
* Sending a confirmation e-mail after a form is submitted
- Running a large legacy desktop program
- Storing 10 TB of video
= Short event-driven tasks fit the serverless model.

Q: What should a small NGO with limited IT staff usually prefer?
- Running several servers itself
* SaaS and managed services with strong account security
- Sharing one login
- Avoiding backups
= Managed services reduce maintenance and risk.

Q: A design with one server, one database and no backup has what weakness?
- Too many zones
* Single points of failure
- Too much monitoring
- Excess redundancy
= Add copies, failover and backups to survive failures.
QUIZ,
];
