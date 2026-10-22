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
    /**
     * FORM BUILDER limits (docs/FORMS.md). Read with config('form_builder.max_fields'). The definition normaliser (App\Services\Forms\FormDefinition)
     * enforces every one of them on the server; the builder UI only mirrors them.
     */
    'form_builder' => [
        'max_pages'          => 12,
        'max_fields'         => 150,          // input + layout fields, all pages together
        'max_options'        => 200,          // per choice field
        'max_rules'          => 2,            // conditions per field / page
        'max_title'          => 190,
        'max_label'          => 200,
        'max_help'           => 600,
        'max_text'           => 2000,         // paragraph block
        'max_option_label'   => 120,
        'max_definition_kb'  => 512,          // JSON size of one definition
        'max_settings_kb'    => 64,
        'max_answer_chars'   => 8000,         // one text answer (long text)
        'max_total_answer_kb' => 256,         // all answers of one submission together
        'file_types'         => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'png'],   // the ONLY file types a form can accept
        'file_max_mb'        => 25,           // hard ceiling per file (also capped by the setting forms_max_upload_mb and the server limit)
        'file_max_files'     => 5,            // per file field
        'file_total_mb'      => 40,           // all files of one submission
        'staff_emails_max'   => 10,
        'confirm_per_address' => [3, 3600],   // confirmation e-mails: max 3 per MAILBOX per hour (+tags and Gmail dots count as one)
        'confirm_per_form'    => [120, 3600], // ... and at most 120 per form per hour
        'notify_per_form'     => [60, 3600],  // staff notifications per form per hour (beyond it only the first of each batch is sent)
        'retention_max_days'  => 3650,
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
        'members.card'    => ['admin'],             // the MEMBERSHIP CARD DESIGNER (look, colours, wording, number format, e-mail, rules); issuing / re-sending a member's card needs members.manage (docs/MEMBERSHIP_CARD.md)
        'newsletter.manage' => ['admin', 'editor'], // newsletter issues: create, edit, duplicate, preview, publish, schedule, unpublish, archive, delete drafts (docs/NEWSLETTER.md)
        'newsletter.delete_published' => ['admin'], // delete an issue that is (or was) public
        'newsletter.send' => ['admin'],             // EVERYTHING about e-mailing an issue: settings, test sends, campaign, retries
        'social.manage'     => ['admin'],           // SOCIAL MEDIA settings: profile links, auto-share channels (API keys and tokens), post templates, share images (docs/SOCIAL.md): admin only by default, holds secrets
        'social.share'      => ['admin', 'editor'], // choose the channels and captions of an issue, the Share kit, the share log, retry a failed post
        'calendar.manage'   => ['admin', 'editor'], // calendar entries, quick add, per-occurrence changes, confirming holiday dates, types, CSV import / export (docs/CALENDAR.md)
        'calendar.advanced' => ['admin'],           // the advanced repeat rules (nth weekday, Easter offset, Islamic holidays)
        'calendar.delete'   => ['admin'],           // BULK delete of calendar entries
        'meetings.manage'   => ['admin', 'editor'], // meetings: schedule, edit, invite, cancel, answer requests, e-mail attendees (docs/MEETINGS.md)
        'meetings.bulk'     => ['admin'],           // add more than 50 recipients at once (e.g. all approved members)
        'meetings.delete'   => ['admin'],           // delete a meeting request
        'forms.manage'      => ['admin', 'editor'], // create / edit / publish / close / archive forms, templates, the builder (docs/FORMS.md)
        'forms.submissions' => ['admin', 'editor'], // view and handle the answers people sent through a form: status, notes, files (PERSONAL DATA)
        'forms.export'      => ['admin'],           // download the answers of a form as CSV
        'forms.delete'      => ['admin'],           // delete a form, or delete submissions (single or bulk)
        'shop.manage'       => ['admin', 'editor'], // shop products, categories and pictures (docs/SHOP.md)
        'shop.orders'       => ['admin', 'editor'], // shop orders: view, confirm, record payments, change status, notes, packing slips (PERSONAL DATA of buyers)
        'shop.settings'     => ['admin'],           // shop settings, delivery and PAYMENT settings (holds the payment-gateway secrets): admin only by default
        'shop.export'       => ['admin'],           // download shop orders as CSV
        'shop.delete'       => ['admin'],           // delete products and orders
        'documents.sign'    => ['admin'],           // DOCUMENTS: manage signatories and their signature images, apply signatures and build signed PDFs (docs/DOCUMENTS-SIGNATURES.md): admin only by default
        'academy.manage'    => ['admin'],           // LAF ACADEMY, everything: all courses, learners, certificates, facilitators, settings, starter courses (docs/ACADEMY.md)
        'academy.teach'     => ['admin'],           // LAF ACADEMY, teaching: the facilitator role. Own courses (lessons, quizzes), virtual classes, the learners of those courses
    ],

    /** Slugs a custom page may NOT use (they are real routes or folders). */
    'reserved_slugs' => [
        'admin', 'assets', 'uploads', 'api', 'login', 'logout', 'index', 'storage', 'app', 'bin', 'database', 'docs', 'scss', 'tools',
        'vendor', 'sitemap', 'sitemap-xml', 'robots', 'robots-txt', 'subscribe', 'unsubscribe', 'search', 'download', 'downloads',
        'favicon', 'feed', 'rss', 'new', 'edit', 'delete', 'create', 'press', 'tests', 'public', 'node-modules', 'install', 'newsletter', 'calendar', 'meeting', 'meetings', 'forms', 'shop', 'offline', 'opportunities', 'academy', 'manifest-webmanifest', 'sw-js',
    ],
];
