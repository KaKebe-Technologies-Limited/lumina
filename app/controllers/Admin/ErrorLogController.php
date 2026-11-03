<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Request;
use App\Core\Response;

/**
 * Admin > Administration > Error log (read only): the newest errors the website wrote to storage/logs, so a "500" page can be diagnosed without opening the file manager.
 * Needs settings.manage. The logger already hides secret links and tokens (App\Core\Log). Route: GET admin/error-log?level=error|warning|all&q=text&days=1..7
 */
final class ErrorLogController extends AdminController
{
    private const MAX_LINES = 300;

    public function index(Request $request): Response
    {
        $this->authorize('settings.manage');
        $level = in_array($request->string('level'), ['all', 'warning', 'error'], true) ? $request->string('level') : 'error';
        $q = mb_substr(trim($request->string('q')), 0, 80);
        $days = max(1, min(7, $request->int('days') ?: 2));
        $files = [];
        $lines = [];
        for ($i = 0; $i < $days; $i++) {
            $f = STORAGE_PATH . '/logs/app-' . date('Y-m-d', strtotime('-' . $i . ' day')) . '.log';
            if (!is_file($f)) {
                continue;
            }
            $files[] = basename($f) . ' (' . number_format((int) filesize($f) / 1024, 1) . ' KB)';
            foreach (self::tail($f, 4000) as $ln) {
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+) (.*)$/', $ln, $m) !== 1) {
                    continue;
                }
                $lv = strtolower($m[2]);
                if ($level === 'error' && $lv !== 'error') {
                    continue;
                }
                if ($level === 'warning' && !in_array($lv, ['error', 'warning'], true)) {
                    continue;
                }
                if ($q !== '' && stripos($m[3], $q) === false) {
                    continue;
                }
                $lines[] = ['at' => $m[1], 'level' => $lv, 'text' => $m[3]];
            }
        }
        usort($lines, static fn(array $a, array $b): int => strcmp($b['at'], $a['at']));
        $lines = array_slice($lines, 0, self::MAX_LINES);
        return $this->render('admin/error-log', ['lines' => $lines, 'files' => $files, 'level' => $level, 'q' => $q, 'days' => $days],
            ['title' => 'Error log', 'nav' => 'errorlog', 'breadcrumbs' => [['Error log', null]]]);
    }

    /** The last $n lines of a file without loading all of it. @return list<string> */
    private static function tail(string $file, int $n): array
    {
        $size = (int) filesize($file);
        $fh = @fopen($file, 'rb');
        if ($fh === false || $size === 0) {
            return [];
        }
        $chunk = min($size, 1024 * 1024);
        fseek($fh, $size - $chunk);
        $data = (string) fread($fh, $chunk);
        fclose($fh);
        $rows = preg_split('/\R/', $data) ?: [];
        if ($chunk < $size) {
            array_shift($rows);                    // the first line may be cut
        }
        return array_slice(array_values(array_filter($rows, static fn(string $l): bool => $l !== '')), -$n);
    }
}
