<?php
/**
 * Membership housekeeping for cron (once a day is plenty). CLI only. Contract: docs/MEMBERSHIP_CARD.md section "Privacy and retention".
 *
 *   /usr/local/bin/php /home/USER/public_html/bin/membership-maintenance.php          (Hostinger: Advanced > Cron Jobs, daily)
 *   /c/xampp/php/php.exe bin/membership-maintenance.php                              (local)
 *
 * 1. Retention: deletes the PHOTO and the PROOF OF PAYMENT (and the proofs attached to payments) of members who were REJECTED or CANCELLED more than N days ago, N = setting
 *    "Delete photo and proof N days after a rejection or cancellation" in Admin > Membership > Membership card (0 = never; default 90). The application record, the
 *    membership number and the receipts stay. Nothing is deleted for a member who is pending, approved, suspended or expired.
 * 2. Marks lapsed memberships as expired (the same idempotent step the members list runs when it opens), and deletes stray files in storage/private/members
 *    that no record points to (older than one day: left by a request that died half way).
 * Prints one line per task. The audit log gets counts only.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\Membership\MemberFiles;
use App\Services\Membership\MembershipService;

$purged = MemberFiles::purgeStale();
echo 'Retention: ' . $purged['members'] . ' member(s), ' . $purged['files'] . " file(s) deleted\n";
echo 'Expired: ' . MembershipService::syncExpired() . " membership(s) marked as expired\n";
echo 'Orphans: ' . MemberFiles::cleanOrphans() . " stray private file(s) deleted\n";
