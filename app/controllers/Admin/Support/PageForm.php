<?php
declare(strict_types=1);

namespace App\Controllers\Admin\Support;

use App\Core\Content;
use App\Core\Database;
use App\Core\PageRegistry;
use App\Core\Sanitizer;
use App\Core\SitePages;

/**
 * Helper for the admin Pages editor (OWNER: B3 admin-core). Pure logic, no HTTP:
 *   - builds the edit model (sections -> fields -> value/default/edited state),
 *   - validates and normalises a submitted editor form (blocks[...], visible[...], reset[...]),
 *   - offers small look-ups used by the views (link suggestions, last edit, concurrency stamp).
 *
 * The form contract (see docs/ARCHITECTURE.md 8.7):
 *   blocks[<section>.<field>]                       plain values (text, textarea, richtext, url, email, number, boolean, select, image path)
 *   blocks[<section>.<field>.alt]                   image alt text
 *   blocks[<section>.<field>][<i>][<sub>]           repeater rows (image sub-fields also <sub>_alt)
 *   visible[<section>]                              '1' / '0' for toggle sections
 *   reset[<section>.<field>]                        '1' = drop the override (back to the registry default)
 */
final class PageForm
{
    /**
     * Sections of a registry page with each field resolved for the editor.
     * @return list<array<string,mixed>> section => [key,label,help,toggle,visible,visible_default,edited,fields => [fk => snapshot entry]]
     */
    public static function sections(string $slug): array
    {
        $snapshot = Content::snapshot($slug);
        $over = Content::overrides($slug);
        $out = [];
        foreach (PageRegistry::sections($slug) as $skey => $section) {
            $fields = [];
            $edited = 0;
            foreach ($section['fields'] as $fkey => $field) {
                $bk = $skey . '.' . $fkey;
                $entry = $snapshot[$bk] ?? null;
                if ($entry === null) {
                    continue;
                }
                $fields[$bk] = $entry;
                if ($entry['overridden'] || ($field['type'] === 'image' && array_key_exists($bk . '.alt', $over))) {
                    $edited++;
                }
            }
            $visibleOverride = $section['toggle'] && array_key_exists($skey . '._visible', $over);
            if ($visibleOverride) {
                $edited++;
            }
            $out[] = [
                'key'             => $skey,
                'label'           => $section['label'],
                'help'            => $section['help'],
                'toggle'          => $section['toggle'],
                'visible'         => Content::sectionOn($slug, $skey),
                'visible_default' => $section['default_visible'],
                'edited'          => $edited,
                'fields'          => $fields,
            ];
        }
        return $out;
    }

    /** Short human description of a field's registry default (shown in the "reset to default" note). */
    public static function describeDefault(array $field): string
    {
        $d = $field['default'] ?? '';
        switch ($field['type']) {
            case 'repeater':
                $n = count((array) $d);
                return $n === 1 ? '1 item' : $n . ' items';
            case 'boolean':
                return $d ? 'On' : 'Off';
            case 'image':
                return (string) $d === '' ? 'no image' : basename((string) $d);
            case 'select':
                return (string) (($field['options'][(string) $d] ?? null) ?: $d);
            default:
                $t = excerpt((string) $d, 140);
                return $t === '' ? '(empty)' : $t;
        }
    }

    /**
     * Validate + normalise a submitted editor form.
     * @param array<string,mixed> $post the raw $_POST array
     * @return array{input:array<string,mixed>,resets:list<string>,errors:array<string,string>}
     */
    public static function collect(string $slug, array $post): array
    {
        $fields = PageRegistry::fields($slug);
        $blocks = isset($post['blocks']) && is_array($post['blocks']) ? $post['blocks'] : [];
        $reset = isset($post['reset']) && is_array($post['reset']) ? $post['reset'] : [];
        $visible = isset($post['visible']) && is_array($post['visible']) ? $post['visible'] : [];
        // repeaters that were on the form (see _repeater.php): when all their rows were removed no blocks[...] input is posted at all
        $present = isset($post['repeaters']) && is_array($post['repeaters']) ? array_map('strval', array_filter($post['repeaters'], 'is_scalar')) : [];

        $input = [];
        $resets = [];
        $errors = [];
        foreach ($fields as $key => $f) {
            if (isset($reset[$key]) && (string) $reset[$key] === '1') {
                $resets[] = $key;
                continue;
            }
            if (!array_key_exists($key, $blocks) && $f['type'] === 'repeater' && in_array($key, $present, true)) {
                $blocks[$key] = [];                         // the list was on the form and the editor removed every row
            }
            if (!array_key_exists($key, $blocks)) {
                continue;                                   // the field was not on the form (e.g. disabled) - leave it alone
            }
            $raw = $blocks[$key];
            $name = 'blocks[' . $key . ']';
            if ($f['type'] === 'repeater') {
                $rows = is_array($raw) ? array_values(array_filter($raw, 'is_array')) : [];
                $max = (int) ($f['max_rows'] ?? 50);
                if (count($rows) > $max) {
                    $errors[$name] = $f['label'] . ': at most ' . $max . ' items are allowed.';
                }
                foreach ($rows as $i => $row) {
                    foreach ($f['fields'] as $sk => $sf) {
                        self::check($sf, $row[$sk] ?? '', $name . '[' . $i . '][' . $sk . ']', $errors, $f['label'] . ', item ' . ($i + 1) . ': ');
                    }
                }
                $min = (int) ($f['min_rows'] ?? 0);
                $filled = count(array_filter($rows, static function (array $r) use ($f): bool {
                    foreach ($f['fields'] as $sk => $sf) {
                        if (trim((string) ($r[$sk] ?? '')) !== '') {
                            return true;
                        }
                    }
                    return false;
                }));
                if ($min > 0 && $filled < $min) {
                    $errors[$name] = $f['label'] . ': please keep at least ' . $min . ($min === 1 ? ' item.' : ' items.');
                }
                $input[$key] = $rows;
                continue;
            }
            self::check($f, $raw, $name, $errors);
            $input[$key] = is_scalar($raw) ? (string) $raw : '';
            if ($f['type'] === 'image' && array_key_exists($key . '.alt', $blocks) && is_scalar($blocks[$key . '.alt'])) {
                $input[$key . '.alt'] = (string) $blocks[$key . '.alt'];
            }
        }
        foreach (PageRegistry::sections($slug) as $skey => $section) {
            if ($section['toggle'] && isset($visible[$skey]) && is_scalar($visible[$skey])) {
                $input[$skey . '._visible'] = (string) $visible[$skey] === '1' ? '1' : '0';
            }
        }
        return ['input' => $input, 'resets' => $resets, 'errors' => $errors];
    }

