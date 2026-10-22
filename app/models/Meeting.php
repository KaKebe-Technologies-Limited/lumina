<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Table `meetings` = one scheduled meeting (details, join link, organiser, status, SEQUENCE). Generic API: see App\Core\Model.
 * The meeting OWNS one calendar entry; business rules (status transitions, SEQUENCE, calendar sync, attendees) live in
 * App\Services\Meetings\MeetingService: use it, do not write `status`, `sequence` or the calendar entry yourself.
 * The join link is a SECRET: never expose it in a public endpoint unless show_join_link_public = 1. Docs: docs/MEETINGS.md.
 */
final class Meeting extends Model
{
    protected static string $table = 'meetings';
    protected static string $order = 'start_at DESC, id DESC';

    public const STATUSES = ['draft', 'scheduled', 'cancelled', 'completed'];
    public const VISIBILITIES = ['private', 'public'];
    /** platform key => label */
    public const PLATFORMS = [
        'zoom'        => 'Zoom',
        'google_meet' => 'Google Meet',
        'teams'       => 'Microsoft Teams',
        'jitsi'       => 'Jitsi (free video room)',
        'whatsapp'    => 'WhatsApp call',
        'phone'       => 'Phone call',
        'in_person'   => 'In person',
        'other'       => 'Other',
        'podcast'        => 'Podcast / live broadcast (any link)',
        'youtube_live'   => 'YouTube Live',
        'facebook_live'  => 'Facebook Live',
        'tiktok_live'    => 'TikTok Live',
        'instagram_live' => 'Instagram Live',
    ];

    /** Platforms that are a public podcast or live broadcast: the link is for viewers, so the meeting is shown on the website with a "Watch live" button. */
    public const BROADCAST = ['podcast', 'youtube_live', 'facebook_live', 'tiktok_live', 'instagram_live'];
}
