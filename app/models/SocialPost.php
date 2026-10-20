<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Table `social_posts` = one row per (issue, channel, share_version): the queue AND the share log of the social-media module (docs/SOCIAL.md section 2).
 * Written only through App\Services\Social\PostQueue. Never holds a token or a full API payload.
 */
final class SocialPost extends Model
{
    protected static string $table = 'social_posts';
    protected static string $order = 'id DESC';

    public const STATUSES = ['queued', 'posting', 'posted', 'failed', 'skipped', 'review'];
    public const STATUS_LABELS = ['queued' => 'Queued', 'posting' => 'Posting', 'posted' => 'Posted', 'failed' => 'Failed', 'skipped' => 'Skipped', 'review' => 'Needs review'];
    public const STATUS_TONES = ['queued' => 'info', 'posting' => 'info', 'posted' => 'success', 'failed' => 'danger', 'skipped' => 'neutral', 'review' => 'warning'];
}
