<?php
/**
 * Central configuration. Read with config('app.debug') or Config::get('db.host').
 * Values come from .env (see .env.example). Do not put secrets in this file.
 */

use App\Core\Env;

$env = Env::get('APP_ENV', 'production');
$mailDriver = Env::get('MAIL_DRIVER', '');
if ($mailDriver === null || $mailDriver === '') {
    $mailDriver = ($env === 'local') ? 'file' : 'mail';
}

return [
    'app' => [
        'name'        => 'Lumina Africa Foundation',
        'env'         => $env,
        'debug'       => Env::bool('APP_DEBUG', false),
        'maintenance' => Env::bool('APP_MAINTENANCE', false),
        'url'         => rtrim((string) Env::get('APP_URL', ''), '/'),
        'timezone'    => Env::get('APP_TIMEZONE', 'Africa/Kampala'),
        'key'         => Env::get('APP_KEY', ''),
        'install_token' => (string) Env::get('INSTALL_TOKEN', ''),     // enables /install (first admin without a shell) while no user exists
        'locale'      => 'en',
    ],
    'db' => [
        'host'    => Env::get('DB_HOST', '127.0.0.1'),
        'port'    => Env::int('DB_PORT', 3306),
        'name'    => Env::get('DB_NAME', 'lumina'),
        'user'    => Env::get('DB_USER', 'root'),
        'pass'    => Env::get('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],
    'session' => [
        'name'     => 'lumina_session',
        'lifetime' => 0,        // browser-session cookie
        'idle'     => 7200,     // seconds of inactivity before a logged-in admin is signed out
    ],
    'upload' => [
        'max_image_bytes'    => 5 * 1024 * 1024,
        'max_document_bytes' => 10 * 1024 * 1024,
        'max_cv_bytes'       => 5 * 1024 * 1024,
        'max_pixels'         => 40000000,           // reject decompression bombs (width*height)
        'max_image_width'    => 2000,               // GD downscale target (only when the GD extension is loaded)
        'quality'            => 85,
        'image_exts'         => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'document_exts'      => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'txt'],
        'cv_exts'            => ['pdf', 'doc', 'docx'],
    ],
    'mail' => [
        'driver'    => $mailDriver,
        'from'      => Env::get('MAIL_FROM', 'no-reply@luminaafricafoundation.com'),
        'from_name' => Env::get('MAIL_FROM_NAME', 'Lumina Africa Foundation'),
        // MAIL_DRIVER=smtp only (App\Core\Smtp). The SMTP login lives ONLY in .env: never in the database, never in logs.
        'smtp'      => [
            'host'       => trim((string) Env::get('MAIL_HOST', '')),
            'port'       => Env::int('MAIL_PORT', 587),
            'username'   => (string) Env::get('MAIL_USERNAME', ''),
            'password'   => (string) Env::get('MAIL_PASSWORD', ''),
            'encryption' => strtolower(trim((string) Env::get('MAIL_ENCRYPTION', ''))) ?: (Env::int('MAIL_PORT', 587) === 465 ? 'ssl' : 'tls'),   // ssl | tls | none
            'timeout'    => Env::int('MAIL_TIMEOUT', 20),
            'cafile'     => (string) Env::get('MAIL_CAFILE', ''),         // optional CA bundle; certificate checks stay ON
        ],
    ],
    'auth' => [
        'max_failures'    => 5,     // per IP+email within the window
        'max_ip_failures' => 20,    // per IP within the window
        'window'          => 900,   // seconds (15 min)
        'min_password'    => 10,
    ],
    'forms' => [
        'honeypot_field' => 'website',     // must stay EMPTY (bots fill it)
        'min_seconds'    => 3,             // a human needs at least this long to submit
        'max_seconds'    => 7200,          // stale form token after 2 h
    ],
    // extra Host values accepted besides the APP_URL host (comma list). Only enforced when APP_ENV is not "local".
    'allowed_hosts' => array_values(array_filter(array_map('trim', explode(',', (string) Env::get('ALLOWED_HOSTS', ''))))),
    'trusted_proxies' => array_values(array_filter(array_map('trim', explode(',', (string) Env::get('TRUSTED_PROXIES', ''))))),
    'pagination' => ['per_page' => 12, 'admin_per_page' => 20],

    /**
     * Abilities: can('name') / middleware 'can:name'. ability => roles that hold it.
     * Roles: stored in the `roles` table (Admin > Roles). The role lists below are the built-in defaults, used for the admin and editor roles
     * until the roles table exists; once it does, the table decides (admin always holds every ability).
     */
    'abilities' => [
        'dashboard.view'  => ['admin', 'editor'],
        'pages.edit'      => ['admin', 'editor'],   // edit page copy, SEO, create/edit custom pages
        'pages.delete'    => ['admin'],             // delete custom pages
        'menus.manage'    => ['admin'],
        'settings.manage' => ['admin'],
        'users.manage'    => ['admin'],
        'roles.manage'    => ['admin'],             // create / edit roles and choose what each role may do (never assignable to a custom role)
        'audit.view'      => ['admin'],
        'content.manage'  => ['admin', 'editor'],   // programs, projects, posts, activities, gallery, jobs, team, partners, documents, events, stories, FAQs, stats
        'content.delete'  => ['admin', 'editor'],
        'inbox.manage'    => ['admin', 'editor'],   // messages, subscribers, involvement, applications, donations, event registrations
        'inbox.delete'    => ['admin'],
        'export.csv'      => ['admin', 'editor'],
        'members.manage'  => ['admin', 'editor'],   // membership applications: view, approve/reject, payments, receipts, notes (never public, never anonymous)
        'members.delete'  => ['admin'],             // delete a member record that has no payments
        'members.types'   => ['admin'],             // membership types = the FEE CONFIGURATION (create, change fees, deactivate)
        'newsletter.manage' => ['admin', 'editor'], // newsletter issues: create, edit, duplicate, preview, publish, schedule, unpublish, archive, delete drafts (docs/NEWSLETTER.md)
        'newsletter.delete_published' => ['admin'], // delete an issue that is (or was) public
        'newsletter.send' => ['admin'],             // EVERYTHING about e-mailing an issue: settings, test sends, campaign, retries
        'calendar.manage'   => ['admin', 'editor'], // calendar entries, quick add, per-occurrence changes, confirming holiday dates, types, CSV import / export (docs/CALENDAR.md)
        'calendar.advanced' => ['admin'],           // the advanced repeat rules (nth weekday, Easter offset, Islamic holidays)
        'calendar.delete'   => ['admin'],           // BULK delete of calendar entries
        'meetings.manage'   => ['admin', 'editor'], // meetings: schedule, edit, invite, cancel, answer requests, e-mail attendees (docs/MEETINGS.md)
        'meetings.bulk'     => ['admin'],           // add more than 50 recipients at once (e.g. all approved members)
        'meetings.delete'   => ['admin'],           // delete a meeting request
    ],

    /** Slugs a custom page may NOT use (they are real routes or folders). */
    'reserved_slugs' => [
        'admin', 'assets', 'uploads', 'api', 'login', 'logout', 'index', 'storage', 'app', 'bin', 'database', 'docs', 'scss', 'tools',
        'vendor', 'sitemap', 'sitemap-xml', 'robots', 'robots-txt', 'subscribe', 'unsubscribe', 'search', 'download', 'downloads',
        'favicon', 'feed', 'rss', 'new', 'edit', 'delete', 'create', 'press', 'tests', 'public', 'node-modules', 'install', 'newsletter', 'calendar', 'meeting', 'meetings',
    ],
];
