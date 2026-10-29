-- Founder photo: the board card of Moses Komakech uses the photo that ships with the site (assets/img/site/photo-founder-tall.jpg), so it
-- never depends on an uploaded file. phpMyAdmin: select the Lumina database > Import > choose this file > Go. Safe to run more than once.
UPDATE `team_members` SET `photo` = 'assets/img/site/photo-founder-tall.jpg' WHERE `name` = 'Moses Komakech' AND `role_title` = 'Founder';
