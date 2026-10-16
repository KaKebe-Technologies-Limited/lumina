<?php
/**
 * One-shot developer setup. Idempotent: safe to run as often as you like.
 *
 *   /c/xampp/php/php.exe bin/setup.php            create DB if missing, import schema + seed, sync the pages table, create a dev admin if there is none
 *   /c/xampp/php/php.exe bin/setup.php --fresh    DROP the database first (APP_ENV=local only!), then do everything above
 *   /c/xampp/php/php.exe bin/setup.php --no-seed  skip database/seed.sql
 *
 * The dev admin password is random and is written ONLY to storage/dev-admin-credentials.txt (git-ignored); the path is printed.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Auth;
use App\Core\Config;
use App\Core\Database;
use App\Core\SitePages;
use App\Models\User;

$opts = array_slice($argv, 1);
$fresh = in_array('--fresh', $opts, true);
$seed = !in_array('--no-seed', $opts, true);
$dbName = (string) Config::get('db.name', 'lumina');
if (!preg_match('/^[A-Za-z0-9_]+$/', $dbName)) {
    fwrite(STDERR, "Illegal DB_NAME '{$dbName}'.\n");
    exit(1);
}

function out(string $m): void
{
    fwrite(STDOUT, $m . PHP_EOL);
}

try {
    $server = Database::connect('');
} catch (Throwable $e) {
    fwrite(STDERR, 'Cannot connect to MySQL: ' . $e->getMessage() . PHP_EOL . "Is MariaDB running in XAMPP and are DB_HOST/DB_USER/DB_PASS in .env correct?\n");
    exit(1);
}

if ($fresh) {
    if (Config::get('app.env') !== 'local') {
        fwrite(STDERR, "--fresh is only allowed when APP_ENV=local.\n");
        exit(1);
    }
    $server->exec("DROP DATABASE IF EXISTS `{$dbName}`");
    out("Dropped database {$dbName}.");
}
try {
    $server->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch (Throwable $e) {
    // shared hosting: ordinary users may not run CREATE DATABASE; that is fine when the database already exists
    out('Note: CREATE DATABASE was refused (' . $e->getMessage() . '). Continuing with the existing database.');
}
try {
    $server->exec("USE `{$dbName}`");
} catch (Throwable $e) {
    fwrite(STDERR, "Cannot use database '{$dbName}': " . $e->getMessage() . PHP_EOL . "Create it in your hosting panel (utf8mb4) and check DB_NAME / DB_USER / DB_PASS in .env.\n");
    exit(1);
}
out("Database {$dbName} ready.");

// ---- schema + seed ------------------------------------------------------------------------------------------------
function runSqlFile(PDO $pdo, string $file): int
{
    $n = 0;
    foreach (Database::splitSql((string) file_get_contents($file)) as $stmt) {
        if (preg_match('/^(CREATE\s+DATABASE|USE\s)/i', $stmt)) {
            continue;                    // the DB was created/selected above using DB_NAME
        }
        $pdo->exec($stmt);
        $n++;
    }
    return $n;
}

out('Schema: ' . runSqlFile($server, BASE_PATH . '/database/schema.sql') . ' statements.');
if ($seed && is_file(BASE_PATH . '/database/seed.sql')) {
    out('Seed:   ' . runSqlFile($server, BASE_PATH . '/database/seed.sql') . ' statements.');
}

Database::setConnection(Database::connect($dbName));

// ---- pages table from the registry --------------------------------------------------------------------------------
$created = SitePages::sync();
out("Pages: {$created} new row(s) from the page registry; total " . Database::value('SELECT COUNT(*) FROM pages') . '.');

// ---- dev admin ----------------------------------------------------------------------------------------------------
if ((int) Database::value('SELECT COUNT(*) FROM users') === 0) {
    $email = 'admin@luminaafricafoundation.local';
    $password = Auth::generatePassword(18);
    $isLocal = Config::get('app.env') === 'local';
    User::create([
        'name'                 => 'Dev Admin',
        'email'                => $email,
        'password_hash'        => Auth::hashPassword($password),
        'role'                 => 'admin',
        'is_active'            => 1,
        'must_change_password' => $isLocal ? 0 : 1,
    ]);
    $file = STORAGE_PATH . '/dev-admin-credentials.txt';
    file_put_contents($file, "Lumina dev admin (local only - delete this file when done)\nURL:      /admin/login\nEmail:    {$email}\nPassword: {$password}\n");
    @chmod($file, 0600);
    out('Created dev admin. Credentials written to: ' . $file);
} else {
    out('Users exist: no dev admin created.');
}

foreach (['uploads', 'storage/private', 'storage/cache', 'storage/logs', 'storage/mail', 'storage/sessions'] as $dir) {
    if (!is_dir(BASE_PATH . '/' . $dir)) {
        mkdir(BASE_PATH . '/' . $dir, 0775, true);
    }
}
out('Done.');
