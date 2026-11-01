<?php
return [
    'slug'        => 'robotics-automation',
    'title'       => 'Robotics and Automation: Build, Program and Innovate',
    'category'    => 'hardware',
    'level'       => 'beginner',
    'icon'        => 'settings',
    'sort'        => 690,
    'summary'     => 'Learn how robots work and build one: electronics, programming, sensors, motors, autonomy, AI, industrial automation, simulation, IoT, safety and ethics, then design a robotic solution for a real community problem.',
    'description' => '<p>Robots already sort parcels, water farms, assist surgeons and explore dangerous places, and Africa needs young people who can design and build them. This course takes you from the basic idea of a robot to a complete robotic project. It is designed for students, young innovators and technology enthusiasts aged 15 to 35, and you do not need to buy anything to start: free simulators let you practise circuits, programs and robot behaviour on a computer or phone before you spend money on parts.</p><p>You will begin with robotics, basic electronics and programming, then study hardware, sensors, motors and movement. Next you plan and build your first robot, make it navigate on its own, and learn how artificial intelligence and industrial robots work. Later stages cover simulation tools, connected devices and the Internet of Things, robotics for farming and community problems, and safety, ethics and responsible innovation. The last stage shows careers and business paths and guides your final project.</p><p>Every lesson has worked examples from East African situations, a practical demonstration, a case scenario, quick checks, a hands-on task and simple code you can copy. Safety is taught throughout: you will work with low-voltage batteries and USB power only, and learners under 18 should have an adult nearby when using tools or batteries. Choose one of seven final projects, such as an obstacle-avoiding robot, a smart irrigation system or an IoT monitoring dashboard, and build it with real parts or in a simulator.</p>',
    'outcomes'    => [
        'Explain what a robot is, the main robot types and where robotics is used',
        'Build and test simple low-voltage circuits and read circuit diagrams',
        'Write beginner programs for controlling LEDs, sensors and motors',
        'Choose sensors, motors, controllers and power supplies for a simple robot',
        'Plan, simulate and document a mobile robot that moves and avoids obstacles',
        'Explain how AI, industrial robots and connected IoT systems work',
        'Design an automated irrigation or monitoring system for a local community problem',
        'Apply safety, privacy and ethical practice, and prepare a robotics concept note and final project',
    ],
    'requirements' => ['A phone or computer with an internet connection', 'No electronics or programming experience needed', 'Free simulator accounts (the lessons show how to start free)', 'Optional: a basic Arduino-compatible kit, only if you want to build physically'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Arduino documentation', 'url' => 'https://docs.arduino.cc/'],
        ['title' => 'Tinkercad', 'url' => 'https://www.tinkercad.com/'],
        ['title' => 'Wokwi simulator', 'url' => 'https://wokwi.com/'],
        ['title' => 'Onshape', 'url' => 'https://www.onshape.com/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- STAGE 1
        [
            'title'   => 'Stage 1: Foundations',
            'summary' => 'Understand what robots are and where they are used, learn the basic electronics of voltage, current and circuits, and write your first programs to control an LED.',
            'lessons' => [
                [
                    'title'   => 'What is a robot? Concepts, history and types',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to define a robot, describe how robotics grew over time, name the main types of robot and explain how robotics joins engineering and computer science. This matters because every later skill in this course, from wiring to coding, makes more sense when you can see the whole picture first.</p>

<h3>Key concepts and what a robot is</h3>
<p><strong>Robotics</strong> is the field that designs, builds and programs machines that can sense their surroundings, make a decision and act on it. A useful way to remember this is the loop <strong>sense, think, act</strong>. A <strong>robot</strong> is a programmable machine that repeats this loop with little or no help from a person.</p>
<p>Most robots share these characteristics: a body or frame, sensors that collect information, a controller (a small computer) that decides, actuators such as motors that create movement, and a power source. The functions they perform are usually sensing, moving, manipulating objects (picking, pushing, spraying) and communicating.</p>

<h3>A short history</h3>
<ul>
<li>Early 1920s: the word "robot" appears in a Czech play, from a word meaning forced work.</li>
<li>1950s and 1960s: the first programmable industrial arm is installed in a car factory.</li>
<li>1970s to 1990s: robots spread in factories and the first wheeled research robots roam labs.</li>
<li>2000s: robot vacuum cleaners reach homes; drones and surgical robots grow.</li>
<li>Today: cheap boards, sensors and free simulators let a student in Kampala learn what once needed a university lab.</li>
</ul>

<h3>Types of robot</h3>
<table>
<thead><tr><th>Type</th><th>What it does</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Industrial</td><td>Fixed arm repeating factory tasks</td><td>Welding or packing arm</td></tr>
<tr><td>Service</td><td>Helps people at home or work</td><td>Robot vacuum cleaner</td></tr>
<tr><td>Mobile</td><td>Moves through a space</td><td>Warehouse cart, drone</td></tr>
<tr><td>Humanoid</td><td>Shaped like a person</td><td>Research walking robots</td></tr>
<tr><td>Collaborative</td><td>Works safely beside people</td><td>Light arm helping an assembler</td></tr>
</tbody>
</table>

<h3>Robotics, engineering and computer science</h3>
<p>Robotics sits where three skills meet. <strong>Mechanical engineering</strong> gives the frame, wheels and gears. <strong>Electrical and electronic engineering</strong> gives power, sensors and motor circuits. <strong>Computer science</strong> gives the program that makes decisions. A robot that fails is often a failure in the joint between two of these, such as a program that sends power to a motor that the battery cannot supply.</p>

<h3>Worked example: sense, think, act</h3>
<p>Take a simple automatic gate light at a school in Gulu. Sense: a light sensor reads brightness from 0 (dark) to 1023 (bright). Think: if the reading is below 300, it is dark. Act: switch on the lamp. If the evening reading is 250, the rule gives "dark", so the lamp turns on. If it is 600, the lamp stays off. Three parts, one loop, and you have described a tiny robotic system.</p>

<h3>Case scenario</h3>
<p>A farm cooperative near Gulu bought a "robot" sprayer from a dealer. It only moved when a worker held a remote button down, so it never decided anything. The cooperative expected it to spray alone and lost a week of crop protection. The leader later learned that a remote-controlled machine is not an autonomous robot. Lesson: ask what the machine senses and decides before you buy, and judge it by the sense-think-act loop, not the name on the box.</p>

<blockquote><p><strong>Quick check:</strong> Which three steps form the basic robot loop?</p><p>Answer: Sense, think and act, because a robot must collect information, decide, then move or change something.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A hand-held drill has a motor and moves. Is it a robot?</p><p>Answer: No, because it does not sense and decide on its own; a person controls every action.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Draw a box labelled "sense", a box "think" and a box "act" with arrows between them.</li>
<li>Pick one machine you know, such as an automatic door, and write what it senses, what rule it follows and what it does.</li>
<li>Write the three engineering fields and one part of the machine that belongs to each.</li>
<li>Share your drawing with a friend and see whether they agree with your answers.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A robot senses, decides and acts with little human help.</li>
<li>Main parts: frame, sensors, controller, actuators and power.</li>
<li>Types include industrial, service, mobile, humanoid and collaborative.</li>
<li>Robotics combines mechanical, electronic and software skills.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Robots in everyday life, careers and Africa',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to describe where robots are used, tell robots from ordinary machines and automated systems, and explain careers and innovation opportunities in robotics, including in Africa. This matters because knowing real needs helps you choose useful projects instead of copying gadgets.</p>

<h3>Applications across sectors</h3>
<table>
<thead><tr><th>Sector</th><th>Examples</th></tr></thead>
<tbody>
<tr><td>Agriculture</td><td>Automatic irrigation, crop-monitoring drones, milking and sorting machines</td></tr>
<tr><td>Healthcare</td><td>Surgical assistants, disinfection robots, medicine delivery</td></tr>
<tr><td>Education</td><td>Programmable kits that teach coding and logic</td></tr>
<tr><td>Manufacturing</td><td>Welding, painting, packing and inspection arms</td></tr>
<tr><td>Transportation</td><td>Driver-assist systems, warehouse vehicles, delivery drones</td></tr>
</tbody>
</table>

<h3>Automation and autonomous systems</h3>
<p><strong>Automation</strong> means using technology to do a task with less human effort, for example a timer that switches a pump on at six each morning. An <strong>autonomous system</strong> goes further: it senses its situation and chooses actions itself, such as a robot that steers around a person in a corridor.</p>

<h3>Robot, machine or automated system?</h3>
<table>
<thead><tr><th>Item</th><th>Senses?</th><th>Decides?</th><th>Verdict</th></tr></thead>
<tbody>
<tr><td>Hand drill</td><td>No</td><td>No (person decides)</td><td>Machine</td></tr>
<tr><td>Timer-based water pump</td><td>No (only a clock)</td><td>Fixed schedule</td><td>Automated system</td></tr>
<tr><td>Soil-moisture irrigation controller</td><td>Yes</td><td>Yes</td><td>Simple robotic system</td></tr>
</tbody>
</table>
<p>The lines are not sharp, and experts argue about them. The sense-and-decide test is a practical guide.</p>

<h3>Careers and opportunities</h3>
<p>Skills in this field lead to roles such as robotics technician, embedded-systems developer, automation engineer, mechatronics technician, drone operator, data analyst, teacher or trainer, and founder of a small solutions business. You can begin with free simulators and a laptop.</p>

<h3>Innovation and opportunities in Africa</h3>
<p>African communities have problems robotics can address: irrigation on small farms, solar-powered monitoring, sorting produce, and affordable teaching kits for schools. Local builders understand the cost, climate and repair conditions better than outsiders. Note the limits: imported parts can be costly and slow to arrive, and power can be unreliable, so good designs are simple, repairable and low in power use.</p>

<h3>Worked example: is automation worth it?</h3>
<p>A farmer in Mbarara hires a worker for 2 hours a day to water seedlings, 30 days a month, at UGX 2,000 per hour. Cost per month: 2 x 30 x 2,000 = UGX 120,000. An automatic watering controller might cost a one-off amount that varies by shop. If the parts cost UGX 240,000, payback is 240,000 divided by 120,000 = 2 months of saved labour, ignoring repairs and that the worker could do other jobs. Always do this sum before you build.</p>

<h3>Case scenario</h3>
<p>A school lab in Kampala was given a large robot arm by a donor, but there was no one trained to maintain it and spare parts were unavailable. It stood idle after a motor failed. A teacher then used a free simulator and cheap boards to teach the same ideas, and learners built working models. Lesson: a robot is only useful if people can run and repair it, so plan for training and spare parts, not just the purchase.</p>

<blockquote><p><strong>Quick check:</strong> A pump that switches on at 6 a.m. every day by a clock: robot or automated system?</p><p>Answer: An automated system, because it follows a fixed schedule and does not sense conditions.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Give one reason simple, repairable designs suit many African workshops.</p><p>Answer: Parts can be costly or slow to import, so a design that is cheap to fix keeps working.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Identify five robots or automated systems used in everyday life (for example automatic doors, a robot vacuum cleaner, a washing machine, a warehouse robot, a drone).</li>
<li>For each one, write its function and what it senses.</li>
<li>Mark each as machine, automated system or robot, using the sense-and-decide test.</li>
<li>Choose one career from this lesson and write two skills you would need for it.</li>
<li>Write one local problem in your village or town that a simple robotic system could help with.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Robots are used in farming, health, education, factories and transport.</li>
<li>Robots sense and decide; simple machines and timers do not.</li>
<li>Robotics skills open technical and business careers.</li>
<li>In Africa, simple, repairable and low-cost solutions to local problems are valuable.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Voltage, current, resistance and circuits',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to explain electrical and electronic systems, use Ohm's law and power calculations, describe a circuit, and recognise the main parts used in robots. This matters because wrong numbers burn parts, and a few simple sums prevent that.</p>

<p><strong>Safety first:</strong> in this course we use only low-voltage power: batteries and USB supplies up to about 12 V. Mains electricity in sockets and walls can kill. Never work on it; leave it to qualified electricians. Learners under 18 should have an adult nearby when using batteries or tools.</p>

<h3>Electrical and electronic systems</h3>
<p>An <strong>electrical system</strong> moves energy to do work, like a battery driving a motor. An <strong>electronic system</strong> uses small signals to sense and control, like a light sensor telling a controller it is dark. Robots use both.</p>

<h3>The four quantities</h3>
<ul>
<li><strong>Voltage (V), in volts:</strong> the push that drives charge.</li>
<li><strong>Current (I), in amperes:</strong> how much charge flows. Small currents are in milliamperes: 1 A = 1000 mA.</li>
<li><strong>Resistance (R), in ohms:</strong> how much a part opposes current.</li>
<li><strong>Power (P), in watts:</strong> the rate of using energy.</li>
</ul>
<p>The key formulas: <code>V = I x R</code> and <code>P = V x I</code>.</p>

<h3>Worked examples</h3>
<p><strong>Example 1.</strong> A 9 V battery across a 450 ohm resistor. Current = V / R = 9 / 450 = 0.02 A = 20 mA.</p>
<p><strong>Example 2.</strong> A small motor takes 0.5 A from 6 V. Power = 6 x 0.5 = 3 W.</p>
<p><strong>Example 3.</strong> A 2000 mAh battery powers a load of 200 mA. Run time is about 2000 / 200 = 10 hours (real batteries give less).</p>

<h3>Circuits</h3>
<p>A <strong>circuit</strong> is a closed loop from the battery's positive side, through parts, and back to the negative side. If the loop is broken, current stops: this is how a switch works. A <strong>short circuit</strong> is an unplanned low-resistance path that lets very high current flow, which heats wires and batteries. Never short a battery, especially Li-ion or LiPo.</p>

<h3>DC and AC</h3>
<p><strong>DC</strong> (direct current) flows one way, as from batteries and USB. <strong>AC</strong> (alternating current) reverses direction many times a second, as in wall sockets. Robots in this course use DC only.</p>

<h3>Components and their jobs</h3>
<table>
<thead><tr><th>Part</th><th>Function</th></tr></thead>
<tbody>
<tr><td>Resistor</td><td>Limits current, sets voltages</td></tr>
<tr><td>Capacitor</td><td>Stores a little charge, smooths power</td></tr>
<tr><td>Diode</td><td>Lets current pass one way only</td></tr>
<tr><td>Transistor</td><td>An electronic switch or amplifier controlled by a small signal</td></tr>
</tbody>
</table>

<h3>Case scenario</h3>
<p>Brian, a student in Jinja, connected a red LED straight to a 9 V battery for his first project. It flashed brightly, then died with a burnt smell. Without a resistor, nothing limited the current, so the LED took far more than its limit. He calculated a resistor for the next one (see the next lesson) and the replacement lasted for months. Lesson: always check the current before powering a part.</p>

<blockquote><p><strong>Quick check:</strong> What current flows when 5 V is across a 100 ohm resistor?</p><p>Answer: 0.05 A (50 mA), because I = V / R = 5 / 100.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What does a diode do?</p><p>Answer: It lets current flow in one direction only, which protects circuits from reversed connections.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Calculate the current for 12 V across 240 ohms, then the power (use P = V x I).</li>
<li>Open Tinkercad Circuits (free, account needed) and look for the option to create a new circuit.</li>
<li>Place a battery and a resistor, add a multimeter from the components list, and compare its reading with your sum.</li>
<li>Change the resistor value and see how the current changes.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>V = I x R and P = V x I.</li>
<li>A circuit needs a complete loop; a short circuit is dangerous.</li>
<li>Robots use DC; mains AC is never for learners.</li>
<li>Resistors, capacitors, diodes and transistors each have a clear job.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Building circuits: LEDs, batteries, breadboards and the multimeter',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to choose an LED resistor, use switches and breadboards, read a circuit diagram, measure with a multimeter and build a safe simulated circuit. This matters because almost every robot starts with these basic skills.</p>

<h3>LEDs, switches and push buttons</h3>
<p>An <strong>LED</strong> (light-emitting diode) has two legs: the longer leg is the anode (positive) and the shorter is the cathode (negative). It works only one way round and needs a series resistor. A <strong>switch</strong> stays open or closed; a <strong>push button</strong> connects only while pressed.</p>

<h3>Worked example: the LED resistor</h3>
<p>Formula: <code>R = (supply voltage - LED voltage) / current</code>. A red LED drops about 2 V and is happy at about 15 mA (0.015 A). With a 9 V battery: R = (9 - 2) / 0.015 = 466.7 ohms. Pick the next common value above: 470 ohms. Actual current = 7 / 470 = 0.0149 A, about 15 mA. With a 5 V supply and 220 ohms: (5 - 2) / 220 = 13.6 mA, which is safe. Resistor power: 3 x 0.0136 = 0.04 W, so a small resistor is fine.</p>

<h3>Batteries and power supplies</h3>
<p>Two AA cells in series give about 3 V; four give about 6 V. A 9 V battery is small in capacity. A USB phone charger or power bank gives 5 V. Li-ion and LiPo cells hold much energy, so never short, puncture or overcharge them, and never leave them charging unattended. Use a proper charger and keep them away from heat.</p>

<h3>Breadboards and connections</h3>
<p>A <strong>breadboard</strong> lets you build without soldering. Holes in a numbered row of five are joined; long outer strips (rails) carry power along the board. Place parts across the centre gap so legs are not connected to each other. Use red wires for positive and black for negative. Soldering and sharp tools need adult supervision for learners under 18.</p>

<h3>Circuit diagrams and symbols</h3>
<p>A diagram draws each part as a symbol and each wire as a line. The battery symbol has a long and a short line; a resistor is a zig-zag or small rectangle; an LED is a triangle with a bar and two arrows; a switch is a line with a gap. Reading diagrams lets you rebuild a circuit from a friend's design.</p>

<h3>Using a multimeter</h3>
<ol>
<li>Set the dial to DC voltage (look for a V with a straight line) in a range above your supply.</li>
<li>Touch the red probe to the positive point and black probe to the negative point.</li>
<li>Read the display.</li>
<li>For resistance, power the circuit off first, then use the ohm setting.</li>
<li>Never measure current without knowing how: the meter must be in series, and a wrong setting can blow its fuse.</li>
</ol>

<h3>Electrical safety when building</h3>
<p>Use low-voltage only, disconnect power before changing wires, check polarity, keep water away, and keep batteries from touching metal tools. Never open or work on mains-powered devices.</p>

<h3>Case scenario</h3>
<p>Aisha, a learner in Kampala, built a circuit with the LED reversed. Nothing lit, and she thought the battery was dead. She then compared her wiring to the diagram and found the long leg was on the negative side. She flipped it and it glowed. Another time she shorted the battery wires on a breadboard rail and the wire became hot. She disconnected quickly and learned to inspect before powering on. Lesson: check polarity and rail connections before every power-up.</p>

<blockquote><p><strong>Quick check:</strong> What resistor is needed for an LED (2 V, 10 mA) on a 5 V supply?</p><p>Answer: 300 ohms, because (5 - 2) / 0.01 = 300; use the next standard value up, such as 330 ohms.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why do you power off before measuring resistance?</p><p>Answer: The meter sends its own tiny current, and a live circuit gives wrong readings and may damage the meter.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>In Tinkercad Circuits, place a 9 V battery, a 470 ohm resistor, a switch and a red LED.</li>
<li>Wire them in a single loop: battery positive, switch, resistor, LED anode, LED cathode, battery negative.</li>
<li>Start the simulation and click the switch; the LED should light.</li>
<li>Remove the resistor and see what the simulator warns you about.</li>
<li>Take a screenshot and draw the matching circuit diagram by hand.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>LED resistor: R = (supply - LED voltage) / current.</li>
<li>LEDs and batteries have polarity; check it before power-on.</li>
<li>Breadboards connect rows of five holes; rails carry power.</li>
<li>A multimeter measures voltage and resistance safely when set correctly.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Thinking like a programmer: algorithms, variables, decisions and loops',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to explain programming's role in robotics, write an algorithm, and use variables, conditions, loops and functions in simple code. This matters because hardware does nothing useful until a program tells it what to do and when.</p>

<h3>Programming and its role</h3>
<p>A <strong>program</strong> is a set of exact instructions a controller follows. In a robot, the program reads sensors, makes decisions and drives motors and lights. The controller cannot guess: it does exactly what you wrote, including your mistakes.</p>

<h3>Algorithms and computational thinking</h3>
<p>An <strong>algorithm</strong> is a step-by-step method to solve a problem. <strong>Computational thinking</strong> means breaking a problem into small parts, spotting patterns, ignoring details that do not matter, and writing clear steps. Algorithm for an automatic tap: 1. Read the hand sensor. 2. If a hand is present, open the valve. 3. Otherwise close it. 4. Repeat.</p>

<h3>Variables and data types</h3>
<p>A <strong>variable</strong> is a named box that holds a value. Common <strong>data types</strong>: whole numbers (integers), numbers with decimals (floating point), true or false (boolean), and text (string).</p>
<pre>int distance = 25;        // Arduino C++: whole number
float temperature = 27.5; // decimals
bool isDark = true;       // true or false</pre>

<h3>Decisions with conditions</h3>
<p>An <strong>if</strong> statement runs code only when a test is true.</p>
<pre>if (distance &lt; 20) {
  // too close: stop
} else {
  // path clear: move
}</pre>
<p>Comparison signs: <code>&lt;</code> less than, <code>&gt;</code> greater than, <code>==</code> equal to, <code>!=</code> not equal to.</p>

<h3>Loops</h3>
<p>A <strong>loop</strong> repeats code. A <code>for</code> loop repeats a set number of times; a <code>while</code> loop repeats while a condition is true. Robot programs usually have a main loop that runs forever: sense, decide, act, repeat.</p>
<pre>for (int i = 1; i &lt;= 3; i++) {
  // blink the LED once
}</pre>

<h3>Functions and reusable code</h3>
<p>A <strong>function</strong> is a named block of code you can use many times. Instead of repeating ten lines, write them once and call the name.</p>
<pre>def stop_robot():
    print("motors off")

stop_robot()   # Python</pre>

<h3>Worked example: tracing a program</h3>
<p>Take the rule: "If the soil reading is below 400, water for 5 seconds." Readings arrive: 520, 390, 410. Trace: 520 is not below 400, so no water. 390 is below 400, so water for 5 seconds. 410 is not below 400, so no water. Total watering: 5 seconds. Tracing by hand like this finds bugs before running code. Notice that a reading of exactly 400 would not water, because the test uses "below", not "below or equal".</p>

<h3>Case scenario</h3>
<p>A student in Kampala wrote a rule "if distance is greater than 20, stop" for an obstacle robot, when he meant "less than 20". The robot stopped in open space and drove into a wall. He traced the program by hand with distances 50 and 10, saw the reversed result, and fixed the sign. Lesson: trace your logic with example numbers before testing, and test both sides of every condition.</p>

<blockquote><p><strong>Quick check:</strong> What data type suits the value "true" or "false"?</p><p>Answer: Boolean, because it holds only two states, like an on-or-off switch.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why do robot programs use a loop that never ends?</p><p>Answer: A robot must keep sensing and acting as long as it is switched on, so the cycle repeats continually.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write an algorithm in five numbered steps for an automatic classroom light that switches on when it is dark and someone is inside.</li>
<li>Choose a variable name and data type for each input in your algorithm.</li>
<li>Trace your algorithm with three sets of example values and write the result for each.</li>
<li>Rewrite one repeated step as a named function.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A program does exactly what you tell it, so be precise.</li>
<li>Algorithms are clear steps; computational thinking breaks problems down.</li>
<li>Variables store values; conditions decide; loops repeat; functions reuse code.</li>
<li>Trace code by hand with real numbers to catch mistakes.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Programming a controller: sensors, outputs, block code, Python and C++',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to read a sensor, control an LED or motor, compare block-based, Python and C/C++ styles, debug simple problems, and make an LED blink in a simulator. This matters because this is the moment your thinking becomes a real working system.</p>

<h3>Reading inputs from sensors</h3>
<p>A <strong>digital input</strong> reads on or off, such as a push button. An <strong>analogue input</strong> reads a range of values, such as light level. On an Arduino Uno, <code>analogRead()</code> returns 0 to 1023 for 0 to 5 V. Worked conversion: a reading of 512 means about 512 / 1023 x 5 = 2.5 V.</p>

<h3>Controlling outputs</h3>
<p>An output pin can switch an LED. A small LED takes a few milliamperes, which a pin can supply with a resistor. A motor needs far more current, so it is switched by a transistor or motor driver, never straight from the pin. We study this later in the course.</p>

<h3>Three ways to write code</h3>
<table>
<thead><tr><th>Style</th><th>Good for</th><th>Notes</th></tr></thead>
<tbody>
<tr><td>Block-based (Scratch, Blockly)</td><td>Beginners, ideas, logic</td><td>Drag blocks; no typing mistakes</td></tr>
<tr><td>Python / MicroPython</td><td>Readable code, boards that support it</td><td>Indentation matters</td></tr>
<tr><td>C/C++ (Arduino)</td><td>Most microcontrollers</td><td>Needs semicolons and braces</td></tr>
</tbody>
</table>

<h3>Blink in Arduino C++</h3>
<pre>const int LED_PIN = 13;

void setup() {
  pinMode(LED_PIN, OUTPUT);
}

void loop() {
  digitalWrite(LED_PIN, HIGH);
  delay(1000);   // wait 1000 ms
  digitalWrite(LED_PIN, LOW);
  delay(1000);
}</pre>
<p><code>setup()</code> runs once; <code>loop()</code> repeats forever. One second on plus one second off gives one blink every 2 seconds, so 30 blinks a minute.</p>

<h3>Blink in MicroPython</h3>
<pre>from machine import Pin
import time

led = Pin(2, Pin.OUT)   # pin number depends on the board

while True:
    led.value(1)
    time.sleep(1)
    led.value(0)
    time.sleep(1)</pre>

<h3>Debugging and troubleshooting</h3>
<ol>
<li>Read the error message, then find the line number.</li>
<li>Check spelling and capital letters, brackets and semicolons.</li>
<li>Check the hardware: wires, polarity, pin numbers.</li>
<li>Test one small piece at a time.</li>
<li>Print or blink a signal to learn what the program is doing.</li>
</ol>

<h3>Case scenario</h3>
<p>Peter, a hobbyist in Mbale, wrote Arduino code for an LED on pin 8 but wired it to pin 9. The code compiled, yet nothing happened. He assumed the board was faulty and almost bought another. A friend asked him to compare the pin in the code with the pin on the board, and the mismatch was found in a minute. Earlier he had also left out a semicolon, which gave a compile error. Lesson: when hardware does nothing, check the simple things first: pin numbers, wiring and spelling.</p>

<blockquote><p><strong>Quick check:</strong> What voltage does an analogue reading of 256 on a 0 to 1023, 5 V scale represent?</p><p>Answer: About 1.25 V, because 256 / 1023 x 5 is roughly 1.25.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why should a motor not be connected straight to a microcontroller pin?</p><p>Answer: A motor needs much more current than a pin can safely give, so a driver or transistor must switch it.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open Wokwi (free) or Tinkercad Circuits and look for the option to start a new Arduino project.</li>
<li>Add an LED and a 220 ohm resistor and connect them to the output pin you will use in your code.</li>
<li>Paste the blink program, then run the simulation and watch the LED switch on and off at regular intervals.</li>
<li>Change the delay to 250 ms and predict how many blinks per minute you will see (answer: 120).</li>
<li>Repeat with a block-based tool such as Scratch or a Blockly-based robotics environment, using a repeat-forever block.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Sensors give digital or analogue inputs; outputs drive LEDs and motors.</li>
<li>Motors need a driver or transistor, not a direct pin connection.</li>
<li>Block code, Python and C/C++ express the same ideas in different styles.</li>
<li>Debug methodically: read the error, check wiring, test small pieces.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which set of steps describes the basic robot loop?
- Plan, build, paint
* Sense, think, act
- Charge, store, release
- Read, write, print
= A robot senses its surroundings, decides what to do using its program, and acts using motors or other actuators.

Q: A pump switches on every day at 6 a.m. by a clock, with no sensor. How is it best described?
- A humanoid robot
- An autonomous robot
* An automated system
- A collaborative robot
= It follows a fixed schedule and does not sense conditions, so it is automation rather than a robot that senses and decides.

Q: A 9 V battery is connected across a 450 ohm resistor. What current flows?
- 2 mA
- 200 mA
* 20 mA
- 4 A
= Using I = V / R, the current is 9 / 450 = 0.02 A, which is 20 mA.

Q: You power a red LED (about 2 V drop, 15 mA target) from a 5 V supply. Which resistor is the best choice?
* 220 ohms
- 10 ohms
- 1 ohm
- 47 kilohms
= R = (5 - 2) / 0.015 = 200 ohms, so a common value near it such as 220 ohms is suitable. 10 ohms or less lets too much current through, and 47 kilohms would make the LED almost dark.

Q: A student connects an LED directly to a 9 V battery and it burns out. What is the most likely cause?
- The battery was too new
- The LED was the wrong colour
* There was no series resistor to limit the current
- The breadboard was too long
= Without a resistor, nothing limited the current, so the LED took more than its rating and failed.

Q: A small motor draws 0.5 A from a 6 V battery. How much power does it use?
- 12 W
- 0.083 W
- 6.5 W
* 3 W
= Power equals voltage times current: 6 x 0.5 = 3 W.

Q: A program uses the rule "if distance is greater than 20 cm, stop". The robot stops in open space and hits a wall. What is the likely fix?
- Use a bigger battery
* Change the rule to "less than 20 cm"
- Remove the loop
- Use a different colour LED
= The comparison is reversed: the robot should stop when something is closer than 20 cm. Tracing the logic with example distances reveals this kind of error.

Q: Which are good ways to debug a program that compiles but does nothing on the board? (choose all that apply)
* Check that the pin number in the code matches the wiring
- Replace the board straight away
* Check LED polarity and wire connections
* Test one small piece of the program at a time
= Simple checks of pins, wiring and small tests usually find the fault. Replacing parts without checking is slow and costly.
QUIZ,
        ],
        // ---------------------------------------------------------------- STAGE 2
        [
            'title'   => 'Stage 2: Hardware and Sensors',
            'summary' => 'Learn the physical parts of a robot, from frame and microcontroller to motors, power and the sensors that let it notice the world.',
            'lessons' => [
                [
                    'title'   => 'Anatomy of a robot: frame, brain, senses and muscles',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to name the main parts of a robot, compare Arduino and ESP32 boards, and explain what sensors and actuators do. This matters because a robot that fails is almost always failing in one particular part, and you can only fix a part you can name.</p>

<h3>The anatomy of a robot</h3>
<p>Every robot, from a toy car to a factory arm, is built from the same five families of parts. Think of a human body as a guide.</p>
<table>
<thead><tr><th>Robot part</th><th>Body comparison</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Frame (structure)</td><td>Skeleton</td><td>Acrylic or wooden chassis</td></tr>
<tr><td>Controller</td><td>Brain</td><td>Arduino Uno, ESP32</td></tr>
<tr><td>Sensors</td><td>Eyes, ears, skin</td><td>Ultrasonic distance sensor</td></tr>
<tr><td>Actuators</td><td>Muscles</td><td>DC motor, servo</td></tr>
<tr><td>Power supply</td><td>Food and blood</td><td>Battery pack</td></tr>
</tbody>
</table>

<h3>Frame and mechanical structure</h3>
<p>The <strong>frame</strong> holds everything in place and sets how strong and heavy the robot is. A frame must be rigid enough that motors do not twist it, light enough that small motors can move it, and roomy enough for wires and a battery. Low-cost options include a ready-made two-wheel kit, a piece of plywood, a cut plastic container or 3D-printed parts. A heavy frame needs bigger motors and a bigger battery, so every extra gram costs you twice.</p>

<h3>Microcontrollers</h3>
<p>A <strong>microcontroller</strong> is a tiny computer on one chip with pins that can read sensors and switch outputs. It runs one program, again and again, and has no screen or keyboard. It reads inputs, decides, then drives outputs. That loop is the heart of robotics.</p>

<h3>Arduino boards</h3>
<p>The <strong>Arduino Uno</strong> is the classic beginner board. It runs at 5 V logic, has 14 digital pins and 6 analogue input pins, and is programmed in C++ through the free Arduino IDE. It has a huge community, so almost every problem already has an answer online.</p>

<h3>ESP32 microcontrollers</h3>
<p>The <strong>ESP32</strong> is faster, has built-in Wi-Fi and Bluetooth, and costs about the same as a genuine Uno clone. Its pins use 3.3 V logic, so connecting a 5 V sensor output directly to an ESP32 pin can damage it. Choose Uno for first lessons and the ESP32 when the robot must talk over a network.</p>
<table>
<thead><tr><th>Feature</th><th>Arduino Uno</th><th>ESP32 dev board</th></tr></thead>
<tbody>
<tr><td>Logic voltage</td><td>5 V</td><td>3.3 V</td></tr>
<tr><td>Wireless</td><td>None built in</td><td>Wi-Fi and Bluetooth</td></tr>
<tr><td>Analogue reading</td><td>10-bit (0 to 1023)</td><td>12-bit (0 to 4095)</td></tr>
<tr><td>Best for</td><td>Learning basics</td><td>Connected projects</td></tr>
</tbody>
</table>

<h3>Sensors and actuators</h3>
<p>A <strong>sensor</strong> turns something in the world (distance, light, heat) into an electrical signal. An <strong>actuator</strong> turns electrical energy into action: movement, sound or light. Sensors are the inputs; actuators are the outputs. Here is the smallest complete robot loop, which reads a button and drives an LED.</p>
<pre><code>const int BUTTON = 2;
const int LED = 13;

void setup() {
  pinMode(BUTTON, INPUT_PULLUP);
  pinMode(LED, OUTPUT);
}

void loop() {
  bool pressed = (digitalRead(BUTTON) == LOW);
  digitalWrite(LED, pressed ? HIGH : LOW);
}</code></pre>
<p>Copy this component list template for any project: <code>Part | Purpose | Voltage | Quantity | Source</code>.</p>

<h3>Case scenario</h3>
<p>A Kampala school lab bought five ESP32 boards and reused 5 V ultrasonic sensors from an old Arduino kit. Three boards stopped responding within a week. The teacher found that the sensor echo pin was pushing 5 V into a 3.3 V pin. The fix was a small resistor divider on the echo line, and the lab added a rule: write the voltage of every part in the component list before wiring. The lesson: compatibility problems are quiet and expensive.</p>

<blockquote><p><strong>Quick check:</strong> Which part of a robot works like muscles?</p><p>Answer: The actuators, such as motors, because they turn electrical energy into movement.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why is a 5 V sensor output risky on an ESP32 pin?</p><p>Answer: ESP32 pins expect 3.3 V, so 5 V can damage the chip.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open Tinkercad Circuits (free account needed) and create a new circuit.</li>
<li>Place an Arduino Uno, a push button and an LED with a 220 ohm resistor.</li>
<li>Open the code editor, switch to text, and paste the listing above.</li>
<li>Wire the button between pin 2 and GND, and the LED to pin 13 through the resistor.</li>
<li>Start the simulation, press the button and note which robot part each component represents.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A robot has a frame, controller, sensors, actuators and a power supply.</li>
<li>Arduino Uno is 5 V and simple; ESP32 is 3.3 V and wireless.</li>
<li>Sensors are inputs; actuators are outputs.</li>
<li>Write voltages in your component list before you connect anything.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Motors, drivers, power and safe connections',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to choose a motor type, explain why a motor driver is needed, estimate battery runtime and connect parts without damaging them. This matters because the most common reason a first robot twitches, resets or smells hot is a power mistake, not a programming mistake.</p>

<h3>DC motors and gear motors</h3>
<p>A <strong>DC motor</strong> spins when voltage is applied; reverse the voltage and it spins the other way. Bare motors spin fast but weakly. A <strong>gear motor</strong> adds a gearbox that trades speed for turning force (torque). Example: a 48:1 gear motor whose bare motor turns at 9600 rpm gives 9600 / 48 = 200 rpm at the shaft, with about 48 times the force (less friction loss).</p>

<h3>Servo and stepper motors</h3>
<table>
<thead><tr><th>Motor</th><th>Strength</th><th>Weakness</th><th>Use</th></tr></thead>
<tbody>
<tr><td>Gear DC motor</td><td>Cheap, strong</td><td>No position knowledge</td><td>Wheels</td></tr>
<tr><td>Hobby servo</td><td>Goes to a set angle (about 0 to 180 degrees)</td><td>Limited rotation</td><td>Arms, steering, grippers</td></tr>
<tr><td>Stepper</td><td>Precise steps</td><td>Heavier, needs a driver</td><td>Printers, slides</td></tr>
</tbody>
</table>
<p>A common stepper makes 200 steps per turn, so each step is 360 / 200 = 1.8 degrees.</p>

<h3>Motor drivers and motor control</h3>
<p>A microcontroller pin can safely supply only about 20 mA, while a small motor draws hundreds. Never connect a motor to a pin. A <strong>motor driver</strong> (for example an L298N or similar module) sits between them: the board sends low-power signals, and the driver switches the battery current to the motors. Two direction pins reverse a motor; a speed pin takes a PWM signal (explained fully in Stage 3).</p>

<h3>Wheels, gears, belts and linkages</h3>
<p>Wheel size sets speed. A 65 mm wheel has circumference 3.14 x 65 = about 204 mm. At 200 rpm the robot travels 200 x 204 = 40,800 mm per minute, which is about 0.68 m per second. <strong>Gears</strong> change speed and force, <strong>belts</strong> move power between distant shafts quietly, and <strong>linkages</strong> (rods and joints) turn one motion into another, like a servo lifting a gripper.</p>

<h3>Power management and battery selection</h3>
<p>Battery runtime in hours equals capacity (mAh) divided by current draw (mA). A pack of 2000 mAh powering a robot that averages 500 mA lasts 2000 / 500 = 4 hours in theory. Allow only about 80 percent as usable: 3.2 hours. Four AA cells give about 6 V (alkaline) and suit small robots. <strong>Safety:</strong> lithium-ion and LiPo cells can overheat, swell or catch fire if shorted, punctured or overcharged. Use protected cells with a proper charger, never leave them charging unattended, and stay at or below about 12 V. Learners under 18 must work with an adult.</p>

<h3>Connecting hardware safely and compatibility</h3>
<ol>
<li>Switch the power off before changing any wire.</li>
<li>Join all grounds (GND) together so signals share a reference.</li>
<li>Check each part's voltage against the board's logic level.</li>
<li>Add a power switch and, for bigger motors, a fuse.</li>
<li>Power motors from the battery through the driver, not from the board's 5 V pin.</li>
</ol>

<h3>Case scenario</h3>
<p>At a workshop in Jinja, a team powered two motors and a servo from the Arduino 5 V pin. Whenever the robot started, the board rebooted (a brownout, when voltage dips so low the chip resets). They first blamed the code. A mentor measured the supply and saw it sag when the motors started. The fix was a separate battery pack for the motors, with grounds joined to the board. The lesson: motors are hungry and noisy, so give them their own supply.</p>

<blockquote><p><strong>Quick check:</strong> A 3000 mAh pack runs a load of 600 mA. What is the theoretical runtime?</p><p>Answer: 5 hours, because 3000 / 600 = 5.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why must a motor go through a driver?</p><p>Answer: A board pin supplies only about 20 mA, far less than a motor needs, so the driver switches the larger battery current.</p></blockquote>

<h3>Try it yourself</h3>
<p>Activity: draw and label a simple mobile robot. Use this layout as a template: <code>Part | Purpose | Connects to</code>.</p>
<ol>
<li>On paper, draw a rectangular chassis seen from above with two wheels and a small caster wheel.</li>
<li>Draw and label the microcontroller, motor driver, two gear motors, battery pack, switch and one ultrasonic sensor.</li>
<li>Draw arrows for power (battery to driver to motors) and signals (board to driver, sensor to board).</li>
<li>Write one sentence beside each label explaining its purpose.</li>
<li>Estimate the battery runtime using capacity divided by current.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Gear motors trade speed for force; servos hold angles; steppers move in exact steps.</li>
<li>Motors need a driver and their own power supply.</li>
<li>Runtime in hours is capacity in mAh divided by current in mA.</li>
<li>Join grounds, check voltages and treat lithium batteries with care.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'How sensors work: analogue, digital, distance, light and heat',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to explain why sensors matter, tell digital from analogue signals, and use ultrasonic, infrared, light, temperature and touch sensors in a simple program. This matters because a robot without sensors is blind: it can only repeat movements, not respond to a wall, a hot room or a person.</p>

<h3>Sensors and their importance</h3>
<p>A sensor lets a robot sense, then decide, then act. A farm in Gulu that waters on a fixed timer wastes water after rain; one with a soil sensor waters only when needed. Information from sensors is what turns a machine into a robot.</p>

<h3>Digital and analogue sensors</h3>
<p>A <strong>digital</strong> sensor gives only two states, HIGH or LOW, such as a push button. An <strong>analogue</strong> sensor gives a voltage that varies smoothly, such as a light sensor. The board converts it to a number with an analogue-to-digital converter (ADC). A 10-bit ADC on an Uno turns 0 to 5 V into 0 to 1023, so a reading of 512 is about 2.5 V.</p>

<h3>Ultrasonic distance sensors</h3>
<p>The common HC-SR04 sends a burst of sound and times the echo. Sound travels about 343 metres per second in air. The sound goes out and back, so divide by two.</p>
<p><strong>Worked example:</strong> the echo lasts 5800 microseconds, which is 0.0058 s. Distance = 343 x 0.0058 / 2 = about 0.99 m, or roughly 99 cm.</p>
<pre><code>const int TRIG = 9;
const int ECHO = 10;

void setup() {
  Serial.begin(9600);
  pinMode(TRIG, OUTPUT);
  pinMode(ECHO, INPUT);
}

void loop() {
  digitalWrite(TRIG, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG, LOW);
  long us = pulseIn(ECHO, HIGH);
  float cm = us * 0.01715;
  Serial.println(cm);
  delay(200);
}</code></pre>
<p>The factor 0.01715 is 0.0343 cm per microsecond divided by two.</p>

<h3>Infrared sensors</h3>
<p>An infrared (IR) sensor sends invisible light and checks how much bounces back. It is cheap and quick, ideal for line following and close obstacles, but strong sunlight confuses it.</p>

<h3>Light and colour sensors</h3>
<p>A light-dependent resistor (LDR) changes resistance with brightness. Pair it with a fixed resistor to form a <strong>voltage divider</strong>: Vout = Vin x R2 / (R1 + R2). With 5 V and two 10 kilohm resistors, Vout = 5 x 10 / 20 = 2.5 V. As light changes the LDR, Vout moves and the board reads the change. Colour sensors compare red, green and blue light to tell objects apart.</p>

<h3>Temperature and humidity sensors</h3>
<p>The TMP36 gives 0.5 V at 0 degrees Celsius plus 10 mV per degree. A reading of 160 on the Uno gives 160 x 5 / 1024 = 0.78 V, so the temperature is (0.78 - 0.5) x 100 = about 28 degrees Celsius. The DHT22 reads both temperature and humidity and sends them digitally.</p>

<h3>Touch and pressure sensors</h3>
<p>A bump switch on a robot's front gives a digital touch signal. Force-sensitive resistors vary with pressure, useful for gripper contact or weighing a small load.</p>

<table>
<thead><tr><th>Sensor</th><th>Type</th><th>Measures</th><th>Weakness</th></tr></thead>
<tbody>
<tr><td>HC-SR04</td><td>Digital pulse</td><td>Distance, about 2 cm to 4 m</td><td>Soft or angled surfaces</td></tr>
<tr><td>IR reflector</td><td>Analogue or digital</td><td>Near objects, lines</td><td>Sunlight</td></tr>
<tr><td>LDR</td><td>Analogue</td><td>Brightness</td><td>Needs a divider</td></tr>
<tr><td>TMP36</td><td>Analogue</td><td>Temperature</td><td>Self-heating, noise</td></tr>
</tbody>
</table>
<p>Use this sensor comparison sheet: <code>Sensor | Range | Output | Price band | Weakness | Chosen? Y/N</code>.</p>

<h3>Case scenario</h3>
<p>A student in Mbarara mounted an infrared obstacle sensor on a robot and tested it indoors, where it worked perfectly. Outside on the veranda in afternoon sun, the robot thought a wall was always in front and refused to move. Sunlight contains infrared, which flooded the sensor. The student shaded the sensor with a small tube and switched to ultrasonic for outdoor work. The lesson: test a sensor in the real place it will work.</p>

<blockquote><p><strong>Quick check:</strong> An echo returns after 2900 microseconds. Roughly how far is the object?</p><p>Answer: About 50 cm, because 2900 x 0.01715 is about 49.7 cm.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Is an LDR digital or analogue?</p><p>Answer: Analogue, because its output changes smoothly with brightness.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>In Tinkercad Circuits, add an Arduino Uno and an ultrasonic distance sensor.</li>
<li>Wire VCC to 5V, GND to GND, and the TRIG and ECHO pins as in the listing (some Tinkercad sensors have a single signal pin; look for the version with four pins).</li>
<li>Paste the code, open the Serial Monitor and start the simulation.</li>
<li>Drag the object closer and further away and note three readings.</li>
<li>Check one reading by hand using 343 m/s and half the echo time.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Sensors are a robot's inputs; digital gives two states, analogue gives a range.</li>
<li>Distance = speed of sound x echo time / 2.</li>
<li>A voltage divider turns resistance changes into readable voltage.</li>
<li>Always test sensors in their real working conditions.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Motion, vision, data and calibration for smart decisions',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to describe motion and vision sensors, read and calibrate sensor data, and build a decision rule that triggers an alert. This matters because raw sensor numbers are only useful when you know what they mean and trust them enough to act on them.</p>

<h3>Motion and orientation sensors</h3>
<p>Motion sensors tell a robot how it is moving and which way it is facing. A PIR sensor detects the heat of a moving person or animal and gives a simple digital signal. Orientation sensors measure tilt and turning so a robot can balance or know when it has been lifted or tipped.</p>

<h3>Accelerometers and gyroscopes</h3>
<p>An <strong>accelerometer</strong> measures acceleration, including gravity. Lying flat, it reads about 1 g on the vertical axis. Tilt it and that reading shifts to other axes, so tilt can be worked out. A <strong>gyroscope</strong> measures how fast something rotates, in degrees per second. If a gyroscope reads 30 degrees per second for 3 seconds, the robot has turned about 30 x 3 = 90 degrees. Small errors add up (drift), which is why robots often combine both. The MPU6050 is a popular low-cost module with both; Wokwi offers it in its parts list, so look for it with the add-part button.</p>

<h3>Cameras and computer vision sensors</h3>
<p>A camera gives a grid of colour values. <strong>Computer vision</strong> is software that finds meaning in it, such as a red ball, a face or a crop leaf. Cameras give rich information but need much more processing power and light than a distance sensor. A phone camera or an ESP32-CAM board can be used for learning; choose the simplest sensor that answers your question.</p>

<h3>Reading and interpreting sensor data</h3>
<p>Raw numbers mean nothing until converted. A soil sensor on an ESP32 may read about 3000 when dry and 1200 in water. Convert it to a percentage: moisture = (dry - reading) x 100 / (dry - wet). For a reading of 2100: (3000 - 2100) x 100 / 1800 = 50 percent. Real data is noisy, so average several readings before using them.</p>

<h3>Using sensor information to make decisions</h3>
<p>Decisions are rules: <em>if</em> the condition is true, <em>then</em> act. Avoid flicker by leaving a gap (hysteresis) between switch-on and switch-off levels, for example alert below 20 cm and clear above 25 cm.</p>
<pre><code>const int TRIG = 9;
const int ECHO = 10;
const int BUZZER = 8;
const float ALERT_CM = 20.0;

float readCm() {
  digitalWrite(TRIG, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG, LOW);
  long us = pulseIn(ECHO, HIGH, 30000);
  return us * 0.01715;
}

void setup() {
  pinMode(TRIG, OUTPUT);
  pinMode(ECHO, INPUT);
  pinMode(BUZZER, OUTPUT);
}

void loop() {
  float cm = readCm();
  bool near = (cm &gt; 0 &amp;&amp; cm &lt; ALERT_CM);
  digitalWrite(BUZZER, near ? HIGH : LOW);
  delay(100);
}</code></pre>

<h3>Calibration and troubleshooting</h3>
<p><strong>Calibration</strong> means comparing a sensor with a known reference and correcting it. Measure real distances with a ruler at 10, 30 and 50 cm and compare. Troubleshoot in order: power, wiring, code, then environment (sunlight, soft surfaces, electrical noise). Keep a calibration log with columns <code>Date | Sensor | Known value | Reading | Error | Fix</code>.</p>

<h3>Applications in agriculture, security and environment</h3>
<table>
<thead><tr><th>Field</th><th>Sensors used</th><th>Decision</th></tr></thead>
<tbody>
<tr><td>Agriculture</td><td>Soil moisture, temperature</td><td>Start irrigation below a set level</td></tr>
<tr><td>Security</td><td>PIR, door contact</td><td>Sound an alarm at night</td></tr>
<tr><td>Environment</td><td>Humidity, rain, water level</td><td>Log data and warn of flooding</td></tr>
</tbody>
</table>

<h3>Case scenario</h3>
<p>A youth group in Gulu built a moisture alarm for a vegetable bed. It worked in the shade of the workshop, but in the field it often read dry right after rain. They found their readings had never been calibrated: the sensor was tested only in dry soil and a cup of water, and the cable had been left lying in full sun. After they logged readings at dry, damp and soaked soil, and shaded the electronics, the alarm matched what a hand test showed. The lesson: calibrate in real conditions and protect the electronics.</p>

<blockquote><p><strong>Quick check:</strong> A gyroscope reads 45 degrees per second for 2 seconds. How far has the robot turned?</p><p>Answer: About 90 degrees, because 45 x 2 = 90.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why use different switch-on and switch-off distances?</p><p>Answer: The gap stops the alert flickering when readings hover near one limit.</p></blockquote>

<h3>Try it yourself</h3>
<p>Activity: build a simulated object-detection alert.</p>
<ol>
<li>In Tinkercad Circuits or Wokwi, add an Arduino, an ultrasonic sensor and a buzzer or LED.</li>
<li>Wire the buzzer to pin 8 and the sensor to pins 9 and 10 as in the listing.</li>
<li>Paste the code and run it. Move the object inside 20 cm and confirm the alert.</li>
<li>Change ALERT_CM to 40 and test again.</li>
<li>Write three readings in a calibration log and note any error.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Accelerometers sense tilt; gyroscopes sense turning speed.</li>
<li>Cameras are rich but demanding; pick the simplest sensor that works.</li>
<li>Convert raw readings into real units and average noisy values.</li>
<li>Calibrate in real conditions and keep a log.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A robot uses a 2400 mAh battery pack and draws an average of 600 mA. Using only 80 percent of the capacity, about how long will it run?
- 4 hours
- 5 hours
* 3.2 hours
- 2 hours
= 2400 mAh x 0.8 = 1920 mAh usable, and 1920 / 600 = 3.2 hours. The full 4 hours is only the theoretical figure.

Q: An ultrasonic sensor reports an echo time of 8000 microseconds. Using about 343 m/s, roughly how far is the object?
* 137 cm
- 274 cm
- 68 cm
- 13.7 cm
= 8000 x 0.01715 = 137 cm. The sound travels out and back, so the time is halved. Forgetting to halve would give 274 cm.

Q: A team connects two gear motors and a servo directly to the Arduino 5 V pin. The board keeps restarting whenever the robot starts. What is the best fix?
- Rewrite the program with shorter delays
* Power the motors from a separate battery through a motor driver and join the grounds
- Replace the Arduino with a larger frame
- Add a longer USB cable
= The motors draw a large starting current that makes the board voltage sag (a brownout). A separate motor supply with a shared ground solves it.

Q: Which part of a robot is an actuator?
- Ultrasonic distance sensor
- Arduino Uno
* Servo motor
- Battery holder
= Actuators turn electrical energy into action. A servo moves; the others sense, think or supply power.

Q: A 5 V sensor output is wired straight to an ESP32 input pin. What is the risk?
- The sensor will read too slowly
- The ESP32 will lose Wi-Fi
* The 5 V can damage the 3.3 V pin
- Nothing, both use the same logic level
= ESP32 pins are 3.3 V logic. Use a voltage divider or level shifter for 5 V signals.

Q: A 65 mm wheel is turned at 200 rpm by a gear motor. About how fast does the robot travel?
- 0.07 m/s
- 2.0 m/s
* 0.68 m/s
- 6.8 m/s
= Circumference is about 3.14 x 65 = 204 mm. 200 rpm x 204 mm = 40,800 mm per minute, about 0.68 m/s.

Q: Which sensors are analogue? (choose all that apply)
* A light-dependent resistor in a voltage divider
- A push button
* A TMP36 temperature sensor
- A bump switch with only open or closed states
= Analogue sensors give a smoothly varying voltage. Buttons and bump switches have only two states.

Q: A raw soil sensor reads 3000 when dry and 1200 when soaked. A reading of 1650 means about what moisture level?
- 25 percent
- 50 percent
- 90 percent
* 75 percent
= (3000 - 1650) x 100 / 1800 = 75 percent.
QUIZ,
        ],
        // ---------------------------------------------------------------- STAGE 3
        [
            'title'   => 'Stage 3: Movement, Building and Autonomy',
            'summary' => 'Make robots move with motors, PWM and drivers, build and test a complete first robot, then add obstacle avoidance and line following.',
            'lessons' => [
                [
                    'title'   => 'How robots move: motors, PWM and motor drivers',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to explain how a motor turns electricity into movement, choose a motor type for a simple robot, and control speed and direction with PWM and a motor driver. This matters because every robot that rolls, lifts or turns depends on this chain, and most beginner failures, such as a robot that will not move or one that spins in circles, start here.</p>

<h3>Mechanical movement and motor types</h3>
<p>Movement comes from an <strong>actuator</strong>, a part that turns energy into motion. Wheels roll, arms swing and grippers close. Choose the motor by the job.</p>
<table>
<thead><tr><th>Motor</th><th>Best for</th><th>How you control it</th></tr></thead>
<tbody>
<tr><td>DC motor</td><td>Spinning fast, such as fans</td><td>Voltage sets speed, polarity sets direction</td></tr>
<tr><td>Gear motor (DC motor plus gearbox)</td><td>Wheels of small robots</td><td>Same as DC; slower but stronger</td></tr>
<tr><td>Servo</td><td>Arm joints, steering, a turning sensor head</td><td>A pulse signal sets the angle</td></tr>
<tr><td>Stepper</td><td>Precise positions, such as a 3D printer</td><td>A driver moves it in small steps</td></tr>
</tbody>
</table>
<p>Cheap kit gear motors are often rated for roughly 3 to 6 V. Read the label first.</p>

<h3>Speed, direction and the H-bridge</h3>
<p>A DC motor turns faster with more voltage and reverses when current flows the other way. A <strong>motor driver</strong> contains an <strong>H-bridge</strong>: four switches arranged like the letter H. Closing one diagonal pair sends current one way through the motor, and closing the other pair reverses it. Never connect a motor straight to an Arduino pin: a pin can safely supply only about 20 mA, while a small gear motor can draw hundreds of milliamps, and much more when stuck.</p>

<h3>PWM: speed without changing the battery</h3>
<p><strong>Pulse-width modulation (PWM)</strong> switches the power on and off very fast. The share of time spent on is the <strong>duty cycle</strong>. On Arduino, <code>analogWrite(pin, value)</code> takes a value from 0 to 255, so duty = value / 255.</p>
<p><em>Worked example:</em> value 153 gives 153 / 255 = 0.6, a 60% duty cycle. On a 6 V supply the motor sees an average of about 6 x 0.6 = 3.6 V.</p>
<table>
<thead><tr><th>analogWrite value</th><th>Duty cycle</th></tr></thead>
<tbody>
<tr><td>0</td><td>0% (off)</td></tr>
<tr><td>128</td><td>about 50%</td></tr>
<tr><td>255</td><td>100% (full speed)</td></tr>
</tbody>
</table>
<p>On an Arduino Uno use the pins marked with a tilde (3, 5, 6, 9, 10, 11). Very low values may only hum because friction wins, so test where your motors start turning.</p>

<h3>Wiring and code for an L298N-type driver</h3>
<p>Many modules have these labels; check yours. ENA and ENB set speed, IN1 to IN4 set direction.</p>
<table>
<thead><tr><th>Driver label</th><th>Arduino pin</th><th>Job</th></tr></thead>
<tbody>
<tr><td>ENA</td><td>5</td><td>Left speed (PWM)</td></tr>
<tr><td>IN1, IN2</td><td>7, 8</td><td>Left direction</td></tr>
<tr><td>ENB</td><td>6</td><td>Right speed (PWM)</td></tr>
<tr><td>IN3, IN4</td><td>9, 10</td><td>Right direction</td></tr>
</tbody>
</table>
<pre>
const int ENA = 5, IN1 = 7, IN2 = 8;
const int ENB = 6, IN3 = 9, IN4 = 10;

// speed: -255 (full reverse) to 255 (full forward)
void setMotor(int en, int a, int b, int speed) {
  speed = constrain(speed, -255, 255);
  digitalWrite(a, speed &gt; 0 ? HIGH : LOW);
  digitalWrite(b, speed &lt; 0 ? HIGH : LOW);
  analogWrite(en, abs(speed));
}

void drive(int left, int right) {
  setMotor(ENA, IN1, IN2, left);
  setMotor(ENB, IN3, IN4, right);
}

void setup() {
  int pins[] = {ENA, IN1, IN2, ENB, IN3, IN4};
  for (int i = 0; i &lt; 6; i++) pinMode(pins[i], OUTPUT);
}

void loop() {
  drive(153, 153);   delay(2000);  // forward at 60%
  drive(0, 0);       delay(1000);  // stop
  drive(-153, -153); delay(2000);  // reverse
  drive(0, 0);       delay(1000);
}
</pre>
<p>Basic moves: forward is both positive, backward both negative, a gentle left curve has the left value smaller than the right, and stop is 0 and 0.</p>

<h3>Case scenario</h3>
<p>A robotics club at a Kampala school wired its first robot and ran the forward command. Instead of going straight, the robot spun in circles. The students blamed the code. A teacher asked them to lift the robot and watch the wheels: one turned forward and the other backward. One motor's two wires were swapped. They swapped the wires, and the robot drove straight. The lesson: check wiring and wheel direction on blocks before touching code.</p>

<blockquote><p><strong>Quick check:</strong> What duty cycle does analogWrite(pin, 51) give?</p><p>Answer: 20%, because 51 / 255 = 0.2.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why use a motor driver instead of connecting a motor to an Arduino pin?</p><p>Answer: A pin supplies only about 20 mA, far less than a motor needs, and the driver also reverses direction.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open Tinkercad (free, needs an account), choose Circuits and start a new circuit.</li>
<li>Place an Arduino Uno, a DC motor and a motor driver chip. Look for an H-bridge such as the L293D in the Components panel (names may vary). Follow its pin labels, which differ from the L298N table above.</li>
<li>Paste the code, change the pins to match your wiring, and start the simulation. Watch the motor spin, stop and reverse.</li>
<li>Change 153 to 100 and to 255 and note the speed difference.</li>
<li>With real parts, lift the robot off the table first and keep fingers clear of the wheels.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Choose motors by job: gear motors for wheels, servos for angles.</li>
<li>An H-bridge driver reverses current; Arduino pins cannot power motors.</li>
<li>PWM duty = value / 255, and it controls average voltage and speed.</li>
<li>Test the first move with the robot lifted off the table.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Steering, feedback and troubleshooting movement',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to steer a differential-drive robot, calculate distance and turning time from wheel size, use a sensor to correct movement, and find faults in a robot that will not drive properly. This matters because a robot that cannot go where you intend is useless, and these calculations let you plan before you build.</p>

<h3>Differential drive</h3>
<p>A <strong>differential-drive</strong> robot has two powered wheels, left and right, plus a free ball caster for balance. It steers only by running the wheels at different speeds.</p>
<table>
<thead><tr><th>Move</th><th>Left wheel</th><th>Right wheel</th></tr></thead>
<tbody>
<tr><td>Straight forward</td><td>forward</td><td>forward, same speed</td></tr>
<tr><td>Gentle left curve</td><td>forward, slower</td><td>forward, faster</td></tr>
<tr><td>Spin left on the spot</td><td>backward</td><td>forward</td></tr>
<tr><td>Reverse</td><td>backward</td><td>backward</td></tr>
<tr><td>Stop</td><td>0</td><td>0</td></tr>
</tbody>
</table>

<h3>Wheels, traction and balance</h3>
<p>Large wheels cover more ground per turn but need more torque. Rubber or tape on the rim improves <strong>traction</strong> (grip). Keep the battery low and central so the robot does not tip when braking. Smooth tiles are slippery; a rough classroom floor grips better.</p>

<h3>Worked calculations</h3>
<p><strong>Distance.</strong> Wheel diameter 65 mm. Circumference = 3.14 x 65 = about 204 mm. Five full turns move the robot 5 x 204 = 1,020 mm, about 1.02 m.</p>
<p><strong>Speed from RPM.</strong> A gear motor turns the wheel at 120 RPM. Distance per minute = 120 x 204 = 24,480 mm. Divide by 60 seconds: about 408 mm per second, or 0.41 m/s. Real speed is lower under load.</p>
<p><strong>Turning time.</strong> Wheels are 120 mm apart. To spin 90 degrees on the spot, each wheel follows a quarter circle of radius 60 mm: 2 x 3.14 x 60 / 4 = about 94 mm. At 100 mm/s the turn takes 94 / 100 = about 0.94 s. Treat this as a starting value, then adjust by testing.</p>

<h3>Commands, sensors and feedback</h3>
<p><strong>Open-loop</strong> control sends a command and hopes: "go forward for 2 seconds". <strong>Closed-loop</strong> control measures the result and corrects it, like steering a bicycle while looking at the road. Here a distance sensor makes the robot slow down as it approaches a wall and stop at 20 cm. The error is the gap between measured and wanted distance, and the speed is proportional to it.</p>
<pre>
const int TRIG = 12, ECHO = 13;

long readCm() {
  digitalWrite(TRIG, LOW);  delayMicroseconds(2);
  digitalWrite(TRIG, HIGH); delayMicroseconds(10);
  digitalWrite(TRIG, LOW);
  long us = pulseIn(ECHO, HIGH, 30000);
  if (us == 0) return 400;   // no echo: treat as far away
  return us / 58;            // microseconds to centimetres
}

void loop() {
  long error = readCm() - 20;                 // cm beyond the 20 cm target
  int speed = constrain(error * 5, 0, 200);   // slower as it gets close
  drive(speed, speed);                        // drive() from lesson 1
  delay(50);
}
</pre>
<p>If the wall is 60 cm away, error = 40 and speed = 200. At 30 cm, error = 10 and speed = 50. At 20 cm the robot stops.</p>

<h3>Troubleshooting movement</h3>
<ul>
<li><strong>Spins in circles:</strong> one motor reversed. Swap its two wires or flip its sign in code.</li>
<li><strong>Drifts to one side:</strong> motors differ. Test each at the same value and reduce the faster one.</li>
<li><strong>Hums but does not turn:</strong> value too low, wheel jammed or battery weak.</li>
<li><strong>Slow or random resets:</strong> low battery. Measure it with a multimeter.</li>
</ul>

<h3>Case scenario</h3>
<p>At a limited-budget workshop in Gulu, a robot ran perfectly in the morning but crawled in the afternoon and drifted left. The team suspected the code. A learner measured the battery pack with a multimeter and found it had dropped well below its rated voltage after many tests. They charged the cells and the speed returned. The drift remained, so they tested each motor alone and found the left one slightly faster, then reduced its value by about 5%. The lesson: check power first, then mechanics, then code.</p>

<blockquote><p><strong>Quick check:</strong> A wheel has a diameter of 50 mm. How far does the robot travel in 10 full turns?</p><p>Answer: About 1.57 m, because 3.14 x 50 = 157 mm per turn and 10 turns give 1,570 mm.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is the difference between open-loop and closed-loop control?</p><p>Answer: Closed-loop measures the result with a sensor and corrects it; open-loop only sends a command.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>In a simulator (Tinkercad Circuits or Wokwi; look for an ultrasonic distance sensor), build the setup with two motors or two LEDs standing in for motor directions.</li>
<li>Write a program that reads a command from the Serial Monitor: F forward, B reverse, L left spin, R right spin, S stop, using drive().</li>
<li>Run each command and confirm the robot moves, turns, reverses and stops.</li>
<li>Calculate the circumference of your wheel and the distance for 3 turns, then write both in your notebook.</li>
<li>With a real robot, test it lifted off the table first and keep fingers clear of the wheels.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Differential drive steers by wheel speed difference.</li>
<li>Distance = circumference x turns; speed = RPM x circumference / 60.</li>
<li>Closed-loop control uses sensor feedback to correct movement.</li>
<li>Troubleshoot in order: power, wiring, mechanics, code.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'First robot project: problem, requirements, parts and budget',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to choose a problem a robot can solve, write measurable requirements, plan the design, select hardware, price a parts list and describe an assembly diagram. This matters because most failed projects were never planned: a clear plan saves money and weeks of rework.</p>

<h3>Step 1: identify a problem</h3>
<p>Pick a problem that is small, real and solvable with simple movement and one or two sensors. A robotics club in Kampala chose this: "Our classroom floor is full of bags and desks, and we want a small rover that can explore the room without bumping into things." That fits an <strong>obstacle-avoiding rover</strong>. Better still, the problem is demonstrable: anyone can see whether it works.</p>

<h3>Step 2: requirements and objectives</h3>
<p>Requirements must be measurable so that you can test them.</p>
<table>
<thead><tr><th>Weak statement</th><th>Measurable requirement</th></tr></thead>
<tbody>
<tr><td>The robot should be fast</td><td>Travels 2 m in less than 10 seconds</td></tr>
<tr><td>It should avoid things</td><td>Stops or turns when an object is within 20 cm</td></tr>
<tr><td>It should run for a while</td><td>Runs at least 20 minutes on one set of cells</td></tr>
<tr><td>It should be cheap</td><td>Total cost under UGX 210,000</td></tr>
</tbody>
</table>

<h3>Step 3: plan the design</h3>
<p>Sketch the robot from above and from the side. Decide three things: the <strong>chassis</strong> (the frame), the <strong>drive</strong> (two driven wheels and one ball caster), and the <strong>brain</strong> (an Arduino-compatible board). Write a block diagram: battery to driver to motors, battery to board, board to driver, sensor to board.</p>

<h3>Step 4: select hardware</h3>
<p>Match parts to each other. The motor driver must handle the motor voltage and current, the battery must be inside the motor's rating after the driver's voltage drop (about 2 V for L298N-type modules), and the sensor must run on 5 V. Rechargeable cells save money after a few uses. Safety: use only AA cells or small packs in a proper holder. Li-ion and LiPo packs can burn if shorted, punctured or overcharged, so avoid them until an adult who knows how to charge them safely is supervising.</p>

<h3>Step 5: component list and basic budget</h3>
<p>These prices are illustrative only. Check local shops and online sellers.</p>
<table>
<thead><tr><th>Item</th><th>Qty</th><th>Unit (UGX)</th><th>Total (UGX)</th></tr></thead>
<tbody>
<tr><td>Arduino Uno-compatible board</td><td>1</td><td>40,000</td><td>40,000</td></tr>
<tr><td>L298N-type motor driver</td><td>1</td><td>15,000</td><td>15,000</td></tr>
<tr><td>Two-wheel chassis kit with 2 gear motors and wheels</td><td>1</td><td>60,000</td><td>60,000</td></tr>
<tr><td>Ball caster wheel</td><td>1</td><td>5,000</td><td>5,000</td></tr>
<tr><td>HC-SR04 ultrasonic sensor</td><td>1</td><td>8,000</td><td>8,000</td></tr>
<tr><td>6-cell AA battery holder</td><td>1</td><td>5,000</td><td>5,000</td></tr>
<tr><td>Rechargeable AA cells</td><td>6</td><td>6,000</td><td>36,000</td></tr>
<tr><td>Jumper wires pack</td><td>1</td><td>10,000</td><td>10,000</td></tr>
<tr><td>Small breadboard</td><td>1</td><td>8,000</td><td>8,000</td></tr>
<tr><td>Slide switch</td><td>1</td><td>2,000</td><td>2,000</td></tr>
</tbody>
</table>
<p>Sum: 40,000 + 15,000 + 60,000 + 5,000 + 8,000 + 5,000 + 36,000 + 10,000 + 8,000 + 2,000 = UGX 189,000. Add a 10% contingency for spare wires and mistakes: 189,000 x 0.10 = 18,900, so the budget is 207,900, inside the 210,000 requirement. The cell charger is not included.</p>
<p>Six rechargeable cells give about 6 x 1.2 = 7.2 V. After the driver's drop of about 2 V the motors see around 5 V, suitable for 3 to 6 V gear motors.</p>

<h3>Step 6: assembly diagram in words</h3>
<table>
<thead><tr><th>Position</th><th>What goes there</th></tr></thead>
<tbody>
<tr><td>Chassis bottom, rear</td><td>Two gear motors with wheels, left and right</td></tr>
<tr><td>Chassis bottom, front centre</td><td>Ball caster</td></tr>
<tr><td>Under the top deck</td><td>Battery holder, low and central</td></tr>
<tr><td>Top deck, centre</td><td>Arduino board and motor driver, side by side</td></tr>
<tr><td>Top deck, front edge</td><td>Ultrasonic sensor facing forward at about 5 to 8 cm above the floor</td></tr>
</tbody>
</table>

<h3>Case scenario</h3>
<p>A workshop in Gulu bought 12 V motors because they were cheap, but powered them with a 7.2 V pack. The robot moved so slowly it looked broken. They had to buy new motors and lost a week. The next team read the motor label first, wrote voltage in the requirements, and compared it with the battery. The lesson: match motor, driver and battery on paper before you buy.</p>

<blockquote><p><strong>Quick check:</strong> Why is "the robot should avoid things" a weak requirement?</p><p>Answer: It cannot be measured; "turns when an object is within 20 cm" can be tested.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A part costs UGX 8,000 and you need 3. What is the total?</p><p>Answer: UGX 24,000, because 3 x 8,000 = 24,000.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write one sentence that states a problem your robot will solve.</li>
<li>List four measurable requirements, each with a number.</li>
<li>Make a parts table with quantity, unit price and total. Use real prices from a seller you can check.</li>
<li>Add the totals, then add a 10% contingency and compare with your budget.</li>
<li>Draw a top view of the robot and label where each part sits.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Choose a small, demonstrable problem.</li>
<li>Requirements need numbers so they can be tested.</li>
<li>Match motor, driver, battery and sensor voltages.</li>
<li>Always add a contingency to a budget.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'First robot project: wiring, code, testing and documentation',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to wire the robot, write and upload its control program, test it step by step, record and fix faults, and document the project. This matters because a robot is only finished when it works reliably and someone else can repeat what you did.</p>

<h3>Step 7: wiring table</h3>
<p>Wire with the battery disconnected. Share one ground between battery, driver and Arduino.</p>
<table>
<thead><tr><th>From</th><th>To</th></tr></thead>
<tbody>
<tr><td>Battery positive, through the switch</td><td>Driver motor-supply terminal and Arduino VIN pin</td></tr>
<tr><td>Battery negative</td><td>Driver GND and Arduino GND</td></tr>
<tr><td>Arduino 5, 7, 8</td><td>Driver ENA, IN1, IN2</td></tr>
<tr><td>Arduino 6, 9, 10</td><td>Driver ENB, IN3, IN4</td></tr>
<tr><td>Arduino 12, 13</td><td>Sensor TRIG, ECHO</td></tr>
<tr><td>Arduino 5V and GND</td><td>Sensor VCC and GND</td></tr>
</tbody>
</table>
<p>Check your boards' own markings: VIN accepts roughly 7 to 12 V on many boards, and module terminal names vary.</p>

<h3>Step 8: the control program</h3>
<p>Rule: drive forward; if an object is closer than 20 cm, stop, reverse a little, spin right, then continue.</p>
<pre>
const int ENA = 5, IN1 = 7, IN2 = 8, ENB = 6, IN3 = 9, IN4 = 10;
const int TRIG = 12, ECHO = 13;
const int STOP_CM = 20, CRUISE = 150;

void setMotor(int en, int a, int b, int speed) {
  speed = constrain(speed, -255, 255);
  digitalWrite(a, speed &gt; 0 ? HIGH : LOW);
  digitalWrite(b, speed &lt; 0 ? HIGH : LOW);
  analogWrite(en, abs(speed));
}
void drive(int l, int r) { setMotor(ENA, IN1, IN2, l); setMotor(ENB, IN3, IN4, r); }

long readCm() {
  digitalWrite(TRIG, LOW);  delayMicroseconds(2);
  digitalWrite(TRIG, HIGH); delayMicroseconds(10);
  digitalWrite(TRIG, LOW);
  long us = pulseIn(ECHO, HIGH, 30000);
  return us == 0 ? 400 : us / 58;
}

void setup() {
  int out[] = {ENA, IN1, IN2, ENB, IN3, IN4, TRIG};
  for (int i = 0; i &lt; 7; i++) pinMode(out[i], OUTPUT);
  pinMode(ECHO, INPUT);
  drive(0, 0);
  delay(2000);               // time to place the robot
}

void loop() {
  if (readCm() &gt; STOP_CM) {
    drive(CRUISE, CRUISE);
  } else {
    drive(0, 0);        delay(200);
    drive(-CRUISE, -CRUISE); delay(400);
    drive(CRUISE, -CRUISE);  delay(500);   // spin right, tune this time
    drive(0, 0);        delay(100);
  }
}
</pre>

<h3>Step 9: test plan</h3>
<table>
<thead><tr><th>Test</th><th>Pass condition</th></tr></thead>
<tbody>
<tr><td>1. Power on, wheels lifted</td><td>Both wheels spin forward, same direction</td></tr>
<tr><td>2. Hand 15 cm in front of sensor</td><td>Wheels stop, reverse, then spin</td></tr>
<tr><td>3. On the floor, open space</td><td>Drives straight about 2 m</td></tr>
<tr><td>4. Box 15 cm from the robot</td><td>Turns away without touching</td></tr>
<tr><td>5. 20-minute run</td><td>Still moving, no resets</td></tr>
</tbody>
</table>

<h3>Step 10: debugging log</h3>
<table>
<thead><tr><th>Date</th><th>Symptom</th><th>Cause found</th><th>Fix</th><th>Result</th></tr></thead>
<tbody>
<tr><td>Day 1</td><td>Spins in circles</td><td>Right motor wires swapped</td><td>Swapped wires</td><td>Straight</td></tr>
<tr><td>Day 1</td><td>Arduino restarts when motors start</td><td>Weak, partly drained cells</td><td>Fresh charged cells</td><td>Stable</td></tr>
</tbody>
</table>

<h3>Steps 11 and 12: improve and document</h3>
<p>Improve one thing at a time: tune the 500 ms turn, reduce CRUISE if it hits things, add a second sensor later. Use this documentation template:</p>
<pre>
Project name:
Problem and requirements (with numbers):
Parts list and total cost:
Assembly description or sketch:
Wiring table:
Code (with date and version):
Test results (pass or fail for each test):
Faults found and fixes:
Improvements planned:
Who built it and when:
</pre>

<h3>Case scenario</h3>
<p>A school team in Kampala powered the Arduino and motors from one weak battery. When the motors started, the board restarted, so the robot stuttered forever. They thought the code looped wrongly. A mentor noticed the restart whenever the wheels began to turn: the motors had pulled the battery voltage down. The team used fresh cells and a thicker battery holder, then logged the fault. The lesson: unexplained restarts often mean a power problem, and a log shows the pattern.</p>

<blockquote><p><strong>Quick check:</strong> The Arduino restarts every time the motors start. What is the most likely cause?</p><p>Answer: The battery voltage drops under motor load, because weak cells cannot supply the current.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why test with the wheels lifted first?</p><p>Answer: A wrong command cannot send the robot off the table, and fingers stay safe.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Build the circuit in Tinkercad Circuits or Wokwi (look for the ultrasonic sensor; use LEDs for the motor-direction pins if there is no driver).</li>
<li>Upload the program and move the sensor object closer than 20 cm; confirm the outputs change as the test plan says.</li>
<li>Fill in the test plan with pass or fail.</li>
<li>Write two rows in your own debugging log, using real or practice faults.</li>
<li>Complete the documentation template. With real parts, build with an adult if you are under 18 and test with the wheels lifted.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Wire with power off and share one ground.</li>
<li>Test in stages, starting with the wheels lifted.</li>
<li>A debugging log turns mistakes into knowledge.</li>
<li>Documentation lets others repeat your robot.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Autonomous robots: navigation, obstacle avoidance and line logic',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to tell manual, remote-controlled and autonomous robots apart, explain basic navigation, and write obstacle-avoidance logic and plan line-following behaviour. This matters because autonomous robots do useful work, such as moving goods in a warehouse, without a person steering every move.</p>

<h3>Manual, remote and autonomous</h3>
<table>
<thead><tr><th>Type</th><th>Who decides</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Manual</td><td>Human drives with direct control</td><td>A toy car on a cable</td></tr>
<tr><td>Remote-controlled</td><td>Human decides, robot obeys by radio or phone</td><td>A Bluetooth rover</td></tr>
<tr><td>Autonomous</td><td>Robot senses and decides by its program</td><td>An obstacle-avoiding rover</td></tr>
</tbody>
</table>
<p>Many robots mix modes. Autonomous does not mean intelligent: it means the robot follows rules you wrote, using sensors, with little human help. A warehouse robot may follow a route alone but still wait for a person to approve each new job, and a farm rover may be remote-controlled near people and autonomous in an open field.</p>

<h3>Navigation basics</h3>
<p><strong>Navigation</strong> answers three questions: where am I, where do I want to go, and how do I get there safely. A simple robot has no map. It reacts: sense, decide, act, repeat. This loop is the heart of every autonomous program.</p>

<h3>Obstacle detection and avoidance</h3>
<p>An ultrasonic sensor sends a sound pulse and times the echo. Distance in cm = echo time in microseconds / 58. A 1,160 microsecond echo means 1,160 / 58 = 20 cm. The rule below picks a turn direction at random so the robot does not get stuck repeating the same turn. It uses <code>drive()</code> and <code>readCm()</code> from the previous lessons.</p>
<pre>
const int STOP_CM = 20;

void setup() {
  // pinMode lines for motors and sensor as before
  randomSeed(analogRead(A5));   // unconnected pin gives varying noise
  drive(0, 0);
  delay(2000);
}

void loop() {
  long d = readCm();
  if (d &gt; STOP_CM) {
    drive(150, 150);                     // clear: go forward
  } else {
    drive(0, 0);        delay(150);      // too close: stop
    drive(-150, -150);  delay(350);      // back away
    if (random(0, 2) == 0) drive(150, -150);   // spin right
    else                   drive(-150, 150);   // spin left
    delay(450);
    drive(0, 0);        delay(100);
  }
}
</pre>
<p>Note that <code>random(0, 2)</code> returns 0 or 1.</p>

<h3>Path following and line following</h3>
<p><strong>Path following</strong> means staying on a marked or planned route. A <strong>line follower</strong> uses two infrared reflectance sensors facing the floor, one each side of a black tape line on a light floor. The line sits between the sensors.</p>
<table>
<thead><tr><th>Left sensor</th><th>Right sensor</th><th>Meaning</th><th>Action</th></tr></thead>
<tbody>
<tr><td>sees floor</td><td>sees floor</td><td>On the line</td><td>Go forward</td></tr>
<tr><td>sees line</td><td>sees floor</td><td>Drifted right</td><td>Turn left</td></tr>
<tr><td>sees floor</td><td>sees line</td><td>Drifted left</td><td>Turn right</td></tr>
<tr><td>sees line</td><td>sees line</td><td>Crossing or finish</td><td>Stop</td></tr>
</tbody>
</table>
<p>Line following also works with one sensor on the edge of the line, but two sensors give a clearer left or right signal. Keep the sensors about 5 to 10 mm above the floor. The code comes in the next lesson. Sensor modules differ: some output HIGH over a dark line and others LOW, so you must test yours.</p>

<h3>Case scenario</h3>
<p>A Kampala school club built a line follower and tested it near a window. In the morning it followed the track; at noon it wandered off. Sunlight contains infrared light, which confused the sensors. The students decided to shade the sensors with a small cardboard skirt and moved the track away from the window. The robot followed the line again. The lesson: the environment can change sensor readings, so test in the conditions where the robot will work.</p>

<blockquote><p><strong>Quick check:</strong> An echo takes 2,320 microseconds. How far is the object?</p><p>Answer: 40 cm, because 2,320 / 58 = 40.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> The left line sensor sees the black line while the right sees floor. Which way should the robot turn?</p><p>Answer: Left, because the line is under the left sensor, which means the robot has drifted right of it.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open Wokwi and start a project with an Arduino Uno and an HC-SR04 sensor (look in the parts list).</li>
<li>Use LEDs on the direction pins to show forward, reverse and spin, then paste the program.</li>
<li>Move the simulated obstacle closer and further and watch which LEDs light.</li>
<li>Run it five times and record whether it spins left or right.</li>
<li>Draw a test track on paper: a 2 cm black line and one box obstacle.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Autonomous robots sense, decide and act in a loop.</li>
<li>Distance in cm = echo microseconds / 58.</li>
<li>Obstacle avoidance: stop, back up, turn, continue.</li>
<li>Line followers compare two floor sensors.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Decisions, feedback and testing autonomous behaviour',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to write a working line-following program, describe mapping and localisation at beginner level, structure robot decisions, apply feedback control, and test autonomous behaviour in a simulator. This matters because an autonomous robot must be trusted to behave sensibly when nobody is holding the controls.</p>

<h3>Sensors for movement decisions</h3>
<p>Choose the sensor for the question. Ultrasonic sensors answer "how far is the object?". Infrared floor sensors answer "is the floor dark or light?". A bump switch answers "have I touched something?". Using two kinds together makes a robot more dependable.</p>

<h3>Mapping and localisation (introduction)</h3>
<p><strong>Mapping</strong> builds a picture of the surroundings. <strong>Localisation</strong> works out where the robot is on that picture. Advanced robots use laser scanners or cameras. A beginner robot can estimate position by counting wheel turns: 5 turns of a 204 mm wheel is about 1,020 mm. This <strong>dead reckoning</strong> drifts over time because wheels slip, so it needs correction from sensors.</p>

<h3>Decision-making with states</h3>
<p>Give the robot a few named <strong>states</strong> and change state when a sensor condition is met: FOLLOW, TURN_LEFT, TURN_RIGHT and STOP. Only one state is active at a time, which keeps the program easy to read and test.</p>

<h3>Line-following code</h3>
<p>Assume sensor pins 2 (left) and 3 (right). The constant <code>ON_LINE</code> is HIGH for modules that output HIGH over a dark line; change it to LOW if yours work the other way. It uses <code>drive()</code> from earlier lessons.</p>
<pre>
const int LEFT_S = 2, RIGHT_S = 3;
const int ON_LINE = HIGH;     // test your module and change if needed
const int FAST = 140, SLOW = 60;

void setup() {
  // motor pinMode lines as before
  pinMode(LEFT_S, INPUT);
  pinMode(RIGHT_S, INPUT);
}

void loop() {
  bool left  = (digitalRead(LEFT_S)  == ON_LINE);
  bool right = (digitalRead(RIGHT_S) == ON_LINE);

  if (left &amp;&amp; right) {
    drive(0, 0);              // both on dark: crossing or finish
  } else if (left) {
    drive(SLOW, FAST);        // drifted right, curve left
  } else if (right) {
    drive(FAST, SLOW);        // drifted left, curve right
  } else {
    drive(FAST, FAST);        // line between sensors
  }
}
</pre>

<h3>Feedback control</h3>
<p>The code above reacts in steps. <strong>Proportional control</strong> corrects in proportion to the error: with analogue sensors the robot turns gently for a small error and sharply for a big one. For example, with a base speed of 120 and a correction of 0.5 x error, an error of 40 gives a correction of 20, so the wheel speeds become 100 and 140. Too large a multiplier makes the robot wobble, so raise it gradually.</p>

<h3>Environmental constraints</h3>
<table>
<thead><tr><th>Condition</th><th>Effect</th><th>Response</th></tr></thead>
<tbody>
<tr><td>Bright sunlight</td><td>Confuses infrared sensors</td><td>Shade sensors, test indoors</td></tr>
<tr><td>Soft or angled surfaces</td><td>Weak ultrasonic echo</td><td>Add a second sensor or bump switch</td></tr>
<tr><td>Dust, wet floor</td><td>Wheels slip</td><td>Slow down, clean wheels</td></tr>
<tr><td>Weak battery</td><td>Slower, less reliable</td><td>Charge before tests</td></tr>
</tbody>
</table>

<h3>Testing in simulated environments</h3>
<p>Test the simplest case first, then add difficulty: straight line, gentle curve, sharp turn, then gap or crossing. Record each run. Simulators save time but do not copy friction, slipping and light, so repeat on real hardware when you have it.</p>

<h3>Case scenario</h3>
<p>A Gulu workshop had a robot that avoided boxes in the simulator but hit a thin chair leg on the real floor. The ultrasonic sound beam missed the narrow leg. The team decided to add a bump switch in front, wired as an input, so the robot would stop and reverse on contact. After the change, collisions caused only a gentle stop. The lesson: a simulator shows the logic works; real tests show where the world differs.</p>

<blockquote><p><strong>Quick check:</strong> Why does dead reckoning drift?</p><p>Answer: Wheels slip and measurements are slightly off, so small errors add up over distance.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> In the line-follower code, what happens when only the right sensor sees the line?</p><p>Answer: The robot curves right, because FAST and SLOW are applied to the left and right wheels.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>In Wokwi or Tinkercad Circuits, add two inputs (switches or pushbuttons) to stand in for the line sensors on pins 2 and 3.</li>
<li>Paste the line-follower program and use LEDs for the wheel direction pins.</li>
<li>Press each switch combination and compare the result with the decision table.</li>
<li>Write a three-row test log: input, expected action, actual action.</li>
<li>Project: sketch a track with one curve and one obstacle, then describe how your robot would handle it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Pick sensors to match the decision.</li>
<li>States keep robot behaviour easy to follow and test.</li>
<li>Proportional control corrects in proportion to the error.</li>
<li>Test in stages, and always retest on real hardware.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A motor driver receives analogWrite(ENA, 204). What is the approximate duty cycle?
- 20%
- 64%
* 80%
- 100%
= 204 / 255 = 0.8, so the duty cycle is 80%.

Q: A robot spins in circles when it should go straight. The code looks right. What should you check first?
- Whether the Arduino is a clone
* Whether one motor's wires are swapped, by watching the wheels with the robot lifted
- Whether the sensor is dusty
- Whether the budget was correct
= One motor running backward makes the robot turn on the spot. Check wheel direction before editing code.

Q: A wheel has a 65 mm diameter and turns at 120 RPM. About how fast does the robot move? (Circumference about 204 mm.)
- About 0.04 m/s
- About 4 m/s
* About 0.41 m/s
- About 24 m/s
= 120 x 204 = 24,480 mm per minute, divided by 60 gives about 408 mm per second, or 0.41 m/s.

Q: Why should a motor never be connected straight to an Arduino pin?
- The pin voltage is too high for any motor
* The pin supplies only a small current, far less than a motor draws
- Motors only work with AC
- Arduino cannot output PWM
= A pin supplies only about 20 mA, while even a small gear motor needs far more. Use a motor driver.

Q: A club needs 6 rechargeable cells at UGX 6,000 each, a 6-cell holder at UGX 5,000 and a 10% contingency on that subtotal. What is the budget?
- UGX 41,000
* UGX 45,100
- UGX 46,000
- UGX 36,000
= Cells 6 x 6,000 = 36,000; plus 5,000 = 41,000; 10% is 4,100, so 41,000 + 4,100 = 45,100.

Q: In the obstacle-avoidance routine, which order of actions is the most sensible once the object is closer than the limit?
- Turn, stop, reverse
* Stop, reverse a little, turn, continue forward
- Reverse, drive forward faster, stop
- Keep driving and turn later
= Stopping first prevents a collision, backing away gives room, and then the turn lets the robot choose a new path.

Q: A line follower works indoors but wanders near a sunny window. What is the likely cause and best response?
- The motors are too strong; remove a motor
* Sunlight contains infrared light that confuses the sensors; shade them or move the track
- The line is too straight; add curves
- The Arduino is too cold; switch it off
= Infrared floor sensors can be disturbed by sunlight. Shading and testing in the real conditions fixes it.

Q: Which of these are features of closed-loop control? (choose all that apply)
* It measures the result with a sensor
* It corrects the output based on the error
- It sends a command and never checks the result
- It needs no sensors at all
= Closed-loop control compares what happened with what was wanted and adjusts. Open-loop control does not check.
QUIZ,
        ],
        // ---------------------------------------------------------------- STAGE 4
        [
            'title'   => 'Stage 4: AI and Industrial Robotics',
            'summary' => 'See how machine learning, computer vision and voice give robots new abilities, then learn how industrial arms, PLCs, conveyors and safety systems run modern production lines.',
            'lessons' => [
                [
                    'title'   => 'How AI gives robots new abilities: learning from data and seeing',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to explain how an AI-enabled robot differs from a conventional one, describe how a machine learning model is trained from data, and calculate how accurate an image-recognition model is. This matters because AI is now part of many farms, clinics and factories, and you need to judge honestly what it can and cannot do.</p>

<p><strong>An important note first:</strong> understand basic programming and sensors before you attempt advanced AI robotics projects. AI sits on top of those skills. If a robot cannot reliably read a distance sensor and drive a motor, adding a camera and a model will only make the problems harder to find.</p>

<h3>Conventional robot or AI-enabled robot</h3>
<p><strong>Artificial intelligence (AI)</strong> means computer systems that perform tasks which normally need human judgement, such as recognising a picture or understanding speech. A conventional robot follows rules that a person wrote: "if the distance is under 20 cm, stop". An AI-enabled robot also uses rules learned from examples.</p>
<table>
<thead><tr><th>Question</th><th>Conventional robot</th><th>AI-enabled robot</th></tr></thead>
<tbody>
<tr><td>Where do the rules come from?</td><td>A programmer writes them</td><td>Partly learned from example data</td></tr>
<tr><td>Best for</td><td>Repeating exact tasks in a fixed setting</td><td>Messy tasks, such as sorting different-looking fruit</td></tr>
<tr><td>Predictable?</td><td>Very</td><td>Usually right, sometimes wrong in new situations</td></tr>
<tr><td>Cost to build</td><td>Lower</td><td>Higher: data, testing and checking</td></tr>
</tbody>
</table>
<p>Use the simplest tool that works. A line-following robot does not need AI.</p>

<h3>Machine learning in plain words</h3>
<p><strong>Machine learning (ML)</strong> is a way of building software by showing it many labelled examples instead of writing every rule. The examples are the <strong>training data</strong>. After training, the <strong>model</strong> makes a guess, called a prediction, for a new picture it has never seen. No heavy maths is needed to use it well, but you must understand the data.</p>

<h3>Collecting good training data</h3>
<ol>
<li><strong>Define the labels:</strong> for coffee, "ripe" and "unripe".</li>
<li><strong>Collect varied photos:</strong> morning and afternoon light, different branches, wet and dry cherries, different phones.</li>
<li><strong>Balance the classes:</strong> roughly equal numbers of each label.</li>
<li><strong>Keep test photos apart:</strong> photos used to check the model must never be used to train it.</li>
</ol>

<h3>Computer vision, recognition and detection</h3>
<p><strong>Computer vision</strong> is software that extracts meaning from images. <strong>Image recognition</strong> labels a whole picture ("this is a ripe cherry"). <strong>Object detection</strong> finds each object and draws a box round it ("three cherries here, here and here"), so a robot arm knows where to reach. Browser-based image-recognition demos, for example Teachable Machine style tools, let you train a small model with your webcam and see its confidence for each label.</p>

<h3>Worked example: measuring accuracy</h3>
<p>A model that spots ripe coffee cherries is tested on 100 new photos: 40 truly ripe and 60 truly unripe. Results: 36 ripe photos correctly called ripe, 4 ripe called unripe, 55 unripe correctly called unripe, 5 unripe wrongly called ripe.</p>
<pre><code>Accuracy  = (36 + 55) / 100 = 91%
Precision = 36 / (36 + 5) = 36 / 41 = about 88%   (when it says ripe, it is right 88% of the time)
Recall    = 36 / 40 = 90%                          (it finds 90% of the truly ripe cherries)</code></pre>
<p>Precision matters if picking unripe cherries spoils the batch. Recall matters if you cannot afford to leave ripe ones on the tree.</p>

<h3>Case scenario</h3>
<p>A student team in Mbarara trained a sorter on 300 photos taken on one sunny afternoon. In the demo, indoors under a dim bulb, it called almost every cherry unripe. The team had trained it on one kind of light. They collected 200 more photos in shade, rain and indoors, retrained, and accuracy on a mixed test set rose from 62% to 89%. The lesson: a model is only as good as the variety in its training data.</p>

<blockquote><p><strong>Quick check:</strong> A model is right on 45 of 50 test photos. What is its accuracy?</p><p>Answer: 90%, because 45 divided by 50 is 0.9.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why must test photos be kept out of the training set?</p><p>Answer: Otherwise the model is tested on pictures it has already seen, so the score looks better than its real performance.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open a free browser-based image-recognition demo (a Teachable Machine style tool) and choose an image project.</li>
<li>Create two classes, for example "pen" and "phone", and record about 30 webcam samples of each, moving the object around.</li>
<li>Train the model and test it with new angles; note when the confidence drops.</li>
<li>Change the lighting or background, test again, and write down what broke.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Learn programming and sensors first; AI builds on them.</li>
<li>ML learns rules from labelled training data.</li>
<li>Varied, balanced data and separate test photos give honest accuracy.</li>
<li>Recognition labels a picture; detection finds where objects are.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Voice, language, decisions and the limits of AI robots',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to describe how a voice-controlled robot works, explain where AI helps in healthcare, agriculture and manufacturing, and name the main limits, biases and ethical risks. This matters because a robot that listens, watches and decides can also make unfair or unsafe mistakes, and the people who build it are responsible.</p>

<h3>Speech recognition and voice control</h3>
<p><strong>Speech recognition</strong> turns spoken sound into text. A <strong>voice-controlled robot</strong> then matches that text to a short list of commands. A simple chain looks like this:</p>
<ol>
<li>A microphone captures sound.</li>
<li>A recogniser turns it into text, such as "go forward".</li>
<li>Your program checks the text against allowed commands.</li>
<li>If it matches, the motors run; if not, the robot does nothing and says so.</li>
</ol>
<p>Keeping a small command list (forward, back, left, right, stop) makes it far more reliable than open conversation. Noise, accents, a second speaker and a weak microphone all reduce accuracy. Uganda has many languages, and recognisers often work better for some than others, so test with your real users.</p>

<h3>Natural language processing in brief</h3>
<p><strong>Natural language processing (NLP)</strong> helps computers work with human language. For a robot it can mean understanding that "come here", "please come" and "move to me" are the same request. At a beginner level you can use keyword matching first and treat full language models as an advanced step.</p>

<h3>AI-assisted decision-making</h3>
<p>The model does not simply act. A good design keeps a person in charge of important choices and uses a <strong>confidence threshold</strong>: the robot acts only when the model is sure enough.</p>
<table>
<thead><tr><th>Model confidence</th><th>Robot action</th></tr></thead>
<tbody>
<tr><td>90% or more</td><td>Act automatically</td></tr>
<tr><td>60% to 89%</td><td>Pause and ask a human to confirm</td></tr>
<tr><td>Below 60%</td><td>Do nothing and log the case</td></tr>
</tbody>
</table>

<h3>Where AI robots are used</h3>
<ul>
<li><strong>Healthcare:</strong> delivery trolleys that carry samples, and software that helps staff read scans. A qualified clinician makes the decision.</li>
<li><strong>Agriculture:</strong> cameras that spot leaf disease or count fruit, so a farmer visits the right plot first.</li>
<li><strong>Manufacturing:</strong> vision systems that check labels and find damaged items on a line.</li>
</ul>

<h3>Limits, bias, privacy and ethics</h3>
<p>AI can be confidently wrong. It struggles with situations unlike its training data, and it cannot explain itself well. <strong>Bias</strong> appears when the training data under-represents some people or conditions, so the model works well for some and badly for others. <strong>Privacy</strong> matters because cameras and microphones collect personal data: collect only what you need, tell people, store it safely and delete it when finished. Ask before you build: who could be harmed if it is wrong, and who decides?</p>

<h3>Worked example: what an error rate means</h3>
<p>A voice-controlled trolley is tested with 50 spoken commands and understands 43 correctly. Accuracy is 43 / 50 = 86%. The trolley receives about 200 commands a day, so the expected mistakes are 200 x 0.14 = 28 a day. If one of the five commands is "stop", a missed stop is a safety problem, not just an annoyance. So the design adds a physical emergency stop button and a bump sensor that does not depend on voice.</p>

<h3>Case scenario</h3>
<p>A small clinic in Gulu tried a camera system to greet patients and note who had arrived. It was trained mostly on photos of lighter-skinned adults taken in good light and often failed to detect darker-skinned patients and children. Staff stopped trusting it, and patients felt ignored. The team replaced the data with photos of the people who actually visit, added consent notices, and kept a paper register as backup. The lesson: biased data produces unfair results, and a manual fallback protects people.</p>

<blockquote><p><strong>Quick check:</strong> A voice robot understands 90 of 120 commands. What is its accuracy?</p><p>Answer: 75%, because 90 divided by 120 is 0.75.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why should a voice-controlled robot never rely only on voice for stopping?</p><p>Answer: Voice recognition can fail in noise or with accents, so a physical stop is needed to guarantee safety.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a free browser-based image-recognition or object-detection demo and run it on a few everyday objects.</li>
<li>Write what the demo outputs (a label, a confidence score, perhaps a box) and how often it is wrong.</li>
<li>Explain in five sentences how a robot could use that output, for example sorting waste or finding fruit, and which action needs a human check.</li>
<li>Write the confidence threshold you would use and why, then list two risks: one of bias, one of privacy.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Voice control works best with a short, fixed command list.</li>
<li>Use confidence thresholds and keep humans in charge of serious decisions.</li>
<li>Bias comes from unbalanced data; test with the people who will use the system.</li>
<li>Protect privacy and always provide a safe, non-AI fallback.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Industrial automation: robot arms, movement, pick-and-place and PLCs',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to describe what industrial automation is, name the main types of industrial robot arm, count degrees of freedom, and explain how a PLC controls a machine. This matters because factories, packing plants and processing lines create skilled jobs for people who understand how these systems fit together.</p>

<h3>Industrial automation and robotic arms</h3>
<p><strong>Industrial automation</strong> means using machines, sensors and controllers to run production with little human effort on repeated tasks. A <strong>robotic arm</strong> is a jointed machine that moves a tool, such as a gripper, a welding torch or a spray nozzle, to exact positions, again and again. Arms are used for welding, painting, assembly, packing, and loading and unloading machines.</p>

<h3>Common types of industrial robot</h3>
<table>
<thead><tr><th>Type</th><th>Shape and movement</th><th>Typical use</th></tr></thead>
<tbody>
<tr><td>Articulated arm</td><td>Several rotating joints, like a human arm</td><td>Welding, painting, loading</td></tr>
<tr><td>SCARA</td><td>Stiff sideways swing with short up-down motion</td><td>Fast assembly and small parts</td></tr>
<tr><td>Cartesian (gantry)</td><td>Straight-line movement along three axes</td><td>Packing into boxes, simple placing</td></tr>
<tr><td>Delta</td><td>Light arms hanging from above</td><td>Very fast picking of light items</td></tr>
</tbody>
</table>

<h3>Degrees of freedom</h3>
<p>A <strong>degree of freedom (DOF)</strong> is one independent way a joint can move, such as one rotation or one slide. The more DOF, the more positions and angles the tool can reach. To place an object anywhere in space and at any angle you generally need six DOF: three for position and three for orientation.</p>
<p><em>Worked example:</em> An articulated arm has six rotating joints, each moving in one way: base, shoulder, elbow, and three wrist joints. That is 1 + 1 + 1 + 3 = 6 DOF. A gantry with three straight sliding axes has 3 DOF: it reaches any point in its box but cannot tilt the tool. A small educational arm with a rotating base, shoulder, elbow and gripper open-close has 3 movement DOF plus a gripper action.</p>

<h3>Pick-and-place</h3>
<p><strong>Pick-and-place</strong> is the most common job: pick an item at point A and put it down at point B. The steps are the same every cycle:</p>
<ol>
<li>Move above the item.</li>
<li>Lower the arm.</li>
<li>Close the gripper (or turn on suction).</li>
<li>Lift.</li>
<li>Move above point B.</li>
<li>Lower and release.</li>
<li>Return to start.</li>
</ol>
<p>Each point is saved as a position, and a sensor confirms an item is present before the pick starts.</p>

<h3>Programmable logic controllers</h3>
<p>A <strong>programmable logic controller (PLC)</strong> is a rugged industrial computer that reads inputs (sensors, buttons), applies a stored program, and switches outputs (motors, valves, lamps), scanning continuously in a loop. Many PLCs are programmed with <strong>ladder logic</strong>, which looks like an electrical ladder. A rung says: if these inputs are true, switch this output on. In words and a table:</p>
<table>
<thead><tr><th>Rung</th><th>Inputs</th><th>Output</th></tr></thead>
<tbody>
<tr><td>1</td><td>Start button pressed AND emergency stop not pressed</td><td>Conveyor motor ON</td></tr>
<tr><td>2</td><td>Item sensor sees a bottle AND arm is idle</td><td>Start pick cycle</td></tr>
<tr><td>3</td><td>Emergency stop pressed</td><td>All motion OFF</td></tr>
</tbody>
</table>

<h3>Case scenario</h3>
<p>An illustrative packaging factory near Jinja wanted a robot to place cartons into crates. Engineers bought a small arm with only 3 DOF and no sensor. When a carton arrived slightly turned, the gripper crushed its corner, and 40 cartons were damaged in one shift. They added a presence sensor and a camera check on carton angle, and switched to a 4-DOF arm that could rotate its wrist. Damage fell to nearly zero. The lesson: pick the arm's DOF and sensors for the real variation in the job.</p>

<blockquote><p><strong>Quick check:</strong> An arm has a rotating base, shoulder, elbow and two wrist joints, each moving one way. How many DOF?</p><p>Answer: 5, because each joint adds one degree of freedom (1 + 1 + 1 + 2).</p></blockquote>

<blockquote><p><strong>Quick check:</strong> In ladder logic, what does a rung describe?</p><p>Answer: A condition and an action: when the listed inputs are true, an output turns on.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick an arm in a free online robot simulator or a drawing of any industrial arm.</li>
<li>List every joint and write its movement type, then add up the DOF.</li>
<li>Write the seven pick-and-place steps for moving a bottle cap from a tray to a bin.</li>
<li>Write a three-rung ladder table for a conveyor with a start button, a stop button and an emergency stop.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Industrial arms repeat precise movements; the main types are articulated, SCARA, Cartesian and delta.</li>
<li>DOF counts independent joint movements; six lets a tool reach any position and angle.</li>
<li>Pick-and-place is a repeated sequence of saved positions with sensor checks.</li>
<li>A PLC reads inputs, runs a program and switches outputs; ladder logic expresses it as rungs.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Production lines: sensors, conveyors, safety, quality and Industry 4.0',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to describe how sensors, actuators and conveyors combine into a production line, explain how robots and people work safely together, calculate line output and efficiency, and describe Industry 4.0. This matters because most robot value is in whole systems, and most accidents and lost output come from poor safety and poorly set sensors, not from the arm itself.</p>

<h3>Actuators and industrial sensors</h3>
<p>An <strong>actuator</strong> turns a command into movement: electric motors, pneumatic cylinders (moved by compressed air) and valves. An <strong>industrial sensor</strong> tells the controller what is happening: photoelectric sensors detect objects by a light beam, proximity sensors detect metal or other items without touching, and cameras check labels and shapes. Industrial sensors must be mounted firmly, kept clean and set to the right sensitivity.</p>

<h3>Conveyors and the production line</h3>
<p>A <strong>conveyor</strong> is a moving belt or set of rollers that carries items between stations. A line is a chain: items enter, a sensor detects them, an arm sorts or packs them, a check rejects bad ones, and the rest are packed.</p>
<p><em>Worked example, sorting line throughput:</em> A robot arm sorts bottles into two crates, and each pick-and-place cycle takes 3 seconds.</p>
<pre><code>Picks per minute = 60 / 3 = 20
Per hour         = 20 x 60 = 1,200
Planned shift    = 8 h = 480 min
Line stopped     = 48 min (changeovers, jams, breaks)
Running time     = 480 - 48 = 432 min
Availability     = 432 / 480 = 90%
Actual output    = 20 x 432 = 8,640 bottles (ideal 9,600)
Rejects at 2%    = 8,640 x 0.02 = about 173 bottles</code></pre>
<p>The lesson in the numbers: cutting stoppages from 48 to 24 minutes adds 24 x 20 = 480 bottles a shift, often cheaper than buying a faster arm.</p>

<h3>Human-robot collaboration</h3>
<p>A <strong>collaborative robot (cobot)</strong> is designed to work near people. It is usually lighter, slower and fitted with force limits so it stops when it touches something. Cobots suit jobs such as lifting boxes or holding a part while a person works. Even so, a risk assessment is needed, because a sharp tool or a heavy payload can still hurt someone.</p>

<h3>Industrial robot safety</h3>
<p>Industrial arms are strong and fast, so safety is built into the design. Standards for robot safety exist, and companies follow the ones that apply in their country. In general terms the ideas are:</p>
<ul>
<li><strong>Guarding:</strong> fences or light curtains keep people out of the robot's working area.</li>
<li><strong>Emergency stops:</strong> red buttons, within reach, that cut power to motion.</li>
<li><strong>Lockout and tagout:</strong> before maintenance, the energy source is switched off and locked with the technician's own lock and tag, so nobody can switch it on while someone works.</li>
<li><strong>Training and rules:</strong> only trained staff enter, and never when the robot is powered. Never enter a robot cell while it could move.</li>
</ul>

<h3>Quality control and Industry 4.0</h3>
<p><strong>Quality control</strong> uses sensors and cameras to check size, fill level, labels and colour, and removes bad items. <strong>Industry 4.0</strong> means connecting machines to networks and data: sensors report to dashboards, software predicts when a motor will fail, and managers see output in real time.</p>

<h3>Illustrative African examples</h3>
<ul>
<li>A bottling plant: sensors count bottles, check fill level, and reject low ones.</li>
<li>A coffee processing line: cameras and sorters remove discoloured beans before packing.</li>
<li>A packaging factory: arms place items into cartons and a scale confirms the weight.</li>
</ul>
<p>These are illustrations of how such systems work, not descriptions of particular companies.</p>

<h3>Case scenario</h3>
<p>On an illustrative bottling line, the arm stopped repeatedly although nothing was wrong. A technician found the photoelectric sensor had been knocked slightly upward, so it sometimes read a bottle's glare as a gap and told the PLC the line was empty. Each false stop cost about two minutes, and it happened 15 times a shift, wasting 30 minutes. After the sensor was re-aimed, fixed with a bracket and added to a weekly cleaning list, the stops vanished. The lesson: check sensors before blaming the robot.</p>

<blockquote><p><strong>Quick check:</strong> An arm completes one cycle in 4 seconds. How many cycles does it complete in one hour?</p><p>Answer: 900, because 3,600 seconds divided by 4 is 900.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What is the purpose of lockout and tagout?</p><p>Answer: It keeps machinery from starting during maintenance, because the energy is isolated and only the person who placed the lock can remove it.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Design a simulated sorting line on paper or in a free simulator: a conveyor, a sensor, an arm and two bins.</li>
<li>Write the rules: when the sensor sees a red item, the arm places it in bin A; otherwise in bin B.</li>
<li>Calculate output for a 5-second cycle over 60 minutes and for a 10% stoppage.</li>
<li>List three safety features your line needs and one quality check.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Production lines combine sensors, actuators, conveyors and a controller.</li>
<li>Output depends on cycle time and running time; reduce stoppages first.</li>
<li>Guarding, emergency stops, lockout and tagout, and training protect people; never enter a robot cell.</li>
<li>Industry 4.0 connects machines and data to improve quality and uptime.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What should a learner understand before starting advanced AI robotics projects?
- Only how to use a chatbot
* Basic programming and sensors
- How to build a factory conveyor
- Nothing; AI replaces the basics
= AI sits on top of basic skills. A robot that cannot read sensors or drive motors reliably cannot be fixed by adding a model.

Q: A model is tested on 200 new photos and gets 170 right. What is its accuracy?
- 70%
- 80%
* 85%
- 90%
= Accuracy is correct divided by total, so 170 / 200 = 0.85, or 85%.

Q: A rice-sorting robot works well in the lab but fails in a dusty, dim store. What is the most likely cause?
- The motors are too strong
- The model has too much training data
* The training data did not include dusty, dim conditions
- Ladder logic does not allow cameras
= A model performs poorly on conditions it never saw in training. The fix is to collect varied photos and retrain.

Q: A voice-controlled robot understands 42 of 50 commands. About how many mistakes would you expect among 300 commands?
* About 48
- About 8
- About 84
- About 258
= Accuracy is 42 / 50 = 84%, so the error rate is 16%. 300 x 0.16 = 48 mistakes.

Q: What does a confidence threshold do in AI-assisted decision-making?
- It makes the model more accurate
- It sets the robot's speed
* It lets the robot act only when the model is sure enough, and ask a human otherwise
- It deletes the training data
= A threshold separates automatic actions from cases a person should check, which reduces the harm of confident mistakes.

Q: A robot arm has a rotating base, one shoulder joint, one elbow joint and three wrist joints, each moving one way. How many degrees of freedom does it have?
- 3
- 5
* 6
- 7
= Each joint adds one degree of freedom: 1 + 1 + 1 + 3 = 6.

Q: An arm sorts items at 5 seconds per cycle. The line runs 60 minutes with no stoppage. How many items does it sort?
- 360
* 720
- 1,200
- 3,000
= 60 minutes is 3,600 seconds. 3,600 / 5 = 720 items.

Q: Before a technician reaches into a machine area for maintenance, what should happen?
- Slow the robot down and watch it
- Ask the operator to stand nearby
- Press the start button to test it
* Lock out and tag out the power so it cannot start, and follow the site rules
= Slowing or watching is not enough. Lockout and tagout isolates the energy so no one can start the machine while someone is working.
QUIZ,
        ],
        // ---------------------------------------------------------------- STAGE 5
        [
            'title'   => 'Stage 5: Simulation and Connected Systems',
            'summary' => 'Practise robot ideas safely in free simulators and CAD tools, then connect an ESP32 to a dashboard using MQTT, with security and low-bandwidth thinking for Uganda.',
            'lessons' => [
                [
                    'title'   => 'Simulation first: virtual circuits, robot models and microcontrollers',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to explain what robotics simulation is, list its advantages, and build and test a virtual circuit with a virtual microcontroller before buying any parts. This matters because a wrongly wired board or a wrong motor order can cost a learner a month of savings, while a mistake on screen costs nothing and can be undone in seconds.</p>

<h3>Simulation and virtual prototyping</h3>
<p>A <strong>simulation</strong> is a computer model that behaves like a real system closely enough to practise on. A <strong>virtual prototype</strong> is the first version of your robot, and it exists only on the screen. The advantages are clear:</p>
<ul>
<li><strong>Cost:</strong> no parts to buy, burn or lose.</li>
<li><strong>Safety:</strong> a short circuit in a simulator harms nobody.</li>
<li><strong>Speed:</strong> change a wire or a line of code and test again in seconds.</li>
<li><strong>Repeatability:</strong> run the same test many times in the same conditions.</li>
</ul>
<p>The limit is that a model is simpler than the real world. Simulators assume perfect wires and flat floors, so success there is a good sign, not a guarantee.</p>

<h3>Simple robot models</h3>
<p>Begin with the smallest model that answers your question. A first mobile robot model needs only a controller, two driven wheels, one distance sensor, an indicator LED and a power source. Add detail later.</p>

<h3>The free tools</h3>
<table>
<thead><tr><th>Tool</th><th>What it is generally used for</th></tr></thead>
<tbody>
<tr><td>Tinkercad Circuits</td><td>Beginner electronics and Arduino-style simulation in the browser</td></tr>
<tr><td>Wokwi</td><td>Simulating supported microcontrollers, including ESP32 boards, with parts and code</td></tr>
<tr><td>Webots</td><td>Simulating and programming whole robots moving in a 3D world</td></tr>
<tr><td>Gazebo</td><td>More advanced robot simulation, often used by professional robotics teams</td></tr>
<tr><td>Onshape</td><td>Browser-based mechanical and 3D design (CAD)</td></tr>
</tbody>
</table>
<p>Some of these tools need a free account, and advanced features may need installation or extra computer resources such as a stronger processor and more memory. Menus change over time, so follow each tool's own current tutorials and look for the parts list, the code editor and the start-simulation control.</p>

<h3>Simulating a circuit and programming a virtual microcontroller</h3>
<p>In a circuit simulator you drag parts onto a board, connect them with wires, write code and press start. Below is a short Arduino C++ program for a virtual Uno with an ultrasonic distance sensor and an LED alert. Distance in centimetres is the echo time in microseconds multiplied by 0.034, divided by 2.</p>
<pre><code>const int trigPin = 9;
const int echoPin = 10;
const int ledPin = 13;

void setup() {
  pinMode(trigPin, OUTPUT);
  pinMode(echoPin, INPUT);
  pinMode(ledPin, OUTPUT);
}

void loop() {
  digitalWrite(trigPin, LOW);  delayMicroseconds(2);
  digitalWrite(trigPin, HIGH); delayMicroseconds(10);
  digitalWrite(trigPin, LOW);
  long us = pulseIn(echoPin, HIGH);
  float cm = us * 0.034 / 2;
  digitalWrite(ledPin, cm &lt; 20 ? HIGH : LOW);
  delay(100);
}</code></pre>

<h3>Testing sensor and motor behaviour: a worked example</h3>
<p>The loop above checks every 100 ms, which is 10 readings per second. A robot moving at 0.5 m/s (50 cm per second) travels 50 / 10 = 5 cm between two readings. With an alert at 20 cm, the robot still has about 15 cm of margin. If the loop used delay(500), the robot would travel 25 cm between readings, which is more than the 20 cm alert distance, so it could hit a wall before it ever noticed it.</p>

<h3>Case scenario</h3>
<p>Brenda, a student in Mbale, built a line of LEDs and a distance sensor in a simulator and it worked perfectly. She wired the same design on a breadboard, but the sensor always read zero. The simulator never showed a missing common ground between the battery pack and the board. She checked each connection with a multimeter, joined the grounds and the robot worked. The lesson: a simulation assumes perfect wiring, so always check power, ground and connections on the real build.</p>

<blockquote><p><strong>Quick check:</strong> Name two advantages of testing a design in a simulator before building it.</p><p>Answer: Lower cost and safety (also speed), because mistakes cost no parts and cannot harm anyone.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A robot moves at 40 cm per second and reads its sensor every 250 ms. How far does it travel between readings?</p><p>Answer: 10 cm, because 40 cm per second multiplied by 0.25 seconds is 10 cm.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open a free simulator such as Tinkercad Circuits or Wokwi and follow its current beginner tutorial.</li>
<li>Add a virtual Arduino-style board, an LED and a resistor, and make the LED blink.</li>
<li>Add a distance sensor and paste the program above, adjusting pin numbers to match your wires.</li>
<li>Move the virtual obstacle and note the distance at which the LED turns on.</li>
<li>Change delay(100) to delay(500) and write down what changes.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Simulation lets you prototype cheaply, safely and quickly, but it is a simplified model.</li>
<li>Start with a very simple robot model and add detail later.</li>
<li>Each tool has a purpose; some need accounts, and advanced ones may need installation or extra resources.</li>
<li>Sensor reading speed and robot speed together decide how safely a robot can react.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Movement, CAD and documenting: from virtual tests to a simulation report',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to simulate simple movement and obstacle avoidance, sketch a basic mechanical part in CAD, debug a simulated system methodically, and present your results in a clear report. This matters because employers, teachers and funders believe a design more readily when you can show tests, evidence and honest notes about what went wrong.</p>

<h3>Simulating movement and obstacle avoidance</h3>
<p>Robot simulators such as Webots and Gazebo place a robot in a virtual world with floors, walls and obstacles, and let your program drive virtual wheels. Obstacle avoidance can be described with three rules that you can read in code or in block form:</p>
<ol>
<li>If the distance is more than 20 cm, drive forward.</li>
<li>If the distance is 20 cm or less, stop, then turn right for a short time.</li>
<li>Measure again and repeat.</li>
</ol>
<p>Test rules in layers: sensor value first, then the stop, then the turn. Added all at once, you cannot tell which part failed.</p>

<h3>CAD for robotics: designing simple mechanical parts</h3>
<p><strong>CAD</strong> means computer-aided design: drawing parts in exact sizes on a computer. A beginner CAD tool such as Onshape works in the browser and builds a part in two stages. You draw a flat <strong>sketch</strong> with dimensions, then you give it thickness with an extrude style tool. Follow the tool's current beginner tutorial.</p>
<p>Worked example: you want a flat bracket to hold a small gear motor. The motor body is 25 mm wide and 40 mm long. Real printing and cutting are never perfect, so allow a small clearance, say 0.5 mm, on each side. The slot should be 25 + 0.5 + 0.5 = 26 mm wide. If you drew exactly 25 mm, the motor might not fit.</p>
<p>Test designs virtually before making them: assemble the parts, check that nothing overlaps and that wheels do not touch the frame. Fixing a drawing costs nothing; fixing plastic or metal does.</p>

<h3>Debugging simulated systems</h3>
<ol>
<li><strong>Reproduce:</strong> make the fault happen again on purpose.</li>
<li><strong>Observe:</strong> print values to the serial monitor, or watch the pins, instead of guessing.</li>
<li><strong>Isolate:</strong> remove parts until only the faulty one remains.</li>
<li><strong>Change one thing:</strong> then test again.</li>
<li><strong>Record:</strong> write what the fault was and what fixed it.</li>
</ol>
<p>Common faults are a wrong pin number, a missing ground wire and an LED without a resistor.</p>

<h3>Documenting and presenting results</h3>
<p>A simulation is only useful if others can repeat it. Copy this template into a document and complete it:</p>
<pre><code>SIMULATION REPORT
Title:                      Date:        Tool used:
Goal (one sentence):
Parts used (virtual):
Circuit (screenshot or project link):
Program (link or file name, version):
Test table:
  Test | Input        | Expected | Actual | Pass or fail
  1    | obstacle 50 cm | LED off  |        |
  2    | obstacle 15 cm | LED on   |        |
Problems found and fixes:
Differences to expect on a real build:
Next step:</code></pre>
<p>When presenting, show the goal first, then a short demonstration, then the test table, then your honest list of limits.</p>

<h3>Case scenario</h3>
<p>Isaac, a learner in Jinja, designed a robot chassis in CAD with motor slots exactly the width of the motors. In the simulation everything fitted. When a friend with a 3D printer made the part, the motors would not go in, and the project lost two weeks waiting for a second print. Isaac added clearance and recorded the fault in his report. The lesson: exact sizes work on screen, but real manufacturing needs tolerance.</p>

<blockquote><p><strong>Quick check:</strong> A part is 30 mm wide and you want 0.5 mm clearance on each side. How wide should the slot be?</p><p>Answer: 31 mm, because 30 plus 0.5 plus 0.5 equals 31.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why test sensor reading, stopping and turning one at a time?</p><p>Answer: Testing in layers shows exactly which step fails, whereas testing everything together hides the cause.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open a free circuit simulator and build a small robot circuit: a controller, a distance sensor, an LED and, if the tool allows, one motor with its driver.</li>
<li>Program it to turn on the LED (or stop the motor) when an obstacle is within 20 cm.</li>
<li>Run at least four tests at different distances, such as 50, 30, 20 and 10 cm, and fill in the test table.</li>
<li>Deliberately remove a ground wire and write how you found the fault.</li>
<li>Complete the simulation report template, add screenshots and share it with one classmate for feedback.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Test movement in layers: sensor first, then stop, then turn.</li>
<li>CAD turns a dimensioned sketch into a part; allow clearance for real manufacturing.</li>
<li>Debug by reproducing, observing, isolating and changing one thing at a time.</li>
<li>A good report includes goal, tests, problems and honest limits.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Connected things: IoT, ESP32 and sending sensor data',
                    'minutes' => 22,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to explain the Internet of Things, tell it apart from a robot, choose between Wi-Fi and Bluetooth, and plan how an ESP32 sends sensor readings to a dashboard without wasting your data bundle. This matters because a farmer in Mbarara or a shop owner in Kampala can watch a water tank or fridge from a phone, if the system is designed with data costs in mind.</p>

<h3>IoT, and IoT devices versus robots</h3>
<p>The <strong>Internet of Things (IoT)</strong> means everyday objects with sensors and a network connection that share information. An <strong>IoT device</strong> usually senses and reports, and sometimes switches something. A <strong>robot</strong> senses, decides and moves in the physical world. The two overlap in a connected robot, for example a robot that sends its battery level to your phone.</p>
<table>
<thead><tr><th>Question</th><th>IoT device</th><th>Robot</th></tr></thead>
<tbody>
<tr><td>Main job</td><td>Measure and report</td><td>Sense, decide and act</td></tr>
<tr><td>Movement</td><td>Often none</td><td>Usually moves or manipulates</td></tr>
<tr><td>Example</td><td>Tank level sensor</td><td>Obstacle-avoiding robot</td></tr>
</tbody>
</table>

<h3>Wi-Fi or Bluetooth</h3>
<table>
<thead><tr><th>Feature</th><th>Wi-Fi</th><th>Bluetooth</th></tr></thead>
<tbody>
<tr><td>Typical range</td><td>Across a house or compound, depending on the router</td><td>Short, often a few metres to tens of metres</td></tr>
<tr><td>Power use</td><td>Higher</td><td>Lower, especially low-energy modes</td></tr>
<tr><td>Best for</td><td>Reaching the internet through a router</td><td>Talking directly to a phone</td></tr>
</tbody>
</table>
<p>If the device must report to the internet, use Wi-Fi. If a phone nearby is the only receiver, Bluetooth can save battery.</p>

<h3>The ESP32</h3>
<p>The <strong>ESP32</strong> is a low-cost microcontroller with Wi-Fi and Bluetooth built in, so you do not need extra wireless parts. It can be programmed with the Arduino-style C++ you already know or with MicroPython. Beginner projects include a soil moisture reader, a shed temperature logger and a water-tank level alert. It is low voltage only: power it from USB or a suitable battery supply, never from mains.</p>
<p>A short MicroPython program to join your Wi-Fi network:</p>
<pre><code>import network, time
wlan = network.WLAN(network.STA_IF)
wlan.active(True)
wlan.connect("YOUR_WIFI_NAME", "YOUR_WIFI_PASSWORD")
while not wlan.isconnected():
    time.sleep(1)
print(wlan.ifconfig())</code></pre>

<h3>Sending sensor data to a dashboard: counting the data</h3>
<p>A <strong>dashboard</strong> is a web page that shows your readings as numbers, gauges or graphs. Every reading you send uses data. Suppose each reading is about 50 bytes and you send one every 10 seconds:</p>
<ul>
<li>Readings per day: 6 per minute x 60 x 24 = 8,640.</li>
<li>Data per day: 8,640 x 50 bytes = 432,000 bytes, about 0.43 MB.</li>
<li>Per month: about 13 MB.</li>
</ul>
<p>That is small. But each web request also carries headers and secure-connection set-up, so assume about 5 KB per request (an estimate). Then sending every 5 seconds is 17,280 requests per day, or 86 MB per day. A 1 GB phone bundle used as a hotspot would be gone in about 11 to 12 days. Sending every 10 minutes instead is 144 requests, only about 0.7 MB per day.</p>

<h3>Remote monitoring and control</h3>
<p><strong>Monitoring</strong> means reading values from far away. <strong>Control</strong> means sending a command back, such as switching a low-voltage relay or a small pump. Control is riskier: confirm the new state with a sensor, set a maximum run time and stop safely if the link is lost.</p>

<h3>Case scenario</h3>
<p>Florence, a student in Gulu, built an ESP32 tank monitor that sent a reading every second over her phone hotspot, using a secure web request each time. Her 2 GB bundle vanished within days. She changed the interval to one reading every five minutes, sent a quick alert only when the level fell below 20 percent, and the same bundle lasted a month. The lesson: calculate data use before deployment, because a working project that drains your bundle will not stay on.</p>

<blockquote><p><strong>Quick check:</strong> Is a device that only reports the water level of a tank to a phone an IoT device or a robot?</p><p>Answer: An IoT device, because it senses and reports but does not decide and move in the physical world.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A reading is 20 bytes and is sent every 60 seconds. How many bytes per day?</p><p>Answer: 28,800 bytes, because 1,440 readings per day multiplied by 20 bytes is 28,800.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose one idea: soil moisture, shed temperature or tank level.</li>
<li>Write what the sensor measures, how often it needs to report and how large one reading is.</li>
<li>Calculate readings per day and data per day for your chosen interval, then for an interval ten times longer.</li>
<li>In a free simulator that supports ESP32 (for example Wokwi), follow its current guide to read a sensor value and print it.</li>
<li>Write a sentence deciding Wi-Fi or Bluetooth for your project and why.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>IoT devices mostly sense and report; robots sense, decide and move.</li>
<li>ESP32 boards have Wi-Fi and Bluetooth built in and run on low voltage.</li>
<li>Count readings and bytes before choosing how often to send.</li>
<li>Remote control needs limits, confirmation and a safe state if the link fails.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'MQTT, dashboards, security and offline-first design',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to explain how MQTT moves messages, design a clear topic naming scheme, describe cloud dashboards and real-time charts, secure a connected device, and plan for low bandwidth and power cuts. This matters because many connected projects fail not from bad sensors but from careless security, tangled topic names or total dependence on a perfect internet connection.</p>

<h3>MQTT explained simply</h3>
<p><strong>MQTT</strong> is a light messaging method for small devices. Think of a community notice board with a clerk. A <strong>broker</strong> is the clerk: a program on a computer or cloud server that receives messages and passes them on. A device <strong>publishes</strong> a message to a named <strong>topic</strong>. Any other device or dashboard that <strong>subscribes</strong> to that topic receives it. The sender and receiver never talk directly, so you can add a new dashboard without changing the sensor.</p>
<p>An example topic naming scheme is organisation, site, device, then measurement:</p>
<pre><code>mbarara-farm/shed1/soil-probe2/moisture
mbarara-farm/shed1/pump1/command
mbarara-farm/shed1/pump1/status</code></pre>
<p>Use lowercase, hyphens, no spaces, and the same order everywhere. Subscribers can also use wildcards: <code>mbarara-farm/shed1/+/moisture</code> matches the moisture of every device in shed1, and <code>mbarara-farm/#</code> matches everything below the farm. A short ESP32 Arduino sketch using the common PubSubClient library might publish like this:</p>
<pre><code>#include &lt;WiFi.h&gt;
#include &lt;PubSubClient.h&gt;
WiFiClient net;
PubSubClient mqtt(net);

void setup() {
  WiFi.begin("YOUR_WIFI_NAME", "YOUR_WIFI_PASSWORD");
  while (WiFi.status() != WL_CONNECTED) delay(500);
  mqtt.setServer("YOUR_BROKER_ADDRESS", 1883);
}

void loop() {
  if (!mqtt.connected()) mqtt.connect("soil-probe2");
  mqtt.publish("mbarara-farm/shed1/soil-probe2/moisture", "41");
  mqtt.loop();
  delay(600000);
}</code></pre>
<p>Here 600000 ms is ten minutes. Never send private data through public test brokers.</p>

<h3>Cloud dashboards and real-time visualisation</h3>
<p>A <strong>cloud dashboard</strong> is a web page hosted on someone else's server that shows your data from anywhere. Many services offer free tiers with limits. Choose the chart to match the question: a gauge for the current value, a line chart for change over time, and an alert for a danger level. For soil moisture, a point every ten minutes is real-time enough.</p>

<h3>Security in simple steps</h3>
<ul>
<li><strong>Change default passwords</strong> on routers, brokers and dashboards at once.</li>
<li><strong>Keep Wi-Fi credentials out of shared code:</strong> put them in a separate settings file and never upload it.</li>
<li><strong>Update firmware</strong> when makers release fixes.</li>
<li><strong>Least privilege:</strong> give each device or person only the access they need; a sensor should publish but not control the pump.</li>
<li><strong>Do not expose devices to the internet carelessly:</strong> avoid opening router ports to a device, and use login-protected, encrypted connections.</li>
</ul>

<h3>Offline-first and bundle-friendly thinking</h3>
<p>In Uganda, internet can be slow and electricity can fail. Design so the device still does its main job alone: an irrigation controller should decide locally from its own sensor and report to the cloud only as a bonus. Store readings, then send them in batches when the link returns. Worked example: 60 readings of 10 bytes sent one by one cost 60 x (10 + 300 bytes of overhead) = 18,600 bytes. One batch costs 600 + 300 = 900 bytes, about 20 times less. After a power cut the device clock resets, so get the time again when it reconnects and reconnect automatically when Wi-Fi returns.</p>

<h3>Case scenario</h3>
<p>Daniel, a technician in Kampala, controlled a pump with an ESP32 and opened a port on the router so he could reach it from his phone. He left the default password. During the night someone found the open port and switched the pump on, and the tank overflowed. Daniel closed the port, changed every password, moved control to a broker that needs a login, and gave the dashboard read-only access. The lesson: an exposed device with a default password invites misuse.</p>

<blockquote><p><strong>Quick check:</strong> In MQTT, which part receives messages and passes them to subscribers?</p><p>Answer: The broker, because publishers and subscribers do not connect to each other directly.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why should Wi-Fi details not appear in code you share?</p><p>Answer: Anyone who sees the code could join your network, so keep them in a private settings file.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Use this topic sheet and complete it for your project: Organisation / Site / Device / Measurement / Publisher / Subscriber.</li>
<li>Write three topics using the naming scheme above and one wildcard subscription.</li>
<li>List which device publishes, which subscribes and what it may control (least privilege).</li>
<li>Follow a free simulator's current guide to publish a reading from a virtual ESP32 to a test broker, with no private data.</li>
<li>Write a short offline plan: what the device does without internet and when it sends.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>MQTT uses a broker, publishers, subscribers and topics.</li>
<li>Consistent topic names keep a system understandable as it grows.</li>
<li>Secure devices with strong passwords, private credentials, updates and least privilege.</li>
<li>Offline-first designs batch data, keep local control and survive power cuts.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A simulated robot moves at 40 cm per second. Its program checks the distance sensor every 500 ms and stops when an obstacle is 15 cm away. In the real test it hits the wall. What is the best explanation and fix?
- The wall is too hard; use a softer wall
* It travels 20 cm between readings, more than the 15 cm alert distance; read the sensor more often or alert earlier
- The battery is too small; add a bigger battery
- Simulators cannot test sensors, so the test proves nothing
= At 40 cm per second and 0.5 seconds per check, the robot moves 20 cm between readings, so it can pass the 15 cm limit unnoticed.

Q: Which tool is described as browser-based mechanical and 3D design (CAD)?
- Wokwi
- Tinkercad Circuits
- Webots
* Onshape
= Onshape is used for mechanical design. Wokwi and Tinkercad Circuits simulate electronics, and Webots simulates robots in a 3D world.

Q: Your circuit worked in the simulator, but the real robot's sensor reads zero. What should you check first?
* Connections, power and a common ground, using a multimeter on a safe low-voltage setting
- Whether the simulator account has expired
- Whether the code uses too many comments
- Whether the room is too bright
= Simulators assume perfect wiring, so the most likely differences are loose wires, missing power or a missing common ground.

Q: A sensor sends 100 bytes every 10 seconds. About how much data is that per day, ignoring overhead?
- About 8.6 KB
- About 86 KB
* About 864 KB
- About 8.6 MB
= There are 8,640 readings per day, and 8,640 x 100 bytes = 864,000 bytes, which is about 864 KB.

Q: A device reads the water level in a tank and reports it to a phone, but it never moves anything. How is it best classified?
- A mobile robot
* An IoT device
- An industrial robot arm
- A humanoid robot
= It only senses and reports. A robot senses, decides and acts physically in its environment.

Q: A pump controller subscribes to the topic farm/shed1/pump1/command. A dashboard publishes "ON" to that topic. What does the broker do?
- Sends the message only to the dashboard that published it
- Deletes the message because the dashboard is not the pump
- Switches the pump on itself
* Receives the message and passes it to every subscriber of that topic, including the pump controller
= The broker is the go-between: it forwards messages from publishers to subscribers of the same topic.

Q: A learner uploads ESP32 code to a public site with the real Wi-Fi name and password inside. What is the best response?
- Leave it, because Wi-Fi passwords are not important
- Delete only the file name but keep the password
* Remove the code, change the Wi-Fi password, and keep credentials in a private settings file in future
- Make the repository larger so it is harder to find
= Anything published may already be copied, so change the password and keep credentials out of shared code.

Q: A solar-powered ESP32 in a village has unreliable internet and frequent power cuts. Which design is best?
- Send every reading instantly and stop working if the link drops
- Require the cloud to decide whether to water the crops
* Decide locally, store readings, send them in batches when connected, and reconnect automatically after a cut
- Open the router port so the device is always reachable
= Offline-first designs keep the main job working locally and save bandwidth by batching, while automatic reconnection handles power cuts.
QUIZ,
        ],
        // ---------------------------------------------------------------- STAGE 6
        [
            'title'   => 'Stage 6: Community Impact, Safety and Ethics',
            'summary' => 'Apply robotics to real community problems such as irrigation, waste, health and education, then make every build safe, private, inclusive and responsible.',
            'lessons' => [
                [
                    'title'   => 'Smart farming and an automated irrigation design',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to describe how sensors and robots help farms, explain a moisture threshold with hysteresis, and simulate a low-voltage automated irrigation controller. This matters because many smallholders water by hand or by guesswork, and too much or too little water costs both money and harvest.</p>

<h3>Agricultural robotics and smart farming</h3>
<p><strong>Smart farming</strong> means using sensor data instead of guesses. Common examples:</p>
<ul>
<li><strong>Automated irrigation:</strong> a soil moisture sensor decides when a pump runs.</li>
<li><strong>Crop monitoring and data collection:</strong> weekly photos, plant height and leaf colour logged in a spreadsheet.</li>
<li><strong>Environmental monitoring:</strong> temperature, humidity and rain sensors that warn of heat or damp.</li>
<li><strong>Planting and harvesting robots:</strong> machines that space seeds or pick ripe fruit using cameras; costly today, so learn the concept first.</li>
</ul>

<h3>Soil moisture and hysteresis</h3>
<p>Use a <strong>capacitive</strong> soil moisture sensor, because resistive ones corrode. It gives an analogue reading from 0 to 1023. Calibrate it: read it in dry air (example: 620) and in a cup of water (example: 300), then convert to a percentage.</p>
<p><strong>Hysteresis</strong> means using two thresholds instead of one. If the pump starts and stops at 40 percent, tiny changes around 40 make the relay click on and off many times a minute and wear out. Instead: start watering below 35 percent, stop above 45 percent. Between 35 and 45 the system keeps doing whatever it was already doing.</p>

<h3>Worked design: one vegetable bed near Gulu</h3>
<p>A pilot tomato bed of 5 m by 1 m (5 square metres), a 5 V mini pump in a 20-litre bucket and a drip pipe, all at 5 V USB level. Mains pumps must only be installed by a qualified electrician. Under-18s need adult supervision. Water and electronics do not mix: keep the board, relay and joints in a closed plastic box, and keep the sensor's electronics above the soil line.</p>
<table>
<thead><tr><th>Part</th><th>Pin or wire</th><th>Connects to</th></tr></thead>
<tbody>
<tr><td>Soil sensor</td><td>VCC, GND, AOUT</td><td>Arduino 5V, GND, A0</td></tr>
<tr><td>Relay module</td><td>VCC, GND, IN</td><td>Arduino 5V, GND, D7</td></tr>
<tr><td>Relay</td><td>COM</td><td>Plus of the separate 5 V pump supply</td></tr>
<tr><td>Relay</td><td>NO (normally open)</td><td>Pump red wire</td></tr>
<tr><td>Pump</td><td>Black wire</td><td>Minus of the pump supply</td></tr>
<tr><td>Diode 1N4007</td><td>Across the pump</td><td>Stripe end to the red wire</td></tr>
</tbody>
</table>
<p>The relay uses its NO contact, so a lost signal or dead Arduino leaves the pump off. Most relay modules switch on when the pin is LOW.</p>
<pre><code>const int SENSOR_PIN = A0;
const int RELAY_PIN = 7;
const int RELAY_ON = LOW;
const int RELAY_OFF = HIGH;
const int DRY_RAW = 620, WET_RAW = 300;
const int START_BELOW = 35, STOP_ABOVE = 45;
const unsigned long MAX_RUN_MS = 720000UL;  // 12 minutes
const unsigned long REST_MS = 1800000UL;    // 30 minutes
bool pumpOn = false;
unsigned long pumpStart = 0, restUntil = 0;

int moisturePercent() {
  long total = 0;
  for (int i = 0; i &lt; 5; i++) { total += analogRead(SENSOR_PIN); delay(10); }
  int pct = map(total / 5, DRY_RAW, WET_RAW, 0, 100);
  return constrain(pct, 0, 100);
}

void setup() {
  digitalWrite(RELAY_PIN, RELAY_OFF);
  pinMode(RELAY_PIN, OUTPUT);
  Serial.begin(9600);
}

void loop() {
  int pct = moisturePercent();
  unsigned long now = millis();
  if (!pumpOn &amp;&amp; now &gt;= restUntil &amp;&amp; pct &lt; START_BELOW) {
    pumpOn = true; pumpStart = now;
    digitalWrite(RELAY_PIN, RELAY_ON);
  } else if (pumpOn &amp;&amp; (pct &gt; STOP_ABOVE || now - pumpStart &gt; MAX_RUN_MS)) {
    pumpOn = false; restUntil = now + REST_MS;
    digitalWrite(RELAY_PIN, RELAY_OFF);
  }
  Serial.println(pct);
  delay(2000);
}</code></pre>

<h3>Practical demonstration (free simulators)</h3>
<ol>
<li>Open Tinkercad Circuits or Wokwi (free; Tinkercad needs an account).</li>
<li>Place an Arduino Uno, a potentiometer on A0 as the sensor stand-in, and an LED with a 220 ohm resistor on D7 as the pump.</li>
<li>Paste the code, set RELAY_ON to HIGH and RELAY_OFF to LOW, and change REST_MS to 10000UL for testing.</li>
<li>Run it and turn the potentiometer slowly. The LED should switch on below 35 percent and stay on until above 45 percent.</li>
</ol>

<h3>Case scenario</h3>
<p>Okello, who farms near Gulu, built a controller but never calibrated it and left the sensor in the air. It read "dry" all day, so the pump ran non-stop and the tomato roots rotted. He then calibrated it, buried the probe at root depth and added hysteresis and a 12-minute limit. Lesson: calibration and limits matter.</p>

<blockquote><p><strong>Quick check:</strong> Why start watering at 35 percent but stop at 45 percent?</p><p>Answer: The gap (hysteresis) stops the pump switching on and off rapidly near one threshold, which protects the relay and the crop.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why keep a maximum pump run time in the code?</p><p>Answer: If the sensor or its wire fails and reads dry, the limit stops the pump from flooding the bed.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Run the simulation and note where the LED switches on and off.</li>
<li>Change the thresholds to 30 and 50 and compare.</li>
<li>Explain why a mains pump needs a qualified electrician.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Calibrate the sensor, then use two thresholds (hysteresis).</li>
<li>Switch only low-voltage pumps, and keep all electronics dry and sealed.</li>
<li>Add run-time limits so one failure cannot flood a crop.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Waste, health, education and measuring community impact',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to apply robotics to waste, health, assistive and education problems, find real local problems by listening, cost an affordable design, and measure its social and economic impact. This matters because the best technology is the one a community actually needs, can afford and can repair.</p>

<h3>Waste, health, assistive devices and education</h3>
<ul>
<li><strong>Waste sorting:</strong> a recycling group in Kampala could use a colour sensor and a servo flap to separate clear from coloured bottles. Test 50 bottles; if 46 are sorted correctly, accuracy is 46 divided by 50 = 92 percent. Dirty labels cause most errors.</li>
<li><strong>Healthcare:</strong> a touchless hand-wash or sanitiser dispenser (distance sensor plus small pump) helps a clinic. Anything that touches patients is medical equipment: it needs qualified approval, so never claim a student prototype is medical-grade.</li>
<li><strong>Assistive robotics:</strong> a cane attachment that vibrates when an ultrasonic sensor sees an obstacle under 1 m, or a switch that turns pages. Design with the people who will use it.</li>
<li><strong>Education:</strong> school clubs can use simulators and recycled parts, so skills grow before money is spent.</li>
</ul>

<h3>Finding local problems</h3>
<p>Interview, observe, measure. Interview at least five farmers, watch a full working day, and measure time, litres and cost. Record findings like this:</p>
<pre><code>PROBLEM DISCOVERY SHEET
Who I asked: ______  Date: ______
Problem in their words: ______
What I saw (observation): ______
What I measured (time, litres, UGX): ______
Who is affected and how often: ______
What they already do about it: ______
Can sensors or a small robot help? Yes / No / Maybe
Cost they could afford: UGX ______</code></pre>

<h3>Affordable design for resource-constrained communities</h3>
<p>Use common parts, solar or USB power, sealed boxes against dust and rain, and a design a local technician can repair. Use the cheapest approach that works: sometimes a bucket with a float valve beats a robot.</p>

<h3>Worked example: budget, water saving and payback</h3>
<p>These are planning figures; replace them with local quotes.</p>
<table>
<thead><tr><th>Item</th><th>UGX</th></tr></thead>
<tbody>
<tr><td>Arduino-compatible board</td><td>45,000</td></tr>
<tr><td>Capacitive soil sensor</td><td>12,000</td></tr>
<tr><td>Relay module</td><td>8,000</td></tr>
<tr><td>5 V mini pump</td><td>15,000</td></tr>
<tr><td>Tubing, diode, wires, breadboard</td><td>16,500</td></tr>
<tr><td>Plastic box and sealant</td><td>15,000</td></tr>
<tr><td>USB supply</td><td>20,000</td></tr>
<tr><td>Subtotal</td><td>131,500</td></tr>
<tr><td>10 percent spare (13,150)</td><td>144,650 total</td></tr>
</tbody>
</table>
<p><strong>Water:</strong> by hand the farmer gives the 5 square metre bed 4 L per square metre daily: 20 L a day, 140 L a week. The sensor system gives 3 L per square metre (15 L) on about 4 days: 60 L a week. Saving: 140 minus 60 = 80 L, which is 80 / 140 = 57 percent.</p>
<p><strong>Money:</strong> watering by hand takes about 2 hours a week, valued at UGX 2,000 an hour, giving 4,000. Extra harvest of 6 kg over a 12-week cycle at UGX 2,000 a kg is 12,000, or 1,000 a week. Total benefit 5,000 a week, so payback is 144,650 / 5,000 = about 29 weeks. For one bed that is slow, so sharing a controller across several beds is better.</p>
<p>Measure social impact too: hours freed, who gains (women and youth farmers?), skills learned, and any harm.</p>

<h3>Case scenario</h3>
<p>Three students designed an automatic bottle sorter for a Kampala recycling group without visiting them. It cost UGX 900,000, jammed with wet bottles, and the workers said their real problem was transport to the buyer. The machine sat unused. The students returned, followed the group for two days, and built a simple sorting table with clearly labelled bins and a weighing scale. Lesson: listen first, build second.</p>

<blockquote><p><strong>Quick check:</strong> A sorter correctly sorts 46 of 50 test bottles. What is its accuracy?</p><p>Answer: 92 percent, because 46 divided by 50 is 0.92.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What are the three steps for finding a local problem?</p><p>Answer: Interview, observe and measure, because stories, behaviour and numbers together show the real problem.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Interview two farmers or small business owners using the discovery sheet and choose one problem.</li>
<li>Design and simulate an irrigation system (Wokwi or Tinkercad) that starts below a threshold you choose and stops above a higher one.</li>
<li>Test it at three potentiometer settings and record the results.</li>
<li>Write a budget in UGX with 10 percent spare, and check the total twice.</li>
<li>Estimate water and time saved, then write four sentences on social and economic impact.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Robotics helps in farming, waste, health, assistive devices and education.</li>
<li>Interview, observe and measure before building.</li>
<li>Affordable means cheap parts, local repair and simple power.</li>
<li>Impact is measured in water, time, money, people and skills.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Hardware safety: electrical, mechanical, battery and fail-safes',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to spot electrical, mechanical and battery hazards, follow a safe test routine, add fail-safes, and keep people safe around a moving robot. This matters because a robot stores energy and moves, and most student accidents come from skipped basics.</p>

<h3>Ground rules</h3>
<p>Work at low voltage only (battery or USB level, about 12 V or less). Under-18s need adult supervision. Never switch mains power: mains devices must be handled by a qualified electrician. Water and electricity do not mix, so keep electronics dry and sealed.</p>

<h3>Electrical safety</h3>
<p>Disconnect the battery before changing wires. Check polarity, cover joints with heat-shrink, and measure with a multimeter before connecting. <strong>Worked example:</strong> a 7.4 V battery shorted through wire of 0.5 ohm drives 7.4 / 0.5 = 14.8 A, which is 14.8 x 7.4 = about 110 W of heat in thin wire. That melts insulation. Add a fuse above the normal current but below the wire rating: with two motors stalling at 1.5 A each (3 A total), use a 4 A fuse and wire rated for at least 5 A.</p>

<h3>Mechanical hazards</h3>
<p>Wheels, gears, arms and propellers pinch and cut. Keep fingers, hair and loose sleeves clear, add guards, and limit speed and force in code. Test with the wheels off the ground first.</p>

<h3>Battery handling</h3>
<p>Li-ion and LiPo packs can burn if shorted, punctured, overcharged or over-discharged. Use the right charger and a protection circuit, charge on a non-flammable surface, never unattended or overnight, and stop using any pack that is swollen, hot or dented. Beginners are safer with AA cells or a USB power bank. <strong>Runtime example:</strong> a 2,200 mAh pack and a robot drawing 1.1 A average gives 2.2 / 1.1 = 2 hours in theory; planning for 80 percent usable gives 1.6 hours, which is 96 minutes.</p>

<h3>Safe testing procedure</h3>
<ol>
<li>Simulate first.</li>
<li>Check wiring against the diagram.</li>
<li>Power the board alone from USB, with no motors.</li>
<li>Test sensors, then test motors with wheels lifted.</li>
<li>Test on the floor in a cleared area with a hand on the e-stop.</li>
<li>Raise speed in small steps.</li>
</ol>

<h3>Fail-safes</h3>
<ul>
<li><strong>Emergency stop:</strong> a physical button wired in series with motor power, so it works even if the software hangs.</li>
<li><strong>Watchdog:</strong> if the program stops reporting that it is healthy, outputs go off or the board resets.</li>
<li><strong>Default-off on power loss:</strong> use NO relay contacts and pull motor-driver enable pins low with a resistor.</li>
</ul>
<pre><code>if (millis() - lastGoodReading &gt; 10000UL) {
  digitalWrite(RELAY_PIN, RELAY_OFF);  // no valid sensor data: stay safe
}</code></pre>

<h3>Human safety around autonomous systems</h3>
<p>Use lights or sound to show the robot is active, mark a keep-out zone, and slow down near people. <strong>Stopping distance:</strong> at 0.5 m/s with a 0.5 s delay, the robot travels 0.25 m before braking begins; add 0.10 m braking for 0.35 m. Set the stop distance to at least 0.5 m to leave a margin.</p>

<h3>Practical demonstration (free simulators)</h3>
<ol>
<li>In Tinkercad Circuits or Wokwi, place an Arduino, a push button and an LED.</li>
<li>Write code so the LED (the motor) lights only while a start button is held.</li>
<li>Add a second button as the e-stop that turns the LED off at once.</li>
<li>Test by releasing the start button: the LED must go off.</li>
</ol>

<h3>Case scenario</h3>
<p>A school robotics club in Kampala powered a robot with a laptop battery pack and thin jumper wires and no fuse. On the first test a loose wire shorted, the wires smoked and the box melted. Nobody was hurt because the teacher was beside the robot with the battery unplugged within seconds. The club added a 4 A fuse, thicker wires and an e-stop. Lesson: assume a fault will happen and design for it.</p>

<blockquote><p><strong>Quick check:</strong> A robot needs 0.35 m to stop. Is a stop distance of 0.3 m safe?</p><p>Answer: No, because it would still hit the obstacle; use at least 0.5 m for a margin.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why should an e-stop cut power in hardware, not only in code?</p><p>Answer: Because if the software freezes, a code-only stop will not work.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Complete the simulation with a start button and an e-stop.</li>
<li>Calculate the runtime for a 2,000 mAh pack at 0.8 A with 80 percent usable.</li>
<li>List three hazards on a mobile robot and one control for each.</li>
<li>Write the test steps you would follow before powering a new robot.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Low voltage, supervision, dry electronics and a fuse come first.</li>
<li>Handle Li-ion and LiPo batteries with care; retire damaged packs.</li>
<li>Test in stages: simulate, no motors, wheels lifted, then floor.</li>
<li>Fail-safes make a fault end safely.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Privacy, ethics, inclusion, e-waste and the safety checklist',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to protect data in connected robots, judge ethical questions in AI and surveillance, design inclusively, handle e-waste, document your testing, and complete a safety checklist. This matters because a robot that is safe but invasive, biased or discarded carelessly still harms people.</p>

<h3>Data privacy in connected robots</h3>
<p>Collect only what you need, store it securely, change default passwords, and delete it when the purpose ends. <strong>Worked example:</strong> a camera that saves one image every 10 seconds makes 6 a minute, 360 an hour and 8,640 a day. Kept for 30 days that is 8,640 x 30 = 259,200 images; kept for 7 days it is 60,480. If a simple motion counter answers your question, store only the count.</p>

<h3>Surveillance, consent and the law</h3>
<p>Before any camera or microphone: state the purpose, tell people with clear signs, get consent where needed, limit who sees the data and for how long, and never hide devices. Uganda has a Data Protection and Privacy Act that, in general terms, expects lawful and fair collection, a stated purpose, limited collection, security and respect for people's rights. This is general education, not legal advice: check the current law and ask a qualified adviser for real projects.</p>

<h3>AI ethics</h3>
<ul>
<li><strong>Bias:</strong> recognition systems can work worse for groups missing from the training data, so test on local people.</li>
<li><strong>Accountability:</strong> a human must be able to review, override and take responsibility for decisions.</li>
<li><strong>Transparency:</strong> tell users when they are dealing with a machine and what it records.</li>
</ul>

<h3>Accessibility and inclusive design</h3>
<p>Involve people with disabilities from the start. Use large buttons, sound plus light signals, local languages and picture labels, and keep the price within reach.</p>

<h3>E-waste and battery recycling</h3>
<p>Choose repairable and reusable parts. Never burn or bin batteries or circuit boards: they hold toxic metals and can ignite. Collect old parts and take them to an authorised recycler or collection point, and plan a device's end of life before building it.</p>

<h3>Testing and documenting</h3>
<p>Keep a log: date, change, test, result and fix. Record every incident or near miss and what you changed. Documentation lets others repeat, inspect and improve your work.</p>

<h3>Safety checklist template</h3>
<pre><code>SAFETY CHECKLIST - Project: ______  Date: ______  Checked by: ______
ELECTRICAL
[ ] Low voltage only; no mains wiring (electrician for mains)
[ ] Polarity checked, joints insulated, fuse fitted
[ ] Electronics sealed and dry; no water near connections
MECHANICAL
[ ] Moving parts guarded; speed and force limited
[ ] Tested with wheels or arm lifted first
BATTERY
[ ] Correct charger and protection; no damaged pack
[ ] Charged supervised on a non-flammable surface
FAIL-SAFES
[ ] Hardware e-stop works  [ ] Watchdog or timeout works
[ ] Default-off on power loss tested
PEOPLE
[ ] Adult supervisor present if under 18
[ ] Keep-out zone and warning light or sound
DATA AND ETHICS
[ ] Purpose stated, consent obtained, signs displayed
[ ] Minimal data, retention limit set, passwords changed
ACCESS AND ENVIRONMENT
[ ] Tested with real users, including people with disabilities
[ ] E-waste and battery recycling plan written
RECORDS
[ ] Test log and incident log up to date
SIGNED OFF: ______</code></pre>

<h3>Case scenario</h3>
<p>A student group put a camera robot in a trading-centre school corridor and streamed the footage to a messaging group, without telling parents or pupils. Parents complained and the head teacher stopped the project. The group restarted with a notice, a clear purpose (counting queue length), no recording, and parent agreement. Lesson: consent and purpose limits come before the technology.</p>

<blockquote><p><strong>Quick check:</strong> A camera stores 6 images a minute. How many in one hour?</p><p>Answer: 360, because 6 times 60 is 360.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A used lithium battery is no longer needed. Where should it go?</p><p>Answer: To an authorised collection or recycling point, because batteries hold harmful materials and can ignite in rubbish.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Copy the checklist and fill it in for a simple mobile robot or your irrigation prototype.</li>
<li>Write one sentence stating the purpose of any camera or sensor you use.</li>
<li>List two ways to make your design more accessible.</li>
<li>Write an end-of-life plan for the battery and electronics.</li>
<li>Start a test log with three entries.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Collect little data, secure it and delete it on time.</li>
<li>Surveillance needs purpose, consent and checking current law.</li>
<li>Bias and accountability matter in AI robots.</li>
<li>Design with users, plan for e-waste, and document everything.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: An irrigation pump starts and stops at exactly 40 percent moisture and the relay clicks many times a minute. What is the best fix?
- Use a bigger pump
* Start watering below 35 percent and stop above 45 percent (hysteresis)
- Remove the sensor
- Read the sensor less accurately
= Two thresholds create a gap so small fluctuations do not toggle the pump.

Q: By hand a 5 square metre bed gets 140 L a week; the sensor system uses 60 L a week. What is the percentage saving, to the nearest whole number?
- 43 percent
- 80 percent
- 133 percent
* 57 percent
= The saving is 140 minus 60 = 80 L, and 80 divided by 140 is about 0.57.

Q: Parts cost UGX 80,000 and you add 10 percent spare. What is the total?
- UGX 81,000
- UGX 90,000
* UGX 88,000
- UGX 800,000
= 10 percent of 80,000 is 8,000, so the total is 88,000.

Q: A sensor cable breaks and always reads dry. Which feature stops the crop from flooding?
* A maximum pump run time with a rest period
- A brighter indicator LED
- A longer sensor cable
- A faster microcontroller
= The time limit ends each run even when the sensor reading never changes.

Q: Students want to build a robot for a village but have not spoken to anyone there. What should they do first?
- Order the parts
- Pick the most advanced design
* Interview, observe and measure with the people affected
- Copy a design from the internet
= Real needs, budget and local skills must come from the community before design starts.

Q: A LiPo battery in your robot has become swollen. What is the correct action?
- Charge it once more and watch it
- Squeeze it flat and keep using it
* Stop using it and take it to a proper battery recycling or collection point
- Throw it in a cooking fire
= A swollen pack can ignite; it must be retired and recycled safely.

Q: What does default-off on power loss mean for a pump circuit?
- The pump runs faster after an outage
* Normally-open relay contacts leave the pump off when control power is lost
- The pump needs no supply
- The program ignores the sensor
= A fail-safe leaves the system in a safe state when control is lost.

Q: A school wants a camera robot in its corridors. Which plan is most responsible?
- Install it secretly so behaviour is natural
- Record everything forever
- Share the video with a large group
* State a purpose, display signs, get consent, limit storage and check current data law
= Responsible surveillance needs purpose limits, consent, minimal data and legal checking.
QUIZ,
        ],
        // ---------------------------------------------------------------- STAGE 7
        [
            'title'   => 'Stage 7: Careers, Business and the Final Project',
            'summary' => 'Explore robotics careers and business ideas, then plan, build or simulate, document and present your own robotic solution as the course final project.',
            'lessons' => [
                [
                    'title'   => 'Careers in robotics and automation',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to describe the main career paths that robotics skills open, match them to your own interests, and sketch a realistic learning path from where you are today. This matters because robotics is a wide field, and learners who know which direction attracts them can choose projects and further study with purpose instead of drifting.</p>

<h3>One field, many doors</h3>
<p>Robotics is not one job. It is a meeting point of mechanics, electronics, software and data. Almost every robotics team needs people with different strengths, and the same course skills can lead to several roles. Below are the main families of work. Names of roles vary between organisations, so treat them as areas rather than fixed job titles.</p>
<table>
<thead><tr><th>Area</th><th>What the work looks like</th><th>Skills to grow</th></tr></thead>
<tbody>
<tr><td>Robotics engineering</td><td>Designing the whole machine: frame, motors, sensors, power and how they fit together</td><td>Mechanics, electronics, CAD, testing</td></tr>
<tr><td>Robotics software</td><td>Writing the code that reads sensors, makes decisions and drives motors</td><td>Python, C/C++, algorithms, debugging</td></tr>
<tr><td>Electronics and embedded systems</td><td>Designing circuits and programming microcontrollers such as Arduino and ESP32 boards</td><td>Circuits, soldering, C/C++, power design</td></tr>
<tr><td>Industrial automation</td><td>Installing and maintaining automated lines, robotic arms, conveyors and PLC controls</td><td>PLCs, sensors, safety, maintenance</td></tr>
<tr><td>AI and machine learning</td><td>Teaching systems to recognise images, sounds or patterns that robots can act on</td><td>Python, data, statistics, ethics</td></tr>
<tr><td>Research and innovation</td><td>Testing new methods, improving designs and publishing or demonstrating results</td><td>Reading papers, experiments, writing</td></tr>
</tbody>
</table>

<h3>Typical learning paths for Ugandan students</h3>
<p>There is no single route. Common paths include a certificate or diploma in electrical, electronics or mechatronics-related studies, a university degree in engineering or computer science, and self-directed learning with free simulators and online courses. Many people combine them: study formally, then build projects in their free time. Employers and clients in all fields care about proof, so a portfolio of working projects counts alongside certificates. Check current entry requirements directly with any institution before planning, because programmes change.</p>

<h3>Worked example: choosing a direction</h3>
<p>Brenda enjoyed this course most when she programmed the line-follower and least when she soldered. She writes down her scores out of 5: programming 5, electronics 3, mechanics 2, data and AI 4. Her top two are programming and data, so she picks robotics software with an AI interest as her direction. Her next 12 months: finish Python practice, build two simulated projects, learn basic image recognition, and join a local tech or maker community meeting.</p>

<h3>Practical demonstration: build your career map</h3>
<ol>
<li>Rate each of the six areas above from 1 to 5 for how much you enjoyed the related lessons.</li>
<li>Circle your top two areas.</li>
<li>For each, write three skills you already have and three you must still learn.</li>
<li>Choose one project from this course that proves each skill.</li>
<li>Set a date within 30 days for the next learning step.</li>
</ol>

<h3>Template: personal learning path</h3>
<pre><code>Direction: ______________________
Skills I have: 1) ____ 2) ____ 3) ____
Skills to learn: 1) ____ 2) ____ 3) ____
Next 3 months: ______________________
Next 12 months: ______________________
Proof I will build: ______________________</code></pre>

<h3>Case scenario</h3>
<p>Isaac in Mbarara decided he wanted "to be a robotics engineer" and bought a large set of electronic parts on credit, planning to learn everything at once. He had no plan, burned out two motor drivers by wiring the battery the wrong way, and gave up in two months with a debt. A friend suggested he restart in a simulator, learn one skill at a time and buy parts only for a defined project. The lesson: a clear path and small steps protect both your money and your motivation.</p>

<blockquote><p><strong>Quick check:</strong> Which career area focuses mainly on writing the code that reads sensors and drives motors?</p><p>Answer: Robotics software, because it turns sensor inputs into decisions and motor commands.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> Why is a portfolio useful even for someone with a certificate?</p><p>Answer: It shows working proof of your skills, which employers and clients can check.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Complete the rating exercise and name your top two areas.</li>
<li>Fill in the personal learning path template.</li>
<li>List two people or groups near you (a lecturer, a technician, a school club) who could advise you.</li>
<li>Write one sentence: "In 12 months I want to be able to ..."</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Robotics offers engineering, software, embedded, automation, AI and research paths.</li>
<li>Choose a direction by what you enjoy and can prove, not by titles alone.</li>
<li>Formal study and self-directed projects work best together.</li>
<li>Buy parts only for a defined project; simulate first.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Robotics business, budgets and your concept note',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to spot a robotics business opportunity, estimate a simple budget with correct arithmetic, plan a portfolio, and write a one-page concept note. This matters because good ideas only reach people when they are costed, explained clearly and presented honestly to partners and funders.</p>

<h3>Finding an opportunity</h3>
<p>Start with a problem people already pay time or money to solve. Examples near us: farmers walking to check soil moisture, shops with power cuts that spoil stock, schools with no hands-on electronics equipment, small workshops that sort or count items by hand. Ask: who has this problem, how often, what do they do now, and what would they pay in time or money to fix it? Affordable products often use few sensors, simple controllers and local repair. Services can be installation, maintenance, training or building custom prototypes. No venture is guaranteed to earn money, so test with one real customer first.</p>

<h3>Estimating costs and budgets</h3>
<p>List every item, multiply by quantity, add them up, then add a contingency (a safety margin) of about 10% for mistakes and delivery. The prices below are made-up practice figures, not market prices; check real local prices before you plan.</p>
<table>
<thead><tr><th>Item</th><th>Qty</th><th>Unit (UGX)</th><th>Total (UGX)</th></tr></thead>
<tbody>
<tr><td>Microcontroller board</td><td>1</td><td>45,000</td><td>45,000</td></tr>
<tr><td>Ultrasonic sensor</td><td>1</td><td>8,000</td><td>8,000</td></tr>
<tr><td>Motor driver</td><td>1</td><td>15,000</td><td>15,000</td></tr>
<tr><td>Motor with wheel</td><td>2</td><td>12,000</td><td>24,000</td></tr>
<tr><td>Chassis</td><td>1</td><td>20,000</td><td>20,000</td></tr>
<tr><td>Batteries and holder</td><td>1</td><td>18,000</td><td>18,000</td></tr>
<tr><td>Wires and fittings</td><td>1</td><td>7,000</td><td>7,000</td></tr>
</tbody>
</table>
<p>Subtotal: 45,000 + 8,000 + 15,000 + 24,000 + 20,000 + 18,000 + 7,000 = UGX 137,000. Contingency 10%: 137,000 x 0.10 = 13,700. Budget: 137,000 + 13,700 = UGX 150,700.</p>

<h3>Portfolio, competitions and presenting</h3>
<p>A <strong>portfolio</strong> is a small collection of your best projects, each with a photo or screenshot, a short problem statement, what you built, test results and what you learned. Two or three complete projects beat ten unfinished ones. Schools, universities, tech hubs and organisations sometimes run innovation challenges; look for them through your institution or community, read the rules carefully and never trust an entry that asks you for money to win. When presenting to partners or funders, state the problem, your solution, the evidence it works, the cost and what help you need. Be honest about limits.</p>

<h3>Business model and the ecosystem</h3>
<p>A <strong>business model</strong> explains who your customers are, what value you give, how you earn money, and what it costs you. The robotics ecosystem in Uganda and across Africa is made of universities and technical institutions, innovation hubs and maker spaces, student clubs, small workshops, farmers, schools and companies that use automation. Join, ask questions and share work; ecosystems grow through people helping each other.</p>

<h3>Practical demonstration: one-page concept note</h3>
<ol>
<li>Choose a local challenge and one affected group.</li>
<li>Describe your idea in two sentences.</li>
<li>List parts and costs; add 10% contingency.</li>
<li>Say how you will test it and who will use it first.</li>
<li>Write the note on one page using the template below.</li>
</ol>

<h3>Template: concept note</h3>
<pre><code>Title:
Problem and who is affected:
Proposed solution (2-3 sentences):
How it works (inputs, logic, outputs):
Estimated budget (UGX) and contingency:
Testing plan:
Expected benefit and how we will measure it:
Risks and safety (low voltage only):
Support needed and next steps:</code></pre>

<h3>Case scenario</h3>
<p>Peter planned a solar-powered irrigation prototype for a school garden and told the head teacher it would cost UGX 100,000. He had forgotten the pump, the relay and the wires, and his real total came to nearly double. The school stopped funding, and the demo never happened. After he redid a line-by-line budget with a 10% contingency and asked for a small first stage, the school approved it. The lesson: an unrealistic budget damages trust faster than a high but honest one.</p>

<blockquote><p><strong>Quick check:</strong> A subtotal is UGX 80,000. What is the budget with 10% contingency?</p><p>Answer: UGX 88,000, because 10% of 80,000 is 8,000, and 80,000 + 8,000 = 88,000.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What should you do with a competition that asks entrants to pay a fee to be guaranteed a prize?</p><p>Answer: Avoid it, because genuine opportunities do not guarantee wins for a fee.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write your concept note using the template.</li>
<li>Build a budget table for it with real local prices you have checked.</li>
<li>Add 10% contingency and show your arithmetic.</li>
<li>Ask one person to read the note and say what is unclear.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Start from a real problem and test with one real user.</li>
<li>Budget line by line and add a contingency.</li>
<li>A portfolio of complete, documented projects is your proof.</li>
<li>Present honestly: problem, solution, evidence, cost and need.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Final project part 1: choose, plan and schedule',
                    'minutes' => 24,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to choose one of the seven final project options that suits your skills and equipment, write requirements and a plan, and set milestones over about two weeks. This matters because the final project and presentation carry 40% of your course mark, and most failures come from poor planning, not poor ability.</p>

<p><strong>Safety first:</strong> use low-voltage power only (batteries or a USB supply, never mains electricity). Learners under 18 should have an adult supervise any build. Never connect a battery until a teacher, technician or adult has checked your wiring.</p>

<h3>The seven options</h3>
<table>
<thead><tr><th>Project</th><th>Description</th><th>Difficulty</th></tr></thead>
<tbody>
<tr><td>Obstacle-Avoiding Robot</td><td>Detects objects and changes direction to avoid collisions</td><td>Beginner</td></tr>
<tr><td>Line-Following Robot</td><td>Follows a marked path using sensors</td><td>Beginner to intermediate</td></tr>
<tr><td>Smart Irrigation System</td><td>Sensor-based system that automatically controls water supply</td><td>Beginner</td></tr>
<tr><td>Robotic Arm Simulation</td><td>Simulated arm that moves objects between locations</td><td>Intermediate</td></tr>
<tr><td>Smart Waste-Sorting System</td><td>Prototype that uses sensors to identify and sort objects</td><td>Intermediate</td></tr>
<tr><td>Voice-Controlled Robot</td><td>Responds to commands through a suitable voice-control system</td><td>Intermediate</td></tr>
<tr><td>IoT Monitoring System</td><td>Connected system sending sensor data to a dashboard</td><td>Intermediate</td></tr>
</tbody>
</table>

<h3>How to choose</h3>
<p>Ask three questions. First, <strong>skills</strong>: which stage of this course did you complete with confidence? Second, <strong>tools</strong>: do you have a laptop or phone, internet and a simulator such as Tinkercad Circuits or Wokwi (some need a free account)? Third, <strong>parts</strong>: can you borrow or afford a board, sensors and a motor driver? If you have parts, a physical build is possible. If not, simulate; learners without physical components complete projects with simulators and receive the same recognition. Beginners should pick a beginner option and do it well rather than an ambitious one half-finished.</p>

<h3>Worked example: choosing</h3>
<p>Aisha has a phone and a library computer, no parts, and enjoyed the irrigation module. She chooses the Smart Irrigation System as a simulation: a soil-moisture sensor reading, a threshold and a pump shown as a virtual relay. Her friend Moses owns an Arduino-compatible board and picks the Obstacle-Avoiding Robot as a physical build, keeping a simulation as backup.</p>

<h3>Requirements and plan templates</h3>
<pre><code>REQUIREMENTS
Problem to solve:
Inputs (sensors or commands):
Outputs (motors, lights, alerts, dashboard):
Must do (3 items):
Success test (a measurable result):
Limits (budget UGX, time, tools):

PLAN
Build or simulation: ______
Parts or tools list with cost:
Steps in order:
Biggest risk and backup plan:
Safety checks:</code></pre>

<h3>Milestone schedule (about two weeks)</h3>
<table>
<thead><tr><th>Days</th><th>Milestone</th></tr></thead>
<tbody>
<tr><td>1-2</td><td>Choose project; write requirements and plan</td></tr>
<tr><td>3-4</td><td>Gather parts or set up simulator; draw the circuit or diagram</td></tr>
<tr><td>5-7</td><td>Build or assemble; write the first working program</td></tr>
<tr><td>8-10</td><td>Test with your test cases; fix faults; record results</td></tr>
<tr><td>11-12</td><td>Finish documentation; take photos or screenshots</td></tr>
<tr><td>13-14</td><td>Prepare and rehearse the 5-minute demo, with a backup</td></tr>
</tbody>
</table>

<h3>Documentation expectations</h3>
<p>Your documentation is worth 20% of the mark. Include the problem, requirements, parts or tools, circuit or flow diagram, program code with comments, test table (expected result and actual result), problems found and how you fixed them, and a short reflection on what you would improve.</p>

<h3>Case scenario</h3>
<p>Davis chose a voice-controlled robot because it looked impressive, but he had never tested a microphone module. In the final week he discovered it would not recognise commands in a noisy classroom, and he had no simulation or fallback. His demo failed in front of the class. A teacher advised him to restart with a smaller scope: typed commands first, voice later. His second attempt worked. The lesson: choose within your skills and always keep a backup plan.</p>

<blockquote><p><strong>Quick check:</strong> You have no physical parts. Can you still complete the final project?</p><p>Answer: Yes, because learners without components complete projects using suitable simulation tools.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> What two things should every test row in your documentation show?</p><p>Answer: The expected result and the actual result, so you can see whether the system works.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Answer the three choosing questions and pick one option.</li>
<li>Fill in the requirements template.</li>
<li>Fill in the plan template, including a backup plan.</li>
<li>Copy the milestone table and write real dates beside each stage.</li>
<li>Create a folder for notes, photos and code.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Choose by skills, tools and parts; simulation is fully acceptable.</li>
<li>Write measurable requirements before building.</li>
<li>Plan about two weeks with milestones and a backup.</li>
<li>Low voltage only, with adult supervision for under-18s.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Final project part 2: outlines, presentation and assessment',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>By the end of this lesson you will be able to follow a build or simulation outline for your chosen project, deliver a 5-minute demonstration, understand how your final mark is calculated, and plan what to do after the course. This matters because a clear outline saves days of confusion and a rehearsed demo shows your work at its best.</p>

<p><strong>Safety reminder:</strong> low voltage only; learners under 18 build with adult supervision.</p>

<h3>Outlines for the seven options</h3>
<table>
<thead><tr><th>Project</th><th>Inputs / outputs</th><th>Key logic</th><th>Test cases</th></tr></thead>
<tbody>
<tr><td>Obstacle-Avoiding Robot</td><td>Distance sensor; motors</td><td>If distance is below a limit, stop, turn, then continue</td><td>Wall ahead, open space, object at the side</td></tr>
<tr><td>Line-Following Robot</td><td>Line sensors; motors</td><td>Sensor on line: go straight; drifting: turn back toward line</td><td>Straight line, curve, line lost</td></tr>
<tr><td>Smart Irrigation System</td><td>Soil moisture sensor; pump or relay</td><td>If moisture is below threshold, pump on; else off</td><td>Dry soil, wet soil, reading exactly at threshold</td></tr>
<tr><td>Robotic Arm Simulation</td><td>Target positions; joint angles</td><td>Move to pick point, close gripper, move to place point, open</td><td>Two locations, reversed locations, unreachable position</td></tr>
<tr><td>Smart Waste-Sorting System</td><td>Sensors such as colour or metal detection; sorting gate or servo</td><td>Classify the item, move the gate to the matching bin</td><td>Each item type, unknown item, two items quickly</td></tr>
<tr><td>Voice-Controlled Robot</td><td>Voice commands; motors</td><td>Match a recognised word to forward, back, left, right or stop</td><td>Each command, unknown word, background noise</td></tr>
<tr><td>IoT Monitoring System</td><td>Sensor readings; dashboard values</td><td>Read, send at intervals, display, flag readings beyond a limit</td><td>Normal reading, high reading, connection lost</td></tr>
</tbody>
</table>

<h3>The 5-minute demo</h3>
<ol>
<li>Minute 1: the problem and who it affects.</li>
<li>Minute 2: your design, with the diagram.</li>
<li>Minutes 3 and 4: live demonstration of the three test cases.</li>
<li>Minute 5: results, problems you solved, what you would improve, and questions.</li>
</ol>
<p>Always prepare a backup: a short screen recording or screenshots, in case a wire, battery or internet connection fails. Practise aloud with a timer at least twice, speak slowly, and ask a classmate to try to break your project so you meet surprises before the real audience does.</p>

<h3>How you are assessed</h3>
<table>
<thead><tr><th>Component</th><th>Weight</th></tr></thead>
<tbody>
<tr><td>Quizzes and knowledge checks</td><td>15%</td></tr>
<tr><td>Practical exercises and simulations</td><td>25%</td></tr>
<tr><td>Project documentation</td><td>20%</td></tr>
<tr><td>Final project and presentation</td><td>40%</td></tr>
</tbody>
</table>
<p>The weights add up to 100%. To get your weighted mark, multiply each score by its weight and add the results.</p>

<h3>Worked example: weighted mark</h3>
<p>Nasra scores 80 in quizzes, 70 in practicals, 60 in documentation and 75 in the final project.</p>
<pre><code>Quizzes:        80 x 0.15 = 12.0
Practicals:     70 x 0.25 = 17.5
Documentation:  60 x 0.20 = 12.0
Final project:  75 x 0.40 = 30.0
Total                     = 71.5</code></pre>
<p>Her weighted mark is 71.5%. Notice that raising documentation from 60 to 80 would add 4 marks (20 x 0.20), so careful notes are worth the effort. Learners who meet the academy's assessment requirements earn a certificate.</p>

<h3>Keep going after the course</h3>
<p>Improve your project (add a second sensor or a safer stop function), build a second one, publish your documentation as part of a portfolio, join a school club or maker community, and learn one new tool at a time.</p>

<h3>Case scenario</h3>
<p>Hellen built a line-following robot and demonstrated it live, but the classroom lights changed the sensor readings and it left the track. She had no recording and no calibration notes. She lost marks for the demo even though her documentation was good. She re-tested, wrote down a calibration step and kept a short video as backup. The lesson: test in the real place beforehand and always carry a backup.</p>

<blockquote><p><strong>Quick check:</strong> Which component carries the largest share of the final mark?</p><p>Answer: The final robotics project and presentation at 40%, the largest of the four weights.</p></blockquote>

<blockquote><p><strong>Quick check:</strong> A learner scores 90 on a component weighted 20%. How many marks does it contribute?</p><p>Answer: 18 marks, because 90 x 0.20 = 18.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Write your project's inputs, outputs, key logic and three test cases from the table.</li>
<li>Draft your 5-minute demo outline with times.</li>
<li>Record a short backup video or take screenshots of a working run.</li>
<li>Calculate your own expected weighted mark with honest estimates.</li>
<li>Write three actions for the month after the course.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Every project has inputs, outputs, key logic and test cases.</li>
<li>Rehearse the demo and keep a backup.</li>
<li>Weighted mark = score x weight, summed: 15%, 25%, 20% and 40%.</li>
<li>Keep building; your portfolio grows your opportunities.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A learner enjoys writing code that reads sensors and makes decisions, and has little interest in soldering. Which career area fits best?
- Industrial automation maintenance
- Electronics hardware repair
* Robotics software development
- Mechanical frame design
= Robotics software is about the code that turns sensor readings into decisions and motor commands, which matches this learner's interest.

Q: A project's parts cost UGX 60,000 in total. You add a 10% contingency. What is the budget?
- UGX 61,000
- UGX 70,000
- UGX 62,000
* UGX 66,000
= 10% of 60,000 is 6,000, and 60,000 + 6,000 = 66,000. A contingency covers mistakes and delivery surprises.

Q: Peter told a school his prototype would cost UGX 100,000 but forgot the pump and relay, so the real cost was much higher and funding stopped. What was the main mistake?
* An unrealistic budget that did not list every item or include a contingency
- Choosing a project that was too small
- Using low-voltage power
- Writing a concept note
= A line-by-line budget with a contingency prevents this. Underestimating costs damages trust with funders.

Q: Which items belong in a one-page concept note? (choose all that apply)
* The problem and who is affected
* An estimated budget and testing plan
- A guarantee that the idea will earn profit
* Risks and support needed
= A concept note explains the problem, the solution, cost, testing and risks honestly. No venture can guarantee profit.

Q: A learner has no physical parts but wants to do the final project. What is the correct approach?
- Wait until parts can be bought
- Skip the project
* Complete the project using a suitable simulation tool
- Borrow mains-powered equipment
= Learners without physical components complete projects with simulators, and mains electricity should never be used for these builds.

Q: A learner scores 80 in quizzes, 60 in practicals, 70 in documentation and 90 in the final project. Using the weights 15%, 25%, 20% and 40%, what is the weighted mark?
- 75.0
* 77.0
- 80.0
- 72.5
= 80 x 0.15 = 12, 60 x 0.25 = 15, 70 x 0.20 = 14 and 90 x 0.40 = 36. The total is 77.

Q: A learner's live demo failed because a battery connection came loose, and they had nothing else to show. What would have prevented the problem?
- A longer presentation
* A prepared backup such as a short video or screenshots of a working run
- Skipping the test cases
- More decoration on the robot
= A backup plan lets you show evidence of your work when hardware or connections fail.

Q: Which test cases fit a Smart Irrigation System?
- Wall ahead, open space, object at the side
- Each voice command and an unknown word
* Dry soil, wet soil, and a reading exactly at the threshold
- Straight line, curve, line lost
= The irrigation logic depends on the moisture threshold, so test below, above and at the threshold.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which of these is NOT normally needed for a robot to work as a sense-think-act system?
- A controller
- Sensors
* A mains wall socket connection for learner projects
- Actuators
= Learner robots use low-voltage batteries or USB power. Mains electricity is dangerous and is left to qualified electricians.

Q: A 2000 mAh battery powers a load that draws 250 mA. About how many hours should it run, ignoring losses?
- 2 hours
- 4 hours
* 8 hours
- 12 hours
= Run time is capacity divided by current: 2000 / 250 = 8 hours. Real batteries usually give somewhat less.

Q: Which part lets current pass in only one direction?
- Resistor
- Capacitor
- Transistor
* Diode
= A diode allows current one way only, which can protect circuits from reversed battery connections.

Q: A farmer in Gulu has a pump on a timer and wants it to run only when soil is dry. What should be added?
- A second timer
* A soil-moisture sensor and a rule in the controller
- A longer wire
- A louder buzzer
= Adding a sensor and a decision rule lets the system sense and decide, turning simple automation into a sensor-based robotic system.

Q: An infrared obstacle sensor works well in a classroom but behaves strangely outside in bright sun. What is the most likely cause?
- The battery is too strong
* Sunlight contains infrared light that floods the sensor
- The wheels are too large
- The code has a spelling mistake
= IR sensors rely on reflected infrared, and sunlight adds a lot of it. Shade the sensor or use another type such as ultrasonic.

Q: Which motor type is best for moving a gripper to an exact angle?
- A bare DC motor
- A stepper on a long belt
* A hobby servo
- A fan motor
= A servo moves to and holds a commanded angle, so it suits arms and grippers.

Q: A stepper motor takes 200 steps per revolution. How many degrees is one step, and how many steps make a quarter turn?
- 3.6 degrees and 25 steps
* 1.8 degrees and 50 steps
- 1.8 degrees and 100 steps
- 0.9 degrees and 25 steps
= 360 / 200 = 1.8 degrees per step, and a quarter turn is 200 / 4 = 50 steps.

Q: A gyroscope reads a steady 20 degrees per second for 4.5 seconds. About how far has the robot rotated?
- 45 degrees
- 24 degrees
* 90 degrees
- 180 degrees
= 20 x 4.5 = 90 degrees. Drift over long periods means gyroscope readings are often combined with an accelerometer.

Q: A robot has wheels 100 mm apart and spins on the spot with each wheel moving at 100 mm/s. About how long does a 90-degree turn take?
- About 0.2 s
* About 0.8 s
- About 3.1 s
- About 8 s
= Each wheel follows a quarter circle of radius 50 mm: 2 x 3.14 x 50 / 4 = about 78.5 mm. At 100 mm/s that takes about 0.79 s.

Q: Why is "the robot should be good at avoiding objects" a poor requirement for a first robot project?
- It is too long
* It cannot be measured or tested; a number such as 20 cm makes it testable
- Robots cannot avoid objects
- Requirements should never mention sensors
= Measurable requirements can be tested with a pass or fail result.

Q: Your robot restarts whenever the motors begin to turn, but runs fine when lifted with the wheels stopped. What is the most likely cause?
- The wheels are too large
* The battery voltage falls under motor load
- The code has too many comments
- The ultrasonic sensor is facing the wrong way
= The restart at the moment the motors draw current points to a voltage drop from weak cells or poor wiring.

Q: The left line sensor is over the tape and the right sensor is over the floor. The program curves left. What does this tell you?
- The robot is correctly centred
* The line is under the left sensor, so the robot has drifted to the right and steers back
- The sensors must be swapped
- The line has ended
= The line should sit between the sensors. A left sensor on the line means the robot is too far right.

Q: What is the main difference between a conventional robot and an AI-enabled robot?
- Conventional robots never use sensors
* An AI-enabled robot uses rules learned partly from example data, not only rules written by a programmer
- AI-enabled robots cannot move
- Conventional robots always cost more
= Conventional robots follow explicit programmed rules. AI-enabled robots add behaviour learned from training data.

Q: A cherry-sorting model produces 30 true ripe, 10 false ripe, 50 true unripe and 10 missed ripe results. What is its accuracy?
- 70%
* 80%
- 75%
- 85%
= Correct results are 30 + 50 = 80 out of 100 total (30 + 10 + 50 + 10), so accuracy is 80%.

Q: A face-detection door system works poorly for people with darker skin. What is the most likely cause?
- The cables are too long
- The camera is too modern
- Industry 4.0 is not enabled
* Biased training data that under-represented those people
= Bias usually comes from unbalanced training data. It can be reduced with more varied data, testing with real users and a manual fallback.

Q: A bottling line stops often. Which check should come first?
- Replace the robot arm with a bigger model
- Rewrite the whole PLC program
* Check sensor aim, cleanliness and settings, then look at jams and the program
- Ask workers to work faster
= Mis-set or dirty sensors are a common cause of false stops and are quick and cheap to check before anything expensive.

Q: Which of these are advantages of virtual prototyping? (choose all that apply)
* Mistakes cost no parts
* Tests can be repeated with the same conditions
- It proves the real robot will work without any testing
- It removes the need to document results
= Simulation is cheap, safe and repeatable, but it is a simplified model, so real testing and records are still needed.

Q: An ESP32 must report soil moisture to a phone through a home router, and battery life is not the main concern. Which link fits best?
- Bluetooth only, because it is always better
* Wi-Fi, because it reaches the internet through a router
- A wired Ethernet cable to the soil
- No link, because ESP32 boards cannot connect
= Wi-Fi connects through the router to the internet, while Bluetooth suits direct links to a nearby phone.

Q: A device publishes to mbarara-farm/shed1/soil-probe2/moisture. Which subscription receives the moisture of every device in shed1?
- mbarara-farm/shed1/moisture
- shed1/#/moisture
* mbarara-farm/shed1/+/moisture
- mbarara-farm/moisture/+
= The plus sign matches exactly one level, here the device name, so the pattern covers all devices in shed1.

Q: A tank sensor sends 40 bytes every 5 minutes, ignoring overhead. How many bytes does it send in one day?
- 2,880 bytes
- 5,760 bytes
* 11,520 bytes
- 57,600 bytes
= There are 12 readings per hour x 24 = 288 readings per day, and 288 x 40 bytes = 11,520 bytes.

Q: A 7.4 V battery is shorted through a wire of 0.5 ohm. About how much current flows?
- 3.7 A
- 7.4 A
- 0.07 A
* 14.8 A
= Current is voltage divided by resistance: 7.4 / 0.5 = 14.8 A.

Q: A starter irrigation controller costs UGX 120,000 and brings benefits of UGX 4,000 a week. How many weeks to pay back?
- 20 weeks
* 30 weeks
- 48 weeks
- 3 weeks
= Payback is cost divided by weekly benefit: 120,000 / 4,000 = 30 weeks.

Q: A recycling group in Kampala asks you to design a project, but your design needs old electronics to be burned for copper. What is the responsible response?
- Allow it because copper is valuable
- Burn only small amounts
- Do it at night
* Reject burning and arrange safe collection and authorised recycling instead
= Burning boards and batteries releases toxic fumes; e-waste should go to authorised recyclers.

Q: Which design choice best supports accessibility for a low-vision user of a robot dispenser?
- A tiny touchscreen
* A large button with sound feedback as well as light
- A silent dim light only
- Instructions in small print
= Inclusive design gives more than one way to sense and operate the device.

Q: A face-recognition robot works well for some pupils but often fails for others. What is the main ethical problem?
- The screen is too small
- The battery is too heavy
* Bias from unrepresentative training data, which needs testing and human oversight
- The robot is too quiet
= Unequal accuracy across groups is bias and must be tested and corrected.

Q: Which share of the final mark comes from the final project and presentation?
- 15%
- 25%
- 20%
* 40%
= The weights are quizzes 15%, practicals and simulations 25%, documentation 20% and final project and presentation 40%.

Q: What does a contingency in a budget protect against?
* Mistakes, missing items and delivery surprises
- Taxes only
- The need for any testing
- Changes in project difficulty
= A contingency of about 10% is a margin for things that were not planned.

Q: A learner with a laptop but no parts, who finished the soil-moisture lessons confidently, chooses a project. Which is the most sensible?
- A voice-controlled robot built with new hardware
* A Smart Irrigation System as a simulation
- A physical robotic arm
- Skipping the project
= It matches the learner's skills and tools, and simulation is fully acceptable for those without parts.

Q: What should a portfolio entry for a project include?
- Only a photo
* The problem, what you built, test results and what you learned
- A list of prices only
- Only the code without explanation
= A good portfolio entry shows the problem, the work, the evidence and your reflection so others can judge it.

Q: A learner scores 100 on quizzes and 50 on everything else. Using the weights 15%, 25%, 20% and 40%, what is the weighted mark?
- 62.5
- 50.0
* 57.5
- 75.0
= 100 x 0.15 = 15, and the other components total 50 x (0.25 + 0.20 + 0.40) = 50 x 0.85 = 42.5. Then 15 + 42.5 = 57.5.
QUIZ,
];
