-- LAF Academy update: EXAM SECURITY. Every final exam attempt now has a server-side time limit (start, deadline), autosaved answers, a "one open tab only" lock,
-- a log of the times the learner left the exam page, and a "flagged" state for attempts that were stopped for leaving too often. Also: grants of a retake / extra time.
-- phpMyAdmin: select the Lumina database > Import > choose this file > Go. Safe to run more than once. Changes no existing data (old attempts keep working).
SET NAMES utf8mb4;

SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_attempts' AND column_name = 'deadline_at');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_attempts` ADD COLUMN `deadline_at` DATETIME NULL, ADD COLUMN `time_limit_min` SMALLINT UNSIGNED NULL, ADD COLUMN `extra_minutes` SMALLINT UNSIGNED NOT NULL DEFAULT 0, ADD COLUMN `secured` TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN `agreed_at` DATETIME NULL, ADD COLUMN `token` CHAR(32) NULL, ADD COLUMN `heartbeat_at` DATETIME NULL, ADD COLUMN `saved_answers` MEDIUMTEXT NULL, ADD COLUMN `saved_at` DATETIME NULL, ADD COLUMN `leave_count` SMALLINT UNSIGNED NOT NULL DEFAULT 0, ADD COLUMN `flagged` TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN `flag_review` VARCHAR(12) NULL, ADD COLUMN `flag_reviewed_by` INT UNSIGNED NULL, ADD COLUMN `flag_reviewed_at` DATETIME NULL, ADD COLUMN `submit_reason` VARCHAR(20) NULL, ADD KEY `ix_academy_attempts_flagged` (`flagged`, `flag_review`)', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

-- Every time the learner left the exam page (or another noteworthy moment), with the time it happened.
CREATE TABLE IF NOT EXISTS `academy_exam_events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `attempt_id` INT UNSIGNED NOT NULL,
  `kind` VARCHAR(20) NOT NULL,                                    -- leave, return, blur, pagehide, fullscreen_exit, copy_blocked, resumed, second_tab, auto_submit
  `counted` TINYINT(1) NOT NULL DEFAULT 0,                        -- 1 = counted towards the leave limit
  `seconds_away` SMALLINT UNSIGNED NULL,
  `detail` VARCHAR(190) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_academy_exam_events_attempt` (`attempt_id`, `id`),
  CONSTRAINT `fk_academy_exam_events_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `academy_attempts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- An administrator lets one learner retake an exam (extra attempts) and/or gives extra minutes. Used up by the learner's next attempt (extra minutes also extend an open attempt at once).
CREATE TABLE IF NOT EXISTS `academy_exam_grants` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `learner_id` INT UNSIGNED NOT NULL,
  `quiz_id` INT UNSIGNED NOT NULL,
  `extra_attempts` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `extra_minutes` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `note` VARCHAR(190) NULL,
  `granted_by` INT UNSIGNED NULL,
  `used_attempt_id` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_academy_exam_grants_learner` (`learner_id`, `quiz_id`, `used_attempt_id`),
  CONSTRAINT `fk_academy_exam_grants_learner` FOREIGN KEY (`learner_id`) REFERENCES `academy_learners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_academy_exam_grants_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `academy_quizzes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
