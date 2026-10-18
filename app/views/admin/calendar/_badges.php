<?php
/**
 * Status badges of one occurrence (the shape CalendarService::occurrences() returns). Var: $o.
 * Words, not just colour: Cancelled, Hidden, Team only, Date to be confirmed, Confirmed, Moved.
 */
$entry = ($o['source'] ?? 'entry') === 'entry';
$t = \App\Services\Calendar\CalendarService::typeBySlug((string) ($o['type_slug'] ?? ''));
$teamOnly = empty($o['is_public']) || ($t !== null && (!$t['is_active'] || !$t['show_on_public']));
?>
<?php if (!empty($o['cancelled'])): ?><span class="badge badge--danger">Cancelled</span><?php endif; ?>
<?php if (empty($o['is_active'])): ?><span class="badge badge--neutral">Hidden</span><?php elseif ($teamOnly): ?><span class="badge badge--neutral"<?= !empty($o['is_public']) ? ' title="Its type is hidden from visitors"' : '' ?>>Team only</span><?php endif; ?>
<?php if (!empty($o['needs_confirmation'])): ?><span class="badge badge--warning">Date to be confirmed</span><?php elseif ($entry && !empty($o['confirmed'])): ?><span class="badge badge--success">Confirmed</span><?php endif; ?>
<?php if (!empty($o['moved'])): ?><span class="badge badge--info">Moved</span><?php endif; ?>
<?php if (!$entry): ?><span class="badge badge--outline">From Events</span><?php endif; ?>
