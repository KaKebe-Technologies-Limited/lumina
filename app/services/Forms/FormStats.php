<?php
declare(strict_types=1);

namespace App\Services\Forms;

use App\Core\Database;

/**
 * STATISTICS of one form's submissions (summary tab). Contract: docs/FORMS.md section 7. Counts only: no personal data leaves this class.
 *
 *   FormStats::overview($form)              total, last 7 / 30 days, by status, newest / oldest date
 *   FormStats::perDay($formId, 30)          [['date' => '2026-10-19', 'count' => 3], ...]  oldest first, zero days included (EAT)
 *   FormStats::choiceTallies($form)         per dropdown / radio / checkbox / yes-no / rating question: how many picked each choice
 */
final class FormStats
{
    private const SCAN_LIMIT = 50000;

    /** @return array{total:int,week:int,month:int,by_status:array<string,int>,unread:int,first_at:?string,last_at:?string} */
    public static function overview(array $form): array
    {
        $id = (int) $form['id'];
        $now = FormService::now();
        $by = SubmissionService::statusCounts($id);
        $range = Database::one('SELECT MIN(created_at) AS a, MAX(created_at) AS b FROM form_submissions WHERE form_id = ?', [$id]);
        return [
            'total'     => $by['all'],
            'week'      => (int) Database::value('SELECT COUNT(*) FROM form_submissions WHERE form_id = ? AND created_at >= ?', [$id, $now->modify('-6 days')->format('Y-m-d') . ' 00:00:00'], 0),
            'month'     => (int) Database::value('SELECT COUNT(*) FROM form_submissions WHERE form_id = ? AND created_at >= ?', [$id, $now->modify('-29 days')->format('Y-m-d') . ' 00:00:00'], 0),
            'by_status' => array_diff_key($by, ['all' => 1]),
            'unread'    => $by['new'],
            'first_at'  => $range['a'] ?? null,
            'last_at'   => $range['b'] ?? null,
        ];
    }

    /** @return list<array{date:string,count:int}> */
    public static function perDay(int $formId, int $days = 30, ?\DateTimeImmutable $today = null): array
    {
        $days = max(1, min(366, $days));
        $today ??= FormService::now();
        $start = $today->modify('-' . ($days - 1) . ' days')->format('Y-m-d');
        $rows = Database::all(
            'SELECT DATE(created_at) AS d, COUNT(*) AS n FROM form_submissions WHERE form_id = ? AND created_at >= ? GROUP BY DATE(created_at)',
            [$formId, $start . ' 00:00:00']
        );
        $map = [];
        foreach ($rows as $r) {
            $map[(string) $r['d']] = (int) $r['n'];
        }
        $out = [];
        for ($i = 0; $i < $days; $i++) {
            $d = $today->modify('-' . ($days - 1 - $i) . ' days')->format('Y-m-d');
            $out[] = ['date' => $d, 'count' => $map[$d] ?? 0];
        }
        return $out;
    }

    /**
     * @return list<array{id:string,label:string,type:string,total:int,average:?float,options:list<array{value:string,label:string,count:int,share:float}>}>
     *   total = submissions that answered the question; share = count / total (0..1); average only for rating questions.
     */
    public static function choiceTallies(array $form): array
    {
        $fields = [];
        foreach (FormDefinition::inputFields((array) $form['def']) as $f) {
            if (in_array($f['type'], ['select', 'radio', 'checkboxes', 'yes_no', 'rating'], true)) {
                $fields[(string) $f['id']] = $f;
            }
        }
        if ($fields === []) {
            return [];
        }
        $counts = [];       // field id => value => count
        $labels = [];       // field id => value => label found in snapshots
        $answered = [];
        $sum = [];
        $scanned = 0;
        foreach (SubmissionService::each((int) $form['id'], ['statuses' => array_values(array_diff(\App\Models\FormSubmission::STATUSES, ['spam']))]) as $sub) {
            if (++$scanned > self::SCAN_LIMIT) {
                break;
            }
            foreach ($sub['entries'] as $e) {
                $fid = (string) ($e['id'] ?? '');
                if (!isset($fields[$fid])) {
                    continue;
                }
                $vals = is_array($e['value'] ?? null) ? $e['value'] : [(string) ($e['value'] ?? '')];
                $vals = array_values(array_filter(array_map('strval', $vals), static fn(string $x): bool => $x !== ''));
                if ($vals === []) {
                    continue;
                }
                $answered[$fid] = ($answered[$fid] ?? 0) + 1;
                foreach ($vals as $v) {
                    $counts[$fid][$v] = ($counts[$fid][$v] ?? 0) + 1;
                    if (($e['type'] ?? '') === 'rating') {
                        $sum[$fid] = ($sum[$fid] ?? 0) + (int) $v;
                    }
                }
                if (!is_array($e['value'] ?? null) && ($e['text'] ?? '') !== '') {
                    $labels[$fid][(string) $e['value']] = (string) $e['text'];
                }
            }
        }
        $out = [];
        foreach ($fields as $fid => $f) {
            $total = (int) ($answered[$fid] ?? 0);
            $opts = [];
            $seen = [];
            $push = static function (string $value, string $label) use (&$opts, &$seen, $counts, $fid, $total): void {
                $n = (int) ($counts[$fid][$value] ?? 0);
                $seen[$value] = true;
                $opts[] = ['value' => $value, 'label' => $label, 'count' => $n, 'share' => $total > 0 ? round($n / $total, 4) : 0.0];
            };
            switch ($f['type']) {
                case 'rating':
                    for ($i = 1; $i <= 5; $i++) {
                        $push((string) $i, (string) $i);
                    }
                    break;
                case 'yes_no':
                    $push('yes', FormText::get('ui.yes'));
                    $push('no', FormText::get('ui.no'));
                    break;
                default:
                    foreach ((array) $f['options'] as $o) {
                        $push((string) $o['value'], (string) $o['label']);
                    }
                    if (!empty($f['allow_other']) || isset($counts[$fid][FormDefinition::OTHER])) {
                        $push(FormDefinition::OTHER, (string) (($f['other_label'] ?? '') !== '' ? $f['other_label'] : FormText::get('ui.other_short')));
                    }
                    foreach (array_keys($counts[$fid] ?? []) as $v) {          // options that were removed from the form later
                        if (!isset($seen[(string) $v])) {
                            $push((string) $v, (string) ($labels[$fid][(string) $v] ?? $v));
                        }
                    }
            }
            $out[] = [
                'id'      => $fid,
                'label'   => (string) $f['label'],
                'type'    => (string) $f['type'],
                'total'   => $total,
                'average' => $f['type'] === 'rating' && $total > 0 ? round(($sum[$fid] ?? 0) / $total, 2) : null,
                'options' => $opts,
            ];
        }
        return $out;
    }
}
