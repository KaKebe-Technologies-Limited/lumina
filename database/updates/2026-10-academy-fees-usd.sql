-- LAF Academy fees in US dollars: sign-up fee USD 3, certificate fee USD 2 (charged when a learner completes and passes a course).
-- The same two values can be set in Admin > Pesapal payments > LAF Academy fees. Importing this file does exactly that. Safe to import more than once.
INSERT INTO settings (setting_key, setting_value) VALUES ('academy_signup_fee', '3') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
INSERT INTO settings (setting_key, setting_value) VALUES ('academy_signup_fee_currency', 'USD') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
INSERT INTO settings (setting_key, setting_value) VALUES ('academy_cert_fee', '2') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
INSERT INTO settings (setting_key, setting_value) VALUES ('academy_cert_fee_currency', 'USD') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