    /** @param array<string,string> $errors */
    private static function check(array $f, mixed $raw, string $name, array &$errors, string $prefix = ''): void
    {
        if (!is_scalar($raw) && $raw !== null) {
            return;
        }
        $s = trim((string) $raw);
        $label = $prefix . (string) $f['label'];
        if ($s === '') {
            if (!empty($f['required']) && !in_array($f['type'], ['boolean', 'select'], true)) {
                $errors[$name] = $label . ' is required.';
            }
            return;
        }
        switch ($f['type']) {
            case 'url':
                if (Sanitizer::url($s) === '') {
                    $errors[$name] = $label . ': enter a page such as /about, a web address starting with https://, or mailto: / tel: / #section.';
                }
                break;
            case 'email':
                if (!filter_var($s, FILTER_VALIDATE_EMAIL)) {
                    $errors[$name] = $label . ': please enter a valid email address.';
                }
                break;
            case 'number':
                if (!is_numeric($s)) {
                    $errors[$name] = $label . ' must be a number.';
                } elseif (isset($f['min']) && (float) $s < (float) $f['min']) {
                    $errors[$name] = $label . ' must be at least ' . $f['min'] . '.';
                } elseif (isset($f['max']) && (float) $s > (float) $f['max']) {
                    $errors[$name] = $label . ' must be at most ' . $f['max'] . '.';
                }
                break;
        }
    }

    /**
     * Internal link suggestions for url inputs (<datalist>): route => title. Published, non-virtual pages only.
     * @return array<string,string>
     */
    public static function linkChoices(): array
    {
        $out = [];
        foreach (SitePages::all() as $p) {
            if ($p['virtual'] || $p['route'] === '') {
                continue;
            }
            $out[$p['route']] = $p['title'] . ($p['is_published'] ? '' : ' (unpublished)');
        }
        return $out;
    }

    /**
     * Who edited the page last: ['at' => 'Y-m-d H:i:s', 'by' => name|null] or null when nothing has been edited yet.
     * @return array{at:string,by:?string}|null
     */
    public static function lastEdit(string $slug): ?array
    {
        $row = Database::one(
            'SELECT x.at, u.name FROM ('
            . ' SELECT updated_at AS at, updated_by FROM content_blocks WHERE page_slug = ?'
            . ' UNION ALL SELECT updated_at, updated_by FROM pages WHERE slug = ? AND updated_by IS NOT NULL'
            . ') x LEFT JOIN users u ON u.id = x.updated_by ORDER BY x.at DESC LIMIT 1',
            [$slug, $slug]
        );
        return $row ? ['at' => (string) $row['at'], 'by' => $row['name'] !== null ? (string) $row['name'] : null] : null;
    }

    /**
     * Opaque version stamp of a page's editable data (unix time of the newest change). The editor posts it back; when the stored
     * stamp is newer, somebody else saved in the meantime (see PagesController::update()).
     */
    public static function stamp(string $slug): int
    {
        return (int) Database::value(
            'SELECT UNIX_TIMESTAMP(GREATEST('
            . 'COALESCE((SELECT MAX(updated_at) FROM content_blocks WHERE page_slug = ?), \'2000-01-01\'),'
            . 'COALESCE((SELECT updated_at FROM pages WHERE slug = ?), \'2000-01-01\')))',
            [$slug, $slug],
            0
        );
    }

    /** Number of saved overrides per page slug + newest edit, for the list screen. @return array<string,array{n:int,at:string}> */
    public static function editSummary(): array
    {
        $out = [];
        foreach (Database::all('SELECT page_slug, COUNT(*) AS n, MAX(updated_at) AS at FROM content_blocks GROUP BY page_slug') as $r) {
            $out[(string) $r['page_slug']] = ['n' => (int) $r['n'], 'at' => (string) $r['at']];
        }
        return $out;
    }
}
