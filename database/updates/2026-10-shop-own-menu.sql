-- Shop becomes an independent top-level menu item (it was a dropdown under the Donate button). Safe to import more than once; a Shop you already moved or hidden in Admin > Menus is left alone.
-- ONE-TIME menu upgrade v2 (own marker): Shop was a child of Donate (a dropdown under the Donate button). It becomes an independent top-level menu item, but ONLY while its menu
-- fields still hold what the first version set (child of Donate, order 96, in the header): a Shop the owner has already moved or hidden in Admin > Menus is left alone.
UPDATE pages SET parent_slug = NULL, nav_order = 80, updated_at = updated_at
WHERE slug = 'shop' AND parent_slug = 'donate' AND nav_order = 96 AND show_in_nav = 1
  AND NOT EXISTS (SELECT 1 FROM (SELECT setting_key FROM settings WHERE setting_key = 'migration_shop_nav_v2') m);
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('migration_shop_nav_v2', '1');
