<?php
/**
 * Lumina Africa Foundation - FRONT CONTROLLER.
 *
 * The web root IS the project root (there is no public/ folder). Apache (.htaccess) serves real
 * files in /assets and /uploads directly and rewrites every other request to this file.
 *
 * PHP's built-in server (`php -S 127.0.0.1:8099 index.php` from the project root) has no .htaccess, so this file
 * does the same job there: only existing files under assets/ and uploads/ (images, css, js, fonts) are served as static
 * files; everything else (app/, storage/, .env, *.sql ...) goes to the application and is never handed out.
 */
declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    // The built-in server sets SCRIPT_NAME to whatever "/x/index.php" URL was requested; pin it so the base path stays "".
    $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = '/index.php';
    $reqPath = rawurldecode((string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH));
    if (preg_match('#^/(assets|uploads)/[^\0]+\.(css|js|map|png|jpe?g|gif|webp|svg|ico|woff2?|ttf|txt|json)$#i', $reqPath)
        && !str_contains($reqPath, '..') && is_file(__DIR__ . $reqPath) && !str_ends_with(strtolower($reqPath), '.map')) {
        return false;
    }
}

// Readable message instead of a white page when the host cannot run the app (checked before anything else is loaded).
$missing = [];
if (PHP_VERSION_ID < 80100) {
    $missing[] = 'PHP 8.1 or newer (this server runs ' . PHP_VERSION . ')';
}
foreach (['pdo_mysql', 'mbstring', 'dom', 'fileinfo', 'ctype', 'json'] as $ext) {
    if (!extension_loaded($ext)) {
        $missing[] = 'the PHP extension "' . $ext . '"';
    }
}
if ($missing !== []) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    exit("This website cannot start because the server is missing:\n - " . implode("\n - ", $missing) . "\nAsk your hosting provider to enable them (PHP version and extensions are chosen in the hosting control panel).\n");
}

require __DIR__ . '/app/bootstrap.php';

\App\Core\App::run();
