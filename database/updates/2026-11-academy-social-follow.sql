-- LAF Academy update: SOCIAL FOLLOW CHECK. Keeps, for each new learner, the usernames they gave on our social media pages and which of our pages they opened from the sign-up form,
-- so the Academy team can look them up in the followers lists and mark the learner as verified (Admin > LAF Academy > Social follow check).
-- phpMyAdmin: select the Lumina database > Import > choose this file > Go. Safe to run more than once. Changes no existing data.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `academy_social` (
  `learner_id` INT UNSIGNED NOT NULL,
  `handles` TEXT NULL,                                -- JSON: platform => username (instagram, tiktok, x, facebook, youtube, linkedin)
  `opened` TEXT NULL,                                 -- JSON list of the pages opened from the sign-up form
  `verified_at` DATETIME NULL,                        -- set by the team after looking the learner up
  `verified_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`learner_id`),
  KEY `ix_academy_social_verified` (`verified_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
