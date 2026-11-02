-- LAF Academy update: FEATURED STUDENTS. An administrator can feature a learner on the public Academy page (Admin > LAF Academy > Learners > the learner > "Feature on the website").
-- phpMyAdmin: select the Lumina database > Import > choose this file > Go. Safe to run more than once. Changes no existing data.
SET NAMES utf8mb4;

SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'featured_at');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_learners` ADD COLUMN `featured_at` DATETIME NULL, ADD COLUMN `featured_note` VARCHAR(400) NULL, ADD COLUMN `featured_by` INT UNSIGNED NULL, ADD KEY `idx_academy_learners_featured` (`featured_at`)', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
