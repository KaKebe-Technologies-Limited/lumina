<?php
/**
 * Shared body of every themed error page. Vars: $status (int), $title, $message (defaults supplied by the ErrorHandler),
 * $debug (null | array, only with APP_DEBUG=1), $suggest (bool: list the main menu entries), $maintenance (bool).
 * Wording is editable: content('site_errors', 'e404.title') etc. (admin: Pages > Error pages & maintenance message).
 */

use App\Core\Nav;
use App\Core\PageRegistry;

$key = match ((int) $status) {
    403 => 'e403', 404 => 'e404', 405 => 'e405', 419 => 'e419', 429 => 'e429', 503 => 'maintenance', default => 'e500',
};
$hasCopy = PageRegistry::field('site_errors', $key . '.title') !== null;
$heading = $hasCopy && trim((string) content('site_errors', $key . '.title')) !== '' ? (string) content('site_errors', $key . '.title') : (string) $title;
$text = $hasCopy && trim((string) content('site_errors', $key . '.text')) !== '' ? (string) content('site_errors', $key . '.text') : (string) $message;
$maintenance = !empty($maintenance);
$contact = content_btn('site_errors', 'common.contact');
$emailPrimary = trim((string) setting('email_primary', ''));
$phone = trim((string) setting('phone_1', ''));
?>
<section class="error-page<?= $maintenance ? ' error-page--maintenance' : '' ?>" aria-labelledby="error-title">
    <div class="container container--narrow">
        <div class="error-page__card">
            <?php if ($maintenance): ?>
                <span class="error-page__icon"><?= icon('clock', 'icon--xl') ?></span>
            <?php else: ?>
                <p class="error-page__code"><span class="visually-hidden"><?= e(content('site_errors', 'common.code_label')) ?> </span><?= (int) $status ?></p>
            <?php endif; ?>
            <h1 class="error-page__title" id="error-title"><?= e($heading) ?></h1>
            <p class="lead error-page__text"><?= e($text) ?></p>

            <div class="error-page__actions">
                <a class="btn btn--accent btn--lg" href="<?= e(url('')) ?>"><?= icon('home') ?> <?= e(content('site_errors', 'common.home_label')) ?></a>
                <?php if (!$maintenance): ?>
                    <button class="btn btn--outline btn--lg" type="button" data-history-back hidden><?= icon('arrow-left') ?> <?= e(content('site_errors', 'common.back_label')) ?></button>
                    <?php if ($contact['has'] && (int) $status !== 403): ?><a class="btn btn--ghost btn--lg" href="<?= e($contact['url']) ?>"><?= e($contact['label']) ?></a><?php endif; ?>
                <?php endif; ?>
            </div>

            <?php if ($maintenance && ($emailPrimary !== '' || $phone !== '')): ?>
                <div class="error-page__contact">
                    <h2 class="h4"><?= e(content('site_errors', 'maintenance.contact_title')) ?></h2>
                    <ul>
                        <?php if ($emailPrimary !== ''): ?><li><?= icon('mail', 'icon--sm') ?> <a href="mailto:<?= e($emailPrimary) ?>"><?= e($emailPrimary) ?></a></li><?php endif; ?>
                        <?php if ($phone !== ''): ?><li><?= icon('phone', 'icon--sm') ?> <a href="<?= e(phone_href($phone)) ?>"><?= e($phone) ?></a></li><?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($suggest)): $links = array_slice(Nav::header(), 0, 8); ?>
                <?php if ($links): ?>
                    <nav class="error-page__links" aria-labelledby="suggest-title">
                        <h2 class="h4" id="suggest-title"><?= e(content('site_errors', 'e404.links_title')) ?></h2>
                        <ul class="pills">
                            <?php foreach ($links as $l): ?><li><a class="pills__item" href="<?= e($l['url']) ?>"><?= e($l['label']) ?></a></li><?php endforeach; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!empty($debug)): ?>
                <details class="error-page__debug" open>
                    <summary>Debug (APP_DEBUG=1 only)</summary>
                    <pre><?= e(($debug['message'] ?? '') . "\n" . ($debug['file'] ?? '') . ':' . ($debug['line'] ?? '') . "\n\n" . ($debug['trace'] ?? '')) ?></pre>
                </details>
            <?php endif; ?>
        </div>
    </div>
</section>
