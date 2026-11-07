-- Puts "Projects" before "Programs" in the main menu and footer. Safe to run more than once.
UPDATE pages SET nav_order = 30 WHERE slug = 'projects';
UPDATE pages SET nav_order = 40 WHERE slug = 'programs';
