-- Data protection framework: data-subject requests (access, correction, deletion, objection, withdrawing consent, complaints) are stored as
-- contact messages of kind 'dsr' with a reference number and a response target date; consent times for newsletter-like opt-ins.
-- phpMyAdmin: select the Lumina database > Import > choose this file > Go. Safe to run more than once. Changes no existing data.
SET NAMES utf8mb4;

SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'contact_messages' AND column_name = 'kind');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `contact_messages` ADD COLUMN `kind` VARCHAR(12) NOT NULL DEFAULT ''message'', ADD COLUMN `reference` VARCHAR(24) NULL, ADD COLUMN `request_type` VARCHAR(24) NULL, ADD COLUMN `due_at` DATE NULL, ADD KEY `ix_contact_kind` (`kind`, `status`, `due_at`), ADD KEY `ix_contact_reference` (`reference`)', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
