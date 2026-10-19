<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Table `newsletters` = one newsletter issue (details + structured JSON content + e-mail campaign counters). Generic API: see App\Core\Model.
 * Business rules (status transitions, lazy scheduled publishing, optimistic locking, revisions, slugs, numbering) live in
 * App\Services\Newsletter\NewsletterService: use it, do not write `status`, `version` or `content` yourself.
 * Docs: docs/NEWSLETTER.md.
 */
final class Newsletter extends Model
{
    protected static string $table = 'newsletters';
    protected static string $order = 'issue_date DESC, id DESC';

    public const STATUSES = ['draft', 'scheduled', 'published', 'archived'];
    /** how an issue is read: written with the block editor, or an uploaded PDF (see NewsletterPdf) */
    public const KINDS = ['online', 'pdf'];
    public const EMAIL_STATUSES = ['none', 'queued', 'sending', 'paused', 'sent', 'cancelled'];
    /** e-mail states in which a campaign still owns the issue (it cannot be unpublished or deleted) */
    public const EMAIL_ACTIVE = ['queued', 'sending', 'paused'];
}
