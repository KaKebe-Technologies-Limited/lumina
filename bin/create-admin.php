<?php
/**
 * Create (or reset the password of) an admin/editor account from the command line.
 *
 *   /c/xampp/php/php.exe bin/create-admin.php --email=owner@example.org --name="Site Owner" [--role=admin|editor] [--password=...]
 *
 * Without --password a strong random password is generated and written ONLY to storage/dev-admin-credentials.txt
 * (git-ignored); the path is printed, never the password. If the email already exists its password is reset and the
 * account is flagged must_change_password.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Models\User;

$args = [];
foreach (array_slice($argv, 1) as $a) {
    if (preg_match('/^--([a-z\-]+)=(.*)$/s', $a, $m)) {
        $args[$m[1]] = $m[2];
    }
}
$email = strtolower(trim($args['email'] ?? ''));
$name = trim($args['name'] ?? 'Administrator');
$role = $args['role'] ?? 'admin';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($role, ['admin', 'editor'], true)) {
    fwrite(STDERR, "Usage: php bin/create-admin.php --email=you@example.org --name=\"Your Name\" [--role=admin|editor] [--password=...]\n");
    exit(1);
}
$generated = !isset($args['password']) || $args['password'] === '';
$password = $generated ? Auth::generatePassword(18) : $args['password'];
if (($problem = Auth::passwordProblem($password)) !== null) {
    fwrite(STDERR, $problem . PHP_EOL);
    exit(1);
}

$existing = User::findByEmail($email);
if ($existing) {
    Database::update('users', ['password_hash' => Auth::hashPassword($password), 'must_change_password' => 1, 'is_active' => 1, 'role' => $role, 'name' => $name], 'id = ?', [(int) $existing['id']]);
    fwrite(STDOUT, "Updated existing account {$email}.\n");
} else {
    User::create(['name' => $name, 'email' => $email, 'password_hash' => Auth::hashPassword($password), 'role' => $role, 'is_active' => 1, 'must_change_password' => $generated ? 1 : 0]);
    fwrite(STDOUT, "Created {$role} account {$email}.\n");
}
if ($generated) {
    $file = STORAGE_PATH . '/dev-admin-credentials.txt';
    file_put_contents($file, "\nEmail: {$email}\nPassword: {$password}\n(must change password at first sign-in)\n", FILE_APPEND);
    @chmod($file, 0600);
    fwrite(STDOUT, "Generated password written to: {$file}\n");
}
