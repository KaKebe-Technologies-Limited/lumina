<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Table `meeting_requests` = a visitor's request for a meeting (Contact page, "Request a meeting"). Generic API: see App\Core\Model.
 * PERSONAL DATA (admin + editor only). Nothing is booked automatically. Rules: App\Services\Meetings\MeetingRequestService. Docs: docs/MEETINGS.md.
 */
final class MeetingRequest extends Model
{
    protected static string $table = 'meeting_requests';
    protected static string $order = 'id DESC';

    public const STATUSES = ['new', 'approved', 'declined', 'closed'];
    public const PLATFORM_PREFS = ['video', 'phone', 'in_person'];
    public const TIME_WINDOWS = ['any', 'morning', 'afternoon', 'evening'];
    public const DURATIONS = [15, 30, 45, 60, 90];
}
