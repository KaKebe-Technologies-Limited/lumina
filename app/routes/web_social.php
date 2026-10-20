<?php
/**
 * PUBLIC social-media routes (docs/SOCIAL.md section 8). Only ONE: the share image.
 *
 *   GET /social/image/{token}.jpg    the branded share image of a PUBLIC issue. Instagram and Facebook must fetch a public address; the token (64 hex = 256 random bits,
 *                                    table social_images) is the only way in. 404 for an unknown token, an issue that is not public, a missing file. No cookie, no session,
 *                                    Cache-Control: public, max-age=3600, nosniff, never a directory listing, never any other file.  Web\SocialImageController (IMAGES agent).
 */

use App\Controllers\Web\SocialImageController;

$router->get('/social/image/{token}.jpg', [SocialImageController::class, 'show'])->where('token', '[a-f0-9]{64}')->name('social.image');
