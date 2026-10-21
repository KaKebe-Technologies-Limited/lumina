<?php
return [
    'slug'        => 'video-editing-content-creation',
    'title'       => 'Video Editing and Content Creation',
    'category'    => 'design',
    'level'       => 'beginner',
    'icon'        => 'layers',
    'sort'        => 310,
    'summary'     => 'Plan, shoot, edit and publish clear, honest videos with a phone and free tools, from first idea to a finished 60-second video for an organisation.',
    'description' => <<<'HTML'
<p>Video is how many people learn, shop, follow news and decide whether to trust an organisation. A short, clear video can show a school project, promote a small business, teach a skill or tell a community story, and you can make one with the phone you already own.</p>
<p>This course takes you through the whole process. You will plan content for a real audience, shoot with good framing, light and sound, edit using free tools, add captions, music and graphics, export for each platform, and publish and learn from the results. You will also learn how to film people respectfully, especially children, and how to be honest in what you share.</p>
<p>The lessons use free tools described by kind: a free phone video editor, a free desktop editor such as DaVinci Resolve or Shotcut, and Canva video. Screens and menu names differ between tools and change over time, so focus on the ideas and look for the matching button in your own tool. The last lesson is a capstone: a 60-second video for an NGO from plan to publish.</p>
HTML,
    'outcomes'    => [
        'Define the audience, goal and message of a video before filming',
        'Write a simple script, storyboard and shot list',
        'Choose the right format and length for each platform',
        'Shoot steady, well-framed, well-lit video with clear audio on a phone',
        'Film people with consent and protect the privacy and safety of children',
        'Import, cut, trim and assemble a video on a timeline with text and captions',
        'Improve sound, add voice-over, thumbnails and consistent branding',
        'Export, publish, measure and improve videos, and keep files organised and backed up',
    ],
    'requirements'=> [
        'No experience needed',
        'A smartphone with a camera, and ideally a computer or laptop',
        'Free editing software or apps (suggestions are in the course)',
    ],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'YouTube Creator Academy', 'url' => 'https://creatoracademy.youtube.com'],
        ['title' => 'DaVinci Resolve (free version)', 'url' => 'https://www.blackmagicdesign.com/products/davinciresolve'],
        ['title' => 'Shotcut (free, open-source video editor)', 'url' => 'https://shotcut.org'],
        ['title' => 'Canva video editor', 'url' => 'https://www.canva.com/video-editor'],
        ['title' => 'YouTube Help', 'url' => 'https://support.google.com/youtube'],
    ],
    'modules' => [
        // ------------------------------------------------------------------ MODULE 1
        [
            'title'   => 'Module 1: Planning content',
            'summary' => 'Decide who the video is for, what it must achieve and how to organise the story before you film.',
            'lessons' => [
                [
                    'title'   => 'Audience, goal and message',
                    'minutes' => 10,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to say exactly who a video is for, what you want them to do, and the one message it carries. This matters because videos made without a clear purpose are long, confusing and forgotten, while a focused video gets watched and shared.</p>
<h3>Start with the audience</h3>
<p>Your <strong>audience</strong> is the group of people you want to reach. "Everyone" is not an audience. Be specific: "Parents of primary school learners in Gulu who use WhatsApp", or "young people aged 18 to 25 looking for training".</p>
<p>Ask yourself:</p>
<ul>
<li>Who are they, and what languages do they speak at home?</li>
<li>Which apps do they use, and how much data can they afford?</li>
<li>What do they already know, and what do they worry about?</li>
<li>When and where will they watch: on a phone in a taxi, at home in the evening?</li>
</ul>
<h3>Set one goal</h3>
<p>Choose one main goal for each video.</p>
<table>
<thead><tr><th>Goal</th><th>Example</th><th>How you will know it worked</th></tr></thead>
<tbody>
<tr><td>Inform</td><td>Explain how to register for a free ICT class</td><td>Number of people who register</td></tr>
<tr><td>Teach</td><td>Show how to make a strong password</td><td>Viewers complete the tip, comments and questions</td></tr>
<tr><td>Persuade or raise funds</td><td>Show the need for a school water tank</td><td>Donations or pledges</td></tr>
<tr><td>Build trust</td><td>Introduce the team and their work</td><td>Followers, replies, visits</td></tr>
<tr><td>Entertain or celebrate</td><td>Show highlights of a youth day</td><td>Shares and positive comments</td></tr>
</tbody>
</table>
<h3>Write one core message</h3>
<p>The <strong>core message</strong> is the one sentence you want viewers to remember. Complete this: "After watching, my audience will know ____ and will ____." Example: "After watching, parents will know that the ICT club is free and will register their child by Friday."</p>
<h3>Add a call to action</h3>
<p>A <strong>call to action</strong> (CTA) tells viewers the next step: "Send 'JOIN' to this number", "Visit our centre on Monday", "Share with a friend". Make it single, clear and easy.</p>
<h3>Check the idea</h3>
<ol>
<li>Is it useful to the audience, not only to us?</li>
<li>Is it true, and can we show proof?</li>
<li>Can we make it with the people, time and equipment we have?</li>
<li>Does it respect the dignity of everyone shown?</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Choose a video idea for a school, club, business or organisation you know.</li>
<li>Write the audience in one specific sentence.</li>
<li>Write one goal and the core message using the sentence above.</li>
<li>Write the call to action and one way you will measure success.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Know exactly who the video is for and where they watch.</li>
<li>Give each video one goal and one core message.</li>
<li>End with one clear call to action.</li>
<li>Check that the idea is useful, true, possible and respectful.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Story structure and scripts',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to give a video a clear shape and write a short script in plain spoken language. This matters because viewers decide in the first few seconds whether to stay, and a simple structure keeps them watching to the end.</p>
<h3>A simple story shape</h3>
<p>Most good short videos follow three parts:</p>
<ol>
<li><strong>Hook (first 3 to 5 seconds).</strong> Give the viewer a reason to stay: a question, a surprising fact, a striking picture or a problem. Example: "Do you know how many learners in our village share one book?"</li>
<li><strong>Middle (the story or the steps).</strong> Show the situation, the people, what was done or the steps to do something. Keep to one idea.</li>
<li><strong>Ending and call to action.</strong> Say the result or the lesson, then tell people what to do next.</li>
</ol>
<h3>Types of video and their shape</h3>
<table>
<thead><tr><th>Type</th><th>Shape</th></tr></thead>
<tbody>
<tr><td>How-to</td><td>Hook with the result, steps one to three, show the final result, CTA</td></tr>
<tr><td>Story or testimony</td><td>Problem, what changed, how the person feels now, how to help or join</td></tr>
<tr><td>Event highlight</td><td>Opening shot, best moments, voices of participants, thanks and next date</td></tr>
<tr><td>Announcement</td><td>What, when, where, how to take part</td></tr>
</tbody>
</table>
<h3>Writing a script</h3>
<p>A <strong>script</strong> is the written plan of what will be said and shown. Write it to be <em>heard</em>, not read.</p>
<ul>
<li>Use short sentences and everyday words, in the language your audience prefers.</li>
<li>Read it aloud. If you stumble, simplify.</li>
<li>Speak at about 130 to 150 words per minute. A 60-second video holds about 130 to 150 words at most, and less if you want pauses and music.</li>
<li>Cut every sentence that does not support the core message.</li>
</ul>
<h3>A script in two columns</h3>
<pre><code>PICTURE                          SOUND / WORDS
Close-up of an empty desk        "Many learners here have never touched a computer."
Learners entering the room       "This changes on Monday."
Learners at laptops, smiling     "Our free ICT club starts for ages 15 to 24."
Text: Register - free            "Register today at the school office."</code></pre>
<h3>Interviews</h3>
<p>For interviews, prepare three to five open questions ("What changed for you?") and let the person answer in their own words. Do not put words in their mouth. Always get their agreement before sharing.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose a 60-second video idea and write a hook, middle and ending in three short paragraphs.</li>
<li>Turn it into a two-column script (picture and sound).</li>
<li>Read it aloud with a timer and cut words until it fits 60 seconds.</li>
<li>Ask a friend if the first five seconds make them want to continue.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Use hook, middle and ending with a call to action.</li>
<li>Write scripts for the ear, with short everyday sentences.</li>
<li>About 130 to 150 spoken words fit in a minute.</li>
<li>Prepare open questions for interviews and respect people's own words.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Storyboards and shot lists',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to turn a script into a storyboard and a shot list that you can take to a filming location. This matters because a little drawing and listing at the desk saves hours of confusion, repeated filming and wasted battery and storage on the day.</p>
<h3>What is a storyboard?</h3>
<p>A <strong>storyboard</strong> is a series of simple sketches, like a comic strip, showing what each shot will look like. You do not need to draw well. Stick figures and boxes are enough. Under each picture write the words, text on screen and any sound.</p>
<h3>Drawing a quick storyboard</h3>
<ol>
<li>Draw a row of rectangles on paper in the same shape as your video (tall for vertical, wide for landscape).</li>
<li>Sketch the main picture of each shot with simple shapes.</li>
<li>Write the words or sound below each picture.</li>
<li>Number the shots in order.</li>
</ol>
<h3>The shot list</h3>
<p>A <strong>shot list</strong> is a checklist of all the shots you need to capture, in a practical order for filming (not necessarily the final order). Group shots by location to save time.</p>
<table>
<thead><tr><th>No.</th><th>Shot</th><th>Type</th><th>Place</th><th>Done</th></tr></thead>
<tbody>
<tr><td>1</td><td>School gate sign</td><td>Wide</td><td>Entrance</td><td></td></tr>
<tr><td>2</td><td>Teacher greeting learners</td><td>Medium</td><td>Classroom door</td><td></td></tr>
<tr><td>3</td><td>Hands typing on laptop</td><td>Close-up</td><td>Computer room</td><td></td></tr>
<tr><td>4</td><td>Learner explaining what she learned</td><td>Interview</td><td>Shaded area</td><td></td></tr>
</tbody>
</table>
<h3>Shot types in plain words</h3>
<ul>
<li><strong>Wide shot:</strong> shows the whole place and where things are.</li>
<li><strong>Medium shot:</strong> shows a person from the waist up, good for talking.</li>
<li><strong>Close-up:</strong> shows a face or an object in detail, which carries emotion.</li>
<li><strong>B-roll:</strong> extra shots that cover the main action, such as hands, tools, signs, surroundings and reactions.</li>
</ul>
<h3>Other planning items</h3>
<ul>
<li><strong>Permissions:</strong> who must agree to filming? A head teacher, a landlord, local leaders? Get consent in advance.</li>
<li><strong>Time of day:</strong> morning and late afternoon light is gentler than noon sun.</li>
<li><strong>Equipment checklist:</strong> charged phone, spare power bank, free storage space, lens cleaning cloth, simple tripod or stable support, microphone if available.</li>
<li><strong>Backup plan:</strong> what if it rains or the person does not come?</li>
</ul>
<h3>Try it yourself</h3>
<ol>
<li>Take your script from the last lesson and sketch at least six storyboard frames.</li>
<li>Write a shot list with at least eight shots, mixing wide, medium, close-up and B-roll.</li>
<li>Group them by location and mark where you need permission.</li>
<li>Write your equipment checklist and a backup plan.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A storyboard is a simple sketch plan; stick figures are fine.</li>
<li>A shot list is a checklist grouped by location for efficient filming.</li>
<li>Mix wide, medium and close shots, and always plan B-roll.</li>
<li>Arrange permissions, light and equipment before the day.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Platforms, formats and video length',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose the right shape and length of video for each platform. This matters because a video made for the wrong format gets cropped, shows black bars or is skipped, and wastes your effort.</p>
<h3>Aspect ratio</h3>
<p>The <strong>aspect ratio</strong> is the shape of the frame, written as width to height.</p>
<table>
<thead><tr><th>Format</th><th>Ratio</th><th>Typical size (pixels)</th><th>Best for</th></tr></thead>
<tbody>
<tr><td>Vertical</td><td>9:16</td><td>1080 x 1920</td><td>Phone-first short videos, stories, reels, TikTok, WhatsApp Status</td></tr>
<tr><td>Square</td><td>1:1</td><td>1080 x 1080</td><td>Feed posts that work in both orientations</td></tr>
<tr><td>Portrait feed</td><td>4:5</td><td>1080 x 1350</td><td>Social feeds that show more screen area on phones</td></tr>
<tr><td>Landscape</td><td>16:9</td><td>1920 x 1080</td><td>YouTube, presentations, TV, laptops</td></tr>
</tbody>
</table>
<p>Platform rules change often, so always check the platform's current help page before you export. Decide the format <strong>before</strong> you shoot. If you film vertical you cannot easily turn it into a good landscape video, and the other way round.</p>
<h3>Platforms in brief</h3>
<ul>
<li><strong>YouTube:</strong> longer, searchable videos, tutorials, talks and documentaries. Landscape is normal; vertical Shorts are also supported.</li>
<li><strong>Facebook:</strong> wide reach in many communities. Short videos and Reels; good for events and announcements.</li>
<li><strong>TikTok:</strong> vertical, fast, creative short videos with sound and trends.</li>
<li><strong>WhatsApp Status and groups:</strong> personal and community reach. Short, small files that load on weak data.</li>
<li><strong>Instagram:</strong> vertical Reels and square or portrait posts.</li>
</ul>
<h3>How long should it be?</h3>
<p>There is no perfect length, but these are useful starting points.</p>
<table>
<thead><tr><th>Purpose</th><th>Suggested length</th></tr></thead>
<tbody>
<tr><td>Quick tip or announcement</td><td>15 to 45 seconds</td></tr>
<tr><td>Story or highlight</td><td>45 to 90 seconds</td></tr>
<tr><td>Tutorial or interview</td><td>3 to 10 minutes</td></tr>
<tr><td>Training or talk</td><td>10 minutes or more, split into chapters</td></tr>
</tbody>
</table>
<p>Make it as long as the story needs and not longer. If a point is repeated, cut it.</p>
<h3>Data and file size</h3>
<p>Many viewers pay for data by the megabyte. Compress your video before sharing on WhatsApp, keep the length short and use captions so people can follow without sound. Offer a lighter version when you can.</p>
<h3>Safe areas</h3>
<p>On vertical platforms, buttons and captions cover parts of the screen. Keep important text away from the top and bottom edges and away from the right side.</p>
<h3>Try it yourself</h3>
<ol>
<li>Pick one video idea and choose the main platform and format for it.</li>
<li>Write why that platform fits your audience.</li>
<li>Decide the length and write one thing you will cut to reach it.</li>
<li>Check the platform's help page for its current recommended size and note it.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Choose the aspect ratio before you shoot.</li>
<li>Vertical for phone-first platforms, landscape for YouTube and screens.</li>
<li>Keep videos as short as the message allows, and light on data.</li>
<li>Check platform guidelines because they change.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which is the best description of an audience for a video?
- Everyone on the internet
* Parents of primary school learners in Gulu who use WhatsApp
- People who like videos
- Our team
= A specific audience lets you choose the language, platform and message.

Q: What should a video's core message be?
- As many ideas as possible
* One sentence the viewer should remember
- Only the logo
- A long list of services
= One clear message makes the video focused and memorable.

Q: Where should the hook be placed?
- At the end
* In the first few seconds
- After the credits
- In the description only
= Viewers decide within seconds whether to keep watching.

Q: About how many spoken words fit into 60 seconds at a natural pace?
- 20 to 40
* 130 to 150 at most
- 400 to 500
- 1000
= A normal speaking pace is about 130 to 150 words per minute.

Q: Which are examples of B-roll? (choose all that apply)
* Hands typing on a laptop
* A close-up of a sign
* Learners walking into a classroom
- The audio track with no pictures
= B-roll is extra footage that covers and supports the main action.

Q: You plan a video for TikTok and WhatsApp Status. Which format is best?
- Landscape 16:9
- Wide cinema format
* Vertical 9:16
- Any format works equally well
= Vertical fills a phone screen on these platforms.

Q: Why decide the format before shooting?
- It cannot be decided later
* Changing from vertical to landscape later usually cuts off or crops the picture
- It makes files bigger
- Phones need it
= Planning the frame avoids poor crops and lost content.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 2
        [
            'title'   => 'Module 2: Shooting well',
            'summary' => 'Use a phone camera, framing, light and above all clear sound, and film people with respect.',
            'lessons' => [
                [
                    'title'   => 'Phone camera settings and stability',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to set up your phone camera for good video and keep your shots steady. This matters because shaky, blurry or badly set up footage is hard to fix in editing, while good footage makes everything else easier.</p>
<h3>Before you film</h3>
<ol>
<li><strong>Clean the lens</strong> with a soft clean cloth. A greasy lens makes a foggy picture.</li>
<li><strong>Free up storage</strong> and charge the phone. Video files are large, and a full phone stops recording.</li>
<li><strong>Turn on Do Not Disturb or flight mode</strong> (with the microphone still working) so calls and notifications do not interrupt.</li>
<li><strong>Use the rear camera.</strong> It is usually better than the selfie camera. Use the selfie camera only when you must see yourself.</li>
</ol>
<h3>Settings to check</h3>
<table>
<thead><tr><th>Setting</th><th>Advice</th></tr></thead>
<tbody>
<tr><td>Resolution</td><td>1080p (Full HD) is plenty for most platforms. 4K uses much more storage.</td></tr>
<tr><td>Frame rate</td><td>30 frames per second (fps) for most videos. 24 gives a film look. 60 is for sport or slow motion.</td></tr>
<tr><td>Orientation</td><td>Match your planned format. Hold the phone steady in that orientation for the whole shot.</td></tr>
<tr><td>Grid</td><td>Turn on grid lines in the camera settings to help with straight horizons and composition.</td></tr>
<tr><td>Focus and exposure</td><td>Tap the subject on the screen. Many phones let you lock focus and exposure by holding your finger there, and slide to make the picture brighter or darker.</td></tr>
<tr><td>Zoom</td><td>Avoid digital zoom because it lowers quality. Move closer instead.</td></tr>
</tbody>
</table>
<p>Camera apps differ between phone brands, so look through your settings menu for these options.</p>
<h3>Keeping it steady</h3>
<ul>
<li>Hold the phone with two hands and keep your elbows close to your body.</li>
<li>Lean on a wall, a table or a tree.</li>
<li>Use a small tripod or make one: rest the phone on a stack of books or a bag.</li>
<li>When walking, bend your knees slightly and take smooth, small steps.</li>
<li>Move the camera slowly. Fast swings look amateur and cause blur.</li>
<li>Hold each shot still for at least 5 to 10 seconds so you have enough to edit.</li>
</ul>
<h3>Record a little more</h3>
<p>Start recording a second before the action and stop a few seconds after. Record extra takes of important lines. Check one clip by playing it back with headphones before filming everything.</p>
<h3>Try it yourself</h3>
<ol>
<li>Clean the lens and open the camera settings.</li>
<li>Choose 1080p and 30 fps and turn on the grid.</li>
<li>Film the same object for 10 seconds three ways: handheld without support, handheld with elbows tucked in, and with the phone resting on a stable surface.</li>
<li>Play the three clips and note which is steadiest.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Clean the lens, free storage and silence notifications.</li>
<li>1080p at 30 fps suits most videos.</li>
<li>Tap to focus, avoid digital zoom, and use the grid.</li>
<li>Support the phone and move slowly for steady shots.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Framing, composition and lighting',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to frame pleasing shots and light your subject using windows and cheap lights. This matters because careful framing and light make an ordinary phone video look professional, and they cost nothing.</p>
<h3>Composition basics</h3>
<ul>
<li><strong>Rule of thirds.</strong> Imagine the screen divided into a three-by-three grid. Place the subject, or their eyes, near one of the lines or crossing points rather than in the exact centre.</li>
<li><strong>Headroom.</strong> Leave a small gap above the head. Do not cut off the top of the head or leave a large empty space.</li>
<li><strong>Looking room.</strong> If a person looks or moves to one side, leave space on that side.</li>
<li><strong>Eye level.</strong> Hold the camera at the subject's eye level for a natural look. Too low or too high changes how powerful the person seems.</li>
<li><strong>Clean background.</strong> Check behind the subject. Remove clutter, bright signs and poles that seem to grow out of heads.</li>
<li><strong>Leading lines and depth.</strong> Use paths, fences and walls that lead the eye to the subject. Place something in the foreground for depth.</li>
</ul>
<h3>Shot size recap</h3>
<p>Mix wide, medium and close shots. A talking person is usually framed from mid-chest up. Close-ups of hands, faces and objects add emotion and make editing smoother.</p>
<h3>Light is everything</h3>
<p>Video needs light. A bright, soft light makes clean pictures; a dark scene looks grainy.</p>
<ol>
<li><strong>Use a window.</strong> Put the subject facing a large window so soft daylight lights the face. Do not film with the window behind them, because they become a dark shape (backlight).</li>
<li><strong>Avoid harsh midday sun.</strong> Outdoors, film in the morning or late afternoon, or place the subject in open shade, such as under a tree or the side of a building.</li>
<li><strong>Do not mix light colours.</strong> Yellow bulbs and blue daylight together give strange colours. Turn off room lights if you use window light.</li>
<li><strong>Use cheap lights.</strong> A small LED panel or ring light, or a bright lamp bounced off a white wall or sheet, softens shadows. Place it slightly to one side and above eye level.</li>
<li><strong>Use a reflector.</strong> A sheet of white card, a white cloth or foil on cardboard on the dark side of the face fills the shadow.</li>
</ol>
<h3>Safety</h3>
<p>Be careful with cables and hot lamps near children and flammable cloth. Never place lights where people can trip.</p>
<h3>Quick light checklist</h3>
<table>
<thead><tr><th>Check</th><th>Good</th><th>Problem</th></tr></thead>
<tbody>
<tr><td>Face</td><td>Evenly lit, eyes visible</td><td>Dark face, strong shadows under the eyes</td></tr>
<tr><td>Background</td><td>Not much brighter than the face</td><td>Bright window behind the subject</td></tr>
<tr><td>Colour</td><td>One type of light</td><td>Mixed yellow and blue light</td></tr>
</tbody>
</table>
<h3>Try it yourself</h3>
<ol>
<li>Film a friend, or yourself on a tripod, at three places: with the window behind, with the window in front, and in open shade outside.</li>
<li>Use the grid and the rule of thirds with good headroom.</li>
<li>Compare the three clips and choose the best light.</li>
<li>Try a white sheet as a reflector on the shadow side and compare.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Use the rule of thirds, headroom, looking room and a clean background.</li>
<li>Face the light, usually a window, and never film against a bright window.</li>
<li>Avoid harsh midday sun and mixed colours of light.</li>
<li>Simple reflectors and cheap lights improve a shot a lot.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Clear audio: the most important part',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to record clean, understandable sound, which matters more than picture quality. This is important because viewers forgive a plain picture but quickly leave a video they cannot hear clearly.</p>
<h3>Why audio comes first</h3>
<p>If the picture is a little dark, people still follow the story. If the voice is muffled, echoing or covered by noise, they stop watching. Poor sound is also very hard to repair in editing. Prevent problems at the time of recording.</p>
<h3>The golden rule: get the microphone close</h3>
<p>The closer the microphone is to the mouth, the clearer the voice and the quieter the background noise. A phone held at arm's length picks up the room as much as the speaker. Aim for about 15 to 30 centimetres from the mouth.</p>
<h3>Microphone choices</h3>
<table>
<thead><tr><th>Option</th><th>Notes</th></tr></thead>
<tbody>
<tr><td>Phone's built-in microphone</td><td>Free. Works well if you are close and the place is quiet</td></tr>
<tr><td>Wired earphones with a microphone</td><td>Cheap and often much better than the phone's own microphone when the small mic is near the mouth. Check that it works with your phone</td></tr>
<tr><td>Lavalier (clip-on) microphone</td><td>Clips to the collar, keeps a steady distance, good for interviews and tutorials</td></tr>
<tr><td>Separate voice recorder or second phone</td><td>Record audio on another device close to the speaker and join it in editing</td></tr>
</tbody>
</table>
<h3>Choose a good place</h3>
<ul>
<li>Pick a quiet room. Turn off fans, radios, generators and phone notifications.</li>
<li>Soft things absorb echo: curtains, mattresses, blankets, sofas and clothes. A bare room with hard walls echoes.</li>
<li>Record away from roads, markets, school bells and running water.</li>
<li>Outdoors, face away from the wind or shield the microphone. Wind noise ruins recordings.</li>
</ul>
<h3>Recording tips</h3>
<ol>
<li>Do a 10-second test, then listen with headphones. Fix problems before you film the whole scene.</li>
<li>Ask the speaker to talk at a normal, steady volume and to repeat the first line if there was a noise.</li>
<li>Clap once at the start if using a separate recorder, so you can line up sound and picture in editing.</li>
<li>Record 20 seconds of silence in the same place ("room tone"). It helps to fill gaps and reduce noise.</li>
<li>Do not move the microphone or cable during speech.</li>
</ol>
<h3>Check the level</h3>
<p>If the meter hits the top or you hear crackling, the sound is too loud and distorted ("clipping"). Move back a little or speak softer. If it is very faint, move closer, because turning up a weak recording also raises the noise.</p>
<h3>Try it yourself</h3>
<ol>
<li>Record yourself reading the same three sentences with the phone at arm's length, then 20 centimetres from your mouth.</li>
<li>Record once in a hard-walled room and once in a room with curtains or blankets.</li>
<li>Listen with headphones and note which is clearest.</li>
<li>Write your own audio checklist of five items for future shoots.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Clear audio matters more than perfect picture.</li>
<li>Place the microphone close to the speaker.</li>
<li>Choose a quiet, soft room and protect from wind.</li>
<li>Test with headphones and avoid distortion.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'B-roll, consent and filming people safely',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to collect useful B-roll and film people with consent, care and respect, especially children. This matters because video can help people, but it can also expose them to embarrassment, danger or harm if it is made carelessly.</p>
<h3>B-roll</h3>
<p><strong>B-roll</strong> is the extra footage that supports your main shots. It covers cuts in an interview, adds detail and keeps the video interesting. Good B-roll includes hands at work, tools, signs and logos, the surroundings, reactions, and slow, steady wide shots. Film each B-roll shot for 10 seconds, from different angles, and avoid showing faces if people have not agreed.</p>
<h3>Consent</h3>
<p><strong>Consent</strong> means a person freely agrees, with understanding, to be filmed and to have the video used in a specific way.</p>
<ol>
<li><strong>Ask first.</strong> Explain who you are, what the video is for, where it will be shown (for example Facebook and the organisation's website) and for how long.</li>
<li><strong>Make it voluntary.</strong> No pressure, and no promises or rewards that make it difficult to say no. Say it is fine to refuse or to stop at any time.</li>
<li><strong>Record it.</strong> Use a simple signed consent form, or a short recorded verbal agreement where writing is difficult. Keep it with your records.</li>
<li><strong>Respect withdrawal.</strong> If someone changes their mind, remove the video as far as you can.</li>
<li><strong>Public places.</strong> A crowd in a public place is different from an interview. Still avoid close-ups of identifiable people who did not agree and never film people in distress or at private moments.</li>
</ol>
<h3>Children and vulnerable people</h3>
<p>Take extra care when filming children and anyone who may be vulnerable (for example survivors of violence, people with health conditions, refugees and people in crisis).</p>
<ul>
<li>Get <strong>written consent from a parent or legal guardian</strong>, and the child's own agreement as well, explained simply.</li>
<li>Follow your organisation's child protection and safeguarding policy, and the school's rules. Ask the school before filming on its premises.</li>
<li>Do not publish a child's full name, school, home area, phone number or other details that could help someone find them.</li>
<li>Show children in dignified, positive, clothed and appropriate situations. Never film them in a way that makes them look helpless or ashamed.</li>
<li>Never be alone with a child while filming. Work with another responsible adult present.</li>
<li>If a child or person might be at risk if identified, film hands, backs or silhouettes, or do not film at all.</li>
<li>Do not film people in distress for the sake of a better story. Their safety and dignity come first.</li>
</ul>
<h3>Honest and respectful storytelling</h3>
<p>Let people tell their own story. Avoid stereotypes of poverty or helplessness. Do not stage scenes and present them as real, and do not alter the meaning of what someone said. If a story is sensitive, ask the person to review it before publishing.</p>
<h3>Other people's property and music</h3>
<p>Ask permission to film inside shops, schools, clinics and homes. Be careful about showing logos or private documents. Do not use other people's video or music without permission, which is covered in the next module.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write a short, plain-language consent script (five or six lines) explaining the purpose, where the video will appear and the right to say no or withdraw.</li>
<li>Draft a simple one-page consent form with spaces for name, date and signature, and for a parent or guardian.</li>
<li>List ten B-roll shots for your video idea that do not need faces.</li>
<li>Write three rules from your organisation or school that you would follow when filming children.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>B-roll adds detail and covers cuts.</li>
<li>Consent must be informed, voluntary and recorded, and people can withdraw it.</li>
<li>Children and vulnerable people need extra protection, guardian consent and a safeguarding policy.</li>
<li>Tell real stories with dignity, honesty and care for safety.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which setting is a good default for most phone videos?
- 4K at 120 fps
* 1080p at 30 fps
- 240p
- Digital zoom at maximum
= 1080p at 30 fps is sharp enough for most platforms and uses reasonable storage.

Q: Which methods help keep a shot steady? (choose all that apply)
* Resting the phone on a stable surface
* Keeping elbows close to your body
* Moving the camera slowly
- Using digital zoom while walking
= Support and slow movements reduce shake, while digital zoom lowers quality.

Q: Where should you place a person to light their face well using a window?
- With their back to the window
* Facing the window so soft light falls on their face
- In a dark corner
- Under a bright lamp that is directly overhead
= Facing a large window gives soft, even light. Backlight makes a dark shape.

Q: What is the most important thing to get right in a video?
- The title font
- 4K resolution
* Clear audio
- The thumbnail
= Viewers leave quickly if they cannot hear clearly.

Q: How can you improve a voice recording most simply?
- Stand across the room
* Place the microphone about 15 to 30 centimetres from the mouth in a quiet room
- Turn on a fan to hide noise
- Record next to a busy road
= Distance and noise are the main problems, so go close and choose a quiet place.

Q: You want to film a child at your organisation's event and share the video. What must you do?
- Film first and ask later
* Get written consent from a parent or guardian and follow the safeguarding policy
- Post the child's full name and school with the video
- Offer a gift to make the child agree
= Children need extra protection, and details that identify them should not be published.

Q: A person you interviewed asks to withdraw their video a month later. What should you do?
- Refuse because they signed
* Respect it and remove the video as far as you can
- Ignore the message
- Blur only their name
= Consent is voluntary and can be withdrawn.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 3
        [
            'title'   => 'Module 3: Editing basics',
            'summary' => 'Import clips, cut on a timeline, add text, music and colour, and export for each platform.',
            'lessons' => [
                [
                    'title'   => 'Importing and the timeline',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose a free editor, start a project and place clips on a timeline. This matters because the timeline is the heart of every video editor, and once you understand it, the same ideas work in any tool.</p>
<h3>Choosing a free editor</h3>
<ul>
<li><strong>Free phone video editor:</strong> an app on your phone, good for quick, short, vertical videos and for people without a computer.</li>
<li><strong>Free desktop editor</strong> such as DaVinci Resolve (free version) or Shotcut: more powerful and better for longer projects. These need a reasonably capable computer. Check each tool's website for system requirements.</li>
<li><strong>Canva video:</strong> an online editor with templates, text and music, good for quick social posts and branded clips.</li>
</ul>
<p>Menus, names and buttons <strong>differ between tools and change with updates</strong>. Learn the ideas and then look for the matching feature in your tool. Free versions often have limits, so check what is included.</p>
<h3>Starting a project</h3>
<ol>
<li><strong>Create a new project</strong> and choose the aspect ratio and frame rate that match your plan (for example 9:16 at 30 fps).</li>
<li><strong>Save the project</strong> in a folder with your clips. Name it clearly, such as <code>ict-club-promo-v1</code>.</li>
<li><strong>Import media.</strong> Use the import or add media button, or drag files into the media area. Bring in video clips, audio, images and logos.</li>
<li><strong>Preview</strong> clips by double-clicking or selecting them to see what you have.</li>
</ol>
<h3>The parts of the screen</h3>
<table>
<thead><tr><th>Part</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Media library (bin)</td><td>Holds all your imported clips, sounds and images</td></tr>
<tr><td>Preview or viewer</td><td>Shows the picture at the playhead</td></tr>
<tr><td>Timeline</td><td>Where you arrange clips in order, left to right</td></tr>
<tr><td>Tracks</td><td>Layers on the timeline. Video tracks stack pictures; audio tracks hold sound</td></tr>
<tr><td>Playhead</td><td>The vertical line showing the current moment in time</td></tr>
<tr><td>Inspector or properties</td><td>Controls size, position, volume and effects for the selected item</td></tr>
</tbody>
</table>
<h3>Building a first rough cut</h3>
<ol>
<li>Drag your first clip onto the timeline.</li>
<li>Add the next clips in story order, following your script.</li>
<li>Press the space bar (or the play button) to watch.</li>
<li>Zoom the timeline in and out to see detail or the whole video.</li>
<li>Save often. Many editors also autosave, but do not rely on it.</li>
</ol>
<p>A <strong>rough cut</strong> is a quick first version. Do not worry about polish yet. Get the story in order first.</p>
<h3>Useful shortcuts</h3>
<p>Most editors share these: Space (play and pause), Ctrl+Z (undo, Cmd+Z on a Mac), Ctrl+S (save, Cmd+S on a Mac), and the arrow keys to move frame by frame. Look in your tool's help for others.</p>
<h3>Try it yourself</h3>
<ol>
<li>Install or open a free editor of your choice.</li>
<li>Create a new project with the aspect ratio you planned and save it with a clear name.</li>
<li>Import at least five clips and drag them onto the timeline in story order.</li>
<li>Play the rough cut and write down three things you want to fix.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Free phone, desktop and online editors all share a timeline idea.</li>
<li>Set the aspect ratio first, save the project and keep media together.</li>
<li>The timeline arranges clips and tracks stack layers of picture and sound.</li>
<li>Make a rough cut first and polish later.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Cutting, trimming and transitions',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to trim clips, remove mistakes and join shots smoothly. This matters because most of the skill in editing is deciding what to take out. Tight cuts keep viewers interested and make speakers sound confident.</p>
<h3>Trimming and splitting</h3>
<ul>
<li><strong>Trim.</strong> Drag the start or end edge of a clip on the timeline to remove unwanted seconds. This hides the part but does not delete the original file.</li>
<li><strong>Split (cut).</strong> Place the playhead where you want a cut and use the split or razor button (often the key S or Ctrl+B depending on the tool). This divides the clip in two.</li>
<li><strong>Delete the unwanted piece</strong> and close the gap. Many editors have a "ripple delete" that closes the gap automatically.</li>
<li><strong>Move clips</strong> by dragging them. Reorder them to improve the story.</li>
</ul>
<h3>What to cut</h3>
<ol>
<li>Cut the dead time at the start and end of each clip.</li>
<li>Cut mistakes, long pauses, and filler words such as "um" and "you know".</li>
<li>Cut repeated points.</li>
<li>Cut shots that are shaky, blurred or too dark, unless they are essential.</li>
<li>If it does not support the core message, cut it, even if you love it.</li>
</ol>
<h3>Cutting on action and covering with B-roll</h3>
<p>When someone speaks and you cut out a sentence, the picture "jumps". Cover that jump by placing a B-roll clip on a track above while the voice continues underneath. This is called a <strong>cutaway</strong>. Also cut during movement (a hand reaching, a door opening) so the change is less noticeable.</p>
<h3>Pace</h3>
<p>Most shots in a short video last from 2 to 6 seconds. Close shots can be shorter. Quiet or emotional moments can be longer. Keep the rhythm matched with the music or voice.</p>
<h3>Transitions, used sparingly</h3>
<p>A <strong>transition</strong> is the effect between two clips. The plain <strong>cut</strong> (no effect) is correct most of the time. A short <strong>cross dissolve</strong> (one picture fading into the next) suits a change of time or place. Fancy spins, flips and glitters look amateur and distract. Use at most one or two types in a video and keep them short, for example 0.5 seconds. Fade in from black at the start and out at the end if it suits.</p>
<h3>Join audio smoothly</h3>
<p>Sound should not jump. Add small fades (about a quarter of a second) at the start and end of audio clips, and let the sound from the next scene begin slightly before the picture changes for a smooth feel (a "J cut").</p>
<h3>Try it yourself</h3>
<ol>
<li>Take your rough cut and trim the start and end of every clip.</li>
<li>Cut out at least three mistakes or pauses, and cover one jump with B-roll.</li>
<li>Add a single simple transition where the place or time changes.</li>
<li>Watch it twice, and shorten any shot that feels slow.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Most editing is removing what does not help the story.</li>
<li>Trim, split, delete and reorder clips on the timeline.</li>
<li>Cover jumps with B-roll and use short plain transitions sparingly.</li>
<li>Smooth the sound with small fades.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Text, captions and music',
                    'minutes' => 14,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to add readable text, accurate captions and music that you are allowed to use. This matters because many people watch with the sound off or in noisy places, and music used without permission can get your video muted, blocked or taken down.</p>
<h3>Text on screen</h3>
<ul>
<li>Use a clear, simple font in a large size. Test on a phone screen.</li>
<li>Choose strong contrast: light text on dark areas or dark text on light areas. A soft shadow or a plain box behind the words helps.</li>
<li>Keep words few. One short line at a time.</li>
<li>Keep text in the safe area, away from the screen edges and platform buttons.</li>
<li>Show text long enough to read it twice (about one second for every three words, at least).</li>
<li>Use one or two fonts for the whole video.</li>
</ul>
<h3>Captions and subtitles</h3>
<p><strong>Captions</strong> are the words spoken in your video, shown as text. They help people who are deaf or hard of hearing, people in noisy or silent places, and viewers who speak another language or are using the video to learn English. Many tools can <strong>generate captions automatically</strong>. Always read and fix them, because names, local words and numbers are often wrong.</p>
<ol>
<li>Use the auto-caption or subtitle feature if your tool has one, or type text manually.</li>
<li>Correct spelling, names, numbers and punctuation.</li>
<li>Keep each caption to one or two short lines, matched with the speech.</li>
<li>Use a plain, readable style with strong contrast.</li>
</ol>
<h3>Music and copyright</h3>
<p><strong>Copyright</strong> means that the creator owns a song, video or picture and controls its use. Popular songs are almost always copyrighted. Using them without permission can lead to your video being blocked, muted or removed, or to legal trouble, even if you "give credit".</p>
<table>
<thead><tr><th>Safe options</th><th>Notes</th></tr></thead>
<tbody>
<tr><td>Music from the platform's own free audio library</td><td>Check the licence terms for your use</td></tr>
<tr><td>Royalty-free or Creative Commons music</td><td>Read the licence. Some require credit or do not allow commercial use</td></tr>
<tr><td>Music from your editing tool's free library</td><td>Read the terms. Rules can differ from one tool to another</td></tr>
<tr><td>Music you make yourself, or from a friend who gives written permission</td><td>Keep the written permission</td></tr>
<tr><td>No music at all</td><td>Natural sound and voice are often enough</td></tr>
</tbody>
</table>
<p>The same care applies to photos, clips and logos taken from the internet. "I found it online" does not mean you can use it.</p>
<h3>Mixing music with a voice</h3>
<p>Keep music much quieter than the voice, around a quarter to a third of the volume while someone speaks, and bring it up a little during pauses. Choose music that fits the mood and does not have lyrics over speech.</p>
<h3>Try it yourself</h3>
<ol>
<li>Add a title and a short text caption to your rough cut. Check readability on a phone.</li>
<li>Generate automatic captions if your tool has them, or type captions for 20 seconds of speech, and correct all mistakes.</li>
<li>Choose one music track from a source with a clear licence, and note the licence in your project notes.</li>
<li>Lower the music volume so the voice is clearly understood.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Keep on-screen text large, short and high in contrast.</li>
<li>Captions make videos accessible and useful with sound off, but always correct them.</li>
<li>Use only music, images and clips you have permission or a licence to use.</li>
<li>Keep music well below the voice.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Colour, speed and exporting',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to make simple colour fixes, change clip speed and export a video in the right settings for each platform. This matters because a good edit can look poor or fail to upload if exported with the wrong settings.</p>
<h3>Colour basics</h3>
<p>You can fix many problems with a few basic controls, found under names like Color, Adjust, Filters or Inspector.</p>
<ul>
<li><strong>Exposure or brightness:</strong> makes the picture lighter or darker.</li>
<li><strong>Contrast:</strong> the difference between light and dark. A little more contrast adds punch.</li>
<li><strong>Saturation:</strong> how strong the colours are. Do not overdo it.</li>
<li><strong>White balance or temperature:</strong> corrects pictures that look too yellow or too blue. Find something that should be white and adjust until it looks neutral.</li>
</ul>
<p>Make small changes. Match the look of clips shot at different times so they feel like one video. Apply a single gentle filter to all clips, or none. Skin tones should look natural for everyone in the picture.</p>
<h3>Speed</h3>
<p>You can speed up long, dull actions (such as setting up a table) and slow down special moments. Look for a speed or duration control. Very fast speed changes can feel jumpy and low frame rate clips can look choppy when slowed down. Speech at changed speed sounds odd, so avoid speed changes on talking.</p>
<h3>Exporting</h3>
<p><strong>Exporting</strong> (also called rendering or sharing) turns your project into a single video file. Always export a <strong>new file</strong> and keep the project so you can edit again.</p>
<h3>Typical export settings</h3>
<table>
<thead><tr><th>Where it will go</th><th>Shape and size</th><th>Notes</th></tr></thead>
<tbody>
<tr><td>YouTube (standard)</td><td>16:9, 1920 x 1080, 30 fps, MP4 (H.264)</td><td>Good quality; upload can take time on slow internet</td></tr>
<tr><td>YouTube Shorts, TikTok, Reels, Status</td><td>9:16, 1080 x 1920, 30 fps, MP4</td><td>Check the platform's current maximum length and size</td></tr>
<tr><td>Facebook feed</td><td>4:5 or 1:1 or 16:9, 1080 wide, MP4</td><td>Captions are especially useful</td></tr>
<tr><td>WhatsApp sharing</td><td>720p or 1080p, short, compressed MP4</td><td>Small file size saves data and uploads faster</td></tr>
</tbody>
</table>
<p>These are common starting points, and each platform updates its guidance, so check the current help page. Choose MP4 with the H.264 codec when unsure, because almost every device and site accepts it.</p>
<h3>Steps to export</h3>
<ol>
<li>Play the whole video once, with headphones, to check picture and sound.</li>
<li>Open the export, share or deliver page of your editor.</li>
<li>Select MP4, the resolution and frame rate that match your plan, and a clear file name such as <code>ict-club-promo-v1-vertical.mp4</code>.</li>
<li>Export and wait.</li>
<li>Play the exported file on a phone to check captions, text and sound before publishing.</li>
</ol>
<h3>Try it yourself</h3>
<ol>
<li>Adjust brightness, contrast and white balance on one clip that looks too dark or yellow, and compare before and after.</li>
<li>Speed up one slow action by two times and check that it looks natural.</li>
<li>Export your video as an MP4 in the format for your chosen platform.</li>
<li>Watch the export on your phone and list anything that needs fixing.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Small colour corrections fix most problems; match clips to each other.</li>
<li>Use speed changes sparingly and avoid them on speech.</li>
<li>MP4 with H.264 is a safe export choice.</li>
<li>Always check the exported file on a phone before publishing.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does the timeline in a video editor do?
- Stores your passwords
* Arranges clips and sounds in order over time
- Only shows the file size
- Prints the video
= The timeline is where you build the story, with tracks for picture and sound.

Q: You cut a sentence out of an interview and the picture jumps. How can you hide the jump?
- Delete the whole interview
* Cover it with B-roll while the voice continues
- Add a spinning transition
- Turn off the audio
= A cutaway hides the jump while keeping the audio flowing.

Q: Which is the best choice for most transitions in a video?
- A different fancy effect every time
* A simple cut, with an occasional short dissolve
- A long spin
- No video at all
= Plain cuts feel natural, and extra effects distract.

Q: Why add captions? (choose all that apply)
* Many people watch with the sound off
* They help viewers who are deaf or hard of hearing
* They support viewers in noisy places
- They remove the need to plan a video
= Captions improve access, but automatic captions must be checked for errors.

Q: Can you use a famous song if you give credit in the description?
- Yes, credit always makes it legal
* No, copyright can still apply, so you need permission or a licence
- Yes, if the video is short
- Yes, on any platform
= Credit does not replace permission. Use licensed or free-to-use music.

Q: How loud should music be while someone is speaking?
- Louder than the voice
- The same as the voice
* Much quieter than the voice
- It must be off for the whole video
= Voice must be clear, so keep music low underneath.

Q: What is a safe default export for many platforms?
- A tiny GIF
* MP4 with H.264 at the platform's recommended size
- A screenshot
- A project file
= MP4 with H.264 is accepted almost everywhere, but check current platform guidance.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 4
        [
            'title'   => 'Module 4: Audio, graphics and polish',
            'summary' => 'Improve sound, add voice-over, thumbnails and branding, make videos accessible and keep files safe.',
            'lessons' => [
                [
                    'title'   => 'Cleaning up sound and voice-over',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to improve voice recordings, balance levels and record a clean voice-over. This matters because even a well-recorded video benefits from small audio fixes, and a good voice-over can turn silent footage into a story.</p>
<h3>Levels and loudness</h3>
<p>The audio meter in your editor shows how loud the sound is. Aim for a voice that sits comfortably in the green or yellow zone, peaking below the red. Red means distortion.</p>
<ul>
<li>Set the main voice at a steady, comfortable level.</li>
<li>Keep music and effects lower than the voice.</li>
<li>Make all clips about the same loudness so viewers do not keep changing the volume. Look for a "normalise" or "audio level" feature.</li>
<li>Listen on both headphones and a phone speaker, because many people watch on phones.</li>
</ul>
<h3>Noise reduction</h3>
<p>Some editors include noise reduction or "voice isolation" tools that lower hiss, hum or fans. Use them gently. Too much makes the voice sound robotic and watery. Prevention is better: choose a quiet place and place the microphone close.</p>
<ol>
<li>Select the audio clip.</li>
<li>Find the noise reduction or audio clean-up setting in the inspector or audio effects.</li>
<li>Start with a low amount and listen.</li>
<li>Increase only until the noise is acceptable and the voice still sounds natural.</li>
</ol>
<p>Free audio editors can also do this, but you can often manage with the tools already in your video editor.</p>
<h3>Other simple fixes</h3>
<ul>
<li><strong>Fades:</strong> short fades prevent clicks at cuts.</li>
<li><strong>Room tone:</strong> place the quiet room recording under gaps so silence does not suddenly become "dead".</li>
<li><strong>Cut breaths and long pauses</strong> only if it still sounds natural.</li>
</ul>
<h3>Recording a voice-over</h3>
<p>A <strong>voice-over</strong> is a recorded voice you add over pictures. It is useful when you cannot record clean sound while filming, or to explain a process.</p>
<ol>
<li>Write the script, and read it aloud first.</li>
<li>Find a quiet, soft room, such as a room with a mattress or a wardrobe of clothes around you. Some people record under a blanket.</li>
<li>Hold the phone or microphone about 15 to 20 centimetres from your mouth, slightly to the side to avoid breath popping.</li>
<li>Speak clearly, smile slightly and use a natural, friendly pace. Stand or sit upright.</li>
<li>Record in short sections. If you make a mistake, pause, then repeat the sentence. Cut the mistake out later.</li>
<li>Import the audio into your editor and place it under the pictures. Line up key words with matching visuals.</li>
</ol>
<p>If you speak in a language your audience prefers, add captions in that language. If you use a text-to-speech or AI voice, tell your audience when it matters, and never use a real person's voice without their permission.</p>
<h3>Try it yourself</h3>
<ol>
<li>Record a 20-second voice-over for your video in a quiet, soft room.</li>
<li>Import it and place it under your pictures.</li>
<li>Apply gentle noise reduction and compare before and after.</li>
<li>Balance voice and music, and listen on both headphones and phone speaker.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Keep voice clear and steady and music and effects lower, peaking below red.</li>
<li>Use noise reduction gently, and prevent noise at the source.</li>
<li>Record voice-over close to the mouth in a quiet, soft room.</li>
<li>Check your sound on a phone speaker.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Thumbnails, lower thirds and branding',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to design a clear thumbnail, add names with lower thirds and give your videos a consistent look. This matters because the thumbnail and title are what people see before deciding to watch, and consistent branding helps people recognise and trust your work.</p>
<h3>Thumbnails</h3>
<p>A <strong>thumbnail</strong> is the small preview picture that represents your video on YouTube and similar places. It should be clear even when very small.</p>
<ol>
<li><strong>Pick a strong picture.</strong> A clear face with an honest expression, or one interesting object. Take a still from the video at a bright, sharp moment, or take a special photo.</li>
<li><strong>Add a few words.</strong> Three to five big words at most, for example "Free ICT Classes". Do not repeat the whole title.</li>
<li><strong>Use strong contrast.</strong> Bright subject on a simpler background. Use a plain border or shadow behind text.</li>
<li><strong>Keep it honest.</strong> The thumbnail must match what the video really contains. Misleading "clickbait" loses trust and can break platform rules.</li>
<li><strong>Use the right size.</strong> For YouTube, 16:9, commonly 1280 x 720 pixels. Check the current requirements.</li>
</ol>
<p>Free design tools such as Canva have thumbnail templates. Always check the preview at small size on a phone.</p>
<h3>Lower thirds</h3>
<p>A <strong>lower third</strong> is the name label shown in the lower part of the screen when a person speaks, for example "Akello Grace, ICT Club learner". It tells viewers who is speaking.</p>
<ul>
<li>Show it for about 4 to 5 seconds, when the person first appears.</li>
<li>Use two lines at most: the name, then the role or place.</li>
<li>Keep it within the safe area and use readable colours.</li>
<li>Check spelling of names and use titles correctly. Do not publish extra details about children.</li>
<li>Many editors and Canva have ready-made lower third templates, which you can edit to match your colours.</li>
</ul>
<h3>Logos and branding</h3>
<p><strong>Branding</strong> means using the same look every time: colours, fonts, logo and style, so that viewers recognise the organisation.</p>
<table>
<thead><tr><th>Element</th><th>Advice</th></tr></thead>
<tbody>
<tr><td>Logo</td><td>Use a small, clear logo in a corner or at the end, not over faces. A PNG file with a transparent background works best</td></tr>
<tr><td>Colours</td><td>Choose two or three brand colours and use them in text, shapes and thumbnails</td></tr>
<tr><td>Fonts</td><td>Choose one for headings and one for body text</td></tr>
<tr><td>Intro and outro</td><td>A short intro of two to three seconds, if any, and an outro with the call to action and contact</td></tr>
<tr><td>Templates</td><td>Save your text styles and layouts so every video matches</td></tr>
</tbody>
</table>
<h3>Accessibility in design</h3>
<p>Keep strong contrast between text and background, avoid very small text, avoid flashing effects that can harm people with photosensitivity, and never rely on colour alone to give information. Always add captions.</p>
<h3>Try it yourself</h3>
<ol>
<li>Design a 16:9 thumbnail for your video with a clear picture and no more than five words.</li>
<li>View it at very small size on your phone and fix anything unreadable.</li>
<li>Add a lower third for one speaker, with two lines and a time of five seconds.</li>
<li>Write down your brand colours, fonts and logo placement on a small card.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A good thumbnail is simple, contrasting and honest.</li>
<li>Lower thirds show who is speaking, briefly and clearly.</li>
<li>Consistent colours, fonts and logo build recognition.</li>
<li>Design for accessibility with contrast, captions and no flashing.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Organising and backing up your files',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to organise video projects so that nothing gets lost and back them up safely. This matters because video files are large, projects need their original clips to open, and one phone loss or computer fault can wipe out weeks of work.</p>
<h3>A simple folder system</h3>
<p>Create one main folder per video project, with the same structure each time.</p>
<pre><code>2026-11_ict-club-promo/
  01_script-and-plan/
  02_raw-footage/
  03_audio/
  04_graphics-and-logos/
  05_project-files/
  06_exports/
  07_consent-forms/</code></pre>
<ul>
<li>Put <strong>raw footage</strong> in one place and never edit or rename the original files inside your editor's folders.</li>
<li>Use <strong>clear file names</strong> with dates and versions, such as <code>interview-grace-2026-11-05.mp4</code> and <code>promo-v2.mp4</code>. Avoid names like "final-final2".</li>
<li>Keep <strong>consent forms</strong> and music licence notes in the project folder, in a safe location.</li>
<li>Delete clearly useless clips (such as accidental recordings), but keep originals of anything you may reuse.</li>
</ul>
<h3>Why keeping files together matters</h3>
<p>Editors link to your media files rather than copying them inside the project. If you move or rename the folder, the project may show "media offline". Keep all media in the project folder from the start, and move the entire folder at once. Some editors can collect or copy media into one place.</p>
<h3>Storage space</h3>
<p>Video needs a lot of room. Move footage from your phone to a computer or external drive after each shoot. A phone that is almost full can stop recording or lose a clip. Check how much space a minute of video takes and plan for it.</p>
<h3>Backups for video</h3>
<p>Use the <strong>3-2-1 rule</strong>: three copies of important files, on two different kinds of storage, with one copy kept elsewhere.</p>
<ol>
<li><strong>Copy footage off the phone the same day</strong> to a computer or drive.</li>
<li><strong>Make a second copy</strong> on an external hard drive or flash disk.</li>
<li><strong>Keep one copy away from the first two</strong>, such as cloud storage, or a drive kept at a trusted person's home. Cloud free plans are often small, so store key projects and finished videos there.</li>
<li><strong>Back up the finished video and the project file</strong>, so you can edit again later.</li>
<li><strong>Check</strong> that the backups open.</li>
</ol>
<h3>Protect private material</h3>
<p>Raw footage may show children, faces and places. Protect it with a screen lock and password on your computer, store consent forms securely, and do not leave videos of children in open shared folders or public chat groups. Delete material you no longer need to keep, as agreed with the people filmed.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create the folder structure above for your current project.</li>
<li>Copy your footage from your phone into the raw footage folder.</li>
<li>Make a second backup on another drive, and decide where the off-site copy will be.</li>
<li>Rename three files with clear dates and version numbers.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Use the same folder structure and clear file names for every project.</li>
<li>Keep media together so the project does not lose its files.</li>
<li>Move footage off your phone and follow the 3-2-1 backup rule.</li>
<li>Protect footage and consent forms, especially images of children.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does a red meter on the audio level usually mean?
- The sound is perfect
* The sound is too loud and may be distorted
- The sound is muted
- The microphone is broken
= Peaks in red cause distortion called clipping.

Q: How should you use noise reduction?
- At the maximum for every clip
* Gently, checking that the voice still sounds natural
- Never, it is not possible
- Only on music
= Strong settings make the voice sound robotic, so use small amounts.

Q: Which are good tips for recording a voice-over? (choose all that apply)
* Use a quiet, soft room
* Keep the microphone close to your mouth
* Record in short sections and repeat after mistakes
- Record next to a running generator
= Quiet, close and in small sections gives clean, easy-to-edit audio.

Q: What is a good thumbnail?
- A blurry picture with ten lines of tiny text
* A clear, high-contrast image with a few big words that matches the video
- A misleading shocking image
- An unrelated celebrity photo
= Thumbnails should be readable at small size and honest about the content.

Q: What is a lower third?
- The last third of the video
* A name label shown in the lower part of the screen
- A background track
- A transition
= It tells viewers who is speaking.

Q: Why should you keep all project media in one folder?
- It makes the video prettier
* The editor links to files, so moved or renamed files can show as missing
- It is required by law
- It shortens the video
= Keeping media together prevents "media offline" problems.

Q: Which fits the 3-2-1 backup rule for your video project?
- One copy on the phone only
- Two copies on the same phone
* Three copies, on two kinds of storage, one kept away from the others
- A copy in a chat group
= The rule protects against loss, theft and device failure.
QUIZ,
        ],
        // ------------------------------------------------------------------ MODULE 5
        [
            'title'   => 'Module 5: Publishing and growing',
            'summary' => 'Publish well, learn from analytics, work as a team, stay honest and make a 60-second capstone video.',
            'lessons' => [
                [
                    'title'   => 'Publishing on social platforms',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to upload a video and write a title, description and hashtags that help the right people find it. This matters because a good video with a poor title and no description may never reach the audience it was made for.</p>
<h3>Before you publish: a quick check</h3>
<ol>
<li>Watch the final export on a phone with the sound on and off.</li>
<li>Check spelling of names, numbers, phone numbers and web links.</li>
<li>Check you have consent and rights for every person, picture and sound.</li>
<li>Make sure nothing private appears, such as ID cards, car number plates, house numbers or children's school names.</li>
</ol>
<h3>The platforms in simple terms</h3>
<table>
<thead><tr><th>Platform</th><th>Basics</th></tr></thead>
<tbody>
<tr><td>YouTube</td><td>Upload in YouTube Studio. Add a title, description, thumbnail, and choose whether it is for children (a legal requirement set by YouTube). Videos can be public, unlisted or private. Add chapters and captions where possible.</td></tr>
<tr><td>Facebook</td><td>Post as a video or a Reel on a page or profile. Write a short caption, add captions to the video, and choose the audience.</td></tr>
<tr><td>TikTok</td><td>Upload a vertical video, add a short description and hashtags, set who can view, comment and download.</td></tr>
<tr><td>WhatsApp Status or channel</td><td>Share short videos with your contacts or followers. Use a compressed file. People can save or forward, so avoid sensitive content.</td></tr>
</tbody>
</table>
<p>Menus and rules change often, so use the app's current screens and help pages.</p>
<h3>Titles</h3>
<ul>
<li>Say clearly what the viewer will get: "How to make a strong password in 60 seconds".</li>
<li>Put the main words first.</li>
<li>Keep it short and honest. No lies or exaggerations.</li>
</ul>
<h3>Descriptions</h3>
<p>Write one or two lines on what the video is about, then useful details: a link, a contact, a place and date for an event. On platforms that show only the start, put the most important words first. Include the call to action and a short credit for music and helpers.</p>
<h3>Hashtags and keywords</h3>
<p>Use a small number of relevant hashtags, such as three to five, for example #GuluYouth #ICTclub. Put the place and topic in them. Do not add unrelated trending tags just to get views. They can reduce trust and may break platform rules.</p>
<h3>Privacy and safety settings</h3>
<p>Choose who can view, comment, share and download. Turn on comment filters, and be ready to remove abusive comments. Never reveal children's details in titles, descriptions or comments. Use an organisation account with MFA and a strong password, shared safely with at least two trusted admins.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write a title of 60 characters or fewer for your video.</li>
<li>Write a two-line description with a call to action.</li>
<li>Choose three to five honest hashtags.</li>
<li>Upload as private or unlisted first, test it on a phone, then change the visibility when ready.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Check the video, facts, consent and rights before publishing.</li>
<li>Titles should be clear and honest, with key words first.</li>
<li>Descriptions carry the call to action, links and credits.</li>
<li>Protect accounts and privacy, and moderate comments.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Analytics, schedules and teamwork',
                    'minutes' => 13,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to read basic video statistics, keep a realistic posting schedule and share the work in a team. This matters because steady, improving content beats occasional bursts of effort, and teams get more done than one tired person.</p>
<h3>Reading analytics</h3>
<p><strong>Analytics</strong> are the statistics about how your videos perform. Each platform has its own page (for example YouTube Studio or the page insights on Facebook). Names change, but the main ideas are the same.</p>
<table>
<thead><tr><th>Measure</th><th>What it tells you</th><th>What to do</th></tr></thead>
<tbody>
<tr><td>Views and reach</td><td>How many people saw it</td><td>Compare videos; see which topics attract people</td></tr>
<tr><td>Watch time and retention</td><td>How long people watched and where they left</td><td>If many leave in the first seconds, improve the hook. If they leave at one point, cut or fix that part</td></tr>
<tr><td>Likes, comments and shares</td><td>How much people cared</td><td>Reply to comments and ask questions</td></tr>
<tr><td>Audience (age, place, device)</td><td>Who is really watching</td><td>Adjust language, topics and timing</td></tr>
<tr><td>Click or action rate</td><td>How many did your call to action</td><td>Make the action clearer and easier</td></tr>
</tbody>
</table>
<p>Do not chase numbers alone. Ten people who register for a class matter more than a thousand who scroll past. Match the measure to your goal.</p>
<h3>Learn and improve</h3>
<ol>
<li>After a week, review your top and bottom videos.</li>
<li>Ask: What was the topic, length, hook and thumbnail? What worked?</li>
<li>Try one change at a time and compare.</li>
<li>Ask your audience what they want next, through comments, polls or conversations.</li>
</ol>
<h3>A realistic posting schedule</h3>
<p>Consistency builds an audience, but a schedule you cannot keep causes burnout. For a small organisation, one good video every one or two weeks is better than a daily video that you cannot sustain.</p>
<ul>
<li>Choose a regular day and time when your audience is online. Use analytics to check.</li>
<li>Plan a month at a time with a simple content calendar: date, topic, format, who is responsible, status.</li>
<li>Batch your work: film several videos in one session, then edit them in another.</li>
<li>Reuse content: cut one long video into short clips with captions.</li>
</ul>
<h3>Working in a team</h3>
<table>
<thead><tr><th>Role</th><th>Job</th></tr></thead>
<tbody>
<tr><td>Producer or coordinator</td><td>Plans, schedules, gets permissions and keeps everyone on track</td></tr>
<tr><td>Presenter or storyteller</td><td>Speaks or appears on camera</td></tr>
<tr><td>Camera and sound</td><td>Shoots, checks audio, manages equipment</td></tr>
<tr><td>Editor</td><td>Edits, adds captions and exports</td></tr>
<tr><td>Reviewer</td><td>Checks facts, spelling, consent, safeguarding and message before publishing</td></tr>
</tbody>
</table>
<p>One person may do many roles, but name them. Agree on a simple approval step, such as sending the draft to a manager, and use shared folders and a task board. Give clear, kind feedback ("Please shorten the middle by ten seconds") and credit everyone who helped.</p>
<h3>Try it yourself</h3>
<ol>
<li>Create a one-month content calendar with four videos, giving each a date, topic and owner.</li>
<li>Choose two numbers from the analytics table that match your goal.</li>
<li>If you have published a video, write what you would change in the first 5 seconds.</li>
<li>List who will play each role in your team, and who will do the safety and consent review.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Choose analytics that match your goal, and use retention to improve hooks.</li>
<li>A steady schedule you can keep beats an ambitious one you cannot.</li>
<li>Batch your work, reuse content and plan a month ahead.</li>
<li>Name roles in a team, including a reviewer.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Ethics, honesty and monetisation',
                    'minutes' => 12,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to make honest, responsible videos and set realistic expectations about earning money from content. This matters because trust is the most valuable thing a creator or organisation has, and false hopes about quick income lead many young people to disappointment.</p>
<h3>Honesty in content</h3>
<ul>
<li><strong>Tell the truth.</strong> Check facts and numbers before you say them. Name your sources.</li>
<li><strong>Do not mislead with editing.</strong> Cutting a sentence so it changes its meaning is deception. Do not present staged scenes as real events, or old footage as new.</li>
<li><strong>Be clear about sponsorship.</strong> If a company paid you or gave you a gift, or you are promoting your own product, say so clearly.</li>
<li><strong>Be careful with edited and AI-generated media.</strong> Say when pictures, voices or scenes are generated or heavily altered, especially when they could be mistaken for real events. Never create fake videos of real people.</li>
<li><strong>Correct mistakes</strong> openly, pin a correction or update the description.</li>
</ul>
<h3>Respect and safety</h3>
<ul>
<li>Do not humiliate, bully or expose individuals. Do not film or share private moments, accidents or people in distress.</li>
<li>Avoid content that spreads hatred, rumours or dangerous advice, especially about health, elections or violence.</li>
<li>Protect children and vulnerable people (see Module 2). No publishing of identifying details.</li>
<li>Respect other creators. Ask before using their work and give credit where it is allowed.</li>
<li>Handle comments calmly. Remove abuse, and block or report people who threaten or harass.</li>
</ul>
<h3>Monetisation in general terms</h3>
<p><strong>Monetisation</strong> means earning money from your content. There are several ways: platform advertising programmes, sponsorships, selling services (such as filming events), selling your own products or training, and donations or grants for organisations.</p>
<p>It is important to be realistic:</p>
<ul>
<li>Platform programmes usually have <strong>minimum requirements</strong> (such as followers, watch hours and country eligibility) that change over time, and payment systems may not support every country. Always check the platform's current official rules.</li>
<li>Most creators earn little or nothing at first. Building an audience can take many months or years of regular work.</li>
<li>Many people earn better and sooner by using video skills to <strong>offer services</strong>, such as event videos, short promotional clips for local businesses, or editing for NGOs.</li>
<li>Be careful of anyone who asks you to pay to "unlock" earnings or promises quick riches. This is a common scam.</li>
<li>Never share your passwords or one-time codes with anyone claiming to help you earn.</li>
<li>Keep records of any income and learn about tax and registration rules that apply to you.</li>
</ul>
<h3>Think long term</h3>
<p>Video skills (planning, shooting, editing, storytelling) are valuable in marketing, journalism, education, NGOs and small business. Build a portfolio of your best three videos and keep learning.</p>
<h3>Try it yourself</h3>
<ol>
<li>Write five personal "creator rules" about honesty, consent and respect.</li>
<li>Check the current official monetisation requirements of one platform and write them down.</li>
<li>List three ways you could earn from video skills other than platform advertising.</li>
<li>Choose one video you made and write what to disclose to viewers, such as sponsors, staged scenes or edited elements.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>Trust is your most valuable asset, so be honest and open about edits and sponsors.</li>
<li>Respect people's dignity, privacy and safety.</li>
<li>Platform income is slow and uncertain, and rules change.</li>
<li>Services and a strong portfolio can be a more realistic path, and avoid scams.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Capstone: a 60-second NGO video from plan to publish',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will have a complete method for making a 60-second video for a non-governmental organisation (NGO), using everything in this course. This matters because a finished project in your portfolio proves your skill. The example is a video for a fictional youth ICT club called "Bright Futures ICT Club". Adapt it to a real organisation you can work with, with its permission.</p>
<h3>Step 1: Plan</h3>
<ul>
<li><strong>Audience:</strong> parents and young people aged 15 to 24 in the town, on WhatsApp and Facebook.</li>
<li><strong>Goal:</strong> get 40 young people to register for the free club.</li>
<li><strong>Core message:</strong> "The club is free, practical and starts on Monday."</li>
<li><strong>Call to action:</strong> "Register at the school office by Friday."</li>
<li><strong>Format:</strong> vertical 9:16, 60 seconds, with captions.</li>
</ul>
<h3>Step 2: Script and storyboard</h3>
<table>
<thead><tr><th>Time</th><th>Picture</th><th>Words or text</th></tr></thead>
<tbody>
<tr><td>0 to 5 s</td><td>Close-up of hands on a keyboard</td><td>"What if one skill could open new doors?"</td></tr>
<tr><td>5 to 20 s</td><td>Wide of the room, learners at laptops</td><td>"At Bright Futures ICT Club, young people learn to use computers, write CVs and stay safe online."</td></tr>
<tr><td>20 to 40 s</td><td>Short statement from a volunteer facilitator (with consent), and B-roll of practice</td><td>"It is practical, friendly and completely free."</td></tr>
<tr><td>40 to 55 s</td><td>Group shot of adults and volunteers (no learners identified without consent)</td><td>"Starting Monday for ages 15 to 24."</td></tr>
<tr><td>55 to 60 s</td><td>Logo and contact on a plain background</td><td>"Register at the school office by Friday."</td></tr>
</tbody>
</table>
<h3>Step 3: Prepare for filming</h3>
<ol>
<li>Get the organisation's permission and the school's permission.</li>
<li>Get written consent from every adult shown, and from parents or guardians for any child, following the organisation's child protection policy. Where you do not have consent, film hands, screens or backs only.</li>
<li>Choose a time with soft window light and a quiet room.</li>
<li>Charge the phone and bring a power bank, a tripod or stable support and a clip-on or earphone microphone.</li>
</ol>
<h3>Step 4: Shoot</h3>
<p>Film vertically at 1080p and 30 fps. Capture the shot list, including 10 seconds of every B-roll shot. Record the facilitator close to the microphone and listen with headphones. Record room tone.</p>
<h3>Step 5: Edit</h3>
<ol>
<li>Create a 9:16 project, import clips, and assemble the story in order.</li>
<li>Trim every clip, remove pauses and cut to 60 seconds.</li>
<li>Add text, a lower third for the speaker, the logo at the end and licensed music at low volume.</li>
<li>Add and correct captions, and check contrast.</li>
<li>Adjust colour lightly to match the clips and check the sound on headphones and phone.</li>
<li>Export as MP4 (H.264), vertical, and a smaller compressed copy for WhatsApp.</li>
</ol>
<h3>Step 6: Review and publish</h3>
<p>Ask a colleague to check facts, spelling, consent and safeguarding, and approve in writing. Then upload with a clear title, a two-line description, the call to action and three to five honest hashtags. Post at a time your audience is online, and reply to comments for the first day.</p>
<h3>Step 7: Measure and learn</h3>
<p>After one week, record views, retention, shares and, most importantly, registrations. Write three lessons and one change for the next video, and back up the project, the export and the consent forms.</p>
<h3>Try it yourself</h3>
<ol>
<li>Choose a real or imagined NGO and write the plan block in Step 1.</li>
<li>Complete the script table and a shot list.</li>
<li>Film, edit and export your 60-second video, using only permissions you have and free tools.</li>
<li>Collect feedback from three people and write what you would improve.</li>
</ol>
<h3>Key points</h3>
<ul>
<li>A good 60-second video starts with audience, goal and a single message.</li>
<li>Consent and safeguarding come before filming.</li>
<li>Clear sound, captions and a call to action make the video useful.</li>
<li>Measure, learn and back up everything.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What should you check before publishing a video? (choose all that apply)
* Names, numbers and links are correct
* You have consent and rights for people, pictures and music
* Nothing private appears, such as ID cards or children's school names
- That the video is exactly one hour long
= A final check protects people and prevents mistakes you cannot easily remove from the internet.

Q: What makes a good video title?
- A misleading shocking claim
* A short, clear and honest statement of what viewers will get
- The file name
- Only emojis
= Honest and clear titles build trust and help the right viewers.

Q: In analytics, what does a sharp drop in retention in the first seconds suggest?
- The thumbnail is perfect
* The hook needs improvement
- The music is licensed
- Nothing can be learned
= People are leaving early, so the opening needs to be stronger.

Q: Which posting schedule is better for a small organisation?
- A new video every hour
* A steady schedule you can actually keep, such as one video every one or two weeks
- Nothing for months, then 50 videos
- Whenever someone remembers
= Consistency you can sustain builds an audience without burning out the team.

Q: Which is honest practice? (choose all that apply)
* Telling viewers about sponsorship
* Correcting mistakes openly
* Saying when pictures or voices are generated or heavily altered
- Cutting a sentence so it changes its meaning
= Honesty protects trust. Editing to change meaning is deception.

Q: Someone offers to "unlock your earnings" if you pay a fee and share a one-time code. What should you do?
- Pay quickly
* Refuse, because it is likely a scam, and never share codes
- Share the code but not pay
- Tell them your password to prove you are real
= Never share passwords or one-time codes, and be careful of quick-income promises.

Q: In the NGO capstone, what must be arranged before filming?
- Nothing, just start
* Permissions and written consent, with guardian consent for children
- A music contract only
- A large crew
= Consent and safeguarding come first.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which is the best audience definition for a video?
- Everyone
* Parents of primary learners in Gulu who use WhatsApp
- Anyone with a phone
- People who like movies
= A specific audience guides every other choice.

Q: A video should usually have:
- Five goals and five messages
* One goal and one core message
- No goal
- A goal added after publishing
= Focus makes a video clear and memorable.

Q: What is the purpose of a hook?
- To end the video
* To give viewers a reason to keep watching in the first seconds
- To add copyright music
- To compress the file
= Viewers decide quickly whether to stay.

Q: About how many spoken words fit comfortably into a 60-second video?
* Up to about 130 to 150
- 500
- 800
- 20
= A natural speaking pace is about 130 to 150 words a minute.

Q: What is a shot list?
- The final script
* A checklist of the shots to capture, grouped by location
- A list of followers
- A set of hashtags
= It helps you film efficiently.

Q: Which are examples of B-roll? (choose all that apply)
* Hands working
* A close-up of a sign
* The surroundings of the event
- A voice-over file
= B-roll is supporting footage used to cover cuts and add detail.

Q: Which format suits TikTok and WhatsApp Status best?
- 16:9 landscape
* 9:16 vertical
- 21:9 ultra-wide
- Any, it makes no difference
= Vertical fills the phone screen.

Q: Why should you choose the aspect ratio before filming?
- It saves electricity
* It is hard to change the shape later without cropping or losing content
- Platforms insist on it
- It improves audio
= Planning the frame prevents poor crops.

Q: Which camera settings are a sensible default for most phone videos?
- 240p at 5 fps
* 1080p at 30 fps with the grid turned on
- Digital zoom at maximum
- Dark mode
= These settings give good quality without huge files.

Q: Where should you place a person when lighting their face with a window?
- With their back to the window
* Facing the window
- In a dark corner
- Under a lamp directly above
= Facing the window gives soft light on the face.

Q: What is the most important element in a video for keeping viewers?
- 4K resolution
* Clear audio
- Fast transitions
- A long intro
= People forgive poor pictures more easily than unclear sound.

Q: How can you improve voice recording most simply? (choose all that apply)
* Move the microphone closer to the mouth
* Choose a quiet, soft room
* Protect the microphone from wind
- Record next to traffic
= Closeness and a quiet place matter most.

Q: A parent has not signed consent for a child to appear. What should you do?
- Film anyway
* Do not show the child's identifiable face, or do not film at all
- Offer the child a gift
- Share the video with friends
= Children need guardian consent and protection.

Q: Which details should never be published about a child?
- Their first name only
* Full name, school, home area and phone number
- Their age group
- Their favourite colour
= Identifying details can put a child at risk.

Q: In an editor, the timeline is used to:
- Store your phone number
* Arrange clips and sounds in order over time
- Download music
- Only display the file size
= The timeline is where the story is built.

Q: What is a cutaway?
- A cut with an angry effect
* B-roll placed over a cut so the jump is hidden while the audio continues
- A deleted project
- A kind of music
= It makes edits feel smooth.

Q: Which is usually best between two clips?
- A different spinning transition every time
* A simple cut, with a short dissolve where time or place changes
- A five-second wipe
- No sound
= Plain cuts feel natural and professional.

Q: What should you do about automatic captions?
- Trust them completely
* Read and correct names, numbers and local words
- Delete them
- Hide them
= Automatic captions often make errors.

Q: Can you use any popular song as long as you credit the artist?
- Yes
* No, you need permission or a licence
- Yes, for videos under 60 seconds
- Yes, on WhatsApp only
= Credit does not replace the right to use a work.

Q: Which export is a safe default for many platforms?
- A project file
* MP4 with H.264 at the platform's recommended size
- A screenshot
- A zip file
= It is widely supported, but check current platform guidance.

Q: A red audio meter means:
* The sound is too loud and may be distorted
- The sound is perfect
- The sound is muted
- The video is private
= Red indicates clipping.

Q: What is the best way to use noise reduction?
- Maximum on every clip
* Gently, checking that the voice stays natural
- Never
- Only on music
= Over-processing makes voices sound robotic.

Q: A good thumbnail is:
- Misleading but exciting
* Clear and high-contrast, with a few big words, and matching the video
- Full of small text
- Unrelated to the content
= Honest, readable thumbnails build trust.

Q: Why keep project media in one folder?
- It looks neat only
* The editor links to the files, and moving them can break the project
- It reduces the video length
- It is not needed
= Keeping media together avoids "media offline" errors.

Q: Which follows the 3-2-1 backup rule?
- One copy on the phone
* Three copies on two kinds of storage, with one copy elsewhere
- A copy in a group chat
- Two copies on the same drive
= It protects against loss, theft and failure.

Q: What should a video description include? (choose all that apply)
* A short summary
* A call to action
* Contact or links where useful
- Unrelated trending hashtags
= Descriptions support viewers and search. Unrelated tags harm trust.

Q: Retention graphs show that most viewers leave in the first five seconds. What should you improve?
- The file name
* The hook and opening
- The ending only
- The copyright notice
= An early drop-off usually means the opening is not strong enough.

Q: Which statement about monetisation is correct?
- Everyone earns money quickly
* Programmes have requirements that change, and most creators earn little at first
- Platforms pay for every view
- You must pay to unlock earnings
= Expect slow progress and check official rules. Fee-based "unlocking" is a scam sign.

Q: Which is a more realistic way for a beginner to earn from video skills?
* Offering event videos or short clips to local businesses and NGOs
- Buying followers
- Sharing your password with agents
- Copying others' videos
= Services build skill and income without relying on platform programmes.

Q: What is the first thing to arrange for the capstone NGO video?
- Music
* Permissions and consent, including guardian consent for children
- A new camera
- Hashtags
= Safe, consenting participation comes before filming.
QUIZ,
];
