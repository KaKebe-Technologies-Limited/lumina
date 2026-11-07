<?php
declare(strict_types=1);

/**
 * The Northern Uganda story: the background of the conflict and what it left behind. One source for two places: the short flow on the About page (partials/nu-flow.php,
 * the "short" text of each chapter) and the full interactive page /northern-uganda-story (the "story", "facts" and "deeper" text).
 *
 * Every figure below comes from a published source (listed in "sources"); where sources differ the wording says so ("at least", "an estimated"). Photos are the
 * foundation's own (assets/img/site). Nothing here is invented: when a detail cannot be sourced it is left out.
 */
return [
    'chapters' => [
        [
            'key' => 'lakwena', 'year' => '1986', 'tag' => 'A prophetess and a war', 'icon' => 'flame', 'tone' => 'past',
            'title' => 'The war begins with a prophetess',
            'short' => 'Alice Lakwena, an Acholi woman, leads the Holy Spirit Movement against the government. Her army is defeated in late 1986, and she flees to Kenya.',
            'story' => [
                'In 1985 an Acholi woman named Alice Lakwena began to preach that she was guided by spirits and sent to cleanse her people. Out of that belief grew the Holy Spirit Movement, an armed force that marched against the government of Uganda.',
                'By the end of 1986 her fighters were routed, only about 100 kilometres from Kampala, and she fled to Kenya. The movement was broken. But the pain, the fear and the weapons it left behind in the north were not.',
            ],
            'facts' => ['1985: the Holy Spirit Movement is formed', 'Late 1986: defeated; Lakwena flees to Kenya'],
            'deeper' => 'Many Acholi families were already living through the aftermath of years of political turmoil. The collapse of Lakwena\'s movement left a generation of young fighters, and a region that felt abandoned, searching for what would come next.',
            'image' => '', 'image_alt' => '',
        ],
        [
            'key' => 'kony', 'year' => '1987', 'tag' => 'A rebel army is born', 'icon' => 'shield', 'tone' => 'past',
            'title' => 'Joseph Kony gathers what is left',
            'short' => 'Joseph Kony takes up Lakwena\'s spiritual claim and builds the Lord\'s Resistance Army, an army made up largely of abducted villagers.',
            'story' => [
                'Joseph Kony, a young man from the same Acholi region, claimed Alice Lakwena\'s spiritual inheritance and gathered the remnants of her movement. In time it became known as the Lord\'s Resistance Army, the LRA.',
                'It was an army that grew not by recruiting, but by taking: Kony drew his forces largely from abducted villagers, above all children. A boy or girl would be taken from home and forced to fight, to carry, to obey. For many it meant being made to hurt the very people they loved.',
            ],
            'facts' => ['1987: Kony builds the LRA from the remnants of the movement', 'August 1991: the first large-scale abduction of children, in Kitgum'],
            'deeper' => 'Sources differ on exactly when Kony took command (1986 or 1987), which is why historians describe it as a gradual takeover. What is not in doubt is what followed: nearly two decades of attacks on villages, schools and roads across the north.',
            'image' => '', 'image_alt' => '',
        ],
        [
            'key' => 'children', 'year' => '1987 - 2006', 'tag' => 'Stolen childhoods', 'icon' => 'users', 'tone' => 'past',
            'title' => 'Stolen childhoods',
            'short' => 'UNICEF estimates at least 25,000 children were abducted; some estimates go above 30,000. Many more children lived in fear every night.',
            'story' => [
                'The numbers are hard to hold in your mind: UNICEF\'s estimate is that at least 25,000 children were abducted, and other estimates are higher still. Every one of them was somebody\'s son or daughter, taken from a normal evening.',
                'Those who were not taken lived in fear. In towns such as Gulu and Kitgum, children walked in from the villages every evening to sleep in places that felt safer, then walked home at dawn. School was something you tried to hold on to, between the danger.',
            ],
            'facts' => ['At least 25,000 children abducted (UNICEF)', 'Some estimates exceed 30,000', 'October 2005: the ICC issues arrest warrants for Kony and four other LRA commanders'],
            'deeper' => 'In October 2005 the International Criminal Court issued its first-ever arrest warrants, for Joseph Kony and four other LRA commanders. They have never been brought to trial. For the children who came home, the long work of healing, and of catching up on years of school, was only starting.',
            'image' => 'assets/img/site/photo-02.jpg', 'image_alt' => 'A crowd of young children, in black and white, looking into the camera',
        ],
        [
            'key' => 'camps', 'year' => '1990s - 2006', 'tag' => 'Life in the camps', 'icon' => 'home', 'tone' => 'past',
            'title' => 'Home becomes a camp',
            'short' => 'More than 1.8 million people, close to 1.9 million by mid-2005, were forced into displacement camps: no farms, no schools, little health care.',
            'story' => [
                'To protect people, and to separate them from the rebels, communities were moved into camps. At the height of the conflict, more than 1.8 million people in northern Uganda, close to 1.9 million by mid-2005, were living displaced, 1.1 million of them in the three Acholi districts alone.',
                'A camp was not a home. Families lost their farms and their income. Huts were crowded together, sanitation was poor, clinics were far or empty. In February 2004 nearly 30,000 people were displaced from Pabbo camp when 4,000 huts burned. A child could grow up knowing nothing else.',
            ],
            'facts' => ['More than 1.8 million displaced at the peak (about 1.9 million by mid-2005)', '1.1 million in the three Acholi districts', 'Feb 2004: Pabbo camp, 4,000 huts burned'],
            'deeper' => 'Hunger and crowding do lasting damage to a child\'s body as well as to their schooling. Malnutrition in the first years of life can stunt growth for good. Even today, about 27% of Ugandan children under five are stunted (2016 Demographic and Health Survey), down from 41% in 2000, and the burden has fallen hardest on the poorest communities.',
            'image' => 'assets/img/site/photo-08.jpg', 'image_alt' => 'A young child holding a sign that says HOPE',
        ],
        [
            'key' => 'schools', 'year' => 'The cost', 'tag' => 'Lost years of school and health', 'icon' => 'book-open', 'tone' => 'cost',
            'title' => 'A generation that lost its school years',
            'short' => 'Classrooms emptied. Today most household heads in Acholi and Lango have not completed primary school, and girls drop out the most.',
            'story' => [
                'Peace did not give those years back. Today, a representative survey of Acholi and Lango found that most household heads, 53.5 percent, had not completed primary school. Parents who missed their own education are less able to pay for their children\'s.',
                'The gap shows up in classrooms. Researchers found primary school attendance in Lango and Acholi fell by roughly a fifth between 2013 and 2018, with girls the most likely to leave, because of early marriage, the cost of school and, too often, no clean latrines or sanitary products.',
            ],
            'facts' => ['53.5% of household heads in Acholi and Lango did not complete primary school', 'Primary attendance fell by about a fifth, 2013 to 2018', 'Girls drop out most, from upper primary onward'],
            'deeper' => 'These are the two goals the world agreed to for every child: good health and well-being (Sustainable Development Goal 3) and quality education (SDG 4). Northern Uganda is where those goals are hardest to reach, and where a small amount of support goes furthest.',
            'image' => 'assets/img/site/mentorship/lacekocot-0270.jpg', 'image_alt' => 'Young people gathered to learn together under the trees',
        ],
        [
            'key' => 'peace', 'year' => '2006', 'tag' => 'The guns fall quiet', 'icon' => 'sun', 'tone' => 'hope',
            'title' => 'The guns fall quiet. The work begins.',
            'short' => 'A cessation of hostilities was signed in Juba in August 2006. Large-scale fighting in Uganda ended and hundreds of thousands began to go home.',
            'story' => [
                'On 26 August 2006 a cessation of hostilities was signed in Juba. The LRA moved out of northern Uganda, and large-scale fighting inside the country ended. Talks went on for two more years, but a final peace agreement was never signed.',
                'What mattered most to families was simple: they could go home. Hundreds of thousands of people began to leave the camps, to clear land, rebuild homes and send their children to school again. The war was over. The rebuilding had only started.',
            ],
            'facts' => ['26 Aug 2006: cessation of hostilities signed in Juba', 'Hundreds of thousands begin returning home'],
            'deeper' => 'Rebuilding a region is slower than winning a battle. Schools needed teachers and books, clinics needed staff and medicine, and a whole generation of young adults needed the skills and the second chance that the war had taken from them.',
            'image' => 'assets/img/site/mentorship/lacekocot-0601.jpg', 'image_alt' => 'Pupils and community members gathered in front of a school in Gulu',
        ],
        [
            'key' => 'today', 'year' => 'Today', 'tag' => 'A new gap, and a new chance', 'icon' => 'lightbulb', 'tone' => 'hope',
            'title' => 'The new gap, and a new chance',
            'short' => 'The world has gone digital, and the young people who lost the most are furthest from computers, skills and the internet. This is where Lumina begins.',
            'story' => [
                'The world moved on while the north was rebuilding, and it moved online. Jobs, learning, banking and business now run on computers and the internet. A young person in Northern Uganda who has never touched a computer is being left behind a second time.',
                'That is why Lumina Africa Foundation exists. We were founded by young Africans from this region who refuse to let a generation be defined by what the war took. A computer, a trainer and a year of skills can open a door that the conflict closed.',
            ],
            'facts' => ['Digital skills are now a gateway to work and learning', 'Founded to close the digital gap for the youth of Northern Uganda'],
            'deeper' => 'You can step into this story. Fund a whole programme, a launch package or a single contribution such as a laptop, a scholarship or a clinic day, and we will report back to you on exactly what it did.',
            'image' => 'assets/img/site/photo-03.jpg', 'image_alt' => 'A trainer points at a screen while young people work at computers',
        ],
    ],
    'sources' => [
        ['Human Rights Watch: The Lord\'s Resistance Army and the war in northern Uganda (2003)', 'https://www.hrw.org/reports/2003/uganda0703/uganda0703a-03.htm'],
        ['Human Rights Watch: Uprooted and Forgotten, displacement in northern Uganda (2005)', 'https://www.hrw.org/reports/2005/uganda0905/4.htm'],
        ['The New Humanitarian: the first mass abduction of children, Kitgum, 1991', 'https://www.thenewhumanitarian.org/node/222387'],
        ['The New Humanitarian: Pabbo camp, February 2004', 'https://www.thenewhumanitarian.org/node/219744'],
        ['Tufts Feinstein International Center: Schools Out, why northern Uganda\'s girls and boys are not getting an education', 'https://fic.tufts.edu/publication-item/schools-out-education-dropout/'],
        ['Uganda Demographic and Health Survey 2016 (stunting)', 'https://catalog.ihsn.org/catalog/7389'],
        ['2006 to 2008 Juba talks and the cessation of hostilities', 'https://en.wikipedia.org/wiki/2006%E2%80%932008_Juba_talks'],
    ],
];
