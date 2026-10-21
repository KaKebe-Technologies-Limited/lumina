<?php
/** Starter INNOVATION CHALLENGE (judged). Added as a draft without dates: set the dates in Admin > LAF Academy > Challenges, then publish. */
return [
    'slug' => 'youth-innovation-challenge', 'kind' => 'innovation', 'title' => 'Youth Innovation Challenge', 'category' => 'careers',
    'summary' => 'Turn an idea into a digital solution for a real problem in your school, community or small business, and present it to our judges.',
    'description' => "Technology is most useful when it solves a problem people really have. In this challenge you choose a problem around you, such as keeping school records, helping farmers reach buyers, sharing health information or making a small shop more organised, and design a digital solution for it.\n\nYou can work alone or in a small team. You do not need to build a finished app: a clear idea, a working prototype, a slide deck or a short video can all score well. Judges look at how original and useful your idea is and how well you explain it.",
    'rules' => "Open to registered LAF Academy learners whose entry is approved.\nWork alone or in a team of up to 3 people. One entry per learner.\nYour project must be your own work. Say clearly which tools, templates or AI assistants you used.\nSubmit a project title, a description and a link or a file (PDF, Word or PowerPoint) before the challenge ends.\nYou can update your submission until the end of the challenge.\nJudges score every project against the published criteria and the system averages their scores.\nThe highest total wins. Ties are broken by the earlier submission.\nWinners are published as Winners of the Month and receive a certificate of achievement. Other qualifying entrants receive a certificate of participation.\nThe decision of the judges and the system ranking is final.",
    'prize' => 'A feature as Winner of the Month on the LAF Academy, a certificate of achievement, and a place on the Wall of Winners.',
    'team_max' => 3, 'approval' => 'auto', 'winners_count' => 3, 'pass_score' => 40,
    'criteria' => [
        ['name' => 'Innovation and creativity', 'weight' => 30],
        ['name' => 'Impact and usefulness to real people', 'weight' => 30],
        ['name' => 'Feasibility and quality of the work', 'weight' => 25],
        ['name' => 'Presentation and clarity', 'weight' => 15],
    ],
];
