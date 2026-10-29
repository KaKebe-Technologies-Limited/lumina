<?php
declare(strict_types=1);

/**
 * LAF Academy: re-ask Pesapal about certificate payments that are still waiting (a learner closed the page and the notification never arrived).
 * A pending payment older than two days that Pesapal never confirmed is closed. Run every 15 minutes by cron (docs/PESAPAL.md):
 *   *\/15 * * * * php /path/to/lumina/bin/academy-payments-recheck.php
 */

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\Academy\CertificatePayments;

if (!CertificatePayments::ready() || !CertificatePayments::available()) {
    echo "Payments are not set up (table or Pesapal keys missing): nothing to do.\n";
    exit(0);
}
$n = CertificatePayments::recheckPending(200);
echo "Checked {$n} waiting payment(s) with Pesapal.\n";
