<?php
/**
 * CATCH-ALL for owner-created CUSTOM pages - OWNER: B2 "web-content". Loaded LAST (zz_ prefix sorts after every other route file).
 * Router::fallback() runs only when NO route matched a GET/HEAD request. The handler looks up pages.slug = path
 * (type='custom', is_published=1) and renders it with its template, or throws App\Core\HttpException(404).
 * Reserved slugs (config reserved_slugs) and registry page slugs can never be custom pages.
 */

use App\Controllers\Web\CustomPageController;

$router->fallback([CustomPageController::class, 'show']);
