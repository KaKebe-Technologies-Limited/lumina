<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Icons;
use App\Core\Request;
use App\Core\Response;
use App\Services\Calendar\CalendarService;
use App\Services\Calendar\CalendarWriter;

/**
 * Admin > Calendar > Types & colours. Contract: docs/CALENDAR.md. OWNER: ADMIN agent.
 * A type has a name, ONE colour (the calendar derives an accessible text/background set from it: ColorContrast::pair), a shape (icon) and two
 * switches (active, shown to visitors). The screen shows the contrast check live (assets/js/admin-calendar.js uses the same maths as
 * ColorContrast) and the server repeats the numbers, so the page is useful without JavaScript too. CalendarWriter::saveType / deleteType own
 * the validation; deleteType refuses system types, the core types and types that entries still use.
 */
final class CalendarTypesController extends AdminController
{
    /** Types that are part of the calendar: they can be renamed, recoloured and hidden but never deleted (the same list as CalendarWriter::deleteType). */
    public const CORE = ['programme', 'event', 'uganda-holiday', 'international-day', 'meeting', 'deadline', 'other'];

    /** Shapes offered in the picker: icon name => what a person calls it. Every one exists in App\Core\Icons. */
    public const ICON_LABELS = [
        'graduation-cap' => 'Graduation cap', 'book-open' => 'Open book', 'calendar' => 'Calendar', 'star' => 'Star', 'globe' => 'Globe', 'users' => 'People',
        'user' => 'Person', 'clock' => 'Clock', 'tag' => 'Tag', 'heart' => 'Heart', 'hand-heart' => 'Giving hand', 'handshake' => 'Handshake', 'briefcase' => 'Briefcase',
        'lightbulb' => 'Light bulb', 'flame' => 'Flame', 'sprout' => 'Seedling', 'target' => 'Target', 'award' => 'Award', 'shield' => 'Shield', 'mic' => 'Microphone',
        'map-pin' => 'Map pin', 'home' => 'House', 'laptop' => 'Laptop', 'code' => 'Code', 'rocket' => 'Rocket', 'bell' => 'Bell', 'compass' => 'Compass',
        'message-circle' => 'Speech bubble', 'newspaper' => 'Newspaper', 'camera' => 'Camera', 'sun' => 'Sun', 'banknote' => 'Money', 'alert-triangle' => 'Warning',
        'check-circle' => 'Tick', 'video' => 'Video',
    ];

    public function index(Request $request): Response
    {
        $this->authorize('calendar.manage');
        $counts = [];
        foreach (Database::all('SELECT type_id, COUNT(*) AS n FROM calendar_entries GROUP BY type_id') as $r) {
            $counts[(int) $r['type_id']] = (int) $r['n'];
        }
        $known = array_flip(Icons::names());
        $icons = array_intersect_key(self::ICON_LABELS, $known);
        $types = CalendarService::types();
        // a type may carry an icon that is not in the picker (older data): keep it selectable
        foreach ($types as $t) {
            if (!isset($icons[$t['icon']]) && isset($known[$t['icon']])) {
                $icons[$t['icon']] = ucfirst(str_replace('-', ' ', (string) $t['icon']));
            }
        }
        return $this->render('admin/calendar/types', [
            'available' => CalendarService::available(), 'types' => $types, 'counts' => $counts, 'icons' => $icons, 'core' => self::CORE,
            'nextOrder' => (int) Database::value('SELECT COALESCE(MAX(sort_order), 0) FROM calendar_types', [], 0) + 10,
        ], ['title' => 'Types & colours', 'nav' => 'calendar_types', 'breadcrumbs' => [['Calendar & schedule', url('admin/calendar')], ['Types & colours', null]]]);
    }

    /** POST /admin/calendar/types - name*, color*, icon, sort_order, is_active, show_on_public */
    public function store(Request $request): Response
    {
        $this->authorize('calendar.manage');
        $res = CalendarWriter::saveType(null, $this->input(), $this->userId());
        if (!$res['ok']) {
            return $this->failed($res['errors']);
        }
        return $this->done($res, 'The type "' . $request->string('name') . '" was added. You can now choose it when you add a date.');
    }

    /** POST /admin/calendar/types/{id} */
    public function update(Request $request, int $id): Response
    {
        $this->authorize('calendar.manage');
        $res = CalendarWriter::saveType($id, $this->input(), $this->userId());
        if (!$res['ok']) {
            return $this->failed($res['errors']);
        }
        return $this->done($res, 'The type "' . $request->string('name') . '" was saved.');
    }

    /** POST /admin/calendar/types/{id}/delete - refused for system and core types and for types in use. */
    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('calendar.manage');
        $res = CalendarWriter::deleteType($id);
        if (!$res['ok']) {
            return $this->failed(['_' => $res['error'] ?? 'Could not delete the type.']);
        }
        return $this->done($res, 'The type was deleted.');
    }

    /**
     * The checkboxes of the form (hidden 0 + tick box 1) are passed on as they are; an unticked box that was not posted at all means "off"
     * for a programmatic caller too, so a missing key is never read as the writer's default "on".
     * @return array<string,mixed>
     */
    private function input(): array
    {
        $p = $this->request->post() ?: [];
        foreach (['is_active', 'show_on_public'] as $k) {
            $p[$k] = ($p[$k] ?? '0') === '1' || ($p[$k] ?? '') === 'on' ? '1' : '0';
        }
        return $p;
    }

    /** @param array<string,string> $errors */
    private function failed(array $errors): Response
    {
        if ($this->request->expectsJson()) {
            return $this->json(['ok' => false, 'errors' => $errors], 422);
        }
        $this->fail($errors, 'admin/calendar/types');
    }

    private function done(array $res, string $message): Response
    {
        if ($this->request->expectsJson()) {
            return $this->json(['ok' => true] + array_intersect_key($res, array_flip(['id', 'pair'])));
        }
        Flash::success($message);
        return $this->redirect('admin/calendar/types');
    }
}
