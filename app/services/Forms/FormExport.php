<?php
declare(strict_types=1);

namespace App\Services\Forms;

use App\Core\CsvExport;
use App\Core\Response;

/**
 * CSV EXPORT of a form's submissions (ability forms.export). Contract: docs/FORMS.md section 12.
 *
 * One row per submission; columns: Reference, Submitted (EAT), Status, [Applying for], one column per question (form order; two questions with the same
 * label become "Label" and "Label (2)"), [Consent given at]. Answers that no longer have a question in the form (the question was deleted) get their own
 * column named after the label stored in the snapshot, so history is never dropped. Files are exported as their NAMES only. Dates are EAT.
 * Formula-injection is neutralised by App\Core\CsvExport (cells starting with = + - @ are prefixed).
 *
 *   return FormExport::response($form, ['status' => 'shortlisted']);     // streamed download, UTF-8 with BOM
 *   [$header, $rows] = FormExport::table($form, $submissions);           // pure data (tests)
 */
final class FormExport
{
    public static function filename(array $form): string
    {
        return 'form-' . preg_replace('/[^a-z0-9-]+/', '-', (string) $form['slug']) . '-' . FormService::now()->format('Y-m-d') . '.csv';
    }

    /** @param array<string,mixed> $filters inbox filters (SubmissionService::where) */
    public static function response(array $form, array $filters = []): Response
    {
        // pass 1: which columns exist (questions of the form + questions only the snapshots still know + whether any submission has a context)
        $extra = [];
        $hasContext = false;
        $hasConsent = false;
        foreach (SubmissionService::each((int) $form['id'], $filters) as $sub) {
            $hasContext = $hasContext || (string) ($sub['context_label'] ?? '') !== '';
            $hasConsent = $hasConsent || !empty($sub['consent_at']);
            foreach ($sub['entries'] as $e) {
                $extra[(string) ($e['id'] ?? '')] ??= (string) ($e['label'] ?? '');
            }
        }
        $cols = self::columns($form, $extra);
        $header = self::header($cols, $hasContext, $hasConsent);
        $labels = FormSettings::statusLabels((array) $form['cfg']);
        $rows = (static function () use ($form, $filters, $cols, $labels, $hasContext, $hasConsent): \Generator {
            foreach (SubmissionService::each((int) $form['id'], $filters) as $sub) {
                yield self::row($sub, $cols, $labels, $hasContext, $hasConsent);
            }
        })();
        return CsvExport::response(self::filename($form), $header, $rows);
    }

    /**
     * Header + rows for already loaded submissions (hydrated with entries).
     * @param iterable<array<string,mixed>> $submissions
     * @return array{0:list<string>,1:list<list<string>>}
     */
    public static function table(array $form, iterable $submissions): array
    {
        $subs = [];
        $extra = [];
        $hasContext = false;
        $hasConsent = false;
        foreach ($submissions as $s) {
            $subs[] = $s;
            $hasContext = $hasContext || (string) ($s['context_label'] ?? '') !== '';
            $hasConsent = $hasConsent || !empty($s['consent_at']);
            foreach ($s['entries'] as $e) {
                $extra[(string) ($e['id'] ?? '')] ??= (string) ($e['label'] ?? '');
            }
        }
        $cols = self::columns($form, $extra);
        $labels = FormSettings::statusLabels((array) $form['cfg']);
        $rows = [];
        foreach ($subs as $s) {
            $rows[] = self::row($s, $cols, $labels, $hasContext, $hasConsent);
        }
        return [self::header($cols, $hasContext, $hasConsent), $rows];
    }

    /**
     * Question columns: [['id' => field id, 'label' => unique column title], ...].
     * @param array<string,string> $extra field id => label found in snapshots (questions that may no longer exist)
     * @return list<array{id:string,label:string}>
     */
    public static function columns(array $form, array $extra = []): array
    {
        $cols = [];
        $seen = [];
        $add = static function (string $id, string $label) use (&$cols, &$seen): void {
            $base = $label !== '' ? $label : 'Question';
            $title = $base;
            for ($n = 2; isset($seen[mb_strtolower($title)]); $n++) {
                $title = $base . ' (' . $n . ')';
            }
            $seen[mb_strtolower($title)] = true;
            $cols[] = ['id' => $id, 'label' => $title];
        };
        $known = [];
        foreach (FormDefinition::inputFields((array) $form['def']) as $f) {
            $known[(string) $f['id']] = true;
            $add((string) $f['id'], (string) $f['label']);
        }
        foreach ($extra as $id => $label) {
            if ($id !== '' && !isset($known[$id])) {
                $add($id, $label);
            }
        }
        return $cols;
    }

    /** @param list<array{id:string,label:string}> $cols @return list<string> */
    public static function header(array $cols, bool $hasContext, bool $hasConsent): array
    {
        $h = ['Reference', 'Submitted (EAT)', 'Status'];
        if ($hasContext) {
            $h[] = 'Applying for';
        }
        foreach ($cols as $c) {
            $h[] = $c['label'];
        }
        if ($hasConsent) {
            $h[] = 'Consent given at (EAT)';
        }
        return $h;
    }

    /**
     * @param array<string,mixed> $sub hydrated submission
     * @param list<array{id:string,label:string}> $cols
     * @param array<string,string> $statusLabels
     * @return list<string>
     */
    public static function row(array $sub, array $cols, array $statusLabels, bool $hasContext, bool $hasConsent): array
    {
        $by = [];
        foreach ($sub['entries'] as $e) {
            $by[(string) ($e['id'] ?? '')] = (string) ($e['text'] ?? '');
        }
        $r = [
            SubmissionService::reference((int) $sub['form_id'], (int) $sub['id']),
            substr((string) $sub['created_at'], 0, 16),
            $statusLabels[(string) $sub['status']] ?? (string) $sub['status'],
        ];
        if ($hasContext) {
            $r[] = (string) ($sub['context_label'] ?? '');
        }
        foreach ($cols as $c) {
            $r[] = $by[$c['id']] ?? '';
        }
        if ($hasConsent) {
            $r[] = !empty($sub['consent_at']) ? substr((string) $sub['consent_at'], 0, 16) : '';
        }
        return $r;
    }
}
