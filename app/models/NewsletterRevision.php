<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `newsletter_revisions` = content snapshots of an issue (the latest 30 are kept). Written by NewsletterService only. */
final class NewsletterRevision extends Model
{
    protected static string $table = 'newsletter_revisions';
    protected static string $order = 'id DESC';

    public const KEEP = 30;
    public const SOURCES = ['save', 'autosave', 'publish', 'restore'];
}
