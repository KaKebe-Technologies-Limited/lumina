<?php
return [
    'slug'        => 'devops-docker-ci-cd',
    'title'       => 'DevOps Essentials: Docker, CI/CD and Deployment',
    'category'    => 'cloud',
    'level'       => 'advanced',
    'icon'        => 'layers',
    'sort'        => 490,
    'summary'     => 'Package an app in Docker, test and ship it with an automated GitHub Actions pipeline, then run it safely with logs, alerts, backups and rollback. Ends with a hands-on capstone.',
    'description' => '<p>Writing code is only half of software work. The other half is getting that code to real users reliably, quickly and safely, and keeping it running when something goes wrong. DevOps is the set of habits, teams and tools that make this possible. Companies in Kampala, Nairobi and across the world hire people who can build and ship, not only people who can write code on their own laptop.</p><p>In this advanced course you will learn what DevOps means and how software moves from idea to users. You will learn containers and Docker (images, Dockerfiles, volumes, ports, environment variables and Compose), then continuous integration and delivery with GitHub Actions, automated tests, environments and secrets. You will deploy to a cloud server, understand web servers and reverse proxies, and learn logging, monitoring, backups, rollback and infrastructure as code.</p><p>The course finishes with security basics, free-tier costs, teamwork and incident response, and a capstone where you containerise a small web app and ship it with an automated pipeline. You need a computer with internet access and the willingness to type commands; every tool used has a free option.</p>',
    'outcomes'    => [
        'Explain what DevOps is and describe the stages of the software delivery lifecycle',
        'Compare containers with virtual machines and choose between them for a given job',
        'Write a Dockerfile and run containers with ports, volumes and environment variables',
        'Describe and write a simple docker compose file for an app with a database',
        'Build a GitHub Actions pipeline that checks out code, runs automated tests and builds the app',
        'Manage development, staging and production environments with configuration kept outside the code',
        'Keep secrets out of repositories and apply basic security checks in a pipeline',
        'Deploy a containerised app to a cloud server or platform and place a reverse proxy in front of it',
        'Read logs, set up basic monitoring and alerts, and plan backups and rollback',
        'Work with a team during an incident and write a short blameless review',
    ],
    'requirements' => ['Completed Linux and the Command Line (linux-command-line)', 'Completed Git and GitHub: Version Control and Teamwork (git-github-collaboration)', 'A computer that can run Docker (or use a free online tool such as Play with Docker or a GitHub Codespace), and a free GitHub account'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Docker Documentation', 'url' => 'https://docs.docker.com/'],
        ['title' => 'GitHub Actions Documentation', 'url' => 'https://docs.github.com/actions'],
        ['title' => 'The Twelve-Factor App (methodology for modern apps)', 'url' => 'https://12factor.net/'],
        ['title' => 'OWASP: Open Worldwide Application Security Project', 'url' => 'https://owasp.org/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: DevOps foundations',
            'summary' => 'Understand what DevOps is, how software travels from idea to users, and why containers changed the way we package apps.',
            'lessons' => [
                [
                    'title'   => 'What DevOps is and why it exists',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what DevOps means and why teams adopt it. This matters because almost every software job today expects you to understand how code reaches users, not only how to write it.</p>

<h3>The old problem: the wall</h3>
<p>In many older companies there were two separate groups. <strong>Developers</strong> wrote new features and wanted to release them quickly. <strong>Operations</strong> staff kept the servers running and wanted nothing to change, because every change could break something. Developers "threw the code over the wall" and operations had to make it work on servers they did not fully understand. Releases were rare, big, stressful and often late at night.</p>

<h3>What DevOps means</h3>
<p><strong>DevOps</strong> joins the two words <em>development</em> and <em>operations</em>. It is a way of working in which one team shares responsibility for building, releasing and running software. It is not a job title you can buy or a single tool. It has three parts that support each other:</p>
<ul>
<li><strong>Culture:</strong> shared ownership, honest communication, learning from mistakes without blaming people.</li>
<li><strong>Practices:</strong> small frequent releases, automated testing, automated deployment, monitoring, and everything written down as code.</li>
<li><strong>Tools:</strong> Git, containers, pipelines, monitoring systems. Tools help, but they cannot fix a team that does not talk.</li>
</ul>

<h3>Why small and frequent is safer</h3>
<p>It sounds risky to release often, but the opposite is true. If you change ten lines and something breaks, you know where to look. If you release three months of work at once and something breaks, the cause could be anywhere. Frequent releases also mean users get fixes and features sooner.</p>
<table>
<thead><tr><th>Old way</th><th>DevOps way</th></tr></thead>
<tbody>
<tr><td>Release every few months</td><td>Release many times a week or day</td></tr>
<tr><td>Manual testing and manual copying of files</td><td>Automated tests and automated deployment</td></tr>
<tr><td>"It works on my laptop"</td><td>Same packaged app runs everywhere</td></tr>
<tr><td>Operations finds problems first from angry users</td><td>Monitoring and alerts find problems first</td></tr>
<tr><td>Blame when things break</td><td>Learn and improve the process</td></tr>
</tbody>
</table>

<h3>A mini-case</h3>
<p>A Kampala start-up sells school fee reminders by SMS. Its one developer copies files to the server over FTP every Friday evening. One Friday a typing mistake deletes the config file and the system is down all weekend, so schools miss their reminders. With a DevOps approach the same developer pushes code to GitHub, a pipeline runs tests, and a script deploys the tested version in two minutes. If it breaks, the previous version comes back with one command. The team did not get smarter overnight; the process got safer.</p>

<blockquote><p><strong>Quick check:</strong> Why are small, frequent releases usually safer than rare, big ones?</p><p>Answer: When a release is small, a failure has few possible causes and is quick to find and undo, whereas a huge release hides the cause among many changes.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A manager says "we bought a DevOps tool, so now we do DevOps". What is missing from that idea?</p><p>Answer: DevOps is also culture and practice, such as shared ownership and automation habits, so a tool alone does not make a team DevOps.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Think of one project you made, even a school assignment, and write down how you would get it to a user today.</li>
<li>Mark every step that is manual and could be forgotten or done wrongly.</li>
<li>Write one sentence on what could go wrong in each manual step.</li>
<li>Keep this list; you will automate those steps during the course.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>DevOps means shared responsibility for building, releasing and running software.</li>
<li>It combines culture, practices and tools; tools alone are not enough.</li>
<li>Small frequent releases reduce risk and speed up feedback.</li>
<li>Automation replaces fragile manual steps with repeatable ones.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'The software delivery lifecycle',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to name the stages that software passes through from idea to running service, and say which stages can be automated. This matters because you can only improve a process you can see clearly.</p>

<h3>The stages</h3>
<p>Teams draw the lifecycle as a loop, because after software is running we learn from it and start again.</p>
<ol>
<li><strong>Plan:</strong> decide what to build and write it as small tasks (issues or tickets).</li>
<li><strong>Code:</strong> write the change on a Git branch.</li>
<li><strong>Build:</strong> turn the code into something runnable, such as a package or a container image.</li>
<li><strong>Test:</strong> run automated checks to prove the change works and has not broken old features.</li>
<li><strong>Release:</strong> mark a tested version as ready, usually with a version number or tag.</li>
<li><strong>Deploy:</strong> put that version on the servers where users reach it.</li>
<li><strong>Operate:</strong> keep it running, handle load, backups and updates.</li>
<li><strong>Monitor:</strong> watch health and errors, and gather feedback, which becomes the next plan.</li>
</ol>

<h3>Where automation helps most</h3>
<table>
<thead><tr><th>Stage</th><th>Typical automation</th></tr></thead>
<tbody>
<tr><td>Code</td><td>Formatting and style checks when you commit</td></tr>
<tr><td>Build</td><td>A pipeline builds the app on every push</td></tr>
<tr><td>Test</td><td>Unit tests run automatically on every pull request</td></tr>
<tr><td>Deploy</td><td>A script or pipeline job copies and starts the new version</td></tr>
<tr><td>Monitor</td><td>Alerts message the team when errors rise</td></tr>
</tbody>
</table>

<h3>CI, CD and continuous deployment</h3>
<p>Three terms appear everywhere. <strong>Continuous integration (CI)</strong> means every change is merged often and automatically built and tested. <strong>Continuous delivery (CD)</strong> means every change that passes the tests is kept ready to release at any time; a person presses the button. <strong>Continuous deployment</strong> goes one step further: a passing change is released to users automatically with no button. Choose the level that fits the risk. A school portal may use continuous delivery with a human approval, while a personal blog can deploy automatically.</p>

<h3>Feedback loops</h3>
<p>The most important idea is speed of feedback. A bug found by an automated test two minutes after you commit costs a few minutes to fix. The same bug found by a customer two weeks later costs reputation, money and a stressful evening. Each stage exists to catch problems as early and cheaply as possible.</p>

<h3>A pipeline in one picture</h3>
<pre><code>commit, build, test, package, deploy to staging, approve, deploy to production</code></pre>
<p>Each arrow is a gate: if a step fails, the change stops and nobody is hurt.</p>

<blockquote><p><strong>Quick check:</strong> What is the difference between continuous delivery and continuous deployment?</p><p>Answer: In continuous delivery a person decides when to release a tested change, while in continuous deployment every passing change goes to users automatically.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why do teams run tests on every push instead of once a month?</p><p>Answer: Problems are found within minutes while the change is fresh in the author's mind, which makes fixes cheap.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Draw the eight lifecycle stages on paper as a circle.</li>
<li>Next to each stage write who does it in your own project or in a team you know.</li>
<li>Circle the two stages that cause you the most waiting or worry.</li>
<li>Write one idea for automating each circled stage.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Delivery is a loop: plan, code, build, test, release, deploy, operate, monitor.</li>
<li>CI tests every change; CD keeps every passing change ready; continuous deployment releases it automatically.</li>
<li>Fast feedback makes problems cheap to fix.</li>
<li>Choose how automatic releases should be based on risk.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Containers versus virtual machines',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what a container is, how it differs from a virtual machine, and when to use each. This matters because containers are the standard way to package modern applications, and interviewers ask about them often.</p>

<h3>The "it works on my laptop" problem</h3>
<p>An app needs more than your code. It needs a language version, libraries, settings and sometimes system tools. If your laptop has Node 20 and the server has Node 14, the app may crash. For years teams solved this with long installation documents that were always out of date. The better answer is to package the app together with everything it needs, so it runs the same everywhere.</p>

<h3>Virtual machines</h3>
<p>A <strong>virtual machine (VM)</strong> is a complete pretend computer running inside a real one. Software called a <em>hypervisor</em> shares the real hardware between several VMs. Each VM has its own full operating system, which makes it strongly isolated but heavy: it can take gigabytes of disk space and minutes to start.</p>

<h3>Containers</h3>
<p>A <strong>container</strong> is a packaged app that runs as an isolated process on the host computer. Containers do not each carry a whole operating system. They share the host's <em>kernel</em> (the core of the operating system) and only include the app, its libraries and its files. They are small, often tens or a few hundred megabytes, and start in seconds. Linux features keep containers separated from each other, so one cannot see the files or processes of another unless you allow it.</p>

<table>
<thead><tr><th>Feature</th><th>Virtual machine</th><th>Container</th></tr></thead>
<tbody>
<tr><td>Contains</td><td>Full operating system plus app</td><td>App plus its libraries only</td></tr>
<tr><td>Size</td><td>Gigabytes</td><td>Megabytes to a few hundred megabytes</td></tr>
<tr><td>Start time</td><td>Minutes</td><td>Seconds</td></tr>
<tr><td>Isolation</td><td>Very strong (separate kernel)</td><td>Good (shared kernel)</td></tr>
<tr><td>Best for</td><td>Different operating systems, strong separation</td><td>Packaging and scaling apps</td></tr>
</tbody>
</table>

<h3>Why teams love containers</h3>
<ul>
<li><strong>Consistency:</strong> the same image runs on your laptop, in testing and in production.</li>
<li><strong>Speed:</strong> start, stop and replace in seconds.</li>
<li><strong>Efficiency:</strong> many containers fit on one cheap server, which matters when a server costs real UGX every month.</li>
<li><strong>Clean machines:</strong> you do not install databases and languages directly on your computer; you run them in containers and delete them when finished.</li>
</ul>

<h3>Containers and VMs work together</h3>
<p>They are not enemies. A cloud server you rent is usually a VM, and you run containers inside it. Docker is the most popular tool for building and running containers. On Windows and Mac, Docker Desktop runs a small Linux environment so Linux containers work. A free alternative is to practise in your browser with an online sandbox, or in a GitHub Codespace.</p>

<h3>Scenario</h3>
<p>A small shop in Mbarara runs a stock app and a separate reports app on one rented server. They need different versions of Python. Installing both directly causes conflicts. Putting each in its own container, each with its own Python, solves it with no extra server cost.</p>

<blockquote><p><strong>Quick check:</strong> Why does a container start much faster than a virtual machine?</p><p>Answer: A container shares the host's kernel and carries no full operating system, so there is little to boot.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You must run a Windows-only program next to a Linux app on one Linux host. Which fits better, a container or a VM?</p><p>Answer: A VM, because containers share the host kernel and cannot run a different operating system.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Find out whether your computer can run Docker: search for "Docker Desktop system requirements" on the official docs site.</li>
<li>If it cannot, open a GitHub Codespace or an online Docker sandbox instead.</li>
<li>Write three jobs from your own life that would suit containers.</li>
<li>Write one job that would need a full VM.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A container packages an app with its libraries and shares the host kernel.</li>
<li>A VM carries a whole operating system, so it is heavier but more isolated.</li>
<li>Containers give consistency, speed and efficient use of servers.</li>
<li>Servers are often VMs that run many containers.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A team releases once every four months and each release breaks something. What change would reduce risk most?
- Hire more people to work on the release night
* Release smaller changes more often, with automated tests
- Release even less often so there is more time to prepare
- Stop testing so releases are faster
= Small frequent releases make failures easy to locate and undo, and automation makes the process repeatable.

Q: Which statement best describes DevOps?
- A job title for the person who manages servers
- A tool that deploys code
* Shared responsibility for building, releasing and running software, supported by practices and tools
- A programming language for servers
= DevOps is culture and practice first; tools support it.

Q: Place these stages in the usual order: test, plan, deploy, build.
- Build, plan, test, deploy
- Plan, test, build, deploy
- Deploy, plan, build, test
* Plan, build, test, deploy
= You plan the work, build the code into something runnable, test it, then deploy the tested version.

Q: Every change that passes tests is kept ready to release, but a manager clicks a button to actually release. Which practice is this?
* Continuous delivery
- Continuous deployment
- Manual testing
- Continuous integration only
= With continuous delivery a person chooses when to release. Continuous deployment would release automatically.

Q: Which is TRUE about containers compared with virtual machines?
- A container carries its own full operating system
* A container shares the host's kernel, so it is smaller and starts faster
- Containers can run any operating system on any host
- Containers are always more isolated than VMs
= Sharing the kernel makes containers light, but also means they cannot be a different operating system from the host.

Q: Your app works on your laptop but crashes on the server because of a different Python version. What is the best long-term fix?
- Ask the server administrator to install your version each time
- Write a longer installation document
* Package the app with its Python version in a container image
- Stop using Python
= An image carries the correct versions with the app, so it runs the same everywhere.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Docker in practice',
            'summary' => 'Build and run containers: images, Dockerfiles, ports, volumes, environment variables and Docker Compose.',
            'lessons' => [
                [
                    'title'   => 'Images and containers: your first commands',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to pull an image, run a container, list and stop containers and clean up. This matters because these few commands are what you use every day when working with Docker.</p>

<h3>Image or container?</h3>
<p>People mix these two words up. An <strong>image</strong> is a read-only template: the app, its libraries and the start command, frozen like a recipe. A <strong>container</strong> is a running copy made from an image, like a dish cooked from the recipe. You can start many containers from one image. Images are stored in a <strong>registry</strong>; the default public one is Docker Hub.</p>

<h3>Check Docker works</h3>
<pre><code>docker --version
docker run hello-world</code></pre>
<p>The second command downloads a tiny test image, runs it, prints a welcome message and exits. If you see the message, Docker is ready.</p>

<h3>Run a web server in one line</h3>
<pre><code>docker run -d --name myweb -p 8080:80 nginx</code></pre>
<p>Open <code>http://localhost:8080</code> in your browser and you should see the Nginx welcome page. The parts mean:</p>
<ul>
<li><code>-d</code> runs it in the background ("detached").</li>
<li><code>--name myweb</code> gives the container a friendly name.</li>
<li><code>-p 8080:80</code> maps port 8080 on your computer to port 80 inside the container.</li>
<li><code>nginx</code> is the image name. Docker pulls it from the registry if you do not have it.</li>
</ul>

<h3>The everyday commands</h3>
<table>
<thead><tr><th>Command</th><th>What it does</th></tr></thead>
<tbody>
<tr><td><code>docker ps</code></td><td>List running containers (add <code>-a</code> to include stopped ones)</td></tr>
<tr><td><code>docker images</code></td><td>List images stored on your computer</td></tr>
<tr><td><code>docker logs myweb</code></td><td>Show what the container printed</td></tr>
<tr><td><code>docker exec -it myweb sh</code></td><td>Open a shell inside the running container</td></tr>
<tr><td><code>docker stop myweb</code></td><td>Stop the container politely</td></tr>
<tr><td><code>docker rm myweb</code></td><td>Delete a stopped container</td></tr>
<tr><td><code>docker rmi nginx</code></td><td>Delete an image</td></tr>
</tbody>
</table>

<h3>Containers are disposable</h3>
<p>When you remove a container, anything written inside it is gone. This is a feature: you can throw a container away and start a fresh one from the image in seconds. The next lessons show how to keep important data safe using volumes.</p>

<h3>Tags choose the version</h3>
<p>An image name can carry a <strong>tag</strong> after a colon, such as <code>nginx:1.27</code> or <code>node:20-alpine</code>. Without a tag, Docker uses <code>latest</code>, which can change without warning. For dependable builds, name a specific version.</p>

<blockquote><p><strong>Quick check:</strong> You run <code>docker run -d -p 3000:80 nginx</code>. Which URL on your own computer reaches the server?</p><p>Answer: <code>http://localhost:3000</code>, because the left number is the port on your computer and the right number is the port inside the container.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is the difference between an image and a container?</p><p>Answer: An image is the read-only template, while a container is a running instance created from it.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Run <code>docker run hello-world</code> and read the message.</li>
<li>Start Nginx with <code>docker run -d --name myweb -p 8080:80 nginx</code> and open it in your browser.</li>
<li>Run <code>docker ps</code>, then <code>docker logs myweb</code> and find the line for your browser visit.</li>
<li>Stop and remove it with <code>docker stop myweb</code> then <code>docker rm myweb</code>, and confirm with <code>docker ps -a</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>An image is a template; a container is a running copy.</li>
<li><code>docker run</code> pulls (if needed) and starts a container; <code>-p</code> publishes a port.</li>
<li>Use <code>ps</code>, <code>logs</code>, <code>exec</code>, <code>stop</code> and <code>rm</code> daily.</li>
<li>Pin image tags instead of relying on <code>latest</code>.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Writing a Dockerfile',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a Dockerfile for a small web app, build an image from it and run it. This matters because the Dockerfile is the recipe that makes your app portable, and it lives in Git next to your code.</p>

<h3>The sample app</h3>
<p>Imagine a small Node.js app with a file <code>server.js</code> that listens on port 3000 and a <code>package.json</code> that lists its libraries. The same ideas apply to Python, PHP or any language.</p>

<h3>A Dockerfile, line by line</h3>
<pre><code>FROM node:20-alpine
WORKDIR /app
COPY package*.json ./
RUN npm ci --omit=dev
COPY . .
EXPOSE 3000
CMD ["node", "server.js"]</code></pre>
<table>
<thead><tr><th>Instruction</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td><code>FROM</code></td><td>The starting image. <code>alpine</code> versions are small.</td></tr>
<tr><td><code>WORKDIR</code></td><td>The folder inside the image where later commands run</td></tr>
<tr><td><code>COPY</code></td><td>Copy files from your project into the image</td></tr>
<tr><td><code>RUN</code></td><td>Run a command while building, such as installing libraries</td></tr>
<tr><td><code>EXPOSE</code></td><td>Documents the port the app uses (it does not publish it)</td></tr>
<tr><td><code>CMD</code></td><td>The command that starts when a container runs</td></tr>
</tbody>
</table>

<h3>Why the order matters</h3>
<p>Docker builds an image in <strong>layers</strong>, one per instruction, and reuses a layer if nothing above it changed. Notice that we copy only <code>package*.json</code> first and install libraries, and only then copy the rest of the code. Your code changes often, the library list rarely. This way a code edit reuses the slow install layer and the rebuild takes seconds instead of minutes.</p>

<h3>Ignore what does not belong</h3>
<p>Create a file named <code>.dockerignore</code> listing things that must not go into the image:</p>
<pre><code>node_modules
.git
.env
*.log</code></pre>
<p>This keeps the image small and, importantly, keeps secret files such as <code>.env</code> out of it.</p>

<h3>Build and run</h3>
<pre><code>docker build -t myapp:1.0 .
docker run -d --name myapp -p 8080:3000 myapp:1.0</code></pre>
<p><code>-t</code> names (tags) the image; the final dot means "use the current folder". Then open <code>http://localhost:8080</code>.</p>

<h3>Good habits</h3>
<ul>
<li>Start from a small, official, version-pinned base image.</li>
<li>Run as a normal user rather than root when the base image allows it (for example <code>USER node</code> on Node images).</li>
<li>Keep one main process per container.</li>
<li>Never write passwords into a Dockerfile; anyone with the image can read them.</li>
</ul>

<h3>Scenario</h3>
<p>Amina's build takes four minutes every time she fixes a typo. She had written <code>COPY . .</code> before <code>RUN npm ci</code>. After she swaps the order as shown above, the install layer is reused and the rebuild takes ten seconds.</p>

<blockquote><p><strong>Quick check:</strong> Why copy <code>package*.json</code> and install libraries before copying the rest of the code?</p><p>Answer: Docker can reuse the cached install layer when only your code changed, which makes rebuilds much faster.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Does <code>EXPOSE 3000</code> make the app reachable from your browser?</p><p>Answer: No, it only documents the port; you must publish it with <code>-p</code> when running the container.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a folder with a tiny <code>server.js</code> that answers "Hello from Docker" on port 3000 (or use any small app you already have).</li>
<li>Add the Dockerfile and <code>.dockerignore</code> shown above.</li>
<li>Build with <code>docker build -t myapp:1.0 .</code> and run it with a port mapping.</li>
<li>Edit one line of code and rebuild; watch which steps say CACHED.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A Dockerfile lists the steps to build an image; each step is a cached layer.</li>
<li>Put rarely changing steps first and frequently changing ones last.</li>
<li>Use <code>.dockerignore</code> to keep junk and secrets out.</li>
<li><code>docker build -t name:tag .</code> builds; <code>docker run</code> starts it.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Ports, volumes and environment variables',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to connect a container to the outside world, keep its data after it is deleted, and change its settings without rebuilding the image. This matters because real apps need to be reached by users, to store data safely, and to behave differently in testing and production.</p>

<h3>Ports</h3>
<p>A container has its own network. The app inside listens on a port such as 3000, but nobody outside can reach it until you <strong>publish</strong> the port with <code>-p HOST:CONTAINER</code>. With <code>-p 8080:3000</code>, traffic to port 8080 on the computer goes to port 3000 in the container. If two containers both want host port 8080, one must use a different host port.</p>

<h3>Volumes: data that survives</h3>
<p>Anything a container writes to its own filesystem disappears when the container is removed. For a database, uploaded photos or logs, that is unacceptable. A <strong>volume</strong> is storage managed by Docker that lives outside any one container.</p>
<pre><code>docker volume create shopdata
docker run -d --name db -v shopdata:/var/lib/postgresql/data -e POSTGRES_PASSWORD=change-me postgres:16</code></pre>
<p>Now you can delete and recreate the <code>db</code> container and the data in <code>shopdata</code> is still there. A second kind is a <strong>bind mount</strong>, which links a folder from your computer into the container, for example <code>-v "$PWD":/app</code>. It is handy in development because edits on your computer appear instantly inside the container.</p>
<table>
<thead><tr><th>Type</th><th>Where data lives</th><th>Use for</th></tr></thead>
<tbody>
<tr><td>Named volume</td><td>Managed by Docker</td><td>Databases and production data</td></tr>
<tr><td>Bind mount</td><td>A folder you choose on your computer</td><td>Live code editing in development</td></tr>
<tr><td>Nothing (container filesystem)</td><td>Inside the container only</td><td>Temporary files you can lose</td></tr>
</tbody>
</table>

<h3>Environment variables: settings from outside</h3>
<p>The same image should run in testing and production. What changes are settings, such as the database address or a mode flag. You pass them in as <strong>environment variables</strong>:</p>
<pre><code>docker run -d -p 8080:3000 -e APP_ENV=staging -e DB_HOST=db myapp:1.0
docker run -d -p 8080:3000 --env-file .env myapp:1.0</code></pre>
<p>The app reads them when it starts (in Node, <code>process.env.APP_ENV</code>). With many settings, put them in a file named <code>.env</code> with one <code>NAME=value</code> per line and use <code>--env-file</code>. Never commit that file to Git if it holds passwords.</p>

<h3>Scenario</h3>
<p>Brian runs a small clinic booking app with its database in a container and no volume. After a server restart he recreates the container and all bookings vanish. He adds a named volume, restarts again, and the bookings remain. Lesson learned: if the data matters, give it a volume and back it up.</p>

<h3>Common mistakes</h3>
<ul>
<li>Forgetting <code>-p</code> and wondering why the browser cannot connect.</li>
<li>Storing a database inside the container with no volume.</li>
<li>Baking passwords into the image instead of passing them at run time.</li>
</ul>

<blockquote><p><strong>Quick check:</strong> You delete a database container that had a named volume attached. Is the data lost?</p><p>Answer: No, the named volume lives outside the container, so a new container using the same volume sees the data again.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why pass a database address as an environment variable instead of writing it in the code?</p><p>Answer: The same image can then run in different environments with different settings and no rebuild.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Run <code>docker run -d --name keeper -v notes:/data alpine sh -c "echo hello &gt; /data/a.txt; sleep 300"</code>.</li>
<li>Remove it with <code>docker rm -f keeper</code>.</li>
<li>Run <code>docker run --rm -v notes:/data alpine cat /data/a.txt</code> and confirm "hello" is still there.</li>
<li>Run <code>docker run --rm -e GREETING=Habari alpine sh -c 'echo $GREETING'</code> and observe the variable.</li>
<li>Clean up with <code>docker volume rm notes</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li><code>-p HOST:CONTAINER</code> publishes a port.</li>
<li>Volumes keep data after a container is removed; use them for databases.</li>
<li>Environment variables carry settings so one image fits every environment.</li>
<li>Do not put secrets in images or in Git.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Docker Compose: several containers together',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe a multi-container app in one file and start it with one command. This matters because real apps are rarely one container: a website usually needs at least an app and a database, and typing long <code>docker run</code> commands for each is slow and error-prone.</p>

<h3>What Compose is</h3>
<p><strong>Docker Compose</strong> reads a file named <code>compose.yaml</code> (the older name <code>docker-compose.yml</code> also works) that describes your <strong>services</strong>, their images, ports, volumes and settings. Modern Docker includes it as the command <code>docker compose</code>.</p>

<h3>An app with a database</h3>
<pre><code>services:
  web:
    build: .
    ports:
      - "8080:3000"
    environment:
      DB_HOST: db
      DB_USER: shop
      DB_PASSWORD: ${DB_PASSWORD}
    depends_on:
      - db
  db:
    image: postgres:16
    environment:
      POSTGRES_USER: shop
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    volumes:
      - dbdata:/var/lib/postgresql/data

volumes:
  dbdata:</code></pre>
<p>Read it slowly. The <code>web</code> service is built from the Dockerfile in the current folder and published on port 8080. The <code>db</code> service uses the official Postgres image and stores data in the named volume <code>dbdata</code>. YAML uses spaces for structure, never tabs, so indentation must be exact.</p>

<h3>Things worth noticing</h3>
<ul>
<li><strong>Service names are addresses.</strong> Compose creates a private network and lets services find each other by name. The app connects to host <code>db</code>, not to an IP address.</li>
<li><strong>${DB_PASSWORD}</strong> is filled from a <code>.env</code> file next to <code>compose.yaml</code> or from your shell. The password never appears in the file you commit.</li>
<li><strong>The database is not published</strong> to the outside, because only the web service needs the public. Fewer open doors means better security.</li>
<li><strong>depends_on</strong> controls start order, but it does not wait until the database is ready to accept connections. Good apps retry the connection.</li>
</ul>

<h3>Commands</h3>
<table>
<thead><tr><th>Command</th><th>What it does</th></tr></thead>
<tbody>
<tr><td><code>docker compose up -d</code></td><td>Create and start everything in the background</td></tr>
<tr><td><code>docker compose ps</code></td><td>Show the services and their state</td></tr>
<tr><td><code>docker compose logs -f web</code></td><td>Follow the logs of one service</td></tr>
<tr><td><code>docker compose up -d --build</code></td><td>Rebuild images, then start</td></tr>
<tr><td><code>docker compose down</code></td><td>Stop and remove the containers (volumes are kept)</td></tr>
<tr><td><code>docker compose down -v</code></td><td>Also delete the volumes, which erases the data</td></tr>
</tbody>
</table>

<h3>Scenario</h3>
<p>A new team member joins your project. Without Compose, she needs a page of instructions to install the database, create users and start the app. With Compose she clones the repository, copies <code>.env.example</code> to <code>.env</code>, runs <code>docker compose up -d</code> and is working in five minutes.</p>

<blockquote><p><strong>Quick check:</strong> In the sample file, how does the web service find the database?</p><p>Answer: By the service name <code>db</code>, because Compose puts both services on a private network where names resolve to the containers.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You run <code>docker compose down -v</code> on a production database by mistake. What happens?</p><p>Answer: The containers and the named volumes are deleted, so the data is lost unless you have a backup.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a folder with your Dockerfile app and a <code>compose.yaml</code> modelled on the example.</li>
<li>Create a <code>.env</code> file containing <code>DB_PASSWORD=choose-something-long</code> and add <code>.env</code> to <code>.gitignore</code>.</li>
<li>Create <code>.env.example</code> with <code>DB_PASSWORD=</code> (empty) to commit as a guide.</li>
<li>Run <code>docker compose up -d</code>, then <code>docker compose ps</code> and look at both services.</li>
<li>Run <code>docker compose down</code> and confirm the volume remains with <code>docker volume ls</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Compose describes a whole app in one YAML file and starts it with one command.</li>
<li>Services reach each other by name on a private network.</li>
<li>Keep passwords in an ignored <code>.env</code> file and commit only an example.</li>
<li><code>down -v</code> deletes data; use it carefully.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: You run docker run -d -p 9000:3000 myapp. Where do you open the app on your own computer?
- http://localhost:3000
* http://localhost:9000
- http://localhost:80
- It cannot be reached without a Compose file
= In -p HOST:CONTAINER the left number is the port on your computer.

Q: Which Dockerfile order gives the fastest rebuilds when only your source code changes?
- Copy all code, then install libraries
* Copy the library list, install libraries, then copy the rest of the code
- Install libraries after the container starts
- Copy the code twice
= Layers above a change are reused, so rarely changing steps should come first.

Q: A database container is recreated after an update and all data disappears. What was most likely missing?
- A larger image
- A port mapping
- The EXPOSE instruction
* A named volume for the database folder
= Data written inside a container is lost when the container is removed. A volume keeps it.

Q: Which is the best place for a database password in a Compose-based project?
- Written directly in the Dockerfile
- In compose.yaml committed to a public repository
* In a .env file that is ignored by Git, with only an example committed
- In the README
= Secrets must stay out of images and repositories; an ignored .env file with a safe example is a common pattern.

Q: In compose.yaml the app has DB_HOST: db and a service named db exists. How does the app reach the database?
- Through the internet using the host's public IP
- It cannot; you must publish the database port
* Compose's private network resolves the name db to the database container
- Through the Dockerfile
= Services on the same Compose network find each other by service name.

Q: What does EXPOSE 3000 in a Dockerfile do by itself?
- Opens port 3000 on the host computer
* Documents that the app uses port 3000; you still publish it with -p
- Makes the container faster
- Encrypts traffic on port 3000
= EXPOSE is documentation. Publishing happens at run time or in Compose.

Q: You want an image that always builds with the same Node version over the next year. Which FROM line is safest?
- FROM node
- FROM node:latest
* FROM node:20-alpine
- FROM ubuntu
= A specific tag avoids surprise upgrades. latest changes over time.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: CI/CD pipelines with GitHub Actions',
            'summary' => 'Automate building and testing on every change, manage environments and secrets, and deploy tested versions.',
            'lessons' => [
                [
                    'title'   => 'Continuous integration with GitHub Actions',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to write a GitHub Actions workflow that runs automatically when you push code. This matters because a pipeline is a tireless teammate that checks every change the same way, every time, even at 2 a.m.</p>

<h3>Key words</h3>
<table>
<thead><tr><th>Word</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>Workflow</td><td>An automated process described in a YAML file</td></tr>
<tr><td>Event</td><td>What starts it, such as a push or a pull request</td></tr>
<tr><td>Job</td><td>A group of steps that run on one machine</td></tr>
<tr><td>Step</td><td>One command or one reusable action</td></tr>
<tr><td>Runner</td><td>The machine that does the work (GitHub provides free hosted ones)</td></tr>
<tr><td>Action</td><td>A reusable building block you call with <code>uses:</code></td></tr>
</tbody>
</table>

<h3>Your first workflow</h3>
<p>Workflows live in your repository in the folder <code>.github/workflows/</code>. Create <code>.github/workflows/ci.yml</code>:</p>
<pre><code>name: CI
on:
  push:
    branches: [main]
  pull_request:
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: 20
      - run: npm ci
      - run: npm test</code></pre>
<p>Read it in plain words. <em>When</em> someone pushes to <code>main</code> or opens a pull request, <em>start</em> a job called <code>test</code> on a fresh Ubuntu machine. First, <code>checkout</code> downloads your code onto the runner (the machine starts empty). Then install Node 20, install the exact libraries listed in the lock file with <code>npm ci</code>, and run the tests. If any step returns an error, the job fails and the commit gets a red cross.</p>

<h3>Seeing the result</h3>
<p>Push the file to GitHub and open the <strong>Actions</strong> tab of your repository. Each run shows its steps and their logs. On a pull request you will see a green tick or red cross next to the commits. Teams then use <strong>branch protection</strong> so that a pull request cannot be merged until the checks pass. That single rule stops most broken code from reaching the main branch.</p>

<h3>YAML rules that trip beginners</h3>
<ul>
<li>Indent with spaces, never tabs, and keep the levels exactly aligned.</li>
<li>A list item starts with a dash. <code>uses</code> and <code>run</code> are separate kinds of step.</li>
<li>Pin action versions (such as <code>@v4</code>) so a surprise update does not break your pipeline.</li>
<li>A runner is thrown away after each run, so nothing is saved unless you store it deliberately.</li>
</ul>

<h3>Free usage and limits</h3>
<p>GitHub provides a free monthly allowance of workflow minutes for private repositories on free accounts, and public repositories are generous. Limits can change, so check the current pricing page on GitHub before depending on a number. Keep pipelines short: cache dependencies and avoid running work you do not need.</p>

<h3>Scenario</h3>
<p>Two students edit the same project. Sarah pushes a change that accidentally breaks a function. Within two minutes the workflow fails and her pull request shows a red cross, so nobody merges it. Without the pipeline, the bug would have been found by the lecturer during the demo.</p>

<blockquote><p><strong>Quick check:</strong> Why must a workflow start with <code>actions/checkout</code>?</p><p>Answer: The runner starts as an empty machine, so checkout is what downloads your repository's code onto it.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A step in your job exits with an error. What happens to the rest of the job and to the commit?</p><p>Answer: By default the later steps are skipped, the job is marked failed and the commit shows a red cross.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a new GitHub repository with a tiny project that has at least one test (or a <code>test</code> script that simply prints OK).</li>
<li>Add <code>.github/workflows/ci.yml</code> modelled on the example, adjusting the language setup for your project.</li>
<li>Commit and push, then open the Actions tab and read the logs of the run.</li>
<li>Break the test on purpose in a new branch, open a pull request and see the red cross.</li>
<li>Fix it and watch the check turn green.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A workflow is YAML in <code>.github/workflows/</code> that runs jobs and steps on a runner when an event happens.</li>
<li>Always check out the code first; install, then test.</li>
<li>Failing checks protect the main branch when combined with branch protection.</li>
<li>Pin action versions and keep pipelines quick.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Automated tests in a pipeline',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain the kinds of automated test, write a simple test, and decide what a pipeline should check before code is allowed through. This matters because a pipeline without tests only moves bugs faster.</p>

<h3>Why automate tests</h3>
<p>Checking by hand is slow and people skip steps when they are tired or rushed. A test is a small program that runs your code and checks the answer. Once written, it runs in seconds, forever, and it warns you when a later change breaks something that used to work. This protection against old bugs returning is called <strong>regression</strong> testing.</p>

<h3>The testing pyramid</h3>
<table>
<thead><tr><th>Kind</th><th>What it checks</th><th>Speed and number</th></tr></thead>
<tbody>
<tr><td>Unit test</td><td>One small function on its own</td><td>Very fast; write many</td></tr>
<tr><td>Integration test</td><td>Parts working together, such as app plus database</td><td>Slower; write fewer</td></tr>
<tr><td>End-to-end test</td><td>A whole user journey through the real interface</td><td>Slowest; write a few for key paths</td></tr>
</tbody>
</table>
<p>Many fast tests at the bottom and a few slow ones at the top gives quick feedback without hiding big failures.</p>

<h3>A very small test</h3>
<p>Suppose a function calculates a mobile-money fee. A unit test in Python with the common <code>pytest</code> tool could look like this:</p>
<pre><code>def fee(amount):
    return 500 if amount &lt; 50000 else 1500

def test_small_amount_has_low_fee():
    assert fee(20000) == 500

def test_large_amount_has_high_fee():
    assert fee(100000) == 1500</code></pre>
<p>Run <code>pytest</code> and each <code>assert</code> must be true. In Node the idea is identical, only the syntax changes. The invented fee values are just for practice.</p>

<h3>Other automated checks</h3>
<ul>
<li><strong>Linting:</strong> a tool reads the code and flags style problems and likely mistakes.</li>
<li><strong>Build check:</strong> the project (or the Docker image) must build without error.</li>
<li><strong>Dependency check:</strong> report libraries with known security problems.</li>
<li><strong>Coverage:</strong> a figure showing how much code tests touch. Useful as a hint, but 100 percent coverage does not prove the code is correct.</li>
</ul>

<h3>A pipeline with several checks</h3>
<pre><code>jobs:
  check:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-python@v5
        with:
          python-version: "3.12"
      - run: pip install -r requirements.txt
      - run: pytest
  docker-build:
    runs-on: ubuntu-latest
    needs: check
    steps:
      - uses: actions/checkout@v4
      - run: docker build -t myapp:test .</code></pre>
<p>The <code>needs: check</code> line makes the Docker build wait until the tests pass. Jobs without <code>needs</code> run side by side, which saves time.</p>

<h3>Flaky tests</h3>
<p>A <strong>flaky</strong> test sometimes passes and sometimes fails with no code change. Teams start ignoring red crosses, and then real failures are missed. Fix or remove flaky tests quickly; do not simply press "re-run" forever.</p>

<h3>Scenario</h3>
<p>A fee calculator is changed so that exactly UGX 50,000 is charged the high fee. The existing test with 20,000 still passes, so nobody notices the new rule is wrong for the boundary. A good test suite also covers boundary values such as 49,999 and 50,000.</p>

<blockquote><p><strong>Quick check:</strong> Which kind of test should you have the most of, and why?</p><p>Answer: Unit tests, because they are fast and precise, so feedback arrives within seconds.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A test fails on one run and passes when re-run with the same code. What is it called, and why is it dangerous?</p><p>Answer: It is a flaky test, and it teaches the team to ignore failures so real problems may be missed.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a small function in any language, such as a discount or fee calculator.</li>
<li>Write three tests: normal value, boundary value and invalid value.</li>
<li>Run them on your computer until they pass.</li>
<li>Add them to your workflow so the pipeline runs them.</li>
<li>Change the function to break a boundary and confirm the pipeline catches it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Automated tests give fast, repeatable protection against regressions.</li>
<li>Write many unit tests, fewer integration tests and a few end-to-end tests.</li>
<li>Use <code>needs</code> to order jobs so later steps run only after checks pass.</li>
<li>Do not tolerate flaky tests.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Environments, configuration and secrets',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to separate development, staging and production, keep settings out of your code, and store secrets safely. This matters because leaked passwords and test code touching real customer data are two of the most common and most painful mistakes in software teams.</p>

<h3>Three common environments</h3>
<table>
<thead><tr><th>Environment</th><th>Purpose</th><th>Data</th></tr></thead>
<tbody>
<tr><td>Development</td><td>Where you write and try code, often on your laptop</td><td>Fake or sample data</td></tr>
<tr><td>Staging (or testing)</td><td>A copy of production used for final checks before release</td><td>Fake or anonymised data</td></tr>
<tr><td>Production</td><td>The real system used by real people</td><td>Real data; handle with great care</td></tr>
</tbody>
</table>
<p>The golden rule: the <em>same</em> built image moves through the environments. Only the configuration changes. If you rebuild for production, you are shipping something you never tested.</p>

<h3>Configuration outside the code</h3>
<p>Code stays the same; settings differ. Typical settings are the database address, the website address, a debug switch and the payment provider mode (test or live). Pass them as environment variables, as you learned with Docker. This idea comes from the well-known "twelve-factor app" guidelines, listed in this course's resources.</p>
<pre><code>APP_ENV=staging
DB_HOST=db.staging.internal
PAYMENT_MODE=sandbox</code></pre>

<h3>Secrets</h3>
<p>A <strong>secret</strong> is a setting that gives access: a database password, an API key, a private SSH key, a token. Rules for secrets:</p>
<ul>
<li>Never write them in code, Dockerfiles, Compose files you commit, or chat messages.</li>
<li>Keep local ones in a <code>.env</code> file listed in <code>.gitignore</code>.</li>
<li>Store pipeline secrets in the platform's secret store. In GitHub: repository <strong>Settings</strong>, then <strong>Secrets and variables</strong>, then <strong>Actions</strong>.</li>
<li>Use different secrets for each environment, so a leak in testing cannot open production.</li>
<li>Rotate (change) a secret at once if it might have leaked. Removing it from Git history is not enough, because copies may already exist.</li>
</ul>

<h3>Using a secret in a workflow</h3>
<pre><code>- name: Deploy
  env:
    SSH_KEY: ${{ secrets.SSH_PRIVATE_KEY }}
  run: ./deploy.sh</code></pre>
<p>GitHub hides secret values in the logs, but you should still never print them yourself with <code>echo</code>. Pull requests from forks do not receive your secrets, which protects you from strangers.</p>

<h3>Protecting production</h3>
<p>GitHub lets you create named <strong>environments</strong> such as <code>staging</code> and <code>production</code>, give each its own secrets, and require a named person to approve a deployment before it runs. Features and plan limits for this can vary by account type, so check the documentation for your plan. Even without it, you can deploy production only from the <code>main</code> branch or from a version tag.</p>

<h3>Scenario</h3>
<p>A junior developer commits a <code>.env</code> file with a live payment key to a public repository. Bots scan GitHub and find keys within minutes. The right response: revoke the key immediately in the provider's dashboard, create a new one, store it as a secret, and add <code>.env</code> to <code>.gitignore</code>.</p>

<blockquote><p><strong>Quick check:</strong> Why should staging and production use different database passwords?</p><p>Answer: If a staging secret leaks, an attacker still cannot open the production system.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You accidentally pushed an API key and then deleted it in the next commit. Are you safe?</p><p>Answer: No, the key stays in the Git history and may be copied already, so you must revoke and replace it.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>In a practice repository, add a secret named <code>DEMO_TOKEN</code> under Settings, Secrets and variables, Actions.</li>
<li>Add a workflow step that uses it as an environment variable and prints only the text "token is set" if it is not empty.</li>
<li>Confirm the real value never appears in the logs.</li>
<li>Check that <code>.env</code> is listed in your <code>.gitignore</code>.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Use development, staging and production, and promote the same image through them.</li>
<li>Keep configuration outside the code, in environment variables.</li>
<li>Secrets belong in a secret store, never in Git; use separate secrets per environment.</li>
<li>If a secret leaks, revoke and replace it immediately.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Continuous delivery: deploying to a server or platform',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe the common ways to deploy a containerised app and write a pipeline job that deploys it to a cloud server. This matters because a deployment that anyone can repeat in two minutes is what turns tested code into value for users.</p>

<h3>Where can the app run?</h3>
<table>
<thead><tr><th>Option</th><th>You manage</th><th>Good for</th></tr></thead>
<tbody>
<tr><td>Cloud server (a VM, sometimes called a VPS)</td><td>The operating system, Docker, updates, security</td><td>Learning, full control, low cost</td></tr>
<tr><td>Platform as a service (PaaS)</td><td>Only your app and its settings</td><td>Quick launch, small teams</td></tr>
<tr><td>Managed container service</td><td>Your containers; the provider runs the machines</td><td>Growing systems</td></tr>
</tbody>
</table>
<p>For learning, a small cloud server with Docker installed teaches you the most. Many providers offer a cheap small server or a free trial; read the current terms, since offers change.</p>

<h3>Registries: where images wait</h3>
<p>A pipeline usually builds an image once, tags it (for example with the Git commit id) and pushes it to a registry such as Docker Hub or GitHub's container registry. The server then pulls exactly that tested image. This keeps the golden rule: what you tested is what you run.</p>

<h3>A simple deployment over SSH</h3>
<p>On the server you keep a folder with <code>compose.yaml</code> and a protected <code>.env</code>. The pipeline connects over SSH and tells the server to fetch the new image and restart:</p>
<pre><code>deploy:
  runs-on: ubuntu-latest
  needs: [check]
  if: github.ref == 'refs/heads/main'
  steps:
    - name: Update the server
      env:
        SSH_KEY: ${{ secrets.SSH_PRIVATE_KEY }}
        HOST: ${{ secrets.SERVER_HOST }}
      run: |
        mkdir -p ~/.ssh
        echo "$SSH_KEY" &gt; ~/.ssh/id_deploy
        chmod 600 ~/.ssh/id_deploy
        ssh-keyscan -H "$HOST" &gt;&gt; ~/.ssh/known_hosts
        ssh -i ~/.ssh/id_deploy deploy@"$HOST" "cd /srv/shop &amp;&amp; docker compose pull &amp;&amp; docker compose up -d"</code></pre>
<p>Important details: the job runs only after tests pass and only on <code>main</code>; it logs in as a limited <code>deploy</code> user, not root; and the key is a secret. Create a separate SSH key just for deployments so you can revoke it without touching your personal key.</p>

<h3>Safer release strategies</h3>
<ul>
<li><strong>Rolling update:</strong> replace instances one at a time so the site stays up.</li>
<li><strong>Blue-green:</strong> run the new version beside the old one and switch traffic when it looks healthy; switch back if not.</li>
<li><strong>Canary:</strong> send a small share of users to the new version first.</li>
</ul>
<p>With one small server, a brief restart is usually acceptable. Know what your users can accept.</p>

<h3>After deploying, verify</h3>
<p>A good pipeline ends with a <strong>smoke test</strong>: a tiny check, such as requesting the home page or a <code>/health</code> address, to prove the app responds. If it fails, the pipeline should alert the team and, ideally, restore the previous version.</p>

<blockquote><p><strong>Quick check:</strong> Why push the tested image to a registry and pull it on the server, instead of rebuilding on the server?</p><p>Answer: The server then runs exactly the image that passed the tests, so there are no surprise differences.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What does the line <code>if: github.ref == 'refs/heads/main'</code> achieve?</p><p>Answer: It makes the deploy job run only for changes on the main branch, so unfinished branches never reach the server.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose where you will deploy: a cheap or trial cloud server, or a free platform tier.</li>
<li>On a server, create a limited user, install Docker and create a folder for your app with a compose file.</li>
<li>Run <code>docker compose up -d</code> by hand once and open the app in a browser.</li>
<li>Write down the three commands that update it, then turn them into a pipeline job like the example.</li>
<li>Add a final step that requests the home page and fails if it does not respond.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Choose between your own server, a platform and a managed service based on control and effort.</li>
<li>Build once, push to a registry and deploy that same image.</li>
<li>Deploy with a limited user and a dedicated key stored as a secret, only from <code>main</code>.</li>
<li>Always verify with a smoke test after deploying.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Where must a GitHub Actions workflow file be stored?
- In the root of the repository, named workflow.txt
* In the .github/workflows folder of the repository, as a YAML file
- On the GitHub profile page
- In the Dockerfile
= GitHub looks for workflow YAML files in .github/workflows.

Q: Your workflow fails with "package.json not found" on the first step after setup. What is the most likely cause?
- The runner is out of memory
- The tests are flaky
* The actions/checkout step is missing, so the runner has no copy of your code
- YAML does not support lists
= Runners start empty. Checkout downloads your repository.

Q: A pull request cannot be merged until the tests pass. Which feature enforces this?
- Docker volumes
* Branch protection with required status checks
- A .dockerignore file
- Environment variables
= Branch protection rules can require checks to succeed before merging.

Q: Which mix of tests gives fast feedback while still covering the whole system?
- Only end-to-end tests
- Only manual tests before each release
* Many unit tests, fewer integration tests, a few end-to-end tests
- One huge test that checks everything
= The testing pyramid keeps most checks fast and precise.

Q: A job in your pipeline should run only after the test job passes. What do you add?
- runs-on: after
- if: always()
* needs: test
- on: success
= needs makes a job wait for another job to succeed.

Q: A developer commits a live API key, notices, and deletes the file in the next commit. What should happen next?
- Nothing, the key is gone
- Make the repository private and keep using the key
* Revoke the key, create a new one, store it as a secret
- Rename the key
= The old key remains in history and may already be copied, so it must be replaced.

Q: Why should the same built image be used in staging and production?
- Staging does not need to be tested
* What you tested is exactly what you run; only the configuration differs
- It uses less disk space on GitHub
- Production cannot use environment variables
= Rebuilding for production would ship an untested artefact.

Q: Your deploy job must run only for the main branch after tests pass. Which setup fits?
* A job with needs: check and if: github.ref == 'refs/heads/main'
- A job with no conditions so it always runs
- A job triggered only by a manual phone call
- A job that runs before the tests
= Combine a dependency on the tests with a branch condition.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Running software in production',
            'summary' => 'Put a reverse proxy in front of your app, watch it with logs and alerts, and prepare for failure with backups, rollback and infrastructure as code.',
            'lessons' => [
                [
                    'title'   => 'Web servers and reverse proxies',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain what a web server and a reverse proxy do, and read a short proxy configuration. This matters because almost no production app faces the internet directly; a proxy usually stands in front of it to add security, speed and tidy addresses.</p>

<h3>What a web server does</h3>
<p>A <strong>web server</strong> is software that listens for HTTP requests and sends back responses. It can hand out files directly (pages, images, style sheets) or pass requests to your application. Nginx, Apache and Caddy are well-known examples. Your app, such as a Node or Python program, often has a built-in server too, but it is usually not the best tool to face the public internet alone.</p>

<h3>What a reverse proxy does</h3>
<p>A normal (forward) proxy hides users from websites. A <strong>reverse proxy</strong> does the opposite: it sits in front of your servers and receives requests on their behalf, then passes each one to the right app. Visitors only ever talk to the proxy.</p>
<pre><code>Browser  --&gt;  Reverse proxy (ports 80 and 443)  --&gt;  App container (port 3000)
                                              --&gt;  Another app (port 4000)</code></pre>

<h3>Why use one</h3>
<table>
<thead><tr><th>Benefit</th><th>Explanation</th></tr></thead>
<tbody>
<tr><td>HTTPS in one place</td><td>The proxy holds the certificate and encrypts traffic, so apps can stay simple</td></tr>
<tr><td>Many apps, one server</td><td>Route <code>shop.example.com</code> and <code>blog.example.com</code> to different containers on one machine</td></tr>
<tr><td>Hiding internals</td><td>Databases and app ports are never exposed to the internet</td></tr>
<tr><td>Speed</td><td>The proxy can serve static files and compress or cache responses</td></tr>
<tr><td>Load balancing</td><td>Spread traffic across several copies of the app</td></tr>
<tr><td>Safer restarts</td><td>The proxy can show a friendly page while the app restarts</td></tr>
</tbody>
</table>

<h3>A small Nginx example</h3>
<pre><code>server {
    listen 80;
    server_name shop.example.com;

    location / {
        proxy_pass http://web:3000;
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-For $remote_addr;
    }
}</code></pre>
<p>This says: for requests to <code>shop.example.com</code> on port 80, pass everything to the container named <code>web</code> on port 3000. In Compose you would add an <code>nginx</code> service, publish only ports 80 and 443 from it, and remove the published port from <code>web</code>.</p>

<h3>HTTPS and certificates</h3>
<p>HTTPS needs a certificate that proves your domain is yours. Free certificates are available from the Let's Encrypt service, and some proxies (Caddy is one) can obtain and renew them automatically. Never run a login page or a payment page over plain HTTP. Your domain's DNS record (an "A record") must point to your server's public IP address first.</p>

<h3>Scenario</h3>
<p>Grace hosts a school website and a small shop app on one cheap server. Both want port 80. With a reverse proxy listening on 80 and 443, requests for <code>school.example.ug</code> go to one container and <code>shop.example.ug</code> to another. She pays for one server instead of two.</p>

<blockquote><p><strong>Quick check:</strong> Why publish only the proxy's ports and not the database port?</p><p>Answer: Fewer open ports means fewer ways in for attackers, and the database only needs to be reached by your app on the private network.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Two apps must share one server and one public port 443. What makes this possible?</p><p>Answer: A reverse proxy that chooses the target app from the requested domain name or path.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a Compose file with two services: your app and an <code>nginx</code> container.</li>
<li>Mount a small config file like the example into Nginx and set <code>proxy_pass</code> to your app service name.</li>
<li>Publish only the Nginx port (for example 8080:80) and leave the app unpublished.</li>
<li>Open <code>http://localhost:8080</code> and confirm the app answers through the proxy.</li>
<li>Try to reach the app's own port directly and confirm that it fails.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A reverse proxy receives public requests and forwards them to private apps.</li>
<li>It centralises HTTPS, routing, caching and load balancing.</li>
<li>Expose only what must be public.</li>
<li>Free certificates exist; automate their renewal.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Logs, monitoring and alerts',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to read container logs, decide what to monitor and design a simple alert. This matters because every system fails sometimes, and the question is whether you hear about it first or your customers do.</p>

<h3>Logs: the diary of your app</h3>
<p>A <strong>log</strong> is a record of events: requests received, errors, logins, payments. In containers the rule is simple: the app should write its logs to the screen (standard output), and Docker collects them.</p>
<pre><code>docker logs --tail 50 myapp
docker compose logs -f web</code></pre>
<p><code>--tail 50</code> shows the last 50 lines, and <code>-f</code> follows new lines live. Each log line should carry a time, a level and a clear message. Common levels, from least to most serious: DEBUG, INFO, WARNING, ERROR. In production, log INFO and above, otherwise logs become huge and noisy.</p>
<p><strong>Never log secrets or personal data</strong> such as passwords, full card numbers or national ID numbers. Logs are copied, searched and shared widely.</p>

<h3>Log size</h3>
<p>Logs grow until the disk is full, and a full disk can crash the server. Limit them. In a Compose file you can add rotation:</p>
<pre><code>    logging:
      driver: json-file
      options:
        max-size: "10m"
        max-file: "3"</code></pre>

<h3>Monitoring: watching numbers over time</h3>
<p><strong>Monitoring</strong> collects measurements and shows them on charts. A useful starter set:</p>
<table>
<thead><tr><th>What</th><th>Example measurement</th><th>Why</th></tr></thead>
<tbody>
<tr><td>Availability</td><td>Does the home page return a good response?</td><td>Tells you the site is up</td></tr>
<tr><td>Errors</td><td>Number of failed requests per minute</td><td>Shows bugs and bad releases</td></tr>
<tr><td>Speed</td><td>Time to answer a request</td><td>Slow sites lose users</td></tr>
<tr><td>Resources</td><td>CPU, memory, disk use</td><td>Warns before a server runs out</td></tr>
<tr><td>Business</td><td>Orders or sign-ups per hour</td><td>A sudden drop can mean a hidden failure</td></tr>
</tbody>
</table>
<p>Free options include uptime-checking services with free plans, and open-source tools you run yourself, such as Prometheus for numbers and Grafana for charts. Choose something simple first; a free uptime check that messages you when the site is down is already a big step up.</p>

<h3>Health checks</h3>
<p>Give your app a small address such as <code>/health</code> that answers "OK" when the app and its database connection work. Docker can use it:</p>
<pre><code>    healthcheck:
      test: ["CMD", "wget", "-qO-", "http://localhost:3000/health"]
      interval: 30s
      retries: 3</code></pre>
<p>This works only if the tool you call (here <code>wget</code>) exists in the image; check your base image.</p>

<h3>Good alerts</h3>
<ul>
<li>Alert on symptoms users feel (site down, many errors) before technical causes.</li>
<li>Every alert should need a human action. If people ignore it, delete or fix it. Too many alerts cause "alert fatigue".</li>
<li>Send alerts where the team will see them, such as email, a team chat channel or SMS.</li>
<li>Write what to do next in a short note, called a runbook.</li>
</ul>

<h3>Scenario</h3>
<p>A shop app at night starts failing payments because a provider changes an address. No one is awake. With a business metric alert ("fewer than one order per hour during the day") and an error alert, the owner gets a message in 10 minutes. Without them, the problem would be found the next morning after hours of lost sales.</p>

<blockquote><p><strong>Quick check:</strong> Why should a container app log to standard output instead of a file inside the container?</p><p>Answer: Docker collects standard output for you, so logs survive in one place and are easy to read with <code>docker logs</code> without extra storage inside the container.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> You get 50 alerts a day and everyone ignores them. What is the problem and the fix?</p><p>Answer: Alert fatigue; keep only alerts that need human action and tune or remove the noisy ones.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Run a container of your app and view its last 20 log lines.</li>
<li>Add the log rotation settings to a Compose service.</li>
<li>Add a <code>/health</code> address to a small app that returns the text OK.</li>
<li>Register a free uptime check (any provider with a free plan) for a public page of yours and send alerts to your email.</li>
<li>Stop the app and confirm that you receive the alert.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Log to standard output with levels, and never log secrets.</li>
<li>Rotate logs so they cannot fill the disk.</li>
<li>Monitor availability, errors, speed, resources and a business number.</li>
<li>Keep alerts few, meaningful and actionable.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Backups, rollback and infrastructure as code',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to plan backups that really work, roll back a bad release, and explain infrastructure as code. This matters because mistakes, bugs and hardware failures are certain, so recovery is a skill you must prepare before you need it.</p>

<h3>Backups</h3>
<p>A backup is a copy of important data stored somewhere else. Use the <strong>3-2-1 rule</strong> as a guide: keep three copies, on two different kinds of storage, with one copy off-site (another place or another provider). A copy on the same server dies with the server.</p>
<table>
<thead><tr><th>Question</th><th>Why it matters</th></tr></thead>
<tbody>
<tr><td>What must be saved?</td><td>Databases and uploaded files; code is already in Git</td></tr>
<tr><td>How often?</td><td>Depends on how much data you can afford to lose (your RPO, recovery point objective)</td></tr>
<tr><td>How fast must we recover?</td><td>Your RTO, recovery time objective, decides your method</td></tr>
<tr><td>Is it encrypted and protected?</td><td>A stolen backup is a data leak</td></tr>
<tr><td>Have we tested a restore?</td><td>An untested backup is only a hope</td></tr>
</tbody>
</table>

<h3>A simple database backup</h3>
<pre><code>docker compose exec -T db pg_dump -U shop shopdb | gzip &gt; backup-$(date +%F).sql.gz</code></pre>
<p>This dumps a Postgres database into a compressed file named with the date. Schedule it with <code>cron</code>, copy the file off the server, and delete old copies on a plan. Other databases have equivalent tools. Test it by restoring into a fresh test database and checking that the data is there.</p>

<h3>Rollback</h3>
<p>A <strong>rollback</strong> returns to the last good version. With images tagged by version, it is easy:</p>
<pre><code>IMAGE_TAG=1.4.2 docker compose up -d</code></pre>
<p>Here Compose would read the tag from the variable (for example <code>image: myshop:${IMAGE_TAG}</code>). Keep the previous images available in the registry. Two cautions: a rollback of code does not undo changes already made to the database, so make database changes backward compatible where you can; and decide rollback criteria before the release (for example, error rate above a limit for ten minutes).</p>

<h3>Infrastructure as code (IaC)</h3>
<p><strong>Infrastructure as code</strong> means describing servers, networks and settings in text files kept in Git, instead of clicking in a web console. You have already met small examples: a Dockerfile and a compose file are IaC. Larger tools exist, such as Terraform and OpenTofu for creating cloud resources, and Ansible for configuring servers.</p>
<ul>
<li><strong>Repeatable:</strong> build an identical copy in minutes.</li>
<li><strong>Reviewable:</strong> changes go through pull requests like any code.</li>
<li><strong>Recoverable:</strong> if a server dies, create it again from the files.</li>
<li><strong>Documented:</strong> the files show how the system is built.</li>
</ul>
<p>The idea of <strong>immutable</strong> servers helps: instead of editing a live machine by hand, you replace it with a new one built from code. This avoids "snowflake" servers that nobody can rebuild.</p>

<h3>Scenario</h3>
<p>A hosting provider's disk fails and the shop's only server is lost. Because the team has the code in Git, the setup in Compose and an off-site nightly database backup, they rent a new server, run the setup, restore last night's backup and are back in an hour, losing one day of orders. Without the off-site backup, the business would have ended.</p>

<blockquote><p><strong>Quick check:</strong> Your only backup file is stored on the same server as the database. What is the risk?</p><p>Answer: If the server is lost or damaged, the data and the backup disappear together, so a copy must also be kept off-site.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why is a compose file considered infrastructure as code?</p><p>Answer: It describes how the system is built in a text file kept in Git, so the environment can be recreated and reviewed.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a backup command or script for your project's database and run it once.</li>
<li>Restore that backup into a different, empty database and check the row counts.</li>
<li>Tag your image with two versions (for example 1.0 and 1.1) and practise switching your Compose file between them.</li>
<li>Write the RPO and RTO that would suit a small shop (how much data it can lose, how long it can be offline).</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Follow 3-2-1 for backups and always test restores.</li>
<li>Roll back by redeploying a previous tagged version; mind database changes.</li>
<li>Infrastructure as code makes systems repeatable, reviewable and recoverable.</li>
<li>Replace servers from code rather than hand-editing them.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What is the main job of a reverse proxy?
- To hide the user's identity from websites
* To receive public requests and forward them to the right private application
- To store database backups
- To compile source code
= A reverse proxy stands in front of your servers and routes incoming traffic.

Q: Two apps on one server both need to be reached on HTTPS at their own domain names. What do you set up?
- Two servers, one per app
- Both apps listening on port 443 directly
* A reverse proxy that routes by domain name to each app
- A bigger database
= The proxy owns ports 80 and 443 and forwards by host name.

Q: Your Compose file publishes the database on port 5432 to the whole internet. What is the best improvement?
- Rename the database
* Do not publish it; let only the app reach it on the private network
- Publish it on port 5433 instead
- Add more logs
= Services that only the app needs should not be reachable from outside.

Q: Which log line should NEVER appear in production logs?
- INFO: order 4521 created
- WARNING: payment provider slow (3.2 s)
* INFO: login by user a@example.com with password Secret123
- ERROR: database connection failed, retrying
= Passwords and similar secrets must never be logged.

Q: Disk space on your server keeps filling up and the app crashes weekly. Logs are suspected. What helps?
- Turn off the server at night
* Add log rotation limits such as max-size and max-file
- Log more details at DEBUG level
- Delete the database
= Rotation caps how much space logs may use.

Q: An alert fires 40 times a day, but no one needs to act on it. What should you do?
- Mute the whole alerting channel
- Add more alerts like it
* Remove or tune it so alerts are rare and always require action
- Wait until it fixes itself
= Noisy alerts cause alert fatigue, and real problems get missed.

Q: A bad release is live and errors are rising. Previous images are still in the registry. What is the fastest safe action?
- Rebuild the whole server from scratch
* Redeploy the last known good image tag
- Delete the registry
- Wait for users to report it
= Tagged images make rollback a quick redeploy of the previous version.

Q: You keep nightly database backups in a folder on the same server. What is the biggest weakness?
- The files are compressed
- The backups run at night
* A server failure would destroy both the data and the backups, and restores have not been tested
- Backups are not needed for databases
= Backups need an off-site copy and a tested restore.
QUIZ,
        ],

        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Security, teamwork and capstone',
            'summary' => 'Secure your pipeline, control costs, handle incidents as a team and finish with a complete containerise-and-ship project.',
            'lessons' => [
                [
                    'title'   => 'Security basics in pipelines, and free-tier costs',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to apply basic security habits to containers and pipelines, and keep cloud costs under control. This matters because a pipeline can deploy to production, so whoever controls it controls your system, and because a forgotten server can quietly use up a month's savings.</p>

<h3>Security thinking: least privilege</h3>
<p>The core idea is <strong>least privilege</strong>: give every person, tool and container only the access it needs, and nothing more. If something is stolen or fails, the damage stays small.</p>
<table>
<thead><tr><th>Area</th><th>Good habit</th></tr></thead>
<tbody>
<tr><td>Code and repository</td><td>Require pull request review; protect the main branch; turn on two-step verification for every account</td></tr>
<tr><td>Secrets</td><td>Use the secret store; separate secrets per environment; rotate after any leak</td></tr>
<tr><td>Pipeline</td><td>Give the workflow only the permissions it needs; do not run untrusted code with secrets</td></tr>
<tr><td>Images</td><td>Use official small base images, pin versions, rebuild often, run as a non-root user</td></tr>
<tr><td>Dependencies</td><td>Scan libraries for known vulnerabilities and update them regularly</td></tr>
<tr><td>Servers</td><td>Use SSH keys, disable password login, open only needed ports, install updates</td></tr>
</tbody>
</table>

<h3>Limit the workflow's permissions</h3>
<p>Each run gets a temporary token. You can restrict it at the top of the workflow file:</p>
<pre><code>permissions:
  contents: read</code></pre>
<p>Now the job can only read the repository, even if one of its steps is compromised. Add more permissions only on the job that needs them.</p>

<h3>Scans you can add</h3>
<ul>
<li><strong>Dependency scanning:</strong> GitHub offers dependency alerts and update pull requests (Dependabot) for many repositories.</li>
<li><strong>Secret scanning:</strong> detects keys pushed by mistake; availability depends on repository type, so check the current GitHub documentation.</li>
<li><strong>Image scanning:</strong> free open-source scanners can check an image for known weaknesses.</li>
<li><strong>Static analysis:</strong> tools that read code for risky patterns.</li>
</ul>
<p>Scans produce warnings. Decide which severities block a release, otherwise the team will ignore the output.</p>

<h3>Supply chain care</h3>
<p>Your app trusts hundreds of libraries and actions written by others. Pin their versions, prefer well-known maintainers, and review updates rather than accepting them blindly. A popular package that is taken over by an attacker can reach thousands of projects.</p>

<h3>Controlling cost</h3>
<p>Free tiers and trials are wonderful for learning but have limits and end dates. Read what is included, and what happens when you go over.</p>
<ul>
<li>Set a <strong>budget alert</strong> in your cloud account if the provider offers one.</li>
<li>Delete test servers, volumes, snapshots and unused public IP addresses; some keep costing money after the server is gone.</li>
<li>Choose the smallest server that works and grow when monitoring shows you need it.</li>
<li>Remember that money leaves your account in dollars while your income is in UGX, so a small recurring charge matters more than it seems.</li>
<li>Prefer billing methods that cannot silently overdraw, and use accounts in your own name with two-step verification.</li>
</ul>

<h3>Scenario</h3>
<p>Peter finishes a practice project and forgets his cloud server. A month later his card is charged. Had he set a budget alert and kept a checklist ("delete server, disk, IP address, snapshots"), he would have been told or avoided it.</p>

<blockquote><p><strong>Quick check:</strong> What does "least privilege" mean for a deployment key?</p><p>Answer: The key can do only the deployment job (for example log in as a limited user), so a leak cannot give full control of the server.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why set <code>permissions: contents: read</code> in a workflow?</p><p>Answer: It limits the temporary token to reading code, so a compromised step cannot change your repository.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Add a <code>permissions</code> block to your workflow and confirm it still passes.</li>
<li>Turn on two-step verification for your GitHub account and any cloud account.</li>
<li>Enable dependency alerts for a repository and look at what is reported, if anything.</li>
<li>Write a "clean-up checklist" of every resource you create on a cloud server, and tick them off when you finish a practice.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Apply least privilege to people, tokens, keys and containers.</li>
<li>Scan dependencies and images, and decide what blocks a release.</li>
<li>Pin and review third-party code.</li>
<li>Use budget alerts and clean-up checklists to avoid surprise bills.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Working as a team and handling incidents',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to follow a calm incident process, communicate during an outage and write a short blameless review. This matters because the first time production fails is rarely the time to invent a plan, and a team that handles failures well earns trust from customers.</p>

<h3>Team habits that support DevOps</h3>
<ul>
<li><strong>Pull requests with review:</strong> a second pair of eyes catches mistakes and spreads knowledge.</li>
<li><strong>Small changes:</strong> easy to review, test and undo.</li>
<li><strong>Documentation next to the code:</strong> a README with how to run, test and deploy, and a short runbook for common problems.</li>
<li><strong>Shared on-call:</strong> if someone must be reachable for outages, share the duty fairly and agree how handovers work.</li>
<li><strong>Clear ownership:</strong> every service has a named owner or team.</li>
</ul>

<h3>What is an incident?</h3>
<p>An <strong>incident</strong> is an event that harms or threatens the service for users: the site is down, payments fail, or data may have leaked. Treat it as a process, not a panic.</p>

<h3>The incident steps</h3>
<ol>
<li><strong>Detect:</strong> an alert, a monitor or a user report tells you something is wrong.</li>
<li><strong>Assign roles:</strong> one person leads and decides; another does the technical work; another communicates. In a tiny team one person may do two jobs, but name them.</li>
<li><strong>Stabilise first:</strong> stop the damage before searching for the root cause. Roll back the last release, switch off a broken feature or restore service. Restore comes before explanation.</li>
<li><strong>Communicate:</strong> tell users and managers briefly and honestly what is affected and when you will update again.</li>
<li><strong>Fix properly:</strong> once stable, find and repair the real cause.</li>
<li><strong>Review:</strong> learn from it.</li>
</ol>

<h3>A short, useful status message</h3>
<pre><code>10:42  We are aware that card payments are failing for some customers.
10:55  Cause found: a settings change in our last update. We are rolling it back.
11:05  Payments are working again. We are sorry for the trouble.
       A full explanation will follow within two days.</code></pre>
<p>Notice the pattern: what is wrong, what we are doing, what is next. Do not guess publicly and do not blame individuals.</p>

<h3>The blameless review</h3>
<p>After a serious incident, hold a <strong>blameless review</strong> (also called a post-incident review). The goal is to understand how the system allowed the mistake, not to punish a person. People who fear blame hide problems, and hidden problems repeat. A simple template:</p>
<table>
<thead><tr><th>Section</th><th>Question</th></tr></thead>
<tbody>
<tr><td>Summary</td><td>What happened, to whom, for how long?</td></tr>
<tr><td>Timeline</td><td>Key times from detection to recovery</td></tr>
<tr><td>Causes</td><td>What conditions made this possible?</td></tr>
<tr><td>What went well</td><td>Which alerts, habits or tools helped?</td></tr>
<tr><td>Actions</td><td>Specific tasks, each with an owner and a date</td></tr>
</tbody>
</table>
<p>A strong action is "add a test for the empty-price case and require it in the pipeline", not "be more careful".</p>

<h3>Security incidents</h3>
<p>If you suspect leaked data or a hacked account, also follow your organisation's data protection duties. In Uganda, organisations that handle personal data should know their obligations under the Data Protection and Privacy Act; ask your manager or legal adviser who must be told and when. Do not delete evidence, and change affected passwords and keys.</p>

<h3>Scenario</h3>
<p>On a Monday morning, orders stop. The on-call developer sees a spike in errors that began right after the Friday release. She rolls back to the previous image, and orders resume in five minutes. Only then does the team study the logs and learn that a new field was empty for some customers. The review leads to a new test, a staging check with real-looking data and an alert on order failures.</p>

<blockquote><p><strong>Quick check:</strong> During an outage, should you first find the root cause or first restore service?</p><p>Answer: Restore service first, for example by rolling back, because users are being hurt, and investigate deeply once things are stable.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Which review action is better: "Developers must be more careful" or "Add a pipeline test for empty values"?</p><p>Answer: The pipeline test, because it is specific, checkable and prevents the same failure no matter who is working.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write a one-page runbook for your project: how to check it is up, where the logs are, and how to roll back.</li>
<li>Run a pretend incident with a friend: one breaks the app (stopping the container is enough) and the other follows the steps above.</li>
<li>Write three status messages in the style shown.</li>
<li>Write a short blameless review using the table.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Handle incidents with roles, stabilisation first, honest communication and a proper fix.</li>
<li>Keep updates short: what is wrong, what we are doing, when the next update is.</li>
<li>Hold blameless reviews with concrete, owned actions.</li>
<li>Know your data protection duties if personal data may be exposed.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Capstone: containerise and ship a small web app',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will have a complete, working delivery chain for a small web app: container, tests, automated pipeline and deployment. This matters because a finished project you can show and explain is stronger than any certificate when you apply for a job or freelance work.</p>

<h3>The goal</h3>
<p>Take a small web app you have, or write one in an hour: a page that shows a message and a <code>/health</code> address. Then make every change flow automatically from Git to a running server.</p>

<h3>Capstone checklist</h3>
<table>
<thead><tr><th>Stage</th><th>What to deliver</th></tr></thead>
<tbody>
<tr><td>1. Repository</td><td>Code on GitHub, a README, a <code>.gitignore</code> that lists <code>.env</code></td></tr>
<tr><td>2. Container</td><td>A Dockerfile and <code>.dockerignore</code>; the image builds and runs locally</td></tr>
<tr><td>3. Compose</td><td>A compose file with your app (and a database if you wish), a volume, and an <code>.env.example</code></td></tr>
<tr><td>4. Tests</td><td>At least three automated tests, including a boundary case</td></tr>
<tr><td>5. Pipeline</td><td>A workflow that checks out, installs, tests and builds the image on every push and pull request</td></tr>
<tr><td>6. Protection</td><td>Branch protection that requires the checks to pass</td></tr>
<tr><td>7. Deployment</td><td>A deploy job (main branch only) that updates a server or platform, then runs a smoke test</td></tr>
<tr><td>8. Operations</td><td>A reverse proxy or platform HTTPS, log rotation, an uptime alert, a tested backup (if you have a database)</td></tr>
<tr><td>9. Safety</td><td>Secrets in the secret store, least-privilege permissions, a rollback note in the README</td></tr>
</tbody>
</table>

<h3>Suggested order of work</h3>
<ol>
<li>Get the app running on your own computer without Docker, and write the tests.</li>
<li>Write the Dockerfile and run the image locally. Confirm the port mapping works.</li>
<li>Add Compose so one command starts everything.</li>
<li>Write the CI workflow and make it pass. Protect the main branch.</li>
<li>Prepare the server (or platform): limited user, Docker installed, folder, <code>.env</code> and compose file in place. Do the first deployment by hand.</li>
<li>Automate that manual deployment as a pipeline job with a dedicated key stored as a secret.</li>
<li>Add the smoke test, the uptime alert and the backup.</li>
<li>Make a deliberate bad change on a branch and prove the pipeline stops it. Then ship a good change and watch it reach the server without touching the server yourself.</li>
</ol>

<h3>A model pipeline outline</h3>
<pre><code>name: Ship
on:
  push:
    branches: [main]
  pull_request:
permissions:
  contents: read
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: docker build -t shop:${{ github.sha }} .
      - run: docker run --rm shop:${{ github.sha }} npm test
  deploy:
    needs: test
    if: github.ref == 'refs/heads/main'
    runs-on: ubuntu-latest
    steps:
      - run: echo "Deploy step goes here: SSH, pull, up -d, smoke test"</code></pre>
<p>This version builds the image once, runs the tests inside the same image and only deploys when they pass. Fill in the deploy job with the SSH commands from the earlier lesson. If your image runs production-only installs, make sure the test tools are available in the image you test, or run the tests in a separate step before building the final image.</p>

<h3>Present your work</h3>
<p>Write a short README: what the app does, a diagram of the pipeline in text, how to run it locally, how to deploy and roll back, and what you would improve next. Add a link to the live app. Employers and clients like to see this.</p>

<blockquote><p><strong>Quick check:</strong> Why do we deploy by hand once before automating it?</p><p>Answer: Doing it manually proves each step works and shows exactly what the pipeline must do, so automation does not hide mistakes.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> How do you prove your pipeline really protects production?</p><p>Answer: Push a deliberately broken change on a branch and show that the checks fail and the deployment does not run.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Work through the capstone checklist in the suggested order and tick each stage.</li>
<li>Record the time from pushing a small change to seeing it live.</li>
<li>Perform one rollback to the previous image tag and note the steps.</li>
<li>Ask a friend to follow your README on a clean machine and note where they get stuck; fix those places.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A complete chain is code, container, tests, pipeline, deployment, monitoring and rollback.</li>
<li>Do things by hand first, then automate and prove it works.</li>
<li>Show the breaking-change test: it demonstrates the value of the pipeline.</li>
<li>Document everything so someone else can run and recover your project.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A deployment SSH key is stored as a secret. Which choice best applies least privilege?
- Use the server's root login so the pipeline never fails
- Reuse your personal SSH key
* Create a dedicated key for a limited deploy user that can only do deployment tasks
- Share one key across all environments
= A narrow, dedicated key limits damage if it leaks and can be revoked alone.

Q: You are told your cloud trial ends in two weeks and you no longer need the project. What protects you from surprise charges?
- Closing the browser tab
* Deleting the server, disks, snapshots and IP addresses, and setting a budget alert
- Waiting for the provider to remind you
- Stopping the pipeline only
= Resources can keep billing until they are deleted, and a budget alert gives early warning.

Q: A new release causes failed payments. Your team wants to debug the code live in production. What should the lead say first?
- Debug right now so that we learn the cause
* Roll back to the previous version to restore service, then investigate
- Wait for customers to complain more
- Switch off monitoring
= Stabilise first; investigate once users are no longer being harmed.

Q: Which statement fits a blameless review?
- It names the person who made the error and punishes them
- It is skipped when the outage was short
* It finds how the system allowed the mistake and assigns actions with owners
- It is held only by management
= The aim is learning and prevention, with concrete owned actions.

Q: Your workflow only needs to read the code and run tests. Which setting is best?
- permissions: write-all
* permissions: contents: read
- Disable branch protection
- Add the root password as a variable
= Giving the minimum permissions reduces the damage a compromised step can cause.

Q: In the capstone, you want proof that your pipeline protects production. What do you do?
- Trust that it works because the YAML is valid
- Delete the tests
* Push a deliberately broken change on a branch and show checks fail and deployment is skipped
- Deploy manually every time
= A planned failure demonstrates that the safety gates really work.

Q: During an outage you post: "Payments are failing for some users. We are rolling back the last update and will report again at 11:30." What makes this a good status message?
- It blames the developer who made the change
- It promises that it will never happen again
* It says what is wrong, what is being done and when the next update comes
- It hides the problem to avoid worry
= Honest, brief updates build trust.
QUIZ,
        ],
    ],

    'final_exam' => <<<'QUIZ'
Q: What is the main idea of DevOps?
- Developers and operations work in separate teams to avoid mistakes
* Shared responsibility for building, releasing and running software, supported by automation
- Buying a deployment tool
- Releasing as rarely as possible
= DevOps is culture and practice supported by tools.

Q: Why does a team that releases small changes daily often have fewer serious failures than one that releases once a quarter?
- Daily releases need no testing
* Each change is small, so problems are easy to find and undo
- Users do not notice daily releases
- The servers are faster
= Smaller changes mean fewer possible causes and quicker recovery.

Q: Which sequence matches a normal delivery pipeline?
- Deploy, test, build, commit
* Commit, build, test, deploy
- Test, deploy, commit, build
- Build, deploy, commit, test
= Code is committed, built, tested and only then deployed.

Q: A tested change waits for a manager to approve release, then ships. What is this called?
- Continuous integration only
* Continuous delivery
- Continuous deployment
- A rollback
= A person decides the moment of release in continuous delivery.

Q: Which is true about containers and virtual machines?
- Containers include a complete guest operating system
- VMs share the host kernel
* Containers share the host kernel and are lighter; VMs carry their own operating system
- Containers can run any operating system regardless of the host
= Sharing the kernel makes containers small, but it limits them to the host's kernel type.

Q: An image and a container are related like...
* A recipe and a meal cooked from it
- A meal and its recipe
- A computer and its keyboard
- A server and a website
= An image is the template; containers are running instances of it.

Q: You run docker run -d -p 5000:3000 shop:1.2. Which address opens the app on your machine?
- http://localhost:3000
- http://localhost:80
* http://localhost:5000
- http://shop:1.2
= The left number is the host port.

Q: Why do experienced developers avoid FROM node:latest in production Dockerfiles?
- It is not allowed by Docker
* The image behind latest can change, causing surprise breakage
- It makes images too small
- It disables caching
= A pinned version gives repeatable builds.

Q: A Dockerfile has COPY . . before RUN npm ci. Rebuilds are slow after every small edit. What is the fix?
- Use a bigger server
- Remove the Dockerfile
* Copy package*.json and run npm ci first, then copy the rest of the code
- Add EXPOSE
= Putting the slow, rarely changing install before the frequently changing code keeps that layer cached.

Q: Your database container was deleted and recreated, and its data survived. What made that possible?
- The EXPOSE instruction
* A named volume mounted at the database data folder
- The latest tag
- The image size
= Volumes live outside the container.

Q: Which is the safest way to give a container its database password?
- Write it in the Dockerfile
- Commit it in compose.yaml
* Provide it at run time from an ignored .env file or the platform's secret store
- Print it in the logs for easy copying
= Secrets must not be baked into images or committed.

Q: In a compose file, the web service uses DB_HOST: db. Why does that work?
- db is a public domain name
* Compose gives services on the same network names that resolve to each other
- Docker edits the hosts file of your computer
- It does not work without publishing the database port
= Service names act as addresses on the private network.

Q: Which command stops your Compose app but keeps its named volumes?
- docker compose down -v
* docker compose down
- docker volume prune -a
- docker rmi
= Without -v, volumes are kept.

Q: In a GitHub Actions workflow, which line starts a command such as npm test?
- uses: npm test
* run: npm test
- on: npm test
- name: npm test only
= run executes shell commands; uses calls a reusable action.

Q: Your workflow's first step is run: npm ci and it fails because there is no package.json. What did you forget?
- The runner version
* actions/checkout to download the repository
- A volume
- A reverse proxy
= The runner starts empty.

Q: Which test type should be most numerous in a healthy test suite?
- End-to-end tests of every screen
* Unit tests of small functions
- Manual tests
- Load tests
= Unit tests are fast and pinpoint failures.

Q: A test fails one run in five without any code change. What should you do?
- Keep pressing re-run until it passes
* Fix or remove it, because flaky tests train people to ignore failures
- Delete the whole pipeline
- Hide the result
= Flaky tests destroy trust in the pipeline.

Q: Why should staging and production use different secrets?
- Staging has no internet
* A leak in staging then cannot open production
- Secrets expire if reused
- GitHub forbids it
= Separation limits the damage of a leak.

Q: You pushed an API key to GitHub and removed it in the next commit. What is the correct response?
- Nothing, the key is gone
* Revoke the key, issue a new one and store it as a secret
- Rename the repository
- Ask friends not to look
= History and copies keep the old key alive; it must be replaced.

Q: A deploy job should run only on main and only after the tests pass. Which fits?
- on: always
* needs: test with if: github.ref == 'refs/heads/main'
- runs-on: main
- needs: deploy
= Combine job dependency and a branch condition.

Q: What is the best reason to push a built image to a registry and pull the same image on the server?
- Registries make the image smaller
* The server runs exactly what passed the tests
- It avoids using YAML
- It hides logs
= Build once, run the same artefact.

Q: What does a reverse proxy let you do on one server with one public port 443?
- Run only one app
* Serve several apps by routing on domain name or path, and handle HTTPS in one place
- Remove the need for DNS
- Avoid all attacks
= The proxy fronts many private apps.

Q: Your app container logs only to a file inside the container, and you cannot see errors with docker logs. What is the better approach?
- Delete the logs
- Use a larger disk
* Log to standard output
- Disable the app
= Docker collects standard output, making logs easy to read and ship.

Q: Which backup plan best matches the 3-2-1 idea?
- One copy on the same server
- Two copies in the same folder
* Three copies on two kinds of storage with one off-site, and a tested restore
- A single copy on a USB stick
= Multiple copies in different places survive different failures, and only a tested restore proves they work.

Q: A bad release is live. Which rollback is the fastest and safest if images are tagged by version?
- Edit files on the server by hand
* Redeploy the previous image tag
- Reinstall the operating system
- Wait for the next sprint
= A tagged previous image is a ready, tested version.

Q: Which is an example of infrastructure as code?
- Clicking through a cloud console and not noting the steps
* A compose file and a Terraform-style configuration stored and reviewed in Git
- A sticky note with server details
- A phone call to the provider
= IaC describes systems in versioned text files.

Q: What is the best first action when an incident starts and users cannot pay?
- Find who is to blame
* Stabilise: roll back or disable the broken feature, and communicate
- Write the final report
- Switch off all alerts
= Restore service and keep people informed before deep investigation.
QUIZ,
];
