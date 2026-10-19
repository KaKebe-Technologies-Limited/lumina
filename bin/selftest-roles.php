<?php
declare(strict_types=1);

/** Roles and permissions checks (required by bin/selftest.php: check() and the bootstrap are already loaded). */

use App\Core\Database;
use App\Core\Roles;

Roles::flush();
check('roles: built-in roles exist', Roles::exists('admin') && Roles::exists('editor'));
check('roles: administrator holds every known ability', count(Roles::abilitiesFor('admin')) === count(Roles::knownAbilities()) && Roles::can('admin', 'roles.manage'));
check('roles: editor keeps content but not users/roles/settings', Roles::can('editor', 'content.manage') && !Roles::can('editor', 'users.manage') && !Roles::can('editor', 'roles.manage') && !Roles::can('editor', 'settings.manage'));
check('roles: unknown role and empty role hold nothing', !Roles::can('nope', 'dashboard.view') && !Roles::can(null, 'dashboard.view') && !Roles::can('', 'dashboard.view'));

// a custom role that (wrongly) stores admin-only abilities still cannot use them
$slug = 'zz-selftest-role';
Database::execute('DELETE FROM roles WHERE slug = ?', [$slug]);
Database::insert('roles', ['slug' => $slug, 'name' => 'ZZ selftest', 'description' => '', 'abilities' => json_encode(['users.manage', 'roles.manage', 'inbox.manage', 'bogus.ability']), 'is_system' => 0]);
Roles::flush();
check('roles: stored admin-only and unknown abilities are ignored', !Roles::can($slug, 'users.manage') && !Roles::can($slug, 'roles.manage') && !Roles::can($slug, 'bogus.ability'));
check('roles: stored assignable ability works and dashboard.view is always present', Roles::can($slug, 'inbox.manage') && Roles::can($slug, 'dashboard.view') && !Roles::can($slug, 'content.manage'));
check('roles: clean() keeps only assignable known abilities', Roles::clean(['inbox.manage', 'users.manage', 'x', 5, 'inbox.manage']) === ['inbox.manage']);
check('roles: slug generator avoids collisions', Roles::makeSlug('Editor') !== 'editor' && Roles::makeSlug('ZZ selftest') !== $slug);
check('roles: role options list includes the custom role', isset(Roles::options()[$slug]));
Database::execute('DELETE FROM roles WHERE slug = ?', [$slug]);
Roles::flush();
check('roles: removed role holds nothing', !Roles::can($slug, 'dashboard.view'));
