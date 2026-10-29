<?php
/**
 * LOCAL BACKUP of the database: writes a dated SQL dump to a folder OUTSIDE the web root (default C:\xampp\backups\lumina\) and keeps the newest 30.
 *
 *   /c/xampp/php/php.exe bin/backup-db.php            # dump the database named in .env (DB_NAME)
 *   /c/xampp/php/php.exe bin/backup-db.php --keep=60  # keep the newest 60 instead
 *   /c/xampp/php/php.exe bin/backup-db.php --dir=D:\backups\lumina
 *
 * To restore a dump into a database (phpMyAdmin > Import, or):  C:\xampp\mysql\bin\mysql.exe -uroot lumina < C:\xampp\backups\lumina\lumina-YYYYMMDD-HHMMSS.sql
 * The dump holds personal data (learners, members, messages): keep it private. It refuses to run outside APP_ENV=local and outside the command line.
 * Windows: schedule it every day with Task Scheduler (program: C:\xampp\php\php.exe, arguments: C:\xampp\htdocs\lumina\bin\backup-db.php). docs/BACKUPS.md.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Config;
use App\Core\Env;

if (Config::get('app.env') !== 'local') {
    fwrite(STDERR, "Refusing to run outside APP_ENV=local (this script is for the local copy; on the live site use phpMyAdmin > Export or Hostinger Backups).\n");
    exit(1);
}
$args = array_slice($argv, 1);
$opt = static function (string $name, string $default) use ($args): string {
    foreach ($args as $a) {
        if (str_starts_with($a, '--' . $name . '=')) {
            return substr($a, strlen($name) + 3);
        }
    }
    return $default;
};
$dir = rtrim($opt('dir', 'C:\\xampp\\backups\\lumina'), '\\/');
$keep = max(1, (int) $opt('keep', '30'));
$db = (string) Env::get('DB_NAME', 'lumina');
$host = (string) Env::get('DB_HOST', '127.0.0.1');
$port = (string) Env::get('DB_PORT', '3306');
$user = (string) Env::get('DB_USER', 'root');
$pass = (string) Env::get('DB_PASS', '');
$dump = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
if (!is_file($dump)) {
    fwrite(STDERR, "mysqldump.exe not found at {$dump}\n");
    exit(1);
}
if (!is_dir($dir) && !@mkdir($dir, 0777, true) && !is_dir($dir)) {
    fwrite(STDERR, "Cannot create {$dir}\n");
    exit(1);
}
$file = $dir . DIRECTORY_SEPARATOR . $db . '-' . date('Ymd-His') . '.sql';
$cmd = '"' . $dump . '" --host=' . escapeshellarg($host) . ' --port=' . escapeshellarg($port) . ' --user=' . escapeshellarg($user) . ($pass !== '' ? ' --password=' . escapeshellarg($pass) : '')
    . ' --single-transaction --routines --triggers --default-character-set=utf8mb4 --result-file="' . $file . '" ' . escapeshellarg($db);
exec($cmd . ' 2>&1', $out, $code);
if ($code !== 0 || !is_file($file) || filesize($file) < 1000) {
    @unlink($file);
    fwrite(STDERR, "Backup failed (exit {$code}): " . implode(' ', array_slice($out, -3)) . "\n");
    exit(1);
}
$all = glob($dir . DIRECTORY_SEPARATOR . $db . '-*.sql') ?: [];
rsort($all);
foreach (array_slice($all, $keep) as $old) {
    @unlink($old);
}
echo 'Backup written: ' . $file . ' (' . number_format((int) round(filesize($file) / 1024)) . ' KB). ' . min(count($all), $keep) . " backup(s) kept in {$dir}\n";
exit(0);
