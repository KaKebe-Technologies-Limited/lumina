<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Request;
use App\Core\Response;
use App\Services\Calendar\CalendarService;
use App\Services\Calendar\CivilDate;

/**
 * Admin JSON API of the calendar (month data for the manager, live preview of a schedule). Contract: docs/CALENDAR.md section 8.
 * OWNER: ADMIN agent for changes; both endpoints are delivered working by the architect. Session auth + header X-CSRF-Token on POST.
 */
final class CalendarApiController extends AdminController
{
    /**
     * GET /admin/api/calendar/month?month=YYYY-MM[&types=a,b][&q=text]
     * Everything in the visible grid including hidden and cancelled entries, with flags. Response:
     * {month,label,prev,next,from,to,week_start,today,days:{"2026-03-08":[{key,entry_id,event_id,title,type,type_name,icon,color,tint,text,on_color,start_date,end_date,
     *  all_day,start_time,end_time,is_public,is_active,is_system,cancelled,needs_confirmation,moved,recurring,edit_url}]}}
     */
    public function month(Request $request): Response
    {
        $this->authorize('calendar.manage');
        $str = static function (string $k) use ($request): string {      // an array value (?month[]=) counts as empty
            $v = $request->query($k, '');
            return is_string($v) ? $v : '';
        };
        $month = $str('month');
        $types = array_values(array_filter(array_map('trim', explode(',', $str('types')))));
        $grid = CalendarService::monthGrid(CalendarController::safeMonth($month), [
            'public' => false, 'include_cancelled' => true, 'types' => $types, 'q' => $str('q'),
        ]);
        $days = [];
        foreach ($grid['weeks'] as $week) {
            foreach ($week as $cell) {
                foreach ($cell['items'] as $o) {
                    $days[$cell['date']][] = [
                        'key' => $o['key'], 'entry_id' => $o['entry_id'], 'event_id' => $o['event_id'], 'title' => $o['title'], 'type' => $o['type_slug'],
                        'type_name' => $o['type_name'], 'icon' => $o['type_icon'], 'color' => $o['pair']['solid'], 'tint' => $o['pair']['tint'],
                        'text' => $o['pair']['text'], 'on_color' => $o['pair']['on_solid'], 'start_date' => $o['start_date'], 'end_date' => $o['end_date'],
                        'all_day' => $o['all_day'], 'start_time' => $o['start_time'], 'end_time' => $o['end_time'], 'is_public' => $o['is_public'],
                        'is_active' => $o['is_active'], 'is_system' => $o['is_system'], 'cancelled' => $o['cancelled'],
                        'needs_confirmation' => $o['needs_confirmation'], 'moved' => $o['moved'], 'recurring' => $o['recurring'],
                        'edit_url' => $o['entry_id'] ? url('admin/calendar/' . $o['entry_id'] . '/edit') : url('admin/events/' . $o['event_id'] . '/edit'),
                    ];
                }
            }
        }
        return $this->json([
            'month' => $grid['month'], 'label' => $grid['label'], 'prev' => $grid['prev'], 'next' => $grid['next'], 'from' => $grid['from'], 'to' => $grid['to'],
            'week_start' => $grid['week_start'], 'today' => $grid['today'], 'days' => $days,
        ]);
    }

    /**
     * POST /admin/api/calendar/preview  {start_date*, end_date, recurrence, recurrence_data | rule_* fields of the editor, repeat_until, entry_id?, count?}
     * -> {ok, errors:{field:msg}, description:"Every year on 8 March", dates:[{date,end_date,label,weekday}]}  (nothing is saved)
     */
    public function preview(Request $request): Response
    {
        $this->authorize('calendar.manage');
        $in = $request->post() ?: [];
        // the editor posts the friendly repeat fields (rule_*); API callers may send recurrence_data directly
        $rule = CalendarController::ruleFromForm($in);
        $in['recurrence'] = $rule['recurrence'];
        $in['recurrence_data'] = $rule['recurrence_data'];
        $count = max(1, min(12, (int) ($in['count'] ?? 5)));
        $entryId = isset($in['entry_id']) && ctype_digit((string) $in['entry_id']) ? (int) $in['entry_id'] : null;
        return $this->json(CalendarService::previewOccurrences($in, $count, $entryId));
    }
}
