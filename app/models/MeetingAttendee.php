<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Table `meeting_attendees` = one invitee of one meeting (UNIQUE per meeting + e-mail). Generic API: see App\Core\Model.
 * PERSONAL DATA: the e-mail address never leaves the admin area and never reaches a log or an audit entry (ids only).
 * Rules (adding, removing, RSVP, queue states) live in App\Services\Meetings\MeetingService. Docs: docs/MEETINGS.md.
 */
final class MeetingAttendee extends Model
{
    protected static string $table = 'meeting_attendees';
    protected static string $order = 'id ASC';

    public const SOURCES = ['manual', 'member', 'user', 'previous', 'request'];
    public const RSVPS = ['pending', 'accepted', 'declined', 'tentative'];
    public const INVITE_STATUSES = ['not_sent', 'queued', 'sent', 'failed'];
    public const QUEUE_KINDS = ['invite', 'update', 'cancel'];
    /** attempts per queued message before the row is marked failed */
    public const MAX_ATTEMPTS = 3;
}
