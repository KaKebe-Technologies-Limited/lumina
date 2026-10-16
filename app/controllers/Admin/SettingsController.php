<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Support\MediaCleaner;
use App\Core\AdminController;
use App\Core\Config;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\Settings;
use App\Core\UploadException;
use App\Core\Uploader;
use App\Core\Validator;

/**
 * ORGANISATION-WIDE SETTINGS (ability settings.manage = admins). OWNER: B3 admin-core.
 * The form is generated from app/config/settings_schema.php (groups -> fields), so adding a key to the schema adds it here.
 *   GET  admin/settings
 *   POST admin/settings   (one form, all groups; "tabs" are a client-side view of the same form)
 * Only values that differ from the schema default are stored (saving the default removes the override).
 */
final class SettingsController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('settings.manage');
        $groups = (array) (Settings::schema()['groups'] ?? []);
        $custom = [];
        foreach (Settings::definitions() as $key => $def) {
            $custom[$key] = Settings::isCustomised($key) && (string) Settings::get($key) !== (string) ($def['default'] ?? '');
        }
        return $this->render('admin/settings/index', [
            'groups' => $groups,
            'values' => Settings::all(),
            'custom' => $custom,
            'checks' => $this->systemChecks(),
        ], ['title' => 'Site settings', 'nav' => 'settings', 'breadcrumbs' => [['Site settings', null]]]);
    }

    public function update(Request $request): Response
    {
        $this->authorize('settings.manage');
        $defs = Settings::definitions();
        $rules = [];
        $labels = [];
        foreach ($defs as $key => $def) {
            if (($def['type'] ?? 'text') === 'image' || ($def['type'] ?? '') === 'boolean') {
                continue;
            }
            $rules[$key] = (string) ($def['rules'] ?? 'nullable|max:500');
            $labels[$key] = (string) ($def['label'] ?? $key);
        }
        // forgiving input: accept a pasted <iframe> for the map and normalise the WhatsApp number before validating
        $input = $request->all();
        if (isset($input['map_embed_url']) && is_string($input['map_embed_url']) && preg_match('/src\s*=\s*["\']([^"\']+)["\']/i', $input['map_embed_url'], $m)) {
            $input['map_embed_url'] = html_entity_decode($m[1], ENT_QUOTES);
        }
        $reset = $request->post('reset', []);
        $reset = is_array($reset) ? $reset : [];

        $v = Validator::make($input, $rules, [], $labels);
        $errors = $v->errors();
        foreach ($reset as $key => $flag) {
            unset($errors[(string) $key]);                  // a field that is being reset needs no valid input
        }
        if (!isset($errors['map_embed_url']) && ($map = trim((string) ($input['map_embed_url'] ?? ''))) !== '') {
            $host = strtolower((string) parse_url($map, PHP_URL_HOST));
            $path = (string) parse_url($map, PHP_URL_PATH);
            if (!in_array($host, ['www.google.com', 'google.com', 'maps.google.com'], true) || !str_starts_with($path, '/maps')) {
                $errors['map_embed_url'] = 'Use the address of a Google Maps embed: in Google Maps choose Share, then Embed a map, and copy the link after src=.';
            }
        }
        if (!isset($errors['whatsapp']) && ($wa = trim((string) ($input['whatsapp'] ?? ''))) !== ''
            && !preg_match('~^https://(wa\.me|api\.whatsapp\.com|chat\.whatsapp\.com)/~i', $wa) && !preg_match('/^\+?[0-9][0-9\s().\-]{5,24}$/', $wa)) {
            $errors['whatsapp'] = 'Enter a WhatsApp link (https://wa.me/256...) or just the phone number.';
        }
        if ($errors !== []) {
            $this->fail($errors, 'admin/settings');
        }

        $changed = [];
        $purge = [];
        $toSave = [];
        foreach ($defs as $key => $def) {
            $type = (string) ($def['type'] ?? 'text');
            $default = (string) ($def['default'] ?? '');
            $current = (string) Settings::get($key, '');
            $wasCustom = Settings::isCustomised($key);

            if (isset($reset[$key]) && (string) $reset[$key] === '1') {
                if ($wasCustom) {
                    if ($type === 'image') {
                        $purge[] = $current;
                    }
                    Settings::reset($key);
                    $changed[] = $key;
                }
                continue;
            }
            if ($type === 'image') {
                try {
                    $new = (string) (Uploader::resolveImageField($request, $key, $current !== '' ? $current : null) ?? '');
                } catch (UploadException $e) {
                    $this->fail([$key => $e->getMessage()], 'admin/settings');
                }
            } elseif ($type === 'boolean') {
                $new = $request->string($key) === '1' ? '1' : '0';
            } elseif (!$request->has($key)) {
                continue;
            } else {
                $new = $key === 'map_embed_url' ? trim((string) ($input[$key] ?? '')) : $this->clean($type, $input[$key] ?? '');
            }
            if ($new === $current) {
                continue;
            }
            if ($new === $default) {
                if ($wasCustom) {
                    Settings::reset($key);
                    $changed[] = $key;
                }
                continue;
            }
            $toSave[$key] = $new;
            $changed[] = $key;
        }
        if ($toSave !== []) {
            Settings::setMany($toSave);
        }
        Settings::flush();
        MediaCleaner::purge($purge);

        $this->audit('settings.update', 'settings', null, $changed === [] ? 'Saved settings (no changes)' : 'Updated settings: ' . implode(', ', array_slice($changed, 0, 12)), ['keys' => $changed]);
        Flash::success($changed === [] ? 'Nothing changed.' : 'Settings saved (' . count($changed) . (count($changed) === 1 ? ' setting' : ' settings') . ' updated).');
        return $this->redirect('admin/settings' . ($request->string('tab') !== '' && preg_match('/^[a-z_]+$/', $request->string('tab')) ? '#tab-' . $request->string('tab') : ''));
    }

    private function clean(string $type, mixed $value): string
    {
        $s = is_scalar($value) ? (string) $value : '';
        $s = str_replace(["\r\n", "\r", "\0"], ["\n", "\n", ''], strip_tags($s));
        return trim($type === 'textarea' ? $s : (string) preg_replace('/\s+/u', ' ', $s));
    }

    /** @return list<array{label:string,ok:bool|null,detail:string}> ok: true = fine, false = problem, null = informational */
    private function systemChecks(): array
    {
        $ini = static function (string $k): int {
            $v = trim((string) ini_get($k));
            $n = (int) $v;
            return match (strtolower(substr($v, -1))) {
                'g' => $n * 1073741824,
                'm' => $n * 1048576,
                'k' => $n * 1024,
                default => $n,
            };
        };
        $debug = (bool) Config::get('app.debug', false);
        $env = (string) Config::get('app.env', 'production');
        $mail = (string) Config::get('mail.driver', 'mail');
        return [
            ['label' => 'PHP version', 'ok' => version_compare(PHP_VERSION, '8.1.0', '>='), 'detail' => PHP_VERSION],
            ['label' => 'Image resizing (GD)', 'ok' => extension_loaded('gd') ? true : ($env === 'local' ? null : false), 'detail' => extension_loaded('gd')
                ? 'Uploaded photos are shrunk to a web-friendly size and re-saved without location or camera data.'
                : 'Not installed: uploaded photos are stored at their original size, so keep them small. Location (GPS) and camera data is still removed from every photo by a built-in cleaner, but installing PHP\'s GD extension is recommended.'],
            ['label' => 'Site address (APP_URL)', 'ok' => (string) Config::get('app.url', '') !== '' ? true : ($env === 'local' ? null : false), 'detail' => (string) Config::get('app.url', '') !== ''
                ? (string) Config::get('app.url') . ': links in e-mails, canonical addresses and the sitemap always use it.'
                : 'Not set: set APP_URL=https://your-domain in the .env file (required on the live site, so links never depend on the address a visitor typed).'],
            ['label' => 'Signing key (APP_KEY / storage/app.key)', 'ok' => Config::appKeyPersisted() ? true : ($env === 'local' ? null : false), 'detail' => Config::appKeyPersisted()
                ? 'Stored: form spam checks keep working between requests.'
                : 'Cannot be stored: make the "storage" folder writable or set APP_KEY in the .env file.'],
            ['label' => 'Uploads folder writable', 'ok' => is_dir(UPLOAD_PATH) && is_writable(UPLOAD_PATH), 'detail' => is_writable(UPLOAD_PATH) ? 'Yes' : 'No: images cannot be uploaded until the "uploads" folder is writable.'],
            ['label' => 'Private storage writable', 'ok' => is_dir(STORAGE_PATH . '/private') && is_writable(STORAGE_PATH . '/private'), 'detail' => is_writable(STORAGE_PATH . '/private') ? 'Yes' : 'No: CVs and reports cannot be saved.'],
            ['label' => 'Largest upload accepted', 'ok' => null, 'detail' => human_filesize(min($ini('upload_max_filesize'), $ini('post_max_size')) ?: 0) . ' (PHP limit)'],
            ['label' => 'Email delivery', 'ok' => $mail === 'file' ? null : true, 'detail' => $mail === 'file' ? 'Emails are written to storage/mail instead of being sent (development mode).' : 'Emails are sent with the server mail function.'],
            ['label' => 'Debug mode', 'ok' => !$debug || $env === 'local', 'detail' => $debug ? 'ON: technical error details are shown to visitors. Switch it off (APP_DEBUG=0) on the live site.' : 'Off'],
            ['label' => 'Secure connection (HTTPS)', 'ok' => $this->request->isSecure() ? true : ($env === 'local' ? null : false), 'detail' => $this->request->isSecure() ? 'Yes' : 'No: use https:// on the live site.'],
        ];
    }
}
