<?php
/** Starter HACKATHON (judged). Added as a draft without dates: set the dates in Admin > LAF Academy > Challenges, then publish. */
return [
    'slug' => 'community-tech-hackathon', 'kind' => 'hackathon', 'title' => 'Community Tech Hackathon', 'category' => 'coding',
    'summary' => 'A timed team event: build a working digital solution for a community problem and show it to the judges.',
    'description' => "A hackathon is a short, focused event where teams design and build something useful. In this hackathon, teams pick one theme announced at the start, plan their solution, build a working prototype and submit it before the deadline.\n\nYou do not have to be a programmer to join. Designers, writers, data people and organisers are welcome, and every good team has a mix of skills. Use any tools you like, including no-code tools, spreadsheets and websites.",
    'rules' => "Open to registered LAF Academy learners whose entry is approved.\nTeams of 1 to 4 people. One entry per learner.\nThe theme is announced on the challenge page when the hackathon starts.\nBuild during the event window. You may use open-source tools and free services; say what you used.\nSubmit a title, a description, and a link to your prototype or code (or upload a PDF, Word or PowerPoint file) before the end.\nYou can update your submission until the hackathon ends.\nJudges score every project against the published criteria and the system averages their scores.\nThe highest total wins. Ties are broken by the earlier submission.\nWinners are published as Winners of the Month and receive a certificate of achievement. Other qualifying teams receive a certificate of participation.\nBe respectful. Plagiarism or copying another team's work leads to disqualification.",
    'prize' => 'A feature as Winner of the Month on the LAF Academy, certificates of achievement for the winning team, and a place on the Wall of Winners.',
    'team_max' => 4, 'approval' => 'auto', 'winners_count' => 3, 'pass_score' => 40,
    'criteria' => [
        ['name' => 'Working prototype and technical quality', 'weight' => 35],
        ['name' => 'Impact on the community', 'weight' => 25],
        ['name' => 'Creativity and originality', 'weight' => 20],
        ['name' => 'Teamwork, presentation and documentation', 'weight' => 20],
    ],
];
