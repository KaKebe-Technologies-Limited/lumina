<?php
declare(strict_types=1);

/**
 * The website LAUNCH pop-up (partials/launch-popup.php, assets/js/launch.js): shown once to each visitor on their first visit, with a countdown to the launch moment and a burst of
 * balloons. It disappears by itself: after "ends_at" the server stops printing it. Change the dates here (any strtotime format; East Africa Time is UTC+3), or set "enabled" to false.
 */
return [
    'enabled'   => false,                                 // the launch is over: the pop-up (and the pre-launch lock) are switched off. Set true to bring them back
    'gate'      => true,                                  // until launch_at the WHOLE site is closed: visitors see only this pop-up (admin and signed-in staff are not affected)
    'launch_at' => '2026-11-06 10:00:00 +03:00',        // the countdown runs to this moment: 10:00 AM in Gulu (East Africa Time)
    'ends_at'   => '2026-11-07 10:00:00 +03:00',        // 24 hours after the launch: the pop-up is no longer printed
    'image'     => 'assets/img/site/mentorship/lacekocot-0611.jpg',   // a photo from the gallery
    'image_alt' => 'Pupils, mentors and guests gathered in front of Lacekocot Primary School in Gulu',
    'title'     => 'WE ARE LIVE! | THE LUMINA AFRICA FOUNDATION WEBSITE IS OFFICIALLY LAUNCHED!',
    'site_url'  => 'https://www.luminaafricafoundation.com/',
    'paragraphs' => [
        'A new chapter in our journey of igniting light and inspiring change begins today!',
        'We are excited to officially unveil the Lumina Africa Foundation website, your gateway to discovering our work, programmes, opportunities, impact, and commitment to transforming communities across Africa.',
        'Through this platform, we are making it easier for young people, partners, stakeholders, volunteers, and supporters to connect with our mission and become part of the change we envision.',
        'From digital literacy and education to youth empowerment, employability, innovation, and community development, our work is driven by one belief: every young person deserves an opportunity to learn, grow, and thrive.',
    ],
    'explore'   => 'Explore our website: ',
    'invite'    => 'We invite development partners, organisations, institutions, private sector players, and individuals to explore opportunities for collaboration and partnership as we work together to create lasting impact.',
    'closing'   => 'This is more than a website. It is a digital home for our mission, our stories, our opportunities, and the communities we serve.',
    'signature' => ['Lumina Africa Foundation', 'Igniting Light. Inspiring Change.'],
    'hashtags'  => ['#LuminaAfricaFoundation', '#OfficialLaunch', '#WebsiteLaunch', '#YouthEmpowerment', '#DigitalInclusion', '#EducationForAll', '#PartnershipsForImpact', '#IgnitingLightInspiringChange'],
];
