<?php
/**
 * Application bootstrap. Safe to include from the web front controller (index.php) AND from CLI scripts (bin/*.php).
 * It does NOT start a session and does NOT dispatch a request (see App\Core\App::run()).
 *
 * AUTOLOAD CONVENTION (deploy target is case-sensitive Linux, so file names must match class names EXACTLY):
 *   App\<Segment1>\<Rest...>\<Class>  ->  app/<segment1 lower-cased>/<Rest... exactly as written>/<Class>.php
 *   App\Core\Router                    ->  app/core/Router.php
 *   App\Models\Post                    ->  app/models/Post.php
 *   App\Controllers\Web\HomeController ->  app/controllers/Web/HomeController.php
 *   App\Controllers\Admin\PostController -> app/controllers/Admin/PostController.php
 *   App\Services\Foo                   ->  app/services/Foo.php
 */
declare(strict_types=1);

if (defined('LUMINA_BOOTED')) {
    return;
}
define('LUMINA_BOOTED', true);

define('BASE_PATH', str_replace(chr(92), '/', dirname(__DIR__)));   // project root == web root
define('APP_PATH', BASE_PATH . '/app');
define('ASSET_PATH', BASE_PATH . '/assets');
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('LUMINA_START', microtime(true));

spl_autoload_register(static function (string $class): void {
    if (strncmp($class, 'App' . chr(92), 4) !== 0) {
        return;
    }
    $parts = explode(chr(92), substr($class, 4));
    $parts[0] = strtolower($parts[0]);
    $file = APP_PATH . '/' . implode('/', $parts) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require APP_PATH . '/helpers.php';

use App\Core\Config;
use App\Core\Env;
use App\Core\ErrorHandler;

Env::load(BASE_PATH . '/.env');
Config::load(APP_PATH . '/config/app.php');

date_default_timezone_set((string) Config::get('app.timezone', 'Africa/Kampala'));
mb_internal_encoding('UTF-8');

// Runtime folders are git-ignored, so make sure they exist.
foreach (['cache', 'logs', 'mail', 'private', 'sessions'] as $dir) {
    if (!is_dir(STORAGE_PATH . '/' . $dir)) {
        @mkdir(STORAGE_PATH . '/' . $dir, 0775, true);
    }
}

ErrorHandler::register((bool) Config::get('app.debug', false));
