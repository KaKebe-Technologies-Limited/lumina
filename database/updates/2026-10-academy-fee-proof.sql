-- Academy sign-up fee: the learner reports a mobile-money / bank / Daraza payment (reference + optional proof). phpMyAdmin: select the Lumina database > Import > choose this file > Go. Safe to run more than once.
-- Run it AFTER 2026-10-academy-fees.sql.
SET NAMES utf8mb4;

-- Academy sign-up fee paid by mobile money / bank / Daraza: the learner reports the payment (transaction ID + optional proof), an administrator confirms it.
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'fee_reference');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_learners` ADD COLUMN `fee_reference` VARCHAR(120) NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'fee_proof_path');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_learners` ADD COLUMN `fee_proof_path` VARCHAR(190) NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'fee_reported_at');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_learners` ADD COLUMN `fee_reported_at` DATETIME NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
